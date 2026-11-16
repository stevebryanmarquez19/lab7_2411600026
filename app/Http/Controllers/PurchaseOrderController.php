<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\ReorderService;
use App\Services\SearchService;
use App\Services\SupplierPerformanceService;
use App\Support\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $filters = $request->only(['search', 'status', 'supplier_id', 'date_from', 'date_to']);
        // searchPurchaseOrders() eager loads supplier + creator, so the table runs 3 queries, not 1 + 2N.
        $orders = $search->searchPurchaseOrders($filters)->paginate(15)->withQueryString();
        $suppliers = Supplier::withTrashed()->orderBy('company_name')->pluck('company_name', 'id');

        return view('purchase-orders.index', compact('orders', 'suppliers'));
    }

    public function create()
    {
        $this->authorize('create', PurchaseOrder::class);

        return view('purchase-orders.create', $this->formData(new PurchaseOrder(['order_date' => today()])));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);
        $data = $this->validateOrder($request);

        $order = DB::transaction(function () use ($data) {
            $supplier = Supplier::findOrFail($data['supplier_id']);
            $order = PurchaseOrder::create([
                'reference_number' => PurchaseOrder::generateReferenceNumber(),
                'supplier_id' => $supplier->id,
                'status' => PurchaseOrder::DRAFT,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? \Illuminate\Support\Carbon::parse($data['order_date'])->addDays($supplier->lead_time_days),
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? 'Payment terms: ' . $supplier->payment_terms,
                'created_by' => auth()->id(),
            ]);
            $this->syncItems($order, $data['items']);
            $order->calculateTotal();

            return $order;
        });

        // "Save & submit" goes straight into the approval queue (the observer e-mails the approvers).
        if ($request->input('action') === 'submit' && Gate::forUser(auth()->user())->allows('submit', $order)) {
            $order->statusComment = 'Submitted for approval when created.';
            $order->update(['status' => PurchaseOrder::PENDING]);

            return redirect()->route('purchase-orders.show', $order)->with('success', $order->reference_number . ' was created and submitted for approval.');
        }

        return redirect()->route('purchase-orders.show', $order)->with('success', $order->reference_number . ' was saved as a draft.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'approvedBy', 'activityLogs']);

        return view('purchase-orders.show', ['order' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);
        $purchaseOrder->load('items');

        return view('purchase-orders.edit', $this->formData($purchaseOrder));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder); // draft only (see PurchaseOrderPolicy)
        $data = $this->validateOrder($request, $purchaseOrder);

        DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);
            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $data['items']);
            $purchaseOrder->calculateTotal();
        });

        if ($request->input('action') === 'submit' && Gate::forUser(auth()->user())->allows('submit', $purchaseOrder)) {
            $purchaseOrder->statusComment = 'Submitted for approval.';
            $purchaseOrder->update(['status' => PurchaseOrder::PENDING]);

            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Order updated and submitted for approval.');
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Draft purchase order updated.');
    }

    /** Drafts are soft-deleted; anything further along is cancelled so the history is kept. */
    public function destroy(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === PurchaseOrder::DRAFT) {
            $this->authorize('delete', $purchaseOrder);
            $reference = $purchaseOrder->reference_number;
            $purchaseOrder->delete();

            return redirect()->route('purchase-orders.index')->with('success', 'Draft ' . $reference . ' was deleted.');
        }

        $this->authorize('cancel', $purchaseOrder);
        $purchaseOrder->statusComment = $request->input('comment') ?: 'Cancelled by user.';
        $purchaseOrder->update(['status' => PurchaseOrder::CANCELLED]);
        SupplierPerformanceService::forget($purchaseOrder->supplier_id);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $purchaseOrder->reference_number . ' was cancelled.');
    }

    /* ---------------- Workflow actions ---------------- */

    public function submit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('submit', $purchaseOrder);

        $purchaseOrder->statusComment = 'Submitted for approval.';
        $purchaseOrder->update(['status' => PurchaseOrder::PENDING]);

        return back()->with('success', $purchaseOrder->reference_number . ' was submitted for approval.');
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve', $purchaseOrder); // role + amount threshold (multi-level)

        $purchaseOrder->statusComment = $request->input('comment');
        $purchaseOrder->update([
            'status' => PurchaseOrder::APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', $purchaseOrder->reference_number . ' was approved.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve', $purchaseOrder);
        $data = $request->validate(['rejection_reason' => 'required|string|min:5|max:1000']);

        $purchaseOrder->update([
            'status' => PurchaseOrder::REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'rejected_at' => now(),
        ]);

        return back()->with('success', $purchaseOrder->reference_number . ' was rejected.');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('send', $purchaseOrder);

        $purchaseOrder->load('supplier');
        $purchaseOrder->statusComment = 'Order sent to supplier.';
        $purchaseOrder->update(['status' => PurchaseOrder::SENT, 'sent_at' => now()]);

        $email = optional($purchaseOrder->supplier)->email;
        $message = $email
            ? $purchaseOrder->reference_number . ' was marked as sent and e-mailed to ' . $email . '.'
            : $purchaseOrder->reference_number . ' was marked as sent. The supplier has no e-mail on file, so no message was sent.';

        return back()->with('success', $message);
    }

    /**
     * Record goods received. Each quantity becomes an inventory "in" transaction, so the existing
     * InventoryTransactionObserver / MySQL triggers raise the product stock and fire low-stock checks.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('receive', $purchaseOrder);

        $data = $request->validate([
            'received' => 'required|array',
            'received.*' => 'nullable|integer|min:0|max:1000000',
            'close_short' => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($purchaseOrder, $data, $request) {
                $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
                $order->load('items');
                $receivedAny = false;

                foreach ($order->items as $item) {
                    $qty = (int) ($data['received'][$item->id] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    if ($qty > $item->outstanding_quantity) {
                        throw new \RuntimeException($item->product_name . ': only ' . $item->outstanding_quantity . ' unit(s) are still outstanding.');
                    }
                    if (!$item->product_id) {
                        throw new \RuntimeException($item->product_name . ' no longer exists in inventory, so stock cannot be added.');
                    }

                    $item->increment('received_quantity', $qty);
                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'actor_name' => session('fitpulse_user', 'Admin'),
                        'type' => 'in',
                        'quantity' => $qty,
                        'reference' => $order->reference_number,
                        'notes' => 'Received against purchase order ' . $order->reference_number . '.',
                    ]);
                    $receivedAny = true;
                }

                if (!$receivedAny) {
                    throw new \RuntimeException('Enter a received quantity for at least one item.');
                }

                $order->load('items');
                $complete = $order->isFullyReceived() || $request->boolean('close_short');
                $order->statusComment = $complete ? 'Order completed.' : 'Partial delivery recorded.';
                $order->update($complete
                    ? ['status' => PurchaseOrder::RECEIVED, 'delivered_at' => now()]
                    : ['status' => PurchaseOrder::PARTIAL]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['received' => $e->getMessage()])->withInput();
        }

        SupplierPerformanceService::forget($purchaseOrder->supplier_id);

        return back()->with('success', 'Goods receipt recorded and inventory updated.');
    }

    public function pdf(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        if (!Pdf::available()) {
            return back()->with('error', 'PDF export needs the dompdf package. Run: composer require barryvdh/laravel-dompdf');
        }
        $purchaseOrder->load(['supplier', 'items', 'createdBy', 'approvedBy']);

        return Pdf::make('purchase-orders.pdf', ['order' => $purchaseOrder])->download($purchaseOrder->reference_number . '.pdf');
    }

    /** Run the reorder check from the UI (same logic as the scheduled command). */
    public function generate(ReorderService $reorder)
    {
        Gate::authorize('generate-orders');

        $created = $reorder->generatePurchaseOrders();

        return redirect()->route('purchase-orders.index', ['status' => 'draft'])->with(
            $created->isEmpty() ? 'error' : 'success',
            $created->isEmpty()
                ? 'Nothing needs reordering right now (or the products already have an open order).'
                : $created->count() . ' draft purchase order(s) were generated. Review and submit them for approval.'
        );
    }

    /* ---------------- Helpers ---------------- */

    protected function formData(PurchaseOrder $order): array
    {
        return [
            'order' => $order,
            'suppliers' => Supplier::active()->orderBy('company_name')->get(['id', 'company_name', 'lead_time_days', 'payment_terms']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku', 'supplier_id', 'cost_price', 'pack_size', 'min_order_quantity', 'quantity']),
        ];
    }

    protected function validateOrder(Request $request, ?PurchaseOrder $order = null): array
    {
        return $request->validate([
            // supplier must exist, be active, and not be soft-deleted
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('is_active', 1)->whereNull('deleted_at')],
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity' => 'required|integer|min:1|max:1000000',
            'items.*.unit_price' => 'required|numeric|min:0|max:99999999',
        ], [
            'supplier_id.exists' => 'Choose an active supplier.',
            'items.required' => 'Add at least one line item.',
            'items.min' => 'Add at least one line item.',
            'items.*.product_id.distinct' => 'The same product is listed twice. Combine the quantities into one line.',
        ]);
    }

    protected function syncItems(PurchaseOrder $order, array $items): void
    {
        $products = Product::whereIn('id', collect($items)->pluck('product_id'))->get()->keyBy('id');

        foreach ($items as $line) {
            $product = $products[$line['product_id']];
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'], // price frozen at the time of order
            ]);
        }
    }
}

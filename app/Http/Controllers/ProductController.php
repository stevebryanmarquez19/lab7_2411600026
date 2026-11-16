<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    private $categories = ['Cardio', 'Strength', 'Sports', 'Flexibility', 'Recovery'];

    public function index()
    {
        $products = Product::orderByDesc('date')->orderByDesc('id')->get();
        $transactions = InventoryTransaction::with(['product', 'user'])->latest()->limit(12)->get();
        $totalKcal = $products->sum(function ($p) { return $p->quantity * $p->unit_price * 10; });
        $totalQuantity = $products->sum('quantity');
        $totalDuration = $products->sum('unit_price');
        $completedCount = $products->where('status', 'Completed')->count();
        $lowStockCount = $products->filter(function ($p) { return $p->quantity <= $p->reorder_level; })->count();
        $categorySummary = $products->groupBy('category')->map(function ($items) {
            return $items->sum('quantity');
        });
        $dailySummary = $products->groupBy('date')->map(function ($items) {
            return $items->sum('quantity');
        })->sortKeys()->take(-7);
        $suppliers = Supplier::active()->orderBy('company_name')->get(['id', 'company_name']);
        $signedInUser = auth()->user();
        $unreadNotificationCount = $signedInUser ? $signedInUser->unreadNotifications()->count() : 0;
        $recentNotifications = $signedInUser ? $signedInUser->notifications()->latest()->limit(5)->get() : collect();

        return view('dashboard', compact(
            'products', 'totalKcal', 'totalQuantity', 'totalDuration',
            'completedCount', 'lowStockCount', 'categorySummary', 'dailySummary', 'transactions',
            'unreadNotificationCount', 'recentNotifications', 'suppliers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $validated = $this->withReplenishmentDefaults($validated);
        $validated['sku'] = $this->nextSku();
        $validated['reorder_level'] = max(1, (int) $request->input('reorder_level', 5));
        $initialQuantity = (int) $validated['quantity'];
        DB::transaction(function () use ($validated, $initialQuantity) {
            $product = Product::create($validated);
            if ($initialQuantity > 0) {
                InventoryTransaction::create(['product_id' => $product->id, 'type' => 'opening', 'quantity' => $initialQuantity, 'quantity_before' => 0, 'quantity_after' => $initialQuantity, 'actor_name' => session('fitpulse_user', 'Admin'), 'user_id' => auth()->id(), 'notes' => 'Opening stock when item was created.']);
            }
        });
        return redirect()->route('dashboard')->with('success', 'Inventory record added successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request);
        $validated = $this->withReplenishmentDefaults($validated);
        $validated['reorder_level'] = max(1, (int) $request->input('reorder_level', 5));
        // Stock quantity is changed only through a stock movement to preserve the audit trail.
        $validated['quantity'] = $product->quantity;
        DB::transaction(function () use ($product, $validated) { $product->update($validated); });
        return redirect()->route('dashboard')->with('success', 'Inventory record updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('dashboard')->with('success', 'Inventory record deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }
        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }
        if ($request->filled('min_duration')) $query->where('unit_price', '>=', $request->min_duration);
        if ($request->filled('max_duration')) $query->where('unit_price', '<=', $request->max_duration);
        $products = $query->orderByDesc('date')->orderByDesc('id')->get();

        return new StreamedResponse(function () use ($products) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'SKU', 'Item Name', 'Category', 'Duration (mins)', 'Quantity', 'Reorder Level', 'Calculated Value (kcal)', 'Status', 'Date']);
            foreach ($products as $p) {
                fputcsv($out, [$p->id, $p->sku, $p->name, $p->category, $p->unit_price, $p->quantity, $p->reorder_level, $p->quantity * $p->unit_price * 10, $p->status, $p->date]);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="fitpulse-inventory-' . date('Y-m-d') . '.csv"',
        ]);
    }

    private function validateProduct(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => ['required', Rule::in($this->categories)],
            'unit_price' => 'required|integer|min:1|max:1440',
            'quantity' => 'required|integer|min:0|max:1000000',
            'reorder_level' => 'nullable|integer|min:1|max:1000000',
            'status' => ['required', Rule::in(['Completed', 'Incomplete'])],
            'date' => 'required|date',
            // Lab 7: replenishment settings
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'cost_price' => 'nullable|numeric|min:0|max:99999999',
            'pack_size' => 'nullable|integer|min:1|max:100000',
            'min_order_quantity' => 'nullable|integer|min:1|max:1000000',
        ]);
    }

    private function withReplenishmentDefaults(array $validated)
    {
        $validated['supplier_id'] = $validated['supplier_id'] ?? null;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['pack_size'] = $validated['pack_size'] ?? 1;
        $validated['min_order_quantity'] = $validated['min_order_quantity'] ?? 1;
        return $validated;
    }

    private function nextSku()
    {
        $lastNumber = Product::pluck('sku')->map(function ($sku) {
            return (int) substr($sku, 4);
        })->max() ?? 0;
        return 'FIT-' . str_pad(((int) $lastNumber) + 1, 3, '0', STR_PAD_LEFT);
    }
}

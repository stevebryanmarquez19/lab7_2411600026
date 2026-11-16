<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use App\Services\SearchService;
use App\Services\SupplierPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        Gate::authorize('view-suppliers');

        $filters = $request->only(['search', 'status', 'max_lead_time', 'sort', 'direction']);
        $suppliers = $search->searchSuppliers($filters)
            ->withCount(['products', 'purchaseOrders'])
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        Gate::authorize('manage-suppliers');

        return view('suppliers.create', ['supplier' => new Supplier(['payment_terms' => 'Net 30', 'lead_time_days' => 7, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-suppliers');

        $data = $this->validateSupplier($request);
        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier "' . $supplier->company_name . '" was added.');
    }

    public function show(Supplier $supplier, SupplierPerformanceService $performance)
    {
        Gate::authorize('view-suppliers');

        $recentOrders = $supplier->purchaseOrders()->with('createdBy')->latest('order_date')->latest('id')->limit(10)->get();
        $products = $supplier->products()->orderBy('name')->get();
        $metrics = $performance->calculatePerformance($supplier);

        return view('suppliers.show', compact('supplier', 'recentOrders', 'products', 'metrics'));
    }

    public function edit(Supplier $supplier)
    {
        Gate::authorize('manage-suppliers');

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        Gate::authorize('manage-suppliers');

        $supplier->update($this->validateSupplier($request, $supplier));
        SupplierPerformanceService::forget($supplier->id);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier details were updated.');
    }

    /** Soft delete: the row stays in the database so historical purchase orders still show the supplier. */
    public function destroy(Supplier $supplier)
    {
        Gate::authorize('manage-suppliers');

        $open = $supplier->purchaseOrders()->active()->count();
        if ($open > 0) {
            return redirect()->route('suppliers.index')
                ->with('error', $supplier->company_name . ' has ' . $open . ' open purchase order(s). Receive or cancel them before deleting this supplier.');
        }

        $supplier->delete();
        ActivityLog::record($supplier, 'deleted', null, null, 'Supplier soft-deleted.');
        SupplierPerformanceService::forget($supplier->id);

        return redirect()->route('suppliers.index')->with('success', $supplier->company_name . ' was deleted. You can restore it from the "Deleted" filter.');
    }

    public function restore($id)
    {
        Gate::authorize('restore-suppliers');

        $supplier = Supplier::onlyTrashed()->findOrFail($id);

        // E-mail uniqueness is only enforced among live suppliers, so check before bringing this one back.
        if (Supplier::where('email', $supplier->email)->exists()) {
            return redirect()->route('suppliers.index', ['status' => 'deleted'])
                ->with('error', 'Cannot restore: another supplier already uses ' . $supplier->email . '. Change that e-mail first.');
        }

        $supplier->restore();
        ActivityLog::record($supplier, 'restored', null, null, 'Supplier restored.');
        SupplierPerformanceService::forget($supplier->id);

        return redirect()->route('suppliers.show', $supplier)->with('success', $supplier->company_name . ' was restored.');
    }

    protected function validateSupplier(Request $request, ?Supplier $supplier = null): array
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            // Unique among suppliers that are not soft-deleted.
            'email' => ['required', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore(optional($supplier)->id)->whereNull('deleted_at')],
            'phone' => ['required', 'regex:/^\+?[0-9][0-9\s\-().]{6,19}$/'],
            'address' => 'required|string|max:1000',
            'payment_terms' => ['required', Rule::in(Supplier::PAYMENT_TERMS)],
            'lead_time_days' => 'required|integer|min:0|max:365',
            'minimum_order_amount' => 'required|numeric|min:0|max:99999999',
            'notes' => 'nullable|string|max:2000',
        ], [
            'phone.regex' => 'Enter a valid phone number, e.g. +63 82 221 4455 or 0917-123-4567.',
            'email.unique' => 'Another supplier already uses this e-mail address.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

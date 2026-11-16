@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
<section class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div>
    <div class="eyebrow">PROCUREMENT / SUPPLIERS</div>
    <h1 class="h3 fw-bold mb-1">Suppliers</h1>
    <p class="text-muted mb-0">Vendor details, lead times and payment terms. Deleted suppliers are kept for audit.</p>
  </div>
  @can('manage-suppliers')
  <a href="{{ route('suppliers.create') }}" class="btn btn-emerald"><i class="bi bi-plus-lg me-1"></i> Add Supplier</a>
  @endcan
</section>

<div class="panel-card card mb-4">
  <div class="card-body border-bottom">
    <x-filter-form :action="route('suppliers.index')" :fields="[
        ['name' => 'search', 'label' => 'Search name, contact or e-mail', 'type' => 'text', 'col' => 'col-lg-3 col-md-6', 'placeholder' => 'Search suppliers...'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'col' => 'col-lg-2 col-md-6', 'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'deleted' => 'Deleted (soft)']],
        ['name' => 'max_lead_time', 'label' => 'Max lead time (days)', 'type' => 'number', 'col' => 'col-lg-2 col-md-6'],
        ['name' => 'sort', 'label' => 'Sort by', 'type' => 'select', 'col' => 'col-lg-2 col-md-6', 'placeholder' => 'Name', 'options' => ['lead_time_days' => 'Lead time', 'minimum_order_amount' => 'Minimum order', 'created_at' => 'Date added']],
        ['name' => 'direction', 'label' => 'Order', 'type' => 'select', 'col' => 'col-lg-1 col-md-6', 'placeholder' => 'A→Z', 'options' => ['desc' => 'Z→A']],
    ]" />
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 inventory-table">
      <thead><tr><th>Supplier</th><th>Contact</th><th>Payment terms</th><th>Lead time</th><th>Min. order</th><th>Products</th><th>Orders</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      @forelse($suppliers as $supplier)
        <tr>
          <td><div class="item-name">{{ $supplier->company_name }}</div><div class="item-sku">{{ $supplier->email }}</div></td>
          <td>{{ $supplier->contact_person }}<div class="item-sku">{{ $supplier->phone }}</div></td>
          <td>{{ $supplier->payment_terms }}</td>
          <td>{{ $supplier->lead_time_days }} <span class="text-muted small">days</span></td>
          <td>{{ config('procurement.currency') }}{{ number_format($supplier->minimum_order_amount, 2) }}</td>
          <td>{{ $supplier->products_count }}</td>
          <td>{{ $supplier->purchase_orders_count }}</td>
          <td>
            @if($supplier->trashed())<span class="badge text-bg-dark">Deleted</span>
            @elseif($supplier->is_active)<span class="badge text-bg-success">Active</span>
            @else<span class="badge text-bg-secondary">Inactive</span>@endif
          </td>
          <td class="text-end text-nowrap">
            @if($supplier->trashed())
              @can('restore-suppliers')
              <form action="{{ route('suppliers.restore', $supplier->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success" type="submit"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
              @endcan
            @else
              <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm action-btn" title="View"><i class="bi bi-eye"></i></a>
              @can('manage-suppliers')
              <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm action-btn" title="Edit"><i class="bi bi-pencil-square"></i></a>
              <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm(@json('Delete ' . $supplier->company_name . '? It can be restored later.'));">@csrf @method('DELETE')<button class="btn btn-sm action-btn delete-btn" title="Delete" type="submit"><i class="bi bi-trash3"></i></button></form>
              @endcan
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="9" class="empty-state"><i class="bi bi-truck"></i><div class="fw-semibold mt-2">No suppliers found</div><div class="small">Try changing the filters, or add a supplier.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span class="small text-muted">Showing {{ $suppliers->firstItem() ?? 0 }}–{{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }}</span>
    {{ $suppliers->links() }}
  </div>
</div>
@endsection

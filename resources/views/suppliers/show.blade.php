@extends('layouts.app')
@section('title', $supplier->company_name)
@section('content')
@php($cur = config('procurement.currency'))
<section class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div>
    <div class="eyebrow">PROCUREMENT / SUPPLIERS</div>
    <h1 class="h3 fw-bold mb-1">{{ $supplier->company_name }} @if($supplier->is_active)<span class="badge text-bg-success fs-6 align-middle">Active</span>@else<span class="badge text-bg-secondary fs-6 align-middle">Inactive</span>@endif</h1>
    <p class="text-muted mb-0">{{ $supplier->full_contact }}</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('suppliers.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back</a>
    @can('manage-suppliers')<a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-emerald"><i class="bi bi-pencil-square me-1"></i>Edit</a>@endcan
  </div>
</section>

<div class="row g-3 mb-4">
  <div class="col-lg-5">
    <div class="panel-card card h-100">
      <div class="card-header"><div><h2 class="panel-title">Supplier details</h2></div><span class="panel-icon"><i class="bi bi-building"></i></span></div>
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted">Contact person</dt><dd class="col-7">{{ $supplier->contact_person }}</dd>
          <dt class="col-5 text-muted">E-mail</dt><dd class="col-7">{{ $supplier->email }}</dd>
          <dt class="col-5 text-muted">Phone</dt><dd class="col-7">{{ $supplier->phone }}</dd>
          <dt class="col-5 text-muted">Address</dt><dd class="col-7">{{ $supplier->address }}</dd>
          <dt class="col-5 text-muted">Payment terms</dt><dd class="col-7">{{ $supplier->payment_terms }}</dd>
          <dt class="col-5 text-muted">Lead time</dt><dd class="col-7">{{ $supplier->lead_time_days }} days</dd>
          <dt class="col-5 text-muted">Minimum order</dt><dd class="col-7">{{ $cur }}{{ number_format($supplier->minimum_order_amount, 2) }}</dd>
          @if($supplier->notes)<dt class="col-5 text-muted">Notes</dt><dd class="col-7">{{ $supplier->notes }}</dd>@endif
        </dl>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="panel-card card h-100">
      <div class="card-header"><div><h2 class="panel-title">Performance metrics</h2><p class="panel-subtitle">Based on delivered purchase orders</p></div><span class="panel-icon"><i class="bi bi-speedometer2"></i></span></div>
      <div class="card-body">
        <div class="row g-3 text-center">
          <div class="col-4"><div class="stat-number">{{ $metrics['on_time_rate'] !== null ? $metrics['on_time_rate'].'%' : 'N/A' }}</div><div class="stat-foot">on-time delivery</div></div>
          <div class="col-4"><div class="stat-number">{{ $metrics['order_accuracy'] !== null ? $metrics['order_accuracy'].'%' : 'N/A' }}</div><div class="stat-foot">order accuracy</div></div>
          <div class="col-4"><div class="stat-number">{{ $metrics['total_orders'] }}</div><div class="stat-foot">orders placed</div></div>
          <div class="col-4"><div class="stat-number">{{ $metrics['avg_actual_days'] ?? 'N/A' }}</div><div class="stat-foot">avg. actual days</div></div>
          <div class="col-4"><div class="stat-number">{{ $metrics['avg_quoted_days'] ?? 'N/A' }}</div><div class="stat-foot">avg. quoted days</div></div>
          <div class="col-4"><div class="stat-number">{{ $cur }}{{ number_format($metrics['total_value']) }}</div><div class="stat-foot">total order value</div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="panel-card card mb-4">
  <div class="card-header"><div><h2 class="panel-title">Recent purchase orders</h2></div>@can('create', \App\Models\PurchaseOrder::class)<a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-outline-emerald">New order</a>@endcan</div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0 inventory-table">
    <thead><tr><th>Reference</th><th>Order date</th><th>Expected</th><th>Status</th><th class="text-end">Total</th></tr></thead>
    <tbody>
    @forelse($recentOrders as $order)
      <tr><td><a href="{{ route('purchase-orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->reference_number }}</a></td><td>{{ $order->order_date->format('M d, Y') }}</td><td>{{ optional($order->expected_delivery_date)->format('M d, Y') ?? '—' }}</td><td><span class="badge {{ $order->status_badge }}">{{ $order->status_label }}</span></td><td class="text-end">{{ $cur }}{{ number_format($order->total, 2) }}</td></tr>
    @empty
      <tr><td colspan="5" class="empty-state">No purchase orders for this supplier yet.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>

<div class="panel-card card mb-4">
  <div class="card-header"><div><h2 class="panel-title">Products supplied</h2><p class="panel-subtitle">Set a product's supplier from the dashboard (Add / Edit record).</p></div></div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0 inventory-table">
    <thead><tr><th>SKU</th><th>Product</th><th>On hand</th><th>Reorder level</th><th>Cost price</th><th>Pack size</th><th>Min. order qty</th></tr></thead>
    <tbody>
    @forelse($products as $product)
      <tr><td class="item-sku">{{ $product->sku }}</td><td class="item-name">{{ $product->name }}</td><td>{{ $product->quantity }}@if($product->isLowStock()) <span class="low-stock-dot" title="Low stock"></span>@endif</td><td>{{ $product->reorder_level }}</td><td>{{ $cur }}{{ number_format($product->cost_price, 2) }}</td><td>{{ $product->pack_size }}</td><td>{{ $product->min_order_quantity }}</td></tr>
    @empty
      <tr><td colspan="7" class="empty-state">No products are linked to this supplier.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection

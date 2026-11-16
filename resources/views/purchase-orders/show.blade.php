@extends('layouts.app')
@section('title', $order->reference_number)
@section('content')
@php($cur = config('procurement.currency'))
<section class="page-heading d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <div class="eyebrow">PROCUREMENT / PURCHASE ORDERS</div>
    <h1 class="h3 fw-bold mb-1">{{ $order->reference_number }} <span class="badge {{ $order->status_badge }} fs-6 align-middle">{{ $order->status_label }}</span>@if($order->isOverdue()) <span class="badge text-bg-danger fs-6 align-middle">Overdue</span>@endif</h1>
    <p class="text-muted mb-0">
      @if($order->supplier && !$order->supplier->trashed())<a href="{{ route('suppliers.show', $order->supplier) }}">{{ $order->supplier->company_name }}</a>
      @else{{ optional($order->supplier)->company_name }} @if($order->supplier)<span class="badge text-bg-dark">supplier deleted</span>@endif @endif
      · created by {{ optional($order->createdBy)->name ?? 'System (reorder check)' }}
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back</a>
    <a href="{{ route('purchase-orders.pdf', $order) }}" class="btn btn-outline-emerald"><i class="bi bi-filetype-pdf me-1"></i>PDF</a>
    @can('update', $order)<a href="{{ route('purchase-orders.edit', $order) }}" class="btn btn-outline-emerald"><i class="bi bi-pencil-square me-1"></i>Edit</a>@endcan
    @can('submit', $order)
      <form action="{{ route('purchase-orders.submit', $order) }}" method="POST">@csrf<button class="btn btn-emerald" type="submit"><i class="bi bi-send me-1"></i>Submit for approval</button></form>
    @endcan
    @can('send', $order)
      <form action="{{ route('purchase-orders.send', $order) }}" method="POST">@csrf<button class="btn btn-emerald" type="submit"><i class="bi bi-envelope me-1"></i>Send to supplier</button></form>
    @endcan
    @can('delete', $order)
      <form action="{{ route('purchase-orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Delete this draft?');">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash3 me-1"></i>Delete draft</button></form>
    @endcan
  </div>
</section>

@if($order->canBeApproved())
  @can('approve', $order)
  <div class="panel-card card mb-4 border-warning"><div class="card-body">
    <h2 class="panel-title">This order is waiting for your decision</h2>
    <div class="row g-3">
      <div class="col-lg-6">
        <form action="{{ route('purchase-orders.approve', $order) }}" method="POST" class="d-flex gap-2">@csrf
          <input name="comment" class="form-control" placeholder="Optional comment" maxlength="500">
          <button class="btn btn-success text-nowrap" type="submit"><i class="bi bi-check2-circle me-1"></i>Approve</button>
        </form>
      </div>
      <div class="col-lg-6">
        <form action="{{ route('purchase-orders.reject', $order) }}" method="POST" class="d-flex gap-2">@csrf
          <input name="rejection_reason" class="form-control" placeholder="Reason for rejection (required)" maxlength="1000" required>
          <button class="btn btn-outline-danger text-nowrap" type="submit"><i class="bi bi-x-circle me-1"></i>Reject</button>
        </form>
      </div>
    </div>
  </div></div>
  @else
  <div class="alert alert-warning"><i class="bi bi-hourglass-split me-1"></i>Pending approval.
    @if($order->requiresAdminApproval()) Orders of {{ $cur }}{{ number_format(config('procurement.admin_approval_threshold')) }} or more need an administrator.@endif
  </div>
  @endcan
@endif

@if($order->status === 'rejected')
  <div class="alert alert-danger"><strong>Rejected:</strong> {{ $order->rejection_reason }}</div>
@endif

<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="panel-card card h-100">
      <div class="card-header"><div><h2 class="panel-title">Order information</h2></div><span class="panel-icon"><i class="bi bi-info-circle"></i></span></div>
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-sm-4 text-muted">Order date</dt><dd class="col-sm-8">{{ $order->order_date->format('M d, Y') }}</dd>
          <dt class="col-sm-4 text-muted">Expected delivery</dt><dd class="col-sm-8">{{ optional($order->expected_delivery_date)->format('M d, Y') ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Delivered</dt><dd class="col-sm-8">{{ optional($order->delivered_at)->format('M d, Y H:i') ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Approved by</dt><dd class="col-sm-8">{{ $order->approvedBy ? $order->approvedBy->name . ' · ' . optional($order->approved_at)->format('M d, Y H:i') : '—' }}</dd>
          <dt class="col-sm-4 text-muted">Terms</dt><dd class="col-sm-8">{!! nl2br(e($order->terms ?: '—')) !!}</dd>
          <dt class="col-sm-4 text-muted">Notes</dt><dd class="col-sm-8">{!! nl2br(e($order->notes ?: '—')) !!}</dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="panel-card card h-100">
      <div class="card-header"><div><h2 class="panel-title">Amounts</h2></div><span class="panel-icon"><i class="bi bi-cash-coin"></i></span></div>
      <div class="card-body">
        <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span>{{ $cur }}{{ number_format($order->subtotal, 2) }}</span></div>
        <div class="d-flex justify-content-between"><span class="text-muted">VAT</span><span>{{ $cur }}{{ number_format($order->tax_amount, 2) }}</span></div>
        <hr class="my-2">
        <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span>{{ $cur }}{{ number_format($order->total, 2) }}</span></div>
        @if($order->requiresAdminApproval())<div class="small text-muted mt-2"><i class="bi bi-shield-lock me-1"></i>Requires administrator approval.</div>@endif
      </div>
    </div>
  </div>
</div>

<div class="panel-card card mb-4">
  <div class="card-header"><div><h2 class="panel-title">Line items</h2></div></div>
  @can('receive', $order)
  <form action="{{ route('purchase-orders.receive', $order) }}" method="POST">@csrf
  @endcan
  <div class="table-responsive"><table class="table table-hover align-middle mb-0 inventory-table">
    <thead><tr><th>SKU</th><th>Product</th><th class="text-end">Ordered</th><th class="text-end">Received</th><th class="text-end">Outstanding</th><th class="text-end">Unit price</th><th class="text-end">Line total</th>@can('receive', $order)<th style="width:130px">Receive now</th>@endcan</tr></thead>
    <tbody>
    @foreach($order->items as $item)
      <tr>
        <td class="item-sku">{{ $item->sku }}</td><td class="item-name">{{ $item->product_name }}</td>
        <td class="text-end">{{ $item->quantity }}</td><td class="text-end">{{ $item->received_quantity }}</td><td class="text-end">{{ $item->outstanding_quantity }}</td>
        <td class="text-end">{{ $cur }}{{ number_format($item->unit_price, 2) }}</td><td class="text-end">{{ $cur }}{{ number_format($item->line_total, 2) }}</td>
        @can('receive', $order)
        <td><input type="number" min="0" max="{{ $item->outstanding_quantity }}" name="received[{{ $item->id }}]" value="{{ old('received.' . $item->id, 0) }}" class="form-control form-control-sm" {{ $item->outstanding_quantity === 0 ? 'disabled' : '' }}></td>
        @endcan
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @can('receive', $order)
  <div class="card-body border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="form-check"><input type="hidden" name="close_short" value="0"><input class="form-check-input" type="checkbox" name="close_short" value="1" id="close_short"><label class="form-check-label small" for="close_short">Close the order even if some items are short (supplier will not deliver the rest)</label></div>
    <button class="btn btn-emerald" type="submit"><i class="bi bi-box-arrow-in-down me-1"></i>Record goods received</button>
  </div>
  </form>
  @endcan
</div>

<div class="panel-card card mb-4">
  <div class="card-header"><div><h2 class="panel-title">Approval history</h2><p class="panel-subtitle">Every status change is recorded.</p></div><span class="panel-icon"><i class="bi bi-clock-history"></i></span></div>
  <div class="table-responsive"><table class="table align-middle mb-0 inventory-table">
    <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Change</th><th>Comment</th></tr></thead>
    <tbody>
    @forelse($order->activityLogs as $log)
      <tr><td class="text-nowrap">{{ $log->created_at->format('M d, Y H:i') }}</td><td>{{ $log->actor_name }}</td><td><span class="badge text-bg-light border text-uppercase">{{ $log->action }}</span></td><td>{{ $log->from_status ? ucfirst($log->from_status) . ' → ' : '' }}{{ $log->to_status ? ucfirst($log->to_status) : '' }}</td><td>{{ $log->comment }}</td></tr>
    @empty
      <tr><td colspan="5" class="empty-state">No history yet.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection

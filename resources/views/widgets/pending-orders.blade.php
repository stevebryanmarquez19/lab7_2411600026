{{-- Data ($pendingCount, $pendingValue, $needsAdminCount) is supplied by the View Composer in ViewServiceProvider. --}}
<div class="panel-card card h-100">
  <div class="card-header"><div><h2 class="panel-title">Pending approval</h2><p class="panel-subtitle">Orders waiting for a manager</p></div><span class="panel-icon"><i class="bi bi-hourglass-split"></i></span></div>
  <div class="card-body">
    <div class="stat-number" id="wPendingCount">{{ $pendingCount }}</div>
    <div class="stat-foot" id="wPendingValue">{{ config('procurement.currency') }}{{ number_format($pendingValue, 2) }} total value</div>
    <div class="stat-foot" id="wPendingAdmin">{{ $needsAdminCount }} need administrator approval</div>
    <a href="{{ route('purchase-orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-emerald mt-3">Review orders</a>
  </div>
</div>

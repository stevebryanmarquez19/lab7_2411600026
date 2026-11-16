{{-- $topSuppliers (array) comes from the View Composer. --}}
<div class="panel-card card h-100">
  <div class="card-header"><div><h2 class="panel-title">Top suppliers</h2><p class="panel-subtitle">By on-time delivery</p></div><span class="panel-icon"><i class="bi bi-trophy"></i></span></div>
  <ul class="list-group list-group-flush" id="wPerfList">
    @forelse($topSuppliers as $s)
      <li class="list-group-item d-flex justify-content-between align-items-center"><span>{{ $s['name'] }}</span><span class="badge text-bg-success">{{ $s['on_time_rate'] }}%</span></li>
    @empty
      <li class="list-group-item text-muted">No completed deliveries yet.</li>
    @endforelse
  </ul>
</div>

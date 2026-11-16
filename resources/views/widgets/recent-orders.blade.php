{{-- $recentOrders (array) comes from the View Composer. --}}
<div class="panel-card card h-100">
  <div class="card-header"><div><h2 class="panel-title">Recent purchase orders</h2><p class="panel-subtitle">Latest 5</p></div><span class="panel-icon"><i class="bi bi-file-earmark-text"></i></span></div>
  <div class="table-responsive"><table class="table align-middle mb-0 inventory-table">
    <tbody id="wRecentBody">
    @forelse($recentOrders as $o)
      <tr><td><a href="{{ $o['url'] }}" class="fw-semibold text-decoration-none">{{ $o['reference'] }}</a><div class="item-sku">{{ $o['supplier'] }}</div></td><td><span class="badge {{ $o['status_badge'] }}">{{ $o['status_label'] }}</span></td><td class="text-end">{{ $o['total_formatted'] }}</td></tr>
    @empty
      <tr><td class="empty-state">No purchase orders yet.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>

{{-- $reorderAlerts (array) comes from the View Composer. --}}
<div class="panel-card card h-100">
  <div class="card-header">
    <div><h2 class="panel-title">Reorder alerts <span class="badge text-bg-warning" id="wReorderCount">{{ count($reorderAlerts) }}</span></h2><p class="panel-subtitle">At or below the calculated reorder point</p></div>
    @can('generate-orders')
    <form action="{{ route('purchase-orders.generate') }}" method="POST">@csrf<button class="btn btn-sm btn-emerald" type="submit"><i class="bi bi-magic me-1"></i>Generate drafts</button></form>
    @endcan
  </div>
  <div class="table-responsive"><table class="table align-middle mb-0 inventory-table">
    <thead><tr><th>Product</th><th class="text-end">On hand</th><th class="text-end">Reorder pt.</th><th class="text-end">Suggested</th><th>Supplier</th></tr></thead>
    <tbody id="wReorderBody">
    @forelse($reorderAlerts as $a)
      <tr><td class="item-name">{{ $a['name'] }}<div class="item-sku">{{ $a['sku'] }}</div></td><td class="text-end">{{ $a['quantity'] }}</td><td class="text-end">{{ $a['reorder_point'] }}</td><td class="text-end">{{ $a['suggested_quantity'] }}</td><td>{{ $a['supplier'] }}</td></tr>
    @empty
      <tr><td colspan="5" class="empty-state">Nothing needs reordering right now.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>

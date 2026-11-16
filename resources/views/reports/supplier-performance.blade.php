@extends('layouts.app')
@section('title', 'Supplier Performance')
@section('content')
@php($cur = config('procurement.currency'))
<section class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div>
    <div class="eyebrow">PROCUREMENT / REPORTS</div>
    <h1 class="h3 fw-bold mb-1">Supplier Performance</h1>
    <p class="text-muted mb-0">On-time delivery, quoted vs. actual lead time and order accuracy for completed orders.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-emerald" href="{{ route('exports.download', ['type' => 'supplier-performance', 'format' => 'csv']) }}"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
    <a class="btn btn-outline-emerald" href="{{ route('exports.download', ['type' => 'supplier-performance', 'format' => 'pdf']) }}"><i class="bi bi-filetype-pdf me-1"></i>PDF</a>
  </div>
</section>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="panel-card card h-100"><div class="card-header"><div><h2 class="panel-title">Top performing</h2><p class="panel-subtitle">Highest on-time rate</p></div><span class="panel-icon"><i class="bi bi-trophy"></i></span></div>
      <ul class="list-group list-group-flush">
        @forelse($top as $i => $row)
          <li class="list-group-item d-flex justify-content-between align-items-center"><span><strong>{{ $i + 1 }}.</strong> {{ $row['name'] }}</span><span class="badge text-bg-success">{{ $row['on_time_rate'] }}% on time</span></li>
        @empty <li class="list-group-item text-muted">No completed deliveries yet.</li> @endforelse
      </ul></div>
  </div>
  <div class="col-md-6">
    <div class="panel-card card h-100"><div class="card-header"><div><h2 class="panel-title">Needs attention</h2><p class="panel-subtitle">Lowest on-time rate</p></div><span class="panel-icon"><i class="bi bi-exclamation-triangle"></i></span></div>
      <ul class="list-group list-group-flush">
        @forelse($bottom as $i => $row)
          <li class="list-group-item d-flex justify-content-between align-items-center"><span>{{ $row['name'] }}</span><span class="badge text-bg-danger">{{ $row['on_time_rate'] }}% on time</span></li>
        @empty <li class="list-group-item text-muted">No completed deliveries yet.</li> @endforelse
      </ul></div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-7"><div class="panel-card card h-100"><div class="card-header"><div><h2 class="panel-title">Delivery trend</h2><p class="panel-subtitle">On-time vs. late deliveries per month</p></div></div><div class="card-body"><canvas id="trendChart" height="150"></canvas></div></div></div>
  <div class="col-lg-5"><div class="panel-card card h-100"><div class="card-header"><div><h2 class="panel-title">Quoted vs. actual delivery days</h2></div></div><div class="card-body"><canvas id="daysChart" height="200"></canvas></div></div></div>
</div>

<div class="panel-card card mb-4">
  <div class="card-header"><div><h2 class="panel-title">All suppliers</h2></div></div>
  <div class="table-responsive"><table class="table table-hover align-middle mb-0 inventory-table">
    <thead><tr><th>Supplier</th><th class="text-end">Orders placed</th><th class="text-end">Delivered</th><th class="text-end">On time</th><th class="text-end">On-time %</th><th class="text-end">Avg actual days</th><th class="text-end">Avg quoted days</th><th class="text-end">Accuracy %</th><th class="text-end">Total value</th></tr></thead>
    <tbody>
    @foreach($rows as $r)
      <tr><td class="item-name">{{ $r['name'] }} @unless($r['is_active'])<span class="badge text-bg-secondary">inactive</span>@endunless</td>
        <td class="text-end">{{ $r['total_orders'] }}</td><td class="text-end">{{ $r['delivered_orders'] }}</td><td class="text-end">{{ $r['on_time_orders'] }}</td>
        <td class="text-end">{{ $r['on_time_rate'] !== null ? $r['on_time_rate'] . '%' : 'N/A' }}</td>
        <td class="text-end">{{ $r['avg_actual_days'] ?? 'N/A' }}</td><td class="text-end">{{ $r['avg_quoted_days'] ?? 'N/A' }}</td>
        <td class="text-end">{{ $r['order_accuracy'] !== null ? $r['order_accuracy'] . '%' : 'N/A' }}</td>
        <td class="text-end">{{ $cur }}{{ number_format($r['total_value'], 2) }}</td></tr>
    @endforeach
    </tbody>
  </table></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  const trend = @json($trend);
  const rows = @json(array_values(array_filter($rows, function ($r) { return $r['delivered_orders'] > 0; })));
  new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: { labels: trend.labels, datasets: [
      { label: 'On time', data: trend.on_time, backgroundColor: '#1a936f' },
      { label: 'Late', data: trend.late, backgroundColor: '#e5484d' },
    ]},
    options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } } }
  });
  new Chart(document.getElementById('daysChart'), {
    type: 'bar',
    data: { labels: rows.map(r => r.name), datasets: [
      { label: 'Quoted', data: rows.map(r => r.avg_quoted_days), backgroundColor: '#d4ece4' },
      { label: 'Actual', data: rows.map(r => r.avg_actual_days), backgroundColor: '#0d5c3e' },
    ]},
    options: { responsive: true, indexAxis: 'y', scales: { x: { beginAtZero: true } } }
  });
</script>
@endpush

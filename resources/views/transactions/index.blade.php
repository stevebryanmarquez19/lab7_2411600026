<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FitPulse | Transaction History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark navbar-emerald"><div class="container"><a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">FitPulse Inventory</a><a class="btn btn-outline-light btn-sm" href="{{ route('dashboard') }}">Back to Dashboard</a></div></nav>
<main class="container py-4">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h1 class="h3 fw-bold mb-1">Inventory Transaction History</h1><p class="text-muted mb-0">Review stock-in, stock-out, adjustments, and opening stock records.</p></div></div>
  <div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <x-filter-form :action="route('transactions.index')" :fields="[
        ['name' => 'search', 'label' => 'Product / SKU', 'type' => 'text', 'col' => 'col-lg-3 col-md-6'],
        ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'col' => 'col-lg-2 col-md-6', 'placeholder' => 'All types', 'options' => ['opening' => 'Opening stock', 'in' => 'Stock in', 'out' => 'Stock out', 'adjustment' => 'Adjustment']],
        ['name' => 'user', 'label' => 'Recorded by', 'type' => 'text', 'col' => 'col-lg-2 col-md-6'],
        ['name' => 'date_from', 'label' => 'From', 'type' => 'date', 'col' => 'col-lg-2 col-md-6'],
        ['name' => 'date_to', 'label' => 'To', 'type' => 'date', 'col' => 'col-lg-2 col-md-6'],
    ]">
      @can('export-data')
      <a class="btn btn-outline-success btn-sm" href="{{ route('exports.download', ['type' => 'transactions', 'format' => 'csv'] + request()->query()) }}">CSV</a>
      <a class="btn btn-outline-success btn-sm" href="{{ route('exports.download', ['type' => 'transactions', 'format' => 'pdf'] + request()->query()) }}">PDF</a>
      @endcan
    </x-filter-form>
  </div></div>
  <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Date</th><th>Product</th><th>Type</th><th>Quantity / Count</th><th>Before → After</th><th>Recorded by</th><th></th></tr></thead><tbody>
  @forelse($transactions as $tx)<tr><td class="text-nowrap">{{ optional($tx->created_at)->format('M d, Y H:i') }}</td><td>{{ optional($tx->product)->name ?? 'Deleted product' }}<div class="small text-muted">{{ optional($tx->product)->sku }}</div></td><td><span class="badge text-bg-{{ $tx->type === 'in' ? 'success' : ($tx->type === 'out' ? 'warning' : 'secondary') }}">{{ strtoupper($tx->type) }}</span></td><td>{{ number_format($tx->quantity) }}{{ $tx->type === 'adjustment' ? ' (new count)' : '' }}</td><td>{{ number_format($tx->quantity_before) }} → {{ number_format($tx->quantity_after) }}</td><td>{{ $tx->actor_name ?: 'System' }}</td><td><a class="btn btn-sm btn-outline-success" href="{{ route('transactions.show', $tx) }}">Details</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">No transactions found.</td></tr>@endforelse
  </tbody></table></div><div class="card-footer bg-white">{{ $transactions->links() }}</div></div>
</main>
</body></html>

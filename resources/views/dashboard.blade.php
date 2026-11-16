<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FitPulse | Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-emerald sticky-top">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}"><span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span> FitPulse <span class="brand-sub d-none d-sm-inline">Inventory</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="topNav">
      <div class="ms-auto d-flex align-items-center gap-3 py-2 py-lg-0">
        <a href="{{ route('notifications.index') }}" class="btn btn-outline-light btn-sm position-relative" title="Notifications"><i class="bi bi-bell"></i> Notifications @if(($unreadNotificationCount ?? 0) > 0)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-warning">{{ $unreadNotificationCount }}</span>@endif</a>
        <span class="text-white-50 small"><i class="bi bi-person-circle me-1"></i>{{ session('fitpulse_user', 'Admin') }}</span>
        <form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-outline-light btn-sm px-3" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Logout</button></form>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <aside class="col-md-3 col-xl-2 sidebar p-3">
      <div class="sidebar-label">WORKSPACE</div>
      <nav class="nav flex-column gap-1">
        <a class="nav-link active" href="#overview"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a class="nav-link" href="#inventory"><i class="bi bi-box-seam"></i> Inventory</a>
        <a class="nav-link" href="#analytics"><i class="bi bi-bar-chart-line"></i> Analytics</a>
        <a class="nav-link" href="#inventory"><i class="bi bi-funnel"></i> Search & Filters</a>
        <a class="nav-link" href="{{ route('transactions.index') }}"><i class="bi bi-clock-history"></i> Transaction History</a>
        <a class="nav-link" href="{{ route('suppliers.index') }}"><i class="bi bi-truck"></i> Suppliers</a>
        <a class="nav-link" href="{{ route('purchase-orders.index') }}"><i class="bi bi-file-earmark-text"></i> Purchase Orders</a>
        @can('view-reports')<a class="nav-link" href="{{ route('reports.supplier-performance') }}"><i class="bi bi-bar-chart-line"></i> Supplier Performance</a>@endcan
        <a class="nav-link" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Notifications</a>
      </nav>
      <div class="sidebar-note mt-4">
        <div class="note-icon"><i class="bi bi-shield-check"></i></div>
        <div class="fw-semibold small mt-2">Inventory overview</div>
        <p class="small text-muted mb-0">Keep track of your items and low-stock records.</p>
      </div>
      <div class="sidebar-footer small text-muted"><i class="bi bi-circle-fill me-1"></i> System ready</div>
    </aside>

    <main class="col-md-9 col-xl-10 main-content px-3 px-lg-4 py-4">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Please check the form:</strong> {{ $errors->first() }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      <section id="overview" class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
          <div class="eyebrow">FITPULSE / OVERVIEW</div>
          <h1 class="h3 fw-bold mb-1" id="greeting">Good day, {{ session('fitpulse_user', 'Admin') }}!</h1>
          <p class="text-muted mb-0">Monitor inventory, activity, and stock levels in one place.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-emerald" data-bs-toggle="modal" data-bs-target="#stockMovementModal"><i class="bi bi-arrow-left-right me-1"></i> Stock Movement</button>
          <button class="btn btn-emerald" data-bs-toggle="modal" data-bs-target="#addRecordModal"><i class="bi bi-plus-lg me-1"></i> Add Record</button>
          <button class="btn btn-outline-emerald" id="exportCsvBtn"><i class="bi bi-download me-1"></i> Export CSV</button>
        </div>
      </section>

      <section class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card card h-100"><div class="card-body"><div class="stat-top"><span class="stat-icon icon-green"><i class="bi bi-fire"></i></span><span class="stat-caption">CALCULATED VALUE</span></div><div class="stat-number">{{ number_format($totalKcal) }}</div><div class="stat-foot">kcal estimate</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card card h-100"><div class="card-body"><div class="stat-top"><span class="stat-icon icon-gold"><i class="bi bi-boxes"></i></span><span class="stat-caption">TOTAL QUANTITY</span></div><div class="stat-number" id="totalQuantityStat">{{ number_format($totalQuantity) }}</div><div class="stat-foot">units in inventory</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card card h-100"><div class="card-body"><div class="stat-top"><span class="stat-icon icon-blue"><i class="bi bi-clock-history"></i></span><span class="stat-caption">TOTAL DURATION</span></div><div class="stat-number">{{ number_format($totalDuration) }}</div><div class="stat-foot">minutes recorded</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card card h-100"><div class="card-body"><div class="stat-top"><span class="stat-icon icon-purple"><i class="bi bi-check2-circle"></i></span><span class="stat-caption">COMPLETED</span></div><div class="stat-number">{{ number_format($completedCount) }}</div><div class="stat-foot"><span id="lowStockStat">{{ $lowStockCount }}</span> low-stock item(s)</div></div></div></div>
      </section>

      @if($lowStockCount > 0)
        <div class="alert stock-alert d-flex align-items-center gap-2 mb-4"><i class="bi bi-exclamation-triangle-fill fs-5"></i><div><strong>Stock needs attention.</strong> {{ $lowStockCount }} item(s) are at or below their reorder level.</div><a href="#inventory" class="alert-link ms-auto text-nowrap">Review items <i class="bi bi-arrow-down"></i></a></div>
      @endif

      <section id="analytics" class="row g-3 mb-4">
        <div class="col-lg-8"><div class="panel-card card h-100"><div class="card-header"><div><h2 class="panel-title">Inventory Activity Trend</h2><p class="panel-subtitle">Quantity recorded by date</p></div><span class="panel-icon"><i class="bi bi-graph-up-arrow"></i></span></div><div class="card-body"><div class="chart-wrap"><canvas id="stepTrendChart"></canvas></div></div></div></div>
        <div class="col-lg-4"><div class="panel-card card h-100"><div class="card-header"><div><h2 class="panel-title">Category Distribution</h2><p class="panel-subtitle">Quantity by category</p></div><span class="panel-icon"><i class="bi bi-pie-chart"></i></span></div><div class="card-body"><div class="chart-wrap chart-doughnut"><canvas id="workoutTypeChart"></canvas></div></div></div></div>
      </section>

      <section id="procurement" class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h5 fw-bold mb-0"><i class="bi bi-truck me-1"></i> Procurement overview</h2><a href="{{ route('purchase-orders.index') }}" class="small text-decoration-none">All purchase orders →</a></div>
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">@include('widgets.pending-orders')</div>
          <div class="col-lg-5 col-md-6">@include('widgets.recent-orders')</div>
          <div class="col-lg-4 col-md-12">@include('widgets.supplier-performance')</div>
          <div class="col-12">@include('widgets.reorder-alerts')</div>
        </div>
      </section>

      <section id="inventory" class="panel-card card mb-4">
        <div class="card-header flex-wrap gap-2"><div><h2 class="panel-title">Inventory Records</h2><p class="panel-subtitle mb-0">Add, update, search, and manage your records.</p></div><span class="record-count"><span id="visibleCount">{{ $products->count() }}</span> records</span></div>
        <div class="card-body border-bottom">
          <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6"><label for="searchInput" class="form-label small fw-semibold">Search item / SKU</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input id="searchInput" type="search" class="form-control" placeholder="Search inventory..."></div></div>
            <div class="col-lg-2 col-md-6"><label for="categoryFilter" class="form-label small fw-semibold">Category</label><select id="categoryFilter" class="form-select"><option value="All">All categories</option><option>Cardio</option><option>Strength</option><option>Sports</option><option>Flexibility</option><option>Recovery</option></select></div>
            <div class="col-lg-2 col-md-6"><label for="statusFilter" class="form-label small fw-semibold">Status</label><select id="statusFilter" class="form-select"><option value="All">All statuses</option><option>Completed</option><option>Incomplete</option></select></div>
            <div class="col-lg-3 col-md-6"><label class="form-label small fw-semibold">Duration (minutes)</label><div class="input-group"><input type="number" min="0" id="minPriceInput" class="form-control" placeholder="Min"><span class="input-group-text">to</span><input type="number" min="0" id="maxPriceInput" class="form-control" placeholder="Max"></div></div>
            <div class="col-lg-2"><button id="resetFiltersBtn" class="btn btn-light border w-100" type="button"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</button></div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 inventory-table">
            <thead><tr><th>Date</th><th>Item / SKU</th><th>Category</th><th>Duration</th><th>Quantity</th><th>Value</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="activityTableBody">
              @forelse($products as $item)
              <tr data-name="{{ strtolower($item->name) }}" data-sku="{{ strtolower($item->sku) }}" data-category="{{ $item->category }}" data-status="{{ $item->status }}" data-duration="{{ $item->unit_price }}" data-quantity="{{ $item->quantity }}" data-date="{{ $item->date }}" data-id="{{ $item->id }}">
                <td class="text-nowrap">{{ $item->date }}</td>
                <td><div class="item-name">{{ $item->name }}</div><div class="item-sku">{{ $item->sku }}</div></td>
                <td><span class="category-pill">{{ $item->category }}</span></td>
                <td>{{ $item->unit_price }} <span class="text-muted small">min</span></td>
                <td><span class="fw-semibold">{{ $item->quantity }}</span>@if($item->quantity <= $item->reorder_level) <span class="low-stock-dot" title="Low stock"></span>@endif</td>
                <td class="fw-semibold">{{ number_format($item->quantity * $item->unit_price * 10) }} <span class="text-muted small">kcal</span></td>
                <td><span class="status-pill {{ $item->status === 'Completed' ? 'status-completed' : 'status-incomplete' }}">{{ $item->status }}</span></td>
                <td class="text-end text-nowrap">
                  <button type="button" class="btn btn-sm action-btn edit-btn" title="Edit record" data-bs-toggle="modal" data-bs-target="#editRecordModal"
                    data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-category="{{ $item->category }}" data-duration="{{ $item->unit_price }}" data-quantity="{{ $item->quantity }}" data-reorder="{{ $item->reorder_level }}" data-status="{{ $item->status }}" data-date="{{ $item->date }}" data-supplier="{{ $item->supplier_id }}" data-cost="{{ $item->cost_price }}" data-pack="{{ $item->pack_size }}" data-minqty="{{ $item->min_order_quantity }}"><i class="bi bi-pencil-square"></i></button>
                  <form action="{{ route('products.destroy', $item) }}" method="POST" class="d-inline delete-form">@csrf @method('DELETE')<button type="submit" class="btn btn-sm action-btn delete-btn" title="Delete record"><i class="bi bi-trash3"></i></button></form>
                </td>
              </tr>
              @empty
              <tr id="emptyRow"><td colspan="8" class="empty-state"><i class="bi bi-inbox"></i><div class="fw-semibold mt-2">No inventory records yet</div><div class="small">Add your first record to get started.</div></td></tr>
              @endforelse
              <tr id="noResultsRow" class="d-none"><td colspan="8" class="empty-state"><i class="bi bi-search"></i><div class="fw-semibold mt-2">No matching records</div><div class="small">Try changing your search or filters.</div></td></tr>
            </tbody>
          </table>
        </div>
        <div class="table-footer"><span>FitPulse Inventory Management</span><span><i class="bi bi-database-check me-1"></i>Saved to database</span></div>
      </section>
      <section id="movements" class="panel-card card mb-4">
        <div class="card-header flex-wrap gap-2"><div><h2 class="panel-title">Recent Stock Movements</h2><p class="panel-subtitle mb-0">Every stock-in, stock-out, and adjustment is recorded for auditing.</p></div><div class="d-flex align-items-center gap-3"><a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-success">View history</a><span class="record-count"><i class="bi bi-arrow-repeat me-1"></i><span id="lastUpdatedAt">Updating every 30 seconds</span></span></div></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0 inventory-table"><thead><tr><th>Time</th><th>Item</th><th>Movement</th><th>Change</th><th>Before → After</th><th>By</th></tr></thead><tbody id="recentTransactionsBody">
          @forelse($transactions as $tx)
          <tr><td class="text-nowrap">{{ optional($tx->created_at)->format('M d, H:i') }}</td><td><div class="item-name">{{ optional($tx->product)->name ?? 'Deleted item' }}</div><div class="item-sku">{{ optional($tx->product)->sku }}</div></td><td><span class="badge {{ $tx->type === 'out' ? 'text-bg-warning' : ($tx->type === 'in' ? 'text-bg-success' : ($tx->type === 'adjustment' ? 'text-bg-primary' : 'text-bg-secondary')) }}">{{ strtoupper($tx->type) }}</span></td><td>{{ $tx->type === 'out' ? '−' . number_format($tx->quantity) : ($tx->type === 'adjustment' ? 'Set to ' . number_format($tx->quantity) : '+' . number_format($tx->quantity)) }}</td><td>{{ number_format($tx->quantity_before) }} → {{ number_format($tx->quantity_after) }}</td><td>{{ $tx->actor_name }}</td></tr>
          @empty
          <tr><td colspan="6" class="empty-state">No stock movements have been recorded yet.</td></tr>
          @endforelse
        </tbody></table></div>
      </section>

      <footer class="text-center small text-muted pb-3">FitPulse Inventory System <span class="mx-1">·</span> Lab 4 inspired dashboard</footer>
    </main>
  </div>
</div>

<div class="modal fade" id="stockMovementModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header modal-emerald"><div><h5 class="modal-title fw-bold">Record Stock Movement</h5><div class="small opacity-75">Stock levels and the audit log update together.</div></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
  <form action="{{ route('transactions.store') }}" method="POST">@csrf
    <div class="modal-body">
      <div class="mb-3"><label class="form-label small fw-semibold" for="movement_product">Item</label><select class="form-select" id="movement_product" name="product_id" required><option value="">Choose an item</option>@foreach($products as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->sku }}) — {{ $item->quantity }} available</option>@endforeach</select></div>
      <div class="row g-3"><div class="col-6"><label class="form-label small fw-semibold" for="movement_type">Movement type</label><select class="form-select" id="movement_type" name="type" required><option value="in">Stock In (received/return)</option><option value="out">Stock Out (sale/damaged)</option><option value="adjustment">Stock Adjustment (set physical count)</option></select></div><div class="col-6"><label class="form-label small fw-semibold" for="movement_quantity" id="movement_quantity_label">Quantity</label><input class="form-control" id="movement_quantity" name="quantity" type="number" min="1" max="1000000" required><div class="form-text" id="movement_quantity_help">Enter the number of units to add or remove.</div></div></div>
      <div class="mt-3"><label class="form-label small fw-semibold" for="movement_reference">Reference (optional)</label><input class="form-control" id="movement_reference" name="reference" maxlength="100" placeholder="e.g. PO-1001 or sale number"></div>
      <div class="mt-3"><label class="form-label small fw-semibold" for="movement_notes">Notes (optional)</label><textarea class="form-control" id="movement_notes" name="notes" rows="2" maxlength="1000" placeholder="Reason for this movement"></textarea><div class="form-text">A reason is required for stock adjustments.</div></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-emerald"><i class="bi bi-check2 me-1"></i> Save movement</button></div>
  </form>
</div></div></div>

<div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header modal-emerald"><div><h5 class="modal-title fw-bold">Add Inventory Record</h5><div class="small opacity-75">Enter the item details below.</div></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
  <form action="{{ route('products.store') }}" method="POST">@csrf
    <div class="modal-body">@include('products.form-fields', ['prefix' => 'add', 'record' => null])</div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-emerald"><i class="bi bi-check2 me-1"></i> Save record</button></div>
  </form>
</div></div></div>

<div class="modal fade" id="editRecordModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header modal-emerald"><div><h5 class="modal-title fw-bold">Edit Inventory Record</h5><div class="small opacity-75">Update the selected item.</div></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
  <form id="editRecordForm" method="POST" action="">@csrf @method('PUT')
    <div class="modal-body">
      <div class="mb-3"><label for="edit_name" class="form-label small fw-semibold">Item name</label><input id="edit_name" name="name" class="form-control" maxlength="255" required></div>
      <div class="mb-3"><label for="edit_category" class="form-label small fw-semibold">Category</label><select id="edit_category" name="category" class="form-select" required><option>Cardio</option><option>Strength</option><option>Sports</option><option>Flexibility</option><option>Recovery</option></select></div>
      <div class="row g-3"><div class="col-6"><label for="edit_unit_price" class="form-label small fw-semibold">Duration (minutes)</label><input id="edit_unit_price" name="unit_price" type="number" min="1" max="1440" class="form-control" required></div><div class="col-6"><label for="edit_quantity" class="form-label small fw-semibold">Quantity</label><input id="edit_quantity" name="quantity" type="number" min="0" class="form-control" readonly required><div class="form-text">Use Stock Movement to change quantity so the change is recorded.</div></div><div class="col-6"><label for="edit_reorder_level" class="form-label small fw-semibold">Reorder level</label><input id="edit_reorder_level" name="reorder_level" type="number" min="1" class="form-control" required></div><div class="col-6"><label for="edit_status" class="form-label small fw-semibold">Status</label><select id="edit_status" name="status" class="form-select"><option>Completed</option><option>Incomplete</option></select></div><div class="col-12"><label for="edit_date" class="form-label small fw-semibold">Date</label><input id="edit_date" name="date" type="date" class="form-control" required></div></div>
      @include('products.supplier-fields', ['prefix' => 'edit'])
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-emerald"><i class="bi bi-check2 me-1"></i> Save changes</button></div>
  </form>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rows = Array.from(document.querySelectorAll('#activityTableBody tr[data-id]'));
  const search = document.getElementById('searchInput');
  const category = document.getElementById('categoryFilter');
  const status = document.getElementById('statusFilter');
  const minDuration = document.getElementById('minPriceInput');
  const maxDuration = document.getElementById('maxPriceInput');
  const noResults = document.getElementById('noResultsRow');

  function filterRows() {
    const q = search.value.trim().toLowerCase();
    const min = minDuration.value === '' ? -Infinity : Number(minDuration.value);
    const max = maxDuration.value === '' ? Infinity : Number(maxDuration.value);
    let shown = 0;
    rows.forEach(row => {
      const matches = (row.dataset.name.includes(q) || row.dataset.sku.toLowerCase().includes(q))
        && (category.value === 'All' || row.dataset.category === category.value)
        && (status.value === 'All' || row.dataset.status === status.value)
        && Number(row.dataset.duration) >= min && Number(row.dataset.duration) <= max;
      row.classList.toggle('d-none', !matches);
      if (matches) shown++;
    });
    document.getElementById('visibleCount').textContent = shown;
    noResults.classList.toggle('d-none', shown !== 0 || rows.length === 0);
  }
  [search, category, status, minDuration, maxDuration].forEach(el => {
    el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', filterRows);
  });
  document.getElementById('resetFiltersBtn').addEventListener('click', () => {
    search.value = ''; category.value = 'All'; status.value = 'All'; minDuration.value = ''; maxDuration.value = ''; filterRows();
  });
  document.getElementById('exportCsvBtn').addEventListener('click', () => {
    const params = new URLSearchParams({
      search: search.value, category: category.value, status: status.value,
      min_duration: minDuration.value, max_duration: maxDuration.value
    });
    window.location.href = @json(route('products.export')) + '?' + params.toString();
  });
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', e => {
      if (!window.confirm('Delete this inventory record? This action cannot be undone.')) e.preventDefault();
    });
  });
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = btn.dataset;
      document.getElementById('editRecordForm').action = @json(url('/products')) + '/' + d.id;
      ['name','category','quantity','status','date'].forEach(key => document.getElementById('edit_' + key).value = d[key]);
      document.getElementById('edit_unit_price').value = d.duration;
      document.getElementById('edit_reorder_level').value = d.reorder;
      document.getElementById('edit_supplier_id').value = d.supplier || '';
      document.getElementById('edit_cost_price').value = d.cost || '';
      document.getElementById('edit_pack_size').value = d.pack || '';
      document.getElementById('edit_min_order_quantity').value = d.minqty || '';
    });
  });

  const movementType = document.getElementById('movement_type');
  const movementQuantity = document.getElementById('movement_quantity');
  const movementQuantityLabel = document.getElementById('movement_quantity_label');
  const movementQuantityHelp = document.getElementById('movement_quantity_help');
  function updateMovementInput() {
    if (movementType.value === 'adjustment') {
      movementQuantityLabel.textContent = 'Physical count (new total)';
      movementQuantityHelp.textContent = 'Enter the quantity you physically counted. Zero is allowed.';
      movementQuantity.min = '0';
    } else {
      movementQuantityLabel.textContent = 'Quantity';
      movementQuantityHelp.textContent = 'Enter the number of units to add or remove.';
      movementQuantity.min = '1';
    }
  }
  movementType.addEventListener('change', updateMovementInput);
  updateMovementInput();

  const categoryLabels = @json($categorySummary->keys()->values());
  const categoryValues = @json($categorySummary->values()->values());
  const dailyLabels = @json($dailySummary->keys()->values());
  const dailyValues = @json($dailySummary->values()->values());
  const colors = ['#1a936f', '#f3a712', '#0d5c3e', '#88d49e', '#2e80b6'];
  const lineCanvas = document.getElementById('stepTrendChart');
  new Chart(lineCanvas, {
    type: 'line',
    data: { labels: dailyLabels, datasets: [{ label: 'Quantity', data: dailyValues, borderColor: '#0d5c3e', backgroundColor: 'rgba(13,92,62,.12)', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#1a936f' }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } }
  });
  new Chart(document.getElementById('workoutTypeChart'), {
    type: 'doughnut',
    data: { labels: categoryLabels, datasets: [{ data: categoryValues, backgroundColor: colors, borderWidth: 0, hoverOffset: 5 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } } }
  });
  // AJAX polling: refresh inventory summary and recent movements every 30 seconds.
  async function refreshInventorySummary() {
    try {
      const response = await fetch(@json(route('api.inventory.summary')), { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
      if (!response.ok) return;
      const data = await response.json();
      document.getElementById('totalQuantityStat').textContent = Number(data.total_quantity).toLocaleString();
      document.getElementById('lowStockStat').textContent = Number(data.low_stock_count).toLocaleString();
      document.getElementById('lastUpdatedAt').textContent = 'Updated ' + new Date(data.updated_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'});
      const body = document.getElementById('recentTransactionsBody');
      if (data.recent_transactions && data.recent_transactions.length) {
        body.innerHTML = data.recent_transactions.map(tx => {
          const type = String(tx.type || 'opening');
          const badge = type === 'out' ? 'text-bg-warning' : (type === 'in' ? 'text-bg-success' : (type === 'adjustment' ? 'text-bg-primary' : 'text-bg-secondary'));
          const sign = type === 'out' ? '−' : (type === 'adjustment' ? 'Set to ' : '+');
          const when = tx.created_at ? new Date(tx.created_at.replace(' ', 'T')).toLocaleString([], {month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit'}) : '—';
          return `<tr><td class="text-nowrap">${when}</td><td><div class="item-name">${escapeHtml(tx.product || 'Deleted item')}</div><div class="item-sku">${escapeHtml(tx.sku || '')}</div></td><td><span class="badge ${badge}">${escapeHtml(type.toUpperCase())}</span></td><td>${sign}${Number(tx.quantity).toLocaleString()}</td><td>${Number(tx.quantity_before).toLocaleString()} → ${Number(tx.quantity_after).toLocaleString()}</td><td>${escapeHtml(tx.actor || 'System')}</td></tr>`;
        }).join('');
      }
    } catch (error) { console.warn('Inventory polling failed; will retry in 30 seconds.'); }
  }
  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
  }
  refreshInventorySummary();
  window.setInterval(refreshInventorySummary, 30000);

  const hour = new Date().getHours();
  const greeting = hour < 12 ? 'Good morning' : (hour < 17 ? 'Good afternoon' : 'Good evening');
  document.getElementById('greeting').textContent = greeting + ', ' + @json(session('fitpulse_user', 'Admin')) + '!';
});
</script>
@include('widgets._scripts')
</body>
</html>

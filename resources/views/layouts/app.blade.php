<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>FitPulse | @yield('title', 'Procurement')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @stack('head')
</head>
<body>
@php($me = auth()->user())
<nav class="navbar navbar-expand-lg navbar-dark navbar-emerald sticky-top">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}"><span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span> FitPulse <span class="brand-sub d-none d-sm-inline">Procurement</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="topNav">
      <div class="ms-auto d-flex align-items-center gap-3 py-2 py-lg-0">
        <a href="{{ route('notifications.index') }}" class="btn btn-outline-light btn-sm" title="Notifications"><i class="bi bi-bell"></i> Notifications</a>
        <span class="text-white-50 small"><i class="bi bi-person-circle me-1"></i>{{ session('fitpulse_user', 'Admin') }} <span class="badge text-bg-light text-uppercase ms-1">{{ optional($me)->role ?? 'guest' }}</span></span>
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
        <a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a class="nav-link" href="{{ route('transactions.index') }}"><i class="bi bi-clock-history"></i> Transaction History</a>
        <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}"><i class="bi bi-truck"></i> Suppliers</a>
        <a class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}"><i class="bi bi-file-earmark-text"></i> Purchase Orders</a>
        @can('view-reports')
        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.supplier-performance') }}"><i class="bi bi-bar-chart-line"></i> Supplier Performance</a>
        @endcan
        <a class="nav-link" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Notifications</a>
      </nav>
      <div class="sidebar-footer small text-muted"><i class="bi bi-circle-fill me-1"></i> Signed in as {{ optional($me)->role ?? 'guest' }}</div>
    </aside>

    <main class="col-md-9 col-xl-10 main-content px-3 px-lg-4 py-4">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-octagon me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Please check the form:</strong>
          <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      @yield('content')

      <footer class="text-center small text-muted py-3">FitPulse Inventory System <span class="mx-1">·</span> Procurement module</footer>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

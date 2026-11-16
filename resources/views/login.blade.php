<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FitPulse | Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page">
  <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="login-card card w-100" style="max-width:440px">
      <div class="login-heading text-center text-white p-4">
        <div class="brand-mark mx-auto mb-3"><i class="bi bi-lightning-charge-fill"></i></div>
        <h1 class="h3 fw-bold mb-1">FitPulse</h1>
        <p class="mb-0 opacity-75">Inventory & Activity Dashboard</p>
      </div>
      <div class="card-body p-4 p-md-5">
        <h2 class="h5 fw-bold mb-1">Welcome back</h2>
        <p class="text-muted small mb-4">Sign in to manage your inventory.</p>
        @if(session('login_notice'))
          <div class="alert alert-info py-2">{{ session('login_notice') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('login.submit') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="username" class="form-label fw-semibold small">Username</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input id="username" name="username" type="text" class="form-control" placeholder="Enter username" value="{{ old('username') }}" autocomplete="username" required autofocus>
            </div>
          </div>
          <div class="mb-4">
            <label for="password" class="form-label fw-semibold small">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input id="password" name="password" type="password" class="form-control" placeholder="Enter password" autocomplete="current-password" required>
            </div>
          </div>
          <button type="submit" class="btn btn-emerald w-100 py-2 fw-semibold">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
        </form>
        <div class="demo-hint mt-4 p-3 rounded-3 small">
          <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>Demo account</div>
          Username: <code>admin</code> &nbsp; Password: <code>password123</code>
        </div>
      </div>
    </div>
  </main>
</body>
</html>

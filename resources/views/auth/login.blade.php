<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f6f8fa; color: #1f2328; }
        .auth-card { border: 1px solid #d0d7de; border-radius: 6px; width: 100%; max-width: 350px; }
        .btn-github { background-color: #1f883d; color: #fff; font-weight: 500; border-radius: 6px; }
        .btn-github:hover { background-color: #1a7f37; color: #fff; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">
    <div class="container d-flex flex-column align-items-center">

        <div class="mb-3 text-center">
            <i class="bi bi-shield-lock-fill fs-1 text-dark"></i>
            <h4 class="fw-bold mt-2">Sign in to System</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm w-100 max-w-350 mb-3" style="max-width: 350px;" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="card auth-card shadow-sm p-4 bg-white">
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Email address</label>
                    <input type="email" id="email" name="email" 
                           class="form-control form-control-sm @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Password</label>
                    <input type="password" id="password" name="password" 
                           class="form-control form-control-sm @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-github btn-sm w-100 py-2">Sign in</button>
            </form>
        </div>

        <div class="card auth-card shadow-sm p-3 mt-3 text-center bg-white">
            <span class="small">New to system? <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold">Create an account</a></span>
        </div>

    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Management System</title>
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Vite Asset Directives -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { background-color: #f6f8fa; color: #1f2328; }
        
        /* Navbar Styles */
        .navbar-github { background-color: #24292f; border-bottom: 1px solid #d0d7de; }
        .navbar-github .navbar-brand { color: #ffffff; font-weight: 600; }
        .navbar-github .nav-link { color: #d0d7de; font-size: 0.9rem; font-weight: 500; padding: 0.5rem 0.8rem; }
        .navbar-github .nav-link:hover, 
        .navbar-github .nav-link.active { color: #ffffff; }
        .navbar-github .nav-link.active { font-weight: 600; border-bottom: 2px solid #fd8c73; }
        
        /* Card & Table Custom Rules */
        .card { border: 1px solid #d0d7de; border-radius: 6px; }
        .card-header { background-color: #f6f8fa; border-bottom: 1px solid #d0d7de; }
        .table { margin-bottom: 0; }
        .table th { background-color: #f6f8fa; color: #636c76; font-size: 0.85rem; font-weight: 600; border-bottom: 1px solid #d0d7de; }
        .table td { vertical-align: middle; border-bottom: 1px solid #d0d7de; color: #1f2328; font-size: 0.9rem; }
        
        /* Button Utility Classes */
        .btn-github { background-color: #1f883d; color: #fff; font-weight: 500; border-radius: 6px; }
        .btn-github:hover { background-color: #1a7f37; color: #fff; }
        .btn-outline-github { border-color: #d0d7de; color: #24292f; background-color: #f6f8fa; }
        .btn-outline-github:hover { background-color: #f3f4f6; border-color: #d0d7de; color: #24292f; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand navbar-dark navbar-github mb-4 py-2">
        <div class="container-lg d-flex justify-content-between align-items-center">
            
            <!-- Left: Brand & Always Visible Menu Links -->
            <div class="d-flex align-items-center">
                <a class="navbar-brand d-flex align-items-center me-4" href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam-fill me-2 text-warning"></i>
                    <span>Inventory System</span>
                </a>

                <ul class="navbar-nav flex-row gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                            <i class="bi bi-building me-1"></i> Companies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                            <i class="bi bi-tags me-1"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('locations.*') ? 'active' : '' }}" href="{{ route('locations.index') }}">
                            <i class="bi bi-geo-alt me-1"></i> Locations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            <i class="bi bi-box me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('base-units.*') ? 'active' : '' }}" href="{{ route('base-units.index') }}">
                            <i class="bi bi-rulers me-1"></i> Base Units
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                            <i class="bi bi-clipboard-data me-1"></i> Stock
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Right: User / Logout -->
            @auth
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('profile.edit') }}" class="text-light small text-decoration-none {{ request()->routeIs('profile.*') ? 'fw-semibold' : '' }}">
                        <i class="bi bi-person-circle me-1 text-secondary"></i>
                        {{ Auth::user()->name }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light px-2 py-1" style="font-size: 0.8rem;">
                            Sign out
                        </button>
                    </form>
                </div>
            @endauth

        </div>
    </nav>

    <!-- Main Page Content -->
    <main class="container-lg mb-5 flex-grow-1">

        <!-- Dynamic Flash Notification Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('status'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Global Validation Summary Alert -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                    <strong>Please fix the following validation errors:</strong>
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
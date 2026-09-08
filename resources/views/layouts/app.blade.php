<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Applied before first paint so there's no flash of the wrong theme -->
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

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
        .navbar-github .nav-link { color: #d0d7de; font-size: 0.9rem; font-weight: 500; padding: 0.5rem 0.8rem; border-radius: 6px; transition: background-color 0.15s ease, color 0.15s ease; }
        .navbar-github .nav-link:hover { color: #ffffff; background-color: rgba(255,255,255,0.06); }
        .navbar-github .nav-link.active { color: #ffffff; font-weight: 600; background-color: rgba(255,255,255,0.1); }
        .navbar-github .dropdown-toggle::after { margin-left: 0.4rem; }

        /* Card & Table Custom Rules */
        .card { border: 1px solid #d0d7de; border-radius: 8px; box-shadow: 0 1px 2px rgba(31,35,40,0.04), 0 4px 10px -6px rgba(31,35,40,0.10); }
        .card-header { background-color: #f6f8fa; border-bottom: 1px solid #d0d7de; }
        .table { margin-bottom: 0; }
        .table th { background-color: #f6f8fa; color: #636c76; font-size: 0.85rem; font-weight: 600; border-bottom: 1px solid #d0d7de; }
        .table td { vertical-align: middle; border-bottom: 1px solid #d0d7de; color: #1f2328; font-size: 0.9rem; font-variant-numeric: tabular-nums; }
        .table tbody tr:hover td { background-color: #f6f8fa; }

        /* Button Utility Classes */
        .btn-github { background-color: #1f883d; color: #fff; font-weight: 500; border-radius: 6px; transition: background-color 0.15s ease; }
        .btn-github:hover { background-color: #1a7f37; color: #fff; }
        .btn-outline-github { border-color: #d0d7de; color: #24292f; background-color: #f6f8fa; transition: background-color 0.15s ease, border-color 0.15s ease; }
        .btn-outline-github:hover { background-color: #f3f4f6; border-color: #d0d7de; color: #24292f; }
        .btn-github:focus-visible, .btn-outline-github:focus-visible { outline: 2px solid #1f883d; outline-offset: 2px; }

        /* Dashboard stat tiles */
        .stat-tile {
            display: flex; flex-direction: column; gap: 4px;
            background-color: #ffffff; border: 1px solid #d0d7de; border-radius: 8px;
            padding: 16px 18px; height: 100%;
            box-shadow: 0 1px 2px rgba(31,35,40,0.04), 0 4px 10px -6px rgba(31,35,40,0.10);
            transition: border-color 0.15s ease, transform 0.15s ease;
        }
        .stat-tile:hover { border-color: #1f883d; transform: translateY(-1px); }
        .stat-tile .label { font-size: 0.78rem; font-weight: 600; letter-spacing: 0.02em; text-transform: uppercase; color: #636c76; }
        .stat-tile .value { font-size: 1.6rem; font-weight: 700; color: #1f2328; font-variant-numeric: tabular-nums; }
        .stat-tile-warn { border-color: #d1242f; }
        .stat-tile-warn .value { color: #d1242f; }

        /* Dark mode overrides (Bootstrap 5.3 native color mode) */
        [data-bs-theme="dark"] body { background-color: #0d1117; color: #c9d1d9; }
        [data-bs-theme="dark"] .card { border-color: #30363d; }
        [data-bs-theme="dark"] .card-header { background-color: #161b22; border-color: #30363d; }
        [data-bs-theme="dark"] .table th { background-color: #161b22; color: #8b949e; border-color: #30363d; }
        [data-bs-theme="dark"] .table td { border-color: #30363d; color: #c9d1d9; }
        [data-bs-theme="dark"] .table tbody tr:hover td { background-color: #161b22; }
        [data-bs-theme="dark"] .btn-outline-github { background-color: #161b22; border-color: #30363d; color: #c9d1d9; }
        [data-bs-theme="dark"] .btn-outline-github:hover { background-color: #21262d; }
        [data-bs-theme="dark"] .stat-tile { background-color: #161b22; border-color: #30363d; }
        [data-bs-theme="dark"] .stat-tile:hover { border-color: #3fb950; }
        [data-bs-theme="dark"] .stat-tile .label { color: #8b949e; }
        [data-bs-theme="dark"] .stat-tile .value { color: #c9d1d9; }
        [data-bs-theme="dark"] .stat-tile-warn { border-color: #f85149; }
        [data-bs-theme="dark"] .stat-tile-warn .value { color: #f85149; }

        /* Bootstrap's .text-dark is a fixed near-black color that does NOT
           adapt to dark mode (unlike .text-secondary/.text-muted), so it's
           invisible against a dark card/table. Used all over this app for
           row labels, headings, and sort-link text. Excludes .badge, since
           those pair it with .bg-light and stay legible in both themes. */
        [data-bs-theme="dark"] .text-dark:not(.badge) { color: #c9d1d9 !important; }

        /* Same problem as .text-dark above: Bootstrap's .bg-white is a fixed
           literal color (with !important), used here for card headers and
           search-icon boxes, so it stays a bright white patch in dark mode. */
        [data-bs-theme="dark"] .bg-white { background-color: #161b22 !important; }

        /* Same problem again: .bg-light is a fixed literal light-gray (with
           !important), used here for modal footers. Excludes .badge, since
           badge usages pair it with .text-dark and are self-consistent. */
        [data-bs-theme="dark"] .bg-light:not(.badge) { background-color: #161b22 !important; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark navbar-github py-2 mb-4">
        <div class="container-lg d-flex align-items-center justify-content-between flex-wrap gap-2">

            <div class="d-flex align-items-center flex-wrap gap-2">
                <a class="navbar-brand d-flex align-items-center me-1" href="{{ route('dashboard.index') }}">
                    <i class="bi bi-box-seam-fill me-2 text-warning"></i>
                    <span>Inventory System</span>
                </a>

                @auth
                    <ul class="navbar-nav flex-row flex-wrap gap-1 ms-2">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                                <i class="bi bi-speedometer2 me-1"></i> {{ __('Dashboard') }}
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['companies.*', 'categories.*', 'locations.*', 'base-units.*', 'unit-conversions.*', 'suppliers.*']) ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-database me-1"></i> {{ __('General Data') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                                        <i class="bi bi-building me-2"></i> {{ __('Companies') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('locations.*') ? 'active' : '' }}" href="{{ route('locations.index') }}">
                                        <i class="bi bi-geo-alt me-2"></i> {{ __('Locations') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                                        <i class="bi bi-truck me-2"></i> {{ __('Suppliers') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                        <i class="bi bi-tags me-2"></i> {{ __('Categories') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('base-units.*') ? 'active' : '' }}" href="{{ route('base-units.index') }}">
                                        <i class="bi bi-rulers me-2"></i> {{ __('Base Units') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('unit-conversions.*') ? 'active' : '' }}" href="{{ route('unit-conversions.index') }}">
                                        <i class="bi bi-arrow-left-right me-2"></i> {{ __('Unit Conversions') }}
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['products.*', 'inventory.*']) ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-box-seam me-1"></i> {{ __('Inventory') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                        <i class="bi bi-box me-2"></i> {{ __('Products') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                        <i class="bi bi-clipboard-data me-2"></i> {{ __('Stock') }}
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['purchase-orders.*', 'deliveries.*', 'stock-transfers.*']) ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cart me-1"></i> {{ __('PO & Delivery') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">
                                        <i class="bi bi-cart me-2"></i> {{ __('Product Order') }}
                                    </a>
                                </li>
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isBusinessAdmin())
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs(['deliveries.*', 'stock-transfers.*']) ? 'active' : '' }}" href="{{ route('deliveries.index') }}">
                                            <i class="bi bi-truck me-2"></i> {{ __('Delivery') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['users.*', 'activity-log.*', 'reports.*']) ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-shield-lock me-1"></i> {{ __('Admin') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                        <i class="bi bi-bar-chart-line me-2"></i> {{ __('Graphs') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('activity-log.*') ? 'active' : '' }}" href="{{ route('activity-log.index') }}">
                                        <i class="bi bi-clock-history me-2"></i> {{ __('History') }}
                                    </a>
                                </li>
                                @if(auth()->user()->isSuperAdmin())
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                            <i class="bi bi-people me-2"></i> {{ __('Users') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                @endauth
            </div>

            @auth
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $roleBadge = match(Auth::user()->role) {
                                'super_admin' => ['bg-danger-subtle text-danger', 'Super Admin'],
                                'business_admin' => ['bg-primary-subtle text-primary', 'Business Admin'],
                                default => ['bg-secondary-subtle text-secondary', 'Moderator'],
                            };
                        @endphp
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle border flex-shrink-0" style="width:36px;height:36px;object-fit:cover;">
                        @else
                            <div class="rounded-circle bg-secondary-subtle border d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;">
                                <i class="bi bi-person-fill text-secondary fs-5"></i>
                            </div>
                        @endif
                        <div class="text-light small lh-sm">
                            <div>{{ Auth::user()->name }}</div>
                            <span class="badge {{ $roleBadge[0] }} border">{{ $roleBadge[1] }}</span>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light px-2 py-1 dropdown-toggle {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('Settings') }}" aria-label="{{ __('Settings') }}">
                            <i class="bi bi-gear-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                            <i class="bi bi-person-circle me-2"></i> {{ __('My Profile') }}
                                        </a>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">{{ __('Language') }}</h6></li>
                                    <li>
                                        <form action="{{ route('locale.set', 'en') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                                                <i class="bi bi-check-lg me-2 {{ app()->getLocale() === 'en' ? '' : 'invisible' }}"></i> {{ __('English') }}
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('locale.set', 'tl') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item {{ app()->getLocale() === 'tl' ? 'active' : '' }}">
                                                <i class="bi bi-check-lg me-2 {{ app()->getLocale() === 'tl' ? '' : 'invisible' }}"></i> {{ __('Tagalog') }}
                                            </button>
                                        </form>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">{{ __('Theme') }}</h6></li>
                                    <li>
                                        <button type="button" class="dropdown-item" data-theme-option="light">
                                            <i class="bi bi-check-lg me-2 theme-check" data-theme-check="light"></i>
                                            <i class="bi bi-sun me-1"></i> {{ __('Light') }}
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item" data-theme-option="dark">
                                            <i class="bi bi-check-lg me-2 theme-check" data-theme-check="dark"></i>
                                            <i class="bi bi-moon-stars me-1"></i> {{ __('Dark') }}
                                        </button>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#contactUsModal">
                                            <i class="bi bi-envelope me-2"></i> {{ __('Contact Us') }}
                                        </button>
                                    </li>

                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i> {{ __('Sign out') }}
                                            </button>
                                        </form>
                                    </li>
                        </ul>
                    </div>
                </div>
            @endauth

        </div>
    </nav>

    <!-- Contact Us Modal -->
    @auth
        <div class="modal fade" id="contactUsModal" tabindex="-1" aria-labelledby="contactUsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactUsModalLabel">
                            <i class="bi bi-envelope me-1"></i> {{ __('Contact Us') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary">{{ __('Need help? Reach out to your system administrator.') }}</p>
                        <div class="mb-2">
                            <div class="small text-muted">{{ __('Support Email') }}</div>
                            <a href="mailto:support@inventorysystem.local">support@inventorysystem.local</a>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Support Phone') }}</div>
                            <a href="tel:+10000000000">+1 (000) 000-0000</a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-github btn-sm" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endauth

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

    <!-- Theme Toggle -->
    <script>
        (function () {
            function currentTheme() {
                return localStorage.getItem('theme') || 'light';
            }

            function applyThemeUI() {
                var theme = currentTheme();
                document.querySelectorAll('[data-theme-check]').forEach(function (icon) {
                    icon.classList.toggle('invisible', icon.dataset.themeCheck !== theme);
                });
            }

            document.querySelectorAll('[data-theme-option]').forEach(function (button) {
                button.addEventListener('click', function () {
                    localStorage.setItem('theme', this.dataset.themeOption);
                    document.documentElement.setAttribute('data-bs-theme', this.dataset.themeOption);
                    applyThemeUI();
                });
            });

            applyThemeUI();
        })();
    </script>

    @stack('scripts')
</body>
</html>
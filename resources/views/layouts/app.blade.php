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
        .navbar-github { background-color: #24292f; border-bottom: 1px solid #d0d7de; position: sticky; top: 0; z-index: 1030; }
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
        <div class="container-lg">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.index') }}">
                <i class="bi bi-box-seam-fill me-2 text-warning"></i>
                <span>Inventory System</span>
            </a>

            @auth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav flex-row flex-wrap gap-1 ms-lg-3">
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

                    <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0 ms-lg-auto">
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
                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#faqModal">
                                            <i class="bi bi-question-circle me-2"></i> {{ __('FAQ') }}
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#userGuideModal">
                                            <i class="bi bi-book me-2"></i> {{ __('User Guide') }}
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

    <!-- FAQ Modal -->
    @auth
        <div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="faqModalLabel">
                            <i class="bi bi-question-circle me-1"></i> {{ __('Frequently Asked Questions') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="accordion accordion-flush" id="faqAccordion">
                            @php
                                $faqs = [
                                    [
                                        'q' => 'What\'s the difference between a Warehouse and a Store location?',
                                        'a' => 'A Warehouse receives incoming stock — every Purchase Order is delivered to a Warehouse-type location. A Store is a selling location — POS only ever sells from Store-type locations. Stock moves from a Warehouse into a Store via a Stock Transfer.',
                                    ],
                                    [
                                        'q' => 'How do I get stock from a Warehouse into a Store so it can be sold?',
                                        'a' => 'Go to Inventory → Stock, find the product at the warehouse, and click the Transfer icon. Warehouse→Store transfers are tracked as a delivery: they sit "Pending" until someone confirms receipt in Delivery Tracking, at which point the store\'s stock actually increases.',
                                    ],
                                    [
                                        'q' => 'Why can\'t I select a Store as the "Deliver To" location on a Purchase Order?',
                                        'a' => 'Purchase Orders can only be received into a Warehouse — this keeps supplier deliveries verified before they\'re sellable. Once received, use a Stock Transfer to move it into a Store.',
                                    ],
                                    [
                                        'q' => 'What\'s the difference between Super Admin, Business Admin, and Moderator?',
                                        'a' => 'Super Admin has full access to both this system and POS, including Users management. Business Admin has the same access except Users management. Moderator is POS-only and can\'t log into this Inventory system at all.',
                                    ],
                                    [
                                        'q' => 'Why is a product missing from the Purchase Order product list?',
                                        'a' => 'Two filters apply: the product must belong to the Company you selected, and — once you pick a Supplier — it must also be linked to that Supplier on the product\'s own edit page (see the Suppliers checkboxes there).',
                                    ],
                                    [
                                        'q' => 'How do I get a product to show up under "Needs Attention" on the Dashboard?',
                                        'a' => 'Set a Reorder Point greater than 0 on that product (Products → Edit). Once its stock at any location drops to or below that number, it appears there automatically.',
                                    ],
                                    [
                                        'q' => 'I deleted or can\'t delete a Purchase Order / Stock Transfer / Unit Conversion — why?',
                                        'a' => 'These have safety checks: a received Purchase Order or Stock Transfer can only be deleted by a Super Admin (to preserve stock history); a pending Stock Transfer must be Cancelled first (its stock is already in transit); and a Base Unit or Unit Conversion in use by an active product can\'t be deleted at all.',
                                    ],
                                    [
                                        'q' => 'How do I reset my password if I forget it?',
                                        'a' => 'Use "Forgot password?" on the sign-in page. The reset code is sent to your Alternative Email if you\'ve set one (Profile page), otherwise to your primary login email.',
                                    ],
                                    [
                                        'q' => 'How do I see a user\'s full details without opening the edit form?',
                                        'a' => 'On the Users page (Super Admin only), click the eye icon in the Actions column. It opens a read-only profile with that user\'s companies, active company, POS location, and other details — no risk of accidentally changing anything.',
                                    ],
                                ];
                            @endphp
                            @foreach($faqs as $i => $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faqItem{{ $i }}">
                                            {{ $faq['q'] }}
                                        </button>
                                    </h2>
                                    <div id="faqItem{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body small text-secondary">{{ $faq['a'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-github btn-sm" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <!-- User Guide Modal -->
    @auth
        <div class="modal fade" id="userGuideModal" tabindex="-1" aria-labelledby="userGuideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userGuideModalLabel">
                            <i class="bi bi-book me-1"></i> {{ __('User Guide') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary small">A walkthrough of how stock moves through this system, from supplier to sale.</p>

                        <h6 class="fw-bold mt-3">The flow, end to end</h6>
                        <p class="small text-secondary mb-2">
                            <strong>Supplier</strong> → <strong>Purchase Order</strong> (received into a <strong>Warehouse</strong>) →
                            <strong>Stock Transfer</strong> (moved into a <strong>Store</strong>) → <strong>POS Sale</strong>.
                            Every step is logged in the Activity Log.
                        </p>

                        <h6 class="fw-bold mt-3">1. Set up your foundation data</h6>
                        <p class="small text-secondary mb-1">Under <strong>General Data</strong>: Companies and Locations first (tag each Location as Warehouse or Store), then Suppliers, Categories, Base Units, and Unit Conversions.</p>

                        <h6 class="fw-bold mt-3">2. Add products</h6>
                        <p class="small text-secondary mb-1">Under <strong>Inventory → Products</strong>: set a base unit, an optional unit conversion, a reorder point (for low-stock alerts), and which Suppliers can provide it.</p>

                        <h6 class="fw-bold mt-3">3. Order stock</h6>
                        <p class="small text-secondary mb-1">Under <strong>PO & Delivery → Product Order</strong>: create a Purchase Order against a Supplier and Warehouse, then mark it Received once the delivery arrives — this increases the warehouse's stock.</p>

                        <h6 class="fw-bold mt-3">4. Move stock to a store</h6>
                        <p class="small text-secondary mb-1">Under <strong>Inventory → Stock</strong>: transfer from a Warehouse to a Store. This creates a Pending delivery — confirm it in <strong>PO & Delivery → Delivery</strong> once the stock physically arrives at the store.</p>

                        <h6 class="fw-bold mt-3">5. Track everything</h6>
                        <p class="small text-secondary mb-1">The <strong>Dashboard</strong> shows low-stock alerts, pending deliveries, and recent activity at a glance. <strong>Admin → History</strong> and <strong>Graphs</strong> give the full audit trail and reporting.</p>

                        <h6 class="fw-bold mt-3">6. Manage user accounts (Super Admin)</h6>
                        <p class="small text-secondary mb-1">Under <strong>Admin → Users</strong>: create accounts, assign a Role and Companies, and lock Moderators to a POS Location. Click the eye icon on any row to view that user's full profile read-only, or the pencil icon to edit it.</p>

                        <div class="alert alert-light border small mt-3 mb-0">
                            Still stuck? Check the <a href="#" class="switch-modal-link" data-target="#faqModal">FAQ</a> or use <a href="#" class="switch-modal-link" data-target="#contactUsModal">Contact Us</a>.
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
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" data-auto-dismiss="5000">
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
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-3" role="alert" data-auto-dismiss="5000">
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

    <!-- Alpine.js — used for lightweight, reload-free interactivity (e.g. client-side table sorting) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

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

            // Switch from one help modal straight to another: dismiss the
            // current one, then show the target once it's fully hidden —
            // Bootstrap doesn't support dismiss+show chained on one click.
            document.querySelectorAll('.switch-modal-link').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var currentModalEl = this.closest('.modal');
                    var targetEl = document.querySelector(this.dataset.target);
                    if (!currentModalEl || !targetEl) return;

                    var currentModal = bootstrap.Modal.getOrCreateInstance(currentModalEl);
                    currentModalEl.addEventListener('hidden.bs.modal', function onHidden() {
                        currentModalEl.removeEventListener('hidden.bs.modal', onHidden);
                        bootstrap.Modal.getOrCreateInstance(targetEl).show();
                    });
                    currentModal.hide();
                });
            });

            // Auto-dismiss success/info flash banners after 5s — errors and
            // warnings stay put until the user closes them, since those
            // need to actually be read/acted on, not just glanced at.
            document.querySelectorAll('[data-auto-dismiss]').forEach(function (alertEl) {
                var delay = parseInt(alertEl.dataset.autoDismiss, 10) || 5000;
                setTimeout(function () {
                    bootstrap.Alert.getOrCreateInstance(alertEl)?.close();
                }, delay);
            });

            // Show/hide password toggle — same eye-icon pattern as the
            // standalone reset-password page, just reusable here for any
            // password field that opts in with a .toggle-password button.
            document.querySelectorAll('.toggle-password').forEach(function (button) {
                button.addEventListener('click', function () {
                    var input = document.getElementById(this.dataset.target);
                    var icon = this.querySelector('i');
                    var showing = input.type === 'text';

                    input.type = showing ? 'password' : 'text';
                    icon.classList.toggle('bi-eye', showing);
                    icon.classList.toggle('bi-eye-slash', !showing);
                    this.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
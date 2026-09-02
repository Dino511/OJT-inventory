@extends('layouts.app')

@section('title', 'Graphs & Reports')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">{{ __('Graphs & Reports') }}</h3>
            <p class="text-secondary small mb-0">{{ __('Stock and product levels across every registered company.') }}</p>
        </div>
        <form action="{{ route('reports.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <label for="company_id" class="small text-secondary mb-0">{{ __('Company') }}</label>
            <select id="company_id" name="company_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 200px;">
                <option value="">{{ __('All Companies') }}</option>
                @foreach($companies as $company)
                    <option value="{{ $company->company_id }}" {{ (int) $selectedCompanyId === (int) $company->company_id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Overview Stat Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-2 text-primary"><i class="bi bi-building"></i></div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $totals['companies'] }}</div>
                        <div class="small text-secondary">{{ __('Registered Companies') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-2 text-success"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $totals['products'] }}</div>
                        <div class="small text-secondary">{{ __('Total Products') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-2 text-info"><i class="bi bi-clipboard-data"></i></div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($totals['stock_units']) }}</div>
                        <div class="small text-secondary">{{ __('Total Stock Units') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <!-- Stock by Company -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">{{ __('Stock by Company') }}</span>
                </div>
                <div class="card-body">
                    @if($stockByCompany->isEmpty())
                        <p class="text-muted small mb-0">{{ __('No companies registered yet.') }}</p>
                    @else
                        <div style="position: relative; height: 260px;">
                            <canvas id="stockByCompanyChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Products by Category -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">
                        {{ __('Products by Category') }}
                        @if($selectedCompanyId)
                            &mdash; {{ $companies->firstWhere('company_id', $selectedCompanyId)->name ?? '' }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    @if($productsByCategory->isEmpty())
                        <p class="text-muted small mb-0">{{ __('No products found for this filter.') }}</p>
                    @else
                        <div style="position: relative; height: 260px;">
                            <canvas id="productsByCategoryChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <!-- Top Products by Stock -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">
                        {{ __('Top Products by Stock Quantity') }}
                        @if($selectedCompanyId)
                            &mdash; {{ $companies->firstWhere('company_id', $selectedCompanyId)->name ?? '' }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    @if($stockByProduct->isEmpty())
                        <p class="text-muted small mb-0">{{ __('No stock records found for this filter.') }}</p>
                    @else
                        <div style="position: relative; height: {{ max(200, count($stockByProduct) * 32) }}px;">
                            <canvas id="stockByProductChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Company Report Table -->
    <div class="card shadow-sm">
        <div class="card-header p-3">
            <span class="fw-semibold text-secondary small">{{ __('Company Report') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Company') }}</th>
                        <th class="text-end">{{ __('Products') }}</th>
                        <th class="text-end">{{ __('Total Stock Units') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockByCompany as $row)
                        <tr>
                            <td class="fw-semibold text-dark">{{ $row->label }}</td>
                            <td class="text-end">{{ number_format($row->product_count) }}</td>
                            <td class="text-end">{{ number_format($row->total_quantity) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">{{ __('No companies registered yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sales Report -->
    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Sales Report') }}</h4>
            <p class="text-secondary small mb-0">{{ __('Revenue and transactions over time, by company and location.') }}</p>
        </div>
        <form action="{{ route('reports.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            @if($selectedCompanyId)
                <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
            @endif
            <input type="hidden" name="sales_period" value="{{ $salesPeriod }}">

            <label for="sales_company_id" class="small text-secondary mb-0">{{ __('Company') }}</label>
            <select id="sales_company_id" name="sales_company_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 180px;">
                <option value="">{{ __('All Companies') }}</option>
                @foreach($companies as $company)
                    <option value="{{ $company->company_id }}" data-company-id="{{ $company->company_id }}" {{ (int) $salesCompanyId === (int) $company->company_id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>

            <label for="sales_location_id" class="small text-secondary mb-0">{{ __('Location') }}</label>
            <select id="sales_location_id" name="sales_location_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 180px;">
                <option value="">{{ __('All Locations') }}</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" data-company-id="{{ $location->company_id }}" {{ (int) $salesLocationId === (int) $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="btn-group btn-group-sm mb-3" role="group">
        @foreach(['daily' => __('Daily'), 'monthly' => __('Monthly'), 'yearly' => __('Yearly')] as $value => $labelText)
            <a href="{{ route('reports.index', array_filter(['company_id' => $selectedCompanyId, 'sales_company_id' => $salesCompanyId, 'sales_location_id' => $salesLocationId, 'sales_period' => $value])) }}"
               class="btn {{ $salesPeriod === $value ? 'btn-github' : 'btn-outline-github' }}">
                {{ $labelText }}
            </a>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-2 text-primary"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="fs-4 fw-bold">{{ number_format($salesTotals['transactions']) }}</div>
                        <div class="small text-secondary">{{ __('Transactions') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="fs-2 text-success"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="fs-4 fw-bold">&#8369;{{ number_format($salesTotals['revenue'], 2) }}</div>
                        <div class="small text-secondary">{{ __('Revenue') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <!-- Revenue by Company -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">{{ __('Revenue by Company') }}</span>
                </div>
                <div class="card-body">
                    @if($revenueByCompany->isEmpty())
                        <p class="text-muted small mb-0">{{ __('No companies registered yet.') }}</p>
                    @else
                        <div style="position: relative; height: 260px;">
                            <canvas id="revenueByCompanyChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Revenue by Location -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">
                        {{ __('Revenue by Location') }}
                        @if($salesCompanyId)
                            &mdash; {{ $companies->firstWhere('company_id', $salesCompanyId)->name ?? '' }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    @if($revenueByLocation->isEmpty())
                        <p class="text-muted small mb-0">{{ __('No locations found for this filter.') }}</p>
                    @else
                        <div style="position: relative; height: 260px;">
                            <canvas id="revenueByLocationChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header p-3">
            <span class="fw-semibold text-secondary small">{{ __('Sales Over Time') }}</span>
        </div>
        <div class="card-body">
            @if($salesSeries->isEmpty())
                <p class="text-muted small mb-0">{{ __('No sales recorded for this filter.') }}</p>
            @else
                <div style="position: relative; height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header p-3">
            <span class="fw-semibold text-secondary small">{{ __('Sales Report Table') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Period') }}</th>
                        <th class="text-end">{{ __('Transactions') }}</th>
                        <th class="text-end">{{ __('Revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesSeries as $row)
                        <tr>
                            <td class="fw-semibold text-dark">{{ $row['label'] }}</td>
                            <td class="text-end">{{ number_format($row['transactions']) }}</td>
                            <td class="text-end">&#8369;{{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">{{ __('No sales recorded for this filter.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            var gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
            var textColor = isDark ? '#c9d1d9' : '#1f2328';
            Chart.defaults.color = textColor;
            Chart.defaults.borderColor = gridColor;
            Chart.defaults.maintainAspectRatio = false;

            var palette = ['#1f883d', '#0969da', '#bf8700', '#cf222e', '#8250df', '#0a7ea6', '#bc4c00', '#57606a'];

            var stockByCompany = @json($stockByCompany);
            if (stockByCompany.length) {
                new Chart(document.getElementById('stockByCompanyChart'), {
                    type: 'bar',
                    data: {
                        labels: stockByCompany.map(r => r.label),
                        datasets: [{
                            label: '{{ __('Total Stock Units') }}',
                            data: stockByCompany.map(r => r.total_quantity),
                            backgroundColor: palette[0],
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            var productsByCategory = @json($productsByCategory);
            if (productsByCategory.length) {
                new Chart(document.getElementById('productsByCategoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: productsByCategory.map(r => r.label),
                        datasets: [{
                            data: productsByCategory.map(r => r.product_count),
                            backgroundColor: palette,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'right' } }
                    }
                });
            }

            var stockByProduct = @json($stockByProduct);
            if (stockByProduct.length) {
                new Chart(document.getElementById('stockByProductChart'), {
                    type: 'bar',
                    data: {
                        labels: stockByProduct.map(r => r.label),
                        datasets: [{
                            label: '{{ __('Total Stock Units') }}',
                            data: stockByProduct.map(r => r.total_quantity),
                            backgroundColor: palette[1],
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }

            var revenueByCompany = @json($revenueByCompany);
            if (revenueByCompany.length) {
                new Chart(document.getElementById('revenueByCompanyChart'), {
                    type: 'bar',
                    data: {
                        labels: revenueByCompany.map(r => r.label),
                        datasets: [{
                            label: '{{ __('Revenue') }} (₱)',
                            data: revenueByCompany.map(r => r.revenue),
                            backgroundColor: palette[0],
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            var revenueByLocation = @json($revenueByLocation);
            if (revenueByLocation.length) {
                new Chart(document.getElementById('revenueByLocationChart'), {
                    type: 'bar',
                    data: {
                        labels: revenueByLocation.map(r => r.label),
                        datasets: [{
                            label: '{{ __('Revenue') }} (₱)',
                            data: revenueByLocation.map(r => r.revenue),
                            backgroundColor: palette[1],
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            var salesSeries = @json($salesSeries);
            if (salesSeries.length) {
                new Chart(document.getElementById('salesChart'), {
                    data: {
                        labels: salesSeries.map(r => r.label),
                        datasets: [
                            {
                                type: 'bar',
                                label: '{{ __('Transactions') }}',
                                data: salesSeries.map(r => r.transactions),
                                backgroundColor: palette[7],
                                yAxisID: 'yTransactions',
                                borderRadius: 4,
                            },
                            {
                                type: 'line',
                                label: '{{ __('Revenue') }} (₱)',
                                data: salesSeries.map(r => r.revenue),
                                borderColor: palette[0],
                                backgroundColor: palette[0],
                                yAxisID: 'yRevenue',
                                tension: 0.3,
                            },
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            yRevenue: { position: 'left', beginAtZero: true },
                            yTransactions: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } },
                        }
                    }
                });
            }

            // Only show locations belonging to the selected sales company (if any).
            var salesCompanySelect = document.getElementById('sales_company_id');
            var salesLocationSelect = document.getElementById('sales_location_id');
            function filterSalesLocations() {
                var companyId = salesCompanySelect.value;
                Array.from(salesLocationSelect.options).forEach(function (option) {
                    if (!option.value) return;
                    var matches = !companyId || option.dataset.companyId === companyId;
                    option.hidden = !matches;
                    if (!matches && option.selected) option.selected = false;
                });
            }
            salesCompanySelect?.addEventListener('change', filterSalesLocations);
            filterSalesLocations();
        })();
    </script>
@endpush

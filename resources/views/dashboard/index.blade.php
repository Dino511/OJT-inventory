@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Stat Tiles -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <a href="{{ route('products.index') }}" class="text-decoration-none">
                <div class="stat-tile">
                    <span class="label">Active Products</span>
                    <span class="value">{{ number_format($stats['active_products']) }}</span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route('inventory.index') }}" class="text-decoration-none">
                <div class="stat-tile">
                    <span class="label">Total Stock Units</span>
                    <span class="value">{{ number_format($stats['total_stock_units']) }}</span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="#needs-attention" class="text-decoration-none">
                <div class="stat-tile {{ $stats['low_stock_count'] > 0 ? 'stat-tile-warn' : '' }}">
                    <span class="label">Low Stock</span>
                    <span class="value">{{ number_format($stats['low_stock_count']) }}</span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route('purchase-orders.index') }}" class="text-decoration-none">
                <div class="stat-tile">
                    <span class="label">Pending Purchase Orders</span>
                    <span class="value">{{ number_format($stats['pending_purchase_orders']) }}</span>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg">
            <a href="{{ route('deliveries.index') }}" class="text-decoration-none">
                <div class="stat-tile">
                    <span class="label">Pending Transfers</span>
                    <span class="value">{{ number_format($stats['pending_stock_transfers']) }}</span>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Needs Attention -->
        <div class="col-lg-6" id="needs-attention">
            <div class="card shadow-sm h-100">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary small">NEEDS ATTENTION</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border">{{ $stats['low_stock_count'] }} low stock</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 sortable-table" id="needsAttentionTable">
                        <thead>
                            <tr>
                                <th scope="col" class="sortable-th" data-sort-key="product" data-sort-type="text">PRODUCT <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                <th scope="col" class="sortable-th" data-sort-key="location" data-sort-type="text">LOCATION <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                <th scope="col" class="text-end sortable-th" data-sort-key="stock" data-sort-type="number">STOCK <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                <th scope="col" class="text-end sortable-th" data-sort-key="reorder" data-sort-type="number">REORDER AT <i class="bi bi-arrow-down-up text-muted small"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems as $item)
                                <tr>
                                    <td data-sort-value="{{ $item->product->name ?? '' }}">
                                        <a href="{{ route('inventory.edit', $item) }}" class="text-decoration-none fw-semibold">{{ $item->product->name ?? 'N/A' }}</a>
                                    </td>
                                    <td class="text-secondary small" data-sort-value="{{ $item->location->name ?? '' }}">
                                        {{ $item->location->name ?? 'N/A' }}
                                        @if($item->location)
                                            <span class="badge bg-light text-secondary border ms-1">{{ $item->location->isWarehouse() ? 'Warehouse' : 'Store' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-danger fw-semibold" data-sort-value="{{ $item->quantity }}">{{ number_format($item->quantity) }}</td>
                                    <td class="text-end text-secondary small" data-sort-value="{{ $item->product->reorder_point ?? 0 }}">{{ number_format($item->product->reorder_point ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle fs-4 d-block mb-2 text-success"></i>
                                        Nothing below its reorder point.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Deliveries -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary small">PENDING DELIVERIES</span>
                    <a href="{{ route('deliveries.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 sortable-table" id="pendingDeliveriesTable">
                        <thead>
                            <tr>
                                <th scope="col" class="sortable-th" data-sort-key="type" data-sort-type="text">TYPE <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                <th scope="col" class="sortable-th" data-sort-key="reference" data-sort-type="text">REFERENCE <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                <th scope="col" class="sortable-th" data-sort-key="deliver-to" data-sort-type="text">DELIVER TO <i class="bi bi-arrow-down-up text-muted small"></i></th>
                                <th scope="col">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingDeliveries as $delivery)
                                <tr>
                                    <td data-sort-value="{{ $delivery['type'] === 'purchase_order' ? 'PO' : 'Transfer' }}">
                                        @if($delivery['type'] === 'purchase_order')
                                            <span class="badge bg-info-subtle text-info border">PO</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border">Transfer</span>
                                        @endif
                                    </td>
                                    <td data-sort-value="{{ $delivery['reference'] }}">
                                        <a href="{{ $delivery['show_route'] }}" class="text-decoration-none fw-semibold">{{ $delivery['reference'] }}</a>
                                    </td>
                                    <td class="text-secondary small" data-sort-value="{{ $delivery['destination'] }}">
                                        {{ $delivery['destination'] }}
                                        <span class="badge bg-light text-secondary border ms-1">{{ $delivery['destination_is_warehouse'] ? 'Warehouse' : 'Store' }}</span>
                                    </td>
                                    <td>@include('deliveries._status-badge', ['status' => $delivery['status']])</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle fs-4 d-block mb-2 text-success"></i>
                                        Nothing pending right now.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">RECENT ACTIVITY</span>
            <a href="{{ route('inventory.history.index') }}" class="small text-decoration-none">View all inventory history</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 170px;">DATE &amp; TIME</th>
                        <th scope="col" style="width: 150px;">ACTION</th>
                        <th scope="col" style="width: 160px;">USER</th>
                        <th scope="col">DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity as $log)
                        <tr>
                            <td class="text-secondary small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td>@include('inventory._history-action-badge')</td>
                            <td class="text-secondary small">{{ $log->user_name ?? 'System' }}</td>
                            <td class="small">@include('inventory._history-details')</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .sortable-th {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }
        .sortable-th:hover {
            color: var(--bs-body-color);
        }
        .sortable-th .bi-sort-up,
        .sortable-th .bi-sort-down {
            color: var(--bs-primary, #1f883d);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Lightweight client-side sort for the small, already-rendered
        // Dashboard widgets — no reload, no new JS dependency, matching the
        // rest of this app's plain-JS conventions (see the products/suppliers
        // list search filters). Sorting is per-table and re-toggles asc/desc
        // on repeated clicks of the same column.
        function makeTableSortable(tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const tbody = table.querySelector('tbody');
            const headers = table.querySelectorAll('.sortable-th');
            const totalColumns = table.querySelectorAll('thead th').length;

            headers.forEach((th) => {
                th.addEventListener('click', () => {
                    const key = th.dataset.sortKey;
                    const type = th.dataset.sortType;
                    const currentDirection = th.dataset.sortDirection === 'asc' ? 'desc' : 'asc';

                    headers.forEach((other) => {
                        other.removeAttribute('data-sort-direction');
                        const icon = other.querySelector('i');
                        if (icon) icon.className = 'bi bi-arrow-down-up text-muted small';
                    });
                    th.dataset.sortDirection = currentDirection;
                    const icon = th.querySelector('i');
                    if (icon) icon.className = currentDirection === 'asc' ? 'bi bi-sort-up small' : 'bi bi-sort-down small';

                    const columnIndex = Array.from(th.parentElement.children).indexOf(th);
                    const rows = Array.from(tbody.querySelectorAll('tr')).filter((row) => row.cells.length === totalColumns);

                    rows.sort((rowA, rowB) => {
                        const cellA = rowA.cells[columnIndex];
                        const cellB = rowB.cells[columnIndex];
                        if (!cellA || !cellB) return 0;

                        let valueA = cellA.dataset.sortValue ?? cellA.textContent.trim();
                        let valueB = cellB.dataset.sortValue ?? cellB.textContent.trim();

                        if (type === 'number') {
                            valueA = parseFloat(valueA) || 0;
                            valueB = parseFloat(valueB) || 0;
                            return currentDirection === 'asc' ? valueA - valueB : valueB - valueA;
                        }

                        valueA = String(valueA).toLowerCase();
                        valueB = String(valueB).toLowerCase();
                        if (valueA < valueB) return currentDirection === 'asc' ? -1 : 1;
                        if (valueA > valueB) return currentDirection === 'asc' ? 1 : -1;
                        return 0;
                    });

                    rows.forEach((row) => tbody.appendChild(row));
                });
            });
        }

        makeTableSortable('needsAttentionTable');
        makeTableSortable('pendingDeliveriesTable');
    </script>
@endpush

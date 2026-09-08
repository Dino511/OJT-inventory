@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Dashboard</h3>
        <p class="text-secondary small mb-0">An at-a-glance look at stock, deliveries, and recent activity.</p>
    </div>

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
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">PRODUCT</th>
                                <th scope="col">LOCATION</th>
                                <th scope="col" class="text-end">STOCK</th>
                                <th scope="col" class="text-end">REORDER AT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('inventory.edit', $item) }}" class="text-decoration-none fw-semibold">{{ $item->product->name ?? 'N/A' }}</a>
                                    </td>
                                    <td class="text-secondary small">{{ $item->location->name ?? 'N/A' }}</td>
                                    <td class="text-end text-danger fw-semibold">{{ number_format($item->quantity) }}</td>
                                    <td class="text-end text-secondary small">{{ number_format($item->product->reorder_point ?? 0) }}</td>
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
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">TYPE</th>
                                <th scope="col">REFERENCE</th>
                                <th scope="col">DELIVER TO</th>
                                <th scope="col">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingDeliveries as $delivery)
                                <tr>
                                    <td>
                                        @if($delivery['type'] === 'purchase_order')
                                            <span class="badge bg-info-subtle text-info border">PO</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border">Transfer</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $delivery['show_route'] }}" class="text-decoration-none fw-semibold">{{ $delivery['reference'] }}</a>
                                    </td>
                                    <td class="text-secondary small">{{ $delivery['destination'] }}</td>
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

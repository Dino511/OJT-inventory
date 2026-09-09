@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Purchase Orders</h3>
            <p class="text-secondary small mb-0">Order stock from suppliers and receive it into inventory.</p>
        </div>
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New Purchase Order
        </a>
    </div>

    <!-- Main Card Container -->
    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">ALL PURCHASE ORDERS</span>
            <span class="badge bg-light text-dark border font-monospace">{{ $purchaseOrders->count() }} Total</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 80px;">ID</th>
                        <th scope="col">SUPPLIER</th>
                        <th scope="col">COMPANY</th>
                        <th scope="col">WAREHOUSE</th>
                        <th scope="col">ORDER DATE</th>
                        <th scope="col">ITEMS</th>
                        <th scope="col">TOTAL COST</th>
                        <th scope="col">STATUS</th>
                        <th scope="col" class="text-end" style="width: 100px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $order)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">#{{ $order->id }}</span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $order->supplier_name }}</td>
                            <td class="text-secondary small">{{ $order->company->name ?? 'N/A' }}</td>
                            <td class="text-secondary small">{{ $order->location->name ?? 'N/A' }}</td>
                            <td class="text-secondary small">{{ $order->order_date?->format('M j, Y') }}</td>
                            <td class="text-secondary small">{{ $order->items->count() }}</td>
                            <td class="text-secondary small">₱{{ number_format($order->total_cost, 2) }}</td>
                            <td>
                                @php
                                    $statusBadge = match($order->status) {
                                        'received' => ['bg-success-subtle text-success', 'Received'],
                                        'cancelled' => ['bg-secondary-subtle text-secondary', 'Cancelled'],
                                        default => ['bg-warning-subtle text-warning-emphasis', 'Pending'],
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge[0] }} border">{{ $statusBadge[1] }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-outline-github btn-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-cart fs-2 d-block mb-2 text-secondary"></i>
                                No purchase orders yet. Click <strong>New Purchase Order</strong> to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

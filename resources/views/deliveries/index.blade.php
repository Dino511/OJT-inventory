@extends('layouts.app')

@section('title', 'Delivery Tracking')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Delivery Tracking</h3>
            <p class="text-secondary small mb-0">Every purchase order and warehouse&rarr;store stock transfer, in one place.</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-github btn-sm px-3">
            <i class="bi bi-cart me-1"></i> Purchase Orders
        </a>
    </div>

    <!-- Main Card Container -->
    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">ALL DELIVERIES</span>
            <span class="badge bg-light text-dark border font-monospace">{{ $deliveries->count() }} Total</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 90px;">TYPE</th>
                        <th scope="col">REFERENCE</th>
                        <th scope="col">SUPPLIER / SOURCE</th>
                        <th scope="col">COMPANY</th>
                        <th scope="col">DELIVER TO</th>
                        <th scope="col">DATE</th>
                        <th scope="col">EXPECTED DELIVERY</th>
                        <th scope="col">STATUS</th>
                        <th scope="col" class="text-end" style="width: 140px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td>
                                @if($delivery['type'] === 'purchase_order')
                                    <span class="badge bg-info-subtle text-info border">Purchase Order</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border">Stock Transfer</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $delivery['show_route'] }}" class="badge bg-light text-secondary border font-monospace text-decoration-none">{{ $delivery['reference'] }}</a>
                            </td>
                            <td class="fw-semibold text-dark">{{ $delivery['source_or_supplier'] }}</td>
                            <td class="text-secondary small">{{ $delivery['company'] }}</td>
                            <td class="text-secondary small">{{ $delivery['destination'] }}</td>
                            <td class="text-secondary small">{{ $delivery['date']?->format('M j, Y') ?? '—' }}</td>
                            <td class="text-secondary small">
                                @if($delivery['expected_delivery_date'])
                                    {{ $delivery['expected_delivery_date']->format('M j, Y') }}
                                    @if($delivery['status'] === 'pending' && $delivery['expected_delivery_date']->isPast())
                                        <span class="badge bg-danger-subtle text-danger border ms-1">Overdue</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @include('deliveries._status-badge', ['status' => $delivery['status']])
                            </td>
                            <td class="text-end">
                                @if($delivery['type'] === 'purchase_order')
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ $delivery['edit_route'] }}" class="btn btn-outline-github btn-sm" title="Edit dates">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('purchase-orders.destroy', $delivery['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this purchase order?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ $delivery['show_route'] }}" class="btn btn-outline-github btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($delivery['status'] === 'pending' && (auth()->user()->isSuperAdmin() || auth()->user()->isBusinessAdmin()))
                                        <form action="{{ route('stock-transfers.receive', $delivery['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this transfer as received? Store stock will be updated immediately.');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Mark Received">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('stock-transfers.cancel', $delivery['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this transfer? Stock will be returned to the warehouse.');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancel">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($delivery['status'] !== 'pending' && auth()->user()->isSuperAdmin())
                                        <form action="{{ route('stock-transfers.destroy', $delivery['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transfer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-truck fs-2 d-block mb-2 text-secondary"></i>
                                No deliveries yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

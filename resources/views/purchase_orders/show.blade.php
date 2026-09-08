@extends('layouts.app')

@section('title', 'Purchase Order #' . $purchaseOrder->id)

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $purchaseOrder->id }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0">Purchase Order #{{ $purchaseOrder->id }}</h3>
        </div>
        <div class="d-flex gap-2">
            @if($purchaseOrder->isPending())
                <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Cancel this purchase order?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="bi bi-x-lg me-1"></i> Cancel Order
                    </button>
                </form>
                <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Mark this order as received? Stock quantities will be updated immediately.');">
                    @csrf
                    <button type="submit" class="btn btn-github btn-sm px-3">
                        <i class="bi bi-box-seam me-1"></i> Mark as Received
                    </button>
                </form>
            @endif
            @if(auth()->user()->isSuperAdmin())
                <form action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Delete this purchase order?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-github text-danger btn-sm px-3">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Order Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary small">ORDER ITEMS</span>
                    @php
                        $statusBadge = match($purchaseOrder->status) {
                            'received' => ['bg-success-subtle text-success', 'Received'],
                            'cancelled' => ['bg-secondary-subtle text-secondary', 'Cancelled'],
                            default => ['bg-warning-subtle text-warning-emphasis', 'Pending'],
                        };
                    @endphp
                    <span class="badge {{ $statusBadge[0] }} border">{{ $statusBadge[1] }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">PRODUCT</th>
                                <th scope="col" class="text-end">QUANTITY</th>
                                <th scope="col" class="text-end">UNIT COST</th>
                                <th scope="col" class="text-end">LINE TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->product->name ?? 'N/A' }}</div>
                                        <span class="badge bg-light text-secondary border font-monospace">{{ $item->product->code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">₱{{ number_format($item->unit_cost ?? 0, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end">₱{{ number_format($purchaseOrder->total_cost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">ORDER INFO</span>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-5 text-secondary">Supplier</dt>
                        <dd class="col-sm-7">{{ $purchaseOrder->supplier_name }}</dd>

                        <dt class="col-sm-5 text-secondary">Company</dt>
                        <dd class="col-sm-7">{{ $purchaseOrder->company->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5 text-secondary">Deliver To (Warehouse)</dt>
                        <dd class="col-sm-7">{{ $purchaseOrder->location->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5 text-secondary">Order Date</dt>
                        <dd class="col-sm-7">{{ $purchaseOrder->order_date?->format('M j, Y') }}</dd>

                        <dt class="col-sm-5 text-secondary">Expected Delivery</dt>
                        <dd class="col-sm-7">
                            {{ $purchaseOrder->expected_delivery_date?->format('M j, Y') ?? 'N/A' }}
                            @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('deliveries.edit', $purchaseOrder) }}" class="small text-decoration-none ms-1" title="Edit dates">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-secondary">Created By</dt>
                        <dd class="col-sm-7">{{ $purchaseOrder->creator->name ?? 'N/A' }}</dd>

                        @if($purchaseOrder->isReceived())
                            <dt class="col-sm-5 text-secondary">Received By</dt>
                            <dd class="col-sm-7">{{ $purchaseOrder->receiver->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-5 text-secondary">Received At</dt>
                            <dd class="col-sm-7">{{ $purchaseOrder->received_at?->format('M j, Y g:i A') }}</dd>
                        @endif

                        @if($purchaseOrder->notes)
                            <dt class="col-sm-5 text-secondary">Notes</dt>
                            <dd class="col-sm-7">{{ $purchaseOrder->notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($purchaseOrder->supplier)
                <div class="card shadow-sm mt-4">
                    <div class="card-header p-3">
                        <span class="fw-semibold text-secondary small">SUPPLIER DETAILS</span>
                    </div>
                    <div class="card-body p-4">
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5 text-secondary">Name</dt>
                            <dd class="col-sm-7">{{ $purchaseOrder->supplier->name }}</dd>

                            <dt class="col-sm-5 text-secondary">Contact Person</dt>
                            <dd class="col-sm-7">
                                {{ $purchaseOrder->supplier->contact_person }}
                                @if($purchaseOrder->supplier->secondary_contact_person)
                                    <div class="text-muted">{{ $purchaseOrder->supplier->secondary_contact_person }}</div>
                                @endif
                            </dd>

                            <dt class="col-sm-5 text-secondary">Phone</dt>
                            <dd class="col-sm-7">
                                {{ $purchaseOrder->supplier->phone ?? '—' }}
                                @if($purchaseOrder->supplier->secondary_phone)
                                    <div class="text-muted">{{ $purchaseOrder->supplier->secondary_phone }}</div>
                                @endif
                            </dd>

                            <dt class="col-sm-5 text-secondary">Email</dt>
                            <dd class="col-sm-7">{{ $purchaseOrder->supplier->email ?? '—' }}</dd>

                            <dt class="col-sm-5 text-secondary">Address</dt>
                            <dd class="col-sm-7">{{ $purchaseOrder->supplier->address ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        @if($activityLogs->isNotEmpty())
            <!-- Stock Activity produced by receiving this order -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header p-3 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-secondary small">STOCK ACTIVITY</span>
                        <a href="{{ route('inventory.history.index') }}" class="small text-decoration-none">View all inventory history</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 170px;">DATE &amp; TIME</th>
                                    <th scope="col" style="width: 110px;">ACTION</th>
                                    <th scope="col" style="width: 160px;">USER</th>
                                    <th scope="col">DETAILS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activityLogs as $log)
                                    <tr>
                                        <td class="text-secondary small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                        <td>@include('inventory._history-action-badge')</td>
                                        <td class="text-secondary small">{{ $log->user_name ?? 'System' }}</td>
                                        <td class="small">@include('inventory._history-details')</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

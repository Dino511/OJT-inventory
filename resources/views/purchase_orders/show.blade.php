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

    @php
        $statusBadge = match($purchaseOrder->status) {
            'received' => ['bg-success-subtle text-success', 'Received'],
            'cancelled' => ['bg-secondary-subtle text-secondary', 'Cancelled'],
            default => ['bg-warning-subtle text-warning-emphasis', 'Pending'],
        };
    @endphp

    <!-- Compact horizontal metadata bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap gap-4 row-gap-2 align-items-start po-meta-bar">
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Status</div>
                    <span class="badge {{ $statusBadge[0] }} border">{{ $statusBadge[1] }}</span>
                </div>
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Supplier</div>
                    <div class="fw-semibold small">{{ $purchaseOrder->supplier_name }}</div>
                </div>
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Company</div>
                    <div class="fw-semibold small">{{ $purchaseOrder->company->name ?? 'N/A' }}</div>
                </div>
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Deliver To (Warehouse)</div>
                    <div class="fw-semibold small">{{ $purchaseOrder->location->name ?? 'N/A' }}</div>
                </div>
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Order Date</div>
                    <div class="fw-semibold small">{{ $purchaseOrder->order_date?->format('M j, Y') }}</div>
                </div>
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Expected Delivery</div>
                    <div class="fw-semibold small">
                        {{ $purchaseOrder->expected_delivery_date?->format('M j, Y') ?? 'N/A' }}
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('deliveries.edit', $purchaseOrder) }}" class="small text-decoration-none ms-1" title="Edit dates">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="po-meta-item">
                    <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Created By</div>
                    <div class="fw-semibold small">{{ $purchaseOrder->creator->name ?? 'N/A' }}</div>
                </div>
                @if($purchaseOrder->isReceived())
                    <div class="po-meta-item">
                        <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Received By</div>
                        <div class="fw-semibold small">{{ $purchaseOrder->receiver->name ?? 'N/A' }}</div>
                    </div>
                    <div class="po-meta-item">
                        <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Received At</div>
                        <div class="fw-semibold small">{{ $purchaseOrder->received_at?->format('M j, Y g:i A') }}</div>
                    </div>
                @endif
                @if($purchaseOrder->notes)
                    <div class="po-meta-item" style="flex-basis: 100%;">
                        <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Notes</div>
                        <div class="fw-semibold small">{{ $purchaseOrder->notes }}</div>
                    </div>
                @endif

                @if($purchaseOrder->supplier)
                    <div class="ms-auto align-self-center">
                        <button type="button" class="btn btn-outline-github btn-sm" data-bs-toggle="collapse" data-bs-target="#supplierDetailsCollapse" aria-expanded="false" aria-controls="supplierDetailsCollapse">
                            <i class="bi bi-truck me-1"></i> Supplier Details
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </div>
                @endif
            </div>

            @if($purchaseOrder->supplier)
                <div class="collapse mt-3 pt-3 border-top" id="supplierDetailsCollapse">
                    <div class="d-flex flex-wrap gap-4 row-gap-2">
                        <div class="po-meta-item">
                            <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Name</div>
                            <div class="fw-semibold small">{{ $purchaseOrder->supplier->name }}</div>
                        </div>
                        <div class="po-meta-item">
                            <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Contact Person</div>
                            <div class="fw-semibold small">
                                {{ $purchaseOrder->supplier->contact_person }}
                                @if($purchaseOrder->supplier->secondary_contact_person)
                                    <div class="text-muted fw-normal">{{ $purchaseOrder->supplier->secondary_contact_person }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="po-meta-item">
                            <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Phone</div>
                            <div class="fw-semibold small">
                                {{ $purchaseOrder->supplier->phone ?? '—' }}
                                @if($purchaseOrder->supplier->secondary_phone)
                                    <div class="text-muted fw-normal">{{ $purchaseOrder->supplier->secondary_phone }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="po-meta-item">
                            <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Email</div>
                            <div class="fw-semibold small">{{ $purchaseOrder->supplier->email ?? '—' }}</div>
                        </div>
                        <div class="po-meta-item">
                            <div class="text-secondary text-uppercase" style="font-size: 0.7rem;">Address</div>
                            <div class="fw-semibold small">{{ $purchaseOrder->supplier->address ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Full-width Order Items -->
    <div class="card shadow-sm mb-4">
        <div class="card-header p-3">
            <span class="fw-semibold text-secondary small">ORDER ITEMS</span>
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

    @if($activityLogs->isNotEmpty())
        <!-- Stock Activity produced by receiving this order -->
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
    @endif
@endsection

@push('styles')
    <style>
        .po-meta-item {
            min-width: 120px;
        }
        .po-meta-bar .bi-chevron-down {
            transition: transform 0.15s ease;
        }
        .po-meta-bar .btn[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
    </style>
@endpush

@push('scripts')
    <script>
        const supplierCollapseToggle = document.querySelector('[data-bs-target="#supplierDetailsCollapse"]');
        const supplierCollapseEl = document.getElementById('supplierDetailsCollapse');
        if (supplierCollapseToggle && supplierCollapseEl) {
            supplierCollapseEl.addEventListener('show.bs.collapse', () => supplierCollapseToggle.setAttribute('aria-expanded', 'true'));
            supplierCollapseEl.addEventListener('hide.bs.collapse', () => supplierCollapseToggle.setAttribute('aria-expanded', 'false'));
        }
    </script>
@endpush

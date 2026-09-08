@extends('layouts.app')

@section('title', 'Stock Transfer #' . $stockTransfer->id)

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}" class="text-decoration-none">Delivery Tracking</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transfer #{{ $stockTransfer->id }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0">Stock Transfer #{{ $stockTransfer->id }}</h3>
        </div>
        <div class="d-flex gap-2">
            @if($stockTransfer->isPending() && (auth()->user()->isSuperAdmin() || auth()->user()->isBusinessAdmin()))
                <form action="{{ route('stock-transfers.cancel', $stockTransfer) }}" method="POST" onsubmit="return confirm('Cancel this transfer? Stock will be returned to the warehouse.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="bi bi-x-lg me-1"></i> Cancel Transfer
                    </button>
                </form>
                <form action="{{ route('stock-transfers.receive', $stockTransfer) }}" method="POST" onsubmit="return confirm('Mark this transfer as received? Store stock will be updated immediately.');">
                    @csrf
                    <button type="submit" class="btn btn-github btn-sm px-3">
                        <i class="bi bi-box-seam me-1"></i> Mark as Received
                    </button>
                </form>
            @endif
            @if(! $stockTransfer->isPending() && auth()->user()->isSuperAdmin())
                <form action="{{ route('stock-transfers.destroy', $stockTransfer) }}" method="POST" onsubmit="return confirm('Delete this transfer?');">
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
        <!-- Transfer Items -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary small">TRANSFER ITEMS</span>
                    @include('deliveries._status-badge', ['status' => $stockTransfer->status])
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">PRODUCT</th>
                                <th scope="col">FROM (WAREHOUSE)</th>
                                <th scope="col" class="text-end">QUANTITY</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockTransfer->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->product->name ?? 'N/A' }}</div>
                                        <span class="badge bg-light text-secondary border font-monospace">{{ $item->product->code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-secondary small">{{ $item->fromLocation->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transfer Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">TRANSFER INFO</span>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-5 text-secondary">Company</dt>
                        <dd class="col-sm-7">{{ $stockTransfer->company->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5 text-secondary">Deliver To (Store)</dt>
                        <dd class="col-sm-7">{{ $stockTransfer->toLocation->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5 text-secondary">Dispatched</dt>
                        <dd class="col-sm-7">{{ $stockTransfer->dispatched_at?->format('M j, Y g:i A') }}</dd>

                        <dt class="col-sm-5 text-secondary">Dispatched By</dt>
                        <dd class="col-sm-7">{{ $stockTransfer->creator->name ?? 'N/A' }}</dd>

                        @if($stockTransfer->isReceived())
                            <dt class="col-sm-5 text-secondary">Received By</dt>
                            <dd class="col-sm-7">{{ $stockTransfer->receiver->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-5 text-secondary">Received At</dt>
                            <dd class="col-sm-7">{{ $stockTransfer->received_at?->format('M j, Y g:i A') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        @if($activityLogs->isNotEmpty())
            <!-- Stock Activity produced by this transfer -->
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
                                    <th scope="col" style="width: 130px;">ACTION</th>
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

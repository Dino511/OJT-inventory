@extends('layouts.app')

@section('title', 'Edit Delivery Dates')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}" class="text-decoration-none">Delivery Tracking</a></li>
                <li class="breadcrumb-item active" aria-current="page">PO #{{ $purchaseOrder->id }}</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Edit Delivery Dates &mdash; PO #{{ $purchaseOrder->id }}</h3>
        <p class="text-secondary small mb-0">{{ $purchaseOrder->supplier_name }} &middot; {{ $purchaseOrder->company->name ?? 'N/A' }} &middot; {{ $purchaseOrder->location->name ?? 'N/A' }}</p>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">DELIVERY DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('deliveries.update', $purchaseOrder) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="order_date" class="form-label small fw-semibold">Date of Order</label>
                            <input type="date" id="order_date" name="order_date"
                                   class="form-control form-control-sm @error('order_date') is-invalid @enderror"
                                   value="{{ old('order_date', $purchaseOrder->order_date?->toDateString()) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expected_delivery_date" class="form-label small fw-semibold">Expected Date of Receiving <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="date" id="expected_delivery_date" name="expected_delivery_date"
                                   class="form-control form-control-sm @error('expected_delivery_date') is-invalid @enderror"
                                   value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date?->toDateString()) }}">
                            <div class="form-text small">When this order is expected to arrive at {{ $purchaseOrder->location->name ?? 'its destination' }}.</div>
                            @error('expected_delivery_date')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Save Dates
                            </button>
                            <a href="{{ route('deliveries.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

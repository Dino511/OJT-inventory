@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0">{{ $product->name }}</h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.history', $product->id) }}" class="btn btn-outline-github btn-sm px-3">
                <i class="bi bi-clock-history me-1"></i> History
            </a>
            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-github btn-sm px-3">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Product Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary small">PRODUCT DETAILS</span>
                    @if($product->is_active)
                        <span class="badge bg-success-subtle text-success border">Active</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            @if($product->photo)
                                <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" class="img-fluid rounded border">
                            @else
                                <div class="d-flex align-items-center justify-content-center rounded border bg-light text-muted" style="height: 160px;">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <dl class="row mb-0 small">
                                <dt class="col-sm-4 text-secondary">SKU / Code</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-light text-secondary border font-monospace">{{ $product->code ?? 'N/A' }}</span>
                                </dd>

                                <dt class="col-sm-4 text-secondary">Company</dt>
                                <dd class="col-sm-8">{{ $product->company->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4 text-secondary">Category</dt>
                                <dd class="col-sm-8">
                                    @if($product->category)
                                        <span class="badge bg-secondary-subtle text-secondary border px-2">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4 text-secondary">Base Unit</dt>
                                <dd class="col-sm-8">
                                    <span class="fw-semibold">{{ $product->unit_value ?? 1 }}</span> {{ $product->baseUnit->name ?? 'Piece' }}
                                    @foreach($product->unitConversions as $conversion)
                                        <div class="text-muted" style="font-size: 0.8rem;">
                                            = {{ (float) $conversion->factor * ($product->unit_value ?? 1) }} {{ $conversion->toUnit->name ?? '' }}
                                        </div>
                                    @endforeach
                                </dd>

                                <dt class="col-sm-4 text-secondary">Reorder Point</dt>
                                <dd class="col-sm-8">{{ number_format($product->reorder_point ?? 0, 2) }}</dd>

                                <dt class="col-sm-4 text-secondary">Selling Price</dt>
                                <dd class="col-sm-8">₱{{ number_format($product->selling_price ?? 0, 2) }}</dd>

                                <dt class="col-sm-4 text-secondary">Cost</dt>
                                <dd class="col-sm-8">{{ $product->cost !== null ? '₱' . number_format($product->cost, 2) : 'N/A' }}</dd>

                                <dt class="col-sm-4 text-secondary">Description</dt>
                                <dd class="col-sm-8">{{ $product->description ?: 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock by Location -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary small">STOCK BY LOCATION</span>
                    <span class="badge bg-light text-dark border font-monospace">
                        {{ $product->inventories->sum('quantity') }} Total
                    </span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($product->inventories as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                            <span class="small">{{ $item->location->name ?? 'N/A' }}</span>
                            <span class="badge {{ $item->quantity > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border">
                                {{ $item->quantity }} units
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted small py-4">
                            No stock recorded at any location yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

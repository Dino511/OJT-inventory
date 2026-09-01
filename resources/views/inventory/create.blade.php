@extends('layouts.app')

@section('title', 'Add Inventory Record')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Add Inventory Record</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white p-3 border-bottom">
                    <span class="fw-semibold text-secondary small text-uppercase">New Inventory Details</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('inventory.store') }}" method="POST">
                        @csrf

                        <!-- Product Selection -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label small fw-semibold">Product</label>
                            <select id="product_id" name="product_id" class="form-select form-select-sm @error('product_id') is-invalid @enderror" required>
                                <option value="">Select a product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} {{ $product->sku ? '('.$product->sku.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location Selection -->
                        <div class="mb-3">
                            <label for="location_id" class="form-label small fw-semibold">Location</label>
                            <select id="location_id" name="location_id" class="form-select form-select-sm @error('location_id') is-invalid @enderror" required>
                                <option value="">Select a location...</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label for="quantity" class="form-label small fw-semibold">Quantity</label>
                            <input type="number"
                                   id="quantity"
                                   name="quantity"
                                   min="0"
                                   class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                                   value="{{ old('quantity', 0) }}"
                                   required>
                            @error('quantity')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-plus-lg me-1"></i> Save Record
                            </button>
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

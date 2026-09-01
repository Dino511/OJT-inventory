@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Edit Product</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">EDIT PRODUCT DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Company Selection -->
                        <div class="mb-3">
                            <label for="company_id" class="form-label small fw-semibold">Company</label>
                            <select id="company_id" 
                                    name="company_id" 
                                    class="form-select form-select-sm @error('company_id') is-invalid @enderror" 
                                    required>
                                <option value="" disabled>Select a Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->company_id }}" {{ old('company_id', $product->company_id) == $company->company_id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category Selection -->
                        <div class="mb-3">
    <label for="category_id" class="form-label small fw-semibold">Category</label>
    <select id="category_id" 
            name="category_id" 
            class="form-select form-select-sm @error('category_id') is-invalid @enderror" 
            required>
        <option value="" disabled {{ old('category_id', $product->category_id ?? '') === '' ? 'selected' : '' }}>Select a Category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <div class="invalid-feedback d-block small">{{ $message }}</div>
    @enderror
</div>

                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Product Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $product->name) }}" 
                                   placeholder="e.g. Wireless Mouse" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description Field -->
                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3" 
                                      class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                      placeholder="Enter product details, specifications, or notes...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- SKU Code -->
                        <div class="mb-3">
                            <label for="code" class="form-label small fw-semibold">SKU / Code</label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   class="form-control form-control-sm @error('code') is-invalid @enderror" 
                                   value="{{ old('code', $product->code) }}" 
                                   placeholder="e.g. PROD-001" 
                                   required>
                            @error('code')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit Value & Base Unit Row -->
                        <div class="row mb-3">
                            <!-- Unit Value / Qty Field -->
                            <div class="col-md-4">
                                <label for="unit_value" class="form-label small fw-semibold">Unit Value / Qty</label>
                                <input type="number" 
                                       step="any" 
                                       id="unit_value" 
                                       name="unit_value" 
                                       class="form-control form-control-sm @error('unit_value') is-invalid @enderror" 
                                       value="{{ old('unit_value', $product->unit_value ?? 1) }}" 
                                       placeholder="e.g. 100" 
                                       required>
                                @error('unit_value')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Base Unit Field with Browse Modal Trigger -->
                            <div class="col-md-8">
                                <label for="base_unit_name_display" class="form-label small fw-semibold">Base Unit</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" 
                                           id="base_unit_name_display" 
                                           class="form-control @error('base_unit_id') is-invalid @enderror" 
                                           placeholder="Click 'Browse' to select..." 
                                           readonly 
                                           required>
                                    <input type="hidden" 
                                           id="base_unit_id" 
                                           name="base_unit_id" 
                                           value="{{ old('base_unit_id', $product->base_unit_id ?? '') }}">
                                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#selectBaseUnitModal">
                                        <i class="bi bi-search me-1"></i> Browse
                                    </button>
                                </div>
                                @error('base_unit_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Reorder Point -->
                        <div class="mb-3">
                            <label for="reorder_point" class="form-label small fw-semibold">Reorder Point</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="reorder_point" 
                                   name="reorder_point" 
                                   class="form-control form-control-sm @error('reorder_point') is-invalid @enderror" 
                                   value="{{ old('reorder_point', $product->reorder_point) }}" 
                                   placeholder="e.g. 10.00">
                            @error('reorder_point')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pricing Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="selling_price" class="form-label small fw-semibold">Selling Price</label>
                                <input type="number"
                                       step="0.01"
                                       id="selling_price"
                                       name="selling_price"
                                       class="form-control form-control-sm @error('selling_price') is-invalid @enderror"
                                       value="{{ old('selling_price', $product->selling_price ?? 0) }}"
                                       placeholder="e.g. 199.00">
                                @error('selling_price')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="cost" class="form-label small fw-semibold">Cost</label>
                                <input type="number"
                                       step="0.01"
                                       id="cost"
                                       name="cost"
                                       class="form-control form-control-sm @error('cost') is-invalid @enderror"
                                       value="{{ old('cost', $product->cost ?? '') }}"
                                       placeholder="e.g. 120.00">
                                @error('cost')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Update Product
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Select Base Unit Modal -->
    <div class="modal fade" id="selectBaseUnitModal" tabindex="-1" aria-labelledby="selectBaseUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold" id="selectBaseUnitModalLabel">Select Base Unit</h6>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Unit Name</th>
                                    <th>Code / Symbol</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($baseUnits as $unit)
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $unit->name }}</td>
                                        <td><span class="text-muted">{{ $unit->code ?? '-' }}</span></td>
                                        <td class="text-end pe-3">
                                            <button type="button" 
                                                    class="btn btn-sm btn-github select-unit-btn py-0 px-2" 
                                                    data-id="{{ $unit->id }}" 
                                                    data-name="{{ $unit->name }} @if(!empty($unit->code))({{ $unit->code }})@endif"
                                                    data-bs-dismiss="modal">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No base units available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let hiddenInputId = document.getElementById('base_unit_id').value;
        if (hiddenInputId) {
            let matchingBtn = document.querySelector(`.select-unit-btn[data-id="${hiddenInputId}"]`);
            if (matchingBtn) {
                document.getElementById('base_unit_name_display').value = matchingBtn.getAttribute('data-name');
            }
        }

        document.querySelectorAll('.select-unit-btn').forEach(button => {
            button.addEventListener('click', function() {
                let unitId = this.getAttribute('data-id');
                let unitName = this.getAttribute('data-name');

                document.getElementById('base_unit_id').value = unitId;
                document.getElementById('base_unit_name_display').value = unitName;
            });
        });
    });
    </script>
@endsection
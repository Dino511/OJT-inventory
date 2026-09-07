@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Create Product</h3>
    </div>

    <div class="card shadow-sm">
        <div class="card-header p-3">
            <span class="fw-semibold text-secondary small">NEW PRODUCT DETAILS</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Left column: identity -->
                    <div class="col-lg-6">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="company_id" class="form-label small fw-semibold">Company</label>
                                <select id="company_id"
                                        name="company_id"
                                        class="form-select form-select-sm @error('company_id') is-invalid @enderror"
                                        required>
                                    <option value="" disabled {{ old('company_id') ? '' : 'selected' }}>Select a Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->company_id }}" {{ old('company_id') == $company->company_id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label small fw-semibold">Category</label>
                                <select name="category_id" id="category_id" class="form-select form-select-sm @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label small fw-semibold">Product Name</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-control form-control-sm @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="e.g. Wireless Mouse"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="code" class="form-label small fw-semibold">SKU</label>
                                <input type="text"
                                    id="code"
                                    name="code"
                                    class="form-control form-control-sm @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}"
                                    placeholder="e.g. PROD-001"
                                    required>
                                @error('code')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold">Description</label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="form-control form-control-sm @error('description') is-invalid @enderror"
                                      placeholder="Enter product details, specifications, or notes...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Product Photo -->
                        <div class="mb-3 mb-lg-0">
                            <label for="photo" class="form-label small fw-semibold">Photo</label>
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <input type="file"
                                           id="photo"
                                           name="photo"
                                           accept="image/*"
                                           class="form-control form-control-sm @error('photo') is-invalid @enderror">
                                    <div class="form-text small">Optional. JPG, PNG, or WebP, up to 4MB.</div>
                                    @error('photo')
                                        <div class="invalid-feedback d-block small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <img id="photoPreview" src="" alt="Preview" class="img-thumbnail d-none" style="max-height: 60px;">
                            </div>
                        </div>
                    </div>

                    <!-- Right column: units, conversions, pricing -->
                    <div class="col-lg-6 mt-4 mt-lg-0">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="unit_value" class="form-label small fw-semibold">Unit Value / Qty</label>
                                <input type="number"
                                       step="any"
                                       id="unit_value"
                                       name="unit_value"
                                       class="form-control form-control-sm @error('unit_value') is-invalid @enderror"
                                       value="{{ old('unit_value', 1) }}"
                                       placeholder="e.g. 100"
                                       required>
                                @error('unit_value')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Base Unit Selection with Browse Modal Trigger -->
                            <div class="col-md-8">
                                <label for="selected_base_unit_name" class="form-label small fw-semibold">Base Unit</label>

                                <!-- Hidden Input submitted to Controller -->
                                <input type="hidden"
                                       id="base_unit_id"
                                       name="base_unit_id"
                                       value="{{ old('base_unit_id') }}">

                                <!-- Display Input & Browse Button -->
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           id="selected_base_unit_name"
                                           class="form-control @error('base_unit_id') is-invalid @enderror"
                                           placeholder="Click 'Browse'..."
                                           readonly
                                           data-bs-toggle="modal"
                                           data-bs-target="#selectBaseUnitModal">
                                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#selectBaseUnitModal">
                                        <i class="bi bi-search me-1"></i> Browse
                                    </button>
                                </div>
                                @error('base_unit_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Applicable Unit Conversions -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Applicable Unit Conversions <span class="text-muted fw-normal">(optional)</span></label>
                            <div id="conversionsList" class="border rounded p-2 d-flex flex-wrap column-gap-4 row-gap-1" style="max-height: 110px; overflow-y: auto;">
                                <span class="text-muted small">Select a base unit first.</span>
                            </div>
                            <div class="form-text small">Check which of this base unit's conversions apply to this product.</div>
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
                                       value="{{ old('selling_price', 0) }}"
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
                                       value="{{ old('cost') }}"
                                       placeholder="e.g. 120.00">
                                @error('cost')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center gap-2 mt-2 pt-3 border-top">
                    <button type="submit" class="btn btn-github btn-sm px-3">
                        <i class="bi bi-plus-lg me-1"></i> Save Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-github btn-sm">
                        Cancel
                    </a>
                </div>
            </form>
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
    // Every registered unit conversion — filtered client-side to whichever
    // ones start from the currently selected base unit.
    const allConversions = @json($conversionOptions);
    const selectedConversionIds = @json(old('unit_conversion_ids', []));

    function syncConversionOptions() {
        const baseUnitId = document.getElementById('base_unit_id').value;
        const list = document.getElementById('conversionsList');
        const matches = allConversions.filter(c => String(c.from_unit_id) === String(baseUnitId));

        if (!baseUnitId) {
            list.innerHTML = '<span class="text-muted small">Select a base unit first.</span>';
        } else if (matches.length === 0) {
            list.innerHTML = '<span class="text-muted small">No conversions defined yet for this base unit.</span>';
        } else {
            list.innerHTML = matches.map(c => `
                <div class="form-check" style="min-width: 170px;">
                    <input type="checkbox" name="unit_conversion_ids[]" value="${c.id}" id="conversion_${c.id}"
                           class="form-check-input" ${selectedConversionIds.includes(c.id) || selectedConversionIds.includes(String(c.id)) ? 'checked' : ''}>
                    <label for="conversion_${c.id}" class="form-check-label small">${c.label}</label>
                </div>
            `).join('');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Pre-populate display field on page load if old() exists (e.g. after validation failure)
        let hiddenInputId = document.getElementById('base_unit_id').value;
        if (hiddenInputId) {
            let matchingBtn = document.querySelector(`.select-unit-btn[data-id="${hiddenInputId}"]`);
            if (matchingBtn) {
                document.getElementById('selected_base_unit_name').value = matchingBtn.getAttribute('data-name');
            }
        }
        syncConversionOptions();

        // Handle selection click events from the modal items
        const selectButtons = document.querySelectorAll('.select-unit-btn');
        selectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const unitId = this.getAttribute('data-id');
                const unitName = this.getAttribute('data-name');

                // Set value for hidden input validated by Laravel controller
                document.getElementById('base_unit_id').value = unitId;

                // Set value for visual text input field
                document.getElementById('selected_base_unit_name').value = unitName;

                syncConversionOptions();
            });
        });

        // Live preview of the chosen photo before it's uploaded.
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photoPreview');
        photoInput?.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                photoPreview.classList.add('d-none');
                photoPreview.src = '';
                return;
            }
            photoPreview.src = URL.createObjectURL(file);
            photoPreview.classList.remove('d-none');
        });
    });
    </script>
@endsection
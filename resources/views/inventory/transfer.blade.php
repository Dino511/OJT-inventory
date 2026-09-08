@extends('layouts.app')

@section('title', 'Transfer Stock')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Transfer</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Transfer Stock</h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-3 border-bottom">
            <span class="fw-semibold text-secondary small text-uppercase">Transfer Details</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('inventory.transfer.store', $inventory->id) }}" method="POST" id="transferForm">
                @csrf

                <div id="transferRows">
                    <!-- Row 1: the product this transfer was started from — fixed, matching the original page behavior -->
                    <div class="transfer-row row g-2 align-items-start mb-3">
                        <input type="hidden" name="product_id[]" value="{{ $inventory->product_id }}">

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Product</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $inventory->product->name ?? 'N/A' }}" disabled>
                        </div>

                        <div class="col-md-4">
                            <label for="from_location_id" class="form-label small fw-semibold">From Location</label>
                            <select id="from_location_id" name="from_location_id[]" class="form-select form-select-sm from-location-select" required {{ $sourceOptions->isEmpty() ? 'disabled' : '' }}>
                                @forelse($sourceOptions as $source)
                                    <option value="{{ $source->location_id }}" data-quantity="{{ $source->quantity }}"
                                        {{ (old('from_location_id.0', $inventory->location_id)) == $source->location_id ? 'selected' : '' }}>
                                        {{ $source->location->name ?? 'N/A' }} ({{ $source->location && $source->location->isWarehouse() ? 'Warehouse' : 'Store' }}) — {{ $source->quantity }} available
                                    </option>
                                @empty
                                    <option value="">No locations currently hold stock of this product</option>
                                @endforelse
                            </select>
                            <div class="form-text small">
                                <i class="bi bi-box-seam me-1"></i>
                                <span class="available-quantity">{{ old('from_location_id.0', $inventory->location_id) == $inventory->location_id ? $inventory->quantity : '' }}</span> units available
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Quantity to Transfer</label>
                            <input type="number" name="quantity[]" min="1" class="form-control form-control-sm quantity-input"
                                   value="{{ old('quantity.0', $inventory->quantity) }}" required>
                        </div>

                        <div class="col-md-1"></div>
                    </div>
                </div>

                <button type="button" id="addProductRow" class="btn btn-outline-github btn-sm mb-4" {{ $products->count() < 1 ? 'disabled' : '' }}>
                    <i class="bi bi-plus-lg me-1"></i> Add Another Product
                </button>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="to_location_id" class="form-label small fw-semibold">Transfer To</label>
                        <select id="to_location_id" name="to_location_id" class="form-select form-select-sm @error('to_location_id') is-invalid @enderror" required>
                            <option value="">Select a location...</option>
                            @forelse($locations as $location)
                                <option value="{{ $location->id }}" {{ old('to_location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }} ({{ $location->isWarehouse() ? 'Warehouse' : 'Store' }})
                                </option>
                            @empty
                                <option value="" disabled>No other locations available</option>
                            @endforelse
                        </select>
                        @error('to_location_id')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-github btn-sm px-3" {{ $sourceOptions->isEmpty() || $locations->count() < 2 ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-left-right me-1"></i> Transfer Stock
                    </button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-github btn-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Every product in this company and, per product, which locations
        // currently hold stock of it — lets each "Add Another Product" row
        // populate its own From Location choices without a round-trip.
        const productOptions = @json($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name]));
        const stockByProduct = @json($stockByProduct);

        const toSelect = document.getElementById('to_location_id');
        const rowsContainer = document.getElementById('transferRows');
        const addRowBtn = document.getElementById('addProductRow');

        function syncFromLocation(row) {
            const fromSelect = row.querySelector('.from-location-select');
            const quantityInput = row.querySelector('.quantity-input');
            const availableQuantity = row.querySelector('.available-quantity');
            const option = fromSelect?.selectedOptions[0];
            const available = parseInt(option?.dataset.quantity || '0', 10);

            if (availableQuantity) availableQuantity.textContent = available;
            if (quantityInput) {
                quantityInput.max = available;
                if (parseInt(quantityInput.value || '0', 10) > available) {
                    quantityInput.value = available;
                }
            }

            // A location can't be both a source and the destination.
            Array.from(toSelect?.options || []).forEach(opt => {
                opt.disabled = opt.value !== '' && opt.value === fromSelect.value;
            });
            if (toSelect.value === fromSelect.value) {
                toSelect.value = '';
            }
        }

        function populateFromLocations(row, productId) {
            const fromSelect = row.querySelector('.from-location-select');
            const locations = stockByProduct[productId] || [];

            fromSelect.innerHTML = '';
            if (locations.length === 0) {
                fromSelect.innerHTML = '<option value="">No locations currently hold stock of this product</option>';
                fromSelect.disabled = true;
            } else {
                fromSelect.disabled = false;
                locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.location_id;
                    opt.dataset.quantity = loc.quantity;
                    const typeLabel = loc.location_type === 'warehouse' ? 'Warehouse' : 'Store';
                    opt.textContent = `${loc.location_name} (${typeLabel}) — ${loc.quantity} available`;
                    fromSelect.appendChild(opt);
                });
            }

            syncFromLocation(row);
        }

        function addProductRow() {
            const row = document.createElement('div');
            row.className = 'transfer-row row g-2 align-items-start mb-3';
            row.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Product</label>
                    <select name="product_id[]" class="form-select form-select-sm product-select" required>
                        <option value="">Select a product...</option>
                        ${productOptions.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">From Location</label>
                    <select name="from_location_id[]" class="form-select form-select-sm from-location-select" required disabled>
                        <option value="">Select a product first...</option>
                    </select>
                    <div class="form-text small">
                        <i class="bi bi-box-seam me-1"></i>
                        <span class="available-quantity"></span> units available
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Quantity to Transfer</label>
                    <input type="number" name="quantity[]" min="1" class="form-control form-control-sm quantity-input" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;

            rowsContainer.appendChild(row);

            row.querySelector('.product-select').addEventListener('change', (e) => {
                populateFromLocations(row, e.target.value);
            });
            row.querySelector('.from-location-select').addEventListener('change', () => syncFromLocation(row));
            row.querySelector('.remove-row').addEventListener('click', () => {
                row.remove();
                syncToLocationDisabling();
            });
        }

        function syncToLocationDisabling() {
            const usedFromLocations = Array.from(document.querySelectorAll('.from-location-select'))
                .map(sel => sel.value)
                .filter(Boolean);

            Array.from(toSelect?.options || []).forEach(opt => {
                opt.disabled = opt.value !== '' && usedFromLocations.includes(opt.value);
            });
            if (usedFromLocations.includes(toSelect.value)) {
                toSelect.value = '';
            }
        }

        addRowBtn?.addEventListener('click', addProductRow);

        document.querySelectorAll('.from-location-select').forEach(sel => {
            sel.addEventListener('change', () => {
                syncFromLocation(sel.closest('.transfer-row'));
                syncToLocationDisabling();
            });
            syncFromLocation(sel.closest('.transfer-row'));
        });
    </script>
@endpush

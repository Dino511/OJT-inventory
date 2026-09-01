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

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white p-3 border-bottom">
                    <span class="fw-semibold text-secondary small text-uppercase">Transfer Details</span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Product</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $inventory->product->name ?? 'N/A' }}" disabled>
                    </div>

                    <form action="{{ route('inventory.transfer.store', $inventory->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="from_location_id" class="form-label small fw-semibold">From Location</label>
                            <select id="from_location_id" name="from_location_id" class="form-select form-select-sm @error('from_location_id') is-invalid @enderror" required {{ $sourceOptions->isEmpty() ? 'disabled' : '' }}>
                                @forelse($sourceOptions as $source)
                                    <option value="{{ $source->location_id }}" data-quantity="{{ $source->quantity }}"
                                        {{ (old('from_location_id', $inventory->location_id)) == $source->location_id ? 'selected' : '' }}>
                                        {{ $source->location->name ?? 'N/A' }} ({{ $source->quantity }} available)
                                    </option>
                                @empty
                                    <option value="">No locations currently hold stock of this product</option>
                                @endforelse
                            </select>
                            @error('from_location_id')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-box-seam me-1"></i>
                                <span id="availableQuantity">{{ old('from_location_id', $inventory->location_id) == $inventory->location_id ? $inventory->quantity : '' }}</span> units currently available at this location
                            </span>
                        </div>

                        <div class="mb-3">
                            <label for="to_location_id" class="form-label small fw-semibold">Transfer To</label>
                            <select id="to_location_id" name="to_location_id" class="form-select form-select-sm @error('to_location_id') is-invalid @enderror" required>
                                <option value="">Select a location...</option>
                                @forelse($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('to_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @empty
                                    <option value="" disabled>No other locations available</option>
                                @endforelse
                            </select>
                            @error('to_location_id')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label small fw-semibold">Quantity to Transfer</label>
                            <input type="number" id="quantity" name="quantity" min="1"
                                   class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                                   value="{{ old('quantity', $inventory->quantity) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const fromSelect = document.getElementById('from_location_id');
        const toSelect = document.getElementById('to_location_id');
        const quantityInput = document.getElementById('quantity');
        const availableQuantity = document.getElementById('availableQuantity');

        function syncFromLocation() {
            const option = fromSelect?.selectedOptions[0];
            const available = parseInt(option?.dataset.quantity || '0', 10);

            availableQuantity.textContent = available;
            quantityInput.max = available;
            if (parseInt(quantityInput.value || '0', 10) > available) {
                quantityInput.value = available;
            }

            // A location can't be both the source and the destination.
            Array.from(toSelect?.options || []).forEach(opt => {
                opt.disabled = opt.value !== '' && opt.value === fromSelect.value;
            });
            if (toSelect.value === fromSelect.value) {
                toSelect.value = '';
            }
        }

        fromSelect?.addEventListener('change', syncFromLocation);
        syncFromLocation();
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Edit Unit Conversion')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Edit Unit Conversion</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('unit-conversions.index') }}" class="text-decoration-none">Unit Conversions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">CONVERSION DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('unit-conversions.update', $conversion->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-2 align-items-end mb-1">
                            <div class="col-3">
                                <label class="form-label small fw-semibold">Qty</label>
                                <input type="text" class="form-control form-control-sm" value="1" disabled>
                            </div>
                            <div class="col-9">
                                <label for="from_unit_id" class="form-label small fw-semibold">From Unit</label>
                                <select id="from_unit_id" name="from_unit_id" class="form-select form-select-sm @error('from_unit_id') is-invalid @enderror" required>
                                    <option value="">Select a unit...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('from_unit_id', $conversion->from_unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} {{ $unit->code ? '('.$unit->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_unit_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-center text-secondary small my-2">equals</div>

                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-3">
                                <label for="factor" class="form-label small fw-semibold">Qty</label>
                                <input type="number" id="factor" name="factor" step="any" min="0.0001"
                                       class="form-control form-control-sm @error('factor') is-invalid @enderror"
                                       value="{{ old('factor', (float) $conversion->factor) }}" required>
                                @error('factor')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-9">
                                <label for="to_unit_id" class="form-label small fw-semibold">To Unit</label>
                                <select id="to_unit_id" name="to_unit_id" class="form-select form-select-sm @error('to_unit_id') is-invalid @enderror" required>
                                    <option value="">Select a unit...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('to_unit_id', $conversion->to_unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} {{ $unit->code ? '('.$unit->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_unit_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Update Conversion
                            </button>
                            <a href="{{ route('unit-conversions.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Conversion Calculator -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small"><i class="bi bi-calculator me-1"></i> CONVERSION CALCULATOR</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">Pick any two units to convert between, using the conversion rules already defined.</p>

                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-5">
                            <input type="number" id="calcQtyFrom" class="form-control form-control-sm" value="1" step="any" min="0">
                        </div>
                        <div class="col-7">
                            <select id="calcFromUnit" class="form-select form-select-sm">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $conversion->from_unit_id == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} {{ $unit->code ? '('.$unit->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-center text-secondary small my-2">
                        <i class="bi bi-arrow-down-up"></i> equals
                    </div>

                    <div class="row g-2 align-items-end mb-2">
                        <div class="col-5">
                            <input type="number" id="calcQtyTo" class="form-control form-control-sm" value="" step="any" min="0">
                        </div>
                        <div class="col-7">
                            <select id="calcToUnit" class="form-select form-select-sm">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $conversion->to_unit_id == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} {{ $unit->code ? '('.$unit->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <p id="calcHint" class="text-danger small mt-3 mb-0 d-none">
                        <i class="bi bi-exclamation-triangle me-1"></i> No conversion rule exists between these two units yet.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const conversions = @json($calculatorConversions);

            const calcFromUnit = document.getElementById('calcFromUnit');
            const calcToUnit = document.getElementById('calcToUnit');
            const calcQtyFrom = document.getElementById('calcQtyFrom');
            const calcQtyTo = document.getElementById('calcQtyTo');
            const calcHint = document.getElementById('calcHint');

            function round(n) {
                return Math.round(n * 10000) / 10000;
            }

            // Looks up a direct rule (from -> to) or its inverse (to -> from), so
            // a single "1 Box = 12 Piece" rule answers both directions.
            function findFactor(fromId, toId) {
                if (fromId === toId) return 1;

                const direct = conversions.find(c => c.from === fromId && c.to === toId);
                if (direct) return direct.factor;

                const inverse = conversions.find(c => c.from === toId && c.to === fromId);
                if (inverse && inverse.factor !== 0) return 1 / inverse.factor;

                return null;
            }

            function currentFactor() {
                return findFactor(parseInt(calcFromUnit.value, 10), parseInt(calcToUnit.value, 10));
            }

            function recalcTo() {
                const factor = currentFactor();
                calcHint.classList.toggle('d-none', factor !== null);
                if (factor === null) {
                    calcQtyTo.value = '';
                    return;
                }

                const value = parseFloat(calcQtyFrom.value);
                calcQtyTo.value = isNaN(value) ? '' : round(value * factor);
            }

            function recalcFrom() {
                const factor = currentFactor();
                calcHint.classList.toggle('d-none', factor !== null);
                if (factor === null) {
                    calcQtyFrom.value = '';
                    return;
                }

                const value = parseFloat(calcQtyTo.value);
                calcQtyFrom.value = isNaN(value) ? '' : round(value / factor);
            }

            calcQtyFrom.addEventListener('input', recalcTo);
            calcQtyTo.addEventListener('input', recalcFrom);
            calcFromUnit.addEventListener('change', recalcTo);
            calcToUnit.addEventListener('change', recalcTo);

            recalcTo();
        })();
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Add Unit Conversion')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('unit-conversions.index') }}" class="text-decoration-none">Unit Conversions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Create Unit Conversion</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">NEW CONVERSION DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('unit-conversions.store') }}" method="POST">
                        @csrf

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
                                        <option value="{{ $unit->id }}" {{ old('from_unit_id') == $unit->id ? 'selected' : '' }}>
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
                                       value="{{ old('factor') }}" placeholder="e.g. 12" required>
                                @error('factor')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-9">
                                <label for="to_unit_id" class="form-label small fw-semibold">To Unit</label>
                                <select id="to_unit_id" name="to_unit_id" class="form-select form-select-sm @error('to_unit_id') is-invalid @enderror" required>
                                    <option value="">Select a unit...</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('to_unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }} {{ $unit->code ? '('.$unit->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_unit_id')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <p class="text-muted small">Example: From Unit = Box, To Unit = Piece, Qty = 12 &rarr; "1 Box = 12 Piece".</p>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-plus-lg me-1"></i> Save Conversion
                            </button>
                            <a href="{{ route('unit-conversions.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

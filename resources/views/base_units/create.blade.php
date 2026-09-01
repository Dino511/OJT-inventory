@extends('layouts.app')

@section('title', 'Add New Base Unit')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('base-units.index') }}" class="text-decoration-none">Base Units</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Create Base Unit</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">NEW BASE UNIT DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('base-units.store') }}" method="POST">
                        @csrf

                        <!-- Unit Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Unit Name</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="e.g. Kilogram, Piece, Liter"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit Code -->
                        <div class="mb-3">
                            <label for="code" class="form-label small fw-semibold">Code</label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   class="form-control form-control-sm @error('code') is-invalid @enderror"
                                   value="{{ old('code') }}"
                                   placeholder="e.g. kg, pcs, ltr">
                            @error('code')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-plus-lg me-1"></i> Save Base Unit
                            </button>
                            <a href="{{ route('base-units.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

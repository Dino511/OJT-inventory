@extends('layouts.app')

@section('title', 'Add New Company')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('companies.index') }}" class="text-decoration-none">Companies</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Create Company</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">NEW COMPANY DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('companies.store') }}" method="POST">
                        @csrf

                        <!-- Company Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Company Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="e.g. Acme Corporation" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-plus-lg me-1"></i> Save Company
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-sm px-3">Reset</button>
                            <a href="{{ route('companies.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
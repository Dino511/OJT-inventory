@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('companies.index') }}" class="text-decoration-none">Companies</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #{{ $company->company_id }}</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Edit Company</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">EDIT COMPANY DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('companies.update', $company->company_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Company Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Company Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $company->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Update Company
                            </button>
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
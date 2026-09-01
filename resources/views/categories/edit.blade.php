@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}" class="text-decoration-none">Categories</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #{{ $category->id }}</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Edit Category</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">EDIT CATEGORY DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Company Selection Dropdown -->
                        <div class="mb-3">
                            <label for="company_id" class="form-label small fw-semibold">Company</label>
                            <select id="company_id" 
                                    name="company_id" 
                                    class="form-select form-select-sm @error('company_id') is-invalid @enderror" 
                                    required>
                                <option value="" disabled>Select a Company</option>
                                @foreach($companies as $companyOption)
                                    <option value="{{ $companyOption->company_id }}" {{ old('company_id', $category->company_id) == $companyOption->company_id ? 'selected' : '' }}>
                                        {{ $companyOption->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Category Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $category->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Update Category
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
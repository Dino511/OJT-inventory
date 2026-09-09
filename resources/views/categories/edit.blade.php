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

    <div class="row g-4">
        <!-- Left column: category details -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
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

                        @if($category->isSubcategory())
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Parent Category</label>
                                <div class="form-control form-control-sm bg-light text-secondary">{{ $category->parent->name }}</div>
                                <div class="form-text small">This is a subcategory — subcategories can't have their own subcategories.</div>
                            </div>
                        @endif

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

        <!-- Right column: subcategory management -->
        <div class="col-lg-6">
            @if(! $category->isSubcategory())
                <div class="card shadow-sm h-100">
                    <div class="card-header p-3 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-secondary small">SUBCATEGORIES</span>
                        @if($category->children->isNotEmpty())
                            <span class="badge bg-light text-dark border">
                                {{ $category->children->count() }} total
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        @if($category->children->isNotEmpty())
                            <ul class="list-group list-group-flush mb-3">
                                @foreach($category->children as $child)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><i class="bi bi-arrow-return-right text-muted me-2"></i>{{ $child->name }}</span>
                                        <form action="{{ route('categories.destroy', $child->id) }}" method="POST" onsubmit="return confirm('Delete the &quot;{{ $child->name }}&quot; subcategory?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete subcategory">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center text-muted py-4 mb-3">
                                <i class="bi bi-diagram-2 fs-2 d-block mb-2 text-secondary"></i>
                                No subcategories yet.
                            </div>
                        @endif

                        <form action="{{ route('categories.subcategories.store', $category->id) }}" method="POST" class="d-flex gap-2 mt-auto pt-3 border-top">
                            @csrf
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Soda, Juice" required>
                            <button type="submit" class="btn btn-outline-github btn-sm text-nowrap">
                                <i class="bi bi-plus-lg me-1"></i> Add Subcategory
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center text-muted h-100">
                        <i class="bi bi-diagram-2 fs-1 mb-2 text-secondary"></i>
                        Subcategories can't have their own subcategories, so there's nothing to manage here.
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
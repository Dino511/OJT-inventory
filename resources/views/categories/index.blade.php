@extends('layouts.app')

@section('title', 'Product Categories')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Categories</h3>
            <p class="text-secondary small mb-0">Organize and classify product listings.</p>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New Category
        </a>
    </div>

    <!-- Main CRUD Card Container -->
    <div class="card shadow-sm">
        <!-- Filter Bar -->
        <div class="card-header p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="tableSearch" class="form-control border-start-0" placeholder="Filter categories...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-tags me-1"></i> {{ $categories->count() }} Total
                    </span>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="categoriesTable">
                <thead>
                    <tr>
                        <th scope="col" style="width: 100px;">ID</th>
                        <th scope="col">CATEGORY NAME</th>
                        <th scope="col" class="text-end" style="width: 160px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">#{{ $category->id }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $category->name }}</div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-tags fs-2 d-block mb-2 text-secondary"></i>
                                No categories found. Click <strong>New Category</strong> to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('tableSearch')?.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#categoriesTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endpush
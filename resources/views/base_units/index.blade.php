@extends('layouts.app')

@section('title', 'Base Units')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Base Units</h3>
            <p class="text-secondary small mb-0">Units of measure used to quantify products.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('unit-conversions.index') }}" class="btn btn-outline-github btn-sm px-3">
                <i class="bi bi-arrow-left-right me-1"></i> Conversions
            </a>
            <a href="{{ route('base-units.create') }}" class="btn btn-github btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> New Base Unit
            </a>
        </div>
    </div>

    <!-- Main CRUD Card Container -->
    <div class="card shadow-sm">
        <!-- Filter Bar -->
        <div class="card-header p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="tableSearch" class="form-control border-start-0" placeholder="Filter base units...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-rulers me-1"></i> {{ $units->count() }} Total
                    </span>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="baseUnitsTable">
                <thead>
                    <tr>
                        <th scope="col" style="width: 100px;">ID</th>
                        <th scope="col">UNIT NAME</th>
                        <th scope="col">CODE</th>
                        <th scope="col" class="text-end" style="width: 160px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">#{{ $unit->id }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $unit->name }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $unit->code ?? '-' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('base-units.edit', $unit->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('base-units.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this base unit?');">
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
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-rulers fs-2 d-block mb-2 text-secondary"></i>
                                No base units found. Click <strong>New Base Unit</strong> to get started.
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
            let rows = document.querySelectorAll('#baseUnitsTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endpush

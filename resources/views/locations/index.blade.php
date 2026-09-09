@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark">Locations</h1>
            <p class="text-muted small mb-0">Manage operational locations mapped to companies.</p>
        </div>
        <a href="{{ route('locations.create') }}" class="btn btn-github">
            + New Location
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header p-3 bg-white">
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="tableSearch" class="form-control border-start-0" placeholder="Search company or location...">
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="locationsTable">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Company Name</th>
                        <th>Location Name</th>
                        <th>Description / Landmark</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                        <tr>
                            <td class="ps-4 text-muted">#{{ $location->id }}</td>
                            <td class="fw-semibold text-dark">{{ $location->company->name ?? 'N/A' }}</td>
                            <td>{{ $location->name }}</td>
                            <td class="text-secondary small">{{ $location->description ?? '—' }}</td>
                            <td>
                                @if($location->isWarehouse())
                                    <span class="badge bg-info-subtle text-info border">Warehouse</span>
                                @else
                                    <span class="badge bg-light text-secondary border">Store</span>
                                @endif
                            </td>
                            <td>
                                @if($location->is_active)
                                    <span class="badge bg-success-subtle text-success border">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('locations.toggle-status', $location) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $location->is_active ? 'Deactivate' : 'Activate' }} this location?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $location->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $location->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $location->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('locations.destroy', $location) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No locations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('tableSearch')?.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#locationsTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endpush
@endsection
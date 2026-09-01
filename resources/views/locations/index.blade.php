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
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Company Name</th>
                        <th>Location Name</th>
                        <th>Description / Landmark</th>
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
                            <td class="text-end pe-4">
                                <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-outline-github me-1" title="Edit">✏️</a>
                                <form action="{{ route('locations.destroy', $location) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-github text-danger" title="Delete" onclick="return confirm('Are you sure?')">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No locations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
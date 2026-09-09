@extends('layouts.app')

@section('title', 'Companies')

@section('content')
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Companies</h3>
            <p class="text-secondary small mb-0">Manage registered organization accounts</p>
        </div>
        <a href="{{ route('companies.create') }}" class="btn btn-success btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New Company
        </a>
    </div>

    <!-- Companies Table Card -->
    <div class="card shadow-sm">
        <div class="card-header p-3 bg-white">
            <span class="fw-semibold text-secondary small">ALL REGISTERED COMPANIES</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>COMPANY NAME</th>
                        <th>CREATED AT</th>
                        <th>STATUS</th>
                        <th class="text-end" style="width: 190px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">#{{ $company->company_id }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $company->name }}</span>
                            </td>
                            <td class="text-secondary small">
                                {{ $company->created_at ? $company->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                @if($company->is_active)
                                    <span class="badge bg-success-subtle text-success border">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('companies.toggle-status', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $company->is_active ? 'Deactivate' : 'Activate' }} this company?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $company->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $company->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $company->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
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
                            <td colspan="5" class="text-center py-4 text-muted">
                                No companies created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
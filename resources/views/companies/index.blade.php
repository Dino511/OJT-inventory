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
                        <th class="text-end" style="width: 150px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">#{{ $company->id }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $company->name }}</span>
                            </td>
                            <td class="text-secondary small">
                                {{ $company->created_at ? $company->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-github">Edit</a>
                                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-github text-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No companies created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
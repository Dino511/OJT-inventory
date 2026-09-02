@extends('layouts.app')

@section('title', 'Stock Record History')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">History</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0">History &mdash; {{ $inventory->product->name ?? 'N/A' }}</h3>
            <p class="text-secondary small mb-0">
                At <strong>{{ $inventory->location->name ?? 'N/A' }}</strong> &middot; every create, edit, transfer, and delete recorded for this stock record.
            </p>
        </div>
        <a href="{{ route('inventory.history.index') }}" class="btn btn-outline-github btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> View All Activity
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">ACTIVITY LOG</span>
            <span class="badge bg-light text-dark border font-monospace">{{ $logs->count() }} Entries</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 170px;">DATE &amp; TIME</th>
                        <th scope="col" style="width: 120px;">ACTION</th>
                        <th scope="col" style="width: 160px;">USER</th>
                        <th scope="col">DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-secondary small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td>@include('inventory._history-action-badge')</td>
                            <td class="text-secondary small">{{ $log->user_name ?? 'System' }}</td>
                            <td class="small">@include('inventory._history-details')</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary"></i>
                                No activity recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

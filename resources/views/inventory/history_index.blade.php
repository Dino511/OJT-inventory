@extends('layouts.app')

@section('title', 'Inventory Activity Log')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Activity Log</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0">Inventory Activity Log</h3>
            <p class="text-secondary small mb-0">Every stock record created, edited, transferred, or deleted, plus every product added or removed — across every location.</p>
        </div>
        <a href="{{ route('inventory.history.export') }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Export to Excel
        </a>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-header p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="tableSearch" class="form-control border-start-0" placeholder="Filter by product, location, user, or action...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-clock-history me-1"></i> {{ $logs->count() }} Entries
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="activityTable">
                <thead>
                    <tr>
                        <th scope="col" style="width: 170px;">DATE &amp; TIME</th>
                        <th scope="col">PRODUCT</th>
                        <th scope="col">LOCATION</th>
                        <th scope="col" style="width: 120px;">ACTION</th>
                        <th scope="col" style="width: 160px;">USER</th>
                        <th scope="col">DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-secondary small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->product_name }}</div>
                                @if($log->product_code)
                                    <span class="badge bg-light text-secondary border font-monospace">{{ $log->product_code }}</span>
                                @endif
                                @if($log->source === 'inventory' && $log->inventory_id && in_array($log->inventory_id, $existingInventoryIds))
                                    <a href="{{ route('inventory.history', $log->inventory_id) }}" class="small text-decoration-none ms-1">
                                        View record history
                                    </a>
                                @elseif($log->source === 'product' && $log->product_id && in_array($log->product_id, $existingProductIds))
                                    <a href="{{ route('products.history', $log->product_id) }}" class="small text-decoration-none ms-1">
                                        View product history
                                    </a>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $log->location_name ?? 'All locations' }}</td>
                            <td>@include('inventory._history-action-badge')</td>
                            <td class="text-secondary small">{{ $log->user_name ?? 'System' }}</td>
                            <td class="small">@include('inventory._history-details')</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
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

@push('scripts')
    <script>
        document.getElementById('tableSearch')?.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#activityTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endpush

@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Activity Log</h3>
            <p class="text-secondary small mb-0">
                Every company, category, location, product, and stock change across the whole system.
            </p>
        </div>
        <a href="{{ route('activity-log.export', ['period' => $period]) }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Export to Excel
        </a>
    </div>

    <!-- Period Filter -->
    <div class="btn-group btn-group-sm mb-3" role="group">
        <a href="{{ route('activity-log.index', ['period' => 'all']) }}" class="btn {{ $period === 'all' ? 'btn-github' : 'btn-outline-github' }}">
            All Time
        </a>
        <a href="{{ route('activity-log.index', ['period' => 'daily']) }}" class="btn {{ $period === 'daily' ? 'btn-github' : 'btn-outline-github' }}">
            Today
        </a>
        <a href="{{ route('activity-log.index', ['period' => 'weekly']) }}" class="btn {{ $period === 'weekly' ? 'btn-github' : 'btn-outline-github' }}">
            This Week
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
                        <input type="text" id="tableSearch" class="form-control border-start-0" placeholder="Filter by name, type, user, or action...">
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
                        <th scope="col" style="width: 110px;">TYPE</th>
                        <th scope="col">NAME</th>
                        <th scope="col" style="width: 120px;">ACTION</th>
                        <th scope="col" style="width: 160px;">USER</th>
                        <th scope="col">DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $typeBadge = match ($log->source) {
                                'company' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'category' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                'location' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                'product' => 'bg-dark-subtle text-dark-emphasis border-dark-subtle',
                                'inventory' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                default => 'bg-light text-dark border',
                            };
                        @endphp
                        <tr>
                            <td class="text-secondary small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td><span class="badge {{ $typeBadge }} border">{{ ucfirst($log->source) }}</span></td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->subject_name }}</div>
                                @if($log->source === 'product' && $log->subject_id && in_array($log->subject_id, $existingProductIds))
                                    <a href="{{ route('products.history', $log->subject_id) }}" class="small text-decoration-none">View product history</a>
                                @elseif($log->source === 'inventory' && $log->subject_id && in_array($log->subject_id, $existingInventoryIds))
                                    <a href="{{ route('inventory.history', $log->subject_id) }}" class="small text-decoration-none">View record history</a>
                                @endif
                            </td>
                            <td>@include('activity_log._badge')</td>
                            <td class="text-secondary small">{{ $log->user_name ?? 'System' }}</td>
                            <td class="small">@include('activity_log._details')</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary"></i>
                                No activity recorded {{ $period === 'daily' ? 'today' : ($period === 'weekly' ? 'this week' : 'yet') }}.
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

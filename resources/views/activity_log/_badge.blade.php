@php
    $badge = match ($log->action) {
        'created' => ['bg-success-subtle text-success border-success-subtle', 'bi-plus-circle'],
        'updated' => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'bi-pencil'],
        'deleted' => ['bg-danger-subtle text-danger border-danger-subtle', 'bi-trash'],
        'transferred' => ['bg-info-subtle text-info-emphasis border-info-subtle', 'bi-arrow-left-right'],
        'purchase_received' => ['bg-primary-subtle text-primary border-primary-subtle', 'bi-cart-check'],
        'transfer_dispatched' => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'bi-truck'],
        'transfer_received' => ['bg-success-subtle text-success border-success-subtle', 'bi-box-seam'],
        'transfer_cancelled' => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'bi-x-circle'],
        default => ['bg-light text-dark border', 'bi-dot'],
    };
@endphp
<span class="badge {{ $badge[0] }} border">
    <i class="bi {{ $badge[1] }} me-1"></i> {{ ucwords(str_replace('_', ' ', $log->action)) }}
</span>

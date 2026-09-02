@php
    $badge = match ($log->action) {
        'created' => ['bg-success-subtle text-success border-success-subtle', 'bi-plus-circle'],
        'updated' => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'bi-pencil'],
        'deleted' => ['bg-danger-subtle text-danger border-danger-subtle', 'bi-trash'],
        default => ['bg-light text-dark border', 'bi-dot'],
    };
@endphp
<span class="badge {{ $badge[0] }} border">
    <i class="bi {{ $badge[1] }} me-1"></i> {{ ucfirst($log->action) }}
</span>

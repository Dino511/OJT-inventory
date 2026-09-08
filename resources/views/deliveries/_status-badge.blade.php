@php
    $statusBadge = match($status) {
        'received' => ['bg-success-subtle text-success', 'Received'],
        'cancelled' => ['bg-secondary-subtle text-secondary', 'Cancelled'],
        default => ['bg-warning-subtle text-warning-emphasis', 'Pending'],
    };
@endphp
<span class="badge {{ $statusBadge[0] }} border">{{ $statusBadge[1] }}</span>

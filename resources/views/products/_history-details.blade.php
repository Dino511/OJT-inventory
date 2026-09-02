@if($log->action === 'created')
    <span class="text-secondary">Product "{{ $log->product_name }}" was created.</span>
@elseif($log->action === 'deleted')
    <span class="text-secondary">Product "{{ $log->product_name }}" was deleted.</span>
@elseif(!empty($log->changes))
    <ul class="list-unstyled mb-0">
        @foreach($log->changes as $field => $values)
            <li class="mb-1">
                <span class="fw-semibold text-dark">{{ Str::headline($field) }}:</span>
                <span class="text-danger text-decoration-line-through">{{ $values['old'] ?? '—' }}</span>
                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                <span class="text-success">{{ $values['new'] ?? '—' }}</span>
            </li>
        @endforeach
    </ul>
@else
    <span class="text-muted">No field changes recorded.</span>
@endif

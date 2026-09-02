@if($log->action === 'created')
    @if(($log->source ?? 'inventory') === 'product')
        <span class="text-secondary">Product "{{ $log->product_name }}" was added to the catalog.</span>
    @else
        <span class="text-secondary">Stock record created for "{{ $log->product_name }}" at {{ $log->location_name }}.</span>
    @endif
@elseif($log->action === 'deleted')
    @if(($log->source ?? 'inventory') === 'product')
        <span class="text-secondary">Product "{{ $log->product_name }}" was deleted from the catalog.</span>
    @else
        <span class="text-secondary">Stock record for "{{ $log->product_name }}" at {{ $log->location_name }} was deleted.</span>
    @endif
@elseif($log->action === 'transferred' && !empty($log->changes))
    @php $c = $log->changes; @endphp
    <div class="text-secondary">
        Transferred <strong>{{ $c['quantity'] }}</strong> unit(s) from
        <strong>{{ $c['from_location'] }}</strong> to
        <strong>{{ $c['to_location'] }}</strong>.
    </div>
    @if(array_key_exists('source_quantity_before', $c))
        <div class="small text-muted mt-1">
            {{ $c['from_location'] }}: {{ $c['source_quantity_before'] }} <i class="bi bi-arrow-right mx-1"></i> {{ $c['source_quantity_after'] }}
            &nbsp;&middot;&nbsp;
            {{ $c['to_location'] }}: {{ $c['destination_quantity_before'] }} <i class="bi bi-arrow-right mx-1"></i> {{ $c['destination_quantity_after'] }}
        </div>
    @endif
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
    <span class="text-muted">No details recorded.</span>
@endif

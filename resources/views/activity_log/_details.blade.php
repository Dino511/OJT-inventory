@php
    $label = match ($log->source) {
        'company' => 'Company',
        'category' => 'Category',
        'location' => 'Location',
        'product' => 'Product',
        'inventory' => 'Stock record',
        default => 'Item',
    };
@endphp
@if($log->action === 'created')
    <span class="text-secondary">{{ $label }} "{{ $log->subject_name }}" was added.</span>
@elseif($log->action === 'deleted')
    <span class="text-secondary">{{ $label }} "{{ $log->subject_name }}" was deleted.</span>
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
@elseif($log->action === 'purchase_received' && !empty($log->changes))
    @php $c = $log->changes; @endphp
    <div class="text-secondary">
        Received <strong>{{ $c['quantity'] }}</strong> unit(s) from <strong>{{ $c['supplier'] }}</strong>
        (<a href="{{ route('purchase-orders.show', $c['purchase_order_id']) }}">Purchase Order #{{ $c['purchase_order_id'] }}</a>).
    </div>
    @if(array_key_exists('destination_quantity_before', $c))
        <div class="small text-muted mt-1">
            {{ $log->subject_name }}: {{ $c['destination_quantity_before'] }} <i class="bi bi-arrow-right mx-1"></i> {{ $c['destination_quantity_after'] }}
        </div>
    @endif
@elseif(in_array($log->action, ['transfer_dispatched', 'transfer_received', 'transfer_cancelled']) && !empty($log->changes))
    @php
        $c = $log->changes;
        $verb = match ($log->action) {
            'transfer_dispatched' => 'Dispatched',
            'transfer_received' => 'Received',
            'transfer_cancelled' => 'Cancelled — returned',
        };
    @endphp
    <div class="text-secondary">
        {{ $verb }} <strong>{{ $c['quantity'] }}</strong> unit(s)
        (<a href="{{ route('stock-transfers.show', $c['stock_transfer_id']) }}">Transfer #{{ $c['stock_transfer_id'] }}</a>).
    </div>
    @if(array_key_exists('source_quantity_before', $c))
        <div class="small text-muted mt-1">
            {{ $log->subject_name }}: {{ $c['source_quantity_before'] }} <i class="bi bi-arrow-right mx-1"></i> {{ $c['source_quantity_after'] }}
        </div>
    @elseif(array_key_exists('destination_quantity_before', $c))
        <div class="small text-muted mt-1">
            {{ $log->subject_name }}: {{ $c['destination_quantity_before'] }} <i class="bi bi-arrow-right mx-1"></i> {{ $c['destination_quantity_after'] }}
        </div>
    @endif
@elseif(!empty($log->changes))
    <ul class="list-unstyled mb-0">
        @foreach($log->changes as $field => $values)
            <li class="mb-1">
                <span class="fw-semibold text-dark">{{ Str::headline($field) }}:</span>
                @if(is_array($values) && array_key_exists('old', $values) && array_key_exists('new', $values))
                    <span class="text-danger text-decoration-line-through">{{ $values['old'] ?? '—' }}</span>
                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                    <span class="text-success">{{ $values['new'] ?? '—' }}</span>
                @else
                    <span class="text-secondary">{{ is_scalar($values) ? $values : json_encode($values) }}</span>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <span class="text-muted">No details recorded.</span>
@endif

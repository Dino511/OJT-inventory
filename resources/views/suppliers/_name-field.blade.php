{{--
    Shared contact-name input group: a prefix dropdown (Mr./Ms./Mrs./etc.,
    optional) plus the plain name, combined server-side into one stored
    string. Expects: $fieldPrefix ('contact_person' or
    'secondary_contact_person'), $label, $required (bool), $namePrefixes,
    $selectedPrefix, $nameValue.
--}}
<div class="mb-3">
    <label for="{{ $fieldPrefix }}_name" class="form-label small fw-semibold">
        {{ $label }} @if(! $required)<span class="text-muted fw-normal">(optional)</span>@endif
    </label>
    <div class="input-group input-group-sm">
        <select name="{{ $fieldPrefix }}_prefix" class="form-select form-select-sm @error($fieldPrefix . '_prefix') is-invalid @enderror" style="max-width: 110px;">
            <option value="" {{ old($fieldPrefix . '_prefix', $selectedPrefix) == '' ? 'selected' : '' }}>—</option>
            @foreach($namePrefixes as $prefix)
                <option value="{{ $prefix }}" {{ old($fieldPrefix . '_prefix', $selectedPrefix) == $prefix ? 'selected' : '' }}>
                    {{ $prefix }}
                </option>
            @endforeach
        </select>
        <input type="text" id="{{ $fieldPrefix }}_name" name="{{ $fieldPrefix }}_name"
               class="form-control form-control-sm @error($fieldPrefix . '_name') is-invalid @enderror"
               value="{{ old($fieldPrefix . '_name', $nameValue) }}"
               placeholder="e.g. Juan Dela Cruz" {{ $required ? 'required' : '' }}>
    </div>
    @error($fieldPrefix . '_prefix')
        <div class="invalid-feedback d-block small">{{ $message }}</div>
    @enderror
    @error($fieldPrefix . '_name')
        <div class="invalid-feedback d-block small">{{ $message }}</div>
    @enderror
</div>

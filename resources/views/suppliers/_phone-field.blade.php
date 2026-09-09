{{--
    Shared phone-number input group: a country-code dropdown (defaulting to
    +63 Philippines) plus the local number, combined server-side into one
    stored string. Expects: $fieldPrefix ('phone' or 'secondary_phone'),
    $label, $required (bool), $countryCodes, $selectedCode, $numberValue.
--}}
<div class="mb-3">
    <label for="{{ $fieldPrefix }}_number" class="form-label small fw-semibold">
        {{ $label }} @if(! $required)<span class="text-muted fw-normal">(optional)</span>@endif
    </label>
    <div class="input-group input-group-sm">
        <select name="{{ $fieldPrefix }}_country_code" class="form-select form-select-sm @error($fieldPrefix . '_country_code') is-invalid @enderror" style="max-width: 160px;">
            @foreach($countryCodes as $code => $countryLabel)
                <option value="{{ $code }}" {{ old($fieldPrefix . '_country_code', $selectedCode) == $code ? 'selected' : '' }}>
                    {{ $countryLabel }}
                </option>
            @endforeach
        </select>
        <input type="text" id="{{ $fieldPrefix }}_number" name="{{ $fieldPrefix }}_number"
               class="form-control form-control-sm phone-digits-only @error($fieldPrefix . '_number') is-invalid @enderror"
               value="{{ old($fieldPrefix . '_number', $numberValue) }}"
               inputmode="numeric" pattern="[0-9]{9}" maxlength="9"
               placeholder="e.g. 917123456" {{ $required ? 'required' : '' }}>
    </div>
    <div class="form-text small">Enter exactly 9 digits, without the country code.</div>
    @error($fieldPrefix . '_country_code')
        <div class="invalid-feedback d-block small">{{ $message }}</div>
    @enderror
    @error($fieldPrefix . '_number')
        <div class="invalid-feedback d-block small">{{ $message }}</div>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            // Restrict phone-number inputs to digits only, capped at 9 characters.
            document.querySelectorAll('.phone-digits-only').forEach(function (input) {
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 9);
                });
            });
        </script>
    @endpush
@endonce

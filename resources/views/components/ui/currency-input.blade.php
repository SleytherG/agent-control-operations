@props(['label' => null, 'name' => '', 'value' => null, 'placeholder' => '0.00', 'error' => null, 'disabled' => false, 'required' => false, 'large' => false, 'id' => null, 'prefix' => 'S/'])

@php
    $inputId = $id ?? 'currency-' . $name;
    $errorId = $error ? $inputId . '-error' : null;
@endphp

<div class="form-group">
    @if($label)
        <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>
    @endif
    <div class="currency-input-wrapper {{ $error ? 'form-input--error-wrapper' : '' }}">
        <span class="currency-prefix" aria-hidden="true">{{ $prefix }}</span>
        <input
            type="text"
            inputmode="decimal"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="currency-input {{ $large ? 'currency-input--large' : '' }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            aria-label="{{ $label ?? 'Monto' }}"
            @if($errorId) aria-describedby="{{ $errorId }}" @endif
            {{ $attributes->except('class') }}
        >
    </div>
    @if($error)
        <span class="form-error" id="{{ $errorId }}" role="alert">{{ $error }}</span>
    @endif
</div>

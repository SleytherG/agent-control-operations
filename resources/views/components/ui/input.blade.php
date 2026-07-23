@props(['label' => null, 'name' => '', 'type' => 'text', 'value' => null, 'placeholder' => '', 'error' => null, 'hint' => null, 'disabled' => false, 'required' => false, 'id' => null])

@php
    $inputId = $id ?? 'input-' . $name;
    $errorId = $error ? $inputId . '-error' : null;
    $hintId = $hint ? $inputId . '-hint' : null;
    $describedBy = implode(' ', array_filter([$errorId, $hintId]));
@endphp

<div class="form-group">
    @if($label)
        <label class="form-label" for="{{ $inputId }}">{{ $label }}</label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="form-input {{ $error ? 'form-input--error' : '' }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class') }}
    >
    @if($hint)
        <span class="form-hint" id="{{ $hintId }}">{{ $hint }}</span>
    @endif
    @if($error)
        <span class="form-error" id="{{ $errorId }}" role="alert">{{ $error }}</span>
    @endif
</div>

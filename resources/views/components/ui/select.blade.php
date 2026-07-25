@props(['label' => null, 'name' => '', 'options' => [], 'value' => null, 'selected' => null, 'placeholder' => null, 'error' => null, 'disabled' => false, 'required' => false, 'id' => null])

@php
    $selectId = $id ?? 'select-' . $name;
    $selectedValue = $value ?? $selected;
    $errorId = $error ? $selectId . '-error' : null;
@endphp

<div class="form-group">
    @if($label)
        <label class="form-label" for="{{ $selectId }}">{{ $label }}</label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        class="form-input form-select {{ $error ? 'form-input--error' : '' }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        @if($errorId) aria-describedby="{{ $errorId }}" @endif
        {{ $attributes->except('class') }}
    >
        @if($placeholder)
            <option value="" {{ $selectedValue === null || $selectedValue === '' ? 'selected' : '' }}>{{ $placeholder }}</option>
        @endif
        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ (string)$optValue === (string)$selectedValue ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
    @if($error)
        <span class="form-error" id="{{ $errorId }}" role="alert">{{ $error }}</span>
    @endif
</div>

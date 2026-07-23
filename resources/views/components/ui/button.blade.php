@props(['variant' => 'primary', 'type' => 'button', 'disabled' => false, 'loading' => false, 'size' => null])

<button
    type="{{ $type }}"
    class="btn btn--{{ $variant }} {{ $size ? 'btn--' . $size : '' }} {{ $loading ? 'btn--loading' : '' }} {{ $attributes->get('class') }}"
    {{ $disabled || $loading ? 'disabled' : '' }}
    {{ $attributes->except('class') }}
>
    <span class="btn-text">{{ $slot }}</span>
    <span class="btn-spinner" aria-hidden="true"></span>
</button>

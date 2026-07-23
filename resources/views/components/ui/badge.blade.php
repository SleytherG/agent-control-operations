@props(['variant' => 'active', 'dot' => false])

<span class="badge badge--{{ $variant }}">
    @if($dot)
        <span class="badge-dot badge-dot--{{ $variant }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>

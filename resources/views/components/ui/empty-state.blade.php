@props(['icon' => '&#x1F4ED;', 'title' => 'Sin datos', 'description' => null, 'action' => null])

<div class="empty-state">
    <span class="empty-state-icon" aria-hidden="true">{!! $icon !!}</span>
    <h3 class="empty-state-title">{{ $title }}</h3>
    @if($description)
        <p class="empty-state-description">{{ $description }}</p>
    @endif
    @if($action)
        {{ $action }}
    @endif
</div>

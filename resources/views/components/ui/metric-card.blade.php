@props(['label' => '', 'value' => '', 'icon' => null, 'trend' => null, 'trendLabel' => null, 'sub' => null, 'variant' => 'default', 'class' => ''])

<div class="metric-card {{ $variant === 'dark' ? 'metric-card--dark' : '' }} {{ $class }}">
    @if($variant === 'accent-green')
        <span class="metric-card-accent metric-card-accent--green" aria-hidden="true"></span>
    @elseif($variant === 'accent-red')
        <span class="metric-card-accent metric-card-accent--red" aria-hidden="true"></span>
    @endif

    <div class="metric-card-header">
        <span class="metric-card-label">{{ $label }}</span>
        @if($icon)
            <span class="metric-card-icon" aria-hidden="true">{!! $icon !!}</span>
        @endif
    </div>

    <span class="metric-card-value">{{ $value }}</span>

    @if($trend)
        <div class="metric-card-trend {{ $trend === 'up' ? 'metric-card-trend--up' : 'metric-card-trend--down' }}">
            <span aria-hidden="true">{{ $trend === 'up' ? '&#x25B2;' : '&#x25BC;' }}</span>
            <span>{{ $trendLabel }}</span>
        </div>
    @endif

    @if($sub)
        <span class="metric-card-sub">{{ $sub }}</span>
    @endif
</div>

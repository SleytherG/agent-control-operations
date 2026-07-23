@props(['title' => null, 'loading' => false, 'empty' => false, 'height' => '300px', 'emptyMessage' => 'Sin datos disponibles'])

<div class="chart-container">
    @if($title)
    <div class="chart-container-header">
        <h3 class="chart-container-title">{{ $title }}</h3>
        {{ $actions ?? '' }}
    </div>
    @endif
    <div class="chart-container-body" style="min-height: {{ $height }}">
        <div class="chart-loading-overlay" @if(!$loading) hidden @endif>
            <span class="loading-spinner" aria-hidden="true"></span>
        </div>
        <div class="chart-empty-overlay" @if(!$empty) hidden @endif>
            <span style="font-size: 40px; opacity: 0.4; margin-bottom: 8px;" aria-hidden="true">&#x1F4CA;</span>
            <span>{{ $emptyMessage }}</span>
        </div>
        {{ $slot }}
    </div>
</div>

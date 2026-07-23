@props(['type' => 'card', 'count' => 3])

@if($type === 'card')
    <div class="loading-skeleton-cards" aria-busy="true" aria-label="Cargando">
        @for($i = 0; $i < $count; $i++)
            <div class="skeleton skeleton--card"></div>
        @endfor
    </div>
@elseif($type === 'table')
    <div class="loading-skeleton-table" aria-busy="true" aria-label="Cargando">
        <div class="skeleton skeleton--title"></div>
        @for($i = 0; $i < $count; $i++)
            <div class="skeleton skeleton--table-row"></div>
        @endfor
    </div>
@elseif($type === 'chart')
    <div class="skeleton skeleton--chart" aria-busy="true" aria-label="Cargando gráfico"></div>
@elseif($type === 'spinner')
    <div class="loading-overlay" aria-busy="true">
        <span class="loading-spinner" aria-hidden="true"></span>
        <span>{{ $slot ?? 'Cargando...' }}</span>
    </div>
@endif

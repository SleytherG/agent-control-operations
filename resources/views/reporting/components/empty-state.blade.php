@php
    $state = match($context ?? 'default') {
        'operator' => [
            'icon' => '&#x1F4ED;',
            'title' => 'Sin operaciones por ahora',
            'description' => 'No tienes operaciones registradas en este periodo. Al registrar tus operaciones, aquí verás tus métricas.',
        ],
        'admin' => [
            'icon' => '&#x1F50D;',
            'title' => 'Sin resultados',
            'description' => 'No se encontraron operaciones con los filtros aplicados. Intenta ajustar los filtros para ver resultados.',
        ],
        'comparison' => [
            'icon' => '&#x1F4CA;',
            'title' => 'Sin datos de comparación',
            'description' => 'No hay operadores con operaciones en el periodo seleccionado.',
        ],
        default => [
            'icon' => '&#x1F4CB;',
            'title' => 'Sin datos disponibles',
            'description' => 'No hay información para mostrar en este momento.',
        ],
    };
@endphp

<x-ui.empty-state
    :icon="$state['icon']"
    :title="$state['title']"
    :description="$state['description']"
/>

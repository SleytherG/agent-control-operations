@props(['title' => 'Filtros'])

<div class="filter-bar-wrapper">
    <button class="btn btn--secondary filter-bar-toggle" id="filter-toggle" aria-label="Mostrar filtros">
        &#x1F50D; {{ $title }}
    </button>

    <div class="filter-offcanvas" id="filter-offcanvas" role="dialog" aria-label="{{ $title }}">
        <div class="filter-offcanvas-header">
            <span class="filter-offcanvas-title">{{ $title }}</span>
            <button class="filter-offcanvas-close" id="filter-offcanvas-close" aria-label="Cerrar filtros">&times;</button>
        </div>
        <div class="filter-offcanvas-body">
            {{ $slot }}
        </div>
    </div>

    <div class="filter-bar filter-bar-desktop">
        {{ $slot }}
    </div>
</div>

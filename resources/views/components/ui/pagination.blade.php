@props(['currentPage' => 1, 'lastPage' => 1, 'total' => 0, 'from' => 1, 'to' => 1])

<div class="pagination" role="navigation" aria-label="Paginación">
    <span class="pagination-info">Mostrando {{ $from }} al {{ $to }} de {{ $total }} registros</span>
    <div class="pagination-links">
        @if($currentPage > 1)
            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="pagination-link">Anterior</a>
        @else
            <span class="pagination-link pagination-link--disabled" aria-disabled="true">Anterior</span>
        @endif
        @for($i = 1; $i <= $lastPage; $i++)
            @if($i === $currentPage)
                <span class="pagination-link pagination-link--active" aria-current="page">{{ $i }}</span>
            @else
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" class="pagination-link">{{ $i }}</a>
            @endif
        @endfor
        @if($currentPage < $lastPage)
            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="pagination-link">Siguiente</a>
        @else
            <span class="pagination-link pagination-link--disabled" aria-disabled="true">Siguiente</span>
        @endif
    </div>
</div>

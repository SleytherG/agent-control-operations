@props(['currentPage' => 1, 'lastPage' => 1, 'total' => 0, 'from' => 1, 'to' => 1])

<div class="pagination" role="navigation" aria-label="Paginación">
    <span class="pagination-info">Mostrando {{ $from }} al {{ $to }} de {{ $total }} registros</span>
    <div class="pagination-links">
        <span class="pagination-link pagination-link--disabled" aria-disabled="true">Anterior</span>
        @for($i = 1; $i <= $lastPage; $i++)
            @if($i === $currentPage)
                <span class="pagination-link pagination-link--active" aria-current="page">{{ $i }}</span>
            @else
                <a href="?page={{ $i }}" class="pagination-link">{{ $i }}</a>
            @endif
        @endfor
        <span class="pagination-link pagination-link--disabled" aria-disabled="true">Siguiente</span>
    </div>
</div>

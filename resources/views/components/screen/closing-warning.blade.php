@props(['count' => 0])

<div class="closing-warning-banner" role="alert">
    <span class="closing-warning-icon" aria-hidden="true">&#x26A0;</span>
    <div>
        <div class="closing-warning-title">Operaciones por confirmar</div>
        <p class="closing-warning-text">
            Existen <strong>{{ $count }} operacion(es)</strong> con direcciones de caja no confirmadas.
            Revise estas operaciones antes de proceder con el cierre.
        </p>
    </div>
</div>

@props(['message' => 'Ocurrió un error al cargar los datos.', 'retry' => false, 'retryAction' => null])

<div class="error-state" role="alert">
    <span class="error-state-icon" aria-hidden="true">&#x26A0;</span>
    <h3 class="error-state-title">Error</h3>
    <p class="error-state-message">{{ $message }}</p>
    @if($retry && $retryAction)
        <button class="btn btn--primary" onclick="{{ $retryAction }}">Reintentar</button>
    @endif
</div>

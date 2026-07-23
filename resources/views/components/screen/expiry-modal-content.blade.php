@props(['expiryState' => 'warning', 'timerSeconds' => 30])

<div class="expiry-content">
    <div class="expiry-icon-wrapper">
        <span class="expiry-icon" aria-hidden="true">&#x23F0;</span>
    </div>
    <h2 class="expiry-title" id="expiry-title">
        @if($expiryState === 'expired') Sesion expirada
        @elseif($expiryState === 'revoked') Sesion revocada
        @else Tu sesion esta por finalizar
        @endif
    </h2>
    <p class="expiry-description">
        @if($expiryState === 'expired')
            Tu sesion ha expirado por inactividad. Seras redirigido al inicio de sesion.
        @elseif($expiryState === 'revoked')
            Tu sesion ha sido revocada por un administrador. Contacta a soporte.
        @else
            Por razones de seguridad, tu sesion se cerrara automaticamente debido a inactividad. Deseas continuar operando?
        @endif
    </p>

    @if($expiryState === 'warning' || $expiryState === 'renewing')
    <div class="expiry-timer">
        <span class="expiry-timer-label">Tiempo restante</span>
        <span class="expiry-timer-value" id="expiry-timer-display">
            {{ floor($timerSeconds / 60) }}:{{ str_pad($timerSeconds % 60, 2, '0', STR_PAD_LEFT) }}
        </span>
    </div>
    @endif
</div>

@if($expiryState === 'warning')
<div class="expiry-actions" slot="footer">
    <button class="btn btn--secondary" onclick="window.location.href='/demo/login'">Cerrar sesion</button>
    <button class="btn btn--primary" id="renew-session-btn">Continuar sesion</button>
</div>
@elseif($expiryState === 'renewing')
<div class="expiry-actions" slot="footer">
    <button class="btn btn--primary btn--loading" disabled>
        <span class="btn-text">Renovando...</span>
        <span class="btn-spinner" aria-hidden="true"></span>
    </button>
</div>
@elseif($expiryState === 'expired' || $expiryState === 'revoked')
<div class="expiry-actions" slot="footer">
    <button class="btn btn--primary" onclick="window.location.href='/demo/login'">Ir al inicio de sesion</button>
</div>
@endif

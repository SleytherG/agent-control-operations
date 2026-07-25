<div id="session-expiry-modal" class="session-modal-overlay" hidden role="dialog" aria-labelledby="expiry-title" aria-modal="true">
    <div class="session-modal-box">
        <div class="session-modal-body">
            <div class="session-modal-icon-wrapper">
                <div class="session-modal-icon-ring"></div>
                <span class="session-modal-icon">&#x23F1;</span>
            </div>
            <h2 class="session-modal-title" id="expiry-title">Tu sesión está por finalizar</h2>
            <p class="session-modal-desc">
                Por razones de seguridad, tu sesión se cerrará automáticamente debido a inactividad. ¿Deseas continuar operando?
            </p>
            <div class="session-modal-timer-box">
                <span class="session-modal-timer-label">Tiempo restante</span>
                <span class="session-modal-timer-value" id="modal-timer-display">00:30</span>
            </div>
        </div>
        <div class="session-modal-footer">
            <button class="session-modal-btn session-modal-btn--secondary" id="end-session">Cerrar sesión</button>
            <button class="session-modal-btn session-modal-btn--primary" id="continue-session">Continuar sesión</button>
        </div>
    </div>
</div>

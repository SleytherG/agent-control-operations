@props(['banks' => [], 'types' => []])

<div class="registration-card">
    <div class="registration-context-bar">
        <div class="registration-context-item">
            <span aria-hidden="true">&#x1F3EA;</span>
            <span>Tienda: <strong>Tienda Centro</strong></span>
        </div>
        <div class="registration-context-item">
            <span aria-hidden="true">&#x1F3E6;</span>
            <span>Agente: <strong>BCP - 00124</strong></span>
        </div>
        <div class="registration-context-item">
            <span aria-hidden="true">&#x1F4C5;</span>
            <span>Fecha: <strong>{{ date('d/m/Y') }}</strong></span>
        </div>
    </div>

    <form class="registration-form" id="operation-form" onsubmit="return handleSubmit(event)">
        <div class="registration-hero">
            <x-ui.currency-input
                label="Monto de Operacion"
                name="amount"
                placeholder="0.00"
                large="true"
                required="true"
            />
        </div>

        <div style="grid-column: span 6;">
            <x-ui.select
                label="Tipo de Operacion"
                name="type"
                :options="$types"
                placeholder="Seleccione tipo..."
                required="true"
            />
        </div>

        <div style="grid-column: span 6;">
            <x-ui.select
                label="Banco Destino / Origen"
                name="bank"
                :options="$banks"
                placeholder="Seleccione banco..."
                required="true"
            />
        </div>

        <div style="grid-column: span 6;">
            <x-ui.input
                label="Numero de Referencia"
                name="reference"
                placeholder="Ej. OP-123456"
                hint="Opcional"
            />
        </div>

        <div style="grid-column: span 6;">
            <div class="form-group">
                <label class="form-label">Observaciones <span style="font-weight:400;text-transform:none;">(Opcional)</span></label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Detalles adicionales..."></textarea>
            </div>
        </div>

        <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; padding-top: var(--space-lg); border-top: var(--border-thin);">
            <x-ui.button variant="primary" type="submit" size="lg" id="submit-operation-btn">
                Registrar Operacion
            </x-ui.button>
        </div>
    </form>
</div>

<script>
function handleSubmit(e) {
    e.preventDefault();
    var btn = document.getElementById('submit-operation-btn');
    btn.classList.add('btn--loading');
    btn.disabled = true;
    setTimeout(function() {
        btn.classList.remove('btn--loading');
        btn.disabled = false;
        if (typeof showToast !== 'undefined') {
            showToast('Operacion registrada', 'La operacion ha sido registrada exitosamente.', 'success');
        }
    }, 1500);
    return false;
}
</script>

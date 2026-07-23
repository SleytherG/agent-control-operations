@props(['assignments' => [], 'types' => [], 'idempotencyKey' => ''])

@php
    $agentOptions = [];
    foreach ($assignments as $assignment) {
        $bankName = $assignment->bankAgent->bank->name ?? 'Banco';
        $storeName = $assignment->bankAgent->store->name ?? 'Tienda';
        $code = $assignment->bankAgent->code ?? '';
        $agentOptions[$assignment->bank_agent_id] = "{$bankName} — {$storeName} ({$code})";
    }

    $typeOptions = [];
    foreach ($types as $type) {
        $label = $type->name;
        if ($type->bank_id && isset($type->bank)) {
            $label .= ' (' . $type->bank->name . ')';
        } elseif ($type->bank_id === null) {
            $label .= ' (General)';
        }
        $typeOptions[$type->id] = $label;
    }
@endphp

<div class="registration-card">
    <form class="registration-form" method="POST" action="{{ route('operations.store') }}">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        <div class="registration-hero">
            <x-ui.input
                label="Monto de Operacion"
                name="amount"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="0.00"
                required="true"
                value="{{ old('amount') }}"
            />
        </div>

        <div style="grid-column: span 6;">
            <x-ui.select
                label="Agente Bancario"
                name="bank_agent_id"
                :options="$agentOptions"
                placeholder="Seleccione un agente..."
                required="true"
                :selected="old('bank_agent_id')"
            />
        </div>

        <div style="grid-column: span 6;">
            <x-ui.select
                label="Tipo de Operacion"
                name="operation_type_id"
                :options="$typeOptions"
                placeholder="Seleccione tipo..."
                required="true"
                :selected="old('operation_type_id')"
            />
        </div>

        <div style="grid-column: span 3;">
            <x-ui.input
                label="Moneda"
                name="currency"
                value="{{ old('currency', 'PEN') }}"
                maxlength="3"
            />
        </div>

        <div style="grid-column: span 3;">
            <x-ui.input
                label="Fecha Efectiva"
                name="effective_at"
                type="datetime-local"
                value="{{ old('effective_at', now()->format('Y-m-d\TH:i')) }}"
                required="true"
            />
        </div>

        <div style="grid-column: span 6;">
            <x-ui.input
                label="Numero de Referencia"
                name="reference"
                placeholder="Ej. OP-123456"
                hint="Opcional"
                value="{{ old('reference') }}"
            />
        </div>

        <div style="grid-column: span 6;">
            <div class="form-group">
                <label class="form-label">Observaciones <span style="font-weight:400;text-transform:none;">(Opcional)</span></label>
                <textarea name="observation" class="form-input" rows="2" placeholder="Detalles adicionales...">{{ old('observation') }}</textarea>
            </div>
        </div>

        <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; padding-top: var(--space-lg); border-top: var(--border-thin);">
            <x-ui.button variant="primary" type="submit" size="lg">
                Registrar Operacion
            </x-ui.button>
        </div>
    </form>
</div>

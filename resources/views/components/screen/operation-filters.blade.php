@props(['agents' => [], 'types' => [], 'currentFilters' => []])

@php
    $agentOptions = ['' => 'Todos los agentes'];
    foreach ($agents as $agent) {
        $agentOptions[$agent->id] = ($agent->code ?? '') . ' — ' . ($agent->bank->name ?? 'Sin banco');
    }

    $typeOptions = ['' => 'Todos los tipos'];
    foreach ($types as $type) {
        $typeOptions[$type->id] = $type->name;
    }
@endphp

<div class="filter-bar-wrapper">
    <button class="btn btn--secondary filter-bar-toggle" id="filter-toggle" aria-label="Mostrar filtros">
        &#x1F50D; Filtros
    </button>

    <div class="filter-offcanvas" id="filter-offcanvas" role="dialog" aria-label="Filtros">
        <div class="filter-offcanvas-header">
            <span class="filter-offcanvas-title">Filtros</span>
            <button class="filter-offcanvas-close" id="filter-offcanvas-close" aria-label="Cerrar filtros">&times;</button>
        </div>
        <div class="filter-offcanvas-body">
            <form method="GET" action="{{ route('operations.index') }}" class="form-filter filter-form">
                <div class="form-group">
                    <label class="form-label">Código</label>
                    <input type="text" name="code" class="form-input" value="{{ request('code') }}" placeholder="Código de operación">
                </div>
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <input type="text" name="customer_name" class="form-input" value="{{ request('customer_name') }}" placeholder="Nombre del cliente">
                </div>
                <div class="form-group">
                    <label class="form-label">Monto</label>
                    <input type="number" name="amount" class="form-input" step="0.01" value="{{ request('amount') }}" placeholder="Monto exacto">
                </div>
                <div class="form-group">
                    <label class="form-label">Agente</label>
                    <select name="bank_agent_id" class="form-input form-select">
                        @foreach($agentOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('bank_agent_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="operation_type_id" class="form-input form-select">
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('operation_type_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-input form-select">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Activas</option>
                        <option value="ANNULLED" {{ request('status') === 'ANNULLED' ? 'selected' : '' }}>Anuladas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Desde</label>
                    <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
                </div>
                <div class="filter-form-actions">
                    <a href="{{ route('operations.index') }}" class="btn btn--secondary btn--sm">Limpiar</a>
                    <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

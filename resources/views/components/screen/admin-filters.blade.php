@props([
    'regions' => [], 'agents' => [],
    'types' => [], 'operators' => [], 'currentFilters' => [],
    'period' => 'month', 'date' => null,
])

@php
    $agentOptions = ['' => 'Todos los agentes'];
    foreach ($agents as $agent) {
        $agentOptions[$agent->id] = ($agent->code ?? '') . ' — ' . ($agent->name ?? '');
    }

    $typeOptions = ['' => 'Todos los tipos'];
    foreach ($types as $type) {
        $typeOptions[$type->id] = $type->name;
    }
@endphp

<section class="admin-filters-panel">
    <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-filters-form">
        <div class="admin-filters-header">
            <span aria-hidden="true">&#x1F50D;</span>
            <span class="admin-filters-title">Filtros</span>
        </div>
        <div class="admin-filters-grid">
            <div class="form-group">
                <label class="form-label">Periodo</label>
                <select name="period" class="form-input form-select">
                    <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Hoy</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Semana</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }} selected>Mes</option>
                    <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Trimestre</option>
                    <option value="semester" {{ $period === 'semester' ? 'selected' : '' }}>Semestre</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Año</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Agente</label>
                <select name="agent_id" class="form-input form-select">
                    @foreach($agentOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('agent_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Operación</label>
                <select name="operation_type_id" class="form-input form-select">
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('operation_type_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="admin-filters-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn--secondary btn--sm">Limpiar</a>
            <button type="submit" class="btn btn--primary btn--sm">Aplicar Filtros</button>
        </div>
    </form>
</section>

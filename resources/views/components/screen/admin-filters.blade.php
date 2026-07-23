@props([
    'regions' => [], 'stores' => [], 'banks' => [], 'bankAgents' => [],
    'types' => [], 'operators' => [], 'currentFilters' => [],
    'period' => 'month', 'date' => null,
])

@php
    $regionOptions = ['' => 'Todas las regiones'];
    foreach ($regions as $region) {
        $regionOptions[$region->id] = $region->name;
    }

    $storeOptions = ['' => 'Todas las tiendas'];
    foreach ($stores as $store) {
        $storeOptions[$store->id] = $store->name;
    }

    $bankOptions = ['' => 'Todos los bancos'];
    foreach ($banks as $bank) {
        $bankOptions[$bank->id] = $bank->name;
    }

    $agentOptions = ['' => 'Todos los agentes'];
    foreach ($bankAgents as $agent) {
        $agentOptions[$agent->id] = ($agent->code ?? '') . ' — ' . ($agent->bank->name ?? '');
    }

    $typeOptions = ['' => 'Todos los tipos'];
    foreach ($types as $type) {
        $typeOptions[$type->id] = $type->name;
    }
@endphp

<section class="admin-filters-panel">
    <form method="GET" action="{{ route('admin.dashboard') }}">
        <div class="admin-filters-header">
            <span aria-hidden="true">&#x1F50D;</span>
            <span class="admin-filters-title">Filtros Globales</span>
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
                <label class="form-label">Tienda</label>
                <select name="store_id" class="form-input form-select">
                    @foreach($storeOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('store_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Banco</label>
                <select name="bank_id" class="form-input form-select">
                    @foreach($bankOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('bank_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Operacion</label>
                <select name="operation_type_id" class="form-input form-select">
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('operation_type_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:var(--space-sm);margin-top:var(--space-md);">
            <a href="{{ route('admin.dashboard') }}" class="btn btn--secondary btn--sm">Limpiar</a>
            <button type="submit" class="btn btn--primary btn--sm">Aplicar Filtros</button>
        </div>
    </form>
</section>

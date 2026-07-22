<div class="dashboard-filters">
    <form method="GET" action="{{ route('admin.dashboard') }}">
        <div class="filter-row">
            <div class="filter-group">
                <label for="period">Periodo</label>
                <select name="period" id="period">
                    <option value="day" {{ ($period ?? 'month') === 'day' ? 'selected' : '' }}>Día</option>
                    <option value="week" {{ ($period ?? 'month') === 'week' ? 'selected' : '' }}>Semana</option>
                    <option value="month" {{ ($period ?? 'month') === 'month' ? 'selected' : '' }}>Mes</option>
                    <option value="quarter" {{ ($period ?? 'month') === 'quarter' ? 'selected' : '' }}>Trimestre</option>
                    <option value="semester" {{ ($period ?? 'month') === 'semester' ? 'selected' : '' }}>Semestre</option>
                    <option value="year" {{ ($period ?? 'month') === 'year' ? 'selected' : '' }}>Año</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="date">Fecha de referencia</label>
                <input type="date" name="date" id="date" value="{{ request('date', now()->format('Y-m-d')) }}">
            </div>

            <div class="filter-group">
                <label for="date_from">Desde</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="filter-group">
                <label for="date_to">Hasta</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-group">
                <label for="region_id">Región</label>
                <select name="region_id" id="region_id">
                    <option value="">Todas</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="province_id">Provincia</label>
                <select name="province_id" id="province_id">
                    <option value="">Todas</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="district_id">Distrito</label>
                <select name="district_id" id="district_id">
                    <option value="">Todos</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="store_id">Tienda</label>
                <select name="store_id" id="store_id">
                    <option value="">Todas</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-group">
                <label for="bank_id">Banco</label>
                <select name="bank_id" id="bank_id">
                    <option value="">Todos</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                            {{ $bank->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="bank_agent_id">Agente</label>
                <select name="bank_agent_id" id="bank_agent_id">
                    <option value="">Todos</option>
                    @foreach($bankAgents as $agent)
                        <option value="{{ $agent->id }}" {{ request('bank_agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="operation_type_id">Tipo de operación</label>
                <select name="operation_type_id" id="operation_type_id">
                    <option value="">Todos</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('operation_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group checkbox-group">
                <label>
                    <input type="checkbox" name="include_annulled" value="1" {{ request('include_annulled') ? 'checked' : '' }}>
                    Incluir anuladas
                </label>
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary">Aplicar filtros</button>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

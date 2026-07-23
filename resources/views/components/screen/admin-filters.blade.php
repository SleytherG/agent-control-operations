@props(['banks' => [], 'regions' => [], 'stores' => [], 'statuses' => []])

<section class="admin-filters-panel">
    <div class="admin-filters-header">
        <span aria-hidden="true">&#x1F50D;</span>
        <span class="admin-filters-title">Filtros Globales</span>
    </div>
    <div class="admin-filters-grid">
        <div class="form-group">
            <label class="form-label">Rango de Fechas</label>
            <select class="form-input form-select">
                <option>Hoy</option>
                <option>Ayer</option>
                <option>Ultimos 7 Dias</option>
                <option>Este Mes</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Tienda</label>
            <select class="form-input form-select">
                <option>Todas las tiendas</option>
                @foreach($stores as $store)
                    <option>{{ $store }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Banco</label>
            <select class="form-input form-select">
                <option>Todos los bancos</option>
                @foreach($banks as $bank)
                    <option>{{ $bank }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Estado de Operacion</label>
            <select class="form-input form-select">
                <option>Todos los estados</option>
                @foreach($statuses as $status)
                    <option>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">ID Agente</label>
            <input type="text" class="form-input" placeholder="ej. 00124">
        </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:var(--space-sm);margin-top:var(--space-md);">
        <button class="btn btn--secondary btn--sm">Limpiar</button>
        <button class="btn btn--primary btn--sm">Aplicar Filtros</button>
    </div>
</section>

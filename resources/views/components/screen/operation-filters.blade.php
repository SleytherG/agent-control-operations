<x-ui.filter-bar title="Filtros">
    <div class="form-group">
        <label class="form-label">Rango de Fechas</label>
        <input type="text" class="form-input" placeholder="01/10/2023 - 31/10/2023" readonly>
    </div>
    <div class="form-group">
        <label class="form-label">Tipo</label>
        <select class="form-input form-select">
            <option>Todos los tipos</option>
            <option>Deposito</option>
            <option>Retiro</option>
            <option>Pago Servicio</option>
            <option>Transferencia</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Estado</label>
        <select class="form-input form-select">
            <option>Todos los estados</option>
            <option>Activo</option>
            <option>Anulado</option>
            <option>Pendiente</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Referencia</label>
        <input type="text" class="form-input" placeholder="TRX-...">
    </div>
    <div class="filter-bar-actions">
        <button type="button" class="btn btn--secondary btn--sm">Limpiar</button>
        <button type="button" class="btn btn--primary btn--sm">Aplicar</button>
    </div>
</x-ui.filter-bar>

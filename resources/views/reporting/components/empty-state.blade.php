<div class="empty-state">
    @if($context === 'operator')
        <h2>Sin operaciones por ahora</h2>
        <p>No tienes operaciones registradas en este periodo. Al registrar tus operaciones, aquí verás tus métricas.</p>
    @elseif($context === 'admin')
        <h2>Sin resultados</h2>
        <p>No se encontraron operaciones con los filtros aplicados. Intenta ajustar los filtros para ver resultados.</p>
    @elseif($context === 'comparison')
        <h2>Sin datos de comparación</h2>
        <p>No hay operadores con operaciones en el periodo seleccionado.</p>
    @else
        <h2>Sin datos disponibles</h2>
        <p>No hay información para mostrar en este momento.</p>
    @endif
</div>

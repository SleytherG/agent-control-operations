# Research: Dashboards Operacionales

## Chart.js Integration

**Decision**: Chart.js 4.x como dependencia npm de desarrollo. Entrada Vite separada (`resources/js/reporting/dashboard-charts.js`) cargada solo en páginas de dashboard mediante `@vite`. No incluida en el bundle global.

**Rationale**: Única dependencia adicional justificada. Gráficos nativos (SVG/Canvas manual) requerirían más código que la librería. Cumple Principio IV al ser diferida y solo en páginas que la necesitan.

**Alternatives considered**: SVG manual — rechazado por volumen de código para doughnut/bar/line; ApexCharts — rechazado por mayor tamaño; sin gráficos — rechazado porque el spec los requiere.

## SQL Aggregation vs Eloquent Collections

**Decision**: Usar `DB::select()` con SQL nativo o `Query Builder` con `selectRaw()` y aggregates. Nunca `Operation::all()` ni `::get()` seguido de `->sum()` en PHP.

**Rationale**: Cumple Constitución XI. 50k operaciones agregadas en SQL pesan ~1KB de respuesta; cargar 50k modelos Eloquent consume memoria y tiempo innecesarios.

**Alternatives considered**: Eloquent aggregates con scopes — viable pero debe forzar SQL aggregation, no colecciones; vistas materializadas — innecesarias para el volumen del MVP.

## Period Conversion America/Lima → UTC

**Decision**: Usar Carbon con timezone. El request recibe periodo en `America/Lima`. El servicio convierte inicio/fin a UTC antes de la consulta SQL.

```php
$limaStart = Carbon::parse($date, 'America/Lima')->startOfDay()->setTimezone('UTC');
$limaEnd = Carbon::parse($date, 'America/Lima')->endOfDay()->setTimezone('UTC');
```

**Rationale**: Los datos se almacenan en UTC. La conversión es determinista y testeable.

## Filter Consistency

**Decision**: Un único `DashboardFilterRequest` valida y normaliza todos los filtros. El controlador pasa los filtros resueltos a la vista y al servicio de queries. Las vistas Blade usan los mismos datos de filtro para tarjetas, gráficos y tablas.

**Rationale**: Una sola fuente de verdad para filtros evita divergencia entre componentes visuales.

## Empty State

**Decision**: La vista Blade verifica `$metrics->count === 0` y renderiza un partial de estado vacío en lugar de gráficos con valor cero.

**Rationale**: Cumple BR-007 y SC-004. Evita gráficos engañosos.

## Operator Comparison

**Decision**: Selector múltiple o Ranking top 10 con opción "Ver todos". Gráfico de barras horizontales con nombre del operador y monto bruto. Tabla ordenable con columnas: operador, cantidad, monto bruto, entradas, salidas, neto.

**Rationale**: Legible hasta 100 operadores. Paginación en tabla para más de 10.

## Sources

- [Chart.js](https://www.chartjs.org/)
- [Laravel Query Builder aggregates](https://laravel.com/docs/13.x/queries#aggregates)
- [Carbon timezone](https://carbon.nesbot.com/docs/)

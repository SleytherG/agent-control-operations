# Quickstart Validation: Dashboards Operacionales

## Prerequisites

- Features 001, 002 y 003 completamente desplegadas.
- Operaciones registradas de prueba con diferentes tipos, fechas y operadores.
- Chart.js instalado (`npm install chart.js`).

## Setup

```bash
npm install chart.js
npm run build
```

## Validation Scenarios

### Operator Dashboard

1. Autenticarse como operador con operaciones → GET /dashboard.
2. Verificar tarjetas: count, gross_amount, cash_in, cash_out, net_movement > 0.
3. Verificar gráfico doughnut: segmentos por tipo de operación con etiquetas correctas.
4. Cambiar periodo a "día" → métricas y gráfico se actualizan.
5. Verificar que las operaciones de otro operador no afectan las métricas.
6. Autenticarse como operador sin operaciones → estado vacío visible, sin gráficos.

### Admin Dashboard

1. Admin → GET /admin/dashboard → métricas de toda la org.
2. Filtrar por tienda → tarjetas, gráficos y tabla solo de esa tienda.
3. Filtrar por banco → igual consistencia.
4. Activar "incluir anuladas" → métricas aumentan incluyendo anuladas.
5. Combinar filtros sin resultados → estado vacío en todas las secciones.
6. Verificar que "monto bruto operado" es la etiqueta, no "ingreso"/"utilidad".

### Operator Comparison

1. Admin → GET /admin/dashboard/operators → gráfico de barras con top operadores.
2. Seleccionar operadores específicos → gráfico se actualiza.
3. Ordenar tabla por monto bruto → orden correcto.
4. Verificar que con 1 solo operador no hay error visual.

### Performance

1. Con 50k operaciones seed → dashboard admin < 3s.
2. Verificar en logs que no hay queries N+1.

## Expected Tests

```text
tests/Feature/Reporting/OperatorDashboardTest.php
tests/Feature/Reporting/AdminDashboardTest.php
tests/Feature/Reporting/OperatorComparisonTest.php
tests/Feature/Reporting/DashboardAuthorizationTest.php
```

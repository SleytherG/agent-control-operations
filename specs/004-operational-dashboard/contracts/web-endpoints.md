# Web Endpoint Contracts: Dashboards Operacionales

## Shared Rules

- Solo lectura. Sin mutaciones. HTTPS, cookies, CSRF, `Cache-Control: no-store`.
- Todas las agregaciones en servidor.

## Operator Dashboard

| Method | Path | Description |
|--------|------|-------------|
| GET | /dashboard | Dashboard del operador con métricas de sus operaciones activas |

**Query params**: `period` (day, week, month; default month), `date` (fecha de referencia para el periodo; default today).

**Response**: HTML con tarjetas (count, gross_amount, cash_in, cash_out, net_movement), gráfico doughnut de distribución por tipo, gráfico line de evolución temporal.

## Admin Dashboard

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/dashboard | Dashboard administrativo con filtros |

**Query params**: `period`, `date`, `date_from`, `date_to`, `region_id`, `province_id`, `district_id`, `store_id`, `bank_id`, `bank_agent_id`, `operator_id`, `operation_type_id`, `include_annulled` (bool).

**Response**: HTML con tarjetas, gráficos y tabla de operaciones recientes paginada. Si `include_annulled=true`, el filtro `status='ACTIVE'` se omite en las queries.

## Operator Comparison

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/dashboard/operators | Vista comparativa de operadores |

**Query params**: `period`, `date`, `date_from`, `date_to`, `operator_ids[]`, `page`, `per_page`.

**Response**: HTML con gráfico de barras horizontales (top 10 por monto bruto) y tabla ordenable paginada con ranking completo.

## Period Selection

El frontend envía `period` y `date`:

| period | date example | Range in America/Lima |
|--------|-------------|----------------------|
| day | 2026-07-22 | 2026-07-22 00:00 to 23:59 |
| week | 2026-07-20 | Mon 2026-07-20 00:00 to Sun 2026-07-26 23:59 |
| month | 2026-07-15 | 2026-07-01 00:00 to 2026-07-31 23:59 |
| quarter | 2026-07-15 | Q3: 2026-07-01 00:00 to 2026-09-30 23:59 |
| semester | 2026-07-15 | S2: 2026-07-01 00:00 to 2026-12-31 23:59 |
| year | 2026-07-15 | 2026-01-01 00:00 to 2026-12-31 23:59 |

Si se envían `date_from` y `date_to`, tienen prioridad sobre `period` y `date`.

## Empty State

Cuando `count === 0`: renderizar partial `reporting.components.empty-state` con mensaje contextual. No renderizar gráficos.

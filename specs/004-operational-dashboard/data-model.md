# Data Model: Dashboards Operacionales

## No New Tables

Esta capacidad es de solo lectura. Opera sobre las tablas existentes de 001, 002 y 003.

## Tables Used

| Table | Feature | Key Columns for Dashboard |
|-------|---------|--------------------------|
| operations | 003 | amount, effective_at, status, user_id, bank_agent_id, operation_type_id, store_id |
| operation_types | 003 | cash_direction, name, bank_id |
| bank_agents | 002 | store_id, bank_id, is_active |
| stores | 002 | district_id, name |
| banks | 002 | name |
| districts | 002 | province_id, name |
| provinces | 002 | region_id, name |
| regions | 002 | name |
| users | 001 | role, status |
| user_bank_agent_assignments | 002 | user_id, bank_agent_id, is_active |

## Aggregation Queries

### Operator Dashboard

```sql
-- Summary cards
SELECT
  COUNT(*) AS operation_count,
  COALESCE(SUM(o.amount), 0) AS gross_amount,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0) AS cash_in,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) AS cash_out,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) AS net_movement
FROM operations o
JOIN operation_types ot ON o.operation_type_id = ot.id
WHERE o.status = 'ACTIVE'
  AND o.user_id = :user_id
  AND o.effective_at BETWEEN :start_utc AND :end_utc

-- Distribution by type
SELECT ot.name, ot.cash_direction, COUNT(*) AS count, COALESCE(SUM(o.amount), 0) AS total_amount
FROM operations o
JOIN operation_types ot ON o.operation_type_id = ot.id
WHERE o.status = 'ACTIVE'
  AND o.user_id = :user_id
  AND o.effective_at BETWEEN :start_utc AND :end_utc
GROUP BY ot.id, ot.name, ot.cash_direction

-- Time evolution
SELECT DATE(o.effective_at) AS day, COUNT(*) AS count, COALESCE(SUM(o.amount), 0) AS total_amount
FROM operations o
WHERE o.status = 'ACTIVE'
  AND o.user_id = :user_id
  AND o.effective_at BETWEEN :start_utc AND :end_utc
GROUP BY DATE(o.effective_at)
ORDER BY day
```

### Admin Dashboard

Mismas queries sin el filtro `user_id`. Se añaden joins opcionales según filtros: bank_agents, stores, districts, provinces, regions.

### Operator Comparison

```sql
SELECT u.id, u.username_normalized,
  COUNT(*) AS operation_count,
  COALESCE(SUM(o.amount), 0) AS gross_amount,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ELSE 0 END), 0) AS cash_in,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ELSE 0 END), 0) AS cash_out
FROM operations o
JOIN operation_types ot ON o.operation_type_id = ot.id
JOIN users u ON o.user_id = u.id
WHERE o.status = 'ACTIVE'
  AND o.effective_at BETWEEN :start_utc AND :end_utc
  AND u.role = 'OPERADOR'
GROUP BY u.id, u.username_normalized
ORDER BY gross_amount DESC
LIMIT :limit OFFSET :offset
```

## Indexes Used

Las consultas aprovechan los índices compuestos creados en 003:
- `(user_id, effective_at)` para scoping por operador
- `(bank_agent_id, effective_at)` para filtro por agente
- `(status, effective_at)` para filtro de anuladas
- `(organization_id, effective_at)` para scope global

Sin nuevos índices requeridos.

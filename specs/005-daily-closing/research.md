# Research: Cierre Operativo Diario

## Stack Reuse

**Decision**: Sin nuevas dependencias. Reutiliza Eloquent, Blade, migraciones y middleware de 001-004.

## Unique Active Closure Per Agent+Date

**Decision**: Partial unique index en MySQL 8.0: `CREATE UNIQUE INDEX uq_active_closure ON daily_closures (bank_agent_id, business_date) WHERE status = 'ACTIVO'`. MariaDB: columna virtual con expresión CASE + UNIQUE sobre la virtual.

**Rationale**: La constraint en base de datos garantiza atomicidad sin locks adicionales. El intento de insertar un segundo cierre activo produce `IntegrityConstraintViolation`.

**Alternatives considered**: Validación solo en aplicación — rechazado por riesgo de race condition; lock explícito — rechazado por complejidad innecesaria.

## Consolidation Query

**Decision**: `INSERT INTO daily_closure_operations (daily_closure_id, operation_id) SELECT ?, id FROM operations WHERE bank_agent_id = ? AND status = 'ACTIVE' AND DATE(effective_at) = ?`. Métricas calculadas con `SUM` en la misma transacción.

**Rationale**: Atómico y eficiente. Sin cargar colecciones.

## Blocking Post-Confirmation Operations

**Decision**: Extender `RegisterOperation::execute` y `AnnulOperation::execute` de 003 con una verificación: `DailyClosure::where('bank_agent_id', $agentId)->where('business_date', $effectiveDate)->where('status', 'CONFIRMADO')->exists()`. Si existe, lanzar excepción.

**Rationale**: Reutiliza los actions existentes sin duplicar lógica. Una sola consulta indexada.

## POR_CONFIRMAR Detection

**Decision**: Al consolidar, contar `COUNT(*) WHERE ot.cash_direction = 'POR_CONFIRMAR'`. Guardar `has_pending_confirm BOOLEAN` en el cierre. En la vista, si es true, mostrar warning y etiquetar neto como "Pendiente de confirmación".

**Rationale**: Precalculado en generación, no requiere joins adicionales en cada visualización.

## Regeneration Before Confirmation

**Decision**: El endpoint de generación acepta un `regenerate=true`. Si el cierre está ACTIVO, elimina las operaciones asociadas existentes y las recalcula. No aplica si está CONFIRMADO o REABIERTO.

**Rationale**: Permite reflejar operaciones registradas después de la generación inicial sin crear un nuevo cierre.

## Sources

- [MySQL partial indexes](https://dev.mysql.com/doc/refman/8.4/en/create-index.html)
- [MariaDB virtual columns](https://mariadb.com/kb/en/virtual-computed-columns/)

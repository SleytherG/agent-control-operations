# Research: Registro de Operaciones

## Stack Reuse

**Decision**: Reutilizar el stack completo de 001 y 002. Sin nuevas dependencias. Eloquent, Blade, `lcobucci/jwt`, PHPUnit, Vite.

**Rationale**: La funcionalidad de operaciones es el núcleo del dominio pero técnicamente es CRUD con validaciones adicionales. No requiere nuevas librerías.

## Idempotency Token

**Decision**: Columna `idempotency_key CHAR(64)` UNIQUE en `operations`. El servidor genera un token aleatorio al renderizar el formulario. En el POST, se busca por clave; si existe, se devuelve el resultado existente (redirect o JSON). Si no existe, se inserta con la clave.

**Rationale**: La constraint UNIQUE de base de datos garantiza atomicidad. No depende de sesiones ni locks adicionales. El token viaja como campo hidden del formulario y es de un solo uso por carga de página.

**Alternatives considered**: Token en sesión PHP — rechazado porque la sesión web es solo para CSRF; hash del contenido — rechazado porque referencias opcionales pueden repetirse legítimamente.

## Decimal Precision

**Decision**: `DECIMAL(18,2)` para todas las monedas en MVP. La precisión por moneda se configura en un archivo de monedas si se añaden divisas con diferentes decimales.

**Rationale**: PEN y la mayoría de monedas usan 2 decimales. 18 dígitos totales permite montos de hasta 9 billones con centavos.

**Alternatives considered**: `DECIMAL(10,2)` — insuficiente para agregados futuros; integer de centavos — rechazado por complejidad de conversión en queries.

## Retroactive Date Window

**Decision**: Validación en `RegisterOperationRequest`: `effective_at >= now() - config('operations.retroactive_window_hours', 24)` y `effective_at <= now()`. Configurable en `config/operations.php`.

**Rationale**: Simple, configurable y testeable. El servidor es la autoridad temporal.

## Annulment Window

**Decision**: `AnnulOperationRequest` o `OperationPolicy::annul()` verifica `now() - recorded_at <= config('operations.annulment_window_hours', 24)` para operadores. Administradores sin restricción.

**Rationale**: Consistente con BR-011 y FR-012/FR-013. Configurable sin cambiar lógica.

## Type Catalog: Bank-Specific + General

**Decision**: Al consultar tipos para un agente: `WHERE (bank_id = :agent_bank_id OR bank_id IS NULL) AND is_active = true`. La unicidad se valida por separado: nombre único entre tipos del mismo banco, y nombre único entre tipos generales.

**Rationale**: Implementa la decisión de clarificación Q1. Simple en SQL y Eloquent.

## Aggregation Queries

**Decision**: `SELECT COUNT(*), SUM(amount), SUM(CASE WHEN cash_direction='ENTRADA' THEN amount ELSE 0 END)... FROM operations WHERE status='ACTIVA' GROUP BY ...`. Nunca cargar colecciones para sumar.

**Rationale**: Cumple Constitución XI y SC-005. Los totales se calculan en la base de datos.

## Sources

- [Laravel 13 docs](https://laravel.com/docs/13.x)
- [MySQL DECIMAL](https://dev.mysql.com/doc/refman/8.4/en/precision-math-decimal-characteristics.html)
- [ISO 4217 currency codes](https://en.wikipedia.org/wiki/ISO_4217)

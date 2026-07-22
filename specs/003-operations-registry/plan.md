# Implementation Plan: Registro de Operaciones

**Branch**: `003-operations-registry` | **Date**: 2026-07-22 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/003-operations-registry/spec.md`

## Summary

Crear el módulo `Operations` con el catálogo de tipos de operación y el libro digital de operaciones. El operador registra en agentes asignados activos con monto decimal, el administrador mantiene el catálogo y ambos consultan con filtros. Las operaciones se anulan sin eliminación física, con auditoría completa. Se implementa prevención de doble envío mediante token de idempotencia.

## Technical Context

**Language/Version**: PHP 8.3; JavaScript ES Modules; HTML5; CSS3

**Primary Dependencies**: Laravel 13, Eloquent, Blade; reutiliza `lcobucci/jwt` de 001, modelos de 002

**Storage**: MySQL 8.0 o MariaDB, InnoDB, FK, índices compuestos; `DECIMAL(18,2)` para montos; sin Redis

**Time & Money**: UTC persistido, `America/Lima` display; montos `DECIMAL(18,2)`, moneda `CHAR(3)`; nunca float

**Authentication & Session**: Reutiliza 001-auth-session; operador debe tener asignación activa validada al registrar

**Testing**: PHPUnit Feature; autorización positiva/negativa, precisión decimal, anulación, idempotencia

**Target Platform**: Apache/Nginx, hosting PHP compartido, `public/`

**Project Type**: aplicación web monolítica modular, renderizada en servidor

**Performance Goals**: registro <1s, listados paginados <2s para 10k operaciones, totales en SQL

**Constraints**: sin SPA, Redis, WebSockets, workers; sin eliminación física de operaciones

**Scale/Scope**: catálogo de ~50 tipos, volumen inicial ~10k operaciones, paginación de 25 por página

## Constitution Check

*GATE: aprobado antes de Phase 0. Re-check post-design.*

- **I. Desarrollo dirigido por especificaciones**: PASS. Spec con problema, actores, historias, reglas, exclusiones y aclaración.
- **II. Entregas pequeñas**: PASS. Solo operaciones y tipos. Cierres y reporting se difieren.
- **III. Portabilidad económica**: PASS. Mismo stack. Sin componentes prohibidos.
- **IV. Interfaz mínima**: PASS. Blade, HTML, CSS, ES Modules. Sin SPA.
- **V. Seguridad del servidor**: PASS. Policies, scopes Eloquent, Form Requests imponen rol y asignación.
- **VI. Sesiones seguras**: PASS. Reutiliza ciclo JWT completo de 001.
- **VII. Integridad y trazabilidad**: PASS. Sin eliminación física, anulación con before/after, auditoría.
- **VIII. Exactitud monetaria/temporal**: PASS. `DECIMAL(18,2)`, UTC, `America/Lima`, sin float.
- **IX. Privacidad**: PASS. Sin datos de cliente, tarjeta ni cuenta.
- **X. Pruebas obligatorias**: PASS. Autorización, precisión decimal, anulación, idempotencia, retroactividad.
- **XI. Recursos responsables**: PASS. Paginación, índices compuestos, agregación SQL.
- **XII. Observabilidad/recuperación**: PASS. Auditoría, migraciones reversibles, backups.
- **XIII. Gobernanza**: PASS. Sin excepciones.

## Module Boundaries

- `Operations`: tipos de operación, registro, consulta, anulación, idempotencia.
- `IdentityAccess`: reutiliza middleware de autenticación y políticas.
- `BankingNetwork`: reutiliza agentes, asignaciones y bancos de 002.
- `Audit`: registra creación y anulación de operaciones.

## Data Model — New Tables

### operation_types

`organization_id` FK, `bank_id` nullable FK (null = general), `name` único por `(bank_id, name)` o `(organization_id, name)` si bank_id es null, `description` nullable, `cash_direction` ENUM (`ENTRADA`, `SALIDA`, `NEUTRA`, `POR_CONFIRMAR`), `is_active`, `deactivated_at`. Índice `(organization_id, is_active)`.

### operations

`organization_id` FK, `store_id` FK, `bank_agent_id` FK, `operation_type_id` FK, `user_id` FK (registrador), `amount DECIMAL(18,2)`, `currency CHAR(3)` default `PEN`, `effective_at DATETIME(6)`, `recorded_at DATETIME(6)`, `status` (`ACTIVA`, `ANULADA`), `reference` nullable VARCHAR(100), `observation` nullable VARCHAR(500), `annulled_by` nullable FK users, `annulled_at` nullable DATETIME(6), `annulment_reason` nullable VARCHAR(500).

Índices: `(user_id, effective_at)`, `(bank_agent_id, effective_at)`, `(store_id, effective_at)`, `(operation_type_id, effective_at)`, `(status, effective_at)`, `(organization_id, effective_at)`. Composite FK `(bank_agent_id, store_id)`.

## Idempotency

Columna `idempotency_key CHAR(64)` UNIQUE en `operations`. El formulario incluye un token generado por servidor. Al enviar, el sistema busca la clave; si existe, devuelve el resultado de la operación original sin crear duplicado. El token se genera por carga de formulario y se consume en el primer envío exitoso.

## Authorization Flows

1. Middleware `AuthenticateJwtSession` valida sesión.
2. `OperationPolicy`: administrador ve/anula todas; operador solo propias y dentro de ventana.
3. `OperationTypePolicy`: solo administrador administra el catálogo.
4. Al registrar, `RegisterOperation` action valida asignación activa del operador al agente.
5. Scopes Eloquent: `Operation::scopeByUser()`, `Operation::scopeActive()` antes de filtros.

## Validation Strategy

- `RegisterOperationRequest`: monto > 0, agente activo y asignado, fecha efectiva dentro de ventana, no futura.
- `AnnulOperationRequest`: motivo obligatorio, ventana de operador.
- `OperationTypeRequest`: nombre único por banco/global.
- Base de datos: CHECK `amount > 0`, FK, unique idempotency_key.

## Audit Strategy

- `audit_logs`: `OPERATION_CREATED` en registro, `OPERATION_ANNULLED` en anulación con before/after (amount, status).
- Sin eliminación de auditoría desde interfaz.

## Testing Strategy

- Feature: registro válido, agente no asignado, monto inválido, fecha retroactiva/futura, idempotencia, anulación, ventana de operador vencida.
- Autorización: operador no ve/anula operaciones ajenas, administrador ve/anula todas.
- Precisión: 10k operaciones con decimales variados sin errores de redondeo.
- Totales: SQL aggregate excluye anuladas.

## Migration Plan

1. `operation_types`
2. `operations` (con idempotency_key unique)
3. Seed de tipos: Depósito (ENTRADA), Retiro (SALIDA), Consulta de saldo (NEUTRA), Pago de servicios (SALIDA), Transferencia (NEUTRA) para cada banco y generales.

Cada migración con `down()` inverso. Las operaciones dependen de tablas de 002.

## Deployment And Rollback

Mismo procedimiento de 001. Migraciones reversibles. Rollback productivo prefiere forward fix si ya existen operaciones registradas.

## Contracts

Documentados en `contracts/web-endpoints.md`.

## Project Structure

```text
app/Modules/Operations/
├── Models/OperationType.php, Operation.php
├── Http/Controllers/OperationTypeController.php, OperationController.php
├── Http/Requests/
├── Policies/OperationPolicy.php, OperationTypePolicy.php
└── Application/Actions/RegisterOperation.php, AnnulOperation.php

database/migrations/ (2 nuevas migraciones: 000015, 000016)
routes/operations.php
resources/views/operations/
tests/Feature/Operations/
```

## Exception Tracking

No hay excepciones constitucionales.

# Implementation Plan: Cierre Operativo Diario

**Branch**: `005-daily-closing` | **Date**: 2026-07-22 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/005-daily-closing/spec.md`

## Summary

Crear el módulo `DailyClosing` con las tablas `daily_closures` y `daily_closure_operations`. El cierre consolida operaciones activas de un agente en una fecha de negocio. El administrador confirma (bloquea operaciones) y puede reabrir con motivo auditado. Ambos roles pueden generar; solo admin confirma/reabre. Advertencia POR_CONFIRMAR. Unique constraint agente+fecha activo.

## Technical Context

**Language/Version**: PHP 8.3; Blade; sin nuevas dependencias JS

**Primary Dependencies**: Laravel 13, Eloquent; reutiliza stack de 001-004

**Storage**: MySQL 8.0/MariaDB, InnoDB, FK, unique compuesto (bank_agent_id, business_date, status)

**Time & Money**: Fecha de negocio en `America/Lima`; montos `DECIMAL(18,2)`; "monto bruto operado"

**Authentication & Session**: Reutiliza 001

**Testing**: PHPUnit Feature; autorización, transiciones de estado, bloqueo post-confirmación

**Constraints**: sin SPA, Redis, WebSockets, workers; sin eliminación física de cierres; sin efectivo físico

**Performance Goals**: generación <3s para 500 ops; consolidación en SQL

## Constitution Check

*GATE: aprobado. Re-check post-design.*

- **I–IV**: PASS. Spec completa. Sin componentes prohibidos. Blade + CSS.
- **V–VI**: PASS. Políticas, Gates, middleware. Sesión JWT reutilizada.
- **VII**: PASS. Sin eliminación física. Auditoría completa de transiciones. Operaciones bloqueadas post-confirmación.
- **VIII**: PASS. `DECIMAL(18,2)`, `America/Lima`, "monto bruto operado".
- **IX–X**: PASS. Sin datos de cliente. Pruebas de autorización, estado, bloqueo.
- **XI**: PASS. SQL aggregation. Sin colecciones completas.
- **XII–XIII**: PASS. Migraciones reversibles. Auditoría.

## Module Boundaries

- `DailyClosing`: cierres, consolidación, confirmación, reapertura.
- `Operations`: se extiende con validación de cierre confirmado.
- `Audit`: registra transiciones de estado del cierre.

## Data Model — New Tables

### daily_closures

`organization_id`, `store_id`, `bank_agent_id` FK, `business_date DATE`, `status` (ACTIVO, CONFIRMADO, REABIERTO), `confirmed_by` nullable FK users, `confirmed_at` nullable, `reopened_by` nullable FK users, `reopened_at` nullable, `reopen_reason` nullable, métricas consolidadas (`operation_count`, `gross_amount`, `cash_in`, `cash_out`, `net_movement` como DECIMAL), `has_pending_confirm` BOOLEAN, timestamps.

UNIQUE `(bank_agent_id, business_date)` WHERE `status = 'ACTIVO'` (partial/functional index). INDEX `(bank_agent_id, business_date)`, `(organization_id, business_date)`.

State: `ACTIVO` → `CONFIRMADO` → `REABIERTO` → `CONFIRMADO` (cíclico).

### daily_closure_operations

`daily_closure_id` FK, `operation_id` FK UNIQUE (each operation belongs to exactly one closure). Timestamps de creación. INDEX `(daily_closure_id)`.

## Authorization

- `DailyClosingPolicy::generate`: admin puede cualquier agente; operador solo agentes asignados activos.
- `DailyClosingPolicy::confirm`: solo admin.
- `DailyClosingPolicy::reopen`: solo admin.
- `DailyClosingPolicy::view`: admin ve todos; operador solo de agentes asignados.

## Confirmation Blocking

Al confirmar un cierre, `RegisterOperation::execute` y `AnnulOperation::execute` verifican si existe un `daily_closures` CONFIRMADO para el agente y la fecha de la operación. Si existe, rechazan con mensaje claro. Esto se implementa extendiendo los actions de 003.

## Reopen Flow

1. Admin solicita reapertura con motivo.
2. Se verifica que el cierre esté CONFIRMADO.
3. En transacción: estado → REABIERTO, `reopened_by`, `reopened_at`, `reopen_reason`, auditoría.
4. Las operaciones vuelven a ser modificables.

## Validation Strategy

- `GenerateClosingRequest`: `bank_agent_id` activo y asignado al usuario (si es operador), `business_date` válida.
- `ConfirmClosingRequest`: verifica que el cierre está ACTIVO.
- `ReopenClosingRequest`: motivo obligatorio, verifica que el cierre está CONFIRMADO.
- Base de datos: UNIQUE partial para prevenir duplicados activos.

## Audit Strategy

- `audit_logs`: `CLOSING_GENERATED`, `CLOSING_CONFIRMED`, `CLOSING_REOPENED` con actor, entidad, before/after (métricas y estado).

## Testing Strategy

- Feature: generación, consolidación correcta, confirmación, bloqueo post-confirmación, reapertura, doble cierre rechazado.
- Autorización: operador no confirma/reabre, operador solo ve sus agentes.
- POR_CONFIRMAR: advertencia visible, etiqueta no definitiva.
- Integración con Operations: anulación rechazada post-confirmación, registro rechazado.

## Migration Plan

1. `daily_closures` (000017)
2. `daily_closure_operations` (000018)

Cada migración con `down()` inverso. Dependen de `bank_agents`, `operations`, `users`.

## Deployment And Rollback

Mismo procedimiento. Migraciones reversibles. Rollback productivo prefiere forward fix si existen cierres confirmados.

## Contracts

Documentados en `contracts/web-endpoints.md`.

## Project Structure

```text
app/Modules/DailyClosing/
├── Models/DailyClosure.php, DailyClosureOperation.php
├── Http/Controllers/DailyClosingController.php
├── Http/Requests/
└── Policies/DailyClosingPolicy.php

database/migrations/ (000017, 000018)
routes/daily-closing.php
resources/views/daily-closing/
tests/Feature/DailyClosing/
```

## Exception Tracking

No excepciones constitucionales.

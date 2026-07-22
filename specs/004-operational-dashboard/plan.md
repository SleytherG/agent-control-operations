# Implementation Plan: Dashboards Operacionales

**Branch**: `004-operational-dashboard` | **Date**: 2026-07-22 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/004-operational-dashboard/spec.md`

## Summary

Crear el módulo `Reporting` con dashboards de solo lectura para operador y administrador. Métricas agregadas en SQL: cantidad, monto bruto, entradas, salidas, movimiento neto, distribución por tipo y evolución temporal. Gráficos con Chart.js cargado de forma diferida. Vista comparativa de operadores con ranking. Sin nuevas tablas.

## Technical Context

**Language/Version**: PHP 8.3; JavaScript ES Modules; HTML5; CSS3; Chart.js 4.x (diferido)

**Primary Dependencies**: Laravel 13, Eloquent, Blade, Chart.js; reutiliza stack de 001-003

**Storage**: Solo lectura sobre tablas existentes; sin migraciones nuevas

**Time & Money**: Periodos en `America/Lima` convertidos a UTC para queries; métricas con `DECIMAL(18,2)`; "monto bruto operado" como etiqueta canónica

**Authentication & Session**: Reutiliza 001; operador ve solo sus datos

**Testing**: PHPUnit Feature; autorización, precisión de agregaciones, consistencia de filtros

**Target Platform**: Apache/Nginx, hosting PHP compartido

**Performance Goals**: dashboard administrativo <3s para 50k operaciones; todas las agregaciones en SQL

**Constraints**: sin SPA; Chart.js solo en páginas de dashboard; sin nuevas tablas

## Constitution Check

*GATE: aprobado. Re-check post-design.*

- **I–IV**: PASS. Spec completa. Entrega solo reporting. Sin componentes prohibidos. Blade + CSS + ES Modules + Chart.js diferido.
- **V–VI**: PASS. Autorización en servidor. Reutiliza sesión JWT.
- **VII–IX**: PASS. Sin eliminación. Sin float. Monto bruto, no ganancia. Sin datos de cliente.
- **X**: PASS. Pruebas de agregación, autorización, filtros y estado vacío.
- **XI**: PASS. SQL aggregation, sin N+1, sin colecciones completas.
- **XII–XIII**: PASS. Sin nuevas tablas. Sin excepciones.

**Post-design re-check**: PASS.

## Module Boundaries

- `Reporting`: queries agregadas, DTOs de dashboard, controladores de vistas.
- Reutiliza `Operations`, `BankingNetwork`, `Organization`, `IdentityAccess` como fuentes de datos.

## Data Model

Sin nuevas tablas. Las consultas usan las tablas existentes:

- `operations` con índices `(user_id, effective_at)`, `(bank_agent_id, effective_at)`, `(status, effective_at)`, etc.
- `operation_types` con `cash_direction` y `bank_id`.
- `bank_agents`, `stores`, `banks`, `districts`, `provinces`, `regions` para joins de filtros.

## SQL Aggregation Pattern

```sql
SELECT
  COUNT(*) as count,
  COALESCE(SUM(amount), 0) as gross_amount,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'ENTRADA' THEN amount ELSE 0 END), 0) as cash_in,
  COALESCE(SUM(CASE WHEN ot.cash_direction = 'SALIDA' THEN amount ELSE 0 END), 0) as cash_out
FROM operations o
JOIN operation_types ot ON o.operation_type_id = ot.id
WHERE o.status = 'ACTIVE'  -- excluye anuladas por defecto
  AND o.effective_at BETWEEN :start_utc AND :end_utc
  AND o.user_id = :user_id  -- operador
```

## Period Conversion

Los periodos se definen en `America/Lima`:

- Día: `America/Lima` 00:00:00 → 23:59:59.999999
- Semana: lunes 00:00:00 → domingo 23:59:59.999999
- Mes: día 1 00:00:00 → último día 23:59:59.999999
- Trimestre: Q1 = ene-mar, Q2 = abr-jun, Q3 = jul-sep, Q4 = oct-dic
- Semestre: S1 = ene-jun, S2 = jul-dic
- Año: ene 1 → dic 31

El servidor convierte los límites a UTC antes de la consulta.

## Authorization Strategy

- `DashboardPolicy`: `viewOperatorDashboard` requiere autenticación y scoped query con `user_id = auth()->id()`. `viewAdminDashboard` requiere `ADMINISTRADOR_PROPIETARIO`.
- El controlador del operador fuerza `user_id` antes de cualquier agregación.
- El controlador del administrador aplica filtros opcionales pero nunca scoped por usuario.

## Validation Strategy

- Form Request para filtros: fechas válidas, periodo predefinido válido, IDs existentes y activos.
- Valores por defecto: periodo = mes actual, sin filtros adicionales.

## Testing Strategy

- Feature: métricas del operador, métricas del administrador con filtros, comparativa.
- Precisión: las agregaciones del dashboard coinciden con consultas SQL directas.
- Autorización: operador no ve datos ajenos, admin ve todo.
- Estado vacío: sin operaciones, filtros sin resultados.
- Terminología: sin "ingreso", "utilidad" ni "ganancia" en vistas.

## Chart.js Integration

- Instalado como dependencia npm de desarrollo.
- Cargado mediante `@vite` con entrada separada en `vite.config.js`.
- Solo en páginas de dashboard; no en el bundle global.
- Gráficos: doughnut para distribución por tipo, bar para comparativa de operadores, line para evolución temporal.

## Contracts

Documentados en `contracts/web-endpoints.md`.

## Project Structure

```text
app/Modules/Reporting/
├── Http/Controllers/DashboardController.php
├── Http/Requests/DashboardFilterRequest.php
├── Policies/DashboardPolicy.php
└── Services/DashboardQueryService.php

resources/views/reporting/
├── operator-dashboard.blade.php
├── admin-dashboard.blade.php
└── components/

resources/js/reporting/dashboard-charts.js (Chart.js deferred)

routes/reporting.php
tests/Feature/Reporting/
```

## Exception Tracking

No excepciones. Chart.js está justificado como dependencia diferida solo en páginas con gráficos, conforme al Principio IV.

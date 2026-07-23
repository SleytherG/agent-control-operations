# Data Model: Integración Visual Stitch

**Feature**: 007-stitch-visual-integration
**Date**: 2026-07-22

## No New Entities

Esta especificación no crea nuevas tablas, columnas ni migraciones. Todas las entidades requeridas ya existen en la base de datos (18 tablas migradas, specs 001-005). Este documento mapea qué entidades existentes se exponen en cada pantalla migrada y qué campos son relevantes para la interfaz Stitch.

## Entity Usage Map

### User (IdentityAccess)

**Tabla**: `users` | **Modelo**: `App\Modules\IdentityAccess\Models\User`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `name` | Nombre en topbar y greeting de dashboard | Layout, M3, M6 |
| `role` | Sidebar navigation, badge en topbar | Layout |
| `status` | Control de acceso (ACTIVE/INACTIVE) | Login (error si INACTIVE) |
| `password_changed_at` | Forzar cambio de contraseña | Middleware redirect |

**Relaciones relevantes**:
- `assignments` → `UserBankAgentAssignment` → `BankAgent` (agentes del operador para formulario M4)
- `authSessions` → `AuthSession` (historial de sesiones M7)

### AuthSession (IdentityAccess)

**Tabla**: `auth_sessions` | **Modelo**: `App\Modules\IdentityAccess\Models\AuthSession`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `expires_at` | Contador de sesión en topbar, modal de expiración | Layout (M0) |
| `ip_address` | Listado de sesiones activas | Admin (M7) |
| `user_agent` | Listado de sesiones activas | Admin (M7) |

### Operation (Operations)

**Tabla**: `operations` | **Modelo**: `App\Modules\Operations\Models\Operation`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `id` | Identificador en confirmación y detalle | M4 (confirmación), M5 (tabla), M9 (detalle) |
| `amount` | Monto en tabla, métricas, dashboards | M3, M4, M5, M6, M8 |
| `currency` | Símbolo monetario (siempre PEN) | M4, M5 |
| `effective_at` | Fecha/hora en tabla de historial | M5 |
| `status` | Badge ACTIVA/ANULADA | M5, M8, M9 |
| `reference` | Columna en tabla de historial | M5 |
| `observation` | Detalle de operación | M9 |
| `idempotency_key` | Prevención de doble envío (hidden) | M4 |

**Relaciones relevantes**:
- `bankAgent` → `BankAgent` (nombre del agente en tablas)
- `operationType` → `OperationType` (tipo y cash_direction)
- `user` → `User` (operador que registró)

### OperationType (Operations)

**Tabla**: `operation_types` | **Modelo**: `App\Modules\Operations\Models\OperationType`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `name` | Selector en formulario, filtros, gráficos | M4, M5, M6 |
| `cash_direction` | Clasificación ENTRADA/SALIDA/NEUTRA/POR_CONFIRMAR | M6, M8 (métricas direccionales) |

### DailyClosure (DailyClosing)

**Tabla**: `daily_closures` | **Modelo**: `App\Modules\DailyClosing\Models\DailyClosure`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `status` | Indicador visual ACTIVO/CONFIRMADO/REABIERTO | M8 |
| `business_date` | Fecha del cierre | M8 |
| `total_operations` | KPI card | M8 |
| `gross_amount` | KPI card | M8 |
| `total_cash_in` | KPI card | M8 |
| `total_cash_out` | KPI card | M8 |
| `net_movement` | KPI card | M8 |
| `has_pending_confirm` | Warning de operaciones por confirmar | M8 |
| `confirmed_by` | Auditoría (quién confirmó) | M8 |
| `reopened_by` | Auditoría (quién reabrió) | M8 |
| `reopen_reason` | Motivo de reapertura | M8 |

**Relaciones relevantes**:
- `bankAgent` → `BankAgent` → `Store`, `Bank` (contexto del cierre)
- `operations` → `Operation` (listado de operaciones del cierre)

### BankAgent (BankingNetwork)

**Tabla**: `bank_agents` | **Modelo**: `App\Modules\BankingNetwork\Models\BankAgent`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `code` | Identificador en selectores y tablas | M4, M5, M7, M8 |
| `is_active` | Badge ACTIVO/INACTIVO | M7 |

**Relaciones relevantes**:
- `store` → `Store` (nombre de tienda)
- `bank` → `Bank` (nombre de banco)
- `assignments` → `UserBankAgentAssignment` (operadores asignados)

### Estructura organizacional (Organization)

**Tablas**: `regions`, `provinces`, `districts`, `stores` | **Modelos**: `Region`, `Province`, `District`, `Store`

| Entidad | Campo relevante | Uso en UI |
|---------|----------------|-----------|
| Region | `name` | Filtros admin dashboard, CRUD admin |
| Province | `name` | Filtros admin dashboard, CRUD admin |
| District | `name` | Filtros admin dashboard, CRUD admin |
| Store | `name` | Filtros, contexto en topbar (futuro) |

### Banking Network

**Tabla**: `banks` | **Modelo**: `App\Modules\BankingNetwork\Models\Bank`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `name` | Selector en formulario, filtros | M4, M6, M7 |

**Tabla**: `user_bank_agent_assignments` | **Modelo**: `UserBankAgentAssignment`

| Campo | Uso en UI | Pantallas |
|-------|-----------|-----------|
| `user_id` → `bank_agent_id` | Determina qué agentes ve el operador | M4 (filtrar agentes en formulario) |
| `is_active` | Solo agentes con asignación activa | M4 |

## DashboardQueryService Output Format

El `DashboardQueryService` devuelve objetos/stdClass con las siguientes formas. La vista debe acceder a estas propiedades para alimentar los componentes Stitch:

```text
// Operator Dashboard
$metrics = {
    operation_count: int,
    gross_amount: string (formatted "S/ X,XXX.XX"),
    cash_in: string,
    cash_out: string,
    net_movement: string
}

$typeDistribution = [
    { type: string, count: int, percentage: float }
]

$timeEvolution = {
    labels: string[],
    entradas: int[],
    salidas: int[]
}
```

## Pivot Tables

**Tabla**: `daily_closure_operations` | **Modelo**: `DailyClosureOperation`

Relaciona `DailyClosure` ↔ `Operation`. Sin columnas adicionales relevantes para UI.

---

## Notas

- No se requiere migración de base de datos
- Los seeders existentes (`DatabaseSeeder`, `OperationalStructureSeeder`, `OperationTypeSeeder`) proporcionan datos de desarrollo
- Las factories existentes (15) permiten generar datos de prueba para verificación visual

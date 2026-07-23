# View Contracts: Variables y Componentes por Pantalla

**Feature**: 007-stitch-visual-integration
**Date**: 2026-07-22

Cada contrato define las variables que el controlador DEBE pasar a la vista y los componentes Stitch que la vista DEBE usar. El middleware `AuthenticateJwtSession` garantiza que `$user`, `$role` y `$sessionExpiresAt` están disponibles en todas las vistas autenticadas.

---

## C01: Login

**Ruta**: `GET /login` | **Controller**: `LoginController@showLoginForm`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$loginState` | `string` (`'normal'`) | Controller | Sí |

**Ruta**: `POST /login` | **Controller**: `LoginController@login`

| Variable (flash) | Tipo | Origen | Obligatorio |
|------------------|------|--------|-------------|
| `$errors` | `MessageBag` | Laravel (Validación) | No |
| `login_state` | `string` | Session flash | No — solo en error |

**Componentes requeridos**:
- `layouts.guest` (layout)
- `x-ui.input` (×2: username, password)
- `x-ui.button` (submit con estados loading/disabled)

---

## C02: Home

**Ruta**: `GET /home` | **Controller**: `LoginController@home`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$expiresAt` | `Carbon\|string` | Controller | Sí |
| `$user` | `User` | Middleware | Sí |
| `$role` | `string` | Middleware | Sí |

**Componentes requeridos**:
- `layouts.authenticated` (layout)
- `x-ui.metric-card` (opcional, estado de sesión)

---

## C03: Dashboard Operador

**Ruta**: `GET /operator/dashboard` | **Controller**: `DashboardController@operatorDashboard`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$metrics` | `object` | DashboardQueryService | Sí |
| `$typeDistribution` | `array` | DashboardQueryService | Sí |
| `$timeEvolution` | `object` | DashboardQueryService | Sí |
| `$recentOperations` | `Collection\|array` | Controller (Operation query) | Sí |
| `$period` | `string` | Controller | Sí |
| `$user` | `User` | Middleware | Sí |
| `$role` | `string` (`'operator'`) | Middleware | Sí |

**Componentes requeridos**:
- `layouts.authenticated`
- `x-screen.operator-metrics` (`:metrics="$metrics"`)
- `x-ui.chart-container` (×2: doughnut + bar/line)
- `x-ui.data-table` (operaciones recientes)
- `x-ui.badge` (estados)
- `x-ui.empty-state` (cuando `$metrics->operation_count === 0`)

**Resources**: `@vite('resources/js/reporting/dashboard-charts.js')` solo en `@section('head')`

---

## C04: Registro de Operación

**Ruta**: `GET /operations/create` | **Controller**: `OperationController@create`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$assignments` | `Collection` (UserBankAgentAssignment) | Controller | Sí |
| `$types` | `Collection` (OperationType) | Controller | Sí |
| `$idempotencyKey` | `string` (UUID) | Controller | Sí |
| `$user` | `User` | Middleware | Sí |
| `$role` | `string` | Middleware | Sí |

**Componentes requeridos**:
- `layouts.authenticated`
- `x-screen.operation-form` (`:assignments`, `:types`, `:idempotencyKey`)
- `x-ui.empty-state` (cuando `$assignments` está vacío)

---

## C05: Historial de Operaciones

**Ruta**: `GET /operations` | **Controller**: `OperationController@index`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$operations` | `LengthAwarePaginator` | ListOperations action | Sí |
| `$agents` | `Collection` | Controller | Sí |
| `$types` | `Collection` | Controller | Sí |
| `$summary` | `array` (total_ops, total_amount, cash_in, cash_out, net) | Controller (query) | Sí |
| `$filters` | `array` (query params activos) | Request | No |

**Componentes requeridos**:
- `layouts.authenticated`
- `x-screen.operation-filters` (`:agents`, `:types`)
- `x-ui.metric-card` (×5: summary)
- `x-ui.data-table` (operaciones paginadas)
- `x-ui.badge` (estado)
- `x-ui.pagination`
- `x-ui.empty-state` (sin resultados)

---

## C06: Dashboard Administrativo

**Ruta**: `GET /admin/dashboard` | **Controller**: `DashboardController@adminDashboard`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$metrics` | `object` | DashboardQueryService | Sí |
| `$typeDistribution` | `array` | DashboardQueryService | Sí |
| `$timeEvolution` | `object` | DashboardQueryService | Sí |
| `$flowByRegion` | `object` | DashboardQueryService | Sí |
| `$bankDistribution` | `array` | DashboardQueryService | Sí |
| `$topStores` | `array` | DashboardQueryService | Sí |
| `$topWorkers` | `array` | DashboardQueryService | Sí |
| `$regions` | `Collection` | Controller (Region::all()) | Sí |
| `$stores` | `Collection` | Controller | Sí |
| `$banks` | `Collection` | Controller | Sí |
| `$types` | `Collection` | Controller | Sí |
| `$filters` | `array` | Controller | No |
| `$period` | `string` | Controller | Sí |

**Componentes requeridos**:
- `layouts.authenticated`
- `x-screen.admin-filters` (multidimensional)
- `x-ui.metric-card` (×4 KPI principals + ×5 secundaris)
- `x-ui.chart-container` (×3)
- `x-screen.operator-comparison` (`:stores`, `:workers`)

---

## C08: Cierre Diario — Show

**Ruta**: `GET /daily-closing/{id}` | **Controller**: `DailyClosingController@show`

| Variable | Tipo | Origen | Obligatorio |
|----------|------|--------|-------------|
| `$closure` | `DailyClosure` (Eloquent) | Controller | Sí |
| `$breakdownByType` | `Collection\|array` | Controller | Sí |
| `$breakdownByOperator` | `Collection\|array` | Controller | Sí |
| `$closureOperations` | `Collection\|Paginator` | Controller | Sí |
| `$annulledOperations` | `Collection` | Controller | Sí |

**Componentes requeridos**:
- `layouts.authenticated`
- `x-ui.metric-card` (×5: KPIs del cierre)
- `x-screen.closing-warning` (condicional: `$closure->has_pending_confirm`)
- `x-ui.data-table` (×2: operaciones del cierre + anuladas)
- `x-screen.closing-detail` (`:byType`, `:byWorker`, `:statusBreakdown`, `:participants`)
- `x-ui.badge` (estado del cierre)
- `x-ui.modal` (confirmación de reapertura con motivo)

---

## Contratos de Admin CRUD (M7)

Cada pantalla CRUD sigue este patrón:

**Listado** (`index`):
- `$items` (paginator), `$filters` (array)
- Componentes: `x-ui.data-table`, `x-ui.badge`, `x-ui.pagination`, `x-ui.empty-state`

**Formulario** (`create`/`edit`):
- `$item` (model, solo en edit), `$relatedModels` (para selects)
- Componentes: `x-ui.input`, `x-ui.select`, `x-ui.button`

**Acciones**: `x-ui.modal` para confirmaciones de desactivación/eliminación

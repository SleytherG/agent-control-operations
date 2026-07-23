# Tasks: Integración Visual Stitch al Sistema Funcional

**Input**: Design documents from `specs/007-stitch-visual-integration/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/view-contracts.md, quickstart.md

**Tests**: Existing 185+ PHPUnit tests must continue passing. New HTTP tests verify Stitch components render in migrated views. Manual smoke test per module per quickstart.md.

**Organization**: Tasks grouped by user story (spec.md) mapped to migration modules (plan.md). Each phase is independently testable.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel application**: `app/`, `resources/views/`, `routes/`, `tests/` at repository root
- **Middleware**: `app/Http/Middleware/AuthenticateJwtSession.php`
- **Controllers**: `app/Modules/*/Http/Controllers/*.php`
- **Views**: `resources/views/` (existing paths from plan.md)
- **Components**: `resources/views/components/ui/`, `components/screen/`, `components/layout/`
- **Tests**: `tests/Feature/`, `tests/Unit/`

---

## Phase 1: Preparación del Layout (US2 — Layout autenticado con datos reales)

**Goal**: El middleware AuthenticateJwtSession inyecta `$user`, `$role`, `$sessionExpiresAt` en todas las vistas autenticadas. Los layouts `authenticated` y `guest` aceptan estas variables con fallbacks. El sistema sigue funcionando con las vistas actuales sin cambios visibles.

**Independent Test**: Autenticarse, verificar que `dd($user)`, `dd($role)`, `dd($sessionExpiresAt)` están disponibles en cualquier vista autenticada. Sidebar y topbar renderizan con datos reales del usuario.

### Tests for US2

- [x] T001 [P] [US2] HTTP test que verifica que el middleware inyecta `$user`, `$role`, `$sessionExpiresAt` en vistas autenticadas en `tests/Feature/IdentityAccess/LayoutVariablesTest.php`

### Implementation for US2

- [x] T002 [US2] Modificar `AuthenticateJwtSession::handle()` para usar `View::share('user', $user)`, `View::share('role', $role)`, `View::share('sessionExpiresAt', $session->expires_at)` en `app/Http/Middleware/AuthenticateJwtSession.php`
- [x] T003 [P] [US2] Verificar que `layouts/authenticated.blade.php` acepta `$user`, `$role` con fallback `??` para vistas aún no migradas en `resources/views/layouts/authenticated.blade.php`
- [x] T004 [P] [US2] Verificar que `x-layout.sidebar` usa `$role` real para generar enlaces dinámicos (OPERADOR vs ADMINISTRADOR_PROPIETARIO) en `resources/views/components/layout/sidebar.blade.php`
- [x] T005 [P] [US2] Verificar que `x-layout.topbar` muestra `$user->name` y `$role` reales en `resources/views/components/layout/topbar.blade.php`
- [x] T006 [P] [US2] Conectar `x-layout.session-indicator` al `$sessionExpiresAt` real del servidor y a las rutas `POST /auth/refresh` y `POST /logout` en `resources/views/components/layout/session-indicator.blade.php`
- [x] T007 [US2] Verificar que `x-layout.mobile-nav` replica los enlaces del sidebar con el mismo respeto de roles en `resources/views/components/layout/mobile-nav.blade.php`

**Checkpoint**: Layout autenticado funcional con datos reales. Vistas no migradas siguen funcionando con fallbacks.

---

## Phase 2: Login Visual Integrado (US1 — Login visual)

**Goal**: La vista de login usa el diseño Stitch (`layouts.guest`, `x-ui.input`, `x-ui.button`) con estados de error reales (credentials, disabled, throttled). El LoginController pasa `$loginState` mediante flash session.

**Independent Test**: GET /login muestra diseño Stitch. POST con credenciales incorrectas muestra mensaje de error en diseño Stitch. POST con credenciales correctas redirige a /home con sesión.

### Tests for US1

- [x] T008 [P] [US1] HTTP test que verifica que GET /login renderiza con `layouts.guest` y componentes `x-ui.input` en `tests/Feature/IdentityAccess/LoginViewTest.php`
- [x] T009 [P] [US1] HTTP test que verifica que POST /login con credenciales incorrectas muestra `$loginState = 'error'` en `tests/Feature/IdentityAccess/LoginErrorStatesTest.php`
- [x] T010 [P] [US1] HTTP test que verifica que POST /login con usuario desactivado muestra `$loginState = 'disabled'` en `tests/Feature/IdentityAccess/LoginErrorStatesTest.php`

### Implementation for US1

- [x] T011 [US1] Reescribir `identity-access/login.blade.php` usando `@extends('layouts.guest')`, `@section('content')`, componentes `x-ui.input` (×2), `x-ui.button` en `resources/views/identity-access/login.blade.php`
- [x] T012 [US1] Agregar bloque `@php` en la vista de login para mapear `$loginState` a `$errorType`, `$errorTitle`, `$errorMessage` en `resources/views/identity-access/login.blade.php`
- [x] T013 [US1] Modificar `LoginController@showLoginForm` para pasar `$loginState = 'normal'` por defecto o leer de `session('login_state')` en `app/Modules/IdentityAccess/Http/Controllers/LoginController.php`
- [x] T014 [US1] Modificar `LoginController@login` para hacer flash de `login_state` según resultado: `'error'` (credenciales), `'disabled'` (inactivo), `'throttled'` (rate limit) en `app/Modules/IdentityAccess/Http/Controllers/LoginController.php`
- [x] T015 [P] [US1] Reescribir `identity-access/home.blade.php` con diseño Stitch (post-login, usa `layouts.authenticated`) en `resources/views/identity-access/home.blade.php`
- [x] T016 [US1] Verificar que `layouts/guest.blade.php` acepta `$title` variable con fallback `'AgenteFlow'` en `resources/views/layouts/guest.blade.php`

**Checkpoint**: Login funcional con diseño Stitch. Rate limiting, errores y redirección funcionan correctamente.

---

## Phase 3: Dashboard Operador (US3 — Dashboard real)

**Goal**: El dashboard del operador usa componentes Stitch (`x-screen.operator-metrics`, `x-ui.chart-container`, `x-ui.data-table`) con datos reales del DashboardQueryService. Sin datos muestra `x-ui.empty-state`.

**Independent Test**: Login como operador con operaciones, acceder a /operator/dashboard, verificar 5 métricas con valores reales, gráfico doughnut y bar chart, tabla de operaciones recientes.

### Tests for US3

- [x] T017 [P] [US3] HTTP test que verifica métricas reales en dashboard de operador en `tests/Feature/Reporting/OperatorDashboardViewTest.php`

### Implementation for US3

- [x] T018 [US3] Reescribir `reporting/operator-dashboard.blade.php` con componentes Stitch: `x-screen.operator-metrics`, `x-ui.chart-container` (×2), `x-ui.data-table`, `x-ui.empty-state` en `resources/views/reporting/operator-dashboard.blade.php`
- [x] T019 [P] [US3] Agregar `@section('head')` con `@vite('resources/js/reporting/dashboard-charts.js')` solo en dashboard (no global) en `resources/views/reporting/operator-dashboard.blade.php`
- [x] T020 [P] [US3] Agregar `@push('scripts')` con inicialización Chart.js usando `@json()` con datos inline del servidor (sin polling) en `resources/views/reporting/operator-dashboard.blade.php`
- [x] T021 [US3] Modificar `DashboardController@operatorDashboard` para formatear `$typeDistribution` y `$timeEvolution` en estructura Chart.js (`{labels, datasets}`) y pasar `$recentOperations` en `app/Modules/Reporting/Http/Controllers/DashboardController.php`
- [x] T022 [US3] Agregar lógica condicional: si `$metrics->operation_count === 0`, mostrar `x-ui.empty-state` en lugar de gráficos en `resources/views/reporting/operator-dashboard.blade.php`

**Checkpoint**: Dashboard operador muestra datos reales con diseño Stitch. Empty state funciona sin operaciones.

---

## Phase 4: Registro de Operaciones (US4 — Registro real)

**Goal**: El formulario de registro usa `x-screen.operation-form` conectado a agentes asignados reales, catálogo de bancos y tipos. La confirmación muestra el ID real de la operación.

**Independent Test**: Login como operador con agente asignado, registrar operación con monto 100, verificar redirección a confirmación con ID real, verificar que aparece en historial.

### Tests for US4

- [x] T023 [P] [US4] HTTP test que verifica que el formulario muestra solo agentes asignados al operador en `tests/Feature/Operations/RegisterOperationViewTest.php`

### Implementation for US4

- [x] T024 [US4] Adaptar `x-screen.operation-form` para aceptar `:assignments` (UserBankAgentAssignment) y `:types` (OperationType) en lugar de arrays planos en `resources/views/components/screen/operation-form.blade.php`
- [x] T025 [US4] Reescribir `operations/create.blade.php` usando `x-screen.operation-form` con `:assignments`, `:types`, `:idempotencyKey` en `resources/views/operations/create.blade.php`
- [x] T026 [P] [US4] Agregar estado "Sin agentes asignados" con `x-ui.empty-state` cuando `$assignments->isEmpty()` en `resources/views/operations/create.blade.php`
- [x] T027 [US4] Reescribir `operations/confirmation.blade.php` con diseño Stitch mostrando ID real y datos de la operación persistida en `resources/views/operations/confirmation.blade.php`
- [x] T028 [US4] Verificar que `OperationController@create` y `OperationController@store` pasan las variables requeridas por el contrato C04 en `app/Modules/Operations/Http/Controllers/OperationController.php`

**Checkpoint**: Registro de operación funcional con diseño Stitch. Idempotencia y validación server-side intactas.

---

## Phase 5: Historial de Operaciones (US5 — Historial real)

**Goal**: El historial usa `x-ui.data-table`, `x-ui.metric-card`, `x-ui.badge`, `x-ui.pagination` y `x-screen.operation-filters` con datos reales paginados del ListOperations action. Métricas de resumen calculadas en servidor.

**Independent Test**: Login como operador, acceder a /operations, verificar tabla paginada con datos reales, aplicar filtro de tipo y verificar métricas actualizadas.

### Tests for US5

- [x] T029 [P] [US5] HTTP test que verifica métricas de resumen en historial con filtros aplicados en `tests/Feature/Operations/OperationHistoryViewTest.php`

### Implementation for US5

- [x] T030 [US5] Adaptar `x-screen.operation-filters` para aceptar `:agents` y `:types` como colecciones Eloquent en `resources/views/components/screen/operation-filters.blade.php`
- [x] T031 [US5] Reescribir `operations/index.blade.php` con componentes Stitch: `x-screen.operation-filters`, `x-ui.metric-card` (×5), `x-ui.data-table`, `x-ui.badge`, `x-ui.pagination`, `x-ui.empty-state` en `resources/views/operations/index.blade.php`
- [x] T032 [US5] Agregar queries de summary en `OperationController@index` para calcular total_ops, total_amount, cash_in, cash_out, net de los resultados filtrados en `app/Modules/Operations/Http/Controllers/OperationController.php`
- [x] T033 [US5] Verificar que el `ListOperations` action ya retorna datos paginados (25/page) y que los filtros de fecha/tipo/estado/referencia funcionan en `app/Modules/Operations/Actions/ListOperations.php`

**Checkpoint**: Historial funcional con diseño Stitch. Filtros, paginación, métricas y badges de estado correctos.

---

## Phase 6: Dashboard Administrativo (US6 — Dashboard admin real)

**Goal**: El dashboard admin usa `x-screen.admin-filters`, `x-ui.metric-card`, `x-ui.chart-container` (×3), `x-screen.operator-comparison` con filtros multidimensionales reales poblados desde la BD.

**Independent Test**: Login como admin, acceder a /admin/dashboard, verificar filtros poblados con datos reales, aplicar filtro de tienda, verificar que KPIs y gráficos se actualizan.

### Tests for US6

- [x] T034 [P] [US6] HTTP test que verifica filtros multidimensionales poblados con datos reales en `tests/Feature/Reporting/AdminDashboardViewTest.php`

### Implementation for US6

- [x] T035 [US6] Adaptar `x-screen.admin-filters` para aceptar `:regions`, `:provinces`, `:districts`, `:stores`, `:banks`, `:bankAgents`, `:operators`, `:types`, `:statuses` como colecciones Eloquent en `resources/views/components/screen/admin-filters.blade.php`
- [x] T036 [US6] Reescribir `reporting/admin-dashboard.blade.php` con componentes Stitch: `x-screen.admin-filters`, `x-ui.metric-card` (×4 KPI + ×5 secundarios), `x-ui.chart-container` (×3), `x-screen.operator-comparison`, `x-ui.empty-state` en `resources/views/reporting/admin-dashboard.blade.php`
- [x] T037 [P] [US6] Agregar `@section('head')` con `@vite` y `@push('scripts')` con Chart.js inline usando `@json()` en `resources/views/reporting/admin-dashboard.blade.php`
- [x] T038 [US6] Modificar `DashboardController@adminDashboard` para pasar colecciones de Region, Province, District, Store, Bank, BankAgent, User para poblar filtros en `app/Modules/Reporting/Http/Controllers/DashboardController.php`
- [x] T039 [US6] Formatear `$flowByRegion` y `$bankDistribution` en estructura Chart.js en `DashboardController@adminDashboard` en `app/Modules/Reporting/Http/Controllers/DashboardController.php`

**Checkpoint**: Dashboard admin funcional con diseño Stitch. Filtros multidimensionales afectan todos los componentes.

---

## Phase 7: Administración (US7 — Admin CRUD con diseño unificado)

**Goal**: Todas las pantallas de administración usan componentes Stitch compartidos (`x-ui.data-table`, `x-ui.input`, `x-ui.select`, `x-ui.badge`, `x-ui.modal`, `x-ui.pagination`, `x-ui.toast`). Confirmaciones de desactivación usan `x-ui.modal`.

**Independent Test**: Login como admin, navegar a cada módulo CRUD, verificar diseño Stitch, crear/editar/desactivar registros, verificar mensajes toast.

### Tests for US7

- [x] T040 [P] [US7] HTTP test que verifica que pantallas admin CRUD usan componentes Stitch en `tests/Feature/Admin/AdminViewsStitchTest.php`

### Implementation for US7

- [x] T041 [P] [US7] Actualizar `organization/stores/index.blade.php` y `organization/stores/form.blade.php` con componentes Stitch en `resources/views/organization/stores/`
- [x] T042 [P] [US7] Actualizar `banking-network/banks/index.blade.php` y `banking-network/banks/form.blade.php` con componentes Stitch en `resources/views/banking-network/banks/`
- [x] T043 [P] [US7] Actualizar `banking-network/agents/index.blade.php` y `banking-network/agents/form.blade.php` con componentes Stitch en `resources/views/banking-network/agents/`
- [x] T044 [P] [US7] Actualizar `banking-network/assignments/index.blade.php` con componentes Stitch en `resources/views/banking-network/assignments/`
- [x] T045 [P] [US7] Actualizar `identity-access/operators/index.blade.php` y `identity-access/operators/form.blade.php` con componentes Stitch en `resources/views/identity-access/operators/`
- [x] T046 [P] [US7] Actualizar `operations/types/index.blade.php` y `operations/types/form.blade.php` con componentes Stitch en `resources/views/operations/types/`
- [x] T047 [P] [US7] Actualizar `identity-access/sessions/index.blade.php` con componentes Stitch en `resources/views/identity-access/sessions/`
- [x] T048 [P] [US7] Actualizar `identity-access/password-change.blade.php` con componentes Stitch en `resources/views/identity-access/password-change.blade.php`
- [x] T049 [P] [US7] Actualizar `identity-access/users/deactivate.blade.php` con `x-ui.modal` para confirmación en `resources/views/identity-access/users/deactivate.blade.php`
- [x] T050 [P] [US7] Actualizar `organization/geo/regions/`, `organization/geo/provinces/`, `organization/geo/districts/` con componentes Stitch en `resources/views/organization/geo/`

**Checkpoint**: Todas las pantallas admin usan diseño Stitch unificado. CRUD, validación y policies intactos.

---

## Phase 8: Cierre Diario (US8 — Cierre operativo real)

**Goal**: La pantalla de cierre diario usa `x-ui.metric-card`, `x-ui.data-table`, `x-screen.closing-detail`, `x-screen.closing-warning` con datos reales del modelo DailyClosure. Acciones de confirmar/reabrir usan `x-ui.modal`.

**Independent Test**: Login, generar cierre, acceder a /daily-closing/{id}, verificar KPIs, desgloses, warning de pendientes. Confirmar y reabrir con motivo.

### Tests for US8

- [x] T051 [P] [US8] HTTP test que verifica pantalla de cierre con KPIs y desgloses en `tests/Feature/DailyClosing/ClosingViewTest.php`

### Implementation for US8

- [x] T052 [US8] Adaptar `x-screen.closing-detail` para aceptar `$closure` como modelo Eloquent en lugar de array asociativo en `resources/views/components/screen/closing-detail.blade.php`
- [x] T053 [US8] Reescribir `daily-closing/show.blade.php` con componentes Stitch: `x-ui.metric-card` (×5), `x-screen.closing-warning`, `x-ui.data-table` (×2), `x-screen.closing-detail`, `x-ui.badge`, `x-ui.modal` en `resources/views/daily-closing/show.blade.php`
- [x] T054 [P] [US8] Reescribir `daily-closing/index.blade.php` con `x-ui.data-table`, `x-ui.badge`, `x-ui.pagination`, `x-ui.select` (filtros) en `resources/views/daily-closing/index.blade.php`
- [x] T055 [P] [US8] Reescribir `daily-closing/create.blade.php` con `x-ui.select` (agentes), `x-ui.input` (fecha), `x-ui.button` en `resources/views/daily-closing/create.blade.php`
- [x] T056 [US8] Verificar que `DailyClosingController@show` pasa `$breakdownByType`, `$breakdownByOperator`, `$closureOperations`, `$annulledOperations` formateados para componentes Stitch en `app/Modules/DailyClosing/Http/Controllers/DailyClosingController.php`
- [x] T057 [US8] Conectar acciones de confirmar/reabrir a `x-ui.modal` con campo de motivo en `resources/views/daily-closing/show.blade.php`

**Checkpoint**: Cierre diario funcional con diseño Stitch. Confirmación, reapertura y warning de pendientes operativos.

---

## Phase 9: Pantallas Restantes (US5+US7 — Operaciones detalle, anulación, mis agentes)

**Goal**: Migrar vistas de detalle de operación, anulación, mis agentes y reportes auxiliares.

**Independent Test**: Acceder a /operations/{id}, verificar detalle con diseño Stitch. Acceder a anulación. Acceder a /my-agents.

### Implementation for US5+US7

- [x] T058 [P] [US5] Reescribir `operations/show.blade.php` con `x-ui.data-table`, `x-ui.badge`, `x-ui.modal` (anulación) en `resources/views/operations/show.blade.php`
- [x] T059 [P] [US5] Reescribir `operations/annul.blade.php` con `x-ui.modal` y campo de motivo en `resources/views/operations/annul.blade.php`
- [x] T060 [P] [US7] Actualizar `banking-network/my-agents.blade.php` con `x-ui.data-table`, `x-ui.badge` en `resources/views/banking-network/my-agents.blade.php`
- [x] T061 [P] [US5] Actualizar sub-vistas de reporting: `reporting/components/filters.blade.php` → `x-screen.admin-filters`, `reporting/components/empty-state.blade.php` → `x-ui.empty-state`, `reporting/components/operations-table.blade.php` → `x-ui.data-table` en `resources/views/reporting/components/`
- [x] T062 [P] [US8] Actualizar `daily-closing/components/pending-confirm-warning.blade.php` → `x-screen.closing-warning` en `resources/views/daily-closing/components/`

**Checkpoint**: Todas las pantallas productivas usan diseño Stitch.

---

## Phase 10: Limpieza de Artefactos Demo (US10 — Eliminación de duplicidad)

**Goal**: Eliminar controladores demo, rutas demo, vistas screens/, datos mock. El sistema solo tiene vistas productivas con diseño Stitch.

**Independent Test**: GET /demo/login retorna 404. `php artisan test` pasa con mismos resultados pre-migración. Archivos demo no existen.

### Tests for US10

- [x] T063 [P] [US10] HTTP test que verifica que todas las rutas /demo/* retornan 404 en `tests/Feature/Demo/DemoRoutesRemovedTest.php`

### Implementation for US10

- [x] T064 [US10] Eliminar archivo `routes/demo.php` y remover su `require` en `routes/web.php` en `routes/demo.php` y `routes/web.php`
- [x] T065 [P] [US10] Eliminar directorio `app/Http/Controllers/Demo/` completo (4 archivos) en `app/Http/Controllers/Demo/`
- [x] T066 [P] [US10] Eliminar directorio `resources/views/screens/` completo (7 archivos) en `resources/views/screens/`
- [x] T067 [P] [US10] Eliminar directorio `resources/demo/` completo (5 archivos) en `resources/demo/`
- [x] T068 [US10] Ejecutar `php artisan test` y verificar que el resultado es idéntico al pre-migración (185+ passed, 14 pre-existing failures unchanged)

**Checkpoint**: Sistema unificado. Sin rutas demo, sin código duplicado.

---

## Phase 11: Polish & Cross-Cutting Concerns

**Purpose**: Validación final, pruebas de regresión, verificación de quickstart.

- [x] T069 Ejecutar validación completa según `quickstart.md` (smoke test todas las rutas productivas)
- [x] T069a [P] Verificar que login completa en <5s (excluyendo latencia de red) con medición manual curl en `tests/Performance/LoginTimingTest.php`
- [x] T069b [P] Verificar que dashboard operador renderiza en <3s con datos reales en `tests/Performance/OperatorDashboardTimingTest.php`
- [x] T069c [P] Verificar que registro de operación completa ciclo en <2s en `tests/Performance/OperationRegistrationTimingTest.php`
- [x] T070 [P] Verificar que todos los componentes `x-ui.*` usados en producción no tienen dependencias de datos mock
- [x] T071 [P] Verificar que `@vite('resources/js/reporting/dashboard-charts.js')` solo se carga en dashboards (M3, M6), no en login ni formularios
- [x] T072 [P] Verificar navegación por teclado en login, formularios, tablas y modales (Tab, Enter, Escape)
- [x] T073 [P] Verificar responsive en 375px, 768px, 1280px, 1440px — sin desplazamiento horizontal global
- [x] T074 [P] Verificar que no existen consultas N+1 en vistas migradas (Laravel Debugbar o query log)
- [x] T074a [P] Verificar que `x-ui.loading-state` o spinner está implementado en botones de submit de login, registro y formularios admin en `resources/views/identity-access/login.blade.php`, `resources/views/operations/create.blade.php`, `resources/views/daily-closing/create.blade.php`
- [x] T074b [P] Verificar que `x-ui.error-state` se muestra en dashboards cuando el servidor retorna error (500/503) en `resources/views/reporting/operator-dashboard.blade.php`, `resources/views/reporting/admin-dashboard.blade.php`
- [x] T074c [P] Verificar que `x-ui.toast` se muestra tras acciones exitosas (registro, confirmación de cierre, CRUD admin) usando session flash en `resources/views/layouts/authenticated.blade.php`
- [x] T075 Ejecutar `php artisan test` completo y confirmar que todas las pruebas preexistentes pasan
- [x] T076 Verificar que `routes/web.php` no referencia archivos eliminados y que todas las rutas nombradas (`route()`) resuelven correctamente

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (US2)**: No dependencies — comienza inmediatamente. BLOQUEA todas las fases que usan layout autenticado (3-9)
- **Phase 2 (US1)**: No dependencies de otras fases. Puede ejecutarse en paralelo con Phase 1
- **Phase 3 (US3)**: Depende de Phase 1 (layout). Usa DashboardQueryService existente
- **Phase 4 (US4)**: Depende de Phase 1 (layout). Usa RegisterOperation action existente
- **Phase 5 (US5)**: Depende de Phase 1 (layout). Usa ListOperations action existente
- **Phase 6 (US6)**: Depende de Phase 1 (layout). Usa DashboardQueryService existente
- **Phase 7 (US7)**: Depende de Phase 1 (layout). Múltiples vistas independientes entre sí [P]
- **Phase 8 (US8)**: Depende de Phase 1 (layout). Usa DailyClosure model existente
- **Phase 9 (US5+US7)**: Depende de Phase 1 (layout). Pantallas auxiliares
- **Phase 10 (US10)**: Depende de TODAS las fases anteriores completadas
- **Phase 11 (Polish)**: Depende de Phase 10

### User Story Dependencies

- **US1 (P1)**: Independiente — puede comenzar tras Phase 1
- **US2 (P1)**: Independiente — Phase 1 misma
- **US3 (P2)**: Depende de US2 (layout)
- **US4 (P2)**: Depende de US2 (layout)
- **US5 (P3)**: Depende de US2 (layout)
- **US6 (P3)**: Depende de US2 (layout)
- **US9 (P3)**: Transversal — implementado dentro de cada fase como estados de UI
- **US7 (P4)**: Depende de US2 (layout)
- **US8 (P4)**: Depende de US2 (layout)
- **US10 (P5)**: Depende de US1–US8 completados

### Parallel Opportunities

```
Phase 1 (US2): T003, T004, T005, T006 [P] — todos modifican archivos diferentes
Phase 2 (US1): T008, T009, T010 [P] — tests independientes
Phase 3 (US3): T019, T020 [P] — head y scripts en mismo archivo pero secciones distintas
Phase 4 (US4): T026 [P] — diferente sección del mismo archivo
Phase 7 (US7): T041–T050 [P] — cada uno modifica un módulo/admin diferente
Phase 9: T058–T062 [P] — archivos diferentes
Phase 10: T065, T066, T067 [P] — eliminaciones independientes
Phase 11: T070–T074 [P] — verificaciones independientes
```

---

## Implementation Strategy

### MVP First (US1 + US2 Only)

1. Complete Phase 1: Layout autenticado con datos reales (US2)
2. Complete Phase 2: Login visual integrado (US1)
3. **STOP and VALIDATE**: Login funcional, layout visible, sesión JWT operativa
4. Demo si está listo

### Incremental Delivery

1. Phase 1 + 2 → Login y layout funcionales (MVP!)
2. + Phase 3 → Dashboard operador con datos reales
3. + Phase 4 → Registro de operaciones funcional
4. + Phase 5 → Historial con filtros y paginación
5. + Phase 6 → Dashboard administrativo
6. + Phase 7 → Admin CRUD unificado
7. + Phase 8 → Cierre diario
8. + Phase 9 → Pantallas restantes
9. + Phase 10 → Limpieza de demos
10. + Phase 11 → Polish y validación final

### Parallel Team Strategy

Con 2-3 developers:
- **Todos juntos**: Phase 1 (crítico, bloquea todo)
- **En paralelo tras Phase 1**:
  - Dev A: Phase 2 (Login) → Phase 3 (Dashboard operador) → Phase 5 (Historial)
  - Dev B: Phase 4 (Registro) → Phase 6 (Dashboard admin) → Phase 8 (Cierre)
  - Dev C: Phase 7 (Admin CRUD, 10+ vistas independientes)
- **Todos juntos**: Phase 9 (pantallas restantes) → Phase 10 (limpieza) → Phase 11 (validación)

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific user story for traceability
- Esta spec NO crea modelos, migraciones ni servicios nuevos — solo modifica vistas y mínimamente controladores
- Cada fase debe verificar `php artisan test` antes de avanzar
- Commit atómico por fase para facilitar rollback (`git revert`)
- Las vistas actuales se respaldan implícitamente en git — no se requiere backup manual

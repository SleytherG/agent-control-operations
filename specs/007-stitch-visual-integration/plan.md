# Implementation Plan: Integración Visual Stitch al Sistema Funcional

**Branch**: `007-stitch-visual-integration` | **Date**: 2026-07-22 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/007-stitch-visual-integration/spec.md`

## Summary

Migrar 10 pantallas de producción (HTML básico) al sistema visual Stitch (componentes Blade, CSS tokens, layouts de spec 006) sin modificar reglas de negocio, autorizaciones ni persistencia. La estrategia es incremental: cada módulo migrado debe ser completamente funcional antes de pasar al siguiente. Al finalizar, se eliminan todos los artefactos demo (controladores, rutas, vistas, datos mock).

**Technical approach**: Reemplazar las vistas Blade existentes por versiones que usen los componentes Stitch (`x-ui.*`, `x-screen.*`, `x-layout.*`) y los layouts `authenticated`/`guest`. Los controladores existentes se modifican mínimamente para pasar variables adicionales requeridas por los componentes Stitch (ej. `$role`, `$title`, `$user`). No se crean nuevos controladores, modelos ni migraciones.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13.21.1

**Primary Dependencies**: Laravel Framework, lcobucci/jwt 5.x, Chart.js (dashboards), Vite (build only)

**Storage**: MySQL/MariaDB, 18 tablas existentes, sin nuevas migraciones

**Time & Money**: DECIMAL(18,2) para montos, America/Lima para fechas, S/ como símbolo monetario, períodos con boundaries explícitos (día/semana/mes/trimestre/semestre/año)

**Authentication & Session**: JWT HS256 5min access token, cookie `__Host-access_token` (HttpOnly/Secure/SameSite=Strict), refresh token rotatorio con hash HMAC-SHA-256, renovación explícita, revocación en logout, 8h absolute TTL, modal de advertencia 30s antes

**Testing**: PHPUnit 12.x, 185+ tests existentes (Feature/Unit/Integration/Dusk), `php artisan test`

**Target Platform**: Hosting PHP convencional (Apache/Nginx + PHP-FPM), sin Redis/Node.js en producción

**Project Type**: Web application (Laravel + Blade), server-rendered, no SPA

**Performance Goals**: Login <5s, dashboard <3s server render, registro <2s, paginación 25/page, Chart.js solo en dashboards

**Constraints**: Sin desplazamiento horizontal global, 4 breakpoints (375/768/1280/1440px), WCAG 2.2 AA, sin consultas N+1, sin colecciones completas al navegador

**Scale/Scope**: ~10 pantallas principales a migrar, ~22 componentes Stitch existentes, 0 nuevas tablas, 0 nuevas migraciones

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Specification completeness**: ✅ Spec 007 aprobada con 10 user stories, 16 FRs, 10 BRs, acceptance scenarios, edge cases, explicit out-of-scope
- **Increment classification**: ✅ New functional capability — integración visual sobre funcionalidad existente
- **Security and privacy**: ✅ Server-side enforcement unchanged; existing Policies/Gates preserved; no customer/banking data exposed
- **Session safety**: ✅ JWT 5min, explicit renewal, rotating refresh tokens, revocation — unchanged from spec 001
- **Operation integrity**: ✅ Non-destructive correction (anulación lógica), audit records — unchanged
- **Deployment compatibility**: ✅ Conventional PHP hosting, no new infrastructure dependencies, Vite compile pre-deploy
- **Minimal interface**: ✅ Blade server rendering, semantic HTML, custom CSS, modular JS, no SPA
- **Money and time**: ✅ DECIMAL(18,2), distinct aggregates, America/Lima, no gross-as-profit — unchanged
- **Performance**: ✅ Pagination 25/page, server-side aggregation (DashboardQueryService), Chart.js deferred loading
- **Observability and recovery**: ✅ Health route preserved, secret-safe logs, no DB migrations needed
- **Testing**: ✅ Existing 185+ tests preserved; acceptance scenarios verificables manualmente; no new test framework needed
- **System boundary**: ✅ No bank confirmation, accounting, or official banking claims

**Gate result**: PASS — no exceptions required.

## Project Structure

### Documentation (this feature)

```text
specs/007-stitch-visual-integration/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: research findings
├── data-model.md        # Phase 1: entity mapping (no new tables)
├── quickstart.md        # Phase 1: validation guide
├── contracts/           # Phase 1: UI contracts (view-to-controller)
│   └── view-contracts.md
├── tasks.md             # Phase 2 output (/speckit.tasks)
└── checklists/
    └── requirements.md  # Spec quality checklist
```

### Source Code (repository root) — affected paths

```text
app/Http/Controllers/Demo/                    # [DELETE] 4 demo controllers
app/Http/Middleware/AuthenticateJwtSession.php # [MODIFY] inject layout variables
app/Modules/
├── IdentityAccess/Http/Controllers/
│   └── LoginController.php                   # [MODIFY] pass $loginState to view
├── Operations/Http/Controllers/
│   └── OperationController.php               # [MODIFY] pass $role, $title
├── Reporting/Http/Controllers/
│   └── DashboardController.php               # [MODIFY] pass $role, $user
└── DailyClosing/Http/Controllers/
    └── DailyClosingController.php            # [MODIFY] pass $role, $title

resources/views/
├── screens/                                  # [DELETE] demo views
├── identity-access/login.blade.php           # [REPLACE] with Stitch login
├── identity-access/home.blade.php            # [REPLACE] with Stitch home
├── operations/index.blade.php                # [REPLACE] with Stitch history
├── operations/create.blade.php               # [REPLACE] with Stitch register
├── operations/show.blade.php                 # [REPLACE] with Stitch detail
├── reporting/operator-dashboard.blade.php    # [REPLACE] with Stitch dashboard
├── reporting/admin-dashboard.blade.php       # [REPLACE] with Stitch dashboard
├── daily-closing/show.blade.php              # [REPLACE] with Stitch closing
├── daily-closing/index.blade.php             # [REPLACE] with Stitch list
├── daily-closing/create.blade.php            # [REPLACE] with Stitch form
├── organization/stores/                      # [UPDATE] use Stitch components
├── banking-network/banks/                    # [UPDATE] use Stitch components
├── banking-network/agents/                   # [UPDATE] use Stitch components
├── identity-access/operators/                # [UPDATE] use Stitch components
├── identity-access/sessions/                 # [UPDATE] use Stitch components
└── layouts/
    ├── authenticated.blade.php               # [MODIFY] accept $role, $user from middleware
    └── guest.blade.php                       # [MODIFY] accept optional $title

resources/demo/                               # [DELETE] 5 mock data files

routes/demo.php                               # [DELETE] and remove require from web.php
routes/web.php                                # [MODIFY] remove demo require

tests/
├── Feature/                                  # Existing tests — must all pass
├── Unit/                                     # Existing tests — must all pass
└── Integration/                              # Existing tests — must all pass
```

**Structure Decision**: No new directories. All changes are replacements/updates within existing files. No new models, migrations, controllers, or services. The existing `App\Modules\` structure, service layer, and action classes remain unchanged.

## Exception Tracking

> No constitutional violations. This section is empty.

---

## Migration Matrix: Demo → Producción

| # | Pantalla Demo | Ruta Demo | Pantalla Real | Ruta Real | Componentes Stitch Reutilizados | Fuente Datos Real | Acciones Reales |
|---|--------------|-----------|---------------|-----------|-------------------------------|-------------------|-----------------|
| 1 | `screens/auth/login.blade.php` | `GET /demo/login` | `identity-access/login.blade.php` | `GET /login`, `POST /login` | `x-ui.input`, `x-ui.button`, `x-ui.toast` | LoginRequest, AuthenticateUser action, RateLimiter | Login, rate limiting, error display |
| 2 | `screens/auth/expiry-modal.blade.php` | `GET /demo/expiry` | `components/layout/session-indicator.blade.php` | (embebido en layout autenticado) | `x-ui.modal`, `x-screen.expiry-modal-content` | `$sessionExpiresAt` del middleware, `POST /auth/refresh`, `POST /logout` | Renovar sesión, cerrar sesión |
| 3 | `screens/operator/dashboard.blade.php` | `GET /demo/operator/dashboard` | `reporting/operator-dashboard.blade.php` | `GET /operator/dashboard` | `x-screen.operator-metrics`, `x-ui.chart-container`, `x-ui.data-table`, `x-ui.badge` | DashboardQueryService (datos reales del usuario autenticado) | Consultar métricas, gráficos, cambiar período |
| 4 | `screens/operator/register.blade.php` | `GET /demo/operator/register` | `operations/create.blade.php` | `GET /operations/create`, `POST /operations` | `x-screen.operation-form`, `x-ui.input`, `x-ui.select`, `x-ui.button` | UserBankAgentAssignment, Bank, OperationType (Eloquent), RegisterOperation action | Registrar operación con idempotencia, confirmación |
| 5 | `screens/operator/history.blade.php` | `GET /demo/operator/history` | `operations/index.blade.php` | `GET /operations` | `x-screen.operation-filters`, `x-ui.metric-card`, `x-ui.data-table`, `x-ui.badge`, `x-ui.pagination` | ListOperations action, Operation model (paginado), summary queries | Filtrar, paginar, ver detalle |
| 6 | `screens/admin/dashboard.blade.php` | `GET /demo/admin/dashboard` | `reporting/admin-dashboard.blade.php` | `GET /admin/dashboard` | `x-screen.admin-filters`, `x-ui.metric-card`, `x-ui.chart-container`, `x-screen.operator-comparison` | DashboardQueryService, Region/Province/District/Store/Bank/BankAgent/User models | Filtrar multidimensionalmente, gráficos, rankings |
| 7 | `screens/daily-closing/show.blade.php` | `GET /demo/daily-closing/{id}` | `daily-closing/show.blade.php` | `GET /daily-closing/{id}` | `x-ui.metric-card`, `x-ui.data-table`, `x-screen.closing-detail`, `x-screen.closing-warning` | DailyClosure model + relaciones, breakdown queries | Ver KPIs, confirmar, reabrir |
| 8 | — | — | `daily-closing/index.blade.php` | `GET /daily-closing` | `x-ui.data-table`, `x-ui.badge`, `x-ui.pagination`, `x-ui.select` | DailyClosure model (paginado), BankAgent model | Listar, filtrar, ver detalle |
| 9 | — | — | `daily-closing/create.blade.php` | `GET /daily-closing/create`, `POST /daily-closing` | `x-ui.input`, `x-ui.select`, `x-ui.button` | BankAgent model, GenerateClosing action | Generar cierre diario |
| 10 | — | — | `operations/show.blade.php` | `GET /operations/{id}` | `x-ui.data-table`, `x-ui.badge`, `x-ui.button`, `x-ui.modal` | Operation model + relaciones, AnnulOperation action | Ver detalle, anular |
| 11 | — | — | `identity-access/home.blade.php` | `GET /home` | `x-ui.metric-card` (opcional, estado de sesión) | `$expiresAt` del middleware | Página de bienvenida post-login |

**Nota**: Las pantallas #8-11 no tienen equivalente demo; se diseñan desde cero usando componentes Stitch siguiendo el patrón de las pantallas #3-7.

---

## Fase 0: Migración Modular Incremental

### Estrategia de disponibilidad continua

Cada módulo se migra en este orden. Durante la migración de un módulo, los demás continúan funcionando con sus vistas actuales. La aplicación **nunca queda inaccesible**. Al completar cada módulo, se verifica:
1. `php artisan test` — todas las pruebas pasan
2. Smoke test manual — el módulo migrado funciona con datos reales
3. Los módulos aún no migrados no se rompen

**Rollback por módulo**: Si un módulo falla la verificación, se revierte a la vista anterior (backup en git). El resto de módulos no se afectan.

### Módulo 0: Preparación (sin cambios visibles)

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Preparar el middleware y layout para inyectar variables requeridas por componentes Stitch sin afectar vistas actuales |
| **Rutas afectadas** | Todas las autenticadas (middleware global) |
| **Controladores** | Ninguno modificado aún |
| **Servicios** | AuthenticateJwtSession middleware |
| **Vista real** | `layouts/authenticated.blade.php` |
| **Vista demo** | `screens/` (referencia) |
| **Componentes a reutilizar** | `x-layout.sidebar`, `x-layout.topbar`, `x-layout.mobile-nav`, `x-layout.session-indicator` |
| **Componentes a crear** | Ninguno |
| **Mock data a retirar** | Ninguno aún |
| **Fuente datos real** | `$request->attributes` del middleware (user, role, session_expires_at) |
| **Pruebas a conservar** | Todas las existentes |
| **Nuevas pruebas** | Test que verifica que middleware inyecta `$role`, `$user`, `$sessionExpiresAt` en todas las vistas autenticadas |
| **Riesgos** | Mínimo — solo se agregan variables que las vistas pueden ignorar |
| **Rollback** | Revertir cambios en AuthenticateJwtSession.php |

**Cambios específicos**:
1. `AuthenticateJwtSession::handle()` — agregar `View::share()` o `$request->attributes->set()` para `role`, `user`, `session_expires_at`
2. `layouts/authenticated.blade.php` — verificar que sidebar/topbar/session-indicator aceptan estas variables con fallback `??` para compatibilidad con vistas aún no migradas
3. `layouts/guest.blade.php` — verificar que `$title ?? 'AgenteFlow'` funciona

### Módulo 1: Login y autenticación visual

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar la vista de login por el diseño Stitch con estados de error reales |
| **Rutas afectadas** | `GET /login`, `POST /login` |
| **Controladores** | `LoginController@showLoginForm`, `LoginController@login` |
| **Servicios** | `AuthenticateUser` action, `StartAuthSession` action, `RateLimiter` |
| **Vista real** | `identity-access/login.blade.php` → REEMPLAZAR |
| **Vista demo** | `screens/auth/login.blade.php` (referencia de diseño) |
| **Componentes a reutilizar** | `x-ui.input`, `x-ui.button`, `x-ui.toast` (para errores flash), `layouts.guest` |
| **Componentes a crear** | Ninguno — usar los existentes |
| **Mock data a retirar** | Ninguno aún (demo login usa mock data pero no se elimina hasta módulo 10) |
| **Fuente datos real** | `$errors` (Laravel error bag), `session('error')`, `old()` para preservar campos |
| **Pruebas a conservar** | Tests de LoginController, AuthenticateUser, StartAuthSession, RateLimiter |
| **Nuevas pruebas** | Test HTTP que verifica que `GET /login` renderiza con `layouts.guest` y componentes Stitch |
| **Riesgos** | Rate limiting info debe mostrarse visualmente sin exponer contadores internos; `$loginState` mapping debe ser correcto |
| **Rollback** | Restaurar `identity-access/login.blade.php` desde git |

**Cambios específicos**:
1. Reescribir `identity-access/login.blade.php` usando `@extends('layouts.guest')`, `@section('content')`, y componentes `x-ui.input`, `x-ui.button`
2. Modificar `LoginController@showLoginForm` para pasar `$loginState = 'normal'` por defecto, o leer de `session('login_state')` si existe
3. Modificar `LoginController@login` para:
   - En fallo de autenticación: `redirect()->back()->with('login_state', 'error')->withInput()`
   - En usuario desactivado: `redirect()->back()->with('login_state', 'disabled')`
   - En throttled: `redirect()->back()->with('login_state', 'throttled')`
4. La vista usa `@php` block para mapear `$loginState` a errorType/title/message como en la demo

### Módulo 2: Home post-login

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar la vista home por un dashboard de bienvenida con estado de sesión |
| **Rutas afectadas** | `GET /home` |
| **Controladores** | `LoginController@home` |
| **Vista real** | `identity-access/home.blade.php` → REEMPLAZAR |
| **Vista demo** | No tiene equivalente directo |
| **Componentes a reutilizar** | `x-ui.metric-card` (estado de sesión), `layouts.authenticated` |
| **Fuente datos real** | `$expiresAt` ya pasado por el controlador |
| **Pruebas** | Tests existentes de LoginController@home |
| **Riesgos** | Mínimo — vista simple |

### Módulo 3: Dashboard del operador

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar `reporting/operator-dashboard.blade.php` con diseño Stitch mostrando datos reales |
| **Rutas afectadas** | `GET /operator/dashboard` |
| **Controladores** | `DashboardController@operatorDashboard` |
| **Servicios** | `DashboardQueryService` (ya existente) |
| **Vista real** | `reporting/operator-dashboard.blade.php` → REEMPLAZAR |
| **Vista demo** | `screens/operator/dashboard.blade.php` (referencia de diseño) |
| **Componentes a reutilizar** | `x-screen.operator-metrics`, `x-ui.chart-container`, `x-ui.data-table`, `x-ui.badge` |
| **Componentes a crear** | Ninguno |
| **Mock data a retirar** | Ninguno aún |
| **Fuente datos real** | `DashboardQueryService` → `$metrics`, `$typeDistribution`, `$timeEvolution`, `$period`, Operation model → `$recentOperations` |
| **Pruebas a conservar** | Tests de DashboardController@operatorDashboard, DashboardQueryService |
| **Nuevas pruebas** | Ninguna requerida (lógica de negocio sin cambios) |
| **Riesgos** | El DashboardQueryService devuelve objetos; la vista demo espera arrays. Se necesita un adapter o modificaciones al controller para formatear datos para los componentes Stitch. Chart.js debe cargarse solo aquí. |
| **Rollback** | Restaurar `reporting/operator-dashboard.blade.php` desde git |

**Cambios específicos**:
1. Reescribir la vista usando `x-screen.operator-metrics`, `x-ui.chart-container`, `x-ui.data-table`
2. Modificar `DashboardController@operatorDashboard` para:
   - Pasar `$user` (del middleware) a la vista
   - Formatear `$typeDistribution` y `$timeEvolution` en el formato que espera Chart.js (labels + datasets)
   - Pasar `$recentOperations` como colección formateada para `x-ui.data-table`
   - Pasar `$role = 'operator'`
3. Usar `@section('head')` para `@vite('resources/js/reporting/dashboard-charts.js')`
4. Usar `@push('scripts')` para inicializar Chart.js con datos reales del servidor (NO polling — usar datos inline)

### Módulo 4: Registro de operaciones

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar formulario de registro con componente Stitch `x-screen.operation-form` |
| **Rutas afectadas** | `GET /operations/create`, `POST /operations` |
| **Controladores** | `OperationController@create`, `OperationController@store` |
| **Servicios** | `RegisterOperation` action |
| **Vista real** | `operations/create.blade.php` → REEMPLAZAR |
| **Vista demo** | `screens/operator/register.blade.php` (referencia de diseño, actualmente rota) |
| **Componentes a reutilizar** | `x-screen.operation-form` (acepta `:banks` y `:types`) |
| **Componentes a crear** | Modificar `x-screen.operation-form` para aceptar `$assignments` (agentes del operador) en lugar de `$banks` plano, y añadir campos de monto, moneda, fecha, referencia, observación |
| **Mock data a retirar** | Ninguno aún |
| **Fuente datos real** | `UserBankAgentAssignment` (agentes del operador), `Bank` model, `OperationType` model, `RegisterOperationRequest` (validación) |
| **Pruebas a conservar** | Tests de OperationController@create/@store, RegisterOperation action, RegisterOperationRequest |
| **Nuevas pruebas** | Test HTTP que verifica renderizado de `x-screen.operation-form` con datos reales de agentes asignados |
| **Riesgos** | El componente `x-screen.operation-form` debe adaptarse para mostrar agentes asignados (relación UserBankAgentAssignment) en lugar de bancos planos. Idempotency key debe pasarse como hidden field. |
| **Rollback** | Restaurar `operations/create.blade.php` desde git |

### Módulo 5: Historial de operaciones

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar tabla de historial con componentes Stitch (data-table, filtros, métricas, paginación) |
| **Rutas afectadas** | `GET /operations` |
| **Controladores** | `OperationController@index` |
| **Servicios** | `ListOperations` action |
| **Vista real** | `operations/index.blade.php` → REEMPLAZAR |
| **Vista demo** | `screens/operator/history.blade.php` (referencia de diseño) |
| **Componentes a reutilizar** | `x-screen.operation-filters`, `x-ui.metric-card` (x5), `x-ui.data-table`, `x-ui.badge`, `x-ui.pagination` |
| **Fuente datos real** | `ListOperations` → `$operations` (paginated), `$agents`, `$types`; summary queries para métricas |
| **Pruebas** | Tests existentes de OperationController@index, ListOperations |
| **Riesgos** | Las métricas de resumen deben calcularse en el servidor (no en JS). El componente `x-screen.operation-filters` debe aceptar opciones dinámicas desde Eloquent. |

### Módulo 6: Dashboard administrativo

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar dashboard admin con diseño Stitch y filtros multidimensionales reales |
| **Rutas afectadas** | `GET /admin/dashboard` |
| **Controladores** | `DashboardController@adminDashboard` |
| **Servicios** | `DashboardQueryService`, modelos de Organization/BankingNetwork |
| **Vista real** | `reporting/admin-dashboard.blade.php` → REEMPLAZAR |
| **Vista demo** | `screens/admin/dashboard.blade.php` (referencia de diseño) |
| **Componentes a reutilizar** | `x-screen.admin-filters`, `x-ui.metric-card` (x4+), `x-ui.chart-container` (x3), `x-screen.operator-comparison` |
| **Fuente datos real** | DashboardQueryService → `$metrics`, `$typeDistribution`, `$timeEvolution`, `$flowByRegion`, `$bankDistribution`; modelos → `$topStores`, `$topWorkers`, `$regions`, `$stores`, `$banks` |
| **Riesgos** | Los filtros multidimensionales requieren que `x-screen.admin-filters` se popule dinámicamente con datos de la base de datos. El componente demo acepta arrays planos; debe adaptarse para aceptar colecciones Eloquent o arrays key-value. |

### Módulo 7: Administración (Stores, Banks, Agents, Users, Types, Sessions)

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Actualizar todas las pantallas CRUD de administración para usar componentes Stitch |
| **Rutas afectadas** | `GET/POST /admin/stores/*`, `/admin/banks/*`, `/admin/bank-agents/*`, `/admin/operators/*`, `/admin/operation-types/*`, `/admin/sessions/*` |
| **Controladores** | StoreController, BankController, BankAgentController, OperatorController, OperationTypeController, SessionHistoryController, DeactivateUserController |
| **Vistas reales** | `organization/stores/`, `banking-network/banks/`, `banking-network/agents/`, `identity-access/operators/`, `operations/types/`, `identity-access/sessions/` |
| **Vista demo** | No tienen equivalente demo directo |
| **Componentes a reutilizar** | `x-ui.data-table`, `x-ui.input`, `x-ui.select`, `x-ui.button`, `x-ui.badge`, `x-ui.modal`, `x-ui.pagination`, `x-ui.toast` |
| **Riesgos** | Pantallas numerosas (12+ vistas). Todas deben mantener consistencia visual con el resto del sistema. Las confirmaciones de eliminación/desactivación deben usar `x-ui.modal`. |

### Módulo 8: Cierre diario

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Reemplazar pantalla de cierre diario con diseño Stitch y KPIs reales |
| **Rutas afectadas** | `GET /daily-closing`, `GET /daily-closing/create`, `POST /daily-closing`, `GET /daily-closing/{id}`, `POST /daily-closing/{id}/confirm`, `POST /daily-closing/{id}/reopen` |
| **Controladores** | `DailyClosingController` |
| **Vista real** | `daily-closing/show.blade.php`, `daily-closing/index.blade.php`, `daily-closing/create.blade.php` → REEMPLAZAR |
| **Vista demo** | `screens/daily-closing/show.blade.php` (referencia de diseño, actualmente rota) |
| **Componentes a reutilizar** | `x-ui.metric-card`, `x-ui.data-table`, `x-ui.badge`, `x-screen.closing-detail`, `x-screen.closing-warning` |
| **Riesgos** | La vista demo tiene bug estructural ( `@endsection` mal colocado). Los datos reales requieren formateo de `$closure->breakdownByType` y `$closure->breakdownByOperator` para los componentes. |

### Módulo 9: Pantallas restantes (operations/show, password-change, assignments, my-agents)

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Migrar vistas de detalle y pantallas auxiliares |
| **Vistas** | `operations/show.blade.php`, `operations/annul.blade.php`, `identity-access/password-change.blade.php`, `identity-access/users/deactivate.blade.php`, `banking-network/my-agents.blade.php`, `banking-network/assignments/` |
| **Componentes** | `x-ui.data-table`, `x-ui.badge`, `x-ui.modal`, `x-ui.button`, `x-ui.input` |
| **Riesgos** | Bajo — vistas individuales con patrones ya establecidos en módulos anteriores |

### Módulo 10: Limpieza de artefactos demo

| Aspecto | Detalle |
|---------|---------|
| **Objetivo** | Eliminar todos los controladores, rutas, vistas y datos demo |
| **Rutas afectadas** | `routes/demo.php` y su `require` en `routes/web.php` |
| **Archivos a eliminar** | `app/Http/Controllers/Demo/` (4 archivos), `routes/demo.php`, `resources/views/screens/` (7 archivos), `resources/demo/` (5 archivos) |
| **Archivos a modificar** | `routes/web.php` — remover `require __DIR__.'/demo.php'` |
| **Pruebas a conservar** | Todas las existentes |
| **Nuevas pruebas** | Test que verifica que `GET /demo/login` retorna 404 |
| **Riesgos** | Verificar que ninguna prueba depende de rutas demo. Si hay dependencias, actualizar las pruebas antes de eliminar. |
| **Rollback** | Restaurar archivos desde git |

---

## Matriz de Componentes Stitch

| Componente | Ubicación | Estado | Uso en producción |
|-----------|-----------|--------|-------------------|
| `x-layout.sidebar` | `components/layout/` | ✅ Listo | Layout autenticado (M0) |
| `x-layout.topbar` | `components/layout/` | ✅ Listo | Layout autenticado (M0) |
| `x-layout.mobile-nav` | `components/layout/` | ✅ Listo | Layout autenticado (M0) |
| `x-layout.session-indicator` | `components/layout/` | ✅ Listo | Layout autenticado (M0) |
| `x-ui.input` | `components/ui/` | ✅ Listo | Login, registro, admin forms |
| `x-ui.select` | `components/ui/` | ✅ Listo | Registro, filtros, admin forms |
| `x-ui.button` | `components/ui/` | ✅ Listo | Todas las pantallas |
| `x-ui.badge` | `components/ui/` | ✅ Listo | Tablas de historial, cierre, admin |
| `x-ui.data-table` | `components/ui/` | ✅ Listo | Historial, cierre, admin listados |
| `x-ui.pagination` | `components/ui/` | ✅ Listo | Historial, cierre, admin listados |
| `x-ui.metric-card` | `components/ui/` | ✅ Listo | Dashboards, cierre |
| `x-ui.chart-container` | `components/ui/` | ✅ Listo | Dashboards (M3, M6) |
| `x-ui.modal` | `components/ui/` | ✅ Listo | Expiración, confirmaciones, anulación |
| `x-ui.toast` | `components/ui/` | ✅ Listo | Éxito/error después de acciones |
| `x-ui.filter-bar` | `components/ui/` | ✅ Listo | Historial, admin |
| `x-ui.empty-state` | `components/ui/` | ✅ Listo | Estados sin datos |
| `x-ui.error-state` | `components/ui/` | ✅ Listo | Errores de servidor |
| `x-ui.loading-state` | `components/ui/` | ✅ Listo | Carga de páginas/datos |
| `x-ui.tabs` | `components/ui/` | ✅ Listo | Admin (si aplica) |
| `x-ui.dropdown` | `components/ui/` | ✅ Listo | Topbar, acciones |
| `x-ui.tooltip` | `components/ui/` | ✅ Listo | Iconos, ayuda contextual |
| `x-ui.breadcrumbs` | `components/ui/` | ✅ Listo | Navegación profunda |
| `x-screen.operator-metrics` | `components/screen/` | ✅ Listo | Dashboard operador (M3) |
| `x-screen.operation-form` | `components/screen/` | 🔧 Necesita adaptación | Registro (M4) — adaptar para usar $assignments reales |
| `x-screen.operation-filters` | `components/screen/` | 🔧 Necesita adaptación | Historial (M5) — adaptar para opciones dinámicas |
| `x-screen.admin-filters` | `components/screen/` | 🔧 Necesita adaptación | Dashboard admin (M6) — adaptar para datos reales de DB |
| `x-screen.operator-comparison` | `components/screen/` | ✅ Listo | Dashboard admin (M6) |
| `x-screen.closing-warning` | `components/screen/` | ✅ Listo | Cierre diario (M8) |
| `x-screen.closing-detail` | `components/screen/` | 🔧 Necesita adaptación | Cierre diario (M8) — adaptar para datos reales |
| `x-screen.expiry-modal-content` | `components/screen/` | ✅ Listo | Sesión (M0, embebido en layout) |

**Leyenda**: ✅ Listo = usar sin cambios | 🔧 = requiere adaptación para datos reales

## Exception Tracking

No exceptions to constitutional principles required. All existing backend logic, authorization policies, audit trails, and security controls remain unchanged. The plan only modifies Blade view files and adds variable passing through existing middleware/controllers.

# Implementation Plan: Operaciones Generales por Agente

**Branch**: `008-simplify-agent-operations` | **Date**: 2026-07-23 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `specs/008-simplify-agent-operations/spec.md`

## Summary

Reconstrucción integral del dominio: sustituir el modelo Tienda-Banco-AgenteBancario por el modelo Agente centralizado, migrando operaciones, asignaciones, cierres e interfaces sin pérdida de datos ni interrupción del servicio. La arquitectura se mantiene como monolito modular Laravel 13, PHP 8.3, Blade server-rendered, CSS propio, JS modular, MySQL/MariaDB, sin SPA ni frameworks JS.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13.21.1

**Primary Dependencies**: Laravel Framework, lcobucci/jwt 5.x, Chart.js (solo dashboards), Vite (compilación pre-deploy)

**Storage**: MySQL 8.0 / MariaDB. Esquema actual: 18 migraciones, 22 modelos, ~64 rutas. El nuevo dominio reduce a ~9 entidades core, ~40 rutas tras limpieza.

**Time & Money**: DECIMAL(18,2), America/Lima, S/ como símbolo, períodos con boundaries explícitos. Efectivo y saldo digital como conceptos independientes.

**Authentication & Session**: JWT HS256 5min, refresh rotatorio HMAC-SHA-256, cookies HttpOnly/Secure/SameSite=Strict, modal 30s, renovación explícita, revocación en logout, 8h absolute TTL.

**Testing**: PHPUnit 12.x, ~270+ tests existentes. ~65 tests directamente dependientes de Bank/Store/BankAgent requieren reescritura o reemplazo. Tests de IdentityAccess (~30) no dependen del dominio antiguo.

**Target Platform**: Hosting PHP convencional (Apache/Nginx + PHP-FPM), sin Redis/Node.js en producción. Solo directorio `public/` expuesto.

**Project Type**: Aplicación web Laravel + Blade, server-rendered, monolito modular.

**Performance Goals**: Login <5s, dashboard <3s server render con 100 agentes/500 ops/100k registros, registro <2s, paginación 25/page, Chart.js solo en dashboards.

**Constraints**: Sin desplazamiento horizontal global, 4 breakpoints (375/768/1280/1440px), WCAG 2.2 AA, sin N+1, sin colecciones completas al navegador, comparación pixel-perfect con tolerancia 2px/0.5%.

**Scale/Scope**: 7 pantallas Stitch obligatorias, 7 módulos (Identity, Agents, Assignments, Operations, DailyFunds, Reporting, Audit), 0 tablas de bancos/tiendas al finalizar, migración con rollback <60min.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Specification completeness**: ✅ Spec 008 aprobada con 10 user stories, 35 FRs, 33 BRs, acceptance scenarios, edge cases, mandatory demo flow, exclusiones explícitas.
- **Increment classification**: ✅ Corrección mayor de especificación + cambio arquitectónico del dominio; se entrega por incrementos (migración → agentes → operaciones → fondos → dashboards → sesión → limpieza).
- **Security and privacy**: ✅ Roles ADMINISTRADOR_PROPIETARIO/OPERADOR aplicados en servidor; operador solo ve operaciones propias; admin ve toda su org. Referencia opcional de cliente acotada (BR-026, BR-009). Datos bancarios sensibles prohibidos.
- **Session safety**: ✅ JWT 5min, refresh rotatorio, hashes almacenados, renovación explícita, logout revocatorio, modal 30s, limpieza en token inválido.
- **Operation integrity**: ✅ Operaciones no se eliminan físicamente; anulación lógica con auditoría antes/después; snapshots de efectos monetarios inmutables.
- **Deployment compatibility**: ✅ Hosting PHP convencional, MySQL/MariaDB, Vite pre-compile, HTTPS, solo `public/` expuesto. Sin Redis, Node.js runtime, contenedores.
- **Minimal interface**: ✅ Blade server rendering, HTML semántico, CSS propio, JS modular, Chart.js solo en dashboards. Sin SPA, React, Vue, Angular, Inertia, Livewire.
- **Money and time**: ✅ DECIMAL(18,2), efectivo/digital independientes, America/Lima, period boundaries BR-020/BR-021, monto bruto no es ganancia.
- **Domain simplicity**: ✅ Modelo Agente único, sin bancos/tiendas/terminales. YAGNI aplicado: sin anticipación de entidades futuras.
- **Performance**: ✅ Paginación 25/page, índices para filtros frecuentes, agregación en servidor, sin N+1. Volumen de referencia: 100 agentes, 500 ops, 100k registros.
- **Observability and recovery**: ✅ Secret-safe logs, health route, debug off en prod, respaldo pre-migración, rollback probado <60min, migraciones reversibles.
- **Testing**: ✅ Cada escenario de aceptación tendrá prueba automatizada. Regresiones de dominio antiguo reemplazadas por equivalentes en nuevo dominio. ~65 tests existentes requieren actualización.
- **System boundary**: ✅ Registro interno de control operacional; no confirma procesamiento bancario, no integra bancos, no es contabilidad.

**Gate result**: PASS — constitución v2.0.0 ya enmendada. Sin excepciones requeridas.

## Impact Matrix

### Consolidación del dominio

| Concepto anterior | Nuevo concepto | Acción | Riesgo |
|---|---|---|---|
| Store (tienda) | Agente (agent) | Consolidar stores → agents con mapeo documentado | Pérdida de relación geográfica district→store |
| Bank (banco) | — (eliminado) | Eliminar tabla, migrar operation_types.bank_id a null | OperationType pierde filtro por banco |
| BankAgent (agente bancario) | Agente (agent) | Consolidar bank_agents → agents con mapeo; el nuevo agent.id reemplaza bank_agent_id en FKs | Duplicidad si store y bank_agent representan el mismo punto físico |
| UserBankAgentAssignment | UserAgentAssignment | Renombrar, reemplazar bank_agent_id → agent_id | Pérdida de historial si no se mapea correctamente |
| Operation.store_id | — (eliminado) | Dropear columna; el agente se obtiene vía agent_id | Migración de datos históricos |
| Operation.bank_agent_id | Operation.agent_id | Renombrar/migrar FK | Consistencia con nuevo dominio |
| DailyClosure.store_id | — (eliminado) | Dropear columna | Cálculo histórico de cierres |
| DailyClosure.bank_agent_id | DailyClosure.agent_id | Renombrar/migrar FK | Reapertura de cierres antiguos |
| OperationType.bank_id | — (eliminado) | Dropear columna; los tipos pasan a ser generales | Seeders y factories con bank_id |
| cash_direction (ENTRADA/SALIDA/NEUTRA) | cash_multiplier + digital_multiplier | Nueva migración con defaults | Operaciones históricas sin snapshots |

### Artefactos a retirar

| Artefacto | Tipo | Destino |
|---|---|---|
| `banks` table | Migración/Modelo | Eliminar tras respaldo |
| `stores` table | Migración/Modelo | Eliminar tras consolidación en agents |
| `bank_agents` table | Migración/Modelo | Eliminar tras consolidación en agents |
| `BankController` | Controlador | Eliminar |
| `StoreController` (CRUD stores) | Controlador | Reemplazar por AgentController |
| `BankAgentController` | Controlador | Reemplazar por AgentController |
| `BankPolicy`, `StorePolicy`, `BankAgentPolicy` | Policy | Reemplazar por AgentPolicy |
| `BankRequest`, `BankAgentRequest`, `StoreRequest` | FormRequest | Reemplazar por AgentRequest |
| `routes/banking-network.php` (bancos/agentes) | Rutas | Reemplazar por routes/agents.php |
| `routes/organization.php` (stores) | Rutas | Reemplazar por routes/agents.php |
| Vistas `banking-network/banks/`, `banking-network/agents/`, `organization/stores/` | Vistas | Reemplazar por agents/ |
| `MyAgentsController` (usa BankAgent) | Controlador | Adaptar a Agent |
| `UserBankAgentAssignmentController` | Controlador | Renombrar a UserAgentAssignmentController |
| `DashboardQueryService` filtros bank/store | Servicio | Eliminar filtros bancarios; adaptar store→agent |
| `OperationalStructureSeeder` | Seeder | Reemplazar por Agent seeder |
| `OperationTypeSeeder` (variantes por banco) | Seeder | Simplificar a tipos generales |
| `BankFactory`, `StoreFactory`, `BankAgentFactory` | Factory | Reemplazar por AgentFactory |
| `docs/product-brief.md` (referencias tiendas/bancos) | Documento | Actualizar terminología |
| `docs/deployment.md` (referencias stores/banks) | Documento | Actualizar respaldo |

### Conservar sin cambios

| Artefacto | Razón |
|---|---|
| IdentityAccess completo (auth, JWT, sesiones, eventos, auditoría) | Sin dependencias de banco/tienda |
| Geo-hierarchy (regions, provinces, districts) | Se conserva como referencia opcional del agente |
| AuditLog | Sin cambios estructurales |
| `app.css`, `tokens.css`, layout.css, componentes UI | Sin referencias funcionales a bancos |
| Stitch design assets (`docs/design/stitch/v1/`) | Fuente visual vigente; adaptar eliminando referencias a bancos |
| Constitution v2.0.0 | Ya enmendada; sin cambios requeridos |

## Project Structure

### Módulos objetivo

```text
app/Modules/
├── IdentityAccess/          # [CONSERVAR] Auth, sesiones, usuarios, roles
│   ├── Models/              # User, AuthSession, AuthRefreshToken, SessionEvent
│   ├── Services/            # JwtTokenService, AuthCookieService, RefreshTokenService
│   ├── Policies/            # UserPolicy, AuthSessionPolicy
│   └── Http/Controllers/    # LoginController, OperatorController, etc.
├── Agents/                  # [NUEVO] Reemplaza Organization/Store + BankingNetwork
│   ├── Models/              # Agent, UserAgentAssignment
│   ├── Policies/            # AgentPolicy
│   └── Http/Controllers/    # AgentController, UserAgentAssignmentController
├── Operations/              # [MODIFICAR] Sin bank_agent_id, store_id, bank_id
│   ├── Models/              # Operation, OperationType
│   ├── Actions/             # RegisterOperation (adaptado), ListOperations, AnnulOperation
│   ├── Policies/            # OperationPolicy, OperationTypePolicy
│   └── Http/Controllers/    # OperationController, OperationTypeController
├── DailyFunds/              # [RENOMBRADO desde DailyClosing]
│   ├── Models/              # DailyClosure (adaptado), DailyClosureOperation
│   ├── Policies/            # DailyClosingPolicy (adaptado)
│   └── Http/Controllers/    # DailyClosingController (adaptado)
├── Reporting/               # [MODIFICAR] Sin filtros bank/store
│   ├── Services/            # DashboardQueryService (sin bank_id, store_id)
│   ├── Policies/            # DashboardPolicy
│   └── Http/Controllers/    # DashboardController
├── Audit/                   # [CONSERVAR]
│   └── Models/              # AuditLog
└── Organization/            # [REDUCIDO] Solo geo-hierarchy
    ├── Models/              # Region, Province, District
    └── Policies/            # RegionPolicy, ProvincePolicy, DistrictPolicy
```

### Rutas objetivo

```text
# Identity (conservado)
GET  /login, POST /login
GET  /home
POST /auth/refresh, POST /logout
GET  /sessions
GET  /password/change, PATCH /password/change
GET  /admin/users, POST /admin/users, GET /admin/users/create
GET  /admin/users/{user}/edit, PATCH /admin/users/{user}
DELETE /admin/users/{user}
PATCH /admin/users/{user}/deactivate

# Agents (nuevo)
GET    /admin/agents, POST /admin/agents
GET    /admin/agents/create
GET    /admin/agents/{agent}/edit, PATCH /admin/agents/{agent}
DELETE /admin/agents/{agent}
GET    /admin/users/{user}/assignments, POST /admin/users/{user}/assignments
DELETE /admin/assignments/{assignment}
GET    /my-agents

# Operations (adaptado)
GET    /operations, GET /operations/create, POST /operations
GET    /operations/{operation}
POST   /operations/{operation}/annul
GET    /admin/operation-types, POST /admin/operation-types
GET    /admin/operation-types/create
GET    /admin/operation-types/{type}/edit, PATCH /admin/operation-types/{type}
DELETE /admin/operation-types/{type}

# Daily Funds (adaptado)
GET    /daily-closures, POST /daily-closures
GET    /daily-closures/create
GET    /daily-closures/{closure}
POST   /daily-closures/{closure}/confirm
POST   /daily-closures/{closure}/reopen

# Reporting (adaptado)
GET    /dashboard
GET    /admin/dashboard
GET    /admin/dashboard/operators

# Geo-hierarchy (conservado)
GET    /admin/regions, POST /admin/regions
... (regions/provinces/districts sin cambios)
```

### Estrategia de migración de datos

La migración se ejecuta en 5 fases con rollback por fase. Cada fase es una migración Laravel independiente con su `down()` probado.

**Fase 1 — Crear agents**:
1. Crear tabla `agents` (id, organization_id, code, name, city, region, province, district, address, description, is_active, timestamps)
2. INSERT en `agents` desde `stores`: `code=store.code, name=store.name, is_active=store.is_active, district_id=store.district_id`
3. INSERT en `agents` desde `bank_agents` que NO tengan store correspondiente: `code=bank_agent.code, name='Agente ' + bank_agent.code`
4. Si store y bank_agent representan el mismo punto físico → un solo agent
5. Registrar mapeo `store_id→agent_id` y `bank_agent_id→agent_id` en tabla temporal `_migration_map`

**Fase 2 — Migrar asignaciones**:
1. Crear tabla `user_agent_assignments` (misma estructura que user_bank_agent_assignments pero con agent_id)
2. INSERT desde user_bank_agent_assignments mapeando bank_agent_id→agent_id vía `_migration_map`
3. Conservar timestamps, assigned_by, is_active originales

**Fase 3 — Migrar operaciones**:
1. Agregar `agent_id` (nullable) a `operations`
2. UPDATE operations.agent_id desde bank_agent_id vía `_migration_map`
3. Agregar `customer_name` (varchar 200 nullable) a `operations`
4. Agregar `cash_delta` y `digital_delta` (decimal 18,2, default 0) a `operations`
5. Poblar cash_delta/digital_delta desde operation_types.cash_direction: ENTRADA→+amount en cash_delta, SALIDA→-amount en cash_delta, NEUTRA→0. Digital inicialmente 0 para históricos.
6. Agregar `internal_code` (varchar 30 unique) a `operations`, generar desde id + prefijo

**Fase 4 — Migrar cierres**:
1. Agregar `agent_id` (nullable) a `daily_closures`
2. UPDATE daily_closures.agent_id desde bank_agent_id vía `_migration_map`
3. Agregar columnas: opening_cash, opening_digital, expected_closing_cash, expected_closing_digital, actual_closing_cash, actual_closing_digital, cash_difference, digital_difference (todas decimal 18,2)
4. Agregar columnas de estados: opened_by, submitted_by, opened_at, submitted_at
5. Poblar con defaults (0 para saldos, timestamps actuales)

**Fase 5 — Limpieza**:
1. Dropear FKs: operations.store_id→stores, operations.bank_agent_id→bank_agents, daily_closures.store_id→stores, daily_closures.bank_agent_id→bank_agents, operation_types.bank_id→banks, bank_agents.store_id→stores, bank_agents.bank_id→banks
2. Dropear columnas: operations.store_id, operations.bank_agent_id (ya migradas a agent_id), daily_closures.store_id, daily_closures.bank_agent_id, operation_types.bank_id
3. Hacer operations.agent_id NOT NULL
4. Hacer daily_closures.agent_id NOT NULL
5. Dropear tablas: banks, stores, bank_agents, user_bank_agent_assignments (datos ya migrados)
6. Dropear tabla temporal `_migration_map`
7. Renombrar user_agent_assignments si es necesario

**Rollback**: Cada fase tiene `down()` que revierte los cambios. La tabla `_migration_map` permite reconstruir el mapeo inverso. Respaldos completos antes de cada fase.

## Exception Tracking

> No constitutional violations. This section is empty.

---

## Fase 0: Investigación y Diseño

Ver [research.md](./research.md) para decisiones de arquitectura detalladas.

**Decisiones clave**:
1. **Agente = consolidación Store + BankAgent**: Un store y sus bank_agents que compartan ubicación se consolidan en un solo agent. Si un store tiene bank_agents en múltiples bancos, se crea un agent por cada punto físico realmente distinto. El mapeo se documenta en `_migration_map`.
2. **OperationType sin bank_id**: La columna se droppea. Los tipos son generales. El multiplicador cash/digital reemplaza cash_direction.
3. **DashboardQueryService sin filtros bancarios**: Se eliminan bank_id, store_id de `applyAdminFilters()`. store_id→agent_id. Se agregan filtros por ciudad/región opcionales en el agente.
4. **Internal code**: Formato `OP-{YYYYMMDD}-{SEQ4}` generado en servidor. Único e inmutable.
5. **Módulo Agents**: Nuevo módulo `app/Modules/Agents/` con AgentController, AgentPolicy, AgentRequest, vistas agents/.
6. **Estrategia incremental**: Cada incremento es un PR independiente con sus pruebas. La aplicación nunca queda inaccesible.

## Fase 1: Contratos y Modelo de Datos

Ver [data-model.md](./data-model.md) para el esquema completo de entidades.

Ver [contracts/view-contracts.md](./contracts/view-contracts.md) para contratos vista-controlador.

Ver [quickstart.md](./quickstart.md) para guía de validación.

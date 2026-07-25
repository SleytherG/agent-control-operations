# View Contracts: Operaciones Generales por Agente

**Feature**: 008-simplify-agent-operations | **Date**: 2026-07-23

## C01 — Agent Index/List
- **Route**: `GET /admin/agents` (`admin.agents.index`)
- **Controller**: `AgentController@index`
- **View**: `agents/index.blade.php`
- **Layout**: `layouts.authenticated`
- **Variables**: `$agents` (paginated Agent collection), `$filters` (city, is_active)
- **Auth**: `Gate::authorize('viewAny', Agent::class)` → ADMINISTRADOR_PROPIETARIO

## C02 — Agent Create/Edit Form
- **Route**: `GET /admin/agents/create` (`admin.agents.create`), `GET /admin/agents/{agent}/edit` (`admin.agents.edit`)
- **Controller**: `AgentController@create`, `AgentController@edit`
- **View**: `agents/form.blade.php`
- **Variables**: `$agent` (Agent|null), `$cities` (distinct cities for autocomplete)
- **Auth**: `Gate::authorize('create', Agent::class)` / `Gate::authorize('update', $agent)`

## C03 — Agent Store/Update/Deactivate
- **Route**: `POST /admin/agents`, `PATCH /admin/agents/{agent}`, `DELETE /admin/agents/{agent}`
- **Controller**: `AgentController@store`, `AgentController@update`, `AgentController@deactivate`
- **Request**: `AgentRequest` — rules: code (required, unique per org, max:80), name (required, max:200), city (required, max:160), region/province/district/address/description (nullable)
- **Auth**: Policies correspondientes

## C04 — User-Agent Assignment List
- **Route**: `GET /admin/users/{user}/assignments` (`admin.users.assignments.index`)
- **Controller**: `UserAgentAssignmentController@index`
- **View**: `agents/assignments/index.blade.php`
- **Variables**: `$user` (User), `$assignments` (paginated UserAgentAssignment with agent)
- **Auth**: `Gate::authorize('viewAny', Agent::class)`

## C05 — User-Agent Assignment Create/Destroy
- **Route**: `POST /admin/users/{user}/assignments`, `DELETE /admin/assignments/{assignment}`
- **Controller**: `UserAgentAssignmentController@store`, `UserAgentAssignmentController@destroy`
- **Request**: `AssignAgentRequest` — rules: agent_id (required, exists active agents)
- **Auth**: ADMINISTRADOR_PROPIETARIO, no duplicate active assignment

## C06 — My Agents (Operator)
- **Route**: `GET /my-agents` (`my-agents.index`)
- **Controller**: `MyAgentsController@index` (adaptado)
- **View**: `agents/my-agents.blade.php`
- **Variables**: `$assignments` (UserAgentAssignment where user_id = auth()->id(), is_active = true)
- **Auth**: Usuario autenticado

## C07 — Operation Registration Form
- **Route**: `GET /operations/create` (`operations.create`)
- **Controller**: `OperationController@create`
- **View**: `operations/create.blade.php`
- **Variables**: `$agent` (Agent activo desde asignación), `$types` (OperationType activos), `$idempotencyKey`
- **Component**: `<x-screen.operation-form>` adaptado: sin bank_agent_id, sin store, con customer_name, con agent context visible
- **Auth**: `Gate::authorize('register', Operation::class)` → OPERADOR con asignación activa

## C08 — Operation Store
- **Route**: `POST /operations` (`operations.store`)
- **Controller**: `OperationController@store`
- **Request**: `RegisterOperationRequest` (adaptado) — agent_id (derivado del servidor, no del cliente), operation_type_id, amount (>0), currency (PEN), customer_name (nullable, max:200), notes (nullable, max:500), occurred_at (now para operador, configurable para admin), idempotency_key
- **Auth**: `Gate::authorize('register', Operation::class)`

## C09 — Operation History Index
- **Route**: `GET /operations` (`operations.index`)
- **Controller**: `OperationController@index`
- **View**: `operations/index.blade.php`
- **Variables**: `$operations` (paginated), `$agents` (para filtro admin), `$types`, `$summary` (total_ops, gross, cash_in/out, digital_in/out, net)
- **Filters**: date_from, date_to, agent_id, operator_user_id (admin only), operation_type_id, status, internal_code, customer_name
- **Auth**: `Gate::authorize('viewAny', Operation::class)`

## C10 — Operation Detail
- **Route**: `GET /operations/{operation}` (`operations.show`)
- **Controller**: `OperationController@show`
- **View**: `operations/show.blade.php`
- **Variables**: `$operation` (con agent, operationType, operator_user, voidedBy)
- **Auth**: `Gate::authorize('view', $operation)`

## C11 — Operation Annul
- **Route**: `POST /operations/{operation}/annul` (`operations.annul`)
- **Controller**: `OperationController@annul`
- **Request**: `AnnulOperationRequest` — reason (required, max:500)
- **Auth**: `Gate::authorize('annul', $operation)`

## C12 — Operation Type CRUD
- **Routes**: `GET/POST /admin/operation-types`, `GET /admin/operation-types/create`, `GET /admin/operation-types/{type}/edit`, `PATCH/DELETE /admin/operation-types/{type}`
- **Controller**: `OperationTypeController` (adaptado, sin bank_id)
- **View**: `operations/types/index.blade.php`, `operations/types/form.blade.php`
- **Request**: `OperationTypeRequest` (adaptado) — name (required), description (nullable), cash_multiplier (required, -1/0/+1), digital_multiplier (required, -1/0/+1), sort_order (int), is_active (bool)
- **Auth**: Policies correspondientes (ADMINISTRADOR_PROPIETARIO)

## C13 — Daily Closures Index
- **Route**: `GET /daily-closures` (`daily-closures.index`)
- **Controller**: `DailyClosingController@index`
- **View**: `daily-closing/index.blade.php`
- **Variables**: `$closures` (paginated con agent), `$agents` (para filtro)
- **Filters**: agent_id, date_from, date_to, status
- **Auth**: `Gate::authorize('viewAny', DailyClosure::class)`

## C14 — Daily Closure Detail
- **Route**: `GET /daily-closures/{closure}` (`daily-closures.show`)
- **Controller**: `DailyClosingController@show`
- **View**: `daily-closing/show.blade.php`
- **Variables**: `$closure` (con agent, operator_breakdown, type_breakdown, inconsistencies)
- **Auth**: `Gate::authorize('view', $closure)`

## C15 — Daily Closure Confirm/Reopen
- **Routes**: `POST /daily-closures/{closure}/confirm`, `POST /daily-closures/{closure}/reopen`
- **Controller**: `DailyClosingController@confirm`, `DailyClosingController@reopen`
- **Auth**: ADMINISTRADOR_PROPIETARIO; reopen requiere motivo
- **Differences**: Si cash_difference o digital_difference ≠ 0 → warning + motivo obligatorio

## C16 — Operator Dashboard
- **Route**: `GET /dashboard` (`dashboard.operator`)
- **Controller**: `DashboardController@operatorDashboard`
- **View**: `reporting/operator-dashboard.blade.php`
- **Variables**: `$metrics` (operation_count, gross_amount, cash_in, cash_out, digital_in, digital_out), `$typeDistribution`, `$timeEvolution`, `$recentOperations`
- **Auth**: `Gate::authorize('viewOperatorDashboard')`

## C17 — Admin Dashboard
- **Route**: `GET /admin/dashboard` (`admin.dashboard`)
- **Controller**: `DashboardController@adminDashboard`
- **View**: `reporting/admin-dashboard.blade.php`
- **Variables**: `$metrics`, `$agents`, `$operators`, `$types`, rankings, time evolution, operator comparison
- **Filters**: period, agent_id, city, operator_user_id, operation_type_id, status, time_range (sin bank_id, store_id)
- **Auth**: `Gate::authorize('viewAdminDashboard')`

## C18 — Session Indicator (embedded)
- **Component**: `x-layout.session-indicator`
- **Embedded in**: `layouts/authenticated.blade.php` via topbar
- **Data**: `$sessionExpiresAt` from middleware → countdown, modal at 30s, POST `/auth/refresh`, POST `/logout`
- **Auth**: Usuario autenticado

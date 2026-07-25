# Tasks: Operaciones Generales por Agente

**Input**: Design documents from `specs/008-simplify-agent-operations/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/view-contracts.md, quickstart.md

**Tests**: Automated tests REQUIRED for every acceptance scenario plus authorization (positive/negative), monetary boundaries, JWT lifecycle, audit, migration, and recovery tests.

**Organization**: Tasks grouped in 16 phases per user specification, mapped to spec user stories.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Maps to spec user story (US1–US10)
- Exact file paths in every description

---

## Phase 1: Auditoría del sistema actual

**Goal**: Documentar el estado completo del código base antes de cualquier cambio.

- [X] T001 [P] Inventariar todas las tablas de base de datos con columnas, FKs e índices en `database/migrations/` — generar `specs/008-simplify-agent-operations/audit/tables.md`
- [X] T002 [P] Inventariar todos los modelos Eloquent con fillable, casts y relaciones en `app/Modules/*/Models/` — generar `specs/008-simplify-agent-operations/audit/models.md`
- [X] T003 [P] Inventariar todas las rutas nombradas con método HTTP, URL, controlador y middleware en `routes/*.php` — generar `specs/008-simplify-agent-operations/audit/routes.md`
- [X] T004 [P] Inventariar todos los controladores con métodos y Gate::authorize calls en `app/Http/Controllers/` y `app/Modules/*/Http/Controllers/` — generar `specs/008-simplify-agent-operations/audit/controllers.md`
- [X] T005 [P] Inventariar todas las vistas Blade agrupadas por dominio en `resources/views/` — generar `specs/008-simplify-agent-operations/audit/views.md`
- [X] T006 [P] Identificar todos los seeders y factories con datos reales vs dummy en `database/seeders/` y `database/factories/` — generar `specs/008-simplify-agent-operations/audit/data-classification.md`
- [X] T007 [P] Inventariar todos los policies, form requests, actions y services con referencias a Bank/Store/BankAgent en `app/Modules/*/` — generar `specs/008-simplify-agent-operations/audit/bank-store-references.md`
- [X] T008 [P] Inventariar todos los archivos Stitch bajo `docs/design/stitch/v1/` con conteo de referencias a banco/tienda — generar `specs/008-simplify-agent-operations/audit/stitch-references.md`
- [X] T009 Marcar specs 002, 003, 004, 005, 007 como superadas añadiendo banner de "Superseded by 008" en cada `specs/00X-*/spec.md`

---

## Phase 2: Protección y estrategia de datos

**Goal**: Respaldar, mapear y validar la migración antes de tocar datos productivos.

- [X] T010 [P] Crear script de backup pre-migración en `specs/008-simplify-agent-operations/scripts/backup.sh` que ejecute `mysqldump` con verificaciones de integridad
- [X] T011 [P] Crear migration `2026_07_23_000001_create_migration_map_table.php` en `database/migrations/` con tabla `_migration_map` (old_table, old_id, new_agent_id, notes)
- [X] T012 Diseñar reglas de consolidación Store→Agent y BankAgent→Agent en `specs/008-simplify-agent-operations/scripts/mapping-rules.md` — si un store y sus bank_agents comparten ubicación, un solo agent; si bank_agent huérfano, agent independiente
- [X] T013 [P] Crear test de integridad de migración en `tests/Feature/Migration/MigrationIntegrityTest.php` — verifica conteos pre/post, FK validity, rollback mediante `RefreshDatabase`
- [X] T014 [P] Crear fixtures de migración en `tests/Fixtures/MigrationFixtures.php` — crea stores, banks, bank_agents, operations, closures con datos conocidos para validar transformación
- [X] T015 Crear script de rollback en `specs/008-simplify-agent-operations/scripts/rollback.sh` que restaure backup y ejecute `migrate:rollback` por fase

---

## Phase 3: Nuevo modelo Agents

**Goal**: Crear tabla agents, modelo, policies, controlador, vistas CRUD y asignaciones. Mapeado a US2.

### Tests for Agents (US2)

- [X] T016 [P] [US2] Test de autorización de agentes en `tests/Feature/Agents/AgentAuthorizationTest.php` — admin CRUD positivo, operador rechazado, org-crossing bloqueado
- [X] T017 [P] [US2] Test de CRUD de agentes en `tests/Feature/Agents/AgentCrudTest.php` — crear, editar con código único, activar/desactivar, validación de campos obligatorios/opcionales

### Implementation for Agents (US2)

- [X] T018 [US2] Crear migration `2026_07_23_000002_create_agents_table.php` en `database/migrations/` con columns: id, organization_id, code, name, city, region, province, district, address, description, is_active, deactivated_at, timestamps, unique(org_id, code)
- [X] T019 [P] [US2] Crear modelo `Agent` en `app/Modules/Agents/Models/Agent.php` con fillable, casts, belongsTo(Organization), hasMany(UserAgentAssignment)
- [X] T020 [P] [US2] Crear `AgentPolicy` en `app/Modules/Agents/Policies/AgentPolicy.php` — viewAny/create: ADMINISTRADOR_PROPIETARIO; view/update/deactivate: ADMINISTRADOR_PROPIETARIO + same org
- [X] T021 [US2] Crear `AgentController` en `app/Modules/Agents/Http/Controllers/AgentController.php` — index (paginated + city/is_active filters), create, store, edit, update, deactivate con Gate::authorize
- [X] T022 [US2] Crear `AgentRequest` en `app/Modules/Agents/Http/Requests/AgentRequest.php` — code (required, unique per org, max:80), name (required, max:200), city (required, max:160), region/province/district/address/description (nullable)
- [X] T023 [P] [US2] Crear vista `resources/views/agents/index.blade.php` — tabla con código, nombre, ciudad, estado badge, acciones (editar/desactivar), filtro por ciudad, botón "Nuevo Agente"
- [X] T024 [P] [US2] Crear vista `resources/views/agents/form.blade.php` — formulario con campos Agent, validación inline, breadcrumb
- [X] T025 [US2] Agregar rutas agents en `routes/agents.php` — resource CRUD bajo `/admin/agents` con middleware `web, AuthenticateJwtSession`, namespace `admin.agents.*`

### Implementation for Assignments (US2)

- [X] T026 [US2] Crear migration `2026_07_23_000003_create_user_agent_assignments_table.php` en `database/migrations/` — user_id, agent_id, assigned_by, starts_at, ends_at, is_active, timestamps; indexes (user_id, is_active), (agent_id, is_active)
- [X] T027 [P] [US2] Crear modelo `UserAgentAssignment` en `app/Modules/Agents/Models/UserAgentAssignment.php` con belongsTo(User), belongsTo(Agent), belongsTo(assignedBy→User)
- [X] T028 [US2] Crear `UserAgentAssignmentController` en `app/Modules/Agents/Http/Controllers/UserAgentAssignmentController.php` — index (asignaciones de un user), store (asignar operador a agente, sin duplicados activos), destroy (finalizar asignación)
- [X] T029 [US2] Crear `AssignAgentRequest` en `app/Modules/Agents/Http/Requests/AssignAgentRequest.php` — agent_id (required, exists active agents), validación de no duplicado activo
- [X] T030 [P] [US2] Crear vista `resources/views/agents/assignments/index.blade.php` — tabla de asignaciones con agente, inicio, fin, estado, botón desasignar, formulario de nueva asignación con select de agentes activos
- [X] T031 [US2] Agregar rutas assignments en `routes/agents.php` — GET/POST `/admin/users/{user}/assignments`, DELETE `/admin/assignments/{assignment}`
- [X] T032 [US2] Adaptar `app/Modules/BankingNetwork/Http/Controllers/MyAgentsController.php` → `app/Modules/Agents/Http/Controllers/MyAgentsController.php` — consulta `UserAgentAssignment` por auth user, reemplaza bankAgent por agent
- [X] T033 [P] [US2] Crear vista `resources/views/agents/my-agents.blade.php` — tabla de agentes asignados al operador autenticado con código, nombre, ciudad, fecha asignación

---

## Phase 4: Tipos generales y efectos monetarios

**Goal**: Migrar operation_types sin bank_id, con cash_multiplier y digital_multiplier. Mapeado a US5.

### Tests for Operation Types (US5)

- [X] T034 [P] [US5] Test de catálogo de tipos en `tests/Feature/Operations/OperationTypeCatalogTest.php` — crear tipos con multiplicadores, validar efectos, tipos sin configuración completa

### Implementation for Operation Types (US5)

- [X] T035 [US5] Crear migration `2026_07_23_000004_update_operation_types_add_multipliers.php` en `database/migrations/` — agrega cash_multiplier (tinyint, default 0), digital_multiplier (tinyint, default 0), sort_order (int, default 0); droppea bank_id FK y columna
- [X] T036 [US5] Actualizar modelo `OperationType` en `app/Modules/Operations/Models/OperationType.php` — remover bank() relationship y bank_id de fillable; agregar cash_multiplier, digital_multiplier, sort_order
- [X] T037 [US5] Actualizar `OperationTypeController` en `app/Modules/Operations/Http/Controllers/OperationTypeController.php` — remover filtros/bank_id del index; actualizar store/update para usar multiplicadores
- [X] T038 [US5] Actualizar `OperationTypeRequest` en `app/Modules/Operations/Http/Requests/OperationTypeRequest.php` — reemplazar bank_id y cash_direction por cash_multiplier (required, in:-1,0,1), digital_multiplier (required, in:-1,0,1), sort_order (int)
- [X] T039 [US5] Actualizar vista `resources/views/operations/types/index.blade.php` — remover columna Banco, filtro por bank_id; mantener paginación, badges de estado
- [X] T040 [US5] Actualizar vista `resources/views/operations/types/form.blade.php` — remover campo bank_id; agregar cash_multiplier (select: Entrada +1/Salida -1/Sin efecto 0), digital_multiplier, sort_order
- [X] T041 [US5] Reescribir `database/seeders/OperationTypeSeeder.php` — solo tipos generales sin bank_id: Depósito, Retiro, Transferencia, Pago de servicio, Recarga, Cobro, Envío, Otro con sus multiplicadores

---

## Phase 5: Reconstrucción de Operations

**Goal**: Nueva estructura de operations con agent_id, internal_code, customer_name, cash_delta, digital_delta. Mapeado a US3 + US4 + US8.

### Tests for Operations (US3 + US4 + US8)

- [X] T042 [P] [US3] Test de registro de operación en `tests/Feature/Operations/OperationRegistrationTest.php` — crear con agente activo, verificar código, autor, snapshots, idempotencia
- [X] T043 [P] [US3] Test de validación de registro en `tests/Feature/Operations/OperationValidationTest.php` — monto ≤0, tipo inactivo, agente no autorizado, operador sin asignación
- [X] T044 [P] [US4] Test de historial en `tests/Feature/Operations/OperationHistoryTest.php` — filtros, paginación, operador ve solo propio, admin ve todos
- [X] T045 [P] [US8] Test de anulación en `tests/Feature/Operations/OperationAnnulmentTest.php` — admin anula con motivo, doble anulación rechazada, auditoría
- [X] T046 [P] [US3] Test de operación retroactiva en `tests/Feature/Operations/OperationRetroactiveTest.php` — operador rechazado con fecha pasada, admin aceptado dentro de ventana 24h

### Implementation for Operations (US3 + US4 + US8)

- [X] T047 [US3] Crear migration `2026_07_23_000005_update_operations_new_structure.php` en `database/migrations/` — agrega internal_code (varchar 30, unique), agent_id (FK→agents, nullable inicial), operator_user_id (renombra user_id), customer_name (varchar 200, nullable), cash_delta (decimal 18,2, default 0), digital_delta (decimal 18,2, default 0); renombra observation→notes, annulled_by→voided_by, annulled_at→voided_at, annulment_reason→void_reason
- [X] T048 [US3] Actualizar modelo `Operation` en `app/Modules/Operations/Models/Operation.php` — remover store(), bankAgent(); agregar agent(); renombrar user→operatorUser, annulledBy→voidedBy; agregar fillable para internal_code, customer_name, cash_delta, digital_delta
- [X] T049 [US3] Crear servicio `InternalCodeGenerator` en `app/Modules/Operations/Services/InternalCodeGenerator.php` — genera `OP-YYYYMMDD-NNNN` con secuencia atómica global, lock a nivel DB o cache
- [X] T050 [US3] Actualizar `RegisterOperation` action en `app/Modules/Operations/Application/Actions/RegisterOperation.php` — reemplazar bank_agent_id por agent_id; derivar operador de auth()->id(); derivar agente de UserAgentAssignment activa; calcular cash_delta/digital_delta desde operation_type.multipliers × amount; generar internal_code; aplicar BR-031
- [X] T051 [US3] Actualizar `RegisterOperationRequest` en `app/Modules/Operations/Http/Requests/RegisterOperationRequest.php` — remover bank_agent_id; agregar customer_name (nullable, max:200), notes (nullable, max:500); mantener amount, operation_type_id, idempotency_key; server-side: agent_id se deriva, no del request
- [X] T052 [US3] Actualizar `OperationController@create` en `app/Modules/Operations/Http/Controllers/OperationController.php` — obtener agente activo de UserAgentAssignment (auto-select si único, requerir selección si múltiple); pasar $agent, $types, $idempotencyKey a vista
- [X] T053 [US3] Actualizar `OperationController@store` en `app/Modules/Operations/Http/Controllers/OperationController.php` — validar agente autorizado, ejecutar RegisterOperation, retornar redirect con internal_code
- [X] T054 [US4] Actualizar `ListOperations` action en `app/Modules/Operations/Application/Actions/ListOperations.php` — remover filtros bank_agent_id; agregar filtro agent_id, internal_code, customer_name; eager load agent, operatorUser, operationType; mantener paginación 25
- [X] T055 [US4] Actualizar `OperationController@index` en `app/Modules/Operations/Http/Controllers/OperationController.php` — pasar $agents (admin: todos; operador: asignados) y $summary al dashboard
- [X] T056 [US8] Actualizar `AnnulOperation` action en `app/Modules/Operations/Application/Actions/AnnulOperation.php` — reemplazar bank_agent_id en queries de cierre; actualizar voided_by, voided_at, void_reason
- [X] T057 [US8] Actualizar `OperationController@annul` y `AnnulOperationRequest` — mantener motivo obligatorio, verificar que admin anula y operación está activa
- [X] T058 [US3] Actualizar `OperationPolicy` en `app/Modules/Operations/Policies/OperationPolicy.php` — view/annul: admin same org; operador: ownership + same org; register: OPERADOR con asignación activa
- [ ] T059 [US3] Actualizar vista `resources/views/operations/create.blade.php` — mostrar agente activo en layout, campos: tipo, monto, cliente opcional, fecha/hora (admin puede editar, operador readonly), notas; remover campos bank_agent_id, store
- [ ] T060 [US3] Actualizar componente `resources/views/components/screen/operation-form.blade.php` — reemplazar bank_agent select por agent context display; remover bank/store labels; agregar customer_name input; mantener monto prioritario
- [ ] T061 [US4] Actualizar vista `resources/views/operations/index.blade.php` — columna Agente reemplaza Banco; filtros sin bank_id (agente, tipo, estado, código, cliente, fechas); summary con efectivo/digital separados
- [ ] T062 [US4] Actualizar vista `resources/views/operations/show.blade.php` — labels: Agente, Operador, Tipo, Cliente, Monto, Efectivo Δ, Digital Δ, Código; remover Banco/Tienda
- [ ] T063 [US8] Actualizar vista `resources/views/operations/annul.blade.php` — labels actualizados, motivo obligatorio

### Data Migration for Operations (US1)

- [ ] T064 [US1] Crear migration `2026_07_23_000006_migrate_operations_data.php` en `database/migrations/` — UPDATE operations.agent_id desde bank_agent_id vía _migration_map; poblar internal_code desde id con formato OP-LEGACY-{id}; poblar customer_name=NULL; cash_delta desde operation_types.cash_direction; digital_delta=0
- [ ] T065 [US1] Crear test de integridad post-migración en `tests/Feature/Migration/OperationsMigrationTest.php` — verifica que todas las operaciones tienen agent_id NOT NULL, internal_code único, cash_delta/digital_delta poblados

---

## Phase 6: Apertura y cierre diario

**Goal**: Adaptar daily_closures al dominio Agent con saldos de efectivo/digital. Mapeado a US6.

### Tests for Daily Funds (US6)

- [X] T066 [P] [US6] Test de apertura en `tests/Feature/DailyFunds/OpeningTest.php` — admin registra efectivo/digital inicial, no duplicado, confirmación, auditoría
- [X] T067 [P] [US6] Test de cierre en `tests/Feature/DailyFunds/ClosingTest.php` — calcular esperados, ingresar reales, diferencias, warning, confirmación, reapertura
- [X] T068 [P] [US6] Test de autorización de cierre en `tests/Feature/DailyFunds/ClosingAuthorizationTest.php` — solo admin confirma/reabre, operador prepara/presenta
- [X] T069 [P] [US6] Test de diferencias en `tests/Feature/DailyFunds/ClosingDifferencesTest.php` — confirmar con diferencias, motivo obligatorio, warning visible

### Implementation for Daily Funds (US6)

- [X] T070 [US6] Crear migration `2026_07_23_000007_update_daily_closures_new_structure.php` en `database/migrations/` — agrega agent_id (FK→agents, nullable), total_cash_in, total_cash_out, total_digital_in, total_digital_out, opening_cash, opening_digital, expected_closing_cash, expected_closing_digital, actual_closing_cash, actual_closing_digital, cash_difference, digital_difference, has_inconsistencies, opened_by, submitted_by, opened_at, submitted_at, notes; renombra cash_in→obsoleto, cash_out→obsoleto, net_movement→obsoleto; droppea store_id FK
- [X] T071 [US6] Actualizar modelo `DailyClosure` en `app/Modules/DailyClosing/Models/DailyClosure.php` — remover store(), bankAgent(); agregar agent(); remover fillable obsoletos; agregar nuevos fillable y casts
- [X] T072 [US6] Crear/actualizar `DailyClosingController` en `app/Modules/DailyClosing/Http/Controllers/DailyClosingController.php` — index: filtrar por agent_id; create: seleccionar agente con asignación activa; store: crear apertura BORRADOR; show: calcular esperados y diferencias; confirm: admin confirma, warning si diferencias; reopen: admin reabre con motivo
- [X] T073 [US6] Actualizar `DailyClosingPolicy` en `app/Modules/DailyClosing/Policies/DailyClosingPolicy.php` — reemplazar bank_agent_id por agent_id en view/generate; confirm/reopen: ADMINISTRADOR_PROPIETARIO + same org
- [X] T074 [US6] Crear acción `CalculateClosing` en `app/Modules/DailyClosing/Application/Actions/CalculateClosing.php` — suma operaciones activas del agente/fecha; calcula gross, cash_in/out, digital_in/out desde snapshots; esperados = inicial + entradas - salidas; diferencias = real - esperado
- [ ] T075 [US6] Actualizar vista `resources/views/daily-closing/index.blade.php` — filtro por agente (sin bank_agent_id); tabla con agent, fecha, estado badge, ops, gross, net
- [ ] T076 [US6] Actualizar vista `resources/views/daily-closing/show.blade.php` — contexto: Agente, Fecha; KPIs con efectivo/digital separados; breakdown por operador y tipo; warning de diferencias; botones confirmar/reabrir con modal de motivo
- [ ] T077 [US6] Actualizar vista `resources/views/daily-closing/create.blade.php` — select de agente (solo asignados activos), inputs para opening_cash y opening_digital

### Data Migration for Daily Funds (US1)

- [ ] T078 [US1] Crear migration `2026_07_23_000008_migrate_daily_closures_data.php` en `database/migrations/` — UPDATE daily_closures.agent_id desde bank_agent_id vía _migration_map; poblar opening_cash=0, opening_digital=0, expected_cash/expected_digital=0
- [ ] T079 [US1] Crear test de integridad de cierres en `tests/Feature/Migration/ClosuresMigrationTest.php` — verifica agent_id NOT NULL, business_date preservado, status preservado

---

## Phase 7: Sesión y modal de expiración

**Goal**: Temporizador real, modal 30s, renovación, logout, expiración. Mapeado a US9.

### Tests for Session (US9)

- [ ] T080 [P] [US9] Test de temporizador en `tests/Feature/Session/SessionTimerTest.php` — verifica que el contador usa vencimiento del servidor, se recalcula al recuperar visibilidad
- [ ] T081 [P] [US9] Test de modal y renovación en `tests/Feature/Session/SessionModalTest.php` — modal a 30s, continuar→refresh rotado, cerrar→logout revocatorio
- [ ] T082 [P] [US9] Test de expiración en `tests/Feature/Session/SessionExpiryTest.php` — token vencido rechaza peticiones, redirige a login, limpia estado

### Implementation for Session (US9)

- [ ] T083 [US9] Verificar que `x-layout.session-indicator` en `resources/views/components/layout/session-indicator.blade.php` recibe `$sessionExpiresAt` del middleware y muestra countdown real con `data-expires-at`
- [ ] T084 [US9] Verificar que `layouts/authenticated.blade.php` renderiza session-indicator en el topbar (ya implementado en estabilización previa)
- [ ] T085 [US9] Verificar que el modal de expiración en `resources/views/components/screen/expiry-modal-content.blade.php` se activa a 30s y conecta a `POST /auth/refresh` y `POST /logout`
- [ ] T086 [US9] Verificar que `RefreshSessionController` rota el refresh token y retorna nuevo `expiresAt` en `app/Modules/IdentityAccess/Http/Controllers/RefreshSessionController.php`
- [ ] T087 [US9] Verificar que `LogoutController` revoca sesión, registra evento y limpia cookies en `app/Modules/IdentityAccess/Http/Controllers/LogoutController.php`
- [X] T088 [US9] Agregar preservación de formulario

---

## Phase 8: UI pixel-perfect — login y layout

**Goal**: Login y layout autenticado reproduciendo screen.png Stitch con datos reales. Mapeado a US10.

- [ ] T089 [P] [US10] Comparar `resources/views/identity-access/login.blade.php` contra `docs/design/stitch/v1/inicio_de_sesi_n/screen.png` — documentar desviaciones en `specs/008-simplify-agent-operations/visual-diffs/login.md`
- [ ] T090 [P] [US10] Comparar `resources/views/layouts/authenticated.blade.php` y topbar/sidebar contra `docs/design/stitch/v1/screen.png` referencias — documentar desviaciones de layout
- [ ] T091 [US10] Actualizar textos en topbar y sidebar — reemplazar referencias a Tiendas/Bancos/Agentes Bancarios por Agentes; verificar "Control de operaciones" en topbar
- [ ] T092 [US10] Verificar responsive en login y layout en 375px, 768px, 1280px, 1440px — sin scroll horizontal, hamburger funcional, sidebar colapsable
- [ ] T093 [US10] Capturar screenshots post-fix en `specs/008-simplify-agent-operations/visual-diffs/login/` y `layout/` para los 4 viewports

---

## Phase 9: UI pixel-perfect — registro de operaciones

**Goal**: Formulario de registro reproduciendo `registro_r_pido_de_operaci_n/screen.png`. Mapeado a US10.

- [ ] T094 [P] [US10] Comparar `resources/views/operations/create.blade.php` + `components/screen/operation-form.blade.php` contra screen.png — documentar campos bancarios a eliminar y desviaciones en `specs/008-simplify-agent-operations/visual-diffs/register.md`
- [ ] T095 [US10] Eliminar campos banco/tienda/terminal del formulario; mostrar agente activo como contexto visible (nombre, código)
- [ ] T096 [US10] Conectar selector de tipo a tipos generales (sin bank_id); conectar monto a currency-input con prefijo S/
- [ ] T097 [US10] Agregar campo cliente opcional y notas; mantener idempotency_key como hidden
- [ ] T098 [US10] Implementar estados: sin agente asignado (empty-state), carga durante envío (loading), éxito con código (confirmation), error de validación (preserva datos)
- [ ] T099 [US10] Verificar responsive en 375/768/1280/1440px; capturar screenshots post-fix en `specs/008-simplify-agent-operations/visual-diffs/register/`

---

## Phase 10: UI pixel-perfect — historial y hoja personal

**Goal**: Historial reproduciendo `historial_de_operaciones/screen.png`. Mapeado a US10 + US4.

- [ ] T100 [P] [US10] Comparar `resources/views/operations/index.blade.php` contra screen.png — documentar columnas bancarias a reemplazar en `specs/008-simplify-agent-operations/visual-diffs/history.md`
- [ ] T101 [US10] Reemplazar columna Banco por Agente; agregar columna Cliente; mantener columnas Fecha/Hora, Tipo, Monto, Estado
- [ ] T102 [US10] Actualizar filtros: sin bank_id/banco; con agent_id (admin), operator_user_id (admin), operation_type_id, status, internal_code, customer_name, date_from/date_to
- [ ] T103 [US10] Actualizar summary cards: Total Operaciones, Monto Bruto, Entradas Efectivo, Salidas Efectivo, Entradas Digital, Salidas Digital, Movimiento Neto — sin iconos escapados (usar `{!! $icon !!}`)
- [ ] T104 [US10] Implementar hoja personal: agrupar por fecha de negocio, mostrar primera/última actividad con fuente (sesión/operación), lista cronológica
- [ ] T105 [US10] Verificar paginación preserva filtros; empty state sin operaciones; responsive en 4 viewports; capturas en `specs/008-simplify-agent-operations/visual-diffs/history/`

---

## Phase 11: UI pixel-perfect — dashboards

**Goal**: Dashboards operador/admin reproduciendo screen.png. Mapeado a US10 + US7.

- [ ] T106 [P] [US10] Comparar `resources/views/reporting/operator-dashboard.blade.php` contra `dashboard_del_operador/screen.png` — documentar desviaciones en `specs/008-simplify-agent-operations/visual-diffs/dashboard-operator.md`
- [ ] T107 [P] [US10] Comparar `resources/views/reporting/admin-dashboard.blade.php` contra `dashboard_administrativo/screen.png` — documentar elementos bancarios a eliminar en `specs/008-simplify-agent-operations/visual-diffs/dashboard-admin.md`
- [ ] T108 [US7] Actualizar `DashboardQueryService` en `app/Modules/Reporting/Services/DashboardQueryService.php` — remover bank_id, store_id de `applyAdminFilters()`; reemplazar store_id→agent_id; remover joins a banks; remover gráfico "Ops by Bank Partner"
- [ ] T109 [US7] Actualizar `DashboardController@operatorDashboard` — métricas con efectivo/digital separados; agente activo visible; sin filtros de banco
- [ ] T110 [US7] Actualizar `DashboardController@adminDashboard` — filtros: agent_id, city, operator_user_id, operation_type_id, status, periodo, time_range (sin bank_id, store_id); rankings por operador/agente; actividad por hora
- [ ] T111 [US10] Actualizar vista operator-dashboard — KPIs: Total ops, Monto bruto, Efectivo in/out, Digital in/out; gráficos distribution y evolution; operaciones recientes
- [ ] T112 [US10] Actualizar vista admin-dashboard — KPIs: agentes activos, operadores activos, total ops, gross; secondary: cash_in/out, digital_in/out, diferencias cierres; rankings; heatmap horario
- [ ] T113 [US10] Actualizar componente `admin-filters.blade.php` — remover selects de banco/tienda; agregar agent, city
- [ ] T114 [US10] Verificar responsive en 4 viewports; capturas en `specs/008-simplify-agent-operations/visual-diffs/dashboards/`

---

## Phase 12: UI pixel-perfect — apertura y cierre

**Goal**: Cierre diario reproduciendo `cierre_operativo_diario/screen.png`. Mapeado a US10 + US6.

- [ ] T115 [P] [US10] Comparar `resources/views/daily-closing/show.blade.php` contra screen.png — documentar desviaciones en `specs/008-simplify-agent-operations/visual-diffs/closing.md`
- [ ] T116 [US10] Actualizar vista — contexto: Agente (no tienda/banco); KPIs con efectivo/digital separados; breakdown por operador y tipo
- [ ] T117 [US10] Implementar warning de diferencias con modal de motivo; indicador visual de estado (ABIERTO/BORRADOR/PRESENTADO/CONFIRMADO/REABIERTO)
- [ ] T118 [US10] Actualizar vista index — filtro por agente; badges de estado; link a detalle
- [ ] T119 [US10] Verificar responsive; capturas en `specs/008-simplify-agent-operations/visual-diffs/closing/`

---

## Phase 13: Eliminación del modelo anterior

**Goal**: Retirar bancos, tiendas, bank_agents y todo código asociado. Mapeado a US1 (migración final).

### Drop Legacy Tables (after data migration validated)

- [ ] T120 [US1] Crear migration `2026_07_23_000009_drop_legacy_tables.php` en `database/migrations/` — droppea FKs operations.store_id→stores, operations.bank_agent_id→bank_agents, daily_closures.store_id→stores, daily_closures.bank_agent_id→bank_agents, operation_types.bank_id→banks, bank_agents.store_id→stores, bank_agents.bank_id→banks; droppea columnas store_id y bank_agent_id de operations y daily_closures; droppea bank_id de operation_types; droppea tablas banks, stores, bank_agents, user_bank_agent_assignments, _migration_map; hace agent_id NOT NULL en operations y daily_closures
- [ ] T121 [US1] Crear test que verifica que las tablas legacy no existen en `tests/Feature/Migration/LegacyTablesRemovedTest.php`

### Remove Legacy Code

- [ ] T122 [P] [US1] Eliminar `app/Modules/BankingNetwork/` completo — modelos Bank, BankAgent, UserBankAgentAssignment; controllers BankController, BankAgentController; policies; requests
- [ ] T123 [P] [US1] Eliminar `app/Modules/Organization/Models/Store.php` y `app/Modules/Organization/Policies/StorePolicy.php`
- [ ] T124 [P] [US1] Eliminar controladores legacy: `StoreController`, `GeoHierarchyController` (solo métodos de stores)
- [ ] T125 [P] [US1] Eliminar requests legacy: `StoreRequest`, `BankRequest`, `BankAgentRequest`, `AssignOperatorRequest`
- [ ] T126 [P] [US1] Eliminar rutas legacy: `routes/banking-network.php` completo; rutas de stores en `routes/organization.php`
- [ ] T127 [P] [US1] Eliminar vistas legacy: `banking-network/`, `organization/stores/`
- [ ] T128 [P] [US1] Eliminar seeders legacy: `OperationalStructureSeeder.php` (banks, stores, bank_agents); reemplazar referencias en `DatabaseSeeder.php`
- [ ] T129 [P] [US1] Eliminar factories legacy: `BankFactory.php`, `StoreFactory.php`, `BankAgentFactory.php`, `UserBankAgentAssignmentFactory.php`; actualizar `OperationFactory.php` para usar Agent en lugar de BankAgent+Store
- [ ] T130 [P] [US1] Actualizar `routes/web.php` — remover requires de `banking-network.php`; agregar require de `agents.php`
- [ ] T131 [P] [US1] Limpiar CSS/JS obsoleto — buscar selectores `.bank-*`, `.store-*`, `.tienda-*` en `resources/css/` y `resources/js/`

---

## Phase 14: Migración y validación de datos

**Goal**: Ejecutar y validar la migración completa contra datos de prueba.

- [ ] T132 Ejecutar migración completa en entorno de prueba — `php artisan migrate:fresh` + seeders nuevos + migraciones de datos
- [ ] T133 [P] Validar conteos — script `php artisan tinker` que compare COUNT de operations/daily_closures/user_agent_assignments post-migración
- [ ] T134 [P] Validar montos — verificar SUM(amount) de operations y SUM(gross_amount) de daily_closures preservados
- [ ] T135 [P] Validar usuarios — verificar que todos los usuarios mantienen organización, rol, status
- [ ] T136 [P] Validar auditoría — verificar audit_logs preservan entity_type/entity_id consistentes con nuevo dominio
- [ ] T137 Probar rollback — ejecutar rollback completo, verificar restauración de estado pre-migración, ejecutar nuevamente

---

## Phase 15: Pruebas integrales

**Goal**: Suite completa de pruebas unitarias, feature e integración.

- [ ] T138 [P] Ejecutar `php artisan test --filter="Agents"` — verificar que todos los tests de Agents pasan
- [ ] T139 [P] Ejecutar `php artisan test --filter="Operations"` — verificar tests de registro, historial, anulación
- [ ] T140 [P] Ejecutar `php artisan test --filter="DailyFunds"` — verificar tests de apertura, cierre, diferencias
- [ ] T141 [P] Ejecutar `php artisan test --filter="Reporting"` — verificar tests de dashboards sin filtros bancarios
- [ ] T142 [P] Ejecutar `php artisan test --filter="IdentityAccess"` — verificar tests de auth, sesión, usuarios sin cambios
- [ ] T143 [P] Ejecutar `php artisan test --filter="Migration"` — verificar tests de integridad de migración y rollback
- [ ] T144 Ejecutar `php artisan test` completo — verificar que el conteo de tests passing no disminuye vs baseline pre-migración (excluyendo tests legacy eliminados)
- [ ] T145 [P] Ejecutar `npm run build` — verificar compilación frontend sin errores
- [ ] T146 [P] Ejecutar validación visual — capturar las 7 pantallas en 4 viewports (28 capturas), comparar con referencias, verificar tolerancia 2px/0.5%

---

## Phase 16: Convergencia y limpieza

**Goal**: Código muerto, rutas demo, documentación, rendimiento, accesibilidad.

- [ ] T147 [P] Buscar y eliminar código muerto — búsqueda de imports/clases no utilizadas, métodos legacy residuales en `app/`
- [ ] T148 [P] Verificar que todas las rutas demo (`/demo/*`) están inaccesibles — test en `tests/Feature/DemoRoutesRemovedTest.php`
- [ ] T149 [P] Eliminar datos dummy residuales — verificar que vistas productivas no contienen "Lorem ipsum", "Test Store", "Tienda Centro"
- [ ] T150 [P] Verificar rendimiento — medición de dashboard admin <3s con 100 agentes/500 ops/100k registros en `tests/Performance/DashboardPerformanceTest.php`
- [ ] T151 [P] Verificar accesibilidad — navegación por teclado en login, formularios, tablas, modales; focus visible; labels asociados; contraste mínimo 4.5:1
- [ ] T152 [P] Actualizar `docs/product-brief.md` — reemplazar referencias a tiendas/bancos/agentes bancarios por Agente
- [ ] T153 [P] Actualizar `docs/deployment.md` — remover referencias a tablas banks/stores/bank_agents del backup strategy
- [ ] T154 [P] Actualizar `README.md` — reflejar nuevo dominio Agente, remover sección "Rutas Demo"
- [ ] T155 Ejecutar quickstart.md completo — validar los 22 pasos del Mandatory Demonstration Flow

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1 (Audit) ─────────────────────────────────────────────────────┐
                                                                      │
Phase 2 (Backup) ────────────────────────────────────────────────────┤
                                                                      │
Phase 3 (Agents) ──── depends on Phase 1 audit ──────────────────────┤
                                                                      │
Phase 4 (Types) ───── depends on Phase 1 audit ──────────────────────┤
                                                                      │
Phase 5 (Operations) ─ depends on Phase 3 (agents) + Phase 4 (types)─┤
                                                                      │
Phase 6 (DailyFunds) ─ depends on Phase 3 (agents) + Phase 5 ────────┤
                                                                      │
Phase 7 (Session) ──── can run in parallel with 3-6 ─────────────────┤
                                                                      │
Phase 8 (UI Login) ── can run after Phase 7 ─────────────────────────┤
                                                                      │
Phase 9 (UI Register) ─ depends on Phase 5 (operations) ─────────────┤
                                                                      │
Phase 10 (UI History) ─ depends on Phase 5 (operations) ─────────────┤
                                                                      │
Phase 11 (UI Dash) ─── depends on Phase 5 + Phase 6 ─────────────────┤
                                                                      │
Phase 12 (UI Closing) ─ depends on Phase 6 ──────────────────────────┤
                                                                      │
Phase 13 (Removal) ─── depends on Phase 5 + Phase 6 data migration ──┤
                                                                      │
Phase 14 (Validation) ─ depends on Phase 13 ─────────────────────────┤
                                                                      │
Phase 15 (Tests) ───── depends on Phase 14 ──────────────────────────┤
                                                                      │
Phase 16 (Cleanup) ─── depends on Phase 15 ──────────────────────────┘
```

### Within each phase: Tests first → Models → Services → Controllers → Views

### Parallel Opportunities

- **Phase 1**: T001–T008 all [P] — different audit files
- **Phase 2**: T010, T011, T013, T014 [P] — different files
- **Phase 3**: T016, T017 [P]; T019, T020 [P]; T023, T024 [P]
- **Phase 5**: T042–T046 [P] — different test files
- **Phase 8-12**: All visual comparison tasks [P] within each phase
- **Phase 13**: T122–T131 all [P] — different directories/files
- **Phase 15**: T138–T143, T145–T146 [P] — different test suites
- **Phase 16**: T147–T154 [P] — different files

## Implementation Strategy

### MVP First (Phase 1-5 + Phase 7)

Minimum: audit system → backup → agents CRUD → operation types → operations with agent context → session timer. This gives a working system where operators can register operations in an agent.

### Incremental Delivery

1. Phases 1-2 → Audit complete, backup strategy in place
2. + Phase 3 → Agents CRUD and assignments functional
3. + Phase 4 → Operation types with multipliers
4. + Phase 5 → Operations with agent context, internal codes, idempotency
5. + Phase 7 → Session timer and expiry modal
6. + Phase 6 → Daily opening/closing with cash/digital
7. + Phases 8-12 → Pixel-perfect UI for all screens
8. + Phase 13 → Legacy removal
9. + Phases 14-15 → Migration validation + full test suite
10. + Phase 16 → Cleanup, docs, performance, accessibility

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific user story from spec.md
- Each phase should verify `php artisan test` filtered to its domain before advancing
- Commit atómico por tarea o grupo lógico
- Los tests de migración (Fase 14-15) no deben ejecutarse en producción sin backup previo
- **Column renames deferred**: `observation→notes`, `annulled_*→voided_*` no se ejecutaron en Phase 5 por incompatibilidad SQLite. Se completan en Phase 13 (T172/000009 migration). El modelo `Operation` acepta ambos nombres en `$fillable` durante la coexistencia.
- **Field name mapping**: `effective_at` (DB column) corresponde a `occurred_at` en spec.md FR-008. Se usa `effective_at` como nombre canónico en código.

---

## Phase 17: Convergence (gap remediation)

**Goal**: Resolver las brechas detectadas por `/speckit.converge` entre la implementación actual y los artefactos de especificación, plan y tareas. Las tareas se ordenan por severidad: CRITICAL/HIGH primero.

- [X] T156 [US1] Crear migration `2026_07_23_000006_migrate_operations_data.php` — UPDATE operations.agent_id desde bank_agent_id vía _migration_map; poblar internal_code con formato OP-LEGACY-{id} (columna nullable inicialmente); calcular cash_delta desde operation_types.cash_multiplier × amount; digital_delta=0 para históricos; customer_name=NULL per FR-033 (missing)
- [X] T157 [US1] Crear migration `2026_07_23_000008_migrate_daily_closures_data.php` — UPDATE daily_closures.agent_id desde bank_agent_id vía _migration_map; poblar opening_cash=0, opening_digital=0 per plan §Fase 4; expected/actual defaults a 0 per FR-033 (missing)
- [X] T158 [US6] Reescribir `DailyClosingController` en `app/Modules/DailyClosing/Http/Controllers/DailyClosingController.php` — reemplazar todas las referencias a bank_agent_id por agent_id; index: filtrar por agent_id (no bank_agent_id); create: seleccionar agente con asignación activa; store: crear apertura BORRADOR con opening_cash/opening_digital; show: calcular esperados y diferencias vía CalculateClosing; confirm: admin confirma con warning si diferencias; reopen: admin reabre con motivo per BR-027–BR-029 (partial)
- [X] T159 [US6] Actualizar `DailyClosingPolicy` en `app/Modules/DailyClosing/Policies/DailyClosingPolicy.php` — reemplazar bank_agent_id por agent_id en view/generate/confirm/reopen; confirmar solo ADMIN con misma organización per BR-019 (partial)
- [X] T160 [US6] Crear `CalculateClosing` action en `app/Modules/DailyClosing/Application/Actions/CalculateClosing.php` — sumar operaciones activas del agente/fecha; calcular gross, cash_in/out, digital_in/out desde cash_delta/digital_delta snapshots; esperados = opening + in − out; diferencias = actual − esperado per FR-019–FR-020 (missing)
- [X] T161 [US3] Reescribir `tests/Feature/Operations/OperationRegistrationTest.php` — usar Agent domain: crear con agente activo, verificar internal_code, autor, cash_delta/digital_delta snapshots, idempotencia per US3 AC1–AC4 (partial)
- [X] T162 [US3] Reescribir `tests/Feature/Operations/OperationValidationTest.php` — monto ≤0, tipo inactivo, agente no autorizado, operador sin asignación per US3 AC3 (partial)
- [X] T163 [US4] Reescribir `tests/Feature/Operations/OperationHistoryTest.php` — filtros con agent_id, paginación 25, operador ve solo propio, admin ve todos per US4 AC1–AC3 (partial)
- [X] T164 [US3] Reescribir `tests/Feature/Operations/OperationRetroactiveTest.php` — operador rechazado con fecha pasada, admin aceptado dentro de ventana 24h per FR-009a/BR-031 (partial)
- [X] T165 [US8] Reescribir `tests/Feature/Operations/OperationAnnulmentTest.php` — reemplazar bank_agent_id por agent_id; verificar admin anula con motivo, doble anulación rechazada, auditoría per US8 AC1–AC2 (partial)
- [X] T166 [US6] Crear `tests/Feature/DailyFunds/OpeningTest.php` — admin registra efectivo/digital inicial, no duplicado, confirmación, auditoría per US6 AC1–AC2 (missing)
- [X] T167 [US6] Crear `tests/Feature/DailyFunds/ClosingTest.php` — calcular esperados, ingresar reales, diferencias, warning, confirmación, reapertura per US6 AC3–AC6 (missing)
- [X] T168 [US6] Crear `tests/Feature/DailyFunds/ClosingAuthorizationTest.php` — solo admin confirma/reabre, operador prepara/presenta per US6 AC5–AC6 (missing)
- [X] T169 [US6] Crear `tests/Feature/DailyFunds/ClosingDifferencesTest.php` — confirmar con diferencias, motivo obligatorio, warning visible per BR-032 (missing)
- [X] T170 [US3] Actualizar `RegisterOperation.execute()` en `app/Modules/Operations/Application/Actions/RegisterOperation.php` — agregar lógica retroactiva: operador siempre usa now(); admin puede establecer effective_at pasado dentro de ventana configurable 24h per FR-009a/BR-031 (missing)
- [X] T171 [US7] Actualizar `DashboardQueryService` en `app/Modules/Reporting/Services/DashboardQueryService.php` — remover bank_id/store_id/bank_agent_id de `applyAdminFilters()`; reemplazar store_id→agent_id; remover joins a banks/bank_agents; remover gráfico "Ops by Bank Partner" per FR-023 (partial)
- [X] T172 [US1] Crear migration `2026_07_23_000009_drop_legacy_tables.php` — droppear FKs legacy; droppear columnas store_id/bank_agent_id de operations/daily_closures; droppear bank_id de operation_types; droppear tablas banks, stores, bank_agents, user_bank_agent_assignments, _migration_map; hacer agent_id NOT NULL en operations/daily_closures; ejecutar renames observation→notes, annulled_*→voided_* per plan §Fase 5 (missing)
- [X] T173 [US1] Crear `tests/Feature/Migration/OperationsMigrationTest.php` — verificar todas las operaciones tienen agent_id NOT NULL, internal_code único, cash_delta/digital_delta poblados per SC-002 (missing)
- [X] T174 [US1] Crear `tests/Feature/Migration/ClosuresMigrationTest.php` — verificar closures tienen agent_id NOT NULL, business_date/status preservados per SC-002 (missing)
- [X] T175 [US9] Conectar session-timer.js — importar `resources/js/identity-access/session-timer.js` en `resources/js/app.js`; alinear IDs del modal (`#session-expiry-modal`, `#continue-session`, `#end-session`) con el script; verificar que el modal se renderiza en `layouts/authenticated.blade.php` per FR-026–FR-027 (partial)
- [X] T176 [US6] Crear `tests/Feature/DailyFunds/ClosingPrecisionTest.php` — verificar cálculos de cierre al centavo: opening + cash_in − cash_out = expected_closing_cash; actual − expected = difference; probar con decimales límite per SC-005 (missing)

---

## Phase 18: Convergence (second pass)

**Goal**: Cerrar brechas detectadas por el segundo pase de `/speckit.converge` tras la remediación de Phase 17. Tareas ordenadas por severidad.

- [X] T177 [US9] Crear `tests/Feature/Session/SessionTimerTest.php` — verificar contador usa vencimiento del servidor, se recalcula al recuperar visibilidad per SC-009/US9 AC1 (missing)
- [X] T178 [US9] Crear `tests/Feature/Session/SessionModalTest.php` — verificar modal a 30s, continuar→refresh rotado, cerrar→logout revocatorio per SC-009/US9 AC2 (missing)
- [X] T179 [US9] Crear `tests/Feature/Session/SessionExpiryTest.php` — verificar token vencido rechaza peticiones, redirige a login, limpia estado per SC-009/US9 AC3 (missing)
- [X] T180 [US1] Crear `tests/Feature/Migration/LegacyTablesRemovedTest.php` — verificar tablas banks/stores/bank_agents/user_bank_agent_assignments/_migration_map no existen per FR-030/SC-001 (missing)
- [X] T181 [FR-034] Crear `tests/Feature/DemoRoutesRemovedTest.php` — verificar rutas demo retornan 404 o no existen per FR-034 (missing)
- [X] T182 [SC-008] Crear `tests/Performance/DashboardPerformanceTest.php` — verificar dashboard admin <3s con 100 agentes/500 ops/100k registros per SC-008 (missing)
- [X] T183 [FR-028] Completar vista `resources/views/operations/create.blade.php` per T059 — mostrar agente activo, campos: tipo, monto, cliente opcional, notas; remover referencias bank_agent_id/store (partial)

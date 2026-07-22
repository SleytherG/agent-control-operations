---
description: "Tareas de implementación para administración de estructura operacional"
---

# Tasks: Administración de Estructura Operacional

**Input**: Design documents from `/specs/002-operational-structure/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md; feature 001-auth-session completamente implementada

**Tests**: Pruebas de autorización positiva/negativa, integridad de asignaciones, reglas de negocio y desactivación lógica para cada entidad.

**Organization**: Las tareas se agrupan por historia para permitir validación y entrega incremental.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: puede ejecutarse en paralelo (archivos distintos, sin dependencias).
- **[Story]**: identifica la historia `US1`–`US6`.
- Toda tarea incluye rutas exactas.

## Phase 1: Setup

**Purpose**: Crear la estructura de módulos y directorios nuevos.

- [X] T001 Crear estructura de módulos en app/Modules/Organization/Models/, Http/Controllers/, Http/Requests/, Policies/ y app/Modules/BankingNetwork/Models/, Http/Controllers/, Http/Requests/, Policies/
- [X] T002 [P] Crear directorios de recursos en resources/views/organization/, resources/views/banking-network/ y resources/views/identity-access/operators/
- [X] T003 [P] Crear directorios de tests en tests/Feature/Organization/, tests/Feature/BankingNetwork/ y tests/Feature/IdentityAccess/ para operadores

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Migraciones, modelos base, enums y rutas que bloquean todas las historias.

**CRITICAL**: Ninguna historia comienza hasta completar esta fase.

- [X] T004 Crear migración reversible de regions en database/migrations/2026_07_22_000007_create_regions_table.php
- [X] T005 Crear migración reversible de provinces en database/migrations/2026_07_22_000008_create_provinces_table.php
- [X] T006 Crear migración reversible de districts en database/migrations/2026_07_22_000009_create_districts_table.php
- [X] T007 Crear migración reversible de stores en database/migrations/2026_07_22_000010_create_stores_table.php
- [X] T008 Crear migración reversible de banks en database/migrations/2026_07_22_000011_create_banks_table.php
- [X] T009 Crear migración reversible de bank_agents en database/migrations/2026_07_22_000012_create_bank_agents_table.php
- [X] T010 Crear migración reversible de user_bank_agent_assignments en database/migrations/2026_07_22_000013_create_user_bank_agent_assignments_table.php
- [X] T011 Crear migración para columna password_changed_at en users en database/migrations/2026_07_22_000014_add_password_changed_at_to_users_table.php
- [X] T012 [P] Implementar modelos Region, Province, District en app/Modules/Organization/Models/
- [X] T013 [P] Implementar modelo Store en app/Modules/Organization/Models/Store.php
- [X] T014 [P] Implementar modelos Bank, BankAgent, UserBankAgentAssignment en app/Modules/BankingNetwork/Models/
- [X] T015 [P] Crear factories en database/factories/Organization/ y database/factories/BankingNetwork/
- [X] T016 Crear seeder de datos geográficos, bancos y tienda de ejemplo en database/seeders/OperationalStructureSeeder.php
- [X] T017 Registrar rutas de Organization y BankingNetwork en routes/web.php creando routes/organization.php y routes/banking-network.php

**Checkpoint**: Las 8 migraciones corren en MySQL/MariaDB. Los modelos base existen.

---

## Phase 3: User Story 2 - Registrar y administrar tiendas (Priority: P1)

**Goal**: CRUD administrativo de tiendas con pertenencia a distrito, desactivación lógica y protección contra eliminación de tiendas con agentes.

**Independent Test**: Crear, editar, desactivar tiendas como administrador; verificar que operador no accede.

### Tests for User Story 2 (REQUIRED)

- [X] T018 [P] [US2] Probar AC1 creación de tienda con distrito activo en tests/Feature/Organization/StoreCreateTest.php
- [X] T019 [P] [US2] Probar AC2 edición de tienda y auditoría en tests/Feature/Organization/StoreUpdateTest.php
- [X] T020 [P] [US2] Probar AC3 desactivación de tienda sin agentes en tests/Feature/Organization/StoreDeactivateTest.php
- [X] T021 [P] [US2] Probar AC4 rechazo de eliminación de tienda con agentes en tests/Feature/Organization/StoreDeleteProtectionTest.php
- [X] T022 [P] [US2] Probar AC5 operador solo ve tiendas con agentes asignados en tests/Feature/Organization/StoreOperatorViewTest.php

### Implementation for User Story 2

- [X] T023 [P] [US2] Implementar StorePolicy con permisos de administrador en app/Modules/Organization/Policies/StorePolicy.php
- [X] T024 [P] [US2] Implementar StoreRequest para create/update en app/Modules/Organization/Http/Requests/StoreRequest.php
- [X] T025 [US2] Implementar CRUD de tiendas en app/Modules/Organization/Http/Controllers/StoreController.php
- [X] T026 [US2] Implementar vista Blade de listado con filtros en resources/views/organization/stores/index.blade.php
- [X] T027 [P] [US2] Implementar vista Blade de formulario create/edit en resources/views/organization/stores/form.blade.php

**Checkpoint**: El administrador gestiona tiendas; el operador no accede a administración.

---

## Phase 4: User Story 4 - Registrar y administrar agentes bancarios (Priority: P1)

**Goal**: CRUD de agentes con pertenencia a tienda y banco. Desactivación termina asignaciones activas automáticamente.

**Independent Test**: Crear, editar, desactivar agentes; verificar terminación de asignaciones y vista restringida del operador.

### Tests for User Story 4 (REQUIRED)

- [X] T028 [P] [US4] Probar AC1 creación de agente con tienda y banco activos en tests/Feature/BankingNetwork/BankAgentCreateTest.php
- [X] T029 [P] [US4] Probar AC2 edición de agente y auditoría en tests/Feature/BankingNetwork/BankAgentUpdateTest.php
- [X] T030 [P] [US4] Probar AC3 desactivación con terminación de asignaciones en tests/Feature/BankingNetwork/BankAgentDeactivateTest.php
- [X] T031 [P] [US4] Probar AC4 operador solo ve agentes asignados activos en tests/Feature/BankingNetwork/BankAgentOperatorViewTest.php
- [X] T032 [P] [US4] Probar AC5 filtros administrativos por tienda, banco y estado en tests/Feature/BankingNetwork/BankAgentFilterTest.php

### Implementation for User Story 4

- [X] T033 [P] [US4] Implementar BankAgentPolicy en app/Modules/BankingNetwork/Policies/BankAgentPolicy.php
- [X] T034 [P] [US4] Implementar BankAgentRequest en app/Modules/BankingNetwork/Http/Requests/BankAgentRequest.php
- [X] T035 [US4] Implementar CRUD y desactivación con terminación de asignaciones en app/Modules/BankingNetwork/Http/Controllers/BankAgentController.php
- [X] T036 [US4] Implementar vista Blade de listado con filtros en resources/views/banking-network/agents/index.blade.php
- [X] T037 [P] [US4] Implementar vista Blade de formulario en resources/views/banking-network/agents/form.blade.php

**Checkpoint**: El administrador gestiona agentes; la desactivación termina asignaciones.

---

## Phase 5: User Story 5 - Registrar operadores y asignarlos a agentes (Priority: P1)

**Goal**: Registro de operadores con contraseña inicial, asignación/desasignación con historial, rechazo de solapamiento, cambio forzado de contraseña.

**Independent Test**: Crear operador, asignar a agentes, verificar primer login fuerza cambio, desactivar.

### Tests for User Story 5 (REQUIRED)

- [X] T038 [P] [US5] Probar AC1 creación de operador en tests/Feature/IdentityAccess/OperatorRegistrationTest.php
- [X] T039 [P] [US5] Probar AC2 asignación a agentes en tests/Feature/BankingNetwork/UserBankAgentAssignmentTest.php
- [X] T040 [P] [US5] Probar AC3 desasignación e historial en tests/Feature/BankingNetwork/AssignmentHistoryTest.php
- [X] T041 [P] [US5] Probar AC4 desactivación de operador en tests/Feature/IdentityAccess/OperatorDeactivationTest.php
- [X] T042 [P] [US5] Probar solapamiento de asignaciones en tests/Feature/BankingNetwork/OverlappingAssignmentTest.php
- [X] T043 [P] [US5] Probar cambio forzado de contraseña en primer login en tests/Feature/IdentityAccess/ForcePasswordChangeTest.php

### Implementation for User Story 5

- [X] T044 [P] [US5] Extender UserPolicy con permisos de administrador para gestión de operadores en app/Modules/IdentityAccess/Policies/UserPolicy.php
- [X] T045 [P] [US5] Implementar CreateOperatorRequest en app/Modules/IdentityAccess/Http/Requests/CreateOperatorRequest.php
- [X] T046 [US5] Implementar registro de operador con password_changed_at null en app/Modules/IdentityAccess/Http/Controllers/OperatorController.php
- [X] T047 [P] [US5] Implementar AssignOperatorRequest y lógica de solapamiento en app/Modules/BankingNetwork/Http/Requests/AssignOperatorRequest.php
- [X] T048 [US5] Implementar asignación/desasignación con historial en app/Modules/BankingNetwork/Http/Controllers/UserBankAgentAssignmentController.php
- [X] T049 [US5] Implementar cambio forzado de contraseña en app/Modules/IdentityAccess/Http/Controllers/PasswordChangeController.php
- [X] T050 [US5] Extender AuthenticateJwtSession middleware para redirigir a /password/change si password_changed_at es null en app/Http/Middleware/AuthenticateJwtSession.php
- [X] T051 [P] [US5] Crear vista Blade de lista de operadores en resources/views/identity-access/operators/index.blade.php
- [X] T052 [P] [US5] Crear vista Blade de formulario de operador en resources/views/identity-access/operators/form.blade.php
- [X] T053 [P] [US5] Crear vista Blade de cambio de contraseña en resources/views/identity-access/password-change.blade.php

**Checkpoint**: Operadores creados, asignados, cambio de contraseña forzado en primer login.

---

## Phase 6: User Story 6 - Ver agentes asignados como operador (Priority: P1)

**Goal**: El operador consulta solo sus agentes activos asignados. Sin acceso a otros ni a administración.

**Independent Test**: Autenticarse como operador, consultar agentes, manipular parámetros.

### Tests for User Story 6 (REQUIRED)

- [X] T054 [P] [US6] Probar AC1 lista de agentes propios en tests/Feature/BankingNetwork/OperatorMyAgentsTest.php
- [X] T055 [P] [US6] Probar AC2 lista vacía sin asignaciones en tests/Feature/BankingNetwork/OperatorNoAgentsTest.php
- [X] T056 [P] [US6] Probar AC3 parámetros manipulados no devuelven agentes ajenos en tests/Feature/BankingNetwork/OperatorAgentIsolationTest.php

### Implementation for User Story 6

- [X] T057 [US6] Implementar endpoint /my-agents con scope de asignaciones activas en app/Modules/BankingNetwork/Http/Controllers/MyAgentsController.php
- [X] T058 [P] [US6] Crear vista Blade de agentes del operador en resources/views/banking-network/my-agents.blade.php

**Checkpoint**: El operador ve exclusivamente sus agentes activos asignados.

---

## Phase 7: User Story 1 - Registrar y mantener referencias geográficas (Priority: P2)

**Goal**: CRUD de regiones, provincias y distritos. Desactivación lógica. Sin validación geográfica oficial.

**Independent Test**: Crear jerarquía completa como administrador; verificar que operador no accede.

### Tests for User Story 1 (REQUIRED)

- [X] T059 [P] [US1] Probar AC1-AC4 CRUD de regiones, provincias y distritos en tests/Feature/Organization/GeoHierarchyTest.php
- [X] T060 [P] [US1] Probar AC5 operador rechazado en administración geográfica en tests/Feature/Organization/GeoHierarchyAuthorizationTest.php

### Implementation for User Story 1

- [X] T061 [P] [US1] Implementar RegionPolicy, ProvincePolicy, DistrictPolicy en app/Modules/Organization/Policies/
- [X] T062 [P] [US1] Implementar Form Requests para cada entidad geográfica en app/Modules/Organization/Http/Requests/
- [X] T063 [US1] Implementar CRUD anidado de regiones, provincias y distritos en app/Modules/Organization/Http/Controllers/GeoHierarchyController.php
- [X] T064 [P] [US1] Crear vistas Blade de listado y formulario para cada nivel en resources/views/organization/geo/

**Checkpoint**: El administrador gestiona la jerarquía geográfica completa.

---

## Phase 8: User Story 3 - Registrar y administrar bancos (Priority: P2)

**Goal**: CRUD de bancos con código único. Desactivación lógica.

**Independent Test**: Crear, editar, desactivar bancos; verificar unicidad de código.

### Tests for User Story 3 (REQUIRED)

- [X] T065 [P] [US3] Probar AC1-AC3 CRUD de bancos y unicidad de código en tests/Feature/BankingNetwork/BankTest.php

### Implementation for User Story 3

- [X] T066 [P] [US3] Implementar BankPolicy en app/Modules/BankingNetwork/Policies/BankPolicy.php
- [X] T067 [P] [US3] Implementar BankRequest en app/Modules/BankingNetwork/Http/Requests/BankRequest.php
- [X] T068 [US3] Implementar CRUD de bancos en app/Modules/BankingNetwork/Http/Controllers/BankController.php
- [X] T069 [P] [US3] Crear vistas Blade de listado y formulario en resources/views/banking-network/banks/

**Checkpoint**: El administrador gestiona bancos con código único.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Cerrar auditoría, validaciones compartidas y despliegue.

- [X] T070 [P] Implementar registro de auditoría en cada acción de mutación de entidad estructural en acciones compartidas o controladores
- [X] T071 [P] Probar migraciones up/down de las 8 tablas nuevas en tests/Integration/Migrations/OperationalStructureMigrationsTest.php
- [X] T072 [P] Probar escenarios del quickstart en tests/Feature/OperationalStructureQuickstartTest.php
- [X] T073 Actualizar docs/deployment.md con las nuevas tablas en la estrategia de backup
- [X] T074 Ejecutar php artisan migrate --seed y verificar datos de ejemplo en SQLite de desarrollo

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: inicia de inmediato.
- **Foundational (Phase 2)**: depende de Setup y bloquea todas las historias.
- **US2 (Phase 3)**: P1. Depende de Foundational (stores necesita districts y regions/provinces de US1 vía migraciones, pero las migraciones ya están en Foundational).
- **US4 (Phase 4)**: P1. Depende de stores y banks (US2 y US3 vía migraciones ya en Foundational).
- **US5 (Phase 5)**: P1. Depende de bank_agents (US4) para asignaciones y de users existente.
- **US6 (Phase 6)**: P1. Depende de asignaciones (US5).
- **US1 (Phase 7)**: P2. Migraciones ya en Foundational; puede correr en cualquier momento después de Phase 2.
- **US3 (Phase 8)**: P2. Migraciones ya en Foundational; puede correr en cualquier momento después de Phase 2.
- **Polish (Phase 9)**: depende de todas las historias.

### Recommended MVP Scope

US2 (tiendas) + US4 (agentes) + US5 (operadores y asignaciones) + US6 (vista operador). US1 y US3 (geografía y bancos) pueden posponerse si el seed inicial cubre los valores mínimos.

### Parallel Opportunities

- T012–T015 (modelos) son independientes después de migraciones.
- T018–T022 (pruebas US2) pueden escribirse juntas antes de implementar.
- US1 (Phase 7) y US3 (Phase 8) pueden ejecutarse en paralelo con US2–US6 después de Foundational.
- T070–T074 se distribuyen por archivos distintos después de todas las historias.

---

## Notes

- El proyecto Laravel 13 y la autenticación ya existen de 001-auth-session. No se repiten tareas de scaffold.
- Las migraciones usan timestamps secuenciales desde 000007 para no colisionar con las 6 de 001-auth-session.
- Los índices parciales para solapamiento requieren MySQL 8.0+ o MariaDB con columnas virtuales.
- Las vistas Blade reutilizan el layout authenticated.blade.php existente.

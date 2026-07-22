---
description: "Tareas de implementación para registro de operaciones"
---

# Tasks: Registro de Operaciones

**Input**: Design documents from `/specs/003-operations-registry/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md; features 001 y 002 completamente implementadas

**Tests**: Autorización positiva/negativa, precisión decimal, anulación, idempotencia, ventana retroactiva.

**Organization**: Las tareas se agrupan por historia.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: puede ejecutarse en paralelo (archivos distintos, sin dependencias).
- **[Story]**: identifica la historia `US1`–`US4`.

## Phase 1: Setup

**Purpose**: Crear estructura del módulo Operations y configuración.

- [X] T001 Crear estructura del módulo en app/Modules/Operations/Models/, Http/Controllers/, Http/Requests/, Policies/ y Application/Actions/
- [X] T002 [P] Crear directorios de vistas en resources/views/operations/ y tests en tests/Feature/Operations/
- [X] T003 Crear archivo de configuración config/operations.php con retroactive_window_hours, annulment_window_hours y default_currency

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Migraciones, modelos y rutas que bloquean todas las historias.

**CRITICAL**: Ninguna historia comienza hasta completar esta fase.

- [X] T004 Crear migración reversible de operation_types en database/migrations/2026_07_22_000015_create_operation_types_table.php
- [X] T005 Crear migración reversible de operations con idempotency_key UNIQUE en database/migrations/2026_07_22_000016_create_operations_table.php
- [X] T006 [P] Implementar modelo OperationType en app/Modules/Operations/Models/OperationType.php
- [X] T007 [P] Implementar modelo Operation con casts DECIMAL, estados y relaciones en app/Modules/Operations/Models/Operation.php
- [X] T008 [P] Crear factories en database/factories/Operations/OperationTypeFactory.php y database/factories/Operations/OperationFactory.php
- [X] T009 Crear seeder de tipos de operación (Depósito, Retiro, Consulta, Pago, Transferencia) para bancos y generales en database/seeders/OperationTypeSeeder.php
- [X] T010 Registrar rutas de Operations en routes/operations.php y requerir desde routes/web.php

**Checkpoint**: Las 2 migraciones corren. Los modelos existen con factories y seed.

---

## Phase 3: User Story 2 - Registrar una operación (Priority: P1)

**Goal**: El operador registra operaciones en agentes asignados activos con monto > 0, idempotencia y ventana retroactiva.

**Independent Test**: Registrar con datos válidos, agente no asignado, monto inválido, fecha fuera de ventana, doble envío.

### Tests for User Story 2 (REQUIRED)

- [X] T011 [P] [US2] Probar AC1 registro válido en tests/Feature/Operations/OperationRegistrationTest.php
- [X] T012 [P] [US2] Probar AC2 registro en agente no asignado en tests/Feature/Operations/OperationAgentAssignmentTest.php
- [X] T013 [P] [US2] Probar AC3 monto cero o negativo en tests/Feature/Operations/OperationAmountValidationTest.php
- [X] T014 [P] [US2] Probar AC4 fecha retroactiva fuera de ventana en tests/Feature/Operations/OperationEffectiveDateTest.php
- [X] T015 [P] [US2] Probar AC5 fecha futura rechazada en tests/Feature/Operations/OperationFutureDateTest.php
- [X] T016 [P] [US2] Probar AC6 doble envío con mismo idempotency_key en tests/Feature/Operations/OperationIdempotencyTest.php
- [X] T017 [P] [US2] Probar AC7 usuario registrador no modificable desde formulario en tests/Feature/Operations/OperationUserImmutabilityTest.php

### Implementation for User Story 2

- [X] T018 [P] [US2] Implementar OperationPolicy con permisos de registro y consulta en app/Modules/Operations/Policies/OperationPolicy.php
- [X] T019 [P] [US2] Implementar RegisterOperationRequest con validación de monto, fecha, agente, idempotency_key en app/Modules/Operations/Http/Requests/RegisterOperationRequest.php
- [X] T020 [US2] Implementar RegisterOperation action con validación de asignación activa en app/Modules/Operations/Application/Actions/RegisterOperation.php
- [X] T021 [US2] Implementar OperationController con create y store en app/Modules/Operations/Http/Controllers/OperationController.php
- [X] T022 [P] [US2] Crear formulario Blade de registro con idempotency token en resources/views/operations/create.blade.php
- [X] T023 [P] [US2] Crear vista de confirmación post-registro en resources/views/operations/confirmation.blade.php

**Checkpoint**: El operador registra operaciones con idempotencia. El servidor rechaza agentes no asignados, montos inválidos y fechas fuera de ventana.

---

## Phase 4: User Story 3 - Consultar historial de operaciones (Priority: P1)

**Goal**: Operador ve sus operaciones paginadas. Administrador ve todas con filtros. Operaciones anuladas visibles.

**Independent Test**: Consultar con ambos roles, filtrar, verificar paginación, manipular parámetros.

### Tests for User Story 3 (REQUIRED)

- [X] T024 [P] [US3] Probar AC1 historial del operador en tests/Feature/Operations/OperationHistoryOperatorTest.php
- [X] T025 [P] [US3] Probar AC2 historial del administrador con filtros en tests/Feature/Operations/OperationHistoryAdminTest.php
- [X] T026 [P] [US3] Probar AC3 manipulación de filtros por operador en tests/Feature/Operations/OperationHistoryAuthorizationTest.php
- [X] T027 [P] [US3] Probar AC4 operación anulada visible en historial en tests/Feature/Operations/OperationAnnulledVisibilityTest.php

### Implementation for User Story 3

- [X] T028 [US3] Implementar ListOperations con scopes Eloquent, filtros y paginación en app/Modules/Operations/Application/Actions/ListOperations.php
- [X] T029 [US3] Implementar index y show en OperationController con políticas en app/Modules/Operations/Http/Controllers/OperationController.php
- [X] T030 [P] [US3] Crear vista Blade de historial con filtros y paginación en resources/views/operations/index.blade.php
- [X] T031 [P] [US3] Crear vista Blade de detalle de operación en resources/views/operations/show.blade.php

**Checkpoint**: Cada rol ve solo lo autorizado. Filtros y paginación funcionan. Anuladas visibles.

---

## Phase 5: User Story 1 - Administrar catálogo de tipos de operación (Priority: P2)

**Goal**: Administrador mantiene el catálogo. Unicidad por banco/global. Desactivación lógica.

**Independent Test**: Crear, editar, desactivar tipos; verificar unicidad; operador rechazado.

### Tests for User Story 1 (REQUIRED)

- [X] T032 [P] [US1] Probar AC1-AC3 CRUD de tipos y unicidad en tests/Feature/Operations/OperationTypeTest.php
- [X] T033 [P] [US1] Probar AC4 operador rechazado en tests/Feature/Operations/OperationTypeAuthorizationTest.php

### Implementation for User Story 1

- [X] T034 [P] [US1] Implementar OperationTypePolicy en app/Modules/Operations/Policies/OperationTypePolicy.php
- [X] T035 [P] [US1] Implementar OperationTypeRequest con validación de unicidad en app/Modules/Operations/Http/Requests/OperationTypeRequest.php
- [X] T036 [US1] Implementar CRUD de tipos en app/Modules/Operations/Http/Controllers/OperationTypeController.php
- [X] T037 [P] [US1] Crear vistas Blade de listado y formulario en resources/views/operations/types/

**Checkpoint**: El administrador gestiona tipos. El operador no accede.

---

## Phase 6: User Story 4 - Anular una operación (Priority: P2)

**Goal**: Administrador anula cualquier operación. Operador anula las propias dentro de ventana. Conservación de valor original y trazabilidad.

**Independent Test**: Anular como operador (dentro/fuera de ventana), como administrador, operación ajena, ya anulada.

### Tests for User Story 4 (REQUIRED)

- [X] T038 [P] [US4] Probar AC1 anulación por operador dentro de ventana en tests/Feature/Operations/OperationAnnulmentTest.php
- [X] T039 [P] [US4] Probar AC2 anulación fuera de ventana rechazada en tests/Feature/Operations/OperationAnnulmentWindowTest.php
- [X] T040 [P] [US4] Probar AC3 anulación por administrador sin restricción en tests/Feature/Operations/OperationAdminAnnulmentTest.php
- [X] T041 [P] [US4] Probar AC4 operador no anula operación ajena en tests/Feature/Operations/OperationAnnulmentAuthorizationTest.php
- [X] T042 [P] [US4] Probar AC5 doble anulación rechazada en tests/Feature/Operations/OperationDoubleAnnulmentTest.php

### Implementation for User Story 4

- [X] T043 [P] [US4] Implementar AnnulOperationRequest con validación de motivo y ventana en app/Modules/Operations/Http/Requests/AnnulOperationRequest.php
- [X] T044 [US4] Implementar AnnulOperation action con auditoría en app/Modules/Operations/Application/Actions/AnnulOperation.php
- [X] T045 [US4] Implementar endpoint de anulación en OperationController con políticas en app/Modules/Operations/Http/Controllers/OperationController.php
- [X] T046 [P] [US4] Crear modal o vista de confirmación de anulación en resources/views/operations/annul.blade.php

**Checkpoint**: Anulación con trazabilidad completa. Ventana y autorización impuestas en servidor.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Auditoría, precisión decimal, totales y validación final.

- [X] T047 [P] Implementar registro de auditoría en RegisterOperation y AnnulOperation en app/Modules/Operations/Application/Actions/
- [X] T048 [P] Probar precisión decimal con 10k operaciones en tests/Feature/Operations/OperationDecimalPrecisionTest.php
- [X] T049 [P] Probar migraciones up/down en tests/Integration/Migrations/OperationsMigrationsTest.php
- [X] T050 [P] Probar escenarios del quickstart en tests/Feature/Operations/OperationsQuickstartTest.php
- [X] T051 Ejecutar php artisan migrate --seed y verificar seed de tipos en SQLite

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: inicia de inmediato.
- **Foundational (Phase 2)**: depende de Setup, bloquea todas las historias.
- **US2 (Phase 3)**: P1. Depende de Foundational.
- **US3 (Phase 4)**: P1. Depende de Foundational (modelo Operation). Puede correr en paralelo con US2.
- **US1 (Phase 5)**: P2. Depende de Foundational. Puede correr en cualquier momento.
- **US4 (Phase 6)**: P2. Depende de US2 (operation model + registro).
- **Polish (Phase 7)**: depende de todas las historias.

### MVP Scope

US2 (registro) + US3 (historial). US1 (tipos) puede cubrirse con seed inicial. US4 (anulación) puede posponerse.

### Parallel Opportunities

- T006–T008 (modelos y factories) son independientes.
- T011–T017 (pruebas US2) pueden escribirse juntas antes de implementar.
- US1 (Phase 5) puede ejecutarse en paralelo con US2/US3 después de Foundational.
- US3 (Phase 4) puede ejecutarse en paralelo con US2 (Phase 3).
- T047–T050 se distribuyen por archivos distintos después de todas las historias.

---

## Notes

- El proyecto ya tiene Laravel 13, autenticación (001) y estructura operacional (002). No se repiten tareas de scaffold.
- Las migraciones usan timestamps 000015 y 000016 para no colisionar.
- `idempotency_key` UNIQUE es la defensa principal contra doble envío; no depende de JavaScript.
- Los totales agregados (COUNT, SUM) se implementan en queries SQL, no en colecciones Eloquent.
- Las vistas Blade reutilizan el layout `authenticated.blade.php`.

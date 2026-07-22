---
description: "Tareas de implementación para cierre operativo diario"
---

# Tasks: Cierre Operativo Diario

**Input**: Design documents from `/specs/005-daily-closing/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md; features 001-003 completamente implementadas

**Tests**: Estado, autorización, bloqueo post-confirmación, advertencia POR_CONFIRMAR.

**Organization**: Tareas agrupadas por historia.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

- [X] T001 Crear estructura del módulo en app/Modules/DailyClosing/Models/, Http/Controllers/, Http/Requests/, Policies/
- [X] T002 [P] Crear directorios de vistas en resources/views/daily-closing/ y tests en tests/Feature/DailyClosing/

## Phase 2: Foundational (Blocking Prerequisites)

**CRITICAL**: Ninguna historia comienza hasta completar esta fase.

- [X] T003 Crear migración reversible de daily_closures con unique parcial en database/migrations/2026_07_22_000017_create_daily_closures_table.php
- [X] T004 Crear migración reversible de daily_closure_operations con operation_id UNIQUE en database/migrations/2026_07_22_000018_create_daily_closure_operations_table.php
- [X] T005 [P] Implementar modelo DailyClosure con casts DECIMAL y estados en app/Modules/DailyClosing/Models/DailyClosure.php
- [X] T006 [P] Implementar modelo DailyClosureOperation en app/Modules/DailyClosing/Models/DailyClosureOperation.php
- [X] T007 Implementar DailyClosingPolicy con permisos generate/confirm/reopen/view en app/Modules/DailyClosing/Policies/DailyClosingPolicy.php
- [X] T008 Registrar rutas de DailyClosing en routes/daily-closing.php y requerir desde routes/web.php

**Checkpoint**: Las 2 migraciones corren. Unique constraint impide duplicados activos.

---

## Phase 3: User Story 1 - Generar y visualizar un cierre diario (Priority: P1)

**Goal**: Consolidar operaciones activas de un agente en una fecha. Operador genera solo en agentes asignados; admin en cualquier agente. Vista con métricas, desglose por tipo y operador, y operaciones anuladas.

**Independent Test**: Generar cierre con operaciones de prueba y verificar métricas, desgloses y autorización.

### Tests for User Story 1 (REQUIRED)

- [X] T009 [P] [US1] Probar AC1 generación con métricas correctas en tests/Feature/DailyClosing/GenerateClosingTest.php
- [X] T010 [P] [US1] Probar AC2 desglose por tipo y operador en tests/Feature/DailyClosing/ClosingBreakdownTest.php
- [X] T011 [P] [US1] Probar AC3 operaciones anuladas listadas pero excluidas en tests/Feature/DailyClosing/ClosingAnnulledOpsTest.php
- [X] T012 [P] [US1] Probar AC4 agente sin operaciones muestra ceros en tests/Feature/DailyClosing/ClosingEmptyAgentTest.php
- [X] T013 [P] [US1] Probar AC5 operador no ve cierre de agente no asignado en tests/Feature/DailyClosing/ClosingOperatorAuthorizationTest.php

### Implementation for User Story 1

- [X] T014 [P] [US1] Implementar GenerateClosingRequest con bank_agent_id, business_date y regenerate en app/Modules/DailyClosing/Http/Requests/GenerateClosingRequest.php
- [X] T015 [US1] Implementar consolidación SQL y generación/regeneración en app/Modules/DailyClosing/Http/Controllers/DailyClosingController.php
- [X] T016 [P] [US1] Crear vista Blade de detalle con tarjetas, desgloses y lista de operaciones en resources/views/daily-closing/show.blade.php
- [X] T017 [P] [US1] Crear vista Blade de formulario de generación en resources/views/daily-closing/create.blade.php

**Checkpoint**: Cierre generado con métricas. Operador restringido a sus agentes.

---

## Phase 4: User Story 2 - Confirmar un cierre (Priority: P1)

**Goal**: Admin confirma cierre. Operaciones del cierre quedan bloqueadas. Nuevo registro rechazado en el agente+fecha.

**Independent Test**: Confirmar cierre, intentar anular/registrar operaciones, verificar bloqueo.

### Tests for User Story 2 (REQUIRED)

- [X] T018 [P] [US2] Probar AC1 confirmación y auditoría en tests/Feature/DailyClosing/ConfirmClosingTest.php
- [X] T019 [P] [US2] Probar AC2 anulación rechazada post-confirmación en tests/Feature/DailyClosing/PostConfirmBlockAnnulTest.php
- [X] T020 [P] [US2] Probar AC3 operador no puede confirmar en tests/Feature/DailyClosing/ConfirmAuthorizationTest.php
- [X] T021 [P] [US2] Probar AC4 registro rechazado en agente+fecha confirmado en tests/Feature/DailyClosing/PostConfirmBlockRegisterTest.php

### Implementation for User Story 2

- [X] T022 [US2] Implementar confirmación con transición de estado y auditoría en DailyClosingController en app/Modules/DailyClosing/Http/Controllers/DailyClosingController.php
- [X] T023 [US2] Extender RegisterOperation de 003 para verificar cierre confirmado en app/Modules/Operations/Application/Actions/RegisterOperation.php
- [X] T024 [US2] Extender AnnulOperation de 003 para verificar cierre confirmado en app/Modules/Operations/Application/Actions/AnnulOperation.php

**Checkpoint**: Cierre confirmado bloquea operaciones. Admin confirma, operador no.

---

## Phase 5: User Story 3 - Reabrir un cierre (Priority: P2)

**Goal**: Admin reabre cierre con motivo. Operaciones vuelven a ser modificables. Reapertura auditada.

**Independent Test**: Reabrir cierre, verificar anulación permitida, reconfirmar.

### Tests for User Story 3 (REQUIRED)

- [X] T025 [P] [US3] Probar AC1 reapertura con motivo y auditoría en tests/Feature/DailyClosing/ReopenClosingTest.php
- [X] T026 [P] [US3] Probar AC2 anulación permitida post-reapertura en tests/Feature/DailyClosing/ReopenAllowsAnnulTest.php
- [X] T027 [P] [US3] Probar AC3 reconfirmación después de reapertura en tests/Feature/DailyClosing/ReconfirmAfterReopenTest.php
- [X] T028 [P] [US3] Probar AC4 motivo vacío rechazado en tests/Feature/DailyClosing/ReopenNoReasonTest.php
- [X] T029 [P] [US3] Probar AC5 operador no puede reabrir en tests/Feature/DailyClosing/ReopenAuthorizationTest.php

### Implementation for User Story 3

- [X] T030 [P] [US3] Implementar ReopenClosingRequest con validación de motivo en app/Modules/DailyClosing/Http/Requests/ReopenClosingRequest.php
- [X] T031 [US3] Implementar reapertura con transición de estado y auditoría en DailyClosingController en app/Modules/DailyClosing/Http/Controllers/DailyClosingController.php

**Checkpoint**: Reapertura auditada. Operaciones modificables nuevamente.

---

## Phase 6: User Story 4 - Advertencia POR_CONFIRMAR (Priority: P2)

**Goal**: Mostrar advertencia y etiquetar neto como no definitivo cuando hay operaciones POR_CONFIRMAR.

**Independent Test**: Generar cierre con y sin operaciones POR_CONFIRMAR, verificar advertencia y etiqueta.

### Tests for User Story 4 (REQUIRED)

- [X] T032 [P] [US4] Probar AC1 advertencia con POR_CONFIRMAR en tests/Feature/DailyClosing/PendingConfirmWarningTest.php
- [X] T033 [P] [US4] Probar AC2 sin advertencia sin POR_CONFIRMAR en tests/Feature/DailyClosing/NoPendingConfirmTest.php

### Implementation for User Story 4

- [X] T034 [US4] Implementar detección de POR_CONFIRMAR y flag has_pending_confirm en la consolidación del cierre en DailyClosingController
- [X] T035 [P] [US4] Crear partial de advertencia en resources/views/daily-closing/components/pending-confirm-warning.blade.php

**Checkpoint**: Advertencia visible solo cuando aplica. Neto etiquetado correctamente.

---

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T036 [P] Implementar registro de auditoría en generar, confirmar y reabrir en DailyClosingController
- [X] T037 [P] Probar unique constraint de cierre activo duplicado en tests/Feature/DailyClosing/DuplicateActiveClosingTest.php
- [X] T038 [P] Probar migraciones up/down en tests/Feature/ClosingMigrationsTest.php
- [X] T039 [P] Probar escenarios del quickstart en tests/Feature/DailyClosing/ClosingQuickstartTest.php
- [X] T040 Ejecutar php artisan migrate y verificar unique constraint en SQLite de desarrollo

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: inicia de inmediato.
- **Foundational (Phase 2)**: bloquea todas las historias.
- **US1 (Phase 3)**: P1. Depende de Foundational.
- **US2 (Phase 4)**: P1. Depende de US1 (modelo DailyClosure) + Foundational.
- **US3 (Phase 5)**: P2. Depende de US2 (estado CONFIRMADO existe).
- **US4 (Phase 6)**: P2. Depende de US1 (generación de cierre).
- **Polish (Phase 7)**: depende de todas las historias.

### MVP Scope

US1 + US2 (generar + confirmar). US3 (reapertura) y US4 (advertencia) pueden posponerse.

### Parallel Opportunities

- T005–T006 (modelos) son independientes.
- T009–T013 (pruebas US1) pueden escribirse juntas antes de implementar.
- US4 (Phase 6) puede ejecutarse en paralelo con US2/US3 después de US1.
- T036–T039 se distribuyen por archivos distintos.

---

## Notes

- Las migraciones usan timestamps 000017 y 000018.
- T023 y T024 modifican actions de 003; requieren que 003 esté completamente implementado.
- La unique constraint parcial usa MySQL 8.0+ o MariaDB con columna virtual.
- El bloqueo post-confirmación es una verificación en los actions de Operations, no un trigger de BD.
- Las vistas Blade reutilizan el layout `authenticated.blade.php`.
- "Monto bruto operado" como etiqueta, nunca "Ingreso"/"Utilidad".

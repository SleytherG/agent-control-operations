---
description: "Tareas de implementación para dashboards operacionales"
---

# Tasks: Dashboards Operacionales

**Input**: Design documents from `/specs/004-operational-dashboard/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md; features 001, 002 y 003 completamente implementadas

**Tests**: Agregaciones correctas, autorización, consistencia de filtros, estado vacío, terminología.

**Organization**: Tareas agrupadas por historia. Sin migraciones ni nuevas tablas.

## Format: `[ID] [P?] [Story] Description`

## Phase 1: Setup

**Purpose**: Instalar Chart.js y crear estructura del módulo.

- [X] T001 Instalar chart.js como dependencia npm en package.json
- [X] T002 [P] Agregar entrada dashboard-charts.js en vite.config.js
- [X] T003 Crear estructura del módulo en app/Modules/Reporting/Http/Controllers/, Policies/, Services/
- [X] T004 [P] Crear directorios de vistas en resources/views/reporting/ y tests en tests/Feature/Reporting/

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Servicio de queries agregadas y políticas compartidas.

- [X] T005 Implementar DashboardQueryService con métodos de agregación SQL en app/Modules/Reporting/Services/DashboardQueryService.php
- [X] T006 Implementar DashboardPolicy con viewOperatorDashboard y viewAdminDashboard en app/Modules/Reporting/Policies/DashboardPolicy.php
- [X] T007 Implementar DashboardFilterRequest con validación de periodo, fechas y filtros en app/Modules/Reporting/Http/Requests/DashboardFilterRequest.php
- [X] T008 Registrar rutas de Reporting en routes/reporting.php y requerir desde routes/web.php

**Checkpoint**: El servicio de queries devuelve agregaciones correctas contra SQL directo.

---

## Phase 3: User Story 1 - Dashboard del operador (Priority: P1)

**Goal**: Operador ve tarjetas con métricas, gráfico de distribución por tipo y evolución temporal de sus propias operaciones.

**Independent Test**: Autenticarse como operador con operaciones registradas y verificar métricas, gráficos y estado vacío.

### Tests for User Story 1 (REQUIRED)

- [X] T009 [P] [US1] Probar AC1 tarjetas con métricas del operador en tests/Feature/Reporting/OperatorDashboardMetricsTest.php
- [X] T010 [P] [US1] Probar AC2 distribución por tipo en tests/Feature/Reporting/OperatorDashboardDistributionTest.php
- [X] T011 [P] [US1] Probar AC3 evolución temporal por periodo en tests/Feature/Reporting/OperatorDashboardEvolutionTest.php
- [X] T012 [P] [US1] Probar AC4 estado vacío sin operaciones en tests/Feature/Reporting/OperatorDashboardEmptyTest.php
- [X] T013 [P] [US1] Probar AC5 anuladas excluidas de métricas en tests/Feature/Reporting/OperatorDashboardAnnulledTest.php

### Implementation for User Story 1

- [X] T014 [US1] Implementar operador dashboard en app/Modules/Reporting/Http/Controllers/DashboardController.php
- [X] T015 [P] [US1] Crear vista Blade del dashboard con tarjetas, doughnut y line chart en resources/views/reporting/operator-dashboard.blade.php
- [X] T016 [P] [US1] Implementar gráficos Chart.js en resources/js/reporting/dashboard-charts.js
- [X] T017 [P] [US1] Crear partial de estado vacío en resources/views/reporting/components/empty-state.blade.php

**Checkpoint**: Operador ve solo sus métricas. Estado vacío funciona. Anuladas excluidas.

---

## Phase 4: User Story 2 - Dashboard del administrador (Priority: P1)

**Goal**: Admin ve métricas de toda la organización con filtros multidimensionales y consistencia entre componentes.

**Independent Test**: Aplicar filtros y verificar que tarjetas, gráficos y tabla se actualizan consistentemente.

### Tests for User Story 2 (REQUIRED)

- [X] T018 [P] [US2] Probar AC1 dashboard sin filtros en tests/Feature/Reporting/AdminDashboardAllTest.php
- [X] T019 [P] [US2] Probar AC2 filtro por tienda actualiza todo en tests/Feature/Reporting/AdminDashboardFilterConsistencyTest.php
- [X] T020 [P] [US2] Probar AC3 periodo mensual en tests/Feature/Reporting/AdminDashboardPeriodTest.php
- [X] T021 [P] [US2] Probar AC4 incluir anuladas en tests/Feature/Reporting/AdminDashboardAnnulledTest.php
- [X] T022 [P] [US2] Probar AC5 filtros sin resultados en tests/Feature/Reporting/AdminDashboardEmptyTest.php

### Implementation for User Story 2

- [X] T023 [US2] Implementar admin dashboard con filtros en DashboardController en app/Modules/Reporting/Http/Controllers/DashboardController.php
- [X] T024 [P] [US2] Crear vista Blade del dashboard administrativo con filtros en resources/views/reporting/admin-dashboard.blade.php
- [X] T025 [P] [US2] Crear partial de filtros en resources/views/reporting/components/filters.blade.php
- [X] T026 [P] [US2] Crear partial de tabla de operaciones recientes paginada en resources/views/reporting/components/operations-table.blade.php

**Checkpoint**: Admin filtra por cualquier dimensión. Consistencia entre componentes. Estado vacío.

---

## Phase 5: User Story 3 - Vista comparativa de operadores (Priority: P2)

**Goal**: Admin compara operadores mediante gráfico de barras y tabla ordenable con ranking.

**Independent Test**: Seleccionar operadores, cambiar periodo, ordenar tabla.

### Tests for User Story 3 (REQUIRED)

- [X] T027 [P] [US3] Probar AC1 gráfico y selector de operadores en tests/Feature/Reporting/OperatorComparisonChartTest.php
- [X] T028 [P] [US3] Probar AC2 filtro de fecha en comparativa en tests/Feature/Reporting/OperatorComparisonPeriodTest.php
- [X] T029 [P] [US3] Probar AC3 orden de tabla en tests/Feature/Reporting/OperatorComparisonTableTest.php
- [X] T030 [P] [US3] Probar AC4 un solo operador sin error en tests/Feature/Reporting/OperatorComparisonSingleTest.php

### Implementation for User Story 3

- [X] T031 [US3] Implementar comparativa de operadores en DashboardController en app/Modules/Reporting/Http/Controllers/DashboardController.php
- [X] T032 [P] [US3] Crear vista Blade de comparativa con selector y tabla en resources/views/reporting/operator-comparison.blade.php
- [X] T033 [P] [US3] Extender dashboard-charts.js con gráfico de barras horizontales

**Checkpoint**: Comparativa funcional con ranking. Legible con múltiples operadores.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Terminología, rendimiento y validación final.

- [X] T034 [P] Verificar que "monto bruto operado" es la etiqueta en todas las vistas, sin "ingreso"/"utilidad"/"ganancia"
- [X] T035 [P] Probar autorización de dashboard en tests/Feature/Reporting/DashboardAuthorizationTest.php
- [X] T036 [P] Probar precisión de agregaciones SQL contra queries directas en tests/Feature/Reporting/DashboardPrecisionTest.php
- [X] T037 [P] Probar rendimiento con 50k operaciones en tests/Feature/Reporting/DashboardPerformanceTest.php
- [X] T038 Ejecutar escenarios del quickstart y validar

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: inicia de inmediato.
- **Foundational (Phase 2)**: depende de Setup, bloquea todas las historias.
- **US1 (Phase 3)**: P1. Depende de Foundational.
- **US2 (Phase 4)**: P1. Depende de Foundational. Puede correr en paralelo con US1.
- **US3 (Phase 5)**: P2. Depende de US2 (admin controller + vistas).
- **Polish (Phase 6)**: depende de todas las historias.

### MVP Scope

US1 + US2 (operador y admin dashboard). US3 (comparativa) puede posponerse.

### Parallel Opportunities

- T001–T002 son independientes de T003–T004.
- T009–T013 (pruebas US1) pueden escribirse juntas antes de implementar.
- US1 y US2 pueden ejecutarse en paralelo después de Foundational.
- T034–T037 se distribuyen por archivos distintos.

---

## Notes

- Sin migraciones. Sin nuevas tablas. Solo lectura.
- Chart.js cargado como entrada Vite separada, no en bundle global.
- Todas las agregaciones en SQL; cero colecciones Eloquent cargadas en memoria.
- Las vistas Blade usan @extends('layouts.authenticated').
- La etiqueta canónica es "Monto bruto operado", nunca "Ingreso", "Utilidad" o "Ganancia".

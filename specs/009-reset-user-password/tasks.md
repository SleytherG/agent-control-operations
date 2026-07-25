# Tasks: Restablecimiento Seguro de Contraseña

**Input**: Design documents from `/specs/009-reset-user-password/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md

**Tests**: Las pruebas automatizadas son obligatorias para cada escenario de aceptación, autorización positiva/negativa, ciclo JWT, revocación, expiración, concurrencia, auditoría, migración y recuperación aplicable.

**Organization**: Las tareas están agrupadas por historia de usuario para permitir implementación y validación incremental. Las pruebas de cada historia se escriben primero y deben fallar antes de implementar.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo porque usa archivos distintos y no depende de una tarea incompleta.
- **[Story]**: Mapea la tarea a `US1`, `US2` o `US3`.
- Todas las tareas incluyen rutas exactas del repositorio.

## Phase 1: Setup And Requirements Gate

**Purpose**: Cerrar el gate formal de requisitos y preparar configuración/suites antes de cambiar el dominio.

- [X] T001 Verificar antes de código que specs/009-reset-user-password/spec.md permanece Approved y que specs/009-reset-user-password/checklists/security.md y specs/009-reset-user-password/checklists/requirements.md conservan cero brechas documentales abiertas
- [X] T002 [P] Incorporar una suite Integration ejecutable para tests/Integration/IdentityAccess y tests/Integration/Migrations en phpunit.xml
- [X] T003 [P] Definir TTL 3600, temporal CSPRNG de 20 caracteres con letra/número/símbolo y límites de 5 fallos/60 segundos para login y step-up en config/session-security.php y .env.example

---

## Phase 2: Foundational Domain And Persistence

**Purpose**: Crear el ciclo persistente, reglas compartidas y revocación atómica que bloquean todas las historias.

**⚠️ CRITICAL**: Ninguna historia puede implementarse hasta completar esta fase.

### Tests For Foundation

- [X] T004 [P] Escribir primero las pruebas up/down, FKs, índices, null compatibility y unicidad de sesión restringida en tests/Integration/Migrations/PasswordResetMigrationsTest.php
- [X] T005 [P] Escribir primero las pruebas de transiciones, límite exacto de una hora y política/generación de contraseña en tests/Unit/IdentityAccess/PasswordResetStateTest.php y tests/Unit/IdentityAccess/PasswordPolicyTest.php

### Foundation Implementation

- [X] T006 Crear la migración reversible de password_resets con campos, FKs e índices del modelo de datos en database/migrations/2026_07_23_000010_create_password_resets_table.php
- [X] T007 Agregar la FK nullable y unique password_reset_id a auth_sessions con rollback seguro en database/migrations/2026_07_23_000011_add_password_reset_id_to_auth_sessions_table.php
- [X] T008 [P] Crear PasswordResetStatus y sus transiciones permitidas en app/Modules/IdentityAccess/Domain/Enums/PasswordResetStatus.php
- [X] T009 [P] Crear el modelo y factory del ciclo sin secreto/hash duplicado en app/Modules/IdentityAccess/Models/PasswordReset.php y database/factories/IdentityAccess/PasswordResetFactory.php
- [X] T010 Agregar casts y relaciones de password resets/sesión restringida en app/Modules/IdentityAccess/Models/User.php, app/Modules/IdentityAccess/Models/AuthSession.php y app/Modules/IdentityAccess/Models/PasswordReset.php
- [X] T011 [P] Centralizar la regla permanente y el generador CSPRNG temporal en app/Modules/IdentityAccess/Services/PasswordPolicy.php y registrar sus defaults en app/Providers/AppServiceProvider.php
- [X] T012 [P] Añadir PASSWORD_RESET y los eventos de revocación/login restringido en app/Modules/IdentityAccess/Domain/Enums/SessionEndReason.php y app/Modules/IdentityAccess/Domain/Enums/SessionEventType.php
- [X] T013 Extraer revocación masiva transaccional de sesiones y refresh tokens con eventos sanitizados en app/Modules/IdentityAccess/Application/Actions/RevokeAllUserSessions.php y reutilizarla desde app/Modules/IdentityAccess/Application/Actions/DeactivateUser.php

**Checkpoint**: Migraciones, modelo, política de contraseña y revocación compartida listos; las historias pueden comenzar.

---

## Phase 3: User Story 1 - Restablecer Acceso Del Operador (Priority: P1) 🎯 First Increment

**Goal**: Permitir que un administrador autorizado revalide su contraseña, emita un secreto temporal one-shot para un operador activo y revoque todas sus sesiones sin exponer credenciales.

**Independent Test**: Un administrador del mismo ámbito restablece un operador activo, ve una sola vez la temporal, comprueba que la anterior y todas las sesiones/refresh del operador fallan, mientras actores/targets no autorizados no producen mutaciones.

### Tests For User Story 1 (REQUIRED) ⚠️

- [X] T014 [P] [US1] Escribir primero escenarios de admin mismo ámbito, operador actor, admin cross-organization, target administrador y target no activo en tests/Feature/IdentityAccess/PasswordResetAuthorizationTest.php
- [X] T015 [P] [US1] Escribir primero reautenticación correcta/incorrecta, no mutación y throttling administrativo en tests/Feature/IdentityAccess/AdminPasswordReauthenticationTest.php
- [X] T016 [P] [US1] Escribir primero emisión, expiración registrada, segundo reset, revocación multisesión/refresh y preservación de sesión admin en tests/Feature/IdentityAccess/PasswordResetLifecycleTest.php
- [X] T017 [P] [US1] Escribir primero reveal one-shot, no-store y ausencia del secreto/hash en sesión, URL, DB adicional, auditoría y logs en tests/Feature/IdentityAccess/PasswordResetSecretLeakTest.php
- [X] T018 [P] [US1] Escribir primero carreras reset-vs-reset y reset-vs-deactivación con dos conexiones reales en tests/Integration/IdentityAccess/PasswordResetConcurrencyTest.php

### Implementation For User Story 1

- [X] T019 [P] [US1] Crear validación sin flash de admin_password y reason opcional acotado en app/Modules/IdentityAccess/Http/Requests/ResetOperatorPasswordRequest.php
- [X] T020 [P] [US1] Agregar resetPassword con rol, organización, target OPERADOR y estado ACTIVE en app/Modules/IdentityAccess/Policies/UserPolicy.php
- [X] T021 [US1] Implementar ResetOperatorPassword con reautenticación, lock del target, sustitución de ciclos, hash temporal, revocación y auditoría atómica en app/Modules/IdentityAccess/Application/Actions/ResetOperatorPassword.php
- [X] T022 [US1] Crear respuesta JSON post-commit one-shot y errores 403/409/422/429 sin secretos en app/Modules/IdentityAccess/Http/Controllers/PasswordResetController.php
- [X] T023 [US1] Registrar POST /admin/users/{user}/password-reset con middleware web/JWT/CSRF en routes/identity-access.php
- [X] T024 [US1] Añadir sección de seguridad y modal accesible con identidad, advertencia, admin_password, reason y resultado efímero en resources/views/identity-access/operators/form.blade.php
- [X] T025 [US1] Implementar fetch same-origin, copiado accesible, limpieza del DOM y manejo seguro de errores en resources/js/identity-access/password-reset.js e importarlo desde resources/js/app.js

**Checkpoint**: US1 emite y entrega de manera one-shot una temporal válida, revoca el acceso anterior y bloquea actores/targets no autorizados.

---

## Phase 4: User Story 2 - Cambiar Obligatoriamente La Contraseña Temporal (Priority: P1) 🎯 Safe MVP

**Goal**: Consumir la temporal en un solo login, restringir esa sesión y completar atómicamente una contraseña definitiva antes de habilitar el resto del sistema.

**Independent Test**: La primera autenticación temporal crea una única sesión restringida; la segunda falla, rutas ajenas no ejecutan efectos, logout/refresh siguen las reglas aclaradas y un cambio válido completa el ciclo mientras entradas inválidas lo mantienen consumido.

### Tests For User Story 2 (REQUIRED) ⚠️

- [X] T026 [P] [US2] Escribir primero login temporal válido, credenciales incorrecta/vencida/consumida/sustituida y error genérico en tests/Feature/IdentityAccess/TemporaryPasswordLoginTest.php
- [X] T027 [P] [US2] Escribir primero allowlist de cambio/logout/refresh, conservación de password_reset_id durante rotación y rechazo de lecturas/escrituras manipuladas en tests/Feature/IdentityAccess/RestrictedSessionTest.php
- [X] T028 [P] [US2] Escribir primero ausencia del campo temporal, política, confirmación, igualdad con hash temporal, éxito y rollback en tests/Feature/IdentityAccess/PasswordChangeCompletionTest.php
- [X] T029 [P] [US2] Escribir primero límites issue+59:59 e issue+60:00 y pérdida/logout/expiración de sesión restringida en tests/Feature/IdentityAccess/TemporaryPasswordExpiryTest.php
- [X] T030 [P] [US2] Escribir primero doble login, reset-vs-login y doble finalización con conexiones reales en tests/Integration/IdentityAccess/TemporaryPasswordConcurrencyTest.php
- [X] T031 [P] [US2] Escribir primero la regresión de alta inicial sin password reset y el caso fallido del redirect encadenado en tests/Feature/IdentityAccess/ForcePasswordChangeTest.php

### Implementation For User Story 2

- [X] T032 [US2] Implementar autenticación y sesión transaccionales con lock, expiración lazy, consumo único y auditoría password_reset.consumed/password_reset.expired en app/Modules/IdentityAccess/Application/Actions/AuthenticateAndStartSession.php
- [X] T033 [US2] Integrar el resultado normal/restringido y mensajes genéricos sin carrera en app/Modules/IdentityAccess/Http/Controllers/LoginController.php
- [X] T034 [US2] Aplicar la restricción por password_reset_id y allowlist acordada antes de cualquier efecto protegido en app/Http/Middleware/AuthenticateJwtSession.php
- [X] T035 [P] [US2] Crear validación exclusiva de nueva política compartida y confirmación, sin solicitar la temporal consumida ni hacer flash de secretos en app/Modules/IdentityAccess/Http/Requests/CompletePasswordChangeRequest.php
- [X] T036 [US2] Implementar CompleteRequiredPasswordChange con locks de sesión/reset/usuario, comparación de la nueva contra el hash temporal, transición COMPLETED y auditoría en app/Modules/IdentityAccess/Application/Actions/CompleteRequiredPasswordChange.php
- [X] T037 [US2] Refactorizar PasswordChangeController para delegar en la acción, corregir el redirect inválido y preservar el flujo de alta inicial en app/Modules/IdentityAccess/Http/Controllers/PasswordChangeController.php
- [X] T038 [US2] Adaptar mensajes, política, solo campos nueva/confirmación sin repoblación, logout y navegación restringida en resources/views/identity-access/password-change.blade.php
- [X] T039 [US2] Asegurar que logout y refresh respeten la sesión restringida sin reabrir/completar el reset en app/Modules/IdentityAccess/Http/Controllers/LogoutController.php y app/Modules/IdentityAccess/Http/Controllers/RefreshSessionController.php

**Checkpoint**: US1+US2 constituyen el MVP seguro desplegable; el operador debe sustituir la temporal antes de operar.

---

## Phase 5: User Story 3 - Verificar Estado Y Trazabilidad (Priority: P2)

**Goal**: Mostrar al administrador el estado vigente y una auditoría paginada atribuible, sin exponer secretos ni permitir consulta a operadores u otros ámbitos.

**Independent Test**: Tras emitir, consumir y completar/reemplazar/vencer resets, el administrador ve estados, actores y fechas coherentes; operador y admin ajeno reciben 403 y ninguna respuesta contiene contraseñas o hashes.

### Tests For User Story 3 (REQUIRED) ⚠️

- [X] T040 [P] [US3] Escribir primero autorización, ámbito, filtros y paginación del historial en tests/Feature/IdentityAccess/PasswordResetAuditAuthorizationTest.php
- [X] T041 [P] [US3] Escribir primero contenido de issued/consumed/completed/superseded/expired, actores y snapshots sanitizados en tests/Feature/IdentityAccess/PasswordResetAuditTest.php
- [X] T042 [P] [US3] Escribir primero badge/estado/fechas del último reset sin N+1 ni secreto en tests/Feature/IdentityAccess/PasswordResetStatusViewTest.php

### Implementation For User Story 3

- [X] T043 [P] [US3] Crear validación de page/status/from/to para auditoría en app/Modules/IdentityAccess/Http/Requests/ListPasswordResetAuditRequest.php
- [X] T044 [US3] Implementar query scoped por organización/target/acciones y paginación 25 en app/Modules/IdentityAccess/Application/Actions/ListPasswordResetAudit.php
- [X] T045 [US3] Crear controlador del historial y resolución transaccional de expiraciones pendientes con evento password_reset.expired sin secretos en app/Modules/IdentityAccess/Http/Controllers/PasswordResetAuditController.php
- [X] T046 [US3] Agregar viewPasswordResetAudit a app/Modules/IdentityAccess/Policies/UserPolicy.php y GET /admin/users/{user}/password-resets a routes/identity-access.php
- [X] T047 [US3] Crear historial accesible y añadir carga eficiente del último estado en resources/views/identity-access/password-resets/index.blade.php, resources/views/identity-access/operators/index.blade.php y app/Modules/IdentityAccess/Http/Controllers/OperatorController.php

**Checkpoint**: Las tres historias son funcionales y la trazabilidad solo está disponible al administrador propietario autorizado.

---

## Phase 6: Polish And Cross-Cutting Security

**Purpose**: Cerrar requisitos transversales, recuperación, rendimiento y evidencia completa.

- [X] T048 [P] Añadir cobertura de navegador para foco, teclado, anuncio de copiado, limpieza de secreto, back/reload y viewports en tests/Browser/IdentityAccess/PasswordResetAccessibilityTest.php
- [X] T049 [P] Añadir presupuesto menor a 2 segundos, paginación e inspección de queries sin N+1 en tests/Performance/PasswordResetPerformanceTest.php
- [X] T050 [P] Activar y ampliar la regresión transversal de secretos anidados, excepciones y correlation context en tests/Feature/IdentityAccess/AuthSecretLeakTest.php y app/Logging/LogSanitizer.php
- [X] T051 Ejecutar y estabilizar todas las carreras de reset/login/change/deactivation en MySQL y MariaDB mediante tests/Integration/IdentityAccess/PasswordResetConcurrencyTest.php y tests/Integration/IdentityAccess/TemporaryPasswordConcurrencyTest.php
- [X] T052 [P] Documentar y validar backup, restore, migration up/down y estrategia forward-fix del historial en specs/009-reset-user-password/quickstart.md y tests/Integration/Migrations/PasswordResetMigrationsTest.php
- [X] T053 Ejecutar formato, análisis de sintaxis, npm build y suites Unit/Feature/Integration/Browser; corregir solo archivos del feature enumerados por T006–T050 en app/Modules/IdentityAccess/, app/Http/Middleware/AuthenticateJwtSession.php, resources/ y tests/
- [X] T054 Recorrer los escenarios 1–10, cronometrar 5 resets y 5 cambios, ejecutar la prueba guiada con al menos 10 participantes y registrar evidencia anonimizada en specs/009-reset-user-password/validation-report.md
- [X] T055 Revalidar los 40 ítems de specs/009-reset-user-password/checklists/security.md y los 16 ítems de specs/009-reset-user-password/checklists/requirements.md, dejando cero gates de seguridad pendientes antes de cierre

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 — Setup/Gate**: sin dependencias; debe cerrar decisiones bloqueantes antes de código.
- **Phase 2 — Foundational**: depende de Phase 1 y bloquea todas las historias.
- **Phase 3 — US1**: depende de Foundation; entrega emisión/revocación/revelación.
- **Phase 4 — US2**: depende funcionalmente de la entidad y emisión de US1; puede desarrollarse en paralelo con fixtures después de Foundation, pero se integra después de US1.
- **Phase 5 — US3**: puede desarrollar consulta/UI con fixtures después de Foundation; la validación completa depende de eventos producidos por US1 y US2.
- **Phase 6 — Polish**: depende de todas las historias incluidas en la entrega.

### User Story Dependency Graph

```text
Setup/Gate
    |
Foundation
    |
    +--> US1: emitir/revocar/revelar --------+
    |                                       |
    +--> US2: consumir/restringir/cambiar ---+--> Safe MVP
    |                                       |
    +--> US3: consultar estado/auditoría ----+--> Full Feature
```

### Within Each User Story

- Las pruebas de aceptación/autorización se escriben y fallan antes de la implementación.
- Requests/Policies pueden avanzar en paralelo con pruebas cuando usan archivos distintos.
- Acciones de dominio preceden a controladores, rutas y vistas.
- UI se integra después de que el contrato del endpoint esté estable.
- Cada checkpoint exige ejecutar las pruebas de la historia y regresiones IdentityAccess existentes.

### Parallel Opportunities

- T002 y T003 pueden avanzar en paralelo después de T001.
- T004 y T005 pueden escribirse en paralelo; T008, T009, T011 y T012 usan archivos independientes.
- T014–T018 pueden escribirse en paralelo antes de T019–T025.
- T019 y T020 pueden avanzar en paralelo; T024 puede prepararse mientras T021–T023 estabilizan el contrato.
- T026–T031 pueden escribirse en paralelo; T035 puede avanzar mientras T032–T034 se implementan.
- T040–T042 pueden escribirse en paralelo; T043 puede avanzar antes de T044–T047.
- T048, T049, T050 y T052 pueden ejecutarse en paralelo antes de la estabilización final.

---

## Parallel Example: User Story 1

```text
Task T014: tests/Feature/IdentityAccess/PasswordResetAuthorizationTest.php
Task T015: tests/Feature/IdentityAccess/AdminPasswordReauthenticationTest.php
Task T016: tests/Feature/IdentityAccess/PasswordResetLifecycleTest.php
Task T017: tests/Feature/IdentityAccess/PasswordResetSecretLeakTest.php
Task T018: tests/Integration/IdentityAccess/PasswordResetConcurrencyTest.php
```

## Parallel Example: User Story 2

```text
Task T026: tests/Feature/IdentityAccess/TemporaryPasswordLoginTest.php
Task T027: tests/Feature/IdentityAccess/RestrictedSessionTest.php
Task T028: tests/Feature/IdentityAccess/PasswordChangeCompletionTest.php
Task T029: tests/Feature/IdentityAccess/TemporaryPasswordExpiryTest.php
Task T030: tests/Integration/IdentityAccess/TemporaryPasswordConcurrencyTest.php
Task T031: tests/Feature/IdentityAccess/ForcePasswordChangeTest.php
```

## Parallel Example: User Story 3

```text
Task T040: tests/Feature/IdentityAccess/PasswordResetAuditAuthorizationTest.php
Task T041: tests/Feature/IdentityAccess/PasswordResetAuditTest.php
Task T042: tests/Feature/IdentityAccess/PasswordResetStatusViewTest.php
Task T043: app/Modules/IdentityAccess/Http/Requests/ListPasswordResetAuditRequest.php
```

---

## Implementation Strategy

### First Demonstrable Increment

1. Complete Phase 1 and Phase 2.
2. Complete Phase 3 (US1).
3. Validate emisión, autorización, revocación and reveal one-shot independently.
4. Do not deploy this increment alone unless the existing forced-change flow satisfies the temporary-password lifecycle approved for the environment.

### Safe MVP

1. Complete Setup + Foundation.
2. Complete US1 and US2.
3. Run all Unit/Feature/Integration tests for issuance, one-time login, restriction and completion.
4. Stop and validate the safe end-to-end flow before adding audit UI.

### Full Incremental Delivery

1. **US1**: administrative recovery and revocation.
2. **US2**: one-time consumption and mandatory definitive change — safe MVP.
3. **US3**: authorized status and audit visibility.
4. **Polish**: browser, performance, recovery, full quickstart and security gates.

## Notes

- `[P]` significa archivos distintos y ausencia de dependencia inmediata.
- `[US1]`, `[US2]` y `[US3]` trazan cada tarea a su historia.
- Las pruebas deben fallar antes de implementar la conducta.
- Los secretos nunca se incluyen en fixtures persistidos, logs de test o mensajes de assertions.
- Ejecutar commits por tarea o grupo lógico y conservar los checkpoints independientes.

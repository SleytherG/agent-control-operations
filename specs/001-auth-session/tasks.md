---
description: "Tareas de implementación para autenticación y ciclo de sesión"
---

# Tasks: Autenticación y Ciclo de Sesión

**Input**: Design documents from `/specs/001-auth-session/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md

**Tests**: Todas las pruebas de aceptación, autorización positiva/negativa, ciclo JWT, concurrencia,
auditoría, migraciones y recuperación son obligatorias y se escriben antes de la implementación.

**Organization**: Las tareas se agrupan por historia para permitir validación y entrega incremental.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: puede ejecutarse en paralelo porque afecta archivos distintos y no depende de tareas incompletas.
- **[Story]**: identifica la historia `US1`–`US5`; Setup, Foundational y Polish no llevan etiqueta.
- Toda tarea incluye rutas exactas.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Crear el proyecto Laravel 13 y la estructura monolítica modular sin sobrescribir los artefactos de diseño.

- [X] T001 Inicializar Laravel 13 con PHP 8.3 en composer.json y artisan preservando .specify/, specs/ y docs/
- [X] T002 Instalar lcobucci/jwt 5.x, Laravel Dusk de desarrollo y configurar suites PHP/browser en composer.json y phpunit.xml
- [X] T003 [P] Configurar Vite solo para build con ES Modules en package.json y vite.config.js
- [X] T004 [P] Crear entradas frontend base sin SPA en resources/js/app.js y resources/css/app.css
- [X] T005 Crear límites de módulos en app/Modules/IdentityAccess/, app/Modules/Organization/ y app/Modules/Audit/
- [X] T006 [P] Completar exclusiones de secretos, dependencias y assets compilados en .gitignore y .env.example

**Checkpoint**: Laravel inicia localmente y conserva todos los documentos Spec Kit.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Implementar persistencia, configuración y servicios compartidos que bloquean todas las historias.

**CRITICAL**: Ninguna historia comienza hasta completar esta fase.

- [X] T007 Crear migración reversible de organizations con constraints e índices en database/migrations/2026_07_22_000001_create_organizations_table.php
- [X] T008 Crear migración reversible de users con unicidad normalizada, roles y desactivación lógica en database/migrations/2026_07_22_000002_create_users_table.php
- [X] T009 Crear migración reversible e indexada de auth_sessions en database/migrations/2026_07_22_000003_create_auth_sessions_table.php
- [X] T010 Crear migración reversible de auth_refresh_tokens con hash binario, generación y self-FK en database/migrations/2026_07_22_000004_create_auth_refresh_tokens_table.php
- [X] T011 Crear migración append-only de session_events con FKs e índices en database/migrations/2026_07_22_000005_create_session_events_table.php
- [X] T012 Crear migración append-only de audit_logs con snapshots sanitizados en database/migrations/2026_07_22_000006_create_audit_logs_table.php
- [X] T013 [P] Definir roles y estados de usuario/sesión en app/Modules/IdentityAccess/Domain/Enums/Role.php, app/Modules/IdentityAccess/Domain/Enums/UserStatus.php y app/Modules/IdentityAccess/Domain/Enums/AuthSessionStatus.php
- [X] T014 [P] Definir motivos, estados de refresh y eventos en app/Modules/IdentityAccess/Domain/Enums/SessionEndReason.php, app/Modules/IdentityAccess/Domain/Enums/RefreshTokenState.php y app/Modules/IdentityAccess/Domain/Enums/SessionEventType.php
- [X] T015 [P] Implementar Organization con casts y relaciones en app/Modules/Organization/Models/Organization.php
- [X] T016 [P] Implementar User con hashing, normalización, estados y relaciones en app/Modules/IdentityAccess/Models/User.php
- [X] T017 [P] Implementar AuthSession y AuthRefreshToken con relaciones y casts UTC en app/Modules/IdentityAccess/Models/AuthSession.php y app/Modules/IdentityAccess/Models/AuthRefreshToken.php
- [X] T018 [P] Implementar SessionEvent y AuditLog append-only en app/Modules/IdentityAccess/Models/SessionEvent.php y app/Modules/Audit/Models/AuditLog.php
- [X] T019 Crear factories de organización, usuarios, sesiones y tokens en database/factories/IdentityAccess/OrganizationFactory.php, database/factories/IdentityAccess/UserFactory.php, database/factories/IdentityAccess/AuthSessionFactory.php y database/factories/IdentityAccess/AuthRefreshTokenFactory.php
- [X] T020 Crear seeder seguro de organización única y administrador inicial en database/seeders/DatabaseSeeder.php
- [X] T021 Configurar TTL, máximo absoluto, issuer, audience, cookies y secretos externos en config/session-security.php y .env.example
- [X] T022 [P] Implementar emisión y validación estricta HS256 en app/Modules/IdentityAccess/Services/JwtTokenService.php
- [X] T023 [P] Implementar generación CSPRNG y HMAC-SHA-256 en app/Modules/IdentityAccess/Services/RefreshTokenService.php
- [X] T024 [P] Implementar creación, expiración y borrado de cookies host-only en app/Modules/IdentityAccess/Services/AuthCookieService.php
- [X] T025 Implementar autenticación JWT más consulta de sesión/usuario activos en app/Http/Middleware/AuthenticateJwtSession.php
- [X] T026 Registrar middleware, CSRF web, rate limiter y rutas modulares en bootstrap/app.php y app/Providers/AppServiceProvider.php
- [X] T027 Registrar rutas de IdentityAccess desde routes/web.php y crear routes/identity-access.php

**Checkpoint**: El esquema y servicios base funcionan en MySQL/MariaDB y ninguna ruta protegida confía solo en el JWT o la interfaz.

---

## Phase 3: User Story 1 - Iniciar sesión de forma segura (Priority: P1)

**Goal**: Autenticar usuarios activos por username o correo, crear sesiones independientes y rechazar accesos inválidos o vencidos.

**Independent Test**: Ejecutar login con usuarios activos/inactivos, identificadores normalizados, throttling, tokens vencidos y dos navegadores.

### Tests for User Story 1 (REQUIRED)

- [X] T028 [P] [US1] Probar AC1 login por username, sesión identificable y expiresAt en tests/Feature/IdentityAccess/LoginWithUsernameTest.php
- [X] T029 [P] [US1] Probar AC2 normalización de correo, mayúsculas y espacios en tests/Feature/IdentityAccess/LoginWithEmailTest.php
- [X] T030 [P] [US1] Probar AC3 error genérico y conteo sin enumeración de cuenta en tests/Feature/IdentityAccess/InvalidLoginTest.php
- [X] T031 [P] [US1] Probar AC4 cinco fallos, bloqueo de un minuto y reset exitoso en tests/Feature/IdentityAccess/LoginThrottleTest.php
- [X] T032 [P] [US1] Probar AC5 rechazo servidor de JWT vencido aunque el cliente aparente vigencia en tests/Feature/IdentityAccess/ExpiredAccessTokenTest.php
- [X] T033 [P] [US1] Probar AC6 creación de sesiones simultáneas independientes en tests/Feature/IdentityAccess/ConcurrentUserSessionsTest.php
- [X] T034 [P] [US1] Probar contrato GET/POST login, CSRF, cookies HttpOnly/Secure/SameSite y ausencia de tokens en body en tests/Feature/IdentityAccess/LoginContractTest.php

### Implementation for User Story 1

- [X] T035 [P] [US1] Implementar normalización y validación de identifier/password en app/Modules/IdentityAccess/Http/Requests/LoginRequest.php
- [X] T036 [US1] Implementar verificación genérica de credenciales y estado bajo lock en app/Modules/IdentityAccess/Application/Actions/AuthenticateUser.php
- [X] T037 [US1] Implementar creación transaccional de sesión, refresh generación 1 y evento LOGIN en app/Modules/IdentityAccess/Application/Actions/StartAuthSession.php
- [X] T038 [US1] Implementar GET/POST login y cookies de autenticación en app/Modules/IdentityAccess/Http/Controllers/LoginController.php
- [X] T039 [P] [US1] Crear formulario Blade semántico, responsive y con error genérico en resources/views/identity-access/login.blade.php
- [X] T040 [US1] Crear layout autenticado y página protegida con expiresAt no secreto en resources/views/layouts/authenticated.blade.php y resources/views/identity-access/home.blade.php
- [X] T041 [US1] Registrar login, home y middleware protegido en routes/identity-access.php

**Checkpoint**: US1 autentica y crea sesiones independientes; un token vencido nunca autoriza.

---

## Phase 4: User Story 2 - Decidir antes del vencimiento (Priority: P1)

**Goal**: Mostrar el tiempo restante y permitir renovación explícita o logout con rotación y trazabilidad.

**Independent Test**: Usar una sesión seeded para validar contador, modal, renovación, logout, expiración durante modal y máximo absoluto.

### Tests for User Story 2 (REQUIRED)

- [X] T042 [P] [US2] Probar AC1 cálculo por expiresAt y actualización por segundo en tests/Browser/IdentityAccess/SessionTimerTest.php
- [X] T043 [P] [US2] Probar AC2 modal al umbral de treinta segundos y accesibilidad básica en tests/Browser/IdentityAccess/SessionExpiryModalTest.php
- [X] T044 [P] [US2] Probar AC3 renovación válida, rotación, nuevo expiresAt y evento REFRESHED en tests/Feature/IdentityAccess/RefreshSessionTest.php
- [X] T045 [P] [US2] Probar AC4 logout, revocación exclusiva, evento y redirect en tests/Feature/IdentityAccess/LogoutTest.php
- [X] T046 [P] [US2] Probar AC5 intento de continuar después del vencimiento en tests/Feature/IdentityAccess/RefreshAfterExpiryTest.php
- [X] T047 [P] [US2] Probar AC6 rechazo al límite absoluto de ocho horas en tests/Feature/IdentityAccess/AbsoluteSessionExpiryTest.php
- [X] T048 [P] [US2] Probar contratos POST refresh/logout, CSRF, no-store y cookies rotadas/borradas en tests/Feature/IdentityAccess/SessionLifecycleContractTest.php

### Implementation for User Story 2

- [X] T049 [US2] Implementar rotación válida con lock session-then-token y commit previo a cookies en app/Modules/IdentityAccess/Application/Actions/RotateRefreshToken.php
- [X] T050 [US2] Implementar revocación idempotente con motivo terminal preservado en app/Modules/IdentityAccess/Application/Actions/RevokeSession.php
- [X] T051 [US2] Implementar expiración por acceso y máximo absoluto sin worker en app/Modules/IdentityAccess/Application/Actions/ExpireSession.php
- [X] T052 [US2] Implementar POST refresh con respuesta expiresAt en app/Modules/IdentityAccess/Http/Controllers/RefreshSessionController.php
- [X] T053 [US2] Implementar POST logout y limpieza de cookies en app/Modules/IdentityAccess/Http/Controllers/LogoutController.php
- [X] T054 [P] [US2] Implementar timer, visibilitychange, single-flight y manejo de respuestas inválidas en resources/js/identity-access/session-timer.js
- [X] T055 [P] [US2] Crear modal Blade accesible con Continuar/Cerrar sesión en resources/views/identity-access/components/session-expiry-modal.blade.php
- [X] T056 [P] [US2] Añadir estilos responsive, foco y estados de espera/error en resources/css/identity-access/session.css
- [X] T057 [US2] Integrar componente y módulo JS en todas las vistas protegidas mediante resources/views/layouts/authenticated.blade.php y registrar refresh/logout en routes/identity-access.php

**Checkpoint**: US2 renueva solo por acción explícita y logout revoca la sesión vigente.

---

## Phase 5: User Story 3 - Terminar accesos inválidos (Priority: P1)

**Goal**: Revocar replay y carreras, limpiar clientes inválidos y aplicar expiración exclusivamente desde el servidor.

**Independent Test**: Presentar tokens consumidos, concurrentes, revocados y vencidos; recargar y cerrar navegador sin logout.

### Tests for User Story 3 (REQUIRED)

- [X] T058 [P] [US3] Probar AC1 replay de refresh consumido, revocación y FALLO_SEGURIDAD en tests/Integration/IdentityAccess/RefreshReplayTest.php
- [X] T059 [P] [US3] Probar AC2 dos conexiones concurrentes, un sucesor máximo y revocación final en tests/Integration/IdentityAccess/ConcurrentRefreshTest.php
- [X] T060 [P] [US3] Probar AC3 contador cero y respuesta 401 limpian estado; 403/419 no limpian autenticación en tests/Browser/IdentityAccess/AuthenticationCleanupTest.php
- [X] T061 [P] [US3] Probar AC4 recarga vencida sin solicitud silenciosa de refresh en tests/Browser/IdentityAccess/NoSilentRefreshTest.php
- [X] T062 [P] [US3] Probar AC5 cierre de navegador deriva EXPIRACION sin logout garantizado en tests/Feature/IdentityAccess/BrowserCloseExpiryTest.php
- [X] T063 [P] [US3] Probar AC6 sesión revocada rechaza access y refresh actuales en tests/Feature/IdentityAccess/RevokedSessionTest.php
- [X] T064 [P] [US3] Probar firma, algoritmo, issuer, audience, sid/sub y claims temporales inválidos en tests/Feature/IdentityAccess/JwtValidationTest.php
- [X] T065 [P] [US3] Probar rollback, timeout y deadlock sin clasificar error transitorio como replay en tests/Integration/IdentityAccess/RefreshTransactionFailureTest.php
- [X] T066 [P] [US3] Probar ausencia de JWT, refresh, password y hashes en logs/errores en tests/Feature/IdentityAccess/AuthSecretLeakTest.php

### Implementation for User Story 3

- [X] T067 [US3] Completar detección de CONSUMED y revocación comprometida en app/Modules/IdentityAccess/Application/Actions/RotateRefreshToken.php
- [X] T068 [US3] Implementar registro append-only de LOGIN/REFRESH/EXPIRED/REUSE en app/Modules/IdentityAccess/Application/Actions/RecordSessionEvent.php
- [X] T069 [US3] Endurecer constraints JWT y respuestas genéricas en app/Http/Middleware/AuthenticateJwtSession.php
- [X] T070 [US3] Implementar retries acotados de deadlock y errores transitorios en app/Modules/IdentityAccess/Services/AuthTransactionRunner.php
- [X] T071 [US3] Implementar limpieza cliente y redirect único para estados inválidos en resources/js/identity-access/session-timer.js
- [X] T072 [US3] Sanitizar logs, asignar correlación y aplicar no-store en config/logging.php, app/Http/Middleware/AssignCorrelationId.php y app/Http/Middleware/AddNoStoreHeaders.php

**Checkpoint**: US1–US3 completan el MVP seguro; replay y revocación son efectivos aun con JWT no vencido.

---

## Phase 6: User Story 4 - Desactivar un usuario (Priority: P2)

**Goal**: Permitir al administrador desactivar otro usuario y revocar todas sus sesiones con auditoría.

**Independent Test**: Desactivar un usuario multi-sesión como administrador y repetir como operador, usuario inactivo y self-target.

### Tests for User Story 4 (REQUIRED)

- [X] T073 [P] [US4] Probar AC1 desactivación, revocación total, eventos y before/after/motivo en tests/Feature/IdentityAccess/AdminDeactivateUserTest.php
- [X] T074 [P] [US4] Probar AC2 login de usuario inactivo con error no enumerable en tests/Feature/IdentityAccess/InactiveUserLoginTest.php
- [X] T075 [P] [US4] Probar AC3 refresh de usuario inactivo sin nuevas credenciales en tests/Feature/IdentityAccess/InactiveUserRefreshTest.php
- [X] T076 [P] [US4] Probar AC4 operador y solicitud manipulada reciben 403 sin cambios en tests/Feature/IdentityAccess/DeactivateUserAuthorizationTest.php
- [X] T077 [P] [US4] Probar self-deactivation y último administrador activo reciben conflicto en tests/Feature/IdentityAccess/OwnerAdminInvariantTest.php
- [X] T078 [P] [US4] Probar carreras de desactivación contra login y refresh con dos conexiones en tests/Integration/IdentityAccess/DeactivateUserRaceTest.php

### Implementation for User Story 4

- [X] T079 [P] [US4] Validar motivo y target en app/Modules/IdentityAccess/Http/Requests/DeactivateUserRequest.php
- [X] T080 [P] [US4] Implementar permisos de administrador, self-target y organización en app/Modules/IdentityAccess/Policies/UserPolicy.php
- [X] T081 [US4] Implementar transacción user lock, desactivación, revocación total, eventos y auditoría en app/Modules/IdentityAccess/Application/Actions/DeactivateUser.php
- [X] T082 [US4] Implementar endpoint administrativo de desactivación en app/Modules/IdentityAccess/Http/Controllers/DeactivateUserController.php
- [X] T083 [P] [US4] Crear formulario Blade de desactivación y motivo en resources/views/identity-access/users/deactivate.blade.php
- [X] T084 [US4] Registrar PATCH administrativo protegido por Policy y CSRF en routes/identity-access.php

**Checkpoint**: US4 retira acceso inmediatamente y conserva trazabilidad completa.

---

## Phase 7: User Story 5 - Consultar historial de sesiones (Priority: P2)

**Goal**: Mostrar historial paginado con alcance global para administrador y propio para operador.

**Independent Test**: Consultar datos de varios usuarios con ambos roles, parámetros manipulados y más de una página.

### Tests for User Story 5 (REQUIRED)

- [X] T085 [P] [US5] Probar AC1 administrador consulta y filtra sesiones de todos en tests/Feature/IdentityAccess/AdminSessionHistoryTest.php
- [X] T086 [P] [US5] Probar AC2 operador obtiene exclusivamente sesiones propias en tests/Feature/IdentityAccess/OperatorSessionHistoryTest.php
- [X] T087 [P] [US5] Probar AC3 parámetros/URLs manipulados no filtran ni revelan sesiones ajenas en tests/Feature/IdentityAccess/SessionHistoryAuthorizationTest.php
- [X] T088 [P] [US5] Probar AC4 paginación limitada sin cargar colección completa en tests/Feature/IdentityAccess/SessionHistoryPaginationTest.php
- [X] T089 [P] [US5] Probar contrato de filtros, columnas permitidas y respuestas 401/403/422 en tests/Feature/IdentityAccess/SessionHistoryContractTest.php

### Implementation for User Story 5

- [X] T090 [P] [US5] Validar page/from/to/status/user y límites en app/Modules/IdentityAccess/Http/Requests/ListSessionsRequest.php
- [X] T091 [P] [US5] Implementar permisos viewAny/view y aislamiento de operador en app/Modules/IdentityAccess/Policies/AuthSessionPolicy.php
- [X] T092 [US5] Implementar query Eloquent autorizada antes de filtros y paginate en app/Modules/IdentityAccess/Application/Actions/ListAuthorizedSessions.php
- [X] T093 [US5] Implementar controlador server-rendered del historial en app/Modules/IdentityAccess/Http/Controllers/SessionHistoryController.php
- [X] T094 [P] [US5] Crear tabla semántica, filtros y navegación paginada en resources/views/identity-access/sessions/index.blade.php
- [X] T095 [US5] Registrar GET sessions protegido y autorizado en routes/identity-access.php

**Checkpoint**: US5 ofrece trazabilidad consultable sin acceso horizontal entre operadores.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Cerrar controles constitucionales, operación y despliegue compartidos.

- [X] T096 [P] Implementar health probe mínimo sin secretos en routes/web.php y app/Http/Controllers/HealthController.php
- [X] T097 [P] Probar health 200/503 y ausencia de datos sensibles en tests/Feature/HealthCheckTest.php
- [X] T098 [P] Probar up/down de las seis migraciones en MySQL y MariaDB en tests/Integration/Migrations/IdentityAccessMigrationsTest.php
- [X] T099 Ejecutar suite de compatibilidad de locks en MySQL y MariaDB y documentar resultados en specs/001-auth-session/quickstart.md
- [X] T100 [P] Crear guía de backup/restauración de users, sesiones, eventos y auditoría en docs/backup-restore.md
- [X] T101 [P] Crear procedimiento Apache/Nginx, public/, assets precompilados, debug off y rollback en docs/deployment.md
- [X] T102 [P] Añadir prueba de presupuesto p95 <=2 s y query count del ciclo auth en tests/Performance/IdentityAccessPerformanceTest.php
- [X] T103 [P] Añadir pruebas de teclado, foco, lector de pantalla y responsive en tests/Browser/IdentityAccess/SessionAccessibilityTest.php
- [X] T104 Añadir pruebas de no-store y ausencia de secretos en respuestas/logs en tests/Feature/IdentityAccess/AuthSecurityHeadersTest.php
- [X] T105 Compilar assets Vite y validar manifest sin Chart.js en public/build/manifest.json
- [X] T106 Ejecutar y registrar todos los escenarios de specs/001-auth-session/quickstart.md en specs/001-auth-session/checklists/implementation-validation.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: inicia de inmediato.
- **Foundational (Phase 2)**: depende de Setup y bloquea todas las historias.
- **US1 (Phase 3)**: depende de Foundational.
- **US2 (Phase 4)**: usa servicios Foundational y puede probarse con sesiones factory; integración productiva sigue US1.
- **US3 (Phase 5)**: depende de la rotación de US2 y completa el MVP de seguridad.
- **US4 (Phase 6)**: depende de sesiones US1; puede avanzar después de US1 en paralelo con US2/US3 si coordina archivos compartidos.
- **US5 (Phase 7)**: depende del modelo de sesiones; puede avanzar después de US1 en paralelo con US2–US4.
- **Polish (Phase 8)**: depende de todas las historias incluidas en la entrega.

### User Story Dependency Graph

```text
Setup -> Foundational -> US1 -> US2 -> US3
                         |      |
                         +----> US4
                         +----> US5
US1 + US2 + US3 + US4 + US5 -> Polish
```

### Within Each User Story

- Las pruebas de aceptación y contrato se escriben y fallan antes del código correspondiente.
- Form Requests/Policies pueden avanzar en paralelo; acciones preceden controladores y rutas.
- Tareas que modifican `routes/identity-access.php`, `session-timer.js` o acciones compartidas se ejecutan en orden de ID.
- Cada checkpoint exige pruebas verdes y comparación con la historia antes de continuar.

### Parallel Opportunities

- T003, T004 y T006 son independientes después de T001/T002.
- T013–T018 y T022–T024 se distribuyen por archivos distintos después de sus migraciones/configuración.
- Todas las tareas de pruebas `[P]` de una historia pueden escribirse juntas antes de implementar.
- US4 y US5 pueden desarrollarse en paralelo con el endurecimiento de US2/US3 después de US1, evitando archivos compartidos.
- T096–T104 se reparten por health, migraciones, documentación, rendimiento, accesibilidad y headers.

## Parallel Examples

### User Story 1

```text
T028 tests/Feature/IdentityAccess/LoginWithUsernameTest.php
T029 tests/Feature/IdentityAccess/LoginWithEmailTest.php
T030 tests/Feature/IdentityAccess/InvalidLoginTest.php
T031 tests/Feature/IdentityAccess/LoginThrottleTest.php
T032 tests/Feature/IdentityAccess/ExpiredAccessTokenTest.php
T033 tests/Feature/IdentityAccess/ConcurrentUserSessionsTest.php
T034 tests/Feature/IdentityAccess/LoginContractTest.php
```

### User Story 2

```text
T042 tests/Browser/IdentityAccess/SessionTimerTest.php
T043 tests/Browser/IdentityAccess/SessionExpiryModalTest.php
T044 tests/Feature/IdentityAccess/RefreshSessionTest.php
T045 tests/Feature/IdentityAccess/LogoutTest.php
T046 tests/Feature/IdentityAccess/RefreshAfterExpiryTest.php
T047 tests/Feature/IdentityAccess/AbsoluteSessionExpiryTest.php
T048 tests/Feature/IdentityAccess/SessionLifecycleContractTest.php
```

### User Story 3

```text
T058 tests/Integration/IdentityAccess/RefreshReplayTest.php
T059 tests/Integration/IdentityAccess/ConcurrentRefreshTest.php
T060 tests/Browser/IdentityAccess/AuthenticationCleanupTest.php
T061 tests/Browser/IdentityAccess/NoSilentRefreshTest.php
T062 tests/Feature/IdentityAccess/BrowserCloseExpiryTest.php
T063 tests/Feature/IdentityAccess/RevokedSessionTest.php
```

### User Stories 4 And 5

```text
T073-T078 tests for administrative deactivation and races
T085-T089 tests for authorized paginated session history
```

## Implementation Strategy

### Secure MVP First

1. Complete Setup and Foundational.
2. Deliver US1 login and server-side authorization.
3. Deliver US2 explicit renewal/logout.
4. Deliver US3 expiry, replay and invalid-state handling.
5. Stop and validate US1–US3 as the minimum secure increment.

### Incremental Delivery

1. **MVP secure**: US1 + US2 + US3.
2. **Administration**: add US4 and validate revocation/audit.
3. **Traceability UI**: add US5 and validate isolation/pagination.
4. **Operational closure**: complete Phase 8 and quickstart.

## Notes

- `[P]` significa archivos distintos sin dependencia incompleta; no autoriza carreras sobre la misma DB o archivo.
- No crear tablas de Operations, BankingNetwork o DailyClosing en esta especificación.
- Ninguna tarea introduce Redis, workers permanentes, WebSockets, SPA, Docker productivo o tokens en storage web.
- Las tareas T058–T065 requieren MySQL/MariaDB real donde se indique; SQLite no sustituye locks InnoDB.

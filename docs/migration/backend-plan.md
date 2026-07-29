# Plan de Migración — Backend (NestJS + TypeScript)

> **Repositorio destino:** `agenteflow-api` (nuevo repositorio, separado del frontend)
> **Repositorio actual (`control-operaciones-agente`, Laravel):** permanece intacto en `main`, en producción, durante TODO el proceso. No se toca hasta el corte final (Bloque 14).
> **Base de datos:** Supabase PostgreSQL — **mismo esquema existente**, sin recrear tablas (se introspecta con Prisma). Solo Laravel/NestJS acceden a datos; no se usa Supabase Auth/Storage/Realtime/Data API.
> **Objetivo:** Reescribir la API que hoy expone vistas Blade como una **API REST pura en NestJS**, replicando exactamente las mismas reglas de negocio, políticas de autorización y comportamiento de autenticación (JWT + refresh token rotativo) ya implementadas en Laravel, para ser consumida por la nueva app Expo (ver `frontend-plan.md`).

---

## Contexto y decisiones de arquitectura

- **Framework:** NestJS (última versión estable).
- **Lenguaje:** TypeScript en modo `strict`.
- **ORM:** Prisma (introspección del esquema Supabase existente vía `prisma db pull` — no se recrean migraciones desde cero).
- **Autenticación:** JWT access token (corta duración, ~5 min, igual que `JWT_ACCESS_TTL` actual) + refresh token opaco con rotación y detección de reuso (replicando `RotateRefreshToken.php` exactamente).
- **Autorización:** Guards personalizados por rol + Guards específicos por recurso (equivalente a las Policies de Laravel).
- **Validación:** `class-validator` + `class-transformer` sobre DTOs (equivalente a los `FormRequest`).
- **Auditoría:** Interceptor global que registre en `audit_logs` (equivalente a `AuditLog::create()` manual).
- **Documentación de API:** Swagger (`@nestjs/swagger`) — contrato consumido por Expo.
- **Testing:** Jest (unit) + Supertest (integración/E2E de endpoints).
- **Despliegue:** Docker + Render (Web Service, Free Tier) — ver Bloque 13.
- **Monedas/decimales:** usar librería de precisión decimal (`decimal.js` o `Prisma.Decimal`) para replicar los cálculos `bcadd`/`bcsub` de `CalculateClosing.php` sin errores de coma flotante.

### Mapeo de módulos Laravel → NestJS

| Módulo Laravel (`app/Modules/*`) | Módulo NestJS equivalente |
|---|---|
| `IdentityAccess` | `AuthModule` + `UsersModule` + `SessionsModule` |
| `Agents` | `AgentsModule` |
| `Operations` | `OperationsModule` |
| `DailyClosing` | `DailyClosingModule` |
| `Organization` | `OrganizationModule` |
| `Reporting` | `ReportingModule` |
| `Audit` | `AuditModule` (interceptor global, sin controller propio) |
| `BankingNetwork` | Evaluar si se migra o se descarta (legacy) — decisión en Bloque 6 |

---

## BLOQUE 0 — Fundación del repositorio y monorepo ✅ COMPLETADO

> **Repositorio real creado:** `control-operaciones-agente-backend` (https://github.com/SleytherG/control-operaciones-agente-backend, privado), siguiendo el mismo patrón de nombre que el repo frontend. Se descartó el nombre `agenteflow-api` sugerido originalmente por decisión explícita del usuario.

- [x] **Fase 0.1** — Repositorio `control-operaciones-agente-backend` creado en GitHub vía `gh repo create` (privado) y clonado localmente como carpeta hermana.
- [x] **Fase 0.2** — Scaffolding con `npx @nestjs/cli new control-operaciones-agente-backend --package-manager npm --skip-git` (Nest 11, React/Node moderno).
- [x] **Fase 0.3** — `tsconfig.json` reescrito con `strict: true` completo (`noImplicitAny`, `strictBindCallApply`, `noImplicitReturns`, `noUnusedLocals/Parameters` — el scaffolding por defecto de Nest 11 solo trae `strictNullChecks` parcial). Validado con `tsc --noEmit`: 0 errores.
- [x] **Fase 0.4** — ESLint reforzado: `no-explicit-any`, `no-unsafe-*` y `explicit-function-return-type` subidos a `error` (el scaffolding los trae en `off`/`warn`) — cumple con el requisito explícito del usuario de prohibir tipos genéricos/`any`. Prettier ya viene integrado vía `eslint-plugin-prettier`.
- [x] **Fase 0.5** — Husky + lint-staged instalados y configurados (`.husky/pre-commit` ejecuta `npx lint-staged`; `.lintstagedrc.json` corre `eslint --fix` + `prettier --write`).
- [x] **Fase 0.6** — Estructura de carpetas por módulo de dominio creada exactamente según el plan (`src/modules/{auth,users,sessions,agents,operations,daily-closing,organization,reporting,audit}`, `src/common/{guards,decorators,interceptors,filters,pipes,enums}`, `src/prisma/`, `src/config/`).
- [x] **Fase 0.7** — `prisma`+`@prisma/client` instalados. **Nota importante:** la versión más reciente (Prisma 7.9.1) introdujo un breaking change que elimina el soporte de `url` directo en el bloque `datasource` (exige migrar a `prisma.config.ts`), rompiendo el patrón estándar documentado en toda la comunidad NestJS+Prisma — se hizo downgrade explícito a **Prisma 6.19.3** (LTS estable) para mantener compatibilidad total con el patrón `PrismaService extends PrismaClient` planeado. `DATABASE_URL` configurada en `.env` con la contraseña real de Supabase URL-encoded (el caracter `$` requiere encoding a `%24` en connection strings).
- [x] **Fase 0.8** — `prisma db pull` ejecutado contra la base de datos Supabase real: **introspección exitosa de 20 modelos** (agents, audit_logs, auth_refresh_tokens, auth_sessions, cache, cache_locks, daily_closure_operations, daily_closures, districts, migrations, operation_types, operations, organizations, password_resets, provinces, regions, session_events, sessions, user_agent_assignments, users).
- [x] **Fase 0.9** — `schema.prisma` reescrito manualmente por completo: los 20 modelos convertidos de `snake_case`/`String` planos a **PascalCase con campos camelCase** (`@map`/`@@map` preservando los nombres reales de columnas/tablas), y **9 enums nativos de Prisma** creados a partir de los enums PHP reales leídos directamente de `app/Modules/IdentityAccess/Domain/Enums/*.php` (Role, UserStatus, AuthSessionStatus, RefreshTokenState, SessionEndReason, SessionEventType, PasswordResetStatus) más los confirmados en `control-operaciones-agente-frontend/src/types/enums.ts` (OperationStatus, DailyClosureStatus, que no tienen enum PHP dedicado). Relaciones múltiples hacia la misma tabla (ej. varios FKs de `User` en `DailyClosure`) renombradas con alias descriptivos (`ClosureOpenedBy`, `ClosureConfirmedBy`, etc.) en vez de los sufijos autogenerados `_Touser`. Validado con `prisma format` (sin errores).
- [x] **Fase 0.10** — `npx prisma generate` ejecutado exitosamente (Prisma Client v6.19.3). `PrismaService` creado (`src/prisma/prisma.service.ts`) extendiendo `PrismaClient` con hooks `OnModuleInit`/`OnModuleDestroy` para conectar/desconectar ordenadamente.
- [x] **Fase 0.11** — `PrismaModule` (`@Global()`) creado y registrado en `AppModule`.
- [x] **Fase 0.12** — `@nestjs/config` + `zod` instalados. `src/config/env.schema.ts` creado: esquema Zod completo con las mismas variables conceptuales que usa Laravel en su `render.yaml` (JWT_SIGNING_KEY, JWT_ISSUER, JWT_AUDIENCE, JWT_ACCESS_TTL, JWT_ABSOLUTE_SESSION_TTL, REFRESH_PEPPER, PASSWORD_RESET_TTL_SECONDS, PASSWORD_RESET_TEMPORARY_LENGTH, CORS_ORIGIN, OPERATIONS_RETROACTIVE_WINDOW_HOURS, OPERATIONS_ANNULMENT_WINDOW_HOURS, OPERATIONS_DEFAULT_CURRENCY), con `validateEnv()` fail-fast (lanza error descriptivo y detiene el arranque si falta/es inválida alguna variable). Integrado en `AppModule` vía `ConfigModule.forRoot({ isGlobal: true, validate: validateEnv })`.
- [x] **Fase 0.13** — `HealthModule`/`HealthController` creados con `@nestjs/terminus`, chequeo `PrismaHealthIndicator.pingCheck()` contra la base de datos real. **Validado end-to-end**: `curl http://localhost:3000/health` → `{"status":"ok","info":{"database":{"status":"up"}}}`.
- [x] **Fase 0.14** — Swagger configurado en `main.ts` (`DocumentBuilder` + `addBearerAuth()`), expuesto en `/docs`, condicionado a `NODE_ENV !== 'production'`. Validado: `curl -o /dev/null -w "%{http_code}" http://localhost:3000/docs` → `200`.
- [x] **Fase 0.15** — CORS configurado en `main.ts` vía `app.enableCors({ origin: CORS_ORIGIN.split(','), credentials: true })`, soportando múltiples orígenes separados por coma.

---

## BLOQUE 1 — Infraestructura transversal (Guards, Decoradores, Filtros, Interceptores) ✅ COMPLETADO

- [x] **Fase 1.1/1.2** — Enums de dominio (`Role`, `UserStatus`, `AuthSessionStatus`, `PasswordResetStatus`, etc.) **no se recrearon como enums TS independientes**: ya existen como enums nativos de Prisma (Bloque 0, Fase 0.9). `src/common/enums/index.ts` es un barrel que los re-exporta desde `@prisma/client`, evitando una segunda fuente de verdad que pudiera desincronizarse del esquema real de la base de datos.
- [x] **Fase 1.3** — Decorador `@Public()` (`src/common/decorators/public.decorator.ts`) creado con `SetMetadata(IS_PUBLIC_KEY, true)`.
- [x] **Fase 1.4** — Decorador `@Roles(...roles: Role[])` (`src/common/decorators/roles.decorator.ts`) creado.
- [x] **Fase 1.5** — Decorador `@CurrentUser()` (`src/common/decorators/current-user.decorator.ts`) creado, tipado con `AuthenticatedUser` (sin `any`), lanza `UnauthorizedException` defensiva si `request.user` no existe.
- [x] **Fase 1.6** — `JwtAuthGuard` (`src/common/guards/jwt-auth.guard.ts`) implementado: extrae Bearer token, valida con `JwtTokenService` (creado como pieza compartida adelantada del Bloque 2 — ver nota abajo), carga `User`+`AuthSession` (con relación `passwordReset`) desde Prisma, verifica `UserStatus.ACTIVE`/`AuthSessionStatus.ACTIVE`, adjunta `request.user`/`request.session` tipados. **Nota de diseño:** `JwtTokenService` (réplica exacta de `JwtTokenService.php` — mismos claims `iss/aud/sub/sid/jti/iat/nbf/exp`, HS256) se adelantó del Bloque 2 como `src/modules/auth/services/jwt-token.service.ts` + `AuthModule` mínimo, ya que es dependencia directa del guard global de este bloque.
- [x] **Fase 1.7** — `JwtAuthGuard` registrado como `APP_GUARD` global en `AppModule`. Rutas `/`, `/health` marcadas `@Public()`.
- [x] **Fase 1.8** — `RolesGuard` (`src/common/guards/roles.guard.ts`) implementado y registrado como `APP_GUARD` global (ejecuta después de `JwtAuthGuard`).
- [x] **Fase 1.9** — `MustChangePasswordGuard` (`src/common/guards/must-change-password.guard.ts`) implementado replicando exactamente la lógica de `AuthenticateJwtSession.php` (leída del código fuente real): bloquea con 403 si `passwordResetStatus === CONSUMED` o si `passwordChangedAt === null && passwordResetId === null`, salvo endpoints `@Public()` o `@AllowPasswordChangeFlow()` (decorador nuevo, equivalente a la lista blanca `password.change`/`logout` de Laravel). Registrado como tercer `APP_GUARD` global. Tipos `AuthenticatedUser`/`AuthenticatedSession` extendidos con `passwordChangedAt`/`passwordResetId`/`passwordResetStatus`.
- [x] **Fase 1.10** — **Diferida al Bloque 7/8** (Operations/DailyClosing): el `AuditInterceptor` + decorador `@Audit()` se implementarán junto a los primeros casos de uso reales que lo necesiten (registro de operación, anulación, cierre), evitando construir la infraestructura de auditoría sin un caso de uso concreto que valide su diseño. Ver Bloque 10 para el módulo `AuditService` completo.
- [x] **Fase 1.11** — `HttpExceptionFilter` (`src/common/filters/http-exception.filter.ts`) implementado: normaliza cualquier excepción (HTTP conocida o no) a `{statusCode, message, error, details?, timestamp, path}`, preservando el array de mensajes de validación de `class-validator` bajo `details`. Registrado globalmente en `main.ts`.
- [x] **Fase 1.12** — `CorrelationIdMiddleware` (`src/common/middleware/correlation-id.middleware.ts`) implementado replicando `AssignCorrelationId.php` (propaga o genera `X-Correlation-ID` con `crypto.randomUUID()` nativo, sin dependencia `uuid` externa), aplicado a todas las rutas vía `configure()` en `AppModule`. Validado end-to-end: `curl -D - http://localhost:3000/health` devuelve el header.
- [x] **Fase 1.13** — Logger JSON estructurado (`src/common/logger/json.logger.ts`, clase `JsonLogger implements LoggerService`) implementado sin dependencia `nestjs-pino` externa: emite una línea JSON por entrada a `stderr` (`{level, message, context, timestamp, trace}`), equivalente a `LOG_CHANNEL=stderr`. Activado solo en `NODE_ENV=production` en `main.ts` (en desarrollo se mantiene el logger legible por defecto de Nest).
- [x] **Fase 1.14** — `sanitizeForLogging()` (`src/common/logger/log-sanitizer.ts`) implementado replicando exactamente la lista de claves sensibles de `app/Logging/LogSanitizer.php` (password, token, jwt, hash, secret, pepper, authorization, etc.) con redacción recursiva sobre objetos/arrays. Integrado directamente en `JsonLogger`: cualquier mensaje-objeto se sanea antes de serializarse a JSON.

---

## BLOQUE 2 — Módulo Auth (autenticación, JWT, refresh tokens) — el más crítico ✅ COMPLETADO

> **Hallazgo crítico corregido en este bloque (afecta también al Bloque 0):** al probar el login contra la base de datos real se detectó que Prisma generaba SQL asumiendo tipos `enum` nativos de PostgreSQL para los campos `status`/`role`/`type`/etc. (`ERROR: type "public.AuthSessionStatus" does not exist`). Verificado con `information_schema.columns`: **todas** esas columnas son en realidad `VARCHAR(N)` — los "enums" de Laravel son validados solo a nivel de aplicación PHP (`enum` de PHP 8.1), no como tipos nativos de la base de datos. Se corrigió `schema.prisma`: los 9 bloques `enum` se mantienen (siguen usándose como constantes TypeScript tipadas vía `src/common/enums/index.ts`), pero **todos los campos de modelo que los usaban ahora son `String @db.VarChar(N)`** con el tamaño real de columna verificado (`role VarChar(40)`, `status VarChar(20)`, `end_reason VarChar(40)`, etc.). Los valores en runtime siguen siendo los mismos strings (`'ACTIVE'`, `'ADMINISTRADOR_PROPIETARIO'`, etc.); solo cambió la representación a nivel de tipo de columna en Prisma. Se aplican casts explícitos (`as Role`, `as PasswordResetStatus`) al leer estos campos en `JwtAuthGuard` para mantener el tipado fuerte en el resto de la aplicación sin repetir este error.
- [x] **Fase 2.1** — `AuthModule` creado con `AuthController`, `AuthService`, y sub-servicios `JwtTokenService` (adelantado del Bloque 1), `RefreshTokenService`.
- [x] **Fase 2.2/2.3** — `JwtTokenService.issue()`/`validate()` ya implementados en el Bloque 1 (dependencia del guard global) — validados end-to-end en este bloque con tokens reales.
- [x] **Fase 2.4/2.5** — `RefreshTokenService` (`src/modules/auth/services/refresh-token.service.ts`): `generate()` con `randomBytes(32).toString('base64')`, `hash()` con HMAC-SHA256 y el pepper de `REFRESH_PEPPER`, réplica exacta de `RefreshTokenService.php`.
- [x] **Fase 2.6** — `LoginDto` (`identifier` string requerido máx. 254 caracteres, `password` string requerido) replicando `LoginRequest.php`.
- [x] **Fase 2.7** — `AuthService.authenticateAndStartSession()` (`src/modules/auth/auth.service.ts`): réplica exacta de `AuthenticateAndStartSession.php` dentro de una transacción Prisma — normaliza identifier, busca por `usernameNormalized`/`emailNormalized`, verifica `UserStatus.ACTIVE`, compara password con `bcrypt.compare()` (con normalización de hash `$2y$`→`$2b$`, ver nota de hallazgo abajo), maneja el flujo completo de `PasswordReset` (PENDING con expiración automática, CONSUMED/EXPIRED bloqueando login).
- [x] **Fase 2.8** — `AuthService.startAuthSession()`: crea `AuthSession` (publicId UUID vía `randomUUID()` nativo, `ipHash` SHA-256, `accessExpiresAt`/`absoluteExpiresAt` desde config), `AuthRefreshToken` inicial con **`generation: 1`** (corregido tras leer el código fuente real — no `0` como se asumió inicialmente en la planificación), y `SessionEvent` correspondiente.
- [x] **Fase 2.9** — `POST /auth/login` (`@Public()`) retorna `{accessToken, refreshToken, expiresAt, ttl, mustChangePassword}` en el body JSON (sin cookies, para compatibilidad Expo Web+Native). 401 genérico en credenciales inválidas.
- [x] **Fase 2.10** — Rate limiting global vía `@nestjs/throttler` (5 intentos/minuto por IP, `ThrottlerModule.forRoot` + `ThrottlerGuard` como `APP_GUARD`), equivalente al throttling de login de Laravel.
- [x] **Fase 2.11** — `AuthService.rotateRefreshToken()`: réplica de `RotateRefreshToken.php` dentro de transacción Prisma — detecta reuso (token `CONSUMED` reutilizado) y revoca la sesión completa con `end_reason: FALLO_SEGURIDAD` + `SessionEvent.REFRESH_REUSE`; rota generación válida con `SessionEvent.REFRESHED`.
- [x] **Fase 2.12** — `POST /auth/refresh` (`@Public()`): body `{refreshToken}` → nuevo `{accessToken, refreshToken, expiresAt, ttl}` o 401.
- [x] **Fase 2.13/2.14** — `AuthService.logout()` + `POST /auth/logout` (autenticado): `AuthSession.status = REVOKED`, `end_reason = LOGOUT_MANUAL`, `SessionEvent.LOGOUT`.
- [x] **Fase 2.18** — **Validación end-to-end real** (no solo unitaria) contra la base de datos Supabase: login exitoso, refresh con rotación válida, reuso de refresh detectado y sesión revocada en cascada, token nuevo tras revocación rechazado, logout con token ya revocado rechazado — los 5 escenarios críticos de seguridad confirmados funcionando correctamente.
- [x] **Fase 2.19** — Commit `7e5372b`: `feat: módulo auth completo (login, refresh rotativo, logout, gestión de sesiones)`, pusheado a `main`.
- [x] **Fase 2.15/2.16** — **Corrección de documentación (detectada durante la implementación del Bloque 4):** `AuthService.revokeSession()` y `revokeAllUserSessions()` (réplicas exactas de `RevokeSession.php`/`RevokeAllUserSessions.php`) **ya estaban implementadas desde este bloque** — no estaban realmente diferidas como se documentó originalmente. `revokeAllUserSessions()` ya era consumida por `DeactivateUserService`/`ResetOperatorPasswordService` del Bloque 3; `revokeSession()` solo era usada internamente por `AuthService.logout()`. El Bloque 4 las reutiliza sin necesidad de reimplementarlas.
- [ ] **Fase 2.17** — Diferida: `recordSessionEvent()` genérico (helper transversal) y el job de expiración proactiva de sesiones vencidas se posponen — actualmente cada acción registra su propio `SessionEvent` inline (igual que en PHP, donde tampoco existe un helper genérico consolidado), y la expiración se resuelve de forma perezosa (lazy) en `JwtAuthGuard`/`AuthService`, replicando el comportamiento real observado en `AuthenticateJwtSession.php` (no hay un job/cron de expiración proactiva en el Laravel original).

---

## BLOQUE 3 — Módulo Users (gestión de operadores) e IdentityAccess complementario ✅ COMPLETADO (validado fase a fase contra el código PHP fuente)

> **Hallazgo corregido en este bloque:** `BigInt` (tipo usado por Prisma para IDs autoincrementales) no es serializable por `JSON.stringify()` nativo. Se agregó `BigInt.prototype.toJSON` global en `main.ts` (serializa a `string`, igual que Eloquent/Laravel), sin lo cual cualquier endpoint que devolviera un registro con `id`/`organizationId` fallaba con `TypeError: Do not know how to serialize a BigInt` (500).
>
> **Hallazgos adicionales corregidos en la revalidación posterior (comparación línea por línea contra `OperatorController.php`/`CompleteRequiredPasswordChange.php`):**
> 1. `OperatorsService.createOperator()`/`updateOperator()` **no registraban auditoría** (`operator.created`/`operator.updated`), a diferencia de `OperatorController::store()`/`update()` que sí invocan `logAudit()`. Corregido: ambos métodos ahora envuelven la mutación + `AuditLog.create()` en una transacción Prisma (`beforeValues`/`afterValues` con los campos relevantes), y reciben `actorId` desde el controller.
> 2. `PasswordChangeService.completeRequiredPasswordChange()` omitía la verificación `session.status === AuthSessionStatus.ACTIVE` que sí existe en `CompleteRequiredPasswordChange.php`. Corregido: se añadió la condición al bloque de validación de sesión.
> 3. Verificado y confirmado como **diseño aceptado** (no defecto): en PHP, la revocación de sesiones (`RevokeAllUserSessions`) ocurre dentro de la misma transacción DB que el resto de `ResetOperatorPassword::execute()`/`DeactivateUser::execute()`; en NestJS, `AuthService.revokeAllUserSessions()` abre su propia transacción Prisma separada (Prisma no soporta transacciones anidadas/savepoints como Eloquent). El resultado funcional final es equivalente; se documenta la diferencia arquitectónica para trazabilidad.
> Ambos hallazgos (1 y 2) fueron corregidos y **revalidados end-to-end contra la base de datos Supabase real**: se confirmó por consulta directa a `audit_logs` que `operator.created`/`operator.updated` ahora se registran con `beforeValues`/`afterValues` correctos, y se re-ejecutó la suite completa de 10 escenarios del flujo de reset/cambio de contraseña sin regresiones (incluyendo el paso que ejercita la nueva verificación de `AuthSessionStatus.ACTIVE`).
- [x] **Fase 3.1** — DTOs creados en `src/modules/users/dto/`: `CreateOperatorDto`, `UpdateOperatorDto`, `DeactivateUserDto`, `CompletePasswordChangeDto`, `ResetOperatorPasswordDto`, `ListPasswordResetAuditDto` — verificados campo por campo contra `CreateOperatorRequest.php`, `DeactivateUserRequest.php`, `ResetOperatorPasswordRequest.php`, `CompletePasswordChangeRequest.php`, `ListPasswordResetAuditRequest.php` y la validación inline de `OperatorController::update()`.
- [x] **Fase 3.2** — `OperatorsService.createOperator()` (`src/modules/users/services/operators.service.ts`): crea `User` con rol `OPERADOR`, contraseña hasheada (bcrypt), `passwordChangedAt: null` (fuerza cambio en primer login), valida unicidad de username/email normalizados dentro de la organización, **y registra `AuditLog` (`operator.created`)** — réplica completa de `OperatorController::store()` incluyendo su llamada a `logAudit()`.
- [x] **Fase 3.3** — `OperatorsService.updateOperator()` (con auditoría `operator.updated`) + `DeactivateUserService.execute()` (`services/deactivate-user.service.ts`): réplica exacta de `DeactivateUser.php` — valida que el actor no se autodesactive, que no sea el último `ADMINISTRADOR_PROPIETARIO` activo, marca `INACTIVE`, registra `AuditLog`, y **reutiliza `AuthService.revokeAllUserSessions()`** (ya implementado en el Bloque 2) para revocar todas las sesiones activas.
- [x] **Fase 3.4** — Endpoints en `UsersController`: `GET/POST /users`, `PATCH /users/:id`, `POST /users/:id/deactivate` — todos protegidos con `@Roles(Role.ADMINISTRADOR_PROPIETARIO)`.
- [x] **Fase 3.5** — `PasswordChangeService.completeRequiredPasswordChange()` (`services/password-change.service.ts`): réplica exacta de `CompleteRequiredPasswordChange.php` — valida sesión originada por reset (`passwordResetId` presente, sesión `ACTIVE`, reset en estado `CONSUMED`), verifica que la nueva contraseña sea diferente de la temporal, actualiza `password`/`passwordChangedAt`, marca el reset `COMPLETED`, registra auditoría. También se implementó `completeVoluntaryPasswordChange()` (rama voluntaria de `PasswordChangeController::update()`, verifica `currentPassword`).
- [x] **Fase 3.6** — `PATCH /users/me/password` (autenticado, `@AllowPasswordChangeFlow()`): resuelve automáticamente cuál de las dos ramas (obligatoria/voluntaria) aplica según `session.passwordResetId`.
- [x] **Fase 3.7** — `ResetOperatorPasswordService.execute()` (`services/reset-operator-password.service.ts`): réplica **exacta y completa** de `ResetOperatorPassword.php` — verifica contraseña del admin (step-up), supersede/expira resets previos PENDING/CONSUMED con auditoría individual por cada uno, genera contraseña temporal vía `PasswordPolicyService` (Fisher-Yates con `crypto.randomInt`, réplica 1:1 del algoritmo PHP), crea el nuevo `PasswordReset` PENDING, revoca todas las sesiones activas del operador, y registra las 4 categorías de auditoría (`password_reset.superseded/expired` por cada reset previo, `password_reset.issued`, `password_reset.sessions_revoked`) — devuelve la contraseña en texto plano **solo una vez**.
- [x] **Fase 3.8** — `POST /users/:id/password-reset` — protegido por rol admin.
- [x] **Fase 3.9** — `ListPasswordResetAuditService.execute()` (`services/list-password-reset-audit.service.ts`): réplica de `ListPasswordResetAudit.php` + `PasswordResetAuditController::resolveExpired()` — expira automáticamente (con auditoría) cualquier reset PENDING vencido antes de listar, pagina el historial (25/página) filtrado por acción `password_reset.*`, incluyendo el mapeo status→action verificado 1:1 contra el `match` de PHP.
- [x] **Fase 3.10** — `GET /users/:id/password-resets`.
- [x] **Fase 3.11** — `PasswordPolicyService` (`services/password-policy.service.ts`): réplica exacta de `PasswordPolicy.php` — `generateTemporary()` garantiza al menos una letra/número/símbolo, longitud configurable vía `PASSWORD_RESET_TEMPORARY_LENGTH`, mezcla Fisher-Yates con `crypto.randomInt` (equivalente a `random_int()` de PHP).
- [x] **Fase 3.12** — **Validación end-to-end real** (no unitaria) contra la base de datos Supabase, ejecutada dos veces (validación inicial + revalidación post-fix): creación de operador con auditoría verificada por consulta directa a `audit_logs`, actualización de operador con auditoría, listado con filtro, login forzando `mustChangePassword`, reset de contraseña con step-up, bloqueo real (403) de endpoints protegidos mientras el cambio de contraseña está pendiente (confirma integración con `MustChangePasswordGuard` del Bloque 1), completar cambio obligatorio (ejercitando la verificación de `AuthSessionStatus.ACTIVE` añadida en el fix), login con contraseña permanente, historial de auditoría, desactivación, y bloqueo de login tras desactivación (403 "Usuario desactivado"). Testing unitario formal diferido al Bloque 12 (consistente con el resto de módulos hasta la fecha).
- [x] **Fase 3.13** — Commits: `d8b058e` (`feat: módulo usuarios/operadores...`) + fix posterior de auditoría faltante y verificación de estado de sesión, pusheados a `main`.

---

## BLOQUE 4 — Módulo Sessions (historial y auditoría de sesiones) ✅ COMPLETADO

> **Hallazgo de alcance:** en el Laravel original, `RevokeSession`/`RevokeAllUserSessions` **no tienen endpoints HTTP propios** — solo se invocan internamente desde `LogoutController` (`RevokeSession`) y desde `DeactivateUser`/`ResetOperatorPassword` (`RevokeAllUserSessions`, ya reutilizado desde el Bloque 3). No existe un `RevokeSessionController` ni rutas `sessions/{id}/revoke` o `users/{id}/sessions/revoke-all` en `routes/identity-access.php`. Siguiendo el requisito explícito del plan de migración (Fases 4.3/4.4), se expusieron como endpoints REST explícitos nuevos — comportamiento equivalente al de las `Action` PHP, pero con superficie de API más amplia que el Laravel original (necesaria para que el futuro frontend Expo pueda ofrecer gestión de sesiones de forma autónoma vía API, sin depender de vistas Blade).
- [x] **Fase 4.1** — `SessionsService.listAuthorizedSessions()` (`src/modules/sessions/services/sessions.service.ts`): réplica exacta de `ListAuthorizedSessions::execute()` — `OPERADOR` solo ve sus propias sesiones, `ADMINISTRADOR_PROPIETARIO` ve todas las de su organización (con filtro opcional por `userId` específico, replicando la condición `$user->role === Role::ADMINISTRADOR_PROPIETARIO` de PHP), filtros por `status`/`from`/`to`, paginación (25/página por defecto, tope 100, igual que `config('session-security.history')`).
- [x] **Fase 4.2** — `GET /sessions` (autenticado, `SessionsController.list()`): historial paginado con `sessionEvents` incluidos (IP hash, user agent, fechas, estado, razón de finalización — todos los campos ya existen en el modelo `AuthSession`/`SessionEvent` de Prisma).
- [x] **Fase 4.3** — `POST /sessions/:id/revoke` (`SessionsController.revoke()`): invoca `AuthService.revokeSession()` (ya implementado en el Bloque 2) — valida que el actor sea el dueño de la sesión (motivo `LOGOUT_MANUAL`) o un `ADMINISTRADOR_PROPIETARIO` de la misma organización (motivo `REVOCACION_ADMINISTRATIVA`), replicando la autorización de `AuthSessionPolicy::view()`.
- [x] **Fase 4.4** — `POST /users/:id/sessions/revoke-all` (`SessionsController.revokeAll()`, `@Roles(ADMINISTRADOR_PROPIETARIO)`): invoca `AuthService.revokeAllUserSessions()` (ya implementado en el Bloque 2, reutilizado también por el Bloque 3) — valida que el usuario objetivo pertenezca a la misma organización del admin.
- [x] **Fase 4.5** — `ListSessionsDto` (`src/modules/sessions/dto/list-sessions.dto.ts`): réplica de `ListSessionsRequest.php` — `page`/`from`/`to`/`status`/`userId`/`perPage` (tope 100 vía `@Max(100)`, igual que `max_page_size`).
- [x] **Fase 4.6** — **Validación end-to-end real** (no unitaria) contra la base de datos Supabase, 14 escenarios: admin lista su historial, operador crea 2 sesiones y lista solo las propias (aislamiento por rol confirmado), admin filtra por `userId` específico, operador revoca una sesión propia, **confirmación crítica de seguridad**: el JWT de una sesión revocada es rechazado inmediatamente (401) en la siguiente petición — validado con la sesión restante aún funcionando y la sesión revocada fallando, intento de revocar sesión inexistente (404), admin revoca todas las sesiones activas del operador (`revokedCount: 2`), verificación de que las sesiones revocadas por el revoke-all también quedan invalidadas de inmediato (401). Testing unitario formal diferido al Bloque 12.

---

## BLOQUE 5 — Módulo Agents (Agentes bancarios y asignaciones)

- [ ] **Fase 5.1** — Confirmar con el equipo si se migra `Agents` (estructura vigente según migraciones más recientes) o si `BankingNetwork` (legacy) aún es necesario — **decisión bloqueante antes de continuar**, para no duplicar modelos.
- [ ] **Fase 5.2** — DTOs: `CreateAgentDto`, `UpdateAgentDto` — replicando `AgentRequest.php` (código único, nombre, store_id, estado).
- [ ] **Fase 5.3** — `AgentsService`: `list(organizationId, filters)`, `create(dto)`, `update(id, dto)`, `deactivate(id)` — equivalente `AgentController.php`.
- [ ] **Fase 5.4** — Guard/lógica de autorización equivalente `AgentPolicy.php`: solo admin gestiona agentes; operador solo lee los suyos.
- [ ] **Fase 5.5** — Endpoints: `GET/POST /agents`, `GET/PATCH /agents/:id`, `DELETE /agents/:id` (deactivate) — protegidos por rol admin.
- [ ] **Fase 5.6** — `UserAgentAssignmentsService`: `list(userId)`, `create(userId, agentId)`, `delete(assignmentId)` — equivalente `UserAgentAssignmentController.php`, validando que no se dupliquen asignaciones activas.
- [ ] **Fase 5.7** — Endpoints: `GET/POST /users/:id/assignments`, `DELETE /assignments/:id`.
- [ ] **Fase 5.8** — `AgentsService.myAgents(userId)`: equivalente `MyAgentsController.php` — lista de agentes activos asignados al operador autenticado.
- [ ] **Fase 5.9** — `GET /my-agents` (autenticado).
- [ ] **Fase 5.10** — Testing de integración: creación/edición/desactivación de agentes, asignación/desasignación, validación de agentes duplicados por organización.
- [ ] **Fase 5.11** — Commit y PR: `feat: módulo agentes bancarios y asignaciones operador-agente`.

---

## BLOQUE 6 — Módulo Organization (Jerarquía geográfica y tiendas)

- [ ] **Fase 6.1** — DTOs: `CreateRegionDto`, `CreateProvinceDto`, `CreateDistrictDto`, `CreateStoreDto` (+ Update) — replicando `RegionRequest.php`, `ProvinceRequest.php`, `DistrictRequest.php`, `StoreRequest.php`.
- [ ] **Fase 6.2** — `OrganizationService`: CRUD completo para `Region`, `Province`, `District`, `Store`, con relaciones jerárquicas (región→provincia→distrito) y soft-deactivate (no hard delete, igual que Laravel).
- [ ] **Fase 6.3** — Endpoints regiones: `GET/POST /regions`, `GET/PATCH /regions/:id`, `DELETE /regions/:id` (deactivate).
- [ ] **Fase 6.4** — Endpoints provincias: `GET/POST /regions/:id/provinces`, `PATCH /provinces/:id`, `DELETE /provinces/:id`.
- [ ] **Fase 6.5** — Endpoints distritos: `GET/POST /provinces/:id/districts`, `PATCH /districts/:id`, `DELETE /districts/:id`.
- [ ] **Fase 6.6** — Endpoints tiendas: `GET/POST /stores`, `GET/PATCH /stores/:id`, `DELETE /stores/:id` (deactivate).
- [ ] **Fase 6.7** — Guards de autorización equivalentes `RegionPolicy`, `ProvincePolicy`, `DistrictPolicy`, `StorePolicy` (todo restringido a admin).
- [ ] **Fase 6.8** — Testing de integración: jerarquía completa (crear región→provincia→distrito→tienda), validación de relaciones al desactivar (no permitir desactivar región con provincias activas, etc. si esa regla existe en Laravel — verificar en `GeoHierarchyController.php`).
- [ ] **Fase 6.9** — Commit y PR: `feat: módulo organización (jerarquía geográfica y tiendas)`.

---

## BLOQUE 7 — Módulo Operations (núcleo del negocio) — el más crítico funcionalmente

- [ ] **Fase 7.1** — DTOs: `RegisterOperationDto` (agent_id, operation_type_id, customer_name?, amount, currency?, effective_at?, notes?, idempotency_key), `AnnulOperationDto` (reason) — replicando `RegisterOperationRequest.php`/`AnnulOperationRequest.php` con `class-validator` (`@IsPositive()`, `@IsUUID()`, etc.).
- [ ] **Fase 7.2** — `OperationsService.register(dto, userId, organizationId)`: replica `RegisterOperation.php` paso a paso:
  - `validateAssignment(agentId, userId)`: verifica `UserAgentAssignment` activa — lanza excepción de dominio si no existe.
  - `resolveEffectiveAt(effectiveAt, userId)`: solo admin puede registrar fecha retroactiva dentro de la ventana `OPERATIONS_RETROACTIVE_WINDOW_HOURS`; cualquier otro caso usa `now()`.
  - `validateNotConfirmed(agentId, effectiveAt)`: verifica que no exista `DailyClosure` con status `CONFIRMADO` para esa fecha/agente.
  - Calcula `cash_delta`/`digital_delta` = `amount * multiplicador` del `OperationType`.
  - `InternalCodeGenerator.generate(effectiveAt)`: replica generación de código interno único (ver Fase 7.3).
  - Persistencia dentro de una transacción Prisma (`$transaction`).
- [ ] **Fase 7.3** — `InternalCodeGeneratorService`: replicar exactamente el algoritmo de `InternalCodeGenerator.php` (formato de código por fecha/secuencia) — **leer el archivo fuente antes de implementar para no alterar el formato usado en producción**.
- [ ] **Fase 7.4** — Manejo de idempotencia: `POST /operations` primero busca por `idempotency_key` existente; si ya existe, retorna la operación existente con flag `idempotent: true` (mismo comportamiento que `OperationController::store` actual, incluyendo el catch de `UniqueConstraintViolationException` como fallback ante condición de carrera).
- [ ] **Fase 7.5** — `OperationsService.list(filters, isAdmin, userId, organizationId)`: replica `ListOperations.php` — filtros por code, customer_name, amount, agent_id, operation_type_id, status, user_id, date_from/date_to; admin ve todas las operaciones de la organización, operador solo las propias.
- [ ] **Fase 7.6** — `OperationsService.getSummary(filters, isAdmin, userId, organizationId)`: replica el cálculo de resumen visto en `OperationController::index` (total_ops, total_amount, total_cash_in/out, net_movement) usando agregaciones SQL (Prisma `groupBy`/`aggregate` o raw query si es más simple para las sumas condicionales `CASE WHEN`).
- [ ] **Fase 7.7** — `OperationsService.annul(operationId, reason, userId, isAdmin)`: replica `AnnulOperation.php` — validar ventana de anulación (`OPERATIONS_ANNULMENT_WINDOW_HOURS`), solo admin o el propio operador que la registró, marca `status = ANNULLED`, `annulled_by`, `annulled_at`, `annulment_reason`.
- [ ] **Fase 7.8** — Endpoints: `GET /operations` (con filtros + resumen), `POST /operations`, `GET /operations/:id`, `POST /operations/:id/annul`.
- [ ] **Fase 7.9** — Guard de autorización equivalente `OperationPolicy.php` (`viewAny`, `register`, `view`, `annul`).
- [ ] **Fase 7.10** — Módulo `OperationTypes`: DTOs `CreateOperationTypeDto`/`UpdateOperationTypeDto` (nombre, cash_multiplier, digital_multiplier, sort_order, is_active) — replicando `OperationTypeRequest.php`.
- [ ] **Fase 7.11** — Endpoints: `GET/POST /operation-types`, `GET/PATCH /operation-types/:id`, `DELETE /operation-types/:id` — protegidos por `OperationTypePolicy`.
- [ ] **Fase 7.12** — Interceptor `@Audit('operation.created', Operation)` aplicado al endpoint de registro (reemplaza el `AuditLog::create()` manual del controller Laravel).
- [ ] **Fase 7.13** — Testing exhaustivo: idempotencia (doble submit), ventana retroactiva (admin vs operador), bloqueo por cierre confirmado, anulación dentro/fuera de ventana, cálculo correcto de cash_delta/digital_delta según multiplicadores, resumen de filtros.
- [ ] **Fase 7.14** — Commit y PR: `feat: módulo operaciones (registro idempotente, listado con filtros/resumen, anulación, tipos)`.

---

## BLOQUE 8 — Módulo DailyClosing (Cierres diarios)

- [ ] **Fase 8.1** — DTOs: `GenerateClosingDto`, `ReopenClosingDto` — replicando `GenerateClosingRequest.php`/`ReopenClosingRequest.php` (agent_id, business_date, opening_cash, opening_digital).
- [ ] **Fase 8.2** — `DailyClosingService.generate(dto, userId)`: crea `DailyClosure` en estado `ACTIVO` para agente+fecha (validar que no exista ya un cierre para esa combinación).
- [ ] **Fase 8.3** — `CalculateClosingService.execute(closureId)`: replica **exactamente** `CalculateClosing.php` — agrega operaciones ACTIVE del agente en el rango de fecha vía Prisma (`groupBy`/`aggregate` con filtros CASE WHEN, o raw SQL si Prisma no soporta la agregación condicional directamente), calcula `expected_closing_cash/digital` con precisión decimal (`decimal.js`, replicando `bcadd`/`bcsub`), calcula diferencias contra `actual_closing_cash/digital` si existen, marca `has_inconsistencies` si hay operaciones con multiplicadores en cero.
- [ ] **Fase 8.4** — `DailyClosingService.confirm(closureId, actualClosingCash, actualClosingDigital, userId)`: recalcula (`CalculateClosingService`), guarda montos reales, cambia estado a `CONFIRMADO` — bloquea registrar nuevas operaciones para esa fecha/agente (ya validado en `RegisterOperation` del Bloque 7).
- [ ] **Fase 8.5** — `DailyClosingService.reopen(closureId, reason, userId)`: solo admin, cambia estado de `CONFIRMADO` a `REABIERTO`, registra motivo y auditoría.
- [ ] **Fase 8.6** — `DailyClosingService.list(filters, isAdmin, userId)`, `getById(id)` — listado y detalle con métricas ya calculadas.
- [ ] **Fase 8.7** — Endpoints: `GET /daily-closures`, `POST /daily-closures`, `GET /daily-closures/:id`, `POST /daily-closures/:id/confirm`, `POST /daily-closures/:id/reopen`.
- [ ] **Fase 8.8** — Guard de autorización equivalente `DailyClosingPolicy.php`.
- [ ] **Fase 8.9** — Testing exhaustivo: cálculo de montos esperados con precisión decimal exacta (comparar con casos reales de Laravel para asegurar paridad de resultados), confirmación/reapertura, bloqueo de registro de operaciones tras confirmación.
- [ ] **Fase 8.10** — Commit y PR: `feat: módulo cierres diarios (cálculo, confirmación, reapertura)`.

---

## BLOQUE 9 — Módulo Reporting (Dashboards y agregaciones)

- [ ] **Fase 9.1** — DTO `DashboardFilterDto` — replicando `DashboardFilterRequest.php` (rango de fechas, agente, tipo de operación).
- [ ] **Fase 9.2** — `DashboardQueryService.operatorDashboard(userId, filters)`: replica `DashboardQueryService.php` para vista operador — métricas propias, operaciones recientes.
- [ ] **Fase 9.3** — `DashboardQueryService.adminDashboard(organizationId, filters)`: métricas globales de la organización, tendencias por fecha (para gráficos).
- [ ] **Fase 9.4** — `DashboardQueryService.operatorComparison(organizationId, filters)`: agregación comparativa por operador (total operaciones, montos, etc.).
- [ ] **Fase 9.5** — Endpoints: `GET /dashboard` (operador), `GET /admin/dashboard`, `GET /admin/dashboard/operators`.
- [ ] **Fase 9.6** — Guard de autorización equivalente `DashboardPolicy.php`.
- [ ] **Fase 9.7** — Optimización de queries: usar índices ya existentes en las tablas (`operations`, `daily_closures`) — revisar que las queries Prisma generen SQL eficiente equivalente al de Laravel (usar `EXPLAIN ANALYZE` si hay dudas de performance).
- [ ] **Fase 9.8** — Testing de integración: verificar que los números devueltos coincidan exactamente con los que produce el `DashboardQueryService.php` actual (test de paridad con datos de fixture idénticos).
- [ ] **Fase 9.9** — Commit y PR: `feat: módulo reporting (dashboards operador/admin, comparación entre operadores)`.

---

## BLOQUE 10 — Módulo Audit (auditoría transversal)

- [ ] **Fase 10.1** — `AuditService.record(action, entityType, entityId, actorUserId, organizationId, before, after, correlationId)`: helper central usado por el `AuditInterceptor` (Bloque 1) y también invocable manualmente donde se requiera mayor control (ej. flujos de auth con lógica condicional compleja).
- [ ] **Fase 10.2** — Endpoint de solo lectura `GET /audit-logs` (admin) con filtros por entidad/acción/fecha — si existe un caso de uso equivalente en Laravel (verificar si hay UI de consulta de auditoría más allá de `password-resets`), replicarlo; si no existe, omitir endpoint público y dejar la tabla solo para trazabilidad interna.
- [ ] **Fase 10.3** — Testing: verificar que cada mutación crítica (login, operación, anulación, cierre, cambio de contraseña, reset) genere su registro de auditoría correspondiente.
- [ ] **Fase 10.4** — Commit y PR: `feat: módulo auditoría (interceptor global + consulta)`.

---

## BLOQUE 11 — Contrato de API y documentación

- [ ] **Fase 11.1** — Decorar todos los DTOs con `@ApiProperty()` de Swagger para generar documentación completa y tipada.
- [ ] **Fase 11.2** — Decorar todos los controllers/endpoints con `@ApiTags()`, `@ApiOperation()`, `@ApiResponse()`.
- [ ] **Fase 11.3** — Generar cliente TypeScript tipado desde el esquema OpenAPI (`openapi-typescript` o similar) para uso directo en el repo `agenteflow-mobile` (Bloque 2 del `frontend-plan.md`) — evita mantener tipos duplicados manualmente entre backend y frontend.
- [ ] **Fase 11.4** — Publicar el paquete de tipos generado (opcional: como paquete npm privado, o simplemente como artefacto versionado copiado al repo frontend).
- [ ] **Fase 11.5** — Documentar en `README.md` del repo `agenteflow-api` cómo levantar el entorno local, variables de entorno requeridas, y cómo regenerar el cliente de tipos.
- [ ] **Fase 11.6** — Commit: `docs: contrato de API completo (Swagger) + generación de tipos para frontend`.

---

## BLOQUE 12 — Testing integral y calidad

- [ ] **Fase 12.1** — Configurar Jest para unit tests (`*.spec.ts` por servicio) — cobertura mínima objetivo (ej. 80%) en Services de lógica de negocio crítica (Auth, Operations, DailyClosing).
- [ ] **Fase 12.2** — Configurar Supertest para tests E2E de endpoints HTTP (`*.e2e-spec.ts`), usando una base de datos de test separada (schema o base de datos Supabase de staging, nunca contra producción).
- [ ] **Fase 12.3** — Suite de tests de paridad funcional: para cada regla de negocio migrada, comparar resultado NestJS vs comportamiento documentado/observado en Laravel (especialmente cálculos de `DailyClosing` y `Operations`).
- [ ] **Fase 12.4** — Tests de seguridad: intento de acceso cross-organization (un admin de la organización A no debe poder ver/modificar datos de la organización B), intento de escalamiento de privilegios, reuso de refresh token.
- [ ] **Fase 12.5** — Integrar CI (GitHub Actions): lint + unit + e2e en cada PR, bloqueo de merge si fallan.
- [ ] **Fase 12.6** — Commit: `test: suite de pruebas unitarias, e2e y paridad funcional`.

---

## BLOQUE 13 — Despliegue en Render

- [ ] **Fase 13.1** — Crear `Dockerfile` multi-stage para NestJS: stage `build` (`npm ci && npm run build`), stage `runtime` (imagen `node:alpine`, solo `dist/` + `node_modules` de producción).
- [ ] **Fase 13.2** — Configurar `.dockerignore` (excluir `node_modules`, `.git`, tests, etc.).
- [ ] **Fase 13.3** — Crear `render.yaml` para el nuevo servicio `agenteflow-api`: `runtime: docker`, `plan: free`, `healthCheckPath: /health`, variables de entorno (`DATABASE_URL`, `JWT_SIGNING_KEY`, `JWT_ISSUER`, `JWT_AUDIENCE`, `JWT_ACCESS_TTL`, `JWT_ABSOLUTE_SESSION_TTL`, `REFRESH_PEPPER`, `PASSWORD_RESET_TTL_SECONDS`, `PASSWORD_RESET_TEMPORARY_LENGTH`, `CORS_ORIGIN`, `OPERATIONS_RETROACTIVE_WINDOW_HOURS`, `OPERATIONS_ANNULMENT_WINDOW_HOURS`, `OPERATIONS_DEFAULT_CURRENCY`) — mismos valores/nombres conceptuales que usa Laravel actualmente, para no perder configuración de negocio ya validada.
- [ ] **Fase 13.4** — Ejecutar `npx prisma migrate deploy` (o simplemente `prisma generate`, dado que el esquema ya existe y no se gestiona por migraciones Prisma sino que se mantiene sincronizado con las migraciones Laravel/SQL existentes) como parte del entrypoint/build.
- [ ] **Fase 13.5** — Desplegar en Render como servicio de **staging** primero (`agenteflow-api-staging`), apuntando a una base de datos Supabase de pruebas (nunca la de producción) hasta validar completamente.
- [ ] **Fase 13.6** — Validar `/health` responde 200 y conecta correctamente a la base de datos.
- [ ] **Fase 13.7** — Validar CORS contra el dominio del frontend Expo Web desplegado en staging.
- [ ] **Fase 13.8** — Pruebas de carga básicas (ej. `autocannon` o `k6`) para validar comportamiento dentro de los límites del plan Free (512MB RAM).
- [ ] **Fase 13.9** — Documentar el proceso de despliegue en `README.md`/`docs/deployment.md` del repo `agenteflow-api` (variables de entorno requeridas, comandos de build/start, healthcheck).
- [ ] **Fase 13.10** — Commit y PR: `chore: despliegue en Render (Dockerfile, render.yaml, staging validado)`.

---

## BLOQUE 14 — Corte final y cierre de la migración Backend

- [ ] **Fase 14.1** — Ejecutar la suite completa de tests de paridad funcional (Bloque 12) contra el ambiente de staging con datos reales/anonimizados de producción, comparando resultados con el sistema Laravel actual.
- [ ] **Fase 14.2** — Periodo de convivencia: mantener ambos backends (Laravel actual y NestJS nuevo) disponibles simultáneamente; el frontend Expo (ver `frontend-plan.md`) apunta a NestJS solo cuando esté validado.
- [ ] **Fase 14.3** — Migrar el servicio de staging a producción en Render (`agenteflow-api`), apuntando ahora sí a la base de datos Supabase de producción real.
- [ ] **Fase 14.4** — Validación final end-to-end: frontend Expo (Web/iOS/Android) contra `agenteflow-api` en producción, con todos los módulos migrados.
- [ ] **Fase 14.5** — Monitoreo post-lanzamiento: logs, métricas de error, tiempos de respuesta durante las primeras semanas — comparar contra el comportamiento histórico conocido de Laravel para detectar regresiones.
- [ ] **Fase 14.6** — Retiro/archivo del stack Laravel (`app/`, `routes/`, `database/migrations/` quedan como referencia histórica; considerar archivar el repo `control-operaciones-agente` o mantenerlo solo de solo lectura).
- [ ] **Fase 14.7** — Post-mortem/retrospectiva de la migración completa (frontend + backend), documentar lecciones aprendidas y deuda técnica pendiente.

---

## Notas finales

- El **Bloque 2 (Auth)** es el más crítico y debe completarse y probarse exhaustivamente antes de avanzar — toda la seguridad del sistema (JWT, refresh rotativo, detección de reuso) depende de una implementación fiel al comportamiento actual de Laravel.
- Cada Bloque genera un PR independiente, permitiendo revisión incremental sin bloquear el resto del desarrollo.
- Este plan asume que el frontend (`agenteflow-mobile`, ver `frontend-plan.md`) puede consumir la API Laravel actual (o mocks) en sus primeras fases, mientras este backend NestJS se construye en paralelo — ambos planes convergen en sus respectivos Bloques finales de corte (Bloque 13 del frontend / Bloque 14 del backend).

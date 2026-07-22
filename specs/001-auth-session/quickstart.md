# Quickstart Validation: Autenticación y Ciclo de Sesión

## Prerequisites

- PHP 8.3 with extensions required by Laravel 13 and JWT cryptography.
- Composer and MySQL 8.0 or supported MariaDB.
- Node.js only on the build machine.
- HTTPS for browser cookie validation.
- Environment secrets: distinct app key, JWT signing key and refresh HMAC pepper.

The repository currently contains design artifacts only. Implementation begins by scaffolding Laravel
13 at the repository root without overwriting `.specify/`, `specs/` or `docs/`.

## Planned Setup Commands

```bash
composer install
npm ci
npm run build
php artisan migrate --seed
php artisan test
```

Use a dedicated test database. Run concurrency tests against real MySQL and MariaDB configurations;
do not substitute SQLite for lock-sensitive scenarios.

## Configuration Checks

- Application runtime timezone/storage is UTC; visible timezone is `America/Lima`.
- Access lifetime is 300 seconds and absolute session lifetime is 8 hours.
- `APP_DEBUG=false` and HTTPS/cookie secure flags are enabled outside local development.
- Web document root resolves to `public/` only.
- `public/build/manifest.json` exists before deployment; Node.js is not running in production.
- No Redis, queue worker or WebSocket service is required for health or authentication.

## End-to-End Validation Scenarios

### Login And Throttling

1. Login by normalized username and then email; expect separate identifiable sessions and protected
   page with `expiresAt`.
2. Inspect browser storage: auth cookies exist but JavaScript cannot read them; localStorage and
   sessionStorage contain no token.
3. Submit five invalid attempts in one minute and verify the next applicable attempt returns 429.
4. Verify logs contain no password, JWT, refresh value or token hash.

### Expiry UI

1. Open a protected page and compare timer with `expiresAt`; deviation while visible is <=1 second.
2. Hide/suspend the tab, restore it and verify recalculation rather than interval drift.
3. At 30 seconds verify modal focus, labels `Continuar`/`Cerrar sesión`, keyboard operation and mobile
   layout.
4. Let time reach zero; verify redirect and that the expired JWT cannot authorize a direct request.
5. Reload with expired cookies; verify login is shown without any refresh request.

### Explicit Rotation And Replay

1. Press `Continuar` before expiry; expect 200, new `expiresAt`, closed modal and rotated cookies.
2. Replay the prior refresh through a test client; expect 401, session `FALLO_SEGURIDAD` and current
   successor unusable.
3. Run two requests concurrently with one refresh token; assert at most one rotation and final session
   revocation after the second use.
4. Simulate a lost rotation response/retry and verify strict replay policy still revokes.
5. Attempt renewal at/after five minutes and at the eight-hour absolute boundary; expect 401/login.

### Logout And Deactivation

1. Logout one of two sessions; verify only that session is revoked as `LOGOUT_MANUAL`.
2. Repeat logout; verify idempotence and original reason preserved.
3. As administrator deactivate another user with multiple sessions; verify user inactive, all sessions
   `REVOCACION_ADMINISTRATIVA`, audit before/after/reason and immediate denial.
4. As operator or by manipulated URL attempt deactivation; expect 403 and no changes.
5. Attempt administrator self-deactivation; expect conflict and at least one owner remains active.

### Session History Authorization

1. As administrator list/filter sessions for multiple users; verify bounded pagination.
2. As operator list sessions; verify only own rows.
3. Manipulate user/page/filter parameters as operator; verify no foreign row or existence detail leaks.
4. Verify queries use indexes and do not load all history records.

### Recovery And Operations

1. Call `/health`; expect minimal 200 and no secrets. Break DB connectivity in staging; expect 503
   without stack trace.
2. Restore a recent database backup in an isolated environment and verify users, sessions, events and
   audit records.
3. Execute migration up/down on a disposable database. For production data, validate forward-fix and
   backup restore before destructive rollback.
4. Inspect production logs with debug disabled and verify correlation without sensitive payloads.

## Expected Test Suites

```text
tests/Unit/IdentityAccess/          claims, time, HMAC, state rules
tests/Feature/IdentityAccess/       routes, policies, CSRF, UI contracts
tests/Integration/IdentityAccess/   InnoDB locks, races, rollback
```

Completion requires all acceptance, positive/negative authorization and full authentication lifecycle
tests passing on the supported database engines.

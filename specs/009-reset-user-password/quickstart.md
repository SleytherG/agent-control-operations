# Quickstart Validation: Restablecimiento Seguro de Contraseña

## Prerequisites

- PHP 8.3, Composer and extensions required by Laravel 13.
- MySQL 8.0 or supported MariaDB for concurrency validation.
- Node.js only on the build machine.
- HTTPS for cookie, clipboard and cache behavior in browser validation.
- Test fixtures: one administrator, two active operators in its organization, one inactive operator,
  one operator in another organization and an additional administrator target.

## Setup

```bash
composer install
npm ci
npm run build
php artisan migrate
php artisan test
```

Use a disposable database. Run lock/concurrency cases explicitly against both supported database
engines; SQLite is acceptable only for non-concurrent feature tests.

## Configuration Checks

- `JWT_ACCESS_TTL=300` and existing refresh/absolute session configuration remain active.
- Password-reset TTL resolves to 3,600 seconds.
- Login and administrative step-up limits resolve to 5 failed attempts in 60 seconds with their
  documented identifier/origin and administrator/origin keys.
- `APP_DEBUG=false`, HTTPS and secure cookie flags are enabled outside local development.
- Session and cache drivers do not receive the temporary secret.
- Web document root is `public/`; Node.js, Redis and workers are not required at runtime.
- Responses containing/resetting passwords include `Cache-Control: no-store`.

## End-to-End Scenarios

### 1. Successful Administrative Reset

1. Open `/admin/users/{operator}/edit` as the administrator.
2. Start reset, verify target identity and session-revocation warning.
3. Enter the correct administrator password and confirm.
4. Expect one modal response containing a 20-character temporary password, issue time, one-hour
   expiry and private-channel warning.
5. Close the modal, reload and navigate back; verify the secret is absent and cannot be queried.
6. Verify the target old password, prior access cookies and prior refresh cookies all fail while the
   administrator session remains active.

### 2. Step-Up And Authorization Failures

1. Submit an incorrect administrator password; expect 422 and no change to target hash, resets or
   sessions.
2. Repeat to the configured threshold; expect 429 without exposing the correct-password state.
3. Attempt as operator, cross-organization admin and against an admin target; expect 403.
4. Attempt against inactive/non-active target; expect 409 and guidance, with no temporary credential.

### 3. One-Time Login

1. Login with the issued credential before 59:59; expect direct redirect to `/password/change`.
2. Verify one reset `CONSUMED`, exactly one linked auth session and one active refresh generation.
3. Attempt a second login with the same credential; expect generic failure and no second session.
4. Manipulate URLs/forms for dashboard, operations and admin pages; expect redirect/rejection before
   any read outside the allowlist or any write.
5. Verify explicit refresh can extend only the same restricted session.

### 4. Mandatory Change

1. Submit mismatched confirmation, fewer than 8 characters and a new value equal to the temporary;
   expect 422 and reset remains `CONSUMED`. The form must not request the consumed temporary again.
2. Submit a valid different password; expect `COMPLETED`, `password_changed_at` set and dashboard
   access with the same session.
3. Verify temporary credential fails and definitive credential succeeds in a new login.
4. Verify password inputs are absent from session data, logs and audit.

### 5. Lost Restricted Session

1. Issue and consume a reset.
2. Logout or let the linked session/access path expire without completing.
3. Retry the temporary credential; expect generic failure.
4. Issue another reset; expect prior lifecycle `SUPERSEDED` and only the new credential usable.

### 6. Expiry Boundaries

1. Freeze time at issue+59:59; first login succeeds.
2. Repeat with a fresh reset at issue+60:00; login fails and lifecycle becomes `EXPIRED`.
3. Verify no worker or cleanup command is required for denial.

### 7. Concurrency

Run with two real DB connections:

1. Two simultaneous resets: serialized outcomes, prior lifecycle `SUPERSEDED`, only final hash works.
2. Two simultaneous logins: exactly one `CONSUMED` session, the other fails generically.
3. Reset versus login: no mixed hash/lifecycle and no unauthorized session.
4. Reset versus deactivation: final target state and reset eligibility remain coherent.
5. Two simultaneous password completions: exactly one completion/audit transition.

### 8. Audit And Secret Leakage

1. Open `/admin/users/{operator}/password-resets`; verify paginated issue, revocation, consume,
   supersede/expire and complete actions with actors/timestamps.
2. Attempt the same page as operator/cross-organization admin; expect 403.
3. Search DB, audit, application logs, exception output, HTML history, URLs, session, localStorage and
   sessionStorage for old/admin/temporary/definitive plaintext values; expect zero matches.
4. Confirm audit snapshots contain only allowed IDs, states, counts and instants.

### 9. Measurable Acceptance

1. Execute the administrative flow 5 times with independent eligible fixtures. Start timing when
   the security section is opened and stop when the temporary credential appears; every run must
   finish in less than 60 seconds.
2. Execute the mandatory-change flow 5 times. Start timing when the form appears and stop when
   normal access is shown; every valid run must finish in less than 2 minutes.
3. Recruit at least 10 representative participants and present the same reset-result screen and
   script. Without hints, ask whether the credential may be sent to a public/group channel and
   whether it remains valid as the permanent password. At least 9 must answer both correctly.
4. Record timings, anonymous answers, environment, failures and evidence in
   `specs/009-reset-user-password/validation-report.md`.

### 10. Regression

1. Create a new operator through the existing flow (`password_changed_at=null`, no reset row);
   existing first-change behavior still works.
2. Verify ordinary login, throttle, refresh rotation/replay, logout, session history and deactivation.
3. Verify the corrected password-change redirect returns its success message and compiles.
4. Verify `/health` and production-safe error logging remain unchanged.

## Expected Test Files

```text
tests/Unit/IdentityAccess/PasswordPolicyTest.php
tests/Unit/IdentityAccess/PasswordResetStateTest.php
tests/Feature/IdentityAccess/PasswordResetAuthorizationTest.php
tests/Feature/IdentityAccess/AdminPasswordReauthenticationTest.php
tests/Feature/IdentityAccess/PasswordResetLifecycleTest.php
tests/Feature/IdentityAccess/TemporaryPasswordLoginTest.php
tests/Feature/IdentityAccess/RestrictedSessionTest.php
tests/Feature/IdentityAccess/PasswordChangeCompletionTest.php
tests/Feature/IdentityAccess/PasswordResetAuditTest.php
tests/Feature/IdentityAccess/PasswordResetSecretLeakTest.php
tests/Integration/IdentityAccess/PasswordResetConcurrencyTest.php
tests/Integration/Migrations/PasswordResetMigrationsTest.php
```

## Backup, Restore, And Migration Strategy

### Pre-migration Backup

Before deploying migrations to production:

```bash
mysqldump --single-transaction --routines --triggers \
  --no-tablespaces \
  -u {user} -p {database} > backup_before_password_resets_$(date +%Y%m%d_%H%M%S).sql
```

The `password_resets` and `auth_sessions` tables hold security lifecycle data
(audit evidence, session ownership). A backup must include all rows and schema
to enable point-in-time audit reconstruction.

### Migration Execution

```bash
php artisan migrate --step
```

Two migrations ship with this feature:
1. `2026_07_23_000010_create_password_resets_table.php` — creates `password_resets`
   with PK, FKs, indexes (public_id, user_id+status+issued_at, organization_id+status+issued_at,
   expires_at, initiated_by_user_id+issued_at).
2. `2026_07_23_000011_add_password_reset_id_to_auth_sessions_table.php` — adds nullable
   unique FK `auth_sessions.password_reset_id` referencing `password_resets.id` with RESTRICT.

### Migration Rollback

```bash
php artisan migrate:rollback --step=2
```

The `down()` methods:
1. Drop `password_reset_id` FK, unique index and column on `auth_sessions`.
2. Drop `password_resets` table.

Existing `auth_sessions` rows have `password_reset_id = null` and are unaffected.
No backfill or data rewrite is required in either direction.

### Forward-fix Strategy

In the event of migration failure on a production environment:

1. Restore the pre-migration backup into a staging clone.
2. Re-run migrations on staging to isolate root cause.
3. Apply the fix forward (new migration or corrected migration on a fresh check
   baseline) rather than altering an already-applied migration.
4. Re-run the full test suite against MySQL/MariaDB, particularly
   `PasswordResetMigrationsTest` which validates up/down, FKs, unique constraint
   on `auth_sessions.password_reset_id`, and null-compatibility for existing rows.

### Restore Validation

After restore, verify:

```bash
php artisan migrate:status
php artisan test --testsuite Integration --filter PasswordResetMigrationsTest
```

Confirm:
- `password_resets` schema includes all required columns and indexes.
- `auth_sessions.password_reset_id` is nullable and unique.
- Existing session rows retain null without constraint violations.
- Insertion of linked session-row pairs respects the unique constraint.

### Audit History Retention

The `password_resets` table is the authoritative source for the lifecycle history
of every temporary credential issued. This history must be preserved in backups
and restores to maintain auditability per specification requirements.

Production rollback discards lifecycle data; prefer forward-fix over rollback
when password_resets rows exist. If rollback is unavoidable, export
`password_resets` records first for offline retention.

Completion requires all specification scenarios, positive/negative authorization, security
regressions and supported-engine concurrency tests passing.

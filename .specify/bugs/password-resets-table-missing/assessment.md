# Bug Assessment: password_resets Table Not Found During Login

- **Slug**: password-resets-table-missing
- **Created**: 2026-07-23
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: critical

## Report (verbatim)

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'control_operaciones.password_resets' doesn't exist
(Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: control_operaciones,
SQL: select * from `password_resets` where `user_id` = 1 order by `issued_at` desc, `id` desc limit 1 for update)

POST /login — LoginController@login (login.store)
```

## Symptom

Every login attempt via `POST /login` crashes with an HTTP 500 (Internal Server Error) because `AuthenticateAndStartSession` queries the `password_resets` table unconditionally, but the table does not exist in the MySQL database — its migration is pending. No user can authenticate.

## Reproduction

1. Navigate to `http://localhost:8000/login`.
2. Submit valid credentials for any ACTIVE user.
3. Observe 500 error with `Base table or view not found: 1146 Table 'control_operaciones.password_resets' doesn't exist`.

## Suspected Code Paths

- `app/Modules/IdentityAccess/Application/Actions/AuthenticateAndStartSession.php:52-57` — Queries `PasswordReset` model (maps to `password_resets` table) during every login, inside a `DB::transaction()`, regardless of whether the user has any password reset record. This is the crash site.
- `app/Modules/IdentityAccess/Http/Controllers/LoginController.php:58` — Invokes `AuthenticateAndStartSession::execute()` on `login.store` route.
- `database/migrations/2026_07_23_000010_create_password_resets_table.php` — The migration exists in the codebase but has **NOT** been applied to MySQL.
- `database/migrations/2026_07_23_000011_add_password_reset_id_to_auth_sessions_table.php` — Also pending; depends on the 000010 table.

## Root Cause Hypothesis

**Confidence: high.**

The `password_resets` and `auth_sessions` (password_reset_id column) migrations were created as part of the 009-reset-user-password feature but were never applied to the MySQL database. `php artisan migrate:status` confirms both are `Pending`. The `AuthenticateAndStartSession` action queries `password_resets` for every authentication attempt (not just temporary-password logins), causing an outright login outage for all users.

## Proposed Remediation

**Preferred**: Run the pending migrations to create the table:

```bash
php artisan migrate
```

This will apply both `2026_07_23_000010_create_password_resets_table` and `2026_07_23_000011_add_password_reset_id_to_auth_sessions_table`, resolving the crash immediately.

Additionally, consider a defense-in-depth code change: wrap the password-reset lookup in `AuthenticateAndStartSession` with a `Schema::hasTable('password_resets')` guard or a try/catch, so a missing table does not block all authentication. This would prevent a repeat outage if migrations are ever re-created or reset.

**Alternatives**:
- Run `php artisan migrate --step` to only apply the two pending migrations (avoids running other pending migrations, if any).
- If the password-reset feature is not yet ready for production, the `AuthenticateAndStartSession` code could be made conditional on feature flags — but this adds complexity.

**Files likely to change**:
- `database/migrations/2026_07_23_000010_create_password_resets_table.php` (no change needed — pending, not broken)
- `app/Modules/IdentityAccess/Application/Actions/AuthenticateAndStartSession.php` (optional: add defensive guard)

**Tests to add or update**:
- A migration test confirming `password_resets` table exists after `migrate` (already covered by `PasswordResetMigrationsTest`).
- A deployment smoke test: after a fresh deploy, verify `POST /login` returns 200 (not 500) for valid credentials.

## Risks & Considerations

- **Migration fk constraints**: The 000010 migration references `organizations` and `users` via `foreignId()->constrained()` and `restrictOnDelete()`. If those tables have data inconsistencies, `migrate` could fail. Verify `organizations` and `users` tables are intact before running.
- **Pigeonhole risk**: If the `AuthenticateAndStartSession` code is not hardened, any future scenario that drops/recreates the DB without running these migrations will reproduce the same outage. Consider adding a `Schema::hasTable()` guard.
- **No data migration risk**: Since the table is empty (new), there's no data to lose or migrate.

## Open Questions

- None.

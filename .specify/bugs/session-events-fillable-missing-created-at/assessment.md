# Bug Assessment: SessionEvent `created_at` silently dropped during mass assignment

- **Slug**: session-events-fillable-missing-created-at
- **Created**: 2026-07-22
- **Source**: pasted text (stack trace from POST /login)
- **Verdict**: valid
- **Severity**: high

## Report (verbatim)

```
SQLSTATE[HY000]: General error: 1364 Field 'created_at' doesn't have a default value
(Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: control_operaciones,
SQL: insert into `session_events` (`auth_session_id`, `user_id`, `type`, `occurred_at`)
values (1, 1, LOGIN, 2026-07-23 04:39:35))
```

PHP 8.5.8, Laravel 13.21.1. Triggered during `POST /login`, inside the `StartAuthSession` action within a database transaction. The `AuthSession` and `AuthRefreshToken` rows were created successfully; the failure occurs on the `SessionEvent::create()` call at `StartAuthSession.php:58`.

## Symptom

Any attempt to create a `SessionEvent` record fails with a MySQL general error because the `created_at` column has no default value and is missing from the INSERT statement. This blocks all user login (500 Internal Server Error).

## Reproduction

1. Start the application connected to MySQL (not SQLite).
2. Run migrations: `php artisan migrate`
3. Ensure at least one active user exists in the `users` table.
4. Navigate to `/login` and submit valid credentials.
5. Observe HTTP 500 with the `Field 'created_at' doesn't have a default value` error.

Note: the test suite skips login tests with `$this->markTestSkipped('Requires MySQL for session_events.created_at auto-population.')` (`tests/Feature/IdentityAccess/LoginViewTest.php:29`).

## Suspected Code Paths

- `app/Modules/IdentityAccess/Models/SessionEvent.php:16-18` — the `$fillable` array omits `created_at`, causing mass-assignment to silently drop it.
- `app/Modules/IdentityAccess/Application/Actions/StartAuthSession.php:58-64` — calls `SessionEvent::create()` with `'created_at' => $now`, which is stripped by `$fillable`.
- `database/migrations/2026_07_22_000005_create_session_events_table.php:18` — defines `$table->dateTime('created_at', 6)` without `->nullable()` or `->useCurrent()`.
- All other callers (`RecordSessionEvent`, `ExpireSession`, `RotateRefreshToken`, `RevokeSession`, `DeactivateUser`) — equally affected; only `StartAuthSession` is hit during the login flow.

## Root Cause Hypothesis

**Confidence: high.** The `SessionEvent` model sets `$fillable` to an explicit list that excludes `created_at`. Even though every caller passes `'created_at' => $now` to `SessionEvent::create()`, Eloquent's mass-assignment filter (since `$fillable` is defined) drops the attribute before building the INSERT query. The migration does not provide a column default, so MySQL rejects the row. The model also sets `$timestamps = false`, which prevents Eloquent from auto-filling `created_at`/`updated_at`.

## Proposed Remediation

**Preferred**: Add `'created_at'` to the `$fillable` array in `SessionEvent`. This is a one-line change, safe, and makes the model consistent with its callers (all of which already provide `created_at`).

```php
// SessionEvent.php
protected $fillable = [
    'auth_session_id', 'user_id', 'type', 'occurred_at', 'context', 'created_at',
];
```

**Alternatives**:
- Set `$guarded = []` instead of `$fillable` — removes the guard entirely. Simpler but less explicit about which fields are writable.
- Give `created_at` a DB default via a new migration (`->useCurrent()` or `->nullable()`). This would mask the misconfiguration and contradicts the append-only intent, since `occurred_at` is the authoritative timestamp and `created_at` should match it exactly.
- Re-enable `$timestamps = true` so Eloquent manages `created_at`/`updated_at` automatically. This changes behavior: `updated_at` and `created_at` would diverge from `occurred_at` (which is the domain timestamp). Could also cause unexpected `updated_at` writes.

**Files likely to change**:
- `app/Modules/IdentityAccess/Models/SessionEvent.php`

**Tests to add or update**:
- Unskip `LoginViewTest.php:29` and ensure it passes against MySQL.
- Add a unit test that creates a `SessionEvent` via `SessionEvent::create()` and asserts the row was persisted with the expected `created_at` value.

## Risks & Considerations

- Low risk: adding `created_at` to `$fillable` only enables what the callers already attempt — no behavioral change.
- Existing callers (`RecordSessionEvent`, `ExpireSession`, `RotateRefreshToken`, `RevokeSession`, `DeactivateUser`) will also start working without code changes.
- The model intentionally keeps `$timestamps = false` because `occurred_at` is the domain-level timestamp; this assessment does not challenge that design.

## Open Questions

None.

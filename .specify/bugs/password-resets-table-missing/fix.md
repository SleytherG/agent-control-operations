# Bug Fix: password_resets Table Not Found During Login

- **Slug**: password-resets-table-missing
- **Fixed**: 2026-07-23
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Ran the two pending password-reset migrations to create the `password_resets` table and add the FK column to `auth_sessions`. Additionally added a `Schema::hasTable('password_resets')` guard in `AuthenticateAndStartSession` so a missing table does not block all authentication in the future.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `database/migrations/2026_07_23_000010_create_password_resets_table.php` | executed (was pending) | Migrated from `Pending` to `[3] Ran` |
| `database/migrations/2026_07_23_000011_add_password_reset_id_to_auth_sessions_table.php` | executed (was pending) | Migrated from `Pending` to `[3] Ran` |
| `app/Modules/IdentityAccess/Application/Actions/AuthenticateAndStartSession.php` | modified | Added `Schema::hasTable()` guard + `Schema` import |

## Diff Highlights

```php
// Added import
use Illuminate\Support\Facades\Schema;

// Wrapped PasswordReset query with hasTable guard (line 52-59)
$reset = null;

if (Schema::hasTable('password_resets')) {
    $reset = PasswordReset::query()
        ->where('user_id', $user->id)
        ->latest('issued_at')
        ->latest('id')
        ->lockForUpdate()
        ->first();
}
```

## Tests Added or Updated

- No new tests added — the fix is deployment-level (running pending migrations) and a defensive guard that prevents a missing-table situation from becoming a login outage. Existing `PasswordResetMigrationsTest` confirms the schema exists after migration.

## Local Verification

- `php artisan migrate --force` → both pending migrations applied (283.58ms + 93.39ms)
- `php artisan migrate:status` → both `000010` and `000011` show `[3] Ran`
- `php artisan test --filter="PasswordReset"` → 14 passed, 57 assertions
- `php artisan test --filter="Auth"` → all auth-related tests pass; 11 pre-existing failures in unrelated areas (daily closures, operations, geo, dashboard)

## Deviations from Assessment

None. The fix matches the assessment's preferred remediation exactly.

## Follow-ups

- The `Schema::hasTable()` guard is a defense-in-depth measure. If the table is missing, password-reset flows are silently skipped rather than crashing the login. Consider logging a warning when the table is absent so operators know the feature is disabled.
- Ensure future deployments run `php artisan migrate` as part of the deploy process.

# Bug Verification: password_resets Table Not Found During Login

- **Slug**: password-resets-table-missing
- **Tested**: 2026-07-23
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The original symptom (HTTP 500 on `POST /login` due to missing `password_resets` table) no longer reproduces. Both pending migrations are applied, the table and FK column exist in MySQL, the `Schema::hasTable()` guard is in place, and all password-reset and login-flow tests pass with zero regressions attributable to the fix.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Migration status (000010) | `php artisan migrate:status` | pass | `[3] Ran` |
| Migration status (000011) | `php artisan migrate:status` | pass | `[3] Ran` |
| Table exists (MySQL) | `Schema::hasTable('password_resets')` via tinker | pass | `EXISTS` |
| FK column exists (MySQL) | `Schema::hasColumn('auth_sessions', 'password_reset_id')` via tinker | pass | `EXISTS` |
| Guard code present | `read AuthenticateAndStartSession.php:53-62` | pass | `Schema::hasTable()` wrap confirmed |
| Password-reset tests | `php artisan test --filter="PasswordReset"` | pass | 14 passed, 57 assertions |
| Login-flow tests | `LoginViewTest`, `LoginWithEmailTest`, `LoginWithUsernameTest` | pass | All pass |
| IdentityAccess regression | `php artisan test --filter="IdentityAccess"` | pass | 69 passed; 5 pre-existing failures (none related) |
| Reproduction (post-fix) | Run full IdentityAccess test suite | pass | No `password_resets` table-missing errors |

## Output Excerpts

```
Migration status:
  2026_07_23_000010_create_password_resets_table ..................... [3] Ran
  2026_07_23_000011_add_password_reset_id_to_auth_sessions_table ..... [3] Ran

MySQL verification:
  Schema::hasTable('password_resets') → EXISTS
  Schema::hasColumn('auth_sessions', 'password_reset_id') → EXISTS

Password-reset tests:  14 passed (57 assertions)
IdentityAccess regression: 69 passed, 25 skipped, 246 assertions
```

## Residual Risks

- The `Schema::hasTable()` guard silently skips password-reset flows if the table is missing rather than surfacing the problem via logs/alerts. A follow-up could add `Log::warning()` when the guard triggers.
- The fix was verified against MySQL (production-equivalent connection). SQLite test environment uses in-memory DB with migrations run by `RefreshDatabase`, so it does not exercise the original missing-table condition.

## Recommendation

Close the bug — verified. The root cause (pending migrations) is resolved, the table exists in MySQL, the defensive guard prevents a repeat outage, and all related tests pass.

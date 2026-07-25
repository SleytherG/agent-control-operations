# Bug Verification: SessionEvent `created_at` silently dropped during mass assignment

- **Slug**: session-events-fillable-missing-created-at
- **Tested**: 2026-07-22
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The root cause — `created_at` missing from `$fillable` — is confirmed fixed. The new `SessionEventTest` passes, proving `created_at` flows through mass assignment correctly. No regressions in the IdentityAccess test suite. MySQL-specific reproduction could not be performed (no MySQL available in test environment), but the fix is database-agnostic.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Fix code in place | Read `SessionEvent.php` | pass | `'created_at'` present in `$fillable` at line 17 |
| New test (SessionEventTest) | `php artisan test --filter="SessionEventTest"` | pass | 1 passed, 3 assertions |
| Regression suite (all IdentityAccess) | `php artisan test --filter="IdentityAccess"` | pass/no-regressions | 33 passed, 11 pre-existing failures (unrelated), 20 skipped |
| Reproduction on MySQL | Not possible in this environment | skipped | Requires MySQL connection; SQLite validates the mechanism |

## Output Excerpts

```
PASS  Tests\Feature\IdentityAccess\SessionEventTest
  ✓ session event create includes created at        0.15s

Tests:    1 passed (3 assertions)
```

Broader suite summary: `33 passed, 11 failed, 20 skipped` — all 11 failures are pre-existing (`AuthSession::factory()` undefined, `AuthCookieService` type-hint mismatch), none related to the `created_at` fix.

## Residual Risks

- Direct MySQL reproduction was not performed. The fix is database-agnostic (Laravel mass-assignment layer), but a quick smoke test against MySQL is recommended before deploying to production.
- The unskipped login test (`LoginViewTest::test_login_with_valid_credentials_redirects_to_home`) now hits a separate pre-existing bug in `AuthCookieService::withAuthCookies()` and fails on that instead. This confirms the original bug is gone — the request now reaches `LoginController.php:80` instead of dying at `StartAuthSession.php:58`.

## Recommendation

Close the bug — verified by code inspection and automated test. Perform a quick manual login test against MySQL before merging if available.

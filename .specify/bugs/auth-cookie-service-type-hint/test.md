# Bug Verification: AuthCookieService type-hint rejects RedirectResponse

- **Slug**: auth-cookie-service-type-hint
- **Tested**: 2026-07-22
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The bug no longer reproduces. The `TypeError` from `AuthCookieService::withAuthCookies()` rejecting `RedirectResponse` is gone. All 4 new unit tests pass, all 15 login flow tests pass (including the one previously blocked by this bug), and the broader IdentityAccess suite shows no regressions related to this fix.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| New unit tests | `php artisan test --filter="AuthCookieServiceTest"` | pass | 4 passed, 4 assertions |
| Login flow tests (reproduction) | `php artisan test --filter="LoginViewTest\|LoginWithUsernameTest\|LoginWithEmailTest\|LoginErrorStatesTest"` | pass | 15 passed, 37 assertions |
| IdentityAccess regression suite | `php artisan test --filter="IdentityAccess"` | pass/no-regressions | 37 passed, 7 pre-existing failures, 20 skipped |

## Output Excerpts

```
PASS  Tests\Unit\AuthCookieServiceTest
  ✓ with auth cookies accepts response
  ✓ with auth cookies accepts redirect response
  ✓ clear auth cookies accepts response
  ✓ clear auth cookies accepts redirect response

PASS  Tests\Feature\IdentityAccess\LoginViewTest
  ✓ login with valid credentials redirects to home    ← was failing before fix

Tests:    15 passed (37 assertions)
```

Broader suite: `37 passed, 7 failed, 20 skipped` — the 7 failures are all pre-existing (`AuthSession::factory()` undefined, etc.), zero related to this fix.

## Residual Risks

- The reproduction was exercised via automated tests (SQLite) rather than a manual browser login against MySQL. A quick smoke test against the running dev server is advisable.

## Recommendation

Close the bug — verified end-to-end via automated tests. The `withAuthCookies()` and `clearAuthCookies()` methods now correctly accept both `Response` and `RedirectResponse`.

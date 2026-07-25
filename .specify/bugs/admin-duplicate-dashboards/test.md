# Bug Verification: Admin users see an operator dashboard as a second dashboard

- **Slug**: admin-duplicate-dashboards
- **Tested**: 2026-07-23T15:37:22-05:00
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: partial

## Summary

The duplicate-dashboard authorization path is fixed: Admin receives 403 for `/dashboard`, operators retain access, and Admin retains the canonical dashboard and operator comparison access. The result is partial because the full browser navigation flow was not exercised and unrelated stale tests remain failing.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Reproduction (post-fix) | Admin request to `dashboard.operator` through `DashboardAuthorizationTest` | pass | Returns 403; the duplicate route is no longer accessible to Admin. |
| New / updated tests | `php artisan test tests/Feature/Reporting/DashboardAuthorizationTest.php --filter='test_operator_can_access_operator_dashboard|test_admin_cannot_access_operator_dashboard|test_admin_can_access_admin_dashboard|test_operator_cannot_access_admin_dashboard|test_admin_can_access_operator_comparison' --stop-on-failure` | pass | 5 tests passed (6 assertions). |
| Operator regression | `php artisan test tests/Feature/Reporting/OperatorDashboardViewTest.php --stop-on-failure` | pass | 3 tests passed (5 assertions). |
| Navigation verification | Static review of `sidebar.blade.php` and `mobile-nav.blade.php` | pass | `dashboard.operator` remains only in operator branches; Admin branches contain `Dashboard Admin` only. |
| Regression suite | Full `DashboardAuthorizationTest` | partial | 9 authorization tests passed; one unrelated `Monto bruto operado` fixture/content assertion failed. |
| Login regression | `php artisan test tests/Feature/IdentityAccess/LoginWithUsernameTest.php --stop-on-failure` | fail | Existing test expects `/home`; current application redirects to `/dashboard` for operators. |
| Lint / diff | PHP lint for policy/test and `git diff --check` | pass | No syntax or diff errors. |
| Browser smoke test | Admin desktop and mobile menu inspection | not-run | No browser automation or manual browser session was available. |

## Output Excerpts

```
Focused dashboard authorization: 5 passed (6 assertions)
OperatorDashboardViewTest: 3 passed (5 assertions)
Admin dashboard direct access: passed
Admin operator dashboard access: passed as 403
```

Unrelated existing failures:

```
Expected: Monto bruto operado, but it was not rendered in the seeded scenario
Expected redirect: /home, actual redirect: /dashboard
```

## Residual Risks

- Admin desktop/mobile navigation was validated statically, not through a browser-rendered smoke test.
- Existing Admin bookmarks to `/dashboard` now receive 403 by design; redirect compatibility remains an open product decision.
- The login and reporting suites contain stale expectations unrelated to this fix and are not fully green.

## Recommendation

Hold for a browser smoke test confirming that an Admin sees only `Dashboard Admin` in both desktop and mobile menus. The critical authorization behavior is verified; after the smoke test, close the bug if the navigation matches the expected role separation.

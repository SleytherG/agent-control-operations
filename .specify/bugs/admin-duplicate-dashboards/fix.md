# Bug Fix: Admin users see an operator dashboard as a second dashboard

- **Slug**: admin-duplicate-dashboards
- **Fixed**: 2026-07-23T15:37:22-05:00
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Restricted the operator dashboard to operator users and removed the duplicate operator-dashboard link from the administrator desktop and mobile navigation. Administrators now have one canonical dashboard destination, `/admin/dashboard`, while operators retain `/dashboard`.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `app/Modules/Reporting/Policies/DashboardPolicy.php` | modified | `viewOperatorDashboard()` now authorizes only `Role::OPERADOR`. |
| `resources/views/components/layout/sidebar.blade.php` | modified | Removed the operator `Dashboard` link from the Admin navigation section. |
| `resources/views/components/layout/mobile-nav.blade.php` | modified | Removed the duplicate operator dashboard link from the Admin mobile menu. |
| `tests/Feature/Reporting/DashboardAuthorizationTest.php` | modified | Changed the Admin operator-dashboard expectation from 200 to 403 and verified the admin dashboard does not link to the operator route. |

## Diff Highlights

```php
public function viewOperatorDashboard(User $actor): bool
{
    return $actor->role === Role::OPERADOR;
}
```

Administrators remain authorized for `admin.dashboard` and `admin.dashboard.operators`.

## Tests Added or Updated

- `DashboardAuthorizationTest::test_admin_cannot_access_operator_dashboard` - verifies Admin receives 403 for `/dashboard`.
- `DashboardAuthorizationTest::test_operator_can_access_operator_dashboard` - preserves operator access.
- `DashboardAuthorizationTest::test_admin_can_access_admin_dashboard` - preserves the canonical Admin dashboard and asserts it does not expose the operator route.
- `OperatorDashboardViewTest` - existing operator dashboard coverage remains passing.

## Local Verification

- `php artisan test tests/Feature/Reporting/DashboardAuthorizationTest.php --stop-on-failure` -> 9 authorization tests passed; one pre-existing content assertion failed because `Monto bruto operado` was not rendered in the seeded scenario.
- `php artisan test tests/Feature/Reporting/OperatorDashboardViewTest.php --stop-on-failure` -> 3 passed (5 assertions).
- `php -l app/Modules/Reporting/Policies/DashboardPolicy.php` -> passed.
- `php -l tests/Feature/Reporting/DashboardAuthorizationTest.php` -> passed.
- `git diff --check` -> passed.
- Manual checks: confirmed the only remaining `dashboard.operator` links in the layout are inside the operator-only branches of sidebar and mobile navigation.

## Deviations from Assessment

None. The preferred 403 behavior was applied for direct Admin visits to `/dashboard`.

## Follow-ups

- Decide whether legacy Admin bookmarks to `/dashboard` should remain 403 or be redirected to `/admin/dashboard`.
- Repair the unrelated `Monto bruto operado` authorization test fixture/assertion if the reporting suite is expected to be fully green.
- Perform a browser smoke test for Admin desktop and mobile menus to confirm only `Dashboard Admin` is visible.

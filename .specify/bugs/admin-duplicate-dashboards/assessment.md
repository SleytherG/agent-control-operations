# Bug Assessment: Admin users see an operator dashboard as a second dashboard

- **Slug**: admin-duplicate-dashboards
- **Created**: 2026-07-23T15:37:22-05:00
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: medium

## Report (verbatim or summarized)

When logging in as an administrator, the application exposes two dashboard destinations: `/admin/dashboard` and `/dashboard`. The report questions why the administrator sees both when the administrator should operate in audit mode only, reviewing operations, sessions, and daily closings rather than creating operations.

No URL was supplied. The paths in the report are local application routes, so no external URL fetch was performed.

## Symptom

Admin login correctly redirects to `/admin/dashboard`, but the admin sidebar and mobile navigation also expose a second `Dashboard` link to `/dashboard`, the operator dashboard. The operator dashboard policy explicitly allows administrators, so an administrator can open both dashboard implementations instead of having one canonical audit dashboard.

Expected behavior is that administrators see and use only the admin audit dashboard, while operators retain access to `/dashboard` for operator-specific metrics and operational workflow.

## Reproduction

1. Log in with a user whose role is `ADMINISTRADOR_PROPIETARIO`.
2. Confirm the login redirect goes to `admin.dashboard` (`/admin/dashboard`).
3. Open the admin sidebar or mobile menu and observe a second `Dashboard` entry under the Operations section.
4. Follow that entry to `/dashboard` and observe the operator dashboard is accessible to the administrator.

## Suspected Code Paths

- `app/Modules/IdentityAccess/Http/Controllers/LoginController.php:78-82` - login already selects `admin.dashboard` for `ADMINISTRADOR_PROPIETARIO`, establishing the intended canonical destination.
- `routes/reporting.php:7-12` - defines separate operator and admin dashboard routes, both under the same authentication middleware.
- `app/Modules/Reporting/Policies/DashboardPolicy.php:10-18` - `viewOperatorDashboard()` currently allows both `ADMINISTRADOR_PROPIETARIO` and `OPERADOR`, making `/dashboard` accessible to admins.
- `resources/views/components/layout/sidebar.blade.php:36-71` - the admin navigation includes `Dashboard Admin` and a second `Dashboard` link to `dashboard.operator`.
- `resources/views/components/layout/mobile-nav.blade.php:16-24` - duplicates the same two dashboard destinations in the mobile menu.
- `resources/views/reporting/admin-dashboard.blade.php` and `resources/views/reporting/operator-dashboard.blade.php` - render distinct dashboard experiences, confirming this is not only a duplicate URL alias.
- `tests/Feature/Reporting/DashboardAuthorizationTest.php:42-56` - explicitly codifies that an admin may access the operator dashboard, which conflicts with the reported role separation.

## Root Cause Hypothesis

**Confidence: high.** The duplication is intentional in the current code paths but inconsistent with the requested role model: the admin navigation links to both dashboards, and `DashboardPolicy::viewOperatorDashboard()` grants admin access to the operator route. The login redirect itself is not the source of the duplicate; it already points administrators to `/admin/dashboard`. The issue is the shared authorization policy and admin navigation exposing an operator-only destination to the admin role.

## Proposed Remediation

**Preferred**: Make `viewOperatorDashboard()` return true only for `Role::OPERADOR`. Remove the operator-dashboard link from the administrator branches of `sidebar.blade.php` and `mobile-nav.blade.php`, leaving the admin dashboard, history, daily closing, session review, and other audit/navigation links intact. Keep `/dashboard` as the operator dashboard route for operators. An admin visiting `/dashboard` directly should receive 403, preventing the duplicate experience rather than merely hiding the link.

Update authorization tests to assert the new role boundary, and add navigation assertions that an administrator renders only one dashboard link while an operator still sees `/dashboard`. Preserve administrator access to `/admin/dashboard/operators`, since that is an audit/comparison view under the admin dashboard family rather than the operator workspace.

**Alternatives**:

- Redirect an administrator from `/dashboard` to `/admin/dashboard` instead of returning 403. This is more forgiving for bookmarks but can hide authorization mistakes and makes the operator route less strictly role-specific.
- Merge both dashboard views into one role-aware `/dashboard` route. This removes a route distinction but is a larger redesign and risks mixing operator actions with administrator audit metrics.

**Files likely to change**:

- `app/Modules/Reporting/Policies/DashboardPolicy.php`
- `resources/views/components/layout/sidebar.blade.php`
- `resources/views/components/layout/mobile-nav.blade.php`
- `tests/Feature/Reporting/DashboardAuthorizationTest.php`
- Existing layout/navigation feature tests covering role-specific links, if present.

**Tests to add or update**:

- Admin access to `dashboard.operator` returns 403.
- Operator access to `dashboard.operator` remains 200.
- Admin access to `admin.dashboard` and `admin.dashboard.operators` remains 200.
- Admin desktop and mobile navigation contain `Dashboard Admin` but not the operator `Dashboard` link.
- Operator navigation continues to contain its single `/dashboard` link and operation-registration access.
- Login for an administrator continues redirecting to `admin.dashboard`.

## Risks & Considerations

- Existing users or bookmarks that send admins to `/dashboard` will receive 403 after the role boundary is tightened; a redirect alternative could be chosen if backward navigation compatibility is required.
- Existing tests and any external consumers may currently depend on admin access to operator metrics; the assessment found an explicit test that must change.
- Removing the admin navigation link changes only presentation and authorization; no database or API migration is expected.
- The admin dashboard must continue to expose sufficient audit metrics, operation history, session history, and daily closing links after the operator dashboard link is removed.

## Open Questions

- [NEEDS CLARIFICATION: Should direct admin visits to `/dashboard` return 403 or redirect to `/admin/dashboard`?]
- [NEEDS CLARIFICATION: Should the admin audit dashboard include any operator-specific metric currently available only at `/dashboard`?]

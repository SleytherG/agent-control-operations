# Bug Fix: Admin dashboard 500 after login — missing bcmath extension

- **Slug**: admin-dashboard-bcmath-500
- **Fixed**: 2026-07-27
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Added the `bcmath` PHP extension to both Dockerfile stages. This fixes the `Call to undefined function bcsub()` fatal error thrown by `DashboardController::adminDashboard()` at line 138, which requires `bcsub()` for financial net-movement calculation. The daily closing feature (`CalculateClosing`) also depends on `bcmath` and was silently broken as well.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `Dockerfile:7` | modified | Added `bcmath` to `docker-php-ext-install` in the build stage |
| `Dockerfile:29` | modified | Added `bcmath` to `docker-php-ext-install` in the runtime stage |

## Diff Highlights

```diff
-    && docker-php-ext-install pdo pdo_pgsql zip opcache \
+    && docker-php-ext-install pdo pdo_pgsql zip opcache bcmath \
```

Both build stage (line 7) and runtime stage (line 29) received the same change.

## Tests Added or Updated

- *None* — the fix is an infrastructure change (Dockerfile). A passing deploy is the validation.
- Recommended follow-up: add a PHPUnit test or custom health-check route that calls `bcsub('1', '0', 2)` to confirm `bcmath` is available at runtime.

## Local Verification

Build not possible locally without Docker. Verification deferred to Render deploy:
- Commit `747468e` pushed to `main`.
- Render should rebuild the image, including `bcmath` extension.
- Post-deploy: `GET /admin/dashboard` should return 200 after successful login.

## Deviations from Assessment

None. The fix is exactly what the assessment proposed.

## Follow-ups

- Verify the deploy on Render: log in as `admin`/`password` and confirm `/admin/dashboard` loads.
- If the dashboard still returns 500 after deploy, re-run `/speckit.bug.assess` — the root cause may be different or there may be a secondary issue.
- Consider adding a PHP health-check endpoint that validates extensions (`bcmath`, `pdo_pgsql`, etc.) are loaded.

# Bug Assessment: Admin dashboard 500 after login — missing bcmath extension

- **Slug**: admin-dashboard-bcmath-500
- **Created**: 2026-07-27
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: high

## Report (verbatim or summarized)

> "Persiste el problema que despues del login correcto sale error 500"

User reports successful login (admin/password) followed by HTTP 500 when redirected to `/admin/dashboard`. Render logs show the request hits the middleware pipeline (#9–#54 in the stack trace) and returns 500.

## Symptom

Admin user logs in successfully, receives JWT cookies, is redirected to `/admin/dashboard`, but the dashboard page returns HTTP 500 instead of rendering. Expected: admin dashboard with metrics, charts, and operation data.

## Reproduction

1. Visit `https://agent-control-operations.onrender.com/login`
2. Log in with `admin` / `password`
3. Observe redirect to `/admin/dashboard` returns 500

## Suspected Code Paths

- `app/Modules/Reporting/Http/Controllers/DashboardController.php:138` — calls `bcsub((string) ($metrics->cash_in ?? 0), (string) ($metrics->cash_out ?? 0), 2)` which requires the `bcmath` PHP extension
- `app/Modules/DailyClosing/Application/Actions/CalculateClosing.php:35-52` — also uses `bcadd()` and `bcsub()` in closing calculations
- `Dockerfile:7` (build stage) and `Dockerfile:29` (runtime stage) — both install only `pdo pdo_pgsql zip opcache`; neither includes `bcmath`

## Root Cause Hypothesis

**Confidence: high.** The PHP `bcmath` extension is not installed in the Docker runtime image. The `DashboardController::adminDashboard()` method at line 138 calls `bcsub()` which triggers a `Call to undefined function bcsub()` fatal error → HTTP 500. The login succeeded because `AuthenticateAndStartSession` does not use `bcmath` functions — only the dashboard controller and daily closing actions do.

## Proposed Remediation

**Preferred**: Add `bcmath` to both Docker stages (`docker-php-ext-install`). This is a one-line addition per stage:

```dockerfile
docker-php-ext-install pdo pdo_pgsql zip opcache bcmath
```

**Alternatives**:
- Replace all `bcsub()` / `bcadd()` calls with standard PHP float arithmetic. However, this loses arbitrary-precision decimal math, which could cause rounding errors in financial calculations (daily closings, net movement display). Not recommended.
- Replace with `number_format()` formatting only (display layer). This fixes the dashboard but not the closing calculations.

**Files likely to change**:
- `Dockerfile` (both stages, lines 7 and 29)

**Tests to add or update**:
- Integration test: verify admin dashboard loads with HTTP 200 after successful login.
- Unit test: verify `bcmath` functions are available in the PHP environment.

## Risks & Considerations

- `bcmath` is a standard PHP extension included in the `php:8.4-fpm` Docker image but not enabled by default. Installing it adds negligible overhead (~50KB library).
- The daily closing feature (`CalculateClosing`) also uses `bcmath` and would fail when accessed.
- No migration, no code changes beyond the Dockerfile — safe to deploy.

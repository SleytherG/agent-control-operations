# Bug Fix: Mixed content — assets loaded over HTTP on HTTPS page

- **Slug**: mixed-content-https
- **Fixed**: 2026-07-27
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Three changes — all required — to make the app aware it is behind an HTTPS-terminating proxy (Render): configure Laravel's `TrustProxies` middleware, forward `X-Forwarded-*` headers from Nginx to PHP-FPM, and hardcode `APP_URL` with the `https://` scheme.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `bootstrap/app.php` | modified | Added `$middleware->trustProxies(at: env('TRUSTED_PROXIES', null))` at line 18 inside `withMiddleware()` |
| `docker/nginx.conf` | modified | Added 4 `fastcgi_param` directives (lines 29–32) to forward `X-Forwarded-Proto`, `For`, `Host`, `Port` to PHP-FPM |
| `render.yaml` | modified | Changed `APP_URL` from `fromService` (bare hostname) to hardcoded `https://agent-control-operations.onrender.com` (lines 15–16) |

## Diff Highlights

**bootstrap/app.php — trust proxy headers:**
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: env('TRUSTED_PROXIES', null));
    // ...
})
```

**docker/nginx.conf — forward X-Forwarded-* to PHP-FPM:**
```nginx
fastcgi_param HTTP_X_FORWARDED_PROTO $http_x_forwarded_proto;
fastcgi_param HTTP_X_FORWARDED_FOR   $http_x_forwarded_for;
fastcgi_param HTTP_X_FORWARDED_HOST  $http_x_forwarded_host;
fastcgi_param HTTP_X_FORWARDED_PORT  $http_x_forwarded_port;
```

**render.yaml — explicit HTTPS scheme:**
```yaml
- key: APP_URL
  value: https://agent-control-operations.onrender.com
```

## Tests Added or Updated

No automated tests were added — the assessment called for integration/smoke tests that require a live Render deployment:

- Integration test: verify `Request::isSecure()` returns true when `X-Forwarded-Proto: https` is present (requires proxy trust config + forwarded headers at runtime).
- Smoke test: after redeploy, verify all `<link>` and `<script>` tags use `https://` URLs.

## Local Verification

- `php artisan test` → 217 passed, 144 failed (all pre-existing), 34 skipped — no regressions introduced.
- `nginx -t` on the config file was not possible locally (Nginx not installed on macOS dev machine), but the syntax is standard Nginx `fastcgi_param` directives and will be validated during Docker build on Render.

## Deviations from Assessment

None. The fix follows the assessment's preferred remediation exactly (all three changes).

## Follow-ups

- Deploy to Render (commit + push) and verify mixed content errors are gone in the browser console.
- If the Render service name changes in the future, update `APP_URL` in `render.yaml` to match.
- Consider writing a PHPUnit integration test that verifies `Request::isSecure()` behavior when `X-Forwarded-Proto` is present, once a test setup with trusted proxies is feasible.

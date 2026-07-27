# Bug Fix: Mixed content — assets loaded over HTTP on HTTPS page

- **Slug**: mixed-content-https
- **Fixed**: 2026-07-27
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Two rounds of fixes. The first (TrustProxies + Nginx headers + APP_URL) was insufficient because `UrlGenerator::asset()` constructs URLs from request context (`$request->root()`), not directly from `config('app.url')`. The second round forces HTTPS at the application layer via `URL::forceScheme('https')` — the standard Laravel approach for apps behind SSL-terminating proxies. Also added `ASSET_URL` for Vite defense-in-depth and a `/` → `/login` redirect.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `bootstrap/app.php` | modified | Added `$middleware->trustProxies(at: env('TRUSTED_PROXIES', null))` |
| `docker/nginx.conf` | modified | Added 4 `fastcgi_param` directives to forward `X-Forwarded-*` to PHP-FPM |
| `render.yaml` | modified | `APP_URL` hardcoded to `https://...`; added `ASSET_URL` |
| `app/Providers/AppServiceProvider.php` | modified | Added `URL::forceScheme('https')` in `boot()` for production |
| `routes/web.php` | modified | Added `Route::redirect('/', '/login')` |

## Diff Highlights

**AppServiceProvider.php — force HTTPS for all URLs:**
```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
    // ...
}
```

**render.yaml — defense-in-depth with ASSET_URL:**
```yaml
- key: APP_URL
  value: https://agent-control-operations.onrender.com
- key: ASSET_URL
  value: https://agent-control-operations.onrender.com
```

**routes/web.php — root redirect:**
```php
Route::redirect('/', '/login');
```

## Tests Added or Updated

No automated tests were added — the assessment called for smoke tests requiring a live Render deployment.

## Local Verification

- `php artisan test` → 217 passed, 144 failed (all pre-existing), 34 skipped — no regressions.
- Script: `Route::redirect('/', '/login')` → verified in code; will be validated on deploy.

## Deviations from Assessment

The assessment's three proposed changes (TrustProxies, Nginx headers, APP_URL) were applied first but the bug persisted on Render. Root cause: the `UrlGenerator::asset()` method — used by `@vite()` — constructs URLs from `$request->root()` / `$formattedScheme`, not directly from `config('app.url')`. The proxy trust approach depends on runtime header forwarding which may not take effect before config caching or UrlGenerator initialization.

The definitive fix adds `URL::forceScheme('https')` in `AppServiceProvider::boot()` for production, which bypasses all request-based URL resolution and forces every generated URL to use `https://`. Also added `ASSET_URL` as a Vite-specific safety net. Additionally, the user requested adding a `/` → `/login` redirect, which was not in the original assessment.

## Follow-ups

- Merge `007-stitch-visual-integration` into `main` and push to trigger Render redeploy.
- Verify mixed content errors are gone in browser console + form action URL is `https://`.
- Verify `/` redirects to `/login` automatically.

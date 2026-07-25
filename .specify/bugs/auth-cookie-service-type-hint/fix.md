# Bug Fix: AuthCookieService type-hint rejects RedirectResponse

- **Slug**: auth-cookie-service-type-hint
- **Fixed**: 2026-07-22
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Widened the `$response` parameter and return type-hints in `AuthCookieService` from `Illuminate\Http\Response` to `Response|RedirectResponse` union types. `RedirectResponse` is a sibling class (not a subclass) of `Response` — they share `Symfony\Component\HttpFoundation\Response` as a common ancestor but neither extends the other. Both `withAuthCookies()` and `clearAuthCookies()` were affected; both callers (`LoginController` and `LogoutController`) pass `RedirectResponse`.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `app/Modules/IdentityAccess/Services/AuthCookieService.php` | modified | Widened `withAuthCookies()` and `clearAuthCookies()` param/return types to `Response\|RedirectResponse`; added `RedirectResponse` import |
| `tests/Unit/AuthCookieServiceTest.php` | added | 4 tests covering both methods with both `Response` and `RedirectResponse` inputs |

## Diff Highlights

```php
// app/Modules/IdentityAccess/Services/AuthCookieService.php
-use Illuminate\Http\Response;
+use Illuminate\Http\RedirectResponse;
+use Illuminate\Http\Response;

-public function withAuthCookies(Response $response, string $accessToken, string $refreshToken, int $ttl): Response
+public function withAuthCookies(Response|RedirectResponse $response, string $accessToken, string $refreshToken, int $ttl): Response|RedirectResponse

-public function clearAuthCookies(Response $response): Response
+public function clearAuthCookies(Response|RedirectResponse $response): Response|RedirectResponse
```

## Tests Added or Updated

- `tests/Unit/AuthCookieServiceTest::test_with_auth_cookies_accepts_response` — verifies `withAuthCookies()` works with `Response`
- `tests/Unit/AuthCookieServiceTest::test_with_auth_cookies_accepts_redirect_response` — verifies `withAuthCookies()` works with `RedirectResponse` (the failing case from the bug report)
- `tests/Unit/AuthCookieServiceTest::test_clear_auth_cookies_accepts_response` — verifies `clearAuthCookies()` works with `Response`
- `tests/Unit/AuthCookieServiceTest::test_clear_auth_cookies_accepts_redirect_response` — verifies `clearAuthCookies()` works with `RedirectResponse` (as used by `LogoutController`)

## Local Verification

- `php artisan test --filter="AuthCookieServiceTest"` → **4 passed** (4 assertions)
- `php artisan test --filter="LoginViewTest|LoginWithUsernameTest|LoginWithEmailTest"` → **9 passed** (27 assertions), including `test_login_with_valid_credentials_redirects_to_home` which was previously failing with the exact `TypeError` from this bug

## Deviations from Assessment

None — the fix matches the preferred remediation exactly.

## Follow-ups

- The broader IdentityAccess test suite has remaining pre-existing failures (e.g., `AuthSession::factory()` undefined in `LogoutTest`, `RefreshSessionTest`). These are unrelated to this fix.

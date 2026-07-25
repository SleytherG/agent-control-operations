# Bug Assessment: AuthCookieService type-hint rejects RedirectResponse

- **Slug**: auth-cookie-service-type-hint
- **Created**: 2026-07-22
- **Source**: pasted text (stack trace from POST /login)
- **Verdict**: valid
- **Severity**: high

## Report (verbatim)

```
TypeError: App\Modules\IdentityAccess\Services\AuthCookieService::withAuthCookies():
Argument #1 ($response) must be of type Illuminate\Http\Response,
Illuminate\Http\RedirectResponse given, called in
app/Modules/IdentityAccess/Http/Controllers/LoginController.php on line 80
```

PHP 8.5.8, Laravel 13.21.1. Triggered during `POST /login` after successful authentication and `AuthSession`/`SessionEvent` creation. The crash occurs when the controller tries to attach auth cookies to the redirect response.

Note: the `session_events` INSERT in the database queries includes `created_at`, confirming the previous bug (`session-events-fillable-missing-created-at`) is already resolved.

## Symptom

On successful login with valid credentials, the server returns HTTP 500 after creating the session and event records. The user is not redirected to `/home`. This blocks ALL successful login attempts.

## Reproduction

1. Start the application with `php artisan serve`
2. Navigate to `/login`
3. Enter valid credentials (e.g. admin / password)
4. Observe HTTP 500 with `TypeError` from `AuthCookieService::withAuthCookies()`

## Suspected Code Paths

- `app/Modules/IdentityAccess/Services/AuthCookieService.php:25` — `withAuthCookies(Response $response, ...)` type-hints `Illuminate\Http\Response`, which does not accept `RedirectResponse`.
- `app/Modules/IdentityAccess/Http/Controllers/LoginController.php:78-85` — creates a `RedirectResponse` via `redirect()->route('home')` and passes it to `withAuthCookies()`.
- `app/Modules/IdentityAccess/Services/AuthCookieService.php:38` — `clearAuthCookies(Response $response)` has the same restrictive type-hint, though not currently triggered by the login flow.

## Root Cause Hypothesis

**Confidence: high.** `Illuminate\Http\RedirectResponse` extends `Symfony\Component\HttpFoundation\RedirectResponse`, NOT `Illuminate\Http\Response`. They are sibling classes sharing `Symfony\Component\HttpFoundation\Response` as their common ancestor. The type-hint `Illuminate\Http\Response` is overly restrictive — it should accept both `Response` and `RedirectResponse`, both of which support `withCookie()` and `withoutCookie()`.

## Proposed Remediation

**Preferred**: Widen the parameter type-hint to `Response|RedirectResponse` using a PHP union type. The return type should match the widened parameter type to avoid covariant return type violations.

```php
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

// AuthCookieService.php
public function withAuthCookies(Response|RedirectResponse $response, string $accessToken, string $refreshToken, int $ttl): Response|RedirectResponse

public function clearAuthCookies(Response|RedirectResponse $response): Response|RedirectResponse
```

**Alternatives**:
- Remove the type-hint entirely (`$response` untyped) — fixes the crash but loses type safety and IDE support.
- Extract cookie attachment to a standalone step (e.g., `queueAuthCookies()` + apply in middleware) — more refactoring than warranted for this bug.
- Change `LoginController::login()` to return `Response` instead of `RedirectResponse` and manually redirect — works but goes against Laravel conventions.

**Files likely to change**:
- `app/Modules/IdentityAccess/Services/AuthCookieService.php`
- `tests/Feature/IdentityAccess/LoginViewTest.php` (unskip/adjust related test)

**Tests to add or update**:
- Update `LoginViewTest::test_login_with_valid_credentials_redirects_to_home` — currently fails due to this bug; should pass after fix.
- Add a unit test for `AuthCookieService::withAuthCookies()` accepting a `RedirectResponse`.

## Risks & Considerations

- Low risk: `RedirectResponse` supports the same `withCookie()` and `withoutCookie()` methods as `Response` (confirmed via reflection). The behavior is identical.
- The `clearAuthCookies()` method has the same restrictive type-hint but its current callers may only pass `Response`. Fixing it proactively prevents future bugs.
- Union types require PHP 8.0+ (project is on PHP 8.5.8 — compatible).

## Open Questions

None.

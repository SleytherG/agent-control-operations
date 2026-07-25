# Bug Fix: Session refresh fails while attaching auth cookies

- **Slug**: session-refresh-500-persists
- **Fixed**: 2026-07-23T15:15:58-05:00
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Updated `AuthCookieService` to accept and return `JsonResponse`, which is the response type created by `/auth/refresh`. Added a successful HTTP refresh test using an encrypted refresh cookie, confirming that the response is returned successfully and replacement cookies are issued.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `app/Modules/IdentityAccess/Services/AuthCookieService.php` | modified | Added `JsonResponse` to the `withAuthCookies()` parameter and return unions. |
| `tests/Feature/IdentityAccess/RefreshSessionTest.php` | modified | Added successful encrypted-cookie HTTP refresh coverage and refresh-token reuse assertions. |

## Diff Highlights

```php
public function withAuthCookies(
    Response|RedirectResponse|JsonResponse $response,
    string $accessToken,
    string $refreshToken,
    int $ttl,
): Response|RedirectResponse|JsonResponse
```

The feature test calls `withCredentials()->withCookie(...)->postJson('/auth/refresh')`, matching the browser request's `credentials: 'same-origin'` behavior.

## Tests Added or Updated

- `tests/Feature/IdentityAccess/RefreshSessionTest.php::test_refresh_endpoint_rotates_an_encrypted_cookie_and_returns_json` - pins down HTTP 200, ISO-8601 `expiresAt`, replacement cookies, token rotation, and an active session.
- The same test verifies reuse of the consumed refresh token returns 401 and revokes the session.

## Local Verification

- `php artisan test tests/Feature/IdentityAccess/RefreshSessionTest.php --stop-on-failure` -> 3 passed (15 assertions).
- `php artisan test tests/Unit/AuthCookieServiceTest.php --stop-on-failure` -> 4 passed (4 assertions).
- `php artisan test tests/Feature/Agents/ --stop-on-failure` -> 17 passed (38 assertions).
- `git diff --check` -> passed.
- Manual checks: verified the successful HTTP test uses Laravel's encrypted-cookie path and credentials behavior; no real browser session or supplied credentials were replayed.

## Deviations from Assessment

None. The preferred remediation was applied within the assessed files. The test initially returned 401 because Laravel's test client does not send cookies for JSON requests unless `withCredentials()` is enabled; the test was corrected to model the browser's request behavior.

## Follow-ups

- Invalidate the cookies supplied in the original report because they were exposed in plaintext in the issue text.
- Run `/speckit.bug.test slug=session-refresh-500-persists` for the formal verification report.

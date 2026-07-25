# Bug Assessment: Session refresh fails while attaching auth cookies

- **Slug**: session-refresh-500-persists
- **Created**: 2026-07-23T15:15:58-05:00
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: high

## Report (verbatim or summarized)

After the session-expiry modal appears, selecting the option to continue sends `POST /auth/refresh`. The request returns HTTP 500, the current session is lost, and the browser redirects to `/login`. The supplied response was generated at `2026-07-23 20:11:57 GMT` with correlation ID `d1783aba-3375-4345-a203-8c5b440f6b68`.

The pasted request included access, refresh, CSRF, and session cookies. Their values are intentionally omitted from this assessment and should be treated as exposed credentials.

URL trust record:

- URL supplied: `http://localhost:8000/auth/refresh`
- Parsed host: `localhost`
- Policy branch: `auto-refused: loopback host`
- No HTTP fetch or replay using the supplied credentials was performed.

## Symptom

A valid refresh request rotates the server-side token but fails with HTTP 500 while constructing the response. The expected behavior is HTTP 200 with a valid `expiresAt` value and replacement access/refresh cookies, allowing the modal to close and the session to continue.

Because the browser does not receive the replacement refresh cookie, it retains a token that the server has already marked as consumed. A subsequent refresh attempt is classified as token reuse and revokes the authentication session, which explains the redirect to login.

## Reproduction

1. Log in and wait until the session-expiry modal appears.
2. Select the option to continue the session.
3. Observe `POST /auth/refresh` return HTTP 500 instead of HTTP 200.
4. Observe the client redirect to `/login`; a retry with the retained refresh cookie can revoke the session as refresh-token reuse.

## Suspected Code Paths

- `app/Modules/IdentityAccess/Services/AuthCookieService.php:26` - `withAuthCookies()` accepts only `Illuminate\Http\Response|Illuminate\Http\RedirectResponse`, excluding the `Illuminate\Http\JsonResponse` produced by the refresh controller.
- `app/Modules/IdentityAccess/Http/Controllers/RefreshSessionController.php:32-40` - creates a `JsonResponse` and passes it to the incompatible `withAuthCookies()` parameter after token rotation has completed.
- `app/Modules/IdentityAccess/Application/Actions/RotateRefreshToken.php:29-116` - commits the old token as `CONSUMED` and creates its replacement before control returns to the controller; the later response type error cannot roll this transaction back.
- `app/Modules/IdentityAccess/Application/Actions/RotateRefreshToken.php:51-65` - treats reuse of the old consumed token as a security failure and revokes the session.
- `resources/js/identity-access/session-timer.js:70-79` - redirects to login for every non-successful refresh response, including this server error.
- `tests/Feature/IdentityAccess/RefreshSessionTest.php:21-67` - tests the action directly and the endpoint without a cookie, but never executes the successful HTTP path that returns a `JsonResponse` and attaches replacement cookies.

## Root Cause Hypothesis

**Confidence: high.** The application log at `storage/logs/laravel.log:47479` records the failure at the exact timestamp from the report: `AuthCookieService::withAuthCookies(): Argument #1 ($response) must be of type Illuminate\Http\Response|Illuminate\Http\RedirectResponse, Illuminate\Http\JsonResponse given`. The previous ISO-8601 failure is no longer the active cause; execution now passes serialization and fails at `RefreshSessionController.php:36`. Since `RotateRefreshToken::execute()` commits before `withAuthCookies()` is invoked, the HTTP failure also strands the client with a consumed token.

## Proposed Remediation

**Preferred**: Expand the accepted and returned response union in `AuthCookieService::withAuthCookies()` to include `Illuminate\Http\JsonResponse`. Keep the existing cookie configuration and controller behavior unchanged. This is the smallest correction and reflects every response type currently passed to the service.

Add a full HTTP feature test for the successful refresh path. Create an active session and refresh-token row, send the refresh token through Laravel's encrypted-cookie test mechanism, call `POST /auth/refresh`, and assert HTTP 200, a parseable `expiresAt`, both replacement auth cookies, the old token in `CONSUMED` state, the new token in `ACTIVE` state, and the session still `ACTIVE`. This test would have caught both the original date-format error and the current response-type error.

**Alternatives**:

- Attach the cookies directly in `RefreshSessionController`; this avoids the service type declaration but duplicates security-sensitive cookie construction and is not preferred.
- Generalize the service to a broader response abstraction. Symfony's base `Response` includes all three runtime response types, but it does not declare Laravel's fluent `withCookie()` method, so that choice weakens static guarantees unless cookie attachment is rewritten.

**Files likely to change**:

- `app/Modules/IdentityAccess/Services/AuthCookieService.php`
- `tests/Feature/IdentityAccess/RefreshSessionTest.php`

**Tests to add or update**:

- Valid encrypted refresh cookie returns HTTP 200 and ISO-8601 `expiresAt`.
- Successful refresh response contains replacement access and refresh cookies.
- Successful HTTP refresh consumes the prior token, creates one active replacement, and leaves the session active.
- Reusing the prior refresh cookie returns HTTP 401 and revokes the session, preserving the intended reuse defense.

## Risks & Considerations

- The current failure occurs after refresh-token rotation commits, so each failed attempt can invalidate the client's token and a retry can revoke the whole session.
- The pasted cookies are authentication material. Invalidate the affected session and avoid storing their full values in bug artifacts or logs.
- The existing test report for `session-refresh-500` was a false positive for end-to-end behavior because it explicitly omitted the successful HTTP cookie flow.
- No migration or public API change is required; the successful JSON response contract remains unchanged.
- Client behavior currently redirects to login for all non-2xx responses. That is consistent with authentication failure but makes server defects immediately session-ending.

## Open Questions

- [NEEDS CLARIFICATION: Has the session represented by the pasted cookies already been invalidated?]

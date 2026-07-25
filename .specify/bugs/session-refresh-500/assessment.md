# Bug Assessment: Session refresh 500 error

- **Slug**: session-refresh-500
- **Created**: 2026-07-23
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: high

## Report (verbatim or summarized)

> "Veo que al pulsar en el boton 'continuar sesion' llama al servicio /auth/refresh, pero este da error 500 entonces por este motivo entiendo que da el error y cierra la sesion y redirige al login, revisa del porque pasa esto con ese servicio y corrigelo"

Translation: Clicking "Continuar sesion" calls `/auth/refresh`, which returns HTTP 500, causing logout and redirect to login.

## Symptom

`POST /auth/refresh` returns HTTP 500 instead of JSON `{ expiresAt: "..." }`. The JS `session-timer.js` receives a non-2xx response, runs `cleanupAndLogin()`, and redirects the user to `/login` — forcibly ending their session despite clicking "Continue."

## Reproduction

1. Log in to the application.
2. Wait until the session expiry modal appears (currently at 270s remaining for testing).
3. Click "Continuar sesion".
4. Observe in browser network tab: `POST /auth/refresh` returns 500.
5. User is redirected to `/login`.

## Suspected Code Paths

- `app/Modules/IdentityAccess/Http/Controllers/RefreshSessionController.php:33` — `$result['expires_at']->toIso8601String()` is called on a `DateTimeImmutable` object, but `toIso8601String()` is a Carbon-only method; does not exist on native PHP `DateTimeImmutable`.
- `app/Providers/AppServiceProvider.php:20-25` — `ClockInterface` implementation returns `new \DateTimeImmutable('now', new \DateTimeZone('UTC'))`, NOT a Carbon instance.
- `app/Modules/IdentityAccess/Services/JwtTokenService.php:34-54` — `issue()` returns `'expires_at' => $expiresAt` where `$expiresAt` is a `DateTimeImmutable` from `$this->clock->now()->modify(...)`.

## Root Cause Hypothesis

**Confidence: high.** The JWT clock returns a native `\DateTimeImmutable`, not a Carbon instance. `JwtTokenService::issue()` returns this `DateTimeImmutable` as `expires_at`. `RotateRefreshToken::execute()` forwards it to the controller. `RefreshSessionController::refresh()` line 33 calls `$result['expires_at']->toIso8601String()`, which throws `Call to undefined method DateTimeImmutable::toIso8601String()` → uncaught exception → HTTP 500.

This does NOT affect the login/meta tag path because the authenticated layout's `<meta name="session-expires-at">` reads `$sessionExpiresAt` from `View::share()` in `AuthenticateJwtSession` middleware, which passes `$session->access_expires_at` — a Carbon instance from the Eloquent model cast. Only the refresh controller hits the DateTimeImmutable path.

## Proposed Remediation

**Preferred**: In `RefreshSessionController::refresh()`, replace `$result['expires_at']->toIso8601String()` with `$result['expires_at']->format(\DateTimeInterface::ATOM)`. Both produce the same ISO 8601 string format; `DateTimeInterface::ATOM` works on both DateTimeImmutable and Carbon.

**Alternatives** (not recommended):
- Change the `ClockInterface` binding in `AppServiceProvider` to return a Carbon instance. This would ripple across all JWT operations and could introduce timezone inconsistencies, since Carbon inherits `DateTimeImmutable` behavior plus Carbon-specific features.

**Files likely to change**:
- `app/Modules/IdentityAccess/Http/Controllers/RefreshSessionController.php`

**Tests to add or update**:
- Unit test: verify `refresh()` returns 200 with valid JSON `{ expiresAt }` format when called with a valid refresh token cookie.
- Unit test: verify `refresh()` returns 401 when cookie is missing or invalid.

## Risks & Considerations

- The DateTimeInterface::ATOM format (`Y-m-d\TH:i:sP`) is identical to Carbon's `toIso8601String()`, so the JS `new Date(data.expiresAt)` parsing is unaffected.
- No DB changes, no migration, no API breakage — single-line fix.

## Open Questions

None.

# Web Endpoint Contracts: Identity And Access

## Shared Rules

- Same-origin HTTPS only. Authentication cookies are `HttpOnly`, `Secure`, `SameSite=Strict`,
  host-only and never returned in JSON or HTML.
- Mutable requests require Laravel CSRF. Authenticated responses use `Cache-Control: no-store`.
- Error bodies never distinguish unknown user, wrong password, inactive account or unknown token.
- Protected HTML includes non-secret `expiresAt` in ISO-8601 UTC for the timer.
- `401` means authentication invalid/expired and causes local cleanup/login. `403` means authenticated
  but unauthorized (session MUST NOT be cleaned; UI displays the error). `419` means CSRF failure
  (session MUST NOT be cleaned; UI refreshes the CSRF token and allows retry). `422` means input
  validation. `429` means throttled.

## GET /login

Returns the server-rendered login form. If credentials are missing/expired, no refresh is attempted.

**200 HTML**: identifier, password and CSRF fields; generic error region.

## POST /login

**Request**: form fields `identifier`, `password`, `_token`.

**Success**: `303` to authenticated home. Sets access and refresh cookies. The target HTML carries
`expiresAt` but no token.

**Failures**: `401` generic invalid credentials/account; `422` malformed input; `429` after the
configured identifier+origin threshold. No session is created on failure.

## POST /auth/refresh

Called only after the user presses `Continuar`; never during page load.

**Request**: CSRF header/form token plus automatically sent auth cookies. Empty JSON body.

**200 JSON**:

```json
{
  "expiresAt": "2026-07-22T18:05:00.000000Z"
}
```

Sets replacement access/refresh cookies after transaction commit. The response closes the modal and
resets the timer.

**401**: access/refresh expired, malformed, session revoked, user inactive, absolute limit reached or
replay. Clears auth cookies. Replay additionally commits `FALLO_SEGURIDAD` revocation.

**409** is not used for duplicate refresh: a consumed token is a security failure and returns 401.

## POST /logout

**Request**: CSRF plus auth cookies.

**Success**: idempotently revokes current session as `LOGOUT_MANUAL`, clears cookies and returns `303`
to `/login`. If already invalid, cookies are still cleared and login is returned without changing the
original terminal reason.

## GET /sessions

Protected server-rendered, always paginated.

**Query**: `page` (>=1), optional `from`, `to`, `status`; optional `user` only for administrator.
Page size is fixed at 25 records with a hard maximum of 100; values exceeding 100 receive 422.

**Authorization**:

- `ADMINISTRADOR_PROPIETARIO`: sessions from all users in the organization and may filter by user.
- `OPERADOR`: server forces `user_id=authenticated user` before any filter; a supplied different
  user parameter is silently ignored and results are always scoped to the authenticated operator.
  The response never reveals whether filtered users exist.

**200 HTML**: rows expose user allowed by policy, startedAt, endedAt, status and endReason. No IP/token
hash or raw user-agent is exposed.

## PATCH /admin/users/{user}/deactivate

Protected by administrator policy and CSRF.

**Request**:

```json
{
  "reason": "Acceso retirado por término de asignación"
}
```

Reason is required and bounded. Target cannot be current administrator. In one transaction the user
becomes inactive, all active sessions become revoked with `REVOCACION_ADMINISTRATIVA`, session events
and before/after audit are appended.

**303/200**: administrative users view/confirmation. **403**: operator or cross-organization target.
**409**: self-deactivation or invariant that would leave no active owner administrator.

## GET /health

Unauthenticated minimal probe. Returns only service status and optional database reachability; never
configuration, exception traces, user/session counts or secrets.

**200 JSON**: `{"status":"ok"}`. **503 JSON**: `{"status":"unavailable"}`.

## Middleware Order

1. Trusted proxy/HTTPS enforcement and request correlation.
2. Cookie encryption as configured and web session support for CSRF only.
3. CSRF for unsafe methods.
4. JWT signature/claims validation.
5. Session/user status lookup.
6. Route rate limits.
7. Policy/Gate and scoped Eloquent query.

CSRF must run before any refresh mutation. No state-changing GET endpoint exists.

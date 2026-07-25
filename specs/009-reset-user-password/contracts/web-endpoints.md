# Web Endpoint Contracts: Restablecimiento Seguro de Contraseña

## Shared Rules

- Same-origin HTTPS, CSRF on every mutation and existing JWT/refresh cookies.
- Server-side Policy always checks actor role, target role, organization and target state.
- Secret responses and authenticated password pages use `Cache-Control: no-store`.
- Passwords and hashes never appear in URL, redirect, session/flash, logs, audit or later responses.
- `401` means authentication invalid/expired; `403` means authenticated but unauthorized; `409`
  means target lifecycle/state conflict; `422` means invalid form or failed step-up; `429` means
  throttled.
- Error messages do not reveal reset state to unauthenticated users.
- All visible timestamps are formatted for `America/Lima`.

## Administrative Views

### GET `/admin/users`

Existing paginated operator list. Each row may expose:

- status of latest reset (`Pendiente`, `Consumido`, `Completado`, `Vencido`, `Reemplazado`);
- issue/expiry time when relevant;
- actions `Restablecer contraseña` and `Ver auditoría` only for authorized administrators.

The query eager-loads or subqueries the latest reset to avoid N+1. No secret is returned.

### GET `/admin/users/{user}/edit`

Existing form adds a separate security section. Reset is not mixed into the ordinary update form.
The reset control opens an accessible confirmation modal containing:

- target username/email;
- warning that target sessions will be revoked;
- current administrator password input;
- optional reason;
- confirm/cancel controls.

An inactive/non-active operator shows guidance to resolve account state first and cannot submit.

## POST `/admin/users/{user}/password-reset`

Protected by `UserPolicy::resetPassword`, CSRF and an administrative step-up limiter. The limiter
permits 5 failed attempts per administrator+origin in 60 seconds, clears after a successful reset
and returns `429` without mutating the target.

**Request form/JSON**:

```json
{
  "admin_password": "current administrator password",
  "reason": "Operator reported forgotten password"
}
```

`reason` is optional and bounded to 500 characters. The password is required but never flashed.

**200 JSON after commit**:

```json
{
  "temporaryPassword": "one-time-visible-secret",
  "issuedAt": "2026-07-23T16:00:00-05:00",
  "expiresAt": "2026-07-23T17:00:00-05:00",
  "deliveryWarning": "Compártela únicamente por un canal privado aprobado."
}
```

The page inserts the secret into the open modal only. Closing clears the DOM; reload/back does not
repeat the POST or expose the response. A copy control announces success accessibly without writing
to application storage.

**Failures**:

- `403`: operator actor, cross-organization target or administrator target.
- `409`: target not `ACTIVE`, reset/deactivation conflict or transaction outcome no longer valid.
- `422`: current administrator password incorrect or malformed request; no target mutation.
- `429`: too many failed step-up attempts.

The success transaction supersedes an earlier non-terminal reset, updates only the target hash,
revokes every active target session/refresh, creates audit and leaves the admin session active.

## GET `/admin/users/{user}/password-resets`

Admin-only server-rendered audit, fixed 25-row pagination.

**Query**: `page>=1`, optional `status`, `from`, `to`.

**200 HTML**: issue/consume/complete/supersede/expire events with actor, target, timestamp, result and
optional reason. No secret, hash, raw IP or token metadata.

**403**: operator or cross-organization administrator. Filters never reveal foreign existence.

## POST `/login`

Existing contract remains, with an atomic reset branch.

**Request**: `identifier`, `password`, `_token`.

**Temporary success**:

- requires latest reset `PENDING` and `now < expires_at`;
- consumes it and creates exactly one linked restricted session in the same transaction;
- sets normal access/refresh cookies;
- returns `303` to `/password/change`.

**Normal success**: unchanged redirect by role.

**Failure**: expired, consumed, superseded or incorrect temporary credentials return the same generic
invalid-login state and obey the existing limit of 5 failures per normalized identifier+origin in
60 seconds. Success clears the counter. No session is created. A lazy expiry transition and its
`password_reset.expired` audit event may commit before returning failure.

## GET `/password/change`

For a linked reset session, returns the mandatory form with:

- new password;
- confirmation;
- policy guidance;
- logout action;
- no application navigation/actions beyond explicit refresh infrastructure.

A normal forced-change session created by initial operator registration remains supported.

## PATCH `/password/change`

**Request**:

```json
{
  "password": "new definitive password",
  "password_confirmation": "new definitive password"
}
```

**303 success**: transaction marks reset `COMPLETED`, replaces the hash, sets
`password_changed_at`, appends audit and redirects to the operator dashboard. The same session
continues as normal.

**422**: weak/mismatched new value, new equal to the current temporary hash, or stale reset state.
No partial state changes and password inputs are never flashed.

**403/redirect**: current session is not the one linked to the consumed reset.

## POST `/logout`

Explicitly allowed for a restricted session. Existing behavior revokes current session/refresh,
clears cookies and redirects to login. The consumed reset remains consumed, so a new administrative
reset is required.

## POST `/auth/refresh`

Allowed as session infrastructure for the same restricted session after explicit user action.
Existing rotation, expiry, replay and absolute-lifetime behavior remains. Refresh does not consume,
complete or reopen a reset, create another session, remove `password_reset_id` or authorize any
functional route.

## Middleware Order

1. HTTPS/trusted proxy and correlation ID.
2. Cookie/session web middleware and CSRF for unsafe methods.
3. JWT, auth-session and active-user validation.
4. Restricted-session lookup/allowlist.
5. Route limiter.
6. Policy and organization-scoped query.

No state-changing GET endpoint exists.

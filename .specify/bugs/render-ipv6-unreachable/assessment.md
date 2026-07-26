# Bug Assessment: Render Cannot Reach Supabase IPv6 Direct Connection

- **Slug**: render-ipv6-unreachable
- **Created**: 2026-07-25
- **Source**: pasted text (Render deployment logs)
- **Verdict**: valid
- **Severity**: critical

## Report (verbatim)

```
SQLSTATE[08006] [7] connection to server at "db.oxtpzimqqhkryvyonkyc.supabase.co"
(2600:1f14:131e:fd02:43b8:dfa0:63f9:4399), port 5432 failed: Network is unreachable
Is the server running on that host and accepting TCP/IP connections?
```

Error repeats on every Render deploy attempt. The entrypoint's `php artisan migrate --force --isolated`
is the first process to trigger it, but any database connection would fail identically.

## Symptom

Render Web Service (Free plan, São Paulo) cannot establish a TCP connection to the Supabase
PostgreSQL direct endpoint `db.oxtpzimqqhkryvyonkyc.supabase.co:5432`. DNS resolves the host
to an IPv6 address (`2600:1f14:131e:fd02:43b8:dfa0:63f9:4399`), but Render Free does not
support IPv6 outbound connectivity, so the TCP handshake fails with "Network is unreachable".
The application never starts because the entrypoint migration step never completes.

## Reproduction

1. Deploy the Laravel application to Render Free plan (São Paulo) configured with
   `DB_HOST=db.oxtpzimqqhkryvyonkyc.supabase.co` (IPv6-only direct connection).
2. Observe entrypoint failure at `php artisan migrate` with `PDOException: Network is unreachable`
   targeting the resolved IPv6 address.
3. Same error reproduced locally from macOS/Docker without IPv6 support.

## Suspected Code Paths

- `docker/entrypoint.sh:5` — Runs `php artisan migrate --force --isolated`, which triggers
  `DatabaseLock` on `cache_locks` table, which is the first PDO connection attempt.
- `config/database.php:47-65` — `pgsql` driver configured with `DB_HOST` from environment.
- `.env` / Render environment variables: `DB_HOST=db.oxtpzimqqhkryvyonkyc.supabase.co`

## Root Cause Hypothesis

**Confidence: high.**

The Supabase project `oxtpzimqqhkryvyonkyc` in region `sa-east-1` publishes only an IPv6
address (AAAA record) for its direct PostgreSQL endpoint. Render's Free plan — like many
serverless/container platforms at the free tier — only supports IPv4 outbound. The IPv6
address resolved by DNS is unreachable from the Render container's network namespace.

This is confirmed by:
- DNS resolution: `db.oxtpzimqqhkryvyonkyc.supabase.co` → `2600:1f14:131e:fd02:43b8:dfa0:63f9:4399` (IPv6 only, no A record)
- Local test from macOS: same "Network is unreachable" error
- Supabase pooler endpoint `aws-0-sa-east-1.pooler.supabase.com` resolves to IPv4 addresses (54.94.90.106, 15.229.150.166) — confirming the pooler is reachable over IPv4

## Proposed Remediation

**Preferred**: Switch `DB_HOST` from the direct IPv6 endpoint to the Supabase **Session Pooler**
endpoint, which provides IPv4 connectivity:

1. In Render environment variables, change:
   - `DB_HOST` = `aws-0-sa-east-1.pooler.supabase.com`
   - `DB_PORT` = `5432` (Session pooler mode)

2. Determine the correct pooler user format. Supabase Session Pooler requires the project
   reference in the username or as a connection option. The exact format can be found in
   **Supabase Dashboard → Database → Connection Pooling → Connection string** (select
   "Session" mode). Copy the exact `DB_USERNAME` and `DB_HOST` from that dialog.

3. If the pooler string from the Dashboard is a URI (`postgresql://...`), extract:
   - `DB_USERNAME` from the URI (the part before `@`)
   - `DB_HOST` from the URI (the hostname after `@`)
   - `DB_PORT` from the URI

4. Keep all other environment variables unchanged.

**Alternatives**:
- Use **Transaction Pooler** (port 6543) with `PDO::ATTR_EMULATE_PREPARES => true` — this
  disables native prepared statements, which may break Eloquent queries. Only consider if
  Session Pooler fails.
- Upgrade Render to a paid plan that supports IPv6 — but this is unnecessary when the pooler
  provides IPv4 access for free.
- Contact Supabase support to enable IPv4 on the direct endpoint — this is a platform
  configuration that may or may not be available on the Free plan.

**Files likely to change**:
- Render environment variables (`DB_HOST`, possibly `DB_USERNAME` and `DB_PORT`) — no code
  changes required. The application already supports the pooler path (PDO PostgreSQL driver
  connects identically regardless of direct vs pooler hostname).

**Tests to add or update**:
- A deployment smoke test that verifies `php artisan migrate --force` succeeds from a
  container with only IPv4.
- A connectivity health check that distinguishes "DNS resolved but unreachable" from "DNS
  not found" from "authentication failed" to speed up troubleshooting.

## Risks & Considerations

- **Session Pooler connection limits**: The user's pooler is configured for 15 connections.
  With `pm.max_children=8` in PHP-FPM, this is sufficient for the Free tier's single instance.
- **Pooler timeout**: PgBouncer has an idle timeout. Laravel's persistent connections should
  handle this transparently, but long-running `php artisan` commands should set
  `connect_timeout` appropriately.
- **Prepared statements**: Session Pooler (port 5432) supports native prepared statements,
  unlike Transaction Pooler (port 6543). No code changes needed.

## Open Questions

- [NEEDS CLARIFICATION: Verify the exact pooler user format from Supabase Dashboard →
  Connection Pooling → Connection string (select Session mode). The `postgres.oxtpzimqqhkryvyonkyc`
  format tested locally returned "tenant not found" — the Dashboard may show a different
  project reference or user format for the pooler.]

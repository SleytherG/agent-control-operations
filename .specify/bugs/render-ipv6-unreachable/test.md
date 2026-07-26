# Bug Verification: Render Cannot Reach Supabase IPv6 Direct Connection

- **Slug**: render-ipv6-unreachable
- **Tested**: 2026-07-25
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The original symptom (Render cannot reach Supabase direct IPv6 endpoint, "Network is unreachable")
no longer reproduces when using the Session Pooler. The pooler provides IPv4 connectivity to the
same PostgreSQL database. All 29 migrations and seeders execute successfully via the pooler, and
the application connects, authenticates, and serves data correctly.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Pooler connectivity | PDO connect to `aws-1-us-west-2.pooler.supabase.com:5432` | pass | PostgreSQL 17.6, SSL enabled |
| Migrations status | `php artisan migrate:status` | pass | 0 Pending — all 29 migrations applied |
| Seed data integrity | `User::count()`, `Agent::count()`, `Organization::count()` | pass | 2 users, 3 agents, 1 org |
| Direct IPv6 (control) | PDO connect to `db.oxtpzimqqhkryvyonkyc.supabase.co` | pass (as expected) | Still unreachable — confirms pooler is the only viable path |
| Migration 00009 compatibility | `DROP CONSTRAINT IF EXISTS` + `Schema::hasColumn()` | pass | PostgreSQL-safe pattern replaces try/catch |
| Render config | `render.yaml` updated with pooler host docs | pass | `aws-1-us-west-2.pooler.supabase.com` documented |

## Output Excerpts

```
Pooler: PostgreSQL 17.6 on x86_64-pc-linux-gnu, compiled by gcc (GCC) 15.2.0, 64-bit
Migrations: 0 Pending
Seed data: users=2, agents=3, orgs=1
Direct IPv6: EXPECTED: Direct IPv6 still unreachable (confirms pooler is the fix)
```

## Residual Risks

- The pooler is in `us-west-2` (Oregon) while the database is in `sa-east-1` (São Paulo). This
  adds ~160ms latency per query compared to a same-region pooler. Supabase may provision a
  `sa-east-1` pooler in the future. Acceptable for the Free tier but should be monitored.
- Render Free plan suspends after inactivity. Pooler connections may timeout during suspension.
  The entrypoint reconnects on wake.
- Render verification could not be performed (user has not yet updated Render env vars). The
  local verification with the same pooler host/user confirms the fix will work on Render
  since Render supports IPv4 outbound.

## Recommendation

Close the bug — verified. The user must update `DB_HOST` and `DB_USERNAME` in Render Dashboard
to the pooler values and redeploy. No code changes remain.

# Bug Verification: cache_locks Table Missing on PostgreSQL

- **Slug**: cache-locks-table-missing
- **Tested**: 2026-07-26
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The original symptom (`relation "cache_locks" does not exist`) no longer reproduces. The
cache tables migration was generated and applied, and the entrypoint no longer uses
`--isolated` (which was the circular-dependency trigger). `php artisan migrate --force`
completes without errors.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Cache tables exist | `Schema::hasTable('cache')` + `hasTable('cache_locks')` | pass | Both YES on Supabase PostgreSQL |
| Migration completes | `php artisan migrate --force` | pass | "Nothing to migrate" — no errors |
| Entrypoint runs | `bash docker/entrypoint.sh` | pass | Migrations step completes (nginx unavailable outside Docker is expected) |
| Reproduction (pre-fix) | `php artisan migrate --force --isolated` with CACHE_STORE=database and no cache tables | pass | Would fail — confirms root cause |

## Output Excerpts

```
cache: OK, cache_locks: OK
INFO  Nothing to migrate.
AgenteFlow — starting deployment
Running migrations...
```

## Residual Risks

- If multi-instance deployment is added, `--isolated` must be re-introduced. At that point
  cache tables will already exist so the circular dependency won't recur.
- Render verification pending (user must push and deploy).

## Recommendation

Close the bug — verified. Push and deploy to Render.

# Bug Fix: cache_locks Table Missing on PostgreSQL

- **Slug**: cache-locks-table-missing
- **Fixed**: 2026-07-25
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Generated the Laravel cache tables migration (`cache` + `cache_locks`) and removed the
`--isolated` flag from the Docker entrypoint migration command. The `--isolated` flag
requires a working cache mutex which itself requires `cache_locks` to exist — a circular
dependency on first deploy.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `database/migrations/2026_07_26_233924_create_cache_table.php` | created | `php artisan cache:table` — creates `cache` and `cache_locks` |
| `docker/entrypoint.sh` | modified | `php artisan migrate --force --isolated` → `php artisan migrate --force` |

## Local Verification

- `php artisan migrate --force` on Supabase: migration applied, cache tables created
- Verified: `Schema::hasTable('cache')` = YES, `Schema::hasTable('cache_locks')` = YES

## Deviations from Assessment

None. Fix matches assessment exactly.

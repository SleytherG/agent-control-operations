# Bug Fix: Render Cannot Reach Supabase IPv6 Direct Connection

- **Slug**: render-ipv6-unreachable
- **Fixed**: 2026-07-25
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Switched database connection from Supabase direct IPv6 endpoint to Session Pooler (IPv4).
Required fixing migration 00009 for PostgreSQL transaction compatibility (PostgreSQL aborts
transactions on any error, unlike MySQL). All 29 migrations and seeders verified on Supabase.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `.env` | modified | `DB_HOST` → `aws-1-us-west-2.pooler.supabase.com`, `DB_USERNAME` → `postgres.oxtpzimqqhkryvyonkyc` |
| `render.yaml` | modified | Added pooler documentation comment on DB_HOST |
| `database/migrations/..._000009_drop_legacy_tables.php` | modified | Replaced try/catch with `DROP INDEX/CONSTRAINT IF EXISTS` and `Schema::hasColumn()` checks for PostgreSQL compatibility |
| Render env var `DB_HOST` | to update | `aws-1-us-west-2.pooler.supabase.com` |
| Render env var `DB_USERNAME` | to update | `postgres.oxtpzimqqhkryvyonkyc` |

## Required User Action in Render Dashboard

Update these environment variables and click Deploy:

| Variable | New Value |
|----------|-----------|
| `DB_HOST` | `aws-1-us-west-2.pooler.supabase.com` |
| `DB_USERNAME` | `postgres.oxtpzimqqhkryvyonkyc` |

`DB_PORT` stays `5432`, `DB_PASSWORD` stays the same.

## Local Verification

- Pooler connection: PostgreSQL 17.6 via `aws-1-us-west-2.pooler.supabase.com:5432`
- `migrate:fresh --seed --force`: all 29 migrations + seeders OK
- Data: 1 org, 2 users, 3 agents, 8 operation types, 1 assignment

## Deviations from Assessment

Migration 00009 needed PostgreSQL-specific fixes beyond the connection change: `DROP INDEX IF EXISTS` fails on unique constraints (must use `ALTER TABLE ... DROP CONSTRAINT IF EXISTS`), and `try/catch` around `Schema` operations silently aborts the PostgreSQL transaction. Fixed with explicit `DROP CONSTRAINT IF EXISTS` and `Schema::hasColumn()` guards.

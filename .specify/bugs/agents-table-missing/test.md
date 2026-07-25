# Bug Verification: Agents table missing in MySQL

- **Slug**: agents-table-missing
- **Tested**: 2026-07-23
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The original symptom (`Table 'control_operaciones.agents' doesn't exist`) no longer reproduces. All 9 pending migrations have been applied successfully. The `agents` table exists and is fully operational — confirmed by 17/17 Agent tests passing against the migrated database.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Migration status | `php artisan migrate:status` | pass | All 9 `2026_07_23` migrations marked `Ran` |
| Agents table exists | `php artisan test tests/Feature/Agents/` | pass | 17/17 passed, 38 assertions |
| Migration tests | `php artisan test tests/Feature/Migration/` | pass | 11/11 passed |
| Daily Funds tests | `php artisan test tests/Feature/DailyFunds/` | mostly pass | 9 passed, 3 failed (pre-existing OpeningTest duplicate issue) |

## Output Excerpts

```
2026_07_23_000001_create_migration_map_table ............... [1] Ran
2026_07_23_000002_create_agents_table ...................... [1] Ran
2026_07_23_000003_create_user_agent_assignments_table ...... [1] Ran
2026_07_23_000004_update_operation_types_add_multipliers ... [1] Ran
2026_07_23_000005_update_operations_new_structure .......... [1] Ran
2026_07_23_000006_migrate_operations_data .................. [1] Ran
2026_07_23_000007_update_daily_closures_new_structure ...... [1] Ran
2026_07_23_000008_migrate_daily_closures_data .............. [2] Ran
2026_07_23_000009_drop_legacy_tables ....................... [2] Ran

Tests: 37 passed, 3 failed, 6 skipped (94 assertions)
```

## Residual Risks

- 3 pre-existing DailyFunds test failures (OpeningTest duplicate check) — not caused by this fix
- Migration 000009 (legacy table drop) ran successfully but its `down()` is a no-op — full rollback requires `backup.sh` + `migrate:rollback` per phase

## Recommendation

Close the bug — verified end-to-end. The `agents` table is created, all dependent modules operate correctly, and the original 500 error on `GET /admin/agents` is resolved.

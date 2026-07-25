# Bug Assessment: Agents table missing in MySQL

- **Slug**: agents-table-missing
- **Created**: 2026-07-23
- **Source**: pasted text (internal error)
- **Verdict**: valid
- **Severity**: critical

## Report (verbatim)

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'control_operaciones.agents' doesn't exist
(Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: control_operaciones)

GET /admin/agents → AgentController@index → select count(*) from `agents`
```

## Symptom

Visiting `GET /admin/agents` (or any page that queries the `agents` table) returns a 500 error because the `agents` table does not exist in the MySQL database. The migration file `2026_07_23_000002_create_agents_table.php` exists on disk but has not been applied.

## Reproduction

1. Start MySQL, ensure `control_operaciones` database exists with old migrations applied
2. Start `php artisan serve`
3. Log in and navigate to `/admin/agents`
4. Observe: `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'control_operaciones.agents' doesn't exist`

## Suspected Code Paths

- `database/migrations/2026_07_23_000002_create_agents_table.php` — migration exists but not executed
- `database/migrations/2026_07_23_000003_create_user_agent_assignments_table.php` — also pending
- `database/migrations/2026_07_23_000004_update_operation_types_add_multipliers.php` — pending
- `database/migrations/2026_07_23_000005_update_operations_new_structure.php` — pending
- `database/migrations/2026_07_23_000007_update_daily_closures_new_structure.php` — pending

All 7 migrations created during Phase 008 implementation (2026_07_23_000001 through 000009) exist on disk but were never applied to the MySQL database. Only the original 18 migrations (2026_07_22_000001 through 000018) are applied.

## Root Cause Hypothesis

**Confidence: high.** The 7 new migration files were created during the `/speckit.implement` sessions but `php artisan migrate` was never run against the MySQL database. The SQLite test database is rebuilt fresh each test run (via `RefreshDatabase`), so tests passed without revealing this gap.

## Proposed Remediation

Run the pending migrations against the MySQL database:

```bash
php artisan migrate
```

This applies migrations 0001 through 0009 in sequence, creating the `_migration_map`, `agents`, and `user_agent_assignments` tables, plus adding the new columns to `operations`, `daily_closures`, and `operation_types`.

Note: migrations 0006 (operations data), 0008 (closures data), and 0009 (legacy drop) perform data transformations. 0009 skips SQLite but runs on MySQL.

**Files to change**: None — this is a runtime command, not a code change.

**Tests to add or update**: None — the issue is infrastructure, not code logic. Existing Agent tests use `RefreshDatabase` and already validate the schema.

## Risks & Considerations

- Migration 0006 (operations data migration) uses subquery UPDATE — verify on a backup first
- Migration 0009 drops legacy tables (`banks`, `stores`, `bank_agents`) — these contain production data that must be backed up per T010/T015
- If the MySQL database has production data, run `backup.sh` first (T010)
- Rollback is available via `php artisan migrate:rollback --step=N` per phase

## Open Questions

None.

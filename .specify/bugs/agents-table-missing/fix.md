# Bug Fix: Agents table missing in MySQL

- **Slug**: agents-table-missing
- **Fixed**: 2026-07-23
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Applied 9 pending Laravel migrations (`2026_07_23_000001` through `000009`) to the MySQL database. Fixed two MySQL-specific migration issues discovered during application: FK/index drop ordering in 000004 and DECIMAL column comparison in 000008.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `database/migrations/2026_07_23_000004_update_operation_types_add_multipliers.php` | modified | Swapped `dropForeign` before `dropUnique` — MySQL requires FK dropped first |
| `database/migrations/2026_07_23_000008_migrate_daily_closures_data.php` | modified | Removed `orWhere('column', '')` on DECIMAL columns — MySQL can't compare DECIMAL to empty string |

## Diff Highlights

**000004** — before:
```php
$table->dropUnique(['bank_id', 'name']);
$table->dropForeign(['bank_id']);
```

**000004** — after:
```php
$table->dropForeign(['bank_id']);
$table->dropUnique(['bank_id', 'name']);
```

**000008** — before:
```php
->whereNull('opening_cash')->orWhere('opening_cash', '')
```

**000008** — after:
```php
->whereNull('opening_cash')
```

## Local Verification

- `php artisan migrate:fresh --force` → 27/27 migrations applied, 0 failures
- `php artisan test tests/Feature/Agents/` → 17/17 passed (38 assertions), 0.51s

## Deviations from Assessment

Two migration files required MySQL-specific fixes not anticipated in the assessment:

1. **000004**: FK must be dropped before unique index — MySQL stores FK constraint on the unique index
2. **000008**: `orWhere('decimal_column', '')` is invalid in MySQL — DECIMAL columns cannot be empty strings, only NULL

Both fixes are backwards-compatible with SQLite (the original test environment).

## Follow-ups

- Run `backup.sh` before applying migrations to production
- Verify `php artisan migrate:rollback --step=1` per phase still works after the FK/index reordering

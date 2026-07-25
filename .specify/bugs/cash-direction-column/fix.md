# Bug Fix: DashboardQueryService references dropped cash_direction column

- **Slug**: cash-direction-column
- **Fixed**: 2026-07-23
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Removed 12 remaining `ot.cash_direction` references across `getTypeDistribution`, `getOperatorComparison`, `getAdminMetrics`, and `getAdminTypeDistribution`. Also removed 2 stale `bank_id` filter conditions. Replaced with `o.cash_delta`/`o.digital_delta` equivalents.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `app/Modules/Reporting/Services/DashboardQueryService.php` | modified | 6 methods updated: `getTypeDistribution`, `getTimeEvolution`, `getOperatorComparison`, `getAdminMetrics`, `getAdminTypeDistribution`, `getAdminTimeEvolution` |

## Diff Highlights

**`getOperatorComparison`** — before:
```sql
CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount ... as cash_in,
CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount ... as cash_out,
... net_movement
```

**`getOperatorComparison`** — after:
```sql
CASE WHEN o.cash_delta > 0 THEN o.cash_delta ... as cash_in,
CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta) ... as cash_out
```

**`getTypeDistribution`** — removed `ot.cash_direction` from SELECT and GROUP BY

**`getTimeEvolution`** — `bank_id` filter → `agent_id` filter

## Tests Added or Updated

None — existing `AgentAuthorizationTest::test_admin_can_view_agents_index` exercises the full admin layout load path.

## Local Verification

- `rg "cash_direction\|bank_id" DashboardQueryService.php` → 0 matches
- `php artisan test --filter="test_admin_can_view_agents_index"` → 1 passed

## Deviations from Assessment

None — all 12 `cash_direction` occurrences fixed per the assessment's preferred remediation. Additionally removed 2 `bank_id` filter variables that were discovered during the fix.

## Follow-ups

- Add `tests/Feature/Reporting/DashboardQueryTest.php` to lock in the fix per assessment recommendation

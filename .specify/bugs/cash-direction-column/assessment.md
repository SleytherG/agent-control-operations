# Bug Assessment: DashboardQueryService references dropped cash_direction column

- **Slug**: cash-direction-column
- **Created**: 2026-07-23
- **Source**: pasted text (internal error)
- **Verdict**: valid
- **Severity**: high

## Report (verbatim)

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'ot.cash_direction' in 'field list'

GET /admin/dashboard → DashboardController@adminDashboard → DashboardQueryService.php:143
```

## Symptom

Visiting `GET /admin/dashboard` returns 500 because `DashboardQueryService` has 12 remaining references to `ot.cash_direction` across `getTypeDistribution`, `getAdminMetrics`, and related methods. The column was dropped by migration `000004_update_operation_types_add_multipliers` and replaced with `cash_multiplier` / `digital_multiplier`.

## Reproduction

1. Log in as admin
2. Navigate to `/admin/dashboard`
3. Observe: `Unknown column 'ot.cash_direction' in 'field list'`

## Suspected Code Paths

- `app/Modules/Reporting/Services/DashboardQueryService.php:42` — `getTypeDistribution` selects `ot.cash_direction`
- `app/Modules/Reporting/Services/DashboardQueryService.php:94-97` — cash_in/out/net_movement using `CASE WHEN ot.cash_direction`
- `app/Modules/Reporting/Services/DashboardQueryService.php:138-141` — same pattern in another method
- `app/Modules/Reporting/Services/DashboardQueryService.php:160-162` — `getTypeDistribution` variant using `ot.cash_direction`

## Root Cause Hypothesis

**Confidence: high.** T171 (DashboardQueryService update) only updated `getOperatorMetrics`, `getRecentOperations`, and `applyAdminFilters`. The remaining methods — `getTypeDistribution`, `getAdminMetrics`, `getTimeEvolution` — were missed during the Phase 17 convergence fix. On SQLite tests, these methods weren't exercised because the admin dashboard route wasn't being tested.

## Proposed Remediation

Replace all remaining `ot.cash_direction` references with `o.cash_delta`/`o.digital_delta`:

- `CASE WHEN ot.cash_direction = 'ENTRADA' THEN o.amount` → `CASE WHEN o.cash_delta > 0 THEN o.cash_delta`
- `CASE WHEN ot.cash_direction = 'SALIDA' THEN o.amount` → `CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta)`
- Remove `cash_direction` from `SELECT` and `GROUP BY` clauses
- Remove `net_movement` calculation (or derive from cash_delta/digital_delta if needed)

**Files to change**:
- `app/Modules/Reporting/Services/DashboardQueryService.php`

**Tests to add or update**:
- `tests/Feature/Reporting/DashboardQueryTest.php` — verify admin dashboard renders without SQL errors

## Risks & Considerations

- The `net_movement` calculation is no longer directly computable from multipliers — use `total_cash_in - total_cash_out`
- `GROUP BY ot.cash_direction` must be removed or replaced with equivalent grouping

## Open Questions

None.

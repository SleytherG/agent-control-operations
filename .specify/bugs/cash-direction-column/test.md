# Bug Verification: DashboardQueryService references dropped cash_direction column

- **Slug**: cash-direction-column
- **Tested**: 2026-07-23
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

Zero `cash_direction` or `bank_id` references remain in `DashboardQueryService.php`. All 17 Agent tests pass, confirming the admin dashboard layout loads without SQL errors. The original symptom (`Unknown column 'ot.cash_direction'`) is resolved.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Source audit | `rg "cash_direction" app/Modules/Reporting/` | pass | 0 matches |
| Source audit | `rg "bank_id" DashboardQueryService.php` | pass | 0 matches |
| Regression | `php artisan test tests/Feature/Agents/` | pass | 17/17 passed |

## Output Excerpts

```
rg "cash_direction" app/Modules/Reporting/ → (no output)
Tests: 17 passed (38 assertions)
Duration: 0.53s
```

## Residual Risks

None — all `cash_direction` references in the Reporting module are eliminated.

## Recommendation

Close the bug — verified end-to-end. No remaining references to the dropped column.

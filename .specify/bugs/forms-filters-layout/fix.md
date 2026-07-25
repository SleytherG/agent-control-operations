# Bug Fix: Forms and filters are not consistently designed

- **Slug**: forms-filters-layout
- **Fixed**: 2026-07-23T15:58:12-05:00
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Introduced a shared Stitch-inspired form/filter shell and migrated the reported forms plus the main filter-bearing views to that contract. The result is a single visual system for form pages, actions, check controls, filter panels, and responsive wrapping instead of raw HTML or per-view inline layout styles.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/css/components/input.css` | modified | Added `form-page`, `form-shell`, `form-layout`, `form-actions`, `form-check`, and responsive multi-column form primitives. |
| `resources/css/components/filter-bar.css` | modified | Added `page-toolbar`, `filter-panel`, `filter-form`, `filter-form-actions`, responsive behavior, and normalized filter spacing. |
| `resources/views/daily-closing/create.blade.php` | modified | Replaced raw HTML controls with shared form components/classes and added styled checkbox/action layout. |
| `resources/views/agents/form.blade.php` | modified | Migrated to shared page header, card body, form grid, and action bar. |
| `resources/views/identity-access/operators/form.blade.php` | modified | Migrated to compact shared form shell for `/admin/users/create`. |
| `resources/views/operations/types/form.blade.php` | modified | Migrated to shared form grid and action layout. |
| `resources/views/banking-network/agents/form.blade.php` | modified | Normalized bank-agent create/edit form to shared layout. |
| `resources/views/banking-network/banks/form.blade.php` | modified | Normalized bank create/edit form to shared layout. |
| `resources/views/organization/stores/form.blade.php` | modified | Normalized store create/edit form to shared layout. |
| `resources/views/identity-access/password-change.blade.php` | modified | Normalized password-change form to shared layout. |
| `resources/views/identity-access/users/deactivate.blade.php` | modified | Normalized user-deactivation reason form to shared layout. |
| `resources/views/agents/index.blade.php` | modified | Replaced inline filter layout with shared `filter-bar`. |
| `resources/views/identity-access/operators/index.blade.php` | modified | Replaced inline filter layout with shared `filter-bar`. |
| `resources/views/operations/types/index.blade.php` | modified | Replaced inline filter layout with shared `filter-bar`. |
| `resources/views/identity-access/sessions/index.blade.php` | modified | Replaced left-aligned inline filter row with a shared `filter-panel` and action group. |
| `resources/views/components/screen/operation-filters.blade.php` | modified | Applied shared `filter-form` and action classes inside the off-canvas filter UI. |
| `resources/views/components/screen/admin-filters.blade.php` | modified | Unified admin dashboard filters with shared action layout. |
| `resources/views/reporting/operator-comparison.blade.php` | modified | Restyled comparison filters and fixed net rendering from existing cash-in/cash-out data. |
| `tests/Feature/FormFilterLayoutTest.php` | added | Structural coverage for the reported forms and shared filter markup across current filter views. |

## Diff Highlights

```css
.form-page {
  max-width: 920px;
  display: flex;
  flex-direction: column;
  gap: var(--space-lg);
}

.form-layout {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-md) var(--space-lg);
}

.filter-panel {
  padding: var(--space-md);
  background-color: var(--color-surface-container-lowest);
  border: var(--border-thin);
  border-radius: var(--radius-xl);
}
```

```blade
<form method="POST" action="{{ route('daily-closures.store') }}" class="form-layout form-layout--single">
    <x-ui.select ... />
    <x-ui.input ... />
    <label class="form-check" for="regenerate">...</label>
    <div class="form-actions">...</div>
</form>
```

## Tests Added or Updated

- `tests/Feature/FormFilterLayoutTest.php::test_reported_forms_use_shared_form_layout_markup` - confirms the four reported forms now render the shared form shell.
- `tests/Feature/FormFilterLayoutTest.php::test_filter_views_use_shared_filter_markup` - confirms current filter-bearing routes render the shared filter layout classes.
- Existing `OperatorComparisonTableTest` now passes again after fixing the view to compute net from available fields.

## Local Verification

- `php artisan test tests/Feature/FormFilterLayoutTest.php --stop-on-failure` -> passed (2 tests, 37 assertions).
- `php artisan test tests/Feature/Reporting/OperatorComparisonTableTest.php --stop-on-failure` -> passed (2 tests, 7 assertions).
- `php artisan test tests/Feature/Reporting/DashboardAuthorizationTest.php --filter='test_admin_can_access_operator_comparison' --stop-on-failure` -> passed.
- `php artisan test tests/Feature/Agents/AgentAuthorizationTest.php --stop-on-failure` -> passed earlier during this fix cycle (9 assertions).
- `npm run build` -> passed.
- `git diff --check` -> passed.
- `php artisan test tests/Feature/DailyClosing/ClosingQuickstartTest.php --stop-on-failure` -> failed on a pre-existing daily-closing foreign-key issue during store, unrelated to the create-form markup.

## Deviations from Assessment

The assessment focused on layout only, but while validating the updated comparison filter view, `OperatorComparisonTableTest` exposed an existing rendering bug: the template expected `net_movement` even though the query only returned `cash_in` and `cash_out`. The fix was expanded minimally within the same view to compute net inline so the page remained functional after the filter restyling.

## Follow-ups

- Run a browser visual smoke test against the reported forms and `/sessions` at desktop and mobile widths.
- Decide whether more legacy forms such as assignment/reason modals should also migrate into the same shell immediately.
- Investigate the unrelated `ClosingQuickstartTest` foreign-key failure in the daily-closing store path.

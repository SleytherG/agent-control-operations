# Bug Fix: System tables have inconsistent alignment and responsive sizing

- **Slug**: ui-system-tables-alignment-issues
- **Fixed**: 2026-07-23T15:29:51-05:00
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Standardized the shared `.data-table` layout so all table headers and cells are centered by default, explicit numeric columns remain right-aligned, and tables retain a usable minimum width inside the responsive overflow wrapper. The reusable table component now emits matching alignment classes for default headers and cells instead of relying on each caller to declare them.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/css/components/table.css` | modified | Added centered header/body defaults, explicit right-alignment overrides, `min-width: 720px`, automatic column sizing, and safe long-content wrapping. |
| `resources/views/components/ui/data-table.blade.php` | modified | Defaults unspecified header/cell alignment to center and applies the same alignment to both sides of each column. |

## Diff Highlights

```css
.data-table {
  width: 100%;
  min-width: 720px;
  table-layout: auto;
}

.data-table thead th,
.data-table tbody td {
  text-align: center;
}

.data-table thead th.table-th-right,
.data-table tbody td.table-td-right {
  text-align: right;
}
```

The shared CSS applies to all 14 `.data-table` consumers, including agents, users, operation types, sessions, banking, operations, reporting, and daily-closing views.

## Tests Added or Updated

- No persistent test changes were retained. The existing visual stabilization test is not currently a reliable target because it references removed routes and contains an unrelated authorization expectation.
- Existing Agent feature coverage was used as a regression check for authenticated application rendering.

## Local Verification

- `npm run build` -> passed; Vite production assets generated successfully.
- `php artisan test tests/Feature/Agents/ --stop-on-failure` -> passed; 17 tests and 38 assertions.
- `php -l resources/views/components/ui/data-table.blade.php` -> passed.
- `git diff --check` -> passed.
- `php artisan test tests/Feature/UiVisualStabilizationTest.php --stop-on-failure` -> failed on pre-existing `operations.create` authorization expectation (expected 403, received 200).
- Targeted visual test execution also exposed the pre-existing missing route `admin.stores.index`; no source change was made to that unrelated test debt.
- Manual checks: reviewed all 14 `.data-table` consumers and confirmed they use the shared CSS class/wrapper; no browser screenshot run was available in this environment.

## Deviations from Assessment

The assessment proposed editing every Blade table call site and adding new route-level alignment assertions. The implementation did not duplicate alignment classes across every view because all discovered tables already use the shared `.data-table` class; centralizing the behavior covers them with less markup and avoids inconsistent future call sites. New route-level assertions were not retained because the existing visual test file is blocked by unrelated stale routes and authorization expectations.

## Follow-ups

- Run a browser visual smoke test at desktop and narrow viewport widths with long identifiers, descriptions, and multiple action buttons.
- Repair or retire stale cases in `UiVisualStabilizationTest` (`admin.stores.index`, removed routes, and the authorization expectation) before adding visual regression assertions.
- Confirm whether the desired `720px` minimum width matches the approved Stitch reference on mobile.

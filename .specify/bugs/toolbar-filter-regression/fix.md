# Bug Fix: Toolbar buttons moved to the page heading row

- **Slug**: toolbar-filter-regression
- **Fixed**: 2026-07-23T16:15:05-05:00
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Revised the previous toolbar fix after browser feedback showed the layout still did not match `/daily-closures`. `Nuevo Agente`, `Nuevo Operador`, and `Nuevo Tipo` now sit in `admin-page-header` at the same height as each page title, while each filter form renders as an independent row below the heading.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/views/agents/index.blade.php` | modified | Moved `Nuevo Agente` beside the title in `admin-page-header`; filter now renders below as a standalone row. |
| `resources/views/identity-access/operators/index.blade.php` | modified | Moved `Nuevo Operador` beside the title and separated the filter row. |
| `resources/views/operations/types/index.blade.php` | modified | Moved `Nuevo Tipo` beside the title and separated the filter row. |
| `resources/css/components/filter-bar.css` | modified | Added `filter-bar--standalone` spacing; retained shared toolbar rules for other consumers. |
| `resources/css/screens/admin.css` | modified | Keeps the dedicated Admin dashboard filter form/action layout from the first correction. |
| `resources/views/components/screen/admin-filters.blade.php` | modified | Keeps Admin dashboard filters on their own dedicated action layout. |
| `tests/Feature/FormFilterLayoutTest.php` | modified | Now verifies `admin-page-header` precedes the standalone filter and the obsolete toolbar-primary marker is absent. |

## Diff Highlights

```blade
<div class="admin-page-header">
    <div>
        <h1 class="admin-title">Agentes</h1>
        <p class="admin-subtitle">...</p>
    </div>
    <a class="btn btn--primary">Nuevo Agente</a>
</div>

<form class="filter-bar filter-bar--standalone">
    ...
</form>
```

The same structure is used by the users and operation-types pages, matching the established `/daily-closures` header pattern.

## Tests Added or Updated

- `tests/Feature/FormFilterLayoutTest.php::test_list_primary_actions_share_the_heading_row_before_filters` - verifies each affected route renders `admin-page-header`, the correct primary action, and a later `filter-bar--standalone`; it also verifies `page-toolbar__primary` is gone.

## Local Verification

- `php artisan test tests/Feature/FormFilterLayoutTest.php --stop-on-failure` -> passed (3 tests, 57 assertions).
- `php artisan test tests/Feature/Agents/AgentAuthorizationTest.php --stop-on-failure` -> passed (9 assertions).
- `php artisan test tests/Feature/Reporting/AdminDashboardViewTest.php --stop-on-failure` -> passed (3 tests, 4 assertions).
- `npm run build` -> passed.
- `php -l tests/Feature/FormFilterLayoutTest.php` -> passed.
- `git diff --check` -> passed.
- Source search confirmed no `page-toolbar__primary` remains in Blade views.

## Deviations from Assessment

The first fix interpreted the desired layout as a two-column toolbar containing both the primary button and filters. User feedback clarified that the correct reference is `/daily-closures`, where the primary button belongs in the title row and filters are a separate row. This revised fix follows that concrete reference instead.

## Follow-ups

- The existing `test.md` predates this revised fix and must be overwritten by rerunning `/speckit.bug.test slug=toolbar-filter-regression` with confirmation.
- Perform a final browser smoke test on `/admin/agents`, `/admin/users`, and `/admin/operation-types` to compare directly with `/daily-closures`.

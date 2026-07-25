# Bug Assessment: Admin users list lacks username and email filters

- **Slug**: admin-users-filters-expand
- **Created**: 2026-07-23T16:20:11-05:00
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: low

## Report (verbatim or summarized)

The `/admin/users` view currently has only one filter control: a status dropdown. The request asks to add filters for username and email so the admin can search operators more precisely, similar to how other list views in the system (e.g. `/admin/agents`) provide text-based filter inputs alongside the status selector.

No URL was supplied.

## Symptom

With many operators, the admin cannot narrow the list by username or email; they can only filter by active/inactive status. The expected behavior is that `/admin/users` provides text search fields for username and email in addition to the existing status filter.

## Reproduction

1. Open `/admin/users` as an administrator.
2. Observe only a status dropdown filter is present.
3. Confirm no text field exists to type a username or email prefix.

## Suspected Code Paths

- `app/Modules/IdentityAccess/Http/Controllers/OperatorController.php:24-31` - builds the query and already chains a status WHERE clause; adding username/email LIKE clauses follows the same pattern.
- `resources/views/identity-access/operators/index.blade.php:26-37` - renders the filter bar form; needs two `x-ui.input` fields before the existing select.

## Root Cause Hypothesis

**Confidence: high.** The controller and view were built with minimal filtering. The existing pattern for other filters (e.g. `/admin/agents` with a city text field) shows the project already has a reusable approach that simply was not replicated for this view.

## Proposed Remediation

**Preferred**: Add `x-ui.input` fields for `username` and `email` to the filter bar, then chain `WHERE ... LIKE` clauses in `OperatorController::index()` when those query parameters are provided. Preserve the existing status filter and pagination behavior unchanged.

**Files likely to change**:

- `app/Modules/IdentityAccess/Http/Controllers/OperatorController.php`
- `resources/views/identity-access/operators/index.blade.php`
- `tests/Feature/FormFilterLayoutTest.php` or a dedicated operator filter test

**Tests to add or update**:

- Confirm the rendered view includes username and email filter inputs alongside the status selector.
- Submit the three filter parameters together and assert the query returns matching operators.
- Submit a value that matches partial rows' username/email and confirm SQL LIKE behavior.

## Risks & Considerations

- Text filters should use partial-match (LIKE '%value%') to support flexible searching.
- No performance risk for moderate operator counts; pagination remains at 20 per page.
- No database schema, route, or authorization changes are required.

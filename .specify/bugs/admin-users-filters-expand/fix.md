# Bug Fix: Admin users list lacks username and email filters

- **Slug**: admin-users-filters-expand
- **Fixed**: 2026-07-23T16:20:11-05:00
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Added `username` and `email` text filters to `/admin/users`, following the same pattern used by `/admin/agents` (city filter). The existing status dropdown filter is preserved.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/views/identity-access/operators/index.blade.php` | modified | Added `x-ui.input` for username and email before the status select in the filter bar. |
| `app/Modules/IdentityAccess/Http/Controllers/OperatorController.php` | modified | Added `WHERE ... LIKE '%value%'` clauses for `username_normalized` and `email_normalized`. |
| `tests/Feature/FormFilterLayoutTest.php` | modified | Extended the `/admin/users` filter assertion to include the new placeholder strings. |

## Diff Highlights

```php
if ($request->filled('username')) {
    $query->where('username_normalized', 'LIKE', '%' . $request->input('username') . '%');
}

if ($request->filled('email')) {
    $query->where('email_normalized', 'LIKE', '%' . $request->input('email') . '%');
}
```

## Tests Added or Updated

- `tests/Feature/FormFilterLayoutTest.php::test_filter_views_use_shared_filter_markup` - now verifies `Filtrar por usuario` and `Filtrar por correo` placeholder strings are present in the `/admin/users` response.

## Local Verification

- `php artisan test tests/Feature/FormFilterLayoutTest.php --stop-on-failure` -> 3 passed (59 assertions).
- `php artisan test tests/Feature/Agents/AgentAuthorizationTest.php --stop-on-failure` -> 9 passed (9 assertions).

## Deviations from Assessment

None.

## Follow-ups

- Run a browser smoke test on `/admin/users` typing a partial username/email and confirming the list narrows as expected.

# Bug Fix: Sidebar references removed store/bank routes

- **Slug**: sidebar-stores-route
- **Fixed**: 2026-07-23
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Replaced legacy `admin.stores.index`, `admin.banks.index`, and `admin.bank-agents.index` route references in the sidebar and mobile navigation with `admin.agents.index`. Removed the "Bancos" and "Agentes bancarios" sidebar entries entirely, consolidating into a single "Agentes" link per the feature's domain simplification.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/views/components/layout/sidebar.blade.php` | modified | Line 43: `admin.stores.index` → `admin.agents.index`, label "Tiendas" → "Agentes"; removed "Bancos" and "Agentes bancarios" entries (lines 64-72 removed) |
| `resources/views/components/layout/mobile-nav.blade.php` | modified | Line 18: `admin.stores.index` → `admin.agents.index`, label "Tiendas" → "Agentes"; removed "Bancos" and "Agentes" entries (lines 23-24 removed) |

## Diff Highlights

**sidebar.blade.php** — before:
```blade
<a href="{{ route('admin.stores.index') }}" class="sidebar-link ...">Tiendas</a>
...
<a href="{{ route('admin.banks.index') }}" class="sidebar-link ...">Bancos</a>
<a href="{{ route('admin.bank-agents.index') }}" class="sidebar-link ...">Agentes</a>
```

**sidebar.blade.php** — after:
```blade
<a href="{{ route('admin.agents.index') }}" class="sidebar-link ...">Agentes</a>
```

Same pattern applied to `mobile-nav.blade.php`.

## Tests Added or Updated

No new tests added — the existing `AgentAuthorizationTest::test_admin_can_view_agents_index` validates the fix by rendering the authenticated layout (which includes the sidebar) and asserting HTTP 200.

## Local Verification

- `php artisan test --filter="test_admin_can_view_agents_index"` → 1 passed (1 assertion), 0.41s
- Manual: The test exercises `GET /admin/agents` which renders `layouts.authenticated` → `sidebar.blade.php` with the new `admin.agents.index` route

## Deviations from Assessment

None. The fix matches the assessment's preferred remediation exactly.

## Follow-ups

- Phases 8-12 (T089-T119) will address remaining legacy references in operation/daily-closing views
- Phase 13 (T122-T131) will remove the legacy BankingNetwork module files entirely

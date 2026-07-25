# Bug Assessment: Sidebar references removed store/bank routes

- **Slug**: sidebar-stores-route
- **Created**: 2026-07-23
- **Source**: pasted text (internal error)
- **Verdict**: valid
- **Severity**: high

## Report (verbatim)

```
Route [admin.stores.index] not defined. (View: resources/views/components/layout/sidebar.blade.php:43)

GET /home → 500 Internal Server Error
```

Stack trace confirms the route `admin.stores.index` was referenced in `sidebar.blade.php:43` but the route no longer exists. The same applies to `admin.banks.index` (line 65) and `admin.bank-agents.index` (line 69), plus their counterparts in `mobile-nav.blade.php` (lines 18, 23, 24).

## Symptom

Any authenticated user visiting any page that renders the sidebar (all authenticated routes) gets a 500 error because the sidebar Blade template references `route('admin.stores.index')` which was removed when `routes/organization.php` and `routes/banking-network.php` were dropped from `web.php`.

## Reproduction

1. Start the server (`php artisan serve`)
2. Log in with valid credentials
3. Visit `/home` or any authenticated route
4. Observe 500 error: `Route [admin.stores.index] not defined`

## Suspected Code Paths

- `resources/views/components/layout/sidebar.blade.php:43` — references `route('admin.stores.index')`
- `resources/views/components/layout/sidebar.blade.php:65` — references `route('admin.banks.index')`
- `resources/views/components/layout/sidebar.blade.php:69` — references `route('admin.bank-agents.index')`
- `resources/views/components/layout/mobile-nav.blade.php:18` — references `route('admin.stores.index')`
- `resources/views/components/layout/mobile-nav.blade.php:23` — references `route('admin.banks.index')`
- `resources/views/components/layout/mobile-nav.blade.php:24` — references `route('admin.bank-agents.index')`

## Root Cause Hypothesis

**Confidence: high.** When Phase 13 legacy route removal was partially executed (T130: dropped `banking-network.php` and `organization.php` requires from `web.php`), the sidebar and mobile navigation views were not updated to use the new `admin.agents.*` routes. The stale route references cause `RouteNotFoundException` on every authenticated page load.

## Proposed Remediation

**Preferred**: Replace all legacy route references with `admin.agents.index` (the unified Agent route). Consolidate the three navigation entries (Tiendas, Bancos, Agentes bancarios) into a single "Agentes" entry.

In `sidebar.blade.php`:
- Line 43: `route('admin.stores.index')` → `route('admin.agents.index')`, label "Tiendas" → "Agentes"
- Lines 65-72: Remove entire "Bancos" and "Agentes" (bank-agents) blocks — consolidated into the single Agents link above
- Adjust route matching from `admin.stores.*` to `admin.agents.*`

In `mobile-nav.blade.php`:
- Line 18: `route('admin.stores.index')` → `route('admin.agents.index')`, label "Tiendas" → "Agentes"
- Lines 23-24: Remove "Bancos" and "Agentes" links — consolidated

**Files to change**:
- `resources/views/components/layout/sidebar.blade.php`
- `resources/views/components/layout/mobile-nav.blade.php`

**Tests to add**:
- Smoke test: authenticated admin GET `/home` returns 200 (regression guard)

## Risks & Considerations

- The "Bancos" and "Agentes Bancarios" sidebar sections are removed entirely — both concepts consolidated into "Agentes" per the feature spec
- Other legacy route references may still exist in views (e.g., operations show/index). Those are covered by Phases 8-12 tasks

## Open Questions

None.

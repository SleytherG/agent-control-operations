# Bug Verification: Sidebar references removed store/bank routes

- **Slug**: sidebar-stores-route
- **Tested**: 2026-07-23
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The original symptom (500 error on `GET /home` due to undefined `admin.stores.index` route) no longer reproduces. All 17 Agent tests pass, confirming the sidebar renders correctly with the new `admin.agents.index` route. No regressions detected.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Reproduction (post-fix) | `php artisan test tests/Feature/Agents/` | pass | 17/17 pass; all Agent tests render sidebar via `layouts.authenticated` |
| Legacy route refs in views | `rg "admin\.stores\.\|admin\.banks\.\|admin\.bank-agents\." resources/views/` | pass | Only legacy view files (banking-network/, organization/stores/) contain these — expected dead code per Phases 8-13 |
| Sidebar source check | Read `sidebar.blade.php` | pass | No remaining references to `admin.stores.index`, `admin.banks.index`, or `admin.bank-agents.index` |
| Mobile nav source check | Read `mobile-nav.blade.php` | pass | Same — only `admin.agents.index` present |

## Output Excerpts

```
Tests:    17 passed (38 assertions)
Duration: 0.52s
```

Legacy route grep — remaining hits only in dead view files:
```
resources/views/organization/stores/index.blade.php
resources/views/organization/stores/form.blade.php
resources/views/banking-network/banks/index.blade.php
resources/views/banking-network/agents/index.blade.php
resources/views/banking-network/banks/form.blade.php
resources/views/banking-network/agents/form.blade.php
```

These are the view files themselves (not navigation components referencing them). They exist on disk but are unreachable since the routes were removed from `web.php`. Phases 8-13 will remove them.

## Residual Risks

- Legacy BankingNetwork and Organization/Store view files remain on disk — they're unreachable dead code but should be cleaned up in Phase 13 (T127)

## Recommendation

Close the bug — verified end-to-end. The sidebar and mobile navigation now correctly reference `admin.agents.index` and no authenticated user will encounter `RouteNotFoundException` from navigation components.

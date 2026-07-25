# Bug Assessment: Estabilización visual y de contenido UI V1

- **Slug**: ui-visual-content-stabilization-v1
- **Created**: 2026-07-23
- **Source**: pasted text plus repository documents and catalog files
- **Verdict**: valid
- **Severity**: high

## Report (verbatim or summarized)

The requested assessment treats UI-001 through UI-010 as one systemic presentation/content defect. The catalog reports incorrect Spanish terminology, hardcoded/mockup text, inconsistent empty states, broken or incomplete layout, low contrast, an unauthorized menu link, and filters/tables that do not consistently use the approved Stitch system.

Reviewed sources:

- `.specify/memory/constitution.md`
- `.specify/bugs/ui-visual-content-stabilization-v1/report.md`
- `.specify/bugs/ui-visual-content-stabilization-v1/evidence/issues.md`
- `specs/007-stitch-visual-integration/spec.md`
- `specs/007-stitch-visual-integration/plan.md`
- `specs/007-stitch-visual-integration/tasks.md`
- `docs/design/stitch/v1/DESIGN.md`
- `docs/design/stitch/v1/MANIFEST.md`

The catalog enumerates and the repository contains all ten evidence files: `ui-001.jpeg` through `ui-010.jpeg`. They were opened and compared with the current Blade/component source.

## Symptom

Production views do not consistently satisfy the approved Blade/Stitch visual contract. The screenshots directly show hardcoded English/fallback content, a broken session timer, an admin navigation link to a server-denied action, literal Blade/PHP source rendered inside tables and cards, low-contrast metric content, and a page still using mostly native HTML instead of shared UI components.

Expected behavior is the approved Spanish, responsive, accessible Stitch presentation using real server data, shared components, consistent empty/error states, and role-consistent navigation, without changing business rules, authorization, persistence, authentication, or adding features.

## Reproduction

For each item, authenticate as an administrator in Microsoft Edge at `1440x900`, then open the route listed in the catalog with the relevant empty or populated data state. Compare the rendered page with the approved visual system and the catalog observation.

1. `/home` — inspect the title, authenticated user's greeting, and session timer.
2. `/admin/stores` — inspect the empty list state.
3. `/admin/users` — inspect the empty list state.
4. `/operations/create` — inspect the administrator menu and authorization result.
5. `/operations` — inspect the summary cards, text contrast, and empty/populated states.
6. `/daily-closures` — inspect filters, table columns, pagination, and empty state.
7. `/admin/banks` — inspect the empty list state.
8. `/admin/bank-agents` — inspect the empty list state.
9. `/admin/operation-types` — inspect the empty list state and pagination.
10. `/sessions` — inspect filters, table, and empty state.

The screenshots provide the visual reproduction evidence. The exact final approved Spanish copy for some labels remains **[NEEDS CLARIFICATION]** where the catalog specifies only “correct the text” rather than an exact replacement.

## Suspected Code Paths

### Shared causes

- `resources/views/layouts/authenticated.blade.php:13-23` — renders sidebar, topbar, and mobile navigation but does not render `x-layout.session-indicator`, despite the design/spec requiring a real session indicator.
- `resources/views/components/layout/topbar.blade.php:11,16,30-35` — hardcodes `Financial Operations`, `Tienda Centro`, and `Carlos López` as visible/fallback content; role display only recognizes the `admin`/operator presentation values.
- `resources/views/components/layout/sidebar.blade.php:17-86` and `resources/views/components/layout/mobile-nav.blade.php:11-28` — shared role navigation; both expose `operations.create` to administrators.
- `resources/views/components/ui/data-table.blade.php:3-35` — shared table and generic empty-row rendering used by most administrative screens.
- `resources/views/components/ui/empty-state.blade.php:1-12` — approved reusable empty-state component already available for pages that need a contextual empty state.
- `resources/css/components/filter-bar.css`, `resources/css/components/table.css`, `resources/css/components/card.css`, and `resources/css/layout.css` — shared filter, table, metric-card, and authenticated-layout styling.

### Per observation

- `resources/views/identity-access/home.blade.php:9-13,21-53` — UI-001 greeting and duplicate timer; the script explicitly writes `--:--` when no `.session-indicator` exists.
- `resources/views/organization/stores/index.blade.php:43-85` — UI-002 store table and empty message; uses the shared `data-table` but builds badge components inside HTML strings.
- `resources/views/identity-access/operators/index.blade.php:36-68` — UI-003 operator table and empty message; same shared table/string-rendering pattern.
- `resources/views/components/layout/sidebar.blade.php:56-59` and `resources/views/components/layout/mobile-nav.blade.php:21-22` — UI-004 admin navigation exposes a registration route that `OperationController::create()` protects.
- `app/Modules/Operations/Policies/OperationPolicy.php:26-29` and `app/Modules/Operations/Http/Controllers/OperationController.php:98-126` — UI-004 server authorization correctly allows registration only for `OPERADOR`; this must not be weakened.
- `resources/views/operations/index.blade.php:18-46` and `resources/css/components/card.css:17-30` — UI-005 summary cards, including the dark `Movimiento Neto` variant; contrast and text must be verified against the screenshot and WCAG target.
- `resources/views/daily-closing/index.blade.php:6-78` — UI-006 uses native headings, links, selects, button, table, and `{{ $closures->links() }}` rather than the shared Stitch filter/table/badge/empty-state pattern.
- `resources/views/banking-network/banks/index.blade.php:25-60` — UI-007 bank table, empty message, and HTML-string badge actions.
- `resources/views/banking-network/agents/index.blade.php:50-89` — UI-008 agent table, empty message, filters, and HTML-string badge actions.
- `resources/views/operations/types/index.blade.php:44-79` — UI-009 operation-type table, empty message, pagination, and HTML-string badge actions. Pagination is already present in source; missing pagination would be a screenshot/runtime discrepancy, not a requirement to add a new feature.
- `resources/views/identity-access/sessions/index.blade.php:9-62` and `resources/views/components/screen/operation-filters.blade.php:15-66` — UI-010 session filters and table; the session page uses shared inputs/select/table, while its exact rendered alignment still requires browser evidence.

## Root Cause Hypothesis

**Confidence: high.** The batch is a set of manifestations of incomplete or inconsistent application of the visual integration plan. The strongest shared cause is the use of Blade component markup and PHP expressions inside strings passed to `x-ui.data-table` and `x-ui.metric-card`; Blade does not recursively compile those strings, so source such as `$actions`, `x-ui.badge`, `emptyMessage`, and encoded icon entities reaches the browser. Additional confirmed causes are incomplete authenticated-layout integration, hardcoded fallback/English content, duplicate page-specific session-timer logic, and native HTML in the daily-closing list. UI-004 is a navigation-contract mismatch rather than an authorization bug: the server policy correctly rejects administrators, while the shared menus advertise the forbidden route.

## Evidence Analysis

All ten catalog entries were reviewed and all ten images were opened. The status below combines image evidence with source verification.

| ID | Causa raíz | Archivos involucrados | Corrección propuesta | Riesgos | Prueba o verificación | Estado de aceptación |
|---|---|---|---|---|---|---|
| UI-001 | Shared topbar has English/hardcoded fallback content; authenticated layout omits the shared session indicator; home has duplicate timer logic that falls back to `--:--`. | `layouts/authenticated.blade.php`; `components/layout/topbar.blade.php`; `components/layout/session-indicator.blade.php`; `identity-access/home.blade.php`; `resources/css/layout.css` | Render the real session indicator in the authenticated topbar, remove the page-local timer, use real user/role data, and replace the title with approved Spanish copy. Do not change session expiry behavior. | Could duplicate timers or break all authenticated screens if inserted incorrectly; exact approved Spanish title/name format is not stated. | `ui-001.jpeg` and browser check; assert real name/role, no `--:--`, one ticking timer, and 375/768/1280/1440 layouts. | **Confirmed by image and source; browser acceptance pending.** |
| UI-002 | Store rows pass PHP/Blade source and `<x-ui.badge>` markup inside strings to `x-ui.data-table`; Blade does not compile nested directives in strings. | `organization/stores/index.blade.php`; `components/ui/data-table.blade.php`; `components/ui/badge.blade.php`; admin CSS | Stop embedding Blade/PHP in row strings. Build structured row data or render row actions/components through a supported data-table contract, preserving CRUD actions and empty state. | Changing shared table rendering affects every admin list; do not alter CRUD/actions. | `ui-002.jpeg` shows literal `$actions` source; assert valid rendered HTML in populated and empty `/admin/stores`. | **Confirmed by image and source.** |
| UI-003 | Same HTML-string rendering defect as UI-002 in operator rows. | `identity-access/operators/index.blade.php`; `components/ui/data-table.blade.php`; `components/ui/badge.blade.php` | Apply the shared structured-row/rendering correction and preserve pagination, policies, and deactivation behavior. | Shared fix may affect stores, banks, agents, types, and sessions. | `ui-003.jpeg` shows literal `$actions`/Blade source; assert valid rendered table HTML and approved empty state. | **Confirmed by image and source.** |
| UI-004 | Navigation is not aligned with server authorization: both menus show `operations.create` to admin, while `OperationPolicy::register()` rejects admin. English 403 text is a separate shared error presentation concern. | `components/layout/sidebar.blade.php`; `components/layout/mobile-nav.blade.php`; `OperationPolicy.php`; `OperationController.php`; possible framework error view (none found under `resources/views/errors/`) | Hide/remove the registration link for admin in both navigation components while preserving server-side denial. If Spanish 403 presentation is required, add/update the approved global error presentation only after locating the project's error-handling contract; do not change authorization. | Hiding a link must not be treated as security; inconsistent desktop/mobile menus could remain. Translating the 403 may be a separate scope item. | `ui-004.jpeg` confirms admin receives `403 This action is unauthorized.`; admin sees no registration link after fix, direct route remains 403, operator remains allowed. | **Confirmed by image and source; navigation fix pending.** |
| UI-005 | `metric-card` escapes the icon prop, so entities such as `&#x1F4CB;` render as literal text; the dark card label/value token combination has poor contrast in the evidence. | `operations/index.blade.php`; `components/ui/metric-card.blade.php`; `resources/css/components/card.css`; tokens/design docs | Render icons through a safe explicit icon contract and correct dark-card semantic colors to meet the documented contrast target. Keep monetary meaning and `S/` formatting unchanged. | Shared token changes affect dashboards and all dark metric cards; do not relabel gross amount as profit. | `ui-005.jpeg` shows literal icon entities and dark-on-dark content; add rendered icon and contrast assertions. | **Confirmed by image and source; implementation pending.** |
| UI-006 | Page is an unintegrated native-HTML implementation, unlike the approved shared Stitch patterns. | `daily-closing/index.blade.php`; `resources/css/screens/daily-closing.css`; `components/ui/data-table.blade.php`; `components/ui/select.blade.php`; `components/ui/badge.blade.php`; `components/ui/empty-state.blade.php`; pagination component | Replace native filters/table/links with shared components and the existing daily-closing visual patterns; add contextual `empty-state` for no closures. Preserve routes, filters, pagination query behavior, and real data. | The current pagination/filter behavior may regress; table horizontal overflow must remain controlled on mobile. | `ui-006.jpeg` confirms native controls and concatenated table headers (`IDAgenteFecha...`); test rows/no rows at all four target widths. | **Confirmed by image and source.** |
| UI-007 | Same HTML-string rendering defect as UI-002 in bank rows; `data-table` receives literal source instead of structured cells. | `banking-network/banks/index.blade.php`; `components/ui/data-table.blade.php`; `components/ui/empty-state.blade.php`; `components/ui/badge.blade.php` | Apply the shared structured-row/rendering correction and preserve bank CRUD and empty-query semantics. | Shared table changes can affect all admin lists; avoid altering empty query semantics. | `ui-007.jpeg` confirms literal PHP/Blade source in the table; assert valid populated and empty bank views. | **Confirmed by image and source.** |
| UI-008 | Same HTML-string rendering defect as UI-002 in agent rows. | `banking-network/agents/index.blade.php`; `components/ui/data-table.blade.php`; `components/ui/empty-state.blade.php`; `components/ui/badge.blade.php` | Apply the shared structured-row/rendering correction and preserve filters, relations, and real assignments. | Agent/store/bank relations and actions must remain unchanged; shared fixes can have broad scope. | `ui-008.jpeg` confirms literal PHP/Blade source, including `emptyMessage`, in the table; assert empty and populated views. | **Confirmed by image and source.** |
| UI-009 | Source already has `x-ui.pagination`, but row actions are passed as uncompiled PHP/Blade strings. | `operations/types/index.blade.php`; `components/ui/data-table.blade.php`; `components/ui/pagination.blade.php`; `components/ui/badge.blade.php` | Fix the shared row rendering; retain existing pagination and do not add a duplicate feature. | Unnecessary pagination changes could alter query parameters or policy-protected CRUD behavior. | `ui-009.jpeg` confirms literal PHP/Blade source; assert pagination remains functional and table HTML is valid. | **Confirmed by image and source; pagination is not a missing feature.** |
| UI-010 | Session table has the same HTML-string rendering defect; the supplied viewport shows filters rendered, while the table body exposes literal source. | `identity-access/sessions/index.blade.php`; `components/ui/input.blade.php`; `components/ui/select.blade.php`; `components/ui/data-table.blade.php`; `resources/css/components/filter-bar.css`; `resources/css/layout.css` | Apply the shared structured-row/rendering correction; retain responsive table behavior and verify filters at mobile widths. | Changing shared filter CSS can affect operations/admin filters; session data and authorization must remain unchanged. | `ui-010.jpeg` confirms literal PHP/Blade source in the table; test empty/populated sessions, keyboard navigation, and 375/768/1280/1440 layouts. | **Confirmed by image and source; filter alignment requires responsive verification.** |

## Confirmed Shared Causes and Scope Classification

1. **Authenticated session presentation**: UI-001 is a shared layout integration defect, not a new feature. The constitution and spec already require a server-derived countdown. The fix should reuse `x-layout.session-indicator` rather than invent another timer.
2. **Role-aware navigation**: UI-004 is a presentation/navigation defect exposing a forbidden action. The server policy must remain unchanged. Removing a misleading menu item is a correction, not a new permission rule.
3. **Native versus shared Stitch markup**: UI-006 is directly evidenced by the source and is an implementation gap against FR-012 and the plan's administration/daily-closing migration. It is not a request for a new business capability.
4. **Admin list rendering**: UI-002, UI-003, UI-007, UI-008, UI-009, and UI-010 visibly render literal PHP/Blade source. They share the data contract in which views build row content as strings, while `x-ui.data-table` outputs cell values as raw HTML and does not compile Blade again.
5. **Icon and contrast rendering**: UI-005 visibly renders encoded icon entities because `x-ui.metric-card` escapes `$icon`; its dark variant also needs a contrast correction based on the screenshot and DESIGN tokens.
6. **New requirements versus bugs**: UI-001's move of the timer to the topbar is consistent with the existing session-indicator requirement. UI-004's hidden admin link is navigation consistency, not a new authorization rule. UI-009's pagination is already implemented, so adding pagination would be redundant. Any new copy not backed by an approved source is out of scope under the report's text rules.

## Proposed Remediation

**Preferred**: Resolve the batch in shared-component order without changing backend behavior:

1. Correct `authenticated.blade.php`, `topbar.blade.php`, and `home.blade.php` so one shared server-derived session indicator appears in the topbar and real user/role/copy are used.
2. Make desktop and mobile navigation use the same role/policy-aware visibility for `operations.create`; leave `OperationPolicy` and controller authorization intact.
3. Migrate `daily-closing/index.blade.php` to existing Stitch components and its approved empty state.
4. Replace the confirmed admin list string-rendering pattern with structured component data; do not embed `<x-ui.*>` tags or PHP expressions inside HTML strings.
5. Correct the confirmed UI-005 icon escaping/contrast issue and verify UI-010's responsive filter behavior before changing shared filter CSS.
6. Replace text only when the exact expected Spanish text is documented by the functional spec, approved design, or supplied evidence.

No changes should be made to business rules, policies, calculations, authentication, persistence, or routes except removing an inaccurate navigation link if the route remains intentionally server-protected.

**Alternatives**:

- Fix each page independently — lower immediate blast radius, but duplicates empty-state/filter logic and conflicts with the shared-component requirement.
- Perform a browser accessibility/HTML audit first — useful for validating the confirmed malformed markup and contrast before applying shared CSS changes.

**Files likely to change**:

- `resources/views/layouts/authenticated.blade.php`
- `resources/views/components/layout/topbar.blade.php`
- `resources/views/components/layout/sidebar.blade.php`
- `resources/views/components/layout/mobile-nav.blade.php`
- `resources/views/identity-access/home.blade.php`
- `resources/views/daily-closing/index.blade.php`
- `resources/views/components/ui/data-table.blade.php` and/or affected list views, only if shared malformed output is reproduced
- `resources/css/components/card.css` and/or filter/layout CSS, only if browser verification confirms a shared visual defect
- Related feature/rendering tests under `tests/Feature/` and `tests/Unit/`

## Tests to Add or Update

- UI-001: authenticated admin home renders approved Spanish title, real user identity/role, exactly one live countdown, and no `--:--` fallback; test operator and admin variants.
- UI-004: admin navigation omits `operations.create` in desktop and mobile; direct admin request remains 403; operator navigation and request remain allowed.
- UI-006: daily-closing list renders Stitch filters/table/badges/pagination and `x-ui.empty-state` with no closures; existing query filters remain functional.
- UI-002/UI-003/UI-007/UI-008/UI-009: empty and populated list rendering tests assert approved text, no literal component tags, valid table structure, and pagination where applicable.
- UI-005: render the history summary and run a contrast assertion/manual WCAG audit for `Movimiento Neto`; assert monetary labels retain their functional meaning.
- UI-010: render empty/populated sessions with filters at desktop/mobile widths; verify keyboard access and no unintended global horizontal overflow.
- Cross-cutting: run `php artisan test`, `npm run build`, Blade compilation, and browser screenshots at 375, 768, 1280, and 1440 pixels. Capture post-fix evidence for every UI-XXX.

## Risks & Considerations

- The evidence confirms the rendering defects; exact final Spanish copy remains unspecified for some catalog entries.
- Shared layout/table/filter/CSS corrections have broad blast radius across production screens.
- The admin menu fix must not weaken server authorization; hiding a link is not an authorization control.
- Reusing the session indicator must avoid duplicate timers and preserve explicit renewal/session-expiry behavior.
- The report's source references `specs/007-integracion-ui-sistema-real/spec.md`, while the supplied reviewed specification is `specs/007-stitch-visual-integration/spec.md`; this source inconsistency should be resolved for traceability.
- `specs/007-stitch-visual-integration/spec.md` is marked Draft and `MANIFEST.md` marks the visual reference as pending refinement; visual acceptance should use the explicitly approved decisions and functional terminology, not unapproved mockup HTML.
- No business, authorization, financial, session, or persistence rule may be changed under this batch.

## Open Questions

- [NEEDS CLARIFICATION: Provide the exact approved Spanish text for “Financial Operations”, the greeting, empty states, and the 403 page.]
- [NEEDS CLARIFICATION: Confirm whether `/operations/create` is intentionally unavailable to administrators; current `OperationPolicy` says yes.]
- [NEEDS CLARIFICATION: Confirm whether UI-005's “globo/campo” refers specifically to the dark `Movimiento Neto` metric card.]
- [NEEDS CLARIFICATION: Confirm the expected session timer location and whether the topbar timer button should become the shared live indicator.]
- [NEEDS CLARIFICATION: Resolve the discrepancy between the report's referenced integration spec path and the supplied `007-stitch-visual-integration` artifacts.]

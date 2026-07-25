# Bug Assessment: Forms and filters are not consistently designed

- **Slug**: forms-filters-layout
- **Created**: 2026-07-23T15:47:17-05:00
- **Source**: pasted text plus local Stitch reference
- **Verdict**: valid
- **Severity**: medium

## Report (verbatim or summarized)

The forms at `/admin/agents/create`, `/admin/users`, `/daily-closures/create`, and `/admin/operation-types/create` look like unstyled or partially styled HTML5 forms. They should follow the unified visual language from `docs/design/stitch/v1/registro_r_pido_de_operaci_n/`. The report also asks for every view's filters to be reviewed, specifically calling out `/sessions`, whose filter controls are left-aligned and visually inconsistent.

The local Stitch reference contains `code.html` and `screen.png`. Its form language uses a centered content canvas, a surface card with border/radius, clear uppercase labels, consistent control padding and focus states, grouped fields, responsive grid columns, and aligned action controls.

No external URL was supplied. The Stitch path is a local filesystem reference, not a URL, so no URL fetch was performed.

## Symptom

The daily-closing create view uses bare `<label>`, `<select>`, `<input>`, and `<button>` elements without the application's `form-input`, `form-label`, `form-group`, card, or action classes. Other target forms use shared input components but place them in a plain card with no consistent form layout/gap wrapper, while filter views mix inline flex styles, `filter-bar`, `admin-filters-panel`, and an unstyled `form-filter` class. The result is inconsistent spacing, alignment, visual hierarchy, and responsive behavior across forms and filters.

Expected behavior is one cohesive form/filter system matching the Stitch reference: shared surfaces, labels, controls, errors, spacing, action alignment, responsive columns, and predictable mobile stacking.

## Reproduction

1. Open `/admin/agents/create`, `/admin/users`, `/daily-closures/create`, and `/admin/operation-types/create` while authenticated with the required role.
2. Compare card width, field spacing, labels, control borders/focus states, validation messages, and action buttons between the four forms and the Stitch reference.
3. Open `/sessions`, `/operations`, `/admin/dashboard`, `/daily-closures`, and other list/reporting views with filters.
4. Resize to a narrow viewport and compare filter alignment, wrapping, control widths, and action placement.
5. Review other form views such as agent, store, bank, bank-agent, operator, and operation forms for the same shared inconsistency.

Exact target viewport dimensions and before/after screenshots were not supplied: `[NEEDS CLARIFICATION: confirm target desktop/mobile widths and final Stitch screenshots if pixel-level matching is required]`.

## Suspected Code Paths

- `resources/views/daily-closing/create.blade.php:20-50` - uses raw HTML controls and has no shared form/card classes, directly explaining the unstyled appearance.
- `resources/views/agents/form.blade.php:8-88` - uses `x-ui.input` but relies on a plain card and inline action styles instead of a reusable form layout.
- `resources/views/identity-access/operators/form.blade.php:8-51` - has the same plain-card/inline-action pattern for `/admin/users`.
- `resources/views/operations/types/form.blade.php:20-76` - uses shared controls but lacks a consistent form shell and layout/action component.
- `resources/views/components/ui/input.blade.php:10-32` and `select.blade.php:9-34` - provide reusable field primitives, but no form-level grid, section, spacing, or action contract.
- `resources/css/components/input.css:5-72` - styles individual controls but does not define a form container/layout that matches the Stitch reference.
- `resources/css/components/filter-bar.css:5-83` - styles `.filter-bar`, but not the `.form-filter` class used by operation filters and does not govern all filter variants.
- `resources/views/components/screen/operation-filters.blade.php:15-63` - uses an unstyled `.form-filter` form inside an off-canvas wrapper.
- `resources/views/identity-access/sessions/index.blade.php:9-31` and `operations/types/index.blade.php:21-33` - use inline flex styling for filters, causing inconsistent alignment and spacing.
- `resources/views/components/screen/admin-filters.blade.php:19-58` - has a dedicated panel/grid but inline action layout, creating another filter style.
- `resources/views/daily-closing/index.blade.php:24-29` - uses `.filter-bar`, providing a third filter presentation.
- `docs/design/stitch/v1/registro_r_pido_de_operaci_n/code.html:139-220` - local design reference defining the intended surface, grid, label, control, and action treatment.

## Root Cause Hypothesis

**Confidence: high.** The project has field-level primitives and multiple partial filter styles, but no shared form shell or unified filter layout contract. Several views bypass those primitives entirely, while others mix inline styles with component classes. The absence of a form-level design system is the root cause of the inconsistent HTML5-like appearance and left-aligned filter layouts.

## Proposed Remediation

**Preferred**: Create shared form layout styles/components that encode the Stitch reference: a constrained form surface/card, responsive field grid, consistent section spacing, `.form-actions`, and a unified `.filter-panel`/`.filter-form` layout with desktop alignment and mobile stacking. Extend the existing `form-group`, `form-label`, `form-input`, and `filter-bar` primitives rather than introducing separate one-off styles.

Migrate the four reported forms first, replacing raw controls in daily closing with `x-ui.input`/`x-ui.select` or equivalent shared markup, then migrate the remaining form views. Normalize all filter consumers, including sessions, operations, admin dashboard, daily closing, operation types, and comparison filters, removing inline layout styles and ensuring each filter has an aligned action group and accessible labels.

**Alternatives**:

- Apply only page-specific CSS to the four reported routes. This is faster but preserves inconsistent filters and creates more long-term visual drift.
- Rebuild all forms with Tailwind classes copied from the Stitch HTML. This can match the reference quickly but duplicates styling, conflicts with the existing CSS design tokens, and increases maintenance cost.

**Files likely to change**:

- `resources/css/components/input.css`
- `resources/css/components/filter-bar.css`
- A new shared form stylesheet or existing `resources/css/screens/*.css` as appropriate
- `resources/views/daily-closing/create.blade.php`
- `resources/views/agents/form.blade.php`
- `resources/views/identity-access/operators/form.blade.php`
- `resources/views/operations/types/form.blade.php`
- `resources/views/components/screen/operation-filters.blade.php`
- `resources/views/components/screen/admin-filters.blade.php`
- Other filter/form views identified during migration
- Feature/UI visual tests and browser visual tests

**Tests to add or update**:

- Render each reported form and assert shared form shell, field, error, and action markup is present.
- Render every filter-bearing route and assert a shared filter wrapper and responsive structure.
- Add browser/visual regression checks at desktop and mobile widths against the Stitch reference.
- Submit representative validation cases and verify errors remain associated with their controls.
- Verify labels, focus states, keyboard order, and mobile stacking remain accessible.

## Risks & Considerations

- Replacing raw controls must preserve field names, old values, validation errors, disabled states, and submission behavior.
- A shared responsive grid can change form widths and break long select labels if minimum widths are not chosen carefully.
- Removing inline styles may affect existing views that rely on them for action alignment; review all consumers together.
- The Stitch HTML loads external fonts/CDN assets, but the application should continue using its local token/font strategy unless explicitly approved.
- This is a broad visual change across many screens; backend behavior and routes should remain unchanged.

## Open Questions

- [NEEDS CLARIFICATION: Which exact desktop and mobile viewport widths should be used for visual acceptance?]
- [NEEDS CLARIFICATION: Should all forms use the Stitch two-column desktop grid, or only forms with enough fields?]
- [NEEDS CLARIFICATION: Should filters be shown inline on desktop and in an off-canvas panel on mobile for every list view?]
- [NEEDS CLARIFICATION: Are the Stitch external font imports approved, or must the current local fonts/tokens be preserved?]

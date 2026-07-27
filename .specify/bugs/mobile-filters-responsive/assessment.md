# Bug Assessment: Filters layout not responsive on /operations and /sessions

- **Slug**: mobile-filters-responsive
- **Created**: 2026-07-27
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: medium

## Report (verbatim or summarized)

> "La maquetacion de los filtros de las siguientes rutas '/operations, /sessions' cuando se ingresa por el celular osea el responsive no esta bien, esta todo grande, se sobredimensiona, se ve mal para el usuario final cuando ingresan desde un celular"

The filter layout on `/operations` and `/sessions` is not responsive on mobile devices — elements are oversized, overflow, and the layout looks broken for end users accessing from a phone.

## Symptom

On mobile viewports (≤768px), the filter forms on `/operations` and `/sessions` do not adapt properly. Filters either remain in rigid multi-column desktop layouts or appear oversized, causing horizontal overflow, unusably small inputs, or excessive vertical space consumption. Expected: filters should stack vertically, fit within the screen width, and be sized appropriately for touch interaction on mobile.

## Reproduction

1. Open `https://agent-control-operations.onrender.com/operations` on a mobile device or Chrome DevTools mobile view (375px–768px).
2. Observe the filter form: 9 filters displayed in a rigid 4-column grid → inputs are either too narrow (~80px) or overflow.
3. Open `/sessions` on mobile.
4. Observe the filter form: inputs take full width with `min-width: 180px` and excessive padding; the overall panel feels oversized on a small screen.

## Suspected Code Paths

- `resources/views/operations/index.blade.php:28` — inline `style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr))"` overrides responsive CSS. The `.filter-bar` class at `max-width: 768px` uses `display: none` (for the offcanvas pattern), but the inline `display: grid` takes precedence, keeping 4 columns visible on mobile.
- `resources/css/components/filter-bar.css:39-42` — `.filter-bar .form-group { flex: 1 1 180px; min-width: 180px }` and `.filter-form .form-group { flex: 1 1 180px; min-width: 180px }` — the `min-width: 180px` is too wide for small screens (e.g., 320px–375px), though `flex-wrap` and `flex-direction: column` at `@media (max-width: 768px)` do stack them.
- `resources/views/components/screen/operation-filters.blade.php` — this component already implements the proper `filter-bar-wrapper` → `filter-offcanvas` mobile pattern with a toggle button, but `operations/index.blade.php` does NOT use this component; it has its own inline form.
- `resources/views/identity-access/sessions/index.blade.php:9-35` — uses `.filter-form` which is technically responsive (stacks at 768px), but the spacing/padding on mobile could be tightened.

## Root Cause Hypothesis

**Confidence: high.** The `/operations` page uses an inline `style` attribute on the filter form (`grid-template-columns: repeat(4, ...)`) that has zero responsive breakpoints. On mobile, this forces 4-column grid with ~80px-wide inputs that are too cramped or overflow. The existing CSS at `@media (max-width: 768px)` hides `.filter-bar` to show the offcanvas panel instead, but the inline `display: grid` overrides `display: none`, so the broken 4-column layout stays visible. The `/sessions` page doesn't have this inline style issue — its filters stack via `flex-direction: column` at 768px, but the `min-width: 180px` and overall spacing feel oversized on phone screens.

## Proposed Remediation

**Preferred**:

1. **`operations/index.blade.php`**: Replace the inline grid style with a responsive grid class that adapts to screen width. Add a dedicated CSS class `.filter-bar--responsive` in `filter-bar.css` that uses `grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))` and collapses to a single column at `@media (max-width: 768px)`. Alternatively, reuse the existing `operation-filters.blade.php` component (which has the offcanvas pattern) if the filter field set is compatible.

2. **`filter-bar.css`**: For the `/sessions` page, add or enhance mobile media queries to reduce `min-width` of form groups to `160px` or remove it entirely at `max-width: 480px`, and reduce padding on `.filter-panel` for small screens.

**Alternatives**:
- Use the offcanvas/toggle pattern from `filter-bar-wrapper` on both pages. This would hide the desktop filter bar and show a slide-out panel on mobile. More work but provides the best mobile UX.
- Quick fix: just add `@media (max-width: 768px) { display: grid !important; grid-template-columns: 1fr !important; }` to force single column on mobile for the operations inline style. Not clean but minimal.

**Files likely to change**:
- `resources/views/operations/index.blade.php:28` — remove/replace inline style
- `resources/css/components/filter-bar.css` — add `.filter-bar--responsive` class, enhance mobile breakpoints
- `resources/views/identity-access/sessions/index.blade.php` — optionally reduce padding on mobile

**Tests to add or update**:
- Visual regression test: screenshot `/operations` and `/sessions` at 375px and 768px viewport widths.
- Manual QA: verify filters stack in a single column, touch targets are ≥44px, no horizontal scroll.

## Risks & Considerations

- Changing the operations filter layout may shift UI elements below the fold, but the original layout is already broken on mobile.
- The `operation-filters.blade.php` component uses `bank_agent_id` while the inline form uses `agent_id` — the parameter names differ, so simply swapping to the component may break filter functionality. A quick CSS-only fix is safer.
- No backend changes required — purely CSS/HTML.

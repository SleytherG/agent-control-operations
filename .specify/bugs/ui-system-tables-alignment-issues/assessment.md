# Bug Assessment: System tables have inconsistent alignment and responsive sizing

- **Slug**: ui-system-tables-alignment-issues
- **Created**: 2026-07-23T15:29:51-05:00
- **Source**: pasted text
- **Verdict**: valid
- **Severity**: medium

## Report (verbatim or summarized)

The tables in `/admin/agents/`, `/admin/users`, `/admin/operation-types`, and `/sessions` have misaligned column headings, rows, and columns. The request is to center headings and their corresponding content, make columns grow consistently with their data, and review every table in the system because the same shared styling may hide the issue when a table has little data.

No URL was supplied. The route paths are application paths, not external URLs, so no URL fetch was performed.

## Symptom

Table headers and body cells do not consistently share the same alignment: the common stylesheet defaults headers and cells to left alignment, while only selected status/action columns opt into center alignment. On tables with long values or multiple action controls, column widths and visual alignment become uneven instead of remaining predictable and responsive.

Expected behavior is a consistent alignment contract per column, headers aligned with their body cells, readable sizing as data grows, and horizontal overflow on narrow screens without breaking the surrounding layout.

## Reproduction

1. Open `/admin/agents/`, `/admin/users`, `/admin/operation-types`, and `/sessions` while authenticated as an administrator.
2. Compare each `<thead>` heading with the corresponding `<tbody>` cells, especially status, action, identifier, date, and long-text columns.
3. Repeat with enough records or long values to exercise the table width, then resize to a narrow viewport.
4. Repeat the same inspection for the other application views that render `.data-table`.

The report does not include screenshots or exact viewport/data dimensions: `[NEEDS CLARIFICATION: capture before/after screenshots and target viewport widths if pixel-level matching is required]`.

## Suspected Code Paths

- `resources/css/components/table.css:5-26` - `.table-responsive` only enables horizontal scrolling, while `.data-table` has `width: 100%` and headers default to `text-align: left`; no shared minimum sizing or responsive column policy is defined.
- `resources/css/components/table.css:47-52` - body cells have shared padding and vertical alignment but no default horizontal alignment matching the intended table contract.
- `resources/views/agents/index.blade.php:43-50` - most columns use default left alignment; only status and actions are explicitly centered.
- `resources/views/identity-access/operators/index.blade.php:37-51` - username/email remain left aligned while status/actions are centered, producing mixed alignment in the affected `/admin/users` table.
- `resources/views/operations/types/index.blade.php:37-43` - name, description, cash/digital values, and order lack explicit alignment while status/actions are centered.
- `resources/views/identity-access/sessions/index.blade.php:35-41` - identifier and date/reason columns use defaults while only status is centered.
- `resources/views/components/ui/data-table.blade.php:8-22` - the reusable table component supports `right` and `center` metadata, but callers must supply it; headers and cells can therefore receive inconsistent alignment declarations.
- Other `.data-table` consumers: `resources/views/banking-network/agents/index.blade.php`, `banking-network/banks/index.blade.php`, `daily-closing/index.blade.php`, `organization/stores/index.blade.php`, `operations/index.blade.php`, `operations/confirmation.blade.php`, `operations/types/index.blade.php`, `reporting/operator-comparison.blade.php`, and `components/screen/closing-detail.blade.php`.

## Root Cause Hypothesis

**Confidence: high.** The common table CSS establishes a left-aligned header baseline and does not define a complete column alignment or sizing strategy. Individual Blade tables then add center classes only to a subset of columns, while the reusable component applies alignment only when each header/cell provides metadata. This makes the rendered result depend on each view's markup and data shape, so the same defect can appear across all tables and become more visible as content grows.

## Proposed Remediation

**Preferred**: Define one shared table layout contract in `resources/css/components/table.css` and apply it consistently to every `.data-table`. Make the responsive wrapper the overflow boundary, give tables a sensible content minimum width where needed, use stable column sizing behavior, and ensure headers and body cells use the same alignment classes. Center labels and categorical/action values as requested, retain right alignment for numeric and monetary values, and keep long identifiers/text readable rather than forcing every value into a centered block.

Update all table view call sites and the reusable `x-ui.data-table` component so each column declares alignment once and applies it to both its `<th>` and `<td>`. Avoid one-off inline styles. Review action columns for wrapping or controlled whitespace so several buttons do not distort neighboring columns on desktop or narrow viewports.

**Alternatives**:

- Set every `.data-table th, .data-table td` to `text-align: center` globally. This is smaller but harms readability for descriptions, usernames, dates, and financial values and does not solve width policy by itself.
- Convert tables to CSS grid/card layouts on mobile. This can improve narrow-screen usability but is a larger visual redesign and would make the existing shared table component and accessibility semantics more complex.

**Files likely to change**:

- `resources/css/components/table.css`
- `resources/views/components/ui/data-table.blade.php`
- All `.data-table` Blade views listed under Suspected Code Paths, especially the four reported routes.
- `tests/Feature/UiVisualStabilizationTest.php` and/or browser visual tests for structural alignment assertions.

**Tests to add or update**:

- Render each affected route and assert header/body alignment classes are present consistently for every column.
- Add a component test covering `center`, `right`, and default alignment metadata on both header and body cells.
- Add browser or visual regression coverage at desktop and narrow viewport widths with long values and multiple action buttons.
- Preserve semantic table structure and verify the responsive wrapper remains keyboard and screen-reader usable.

## Risks & Considerations

- A global centering rule could make monetary, date, identifier, and long-text columns less readable; alignment should be explicit by data type.
- Adding a minimum table width can introduce horizontal scrolling on mobile, but removing overflow would cause clipping or layout breakage.
- Changing shared CSS affects every table in the application, including reporting and daily-closing screens; regression review must cover all consumers.
- Visual tests may be environment-sensitive if fonts, viewport dimensions, or seeded data differ.
- No backend, database, or API changes are expected.

## Open Questions

- [NEEDS CLARIFICATION: Should all columns be centered literally, or should numeric/financial columns remain right-aligned while labels, status, and actions are centered?]
- [NEEDS CLARIFICATION: What desktop and mobile viewport widths define the required responsive behavior?]
- [NEEDS CLARIFICATION: Are there approved Stitch reference screenshots for the final table spacing and alignment?]

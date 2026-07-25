# Bug Fix: Estabilización visual y de contenido UI V1

- **Slug**: ui-visual-content-stabilization-v1
- **Fixed**: 2026-07-23
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Corrected the ten cataloged UI observations without changing authorization, business rules, calculations, persistence, or authentication. Shared layout content now uses real session/user data, role navigation matches server policy, affected tables no longer print literal PHP/Blade source, metric icons and contrast render correctly, and the daily-closing list uses the established Stitch visual components.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/views/layouts/authenticated.blade.php` | modified | Passes the real session expiry into the shared topbar |
| `resources/views/components/layout/topbar.blade.php` | modified | Spanish title, real user identity, shared countdown; removed mock store/name/notification content |
| `resources/views/components/layout/session-indicator.blade.php` | modified | Uses the server expiry timestamp and correct initial remaining time |
| `resources/views/identity-access/home.blade.php` | modified | Removes duplicate `--:--` timer and uses role-correct dashboard link |
| `resources/views/components/layout/sidebar.blade.php` | modified | Hides operation registration from admin navigation only |
| `resources/views/components/layout/mobile-nav.blade.php` | modified | Mirrors desktop role visibility |
| `resources/views/organization/stores/index.blade.php` | modified | Replaces uncompiled row strings with semantic Blade table rows |
| `resources/views/identity-access/operators/index.blade.php` | modified | Replaces uncompiled row strings with semantic Blade table rows |
| `resources/views/banking-network/banks/index.blade.php` | modified | Replaces uncompiled row strings with semantic Blade table rows |
| `resources/views/banking-network/agents/index.blade.php` | modified | Replaces uncompiled row strings with semantic Blade table rows |
| `resources/views/operations/types/index.blade.php` | modified | Fixes row rendering while retaining existing pagination |
| `resources/views/identity-access/sessions/index.blade.php` | modified | Fixes session row/badge rendering and localized statuses |
| `resources/views/operations/index.blade.php` | modified | Fixes populated history row/badge rendering |
| `resources/views/components/ui/metric-card.blade.php` | modified | Renders trusted component icon entities instead of escaping them visibly |
| `resources/css/components/card.css` | modified | Raises dark metric-card text/icon contrast |
| `resources/views/daily-closing/index.blade.php` | modified | Migrates filters, table, badges, actions, empty state, and pagination to Stitch patterns |
| `resources/views/components/ui/select.blade.php` | modified | Supports existing `selected` callers and usable empty filter options |
| `resources/views/components/ui/pagination.blade.php` | modified | Preserves query filters and enables real previous/next links |
| `tests/Feature/UiVisualStabilizationTest.php` | added test | Covers shared timer/content, role navigation, literal-source regression, metrics, and daily closings |

## Diff Highlights

```blade
<x-layout.topbar
    :user="$user ?? null"
    :role="$role ?? 'operator'"
    :session-expires-at="$sessionExpiresAt ?? null"
/>
```

```blade
@forelse($banks as $bank)
    <tr>
        <td>{{ $bank->code }}</td>
        <td>{{ $bank->name }}</td>
        <td><x-ui.badge ...>...</x-ui.badge></td>
    </tr>
@empty
    <tr><td colspan="4" class="table-empty">No se encontraron bancos.</td></tr>
@endforelse
```

The table pattern replaces strings containing PHP expressions and `<x-ui.*>` tags, which Blade cannot recursively compile.

## Tests Added or Updated

- `tests/Feature/UiVisualStabilizationTest::test_admin_home_uses_real_identity_and_single_shared_session_timer` — verifies UI-001.
- `tests/Feature/UiVisualStabilizationTest::test_operation_registration_navigation_matches_server_authorization` — verifies UI-004 without weakening policy enforcement.
- `tests/Feature/UiVisualStabilizationTest::test_admin_lists_do_not_render_literal_php_or_blade_source` — verifies UI-002, UI-003, UI-007, UI-008, UI-009, and UI-010.
- `tests/Feature/UiVisualStabilizationTest::test_history_icons_are_not_html_escaped` — verifies UI-005.
- `tests/Feature/UiVisualStabilizationTest::test_daily_closings_index_uses_stitch_components_and_empty_state` — verifies UI-006.

## Local Verification

- `php artisan test --filter="UiVisualStabilizationTest|AdminViewsStitchTest|LayoutVariablesTest|ClosingViewTest|OperationHistoryViewTest"` → **20 passed, 80 assertions**.
- `php artisan view:cache` → **passed**, Blade templates compiled successfully.
- `npm run build` → **passed**, Vite production build completed.
- `git diff --check` → **passed**, no whitespace errors.
- `php artisan test` → **211 passed, 38 failed, 23 skipped**. The full suite remains red on pre-existing/out-of-scope contracts including missing `AuthSession::factory()`, legacy login HTTP expectations, reporting/dashboard output expectations, and unrelated operation-detail assertions. The targeted UI regression set is green.
- Manual checks: all ten before-fix evidence images were reviewed. Post-fix browser screenshots were not captured during this command.

## Deviations from Assessment

- Expanded the view changes to the populated `/operations` table after static verification found the same uncompiled Blade-string defect covered by UI-005. This prevents the bug from returning when the empty history gains rows.
- Updated shared `select` and `pagination` components because affected pages already depended on unsupported `selected` props and pagination links that discarded active filters. These changes preserve existing behavior rather than add functionality.
- Did not add a custom Spanish 403 error page. The assessment classified that presentation as separate unless exact approved copy/error handling was specified; server authorization remains unchanged.

## Follow-ups

- Run `/speckit.bug.test slug=ui-visual-content-stabilization-v1` and capture post-fix screenshots at 375, 768, 1280, and 1440 pixels.
- Resolve the unrelated full-suite failures in separate bug reports; do not mix them into this visual stabilization batch.
- If a dedicated Spanish 403 page is desired, specify its approved copy and assess it separately.

# Bug Verification: Toolbar buttons became misaligned after filter homogenization

- **Slug**: toolbar-filter-regression
- **Tested**: 2026-07-23T16:09:34-05:00
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: partial

## Summary

The shared toolbar contract now exposes explicit anchoring for primary page actions and a dedicated Admin dashboard filter form/action layout. Structural regression tests, page-load checks, build verification, and source inspection all pass, but the original visual browser reproduction was not exercised directly, so the result remains partial.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Reproduction (post-fix) | Static inspection of affected sources for `page-toolbar__primary`, `admin-filters-form`, and `admin-filters-actions` | pass | Confirmed in `/admin/agents`, `/admin/users`, `/admin/operation-types`, and the Admin dashboard filter component. |
| New / updated tests | `php artisan test tests/Feature/FormFilterLayoutTest.php --stop-on-failure` | pass | 3 tests passed (51 assertions), including the new toolbar regression check. |
| Regression suite | `php artisan test tests/Feature/Reporting/AdminDashboardViewTest.php --stop-on-failure` | pass | Admin dashboard still loads, preserves auth boundary, and shows empty state correctly. |
| Regression suite | `php artisan test tests/Feature/Agents/AgentAuthorizationTest.php --stop-on-failure` | pass | 9 assertions passed; affected Admin agents screen remains accessible only to the right role. |
| Frontend build | `npm run build` | pass | Vite production build completed successfully. |
| Lint / diff | `php -l tests/Feature/FormFilterLayoutTest.php && git diff --check` | pass | No syntax or diff issues. |
| Browser smoke test | Manual desktop/mobile inspection of `/admin/dashboard`, `/admin/agents`, `/admin/users`, `/admin/operation-types` | not-run | No browser session or screenshot capture was performed in this verification step. |

## Output Excerpts

```
FormFilterLayoutTest: 3 passed (51 assertions)
AdminDashboardViewTest: 3 passed (4 assertions)
AgentAuthorizationTest: 9 passed (9 assertions)
Vite build: completed successfully
git diff --check: passed
```

## Residual Risks

- The original issue was a visual alignment regression, and no live browser verification was performed at desktop/mobile widths.
- Structural assertions confirm the intended classes and page composition, but they do not measure actual pixel alignment or wrapping.
- Additional pages using `page-toolbar` in the future should be smoke-tested to ensure the shared rule still fits their composition.

## Recommendation

Hold for a quick browser smoke test on the four affected routes. If the primary button remains visually anchored and the filter controls stay aligned on both desktop and mobile widths, close the bug; otherwise reopen with screenshots and viewport details.

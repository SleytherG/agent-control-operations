# Bug Verification: TypeError on inline require() array access in DemoOperatorController

- **Slug**: inline-require-array-access
- **Tested**: 2026-07-22
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: verified

## Summary

The bug no longer reproduces. `/demo/operator/register` returns 200 (was 500). All sibling methods in `DemoOperatorController` and other demo routes continue to work with no regressions.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Reproduction (post-fix) | `curl http://localhost:8000/demo/operator/register` | pass | 200 (was 500) |
| Regression: same controller | `curl http://localhost:8000/demo/operator/dashboard` | pass | 200 |
| Regression: same controller | `curl http://localhost:8000/demo/operator/history` | pass | 200 |
| Regression: other demo route | `curl http://localhost:8000/demo/admin/dashboard` | pass | 200 |
| Regression: other demo route | `curl http://localhost:8000/demo/daily-closing/1` | pass | 200 |

## Output Excerpts

```
/demo/operator/register → 200
/demo/operator/dashboard → 200
/demo/operator/history → 200
/demo/admin/dashboard → 200
/demo/daily-closing/1 → 200
```

## Residual Risks

- None. The fix follows the same pattern as the `dashboard()` method in the same controller, which was already working correctly.

## Recommendation

Close the bug — verified end-to-end. The register page renders correctly with full HTML content (registration form, bank list, operation type selectors) and no TypeError.

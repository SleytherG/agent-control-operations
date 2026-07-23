# Bug Fix: Vite::__invoke() called with zero arguments from JS comment

- **Slug**: vite-invoke-zero-args
- **Fixed**: 2026-07-22
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Removed the `@` prefix from a `// Chart.js loaded via @vite, init inline` JavaScript comment so Blade no longer interprets it as a directive. Cleared compiled view cache to regenerate the compiled template.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/views/screens/operator/dashboard.blade.php:72` | modified | `@vite` → `vite` in JS comment |

## Diff Highlights

```diff
-        // Chart.js loaded via @vite, init inline
+        // Chart.js loaded via vite, init inline
```

## Tests Added or Updated

No automated tests exist for demo views. Manual verification confirms:

- `GET /demo/operator/dashboard` → 200 (was 500)
- Compiled view cache: zero `Vite()` zero-argument calls detected

## Local Verification

- `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/demo/operator/dashboard` → `200`
- `php artisan view:clear` → succeeded
- `php artisan view:cache` → succeeded; `grep -r "Vite()" storage/framework/views/` → no matches

## Deviations from Assessment

None. Followed the preferred remediation exactly.

## Follow-ups

- Consider adding an HTML/browser test for `GET /demo/operator/dashboard` asserting 200.

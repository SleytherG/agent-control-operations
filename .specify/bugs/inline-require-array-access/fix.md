# Bug Fix: TypeError on inline require() array access in DemoOperatorController

- **Slug**: inline-require-array-access
- **Fixed**: 2026-07-22
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Extracted the `require resource_path('demo/user.php')` call to a local variable `$user` in `register()`, then accessed the `'operator'` key on it. This eliminates the PHP parsing ambiguity of inline `require($path)['key']` and matches the pattern already used in the `dashboard()` method.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `app/Http/Controllers/Demo/DemoOperatorController.php:25-28` | modified | Inline `require()['key']` → variable assignment then key access |

## Diff Highlights

```diff
 public function register()
 {
+    $user = require resource_path('demo/user.php');
+
     return view('screens.operator.register', [
-        'user' => require(resource_path('demo/user.php'))['operator'],
+        'user' => $user['operator'],
```

## Tests Added or Updated

No automated tests exist for demo controllers. Manual verification confirms:
- `GET /demo/operator/register` → 200 (was 500)

## Local Verification

- `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/demo/operator/register` → `200`

## Deviations from Assessment

None.

## Follow-ups

- Add an HTTP test for `GET /demo/operator/register` asserting 200.

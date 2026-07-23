# Bug Assessment: TypeError on inline require() array access in DemoOperatorController

- **Slug**: inline-require-array-access
- **Created**: 2026-07-22
- **Source**: pasted text (error trace from browser)
- **Verdict**: valid
- **Severity**: medium

## Report (verbatim or summarized)

Accessing `http://localhost:8000/demo/operator/register` throws:

```
TypeError: Cannot access offset of type string on string
at app/Http/Controllers/Demo/DemoOperatorController.php:28
```

Line 28:

```php
'user' => require(resource_path('demo/user.php'))['operator'],
```

## Symptom

The operator register page crashes with a 500 error. `require(resource_path('demo/user.php'))['operator']` is parsed incorrectly by PHP — instead of requiring the file and accessing the `'operator'` key on the returned array, PHP attempts to access the `'operator'` offset on the string path itself, causing a TypeError.

## Reproduction

1. Run `composer run dev`
2. Open `http://localhost:8000/demo/operator/register` in a browser
3. Observe 500 error with `TypeError: Cannot access offset of type string on string`

## Suspected Code Paths

- `app/Http/Controllers/Demo/DemoOperatorController.php:28` — `require(resource_path('demo/user.php'))['operator']` — inline `require()['key']` syntax causes PHP parsing ambiguity
- `app/Http/Controllers/Demo/DemoOperatorController.php:12` — `$user = require resource_path('demo/user.php');` — the correct pattern used in `dashboard()`, works fine

## Root Cause Hypothesis

**Confidence: High.** In PHP, `require` is a language construct, not a function. When written as `require($path)['key']`, PHP's parser can misinterpret the parentheses and array bracket: it may parse the expression as `require( ($path)['key'] )` — trying to apply array access to the string path value first, then requiring the result. This returns the string `('demo/user.php')['operator']` (which evaluates to the character `u` in PHP since `'string'[0]` is the first character), causing a mismatch between the expected path and the actual value.

The `dashboard()` method at line 12 avoids this entirely by assigning `require` to a variable first:
```php
$user = require resource_path('demo/user.php');  // line 12 — works
// ...
'user' => $user['operator'],                      // line 19 — works
```

## Proposed Remediation

**Preferred — Assign to variable first, matching the dashboard() pattern:**

Change lines 27-43 to assign `require` to a variable before accessing the key:

```php
public function register()
{
    $user = require resource_path('demo/user.php');

    return view('screens.operator.register', [
        'user' => $user['operator'],
        'role' => 'operator',
        // ...
    ]);
}
```

This is the safest approach, consistent with the `dashboard()` method in the same controller.

**Alternatives:**
- Wrap `require` in extra parentheses: `(require resource_path('demo/user.php'))['operator']`. This works in PHP 8 but is fragile across versions.
- Use `include` instead of `require` (same parsing issue).

**Files likely to change:**
- `app/Http/Controllers/Demo/DemoOperatorController.php` — lines 25-44

**Tests to add or update:**
- HTTP test: `GET /demo/operator/register` should return 200

## Risks & Considerations

- The bug only affects `register()`. `dashboard()` and `history()` already use the correct pattern.
- No other controllers in the project use the inline `require()['key']` pattern — verified by searching the codebase.
- The fix is a refactor of 2-3 lines, zero behavioral change.

## Open Questions

- None. Root cause is fully understood and reproducible.

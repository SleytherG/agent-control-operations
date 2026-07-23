# Bug Assessment: Vite::__invoke() called with zero arguments from JS comment

- **Slug**: vite-invoke-zero-args
- **Created**: 2026-07-22
- **Source**: pasted text (error trace from browser)
- **Verdict**: valid
- **Severity**: medium

## Report (verbatim or summarized)

Accessing `http://localhost:8000/demo/operator/dashboard` throws:

```
ArgumentCountError
vendor/laravel/framework/src/Illuminate/Foundation/Vite.php:384
Too few arguments to function Illuminate\Foundation\Vite::__invoke(), 0 passed and at least 1 expected
```

Stack trace points to line 72 of `resources/views/screens/operator/dashboard.blade.php`, which contains:

```javascript
// Chart.js loaded via @vite, init inline
```

## Symptom

The operator dashboard crashes with a 500 error. Blade compiles the `@vite` token inside a JavaScript `//` comment as if it were a real Blade directive, producing `app('Illuminate\Foundation\Vite')()` — a call to `Vite::__invoke()` with zero arguments. The only affected view is `screens/operator/dashboard.blade.php`; other views with similar comment patterns were either unaffected or don't exist.

## Reproduction

1. Run `composer run dev`
2. Open `http://localhost:8000/demo/operator/dashboard` in a browser
3. Observe 500 error with `ArgumentCountError: Too few arguments to function Illuminate\Foundation\Vite::__invoke()`

## Suspected Code Paths

- `resources/views/screens/operator/dashboard.blade.php:72` — JavaScript comment `// Chart.js loaded via @vite, init inline` is compiled as a real `@vite` directive
- `storage/framework/views/981ed4dad7527a88a9103fc799459708.php:155` — compiled view contains `<?php echo app('Illuminate\Foundation\Vite')(); ?>` (zero-arg `__invoke()` call)
- `vendor/laravel/framework/src/Illuminate/Foundation/Vite.php:384` — `Vite::__invoke($entryPoints, $buildDirectory)` requires at least `$entryPoints`

## Root Cause Hypothesis

**Confidence: High.** Blade's compiler processes `@directive` syntax everywhere in a template, including inside JavaScript comments and `//`-style single-line comments. The text `@vite` on line 72 is lexed as a Blade directive, but since it has no following argument (no parentheses or string), the compiler emits a bare call with zero arguments. Laravel's `Vite::__invoke()` signature requires at least one argument (`string|array $entryPoints`), causing the `ArgumentCountError`.

The admin dashboard (`screens/admin/dashboard.blade.php`) was verified clean — it uses `@vite('...')` only in `@section('head')` with a proper argument and has no `@vite` in its `@push('scripts')` block.

## Proposed Remediation

**Preferred — Escape the `@` with `@@`:**

Change line 72 from:
```javascript
        // Chart.js loaded via @vite, init inline
```
to:
```javascript
        // Chart.js loaded via vite, init inline
```

Or, if preserving the literal `@vite` in the comment output is desired:
```javascript
        // Chart.js loaded via @@vite, init inline
```

Blade's `@@` is the escape sequence that produces a literal `@` in the rendered output. Either approach prevents Blade from treating it as a directive.

**After the fix:** run `php artisan view:clear` to purge and regenerate cached views.

**Files likely to change:**
- `resources/views/screens/operator/dashboard.blade.php` — line 72

**Tests to add or update:**
- HTTP test: `GET /demo/operator/dashboard` should return 200

## Risks & Considerations

- The bug is confined to a single file, single line. No other views have bare `@vite` inside `<script>` tags.
- If future views copy the pattern from this file, the same bug will recur. Consider a linter or convention guide.
- The fix is trivial — one character or word change.

## Open Questions

- None. Root cause is fully understood and reproducible.

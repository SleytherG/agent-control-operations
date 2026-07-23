# Bug Assessment: Undefined variable $slot in guest layout

- **Slug**: slot-undefined-guest-layout
- **Created**: 2026-07-22
- **Source**: pasted text (error trace from `composer run dev`)
- **Verdict**: valid
- **Severity**: high

## Report (verbatim or summarized)

Accessing `http://localhost:8000/demo/login` throws:

```
ErrorException
resources/views/layouts/guest.blade.php:14
Undefined variable $slot
```

Stack trace points at line 14 of `guest.blade.php`:

```blade
14        {{ $slot }}
```

The route `/demo/login` is served by `DemoAuthController::login()`, which returns `view('screens.auth.login', [...])`.

## Symptom

The `/demo/login` page crashes with a 500 error because the guest layout uses `{{ $slot }}` (Blade component syntax), but child views extend it via `@extends('layouts.guest')` (template inheritance). When using `@extends`, Blade never populates the `$slot` variable — it is only available when the layout is rendered as an anonymous Blade component (`<x-guest-layout>`). The same mismatch affects `authenticated.blade.php`, which uses `{{ $slot }}` but is extended by 30+ views across the application.

## Reproduction

1. Run `composer run dev`
2. Open `http://localhost:8000/demo/login` in a browser
3. Observe the 500 error and "Undefined variable $slot" exception
4. (Same error would occur on any page whose view extends `layouts.authenticated` — those routes may not have been exercised yet)

## Suspected Code Paths

- `resources/views/layouts/guest.blade.php:14` — uses `{{ $slot }}` but has no `@yield` fallback
- `resources/views/layouts/authenticated.blade.php:19` — same `{{ $slot }}` pattern, same root issue
- `resources/views/screens/auth/login.blade.php:1` — uses `@extends('layouts.guest')` instead of `<x-guest-layout>`
- `resources/views/screens/auth/expiry-modal.blade.php:1` — uses `@extends('layouts.guest')` instead of `<x-guest-layout>`
- `app/Http/Controllers/Demo/DemoAuthController.php:12` — returns `view('screens.auth.login', ...)`, which triggers the path above
- 30+ views across `screens/`, `daily-closing/`, `operations/`, `reporting/`, `identity-access/` — all use `@extends('layouts.authenticated')` with a layout that uses `{{ $slot }}`

## Root Cause Hypothesis

**Confidence: High.** The layouts (`guest.blade.php` and `authenticated.blade.php`) were designed as Blade components (using `{{ $slot }}`, `$title` prop, `@stack`), but the child views were written using the older `@extends` template inheritance pattern. No `@yield` directives exist in the layouts to bridge the two paradigms. Additionally, no `<x-guest-layout>` or `<x-authenticated-layout>` tag is used anywhere in the codebase. The controller never passes `$slot` as a view variable, and even if it did, the child content would not be captured because `@extends` requires `@section`/`@yield` pairs.

## Proposed Remediation

**Preferred — Convert layouts from component-style to `@yield`/`@section` (template inheritance):**

1. In `guest.blade.php` and `authenticated.blade.php`: Replace `{{ $slot }}` with `@yield('content')`.
2. In all child views that extend these layouts: Wrap the main body content in `@section('content') ... @endsection`.

This is the lower-risk approach because it preserves the existing `@extends` calls in 30+ views and avoids restructuring the entire view layer. The `@stack('head')` and `@stack('scripts')` calls in the layouts are already compatible with both patterns and require no changes.

**Alternatives:**

- **Convert child views to Blade component syntax**: Remove `@extends` from child views and wrap content in `<x-guest-layout>` / `<x-authenticated-layout>` tags. This aligns with the layouts' current design but requires touching every child view and renaming/rethinking how props like `$title` are passed. Higher effort, same outcome.

**Files likely to change:**

- `resources/views/layouts/guest.blade.php` — replace `{{ $slot }}` with `@yield('content')`
- `resources/views/layouts/authenticated.blade.php` — replace `{{ $slot }}` with `@yield('content')`
- `resources/views/screens/auth/login.blade.php` — wrap content in `@section('content')`
- `resources/views/screens/auth/expiry-modal.blade.php` — wrap content in `@section('content')`
- 30+ views under `screens/`, `daily-closing/`, `operations/`, `reporting/`, `identity-access/` — wrap content in `@section('content')`

**Tests to add or update:**

- Browser/intégration test for `GET /demo/login` asserting a 200 response
- Browser/intégration test for `GET /demo/login?state=expired` asserting the expiry modal renders
- Smoke test for at least one authenticated route to catch `authenticated.blade.php` slot errors

## Risks & Considerations

- The bug blocks **all** guest-facing auth pages (login, expiry-modal) and potentially all authenticated pages — any user attempting to access the app will hit a 500 error. This renders the app unusable.
- If only `guest.blade.php` is fixed but `authenticated.blade.php` is left unchanged, the app will appear to work for login but crash as soon as a user accesses any authenticated screen.
- The `@section('content')` wrapper must be placed correctly in each child view — wrapping the wrong content or nesting inside Blade directives could break conditional rendering.
- No database migration or config change needed; purely a view-layer fix.

## Open Questions

- Were these layouts auto-generated by a scaffold tool (e.g., Laravel Breeze, Jetstream) that was only partially applied? The layout files look like Jetstream-style component layouts but the child views look hand-written. Understanding this would help prevent recurrence.

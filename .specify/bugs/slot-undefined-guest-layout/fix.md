# Bug Fix: Undefined variable $slot in guest layout

- **Slug**: slot-undefined-guest-layout
- **Fixed**: 2026-07-22
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Converted both layouts (`guest.blade.php`, `authenticated.blade.php`) from Blade component-style (`{{ $slot }}`) to template inheritance (`@yield('content')`), then wrapped body content in `@section('content')` for the 7 child views that lacked it. The 29 authenticated child views that already had `@section('content')` required no changes.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `resources/views/layouts/guest.blade.php` | modified | `{{ $slot }}` → `@yield('content')` |
| `resources/views/layouts/authenticated.blade.php` | modified | `{{ $slot }}` → `@yield('content')` |
| `resources/views/screens/auth/login.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |
| `resources/views/screens/auth/expiry-modal.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |
| `resources/views/screens/admin/dashboard.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |
| `resources/views/screens/operator/dashboard.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |
| `resources/views/screens/operator/register.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |
| `resources/views/screens/operator/history.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |
| `resources/views/screens/daily-closing/show.blade.php` | modified | Added `@section('content')` / `@endsection` wrapper |

## Diff Highlights

**Layout change** (identical pattern in both layouts):
```blade
- {{ $slot }}
+ @yield('content')
```

**Child view wrapper** (7 files, same pattern):
```blade
 @extends('layouts.guest')
+
+@section('content')
 <div class="login-card">
     ...
 </div>
+@endsection
```

## Tests Added or Updated

No automated tests exist in the project for view rendering. Manual verification confirms:

- `GET /demo/login` → 200 (was 500)
- `GET /demo/login?state=expired` → 200 (was 500)

## Local Verification

- Commands run: `curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/demo/login` → `200`
- Commands run: `curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/demo/login?state=expired"` → `200`
- 29 of 36 authenticated child views already had `@section('content')` wrappers — no changes needed for those.

## Deviations from Assessment

None. The fix followed the assessment's preferred remediation exactly.

## Follow-ups

- Add a browser/integration test for `GET /demo/login` asserting 200 response and correct page title.
- Run a smoke test on at least one authenticated route to confirm `authenticated.blade.php` renders correctly with `@yield('content')`.
- Consider standardizing the view layer: either commit fully to `@extends`/`@yield`/`@section` or migrate to Blade components (`<x-guest-layout>`, `<x-authenticated-layout>`). The current mixed state caused this bug.

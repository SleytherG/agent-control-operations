# Bug Fix: SessionEvent `created_at` silently dropped during mass assignment

- **Slug**: session-events-fillable-missing-created-at
- **Fixed**: 2026-07-22
- **Assessment**: ./assessment.md
- **Status**: applied

## Summary

Added `'created_at'` to the `$fillable` array in `SessionEvent` model so that Eloquent's mass-assignment filter no longer drops it from `SessionEvent::create()` calls. All callers already pass `'created_at' => $now`; the attribute was simply being stripped silently.

## Changes

| File | Change | Notes |
|------|--------|-------|
| `app/Modules/IdentityAccess/Models/SessionEvent.php` | modified | Added `'created_at'` to `$fillable` |
| `tests/Feature/IdentityAccess/LoginViewTest.php` | modified | Removed `markTestSkipped` for `test_login_with_valid_credentials_redirects_to_home` |
| `tests/Feature/IdentityAccess/SessionEventTest.php` | added | Unit test asserting `created_at` is persisted via `SessionEvent::create()` |

## Diff Highlights

```php
// app/Modules/IdentityAccess/Models/SessionEvent.php
protected $fillable = [
-   'auth_session_id', 'user_id', 'type', 'occurred_at', 'context',
+   'auth_session_id', 'user_id', 'type', 'occurred_at', 'context', 'created_at',
];
```

```php
// tests/Feature/IdentityAccess/LoginViewTest.php
-        $this->markTestSkipped('Requires MySQL for session_events.created_at auto-population.');
-
         User::factory()->create([
```

## Tests Added or Updated

- `tests/Feature/IdentityAccess/SessionEventTest.php::test_session_event_create_includes_created_at` — creates a `SessionEvent` via `SessionEvent::create(['created_at' => $now, ...])`, fetches it back, and asserts `created_at` was persisted as a valid datetime string.
- `tests/Feature/IdentityAccess/LoginViewTest.php::test_login_with_valid_credentials_redirects_to_home` — unskipped. The original skip reason referenced this exact bug.

## Local Verification

- `php artisan test --filter="SessionEventTest"` → **1 passed** (3 assertions)
- The login flow tests (`LoginViewTest::test_login_with_valid_credentials_redirects_to_home`, `LoginWithUsernameTest::test_login_with_valid_username_creates_session_and_redirects`) now surface a different pre-existing bug (`AuthCookieService::withAuthCookies()` type-hint expects `Response` but receives `RedirectResponse` from `LoginController.php:80`). This is a separate issue, not in scope.

## Deviations from Assessment

- The assessment recommended unskipping `LoginViewTest.php:29` and running it against MySQL. The test was unskipped, but on SQLite (the test suite's default DB) it now fails with an unrelated `TypeError` in `AuthCookieService::withAuthCookies()`. The original `created_at` bug is confirmed fixed because the request reaches `LoginController.php:80` (past `SessionEvent::create()`) instead of failing at `StartAuthSession.php:58`.
- The new `SessionEventTest` uses a regex datetime assertion instead of a strict equality comparison because `created_at` is not in the model's `$casts` array (it returns as a raw string).

## Follow-ups

- Fix the `AuthCookieService::withAuthCookies()` type-hint to accept `RedirectResponse` (or update `LoginController` to wrap the redirect in a `Response`). This is the next blocker for the login flow tests.
- Consider adding `'created_at' => 'datetime'` to `SessionEvent::$casts` for consistency with `occurred_at`, though this is cosmetic since `$timestamps = false` means Eloquent won't manage it.

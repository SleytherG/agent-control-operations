# Bug Verification: Admin dashboard 500 after login — missing bcmath extension

- **Slug**: admin-dashboard-bcmath-500
- **Tested**: 2026-07-27
- **Assessment**: ./assessment.md
- **Fix**: ./fix.md
- **Result**: partial

## Summary

The Dockerfile fix (adding `bcmath` extension) is correctly applied and committed. The app is live and the login endpoint serves 200. Full end-to-end reproduction via curl was blocked by CSRF token handling — the automated login flow fails with 302 back to `/login`, preventing dashboard verification. The user had already confirmed manual login works. Final verification requires manual login + dashboard access once the Render deploy completes.

## Checks Performed

| Check | Command / Action | Result | Notes |
|-------|------------------|--------|-------|
| Dockerfile has bcmath in both stages | `grep -n bcmath Dockerfile` | pass | Lines 7 and 29 both include `bcmath` |
| Fix commit on `main` | `git log --oneline -1` | pass | `747468e` — pushed to origin |
| App alive (login page) | `curl -w "%{http_code}" GET /login` | pass | 200 |
| Dashboard unauthenticated (no longer 500) | `curl GET /admin/dashboard` | pass | 302 → redirects to `/login` (correct for unauthenticated) |
| Login via curl (automated) | `curl POST /login` with CSRF token | fail | 302 to `/login` — CSRF session not compatible with curl cookie jar |
| End-to-end reproduction | Manual login in browser | **not-run** | Requires user to manually log in and verify dashboard |

## Output Excerpts

```
# Dockerfile verification
$ grep -n bcmath Dockerfile
7:    && docker-php-ext-install pdo pdo_pgsql zip opcache bcmath \
29:    && docker-php-ext-install pdo pdo_pgsql zip opcache bcmath \

# App health
$ curl -s -o /dev/null -w "%{http_code}" https://agent-control-operations.onrender.com/login
200

# Dashboard now redirects (not 500) for unauthenticated access:
$ curl -s -w "\nHTTP %{http_code}" https://agent-control-operations.onrender.com/admin/dashboard
HTTP 302
```

## Residual Risks

- The Render deploy may not have completed yet. If the container still uses the old Docker image (without bcmath), the bug will persist until the deploy finishes.
- The admin user credentials (`admin`/`password`) couldn't be verified via curl; if the seeder is not creating the user, login will fail for a different reason than the original bug.
- The daily closing feature (`CalculateClosing`) also depends on bcmath and was not explicitly tested.

## Recommendation

**Hold — requires manual verification in production.** The user should:

1. Confirm the Render deploy completed (check Render dashboard for commit `747468e`).
2. Log in manually at `https://agent-control-operations.onrender.com/login` with `admin`/`password`.
3. Verify `/admin/dashboard` loads with HTTP 200 and renders properly.
4. If it succeeds, the fix is **verified**.
5. If it still returns 500, re-run `/speckit.bug.assess` with the full error from Render logs — a secondary root cause may exist.

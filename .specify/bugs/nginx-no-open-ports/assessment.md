# Bug Assessment: Nginx No Open Ports on Render

- **Slug**: nginx-no-open-ports
- **Created**: 2026-07-26
- **Source**: pasted text (Render deployment logs)
- **Verdict**: valid
- **Severity**: critical

## Report (verbatim)

```
==> No open ports detected on 0.0.0.0, continuing to scan...
```

Nginx and PHP-FPM start without errors, but Render detects no listening port because
our Nginx config was deleted during the Docker build step.

## Symptom

Container starts, Nginx and PHP-FPM initialize successfully, but Render reports "No open
ports detected" and the app is unreachable. The web page doesn't load.

## Root Cause Hypothesis

**Confidence: high.**

The `RUN` step in the Dockerfile removes both the pre-existing default Nginx config AND
our own config:

```dockerfile
RUN chmod +x /entrypoint.sh \
    && rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf \
    ...
```

Step `#26` copies our config to `/etc/nginx/conf.d/default.conf`, but step `#30` (RUN)
immediately deletes it. Nginx starts with no `server` block binding to port 80, so Render's
port scanner never detects an open port.

## Proposed Remediation

Remove `/etc/nginx/conf.d/default.conf` from the delete command. Only delete
`/etc/nginx/sites-enabled/default` which is the pre-existing one from the `php:8.4-fpm`
base image.

**File**: `Dockerfile:38` — change:
```dockerfile
&& rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf \
```
to:
```dockerfile
&& rm -f /etc/nginx/sites-enabled/default \
```

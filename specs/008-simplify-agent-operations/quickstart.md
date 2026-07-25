# Quickstart: Operaciones Generales por Agente

**Feature**: 008-simplify-agent-operations | **Date**: 2026-07-23

## Prerequisites

- PHP 8.3, Composer, Node.js (solo para build), MySQL 8.0 / MariaDB
- Base de datos con migrations ejecutadas (`php artisan migrate`)
- Opcional: datos seeded (`php artisan db:seed`)
- `.env` configurado con `JWT_SIGNING_KEY`, `REFRESH_PEPPER`, credenciales DB

## Quick Validation

### 1. Build Frontend

```bash
npm ci && npm run build
```

### 2. Run Test Suite

```bash
php artisan test
```

Expected: IdentityAccess tests (~30) pass; tests dependientes de Bank/Store/BankAgent fallan (expected until migration complete).

### 3. Smoke Test: Admin Flow

```bash
php artisan serve
```

1. `GET /login` — Login page renders with Stitch design, Spanish labels, no bank references.
2. `POST /login` with `admin` / `password` — Redirects to `/home` with JWT cookies.
3. `GET /home` — Shows "Bienvenido, admin", session countdown visible in topbar.
4. `GET /admin/dashboard` — Admin dashboard renders without bank/store filters.
5. `GET /admin/agents` — Agent list renders (empty or with migrated data).
6. `POST /admin/agents` — Create "Agente Centro" with code `AG-CENTRO`, city "Lima".
7. `GET /admin/users` — Operator list renders.
8. `POST /admin/users/{user}/assignments` — Assign operator to agent.
9. `GET /my-agents` — Operator sees assigned agent.

### 4. Smoke Test: Operator Flow

1. Login as operator.
2. Agent auto-selected from active assignment; agent name visible in layout.
3. `GET /operations/create` — Registration form with agent context, type selector, amount field (no bank dropdown).
4. `POST /operations` with valid data — Operation created, internal code generated, confirmation shown.
5. `GET /operations` — History shows only own operations, summary metrics correct.
6. `GET /dashboard` — Operator dashboard with own metrics, no other operators' data.

### 5. Smoke Test: Daily Funds

1. Admin opens day: registers opening cash and digital for an agent.
2. Operator registers several operations throughout the day.
3. Operator prepares closing: enters actual cash and digital balances.
4. System calculates expected vs actual, shows differences.
5. Operator presents closing (BORRADOR → PRESENTADO).
6. Admin confirms closing with differences warning + motivo.
7. Closing status changes to CONFIRMADO, new operations blocked for that date.

### 6. Visual Validation

For each of the 7 mandatory screens:
1. Open in Microsoft Edge at 1440x900.
2. Capture screenshot.
3. Compare against `docs/design/stitch/v1/{screen}/screen.png`.
4. Document deviations in DEVIATIONS.md.
5. Repeat at 1280x720, 768x1024, 375x812.
6. Verify: no bank/tienda labels, "Agente" terminology, "Efectivo"/"Saldo digital" labels.

### 7. Migration Validation

```bash
# Before migration
php artisan migrate:status
mysqldump control_operaciones > pre_migration_backup.sql

# Run migration
php artisan migrate

# Verify mapping
php artisan tinker
> DB::table('_migration_map')->count(); // Should match stores + bank_agents
> DB::table('agents')->count();

# Verify operation integrity
> DB::table('operations')->whereNull('agent_id')->count(); // Should be 0
> DB::table('operations')->count(); // Should match pre-migration count

# Rollback
php artisan migrate:rollback --step=N
mysql control_operaciones < pre_migration_backup.sql

# Verify rollback integrity
php artisan test --filter="MigrationIntegrity"
```

### 8. Demo Flow (22 steps)

Execute the Mandatory Demonstration Flow from spec.md §Mandatory Demonstration Flow with real test data. All 22 steps must complete without errors.

# Data Model: Registro de Operaciones

## Conventions

Mismas que 001 y 002: InnoDB, `utf8mb4`, PK `BIGINT UNSIGNED`, timestamps `DATETIME(6)` UTC, `America/Lima` display, montos `DECIMAL(18,2)`, FK `RESTRICT`.

## New Tables

### operation_types

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| bank_id | BIGINT UNSIGNED | nullable FK banks RESTRICT; null = tipo general |
| name | VARCHAR(160) | required |
| description | VARCHAR(500) | nullable |
| cash_direction | VARCHAR(20) | ENTRADA, SALIDA, NEUTRA, POR_CONFIRMAR |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(bank_id, name)` donde bank_id no es null; índice único funcional para `(organization_id, name)` donde `bank_id IS NULL`. INDEX `(organization_id, is_active)`.

### operations

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| store_id | BIGINT UNSIGNED | FK stores RESTRICT |
| bank_agent_id | BIGINT UNSIGNED | FK bank_agents RESTRICT |
| operation_type_id | BIGINT UNSIGNED | FK operation_types RESTRICT |
| user_id | BIGINT UNSIGNED | FK users RESTRICT (registrador) |
| amount | DECIMAL(18,2) | required, > 0 |
| currency | CHAR(3) | default 'PEN' |
| effective_at | DATETIME(6) | required |
| recorded_at | DATETIME(6) | required |
| status | VARCHAR(20) | ACTIVE, ANNULLED |
| reference | VARCHAR(100) | nullable |
| observation | VARCHAR(500) | nullable |
| annulled_by | BIGINT UNSIGNED | nullable FK users RESTRICT |
| annulled_at | DATETIME(6) | nullable |
| annulment_reason | VARCHAR(500) | nullable; required when ANNULLED |
| idempotency_key | CHAR(64) | UNIQUE, required |
| created_at/updated_at | DATETIME(6) | required |

CHECK `amount > 0`. Composite FK `(bank_agent_id, store_id)` REFERENCES `bank_agents(id, store_id)`.

Índices: `(user_id, effective_at)`, `(bank_agent_id, effective_at)`, `(store_id, effective_at)`, `(operation_type_id, effective_at)`, `(status, effective_at)`, `(organization_id, effective_at)`.

State: `ACTIVE` → `ANNULLED` (terminal). No `DELETED`.

## Migration Sequencing

1. `operation_types` (000015)
2. `operations` (000016)
3. Seed de tipos de operación

Dependencias: `organization`, `banks`, `bank_agents`, `stores`, `users` de 001/002.

## Config

`config/operations.php`:
- `retroactive_window_hours` (default 24)
- `annulment_window_hours` (default 24)
- `default_currency` (default 'PEN')

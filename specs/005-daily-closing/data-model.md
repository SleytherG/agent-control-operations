# Data Model: Cierre Operativo Diario

## New Tables

### daily_closures

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| store_id | BIGINT UNSIGNED | FK stores RESTRICT |
| bank_agent_id | BIGINT UNSIGNED | FK bank_agents RESTRICT |
| business_date | DATE | required |
| status | VARCHAR(20) | ACTIVO, CONFIRMADO, REABIERTO |
| operation_count | INT UNSIGNED | default 0 |
| gross_amount | DECIMAL(18,2) | default 0 |
| cash_in | DECIMAL(18,2) | default 0 |
| cash_out | DECIMAL(18,2) | default 0 |
| net_movement | DECIMAL(18,2) | default 0 |
| has_pending_confirm | BOOLEAN | default false |
| confirmed_by | BIGINT UNSIGNED | nullable FK users RESTRICT |
| confirmed_at | DATETIME(6) | nullable |
| reopened_by | BIGINT UNSIGNED | nullable FK users RESTRICT |
| reopened_at | DATETIME(6) | nullable |
| reopen_reason | VARCHAR(500) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE partial: `(bank_agent_id, business_date)` WHERE `status = 'ACTIVO'`.
INDEX `(bank_agent_id, business_date)`, `(organization_id, business_date)`, `(status)`.

### daily_closure_operations

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| daily_closure_id | BIGINT UNSIGNED | FK daily_closures RESTRICT |
| operation_id | BIGINT UNSIGNED | FK operations RESTRICT, UNIQUE |
| created_at | DATETIME(6) | required |

UNIQUE `(operation_id)` — cada operación pertenece a un solo cierre.
INDEX `(daily_closure_id)`.

### State Transitions

```
ACTIVO ──confirm()──▶ CONFIRMADO
CONFIRMADO ──reopen()──▶ REABIERTO
REABIERTO ──confirm()──▶ CONFIRMADO
```

Sin estado terminal. Sin eliminación física.

## Migration Sequencing

1. `daily_closures` (000017)
2. `daily_closure_operations` (000018)

Dependencias: 001, 002 (bank_agents, users), 003 (operations).

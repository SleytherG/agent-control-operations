# Data Model: Estructura Operacional

## Conventions

- Mismas que 001-auth-session: InnoDB, `utf8mb4`, PK `BIGINT UNSIGNED`, timestamps `DATETIME(6)` UTC, `America/Lima` display.
- FK con `RESTRICT`. Desactivación lógica con `is_active` y `deactivated_at`.

## New Tables

### regions

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| name | VARCHAR(160) | required |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(organization_id, name)`. INDEX `(organization_id, is_active)`.

### provinces

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| region_id | BIGINT UNSIGNED | FK regions RESTRICT |
| name | VARCHAR(160) | required |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(region_id, name)`. INDEX `(organization_id, is_active)`, `(region_id, is_active)`.

### districts

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| province_id | BIGINT UNSIGNED | FK provinces RESTRICT |
| name | VARCHAR(160) | required |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(province_id, name)`. INDEX `(organization_id, is_active)`, `(province_id, is_active)`.

### stores

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| district_id | BIGINT UNSIGNED | FK districts RESTRICT |
| code | VARCHAR(80) | required |
| name | VARCHAR(200) | required |
| address | VARCHAR(500) | nullable |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(organization_id, code)`. INDEX `(district_id, is_active)`, `(organization_id, is_active)`.

### banks

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| code | VARCHAR(20) | required |
| name | VARCHAR(200) | required |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(organization_id, code)`. INDEX `(organization_id, is_active)`.

### bank_agents

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| organization_id | BIGINT UNSIGNED | FK organizations RESTRICT |
| store_id | BIGINT UNSIGNED | FK stores RESTRICT |
| bank_id | BIGINT UNSIGNED | FK banks RESTRICT |
| code | VARCHAR(80) | required, unique per org |
| terminal_code | VARCHAR(40) | nullable |
| is_active | BOOLEAN | default true |
| deactivated_at | DATETIME(6) | nullable |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(organization_id, code)`. INDEX `(store_id, is_active)`, `(bank_id, is_active)`, `(organization_id, is_active)`.

### user_bank_agent_assignments

| Column | Type | Rules |
|--------|------|-------|
| id | BIGINT UNSIGNED | PK |
| user_id | BIGINT UNSIGNED | FK users RESTRICT |
| bank_agent_id | BIGINT UNSIGNED | FK bank_agents RESTRICT |
| assigned_by | BIGINT UNSIGNED | FK users RESTRICT |
| assigned_at | DATETIME(6) | required |
| unassigned_at | DATETIME(6) | nullable |
| is_active | BOOLEAN | default true |
| created_at/updated_at | DATETIME(6) | required |

UNIQUE `(user_id, bank_agent_id)` where `is_active = true` (partial/functional index). INDEX `(user_id, is_active)`, `(bank_agent_id, is_active)`.

State: `ACTIVE (is_active=true, unassigned_at=null)` → `INACTIVE (is_active=false, unassigned_at=now())`.

### users (column addition)

| Column | Type | Rules |
|--------|------|-------|
| password_changed_at | DATETIME(6) | nullable; null means must change on next login |

## Migration Sequencing

1. `regions`
2. `provinces`
3. `districts`
4. `stores`
5. `banks`
6. `bank_agents`
7. `user_bank_agent_assignments`
8. `add_password_changed_at_to_users_table`

Seed data: region Lima, provincia Lima, distritos (Cercado, Miraflores, San Isidro); bancos BCP, Interbank, BBVA.

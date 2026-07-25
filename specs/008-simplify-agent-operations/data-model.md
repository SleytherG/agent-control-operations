# Data Model: Operaciones Generales por Agente

**Feature**: 008-simplify-agent-operations | **Date**: 2026-07-23

## Entity Relationship Diagram (Conceptual)

```
Organization 1──N Agent
Organization 1──N User
Organization 1──N OperationType
Organization 1──N Operation
Organization 1──N DailyClosure
Organization 1──N AuditLog

Agent 1──N UserAgentAssignment N──1 User
Agent 1──N Operation
Agent 1──N DailyClosure

User 1──N AuthSession
User 1──N Operation (as operator)
User 1──N UserAgentAssignment

OperationType 1──N Operation

Operation N──M DailyClosure (via daily_closure_operations)

AuthSession 1──N AuthRefreshToken
AuthSession 1──N SessionEvent
```

## Entities

### Agent (new)
**Table**: `agents`

| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint unsigned | PK, auto-increment | |
| organization_id | bigint unsigned | FK→organizations, NOT NULL | |
| code | varchar(80) | NOT NULL, unique(org_id, code) | Código interno visible |
| name | varchar(200) | NOT NULL | Nombre visible del agente |
| city | varchar(160) | NOT NULL | Ciudad |
| region | varchar(160) | nullable | Región/departamento |
| province | varchar(160) | nullable | Provincia |
| district | varchar(160) | nullable | Distrito |
| address | varchar(500) | nullable | Dirección física |
| description | text | nullable | Descripción/notas internas |
| is_active | boolean | NOT NULL, default true | |
| deactivated_at | datetime | nullable | |
| created_at | datetime | NOT NULL | |
| updated_at | datetime | NOT NULL | |

**State transitions**: Active ↔ Inactive (via admin action, audited)

### UserAgentAssignment (replaces UserBankAgentAssignment)
**Table**: `user_agent_assignments`

| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint unsigned | PK, auto-increment | |
| user_id | bigint unsigned | FK→users, NOT NULL | Operador asignado |
| agent_id | bigint unsigned | FK→agents, NOT NULL | Agente asignado |
| assigned_by | bigint unsigned | FK→users, NOT NULL | Admin que asignó |
| starts_at | datetime | NOT NULL | Inicio de vigencia |
| ends_at | datetime | nullable | Fin de vigencia |
| is_active | boolean | NOT NULL, default true | |
| created_at | datetime | NOT NULL | |
| updated_at | datetime | NOT NULL | |

**Indexes**: (user_id, is_active), (agent_id, is_active)

**State transitions**: Active → Inactive (via admin finalization, audited)

### OperationType (modified)
**Table**: `operation_types`

| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint unsigned | PK, auto-increment | |
| organization_id | bigint unsigned | FK→organizations, NOT NULL | |
| name | varchar(160) | NOT NULL, unique(org_id, name) | |
| description | varchar(500) | nullable | |
| cash_multiplier | tinyint | NOT NULL, default 0 | -1 salida, 0 neutro, +1 entrada |
| digital_multiplier | tinyint | NOT NULL, default 0 | -1 salida, 0 neutro, +1 entrada |
| sort_order | int unsigned | default 0 | Orden de presentación |
| is_active | boolean | NOT NULL, default true | |
| deactivated_at | datetime | nullable | |
| created_at | datetime | NOT NULL | |
| updated_at | datetime | NOT NULL | |

**Removed**: `bank_id` (FK→banks), `cash_direction` (enum)

**Seed data**: Tipos generales sin bank_id: Depósito, Retiro, Transferencia, Pago de servicio, Recarga, Cobro, Envío, Otro.

### Operation (modified)
**Table**: `operations`

| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint unsigned | PK, auto-increment | |
| organization_id | bigint unsigned | FK→organizations, NOT NULL | |
| internal_code | varchar(30) | NOT NULL, unique | Formato OP-YYYYMMDD-NNNN |
| agent_id | bigint unsigned | FK→agents, NOT NULL | Replaces store_id + bank_agent_id |
| operator_user_id | bigint unsigned | FK→users, NOT NULL | Operador autenticado |
| operation_type_id | bigint unsigned | FK→operation_types, NOT NULL | |
| customer_name | varchar(200) | nullable | Referencia opcional del cliente |
| amount | decimal(18,2) | NOT NULL | |
| currency | char(3) | NOT NULL, default 'PEN' | |
| occurred_at | datetime | NOT NULL | Fecha/hora efectiva (servidor) |
| recorded_at | datetime | NOT NULL | Fecha/hora de registro |
| notes | varchar(500) | nullable | Observación |
| cash_delta | decimal(18,2) | NOT NULL, default 0 | Efecto sobre efectivo |
| digital_delta | decimal(18,2) | NOT NULL, default 0 | Efecto sobre saldo digital |
| status | varchar(20) | NOT NULL, default 'ACTIVE' | ACTIVE / ANNULLED |
| voided_at | datetime | nullable | |
| voided_by | bigint unsigned | nullable, FK→users | |
| void_reason | varchar(500) | nullable | |
| idempotency_key | char(64) | NOT NULL, unique | |
| created_at | datetime | NOT NULL | |
| updated_at | datetime | NOT NULL | |

**Removed**: `store_id`, `bank_agent_id`, `reference`, `observation`, `annulled_by`, `annulled_at`, `annulment_reason`
**Renamed**: `user_id` → `operator_user_id`, `observation` → `notes`, `annulled_*` → `voided_*`
**Added**: `internal_code`, `customer_name`, `cash_delta`, `digital_delta`

**Indexes**: (agent_id, occurred_at), (operator_user_id, occurred_at), (operation_type_id, occurred_at), (status, occurred_at), (org_id, occurred_at), unique(internal_code), unique(idempotency_key)

### DailyClosure (modified)
**Table**: `daily_closures`

| Column | Type | Constraints | Notes |
|--------|------|------------|-------|
| id | bigint unsigned | PK, auto-increment | |
| organization_id | bigint unsigned | FK→organizations, NOT NULL | |
| agent_id | bigint unsigned | FK→agents, NOT NULL | Replaces store_id + bank_agent_id |
| business_date | date | NOT NULL | |
| status | varchar(20) | NOT NULL, default 'ABIERTO' | ABIERTO/BORRADOR/PRESENTADO/CONFIRMADO/REABIERTO |
| operation_count | int unsigned | default 0 | |
| gross_operated_amount | decimal(18,2) | default 0 | Suma valores absolutos |
| total_cash_in | decimal(18,2) | default 0 | |
| total_cash_out | decimal(18,2) | default 0 | |
| total_digital_in | decimal(18,2) | default 0 | |
| total_digital_out | decimal(18,2) | default 0 | |
| opening_cash | decimal(18,2) | NOT NULL, default 0 | Efectivo inicial |
| opening_digital | decimal(18,2) | NOT NULL, default 0 | Saldo digital inicial |
| expected_closing_cash | decimal(18,2) | default 0 | |
| expected_closing_digital | decimal(18,2) | default 0 | |
| actual_closing_cash | decimal(18,2) | nullable | |
| actual_closing_digital | decimal(18,2) | nullable | |
| cash_difference | decimal(18,2) | nullable | real - esperado |
| digital_difference | decimal(18,2) | nullable | real - esperado |
| has_inconsistencies | boolean | default false | Tipos sin efectos o datos faltantes |
| opened_by | bigint unsigned | FK→users | Quién registró apertura |
| submitted_by | bigint unsigned | nullable, FK→users | Quién presentó cierre |
| confirmed_by | bigint unsigned | nullable, FK→users | |
| reopened_by | bigint unsigned | nullable, FK→users | |
| opened_at | datetime | |
| submitted_at | datetime | nullable | |
| confirmed_at | datetime | nullable | |
| reopened_at | datetime | nullable | |
| reopen_reason | varchar(500) | nullable | |
| notes | varchar(500) | nullable | |
| created_at | datetime | NOT NULL | |
| updated_at | datetime | NOT NULL | |

**Removed**: `store_id`, `bank_agent_id`, `cash_in`, `cash_out`, `net_movement`, `has_pending_confirm`
**Added**: `total_cash_in`, `total_cash_out`, `total_digital_in`, `total_digital_out`, `opening_cash`, `opening_digital`, `expected_closing_cash`, `expected_closing_digital`, `actual_closing_cash`, `actual_closing_digital`, `cash_difference`, `digital_difference`, `has_inconsistencies`, `opened_by`, `submitted_by`, `opened_at`, `submitted_at`, `notes`

**Indexes**: unique(agent_id, business_date, status) WHERE status IN ('ABIERTO','BORRADOR'), (agent_id, business_date), (org_id, business_date)

**State transitions**:
```
                    ┌─→ CONFIRMED ←─┐
                    │       ↑        │
ABIERTO ← BORRADOR ←┤       │        │
                    │       ↓        │
                    └─→ PRESENTADO ──┘
                          ↑
                    REABIERTO
```
- Admin: ABIERTO/BORRADOR/PRESENTADO/REABIERTO → CONFIRMADO
- Admin: CONFIRMADO → REABIERTO (con motivo)
- Operator: BORRADOR → PRESENTADO

### DailyClosureOperation (conservado)
**Table**: `daily_closure_operations` — sin cambios estructurales. Se agrega snapshot de cash_delta y digital_delta de la operación al momento del cierre.

### Entities conservadas sin cambios

- **Organization**: Sin cambios
- **User**: Sin cambios (ya no depende de store/bank)
- **AuthSession, AuthRefreshToken, SessionEvent**: Sin cambios
- **AuditLog**: Sin cambios estructurales
- **Region, Province, District**: Sin cambios (referencia geográfica opcional del agente)

### Entities eliminadas

- **Bank**: Tabla `banks` — eliminada tras migración
- **Store**: Tabla `stores` — consolidada en agents
- **BankAgent**: Tabla `bank_agents` — consolidada en agents
- **UserBankAgentAssignment**: Tabla `user_bank_agent_assignments` — migrada a `user_agent_assignments`

### Tabla temporal de migración

**_migration_map**:
| Column | Type | Notes |
|--------|------|-------|
| old_table | varchar(50) | 'stores' o 'bank_agents' |
| old_id | bigint unsigned | ID en la tabla original |
| new_agent_id | bigint unsigned | ID en agents |
| notes | varchar(255) nullable | |
| created_at | datetime | |

Se elimina en la fase de limpieza final.

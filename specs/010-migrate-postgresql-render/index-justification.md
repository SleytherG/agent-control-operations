# PostgreSQL Index Justification Report

**Feature**: 010-migrate-postgresql-render | **Date**: 2026-07-25

Each index in the destination PostgreSQL schema is mapped to its purpose: constraint support
(FK/unique), query performance (EXPLAIN-backed), ordering, or filtering.

## Index Inventory

### organizations

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| organizations_pkey | PK | id | Uniqueness | Primary key |
| organizations_public_id_unique | UNIQUE | public_id | Uniqueness | UUID lookup |

### users

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| users_pkey | PK | id | Uniqueness | Primary key |
| users_public_id_unique | UNIQUE | public_id | Uniqueness | UUID lookup |
| users_organization_id_username_normalized_unique | UNIQUE | (organization_id, username_normalized) | Uniqueness | Login lookup by username within org |
| users_organization_id_email_normalized_unique | UNIQUE | (organization_id, email_normalized) | Uniqueness | Login lookup by email within org |
| users_organization_id_role_status_index | INDEX | (organization_id, role, status) | Filter | Operator listing filtered by org, role, status |

### auth_sessions

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| auth_sessions_pkey | PK | id | Uniqueness | Primary key |
| auth_sessions_public_id_unique | UNIQUE | public_id | Uniqueness | UUID lookup |
| auth_sessions_user_id_started_at_index | INDEX | (user_id, started_at) | Query | Session history ordered by time |
| auth_sessions_user_id_status_started_at_index | INDEX | (user_id, status, started_at) | Query/Filter | Find active sessions for user |
| auth_sessions_status_access_expires_at_index | INDEX | (status, access_expires_at) | Query | Find expired sessions for cleanup |
| auth_sessions_absolute_expires_at_index | INDEX | absolute_expires_at | Query | Absolute expiry cleanup |
| auth_sessions_password_reset_id_unique | UNIQUE | password_reset_id | Uniqueness | One session per password reset |

### auth_refresh_tokens

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| auth_refresh_tokens_pkey | PK | id | Uniqueness | Primary key |
| auth_refresh_tokens_token_hash_unique | UNIQUE | token_hash | Uniqueness | Token lookup by hash |
| auth_refresh_tokens_auth_session_id_generation_unique | UNIQUE | (auth_session_id, generation) | Uniqueness | One active token per session+gen |
| auth_refresh_tokens_auth_session_id_state_index | INDEX | (auth_session_id, state) | Filter | Find active/revoked tokens per session |
| auth_refresh_tokens_expires_at_index | INDEX | expires_at | Query | Expired token cleanup |

### session_events

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| session_events_pkey | PK | id | Uniqueness | Primary key |
| session_events_auth_session_id_occurred_at_index | INDEX | (auth_session_id, occurred_at) | Query | Events per session ordered by time |
| session_events_user_id_occurred_at_index | INDEX | (user_id, occurred_at) | Query | Events per user ordered by time |
| session_events_type_occurred_at_index | INDEX | (type, occurred_at) | Filter/Query | Events by type ordered by time |

### audit_logs

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| audit_logs_pkey | PK | id | Uniqueness | Primary key |
| audit_logs_entity_type_entity_id_occurred_at_index | INDEX | (entity_type, entity_id, occurred_at) | Query | Audit trail per entity |
| audit_logs_actor_user_id_occurred_at_index | INDEX | (actor_user_id, occurred_at) | Query | Audit actions by actor |
| audit_logs_organization_id_occurred_at_index | INDEX | (organization_id, occurred_at) | Query | Org-scoped audit history |
| audit_logs_action_occurred_at_index | INDEX | (action, occurred_at) | Query/Filter | Audit by action type |

### regions, provinces, districts

| Table | Index | Type | Columns | Purpose | Justification |
|-------|-------|------|---------|---------|---------------|
| regions | regions_organization_id_name_unique | UNIQUE | (organization_id, name) | Uniqueness | No duplicate region names per org |
| regions | regions_organization_id_is_active_index | INDEX | (organization_id, is_active) | Filter | List active regions |
| provinces | provinces_region_id_name_unique | UNIQUE | (region_id, name) | Uniqueness | No duplicate names per region |
| provinces | provinces_organization_id_is_active_index | INDEX | (organization_id, is_active) | Filter | List active per org |
| provinces | provinces_region_id_is_active_index | INDEX | (region_id, is_active) | Filter | List active per region |
| districts | districts_province_id_name_unique | UNIQUE | (province_id, name) | Uniqueness | No duplicate names per province |
| districts | districts_organization_id_is_active_index | INDEX | (organization_id, is_active) | Filter | List active per org |
| districts | districts_province_id_is_active_index | INDEX | (province_id, is_active) | Filter | List active per province |

### operation_types

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| operation_types_pkey | PK | id | Uniqueness | Primary key |
| operation_types_organization_id_name_unique | UNIQUE | (organization_id, name) | Uniqueness | No duplicate type names |
| operation_types_organization_id_is_active_index | INDEX | (organization_id, is_active) | Filter | List active types |

### operations

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| operations_pkey | PK | id | Uniqueness | Primary key |
| operations_idempotency_key_unique | UNIQUE | idempotency_key | Uniqueness | Duplicate prevention |
| operations_user_id_effective_at_index | INDEX | (user_id, effective_at) | Query | User operations ordered by date |
| operations_operation_type_id_effective_at_index | INDEX | (operation_type_id, effective_at) | Query | Operations by type and date |
| operations_status_effective_at_index | INDEX | (status, effective_at) | Filter/Query | Filter by status (ACTIVE, ANNULLED) |
| operations_organization_id_effective_at_index | INDEX | (organization_id, effective_at) | Query | Org-scoped operations by date |

> **Note**: `bank_agent_id_effective_at` and `store_id_effective_at` indexes were dropped in migration 000009 (removed as orphan cleanup).

### daily_closures

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| daily_closures_pkey | PK | id | Uniqueness | Primary key |
| daily_closures_organization_id_business_date_index | INDEX | (organization_id, business_date) | Query | Closures per org and date |
| daily_closures_status_index | INDEX | status | Filter | Filter by status (ACTIVO, CONFIRMADO, REABIERTO) |

> **Note**: `bank_agent_id_business_date_status` unique constraint and `bank_agent_id_business_date` index were dropped in migration 000009.

### daily_closure_operations

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| daily_closure_operations_pkey | PK | id | Uniqueness | Primary key |
| daily_closure_operations_operation_id_unique | UNIQUE | operation_id | Uniqueness | One operation per closure |
| daily_closure_operations_daily_closure_id_index | INDEX | daily_closure_id | Query | Operations for a specific closure |

### agents

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| agents_pkey | PK | id | Uniqueness | Primary key |
| agents_organization_id_code_unique | UNIQUE | (organization_id, code) | Uniqueness | Agent code lookup |
| agents_organization_id_is_active_index | INDEX | (organization_id, is_active) | Filter | List active agents |
| agents_city_is_active_index | INDEX | (city, is_active) | Filter | Filter agents by city |

### user_agent_assignments

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| user_agent_assignments_pkey | PK | id | Uniqueness | Primary key |
| user_agent_assignments_user_id_is_active_index | INDEX | (user_id, is_active) | Query | Active assignments for user |
| user_agent_assignments_agent_id_is_active_index | INDEX | (agent_id, is_active) | Query | Active assignments for agent |

### password_resets

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| password_resets_pkey | PK | id | Uniqueness | Primary key |
| password_resets_public_id_unique | UNIQUE | public_id | Uniqueness | UUID lookup |
| password_resets_user_id_status_issued_at_index | INDEX | (user_id, status, issued_at) | Query | Find pending resets per user |
| password_resets_organization_id_status_issued_at_index | INDEX | (organization_id, status, issued_at) | Query | Org-scoped reset audit |
| password_resets_expires_at_index | INDEX | expires_at | Query | Expired reset cleanup |
| password_resets_initiated_by_user_id_issued_at_index | INDEX | (initiated_by_user_id, issued_at) | Query | Reset initiator history |

### migrations

| Index | Type | Columns | Purpose | Justification |
|-------|------|---------|---------|---------------|
| migrations_pkey | PK | id | Uniqueness | Primary key (Laravel standard) |

## Summary

| Metric | Count |
|--------|-------|
| Tables with indexes | 17 |
| Primary keys | 17 |
| Unique constraints | 15 |
| Non-unique indexes | 39 |
| **Total indexes** | **71** |
| Orphan indexes removed (migration 000009) | 4 |

All indexes serve at least one of: FK enforcement, unique constraint, query performance,
filtering by status, or ordering by timestamp. No PostgreSQL-specific indexes (GIN, GiST, BRIN)
are required at this stage. The existing btree indexes cover all identified query patterns.

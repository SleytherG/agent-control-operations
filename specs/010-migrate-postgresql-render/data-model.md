# Data Model: Migración MySQL → PostgreSQL

**Feature**: 010-migrate-postgresql-render | **Date**: 2026-07-25

## Resumen

El modelo de datos funcional (entidades, relaciones, estados, reglas de negocio) no cambia.
Este documento describe exclusivamente los cambios de representación física necesarios para
que el esquema funcione correctamente sobre PostgreSQL.

## Tipo de estrategia

**Esquema limpio** (`migrate:fresh --seed`). No hay migración de datos desde MySQL. Las
migraciones versionadas de Laravel construyen el esquema directamente en PostgreSQL. Los
seeders controlados pueblan datos de desarrollo.

## Mapeo de Tipos MySQL → PostgreSQL

### Tabla: organizations

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno — Laravel automático |
| public_id | char(36) | UUID o VARCHAR(36) | Opcional: evaluar `uuid` type |
| name | varchar(160) | VARCHAR(160) | Ninguno |
| timezone | varchar(64) default 'America/Lima' | VARCHAR(64) DEFAULT 'America/Lima' | Ninguno |
| is_active | boolean default true | BOOLEAN DEFAULT true | Ninguno |
| deactivated_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

### Tabla: users

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno |
| public_id | char(36) | UUID/VARCHAR(36) | Opcional |
| organization_id | foreignId | BIGINT FK | Ninguno |
| username_normalized | varchar(100) | VARCHAR(100) | Ninguno |
| email_normalized | varchar(254) | VARCHAR(254) | Ninguno |
| password | varchar | VARCHAR | Ninguno |
| role | varchar(40) | VARCHAR(40) | Ninguno |
| status | varchar(20) default 'ACTIVE' | VARCHAR(20) DEFAULT 'ACTIVE' | Ninguno |
| password_changed_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| deactivated_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| deactivated_by | foreignId nullable | BIGINT FK | Ninguno |
| deactivation_reason | varchar(500) nullable | VARCHAR(500) | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

**Índices**: UNIQUE (organization_id, username_normalized), UNIQUE (organization_id, email_normalized). PostgreSQL maneja NULL en unique constraints igual que MySQL.

### Tabla: auth_sessions

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno |
| public_id | char(36) | UUID/VARCHAR(36) | Opcional |
| user_id | foreignId | BIGINT FK | Ninguno |
| password_reset_id | foreignId nullable unique | BIGINT FK UNIQUE | Ninguno |
| status | varchar(20) default 'ACTIVE' | VARCHAR(20) DEFAULT 'ACTIVE' | Ninguno |
| started_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| access_expires_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| absolute_expires_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| last_refreshed_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| ended_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| end_reason | varchar(40) nullable | VARCHAR(40) | Ninguno |
| **ip_hash** | **binary(32) nullable** | **BYTEA** | **Laravel binary() → bytea automático** |
| user_agent_summary | varchar(255) nullable | VARCHAR(255) | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

**⚠️ binary(32) → bytea**: El modelo `AuthSession` no define cast explícito para `ip_hash`. El
hash se almacena como bytes crudos (`hash('sha256', $ip, true)`). Laravel maneja la conversión
automáticamente con PDO. **Verificar** que `AuthCookieService` y `StartAuthSession` leen/escriben
correctamente el hash en formato bytea.

### Tabla: auth_refresh_tokens

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno |
| auth_session_id | foreignId | BIGINT FK | Ninguno |
| **token_hash** | **binary(32)** | **BYTEA** | **Laravel binary() → bytea automático** |
| generation | unsignedInteger default 1 | INTEGER DEFAULT 1 | Pierde unsigned; PG no distingue |
| state | varchar(20) default 'ACTIVE' | VARCHAR(20) DEFAULT 'ACTIVE' | Ninguno |
| issued_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| expires_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| consumed_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| revoked_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| replaced_by_id | foreignId nullable | BIGINT FK self-ref | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

**⚠️ binary(32) → bytea**: Similar a `auth_sessions.ip_hash`. El `token_hash` almacena el hash
SHA-256 del refresh token en bruto. La comparación `hash_equals()` en PHP funciona sobre strings
binarios independientemente de la representación en base de datos.

### Tabla: operation_types

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno |
| organization_id | foreignId | BIGINT FK | Ninguno |
| name | varchar(160) | VARCHAR(160) | Ninguno |
| description | varchar(500) nullable | VARCHAR(500) | Ninguno |
| **cash_multiplier** | **tinyInteger default 0** | **SMALLINT DEFAULT 0** | **Laravel tinyInteger → smallint en pgsql** |
| **digital_multiplier** | **tinyInteger default 0** | **SMALLINT DEFAULT 0** | **Laravel tinyInteger → smallint en pgsql** |
| **sort_order** | **unsignedInteger default 0** | **INTEGER DEFAULT 0** | **Pierde unsigned** |
| is_active | boolean default true | BOOLEAN DEFAULT true | Ninguno |
| deactivated_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

**Nota**: `tinyInteger` mapea a `smallint` (2 bytes, rango -32768 a 32767) en PostgreSQL. Los
valores actuales de multiplicadores son -1, 0, 1 — cabe perfectamente.

### Tabla: operations

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno |
| internal_code | varchar(30) nullable | VARCHAR(30) | Ninguno |
| organization_id | foreignId | BIGINT FK | Ninguno |
| agent_id | foreignId nullable | BIGINT FK | Ninguno |
| operation_type_id | foreignId | BIGINT FK | Ninguno |
| user_id | foreignId | BIGINT FK | Ninguno |
| customer_name | varchar(200) nullable | VARCHAR(200) | Ninguno |
| **amount** | **decimal(18,2)** | **NUMERIC(18,2)** | **Laravel decimal → numeric** |
| **cash_delta** | **decimal(18,2) default 0** | **NUMERIC(18,2) DEFAULT 0** | **Laravel decimal → numeric** |
| **digital_delta** | **decimal(18,2) default 0** | **NUMERIC(18,2) DEFAULT 0** | **Laravel decimal → numeric** |
| currency | char(3) default 'PEN' | CHAR(3) DEFAULT 'PEN' | Ninguno — char(3) no varía |
| effective_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| recorded_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| status | varchar(20) default 'ACTIVE' | VARCHAR(20) DEFAULT 'ACTIVE' | Ninguno |
| observation | varchar(500) nullable | VARCHAR(500) | Ninguno |
| annulled_by | foreignId nullable | BIGINT FK | Ninguno |
| annulled_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| annulment_reason | varchar(500) nullable | VARCHAR(500) | Ninguno |
| idempotency_key | char(64) unique | CHAR(64) UNIQUE | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

### Tabla: daily_closures

| Columna MySQL | Tipo MySQL | Tipo PostgreSQL | Cambio en migración |
|--------------|-----------|----------------|---------------------|
| id | bigIncrements | BIGSERIAL | Ninguno |
| organization_id | foreignId | BIGINT FK | Ninguno |
| agent_id | foreignId nullable | BIGINT FK | Ninguno |
| **business_date** | **date** | **DATE** | **Compatible** |
| status | varchar(20) default 'ACTIVO' | VARCHAR(20) DEFAULT 'ACTIVO' | Ninguno |
| operation_count | unsignedInteger default 0 | INTEGER DEFAULT 0 | Pierde unsigned |
| gross_amount | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| total_cash_in | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| total_cash_out | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| total_digital_in | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| total_digital_out | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| opening_cash | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| opening_digital | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| expected_closing_cash | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| expected_closing_digital | decimal(18,2) default 0 | NUMERIC(18,2) DEFAULT 0 | Laravel auto |
| actual_closing_cash | decimal(18,2) nullable | NUMERIC(18,2) | Laravel auto |
| actual_closing_digital | decimal(18,2) nullable | NUMERIC(18,2) | Laravel auto |
| cash_difference | decimal(18,2) nullable | NUMERIC(18,2) | Laravel auto |
| digital_difference | decimal(18,2) nullable | NUMERIC(18,2) | Laravel auto |
| has_inconsistencies | boolean default false | BOOLEAN DEFAULT false | Ninguno |
| opened_by | foreignId nullable | BIGINT FK | Ninguno |
| submitted_by | foreignId nullable | BIGINT FK | Ninguno |
| opened_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| submitted_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| confirmed_by | foreignId nullable | BIGINT FK | Ninguno |
| confirmed_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| reopened_by | foreignId nullable | BIGINT FK | Ninguno |
| reopened_at | datetime(6) nullable | TIMESTAMP(6) | Ninguno |
| reopen_reason | varchar(500) nullable | VARCHAR(500) | Ninguno |
| notes | varchar(500) nullable | VARCHAR(500) | Ninguno |
| created_at | datetime(6) | TIMESTAMP(6) | Ninguno |
| updated_at | datetime(6) | TIMESTAMP(6) | Ninguno |

### Tablas sin cambios significativos

- `session_events`: json → json (Laravel auto). Evaluar migración futura a `jsonb`.
- `audit_logs`: json → json (Laravel auto). Evaluar migración futura a `jsonb`. `unsignedBigInteger` → `bigint`.
- `regions`, `provinces`, `districts`: sin cambios.
- `agents`: `text` → `text` (compatible).
- `user_agent_assignments`, `daily_closure_operations`: sin cambios.
- `password_resets`: sin cambios.
- `migrations`: tabla estándar Laravel, compatible.

## Estrategia de Secuencias

Todas las PK usan `$table->id()` que en Laravel 13 con PostgreSQL genera `BIGSERIAL` (equivalente
a `BIGINT GENERATED BY DEFAULT AS IDENTITY`). Como no se importan datos con IDs explícitos, las
secuencias comienzan en 1 y se auto-incrementan normalmente. No se requiere `setval()`.

Si en el futuro se importaran datos con IDs explícitos (por ejemplo, desde un backup de
producción), después de la importación se debe ejecutar:

```sql
SELECT setval('users_id_seq', COALESCE((SELECT MAX(id) FROM users), 1));
```

Para cada tabla con ID importado explícitamente.

## Índices y Restricciones: Cambios Requeridos

### Migración 000009: eliminación de índices huérfanos

Antes de dropear las columnas `store_id` y `bank_agent_id`, se deben eliminar explícitamente
los índices que las referencian. Laravel nombra los índices compuestos como
`<table>_<col1>_<col2>_index`. Los nombres específicos dependen de la versión de Laravel
que generó la migración. La estrategia más segura es nombrar explícitamente los índices al
crearlos (en migraciones nuevas) o dropearlos con `$table->dropIndex()` usando el nombre
generado en migraciones existentes.

**Índices a dropear en operations**:
```php
$table->dropIndex(['bank_agent_id', 'effective_at']); // Nombre generado
$table->dropIndex(['store_id', 'effective_at']);      // Nombre generado
```

**Índices/unique a dropear en daily_closures**:
```php
$table->dropUnique(['bank_agent_id', 'business_date', 'status']); // Nombre generado
$table->dropIndex(['bank_agent_id', 'business_date']);            // Nombre generado
```

**Nota**: En MySQL, dropear una columna con `$table->dropColumn()` también elimina sus índices
asociados. PostgreSQL NO hace esto — los índices deben dropearse explícitamente antes de
dropear la columna, o la migración fallará con "cannot drop column because it is referenced
by an index".

## Transformaciones de Datos (solo para referencia futura)

Aunque no se migran datos reales en este plan, se documentan las transformaciones que
aplicarían si se importaran datos desde MySQL:

1. **Booleanos**: `0`/`1`/`'0'`/`'1'` → `false`/`true`. PostgreSQL rechaza insertar string
   `'0'` en columna boolean.
2. **binary a bytea**: `UNHEX(hash_string)` → `decode(hash_string, 'hex')` en PostgreSQL.
3. **datetime a timestamp**: MySQL `DATETIME` no tiene zona; PostgreSQL `TIMESTAMP` es
   timezone-aware si se usa `TIMESTAMPTZ`. Almacenar en UTC sin zona horaria (`TIMESTAMP`)
   y mostrar en `America/Lima` preserva compatibilidad.
4. **decimal**: `DECIMAL(18,2)` en MySQL → `NUMERIC(18,2)` en PostgreSQL preserva escala y
   precisión exactamente.
5. **JSON**: MySQL `JSON` → PostgreSQL `JSONB`. Evaluar migración con `::jsonb` cast para
   validación y compresión.
6. **Orden de importación**: Resolver dependencias topológicamente por FK: organizations →
   users → agents → user_agent_assignments → operation_types → operations →
   daily_closures → daily_closure_operations → password_resets → auth_sessions →
   auth_refresh_tokens → session_events → audit_logs.

## Verificación de Integridad Post-Migración

Al no haber datos reales, la verificación se limita a:
1. `SELECT count(*)` en todas las tablas tras `migrate:fresh --seed`
2. Verificar que seeders crean registros sin errores
3. Verificar FK activas con `SELECT conname FROM pg_constraint WHERE contype = 'f'`
4. Verificar secuencias inicializadas correctamente
5. Ejecutar suite de pruebas completa contra PostgreSQL

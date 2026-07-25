# Mapping Rules: Store → Agent, BankAgent → Agent

**Feature**: 008-simplify-agent-operations | **Date**: 2026-07-23

## Primary Rule: One Store = One Agent

Cada registro de la tabla `stores` genera exactamente un registro en la tabla `agents`. La tienda es la fuente de verdad de la ubicación física, por lo que su identidad se preserva 1:1 en el nuevo modelo.

## Consolidation Rules

### Rule 1: Store → Agent (mapping principal)

```
agents.code  = stores.code
agents.name  = stores.name
agents.address = stores.address
agents.is_active = stores.is_active
agents.organization_id = stores.organization_id
agents.city     = districts.name          (via stores.district_id → districts.id)
agents.province = provinces.name          (via districts.province_id)
agents.region   = regions.name            (via provinces.region_id)
agents.district = districts.name          (via stores.district_id)
agents.description = 'Migrado desde Stores (id=' || stores.id || ')'
```

### Rule 2: BankAgent consolidation

Cada `bank_agent` existente se asocia al `agent` correspondiente según el `store_id` del bank_agent:

```
Si bank_agent.store_id → store → agent
Entonces bank_agent se consolida en el mismo agent (no se crea un agente adicional)
```

Esto significa que un `store` con N `bank_agents` produce **1 solo agent**, y los N bank_agents se mapean a ese mismo `agent.id`.

### Rule 3: BankAgent huérfano (sin store)

```
Si bank_agent.store_id NO tiene store correspondiente
O bank_agent.store_id IS NULL
Entonces se crea un agent independiente:
  agents.code  = bank_agent.code
  agents.name  = 'Agente ' || bank_agent.code
  agents.city  = 'Sin ciudad' (o se deriva del bank.name si está disponible)
  agents.organization_id = bank_agent.organization_id
  agents.is_active = bank_agent.is_active
  agents.description = 'Migrado desde BankAgent huérfano (id=' || bank_agent.id || ')'
```

### Rule 4: Multiple BankAgents at one Store

Un store con múltiples bank_agents (ej. "Agente Centro" tiene bank_agents en BCP, Interbank y BBVA) produce:

- **Un solo agent** (consolidando el store)
- **Tres entradas en `_migration_map`**:
  - `(old_table='stores', old_id=store.id, new_agent_id=agent.id, notes='Store principal')`
  - `(old_table='bank_agents', old_id=bcp_ba.id, new_agent_id=agent.id, notes='BCP')`
  - `(old_table='bank_agents', old_id=interbank_ba.id, new_agent_id=agent.id, notes='Interbank')`

### Rule 5: Store sin BankAgent

Un store que no tiene ningún bank_agent asociado produce un agent normalmente. Las operaciones/cierres que referenciaban ese store vía `store_id` se mapearán al agent creado.

## Mapping Table Population

La tabla `_migration_map` se pobla de la siguiente manera:

```sql
-- Paso 1: Insertar mapeo Store → Agent
INSERT INTO _migration_map (old_table, old_id, new_agent_id, notes, created_at)
SELECT 'stores', s.id, a.id, 'Store principal', NOW()
FROM stores s
JOIN agents a ON a.code = s.code AND a.organization_id = s.organization_id;

-- Paso 2: Insertar mapeo BankAgent → Agent (consolidado)
INSERT INTO _migration_map (old_table, old_id, new_agent_id, notes, created_at)
SELECT 'bank_agents', ba.id, a.id, CONCAT('Via store_id=', ba.store_id), NOW()
FROM bank_agents ba
JOIN _migration_map m ON m.old_table = 'stores' AND m.old_id = ba.store_id
JOIN agents a ON a.id = m.new_agent_id
WHERE ba.store_id IS NOT NULL;

-- Paso 3: Insertar mapeo BankAgent → Agent (huérfanos)
INSERT INTO _migration_map (old_table, old_id, new_agent_id, notes, created_at)
SELECT 'bank_agents', ba.id, a.id, 'BankAgent huérfano', NOW()
FROM bank_agents ba
JOIN agents a ON a.code = ba.code AND a.organization_id = ba.organization_id
WHERE ba.store_id IS NULL
   OR ba.store_id NOT IN (SELECT id FROM stores);
```

## City Derivation

El campo `agents.city` se deriva de la jerarquía geográfica:

```
agents.city = districts.name
```

Si `district_id` es NULL en stores, se usa 'Sin ciudad' como valor por defecto.

## Idempotency

La migración es **idempotente**: ejecutarla múltiples veces no debe duplicar agentes ni mapeos.

Antes de insertar, se verifica:

```sql
-- Verificar que no exista ya un agent con el mismo código y organización
SELECT COUNT(*) FROM agents WHERE code = ? AND organization_id = ?
```

Si ya existe, se usa el `agent.id` existente y **no** se duplica.

## Collision Handling

### Colisión de código (code)

Si dos stores en la misma organización tienen el mismo `code` (teóricamente imposible por el constraint unique, pero posible en datos corruptos):

1. La primera inserción gana el código.
2. La segunda usa `code || '-2'` (o el siguiente sufijo disponible).
3. El código duplicado original se registra en la columna `notes` de `_migration_map`.
4. Ambas stores se reportan en el reporte de transformación como "collision_resolved".

### Colisión de BankAgent con Store

Si un bank_agent tiene el mismo `code` que un store existente pero representan puntos físicos distintos (el bank_agent no tiene store_id que apunte a ese store), se crea un agent independiente con `code = code || '-BA'`.

## Exception Reporting

Las siguientes situaciones se reportan como excepciones (no bloquean la migración):

| Situación | Severidad | Acción |
|-----------|-----------|--------|
| BankAgent sin store_id | WARNING | Crear agent independiente |
| Store sin district_id | WARNING | city = 'Sin ciudad' |
| Código duplicado | WARNING | Resolver con sufijo |
| BankAgent con store_id que no existe en stores | ERROR | Crear agent independiente, reportar |
| Store con bank_agents en múltiples bancos | INFO | Consolidar en un solo agent |

Ningún dato se descarta silenciosamente. Todo registro que no puede mapearse limpiamente se reporta y se le asigna un agent de todas formas.

## Rollback

El mapeo inverso se reconstruye desde `_migration_map`:

```
agents.id → _migration_map.new_agent_id → (old_table, old_id)
```

Si `old_table = 'stores'`, el `old_id` es el `stores.id` original.
Si `old_table = 'bank_agents'`, el `old_id` es el `bank_agents.id` original.

Las operaciones y cierres migrados revierten su `agent_id` al `bank_agent_id` original usando el mapeo inverso.

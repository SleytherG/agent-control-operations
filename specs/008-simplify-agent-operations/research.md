# Research: Operaciones Generales por Agente

**Feature**: 008-simplify-agent-operations | **Date**: 2026-07-23

## Decision 1: Estrategia de consolidación Store + BankAgent → Agent

**Decision**: Cada tienda (store) se convierte en un agente. Los bank_agents que referencian esa tienda se consolidan en el mismo agente. Si un bank_agent no tiene store (huérfano), se crea un agente independiente. El mapeo bidireccional se registra en tabla temporal `_migration_map(old_table, old_id, new_agent_id)`.

**Rationale**: La mayoría de stores en los seeders tienen exactamente un bank_agent. Consolidar por store es la ruta de menor pérdida semántica: la ubicación física (store) es lo que el negocio llama Agente.

**Alternatives considered**:
- Crear agent desde bank_agent y descartar store: perdería la geolocalización (district→province→region) que store sí tiene.
- Crear agent nuevo sin migrar datos: requeriría que el cliente reingrese todo manualmente.

## Decision 2: OperationType sin bank_id

**Decision**: Eliminar columna `bank_id` de `operation_types`. Todos los tipos pasan a ser generales (bank_id = null). Los seeders que crean variantes por banco ("Depósito - BCP", "Depósito - Interbank") se simplifican a un solo tipo por nombre.

**Rationale**: BR-002 y FR-030 requieren eliminar referencias productivas a bancos. Los tipos generales cubren todas las operaciones sin segmentación artificial.

**Alternatives considered**:
- Mantener bank_id nullable sin usar: viola FR-030 y deja código muerto.

## Decision 3: cash_direction → cash_multiplier + digital_multiplier

**Decision**: Reemplazar el enum `cash_direction` (ENTRADA/SALIDA/NEUTRA) por dos columnas integer: `cash_multiplier` y `digital_multiplier` con valores -1, 0, +1. El snapshot en operations usa `cash_delta` y `digital_delta` (decimal 18,2).

**Rationale**: El nuevo dominio requiere distinguir efectos sobre efectivo y saldo digital por separado. Un tipo "Transferencia" puede ser efectivo +1 y digital -1 simultáneamente, lo cual `cash_direction` no puede expresar.

**Alternatives considered**:
- Mantener cash_direction + agregar columna separada: duplicaría lógica y complicaría queries de cierre.

## Decision 4: Módulo Agents como reemplazo de Organization/Stores + BankingNetwork

**Decision**: Crear `app/Modules/Agents/` con AgentController (CRUD agentes + asignaciones), AgentPolicy, AgentRequest. Eliminar StoreController, BankController, BankAgentController, sus policies y requests.

**Rationale**: El nuevo dominio tiene un solo punto físico. Separar Agents en su propio módulo simplifica la estructura y evita confusión con el código legacy durante la migración.

## Decision 5: Internal code formato OP-YYYYMMDD-NNNN

**Decision**: Código autogenerado en servidor con formato `OP-{fecha_efectiva_YYYYMMDD}-{secuencia_4_digitos}`. Único global, no reseteable por día. Se almacena en `operations.internal_code` (varchar 30, unique).

**Rationale**: BR-008 requiere código único, visible, inmutable, sin dependencia bancaria. El formato con fecha facilita búsquedas visuales. La secuencia global evita colisiones entre agentes.

**Alternatives considered**:
- UUID: demasiado largo para uso operativo diario.
- Secuencia por agente: requiere lógica adicional y complica búsquedas cross-agente.

## Decision 6: DashboardQueryService sin filtros bancarios

**Decision**: Eliminar `bank_id`, `store_id` de `applyAdminFilters()`. Reemplazar store_id→agent_id. Eliminar joins a stores, banks. Eliminar gráfico "Ops by Bank Partner" del dashboard admin. Agregar filtro por ciudad/región desde el agente.

**Rationale**: FR-023 y la spec prohíben filtros por banco. La segmentación geográfica se mantiene vía las columnas del agente (city, region, province, district).

## Decision 7: Estrategia incremental

**Decision**: 7 incrementos independientes con PRs atómicos:
1. **Migración base**: Crear agents, _migration_map, user_agent_assignments. Migrar datos. Tests de integridad.
2. **Agentes y asignaciones**: AgentController, AgentPolicy, vistas agents/, reemplazar rutas banking-network + stores.
3. **Tipos y operaciones**: Eliminar bank_id de operation_types, adaptar RegisterOperation/ListOperations, migrar columnas operations.
4. **Apertura y cierre**: Adaptar DailyClosingController, migrar daily_closures, agregar columnas de saldos.
5. **Consultas y dashboards**: Adaptar DashboardQueryService y vistas reporting.
6. **Sesión y visual**: Integrar session-indicator, validar Stitch pixel-perfect, textos Agente.
7. **Limpieza final**: Dropear tablas banks/stores/bank_agents, eliminar controllers/policies/vistas legacy.

**Rationale**: Cada incremento es independientemente testeable y deployable. La aplicación nunca queda inaccesible. Permite rollback por incremento. Alinea con Principio II constitucional.

## Decision 8: Rollback strategy

**Decision**: Cada migración de base de datos tiene `down()` probado. La tabla `_migration_map` persiste hasta la limpieza final para permitir reconstruir estado anterior. Respaldos completos de BD antes de cada incremento en producción.

**Rationale**: SC-014 requiere rollback <60min para el volumen de referencia. Las migraciones de Laravel con down() bien definido + respaldo binario cubren este requisito.

## Decision 9: Stitch adaptation

**Decision**: Las 7 pantallas Stitch se adaptan eliminando labels/filtros de banco/tienda y reemplazando por Agente/Efectivo/Saldo digital. Las screenshots de referencia (`screen.png`) no se modifican; las desviaciones se documentan en DEVIATIONS.md.

**Rationale**: BR-025 establece `screen.png` como referencia visual. Las adaptaciones por dominio corregido son desviaciones permitidas según spec.

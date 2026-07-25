# Referencias a Bank, Store y BankAgent

Alcance exigido por T007: policies, Form Requests, actions y services bajo `app/Modules/*/`. Se consideran referencias de clase, relaciones y nombres de tabla/campo (`bank_id`, `store_id`, `bank_agent_id`). Los controladores y modelos estan inventariados por separado en T002/T004.

## Policies (4)

| Archivo | Referencias |
|---|---|
| `BankingNetwork/Policies/BankPolicy.php` | Policy tipada sobre `Bank`; `view`, `update` y `deactivate` comparan organizacion del banco |
| `BankingNetwork/Policies/BankAgentPolicy.php` | Policy tipada sobre `BankAgent`; `view`, `update` y `deactivate` comparan organizacion del agente bancario |
| `Organization/Policies/StorePolicy.php` | Policy tipada sobre `Store`; `view`, `update` y `deactivate` comparan organizacion de la tienda |
| `DailyClosing/Policies/DailyClosingPolicy.php` | Autoriza `view` y `generate` mediante asignaciones filtradas por `bank_agent_id` |

## Form Requests (8)

| Archivo | Referencias |
|---|---|
| `BankingNetwork/Http/Requests/BankRequest.php` | Autoriza `Bank`; valida unicidad en tabla `banks` |
| `BankingNetwork/Http/Requests/BankAgentRequest.php` | Autoriza `BankAgent`; exige `store_id` activo, `bank_id` activo y codigo unico en `bank_agents` |
| `BankingNetwork/Http/Requests/AssignOperatorRequest.php` | Exige `bank_agent_id` activo y evita asignacion activa duplicada |
| `Organization/Http/Requests/StoreRequest.php` | Autoriza `Store`; valida codigo unico en `stores` |
| `Operations/Http/Requests/RegisterOperationRequest.php` | Importa `BankAgent`; exige `bank_agent_id` existente y emite mensajes de agente bancario |
| `Operations/Http/Requests/OperationTypeRequest.php` | Valida `bank_id` existente/activo y unicidad de nombre por banco |
| `DailyClosing/Http/Requests/GenerateClosingRequest.php` | Exige `bank_agent_id` existente y usa mensajes de agente bancario |
| `Reporting/Http/Requests/DashboardFilterRequest.php` | Acepta `store_id`, `bank_id` y `bank_agent_id`, con `exists` sobre las tres tablas legacy |

## Actions (3)

| Archivo | Referencias |
|---|---|
| `Operations/Application/Actions/ListOperations.php` | Eager-load `bankAgent.store` y `bankAgent.bank`; filtra por `bank_agent_id` |
| `Operations/Application/Actions/RegisterOperation.php` | Carga `BankAgent`; valida `UserBankAgentAssignment` por `bank_agent_id`; deriva `store_id`; guarda `bank_agent_id`; bloquea fechas con cierre confirmado del agente |
| `Operations/Application/Actions/AnnulOperation.php` | Usa `operation.bank_agent_id` y consulta cierres confirmados por `bank_agent_id` antes de anular |

No hay referencias coincidentes en las actions de `IdentityAccess`.

## Services (1)

| Archivo | Referencias |
|---|---|
| `Reporting/Services/DashboardQueryService.php` | Filtros `store_id`, `bank_id`, `bank_agent_id`; joins a `bank_agents` y `stores`; geografia derivada desde `operations.store_id`; banco derivado desde `operation_types.bank_id`; selecciona `agent_code` y `store_name` |

Los demas services (`IdentityAccess/Services/*`) no contienen referencias coincidentes. Total afectado en este alcance: 16 archivos.

# Web Endpoint Contracts: Registro de Operaciones

## Shared Rules

Mismas que 001 y 002: HTTPS, HttpOnly cookies, CSRF, `Cache-Control: no-store`. Operador solo ve/opera sobre sus datos.

## Operation Types (Admin Only)

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/operation-types | Listado con filtro por banco y estado |
| POST | /admin/operation-types | Crear tipo |
| PATCH | /admin/operation-types/{type} | Editar |
| DELETE | /admin/operation-types/{type} | Desactivar |

**POST/PATCH body**: `name`, `description?`, `bank_id?` (null=general), `cash_direction`.

## Operations

| Method | Path | Description |
|--------|------|-------------|
| GET | /operations | Historial: operador ve propias; admin ve todas con filtros (agent, type, date, status, user) |
| GET | /operations/create | Formulario de registro con agente, tipos disponibles e idempotency token |
| POST | /operations | Registrar operación |
| GET | /operations/{operation} | Ver detalle de operación |
| POST | /operations/{operation}/annul | Anular operación |

**POST /operations body**: `bank_agent_id`, `operation_type_id`, `amount`, `currency` (default PEN), `effective_at`, `reference?`, `observation?`, `idempotency_key`.

**POST /operations/{operation}/annul body**: `reason` (obligatorio).

## Validation

- `bank_agent_id`: asignación activa del operador autenticado verificada en servidor.
- `amount`: > 0, decimal.
- `effective_at`: no futura, no anterior a `now - retroactive_window_hours`.
- `idempotency_key`: unique; si ya existe, devolver resultado de la operación original (200 con redirect).
- `reason` en anulación: obligatorio, máximo 500 caracteres. Operador solo dentro de ventana configurable.

## Responses

- `201/303`: operación creada, redirigir a confirmación o historial.
- `409`: idempotency key duplicada (misma operación ya registrada).
- `422`: validación fallida.
- `403`: sin permiso (operador anulando operación ajena, fuera de ventana).
- `404`: operación no encontrada o no autorizada.

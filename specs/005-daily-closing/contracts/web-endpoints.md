# Web Endpoint Contracts: Cierre Operativo Diario

## Shared Rules

Mismas que 001-004: HTTPS, cookies, CSRF, `Cache-Control: no-store`. Solo admin confirma/reabre.

## Daily Closures

| Method | Path | Description |
|--------|------|-------------|
| GET | /daily-closures | Listado: admin ve todos; operador solo de sus agentes asignados. Filtros: agente, fecha, estado |
| GET | /daily-closures/create | Formulario de generación con selector de agente (asignados para operador) |
| POST | /daily-closures | Generar o regenerar cierre |
| GET | /daily-closures/{closure} | Ver detalle con métricas, desglose por tipo y operador, y lista de operaciones |
| POST | /daily-closures/{closure}/confirm | Confirmar cierre (admin only) |
| POST | /daily-closures/{closure}/reopen | Reabrir cierre con motivo (admin only) |

## Request/Response

### POST /daily-closures

**Body**: `bank_agent_id` (required), `business_date` (required, date), `regenerate` (bool, default false).

**Success 201**: cierre generado, redirect a detalle. **409**: ya existe cierre activo para ese agente y fecha. **403**: operador intenta generar para agente no asignado.

### POST /daily-closures/{closure}/confirm

**Success 200**: cierre confirmado, operaciones bloqueadas. **409**: cierre no está en estado ACTIVO. **403**: no es administrador.

### POST /daily-closures/{closure}/reopen

**Body**: `reason` (required, max 500).

**Success 200**: cierre reabierto. **422**: motivo vacío. **409**: cierre no está CONFIRMADO. **403**: no es administrador.

## View Detail Response

El detalle muestra:
- Tarjetas: operation_count, gross_amount, cash_in, cash_out, net_movement
- Tabla de desglose por tipo de operación
- Tabla de desglose por operador
- Lista de operaciones anuladas del periodo
- Si `has_pending_confirm = true`: warning + "Pendiente de confirmación" en net_movement
- Fecha y hora de generación, usuario que confirmó (si aplica), fecha de confirmación
- Si REABIERTO: usuario que reabrió, fecha, motivo
- Botón Confirmar (admin, si ACTIVO) / Botón Reabrir (admin, si CONFIRMADO)

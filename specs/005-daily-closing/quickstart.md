# Quickstart Validation: Cierre Operativo Diario

## Prerequisites

- Features 001-003 completamente desplegadas.
- Operaciones de prueba registradas en al menos un agente para la fecha actual.

## Setup

```bash
php artisan migrate
```

## Validation Scenarios

### Generate Closure

1. Admin: GET /daily-closures/create → selector de agentes.
2. Seleccionar agente con operaciones hoy, fecha = today → POST → 201, detalle con métricas correctas.
3. Operador: mismo flujo, solo ve sus agentes asignados → métricas solo de sus operaciones.
4. Operador: intentar generar cierre para agente no asignado → 403.
5. Intentar generar segundo cierre activo misma fecha+agente → 409.

### Confirm Closure

1. Admin: POST /daily-closures/1/confirm → 200, estado CONFIRMADO, métricas de confirmación visibles.
2. Operador: intentar anular operación del cierre confirmado → rechazado.
3. Operador: intentar registrar nueva operación en agente+fecha del cierre confirmado → rechazado.
4. Operador: intentar confirmar cierre → 403.

### Reopen Closure

1. Admin: POST /daily-closures/1/reopen con reason → 200, estado REABIERTO, auditoría.
2. Sin motivo → 422.
3. Operador: intentar anular operación del cierre reabierto → exitoso.
4. Admin: volver a confirmar → 200, CONFIRMADO nuevamente.
5. Operador: intentar reabrir → 403.

### POR_CONFIRMAR Warning

1. Asegurar que existen operaciones con tipo POR_CONFIRMAR en el agente.
2. Generar cierre → advertencia visible, net_movement etiquetado como "Pendiente de confirmación".
3. Cierre sin POR_CONFIRMAR → sin advertencia.

## Expected Tests

```text
tests/Feature/DailyClosing/GenerateClosingTest.php
tests/Feature/DailyClosing/ConfirmClosingTest.php
tests/Feature/DailyClosing/ReopenClosingTest.php
tests/Feature/DailyClosing/ClosingAuthorizationTest.php
tests/Feature/DailyClosing/PendingConfirmWarningTest.php
tests/Feature/DailyClosing/PostConfirmBlockingTest.php
```

# Quickstart Validation: Registro de Operaciones

## Prerequisites

- Features 001 y 002 completamente desplegadas.
- Operador asignado a al menos un agente activo.
- Seed de tipos de operación ejecutado.

## Setup

```bash
php artisan migrate
php artisan db:seed --class=OperationTypeSeeder
```

## Validation Scenarios

### Operation Type Catalog

1. Admin: crear tipo "Depósito" para BCP, dirección ENTRADA → 201.
2. Admin: crear tipo general "Consulta de saldo", dirección NEUTRA → 201.
3. Admin: intentar duplicar nombre para mismo banco → 422.

### Operation Registration

1. Operador: GET /operations/create → formulario con agentes asignados y tipos (banco + generales).
2. Registrar operación con monto 150.00 PEN → 201, confirmación visible.
3. Reenviar mismo formulario (mismo idempotency_key) → 409/redirect a operación existente.
4. Registrar con monto 0 → 422.
5. Registrar con fecha efectiva de hace 48 horas → 422 (fuera de ventana retroactiva).
6. Intentar registrar en agente no asignado modificando parámetro → 403.

### Operation History

1. Operador: GET /operations → solo ve sus operaciones paginadas.
2. Admin: GET /operations → ve todas, filtra por agente, tipo, fecha, operador.
3. Operador manipula filtro de usuario → resultados restringidos a sus propias operaciones.

### Annulment

1. Operador: anular operación propia recién creada con motivo → operación ANNULLED, metadatos visibles.
2. Operador: intentar anular operación de hace 48 horas → 403 (ventana vencida).
3. Admin: anular cualquier operación activa → 200, sin restricción de ventana.
4. Operador: intentar anular operación ajena → 403.
5. Verificar que operación anulada no aparece en totales activos pero sí en historial.

### Decimal Precision

1. Registrar operaciones con montos 0.01, 100.50, 9999999.99 → todos persisten sin pérdida.
2. Verificar suma SQL de 100 operaciones coincide con suma esperada.

## Expected Tests

```text
tests/Feature/Operations/OperationTypeTest.php
tests/Feature/Operations/OperationRegistrationTest.php
tests/Feature/Operations/OperationHistoryTest.php
tests/Feature/Operations/OperationAnnulmentTest.php
tests/Feature/Operations/OperationDecimalPrecisionTest.php
```

Cada suite incluye autorización positiva y negativa.

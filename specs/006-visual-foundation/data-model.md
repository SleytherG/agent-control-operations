# Data Model: Fundamentos Visuales

## No Database Tables

Esta capacidad no introduce tablas, migraciones ni modelos Eloquent. Es puramente presentacional.

## Demo Data Fixtures

Archivos PHP en `resources/demo/` que retornan arrays asociativos:

### resources/demo/user.php
```php
return [
    'operator' => [
        'id' => 1, 'name' => 'Carlos López', 'role' => 'OPERADOR',
        'store' => 'Tienda Centro', 'bank' => 'BCP',
        'agent' => 'BCP-Centro', 'agent_code' => 'AGT-001',
    ],
    'admin' => [
        'id' => 2, 'name' => 'María García', 'role' => 'ADMINISTRADOR_PROPIETARIO',
    ],
];
```

### resources/demo/operations.php
Array de 15-20 operaciones demo con: id, amount, currency, type, cash_direction, status, effective_at, reference, operator_name, agent_code.

### resources/demo/metrics.php
Array con métricas agregadas: operation_count, gross_amount, cash_in, cash_out, net_movement, by_type, by_operator, evolution.

### resources/demo/closing.php
Array con cierre demo en 3 estados: ACTIVO, CONFIRMADO, REABIERTO. Incluye métricas, breakdown, confirming_user, reopen_reason.

## State Query Parameters

Las pantallas de autenticación aceptan `?state=` para mostrar variantes:

| state | Visual |
|-------|--------|
| (none) | Normal |
| error | Credenciales incorrectas |
| disabled | Usuario desactivado |
| throttled | Demasiados intentos |
| network-error | Error de red |
| loading | Envío en progreso |

El modal de expiración acepta `?expiry=`:
- warning (30s), renewing, renewed, expired, revoked.

# Quickstart Validation: Administración de Estructura Operacional

## Prerequisites

- Feature 001-auth-session completamente desplegada y funcional.
- Administrador propietario seeded (`admin / password`).

## Setup

```bash
php artisan migrate
php artisan db:seed --class=OperationalStructureSeeder
```

## Validation Scenarios

### Admin CRUD

1. Iniciar sesión como administrador.
2. Crear región "Lima" → 201; listar regiones → aparece Lima.
3. Crear provincia "Lima" en región Lima → 201.
4. Crear distrito "Cercado de Lima" en provincia Lima → 201.
5. Crear tienda "Tienda Centro" en distrito Cercado → 201; listar tiendas.
6. Crear banco "BCP" con código "BCP" → 201; crear duplicado → 422.
7. Crear agente "BCP-Centro" en tienda Centro y banco BCP → 201.
8. Editar nombre de tienda → 200; desactivar tienda sin agentes → 200.

### Operator Creation And Assignment

1. Crear operador "operador1" con correo y contraseña → 201.
2. Asignar operador1 al agente BCP-Centro → 201.
3. Asignar duplicado activo → 409.
4. Desasignar → 200; verificar historial en GET assignments.
5. Desactivar operador1 → 200; verificar que no puede iniciar sesión.

### Operator First Login

1. Iniciar sesión como operador1 recién creado → redirigido a /password/change.
2. Cambiar contraseña → redirigido a /home.
3. Intentar acceder a /admin/stores → 403.

### Operator Agent View

1. Iniciar sesión como operador asignado → GET /my-agents muestra solo BCP-Centro.
2. Manipular parámetros para ver otro agente → respuesta sin datos ajenos.

### Blocked Operations

1. Desactivar tienda con agente activo → 409 con lista de agentes bloqueantes.
2. Desactivar agente → verificar que asignaciones se terminan automáticamente.
3. Intentar DELETE físico en tienda con operaciones → 405/409.

## Expected Tests

```text
tests/Feature/Organization/   RegionTest, ProvinceTest, DistrictTest, StoreTest
tests/Feature/BankingNetwork/ BankTest, BankAgentTest, UserBankAgentAssignmentTest
tests/Feature/IdentityAccess/ OperatorRegistrationTest, ForcePasswordChangeTest
```

Cada suite incluye casos positivos y negativos de autorización para ambos roles.

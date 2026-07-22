# Research: Administración de Estructura Operacional

## Stack And Reuse

**Decision**: Reutilizar el stack completo de 001-auth-session: Laravel 13, Eloquent, Blade, `lcobucci/jwt`, PHPUnit. Sin nuevas dependencias.

**Rationale**: CRUD administrativo sobre el mismo monolito. La autenticación, autorización, auditoría y cookies ya están implementadas.

**Alternatives considered**: Paquete de administración separado — rechazado por innecesario para el volumen del MVP.

## Geographic Hierarchy

**Decision**: Tres tablas separadas con FK: `regions` → `provinces` → `districts`. Cada nivel pertenece a `organizations` y tiene nombre único dentro de su padre. Sin validación contra fuente geográfica externa.

**Rationale**: Mantiene integridad referencial simple, permite filtros por cualquier nivel y es suficiente para el MVP sin depender de APIs o datasets externos.

**Alternatives considered**: Strings planos — rechazado porque impide filtros jerárquicos y normalización; tabla única polimórfica — rechazada por complejidad innecesaria para tres niveles fijos.

## Logical Deactivation

**Decision**: `is_active` booleano + `deactivated_at` timestamp en cada entidad. Las FK referencian registros inactivos (RESTRICT no aplica por estado). La lógica de negocio en acciones impide nuevas relaciones hacia entidades inactivas.

**Rationale**: Consistente con `001-auth-session` (users, organizations). Permite restaurar sin pérdida de historial.

**Alternatives considered**: Soft deletes de Laravel — rechazado porque `deleted_at` semánticamente implica eliminación y complica queries con FK activas.

## Overlapping Assignments

**Decision**: Unique parcial en `user_bank_agent_assignments` para `(user_id, bank_agent_id)` donde `is_active = true`. MySQL 8 soporta índices parciales funcionales; MariaDB usa columna virtual con UNIQUE.

**Rationale**: El modelo es más simple que un CHECK complejo y ambos motores lo soportan.

**Alternatives considered**: Validación solo en aplicación — rechazada por riesgo de race condition; triggers — rechazados por menor portabilidad.

## Force Password Change

**Decision**: Columna `password_changed_at` nullable en `users`. El middleware `AuthenticateJwtSession` redirige a cambio de contraseña si es null y la ruta no es `/password/change`. Al cambiar se actualiza el timestamp.

**Rationale**: Reutiliza la infraestructura JWT existente sin agregar flujo de autenticación nuevo. El operador ya está autenticado cuando se le exige el cambio.

**Alternatives considered**: Token de un solo uso por correo — rechazado porque no hay recuperación por correo; flag booleano — rechazado porque no permite auditoría de cuándo se cambió.

## Sources

- [Laravel 13 docs](https://laravel.com/docs/13.x)
- [MySQL partial indexes](https://dev.mysql.com/doc/refman/8.4/en/create-index.html)
- [MariaDB virtual columns](https://mariadb.com/kb/en/virtual-computed-columns/)

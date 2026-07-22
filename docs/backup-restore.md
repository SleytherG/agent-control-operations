# Guía de Backup y Restauración

## Tablas a respaldar

- `users`: usuarios activos/inactivos con hashes de contraseñas.
- `auth_sessions`: sesiones activas, expiradas y revocadas.
- `session_events`: eventos append-only de cada sesión.
- `audit_logs`: trazabilidad de acciones administrativas.

## Procedimiento de backup (MySQL)

```bash
mysqldump --single-transaction --routines --triggers \
  database_name users auth_sessions session_events audit_logs > backup.sql
```

## Procedimiento de restauración

```bash
mysql database_name < backup.sql
```

## Consideraciones

- Los secretos (JWT_SIGNING_KEY, REFRESH_PEPPER) se mantienen en .env y no en la BD.
- Las cookies de sesión activas serán inválidas tras rotar la clave JWT.
- Verificar la integridad de registros huérfanos (sessions sin user, events sin session).

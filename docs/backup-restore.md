# Guía de Backup y Restauración

## Tablas a respaldar

- `users`: usuarios activos/inactivos con hashes de contraseñas.
- `auth_sessions`: sesiones activas, expiradas y revocadas.
- `session_events`: eventos append-only de cada sesión.
- `audit_logs`: trazabilidad de acciones administrativas.

## Procedimiento de backup (PostgreSQL administrado por Supabase)

La política DEBE combinar las copias administradas disponibles en el plan de Supabase con una
exportación lógica periódica y cifrada. Las credenciales se suministran mediante variables de entorno
seguras (`PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`) y nunca se escriben en comandos,
scripts versionados o archivos de documentación.

```bash
pg_dump --format=custom --no-owner --no-acl --file=backup.dump "$PGDATABASE"
```

## Procedimiento de restauración

```bash
pg_restore --clean --if-exists --no-owner --no-acl --dbname="$PGDATABASE" backup.dump
```

## Consideraciones

- Los secretos (`JWT_SIGNING_KEY`, `REFRESH_PEPPER` y credenciales PostgreSQL) se mantienen en
  variables de entorno seguras y no en la BD ni en el repositorio.
- Las cookies de sesión activas serán inválidas tras rotar la clave JWT.
- Verificar la integridad de registros huérfanos (sessions sin user, events sin session).
- Ensayar periódicamente la restauración en una instancia PostgreSQL aislada y registrar el resultado.
- Confirmar la retención y el alcance de backups incluidos en el plan de Supabase contratado.

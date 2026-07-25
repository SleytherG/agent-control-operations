# Quickstart: Migración a PostgreSQL y Despliegue Render

**Feature**: 010-migrate-postgresql-render | **Date**: 2026-07-25

## Prerrequisitos

- PHP 8.3+, Composer, Node.js (solo build)
- Docker (para build de imagen y pruebas locales)
- Cuenta Supabase con proyecto en sa-east-1 (PostgreSQL)
- Cuenta Render (plan Free)
- CLI de Supabase o panel web para obtener credenciales
- `psql` o `pg_dump`/`pg_restore` instalados para backup
- Acceso de solo lectura a MySQL/MariaDB actual (para inventario, no requerido para migración)

## Variables de Entorno Requeridas

```bash
# PostgreSQL (Render Secrets / .env local)
DB_CONNECTION=pgsql
DB_HOST=[SUPABASE_SESSION_POOLER_HOST]
DB_PORT=5432
DB_DATABASE=[SUPABASE_DATABASE]
DB_USERNAME=[SUPABASE_USERNAME]
DB_PASSWORD=[SUPABASE_PASSWORD]
DB_SSLMODE=require

# Sesión y caché
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

# Aplicación
APP_NAME=AgenteFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://[render-app].onrender.com
APP_KEY=[stable-key]

# JWT (valores existentes conservados)
JWT_SIGNING_KEY=[existing-key]
JWT_ISSUER=[existing-issuer]
JWT_AUDIENCE=[existing-audience]
JWT_ACCESS_TTL=300
JWT_ABSOLUTE_SESSION_TTL=28800
REFRESH_PEPPER=[existing-pepper]

# Cookies seguras para HTTPS de Render
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=*

# Password reset (conservar configuración)
PASSWORD_RESET_TTL_SECONDS=3600
PASSWORD_RESET_TEMPORARY_LENGTH=16
PASSWORD_RESET_STEP_UP_MAX_ATTEMPTS=5
PASSWORD_RESET_STEP_UP_DECAY_SECONDS=60
```

## Runbook 1: Preparación Local

### Paso 1: Verificar extensión pdo_pgsql

```bash
php -m | grep pdo_pgsql
# Debe mostrar: pdo_pgsql
```

### Paso 2: Probar conexión PostgreSQL local

```bash
# Usar credenciales de Supabase (placeholders)
psql "postgresql://[SUPABASE_USERNAME]:[SUPABASE_PASSWORD]@[SUPABASE_HOST]:5432/[SUPABASE_DATABASE]?sslmode=require" -c "SELECT version();"
```

### Paso 3: Migraciones en PostgreSQL

```bash
# Configurar .env temporal para PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=[SUPABASE_SESSION_POOLER_HOST]
DB_PORT=5432
DB_DATABASE=[SUPABASE_DATABASE]
DB_USERNAME=[SUPABASE_USERNAME]
DB_PASSWORD=[SUPABASE_PASSWORD]
DB_SSLMODE=require

# Ejecutar migraciones
php artisan migrate:fresh --seed
# Verificar: debe mostrar "Migration table created successfully" y ejecutar 29 migraciones
# Verificar: seeders deben ejecutarse sin errores
```

### Paso 4: Verificar esquema

```bash
php artisan tinker --execute="
echo 'Tablas: ' . count(Schema::getAllTables()) . PHP_EOL;
echo 'FK: ' . DB::select('SELECT count(*) as cnt FROM pg_constraint WHERE contype = \\'f\\'')[0]->cnt . PHP_EOL;
"
```

### Paso 5: Ejecutar suite de pruebas contra PostgreSQL

```bash
php artisan test --env=testing-pgsql
# Todas las pruebas deben pasar (las que dependen de DB)
```

## Runbook 2: Ensayo de Despliegue

### Paso 1: Construir imagen Docker

```bash
docker build -t agenteflow:latest .
```

### Paso 2: Ejecutar localmente contra Supabase

```bash
docker run --rm -p 8080:80 \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=[SUPABASE_SESSION_POOLER_HOST] \
  -e DB_PORT=5432 \
  -e DB_DATABASE=[SUPABASE_DATABASE] \
  -e DB_USERNAME=[SUPABASE_USERNAME] \
  -e DB_PASSWORD=[SUPABASE_PASSWORD] \
  -e DB_SSLMODE=require \
  -e APP_KEY=[stable-key] \
  -e APP_URL=http://localhost:8080 \
  -e JWT_SIGNING_KEY=[key] \
  -e REFRESH_PEPPER=[pepper] \
  agenteflow:latest
```

### Paso 3: Smoke test manual

```bash
# Health check
curl http://localhost:8080/up
# Debe devolver 200

# Readiness check  
curl http://localhost:8080/health
# Debe devolver 200 con {"database":"connected"}

# Login page
curl -I http://localhost:8080/login
# Debe devolver 200
```

## Runbook 3: Corte Definitivo (versión datos dummy)

### Pre-corte (T-30 min)

1. **Comunicar**: Informar que no hay usuarios reales; el corte es técnico.
2. **Backup MySQL** (opcional para datos dummy, obligatorio si hubiera reales):
   ```bash
   mysqldump --single-transaction --routines --triggers \
     control_operaciones > pre_migration_backup.sql
   ```
3. **Verificar conectividad PostgreSQL**: `php artisan tinker --execute="DB::connection()->getPdo();"`

### Corte (T-0)

1. **Migrar esquema en PostgreSQL**:
   ```bash
   php artisan migrate:fresh --seed --force
   ```
2. **Verificar seeders**:
   ```bash
   php artisan tinker --execute="echo 'Users: ' . App\Modules\IdentityAccess\Models\User::count();"
   # Debe mostrar al menos 2 usuarios (admin + operador)
   ```
3. **Desplegar en Render**:
   ```bash
   # Push a GitHub; Render hace auto-deploy desde el repo configurado
   # O usar render deploy manual desde dashboard
   ```
4. **Verificar health check**: `curl https://[render-app].onrender.com/up`
5. **Smoke test HTTPS**: Login, dashboard, operaciones, cierre.

### Post-corte (T+15 min)

1. **Monitorizar errores**: Revisar logs de Render (Dashboard → Logs).
2. **Verificar cookies HTTPS**: Login → inspeccionar cookies `Secure; SameSite=Lax`.
3. **Probar arranque en frío**: Esperar inactividad → verificar que el servicio responde.
4. **Declarar corte completo**: PostgreSQL es la única base productiva.

## Runbook 4: Rollback

### Condiciones de activación

- Health check falla consistentemente (>3 intentos en 5 min)
- Login no funciona para todos los usuarios
- Dashboard muestra datos incorrectos (comparado con backup)
- Error 500 en operaciones de escritura (registro, cierre)
- Deadlocks o timeouts persistentes que no existían en MySQL

### Procedimiento

1. **Decisión**: Responsable técnico decide rollback en ≤15 min del corte.
2. **Detener Render**: Pausar el Web Service desde dashboard.
3. **Restaurar MySQL**:
   ```bash
   # Si se hizo backup en pre-corte
   mysql control_operaciones < pre_migration_backup.sql
   ```
4. **Revertir deploy**: Desplegar commit anterior en Render (sin cambios pgsql).
5. **Verificar MySQL**: `php artisan migrate:status` muestra todas las migraciones aplicadas.
6. **Abrir aplicación en MySQL**: Iniciar servidor local o re-desplegar versión anterior en Render.
7. **Comunicar**: Informar resultado del rollback.

### Tratamiento de escrituras en PostgreSQL post-corte

Como no hay operaciones reales (datos dummy), no hay riesgo de divergencia. Si existieran,
el runbook documenta que deben exportarse como respaldo y re-ejecutarse manualmente en MySQL
tras el rollback.

## Runbook 5: Despliegue Continuo

### Proceso de release

1. **Build**: GitHub push → Render detecta → `docker build`
2. **Migrate**: Entrypoint ejecuta `php artisan migrate --force --isolated`
3. **Cache**: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
4. **Start**: PHP-FPM + Nginx inician
5. **Health**: Render verifica `GET /up` → 200

### Proceso de migraciones controladas

```bash
# Entrypoint del Dockerfile
php artisan migrate --force --isolated
# --isolated: lock atómico para prevenir ejecución concurrente multi-instancia
# --force: necesario en producción (sin confirmación interactiva)
```

### Rollback de migraciones

```bash
php artisan migrate:rollback --step=1
# Solo para desarrollo/ensayo; en producción se usa deploy rollback (restaurar backup)
```

## Verificación del Flujo de Demostración

Ejecutar secuencialmente y verificar cada paso:

1. ✅ `php artisan migrate:fresh --seed` (PostgreSQL)
2. ✅ `php artisan serve` (local o Docker)
3. ✅ GET `/login` → 200, muestra formulario
4. ✅ POST `/login` con admin@controloperaciones.local / password → redirect a dashboard
5. ✅ GET `/dashboard` → muestra dashboard de operador
6. ✅ POST `/operations` → registra operación
7. ✅ GET `/operations` → lista la operación registrada
8. ✅ POST `/operations/{id}/annul` → anula la operación
9. ✅ GET `/admin/dashboard` → dashboard admin con datos
10. ✅ POST `/daily-closures` → genera cierre diario
11. ✅ POST `/daily-closures/{id}/confirm` → confirma cierre
12. ✅ POST `/refresh` → rota JWT y refresh token
13. ✅ `curl https://[app].onrender.com/up` → 200
14. ✅ `curl https://[app].onrender.com/health` → {"database":"connected"}
15. ✅ Simular rollback: restaurar MySQL, revertir deploy, verificar login

## Referencias

- [Data Model](./data-model.md): Cambios de tipos, secuencias, migraciones
- [Research](./research.md): Inventarios, matrices de compatibilidad, decisiones
- [Plan](./plan.md): Constitution Check, Technical Context
- [Spec](./spec.md): Requisitos funcionales, criterios de aceptación

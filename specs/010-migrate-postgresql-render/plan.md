# Implementation Plan: Migración Integral a PostgreSQL y Render

**Branch**: `010-migrate-postgresql-render` | **Date**: 2026-07-25 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/010-migrate-postgresql-render/spec.md`

## Summary

Migrar la persistencia desde MySQL/MariaDB hacia PostgreSQL administrado por Supabase y preparar el
despliegue en Render como Web Service Dockerizado. La base actual contiene exclusivamente datos dummy
(usuarios Faker, admin hardcodeado, agentes ficticios, cero operaciones reales), por lo que se
aplicará la ruta de esquema limpio con seeders controlados, eliminando la complejidad de ETL y
reconciliación de datos reales. El cambio es exclusivamente arquitectónico sin modificar reglas de
negocio, roles, autorización ni modelo funcional. Supabase actuará únicamente como proveedor
administrado y reemplazable de PostgreSQL, con Data API deshabilitada.

## Technical Context

**Language/Version**: PHP 8.3+

**Primary Dependencies**: Laravel 13.8+, lcobucci/jwt 5.0, Eloquent ORM, Blade, Vite 8

**Storage**: PostgreSQL administrado por Supabase en sa-east-1 (São Paulo), conexión mediante
Session Pooler con SSL. Esquema `public` de Laravel. Sin dependencia de Supabase Auth, Storage,
Realtime, Edge Functions ni Data API.

**Data Platform Boundary**: Laravel conserva autenticación, emisión/validación de JWT y refresh
tokens, autorización, reglas de negocio, validación, auditoría y acceso a datos. Supabase solo
proporciona PostgreSQL administrado.

**Time & Money**: Almacenamiento en UTC con presentación `America/Lima`. Importes en `NUMERIC(18,2)`.
Los periodos y `business_date` conservan sus límites sin desplazamiento horario.

**Authentication & Session**: JWT access token de 5 minutos configurable, refresh tokens rotatorios
con hash almacenado, renovación explícita, revocación, logout. Sesiones y refresh tokens existentes
se revocarán durante el corte. Drivers: sesión y caché en `database` (PostgreSQL), cola `sync`.

**Testing**: PHPUnit con PostgreSQL real para migraciones, locks, transacciones, FK, únicos,
decimales, JSON, fechas, dashboards, cierres y sesiones. SQLite sigue usándose para desarrollo local
rápido; PostgreSQL es obligatorio en CI y validación pre-corte.

**Target Platform**: Render Web Service (Docker, Nginx + PHP-FPM), región São Paulo, plan Free
inicial con tolerancia a suspensión y arranques en frío, migrando a plan pago con datos reales.

**Project Type**: Web service monolítico Laravel con Blade server-rendered

**Performance Goals**: Login < 2s, registro de operación < 1s, dashboards < 3s, cierre diario < 5s
desde São Paulo hacia Supabase en la misma región (~5ms latencia de red).

**Constraints**: Render Free: suspensión por inactividad, filesystem efímero, 512 MB RAM, arranque
en frío. Supabase Free: 500 MB almacenamiento, pausa tras inactividad, 2 proyectos gratuitos. Sin
Redis, sin colas externas, sin WebSockets.

**Scale/Scope**: ~20 tablas, ~17 entidades activas, cero operaciones reales iniciales, 2 roles
(ADMINISTRADOR_PROPIETARIO, OPERADOR), una organización, despliegue single-instance.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Specification completeness**: Spec 010 aprobada con 5 historias de usuario, 20 reglas de negocio,
  45 FRs, 70 ACs, 15 SCs, 14 edge cases y exclusiones explícitas. Este plan contiene las decisiones
  de implementación y despliegue.
- **Increment classification**: Cambio arquitectónico sin cambio funcional. No se añaden capacidades
  nuevas de negocio.
- **III. Portabilidad económica**: PASS. PostgreSQL administrado por Supabase como proveedor
  reemplazable; Render Free para demo inicial; sin Redis, Kubernetes, WebSockets ni colas externas.
  El servicio PostgreSQL es la excepción aprobada a servicios externos de pago.
- **V. Seguridad del servidor**: PASS. Laravel conserva autenticación, JWT, autorización,
  validación, auditoría y acceso a datos. Credenciales solo en variables de entorno seguras.
  Data API deshabilitada. Sin Supabase Auth ni servicios propietarios.
- **VI. Sesiones seguras**: PASS. JWT 5 min, refresh rotatorios, revocación, hash almacenado.
  El corte revoca todas las sesiones previas; usuarios deben reautenticarse.
- **VII. Integridad y trazabilidad**: PASS. FK, transacciones, migraciones versionadas, auditoría
  conservada. Sin cambios manuales en producción.
- **VIII. Exactitud monetaria**: PASS. `NUMERIC(18,2)` para todos los importes. Sin float/double.
  Presentación `America/Lima`. Periodos con límites explícitos.
- **X. Pruebas obligatorias**: PASS. Suite completa contra PostgreSQL real. Migraciones up/down
  probadas. SQLite no sustituye pruebas de compatibilidad de motor.
- **XII. Observabilidad y recuperación**: PASS. Liveness/readiness, logs sanitizados, backups
  externos con pg_dump, restauración ensayada, rollback documentado y probado.
- **XIV. Simplicidad del dominio**: PASS. Sin nuevas entidades, catálogos ni segmentaciones.
  Solo cambios de persistencia.
- **Deployment compatibility**: PASS. Render con Docker, PostgreSQL administrado, SSL, HTTPS, assets
  precompilados, solo `public/` expuesto. Sin Node.js en runtime.
- **Database governance**: PASS. PostgreSQL canónico. FK, transacciones, migraciones versionadas,
  `NUMERIC`, pruebas reales, backups, restauración.
- **Supabase boundary**: PASS. Solo PostgreSQL administrado. Data API deshabilitada. Sin coupling a
  Auth, Storage, Realtime, Edge Functions. Credenciales externas.
- **All other principles (I, II, IV, IX, XI, XIII)**: PASS. Sin cambios funcionales, sin nuevos
  datos, sin cambios de UI, sin nuevas dependencias de infraestructura.

## Project Structure

### Documentation (this feature)

```text
specs/010-migrate-postgresql-render/
├── plan.md              # This file
├── research.md          # Phase 0 output (inventory, compatibility matrices, decisions)
├── data-model.md        # Phase 1 output (type mapping, sequence strategy, schema changes)
├── quickstart.md        # Phase 1 output (runbooks, validation guide)
├── checklists/
│   └── requirements.md  # Spec quality checklist
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
# Files to CREATE
Dockerfile                    # Multi-stage: PHP-FPM + Nginx, pdo_pgsql
render.yaml                   # Render Blueprint: web service, health, env vars
docker/                       # Docker config (nginx.conf, php.ini, entrypoint)
  ├── nginx.conf
  ├── php.ini
  └── entrypoint.sh

# Files to MODIFY
config/database.php           # Update pgsql driver config; set PostgreSQL as default for production
app/Modules/Reporting/Services/DashboardQueryService.php  # Add PostgreSQL DATE_FORMAT → TO_CHAR branch
app/Modules/IdentityAccess/Services/AuthTransactionRunner.php  # Update transient error detection for PostgreSQL
database/migrations/2026_07_23_000009_drop_legacy_tables.php  # Add PostgreSQL driver check, drop orphan indexes

# Database migrations that will run natively on PostgreSQL
database/migrations/*.php     # All 29 migrations reviewed; char→varchar, binary→bytea managed by Laravel

# Tests to CREATE
tests/Integration/Migrations/PostgresCompatibilityTest.php  # migrate:fresh on PostgreSQL, FK, unique, decimal
tests/Feature/Migration/MigrationSmokeTest.php              # Full flow validation against PostgreSQL
```

**Structure Decision**: El código de aplicación no requiere cambios estructurales nuevos. Las
modificaciones se limitan a: (a) adaptar consultas con DATE_FORMAT, (b) corregir detección de
errores transitorios, (c) limpiar índices huérfanos en migración 000009. El resto es
configuración, Dockerfile y nuevas pruebas de compatibilidad PostgreSQL.

## Exception Tracking

> No constitutional violations. No exceptions required.

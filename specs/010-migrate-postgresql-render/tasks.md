# Tasks: Migración Integral a PostgreSQL y Render

**Input**: Design documents from `/specs/010-migrate-postgresql-render/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, quickstart.md

**Tests**: Automated tests are REQUIRED for every acceptance scenario. PostgreSQL compatibility,
migration up/down, FK, unique, decimal, JSON, date, transaction, lock, dashboard, closing, and
session tests are mandatory. SQLite alone is insufficient for migration validation.

**Organization**: Tasks are organized in 12 sequential phases following the cutover lifecycle:
security prep → audit → environment → schema → code → Supabase security → data migration →
rehearsal → Docker/Render → cutover → post-validation → cleanup.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4, US5)
- Include exact file paths in descriptions
- Each task includes: path, dependencies, environment, test command, completion criteria, risk,
  reversibility

---

## Phase 1: Seguridad y Preparación (Pre-Migration Baseline)

**Purpose**: Secure credentials, establish backups, and create a documented restoration point
before any schema or code change.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T001 | | | Scan Git history for exposed credentials (passwords, tokens, connection strings) and document findings | `.specify/bugs/` or report in `specs/010-migrate-postgresql-render/` | None | Local | `git log -p \| grep -iE '(password\|secret\|token\|PRIVATE\|KEY\|DATABASE_URL)' \| wc -l` returns 0 after exclusions | Scan complete; any real credential found is rotated before proceeding | HIGH — exposed credentials invalidate security posture | Irreversible (scan is read-only; rotation is permanent) |
| T002 | | | Rotate any credential found in T001: generate new values, update `.env`, verify app works, delete old credential from provider | `.env` (local, ignored by Git) | T001 | Local | `php artisan tinker --execute="return app()->environment();"` succeeds after rotation | All exposed credentials replaced with new values; old credentials revoked at provider | CRITICAL — stale credentials enable unauthorized access | If app breaks, old credential still valid until explicitly revoked at provider |
| T003 | | | Create MySQL backup script leveraging existing `specs/008-simplify-agent-operations/scripts/backup.sh` logic, add checksum verification | `specs/010-migrate-postgresql-render/scripts/backup.sh` | None | MySQL | `bash scripts/backup.sh && sha256sum backup.sql` produces valid checksum | Backup file created, checksum recorded, restoration tested on a disposable MySQL instance | HIGH — no valid backup blocks rollback | Yes: delete backup file |
| T004 | [P] | | Execute MySQL backup and store outside repository; record filename, checksum, row counts per table, MySQL version | `specs/010-migrate-postgresql-render/backup-manifest.md` | T003 | MySQL | `mysql -e "SELECT VERSION(); SHOW TABLE STATUS;" > mysql_pre_migration_snapshot.txt` | Manifest populated with version, checksum, row counts, timestamp | HIGH — missing metadata invalidates rollback verification | Manifest is append-only record; backup is deletable |
| T005 | [P] | | Document MySQL credentials and connection parameters used in current `.env` (masked) into a restoration guide | `specs/010-migrate-postgresql-render/restoration-guide.md` | None | Local | Manual review that guide contains: host, port, database name, user (masked), SSL status, PHP extension used | Guide answers: how to reconnect to MySQL, restore backup, verify app with MySQL | LOW — informational only | N/A |
| T006 | | | Classify all current data as dummy (per clarification) and document which seeders recreate each dataset | `specs/010-migrate-postgresql-render/data-classification.md` | None | Local | `grep -c "dummy" data-classification.md` returns ≥1 and matches DatabaseSeeder expectations | Every dataset mapped to a seeder or marked as empty; decision recorded | LOW — data already confirmed dummy | N/A |

**Checkpoint**: Credentials secured, backup created and verified, data classification documented.

---

## Phase 2: Auditoría MySQL (Full Inventory)

**Purpose**: Produce a complete, machine-verifiable inventory of the MySQL origin to drive
compatibility decisions.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T007 | | US1 | Generate schema inventory script that reads `information_schema` and outputs every table, column, type, default, nullable, PK, FK, unique, index, charset, collation, auto_increment | `specs/010-migrate-postgresql-render/scripts/schema-inventory.sh` | None | MySQL | `bash scripts/schema-inventory.sh > schema-inventory.csv && wc -l schema-inventory.csv > 200` | CSV with ≥200 rows covering all 17+ active tables; every column documented | MEDIUM — incomplete inventory misses compatibility issues | Yes: delete CSV |
| T008 | [P] | US1 | Audit all migrations for MySQL-specific constructs: unsignedInteger/BigInteger, tinyInteger, binary(N), char(N), decimal, json, date, engine InnoDB, charset, collation, raw SQL | `specs/010-migrate-postgresql-render/migration-audit.md` (report) | research.md | Local | `grep -c "unsigned\|tinyInteger\|binary\|char\|Engine\|charset\|collation\|DB::statement" migration-audit.md` ≥ findings count from research | Every migration file linked to its MySQL-specific construct with PostgreSQL equivalent and risk level | HIGH — missed construct causes migration failure in Phase 4 | N/A (report) |
| T009 | | US1 | Run MySQL-specific SQL audit across entire `app/` directory: DATE_FORMAT, raw queries, lockForUpdate usage, DB::transaction counts, isTransientError patterns | `specs/010-migrate-postgresql-render/sql-audit.md` | research.md | Local | `grep -c "DATE_FORMAT\|TO_CHAR\|isTransientError\|lockForUpdate\|DB::transaction\|selectRaw\|whereRaw\|orderByRaw" sql-audit.md` ≥15 | Every MySQL-specific SQL site documented with file, line, snippet, PostgreSQL equivalent, action required | HIGH — unadapted SQL breaks queries silently or with errors | N/A (report) |
| T010 | [P] | US1 | Inventory all Eloquent casts (decimal, array, boolean, datetime, date, encrypted, enum), model timestamp settings, and JSON column usage across all models | `specs/010-migrate-postgresql-render/model-cast-audit.md` | research.md | Local | `grep -c "decimal\|array\|boolean\|datetime\|date\|timestamps.*false" model-cast-audit.md` ≥10 | Every model linked to its casts with PostgreSQL compatibility assessment | MEDIUM — decimal:2 cast behavior may differ between drivers | N/A (report) |
| T011 | | US1 | Run existing test suite against MySQL and capture baseline: pass count, fail count, skipped count, duration per suite | `specs/010-migrate-postgresql-render/baseline-tests.txt` | None | MySQL | `php artisan test > baseline-tests.txt 2>&1; echo "Exit: $?"` | Baseline recorded with pass/fail/skip counts for Unit, Feature, Integration | LOW — establishes comparison point for PostgreSQL test runs | N/A (record) |

**Checkpoint**: Full MySQL inventory complete. All compatibility risks identified and documented.

---

## Phase 3: Entorno PostgreSQL Reproducible

**Purpose**: Create a local and CI PostgreSQL environment where every subsequent task can be verified.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T012 | | US4 | Install and configure local PostgreSQL (via Homebrew, Docker, or Supabase CLI); create database `control_operaciones_test` | `config/database.php` (add pgsql_test connection) | None | Local | `psql -d control_operaciones_test -c "SELECT 1;"` returns 1 | PostgreSQL running locally; database created and accessible | LOW — local env only | Yes: drop database |
| T013 | [P] | US4 | Verify `pdo_pgsql` PHP extension is loaded and functional | None (runtime check) | T012 | Local | `php -m \| grep pdo_pgsql && php -r "new PDO('pgsql:host=localhost;dbname=control_operaciones_test', ...);"` succeeds | Extension loaded; PDO connection to PostgreSQL works | HIGH — without pdo_pgsql, Laravel cannot connect | Yes: disable extension |
| T014 | | US4 | Create `.env.pgsql` with PostgreSQL connection variables using placeholders (no real secrets); reference from `.env.testing` or `phpunit.xml` | `.env.pgsql`, `phpunit.xml` (update) | T012, T013 | Local | `php -r "echo parse_ini_file('.env.pgsql')['DB_CONNECTION'];"` outputs `pgsql` | Env file created with all required PG variables; test config references it | MEDIUM — misconfiguration blocks test suite | Yes: delete .env.pgsql |
| T015 | | US3 | Execute `migrate:fresh` on PostgreSQL test database | None (command) | T014 | Local, PostgreSQL | `php artisan migrate:fresh --env=testing-pgsql 2>&1` exits 0; `php artisan migrate:status` shows all migrations as `Ran` | All 29 migrations applied successfully on PostgreSQL; zero errors | CRITICAL — migration failure blocks entire migration | `migrate:rollback` or `migrate:reset` |
| T016 | | US3 | Execute `db:seed` on PostgreSQL and verify all seeders complete without errors | None (command) | T015 | Local, PostgreSQL | `php artisan db:seed --env=testing-pgsql 2>&1` exits 0 | Organization, admin user, operator, agents, and operation types created | MEDIUM — seeder failure indicates type/cast incompatibility | `migrate:fresh` |

**Checkpoint**: PostgreSQL environment functional. All migrations and seeders execute without errors.

---

## Phase 4: Compatibilidad de Esquema

**Purpose**: Fix every schema-level incompatibility so `migrate:fresh --seed` runs cleanly on PostgreSQL.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T017 | | US3 | Fix orphan indexes in migration 000009: add explicit `$table->dropIndex()` for `bank_agent_id` and `store_id` indexes in `operations` and `daily_closures` before `dropColumn` | `database/migrations/2026_07_23_000009_drop_legacy_tables.php` | T015 | PostgreSQL | `php artisan migrate:fresh --env=testing-pgsql` exits 0 without "cannot drop column" error | Migration 000009 completes without index-related errors | CRITICAL — orphan indexes block entire migration | `migrate:rollback` |
| T018 | [P] | US3 | Add PostgreSQL driver branch to migration 000009 alongside existing SQLite check: `if (DB::getDriverName() === 'pgsql') { ... }` to handle Pg-specific index naming | `database/migrations/2026_07_23_000009_drop_legacy_tables.php` | T017 | PostgreSQL | `php artisan migrate:fresh --env=testing-pgsql` exits 0 | SQLite and PostgreSQL both pass 000009 without conditional issues | MEDIUM — missing driver check causes SQLite-specific assumptions on Pg | `migrate:rollback` |
| T019 | | US3 | Verify `binary(32)` columns (`ip_hash`, `token_hash`) store and retrieve correctly via Eloquent on PostgreSQL; create integration test | `tests/Integration/Migrations/BinaryColumnTest.php` | T015 | PostgreSQL | `php artisan test --filter=BinaryColumnTest --env=testing-pgsql` passes | Hash written and read back produces same bytes; no encoding corruption | HIGH — hash mismatch breaks auth, refresh tokens, session tracking | Yes: rollback test file |
| T020 | [P] | US3 | Verify `decimal(18,2)` columns preserve precision on PostgreSQL; insert, read, and sum values with known edge cases | `tests/Integration/Migrations/DecimalPrecisionTest.php` | T015 | PostgreSQL | `php artisan test --filter=DecimalPrecisionTest --env=testing-pgsql` passes | Values round-trip exactly; sums match expected; no float artifacts | MEDIUM — decimal precision loss corrupts monetary data | Yes: rollback test |
| T021 | [P] | US3 | Verify `json` column behavior on PostgreSQL (Eloquent array cast, read/write); compare with MySQL baseline from Phase 2 | `tests/Integration/Migrations/JsonColumnTest.php` | T015 | PostgreSQL | `php artisan test --filter=JsonColumnTest --env=testing-pgsql` passes | JSON arrays serialize/deserialize correctly; `json` vs `jsonb` behavior documented | LOW — JSON columns used only in session_events and audit_logs | N/A |
| T022 | [P] | US3 | Verify `date`, `datetime(6)`, `boolean`, `char(N)` types behave identically on PostgreSQL; test edge cases (NULL, default, range limits) | `tests/Integration/Migrations/TypeCompatibilityTest.php` | T015 | PostgreSQL | `php artisan test --filter=TypeCompatibilityTest --env=testing-pgsql` passes | All core types validate; differences from MySQL documented | MEDIUM — char(36) padding may differ (MySQL trims, PG pads) | Yes: rollback test |
| T023 | | US3 | Verify sequences auto-increment correctly after `migrate:fresh --seed` on PostgreSQL; IDs start at 1 and increment | `tests/Integration/Migrations/SequenceTest.php` | T016 | PostgreSQL | `php artisan test --filter=SequenceTest --env=testing-pgsql` passes | IDs sequential; no gaps from failed inserts; explicit ID inserts advance sequence | MEDIUM — sequence mismatch causes PK collisions after inserts | N/A |
| T024 | | US3 | Run full migration up/down cycle: `migrate:fresh`, `migrate:rollback --step=29`, `migrate` again | None (command) | T017-T023 | PostgreSQL | `php artisan migrate:fresh --env=testing-pgsql && php artisan migrate:rollback --env=testing-pgsql && php artisan migrate --env=testing-pgsql` exits 0 | All 29 migrations up, all reversible ones down, all up again | HIGH — irreversible migration blocks rollback | `migrate:reset` |

**Checkpoint**: Schema fully compatible. All types, constraints, indexes, and sequences work on PostgreSQL.

---

## Phase 5: Compatibilidad del Código

**Purpose**: Fix every application-level incompatibility so the full test suite passes against PostgreSQL.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T025 | | US3 | Replace MySQL `DATE_FORMAT` with PostgreSQL `TO_CHAR` in `DashboardQueryService::getDateExpression()`; add `pgsql` driver branch | `app/Modules/Reporting/Services/DashboardQueryService.php:244-263` | T009, T015 | PostgreSQL | `php artisan test --filter=DashboardQuery --env=testing-pgsql` passes | `GROUP BY day/week/month` returns correct date strings on PostgreSQL; SQLite and MySQL branches preserved | CRITICAL — all dashboards and evolution charts depend on this function | Yes: revert to commit before change |
| T026 | [P] | US3 | Update `AuthTransactionRunner::isTransientError()` to detect PostgreSQL deadlock (`40P01`) and lock timeout (`55P03`) error codes alongside MySQL strings | `app/Modules/IdentityAccess/Services/AuthTransactionRunner.php:39` | T009 | PostgreSQL | `php artisan test --filter=AuthTransaction --env=testing-pgsql` passes | Deadlock and lock timeout retries work on PostgreSQL | HIGH — missed transient errors cause unnecessary 500s under concurrency | Yes: revert |
| T027 | [P] | US3 | Audit all `->selectRaw()`, `->orderByRaw()`, `->whereRaw()` calls for non-portable syntax; verify each against PostgreSQL | `specs/010-migrate-postgresql-render/raw-query-audit.md` (report) | T009 | PostgreSQL | Each raw query test passes on PostgreSQL; report documents any adaptations needed | All raw queries verified; adaptations documented and tested | MEDIUM — raw SQL that works in MySQL may silently return wrong results in PG | Report is read-only |
| T028 | | US3 | Run full Feature test suite against PostgreSQL; triage failures into: expected (type differences), fixable (code changes), and pre-existing (unrelated) | `tests/Feature/` | T025, T026, T027 | PostgreSQL | `php artisan test --env=testing-pgsql 2>&1 \| tee pgsql-feature-results.txt` | All migration-related failures identified and classified; no unresolved P0/P1 failures from migration | HIGH — unreported test failures hide functional regressions | N/A (report) |
| T029 | | US3 | Fix any code-level failures found in T028 that are caused by PostgreSQL differences (not pre-existing) | Files identified in T028 | T028 | PostgreSQL | Re-run failing tests after fixes: `php artisan test --filter=<fixed> --env=testing-pgsql` passes | All migration-caused test failures resolved; pre-existing failures documented as known | CRITICAL — functional regressions invalidate the migration | Yes: revert individual fixes |
| T030 | | US3 | Run Integration test suite against PostgreSQL; verify locks, transactions, concurrency patterns work | `tests/Integration/` | T029 | PostgreSQL | `php artisan test --filter=Integration --env=testing-pgsql` passes for migration-related tests | Concurrency, locks, and transaction tests pass on PostgreSQL | MEDIUM — lockForUpdate/Pessimistic Locking semantics may differ subtly | N/A |
| T031 | | US3 | Create dashboard regression test: execute all DashboardQueryService methods against PostgreSQL seeded data and compare results structure with MySQL baseline | `tests/Feature/Migration/DashboardRegressionTest.php` | T025, T016 | PostgreSQL | `php artisan test --filter=DashboardRegressionTest --env=testing-pgsql` passes | Aggregations, date groupings, and totals match expected structure | HIGH — incorrect aggregations misrepresent operational data | Yes: rollback test |
| T032 | [P] | US3 | Create daily closing regression test: generate and confirm a closure on PostgreSQL; verify all decimal fields, operation counts, and pivot relationships | `tests/Feature/Migration/ClosingRegressionTest.php` | T029, T016 | PostgreSQL | `php artisan test --filter=ClosingRegressionTest --env=testing-pgsql` passes | Closure created, operations linked, amounts correct, status transitions work | HIGH — closing failures block critical workflow | Yes: rollback test |

**Checkpoint**: All application code adapted. Full test suite passes against PostgreSQL.

---

## Phase 6: Seguridad Supabase

**Purpose**: Configure Supabase project with SSL, minimum privilege, disabled Data API, and verified pooler connectivity.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T033 | | US4 | Disable Data API in Supabase project (Dashboard → API Settings → Disable); verify no tables exposed via REST | Supabase Dashboard (manual) | Supabase project | Supabase | `curl -s "https://[SUPABASE_HOST]/rest/v1/" \| grep -c "404\|405"` returns non-zero | REST endpoint returns 404/405; no table data accessible anonymously | CRITICAL — exposed Data API leaks operational data | Yes: re-enable |
| T034 | [P] | US4 | Create application PostgreSQL role with SELECT, INSERT, UPDATE, DELETE on `public` schema tables; revoke CREATE, DROP, ALTER from application role | SQL script in `specs/010-migrate-postgresql-render/sql/create-app-role.sql` | Supabase project | Supabase | Execute script via Supabase SQL Editor; `SELECT has_schema_privilege('app_role', 'public', 'usage')` returns true | Application role exists with minimum required privileges; migration role (postgres) used only during deploys | HIGH — over-privileged role enables schema destruction from app | Yes: drop role |
| T035 | | US4 | Verify SSL connectivity to Supabase from local environment: configure `sslmode=require`, test connection | None (manual) | Local → Supabase | `psql "postgresql://[USER]:[PASS]@[HOST]:5432/[DB]?sslmode=require" -c "SELECT 1;"` returns 1 | SSL handshake completes; encrypted connection established | CRITICAL — unencrypted connection violates BR-011 | Irreversible (SSL is required) |
| T036 | [P] | US4 | Test connectivity using Supabase Session Pooler (port 5432); verify PDO, prepared statements, and transactions work | `.env.pgsql` (update DB_HOST to pooler) | T035 | Local → Supabase Pooler | `php artisan tinker --env=testing-pgsql --execute="DB::transaction(fn() => DB::select('SELECT 1'));"` returns [{"?column?":1}] | Pooler connection works; prepared statements not rejected; transactions commit | HIGH — pooler incompatibility blocks Render deployment | Yes: switch to direct connection |
| T037 | | US4 | Document Supabase limits: storage cap, connection pool size, pause policy, backup retention; record current plan details | `specs/010-migrate-postgresql-render/supabase-limits.md` | Supabase project | Supabase | Document created with limits from Supabase Dashboard → Settings → Usage | Limits documented; alert thresholds noted (e.g., 80% storage) | LOW — informational until limits approached | N/A |
| T038 | | US4 | Create observability query log: enable `pg_stat_statements` extension or Supabase query performance dashboard; verify slow query visibility | Supabase Dashboard → Performance | T037 | Supabase | Manual: Performance dashboard shows query stats after running test suite | Slow queries, connection counts, and cache hit ratios visible | LOW — enables Phase 11 monitoring | N/A |

**Checkpoint**: Supabase secured. SSL enforced, roles created, Data API off, pooler tested, limits documented.

---

## Phase 7: Migrador de Datos (Preparación para Futuro)

**Purpose**: Build the controlled migration infrastructure for data transfer, validated even though
current data is dummy — the migrator must be ready when real data exists.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T039 | | US2 | Configure dual database connections in `config/database.php`: `mysql_legacy` (read-only, origin) and `pgsql` (default, destination) | `config/database.php` | None | PostgreSQL, MySQL | `php artisan tinker --execute="DB::connection('mysql_legacy')->getPdo(); DB::connection('pgsql')->getPdo();"` succeeds for both | Both connections active from same Laravel process | LOW — configuration only; no data moved | Yes: remove connection |
| T040 | | US2 | Create Artisan command `db:migrate-data` with `--dry-run`, `--batch-size`, `--only-table`, `--verify` options and sanitized progress output | `app/Console/Commands/MigrateDataCommand.php` | T039 | PostgreSQL, MySQL | `php artisan db:migrate-data --dry-run --batch-size=100` exits 0 with table list and row counts | Command registered; dry-run outputs table discovery, row counts, dependency order without modifying data | MEDIUM — command that moves data incorrectly corrupts destination | `migrate:fresh --seed` on destination |
| T041 | | US2 | Implement topological dependency resolver: compute table load order from FK relationships using `information_schema` or Laravel Schema | `app/Services/Migration/DependencyResolver.php` | T040 | PostgreSQL, MySQL | `php artisan db:migrate-data --dry-run` outputs tables in valid FK order (parents before children) | All tables ordered by dependency; no FK violations on insert | HIGH — wrong order causes FK constraint errors | Yes: fix resolver logic |
| T042 | [P] | US2 | Implement type transformations: boolean (0/1/true/false → BOOLEAN), binary → bytea, date → DATE, datetime → TIMESTAMP, decimal → NUMERIC | `app/Services/Migration/TypeTransformer.php` | T041 | PostgreSQL, MySQL | Unit test: `php artisan test --filter=TypeTransformerTest` passes for all type mappings | Every MySQL→PG type mapping tested with edge cases | HIGH — wrong transformation silently corrupts data | Yes: fix transformer |
| T043 | | US2 | Implement batch transfer with transaction per batch, progress bar, error logging (sanitized), and resume-from capability | `app/Services/Migration/BatchTransferService.php` | T041, T042 | PostgreSQL, MySQL | `php artisan db:migrate-data --batch-size=100 --verify` transfers mock data correctly | All rows transferred; batch boundaries respected; errors logged without secrets; resume works | HIGH — batch failure without resume loses partial progress | `migrate:fresh --seed` resets destination |
| T044 | | US2 | Add `--verify` mode that compares row counts, checksums, and sample values between source and destination after transfer | In `MigrateDataCommand.php` and `BatchTransferService.php` | T043 | PostgreSQL, MySQL | `php artisan db:migrate-data --verify` reports per-table: source count, dest count, match status | Every table has verified count match or documented mismatch with reason | MEDIUM — verification gap allows silent data loss | N/A (read-only) |

**Checkpoint**: Data migrator operational. Ready for real data when needed. Dummy data handled by seeders in Phase 4.

---

## Phase 8: Ensayo Completo

**Purpose**: Execute a full end-to-end migration rehearsal on a disposable environment and prove
rollback works.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T045 | | US5 | Create rehearsal runbook: step-by-step script from backup to PostgreSQL-open, including timing markers and go/no-go checkpoints | `specs/010-migrate-postgresql-render/runbooks/rehearsal.md` | T024, T032, T044 | N/A (document) | Manual review verifies all steps from quickstart.md Runbook 2 are covered | Runbook executable by a single operator; every step has expected output and failure response | LOW — document only | N/A |
| T046 | | US5 | Execute clean rehearsal: `migrate:fresh --seed` on disposable Supabase project, run full test suite, verify all smoke tests | Disposable Supabase project or local PG | T045 | PostgreSQL | `php artisan migrate:fresh --seed --env=testing-pgsql && php artisan test --env=testing-pgsql` passes; smoke test script passes | Schema created, seeded, tested; zero unexpected failures | HIGH — rehearsal failure indicates production will fail | N/A (disposable env) |
| T047 | | US5 | Execute data migration rehearsal using dummy data: run `db:migrate-data` from MySQL to PG with verify | Disposable PG instance | T046 | PostgreSQL, MySQL | `php artisan db:migrate-data --verify` reports 100% match or documents expected gaps | Data transfer works; verification passes; timing recorded | MEDIUM — proves migrator readiness for real data | N/A (disposable env) |
| T048 | | US5 | Execute rollback rehearsal: restore MySQL backup, revert config, verify app works with MySQL | Disposable env | T047 | MySQL | App starts with MySQL; login, dashboard, operations work | Rollback completes within documented time limit; no data lost | HIGH — rollback failure removes safety net | N/A (disposable env) |
| T049 | | US5 | Document rehearsal results: duration per phase, issues found, resolutions, final go/no-go recommendation | `specs/010-migrate-postgresql-render/rehearsal-report.md` | T046, T047, T048 | N/A (document) | Report created with timings, issues, and recommendation | Report answers: rehearsal passed/failed, estimated production duration, risks identified | MEDIUM — undocumented issues recur in production | N/A |

**Checkpoint**: Full rehearsal complete. Rollback verified. Production cutover ready.

---

## Phase 9: Docker y Render

**Purpose**: Build the deployable Docker image, configure Render service, and verify HTTPS/proxy.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T050 | | US4 | Create multi-stage `Dockerfile`: build stage (composer install, npm ci, vite build) + runtime stage (PHP-FPM 8.3, Nginx, pdo_pgsql, production config) | `Dockerfile` | None | Docker | `docker build -t agenteflow .` completes without error; `docker images agenteflow` shows image < 500 MB | Image builds; contains PHP 8.3, pdo_pgsql, Nginx; no dev dependencies; debug=false | HIGH — broken image blocks deployment | Yes: delete Dockerfile |
| T051 | [P] | US4 | Create Nginx config: serve `public/` as root, proxy PHP to FPM, set security headers, limit to `$PORT` | `docker/nginx.conf` | T050 | Docker | `docker run --rm -p 8080:80 agenteflow && curl -I http://localhost:8080/up` returns 200 | Nginx responds; PHP executed; static assets served; headers present | MEDIUM — misconfigured Nginx exposes files or breaks routing | Yes: update config |
| T052 | [P] | US4 | Create PHP-FPM config: production settings (opcache, no xdebug), log to stdout, worker limits appropriate for free tier | `docker/php.ini`, `docker/php-fpm.conf` | T050 | Docker | `docker run --rm agenteflow php -i \| grep -E "opcache.enable\|xdebug\|pdo_pgsql"` shows opcache=on, xdebug=off, pdo_pgsql=enabled | PHP-FPM runs with production settings; no dev extensions loaded | LOW — dev settings in production waste resources | Yes: update config |
| T053 | [P] | US4 | Create Docker entrypoint: run migrations with `--isolated` lock, optimize Laravel (config/route/view cache), start Nginx+PHP-FPM | `docker/entrypoint.sh` | T050 | Docker | `docker run --rm -e DB_CONNECTION=pgsql -e DB_HOST=... agenteflow` completes entrypoint, app responds on port 80 | Migrations run; caches built; server starts | CRITICAL — failed entrypoint blocks app start | Yes: fix entrypoint |
| T054 | | US4 | Create `render.yaml` Blueprint: web service, Dockerfile path, health check path `/up`, environment variable definitions (all placeholders, no secrets) | `render.yaml` | T050-T053 | Render | Render Dashboard accepts `render.yaml` without validation errors | Blueprint defines web service, health check, and env var schema | MEDIUM — missing render.yaml requires manual Render setup | Yes: delete or update |
| T055 | | US4 | Configure Render environment variables as secrets (not in repo): APP_KEY, DB_*, JWT_*, REFRESH_PEPPER, APP_URL — all using placeholders in docs | Render Dashboard (manual) | T054 | Render | `curl https://[app].onrender.com/up` returns 200 | App deployed on Render; environment variables loaded; app responds | CRITICAL — missing secrets prevent startup | Yes: update in Dashboard |
| T056 | | US4 | Verify HTTPS, cookie security, and proxy trust on Render: test login, refresh, logout under `*.onrender.com` domain | Manual browser test | T055 | Render | Login sets Secure/HttpOnly/SameSite cookies; refresh rotates tokens; logout clears cookies; all under HTTPS | Full auth cycle works on Render domain; no redirect loops; no mixed content | HIGH — broken auth blocks all users | N/A (verification only) |
| T057 | | US4 | Verify Vite assets load correctly on Render: CSS, JS, images served from `public/build/` with correct paths | Manual browser test | T053, T055 | Render | DevTools Network tab shows all assets loaded with 200; no 404 on `/build/` paths | Assets compiled during build; manifest.json correct; MIME types correct | MEDIUM — missing assets break UI (non-blocking for API) | N/A |
| T058 | | US4 | Verify health endpoints: `/up` returns 200 (liveness), `/health` returns 200 with DB status (readiness), and Render uses `/up` for health checks | `routes/web.php` (ensure routes exist), Render Dashboard | T055 | Render | `curl -s https://[app].onrender.com/up \| jq .` returns `{"status":"ok"}`; `/health` returns `{"database":"connected"}` | Both endpoints respond; Render health check configured to use `/up` | HIGH — failing health check triggers restart loop | N/A |

**Checkpoint**: Application deployed on Render. HTTPS, cookies, assets, health checks functional.

---

## Phase 10: Corte

**Purpose**: Execute the final cutover, validate, and declare PostgreSQL as the sole production database.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T059 | | US5 | Enable Laravel maintenance mode on current MySQL instance (`php artisan down`) | Current server (MySQL) | T048 | MySQL production | `curl -I [app]/login` returns 503 | Maintenance mode active; all requests show maintenance page | LOW — maintenance mode is reversible | `php artisan up` |
| T060 | | US5 | Create final MySQL backup with row count snapshot and checksum | `specs/010-migrate-postgresql-render/backups/final-backup.sql` | T059 | MySQL production | Backup file created; `sha256sum` recorded; row counts captured via `mysql -e "SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.tables WHERE table_schema='control_operaciones'"` | Final backup stored outside repo; checksum and counts documented | CRITICAL — no final backup = no rollback | Yes: delete backup (keep for retention period) |
| T061 | | US5 | Execute `migrate:fresh --seed` on Supabase production database | Supabase production | T060 | Supabase production | `php artisan migrate:fresh --seed --force` exits 0 | All tables created; seeders executed; admin and operator accounts exist | CRITICAL — migration failure leaves empty or corrupt DB | `migrate:rollback --step=29` or restore pg_dump |
| T062 | | US5 | Run smoke test script against Supabase/Render: login, register operation, consult dashboard, generate closure, refresh token | `specs/010-migrate-postgresql-render/scripts/smoke-test.sh` | T061, T055 | Render + Supabase | `bash scripts/smoke-test.sh` exits 0 | All critical flows pass: auth, operations, closures, dashboards | CRITICAL — smoke test failure blocks opening | N/A (test only) |
| T063 | | US5 | Validate data integrity: compare row counts (PostgreSQL seeders vs MySQL baseline), verify admin/operator accounts exist | `specs/010-migrate-postgresql-render/validation-report.md` | T062 | Render + Supabase | Counts match expected seeder output; login with admin and operator succeeds | Validation report signed; all checks pass or have documented exceptions | HIGH — incomplete validation risks undiscovered gaps | N/A (report) |
| T064 | | US5 | Disable Laravel maintenance mode (`php artisan up`); open application to users | Render deployment | T062, T063 | Render | `curl -I [app]/login` returns 200 | Application open; users can authenticate | CRITICAL — irreversible step; commits PostgreSQL as production | Rollback via Phase 10 rollback runbook |
| T065 | | US5 | Record cutover event: timestamp, responsible, MySQL final state, PostgreSQL initial state, backup references | `specs/010-migrate-postgresql-render/cutover-record.md` | T064 | N/A (document) | Record created with all metadata | Cutover officially documented for audit trail | LOW — documentation only | N/A |

**Checkpoint**: PostgreSQL is the production database. Application open on Render. MySQL in read-only retention.

---

## Phase 11: Validación Posterior

**Purpose**: Monitor, test, and confirm that the new environment is fully operational and stable.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T066 | [P] | | Verify no credentials in Git, logs, error pages, or environment dump: scan tree, Render logs, and database for secrets | Manual scan | T064 | Render + local | `git log --all -p \| grep -cE "(password\|secret\|token\|PRIVATE)"` after cutover returns 0; Render logs show no connection strings | Zero credentials detected in code, logs, or responses | CRITICAL — exposed credentials enable data breach | Rotate found credentials |
| T067 | [P] | | Monitor Render logs for 24h: 500 errors, connection timeouts, deadlocks, pool exhaustion, slow queries | Render Dashboard → Logs | T064 | Render | After 24h, ≤5 migration-related errors; all critical flows logged successfully | No systemic errors; transient issues documented | HIGH — undetected errors erode trust in migration | N/A (observation) |
| T068 | | | Verify session cycle: login, JWT expiry at 5 min, modal warning at 30s, explicit renewal, refresh rotation, logout revokes, reuse of consumed refresh fails | Manual + automated tests | T064 | Render + Supabase | `php artisan test --filter="Session\|Login\|Refresh\|Logout" --env=testing-pgsql` passes | Full JWT lifecycle works; no silent expiration changes | HIGH — session failures lock users out | N/A |
| T069 | | US3 | Verify dashboards match MySQL baseline: run comparison queries, validate aggregations, date groupings, and totals | `specs/010-migrate-postgresql-render/dashboard-comparison.md` (report) | T064 | Render + Supabase | Dashboard values match expected structure; date groupings correct with TO_CHAR | All dashboard metrics consistent; no regression from MySQL | HIGH — incorrect dashboard data misleads operational decisions | N/A |
| T070 | [P] | US3 | Verify daily closures: create, confirm, reopen; verify all decimal fields, operation counts, pivot relationships | Manual + automated tests | T064 | Render + Supabase | `php artisan test --filter="Closing" --env=testing-pgsql` passes | Closure lifecycle works; amounts reconcile; states transition correctly | HIGH — closure failures block end-of-day workflow | N/A |
| T071 | | US3 | Performance baseline: measure login time, operation registration, dashboard load, closure generation against PostgreSQL | `specs/010-migrate-postgresql-render/performance-baseline.md` | T064 | Render + Supabase | Login < 2s, operation < 1s, dashboard < 3s, closure < 5s from Render to Supabase (same region) | All metrics within targets; no regression from MySQL baseline | MEDIUM — slow performance degrades user experience | N/A |

**Checkpoint**: Environment stable. All critical flows verified. Performance within targets.

---

## Phase 12: Limpieza

**Purpose**: Remove MySQL configuration, temporary credentials, update documentation, and formally close the migration.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T072 | | | Remove MySQL driver config from `config/database.php` (keep pgsql, sqlite); update default to `env('DB_CONNECTION', 'pgsql')` | `config/database.php` | T071 | Local | `php artisan config:clear && php artisan tinker --execute="echo config('database.default');"` outputs `pgsql` | MySQL config removed; PostgreSQL is default | MEDIUM — premature removal breaks local dev without .env update | Yes: restore from git |
| T073 | [P] | | Remove MySQL connection references from `.env.example`: change `DB_CONNECTION` to `pgsql`, `DB_PORT` to `5432`, replace host/port with PG placeholders | `.env.example` | T072 | Local | `grep -c "mysql\|mariadb\|3306" .env.example` returns 0 | No MySQL references in documented environment template | LOW — outdated .env.example confuses new developers | Yes: revert |
| T074 | [P] | | Revoke or delete any temporary credentials used during migration: migration PostgreSQL role password, test credentials, pooler test connection | Supabase Dashboard (manual) | T068 | Supabase | Application connects with permanent app_role only; temporary credentials return authentication error | All temporary credentials rotated or deleted; app uses final role | HIGH — lingering temporary credentials are attack vectors | Rotate again if needed |
| T075 | | | Document final architecture: relationship between Render, Supabase, connection method, backup strategy, rollback retention period | `docs/architecture.md` (create if absent) | T072, T073, T074 | N/A (document) | Document reviewed and accurate against production state | Architecture doc reflects actual deployment; answers: where, how, backed up, restored, monitored | LOW — documentation gaps cause operational confusion | N/A |
| T076 | [P] | | Update `README.md`: remove MySQL requirement, add PostgreSQL/Supabase requirement, update installation steps | `README.md` | T075 | Local | `grep -c "MySQL\|MariaDB" README.md` returns 0 (except in historical notes) | README reflects current production stack | LOW — outdated README misdirects new developers | Yes: revert |
| T077 | | | Define MySQL retention period and rollback deadline: document date when MySQL backup is deleted and rollback window closes | `specs/010-migrate-postgresql-render/retention.md` | T064 | N/A (document) | Document specifies: retention end date, backup location, rollback deadline, responsible party | MySQL backup retained for agreed window; rollback possible until deadline | MEDIUM — premature deletion removes safety net | N/A (document) |
| T078 | | | Formally close migration: sign off on all acceptance criteria, archive rehearsal artifacts, declare PostgreSQL sole production database | `specs/010-migrate-postgresql-render/closure.md` | T077 | N/A (document) | Closure document signed with references to: rehearsal report, cutover record, validation report, performance baseline, post-validation log review | Migration declared complete; PostgreSQL is the only production database | LOW — ceremonial; operational decision already made | N/A |

**Checkpoint**: Migration closed. MySQL removed. Documentation updated. PostgreSQL is the canonical production database.

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1 (Security) ─────────────────────────────────────────────────────────────┐
Phase 2 (Audit) ───── depends on Phase 1 (backup exists before inventory) ──────┤
Phase 3 (PG Env) ──── independent of Phase 1-2 ─────────────────────────────────┤
Phase 4 (Schema) ──── depends on Phase 3 (PG running) ──────────────────────────┤
Phase 5 (Code) ────── depends on Phase 4 (schema clean) ────────────────────────┤
Phase 6 (Supabase) ── depends on Phase 5 (code ready before securing prod) ─────┤
Phase 7 (Migrator) ── depends on Phase 4 (schema) and Phase 5 (code) ───────────┤
Phase 8 (Rehearsal) ─ depends on Phase 4-7 (all pieces ready) ──────────────────┤
Phase 9 (Docker) ──── independent of Phase 1-8 (can start in parallel) ─────────┤
Phase 10 (Cutover) ── depends on Phase 8 AND Phase 9 ───────────────────────────┤
Phase 11 (Post-Val) ─ depends on Phase 10 (cutover complete) ───────────────────┤
Phase 12 (Cleanup) ── depends on Phase 11 (confirmed stable) ───────────────────┘
```

### Critical Path

`Phase 3 → Phase 4 → Phase 5 → Phase 8 ∩ Phase 9 → Phase 10 → Phase 11 → Phase 12`

### Parallel Opportunities

- **Phase 1**: T003, T004, T005 can run in parallel
- **Phase 2**: T008, T010 can run in parallel after T007
- **Phase 3**: T013 can run in parallel with T012 setup
- **Phase 4**: T018, T019, T020, T021, T022 can run in parallel after T017
- **Phase 5**: T026, T027, T032 can run in parallel after T025
- **Phase 6**: T034, T036 can run in parallel with T033
- **Phase 7**: T042 can run in parallel with T041
- **Phase 9**: T051, T052, T053 can run in parallel
- **Phase 11**: T066, T067, T070 can run in parallel after T064
- **Phase 12**: T073, T074, T076 can run in parallel after T072

### Within Each Phase

- Reports/documentation → Fix schema/code → Automate → Test
- Each task must complete before its successor in the same file

## Implementation Strategy

### MVP (Minimal Viable Migration)

1. Complete Phase 1 (Security baseline)
2. Complete Phase 3 (PostgreSQL environment)
3. Complete Phase 4 (Schema compatibility) — this alone proves migration viability
4. Complete Phase 5 (Code compatibility) — full suite passes
5. **STOP and VALIDATE**: Schema and code work on PostgreSQL
6. Phase 9 (Docker + Render) can proceed in parallel

### Incremental Delivery

1. Phases 1-5 → PostgreSQL functional locally
2. Phase 6 → Supabase secured
3. Phase 7 → Data migrator ready
4. Phase 8 → Rehearsal complete
5. Phase 9 → Deployed on Render
6. Phase 10 → Cutover
7. Phase 11 → Validated
8. Phase 12 → Clean

### Risk Mitigation

- **Highest risk phases**: 4 (schema), 5 (code), 10 (cutover)
- **Irreversible step**: T064 (open application on PostgreSQL)
- **Safety net**: Phase 8 rollback rehearsal + Phase 12 retention period

## Summary

| Phase | Tasks | Environment |
|-------|-------|-------------|
| 1. Seguridad | T001-T006 | Local, MySQL |
| 2. Auditoría | T007-T011 | MySQL, Local |
| 3. Entorno PG | T012-T016 | PostgreSQL |
| 4. Schema | T017-T024 | PostgreSQL |
| 5. Código | T025-T032 | PostgreSQL |
| 6. Supabase | T033-T038 | Supabase |
| 7. Migrador | T039-T044 | MySQL + PostgreSQL |
| 8. Ensayo | T045-T049 | Disposable env |
| 9. Docker/Render | T050-T058 | Docker, Render |
| 10. Corte | T059-T065 | Production |
| 11. Post-Val | T066-T071 | Render + Supabase |
| 12. Limpieza | T072-T078 | Production |
| 13. Convergence | T079-T082 | All |
| **Total** | **82 tasks** |

---

## Phase 13: Convergence (Gap Remediation)

**Purpose**: Address gaps between spec requirements and current implementation not yet captured
by existing tasks.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T079 | | | Create `/up` liveness route: respond 200 with `{"status":"ok"}` without database check. Keep existing `/health` as readiness (with DB check) and update HealthController to support both modes | `routes/web.php`, `app/Http/Controllers/HealthController.php` | T058 | Local | `curl -s http://localhost/up` returns `{"status":"ok"}` with 200; `curl -s http://localhost/health` returns 200 with DB connected or 503 if not | /up returns 200 always; /health returns DB status; Render health check configured to use `/up` per FR-032 | MEDIUM — without /up, Render health check fails on cold starts | Yes: remove route |
| T080 | [P] | | Generate PostgreSQL index justification report: list every index in the destination schema, map to its purpose (constraint/FK/query/ordering/filter), provide EXPLAIN evidence for query-driven indexes, flag any index without justification per FR-041 | `specs/010-migrate-postgresql-render/index-justification.md` | T015 | PostgreSQL | Report has ≥25 indexes each with: name, table, columns, purpose, EXPLAIN reference or FK/constraint justification | Every index justified or flagged for removal; no MySQL indexes blindly copied | LOW — non-blocking but required for FR-041 compliance | N/A (report) |
| T081 | [P] | | Generate acceptance criteria traceability matrix: map every AC-001 through AC-070 to the task(s) that provide evidence of completion. Flag any AC without coverage per SC-006 | `specs/010-migrate-postgresql-render/ac-traceability.md` | T078 | N/A (document) | Matrix has 70 rows; each AC mapped to ≥1 task or documentation reference | All 70 ACs have identifiable evidence source; gaps documented for manual verification | LOW — traceability gap may delay sign-off | N/A (report) |
| T082 | | | Add seed-count verification to rehearsal and cutover tasks: after `migrate:fresh --seed`, capture per-table row counts and compare with expected seeder output counts. Document in rehearsal report (T049) and validation report (T063) per FR-016 for the dummy-data path | `specs/010-migrate-postgresql-render/scripts/verify-seed-counts.sh` | T046, T061 | PostgreSQL | `bash scripts/verify-seed-counts.sh` returns 0; outputs: organizations≥1, users≥2, agents≥3, operation_types≥8, assignments≥1 | Every seeded table count matches expected; discrepancies documented | MEDIUM — structural gap in dummy-data verification | N/A (verification only) |

**Checkpoint**: All spec-defined gaps remediated. Indexes justified. AC traceability documented. Seed verification automated. Liveness endpoint available for Render.

---

## Phase 13: Convergence (Gap Remediation)

**Purpose**: Address gaps between spec requirements and current implementation not yet captured
by existing tasks.

| ID | P? | Story | Task | Path | Deps | Env | Test Command | Completion | Risk | Reversible |
|----|-----|-------|------|------|------|-----|-------------|------------|------|------------|
| T079 | | | Create `/up` liveness route: respond 200 with `{"status":"ok"}` without database check. Keep existing `/health` as readiness (with DB check) and update HealthController to support both modes | `routes/web.php`, `app/Http/Controllers/HealthController.php` | T058 | Local | `curl -s http://localhost/up` returns `{"status":"ok"}` with 200; `curl -s http://localhost/health` returns 200 with DB connected or 503 if not | /up returns 200 always; /health returns DB status; Render health check configured to use `/up` per FR-032 | MEDIUM — without /up, Render health check fails on cold starts | Yes: remove route |
| T080 | [P] | | Generate PostgreSQL index justification report: list every index in the destination schema, map to its purpose (constraint/FK/query/ordering/filter), provide EXPLAIN evidence for query-driven indexes, flag any index without justification per FR-041 | `specs/010-migrate-postgresql-render/index-justification.md` | T015 | PostgreSQL | Report has ≥25 indexes each with: name, table, columns, purpose, EXPLAIN reference or FK/constraint justification | Every index justified or flagged for removal; no MySQL indexes blindly copied | LOW — non-blocking but required for FR-041 compliance | N/A (report) |
| T081 | [P] | | Generate acceptance criteria traceability matrix: map every AC-001 through AC-070 to the task(s) that provide evidence of completion. Flag any AC without coverage per SC-006 | `specs/010-migrate-postgresql-render/ac-traceability.md` | T078 | N/A (document) | Matrix has 70 rows; each AC mapped to ≥1 task or documentation reference | All 70 ACs have identifiable evidence source; gaps documented for manual verification | LOW — traceability gap may delay sign-off | N/A (report) |
| T082 | | | Add seed-count verification to rehearsal and cutover tasks: after `migrate:fresh --seed`, capture per-table row counts and compare with expected seeder output counts. Document in rehearsal report (T049) and validation report (T063) per FR-016 for the dummy-data path | `specs/010-migrate-postgresql-render/scripts/verify-seed-counts.sh` | T046, T061 | PostgreSQL | `bash scripts/verify-seed-counts.sh` returns 0; outputs: organizations≥1, users≥2, agents≥3, operation_types≥8, assignments≥1 | Every seeded table count matches expected; discrepancies documented | MEDIUM — structural gap in dummy-data verification | N/A (verification only) |

**Checkpoint**: All spec-defined gaps remediated. Indexes justified. AC traceability documented. Seed verification automated. Liveness endpoint available for Render.

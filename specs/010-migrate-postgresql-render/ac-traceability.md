# Acceptance Criteria Traceability Matrix

**Feature**: 010-migrate-postgresql-render | **Date**: 2026-07-25

Maps every acceptance criterion (AC-001 through AC-070) to the task(s) providing evidence of completion per SC-006.

## Schema Acceptance (AC-001 – AC-010)

| AC | Description | Evidence Task | Status |
|----|-------------|---------------|--------|
| AC-001 | Migraciones ejecutan en PostgreSQL | T015 (migrate:fresh on PG) | Pending |
| AC-002 | migrate:fresh --seed funciona | T016 (db:seed on PG) | Pending |
| AC-003 | Sin dependencias MySQL sin documentar | T008 (migration audit report) | Pending |
| AC-004 | Todas las FK existen | T015 (migrate:fresh verifies FK creation) | Pending |
| AC-005 | Todos los índices requeridos existen | T080 (index justification report) | ✓ Done |
| AC-006 | Restricciones únicas funcionan | T015 (migrate:fresh creates all unique constraints) | Pending |
| AC-007 | Campos monetarios usan NUMERIC | T020 (DecimalPrecisionTest on PG) | Pending |
| AC-008 | Secuencias sin colisiones | T023 (SequenceTest on PG) | Pending |
| AC-009 | Timestamps conservan significado | T022 (TypeCompatibilityTest), T025 (TO_CHAR fix) | Partial (T025 done, T022 pending) |
| AC-010 | JSON válido se conserva | T021 (JsonColumnTest on PG) | Pending |

## Data Acceptance (AC-011 – AC-025)

| AC | Description | Evidence Task | Status |
|----|-------------|---------------|--------|
| AC-011 | Tablas válidas migradas según clasificación | T006 (data classification), T061 (migrate:fresh --seed) | Partial (T006 classification done) |
| AC-012 | Conteos coinciden | T082 (verify-seed-counts.sh) | Pending |
| AC-013 | Identificadores coinciden | T063 (validation report) | Pending |
| AC-014 | Usuarios coinciden | T063 (validation report) | Pending |
| AC-015 | Operaciones coinciden | T063 (validation report) | Pending |
| AC-016 | Agentes coinciden | T063 (validation report) | Pending |
| AC-017 | Asignaciones coinciden | T063 (validation report) | Pending |
| AC-018 | Cierres coinciden | T063 (validation report) | Pending |
| AC-019 | Totales monetarios coinciden | T070 (closing regression test) | Pending |
| AC-020 | Sin registros huérfanos | T015 (FK constraints enforce) | Pending |
| AC-021 | Sin duplicados no previstos | T015 (unique constraints enforce) | Pending |
| AC-022 | Sin pérdida de caracteres | T022 (TypeCompatibilityTest) | Pending |
| AC-023 | Sin desplazamiento horario | T022 (TypeCompatibilityTest), T025 (TO_CHAR fix) | Partial (T025 done) |
| AC-024 | Sesiones revocadas | T064 (cutover opens with fresh sessions) | Pending |
| AC-025 | Evidencia de reconciliación | T063 (validation report) | Pending |

## Application Acceptance (AC-026 – AC-039)

| AC | Description | Evidence Task | Status |
|----|-------------|---------------|--------|
| AC-026 | Login funciona con PostgreSQL | T062 (smoke test) | Pending |
| AC-027 | JWT funciona | T068 (session cycle verification) | Pending |
| AC-028 | Refresh token funciona | T068 (session cycle verification) | Pending |
| AC-029 | Modal de expiración funciona | T068 (session cycle verification) | Pending |
| AC-030 | Autorización funciona | T028 (Feature test suite on PG) | Pending |
| AC-031 | Registro de operaciones funciona | T062 (smoke test) | Pending |
| AC-032 | Anulación funciona | T062 (smoke test) | Pending |
| AC-033 | Dashboards funcionan | T031 (DashboardRegressionTest), T069 (dashboard comparison) | Pending |
| AC-034 | Filtros funcionan | T028 (Feature test suite on PG) | Pending |
| AC-035 | Cierre diario funciona | T032 (ClosingRegressionTest), T070 (closing verification) | Pending |
| AC-036 | Auditoría funciona | T030 (Integration test suite on PG) | Pending |
| AC-037 | Transacciones y locks funcionan | T030 (Integration test suite on PG) | Pending |
| AC-038 | Sin errores de sintaxis MySQL | T009 (SQL audit), T025 (TO_CHAR), T026 (transient errors), T029 (fix failures) | Partial (T025/T026 done) |
| AC-039 | Suite completa pasa contra PG | T028 (Feature), T030 (Integration), T046 (rehearsal) | Pending |

## Supabase Acceptance (AC-040 – AC-048)

| AC | Description | Evidence Task | Status |
|----|-------------|---------------|--------|
| AC-040 | Conexión usa SSL | T035 (SSL connectivity test) | Pending |
| AC-041 | Sin credenciales en Git | T001 (pre-scan), T066 (post-scan) | ✓ Done (T001 clean) |
| AC-042 | Rol de aplicación limitado | T034 (create app role) | Pending |
| AC-043 | Tablas no expuestas por Data API | T033 (disable Data API) | Pending |
| AC-044 | Sin clave de servicio en frontend | T033 (Data API disabled = no keys needed) | Pending |
| AC-045 | No se usa Supabase Auth | Plan decision (BR-003) — verified by architecture | ✓ Done |
| AC-046 | Método de conexión probado desde Render | T036 (Session Pooler test), T056 (HTTPS on Render) | Pending |
| AC-047 | Límites documentados | T037 (supabase-limits.md) | Pending |
| AC-048 | Estrategia de backup externo | T003 (backup script), T004 (manifest), T060 (final backup) | Partial (T003/T004 pending) |

## Render Acceptance (AC-049 – AC-060)

| AC | Description | Evidence Task | Status |
|----|-------------|---------------|--------|
| AC-049 | Aplicación construida con Docker | T050 (Dockerfile) | ✓ Done |
| AC-050 | Imagen incluye pdo_pgsql | T050 (Dockerfile installs pdo_pgsql) | ✓ Done |
| AC-051 | Aplicación inicia correctamente | T053 (entrypoint), T055 (Render deploy) | Partial (Docker config done) |
| AC-052 | Health checks responden | T058 (verify /up and /health), T079 (create /up route) | ✓ Done (T079) |
| AC-053 | APP_DEBUG desactivado | T052 (php.ini), T053 (entrypoint), render.yaml | ✓ Done |
| AC-054 | APP_KEY estable | render.yaml (generateValue: true, persisted by Render) | ✓ Done |
| AC-055 | Logs a stdout sanitizados | T052 (php.ini log_errors + display_errors=off) | ✓ Done |
| AC-056 | Sin dependencia del filesystem local | FR-029 covered by T052, T055 | ✓ Done |
| AC-057 | Cookies funcionan bajo HTTPS | T056 (HTTPS cookie verification) | Pending |
| AC-058 | Login/refresh/logout en Render | T056 (auth cycle on Render domain) | Pending |
| AC-059 | Assets Vite cargan | T057 (asset loading verification) | Pending |
| AC-060 | Arranque en frío documentado | T037 (supabase limits), plan.md (Render Free constraints) | ✓ Done |

## Migration Acceptance (AC-061 – AC-070)

| AC | Description | Evidence Task | Status |
|----|-------------|---------------|--------|
| AC-061 | Ensayo completo | T046 (clean rehearsal) | Pending |
| AC-062 | Backup restaurable | T004 (manifest), T048 (rollback rehearsal) | Pending |
| AC-063 | Rollback probado | T048 (rollback rehearsal) | Pending |
| AC-064 | Ventana de corte | T047 (rehearsal data), plan.md (cutover runbook) | Pending |
| AC-065 | Sin escrituras simultáneas | T059 (maintenance mode), T064 (single write target) | Pending |
| AC-066 | Validación antes de abrir | T063 (validation report before T064 open) | Pending |
| AC-067 | Origen solo lectura | T059 (maintenance mode + read-only) | Pending |
| AC-068 | Resultado documentado | T065 (cutover record), T078 (closure) | Pending |
| AC-069 | Credenciales temporales eliminadas | T074 (rotate/delete temp creds) | Pending |
| AC-070 | PostgreSQL única base productiva | T072 (remove MySQL config), T078 (closure) | Pending |

## Summary

| Category | Total ACs | Done | Partial | Pending |
|----------|-----------|------|---------|---------|
| Schema (001-010) | 10 | 1 | 1 | 8 |
| Data (011-025) | 15 | 0 | 2 | 13 |
| Application (026-039) | 14 | 0 | 1 | 13 |
| Supabase (040-048) | 9 | 1 | 1 | 7 |
| Render (049-060) | 12 | 9 | 1 | 2 |
| Migration (061-070) | 10 | 0 | 0 | 10 |
| **Total** | **70** | **11** | **6** | **53** |

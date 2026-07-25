# Migration & Cutover Requirements Quality Checklist: PostgreSQL y Render

**Purpose**: Validate completeness, clarity, and consistency of migration and cutover requirements before implementation planning
**Created**: 2026-07-25
**Feature**: [spec.md](../spec.md)

## Requirement Completeness

- [ ] CHK001 - Are schema inventory requirements complete for ALL object types listed in FR-001 (tables, columns, types, defaults, nullability, PK, FK, constraints, indexes, views, programmatic objects, generated columns, auto-increments, collations, JSON, enums, monetary/temporal fields, pivots, sessions, audit, migrations, seeders, factories)? [Completeness, Spec §FR-001]
- [ ] CHK002 - Does the inventory item format (FR-002) cover all traceability dimensions needed for a go/no-go decision? [Completeness, Spec §FR-002]
- [ ] CHK003 - Are compatibility audit requirements (FR-003) scoped to cover all code layers: migrations, Eloquent, Query Builder, raw SQL, seeders, factories, tests, and operational scripts? [Completeness, Spec §FR-003]
- [ ] CHK004 - Does the data classification requirement (FR-004) define what constitutes "real", "dummy", "partially valid", "obsolete", "sensitive", and "regenerable" data with measurable criteria? [Clarity, Spec §FR-004]
- [ ] CHK005 - Are migration up/down requirements (FR-005) explicit about whether ALL migrations must be reversible or only those "technically viable"? [Clarity, Spec §FR-005]
- [ ] CHK006 - Are the required destination constraints (FR-006) enumerated explicitly (FK, unique, indexes, transactions, locks, delete semantics) or is the list open to interpretation? [Clarity, Spec §FR-006]
- [ ] CHK007 - Are acceptance baseline items AC-001 through AC-010 sufficient to verify schema correctness, or are constraint types like CHECK, EXCLUDE, and partial unique indexes omitted? [Gap, Spec §Acceptance Baseline]
- [ ] CHK008 - Are data acceptance criteria AC-011 through AC-025 covering all entity types present in the domain (users, agents, assignments, operations, closures, audit, sessions, password resets)? [Completeness, Spec §Acceptance Baseline]

## Cutover & Rollback Clarity

- [ ] CHK009 - Is the "window" for cutover (BR-016) defined with concrete parameters: maximum duration, start trigger, end condition, and who declares completion? [Clarity, Spec §BR-016]
- [ ] CHK010 - Does the rollback requirement (FR-039) specify a measurable time-limit for the rollback decision, or is "tiempo máximo de decisión" left undefined? [Clarity, Spec §FR-039]
- [ ] CHK011 - Are rollback triggers (FR-039) defined as specific, observable conditions (e.g., "health check fails 3x in 5 min") or left as abstract "disparadores"? [Clarity, Spec §FR-039]
- [ ] CHK012 - Does BR-017 ("resolver explícitamente las nuevas operaciones") define what resolution means: re-execute in MySQL, discard, merge, or manual reconciliation? [Ambiguity, Spec §BR-017]
- [ ] CHK013 - Is the requirement "el origen permanece solo lectura durante la ventana acordada" (FR-038) quantified with a specific retention period? [Clarity, Spec §FR-038]
- [ ] CHK014 - Does FR-038 enumerate ALL steps required during cutover (maintenance mode, write block, final backup, metrics, transfer, validation, config, smoke tests, open, monitor) or is the list illustrative? [Completeness, Spec §FR-038]
- [ ] CHK015 - Are backup requirements (FR-036) complete: checksum, retention period, encryption, responsible party, restoration test — or is any dimension missing? [Completeness, Spec §FR-036]
- [ ] CHK016 - Does the rehearsal requirement (FR-037) cover ALL phases: clean destination, migrate/seed, validate, automated tests, smoke tests, duration measurement, problem log, rollback test? [Completeness, Spec §FR-037]

## Data Integrity & Validation

- [ ] CHK017 - Are the pre-transfer metrics (FR-016) defined with a complete list per table (row count, ID range, null counts, uniqueness, orphans, duplicates, totals, date ranges) — or are aggregation dimensions like totals-per-status and totals-per-agent missing? [Completeness, Spec §FR-016]
- [ ] CHK018 - Is the post-transfer validation (FR-017) explicit about which attributes must match exactly vs. which may have approved transformations? [Clarity, Spec §FR-017]
- [ ] CHK019 - Are monetary reconciliation dimensions (FR-019) complete: gross total, per-agent, per-operator, per-date, per-status, per-monetary-direction — or are directions like "entradas digitales" and "salidas digitales" explicitly listed? [Completeness, Spec §FR-019]
- [ ] CHK020 - Does FR-018 define ALL five difference classifications ("esperada", "transformada", "rechazada", "error de migración", "dato inválido previo") with unambiguous criteria for each? [Clarity, Spec §FR-018]
- [ ] CHK021 - Are type transformation requirements (FR-009) specific about which boolean representations (0, 1, '0', '1', true, false) must be handled, or does "de manera explícita" leave room for silent assumptions? [Clarity, Spec §FR-009]
- [ ] CHK022 - Does the timestamp convention requirement (FR-010) define whether storage is UTC, UTC-with-offset, or a timezone-aware type — or does "una convención documentada y consistente" defer the decision? [Ambiguity, Spec §FR-010]

## Edge Case Coverage

- [ ] CHK023 - Are edge cases requiring explicit requirements defined: orphan indexes after column drops, char(N) padding differences, multiple-NULL unique semantics, case-sensitive comparisons, boolean representation ambiguity, and JSON validity enforcement? [Coverage, Spec §Edge Cases]
- [ ] CHK024 - Is the edge case "dos instancias intentan ejecutar migraciones simultáneamente" addressed by a specific requirement (FR-031 mentions a lock — is the lock mechanism defined)? [Coverage, Spec §Edge Cases, FR-031]
- [ ] CHK025 - Does the spec address the scenario "el backup se genera correctamente pero falla su restauración de prueba" with a clear requirement for pre-cutover restoration validation? [Coverage, Spec §Edge Cases, FR-036]
- [ ] CHK026 - Is the edge case "se detecta una credencial en el historial Git" covered by FR-024 with a clear remediation ("rotarse antes de uso") — or is rotation procedural guidance missing? [Clarity, Spec §Edge Cases, FR-024]
- [ ] CHK027 - Are recovery edge cases defined for "el servicio reinicia mientras un lote de transferencia está en curso" when ETL (FR-013) is active? [Gap, Spec §Edge Cases]
- [ ] CHK028 - Does the spec address "el corte se abre a usuarios y luego aparece una diferencia monetaria crítica" with a specific response procedure, or is it only listed as an edge case without resolution? [Gap, Spec §Edge Cases]

## Requirement Consistency

- [ ] CHK029 - Are BR-005 ("datos dummy no se copiarán automáticamente") and FR-013 ("si existen datos reales, la transferencia DEBE soportar lotes...") consistent in that FR-013's applicability to a dummy-only scenario is clear? [Consistency, Spec §BR-005, FR-013]
- [ ] CHK030 - Do BR-016 ("ningún corte con datos reales sin backup, ensayo, reconciliación, rollback probado") and the dummy-data clarification align so that the rehearsal scope for a dummy-only cutover is unambiguous? [Consistency, Spec §BR-016, Clarifications]
- [ ] CHK031 - Are FR-038 (cutover steps) and FR-039 (rollback steps) free of contradictions — e.g., does rollback "restaurar configuración y código" conflict with "el origen permanece solo lectura"? [Consistency, Spec §FR-038, FR-039]
- [ ] CHK032 - Do AC-011 through AC-025 (data acceptance) conflict with the confirmed dummy-data strategy where data reconciliation items become non-applicable? [Consistency, Spec §Acceptance Baseline, Clarifications]

## Notes

- Focus area: migration strategy, schema compatibility, cutover procedure, rollback safety, data integrity validation
- Depth: Standard (comprehensive coverage of migration & cutover domain)
- Audience: Reviewer (PR) — validates requirements quality before task generation
- Items CHK013, CHK016, CHK019, CHK022, CHK027, CHK028, CHK030 are flagged for potential ambiguity or gaps requiring clarification during planning

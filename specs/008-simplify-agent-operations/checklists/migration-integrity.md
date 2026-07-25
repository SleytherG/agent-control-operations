# Migration Integrity Checklist: Operaciones Generales por Agente

**Purpose**: Validate that the migration data integrity requirements are complete, clear, consistent, and measurable before implementation
**Created**: 2026-07-23
**Feature**: [spec.md](../spec.md)

## Requirement Completeness — Backup & Safety

- [ ] CHK001 — Are backup requirements specified before any destructive migration step (type, scope, verification method)? [Completeness, Spec §Migration integrity, Plan §Estrategia de migración]
- [ ] CHK002 — Is the backup file format and storage location documented in migration requirements? [Gap]
- [ ] CHK003 — Are pre-migration integrity checks defined (row counts, checksums, FK validity) to establish a baseline? [Gap, Spec §FR-032]
- [ ] CHK004 — Are post-migration integrity verification criteria specified for each migrated table? [Completeness, Spec §US1 Scenario 1]
- [ ] CHK005 — Is the procedure for comparing pre/post migration row counts and aggregates defined? [Clarity, Spec §US1 Independent Test]

## Requirement Completeness — Mapping Rules

- [ ] CHK006 — Are the consolidation rules for Store→Agent documented with explicit field-level mappings? [Completeness, Plan §Fase 1, Data-model §_migration_map]
- [ ] CHK007 — Are the consolidation rules for BankAgent→Agent documented when a store and its bank_agent represent the same physical point? [Clarity, Plan §Fase 1]
- [ ] CHK008 — Are consolidation rules defined for orphan bank_agents (no corresponding store)? [Edge Case, Plan §Fase 1]
- [ ] CHK009 — Is the bidirectional mapping table `_migration_map` schema documented with its retention policy? [Completeness, Data-model §_migration_map]
- [ ] CHK010 — Are the rules for populating `agent.code` and `agent.name` from legacy `store.code`/`bank_agent.code` specified unambiguously? [Clarity, Data-model §Agent]

## Requirement Completeness — Foreign Key Migration

- [ ] CHK011 — Are requirements defined for migrating `operations.bank_agent_id → operations.agent_id` preserving referential integrity? [Completeness, Plan §Fase 3]
- [ ] CHK012 — Are requirements defined for migrating `daily_closures.bank_agent_id → daily_closures.agent_id` including unique constraint handling? [Completeness, Plan §Fase 4]
- [ ] CHK013 — Are requirements defined for migrating `user_bank_agent_assignments → user_agent_assignments` with temporal fields preserved? [Completeness, Plan §Fase 2]
- [ ] CHK014 — Is the order of FK drops vs. column additions specified to avoid constraint violations during migration? [Clarity, Plan §Fase 5]
- [ ] CHK015 — Are requirements specified for making `agent_id` NOT NULL after migration completes and before legacy columns are dropped? [Completeness, Plan §Fase 5]

## Requirement Completeness — Data Preservation

- [ ] CHK016 — Are column-level preservation guarantees specified for each migrated table (which columns must survive, which may be defaulted)? [Completeness, Spec §FR-033]
- [ ] CHK017 — Is the preservation of `operation.amount`, `operation.occurred_at`, `operation.user_id` (→operator_user_id) explicitly required in migration rules? [Clarity, Spec §FR-033]
- [ ] CHK018 — Are requirements defined for preserving audit log references when table/column names change? [Gap, Spec §Auditability]
- [ ] CHK019 — Is the treatment of `operation.store_id` (dropped column) documented — should its value be preserved in audit or migration report before deletion? [Gap, Plan §Fase 5]
- [ ] CHK020 — Are requirements specified for handling `daily_closure_operations` pivot table during agent_id migration? [Coverage, Plan §Fase 4]

## Requirement Completeness — Rollback

- [ ] CHK021 — Are rollback requirements specified for each of the 5 migration phases individually? [Completeness, Plan §Estrategia, Spec §SC-014]
- [ ] CHK022 — Is the rollback verification criterion defined ("se recupera el estado anterior verificable sin pérdida") with specific assertions? [Clarity, Spec §US1 Scenario 3]
- [ ] CHK023 — Are requirements specified for `_migration_map` availability during rollback to reconstruct original IDs? [Gap, Plan §Fase 5]
- [ ] CHK024 — Is the maximum rollback time bound (60 minutes per SC-014) traceable to a specific data volume assumption? [Measurability, Spec §SC-014]
- [ ] CHK025 — Are partial rollback requirements defined — can a single phase be rolled back without affecting earlier completed phases? [Gap, Plan §Estrategia]

## Requirement Clarity — Data Transformation

- [ ] CHK026 — Are the rules for converting `cash_direction` (ENTRADA/SALIDA/NEUTRA) to `cash_multiplier` and `digital_multiplier` specified with a complete mapping table? [Clarity, Plan §Decision 3, Data-model §OperationType]
- [ ] CHK027 — Are the default values for `cash_delta` and `digital_delta` on historical operations explicitly stated (e.g., digital_delta = 0 for pre-migration records)? [Clarity, Plan §Fase 3]
- [ ] CHK028 — Is the format specification for `operation.internal_code` (`OP-YYYYMMDD-NNNN`) documented with rules for sequence generation, collision handling, and uniqueness scope? [Clarity, Plan §Decision 5]
- [ ] CHK029 — Are the rules for populating `daily_closures.opening_cash` and `opening_digital` on migrated closures (default to 0) explicitly stated? [Clarity, Plan §Fase 4]

## Requirement Consistency — Cross-Artifact Alignment

- [ ] CHK030 — Do the migration phase descriptions in plan.md align with the data preservation guarantees in spec.md §FR-033? [Consistency, Plan §Fases 1-5, Spec §FR-033]
- [ ] CHK031 — Are the entities listed in data-model.md consistent with the tables marked for deletion in plan.md's impact matrix? [Consistency, Data-model, Plan §Impact Matrix]
- [ ] CHK032 — Do the SC-014 time-bound (60min rollback) and SC-008 data-volume assumptions (100 agents, 100k ops) appear consistent with the migration complexity described? [Consistency, Spec §SC-008, Spec §SC-014]
- [ ] CHK033 — Are the "sin pérdida silenciosa" requirements in spec.md consistent with the plan's treatment of ambiguous/orphan data (report and block, not discard)? [Consistency, Spec §US1 Scenario 2, Plan §Fase 1]

## Edge Case Coverage — Migration

- [ ] CHK034 — Are requirements defined for stores with multiple bank_agents (one physical point, multiple bank relationships)? [Edge Case, Plan §Fase 1]
- [ ] CHK035 — Are requirements defined for operations referencing a bank_agent_id that no longer exists in the source table? [Edge Case, Gap]
- [ ] CHK036 — Are requirements defined for daily_closures whose unique constraint (bank_agent_id, business_date, status) may collide after agent consolidation? [Edge Case, Plan §Fase 4, Data-model §DailyClosure indexes]
- [ ] CHK037 — Are requirements defined for operation_types that had a specific bank_id — should they be deduplicated or kept as distinct records with bank_id dropped? [Edge Case, Plan §Decision 2]
- [ ] CHK038 — Are requirements defined for handling existing seed data (real bank names, real geo data) during migration — transform or reseed? [Edge Case, Gap]
- [ ] CHK039 — Is the migration behavior specified when the application receives write requests during an in-progress migration phase? [Edge Case, Gap, Spec §FR-009]
- [ ] CHK040 — Are requirements defined for the `daily_closure_operations` junction table when closures are remapped to new agent_ids? [Edge Case, Plan §Fase 4]

## Non-Functional Requirements — Migration

- [ ] CHK041 — Are observability requirements specified for the migration process (progress reporting, error logging without secrets)? [Gap, Spec §Observability]
- [ ] CHK042 — Is the requirement for migration reversibility ("migraciones DEBEN ser reversibles cuando sea técnicamente viable") satisfied with `down()` methods documented for each phase? [Completeness, Constitution §XII, Plan §Rollback]
- [ ] CHK043 — Are idempotency requirements specified for migration steps (safe to re-run a partially completed phase)? [Gap]

## Measurability — Acceptance Criteria

- [ ] CHK044 — Can SC-002 ("100% de las operaciones migradas conservan autor, agente resultante, monto, fecha, estado y trazabilidad, con 0 pérdidas silenciosas") be objectively verified with a defined test procedure? [Measurability, Spec §SC-002]
- [ ] CHK045 — Is the "reporte de transformación" required by FR-032 specified with a minimum content checklist (what fields, counts, exceptions must appear)? [Clarity, Spec §FR-032]
- [ ] CHK046 — Are the acceptance criteria for US1 Scenario 2 ("relación ambigua... se reporta como excepción y no se elimina") clear about what constitutes "ambigua" and the exception report format? [Clarity, Spec §US1 Scenario 2]

## Dependencies & Assumptions

- [ ] CHK047 — Is the assumption "Si el entorno actual contiene solo datos dummy, su eliminación igualmente requiere un procedimiento y evidencia documentados" traceable to a specific migration phase or decision? [Assumption, Spec §Assumptions]
- [ ] CHK048 — Are the dependencies on backup infrastructure, staging environment, and test data availability explicitly documented in migration requirements? [Dependency, Gap]
- [ ] CHK049 — Is the dependency on `_migration_map` table across phases 1-5 explicitly noted, including its removal timing? [Dependency, Plan §Fase 5]

## Notes

- Focus: Migration data integrity requirements quality (Option A selected).
- Items marked [Gap] indicate missing requirements that should be added to spec or plan before implementation.
- Items marked [Clarity] indicate existing requirements that need quantification or disambiguation.
- Items marked [Edge Case] indicate boundary scenarios not yet addressed in requirements.
- All items trace to spec.md sections (§), plan.md phases/decisions, data-model.md entities, or constitution principles.

# Closing Requirements Readiness Checklist: Cierre Operativo Diario

**Purpose**: Evaluar completitud y consistencia de requisitos de cierre antes de generar tareas
**Created**: 2026-07-22
**Feature**: [spec.md](../spec.md)
**Audience**: Revisor antes de `/speckit.tasks`
**Depth**: Estándar, foco en transiciones de estado y bloqueo post-confirmación

## State Machine And Uniqueness

- [ ] CHK001 ¿Las transiciones de estado están definidas sin ambigüedad (ACTIVO → CONFIRMADO → REABIERTO → CONFIRMADO)? [Clarity, Spec §Key Entities, Data Model §daily_closures]
- [ ] CHK002 ¿El comportamiento de operaciones idempotentes (confirmar ya confirmado, reabrir ya reabierto) está definido? [Clarity, Spec §Edge Cases]
- [ ] CHK003 ¿La restricción de un solo cierre activo por agente y fecha está definida como regla verificable a nivel de base de datos? [Clarity, Spec §BR-002, Plan §Unique Active Closure]
- [ ] CHK004 ¿Está definido el comportamiento al intentar crear un segundo cierre activo (código de error, mensaje)? [Clarity, Spec §Edge Cases, Contracts §POST /daily-closures]

## Authorization

- [ ] CHK005 ¿Los permisos de generación diferencian administrador (cualquier agente) de operador (solo asignados)? [Clarity, Spec §BR-001, FR-001, Clarifications §Q1]
- [ ] CHK006 ¿La confirmación está restringida exclusivamente al administrador? [Clarity, Spec §BR-003, FR-006]
- [ ] CHK007 ¿La reapertura está restringida exclusivamente al administrador? [Clarity, Spec §FR-009]
- [ ] CHK008 ¿El operador solo visualiza cierres de sus agentes asignados? [Clarity, Spec §BR-008, FR-012]

## Post-Confirmation Blocking

- [ ] CHK009 ¿Está definido que las operaciones de un cierre confirmado no pueden modificarse ni anularse? [Clarity, Spec §BR-004, FR-007]
- [ ] CHK010 ¿Está definido que no pueden registrarse nuevas operaciones en un agente y fecha con cierre confirmado? [Clarity, Spec §FR-008, US2.AC4]
- [ ] CHK011 ¿Está definido que un cierre reabierto permite nuevamente modificaciones? [Clarity, Spec §BR-006, FR-010, US3.AC2]
- [ ] CHK012 ¿La verificación de cierre confirmado al registrar/anular operaciones está especificada como dependencia del módulo Operations? [Consistency, Plan §Confirmation Blocking, Spec §Dependencies]

## POR_CONFIRMAR Warning

- [ ] CHK013 ¿La advertencia POR_CONFIRMAR está definida como regla verificable? [Clarity, Spec §BR-007, FR-011]
- [ ] CHK014 ¿La etiqueta "no definitivo" o "pendiente de confirmación" para el movimiento neto está especificada? [Clarity, Spec §BR-007, FR-011]
- [ ] CHK015 ¿El indicador `has_pending_confirm` está definido en el modelo de datos? [Consistency, Spec §Key Entities, Data Model §daily_closures, Research §POR_CONFIRMAR]

## Audit And Traceability

- [ ] CHK016 ¿Cada transición de estado (generado, confirmado, reabierto) genera auditoría con actor, fecha, entidad y valores? [Completeness, Spec §BR-009, FR-013]
- [ ] CHK017 ¿La reapertura registra motivo como campo obligatorio y auditado? [Clarity, Spec §BR-005, FR-009]

## Temporal Rules

- [ ] CHK018 ¿La fecha de negocio está definida en America/Lima (00:00 a 23:59:59)? [Clarity, Spec §BR-010, FR-014]
- [ ] CHK019 ¿La conversión de fecha de negocio para consultas SQL está documentada? [Consistency, Plan §Technical Context, Research §Consolidation Query]

## Edge Cases

- [ ] CHK020 ¿Está definido el comportamiento cuando se registra una operación después de generar el cierre pero antes de confirmarlo? [Edge Case, Spec §Edge Cases]
- [ ] CHK021 ¿La regeneración del cierre antes de confirmar está especificada? [Clarity, Plan §Regeneration Before Confirmation, Contracts §POST /daily-closures]
- [ ] CHK022 ¿Está definido que las operaciones anuladas antes del cierre no se incluyen, pero las anuladas después sí aparecen como anuladas? [Clarity, Spec §BR-001, US1.AC3]

## Consistency With Dependent Features

- [ ] CHK023 ¿La dependencia en 003-operations-registry para el bloqueo post-confirmación especifica qué actions se modifican? [Clarity, Plan §Confirmation Blocking]
- [ ] CHK024 ¿Los montos usan DECIMAL(18,2) y "monto bruto operado" como etiqueta, consistente con 003 y 004? [Consistency, Spec §BR-011, FR-015]

## Notes

- La regeneración del cierre (antes de confirmar) es una funcionalidad de conveniencia; el plan la documenta.
- La constraint UNIQUE parcial requiere MySQL 8.0+ o MariaDB con columna virtual.

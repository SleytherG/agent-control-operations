# Administration Requirements Readiness Checklist: Administración de Estructura Operacional

**Purpose**: Evaluar claridad, completitud, consistencia y verificabilidad de los requisitos de administración antes de generar tareas
**Created**: 2026-07-22
**Feature**: [spec.md](../spec.md)
**Audience**: Revisor antes de `/speckit.tasks`
**Depth**: Estándar, con foco en autorización e integridad estructural

## Authorization Completeness

- [ ] CHK001 ¿Está definida la autorización del administrador para cada entidad (regiones, provincias, distritos, tiendas, bancos, agentes, asignaciones) en reglas, requisitos y contratos? [Completeness, Spec §BR-001, FR-001–FR-007, FR-016]
- [ ] CHK002 ¿Está definida la restricción del operador a solo ver sus agentes activos asignados en reglas, requisitos y contratos? [Completeness, Spec §BR-001, FR-011–FR-012, Contracts §GET /my-agents]
- [ ] CHK003 ¿Cada endpoint administrativo tiene especificado el código HTTP de rechazo para el operador (403)? [Consistency, Contracts §Shared Rules]
- [ ] CHK004 ¿Los escenarios de aceptación cubren intentos del operador de acceder a cada entidad administrativa mediante URL manipulada? [Coverage, Spec §US1.AC5, Spec §FR-016]

## Data Integrity And Business Rules

- [ ] CHK005 ¿Está definido sin ambigüedad el comportamiento al intentar eliminar físicamente una tienda con agentes? [Clarity, Spec §BR-011, Spec §US2.AC4]
- [ ] CHK006 ¿Está definido el comportamiento al desactivar una tienda que tiene agentes activos? [Clarity, Spec §BR-012, Spec §Edge Cases]
- [ ] CHK007 ¿Está definido el comportamiento al desactivar un agente bancario con asignaciones activas? [Clarity, Spec §BR-015, Spec §FR-017]
- [ ] CHK008 ¿El requisito de asignaciones solapadas define el código de error y el comportamiento esperado? [Clarity, Spec §BR-008, Spec §FR-008, Contracts §POST assignments]
- [ ] CHK009 ¿El historial de asignaciones especifica qué campos se conservan al desasignar? [Completeness, Spec §BR-008, Spec §US5.AC3]
- [ ] CHK010 ¿Está definido si un agente inactivo puede reactivarse manteniendo sus asignaciones históricas? [Gap, Spec §BR-010]

## Geographic Hierarchy

- [ ] CHK011 ¿La jerarquía región → provincia → distrito es consistente entre reglas, requisitos y modelo de datos? [Consistency, Spec §BR-002, Spec §FR-002, Data Model §regions/provinces/districts]
- [ ] CHK012 ¿Está definido si una provincia o distrito puede existir sin pertenecer a la jerarquía completa? [Gap, Spec §BR-002]
- [ ] CHK013 ¿Los filtros administrativos cubren todos los niveles geográficos declarados en alcance? [Completeness, Spec §FR-010, Contracts §Stores]

## Operator Lifecycle

- [ ] CHK014 ¿El cambio forzado de contraseña en primer login está definido como regla verificable? [Clarity, Spec §BR-016, Spec §FR-018]
- [ ] CHK015 ¿Está especificado qué funcionalidades quedan bloqueadas hasta que el operador cambie su contraseña? [Clarity, Spec §BR-016]
- [ ] CHK016 ¿La desactivación de un operador define explícitamente el impacto en sesiones activas y capacidad de login? [Consistency, Spec §BR-009, Spec §US5.AC4, Dependencia en 001-auth-session §FR-018]

## Consistency With Dependent Specifications

- [ ] CHK017 ¿El requisito de que un operador desactivado no pueda iniciar sesión es consistente con el comportamiento definido en 001-auth-session? [Consistency, Spec §US5.AC4, 001-auth-session §FR-005]
- [ ] CHK018 ¿La auditoría de cambios estructurales utiliza la misma tabla y convenciones de 001-auth-session? [Consistency, Spec §BR-014, Data Model §audit_logs]
- [ ] CHK019 ¿Los contratos de endpoints administrativos siguen las mismas convenciones de códigos HTTP que 001-auth-session? [Consistency, Contracts §Shared Rules]

## Non-Functional Requirements

- [ ] CHK020 ¿Los objetivos de rendimiento están cuantificados con métricas específicas para listados con filtros? [Measurability, Spec §SC-007]
- [ ] CHK021 ¿Está definido el comportamiento ante volúmenes superiores a 500 tiendas o 2000 agentes? [Gap, Spec §SC-007, Plan §Scale/Scope]
- [ ] CHK022 ¿Los requisitos de paginación cubren todas las entidades con riesgo de volumen (regiones, tiendas, agentes, asignaciones)? [Coverage, Spec §Operational Quality Constraints]

## Edge Cases And Recovery

- [ ] CHK023 ¿Está definido el comportamiento al modificar la tienda o el banco de un agente que ya tiene operaciones registradas? [Clarity, Spec §Edge Cases]
- [ ] CHK024 ¿Está definido el comportamiento cuando un administrador intenta desactivar la última tienda activa? [Gap]
- [ ] CHK025 ¿Los requisitos de migraciones reversibles cubren todas las tablas nuevas de esta capacidad? [Completeness, Plan §Migration Plan, Data Model §Migration Sequencing]

## Dependencies And Assumptions

- [ ] CHK026 ¿La dependencia en 001-auth-session especifica qué versión o estado mínimo requiere? [Clarity, Spec §Dependencies]
- [ ] CHK027 ¿La asunción de referencias geográficas informativas sin validación oficial está documentada como aceptación de riesgo? [Assumption, Spec §Assumptions]
- [ ] CHK028 ¿La entrega de contraseña inicial del operador define responsabilidad del administrador y canal? [Clarity, Spec §Assumptions]

## Notes

- Marcar cada elemento cuando los artefactos expresen una respuesta clara, consistente y verificable.
- Los elementos `[Gap]`, `[Ambiguity]` o `[Conflict]` señalan requisitos que podrían necesitar ajuste antes de `/speckit.tasks`.

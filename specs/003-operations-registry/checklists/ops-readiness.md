# Operations Requirements Readiness Checklist: Registro de Operaciones

**Purpose**: Evaluar claridad, completitud y consistencia de los requisitos operacionales antes de generar tareas
**Created**: 2026-07-22
**Feature**: [spec.md](../spec.md)
**Audience**: Revisor antes de `/speckit.tasks`
**Depth**: Estándar, con foco en integridad de datos, precisión y autorización

## Data Integrity And Precision

- [ ] CHK001 ¿El requisito de monto mayor que cero está definido como regla verificable con mensaje de error específico? [Clarity, Spec §BR-004, FR-004]
- [ ] CHK002 ¿El tipo de dato para montos está explícitamente definido como DECIMAL y se prohíbe float/double? [Clarity, Spec §BR-013, FR-016, Plan §Storage]
- [ ] CHK003 ¿Está definida la precisión decimal por moneda y el comportamiento ante más decimales de los permitidos? [Gap, Spec §Edge Cases, Plan §Decimal Precision]
- [ ] CHK004 ¿El mecanismo de idempotencia está especificado con suficiente detalle para ser implementado sin ambigüedad? [Clarity, Spec §BR-008, FR-007, Plan §Idempotency]
- [ ] CHK005 ¿Está definido el comportamiento cuando el idempotency key ya existe (devolver operación original vs rechazar)? [Clarity, Spec §Edge Cases, Contracts §POST /operations]

## Annulment Rules

- [ ] CHK006 ¿La ventana de anulación del operador está cuantificada y es configurable? [Clarity, Spec §BR-011, FR-013]
- [ ] CHK007 ¿Está definido el comportamiento cuando un operador intenta anular fuera de la ventana? [Clarity, Spec §US4.AC2, Contracts §POST annul]
- [ ] CHK008 ¿Los metadatos de anulación (usuario, fecha, motivo, valor original) están enumerados explícitamente como obligatorios? [Completeness, Spec §BR-011, FR-014]
- [ ] CHK009 ¿Está definido que una operación ya anulada no puede anularse nuevamente? [Clarity, Spec §US4.AC5]
- [ ] CHK010 ¿La exclusión de operaciones anuladas de totales activos está especificada para todos los agregados (cantidad, monto, entradas, salidas)? [Completeness, Spec §BR-012, FR-015]

## Authorization

- [ ] CHK011 ¿Está definido que el operador solo puede registrar en agentes asignados activos? [Clarity, Spec §BR-003, FR-003]
- [ ] CHK012 ¿El usuario registrador obtenido de sesión está definido como no modificable desde formulario ni parámetros? [Clarity, Spec §BR-005, FR-005]
- [ ] CHK013 ¿La restricción del operador a solo consultar sus propias operaciones está especificada en reglas, requisitos y contratos? [Consistency, Spec §FR-009, FR-017, Contracts §GET /operations]
- [ ] CHK014 ¿Los permisos de anulación diferencian explícitamente administrador (sin restricción) de operador (ventana + propias)? [Clarity, Spec §BR-010, FR-012–FR-013]

## Temporal Rules

- [ ] CHK015 ¿La ventana retroactiva está cuantificada (24 horas) y es configurable? [Clarity, Spec §BR-006, FR-006]
- [ ] CHK016 ¿Las fechas futuras están explícitamente prohibidas? [Clarity, Spec §BR-006, FR-006, US2.AC5]
- [ ] CHK017 ¿La zona horaria de presentación y el almacenamiento UTC son consistentes entre spec, plan y modelo de datos? [Consistency, Spec §BR-013, Plan §Time & Money, Data Model §Conventions]

## Type Catalog

- [ ] CHK018 ¿La unicidad de nombre de tipo por banco y entre tipos generales está definida sin ambigüedad? [Clarity, Spec §BR-002, Data Model §operation_types]
- [ ] CHK019 ¿La combinación de tipos del banco más tipos generales en el formulario está especificada? [Clarity, Spec §BR-002, Clarifications §Q1]
- [ ] CHK020 ¿La desactivación de un tipo impide nuevos registros pero conserva operaciones existentes? [Clarity, Spec §US1.AC3]

## Consistency With Dependent Features

- [ ] CHK021 ¿Las dependencias en 001-auth-session y 002-operational-structure especifican qué entidades y estados se requieren? [Clarity, Spec §Dependencies]
- [ ] CHK022 ¿El modelo de datos de operaciones referencia correctamente las FK de tablas creadas en 002? [Consistency, Data Model §operations, 002 Data Model]

## Edge Cases And Recovery

- [ ] CHK023 ¿Está definido el comportamiento cuando un operador intenta registrar justo cuando su asignación expira? [Edge Case, Spec §Edge Cases]
- [ ] CHK024 ¿Está definido el comportamiento para fechas efectivas en días con cambio de horario? [Edge Case, Spec §Edge Cases]
- [ ] CHK025 ¿Los requisitos de migraciones reversibles cubren las tablas de operaciones y tipos? [Completeness, Plan §Migration Plan]

## Notes

- La ventana retroactiva y la ventana de anulación son configurables; el plan delega los valores por defecto en `config/operations.php`.
- La precisión decimal por moneda (PEN = 2 decimales) está implícita en DECIMAL(18,2); si se añaden monedas con 0 o 3 decimales, se requiere ajuste.

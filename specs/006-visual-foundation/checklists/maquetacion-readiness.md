# Maquetación Requirements Quality Checklist: Fundamentos Visuales

**Purpose**: Evaluar la calidad, claridad y verificabilidad de los requisitos de maquetación basados en Google Stitch
**Created**: 2026-07-22
**Feature**: [spec.md](../spec.md)
**Audience**: Revisor de diseño antes de `/speckit.tasks`
**Depth**: Estándar

## Stitch Reference Traceability

- [ ] CHK001 ¿Cada pantalla de la spec tiene un identificador canónico y una referencia a su carpeta Stitch correspondiente? [Completeness, Spec §US1–US6, Plan §Stitch → Laravel Screen Mapping]
- [ ] CHK002 ¿El grado de fidelidad a Stitch está definido de forma no ambigua (intención visual, no píxel-perfecto)? [Clarity, Spec §Clarifications Q1, Spec §BR-001]
- [ ] CHK003 ¿Las mejoras permitidas sobre Stitch están delimitadas por criterios objetivos (accesibilidad, consistencia, legibilidad, rendimiento, semántica HTML)? [Clarity, Spec §Mejoras autorizadas, Spec §BR-007]
- [ ] CHK004 ¿La precedencia entre spec, DESIGN.md, screen.png y code.html está definida de forma explícita para presentación visual? [Clarity, Spec §Regla de precedencia]

## Visual System Completeness

- [ ] CHK005 ¿Los colores están definidos con tokens semánticos que expresan propósito y no solo apariencia? [Clarity, Spec §BR-001, Plan §Design Token Translation Strategy]
- [ ] CHK006 ¿La tipografía está definida sin dependencias externas (system-ui en vez de Google Fonts)? [Clarity, Plan §Design Token Translation Strategy]
- [ ] CHK007 ¿La escala de espaciado, tamaños, radios, bordes y sombras están especificados en un solo lugar? [Completeness, Spec §US1, Plan §CSS Structure]
- [ ] CHK008 ¿Los breakpoints responsive están cuantificados con valores concretos (375, 768, 1280, 1440 px)? [Clarity, Spec §BR-004, Plan §Responsive Strategy]
- [ ] CHK009 ¿La entrada y salida de efectivo se distinguen por texto (+/-), iconografía y señal visual, no solo por color? [Consistency, Spec §BR-002, Spec §SC-006]
- [ ] CHK010 ¿Los estados de operación (ACTIVA, ANULADA) y cierre (ACTIVO, CONFIRMADO, REABIERTO) tienen representación visual definida? [Completeness, Spec §US1.AC5, Spec §US6.AC6]

## Responsive Behavior

- [ ] CHK011 ¿Existe un requisito verificable que prohíba el desplazamiento horizontal global en los 4 breakpoints? [Measurability, Spec §BR-004, Spec §SC-004]
- [ ] CHK012 ¿El comportamiento de tablas en móvil está definido con regla concreta (scroll horizontal para datos, tarjetas para ≤3 columnas)? [Clarity, Spec §BR-005, Spec §Clarifications Q3]
- [ ] CHK013 ¿La presentación de filtros en móvil está definida (off-canvas con botón "Filtros")? [Clarity, Spec §BR-006, Spec §Clarifications Q4]
- [ ] CHK014 ¿Las funciones esenciales que no deben desaparecer en móvil están identificadas? [Gap, Spec §BR-006]

## Interface States

- [ ] CHK015 ¿Los estados de componentes (normal, hover, focus, active, disabled, loading, error, success) están enumerados de forma exhaustiva? [Completeness, Spec §Historia de usuario 1, Spec §FR-012]
- [ ] CHK016 ¿Los estados de autenticación están especificados con query params verificables (?state=error, throttled, etc.)? [Clarity, Spec §US3, Plan §Data Model State Query Parameters]
- [ ] CHK017 ¿Los estados de carga (skeleton, spinner) y error (mensaje, reintento) están definidos? [Coverage, Spec §Edge Cases, Spec §US4, Spec §US5]
- [ ] CHK018 ¿El estado vacío está definido como requisito explícito con mensaje contextual? [Clarity, Spec §Edge Cases, Spec §US4.AC4]

## Accessibility Requirements

- [ ] CHK019 ¿La conformidad con WCAG 2.2 nivel AA está declarada como objetivo verificable? [Clarity, Spec §Accesibilidad]
- [ ] CHK020 ¿La navegación por teclado está especificada con criterios observables (foco visible, orden de tabulación, focus trap en modales)? [Measurability, Spec §Accesibilidad, Spec §SC-003]
- [ ] CHK021 ¿Los requisitos de contraste están cuantificados (≥4.5:1 texto normal, ≥3:1 texto grande)? [Clarity, Plan §Accessibility Strategy]
- [ ] CHK022 ¿Las etiquetas de formulario asociadas a campos (for/id) y mensajes de error descriptivos (aria-describedby) están especificados? [Completeness, Spec §Accesibilidad]
- [ ] CHK023 ¿El respeto por preferencias de movimiento reducido está especificado (prefers-reduced-motion)? [Completeness, Spec §Accesibilidad, Plan §Accessibility Strategy]

## Separation Of Concerns

- [ ] CHK024 ¿La spec declara explícitamente que no implementa lógica de negocio, autenticación real, persistencia ni CRUD? [Clarity, Spec §Out of Scope]
- [ ] CHK025 ¿Los datos de demostración están aislados en fixtures separados de las vistas Blade? [Clarity, Spec §FR-014, Plan §Demo Data Strategy]
- [ ] CHK026 ¿Existe un requisito que prohíba copiar directamente el HTML de Stitch como código de producción? [Clarity, Spec §BR-008]

## Component Reusability

- [ ] CHK027 ¿Los componentes reutilizables están identificados con nombre y ubicación en la estructura de archivos? [Completeness, Plan §Component Inventory, Plan §Blade Structure]
- [ ] CHK028 ¿Existe un requisito que prohíba la duplicación innecesaria de HTML entre pantallas? [Clarity, Spec §BR-009, Spec §SC-002]
- [ ] CHK029 ¿La relación entre maquetación y vistas existentes está definida (reemplazo directo)? [Clarity, Spec §Clarifications Q2]

## Terminology And Financial Presentation

- [ ] CHK030 ¿Está definido que "monto bruto operado" es la etiqueta canónica y no debe aparecer "ingreso", "utilidad" o "ganancia"? [Clarity, Spec §BR-003, Spec §SC-007]
- [ ] CHK031 ¿El formato de presentación de montos está especificado (S/ X,XXX.XX, entradas con +, salidas con -)? [Clarity, Spec §US1, Plan §Technical Context]
- [ ] CHK032 ¿La pantalla de cierre diario especifica que no debe presentarse como conciliación bancaria? [Clarity, Spec §US6, Spec §SC-007]

## Anti-Ambiguity

- [ ] CHK033 ¿La spec evita adjetivos no observables como "moderno", "bonito", "intuitivo" o "revolucionario" sin criterios de verificación? [Ambiguity]
- [ ] CHK034 ¿Cada criterio de éxito (SC-001 a SC-009) es verificable sin requerir interpretación subjetiva? [Measurability, Spec §Success Criteria]
- [ ] CHK035 ¿El procedimiento de revisión manual está documentado con pasos concretos y verificables? [Completeness, Plan §Manual Review Procedure]

## Notes

- Las referencias a secciones del Plan son informativas; la validación principal es contra la Spec.
- Los items marcados como [Gap] señalan áreas donde la especificación podría necesitar mayor detalle.
- La revisión manual (Plan §Manual Review Procedure) complementa pero no sustituye este checklist de requisitos.

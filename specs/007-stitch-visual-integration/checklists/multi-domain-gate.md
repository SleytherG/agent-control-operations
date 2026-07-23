# Multi-Domain Requirements Gate Checklist: Integración Visual Stitch

**Purpose**: Validar calidad, completitud y consistencia de los requisitos de integración visual antes de iniciar implementación. Cubre UX/visuales, seguridad/permisos, migración/limpieza, accesibilidad/responsive.
**Created**: 2026-07-22
**Feature**: [spec.md](../spec.md)
**Depth**: Gate pre-implementación (exhaustivo)

**Note**: Este checklist evalúa la CALIDAD DE LOS REQUISITOS, no verifica la implementación. Cada item pregunta si el requisito está bien definido, es medible, consistente y completo.

## Visual & UX Requirements Quality

- [ ] CHK001 - ¿Están definidos los requisitos de jerarquía visual para cada tipo de tarjeta métrica (KPI principal vs secundaria)? [Clarity, Spec §C06]
- [ ] CHK002 - ¿Especifica la spec qué componentes Stitch son obligatorios vs opcionales por pantalla? [Completeness, Plan §Matriz de Componentes]
- [ ] CHK003 - ¿Están definidos los requisitos de espaciado, alineación y consistencia tipográfica entre módulos migrados? [Gap]
- [ ] CHK004 - ¿Está cuantificado el estándar de fidelidad visual con criterios verificables (ej. "mismos componentes, mismos CSS tokens")? [Clarity, BR-010]
- [ ] CHK005 - ¿Están documentadas las diferencias visuales esperadas entre la vista demo y la vista productiva para cada pantalla? [Completeness, FR-016]

## Component Contract Requirements Quality

- [ ] CHK006 - ¿Define cada contrato de vista (C01–C08) todas las variables obligatorias que el controlador debe pasar? [Completeness, contracts/view-contracts.md]
- [ ] CHK007 - ¿Están especificados los tipos de datos esperados para cada variable en los contratos de vista? [Clarity, contracts/view-contracts.md]
- [ ] CHK008 - ¿Está documentado el formato de salida del DashboardQueryService para que la vista Stitch lo consuma sin transformación adicional? [Completeness, data-model.md]
- [ ] CHK009 - ¿Especifican los contratos qué variables son provistas por el middleware vs por el controlador, sin ambigüedad? [Clarity, contracts/view-contracts.md]
- [ ] CHK010 - ¿Están definidos los requisitos de adaptación para los 4 componentes `x-screen.*` que necesitan aceptar datos Eloquent en lugar de arrays mock? [Completeness, Plan §Matriz de Componentes]

## Session & Auth UI Requirements Quality

- [ ] CHK011 - ¿Está especificado el mapping completo de `$loginState` → mensajes de error visuales (credentials, disabled, throttled, network-error)? [Completeness, Spec §User Story 2]
- [ ] CHK012 - ¿Está definido el comportamiento visual del botón de login en estado `loading` (spinner, texto, disabled)? [Clarity, Spec §User Story 2]
- [ ] CHK013 - ¿Especifica la spec cómo obtiene el contador de sesión la fecha de expiración real del servidor (no simulación)? [Clarity, FR-005]
- [ ] CHK014 - ¿Está definido el flujo visual completo del modal de expiración: advertencia 30s → renovar → logout → expiración forzada? [Completeness, Spec §User Story 2]
- [ ] CHK015 - ¿Son consistentes los requisitos de sesión entre el indicador del layout (M0) y el modal de expiración (spec 001)? [Consistency, Spec §FR-005 ↔ Spec 001]

## Security & Authorization Requirements Quality

- [ ] CHK016 - ¿Está definido explícitamente que el sidebar DEBE ocultar enlaces no autorizados pero el servidor DEBE rechazar peticiones no autorizadas independientemente? [Clarity, FR-004]
- [ ] CHK017 - ¿Están especificados los requisitos de autorización para cada acción visual (confirmar cierre, reabrir, anular, desactivar) usando `@can`/Gate? [Completeness, Spec §User Story 7, §User Story 8]
- [ ] CHK018 - ¿Define la spec cómo debe comportarse la interfaz ante un 401 (sesión expirada) vs 403 (sin permisos) de forma diferenciada? [Coverage, Spec §User Story 9]
- [ ] CHK019 - ¿Está documentado que ningún rol, identificador de usuario o permiso se envía desde el navegador como parámetro de formulario? [Clarity, Spec §Datos y seguridad]
- [ ] CHK020 - ¿Están definidos los requisitos de validación server-side para datos que la interfaz Stitch muestra pero no debe confiar (IDs, roles, totales)? [Completeness, Spec §Datos y seguridad]

## Migration Completeness Requirements Quality

- [ ] CHK021 - ¿Está documentada la correspondencia 1:1 entre cada pantalla demo y su pantalla productiva destino? [Completeness, Plan §Matriz de Migración]
- [ ] CHK022 - ¿Especifica la spec qué artefactos se ELIMINAN vs se CONSERVAN para documentación, sin ambigüedad? [Clarity, FR-013/FR-014/FR-015, Clarifications Q2]
- [ ] CHK023 - ¿Está definido el orden de migración incremental con criterios de verificación por módulo? [Completeness, Plan §Fase 0]
- [ ] CHK024 - ¿Está especificada la estrategia de rollback por módulo con comandos concretos? [Clarity, Plan §Estrategia de rollback]
- [ ] CHK025 - ¿Define la spec cómo se verifica que un módulo migrado no rompe los módulos aún no migrados? [Coverage, Plan §Estrategia de disponibilidad continua]
- [ ] CHK026 - ¿Están identificadas todas las dependencias entre módulos migrados (ej. M4 depende de M0 para el layout)? [Completeness, Plan §Fase 0]

## Responsive & Accessibility Requirements Quality

- [ ] CHK027 - ¿Están cuantificados los breakpoints responsive con valores exactos (375/768/1280/1440px)? [Clarity, BR-004]
- [ ] CHK028 - ¿Está especificado el comportamiento de tablas anchas en viewport pequeño (scroll horizontal en contenedor vs transformación)? [Clarity, Spec §Responsive design]
- [ ] CHK029 - ¿Define la spec qué acciones esenciales NO deben desaparecer en resoluciones pequeñas? [Gap]
- [ ] CHK030 - ¿Están definidos los requisitos de accesibilidad con criterios WCAG 2.2 AA verificables (contraste, foco, teclado, etiquetas, áreas táctiles)? [Completeness, Spec §Accesibilidad]
- [ ] CHK031 - ¿Está especificado el requisito de `prefers-reduced-motion` para animaciones y transiciones CSS? [Coverage, Spec §Accesibilidad]

## Error & Edge State Requirements Quality

- [ ] CHK032 - ¿Están definidos los requisitos de estado vacío para cada pantalla que puede no tener datos (dashboard sin operaciones, historial sin resultados, cierre sin operaciones)? [Completeness, Spec §User Story 9]
- [ ] CHK033 - ¿Está especificado el comportamiento del formulario de registro cuando el operador no tiene agentes asignados? [Coverage, Spec §Edge Cases]
- [ ] CHK034 - ¿Define la spec cómo debe comportarse la interfaz ante error 500 del servidor (sin exponer detalles internos)? [Clarity, Spec §Edge Cases]
- [ ] CHK035 - ¿Está especificado el comportamiento de múltiples pestañas con sesión compartida (contadores independientes, misma expiración)? [Coverage, Spec §Edge Cases]
- [ ] CHK036 - ¿Define la spec qué datos del formulario se preservan tras un error de validación y cuáles se resetean? [Gap, Spec §User Story 4]

## Performance Requirements Quality

- [ ] CHK037 - ¿Está especificado que Chart.js DEBE cargarse solo en dashboards, no en login ni formularios? [Clarity, BR-006]
- [ ] CHK038 - ¿Están definidos los requisitos de server-side aggregation para dashboards (no descargar colecciones completas al navegador)? [Clarity, BR-007, FR-006/FR-009]
- [ ] CHK039 - ¿Especifica la spec límites de paginación (25/page) y que los filtros deben usar índices de BD? [Completeness, Spec §Rendimiento]

## Notes

- Items CHK001–CHK039 cubren 7 dominios de calidad de requisitos
- Cada item evalúa completitud, claridad, consistencia, cobertura o medibilidad de los requisitos
- NO evalúa implementación — solo si los requisitos están bien definidos
- Marcar `[x]` cuando el requisito esté validado como completo y no ambiguo
- Los items marcados `[ ]` requieren actualización de spec/plan/contracts antes de implementar

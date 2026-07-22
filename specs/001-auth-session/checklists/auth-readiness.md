# Authentication Requirements Readiness Checklist: Autenticación y Ciclo de Sesión

**Purpose**: Evaluar claridad, completitud, consistencia y verificabilidad de los requisitos antes de generar tareas
**Created**: 2026-07-22
**Feature**: [spec.md](../spec.md)
**Audience**: Revisor de seguridad antes de `/speckit.tasks`
**Depth**: Estándar, con cobertura equilibrada de seguridad, UX y operación

## Requirement Completeness

- [ ] CHK001 ¿Están definidos los requisitos para cada etapa del ciclo de sesión: login, acceso, advertencia, renovación, logout, expiración, revocación y replay? [Completeness, Spec §FR-001–FR-015]
- [ ] CHK002 ¿Están documentadas todas las condiciones que impiden autenticar o renovar a un usuario inactivo? [Completeness, Spec §FR-002, FR-005, FR-008, FR-018]
- [ ] CHK003 ¿Se especifica si la reactivación de usuarios está fuera de esta capacidad o requiere requisitos adicionales? [Gap, Spec §Scope]
- [ ] CHK004 ¿Están definidos los requisitos de creación y mantenimiento interno de credenciales, o se declaran explícitamente como dependencia de otra capacidad? [Gap, Spec §Dependencies]
- [ ] CHK005 ¿Se documenta qué información mínima puede mostrar el historial y qué metadatos sensibles deben permanecer excluidos? [Completeness, Spec §FR-026–FR-028, Contract §GET /sessions]
- [ ] CHK006 ¿Están definidos los requisitos de retención para sesiones, refresh tokens consumidos, eventos y auditorías, o su decisión está explícitamente diferida? [Gap, Spec §Assumptions, Plan §Audit Strategy]

## Requirement Clarity

- [ ] CHK007 ¿La expresión “misma combinación de identificador y origen” define suficientemente qué constituye el origen y cómo evita bloquear cuentas de terceros? [Clarity, Spec §BR-009, Assumptions]
- [ ] CHK008 ¿Está definido sin ambigüedad el instante límite inclusivo o exclusivo para access token, refresh token y sesión absoluta? [Clarity, Spec §BR-006, BR-015, BR-017]
- [ ] CHK009 ¿Los cuatro motivos terminales distinguen claramente estado de sesión, evento observado y causa de finalización? [Clarity, Spec §BR-013, Data Model §auth_sessions]
- [ ] CHK010 ¿Está cuantificado el tamaño máximo de página del historial o existe una regla explícita para definirlo antes de implementar? [Clarity, Spec §FR-028, Plan §Performance Goals]
- [ ] CHK011 ¿La frase “respuesta de autenticación inválida” identifica las clases de respuesta que deben limpiar estado y redirigir? [Ambiguity, Spec §FR-013, Contract §Shared Rules]
- [ ] CHK012 ¿Está definido qué significa “inmediatamente” para revocar sesiones durante la desactivación de un usuario? [Clarity, Spec §SC-006]

## Requirement Consistency

- [ ] CHK013 ¿Son consistentes la expiración de cinco minutos, el refresh con igual vencimiento y el límite absoluto de ocho horas en reglas, requisitos y criterios medibles? [Consistency, Spec §BR-004, BR-015, BR-017, FR-004, FR-010, SC-010, SC-012]
- [ ] CHK014 ¿Coinciden los motivos de finalización permitidos entre reglas, requisitos, modelo de datos, eventos y contratos? [Consistency, Spec §BR-013, FR-015, Data Model §auth_sessions, Plan §Audit Strategy]
- [ ] CHK015 ¿Es consistente permitir sesiones simultáneas con que logout afecte solo a la sesión vigente y desactivación afecte a todas? [Consistency, Spec §BR-016, FR-012, FR-018, FR-025]
- [ ] CHK016 ¿La consulta global del administrador y el aislamiento del operador se expresan igual en actores, escenarios, requisitos, políticas y contrato? [Consistency, Spec §Actors, US5, FR-026–FR-028, Contract §GET /sessions]
- [ ] CHK017 ¿La prohibición de renovación silenciosa es consistente entre alcance, reglas, requisitos, contrato y flujo técnico? [Consistency, Spec §Out of Scope, BR-006, FR-014, Contract §POST /auth/refresh]
- [ ] CHK018 ¿Las obligaciones de no exponer tokens son consistentes entre almacenamiento navegador, respuestas, logs, auditoría y comprobación de salud? [Consistency, Spec §FR-020, SC-008, Plan §Audit Strategy, Contract §Shared Rules]

## Acceptance Criteria Quality

- [ ] CHK019 ¿Cada requisito funcional puede trazarse a uno o más escenarios de aceptación o criterios medibles? [Traceability, Spec §User Scenarios, FR-001–FR-028, SC-001–SC-013]
- [ ] CHK020 ¿Los criterios que exigen 100% identifican un universo de casos acotado y reproducible? [Measurability, Spec §SC-001–SC-006, SC-010–SC-013]
- [ ] CHK021 ¿El objetivo de respuesta en dos segundos define la carga interna bajo la cual debe medirse? [Gap, Spec §SC-007, Plan §Scale/Scope]
- [ ] CHK022 ¿El criterio de desviación visual máxima de un segundo contempla pestañas ocultas, suspensión y reanudación? [Measurability, Spec §SC-003, Edge Cases]
- [ ] CHK023 ¿El criterio multidispositivo especifica tamaños, capacidades de interacción y condiciones objetivas de éxito suficientes? [Clarity, Spec §SC-009, Plan §Portability and UI]

## Scenario Coverage

- [ ] CHK024 ¿Los escenarios cubren login por usuario y correo, credenciales erróneas, usuario inactivo y throttling sin revelar existencia de cuenta? [Coverage, Spec §US1]
- [ ] CHK025 ¿Los escenarios cubren continuar, cerrar, dejar vencer, recargar vencido y alcanzar el máximo de ocho horas? [Coverage, Spec §US2, US3]
- [ ] CHK026 ¿Los escenarios de autorización incluyen resultados positivos y negativos para desactivación e historial en ambos roles? [Coverage, Spec §US4, US5]
- [ ] CHK027 ¿Está definido el comportamiento cuando la desactivación compite con login, refresh o una petición protegida en curso? [Gap, Spec §US4, Plan §Explicit Renewal]
- [ ] CHK028 ¿Están documentados los estados de espera, éxito y error durante una renovación para impedir decisiones duplicadas del usuario? [Coverage, Spec §US2, Edge Cases, Plan §Frontend Timer]

## Edge Case Coverage

- [ ] CHK029 ¿La política estricta ante refresh concurrente y respuesta perdida está explicada como riesgo de revocación legítima, sin una gracia implícita? [Edge Case, Spec §BR-008, Edge Cases, Research §Atomic Rotation And Concurrency]
- [ ] CHK030 ¿Está definido el resultado cuando logout local ocurre pero la revocación de servidor no puede confirmarse por pérdida de conexión? [Edge Case, Spec §Edge Cases]
- [ ] CHK031 ¿Están cubiertos reloj adelantado/atrasado, cambio de visibilidad y tiempo de servidor como única autoridad? [Edge Case, Spec §BR-005, BR-014, FR-021, Edge Cases]
- [ ] CHK032 ¿Está definido qué ocurre ante fallo de base de datos o timeout durante una rotación, sin clasificarlo erróneamente como replay? [Gap, Plan §Explicit Renewal, Research §Atomic Rotation And Concurrency]
- [ ] CHK033 ¿Se especifica la conducta cuando una sesión expira sin nueva petición y luego aparece en el historial? [Edge Case, Spec §FR-022, Edge Cases, US5]

## Non-Functional Requirements

- [ ] CHK034 ¿Los requisitos de cookies, CSRF y HTTPS están formulados como obligaciones completas para todas las operaciones mutables? [Security, Constitution §V–VI, Plan §Technical Context, Contract §Shared Rules]
- [ ] CHK035 ¿Los requisitos de accesibilidad cubren foco del modal, teclado, lector de pantalla, mensajes de error y contador no dependiente solo de percepción visual? [Gap, Spec §US2, SC-009]
- [ ] CHK036 ¿Los requisitos de observabilidad distinguen eventos auditables, logs técnicos y datos expresamente prohibidos? [Completeness, Constitution §XII, Spec §Auditability, Plan §Audit Strategy]
- [ ] CHK037 ¿La estrategia de backup define alcance, frecuencia, retención, objetivo de recuperación y evidencia de restauración? [Gap, Plan §Deployment Procedure, Quickstart §Recovery And Operations]
- [ ] CHK038 ¿Los requisitos de hosting compartido cuantifican límites o supuestos relevantes de conexiones, memoria, reloj y tiempo de respuesta? [Gap, Plan §Shared Hosting Risks, Spec §SC-007]

## Dependencies & Assumptions

- [ ] CHK039 ¿La dependencia del catálogo interno de usuarios define claramente qué datos y garantías deben existir antes de habilitar login? [Dependency, Spec §Dependencies, Data Model §users]
- [ ] CHK040 ¿La suposición de navegadores modernos con cookies y JavaScript identifica versiones mínimas o capacidades requeridas? [Assumption, Spec §Assumptions]
- [ ] CHK041 ¿La necesidad de MySQL/MariaDB real para concurrencia está documentada como condición de validación y no como comportamiento funcional? [Consistency, Plan §Testing Strategy, Research §Test Database]
- [ ] CHK042 ¿Está explícito que los dominios operacionales diferidos no forman parte de las migraciones ni aceptación de esta capacidad? [Boundary, Plan §Scale/Scope, Data Model §Target Operational Model]

## Ambiguities & Conflicts

- [ ] CHK043 ¿Existe una definición canónica para access token, refresh token, credencial de renovación, sesión y evento de sesión, evitando sinónimos contradictorios? [Terminology, Spec §Business Rules, Key Entities]
- [ ] CHK044 ¿Está resuelta la posible tensión entre refresh token con vencimiento de cinco minutos y retención de su hash consumido hasta ocho horas? [Consistency, Spec §BR-017, Data Model §auth_refresh_tokens]
- [ ] CHK045 ¿Está resuelta la diferencia entre sesión `REVOKED` y los motivos `LOGOUT_MANUAL`, `REVOCACION_ADMINISTRATIVA` y `FALLO_SEGURIDAD`? [Clarity, Spec §BR-013, Data Model §auth_sessions]
- [ ] CHK046 ¿Está explícitamente excluido que cerrar el navegador, ocultar la pestaña o eliminar estado visual equivalga a revocación del servidor? [Boundary, Spec §Out of Scope, US3]

## Notes

- Marcar cada elemento cuando los artefactos expresen una respuesta clara, consistente y verificable.
- Los elementos `[Gap]`, `[Ambiguity]` o `[Conflict]` señalan requisitos que podrían necesitar ajuste antes de `/speckit.tasks`.
- Este checklist evalúa la calidad de los requisitos; no valida código ni sustituye las pruebas obligatorias.

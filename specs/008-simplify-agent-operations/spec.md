# Feature Specification: Operaciones Generales por Agente

**Feature Branch**: `008-simplify-agent-operations`

**Created**: 2026-07-23

**Status**: Draft

**Input**: Corrección mayor del producto para convertirlo en un cuaderno digital centralizado de operaciones generales por agente y trabajador, sin segmentación funcional por bancos ni separación entre tiendas y agentes, con control diario de efectivo y saldo digital y fidelidad visual pixel-perfect a Google Stitch.

## Clarifications

### Session 2026-07-23

- Q: ¿Se permiten operaciones retroactivas (fecha/hora distinta a "ahora")? → A: Solo el administrador puede registrar con fecha pasada, con límite configurable inicial de 24h; el operador siempre usa `now()`.
- Q: ¿Se puede confirmar un cierre con diferencias de efectivo o digital distintas de cero? → A: Sí, mostrando advertencia visible y exigiendo motivo del administrador; la confirmación no se bloquea por diferencias.
- Q: ¿Qué sucede cuando el JWT expira durante el envío de un formulario? → A: Se redirige al login con mensaje "Sesión expirada" y los datos del formulario se preservan en sesión flash para restaurarlos tras re-autenticarse.

## Problem & Actors *(mandatory)*

**Problem**: El modelo vigente representa el punto físico mediante tiendas, bancos y agentes bancarios relacionados, aunque el negocio opera cada punto como una sola unidad llamada Agente. Esa segmentación obliga a capturar y filtrar información bancaria que el cliente no necesita en el MVP, fragmenta la atribución de operaciones por trabajador y dificulta comparar el efectivo y saldo digital inicial, esperado y real al cierre del día. El producto debe corregirse sin perder operaciones históricas, trazabilidad, seguridad ni las referencias visuales aprobadas.

**Actors**:

- **Administrador propietario**: Administra agentes y operadores, asigna personal, consulta toda la actividad, registra o confirma aperturas, confirma o reabre cierres, anula operaciones, revisa auditoría y revoca sesiones.
- **Operador**: Trabaja dentro de un agente asignado, registra operaciones propias con fecha/hora actual del servidor, consulta su hoja digital y dashboard, y puede preparar aperturas o cierres cuando una regla aprobada lo habilita.
- **Responsable de migración**: Inventaría el modelo anterior, protege respaldos, ejecuta transformaciones verificables, documenta equivalencias y garantiza rollback sin pérdida silenciosa.
- **Responsable de producto/diseño**: Valida terminología, composición pixel-perfect, accesibilidad y diferencias justificadas frente a Google Stitch.

**Change Classification**: Corrección mayor de especificación y cambio arquitectónico del dominio, con migración funcional y visual incompatible con partes de las especificaciones 002, 003, 004, 005 y 007.

**Governance Gate**: La constitución vigente todavía describe agentes bancarios ubicados en tiendas y campos de operación asociados a ese dominio. Además, su Principio IX prohíbe datos de clientes en el MVP hasta contar con una especificación independiente, análisis de privacidad y reglas aprobadas; esta corrección solicita un nombre/referencia opcional. Antes de aprobar el plan o implementar, se DEBE aprobar una enmienda constitucional o una excepción completa que cubra ambos conflictos conforme al Principio XIII. Por su amplitud, también se DEBE aprobar su ejecución como programa de incrementos pequeños, reversibles e independientemente verificables. Esta especificación no puede sustituir la constitución por sí sola.

## Scope *(mandatory)*

### In Scope

- Sustituir Tienda, Banco y Agente bancario por Agente como único punto físico funcional.
- Migrar datos históricos y relaciones anteriores a agentes, asignaciones, operaciones y cierres sin pérdida silenciosa.
- Administrar agentes y asignaciones temporales de operadores con historial.
- Mantener un agente activo autorizado como contexto operacional visible.
- Registrar operaciones generales con código interno, operador autenticado, agente autorizado, cliente opcional, monto, fecha/hora, observación y efectos monetarios inmutables.
- Administrar tipos generales con efectos independientes sobre efectivo y saldo digital.
- Proporcionar hoja digital personal, historial paginado y dashboards por operador y administrador.
- Registrar apertura diaria y cierre operativo con saldos iniciales, esperados, reales y diferencias.
- Mantener anulación lógica, auditoría completa y seguridad de sesión existente.
- Adaptar todas las pantallas productivas y textos al dominio corregido.
- Reproducir pixel-perfect las referencias Stitch aplicables en cuatro viewports y documentar desviaciones.
- Retirar rutas, formularios, filtros y módulos productivos de bancos y tiendas cuando la migración haya sido validada.
- Entregar la corrección mediante incrementos verificables: migración/base Agente; agentes/asignaciones; tipos/operaciones; apertura/cierre; consultas/dashboards; sesión/visual; retiro definitivo del modelo anterior.
- Validar las siete pantallas obligatorias: Login, Registro de operación, Historial, Dashboard del operador, Dashboard administrativo, Cierre operativo diario y Advertencia de expiración.

### Out of Scope

- Catálogo, segmentación o integración con bancos, cajas, billeteras o entidades financieras.
- Conciliación bancaria, contabilidad formal, comisiones, ganancias o rentabilidad.
- Maestro estructurado de clientes, DNI, cuentas, tarjetas, teléfonos o correos obligatorios.
- Turnos formales, asistencia, planillas o inferencia de jornada laboral.
- Aplicación móvil nativa, modo offline, geolocalización, importación masiva o exportación contable.
- Multiempresa SaaS y control detallado por cada canal digital.
- Confirmación de que una operación fue procesada por una entidad externa.

### Business Rules

- **BR-001**: Agente es el único punto físico del dominio productivo; Tienda y Agente bancario no pueden coexistir como entidades funcionales separadas al finalizar la migración.
- **BR-002**: Una operación no requiere banco, tienda ni terminal bancaria.
- **BR-003**: El operador de una operación proviene exclusivamente de la sesión autenticada y no puede editarse desde el cliente.
- **BR-004**: El agente de una operación proviene exclusivamente del contexto activo que el servidor verificó contra una asignación vigente.
- **BR-005**: Una sola asignación activa selecciona automáticamente el agente; múltiples asignaciones requieren selección explícita entre agentes autorizados.
- **BR-006**: Dos operadores pueden registrar actividad simultánea en el mismo agente; cada operación conserva su autor individual.
- **BR-007**: El monto es decimal, mayor que cero y usa PEN inicialmente.
- **BR-008**: Cada operación recibe un código interno único, visible, inmutable y generado automáticamente sin dependencia bancaria.
- **BR-009**: El cliente es texto breve opcional; no constituye un maestro de clientes ni autoriza datos bancarios sensibles.
- **BR-010**: Cada tipo general define efectos independientes sobre efectivo y saldo digital mediante entrada, salida o sin efecto.
- **BR-011**: La operación conserva un snapshot de los efectos monetarios aplicados; cambios futuros del tipo no alteran históricos.
- **BR-012**: Una operación no se elimina físicamente; la anulación conserva valores originales, autor, momento, motivo y transiciones de estado.
- **BR-013**: Las operaciones anuladas no participan en totales activos ni cierres activos, pero permanecen visibles.
- **BR-014**: Existe como máximo una apertura activa por agente y fecha de negocio.
- **BR-015**: El efectivo esperado es efectivo inicial más entradas de efectivo menos salidas de efectivo.
- **BR-016**: El saldo digital esperado es saldo digital inicial más entradas digitales menos salidas digitales.
- **BR-017**: Las diferencias son saldo real menos saldo esperado para cada medio.
- **BR-018**: Un cierre no puede confirmarse como cuadrado si contiene operaciones con efectos monetarios incompletos o inconsistentes.
- **BR-019**: Solo el administrador propietario confirma definitivamente o reabre un cierre; toda reapertura exige motivo y auditoría.
- **BR-020**: La fecha de negocio y periodos se interpretan en `America/Lima`; el día abarca 00:00:00 a 23:59:59.999999 local.
- **BR-021**: Semana inicia lunes 00:00 y termina domingo 23:59:59.999999; mes, trimestre, semestre y año usan sus límites naturales en `America/Lima`.
- **BR-022**: Monto bruto operado es la suma de valores absolutos de operaciones activas y nunca se presenta como ganancia o utilidad.
- **BR-023**: El temporizador usa el vencimiento del servidor, advierte a los 30 segundos, renueva solo por acción explícita y no autoriza peticiones vencidas.
- **BR-024**: Ocultar controles no sustituye autorización; todas las restricciones se aplican en servidor.
- **BR-025**: La referencia visual es `screen.png`; `code.html` solo orienta estructura y no define reglas de negocio.
- **BR-026**: El texto opcional de cliente solo puede ser visto por el operador autor y el administrador propietario de su organización; no se usa para perfiles, contacto ni decisiones automatizadas.
- **BR-027**: Una apertura inicia en BORRADOR, puede ser registrada y confirmada por el administrador, y solo al confirmarse habilita el estado ABIERTO del día operacional.
- **BR-028**: El operador asignado puede preparar un cierre BORRADOR y pasarlo a PRESENTADO; solo el administrador puede pasar ABIERTO, BORRADOR, PRESENTADO o REABIERTO a CONFIRMADO, y CONFIRMADO a REABIERTO con motivo.
- **BR-029**: Después de un cierre CONFIRMADO no se aceptan nuevas operaciones para esa fecha hasta que el administrador lo REABRA; esta especificación no contempla una vía administrativa alternativa.
- **BR-030**: Cada incremento puede coexistir temporalmente con artefactos anteriores solo bajo un plan de compatibilidad/rollback; al concluir la migración no pueden coexistir rutas productivas duplicadas.
- **BR-031**: El operador registra siempre con `occurred_at = now()` del servidor; solo el administrador propietario puede establecer una fecha/hora pasada, dentro de una ventana configurable con valor inicial de 24 horas.
- **BR-032**: Un cierre puede confirmarse con diferencias de efectivo o digital distintas de cero; el sistema DEBE mostrar una advertencia visible y exigir un motivo del administrador antes de confirmar.
- **BR-033**: Cuando el JWT expira durante el envío de un formulario, el sistema DEBE redirigir al login con mensaje de sesión expirada y preservar los datos válidos del formulario en sesión para restaurarlos tras la re-autenticación.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Migración segura al dominio Agente (Priority: P1)

El propietario conserva operaciones, usuarios y trazabilidad mientras tiendas y agentes bancarios anteriores se consolidan en agentes únicos y se retiran dependencias obligatorias con bancos.

**Why this priority**: Todos los flujos nuevos dependen de un dominio migrado, íntegro y reversible.

**Independent Test**: Ejecutar la migración sobre una copia representativa, comparar conteos y totales antes/después, revisar el reporte de transformación y ejecutar rollback.

**Acceptance Scenarios**:

1. **Given** datos históricos con tienda, banco y agente bancario, **When** se transforma el modelo, **Then** cada operación conserva autor, monto, fecha, estado, auditoría y un agente resultante documentado.
2. **Given** una relación ambigua que no puede consolidarse automáticamente, **When** se valida la migración, **Then** el registro se reporta como excepción y no se elimina ni transforma silenciosamente.
3. **Given** una migración aplicada, **When** se ejecuta rollback, **Then** se recupera el estado anterior verificable sin pérdida.

---

### User Story 2 - Administrar agentes y asignaciones (Priority: P1)

El administrador crea agentes, administra sus datos y asigna operadores con vigencia e historial.

**Why this priority**: Sin agentes y asignaciones no existe contexto autorizado para operar.

**Independent Test**: Crear un agente, crear un operador, iniciar/finalizar una asignación y verificar historial y restricciones.

**Acceptance Scenarios**:

1. **Given** administrador autenticado, **When** crea un agente válido, **Then** el agente queda activo, auditado y disponible para asignación.
2. **Given** un operador con una asignación activa, **When** inicia sesión, **Then** el agente se selecciona automáticamente y permanece visible.
3. **Given** varias asignaciones activas, **When** el operador inicia su trabajo, **Then** solo puede elegir entre agentes asignados.
4. **Given** un agente inactivo o no asignado, **When** se intenta usar como contexto, **Then** el servidor rechaza la operación.
5. **Given** agente u operador existente, **When** el administrador edita, desactiva o reactiva el registro, **Then** el cambio queda auditado y afecta nuevas operaciones sin alterar históricos.
6. **Given** asignación activa, **When** el administrador la finaliza, **Then** conserva inicio, fin, asignador e historial y deja de autorizar nuevas operaciones.

---

### User Story 3 - Registrar operación general (Priority: P1)

El operador registra rápidamente una operación en su agente activo con monto prioritario, tipo, cliente opcional, fecha/hora y observación.

**Why this priority**: Es la tarea principal y el reemplazo directo del cuaderno manual.

**Independent Test**: Registrar una operación válida y verificar código, autor, agente, efectos, confirmación, auditoría e idempotencia.

**Acceptance Scenarios**:

1. **Given** operador con agente activo y tipo configurado, **When** registra un monto positivo, **Then** se crea una sola operación con código único, snapshot de efectos y autor/agente derivados del servidor.
2. **Given** doble envío con la misma identidad de solicitud, **When** llega el segundo envío, **Then** se devuelve la operación existente sin duplicar efectos.
3. **Given** monto cero/negativo, tipo inactivo o agente no autorizado, **When** se envía el formulario, **Then** se rechaza con mensaje descriptivo y sin persistencia.
4. **Given** cliente opcional, **When** se registra, **Then** solo se conserva el texto breve permitido y ningún dato bancario sensible.

---

### User Story 4 - Consultar hoja e historial personal (Priority: P2)

El operador consulta su propia hoja digital por fecha de negocio, con actividad observada, totales de efectivo/digital y lista cronológica.

**Why this priority**: Reproduce la separación por trabajador del cuaderno físico y permite autocontrol.

**Independent Test**: Crear operaciones de dos operadores y verificar que cada uno solo visualiza las propias y sus totales.

**Acceptance Scenarios**:

1. **Given** operaciones de varios trabajadores, **When** un operador abre su historial, **Then** solo ve sus registros y agregados.
2. **Given** administrador, **When** filtra por operador/agente/fecha/hora/código/cliente, **Then** ve resultados paginados y totales activos coherentes.
3. **Given** actividad observada, **When** se muestran primera y última hora, **Then** se identifica si provienen de sesión o de operaciones y no se presentan como asistencia laboral.

---

### User Story 5 - Configurar tipos y efectos monetarios (Priority: P2)

El administrador mantiene tipos generales ordenados con efectos de efectivo y saldo digital.

**Why this priority**: Los efectos definen saldos esperados y cierre diario.

**Independent Test**: Configurar tipos con combinaciones +1, -1 y 0, registrar operaciones y verificar deltas/snapshots exactos.

**Acceptance Scenarios**:

1. **Given** un tipo Transferencia con efectivo +1 y digital -1, **When** se registra S/ 100.00, **Then** la operación conserva +100.00 efectivo y -100.00 digital.
2. **Given** una operación histórica, **When** cambia el tipo, **Then** sus efectos almacenados no cambian.
3. **Given** tipo sin configuración completa, **When** se intenta confirmar un cierre que lo incluye, **Then** se identifica la operación y se bloquea la confirmación.

---

### User Story 6 - Apertura y cierre operativo (Priority: P2)

El personal autorizado registra saldos iniciales y reales; el administrador confirma un cierre con cálculos y diferencias por agente, tipo y operador.

**Why this priority**: Resuelve el control diario de efectivo y saldo digital solicitado por el propietario.

**Independent Test**: Abrir un día, registrar operaciones de dos operadores, ingresar saldos reales y confirmar que fórmulas, diferencias y desgloses son correctos.

**Acceptance Scenarios**:

1. **Given** agente sin apertura del día, **When** el administrador registra saldos iniciales, **Then** se crea una apertura BORRADOR que no puede duplicarse.
2. **Given** apertura BORRADOR válida, **When** el administrador la confirma, **Then** queda inmutable para edición silenciosa y habilita el día ABIERTO.
3. **Given** apertura confirmada, **When** se agregan operaciones activas, **Then** el cierre calcula efectivo y digital esperados con los snapshots.
4. **Given** operador asignado y saldos reales, **When** prepara y presenta el cierre, **Then** transita BORRADOR → PRESENTADO y muestra diferencias/totales por trabajador/tipo.
5. **Given** cierre PRESENTADO sin inconsistencias, **When** el administrador confirma, **Then** transita a CONFIRMADO y bloquea nuevas operaciones ordinarias para la fecha.
6. **Given** cierre CONFIRMADO, **When** el administrador reabre con motivo, **Then** transita a REABIERTO y audita estado anterior/nuevo, usuario, fecha y razón.

---

### User Story 7 - Dashboards de actividad (Priority: P3)

Operador y administrador consultan métricas de operaciones, efectivo, saldo digital, actividad horaria, cierres y rankings dentro de sus límites.

**Why this priority**: Convierte los registros en control operacional útil sin crear contabilidad.

**Independent Test**: Registrar actividad distribuida por operadores/agentes/horas y validar cada dashboard contra agregados independientes.

**Acceptance Scenarios**:

1. **Given** operador, **When** abre su dashboard, **Then** solo ve su actividad, agente activo y acción Registrar operación.
2. **Given** administrador, **When** filtra por agente, operador, periodo, tipo, estado u horario, **Then** todos los indicadores responden al mismo universo de datos.
3. **Given** periodo sin datos, **When** se consulta, **Then** aparece el estado vacío aprobado sin gráficos engañosos.
4. **Given** operaciones anuladas, **When** se calculan métricas activas, **Then** se excluyen y se informan separadamente cuando corresponda.

---

### User Story 8 - Anular con trazabilidad (Priority: P3)

El administrador anula una operación sin eliminarla y los cálculos dejan de incluirla.

**Why this priority**: Protege integridad y permite corregir errores operativos.

**Independent Test**: Anular una operación incluida en agregados/cierre borrador y comprobar auditoría y reversión lógica.

**Acceptance Scenarios**:

1. **Given** operación activa, **When** administrador la anula con motivo, **Then** permanece visible y queda fuera de totales/cierre activo.
2. **Given** operación ya anulada, **When** se intenta anular otra vez, **Then** se rechaza sin duplicar auditoría.

---

### User Story 9 - Sesión segura visible (Priority: P1)

Todo usuario autenticado ve el tiempo real restante y puede continuar o cerrar desde el modal Stitch.

**Why this priority**: La sesión de cinco minutos afecta cada flujo productivo y debe evitar cierres inesperados.

**Independent Test**: Avanzar una sesión hasta 30 segundos, renovar, expirar, cerrar y reutilizar un refresh token para validar todos los estados.

**Acceptance Scenarios**:

1. **Given** sesión activa, **When** cambia la visibilidad de pestaña, **Then** el contador se recalcula desde el vencimiento del servidor.
2. **Given** 30 segundos restantes, **When** continúa, **Then** se rota el refresh, se recibe nuevo vencimiento y se cierra el modal.
3. **Given** expiración o revocación, **When** se solicita un recurso, **Then** se rechaza, limpia estado y redirige a login sin renovación silenciosa.

---

### User Story 10 - Experiencia Stitch pixel-perfect (Priority: P2)

Los flujos productivos reproducen las maquetas aprobadas con datos reales y terminología Agente.

**Why this priority**: La fidelidad visual es un criterio contractual explícito de esta corrección.

**Independent Test**: Capturar cada pantalla obligatoria en cuatro viewports y comparar composición, tipografía, color, espacio y acciones con su referencia.

**Acceptance Scenarios**:

1. **Given** referencia y pantalla productiva, **When** se comparan en 1440x900, 1280x720, 768x1024 y 375x812, **Then** no existe desplazamiento inexplicado de componentes mayor a 2 px, la diferencia visual global no supera 0.5% de píxeles y toda diferencia restante corresponde a datos reales, dominio, accesibilidad o responsive documentado.
2. **Given** pantalla productiva, **When** se inspecciona, **Then** no contiene datos dummy ni referencias a banco, tienda o terminal bancaria.
3. **Given** navegación por teclado/móvil, **When** se recorren formularios, tablas y modal, **Then** no hay pérdida de acciones, scroll horizontal global ni dependencia exclusiva del color.

### Edge Cases

- Operador sin asignación activa: no puede registrar y recibe estado vacío orientativo.
- Operador con varias asignaciones: debe elegir contexto antes de operar y no puede falsificarlo.
- Agente desactivado durante una sesión: nuevas escrituras se rechazan; históricos permanecen visibles según permisos.
- Apertura o cierre duplicado: se conserva una sola instancia activa por agente/fecha.
- Cierre sin apertura: no se confirma y explica los datos faltantes.
- Operación durante expiración o pérdida de conexión: idempotencia evita duplicados y no informa éxito sin persistencia; los datos válidos del formulario se preservan en sesión para restaurarlos tras re-autenticarse.
- Operación posterior a cierre confirmado: se bloquea hasta que el administrador reabra.
- Anulación después de preparar cierre: el borrador se recalcula y queda marcado como modificado.
- Día sin operaciones o saldo inicial cero: los estados y fórmulas siguen siendo válidos.
- Actividad simultánea en pestañas/agentes: cada solicitud revalida sesión y agente activo.
- Refresh token reutilizado: revocación y evento de seguridad sin exponer secretos.
- Datos anteriores ambiguos o huérfanos: se reportan y bloquean del descarte automático.
- Operación retroactiva por operador: se rechaza y registra auditoría; el administrador puede hacerlo dentro de la ventana configurable.
- Cierre con diferencias: se permite confirmar con advertencia y motivo obligatorio; las diferencias quedan auditadas.

### Mandatory Demonstration Flow

1. El administrador crea un agente.
2. El administrador crea un operador.
3. El administrador asigna el operador al agente.
4. El administrador registra efectivo inicial.
5. El administrador registra saldo digital inicial.
6. El operador inicia sesión.
7. El sistema selecciona el agente autorizado.
8. El operador registra varias operaciones.
9. Cada operación recibe código automático.
10. El operador visualiza únicamente sus operaciones.
11. Otro operador registra sus propias operaciones.
12. El administrador observa ambos operadores.
13. El administrador consulta actividad por hora.
14. El operador prepara el cierre BORRADOR.
15. Se ingresan saldos reales y se PRESENTA el cierre.
16. El sistema calcula saldos esperados.
17. El sistema calcula diferencias.
18. El administrador confirma el cierre.
19. El administrador consulta el resumen por operador.
20. El temporizador muestra el modal treinta segundos antes de expirar.
21. Continuar renueva la sesión.
22. Cerrar finaliza la sesión.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE utilizar Agente como único punto físico funcional.
- **FR-002**: El sistema DEBE retirar formularios, filtros, rutas y vistas productivas de bancos y tiendas al completar la migración.
- **FR-003**: El sistema DEBE permitir crear, editar, activar y desactivar agentes con código, nombre y ciudad obligatorios, y región, provincia, distrito, dirección y descripción opcionales.
- **FR-004**: El sistema DEBE mantener asignaciones de operadores a agentes con inicio, fin, estado, asignador e historial.
- **FR-005**: El sistema DEBE seleccionar automáticamente un único agente asignado y solicitar selección autorizada cuando existan varios.
- **FR-006**: El agente activo DEBE permanecer visible en toda vista operacional autenticada.
- **FR-007**: El servidor DEBE derivar y verificar operador/agente al registrar, sin confiar en identidad manipulable del navegador.
- **FR-008**: Cada operación DEBE tener código único automático, tipo general, cliente opcional, monto decimal, moneda, momento efectivo, observación, efectos de efectivo/digital, estado e idempotencia.
- **FR-009**: El registro DEBE impedir doble envío en interfaz y servidor.
- **FR-009a**: El operador DEBE usar la fecha/hora actual del servidor; solo el administrador autorizado puede registrar con fecha pasada dentro de la ventana configurable (BR-031).
- **FR-010**: Los tipos DEBEN definir nombre, descripción, orden, estado y multiplicadores independientes de efectivo/digital.
- **FR-011**: La operación DEBE conservar snapshots decimales de ambos efectos.
- **FR-012**: El historial DEBE filtrar por fecha/rango, agente, operador administrativo, tipo, estado, código, cliente y horario, sin filtros bancarios.
- **FR-013**: El historial DEBE paginar en servidor, mantener filtros y mostrar estados vacío/error reales.
- **FR-014**: El operador DEBE ver exclusivamente operaciones propias; el administrador DEBE ver toda su organización.
- **FR-015**: La hoja personal DEBE agrupar por fecha de negocio y mostrar primera/última actividad con fuente explícita.
- **FR-016**: El administrador DEBE anular con motivo, sin eliminación física y con actualización de agregados activos.
- **FR-017**: El sistema DEBE registrar apertura diaria con efectivo y saldo digital iniciales.
- **FR-018**: El sistema DEBE impedir más de una apertura activa por agente/fecha.
- **FR-019**: El cierre DEBE calcular monto bruto, entradas/salidas, saldos esperados/reales y diferencias de efectivo/digital.
- **FR-020**: El cierre DEBE desglosar por operador/tipo y mostrar anuladas, pendientes e inconsistentes.
- **FR-021**: El cierre DEBE aplicar las transiciones y permisos de BR-028/BR-029; cualquier transición no enumerada debe rechazarse y auditarse cuando corresponda.
- **FR-022**: El sistema DEBE bloquear confirmación solo cuando falten efectos monetarios confiables; las diferencias de efectivo o digital no bloquean la confirmación pero exigen advertencia y motivo (BR-032).
- **FR-023**: Los dashboards DEBEN mostrar métricas y filtros definidos para cada rol, sin banco/tienda.
- **FR-024**: El análisis horario DEBE derivarse de operaciones, eventos de sesión y asignaciones sin afirmar asistencia continua.
- **FR-025**: Login, logout, renovación, expiración, revocación y reutilización inválida DEBEN conservar seguridad y eventos actuales.
- **FR-026**: Todas las vistas autenticadas DEBEN mostrar un contador basado en vencimiento del servidor.
- **FR-027**: A 30 segundos DEBE aparecer el modal Stitch con continuar/cerrar; continuar renueva/rota y cerrar revoca.
- **FR-027a**: Al expirar el JWT durante un envío, el sistema DEBE redirigir al login, mostrar el mensaje de sesión expirada y preservar los datos válidos del formulario en sesión flash para restaurarlos tras la re-autenticación (BR-033).
- **FR-028**: Login, Registro de operación, Historial, Dashboard del operador, Dashboard administrativo, Cierre operativo diario y Advertencia de expiración DEBEN seguir sus referencias Stitch con datos reales.
- **FR-029**: Cada una de las siete pantallas DEBE contar con captura y comparación en 1440x900, 1280x720, 768x1024 y 375x812, aplicando el umbral de la Historia 10.
- **FR-030**: El sistema DEBE eliminar referencias productivas a banco, tienda, agente bancario y terminal bancaria.
- **FR-031**: La migración DEBE inventariar y clasificar todo artefacto previo de datos, dominio, interfaz, seguridad, pruebas y documentación.
- **FR-032**: La migración DEBE producir respaldo, mapeo, reporte de transformación, comprobación de integridad y rollback probado.
- **FR-033**: Las operaciones históricas DEBEN conservar autor, monto, fecha, estado, código/equivalencia y auditoría.
- **FR-034**: Rutas demo y rutas productivas duplicadas DEBEN quedar inaccesibles al finalizar.
- **FR-035**: El Mandatory Demonstration Flow de 22 pasos DEBE completarse con datos reales de prueba.

### Key Entities *(include if feature involves data)*

- **Agente**: Punto físico único con código, nombre, ciudad, ubicación/dirección opcional, descripción, estado y trazabilidad temporal.
- **Usuario**: Administrador propietario u operador autenticado; no depende obligatoriamente de banco o tienda.
- **Asignación de operador**: Relación temporal e histórica entre usuario y agente, con asignador y vigencia.
- **Tipo general de operación**: Clasificación ordenada con efectos independientes sobre efectivo y saldo digital.
- **Operación**: Registro inmutable en identidad/atribución, con código, agente, operador, tipo, cliente opcional, monto, tiempo, notas, snapshots monetarios, estado e idempotencia.
- **Apertura diaria**: Saldos iniciales de efectivo/digital de un agente para una fecha de negocio.
- **Cierre diario**: Consolidación por agente/fecha con saldos iniciales, esperados, reales, diferencias, estados y responsables.
- **Inclusión de operación en cierre**: Evidencia de qué operaciones y efectos monetarios formaron un cierre, suficiente para reconstruir sus cálculos históricos.
- **Sesión y evento de sesión**: Estado de autenticación y eventos de seguridad.
- **Auditoría**: Registro de actor, momento, acción, entidad, valores anteriores/posteriores y motivo.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: El servidor aplica roles, propiedad de operación y agente asignado en cada lectura/escritura. Un operador no puede acceder a operaciones ajenas ni agentes no asignados por URL, query, formulario, payload o script. El administrador accede dentro de su ámbito organizacional. La interfaz nunca es control de seguridad.
- **Data minimization**: Solo se admite nombre/referencia breve opcional del cliente, visible conforme a BR-026 y sujeto a la excepción/enmienda constitucional previa. Se prohíben DNI obligatorio, cuentas, tarjetas, credenciales, biometría y secretos de entidades. No se permite búsqueda global fuera del ámbito operacional autorizado, perfilado ni reutilización secundaria. Cualquier catálogo futuro requiere otra especificación y análisis de privacidad.
- **Auditability**: Operaciones no se eliminan; anulación, agentes, asignaciones, apertura/cierre y transiciones sensibles conservan actor, tiempo, antes/después y motivo. Login/renovación/logout/expiración/revocación mantienen eventos sin secretos.
- **Time and money**: Todos los importes y deltas usan precisión decimal; fechas/periodos se muestran y agrupan en `America/Lima` con límites de BR-020/BR-021. Monto bruto, efectivo, saldo digital y diferencias son conceptos distintos; ninguno equivale a ganancia.
- **Session security**: JWT inicial de cinco minutos, advertencia a 30 segundos, renovación explícita, refresh rotatorio/revocable, hashes almacenados, logout revocatorio, limpieza/redirección en token inválido y sin renovación silenciosa.
- **Migration integrity**: Ningún dato/columna/tabla anterior se elimina antes de respaldo, perfilado, mapeo, validación, reporte y rollback. Ambigüedades se resuelven explícitamente o bloquean el descarte.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Debe conservarse hosting PHP convencional, renderizado en servidor, HTML semántico, estilos propios y JavaScript modular, sin SPA ni dependencia runtime pesada. Debe funcionar en 1440, 1280, 768 y 375 px, sin scroll horizontal global.
- **Performance**: Listas paginadas con máximo inicial de 25 por página; agregaciones de dashboard/cierre en servidor. Los accesos frecuentes por agente-tiempo, operador-tiempo, tipo-tiempo, estado-tiempo, agente-fecha de negocio, código interno y asignación activa DEBEN estar respaldados por índices de búsqueda y mantener SC-008 con el volumen de referencia. No se permiten N+1 ni colecciones completas al navegador.
- **Observability and recovery**: Salud del servicio disponible, errores sin secretos, debug desactivado en producción, respaldo previo, recuperación documentada y cambios reversibles cuando sea viable. Toda excepción requiere justificación/medida compensatoria.
- **System boundary**: Es un registro interno de control operacional; no confirma procesamiento externo, no integra bancos, no realiza conciliación ni constituye contabilidad o fuente bancaria oficial.
- **Visual quality**: Comparación pixel-perfect documentada por pantalla/viewport; accesibilidad, responsive y adaptación al dominio son las únicas desviaciones permitidas, además de correcciones técnicas justificadas.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% de los puntos físicos productivos se representan como Agente y 0 flujos productivos requieren banco, tienda o terminal bancaria.
- **SC-002**: 100% de las operaciones migradas conservan autor, agente resultante, monto, fecha, estado y trazabilidad, con 0 pérdidas silenciosas.
- **SC-003**: Un operador con agente activo completa un registro válido en menos de 60 segundos y el doble envío produce exactamente una operación.
- **SC-004**: 100% de operaciones nuevas reciben código único y snapshots de efectivo/digital coherentes con su tipo.
- **SC-005**: Los cálculos de apertura/cierre coinciden al centavo con casos de referencia para entradas, salidas, anulaciones y diferencias.
- **SC-006**: En pruebas negativas, 0 accesos de operador a operaciones ajenas o agentes no asignados resultan autorizados.
- **SC-007**: Historiales mantienen filtros entre páginas y muestran como máximo 25 registros por página.
- **SC-008**: Dashboards y cierres muestran resultados en menos de 3 segundos con 100 agentes, 500 operadores y 100,000 operaciones distribuidas en un año.
- **SC-009**: El temporizador aparece en 100% de vistas autenticadas, muestra el modal a 30 segundos y todos los escenarios de renovación/revocación pasan.
- **SC-010**: Las siete pantallas obligatorias tienen 4 comparaciones visuales aprobadas (28 capturas), diferencia global máxima de 0.5% y ningún desplazamiento inexplicado mayor a 2 px.
- **SC-011**: 100% de textos productivos usan terminología Agente/Efectivo/Saldo digital y 0 referencias funcionales prohibidas permanecen.
- **SC-012**: El flujo de demostración completo de 22 pasos se ejecuta sin errores críticos ni datos dummy.
- **SC-013**: El 100% de escenarios de aceptación obligatorios finaliza correctamente y se registran 0 errores críticos en navegador durante el flujo demostrable.
- **SC-014**: La migración dispone de respaldo restaurable y rollback completo probado en menos de 60 minutos para el volumen de referencia de SC-008.

## Assumptions

- La organización/cliente contratante existente se conserva; esta corrección no convierte el producto en SaaS multiempresa.
- PEN es la única moneda operativa inicial, aunque el registro conserva el identificador de moneda.
- El nombre opcional del cliente tiene finalidad operativa interna y se limita a texto breve sin identificadores sensibles.
- Un operador puede tener múltiples asignaciones activas y debe elegir contexto; no se implementan turnos.
- La aprobación funcional final exige enmienda constitucional o excepción formal antes de planificación.
- Las referencias Stitch existentes siguen vigentes, pero se adaptan para retirar elementos bancarios y usar datos reales.
- Los umbrales de rendimiento y comparación visual definidos en Success Criteria son criterios mínimos; el plan puede endurecerlos, no relajarlos.
- Si el entorno actual contiene solo datos dummy, su eliminación igualmente requiere un procedimiento y evidencia documentados.

## Dependencies

- Enmienda de `.specify/memory/constitution.md` o excepción formal conforme al Principio XIII.
- Especificación 001 de autenticación/sesión: se conserva, salvo terminología incompatible de agente.
- Especificaciones 002, 003, 004 y 005: se conservan como historial; requisitos de banco/tienda/agente bancario quedan superados por esta especificación tras su aprobación.
- Especificación 006 y artefactos `docs/design/stitch/v1/`: continúan como fuente visual obligatoria.
- Especificación 007: se conserva como historial; su integración visual se reutiliza, pero filtros/datos bancarios y estándar de equivalencia no pixel-perfect quedan superados.
- Disponibilidad de respaldo, inventario de datos existentes, ambiente de ensayo de migración y datos reales de prueba no sensibles.

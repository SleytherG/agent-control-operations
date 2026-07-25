> **Superseded by 008**: Esta especificacion fue superada por [`008-simplify-agent-operations`](../008-simplify-agent-operations/spec.md).

# Feature Specification: Integración Visual Stitch al Sistema Funcional

**Feature Branch**: `007-stitch-visual-integration`

**Created**: 2026-07-22

**Status**: Draft

**Input**: User description: "Integrar y aplicar al sistema funcional real toda la interfaz visual, componentes, layouts y patrones de experiencia de usuario desarrollados en la especificación de maquetación y diseño basada en Google Stitch."

## Problem & Actors *(mandatory)*

**Problem**: El sistema cuenta con dos conjuntos de interfaces separadas: las vistas funcionales (especificaciones 001-005) que utilizan HTML básico sin diseño consistente, y las vistas de maquetación (especificación 006) que implementan el sistema visual Stitch con datos ficticios. Esta duplicación genera una experiencia de usuario fragmentada, dificulta el mantenimiento y expone riesgos al mantener rutas demo accesibles. Los operadores y administradores no pueden beneficiarse del diseño aprobado en sus flujos reales de trabajo.

**Actors**:

- **Operador**: Registra operaciones en agentes bancarios asignados, consulta su historial, visualiza su dashboard con métricas propias. Solo accede a sus datos. Su interfaz actual es HTML básico sin los componentes visuales aprobados.
- **Administrador Propietario**: Gestiona la estructura organizacional (regiones, tiendas, bancos, agentes), administra usuarios, visualiza dashboards consolidados con filtros multidimensionales, confirma y reabre cierres diarios. Requiere una interfaz que refleje sus permisos amplios con el diseño aprobado.
- **Equipo de mantenimiento**: Necesita un sistema sin duplicidad de vistas, rutas demo aisladas y código obsoleto eliminado. La interfaz debe ser mantenible, con componentes reutilizables.

**Change Classification**: New functional capability (integración de interfaz visual sobre funcionalidad existente; no modifica reglas de negocio, autorizaciones ni persistencia)

## Clarifications

### Session 2026-07-22

- Q: ¿Qué grado de fidelidad visual se requiere respecto a los screen.png de Stitch? → A: Equivalencia funcional usando los mismos componentes Stitch, CSS tokens y jerarquía visual de la spec 006. Variaciones menores de espaciado o alineación son aceptables siempre que no degraden usabilidad ni accesibilidad. No se requiere pixel-perfect.

## Scope *(mandatory)*

### In Scope

- Migrar la vista de login funcional al diseño Stitch aprobado, conservando validación real, rate limiting y mensajes de error del servidor
- Conectar el layout autenticado a datos reales: identidad del usuario, rol, tienda, banco, agente activo y tiempo real de sesión
- Migrar el dashboard del operador a componentes Stitch con datos reales del usuario autenticado (métricas, gráficos, operaciones recientes)
- Migrar el formulario de registro de operaciones al componente Stitch, conectado a agentes asignados reales, catálogo real de bancos y tipos, con validación, idempotencia y persistencia real
- Migrar el historial de operaciones a tabla y filtros Stitch, con paginación y métricas de resumen reales
- Migrar el dashboard administrativo a componentes Stitch con filtros multidimensionales reales, métricas y gráficos con datos de toda la organización
- Migrar pantallas de administración (tiendas, bancos, agentes, usuarios, tipos de operación, sesiones) al sistema visual común
- Migrar el cierre diario a la pantalla Stitch con KPIs, desgloses, warning de pendientes y acciones de confirmar/reabrir reales
- Implementar estados reales de interfaz (carga, error, vacío, éxito, deshabilitado) derivados de respuestas del servidor
- Reutilizar componentes visuales Stitch (metric-card, data-table, badge, modal, chart-container, filter-bar, pagination, toast, empty-state, error-state, button, input, select) en todas las vistas productivas
- Eliminar rutas demo del flujo productivo; conservar solo assets reutilizados
- Clasificar cada artefacto demo como reutilizado, migrado, reemplazado, eliminado o conservado para pruebas/documentación
- Documentar matriz de migración y desviaciones respecto al diseño Stitch

### Out of Scope

- Nuevas reglas de negocio, roles, permisos o flujos funcionales
- Comisiones, ganancias, conciliación bancaria o integración con bancos
- Registro de clientes, datos personales bancarios o información de tarjetas
- Aplicación móvil nativa, modo offline, SPA (React, Angular, Vue)
- Reescritura del backend, cambio de base de datos, modificación del sistema de autenticación
- Rediseño del sistema visual diferente de lo aprobado en 006
- Nuevos componentes visuales no contemplados en la especificación 006
- Migración a otro framework CSS o sistema de diseño

### Business Rules

- **BR-001**: El servidor DEBE verificar toda autorización; la interfaz nunca constituye una barrera de seguridad (Principio V)
- **BR-002**: Las rutas demo no DEBEN ser accesibles en el flujo productivo después de la migración
- **BR-003**: Los datos mock no DEBEN permanecer en vistas productivas; solo en factories, seeders o documentación
- **BR-004**: Las pantallas deben ser responsive en 1440px, 1280px, 768px y 375px sin desplazamiento horizontal global
- **BR-005**: La interfaz DEBE cumplir los criterios de accesibilidad del Principio IV y buscar conformidad con WCAG 2.2 AA
- **BR-006**: Chart.js solo DEBE cargarse en pantallas que contengan gráficos (dashboard, no en login ni formularios)
- **BR-007**: Las consultas de datos para dashboards DEBEN ejecutarse en el servidor; no descargar colecciones completas al navegador (Principio XI)
- **BR-008**: El monto bruto operado NO DEBE presentarse como ganancia, utilidad o conciliación bancaria (Principio VIII)
- **BR-009**: Si el diseño visual contradice una regla funcional, de autorización, validación de seguridad o restricción de datos, prevalece la especificación funcional; la contradicción debe documentarse
- **BR-010**: El estándar de fidelidad visual es equivalencia funcional: mismos componentes Stitch, mismos CSS tokens y misma jerarquía visual que la spec 006. Variaciones menores de espaciado o alineación son aceptables. No se requiere reproducción pixel-perfect de los screen.png

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Login visual integrado al flujo real (Priority: P1)

El operador o administrador accede a la pantalla de inicio de sesión con el diseño Stitch aprobado. Ingresa credenciales reales. El sistema valida contra la base de datos, aplica rate limiting y responde con mensajes reales de error (credenciales incorrectas, usuario desactivado, throttled). En caso de éxito, redirige al home con sesión JWT establecida. La pantalla muestra estados de carga durante el envío y preserva el diseño visual completo.

**Why this priority**: Es el punto de entrada al sistema. Sin login funcional con diseño integrado, ningún otro flujo es accesible.

**Independent Test**: Acceder a GET /login, verificar que la vista usa el layout guest Stitch con componentes x-ui. Enviar POST con credenciales válidas y verificar redirección a /home con cookies de sesión. Enviar POST con credenciales inválidas y verificar mensaje de error en la misma vista Stitch.

**Acceptance Scenarios**:

1. **Given** usuario no autenticado, **When** accede a /login, **Then** ve la pantalla Stitch con logo "AF", campos de usuario y contraseña, y botón "Iniciar sesión"
2. **Given** credenciales correctas, **When** envía el formulario, **Then** el botón muestra estado de carga, y al recibir respuesta 200 es redirigido a /home con sesión activa
3. **Given** credenciales incorrectas, **When** envía el formulario, **Then** ve mensaje "Credenciales incorrectas" en el componente de error Stitch, preservando los campos del formulario
4. **Given** usuario desactivado, **When** intenta autenticarse, **Then** ve mensaje "Usuario desactivado. Contacte soporte."
5. **Given** 5 intentos fallidos en 60 segundos, **When** intenta nuevamente, **Then** ve mensaje de throttling y el botón aparece deshabilitado

---

### User Story 2 - Layout autenticado con datos reales (Priority: P1)

El usuario autenticado navega por la aplicación con un layout consistente que muestra: sidebar con opciones según su rol, topbar con su nombre y rol real, tienda activa, indicador de tiempo de sesión calculado desde el servidor, y acción de logout real. El contenido principal se renderiza en el área central mediante @yield('content').

**Why this priority**: El layout es el contenedor de todas las pantallas autenticadas. Debe funcionar correctamente con datos reales antes de migrar cualquier vista interna.

**Independent Test**: Autenticarse, verificar que el sidebar muestra solo las opciones correspondientes al rol del usuario, que el topbar muestra el nombre y rol correctos, que el indicador de sesión muestra el tiempo restante real, y que el botón de logout cierra la sesión.

**Acceptance Scenarios**:

1. **Given** operador autenticado, **When** navega a cualquier página, **Then** el sidebar muestra Dashboard, Registrar Operación, Historial (no muestra opciones de administrador)
2. **Given** administrador autenticado, **When** navega a cualquier página, **Then** el sidebar muestra todas las opciones incluyendo administración
3. **Given** sesión con 4 minutos restantes, **When** observa el indicador, **Then** muestra "04:00" con conteo regresivo real
4. **Given** sesión con 30 segundos restantes, **When** el contador llega a 0:30, **Then** se muestra modal de expiración con opciones "Continuar sesión" y "Cerrar sesión"
5. **Given** usuario hace clic en "Cerrar sesión", **When** se ejecuta POST /logout, **Then** las cookies se limpian y es redirigido a /login

---

### User Story 3 - Dashboard real del operador (Priority: P2)

El operador visualiza su dashboard con el diseño Stitch mostrando datos reales: tarjetas métricas (cantidad de operaciones, monto bruto, entradas, salidas, movimiento neto), gráfico de distribución por tipo, gráfico de evolución temporal, y tabla de operaciones recientes. Los datos corresponden exclusivamente a las operaciones del usuario autenticado en el período seleccionado, en zona horaria America/Lima, excluyendo operaciones anuladas por defecto.

**Why this priority**: Es la pantalla principal después del login. Proporciona visibilidad inmediata de la actividad del operador.

**Independent Test**: Autenticarse como operador con operaciones registradas, acceder a /operator/dashboard, verificar que las 5 métricas muestran valores calculados desde la base de datos, que los gráficos renderizan con datos reales, y que la tabla de operaciones recientes coincide con las últimas operaciones del usuario.

**Acceptance Scenarios**:

1. **Given** operador con 25 operaciones en el día, **When** accede a su dashboard, **Then** ve 5 tarjetas métricas con valores reales, gráfico de doughnut por tipo y gráfico de evolución por hora
2. **Given** operador sin operaciones en el período, **When** accede a su dashboard, **Then** ve el estado vacío aprobado (no tarjetas con ceros, no gráficos vacíos)
3. **Given** operador con operaciones anuladas, **When** accede a su dashboard, **Then** las operaciones anuladas NO se incluyen en métricas ni gráficos
4. **Given** operador autenticado, **When** cambia el período a "semana", **Then** las métricas y gráficos se actualizan con datos del período seleccionado

---

### User Story 4 - Registro real de operaciones con formulario Stitch (Priority: P2)

El operador registra una operación usando el formulario visual Stitch. El formulario se conecta a datos reales: agentes asignados al usuario, catálogo de bancos y tipos de operación. Al enviar, el sistema valida monto > 0, moneda PEN, previene doble envío mediante idempotency key, aplica reglas de ventana retroactiva, y persiste en base de datos con auditoría. Tras éxito, muestra confirmación con el ID real de la operación. Si falla, preserva los datos del formulario y muestra el error.

**Why this priority**: Es la acción principal del operador. Sin registro funcional, el sistema no cumple su propósito.

**Independent Test**: Autenticarse como operador con agente asignado, acceder a /operations/create, verificar que los selectores muestran solo agentes del operador, llenar el formulario con monto 100, enviar, verificar redirección a confirmación con ID real, verificar que la operación aparece en el historial.

**Acceptance Scenarios**:

1. **Given** operador con 2 agentes asignados, **When** abre el formulario de registro, **Then** el selector de agente muestra solo esos 2 agentes
2. **Given** formulario completo con monto 150.00 PEN, **When** envía, **Then** el botón muestra estado de carga, y al recibir 201 es redirigido a pantalla de confirmación con el ID real
3. **Given** formulario con monto 0 o negativo, **When** envía, **Then** ve error de validación "El monto debe ser mayor a 0" y el formulario preserva los datos ingresados
4. **Given** operación ya registrada (misma idempotency key), **When** reenvía el formulario, **Then** ve mensaje informando que la operación ya fue registrada
5. **Given** operador sin agentes asignados, **When** accede al formulario, **Then** ve mensaje indicando que no tiene agentes asignados y el formulario no se muestra

---

### User Story 5 - Historial real de operaciones con tabla Stitch (Priority: P3)

El operador consulta su historial de operaciones usando la tabla y filtros Stitch. Puede filtrar por fechas, tipo y estado, buscar por referencia, y paginar resultados. Las métricas de resumen (total operaciones, monto bruto, entradas, salidas, neto) se calculan sobre los resultados filtrados. El administrador puede ver operaciones de cualquier operador según sus permisos.

**Why this priority**: Permite al operador auditar su trabajo y verificar operaciones pasadas. Es una funcionalidad de consulta, no de registro.

**Independent Test**: Autenticarse como operador con varias operaciones, acceder a /operations, verificar que la tabla muestra solo sus operaciones, aplicar filtro por tipo, verificar que las métricas de resumen se actualizan, navegar entre páginas.

**Acceptance Scenarios**:

1. **Given** operador con 50 operaciones, **When** accede al historial, **Then** ve tabla paginada (25 por página) con columnas: fecha, banco, agente, tipo, monto, referencia, estado
2. **Given** operador en historial, **When** aplica filtro de tipo "Depósito", **Then** la tabla muestra solo depósitos y las métricas de resumen reflejan solo esos resultados
3. **Given** operador en historial, **When** busca por referencia "TRX-99120", **Then** la tabla muestra solo operaciones que coinciden con esa referencia
4. **Given** administrador en historial, **When** accede sin filtro de operador, **Then** ve operaciones de todos los operadores

---

### User Story 6 - Dashboard administrativo real (Priority: P3)

El administrador visualiza el dashboard Stitch con filtros multidimensionales reales (período, región, provincia, distrito, tienda, banco, agente, operador, tipo, estado). Las tarjetas métricas, gráficos de evolución, distribución por banco, comparación de flujo y ranking de tiendas/operadores muestran datos reales de toda la organización. Todos los componentes del dashboard responden coherentemente a los filtros aplicados.

**Why this priority**: Proporciona visibilidad gerencial. Depende de que existan datos registrados (historias 4 y 5) y de que la estructura organizacional esté poblada (historias administrativas).

**Independent Test**: Autenticarse como administrador, acceder a /admin/dashboard, verificar que las métricas muestran datos de toda la organización, aplicar filtro de tienda, verificar que todos los componentes se actualizan, verificar gráficos con datos reales.

**Acceptance Scenarios**:

1. **Given** administrador autenticado, **When** accede al dashboard, **Then** ve 4 tarjetas KPI con valores de toda la organización y métricas secundarias (total ops, trabajadores activos, tiendas activas, agentes activos, ops anuladas)
2. **Given** dashboard con datos, **When** aplica filtro de región "Lima", **Then** todos los KPIs, gráficos y tablas se actualizan para reflejar solo datos de Lima
3. **Given** dashboard con selector de período "mes", **When** cambia a "trimestre", **Then** los datos se recalculan para el nuevo período
4. **Given** sin datos para los filtros seleccionados, **When** accede al dashboard, **Then** muestra estado vacío aprobado

---

### User Story 7 - Administración con diseño unificado (Priority: P4)

El administrador gestiona tiendas, bancos, agentes bancarios, usuarios, asignaciones, tipos de operación y sesiones usando el sistema visual Stitch. Cada módulo (CRUD) utiliza componentes compartidos: data-table para listados, formularios con inputs/selects Stitch, modales de confirmación para acciones destructivas, badges para estados activo/inactivo, y paginación consistente.

**Why this priority**: Son pantallas de configuración y administración. Menos frecuentes que las operativas pero necesarias para la completitud del sistema.

**Independent Test**: Autenticarse como administrador, navegar a cada módulo de administración, verificar que usan componentes Stitch, crear/editar/desactivar registros, verificar mensajes de éxito/error con estilo Stitch.

**Acceptance Scenarios**:

1. **Given** administrador en listado de tiendas, **When** ve la tabla, **Then** usa x-ui.data-table con columnas, badges de estado y paginación
2. **Given** administrador en formulario de creación de banco, **When** completa y envía, **Then** ve mensaje de éxito con x-ui.toast y es redirigido al listado
3. **Given** administrador intenta desactivar una tienda con agentes activos, **When** confirma, **Then** ve mensaje de error explicando el bloqueo
4. **Given** administrador en listado de sesiones, **When** aplica filtros, **Then** la tabla se actualiza con resultados filtrados y paginados

---

### User Story 8 - Cierre diario con diseño Stitch (Priority: P4)

El usuario autorizado consulta el cierre diario con el diseño Stitch mostrando: fecha, tienda, banco, agente, KPIs (total ops, monto bruto, entradas, salidas, neto), desglose por tipo y por operador, warning de operaciones pendientes, y tabla de anuladas. Las acciones de confirmar y reabrir ejecutan los casos de uso reales con auditoría. El estado del cierre (activo, confirmado, reabierto) se refleja visualmente con indicadores de color.

**Why this priority**: El cierre diario es una operación de fin de jornada que consolida la actividad. Es naturalmente posterior al registro de operaciones.

**Independent Test**: Autenticarse, generar un cierre diario, acceder a /daily-closing/{id}, verificar KPIs con datos reales, confirmar el cierre, verificar que el estado cambia visualmente, reabrir con motivo, verificar auditoría.

**Acceptance Scenarios**:

1. **Given** cierre activo con 142 operaciones, **When** el usuario consulta el detalle, **Then** ve 5 tarjetas KPI, contexto (fecha, tienda, banco, agente), desglose por tipo y por operador
2. **Given** cierre con operaciones POR_CONFIRMAR, **When** consulta, **Then** ve warning "Operaciones por confirmar" con el conteo real
3. **Given** administrador en cierre activo, **When** hace clic en "Confirmar Cierre", **Then** el cierre cambia a estado CONFIRMADO y el indicador visual se actualiza
4. **Given** administrador en cierre confirmado, **When** reabre con motivo "Error en monto de operación #42", **Then** el cierre cambia a REABIERTO y se registra auditoría con el motivo

---

### User Story 9 - Estados reales de interfaz (Priority: P3)

Todas las pantallas reflejan estados reales derivados del servidor: carga durante peticiones, éxito tras operaciones completadas, error de validación con mensajes específicos, error de autorización (403), error de autenticación (401), estado vacío cuando no hay datos, y acciones deshabilitadas según permisos. Los estados no son simulaciones decorativas.

**Why this priority**: La calidad de la experiencia depende de que los estados reflejen fielmente la realidad del sistema. Es transversal a todas las demás historias.

**Independent Test**: Provocar cada estado (sesión expirada, sin datos, error de validación, sin permisos) y verificar que la interfaz responde con el componente Stitch adecuado.

**Acceptance Scenarios**:

1. **Given** petición en curso, **When** el usuario espera, **Then** ve indicador de carga (spinner en botón, skeleton o loading state)
2. **Given** sesión expirada, **When** el usuario intenta cualquier acción, **Then** es redirigido a /login con mensaje "Sesión expirada"
3. **Given** operador intenta acceder a ruta de administrador, **When** el servidor responde 403, **Then** ve componente de error con mensaje "No autorizado"
4. **Given** listado sin resultados, **When** el usuario aplica filtros que no devuelven datos, **Then** ve componente empty-state con mensaje contextual

---

### User Story 10 - Eliminación de duplicidad y limpieza (Priority: P5)

El equipo de mantenimiento elimina las rutas demo del flujo productivo. Los controladores demo, datos mock y vistas duplicadas son clasificados y tratados según su destino: reutilizado en producción, migrado, reemplazado, eliminado, o conservado solo para pruebas/documentación. Las rutas /demo/* quedan inaccesibles. El resultado es un sistema sin interfaces paralelas.

**Why this priority**: Es la tarea de cierre que garantiza la no regresión a la duplicidad. Debe ser la última en ejecutarse para no romper las pantallas demo antes de que sus equivalentes productivos estén migrados.

**Independent Test**: Verificar que las rutas /demo/* no son accesibles (404 o redirección), que los controladores demo están eliminados o marcados como @deprecated, que no existen datos mock en vistas productivas, ejecutar el test suite completo para verificar que nada se rompió.

**Acceptance Scenarios**:

1. **Given** migración completada, **When** se accede a /demo/login, **Then** retorna 404 o redirección a /login
2. **Given** migración completada, **When** se ejecuta el test suite, **Then** todas las pruebas existentes continúan pasando
3. **Given** migración completada, **When** se inspeccionan las vistas productivas, **Then** no contienen datos mock hardcodeados
4. **Given** migración completada, **When** se revisa el inventario de migración, **Then** cada artefacto demo tiene una clasificación documentada

---

### Edge Cases

- ¿Qué sucede cuando un operador no tiene agentes asignados? El dashboard debe mostrar estado vacío y el formulario de registro debe indicar que no hay agentes disponibles
- ¿Qué sucede cuando el servidor retorna error 500? La interfaz debe mostrar el componente error-state sin exponer detalles internos
- ¿Qué sucede con sesiones abiertas en múltiples pestañas? Cada pestaña mantiene su propio contador basado en la misma expiración del servidor
- ¿Qué sucede cuando un administrador desactiva a un operador con sesión activa? La siguiente petición del operador debe fallar con 401 y redirigir a login
- ¿Qué sucede si el diseño Stitch no contempla un campo requerido por una regla funcional? Prevalece la regla funcional; se documenta la desviación

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE reemplazar la vista de login actual (`identity-access.login`) por el diseño Stitch (`layouts.guest` + componentes `x-ui.input`, `x-ui.button`) conservando la ruta POST `/login` y toda la lógica de autenticación existente
- **FR-002**: El LoginController DEBE pasar a la vista las variables necesarias para que el diseño Stitch muestre estados de error (credentials, disabled, throttled, network) basados en la respuesta real del servidor
- **FR-003**: El layout autenticado DEBE recibir del controlador o middleware las variables `$user` (modelo User autenticado), `$role` (string del rol), `$sessionExpiresAt` (timestamp de expiración) para que sidebar, topbar e indicador de sesión funcionen con datos reales
- **FR-004**: El sidebar DEBE generar sus enlaces dinámicamente según el rol del usuario autenticado (OPERADOR ve Dashboard, Registrar, Historial; ADMINISTRADOR_PROPIETARIO ve además Administración)
- **FR-005**: El indicador de sesión DEBE calcular el tiempo restante a partir de `$sessionExpiresAt` proporcionado por el servidor, mostrar advertencia 30 segundos antes, y conectar el modal de expiración a las rutas reales `/auth/refresh` y `/logout`
- **FR-006**: El dashboard del operador DEBE reemplazar la vista `reporting.operator-dashboard` por el diseño Stitch (`screens.operator.dashboard`) usando `x-screen.operator-metrics`, `x-ui.chart-container`, `x-ui.data-table`, y recibiendo del DashboardController datos reales del usuario autenticado
- **FR-007**: El formulario de registro DEBE reemplazar la vista `operations.create` por el diseño Stitch usando el componente `x-screen.operation-form`, conectado a datos reales de agentes asignados (`$assignments`), bancos y tipos desde la base de datos
- **FR-008**: El historial de operaciones DEBE reemplazar la vista `operations.index` usando `x-ui.metric-card`, `x-screen.operation-filters`, `x-ui.data-table`, `x-ui.badge` y `x-ui.pagination`, con métricas de resumen calculadas en el servidor sobre los resultados filtrados
- **FR-009**: El dashboard administrativo DEBE reemplazar la vista `reporting.admin-dashboard` usando `x-ui.metric-card`, `x-screen.admin-filters`, `x-ui.chart-container` y `x-screen.operator-comparison`, con filtros multidimensionales conectados al DashboardQueryService real
- **FR-010**: Las pantallas de administración DEBEN usar componentes Stitch compartidos (`x-ui.data-table`, `x-ui.input`, `x-ui.select`, `x-ui.button`, `x-ui.badge`, `x-ui.modal`, `x-ui.pagination`) manteniendo la funcionalidad CRUD, validaciones y policies existentes
- **FR-011**: El cierre diario DEBE reemplazar la vista `daily-closing.show` usando `x-ui.metric-card`, `x-screen.closing-warning`, `x-ui.data-table` y `x-screen.closing-detail`, con datos reales del cierre y sus operaciones asociadas
- **FR-012**: Cada pantalla DEBE implementar estados reales usando componentes `x-ui.loading-state`, `x-ui.error-state`, `x-ui.empty-state`, y `x-ui.toast` según la respuesta del servidor
- **FR-013**: Las rutas demo (`/demo/*`) DEBEN ser eliminadas completamente del archivo `routes/demo.php`. No deben quedar accesibles bajo ningún prefijo en producción. El archivo `routes/demo.php` DEBE ser eliminado y su `require` en `routes/web.php` removido
- **FR-014**: Los controladores demo (`DemoAuthController`, `DemoOperatorController`, `DemoAdminController`, `DemoClosingController`) DEBEN ser eliminados completamente del repositorio
- **FR-015**: Los datos mock en `resources/demo/*.php` DEBEN ser eliminados. Las vistas demo en `resources/views/screens/` DEBEN ser eliminadas completamente. No se conserva código demo en el repositorio principal; la documentación visual de diseño reside en `docs/design/stitch/` (PNG, HTML estático exportado de Stitch)
- **FR-016**: El sistema DEBE documentar una matriz de migración que relacione cada pantalla demo con su equivalente productivo, componentes reutilizados, fuente de datos real y decisión sobre el artefacto demo

### Key Entities *(include if feature involves data)*

- **User**: Usuario autenticado con rol (ADMINISTRADOR_PROPIETARIO / OPERADOR), nombre, tienda y agente asignado. Ya existe en `App\Modules\IdentityAccess\Models\User`
- **AuthSession**: Sesión activa con JWT access token, refresh token y tiempo de expiración. Ya existe
- **Operation**: Operación registrada con monto, tipo, agente, estado y auditoría. Ya existe en `App\Modules\Operations\Models\Operation`
- **DailyClosure**: Cierre diario por agente y fecha con métricas consolidadas. Ya existe en `App\Modules\DailyClosing\Models\DailyClosure`
- **BankAgent**: Agente bancario asignado a tienda y banco. Ya existe en `App\Modules\BankingNetwork\Models\BankAgent`
- **Store / Bank / Region / Province / District**: Entidades de la estructura organizacional. Ya existen

Nota: Esta especificación no crea nuevas entidades. Todas las entidades requeridas ya existen en la base de datos y modelos.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: El servidor DEBE continuar verificando autorización mediante Policies y Gates existentes. El sidebar DEBE ocultar enlaces no autorizados pero el servidor DEBE rechazar peticiones no autorizadas independientemente. Un OPERADOR no DEBE acceder a operaciones de otro operador mediante manipulación de URL o parámetros (Principio V)
- **Data minimization**: Los datos mostrados DEBEN limitarse a los necesarios para cada pantalla. No se DEBEN exponer datos de clientes bancarios, números de tarjeta ni información secreta (Principio IX). La interfaz no DEBE incluir campos o columnas no autorizados por las especificaciones funcionales
- **Auditability**: Las operaciones de escritura (registro, anulación, confirmación de cierre, reapertura) DEBEN continuar generando registros de auditoría con usuario, fecha, acción, entidad, valores anteriores/posteriores y motivo (Principio VII). La migración visual no altera este comportamiento
- **Time and money**: Los importes DEBEN mostrarse en formato decimal con 2 decimales y símbolo "S/". Las fechas DEBEN mostrarse en zona horaria America/Lima. Los períodos (día, semana, mes, trimestre, semestre, año) DEBEN usar los límites definidos en las especificaciones funcionales. El monto bruto operado NO DEBE etiquetarse como ganancia (Principio VIII)
- **Session security**: El access token JWT DEBE mantener duración de 5 minutos. El contador visual DEBE advertir 30 segundos antes de la expiración. La renovación SOLO DEBE ocurrir tras acción explícita del usuario. El refresh token DEBE ser rotatorio. El logout DEBE revocar la sesión. Tokens inválidos DEBEN redirigir al login (Principio VI)

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: La interfaz DEBE continuar funcionando en hosting PHP convencional con renderizado Blade desde servidor. NO DEBE convertirse en SPA. Los assets DEBEN compilarse con Vite antes del despliegue. Las pantallas DEBEN ser responsive (1440px, 1280px, 768px, 375px) sin desplazamiento horizontal global (Principios III, IV)
- **Performance**: Las consultas DEBEN estar paginadas (máximo 25 registros por página). Chart.js DEBE cargarse solo en dashboards. Los dashboards DEBEN agregar datos en el servidor (DashboardQueryService). Los filtros frecuentes DEBEN usar índices de base de datos existentes. NO DEBEN introducirse consultas N+1 (Principio XI)
- **Observability and recovery**: Los errores DEBEN continuar registrándose sin secretos. La ruta `/health` DEBE mantenerse funcional. Las migraciones de vistas no requieren migraciones de base de datos. El debug DEBE permanecer desactivado en producción (Principio XII)
- **System boundary**: La interfaz NO DEBE sugerir que el sistema confirma procesamiento bancario, realiza conciliación o constituye fuente oficial bancaria. Las etiquetas y mensajes DEBEN reflejar que es un registro interno de control operacional (Propósito del Sistema)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de las rutas funcionales productivas utilizan el sistema visual Stitch aprobado (layouts, componentes, tokens de diseño)
- **SC-002**: El usuario completa el flujo de login en menos de 5 segundos desde el envío del formulario hasta la redirección (excluyendo latencia de red)
- **SC-003**: El dashboard del operador muestra datos en menos de 3 segundos desde la carga de la página (server-side rendering)
- **SC-004**: El formulario de registro de operación completa el ciclo envío → confirmación en menos de 2 segundos
- **SC-005**: Cero rutas demo (`/demo/*`) accesibles en el flujo productivo después de la migración
- **SC-006**: El 100% de las pruebas funcionales existentes (185+) continúan pasando después de la migración, y cada pantalla migrada pasa verificación manual contra sus acceptance scenarios
- **SC-007**: Cero datos mock hardcodeados en vistas productivas después de la limpieza
- **SC-008**: Todas las pantallas son utilizables en viewport de 375px sin desplazamiento horizontal ni pérdida de acciones esenciales
- **SC-009**: La navegación mediante teclado (Tab, Enter, Escape) es funcional en login, formularios de registro, tablas y modales
- **SC-010**: Matriz de migración documentada con clasificación de cada artefacto demo (reutilizado, migrado, reemplazado, eliminado, conservado)

## Assumptions

- Los componentes Stitch (`x-ui.*`, `x-screen.*`, `x-layout.*`) definidos en la especificación 006 están implementados y funcionales
- Las vistas de producción actuales extienden correctamente `layouts.authenticated` y `layouts.guest` (corregido en bug `slot-undefined-guest-layout`)
- Las migraciones de base de datos están ejecutadas y los seeders pueden poblarse para desarrollo
- El DashboardQueryService existente proporciona todas las agregaciones necesarias para los dashboards
- Los controladores de producción existentes (LoginController, OperationController, DashboardController, etc.) manejan correctamente la lógica de negocio y solo necesitan ajustes en las variables pasadas a las vistas
- El hosting de producción soporta PHP 8.3+, MySQL/MariaDB y compilación Vite previa al despliegue
- Los operadores y administradores tienen acceso a navegadores modernos con JavaScript habilitado
- Las pruebas automatizadas existentes cubren adecuadamente la lógica de negocio y no dependen de la estructura HTML específica de las vistas antiguas

## Dependencies

- Especificación 001 (Auth & Session): Sistema de autenticación JWT funcional con login, logout, refresh y sesiones
- Especificación 002 (Operational Structure): Estructura organizacional, bancos, agentes y asignaciones funcionales
- Especificación 003 (Operations Registry): Registro de operaciones con idempotencia, validación y anulación
- Especificación 004 (Operational Dashboard): Dashboards con agregaciones SQL y Chart.js
- Especificación 005 (Daily Closing): Cierres diarios con confirmación y reapertura
- Especificación 006 (Visual Foundation): Componentes Stitch, layouts, tokens de diseño y pantallas de referencia
- Bug fixes: `slot-undefined-guest-layout` (layouts funcionales), `vite-invoke-zero-args` (compilación Blade), `inline-require-array-access` (controlador operador)

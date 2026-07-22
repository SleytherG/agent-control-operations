# Feature Specification: Administración de Estructura Operacional

**Feature Branch**: No creada (no hay hook de creación de rama configurado)

**Created**: 2026-07-22

**Status**: Draft

**Input**: Administración de regiones, provincias, distritos, tiendas, bancos, agentes bancarios, operadores y asignaciones, con autorización completa del administrador y vista restringida del operador.

## Problem & Actors *(mandatory)*

**Problem**: El cliente necesita registrar su red de tiendas, los bancos con los que trabaja y los agentes bancarios instalados en cada tienda, así como los operadores que los atienden y a qué agentes están asignados. Sin esta estructura centralizada no puede organizar las operaciones ni garantizar que cada operador solo vea los agentes que le corresponden.

**Actors**:

- **ADMINISTRADOR_PROPIETARIO**: registra, consulta, modifica y desactiva toda la estructura operacional. Visualiza la red nacional completa y filtra por cualquier criterio.
- **OPERADOR**: visualiza únicamente los agentes bancarios activos a los que está asignado. No accede a la estructura nacional ni a agentes de otros operadores.
- **Sistema de control operacional**: impone autorización de servidor y evita eliminación física de entidades con operaciones asociadas.

**Change Classification**: Nueva capacidad funcional

## Clarifications

### Session 2026-07-22

- Q: ¿Desactivar una tienda con agentes activos debe hacer cascada o advertir? → A: Advertir y requerir desactivación manual previa de los agentes.
- Q: ¿Desactivar un agente con operadores asignados debe terminar las asignaciones o rechazar? → A: Terminar automáticamente las asignaciones activas, registrando fecha de fin y motivo.
- Q: ¿El sistema debe forzar cambio de contraseña en el primer inicio de sesión del operador? → A: Sí, forzar cambio en el primer login.


## Scope *(mandatory)*

### In Scope

- Registro, consulta, modificación y desactivación de referencias geográficas (región, provincia, distrito).
- Registro, consulta, modificación y desactivación de tiendas o locales.
- Registro, consulta, modificación y desactivación de bancos.
- Registro, consulta, modificación y desactivación de agentes bancarios con pertenencia a tienda y banco, nombre o código interno, estado y código de terminal opcional.
- Registro de usuarios operadores con datos de identidad y rol.
- Asignación de operadores a uno o más agentes bancarios con historial de cambios conservado.
- Vista del operador limitada exclusivamente a los agentes bancarios activos a los que está asignado.
- Vista del administrador de toda la estructura nacional con filtros por región, provincia, distrito, tienda, banco y estado.
- Protección contra eliminación física de tiendas, agentes o usuarios que tengan operaciones asociadas.
- Auditoría de cambios sensibles en entidades y asignaciones.

### Out of Scope

- Jerarquía de gerentes regionales.
- Control de asistencia, turnos ni marcación de ingreso.
- Planillas de remuneración.
- Geolocalización en tiempo real de tiendas o agentes.
- Registro de propietarios diferentes de las tiendas.
- Multiempresa o modalidad SaaS.
- Altas o bajas masivas de operadores.
- Cálculo de comisiones o ganancias.

### Business Rules

- **BR-001**: Solo `ADMINISTRADOR_PROPIETARIO` puede administrar la estructura operacional. `OPERADOR` solo consulta sus agentes asignados activos.
- **BR-002**: Una referencia geográfica tiene código o nombre único dentro de su nivel y organización. Distrito pertenece a una provincia; provincia pertenece a una región. El MVP permite nombres libres sin validación de correspondencia geográfica oficial.
- **BR-003**: Cada tienda pertenece a un distrito (o referencia equivalente) y tiene un código o nombre único dentro de la organización.
- **BR-004**: Cada banco tiene código único dentro de la organización y nombre descriptivo.
- **BR-005**: Un agente bancario pertenece exactamente a una tienda y a un banco. Tiene nombre o código interno único por organización, estado activo/inactivo y código de terminal opcional.
- **BR-006**: Una tienda puede tener uno o más agentes bancarios.
- **BR-007**: Un operador puede estar asignado a uno o más agentes bancarios.
- **BR-008**: Las asignaciones de operadores a agentes conservan historial: al desasignar se registra fecha de fin sin eliminar la asignación anterior. Una nueva asignación al mismo operador y agente se registra como registro independiente.
- **BR-009**: Un operador no puede registrarse como `ADMINISTRADOR_PROPIETARIO` desde esta funcionalidad. La cuenta administradora inicial ya existe.
- **BR-010**: La desactivación de una entidad (tienda, banco, agente, usuario) no elimina físicamente el registro. Las entidades con estado inactivo no pueden ser asignadas ni recibir nuevas operaciones, pero conservan sus relaciones históricas.
- **BR-011**: No se permite eliminar físicamente una tienda, agente o usuario que tenga operaciones asociadas. El intento debe ser rechazado y sugerir desactivación.
- **BR-012**: No se permite desactivar una tienda que tenga agentes bancarios activos. El sistema DEBE advertir al administrador listando los agentes que deben desactivarse manualmente antes de proceder.
- **BR-013**: Las fechas se muestran en `America/Lima` y se almacenan en la referencia temporal consistente del servidor.
- **BR-014**: Los cambios de estado y las modificaciones de asignación generan registro de auditoría con actor, fecha, entidad, valores anteriores, valores posteriores y motivo cuando corresponda.
- **BR-015**: Desactivar un agente bancario DEBE terminar automáticamente todas sus asignaciones activas en la misma transacción, registrando la fecha de fin y el motivo de la desactivación del agente en cada asignación afectada.
- **BR-016**: Un operador recién creado DEBE cambiar su contraseña en el primer inicio de sesión. Hasta que complete el cambio, no DEBE acceder a ninguna funcionalidad protegida excepto el formulario de cambio de contraseña.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Registrar y mantener referencias geográficas (Priority: P2)

Como `ADMINISTRADOR_PROPIETARIO`, quiero registrar regiones, provincias y distritos y mantenerlas actualizadas para organizar la ubicación de las tiendas.

**Why this priority**: Las tiendas dependen de una ubicación, pero el sistema puede operar inicialmente con una referencia mínima.

**Independent Test**: Crear, modificar y desactivar referencias geográficas desde la sesión del administrador y verificar que un operador no accede a esa administración.

**Acceptance Scenarios**:

1. **Given** un administrador autenticado, **When** registra una región con nombre, **Then** queda disponible para asignar provincias.
2. **Given** una región existente, **When** el administrador registra una provincia asociada, **Then** la provincia queda vinculada correctamente.
3. **Given** una provincia existente, **When** el administrador registra un distrito asociado, **Then** el distrito queda vinculado correctamente.
4. **Given** una referencia geográfica activa, **When** el administrador la desactiva, **Then** no puede asignarse a nuevas tiendas y se audita el cambio.
5. **Given** un operador autenticado, **When** intenta acceder a la administración geográfica por URL, **Then** el servidor rechaza la acción.

---

### User Story 2 - Registrar y administrar tiendas (Priority: P1)

Como `ADMINISTRADOR_PROPIETARIO`, quiero registrar las tiendas o locales del cliente con su ubicación para saber dónde opera cada agente bancario.

**Why this priority**: Las tiendas son la unidad organizativa principal; sin ellas no pueden existir agentes bancarios.

**Independent Test**: Crear, modificar, desactivar y filtrar tiendas; verificar que un operador no accede a la administración y que una tienda con agentes no se elimina físicamente.

**Acceptance Scenarios**:

1. **Given** un administrador y referencias geográficas activas, **When** registra una tienda con nombre o código, distrito y dirección, **Then** queda activa en la red.
2. **Given** una tienda activa, **When** el administrador modifica su nombre o ubicación, **Then** los cambios se reflejan y se auditan.
3. **Given** una tienda activa sin agentes ni operaciones, **When** el administrador la desactiva, **Then** pasa a inactiva y no puede recibir nuevos agentes.
4. **Given** una tienda con agentes u operaciones asociadas, **When** se intenta eliminar físicamente, **Then** el sistema rechaza la operación.
5. **Given** un operador, **When** consulta la lista de tiendas, **Then** solo visualiza las tiendas donde tiene agentes asignados activos.

---

### User Story 3 - Registrar y administrar bancos (Priority: P2)

Como `ADMINISTRADOR_PROPIETARIO`, quiero registrar los bancos con los que trabaja el cliente para clasificar los agentes bancarios por entidad financiera.

**Why this priority**: Los bancos son necesarios para los agentes, pero puede empezarse con uno o dos registros iniciales.

**Independent Test**: Crear, modificar y desactivar bancos; verificar unicidad de código y que el operador no administra bancos.

**Acceptance Scenarios**:

1. **Given** un administrador, **When** registra un banco con código y nombre, **Then** queda activo.
2. **Given** un banco activo, **When** se intenta crear otro con el mismo código, **Then** el sistema rechaza el duplicado.
3. **Given** un banco activo, **When** el administrador lo desactiva, **Then** no puede asignarse a nuevos agentes y se audita el cambio.

---

### User Story 4 - Registrar y administrar agentes bancarios (Priority: P1)

Como `ADMINISTRADOR_PROPIETARIO`, quiero registrar agentes bancarios asignados a tiendas y bancos para que cada punto de atención quede identificado en la red.

**Why this priority**: Los agentes bancarios son el punto donde se registran las operaciones; sin ellos no hay trazabilidad por ubicación ni banco.

**Independent Test**: Crear, modificar, desactivar y listar agentes; verificar que pertenecen a una tienda y banco activos; que el operador solo ve sus agentes asignados.

**Acceptance Scenarios**:

1. **Given** una tienda y un banco activos, **When** el administrador registra un agente bancario con nombre o código interno y código de terminal opcional, **Then** queda activo.
2. **Given** un agente activo, **When** el administrador modifica su nombre o código de terminal, **Then** los cambios se reflejan y se auditan.
3. **Given** un agente activo, **When** el administrador lo desactiva, **Then** no puede asignarse a operadores ni recibir operaciones.
4. **Given** un operador, **When** consulta agentes bancarios, **Then** solo visualiza los activos a los que está asignado y ningún otro.
5. **Given** un administrador, **When** lista agentes bancarios, **Then** puede filtrar por región, provincia, distrito, tienda, banco y estado.

---

### User Story 5 - Registrar operadores y asignarlos a agentes (Priority: P1)

Como `ADMINISTRADOR_PROPIETARIO`, quiero dar de alta operadores y asignarlos a los agentes bancarios que atenderán para que puedan iniciar sesión y registrar operaciones solo en sus puntos asignados.

**Why this priority**: Sin operadores asignados, el sistema no tiene usuarios que registren operaciones.

**Independent Test**: Crear operadores, asignarlos a agentes, cambiar asignaciones y verificar que un operador desactivado no inicia sesión.

**Acceptance Scenarios**:

1. **Given** un administrador, **When** registra un nuevo operador con nombre de usuario, correo, contraseña inicial y rol `OPERADOR`, **Then** el usuario queda activo y puede iniciar sesión.
2. **Given** un operador activo y agentes bancarios activos, **When** el administrador asigna el operador a uno o más agentes, **Then** el operador puede visualizar esos agentes.
3. **Given** un operador con asignaciones activas, **When** el administrador desasigna un agente, **Then** la asignación anterior registra su fecha de fin y el operador deja de ver ese agente.
4. **Given** un operador activo, **When** el administrador lo desactiva, **Then** el operador no puede iniciar sesión ni renovar sesiones existentes, y el cambio se audita.
5. **Given** un administrador, **When** lista operadores, **Then** puede filtrar por estado y ver sus asignaciones actuales.

---

### User Story 6 - Ver agentes asignados como operador (Priority: P1)

Como `OPERADOR`, quiero ver únicamente los agentes bancarios activos a los que estoy asignado para seleccionar dónde voy a registrar operaciones.

**Why this priority**: El operador necesita conocer sus puntos de trabajo autorizados antes de registrar cualquier operación.

**Independent Test**: Autenticarse como operador, consultar agentes asignados, manipular parámetros para obtener otros agentes y verificar rechazo.

**Acceptance Scenarios**:

1. **Given** un operador autenticado con asignaciones activas, **When** consulta sus agentes, **Then** visualiza los agentes activos asignados con su tienda y banco.
2. **Given** un operador sin asignaciones activas, **When** consulta sus agentes, **Then** recibe una lista vacía sin error.
3. **Given** un operador, **When** manipula parámetros o URLs para consultar agentes de otro operador, **Then** el servidor no devuelve esos registros.

### Edge Cases

- Asignar un operador al mismo agente dos veces con fechas solapadas: el sistema rechaza la segunda asignación activa mientras la primera no tenga fecha de fin.
- Desactivar una tienda que contiene agentes activos: el sistema rechaza la desactivación y muestra los agentes que deben desactivarse manualmente primero.
- Eliminar físicamente un banco con agentes históricos: el sistema rechaza la operación y sugiere desactivación.
- Un operador con asignación finalizada intenta ver ese agente: no debe aparecer en su lista de agentes activos.
- Modificar el banco o la tienda de un agente existente que ya tiene operaciones: el cambio debe auditarse y no debe romper la integridad histórica de las operaciones ya registradas.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` registrar, consultar, modificar y desactivar referencias geográficas (región, provincia, distrito).
- **FR-002**: El sistema DEBE exigir que una provincia pertenezca a una región y un distrito a una provincia.
- **FR-003**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` registrar, consultar, modificar y desactivar tiendas con pertenencia a un distrito.
- **FR-004**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` registrar, consultar, modificar y desactivar bancos con código único por organización.
- **FR-005**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` registrar, consultar, modificar y desactivar agentes bancarios con pertenencia a una tienda y un banco, nombre o código interno, estado y código de terminal opcional.
- **FR-006**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` registrar nuevos operadores con nombre de usuario, correo, contraseña inicial y rol `OPERADOR`.
- **FR-007**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` asignar y desasignar operadores a agentes bancarios conservando el historial de asignaciones.
- **FR-008**: El servidor DEBE impedir asignaciones activas solapadas del mismo operador al mismo agente.
- **FR-009**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` desactivar operadores. La desactivación DEBE impedir nuevos inicios de sesión y renovaciones, y el cambio DEBE auditarse.
- **FR-010**: El sistema DEBE permitir al administrador visualizar y filtrar toda la estructura nacional por región, provincia, distrito, tienda, banco y estado.
- **FR-011**: El sistema DEBE permitir al `OPERADOR` consultar exclusivamente los agentes bancarios activos a los que está asignado, incluyendo su tienda y banco.
- **FR-012**: El servidor DEBE rechazar toda solicitud del `OPERADOR` de acceder a agentes, tiendas o estructura que excedan sus asignaciones activas.
- **FR-013**: El sistema DEBE rechazar la eliminación física de tiendas, agentes o usuarios que tengan operaciones asociadas y sugerir desactivación.
- **FR-014**: Toda desactivación de entidad (región, provincia, distrito, tienda, banco, agente, usuario) DEBE conservar el registro histórico y generar auditoría.
- **FR-015**: Toda modificación de asignación DEBE generar auditoría con actor, fecha, entidad afectada y motivo cuando corresponda.
- **FR-016**: El sistema DEBE impedir que un `OPERADOR` acceda a funciones administrativas de la estructura operacional modificando URLs o parámetros.
- **FR-017**: Al desactivar un agente bancario, el sistema DEBE terminar automáticamente todas sus asignaciones activas con fecha de fin y motivo, y auditar el cambio.
- **FR-018**: El sistema DEBE exigir al operador recién creado cambiar su contraseña en el primer inicio de sesión antes de acceder a cualquier funcionalidad protegida.

### Key Entities *(include if feature involves data)*

- **Región**: nombre o código único, estado activo/inactivo, datos de auditoría.
- **Provincia**: nombre o código único dentro de su región, referencia a región, estado activo/inactivo.
- **Distrito**: nombre o código único dentro de su provincia, referencia a provincia, estado activo/inactivo.
- **Tienda**: nombre o código único, referencia a distrito, dirección u otra información de ubicación, estado activo/inactivo.
- **Banco**: código único por organización, nombre descriptivo, estado activo/inactivo.
- **Agente bancario**: nombre o código interno único por organización, referencia a tienda y banco, código de terminal opcional, estado activo/inactivo.
- **Usuario operador**: identidad del módulo de autenticación con rol `OPERADOR`, activo/inactivo.
- **Asignación operador-agente**: referencia a usuario y agente, fecha de inicio, fecha de fin, usuario que asigna/desasigna, estado activo/inactivo.
- **Registro de auditoría**: cambios en entidades operacionales con actor, fecha, entidad, before/after y motivo.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: El servidor aplica `ADMINISTRADOR_PROPIETARIO` para administración estructural y `OPERADOR` para consulta de agentes propios. Cualquier intento de cruce de roles se rechaza en el servidor antes de ejecutar consultas o mutaciones.
- **Data minimization**: Solo se conservan los datos necesarios para la estructura operacional. No se almacenan datos de clientes bancarios, información financiera ni documentos de identidad más allá del mínimo requerido por la especificación de autenticación.
- **Auditability**: Creación, modificación, desactivación y cambios de asignación generan registros de auditoría con actor, instante, entidad, valores anteriores y posteriores. Los registros no se eliminan desde la interfaz.
- **Time and money**: Las fechas se muestran en `America/Lima` y se almacenan consistentemente. No se procesan importes monetarios en esta capacidad.
- **Session security**: Aplica la sesión vigente del módulo de autenticación. Un operador desactivado no puede iniciar sesión ni renovar.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Formularios y listados administrativos utilizan renderizado de servidor, HTML semántico y CSS propio. Las vistas del operador son responsive y no requieren SPA.
- **Performance**: Listados de tiendas, agentes y asignaciones con paginación. Filtros frecuentes respaldados por índices. Consultas de estructura sin carga completa de relaciones innecesarias.
- **Observability and recovery**: Cambios administrativos y de asignación quedan registrados en auditoría. Las migraciones de esta capacidad son reversibles. Las copias de seguridad incluyen las nuevas tablas.
- **System boundary**: Esta capacidad no incorpora operaciones bancarias, conciliación, integración con bancos, multiempresa ni SaaS.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de las solicitudes de administración estructural realizadas por un operador son rechazadas por el servidor.
- **SC-002**: El 100% de las consultas de agentes realizadas por un operador contienen exclusivamente sus agentes asignados activos.
- **SC-003**: El 100% de los intentos de eliminar físicamente una tienda, agente o usuario con operaciones asociadas son rechazados.
- **SC-004**: El 100% de las desactivaciones de entidades conservan el registro histórico y generan auditoría.
- **SC-005**: El 100% de las asignaciones solapadas del mismo operador al mismo agente son rechazadas.
- **SC-006**: El administrador puede registrar una tienda con agente y asignar un operador en menos de 2 minutos.
- **SC-007**: Los listados administrativos con filtros aplicados responden en 2 segundos o menos bajo la carga definida en el plan para un volumen de hasta 500 tiendas y 2000 agentes.
- **SC-008**: El 100% de los cambios de asignación conservan el historial completo sin eliminar registros anteriores.

## Assumptions

- La organización ya existe (creada en la capacidad de autenticación) y esta capacidad opera dentro de ella.
- Las referencias geográficas son informativas; no se validan contra una base de datos geográfica oficial.
- El administrador propietario inicial creado en la capacidad de autenticación es quien administra la estructura.
- La contraseña inicial del operador se fuerza a cambio en el primer inicio de sesión; el administrador la establece al crear el operador y la comunica por el canal que considere adecuado.
- Los operadores se crean uno a uno; no hay carga masiva en este MVP.
- La dirección de la tienda es un campo de texto libre que puede incluir referencia, sin geocodificación.

## Dependencies

- Capacidad de autenticación y ciclo de sesión (`001-auth-session`) para identidad de usuarios, roles y middleware de autorización.
- Tabla `organizations` existente para el contexto de pertenencia.
- Tabla `users` con rol `OPERADOR` ampliada para el registro desde esta capacidad.

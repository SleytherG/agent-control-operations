# Feature Specification: Registro de Operaciones

**Feature Branch**: No creada (no hay hook de creación de rama configurado)

**Created**: 2026-07-22

**Status**: Draft

**Input**: Libro digital de operaciones bancarias con catálogo de tipos, registro por operador en agente asignado, anulación auditable y consultas filtrables.

## Problem & Actors *(mandatory)*

**Problem**: El cliente registra actualmente operaciones bancarias en cuadernos físicos. Necesita una herramienta digital donde cada operador registre operaciones en el agente que atiende, el administrador mantenga el catálogo de tipos, y ambos consulten el historial con trazabilidad completa. Ninguna operación debe eliminarse; las correcciones se realizan mediante anulación auditable.

**Actors**:

- **OPERADOR**: registra operaciones en los agentes bancarios activos a los que está asignado, consulta exclusivamente sus propias operaciones y puede anularlas dentro de una ventana configurable.
- **ADMINISTRADOR_PROPIETARIO**: mantiene el catálogo de tipos de operación, consulta todas las operaciones de la red, puede anular cualquier operación y define reglas de anulación.
- **Sistema de control operacional**: impone autorización de servidor, impide doble envío, preserva integridad decimal y evita eliminación física.

**Change Classification**: Nueva capacidad funcional

## Clarifications

### Session 2026-07-22

- Q: ¿Cómo se combinan los tipos generales con los tipos por banco en el formulario de registro? → A: Mostrar los tipos del banco del agente más todos los tipos generales combinados.

## Scope *(mandatory)*

### In Scope

- Catálogo de tipos de operación administrable con nombre, descripción, banco, dirección de efectivo y estado.
- Registro de operaciones por operador en agente asignado activo con monto > 0, moneda, fecha efectiva y referencia opcional.
- El usuario registrador se obtiene de la sesión; no es editable desde el formulario.
- Fecha efectiva por defecto es la hora actual del servidor; fecha retroactiva restringida a una ventana configurable.
- Confirmación visible después del registro.
- Prevención de doble envío accidental.
- Consulta de operaciones propias para el operador y de todas para el administrador, con filtros.
- Anulación de operaciones con registro de usuario, fecha, motivo y conservación del valor original.
- Operaciones anuladas visibles pero excluidas de totales activos.
- Montos con precisión decimal; sin punto flotante.

### Out of Scope

- Datos de cliente bancario (nombre, documento, cuenta).
- Número completo de tarjeta o cuenta.
- Comisiones, ganancias o márgenes.
- Integración con POS o terminales bancarias.
- Conciliación automática con bancos.
- Carga masiva de operaciones.
- Fotografías de comprobantes o vouchers.
- Cierre formal de caja (será una especificación independiente).
- Exportación contable.

### Business Rules

- **BR-001**: Solo `ADMINISTRADOR_PROPIETARIO` administra el catálogo de tipos de operación.
- **BR-002**: Cada tipo de operación tiene nombre, descripción opcional, referencia a un banco o nula para aplicación general, dirección de efectivo (`ENTRADA`, `SALIDA`, `NEUTRA`, `POR_CONFIRMAR`) y estado activo/inactivo. El nombre debe ser único por banco o global si no tiene banco asignado. Al registrar una operación en un agente, el operador ve los tipos del banco del agente más todos los tipos generales activos combinados.
- **BR-003**: Un operador solo puede registrar operaciones en agentes bancarios activos a los que está asignado. El sistema valida la asignación activa en el momento del registro.
- **BR-004**: El monto de una operación debe ser mayor que cero y almacenarse con tipo decimal. La moneda por defecto es `PEN`.
- **BR-005**: El usuario que registra la operación se obtiene de la sesión autenticada y no puede modificarse desde el formulario ni desde parámetros de solicitud.
- **BR-006**: La fecha y hora efectiva de la operación toma por defecto la hora actual del servidor. El sistema permite una fecha retroactiva hasta un máximo configurable, inicialmente de 24 horas hacia atrás. Fechas futuras no están permitidas.
- **BR-007**: Cada registro exitoso muestra una confirmación clara al operador y la operación aparece inmediatamente en su historial.
- **BR-008**: El sistema debe impedir el doble envío accidental del mismo formulario mediante un token de idempotencia por sesión.
- **BR-009**: Ninguna operación puede eliminarse físicamente. El estado de una operación es `ACTIVA` o `ANULADA`.
- **BR-010**: `ADMINISTRADOR_PROPIETARIO` puede anular cualquier operación. `OPERADOR` puede anular únicamente sus propias operaciones dentro de una ventana configurable, inicialmente de 24 horas desde el registro.
- **BR-011**: Una anulación debe registrar: usuario que anuló, fecha y hora de anulación, motivo (obligatorio) y debe conservar el valor original de la operación. La operación anulada permanece visible y auditable.
- **BR-012**: Las operaciones anuladas no participan en totales activos (cantidad, monto bruto, entradas, salidas).
- **BR-013**: Las fechas se muestran en `America/Lima`. Los montos se almacenan con precisión decimal y nunca se usa `float` o `double`.
- **BR-014**: Los cambios de estado de operación (registro, anulación) generan registro de auditoría.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Administrar catálogo de tipos de operación (Priority: P2)

Como `ADMINISTRADOR_PROPIETARIO`, quiero mantener el catálogo de tipos de operación para que los operadores puedan clasificar cada registro.

**Why this priority**: El catálogo puede precargarse con tipos comunes; el registro de operaciones puede comenzar con valores seed.

**Independent Test**: Crear, modificar y desactivar tipos de operación como administrador; verificar unicidad de nombre por banco.

**Acceptance Scenarios**:

1. **Given** un administrador, **When** registra un tipo de operación con nombre, banco específico y dirección `ENTRADA`, **Then** queda activo y disponible para ese banco.
2. **Given** un tipo existente, **When** el administrador intenta crear otro con el mismo nombre para el mismo banco, **Then** el sistema rechaza el duplicado.
3. **Given** un tipo activo, **When** el administrador lo desactiva, **Then** no puede seleccionarse en nuevos registros pero las operaciones existentes conservan su tipo.
4. **Given** un operador, **When** intenta acceder al catálogo de tipos, **Then** el servidor rechaza la acción.

---

### User Story 2 - Registrar una operación (Priority: P1)

Como `OPERADOR`, quiero registrar una operación en el agente bancario que estoy atendiendo para digitalizar el cuaderno físico.

**Why this priority**: Es la funcionalidad central del sistema; sin registro de operaciones no hay libro digital.

**Independent Test**: Autenticarse como operador asignado, registrar operación con datos válidos, verificar confirmación y persistencia.

**Acceptance Scenarios**:

1. **Given** un operador autenticado con asignación activa al agente, **When** registra una operación con tipo, monto > 0, moneda PEN y fecha efectiva actual, **Then** la operación queda registrada como `ACTIVA` con el usuario de sesión y aparece confirmación.
2. **Given** un operador, **When** intenta registrar en un agente al que no está asignado, **Then** el servidor rechaza la operación.
3. **Given** un operador, **When** envía monto cero o negativo, **Then** el sistema rechaza con error de validación.
4. **Given** un operador, **When** envía una fecha efectiva mayor a 24 horas hacia atrás, **Then** el sistema rechaza con error de validación.
5. **Given** un operador, **When** envía una fecha efectiva futura, **Then** el sistema rechaza con error de validación.
6. **Given** un formulario enviado, **When** el operador intenta reenviarlo (doble clic, retroceso del navegador), **Then** el sistema rechaza el duplicado y muestra el resultado del primer envío.
7. **Given** un operador, **When** intenta modificar el usuario registrador mediante parámetros manipulados, **Then** el servidor ignora el valor y usa el usuario de sesión.

---

### User Story 3 - Consultar historial de operaciones (Priority: P1)

Como usuario autenticado, quiero consultar el historial de operaciones permitido por mi rol para verificar registros y detectar errores.

**Why this priority**: Sin consulta, el registro digital no reemplaza la revisión del cuaderno físico.

**Independent Test**: Registrar operaciones con múltiples operadores y verificar que cada rol ve solo lo autorizado.

**Acceptance Scenarios**:

1. **Given** un operador, **When** consulta el historial, **Then** solo ve sus propias operaciones con paginación.
2. **Given** un administrador, **When** consulta el historial, **Then** puede ver todas las operaciones y filtrar por agente, tipo, fecha, estado y operador.
3. **Given** un operador, **When** manipula filtros o URLs para ver operaciones ajenas, **Then** el servidor restringe los resultados a sus propias operaciones.
4. **Given** una operación anulada, **When** se consulta el historial, **Then** aparece visible con su estado `ANULADA`, datos de anulación y valor original.

---

### User Story 4 - Anular una operación (Priority: P2)

Como usuario autorizado, quiero anular una operación incorrecta para corregir el registro sin eliminar evidencia.

**Why this priority**: La anulación es esencial para la integridad, pero el MVP puede operar inicialmente con corrección solo por el administrador.

**Independent Test**: Anular operación propia como operador dentro de la ventana, como administrador sin restricción, y verificar que operador no anula operación ajena.

**Acceptance Scenarios**:

1. **Given** un operador y una operación propia registrada hace menos de 24 horas, **When** la anula con un motivo, **Then** la operación pasa a `ANULADA`, se registra usuario, fecha y motivo, y el valor original se conserva.
2. **Given** un operador y una operación propia registrada hace más de 24 horas, **When** intenta anularla, **Then** el sistema rechaza la anulación por ventana vencida.
3. **Given** un administrador, **When** anula cualquier operación activa con un motivo, **Then** la operación pasa a `ANULADA` sin restricción de ventana.
4. **Given** un operador, **When** intenta anular una operación de otro operador, **Then** el servidor rechaza la acción.
5. **Given** una operación ya anulada, **When** se intenta anular nuevamente, **Then** el sistema rechaza la operación.

### Edge Cases

- Doble envío simultáneo con el mismo token de idempotencia: el primero crea la operación; el segundo devuelve el resultado del primero sin duplicar.
- Fecha efectiva en un día con cambio de horario: el sistema usa UTC internamente y `America/Lima` para presentación.
- Registro justo cuando expira la asignación del operador: el sistema valida la asignación activa al momento de la solicitud.
- Monto con más de dos decimales: el sistema redondea o rechaza según la precisión configurada de la moneda (PEN usa 2 decimales).
- Anulación durante el cierre de la ventana de 24 horas: el sistema usa precisión de segundos.
- Operador intenta registrar en agente inactivo o desactivado: rechazo con mensaje claro.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` registrar, consultar, modificar y desactivar tipos de operación con nombre, descripción opcional, banco (específico o nulo para general), dirección de efectivo y estado.
- **FR-002**: El sistema DEBE exigir unicidad de nombre de tipo de operación por banco o global si no tiene banco.
- **FR-003**: El sistema DEBE permitir al `OPERADOR` registrar una operación en un agente bancario activo al que está asignado.
- **FR-004**: El sistema DEBE validar que el monto sea mayor que cero y almacenarlo con tipo decimal.
- **FR-005**: El sistema DEBE obtener el usuario registrador de la sesión autenticada. El formulario y la solicitud no deben permitir modificar este valor.
- **FR-006**: La fecha efectiva por defecto DEBE ser la hora actual del servidor. El sistema DEBE permitir una fecha retroactiva hasta un máximo configurable (inicialmente 24 horas) y DEBE rechazar fechas futuras.
- **FR-007**: El sistema DEBE impedir el doble envío accidental mediante token de idempotencia por sesión de formulario.
- **FR-008**: El sistema DEBE mostrar una confirmación después del registro exitoso y la operación DEBE aparecer en el historial.
- **FR-009**: El sistema DEBE permitir al `OPERADOR` consultar exclusivamente sus propias operaciones con paginación.
- **FR-010**: El sistema DEBE permitir al `ADMINISTRADOR_PROPIETARIO` consultar todas las operaciones y filtrar por agente, tipo, fecha, estado y operador.
- **FR-011**: Ninguna operación DEBE poder eliminarse físicamente. El estado DEBE ser `ACTIVA` o `ANULADA`.
- **FR-012**: `ADMINISTRADOR_PROPIETARIO` DEBE poder anular cualquier operación activa registrando usuario, fecha, hora y motivo.
- **FR-013**: `OPERADOR` DEBE poder anular sus propias operaciones activas dentro de una ventana configurable (inicialmente 24 horas desde el registro).
- **FR-014**: Una operación anulada DEBE conservar su valor original, registrar quién anuló, cuándo y por qué, y permanecer visible y auditable.
- **FR-015**: Las operaciones anuladas NO DEBEN participar en totales activos de cantidad, monto bruto, entradas ni salidas.
- **FR-016**: Los montos DEBEN almacenarse con precisión decimal. El sistema NUNCA DEBE usar `float` o `double` para importes.
- **FR-017**: Los filtros de historial DEBEN aplicar restricciones de autorización en el servidor antes de ejecutar la consulta.
- **FR-018**: El sistema DEBE generar auditoría en cada registro de operación y en cada anulación.

### Key Entities *(include if feature involves data)*

- **Tipo de operación**: nombre único por banco/global, descripción, banco (nullable = general), dirección de efectivo (`ENTRADA`, `SALIDA`, `NEUTRA`, `POR_CONFIRMAR`), estado activo/inactivo.
- **Operación**: agente bancario, tipo de operación, usuario registrador, monto decimal, moneda ISO, fecha y hora efectiva, fecha y hora de registro, estado (`ACTIVA`, `ANULADA`), referencia opcional, observación opcional.
- **Anulación**: referencia a la operación, usuario que anuló, fecha y hora, motivo. Es parte de la misma entidad operación (no una tabla separada).
- **Registro de auditoría**: evidencia de creación y anulación con actor, fecha, entidad, valores y motivo.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: Operador registra solo en agentes asignados activos, consulta solo sus operaciones, anula solo las propias dentro de la ventana. Administrador consulta todas, anula todas sin restricción. Toda autorización se impone en servidor.
- **Data minimization**: Solo se almacenan datos operacionales. No se registran datos de clientes, DNI, cuentas, tarjetas ni credenciales bancarias. La referencia y observación son campos libres sin formato forzado.
- **Auditability**: Cada creación de operación y cada anulación genera registro de auditoría. Las operaciones anuladas conservan su fila original con el estado cambiado y los metadatos de anulación. Sin eliminación física desde la interfaz.
- **Time and money**: Fechas en `America/Lima`, almacenamiento UTC. Montos en `DECIMAL(18,2)`. Moneda en `CHAR(3)` ISO 4217. Nunca punto flotante.
- **Session security**: La sesión JWT existente autentica al operador. Un operador desactivado no puede registrar operaciones.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Formulario de registro y listados usan renderizado de servidor con Blade. HTML semántico y CSS propio. Prevención de doble envío con JavaScript y token de servidor.
- **Performance**: Listados paginados con índices compuestos por usuario/fecha, agente/fecha, tipo/fecha y estado/fecha. Totales calculados en base de datos con agregación SQL.
- **Observability and recovery**: Registros de auditoría en cada mutación. Migraciones reversibles. Backups incluyen operaciones y anulaciones.
- **System boundary**: El registro de operaciones no confirma procesamiento bancario, no concilia con bancos y no constituye sistema contable oficial.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de los intentos de registrar en un agente no asignado o inactivo son rechazados por el servidor.
- **SC-002**: El 100% de las consultas de historial del operador contienen exclusivamente sus propias operaciones.
- **SC-003**: El 100% de los dobles envíos del mismo formulario son detectados y no generan operaciones duplicadas.
- **SC-004**: El 100% de las anulaciones conservan el valor original, el usuario que anuló, la fecha y el motivo.
- **SC-005**: El 100% de las operaciones anuladas son excluidas de los totales activos pero permanecen visibles en el historial.
- **SC-006**: Un operador puede completar el registro de una operación en menos de 30 segundos desde que carga el formulario.
- **SC-007**: Las consultas de historial con filtros responden en 2 segundos o menos para un volumen de hasta 10,000 operaciones.
- **SC-008**: El 100% de los intentos de anulación fuera de la ventana del operador son rechazados.
- **SC-009**: Ningún monto presenta errores de precisión por punto flotante en 10,000 operaciones de prueba con valores decimales variados.

## Assumptions

- Los tipos de operación iniciales se cargan mediante seed para los bancos registrados (BCP, Interbank, BBVA).
- El token de idempotencia se genera por formulario y se valida en el servidor antes de crear la operación.
- La referencia o comprobante es un texto libre sin validación de formato ni unicidad.
- La moneda inicial es PEN; el modelo soporta múltiples monedas para futura expansión.
- El operador debe tener al menos una asignación activa para ver el formulario de registro.
- Las operaciones con fecha retroactiva conservan el orden de registro (created_at) independientemente de la fecha efectiva.

## Dependencies

- Capacidad de autenticación y sesión (`001-auth-session`): identidad del usuario y middleware de autorización.
- Capacidad de estructura operacional (`002-operational-structure`): agentes bancarios, asignaciones operador-agente, bancos activos.
- Tablas `bank_agents`, `user_bank_agent_assignments`, `banks` existentes.

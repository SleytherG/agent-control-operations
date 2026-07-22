# Feature Specification: Autenticación y Ciclo de Sesión

**Feature Branch**: No creada (no hay hook de creación de rama configurado)

**Created**: 2026-07-22

**Status**: Draft

**Input**: Autenticación por usuario o correo, sesión JWT renovable solo por acción explícita,
rotación y revocación de refresh tokens, cierre trazable y desactivación administrativa de usuarios.

## Problem & Actors *(mandatory)*

**Problem**: La aplicación necesita identificar de forma confiable a cada usuario y limitar la
duración de su acceso. Sin un ciclo de sesión controlado por el servidor, una credencial vencida,
reutilizada o perteneciente a un usuario desactivado podría permitir acceso indebido y dejar un
historial incompleto de quién ingresó y por qué terminó su sesión.

**Actors**:

- **Usuario autenticable**: persona con credenciales internas activas que inicia sesión mediante su
  nombre de usuario o correo y contraseña.
- **OPERADOR**: usuario que, una vez autenticado, accede solo a las capacidades e información
  autorizadas para su rol.
- **ADMINISTRADOR_PROPIETARIO**: usuario que accede a las capacidades administrativas y puede
  desactivar usuarios, con alcance sobre toda la red.
- **Usuario autenticado**: consulta sus propias sesiones para reconocer inicios y finalizaciones.
  `ADMINISTRADOR_PROPIETARIO` puede consultar el historial de todos los usuarios; `OPERADOR` solo el
  suyo.

**Change Classification**: Nueva capacidad funcional

## Clarifications

### Session 2026-07-22

- Q: ¿Cuál debe ser la duración máxima continua de una sesión, aun con renovaciones explícitas? → A: Ocho horas.
- Q: ¿Puede un mismo usuario mantener varias sesiones activas simultáneamente? → A: Sí, como sesiones independientes.
- Q: ¿Cuándo debe vencer cada refresh token rotatorio? → A: Junto con el access token vigente.
- Q: ¿Qué debe ocurrir si dos renovaciones presentan el mismo refresh token? → A: La primera renueva y la segunda revoca toda la sesión.
- Q: ¿La consulta del historial de sesiones forma parte de esta capacidad? → A: Sí; el administrador consulta todas y cada operador solo las propias.

## Scope *(mandatory)*

### In Scope

- Inicio de sesión con nombre de usuario o correo y contraseña.
- Creación de una sesión identificable y registro de su inicio.
- Emisión de acceso con duración configurable, inicialmente de cinco minutos.
- Contador visible basado en el vencimiento informado por el servidor.
- Advertencia de vencimiento treinta segundos antes y decisión explícita de continuar o cerrar.
- Renovación explícita con rotación de la credencial de renovación.
- Logout, expiración, revocación administrativa y fallo de seguridad con trazabilidad.
- Rechazo de credenciales vencidas, revocadas, reutilizadas o de usuarios desactivados.
- Limitación de intentos repetidos de login.
- Desactivación administrativa de usuarios y revocación de sus sesiones activas.
- Consulta paginada del historial de sesiones con alcance según el rol.

### Out of Scope

- Registro público de usuarios.
- Recuperación de contraseña por correo.
- Autenticación multifactor (MFA).
- Inicio de sesión social.
- OAuth con bancos.
- Control formal de asistencia.
- Renovación automática o silenciosa de sesiones.
- Considerar el cierre del navegador como garantía de logout.

### Business Rules

- **BR-001**: Los roles iniciales son exclusivamente `ADMINISTRADOR_PROPIETARIO` y `OPERADOR`.
- **BR-002**: El nombre de usuario y el correo identifican de forma única a un usuario; el login
  ignora espacios exteriores y diferencias entre mayúsculas y minúsculas en ambos identificadores.
- **BR-003**: Una autenticación válida crea una sesión con identificador único, usuario, fecha y hora
  de inicio y fecha y hora de vencimiento del acceso.
- **BR-004**: La vigencia inicial del acceso es de cinco minutos y puede cambiarse mediante
  configuración sin modificar el comportamiento funcional.
- **BR-005**: Solo el servidor determina si una sesión y sus credenciales son válidas; el contador y
  el modal son ayudas visuales y no conceden autorización.
- **BR-006**: La renovación requiere que el usuario pulse `Continuar` mientras la sesión y el acceso
  aún son válidos; no se permite renovación automática, al recargar ni después del vencimiento.
- **BR-007**: Cada renovación válida invalida inmediatamente la credencial de renovación anterior y
  entrega una nueva asociada a la misma sesión.
- **BR-008**: La reutilización de una credencial de renovación previamente usada, incluso por una
  solicitud concurrente, se considera fallo de seguridad, revoca la sesión completa y no emite
  nuevas credenciales.
- **BR-009**: Cinco intentos fallidos de login para la misma clave de throttling en un periodo de un
  minuto bloquean nuevos intentos de esa clave durante un minuto. La clave se compone del identificador
  normalizado (lowercased, trimmed) y el hash SHA-256 de los primeros 48 bits de la IP remota.
- **BR-010**: Una autenticación exitosa reinicia el conteo de intentos fallidos aplicable.
- **BR-011**: Solo un `ADMINISTRADOR_PROPIETARIO` puede desactivar usuarios y debe registrar un motivo.
- **BR-012**: Desactivar un usuario impide nuevos logins, invalida renovaciones y revoca todas sus
  sesiones activas.
- **BR-013**: Toda sesión finalizada registra fecha y hora y exactamente uno de estos motivos:
  `LOGOUT_MANUAL`, `EXPIRACION`, `REVOCACION_ADMINISTRATIVA` o `FALLO_SEGURIDAD`.
- **BR-014**: Las fechas y horas se presentan en `America/Lima`; el servidor usa una referencia
  temporal consistente para decidir vencimientos.
- **BR-015**: Una sesión puede mantenerse mediante renovaciones explícitas durante un máximo absoluto
  de ocho horas desde su inicio; al alcanzar ese límite debe finalizar por `EXPIRACION` y exigir
  nuevamente las credenciales.
- **BR-016**: Un usuario puede mantener varias sesiones activas simultáneamente. Cada login crea una
  sesión independiente y renovar o cerrar una no modifica las demás; desactivar al usuario sí revoca
  todas sus sesiones activas.
- **BR-017**: Cada credencial de renovación vence en el mismo instante que el access token con el que
  fue emitida; una renovación válida reemplaza ambos vencimientos sin superar el límite absoluto de
  la sesión.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Iniciar sesión de forma segura (Priority: P1)

Como usuario activo, quiero ingresar con mi nombre de usuario o correo y contraseña para acceder a
la aplicación con una sesión identificable y temporal.

**Why this priority**: Ninguna capacidad protegida puede utilizarse sin identificar al usuario y
establecer una sesión válida controlada por el servidor.

**Independent Test**: Puede probarse con usuarios activos e inactivos, credenciales válidas e
inválidas y solicitudes protegidas, verificando creación de sesión, vencimiento y limitación de
intentos sin depender de las demás historias.

**Acceptance Scenarios**:

1. **Given** un usuario activo, **When** ingresa su nombre de usuario y contraseña correctos,
   **Then** accede a la aplicación y queda registrada una sesión identificable con su hora de inicio
   y vencimiento informado por el servidor.
2. **Given** un usuario activo, **When** ingresa su correo con diferencias de mayúsculas o espacios
   exteriores y la contraseña correcta, **Then** el sistema lo autentica como el mismo usuario.
3. **Given** credenciales incorrectas, **When** se intenta iniciar sesión, **Then** el acceso se
   rechaza sin revelar si el identificador existe y el intento cuenta para el límite de frecuencia.
4. **Given** cinco fallos aplicables durante un minuto, **When** se realiza otro intento antes de
   terminar el bloqueo, **Then** el servidor lo rechaza aunque las credenciales sean correctas.
5. **Given** un acceso cuyo vencimiento ya pasó, **When** se solicita un recurso protegido,
   **Then** el servidor rechaza la petición y nunca autoriza por el estado del contador visual.
6. **Given** un usuario con una sesión activa, **When** inicia sesión válidamente desde otro
   dispositivo o navegador, **Then** se crea una segunda sesión independiente sin revocar la primera.

---

### User Story 2 - Decidir antes del vencimiento (Priority: P1)

Como usuario autenticado, quiero conocer el tiempo restante y decidir explícitamente si continúo o
termino para evitar interrupciones inesperadas sin prolongar mi sesión silenciosamente.

**Why this priority**: La vigencia breve solo es utilizable si el usuario recibe una advertencia y
controla cada renovación de manera consciente.

**Independent Test**: Puede probarse con una sesión activa y tiempos controlados, verificando el
contador, el modal, una renovación y un logout sin necesitar administración de usuarios.

**Acceptance Scenarios**:

1. **Given** una sesión autenticada, **When** se muestra una página protegida, **Then** aparece un
   contador que se actualiza cada segundo a partir del vencimiento entregado por el servidor.
2. **Given** una sesión válida con treinta segundos restantes, **When** el contador alcanza el umbral,
   **Then** se muestra un modal con las opciones `Continuar` y `Cerrar sesión`.
3. **Given** el modal y una sesión todavía válida, **When** el usuario elige `Continuar`, **Then** el
   servidor valida la sesión, emite un nuevo acceso, rota la credencial de renovación, reinicia el
   contador con el nuevo vencimiento y cierra el modal.
4. **Given** el modal, **When** el usuario elige `Cerrar sesión`, **Then** la sesión se revoca, se
   registra su finalización como `LOGOUT_MANUAL`, se limpia el estado local y se redirige al login.
5. **Given** una sesión que vence mientras el modal está abierto, **When** el usuario intenta
   continuar, **Then** no se renueva, se finaliza como `EXPIRACION` y se solicitan credenciales.
6. **Given** una sesión próxima a cumplir ocho horas desde su inicio, **When** el usuario intenta
   continuar más allá del límite absoluto, **Then** no se renueva, finaliza como `EXPIRACION` y se
   solicitan nuevamente las credenciales.

---

### User Story 3 - Terminar accesos inválidos (Priority: P1)

Como responsable de seguridad operacional, quiero que todo acceso vencido, revocado o sospechoso se
termine en el servidor para que ninguna acción visual o credencial reutilizada amplíe su validez.

**Why this priority**: La expiración y la rotación no protegen el sistema si los estados inválidos
pueden seguir autorizando solicitudes o renovaciones.

**Independent Test**: Puede probarse presentando accesos vencidos, credenciales de renovación usadas,
sesiones revocadas y recargas del navegador, y verificando rechazo, limpieza y motivo de cierre.

**Acceptance Scenarios**:

1. **Given** una credencial de renovación ya usada, **When** se presenta nuevamente, **Then** el
   servidor no emite credenciales, revoca la sesión y registra `FALLO_SEGURIDAD`.
2. **Given** dos renovaciones concurrentes con la misma credencial, **When** una de ellas rota la
   credencial primero, **Then** la otra se trata como reutilización, revoca toda la sesión y registra
   `FALLO_SEGURIDAD`.
3. **Given** que el contador llega a cero, **When** la interfaz detecta el vencimiento o recibe una
   respuesta inválida del servidor, **Then** limpia el estado de autenticación y redirige al login.
4. **Given** un acceso vencido al recargar o reabrir la aplicación, **When** se carga una ruta
   protegida, **Then** no ocurre renovación silenciosa y se solicitan nuevamente las credenciales.
5. **Given** una sesión cuyo navegador fue cerrado, **When** no se recibió logout, **Then** la sesión
   conserva únicamente su vigencia de servidor y finaliza por expiración o revocación, no por el
   cierre del navegador.
6. **Given** una sesión revocada, **When** se usa cualquiera de sus credenciales, **Then** el servidor
   rechaza la solicitud y la interfaz elimina su estado local al recibir la respuesta.

---

### User Story 4 - Desactivar un usuario (Priority: P2)

Como `ADMINISTRADOR_PROPIETARIO`, quiero desactivar un usuario para impedir de inmediato que inicie o
mantenga acceso cuando ya no está autorizado.

**Why this priority**: La administración de acceso exige retirar permisos sin esperar al vencimiento
natural de todas las sesiones existentes.

**Independent Test**: Puede probarse con un administrador, un usuario objetivo con varias sesiones y
un motivo, verificando autorización, auditoría, revocación y rechazo posterior.

**Acceptance Scenarios**:

1. **Given** un usuario activo con sesiones vigentes, **When** un `ADMINISTRADOR_PROPIETARIO` lo
   desactiva indicando un motivo, **Then** todas sus sesiones se revocan como
   `REVOCACION_ADMINISTRATIVA` y se audita el cambio de estado.
2. **Given** un usuario desactivado, **When** intenta iniciar sesión con credenciales correctas,
   **Then** el servidor rechaza el acceso sin revelar su estado en el mensaje público.
3. **Given** un usuario desactivado, **When** una de sus sesiones intenta renovarse, **Then** el
   servidor rechaza la renovación y no emite credenciales.
4. **Given** un `OPERADOR`, **When** intenta desactivar un usuario mediante una solicitud manipulada,
   **Then** el servidor rechaza la acción y ningún usuario ni sesión cambia de estado.

---

### User Story 5 - Consultar historial de sesiones (Priority: P2)

Como usuario autenticado, quiero consultar el historial de sesiones permitido por mi rol para
reconocer accesos, horas de inicio y finalización y sus motivos.

**Why this priority**: La consulta hace utilizable la trazabilidad registrada sin ampliar los
privilegios del operador sobre otros usuarios.

**Independent Test**: Puede probarse con sesiones de varios usuarios y ambos roles, verificando
paginación, contenido y rechazo de filtros o identificadores que excedan el alcance autorizado.

**Acceptance Scenarios**:

1. **Given** un `ADMINISTRADOR_PROPIETARIO`, **When** consulta el historial de sesiones, **Then** puede
   ver sesiones de todos los usuarios con inicio, finalización, estado y motivo.
2. **Given** un `OPERADOR`, **When** consulta el historial, **Then** solo obtiene sus propias sesiones.
3. **Given** un `OPERADOR`, **When** manipula filtros, parámetros o URLs para solicitar sesiones de
   otro usuario, **Then** el servidor ignora silenciosamente el filtro ajeno y restringe los resultados
   exclusivamente a sus propias sesiones, sin revelar contenido ni existencia de registros de terceros.
4. **Given** más sesiones que el tamaño de una página, **When** un usuario autorizado consulta el
   historial, **Then** recibe una página limitada y puede navegar a las demás sin descargar todo el
   historial de una vez.

### Edge Cases

- Dos solicitudes de renovación simultáneas con la misma credencial: como máximo una puede tener
  éxito; la otra se trata como reutilización y revoca la sesión por fallo de seguridad.
- El reloj visual se retrasa, adelanta o la pestaña queda suspendida: al reactivarse recalcula el
  tiempo desde el vencimiento del servidor y nunca extiende la autorización.
- La conexión falla al elegir `Continuar`: el modal permanece sin asumir renovación; al vencer el
  acceso se limpia el estado y se solicita login.
- La conexión falla durante logout: la interfaz descarta su estado local; cualquier credencial que
  el servidor ya haya revocado sigue inválida y las restantes no exceden su vencimiento de servidor.
- El administrador intenta desactivarse a sí mismo: el servidor rechaza la acción para evitar que la
  red quede sin un administrador propietario activo.
- Una sesión expira sin una nueva solicitud porque se cerró el navegador: su hora final corresponde
  al vencimiento ya registrado y su motivo se deriva como `EXPIRACION` cuando se consulta o evalúa.
- Una respuesta de autenticación inválida llega con el modal abierto: el modal se cierra, se limpia
  el estado y se redirige al login.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE aceptar nombre de usuario o correo y contraseña para iniciar sesión.
- **FR-002**: El sistema DEBE autenticar únicamente usuarios activos con credenciales válidas y
  responder con un mensaje público que no revele si falló el identificador, la contraseña o el estado.
- **FR-003**: El sistema DEBE crear una sesión identificable tras cada login válido y registrar
  usuario, inicio, vencimiento previsto, estado y motivo de finalización cuando corresponda.
- **FR-004**: El sistema DEBE emitir un access token JWT con vigencia configurable e inicialmente
  igual a cinco minutos, incluyendo un vencimiento verificable por el servidor.
- **FR-005**: Toda solicitud protegida DEBE ser rechazada si el acceso está vencido, la sesión está
  revocada o el usuario está desactivado.
- **FR-006**: La interfaz autenticada DEBE mostrar cada segundo el tiempo restante calculado desde el
  vencimiento informado por el servidor.
- **FR-007**: La interfaz DEBE mostrar, al faltar treinta segundos, un modal con las acciones
  `Continuar` y `Cerrar sesión`.
- **FR-008**: El sistema DEBE renovar únicamente después de que el usuario elija `Continuar` y solo
  mientras el acceso, la sesión, la credencial de renovación y el usuario sigan válidos.
- **FR-009**: Una renovación válida DEBE emitir un nuevo acceso, invalidar la credencial de renovación
  presentada, emitir su reemplazo y devolver el nuevo vencimiento.
- **FR-010**: Cada credencial de renovación DEBE ser rotatoria, revocable, vencer junto con el access
  token vigente y persistirse únicamente de forma no recuperable; su valor original no debe poder
  obtenerse desde los datos almacenados.
- **FR-011**: El sistema DEBE rechazar toda reutilización de una credencial de renovación, incluida
  una segunda solicitud concurrente, revocar la sesión asociada y registrar el cierre como
  `FALLO_SEGURIDAD`.
- **FR-012**: La acción `Cerrar sesión` DEBE revocar la sesión vigente, registrar fecha, hora y
  `LOGOUT_MANUAL`, limpiar el estado local y redirigir al login.
- **FR-013**: Al vencer el acceso, recibir una respuesta 401 del servidor o detectar que la sesión
  expiró, la interfaz DEBE limpiar el estado autenticado y redirigir al login. Una respuesta 403
  (prohibido) o 419 (CSRF inválido) NO DEBE limpiar la autenticación; la interfaz DEBE mostrar el error
  y permitir reintentar la acción correspondiente.
- **FR-014**: Al recargar o reabrir la aplicación con un acceso vencido, el sistema DEBE solicitar
  credenciales y NO DEBE renovar silenciosamente.
- **FR-015**: El sistema DEBE registrar toda finalización con fecha, hora y uno de los cuatro motivos
  definidos en BR-013.
- **FR-016**: El sistema DEBE aplicar el límite de login definido en BR-009 y comunicar el rechazo sin
  exponer datos de la cuenta.
- **FR-017**: Un `ADMINISTRADOR_PROPIETARIO` DEBE poder desactivar a otro usuario indicando un motivo.
- **FR-018**: La desactivación DEBE impedir nuevos logins y renovaciones y revocar todas las sesiones
  activas del usuario como `REVOCACION_ADMINISTRATIVA`.
- **FR-019**: El servidor DEBE rechazar la desactivación de usuarios solicitada por un `OPERADOR`.
- **FR-020**: Las credenciales de acceso y renovación almacenadas de forma segura en el navegador NO
  DEBEN ser legibles por scripts de la página; el navegador puede transportarlas sin revelar sus
  valores al código de interfaz.
- **FR-021**: El servidor DEBE usar su propia referencia temporal y el estado persistido para toda
  decisión de autorización, independientemente del contador visual.
- **FR-022**: El sistema DEBE conservar la hora de vencimiento prevista para poder determinar y
  registrar `EXPIRACION` aun cuando el navegador se cierre sin enviar logout.
- **FR-023**: El sistema DEBE impedir que un administrador propietario se desactive a sí mismo.
- **FR-024**: El sistema DEBE impedir que cualquier renovación prolongue una sesión más de ocho horas
  desde su inicio, incluso si el acceso y la credencial de renovación aún no han vencido.
- **FR-025**: El sistema DEBE permitir múltiples sesiones simultáneas por usuario, identificarlas por
  separado y limitar renovación y logout a la sesión que presenta sus propias credenciales.
- **FR-026**: `ADMINISTRADOR_PROPIETARIO` DEBE poder consultar el historial de sesiones de todos los
  usuarios y `OPERADOR` DEBE poder consultar únicamente sus propias sesiones.
- **FR-027**: El servidor DEBE imponer el alcance del historial independientemente de filtros,
  parámetros o URLs aplicando silenciosamente `user_id = current_user` para `OPERADOR` antes de
  cualquier otro filtro, sin revelar la existencia o el contenido de sesiones ajenas.
- **FR-028**: Toda consulta del historial DEBE estar paginada con un tamaño predeterminado de 25
  registros y un máximo de 100; valores superiores al máximo DEBEN ser rechazados. Como mínimo cada
  página DEBE mostrar usuario permitido, inicio, finalización, estado y motivo de finalización.

### Key Entities *(include if feature involves data)*

- **Usuario**: identidad interna con nombre de usuario y correo únicos, contraseña protegida, rol
  (`ADMINISTRADOR_PROPIETARIO` u `OPERADOR`), estado activo/inactivo y datos de auditoría.
- **Sesión**: acceso identificable de un usuario con inicio, límite absoluto de ocho horas,
  vencimiento previsto, última renovación, estado, finalización, motivo de finalización y datos
  mínimos de contexto de seguridad que permitan distinguirla de otras sesiones del mismo usuario.
- **Credencial de renovación**: credencial de un solo uso vinculada a una sesión, conservada de forma
  no recuperable, con creación, vencimiento igual al del acceso vigente, uso y revocación.
- **Intento de autenticación**: evento mínimo para aplicar limitación por identificador normalizado y
  origen, sin almacenar contraseñas ni secretos.
- **Registro de auditoría**: evidencia de desactivación y revocación con actor, fecha, acción, usuario
  afectado, valores anteriores y posteriores y motivo.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: El servidor aplica roles en cada acción. Solo `ADMINISTRADOR_PROPIETARIO` puede
  desactivar usuarios y consultar sesiones de cualquier usuario; `OPERADOR` solo consulta sus propias
  sesiones y no obtiene datos ajenos o capacidades administrativas aunque manipule solicitudes.
- **Data minimization**: Se conservan solo identidad interna, estado, rol y metadatos necesarios de
  autenticación, sesión, limitación y auditoría. No se recopilan datos de clientes bancarios ni se
  registran contraseñas, JWT, credenciales de renovación o secretos en logs.
- **Auditability**: Inicio y finalización de cada sesión son trazables. La desactivación conserva
  administrador, fecha, usuario afectado, estado anterior/posterior y motivo; los registros de
  seguridad no se eliminan desde la interfaz.
- **Time and money**: No se procesan importes. Todas las decisiones temporales usan una referencia
  consistente y las fechas se muestran en `America/Lima`.
- **Session security**: El acceso vence inicialmente a los cinco minutos; la advertencia aparece a
  treinta segundos; renovar exige acción explícita y rota una credencial no recuperable. Expiración,
  revocación, inactividad del usuario o respuestas inválidas eliminan el estado local.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Login, contador y modal deben ser utilizables con renderizado de servidor
  en computadora, tablet y teléfono, sin exigir procesos residentes ni infraestructura adicional.
- **Performance**: Login, validación, renovación y logout deben procesar solo la identidad o sesión
  involucrada. El historial de sesiones debe consultarse paginado y sus filtros frecuentes deben
  admitir búsqueda eficiente en el volumen previsto por el plan.
- **Observability and recovery**: Los fallos de autenticación y seguridad deben registrarse sin
  contraseñas ni tokens. La comprobación de salud no debe revelar datos de sesión. La estrategia de
  respaldo debe incluir usuarios, sesiones y auditoría, y los cambios de datos deben poder revertirse
  cuando sea técnicamente viable.
- **System boundary**: Autenticar en esta aplicación no confirma operaciones bancarias, no autentica
  ante bancos y no convierte al sistema en fuente oficial bancaria o contable.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de las solicitudes protegidas con acceso vencido, sesión revocada o usuario
  desactivado son rechazadas por el servidor.
- **SC-002**: El 100% de las renovaciones válidas invalidan la credencial presentada, y el 100% de
  los intentos posteriores o concurrentes de reutilizarla son rechazados, revocan toda la sesión y
  no emiten nuevas credenciales.
- **SC-003**: En el 100% de las sesiones autenticadas, el contador refleja el vencimiento del servidor
  con una desviación visual máxima de un segundo mientras la página está activa.
- **SC-004**: En el 100% de los casos, el modal se presenta al alcanzar treinta segundos restantes y
  ninguna sesión se renueva sin que el usuario elija `Continuar`.
- **SC-005**: El 100% de los cierres observados conserva inicio, finalización y uno de los cuatro
  motivos permitidos.
- **SC-006**: Desactivar un usuario impide inmediatamente nuevos logins y renovaciones y revoca el
  100% de sus sesiones activas.
- **SC-007**: Bajo la carga interna esperada definida en el plan, al menos el 95% de los usuarios
  recibe el resultado de login, renovación o logout en dos segundos o menos.
- **SC-008**: Ninguna contraseña, access token o credencial de renovación aparece en logs, respuestas
  de error visibles o almacenamiento legible por scripts de página durante las pruebas de seguridad.
- **SC-009**: Usuarios de ambos roles pueden completar correctamente login, renovación explícita y
  logout en todos los tamaños de pantalla soportados sin asistencia externa.
- **SC-010**: El 100% de los intentos de renovar más allá de ocho horas desde el inicio son rechazados
  y terminan la sesión por `EXPIRACION`.
- **SC-011**: El 100% de los logins simultáneos válidos del mismo usuario crean sesiones independientes;
  renovar o cerrar una no altera las demás y la desactivación revoca todas las que sigan activas.
- **SC-012**: El 100% de las credenciales de renovación dejan de ser válidas al vencer su access token
  asociado y ninguna permite renovar después de ese instante.
- **SC-013**: El 100% de las consultas de historial hechas por un operador contienen exclusivamente
  sus sesiones, mientras que el administrador puede localizar sesiones de cualquier usuario; ninguna
  página supera los 100 registros.

## Assumptions

- Las cuentas son creadas y administradas internamente; no existe autoservicio de alta.
- El correo y el nombre de usuario son obligatorios y únicos para cada cuenta.
- La retención del historial se definirá en el plan conforme a seguridad y capacidad; cada credencial
  de renovación vence junto con su access token asociado.
- El origen usado para limitar intentos es el hash SHA-256 de los primeros 48 bits de la IP remota,
  combinado con el identificador normalizado (lowercased, trimmed); esta clave no permite bloquear
  cuentas de terceros que comparten IP.
- La aplicación dispone de al menos un administrador propietario activo distinto del usuario que se
  desea desactivar.
- El cliente usa navegadores modernos con cookies y JavaScript habilitados.

## Dependencies

- Catálogo interno de usuarios con rol, estado, nombre de usuario, correo y contraseña protegida.
- Configuración segura de HTTPS y secretos en producción.
- Referencia temporal confiable del servidor.

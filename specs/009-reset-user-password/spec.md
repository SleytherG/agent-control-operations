# Feature Specification: Restablecimiento Seguro de Contraseña

**Feature Branch**: No creada (el proyecto no tiene hook `before_specify` configurado)

**Created**: 2026-07-23

**Status**: Approved

**Input**: Permitir que un administrador propietario restablezca la contraseña olvidada de un operador desde `/admin/users`, comparta de forma segura una contraseña temporal y obligue al operador a definir una contraseña propia al iniciar sesión con ella.

## Clarifications

### Session 2026-07-23

- Q: ¿Cómo debe confirmar su identidad el administrador antes de restablecer una contraseña? → A: Debe ingresar y validar su contraseña actual antes de cada restablecimiento.
- Q: ¿Cuántas veces puede usarse la contraseña temporal para iniciar sesión? → A: Se invalida tras el primer inicio de sesión exitoso; si se pierde esa sesión restringida, se requiere otro restablecimiento.
- Q: ¿Qué ocurre si la cuenta del operador está inactiva o bloqueada? → A: El restablecimiento se bloquea hasta que la cuenta sea activada o desbloqueada mediante su flujo correspondiente.
- Q: ¿Cuánto tiempo permanece vigente la contraseña temporal antes de su primer uso? → A: Vence 1 hora después de su emisión.
- Q: ¿Quién puede consultar la auditoría de los restablecimientos? → A: Solo el administrador propietario.
- Q: ¿La sesión restringida puede renovar su token de acceso? → A: Sí, únicamente mediante la renovación explícita de la misma sesión restringida; esta excepción de infraestructura no concede acceso a funciones operativas ni crea una sesión nueva.
- Q: ¿El operador debe volver a introducir la contraseña temporal para completar el cambio? → A: No. El primer login exitoso la consume; la sesión vinculada demuestra ese uso y el formulario solo solicita la nueva contraseña y su confirmación.
- Q: ¿Qué límites de frecuencia se aplican? → A: Tanto el login temporal como la reautenticación administrativa permiten un máximo de 5 fallos por clave de control dentro de 60 segundos; un éxito limpia el contador correspondiente.

## Problem & Actors *(mandatory)*

**Problem**: Las contraseñas se almacenan de forma no recuperable por seguridad y el formulario de edición de usuarios no ofrece una alternativa cuando un operador olvida la suya. Como resultado, el administrador no puede recuperar oportunamente el acceso del operador. Se necesita un restablecimiento controlado que no revele la contraseña anterior, reduzca la exposición de la credencial temporal y asegure que esta sea reemplazada antes de permitir el uso normal del sistema.

**Actors**:

- **Administrador propietario**: Restablece la contraseña de un operador autorizado, recibe la credencial temporal una sola vez, la entrega por un canal privado aprobado y puede verificar el estado del restablecimiento sin conocer la contraseña definitiva.
- **Operador**: Recibe la credencial temporal, inicia sesión y define obligatoriamente una contraseña personal antes de acceder a cualquier función operativa.

**Change Classification**: Nueva capacidad funcional de administración de acceso.

## Scope *(mandatory)*

### In Scope

- Incorporar en la vista de administración de usuarios una acción explícita para restablecer la contraseña de un operador.
- Solicitar confirmación antes de restablecer y mostrar claramente la identidad del operador afectado.
- Generar una contraseña temporal segura sin revelar ni reemplazar visualmente la contraseña anterior.
- Mostrar la contraseña temporal únicamente una vez al administrador que completó el restablecimiento.
- Proporcionar instrucciones para compartir la contraseña temporal por un canal privado aprobado y evitar medios públicos o grupales.
- Marcar la cuenta para cambio obligatorio de contraseña.
- Limitar al operador autenticado con contraseña temporal a la pantalla de cambio obligatorio, a
  cerrar sesión y a renovar explícitamente esa misma sesión restringida como única excepción de
  infraestructura.
- Validar la nueva contraseña, exigir su confirmación y evitar que sea igual a la temporal.
- Completar el estado de restablecimiento y retirar la restricción al establecer correctamente la contraseña definitiva.
- Revocar las sesiones activas y credenciales de renovación del operador cuando se restablece su contraseña.
- Registrar auditoría del restablecimiento, intentos relevantes y finalización del cambio sin almacenar contraseñas.
- Permitir al administrador consultar si el cambio obligatorio continúa pendiente.

### Out of Scope

- Recuperar, descifrar o mostrar la contraseña anterior.
- Permitir que el administrador conozca o elija la contraseña personal definitiva del operador.
- Restablecimiento autónomo por correo electrónico, SMS, mensajería instantánea o preguntas de seguridad.
- Integraciones con gestores de contraseñas o servicios externos de envío de secretos.
- Restablecer la contraseña de otro administrador propietario mediante esta acción.
- Modificar los requisitos generales de inicio de sesión, duración de sesión o roles del sistema.
- Recuperación de una cuenta cuando no existe ningún administrador propietario autorizado disponible.

### Business Rules

- **BR-001**: Solo un `ADMINISTRADOR_PROPIETARIO` autenticado puede restablecer la contraseña de una cuenta `OPERADOR` dentro de la organización que administra.
- **BR-002**: La contraseña existente nunca puede mostrarse, recuperarse, registrarse ni incluirse en una respuesta del sistema.
- **BR-003**: El restablecimiento requiere identificar inequívocamente al operador, advertir que sus sesiones activas se cerrarán y validar nuevamente la contraseña actual del administrador antes de completar la acción.
- **BR-004**: Cada restablecimiento genera mediante una fuente criptográficamente segura una contraseña temporal de 20 caracteres, sin espacios y con al menos una letra, un número y un símbolo. La contraseña definitiva y las altas de usuario comparten una política canónica de 8 caracteres como mínimo y confirmación coincidente.
- **BR-005**: La contraseña temporal se muestra una sola vez, exclusivamente al administrador que confirmó la acción; después de abandonar o cerrar el resultado no puede volver a consultarse.
- **BR-006**: El sistema no almacena la contraseña temporal en texto legible ni la incluye en logs, auditorías, notificaciones o historiales.
- **BR-007**: Un nuevo restablecimiento invalida inmediatamente cualquier contraseña temporal anterior todavía pendiente.
- **BR-008**: El restablecimiento revoca las sesiones activas y credenciales de renovación del operador afectado, sin cerrar la sesión del administrador.
- **BR-009**: El primer inicio de sesión exitoso consume e invalida la contraseña temporal y crea una sesión restringida en la que el operador solo puede acceder al cambio obligatorio de contraseña, al cierre de sesión y a la renovación explícita de esa misma sesión como excepción de infraestructura. La renovación conserva el vínculo y la restricción, no crea otra sesión y no habilita funciones operativas.
- **BR-010**: Una contraseña temporal que todavía no fue consumida vence 1 hora después de su emisión; si vence antes de usarse o si, después de consumirse, se pierde la sesión restringida, el administrador debe efectuar un nuevo restablecimiento.
- **BR-011**: La nueva contraseña debe tener al menos 8 caracteres, coincidir con su confirmación y ser diferente de la contraseña temporal. Como la temporal ya fue consumida en el login, no se vuelve a solicitar: el sistema compara la nueva contraseña contra el hash temporal vigente desde la sesión vinculada.
- **BR-012**: El cambio obligatorio se completa de manera indivisible: la cuenta solo recupera acceso normal cuando la nueva contraseña queda aceptada y el restablecimiento consumido queda marcado como completado.
- **BR-013**: El administrador puede ver si el operador tiene un cambio obligatorio pendiente, pero nunca la contraseña temporal ni la contraseña definitiva.
- **BR-014**: La entrega de la contraseña temporal ocurre fuera del sistema mediante un canal privado aprobado por la organización; la interfaz debe advertir que no se comparta en canales públicos, grupales o persistentes sin protección.
- **BR-015**: La auditoría conserva actor, operador afectado, fecha y hora, acción, resultado y motivo opcional, pero nunca valores de contraseña.
- **BR-016**: Los intentos de inicio de sesión con credenciales temporales están sujetos a los mismos límites de frecuencia y controles de seguridad que cualquier inicio de sesión.
- **BR-017**: Una cuenta cuyo estado sea distinto de `ACTIVE`, incluidas una cuenta inactiva o una eventual cuenta bloqueada, no puede recibir un restablecimiento; su activación o desbloqueo debe completarse primero mediante el flujo administrativo correspondiente.
- **BR-018**: Solo un `ADMINISTRADOR_PROPIETARIO` puede consultar la auditoría de restablecimientos; el operador no accede a este historial mediante esta funcionalidad.
- **BR-019**: Los fallos de reautenticación administrativa se limitan por administrador y origen a 5 dentro de 60 segundos; los intentos de login, incluidas credenciales temporales, se limitan por identificador normalizado y origen con el mismo umbral. Al excederlo no se realiza ninguna mutación y la respuesta no revela si la cuenta o credencial era válida.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Restablecer acceso del operador (Priority: P1)

El administrador propietario restablece la contraseña de un operador que no puede iniciar sesión y obtiene una credencial temporal para entregársela de manera privada.

**Why this priority**: Resuelve directamente la interrupción operativa sin debilitar el almacenamiento seguro de contraseñas.

**Independent Test**: Un administrador puede seleccionar un operador, confirmar el restablecimiento, ver una sola vez la contraseña temporal y comprobar que la contraseña anterior y las sesiones existentes dejan de funcionar.

**Acceptance Scenarios**:

1. **Given** un administrador autenticado y un operador de su organización, **When** confirma el restablecimiento, **Then** el sistema genera una contraseña temporal, marca el cambio obligatorio, revoca las sesiones del operador y muestra la credencial una sola vez.
2. **Given** el resultado del restablecimiento visible, **When** el administrador lo cierra o abandona la página, **Then** la contraseña temporal no puede volver a consultarse.
3. **Given** una contraseña temporal pendiente, **When** el administrador efectúa otro restablecimiento, **Then** la credencial anterior deja de autenticar y solo la nueva permanece vigente.
4. **Given** un operador ajeno a la organización o una cuenta de administrador, **When** se intenta restablecer mediante manipulación de la solicitud, **Then** la acción se rechaza sin cambiar credenciales ni sesiones.
5. **Given** un administrador autenticado, **When** ingresa una contraseña propia incorrecta al confirmar el restablecimiento, **Then** la acción se rechaza sin modificar la cuenta ni las sesiones del operador.
6. **Given** una cuenta de operador inactiva o bloqueada, **When** el administrador intenta restablecer su contraseña, **Then** la acción se rechaza y se le indica activar o desbloquear la cuenta mediante el flujo correspondiente.

---

### User Story 2 - Cambiar obligatoriamente la contraseña temporal (Priority: P1)

El operador inicia sesión con la contraseña temporal y debe crear una contraseña personal antes de realizar cualquier actividad.

**Why this priority**: La credencial compartida solo es aceptable si su exposición queda limitada y no se convierte en la contraseña permanente.

**Independent Test**: Autenticar al operador con la credencial temporal, comprobar que las rutas normales están bloqueadas, establecer una contraseña válida y verificar que la temporal ya no funciona.

**Acceptance Scenarios**:

1. **Given** una contraseña temporal vigente, **When** el operador inicia sesión, **Then** el sistema lo dirige al cambio obligatorio y no le permite acceder a funciones operativas.
2. **Given** el cambio obligatorio pendiente, **When** el operador intenta abrir directamente cualquier otra ruta protegida, **Then** el sistema lo devuelve al cambio obligatorio sin ejecutar la acción solicitada.
3. **Given** una nueva contraseña válida y confirmada, **When** el operador completa el cambio, **Then** recupera el acceso correspondiente a su rol y el restablecimiento consumido queda completado.
4. **Given** una contraseña nueva inválida, diferente de su confirmación o igual a la temporal, **When** el operador intenta guardarla, **Then** el sistema explica la corrección necesaria y mantiene el acceso restringido.
5. **Given** una contraseña temporal vencida, **When** el operador intenta iniciar sesión, **Then** el acceso se rechaza sin revelar si la credencial llegó a ser válida y se le indica contactar al administrador.
6. **Given** una contraseña temporal consumida y una sesión restringida perdida o vencida, **When** el operador intenta autenticarse nuevamente con esa credencial, **Then** el acceso se rechaza y se le indica solicitar un nuevo restablecimiento.

---

### User Story 3 - Verificar estado y trazabilidad (Priority: P2)

El administrador propietario consulta si el operador aún debe cambiar su contraseña y puede reconstruir quién restableció el acceso y cuándo se completó sin exponer secretos.

**Why this priority**: Permite dar seguimiento al incidente y detectar restablecimientos no reconocidos.

**Independent Test**: Restablecer una cuenta, consultar el estado pendiente, completar el cambio como operador y revisar que estado y auditoría reflejen ambas acciones sin valores de contraseña.

**Acceptance Scenarios**:

1. **Given** un restablecimiento no completado, **When** el administrador consulta al operador, **Then** ve que el cambio está pendiente y cuándo se emitió la credencial, pero no su valor.
2. **Given** un cambio obligatorio completado, **When** el administrador vuelve a consultar al operador, **Then** el estado ya no aparece pendiente.
3. **Given** un administrador propietario y un restablecimiento completado, **When** revisa la auditoría, **Then** identifica actores, cuenta, fechas, acciones y resultados sin contraseñas ni secretos.
4. **Given** un operador autenticado, **When** intenta consultar la auditoría de restablecimientos mediante una ruta o solicitud manipulada, **Then** el acceso se rechaza sin revelar eventos ni estados internos.

### Edge Cases

- Si dos administradores intentan restablecer simultáneamente la misma cuenta, solo la credencial correspondiente al último restablecimiento completado permanece vigente.
- Si el administrador cierra accidentalmente el resultado antes de copiar la credencial temporal, debe realizar un nuevo restablecimiento; el secreto anterior no se recupera.
- Si el operador tiene sesiones abiertas en varios dispositivos, todas quedan revocadas al completar el restablecimiento.
- Si el operador intenta reutilizar la contraseña temporal después del cambio obligatorio, la autenticación se rechaza.
- Si la sesión restringida del operador vence o se pierde durante el formulario de cambio, los campos de contraseña no se conservan, la credencial temporal consumida no puede reutilizarse y el administrador debe efectuar un nuevo restablecimiento.
- Si la cuenta está inactiva o bloqueada, no se genera ninguna credencial temporal y el administrador recibe una explicación del flujo previo requerido.
- Si falla el restablecimiento antes de completarse, la credencial vigente anterior y el estado de la cuenta permanecen coherentes y no se muestra una contraseña temporal inutilizable.
- Si la validación del cambio obligatorio falla dentro de una sesión restringida vigente, el operador puede corregir los datos y volver a enviarlos; la contraseña temporal permanece consumida.
- El uso de navegación atrás, historial del navegador o recarga no vuelve a revelar la contraseña temporal.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE ofrecer la acción de restablecimiento únicamente a administradores propietarios autorizados al gestionar cuentas de operador.
- **FR-002**: El sistema DEBE exigir confirmación informada, validar nuevamente la contraseña actual del administrador y limitar sus fallos a 5 por administrador y origen dentro de 60 segundos antes de modificar las credenciales del operador.
- **FR-003**: El sistema DEBE generar con aleatoriedad criptográficamente segura una contraseña temporal de 20 caracteres, sin espacios y con al menos una letra, un número y un símbolo.
- **FR-004**: El sistema DEBE revelar la contraseña temporal una sola vez al administrador que completó la acción.
- **FR-005**: El sistema DEBE acompañar la revelación con instrucciones de entrega privada y advertencias contra canales inseguros.
- **FR-006**: El sistema DEBE impedir toda consulta posterior de la contraseña temporal.
- **FR-007**: El sistema DEBE marcar la cuenta con cambio obligatorio pendiente y registrar el momento de emisión y vencimiento.
- **FR-008**: El sistema DEBE revocar las sesiones y credenciales de renovación activas del operador al restablecer.
- **FR-009**: El sistema DEBE permitir un único inicio de sesión exitoso con la credencial temporal vigente y rechazar credenciales anteriores, vencidas, consumidas o ya sustituidas por una contraseña definitiva.
- **FR-010**: El sistema DEBE restringir la sesión iniciada con contraseña temporal al cambio obligatorio, al cierre de sesión y a la renovación explícita de esa misma sesión restringida; la renovación no puede crear otra sesión, retirar la restricción ni autorizar funciones operativas.
- **FR-011**: El sistema DEBE aplicar la restricción de cambio obligatorio a toda solicitud protegida, incluso cuando se manipulan rutas o solicitudes.
- **FR-012**: El sistema DEBE solicitar únicamente la nueva contraseña y su confirmación, exigir un mínimo de 8 caracteres y coincidencia, y validarlas antes de aceptar el cambio; no debe volver a solicitar la contraseña temporal consumida.
- **FR-013**: El sistema DEBE rechazar como contraseña definitiva el mismo valor de la contraseña temporal.
- **FR-014**: El sistema DEBE marcar el restablecimiento consumido como completado y retirar la restricción únicamente después de aceptar correctamente la contraseña definitiva.
- **FR-015**: El sistema DEBE mostrar al administrador el estado pendiente o completado del restablecimiento sin revelar secretos.
- **FR-016**: El sistema DEBE registrar eventos append-only para emisión, reemplazo, revocación asociada, consumo/inicio restringido, vencimiento y finalización, con actor, cuenta, resultado e instante, sin valores de contraseña, y permitir su consulta únicamente a un administrador propietario.
- **FR-017**: El sistema DEBE rechazar el restablecimiento de toda cuenta cuyo estado sea distinto de `ACTIVE`, sin alterarlo, y orientar al administrador hacia el flujo correspondiente.
- **FR-018**: El sistema DEBE limitar los intentos de autenticación, incluidas las credenciales temporales, a 5 fallos por identificador normalizado y origen dentro de 60 segundos; un login exitoso limpia el contador.
- **FR-019**: El sistema DEBE presentar mensajes seguros que orienten al usuario sin confirmar datos de cuentas a actores no autorizados.
- **FR-020**: El sistema DEBE garantizar que recargar, retroceder o consultar el historial no vuelva a exponer la contraseña temporal.

### Key Entities *(include if feature involves data)*

- **Usuario**: Cuenta administrada con rol, organización, estado de acceso y señal de cambio obligatorio pendiente; la contraseña permanece no recuperable.
- **Restablecimiento de contraseña**: Evento de seguridad asociado a un operador, con emisor, emisión, vencimiento, estado pendiente/consumido/completado/reemplazado/vencido y momentos relevantes, sin conservar el secreto legible.
- **Sesión**: Acceso autenticado del usuario; puede quedar revocado por el restablecimiento o restringido hasta completar el cambio obligatorio.
- **Evento de auditoría**: Registro inmutable de la acción, actor, cuenta afectada, fecha, resultado y contexto permitido, excluyendo credenciales.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: El servidor autoriza exclusivamente a `ADMINISTRADOR_PROPIETARIO` sobre cuentas `OPERADOR` de su organización y reserva a ese rol la consulta de la auditoría de restablecimientos. Un `OPERADOR`, un administrador ajeno o una solicitud manipulada no pueden restablecer cuentas, consultar dicha auditoría, conocer estados internos ni eludir el cambio obligatorio.
- **Data minimization**: Solo se usan identificadores de usuario, rol, organización, estados y fechas necesarios para controlar el restablecimiento. No se incorporan datos bancarios, de clientes, biometría ni secretos legibles; la contraseña temporal no forma parte de historiales ni comunicaciones almacenadas.
- **Auditability**: Cada emisión, reemplazo, revocación, consumo, vencimiento y finalización conserva actor humano o actor de sistema, cuenta afectada, fecha y hora en `America/Lima`, acción y resultado. Los valores anteriores y posteriores se expresan como cambios de estado, nunca como contraseñas; estos ciclos y eventos no se eliminan automáticamente ni desde la interfaz.
- **Time and money**: Emisión, vencimiento y finalización se muestran en `America/Lima`. La funcionalidad no procesa importes, periodos monetarios ni agregados, y no altera el significado del monto bruto operado.
- **Session security**: El restablecimiento revoca sesiones y credenciales de renovación del operador. La sesión obtenida con contraseña temporal permanece restringida; conserva las reglas vigentes de expiración, aviso, renovación explícita, rotación, revocación, logout y limpieza ante credenciales inválidas. Ningún campo de contraseña se persiste en estado local ni se restaura tras expirar.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: La experiencia debe integrarse a las vistas responsivas existentes de administración e inicio de sesión, ser utilizable con teclado, teléfono, tableta y computadora, y preservar la operación en hosting PHP convencional con renderizado desde servidor.
- **Performance**: La confirmación del restablecimiento, la autenticación restringida y el cambio obligatorio deben mostrar un resultado al usuario en menos de 2 segundos en condiciones normales de hosting compartido. La consulta de usuarios conserva su paginación y filtros frecuentes.
- **Observability and recovery**: Los errores se registran sin contraseñas ni secretos. La comprobación de salud existente permanece operativa; respaldo y recuperación incluyen los estados y auditorías del restablecimiento, y cualquier cambio de datos debe admitir reversión segura cuando sea viable.
- **System boundary**: Esta capacidad administra acceso interno únicamente; no confirma operaciones bancarias, no procesa transacciones, no constituye contabilidad ni integra sistemas bancarios o servicios externos de mensajería.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: En una prueba cronometrada desde que el administrador abre la sección de seguridad de un operador elegible hasta que aparece la credencial temporal, el 100% de al menos 5 recorridos independientes se completa en menos de 60 segundos, sin contar tiempos de entrega fuera del sistema.
- **SC-002**: En el 100% de intentos de prueba, la contraseña anterior, las sesiones revocadas y las credenciales temporales reemplazadas, vencidas, consumidas o ya sustituidas por una contraseña definitiva no conceden acceso.
- **SC-003**: En el 100% de rutas protegidas probadas, un operador con cambio pendiente queda limitado al cambio obligatorio, al cierre de sesión o a renovar explícitamente la misma sesión restringida sin obtener acceso operativo.
- **SC-004**: En una prueba cronometrada desde que aparece el formulario obligatorio hasta que se muestra el acceso normal, el 100% de al menos 5 recorridos con datos válidos se completa en menos de 2 minutos.
- **SC-005**: Ninguna contraseña anterior, temporal o definitiva aparece en la vista posterior, historial, auditoría, registros de errores o estado local durante las pruebas de seguridad.
- **SC-006**: El 100% de restablecimientos y cambios completados puede atribuirse a la cuenta afectada, actor y fecha correspondiente sin revelar secretos.
- **SC-007**: En una prueba guiada con al menos 10 participantes representativos y un guion idéntico, al menos 9 identifican sin ayuda, mediante dos preguntas cerradas, que la credencial debe entregarse por un canal privado aprobado y sustituirse en el primer acceso.
- **SC-008**: Durante el primer mes de uso, al menos 95% de los casos de contraseña olvidada gestionados por un administrador se resuelve sin intervención técnica ni creación de una cuenta sustituta.

## Assumptions

- La política canónica actual de contraseñas personales exige al menos 8 caracteres y confirmación coincidente; cualquier cambio futuro deberá aplicarse de forma uniforme a altas, cambios normales y cambios obligatorios.
- La organización dispone de al menos un canal privado aprobado para entregar temporalmente el secreto al operador; el sistema solo orienta la entrega y no realiza el envío.
- El administrador confirma la identidad del operador mediante el procedimiento interno de la organización antes de entregar la credencial.
- El vencimiento de 1 hora ofrece tiempo suficiente para coordinar el primer acceso sin convertir la credencial temporal en una contraseña duradera.
- Las cuentas inactivas o bloqueadas requieren una decisión administrativa separada y no se rehabilitan mediante el restablecimiento.
- La gestión existente de usuarios en `/admin/users`, autenticación, sesiones y auditoría será ampliada, no reemplazada.

## Dependencies

- Gestión existente de usuarios, roles y organizaciones.
- Autenticación, política de contraseñas, control de frecuencia y gestión segura de sesiones vigentes.
- Capacidad de auditoría de eventos de seguridad.

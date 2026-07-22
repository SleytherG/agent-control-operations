# Comando para inicializar un constitution
- /speckit.constitution
# Flujo de Spec Kit para cada Spec
- /speckit.specify
- /speckit.clarify
- /speckit.plan
- /speckit.checklist
- /speckit.tasks
- /speckit.analyze
- /speckit.implement
- /speckit.converge

# SPEC 001 — Autenticación y ciclo de sesión

/speckit.specify

Construye la capacidad de autenticación y administración del ciclo de sesión para una aplicación web interna de control de operaciones de agentes bancarios.

Los usuarios deben iniciar sesión mediante nombre de usuario o correo y contraseña.

Existen inicialmente dos roles: ADMINISTRADOR_PROPIETARIO y OPERADOR.

Después de una autenticación válida, el sistema debe crear una sesión identificable y registrar la fecha y hora del inicio.

El access token será un JWT con vigencia inicial de cinco minutos. La duración debe ser configurable.

La interfaz autenticada debe mostrar un contador visible de tiempo restante, actualizado cada segundo a partir de la fecha de vencimiento entregada por el servidor.

Cuando falten treinta segundos, debe aparecer un modal que informe que la sesión está por vencer y permita elegir entre Continuar y Cerrar sesión.

Continuar debe solicitar explícitamente una renovación. Una renovación válida debe emitir un nuevo access token, rotar el refresh token, reiniciar el contador y cerrar el modal.

Cerrar sesión debe revocar la sesión vigente, registrar la fecha y hora y redirigir al login.

Si el contador llega a cero, el token es inválido, el usuario está desactivado o el servidor responde que la sesión expiró, la aplicación debe limpiar el estado de autenticación y redirigir al login.

Al recargar o abrir nuevamente la aplicación con un token vencido, el sistema no debe renovar silenciosamente la sesión. Debe solicitar nuevamente las credenciales.

Debe registrarse el motivo de finalización de la sesión: logout manual, expiración, revocación administrativa o fallo de seguridad.

El sistema debe limitar intentos repetidos de login.

El administrador debe poder desactivar un usuario. La desactivación debe impedir nuevos accesos y revocar sus sesiones activas.

El frontend no debe poder acceder directamente al valor de los tokens almacenados de forma segura.

Fuera de alcance:

* Registro público de usuarios.
* Recuperación de contraseña por correo.
* MFA.
* Inicio de sesión social.
* OAuth con bancos.
* Control formal de asistencia.

Criterios críticos:

* Un token vencido nunca autoriza una petición.
* Un refresh token utilizado anteriormente no puede volver a utilizarse.
* Un usuario desactivado no puede renovar su sesión.
* El servidor, y no el contador visual, determina la validez de la sesión.
* El cierre del navegador no se considera un logout garantizado.

# SPEC 002 — Estructura, agentes y personal

/speckit.specify

Construye la administración de la estructura operacional del cliente.

El ADMINISTRADOR_PROPIETARIO debe poder registrar, consultar, modificar y desactivar:

* Regiones, provincias y distritos o su referencia geográfica equivalente.
* Tiendas o locales.
* Bancos.
* Agentes bancarios instalados en cada tienda.
* Usuarios operadores.
* Asignaciones de operadores a agentes bancarios.

Una tienda puede tener uno o más agentes bancarios.

Cada agente bancario pertenece a una tienda y a un banco. Debe tener un nombre o código interno, estado activo y código de terminal opcional.

Un operador puede estar asignado a uno o más agentes bancarios.

Las asignaciones pueden cambiar con el tiempo sin eliminar el historial anterior.

Un operador desactivado no puede iniciar sesión ni registrar nuevas operaciones.

No se requiere controlar horarios de trabajo, turnos, marcación de ingreso o asistencia.

El operador únicamente debe visualizar los agentes bancarios activos a los que se encuentra asignado.

El administrador debe visualizar toda la estructura nacional y filtrar por región, provincia, distrito, tienda, banco y estado.

No se deben eliminar físicamente tiendas, agentes o usuarios que tengan operaciones asociadas.

Fuera de alcance:

* Jerarquía de gerentes regionales.
* Control de asistencia.
* Planillas.
* Geolocalización en tiempo real.
* Registro de propietarios diferentes.
* Multiempresa SaaS.

# SPEC 003 — Registro de operaciones

/speckit.specify

Construye el libro digital de operaciones realizadas en agentes bancarios.

El ADMINISTRADOR_PROPIETARIO debe poder mantener un catálogo de tipos de operación.

Cada tipo debe incluir:

* Nombre.
* Descripción opcional.
* Banco aplicable o aplicación general.
* Dirección de efectivo: ENTRADA, SALIDA, NEUTRA o POR_CONFIRMAR.
* Estado activo.

El OPERADOR debe poder registrar una operación únicamente en un agente bancario activo al que esté asignado.

Una operación debe contener:

* Agente bancario.
* Tipo de operación.
* Monto mayor que cero.
* Moneda, inicialmente PEN.
* Fecha y hora de la operación.
* Número de referencia o comprobante opcional.
* Observación opcional.

El usuario que registra la operación debe ser obtenido de la sesión y no debe ser elegible ni modificable desde el formulario.

La fecha y hora deben tomar por defecto la hora actual del servidor. Las modificaciones retroactivas deben estar restringidas por reglas configurables.

Después del registro debe mostrarse una confirmación clara y la operación debe aparecer en el historial del usuario.

El OPERADOR solo puede consultar sus propias operaciones.

El ADMINISTRADOR_PROPIETARIO puede consultar todas las operaciones.

Las operaciones no deben eliminarse físicamente.

Cuando una operación sea incorrecta debe poder anularse de acuerdo con permisos definidos, manteniendo el valor original, el usuario que anuló, la fecha y el motivo.

Una operación anulada no participa en los totales activos, pero debe continuar visible y auditable.

Debe impedirse el doble envío accidental de un formulario.

Los importes deben mantener precisión decimal.

Fuera de alcance:

* Datos del cliente.
* Número de tarjeta o cuenta completo.
* Comisiones.
* Ganancia.
* Integración con POS.
* Conciliación bancaria.
* Carga masiva.
* Fotografías de comprobantes.

# SPEC 004 — Dashboard y consultas

/speckit.specify

Construye dashboards y consultas para analizar las operaciones registradas.

El OPERADOR debe visualizar únicamente métricas correspondientes a sus propias operaciones.

El dashboard del operador debe mostrar:

* Cantidad de operaciones.
* Monto bruto operado.
* Total de entradas de efectivo.
* Total de salidas de efectivo.
* Movimiento neto de efectivo.
* Distribución por tipo de operación.
* Evolución en el tiempo.

El ADMINISTRADOR_PROPIETARIO debe visualizar métricas de toda la organización.

El dashboard administrativo debe permitir filtrar por:

* Rango de fechas.
* Día.
* Semana.
* Mes.
* Trimestre.
* Semestre.
* Año.
* Región.
* Provincia.
* Distrito.
* Tienda.
* Banco.
* Agente bancario.
* Operador.
* Tipo de operación.
* Estado.

Debe existir una vista comparativa de operadores mediante gráfico y tabla. No es necesario presentar simultáneamente un gráfico independiente por cada operador; debe utilizarse un selector, ranking o visualización comparativa que permanezca legible al aumentar la cantidad de trabajadores.

Los filtros aplicados deben afectar de manera consistente tarjetas, gráficos y tablas.

Los periodos deben utilizar la zona horaria America/Lima.

Las operaciones anuladas deben excluirse por defecto y poder incluirse mediante un filtro administrativo.

Las listas deben estar paginadas.

Cuando no existan datos debe mostrarse un estado vacío, no un error ni un gráfico engañoso.

El monto bruto operado no debe denominarse ingreso, utilidad o ganancia.

Fuera de alcance:

* Predicciones.
* Inteligencia artificial.
* Exportación avanzada.
* Comparación contra información del banco.
* Cálculo de comisiones.

# SPEC 005 — Cierre operativo diario

/speckit.specify

Construye un cierre operativo diario calculado para cada agente bancario.

El cierre permitirá consolidar las operaciones activas realizadas en un agente bancario durante una fecha de negocio.

Debe mostrar:

* Cantidad total de operaciones.
* Monto bruto operado.
* Entradas de efectivo.
* Salidas de efectivo.
* Movimiento neto de efectivo.
* Totales por tipo de operación.
* Totales por operador.
* Operaciones anuladas.
* Fecha y hora de generación.
* Usuario que confirmó el cierre.

El cierre del MVP será un resumen operativo basado en las operaciones registradas. No será todavía una conciliación con el efectivo contado físicamente ni con el reporte del POS o del banco.

El operador podrá visualizar el resumen correspondiente a sus operaciones y agentes asignados.

La confirmación definitiva del cierre debe ser realizada por un usuario autorizado.

Después de la confirmación, el operador no podrá modificar las operaciones incluidas.

El administrador podrá reabrir un cierre únicamente registrando un motivo. La reapertura debe quedar auditada.

No debe permitirse más de un cierre activo para el mismo agente y fecha.

Si existen tipos de operación con dirección de efectivo POR_CONFIRMAR, el sistema debe advertirlo y no presentar el movimiento neto como un valor definitivamente conciliado.

Fuera de alcance:

* Efectivo inicial.
* Conteo físico final.
* Diferencia o faltante de caja.
* Comisiones.
* Rentabilidad.
* Conciliación contra el POS.
* Transferencias automáticas al banco.


### Prompt general para cada inicio de cada plan en cada spec independiente

- /speckit.plan

Genera el plan técnico para la especificación activa respetando estrictamente la constitución.

Utiliza la siguiente línea base tecnológica y arquitectónica:

* PHP 8.3.
* Laravel 13.
* Arquitectura monolítica modular.
* MySQL 8.0 o MariaDB compatible.
* Eloquent ORM y migraciones Laravel.
* Blade para renderizado desde servidor.
* HTML5 semántico.
* CSS3 propio.
* JavaScript ES Modules sin framework SPA.
* Vite únicamente para compilar y minificar assets.
* Chart.js cargado de forma diferida exclusivamente en páginas con gráficos.
* PHPUnit o Pest para pruebas PHP.
* Sin dependencia obligatoria de Redis.
* Sin procesos de cola permanentes en el MVP.
* Sin WebSockets.
* Sin Docker requerido en producción.
* Compatible con Apache o Nginx y hosting PHP compartido.
* Assets frontend compilados antes del despliegue.
* Zona horaria visible America/Lima y almacenamiento temporal normalizado.
* Valores monetarios mediante DECIMAL, nunca float.

Estructura la aplicación mediante módulos o dominios claramente delimitados:

* Identity y Access.
* Organization.
* Banking Network.
* Operations.
* Reporting.
* Daily Closing.
* Audit.

Mantén controladores delgados.

Coloca las reglas de negocio en servicios de aplicación, acciones o clases de dominio con nombres explícitos.

Utiliza Form Requests para validación.

Utiliza Policies, Gates y middleware para autorización.

Toda restricción de acceso debe aplicarse en consultas y servidor, no solamente en Blade o JavaScript.

Para autenticación JWT:

* Access token con duración configurable de cinco minutos.
* Refresh token opaco, rotatorio y revocable.
* Hash del refresh token almacenado en MySQL.
* Identificador de sesión asociado al refresh token.
* Detección de reutilización.
* Cookies HttpOnly, Secure y SameSite.
* Protección CSRF para solicitudes que utilicen cookies.
* Sin tokens en localStorage.
* Respuesta de autenticación con expiresAt para el temporizador.
* Sin renovación silenciosa al cargar la aplicación.
* Logout con revocación en servidor.
* Registro de eventos de sesión.

El frontend debe calcular el tiempo restante usando expiresAt menos la hora actual, recalculándolo después de cambios de visibilidad de la pestaña.

Incluye un diseño de base de datos con claves foráneas, restricciones, índices y estrategia de auditoría.

Considera como mínimo las entidades:

* organizations, aunque el MVP utilice una sola organización.
* users.
* stores.
* banks.
* bank_agents.
* user_bank_agent_assignments.
* operation_types.
* operations.
* auth_sessions.
* session_events.
* audit_logs.
* daily_closures.
* daily_closure_operations.

Usa desactivación lógica en catálogos y usuarios.

No uses eliminación física para operaciones o cierres.

Incluye índices compuestos para consultas por usuario y fecha, agente y fecha, tienda y fecha, tipo y fecha, y estado y fecha.

Diseña consultas agregadas en base de datos. No cargues colecciones completas para sumar importes en PHP.

Incluye:

1. Diagrama lógico de componentes.
2. Modelo de datos.
3. Flujo de autenticación.
4. Flujo de renovación.
5. Flujo de autorización.
6. Contratos de endpoints necesarios.
7. Estrategia de validación.
8. Estrategia de auditoría.
9. Estrategia de pruebas.
10. Plan de migraciones.
11. Riesgos de hosting compartido.
12. Procedimiento de despliegue.
13. Estrategia de rollback.
14. Comprobación explícita contra cada principio de la constitución.

No incluyas todavía:

* Registro de clientes.
* Comisiones.
* Cálculo de ganancias.
* Integración con APIs bancarias.
* Conciliación automática.
* Aplicación móvil.
* Arquitectura de microservicios.
* Multiempresa completa.

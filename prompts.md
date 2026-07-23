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


# SPEC 006 - Diseño UX/UI

/speckit.specify

Construir los fundamentos visuales, los componentes reutilizables y la
maquetación responsive de la aplicación web interna para el control de
operaciones realizadas en agentes bancarios.

La maquetación debe tomar como referencia los artefactos exportados desde
Google Stitch ubicados en:

- docs/design/stitch/v1/DESIGN.md
- docs/design/stitch/v1/MANIFEST.md
- docs/design/stitch/v1/inicio_de_sesi_n/
- docs/design/stitch/v1/aviso_de_expiraci_n_de_sesi_n/
- docs/design/stitch/v1/dashboard_del_operador/
- docs/design/stitch/v1/registro_r_pido_de_operaci_n/
- docs/design/stitch/v1/historial_de_operaciones/
- docs/design/stitch/v1/dashboard_administrativo/
- docs/design/stitch/v1/cierre_operativo_diario/

Cada carpeta contiene:

- screen.png como referencia visual.
- code.html como referencia estructural.

Los diseños de Stitch constituyen una propuesta visual inicial y pueden ser
mejorados cuando sea necesario para cumplir la constitution, las
especificaciones funcionales, la accesibilidad, la consistencia, el
responsive design y la eficiencia operacional.

El código HTML exportado por Stitch no debe copiarse directamente como código
de producción. Debe analizarse y traducirse a una solución mantenible,
reutilizable y consistente con la arquitectura del proyecto.

# Objetivo

Proporcionar una base visual reutilizable para que las futuras
especificaciones funcionales puedan conectar lógica real, datos,
autorización y persistencia sin volver a diseñar cada pantalla desde cero.

La interfaz debe ser:

- Profesional.
- Sobria.
- Confiable.
- Rápida.
- Accesible.
- Responsive.
- Consistente.
- De baja carga cognitiva.
- Adecuada para usuarios con diferente nivel de experiencia tecnológica.

# Regla de precedencia

Para comportamiento y reglas de negocio prevalecen:

1. Constitution.
2. Especificaciones funcionales.
3. Criterios de aceptación.
4. Decisiones aprobadas.

Para presentación visual:

1. DESIGN.md.
2. Decisiones visuales documentadas.
3. screen.png.
4. code.html.

Cuando una referencia visual contradiga una regla funcional, debe mantenerse
la regla funcional y documentarse la desviación visual.

# Historia de usuario 1 — Sistema visual reutilizable

Como equipo de desarrollo, necesitamos un sistema visual común para que todas
las pantallas mantengan la misma identidad, jerarquía, espaciado,
comportamiento y accesibilidad.

El sistema visual debe definir:

- Colores semánticos.
- Tipografía.
- Escala de espaciado.
- Tamaños.
- Radios.
- Bordes.
- Sombras necesarias.
- Iconografía.
- Breakpoints responsive.
- Estados interactivos.
- Estados de foco.
- Densidad de información.
- Presentación de importes monetarios.
- Presentación de entradas y salidas de efectivo.
- Presentación de estados activos, anulados y pendientes.

Los nombres de los colores deben expresar su propósito y no solamente su
apariencia.

La entrada y salida de efectivo deben distinguirse mediante texto, iconografía
y señales visuales. No deben depender únicamente del color.

# Historia de usuario 2 — Layout autenticado

Como usuario autenticado, necesito una estructura de navegación consistente
para reconocer dónde estoy, qué agente bancario estoy utilizando y cuánto
tiempo permanece activa mi sesión.

El layout autenticado debe contemplar:

- Navegación lateral en escritorio.
- Navegación adaptada para tablet y móvil.
- Encabezado.
- Identificación del usuario.
- Rol.
- Tienda activa.
- Banco y agente activo.
- Indicador de sesión.
- Área principal de contenido.
- Breadcrumbs cuando sean necesarios.
- Sistema de alertas y notificaciones.
- Acción de cerrar sesión.

Las opciones de navegación deben poder variar según el rol.

El diseño debe diferenciar la experiencia del operador de la experiencia del
administrador sin crear dos sistemas visuales incompatibles.

# Historia de usuario 3 — Pantallas de autenticación

Como usuario, necesito una pantalla de inicio de sesión y una advertencia de
expiración claras para autenticarme y controlar la continuidad de mi sesión.

Maquetar:

- Inicio de sesión.
- Credenciales incorrectas.
- Usuario desactivado.
- Demasiados intentos.
- Error de red.
- Envío en progreso.
- Advertencia de expiración.
- Renovación en progreso.
- Renovación exitosa.
- Sesión expirada.
- Sesión revocada.

La maquetación debe basarse en:

- docs/design/stitch/v1/inicio_de_sesi_n/
- docs/design/stitch/v1/aviso_de_expiraci_n_de_sesi_n/

Esta historia implementa presentación visual. Las reglas JWT y el ciclo de
seguridad pertenecen a la especificación funcional de autenticación.

# Historia de usuario 4 — Pantallas del operador

Como operador, necesito una interfaz orientada a registrar y consultar
operaciones rápidamente, con mínima carga cognitiva.

Maquetar:

- Dashboard del operador.
- Registro rápido de operación.
- Historial de operaciones.

Referencias:

- docs/design/stitch/v1/dashboard_del_operador/
- docs/design/stitch/v1/registro_r_pido_de_operaci_n/
- docs/design/stitch/v1/historial_de_operaciones/

El dashboard debe contemplar visualmente:

- Cantidad de operaciones.
- Monto bruto operado.
- Entradas de efectivo.
- Salidas de efectivo.
- Movimiento neto.
- Historial reciente.
- Gráficos.
- Acción principal Registrar operación.

El formulario de registro debe priorizar:

- Monto.
- Tipo de operación.
- Agente activo.
- Fecha y hora.
- Referencia opcional.
- Observación opcional.
- Confirmación.
- Prevención visual de doble envío.

El historial debe contemplar:

- Filtros.
- Tabla.
- Paginación.
- Estados.
- Operaciones anuladas.
- Vista responsive.

No implementar todavía persistencia ni cálculos reales dentro de esta spec.

# Historia de usuario 5 — Dashboard administrativo

Como administrador propietario, necesito una interfaz de supervisión que me
permita comprender el estado global de la organización y aplicar filtros.

Maquetar el dashboard administrativo tomando como referencia:

- docs/design/stitch/v1/dashboard_administrativo/

Debe contemplar visualmente:

- Cantidad total de operaciones.
- Monto bruto operado.
- Entradas.
- Salidas.
- Movimiento neto.
- Tiendas.
- Agentes.
- Operadores.
- Comparación de periodos.
- Filtros geográficos y operacionales.
- Rankings.
- Gráficos.
- Tablas comparativas.
- Estados vacíos.
- Estados de carga.
- Estados de error.

Los datos utilizados durante la maquetación deben ser ficticios y claramente
identificables como datos de demostración.

# Historia de usuario 6 — Cierre operativo diario

Como usuario autorizado, necesito una pantalla de cierre diario clara para
revisar visualmente el resumen de las operaciones de un agente.

Maquetar la pantalla tomando como referencia:

- docs/design/stitch/v1/cierre_operativo_diario/

Debe contemplar:

- Fecha de negocio.
- Tienda.
- Banco.
- Agente.
- Cantidad de operaciones.
- Monto bruto.
- Entradas.
- Salidas.
- Movimiento neto.
- Totales por tipo.
- Totales por operador.
- Operaciones anuladas.
- Advertencias.
- Acción de confirmar.
- Acción de reabrir para usuarios autorizados.
- Estado abierto.
- Estado confirmado.
- Estado reabierto.

La maquetación no debe presentar el cierre como conciliación bancaria,
ganancia o utilidad.

# Componentes obligatorios

Crear una representación visual consistente para:

- Layout principal.
- Sidebar.
- Encabezado.
- Navegación móvil.
- Botones.
- Enlaces.
- Inputs.
- Campo monetario.
- Selectores.
- Calendarios.
- Filtros.
- Tablas.
- Tarjetas métricas.
- Badges.
- Alertas.
- Modales.
- Toasts.
- Paginación.
- Dropdowns.
- Tabs.
- Breadcrumbs.
- Tooltips.
- Gráficos.
- Skeletons.
- Spinners.
- Estados vacíos.
- Estados de error.
- Estados de éxito.
- Confirmaciones.
- Menús de acciones.

Los componentes deben contemplar los estados que correspondan:

- Normal.
- Hover.
- Focus.
- Active.
- Disabled.
- Loading.
- Error.
- Success.

# Responsive design

Las pantallas deben ser utilizables en:

- Escritorio de 1440 px.
- Laptop de 1280 px.
- Tablet de 768 px.
- Móvil de 375 px.

No debe existir desplazamiento horizontal global.

Las tablas pueden desplazarse horizontalmente dentro de su propio contenedor
o transformarse en tarjetas cuando esto mejore la comprensión.

Las funciones esenciales no deben desaparecer en móvil.

Los filtros avanzados pueden mostrarse mediante un panel lateral o modal.

# Accesibilidad

La maquetación debe buscar conformidad con WCAG 2.2 nivel AA.

Debe incluir:

- Contraste suficiente.
- Foco visible.
- Navegación mediante teclado.
- Orden lógico de tabulación.
- Etiquetas asociadas a campos.
- Mensajes de error descriptivos.
- Áreas táctiles adecuadas.
- Textos alternativos.
- Encabezados jerárquicos.
- No depender únicamente del color.
- Respeto por preferencias de movimiento reducido.

# Mejoras autorizadas

El agente puede apartarse de Stitch para mejorar:

- Accesibilidad.
- Consistencia.
- Legibilidad.
- Comportamiento responsive.
- Jerarquía visual.
- Rendimiento.
- Reutilización.
- Semántica HTML.
- Navegación mediante teclado.
- Estados faltantes.

Toda desviación relevante debe documentar:

- Elemento original.
- Cambio realizado.
- Justificación.
- Beneficio.
- Pantallas afectadas.

# Restricciones

No utilizar logos oficiales de bancos.

No utilizar datos reales de clientes.

No incorporar información bancaria sensible.

No presentar monto operado como ganancia.

No depender de animaciones complejas.

No convertir la aplicación en una SPA.

No duplicar componentes equivalentes entre pantallas.

No insertar estilos visuales incompatibles con DESIGN.md sin documentarlo.

# Fuera de alcance

- Autenticación JWT real.
- Persistencia en base de datos.
- CRUD real.
- Cálculos financieros reales.
- Autorización real por roles.
- Integración bancaria.
- Conciliación bancaria.
- Comisiones.
- Ganancias.
- Exportaciones.
- Aplicación móvil nativa.
- Modo offline.
- Implementación completa de los gráficos con datos reales.

# Criterios de éxito

La feature se considera completa cuando:

1. Las siete pantallas de Stitch tienen una representación maquetada.
2. Las pantallas reutilizan el mismo sistema visual.
3. No existe código HTML duplicado de forma innecesaria.
4. Las pantallas funcionan en los cuatro tamaños definidos.
5. Es posible navegar por teclado.
6. Los estados importantes están representados.
7. El diseño mantiene la intención visual de Stitch.
8. Las mejoras respecto de Stitch están documentadas.
9. Los datos de demostración están aislados de las vistas.
10. La maquetación queda preparada para que futuras specs conecten lógica real.

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


<!--
URL	Vista
http://localhost:8000/demo/login	Pantalla de login (mock)
http://localhost:8000/demo/operator/dashboard	Dashboard de operador
http://localhost:8000/demo/operator/register	Registro de operación
http://localhost:8000/demo/operator/history	Historial de operaciones
http://localhost:8000/demo/admin/dashboard	Dashboard de admin
http://localhost:8000/demo/daily-closing/1	Cierre diario
http://localhost:8000/demo/expiry?expiry=warning	Modal de expiración -->

Story	Tasks	Priority
US2 (Layout)	T001–T007 (7)	P1
US1 (Login)	T008–T016 (9)	P1
US3 (Dashboard Op)	T017–T022 (6)	P2
US4 (Registro)	T023–T028 (6)	P2
US5 (Historial)	T029–T033 (5)	P3
US6 (Dashboard Admin)	T034–T039 (6)	P3
US7 (Admin CRUD)	T040–T050 (11)	P4
US8 (Cierre Diario)	T051–T057 (7)	P4
US5+US7 (Restantes)	T058–T062 (5)	P4
US10 (Limpieza)	T063–T068 (6)	P5
Polish	T069–T076 (8)	—

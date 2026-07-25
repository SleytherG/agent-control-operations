<!--
Sync Impact Report
- Version change: 2.0.0 -> 3.0.0
- Modified principles:
  - III. Portabilidad y Operación Económica — PostgreSQL sustituye MySQL/MariaDB como base
    relacional canónica; Supabase queda limitado a proveedor administrado reemplazable
  - V. Seguridad Impuesta por el Servidor — Laravel conserva las responsabilidades de seguridad y
    negocio; credenciales solo mediante variables de entorno seguras
  - VII. Integridad y Trazabilidad Operacional — integridad referencial, transacciones y migraciones
    versionadas quedan explícitas
  - VIII. Exactitud Monetaria y Temporal — DECIMAL o NUMERIC son obligatorios para importes
  - X. Calidad y Pruebas Obligatorias — se exigen pruebas de migración sobre PostgreSQL real
  - XII. Observabilidad y Recuperación — backups y restauración cubren PostgreSQL administrado
- Added principles: none
- Added sections: none
- Removed sections: none
- Removed constraints: MySQL/MariaDB como tecnología obligatoria de producción
- Templates:
  - ✅ updated: .specify/templates/plan-template.md
  - ✅ reviewed, no change required: .specify/templates/spec-template.md
  - ✅ updated: .specify/templates/tasks-template.md
  - ✅ reviewed, no change required: .specify/templates/checklist-template.md
  - ✅ reviewed, no change required: .specify/templates/constitution-template.md
  - ✅ reviewed, no change required: speckit command files under .opencode/commands/
- Runtime guidance:
  - ✅ updated: README.md
  - ✅ updated: docs/product-brief.md
  - ✅ updated: docs/backup-restore.md
  - ✅ updated: docs/deployment.md
- Specs with superseded database assumptions: 001, 002, 003, 004, 005, 007, 008, 009
- Follow-up TODOs: plan and execute the application/data migration to PostgreSQL in a separate
  technical specification before changing production infrastructure; include .env.example,
  Laravel database configuration, migrations, operational scripts, PostgreSQL tests and data cutover
-->
# Control de Operaciones Constitution

## Propósito del Sistema

El sistema DEBE ser una aplicación web interna que digitalice el cuaderno de operaciones
de una organización que administra uno o más puntos físicos.

El punto físico se denomina únicamente AGENTE. Un agente representa directamente un local,
sucursal, tienda, punto de atención, agente corresponsal o agente bancario. El sistema NO DEBE
mantener una entidad Tienda separada de Agente ni requerir que un agente pertenezca a un banco.

El sistema es exclusivamente un registro interno de control operacional. NO DEBE reemplazar los
sistemas de los bancos, confirmar que una operación fue procesada por un banco ni presentarse como
sistema contable, integración bancaria o fuente oficial de información bancaria. Estos límites
evitan atribuir al registro interno validez bancaria o contable que no posee.

## Core Principles

### I. Desarrollo Dirigido por Especificaciones

Toda funcionalidad DEBE originarse en una especificación aprobada. Cada especificación DEBE
describir el problema, los actores, las historias de usuario, las reglas de negocio, los escenarios
de aceptación, los casos límite y los elementos explícitamente fuera de alcance.

La especificación DEBE expresar qué hará el sistema y por qué, sin decidir tecnología, estructura
de código ni despliegue; esas decisiones pertenecen al plan técnico. Una conversación informal NO
autoriza una funcionalidad nueva: primero se DEBE crear o actualizar su artefacto de especificación.
Así se conserva una fuente de intención aprobada, verificable y separada de la implementación.

### II. Entregas Pequeñas e Incrementales

El producto DEBE evolucionar mediante especificaciones pequeñas, independientes y verificables.
Cada especificación DEBE representar una capacidad funcional demostrable al cliente y permitir
revisión, prueba y despliegue controlados.

Todo pedido nuevo DEBE clasificarse como corrección de especificación, corrección de implementación,
nueva capacidad funcional, cambio arquitectónico o trabajo técnico sin cambio funcional. Toda nueva
capacidad DEBE generar una especificación nueva y NO DEBE incorporarse silenciosamente a una
funcionalidad terminada. Esta clasificación contiene el alcance y mantiene trazabilidad entre
solicitud, decisión y entrega.

### III. Portabilidad y Operación Económica

La aplicación DEBE usar una base de datos relacional PostgreSQL como almacenamiento canónico de
producción, con claves foráneas e integridad referencial impuestas por la base de datos. Inicialmente,
PostgreSQL será administrado por Supabase, pero la aplicación DEBE poder trasladarse a otro proveedor
PostgreSQL sin modificar reglas de negocio. El alojamiento de Laravel PUEDE ser un hosting PHP
económico separado, siempre que soporte la versión requerida de PHP, Composer, HTTPS, conexión TLS
saliente a PostgreSQL y acceso seguro al directorio público.

Supabase DEBE utilizarse únicamente como proveedor administrado de PostgreSQL. Las reglas de negocio
NO DEBEN depender de Supabase Auth, Storage, Realtime, Edge Functions ni Data API. Laravel DEBE
acceder a PostgreSQL mediante su capa de datos y migraciones convencionales. Esta separación conserva
portabilidad razonable y evita dependencia funcional del proveedor.

El MVP NO DEBE requerir Redis, contenedores en producción, Kubernetes, servidores de aplicaciones
Java, WebSockets, procesos residentes, colas permanentes ni servicios externos de pago. Los assets
del frontend DEBEN compilarse antes del despliegue y producción NO DEBE requerir Node.js para atender
solicitudes. El servidor web DEBE exponer únicamente el directorio `public` de Laravel. Estas reglas
preservan un despliegue seguro y viable en recursos económicos. La selección de proveedor y plan
DEBE mantener una alternativa de bajo costo compatible con PostgreSQL. El servicio administrado de
PostgreSQL es la única excepción permitida a la prohibición de servicios externos de pago y DEBE
seleccionarse en el nivel de costo mínimo que satisfaga capacidad, backups y recuperación requeridos.

### IV. Interfaz Mínima y Progresiva

La interfaz DEBE construirse con renderizado desde servidor, plantillas Blade, HTML semántico, CSS
propio y JavaScript modular. NO SE DEBEN incorporar frameworks SPA ni bibliotecas de interfaz
extensas sin una justificación documentada y aprobada conforme a la gobernanza.

Las dependencias frontend DEBEN ser mínimas, auditables y cargarse únicamente en las páginas que las
necesitan. La aplicación DEBE ser utilizable en computadora, tablet y teléfono. Este enfoque reduce
costos de operación y complejidad sin sacrificar acceso multidispositivo.

### V. Seguridad Impuesta por el Servidor

El servidor DEBE verificar toda autenticación, autorización, validación y restricción de acceso. La
interfaz NUNCA DEBE considerarse una barrera de seguridad. Los roles iniciales son:

- `ADMINISTRADOR_PROPIETARIO`: administra la red y consulta información de todos los agentes y
  operadores.
- `OPERADOR`: registra operaciones y consulta únicamente la información permitida por las reglas de
  negocio.

Un `OPERADOR` NO DEBE acceder a operaciones de otro operador mediante URLs, parámetros, formularios
o solicitudes HTTP manipuladas. Las contraseñas DEBEN usar el algoritmo de hash configurado por
Laravel y NUNCA aparecer en logs. Producción DEBE usar exclusivamente HTTPS, los intentos de
autenticación DEBEN limitarse por frecuencia y los secretos y credenciales DEBEN permanecer fuera
del repositorio. Las credenciales de PostgreSQL y cualquier secreto de Supabase DEBEN suministrarse
exclusivamente mediante variables de entorno seguras y NUNCA incorporarse a código, plantillas,
documentación, imágenes de despliegue o logs.

Laravel DEBE seguir siendo responsable de autenticación, emisión y rotación de JWT y refresh tokens,
autorización, reglas de negocio, validación, auditoría y acceso a datos. Estas responsabilidades NO
DEBEN delegarse a servicios propietarios de Supabase. Estos controles hacen cumplir el mínimo
privilegio aun frente a clientes alterados y conservan una frontera de seguridad auditable.

### VI. Gestión Segura de Sesiones

El access token JWT DEBE tener duración configurable, inicialmente de cinco minutos. La interfaz
DEBE mostrar un contador calculado a partir del vencimiento informado por el servidor y, treinta
segundos antes, un modal que permita continuar o finalizar la sesión. La renovación SOLO DEBE
ocurrir después de una acción explícita del usuario.

Los refresh tokens DEBEN ser rotatorios, revocables y almacenarse de forma segura; la base de datos
DEBE guardar únicamente su hash. El logout DEBE revocar la sesión de renovación correspondiente. El
vencimiento del access token, la revocación del refresh token o una respuesta de autenticación
inválida DEBEN limpiar el estado local y redirigir al login. El cierre del navegador NO DEBE
considerarse logout confiable; la seguridad depende de expiración y revocación controladas por el
servidor.

### VII. Integridad y Trazabilidad Operacional

Las operaciones son registros de control y DEBEN conservar trazabilidad. La interfaz NO DEBE
permitir su eliminación física. Las correcciones DEBEN realizarse mediante anulación lógica,
reemplazo o edición auditada, según la especificación aprobada.

Cada operación DEBE conservar como mínimo el operador autenticado, el agente del contexto
operacional, el tipo de operación, el monto, la moneda, la fecha y hora efectiva, la fecha y
hora de registro, los efectos monetarios sobre efectivo y saldo digital, el estado y una
referencia u observación opcional. Los cambios sensibles DEBEN generar una auditoría con usuario,
fecha, acción, entidad afectada, valores anteriores, valores posteriores y motivo cuando
corresponda. Esto permite reconstruir quién hizo qué, cuándo y sobre qué información.

Las relaciones persistentes DEBEN usar claves foráneas y restricciones compatibles con PostgreSQL.
Los cambios que deban ser atómicos DEBEN ejecutarse dentro de transacciones. Toda evolución del
esquema DEBE realizarse mediante migraciones Laravel versionadas, revisables y ordenadas; los cambios
manuales no reproducibles sobre la base productiva están prohibidos.

### VIII. Exactitud Monetaria y Temporal

Los importes DEBEN almacenarse con tipos `DECIMAL` o `NUMERIC`; queda prohibido usar `float` o
`double` para valores monetarios. El sistema DEBE distinguir al menos: monto bruto operado, entradas
de efectivo,
salidas de efectivo, entradas digitales, salidas digitales, efectivo inicial, saldo digital inicial,
efectivo esperado, saldo digital esperado, efectivo real, saldo digital real y diferencias
operativas. Ninguno de estos valores DEBE presentarse como ganancia, utilidad o comisión.

Las fechas DEBEN almacenarse de forma consistente y mostrarse en la zona horaria `America/Lima`.
Toda especificación que use periodos diarios, semanales, mensuales, trimestrales, semestrales o
anuales DEBE definir explícitamente sus instantes de inicio y final. Estas reglas previenen errores
de redondeo, agregación e interpretación temporal.

### IX. Privacidad y Minimización de Datos

El sistema DEBE recopilar únicamente datos necesarios para el control operacional. El MVP NO DEBE
almacenar números completos de tarjetas, claves, credenciales bancarias, biometría, DNI obligatorio
ni información secreta del banco.

El MVP PUEDE conservar, de manera opcional, un texto breve que identifique al cliente de una
operación con propósito exclusivamente operativo interno: nombre, alias, razón social o descripción
de cliente recurrente. Este texto NO constituye un catálogo maestro de clientes, NO autoriza la
recopilación de datos sensibles adicionales y DEBE ser visible únicamente para el operador autor
y el administrador propietario de la organización. Cualquier expansión futura de la captura de
clientes DEBE contar con una especificación independiente, un análisis de privacidad y reglas de
acceso aprobadas. La ausencia de una necesidad aprobada obliga a no almacenar el dato y reduce
exposición innecesaria.

### X. Calidad y Pruebas Obligatorias

Toda historia de usuario DEBE tener escenarios de aceptación verificables. Las reglas de autorización
DEBEN contar con pruebas automáticas positivas y negativas. Las agregaciones monetarias DEBEN probar
valores límite y redondeos. El ciclo de autenticación DEBE probar login, expiración, renovación,
rotación, logout, revocación y reutilización inválida del refresh token.

Cada corrección de defecto DEBE incluir una prueba que falle antes de la corrección y pase después.
Una especificación NO DEBE considerarse terminada mientras falle cualquiera de sus pruebas
obligatorias. Las pruebas constituyen evidencia ejecutable de cumplimiento, no una actividad
opcional posterior.

Toda migración DEBE probarse en subida y, cuando sea reversible, en bajada. Los comportamientos que
dependan de bloqueos, restricciones, transacciones o sintaxis del motor DEBEN validarse contra una
instancia real de PostgreSQL; SQLite NO sustituye estas pruebas de compatibilidad.

### XI. Rendimiento y Uso Responsable de Recursos

Las consultas de operaciones DEBEN estar paginadas y los filtros frecuentes DEBEN respaldarse con
índices de base de datos. Los dashboards DEBEN agregar datos en el servidor y NO DEBEN descargar
todos los registros al navegador.

La implementación DEBE evitar consultas N+1, carga innecesaria de relaciones y procesamiento masivo
en memoria. Cada plan DEBE preservar un funcionamiento razonable en recursos de hosting compartido.
Estas restricciones impiden que el volumen de datos convierta una función correcta en una carga
operacional inviable.

### XII. Observabilidad y Recuperación

Los errores DEBEN registrarse sin secretos ni datos sensibles. La aplicación DEBE proporcionar una
ruta de comprobación de salud y producción DEBE ejecutarse con debug desactivado.

DEBE existir una estrategia documentada de copias de seguridad y recuperación para PostgreSQL y los
archivos necesarios. En Supabase, la estrategia DEBE identificar las capacidades de backup del plan
contratado y mantener un procedimiento de exportación y restauración verificable que no dependa
exclusivamente del proveedor. Las restauraciones DEBEN ensayarse periódicamente en un entorno seguro.
Las migraciones DEBEN ser reversibles cuando sea técnicamente viable; toda excepción DEBE justificarse
en el plan. Estas medidas permiten detectar fallos y recuperar el servicio sin incrementar la
exposición de datos.

### XIII. Control de Cambios y Gobernanza

Esta Constitución prevalece sobre decisiones puntuales de especificaciones, planes, tareas e
implementaciones. Toda excepción DEBE documentar el principio afectado, motivo, riesgo, alternativas
evaluadas, medida compensatoria y responsable de aprobación. Las excepciones implícitas están
prohibidas.

Cada cambio constitucional DEBE registrar fecha, versión y justificación. Antes de implementar se
DEBE comprobar que la especificación, el plan y las tareas cumplen esta Constitución. Este control
evita que decisiones locales degraden garantías transversales.

### XIV. Simplicidad del Dominio

El MVP DEBE modelar únicamente los conceptos confirmados por el cliente como necesarios para su
proceso actual. NO DEBEN introducirse entidades, filtros, catálogos o segmentaciones por anticipación
de requerimientos hipotéticos.

Debe aplicarse el principio YAGNI: no diseñar ni implementar para necesidades que no estén
documentadas en una especificación aprobada. Las necesidades futuras DEBEN incorporarse mediante
nuevas especificaciones, nunca mediante sobreingeniería preventiva.

## Separación entre Constitución y Especificaciones

La Constitución contiene principios permanentes, restricciones transversales y criterios de calidad
que aplican a todo el producto. Los modelos funcionales detallados, campos, estados, flujos y
entidades específicas pertenecen a las especificaciones y al plan técnico.

La Constitución NO DEBE inmovilizar el producto con modelos de datos detallados que puedan cambiar
tras el descubrimiento con el cliente. Cuando una nueva validación de negocio invalide una suposición
anterior:

1. Se actualiza la Constitución únicamente si el cambio afecta un principio permanente.
2. Se crea o actualiza una especificación para el comportamiento funcional.
3. Se registra qué requisitos anteriores quedan superados.
4. Se regeneran plan y tareas antes de implementar.

## Flujo de Desarrollo y Control Previo

1. Clasificar la solicitud conforme al Principio II.
2. Crear o actualizar y aprobar la especificación antes de planificar una capacidad funcional.
3. Elaborar el plan técnico y completar el control constitucional antes de investigación o diseño.
4. Repetir el control constitucional después del diseño y antes de generar tareas.
5. Generar tareas trazables a historias, escenarios, reglas y obligaciones constitucionales.
6. Ejecutar pruebas obligatorias y verificar especificación, plan y tareas antes de implementar y
   antes de cerrar la entrega.

Una entrega solo DEBE cerrarse cuando sus escenarios y reglas aplicables estén implementados, las
pruebas obligatorias pasen, no existan vulneraciones conocidas de autorización, la documentación
operativa esté actualizada y cualquier excepción tenga aprobación documentada.

## Governance

Toda enmienda DEBE incluir justificación, impacto, fecha, aprobación responsable y revisión de los
artefactos dependientes. La fecha de ratificación original permanece inmutable y la fecha de última
enmienda cambia con cada modificación aprobada.

La versión sigue versionado semántico: MAJOR para eliminar o redefinir de modo incompatible un
principio o regla de gobierno; MINOR para incorporar un principio, una sección o una ampliación
sustancial; PATCH para aclaraciones que no cambien obligaciones. Toda revisión de especificación,
plan, tareas o implementación DEBE tratar los incumplimientos constitucionales como bloqueantes
hasta corregirlos o aprobar una excepción completa conforme al Principio XIII.

**Version**: 3.0.0 | **Ratified**: 2026-07-22 | **Last Amended**: 2026-07-25

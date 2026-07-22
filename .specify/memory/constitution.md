<!--
Sync Impact Report
- Version change: 1.0.0 -> 1.1.0
- Modified principles:
  - I. Desarrollo Incremental Dirigido por Especificaciones -> I. Desarrollo Dirigido por Especificaciones
  - II. Corrección del Dominio -> II. Entregas Pequeñas e Incrementales
  - III. Seguridad y Mínimo Privilegio -> V. Seguridad Impuesta por el Servidor
  - IV. Integridad y Auditabilidad -> VII. Integridad y Trazabilidad Operacional
  - V. Privacidad y Minimización de Datos -> IX. Privacidad y Minimización de Datos
  - VI. Compatibilidad y Despliegue Económico -> III. Portabilidad y Operación Económica
  - VII. Calidad y Pruebas -> X. Calidad y Pruebas Obligatorias
  - VIII. Simplicidad y Uso Directo del Framework -> IV. Interfaz Mínima y Progresiva
  - IX. Gobierno de Cambios -> XIII. Control de Cambios y Gobernanza
- Added principles: VI. Gestión Segura de Sesiones; VIII. Exactitud Monetaria y Temporal;
  XI. Rendimiento y Uso Responsable de Recursos; XII. Observabilidad y Recuperación
- Added sections: Propósito del Sistema; Flujo de Desarrollo y Control Previo
- Removed sections: Cross-Cutting Rules; Definition of Done (obligations integrated into principles
  and workflow)
- Templates:
  - ✅ updated: .specify/templates/plan-template.md
  - ✅ updated: .specify/templates/spec-template.md
  - ✅ updated: .specify/templates/tasks-template.md
  - ✅ reviewed, no change required: .specify/templates/checklist-template.md
  - ✅ updated: .opencode/commands/speckit.specify.md
  - ✅ updated: .opencode/commands/speckit.tasks.md
  - ✅ reviewed, no change required: remaining .opencode/commands/speckit.*.md files
  - ✅ updated: docs/product-brief.md
  - ✅ reviewed, no change required: README.md
- Follow-up TODOs: none
-->
# Control de Operaciones de Agentes Bancarios Constitution

## Propósito del Sistema

El sistema DEBE ser una aplicación web interna que digitalice el registro manual de operaciones
efectuadas en una red de cajeros corresponsales o agentes bancarios ubicados en distintas tiendas
del cliente.

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

La aplicación DEBE ejecutarse en hosting PHP convencional que soporte la versión requerida de PHP,
Composer, MySQL o MariaDB, HTTPS y acceso seguro al directorio público. La solución DEBE evitar toda
dependencia de infraestructura que no sea imprescindible para el negocio.

El MVP NO DEBE requerir Redis, contenedores en producción, Kubernetes, servidores de aplicaciones
Java, WebSockets, procesos residentes, colas permanentes ni servicios externos de pago. Los assets
del frontend DEBEN compilarse antes del despliegue y producción NO DEBE requerir Node.js para atender
solicitudes. El servidor web DEBE exponer únicamente el directorio `public` de Laravel. Estas reglas
preservan un despliegue seguro y viable en recursos económicos.

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

- `ADMINISTRADOR_PROPIETARIO`: administra la red y consulta información de todas las tiendas,
  agentes y operadores.
- `OPERADOR`: registra operaciones y consulta únicamente la información permitida por las reglas de
  negocio.

Un `OPERADOR` NO DEBE acceder a operaciones de otro operador mediante URLs, parámetros, formularios
o solicitudes HTTP manipuladas. Las contraseñas DEBEN usar el algoritmo de hash configurado por
Laravel y NUNCA aparecer en logs. Producción DEBE usar exclusivamente HTTPS, los intentos de
autenticación DEBEN limitarse por frecuencia y los secretos y credenciales DEBEN permanecer fuera
del repositorio. Estos controles hacen cumplir el mínimo privilegio aun frente a clientes alterados.

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

Cada operación DEBE conservar como mínimo usuario registrador, agente bancario, tipo de operación,
monto, moneda, fecha y hora efectiva, fecha y hora de registro, estado, referencia opcional y
observación opcional. Los cambios sensibles DEBEN generar una auditoría con usuario, fecha, acción,
entidad afectada, valores anteriores, valores posteriores y motivo cuando corresponda. Esto permite
reconstruir quién hizo qué, cuándo y sobre qué información.

### VIII. Exactitud Monetaria y Temporal

Los importes DEBEN almacenarse con tipos decimales; queda prohibido usar `float` o `double` para
valores monetarios. El sistema DEBE distinguir cantidad de operaciones, monto bruto operado, entrada
de efectivo, salida de efectivo, movimiento neto de efectivo y comisión o ganancia. El MVP NO DEBE
presentar el monto bruto operado como ganancia.

Las fechas DEBEN almacenarse de forma consistente y mostrarse en la zona horaria `America/Lima`.
Toda especificación que use periodos diarios, semanales, mensuales, trimestrales, semestrales o
anuales DEBE definir explícitamente sus instantes de inicio y final. Estas reglas previenen errores
de redondeo, agregación e interpretación temporal.

### IX. Privacidad y Minimización de Datos

El sistema DEBE recopilar únicamente datos necesarios para el control operacional. El MVP NO DEBE
almacenar datos de los clientes que realizan operaciones bancarias, números completos de tarjetas,
claves, credenciales bancarias, biometría ni información secreta del banco.

La incorporación futura de clientes DEBE contar con una especificación independiente, un análisis
de privacidad y reglas de acceso aprobadas antes de recopilar datos. La ausencia de una necesidad
aprobada obliga a no almacenar el dato y reduce exposición innecesaria.

### X. Calidad y Pruebas Obligatorias

Toda historia de usuario DEBE tener escenarios de aceptación verificables. Las reglas de autorización
DEBEN contar con pruebas automáticas positivas y negativas. Las agregaciones monetarias DEBEN probar
valores límite y redondeos. El ciclo de autenticación DEBE probar login, expiración, renovación,
rotación, logout, revocación y reutilización inválida del refresh token.

Cada corrección de defecto DEBE incluir una prueba que falle antes de la corrección y pase después.
Una especificación NO DEBE considerarse terminada mientras falle cualquiera de sus pruebas
obligatorias. Las pruebas constituyen evidencia ejecutable de cumplimiento, no una actividad
opcional posterior.

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

DEBE existir una estrategia documentada de copias de seguridad para la base de datos y los archivos
necesarios. Las migraciones DEBEN ser reversibles cuando sea técnicamente viable; toda excepción
DEBE justificarse en el plan. Estas medidas permiten detectar fallos y recuperar el servicio sin
incrementar la exposición de datos.

### XIII. Control de Cambios y Gobernanza

Esta Constitución prevalece sobre decisiones puntuales de especificaciones, planes, tareas e
implementaciones. Toda excepción DEBE documentar el principio afectado, motivo, riesgo, alternativas
evaluadas, medida compensatoria y responsable de aprobación. Las excepciones implícitas están
prohibidas.

Cada cambio constitucional DEBE registrar fecha, versión y justificación. Antes de implementar se
DEBE comprobar que la especificación, el plan y las tareas cumplen esta Constitución. Este control
evita que decisiones locales degraden garantías transversales.

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

**Version**: 1.1.0 | **Ratified**: 2026-07-22 | **Last Amended**: 2026-07-22

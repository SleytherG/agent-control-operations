# Feature Specification: Migración Integral a PostgreSQL y Render

**Feature Branch**: `010-migrate-postgresql-render`

**Created**: 2026-07-25

**Status**: Draft

**Input**: Migrar integralmente la persistencia desde MySQL/MariaDB hacia PostgreSQL administrado
por Supabase y preparar el despliegue de Laravel en Render, sin modificar reglas funcionales ni el
modelo de dominio.

## Problem & Actors *(mandatory)*

**Problem**: La aplicación depende actualmente de un esquema, consultas, pruebas y procedimientos
operativos orientados a MySQL/MariaDB, mientras que la arquitectura aprobada exige PostgreSQL como
única base productiva y Render como entorno de ejecución. El cambio debe conservar íntegramente los
datos válidos y el comportamiento observable, eliminar dependencias del motor anterior, mantener la
seguridad de autenticación y producir evidencia suficiente para ejecutar, validar o revertir el
corte sin pérdida ni corrupción.

**Actors**:

- **Responsable técnico de migración**: inventaría el origen, clasifica datos, prepara ensayos,
  ejecuta la transferencia y conserva evidencias sin acceder innecesariamente a secretos.
- **Responsable de despliegue**: configura el entorno productivo, secretos, conectividad, salud,
  observabilidad y procedimiento de despliegue único.
- **Administrador propietario**: valida que usuarios, agentes, operaciones, cierres, auditoría y
  dashboards conserven su comportamiento y autoriza el corte o rollback.
- **Operador**: debe continuar autenticándose y realizando sus flujos autorizados después del corte,
  sin cambios funcionales salvo la obligación de iniciar sesión nuevamente.
- **Responsable de seguridad**: verifica mínimo privilegio, ausencia de credenciales expuestas,
  revocación de sesiones y falta de acceso público a los datos.

**Change Classification**: Cambio arquitectónico sin cambio funcional.

## Clarifications

### Session 2026-07-25

- Q: Clasificación de datos — todos los datos actuales son dummy, ¿usar esquema limpio con seeders o
  transferir datos? → A: Esquema limpio + seeders controlados (descartar dummy).
- Q: Estrategia de seguridad de Data API — ¿cómo evitar exposición pública de tablas? → A:
  Deshabilitar Data API completamente para el proyecto.

## Scope *(mandatory)*

### In Scope

- Inventariar esquema, datos, migraciones, consultas, seeders, factories, pruebas y scripts del
  origen, con su equivalencia, riesgo, acción y prueba requerida en el destino.
- Clasificar los datos actuales y elegir entre esquema limpio con seeders controlados o transferencia
  íntegra de datos válidos.
- Hacer que el esquema, restricciones, consultas y flujos existentes funcionen sobre PostgreSQL.
- Preservar identificadores, hashes de contraseña, relaciones, estados, importes, caracteres y
  significado temporal de los datos reales válidos.
- Revocar sesiones, refresh tokens y credenciales temporales durante el corte definitivo.
- Ensayar y ejecutar una migración controlada, verificable, reanudable e idempotente cuando existan
  datos reales.
- Reconciliar conteos, identificadores, relaciones, periodos e importes antes de abrir el destino.
- Preparar un despliegue reproducible de la aplicación en Render con PostgreSQL administrado por
  Supabase, SSL, secretos externos, filesystem efímero, health checks y logs sanitizados.
- Documentar backup, restauración, corte, rollback, operación, límites de planes y rotación de
  credenciales.
- Mantener MySQL/MariaDB en solo lectura durante la ventana de rollback acordada y dejar PostgreSQL
  como única base productiva al cerrar la migración.

### Out of Scope

- Cambiar reglas de negocio, roles, autorización, modelo funcional, campos de negocio, módulos,
  dashboards o diseño visual.
- Incorporar multi-tenancy, replicación permanente, doble escritura definitiva o sincronización
  activa entre motores.
- Usar Supabase Auth, Storage, Realtime, Edge Functions, Data API, `supabase-js` o acceso directo
  desde el navegador.
- Sustituir autenticación, contraseñas, JWT, refresh tokens, sesiones, Policies o auditoría de Laravel.
- Migrar claves numéricas a UUID sin una razón funcional aprobada.
- Introducir almacenamiento permanente de archivos de usuario; si fuera necesario requerirá otra
  especificación.
- Incluir credenciales reales en cualquier artefacto versionado, log, prueba, captura o reporte.

### Business Rules

- **BR-001**: PostgreSQL será la única base productiva después del corte definitivo.
- **BR-002**: Supabase actuará únicamente como proveedor administrado y reemplazable de PostgreSQL.
- **BR-003**: Laravel conservará autenticación, JWT, autorización, validación, reglas de negocio,
  auditoría y acceso a datos.
- **BR-004**: Ninguna regla funcional o frontera de autorización cambiará por la migración.
- **BR-005**: Solo se transferirán datos clasificados como reales o válidos; datos dummy no se
  copiarán automáticamente a producción.
- **BR-006**: Los hashes de contraseña se conservarán sin descifrarlos, regenerarlos ni registrarlos.
- **BR-007**: Las sesiones, refresh tokens, nonces y credenciales temporales existentes no conservarán
  validez después del corte.
- **BR-008**: Los identificadores existentes se preservarán cuando sea viable y las secuencias del
  destino deberán continuar sin colisiones.
- **BR-009**: Todos los importes conservarán exactamente precisión y escala decimal; queda prohibido
  migrarlos o reconciliarlos mediante tipos de punto flotante.
- **BR-010**: La migración no podrá corregir silenciosamente JSON inválido, relaciones rotas,
  duplicados ni datos ambiguos; deberá reportarlos y clasificarlos.
- **BR-011**: Ninguna conexión productiva podrá operar sin cifrado.
- **BR-012**: Las credenciales reales solo podrán existir en entornos locales ignorados, secretos de
  Render o gestores de secretos autorizados.
- **BR-013**: El rol permanente de aplicación tendrá mínimo privilegio y estará separado
  conceptualmente del rol usado para migraciones controladas.
- **BR-014**: Las tablas de aplicación no podrán quedar accesibles públicamente mediante interfaces
  de datos del proveedor.
- **BR-015**: No habrá escrituras simultáneas no controladas en origen y destino.
- **BR-016**: Ningún corte con datos reales podrá comenzar sin backup restaurable, ensayo completo,
  reconciliación definida y rollback probado.
- **BR-017**: Si el destino se abre a escrituras, el rollback deberá resolver explícitamente las
  nuevas operaciones para evitar divergencia o pérdida.
- **BR-018**: Los backups y evidencias no se almacenarán permanentemente en el filesystem efímero del
  servicio de aplicación.
- **BR-019**: La clave de aplicación será estable entre despliegues y nunca se generará durante cada
  build.
- **BR-020**: El vencimiento de cinco minutos de los access tokens no cambiará para compensar límites
  o arranques en frío de infraestructura.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Determinar Preparación y Estrategia (Priority: P1)

Como responsable técnico, necesito un inventario completo y una clasificación confiable de los datos
para seleccionar una estrategia segura y conocer todo trabajo requerido antes de tocar producción.

**Why this priority**: Sin inventario ni clasificación no es posible demostrar compatibilidad,
estimar el corte ni decidir si deben transferirse datos.

**Independent Test**: Revisar una copia del origen y verificar que cada objeto de base de datos,
dependencia de código y conjunto de datos tenga equivalencia, riesgo, acción, prueba y estado.

**Acceptance Scenarios**:

1. **Given** un esquema y código existentes, **When** finaliza el inventario, **Then** cada tabla,
   columna, restricción, índice, objeto programable, migración, consulta y script tiene una entrada
   trazable con uso, equivalencia, riesgo, acción y prueba.
2. **Given** la base actual, **When** se clasifica su contenido, **Then** cada conjunto se identifica
   como real, prueba, dummy, parcialmente válido, obsoleto, sensible o regenerable con evidencia.
3. **Given** que toda la base es dummy, **When** se aprueba la estrategia, **Then** se descarta la
   copia automática y se define un esquema limpio con seeders autorizados sin cuentas ni secretos de
   desarrollo.
4. **Given** que existen datos reales, **When** se aprueba la estrategia, **Then** quedan definidos
   backup, transferencia, reconciliación, ensayo, corte y rollback antes de modificar producción.

---

### User Story 2 - Transferir Datos sin Pérdida (Priority: P1)

Como administrador propietario, necesito que todos los datos históricos válidos lleguen al destino
sin alterar importes, relaciones, fechas, estados, caracteres o hashes de contraseña.

**Why this priority**: La pérdida o alteración de información operacional y de auditoría invalida la
migración aunque la aplicación pueda iniciar.

**Independent Test**: Migrar una copia controlada, comparar origen y destino y comprobar conteos,
IDs, relaciones, importes, fechas, caracteres, secuencias y clasificación de toda diferencia.

**Acceptance Scenarios**:

1. **Given** datos reales válidos y un destino vacío, **When** se ejecuta la migración, **Then** se
   conservan identificadores, hashes, relaciones, estados, importes, fechas y contenido textual.
2. **Given** IDs importados explícitamente, **When** se crea un registro posterior, **Then** la nueva
   secuencia genera un ID válido no duplicado.
3. **Given** JSON inválido, registros huérfanos o duplicados conflictivos, **When** se validan los
   datos, **Then** se detiene o rechaza el elemento afectado y se registra evidencia sanitizada sin
   corrección silenciosa.
4. **Given** tablas monetarias migradas, **When** se reconcilian origen y destino, **Then** coinciden
   conteos y totales por estado, agente, operador, fecha y dirección monetaria usando aritmética
   decimal.

---

### User Story 3 - Conservar Todos los Flujos Funcionales (Priority: P1)

Como usuario de la aplicación, necesito que autenticación, operaciones, cierres, auditoría y
dashboards funcionen igual después del corte, respetando mis permisos existentes.

**Why this priority**: PostgreSQL no puede declararse productivo si cambia el comportamiento o rompe
un flujo existente.

**Independent Test**: Ejecutar la suite completa y el flujo de demostración sobre el destino, con
casos positivos y negativos para ambos roles.

**Acceptance Scenarios**:

1. **Given** un usuario migrado, **When** inicia sesión con su contraseña existente, **Then** obtiene
   una sesión válida sin cambio de rol ni permisos.
2. **Given** sesiones previas al corte, **When** PostgreSQL queda activo, **Then** esas sesiones y
   refresh tokens son inválidos y todos los usuarios deben autenticarse nuevamente.
3. **Given** un operador autenticado, **When** registra, consulta o anula una operación y participa en
   aperturas o cierres permitidos, **Then** se conservan transacciones, auditoría y restricciones de
   propiedad existentes.
4. **Given** datos históricos migrados, **When** se consultan dashboards, rankings y filtros, **Then**
   muestran los mismos resultados monetarios y temporales esperados sin errores del motor anterior.
5. **Given** parámetros manipulados por un operador, **When** intenta acceder a datos ajenos, **Then**
   el servidor mantiene la denegación existente y no filtra información.

---

### User Story 4 - Desplegar de Forma Segura y Reproducible (Priority: P2)

Como responsable de despliegue, necesito ejecutar la aplicación en Render con conexión segura al
PostgreSQL administrado, secretos externos, salud verificable y sin depender del filesystem local.

**Why this priority**: La migración necesita un destino operable y repetible para completar el corte.

**Independent Test**: Construir el artefacto desde cero, desplegarlo en un entorno de ensayo y probar
conectividad, HTTPS, cookies, logs, assets, salud y flujos de sesión bajo el dominio real.

**Acceptance Scenarios**:

1. **Given** un despliegue limpio, **When** inicia el servicio, **Then** responde mediante un servidor
   productivo, carga assets y conecta cifradamente al destino sin revelar configuración sensible.
2. **Given** secretos configurados externamente, **When** se inspeccionan repositorio, imagen, logs y
   errores, **Then** no aparecen contraseñas, URLs con credenciales, tokens, cookies ni claves.
3. **Given** terminación TLS y proxy inverso, **When** se ejecutan login, refresh y logout, **Then** las
   URLs, redirecciones y cookies seguras funcionan bajo el dominio de producción.
4. **Given** un arranque en frío, **When** un usuario usa el ciclo de sesión, **Then** el vencimiento y
   renovación conservan su comportamiento sin ampliar silenciosamente la duración del token.
5. **Given** una comprobación de salud, **When** aplicación o base no están disponibles, **Then** se
   distingue disponibilidad de proceso y preparación para tráfico sin exponer detalles sensibles.

---

### User Story 5 - Ejecutar Corte y Recuperación Controlados (Priority: P2)

Como responsable del corte, necesito una ventana ensayada con criterios objetivos para abrir el
destino o regresar al origen sin perder escrituras ni dejar bases divergentes.

**Why this priority**: Un corte irreversible o sin reglas de decisión expone los datos operacionales.

**Independent Test**: Ejecutar en un entorno controlado el runbook completo de corte y el rollback,
incluyendo backup, mantenimiento, migración, validación, reapertura y tratamiento de nuevas escrituras.

**Acceptance Scenarios**:

1. **Given** una ventana aprobada, **When** comienza el corte, **Then** se bloquean nuevas escrituras,
   se obtiene backup final restaurable y se registran métricas finales del origen.
2. **Given** una migración finalizada, **When** falla cualquier control crítico, **Then** la aplicación
   no se abre y se activa el procedimiento de rollback dentro del tiempo acordado.
3. **Given** validaciones completas, **When** se abre el destino, **Then** se monitorizan errores y el
   origen permanece solo lectura durante el periodo aprobado.
4. **Given** escrituras nuevas en el destino y una decisión de rollback, **When** se ejecuta el
   retorno, **Then** esas escrituras se preservan o reconcilian según el runbook sin doble escritura
   no controlada.

### Edge Cases

- El origen mezcla datos dummy, reales y parcialmente válidos en las mismas tablas.
- Un identificador importado supera el valor actual de la secuencia del destino.
- Existen timestamps sin zona cuyo significado histórico depende de la zona del servidor anterior.
- Valores booleanos aparecen como números, texto y booleanos reales en una misma columna.
- Un JSON es sintácticamente válido pero no cumple la estructura esperada por la aplicación.
- Diferencias de mayúsculas, collation o espacios finales generan duplicados en restricciones únicas.
- Una restricción única permite múltiples `NULL` con semántica distinta a la asumida previamente.
- La conexión directa no está disponible desde la red de Render y debe usarse una conexión agrupada.
- El proveedor pausa el proyecto, agota conexiones o entra en modo solo lectura durante un ensayo.
- Dos instancias intentan ejecutar migraciones simultáneamente durante un despliegue.
- El servicio reinicia mientras un lote de transferencia está en curso.
- El backup se genera correctamente pero falla su restauración de prueba.
- Se detecta una credencial en el historial Git aunque ya no exista en la rama actual.
- El corte se abre a usuarios y luego aparece una diferencia monetaria crítica.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE producir un inventario trazable de tablas, columnas, tipos, defaults,
  nullability, PK, FK, restricciones, índices, vistas, objetos programables, columnas generadas,
  auto-incrementos, collations, JSON, enums, campos monetarios/temporales, pivotes, sesiones,
  auditoría, migraciones, seeders y factories.
- **FR-002**: Cada elemento inventariado DEBE indicar nombre actual, uso, equivalencia de destino,
  riesgo, acción, prueba y estado de migración.
- **FR-003**: El sistema DEBE auditar migraciones, consultas y scripts para detectar construcciones o
  semánticas dependientes del motor de origen.
- **FR-004**: La estrategia DEBE clasificar los datos y aplicar rutas separadas para datos dummy y
  datos reales o parcialmente válidos.
- **FR-005**: Todas las migraciones versionadas DEBEN ejecutar desde cero y revertirse cuando sea
  técnicamente viable sobre la base relacional de destino.
- **FR-006**: El destino DEBE conservar claves foráneas, restricciones únicas, índices justificados,
  transacciones, locks y semántica de eliminación requerida por el dominio actual.
- **FR-007**: Los identificadores numéricos existentes DEBEN preservarse cuando sea viable y sus
  secuencias DEBEN sincronizarse después de importaciones explícitas.
- **FR-008**: Los importes DEBEN conservar precisión y escala mediante aritmética decimal; no se
  permitirá punto flotante para almacenamiento, transformación o reconciliación monetaria.
- **FR-009**: Los booleanos, JSON, texto y fechas DEBEN validarse y transformarse de manera explícita,
  sin pérdida de caracteres ni desplazamiento histórico de horas.
- **FR-010**: Las fechas de presentación DEBEN continuar usando `America/Lima`, con una convención
  documentada y consistente de almacenamiento temporal.
- **FR-011**: Las consultas de autenticación, sesiones, operaciones, auditoría, cierres, dashboards,
  agregaciones, rankings y filtros DEBEN conservar resultados y rendimiento razonable en el destino.
- **FR-012**: Toda consulta específica del destino DEBE quedar aislada, documentada y cubierta por
  pruebas; se preferirán mecanismos portables cuando satisfagan el comportamiento requerido.
- **FR-013**: Si existen datos reales, la transferencia DEBE soportar lotes, dry run, verificación,
  reanudación, selección controlada, progreso sanitizado, idempotencia, detención segura y
  transacciones acotadas.
- **FR-014**: Una corrupción crítica o violación de integridad DEBE detener el proceso afectado; no
  podrá ignorarse una FK ni registrarse secretos, hashes o tokens sensibles.
- **FR-015**: El orden de carga DEBE derivarse de las dependencias reales y no de una lista conceptual
  no verificada.
- **FR-016**: Antes y después de transferir, el sistema DEBE registrar por tabla conteos, rangos de ID,
  nulos críticos, unicidad, huérfanos, duplicados, totales monetarios y rangos de fechas aplicables.
- **FR-017**: La validación posterior DEBE comparar usuarios, agentes, asignaciones, operaciones,
  cierres, relaciones, IDs, caracteres, timestamps, secuencias, restricciones e índices.
- **FR-018**: Toda diferencia DEBE clasificarse como esperada, transformada, rechazada, error de
  migración o dato inválido previo.
- **FR-019**: La reconciliación monetaria DEBE comparar totales generales y por agente, operador,
  fecha, estado y dirección monetaria, y conservar un reporte como evidencia.
- **FR-020**: Los usuarios, roles, estados y hashes de contraseña DEBEN conservarse; las sesiones,
  refresh tokens, nonces y tokens temporales previos DEBEN quedar revocados durante el corte.
- **FR-021**: El corte DEBE registrar un evento de seguridad y obligar a todos los usuarios a iniciar
  sesión nuevamente.
- **FR-022**: La configuración DEBE usar variables de entorno separadas y SSL obligatorio, sin
  credenciales en URLs cuando existan variables equivalentes.
- **FR-023**: La documentación solo DEBE usar `[SUPABASE_HOST]`, `[SUPABASE_PORT]`,
  `[SUPABASE_DATABASE]`, `[SUPABASE_USERNAME]`, `[SUPABASE_PASSWORD]` y
  `[SUPABASE_SESSION_POOLER_HOST]` como placeholders de conexión.
- **FR-024**: Antes del corte DEBE revisarse el repositorio y su historial para detectar credenciales;
  toda credencial expuesta DEBE rotarse antes de uso.
- **FR-025**: La aplicación DEBE usar un rol permanente de mínimo privilegio y un contexto separado
  para migraciones, o documentar la imposibilidad y sus medidas compensatorias.
- **FR-026**: Los datos de aplicación NO DEBEN quedar expuestos públicamente por REST, GraphQL ni
  interfaces del proveedor, y no habrá claves de servicio en frontend.
- **FR-027**: El método de conexión productivo DEBE seleccionarse después de una prueba real desde el
  entorno de aplicación; una conexión transaccional agrupada requerirá justificación y pruebas
  adicionales de prepared statements, transacciones, migraciones y locks.
- **FR-028**: El despliegue DEBE construirse reproduciblemente mediante una imagen Docker de
  producción, incluir el controlador PostgreSQL requerido, usar un servidor productivo, compilar
  assets, desactivar debug y emitir logs a salida estándar.
- **FR-029**: El entorno de aplicación NO DEBE usar su filesystem efímero para sesiones persistentes,
  backups, caché crítica, datos de usuario ni exportaciones permanentes.
- **FR-030**: Las variables secretas, la clave estable de aplicación, cookies seguras, proxy confiable
  y URL pública DEBEN configurarse externamente y validarse bajo HTTPS real.
- **FR-031**: El despliegue DEBE impedir migraciones concurrentes mediante un procedimiento único o
  lock verificable.
- **FR-032**: La salud DEBE distinguir liveness y readiness, comprobar conectividad de datos sin
  modificar información y no revelar detalles internos.
- **FR-033**: La observabilidad DEBE cubrir errores y tiempos de conexión, agotamiento de conexiones,
  consultas lentas, deadlocks, migraciones, errores 500, login, refresh, cierres y capacidad, con
  sanitización de secretos y datos sensibles.
- **FR-034**: Deben documentarse límites, pausas, arranques en frío, filesystem, conexiones, capacidad
  y recuperación del plan elegido, sin prometer disponibilidad no garantizada.
- **FR-035**: Deben medirse latencia y tiempos de login, registro, dashboards y cierre antes de elegir
  definitivamente regiones y método de conexión.
- **FR-036**: Debe existir un backup externo del origen y del destino con checksum, retención,
  cifrado, responsable y restauración ensayada fuera del repositorio y del filesystem efímero.
- **FR-037**: El ensayo completo DEBE crear el destino desde cero, migrar o sembrar según la
  clasificación, validar, ejecutar pruebas y smoke tests, medir duración y probar rollback.
- **FR-038**: El corte DEBE incluir mantenimiento, bloqueo de escrituras, backup final, métricas,
  transferencia, validación, configuración, smoke tests, apertura y monitorización.
- **FR-039**: El rollback DEBE definir disparadores, responsable, tiempo de decisión, restauración de
  configuración y código, tratamiento de nuevas escrituras y comunicación a usuarios.
- **FR-040**: La suite completa DEBE ejecutarse contra una instancia real del motor de destino y
  cubrir esquema limpio, seeders autorizados, rollback, FK, únicos, transacciones, locks, upserts,
  JSON, fechas, decimales, búsquedas, paginación, dashboards, cierres y sesiones.
- **FR-041**: Los índices del destino DEBEN justificarse por restricciones o consultas reales y no
  copiarse automáticamente desde el origen.
- **FR-042**: Los seeders DEBEN clasificarse por uso y producción NO DEBE ejecutar datos dummy ni
  crear un administrador con contraseña hardcodeada.
- **FR-043**: Ningún cambio destructivo DEBE ejecutarse sin backup, revisión, confirmación, prueba y
  rollback documentado.
- **FR-044**: La entrega DEBE incluir inventario, matriz de compatibilidad, runbooks de migración,
  rollback y despliegue, configuración sin secretos, reportes de validación, reconciliación y pruebas,
  backups, rotación de credenciales y diagrama final.
- **FR-045**: Al cerrar la ventana acordada, PostgreSQL DEBE quedar como única base productiva y las
  credenciales temporales de migración DEBEN eliminarse o revocarse.

### Acceptance Baseline

- **AC-001**: Todas las migraciones ejecutan correctamente en PostgreSQL.
- **AC-002**: El esquema completo y los seeders autorizados ejecutan desde una base vacía.
- **AC-003**: No quedan dependencias de MySQL sin documentación y tratamiento aprobado.
- **AC-004**: Todas las claves foráneas requeridas existen y están activas.
- **AC-005**: Todos los índices requeridos existen y tienen justificación.
- **AC-006**: Todas las restricciones únicas conservan la semántica requerida.
- **AC-007**: Todos los campos monetarios usan representación decimal exacta.
- **AC-008**: Las secuencias generan identificadores no duplicados después de la importación.
- **AC-009**: Los timestamps conservan su significado histórico.
- **AC-010**: Todo JSON válido se conserva y el inválido queda reportado.
- **AC-011**: Todas las tablas con datos válidos se migran según su clasificación.
- **AC-012**: Los conteos de origen y destino coinciden o tienen diferencia aprobada.
- **AC-013**: Los identificadores coinciden.
- **AC-014**: Los usuarios coinciden.
- **AC-015**: Las operaciones coinciden.
- **AC-016**: Los agentes coinciden.
- **AC-017**: Las asignaciones coinciden.
- **AC-018**: Los cierres coinciden.
- **AC-019**: Los totales monetarios coinciden exactamente.
- **AC-020**: No existen registros huérfanos.
- **AC-021**: No existen duplicados no previstos.
- **AC-022**: No existe pérdida de caracteres.
- **AC-023**: No existe desplazamiento horario.
- **AC-024**: Las sesiones y refresh tokens anteriores quedan revocados.
- **AC-025**: Se conserva evidencia de reconciliación.
- **AC-026**: El login funciona con la persistencia de destino.
- **AC-027**: La emisión y validación de JWT funciona sin cambio de comportamiento.
- **AC-028**: La rotación y uso de refresh tokens funciona.
- **AC-029**: El modal y flujo de expiración funciona.
- **AC-030**: La autorización por rol, ownership y asignación funciona.
- **AC-031**: El registro de operaciones funciona.
- **AC-032**: La anulación de operaciones funciona.
- **AC-033**: Los dashboards funcionan y conservan resultados.
- **AC-034**: Los filtros funcionan y conservan resultados.
- **AC-035**: El cierre diario funciona.
- **AC-036**: La auditoría funciona y conserva historia.
- **AC-037**: Las transacciones y locks críticos funcionan.
- **AC-038**: No aparecen errores por sintaxis dependiente del motor anterior.
- **AC-039**: La suite completa pasa contra PostgreSQL real.
- **AC-040**: Toda conexión externa de datos usa SSL.
- **AC-041**: No existen credenciales activas en Git.
- **AC-042**: Existe rol limitado de aplicación o excepción aprobada con medidas compensatorias.
- **AC-043**: Las tablas de aplicación no están expuestas públicamente por Data API.
- **AC-044**: No existe clave de servicio en frontend.
- **AC-045**: La aplicación no utiliza Supabase Auth.
- **AC-046**: El método de conexión fue probado desde Render.
- **AC-047**: Los límites, pausas y recuperación del proveedor están documentados.
- **AC-048**: Existe una estrategia de backup externo restaurable.
- **AC-049**: La aplicación se construye mediante Docker.
- **AC-050**: La imagen incluye el controlador PostgreSQL requerido.
- **AC-051**: La aplicación inicia correctamente con configuración externa.
- **AC-052**: Liveness y readiness responden según el estado real.
- **AC-053**: Debug está desactivado en producción.
- **AC-054**: La clave de aplicación permanece estable entre despliegues.
- **AC-055**: Los logs se envían a salida estándar y están sanitizados.
- **AC-056**: La aplicación no depende del filesystem local para persistencia.
- **AC-057**: Las cookies funcionan bajo HTTPS y proxy real.
- **AC-058**: Login, refresh y logout funcionan bajo el dominio de Render.
- **AC-059**: Los assets compilados cargan correctamente.
- **AC-060**: El arranque en frío está documentado y probado con el ciclo de sesión.
- **AC-061**: Existe al menos un ensayo completo registrado.
- **AC-062**: Existe un backup restaurable verificado.
- **AC-063**: Existe un rollback probado.
- **AC-064**: Existe una ventana de corte aprobada.
- **AC-065**: No existen escrituras simultáneas no controladas.
- **AC-066**: La validación termina antes de abrir la aplicación.
- **AC-067**: El origen queda solo lectura durante la ventana acordada.
- **AC-068**: El resultado final queda documentado.
- **AC-069**: Las credenciales temporales se eliminan o revocan.
- **AC-070**: PostgreSQL queda como única base productiva al cerrar la migración.

### Key Entities *(include if feature involves data)*

- **Inventario de compatibilidad**: catálogo trazable de objetos del origen, usos, equivalencias,
  riesgos, acciones, pruebas y estado.
- **Clasificación de datos**: decisión documentada sobre naturaleza, sensibilidad, validez y destino
  de cada conjunto de datos.
- **Lote de migración**: unidad reanudable de transferencia con alcance, progreso, resultado y errores
  sanitizados.
- **Registro de reconciliación**: evidencia comparativa de conteos, relaciones, importes, caracteres,
  fechas y secuencias entre origen y destino.
- **Ventana de corte**: periodo aprobado con responsables, estados, hitos, criterios de apertura y
  disparadores de rollback.
- **Evidencia de backup/restauración**: referencia externa, checksum, fecha y resultado de una
  restauración ensayada, sin contener el backup ni secretos.
- **Configuración de despliegue**: nombres de variables, límites, región, salud y políticas
  operativas, siempre sin valores secretos.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: `ADMINISTRADOR_PROPIETARIO`, `OPERADOR`, ownership y asignaciones de agente se
  conservarán sin cambios y se probarán con casos positivos, negativos y parámetros manipulados.
- **Data minimization**: Solo se migrarán datos válidos necesarios. No se incorporarán nuevos datos
  bancarios o sensibles; hashes, tokens, cookies y credenciales no aparecerán en evidencias.
- **Auditability**: Se preservarán operaciones y auditoría histórica. El corte, revocación masiva,
  diferencias rechazadas y decisiones de apertura/rollback dejarán evidencia con actor, fecha,
  acción, resultado y motivo, sin secretos.
- **Time and money**: Los importes mantendrán precisión y significado; monto bruto no se presentará
  como utilidad. Los periodos y `business_date` conservarán límites y la presentación seguirá en
  `America/Lima` sin desplazar timestamps históricos.
- **Session security**: Se mantienen expiración, aviso, renovación explícita, rotación, revocación y
  logout existentes. El corte revoca todas las sesiones y refresh tokens previos.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: La arquitectura seguirá siendo reemplazable a nivel de proveedor de base de
  datos y mantendrá la interfaz responsive renderizada por servidor sin cambios visuales.
- **Performance**: Se medirán flujos críticos, se revisarán índices y planes según consultas reales,
  se mantendrán paginación, agregación en servidor y prevención de N+1.
- **Observability and recovery**: Habrá liveness/readiness, logs sanitizados, métricas operativas,
  backups externos restaurables, ensayo y rollback probado.
- **System boundary**: El cambio no convierte al sistema en integración bancaria, fuente contable ni
  confirmación oficial de procesamiento bancario.
- **Secret safety**: Ninguna credencial real puede existir en especificaciones, planes, tareas,
  documentación, código, migraciones, seeders, artefactos de despliegue, CI, capturas, logs, commits,
  pruebas o fixtures.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de los objetos de persistencia y dependencias de código queda inventariado con
  equivalencia, riesgo, acción, prueba y estado antes del primer ensayo.
- **SC-002**: El 100% de las migraciones autorizadas puede crear el esquema desde cero y toda
  migración declarada reversible completa su reversión en el entorno de destino.
- **SC-003**: Para datos reales válidos, el 100% de conteos, identificadores y relaciones críticas
  coincide entre origen y destino, sin huérfanos ni duplicados no previstos.
- **SC-004**: Todas las reconciliaciones monetarias coinciden exactamente a la escala definida, tanto
  en total como por agente, operador, fecha, estado y dirección monetaria.
- **SC-005**: El 100% de los timestamps muestreados y todos los límites de fecha críticos conservan su
  significado histórico y presentación esperada.
- **SC-006**: Los 70 criterios de aceptación declarados por el solicitante tienen evidencia de pase o
  una excepción aprobada antes del corte definitivo.
- **SC-007**: Login, refresh, logout, operaciones, anulaciones, dashboards, filtros, cierres y
  auditoría completan el flujo de demostración sin errores atribuibles al motor anterior.
- **SC-008**: El 100% de sesiones y refresh tokens anteriores al corte resulta inválido, mientras los
  usuarios pueden volver a autenticarse con sus contraseñas existentes.
- **SC-009**: Ningún escaneo del árbol versionado ni del historial detecta credenciales activas; toda
  coincidencia real previa queda rotada antes del despliegue.
- **SC-010**: El servicio supera health checks, HTTPS, cookies, assets y flujos de sesión bajo el
  dominio de destino, incluyendo un arranque en frío ensayado.
- **SC-011**: Un ensayo completo de corte y rollback termina dentro de la ventana aprobada y recupera
  el servicio sin pérdida ni doble escritura no controlada.
- **SC-012**: Backup y restauración de prueba se completan con checksum verificado antes de migrar
  datos reales.
- **SC-013**: El 100% de las pruebas obligatorias pasa contra el motor relacional productivo previsto;
  ningún pase depende exclusivamente de una base simplificada o mocks.
- **SC-014**: Durante la observación posterior al corte no aparecen errores críticos de conexión,
  integridad, autorización, sesiones, importes o cierres antes de declarar la migración terminada.
- **SC-015**: El flujo de demostración de 20 pasos puede ejecutarse de principio a fin con evidencia
  reproducible y sin exponer secretos.

## Assumptions

- La duración exacta de mantenimiento, periodo de solo lectura, RTO, RPO, frecuencia de backup y
  periodo de observación se definirán y aprobarán durante la planificación usando volumen y ensayo.
- La base actual contiene exclusivamente datos dummy (usuarios Faker, admin hardcodeado, agentes
  ficticios, cero operaciones reales); se aplicará la ruta de esquema limpio con seeders
  controlados.
- La estrategia de transferencia se elegirá después del inventario y medición de volumen; ninguna
  herramienta concreta queda aprobada por esta especificación.
- El método de conexión y región definitivos se elegirán tras pruebas reales de conectividad y
  latencia desde el entorno de despliegue; tentativamente Supabase en sa-east-1 y Render en São Paulo
  para minimizar latencia.
- Los planes de proveedor pueden cambiar; Render Free se usará inicialmente con tolerancia a
  suspensión por inactividad y arranques en frío, migrando a plan pago cuando existan operaciones
  reales. Los límites se documentarán según condiciones vigentes al desplegar.
- El origen permanecerá disponible y restaurable durante la ventana de rollback acordada.
- No existen archivos permanentes de usuario requeridos por el alcance actual; drivers de sesión y
  caché usarán `database` para evitar dependencia del filesystem efímero de Render.

## Dependencies

- Acceso autorizado de solo lectura a esquema, datos y metadatos del origen.
- Una copia segura y restaurable del origen para ensayos.
- Un proyecto de PostgreSQL administrado configurado sin exposición pública de datos de aplicación;
  Data API será deshabilitada.
- Un entorno de despliegue de ensayo con gestión de secretos, HTTPS y conectividad hacia la base.
- Aprobación del responsable de negocio para clasificación de datos, ventana de mantenimiento,
  criterios de rollback y periodo de observación.
- Disponibilidad de herramientas de backup y restauración compatibles con ambos extremos.

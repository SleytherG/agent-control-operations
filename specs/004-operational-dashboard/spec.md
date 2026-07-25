> **Superseded by 008**: Esta especificacion fue superada por [`008-simplify-agent-operations`](../008-simplify-agent-operations/spec.md).

# Feature Specification: Dashboards Operacionales

**Feature Branch**: No creada

**Created**: 2026-07-22

**Status**: Draft

**Input**: Dashboards con métricas agregadas, filtros, gráficos y vista comparativa de operadores. Operador ve solo sus datos; administrador ve toda la organización.

## Problem & Actors *(mandatory)*

**Problem**: Con las operaciones ya registradas digitalmente, el cliente necesita visualizar métricas agregadas para entender el volumen operado, la distribución por tipo y la evolución en el tiempo, sin descargar todos los registros al navegador ni realizar cálculos manuales. El operador debe ver solo sus números; el administrador debe ver la red completa y comparar operadores.

**Actors**:

- **OPERADOR**: visualiza métricas exclusivamente de sus propias operaciones activas: cantidad, monto bruto, entradas, salidas, movimiento neto, distribución por tipo y evolución temporal.
- **ADMINISTRADOR_PROPIETARIO**: visualiza métricas de toda la organización con filtros multidimensionales (fecha, periodo, geografía, tienda, banco, agente, operador, tipo, estado). Puede comparar operadores mediante gráfico y ranking.
- **Sistema**: impone autorización de servidor, agrega en base de datos, muestra estado vacío cuando no hay datos y nunca presenta monto bruto como ingreso o ganancia.

**Change Classification**: Nueva capacidad funcional

## Scope *(mandatory)*

### In Scope

- Dashboard del operador con tarjetas de resumen, gráfico de distribución por tipo y evolución temporal.
- Dashboard del administrador con los mismos elementos más filtros avanzados.
- Vista comparativa de operadores mediante selector/ranking con gráfico y tabla.
- Filtros consistentes que afectan tarjetas, gráficos y tablas simultáneamente.
- Periodos: día, semana, mes, trimestre, semestre, año con inicio y fin definidos en `America/Lima`.
- Filtros administrativos: rango de fechas, región, provincia, distrito, tienda, banco, agente, operador, tipo y estado.
- Exclusión de operaciones anuladas por defecto; administrador puede incluirlas.
- Estado vacío explícito cuando no hay datos.
- Paginación en listados y tablas.
- Agregaciones calculadas en el servidor mediante consultas SQL.
- Terminología correcta: "monto bruto operado", nunca "ingreso", "utilidad" o "ganancia".

### Out of Scope

- Predicciones o proyecciones.
- Inteligencia artificial o machine learning.
- Exportación avanzada (CSV, PDF, Excel).
- Comparación contra información del banco.
- Cálculo de comisiones o ganancias.
- Mapas o geolocalización.
- Alertas o notificaciones.

### Business Rules

- **BR-001**: El operador solo ve métricas de sus propias operaciones. El servidor impone el filtro `user_id` antes de cualquier agregación.
- **BR-002**: Las operaciones anuladas se excluyen de todas las métricas por defecto. Solo el administrador puede incluirlas mediante un filtro explícito.
- **BR-003**: Todas las agregaciones (cantidad, sumas, promedios) se calculan en la base de datos mediante SQL. Nunca se cargan colecciones completas de operaciones en memoria.
- **BR-004**: Los periodos diario, semanal, mensual, trimestral, semestral y anual tienen reglas de inicio y final explícitas en zona horaria `America/Lima`. La conversión a UTC para filtrado es responsabilidad del servidor.
- **BR-005**: Las métricas mostradas son: cantidad de operaciones, monto bruto operado, total de entradas de efectivo, total de salidas de efectivo y movimiento neto (entradas - salidas).
- **BR-006**: El monto bruto operado no debe etiquetarse como ingreso, utilidad, ganancia ni revenue.
- **BR-007**: Cuando un filtro no produce resultados, debe mostrarse un estado vacío claro. No deben mostrarse gráficos con valor cero ni mensajes de error.
- **BR-008**: Todos los filtros activos deben reflejarse de manera consistente en tarjetas de resumen, gráficos y tablas.
- **BR-009**: La vista comparativa de operadores usa un selector o ranking; no requiere un gráfico independiente por cada operador y debe permanecer legible al aumentar la cantidad de trabajadores.
- **BR-010**: Los listados de operaciones en el dashboard deben estar paginados.
- **BR-011**: Las fechas y horas se muestran en `America/Lima`.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Dashboard del operador (Priority: P1)

Como `OPERADOR`, quiero ver un resumen de mis operaciones con tarjetas, gráfico de distribución y evolución temporal para entender mi desempeño diario.

**Why this priority**: Cada operador necesita visibilidad inmediata de su trabajo sin depender del administrador.

**Independent Test**: Registrar operaciones de un operador con diferentes tipos y verificar que el dashboard refleja solo sus métricas con agregaciones correctas.

**Acceptance Scenarios**:

1. **Given** un operador con operaciones activas, **When** accede a su dashboard, **Then** ve tarjetas con cantidad, monto bruto, entradas, salidas y movimiento neto calculados solo de sus operaciones.
2. **Given** un operador, **When** el dashboard carga, **Then** ve un gráfico de distribución por tipo de operación con porcentajes o valores.
3. **Given** un operador, **When** selecciona un periodo (día, semana, mes), **Then** la evolución temporal se ajusta al periodo seleccionado.
4. **Given** un operador sin operaciones, **When** accede al dashboard, **Then** ve un estado vacío claro sin errores ni gráficos engañosos.
5. **Given** un operador con operaciones anuladas, **When** accede al dashboard, **Then** las anuladas no se incluyen en las métricas.

---

### User Story 2 - Dashboard del administrador (Priority: P1)

Como `ADMINISTRADOR_PROPIETARIO`, quiero un dashboard con filtros multidimensionales y métricas de toda la red para supervisar la operación nacional.

**Why this priority**: El administrador necesita consolidar la información de todas las tiendas y agentes para tomar decisiones.

**Independent Test**: Aplicar filtros por tienda, banco, agente, fecha y verificar que tarjetas, gráficos y tabla se actualizan consistentemente.

**Acceptance Scenarios**:

1. **Given** un administrador, **When** accede al dashboard sin filtros, **Then** ve métricas de toda la organización con gráficos agregados.
2. **Given** un administrador, **When** aplica un filtro por tienda, **Then** tarjetas, gráfico de distribución, evolución temporal y tabla se actualizan con los datos de esa tienda.
3. **Given** un administrador, **When** selecciona el periodo "mes" y un mes específico, **Then** las métricas reflejan solo ese mes en `America/Lima`.
4. **Given** un administrador, **When** activa el filtro "incluir anuladas", **Then** las métricas incluyen operaciones anuladas en los cálculos.
5. **Given** un administrador, **When** accede con una combinación de filtros sin datos, **Then** ve estado vacío en todas las secciones.

---

### User Story 3 - Vista comparativa de operadores (Priority: P2)

Como `ADMINISTRADOR_PROPIETARIO`, quiero comparar operadores mediante un gráfico y una tabla con ranking para identificar diferencias de desempeño.

**Why this priority**: La comparación entre operadores detecta patrones y necesidades de capacitación, pero el dashboard básico ya aporta valor sin ella.

**Independent Test**: Seleccionar operadores en el comparador y verificar que el gráfico y la tabla muestran métricas correctas y ordenadas.

**Acceptance Scenarios**:

1. **Given** un administrador, **When** accede a la vista comparativa, **Then** ve un selector con los operadores activos y un gráfico de barras con sus métricas principales.
2. **Given** varios operadores seleccionados, **When** se aplica un filtro de fecha, **Then** el gráfico y la tabla se actualizan al periodo seleccionado.
3. **Given** un administrador, **When** ordena la tabla por monto bruto operado, **Then** los operadores se ordenan de mayor a menor o viceversa.
4. **Given** un solo operador en la organización, **When** accede al comparador, **Then** ve el gráfico con una sola barra sin errores.

### Edge Cases

- Periodo "semana" comenzando en domingo o lunes: definido como lunes 00:00 a domingo 23:59 en `America/Lima`.
- Periodo "mes" con cambio de año: el filtro incluye correctamente el mes independientemente del año.
- Dashboard sin operaciones en toda la organización: estado vacío global sin gráficos.
- Filtro por tipo de operación que no tiene operaciones en el periodo: tarjetas en cero, gráfico vacío, tabla sin filas.
- Combinación de filtro "incluir anuladas" con periodo: las anuladas dentro del periodo se suman.
- Operador comparativo con más de 50 trabajadores: el gráfico usa ranking top N con opción de scroll o paginación.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE mostrar un dashboard para el operador con tarjetas de cantidad de operaciones, monto bruto operado, entradas, salidas y movimiento neto, calculados exclusivamente de sus operaciones activas.
- **FR-002**: El sistema DEBE mostrar un gráfico de distribución por tipo de operación en el dashboard del operador.
- **FR-003**: El sistema DEBE mostrar evolución temporal (día, semana, mes) en el dashboard del operador.
- **FR-004**: El sistema DEBE mostrar un dashboard para el administrador con las mismas métricas y filtros adicionales.
- **FR-005**: El dashboard administrativo DEBE permitir filtrar por rango de fechas, periodo predefinido, región, provincia, distrito, tienda, banco, agente, operador, tipo de operación y estado.
- **FR-006**: Todos los filtros activos DEBEN afectar de manera consistente tarjetas de resumen, gráficos y tablas.
- **FR-007**: El sistema DEBE excluir operaciones anuladas de las métricas por defecto y DEBE permitir al administrador incluirlas mediante un filtro.
- **FR-008**: El sistema DEBE mostrar un estado vacío claro cuando no existan datos para los filtros aplicados, sin errores ni gráficos con valor cero.
- **FR-009**: El sistema DEBE calcular todas las agregaciones en el servidor mediante consultas SQL, sin cargar colecciones completas en memoria.
- **FR-010**: Los periodos DEBEN definirse en zona horaria `America/Lima` con inicio y final explícitos: día (00:00–23:59), semana (lunes 00:00 – domingo 23:59), mes (día 1 00:00 – último día 23:59), trimestre, semestre y año.
- **FR-011**: El monto bruto operado NO DEBE etiquetarse como ingreso, utilidad, ganancia ni revenue en ninguna vista.
- **FR-012**: El sistema DEBE proporcionar una vista comparativa de operadores con selector, gráfico de barras y tabla ordenable con ranking de métricas.
- **FR-013**: La vista comparativa DEBE permanecer legible al aumentar la cantidad de operadores, usando ranking top N o paginación.
- **FR-014**: Los listados y tablas del dashboard DEBEN estar paginados.
- **FR-015**: El servidor DEBE imponer el alcance de datos del operador (`user_id`) antes de cualquier agregación, independientemente de filtros manipulados.

### Key Entities *(include if feature involves data)*

Esta capacidad no introduce nuevas entidades. Opera sobre:

- **Operación** (de 003): agente, tipo, monto, moneda, dirección de efectivo, fecha efectiva, estado, usuario registrador.
- **Agente bancario** (de 002): tienda, banco, código.
- **Tienda** (de 002): distrito.
- **Usuario** (de 001): rol, estado.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: Operador ve solo métricas de sus operaciones; administrador ve todas. El servidor impone el filtro de `user_id` y rechaza filtros que excedan el alcance del rol.
- **Data minimization**: No se almacenan nuevos datos. Las consultas son de solo lectura. No se exponen datos de clientes.
- **Auditability**: No se generan registros de auditoría por consultas de dashboard (solo lectura).
- **Time and money**: Periodos en `America/Lima`. Montos en `DECIMAL(18,2)`. "Monto bruto operado" como etiqueta canónica.
- **Session security**: Reutiliza la sesión JWT existente.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Renderizado de servidor con Blade. Chart.js cargado de forma diferida solo en páginas de dashboard. Sin SPA. Gráficos responsive.
- **Performance**: Todas las agregaciones mediante SQL (`COUNT`, `SUM`, `SUM(CASE...)`). Índices existentes de 003 utilizados para filtros. Sin N+1.
- **Observability and recovery**: Sin nuevas tablas. Backups existentes cubren los datos fuente.
- **System boundary**: El dashboard no constituye sistema contable, no concilia con bancos y no calcula ganancias.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de las consultas de dashboard del operador contienen exclusivamente sus propias operaciones.
- **SC-002**: Las métricas agregadas (cantidad, montos, entradas, salidas) coinciden exactamente con los resultados de una consulta SQL directa para los mismos filtros en el 100% de los casos.
- **SC-003**: El dashboard administrativo con todos los filtros aplicados responde en 3 segundos o menos para un volumen de hasta 50,000 operaciones.
- **SC-004**: El 100% de las combinaciones de filtros sin resultados muestran estado vacío, no error ni gráfico engañoso.
- **SC-005**: El cambio de cualquier filtro actualiza tarjetas, gráficos y tablas de manera consistente en el 100% de los casos.
- **SC-006**: La vista comparativa de operadores soporta hasta 100 operadores sin degradación visual ni pérdida de legibilidad.
- **SC-007**: En ninguna vista, tooltip o etiqueta aparece la palabra "ingreso", "utilidad" o "ganancia" para referirse al monto bruto operado.

## Assumptions

- Los tipos de operación con dirección `ENTRADA` y `SALIDA` son los únicos que contribuyen a entradas y salidas de efectivo. `NEUTRA` y `POR_CONFIRMAR` se incluyen en monto bruto pero no en entradas ni salidas.
- Chart.js es la única dependencia frontend adicional; se carga de forma diferida solo en páginas de dashboard.
- El periodo por defecto al cargar el dashboard es el mes actual en `America/Lima`.
- La paginación de tablas usa 25 registros por página.
- El ranking de operadores muestra top 10 por defecto con opción de ver todos paginados.

## Dependencies

- Capacidad de autenticación (`001-auth-session`): identidad y autorización.
- Capacidad de estructura operacional (`002-operational-structure`): agentes, tiendas, bancos, geografía, asignaciones.
- Capacidad de registro de operaciones (`003-operations-registry`): operaciones, tipos, estados.

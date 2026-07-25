> **Superseded by 008**: Esta especificacion fue superada por [`008-simplify-agent-operations`](../008-simplify-agent-operations/spec.md).

# Feature Specification: Cierre Operativo Diario

**Feature Branch**: No creada

**Created**: 2026-07-22

**Status**: Draft

**Input**: Cierre diario por agente bancario que consolida operaciones activas, con confirmación autorizada, reapertura auditada y restricción de modificación posterior.

## Problem & Actors *(mandatory)*

**Problem**: Al final de cada día de operación, el cliente necesita consolidar las operaciones realizadas en cada agente bancario para obtener un resumen oficial del día. Este resumen debe ser confirmado por una persona autorizada, impedir modificaciones posteriores y permitir reapertura solo con trazabilidad. Sin este mecanismo, no hay un punto de cierre que separe días de negocio ni trazabilidad de quién validó el cierre.

**Actors**:

- **OPERADOR**: visualiza los cierres correspondientes a los agentes bancarios a los que está asignado y las operaciones que los componen.
- **ADMINISTRADOR_PROPIETARIO**: confirma cierres, visualiza todos los cierres de la organización y puede reabrir un cierre registrando un motivo.
- **Sistema**: consolida automáticamente las operaciones activas del agente en la fecha de negocio, impide más de un cierre activo por agente y fecha, advierte sobre operaciones POR_CONFIRMAR y audita toda confirmación y reapertura.

**Change Classification**: Nueva capacidad funcional

## Clarifications

### Session 2026-07-22

- Q: ¿Quién puede generar el cierre diario? → A: Ambos roles. Administrador genera cualquier cierre; operador genera cierres de sus agentes asignados. Solo el administrador confirma.

## Scope *(mandatory)*

### In Scope

- Generación de resumen de cierre para un agente bancario en una fecha de negocio específica.
- Consolidación automática de operaciones activas: cantidad, monto bruto, entradas, salidas, movimiento neto.
- Desglose por tipo de operación y por operador.
- Visualización de operaciones anuladas incluidas en el periodo.
- Confirmación del cierre por usuario autorizado (ADMINISTRADOR_PROPIETARIO).
- Bloqueo de modificación de operaciones incluidas después de la confirmación.
- Reapertura de cierre por administrador con motivo obligatorio y auditoría.
- Restricción de un solo cierre activo por agente y fecha de negocio.
- Advertencia cuando existen operaciones con dirección POR_CONFIRMAR; el movimiento neto no se presenta como valor definitivo en ese caso.
- Visualización del operador limitada a sus agentes asignados.

### Out of Scope

- Efectivo inicial del día.
- Conteo físico de efectivo al cierre.
- Diferencia, faltante o sobrante de caja.
- Cálculo de comisiones o ganancias.
- Rentabilidad del agente o tienda.
- Conciliación contra reportes del POS o banco.
- Transferencias automáticas al banco.
- Cierre automático programado (solo manual).

### Business Rules

- **BR-001**: Un cierre consolida las operaciones activas de un agente bancario en una fecha de negocio específica. Las operaciones anuladas antes del cierre no se incluyen. `ADMINISTRADOR_PROPIETARIO` puede generar cierres para cualquier agente; `OPERADOR` puede generar cierres solo para los agentes a los que está asignado.
- **BR-002**: No puede existir más de un cierre en estado ACTIVO para el mismo agente y fecha de negocio.
- **BR-003**: La confirmación del cierre solo puede realizarla un `ADMINISTRADOR_PROPIETARIO`. Al confirmar, se registra usuario, fecha y hora.
- **BR-004**: Después de la confirmación de un cierre, las operaciones asociadas no pueden ser modificadas ni anuladas.
- **BR-005**: El administrador puede reabrir un cierre confirmado registrando un motivo obligatorio. La reapertura debe quedar auditada con usuario, fecha, hora y motivo.
- **BR-006**: Un cierre reabierto permite nuevamente la modificación de las operaciones asociadas.
- **BR-007**: Si existen operaciones con tipo de dirección `POR_CONFIRMAR` asociadas al cierre, el sistema debe mostrar una advertencia visible y el movimiento neto debe etiquetarse como "no definitivo" o "pendiente de confirmación".
- **BR-008**: El operador solo puede visualizar cierres de agentes a los que está asignado.
- **BR-009**: Cada cambio de estado de un cierre (generado, confirmado, reabierto) genera registro de auditoría.
- **BR-010**: Las fechas de negocio se interpretan en zona horaria `America/Lima`. La fecha de negocio va de 00:00 a 23:59:59.999999 en esa zona.
- **BR-011**: El monto bruto operado no debe etiquetarse como ingreso, utilidad o ganancia.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Generar y visualizar un cierre diario (Priority: P1)

Como usuario autorizado, quiero generar un resumen de cierre para un agente en una fecha para consolidar las operaciones del día.

**Why this priority**: Sin capacidad de generar cierres, no hay separación entre días de negocio ni trazabilidad de cierre.

**Independent Test**: Generar cierre para un agente con operaciones en una fecha y verificar que el resumen contiene todas las métricas correctas.

**Acceptance Scenarios**:

1. **Given** un agente con operaciones activas en una fecha, **When** se solicita el resumen de cierre, **Then** se muestran cantidad, monto bruto, entradas, salidas y movimiento neto consolidados.
2. **Given** un agente con operaciones de múltiples tipos y operadores, **When** se genera el cierre, **Then** se muestran los totales desglosados por tipo de operación y por operador.
3. **Given** un agente con operaciones anuladas en la fecha, **When** se genera el cierre, **Then** las anuladas aparecen listadas pero no se incluyen en los totales.
4. **Given** un agente sin operaciones en la fecha, **When** se solicita el cierre, **Then** se muestra un resumen con todos los valores en cero.
5. **Given** un operador, **When** intenta ver el cierre de un agente no asignado, **Then** el servidor rechaza la solicitud.

---

### User Story 2 - Confirmar un cierre (Priority: P1)

Como `ADMINISTRADOR_PROPIETARIO`, quiero confirmar un cierre para oficializar el resumen del día y bloquear modificaciones posteriores.

**Why this priority**: La confirmación es el acto que da validez al cierre y protege las operaciones de cambios posteriores.

**Independent Test**: Confirmar un cierre como administrador y verificar que las operaciones asociadas no pueden modificarse ni anularse.

**Acceptance Scenarios**:

1. **Given** un cierre generado no confirmado, **When** un administrador lo confirma, **Then** el cierre pasa a estado CONFIRMADO, se registra el usuario y la fecha de confirmación, y se audita.
2. **Given** un cierre confirmado, **When** un operador intenta anular una de sus operaciones asociadas, **Then** el servidor rechaza la anulación porque la operación pertenece a un cierre confirmado.
3. **Given** un cierre no confirmado, **When** un operador intenta confirmarlo, **Then** el servidor rechaza la acción por falta de permisos.
4. **Given** un operador, **When** intenta registrar una nueva operación en un agente y fecha que ya tiene cierre confirmado, **Then** el servidor rechaza el registro.

---

### User Story 3 - Reabrir un cierre (Priority: P2)

Como `ADMINISTRADOR_PROPIETARIO`, quiero reabrir un cierre confirmado registrando un motivo para permitir correcciones excepcionales con trazabilidad.

**Why this priority**: La reapertura es un caso excepcional; el flujo normal es generar, confirmar y no modificar.

**Independent Test**: Reabrir un cierre confirmado con motivo, verificar que las operaciones vuelven a ser modificables y que la reapertura queda auditada.

**Acceptance Scenarios**:

1. **Given** un cierre confirmado, **When** un administrador lo reabre con un motivo, **Then** el cierre pasa a estado REABIERTO, se registra usuario, fecha, hora y motivo, y se audita.
2. **Given** un cierre reabierto, **When** un operador intenta anular una operación asociada, **Then** la anulación es permitida.
3. **Given** un cierre reabierto, **When** se vuelve a confirmar, **Then** el cierre pasa nuevamente a CONFIRMADO con nueva fecha de confirmación.
4. **Given** un administrador, **When** intenta reabrir un cierre sin proporcionar motivo, **Then** el sistema rechaza la acción.
5. **Given** un operador, **When** intenta reabrir un cierre, **Then** el servidor rechaza la acción.

---

### User Story 4 - Advertencia por operaciones POR_CONFIRMAR (Priority: P2)

Como usuario que visualiza un cierre, quiero ser advertido cuando existen operaciones con dirección POR_CONFIRMAR para no interpretar el movimiento neto como un valor definitivo.

**Why this priority**: La advertencia es importante para la interpretación correcta, pero el cierre puede usarse sin ella si no hay operaciones POR_CONFIRMAR.

**Independent Test**: Generar cierre con operaciones POR_CONFIRMAR y verificar advertencia y etiqueta "no definitivo".

**Acceptance Scenarios**:

1. **Given** un cierre que incluye operaciones POR_CONFIRMAR, **When** se visualiza, **Then** se muestra una advertencia visible y el movimiento neto se etiqueta como "Pendiente de confirmación".
2. **Given** un cierre sin operaciones POR_CONFIRMAR, **When** se visualiza, **Then** no se muestra advertencia y el movimiento neto se presenta normalmente.

### Edge Cases

- Intentar generar un segundo cierre activo para el mismo agente y fecha: el sistema rechaza con indicación de que ya existe un cierre para esa combinación.
- Confirmar un cierre que ya está confirmado: operación idempotente, no genera error pero tampoco duplica la confirmación.
- Reabrir un cierre que ya está reabierto: operación idempotente, no cambia el estado.
- Operación registrada después de generar el cierre pero antes de confirmarlo: no se incluye automáticamente; el administrador debe regenerar el resumen antes de confirmar.
- Cambio de día en zona horaria America/Lima: la fecha de negocio se determina por la fecha local del agente, no por UTC.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE permitir generar un resumen de cierre para un agente bancario en una fecha de negocio. `ADMINISTRADOR_PROPIETARIO` puede generar para cualquier agente; `OPERADOR` solo para agentes a los que está asignado.
- **FR-002**: El resumen DEBE mostrar cantidad de operaciones, monto bruto operado, entradas de efectivo, salidas de efectivo y movimiento neto.
- **FR-003**: El resumen DEBE incluir desglose por tipo de operación y por operador.
- **FR-004**: El sistema DEBE mostrar las operaciones anuladas del periodo de forma separada, sin incluirlas en los totales.
- **FR-005**: El sistema DEBE impedir más de un cierre en estado ACTIVO para el mismo agente y fecha de negocio.
- **FR-006**: Solo `ADMINISTRADOR_PROPIETARIO` DEBE poder confirmar un cierre. La confirmación DEBE registrar usuario, fecha y hora.
- **FR-007**: Después de la confirmación, el sistema DEBE rechazar cualquier modificación o anulación de las operaciones asociadas al cierre.
- **FR-008**: Después de la confirmación, el sistema DEBE rechazar el registro de nuevas operaciones en el agente y fecha del cierre.
- **FR-009**: `ADMINISTRADOR_PROPIETARIO` DEBE poder reabrir un cierre confirmado registrando un motivo obligatorio. La reapertura DEBE auditarse.
- **FR-010**: Un cierre reabierto DEBE permitir nuevamente la modificación de operaciones asociadas y el registro de nuevas operaciones en ese agente y fecha.
- **FR-011**: Si el cierre incluye operaciones con tipo `POR_CONFIRMAR`, el sistema DEBE mostrar una advertencia y etiquetar el movimiento neto como no definitivo.
- **FR-012**: El operador DEBE poder visualizar únicamente los cierres de los agentes a los que está asignado.
- **FR-013**: Cada cambio de estado del cierre DEBE generar registro de auditoría con usuario, fecha, acción, entidad y valores anteriores y posteriores.
- **FR-014**: La fecha de negocio DEBE interpretarse en `America/Lima` (00:00 a 23:59:59.999999).
- **FR-015**: El monto bruto operado NO DEBE etiquetarse como ingreso, utilidad o ganancia.

### Key Entities *(include if feature involves data)*

- **Cierre diario**: agente bancario, fecha de negocio, estado (ACTIVO, CONFIRMADO, REABIERTO), usuario que confirmó, fecha de confirmación, usuario que reabrió, fecha de reapertura, motivo de reapertura, métricas consolidadas (cantidad, monto bruto, entradas, salidas, neto).
- **Operación de cierre**: relación entre un cierre y una operación. Una operación solo puede pertenecer a un cierre.
- **Registro de auditoría**: evidencia de generación, confirmación y reapertura del cierre.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: Administrador genera, confirma y reabre cualquier cierre. Operador solo visualiza cierres de sus agentes asignados. Toda mutación de estado requiere autorización de servidor.
- **Data minimization**: Solo se consolidan métricas agregadas y referencias a operaciones. Sin datos de cliente, tarjetas ni cuentas.
- **Auditability**: Generación, confirmación y reapertura generan auditoría con actor, fecha, entidad, before/after y motivo cuando corresponda. Sin eliminación física de cierres.
- **Time and money**: Fecha de negocio en `America/Lima`. Montos `DECIMAL(18,2)`. "Monto bruto operado" como etiqueta canónica.
- **Session security**: Reutiliza la sesión JWT existente.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Renderizado de servidor con Blade. Sin SPA. Sin dependencias nuevas.
- **Performance**: Consultas de consolidación en SQL. Sin cargar colecciones completas.
- **Observability and recovery**: Auditoría en cada cambio de estado. Migraciones reversibles. Backups incluyen cierres.
- **System boundary**: El cierre es un resumen operativo interno. No constituye conciliación bancaria ni contable.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El 100% de los intentos de crear un segundo cierre activo para el mismo agente y fecha son rechazados.
- **SC-002**: El 100% de los intentos de modificar o anular una operación perteneciente a un cierre confirmado son rechazados.
- **SC-003**: El 100% de las confirmaciones y reaperturas generan registro de auditoría con todos los metadatos requeridos.
- **SC-004**: El 100% de los intentos de confirmar o reabrir un cierre por parte de un operador son rechazados.
- **SC-005**: El 100% de los cierres con operaciones POR_CONFIRMAR muestran la advertencia y etiquetan el movimiento neto como no definitivo.
- **SC-006**: Un cierre se genera y muestra en menos de 3 segundos para un agente con hasta 500 operaciones en el día.

## Assumptions

- La fecha de negocio se ingresa manualmente o se toma por defecto como la fecha actual en `America/Lima`.
- Las operaciones anuladas antes de generar el cierre no se asocian. Las anuladas después aparecen en el cierre como anuladas.
- Un cierre reabierto mantiene el historial completo de cambios de estado.
- El administrador puede regenerar el resumen de un cierre no confirmado para reflejar operaciones registradas después de la generación inicial.

## Dependencies

- Capacidad de autenticación (`001-auth-session`): identidad, roles y middleware.
- Capacidad de estructura operacional (`002-operational-structure`): agentes bancarios, asignaciones operador-agente.
- Capacidad de registro de operaciones (`003-operations-registry`): operaciones, tipos, estados, anulaciones.

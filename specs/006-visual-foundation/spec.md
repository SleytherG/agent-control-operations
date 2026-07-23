# Feature Specification: Fundamentos Visuales y Sistema de Componentes

**Feature Branch**: No creada

**Created**: 2026-07-22

**Status**: Draft

**Input**: Construcción del sistema visual base con componentes reutilizables Blade, CSS propio, diseño responsive y referencias Stitch para las 7 pantallas principales de la aplicación.

## Problem & Actors *(mandatory)*

**Problem**: Las pantallas actuales de la aplicación carecen de una identidad visual común, componentes reutilizables y comportamiento responsive consistente. Cada vista existente se construyó de forma independiente, duplicando estilos y sin un sistema de diseño que garantice accesibilidad, jerarquía visual y bajo acoplamiento entre presentación y lógica de negocio.

**Actors**:

- **Equipo de desarrollo**: necesita un sistema de componentes reutilizables para no rediseñar cada pantalla desde cero.
- **OPERADOR**: interactúa con pantallas de registro, consulta y dashboard. Necesita baja carga cognitiva y acceso rápido a funciones esenciales.
- **ADMINISTRADOR_PROPIETARIO**: interactúa con dashboards, filtros avanzados y pantallas de supervisión. Necesita densidad de información adecuada.
- **Usuarios con distintas capacidades**: necesitan navegación por teclado, contraste suficiente, etiquetas adecuadas y áreas táctiles suficientes.

**Change Classification**: Nueva capacidad funcional (infraestructura visual)

## Clarifications

### Session 2026-07-22

- Q: ¿Qué nivel de fidelidad al diseño Stitch se espera? → A: Intención visual. Mantener jerarquía, layout y proporciones de Stitch; traducir colores a tokens semánticos y usar system-ui en vez de Google Fonts.
- Q: ¿La maquetación debe ser demo independiente o reemplazar las vistas existentes? → A: Reemplazar directamente las vistas existentes con la nueva maquetación.
- Q: ¿Comportamiento de tablas en móvil? → A: Scroll horizontal para tablas de datos; transformar a tarjetas solo tablas simples con ≤3 columnas.
- Q: ¿Presentación de filtros en móvil? → A: Panel lateral (off-canvas) que se desliza, con botón "Filtros" visible.

## Scope *(mandatory)*

### In Scope

- Sistema de diseño con colores semánticos, tipografía, espaciado, sombras, radios y bordes.
- Componentes Blade reutilizables (botones, inputs, tablas, tarjetas, modales, badges, alerts, etc.).
- Layout autenticado con sidebar, header, indicador de sesión, selector de agente y navegación por rol.
- Maquetación responsive de 7 pantallas basadas en Stitch: login, aviso de expiración, dashboard operador, registro de operación, historial, dashboard admin, cierre diario.
- Datos de demostración aislados de las vistas.
- Estilos para estados: normal, hover, focus, active, disabled, loading, error, success, empty.
- Accesibilidad WCAG 2.2 AA: contraste, foco, teclado, etiquetas, áreas táctiles.
- Documentación de desviaciones respecto a Stitch cuando se mejore accesibilidad o consistencia.

### Out of Scope

- Autenticación JWT real, persistencia, CRUD, cálculos financieros.
- Autorización real por roles implementada en servidor.
- Lógica de negocio, validación de formularios real.
- Datos de clientes, logos de bancos, información bancaria sensible.
- SPA, animaciones complejas, modo offline, aplicación móvil nativa.
- Comisiones, ganancias, conciliación bancaria.

### Business Rules

- **BR-001**: Los nombres de colores deben expresar propósito semántico (ej. `--color-success-text`), no solo apariencia (`--color-green`). Los tokens de Stitch se traducen a variables CSS semánticas; los nombres originales de Stitch se conservan como referencia en la documentación de traducción.
- **BR-002**: La entrada y salida de efectivo deben distinguirse mediante texto (+$ / -$), iconografía y señal visual. No depender únicamente del color.
- **BR-003**: El monto bruto operado no debe etiquetarse como ingreso, utilidad o ganancia.
- **BR-004**: Las pantallas deben funcionar en 1440 px, 1280 px, 768 px y 375 px sin desplazamiento horizontal global.
- **BR-005**: Las tablas de datos (operaciones, historial, cierres) usan scroll horizontal en móvil. Las tablas simples con ≤3 columnas pueden transformarse en tarjetas cuando mejore la comprensión.
- **BR-006**: Las funciones esenciales no deben desaparecer en móvil. Los filtros avanzados se presentan mediante un panel lateral (off-canvas) accesible con botón "Filtros" visible. Los filtros simples (búsqueda, fecha) permanecen visibles en la barra superior.
- **BR-007**: Toda desviación relevante del diseño Stitch debe documentar: elemento original, cambio, justificación, beneficio y pantallas afectadas.
- **BR-008**: El código HTML exportado por Stitch no debe copiarse directamente. Debe traducirse a componentes Blade semánticos y reutilizables.
- **BR-009**: No debe existir código HTML duplicado de forma innecesaria entre pantallas.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Sistema visual reutilizable (Priority: P1)

Como equipo de desarrollo, necesito un sistema visual común para mantener identidad, jerarquía y accesibilidad consistentes en todas las pantallas.

**Why this priority**: Sin sistema visual base, cada pantalla duplicaría estilos y no habría garantía de accesibilidad ni consistencia.

**Independent Test**: Verificar que los tokens de diseño están definidos en un solo archivo CSS y que los componentes Blade existen en un directorio compartido.

**Acceptance Scenarios**:

1. Existe un archivo de tokens CSS con colores semánticos, tipografía, espaciado, sombras y breakpoints.
2. Los componentes Blade están en `resources/views/components/` y son reutilizables mediante `<x-button>`, `<x-table>`, etc.
3. Los estados de cada componente (hover, focus, active, disabled, loading) están definidos y son verificables.
4. Las entradas de efectivo se distinguen de las salidas mediante texto (+$1,000.00 vs -$500.00), icono y color.
5. Los estados de operación (ACTIVA, ANULADA) y cierre (ACTIVO, CONFIRMADO, REABIERTO) tienen representación visual consistente.

---

### User Story 2 - Layout autenticado (Priority: P1)

Como usuario autenticado, necesito navegación consistente, identificación de sesión y contexto operativo en todas las pantallas.

**Why this priority**: El layout es el contenedor de todas las pantallas; sin él no hay experiencia de navegación unificada.

**Independent Test**: Renderizar el layout con datos de demostración y verificar sidebar, header, session timer y variación por rol.

**Acceptance Scenarios**:

1. El layout muestra sidebar en escritorio con navegación colapsable y menú adaptado en tablet/móvil.
2. El header muestra identificación del usuario, rol y temporizador de sesión.
3. Para el operador, la navegación muestra Dashboard, Registrar Operación, Historial y Cerrar Sesión.
4. Para el administrador, la navegación añade Dashboard Admin, Estructura, Usuarios y Cierre Diario.
5. Existen breadcrumbs contextuales y área de contenido principal responsiva.

---

### User Story 3 - Pantallas de autenticación (Priority: P1)

Como usuario no autenticado, necesito pantallas de inicio de sesión y advertencia de expiración claras y con todos los estados posibles representados.

**Why this priority**: Son las primeras pantallas que ve cualquier usuario; deben inspirar confianza y cubrir todos los escenarios.

**Independent Test**: Renderizar login y modal de expiración con datos de demostración para todos los estados definidos.

**Acceptance Scenarios**:

1. Login muestra formulario con campos de usuario/contraseña, botón de ingreso y versión.
2. Estados representados: normal, credenciales incorrectas, usuario desactivado, demasiados intentos, error de red, envío en progreso.
3. Modal de expiración muestra temporizador regresivo, opciones Continuar y Cerrar Sesión.
4. Estados del modal: advertencia (30s), renovación en progreso, renovación exitosa, sesión expirada, sesión revocada.
5. Todo navegable por teclado con foco visible y etiquetas asociadas.

---

### User Story 4 - Pantallas del operador (Priority: P1)

Como operador, necesito un dashboard, formulario de registro y listado de operaciones con baja carga cognitiva.

**Why this priority**: El operador pasa la mayor parte del tiempo en estas pantallas; deben priorizar velocidad y claridad.

**Independent Test**: Renderizar las 3 pantallas con datos de demostración y verificar que todos los elementos visuales y estados están presentes.

**Acceptance Scenarios**:

1. Dashboard muestra tarjetas métricas (cantidad, monto bruto, entradas, salidas, neto), gráfico de distribución y lista de operaciones recientes.
2. Formulario de registro prioriza campo de monto y tipo de operación, con botón de envío prominente y prevención visual de doble envío.
3. Historial muestra filtros, tabla con paginación, badges de estado y filas para operaciones anuladas con estilo diferenciado.
4. Las 3 pantallas son navegables en móvil con las funciones esenciales accesibles.
5. Estados vacíos representados en dashboard, historial y formulario sin agente asignado.

---

### User Story 5 - Dashboard administrativo (Priority: P2)

Como administrador, necesito un dashboard de supervisión con filtros multidimensionales, gráficos y rankings.

**Why this priority**: El dashboard administrativo consolida información de toda la red; puede priorizarse después del operador.

**Independent Test**: Renderizar dashboard admin con datos de demostración y verificar filtros, gráficos y estados vacíos.

**Acceptance Scenarios**:

1. Dashboard muestra tarjetas métricas globales, gráfico de tendencia y ranking de operadores.
2. Panel de filtros incluye rango de fechas, región, provincia, distrito, tienda, banco, agente, operador, tipo y estado.
3. Estados representados: con datos, sin datos (vacío), cargando (skeleton), error de red.
4. Los datos de demostración están claramente identificados como ficticios.
5. Tabla de operadores comparativa con columnas ordenables y gráfico de barras.

---

### User Story 6 - Cierre operativo diario (Priority: P2)

Como usuario autorizado, necesito una pantalla de cierre diario que muestre el resumen de operaciones con estados y advertencias.

**Why this priority**: El cierre diario es la pantalla de consolidación; puede implementarse después del flujo principal del operador.

**Independent Test**: Renderizar cierre diario con datos de demostración para estados ACTIVO, CONFIRMADO y REABIERTO.

**Acceptance Scenarios**:

1. Cierre muestra fecha de negocio, tienda, banco y agente como contexto.
2. Tarjetas métricas: cantidad, monto bruto, entradas, salidas y movimiento neto.
3. Tablas de desglose por tipo de operación y por operador.
4. Lista de operaciones anuladas y advertencia POR_CONFIRMAR cuando aplica.
5. Botones de Confirmar (si ACTIVO) y Reabrir (si CONFIRMADO) visibles según estado.
6. Estados: ACTIVO, CONFIRMADO y REABIERTO con representación visual diferenciada.
7. No presenta el cierre como conciliación bancaria, ganancia o utilidad.

### Edge Cases

- Pantalla sin datos: estado vacío con mensaje contextual, no error.
- Tabla con muchas columnas en móvil: scroll horizontal dentro del contenedor.
- Sidebar colapsado en tablet: íconos con tooltips.
- Modal abierto y navegación por teclado: foco atrapado dentro del modal.
- Selector de agente sin asignaciones: mensaje indicando que no hay agentes asignados.
- Gráfico sin datos: mensaje en lugar de gráfico vacío.
- Carga lenta simulada: skeleton en tarjetas y tabla.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE proporcionar un archivo de tokens CSS con colores semánticos, tipografía, escala de espaciado, sombras, radios, bordes y breakpoints.
- **FR-002**: El sistema DEBE proporcionar componentes Blade reutilizables para botones, inputs, selects, tablas, tarjetas métricas, badges, alerts, modales, paginación y skeletons.
- **FR-003**: El sistema DEBE proporcionar un layout autenticado con sidebar, header, session timer, selector de agente y navegación por rol.
- **FR-004**: El sistema DEBE maquetar la pantalla de inicio de sesión con todos los estados de error, carga y envío.
- **FR-005**: El sistema DEBE maquetar el modal de expiración de sesión con estados de advertencia, renovación y sesión finalizada.
- **FR-006**: El sistema DEBE maquetar el dashboard del operador con tarjetas métricas, gráfico y operaciones recientes.
- **FR-007**: El sistema DEBE maquetar el formulario de registro de operación con prevención visual de doble envío.
- **FR-008**: El sistema DEBE maquetar el historial de operaciones con filtros, tabla, paginación y estados.
- **FR-009**: El sistema DEBE maquetar el dashboard administrativo con filtros multidimensionales, gráficos y ranking.
- **FR-010**: El sistema DEBE maquetar la pantalla de cierre operativo diario con estados ACTIVO, CONFIRMADO y REABIERTO.
- **FR-011**: Todas las pantallas DEBEN ser utilizables en 1440 px, 1280 px, 768 px y 375 px sin desplazamiento horizontal global.
- **FR-012**: Los componentes DEBEN incluir estilos para estados normal, hover, focus, active, disabled, loading, error y success.
- **FR-013**: Las entradas y salidas de efectivo DEBEN distinguirse mediante texto (+/-), iconografía y señal visual, no solo color.
- **FR-014**: Los datos de demostración DEBEN estar aislados y claramente identificados como ficticios.
- **FR-015**: Toda desviación del diseño Stitch DEBE documentarse con elemento original, cambio, justificación, beneficio y pantallas afectadas.

### Key Entities *(include if feature involves data)*

Esta capacidad no introduce entidades persistentes. Opera con datos de demostración para:

- **Usuario**: nombre, rol (operador/admin), agente activo.
- **Operación demo**: monto, tipo, fecha, estado, agente, referencia.
- **Métrica demo**: valores agregados ficticios para tarjetas y gráficos.
- **Cierre demo**: fecha, agente, métricas, estado.

### Data, Authorization & Audit Constraints *(mandatory)*

- **Authorization**: La maquetación diferencia visualmente la experiencia del operador y del administrador mediante variación de navegación y componentes visibles, pero no implementa autorización real de servidor (fuera de alcance).
- **Data minimization**: Los datos de demostración no contienen información real de clientes, tarjetas, cuentas o credenciales.
- **Auditability**: No aplica. Sin persistencia.
- **Time and money**: Los montos se presentan con formato decimal y símbolo de moneda. "Monto bruto operado" como etiqueta. Entradas con +$, salidas con -$.
- **Session security**: El temporizador de sesión se maqueta visualmente. La lógica JWT real pertenece a 001-auth-session.

### Operational Quality Constraints *(mandatory)*

- **Portability and UI**: Blade + CSS propio + ES Modules. Sin SPA. Sin framework CSS externo. Los componentes se renderizan en servidor.
- **Performance**: CSS en un solo archivo minificado por Vite. Sin dependencias CSS externas. Sin animaciones complejas.
- **Observability and recovery**: No aplica. Sin persistencia.
- **System boundary**: Esta capacidad es puramente presentacional. No procesa datos reales, no persiste y no implementa reglas de negocio.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Las 7 pantallas de referencia Stitch tienen una representación Blade maquetada con datos de demostración.
- **SC-002**: No existe archivo CSS con más de 20% de reglas duplicadas entre pantallas.
- **SC-003**: El 100% de los componentes interactivos son navegables por teclado con foco visible.
- **SC-004**: Las 7 pantallas no presentan desplazamiento horizontal global en 375 px, 768 px, 1280 px y 1440 px.
- **SC-005**: Los 30+ componentes listados en la especificación existen como componentes Blade o estilos CSS.
- **SC-006**: Las entradas y salidas de efectivo son distinguibles sin depender del color (prueba de escala de grises).
- **SC-007**: El 100% de las etiquetas "monto bruto operado" no contienen las palabras "ingreso", "utilidad" o "ganancia".
- **SC-008**: Existe un documento de desviaciones respecto a Stitch cuando se realizaron mejoras de accesibilidad o consistencia.
- **SC-009**: Los 4 breakpoints definidos muestran las funciones esenciales sin desaparición de contenido crítico.

## Assumptions

- Los diseños Stitch en `docs/design/stitch/v1/` existen y son accesibles. Si no existen, se usarán referencias visuales estándar.
- Chart.js se reutiliza para gráficos demo con datos ficticios.
- La tipografía usa fuentes del sistema (system-ui) para evitar dependencias externas.
- Los colores semánticos se alinean con DESIGN.md de Stitch; en su ausencia se usan tokens estándar accesibles.
- Las pantallas maquetadas se integrarán progresivamente con las features 001-005 en iteraciones futuras.

## Dependencies

- Ninguna dependencia funcional. Esta capacidad es autónoma.
- Referencia visual: `docs/design/stitch/v1/DESIGN.md` y `docs/design/stitch/v1/MANIFEST.md`.
- Chart.js (ya instalado en 004) para gráficos demo.

---
description: "Tareas de implementación para fundamentos visuales y maquetación"
---

# Tasks: Fundamentos Visuales

**Input**: Design documents from `/specs/006-visual-foundation/`. Referencia visual: `docs/design/stitch/v1/`.

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/web-endpoints.md, quickstart.md.

**Tests**: Validación manual contra screen.png, criterios de accesibilidad y quickstart.md.

**Organization**: Tareas organizadas por capa de construcción (tokens → layout → componentes → pantallas → validación).

## Format: `[ID] [P?] Description`

## Phase 1: Análisis de la exportación Stitch

**Purpose**: Inspeccionar la exportación Stitch para extraer patrones, identificar dependencias y documentar problemas antes de construir.

- [X] T001 [P] Analizar DESIGN.md y extraer paleta de colores, tipografía, espaciado y jerarquía en docs/design/stitch/v1/DESIGN.md
- [X] T002 [P] Analizar MANIFEST.md y verificar el mapeo de 7 pantallas contra la spec en docs/design/stitch/v1/MANIFEST.md
- [X] T003 [P] Inspeccionar cada code.html para identificar estilos inline, recursos CDN, Google Fonts y estructuras repetidas en docs/design/stitch/v1/*/code.html
- [X] T004 [P] Comparar cada screen.png con su code.html y documentar discrepancias visuales en docs/design/stitch/v1/ANALYSIS.md
- [X] T005 Crear inventario de elementos reutilizables entre pantallas (headers, cards, tables, buttons, filters) en docs/design/stitch/v1/COMPONENT_INVENTORY.md

## Phase 2: Extracción de Design Tokens

**Purpose**: Traducir DESIGN.md a variables CSS semánticas. Sin dependencias externas.

- [X] T006 Crear archivo de tokens CSS con variables de Stitch (--st-*) y semánticas (--color-*) en resources/css/tokens.css
- [X] T007 [P] Definir escala tipográfica con system-ui, tamaños, pesos y line-height en resources/css/tokens.css
- [X] T008 [P] Definir escala de espaciado, radios, bordes, sombras y breakpoints en resources/css/tokens.css
- [X] T009 Crear reset CSS mínimo y estilos base (html, body, headings, links, focus-visible) en resources/css/reset.css y resources/css/base.css

## Phase 3: Layout Base

**Purpose**: Construir los layouts guest y authenticated que envuelven todas las pantallas.

- [X] T010 Crear layout guest (login, sin sidebar) con slots head/body en resources/views/layouts/guest.blade.php
- [X] T011 Crear layout authenticated con app shell, sidebar, topbar y área de contenido en resources/views/layouts/authenticated.blade.php
- [X] T012 [P] Implementar sidebar con navegación colapsable, íconos y variación por rol en resources/views/components/layout/sidebar.blade.php
- [X] T013 [P] Implementar topbar con user info, session timer y agent selector en resources/views/components/layout/topbar.blade.php
- [X] T014 [P] Implementar navegación móvil (hamburger, bottom nav) en resources/views/components/layout/mobile-nav.blade.php
- [X] T015 [P] Crear estilos de layout (sidebar, topbar, grid, responsive) en resources/css/layout.css
- [X] T016 [P] Implementar session indicator con temporizador visual en resources/views/components/layout/session-indicator.blade.php

## Phase 4: Componentes Reutilizables

**Purpose**: Construir los ~20 componentes UI compartidos por todas las pantallas.

- [X] T017 [P] Crear button con variantes (primary, secondary, danger, ghost) y estados en resources/views/components/ui/button.blade.php y resources/css/components/button.css
- [X] T018 [P] Crear input, select y currency-input con label, error, hint en resources/views/components/ui/input.blade.php, select.blade.php, currency-input.blade.php
- [X] T019 [P] Crear metric-card con label, valor, tendencia e icono en resources/views/components/ui/metric-card.blade.php y resources/css/components/card.css
- [X] T020 [P] Crear badge con variantes (active, annulled, pending, error) en resources/views/components/ui/badge.blade.php y resources/css/components/badge.css
- [X] T021 [P] Crear modal con overlay, focus trap y cierre por ESC en resources/views/components/ui/modal.blade.php, resources/js/components/modal.js y resources/css/components/modal.css
- [X] T022 [P] Crear toast con variantes (success, error, warning, info) y auto-dismiss en resources/views/components/ui/toast.blade.php, resources/js/components/toast.js y resources/css/components/toast.css
- [X] T023 [P] Crear data-table con headers, sort, empty y loading skeleton en resources/views/components/ui/data-table.blade.php y resources/css/components/table.css
- [X] T024 [P] Crear pagination con links e info "X de Y" en resources/views/components/ui/pagination.blade.php y resources/css/components/pagination.css
- [X] T025 [P] Crear filter-bar con slots y off-canvas mobile en resources/views/components/ui/filter-bar.blade.php y resources/css/components/filter-bar.css
- [X] T026 [P] Crear empty-state, error-state y loading-state (skeleton/spinner) en resources/views/components/ui/ y resources/css/components/skeleton.css
- [X] T027 [P] Crear chart-container con canvas, empty y loading en resources/views/components/ui/chart-container.blade.php y resources/css/components/chart.css
- [X] T028 [P] Crear breadcrumbs, tabs, dropdown y tooltip en resources/views/components/ui/
- [X] T029 [P] Crear estilos de inputs (text, select, currency) en resources/css/components/input.css
- [X] T030 [P] Implementar JS de componentes (sidebar toggle, dropdown, tabs, table-sort, currency-input format) en resources/js/components/

## Phase 5: Inicio de Sesión

**Purpose**: Maquetar login con todos los estados visuales.

- [X] T031 Implementar ruta demo GET /demo/login?state= en routes/web.php
- [X] T032 Crear controlador demo DemoAuthController con método login en app/Http/Controllers/Demo/DemoAuthController.php
- [X] T033 Crear vista login con formulario, campos, botón y estados vía query param en resources/views/screens/auth/login.blade.php
- [X] T034 [P] Crear estilos de pantalla auth en resources/css/screens/auth.css
- [X] T035 [P] Comparar login maquetado con docs/design/stitch/v1/inicio_de_sesi_n/screen.png y documentar diferencias

## Phase 6: Modal de Expiración

**Purpose**: Maquetar modal de expiración con estados de advertencia, renovación y sesión finalizada.

- [X] T036 Implementar ruta demo GET /demo/expiry?expiry= en routes/web.php
- [X] T037 Crear método expiry en DemoAuthController en app/Http/Controllers/Demo/DemoAuthController.php
- [X] T038 Crear vista de modal de expiración con temporizador y botones Continuar/Cerrar en resources/views/screens/auth/expiry-modal.blade.php
- [X] T039 [P] Crear componente screen expiry-modal-content en resources/views/components/screen/expiry-modal-content.blade.php
- [X] T040 [P] Comparar modal con docs/design/stitch/v1/aviso_de_expiraci_n_de_sesi_n/screen.png

## Phase 7: Dashboard del Operador

**Purpose**: Maquetar dashboard con tarjetas métricas, gráfico de distribución y operaciones recientes.

- [X] T041 Implementar ruta demo GET /demo/operator/dashboard en routes/web.php
- [X] T042 Crear controlador demo DemoOperatorController en app/Http/Controllers/Demo/DemoOperatorController.php
- [X] T043 Crear fixtures de datos demo (métricas, operaciones, distribución) en resources/demo/operator-dashboard.php
- [X] T044 Crear vista dashboard operador con tarjetas, gráfico doughnut y tabla de recientes en resources/views/screens/operator/dashboard.blade.php
- [X] T045 [P] Crear componente screen operator-metrics en resources/views/components/screen/operator-metrics.blade.php
- [X] T046 [P] Crear estilos de pantallas operador en resources/css/screens/operator.css
- [X] T047 [P] Comparar dashboard con docs/design/stitch/v1/dashboard_del_operador/screen.png

## Phase 8: Registro Rápido

**Purpose**: Maquetar formulario de registro de operación con prevención visual de doble envío.

- [X] T048 Implementar ruta demo GET /demo/operator/register en routes/web.php
- [X] T049 Crear método register en DemoOperatorController
- [X] T050 Crear vista formulario de registro con campos priorizados y botón con estado loading en resources/views/screens/operator/register.blade.php
- [X] T051 [P] Crear componente screen operation-form en resources/views/components/screen/operation-form.blade.php
- [X] T052 [P] Comparar formulario con docs/design/stitch/v1/registro_r_pido_de_operaci_n/screen.png

## Phase 9: Historial de Operaciones

**Purpose**: Maquetar historial con filtros, tabla paginada y estados de operación.

- [X] T053 Implementar ruta demo GET /demo/operator/history en routes/web.php
- [X] T054 Crear método history en DemoOperatorController
- [X] T055 Crear fixtures de datos demo (operaciones con estados variados) en resources/demo/operations.php
- [X] T056 Crear vista historial con filtros, tabla, paginación y badges de estado en resources/views/screens/operator/history.blade.php
- [X] T057 [P] Crear componente screen operation-filters en resources/views/components/screen/operation-filters.blade.php
- [X] T058 [P] Comparar historial con docs/design/stitch/v1/historial_de_operaciones/screen.png

## Phase 10: Dashboard Administrativo

**Purpose**: Maquetar dashboard admin con filtros multidimensionales, gráficos y ranking.

- [X] T059 Implementar ruta demo GET /demo/admin/dashboard en routes/web.php
- [X] T060 Crear controlador demo DemoAdminController en app/Http/Controllers/Demo/DemoAdminController.php
- [X] T061 Crear fixtures de datos demo (métricas globales, ranking operadores) en resources/demo/admin-dashboard.php
- [X] T062 Crear vista dashboard admin con filtros, tarjetas, gráficos y tabla comparativa en resources/views/screens/admin/dashboard.blade.php
- [X] T063 [P] Crear componente screen admin-filters (multidimensional) en resources/views/components/screen/admin-filters.blade.php
- [X] T064 [P] Crear componente screen operator-comparison (tabla + gráfico) en resources/views/components/screen/operator-comparison.blade.php
- [X] T065 [P] Crear estilos de pantallas admin en resources/css/screens/admin.css
- [X] T066 [P] Comparar dashboard admin con docs/design/stitch/v1/dashboard_administrativo/screen.png

## Phase 11: Cierre Operativo Diario

**Purpose**: Maquetar cierre diario con estados ACTIVO, CONFIRMADO y REABIERTO.

- [X] T067 Implementar ruta demo GET /demo/daily-closing/{id}?status= en routes/web.php
- [X] T068 Crear controlador demo DemoClosingController en app/Http/Controllers/Demo/DemoClosingController.php
- [X] T069 Crear fixtures de datos demo (cierre en 3 estados) en resources/demo/closing.php
- [X] T070 Crear vista cierre diario con métricas, desgloses, advertencia POR_CONFIRMAR y botones de acción en resources/views/screens/daily-closing/show.blade.php
- [X] T071 [P] Crear componente screen closing-detail y closing-warning en resources/views/components/screen/
- [X] T072 [P] Crear estilos de pantalla cierre en resources/css/screens/daily-closing.css
- [X] T073 [P] Comparar cierre con docs/design/stitch/v1/cierre_operativo_diario/screen.png

## Phase 12: Responsive Design

**Purpose**: Verificar y corregir los 4 breakpoints en todas las pantallas.

- [X] T074 Verificar cada pantalla en 1440px y corregir layout, espaciado y visibilidad de sidebar
- [X] T075 Verificar cada pantalla en 1280px y corregir sidebar colapsado, tablas y filtros
- [X] T076 Verificar cada pantalla en 768px y corregir hamburger menu, off-canvas filters, card-view en tablas ≤3 cols
- [X] T077 Verificar cada pantalla en 375px y asegurar que funciones esenciales no desaparecen
- [X] T078 [P] Corregir cualquier scroll horizontal global detectado en los 4 breakpoints

## Phase 13: Accesibilidad

**Purpose**: Verificar y corregir criterios WCAG 2.2 AA.

- [X] T079 Verificar contraste de color en todos los textos (≥4.5:1 normal, ≥3:1 large) usando herramienta de contraste
- [X] T080 Verificar navegación por teclado completa (Tab, Enter, Escape,箭头) en todas las pantallas
- [X] T081 [P] Verificar focus trap en modales y orden lógico de tabulación en formularios
- [X] T082 [P] Verificar labels asociados a inputs (for/id), mensajes de error con aria-describedby y headings jerárquicos
- [X] T083 [P] Verificar escala de grises: entradas y salidas de efectivo distinguibles sin color
- [X] T084 [P] Verificar prefers-reduced-motion: transiciones y animaciones desactivadas

## Phase 14: Validación Visual

**Purpose**: Validación completa contra spec, plan y quickstart.

- [X] T085 Comparar cada pantalla con su screen.png y documentar nivel de fidelidad alcanzado
- [X] T086 Verificar que no existen dependencias CDN, Google Fonts ni recursos remotos en el HTML renderizado
- [X] T087 Verificar que no existen estilos inline en los componentes Blade (todo en CSS externo)
- [X] T088 Verificar que los datos de demostración están en resources/demo/ y no hardcodeados en Blade
- [X] T089 Verificar que no existe lógica de negocio ficticia en las vistas (solo presentación)
- [X] T090 Verificar que "monto bruto operado" es la etiqueta en todas las tarjetas métricas y que no aparece "ingreso", "utilidad" o "ganancia"
- [X] T091 Verificar que los 30+ componentes del plan existen y son reutilizados por las pantallas
- [X] T092 Verificar que Chart.js solo se carga en pantallas de dashboard (no en login, historial, etc.)

## Phase 15: Documentación de Desviaciones

**Purpose**: Documentar toda mejora o cambio respecto a Stitch.

- [X] T093 Crear docs/design/stitch/v1/DEVIATIONS.md con formato de tabla (Stitch Element, Change, Justification, Benefit, Screens)
- [X] T094 [P] Documentar cambio de Google Fonts → system-ui
- [X] T095 [P] Documentar traducción de colores Stitch → tokens semánticos
- [X] T096 [P] Documentar adición de atributos ARIA y mejoras de accesibilidad no presentes en code.html
- [X] T097 [P] Documentar cualquier ajuste de layout, espaciado o jerarquía respecto a screen.png

## Phase 16: Limpieza y Optimización

**Purpose**: Preparar para entrega.

- [X] T098 Eliminar archivos de vistas antiguas que fueron reemplazadas (resources/views/identity-access/, resources/views/operations/, resources/views/reporting/, resources/views/daily-closing/ legacy)
- [X] T099 [P] Verificar que todas las rutas demo están bajo prefijo /demo/ y no interfieren con rutas reales
- [X] T100 [P] Ejecutar npm run build y verificar que CSS se minifica sin errores
- [X] T101 Ejecutar todos los escenarios de specs/006-visual-foundation/quickstart.md y registrar resultados
- [X] T102 [P] Actualizar README.md con instrucciones de revisión visual (rutas demo)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Análisis)**: inicia de inmediato. Sin dependencias.
- **Phase 2 (Tokens)**: depende de Phase 1 (DESIGN.md analizado).
- **Phase 3 (Layout)**: depende de Phase 2 (tokens CSS disponibles).
- **Phase 4 (Componentes)**: depende de Phase 2. Puede ejecutarse en paralelo con Phase 3.
- **Phases 5-11 (Pantallas)**: dependen de Phases 3+4 (layout + componentes). Pueden ejecutarse en paralelo entre sí.
- **Phase 12 (Responsive)**: depende de Phases 5-11 completas.
- **Phase 13 (Accesibilidad)**: depende de Phases 5-11 completas. Puede ejecutarse en paralelo con Phase 12.
- **Phase 14 (Validación)**: depende de Phases 5-13 completas.
- **Phase 15 (Documentación)**: puede ejecutarse en paralelo con Phases 5-14.
- **Phase 16 (Limpieza)**: depende de todas las fases anteriores.

### MVP Scope

Phases 1-7 (análisis → dashboard operador) entregan el sistema visual base. Phases 8-11 completan las 7 pantallas Stitch.

### Parallel Opportunities

- T001-T005 (análisis) son independientes.
- T006-T009 (tokens) son secuenciales dentro de la fase pero todos editando tokens.css.
- T012-T016 (componentes de layout) pueden crearse en paralelo.
- T017-T030 (componentes UI) pueden crearse en paralelo.
- Phases 5-11 (pantallas) pueden desarrollarse en paralelo por diferentes personas.
- T078-T091 (validación) se distribuyen por archivos distintos.

---

## Notes

- Sin migraciones, sin modelos, sin tests automatizados. Feature puramente visual.
- La validación es manual contra screen.png y quickstart.md.
- Las rutas demo usan controladores dummy en `app/Http/Controllers/Demo/`.
- Chart.js ya está instalado de 004-operational-dashboard.
- Los datos de demostración usan nombres como "Operador Demo 01", "Tienda Centro", montos redondos.
- El reemplazo de vistas legacy (T098) debe preservar las rutas funcionales de 001-005.

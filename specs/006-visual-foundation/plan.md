# Implementation Plan: Fundamentos Visuales y Sistema de Componentes

**Branch**: `006-visual-foundation` | **Date**: 2026-07-22 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/006-visual-foundation/spec.md`. Referencia visual: `docs/design/stitch/v1/`

## Summary

Reemplazar las vistas Blade existentes con un sistema visual unificado basado en la intención de Stitch. Construir tokens CSS semánticos, ~30 componentes Blade reutilizables, layout autenticado con variación por rol, y maquetar las 7 pantallas Stitch con datos de demostración. Sin migraciones, sin backend, sin lógica real. Chart.js diferido en dashboards.

## Technical Context

**Language/Version**: PHP 8.3 (Blade); HTML5; CSS3; JavaScript ES Modules

**Primary Dependencies**: Laravel 13 Blade, Vite, Chart.js (ya instalado). Cero dependencias CSS/JS externas.

**Storage**: Sin persistencia. Datos de demostración en fixtures PHP.

**Time & Money**: Montos demo con formato `S/ X,XXX.XX`. "Monto bruto operado" como etiqueta. Sin float en datos demo.

**Authentication & Session**: Sin lógica real. El layout renderiza variantes visuales según rol simulado.

**Testing**: Sin tests automatizados (feature visual). Validación manual contra criterios SC-001 a SC-009.

**Constraints**: Sin React, Vue, Angular, Inertia, Livewire, Bootstrap, Tailwind, jQuery. Sin WebSockets. CSS propio. Sin Google Fonts (system-ui). Sin animaciones complejas. Sin SPA.

## Constitution Check

*GATE: aprobado. Re-check post-design.*

- **I. Desarrollo por especificaciones**: PASS. Spec define 6 historias, 9 BR, 15 FR, 9 SC.
- **II. Entregas pequeñas**: PASS. Solo maquetación. Sin lógica de negocio.
- **III. Portabilidad**: PASS. Blade + CSS + ES Modules. Sin dependencias externas. Sin Node.js en runtime.
- **IV. Interfaz mínima**: PASS. CSS propio, sin frameworks CSS. Chart.js diferido. Sin SPA.
- **V. Seguridad servidor**: PASS. Sin autorización real. La diferenciación visual por rol no sustituye autorización.
- **VI. Sesiones**: PASS. Temporizador visual solamente.
- **VII. Integridad**: PASS. Sin datos reales. Sin operaciones.
- **VIII. Exactitud**: PASS. Formato decimal en datos demo. "Monto bruto operado".
- **IX. Privacidad**: PASS. Datos ficticios. Sin información real.
- **X. Pruebas**: PASS. Validación manual documentada contra SC.
- **XI. Recursos**: PASS. CSS minificado. Sin dependencias pesadas.
- **XII. Observabilidad**: PASS. Sin persistencia.
- **XIII. Gobernanza**: PASS. Desviaciones Stitch documentadas.

## Stitch → Laravel Screen Mapping

| Stitch folder | Canonical ID | Blade view |
|---------------|-------------|------------|
| inicio_de_sesi_n | authentication-login | `resources/views/screens/auth/login.blade.php` |
| aviso_de_expiraci_n_de_sesi_n | authentication-expiry-warning | `resources/views/screens/auth/expiry-modal.blade.php` |
| dashboard_del_operador | operator-dashboard | `resources/views/screens/operator/dashboard.blade.php` |
| registro_r_pido_de_operaci_n | operation-registration | `resources/views/screens/operator/register.blade.php` |
| historial_de_operaciones | operations-history | `resources/views/screens/operator/history.blade.php` |
| dashboard_administrativo | administrator-dashboard | `resources/views/screens/admin/dashboard.blade.php` |
| cierre_operativo_diario | daily-closing | `resources/views/screens/daily-closing/show.blade.php` |

## Component Inventory

### Layout Components (`resources/views/components/layout/`)
- `app-shell.blade.php` — HTML5 shell con slots head/body.
- `sidebar.blade.php` — Navegación lateral con colapso, rol y active state.
- `topbar.blade.php` — Header con user info, session timer, agent selector.
- `mobile-nav.blade.php` — Bottom nav o hamburger menu para ≤768px.

### UI Components (`resources/views/components/ui/`)
- `button.blade.php` — Variantes primary/secondary/danger/ghost. Estados: normal, hover, focus, active, disabled, loading.
- `input.blade.php` — Texto, email, password. Label, error, hint.
- `currency-input.blade.php` — Input numérico con prefijo S/, formato decimal.
- `select.blade.php` — Dropdown nativo estilizado. Label, error.
- `modal.blade.php` — Overlay + contenido. Foco atrapado, ESC para cerrar.
- `toast.blade.php` — Notificación efímera. Variantes: success, error, warning, info.
- `badge.blade.php` — Variantes: active/success, annulled/warning, pending/info, error/danger.
- `metric-card.blade.php` — Tarjeta con label, valor, tendencia opcional, icono.
- `data-table.blade.php` — Tabla con headers, rows, sort indicators, empty state, loading skeleton.
- `pagination.blade.php` — Links de página, info "X de Y".
- `filter-bar.blade.php` — Barra horizontal con slots para filtros. Off-canvas en móvil.
- `empty-state.blade.php` — Icono, título, descripción, acción opcional.
- `error-state.blade.php` — Mensaje de error con acción de reintento.
- `loading-state.blade.php` — Skeleton/spinner para cards, table, chart.
- `chart-container.blade.php` — Contenedor con canvas + estado vacío + loading.
- `breadcrumbs.blade.php` — Ruta de navegación contextual.
- `tabs.blade.php` — Navegación por pestañas.
- `dropdown.blade.php` — Menú desplegable.
- `tooltip.blade.php` — Tooltip en hover/focus.
- `session-indicator.blade.php` — Temporizador de sesión integrado.

### Screen Components (`resources/views/components/screen/`)
- `login-form.blade.php` — Formulario de autenticación con estados.
- `expiry-modal-content.blade.php` — Contenido del modal de expiración.
- `operator-metrics.blade.php` — Tarjetas métricas del operador.
- `operation-form.blade.php` — Formulario de registro de operación.
- `operation-filters.blade.php` — Filtros de historial.
- `admin-filters.blade.php` — Filtros multidimensionales del admin.
- `operator-comparison.blade.php` — Tabla comparativa de operadores.
- `closing-detail.blade.php` — Detalle de cierre diario.
- `closing-warning.blade.php` — Advertencia POR_CONFIRMAR.

## Design Token Translation Strategy

Los tokens de `DESIGN.md` se traducen a variables CSS semánticas:

```css
/* Stitch → Semántico */
--color-surface: var(--st-surface);           /* #fcf8fa */
--color-surface-container: var(--st-surface-container); /* #f0edef */
--color-primary: var(--st-primary);           /* #000000 */
--color-on-primary: var(--st-on-primary);     /* #ffffff */
--color-error: var(--st-error);               /* #ba1a1a */
--color-error-container: var(--st-error-container); /* #ffdad6 */

/* Semánticos derivados */
--color-success-text: #0d6b3e;
--color-success-bg: #d9f2e4;
--color-warning-text: #8a5c0a;
--color-warning-bg: #fdf0d0;
--color-cash-in: var(--color-success-text);
--color-cash-out: var(--color-error);
--color-net-positive: var(--color-success-text);
--color-net-negative: var(--color-error);
--color-status-active: var(--color-success-text);
--color-status-annulled: var(--st-on-surface-variant);
--color-status-pending: var(--color-warning-text);
```

Tipografía: `system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif` en lugar de Public Sans/Inter. Monoespaciado para datos numéricos: `'SF Mono', 'Cascadia Code', monospace`.

## Blade Structure

```
resources/views/
├── layouts/
│   ├── guest.blade.php          # Login (sin sidebar)
│   └── authenticated.blade.php  # App shell + sidebar + topbar
├── components/
│   ├── layout/   (5 files)
│   ├── ui/       (20 files)
│   └── screen/   (9 files)
└── screens/
    ├── auth/      (2 files)
    ├── operator/  (3 files)
    ├── admin/     (1 file)
    └── daily-closing/ (1 file)
```

Se reutiliza `@extends('layouts.authenticated')` o `@extends('layouts.guest')`.

## CSS Structure

```
resources/css/
├── tokens.css          # Variables CSS desde Stitch + semánticas
├── reset.css           # Reset mínimo
├── base.css            # Tipografía, headings, links, focus-visible
├── layout.css          # App shell, sidebar, topbar, grid
├── utilities.css       # Spacing, text, visibility helpers
├── components/
│   ├── button.css
│   ├── input.css
│   ├── modal.css
│   ├── table.css
│   ├── badge.css
│   ├── card.css
│   ├── toast.css
│   ├── pagination.css
│   ├── filter-bar.css
│   ├── skeleton.css
│   ├── dropdown.css
│   ├── tabs.css
│   └── chart.css
└── screens/
    ├── auth.css
    ├── operator.css
    ├── admin.css
    └── daily-closing.css
```

Un solo entry point `resources/css/app.css` con `@import` en orden. Vite minifica en build.

## JavaScript Structure

```
resources/js/
├── app.js                    # Entry: sidebar toggle, dropdowns, focus traps
├── components/
│   ├── modal.js              # open/close, focus trap, ESC
│   ├── toast.js              # show/dismiss, auto-dismiss timer
│   ├── tabs.js               # tab switching
│   ├── dropdown.js           # click/focus toggle
│   ├── sidebar.js            # collapse, mobile toggle
│   ├── table-sort.js         # client-side column sort
│   └── currency-input.js     # format on blur
└── reporting/
    └── dashboard-charts.js   # Chart.js (ya existente, se actualiza)
```

Chart.js se carga vía `@vite('resources/js/reporting/dashboard-charts.js')` solo en pantallas de dashboard. El resto del JS es vanilla ES Modules sin dependencias.

## Demo Data Strategy

Fixtures en `resources/demo/` como arrays PHP:

```php
// resources/demo/operations.php
return [
    ['id' => 1, 'amount' => 150.00, 'currency' => 'PEN', 'type' => 'Depósito', ...],
    ...
];
```

Las vistas reciben datos vía view composers o directamente desde el controlador demo. Las pantallas de autenticación muestran estados vía query params (`?state=error`, `?state=throttled`).

## Responsive Strategy

Breakpoints:
- `--bp-sm: 375px` (móvil)
- `--bp-md: 768px` (tablet)
- `--bp-lg: 1280px` (laptop)
- `--bp-xl: 1440px` (escritorio)

Mobile-first CSS con `min-width` media queries. Sidebar se colapsa a íconos en tablet, hamburger en móvil. Tablas: scroll horizontal en todas las pantallas salvo las de ≤3 columnas que usan card-view en móvil. Filtros: off-canvas desde la izquierda.

## Accessibility Strategy

- Contraste: ≥4.5:1 para texto normal, ≥3:1 para texto grande (WCAG AA).
- Foco: `:focus-visible` con outline de 2px + offset. Orden de tabulación lógico.
- Teclado: todos los componentes interactivos operables sin mouse. Focus trap en modales.
- Formularios: `<label>` asociado a `<input>` vía `for`/`id`. Errores con `aria-describedby`.
- Tablas: `scope="col"` en headers, `role="rowheader"` en primera columna.
- Motion: `@media (prefers-reduced-motion: reduce)` desactiva transiciones.

## Deviation Documentation

Formato en `docs/design/stitch/v1/DEVIATIONS.md`:

```markdown
| Stitch Element | Change | Justification | Benefit | Screens Affected |
|---------------|--------|---------------|---------|-----------------|
| Public Sans font | system-ui | No external dependency | Load performance | All |
| color tokens | Semantic CSS vars | BR-001 compliance | Maintainable | All |
| code.html inline styles | CSS classes | No inline styles | Cacheable | All |
```

## Chart.js Loading

Entrada Vite separada `resources/js/reporting/dashboard-charts.js`. Solo las vistas de dashboard incluyen `@vite('resources/js/reporting/dashboard-charts.js')`. El bundle `app.js` global no incluye Chart.js.

## Exception Tracking

No hay excepciones constitucionales. Chart.js está justificado conforme al Principio IV. Cero dependencias CSS/JS externas.

## Manual Review Procedure

1. Abrir cada pantalla en 375px, 768px, 1280px, 1440px.
2. Verificar SC-001 a SC-009 contra la spec.
3. Navegar todas las pantallas con Tab/Shift+Tab.
4. Activar todos los estados vía query params.
5. Verificar modo escala de grises (SC-006).
6. Comparar con screen.png para intención visual.
7. Documentar desviaciones en DEVIATIONS.md.

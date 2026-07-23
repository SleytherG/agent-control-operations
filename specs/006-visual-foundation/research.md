# Research: Fundamentos Visuales

## Stitch Analysis

**Decision**: Usar DESIGN.md como fuente de verdad visual. Extraer tokens de color, tipografía y espaciado. Traducir a variables CSS semánticas. Usar system-ui en vez de Google Fonts.

**Rationale**: La intención visual de Stitch se conserva sin dependencias externas. Los colores semánticos cumplen BR-001 y facilitan temas futuros. System-ui evita descargas de fuentes y mejora rendimiento.

**Stitch code.html analysis**:
- Contiene estilos inline extensos (extraídos, no copiados).
- Usa clases generadas automáticamente (reemplazadas por BEM semántico).
- Referencia recursos remotos de Google Fonts (eliminados).
- Estructura HTML plana (reestructurada con componentes Blade anidados).
- Sin atributos ARIA (añadidos en la traducción).

## CSS Architecture

**Decision**: CSS propio con variables CSS (custom properties). Sin preprocesador. Sin PostCSS más allá de Vite. Un solo archivo de tokens, componentes en archivos separados, entry point con @import.

**Rationale**: Variables CSS nativas son soportadas por todos los navegadores modernos requeridos. Sin dependencia de build para estilos. La organización por componentes permite mantenimiento independiente.

**Alternatives considered**: Sass/SCSS — rechazado por agregar dependencia de build; Tailwind — rechazado por Restricciones; CSS Modules — rechazado por complejidad innecesaria para Blade renderizado en servidor.

## Component Architecture

**Decision**: Componentes Blade sin clase (class-less). Props vía `@props()`. Slots para contenido variable. Sin lógica de negocio en componentes. Estados vía props (`$variant`, `$disabled`, `$loading`).

**Rationale**: Blade nativo sin dependencias. Un componente por archivo. Los estados se controlan desde el llamante (controlador). Los datos de demostración se pasan como props.

## Demo Data

**Decision**: Fixtures PHP en `resources/demo/`. Cada pantalla con su propio archivo de datos. Datos claramente ficticios (nombres como "Operador Demo 01", montos redondos). Sin hardcodear en Blade.

**Rationale**: Separación clara entre presentación y datos. Facilita la migración futura a datos reales.

## Chart.js Deferred Loading

**Decision**: Entrada Vite separada `resources/js/reporting/dashboard-charts.js`. Solo incluida en vistas de dashboard vía `@vite()`. No en bundle global.

**Rationale**: Cumple Principio IV de la Constitución. Chart.js ~60KB minificado solo se descarga en dashboards.

## Mobile-First Responsive

**Decision**: Mobile-first con `min-width` media queries. Sidebar: full en desktop, íconos en tablet, hamburger en móvil. Filtros: off-canvas lateral. Tablas: scroll horizontal.

**Rationale**: Mobile-first asegura que el contenido esencial funciona en la pantalla más pequeña. Cumple BR-004, BR-005, BR-006.

## Sources

- [MDN CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [WCAG 2.2 AA](https://www.w3.org/TR/WCAG22/)
- [Laravel Blade Components](https://laravel.com/docs/13.x/blade#components)
- [Chart.js](https://www.chartjs.org/)

# Quickstart Validation: Fundamentos Visuales

## Prerequisites

- Laravel 13 funcionando localmente.
- Vite build ejecutado (`npm run build` o `npm run dev`).
- Sin dependencias externas de fuentes o CDNs.

## Visual Review Checklist

### 1. Design Tokens
- [ ] `resources/css/tokens.css` existe con variables semánticas.
- [ ] Los nombres de variables expresan propósito, no solo apariencia.
- [ ] Los valores de color provienen de DESIGN.md (traducidos).

### 2. Componentes
- [ ] ~30 componentes Blade en `resources/views/components/`.
- [ ] Cada componente acepta props para variantes y estados.
- [ ] Sin estilos inline en los componentes Blade.
- [ ] Sin HTML duplicado entre pantallas.

### 3. Pantallas Stitch
- [ ] Las 7 pantallas tienen vista Blade en `resources/views/screens/`.
- [ ] Cada pantalla usa el layout `guest` o `authenticated` según corresponda.
- [ ] Cada pantalla recibe datos desde fixtures demo, no hardcodeados.

### 4. Estados Visuales
- [ ] Login: 6 estados vía `?state=` query param.
- [ ] Expiry modal: 5 estados vía `?expiry=` query param.
- [ ] Componentes: estados hover, focus, active, disabled, loading verificables.

### 5. Responsive
- [ ] Sidebar: full → iconos → hamburger en 1440/1280/768/375.
- [ ] Tablas: scroll horizontal en móvil.
- [ ] Filtros: off-canvas en móvil con botón "Filtros".
- [ ] Sin scroll horizontal global.
- [ ] Funciones esenciales visibles en 375px.

### 6. Accesibilidad
- [ ] Navegación por teclado en todas las pantallas (Tab, Enter, Escape).
- [ ] Focus visible en todos los elementos interactivos.
- [ ] Labels asociados a inputs vía `for`.
- [ ] Modal con focus trap.
- [ ] Contraste ≥4.5:1 en texto normal.

### 7. Terminología
- [ ] "Monto bruto operado" en todas las tarjetas métricas.
- [ ] Sin "ingreso", "utilidad" o "ganancia".
- [ ] Entradas con "+S/", salidas con "-S/".

### 8. Datos Demo
- [ ] Datos en `resources/demo/`, no en Blade.
- [ ] Nombres y valores claramente ficticios.
- [ ] Sin información bancaria real ni logos de bancos.

### 9. Chart.js
- [ ] Solo cargado en pantallas de dashboard.
- [ ] No en el bundle global.
- [ ] Gráficos con datos demo.

### 10. Desviaciones Stitch
- [ ] `docs/design/stitch/v1/DEVIATIONS.md` documenta todos los cambios.
- [ ] Cada desviación tiene elemento original, cambio, justificación, beneficio y pantallas.

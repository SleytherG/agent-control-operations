# Stitch Export Analysis

## Color Palette Analysis

The DESIGN.md defines a Material 3-based palette with these key color groups:

### Surface/Background Hierarchy
- `surface`: #fcf8fa -- primary light background
- `surface-container`: #f0edef -- cards, elevated surfaces
- `surface-container-lowest`: #ffffff -- white cards, highest contrast
- `surface-container-low`: #f6f3f5 -- subtle elevation

### Primary Brand
- `primary`: #000000 -- black primary color (unusual but intentional for financial apps)
- `primary-container`: #131b2e -- dark navy, used for sidebar background
- `on-primary`: #ffffff
- `on-primary-container`: #7c839b -- lighter text on dark navy

### Semantic Colors
- `error`: #ba1a1a -- errors, cash-outs
- `error-container`: #ffdad6 -- error backgrounds
- `tertiary`: #000000, `tertiary-container`: #002113 -- used for success/green states
- `tertiary-fixed-dim`: #4edea3 -- green accent for cash-ins, active states

## CDN References Found in code.html Files

Every code.html file contains:
1. `https://cdn.tailwindcss.com?plugins=forms,container-queries` -- Tailwind CDN
2. `https://fonts.googleapis.com/css2?family=Public+Sans...` -- Google Fonts
3. `https://fonts.googleapis.com/css2?family=Inter...` -- Google Fonts
4. `https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined...` -- Material Icons
5. Dashboard screens: `https://cdn.jsdelivr.net/npm/chart.js` -- Chart.js CDN
6. Profile images: `https://lh3.googleusercontent.com/aida-public/...` -- AI-generated images

## Inline Styles Analysis

All code.html files contain extensive inline styles via Tailwind utility classes. Key patterns:
- Sidebar: fixed left panel with w-64, bg-surface-container-lowest, border-r
- Topbar: fixed top with h-16, offset by sidebar width
- Metric cards: bg-surface, border, rounded-xl, p-md
- Tables: dense with bg-surface-container-low headers and text-right amounts
- Active nav: bg-secondary-container with border-r-4 border-primary

## Recurring Structural Patterns

1. **App Shell**: flex container with fixed sidebar + ml-64 main area + fixed topbar
2. **Sidebar**: dark (primary-container: #131b2e) with nav items + user footer
3. **Topbar**: user info, timer icon, notifications bell, account circle
4. **Metric Cards**: white bg, 1px border, uppercase label, large value, optional trend
5. **Data Tables**: dense, header with bg-surface-container-low, hover states
6. **Filters**: horizontal bar with date range, type, status selects + apply button
7. **Status Badges**: pill-shaped, colored bg with matching text

## Identified Issues

1. No ARIA attributes -- all components need accessibility annotations
2. Font dependencies -- Public Sans and Inter require network download
3. Inline scripts -- logic mixed with presentation
4. No focus management -- tab order needs improvement
5. No responsive sidebar toggle JS -- hamburger referenced but not functional
6. Fixed dimensions (w-64, h-16) -- should use CSS variables for consistency
7. No prefers-reduced-motion support
8. No labels associated with inputs via for/id

---
name: AgenteFlow
colors:
  surface: '#fcf8fa'
  surface-dim: '#dcd9db'
  surface-bright: '#fcf8fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f5'
  surface-container: '#f0edef'
  surface-container-high: '#eae7e9'
  surface-container-highest: '#e4e2e4'
  on-surface: '#1b1b1d'
  on-surface-variant: '#45464d'
  inverse-surface: '#303032'
  inverse-on-surface: '#f3f0f2'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#505f76'
  on-secondary: '#ffffff'
  secondary-container: '#d0e1fb'
  on-secondary-container: '#54647a'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#002113'
  on-tertiary-container: '#009668'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#d3e4fe'
  secondary-fixed-dim: '#b7c8e1'
  on-secondary-fixed: '#0b1c30'
  on-secondary-fixed-variant: '#38485d'
  tertiary-fixed: '#6ffbbe'
  tertiary-fixed-dim: '#4edea3'
  on-tertiary-fixed: '#002113'
  on-tertiary-fixed-variant: '#005236'
  background: '#fcf8fa'
  on-background: '#1b1b1d'
  surface-variant: '#e4e2e4'
typography:
  headline-lg:
    fontFamily: Public Sans
    fontSize: 30px
    fontWeight: '600'
    lineHeight: 36px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Public Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Public Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  data-mono:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-bold:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  gutter: 20px
  margin: 24px
  max-width: 1440px
---

## Brand & Style
The design system is engineered for high-stakes financial operations, prioritizing clarity, speed of cognition, and unwavering reliability. The brand personality is authoritative yet unobtrusive, acting as a precise instrument rather than a decorative interface. 

The aesthetic follows a **Corporate / Modern** approach with a focus on high-utility density. It rejects "trendy" visual effects like glassmorphism in favor of a structured, high-contrast environment that minimizes eye strain during long working sessions. The emotional response is one of controlled efficiency—the UI stays out of the way until a decision-making signal is required.

## Colors
The palette is rooted in a foundation of **Deep Navy (#0F172A)** and **Slate (#64748B)** to establish a professional, institutional character. 

- **Primary & Neutral:** The background uses a crisp **Light Gray (#F8FAFC)** to reduce glare, while white surfaces provide clear containment for data.
- **Semantic Indicators:** This system uses strict color-coding for financial movement. **Emerald (#10B981)** is reserved exclusively for "Cash-ins" and positive growth. **Crimson (#EF4444)** signals "Cash-outs" or critical errors. **Amber (#F59E0B)** is used for pending states or warnings. 
- **Contrast:** Maintain a minimum 4.5:1 contrast ratio for all functional text to ensure accessibility in data-heavy environments.

## Typography
The system employs a dual-font strategy to balance institutional authority with technical precision. 

- **Public Sans** is used for headings and primary navigation to provide a sturdy, trustworthy feel.
- **Inter** is used for all body text and data displays. 
- **Tabular Figures:** For all financial values, `font-variant-numeric: tabular-nums` must be enabled. This ensures that columns of numbers align perfectly, allowing users to scan and compare values vertically without optical shifting.
- **Scale:** On mobile devices, `headline-lg` should scale down to 24px (`headline-md` equivalent) to prevent awkward word breaks.

## Layout & Spacing
The layout follows a **Fixed Grid** philosophy on desktop to maintain a consistent density for financial dashboards.

- **Grid System:** A 12-column grid with a 1440px max-width. Gutters are fixed at 20px to balance data density with legibility.
- **Rhythm:** An 8px linear scale (with a 4px step for tight components) governs all padding and margins.
- **Responsiveness:** 
  - **Desktop (1024px+):** Full 12-column layout.
  - **Tablet (768px - 1023px):** 6-column layout, sidebars collapse into a "hamburger" or icon-only rail.
  - **Mobile (<767px):** Single-column stack. Horizontal scrolling is permitted only for data tables with a "sticky" first column for identification.

## Elevation & Depth
This design system uses **Tonal Layers** and **Low-contrast outlines** rather than deep shadows. This keeps the interface feeling "flat" and efficient, preventing the "heavy" look of traditional enterprise software.

- **Level 0 (Background):** Slate-50 (#F8FAFC).
- **Level 1 (Cards/Surface):** White (#FFFFFF) with a 1px solid border (#E2E8F0). No shadow.
- **Level 2 (Dropdowns/Modals):** White with a 1px solid border and a subtle, high-diffusion shadow (0px 4px 12px rgba(15, 23, 42, 0.08)).
- **Depth Cues:** Depth is communicated through subtle shifts in background color (e.g., a slightly darker gray for a side navigation rail) rather than physical stacking.

## Shapes
The shape language is **Soft (0.25rem)**. This provides a modern touch without appearing overly "bubbly" or consumer-oriented. 

- **Buttons & Inputs:** 4px (0.25rem) radius.
- **Cards & Containers:** 8px (0.5rem) radius.
- **Badges:** Fully rounded (pill) to distinguish them from interactive buttons.
- **Data Rows:** No rounded corners; rows are separated by 1px horizontal dividers to maximize vertical space.

## Components
- **Data Tables:** High-density. Header cells should have a subtle background (#F1F5F9) and use `label-bold` typography. Interactive rows should have a faint hover state (#F8FAFC).
- **Buttons:** 
  - *Primary:* Solid Deep Navy with white text. 
  - *Secondary:* Ghost style with Slate-600 borders.
  - *Semantic:* Solid Emerald or Crimson for final "Confirm" actions related to cash flow.
- **Input Fields:** Large, clear borders (1px solid #CBD5E1). Active state uses a 2px Deep Navy border. Numeric inputs should include clear currency symbols and larger font sizes for "Amount" fields.
- **Semantic Badges:** Small, caps-heavy labels with low-opacity background tints (e.g., Emerald-100 background with Emerald-700 text).
- **Financial Cards:** Used for summary metrics. They must contain a label, a large tabular value, and a small "trend" indicator using the semantic color palette.
- **Status Indicators:** Small 8px dots for "Live" or "Offline" status, positioned next to the relevant ID or name.
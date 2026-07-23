# Deviations from Stitch Export

This document records all intentional differences between the original Stitch code.html exports and the final Laravel Blade implementation.

| Stitch Element | Change | Justification | Benefit | Screens Affected |
|---|---|---|---|---|
| Google Fonts (Public Sans, Inter) | system-ui font stack | No external network dependency | Faster load, offline-compatible, no FOUT | All 7 screens |
| Material Symbols (Google Icons) | Unicode characters / text labels | No external font dependency | Zero network requests for icons, lighter bundle | All 7 screens |
| Tailwind CSS CDN | Custom CSS with variables (tokens.css) | No CDN dependency, no build-time JS | Full control, cacheable CSS, smaller bundle | All 7 screens |
| Chart.js CDN | Chart.js via Vite/npm | No CDN dependency, version locked | Deterministic builds, deferred loading | Dashboards only |
| Inline styles in code.html | External CSS files (components/*.css) | No inline styles policy | Caching, separation of concerns, maintainable | All 7 screens |
| Inline scripts in code.html | External JS modules (resources/js/components/) | No inline scripts policy | Caching, testable modules | All 7 screens |
| No ARIA attributes | Full ARIA annotations (role, aria-label, aria-describedby, aria-live, aria-modal) | WCAG 2.2 AA compliance | Screen reader support, keyboard navigation | All 7 screens |
| No focus management | focus-visible outlines, focus traps in modals | Accessibility best practice | Keyboard operability | All 7 screens |
| prefers-reduced-motion unsupported | Media query in reset.css | WCAG 2.2 compliance | Respects user motion preferences | All 7 screens |
| Sidebar uses w-64 (256px) | CSS variable --sidebar-width | Consistency, token-based sizing | Single source of truth for layout dimensions | All authenticated screens |
| Topbar uses h-16 (64px) | CSS variable --topbar-height | Consistency | Single source of truth | All authenticated screens |
| Sidebar with light bg (bg-surface-container-lowest) | Dark sidebar (--color-sidebar-bg: #131b2e) | DESIGN.md spec: sidebar is dark navy | Per spec: Primary Container #131b2e for sidebar | All authenticated screens |
| Color tokens named per Stitch | Semantic naming (--color-primary, --color-error, etc.) | BR-001: Semantic color tokens | Theme-agnostic components, future-proof | All 7 screens |
| No states for auth screens | Query param states (?state=error, loading, etc.) | FR-001, FR-004: Visual state management | Visual review of all states without backend | Login, Expiry |
| No empty/loading/error states | empty-state, error-state, loading-state components | SC-005: Visual state completeness | Complete UX coverage | All 7 screens |
| code.html sidebar has all nav items | Role-based sidebar (operator vs admin) | FR-002: Role-dependent views | Correct information architecture per role | Sidebar component |
| Dashboard operator has 5 KPI cards (design shows 5) | Implemented as design shows | Match screen.png | Visual fidelity | Operator Dashboard |
| Currency format used "$" in some code.html | All currency as "S/" per Peruvian convention | FR-009: Currency display | Locale-appropriate formatting | All screens |
| "Net Movement" / "Total Ops" labels | "Monto bruto operado" and other spec labels | BR-003, FR-010: Terminology standardization | Consistent language across the app | All screens |
| Profile images from Google URLs | Text initials in sidebar footer | No external image dependency | Privacy, no network requests for avatars | All authenticated screens |
| Mobile hamburger not functional in code.html | Functional hamburger + off-canvas mobile nav | Responsive design requirement | Works on mobile devices | All authenticated screens |
| Filter off-canvas not in code.html | Off-canvas filter panel for mobile | Mobile-friendly filter UX | Filters usable on narrow screens | History, Admin |
| No tabular-nums in code.html | font-variant-numeric: tabular-nums on financial values | DESIGN.md typography spec | Numbers align in columns for easy scanning | All screens with tables |
| No alternating row colors | Even rows have subtle background | Readability improvement | Easier to scan long table rows | All screens with tables |
| Chart.js loaded globally (in code.html) | Deferred to dashboard views only | Principle IV: Minimal interface | ~60KB saved on non-dashboard pages | Login, Registration, History, Closing |

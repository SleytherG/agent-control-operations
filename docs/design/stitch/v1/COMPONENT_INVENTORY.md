# Component Inventory

## Reusable Elements Across All 7 Screens

### Layout Shell
- **Sidebar**: Present in all authenticated screens (6 of 7). 256px wide, fixed left, dark background.
- **Topbar**: Present in all authenticated screens. 64px tall, fixed top, offset by sidebar.
- **Main Content Area**: ml-64, mt-16, overflow-y-auto, max-w-[1440px] centered.
- **Mobile Header**: Simple top bar with hamburger icon, hidden on md+.

### Navigation
- **Sidebar Nav Items**: Each is an `<a>` with icon + label. Active state: bg-secondary-container + border-r-4 border-primary.
- **Hamburger Menu**: Referenced in mobile headers but not functional in code.html.
- **Section Headers**: px-4 py-2 with uppercase tracking-wider text for grouping nav items.

### Cards
- **Metric Card (White)**: bg-surface, border border-outline-variant, rounded-xl, p-md. Contains label (uppercase tracking-wider), large value (headline-lg), optional trend.
- **Metric Card (Dark)**: bg-primary-container, text-on-primary-container. Highlighted variant.
- **Metric Card (Green accent)**: Right border accent via absolute div with bg-tertiary-fixed-dim.
- **Metric Card (Red accent)**: Right border accent via absolute div with bg-error.
- **Status Card**: bg-surface-container, rounded-xl, border. Used for connection status.

### Form Elements
- **Text Input**: border-outline-variant, rounded-lg, focus:border-primary, focus:ring-1.
- **Select**: Same style as input + material chevron overlay.
- **Currency Input**: Large (48px+), right-aligned, with currency prefix.
- **Radio Direction**: Segmented button style with peer-checked for cash direction.
- **Error Banner**: bg-error-container, border-error/20, rounded-lg, with icon + text + dismiss.

### Buttons
- **Primary**: bg-primary, text-on-primary, rounded-lg, font-label-bold.
- **Secondary/Outline**: border border-outline, text-primary, rounded-lg.
- **CTA Action**: Wider, centered, with icon + text.

### Data Display
- **Data Table Header**: bg-surface-container-low, font-label-bold, uppercase tracking-wider.
- **Data Table Row**: border-b, hover:bg-surface-bright, data-mono for numeric columns.
- **Status Dot**: w-2 h-2 rounded-full, green for active, red for annulled/error.
- **Status Badge**: px-2 py-0.5, rounded-full, color-coded bg + text.
- **Summary Bar**: grid of cells with label-bold + headline-sm values (dense metrics bar).

### Charts
- **Chart Container**: bg-surface-container-lowest, rounded-xl, border, p-6.
- **Doughnut Center Text**: Absolute positioned overlay on chart canvas.
- **Custom Legend**: HTML legend below chart with colored dots.
- **Chart Bar (CSS)**: CSS-only bars for simple visualizations (dashboard operator).

### Filters & Controls
- **Filter Row**: flex flex-wrap gap-md, multiple inputs in horizontal row.
- **Filter Panel**: bg-surface, border, rounded-xl, p-md, with filter icon header.
- **Multidimensional Filter**: Grid of selects + inputs across columns.

### Feedback States
- **Success Screen**: Hidden by default card with check_circle icon, transaction details, and action buttons.
- **Expiry Modal**: Fixed overlay with backdrop blur, centered card with timer + action buttons.
- **Empty State**: (Not present in code.html but required by spec).

### Contextual Information
- **Context Bar**: bg-surface-container-low, px-lg, py-3, border-b. Shows store/agent info.
- **User Info Footer**: Sidebar bottom with avatar + name + role.
- **Date/Time Display**: text-right with label-bold date + body-sm time.

## Component Count by Screen

| Screen | Sidebar | Topbar | Metric Cards | Table | Chart | Filter Bar | Form | Modal |
|--------|---------|--------|-------------|-------|-------|-----------|------|-------|
| Login | - | - | - | - | - | - | Yes | - |
| Expiry Modal | Yes | Yes | Yes | - | CSS | - | - | Yes |
| Operator Dashboard | Yes | Yes | 5 | Yes | 2 | - | - | - |
| Registration | Yes | Yes | - | - | - | - | Yes | - |
| History | Yes | Yes | 5 | Yes | - | Yes | - | - |
| Admin Dashboard | Yes | Yes | 4+5 | Yes | 3 | Yes | - | - |
| Daily Closing | Yes | Yes | 5 | Yes | - | - | - | - |

## Icon Usage

All screens use Material Symbols with specific icons:
- hub (login logo), person, lock, visibility/visibility_off, close
- home, add_card, history, dashboard, event_available
- storefront, account_balance, group, account_balance_wallet, category, security, settings
- timer, notifications, account_circle
- receipt_long, payments, arrow_downward, arrow_upward, trending_up
- south_west, north_east, check_circle, error, warning
- expand_more, search, calendar_month, filter_list, visibility, print, list_alt, download, autorenew, more_horiz

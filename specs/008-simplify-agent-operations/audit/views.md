# Auditoria de vistas Blade

Fuente: los 70 archivos `*.blade.php` presentes bajo `resources/views/`, agrupados por dominio/directorio.

## Layouts y raiz (3)

- `layouts/authenticated.blade.php`
- `layouts/guest.blade.php`
- `welcome.blade.php`

## Identity Access (7)

- `identity-access/home.blade.php`
- `identity-access/login.blade.php`
- `identity-access/password-change.blade.php`
- `identity-access/operators/form.blade.php`
- `identity-access/operators/index.blade.php`
- `identity-access/sessions/index.blade.php`
- `identity-access/users/deactivate.blade.php`

## Organization (6)

- `organization/stores/form.blade.php`
- `organization/stores/index.blade.php`
- `organization/geo/regions/index.blade.php`
- `organization/geo/regions/show.blade.php`
- `organization/geo/provinces/index.blade.php`
- `organization/geo/districts/index.blade.php`

## Banking Network (6)

- `banking-network/banks/form.blade.php`
- `banking-network/banks/index.blade.php`
- `banking-network/agents/form.blade.php`
- `banking-network/agents/index.blade.php`
- `banking-network/assignments/index.blade.php`
- `banking-network/my-agents.blade.php`

## Operations (7)

- `operations/index.blade.php`
- `operations/create.blade.php`
- `operations/show.blade.php`
- `operations/annul.blade.php`
- `operations/confirmation.blade.php`
- `operations/types/index.blade.php`
- `operations/types/form.blade.php`

## Daily Closing (4)

- `daily-closing/index.blade.php`
- `daily-closing/create.blade.php`
- `daily-closing/show.blade.php`
- `daily-closing/components/pending-confirm-warning.blade.php`

## Reporting (6)

- `reporting/operator-dashboard.blade.php`
- `reporting/admin-dashboard.blade.php`
- `reporting/operator-comparison.blade.php`
- `reporting/components/empty-state.blade.php`
- `reporting/components/filters.blade.php`
- `reporting/components/operations-table.blade.php`

## Componentes de layout (4)

- `components/layout/mobile-nav.blade.php`
- `components/layout/session-indicator.blade.php`
- `components/layout/sidebar.blade.php`
- `components/layout/topbar.blade.php`

## Componentes de pantalla (8)

- `components/screen/admin-filters.blade.php`
- `components/screen/closing-detail.blade.php`
- `components/screen/closing-warning.blade.php`
- `components/screen/expiry-modal-content.blade.php`
- `components/screen/operation-filters.blade.php`
- `components/screen/operation-form.blade.php`
- `components/screen/operator-comparison.blade.php`
- `components/screen/operator-metrics.blade.php`

## Componentes UI (19)

- `components/ui/badge.blade.php`
- `components/ui/breadcrumbs.blade.php`
- `components/ui/button.blade.php`
- `components/ui/chart-container.blade.php`
- `components/ui/currency-input.blade.php`
- `components/ui/data-table.blade.php`
- `components/ui/dropdown.blade.php`
- `components/ui/empty-state.blade.php`
- `components/ui/error-state.blade.php`
- `components/ui/filter-bar.blade.php`
- `components/ui/input.blade.php`
- `components/ui/loading-state.blade.php`
- `components/ui/metric-card.blade.php`
- `components/ui/modal.blade.php`
- `components/ui/pagination.blade.php`
- `components/ui/select.blade.php`
- `components/ui/tabs.blade.php`
- `components/ui/toast.blade.php`
- `components/ui/tooltip.blade.php`

Nota de conteo: las listas contienen 3 + 7 + 6 + 6 + 7 + 4 + 6 + 4 + 8 + 19 = 70 archivos. No existen otras vistas Blade fuera de estos grupos al momento de la auditoria.

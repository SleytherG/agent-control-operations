# Auditoria de rutas nombradas

Fuente: `routes/*.php`, contrastada con `php artisan route:list --json`. Se inventarian las 68 rutas nombradas de la aplicacion; se excluyen rutas nombradas aportadas por Dusk/framework. `AuthenticateJwtSession` se abrevia como `JWT`.

| Nombre | Metodo | URL | Controlador@metodo | Middleware |
|---|---|---|---|---|
| `login` | GET/HEAD | `/login` | `LoginController@showLoginForm` | `web` |
| `login.store` | POST | `/login` | `LoginController@login` | `web` |
| `home` | GET/HEAD | `/home` | `LoginController@home` | `web`, `JWT` |
| `auth.refresh` | POST | `/auth/refresh` | `RefreshSessionController@refresh` | `web`, `JWT` |
| `logout` | POST | `/logout` | `LogoutController@logout` | `web`, `JWT` |
| `sessions.index` | GET/HEAD | `/sessions` | `SessionHistoryController@index` | `web`, `JWT` |
| `admin.users.deactivate` | PATCH | `/admin/users/{user}/deactivate` | `DeactivateUserController@deactivate` | `web`, `JWT` |
| `admin.users.index` | GET/HEAD | `/admin/users` | `OperatorController@index` | `web`, `JWT` |
| `admin.users.create` | GET/HEAD | `/admin/users/create` | `OperatorController@create` | `web`, `JWT` |
| `admin.users.store` | POST | `/admin/users` | `OperatorController@store` | `web`, `JWT` |
| `admin.users.edit` | GET/HEAD | `/admin/users/{user}/edit` | `OperatorController@edit` | `web`, `JWT` |
| `admin.users.update` | PATCH | `/admin/users/{user}` | `OperatorController@update` | `web`, `JWT` |
| `admin.users.deactivate-operator` | DELETE | `/admin/users/{user}` | `OperatorController@deactivate` | `web`, `JWT` |
| `password.change` | GET/HEAD | `/password/change` | `PasswordChangeController@show` | `web`, `JWT` |
| `password.change.update` | PATCH | `/password/change` | `PasswordChangeController@update` | `web`, `JWT` |
| `admin.regions.index` | GET/HEAD | `/admin/regions` | `GeoHierarchyController@regionsIndex` | `web`, `JWT` |
| `admin.regions.store` | POST | `/admin/regions` | `GeoHierarchyController@storeRegion` | `web`, `JWT` |
| `admin.regions.show` | GET/HEAD | `/admin/regions/{region}` | `GeoHierarchyController@showRegion` | `web`, `JWT` |
| `admin.regions.update` | PATCH | `/admin/regions/{region}` | `GeoHierarchyController@updateRegion` | `web`, `JWT` |
| `admin.regions.deactivate` | DELETE | `/admin/regions/{region}` | `GeoHierarchyController@deactivateRegion` | `web`, `JWT` |
| `admin.regions.provinces.index` | GET/HEAD | `/admin/regions/{region}/provinces` | `GeoHierarchyController@provincesIndex` | `web`, `JWT` |
| `admin.regions.provinces.store` | POST | `/admin/regions/{region}/provinces` | `GeoHierarchyController@storeProvince` | `web`, `JWT` |
| `admin.provinces.update` | PATCH | `/admin/provinces/{province}` | `GeoHierarchyController@updateProvince` | `web`, `JWT` |
| `admin.provinces.deactivate` | DELETE | `/admin/provinces/{province}` | `GeoHierarchyController@deactivateProvince` | `web`, `JWT` |
| `admin.provinces.districts.index` | GET/HEAD | `/admin/provinces/{province}/districts` | `GeoHierarchyController@districtsIndex` | `web`, `JWT` |
| `admin.provinces.districts.store` | POST | `/admin/provinces/{province}/districts` | `GeoHierarchyController@storeDistrict` | `web`, `JWT` |
| `admin.districts.update` | PATCH | `/admin/districts/{district}` | `GeoHierarchyController@updateDistrict` | `web`, `JWT` |
| `admin.districts.deactivate` | DELETE | `/admin/districts/{district}` | `GeoHierarchyController@deactivateDistrict` | `web`, `JWT` |
| `admin.stores.index` | GET/HEAD | `/admin/stores` | `StoreController@index` | `web`, `JWT` |
| `admin.stores.create` | GET/HEAD | `/admin/stores/create` | `StoreController@create` | `web`, `JWT` |
| `admin.stores.store` | POST | `/admin/stores` | `StoreController@store` | `web`, `JWT` |
| `admin.stores.show` | GET/HEAD | `/admin/stores/{store}` | `StoreController@show` | `web`, `JWT` |
| `admin.stores.update` | PATCH | `/admin/stores/{store}` | `StoreController@update` | `web`, `JWT` |
| `admin.stores.deactivate` | DELETE | `/admin/stores/{store}` | `StoreController@deactivate` | `web`, `JWT` |
| `admin.banks.index` | GET/HEAD | `/admin/banks` | `BankController@index` | `web`, `JWT` |
| `admin.banks.create` | GET/HEAD | `/admin/banks/create` | `BankController@create` | `web`, `JWT` |
| `admin.banks.store` | POST | `/admin/banks` | `BankController@store` | `web`, `JWT` |
| `admin.banks.update` | PATCH | `/admin/banks/{bank}` | `BankController@update` | `web`, `JWT` |
| `admin.banks.deactivate` | DELETE | `/admin/banks/{bank}` | `BankController@deactivate` | `web`, `JWT` |
| `admin.bank-agents.index` | GET/HEAD | `/admin/bank-agents` | `BankAgentController@index` | `web`, `JWT` |
| `admin.bank-agents.create` | GET/HEAD | `/admin/bank-agents/create` | `BankAgentController@create` | `web`, `JWT` |
| `admin.bank-agents.store` | POST | `/admin/bank-agents` | `BankAgentController@store` | `web`, `JWT` |
| `admin.bank-agents.update` | PATCH | `/admin/bank-agents/{agent}` | `BankAgentController@update` | `web`, `JWT` |
| `admin.bank-agents.deactivate` | DELETE | `/admin/bank-agents/{agent}` | `BankAgentController@deactivate` | `web`, `JWT` |
| `admin.users.assignments.index` | GET/HEAD | `/admin/users/{user}/assignments` | `UserBankAgentAssignmentController@index` | `web`, `JWT` |
| `admin.users.assignments.store` | POST | `/admin/users/{user}/assignments` | `UserBankAgentAssignmentController@store` | `web`, `JWT` |
| `admin.assignments.destroy` | DELETE | `/admin/assignments/{assignment}` | `UserBankAgentAssignmentController@destroy` | `web`, `JWT` |
| `my-agents.index` | GET/HEAD | `/my-agents` | `MyAgentsController@index` | `web`, `JWT` |
| `operations.index` | GET/HEAD | `/operations` | `OperationController@index` | `web`, `JWT` |
| `operations.create` | GET/HEAD | `/operations/create` | `OperationController@create` | `web`, `JWT` |
| `operations.store` | POST | `/operations` | `OperationController@store` | `web`, `JWT` |
| `operations.show` | GET/HEAD | `/operations/{operation}` | `OperationController@show` | `web`, `JWT` |
| `operations.annul` | POST | `/operations/{operation}/annul` | `OperationController@annul` | `web`, `JWT` |
| `admin.operation-types.index` | GET/HEAD | `/admin/operation-types` | `OperationTypeController@index` | `web`, `JWT` |
| `admin.operation-types.create` | GET/HEAD | `/admin/operation-types/create` | `OperationTypeController@create` | `web`, `JWT` |
| `admin.operation-types.store` | POST | `/admin/operation-types` | `OperationTypeController@store` | `web`, `JWT` |
| `admin.operation-types.edit` | GET/HEAD | `/admin/operation-types/{type}/edit` | `OperationTypeController@edit` | `web`, `JWT` |
| `admin.operation-types.update` | PATCH | `/admin/operation-types/{type}` | `OperationTypeController@update` | `web`, `JWT` |
| `admin.operation-types.destroy` | DELETE | `/admin/operation-types/{type}` | `OperationTypeController@destroy` | `web`, `JWT` |
| `daily-closures.index` | GET/HEAD | `/daily-closures` | `DailyClosingController@index` | `web`, `JWT` |
| `daily-closures.create` | GET/HEAD | `/daily-closures/create` | `DailyClosingController@create` | `web`, `JWT` |
| `daily-closures.store` | POST | `/daily-closures` | `DailyClosingController@store` | `web`, `JWT` |
| `daily-closures.show` | GET/HEAD | `/daily-closures/{closure}` | `DailyClosingController@show` | `web`, `JWT` |
| `daily-closures.confirm` | POST | `/daily-closures/{closure}/confirm` | `DailyClosingController@confirm` | `web`, `JWT` |
| `daily-closures.reopen` | POST | `/daily-closures/{closure}/reopen` | `DailyClosingController@reopen` | `web`, `JWT` |
| `dashboard.operator` | GET/HEAD | `/dashboard` | `DashboardController@operatorDashboard` | `web`, `JWT` |
| `admin.dashboard` | GET/HEAD | `/admin/dashboard` | `DashboardController@adminDashboard` | `web`, `JWT` |
| `admin.dashboard.operators` | GET/HEAD | `/admin/dashboard/operators` | `DashboardController@operatorComparison` | `web`, `JWT` |

Rutas no nombradas de la aplicacion: `GET /health` apunta a `HealthController@__invoke`. `routes/console.php` solo registra el comando `inspire` y no contiene rutas HTTP.

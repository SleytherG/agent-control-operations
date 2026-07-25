# Auditoria de controladores

Fuente: `app/Http/Controllers/` y `app/Modules/*/Http/Controllers/`. Se listan los 19 archivos, todos sus metodos declarados y cada llamada directa a `Gate::authorize`.

| Controlador | Metodos | `Gate::authorize` por metodo |
|---|---|---|
| `Controller` (abstracto) | Ninguno | Ninguna |
| `HealthController` | `__invoke` | Ninguna |
| `BankController` | `index`, `create`, `store`, `update`, `deactivate`, privado `logAudit` | `index: viewAny Bank`; `create/store: create Bank`; `update: update $bank`; `deactivate: deactivate $bank` |
| `BankAgentController` | `index`, `create`, `store`, `update`, `deactivate`, privado `logAudit` | `index: viewAny BankAgent`; `create/store: create BankAgent`; `update: update $agent`; `deactivate: deactivate $agent` |
| `MyAgentsController` | `index` | Ninguna |
| `UserBankAgentAssignmentController` | `index`, `store`, `destroy` | Los tres usan `viewAny BankAgent` |
| `DailyClosingController` | `index`, `create`, `store`, privado `executeStore`, `show`, `confirm`, `reopen` | `index/create: viewAny DailyClosure`; `store: generate [DailyClosure, $bankAgentId]`; `show: view $closure`; `confirm: confirm $closure`; `reopen: reopen $closure` |
| `DeactivateUserController` | `__construct`, `deactivate` | `deactivate: deactivate $target` |
| `LoginController` | `__construct`, `showLoginForm`, `login`, privado `throttleKey`, `home` | Ninguna |
| `LogoutController` | `__construct`, `logout` | Ninguna |
| `OperatorController` | `index`, `create`, `store`, `edit`, `update`, `deactivate`, privado `logAudit` | `index: viewAny User`; `create/store: createOperator User`; `edit/update: updateOperator $user`; `deactivate: deactivateOperator $user` |
| `PasswordChangeController` | `show`, `update` | Ninguna |
| `RefreshSessionController` | `__construct`, `refresh` | Ninguna |
| `SessionHistoryController` | `__construct`, `index` | Ninguna directa; delega alcance a `ListAuthorizedSessions` |
| `OperationController` | `index`, `create`, `store`, `show`, `annul` | `index: viewAny Operation`; `create/store: register Operation`; `show: view $operation`; `annul: annul $operation` |
| `OperationTypeController` | `index`, `create`, `store`, `edit`, `update`, `destroy` | `index: viewAny OperationType`; `create/store: create OperationType`; `edit/update: update $type`; `destroy: delete $type` |
| `GeoHierarchyController` | `regionsIndex`, `storeRegion`, `showRegion`, `updateRegion`, `deactivateRegion`, `provincesIndex`, `storeProvince`, `updateProvince`, `deactivateProvince`, `districtsIndex`, `storeDistrict`, `updateDistrict`, `deactivateDistrict`, privado `logAudit` | Cada metodo publico autoriza su operacion: `viewAny/create/view/update/deactivate` sobre `Region`, `Province` o `District`, segun corresponda |
| `StoreController` | `index`, `create`, `store`, `show`, `update`, `deactivate`, privado `logAudit` | `index: viewAny Store`; `create/store: create Store`; `show: view $store`; `update: update $store`; `deactivate: deactivate $store` |
| `DashboardController` | `__construct`, `operatorDashboard`, `adminDashboard`, `operatorComparison`, privados `resolvePeriod`, `resolvePeriodFromRequest`, `firstOfSemester`, `lastOfSemester` | `operatorDashboard: viewOperatorDashboard`; `adminDashboard/operatorComparison: viewAdminDashboard` |

Los metodos sin llamada directa pueden estar protegidos por middleware, Form Requests o acciones; esta tabla no atribuye autorizacion que no este escrita en el controlador.

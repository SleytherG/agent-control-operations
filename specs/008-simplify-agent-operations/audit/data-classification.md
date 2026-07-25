# Clasificacion de seeders y factories

Fuente: `database/seeders/` y `database/factories/`. En esta auditoria, **real/configuracion** significa dato estable utilizable como catalogo o bootstrap; **dummy/prueba** significa ejemplo, credencial por defecto o dato generado por Faker. No se encontraron cargas de datos productivos externos.

## Seeders (3)

| Archivo | Datos creados | Clasificacion factual |
|---|---|---|
| `DatabaseSeeder.php` | Organizacion `Red Principal`; usuario `admin@controloperaciones.local` con password literal `password`; invoca los otros dos seeders | Mixto: bootstrap determinista, pero la identidad y credencial son dummy/desarrollo, no datos productivos |
| `OperationalStructureSeeder.php` | Region/provincia Lima; distritos Cercado de Lima, Miraflores y San Isidro; `ST-EJEMPLO` / `Tienda de Ejemplo` / `Av. Ejemplo 123`; bancos BCP, Interbank y BBVA | Mixto: nombres geograficos y bancos corresponden a entidades reales; la tienda es explicitamente dummy. No crea `BankAgent` ni asignaciones |
| `OperationTypeSeeder.php` | Tipos generales y por cada banco: Deposito, Retiro, Consulta, Pago de servicios y Transferencia, con `cash_direction` | Catalogo determinista de ejemplo/configuracion; depende de los bancos sembrados y no contiene transacciones reales |

## Factories (15)

Todas las factories son **dummy/prueba**: generan datos sinteticos, estados controlados o fechas relativas. Ninguna representa una fuente de datos real.

| Archivo | Modelo/uso y estados |
|---|---|
| `database/factories/UserFactory.php` | Factory Laravel legacy para `App\Models\User`, con nombre/email Faker, password `password`, estado `unverified`; no corresponde al modelo modular actual |
| `BankingNetwork/BankFactory.php` | `Bank`; codigo/nombre Faker; estado `inactive` |
| `BankingNetwork/BankAgentFactory.php` | `BankAgent`; crea Organization, Store y Bank; terminal opcional; estado `inactive` |
| `BankingNetwork/UserBankAgentAssignmentFactory.php` | `UserBankAgentAssignment`; crea usuarios y agente; estado `inactive`; ejecuta `User::factory()->create()` para `assigned_by` dentro de `definition()` |
| `IdentityAccess/AuthRefreshTokenFactory.php` | `AuthRefreshToken`; hash sintetico, expiracion relativa; estado `consumed` |
| `IdentityAccess/AuthSessionFactory.php` | `AuthSession`; UUID y expiraciones relativas; estados `expired` y `revoked` |
| `IdentityAccess/OrganizationFactory.php` | `Organization`; empresa Faker. Duplica conceptualmente la factory del namespace Organization |
| `IdentityAccess/UserFactory.php` | `User` modular; username/email Faker, password `password`; estados `administradorPropietario` e `inactive` |
| `Operations/OperationFactory.php` | `Operation`; dependencias por factory, monto/referencia/observacion Faker; estados `annulled`, `withIdempotencyKey`, `withAmount`, `atDate` |
| `Operations/OperationTypeFactory.php` | `OperationType`; nombre/descripcion/direccion Faker; estados `forBank`, `general`, `inactive` |
| `Organization/DistrictFactory.php` | `District`; nombre Faker; estado `inactive` |
| `Organization/OrganizationFactory.php` | `Organization`; empresa Faker |
| `Organization/ProvinceFactory.php` | `Province`; ciudad Faker; estado `inactive` |
| `Organization/RegionFactory.php` | `Region`; estado Faker; estado `inactive` |
| `Organization/StoreFactory.php` | `Store`; codigo, empresa y direccion Faker; estado `inactive` |

Riesgos observados, sin corregir en Phase 1: credencial conocida en `DatabaseSeeder`, factory legacy `database/factories/UserFactory.php`, dos factories para `Organization`, y factories relacionadas que crean organizaciones independientes salvo que el test las vincule explicitamente.

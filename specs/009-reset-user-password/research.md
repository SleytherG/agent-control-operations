# Research: Restablecimiento Seguro de Contraseña

## Lifecycle Model

**Decision**: Crear `password_resets` como entidad explícita y mantener `password_changed_at` como
compatibilidad para el cambio forzado ya existente.

**Rationale**: El campo nullable actual no distingue alta inicial, reset pendiente, consumo único,
expiración, reemplazo ni sesión autorizada. Una entidad mínima hace comprobables esos estados sin
agregar un subsistema de recuperación autónoma.

**Alternatives considered**:

- Solo `password_changed_at=null`: insuficiente para consumo/expiración/concurrencia.
- Password broker de Laravel: está orientado a tokens y enlaces enviados al usuario, fuera del
  alcance de una credencial temporal emitida por el administrador.
- Columnas adicionales en `users`: mezclan historial con el estado actual y dificultan auditoría.

## Temporary Secret Storage And Reveal

**Decision**: Guardar únicamente `Hash::make(temporal)` en `users.password`. El secreto legible se
genera en memoria y, después del commit, se devuelve una sola vez mediante respuesta JSON
same-origin con `Cache-Control: no-store` a un modal de la página administrativa.

**Rationale**: El `fetch` evita redirect, URL, flash y sesión. Cerrar el modal elimina el nodo; reload
vuelve al GET original, por lo que no repite ni reexpone la respuesta. Si se pierde la respuesta, el
administrador debe emitir otro reset.

**Alternatives considered**:

- Guardarlo en texto legible: prohibido.
- Duplicar el hash en `password_resets`: no aporta validación; el hash vigente ya está en `users`.
- Flash/session cifrada: reduce exposición, pero conserva un secreto recuperable entre requests.
- Respuesta POST HTML completa: un reload puede volver a enviar la mutación.

## Password Policy And Generation

**Decision**: Centralizar la política permanente existente (`min:8`, confirmada) y generar una
temporal CSPRNG de exactamente 20 caracteres, sin espacios y con al menos una letra, un número y un
símbolo.

**Rationale**: La credencial aleatoria excede el mínimo existente y es copiable. Una regla compartida
evita divergencias entre alta, cambio normal y cambio obligatorio.

**Alternatives considered**: permitir que el administrador elija la temporal, frases registrables o
dependencia de un generador externo; todas aumentan predictibilidad, exposición o infraestructura.

## Administrative Step-Up

**Decision**: Validar `Hash::check(admin_password, actor.password)` en cada reset y aplicar un límite
independiente de 5 fallos por administrador+origen dentro de 60 segundos, limpiado después de un
reset exitoso.

**Rationale**: Cumple la aclaración y limita el daño de una sesión administrativa expuesta. La
contraseña nunca se flashea ni entra en contexto de logging.

**Alternatives considered**: simple confirmación visual o ventana de autenticación reciente; ambas
fueron rechazadas en la aclaración.

## Atomic Reset And Revocation

**Decision**: `ResetOperatorPassword` ejecuta en una transacción el reemplazo de resets previos,
cambio del hash, creación del ciclo, revocación de sesiones/refresh y auditoría, bloqueando primero
el usuario objetivo.

**Rationale**: Impide estados parciales y hace determinista el último reset concurrente. Laravel
confirma o revierte automáticamente la transacción; los locks de fila se mantienen hasta commit.

**Alternatives considered**: actualizaciones independientes, locks de caché o “last write wins” sin
lock. No ofrecen atomicidad o agregan infraestructura.

## One-Time Login

**Decision**: Orquestar verificación de contraseña, lock de usuario/reset, creación de sesión y
transición `PENDING -> CONSUMED` dentro de una única acción transaccional.

**Rationale**: El baseline separa `AuthenticateUser` y `StartAuthSession`; dos solicitudes pueden
validar el mismo hash antes del consumo. El lock más un FK único garantiza una sola sesión.

**Alternatives considered**: consumir después del redirect, contador de usos o tolerancia de varios
logins; contradicen la aclaración de un solo uso.

## Restricted Session Identity

**Decision**: Agregar `auth_sessions.password_reset_id` nullable y único. Solo esa sesión puede
completar el reset consumido.

**Rationale**: Una FK expresa la relación sin confiar únicamente en `password_changed_at=null`, que
también se usa para altas iniciales. El null mantiene compatibilidad con todas las sesiones actuales.

**Alternatives considered**: claim JWT nuevo sin estado persistido o flag global en usuario; no
prueban que la sesión sea la que consumió la credencial.

## Restricted Route Allowlist

**Decision**: Permitir a la sesión restringida GET/PATCH `/password/change`, POST `/logout` y la
renovación explícita de la misma sesión; redirigir o rechazar cualquier otra ruta protegida antes de
ejecutar efectos.

**Rationale**: Cumple el acceso obligatorio y preserva el ciclo constitucional de sesión. El
middleware actual debe incorporar logout, que hoy queda bloqueado cuando `password_changed_at` es
null.

**Alternatives considered**: ocultar navegación solamente, bloquear refresh o permitir páginas de
solo lectura; las dos últimas cambian reglas existentes o amplían el alcance.

## Completion Transaction

**Decision**: Bloquear sesión, reset y usuario; comprobar asociación/estado, validar la nueva
contraseña y su confirmación, y comprobar su diferencia frente al hash temporal vigente sin volver
a solicitar el secreto consumido; actualizar hash,
`password_changed_at`, reset y auditoría en un commit.

**Rationale**: Evita que una nueva contraseña se aplique sin completar el ciclo o que dos envíos
concurrentes lo completen dos veces. La sesión vinculada puede continuar bajo su expiración normal.

**Alternatives considered**: cerrar siempre la sesión tras el cambio o guardar historial de hashes.
No son requisitos y el historial ampliaría datos sensibles.

## Expiry Without Workers

**Decision**: Configurar TTL de 3,600 segundos y resolver `EXPIRED` oportunistamente durante login,
nuevo reset o consulta administrativa.

**Rationale**: La autorización siempre compara `now()` con `expires_at`; no depende de cleanup,
scheduler o proceso residente.

**Alternatives considered**: job permanente, borrado físico o credencial sin vencimiento.

## Audit And Query

**Decision**: Conservar cada ciclo en `password_resets` y escribir acciones append-only en
`audit_logs` con `User` como entidad afectada. La consulta admin filtra organización, target y
acciones `password_reset.*`, paginada a 25.

**Rationale**: Reutiliza la tabla e índices existentes, permite reconstrucción y evita un rol nuevo.
Los snapshots contienen estados, IDs e instantes; no hash, secreto, IP cruda ni payload.

**Alternatives considered**: solo logs técnicos, nueva tabla duplicada de auditoría o acceso del
operador; no cumplen trazabilidad o contradicen la aclaración.

## Database And Test Strategy

**Decision**: Feature tests rápidos pueden usar SQLite, pero carreras y locks se verifican con dos
conexiones reales en MySQL y MariaDB.

**Rationale**: SQLite no reproduce `FOR UPDATE`, aislamiento ni deadlocks de los motores declarados.
La documentación oficial de Laravel recomienda transacciones para atomicidad y soporta reintentos de
deadlock.

**Alternatives considered**: mocks o solo SQLite; no validan la propiedad central de un solo uso.

## Sources

- [Laravel 13 database transactions](https://laravel.com/docs/13.x/database#database-transactions)
- [Laravel 13 validation and password rules](https://laravel.com/docs/13.x/validation#validating-passwords)
- [Laravel 13 hashing API](https://api.laravel.com/docs/13.x/Illuminate/Contracts/Hashing/Hasher.html)
- [Laravel 13 rate limiting](https://laravel.com/docs/13.x/rate-limiting)
- [Laravel 13 middleware](https://laravel.com/docs/13.x/middleware)
- [Laravel 13 password reset model](https://laravel.com/docs/13.x/passwords)
- [Laravel 13 request flashing guidance](https://laravel.com/docs/13.x/requests#old-input)

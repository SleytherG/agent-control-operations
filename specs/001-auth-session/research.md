# Research: Autenticación y Ciclo de Sesión

## JWT Library And Authentication Model

**Decision**: Usar `lcobucci/jwt` 5.x para emitir/validar access JWT y una capa de sesión propia en
Laravel. El JWT viaja en cookie HttpOnly y cada petición protegida consulta estado de sesión/usuario.

**Rationale**: Cumple JWT sin desplegar OAuth. La consulta MySQL hace inmediatos logout, desactivación
y replay; los cinco minutos no se usan como ventana de revocación diferida. La librería ofrece
constraints explícitos para firma, issuer, audience y tiempos y funciona con PHP 8.3.

**Alternatives considered**:

- Sanctum: oficial y simple, pero su modo web usa sesiones Laravel y sus API tokens no son JWT.
- Passport: OAuth2 completo, excesivo para un monolito interno sin clientes OAuth.
- `tymon/jwt-auth`/forks: guard conveniente, pero refresh JWT/blacklist no representa el refresh opaco
  de un uso almacenado por hash.
- `firebase/php-jwt`: viable y menor, pero requiere más validación manual de perfil/claims.

## JWT Profile And Keys

**Decision**: HS256 con clave aleatoria dedicada de al menos 256 bits y claims `iss`, `aud`, `sub`,
`sid`, `jti`, `iat`, `nbf`, `exp`. Algoritmo, issuer y audience son configuración cerrada.

**Rationale**: Un solo monolito emite y verifica; clave asimétrica no separa responsabilidades aquí.
`sid` enlaza el JWT con revocación en servidor. La clave JWT, `APP_KEY` y pepper HMAC son distintos.

**Alternatives considered**: RS256, reservado para verificadores externos; confiar en `alg` o `kid`
del cliente, rechazado por RFC 8725.

## Refresh Token Storage

**Decision**: Generar 256 bits con CSPRNG, enviar base64url y almacenar
`HMAC-SHA-256(refresh_pepper, token)` como `BINARY(32)` con índice único. Retener generaciones
consumidas al menos hasta el fin absoluto de sesión.

**Rationale**: El hash indexable permite lookup constante y replay. HMAC protege mejor ante fuga solo
de base. Bcrypt/Argon no aportan frente a 256 bits aleatorios y dificultan búsqueda indexada.

**Alternatives considered**: plaintext, prohibido; SHA-256 sin pepper, aceptable pero con menor defensa
ante fuga de DB; guardar solo token actual, rechazado porque pierde detección de reutilización.

## Atomic Rotation And Concurrency

**Decision**: InnoDB y transacción con orden de lock fijo: `auth_sessions FOR UPDATE`, luego
`auth_refresh_tokens FOR UPDATE`. La primera petición consume e inserta sucesor; una segunda observa
`CONSUMED`, revoca toda la sesión y confirma esa revocación antes de responder 401.

**Rationale**: Implementa exactamente la decisión aclarada y evita doble sucesor. El session row es
el punto de serialización para refresh, logout y desactivación.

**Alternatives considered**: grace period o respuesta idempotente, rechazadas porque permitirían una
segunda presentación sin revocación; locks en orden variable, rechazados por deadlocks evitables.

## Cookie And CSRF Model

**Decision**: Cookies `__Host-access_token` y `__Host-refresh_token`, `Secure`, `HttpOnly`,
`SameSite=Strict`, `Path=/`, sin `Domain`. Rutas mutables son POST/PATCH dentro del middleware `web`
con token CSRF Laravel y validación opcional de Origin/Fetch Metadata.

**Rationale**: JavaScript no lee tokens y el navegador los transporta same-origin. HttpOnly mitiga
exfiltración por XSS, no CSRF; por ello CSRF sigue siendo obligatorio.

**Alternatives considered**: local/session storage, prohibidos; `SameSite=None`, innecesario sin
frontend cross-site; SameSite como única defensa, insuficiente.

## Access Revocation

**Decision**: Toda petición protegida valida JWT y luego consulta `auth_sessions` y `users` por IDs
indexados. Roles vigentes provienen de DB, no del token.

**Rationale**: Usuario inactivo o sesión revocada deja de autorizar inmediatamente. Acepta una
consulta liviana por petición a cambio de cumplir el criterio crítico.

**Alternatives considered**: JWT totalmente stateless, rechazado porque logout/desactivación tardaría
hasta `exp`; blacklist en Redis, prohibida como dependencia obligatoria.

## Expiry Without Workers

**Decision**: UTC del servidor/DB es autoridad. Cada request marca/deriva expiración cuando
`now >= access_expires_at` o `now >= absolute_expires_at`. El cleanup es oportunista y nunca requisito
de corrección. Refresh vence junto con access; sesión absoluta vence a ocho horas.

**Rationale**: Funciona en hosting sin procesos residentes y conserva finalización derivable cuando
el navegador se cierra.

**Alternatives considered**: job permanente, prohibido; confiar en contador cliente, inseguro.

## Modular Monolith

**Decision**: Un Laravel con límites en `app/Modules`, acciones de aplicación, Eloquent, Form
Requests y Policies. No repositorios genéricos ni bus/eventos distribuidos sin necesidad concreta.

**Rationale**: Mantiene nombres de negocio y controladores delgados sin convertir módulos en
microservicios. Las transacciones cruzan IdentityAccess/Audit dentro de la misma DB.

**Alternatives considered**: estructura Laravel plana, viable pero menos clara al crecer; paquetes
Composer internos y microservicios, complejidad prematura.

## Frontend Session Timer

**Decision**: Blade expone `expiresAt` no secreto; un ES Module recalcula contra `Date.now()` cada
segundo y en `visibilitychange`. Solo una renovación puede estar en vuelo por página.

**Rationale**: Evita drift por intervalos suspendidos y solicitudes duplicadas accidentales. El
servidor sigue siendo autoridad.

**Alternatives considered**: decrementar un entero, deriva tras suspensión; leer JWT/cookies, viola
inaccesibilidad de tokens; renovación al cargar, fuera de alcance.

## Database And Reporting Baseline

**Decision**: InnoDB, FK, checks, estados explícitos, timestamps UTC, importes `DECIMAL(18,2)` e
índices compuestos orientados a filtros. Reporting usa `COUNT`, `SUM` y `GROUP BY` SQL.

**Rationale**: Integridad, precisión y uso razonable de memoria. El modelo global guía futuras
especificaciones, pero esta entrega migra solo tablas de identidad/sesión/auditoría.

**Alternatives considered**: `float`, rechazado; sumas Eloquent en memoria, rechazadas; crear ahora
todas las tablas vacías, rechazado por entrega incremental y YAGNI.

## Test Database

**Decision**: PHPUnit. La suite funcional puede usar una DB aislada, pero concurrencia/locks se
ejecuta obligatoriamente sobre MySQL 8 y MariaDB soportada con dos conexiones reales.

**Rationale**: SQLite no reproduce `FOR UPDATE`, isolation ni deadlocks. Probar ambos motores evita
declarar compatibilidad solo nominal.

**Alternatives considered**: mocks de repositorio para concurrencia, insuficientes; Pest, válido pero
añade una preferencia no necesaria.

## Sources

- [Laravel 13 documentation](https://laravel.com/docs/13.x)
- [Laravel authentication](https://laravel.com/docs/13.x/authentication)
- [Laravel CSRF](https://laravel.com/docs/13.x/csrf)
- [lcobucci/jwt](https://github.com/lcobucci/jwt)
- [RFC 8725 JWT BCP](https://datatracker.ietf.org/doc/html/rfc8725)
- [RFC 9700 refresh token rotation](https://www.rfc-editor.org/rfc/rfc9700.html)
- [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [OWASP CSRF Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [MDN Set-Cookie](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Set-Cookie)
- [MySQL locking reads](https://dev.mysql.com/doc/refman/8.4/en/innodb-locking-reads.html)
- [MariaDB FOR UPDATE](https://mariadb.com/docs/server/reference/sql-statements/data-manipulation/selecting-data/for-update/)

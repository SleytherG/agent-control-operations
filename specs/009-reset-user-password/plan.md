# Implementation Plan: Restablecimiento Seguro de Contraseña

**Branch**: `009-reset-user-password` (feature identifier; no checkout performed) | **Date**: 2026-07-23 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/009-reset-user-password/spec.md`

## Summary

Ampliar el módulo `IdentityAccess` para que un `ADMINISTRADOR_PROPIETARIO` revalide su propia
contraseña y emita una credencial temporal de una hora para un `OPERADOR` activo de su organización.
Un registro `password_resets` conserva únicamente el ciclo de vida; el único hash de la credencial
temporal reemplaza transitoriamente `users.password`. El primer login consume el restablecimiento
bajo transacción, crea una sola sesión restringida y obliga a cambiar la contraseña. El reset,
consumo, cambio definitivo, revocación de sesiones y auditoría se serializan con locks de base de
datos, sin persistir ni registrar el secreto legible.

## Technical Context

**Language/Version**: PHP 8.3; JavaScript ECMAScript Modules; HTML5; CSS3

**Primary Dependencies**: Laravel 13.8, Eloquent, Blade, `lcobucci/jwt` 5.x y componentes UI propios;
Vite 8 solo durante build; no se incorpora una dependencia de runtime nueva

**Storage**: MySQL 8.0 o MariaDB compatible con InnoDB en producción; migraciones Laravel; SQLite en
pruebas funcionales no concurrentes; nueva tabla `password_resets` y FK opcional única desde
`auth_sessions`

**Time & Money**: instantes persistidos consistentemente y presentados en `America/Lima`; la
credencial no consumida vence a los 3,600 segundos; la capacidad no procesa dinero ni modifica
agregados operacionales

**Authentication & Session**: access JWT configurable de 300 segundos, refresh opaco rotatorio y
revocable, renovación explícita, límite absoluto vigente; el reset revoca todas las sesiones del
operador y el primer login temporal crea una sesión vinculada y restringida

**Testing**: PHPUnit 12.5, pruebas Feature y Unit, integración MySQL/MariaDB para locks y carreras;
pruebas de navegador o contrato DOM para revelación única, accesibilidad y ausencia de caché

**Target Platform**: Apache o Nginx sobre hosting PHP convencional con HTTPS, Composer,
MySQL/MariaDB y document root en `public/`

**Project Type**: aplicación web monolítica modular renderizada en servidor

**Performance Goals**: confirmación, login restringido y cambio obligatorio visibles en menos de
2 segundos en condiciones normales; flujo administrativo completo en menos de 60 segundos y cambio
del operador en menos de 2 minutos; auditoría siempre paginada

**Constraints**: sin SPA, sin Redis obligatorio, WebSockets, workers residentes, servicio de mensajería
ni Node.js en runtime; ningún secreto en logs, auditoría, URL, sesión, caché o almacenamiento local;
respuesta de revelación con `Cache-Control: no-store`

**Scale/Scope**: operadores paginados de 20 en 20 y auditoría de 25 en 25; múltiples sesiones por
operador y múltiples organizaciones ya representadas, siempre con alcance por organización; una
credencial temporal vigente o consumida por operador

## Constitution Check

*GATE: aprobado antes de Phase 0 y revalidado después de Phase 1.*

- **I. Desarrollo dirigido por especificaciones**: PASS. `spec.md` está aprobada y define problema,
  actores, reglas, escenarios, casos límite, exclusiones y decisiones aclaradas; el HOW queda en este plan.
- **II. Entregas pequeñas**: PASS. Es una nueva capacidad independiente y demostrable dentro de
  `IdentityAccess`, sin ampliar roles ni recuperación autónoma.
- **III. Portabilidad económica**: PASS. Usa PHP/Laravel/MySQL-MariaDB existentes; no requiere Redis,
  colas, workers, servicios externos, contenedores ni Node.js en producción.
- **IV. Interfaz mínima**: PASS. Blade, HTML semántico, componentes propios y un módulo JavaScript
  acotado controlan confirmación, respuesta one-shot y copiado; no hay SPA.
- **V. Seguridad del servidor**: PASS. Policy, reautenticación del administrador, estado, rol,
  organización y restricción de sesión se verifican en servidor; hashes Laravel y throttling se
  aplican sin registrar secretos.
- **VI. Sesiones seguras**: PASS. Se conservan JWT de cinco minutos, aviso, renovación explícita,
  rotación/revocación y logout. El reset revoca access sessions y refresh tokens del operador.
- **VII. Integridad y trazabilidad**: PASS. Los ciclos se conservan sin eliminación; auditoría
  before/after registra estados y fechas, nunca hashes ni contraseñas.
- **VIII. Exactitud monetaria y temporal**: PASS. El vencimiento usa instantes consistentes y se
  muestra en `America/Lima`; no intervienen importes.
- **IX. Privacidad y minimización**: PASS. Solo se agregan IDs, estados y fechas necesarios. El
  secreto legible existe únicamente en memoria y en la respuesta one-shot.
- **X. Pruebas obligatorias**: PASS. Se planifican autorización positiva/negativa, login, expiración,
  rotación, logout, revocación, consumo único, carreras y ausencia de secretos.
- **XI. Recursos responsables**: PASS. Locks breves, índices por usuario/estado/fecha, consultas
  paginadas y carga anticipada del último reset evitan N+1.
- **XII. Observabilidad y recuperación**: PASS. Logs sanitizados, health existente, backup previo,
  migraciones reversibles y pruebas up/down quedan cubiertos.
- **XIII. Gobernanza**: PASS. No se requiere excepción constitucional.
- **XIV. Simplicidad del dominio**: PASS. Se agrega una sola entidad de ciclo de vida; no se crea
  rol de soporte, canal externo, catálogo ni estado `BLOCKED` nuevo.
- **System boundary**: PASS. La capacidad administra acceso interno y no procesa ni confirma
  operaciones bancarias o contables.

**Post-design re-check**: PASS. `research.md`, `data-model.md`, `contracts/web-endpoints.md` y
`quickstart.md` preservan los controles anteriores, resuelven todos los puntos de investigación y no
introducen infraestructura prohibida.

## Architecture And Flow

### Administrative Reset

1. La vista de edición/listado abre un modal que identifica al operador, advierte la revocación y
   solicita la contraseña actual del administrador.
2. El Form Request valida forma y límite; la Policy exige administrador, misma organización,
   `OPERADOR` y estado `ACTIVE`. Cualquier estado distinto de `ACTIVE` se considera no elegible,
   cubriendo un eventual estado bloqueado sin crearlo en esta entrega.
3. `ResetOperatorPassword` revalida el hash del administrador y, en una transacción, bloquea el
   operador, sustituye resets pendientes/consumidos, guarda el nuevo hash temporal, deja
   `password_changed_at=null`, crea el reset, revoca sesiones/refresh y escribe auditoría.
4. El secreto se devuelve solo después del commit en la respuesta JSON al `fetch` same-origin. El
   modal lo inserta en el DOM y lo elimina al cerrar; recargar vuelve al GET original y no repite el
   POST. La respuesta y la vista usan `no-store`.

### Temporary Login

1. `AuthenticateAndStartSession` normaliza el identificador, aplica el throttling existente y abre
   una transacción antes de aceptar la contraseña.
2. La transacción bloquea usuario y reset vigente, vuelve a comprobar hash/estado/vencimiento y
   resuelve expiración de manera oportunista.
3. Para `PENDING`, crea sesión y refresh mediante los servicios existentes, enlaza
   `auth_sessions.password_reset_id` y cambia el reset a `CONSUMED` en el mismo commit.
4. Una segunda petición ve `CONSUMED` y falla con el mismo mensaje genérico. El login normal y el
   alta inicial sin fila de reset conservan su comportamiento actual.

### Restricted Session And Completion

1. `AuthenticateJwtSession` reconoce la FK del reset y permite solamente GET/PATCH de cambio,
   POST logout y POST refresh. Refresh solo rota credenciales de la misma `auth_session` vinculada:
   conserva `password_reset_id`, no crea otra sesión, no completa/reabre el reset y no habilita
   navegación ni operaciones protegidas.
2. `CompleteRequiredPasswordChange` bloquea sesión, reset y usuario, confirma que la sesión está
   vinculada a un reset `CONSUMED`, valida la nueva contraseña y su confirmación, y compara la nueva
   contraseña frente al hash temporal todavía vigente. El formulario no vuelve a solicitar el
   secreto temporal ya consumido.
3. En un commit reemplaza el hash, fija `password_changed_at`, marca `COMPLETED` y audita estados.
   La sesión vinculada continúa como sesión normal bajo sus vencimientos vigentes.
4. Logout, expiración o pérdida antes de completar deja el reset consumido; se necesita otro reset.

## Data And Concurrency Strategy

- `password_resets` es la fuente canónica del ciclo; `password_changed_at=null` continúa soportando
  el flujo histórico de alta inicial, pero no decide consumo, expiración o sesión restringida.
- El hash temporal reside solo en `users.password`; no se duplica en la nueva tabla. La nueva
  contraseña se compara contra ese hash antes de reemplazarlo.
- Orden de lock: usuarios afectados en orden de ID, reset por usuario, sesiones activas ordenadas por
  ID y refresh tokens. El mismo orden se comparte con desactivación/revocación.
- Dos resets se serializan por el usuario; el último commit deja el único reset `PENDING`. Dos logins
  se serializan por usuario/reset y el índice único de `auth_sessions.password_reset_id` impide dos
  sesiones restringidas.
- No hay worker de expiración. Login, nuevo reset y consulta administrativa cambian `PENDING` vencido
  a `EXPIRED` antes de decidir.
- `RevokeAllUserSessions` extrae y reutiliza la revocación masiva existente, añade razón
  `PASSWORD_RESET` y revoca explícitamente refresh tokens activos.

## Validation And Audit Strategy

- Una regla de contraseña central mantiene `min:8` y confirmación para alta/cambio; el generador
  CSPRNG produce exactamente 20 caracteres sin espacios e incluye al menos una letra, un número y
  un símbolo.
- La reautenticación administrativa admite 5 fallos por administrador+origen dentro de 60 segundos,
  responde 429 al excederse, no muta el target y limpia su contador tras un reset exitoso.
- El login normal o temporal conserva el límite existente de 5 fallos por identificador
  normalizado+origen dentro de 60 segundos y limpia el contador tras un login exitoso.
- Acciones de auditoría: `password_reset.issued`, `password_reset.superseded`,
  `password_reset.sessions_revoked`, `password_reset.consumed`, `password_reset.expired` y
  `password_reset.completed`. Cada transición se escribe en la misma transacción que la produce;
  los snapshots solo contienen IDs, estados, conteos e instantes.
- La consulta de auditoría filtra primero organización, target y acciones permitidas, usa el índice
  existente de `audit_logs` y pagina 25 registros. Un operador recibe 403.
- `LogSanitizer` es defensa secundaria: controladores, excepciones y contexto nunca reciben el
  secreto, el hash o campos de contraseña.
- Respuestas sensibles llevan `Cache-Control: no-store`; la contraseña no entra en query string,
  redirect, flash input, sesión, local/session storage ni historial.

## Migration And Rollback

1. Crear `password_resets` con FKs, estados respaldados por enum de dominio, timestamps e índices.
2. Agregar `password_reset_id` nullable y único a `auth_sessions`; filas existentes permanecen null.
3. Desplegar modelos/acciones/rutas/middleware/vistas compatibles con null antes de utilizar resets.
4. No hay backfill: usuarios y sesiones existentes representan el flujo normal o alta inicial.
5. Verificar migración up/down sobre base descartable MySQL y MariaDB; respaldar antes de producción.
6. Rollback de código primero deja de emitir resets. La reversión de esquema solo procede si no hay
   restablecimientos que deban conservarse; con datos productivos se prefiere forward fix o exportar
   la auditoría antes del rollback.

## Testing Strategy

- **Unit**: enum/transiciones, generador, regla de contraseña, vencimiento exacto y sanitización.
- **Feature**: endpoints, CSRF, Policy, reautenticación, estados no activos, reveal one-shot, login
  temporal, allowlist, cambio, logout, refresh, auditoría paginada y regresión del alta inicial.
- **Integration MySQL/MariaDB**: reset-vs-reset, doble login, reset-vs-login,
  reset-vs-deactivación y doble cambio con conexiones reales.
- **Browser/DOM**: modal accesible, copy, limpieza al cerrar, reload/back, responsive y ausencia de
  secreto en storage.
- **Security regression**: contraseñas/hash ausentes en DB adicional, auditoría, logs, excepciones,
  sesión, URLs y respuestas posteriores.
- Se corrige dentro del flujo tocado el encadenamiento inválido actualmente presente en
  `PasswordChangeController`; las pruebas nuevas deben impedir su regresión.

## Threat Model And Evidence Retention

- **Sesión administrativa robada**: cada reset exige la contraseña actual, tiene límite
  independiente y vuelve a comprobar rol, organización, target y estado dentro de la transacción.
- **Enumeración y manipulación**: login usa errores genéricos; Policy y queries con ámbito impiden
  conocer estados o auditoría de otras organizaciones.
- **Replay y carreras**: consumo único, vínculo único sesión-reset, locks e índices serializan
  reset/login/cambio; refresh solo rota la sesión vinculada.
- **CSRF/XSS y exposición del secreto**: mutaciones same-origin con CSRF, respuesta `no-store`,
  secreto efímero en memoria/DOM, sin HTML no confiable ni persistencia en cliente, sesión o logs.
- **Backups y acceso interno**: backups heredan cifrado, acceso restringido y restauración
  controlada del sistema; no contienen texto legible porque el secreto nunca se persiste.
- Los ciclos y eventos de auditoría tienen retención indefinida dentro del sistema en esta entrega:
  no existe borrado automático ni desde la interfaz. Una política institucional futura solo podrá
  reducirla mediante otro cambio aprobado. La reversión con datos exige exportación/backup y
  aprobación.

## Project Structure

### Documentation (this feature)

```text
specs/009-reset-user-password/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── validation-report.md
├── contracts/
│   └── web-endpoints.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Http/Middleware/
│   └── AuthenticateJwtSession.php
└── Modules/
    ├── Audit/Models/AuditLog.php
    └── IdentityAccess/
        ├── Application/Actions/
        ├── Domain/Enums/
        ├── Http/Controllers/
        ├── Http/Requests/
        ├── Models/
        ├── Policies/
        └── Services/
config/session-security.php
database/
├── factories/IdentityAccess/
└── migrations/
resources/
├── js/identity-access/
└── views/identity-access/
    ├── operators/
    └── password-change.blade.php
routes/identity-access.php
tests/
├── Feature/IdentityAccess/
├── Integration/IdentityAccess/
└── Unit/IdentityAccess/
```

**Structure Decision**: Mantener el monolito modular vigente. `IdentityAccess` concentra ciclo,
sesiones, políticas y UI; `Audit` conserva el registro genérico. Los controladores coordinan Form
Requests, acciones y respuestas; las transacciones y estados viven en acciones reutilizables.

## Exception Tracking

No hay excepciones constitucionales.

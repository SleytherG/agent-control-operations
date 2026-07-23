# Web Endpoint Contracts: Fundamentos Visuales

Esta capacidad expone endpoints de maquetación para revisión. No implementa lógica real.

## Demo Endpoints

| Method | Path | View |
|--------|------|------|
| GET | /demo/login?state={state} | `screens.auth.login` |
| GET | /demo/expiry?state={expiry} | `screens.auth.expiry-modal` |
| GET | /demo/operator/dashboard | `screens.operator.dashboard` |
| GET | /demo/operator/register | `screens.operator.register` |
| GET | /demo/operator/history | `screens.operator.history` |
| GET | /demo/admin/dashboard | `screens.admin.dashboard` |
| GET | /demo/daily-closing/{id}?status={status} | `screens.daily-closing.show` |

Todas las rutas bajo middleware `web`. Sin autenticación real.

## Responsive Verification

Las mismas rutas deben renderizar correctamente en:
- 375px (mobile)
- 768px (tablet)
- 1280px (laptop)
- 1440px (desktop)

## State Query Parameters (auth screens)

**GET /demo/login?state=**:
- `error` — mensaje "Credenciales inválidas", input con error
- `disabled` — mensaje "Usuario desactivado"
- `throttled` — mensaje "Demasiados intentos", botón disabled 60s
- `network-error` — banner "Error de conexión"
- `loading` — botón con spinner, inputs disabled

**GET /demo/expiry?expiry=**:
- `warning` — modal con 30s, botones Continuar y Cerrar
- `renewing` — botón Continuar con spinner
- `renewed` — toast "Sesión renovada", modal cerrado
- `expired` — modal "Sesión expirada", redirect a login
- `revoked` — modal "Sesión revocada", redirect a login

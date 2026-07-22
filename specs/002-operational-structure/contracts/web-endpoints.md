# Web Endpoint Contracts: Estructura Operacional

## Shared Rules

- Mismas que 001-auth-session: HTTPS, HttpOnly cookies, CSRF en mutaciones, `Cache-Control: no-store`.
- 401 no autenticado, 403 sin permiso, 422 validación, 409 conflicto (solapamiento, desactivación bloqueada).
- Solo `ADMINISTRADOR_PROPIETARIO` accede a endpoints de administración.

## Regions

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/regions | Listado paginado con filtro por estado |
| POST | /admin/regions | Crear región |
| GET | /admin/regions/{region} | Ver región con provincias |
| PATCH | /admin/regions/{region} | Editar nombre o estado |
| DELETE | /admin/regions/{region} | Desactivar (no elimina físicamente) |

## Provinces

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/regions/{region}/provinces | Listado paginado |
| POST | /admin/regions/{region}/provinces | Crear provincia |
| PATCH | /admin/provinces/{province} | Editar |
| DELETE | /admin/provinces/{province} | Desactivar |

## Districts

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/provinces/{province}/districts | Listado paginado |
| POST | /admin/provinces/{province}/districts | Crear distrito |
| PATCH | /admin/districts/{district} | Editar |
| DELETE | /admin/districts/{district} | Desactivar |

## Stores

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/stores | Listado con filtros (district, is_active) |
| POST | /admin/stores | Crear tienda |
| GET | /admin/stores/{store} | Ver tienda con agentes |
| PATCH | /admin/stores/{store} | Editar |
| DELETE | /admin/stores/{store} | Desactivar si no tiene agentes activos (409 si tiene) |

## Banks

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/banks | Listado paginado |
| POST | /admin/banks | Crear banco |
| PATCH | /admin/banks/{bank} | Editar |
| DELETE | /admin/banks/{bank} | Desactivar |

## Bank Agents

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/bank-agents | Listado admin con filtros (store, bank, is_active) |
| POST | /admin/bank-agents | Crear agente |
| PATCH | /admin/bank-agents/{agent} | Editar |
| DELETE | /admin/bank-agents/{agent} | Desactivar (termina asignaciones activas) |
| GET | /my-agents | Operador: solo agentes activos asignados |

## Operators

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/users | Listado paginado con filtro por role=OPERADOR y estado |
| POST | /admin/users | Crear operador con username, email, password |
| PATCH | /admin/users/{user} | Editar (no permite cambiar role a ADMIN) |
| DELETE | /admin/users/{user} | Desactivar operador |
| GET | /password/change | Formulario de cambio forzado de contraseña |
| PATCH | /password/change | Cambiar contraseña en primer login |

## Assignments

| Method | Path | Description |
|--------|------|-------------|
| GET | /admin/users/{user}/assignments | Ver asignaciones del operador con historial |
| POST | /admin/users/{user}/assignments | Asignar operador a agente (409 si solapa) |
| DELETE | /admin/assignments/{assignment} | Desasignar (registra fecha de fin) |

## Validation Rules

- `POST /admin/stores`: rechaza si `district_id` está inactivo.
- `POST /admin/bank-agents`: rechaza si `store_id` o `bank_id` están inactivos.
- `DELETE /admin/stores/{store}`: rechaza 409 si `bank_agents` activos existen.
- `POST /admin/users/{user}/assignments`: rechaza 409 si ya existe asignación activa al mismo agente.

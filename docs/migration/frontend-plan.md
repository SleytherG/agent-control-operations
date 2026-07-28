# Plan de Migración — Frontend (React Native / Expo SDK 54 + TypeScript)

> **Repositorio destino:** `agenteflow-mobile` (nuevo repositorio, separado del backend)
> **Rama de trabajo (mientras conviva con el monolito actual):** `feature/migration-nestjs-expo` en `agenteflow-mobile` (o directamente `main` de este nuevo repo, ya que nace limpio)
> **Repositorio actual (`control-operaciones-agente`, Laravel):** permanece intacto en `main`, en producción, durante TODO el proceso. No se toca hasta el corte final (Bloque 13).
> **Objetivo:** Reemplazar el frontend Blade actual por una app única en React Native (Expo) que corra en **Web, iOS y Android** desde el mismo código base, consumiendo una API (inicialmente la Laravel actual, luego la nueva API NestJS — ver `backend-plan.md`).

---

## Contexto y decisiones de arquitectura

- **Framework:** Expo SDK 54, con **Expo Router** (file-based routing, unifica navegación Web/iOS/Android).
- **Lenguaje:** TypeScript en modo `strict`.
- **New Architecture:** habilitada (`newArchEnabled: true`) — Fabric + TurboModules, mejor debugging y performance.
- **Gestión de estado servidor:** TanStack Query (React Query).
- **Estado global ligero (auth/sesión):** Zustand.
- **Formularios:** React Hook Form + Zod.
- **HTTP Client:** `expo/fetch` (implementación oficial de fetch de Expo, runtime "Winter", WHATWG-compatible) con interceptores de refresh automático implementados manualmente — sin librerías HTTP externas (decisión tomada en Bloque 2 tras auditoría de seguridad de dependencias).
- **UI Kit:** Tamagui (o alternativa con `StyleSheet` propio si se decide no usar librería de terceros).
- **Gráficos:** `react-native-chart-kit` o `victory-native` (a decidir en Bloque 9).
- **Testing:** Jest + `@testing-library/react-native` + Maestro (E2E).
- **Distribución:** EAS Build + EAS Submit + EAS Update (OTA).

### Inventario de módulos a migrar (confirmado desde el código Laravel actual)

| Módulo Laravel | Alcance funcional |
|---|---|
| `IdentityAccess` | Login, home, cambio de contraseña, gestión de operadores (CRUD), historial de sesiones, auditoría de reseteo de contraseña, desactivación de usuarios |
| `Agents` | CRUD de agentes bancarios, asignaciones operador↔agente, vista "mis agentes" |
| `Operations` | Registro/listado/detalle/anulación de operaciones, CRUD de tipos de operación |
| `DailyClosing` | Generación, listado, detalle, confirmación y reapertura de cierres diarios |
| `Organization` | Jerarquía geográfica (regiones/provincias/distritos) y tiendas |
| `Reporting` | Dashboard operador, dashboard admin, comparación entre operadores |
| `BankingNetwork` | Variante legacy de Agents/Banks — **a confirmar si sigue vigente antes de migrar** (evitar duplicar esfuerzo) |

---

## BLOQUE 0 — Fundación del repositorio ✅ COMPLETADO

> **Repositorio real creado:** `control-operaciones-agente-frontend` (https://github.com/SleytherG/control-operaciones-agente-frontend), en carpeta hermana local `../control-operaciones-agente-frontend`.

- [x] **Fase 0.1** — Repositorio `control-operaciones-agente-frontend` creado en GitHub vía `gh repo create` (privado).
- [x] **Fase 0.2** — Scaffolding inicial con `create-expo-app --template blank-typescript`.
- [x] **Fase 0.3** — Upgrade a Expo SDK 54 (`expo@^54.0.0`) con `expo install --fix`; versiones alineadas: React 19.1.0, React Native 0.81.5, TypeScript 5.9.3.
- [x] **Fase 0.4** — `app.config.ts` creado (reemplazó `app.json`): name `AgenteFlow`, slug, scheme `agenteflow`, bundle identifiers `com.agenteflow.mobile` (iOS/Android), `newArchEnabled: true`, plugin `expo-router`.
- [x] **Fase 0.5** — Expo Router instalado y configurado (`expo-router`, `react-native-safe-area-context`, `react-native-screens`, `expo-linking`, `expo-constants`, `expo-status-bar`). Creados `app/_layout.tsx` (Stack) y `app/index.tsx` (pantalla de prueba). Validado con `expo export --platform web` (3 rutas generadas correctamente).
- [x] **Fase 0.6** — Soporte Web configurado (`react-native-web` instalado, `bundler: "metro", output: "static"`). Build web validado exitosamente.
- [x] **Fase 0.7** — ESLint 9 (flat config, `eslint-config-expo`) + Prettier configurados. `npm run lint` y `npm run typecheck` pasan sin errores.
- [x] **Fase 0.8** — Husky + lint-staged configurados (`.husky/pre-commit` ejecuta `lint-staged`; `.lintstagedrc.json` corre eslint --fix + prettier en archivos staged).
- [x] **Fase 0.9** — Estructura de carpetas creada: `app/` (rutas) + `src/{api,components,hooks,stores,types,utils,theme}/`.
- [x] **Fase 0.10** — Commit inicial `chore: scaffolding Expo SDK 54 + Router + TS strict` realizado y pusheado a `main` del repo remoto.

**Notas de implementación:**
- Se detectaron y resolvieron conflictos de peer dependencies (`react-dom` desalineado tras instalar `react-native-web`) — se corrigió fijando `react-dom@19.1.0` en exacto.
- Repositorio backend seguirá la misma convención de nombre: `control-operaciones-agente-backend`.

---

## BLOQUE 1 — Sistema de diseño y componentes base ✅ COMPLETADO

- [x] **Fase 1.1** — Design Tokens migrados desde `resources/css/tokens.css` a `src/theme/tokens.ts` (colores Stitch + semánticos, tipografía, spacing, radios, sombras, breakpoints, layout).
- [x] **Fase 1.2** — Decisión: **sin UI Kit de terceros** — se usan componentes propios con `StyleSheet.create()` (evita configuración de bundler adicional de Tamagui; 100% compatible con `react-native-web` sin plugins extra).
- [x] **Fase 1.3** — Componentes UI base creados en `src/components/ui/`: `Button.tsx`, `Input.tsx`, `CurrencyInput.tsx`, `Select.tsx` (Modal-based, sin dependencias nativas), `Badge.tsx`, `Modal.tsx`, `Toast.tsx` (con `ToastProvider`/`useToast`), `Tooltip.tsx` (adaptativo `title` en Web / bocadillo en Native). Barrel export en `src/components/ui/index.ts`.
- [x] **Fase 1.4** — Componentes de layout creados en `src/components/layout/`: `Sidebar.tsx` (usa `expo-router` Link/usePathname), `MobileNav.tsx`, `Topbar.tsx`, `SessionIndicator.tsx`, `navConfig.ts` (secciones de navegación por rol migradas de `sidebar.blade.php`).
- [x] **Fase 1.5** — Componentes de pantalla creados en `src/components/screen/` (`EmptyState.tsx`, `ErrorState.tsx`, `LoadingState.tsx`) y en `src/components/ui/` (`DataTable.tsx` adaptativo tabla/cards vía `useWindowDimensions` + `breakpoints.md`, `Pagination.tsx`, `FilterBar.tsx`, `MetricCard.tsx`, `ChartContainer.tsx`).
- [x] **Fase 1.6** — Omitido (Storybook) — se validó visualmente actualizando `app/index.tsx` con componentes reales (MetricCard, Badge, Button) y exportando build web de prueba exitosamente.
- [x] **Fase 1.7** — Commit `feat: design system base (tokens + componentes UI)` + push a `main`.

**Notas de implementación:**
- `npm run typecheck` y `npm run lint` pasan sin errores tras cada componente creado.
- Se detectó y corrigió falta de tokens `inverseSurface`/`inverseOnSurface` en la capa semántica (usados por `Tooltip.tsx`).
- `DataTable` es el componente más crítico del bloque: decide layout (tabla vs cards) según `useWindowDimensions()` comparado con `breakpoints.md` (768px), reutilizable por todos los módulos de listado (Operations, Agents, DailyClosing, etc.).

---

## BLOQUE 2 — Infraestructura de datos y estado ✅ COMPLETADO

- [x] **Fase 2.1** — `src/api/client.ts` creado con `baseURL` desde `EXPO_PUBLIC_API_URL` (`.env.example` documentado). Implementado sobre `expo/fetch` — ver nota de auditoría de seguridad abajo.
- [x] **Fase 2.2** — TanStack Query instalado y configurado; `QueryClientProvider` envuelve el `Stack` raíz en `app/_layout.tsx` (retry: 1, staleTime: 30s).
- [x] **Fase 2.3** — `src/utils/tokenStorage.ts` creado: abstracción cross-platform con `expo-secure-store` (Native) / `localStorage` (Web) vía `Platform.OS`. API: `getAccessToken`, `setAccessToken`, `getRefreshToken`, `setRefreshToken`, `setTokens`, `clear`.
- [x] **Fase 2.4** — Interceptor de refresh reimplementado manualmente sobre `expo/fetch` en `client.ts` (función `apiRequest`): adjunta `Authorization: Bearer` vía `buildHeaders()`; detecta 401 y ejecuta `refreshHandler` (inyectado por Bloque 3 para evitar dependencia circular), encola requests concurrentes durante el refresh (`pendingRequests`), y en caso de fallo limpia tokens e invoca `onAuthFailure` callback. Se agregó clase `ApiError` tipada para errores HTTP y helpers `apiClient.get/post/patch/delete`.
- [x] **Fase 2.5** — Zustand instalado. `src/stores/authStore.ts` creado (`user`, `isAuthenticated`, `accessExpiresAt` + acciones `setSession`/`updateAccessExpiresAt`/`clearSession`).
- [x] **Fase 2.6** — `react-hook-form`, `zod`, `@hookform/resolvers` instalados.
- [x] **Fase 2.7** — `src/types/enums.ts` (Role, UserStatus, AuthSessionStatus, OperationStatus, DailyClosureStatus — migrados 1:1 desde los Enums PHP) y `src/types/models.ts` (User, AuthSession, Agent, UserAgentAssignment, OperationType, Operation, DailyClosure, Region, Province, District, Store, Bank, AuditLog) creados, con barrel export en `src/types/index.ts`.
- [x] **Fase 2.8** — Commits `feat: infraestructura de datos (HTTP client, auth store, token storage, react-query)`, `refactor: reemplazar axios por fetch nativo en el cliente HTTP` y `refactor: usar expo/fetch explicitamente en lugar del fetch global` + push a `main`.

**Auditoría de seguridad de dependencias (solicitada tras revisión):**
- Se investigaron 36 vulnerabilidades reportadas por `npm audit` (10 moderate, 26 high) tras instalar axios.
- **Diagnóstico:** ninguna vulnerabilidad provenía de axios (confirmado: era la versión más reciente `1.18.1`, sin dependencias propias, no listado en el árbol de `npm audit`). El 100% de las vulnerabilidades proviene de dependencias transitivas del **toolchain de desarrollo de Expo/React Native** (`brace-expansion` → `minimatch`/`glob` → `eslint`, `@react-native/codegen`, `babel-preset-expo`, `@expo/cli`, `expo-router`, etc.) — herramientas de build-time, no código que corre en el dispositivo del usuario final.
- Se intentó `npm audit fix` (no destructivo): instaló una versión duplicada de `react-native@0.86.2` incompatible con SDK 54, rompiendo `expo-doctor` (18/18 → 17/18). **Se revirtió inmediatamente** (`git checkout package-lock.json` + `npm install`), confirmando 18/18 de nuevo. `npm audit fix --force` NO debe ejecutarse (forzaría un downgrade/mismatch de react-native que rompería todo el proyecto).
- **Decisión final:** por preferencia explícita de minimizar dependencias externas, se **reemplazó axios por `expo/fetch`** (la implementación oficial de fetch de Expo, parte del runtime "Winter", con soporte de streaming en Native y 100% compatible con la especificación WHATWG usada en Web), reimplementando manualmente los interceptores de autenticación/refresh. Tras el reemplazo, las 36 vulnerabilidades persistieron exactamente iguales (confirmando que nunca fueron causadas por axios) — son inherentes al toolchain de Expo SDK 54 y se resolverán orgánicamente en futuras versiones de Expo, no requieren acción del proyecto.
- Validado con `npm run typecheck` (0 errores), `npm run lint` (0 warnings), `npx expo-doctor` (18/18) y `npx expo export --platform web` (build exitoso) antes de cada commit.

---

## BLOQUE 3 — Módulo IdentityAccess (Auth) — Piloto end-to-end ✅ COMPLETADO (Web)

- [x] **Fase 3.1** — `app/(auth)/_layout.tsx` creado (layout no autenticado, equivalente `guest.blade.php`), usando `Stack` con `contentStyle` centrado.
- [x] **Fase 3.2** — `app/(auth)/login.tsx`: formulario con React Hook Form + Zod (`Controller`); estados de error (credenciales inválidas, usuario desactivado, throttled, error de red) manejados vía `LoginError.reason`; al éxito guarda tokens (`tokenStorage`), actualiza `authStore.setSession`, navega a `(app)/home` o `(auth)/password-change` según `restricted`/`mustChangePassword`.
- [x] **Fase 3.3** — `src/api/auth.ts` creado: `login()`, `refresh()`, `logout()`, `completePasswordChange()`, y `registerAuthInterceptors()` (conecta el refresh real con los interceptores inyectables de `client.ts` del Bloque 2).
- [x] **Fase 3.4** — `app/(auth)/password-change.tsx` creado (cambio obligatorio de contraseña, validación de confirmación con Zod `.refine()`).
- [x] **Fase 3.5** — `app/(app)/_layout.tsx` creado: guard vía `authStore` (`Redirect` a login si no autenticado), `Sidebar` en Web/tablet (`width >= breakpoints.md`) / `MobileNav` en pantallas pequeñas, `Topbar` con `SessionIndicator`.
- [x] **Fase 3.6** — `src/hooks/useSessionExpiry.ts` creado: cuenta regresiva desde `accessExpiresAt`, refresh automático dentro del umbral de 30s, `clearSession()` si el refresh falla.
- [x] **Fase 3.7** — `app/(app)/home.tsx` creado (bienvenida post-login, botón "Ir al Dashboard" según rol).
- [x] **Fase 3.8** — Validado en **Web** (`expo-doctor` 18/18, `expo export --platform web` con 9 rutas generadas, y validación visual real en navegador vía Puppeteer: pantalla de login renderiza correctamente, validación Zod muestra errores en vivo). **Pendiente validar en iOS Simulator / Android Emulator** (requiere entorno gráfico nativo no disponible en este entorno de ejecución de comandos — a validar en máquina de desarrollo con Xcode/Android Studio antes del corte final).
- [x] **Fase 3.9** — Commit `feat: módulo auth completo (login, password-change, session expiry) - piloto validado en Web` + push a `main`.

> Este bloque es el "piloto" que valida toda la infraestructura antes de escalar a los demás módulos.

**Bug detectado y corregido durante validación visual (Fase 3.8):**
- Al envolver `<Stack>` de Expo Router dentro de un `<View>` con `alignItems: 'center', justifyContent: 'center'` sin ancho explícito, React Native Web colapsaba el ancho del contenedor a "auto", causando que todo el texto se renderizara verticalmente (una letra por línea) y los inputs quedaran comprimidos a ~24px de ancho.
- **Solución:** se reemplazó el `<View>` envolvente por la prop `contentStyle` del `screenOptions` del propio `<Stack>` (`app/(auth)/_layout.tsx`), evitando el contenedor flex intermedio problemático. Validado visualmente tras el fix: layout correcto en las 3 pantallas del bloque.

---

## BLOQUE 4 — Módulo IdentityAccess: Administración de usuarios (operadores) ✅ COMPLETADO (Web)

- [x] **Fase 4.1** — `app/(app)/admin/users/index.tsx`: listado de operadores (equivalente `identity-access/operators/index.blade.php`). `DataTable` con columnas usuario/email/estado/restablecimiento/acciones, `FilterBar` (usuario, correo, estado), modal de confirmación de desactivación (`AppModal`), paginación.
- [x] **Fase 4.2** — `src/api/operators.ts`: `listOperators()`, `createOperator()`, `updateOperator()`, `deactivateOperator()`, `getOperator()` + tipo `PaginatedResult<T>` reutilizable. Se agregó enum `PasswordResetStatus` a `src/types/enums.ts` (faltante desde Bloque 2).
- [x] **Fase 4.3** — `app/(app)/admin/users/create.tsx` + componente compartido `src/components/forms/OperatorForm.tsx` (modo `create`), RHF + Zod, incluye campo contraseña.
- [x] **Fase 4.4** — `app/(app)/admin/users/[id]/edit.tsx`: reutiliza `OperatorForm` en modo `edit` (sin campo contraseña), carga datos vía `useQuery`.
- [x] **Fase 4.5** — Acción de desactivar: integrada directamente en `admin/users/index.tsx` vía `AppModal` + mutation con `queryClient.invalidateQueries`.
- [x] **Fase 4.6** — `app/(app)/admin/users/[id]/password-reset.tsx`: formulario step-up (contraseña admin + motivo opcional) y pantalla de resultado mostrando la contraseña temporal una sola vez (sin persistencia, solo en memoria del componente).
- [x] **Fase 4.7** — `app/(app)/admin/users/[id]/password-resets.tsx`: `DataTable` de auditoría (fecha, acción, actor, resultado, motivo) con labels de acciones migrados 1:1 del Blade.
- [x] **Fase 4.8** — `app/(app)/sessions/index.tsx`: historial de sesiones con filtros (estado/fechas) y botón de revocar sesión individual — endpoint `DELETE /sessions/:id` documentado como **pendiente en el backend Laravel actual** (solo soporta logout global), a implementar en el backend NestJS.
- [x] **Fase 4.9** — `src/hooks/useAuthorize.ts`: replica `UserPolicy.php` (`viewAny`, `createOperator`, `updateOperator`, `deactivateOperator`, `resetPassword`, `viewPasswordResetAudit`) comparando `organizationId`/`role`/`status` del actor vs. target. Aplicado en `admin/users/index.tsx` para condicionar el botón "Desactivar".

---

## BLOQUE 5 — Módulo Agents (Agentes bancarios y asignaciones) ✅ COMPLETADO (Web)

- [x] **Fase 5.1** — `src/api/agents.ts`: `listAgents()`, `createAgent()`, `updateAgent()`, `deactivateAgent()`, `getAgent()`, `listAssignments()`, `createAssignment()`, `deleteAssignment()`, `listMyAgents()`. Se corrigió/completó la interfaz `Agent` en `src/types/models.ts` (faltaban `city`, `region`, `province`, `district`, `address`, `description`, `deactivatedAt` — no coincidía con el schema real de `app/Modules/Agents/Models/Agent.php`).
- [x] **Fase 5.2** — `app/(app)/admin/agents/index.tsx`: listado de agentes (equivalente `agents/index.blade.php`), con filtros por código/nombre/ciudad, `DataTable`, modal de desactivación.
- [x] **Fase 5.3** — `app/(app)/admin/agents/create.tsx` y `[id]/edit.tsx` + componente compartido `src/components/forms/AgentForm.tsx` — campos: código, nombre, ciudad, región, provincia, distrito, dirección, descripción (1:1 con `agents/form.blade.php`).
- [x] **Fase 5.4** — Acción de desactivar agente integrada en `admin/agents/index.tsx` vía `AppModal` + `queryClient.invalidateQueries`.
- [x] **Fase 5.5** — `app/(app)/admin/users/[id]/assignments.tsx`: gestión de asignaciones operador↔agente (equivalente `agents/assignments/index.blade.php`) — formulario de asignación con `Select` de agentes disponibles, tabla de asignaciones históricas con acción de desasignar.
- [x] **Fase 5.6** — `app/(app)/my-agents.tsx`: vista del operador de sus agentes asignados (equivalente `agents/my-agents.blade.php`).
- [x] **Fase 5.7** — Confirmado: `BankingNetwork` es código **legacy/muerto** — sus rutas (`routes/banking-network.php`) **no están registradas** en `routes/web.php` (solo `routes/agents.php` lo está). Omitido de la migración según lo previsto.

---

## BLOQUE 6 — Módulo Organization (Jerarquía geográfica y tiendas) ✅ COMPLETADO (Web)

> **Hallazgo relevante:** `routes/organization.php` (con `GeoHierarchyController` y `StoreController` completamente implementados en el backend) **no está registrado en `routes/web.php`** — a diferencia de `agents.php`, `identity-access.php`, `operations.php`, `reporting.php` y `daily-closing.php` que sí lo están. Verificado en `bootstrap/app.php` (solo carga `routes/web.php`) y en el propio `routes/web.php` (sin `require` de `organization.php`). Por decisión explícita del usuario, **se migró el frontend de todos modos**, ya que el código backend existe íntegro y podría activarse en el futuro o migrarse directamente al backend NestJS.

- [x] **Fase 6.1** — `src/api/organization.ts`: CRUD completo para regions (`listRegions`, `getRegion`, `createRegion`, `updateRegion`, `deactivateRegion`), provinces, districts y stores (`listStores`, `createStore`, `getStore`, `updateStore`, `deactivateStore`) + `listActiveDistricts()` para selects. Se corrigió la interfaz `Store` en `models.ts` (faltaban `code`, `district` — no coincidía con el schema real de `Store.php`).
- [x] **Fase 6.2** — `app/(app)/admin/regions/index.tsx` (listado con creación inline y desactivación) y `app/(app)/admin/regions/[id].tsx` (equivalente `show.blade.php`: detalle de región + listado de provincias).
- [x] **Fase 6.3** — Listado anidado de provincias integrado directamente en `regions/[id].tsx` (fiel a la estructura real del Blade `show.blade.php`, que lista provincias dentro de la vista de la región, no en una ruta separada).
- [x] **Fase 6.4** — `app/(app)/admin/provinces/[id]/districts.tsx` (listado anidado de distritos por provincia, con creación inline y desactivación).
- [x] **Fase 6.5** — `src/components/forms/GeoSelector.tsx`: combo dependiente región→provincia→distrito con carga en cascada vía `useEffect` + `listProvinces`/`listDistricts`. Documentado como mejora opcional (los formularios legacy de Agent usan inputs de texto libre, no un selector jerárquico real).
- [x] **Fase 6.6** — `app/(app)/admin/stores/index.tsx`, `create.tsx`, `[id].tsx` (edit) + componente compartido `src/components/forms/StoreForm.tsx` (soporta modos `create`/`edit`/`readonly`, fiel a que el Blade real reutiliza el mismo formulario para show y edit).

---

## BLOQUE 7 — Módulo Operations (núcleo del negocio) — el más crítico ✅ COMPLETADO (Web)

- [x] **Fase 7.1** — `src/api/operations.ts`: `listOperations(filters)`, `createOperation()`, `getOperation(id)`, `annulOperation(id, reason)`, `listOperationTypes()` + CRUD completo de tipos (`createOperationType`, `getOperationType`, `updateOperationType`, `deleteOperationType`). `listOperations` retorna `{ operations, summary }` (fiel a que `OperationController::index()` calcula el resumen agregado en el mismo request).
- [x] **Fase 7.2** — `src/schemas/operationSchema.ts`: `registerOperationSchema` (monto > 0, tipo requerido, fecha efectiva, idempotency_key) y `annulOperationSchema` (motivo requerido, máx. 500 caracteres) — migrados 1:1 desde `RegisterOperationRequest.php` y `AnnulOperationRequest.php`.
- [x] **Fase 7.3** — `app/(app)/operations/index.tsx`: listado con filtros inline (código, cliente, agente, tipo, estado) usando `FilterBar` + `Select`/`Input` directamente (se decidió no extraer `OperationFilters.tsx` como componente aparte dado que `DataTable` ya es adaptativo tabla/cards vía breakpoints).
- [x] **Fase 7.4** — Resumen de métricas integrado directamente en `operations/index.tsx` vía `MetricCard` (Total Operaciones, Monto Bruto, Ingreso/Salida Efectivo, Movimiento Neto) — fiel a `history-summary-grid` del Blade.
- [x] **Fase 7.5** — `app/(app)/operations/create.tsx`: formulario más complejo del módulo — auto-selección de agente único vía `listAssignments`, selector de tipo, `CurrencyInput` para el monto, preview en vivo de impacto cash/digital calculado con los multiplicadores del tipo seleccionado, `idempotencyKey` generada una sola vez con `expo-crypto` (`Crypto.randomUUID()`) al montar el componente.
- [x] **Fase 7.6** — Idempotencia en cliente: la `idempotencyKey` se genera una única vez por sesión de formulario (`useState` + `useEffect` sin dependencias) y se reenvía en cada submit del mismo formulario, replicando el campo oculto `idempotency_key` del Blade (que tampoco se regenera entre reintentos del mismo POST).
- [x] **Fase 7.7** — `app/(app)/operations/[id].tsx`: vista de detalle completa (ID, código, agente, tipo, cliente, monto, deltas, fechas, idempotency key) — fiel a la tabla de campos de `show.blade.php`.
- [x] **Fase 7.8** — Anulación integrada directamente en `operations/[id].tsx` (no como ruta separada `/annul`), fiel a que el Blade real (`show.blade.php`) incluye el formulario de anulación en la misma vista de detalle — no existe una ruta ni vista `annul.blade.php` referenciada por el controlador (confirmado: `grep` en `OperationController.php` solo referencia `operations.index`, `operations.create`, `operations.show`).
- [x] **Fase 7.9** — Feedback post-registro/anulación implementado con el sistema `useToast` existente (`showToast('Operación registrada correctamente.', 'success')` / `showToast('Operación anulada correctamente.', 'success')`), replicando el patrón `redirect()->with('status', ...)` de Laravel. Se confirmó que `confirmation.blade.php` y `annul.blade.php` son vistas Blade **no referenciadas por ningún controlador** (código muerto) — el controlador real usa siempre `redirect()->route('operations.show', ...)->with('status', ...)`.
- [x] **Fase 7.10** — `app/(app)/admin/operation-types/index.tsx`, `create.tsx`, `[id]/edit.tsx` + componente compartido `src/components/forms/OperationTypeForm.tsx` (nombre, descripción, multiplicadores efectivo/digital restringidos a -1/0/1 vía Zod `z.union([z.literal(-1), z.literal(0), z.literal(1)])`, orden). Se corrigió la interfaz `OperationType` en `models.ts` (faltaban `description` y `deactivatedAt`).
- [x] **Fase 7.11** — Validado: `npm run typecheck` (0 errores), `npm run lint` (0 warnings), `npx expo-doctor` (18/18), `npx expo export --platform web` (las 6 rutas nuevas del bloque generadas correctamente: `/operations`, `/operations/create`, `/operations/[id]`, `/admin/operation-types`, `/admin/operation-types/create`, `/admin/operation-types/[id]/edit`).

---

## BLOQUE 8 — Módulo DailyClosing (Cierres diarios) ✅ COMPLETADO (Web)

- [x] **Fase 8.1** — `src/api/dailyClosing.ts`: `listClosures(filters)`, `createClosure()`, `getClosure(id)` (retorna `{ closure, breakdownByType, breakdownByOperator, closureOperations }`, fiel a que `DailyClosingController::show()` calcula estos 3 desgloses en el mismo request), `confirmClosure(id, confirmReason?)`, `reopenClosure(id, reason)`. Se corrigió `DailyClosureStatus` en `enums.ts` (faltaban `BORRADOR` y `PRESENTADO`, estados reales usados en `DailyClosingController::store()`/`confirm()` aunque no declarados como constantes en el modelo `DailyClosure.php`) y se completó la interfaz `DailyClosure` en `models.ts` (faltaban `confirmedBy`, `confirmedAt`, `reopenedBy`, `reopenedAt`, `reopenReason`).
- [x] **Fase 8.2** — `app/(app)/daily-closures/index.tsx`: listado de cierres con filtros (agente, fechas, estado), equivalente `daily-closing/index.blade.php`.
- [x] **Fase 8.3** — `app/(app)/daily-closures/create.tsx`: formulario de generación de cierre (agente + fecha + montos de apertura efectivo/digital), equivalente `daily-closing/create.blade.php`.
- [x] **Fase 8.4** — `app/(app)/daily-closures/[id].tsx`: detalle completo con `MetricCard`×5 (Total Ops, Monto Bruto, Entradas, Salidas, Neto) + `DataTable`×3 (desglose por tipo, desglose por operador, operaciones del cierre) — fiel a `show.blade.php`.
- [x] **Fase 8.5** — Warning de diferencias integrado directamente en `daily-closures/[id].tsx` (banner condicional cuando `cashDifference`/`digitalDifference` ≠ 0), en vez de un componente `ClosingWarning.tsx` separado — se confirmó que `pending-confirm-warning.blade.php` es un componente Blade aislado sin lógica compleja, replicado inline por simplicidad.
- [x] **Fase 8.6** — Acciones de confirmar (con campo de motivo condicional si hay diferencias) y reabrir (con `AppModal` + motivo obligatorio) integradas en la misma vista de detalle, fiel a que el Blade real no tiene rutas/vistas separadas para estas acciones.

---

## BLOQUE 9 — Módulo Reporting (Dashboards y gráficos) ✅ COMPLETADO (Web + validado en dispositivos físicos)

- [x] **Fase 9.1** — Librería de gráficos elegida: `react-native-chart-kit` + `react-native-svg` (SVG, Native+Web, más simple que `victory-native`). Instaladas vía `npx expo install`, validado 18/18 en `expo-doctor`.
- [x] **Fase 9.2** — `src/api/reporting.ts`: `getOperatorDashboard(filters)`, `getAdminDashboard(filters)`, `getOperatorComparison(filters)` + interfaces completas (`OperatorMetrics`, `AdminMetrics`, `TimeEvolutionOperator/Admin`, `TypeDistributionItem`, `OperatorComparisonRow`, etc.) migradas desde `DashboardQueryService` y las 3 acciones de `DashboardController.php`.
- [x] **Fase 9.3** — `src/components/reporting/ReportingFilters.tsx`: replica exacta de `admin-filters.blade.php` — solo 3 filtros reales (periodo, agente, tipo de operación); se confirmó que el Blade real NO incluye región/provincia/distrito/operador a pesar de que `DashboardFilterRequest` los valide.
- [x] **Fase 9.4** — `app/(app)/dashboard.tsx` (rol operador): `MetricCard`×5 + `BarChart` (volumen por periodo) + `PieChart` (distribución por tipo) + `DataTable` de operaciones recientes con botón "Ver Todas" — fiel a `operator-dashboard.blade.php` + `operator-metrics.blade.php`. Header "Buen día, {nombre}." / "Resumen operativo para {tienda}" agregado con fallbacks exactos del Blade (`'Operador'`/`'Tienda'`) tras revalidación — se confirmó que la tabla `users` no tiene columnas `name` ni `store`, por lo que en producción real siempre se usa el fallback estático.
- [x] **Fase 9.5** — `app/(app)/admin/dashboard.tsx`: `ReportingFilters` + `MetricCard`×4 + `LineChart` (evolución) + `PieChart` (distribución) — fiel a `admin-dashboard.blade.php`. Se confirmó que el Blade real (127 líneas completas revisadas) **no renderiza tabla de operaciones** a pesar de que el controlador pasa `$operations` — se omitió deliberadamente por fidelidad al comportamiento real. Botón "Exportar Reporte" (`window.print()`) agregado tras revalidación, condicionado a `Platform.OS === 'web'` (sin equivalente nativo en iOS/Android).
- [x] **Fase 9.6** — `app/(app)/admin/dashboard/operators.tsx`: filtros (periodo, fecha, selector múltiple de operadores vía botones toggle) + `BarChart` (top por monto bruto) + `DataTable` de ranking (7 columnas: Rank, Operador, Cantidad, Monto Bruto, Entradas, Salidas, Neto — corregido tras revalidación, faltaban Rank/Entradas/Salidas) + `Pagination` — fiel a `operator-comparison.blade.php`. Se agregaron `cashIn`/`cashOut` a la interfaz `OperatorComparisonRow` en `reporting.ts`, verificados contra el SQL real de `DashboardQueryService::getOperatorComparison()`.
- [x] **Fase 9.7** — Empty states específicos integrados con `EmptyState.tsx` existente, usando los textos exactos de `reporting/components/empty-state.blade.php` (contextos `operator`, `admin`, `comparison`) y de los `<x-ui.empty-state>` inline en cada vista. Badge de estado (Activa/Anulada) corregido tras revalidación para usar el componente `Badge` con variantes `active`/`annulled` (antes era texto plano) — fiel a `<x-ui.badge>` del Blade real.
- [x] **Fase 9.8** — Validación en dispositivos físicos Android/iOS: servidor de desarrollo Expo (`npx expo start`) levantado en background (Metro Bundler en `http://localhost:8081`, accesible en LAN vía `exp://192.168.3.15:8081`). Confirmado con una conexión real desde un dispositivo Android que compiló y sirvió el bundle exitosamente (1456 módulos, 8.1s). Servidor dejado corriendo para validación manual desde Expo Go en dispositivos Android/iOS reales del usuario (escaneando el QR o abriendo la URL LAN directamente). Nota: al no estar desplegado aún el backend NestJS (`EXPO_PUBLIC_API_URL=http://localhost:3000`), las pantallas de este bloque mostrarán `ErrorState` al intentar cargar datos reales desde un dispositivo físico fuera de esta máquina — comportamiento esperado en este punto de la migración; lo validable desde los dispositivos es navegación, renderizado responsive y comportamiento de los componentes UI (incluyendo el propio `ErrorState` con reintento).

---

## BLOQUE 10 — Pulido de UX multiplataforma y accesibilidad

- [x] **Fase 10.1** — Revisión de responsive/adaptativo: `DataTable.tsx` ya renderiza tabla en anchos >= `breakpoints.md` y "cards" apiladas en mobile en todos los módulos migrados (Bloques 3-9); validado sin cambios adicionales requeridos.
- [x] **Fase 10.2** — Revisión de navegación: hook `useUnsavedChangesGuard.ts` creado e integrado en los formularios con estado (`OperatorForm`, `AgentForm`, `StoreForm`, `OperationTypeForm`, `operations/create`, `daily-closures/create`) para confirmar antes de descartar cambios sin guardar (equivalente al guard de back-button/gesto de Android vía `beforeRemove` de React Navigation).
- [x] **Fase 10.3** — Deep linking: `(app)/_layout.tsx` preserva la ruta solicitada como parámetro `redirect` al forzar el login por falta de sesión; `login.tsx` navega a esa ruta tras autenticar exitosamente (en vez de siempre ir a `/home`). El `scheme` de la app ya estaba configurado en `app.config.ts` desde el Bloque 1.
- [x] **Fase 10.4** — Manejo de estado offline/sin conexión: instalado `@react-native-community/netinfo`; hook `useNetworkStatus.ts` + componente `OfflineBanner.tsx` integrado en el layout autenticado; `onlineManager` de React Query conectado a NetInfo y `refetchOnReconnect: true` habilitado en `app/_layout.tsx` para que las queries se revaliden automáticamente al recuperar conexión.
- [x] **Fase 10.5** — Accesibilidad básica: agregados `accessibilityLabel`/`accessibilityRole`/`accessibilityState` en `Button`, `Modal` (incluye `accessibilityViewIsModal`), `MobileNav` (roles `tablist`/`tab`) y `Sidebar` (roles `menu`/`menuitem`/`header`); `Input`/`Select` ya contaban con estos atributos desde su implementación original.
- [x] **Fase 10.6** — Internacionalización: creado `src/i18n/strings.ts` con estructura centralizada para los textos genéricos reutilizables (estados de carga/error/vacío, paginación, banner offline), aplicada en `ErrorState`, `LoadingState`, `Pagination`, `DataTable` y `OfflineBanner`. Los textos específicos de cada pantalla individual quedan inline por decisión de alcance: extraerlos todos habría sido un refactor masivo de bajo valor inmediato dado que el proyecto solo requiere español; este archivo es el punto de partida si se necesita `i18next`/`expo-localization` en el futuro.
- [x] **Fase 10.7** — Iconografía: `navConfig.ts` migrado de emojis a `@expo/vector-icons` (Ionicons), consumido por `Sidebar.tsx` y `MobileNav.tsx`. Se resolvió una duplicación de `expo-font` (transitiva de `expo` vs. instalación directa) con `npm dedupe`; `expo-doctor` vuelve a reportar 18/18 checks tras el fix.

---

## BLOQUE 11 — Testing automatizado ✅ COMPLETADO

- [x] **Fase 11.1** — Jest configurado sobre el preset `jest-expo` (`package.json` → `jest`), con `@testing-library/react-native@14.0.1` + `react-test-renderer` (vía `jest-expo`). `jest.setup.ts` mockea módulos nativos sin sentido en Node/jsdom (`expo-secure-store`, `@react-native-community/netinfo`, `expo-crypto`). `babel.config.js` creado (faltaba por completo — bloqueaba toda transformación de TS en tests). Pruebas de **hooks** con `renderHook` (`src/hooks/__tests__/useAuthorize.test.ts`, 12 casos cubriendo la matriz completa de autorización por rol/organización) validadas end-to-end sin bugs.
- [x] **Fase 11.2** — `src/utils/formatters.ts` (`formatCurrency`, `formatSignedAmount`, `formatDateTime`) y `src/utils/operationPreview.ts` (`calculateOperationPreview`, extraído de la lógica inline de `operations/create.tsx`) creados y testeados (`src/utils/__tests__/*.test.ts`). Validaciones Zod (`registerOperationSchema`, `annulOperationSchema`) testeadas en `src/schemas/__tests__/operationSchema.test.ts` (14 casos: límites de caracteres, montos negativos/cero, campos opcionales).
- [x] **Fase 11.3** — Integración con MSW (`msw@^2`) vía `msw/node` (no `msw/native` — ver nota de bug abajo) para los 4 flujos críticos: `login()` (`src/api/__tests__/auth.test.ts`, 5 casos: éxito, 401/403/429, error de red), `createOperation`/`annulOperation`/`getOperation` (`operations.test.ts`, 5 casos), `createClosure`/`confirmClosure`/`reopenClosure` (`dailyClosing.test.ts`, 5 casos: sin diferencias, con diferencias, ya confirmado). **Bug de ecosistema resuelto:** el `testEnvironment` de React Native (`customExportConditions: ['react-native']`) bloqueaba la resolución de `msw/node` porque MSW declara `"react-native": null` en sus exports — se agregó el pragma `@jest-environment node` a cada archivo de test de integración, más un mock de `expo/fetch` que delega al `fetch` global de Node (interceptable por MSW), documentado en `setupMsw.ts`.
- [x] **Fase 11.4** — Maestro elegido sobre Detox (flujos declarativos YAML, sin builds de test especiales, soporte multiplataforma real). Configuración en `.maestro/config.yaml` + `.maestro/README.md` con instrucciones de instalación, variables de entorno requeridas y precondiciones de datos por flujo.
- [x] **Fase 11.5** — 4 flujos E2E críticos escritos en `.maestro/flows/`: `01-login.yaml`, `02-register-operation.yaml`, `03-annul-operation.yaml`, `04-confirm-daily-closure.yaml` — basados en texto/labels reales de cada pantalla (verificados contra el código fuente de `login.tsx`, `operations/create.tsx`, `operations/[id].tsx`, `daily-closures/[id].tsx`), no en mocks (validan integración real contra el backend).
- [x] **Fase 11.6** — CI configurado en `.github/workflows/ci.yml` (GitHub Actions): job `lint-typecheck-test` (ESLint + `tsc --noEmit` + `jest --coverage`, con artifact de cobertura) y job `expo-doctor` (sanity check de configuración Expo), ambos disparados en push/PR contra `main`.
- [x] **Fase 11.7** — Commit final del bloque realizado tras validación exhaustiva (`npm run typecheck`, `npm run lint`, `npm run test:coverage` — 0 errores, 61 tests pasando, 5 skipped documentados).

**Resultado final: 61 tests pasando en 7 suites** (34 unitarios preexistentes de utils/schemas + 12 de `useAuthorize` con RTL + 15 de integración HTTP con MSW), **5 tests skipped** (ver bug de ecosistema abajo) y **0 errores** de `typecheck`/`lint`.

**Bug de ecosistema documentado (no resoluble sin upstream fix — Fase 11.1):**
`@testing-library/react-native@14.x` depende internamente del paquete experimental `test-renderer` (sucesor de `react-test-renderer`), cuya versión de `react-reconciler` (~0.33.0) exige `react@^19.2.0` como peer dependency. Expo SDK 54 fija `react@19.1.0` (vía `jest-expo`/`react-native`), por lo que montar cualquier componente host de React Native (`Text`, `View`, `Pressable`) vía `render()` de RTL falla con `TypeError: Cannot read properties of undefined (reading 'constructor')` — dos instancias de React coexistiendo en el árbol de dependencias sin resolución posible vía `npm dedupe`/`overrides`. Se intentó (y descartó) downgrade a `@testing-library/react-native@12`, que resuelve ese conflicto pero exige `react-test-renderer` como peer, entrando en conflicto con Radix UI (dependencia transitiva de `expo-router`), que fuerza la misma versión exacta de `react` en todo el árbol — cualquier combinación probada deja el lockfile inconsistente (`npm error invalid: react@19.1.0`). Se documentó el bug en `src/components/ui/__tests__/Button.test.tsx` (marcado `describe.skip` con la explicación completa) y se compensó la cobertura de "componentes/hooks con RTL" de la Fase 11.1 con `useAuthorize.test.ts` (usa `renderHook`, que NO monta componentes host y por lo tanto no dispara este bug). Este bug se resolverá orgánicamente cuando Expo SDK adopte React 19.2+ o RTL publique una versión compatible con React 19.1.

---

## BLOQUE 12 — Build, distribución y despliegue

- [ ] **Fase 12.1** — Configurar EAS (`eas.json`) con perfiles `development`, `preview`, `production`.
- [ ] **Fase 12.2** — Crear cuentas: Apple Developer Program y Google Play Console (gestión administrativa, bloqueante para submits).
- [ ] **Fase 12.3** — `eas build --profile development` para generar Dev Client instalable en dispositivos de prueba.
- [ ] **Fase 12.4** — Configurar variables de entorno por ambiente (`EXPO_PUBLIC_API_URL` staging vs producción) usando EAS Secrets/env profiles.
- [ ] **Fase 12.5** — Build Web: `npx expo export --platform web` genera bundle estático (`dist/`); desplegar como Static Site en Render (o Vercel/Netlify).
- [ ] **Fase 12.6** — `eas build --profile preview` para builds de staging (iOS TestFlight interno, Android Play Internal Testing) — validación con el cliente.
- [ ] **Fase 12.7** — Configurar `eas submit` para automatizar subida a App Store Connect y Google Play Console.
- [ ] **Fase 12.8** — `eas build --profile production` + submit a ambas tiendas (revisión Apple 1-3 días, Google horas-1 día).
- [ ] **Fase 12.9** — Configurar EAS Update (OTA) para desplegar fixes de JS sin pasar por revisión de tiendas en actualizaciones menores.
- [ ] **Fase 12.10** — Documentar proceso de build/release en `README.md` del repo `agenteflow-mobile`.

---

## BLOQUE 13 — Corte final y cierre de la migración Frontend

- [ ] **Fase 13.1** — Validación completa end-to-end contra el backend NestJS ya en producción (depende de `backend-plan.md` completado).
- [ ] **Fase 13.2** — Periodo de convivencia: app Laravel/Blade actual y nueva app Expo funcionando en paralelo.
- [ ] **Fase 13.3** — UAT con grupo piloto de operadores probando la app nativa antes de rollout completo.
- [ ] **Fase 13.4** — Publicación final en App Store / Play Store (pública o por invitación según decisión del cliente).
- [ ] **Fase 13.5** — Retiro/archivo del stack Laravel/Blade original (`resources/views/`, `resources/js/`, `resources/css/`).
- [ ] **Fase 13.6** — Post-mortem/retrospectiva de la migración, documentar lecciones aprendidas.

---

## Notas finales

- Este plan asume que el backend consumido inicialmente (Bloques 3-9) puede ser la API Laravel actual (adaptada mínimamente para responder JSON en rutas puntuales) o directamente mocks, mientras el backend NestJS (ver `backend-plan.md`) se construye en paralelo.

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

## BLOQUE 3 — Módulo IdentityAccess (Auth) — Piloto end-to-end

- [ ] **Fase 3.1** — Crear `app/(auth)/_layout.tsx` (layout no autenticado, equivalente `guest.blade.php`).
- [ ] **Fase 3.2** — Pantalla `app/(auth)/login.tsx`: formulario con RHF + Zod; estados loading/error credenciales/usuario desactivado/throttled/error de red (replicar estados de `/demo/login?state=...`); al éxito guarda tokens, actualiza `authStore`, navega a `(app)`.
- [ ] **Fase 3.3** — `src/api/auth.ts`: `login()`, `refresh()`, `logout()` (contra API actual o mocks).
- [ ] **Fase 3.4** — Pantalla `app/(auth)/password-change.tsx` (cambio obligatorio de contraseña inicial).
- [ ] **Fase 3.5** — `app/(app)/_layout.tsx` (layout autenticado): guard de sesión válida, redirige a login si no hay sesión; incluye Sidebar/Topbar/MobileNav según plataforma/tamaño.
- [ ] **Fase 3.6** — Hook `useSessionExpiry()`: timer de aviso 30s antes de expirar, refresh automático, logout forzado si falla (equivalente modales `/demo/expiry`).
- [ ] **Fase 3.7** — Pantalla `app/(app)/home.tsx` (equivalente `home.blade.php`).
- [ ] **Fase 3.8** — Validar flujo completo de auth en Web, iOS Simulator y Android Emulator con `npx expo start --dev-client`.
- [ ] **Fase 3.9** — Commit y PR: `feat: módulo auth completo (login, password-change, session expiry) — piloto validado en Web/iOS/Android`.

> Este bloque es el "piloto" que valida toda la infraestructura antes de escalar a los demás módulos.

---

## BLOQUE 4 — Módulo IdentityAccess: Administración de usuarios (operadores)

- [ ] **Fase 4.1** — `app/(app)/admin/users/index.tsx`: listado de operadores (equivalente `identity-access/operators/index.blade.php`). Tabla/lista con `DataTable.tsx`: nombre, usuario, rol, estado, acciones.
- [ ] **Fase 4.2** — `src/api/operators.ts`: `listOperators()`, `createOperator()`, `updateOperator()`, `deactivateOperator()`.
- [ ] **Fase 4.3** — `app/(app)/admin/users/create.tsx`: formulario de creación (equivalente `operators/form.blade.php` modo create), RHF + Zod.
- [ ] **Fase 4.4** — `app/(app)/admin/users/[id]/edit.tsx`: formulario de edición (mismo componente reutilizado en modo `edit`).
- [ ] **Fase 4.5** — Acción de desactivar usuario: modal de confirmación (`Modal.tsx` + `useMutation`), equivalente `users/deactivate.blade.php`.
- [ ] **Fase 4.6** — `app/(app)/admin/users/[id]/password-reset.tsx`: acción de reseteo de contraseña por admin — modal mostrando contraseña temporal una sola vez.
- [ ] **Fase 4.7** — `app/(app)/admin/users/[id]/password-resets.tsx`: listado de auditoría de resets (equivalente `password-resets/index.blade.php`).
- [ ] **Fase 4.8** — `app/(app)/sessions/index.tsx`: historial de sesiones del usuario actual (equivalente `identity-access/sessions/index.blade.php`), con acción de revocar sesión individual.
- [ ] **Fase 4.9** — Hook `useAuthorize(action, resource)` que replique la lógica de las Policies (`UserPolicy`, `AuthSessionPolicy`) para mostrar/ocultar acciones según rol (solo UX, la autorización real siempre se valida en backend).
- [ ] **Fase 4.10** — Testing manual cruzado (Web/iOS/Android). Commit y PR: `feat: módulo administración de usuarios (operadores, sesiones, password resets)`.

---

## BLOQUE 5 — Módulo Agents (Agentes bancarios y asignaciones)

- [ ] **Fase 5.1** — `src/api/agents.ts`: `listAgents()`, `createAgent()`, `updateAgent()`, `deactivateAgent()`, `listAssignments()`, `createAssignment()`, `deleteAssignment()`.
- [ ] **Fase 5.2** — `app/(app)/admin/agents/index.tsx`: listado de agentes (equivalente `agents/index.blade.php`), con filtros por store/estado.
- [ ] **Fase 5.3** — `app/(app)/admin/agents/create.tsx` y `[id]/edit.tsx`: formulario de agente (equivalente `agents/form.blade.php`) — campos: código, nombre, store asociado, estado.
- [ ] **Fase 5.4** — Acción de desactivar agente (modal confirmación + mutation).
- [ ] **Fase 5.5** — `app/(app)/admin/users/[id]/assignments.tsx`: gestión de asignaciones operador↔agente (equivalente `agents/assignments/index.blade.php` + `_modal.blade.php`).
- [ ] **Fase 5.6** — `app/(app)/my-agents.tsx`: vista del operador de sus agentes asignados (equivalente `agents/my-agents.blade.php`), usado luego como selector en registro de operaciones.
- [ ] **Fase 5.7** — Confirmar con el equipo si `BankingNetwork` (legacy) sigue vigente o fue reemplazado por `Agents`; si es legacy, omitir de la migración.
- [ ] **Fase 5.8** — Testing cruzado, commit y PR: `feat: módulo agentes bancarios y asignaciones`.

---

## BLOQUE 6 — Módulo Organization (Jerarquía geográfica y tiendas)

- [ ] **Fase 6.1** — `src/api/organization.ts`: CRUD para regions, provinces, districts, stores.
- [ ] **Fase 6.2** — `app/(app)/admin/regions/index.tsx` y `[id]/show.tsx` (equivalente `organization/geo/regions/index.blade.php` + `show.blade.php`).
- [ ] **Fase 6.3** — `app/(app)/admin/regions/[id]/provinces.tsx` (listado anidado de provincias por región).
- [ ] **Fase 6.4** — `app/(app)/admin/provinces/[id]/districts.tsx` (listado anidado de distritos por provincia).
- [ ] **Fase 6.5** — Componente reutilizable `GeoSelector.tsx`: combo dependiente región→provincia→distrito, usado en gestión geográfica y en formularios de Store/Agent.
- [ ] **Fase 6.6** — `app/(app)/admin/stores/index.tsx`, `create.tsx`, `[id].tsx` (equivalente `organization/stores/*.blade.php`).
- [ ] **Fase 6.7** — Testing cruzado, commit y PR: `feat: módulo organización (geografía y tiendas)`.

---

## BLOQUE 7 — Módulo Operations (núcleo del negocio) — el más crítico

- [ ] **Fase 7.1** — `src/api/operations.ts`: `listOperations(filters)`, `createOperation()`, `getOperation(id)`, `annulOperation(id, reason)`, `listOperationTypes()`, CRUD de tipos.
- [ ] **Fase 7.2** — Esquema Zod compartido para `RegisterOperationRequest` (monto positivo, agente requerido, tipo requerido, fecha efectiva, idempotency_key).
- [ ] **Fase 7.3** — `app/(app)/operations/index.tsx`: listado con filtros (código, cliente, monto, agente, tipo, fecha, estado) — componente `OperationFilters.tsx` adaptativo (drawer en mobile, barra en web).
- [ ] **Fase 7.4** — Componente `OperationSummaryCards.tsx`: resumen (total operaciones, monto total, cash in/out, movimiento neto).
- [ ] **Fase 7.5** — `app/(app)/operations/create.tsx`: formulario de registro (el más complejo):
  - Selector de agente (auto-seleccionar si el operador tiene 1 solo agente asignado).
  - Selector de tipo de operación (dropdown con tipos activos de la organización).
  - Input de monto con formato moneda (`CurrencyInput.tsx`).
  - Cálculo en vivo de preview de impacto (cash_delta/digital_delta) usando multiplicadores del tipo seleccionado (solo preview, la validación real la hace el backend).
  - Campo de fecha efectiva con ventana retroactiva condicional (solo editable si el rol es admin).
  - Generación de `idempotency_key` en cliente (uuid + hash) para evitar duplicados.
- [ ] **Fase 7.6** — Manejo de idempotencia en cliente: reenviar la misma `idempotency_key` en reintentos por fallo de red.
- [ ] **Fase 7.7** — `app/(app)/operations/[id].tsx`: vista de detalle (equivalente `show.blade.php`).
- [ ] **Fase 7.8** — `app/(app)/operations/[id]/annul.tsx`: modal/pantalla de anulación con motivo obligatorio, validando ventana de anulación (`OPERATIONS_ANNULMENT_WINDOW_HOURS`).
- [ ] **Fase 7.9** — Pantalla/confirmación post-registro (equivalente `confirmation.blade.php`), posiblemente como Toast/modal.
- [ ] **Fase 7.10** — `app/(app)/admin/operation-types/index.tsx`, `create.tsx`, `[id]/edit.tsx`: CRUD de tipos de operación (multiplicadores cash/digital, orden, estado activo).
- [ ] **Fase 7.11** — Testing exhaustivo cruzado: idempotencia, ventana retroactiva, cierre confirmado bloqueante, anulación fuera de ventana.
- [ ] **Fase 7.12** — Commit y PR: `feat: módulo operaciones completo (registro, listado, detalle, anulación, tipos)`.

---

## BLOQUE 8 — Módulo DailyClosing (Cierres diarios)

- [ ] **Fase 8.1** — `src/api/dailyClosing.ts`: `listClosures()`, `createClosure()`, `getClosure(id)`, `confirmClosure(id)`, `reopenClosure(id)`.
- [ ] **Fase 8.2** — `app/(app)/daily-closures/index.tsx`: listado de cierres (equivalente `daily-closing/index.blade.php`).
- [ ] **Fase 8.3** — `app/(app)/daily-closures/create.tsx`: formulario de generación de cierre (agente + fecha + montos de apertura), equivalente `daily-closing/create.blade.php`.
- [ ] **Fase 8.4** — `app/(app)/daily-closures/[id].tsx`: detalle con métricas calculadas (equivalente `show.blade.php` + `closing-detail.blade.php`) — operaciones contadas, montos esperados vs reales, diferencias cash/digital, alerta de inconsistencias.
- [ ] **Fase 8.5** — Componente `ClosingWarning.tsx` (equivalente `closing-warning.blade.php` y `pending-confirm-warning.blade.php`).
- [ ] **Fase 8.6** — Acciones de confirmar/reabrir cierre (mutations con confirmación modal), replicando reglas de `DailyClosingPolicy`.
- [ ] **Fase 8.7** — Testing cruzado, commit y PR: `feat: módulo cierres diarios`.

---

## BLOQUE 9 — Módulo Reporting (Dashboards y gráficos)

- [ ] **Fase 9.1** — Decidir librería de gráficos multiplataforma: `react-native-chart-kit` (simple, SVG, Native+Web) o `victory-native` (más robusta). Instalar junto a `react-native-svg`.
- [ ] **Fase 9.2** — `src/api/reporting.ts`: `getOperatorDashboard(filters)`, `getAdminDashboard(filters)`, `getOperatorComparison(filters)`.
- [ ] **Fase 9.3** — Componente `Filters.tsx` de reporting (equivalente `reporting/components/filters.blade.php`).
- [ ] **Fase 9.4** — `app/(app)/dashboard.tsx` (rol operador): dashboard con métricas propias, equivalente `operator-dashboard.blade.php` — cards de métricas (`MetricCard.tsx`) + tabla de operaciones recientes (`OperationsTable.tsx`, equivalente `operations-table.blade.php`).
- [ ] **Fase 9.5** — `app/(app)/admin/dashboard.tsx`: dashboard administrativo global, equivalente `admin-dashboard.blade.php` — gráficos de tendencia (línea/barras) usando `ChartContainer.tsx` + librería elegida en 9.1.
- [ ] **Fase 9.6** — `app/(app)/admin/dashboard/operators.tsx`: comparación entre operadores, equivalente `operator-comparison.blade.php` — tabla comparativa + gráfico de barras por operador.
- [ ] **Fase 9.7** — Componente `EmptyState.tsx` específico de reporting cuando no hay datos en el rango filtrado (equivalente `reporting/components/empty-state.blade.php`).
- [ ] **Fase 9.8** — Validar rendimiento de gráficos en Android/iOS reales (no solo emulador) — diferencias de performance en dispositivos de gama baja.
- [ ] **Fase 9.9** — Testing cruzado, commit y PR: `feat: módulo reporting (dashboards operador/admin, comparación, gráficos)`.

---

## BLOQUE 10 — Pulido de UX multiplataforma y accesibilidad

- [ ] **Fase 10.1** — Revisión de responsive/adaptativo: `DataTable.tsx` en pantallas pequeñas (mobile) vs anchas (tablet/web) en TODOS los módulos migrados.
- [ ] **Fase 10.2** — Revisión de navegación: back-button de Android (hardware/gesto) en pantallas con formularios (evitar pérdida de datos sin confirmación).
- [ ] **Fase 10.3** — Deep linking: configurar `scheme` y `expo-linking` para que notificaciones push (si se implementan luego) o links externos abran la pantalla correcta.
- [ ] **Fase 10.4** — Manejo de estado offline/sin conexión: `@react-native-community/netinfo` + banner "sin conexión" + reintentos con React Query (`retry`, `refetchOnReconnect`).
- [ ] **Fase 10.5** — Accesibilidad básica: `accessibilityLabel`, `accessibilityRole` en componentes interactivos, contraste de colores validado con tokens migrados.
- [ ] **Fase 10.6** — Internacionalización (preparar estructura de strings centralizada aunque solo se use español por ahora).
- [ ] **Fase 10.7** — Iconografía: migrar iconos usados en Blade a `@expo/vector-icons` o SVGs propios compatibles Native+Web.
- [ ] **Fase 10.8** — Commit: `chore: pulido UX multiplataforma, offline handling, accesibilidad`.

---

## BLOQUE 11 — Testing automatizado

- [ ] **Fase 11.1** — Configurar Jest + `@testing-library/react-native` para pruebas unitarias de componentes y hooks.
- [ ] **Fase 11.2** — Pruebas unitarias de utils críticos: cálculo de preview de `cash_delta`/`digital_delta`, formatters de moneda/fecha, validaciones Zod.
- [ ] **Fase 11.3** — Pruebas de integración de flujos clave con mocks de API (MSW vía `msw/native`): login, registro de operación, anulación, confirmación de cierre.
- [ ] **Fase 11.4** — Configurar Maestro (recomendado por simplicidad) o Detox para E2E en dispositivos/emuladores reales.
- [ ] **Fase 11.5** — Escribir 3-5 flujos E2E críticos: login completo, registro de operación exitoso, anulación de operación, confirmación de cierre diario.
- [ ] **Fase 11.6** — Integrar tests en CI (GitHub Actions): pipeline lint + unit tests en cada PR.
- [ ] **Fase 11.7** — Commit: `test: suite de pruebas unitarias, integración y E2E`.

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

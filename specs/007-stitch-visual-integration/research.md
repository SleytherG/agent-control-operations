# Research: Integración Visual Stitch al Sistema Funcional

**Feature**: 007-stitch-visual-integration
**Date**: 2026-07-22

## Decision 1: Estrategia de inyección de variables al layout

**Decision**: Usar `View::share()` en el middleware `AuthenticateJwtSession` para inyectar `$role`, `$user`, `$sessionExpiresAt` en todas las vistas autenticadas.

**Rationale**: 
- El middleware ya autentica al usuario y conoce su rol y sesión
- `View::share()` evita modificar cada controlador individualmente
- Las vistas existentes que no usan estas variables no se rompen (Laravel ignora variables no referenciadas)
- Es el patrón recomendado para datos de layout compartidos

**Alternatives considered**:
- View Composer: Más complejo, requiere registrar explícitamente cada vista
- Pasar desde cada controlador: Duplicación de código, propenso a omisiones
- Service Provider con `View::composer('layouts.authenticated', ...)`: Válido pero más acoplado al nombre del layout

## Decision 2: Formateo de datos para Chart.js

**Decision**: El `DashboardController` formatea los datos en el servidor usando `@json()` en Blade. Los dashboards reciben los datos ya en formato `{labels: [...], datasets: [...]}`.

**Rationale**:
- Los datos de agregación ya se calculan en el servidor (DashboardQueryService)
- Formatear a estructura Chart.js en el servidor evita lógica de transformación en JS
- `@json($variable)` de Blade escapa correctamente para uso en `<script>` tags
- Elimina el polling del demo (setInterval) — datos inline desde el servidor

**Alternatives considered**:
- Transformar en JS: Duplica lógica, propenso a errores, ejecución en cliente
- API JSON separada: Requiere nueva ruta y fetch, complejidad innecesaria para dashboards server-rendered

## Decision 3: Adaptación de componentes screen para datos reales

**Decision**: Los componentes `x-screen.*` que actualmente aceptan arrays planos mock serán adaptados para aceptar tanto arrays como colecciones/objetos Eloquent. Esto se logra usando `collect()` en el componente o pasando datos pre-formateados desde el controller.

**Rationale**:
- Los componentes demo fueron diseñados con arrays PHP planos (datos mock)
- Los datos reales vienen como colecciones Eloquent o arrays asociativos de servicios
- En lugar de reescribir los componentes, se adaptan mínimamente para aceptar ambos formatos
- La transformación ocurre en el controlador (no en la vista ni en el componente)

**Alternatives considered**:
- Reescribir componentes desde cero: Innecesario, los componentes ya tienen el diseño correcto
- Crear nuevos componentes para producción: Duplicación, viola principio de reutilización

**Componentes que necesitan adaptación**:
1. `x-screen.operation-form`: Cambiar prop `:banks` (array asociativo) por `:assignments` (colección Eloquent de UserBankAgentAssignment)
2. `x-screen.operation-filters`: Aceptar `:agents` y `:types` como colecciones Eloquent o arrays
3. `x-screen.admin-filters`: Aceptar `:regions`, `:provinces`, `:districts`, `:stores`, `:banks`, `:bankAgents`, `:types` como colecciones Eloquent
4. `x-screen.closing-detail`: Aceptar `$closure` como modelo Eloquent en lugar de array asociativo

## Decision 4: LoginState mapping desde errores reales

**Decision**: El `LoginController` usa `session()->flash('login_state', $state)` para comunicar el estado a la vista. La vista usa un bloque `@php` para mapear el estado a mensajes visuales (error, disabled, throttled, network-error).

**Rationale**:
- El controller ya tiene acceso al resultado de autenticación
- Flash session data es el mecanismo estándar de Laravel para pasar datos entre redirects
- El mapeo en la vista (no en el controller) mantiene la lógica de presentación separada
- El rate limiter ya existe en el LoginController

**States mapeados**:
| `login_state` | Origen | Mensaje |
|---------------|--------|---------|
| `normal` | Default / GET | Sin error |
| `error` | `AuthenticateUser` retorna null | "Credenciales incorrectas" |
| `disabled` | Usuario con `status = INACTIVE` | "Usuario desactivado" |
| `throttled` | `RateLimiter::tooManyAttempts()` | "Demasiados intentos. Espere 60s." |
| `loading` | Durante POST (JS) | Botón con spinner |

## Decision 5: Sin API JSON adicional

**Decision**: No se crean nuevas rutas API. Toda la comunicación es mediante formularios HTML tradicionales (POST/redirect) y renderizado server-side.

**Rationale**:
- La constitución prohíbe SPA (Principio IV)
- Los dashboards reciben datos inline mediante `@json()` en Blade, no fetch()
- La renovación de sesión ya funciona mediante POST /auth/refresh con cookies
- No se requiere interactividad en tiempo real

**Alternatives considered**:
- API REST para dashboards: Over-engineering, viola minimal interface principle
- Fetch/AJAX para filtros: Complejidad innecesaria; el refresh de página con parámetros es suficiente

## Decision 6: Estrategia de rollback por módulo

**Decision**: Cada módulo migrado tiene un punto de restauración en git (commit atómico por módulo). Si un módulo falla verificación, se revierte mediante `git checkout` del archivo específico.

**Rationale**:
- Los cambios son exclusivamente en archivos de vista y controladores (no esquema de BD)
- Un `git revert` o `git checkout <file>` restaura el estado anterior sin afectar otros módulos
- Los datos en BD no se modifican, por lo que no hay riesgo de corrupción

**Alternatives considered**:
- Feature flags: Complejidad innecesaria para migración de vistas
- Deploy blue/green: No aplica en hosting compartido

## Decision 7: Pruebas de integración visual

**Decision**: No se crean pruebas de regresión visual automatizadas. La validación se realiza mediante:
1. Smoke test manual por pantalla según acceptance scenarios de la spec
2. `php artisan test` para verificar que toda la lógica de negocio sigue pasando
3. Verificación puntual de que componentes Stitch clave aparecen en el HTML (`assertSee` en tests HTTP existentes)

**Rationale**:
- Alineado con la clarificación Q1 de la spec (pruebas existentes + smoke manual)
- No hay infraestructura de screenshot testing configurada
- El riesgo es bajo porque solo se modifican vistas, no lógica de negocio

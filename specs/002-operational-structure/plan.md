# Implementation Plan: Administración de Estructura Operacional

**Branch**: `002-operational-structure` | **Date**: 2026-07-22 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-operational-structure/spec.md`

## Summary

Extender el módulo `Organization` y crear `BankingNetwork` dentro del monolito modular existente. Implementar CRUD con desactivación lógica para regiones, provincias, distritos, tiendas, bancos y agentes bancarios. Añadir registro de operadores y asignaciones con historial. Toda mutación administrativa requiere rol `ADMINISTRADOR_PROPIETARIO` impuesto en servidor. Los operadores solo ven sus agentes asignados activos.

## Technical Context

**Language/Version**: PHP 8.3; JavaScript ECMAScript Modules; HTML5; CSS3

**Primary Dependencies**: Laravel 13, Eloquent, Blade; reutiliza `lcobucci/jwt` 5.x y servicios del módulo `IdentityAccess`

**Storage**: MySQL 8.0 o MariaDB, InnoDB, migraciones con FK y restricciones; sin Redis

**Time & Money**: UTC persistido, `America/Lima` visible; sin importes monetarios en esta capacidad

**Authentication & Session**: Reutiliza el ciclo JWT + refresh opaco de `001-auth-session`; operador desactivado es rechazado por middleware existente; primer login fuerza cambio de contraseña

**Testing**: PHPUnit Feature; tests de autorización positiva/negativa para cada entidad

**Target Platform**: Apache/Nginx, hosting PHP compartido, document root `public/`

**Project Type**: aplicación web monolítica modular, renderizada en servidor

**Performance Goals**: listados paginados (<1s), creación/edición (<1s), filtros con índices compuestos

**Constraints**: sin SPA, Redis, WebSockets, workers; solo `public/` expuesto; no eliminación física de entidades con dependencias

**Scale/Scope**: hasta 500 tiendas, 2000 agentes, asignaciones ilimitadas con historial

## Constitution Check

*GATE: aprobado antes de Phase 0 y revalidado después de Phase 1.*

- **I. Desarrollo dirigido por especificaciones**: PASS. `spec.md` contiene problema, actores, historias, reglas, aceptación, exclusiones y aclaraciones.
- **II. Entregas pequeñas**: PASS. Esta entrega implementa estructura operacional. Tipos de operación y operaciones quedan para especificaciones futuras.
- **III. Portabilidad económica**: PASS. Mismo stack que 001; sin componentes prohibidos; solo `public/` se expone.
- **IV. Interfaz mínima**: PASS. Blade, HTML semántico, CSS propio; sin SPA ni dependencias frontend nuevas.
- **V. Seguridad del servidor**: PASS. Policies por entidad, Form Requests, scopes Eloquent y middleware existente imponen rol y aislamiento.
- **VI. Sesiones seguras**: PASS. Reutiliza el ciclo completo de 001-auth-session; operador desactivado no renueva.
- **VII. Integridad y trazabilidad**: PASS. Desactivación lógica, auditoría con before/after, sin eliminación física de entidades con dependencias.
- **VIII. Exactitud monetaria/temporal**: PASS. UTC, `America/Lima` y `DECIMAL` para futuros importes de cierres.
- **IX. Privacidad**: PASS. Solo identidad operacional; sin datos de clientes, tarjetas ni biometría.
- **X. Pruebas obligatorias**: PASS. Autorización positiva/negativa, integridad de asignaciones, rechazo de solapamiento.
- **XI. Recursos responsables**: PASS. Listados paginados, índices compuestos, sin N+1 ni colecciones completas.
- **XII. Observabilidad/recuperación**: PASS. Auditoría, migraciones reversibles, backups incluyen nuevas tablas.
- **XIII. Gobernanza**: PASS. Sin excepciones; spec, plan y diseño trazables.

**Post-design re-check**: PASS. Sin componentes prohibidos ni excepciones.

## Module Boundaries

- `Organization`: regiones, provincias, distritos y tiendas (extiende el módulo existente que ya contiene `Organization`).
- `BankingNetwork`: bancos, agentes bancarios, asignaciones operador-agente.
- `IdentityAccess`: se extiende con registro de operadores y cambio forzado de contraseña.
- `Audit`: se reutiliza para registrar cambios en entidades operacionales.

## Data Model — New Tables

### regions, provinces, districts

Jerarquía estricta: `districts.province_id → provinces.id`, `provinces.region_id → regions.id`. Cada nivel tiene nombre único dentro de su padre y `organization_id`. Desactivación lógica con `is_active` y `deactivated_at`.

### stores

`organization_id`, `district_id` FK, nombre/código único, dirección, `is_active`. Índice `(organization_id, is_active)`.

### banks

`organization_id`, código único, nombre, `is_active`. UNIQUE `(organization_id, code)`.

### bank_agents

`organization_id`, `store_id` FK, `bank_id` FK, nombre/código interno único por organización, código de terminal opcional, `is_active`. UNIQUE `(organization_id, code)`. Índices `(store_id, is_active)`, `(bank_id, is_active)`. CHECK `is_active` con tienda y banco activos.

### user_bank_agent_assignments

`user_id` FK, `bank_agent_id` FK, `assigned_at`, `unassigned_at` (nullable), `assigned_by` FK users, `is_active`. Índices `(user_id, is_active)`, `(bank_agent_id, is_active)`. UNIQUE parcial sobre `(user_id, bank_agent_id)` donde `is_active = true`.

## Authorization Flows

1. Middleware `AuthenticateJwtSession` valida JWT, sesión y usuario activo.
2. Policies por entidad: `StorePolicy`, `BankPolicy`, `BankAgentPolicy`, `UserPolicy` (extendido) restringen administración a `ADMINISTRADOR_PROPIETARIO`.
3. `BankAgentPolicy::viewAssigned` fuerza `user_id = current_user` para `OPERADOR`.
4. Controladores llaman `Gate::authorize()` antes de delegar a acciones.
5. Form Requests validan pertenencia a organización activa, unicidad y referencias.

## Validation Strategy

- Form Requests: campos requeridos, longitudes, unicidad compuesta, FK activas y pertenencia a la misma organización.
- Acciones: reglas de negocio (solapamiento de asignaciones, desactivación de tienda con agentes activos, eliminación física prohibida).
- Base de datos: unique compuestos, FK, checks de integridad y nullable donde corresponde.

## Audit Strategy

- Reutiliza `audit_logs` con actor, acción (`STORE_CREATED`, `BANK_AGENT_DEACTIVATED`, `OPERATOR_ASSIGNED`, etc.), entidad, before/after JSON y motivo.
- Las asignaciones generan auditoría en cada activación y desactivación.
- Sin eliminación de auditoría desde interfaz.

## Testing Strategy

- Feature: CRUD de cada entidad con autorización positiva y negativa.
- Autorización: Policies testeadas para ambos roles y escenarios cruzados (operador intenta administrar).
- Asignaciones: solapamiento rechazado, historial conservado, terminación por desactivación de agente.
- Contraseña inicial: cambio forzado en primer login.

## Migration Plan

1. `regions` → `provinces` → `districts`
2. `stores`
3. `banks`
4. `bank_agents`
5. `user_bank_agent_assignments`
6. Seed de datos geográficos y bancos de ejemplo (Perú: región Lima, provincia Lima, distritos principales; bancos BCP, Interbank, BBVA).

Cada migración con `down()` inverso. Sin migración de `users` porque la tabla ya existe desde 001.

## Deployment And Rollback

Mismo procedimiento documentado en `docs/deployment.md`. Las 7 migraciones son reversibles. El rollback productivo prefiere forward fix si ya existen asignaciones.

## Contracts

Documentados en `contracts/web-endpoints.md`.

## Project Structure

```text
app/Modules/
├── IdentityAccess/
│   └── (extendido: operador registration, force password change)
├── Organization/
│   ├── Models/Region.php, Province.php, District.php, Store.php
│   ├── Http/Controllers/
│   ├── Http/Requests/
│   └── Policies/
├── BankingNetwork/
│   ├── Models/Bank.php, BankAgent.php, UserBankAgentAssignment.php
│   ├── Http/Controllers/
│   ├── Http/Requests/
│   └── Policies/
└── Audit/ (reutilizado)

database/migrations/ (7 nuevas migraciones)
routes/organization.php, routes/banking-network.php
resources/views/organization/, resources/views/banking-network/
tests/Feature/Organization/, tests/Feature/BankingNetwork/
```

## Exception Tracking

No hay excepciones constitucionales.

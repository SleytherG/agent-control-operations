# Quickstart: Validación de Integración Visual Stitch

**Feature**: 007-stitch-visual-integration
**Date**: 2026-07-22

Esta guía permite validar que la integración visual funciona correctamente después de cada módulo migrado.

## Prerrequisitos

```bash
# 1. Base de datos con migraciones ejecutadas
php artisan migrate:fresh --seed

# 2. Vite compilado
npm run build
# o en desarrollo:
npm run dev

# 3. Servidor Laravel
php artisan serve
```

## Validación por Módulo

### M0: Preparación (Layout)

```bash
# Verificar que el middleware inyecta variables
php artisan test --filter=AuthenticateJwtSession

# Verificar que layouts renderizan
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/login
# Esperado: 200

# Después de login, verificar layout autenticado
# Esperado: sidebar, topbar, session indicator visibles
```

### M1: Login

```bash
# 1. Acceder a /login — debe mostrar diseño Stitch
curl -s http://localhost:8000/login | grep -c "guest-layout"
# Esperado: > 0

# 2. Login con credenciales del seeder
# Usuario: admin@agenteflow.local / Password: password
# Esperado: redirección a /home con cookies de sesión

# 3. Login con credenciales incorrectas
# Esperado: mensaje de error en diseño Stitch

# 4. Verificar rate limiting (5 intentos en 60s)
# Esperado: botón deshabilitado con mensaje de throttling
```

### M3: Dashboard Operador

```bash
# 1. Login como operador (creado por seeder)
# 2. Acceder a /operator/dashboard
# 3. Verificar:
#    - 5 tarjetas métricas con valores reales (no "0" mock)
#    - Gráfico doughnut con distribución por tipo
#    - Gráfico de evolución temporal
#    - Tabla de operaciones recientes

# Sin operaciones:
# Esperado: componente empty-state, no gráficos vacíos
```

### M4: Registro de Operación

```bash
# 1. Login como operador con agente asignado
# 2. Acceder a /operations/create
# 3. Verificar:
#    - Selector de agente muestra solo los asignados
#    - Selector de tipo muestra catálogo real
# 4. Registrar operación: monto=100, tipo=Depósito
# 5. Verificar:
#    - Redirección a confirmación con ID real
#    - Operación aparece en historial

# Sin agentes asignados:
# Esperado: mensaje informativo, formulario oculto
```

### M5: Historial

```bash
# 1. Login como operador con operaciones
# 2. Acceder a /operations
# 3. Verificar:
#    - 5 tarjetas de resumen con valores calculados
#    - Filtros funcionales (fecha, tipo, estado)
#    - Tabla paginada (25/page)
#    - Badges de estado ACTIVA/ANULADA
# 4. Aplicar filtro de tipo — verificar que métricas se actualizan
```

### M6: Dashboard Admin

```bash
# 1. Login como admin
# 2. Acceder a /admin/dashboard
# 3. Verificar:
#    - Filtros multidimensionales con datos reales
#    - 4 KPI cards + métricas secundarias
#    - 3 gráficos (evolución, distribución, flujo)
#    - Ranking de tiendas y operadores
# 4. Aplicar filtro de región/tienda — verificar actualización

# Sin datos para filtros:
# Esperado: empty-state
```

### M8: Cierre Diario

```bash
# 1. Login, generar cierre desde /daily-closing/create
# 2. Acceder a /daily-closing/{id}
# 3. Verificar:
#    - Contexto (fecha, tienda, banco, agente)
#    - 5 KPI cards
#    - Desglose por tipo y operador
#    - Warning de pendientes (si aplica)
# 4. Como admin: confirmar cierre — verificar cambio de estado
# 5. Como admin: reabrir con motivo — verificar auditoría

# Cierre con operaciones POR_CONFIRMAR:
# Esperado: warning visible, net_movement etiquetado "Pendiente"
```

### M10: Limpieza

```bash
# 1. Verificar rutas demo inaccesibles
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/demo/login
# Esperado: 404

# 2. Verificar tests
php artisan test
# Esperado: mismos resultados que antes de la migración
# (185+ passed, 14 pre-existing failures unchanged)

# 3. Verificar que archivos demo no existen
ls app/Http/Controllers/Demo/ 2>/dev/null && echo "EXISTEN" || echo "ELIMINADOS"
ls resources/demo/ 2>/dev/null && echo "EXISTEN" || echo "ELIMINADOS"
ls resources/views/screens/ 2>/dev/null && echo "EXISTEN" || echo "ELIMINADOS"
```

## Smoke Test Completo

Después de migrar todos los módulos, ejecutar:

```bash
# 1. Test suite completo
php artisan test

# 2. Verificar todas las rutas productivas responden 200/302 (no 500)
for route in /login /operator/dashboard /operations /operations/create /admin/dashboard /daily-closing; do
  echo -n "$route → "
  curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000$route"
  echo
done

# 3. Verificar que componentes Stitch están presentes en HTML
curl -s http://localhost:8000/login | grep -c "guest-layout"  # > 0
curl -s http://localhost:8000/login | grep -c "login-card"     # > 0
```

## Criterios de Aceptación

- [ ] Todas las rutas funcionales usan diseño Stitch
- [ ] Login funcional con diseño Stitch
- [ ] Dashboard operador muestra datos reales (no mock)
- [ ] Registro de operación persiste en BD
- [ ] Historial consulta operaciones reales con filtros
- [ ] Dashboard admin muestra datos de toda la organización
- [ ] Cierre diario ejecuta acciones reales
- [ ] Sidebar respeta roles (operador no ve admin)
- [ ] Indicador de sesión usa expiración real del servidor
- [ ] `php artisan test` pasa con mismos resultados pre-migración
- [ ] Rutas demo retornan 404
- [ ] No existen archivos demo en controllers/views/demo/

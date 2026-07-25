# Catalogo de observaciones UI

## Resumen

| ID     | Pantalla          | Categoría         | Severidad | Estado    |
| ------ | ----------------- | ----------------- | --------- | --------- |
| UI-001 | Home              | Texto/Maquetación | High      | Pendiente |
| UI-002 | Admin Stores      | Text/Maquetación  | High      | Pendiente |
| UI-003 | Admin Users       | Text/Maquetación  | High      | Pendiente |
| UI-004 | Operations Create | Text/Maquetación  | High      | Pendiente |
| UI-005 | Historial         | Text/Maquetación  | High      | Pendiente |
| UI-006 | Cierres Diarios   | Text/Maquetación  | High      | Pendiente |
| UI-007 | Bancos            | Text/Maquetación  | High      | Pendiente |
| UI-008 | Agentes           | Text/Maquetación  | High      | Pendiente |
| UI-009 | Tipos de Op.      | Text/Maquetación  | High      | Pendiente |
| UI-010 | Sesiones          | Text/Maquetación  | High      | Pendiente |

## UI-001 — Textos incorrectos en vista home

### Contexto

- Ruta: `/home`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-001.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. El texto Financial Operations se muestra actualmente en Ingles.

2. En el texto Bienvenido, Operador, esta mostrando Operador a pesar de haber iniciado sesion como Administrador.

3. En el texto tiempo restante de sesión, se muestra de la siguiente manera: --:--

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  El texto "Financial Operations" debe mostrarse en Español ya que es una aplicacion para Perú.
2.  En el texto "Bienvenido, Operador", debe mostrar el Rol o nombre de la persona que inicio sesión, actualmente a pesar de haber iniciado sesión como Admin sigue mostrando "Operador".
3.  En este texto de tiempo restante, no deberia estar aqui, deberia ver un segundero, en la parte superior donde se encuentra el nombre de quien inicio sesion, y debe estar reduciendo segundo a segundo, esto da a entender de que la sesión esta activa por el tiempo que resta, se debe eliminar este texto de tiempo restante de esta vista ya que es algo no util.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-002 — Textos incorrectos en vista admin/stores

### Contexto

- Ruta: `/admin/stores`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-002.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. En la parte donde se ubica el boton DESACTIVAR cuando no hay ni una sola tienda, se muestra todo un texto extraño que no se entiende.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  En la parte donde se listan todas las tiendas creadas por el admin, se muestra un texto extraño que no se entiende y debe ser corregido, ya que esto es un bug de UI para el usuario final.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-003 — Textos incorrectos en vista admin/users

### Contexto

- Ruta: `/admin/users`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-003.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. En la parte donde se ubica el boton DESACTIVAR cuando no hay ni un solo operador, se muestra todo un texto extraño que no se entiende.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  En la parte donde se listan todos los operadores creados por el admin, se muestra un texto extraño que no se entiende y debe ser corregido, ya que esto es un bug de UI para el usuario final.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-004 — Redireccionamiento incorrecto

### Contexto

- Ruta: `/operations/create`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-004.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Cuando redirecciona a la ruta /operations/create pero logueado como ADMIN, sale la vista de 403 | This action is unauthorized..

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Cuando se redirecciona a la ruta /operations/create pero logueado como ADMIN, redigire a esta ruta pero sale la pantalla 403 | This action is unauthorized, uno esta pagina debe estar en español y otro es que si esta ruta no le esta permitido al admin ingresar entonces no deberia aparecer en el menu de rutas ya que para el usuario final es un error y es mejor quitar la ruta del menu para que el usuario final no lo reporte como error.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-005 — Textos extraños en la ruta Historial "/operations"

### Contexto

- Ruta: `/operations`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-005.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Textos extraños en la vista de "Mis Operaciones" y en el bloque "Movimiento Neto", no se nota bien los textos porque esta de color oscuro y el texto oscuro

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Todos los textos extraños no entendibles para un usuario final, se debe corregir y maquetar lo que es correcto, adicional revisar los colores ya que un globo/campo esta de color oscuro y el texto tambien oscuro entonces no se puede leer correctamente.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-006 — Falta de maquetación en la ruta "daily-closures"

### Contexto

- Ruta: `/daily-closures`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-006.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Se visualiza que la parte de los filtros no esta maquetado solo esta generado por el HTML5 por defecto, la parte de la tabla y sus columnas como ID, AGENTE, FECHA, ESTADO, OPERACIONES, MONTO, BRUTO, NETO, ACCIONES estan distribuidos de manera incorrecta.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Se debe realizar la maquetacion de esta vista correctamente "/daily-closures" al estilo como esta maquetado otras vistas, ya que actualmente esta todos los estilos por defecto osea con solo HTML5, y la tabla y sus columnas estan todas juntas sin su separacion correcta, adicional a ello, cuando no existen valores/cierres, se debe mostrar lo que en otras vistas se esta mostrando cuando no existen resultados/items.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-007 — Vista de Bancos con textos extraños, cuando no existen bancos creados o cuando hay una lista vacia.

### Contexto

- Ruta: `/admin/banks`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-007.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Se muestra un texto extraño en la vista de bancos cuando no existen bancos.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Se debe corregir estos textos extraños, no deben aparecer, y debe estar la maquetacion de manera correcta, revisa nuevamente si la distribucion es la correcta de todos los textos, de acuerdo al diseño base, tambien si no existen items creados en esta vista se debe mostrar el texto donde indica que no hay bancos por mostrar guiandote como esta en otras vistas vacias ya maquetadas.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-008 — Vista de Agentes Bancarios con textos extraños, cuando no existen agentes bancarios creados o cuando la lista esta vacia.

### Contexto

- Ruta: `/admin/bank-agents`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-008.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Se muestra un texto extraño en la vista de bancos cuando no existen agentes bancarios.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Se debe corregir este texto extraño que esta apareciendo cuando no existen agentes bancarios, adicional a ello si no existe ningun agente bancario creado entonces debe mostrar el icono o texto de que no hay valores para mostrar asi como se muestra en otras vistas ya maquetadas.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-009 — Vista de Tipos de Operacion muestra textos extraños

### Contexto

- Ruta: `/admin/operation-types`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-009.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Se muestra un texto extraño en la vista de tipos de operacion cuando no existen tipos de operaciones creadas.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Se debe corregir el texto extraño adicional a ello tambien debe de tener paginacion como en otras vistas, y tambien si en caso no existan items creados, se debe mostrar la vista cuando no hay datos por mostrar asi como en otras vistas maquetadas.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

## UI-010 — Vista de Historial de Sesiones, muestra textos extraños

### Contexto

- Ruta: `/sessions`
- Rol: Admin
- Viewport: 1440x900
- Navegador: Microsoft Edge
- Evidencia:
  `./ui-010.jpeg`

### Resultado actual

En esta vista se muestran varios issues te detallare uno por uno:

1. Se muestra textos extraños en la vista, entenderia cuando no hay valores.
2. Los filtros no se encuentran bien maquetados estan todos apilados hacia la izquierda.

### Resultado esperado

Como debe de estar es de la siguiente manera:

1.  Se debe corregir estos textos extraños no se debe mostrar de esa forma, ya que si no existen sessiones activas debe de mostrar lo que se muestra por defecto cuando no hay items creados.
2.  La maquetacion de los filtros esta mal maquetado esta todo pegado a la izquierda y debe estar bien maquetado como en las demas vistas.

### Fuente de verdad

- `docs/design/stitch/v1/DESIGN.md`
- Pantalla de inicio de sesión aprobada.
- Terminología de la spec funcional.

### Restricciones

- No cambiar el flujo de redireccionamiento.
- No modificar validaciones.
- No crear una vista alternativa.

---

# Product Brief: Control Interno de Operaciones de Agentes Bancarios

## 1. Propósito

Construir una aplicación web interna que sustituya el registro manual en cuadernos de las operaciones realizadas en los agentes bancarios administrados por el cliente.

La solución permitirá conocer quién realizó cada operación, en qué tienda y agente se realizó, qué tipo de operación fue registrada, cuál fue su monto y cuándo ocurrió.

Es un registro interno de control operacional: no reemplaza sistemas bancarios, no confirma el procesamiento de una operación por el banco y no constituye inicialmente un sistema contable, una integración bancaria ni una fuente oficial de información bancaria.

## 2. Situación actual

El cliente posee varias tiendas en diferentes ciudades, distritos y regiones del país.

Una tienda puede operar uno o varios agentes bancarios correspondientes a bancos como BCP, Interbank, BBVA u otras entidades.

Las operaciones se registran actualmente en cuadernos físicos. Este proceso dificulta:

- Consolidar información.
- Consultar operaciones históricas.
- Identificar quién realizó una operación.
- Comparar trabajadores.
- Obtener totales diarios.
- Visualizar operaciones de todas las tiendas.
- Detectar registros equivocados.
- Obtener información nacional, regional o local.

## 3. Usuarios iniciales

### Administrador propietario (`ADMINISTRADOR_PROPIETARIO`)

Representa al propietario o responsable general de la red.

Puede:

- Consultar todas las tiendas.
- Consultar todos los agentes.
- Consultar todas las operaciones.
- Filtrar información.
- Crear y desactivar trabajadores.
- Asignar trabajadores a agentes.
- Gestionar bancos, tiendas, agentes y tipos de operación.
- Anular operaciones con un motivo.
- Consultar sesiones de acceso.

### Operador (`OPERADOR`)

Representa al trabajador que atiende uno o varios agentes.

Puede:

- Iniciar y cerrar sesión.
- Seleccionar un agente asignado.
- Registrar operaciones.
- Consultar únicamente sus propias operaciones.
- Consultar únicamente sus propios indicadores.
- Continuar o cancelar su sesión cuando se muestre la advertencia de expiración.

No puede consultar operaciones de otros trabajadores.

La sesión utiliza un access token JWT de duración configurable, inicialmente de cinco minutos. La interfaz avisa treinta segundos antes del vencimiento y solo renueva tras una acción explícita. Los refresh tokens son rotatorios, revocables y se almacenan únicamente mediante su hash.

## 4. Modelo operativo

Una tienda es un establecimiento físico.

Una tienda puede tener varios agentes bancarios.

Cada agente bancario pertenece a un banco y puede tener un código o identificador operativo.

Un trabajador puede cambiar de turno, tienda o agente. El sistema no controlará horarios laborales ni asistencia formal.

Cada operación debe quedar asociada al trabajador y al agente bancario utilizados durante el registro.

## 5. Alcance del primer MVP

El MVP incluye:

- Inicio de sesión.
- Cierre de sesión.
- Control de expiración de sesión.
- Historial de sesiones.
- Roles de administrador propietario y operador.
- Gestión de tiendas.
- Gestión de bancos.
- Gestión de agentes bancarios.
- Gestión de trabajadores.
- Asignación de trabajadores a agentes.
- Catálogo de tipos de operación.
- Registro de operaciones.
- Consulta de operaciones propias.
- Consulta administrativa global.
- Filtros por fechas, trabajador, ubicación, banco y agente.
- Indicadores por día, semana, mes, trimestre, semestre y año.
- Resumen diario de cantidad de operaciones y monto operado.
- Anulación auditable de operaciones.

## 6. Fuera del alcance del primer MVP

No se incluye:

- Integración con APIs bancarias.
- Sincronización con POS.
- Lectura automática de comprobantes.
- Gestión de clientes finales.
- DNI, teléfono o cuentas de clientes.
- Cálculo de comisiones bancarias.
- Comisión cobrada al cliente.
- Utilidad o ganancia.
- Planilla.
- Control de asistencia.
- Horarios o turnos programados.
- Aplicación móvil.
- Funcionamiento offline.
- Multiempresa o modalidad SaaS.
- Exportaciones contables.
- Conciliación automática con bancos.
- Notificaciones por WhatsApp o SMS.

## 7. Conceptos económicos

El monto de una operación representa el valor procesado, no la ganancia del establecimiento.

Los primeros dashboards mostrarán:

- Cantidad de operaciones.
- Monto total operado.
- Promedio por operación.
- Distribución por tipo.
- Distribución por banco.
- Distribución por agente.
- Distribución por trabajador.

Un cierre formal de caja será una funcionalidad posterior que requerirá definir efectivo inicial, entradas, salidas, efectivo contado y diferencias.

## 8. Restricciones

- Aplicación interna.
- Un solo cliente.
- Monolito web.
- Bajo costo de hosting.
- PostgreSQL relacional administrado inicialmente por Supabase, sin acoplar reglas de negocio a otros
  servicios de Supabase.
- Frontend ligero.
- Sin procesos permanentes obligatorios.
- Uso desde navegadores modernos.
- Interfaz responsive para computadora, tableta y celular.

## 9. Decisiones pendientes del cliente

- Campos exactos que actualmente escribe en el cuaderno.
- Tipos de operación por cada banco.
- Si se registrará número de comprobante.
- Si se permitirá registrar operaciones con fecha anterior.
- Si un operador puede corregir una operación.
- Quién puede anular operaciones.
- Si las operaciones son únicamente en soles.
- Si un trabajador puede utilizar varios agentes simultáneamente.
- Duración máxima de una jornada autenticada.
- Tiempo de inactividad aceptable.
- Necesidad de un cierre formal de caja.
- Volumen aproximado de operaciones diarias.

# Manifiesto de diseños de Google Stitch

## Exportación

- Herramienta: Google Stitch
- Versión local: STITCH-V1
- Estado: referencia visual inicial pendiente de refinamiento
- Propósito: guiar la maquetación responsive de la aplicación
- Código generado: referencia, no código de producción

## Mapeo de pantallas

| Identificador canónico | Carpeta Stitch | Propósito |
|---|---|---|
| authentication-login | inicio_de_sesi_n | Inicio de sesión |
| authentication-expiry-warning | aviso_de_expiraci_n_de_sesi_n | Advertencia de expiración |
| operator-dashboard | dashboard_del_operador | Dashboard del operador |
| operation-registration | registro_r_pido_de_operaci_n | Registro rápido |
| operations-history | historial_de_operaciones | Historial |
| administrator-dashboard | dashboard_administrativo | Dashboard administrativo |
| daily-closing | cierre_operativo_diario | Cierre operativo diario |

## Uso de los artefactos

### DESIGN.md

Define:

- Colores.
- Tipografía.
- Espaciado.
- Jerarquía visual.
- Patrones comunes.
- Dirección visual general.

### screen.png

Define:

- Composición visual de la pantalla.
- Jerarquía de información.
- Distribución de regiones.
- Relaciones espaciales.
- Apariencia esperada.

### code.html

Se utiliza únicamente para analizar:

- Estructura sugerida.
- Textos.
- Componentes visuales.
- Clases y estilos propuestos.
- Elementos presentes en la pantalla.

No debe copiarse directamente dentro de Laravel.

## Precedencia

Para reglas de negocio y comportamiento:

1. Constitution.
2. Spec funcional activa.
3. Criterios de aceptación.
4. Decisiones documentadas.

Para sistema visual:

1. DESIGN.md.
2. Decisiones visuales aprobadas.
3. screen.png.
4. code.html.

Cuando exista una contradicción entre diseño y reglas funcionales, prevalece la spec funcional.

## Mejoras permitidas

El agente puede mejorar:

- Accesibilidad.
- Contraste.
- Responsive design.
- Consistencia entre pantallas.
- Jerarquía visual.
- Navegación mediante teclado.
- Estados de error, vacío y carga.
- Reutilización de componentes.
- Rendimiento frontend.

El agente no puede cambiar sin aprobación:

- Roles.
- Permisos.
- Reglas de negocio.
- Ciclo de sesión.
- Significado de las métricas.
- Acciones disponibles.
- Flujos operacionales principales.

# Estabilización visual y de contenido UI — V1

## Identificación

- Slug: `ui-visual-content-stabilization-v1`
- Tipo: defecto sistémico de presentación
- Severidad general: High
- Spec principal relacionada:
  `specs/007-integracion-ui-sistema-real/spec.md`
- Spec visual relacionada:
  `ui-visual-content-stabilization-v1`
- Estado: pendiente de evaluación

## Declaración del defecto

La aplicación productiva utiliza parcialmente el sistema visual aprobado,
pero presenta errores generales de maquetación, textos incorrectos,
inconsistencias entre pantallas, problemas responsive y desviaciones respecto
de las referencias de diseño.

Las funcionalidades reales ya existen. Esta corrección no debe volver a
crear vistas demo ni sustituir datos reales por datos ficticios.

## Fuentes de verdad

El agente debe revisar, en este orden:

1. `.specify/memory/constitution.md`
2. Las especificaciones funcionales relacionadas.
3. La spec de integración visual.
4. La spec de maquetación basada en Google Stitch.
5. `docs/design/stitch/v1/DESIGN.md`
6. `docs/design/stitch/v1/MANIFEST.md`
7. Los archivos `screen.png` aplicables.
8. Las decisiones visuales documentadas.
9. El presente catálogo de observaciones.

## Alcance

La corrección incluye:

- Textos incorrectos.
- Etiquetas incorrectas.
- Terminología inconsistente.
- Alineación.
- Márgenes.
- Espaciado.
- Tamaños.
- Anchos y alturas.
- Desbordamientos.
- Layout responsive.
- Jerarquía tipográfica.
- Colores semánticos.
- Contraste.
- Visibilidad de componentes.
- Reutilización de componentes.
- Estados vacíos, carga, error y éxito.
- Diferencias respecto del diseño aprobado.

## Fuera de alcance

Esta corrección no autoriza:

- Cambiar reglas de negocio.
- Cambiar roles o permisos.
- Modificar el comportamiento funcional aprobado.
- Alterar cálculos financieros.
- Agregar funcionalidades nuevas.
- Crear datos mock en producción.
- Crear rutas demo.
- Reemplazar datos reales con fixtures.
- Cambiar autenticación.
- Cambiar el esquema de base de datos, salvo que una causa raíz lo requiera y
  quede documentada.
- Rediseñar toda la aplicación con una dirección visual diferente.

## Reglas para textos

Cuando una observación indique un texto incorrecto, debe existir:

- Texto actual exacto.
- Texto esperado exacto.
- Ubicación.
- Fuente que justifica el texto esperado.

El agente no debe inventar terminología.

## Reglas para maquetación

Las correcciones deben:

- Reutilizar componentes compartidos.
- Evitar estilos inline.
- Evitar soluciones específicas que rompan otras pantallas.
- Mantener HTML semántico.
- Mantener navegación por teclado.
- Respetar responsive design.
- Evitar desplazamiento horizontal global.
- Conservar las funcionalidades y datos reales.
- No copiar directamente el HTML de Stitch.

## Catálogo

El detalle completo se encuentra en:

`.specify/bugs/ui-visual-content-stabilization-v1/evidence/issues.md`

## Criterios generales de aceptación

1. Todas las observaciones enumeradas tienen resolución verificable.
2. Los textos coinciden exactamente con lo documentado.
3. La aplicación continúa utilizando datos reales.
4. No se modifican reglas de negocio.
5. No se introducen rutas demo.
6. No se crean componentes duplicados innecesariamente.
7. Las pruebas funcionales existentes continúan pasando.
8. `npm run build` finaliza correctamente.
9. No existe desplazamiento horizontal global.
10. Las resoluciones afectadas fueron verificadas.
11. Se capturan evidencias posteriores para cada observación.
12. No quedan observaciones marcadas como pendientes dentro de este lote.

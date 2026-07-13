# Instrucciones permanentes de GoodVenta para Clinident Salud

Estas reglas se aplican a todo el repositorio `GoodVentaAsisCap`.

## Fuente de conocimiento obligatoria

El vault de Obsidian se encuentra en:

`C:\Users\HP\Documents\Clinident Knowledge Base`

Antes de analizar o modificar una funcionalidad, consultar como mínimo:

1. `00 - Inicio/Panel de desarrollo.md`.
2. `01 - GoodVenta/Arquitectura actual.md`.
3. La ficha funcional de `02 - Clinident Salud` relacionada.
4. La ficha técnica de `01 - GoodVenta/Modulos tecnicos` relacionada, si existe.
5. El proceso de `03 - Procesos` y los antecedentes de `06 - Cambios y decisiones`.

Si el vault no está disponible, informarlo y continuar únicamente con la inspección del código. No inventar contenido faltante.

## Antes de programar

1. Recorrer el flujo actual desde la pantalla hasta JavaScript, PHP y base de datos.
2. Identificar archivos, funciones, operaciones `funt`, tablas, permisos, estados y consumidores.
3. Explicar brevemente qué se entendió, cómo funciona actualmente y la solución mínima propuesta.
4. Enumerar archivos probables, riesgos y datos que deben conservarse.
5. Hacer cuatro o cinco preguntas concretas y esperar confirmación antes de editar.

## Reglas de implementación

- Mantener compatibilidad con PHP 7.2.
- Respetar la arquitectura legacy con HTML, CSS, JavaScript, jQuery, AJAX, PHP y MySQL.
- Reutilizar IDs, clases, selectores, funciones, endpoints, permisos y formatos de respuesta existentes.
- No reescribir módulos completos cuando pueda hacerse una mejora incremental.
- No cambiar tablas o relaciones sin comprobar el esquema y preparar una migración controlada.
- Preservar registros antiguos y evitar escrituras, cobros, consumos o migraciones duplicadas.
- Validar en servidor aunque la interfaz oculte la acción.
- Mantener trazabilidad para acciones clínicas, financieras, administrativas y de RR. HH.
- Limitar CSS y JavaScript al módulo afectado.
- No agregar dependencias que requieran una versión superior a PHP 7.2.

## Privacidad y seguridad

- No copiar al vault contraseñas, tokens, archivos de conexión, dumps ni datos identificables.
- No usar nombres, documentos, teléfonos, imágenes, diagnósticos o historias clínicas reales en ejemplos o pruebas.
- No publicar el vault ni sus adjuntos en servicios externos sin autorización.
- No considerar segura una acción solamente porque su botón esté oculto.

## Documentación obligatoria al terminar

Actualizar solamente las notas relacionadas, manteniendo el vault útil y sin ruido:

1. La ficha técnica y funcional del módulo.
2. El proceso operativo si cambió el recorrido del usuario.
3. Una nota basada en `90 - Plantillas/Plantilla de cambio.md`.
4. Los casos de prueba ejecutados.
5. La decisión técnica cuando exista una elección con consecuencias futuras.
6. El inventario de migraciones cuando haya cambios de base de datos.
7. `00 - Inicio/Panel de desarrollo.md` si cambió un estado o pendiente.

Marcar como `vigente` solamente información comprobada en código, pruebas o validación funcional. Usar `en_revision` o `borrador` para lo demás.

## Definición de terminado

Un cambio no está terminado hasta que:

- Cumple el comportamiento solicitado.
- Conserva compatibilidad con datos y funciones anteriores.
- Pasó las pruebas proporcionales al riesgo.
- Tiene estrategia de reversión cuando corresponde.
- No expone información sensible.
- La documentación relacionada quedó actualizada.
- Se informó qué cambió, cómo probarlo y qué sigue pendiente.


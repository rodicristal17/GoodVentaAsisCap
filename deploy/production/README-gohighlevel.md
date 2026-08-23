# GoHighLevel en Sistema Telar

## Alcance de la fase 1

El modulo consulta datos reales de una unica subcuenta de GoHighLevel y los presenta dentro de Telar. Incluye conversaciones, contactos, oportunidades, pipelines y calendarios. La vista inicial es **Conversaciones**; el resumen superior y las oportunidades quedan separados para no interferir con la operatoria diaria. Contactos, conversaciones y oportunidades incluyen busqueda y carga progresiva para recorrer el conjunto completo sin volver pesada la mesa de trabajo.

La integracion es estrictamente de solo lectura. No crea ni actualiza contactos, no mueve oportunidades, no envia mensajes y no dispara workflows. Esta restriccion protege especialmente las automatizaciones que reaccionan a `Contact Created` o a cambios de etapa.

El historial de una conversacion tambien se consulta en modo solo lectura: abrirlo no marca mensajes, no responde y no ejecuta automatizaciones.

## Vinculacion con pacientes

Cada contacto consultado se compara con `central_telefonica_paciente_telefono` usando el telefono normalizado. Se vincula automaticamente solo cuando existe exactamente un paciente coincidente. Si existen varias coincidencias, el modulo muestra una advertencia y no elige un paciente. Si no existe coincidencia, permanece sin vincular.

La prioridad visual del avatar es:

1. foto del contacto expuesta por GoHighLevel;
2. avatar disponible en Telar;
3. iniciales del nombre.

La foto nativa del perfil de WhatsApp no se presupone porque no esta garantizada por la API.

## Seguridad y credenciales

Se debe crear una integracion privada exclusiva para Telar con permisos de lectura. No se debe reutilizar, editar ni rotar otra integracion existente.

Alcances requeridos en la integracion privada:

- `contacts.readonly`
- `conversations.readonly`
- `conversations/message.readonly`
- `opportunities.readonly`
- `calendars.readonly`

El token se instala fuera del repositorio en:

`deploy/production/secrets/gohighlevel_readonly_token`

El directorio `deploy/production/secrets` debe pertenecer a `root:www-data` y usar modo `0750`, para que el proceso web pueda atravesarlo sin exponerlo a otros usuarios. El archivo debe pertenecer a `root:www-data`, usar modo `0440` y montarse como solo lectura en `/run/secrets/gohighlevel_readonly_token`. Nunca debe agregarse al archivo `.env`, la base de datos, Git, la documentacion o los logs.

Variables no sensibles:

- `TELAR_GOHIGHLEVEL_API_BASE`
- `TELAR_GOHIGHLEVEL_API_VERSION`
- `TELAR_GOHIGHLEVEL_LOCATION_ID`
- `TELAR_GOHIGHLEVEL_TOKEN_FILE`

## Permisos

El engranaje superior derecho administra dos capacidades:

- **Puede ver:** acceso al modulo y a los datos consultados.
- **Administra:** acceso al engranaje y gestion de permisos; implica tambien poder ver.

Los cambios quedan registrados en `gohighlevel_evento`. El usuario propietario inicial no puede quedar sin acceso administrativo.

## Migracion y reversion

- Aplicar `actualizacion_23082026_gohighlevel_fase1.sql`.
- Revertir, si fuera necesario, con `actualizacion_23082026_gohighlevel_fase1_rollback.sql`.

La reversion elimina solamente tablas, acceso y permisos propios del modulo. No toca datos ni automatizaciones de GoHighLevel.

# GoHighLevel en Sistema Telar

## Alcance actual

El modulo consulta datos reales de una unica subcuenta de GoHighLevel y los presenta dentro de Telar. Incluye conversaciones, contactos, oportunidades, pipelines y calendarios. La vista inicial es **Conversaciones**; el resumen superior y las oportunidades quedan separados para no interferir con la operatoria diaria. Contactos, conversaciones y oportunidades incluyen busqueda y carga progresiva para recorrer el conjunto completo sin volver pesada la mesa de trabajo.

La consulta continúa en modo de solo lectura: no crea ni actualiza contactos, no mueve oportunidades y no modifica workflows. La fase 2A agrega una unica escritura permitida: responder manualmente por WhatsApp desde una conversacion existente cuando el contacto escribio durante las ultimas 24 horas.

Abrir el historial no marca mensajes ni ejecuta automatizaciones. El envio requiere permiso individual, confirmacion final, una ventana de 24 horas verificada nuevamente en servidor y el interruptor operativo habilitado. No se admiten plantillas, adjuntos, envios masivos ni texto libre fuera de la ventana.

## Vinculacion con pacientes

Cada contacto consultado se compara con `central_telefonica_paciente_telefono` usando el telefono normalizado. Se vincula automaticamente solo cuando existe exactamente un paciente coincidente. Si existen varias coincidencias, el modulo muestra una advertencia y no elige un paciente. Si no existe coincidencia, permanece sin vincular.

La prioridad visual del avatar es:

1. foto del contacto expuesta por GoHighLevel;
2. avatar disponible en Telar;
3. iniciales del nombre.

La foto nativa del perfil de WhatsApp no se presupone porque no esta garantizada por la API.

## Seguridad y credenciales

Se debe usar la integracion privada exclusiva de Telar. La escritura se limita por codigo a `POST /conversations/messages`; no se habilitan rutas para contactos, oportunidades o workflows.

Alcances requeridos en la integracion privada:

- `contacts.readonly`
- `conversations.readonly`
- `conversations/message.readonly`
- `conversations/message.write` (solamente para la fase 2A)
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
- `TELAR_GOHIGHLEVEL_WRITE_ENABLED` (`false` por defecto)

El permiso externo y el interruptor deben habilitarse juntos. Si cualquiera falta, Telar muestra el historial pero bloquea el compositor.

## Permisos

El engranaje superior derecho administra tres capacidades:

- **Puede ver:** acceso al modulo y a los datos consultados.
- **Responde:** envio manual por WhatsApp dentro de la ventana; implica tambien poder ver.
- **Administra:** acceso al engranaje y gestion de permisos; implica tambien poder ver.

Los cambios quedan registrados en `gohighlevel_evento`. Cada intento de envio se registra ademas en `gohighlevel_envio_manual` sin almacenar el texto. El usuario propietario inicial no puede quedar sin acceso administrativo ni de respuesta.

## Protecciones del envio manual

- El contacto se obtiene de nuevo desde la conversacion de HighLevel; no se confia en un identificador enviado por el navegador.
- El servidor consulta hasta 100 mensajes recientes y exige un inbound de WhatsApp dentro de las ultimas 24 horas.
- La interfaz exige revision del destinatario y una segunda confirmacion antes del envio.
- Cada envio usa un token unico para evitar dobles clics y aplica limites de frecuencia.
- La auditoria guarda actor, conversacion, resultado y longitud; nunca el cuerpo del mensaje.
- Una conversacion vencida queda bloqueada hasta implementar plantillas aprobadas en una fase posterior.

## Migracion y reversion

- Aplicar `actualizacion_23082026_gohighlevel_fase1.sql`.
- Aplicar `actualizacion_23082026_gohighlevel_respuestas_manual.sql`.
- Revertir, si fuera necesario, con `actualizacion_23082026_gohighlevel_fase1_rollback.sql`.
- La fase 2A puede revertirse por separado con `actualizacion_23082026_gohighlevel_respuestas_manual_rollback.sql`.

La reversion elimina solamente tablas, acceso y permisos propios del modulo. No toca datos ni automatizaciones de GoHighLevel.

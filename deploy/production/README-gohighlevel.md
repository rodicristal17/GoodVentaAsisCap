# GoHighLevel en Sistema Telar

## Alcance actual

El modulo consulta datos reales de una unica subcuenta de GoHighLevel y los presenta dentro de Telar. Incluye conversaciones, contactos, oportunidades, pipelines, calendarios, responsables, tareas y adjuntos. La vista inicial es **Conversaciones**; el resumen superior y las oportunidades quedan separados para no interferir con la operatoria diaria. Contactos, conversaciones, oportunidades y tareas incluyen busqueda y carga progresiva.

La consulta no crea ni actualiza contactos, no mueve oportunidades y no modifica workflows. La fase 2A agrega una escritura limitada: responder manualmente por WhatsApp desde una conversacion existente cuando el contacto escribio durante las ultimas 24 horas. La fase 2B permite retomar una conversacion vencida con una plantilla aprobada, activa, en español, de categoria Utility y sin variables manuales. La fase 3 permite crear, editar, completar y reabrir tareas de contactos; no permite eliminarlas.

## Responsables y tareas

Telar importa el catalogo de usuarios de la subcuenta e intenta vincularlo con usuarios activos de Telar por correo exacto y unico. El correo de GoHighLevel se usa en memoria durante esa comparacion: la base local conserva solamente su hash. El engranaje permite revisar y corregir los vinculos manualmente.

- Un usuario sin **Ve equipo** consulta sus conversaciones y tareas propias junto con las no asignadas.
- Un usuario con **Ve equipo** puede filtrar por todo el equipo, por responsable o por elementos sin asignar.
- La asignacion de conversaciones se consulta y filtra, pero no se modifica desde Telar porque la API oficial de conversaciones no expone esa escritura en el contrato usado.
- Las tareas de cada contacto aparecen en un panel plegable dentro del chat, para no quitar espacio al historial.
- La pestaña **Tareas** usa un indice local reversible. GoHighLevel sigue siendo la fuente oficial.
- La sincronizacion inicial usa la busqueda oficial de tareas en lotes de 100, sin recorrer los casi cuarenta mil contactos uno por uno. Se conservan pendientes y completadas de los ultimos 90 dias; no se duplican tareas.
- Crear, editar, completar o reabrir una tarea se envia primero a GoHighLevel y se registra con token de idempotencia y auditoria sin copiar su descripcion.

Abrir el historial no marca mensajes ni ejecuta automatizaciones. Cada conversacion se posiciona inicialmente en el mensaje mas reciente; al cargar mensajes anteriores conserva el punto de lectura en lugar de saltar. El envio manual se realiza con un unico boton, pero conserva el permiso individual, la validacion de la ventana nuevamente en servidor y el interruptor operativo habilitado. El compositor permanece fijo, comienza en una linea y crece hasta tres; el estado de las 24 horas se muestra como indicador compacto y solamente el historial de mensajes se desplaza. La conversacion no reserva un pie inferior: la X, Escape y el fondo del modal permiten cerrarla. No se admiten envios masivos ni texto libre fuera de la ventana. Fuera de 24 horas Telar solo envia el identificador de una plantilla que vuelve a validar contra el catalogo real de GoHighLevel.

Los adjuntos recibidos se registran al abrir el historial y se descargan al visualizarlos por primera vez. GoHighLevel sigue siendo la fuente original, pero Telar conserva una copia permanente privada para que una URL vencida no elimine el antecedente. El navegador nunca recibe la URL original. Solamente se aceptan los dominios configurados, HTTPS, resolucion a una IP publica, archivos de hasta 20 MB y tipos expresamente permitidos. Imagenes, audio y video se muestran dentro del chat; los documentos se abren o descargan desde un enlace firmado de una hora. Los archivos quedan en el volumen `telar_ghl_media` bajo `/var/lib/telar/gohighlevel_adjuntos`.

## Asistente DeepSeek

El engranaje incorpora una pestaña **Asistente IA**. Alli se administran el tono, la instruccion principal, la informacion autorizada de la clinica, preguntas frecuentes, reglas de derivacion y el modelo. La clave de DeepSeek no se escribe ni se muestra en la interfaz: se instala en `deploy/production/secrets/deepseek_api_key`, se monta en `/run/secrets/deepseek_api_key` y debe usar propietario `root:www-data` y modo `0440`.

El modo inicial es un borrador: el boton **Sugerir** anonimiza telefonos, correos e identificadores, consulta DeepSeek y coloca el texto en el compositor para que un funcionario lo revise. Nunca envia por si solo. Salud, pagos, reclamos, asuntos legales y mensajes con adjuntos se derivan sin consultar o responder automaticamente. La auditoria conserva actor, conversacion, modelo, resultado, intencion, confianza y cantidades de caracteres; no conserva el historial enviado a la IA ni el texto sugerido.

Las respuestas automaticas tienen tres interruptores acumulativos: clave instalada, `TELAR_DEEPSEEK_AUTO_REPLY_ENABLED=true` en el servidor y opcion activa en el engranaje. Los tres comienzan apagados. El servicio `gohighlevel-ai-auto` procesa como maximo dos conversaciones por ciclo, exige una confianza minima de 0,88 y reutiliza todas las validaciones del envio manual. Mientras el interruptor del servidor siga en `false`, la opcion visual permanece bloqueada y el servicio no consulta DeepSeek ni envia mensajes.

## Vinculacion con pacientes

Cada contacto consultado se compara con `central_telefonica_paciente_telefono` usando el telefono normalizado. Se vincula automaticamente solo cuando existe exactamente un paciente coincidente. Si existen varias coincidencias, el modulo muestra una advertencia y no elige un paciente. Si no existe coincidencia, permanece sin vincular.

La prioridad visual del avatar es:

1. foto del contacto expuesta por GoHighLevel;
2. avatar disponible en Telar;
3. iniciales del nombre.

La foto nativa del perfil de WhatsApp no se presupone porque no esta garantizada por la API.

## Seguridad y credenciales

Se debe usar la integracion privada exclusiva de Telar. La escritura se limita por codigo a `POST /conversations/messages` y a las rutas exactas de tareas bajo `/contacts/{contactId}/tasks`; no se habilitan escrituras para contactos, oportunidades o workflows.

Alcances requeridos en la integracion privada:

- `contacts.readonly`
- `contacts.write` (crear, actualizar, completar y reabrir tareas)
- `conversations.readonly`
- `conversations/message.readonly`
- `conversations/message.write` (envios protegidos de las fases 2A y 2B)
- `locations/templates.readonly` (catalogo aprobado de la fase 2B)
- `opportunities.readonly`
- `calendars.readonly`
- `users.readonly` (catalogo y vinculacion de responsables)
- `locations/tasks.readonly` (busqueda global y sincronizacion eficiente de tareas)

El token se instala fuera del repositorio en:

`deploy/production/secrets/gohighlevel_readonly_token`

La firma temporal de los adjuntos se instala en:

`deploy/production/secrets/gohighlevel_attachment_signing_key`

El directorio `deploy/production/secrets` debe pertenecer a `root:www-data` y usar modo `0750`, para que el proceso web pueda atravesarlo sin exponerlo a otros usuarios. El archivo debe pertenecer a `root:www-data`, usar modo `0440` y montarse como solo lectura en `/run/secrets/gohighlevel_readonly_token`. Nunca debe agregarse al archivo `.env`, la base de datos, Git, la documentacion o los logs.

Variables no sensibles:

- `TELAR_GOHIGHLEVEL_API_BASE`
- `TELAR_GOHIGHLEVEL_API_VERSION`
- `TELAR_GOHIGHLEVEL_LOCATION_ID`
- `TELAR_GOHIGHLEVEL_COMPANY_ID` (opcional; habilita la ruta v3 del catalogo de usuarios)
- `TELAR_GOHIGHLEVEL_TOKEN_FILE`
- `TELAR_GOHIGHLEVEL_WRITE_ENABLED` (`false` por defecto)
- `TELAR_GOHIGHLEVEL_TASK_WRITE_ENABLED` (`false` por defecto; se activa separadamente tras confirmar `contacts.write`)
- `TELAR_GOHIGHLEVEL_ATTACHMENT_HOSTS` (lista exacta de origenes HTTPS permitidos)
- `TELAR_GOHIGHLEVEL_ATTACHMENT_MAX_BYTES` (20 MB por defecto)
- `TELAR_DEEPSEEK_MODEL` (`deepseek-v4-flash` por defecto)
- `TELAR_DEEPSEEK_AUTO_REPLY_ENABLED` (`false` por defecto)
- `TELAR_DEEPSEEK_AUTO_INTERVAL_SECONDS` (30 segundos por defecto)

Los permisos externos y sus interruptores deben habilitarse juntos. Mensajes y tareas usan interruptores separados para que ampliar tareas no altere WhatsApp.

## Permisos

El engranaje superior derecho administra siete capacidades, los responsables y el catalogo local:

- **Puede ver:** acceso al modulo y a los datos consultados.
- **Responde:** envio manual por WhatsApp dentro de la ventana; implica tambien poder ver.
- **Plantillas:** envio de plantillas aprobadas fuera de 24 horas; implica tambien poder ver.
- **Ve tareas:** consulta tareas propias y no asignadas.
- **Ve equipo:** permite consultar y filtrar asignaciones de otros responsables.
- **Gestiona tareas:** crea, edita, completa o reabre tareas; implica tambien ver tareas.
- **Administra:** acceso al engranaje y gestion de permisos; implica tambien poder ver.

La pestaña **Plantillas de WhatsApp** del engranaje importa el catalogo real, permite habilitar o deshabilitar cada plantilla y marcar advertencias adicionales. El contenido aprobado se crea o edita en GoHighLevel porque Meta debe volver a revisarlo. Telar detecta como sensibles los nombres o cuerpos relacionados con Informconf, judiciales o area legal; esa advertencia no puede quitarse y exige una confirmacion reforzada.

Los cambios quedan registrados en `gohighlevel_evento`. Cada intento manual se registra en `gohighlevel_envio_manual`, cada plantilla en `gohighlevel_envio_plantilla` y cada operacion de tarea en `gohighlevel_tarea_operacion`; ninguna auditoria almacena el cuerpo del mensaje, plantilla o descripcion de tarea. El usuario propietario inicial no puede quedar sin acceso.

## Protecciones del envio manual

- El contacto se obtiene de nuevo desde la conversacion de HighLevel; no se confia en un identificador enviado por el navegador.
- El servidor consulta hasta 100 mensajes recientes y exige un inbound de WhatsApp dentro de las ultimas 24 horas.
- La interfaz envia en un solo paso; el servidor conserva las validaciones de permiso, canal, ventana, longitud, frecuencia e idempotencia.
- Cada envio usa un token unico para evitar dobles clics y aplica limites de frecuencia.
- La auditoria guarda actor, conversacion, resultado y longitud; nunca el cuerpo del mensaje.
- Una conversacion vencida oculta el texto libre y ofrece solamente plantillas habilitadas.
- El servidor vuelve a consultar estado, idioma, categoria, variables y habilitacion inmediatamente antes del envio.
- Las plantillas con variables sin resolver quedan bloqueadas. En esta fase no se solicitan valores manuales.
- Los avisos Informconf/judiciales permanecen disponibles, pero con señal sensible y doble confirmacion.
- Enviar una plantilla no abre la ventana de 24 horas; la respuesta manual se habilita recien cuando el contacto responde.

## Migracion y reversion

- Aplicar `actualizacion_23082026_gohighlevel_fase1.sql`.
- Aplicar `actualizacion_23082026_gohighlevel_respuestas_manual.sql`.
- Aplicar `actualizacion_23082026_gohighlevel_plantillas_whatsapp.sql`.
- Aplicar `actualizacion_23082026_gohighlevel_tareas.sql`.
- Aplicar `actualizacion_24082026_gohighlevel_adjuntos_ia.sql`.
- Revertir, si fuera necesario, con `actualizacion_23082026_gohighlevel_fase1_rollback.sql`.
- La fase 2A puede revertirse por separado con `actualizacion_23082026_gohighlevel_respuestas_manual_rollback.sql`.
- La fase 2B puede revertirse por separado con `actualizacion_23082026_gohighlevel_plantillas_whatsapp_rollback.sql`.
- La fase 3 puede revertirse por separado con `actualizacion_23082026_gohighlevel_tareas_rollback.sql`.
- La fase 4 puede revertirse por separado con `actualizacion_24082026_gohighlevel_adjuntos_ia_rollback.sql`. La reversion no borra los archivos permanentes; eliminarlos requiere una autorizacion destructiva separada.

La reversion elimina solamente el indice local, los vinculos y permisos propios de la fase. No toca tareas, usuarios, conversaciones ni automatizaciones de GoHighLevel.

La integracion privada permite esta fase mediante consultas programadas. Para actualizacion instantanea por webhooks y renovacion automatica de autorizaciones, la etapa posterior debe migrar a OAuth sin retirar primero la integracion privada estable.

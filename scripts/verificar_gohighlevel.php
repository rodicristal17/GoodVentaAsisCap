<?php

/** Verificador estructural de GoHighLevel fases 1, 2A, 2B, 3 y 4. Compatible con PHP 7.2. */

$raiz = dirname(__DIR__);
$errores = array();
$comprobaciones = 0;

function ghlVerificar($condicion, $mensaje)
{
    global $errores, $comprobaciones;
    $comprobaciones++;
    if (!$condicion) {
        $errores[] = $mensaje;
    }
}

function ghlContenido($raiz, $ruta)
{
    $archivo = $raiz.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $ruta);
    return is_file($archivo) ? (string)file_get_contents($archivo) : '';
}

$archivos = array(
    'php_system/abmGoHighLevel.php',
    'php_system/gohighlevel_helper.php',
    'php_system/gohighlevel_media_ia_helper.php',
    'php_system/gohighlevel_adjunto.php',
    'js_system/gohighlevel.js',
    'css_system/gohighlevel.css',
    'iconos/gohighlevel.svg',
    'actualizacion_23082026_gohighlevel_fase1.sql',
    'actualizacion_23082026_gohighlevel_fase1_rollback.sql',
    'actualizacion_23082026_gohighlevel_respuestas_manual.sql',
    'actualizacion_23082026_gohighlevel_respuestas_manual_rollback.sql',
    'actualizacion_23082026_gohighlevel_plantillas_whatsapp.sql',
    'actualizacion_23082026_gohighlevel_plantillas_whatsapp_rollback.sql',
    'actualizacion_23082026_gohighlevel_tareas.sql',
    'actualizacion_23082026_gohighlevel_tareas_rollback.sql',
    'actualizacion_24082026_gohighlevel_adjuntos_ia.sql',
    'actualizacion_24082026_gohighlevel_adjuntos_ia_rollback.sql',
    'scripts/procesar_gohighlevel_ia.php',
    'scripts/verificar_migracion_gohighlevel_tareas.sh',
    'scripts/verificar_gohighlevel_capacidades.php',
    'scripts/fixtures/gohighlevel_tareas_schema_minimo.sql',
    'scripts/verificar_gohighlevel_plantillas.php',
    'scripts/verificar_migracion_gohighlevel_plantillas.sh',
    'scripts/fixtures/gohighlevel_plantillas_schema_minimo.sql',
    'deploy/production/README-gohighlevel.md'
);
foreach ($archivos as $archivo) {
    ghlVerificar(is_file($raiz.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $archivo)), 'Falta '.$archivo);
}

$helper = ghlContenido($raiz, 'php_system/gohighlevel_helper.php');
$mediaIa = ghlContenido($raiz, 'php_system/gohighlevel_media_ia_helper.php');
$adjuntoEndpoint = ghlContenido($raiz, 'php_system/gohighlevel_adjunto.php');
$endpoint = ghlContenido($raiz, 'php_system/abmGoHighLevel.php');
$js = ghlContenido($raiz, 'js_system/gohighlevel.js');
$css = ghlContenido($raiz, 'css_system/gohighlevel.css');
$inicio = ghlContenido($raiz, 'system/inicio.html');
$dashboardPhp = ghlContenido($raiz, 'php_system/dashboard_shortcuts.php');
$dashboardJs = ghlContenido($raiz, 'js_system/dashboard_shortcuts.js');
$compose = ghlContenido($raiz, 'deploy/production/compose.yml');
$migracion = ghlContenido($raiz, 'actualizacion_23082026_gohighlevel_fase1.sql');
$migracionEnvio = ghlContenido($raiz, 'actualizacion_23082026_gohighlevel_respuestas_manual.sql');
$migracionPlantillas = ghlContenido($raiz, 'actualizacion_23082026_gohighlevel_plantillas_whatsapp.sql');
$migracionTareas = ghlContenido($raiz, 'actualizacion_23082026_gohighlevel_tareas.sql');
$migracionFase4 = ghlContenido($raiz, 'actualizacion_24082026_gohighlevel_adjuntos_ia.sql');

ghlVerificar(strpos($helper, 'CURLOPT_SSL_VERIFYPEER => true') !== false, 'La API debe validar TLS.');
ghlVerificar(strpos($helper, 'CURLOPT_FOLLOWLOCATION => false') !== false, 'La API no debe seguir redirecciones.');
ghlVerificar(strpos($helper, 'TELAR_GOHIGHLEVEL_TOKEN_FILE') !== false, 'El token debe leerse desde archivo privado.');
ghlVerificar(strpos($helper, 'services.leadconnectorhq.com') !== false, 'El host oficial debe estar fijado.');
ghlVerificar(strpos($helper, "in_array(\$metodo, array('POST', 'PUT'), true)") !== false, 'Las tareas deben limitarse a POST y PUT.');
ghlVerificar(strpos($helper, "'/completed'") !== false, 'La finalizacion de tareas debe usar una ruta exacta.');
ghlVerificar(strpos($helper, "\$ruta = '/conversations/messages';") !== false, 'La escritura debe fijarse a la ruta de mensajes.');
ghlVerificar(strpos($helper, 'CURLOPT_POST => true') !== false, 'Falta el POST limitado para respuestas manuales.');
ghlVerificar(strpos($helper, "empty(\$config['write_enabled'])") !== false, 'La escritura debe depender de un interruptor seguro.');
ghlVerificar(strpos($helper, "empty(\$config['task_write_enabled'])") !== false, 'Las tareas deben tener un interruptor independiente.');
ghlVerificar(strpos($helper, "'/locations/'.rawurlencode(\$locationId).'/tasks/search'") !== false, 'La sincronizacion global debe usar la busqueda oficial de tareas.');
ghlVerificar(strpos($helper, "array('limit' => \$limite, 'skip' => \$skip)") !== false, 'La busqueda de tareas debe paginar con limite y desplazamiento acotados.');
ghlVerificar(strpos($helper, "'Version: v3'") !== false, 'La busqueda y gestion de tareas deben usar la version v3.');
ghlVerificar(strpos($helper, "'metodo' => 'busqueda_directa'") !== false, 'El estado debe identificar la sincronizacion directa de tareas.');
ghlVerificar(substr_count($helper, "'puede_sincronizar'") >= 2, 'La sincronizacion global de tareas debe exponer el permiso de ejecucion.');
ghlVerificar(strpos($helper, "'gohighlevel_vinculo_contacto'") !== false, 'Debe persistir vinculos locales.');
ghlVerificar(strpos($helper, "'ambiguo'") !== false, 'Debe advertir coincidencias ambiguas.');
ghlVerificar(strpos($helper, 'count($filas) === 1') !== false, 'Solo debe vincular una coincidencia unica.');
ghlVerificar(strpos($helper, 'permisos_actualizados') !== false, 'Los cambios de permisos deben ser trazables.');
ghlVerificar(strpos($helper, "'startAfter'") !== false, 'Contactos debe soportar paginacion segura.');
ghlVerificar(strpos($helper, "'startAfterDate'") !== false, 'Conversaciones debe soportar paginacion segura.');
ghlVerificar(strpos($helper, 'goHighLevelListarMensajesConversacion') !== false, 'Falta el historial de mensajes.');
ghlVerificar(strpos($helper, "'/messages'") !== false, 'Falta la ruta de mensajes de solo lectura.');
ghlVerificar(strpos($helper, 'goHighLevelVentanaWhatsApp') !== false, 'Falta validar la ventana de 24 horas.');
ghlVerificar(strpos($helper, 'goHighLevelControlFrecuenciaEnvio') !== false, 'Falta limitar la frecuencia de envios.');
ghlVerificar(strpos($helper, 'texto no almacenado') !== false, 'La auditoria debe excluir el contenido del mensaje.');
ghlVerificar(strpos($helper, "'v3'") !== false, 'El catalogo de plantillas debe usar la version oficial v3.');
ghlVerificar(strpos($helper, "'originId' => \$config['location_id']") !== false, 'El catalogo debe limitarse a la subcuenta configurada.');
ghlVerificar(strpos($helper, 'goHighLevelListarPlantillasWhatsApp') !== false, 'Falta consultar plantillas de WhatsApp.');
ghlVerificar(strpos($helper, 'goHighLevelEnviarPlantillaWhatsApp') !== false, 'Falta el envio protegido de plantillas.');
ghlVerificar(strpos($helper, 'goHighLevelPlantillaEsSensibleDetectada') !== false, 'Falta detectar avisos sensibles.');
ghlVerificar(strpos($helper, "'templateId' => \$plantilla['id']") !== false, 'El envio debe usar el identificador aprobado.');
ghlVerificar(strpos($helper, "empty(\$plantilla['tiene_variables'])") !== false, 'El servidor debe bloquear variables manuales.');
ghlVerificar(strpos($helper, 'cuerpo no almacenado') !== false, 'La auditoria de plantillas no debe copiar el cuerpo.');
ghlVerificar(strpos($helper, 'goHighLevelCatalogoUsuarios') !== false, 'Falta el catalogo de responsables.');
ghlVerificar(strpos($helper, 'goHighLevelListarTareasContacto') !== false, 'Faltan tareas por contacto.');
ghlVerificar(strpos($helper, 'goHighLevelListarTareasCache') !== false, 'Falta la bandeja global de tareas.');
ghlVerificar(strpos($helper, 'goHighLevelGestionarTarea') !== false, 'Falta la gestion protegida de tareas.');
ghlVerificar(strpos($helper, 'mine_or_unassigned') !== false, 'Las tareas ordinarias deben limitarse a propias y sin asignar.');
ghlVerificar(strpos($helper, "\$actual.',unassigned'") !== false, 'Las conversaciones ordinarias deben incluir propias y sin asignar.');
ghlVerificar(strpos($helper, "in_array(\$accion, array('crear', 'actualizar', 'completar'), true)") !== false, 'No debe habilitarse el borrado de tareas.');
ghlVerificar(strpos($helper, 'descripcion') !== false && strpos($helper, 'descripcion no almacenada') === false, 'La auditoria no debe afirmar que almacena descripciones.');
ghlVerificar(strpos($helper, 'goHighLevelRegistrarAdjuntosMensaje') !== false, 'El historial debe registrar adjuntos persistentes.');
ghlVerificar(strpos($mediaIa, 'TELAR_GOHIGHLEVEL_ATTACHMENT_KEY_FILE') !== false, 'La firma de adjuntos debe usar un secreto privado.');
ghlVerificar(strpos($mediaIa, 'CURLOPT_RESOLVE') !== false, 'La descarga de adjuntos debe fijar una IP publica validada.');
ghlVerificar(strpos($mediaIa, 'FILTER_FLAG_NO_PRIV_RANGE') !== false, 'La descarga debe bloquear redes privadas.');
ghlVerificar(strpos($mediaIa, 'goHighLevelMimeAdjuntoPermitido') !== false, 'Los adjuntos deben limitar tipos MIME.');
ghlVerificar(strpos($mediaIa, "'https://api.deepseek.com'") !== false, 'DeepSeek debe usar el host oficial fijado.');
ghlVerificar(strpos($mediaIa, 'TELAR_DEEPSEEK_API_KEY_FILE') !== false, 'La clave de DeepSeek debe leerse desde archivo privado.');
ghlVerificar(strpos($mediaIa, 'goHighLevelAnonimizarTextoIa') !== false, 'La IA debe anonimizar el contexto.');
ghlVerificar(strpos($mediaIa, 'goHighLevelRiesgoIa') !== false, 'La IA debe derivar consultas sensibles.');

ghlVerificar(strpos($endpoint, "case 'conversaciones'") !== false, 'Falta accion conversaciones.');
ghlVerificar(strpos($endpoint, "case 'mensajes_conversacion'") !== false, 'Falta accion historial de mensajes.');
ghlVerificar(strpos($endpoint, "case 'enviar_respuesta_manual'") !== false, 'Falta la accion de respuesta manual.');
ghlVerificar(strpos($endpoint, "case 'plantillas_whatsapp'") !== false, 'Falta la accion de catalogo de plantillas.');
ghlVerificar(strpos($endpoint, "case 'enviar_plantilla_whatsapp'") !== false, 'Falta la accion de envio de plantillas.');
ghlVerificar(strpos($endpoint, "case 'guardar_plantillas'") !== false, 'Falta la accion para administrar plantillas.');
ghlVerificar(strpos($endpoint, "case 'contactos'") !== false, 'Falta accion contactos.');
ghlVerificar(strpos($endpoint, "case 'oportunidades'") !== false, 'Falta accion oportunidades.');
ghlVerificar(strpos($endpoint, "case 'calendarios'") !== false, 'Falta accion calendarios.');
ghlVerificar(strpos($endpoint, "case 'guardar_permisos'") !== false, 'Falta accion guardar permisos.');
ghlVerificar(strpos($endpoint, "case 'usuarios_ghl'") !== false, 'Falta accion catalogo de usuarios.');
ghlVerificar(strpos($endpoint, "case 'guardar_vinculos_usuarios'") !== false, 'Falta accion de vinculacion de usuarios.');
ghlVerificar(strpos($endpoint, "case 'tareas_contacto'") !== false, 'Falta accion tareas por contacto.');
ghlVerificar(strpos($endpoint, "case 'tareas'") !== false, 'Falta accion bandeja de tareas.');
ghlVerificar(strpos($endpoint, "case 'sincronizar_tareas_paso'") !== false, 'Falta sincronizacion progresiva de tareas.');
ghlVerificar(strpos($endpoint, "case 'gestionar_tarea'") !== false, 'Falta accion protegida de tareas.');
ghlVerificar(strpos($endpoint, 'verificar_navegador') !== false, 'El endpoint debe validar la sesion.');
ghlVerificar(strpos($endpoint, 'empty($contexto[\'puede_ver\'])') !== false, 'El endpoint debe aplicar el permiso de consulta.');
ghlVerificar(strpos($endpoint, "case 'sugerir_respuesta_ia'") !== false, 'Falta la accion de sugerencia con IA.');
ghlVerificar(strpos($endpoint, "case 'guardar_configuracion_ia'") !== false, 'Falta administrar la IA desde el engranaje.');
ghlVerificar(strpos($adjuntoEndpoint, 'hash_equals') !== false, 'El adjunto debe validar una firma temporal.');
ghlVerificar(strpos($adjuntoEndpoint, 'realpath') !== false, 'El adjunto debe impedir escapes de ruta.');

ghlVerificar(strpos($js, 'tab: "conversaciones"') !== false, 'Conversaciones debe ser la vista inicial.');
ghlVerificar(strpos($js, 'data-ghl-action=\'toggle-summary\'') !== false, 'El resumen debe ser plegable.');
ghlVerificar(strpos($js, 'data-ghl-action=\'settings\'') !== false, 'Debe existir el engranaje del modulo.');
ghlVerificar(strpos($js, 'Coincidencia ambigua') !== false, 'La UI debe advertir coincidencias ambiguas.');
ghlVerificar(strpos($js, 'Tareas habilitadas') !== false, 'La UI debe indicar cuando la gestion de tareas esta habilitada.');
ghlVerificar(strpos($js, 'Solo lectura') !== false, 'La UI debe conservar el estado de solo lectura cuando corresponda.');
ghlVerificar(strpos($js, 'abrirGoHighLevel') !== false, 'Falta la funcion de apertura.');
ghlVerificar(strpos($js, 'data-ghl-search-form') !== false, 'Falta la busqueda dentro del modulo.');
ghlVerificar(strpos($js, "data-ghl-action='load-more'") !== false, 'Falta el acceso a paginas adicionales.');
ghlVerificar(strpos($js, 'mensajes_conversacion') !== false, 'Falta el visor de conversaciones.');
ghlVerificar(strpos($js, 'enviar_respuesta_manual') !== false, 'Falta conectar la respuesta manual.');
ghlVerificar(strpos($js, "data-ghl-action='confirm-send'") === false, 'La respuesta manual no debe exigir una segunda confirmacion.');
ghlVerificar(strpos($js, 'data-ghl-rules-confirmed') === false, 'La respuesta manual no debe exigir una casilla redundante.');
ghlVerificar(strpos($js, 'ghl-btn--compact') !== false && strpos($js, '> Enviar</button>') !== false, 'Falta el envio manual compacto de un solo paso.');
ghlVerificar(strpos($js, "class='ghl-conversation-scroll'") !== false, 'El historial debe tener un contenedor de desplazamiento propio.');
ghlVerificar(strpos($js, '<strong>24 h abierta</strong>') !== false, 'La ventana abierta debe mostrarse como un indicador compacto.');
ghlVerificar(strpos($js, "data-ghl-manual-reply maxlength='2000' rows='1'") !== false, 'La respuesta manual debe comenzar en una sola linea.');
ghlVerificar(strpos($js, 'resizeManualReply') !== false, 'La respuesta manual debe crecer automaticamente hasta su limite.');
ghlVerificar(strpos($js, 'function conversationScrollSnapshot') !== false, 'Falta recordar la posicion al consultar mensajes anteriores.');
ghlVerificar(strpos($js, 'scroll.scrollTop = scroll.scrollHeight') !== false, 'La conversacion debe abrir en el mensaje mas reciente.');
ghlVerificar(strpos($js, 'renderConversationDetail(scrollSnapshot)') !== false, 'Cargar mensajes anteriores debe conservar la posicion de lectura.');
ghlVerificar(strpos($js, '>Respuesta manual por WhatsApp</label>') === false, 'El compositor compacto no debe repetir un titulo visible.');
ghlVerificar(strpos($js, 'Telar valida permiso, canal y ventana antes de cada envío.') === false, 'El chat no debe reservar un pie redundante para validaciones internas.');
ghlVerificar(strpos($js, 'event.target.querySelector(".ghl-conversation-modal")') !== false, 'La conversacion debe poder cerrarse al pulsar fuera sin afectar otros modales.');
ghlVerificar(strpos($js, "data-permission='reply'") !== false, 'Falta el permiso Responde en el engranaje.');
ghlVerificar(strpos($js, "data-permission='template'") !== false, 'Falta el permiso Plantillas en el engranaje.');
ghlVerificar(strpos($js, 'Plantillas de WhatsApp') !== false, 'Falta la administracion de plantillas en el engranaje.');
ghlVerificar(strpos($js, 'enviar_plantilla_whatsapp') !== false, 'Falta conectar el envio de plantillas.');
ghlVerificar(strpos($js, "data-ghl-sensitive-confirm") !== false, 'Falta la confirmacion reforzada para avisos sensibles.');
ghlVerificar(strpos($js, 'variables manuales') !== false, 'La UI debe explicar el bloqueo de variables manuales.');
ghlVerificar(strpos($js, 'Ventana de 24 horas cerrada') !== false, 'La UI debe bloquear texto libre fuera de ventana.');
ghlVerificar(strpos($js, 'tabButton("tareas"') !== false, 'Falta la pestaña Tareas.');
ghlVerificar(strpos($js, 'data-ghl-filter=\'assigned\'') !== false, 'Falta el filtro por responsable.');
ghlVerificar(strpos($js, 'data-ghl-action=\'sync-tasks\'') !== false, 'Falta la sincronizacion progresiva desde la interfaz.');
ghlVerificar(strpos($js, 'data-ghl-task-form') !== false, 'Falta el editor de tareas.');
ghlVerificar(strpos($js, 'data-settings-tab=\'usuarios\'') !== false, 'Falta administrar responsables desde el engranaje.');
ghlVerificar(strpos($js, "data-permission='team'") !== false, 'Falta el permiso Ver equipo.');
ghlVerificar(strpos($js, "data-permission='manage-tasks'") !== false, 'Falta el permiso Gestiona tareas.');
ghlVerificar(strpos($js, "data-settings-tab='ia'") !== false, 'Falta la configuracion de IA en el engranaje.');
ghlVerificar(strpos($js, "data-ghl-action='suggest-ai'") !== false, 'Falta el boton de borrador con IA.');
ghlVerificar(strpos($js, 'renderMessageAttachments') !== false, 'Falta visualizar adjuntos dentro del chat.');
ghlVerificar(strpos($css, '#telarGoHighLevel') !== false, 'El CSS debe estar limitado al modulo.');
ghlVerificar(strpos($css, 'grid-template-columns: minmax(0, 1fr)') !== false, 'La lista de conversaciones debe respetar el ancho visible.');
ghlVerificar(strpos($css, 'overflow-x: hidden') !== false, 'El modulo debe impedir el desplazamiento horizontal involuntario.');
ghlVerificar(strpos($css, '.ghl-conversation__main { width: 0; min-width: 0;') !== false, 'El contenido de cada conversacion debe poder contraerse.');
ghlVerificar(strpos($css, '.ghl-composer') !== false, 'Falta el compositor protegido.');
ghlVerificar(strpos($css, '.ghl-conversation-scroll { flex: 1 1 auto;') !== false, 'El desplazamiento debe limitarse al historial de mensajes.');
ghlVerificar(strpos($css, '.ghl-composer { flex: 0 0 auto;') !== false, 'El compositor debe permanecer fijo dentro del modal.');
ghlVerificar(strpos($css, '.ghl-composer--manual') !== false && strpos($css, '.ghl-manual-row') !== false, 'Falta el compositor manual compacto en una fila.');
ghlVerificar(strpos($css, '.ghl-conversation-modal > footer') === false, 'La conversacion no debe reservar espacio para un pie redundante.');
ghlVerificar(strpos($css, '.ghl-template-setting') !== false, 'Falta el estilo del catalogo administrable.');
ghlVerificar(strpos($css, '.ghl-template-confirm.is-sensitive') !== false, 'Falta diferenciar la confirmacion sensible.');
ghlVerificar(strpos($css, '.ghl-task-list') !== false, 'Falta el estilo de la bandeja de tareas.');
ghlVerificar(strpos($css, '.ghl-contact-tasks') !== false, 'Falta el panel plegable de tareas del contacto.');
ghlVerificar(strpos($css, '.ghl-user-links') !== false, 'Falta el estilo de vinculacion de responsables.');

ghlVerificar(strpos($inicio, 'divMenuGoHighLevel') !== false, 'Falta el acceso GoHighLevel.');
ghlVerificar(strpos($inicio, 'divGoHighLevel') !== false, 'Falta el contenedor GoHighLevel.');
ghlVerificar(strpos($inicio, 'gohighlevel.css') !== false, 'Falta incluir el CSS.');
ghlVerificar(strpos($inicio, 'gohighlevel.js') !== false, 'Falta incluir el JavaScript.');
ghlVerificar(strpos($dashboardPhp, 'dashboard_user_can_access_gohighlevel') !== false, 'Falta el filtro de acceso servidor.');
ghlVerificar(strpos($dashboardJs, 'divMenuGoHighLevel') !== false, 'Falta registrar el acceso en el escritorio.');

ghlVerificar(strpos($compose, 'TELAR_GOHIGHLEVEL_TOKEN_FILE: /run/secrets/gohighlevel_readonly_token') !== false, 'Compose debe apuntar al secreto.');
ghlVerificar(strpos($compose, 'TELAR_GOHIGHLEVEL_WRITE_ENABLED: ${TELAR_GOHIGHLEVEL_WRITE_ENABLED:-false}') !== false, 'El envio debe permanecer apagado por defecto.');
ghlVerificar(strpos($compose, 'TELAR_GOHIGHLEVEL_TASK_WRITE_ENABLED: ${TELAR_GOHIGHLEVEL_TASK_WRITE_ENABLED:-false}') !== false, 'La escritura de tareas debe permanecer apagada por defecto.');
ghlVerificar(strpos($compose, 'TELAR_GOHIGHLEVEL_COMPANY_ID') !== false, 'Compose debe admitir el company ID opcional.');
ghlVerificar(strpos($compose, 'TELAR_DEEPSEEK_AUTO_REPLY_ENABLED: ${TELAR_DEEPSEEK_AUTO_REPLY_ENABLED:-false}') !== false, 'La respuesta automatica debe permanecer apagada por defecto.');
ghlVerificar(strpos($compose, 'telar_ghl_media:/var/lib/telar/gohighlevel_adjuntos') !== false, 'Los adjuntos deben usar un volumen persistente.');
ghlVerificar(strpos($compose, './secrets:/run/secrets:ro') !== false, 'El secreto debe montarse en modo lectura.');
ghlVerificar(strpos($migracion, 'gohighlevel_permiso_usuario') !== false, 'Falta tabla de permisos.');
ghlVerificar(strpos($migracion, 'gohighlevel_vinculo_contacto') !== false, 'Falta tabla de vinculos.');
ghlVerificar(strpos($migracion, 'gohighlevel_evento') !== false, 'Falta tabla de eventos.');
ghlVerificar(strpos($migracion, "'gohighlevel','GoHighLevel'") !== false, 'Falta catalogar el acceso.');
ghlVerificar(strpos($migracionEnvio, 'puede_responder') !== false, 'Falta el permiso de respuesta.');
ghlVerificar(strpos($migracionEnvio, 'gohighlevel_envio_manual') !== false, 'Falta la auditoria de envios manuales.');
ghlVerificar(strpos($migracionEnvio, 'longitud_mensaje') !== false, 'La auditoria debe guardar solo longitud, no contenido.');
ghlVerificar(strpos($migracionPlantillas, 'puede_enviar_plantilla') !== false, 'Falta el permiso separado para plantillas.');
ghlVerificar(strpos($migracionPlantillas, 'gohighlevel_plantilla_config') !== false, 'Falta el catalogo local de plantillas.');
ghlVerificar(strpos($migracionPlantillas, 'gohighlevel_envio_plantilla') !== false, 'Falta la auditoria separada de plantillas.');
ghlVerificar(strpos($migracionPlantillas, 'cuerpo TEXT') === false, 'La migracion no debe almacenar el cuerpo de las plantillas.');
ghlVerificar(strpos($migracionTareas, 'gohighlevel_usuario_vinculo') !== false, 'Falta el vinculo local de responsables.');
ghlVerificar(strpos($migracionTareas, 'gohighlevel_tarea_cache') !== false, 'Falta el indice local de tareas.');
ghlVerificar(strpos($migracionTareas, 'gohighlevel_tarea_operacion') !== false, 'Falta la idempotencia de tareas.');
ghlVerificar(strpos($migracionTareas, 'email_hash') !== false && strpos($migracionTareas, 'email VARCHAR') === false, 'No debe persistirse el correo de GoHighLevel en claro.');
ghlVerificar(strpos($migracionFase4, 'gohighlevel_adjunto_cache') !== false, 'Falta el indice persistente de adjuntos.');
ghlVerificar(strpos($migracionFase4, 'gohighlevel_ia_config') !== false, 'Falta la configuracion local de IA.');
ghlVerificar(strpos($migracionFase4, 'gohighlevel_ia_operacion') !== false, 'Falta la auditoria de IA.');
ghlVerificar(strpos($migracionFase4, 'api_key') === false, 'La migracion no debe almacenar la clave de DeepSeek.');

$sensibles = array('/pit-[A-Za-z0-9_-]{20,}/', '/Bearer\s+[A-Za-z0-9_-]{25,}/');
foreach ($sensibles as $patron) {
    ghlVerificar(!preg_match($patron, $helper.$mediaIa.$endpoint.$adjuntoEndpoint.$js.$compose.$migracion.$migracionEnvio.$migracionPlantillas.$migracionTareas.$migracionFase4), 'Se encontro una credencial con apariencia real.');
}

if (count($errores) > 0) {
    fwrite(STDERR, "GoHighLevel: ".count($errores)." error(es) en ".$comprobaciones." comprobaciones.\n");
    foreach ($errores as $error) {
        fwrite(STDERR, "- ".$error."\n");
    }
    exit(1);
}

echo "GoHighLevel: ".$comprobaciones." comprobaciones correctas.\n";
exit(0);

?>

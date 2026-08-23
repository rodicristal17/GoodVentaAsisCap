<?php

/** Verificador estructural de GoHighLevel fase 1. Compatible con PHP 7.2. */

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
    'js_system/gohighlevel.js',
    'css_system/gohighlevel.css',
    'iconos/gohighlevel.svg',
    'actualizacion_23082026_gohighlevel_fase1.sql',
    'actualizacion_23082026_gohighlevel_fase1_rollback.sql',
    'deploy/production/README-gohighlevel.md'
);
foreach ($archivos as $archivo) {
    ghlVerificar(is_file($raiz.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $archivo)), 'Falta '.$archivo);
}

$helper = ghlContenido($raiz, 'php_system/gohighlevel_helper.php');
$endpoint = ghlContenido($raiz, 'php_system/abmGoHighLevel.php');
$js = ghlContenido($raiz, 'js_system/gohighlevel.js');
$css = ghlContenido($raiz, 'css_system/gohighlevel.css');
$inicio = ghlContenido($raiz, 'system/inicio.html');
$dashboardPhp = ghlContenido($raiz, 'php_system/dashboard_shortcuts.php');
$dashboardJs = ghlContenido($raiz, 'js_system/dashboard_shortcuts.js');
$compose = ghlContenido($raiz, 'deploy/production/compose.yml');
$migracion = ghlContenido($raiz, 'actualizacion_23082026_gohighlevel_fase1.sql');

ghlVerificar(strpos($helper, 'CURLOPT_SSL_VERIFYPEER => true') !== false, 'La API debe validar TLS.');
ghlVerificar(strpos($helper, 'CURLOPT_FOLLOWLOCATION => false') !== false, 'La API no debe seguir redirecciones.');
ghlVerificar(strpos($helper, 'TELAR_GOHIGHLEVEL_TOKEN_FILE') !== false, 'El token debe leerse desde archivo privado.');
ghlVerificar(strpos($helper, 'services.leadconnectorhq.com') !== false, 'El host oficial debe estar fijado.');
ghlVerificar(strpos($helper, 'CURLOPT_CUSTOMREQUEST') === false, 'Fase 1 no debe definir operaciones de escritura HTTP.');
ghlVerificar(strpos($helper, 'CURLOPT_POST') === false, 'Fase 1 no debe enviar escrituras a HighLevel.');
ghlVerificar(strpos($helper, "'gohighlevel_vinculo_contacto'") !== false, 'Debe persistir vinculos locales.');
ghlVerificar(strpos($helper, "'ambiguo'") !== false, 'Debe advertir coincidencias ambiguas.');
ghlVerificar(strpos($helper, 'count($filas) === 1') !== false, 'Solo debe vincular una coincidencia unica.');
ghlVerificar(strpos($helper, 'permisos_actualizados') !== false, 'Los cambios de permisos deben ser trazables.');
ghlVerificar(strpos($helper, "'startAfter'") !== false, 'Contactos debe soportar paginacion segura.');
ghlVerificar(strpos($helper, "'startAfterDate'") !== false, 'Conversaciones debe soportar paginacion segura.');
ghlVerificar(strpos($helper, 'goHighLevelListarMensajesConversacion') !== false, 'Falta el historial de mensajes.');
ghlVerificar(strpos($helper, "'/messages'") !== false, 'Falta la ruta de mensajes de solo lectura.');

ghlVerificar(strpos($endpoint, "case 'conversaciones'") !== false, 'Falta accion conversaciones.');
ghlVerificar(strpos($endpoint, "case 'mensajes_conversacion'") !== false, 'Falta accion historial de mensajes.');
ghlVerificar(strpos($endpoint, "case 'contactos'") !== false, 'Falta accion contactos.');
ghlVerificar(strpos($endpoint, "case 'oportunidades'") !== false, 'Falta accion oportunidades.');
ghlVerificar(strpos($endpoint, "case 'calendarios'") !== false, 'Falta accion calendarios.');
ghlVerificar(strpos($endpoint, "case 'guardar_permisos'") !== false, 'Falta accion guardar permisos.');
ghlVerificar(strpos($endpoint, 'verificar_navegador') !== false, 'El endpoint debe validar la sesion.');
ghlVerificar(strpos($endpoint, 'empty($contexto[\'puede_ver\'])') !== false, 'El endpoint debe aplicar el permiso de consulta.');

ghlVerificar(strpos($js, 'tab: "conversaciones"') !== false, 'Conversaciones debe ser la vista inicial.');
ghlVerificar(strpos($js, 'data-ghl-action=\'toggle-summary\'') !== false, 'El resumen debe ser plegable.');
ghlVerificar(strpos($js, 'data-ghl-action=\'settings\'') !== false, 'Debe existir el engranaje del modulo.');
ghlVerificar(strpos($js, 'Coincidencia ambigua') !== false, 'La UI debe advertir coincidencias ambiguas.');
ghlVerificar(strpos($js, 'Solo lectura') !== false, 'La UI debe indicar solo lectura.');
ghlVerificar(strpos($js, 'abrirGoHighLevel') !== false, 'Falta la funcion de apertura.');
ghlVerificar(strpos($js, 'data-ghl-search-form') !== false, 'Falta la busqueda dentro del modulo.');
ghlVerificar(strpos($js, "data-ghl-action='load-more'") !== false, 'Falta el acceso a paginas adicionales.');
ghlVerificar(strpos($js, 'mensajes_conversacion') !== false, 'Falta el visor de conversaciones.');
ghlVerificar(strpos($css, '#telarGoHighLevel') !== false, 'El CSS debe estar limitado al modulo.');
ghlVerificar(strpos($css, 'grid-template-columns: minmax(0, 1fr)') !== false, 'La lista de conversaciones debe respetar el ancho visible.');
ghlVerificar(strpos($css, 'overflow-x: hidden') !== false, 'El modulo debe impedir el desplazamiento horizontal involuntario.');
ghlVerificar(strpos($css, '.ghl-conversation__main { width: 0; min-width: 0;') !== false, 'El contenido de cada conversacion debe poder contraerse.');

ghlVerificar(strpos($inicio, 'divMenuGoHighLevel') !== false, 'Falta el acceso GoHighLevel.');
ghlVerificar(strpos($inicio, 'divGoHighLevel') !== false, 'Falta el contenedor GoHighLevel.');
ghlVerificar(strpos($inicio, 'gohighlevel.css') !== false, 'Falta incluir el CSS.');
ghlVerificar(strpos($inicio, 'gohighlevel.js') !== false, 'Falta incluir el JavaScript.');
ghlVerificar(strpos($dashboardPhp, 'dashboard_user_can_access_gohighlevel') !== false, 'Falta el filtro de acceso servidor.');
ghlVerificar(strpos($dashboardJs, 'divMenuGoHighLevel') !== false, 'Falta registrar el acceso en el escritorio.');

ghlVerificar(strpos($compose, 'TELAR_GOHIGHLEVEL_TOKEN_FILE: /run/secrets/gohighlevel_readonly_token') !== false, 'Compose debe apuntar al secreto.');
ghlVerificar(strpos($compose, './secrets:/run/secrets:ro') !== false, 'El secreto debe montarse en modo lectura.');
ghlVerificar(strpos($migracion, 'gohighlevel_permiso_usuario') !== false, 'Falta tabla de permisos.');
ghlVerificar(strpos($migracion, 'gohighlevel_vinculo_contacto') !== false, 'Falta tabla de vinculos.');
ghlVerificar(strpos($migracion, 'gohighlevel_evento') !== false, 'Falta tabla de eventos.');
ghlVerificar(strpos($migracion, "'gohighlevel','GoHighLevel'") !== false, 'Falta catalogar el acceso.');

$sensibles = array('/pit-[A-Za-z0-9_-]{20,}/', '/Bearer\s+[A-Za-z0-9_-]{25,}/');
foreach ($sensibles as $patron) {
    ghlVerificar(!preg_match($patron, $helper.$endpoint.$js.$compose.$migracion), 'Se encontro una credencial con apariencia real.');
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

<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/central_telefonica_helper.php';
require_once __DIR__.'/gohighlevel_helper.php';

function goHighLevelParametro($nombre, $predeterminado = '')
{
    return isset($_POST[$nombre]) ? $_POST[$nombre] : $predeterminado;
}

function goHighLevelResponder($ok, $codigo, $mensaje, $datos, $estadoHttp)
{
    http_response_code(intval($estadoHttp));
    echo json_encode(
        centralTelefonicaUtf8(array(
            'ok' => (bool)$ok,
            'codigo' => (string)$codigo,
            'mensaje' => (string)$mensaje,
            'datos' => is_array($datos) ? $datos : array()
        )),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

try {
    $accion = goHighLevelTexto(goHighLevelParametro('accion', ''), 50);
    $codUsuario = intval(goHighLevelParametro('useru', 0));
    $pass = str_replace('=', '+', (string)goHighLevelParametro('passu', ''));
    $navegador = goHighLevelTexto(goHighLevelParametro('navegador', ''), 100);
    if ($accion === '' || $codUsuario <= 0 || $pass === '' || $navegador === '') {
        goHighLevelResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    if (verificar_navegador($codUsuario, $navegador, $pass) !== 'ok') {
        goHighLevelResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        goHighLevelResponder(false, 'conexion_no_disponible', 'No se pudo conectar con Telar.', array(), 500);
    }
    if (!goHighLevelEstructuraDisponible($mysqli)) {
        goHighLevelResponder(
            false,
            'gohighlevel_no_instalado',
            'El modulo GoHighLevel todavia no tiene aplicada su migracion.',
            array(),
            503
        );
    }
    $contexto = goHighLevelContextoUsuario($mysqli, $codUsuario);
    $config = goHighLevelConfiguracion();
    switch ($accion) {
        case 'contexto':
            goHighLevelResponder(true, 'contexto_obtenido', 'GoHighLevel esta listo.', array(
                'usuario' => $contexto,
                'integracion' => array(
                    'configurado' => goHighLevelConfigurado($config),
                    'modo' => 'solo_lectura',
                    'location_id' => (string)$config['location_id']
                )
            ), 200);
            break;
        case 'conversaciones':
            goHighLevelResponder(true, 'conversaciones_obtenidas', 'Conversaciones actualizadas.',
                goHighLevelListarConversaciones($mysqli, $config, $_POST), 200);
            break;
        case 'contactos':
            goHighLevelResponder(true, 'contactos_obtenidos', 'Contactos actualizados.',
                goHighLevelListarContactos($mysqli, $config, $_POST), 200);
            break;
        case 'oportunidades':
            goHighLevelResponder(true, 'oportunidades_obtenidas', 'Oportunidades actualizadas.',
                goHighLevelListarOportunidades($mysqli, $config, $_POST), 200);
            break;
        case 'calendarios':
            goHighLevelResponder(true, 'calendarios_obtenidos', 'Calendarios actualizados.',
                goHighLevelListarCalendarios($config), 200);
            break;
        case 'resumen':
            goHighLevelResponder(true, 'resumen_obtenido', 'Resumen actualizado.',
                goHighLevelResumen($mysqli, $config), 200);
            break;
        case 'sincronizacion':
            goHighLevelResponder(true, 'sincronizacion_obtenida', 'Estado actualizado.',
                goHighLevelEstadoSincronizacion($mysqli, $config), 200);
            break;
        case 'configuracion_permisos':
            goHighLevelResponder(true, 'permisos_obtenidos', 'Permisos actualizados.',
                goHighLevelUsuariosPermisos($mysqli, $contexto), 200);
            break;
        case 'guardar_permisos':
            goHighLevelResponder(true, 'permisos_guardados', 'Configuracion y permisos guardados.',
                goHighLevelGuardarPermisos(
                    $mysqli,
                    $contexto,
                    goHighLevelParametro('permisos', '[]')
                ), 200);
            break;
        default:
            goHighLevelLanzar('accion_no_reconocida', 'La accion solicitada no existe.', array(), 400);
    }
} catch (GoHighLevelExcepcion $e) {
    goHighLevelResponder(
        false,
        $e->codigoOperacion,
        $e->getMessage(),
        $e->datosOperacion,
        $e->estadoHttp
    );
} catch (Exception $e) {
    error_log('GoHighLevel: error interno sin datos sensibles');
    goHighLevelResponder(
        false,
        'error_interno',
        'No se pudo completar la consulta de GoHighLevel.',
        array(),
        500
    );
}

?>

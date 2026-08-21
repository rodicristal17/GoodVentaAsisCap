<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/mi_cartera_helper.php';

function miCarteraParametro($nombre, $predeterminado = '')
{
    return isset($_POST[$nombre]) ? $_POST[$nombre] : $predeterminado;
}

function miCarteraResponder($ok, $codigo, $mensaje, $datos, $estadoHttp)
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
    $accion = miCarteraTexto(miCarteraParametro('accion', ''), 50);
    $codUsuario = intval(miCarteraParametro('useru', 0));
    $pass = str_replace('=', '+', (string)miCarteraParametro('passu', ''));
    $navegador = miCarteraTexto(miCarteraParametro('navegador', ''), 100);
    if ($accion === '' || $codUsuario <= 0 || $pass === '' || $navegador === '') {
        miCarteraResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    if (verificar_navegador($codUsuario, $navegador, $pass) !== 'ok') {
        miCarteraResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        miCarteraResponder(false, 'conexion_no_disponible', 'No se pudo conectar con Telar.', array(), 500);
    }
    if (!miCarteraEstructuraDisponible($mysqli)) {
        miCarteraResponder(
            false,
            'mi_cartera_no_instalada',
            'Mi cartera todavia no tiene aplicada su migracion.',
            array(),
            503
        );
    }
    $contexto = miCarteraContextoUsuario($mysqli, $codUsuario);
    switch ($accion) {
        case 'contexto':
            miCarteraResponder(
                true,
                'contexto_obtenido',
                'Mi cartera esta lista.',
                miCarteraContextoModulo($mysqli, $contexto),
                200
            );
            break;
        case 'listar':
            miCarteraResponder(
                true,
                'cartera_obtenida',
                'Cartera actualizada.',
                miCarteraListar($mysqli, $contexto, $_POST),
                200
            );
            break;
        case 'detalle':
            miCarteraResponder(
                true,
                'detalle_obtenido',
                'Seguimiento obtenido.',
                miCarteraDetalle($mysqli, $contexto, miCarteraParametro('id_asignacion', 0)),
                200
            );
            break;
        case 'guardar_gestion':
            miCarteraResponder(
                true,
                'gestion_guardada',
                'Resultado y siguiente accion guardados.',
                miCarteraGuardarGestion($mysqli, $contexto, $_POST),
                201
            );
            break;
        case 'sincronizar':
            miCarteraResponder(
                true,
                'cartera_sincronizada',
                'Estados y escalamientos revisados.',
                miCarteraSincronizar($mysqli, $contexto),
                200
            );
            break;
        case 'configuracion':
            miCarteraResponder(
                true,
                'configuracion_obtenida',
                'Responsables disponibles.',
                miCarteraCatalogosConfiguracion($mysqli, $contexto),
                200
            );
            break;
        case 'guardar_configuracion':
            miCarteraResponder(
                true,
                'configuracion_guardada',
                'Equipo de cartera actualizado.',
                miCarteraGuardarConfiguracion($mysqli, $contexto, $_POST),
                200
            );
            break;
        case 'previsualizar_configuracion':
            miCarteraResponder(
                true,
                'configuracion_previsualizada',
                'Revise el impacto antes de guardar la configuracion.',
                miCarteraPrevisualizarConfiguracion($mysqli, $contexto, $_POST),
                200
            );
            break;
        case 'previsualizar_asignacion':
            miCarteraResponder(
                true,
                'asignacion_previsualizada',
                'Revise el reparto antes de confirmarlo.',
                miCarteraPrevisualizarAsignacion($mysqli, $contexto),
                200
            );
            break;
        case 'confirmar_asignacion':
            if (function_exists('set_time_limit')) {
                @set_time_limit(120);
            }
            miCarteraResponder(
                true,
                'asignacion_confirmada',
                'La cartera fue repartida y quedo trazable.',
                miCarteraConfirmarAsignacion($mysqli, $contexto),
                201
            );
            break;
        case 'tomar_caso_jefe':
            miCarteraResponder(
                true,
                'caso_asignado_al_jefe',
                'El caso ya forma parte de la cartera especial del jefe.',
                miCarteraTomarCasoJefe(
                    $mysqli,
                    $contexto,
                    miCarteraParametro('id_asignacion', 0)
                ),
                200
            );
            break;
        default:
            miCarteraLanzar('accion_no_reconocida', 'La accion solicitada no existe.');
    }
} catch (MiCarteraExcepcion $e) {
    $estado = in_array(
        $e->codigoOperacion,
        array('acceso_no_configurado', 'accion_no_autorizada'),
        true
    ) ? 403 : 422;
    miCarteraResponder(false, $e->codigoOperacion, $e->getMessage(), $e->datosOperacion, $estado);
} catch (Exception $e) {
    error_log('MiCartera: '.$e->getMessage());
    miCarteraResponder(
        false,
        'error_interno',
        'No se pudo completar la operacion de cartera.',
        array(),
        500
    );
}

?>

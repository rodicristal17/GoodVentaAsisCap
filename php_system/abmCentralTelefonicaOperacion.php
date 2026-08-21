<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/central_telefonica_operacion_helper.php';

function centralTelefonicaOperacionParametro($nombre, $predeterminado = '')
{
    return isset($_POST[$nombre]) ? $_POST[$nombre] : $predeterminado;
}

function centralTelefonicaOperacionResponder($ok, $codigo, $mensaje, $datos, $estadoHttp)
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
    $accion = centralTelefonicaOperacionTexto(
        centralTelefonicaOperacionParametro('accion', ''),
        50
    );
    $codUsuario = intval(centralTelefonicaOperacionParametro('useru', 0));
    $pass = str_replace('=', '+', (string)centralTelefonicaOperacionParametro('passu', ''));
    $navegador = centralTelefonicaOperacionTexto(
        centralTelefonicaOperacionParametro('navegador', ''),
        100
    );
    if ($accion === '' || $codUsuario <= 0 || $pass === '' || $navegador === '') {
        centralTelefonicaOperacionResponder(
            false,
            'sesion_invalida',
            'La sesion no es valida.',
            array(),
            401
        );
    }
    if (verificar_navegador($codUsuario, $navegador, $pass) !== 'ok') {
        centralTelefonicaOperacionResponder(
            false,
            'sesion_invalida',
            'La sesion no es valida.',
            array(),
            401
        );
    }
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        centralTelefonicaOperacionResponder(
            false,
            'conexion_no_disponible',
            'No se pudo conectar con Telar.',
            array(),
            500
        );
    }
    if (!centralTelefonicaOperacionEstructuraDisponible($mysqli)) {
        centralTelefonicaOperacionResponder(
            false,
            'operacion_no_instalada',
            'La gestion de llamadas todavia no tiene aplicada su migracion.',
            array(),
            503
        );
    }
    $contexto = centralTelefonicaOperacionContextoUsuario($mysqli, $codUsuario);
    switch ($accion) {
        case 'estado_operativo':
        case 'consultar_actividad':
            centralTelefonicaOperacionResponder(
                true,
                'actividad_obtenida',
                'Estado telefonico obtenido.',
                centralTelefonicaOperacionActividad($mysqli, $contexto),
                200
            );
            break;
        case 'buscar_pacientes':
            $resultadoIndice = $mysqli->query(
                "SELECT COUNT(*) total FROM central_telefonica_paciente_telefono WHERE activo=1"
            );
            $filaIndice = $resultadoIndice
                ? $resultadoIndice->fetch_assoc() : array('total' => 0);
            if (intval($filaIndice['total']) === 0) {
                centralTelefonicaOperacionRefrescarTelefonos($mysqli);
            }
            centralTelefonicaOperacionResponder(
                true,
                'pacientes_obtenidos',
                'Pacientes encontrados.',
                centralTelefonicaOperacionBuscarPacientes(
                    $mysqli,
                    centralTelefonicaOperacionParametro('busqueda', '')
                ),
                200
            );
            break;
        case 'obtener_paciente':
            $codCliente = intval(centralTelefonicaOperacionParametro('cod_cliente', 0));
            $paciente = centralTelefonicaOperacionPaciente($mysqli, $codCliente, false);
            if (!$paciente) {
                centralTelefonicaOperacionLanzar(
                    'paciente_no_disponible',
                    'No se encontro el paciente seleccionado.'
                );
            }
            centralTelefonicaOperacionResponder(
                true,
                'paciente_obtenido',
                'Paciente y telefonos obtenidos.',
                array('paciente' => $paciente),
                200
            );
            break;
        case 'resolver_hilo_paciente':
            $codCliente = intval(centralTelefonicaOperacionParametro('cod_cliente', 0));
            $paciente = centralTelefonicaOperacionPaciente($mysqli, $codCliente, false);
            if (!$paciente) {
                centralTelefonicaOperacionLanzar(
                    'paciente_no_disponible',
                    'No se encontro el paciente seleccionado.'
                );
            }
            $hilo = centralTelefonicaOperacionResolverHiloPaciente(
                $mysqli,
                $codCliente,
                intval($contexto['cod_usuario']),
                intval(centralTelefonicaOperacionParametro('cod_interconsulta', 0)),
                true
            );
            if (!$hilo) {
                centralTelefonicaOperacionLanzar(
                    'hilo_no_disponible',
                    'El paciente no tiene una venta desde la cual preparar su Hilo.'
                );
            }
            centralTelefonicaOperacionResponder(
                true,
                'hilo_resuelto',
                'Hilo del paciente localizado.',
                array('hilo' => $hilo),
                200
            );
            break;
        case 'solicitar_llamada':
            centralTelefonicaOperacionResponder(
                true,
                'llamada_solicitada',
                'La solicitud fue enviada. Atienda su MicroSIP para completar la llamada.',
                centralTelefonicaOperacionSolicitarLlamada(
                    $mysqli,
                    $contexto,
                    $_POST,
                    isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
                ),
                201
            );
            break;
        default:
            centralTelefonicaOperacionLanzar(
                'accion_no_reconocida',
                'La accion solicitada no existe.'
            );
    }
} catch (CentralTelefonicaOperacionExcepcion $e) {
    $estado = in_array(
        $e->codigoOperacion,
        array('conector_no_disponible', 'operacion_no_instalada'),
        true
    ) ? 503 : 422;
    centralTelefonicaOperacionResponder(
        false,
        $e->codigoOperacion,
        $e->getMessage(),
        $e->datosOperacion,
        $estado
    );
} catch (Exception $e) {
    error_log('CentralTelefonicaOperacion: '.$e->getMessage());
    centralTelefonicaOperacionResponder(
        false,
        'error_interno',
        'No se pudo completar la operacion telefonica.',
        array(),
        500
    );
}

?>

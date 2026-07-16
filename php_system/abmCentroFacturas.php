<?php

ob_start();
ini_set('display_errors', '0');

require_once('conexion.php');
include_once('verificar_navegador.php');
include_once('buscar_nivel.php');
require_once('interconsulta_seguimiento_programado_helper.php');
require_once('centro_facturas_helper.php');

date_default_timezone_set('America/Asuncion');

function centroFacturasResponder($datos)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(centroFacturaValorUtf8($datos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function centroFacturasPost($clave, $predeterminado = '')
{
    return isset($_POST[$clave]) ? $_POST[$clave] : $predeterminado;
}

function centroFacturasJsonPost($clave, $predeterminado = array())
{
    $valor = centroFacturasPost($clave, '');
    if (is_array($valor)) {
        return $valor;
    }
    $datos = json_decode((string)$valor, true);
    return is_array($datos) ? $datos : $predeterminado;
}

function centroFacturasArchivos($clave = 'archivos')
{
    if (!isset($_FILES[$clave])) {
        return array();
    }
    $origen = $_FILES[$clave];
    if (!is_array($origen['name'])) {
        return array($origen);
    }
    $salida = array();
    foreach ($origen['name'] as $indice => $nombre) {
        $salida[] = array(
            'name' => $nombre,
            'type' => isset($origen['type'][$indice]) ? $origen['type'][$indice] : '',
            'tmp_name' => isset($origen['tmp_name'][$indice]) ? $origen['tmp_name'][$indice] : '',
            'error' => isset($origen['error'][$indice]) ? $origen['error'][$indice] : UPLOAD_ERR_NO_FILE,
            'size' => isset($origen['size'][$indice]) ? $origen['size'][$indice] : 0
        );
    }
    return $salida;
}

function centroFacturasUsuarioAutenticado()
{
    $usuario = mb_convert_encoding((string)centroFacturasPost('useru'), 'ISO-8859-1', 'UTF-8');
    $pass = str_replace('=', '+', (string)centroFacturasPost('passu'));
    $navegador = mb_convert_encoding((string)centroFacturasPost('navegador'), 'ISO-8859-1', 'UTF-8');
    if ($usuario === '' || $pass === '' || $navegador === '' || verificar_navegador($usuario, $navegador, $pass) !== 'ok') {
        centroFacturasResponder(array('ok' => false, 'codigo' => 'UI', 'mensaje' => 'La sesion no es valida.'));
    }
    return intval($usuario);
}

function centroFacturasFiltrosPost()
{
    $filtros = centroFacturasJsonPost('filtros', array());
    $claves = array(
        'busqueda','cod_local','estado_pago','estado_original','estado_validacion','tipo_contraparte',
        'fecha_desde','fecha_hasta','fecha_limite_desde','fecha_limite_hasta','filtro_rapido',
        'incluir_anuladas','estado','cod_proveedor','cod_funcionario'
    );
    foreach ($claves as $clave) {
        if (isset($_POST[$clave])) {
            $filtros[$clave] = $_POST[$clave];
        }
    }
    return $filtros;
}

$codUsuario = centroFacturasUsuarioAutenticado();
$accion = trim((string)centroFacturasPost('funt', centroFacturasPost('accion', '')));

if ($accion === '') {
    centroFacturasResponder(array('ok' => false, 'codigo' => 'accion', 'mensaje' => 'No se indico la operacion.'));
}
if (!centroFacturaEstructuraDisponible()) {
    centroFacturasResponder(array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura del Centro de Facturas no esta instalada.'));
}

switch ($accion) {
    case 'contexto':
        centroFacturasResponder(centroFacturaCatalogos($codUsuario));
        break;
    case 'metricas':
        if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
            centroFacturasResponder(array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar las metricas.'));
        }
        centroFacturasResponder(array('ok' => true, 'metricas' => centroFacturaMetricas($codUsuario)));
        break;
    case 'listarEntrantes':
        centroFacturasResponder(centroFacturaListarEntrantes(
            $codUsuario,
            centroFacturasFiltrosPost(),
            intval(centroFacturasPost('limite', 80)),
            intval(centroFacturasPost('offset', 0))
        ));
        break;
    case 'listarEmitidas':
        centroFacturasResponder(centroFacturaListarEmitidas(
            $codUsuario,
            centroFacturasFiltrosPost(),
            intval(centroFacturasPost('limite', 80)),
            intval(centroFacturasPost('offset', 0))
        ));
        break;
    case 'detalle':
        centroFacturasResponder(centroFacturaObtenerDetalle(intval(centroFacturasPost('id_factura')), $codUsuario));
        break;
    case 'registrarDesdeMensaje':
        centroFacturasResponder(centroFacturaRegistrarDesdeMensaje(intval(centroFacturasPost('cod_mensaje')), $codUsuario));
        break;
    case 'registrarManual':
        centroFacturasResponder(centroFacturaRegistrarManual(
            centroFacturasJsonPost('datos', array()),
            centroFacturasArchivos('archivos'),
            $codUsuario
        ));
        break;
    case 'guardarDatos':
        centroFacturasResponder(centroFacturaGuardarDatos(
            intval(centroFacturasPost('id_factura')),
            centroFacturasJsonPost('datos', array()),
            $codUsuario
        ));
        break;
    case 'agregarArchivos':
        centroFacturasResponder(centroFacturaAgregarArchivos(
            intval(centroFacturasPost('id_factura')),
            centroFacturasArchivos('archivos'),
            $codUsuario,
            centroFacturasPost('tipo_origen', 'carga_manual')
        ));
        break;
    case 'vincularMovimiento':
        centroFacturasResponder(centroFacturaVincularMovimiento(
            intval(centroFacturasPost('id_factura')),
            centroFacturasPost('tipo_movimiento'),
            intval(centroFacturasPost('id_movimiento')),
            centroFacturasPost('motivo'),
            $codUsuario
        ));
        break;
    case 'desvincularMovimiento':
        centroFacturasResponder(centroFacturaDesvincularMovimiento(
            intval(centroFacturasPost('id_factura')),
            centroFacturasPost('motivo'),
            $codUsuario
        ));
        break;
    case 'cambiarOriginal':
        centroFacturasResponder(centroFacturaCambiarOriginal(
            intval(centroFacturasPost('id_factura')),
            centroFacturasPost('accion_original'),
            centroFacturasJsonPost('datos', array()),
            $codUsuario
        ));
        break;
    case 'cambiarValidacion':
        centroFacturasResponder(centroFacturaCambiarValidacion(
            intval(centroFacturasPost('id_factura')),
            centroFacturasPost('estado_validacion'),
            centroFacturasPost('motivo'),
            $codUsuario
        ));
        break;
    case 'actualizarConfiguracion':
        centroFacturasResponder(centroFacturaActualizarConfiguracion(
            intval(centroFacturasPost('dias_plazo_original')),
            intval(centroFacturasPost('ocr_habilitado')),
            centroFacturasPost('ocr_proveedor'),
            $codUsuario
        ));
        break;
    case 'listarLotes':
        centroFacturasResponder(centroFacturaListarLotes(
            $codUsuario,
            centroFacturasFiltrosPost(),
            intval(centroFacturasPost('limite', 80)),
            intval(centroFacturasPost('offset', 0))
        ));
        break;
    case 'crearLote':
        centroFacturasResponder(centroFacturaCrearLote(
            intval(centroFacturasPost('cod_local')),
            centroFacturasJsonPost('facturas', array()),
            centroFacturasJsonPost('datos', array()),
            $codUsuario
        ));
        break;
    case 'detalleLote':
        centroFacturasResponder(centroFacturaObtenerDetalleLote(intval(centroFacturasPost('id_lote')), $codUsuario));
        break;
    case 'agregarFacturaLote':
        centroFacturasResponder(centroFacturaAgregarFacturaLote(
            intval(centroFacturasPost('id_lote')),
            intval(centroFacturasPost('id_factura')),
            $codUsuario
        ));
        break;
    case 'retirarFacturaLote':
        centroFacturasResponder(centroFacturaRetirarFacturaLote(
            intval(centroFacturasPost('id_lote')),
            intval(centroFacturasPost('id_factura')),
            centroFacturasPost('motivo'),
            $codUsuario
        ));
        break;
    case 'enviarLote':
        centroFacturasResponder(centroFacturaEnviarLote(
            intval(centroFacturasPost('id_lote')),
            intval(centroFacturasPost('cod_responsable')),
            $codUsuario
        ));
        break;
    case 'recibirLote':
        centroFacturasResponder(centroFacturaRecibirLote(
            intval(centroFacturasPost('id_lote')),
            centroFacturasJsonPost('recepciones', array()),
            centroFacturasJsonPost('datos', array()),
            $codUsuario
        ));
        break;
    case 'anularLote':
        centroFacturasResponder(centroFacturaAnularLote(
            intval(centroFacturasPost('id_lote')),
            centroFacturasPost('motivo'),
            $codUsuario
        ));
        break;
    default:
        centroFacturasResponder(array('ok' => false, 'codigo' => 'accion', 'mensaje' => 'La operacion solicitada no existe.'));
}

<?php

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Asuncion');

require('conexion.php');
include('verificar_navegador.php');
include('buscar_nivel.php');

define('HPS_PERMISO', 'VERHISTORIALPAGOSSALTEADOS');

function hps_json($datos)
{
    while (ob_get_level() > 0) { ob_end_clean(); }
    if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function hps_param($nombre, $defecto = '')
{
    if (isset($_POST[$nombre])) { return $_POST[$nombre]; }
    if (isset($_GET[$nombre])) { return $_GET[$nombre]; }
    return $defecto;
}

function hps_db($valor)
{
    return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function hps_utf8($valor)
{
    $valor = (string)$valor;
    return $valor === '' || mb_check_encoding($valor, 'UTF-8')
        ? $valor : mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
}

function hps_query($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if ($resultado === false) {
        error_log('Historial pagos salteados: '.$mysqli->error);
        hps_json(array('1' => 'error', '2' => 'No se pudo consultar el historial.'));
    }
    return $resultado;
}

$usuario = (int)hps_param('useru', hps_param('user'));
$pass = str_replace('=', '+', (string)hps_param('passu', hps_param('pass')));
$navegador = hps_db(hps_param('navegador'));
if ($usuario <= 0 || $pass === '' || $navegador === '' || verificar_navegador((string)$usuario, $navegador, $pass) !== 'ok') {
    hps_json(array('1' => 'UI', '2' => 'Sesion invalida'));
}
if (controldeaccesoacasas($usuario, HPS_PERMISO, " u.accion='SI' ") != 1) {
    hps_json(array('1' => 'sinpermiso', '2' => 'No tiene permiso para consultar el historial.'));
}

$mysqli = conectar_al_servidor();
if ($mysqli->connect_errno) {
    hps_json(array('1' => 'error', '2' => 'No se pudo conectar a la base de datos.'));
}
$existe = hps_query($mysqli, "SELECT COUNT(*) total FROM information_schema.TABLES
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN
      ('historial_pago_salteado','historial_pago_salteado_cuota','historial_pago_salteado_venta')");
if ((int)$existe->fetch_assoc()['total'] !== 3) {
    hps_json(array('1' => 'error', '2' => 'Falta ejecutar la actualizacion del historial de pagos salteados.'));
}

$operacion = strtolower(trim((string)hps_param('funt', 'listar')));
if ($operacion === 'detalle') {
    $idHistorial = (int)hps_param('id', 0);
    if ($idHistorial <= 0) {
        hps_json(array('1' => 'camposvacio', '2' => 'Registro historico invalido.'));
    }
    $filtroLocalDetalle = '';
    if ((string)$usuario !== '2' && controldeaccesoacasas($usuario, 'CAMBIARLOCAL', " u.accion='SI' ") != 1) {
        $resLocalDetalle = hps_query($mysqli, 'SELECT cod_localFK FROM usuario WHERE cod_usuario='.$usuario.' LIMIT 1');
        $filaLocalDetalle = $resLocalDetalle->fetch_assoc();
        $localDetalle = $filaLocalDetalle ? (int)$filaLocalDetalle['cod_localFK'] : 0;
        $filtroLocalDetalle = ' AND cod_local='.$localDetalle;
    }
    $cabeceraRes = hps_query($mysqli, "SELECT id_historial_venta,cod_venta,cliente_snapshot,
        fecha_deteccion,ultima_cuota_pagada,id_historial_detalleFK,cantidad_entregas,monto_entrega,pagado_entrega
        FROM historial_pago_salteado_venta
        WHERE id_historial_venta=$idHistorial $filtroLocalDetalle LIMIT 1");
    $cabecera = $cabeceraRes->fetch_assoc();
    if (!$cabecera) {
        hps_json(array('1' => 'error', '2' => 'El registro no existe o pertenece a otro local.'));
    }
    $idDetalle = (int)$cabecera['id_historial_detalleFK'];
    $entregaRes = hps_query($mysqli, "SELECT * FROM historial_pago_salteado_entrega
        WHERE id_historial_ventaFK=$idHistorial ORDER BY id_entrega");
    $detalleRes = hps_query($mysqli, "SELECT * FROM historial_pago_salteado_cuota
        WHERE id_historialFK=$idDetalle ORDER BY nro_cuota,cod_credito");
    $cuotas = array();
    while ($fila = $entregaRes->fetch_assoc()) {
        $cuotas[] = array('credito' => (int)$fila['cod_credito'], 'numero' => 0,
            'es_entrega' => 1, 'plazo' => hps_utf8($fila['plazo']), 'vencimiento' => $fila['vencimiento'],
            'capital_debido' => (int)$fila['capital_debido'], 'capital_pagado' => (int)$fila['capital_pagado'],
            'interes_debido' => 0, 'interes_pagado' => 0, 'saldo' => (int)$fila['saldo'],
            'pagada' => (int)$fila['pagada'], 'tiene_pago' => (int)$fila['tiene_pago'],
            'salteada' => 0, 'estado' => hps_utf8($fila['estado_snapshot']));
    }
    if (count($cuotas) === 0 && (int)$cabecera['cantidad_entregas'] > 0) {
        $montoEntrega = (int)$cabecera['monto_entrega'];
        $pagadoEntrega = (int)$cabecera['pagado_entrega'];
        $saldoEntrega = max(0, $montoEntrega - $pagadoEntrega);
        $estadoEntrega = $montoEntrega <= 0 ? 'Sin monto' : ($saldoEntrega === 0 ? 'Pagada' : ($pagadoEntrega > 0 ? 'Pago parcial' : 'Pendiente'));
        $cuotas[] = array('credito' => 0, 'numero' => 0, 'es_entrega' => 1,
            'plazo' => (int)$cabecera['cantidad_entregas'] > 1 ? 'ENTREGAS ('.(int)$cabecera['cantidad_entregas'].')' : 'ENTREGA',
            'vencimiento' => null, 'capital_debido' => $montoEntrega, 'capital_pagado' => $pagadoEntrega,
            'interes_debido' => 0, 'interes_pagado' => 0, 'saldo' => $saldoEntrega,
            'pagada' => $saldoEntrega === 0 && $montoEntrega > 0 ? 1 : 0, 'tiene_pago' => $pagadoEntrega > 0 ? 1 : 0,
            'salteada' => 0, 'estado' => $estadoEntrega);
    }
    while ($fila = $detalleRes->fetch_assoc()) {
        $cuotas[] = array(
            'credito' => (int)$fila['cod_credito'], 'numero' => (int)$fila['nro_cuota'],
            'es_entrega' => 0,
            'plazo' => $fila['plazo'], 'vencimiento' => $fila['vencimiento'],
            'capital_debido' => (int)$fila['capital_debido'], 'capital_pagado' => (int)$fila['capital_pagado'],
            'interes_debido' => (int)$fila['interes_debido'], 'interes_pagado' => (int)$fila['interes_pagado'],
            'saldo' => (int)$fila['saldo'], 'pagada' => (int)$fila['pagada'],
            'tiene_pago' => (int)$fila['tiene_pago'], 'salteada' => (int)$fila['salteada'],
            'estado' => hps_utf8($fila['estado_snapshot'])
        );
    }
    mysqli_close($mysqli);
    hps_json(array('1' => 'exito', 'id' => (int)$cabecera['id_historial_venta'],
        'venta' => (int)$cabecera['cod_venta'], 'cliente' => hps_utf8($cabecera['cliente_snapshot']),
        'fecha_deteccion' => $cabecera['fecha_deteccion'],
        'ultima_pagada' => (int)$cabecera['ultima_cuota_pagada'], 'cuotas' => $cuotas));
}

$filtros = array('1=1');
$buscar = trim((string)hps_param('buscar'));
if ($buscar !== '') {
    $texto = $mysqli->real_escape_string(hps_db($buscar));
    $doc = $mysqli->real_escape_string(preg_replace('/[^0-9A-Za-z]/', '', $buscar));
    $partes = array("h.cliente_snapshot LIKE '%$texto%'", "CAST(h.cod_cliente AS CHAR)='$texto'");
    if ($doc !== '') {
        $partes[] = "REPLACE(REPLACE(REPLACE(IFNULL(h.documento_snapshot,''),'.',''),'-',''),' ','') LIKE '%$doc%'";
    }
    $filtros[] = '('.implode(' OR ', $partes).')';
}
$venta = (int)hps_param('venta', 0);
if ($venta > 0) { $filtros[] = 'h.cod_venta='.$venta; }
$local = (int)hps_param('local', 0);
$puedeCambiarLocal = ((string)$usuario === '2' || controldeaccesoacasas($usuario, 'CAMBIARLOCAL', " u.accion='SI' ") == 1);
if (!$puedeCambiarLocal) {
    $resLocal = hps_query($mysqli, 'SELECT cod_localFK FROM usuario WHERE cod_usuario='.$usuario.' LIMIT 1');
    $filaLocal = $resLocal->fetch_assoc();
    $local = $filaLocal ? (int)$filaLocal['cod_localFK'] : 0;
}
if ($local > 0) { $filtros[] = 'h.cod_local='.$local; }
$desde = trim((string)hps_param('desde'));
$hasta = trim((string)hps_param('hasta'));
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
    $filtros[] = "h.fecha_deteccion>='".$mysqli->real_escape_string($desde)." 00:00:00'";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
    $filtros[] = "h.fecha_deteccion<='".$mysqli->real_escape_string($hasta)." 23:59:59'";
}
$where = implode(' AND ', $filtros);
$pagina = max(1, (int)hps_param('pagina', 1));
$limite = (int)hps_param('limite', 25);
if ($limite < 10) { $limite = 10; }
if ($limite > 5000) { $limite = 5000; }
$offset = ($pagina - 1) * $limite;

$totalRes = hps_query($mysqli, "SELECT COUNT(*) total FROM historial_pago_salteado_venta h WHERE $where");
$total = (int)$totalRes->fetch_assoc()['total'];
$resumenRes = hps_query($mysqli, "SELECT COUNT(DISTINCT h.cod_cliente) clientes,
    COUNT(*) ventas,SUM(h.cuotas_salteadas) cuotas,SUM(h.saldo_huecos) saldo,
    SUM(h.primer_vencimiento<CURDATE()) vencidas,SUM(h.primer_vencimiento=CURDATE()) hoy,
    SUM(h.primer_vencimiento>CURDATE()) futuras,SUM(h.cuotas_parciales) parciales
    FROM historial_pago_salteado_venta h WHERE $where");
$resumen = $resumenRes->fetch_assoc();

$resultado = hps_query($mysqli, "SELECT h.* FROM historial_pago_salteado_venta h
    WHERE $where
    ORDER BY h.primer_vencimiento ASC,h.saldo_huecos DESC,h.cod_venta ASC,h.id_historial_venta ASC
    LIMIT $offset,$limite");
$filas = array();
while ($fila = $resultado->fetch_assoc()) {
    $filas[] = array(
        'id' => (int)$fila['id_historial_venta'], 'fecha_deteccion' => $fila['fecha_deteccion'],
        'usuario' => (int)$fila['usuario_deteccion'], 'cliente_id' => (int)$fila['cod_cliente'],
        'cliente' => hps_utf8($fila['cliente_snapshot']), 'documento' => hps_utf8($fila['documento_snapshot']),
        'telefono' => hps_utf8($fila['telefono_snapshot']),
        'venta' => (int)$fila['cod_venta'], 'factura' => hps_utf8($fila['factura_snapshot']),
        'local_id' => (int)$fila['cod_local'], 'local' => hps_utf8($fila['local_snapshot']),
        'cuotas_pagadas' => $fila['cuotas_pagadas'], 'ultima_pagada' => (int)$fila['ultima_cuota_pagada'],
        'cuotas_pendientes' => $fila['cuotas_pendientes'],
        'cuotas_salteadas' => (int)$fila['cuotas_salteadas'],
        'cuotas_parciales' => (int)$fila['cuotas_parciales'],
        'saldo' => (int)$fila['saldo_huecos'], 'primer_vencimiento' => $fila['primer_vencimiento'],
        'tiene_entrega' => (int)$fila['cantidad_entregas'] > 0 ? 1 : 0,
        'monto_entrega' => (int)$fila['monto_entrega'],
        'pagado_entrega' => (int)$fila['pagado_entrega']
    );
}

$locales = array();
if ($puedeCambiarLocal) {
    $resLocales = hps_query($mysqli, "SELECT DISTINCT cod_local,local_snapshot
        FROM historial_pago_salteado_venta ORDER BY local_snapshot");
    while ($filaLocal = $resLocales->fetch_assoc()) {
        $locales[] = array('id' => (int)$filaLocal['cod_local'], 'nombre' => hps_utf8($filaLocal['local_snapshot']));
    }
}
mysqli_close($mysqli);
hps_json(array('1' => 'exito', 'filas' => $filas, 'total' => $total,
    'pagina' => $pagina, 'limite' => $limite, 'paginas' => max(1, (int)ceil($total / $limite)),
    'resumen' => array_map('intval', $resumen), 'locales' => $locales,
    'puede_cambiar_local' => $puedeCambiarLocal, 'local_aplicado' => $local));

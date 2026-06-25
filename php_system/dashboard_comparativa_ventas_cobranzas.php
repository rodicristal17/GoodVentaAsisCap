<?php

ob_start();
ini_set('display_errors', '0');

require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");

date_default_timezone_set('America/Asuncion');

define('COMPARATIVA_PERMISO', 'VERCOMPARATIVAVENTASCOBRANZAS');

function comparativa_json($datos)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function comparativa_param($nombre, $default = '')
{
    if (isset($_POST[$nombre])) {
        return $_POST[$nombre];
    }
    if (isset($_GET[$nombre])) {
        return $_GET[$nombre];
    }
    return $default;
}

function comparativa_to_iso($valor)
{
    return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function comparativa_to_utf8($valor)
{
    return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function comparativa_autenticar_usuario()
{
    $user = comparativa_to_iso(comparativa_param('useru'));
    $pass = comparativa_param('passu');

    if ($user == '') {
        $user = comparativa_to_iso(comparativa_param('user'));
    }
    if ($pass == '') {
        $pass = comparativa_param('pass');
    }

    $pass = str_replace('=', '+', $pass);
    $navegador = comparativa_to_iso(comparativa_param('navegador'));

    if ($user == '' || $pass == '' || $navegador == '') {
        comparativa_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }

    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != 'ok') {
        comparativa_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }

    return $user;
}

function comparativa_usuario_tiene_permiso($user)
{
    if ((string)$user === '2') {
        return true;
    }

    return controldeaccesoacasas($user, COMPARATIVA_PERMISO, " u.accion='SI' ") == 1;
}

function comparativa_normalizar_texto($texto)
{
    return strtolower(trim((string)$texto));
}

function comparativa_es_sucursal_gerencial($nombre)
{
    $normalizado = comparativa_normalizar_texto($nombre);
    $excluir = array('administr', 'cuadril');

    foreach ($excluir as $patron) {
        if (strpos($normalizado, $patron) !== false) {
            return false;
        }
    }

    return true;
}

function comparativa_locales_autorizados($mysqli, $user)
{
    $localUsuario = '';
    $puedeCambiarLocal = ((string)$user === '2') || controldeaccesoacasas($user, "CAMBIARLOCAL", " u.accion='SI' ") == 1;

    if (!$puedeCambiarLocal) {
        $localUsuario = buscarlocaluser($user);
    }

    $sql = "SELECT cod_local, Nombre
            FROM local
            WHERE estado='Activo'
            ORDER BY cod_local ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudieron consultar las sucursales'));
    }

    $result = $stmt->get_result();
    $locales = array();

    while ($row = $result->fetch_assoc()) {
        $codLocal = (int)$row['cod_local'];
        $nombre = comparativa_to_utf8($row['Nombre']);

        if ($localUsuario != '' && (string)$codLocal !== (string)$localUsuario) {
            continue;
        }
        if (!comparativa_es_sucursal_gerencial($nombre)) {
            continue;
        }

        $locales[] = array(
            'sucursalId' => $codLocal,
            'sucursalNombre' => $nombre
        );
    }

    return $locales;
}

function comparativa_periodos()
{
    $zona = new DateTimeZone('America/Asuncion');
    $ahora = new DateTime('now', $zona);

    $actualDesde = clone $ahora;
    $actualDesde->modify('first day of this month');
    $actualDesde->setTime(0, 0, 0);

    $actualHasta = clone $ahora;

    $anteriorDesde = clone $actualDesde;
    $anteriorDesde->modify('-1 month');

    $diaActual = (int)$ahora->format('j');
    $ultimoDiaAnterior = (int)$anteriorDesde->format('t');
    $diaAnterior = min($diaActual, $ultimoDiaAnterior);

    $anteriorHasta = clone $anteriorDesde;
    $anteriorHasta->setDate((int)$anteriorDesde->format('Y'), (int)$anteriorDesde->format('m'), $diaAnterior);
    $anteriorHasta->setTime((int)$ahora->format('H'), (int)$ahora->format('i'), (int)$ahora->format('s'));

    return array(
        'actualDesde' => $actualDesde,
        'actualHasta' => $actualHasta,
        'anteriorDesde' => $anteriorDesde,
        'anteriorHasta' => $anteriorHasta
    );
}

function comparativa_monto_entero($valor)
{
    if ($valor === null || $valor === '') {
        return 0;
    }
    $valor = preg_replace('/[^0-9\-]/', '', (string)$valor);
    if ($valor === '' || $valor === '-') {
        return 0;
    }
    return (int)$valor;
}

function comparativa_sumar_ventas($mysqli, $localesIds, $desde, $hasta)
{
    $totales = array();
    if (count($localesIds) == 0) {
        return $totales;
    }

    $ids = implode(',', array_map('intval', $localesIds));
    $sql = "SELECT vt.cod_local,
                   IFNULL(SUM(CAST(ROUND(IFNULL(vt.total_venta,0) - IFNULL(vt.descuento,0)) AS SIGNED)),0) AS total
            FROM venta vt
            WHERE vt.cod_venta!='0'
              AND vt.fecha_venta>=?
              AND vt.fecha_venta<=?
              AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones ca WHERE ca.cod_venta=vt.cod_venta LIMIT 1),0)=0
              AND IFNULL(vt.anulado,'')=''
              AND IFNULL(vt.estadocuenta,'Activo')!='Anulado'
              AND vt.cod_local IN ($ids)
            GROUP BY vt.cod_local";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudo preparar la consulta de ventas'));
    }

    $s = 'ss';
    $stmt->bind_param($s, $desde, $hasta);

    if (!$stmt->execute()) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudieron calcular las ventas'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $totales[(int)$row['cod_local']] = comparativa_monto_entero($row['total']);
    }

    return $totales;
}

function comparativa_contar_ventas_credito($mysqli, $localesIds, $desde, $hasta)
{
    $totales = array();
    if (count($localesIds) == 0) {
        return $totales;
    }

    $ids = implode(',', array_map('intval', $localesIds));
    $sql = "SELECT vt.cod_local,
                   COUNT(vt.cod_venta) AS total
            FROM venta vt
            WHERE vt.cod_venta!='0'
              AND vt.fecha_venta>=?
              AND vt.fecha_venta<=?
              AND UPPER(IFNULL(vt.TipoVenta,''))='CREDITO'
              AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones ca WHERE ca.cod_venta=vt.cod_venta LIMIT 1),0)=0
              AND IFNULL(vt.anulado,'')=''
              AND IFNULL(vt.estadocuenta,'Activo')!='Anulado'
              AND vt.cod_local IN ($ids)
            GROUP BY vt.cod_local";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudo preparar la consulta de ventas a credito'));
    }

    $s = 'ss';
    $stmt->bind_param($s, $desde, $hasta);

    if (!$stmt->execute()) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudieron calcular las ventas a credito'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $totales[(int)$row['cod_local']] = (int)$row['total'];
    }

    return $totales;
}

function comparativa_sumar_cobranzas($mysqli, $localesIds, $desde, $hasta)
{
    $totales = array();
    if (count($localesIds) == 0) {
        return $totales;
    }

    $ids = implode(',', array_map('intval', $localesIds));
    $sql = "SELECT vt.cod_local,
                   IFNULL(SUM(CAST(IFNULL(pg.Monto,0) AS SIGNED)),0) AS total
            FROM pago pg
            INNER JOIN venta vt ON vt.cod_venta=pg.cod_venta_fk
            WHERE pg.Monto>0
              AND pg.Fecha>=?
              AND pg.Fecha<=?
              AND IFNULL(pg.anulado,'')=''
              AND vt.cod_local IN ($ids)
            GROUP BY vt.cod_local";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudo preparar la consulta de cobranzas'));
    }

    $s = 'ss';
    $stmt->bind_param($s, $desde, $hasta);

    if (!$stmt->execute()) {
        comparativa_json(array('1' => 'error', '2' => 'No se pudo calcular la cobranza'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $totales[(int)$row['cod_local']] = comparativa_monto_entero($row['total']);
    }

    return $totales;
}

function comparativa_formatear_variacion($actual, $anterior)
{
    $actual = (int)$actual;
    $anterior = (int)$anterior;

    if ($anterior == 0 && $actual == 0) {
        return array('texto' => '0,0%', 'valorDecimal' => '0.0', 'valorTenths' => 0, 'tipo' => 'neutral', 'indicador' => 'neutral');
    }

    if ($anterior == 0 && $actual > 0) {
        return array('texto' => 'Nuevo', 'valorDecimal' => null, 'valorTenths' => null, 'tipo' => 'positivo', 'indicador' => 'up');
    }

    $diferencia = $actual - $anterior;
    $signo = $diferencia < 0 ? -1 : 1;
    $numerador = abs($diferencia) * 1000;
    $tenths = intdiv($numerador + intdiv($anterior, 2), $anterior) * $signo;
    $abs = abs($tenths);
    $texto = ($tenths > 0 ? '+' : ($tenths < 0 ? '-' : '')) . intdiv($abs, 10) . ',' . ($abs % 10) . '%';
    $tipo = $tenths > 0 ? 'positivo' : ($tenths < 0 ? 'negativo' : 'neutral');
    $indicador = $tenths > 0 ? 'up' : ($tenths < 0 ? 'down' : 'neutral');
    $valorDecimal = ($tenths / 10);

    return array(
        'texto' => $texto,
        'valorDecimal' => number_format($valorDecimal, 1, '.', ''),
        'valorTenths' => $tenths,
        'tipo' => $tipo,
        'indicador' => $indicador
    );
}

function comparativa_responder($user)
{
    if (!comparativa_usuario_tiene_permiso($user)) {
        comparativa_json(array('1' => 'NI', '2' => 'Sin permiso'));
    }

    $mysqli = conectar_al_servidor();
    $locales = comparativa_locales_autorizados($mysqli, $user);
    $ids = array();

    foreach ($locales as $local) {
        $ids[] = (int)$local['sucursalId'];
    }

    $periodos = comparativa_periodos();
    $actualDesde = $periodos['actualDesde']->format('Y-m-d');
    $actualHasta = $periodos['actualHasta']->format('Y-m-d');
    $anteriorDesde = $periodos['anteriorDesde']->format('Y-m-d');
    $anteriorHasta = $periodos['anteriorHasta']->format('Y-m-d');

    $ventasActual = comparativa_sumar_ventas($mysqli, $ids, $actualDesde, $actualHasta);
    $ventasAnterior = comparativa_sumar_ventas($mysqli, $ids, $anteriorDesde, $anteriorHasta);
    $creditosActual = comparativa_contar_ventas_credito($mysqli, $ids, $actualDesde, $actualHasta);
    $creditosAnterior = comparativa_contar_ventas_credito($mysqli, $ids, $anteriorDesde, $anteriorHasta);
    $cobranzaActual = comparativa_sumar_cobranzas($mysqli, $ids, $actualDesde, $actualHasta);
    $cobranzaAnterior = comparativa_sumar_cobranzas($mysqli, $ids, $anteriorDesde, $anteriorHasta);

    $sucursales = array();
    $totalVentasActual = 0;
    $totalVentasAnterior = 0;
    $totalCreditosActual = 0;
    $totalCreditosAnterior = 0;
    $totalCobranzaActual = 0;
    $totalCobranzaAnterior = 0;

    foreach ($locales as $local) {
        $id = (int)$local['sucursalId'];
        $va = isset($ventasActual[$id]) ? (int)$ventasActual[$id] : 0;
        $vp = isset($ventasAnterior[$id]) ? (int)$ventasAnterior[$id] : 0;
        $cra = isset($creditosActual[$id]) ? (int)$creditosActual[$id] : 0;
        $crp = isset($creditosAnterior[$id]) ? (int)$creditosAnterior[$id] : 0;
        $ca = isset($cobranzaActual[$id]) ? (int)$cobranzaActual[$id] : 0;
        $cp = isset($cobranzaAnterior[$id]) ? (int)$cobranzaAnterior[$id] : 0;

        $totalVentasActual += $va;
        $totalVentasAnterior += $vp;
        $totalCreditosActual += $cra;
        $totalCreditosAnterior += $crp;
        $totalCobranzaActual += $ca;
        $totalCobranzaAnterior += $cp;

        $sucursales[] = array(
            'sucursalId' => $id,
            'sucursalNombre' => $local['sucursalNombre'],
            'ventasActual' => $va,
            'ventasAnterior' => $vp,
            'variacionVentas' => comparativa_formatear_variacion($va, $vp),
            'creditosActual' => $cra,
            'creditosAnterior' => $crp,
            'variacionCreditos' => comparativa_formatear_variacion($cra, $crp),
            'cobranzaActual' => $ca,
            'cobranzaAnterior' => $cp,
            'variacionCobranza' => comparativa_formatear_variacion($ca, $cp)
        );
    }

    mysqli_close($mysqli);

    comparativa_json(array(
        '1' => 'exito',
        'periodoActual' => array(
            'desde' => $periodos['actualDesde']->format('Y-m-d H:i:s'),
            'hasta' => $periodos['actualHasta']->format('Y-m-d H:i:s'),
            'desdeFecha' => $actualDesde,
            'hastaFecha' => $actualHasta
        ),
        'periodoAnterior' => array(
            'desde' => $periodos['anteriorDesde']->format('Y-m-d H:i:s'),
            'hasta' => $periodos['anteriorHasta']->format('Y-m-d H:i:s'),
            'desdeFecha' => $anteriorDesde,
            'hastaFecha' => $anteriorHasta
        ),
        'resumenGeneral' => array(
            'ventasActual' => $totalVentasActual,
            'ventasAnterior' => $totalVentasAnterior,
            'variacionVentas' => comparativa_formatear_variacion($totalVentasActual, $totalVentasAnterior),
            'creditosActual' => $totalCreditosActual,
            'creditosAnterior' => $totalCreditosAnterior,
            'variacionCreditos' => comparativa_formatear_variacion($totalCreditosActual, $totalCreditosAnterior),
            'cobranzaActual' => $totalCobranzaActual,
            'cobranzaAnterior' => $totalCobranzaAnterior,
            'variacionCobranza' => comparativa_formatear_variacion($totalCobranzaActual, $totalCobranzaAnterior)
        ),
        'sucursales' => $sucursales,
        'sinMovimientos' => ($totalVentasActual == 0 && $totalVentasAnterior == 0 && $totalCreditosActual == 0 && $totalCreditosAnterior == 0 && $totalCobranzaActual == 0 && $totalCobranzaAnterior == 0)
    ));
}

$user = comparativa_autenticar_usuario();
$funt = comparativa_to_iso(comparativa_param('funt', 'comparativa'));

if ($funt == 'comparativa') {
    comparativa_responder($user);
}

comparativa_json(array('1' => 'error', '2' => 'Operacion no reconocida'));

?>

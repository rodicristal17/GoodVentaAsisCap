<?php

ob_start();
ini_set('display_errors', '0');

require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");

date_default_timezone_set('America/Asuncion');

define('FLUJO_DASHBOARD_PERMISO', 'VERDASHBOARDFLUJOFINANCIERO');
define('FLUJO_DASHBOARD_LOCAL_ADMIN', 1);
define('FLUJO_DASHBOARD_PERIODO_MINIMO', '2026-05');

function flujo_dashboard_tabla_existe($mysqli, $tabla)
{
    $tabla = $mysqli->real_escape_string($tabla);
    $resultado = $mysqli->query("SHOW TABLES LIKE '$tabla'");
    return $resultado && $resultado->num_rows > 0;
}

function flujo_dashboard_json($datos)
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

function flujo_dashboard_param($nombre, $default = '')
{
    if (isset($_POST[$nombre])) {
        return $_POST[$nombre];
    }
    if (isset($_GET[$nombre])) {
        return $_GET[$nombre];
    }
    return $default;
}

function flujo_dashboard_to_iso($valor)
{
    return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function flujo_dashboard_to_utf8($valor)
{
    return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function flujo_dashboard_normalizar_concepto_interno($valor)
{
    $texto = (string)$valor;
    if ($texto != '' && !mb_check_encoding($texto, 'UTF-8')) {
        $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
    }

    $texto = mb_strtoupper(trim($texto), 'UTF-8');
    $texto = str_replace(
        array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'),
        array('A', 'E', 'I', 'O', 'U', 'U'),
        $texto
    );
    return preg_replace('/\s+/u', ' ', $texto);
}

function flujo_dashboard_es_migracion_caja($concepto)
{
    return flujo_dashboard_normalizar_concepto_interno($concepto) == 'MIGRACION DE CAJA';
}

function flujo_dashboard_autenticar_usuario()
{
    $user = flujo_dashboard_to_iso(flujo_dashboard_param('useru'));
    $pass = flujo_dashboard_param('passu');

    if ($user == '') {
        $user = flujo_dashboard_to_iso(flujo_dashboard_param('user'));
    }
    if ($pass == '') {
        $pass = flujo_dashboard_param('pass');
    }

    $pass = str_replace('=', '+', $pass);
    $navegador = flujo_dashboard_to_iso(flujo_dashboard_param('navegador'));

    if ($user == '' || $pass == '' || $navegador == '') {
        flujo_dashboard_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }

    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != 'ok') {
        flujo_dashboard_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }

    return $user;
}

function flujo_dashboard_usuario_tiene_permiso($user)
{
    return controldeaccesoacasas($user, FLUJO_DASHBOARD_PERMISO, " u.accion='SI' ") == 1;
}

function flujo_dashboard_usuario_puede_cambiar_local($user)
{
    if ((string)$user === '2') {
        return true;
    }

    return controldeaccesoacasas($user, 'CAMBIARLOCAL', " u.accion='SI' ") == 1;
}

function flujo_dashboard_locales_fijos()
{
    return array(
        3 => 'CLINIDENT CERRO CORA (VILLARRICA)',
        5 => 'CLINIDENT VILLA INDUSTRIAL (SAN LORENZO)',
        6 => 'CLINIDENT PADRE MOLAS (OVIEDO)',
        7 => 'CLINIDENT SANTA LIBRADA (VILLARRICA)',
        9 => 'CLINIDENT VILLA MORRA'
    );
}

function flujo_dashboard_mes_nombre($mes)
{
    $nombres = array(
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    );

    return isset($nombres[$mes]) ? $nombres[$mes] : '';
}

function flujo_dashboard_periodo($mesSolicitado = '')
{
    $zona = new DateTimeZone('America/Asuncion');
    $ahora = new DateTime('now', $zona);
    $primerDiaMesActual = new DateTime($ahora->format('Y-m-01'), $zona);
    $mesActual = $primerDiaMesActual->format('Y-m');

    $mesPorDefecto = clone $primerDiaMesActual;
    $mesPorDefecto->modify('-1 month');
    $mesSolicitado = trim((string)$mesSolicitado);
    if ($mesSolicitado == '') {
        $mesSolicitado = $mesPorDefecto->format('Y-m');
    }

    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mesSolicitado)) {
        return null;
    }
    if (strcmp($mesSolicitado, FLUJO_DASHBOARD_PERIODO_MINIMO) < 0 || strcmp($mesSolicitado, $mesActual) > 0) {
        return null;
    }

    $desde = DateTime::createFromFormat('!Y-m-d', $mesSolicitado . '-01', $zona);
    if (!$desde || $desde->format('Y-m') != $mesSolicitado) {
        return null;
    }
    $desde->setTime(0, 0, 0);

    $esActual = $mesSolicitado == $mesActual;
    if ($esActual) {
        $hasta = clone $ahora;
    } else {
        $hasta = clone $desde;
        $hasta->modify('last day of this month');
    }
    $hasta->setTime(23, 59, 59);

    return array(
        'desde' => $desde,
        'hasta' => $hasta,
        'desdeFecha' => $desde->format('Y-m-d'),
        'hastaFecha' => $hasta->format('Y-m-d'),
        'mes' => $desde->format('Y-m'),
        'etiqueta' => flujo_dashboard_mes_nombre((int)$desde->format('n')) . ' ' . $desde->format('Y'),
        'esActual' => $esActual,
        'minimo' => FLUJO_DASHBOARD_PERIODO_MINIMO,
        'maximo' => $mesActual
    );
}

function flujo_dashboard_periodo_cerrado()
{
    return flujo_dashboard_periodo('');
}

function flujo_dashboard_periodo_solicitado()
{
    $periodo = flujo_dashboard_periodo(flujo_dashboard_param('periodo', ''));
    if ($periodo === null) {
        flujo_dashboard_json(array(
            '1' => 'error',
            '2' => 'Periodo no valido. Selecciona un mes entre Mayo 2026 y el mes actual.'
        ));
    }
    return $periodo;
}

function flujo_dashboard_periodo_respuesta($periodo)
{
    return array(
        'desde' => $periodo['desde']->format('Y-m-d H:i:s'),
        'hasta' => $periodo['hasta']->format('Y-m-d H:i:s'),
        'desdeFecha' => $periodo['desdeFecha'],
        'hastaFecha' => $periodo['hastaFecha'],
        'mes' => $periodo['mes'],
        'etiqueta' => $periodo['etiqueta'],
        'esActual' => $periodo['esActual']
    );
}

function flujo_dashboard_limites_periodo_respuesta($periodo)
{
    return array(
        'minimo' => $periodo['minimo'],
        'maximo' => $periodo['maximo']
    );
}

function flujo_dashboard_lista_ids($ids)
{
    $limpios = array();
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $limpios[] = $id;
        }
    }
    return implode(',', $limpios);
}

function flujo_dashboard_ids_autorizados($user)
{
    $locales = flujo_dashboard_locales_fijos();
    $ids = array_keys($locales);

    if (!flujo_dashboard_usuario_puede_cambiar_local($user)) {
        $localUsuario = (int)buscarlocaluser($user);
        if ($localUsuario <= 0 || !isset($locales[$localUsuario])) {
            return array();
        }
        return array($localUsuario);
    }

    return $ids;
}

function flujo_dashboard_nombres_locales($mysqli)
{
    $locales = flujo_dashboard_locales_fijos();
    $idsSql = flujo_dashboard_lista_ids(array_keys($locales));
    if ($idsSql == '') {
        return $locales;
    }

    $sql = "SELECT cod_local, Nombre FROM local WHERE cod_local IN ($idsSql)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        return $locales;
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $codigo = (int)$row['cod_local'];
        $locales[$codigo] = flujo_dashboard_to_utf8($row['Nombre']);
    }

    return $locales;
}

function flujo_dashboard_sumar_pagos($mysqli, $ids, $desde, $hasta)
{
    $totales = array();
    $idsSql = flujo_dashboard_lista_ids($ids);
    if ($idsSql == '') {
        return $totales;
    }

    $desdeSql = $mysqli->real_escape_string($desde);
    $hastaSql = $mysqli->real_escape_string($hasta);
    $sql = "SELECT vt.cod_local, IFNULL(SUM(pg.Monto),0) AS total
            FROM pago pg
            INNER JOIN venta vt ON vt.cod_venta=pg.cod_venta_fk
            WHERE pg.Monto>0
            AND pg.Fecha>='$desdeSql'
            AND pg.Fecha<='$hastaSql'
            AND vt.cod_local IN ($idsSql)
            GROUP BY vt.cod_local";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudieron consultar los ingresos de caja'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $totales[(int)$row['cod_local']] = (int)round($row['total']);
    }

    return $totales;
}

function flujo_dashboard_categoria($categoria, $tipo = '')
{
	if (strtolower(trim((string)$tipo)) == 'deposito') {
		return 'deposito';
	}
    $categoria = strtolower(trim((string)$categoria));
    if ($categoria == 'ingreso' || $categoria == 'directo' || $categoria == 'operativo' || $categoria == 'deposito' || $categoria == 'administracion') {
        return $categoria;
    }
    return 'sinCategoria';
}

function flujo_dashboard_sumar_movimientos($mysqli, $ids, $desde, $hasta)
{
    $totales = array();
    $idsSql = flujo_dashboard_lista_ids($ids);
    if ($idsSql == '') {
        return $totales;
    }

    $desdeSql = $mysqli->real_escape_string($desde);
    $hastaSql = $mysqli->real_escape_string($hasta);
    if (flujo_dashboard_tabla_existe($mysqli, 'gasto_distribucion_local')) {
        $sql = "SELECT mov.cod_local, mov.tipo, mov.concepto, mov.categoria, IFNULL(SUM(mov.monto),0) AS total
            FROM (
                SELECT d.cod_localFK AS cod_local, IFNULL(g.tipo,'') AS tipo,
                    IFNULL(m.descripcion,'') AS concepto,
                    IF(g.cod_local=".(int)FLUJO_DASHBOARD_LOCAL_ADMIN.", 'administracion', IFNULL(m.categoria,'')) AS categoria,
                    d.monto_asignado AS monto
                FROM gasto_distribucion_local d
                INNER JOIN gastos g ON g.idgastos=d.idgastosFK
                LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
                WHERE g.fecha>='$desdeSql' AND g.fecha<='$hastaSql'
                    AND d.cod_localFK IN ($idsSql)
                    AND LOWER(TRIM(IFNULL(g.tipo,'')))='egreso'
                    AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
                UNION ALL
                SELECT g.cod_local, IFNULL(g.tipo,'') AS tipo,
                    IFNULL(m.descripcion,'') AS concepto,
                    IFNULL(m.categoria,'') AS categoria,
                    g.monto
                FROM gastos g
                LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
                WHERE g.fecha>='$desdeSql' AND g.fecha<='$hastaSql'
                    AND g.cod_local IN ($idsSql)
                    AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
                    AND (LOWER(TRIM(IFNULL(g.tipo,'')))!='egreso'
                        OR NOT EXISTS (SELECT 1 FROM gasto_distribucion_local dx WHERE dx.idgastosFK=g.idgastos))
            ) mov
            GROUP BY mov.cod_local, mov.tipo, mov.concepto, mov.categoria";
    } else {
        $sql = "SELECT g.cod_local, IFNULL(g.tipo,'') AS tipo,
                IFNULL(m.descripcion,'') AS concepto,
                IFNULL(m.categoria,'') AS categoria,
                IFNULL(SUM(g.monto),0) AS total
                FROM gastos g
                LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
                WHERE g.fecha>='$desdeSql'
                AND g.fecha<='$hastaSql'
                AND g.cod_local IN ($idsSql)
                AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
                GROUP BY g.cod_local, IFNULL(g.tipo,''), IFNULL(m.descripcion,''), IFNULL(m.categoria,'')";
    }

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudieron consultar los movimientos financieros'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (flujo_dashboard_es_migracion_caja($row['concepto'])) {
            continue;
        }
        $codLocal = (int)$row['cod_local'];
        $categoria = flujo_dashboard_categoria($row['categoria'], $row['tipo']);
        if (!isset($totales[$codLocal])) {
            $totales[$codLocal] = array();
        }
		if (!isset($totales[$codLocal][$categoria])) {
			$totales[$codLocal][$categoria] = 0;
		}
		$totales[$codLocal][$categoria] += (int)round($row['total']);
    }

    return $totales;
}

function flujo_dashboard_total_admin($mysqli, $desde, $hasta)
{
    $desdeSql = $mysqli->real_escape_string($desde);
    $hastaSql = $mysqli->real_escape_string($hasta);
    $codAdmin = (int)FLUJO_DASHBOARD_LOCAL_ADMIN;
    $soloHistoricosSinDistribucion = flujo_dashboard_tabla_existe($mysqli, 'gasto_distribucion_local')
        ? " AND NOT EXISTS (SELECT 1 FROM gasto_distribucion_local d WHERE d.idgastosFK=g.idgastos)"
        : "";

    $sql = "SELECT IFNULL(SUM(g.monto),0) AS total
            FROM gastos g
            LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
            WHERE g.fecha>='$desdeSql'
            AND g.fecha<='$hastaSql'
            AND g.cod_local=$codAdmin
            AND g.tipo='Egreso'
            AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
            AND IFNULL(m.categoria,'')!='ingreso'
            $soloHistoricosSinDistribucion";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudo consultar la administracion compartida'));
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    return $row ? (int)round($row['total']) : 0;
}

function flujo_dashboard_distribuir_admin($total)
{
    $locales = flujo_dashboard_locales_fijos();
    $cantidad = count($locales);
    $distribucion = array();

    if ($cantidad <= 0) {
        return $distribucion;
    }

    $base = intdiv((int)$total, $cantidad);
    $residuo = ((int)$total) % $cantidad;
    $indice = 0;

    foreach ($locales as $codigo => $nombre) {
        $distribucion[$codigo] = $base + ($indice < $residuo ? 1 : 0);
        $indice++;
    }

    return $distribucion;
}

function flujo_dashboard_categorias_base()
{
    return array(
        'ingreso' => array(
            'codigo' => 'ingreso',
            'titulo' => 'Ingresos',
            'color' => '#168a68',
            'total' => 0,
            'conceptos' => array()
        ),
        'directo' => array(
            'codigo' => 'directo',
            'titulo' => 'Costos variables',
            'color' => '#e58a12',
            'total' => 0,
            'conceptos' => array()
        ),
        'operativo' => array(
            'codigo' => 'operativo',
            'titulo' => 'Gastos fijos',
            'color' => '#e33d3d',
            'total' => 0,
            'conceptos' => array()
        ),
        'administracion' => array(
            'codigo' => 'administracion',
            'titulo' => 'Administracion asignada',
            'color' => '#3b6ea8',
            'total' => 0,
            'conceptos' => array()
        ),
        'deposito' => array(
            'codigo' => 'deposito',
            'titulo' => 'Depositos a central - no afecta el resultado',
            'color' => '#2879a8',
            'total' => 0,
            'conceptos' => array()
        ),
        'sinCategoria' => array(
            'codigo' => 'sinCategoria',
            'titulo' => 'Sin categorizar',
            'color' => '#7b8794',
            'total' => 0,
            'conceptos' => array()
        )
    );
}

function flujo_dashboard_nombre_categoria($categoria, $tipo = '')
{
    $categoria = flujo_dashboard_categoria($categoria, $tipo);
    return $categoria;
}

function flujo_dashboard_agregar_movimiento(&$categorias, $categoriaCodigo, $conceptoCodigo, $conceptoNombre, $movimiento)
{
    $categoriaCodigo = $categoriaCodigo == 'administracion' ? 'administracion' : flujo_dashboard_nombre_categoria($categoriaCodigo);
    if (!isset($categorias[$categoriaCodigo])) {
        $categoriaCodigo = 'sinCategoria';
    }

    $conceptoCodigo = trim((string)$conceptoCodigo);
    if ($conceptoCodigo == '') {
        $conceptoCodigo = 'sin_codigo';
    }
    $conceptoNombre = trim((string)$conceptoNombre);
    if ($conceptoNombre == '') {
        $conceptoNombre = 'Sin concepto';
    }

    if (!isset($categorias[$categoriaCodigo]['conceptos'][$conceptoCodigo])) {
        $categorias[$categoriaCodigo]['conceptos'][$conceptoCodigo] = array(
            'codigo' => $conceptoCodigo,
            'nombre' => $conceptoNombre,
            'total' => 0,
            'movimientos' => array()
        );
    }

    $monto = isset($movimiento['monto']) ? (int)$movimiento['monto'] : 0;
    $categorias[$categoriaCodigo]['total'] += $monto;
    $categorias[$categoriaCodigo]['conceptos'][$conceptoCodigo]['total'] += $monto;
    $categorias[$categoriaCodigo]['conceptos'][$conceptoCodigo]['movimientos'][] = $movimiento;
}

function flujo_dashboard_movimiento_pago($row)
{
    $metodo = trim((string)$row['metodo_pago_nombre']);
    if ($metodo == '') {
        $metodo = trim((string)$row['tipopago']);
    }
    if ($metodo == '') {
        $metodo = 'Sin metodo';
    }

    $cliente = flujo_dashboard_to_utf8($row['nombrecliente']);
    $descripcion = 'Cobro realizado';
    if ($cliente != '') {
        $descripcion .= ' a ' . $cliente;
    }
    $descripcion .= ' en formato ' . flujo_dashboard_to_utf8($metodo);

    return array(
        'id' => 'PAGO-' . (int)$row['idPago'],
        'tipo' => 'Ingreso',
        'fecha' => $row['Fecha'],
        'concepto' => 'Movimiento de caja',
        'descripcion' => $descripcion,
        'monto' => (int)round($row['Monto']),
        'montoOriginal' => (int)round($row['Monto']),
        'montoAsignado' => (int)round($row['Monto']),
        'estado' => 'Activo',
        'responsable' => flujo_dashboard_to_utf8($row['cobradornombre']),
        'localOrigen' => flujo_dashboard_to_utf8($row['nombrelocal']),
        'referencia' => flujo_dashboard_to_utf8($row['nrofactura'])
    );
}

function flujo_dashboard_movimiento_gasto($row, $montoAsignado = null, $localDestino = '')
{
    $montoOriginal = (int)round($row['monto']);
    $monto = $montoAsignado === null ? $montoOriginal : (int)$montoAsignado;
    $concepto = flujo_dashboard_to_utf8($row['concepto']);
    if ($concepto == '') {
        $concepto = 'Sin concepto';
    }
    $descripcion = flujo_dashboard_to_utf8($row['descripcion']);
    if ($descripcion == '') {
        $descripcion = $concepto;
    }

    $movimiento = array(
        'id' => (int)$row['idgastos'],
        'tipo' => flujo_dashboard_to_utf8($row['tipo']),
        'fecha' => $row['fecha'],
        'concepto' => $concepto,
        'descripcion' => $descripcion,
        'monto' => $monto,
        'montoOriginal' => $montoOriginal,
        'montoAsignado' => $monto,
        'estado' => flujo_dashboard_to_utf8($row['estado']),
        'responsable' => flujo_dashboard_to_utf8($row['usuario_nombre']),
        'localOrigen' => flujo_dashboard_to_utf8($row['nombrelocal']),
        'referencia' => flujo_dashboard_to_utf8($row['nroboleta'])
    );

    if ($localDestino != '') {
        $movimiento['localDestino'] = $localDestino;
        $movimiento['descripcion'] = $descripcion . ' | Asignacion contable a ' . $localDestino . ' desde ' . $movimiento['localOrigen'];
    }

    return $movimiento;
}

function flujo_dashboard_detalle_pagos($mysqli, &$categorias, $codLocal, $desde, $hasta)
{
    $codLocal = (int)$codLocal;
    $desdeSql = $mysqli->real_escape_string($desde);
    $hastaSql = $mysqli->real_escape_string($hasta);

    $sql = "SELECT pg.idPago, pg.Fecha, pg.Monto, pg.tipopago, pg.nrofactura,
            vt.cod_local,
            IFNULL(tp.nombre,'') AS metodo_pago_nombre,
            IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=pg.cod_cobradorFK LIMIT 1),'') AS cobradornombre,
            IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=vt.cod_clienteFK LIMIT 1),'') AS nombrecliente,
            IFNULL((SELECT Nombre FROM local l WHERE l.cod_local=vt.cod_local LIMIT 1),'') AS nombrelocal
            FROM pago pg
            INNER JOIN venta vt ON vt.cod_venta=pg.cod_venta_fk
            LEFT JOIN tipopago tp ON tp.cod_tipoPago=pg.cod_tipoPagoFK
            WHERE pg.Monto>0
            AND pg.Fecha>='$desdeSql'
            AND pg.Fecha<='$hastaSql'
            AND vt.cod_local=$codLocal
            ORDER BY pg.Fecha DESC, pg.idPago DESC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudieron consultar los cobros de la sucursal'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        flujo_dashboard_agregar_movimiento(
            $categorias,
            'ingreso',
            'movimiento_caja',
            'Movimiento de caja',
            flujo_dashboard_movimiento_pago($row)
        );
    }
}

function flujo_dashboard_detalle_gastos_local($mysqli, &$categorias, $codLocal, $desde, $hasta)
{
    $codLocal = (int)$codLocal;
    $desdeSql = $mysqli->real_escape_string($desde);
    $hastaSql = $mysqli->real_escape_string($hasta);

    $tieneDistribucion = flujo_dashboard_tabla_existe($mysqli, 'gasto_distribucion_local');
    $campoMonto = $tieneDistribucion ? "IFNULL(d.monto_asignado,g.monto) AS monto_asignado," : "g.monto AS monto_asignado,";
    $joinDistribucion = $tieneDistribucion
        ? " LEFT JOIN gasto_distribucion_local d ON d.idgastosFK=g.idgastos AND d.cod_localFK=$codLocal"
        : "";
    $filtroLocal = $tieneDistribucion
        ? " AND ((LOWER(TRIM(IFNULL(g.tipo,'')))='egreso' AND (d.id_distribucion IS NOT NULL OR (g.cod_local=$codLocal AND NOT EXISTS (SELECT 1 FROM gasto_distribucion_local dx WHERE dx.idgastosFK=g.idgastos)))) OR (LOWER(TRIM(IFNULL(g.tipo,'')))!='egreso' AND g.cod_local=$codLocal))"
        : " AND g.cod_local=$codLocal";

    $sql = "SELECT g.idgastos, g.monto, $campoMonto g.motivo AS descripcion, g.fecha, g.estado, g.tipo,
            g.cod_local AS cod_local_origen, g.cod_motivoIngresoEgresoFK, g.nroboleta,
            IFNULL(m.descripcion,'') AS concepto,
            IF(g.cod_local=".(int)FLUJO_DASHBOARD_LOCAL_ADMIN." AND LOWER(TRIM(IFNULL(g.tipo,'')))='egreso'".($tieneDistribucion ? " AND d.id_distribucion IS NOT NULL" : " AND 1=0").", 'administracion', IFNULL(m.categoria,'')) AS categoria,
            IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=g.cod_usuario LIMIT 1),'') AS usuario_nombre,
            IFNULL((SELECT Nombre FROM local l WHERE l.cod_local=g.cod_local LIMIT 1),'') AS nombrelocal,
            IFNULL((SELECT Nombre FROM local ld WHERE ld.cod_local=$codLocal LIMIT 1),'') AS nombrelocal_destino,
            ".($tieneDistribucion ? "IF(d.id_distribucion IS NULL,0,1)" : "0")." AS es_asignacion
            FROM gastos g
            LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
            $joinDistribucion
            WHERE g.fecha>='$desdeSql'
            AND g.fecha<='$hastaSql'
            $filtroLocal
            AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
            ORDER BY FIELD(categoria, 'ingreso', 'directo', 'operativo', 'administracion'), categoria IS NULL, concepto ASC, g.fecha DESC, g.idgastos DESC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudieron consultar los movimientos de la sucursal'));
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (flujo_dashboard_es_migracion_caja($row['concepto'])) {
            continue;
        }
        $categoria = flujo_dashboard_nombre_categoria($row['categoria'], $row['tipo']);
        $conceptoCodigo = trim((string)$row['cod_motivoIngresoEgresoFK']);
        $conceptoNombre = flujo_dashboard_to_utf8($row['concepto']);
		$montoAsignado = !empty($row['es_asignacion']) ? (int)round($row['monto_asignado']) : null;
		$localDestino = !empty($row['es_asignacion']) ? flujo_dashboard_to_utf8($row['nombrelocal_destino']) : '';
        flujo_dashboard_agregar_movimiento(
            $categorias,
            $categoria,
            $conceptoCodigo,
            $conceptoNombre,
            flujo_dashboard_movimiento_gasto($row, $montoAsignado, $localDestino)
        );
    }
}

function flujo_dashboard_distribuir_admin_registros($registros)
{
    $locales = flujo_dashboard_locales_fijos();
    $cantidad = count($locales);
    $distribucion = array();
    foreach ($locales as $codigo => $nombre) {
        $distribucion[$codigo] = 0;
    }
    if ($cantidad <= 0) {
        return $distribucion;
    }
    foreach ($registros as $row) {
        $monto = max(0, (int)round($row['monto']));
        $base = intdiv($monto, $cantidad);
        $residuo = $monto % $cantidad;
        $indice = 0;
        foreach ($locales as $codigo => $nombre) {
            $distribucion[$codigo] += $base + ($indice < $residuo ? 1 : 0);
            $indice++;
        }
    }
    return $distribucion;
}

function flujo_dashboard_gastos_admin_origen($mysqli, $desde, $hasta)
{
    $desdeSql = $mysqli->real_escape_string($desde);
    $hastaSql = $mysqli->real_escape_string($hasta);
    $codAdmin = (int)FLUJO_DASHBOARD_LOCAL_ADMIN;
	$soloHistoricosSinDistribucion = flujo_dashboard_tabla_existe($mysqli, 'gasto_distribucion_local')
		? " AND NOT EXISTS (SELECT 1 FROM gasto_distribucion_local d WHERE d.idgastosFK=g.idgastos)"
		: "";

    $sql = "SELECT g.idgastos, g.monto, g.motivo AS descripcion, g.fecha, g.estado, g.tipo,
            g.cod_motivoIngresoEgresoFK, g.nroboleta,
            IFNULL(m.descripcion,'') AS concepto,
            IFNULL(m.categoria,'') AS categoria,
            IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=g.cod_usuario LIMIT 1),'') AS usuario_nombre,
            IFNULL((SELECT Nombre FROM local l WHERE l.cod_local=g.cod_local LIMIT 1),'') AS nombrelocal
            FROM gastos g
            LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
            WHERE g.fecha>='$desdeSql'
            AND g.fecha<='$hastaSql'
            AND g.cod_local=$codAdmin
            AND g.tipo='Egreso'
            AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')
            AND IFNULL(m.categoria,'')!='ingreso'
			$soloHistoricosSinDistribucion
            ORDER BY FIELD(IFNULL(m.categoria,''), 'directo', 'operativo'), m.categoria IS NULL, m.descripcion ASC, g.fecha DESC, g.idgastos DESC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudieron consultar los gastos administrativos'));
    }

    $result = $stmt->get_result();
    $registros = array();
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }

    return $registros;
}

function flujo_dashboard_detalle_admin_asignado($mysqli, &$categorias, $codLocal, $nombreLocal, $desde, $hasta)
{
    if (!isset(flujo_dashboard_locales_fijos()[$codLocal])) {
        return;
    }

    $registros = flujo_dashboard_gastos_admin_origen($mysqli, $desde, $hasta);
    $distribucion = flujo_dashboard_distribuir_admin_registros($registros);
    $montoObjetivoLocal = isset($distribucion[$codLocal]) ? (int)$distribucion[$codLocal] : 0;
    if ($montoObjetivoLocal <= 0 || count($registros) == 0) {
        return;
    }

    $cantidadLocales = count(flujo_dashboard_locales_fijos());
	$codigosLocales = array_keys(flujo_dashboard_locales_fijos());
	$indiceLocal = array_search((int)$codLocal, $codigosLocales, true);
    foreach ($registros as $indice => $row) {
		$montoRegistro = max(0, (int)round($row['monto']));
		$montoAsignado = $cantidadLocales > 0 ? intdiv($montoRegistro, $cantidadLocales) : 0;
		$residuoRegistro = $cantidadLocales > 0 ? $montoRegistro % $cantidadLocales : 0;
		if ($indiceLocal !== false && $indiceLocal < $residuoRegistro) {
			$montoAsignado++;
		}
        if ($montoAsignado <= 0) {
            continue;
        }

        $conceptoCodigo = trim((string)$row['cod_motivoIngresoEgresoFK']);
        $conceptoNombre = flujo_dashboard_to_utf8($row['concepto']);
        flujo_dashboard_agregar_movimiento(
            $categorias,
            'administracion',
            $conceptoCodigo,
            $conceptoNombre,
            flujo_dashboard_movimiento_gasto($row, $montoAsignado, $nombreLocal)
        );
    }
}

function flujo_dashboard_finalizar_categorias($categorias)
{
    $salida = array();
    foreach ($categorias as $categoria) {
        if (isset($categoria['codigo']) && $categoria['codigo'] == 'deposito') {
            continue;
        }
        $conceptos = array();
        foreach ($categoria['conceptos'] as $concepto) {
            $conceptos[] = $concepto;
        }
        $categoria['conceptos'] = $conceptos;
        $salida[] = $categoria;
    }
    return $salida;
}

function flujo_dashboard_detalle_responder($user)
{
    if (!flujo_dashboard_usuario_tiene_permiso($user)) {
        flujo_dashboard_json(array('1' => 'NI', '2' => 'Sin permiso'));
    }

    $codLocal = (int)flujo_dashboard_param('cod_local', flujo_dashboard_param('local', 0));
    $idsAutorizados = flujo_dashboard_ids_autorizados($user);
    if ($codLocal <= 0 || !in_array($codLocal, $idsAutorizados)) {
        flujo_dashboard_json(array('1' => 'NI', '2' => 'Sucursal no autorizada'));
    }

    $periodo = flujo_dashboard_periodo_solicitado();
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudo conectar a la base de datos'));
    }

    $desde = $periodo['desdeFecha'];
    $hasta = $periodo['hastaFecha'];
    $nombres = flujo_dashboard_nombres_locales($mysqli);
    $nombreLocal = isset($nombres[$codLocal]) ? $nombres[$codLocal] : '';
    $categorias = flujo_dashboard_categorias_base();

    flujo_dashboard_detalle_pagos($mysqli, $categorias, $codLocal, $desde, $hasta);
    flujo_dashboard_detalle_gastos_local($mysqli, $categorias, $codLocal, $desde, $hasta);
    flujo_dashboard_detalle_admin_asignado($mysqli, $categorias, $codLocal, $nombreLocal, $desde, $hasta);

    mysqli_close($mysqli);

    $ingresos = (int)$categorias['ingreso']['total'];
    $costosVariables = (int)$categorias['directo']['total'];
    $gastosFijos = (int)$categorias['operativo']['total'];
    $administracion = (int)$categorias['administracion']['total'];
    $sinCategorizar = (int)$categorias['sinCategoria']['total'];
    $egresos = $costosVariables + $gastosFijos + $administracion + $sinCategorizar;
    $resultado = $ingresos - $egresos;
    $escala = max($ingresos, $egresos, 1);

    flujo_dashboard_json(array(
        '1' => 'exito',
        'periodo' => flujo_dashboard_periodo_respuesta($periodo),
        'limitesPeriodo' => flujo_dashboard_limites_periodo_respuesta($periodo),
        'local' => array(
            'sucursalId' => $codLocal,
            'sucursalNombre' => $nombreLocal
        ),
        'escala' => $escala,
        'totales' => array(
            'ingresos' => $ingresos,
            'costosVariables' => $costosVariables,
            'gastosFijos' => $gastosFijos,
            'administracion' => $administracion,
            'sinCategorizar' => $sinCategorizar,
            'egresos' => $egresos,
            'resultado' => $resultado
        ),
        'categorias' => flujo_dashboard_finalizar_categorias($categorias)
    ));
}

function flujo_dashboard_responder($user)
{
    if (!flujo_dashboard_usuario_tiene_permiso($user)) {
        flujo_dashboard_json(array('1' => 'NI', '2' => 'Sin permiso'));
    }

    $periodo = flujo_dashboard_periodo_solicitado();
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        flujo_dashboard_json(array('1' => 'error', '2' => 'No se pudo conectar a la base de datos'));
    }

    $desde = $periodo['desdeFecha'];
    $hasta = $periodo['hastaFecha'];
    $idsAutorizados = flujo_dashboard_ids_autorizados($user);
    $nombres = flujo_dashboard_nombres_locales($mysqli);
    $pagos = flujo_dashboard_sumar_pagos($mysqli, $idsAutorizados, $desde, $hasta);
    $movimientos = flujo_dashboard_sumar_movimientos($mysqli, $idsAutorizados, $desde, $hasta);
    $registrosAdmin = flujo_dashboard_gastos_admin_origen($mysqli, $desde, $hasta);
    $adminPorLocal = flujo_dashboard_distribuir_admin_registros($registrosAdmin);
	$totalAdmin = 0;
	foreach ($registrosAdmin as $registroAdmin) {
		$totalAdmin += max(0, (int)round($registroAdmin['monto']));
	}

    $locales = array();
    $totalIngresos = 0;
    $totalCostosVariables = 0;
    $totalGastosFijos = 0;
    $totalAdministracion = 0;
    $totalSinCategorizar = 0;
    $escala = 0;

    foreach (flujo_dashboard_locales_fijos() as $codigo => $nombreFallback) {
        if (!in_array($codigo, $idsAutorizados)) {
            continue;
        }

        $mov = isset($movimientos[$codigo]) ? $movimientos[$codigo] : array();
        $ingresosCaja = isset($pagos[$codigo]) ? (int)$pagos[$codigo] : 0;
        $ingresosExtra = isset($mov['ingreso']) ? (int)$mov['ingreso'] : 0;
        $ingresos = $ingresosCaja + $ingresosExtra;
        $costosVariables = isset($mov['directo']) ? (int)$mov['directo'] : 0;
        $gastosFijos = isset($mov['operativo']) ? (int)$mov['operativo'] : 0;
        $sinCategorizar = isset($mov['sinCategoria']) ? (int)$mov['sinCategoria'] : 0;
        $administracionPersistida = isset($mov['administracion']) ? (int)$mov['administracion'] : 0;
        $administracionHistorica = isset($adminPorLocal[$codigo]) ? (int)$adminPorLocal[$codigo] : 0;
        $administracion = $administracionPersistida + $administracionHistorica;
        $egresos = $costosVariables + $gastosFijos + $administracion + $sinCategorizar;
        $resultado = $ingresos - $egresos;

        $totalIngresos += $ingresos;
        $totalCostosVariables += $costosVariables;
        $totalGastosFijos += $gastosFijos;
        $totalAdministracion += $administracion;
        $totalSinCategorizar += $sinCategorizar;
        $escala = max($escala, $ingresos, $egresos);

        $locales[] = array(
            'sucursalId' => $codigo,
            'sucursalNombre' => isset($nombres[$codigo]) ? $nombres[$codigo] : $nombreFallback,
            'ingresos' => $ingresos,
            'ingresosCaja' => $ingresosCaja,
            'ingresosMovimiento' => $ingresosExtra,
            'costosVariables' => $costosVariables,
            'gastosFijos' => $gastosFijos,
            'administracion' => $administracion,
            'sinCategorizar' => $sinCategorizar,
            'egresos' => $egresos,
            'resultado' => $resultado
        );
    }

    mysqli_close($mysqli);

    $totalEgresos = $totalCostosVariables + $totalGastosFijos + $totalAdministracion + $totalSinCategorizar;
    $totalResultado = $totalIngresos - $totalEgresos;

    flujo_dashboard_json(array(
        '1' => 'exito',
        'periodo' => flujo_dashboard_periodo_respuesta($periodo),
        'limitesPeriodo' => flujo_dashboard_limites_periodo_respuesta($periodo),
        'escala' => $escala > 0 ? $escala : 1,
        'locales' => $locales,
        'resumenGeneral' => array(
            'ingresos' => $totalIngresos,
            'costosVariables' => $totalCostosVariables,
            'gastosFijos' => $totalGastosFijos,
            'administracion' => $totalAdministracion,
            'sinCategorizar' => $totalSinCategorizar,
            'egresos' => $totalEgresos,
            'resultado' => $totalResultado
        ),
        'administracionCompartida' => array(
            'origenId' => FLUJO_DASHBOARD_LOCAL_ADMIN,
            'totalOrigen' => $totalAdmin,
            'cantidadLocales' => count(flujo_dashboard_locales_fijos())
        ),
        'sinMovimientos' => ($totalIngresos == 0 && $totalEgresos == 0)
    ));
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $user = flujo_dashboard_autenticar_usuario();
    $funt = flujo_dashboard_to_iso(flujo_dashboard_param('funt', 'resumen'));

    if ($funt == 'resumen') {
        flujo_dashboard_responder($user);
    }

    if ($funt == 'detalle') {
        flujo_dashboard_detalle_responder($user);
    }

    flujo_dashboard_json(array('1' => 'error', '2' => 'Operacion no reconocida'));
}

?>

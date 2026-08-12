<?php

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Asuncion');

require('conexion.php');
include('verificar_navegador.php');
include('buscar_nivel.php');
require_once('historial_pagos_salteados_config.php');

define('CUOTAS_SALTEADAS_PERMISO', 'VERCUOTASSALTEADAS');
define('CUOTAS_SALTEADAS_HISTORIAL_REGISTRAR', 'REGISTRARHISTORIALPAGOSSALTEADOS');
define('CUOTAS_SALTEADAS_LOCALES', '1,3,5,6,7,8,9');

function cs_json($datos)
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

function cs_param($nombre, $defecto = '')
{
    if (isset($_POST[$nombre])) {
        return $_POST[$nombre];
    }
    if (isset($_GET[$nombre])) {
        return $_GET[$nombre];
    }
    return $defecto;
}

function cs_db($valor)
{
    return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function cs_utf8($valor)
{
    $valor = (string)$valor;
    if ($valor === '') {
        return '';
    }
    return mb_check_encoding($valor, 'UTF-8') ? $valor : mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
}

function cs_autenticar()
{
    $usuario = cs_db(cs_param('useru', cs_param('user')));
    $pass = str_replace('=', '+', (string)cs_param('passu', cs_param('pass')));
    $navegador = cs_db(cs_param('navegador'));
    if ($usuario === '' || $pass === '' || $navegador === '' || verificar_navegador($usuario, $navegador, $pass) !== 'ok') {
        cs_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }
    if (controldeaccesoacasas($usuario, CUOTAS_SALTEADAS_PERMISO, " u.accion='SI' ") != 1) {
        cs_json(array('1' => 'sinpermiso', '2' => 'No tiene permiso para consultar cuotas salteadas.'));
    }
    return (int)$usuario;
}

function cs_query($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if ($resultado === false) {
        error_log('Cuotas salteadas: '.$mysqli->error);
        cs_json(array('1' => 'error', '2' => 'No se pudo preparar el informe.'));
    }
    return $resultado;
}

function cs_preparar_temporales($mysqli)
{
    cs_query($mysqli, "DROP TEMPORARY TABLE IF EXISTS tmp_cs_cuota, tmp_cs_venta, tmp_cs_hueco, tmp_cs_secuencia, tmp_cs_hueco_resumen, tmp_cs_entrega, tmp_cs_filtrado, tmp_cs_afectada");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_cuota AS
        SELECT cr.idcredito, cr.cod_venta, vt.cod_clienteFK, vt.cod_local,
            CAST(SUBSTRING_INDEX(TRIM(cr.plazo),'/',1) AS UNSIGNED) AS nro_cuota,
            TRIM(cr.plazo) AS plazo, cr.fechapago,
            GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0)) AS capital_debido,
            IFNULL(pg.capital_pagado,0) AS capital_pagado,
            GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0)) AS interes_debido,
            IFNULL(pg.interes_pagado,0) AS interes_pagado,
            GREATEST(0,GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))-IFNULL(pg.capital_pagado,0))
              + GREATEST(0,GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))-IFNULL(pg.interes_pagado,0)) AS saldo,
            CASE WHEN IFNULL(pg.capital_pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))
                    AND IFNULL(pg.interes_pagado,0)>=GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))
                 THEN 1 ELSE 0 END AS pagada,
            CASE WHEN IFNULL(pg.capital_pagado,0)+IFNULL(pg.interes_pagado,0)>0 THEN 1 ELSE 0 END AS tiene_pago
        FROM credito cr
        INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta
        LEFT JOIN local local_venta ON local_venta.cod_local=vt.cod_local
        LEFT JOIN (
            SELECT cod_creditoFK,
                SUM(CASE WHEN Tipo='Pago Cuota' THEN IFNULL(Monto,0) ELSE 0 END) AS capital_pagado,
                SUM(CASE WHEN Tipo='Interes' THEN IFNULL(Monto,0) ELSE 0 END) AS interes_pagado
            FROM pago GROUP BY cod_creditoFK
        ) pg ON pg.cod_creditoFK=cr.idcredito
        WHERE TRIM(cr.plazo) REGEXP '^[0-9]+(/[0-9]+)?$'
          AND GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))>0
          AND UPPER(IFNULL(cr.Esado,''))<>'INACTIVO'
          AND IFNULL(vt.anulado,'')=''
          AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado'
          AND NOT EXISTS (
              SELECT 1 FROM cancelaciones can WHERE can.cod_venta=vt.cod_venta
          )
          AND vt.cod_local IN (".CUOTAS_SALTEADAS_LOCALES.")
          AND LOWER(TRIM(IFNULL(local_venta.estado,'')))='activo'");
    cs_query($mysqli, "ALTER TABLE tmp_cs_cuota ADD INDEX idx_cs_venta_nro(cod_venta,nro_cuota), ADD INDEX idx_cs_cliente(cod_clienteFK), ADD INDEX idx_cs_local(cod_local)");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_venta AS
        SELECT cod_venta,cod_clienteFK,cod_local,
            MAX(CASE WHEN pagada=1 THEN nro_cuota ELSE 0 END) AS ultima_pagada,
            MIN(CASE WHEN pagada=0 THEN nro_cuota ELSE NULL END) AS primera_pendiente
        FROM tmp_cs_cuota GROUP BY cod_venta,cod_clienteFK,cod_local");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_hueco AS
        SELECT c.* FROM tmp_cs_cuota c
        INNER JOIN tmp_cs_venta v ON v.cod_venta=c.cod_venta
        WHERE v.primera_pendiente IS NOT NULL AND v.ultima_pagada>v.primera_pendiente
          AND c.pagada=0 AND c.nro_cuota<v.ultima_pagada");
    cs_query($mysqli, "ALTER TABLE tmp_cs_hueco ADD INDEX idx_cs_hueco_venta(cod_venta), ADD INDEX idx_cs_hueco_fecha(fechapago)");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_secuencia AS
        SELECT c.cod_venta,
            GROUP_CONCAT(CASE WHEN c.pagada=1 THEN c.nro_cuota END ORDER BY c.nro_cuota SEPARATOR ', ') AS cuotas_pagadas,
            MAX(CASE WHEN c.pagada=1 THEN c.nro_cuota ELSE 0 END) AS ultima_pagada
        FROM tmp_cs_cuota c GROUP BY c.cod_venta");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_hueco_resumen AS
        SELECT h.cod_venta, COUNT(*) AS cuotas_salteadas,
            SUM(h.tiene_pago=1) AS cuotas_parciales, SUM(h.saldo) AS saldo_huecos,
            MIN(h.fechapago) AS primer_vencimiento,
            GROUP_CONCAT(CONCAT(h.nro_cuota,IF(h.tiene_pago=1,' (parcial)','')) ORDER BY h.nro_cuota SEPARATOR ', ') AS cuotas_pendientes
        FROM tmp_cs_hueco h GROUP BY h.cod_venta");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_entrega AS
        SELECT cr.cod_venta,COUNT(*) cantidad_entregas,
            SUM(GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))) monto_entrega,
            SUM(IFNULL((SELECT SUM(pg.Monto) FROM pago pg
                WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Pago Cuota'),0)) pagado_entrega
        FROM credito cr
        WHERE (UPPER(TRIM(IFNULL(cr.plazo,'')))='ENTREGA'
               OR UPPER(TRIM(IFNULL(cr.tipo,'')))='ENTREGA')
          AND UPPER(TRIM(IFNULL(cr.Esado,'')))<>'INACTIVO'
        GROUP BY cr.cod_venta");
    cs_query($mysqli, "ALTER TABLE tmp_cs_entrega ADD PRIMARY KEY(cod_venta)");
}

function cs_filtros($mysqli, $usuario)
{
    $filtros = array('1=1');
    $situacion = strtolower(trim((string)cs_param('situacion', 'todas')));
    if ($situacion === 'vencidas') {
        $filtros[] = 'h.fechapago<CURDATE()';
    } elseif ($situacion === 'hoy') {
        $filtros[] = 'h.fechapago=CURDATE()';
    } elseif ($situacion === 'futuras') {
        $filtros[] = 'h.fechapago>CURDATE()';
    } elseif ($situacion === 'parciales') {
        $filtros[] = 'h.tiene_pago=1';
    }
    $buscar = trim((string)cs_param('buscar'));
    if ($buscar !== '') {
        $buscarSql = $mysqli->real_escape_string(cs_db($buscar));
        $doc = $mysqli->real_escape_string(preg_replace('/[^0-9A-Za-z]/', '', $buscar));
        $partesBusqueda = array("pe.nombre_persona LIKE '%$buscarSql%'", "CAST(h.cod_clienteFK AS CHAR)='$buscarSql'");
        if ($doc !== '') {
            $partesBusqueda[] = "REPLACE(REPLACE(REPLACE(IFNULL(cl.ci_cliente,''),'.',''),'-',''),' ','') LIKE '%$doc%'";
        }
        $filtros[] = '('.implode(' OR ', $partesBusqueda).')';
    }
    $venta = (int)cs_param('venta', 0);
    if ($venta > 0) {
        $filtros[] = 'h.cod_venta='.$venta;
    }
    $local = (int)cs_param('local', 0);
    $puedeCambiarLocal = ((string)$usuario === '2' || controldeaccesoacasas($usuario, 'CAMBIARLOCAL', " u.accion='SI' ") == 1);
    if (!$puedeCambiarLocal) {
        $res = cs_query($mysqli, 'SELECT cod_localFK FROM usuario WHERE cod_usuario='.(int)$usuario.' LIMIT 1');
        $fila = $res->fetch_assoc();
        $local = $fila ? (int)$fila['cod_localFK'] : 0;
    }
    if ($local > 0) {
        $filtros[] = 'h.cod_local='.$local;
    }
    $desde = trim((string)cs_param('desde'));
    $hasta = trim((string)cs_param('hasta'));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
        $filtros[] = "h.fechapago>='".$mysqli->real_escape_string($desde)."'";
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
        $filtros[] = "h.fechapago<='".$mysqli->real_escape_string($hasta)."'";
    }
    return array(implode(' AND ', $filtros), $puedeCambiarLocal, $local);
}

function cs_preparar_filtrado($mysqli, $usuario)
{
    cs_preparar_temporales($mysqli);
    list($where, $puedeCambiarLocal, $localAplicado) = cs_filtros($mysqli, $usuario);
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_filtrado AS
        SELECT h.* FROM tmp_cs_hueco h
        INNER JOIN venta vt ON vt.cod_venta=h.cod_venta
        LEFT JOIN persona pe ON pe.cod_persona=h.cod_clienteFK
        LEFT JOIN cliente cl ON cl.cod_cliente=h.cod_clienteFK
        WHERE $where");
    cs_query($mysqli, "ALTER TABLE tmp_cs_filtrado ADD INDEX idx_cs_filtrado_venta(cod_venta), ADD INDEX idx_cs_filtrado_cliente(cod_clienteFK)");
    cs_query($mysqli, "CREATE TEMPORARY TABLE tmp_cs_afectada AS SELECT DISTINCT cod_venta FROM tmp_cs_filtrado");
    return array($puedeCambiarLocal, $localAplicado);
}

function cs_listar($mysqli, $usuario)
{
    list($puedeCambiarLocal, $localAplicado) = cs_preparar_filtrado($mysqli, $usuario);

    $pagina = max(1, (int)cs_param('pagina', 1));
    $limite = (int)cs_param('limite', 25);
    if ($limite < 10) { $limite = 10; }
    if ($limite > 5000) { $limite = 5000; }
    $offset = ($pagina - 1) * $limite;
    $totalRes = cs_query($mysqli, 'SELECT COUNT(*) total FROM tmp_cs_afectada');
    $total = (int)$totalRes->fetch_assoc()['total'];

    $resumenRes = cs_query($mysqli, "SELECT COUNT(DISTINCT h.cod_clienteFK) clientes,
            COUNT(*) cuotas, SUM(h.saldo) saldo,
            SUM(h.fechapago<CURDATE()) vencidas, SUM(h.fechapago=CURDATE()) hoy,
            SUM(h.fechapago>CURDATE()) futuras, SUM(h.tiene_pago=1) parciales
        FROM tmp_cs_filtrado h");
    $resumen = $resumenRes->fetch_assoc();

    $sql = "SELECT vt.cod_venta,vt.cod_clienteFK,vt.num_factura,vt.fecha_venta,vt.cod_local,
            pe.nombre_persona cliente,pe.telefono,cl.ci_cliente,lo.Nombre local_nombre,
            seq.cuotas_pagadas,seq.ultima_pagada,hr.cuotas_pendientes,hr.cuotas_salteadas,
            hr.cuotas_parciales,hr.saldo_huecos,hr.primer_vencimiento,
            IFNULL(ent.cantidad_entregas,0) cantidad_entregas,
            IFNULL(ent.monto_entrega,0) monto_entrega,IFNULL(ent.pagado_entrega,0) pagado_entrega
        FROM tmp_cs_afectada a
        INNER JOIN venta vt ON vt.cod_venta=a.cod_venta
        LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
        LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
        LEFT JOIN local lo ON lo.cod_local=vt.cod_local
        INNER JOIN tmp_cs_secuencia seq ON seq.cod_venta=vt.cod_venta
        INNER JOIN tmp_cs_hueco_resumen hr ON hr.cod_venta=vt.cod_venta
        LEFT JOIN tmp_cs_entrega ent ON ent.cod_venta=vt.cod_venta
        ORDER BY hr.primer_vencimiento ASC,hr.saldo_huecos DESC,vt.cod_venta ASC
        LIMIT $offset,$limite";
    $resultado = cs_query($mysqli, $sql);
    $filas = array();
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = array(
            'venta' => (int)$fila['cod_venta'], 'cliente_id' => (int)$fila['cod_clienteFK'],
            'cliente' => cs_utf8($fila['cliente']), 'documento' => cs_utf8($fila['ci_cliente']),
            'telefono' => cs_utf8($fila['telefono']), 'factura' => cs_utf8($fila['num_factura']),
            'fecha_venta' => $fila['fecha_venta'], 'local_id' => (int)$fila['cod_local'],
            'local' => cs_utf8($fila['local_nombre']), 'cuotas_pagadas' => $fila['cuotas_pagadas'],
            'ultima_pagada' => (int)$fila['ultima_pagada'], 'cuotas_pendientes' => $fila['cuotas_pendientes'],
            'cuotas_salteadas' => (int)$fila['cuotas_salteadas'], 'cuotas_parciales' => (int)$fila['cuotas_parciales'],
            'saldo' => (int)$fila['saldo_huecos'], 'primer_vencimiento' => $fila['primer_vencimiento'],
            'tiene_entrega' => (int)$fila['cantidad_entregas'] > 0 ? 1 : 0,
            'monto_entrega' => (int)$fila['monto_entrega'], 'pagado_entrega' => (int)$fila['pagado_entrega']
        );
    }
    $locales = array();
    if ($puedeCambiarLocal) {
        $resLocales = cs_query($mysqli, "SELECT cod_local,Nombre FROM local
            WHERE cod_local IN (".CUOTAS_SALTEADAS_LOCALES.")
              AND LOWER(TRIM(IFNULL(estado,'')))='activo'
            ORDER BY Nombre");
        while ($filaLocal = $resLocales->fetch_assoc()) {
            $locales[] = array('id' => (int)$filaLocal['cod_local'], 'nombre' => cs_utf8($filaLocal['Nombre']));
        }
    }
    mysqli_close($mysqli);
    cs_json(array('1' => 'exito', 'filas' => $filas, 'total' => $total, 'pagina' => $pagina,
        'limite' => $limite, 'paginas' => max(1, (int)ceil($total / $limite)),
        'resumen' => array_map('intval', $resumen), 'locales' => $locales,
        'puede_cambiar_local' => $puedeCambiarLocal, 'local_aplicado' => $localAplicado));
}

function cs_registrar_historial($mysqli, $usuario)
{
    if (controldeaccesoacasas($usuario, CUOTAS_SALTEADAS_HISTORIAL_REGISTRAR, " u.accion='SI' ") != 1) {
        cs_json(array('1' => 'sinpermiso', '2' => 'No tiene permiso para registrar el historial.'));
    }
    cs_validar_codigo_historial($mysqli, $usuario, (string)cs_param('codigo_seguridad'));
    $existe = cs_query($mysqli, "SELECT COUNT(*) total FROM information_schema.TABLES
        WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='historial_pago_salteado'");
    if ((int)$existe->fetch_assoc()['total'] !== 1) {
        cs_json(array('1' => 'error', '2' => 'Falta ejecutar la actualizacion del historial de pagos salteados.'));
    }

    $inicioRegistro = date('Y-m-d H:i:s');
    cs_preparar_filtrado($mysqli, $usuario);
    $filtros = array(
        'situacion' => (string)cs_param('situacion', 'todas'),
        'buscar' => (string)cs_param('buscar'),
        'venta' => (int)cs_param('venta', 0),
        'local' => (int)cs_param('local', 0),
        'desde' => (string)cs_param('desde'),
        'hasta' => (string)cs_param('hasta')
    );
    $filtrosJson = $mysqli->real_escape_string(json_encode($filtros, JSON_UNESCAPED_UNICODE));

    $posiblesRes = cs_query($mysqli, "SELECT COUNT(DISTINCT CONCAT(h.idcredito,'-',pg.idPago)) total
        FROM tmp_cs_filtrado h
        INNER JOIN tmp_cs_cuota posterior ON posterior.cod_venta=h.cod_venta
          AND posterior.nro_cuota>h.nro_cuota AND posterior.pagada=1
        INNER JOIN pago pg ON pg.cod_creditoFK=posterior.idcredito
          AND IFNULL(pg.Monto,0)>0");
    $posibles = (int)$posiblesRes->fetch_assoc()['total'];

    $sql = "INSERT IGNORE INTO historial_pago_salteado
        (usuario_deteccion,cod_cliente,cliente_snapshot,documento_snapshot,cod_venta,
         factura_snapshot,cod_local,local_snapshot,cod_credito_pendiente,nro_cuota_pendiente,
         plazo_pendiente,vencimiento_pendiente,capital_pendiente,capital_pagado_pendiente,
         interes_pendiente,interes_pagado_pendiente,saldo_pendiente,cod_credito_pagado,
         nro_cuota_pagada,plazo_pagado,id_pago,fecha_pago,fecha_hora_pago,monto_pago,
         tipo_pago,forma_pago,comprobante_snapshot,ultima_cuota_pagada,filtros_snapshot,huella)
        SELECT ".(int)$usuario.",h.cod_clienteFK,pe.nombre_persona,cl.ci_cliente,h.cod_venta,
          vt.num_factura,h.cod_local,lo.Nombre,h.idcredito,h.nro_cuota,h.plazo,h.fechapago,
          h.capital_debido,h.capital_pagado,h.interes_debido,h.interes_pagado,h.saldo,
          posterior.idcredito,posterior.nro_cuota,posterior.plazo,pg.idPago,pg.Fecha,pg.hora,
          IFNULL(pg.Monto,0),pg.Tipo,pg.tipopago,COALESCE(NULLIF(pg.num_comprobante,''),pg.nrofactura),
          posterior.nro_cuota,'$filtrosJson',SHA1(CONCAT(h.idcredito,'|',pg.idPago))
        FROM tmp_cs_filtrado h
        INNER JOIN venta vt ON vt.cod_venta=h.cod_venta
        LEFT JOIN persona pe ON pe.cod_persona=h.cod_clienteFK
        LEFT JOIN cliente cl ON cl.cod_cliente=h.cod_clienteFK
        LEFT JOIN local lo ON lo.cod_local=h.cod_local
        INNER JOIN tmp_cs_cuota posterior ON posterior.cod_venta=h.cod_venta
          AND posterior.nro_cuota>h.nro_cuota AND posterior.pagada=1
        INNER JOIN pago pg ON pg.cod_creditoFK=posterior.idcredito
          AND IFNULL(pg.Monto,0)>0";
    cs_query($mysqli, $sql);
    $insertados = (int)$mysqli->affected_rows;
    $sqlDetalle = "INSERT IGNORE INTO historial_pago_salteado_cuota
        (id_historialFK,cod_credito,nro_cuota,plazo,vencimiento,capital_debido,
         capital_pagado,interes_debido,interes_pagado,saldo,pagada,tiene_pago,salteada,estado_snapshot)
        SELECT hist.id_historial,cr.idcredito,
          CAST(SUBSTRING_INDEX(TRIM(cr.plazo),'/',1) AS UNSIGNED),TRIM(cr.plazo),cr.fechapago,
          GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0)),IFNULL(pt.capital_pagado,0),
          GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0)),IFNULL(pt.interes_pagado,0),
          GREATEST(0,GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))-IFNULL(pt.capital_pagado,0))
            +GREATEST(0,GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))-IFNULL(pt.interes_pagado,0)),
          CASE WHEN IFNULL(pt.capital_pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))
             AND GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))>0
             AND IFNULL(pt.interes_pagado,0)>=GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0)) THEN 1 ELSE 0 END,
          CASE WHEN IFNULL(pt.capital_pagado,0)+IFNULL(pt.interes_pagado,0)>0 THEN 1 ELSE 0 END,
          CASE WHEN GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))>0
             AND NOT (IFNULL(pt.capital_pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))
               AND IFNULL(pt.interes_pagado,0)>=GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0)))
             AND CAST(SUBSTRING_INDEX(TRIM(cr.plazo),'/',1) AS UNSIGNED)<hist.ultima_cuota_pagada THEN 1 ELSE 0 END,
          CASE
            WHEN GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))=0 THEN 'Sin monto'
            WHEN IFNULL(pt.capital_pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))
             AND IFNULL(pt.interes_pagado,0)>=GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0)) THEN 'Pagada'
            WHEN IFNULL(pt.capital_pagado,0)+IFNULL(pt.interes_pagado,0)>0 THEN 'Pago parcial'
            WHEN cr.fechapago<CURDATE() THEN 'Vencida'
            WHEN cr.fechapago=CURDATE() THEN 'Vence hoy'
            ELSE 'Pendiente' END
        FROM historial_pago_salteado hist
        INNER JOIN credito cr ON cr.cod_venta=hist.cod_venta
        LEFT JOIN (
          SELECT cod_creditoFK,
            SUM(CASE WHEN Tipo='Pago Cuota' THEN IFNULL(Monto,0) ELSE 0 END) capital_pagado,
            SUM(CASE WHEN Tipo='Interes' THEN IFNULL(Monto,0) ELSE 0 END) interes_pagado
          FROM pago GROUP BY cod_creditoFK
        ) pt ON pt.cod_creditoFK=cr.idcredito
        WHERE TRIM(cr.plazo) REGEXP '^[0-9]+(/[0-9]+)?$'
          AND UPPER(IFNULL(cr.Esado,''))<>'INACTIVO'
          AND NOT EXISTS (SELECT 1 FROM historial_pago_salteado_cuota d
                          WHERE d.id_historialFK=hist.id_historial)";
    cs_query($mysqli, $sqlDetalle);
    $detallesInsertados = (int)$mysqli->affected_rows;
    $sqlVenta = "INSERT IGNORE INTO historial_pago_salteado_venta
        (usuario_deteccion,cod_cliente,cliente_snapshot,documento_snapshot,telefono_snapshot,
         cod_venta,factura_snapshot,fecha_venta,cod_local,local_snapshot,cuotas_pagadas,
         ultima_cuota_pagada,cuotas_pendientes,cuotas_salteadas,cuotas_parciales,saldo_huecos,
         primer_vencimiento,cantidad_entregas,monto_entrega,pagado_entrega,
         id_historial_detalleFK,filtros_snapshot,huella)
        SELECT ".(int)$usuario.",vt.cod_clienteFK,pe.nombre_persona,cl.ci_cliente,pe.telefono,
          vt.cod_venta,vt.num_factura,vt.fecha_venta,vt.cod_local,lo.Nombre,seq.cuotas_pagadas,
          seq.ultima_pagada,hr.cuotas_pendientes,hr.cuotas_salteadas,hr.cuotas_parciales,
          hr.saldo_huecos,hr.primer_vencimiento,IFNULL(ent.cantidad_entregas,0),
          IFNULL(ent.monto_entrega,0),IFNULL(ent.pagado_entrega,0),
          (SELECT MAX(hist.id_historial) FROM historial_pago_salteado hist
           WHERE hist.cod_venta=vt.cod_venta),
          '$filtrosJson',SHA1(CONCAT(vt.cod_venta,'|',IFNULL(seq.cuotas_pagadas,''),'|',
             IFNULL(hr.cuotas_pendientes,''),'|',hr.saldo_huecos,'|',hr.cuotas_parciales,'|',
             IFNULL(hr.primer_vencimiento,''),'|',IFNULL(ent.monto_entrega,0),'|',IFNULL(ent.pagado_entrega,0)))
        FROM tmp_cs_afectada a
        INNER JOIN venta vt ON vt.cod_venta=a.cod_venta
        LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
        LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
        LEFT JOIN local lo ON lo.cod_local=vt.cod_local
        INNER JOIN tmp_cs_secuencia seq ON seq.cod_venta=vt.cod_venta
        INNER JOIN tmp_cs_hueco_resumen hr ON hr.cod_venta=vt.cod_venta
        LEFT JOIN tmp_cs_entrega ent ON ent.cod_venta=vt.cod_venta
        WHERE EXISTS (SELECT 1 FROM historial_pago_salteado hist WHERE hist.cod_venta=vt.cod_venta)";
    cs_query($mysqli, $sqlVenta);
    $ventasInsertadas = (int)$mysqli->affected_rows;
    $inicioSql = $mysqli->real_escape_string($inicioRegistro);
    $sqlEntregaHistorica = "INSERT IGNORE INTO historial_pago_salteado_entrega
        (id_historial_ventaFK,cod_credito,plazo,vencimiento,capital_debido,capital_pagado,saldo,pagada,tiene_pago,estado_snapshot)
        SELECT hv.id_historial_venta,cr.idcredito,TRIM(cr.plazo),cr.fechapago,
          GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0)),IFNULL(pg.pagado,0),
          GREATEST(0,GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))-IFNULL(pg.pagado,0)),
          CASE WHEN GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))>0
             AND IFNULL(pg.pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0)) THEN 1 ELSE 0 END,
          CASE WHEN IFNULL(pg.pagado,0)>0 THEN 1 ELSE 0 END,
          CASE WHEN GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))=0 THEN 'Sin monto'
             WHEN IFNULL(pg.pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0)) THEN 'Pagada'
             WHEN IFNULL(pg.pagado,0)>0 THEN 'Pago parcial' ELSE 'Pendiente' END
        FROM historial_pago_salteado_venta hv
        INNER JOIN credito cr ON cr.cod_venta=hv.cod_venta
          AND (UPPER(TRIM(IFNULL(cr.plazo,'')))='ENTREGA' OR UPPER(TRIM(IFNULL(cr.tipo,'')))='ENTREGA')
        LEFT JOIN (SELECT cod_creditoFK,SUM(Monto) pagado FROM pago WHERE Tipo='Pago Cuota' GROUP BY cod_creditoFK) pg
          ON pg.cod_creditoFK=cr.idcredito
        WHERE hv.usuario_deteccion=".(int)$usuario." AND hv.fecha_deteccion>='$inicioSql'
          AND UPPER(IFNULL(cr.Esado,''))<>'INACTIVO'";
    cs_query($mysqli, $sqlEntregaHistorica);
    mysqli_close($mysqli);
    cs_json(array('1' => 'exito', 'insertados' => $insertados,
        'existentes' => max(0, $posibles - $insertados), 'detectados' => $posibles,
        'cuotas_insertadas' => $detallesInsertados, 'ventas_insertadas' => $ventasInsertadas));
}

function cs_validar_codigo_historial($mysqli, $usuario, $codigo)
{
    $usuario = (int)$usuario;
    $mysqli->begin_transaction();
    $res = $mysqli->query("SELECT intentos_fallidos,ultimo_intento,bloqueo_hasta
        FROM historial_pago_salteado_intento_seguridad WHERE usuario_id=$usuario FOR UPDATE");
    if ($res === false) {
        $mysqli->rollback();
        cs_json(array('1' => 'error', '2' => 'No se pudo validar el codigo de seguridad.'));
    }
    $fila = $res->fetch_assoc();
    if ($fila && $fila['bloqueo_hasta'] !== null && strtotime($fila['bloqueo_hasta']) > time()) {
        $mysqli->commit();
        cs_json(array('1' => 'bloqueado', '2' => 'Demasiados intentos incorrectos. Intente nuevamente en 10 minutos.'));
    }
    $intentos = $fila ? (int)$fila['intentos_fallidos'] : 0;
    if ($fila && $fila['ultimo_intento'] !== null && strtotime($fila['ultimo_intento']) < time() - (HPS_MINUTOS_BLOQUEO * 60)) {
        $intentos = 0;
    }
    $hashIngresado = hash('sha256', HPS_CODIGO_SALT.'|'.$codigo);
    if ($codigo === '' || !hash_equals(HPS_CODIGO_HASH, $hashIngresado)) {
        $intentos++;
        $bloqueoSql = $intentos >= HPS_MAX_INTENTOS
            ? 'DATE_ADD(NOW(),INTERVAL '.(int)HPS_MINUTOS_BLOQUEO.' MINUTE)' : 'NULL';
        $sql = "INSERT INTO historial_pago_salteado_intento_seguridad
            (usuario_id,intentos_fallidos,ultimo_intento,bloqueo_hasta)
            VALUES ($usuario,$intentos,NOW(),$bloqueoSql)
            ON DUPLICATE KEY UPDATE intentos_fallidos=VALUES(intentos_fallidos),
              ultimo_intento=VALUES(ultimo_intento),bloqueo_hasta=VALUES(bloqueo_hasta)";
        if (!$mysqli->query($sql)) {
            $mysqli->rollback();
            cs_json(array('1' => 'error', '2' => 'No se pudo validar el codigo de seguridad.'));
        }
        $mysqli->commit();
        if ($intentos >= HPS_MAX_INTENTOS) {
            cs_json(array('1' => 'bloqueado', '2' => 'Codigo incorrecto. El registro fue bloqueado por 10 minutos.'));
        }
        cs_json(array('1' => 'codigo_invalido', '2' => 'Codigo de seguridad incorrecto.',
            'intentos_restantes' => HPS_MAX_INTENTOS - $intentos));
    }
    if ($fila) {
        $mysqli->query("DELETE FROM historial_pago_salteado_intento_seguridad WHERE usuario_id=$usuario");
    }
    $mysqli->commit();
}

function cs_detalle($mysqli, $usuario)
{
    $venta = (int)cs_param('venta', 0);
    if ($venta <= 0) {
        cs_json(array('1' => 'camposvacio', '2' => 'Venta invalida.'));
    }
    $filtroLocal = '';
    if ((string)$usuario !== '2' && controldeaccesoacasas($usuario, 'CAMBIARLOCAL', " u.accion='SI' ") != 1) {
        $resLocal = cs_query($mysqli, 'SELECT cod_localFK FROM usuario WHERE cod_usuario='.(int)$usuario.' LIMIT 1');
        $filaLocal = $resLocal->fetch_assoc();
        $localUsuario = $filaLocal ? (int)$filaLocal['cod_localFK'] : 0;
        $filtroLocal = ' AND vt.cod_local='.$localUsuario;
    }
    $resultado = cs_query($mysqli, "SELECT cr.idcredito,
            CASE WHEN UPPER(TRIM(IFNULL(cr.plazo,'')))='ENTREGA' OR UPPER(TRIM(IFNULL(cr.tipo,'')))='ENTREGA' THEN 0 ELSE CAST(SUBSTRING_INDEX(TRIM(cr.plazo),'/',1) AS UNSIGNED) END nro_cuota,
            CASE WHEN UPPER(TRIM(IFNULL(cr.plazo,'')))='ENTREGA' OR UPPER(TRIM(IFNULL(cr.tipo,'')))='ENTREGA' THEN 1 ELSE 0 END es_entrega,
            TRIM(cr.plazo) plazo,cr.fechapago,
            GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0)) capital_debido,
            IFNULL(pg.capital_pagado,0) capital_pagado,
            GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0)) interes_debido,
            IFNULL(pg.interes_pagado,0) interes_pagado,
            GREATEST(0,GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))-IFNULL(pg.capital_pagado,0))
              + GREATEST(0,GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))-IFNULL(pg.interes_pagado,0)) saldo,
            CASE WHEN IFNULL(pg.capital_pagado,0)>=GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))
                    AND GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))>0
                    AND IFNULL(pg.interes_pagado,0)>=GREATEST(0,IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))
                 THEN 1 ELSE 0 END pagada,
            CASE WHEN GREATEST(0,IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))=0 THEN 1 ELSE 0 END sin_monto,
            CASE WHEN IFNULL(pg.capital_pagado,0)+IFNULL(pg.interes_pagado,0)>0 THEN 1 ELSE 0 END tiene_pago
        FROM credito cr
        LEFT JOIN (
            SELECT cod_creditoFK,
                SUM(CASE WHEN Tipo='Pago Cuota' THEN IFNULL(Monto,0) ELSE 0 END) capital_pagado,
                SUM(CASE WHEN Tipo='Interes' THEN IFNULL(Monto,0) ELSE 0 END) interes_pagado
            FROM pago WHERE cod_venta_fk=$venta GROUP BY cod_creditoFK
        ) pg ON pg.cod_creditoFK=cr.idcredito
        INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta
        LEFT JOIN local local_venta ON local_venta.cod_local=vt.cod_local
        WHERE cr.cod_venta=$venta
          AND (TRIM(cr.plazo) REGEXP '^[0-9]+(/[0-9]+)?$'
               OR UPPER(TRIM(IFNULL(cr.plazo,'')))='ENTREGA'
               OR UPPER(TRIM(IFNULL(cr.tipo,'')))='ENTREGA')
          AND UPPER(IFNULL(cr.Esado,''))<>'INACTIVO'
          AND IFNULL(vt.anulado,'')='' AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado'
          AND NOT EXISTS (
              SELECT 1 FROM cancelaciones can WHERE can.cod_venta=vt.cod_venta
          )
          AND vt.cod_local IN (".CUOTAS_SALTEADAS_LOCALES.")
          AND LOWER(TRIM(IFNULL(local_venta.estado,'')))='activo'
          $filtroLocal
        ORDER BY es_entrega DESC,nro_cuota,cr.fechapago,cr.idcredito");
    $filasCuotas = array();
    $ultimaPagada = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $filasCuotas[] = $fila;
        if ((int)$fila['es_entrega'] !== 1 && (int)$fila['pagada'] === 1 && (int)$fila['nro_cuota'] > $ultimaPagada) {
            $ultimaPagada = (int)$fila['nro_cuota'];
        }
    }
    if (count($filasCuotas) === 0) {
        mysqli_close($mysqli);
        cs_json(array('1' => 'error', '2' => 'La venta no esta disponible o pertenece a un local inactivo.'));
    }
    $cuotas = array();
    foreach ($filasCuotas as $fila) {
        $sinMonto = (int)$fila['sin_monto'] === 1;
        $esEntrega = (int)$fila['es_entrega'] === 1;
        $salteada = (!$esEntrega && !$sinMonto && (int)$fila['pagada'] !== 1 && (int)$fila['nro_cuota'] < $ultimaPagada) ? 1 : 0;
        $estado = $sinMonto ? 'Sin monto' : (((int)$fila['pagada'] === 1) ? 'Pagada' : (((int)$fila['tiene_pago'] === 1) ? 'Pago parcial' : (($fila['fechapago'] < date('Y-m-d')) ? 'Vencida' : (($fila['fechapago'] === date('Y-m-d')) ? 'Vence hoy' : 'Pendiente'))));
        $cuotas[] = array('credito' => (int)$fila['idcredito'], 'numero' => (int)$fila['nro_cuota'],
            'es_entrega' => $esEntrega ? 1 : 0,
            'plazo' => $fila['plazo'], 'vencimiento' => $fila['fechapago'], 'estado' => $estado,
            'salteada' => $salteada, 'sin_monto' => $sinMonto ? 1 : 0,
            'capital_debido' => (int)$fila['capital_debido'], 'capital_pagado' => (int)$fila['capital_pagado'],
            'interes_debido' => (int)$fila['interes_debido'], 'interes_pagado' => (int)$fila['interes_pagado'],
            'saldo' => (int)$fila['saldo']);
    }
    mysqli_close($mysqli);
    cs_json(array('1' => 'exito', 'venta' => $venta, 'ultima_pagada' => $ultimaPagada, 'cuotas' => $cuotas));
}

$usuario = cs_autenticar();
$mysqli = conectar_al_servidor();
if ($mysqli->connect_errno) {
    cs_json(array('1' => 'error', '2' => 'No se pudo conectar a la base de datos.'));
}
$operacion = strtolower(trim((string)cs_param('funt', 'listar')));
if ($operacion === 'registrar_historial') {
    cs_registrar_historial($mysqli, $usuario);
}
if ($operacion === 'detalle') {
    cs_detalle($mysqli, $usuario);
}
cs_listar($mysqli, $usuario);

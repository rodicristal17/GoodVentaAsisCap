<?php

function interconsultaOperacionUtf8($valor)
{
    $valor = (string)$valor;
    if ($valor !== '' && !mb_check_encoding($valor, 'UTF-8')) {
        return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
    }
    return $valor;
}

function interconsultaOperacionTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '' || !($mysqli instanceof mysqli)) { return false; }
    if (array_key_exists($tabla, $cache)) { return $cache[$tabla]; }
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1");
    if (!$stmt) { $cache[$tabla] = false; return false; }
    $stmt->bind_param('s', $tabla);
    $stmt->execute();
    $cache[$tabla] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$tabla];
}

function interconsultaOperacionColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$columna);
    $clave = $tabla.'.'.$columna;
    if ($tabla === '' || $columna === '' || !($mysqli instanceof mysqli)) { return false; }
    if (array_key_exists($clave, $cache)) { return $cache[$clave]; }
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1");
    if (!$stmt) { $cache[$clave] = false; return false; }
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $cache[$clave] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$clave];
}

function interconsultaOperacionCondicionLocalEspecifico($codLocal, $alias = 'ic')
{
    $codLocal = intval($codLocal);
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    if ($alias === '' || $codLocal <= 0) {
        return '1=0';
    }
    return "(".$alias.".cod_localFK=".$codLocal."
        OR EXISTS(
            SELECT 1 FROM interconsulta_paciente_venta io_ipv
            INNER JOIN venta io_vt ON io_vt.cod_venta=io_ipv.cod_ventaFK
            WHERE io_ipv.cod_interConsultaFK=".$alias.".cod_interConsulta
              AND io_ipv.estado='activo' AND io_vt.cod_local=".$codLocal." LIMIT 1
        )
        OR EXISTS(
            SELECT 1 FROM venta io_vtd
            WHERE io_vtd.cod_venta=".$alias.".cod_ventaFK
              AND io_vtd.cod_local=".$codLocal." LIMIT 1
        )
        OR ((IFNULL(".$alias.".cod_localFK,0)=0)
            AND EXISTS(
                SELECT 1 FROM usuario io_uc
                WHERE io_uc.cod_usuario=".$alias.".cod_usuarioFK_create
                  AND io_uc.cod_localFK=".$codLocal." LIMIT 1
            )))";
}

function interconsultaOperacionCondicionLocalUsuario($codUsuario, $codLocal, $alias, $mysqli)
{
    $codLocal = intval($codLocal);
    if ($codLocal > 0) {
        if (!interconsultaAccesoUsuarioPuedeUsarLocal($codUsuario, $codLocal, $mysqli)) {
            return '1=0';
        }
        return interconsultaOperacionCondicionLocalEspecifico($codLocal, $alias);
    }
    return interconsultaAccesoCondicionLocalSql($codUsuario, $alias, $mysqli);
}

function interconsultaOperacionCondicionLocalAgenda($codUsuario, $codLocal, $aliasAgenda, $aliasConsultorio, $mysqli)
{
    $codUsuario = intval($codUsuario);
    $codLocal = intval($codLocal);
    $aliasAgenda = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasAgenda);
    $aliasConsultorio = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasConsultorio);
    if ($aliasAgenda === '' || $aliasConsultorio === '') {
        return '1=0';
    }
    if ($codLocal <= 0) {
        if (interconsultaAccesoPuedeVerTodosLocales($codUsuario)) {
            return '1=1';
        }
        $codLocal = interconsultaAccesoLocalPrincipal($codUsuario, $mysqli);
    }
    if ($codLocal <= 0 || !interconsultaAccesoUsuarioPuedeUsarLocal($codUsuario, $codLocal, $mysqli)) {
        return '1=0';
    }
    return "(".$aliasConsultorio.".cod_localFk=".$codLocal."
        OR (IFNULL(".$aliasConsultorio.".cod_localFk,0)=0
            AND EXISTS(
                SELECT 1 FROM venta io_ag_vt
                WHERE io_ag_vt.cod_venta=".$aliasAgenda.".cod_ventaFK
                  AND io_ag_vt.cod_local=".$codLocal."
                LIMIT 1
            )))";
}

function interconsultaOperacionPermisoDefinido($mysqli, $codigo)
{
    $stmt = $mysqli->prepare("SELECT 1 FROM listadodeacceso WHERE codigo=? LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $existe = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $existe;
}

function interconsultaOperacionPuedeVerCuotas($mysqli, $codUsuario)
{
    $codUsuario = intval($codUsuario);
    if ($codUsuario === 2) {
        return true;
    }
    if (interconsultaOperacionPermisoDefinido($mysqli, 'VERCOBRARCUOTA')) {
        return interconsultaAccesoTienePermiso($codUsuario, 'VERCOBRARCUOTA');
    }
    if (interconsultaOperacionPermisoDefinido($mysqli, 'VERPAGOSCREDITO')) {
        return interconsultaAccesoTienePermiso($codUsuario, 'VERPAGOSCREDITO');
    }
    return false;
}

function interconsultaOperacionEstadoCuota($saldoCapital, $saldoInteres, $fechaVencimiento, $pagadoCapital, $pagadoInteres, $estadoOriginal)
{
    $estadoOriginal = strtoupper(trim((string)$estadoOriginal));
    if (strpos($estadoOriginal, 'ANUL') !== false) {
        return 'Anulada';
    }
    if ($estadoOriginal === 'INACTIVO') {
        return 'Inactiva';
    }
    if ($saldoCapital <= 0 && $saldoInteres <= 0) {
        return 'Pagada';
    }
    if ($fechaVencimiento !== '' && $fechaVencimiento < date('Y-m-d')) {
        return ($pagadoCapital + $pagadoInteres) > 0 ? 'Vencida con pago parcial' : 'Vencida';
    }
    if (($pagadoCapital + $pagadoInteres) > 0) {
        return 'Pago parcial';
    }
    return 'Pendiente';
}

function buscarDetalleCuotasInterConsulta($codInterConsulta, $codVentaSolicitada, $codUsuario)
{
    $codInterConsulta = intval($codInterConsulta);
    $codVentaSolicitada = intval($codVentaSolicitada);
    $codUsuario = intval($codUsuario);
    if ($codInterConsulta <= 0) {
        return array('ok' => false, 'codigo' => 'camposvacio', 'mensaje' => 'Seleccione un hilo para consultar sus cuotas.');
    }

    $mysqli = conectar_al_servidor();
    if (!interconsultaAccesoUsuarioPuedeAccederHilo($codInterConsulta, $codUsuario, false, $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'Usted no tiene acceso a este hilo.');
    }
    if (!interconsultaOperacionPuedeVerCuotas($mysqli, $codUsuario)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso financiero para visualizar cuotas.');
    }

    $stmt = $mysqli->prepare("SELECT ic.cod_interConsulta,ic.asunto,ic.cod_ventaFK,ic.cod_localFK,
            ip.cod_clienteFK_principal
        FROM interconsulta ic
        LEFT JOIN interconsulta_paciente ip
          ON ip.cod_interConsultaFK=ic.cod_interConsulta AND ip.estado='activo'
        WHERE ic.cod_interConsulta=? LIMIT 1");
    $stmt->bind_param('i', $codInterConsulta);
    $stmt->execute();
    $hilo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$hilo) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'error', 'mensaje' => 'El hilo ya no existe.');
    }

    $ventasRelacionadas = array();
    if (intval($hilo['cod_ventaFK']) > 0) {
        $ventasRelacionadas[intval($hilo['cod_ventaFK'])] = 1;
    }
    $stmt = $mysqli->prepare("SELECT cod_ventaFK FROM interconsulta_paciente_venta
        WHERE cod_interConsultaFK=? AND estado='activo'");
    if ($stmt) {
        $stmt->bind_param('i', $codInterConsulta);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $ventasRelacionadas[intval($fila['cod_ventaFK'])] = 1;
            }
        }
        $stmt->close();
    }

    $codCliente = intval($hilo['cod_clienteFK_principal']);
    $condicionesVenta = array();
    if (count($ventasRelacionadas) > 0) {
        $condicionesVenta[] = 'vt.cod_venta IN ('.implode(',', array_keys($ventasRelacionadas)).')';
    }
    if ($codCliente > 0) {
        $condicionesVenta[] = 'vt.cod_clienteFK='.$codCliente;
    }
    if (count($condicionesVenta) === 0) {
        $mysqli->close();
        return array('ok' => true, 'datos' => array(
            'hilo' => array('cod_interConsulta' => $codInterConsulta, 'asunto' => interconsultaOperacionUtf8($hilo['asunto'])),
            'ventas' => array()
        ));
    }

    $sqlVentas = "SELECT vt.cod_venta,vt.num_factura,vt.puntoexpedicion,vt.fecha_venta,vt.TipoVenta,
            vt.total_venta,vt.descuento,vt.cod_local,vt.cod_clienteFK
        FROM venta vt
        WHERE (".implode(' OR ', $condicionesVenta).")
          AND IFNULL(vt.anulado,'')=''
          AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado'
          AND NOT EXISTS(SELECT 1 FROM cancelaciones cv WHERE cv.cod_venta=vt.cod_venta LIMIT 1)
        ORDER BY vt.fecha_venta DESC,vt.cod_venta DESC LIMIT 100";
    $resultadoVentas = $mysqli->query($sqlVentas);
    if (!$resultadoVentas) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'error', 'mensaje' => 'No se pudieron consultar las ventas relacionadas.');
    }
    $ventas = array();
    while ($venta = $resultadoVentas->fetch_assoc()) {
        $idVenta = intval($venta['cod_venta']);
        if (!interconsultaAccesoUsuarioPuedeUsarLocal($codUsuario, intval($venta['cod_local']), $mysqli)) {
            continue;
        }
        if ($codVentaSolicitada > 0 && $idVenta !== $codVentaSolicitada) {
            continue;
        }
        $venta['cuotas'] = array();
        $venta['saldo_pendiente'] = 0;
        $ventas[$idVenta] = $venta;
    }
    if ($codVentaSolicitada > 0 && !isset($ventas[$codVentaSolicitada])) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'La venta solicitada no pertenece al paciente y local autorizados para este hilo.');
    }
    if (count($ventas) === 0) {
        $mysqli->close();
        return array('ok' => true, 'datos' => array(
            'hilo' => array('cod_interConsulta' => $codInterConsulta, 'asunto' => interconsultaOperacionUtf8($hilo['asunto'])),
            'ventas' => array()
        ));
    }

    $idsVentas = array_keys($ventas);
    $sqlCuotas = "SELECT cr.idcredito,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.descuento,
            cr.totalinteres,cr.deudaInteres,cr.Esado,
            IFNULL(SUM(CASE WHEN pg.Tipo='Pago Cuota' THEN pg.Monto ELSE 0 END),0) AS pago_capital,
            IFNULL(SUM(CASE WHEN pg.Tipo='Interes' THEN pg.Monto ELSE 0 END),0) AS pago_interes
        FROM credito cr
        LEFT JOIN pago pg ON pg.cod_creditoFK=cr.idcredito
        WHERE cr.cod_venta IN (".implode(',', $idsVentas).")
        GROUP BY cr.idcredito,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.descuento,
            cr.totalinteres,cr.deudaInteres,cr.Esado
        ORDER BY cr.cod_venta ASC,
            CASE WHEN cr.plazo REGEXP '^[0-9]+' THEN CAST(SUBSTRING_INDEX(cr.plazo,'/',1) AS UNSIGNED) ELSE 999999 END ASC,
            cr.fechapago ASC,cr.idcredito ASC";
    $resultadoCuotas = $mysqli->query($sqlCuotas);
    if (!$resultadoCuotas) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'error', 'mensaje' => 'No se pudo calcular el detalle de cuotas.');
    }
    while ($fila = $resultadoCuotas->fetch_assoc()) {
        $idVenta = intval($fila['cod_venta']);
        if (!isset($ventas[$idVenta])) {
            continue;
        }
        $capital = max(0, intval($fila['Monto']) - intval($fila['descuento']));
        $interes = max(0, intval($fila['totalinteres']) + intval($fila['deudaInteres']));
        $pagadoCapital = intval($fila['pago_capital']);
        $pagadoInteres = intval($fila['pago_interes']);
        $saldoCapital = max(0, $capital - $pagadoCapital);
        $saldoInteres = max(0, $interes - $pagadoInteres);
        $saldo = $saldoCapital + $saldoInteres;
        $fechaVencimiento = (string)$fila['fechapago'];
        $estado = interconsultaOperacionEstadoCuota($saldoCapital, $saldoInteres, $fechaVencimiento, $pagadoCapital, $pagadoInteres, $fila['Esado']);
        $esCobrable = !in_array($estado, array('Anulada', 'Inactiva'), true);
        $diasMora = ($esCobrable && $saldo > 0 && $fechaVencimiento !== '' && $fechaVencimiento < date('Y-m-d'))
            ? max(0, intval(floor((strtotime(date('Y-m-d')) - strtotime($fechaVencimiento)) / 86400))) : 0;
        if ($esCobrable) {
            $ventas[$idVenta]['saldo_pendiente'] += $saldo;
        }
        $ventas[$idVenta]['cuotas'][] = array(
            'id_credito' => intval($fila['idcredito']),
            'nro_cuota' => interconsultaOperacionUtf8($fila['plazo']),
            'fecha_vencimiento' => $fechaVencimiento,
            'capital' => $capital,
            'interes' => $interes,
            'pagado_capital' => $pagadoCapital,
            'pagado_interes' => $pagadoInteres,
            'saldo' => $saldo,
            'estado' => $estado,
            'dias_mora' => $diasMora,
            'es_cobrable' => $esCobrable ? 1 : 0
        );
    }

    $salidaVentas = array();
    foreach ($ventas as $venta) {
        $numeroVisible = trim((string)$venta['puntoexpedicion']) !== ''
            ? $venta['puntoexpedicion'].'-'.$venta['num_factura'] : $venta['num_factura'];
        $salidaVentas[] = array(
            'cod_venta' => intval($venta['cod_venta']),
            'num_factura' => interconsultaOperacionUtf8($numeroVisible),
            'fecha_venta' => (string)$venta['fecha_venta'],
            'tipo_venta' => interconsultaOperacionUtf8($venta['TipoVenta']),
            'total_venta' => max(0, intval($venta['total_venta']) - intval($venta['descuento'])),
            'saldo_pendiente' => intval($venta['saldo_pendiente']),
            'cuotas' => $venta['cuotas']
        );
    }
    $mysqli->close();
    return array('ok' => true, 'datos' => array(
        'hilo' => array('cod_interConsulta' => $codInterConsulta, 'asunto' => interconsultaOperacionUtf8($hilo['asunto'])),
        'ventas' => $salidaVentas
    ));
}

function interconsultaOperacionTipoCategoria($categoria, $tipo)
{
    $categoria = normalizarTipoHiloInterConsulta($categoria);
    $tipo = normalizarTipoHiloInterConsulta($tipo);
    $mapa = array(
        'pagos_egresos' => array('pagos' => 'pagos', 'pago' => 'pagos', 'compras' => 'compras', 'compra' => 'compras', 'egresos' => 'egresos', 'egreso' => 'egresos', 'colaborador' => 'colaborador', 'rrhh' => 'colaborador'),
        'judiciales' => array('judicial' => 'judicial', 'judiciales' => 'judicial'),
        'administrativo_clinico' => array('administrativo' => 'administrativo', 'clinico' => 'clinico', 'interno' => 'interno')
    );
    if ($categoria === '' && $tipo !== '') {
        $categoria = obtenerCategoriaPrincipalHilo($tipo);
    }
    if (!isset($mapa[$categoria])) {
        return array('ok' => false, 'mensaje' => 'La categoria de destino no es valida.');
    }
    if ($tipo === '') {
        $predeterminados = array('pagos_egresos' => 'pagos', 'judiciales' => 'judicial', 'administrativo_clinico' => 'administrativo');
        $tipo = $predeterminados[$categoria];
    }
    if (!isset($mapa[$categoria][$tipo])) {
        return array('ok' => false, 'mensaje' => 'El subtipo no corresponde a la categoria seleccionada.');
    }
    return array('ok' => true, 'categoria' => $categoria, 'tipo' => $mapa[$categoria][$tipo]);
}

function reclasificarHiloInterConsulta($codInterConsulta, $categoria, $tipo, $codUsuario)
{
    $codInterConsulta = intval($codInterConsulta);
    $codUsuario = intval($codUsuario);
    if ($codInterConsulta <= 0) {
        return array('ok' => false, 'codigo' => 'camposvacio', 'mensaje' => 'Seleccione el hilo que desea reclasificar.');
    }
    if (!interconsultaAccesoTienePermiso($codUsuario, 'EDITARINTERCONSULTA')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para reclasificar hilos.');
    }
    $destino = interconsultaOperacionTipoCategoria($categoria, $tipo);
    if (empty($destino['ok'])) {
        return array('ok' => false, 'codigo' => 'error', 'mensaje' => $destino['mensaje']);
    }

    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $condicionAcceso = interconsultaAccesoCondicionLocalSql($codUsuario, 'ic', $mysqli);
        $campoHiloColaborador = interconsultaOperacionTablaExiste($mysqli, 'funcionario_hilo_principal')
            ? "EXISTS(SELECT 1 FROM funcionario_hilo_principal fh WHERE fh.cod_interConsultaFK=ic.cod_interConsulta AND fh.estado='activo')"
            : '0';
        $stmt = $mysqli->prepare("SELECT ic.cod_interConsulta,ic.tipo,ic.estado,ic.cod_ventaFK,
                ".$campoHiloColaborador." AS es_hilo_colaborador
            FROM interconsulta ic
            WHERE ic.cod_interConsulta=? AND ic.estado<>'inactivo' AND ".$condicionAcceso." LIMIT 1 FOR UPDATE");
        if (!$stmt) {
            throw new Exception('No se pudo validar el hilo.');
        }
        $stmt->bind_param('i', $codInterConsulta);
        $stmt->execute();
        $actual = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$actual) {
            throw new Exception('NI|El hilo no existe o pertenece a un local no autorizado.');
        }
        if (intval($actual['es_hilo_colaborador']) === 1
            && ($destino['categoria'] !== 'pagos_egresos' || $destino['tipo'] !== 'colaborador')) {
            throw new Exception('El hilo laboral es la credencial permanente del colaborador y solo puede permanecer en Pagos y Egresos.');
        }
        $tipoAnterior = normalizarTipoHiloInterConsulta($actual['tipo']);
        $categoriaAnterior = obtenerCategoriaPrincipalHilo($tipoAnterior);
        if ($tipoAnterior !== $destino['tipo']) {
            $stmt = $mysqli->prepare("UPDATE interconsulta
                SET tipo=?,cod_usuarioFK_edit=?,fecha_edit=NOW()
                WHERE cod_interConsulta=?");
            if (!$stmt) {
                throw new Exception('No se pudo preparar la reclasificacion.');
            }
            $stmt->bind_param('sii', $destino['tipo'], $codUsuario, $codInterConsulta);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('No se pudo guardar la reclasificacion.');
            }
            $stmt->close();

            $contenido = 'Se reclasifico el hilo de '.
                ($categoriaAnterior !== '' ? $categoriaAnterior : 'sin_categoria').' / '.($tipoAnterior !== '' ? $tipoAnterior : 'sin_tipo').
                ' a '.$destino['categoria'].' / '.$destino['tipo'].'.';
            $stmt = $mysqli->prepare("INSERT INTO mensaje
                (contenido,estado,cod_interConsultaFK,cod_usuarioFK,fecha_creacion)
                VALUES (?,'activo',?,?,NOW())");
            if (!$stmt) {
                throw new Exception('No se pudo registrar la trazabilidad de la reclasificacion.');
            }
            $stmt->bind_param('sii', $contenido, $codInterConsulta, $codUsuario);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('No se pudo registrar la trazabilidad de la reclasificacion.');
            }
            $stmt->close();
        }
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'datos' => array(
            'cod_interConsulta' => $codInterConsulta,
            'categoria_anterior' => $categoriaAnterior,
            'tipo_anterior' => $tipoAnterior,
            'categoria_nueva' => $destino['categoria'],
            'tipo_nuevo' => $destino['tipo'],
            'mensaje' => $tipoAnterior === $destino['tipo'] ? 'El hilo ya estaba en la clasificacion seleccionada.' : 'Hilo reclasificado con trazabilidad.'
        ));
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        $mensaje = $e->getMessage();
        $codigo = 'error';
        if (strpos($mensaje, 'NI|') === 0) {
            $codigo = 'NI';
            $mensaje = substr($mensaje, 3);
        }
        return array('ok' => false, 'codigo' => $codigo, 'mensaje' => $mensaje);
    }
}

function interconsultaOperacionRangoMetricas($fechaDesde, $fechaHasta)
{
    $hoy = date('Y-m-d');
    $fechaDesde = trim((string)$fechaDesde);
    $fechaHasta = trim((string)$fechaHasta);
    if ($fechaDesde === '') {
        $fechaDesde = $fechaHasta !== '' ? $fechaHasta : $hoy;
    }
    if ($fechaHasta === '') {
        $fechaHasta = $fechaDesde;
    }
    foreach (array($fechaDesde, $fechaHasta) as $fecha) {
        $objeto = DateTime::createFromFormat('!Y-m-d', $fecha);
        $errores = DateTime::getLastErrors();
        if (!$objeto || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
            return array('ok' => false, 'mensaje' => 'El periodo de metricas no es valido.');
        }
    }
    if ($fechaDesde > $fechaHasta) {
        $temporal = $fechaDesde;
        $fechaDesde = $fechaHasta;
        $fechaHasta = $temporal;
    }
    $esHoy = $fechaDesde === $hoy && $fechaHasta === $hoy;
    $rango = $fechaDesde === $fechaHasta
        ? date('d/m/Y', strtotime($fechaDesde))
        : date('d/m/Y', strtotime($fechaDesde)).' al '.date('d/m/Y', strtotime($fechaHasta));
    return array(
        'ok' => true,
        'desde' => $fechaDesde.' 00:00:00',
        'hasta' => date('Y-m-d H:i:s', strtotime($fechaHasta.' 00:00:00 +1 day')),
        'fecha_desde' => $fechaDesde,
        'fecha_hasta' => $fechaHasta,
        'etiqueta' => $esHoy ? 'Gestiones hoy' : 'Gestiones del periodo',
        'rango' => $rango
    );
}

function interconsultaOperacionEsMensajeManual($contenido)
{
    $contenido = strtolower(interconsultaOperacionUtf8(strip_tags((string)$contenido)));
    foreach (array(
        ' modifico', 'modifico ', ' decidio', ' aprobo', ' rechazo',
        'creo un nuevo movimiento', 'fueron unidas', 'solicito el acceso',
        'reclasifico el hilo', 'se quito la mencion', 'se agrego la mencion',
        'tarea interna', 'seguimiento programado'
    ) as $marca) {
        if (strpos($contenido, $marca) !== false) {
            return false;
        }
    }
    return true;
}

function interconsultaOperacionAgregarGestion(&$mapa, $codUsuario, $codHilo, $tipo)
{
    $codUsuario = intval($codUsuario);
    $codHilo = intval($codHilo);
    if ($codUsuario <= 0 || $codHilo <= 0) {
        return;
    }
    if (!isset($mapa[$codUsuario])) {
        $mapa[$codUsuario] = array('manual' => array(), 'seguimiento' => array(), 'cita' => array(), 'total' => array());
    }
    $mapa[$codUsuario][$tipo][$codHilo] = 1;
    $mapa[$codUsuario]['total'][$codHilo] = 1;
}

function interconsultaBuscarHiloCanonicoAgenda($mysqli, $codVenta, $codPaciente, $condicionLocal)
{
    static $cache = array();
    $codVenta = intval($codVenta);
    $codPaciente = intval($codPaciente);
    if ($codVenta <= 0 && $codPaciente <= 0) {
        return 0;
    }
    $clave = $codVenta.'|'.$codPaciente.'|'.md5($condicionLocal);
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    $relaciones = array();
    if ($codVenta > 0) {
        $relaciones[] = "ic.cod_ventaFK=".$codVenta;
        $relaciones[] = "EXISTS(SELECT 1 FROM interconsulta_paciente_venta ca_ipv
            WHERE ca_ipv.cod_interConsultaFK=ic.cod_interConsulta
              AND ca_ipv.estado='activo' AND ca_ipv.cod_ventaFK=".$codVenta." LIMIT 1)";
    }
    if ($codPaciente > 0) {
        $relaciones[] = "EXISTS(SELECT 1 FROM interconsulta_paciente ca_ip
            WHERE ca_ip.cod_interConsultaFK=ic.cod_interConsulta
              AND ca_ip.estado='activo' AND ca_ip.cod_clienteFK_principal=".$codPaciente." LIMIT 1)";
        $relaciones[] = "EXISTS(SELECT 1 FROM interconsulta_paciente_venta ca_ipv2
            INNER JOIN venta ca_vt2 ON ca_vt2.cod_venta=ca_ipv2.cod_ventaFK
            WHERE ca_ipv2.cod_interConsultaFK=ic.cod_interConsulta
              AND ca_ipv2.estado='activo' AND ca_vt2.cod_clienteFK=".$codPaciente." LIMIT 1)";
    }
    $esMaestro = $codPaciente > 0
        ? "EXISTS(SELECT 1 FROM interconsulta_paciente ca_m
            WHERE ca_m.cod_interConsultaFK=ic.cod_interConsulta AND ca_m.estado='activo'
              AND ca_m.cod_clienteFK_principal=".$codPaciente." LIMIT 1)" : '0';
    $vinculoVenta = $codVenta > 0
        ? "(ic.cod_ventaFK=".$codVenta." OR EXISTS(SELECT 1 FROM interconsulta_paciente_venta ca_v
            WHERE ca_v.cod_interConsultaFK=ic.cod_interConsulta AND ca_v.estado='activo'
              AND ca_v.cod_ventaFK=".$codVenta." LIMIT 1))" : '0';
    $sql = "SELECT ic.cod_interConsulta,
            ".$esMaestro." AS es_maestro,
            ".$vinculoVenta." AS vinculo_venta,
            (ic.estado IN ('pendiente','proceso')) AS esta_activo
        FROM interconsulta ic
        WHERE ".$condicionLocal." AND (".implode(' OR ', $relaciones).")
        ORDER BY es_maestro DESC,esta_activo DESC,vinculo_venta DESC,
            ic.fecha_creacion ASC,ic.cod_interConsulta ASC LIMIT 1";
    $resultado = $mysqli->query($sql);
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $cache[$clave] = $fila ? intval($fila['cod_interConsulta']) : 0;
    return $cache[$clave];
}

function interconsultaConstruirMapaHilosAgenda($mysqli, $agendas, $condicionLocal)
{
    $ventas = array();
    $pacientes = array();
    foreach ($agendas as $agenda) {
        $codVenta = intval($agenda['cod_ventaFK']);
        $codPaciente = intval($agenda['id_paciente']);
        if ($codVenta > 0) { $ventas[$codVenta] = 1; }
        if ($codPaciente > 0) { $pacientes[$codPaciente] = 1; }
    }

    $pacientePorVenta = array();
    foreach (array_chunk(array_keys($ventas), 500) as $grupoVentas) {
        if (count($grupoVentas) === 0) { continue; }
        $resultado = $mysqli->query("SELECT cod_venta,cod_clienteFK FROM venta WHERE cod_venta IN (".implode(',', array_map('intval', $grupoVentas)).")");
        while ($resultado && $fila = $resultado->fetch_assoc()) {
            $codVenta = intval($fila['cod_venta']);
            $codPaciente = intval($fila['cod_clienteFK']);
            $pacientePorVenta[$codVenta] = $codPaciente;
            if ($codPaciente > 0) { $pacientes[$codPaciente] = 1; }
        }
    }

    $hiloPorPaciente = array();
    foreach (array_chunk(array_keys($pacientes), 500) as $grupoPacientes) {
        if (count($grupoPacientes) === 0) { continue; }
        $sql = "SELECT ip.cod_clienteFK_principal,ic.cod_interConsulta,
                (ic.estado IN ('pendiente','proceso')) AS esta_activo,ic.fecha_creacion
            FROM interconsulta_paciente ip
            INNER JOIN interconsulta ic ON ic.cod_interConsulta=ip.cod_interConsultaFK
            WHERE ip.estado='activo'
              AND ip.cod_clienteFK_principal IN (".implode(',', array_map('intval', $grupoPacientes)).")
              AND ".$condicionLocal."
            ORDER BY ip.cod_clienteFK_principal ASC,esta_activo DESC,ic.fecha_creacion ASC,ic.cod_interConsulta ASC";
        $resultado = $mysqli->query($sql);
        while ($resultado && $fila = $resultado->fetch_assoc()) {
            $codPaciente = intval($fila['cod_clienteFK_principal']);
            if (!isset($hiloPorPaciente[$codPaciente])) {
                $hiloPorPaciente[$codPaciente] = intval($fila['cod_interConsulta']);
            }
        }
    }

    $hiloPorVenta = array();
    foreach (array_chunk(array_keys($ventas), 500) as $grupoVentas) {
        if (count($grupoVentas) === 0) { continue; }
        $listaVentas = implode(',', array_map('intval', $grupoVentas));
        $sql = "SELECT relacion.cod_venta,ic.cod_interConsulta,
                MAX(relacion.prioridad) AS prioridad,
                (ic.estado IN ('pendiente','proceso')) AS esta_activo,ic.fecha_creacion
            FROM (
                SELECT icd.cod_ventaFK AS cod_venta,icd.cod_interConsulta,2 AS prioridad
                FROM interconsulta icd WHERE icd.cod_ventaFK IN (".$listaVentas.")
                UNION ALL
                SELECT ipv.cod_ventaFK,ipv.cod_interConsultaFK,1 AS prioridad
                FROM interconsulta_paciente_venta ipv
                WHERE ipv.estado='activo' AND ipv.cod_ventaFK IN (".$listaVentas.")
            ) relacion
            INNER JOIN interconsulta ic ON ic.cod_interConsulta=relacion.cod_interConsulta
            WHERE ".$condicionLocal."
            GROUP BY relacion.cod_venta,ic.cod_interConsulta,ic.estado,ic.fecha_creacion
            ORDER BY relacion.cod_venta ASC,esta_activo DESC,prioridad DESC,ic.fecha_creacion ASC,ic.cod_interConsulta ASC";
        $resultado = $mysqli->query($sql);
        while ($resultado && $fila = $resultado->fetch_assoc()) {
            $codVenta = intval($fila['cod_venta']);
            if (!isset($hiloPorVenta[$codVenta])) {
                $hiloPorVenta[$codVenta] = intval($fila['cod_interConsulta']);
            }
        }
    }

    $mapa = array();
    foreach ($agendas as $agenda) {
        $idAgenda = intval($agenda['id_agenda']);
        $codVenta = intval($agenda['cod_ventaFK']);
        $codPaciente = intval($agenda['id_paciente']);
        if ($codPaciente <= 0 && isset($pacientePorVenta[$codVenta])) {
            $codPaciente = $pacientePorVenta[$codVenta];
        }
        if ($codPaciente > 0 && isset($hiloPorPaciente[$codPaciente])) {
            $mapa[$idAgenda] = $hiloPorPaciente[$codPaciente];
        } else if ($codVenta > 0 && isset($hiloPorVenta[$codVenta])) {
            $mapa[$idAgenda] = $hiloPorVenta[$codVenta];
        } else {
            $mapa[$idAgenda] = 0;
        }
    }
    return $mapa;
}

function obtenerMetricasGestionInterConsulta($codUsuarioSesion, $fechaDesde = '', $fechaHasta = '', $codLocal = 0)
{
    $codUsuarioSesion = intval($codUsuarioSesion);
    $codLocal = intval($codLocal);
    $rango = interconsultaOperacionRangoMetricas($fechaDesde, $fechaHasta);
    if (empty($rango['ok'])) {
        return array('ok' => false, 'codigo' => 'error', 'mensaje' => $rango['mensaje']);
    }
    $mysqli = conectar_al_servidor();
    if ($codLocal > 0 && !interconsultaAccesoUsuarioPuedeUsarLocal($codUsuarioSesion, $codLocal, $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'El local solicitado no esta autorizado.');
    }
    $condicionLocal = interconsultaOperacionCondicionLocalUsuario($codUsuarioSesion, $codLocal, 'ic', $mysqli);
    $gestiones = array();

    $sql = "SELECT m.cod_usuarioFK,m.cod_interConsultaFK,m.contenido
        FROM mensaje m INNER JOIN interconsulta ic ON ic.cod_interConsulta=m.cod_interConsultaFK
        WHERE m.estado='activo' AND m.cod_usuarioFK IS NOT NULL AND m.cod_usuarioFK<>0
          AND m.fecha_creacion>=? AND m.fecha_creacion<? AND m.fecha_creacion<=NOW()
          AND ".$condicionLocal;
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $rango['desde'], $rango['hasta']);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                if (interconsultaOperacionEsMensajeManual($fila['contenido'])) {
                    interconsultaOperacionAgregarGestion($gestiones, $fila['cod_usuarioFK'], $fila['cod_interConsultaFK'], 'manual');
                }
            }
        }
        $stmt->close();
    }

    if (function_exists('seguimientoProgramadoTablaExiste') && seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_programado')) {
        $sql = "SELECT sp.cod_usuarioFK_create,sp.cod_interConsultaFK
            FROM interconsulta_seguimiento_programado sp
            INNER JOIN interconsulta ic ON ic.cod_interConsulta=sp.cod_interConsultaFK
            WHERE sp.fecha_creacion>=? AND sp.fecha_creacion<? AND ".$condicionLocal;
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ss', $rango['desde'], $rango['hasta']);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                while ($fila = $resultado->fetch_assoc()) {
                    interconsultaOperacionAgregarGestion($gestiones, $fila['cod_usuarioFK_create'], $fila['cod_interConsultaFK'], 'seguimiento');
                }
            }
            $stmt->close();
        }
    }

    $condicionLocalAgenda = interconsultaOperacionCondicionLocalAgenda($codUsuarioSesion, $codLocal, 'ag', 'co_ag', $mysqli);
    $sql = "SELECT ag.id_agenda,ag.id_paciente,ag.cod_ventaFK,CAST(ag.creado_por AS UNSIGNED) AS cod_usuario
        FROM agenda ag
        LEFT JOIN consultorios co_ag ON co_ag.id_consultorio=ag.id_consultorio
        WHERE ag.creado_por REGEXP '^[0-9]+$' AND ag.creado_en>=? AND ag.creado_en<?
          AND ".$condicionLocalAgenda;
    $agendasPeriodo = array();
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ss', $rango['desde'], $rango['hasta']);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $agendasPeriodo[]= $fila;
            }
        }
        $stmt->close();
    }
    $mapaHilosAgenda = interconsultaConstruirMapaHilosAgenda($mysqli, $agendasPeriodo, $condicionLocal);
    foreach ($agendasPeriodo as $fila) {
        $idAgenda = intval($fila['id_agenda']);
        $hiloAgenda = isset($mapaHilosAgenda[$idAgenda]) ? intval($mapaHilosAgenda[$idAgenda]) : 0;
        if ($hiloAgenda > 0) {
            interconsultaOperacionAgregarGestion($gestiones, $fila['cod_usuario'], $hiloAgenda, 'cita');
        }
    }

    $usuarios = array();
    if (count($gestiones) > 0) {
        $idsUsuarios = array_map('intval', array_keys($gestiones));
        $sql = "SELECT u.cod_usuario,IFNULL(p.nombre_persona,CONCAT('Usuario ',u.cod_usuario)) AS nombre_persona,
                IFNULL(u.url,'') AS url_usuario
            FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
            WHERE u.cod_usuario IN (".implode(',', $idsUsuarios).") AND u.estado='Activo'";
        $resultado = $mysqli->query($sql);
        while ($resultado && $fila = $resultado->fetch_assoc()) {
            $idUsuario = intval($fila['cod_usuario']);
            $registro = $gestiones[$idUsuario];
            $usuarios[] = array(
                'cod_usuario' => $idUsuario,
                'nombre_persona' => interconsultaOperacionUtf8($fila['nombre_persona']),
                'url_usuario' => interconsultaOperacionUtf8($fila['url_usuario']),
                'total_gestiones' => count($registro['total']),
                'mensajes_manuales' => count($registro['manual']),
                'seguimientos_programados' => count($registro['seguimiento']),
                'citas_creadas' => count($registro['cita']),
                'hilos_unicos' => count($registro['total'])
            );
        }
    }
    usort($usuarios, function ($a, $b) {
        if ($a['total_gestiones'] === $b['total_gestiones']) {
            return strcasecmp($a['nombre_persona'], $b['nombre_persona']);
        }
        return $a['total_gestiones'] > $b['total_gestiones'] ? -1 : 1;
    });
    $mysqli->close();
    return array('ok' => true, 'datos' => array(
        'fecha_desde' => $rango['fecha_desde'],
        'fecha_hasta' => $rango['fecha_hasta'],
        'etiqueta' => $rango['etiqueta'],
        'rango' => $rango['rango'],
        'usuarios' => $usuarios,
        'criterio_total' => 'Un usuario suma como maximo una gestion por hilo dentro del periodo, aunque realice mas de un tipo de accion.',
        'aproximaciones_historicas' => array(
            'Los mensajes legacy no poseen fecha de guardado separada de su fecha efectiva.',
            'Las citas sin hilo directo se asignan a un unico hilo maestro activo del paciente de forma determinista.'
        )
    ));
}

function renderVistaMetricasGestionInterConsulta($datos)
{
    $usuarios = isset($datos['usuarios']) && is_array($datos['usuarios']) ? $datos['usuarios'] : array();
    $etiqueta = isset($datos['etiqueta']) ? (string)$datos['etiqueta'] : 'Gestiones hoy';
    if (count($usuarios) === 0) {
        return '<div class="interconsulta-daily-activity__empty">Sin gestiones en el periodo</div>';
    }
    $html = '';
    foreach ($usuarios as $registro) {
        $nombre = trim((string)$registro['nombre_persona']);
        $url = trim((string)$registro['url_usuario']);
        $total = intval($registro['total_gestiones']);
        $titulo = $nombre.' - '.$total.' gestion'.($total === 1 ? '' : 'es').'. Mensajes: '.
            intval($registro['mensajes_manuales']).'. Seguimientos: '.intval($registro['seguimientos_programados']).
            '. Citas: '.intval($registro['citas_creadas']).'. '.$etiqueta.'.';
        if ($url !== '') {
            $avatar = '<img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="Foto de '.htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8').'">';
        } else {
            $iniciales = function_exists('obtenerInicialesInterConsulta') ? obtenerInicialesInterConsulta($nombre, 'US') : 'US';
            $avatar = '<span>'.htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8').'</span>';
        }
        $html .= '<span class="interconsulta-daily-activity__user" title="'.htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8').'" tabindex="0" '
            .'data-total-gestiones="'.$total.'" data-mensajes-manuales="'.intval($registro['mensajes_manuales']).'" '
            .'data-seguimientos-programados="'.intval($registro['seguimientos_programados']).'" data-citas-creadas="'.intval($registro['citas_creadas']).'">'
            .'<span class="interconsulta-daily-activity__avatar">'.$avatar.'</span><strong>'.$total.'</strong></span>';
    }
    return $html;
}

function interconsultaOperacionVentasPacienteHilo($mysqli, $codInterConsulta)
{
    $codInterConsulta = intval($codInterConsulta);
    $ventas = array();
    $pacientes = array();
    $sql = "SELECT ic.cod_ventaFK,ip.cod_clienteFK_principal
        FROM interconsulta ic
        LEFT JOIN interconsulta_paciente ip ON ip.cod_interConsultaFK=ic.cod_interConsulta AND ip.estado='activo'
        WHERE ic.cod_interConsulta=".$codInterConsulta." LIMIT 1";
    $resultado = $mysqli->query($sql);
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if ($fila) {
        if (intval($fila['cod_ventaFK']) > 0) {
            $ventas[intval($fila['cod_ventaFK'])] = 1;
        }
        if (intval($fila['cod_clienteFK_principal']) > 0) {
            $pacientes[intval($fila['cod_clienteFK_principal'])] = 1;
        }
    }
    $resultado = $mysqli->query("SELECT ipv.cod_ventaFK,vt.cod_clienteFK
        FROM interconsulta_paciente_venta ipv
        INNER JOIN venta vt ON vt.cod_venta=ipv.cod_ventaFK
        WHERE ipv.cod_interConsultaFK=".$codInterConsulta." AND ipv.estado='activo'");
    while ($resultado && $fila = $resultado->fetch_assoc()) {
        $ventas[intval($fila['cod_ventaFK'])] = 1;
        if (intval($fila['cod_clienteFK']) > 0) {
            $pacientes[intval($fila['cod_clienteFK'])] = 1;
        }
    }
    return array('ventas' => array_keys($ventas), 'pacientes' => array_keys($pacientes));
}

function interconsultaOperacionFechaOrden($fecha)
{
    $marca = strtotime((string)$fecha);
    return $marca !== false ? $marca : 0;
}

function buscarTimelineUnificadoInterConsulta($codInterConsulta, $codUsuario, $limite = 30, $offset = 0, $tipoObjetivo = '', $idObjetivo = 0)
{
    $codInterConsulta = intval($codInterConsulta);
    $codUsuario = intval($codUsuario);
    $limite = max(1, min(100, intval($limite)));
    $offset = max(0, intval($offset));
    if ($codInterConsulta <= 0) {
        return array('ok' => false, 'codigo' => 'camposvacio', 'mensaje' => 'Seleccione un hilo para consultar su timeline.');
    }
    $mysqli = conectar_al_servidor();
    if (!interconsultaAccesoUsuarioPuedeAccederHilo($codInterConsulta, $codUsuario, false, $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'Usted no tiene acceso a este hilo.');
    }

    $stmt = $mysqli->prepare("SELECT fecha_creacion FROM interconsulta WHERE cod_interConsulta=? LIMIT 1");
    $stmt->bind_param('i', $codInterConsulta);
    $stmt->execute();
    $filaHilo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $fechaBase = $filaHilo && !empty($filaHilo['fecha_creacion']) ? $filaHilo['fecha_creacion'] : date('Y-m-d H:i:s');
    $items = array();
    $ultimaFechaRealMensaje = $fechaBase;

    $campoFechaRegistroMensaje = interconsultaOperacionColumnaExiste($mysqli, 'mensaje', 'fecha_registro_timeline')
        ? 'm.fecha_registro_timeline' : 'NULL';
    $stmt = $mysqli->prepare("SELECT m.cod_mensaje,m.contenido,m.url,m.tipo_adjunto,m.estado,m.cod_usuarioFK,
            m.fecha_creacion,".$campoFechaRegistroMensaje." AS fecha_registro_timeline,
            m.cod_mensaje_respuestaFK,IFNULL(p.nombre_persona,'Sistema') AS nombre_usuario,
            IFNULL(u.url,'') AS url_usuario
        FROM mensaje m
        LEFT JOIN persona p ON p.cod_persona=m.cod_usuarioFK
        LEFT JOIN usuario u ON u.cod_usuario=m.cod_usuarioFK
        WHERE m.cod_interConsultaFK=? AND m.estado='activo' AND m.cod_dictamenFK IS NULL
        ORDER BY m.cod_mensaje ASC");
    if ($stmt) {
        $stmt->bind_param('i', $codInterConsulta);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $fechaOriginal = (string)$fila['fecha_creacion'];
                $fechaRegistroExacta = trim((string)$fila['fecha_registro_timeline']);
                $esLegacy = $fechaRegistroExacta === '' && $fechaOriginal !== '' && strtotime($fechaOriginal) > time();
                if ($fechaRegistroExacta !== '') {
                    $fechaRegistro = $fechaRegistroExacta;
                    $precisionFechaRegistro = 'exacta_fecha_registro';
                } else {
                    $fechaRegistro = $esLegacy ? $ultimaFechaRealMensaje : ($fechaOriginal !== '' ? $fechaOriginal : $ultimaFechaRealMensaje);
                    $precisionFechaRegistro = $esLegacy ? 'aproximada_por_mensaje_anterior' : 'exacta_fecha_evento';
                }
                if (!$esLegacy && $fechaOriginal !== '' && $fechaRegistroExacta === '') {
                    $ultimaFechaRealMensaje = $fechaOriginal;
                } elseif ($fechaRegistroExacta !== '') {
                    $ultimaFechaRealMensaje = $fechaRegistroExacta;
                }
                $id = intval($fila['cod_mensaje']);
                $items[] = array(
                    'tipo' => 'mensaje',
                    'id' => $id,
                    'fecha_registro' => $fechaRegistro,
                    'fecha_evento' => $fechaOriginal,
                    'orden_estable' => sprintf('%010d-m-%010d', interconsultaOperacionFechaOrden($fechaRegistro), $id),
                    'es_legacy' => $esLegacy ? 1 : 0,
                    'precision_fecha_registro' => $precisionFechaRegistro,
                    'datos' => array(
                        'contenido' => interconsultaOperacionUtf8($fila['contenido']),
                        'url_adjunto' => interconsultaOperacionUtf8($fila['url']),
                        'tipo_adjunto' => interconsultaOperacionUtf8($fila['tipo_adjunto']),
                        'cod_usuario' => intval($fila['cod_usuarioFK']),
                        'nombre_usuario' => interconsultaOperacionUtf8($fila['nombre_usuario']),
                        'url_usuario' => interconsultaOperacionUtf8($fila['url_usuario']),
                        'cod_mensaje_respuesta' => intval($fila['cod_mensaje_respuestaFK']),
                        'es_programado_legacy' => $esLegacy ? 1 : 0
                    )
                );
            }
        }
        $stmt->close();
    }

    if (function_exists('seguimientoProgramadoTablaExiste') && seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_programado')) {
        $stmt = $mysqli->prepare("SELECT sp.*,IFNULL(pr.nombre_persona,'') AS nombre_responsable,
                IFNULL(pc.nombre_persona,'') AS nombre_creador,IFNULL(ur.url,'') AS url_responsable
            FROM interconsulta_seguimiento_programado sp
            LEFT JOIN persona pr ON pr.cod_persona=sp.cod_responsableFK
            LEFT JOIN persona pc ON pc.cod_persona=sp.cod_usuarioFK_create
            LEFT JOIN usuario ur ON ur.cod_usuario=sp.cod_responsableFK
            WHERE sp.cod_interConsultaFK=? ORDER BY sp.fecha_creacion ASC,sp.id_seguimiento ASC");
        if ($stmt) {
            $stmt->bind_param('i', $codInterConsulta);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                while ($fila = $resultado->fetch_assoc()) {
                    $id = intval($fila['id_seguimiento']);
                    $fechaRegistro = (string)$fila['fecha_creacion'];
                    $items[] = array(
                        'tipo' => 'tarea',
                        'id' => $id,
                        'fecha_registro' => $fechaRegistro,
                        'fecha_evento' => (string)$fila['fecha_programada'],
                        'orden_estable' => sprintf('%010d-t-%010d', interconsultaOperacionFechaOrden($fechaRegistro), $id),
                        'es_legacy' => 0,
                        'precision_fecha_registro' => 'exacta',
                        'datos' => array(
                            'id_plantilla' => intval($fila['id_plantillaFK']),
                            'motivo' => interconsultaOperacionUtf8($fila['motivo']),
                            'mensaje' => interconsultaOperacionUtf8($fila['mensaje']),
                            'estado' => interconsultaOperacionUtf8($fila['estado']),
                            'resultado' => interconsultaOperacionUtf8($fila['resultado']),
                            'cod_responsable' => intval($fila['cod_responsableFK']),
                            'nombre_responsable' => interconsultaOperacionUtf8($fila['nombre_responsable']),
                            'url_responsable' => interconsultaOperacionUtf8($fila['url_responsable']),
                            'cod_creador' => intval($fila['cod_usuarioFK_create']),
                            'nombre_creador' => interconsultaOperacionUtf8($fila['nombre_creador']),
                            'fecha_cierre' => (string)$fila['fecha_cierre']
                        )
                    );
                }
            }
            $stmt->close();
        }
    }

    $relacion = interconsultaOperacionVentasPacienteHilo($mysqli, $codInterConsulta);
    $condicionesAgenda = array();
    if (count($relacion['ventas']) > 0) {
        $condicionesAgenda[] = 'ag.cod_ventaFK IN ('.implode(',', array_map('intval', $relacion['ventas'])).')';
    }
    if (count($relacion['pacientes']) > 0) {
        $condicionesAgenda[] = 'ag.id_paciente IN ('.implode(',', array_map('intval', $relacion['pacientes'])).')';
    }
    if (count($condicionesAgenda) > 0) {
        $condicionLocal = interconsultaAccesoCondicionLocalSql($codUsuario, 'ic', $mysqli);
        $condicionLocalAgenda = interconsultaOperacionCondicionLocalAgenda($codUsuario, 0, 'ag', 'co', $mysqli);
        $sql = "SELECT ag.*,IFNULL(pp.nombre_persona,'') AS nombre_profesional,
                IFNULL(pc.nombre_persona,'') AS nombre_creador,IFNULL(uc.url,'') AS url_creador,
                IFNULL(co.nombre,'') AS nombre_consultorio,IFNULL(lo.Nombre,'') AS nombre_local
            FROM agenda ag
            LEFT JOIN persona pp ON pp.cod_persona=ag.id_profesional
            LEFT JOIN persona pc ON pc.cod_persona=CAST(ag.creado_por AS UNSIGNED)
            LEFT JOIN usuario uc ON uc.cod_usuario=CAST(ag.creado_por AS UNSIGNED)
            LEFT JOIN consultorios co ON co.id_consultorio=ag.id_consultorio
            LEFT JOIN local lo ON lo.cod_local=co.cod_localFk
            WHERE (".implode(' OR ', $condicionesAgenda).")
              AND ".$condicionLocalAgenda."
            ORDER BY ag.id_agenda ASC";
        $resultado = $mysqli->query($sql);
        while ($resultado && $fila = $resultado->fetch_assoc()) {
            $hiloCanonico = interconsultaBuscarHiloCanonicoAgenda($mysqli, $fila['cod_ventaFK'], $fila['id_paciente'], $condicionLocal);
            if ($hiloCanonico !== $codInterConsulta) {
                continue;
            }
            $fechaEvento = trim((string)$fila['fecha'].' '.(string)$fila['hora_inicio']);
            $fechaRegistro = trim((string)$fila['creado_en']);
            $esLegacy = $fechaRegistro === '';
            if ($esLegacy) {
                $fechaRegistro = $fechaBase;
            }
            $id = intval($fila['id_agenda']);
            $items[] = array(
                'tipo' => 'cita',
                'id' => $id,
                'fecha_registro' => $fechaRegistro,
                'fecha_evento' => $fechaEvento,
                'orden_estable' => sprintf('%010d-a-%010d', interconsultaOperacionFechaOrden($fechaRegistro), $id),
                'es_legacy' => $esLegacy ? 1 : 0,
                'precision_fecha_registro' => $esLegacy ? 'aproximada_por_fecha_hilo' : 'exacta',
                'datos' => array(
                    'estado' => interconsultaOperacionUtf8($fila['estado']),
                    'motivo' => interconsultaOperacionUtf8($fila['motivo']),
                    'id_paciente' => intval($fila['id_paciente']),
                    'id_profesional' => intval($fila['id_profesional']),
                    'nombre_profesional' => interconsultaOperacionUtf8($fila['nombre_profesional']),
                    'cod_creador' => intval($fila['creado_por']),
                    'nombre_creador' => interconsultaOperacionUtf8($fila['nombre_creador']),
                    'url_creador' => interconsultaOperacionUtf8($fila['url_creador']),
                    'cod_venta' => intval($fila['cod_ventaFK']),
                    'nombre_consultorio' => interconsultaOperacionUtf8($fila['nombre_consultorio']),
                    'nombre_local' => interconsultaOperacionUtf8($fila['nombre_local'])
                )
            );
        }
    }

    // Los hilos laborales muestran eventos virtuales: la asistencia sigue
    // siendo la unica fuente de verdad y no se copia como mensaje ni se expone
    // IP, ubicacion, documento o justificacion.
    if (interconsultaOperacionTablaExiste($mysqli, 'funcionario_hilo_principal')
        && interconsultaOperacionTablaExiste($mysqli, 'asistencia')) {
        $stmt = $mysqli->prepare("SELECT fh.cod_usuarioFK,IFNULL(p.nombre_persona,'Colaborador') AS nombre_persona,
                a.cod_asistencia,a.fecha,a.hora_entrada,a.hora_salida
            FROM funcionario_hilo_principal fh
            INNER JOIN asistencia a ON a.cod_usuarioFK=fh.cod_usuarioFK
            LEFT JOIN persona p ON p.cod_persona=fh.cod_usuarioFK
            WHERE fh.cod_interConsultaFK=? AND fh.estado='activo'
            ORDER BY a.fecha ASC,a.cod_asistencia ASC");
        if ($stmt) {
            $stmt->bind_param('i', $codInterConsulta);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                while ($fila = $resultado->fetch_assoc()) {
                    $idAsistencia = intval($fila['cod_asistencia']);
                    $fechaBaseAsistencia = substr((string)$fila['fecha'], 0, 10);
                    $horaEntrada = substr((string)$fila['hora_entrada'], 0, 8);
                    $horaSalida = substr((string)$fila['hora_salida'], 0, 8);
                    if ($fechaBaseAsistencia !== '' && $horaEntrada !== '') {
                        $fechaEntrada = $fechaBaseAsistencia.' '.$horaEntrada;
                        $items[] = array(
                            'tipo' => 'asistencia',
                            'id' => ($idAsistencia * 2),
                            'fecha_registro' => $fechaEntrada,
                            'fecha_evento' => $fechaEntrada,
                            'orden_estable' => sprintf('%010d-e-%010d', interconsultaOperacionFechaOrden($fechaEntrada), $idAsistencia),
                            'es_legacy' => 0,
                            'precision_fecha_registro' => 'exacta',
                            'datos' => array(
                                'evento' => 'entrada',
                                'cod_asistencia' => $idAsistencia,
                                'cod_usuario' => intval($fila['cod_usuarioFK']),
                                'nombre_usuario' => interconsultaOperacionUtf8($fila['nombre_persona'])
                            )
                        );
                    }
                    if ($fechaBaseAsistencia !== '' && $horaSalida !== '') {
                        $fechaSalida = $fechaBaseAsistencia.' '.$horaSalida;
                        if ($horaEntrada !== '' && strcmp($horaSalida, $horaEntrada) < 0) {
                            $marcaSalida = strtotime($fechaSalida.' +1 day');
                            if ($marcaSalida !== false) { $fechaSalida = date('Y-m-d H:i:s', $marcaSalida); }
                        }
                        $items[] = array(
                            'tipo' => 'asistencia',
                            'id' => ($idAsistencia * 2) + 1,
                            'fecha_registro' => $fechaSalida,
                            'fecha_evento' => $fechaSalida,
                            'orden_estable' => sprintf('%010d-s-%010d', interconsultaOperacionFechaOrden($fechaSalida), $idAsistencia),
                            'es_legacy' => 0,
                            'precision_fecha_registro' => 'exacta',
                            'datos' => array(
                                'evento' => 'salida',
                                'cod_asistencia' => $idAsistencia,
                                'cod_usuario' => intval($fila['cod_usuarioFK']),
                                'nombre_usuario' => interconsultaOperacionUtf8($fila['nombre_persona'])
                            )
                        );
                    }
                }
            }
            $stmt->close();
        }
    }

    usort($items, function ($a, $b) {
        $fechaA = interconsultaOperacionFechaOrden($a['fecha_registro']);
        $fechaB = interconsultaOperacionFechaOrden($b['fecha_registro']);
        if ($fechaA === $fechaB) {
            return strcmp($a['orden_estable'], $b['orden_estable']);
        }
        return $fechaA < $fechaB ? -1 : 1;
    });
    $total = count($items);
    $itemsDesc = array_reverse($items);
    $offsetObjetivo = -1;
    $tipoObjetivo = trim((string)$tipoObjetivo);
    $idObjetivo = intval($idObjetivo);
    if ($tipoObjetivo !== '' && $idObjetivo > 0) {
        foreach ($itemsDesc as $indiceObjetivo => $itemObjetivo) {
            if ($itemObjetivo['tipo'] === $tipoObjetivo && intval($itemObjetivo['id']) === $idObjetivo) {
                $offsetObjetivo = intval($indiceObjetivo);
                break;
            }
        }
    }
    $pagina = array_slice($itemsDesc, $offset, $limite);
    $pagina = array_reverse($pagina);
    $siguiente = $offset + count($pagina);
    $mysqli->close();
    return array('ok' => true, 'datos' => array(
        'items' => $pagina,
        'total' => $total,
        'offset_siguiente' => $siguiente,
        'hay_mas' => $siguiente < $total ? 1 : 0,
        'offset_objetivo' => $offsetObjetivo,
        'criterio_orden' => 'fecha_registro',
        'nota_legacy' => 'Solo los mensajes programados anteriores a la migracion que no guardaban fecha de alta se ubican con una aproximacion estable y se identifican con es_legacy=1.'
    ));
}

?>

<?php

date_default_timezone_set('America/Asuncion');

function centroLegajoPagareEstructuraDisponible($mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $disponible = centroFacturaTablaExiste($mysqli, 'centro_legajo_pagare_solicitud')
        && centroFacturaTablaExiste($mysqli, 'centro_legajo_pagare_solicitud_evento')
        && centroFacturaColumnaExiste($mysqli, 'centro_legajo_pagare_solicitud', 'evidencia_nombre_fisico');
    if ($disponible) {
        $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.columns
            WHERE table_schema=DATABASE() AND table_name='centro_legajo_documento'
              AND column_name='estado_fisico' AND column_type LIKE '%''devuelto_cliente''%' LIMIT 1");
        if (!$stmt || !$stmt->execute() || $stmt->get_result()->num_rows < 1) $disponible = false;
        if ($stmt) $stmt->close();
    }
    if ($cerrar) $mysqli->close();
    return $disponible;
}

function centroLegajoPagareErrorEstructura()
{
    return array('ok' => false, 'codigo' => 'estructura_pagares',
        'mensaje' => 'La estructura de solicitudes de devolucion de pagares no esta instalada.');
}

function centroLegajoPagarePuedeVer($codUsuario)
{
    return centroLegajoPuedeVer($codUsuario);
}

function centroLegajoPagarePuedeGestionar($codUsuario)
{
    return centroLegajoTienePermiso($codUsuario, 'GESTIONARLEGAJOSVENTA');
}

function centroLegajoPagarePuedeAprobar($codUsuario)
{
    return centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS');
}

function centroLegajoPagarePuedeOperarUbicacion($codUsuario, $documento, $mysqli)
{
    $codLocal = isset($documento['cod_local_ubicacionFK']) ? intval($documento['cod_local_ubicacionFK']) : 0;
    return $codLocal > 0 && centroFacturaPuedeUsarLocal($codUsuario, $codLocal, $mysqli);
}

function centroLegajoPagareEstadosAbiertos()
{
    return array('solicitada', 'aprobada', 'esperando_recepcion', 'preparada');
}

function centroLegajoPagareSolicitudRaw($mysqli, $idSolicitud, $bloquear = false)
{
    $idSolicitud = intval($idSolicitud);
    $stmt = $mysqli->prepare('SELECT * FROM centro_legajo_pagare_solicitud WHERE id_solicitud=? LIMIT 1'.($bloquear ? ' FOR UPDATE' : ''));
    if (!$stmt) return array();
    $stmt->bind_param('i', $idSolicitud);
    if (!$stmt->execute()) { $stmt->close(); return array(); }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroLegajoPagareDocumentoRaw($mysqli, $idDocumento, $bloquear = false)
{
    $idDocumento = intval($idDocumento);
    $stmt = $mysqli->prepare('SELECT * FROM centro_legajo_documento WHERE id_documento=? LIMIT 1'.($bloquear ? ' FOR UPDATE' : ''));
    if (!$stmt) return array();
    $stmt->bind_param('i', $idDocumento);
    if (!$stmt->execute()) { $stmt->close(); return array(); }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroLegajoPagareLoteDocumento($mysqli, $idDocumento)
{
    $idDocumento = intval($idDocumento);
    $stmt = $mysqli->prepare("SELECT lo.id_lote,lo.codigo_lote,lo.estado,lo.cod_local_origenFK,lo.cod_local_destinoFK,
        lo.cod_usuario_transportistaFK,ld.estado AS estado_detalle_lote,ld.fecha_estado
      FROM centro_legajo_lote_detalle ld
      INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
      WHERE ld.id_documentoFK=? AND ld.estado<>'retirado' AND lo.estado<>'anulado'
      ORDER BY ld.id_lote_detalle DESC LIMIT 1");
    if (!$stmt) return array();
    $stmt->bind_param('i', $idDocumento);
    if (!$stmt->execute()) { $stmt->close(); return array(); }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroLegajoPagareSnapshot($documento, $lote)
{
    return array(
        'estado_fisico' => isset($documento['estado_fisico']) ? (string)$documento['estado_fisico'] : '',
        'cod_local' => !empty($documento['cod_local_ubicacionFK']) ? intval($documento['cod_local_ubicacionFK']) : null,
        'ubicacion' => isset($documento['ubicacion_fisica']) && trim((string)$documento['ubicacion_fisica']) !== ''
            ? (string)$documento['ubicacion_fisica'] : null,
        'id_lote' => !empty($lote['id_lote']) ? intval($lote['id_lote']) : null,
        'codigo_lote' => !empty($lote['codigo_lote']) ? (string)$lote['codigo_lote'] : null,
        'estado_lote' => !empty($lote['estado']) ? (string)$lote['estado'] : null
    );
}

function centroLegajoPagareRegistrarEvento($mysqli, $solicitud, $tipoEvento, $estadoAnterior, $estadoNuevo, $detalle, $codUsuario, $documento, $lote)
{
    $snapshot = centroLegajoPagareSnapshot($documento, $lote);
    $tipoEvento = centroFacturaTextoBaseDatos($tipoEvento, 50);
    $estadoAnterior = centroFacturaTextoBaseDatos($estadoAnterior, 30);
    $estadoNuevo = centroFacturaTextoBaseDatos($estadoNuevo, 30);
    $detalle = centroFacturaTextoBaseDatos($detalle, 3000, true);
    $ubicacion = $snapshot['ubicacion'] === null ? null : centroFacturaTextoBaseDatos($snapshot['ubicacion'], 255);
    $codigoLote = $snapshot['codigo_lote'] === null ? null : centroFacturaTextoBaseDatos($snapshot['codigo_lote'], 40);
    $estadoLote = $snapshot['estado_lote'] === null ? null : centroFacturaTextoBaseDatos($snapshot['estado_lote'], 30);
    $stmt = $mysqli->prepare("INSERT INTO centro_legajo_pagare_solicitud_evento
        (id_solicitudFK,id_documentoFK,cod_ventaFK,tipo_evento,estado_anterior,estado_nuevo,
         estado_fisico_snapshot,cod_local_ubicacion_snapshotFK,ubicacion_fisica_snapshot,
         id_lote_snapshotFK,codigo_lote_snapshot,estado_lote_snapshot,detalle,cod_usuario_actorFK)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) return false;
    $parametros = array(
        intval($solicitud['id_solicitud']), intval($solicitud['id_documentoFK']), intval($solicitud['cod_ventaFK']),
        $tipoEvento, $estadoAnterior, $estadoNuevo, $snapshot['estado_fisico'], $snapshot['cod_local'], $ubicacion,
        $snapshot['id_lote'], $codigoLote, $estadoLote, $detalle, intval($codUsuario)
    );
    centroFacturaBind($stmt, 'iiissssisisssi', $parametros);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centroLegajoPagareVentaValida($venta, $documento, $admitirDevuelto = false)
{
    if (!$venta || !$documento || (string)$documento['tipo_documento'] !== 'pagare') {
        return 'La solicitud no corresponde a un pagare de venta valido.';
    }
    if (strtoupper(trim((string)$venta['tipo_venta'])) !== 'CREDITO') {
        return 'La devolucion de pagare solo corresponde a ventas a credito.';
    }
    if (intval($venta['es_anulada'])) return 'La venta esta anulada y no admite esta operacion.';
    if (!$admitirDevuelto && (string)$documento['estado_fisico'] === 'devuelto_cliente') {
        return 'El pagare ya fue devuelto al cliente.';
    }
    return '';
}

function centroLegajoPagareBloquearOperacion($mysqli, $idSolicitud)
{
    $referencia = centroLegajoPagareSolicitudRaw($mysqli, $idSolicitud, false);
    if (!$referencia) throw new Exception('La solicitud no existe.');
    $codVenta = intval($referencia['cod_ventaFK']);
    $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM venta WHERE cod_venta=? LIMIT 1 FOR UPDATE');
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute() || !$stmt->get_result()->fetch_assoc()) { $stmt->close(); throw new Exception('La venta asociada ya no existe.'); }
    $stmt->close();
    $documento = centroLegajoPagareDocumentoRaw($mysqli, intval($referencia['id_documentoFK']), true);
    $solicitud = centroLegajoPagareSolicitudRaw($mysqli, $idSolicitud, true);
    if (!$solicitud || !$documento || intval($documento['cod_ventaFK']) !== $codVenta
        || intval($solicitud['id_documentoFK']) !== intval($documento['id_documento'])) {
        throw new Exception('La solicitud ya no coincide con el documento del legajo.');
    }
    $venta = centroLegajoVentaRaw($mysqli, $codVenta);
    if (!$venta) throw new Exception('No se pudo recuperar la venta asociada.');
    return array('solicitud' => $solicitud, 'documento' => $documento, 'venta' => $venta,
        'lote' => centroLegajoPagareLoteDocumento($mysqli, $documento['id_documento']));
}

function centroLegajoPagareSolicitudCompleta($mysqli, $idSolicitud)
{
    $idSolicitud = intval($idSolicitud);
    $stmt = $mysqli->prepare("SELECT s.*,d.tipo_documento,d.estado_documental,d.estado_fisico,d.cod_local_ubicacionFK,
        d.ubicacion_fisica,d.version_registro AS version_documento,v.fecha_venta,v.TipoVenta AS tipo_venta,v.cod_local,
        GREATEST(0,IFNULL(v.total_venta,0)-IFNULL(v.descuento,0)) AS importe_venta,
        p.nombre_persona AS titular,COALESCE(NULLIF(TRIM(c.rut_cliente),''),NULLIF(TRIM(c.ci_cliente),''),'') AS documento_cliente,
        lo.Nombre AS nombre_local_origen,lu.Nombre AS nombre_local_ubicacion,
        ps.nombre_persona AS usuario_solicita,pa.nombre_persona AS usuario_aprueba,pp.nombre_persona AS usuario_prepara,
        pe.nombre_persona AS usuario_entrega,pr.nombre_persona AS usuario_rechaza,pc.nombre_persona AS usuario_cancela
      FROM centro_legajo_pagare_solicitud s
      INNER JOIN centro_legajo_documento d ON d.id_documento=s.id_documentoFK
      INNER JOIN venta v ON v.cod_venta=s.cod_ventaFK
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local lo ON lo.cod_local=v.cod_local
      LEFT JOIN local lu ON lu.cod_local=d.cod_local_ubicacionFK
      LEFT JOIN persona ps ON ps.cod_persona=s.cod_usuario_solicitaFK
      LEFT JOIN persona pa ON pa.cod_persona=s.cod_usuario_apruebaFK
      LEFT JOIN persona pp ON pp.cod_persona=s.cod_usuario_preparaFK
      LEFT JOIN persona pe ON pe.cod_persona=s.cod_usuario_entregaFK
      LEFT JOIN persona pr ON pr.cod_persona=s.cod_usuario_rechazaFK
      LEFT JOIN persona pc ON pc.cod_persona=s.cod_usuario_cancelaFK
      WHERE s.id_solicitud=? LIMIT 1");
    if (!$stmt) return array();
    $stmt->bind_param('i', $idSolicitud);
    if (!$stmt->execute()) { $stmt->close(); return array(); }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroLegajoPagareDecorarSolicitud($fila, $mysqli, $codUsuario)
{
    $fila = centroFacturaFilaUtf8($fila);
    $lote = centroLegajoPagareLoteDocumento($mysqli, intval($fila['id_documentoFK']));
    $loteUtf8 = centroFacturaFilaUtf8($lote);
    $fila['codigo_documento'] = centroLegajoCodigoDocumento($fila['cod_ventaFK'], 'pagare');
    $fila['lote_actual'] = $loteUtf8;
    $fila['estado_fisico_actual'] = isset($fila['estado_fisico']) ? $fila['estado_fisico'] : '';
    $fila['ubicacion_fisica_actual'] = isset($fila['ubicacion_fisica']) ? $fila['ubicacion_fisica'] : '';
    $fila['codigo_lote_actual'] = isset($loteUtf8['codigo_lote']) ? $loteUtf8['codigo_lote'] : '';
    $fila['estado_lote_actual'] = isset($loteUtf8['estado']) ? $loteUtf8['estado'] : '';
    $fila['documento'] = isset($fila['documento_cliente']) ? $fila['documento_cliente'] : '';
    $fila['nombre_local'] = isset($fila['nombre_local_origen']) ? $fila['nombre_local_origen'] : '';
    $fila['usuario_solicitud'] = isset($fila['usuario_solicita']) ? $fila['usuario_solicita'] : '';
    $fila['usuario_aprobacion'] = isset($fila['usuario_aprueba']) ? $fila['usuario_aprueba'] : '';
    $fila['usuario_preparacion'] = isset($fila['usuario_prepara']) ? $fila['usuario_prepara'] : '';
    $fila['usuario_rechazo'] = isset($fila['usuario_rechaza']) ? $fila['usuario_rechaza'] : '';
    $fila['ubicacion_actual'] = trim((string)$fila['ubicacion_fisica']);
    if ($fila['ubicacion_actual'] === '') {
        if (in_array($fila['estado_fisico'], array('en_lote','pendiente_custodia','en_transito'), true) && !empty($lote['codigo_lote'])) {
            $fila['ubicacion_actual'] = $lote['codigo_lote'].' - '.str_replace('_', ' ', $lote['estado']);
        } elseif (!empty($fila['nombre_local_ubicacion'])) {
            $fila['ubicacion_actual'] = $fila['nombre_local_ubicacion'];
        } else {
            $fila['ubicacion_actual'] = 'Ubicacion pendiente de confirmar';
        }
    }
    $estado = (string)$fila['estado'];
    $gestiona = centroLegajoPagarePuedeGestionar($codUsuario);
    $admin = centroLegajoPagarePuedeAprobar($codUsuario);
    $documentoDisponible = in_array($fila['estado_documental'], array('disponible','validado'), true);
    $documentoEnSucursal = in_array($fila['estado_fisico'], array('en_sucursal','recibido'), true);
    $operaUbicacion = centroLegajoPagarePuedeOperarUbicacion($codUsuario, $fila, $mysqli);
    $fila['puede_aprobar'] = $admin && $estado === 'solicitada' ? 1 : 0;
    $fila['puede_rechazar'] = $admin && $estado === 'solicitada' ? 1 : 0;
    $fila['puede_preparar'] = $gestiona && $operaUbicacion && in_array($estado, array('aprobada','esperando_recepcion'), true)
        && $documentoDisponible && $documentoEnSucursal ? 1 : 0;
    $fila['puede_entregar'] = $gestiona && $operaUbicacion && $estado === 'preparada' && $documentoDisponible && $documentoEnSucursal ? 1 : 0;
    $fila['puede_cancelar'] = $gestiona && in_array($estado, centroLegajoPagareEstadosAbiertos(), true) ? 1 : 0;
    $fila['evidencia_disponible'] = !empty($fila['evidencia_nombre_fisico']) ? 1 : 0;
    $fila['tiene_evidencia'] = $fila['evidencia_disponible'];
    unset($fila['evidencia_nombre_fisico']);
    return $fila;
}

function centroLegajoPagareListar($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroLegajoPagarePuedeVer($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar solicitudes de pagares.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $limite = max(1, min(150, intval($limite)));
    $offset = max(0, intval($offset));
    $filtros = (array)$filtros;
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    if (!$contexto) { $mysqli->close(); return array('ok' => false, 'codigo' => 'contexto', 'mensaje' => 'No se pudo determinar el local del usuario.'); }
    $condiciones = array("d.tipo_documento='pagare'");
    $tipos = '';
    $parametros = array();
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $local = intval($contexto['cod_localFK']);
        $condiciones[] = "(v.cod_local=? OR d.cod_local_ubicacionFK=? OR s.cod_local_ubicacion_snapshotFK=? OR EXISTS (
            SELECT 1 FROM centro_legajo_lote_detalle lda
            INNER JOIN centro_legajo_lote loa ON loa.id_lote=lda.id_loteFK
            WHERE lda.id_documentoFK=d.id_documento AND lda.estado<>'retirado' AND loa.estado<>'anulado'
              AND (loa.cod_local_origenFK=? OR loa.cod_local_destinoFK=? OR loa.cod_usuario_transportistaFK=?)))";
        $tipos .= 'iiiiii';
        $parametros[] = $local; $parametros[] = $local; $parametros[] = $local;
        $parametros[] = $local; $parametros[] = $local; $parametros[] = intval($codUsuario);
    } elseif (!empty($filtros['cod_local'])) {
        $local = intval($filtros['cod_local']);
        $condiciones[] = '(v.cod_local=? OR d.cod_local_ubicacionFK=? OR s.cod_local_ubicacion_snapshotFK=?)';
        $tipos .= 'iii'; $parametros[] = $local; $parametros[] = $local; $parametros[] = $local;
    }
    $estado = isset($filtros['estado']) ? trim((string)$filtros['estado']) : '';
    $estados = array('solicitada','aprobada','esperando_recepcion','preparada','entregada','rechazada','cancelada');
    if (in_array($estado, $estados, true)) {
        $condiciones[] = 's.estado=?'; $tipos .= 's'; $parametros[] = $estado;
    }
    if (!empty($filtros['busqueda'])) {
        $patron = '%'.centroFacturaTextoBaseDatos($filtros['busqueda'], 100).'%';
        $condiciones[] = '(s.codigo_solicitud LIKE ? OR s.solicitante_nombre LIKE ? OR s.solicitante_documento LIKE ?
            OR s.receptor_nombre LIKE ? OR p.nombre_persona LIKE ? OR c.rut_cliente LIKE ? OR c.ci_cliente LIKE ?
            OR CAST(s.cod_ventaFK AS CHAR) LIKE ?)';
        $tipos .= 'ssssssss';
        for ($i = 0; $i < 8; $i++) $parametros[] = $patron;
    }
    foreach (array('fecha_desde' => '>=', 'fecha_hasta' => '<=') as $clave => $operador) {
        if (!empty($filtros[$clave]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$filtros[$clave])) {
            $condiciones[] = 'DATE(s.fecha_solicitud)'.$operador.'?';
            $tipos .= 's'; $parametros[] = $filtros[$clave];
        }
    }
    $sql = "SELECT s.*,d.tipo_documento,d.estado_documental,d.estado_fisico,d.cod_local_ubicacionFK,
        d.ubicacion_fisica,v.fecha_venta,v.TipoVenta AS tipo_venta,v.cod_local,
        GREATEST(0,IFNULL(v.total_venta,0)-IFNULL(v.descuento,0)) AS importe_venta,
        p.nombre_persona AS titular,COALESCE(NULLIF(TRIM(c.rut_cliente),''),NULLIF(TRIM(c.ci_cliente),''),'') AS documento_cliente,
        lo.Nombre AS nombre_local_origen,lu.Nombre AS nombre_local_ubicacion,
        ps.nombre_persona AS usuario_solicita,pa.nombre_persona AS usuario_aprueba,pp.nombre_persona AS usuario_prepara,
        pe.nombre_persona AS usuario_entrega,pr.nombre_persona AS usuario_rechaza,pc.nombre_persona AS usuario_cancela
      FROM centro_legajo_pagare_solicitud s
      INNER JOIN centro_legajo_documento d ON d.id_documento=s.id_documentoFK
      INNER JOIN venta v ON v.cod_venta=s.cod_ventaFK
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local lo ON lo.cod_local=v.cod_local
      LEFT JOIN local lu ON lu.cod_local=d.cod_local_ubicacionFK
      LEFT JOIN persona ps ON ps.cod_persona=s.cod_usuario_solicitaFK
      LEFT JOIN persona pa ON pa.cod_persona=s.cod_usuario_apruebaFK
      LEFT JOIN persona pp ON pp.cod_persona=s.cod_usuario_preparaFK
      LEFT JOIN persona pe ON pe.cod_persona=s.cod_usuario_entregaFK
      LEFT JOIN persona pr ON pr.cod_persona=s.cod_usuario_rechazaFK
      LEFT JOIN persona pc ON pc.cod_persona=s.cod_usuario_cancelaFK
      WHERE ".implode(' AND ', $condiciones)." ORDER BY s.fecha_solicitud DESC,s.id_solicitud DESC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo preparar el listado: '.$error); }
    centroFacturaBind($stmt, $tipos, $parametros);
    if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo consultar el listado de solicitudes.'); }
    $registros = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $registros[] = centroLegajoPagareDecorarSolicitud($fila, $mysqli, $codUsuario);
    $stmt->close();
    $total = count($registros);
    $pagina = array_slice($registros, $offset, $limite);
    $mysqli->close();
    return array('ok' => true, 'registros' => $pagina, 'total' => $total, 'limite' => $limite, 'offset' => $offset);
}

function centroLegajoPagareDetalle($idSolicitud, $codUsuario)
{
    if (!centroLegajoPagarePuedeVer($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar la solicitud.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $mysqli = conectar_al_servidor();
    $fila = centroLegajoPagareSolicitudCompleta($mysqli, $idSolicitud);
    if (!$fila) { $mysqli->close(); return array('ok' => false, 'codigo' => 'solicitud', 'mensaje' => 'La solicitud no existe.'); }
    $venta = centroLegajoVentaRaw($mysqli, intval($fila['cod_ventaFK']));
    if (!$venta || !centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) {
        $mysqli->close(); return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'La solicitud pertenece a otro local.');
    }
    $solicitud = centroLegajoPagareDecorarSolicitud($fila, $mysqli, $codUsuario);
    $eventos = array();
    $stmt = $mysqli->prepare("SELECT e.*,p.nombre_persona AS usuario_actor,l.Nombre AS nombre_local_snapshot
        FROM centro_legajo_pagare_solicitud_evento e
        LEFT JOIN persona p ON p.cod_persona=e.cod_usuario_actorFK
        LEFT JOIN local l ON l.cod_local=e.cod_local_ubicacion_snapshotFK
        WHERE e.id_solicitudFK=? ORDER BY e.id_evento ASC");
    $idSolicitud = intval($idSolicitud);
    if ($stmt) {
        $stmt->bind_param('i', $idSolicitud);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($evento = $resultado->fetch_assoc()) $eventos[] = centroFacturaFilaUtf8($evento);
        }
        $stmt->close();
    }
    $mysqli->close();
    return array('ok' => true, 'solicitud' => $solicitud, 'eventos' => $eventos);
}

function centroLegajoPagareSolicitudActivaVenta($codVenta, $codUsuario, $mysqli = null)
{
    if (!centroLegajoPagarePuedeVer($codUsuario) || !centroLegajoPagareEstructuraDisponible($mysqli)) return array();
    $cerrar = false;
    if (!$mysqli) { $mysqli = conectar_al_servidor(); $cerrar = true; }
    $venta = centroLegajoVentaRaw($mysqli, $codVenta);
    if (!$venta || !centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) { if ($cerrar) $mysqli->close(); return array(); }
    $stmt = $mysqli->prepare("SELECT s.*,d.tipo_documento,d.estado_documental,d.estado_fisico,d.cod_local_ubicacionFK,
        d.ubicacion_fisica,v.fecha_venta,v.TipoVenta AS tipo_venta,v.cod_local,p.nombre_persona AS titular,
        COALESCE(NULLIF(TRIM(c.rut_cliente),''),NULLIF(TRIM(c.ci_cliente),''),'') AS documento_cliente,
        lo.Nombre AS nombre_local_origen,lu.Nombre AS nombre_local_ubicacion
      FROM centro_legajo_pagare_solicitud s
      INNER JOIN centro_legajo_documento d ON d.id_documento=s.id_documentoFK
      INNER JOIN venta v ON v.cod_venta=s.cod_ventaFK
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local lo ON lo.cod_local=v.cod_local
      LEFT JOIN local lu ON lu.cod_local=d.cod_local_ubicacionFK
      WHERE s.cod_ventaFK=? AND s.solicitud_abierta=1 ORDER BY s.id_solicitud DESC LIMIT 1");
    $codVenta = intval($codVenta);
    $fila = array();
    if ($stmt) {
        $stmt->bind_param('i', $codVenta);
        if ($stmt->execute()) $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if ($fila) $fila = centroLegajoPagareDecorarSolicitud($fila, $mysqli, $codUsuario);
    if ($cerrar) $mysqli->close();
    return $fila ? $fila : array();
}

function centroLegajoPagareCrear($codVenta, $datos, $codUsuario)
{
    if (!centroLegajoPagarePuedeGestionar($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para crear solicitudes de pagares.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $datos = (array)$datos;
    $solicitante = centroFacturaTextoBaseDatos(isset($datos['solicitante_nombre']) ? $datos['solicitante_nombre'] : '', 255);
    $documentoSolicitante = centroFacturaTextoBaseDatos(isset($datos['solicitante_documento']) ? $datos['solicitante_documento'] : '', 45);
    $motivo = centroFacturaTextoBaseDatos(isset($datos['motivo_solicitud']) ? $datos['motivo_solicitud'] : '', 3000, true);
    if ($solicitante === '' || $documentoSolicitante === '' || $motivo === '') {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'Identifique al solicitante con nombre y documento, e indique el motivo.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $codVenta = intval($codVenta);
        $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM venta WHERE cod_venta=? LIMIT 1 FOR UPDATE');
        $stmt->bind_param('i', $codVenta);
        if (!$stmt->execute() || !$stmt->get_result()->fetch_assoc()) { $stmt->close(); throw new Exception('La venta no existe.'); }
        $stmt->close();
        $venta = centroLegajoVentaRaw($mysqli, $codVenta);
        if (!$venta || !centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) throw new Exception('No puede solicitar el pagare de otro local.');
        if (!centroLegajoAsegurarDocumentosVenta($mysqli, $venta, $codUsuario)) throw new Exception('No se pudo preparar el legajo documental.');
        $stmt = centroLegajoPrepararEscritura($mysqli, "SELECT * FROM centro_legajo_documento
            WHERE cod_ventaFK=? AND tipo_documento='pagare' LIMIT 1 FOR UPDATE");
        $stmt->bind_param('i', $codVenta);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo validar el pagare.'); }
        $documento = $stmt->get_result()->fetch_assoc(); $stmt->close();
        $error = centroLegajoPagareVentaValida($venta, $documento, false);
        if ($error !== '') throw new Exception($error);
        if (!in_array($documento['estado_documental'], array('disponible','validado'), true)
            || !in_array($documento['estado_fisico'], array('en_sucursal','en_lote','pendiente_custodia','en_transito','recibido'), true)) {
            throw new Exception('El pagare debe estar confirmado y localizado antes de solicitar su devolucion.');
        }
        $idDocumento = intval($documento['id_documento']);
        $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT id_solicitud,codigo_solicitud FROM centro_legajo_pagare_solicitud
            WHERE id_documentoFK=? AND solicitud_abierta=1 LIMIT 1 FOR UPDATE');
        $stmt->bind_param('i', $idDocumento);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo validar si ya existe una solicitud.'); }
        $abierta = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($abierta) throw new Exception('El pagare ya tiene la solicitud activa '.centroFacturaValorUtf8($abierta['codigo_solicitud']).'.');
        $lote = centroLegajoPagareLoteDocumento($mysqli, $idDocumento);
        $snapshot = centroLegajoPagareSnapshot($documento, $lote);
        try {
            $sufijoTemporal = bin2hex(random_bytes(6));
        } catch (Exception $e) {
            $sufijoTemporal = substr(sha1(uniqid((string)mt_rand(), true)), 0, 12);
        }
        $temporal = 'TMP-SPG-'.date('YmdHis').'-'.$sufijoTemporal;
        $ahora = date('Y-m-d H:i:s');
        $ubicacion = $snapshot['ubicacion'] === null ? null : centroFacturaTextoBaseDatos($snapshot['ubicacion'], 255);
        $codigoLote = $snapshot['codigo_lote'] === null ? null : centroFacturaTextoBaseDatos($snapshot['codigo_lote'], 40);
        $estadoLote = $snapshot['estado_lote'] === null ? null : centroFacturaTextoBaseDatos($snapshot['estado_lote'], 30);
        $documentoSolicitanteDb = $documentoSolicitante === '' ? null : $documentoSolicitante;
        $stmt = centroLegajoPrepararEscritura($mysqli, "INSERT INTO centro_legajo_pagare_solicitud
            (codigo_solicitud,id_documentoFK,cod_ventaFK,estado,solicitante_nombre,solicitante_documento,motivo_solicitud,
             estado_fisico_snapshot,cod_local_ubicacion_snapshotFK,ubicacion_fisica_snapshot,id_lote_snapshotFK,
             codigo_lote_snapshot,estado_lote_snapshot,cod_usuario_solicitaFK,cod_usuarioFK_update,fecha_actualizacion)
            VALUES (?,?,?,'solicitada',?,?,?,?,?,?,?,?,?,?,?,?)");
        $parametros = array($temporal, $idDocumento, $codVenta, $solicitante, $documentoSolicitanteDb, $motivo,
            $snapshot['estado_fisico'], $snapshot['cod_local'], $ubicacion, $snapshot['id_lote'], $codigoLote, $estadoLote,
            intval($codUsuario), intval($codUsuario), $ahora);
        centroFacturaBind($stmt, 'siissssisissiis', $parametros);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo crear la solicitud; verifique que no exista otra activa.'); }
        $idSolicitud = intval($stmt->insert_id); $stmt->close();
        $codigo = 'SPG-'.date('Ymd').'-'.str_pad((string)$idSolicitud, 6, '0', STR_PAD_LEFT);
        $stmt = centroLegajoPrepararEscritura($mysqli, 'UPDATE centro_legajo_pagare_solicitud SET codigo_solicitud=? WHERE id_solicitud=?');
        $stmt->bind_param('si', $codigo, $idSolicitud);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo asignar el codigo de solicitud.'); }
        $stmt->close();
        $solicitud = centroLegajoPagareSolicitudRaw($mysqli, $idSolicitud, false);
        if (!centroLegajoPagareRegistrarEvento($mysqli, $solicitud, 'crear_solicitud', '', 'solicitada', $motivo, $codUsuario, $documento, $lote)) {
            throw new Exception('No se pudo auditar la solicitud.');
        }
        if (!$mysqli->commit()) throw new Exception('No se pudo confirmar la solicitud.');
        $mysqli->close();
        return array('ok' => true, 'id_solicitud' => $idSolicitud, 'codigo_solicitud' => $codigo,
            'codigo_documento' => centroLegajoCodigoDocumento($codVenta, 'pagare'), 'estado' => 'solicitada');
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'solicitud_pagare', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoPagareResolver($idSolicitud, $aprobar, $observacion, $codUsuario)
{
    if (!centroLegajoPagarePuedeAprobar($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'La aprobacion o rechazo requiere permiso administrativo superior.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $observacion = centroFacturaTextoBaseDatos($observacion, 3000, true);
    if (!$aprobar && $observacion === '') return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Indique el motivo del rechazo.');
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    try {
        $bloqueo = centroLegajoPagareBloquearOperacion($mysqli, $idSolicitud);
        $solicitud = $bloqueo['solicitud']; $documento = $bloqueo['documento']; $venta = $bloqueo['venta']; $lote = $bloqueo['lote'];
        if (!centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) throw new Exception('La solicitud pertenece a otro local.');
        if ($solicitud['estado'] !== 'solicitada') throw new Exception('La solicitud ya fue resuelta o cambio de estado.');
        $error = centroLegajoPagareVentaValida($venta, $documento, !$aprobar);
        if ($aprobar && $error !== '') throw new Exception($error);
        $idSolicitud = intval($solicitud['id_solicitud']);
        $ahora = date('Y-m-d H:i:s');
        if ($aprobar) {
            $espera = in_array($documento['estado_fisico'], array('en_lote','pendiente_custodia','en_transito'), true);
            $nuevoEstado = $espera ? 'esperando_recepcion' : 'aprobada';
            $fechaEspera = $espera ? $ahora : null;
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_pagare_solicitud SET estado=?,observacion_resolucion=?,
                cod_usuario_apruebaFK=?,fecha_aprobacion=?,fecha_esperando_recepcion=?,cod_usuarioFK_update=?,
                fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_solicitud=? AND estado='solicitada'");
            $parametros = array($nuevoEstado, $observacion, intval($codUsuario), $ahora, $fechaEspera, intval($codUsuario), $ahora, $idSolicitud);
            centroFacturaBind($stmt, 'ssissisi', $parametros);
            $tipoEvento = 'aprobar_solicitud';
            $detalle = $observacion !== '' ? $observacion : ($espera ? 'Aprobada; debe esperar la recepcion fisica del pagare.' : 'Solicitud aprobada.');
        } else {
            $nuevoEstado = 'rechazada';
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_pagare_solicitud SET estado='rechazada',observacion_resolucion=?,
                cod_usuario_rechazaFK=?,fecha_rechazo=?,cod_usuarioFK_update=?,fecha_actualizacion=?,
                version_registro=version_registro+1 WHERE id_solicitud=? AND estado='solicitada'");
            $parametros = array($observacion, intval($codUsuario), $ahora, intval($codUsuario), $ahora, $idSolicitud);
            centroFacturaBind($stmt, 'sisisi', $parametros);
            $tipoEvento = 'rechazar_solicitud'; $detalle = $observacion;
        }
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo resolver la solicitud.'); }
        $stmt->close();
        if (!centroLegajoPagareRegistrarEvento($mysqli, $solicitud, $tipoEvento, 'solicitada', $nuevoEstado,
            $detalle, $codUsuario, $documento, $lote)) throw new Exception('No se pudo auditar la resolucion.');
        if (!$mysqli->commit()) throw new Exception('No se pudo confirmar la resolucion.');
        $mysqli->close();
        return array('ok' => true, 'id_solicitud' => $idSolicitud, 'estado' => $nuevoEstado);
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'solicitud_pagare', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoPagareAprobar($idSolicitud, $observacion, $codUsuario)
{
    return centroLegajoPagareResolver($idSolicitud, true, $observacion, $codUsuario);
}

function centroLegajoPagareRechazar($idSolicitud, $observacion, $codUsuario)
{
    return centroLegajoPagareResolver($idSolicitud, false, $observacion, $codUsuario);
}

function centroLegajoPagarePreparar($idSolicitud, $codUsuario)
{
    if (!centroLegajoPagarePuedeGestionar($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para preparar la devolucion.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    try {
        $bloqueo = centroLegajoPagareBloquearOperacion($mysqli, $idSolicitud);
        $solicitud = $bloqueo['solicitud']; $documento = $bloqueo['documento']; $venta = $bloqueo['venta']; $lote = $bloqueo['lote'];
        if (!centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) throw new Exception('La solicitud pertenece a otro local.');
        if (!centroLegajoPagarePuedeOperarUbicacion($codUsuario, $documento, $mysqli)) {
            throw new Exception('Solo el local que tiene fisicamente el pagare puede prepararlo para entrega.');
        }
        if (!in_array($solicitud['estado'], array('aprobada','esperando_recepcion'), true)) throw new Exception('La solicitud no esta aprobada para preparacion.');
        $error = centroLegajoPagareVentaValida($venta, $documento, false);
        if ($error !== '') throw new Exception($error);
        if (!in_array($documento['estado_documental'], array('disponible','validado'), true)) {
            throw new Exception('El pagare ya no tiene un estado documental habilitado para preparacion.');
        }
        if (!in_array($documento['estado_fisico'], array('en_sucursal','recibido'), true)) {
            throw new Exception('El pagare debe estar fisicamente en sucursal o recibido antes de prepararlo.');
        }
        $idSolicitud = intval($solicitud['id_solicitud']); $anterior = $solicitud['estado'];
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_pagare_solicitud SET estado='preparada',
            cod_usuario_preparaFK=?,fecha_preparacion=NOW(),cod_usuarioFK_update=?,fecha_actualizacion=NOW(),
            version_registro=version_registro+1 WHERE id_solicitud=? AND estado IN ('aprobada','esperando_recepcion')");
        $stmt->bind_param('iii', $codUsuario, $codUsuario, $idSolicitud);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo preparar la solicitud.'); }
        $stmt->close();
        if (!centroLegajoPagareRegistrarEvento($mysqli, $solicitud, 'preparar_entrega', $anterior, 'preparada',
            'Pagare localizado y preparado para la entrega.', $codUsuario, $documento, $lote)) throw new Exception('No se pudo auditar la preparacion.');
        if (!$mysqli->commit()) throw new Exception('No se pudo confirmar la preparacion.');
        $mysqli->close();
        return array('ok' => true, 'id_solicitud' => $idSolicitud, 'estado' => 'preparada');
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'solicitud_pagare', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoPagareCancelar($idSolicitud, $motivo, $codUsuario)
{
    if (!centroLegajoPagarePuedeGestionar($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para cancelar la solicitud.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $motivo = centroFacturaTextoBaseDatos($motivo, 3000, true);
    if ($motivo === '') return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Indique el motivo de cancelacion.');
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    try {
        $bloqueo = centroLegajoPagareBloquearOperacion($mysqli, $idSolicitud);
        $solicitud = $bloqueo['solicitud']; $documento = $bloqueo['documento']; $venta = $bloqueo['venta']; $lote = $bloqueo['lote'];
        if (!centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) throw new Exception('La solicitud pertenece a otro local.');
        if (!in_array($solicitud['estado'], centroLegajoPagareEstadosAbiertos(), true)) throw new Exception('La solicitud ya no puede cancelarse.');
        $anterior = $solicitud['estado']; $idSolicitud = intval($solicitud['id_solicitud']);
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_pagare_solicitud SET estado='cancelada',
            observacion_resolucion=?,cod_usuario_cancelaFK=?,fecha_cancelacion=NOW(),cod_usuarioFK_update=?,
            fecha_actualizacion=NOW(),version_registro=version_registro+1 WHERE id_solicitud=? AND solicitud_abierta=1");
        $stmt->bind_param('siii', $motivo, $codUsuario, $codUsuario, $idSolicitud);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo cancelar la solicitud.'); }
        $stmt->close();
        if (!centroLegajoPagareRegistrarEvento($mysqli, $solicitud, 'cancelar_solicitud', $anterior, 'cancelada',
            $motivo, $codUsuario, $documento, $lote)) throw new Exception('No se pudo auditar la cancelacion.');
        if (!$mysqli->commit()) throw new Exception('No se pudo confirmar la cancelacion.');
        $mysqli->close();
        return array('ok' => true, 'id_solicitud' => $idSolicitud, 'estado' => 'cancelada');
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'solicitud_pagare', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoPagareDirectorioEvidencias()
{
    return dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosLegajosPagares'.DIRECTORY_SEPARATOR;
}

function centroLegajoPagareAsegurarDirectorioEvidencias()
{
    $directorio = centroLegajoPagareDirectorioEvidencias();
    if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
        return array('ok' => false, 'mensaje' => 'No se pudo preparar la carpeta privada de evidencias.');
    }
    $proteccion = $directorio.'.htaccess';
    $contenido = "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n"
        ."<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n";
    $proteccionActual = is_file($proteccion) ? file_get_contents($proteccion) : false;
    if ($proteccionActual !== $contenido && file_put_contents($proteccion, $contenido, LOCK_EX) === false) {
        return array('ok' => false, 'mensaje' => 'No se pudo proteger la carpeta privada de evidencias.');
    }
    clearstatcache(true, $proteccion);
    $proteccionVerificada = is_file($proteccion) && is_readable($proteccion) ? file_get_contents($proteccion) : false;
    $directorioReal = realpath($directorio);
    if ($directorioReal === false || $proteccionVerificada !== $contenido) {
        return array('ok' => false, 'mensaje' => 'La proteccion de evidencias privadas no pudo verificarse.');
    }
    return array('ok' => true, 'directorio' => rtrim($directorioReal, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
}

function centroLegajoPagareNombreAleatorioEvidencia($idSolicitud)
{
    try {
        $aleatorio = bin2hex(random_bytes(20));
    } catch (Exception $e) {
        $fuerte = false;
        $bytes = function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(20, $fuerte) : false;
        if ($bytes === false || !$fuerte) throw new Exception('No se pudo generar un nombre seguro para la evidencia.');
        $aleatorio = bin2hex($bytes);
    }
    return 'pagare_'.intval($idSolicitud).'_'.$aleatorio.'.dat';
}

function centroLegajoPagareGuardarEvidenciaPreparada($idSolicitud, $archivo)
{
    $preparacion = centroLegajoPagareAsegurarDirectorioEvidencias();
    if (empty($preparacion['ok'])) return $preparacion;
    try {
        $nombreFisico = centroLegajoPagareNombreAleatorioEvidencia($idSolicitud);
    } catch (Exception $e) {
        return array('ok' => false, 'mensaje' => $e->getMessage());
    }
    $ruta = $preparacion['directorio'].$nombreFisico;
    $esperados = strlen($archivo['binario']);
    $escritos = file_put_contents($ruta, $archivo['binario'], LOCK_EX);
    if ($escritos === false || intval($escritos) !== $esperados) {
        if (is_file($ruta)) @unlink($ruta);
        return array('ok' => false, 'mensaje' => 'No se pudo guardar la evidencia firmada.');
    }
    $hashGuardado = hash_file('sha256', $ruta);
    if (!$hashGuardado || !hash_equals(strtolower((string)$archivo['hash']), strtolower($hashGuardado))) {
        @unlink($ruta);
        return array('ok' => false, 'mensaje' => 'La evidencia firmada no supera la verificacion de integridad.');
    }
    return array('ok' => true, 'nombre_fisico' => $nombreFisico, 'ruta_absoluta' => $ruta);
}

function centroLegajoPagareEntregar($idSolicitud, $datos, $archivos, $codUsuario)
{
    if (!centroLegajoPagarePuedeGestionar($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para entregar el pagare.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $archivos = array_values((array)$archivos);
    if (count($archivos) !== 1) return array('ok' => false, 'codigo' => 'evidencia', 'mensaje' => 'Adjunte exactamente una evidencia firmada.');
    $evidencia = centroFacturaPrepararArchivo($archivos[0]);
    if (empty($evidencia['ok'])) return array('ok' => false, 'codigo' => 'evidencia', 'mensaje' => $evidencia['mensaje']);
    $datos = (array)$datos;
    $receptorNombre = centroFacturaTextoBaseDatos(isset($datos['receptor_nombre']) ? $datos['receptor_nombre'] : '', 255);
    $receptorDocumento = centroFacturaTextoBaseDatos(isset($datos['receptor_documento']) ? $datos['receptor_documento'] : '', 45);
    $receptorRelacion = centroFacturaTextoBaseDatos(isset($datos['receptor_relacion']) ? $datos['receptor_relacion'] : '', 100);
    $observacion = centroFacturaTextoBaseDatos(isset($datos['observacion_entrega']) ? $datos['observacion_entrega'] : '', 3000, true);
    if ($receptorNombre === '' || $receptorDocumento === '') {
        return array('ok' => false, 'codigo' => 'receptor', 'mensaje' => 'Identifique al receptor con nombre y documento.');
    }
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    $rutaGuardada = '';
    try {
        $bloqueo = centroLegajoPagareBloquearOperacion($mysqli, $idSolicitud);
        $solicitud = $bloqueo['solicitud']; $documento = $bloqueo['documento']; $venta = $bloqueo['venta']; $lote = $bloqueo['lote'];
        if (!centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) throw new Exception('La solicitud pertenece a otro local.');
        if (!centroLegajoPagarePuedeOperarUbicacion($codUsuario, $documento, $mysqli)) {
            throw new Exception('Solo el local que tiene fisicamente el pagare puede registrar su entrega.');
        }
        if ($solicitud['estado'] !== 'preparada') throw new Exception('La solicitud debe estar preparada antes de entregar el pagare.');
        $error = centroLegajoPagareVentaValida($venta, $documento, false);
        if ($error !== '') throw new Exception($error);
        if (!in_array($documento['estado_documental'], array('disponible','validado'), true)) {
            throw new Exception('El pagare ya no tiene un estado documental habilitado para entrega.');
        }
        if (!in_array($documento['estado_fisico'], array('en_sucursal','recibido'), true)) {
            throw new Exception('El pagare ya no esta disponible en la ubicacion preparada.');
        }
        if (!empty($solicitud['evidencia_nombre_fisico'])) throw new Exception('La solicitud ya tiene una evidencia registrada.');
        $idSolicitud = intval($solicitud['id_solicitud']);
        $guardado = centroLegajoPagareGuardarEvidenciaPreparada($idSolicitud, $evidencia);
        if (empty($guardado['ok'])) throw new Exception($guardado['mensaje']);
        $rutaGuardada = $guardado['ruta_absoluta'];
        $nombreOriginal = centroFacturaTextoBaseDatos($evidencia['nombre'], 255);
        $extension = centroFacturaTextoBaseDatos($evidencia['extension'], 10);
        $mime = centroFacturaTextoBaseDatos($evidencia['mime'], 100);
        $hash = centroFacturaTextoBaseDatos($evidencia['hash'], 64);
        $relacionDb = $receptorRelacion === '' ? null : $receptorRelacion;
        $observacionDb = $observacion === '' ? null : $observacion;
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_pagare_solicitud SET estado='entregada',
            receptor_nombre=?,receptor_documento=?,receptor_relacion=?,observacion_entrega=?,
            cod_usuario_entregaFK=?,fecha_entrega=NOW(),evidencia_nombre_fisico=?,evidencia_nombre_original=?,
            evidencia_extension=?,evidencia_mime_type=?,evidencia_hash_sha256=?,cod_usuario_evidenciaFK=?,fecha_evidencia=NOW(),
            cod_usuarioFK_update=?,fecha_actualizacion=NOW(),version_registro=version_registro+1
            WHERE id_solicitud=? AND estado='preparada' AND evidencia_nombre_fisico IS NULL");
        $parametros = array($receptorNombre, $receptorDocumento, $relacionDb, $observacionDb, intval($codUsuario),
            $guardado['nombre_fisico'], $nombreOriginal, $extension, $mime, $hash, intval($codUsuario), intval($codUsuario), $idSolicitud);
        centroFacturaBind($stmt, 'ssssisssssiii', $parametros);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo registrar la entrega.'); }
        $stmt->close();
        $detalleDocumento = 'Devuelto mediante '.$solicitud['codigo_solicitud'].' a '.$receptorNombre.' ('.$receptorDocumento.').';
        if ($observacion !== '') $detalleDocumento .= ' '.$observacion;
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_fisico='devuelto_cliente',
            cod_local_ubicacionFK=NULL,ubicacion_fisica='Devuelto al cliente',
            observaciones=CONCAT_WS('\n',NULLIF(observaciones,''),?),cod_usuarioFK_update=?,fecha_actualizacion=NOW(),
            version_registro=version_registro+1 WHERE id_documento=? AND estado_fisico IN ('en_sucursal','recibido')");
        $idDocumento = intval($documento['id_documento']);
        $stmt->bind_param('sii', $detalleDocumento, $codUsuario, $idDocumento);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo actualizar la ubicacion final del pagare.'); }
        $stmt->close();
        if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'devolver_cliente',
            $documento['estado_documental'], $documento['estado_documental'], $documento['estado_fisico'],
            'devuelto_cliente', $detalleDocumento, $codUsuario)) throw new Exception('No se pudo auditar el documento devuelto.');
        $documentoFinal = $documento;
        $documentoFinal['estado_fisico'] = 'devuelto_cliente';
        $documentoFinal['cod_local_ubicacionFK'] = null;
        $documentoFinal['ubicacion_fisica'] = 'Devuelto al cliente';
        $detalleSolicitud = 'Entrega a '.$receptorNombre.' ('.$receptorDocumento.') con evidencia firmada SHA-256 '.$hash.'.';
        if (!centroLegajoPagareRegistrarEvento($mysqli, $solicitud, 'entregar_pagare', 'preparada', 'entregada',
            $detalleSolicitud, $codUsuario, $documentoFinal, $lote)) throw new Exception('No se pudo auditar la entrega.');
        if (!$mysqli->commit()) throw new Exception('No se pudo confirmar la entrega.');
        $mysqli->close();
        return array('ok' => true, 'id_solicitud' => $idSolicitud, 'estado' => 'entregada',
            'codigo_documento' => centroLegajoCodigoDocumento($venta['cod_venta'], 'pagare'), 'evidencia_disponible' => 1);
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        if ($rutaGuardada !== '' && is_file($rutaGuardada)) @unlink($rutaGuardada);
        return array('ok' => false, 'codigo' => 'solicitud_pagare', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoPagareDescargarEvidencia($idSolicitud, $codUsuario)
{
    if (!centroLegajoPagarePuedeVer($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para descargar la evidencia.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $mysqli = conectar_al_servidor();
    $fila = centroLegajoPagareSolicitudCompleta($mysqli, $idSolicitud);
    if (!$fila) { $mysqli->close(); return array('ok' => false, 'codigo' => 'solicitud', 'mensaje' => 'La solicitud no existe.'); }
    $venta = centroLegajoVentaRaw($mysqli, intval($fila['cod_ventaFK']));
    if (!$venta || !centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) {
        $mysqli->close(); return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'La solicitud pertenece a otro local.');
    }
    $mysqli->close();
    $nombreFisico = isset($fila['evidencia_nombre_fisico']) ? basename((string)$fila['evidencia_nombre_fisico']) : '';
    if ($nombreFisico === '' || $nombreFisico !== (string)$fila['evidencia_nombre_fisico']) {
        return array('ok' => false, 'codigo' => 'evidencia', 'mensaje' => 'La solicitud no tiene una evidencia valida.');
    }
    $directorio = realpath(centroLegajoPagareDirectorioEvidencias());
    $ruta = $directorio === false ? false : realpath($directorio.DIRECTORY_SEPARATOR.$nombreFisico);
    $prefijo = $directorio === false ? '' : rtrim($directorio, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    if ($ruta === false || $prefijo === '' || strncasecmp($ruta, $prefijo, strlen($prefijo)) !== 0
        || !is_file($ruta) || !is_readable($ruta)) {
        return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => 'La evidencia no esta disponible en el almacenamiento.');
    }
    $hash = hash_file('sha256', $ruta);
    if (!$hash || empty($fila['evidencia_hash_sha256']) || !hash_equals(strtolower((string)$fila['evidencia_hash_sha256']), strtolower($hash))) {
        return array('ok' => false, 'codigo' => 'integridad', 'mensaje' => 'La evidencia no supera el control de integridad.');
    }
    $mime = in_array($fila['evidencia_mime_type'], array('image/jpeg','image/png','image/webp','image/gif','application/pdf'), true)
        ? $fila['evidencia_mime_type'] : 'application/octet-stream';
    $original = centroFacturaValorUtf8((string)$fila['evidencia_nombre_original']);
    if ($original === '') $original = 'evidencia_pagare_'.intval($idSolicitud).'.'.preg_replace('/[^a-z0-9]/i', '', (string)$fila['evidencia_extension']);
    $alternativo = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $original);
    if ($alternativo === '') $alternativo = 'evidencia_pagare';
    while (ob_get_level() > 0) ob_end_clean();
    if (!headers_sent()) {
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($ruta));
        header('Content-Disposition: attachment; filename="'.$alternativo.'"; filename*=UTF-8\'\''.rawurlencode($original));
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
    }
    readfile($ruta);
    exit;
}

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
        lo.cod_usuario_transportistaFK,ld.estado AS estado_detalle_lote,ld.fecha_estado,
        pc.nombre_persona AS usuario_creador,pt.nombre_persona AS usuario_transportista,
        pct.nombre_persona AS usuario_custodia,pr.nombre_persona AS usuario_recepcion,
        pu.nombre_persona AS usuario_ultima_accion,COALESCE(lo.fecha_actualizacion,lo.fecha_creacion) AS ultima_accion_fecha
      FROM centro_legajo_lote_detalle ld
      INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
      LEFT JOIN persona pc ON pc.cod_persona=lo.cod_usuarioFK_create
      LEFT JOIN persona pt ON pt.cod_persona=lo.cod_usuario_transportistaFK
      LEFT JOIN persona pct ON pct.cod_persona=lo.cod_usuario_custodiaFK
      LEFT JOIN persona pr ON pr.cod_persona=lo.cod_usuario_recepcionFK
      LEFT JOIN persona pu ON pu.cod_persona=COALESCE(lo.cod_usuarioFK_update,lo.cod_usuarioFK_create)
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

function centroLegajoPagareEstadoFinancieroVenta($mysqli, $codVenta, $bloquear = false)
{
    $codVenta = intval($codVenta);
    $sql = "SELECT idcredito,Monto,descuento,totalinteres,deudaInteres
        FROM credito
        WHERE cod_venta=? AND UPPER(TRIM(IFNULL(Esado,'')))<>'INACTIVO'
        ORDER BY idcredito".($bloquear ? ' FOR UPDATE' : '');
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('No se pudo verificar el saldo de la venta.');
    }
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('No se pudo consultar el saldo de la venta.');
    }
    $creditos = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $creditos[] = $fila;
    }
    $stmt->close();

    $saldo = 0.0;
    $stmtPagos = $mysqli->prepare("SELECT tipo,Monto FROM pago
        WHERE cod_creditoFK=? AND tipo IN ('Pago Cuota','Interes')".($bloquear ? ' FOR UPDATE' : ''));
    if (!$stmtPagos) {
        throw new Exception('No se pudieron verificar los pagos aplicados.');
    }
    foreach ($creditos as $credito) {
        $capitalPagado = 0.0;
        $interesPagado = 0.0;
        $idCredito = intval($credito['idcredito']);
        $stmtPagos->bind_param('i', $idCredito);
        if (!$stmtPagos->execute()) {
            $stmtPagos->close();
            throw new Exception('No se pudieron consultar los pagos aplicados.');
        }
        $pagos = $stmtPagos->get_result();
        while ($pago = $pagos->fetch_assoc()) {
            if ((string)$pago['tipo'] === 'Pago Cuota') {
                $capitalPagado += floatval($pago['Monto']);
            } elseif ((string)$pago['tipo'] === 'Interes') {
                $interesPagado += floatval($pago['Monto']);
            }
        }
        $capital = max(0, (floatval($credito['Monto']) - floatval($credito['descuento'])) - $capitalPagado);
        $interes = max(0, (floatval($credito['totalinteres']) + floatval($credito['deudaInteres'])) - $interesPagado);
        $saldo += $capital + $interes;
    }
    $stmtPagos->close();
    $saldo = round($saldo, 2);
    return array(
        'creditos' => count($creditos),
        'saldo' => $saldo,
        'saldada' => count($creditos) > 0 && $saldo <= 0.01
    );
}

function centroLegajoPagareExigirCuentaSaldada($mysqli, $codVenta, $etapa)
{
    $finanzas = centroLegajoPagareEstadoFinancieroVenta($mysqli, intval($codVenta), true);
    if (empty($finanzas['saldada'])) {
        $saldo = isset($finanzas['saldo']) ? max(0, floatval($finanzas['saldo'])) : 0;
        throw new Exception(
            'La cuenta ya no figura saldada y no se puede '.$etapa.' la devolucion del pagare. '.
            'Saldo pendiente: Gs. '.number_format($saldo, 0, ',', '.').'.'
        );
    }
    return $finanzas;
}

function centroLegajoPagareValidarHiloVenta($mysqli, $codInterConsulta, $codVenta)
{
    $codInterConsulta = intval($codInterConsulta);
    $codVenta = intval($codVenta);
    if ($codInterConsulta <= 0) return 0;
    if (!centroFacturaColumnaExiste($mysqli, 'centro_legajo_pagare_solicitud', 'cod_interConsultaFK')) {
        throw new Exception('Instale la migracion de devolucion desde Hilos antes de usar esta opcion.');
    }
    $stmt = $mysqli->prepare("SELECT ic.cod_interConsulta
        FROM interconsulta ic
        WHERE ic.cod_interConsulta=? AND ic.estado<>'inactivo'
          AND (ic.cod_ventaFK=? OR EXISTS(
              SELECT 1 FROM interconsulta_paciente_venta ipv
              WHERE ipv.cod_interConsultaFK=ic.cod_interConsulta
                AND ipv.cod_ventaFK=? AND ipv.estado='activo'
          ))
        LIMIT 1 FOR UPDATE");
    if (!$stmt) throw new Exception('No se pudo validar el Hilo de la solicitud.');
    $stmt->bind_param('iii', $codInterConsulta, $codVenta, $codVenta);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('No se pudo consultar el Hilo de la solicitud.');
    }
    $valido = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$valido) throw new Exception('El Hilo indicado no corresponde a la venta del pagare.');
    return $codInterConsulta;
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
        d.ubicacion_fisica,d.version_registro AS version_documento,v.puntoexpedicion,v.num_factura,v.fecha_venta,v.TipoVenta AS tipo_venta,v.cod_local,
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
    $fila['codigo_legajo'] = centroLegajoCodigoLegajo($fila);
    $fila['codigo_documento'] = centroLegajoCodigoDocumento($fila, 'pagare');
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
    $actoresEstado = array(
        'solicitada' => array('usuario_solicita', 'fecha_solicitud', 'registro_solicitud', 'Responsable de la solicitud'),
        'aprobada' => array('usuario_aprueba', 'fecha_aprobacion', 'aprobacion_solicitud', 'Responsable administrativo'),
        'esperando_recepcion' => array('usuario_aprueba', 'fecha_esperando_recepcion', 'espera_recepcion', 'Responsable administrativo'),
        'preparada' => array('usuario_prepara', 'fecha_preparacion', 'preparacion_entrega', 'Responsable de preparación'),
        'entregada' => array('usuario_entrega', 'fecha_entrega', 'entrega_cliente', 'Responsable de entrega al cliente'),
        'rechazada' => array('usuario_rechaza', 'fecha_rechazo', 'rechazo_solicitud', 'Responsable del cierre'),
        'cancelada' => array('usuario_cancela', 'fecha_cancelacion', 'cancelacion_solicitud', 'Responsable del cierre')
    );
    $definicionActor = isset($actoresEstado[$estado]) ? $actoresEstado[$estado] : $actoresEstado['solicitada'];
    $actorSolicitud = !empty($fila[$definicionActor[0]]) ? (string)$fila[$definicionActor[0]] : '';
    $fechaSolicitud = !empty($fila[$definicionActor[1]]) ? (string)$fila[$definicionActor[1]]
        : (!empty($fila['fecha_actualizacion']) ? (string)$fila['fecha_actualizacion'] : (isset($fila['fecha_solicitud']) ? (string)$fila['fecha_solicitud'] : ''));
    $responsableActual = $actorSolicitud;
    $rolResponsable = $definicionActor[3];
    if (!empty($loteUtf8)) {
        $estadoLote = isset($loteUtf8['estado']) ? (string)$loteUtf8['estado'] : '';
        if ($estadoLote === 'pendiente_custodia') {
            $responsableActual = isset($loteUtf8['usuario_transportista']) ? $loteUtf8['usuario_transportista'] : '';
            $rolResponsable = 'Transportista asignado · custodia pendiente';
        } elseif ($estadoLote === 'en_transito') {
            $responsableActual = !empty($loteUtf8['usuario_custodia']) ? $loteUtf8['usuario_custodia']
                : (isset($loteUtf8['usuario_transportista']) ? $loteUtf8['usuario_transportista'] : '');
            $rolResponsable = 'Custodia durante el traslado';
        } elseif (in_array($estadoLote, array('recibido','recibido_parcial','observado'), true)) {
            $responsableActual = isset($loteUtf8['usuario_recepcion']) ? $loteUtf8['usuario_recepcion'] : '';
            $rolResponsable = 'Custodia en destino';
        } elseif ($estadoLote === 'borrador') {
            $responsableActual = isset($loteUtf8['usuario_creador']) ? $loteUtf8['usuario_creador'] : '';
            $rolResponsable = 'Responsable de preparación';
        }
    }
    $fila['responsable_actual'] = $responsableActual !== '' ? $responsableActual : 'Sin asignar';
    $fila['responsable_actual_rol'] = $rolResponsable;
    $fila['ultima_accion_usuario'] = $actorSolicitud;
    $fila['ultima_accion_fecha'] = $fechaSolicitud;
    $fila['ultima_accion_tipo'] = $definicionActor[2];
    if (!empty($loteUtf8['ultima_accion_fecha']) && ($fechaSolicitud === '' || strcmp((string)$loteUtf8['ultima_accion_fecha'], $fechaSolicitud) > 0)) {
        $fila['ultima_accion_usuario'] = isset($loteUtf8['usuario_ultima_accion']) ? $loteUtf8['usuario_ultima_accion'] : '';
        $fila['ultima_accion_fecha'] = $loteUtf8['ultima_accion_fecha'];
        $fila['ultima_accion_tipo'] = 'actualizacion_custodia';
    }
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
              AND (loa.cod_local_origenFK=? OR loa.cod_local_destinoFK=?
                OR (loa.cod_usuario_transportistaFK=? AND loa.estado IN ('pendiente_custodia','en_transito','recibido_parcial','observado')))))";
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

function centroLegajoPagareEstadoFinancieroConsulta($codVenta)
{
    $mysqli = conectar_al_servidor();
    try {
        $estado = centroLegajoPagareEstadoFinancieroVenta($mysqli, intval($codVenta), false);
        $mysqli->close();
        return array('ok' => true, 'estado' => $estado);
    } catch (Exception $e) {
        $mysqli->close();
        return array('ok' => false, 'estado' => array('creditos' => 0, 'saldo' => 0, 'saldada' => false), 'mensaje' => $e->getMessage());
    }
}

function centroLegajoPagareNumeroVentaVisible($fila)
{
    $numero = isset($fila['num_factura']) ? trim((string)$fila['num_factura']) : '';
    $punto = isset($fila['puntoexpedicion']) ? trim((string)$fila['puntoexpedicion']) : '';
    if ($numero === '') return isset($fila['cod_venta']) ? (string)intval($fila['cod_venta']) : '';
    return $punto !== '' ? $punto.'-'.$numero : $numero;
}

function centroLegajoPagareSoloDigitos($valor)
{
    return preg_replace('/[^0-9]/', '', (string)$valor);
}

function centroLegajoPagareAgregarMotivo(&$motivos, $codigo, $mensaje)
{
    $motivos[] = array('codigo' => (string)$codigo, 'mensaje' => (string)$mensaje);
}

function centroLegajoPagareEvaluarBusqueda($fila, $finanzas, $finanzasDisponibles, $solicitudActiva)
{
    $motivos = array();
    $esAnulada = !empty($fila['es_anulada']);
    $esCredito = strtoupper(trim((string)$fila['tipo_venta'])) === 'CREDITO';
    $idDocumento = isset($fila['id_documento']) ? intval($fila['id_documento']) : 0;

    if ($esAnulada) {
        centroLegajoPagareAgregarMotivo($motivos, 'venta_anulada', 'La venta está anulada.');
    } elseif (!$esCredito) {
        centroLegajoPagareAgregarMotivo($motivos, 'venta_contado', 'Venta contado: no corresponde pagaré.');
    } else {
        if ($idDocumento <= 0) {
            centroLegajoPagareAgregarMotivo($motivos, 'pagare_sin_registro', 'El pagaré todavía no fue confirmado en el legajo.');
        } else {
            $estadoDocumental = isset($fila['estado_documental']) ? (string)$fila['estado_documental'] : '';
            $estadoFisico = isset($fila['estado_fisico']) ? (string)$fila['estado_fisico'] : '';
            if (!in_array($estadoDocumental, array('disponible','validado'), true)) {
                centroLegajoPagareAgregarMotivo($motivos, 'pagare_no_confirmado',
                    $estadoDocumental === 'observado' ? 'El pagaré está observado.' : 'El pagaré está pendiente de confirmación.');
            }
            if ($estadoFisico === 'devuelto_cliente') {
                centroLegajoPagareAgregarMotivo($motivos, 'pagare_devuelto', 'El pagaré ya fue devuelto al cliente.');
            } elseif (!in_array($estadoFisico, array('en_sucursal','en_lote','pendiente_custodia','en_transito','recibido'), true)) {
                centroLegajoPagareAgregarMotivo($motivos, 'pagare_sin_ubicacion', 'El pagaré no tiene una ubicación física confirmada.');
            }
        }

        if (!$finanzasDisponibles) {
            centroLegajoPagareAgregarMotivo($motivos, 'saldo_no_verificado', 'No se pudo verificar el saldo de la venta.');
        } elseif (intval($finanzas['creditos']) <= 0) {
            centroLegajoPagareAgregarMotivo($motivos, 'sin_creditos_activos', 'La venta no tiene créditos activos para comprobar su cancelación.');
        } elseif (empty($finanzas['saldada'])) {
            centroLegajoPagareAgregarMotivo($motivos, 'saldo_pendiente',
                'Cuenta con saldo pendiente de Gs. '.number_format(max(0, floatval($finanzas['saldo'])), 0, ',', '.').'.');
        }
    }

    $tieneSolicitud = !empty($solicitudActiva) && !empty($solicitudActiva['id_solicitud']);
    $elegible = !$tieneSolicitud && count($motivos) === 0;
    if ($tieneSolicitud) {
        $estado = 'solicitud_activa';
        $descripcion = 'Esta venta ya tiene una solicitud de devolución activa.';
    } elseif ($elegible) {
        $estado = 'elegible';
        $descripcion = 'Pagaré localizado y cuenta saldada: puede solicitar la devolución.';
    } else {
        $estado = 'no_elegible';
        $descripcion = isset($motivos[0]['mensaje']) ? $motivos[0]['mensaje'] : 'La venta no está habilitada para devolución.';
    }

    return array(
        'elegible' => $elegible ? 1 : 0,
        'estado_elegibilidad' => $estado,
        'descripcion_elegibilidad' => $descripcion,
        'motivos_no_elegible' => $motivos
    );
}

function centroLegajoPagareBuscarElegibles($codUsuario, $busqueda, $limite = 30, $codInterConsulta = 0)
{
    if (!centroLegajoPagarePuedeGestionar($codUsuario)) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para solicitar devoluciones de pagares.');
    }
    if (!centroLegajoPagareEstructuraDisponible()) return centroLegajoPagareErrorEstructura();
    $limite = max(1, min(60, intval($limite)));
    $codInterConsulta = intval($codInterConsulta);
    $busqueda = trim((string)$busqueda);
    if (function_exists('mb_substr')) $busqueda = mb_substr($busqueda, 0, 100, 'UTF-8');
    else $busqueda = substr($busqueda, 0, 100);
    $busquedaDb = centroFacturaTextoBaseDatos($busqueda, 100);
    $busquedaDigitos = preg_replace('/[^0-9]/', '', $busqueda);
    $modoLocalizacion = $busquedaDb !== '' || $codInterConsulta > 0;
    $mysqli = conectar_al_servidor();
    $anulada = centroLegajoVentaAnuladaSql('v');
    $hiloSql = "COALESCE(
          (SELECT ic.cod_interConsulta FROM interconsulta ic
           WHERE ic.cod_ventaFK=v.cod_venta AND ic.estado<>'inactivo'
           ORDER BY ic.cod_interConsulta DESC LIMIT 1),
          (SELECT ipv.cod_interConsultaFK FROM interconsulta_paciente_venta ipv
           INNER JOIN interconsulta ic2 ON ic2.cod_interConsulta=ipv.cod_interConsultaFK AND ic2.estado<>'inactivo'
           WHERE ipv.cod_ventaFK=v.cod_venta AND ipv.estado='activo'
           ORDER BY ipv.cod_interConsultaFK DESC LIMIT 1),0
        )";
    $numeroVisibleSql = "CONCAT(CASE WHEN TRIM(IFNULL(v.puntoexpedicion,''))<>''
        THEN CONCAT(TRIM(v.puntoexpedicion),'-') ELSE '' END,TRIM(IFNULL(v.num_factura,'')))";
    $sql = "SELECT v.cod_venta,v.fecha_venta,v.total_venta,v.descuento,v.TipoVenta AS tipo_venta,
        v.tipo_comprobante,v.puntoexpedicion,v.num_factura,
        v.cod_clienteFK,v.cod_local,v.cod_usuarioFK,v.estado,v.anulado,v.estadocuenta,
        p.nombre_persona AS titular,COALESCE(NULLIF(TRIM(c.ci_cliente),''),NULLIF(TRIM(c.rut_cliente),''),'') AS documento,
        TRIM(IFNULL(c.ci_cliente,'')) AS ci_cliente_busqueda,TRIM(IFNULL(c.rut_cliente,'')) AS rut_cliente_busqueda,
        TRIM(IFNULL(p.telefono,'')) AS telefono,
        l.Nombre AS nombre_local,d.id_documento,d.tipo_documento,d.estado_documental,d.estado_fisico,d.cod_local_ubicacionFK,d.ubicacion_fisica,
        ".$hiloSql." AS cod_interConsulta,
        CASE WHEN ".$anulada." THEN 1 ELSE 0 END AS es_anulada
      FROM venta v
      LEFT JOIN centro_legajo_documento d ON d.cod_ventaFK=v.cod_venta AND d.tipo_documento='pagare'
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      WHERE v.cod_venta<>0";
    $tipos = '';
    $parametros = array();
    if ($codInterConsulta > 0) {
        $sql .= " AND (EXISTS(
              SELECT 1 FROM interconsulta ich
              WHERE ich.cod_interConsulta=? AND ich.estado<>'inactivo' AND ich.cod_ventaFK=v.cod_venta
            ) OR EXISTS(
              SELECT 1 FROM interconsulta_paciente_venta ipvh
              INNER JOIN interconsulta ich2 ON ich2.cod_interConsulta=ipvh.cod_interConsultaFK AND ich2.estado<>'inactivo'
              WHERE ipvh.cod_interConsultaFK=? AND ipvh.cod_ventaFK=v.cod_venta AND ipvh.estado='activo'
            ))";
        $tipos .= 'ii';
        $parametros[] = $codInterConsulta;
        $parametros[] = $codInterConsulta;
    }
    if ($busquedaDb !== '') {
        $partesBusqueda = array(
            'CAST(v.cod_venta AS CHAR)=?',
            'CAST('.$hiloSql.' AS CHAR)=?',
            'p.nombre_persona LIKE ?',
            'c.rut_cliente LIKE ?',
            'c.ci_cliente LIKE ?',
            'p.telefono LIKE ?',
            'v.num_factura LIKE ?',
            $numeroVisibleSql.' LIKE ?'
        );
        $like = '%'.$busquedaDb.'%';
        $tipos .= 'ssssssss';
        $parametros[] = $busquedaDb;
        $parametros[] = $busquedaDb;
        $parametros[] = $like;
        $parametros[] = $like;
        $parametros[] = $like;
        $parametros[] = $like;
        $parametros[] = $like;
        $parametros[] = $like;
        if ($busquedaDigitos !== '' && strlen($busquedaDigitos) >= 3) {
            $normalizado = '%'.$busquedaDigitos.'%';
            $partesBusqueda[] = "REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(c.ci_cliente,''),'.',''),'-',''),' ',''),'/','') LIKE ?";
            $partesBusqueda[] = "REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(c.rut_cliente,''),'.',''),'-',''),' ',''),'/','') LIKE ?";
            $partesBusqueda[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(p.telefono,''),' ',''),'.',''),'-',''),'(',''),')',''),'+','') LIKE ?";
            $partesBusqueda[] = "REPLACE(REPLACE(".$numeroVisibleSql.",'-',''),' ','') LIKE ?";
            $tipos .= 'ssss';
            $parametros[] = $normalizado;
            $parametros[] = $normalizado;
            $parametros[] = $normalizado;
            $parametros[] = $normalizado;
        }
        $sql .= ' AND ('.implode(' OR ', $partesBusqueda).')';
    } elseif ($codInterConsulta <= 0) {
        $sql .= " AND UPPER(TRIM(IFNULL(v.TipoVenta,'')))='CREDITO'
            AND NOT ".$anulada." AND d.id_documento IS NOT NULL";
    }
    $sql .= ' ORDER BY v.fecha_venta DESC,v.cod_venta DESC LIMIT 120';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo preparar la busqueda de pagares: '.$error);
    }
    if ($tipos !== '') centroFacturaBind($stmt, $tipos, $parametros);
    if (!$stmt->execute()) {
        $stmt->close(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo buscar ventas con pagare.');
    }
    $candidatos = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $candidatos[] = $fila;
    $stmt->close();
    $tipoCoincidencia = 'general';
    if ($busqueda !== '') {
        $clientesExactos = array();
        if (strlen($busquedaDigitos) >= 5) {
            foreach ($candidatos as $filaCandidata) {
                $cedulaExacta = centroLegajoPagareSoloDigitos(isset($filaCandidata['ci_cliente_busqueda']) ? $filaCandidata['ci_cliente_busqueda'] : '');
                $rucExacto = centroLegajoPagareSoloDigitos(isset($filaCandidata['rut_cliente_busqueda']) ? $filaCandidata['rut_cliente_busqueda'] : '');
                $telefonoExacto = centroLegajoPagareSoloDigitos(isset($filaCandidata['telefono']) ? $filaCandidata['telefono'] : '');
                if ($cedulaExacta === $busquedaDigitos || $rucExacto === $busquedaDigitos || $telefonoExacto === $busquedaDigitos) {
                    $clientesExactos[intval($filaCandidata['cod_clienteFK'])] = true;
                }
            }
        }
        if (count($clientesExactos) > 0) {
            $filtrados = array();
            foreach ($candidatos as $filaCandidata) {
                if (isset($clientesExactos[intval($filaCandidata['cod_clienteFK'])])) $filtrados[] = $filaCandidata;
            }
            $candidatos = $filtrados;
            $tipoCoincidencia = 'cliente_exacto';
        } else {
            $ventasVisibles = array();
            $busquedaComparable = strtolower(trim((string)$busqueda));
            foreach ($candidatos as $filaCandidata) {
                $visible = centroLegajoPagareNumeroVentaVisible($filaCandidata);
                $visibleComparable = strtolower(trim((string)$visible));
                $visibleDigitos = centroLegajoPagareSoloDigitos($visible);
                if ($visibleComparable === $busquedaComparable
                    || ($busquedaDigitos !== '' && $visibleDigitos === $busquedaDigitos)) {
                    $ventasVisibles[] = $filaCandidata;
                }
            }
            if (count($ventasVisibles) > 0) {
                $candidatos = $ventasVisibles;
                $tipoCoincidencia = 'venta_visible';
            } elseif (ctype_digit($busquedaDigitos) && $busquedaDigitos !== '') {
                $ventasInternas = array();
                foreach ($candidatos as $filaCandidata) {
                    if (intval($filaCandidata['cod_venta']) === intval($busquedaDigitos)) $ventasInternas[] = $filaCandidata;
                }
                if (count($ventasInternas) > 0) {
                    $candidatos = $ventasInternas;
                    $tipoCoincidencia = 'venta_interna';
                }
            }
        }
    }
    $registros = array();
    $totalCoincidencias = 0;
    $totalElegibles = 0;
    $totalSolicitudes = 0;
    $totalAnuladas = 0;
    $totalCredito = 0;
    $totalContado = 0;
    foreach ($candidatos as $fila) {
        $venta = $fila;
        $venta['importe_venta'] = max(0, floatval($fila['total_venta']) - floatval($fila['descuento']));
        if (!centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) continue;

        $finanzas = array('creditos' => 0, 'saldo' => 0, 'saldada' => false);
        $finanzasDisponibles = true;
        if (strtoupper(trim((string)$fila['tipo_venta'])) === 'CREDITO' && empty($fila['es_anulada'])) {
            try {
                $finanzas = centroLegajoPagareEstadoFinancieroVenta($mysqli, intval($fila['cod_venta']), false);
            } catch (Exception $e) {
                $finanzasDisponibles = false;
            }
        }
        $activa = intval($fila['id_documento']) > 0
            ? centroLegajoPagareSolicitudActivaVenta(intval($fila['cod_venta']), $codUsuario, $mysqli) : array();
        $evaluacion = centroLegajoPagareEvaluarBusqueda($fila, $finanzas, $finanzasDisponibles, $activa);
        if (!$modoLocalizacion && empty($evaluacion['elegible']) && empty($activa)) continue;

        $totalCoincidencias++;
        if (!empty($evaluacion['elegible'])) $totalElegibles++;
        if (!empty($activa)) $totalSolicitudes++;
        if (!empty($fila['es_anulada'])) {
            $totalAnuladas++;
        } elseif (strtoupper(trim((string)$fila['tipo_venta'])) === 'CONTADO') {
            $totalContado++;
        } else {
            $totalCredito++;
        }

        if (count($registros) >= $limite) continue;
        $numeroVisible = centroLegajoPagareNumeroVentaVisible($fila);
        unset($fila['ci_cliente_busqueda'], $fila['rut_cliente_busqueda']);
        $fila = centroFacturaFilaUtf8($fila);
        if ($codInterConsulta > 0) $fila['cod_interConsulta'] = $codInterConsulta;
        $fila['saldo_pendiente'] = $finanzas['saldo'];
        $fila['cuenta_saldada'] = $finanzas['saldada'] ? 1 : 0;
        $fila['cantidad_creditos'] = intval($finanzas['creditos']);
        $fila['solicitud_activa'] = $activa;
        $fila['codigo_legajo'] = centroLegajoCodigoLegajo($fila);
        $fila['codigo_documento'] = centroLegajoCodigoDocumento($fila, 'pagare');
        $fila['numero_venta_visible'] = centroFacturaValorUtf8($numeroVisible);
        $fila['importe_venta'] = max(0, floatval($fila['total_venta']) - floatval($fila['descuento']));
        $fila = array_merge($fila, $evaluacion);
        $registros[] = $fila;
    }
    $mysqli->close();
    return array(
        'ok' => true,
        'registros' => $registros,
        'total' => $totalCoincidencias,
        'total_elegibles' => $totalElegibles,
        'total_solicitudes_activas' => $totalSolicitudes,
        'total_anuladas' => $totalAnuladas,
        'total_credito' => $totalCredito,
        'total_contado' => $totalContado,
        'modo_localizacion' => $modoLocalizacion ? 1 : 0,
        'tipo_coincidencia' => $tipoCoincidencia,
        'truncado' => $totalCoincidencias > $limite || count($candidatos) >= 120 ? 1 : 0
    );
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
    $codInterConsulta = isset($datos['cod_interConsulta']) ? intval($datos['cod_interConsulta']) : 0;
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
        $codInterConsulta = centroLegajoPagareValidarHiloVenta($mysqli, $codInterConsulta, $codVenta);
        $estadoFinanciero = centroLegajoPagareEstadoFinancieroVenta($mysqli, $codVenta, true);
        if (!$estadoFinanciero['saldada']) {
            if (intval($estadoFinanciero['creditos']) < 1) {
                throw new Exception('La venta no tiene una cuenta de credito activa para solicitar el pagare.');
            }
            throw new Exception('La venta aun no esta saldada. Saldo pendiente: Gs. '.number_format($estadoFinanciero['saldo'], 0, ',', '.').'.');
        }
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
        $tieneColumnaHilo = centroFacturaColumnaExiste($mysqli, 'centro_legajo_pagare_solicitud', 'cod_interConsultaFK');
        if ($tieneColumnaHilo) {
            $stmt = centroLegajoPrepararEscritura($mysqli, "INSERT INTO centro_legajo_pagare_solicitud
                (codigo_solicitud,id_documentoFK,cod_ventaFK,cod_interConsultaFK,estado,solicitante_nombre,solicitante_documento,motivo_solicitud,
                 estado_fisico_snapshot,cod_local_ubicacion_snapshotFK,ubicacion_fisica_snapshot,id_lote_snapshotFK,
                 codigo_lote_snapshot,estado_lote_snapshot,cod_usuario_solicitaFK,cod_usuarioFK_update,fecha_actualizacion)
                VALUES (?,?,?,?,'solicitada',?,?,?,?,?,?,?,?,?,?,?,?)");
            $hiloDb = $codInterConsulta > 0 ? $codInterConsulta : null;
            $parametros = array($temporal, $idDocumento, $codVenta, $hiloDb, $solicitante, $documentoSolicitanteDb, $motivo,
                $snapshot['estado_fisico'], $snapshot['cod_local'], $ubicacion, $snapshot['id_lote'], $codigoLote, $estadoLote,
                intval($codUsuario), intval($codUsuario), $ahora);
            centroFacturaBind($stmt, 'siiissssisissiis', $parametros);
        } else {
            $stmt = centroLegajoPrepararEscritura($mysqli, "INSERT INTO centro_legajo_pagare_solicitud
                (codigo_solicitud,id_documentoFK,cod_ventaFK,estado,solicitante_nombre,solicitante_documento,motivo_solicitud,
                 estado_fisico_snapshot,cod_local_ubicacion_snapshotFK,ubicacion_fisica_snapshot,id_lote_snapshotFK,
                 codigo_lote_snapshot,estado_lote_snapshot,cod_usuario_solicitaFK,cod_usuarioFK_update,fecha_actualizacion)
                VALUES (?,?,?,'solicitada',?,?,?,?,?,?,?,?,?,?,?,?)");
            $parametros = array($temporal, $idDocumento, $codVenta, $solicitante, $documentoSolicitanteDb, $motivo,
                $snapshot['estado_fisico'], $snapshot['cod_local'], $ubicacion, $snapshot['id_lote'], $codigoLote, $estadoLote,
                intval($codUsuario), intval($codUsuario), $ahora);
            centroFacturaBind($stmt, 'siissssisissiis', $parametros);
        }
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
            'codigo_documento' => centroLegajoCodigoDocumento($venta, 'pagare'), 'estado' => 'solicitada');
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
        if ($aprobar) {
            centroLegajoPagareExigirCuentaSaldada($mysqli, intval($solicitud['cod_ventaFK']), 'aprobar');
        }
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
        centroLegajoPagareExigirCuentaSaldada($mysqli, intval($solicitud['cod_ventaFK']), 'preparar');
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
        centroLegajoPagareExigirCuentaSaldada($mysqli, intval($solicitud['cod_ventaFK']), 'entregar');
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
            'codigo_documento' => centroLegajoCodigoDocumento($venta, 'pagare'), 'evidencia_disponible' => 1);
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
    if (!$venta || !centroFacturaPuedeUsarLocal($codUsuario, intval($venta['cod_local']), $mysqli)) {
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

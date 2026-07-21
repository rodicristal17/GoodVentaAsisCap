<?php

date_default_timezone_set('America/Asuncion');

function centroLegajoEstructuraDisponible($mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $tablas = array(
        'centro_legajo_documento', 'centro_legajo_documento_evento',
        'centro_legajo_lote', 'centro_legajo_lote_detalle', 'centro_legajo_lote_evento'
    );
    $disponible = true;
    foreach ($tablas as $tabla) {
        if (!centroFacturaTablaExiste($mysqli, $tabla)) {
            $disponible = false;
            break;
        }
    }
    if ($cerrar) $mysqli->close();
    return $disponible;
}

function centroLegajoTienePermiso($codUsuario, $codigo)
{
    return centroFacturaTienePermiso($codUsuario, $codigo)
        || centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS');
}

function centroLegajoPuedeVer($codUsuario)
{
    return centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')
        && centroLegajoTienePermiso($codUsuario, 'VERLEGAJOSVENTA');
}

function centroLegajoTipos()
{
    return array('contrato', 'pagare', 'cedula', 'consentimiento', 'detalle_venta');
}

function centroLegajoNumerosDocumento()
{
    return array(
        'contrato' => '01',
        'pagare' => '02',
        'cedula' => '03',
        'consentimiento' => '04',
        'detalle_venta' => '05'
    );
}

function centroLegajoSiglaLocal($codLocal, $nombreLocal = '')
{
    $siglas = array(
        1 => 'ADM',
        3 => 'CC',
        5 => 'VI',
        6 => 'PM',
        7 => 'SL',
        8 => 'CQ',
        9 => 'VM'
    );
    $codLocal = intval($codLocal);
    if (isset($siglas[$codLocal])) return $siglas[$codLocal];

    $nombre = strtoupper(trim((string)$nombreLocal));
    $nombre = strtr($nombre, array('Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N'));
    $nombre = preg_replace('/\b(CLINIDENT|ADMINISTRACION|COMPARTIDOS|LOCAL|SUCURSAL)\b/', ' ', $nombre);
    $palabras = preg_split('/[^A-Z0-9]+/', trim($nombre), -1, PREG_SPLIT_NO_EMPTY);
    $sigla = '';
    foreach ((array)$palabras as $palabra) {
        $sigla .= substr($palabra, 0, 1);
        if (strlen($sigla) >= 3) break;
    }
    if ($sigla !== '') return $sigla;
    return $codLocal > 0 ? 'L'.$codLocal : 'LOC';
}

function centroLegajoNumeroVenta($venta)
{
    if (is_array($venta)) {
        $numero = isset($venta['num_factura']) ? trim((string)$venta['num_factura']) : '';
        $punto = isset($venta['puntoexpedicion']) ? trim((string)$venta['puntoexpedicion']) : '';
        if ($numero !== '') {
            $numero = preg_replace('/\s+/', '', $numero);
            $punto = preg_replace('/\s+/', '', $punto);
            if ($punto !== '' && stripos($numero, $punto.'-') !== 0) return $punto.'-'.$numero;
            return $numero;
        }
        return isset($venta['cod_venta']) ? (string)intval($venta['cod_venta']) : '0';
    }
    $numero = trim((string)$venta);
    return $numero !== '' ? preg_replace('/\s+/', '', $numero) : '0';
}

function centroLegajoCodigoLegajo($venta)
{
    $numero = centroLegajoNumeroVenta($venta);
    if (!is_array($venta)) return $numero;
    $sigla = centroLegajoSiglaLocal(
        isset($venta['cod_local']) ? $venta['cod_local'] : (isset($venta['cod_localFK']) ? $venta['cod_localFK'] : 0),
        isset($venta['nombre_local']) ? $venta['nombre_local'] : (isset($venta['nombre_local_origen']) ? $venta['nombre_local_origen'] : '')
    );
    if (preg_match('/-'.preg_quote($sigla, '/').'$/i', $numero)) return strtoupper($numero);
    return $numero.'-'.$sigla;
}

function centroLegajoCodigoDocumento($venta, $tipoDocumento)
{
    $numeros = centroLegajoNumerosDocumento();
    $tipoDocumento = (string)$tipoDocumento;
    if (!isset($numeros[$tipoDocumento])) return '';
    return centroLegajoCodigoLegajo($venta).'-'.$numeros[$tipoDocumento];
}

function centroLegajoTipoEsRequerido($venta, $tipoDocumento)
{
    if (!in_array($tipoDocumento, centroLegajoTipos(), true)) return false;
    $tipoVenta = isset($venta['tipo_venta']) ? strtoupper(trim((string)$venta['tipo_venta'])) : '';
    if ($tipoVenta === 'CONTADO') return in_array($tipoDocumento, array('consentimiento','detalle_venta'), true);
    return true;
}

function centroLegajoDocumentoConservaAntecedente($documento)
{
    $documento = (array)$documento;
    $estadoDocumental = isset($documento['estado_documental']) ? (string)$documento['estado_documental'] : '';
    $estadoFisico = isset($documento['estado_fisico']) ? (string)$documento['estado_fisico'] : '';
    return in_array($estadoDocumental, array('disponible','validado','observado'), true)
        || in_array($estadoFisico, array('en_sucursal','en_lote','pendiente_custodia','en_transito','recibido','faltante','observado','devuelto_cliente'), true);
}

function centroLegajoSolicitudPagareAbiertaDocumento($mysqli, $idDocumento, $bloquear = false)
{
    if (!centroFacturaTablaExiste($mysqli, 'centro_legajo_pagare_solicitud')) return array();
    $stmt = $mysqli->prepare('SELECT id_solicitud,codigo_solicitud,estado,estado_fisico_snapshot,id_lote_snapshotFK
        FROM centro_legajo_pagare_solicitud WHERE id_documentoFK=? AND solicitud_abierta=1 LIMIT 1'.($bloquear ? ' FOR UPDATE' : ''));
    if (!$stmt) return array();
    $idDocumento = intval($idDocumento);
    $stmt->bind_param('i', $idDocumento);
    if (!$stmt->execute()) { $stmt->close(); return array(); }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroLegajoTiposRequeridosVenta($venta)
{
    $requeridos = array();
    foreach (centroLegajoTipos() as $tipo) {
        if (centroLegajoTipoEsRequerido($venta, $tipo)) $requeridos[] = $tipo;
    }
    return $requeridos;
}

function centroLegajoPrepararEscritura($mysqli, $sql)
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('No se pudo preparar una operacion de legajos.');
    }
    return $stmt;
}

function centroLegajoVentaAnuladaSql($alias = 'v')
{
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    if ($alias === '') $alias = 'v';
    return "(LOWER(TRIM(IFNULL(".$alias.".estado,''))) IN ('inactivo','anulado')
        OR LOWER(TRIM(IFNULL(".$alias.".anulado,''))) IN ('si','anulado','activo')
        OR EXISTS (SELECT 1 FROM cancelaciones ca WHERE ca.cod_venta=".$alias.".cod_venta))";
}

function centroLegajoVentaRaw($mysqli, $codVenta)
{
    $codVenta = intval($codVenta);
    $anulada = centroLegajoVentaAnuladaSql('v');
    $stmt = $mysqli->prepare("SELECT v.cod_venta,v.puntoexpedicion,v.num_factura,v.fecha_venta,v.total_venta,v.descuento,v.TipoVenta AS tipo_venta,
        v.cod_clienteFK,v.cod_local,v.cod_usuarioFK,v.estado,v.anulado,v.estadocuenta,
        p.nombre_persona AS titular,TRIM(IFNULL(c.ci_cliente,'')) AS documento,TRIM(IFNULL(c.rut_cliente,'')) AS ruc_cliente,
        l.Nombre AS nombre_local,pu.nombre_persona AS usuario_venta,
        CASE WHEN TRIM(IFNULL(c.foto1,''))<>'' OR TRIM(IFNULL(c.foto2,''))<>'' THEN 1 ELSE 0 END AS fuente_cedula,
        CASE WHEN EXISTS (SELECT 1 FROM detalle_venta dv WHERE dv.cod_ventaFK=v.cod_venta) THEN 1 ELSE 0 END AS fuente_detalle_venta,
        CASE WHEN ".$anulada." THEN 1 ELSE 0 END AS es_anulada
      FROM venta v
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      LEFT JOIN persona pu ON pu.cod_persona=v.cod_usuarioFK
      WHERE v.cod_venta=? LIMIT 1");
    if (!$stmt) return array();
    $stmt->bind_param('i', $codVenta);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) return array();
    $fila['importe_venta'] = max(0, floatval($fila['total_venta']) - floatval($fila['descuento']));
    return centroFacturaFilaUtf8($fila);
}

function centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)
{
    if (empty($venta)) return false;
    if (centroFacturaPuedeUsarLocal($codUsuario, intval($venta['cod_local']), $mysqli)) return true;
    $codVenta = intval($venta['cod_venta']);
    $stmt = $mysqli->prepare("SELECT DISTINCT lo.*
        FROM centro_legajo_documento d
        INNER JOIN centro_legajo_lote_detalle ld ON ld.id_documentoFK=d.id_documento
        INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
        WHERE d.cod_ventaFK=? AND ld.estado<>'retirado' AND lo.estado<>'anulado'
        ORDER BY lo.id_lote DESC");
    if (!$stmt) return false;
    $stmt->bind_param('i', $codVenta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $permitido = false;
    while ($lote = $resultado->fetch_assoc()) {
        if (centroLegajoPuedeAccederLote($codUsuario, $lote, $mysqli)) {
            $permitido = true;
            break;
        }
    }
    $stmt->close();
    return $permitido;
}

function centroLegajoPuedeAccederLote($codUsuario, $lote, $mysqli)
{
    if (empty($lote)) return false;
    if (centroFacturaPuedeVerTodosLocales($codUsuario)) return true;
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    $local = isset($contexto['cod_localFK']) ? intval($contexto['cod_localFK']) : 0;
    $estadoLote = strtolower(trim((string)(isset($lote['estado']) ? $lote['estado'] : '')));
    $custodiaTransportistaVigente = intval($lote['cod_usuario_transportistaFK']) === intval($codUsuario)
        && in_array($estadoLote, array('pendiente_custodia','en_transito','recibido_parcial','observado'), true);
    return $local === intval($lote['cod_local_origenFK'])
        || $local === intval($lote['cod_local_destinoFK'])
        || $custodiaTransportistaVigente;
}

function centroLegajoPuedeOperarOrigen($codUsuario, $lote, $mysqli)
{
    if (centroFacturaPuedeVerTodosLocales($codUsuario)) return true;
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    return !empty($contexto) && intval($contexto['cod_localFK']) === intval($lote['cod_local_origenFK']);
}

function centroLegajoPuedeOperarDestino($codUsuario, $lote, $mysqli)
{
    if (centroFacturaPuedeVerTodosLocales($codUsuario)) return true;
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    return !empty($contexto) && intval($contexto['cod_localFK']) === intval($lote['cod_local_destinoFK']);
}

function centroLegajoAsegurarDocumentosVenta($mysqli, $venta, $codUsuario)
{
    $codVenta = intval($venta['cod_venta']);
    $stmt = $mysqli->prepare("INSERT IGNORE INTO centro_legajo_documento
        (cod_ventaFK,tipo_documento,es_requerido,estado_documental,estado_fisico,cod_usuarioFK_create)
        VALUES (?,?,?,?,?,?)");
    if (!$stmt) return false;
    foreach (centroLegajoTipos() as $tipo) {
        $requerido = centroLegajoTipoEsRequerido($venta, $tipo) ? 1 : 0;
        $estadoDocumento = $requerido ? 'pendiente' : 'no_aplica';
        $estadoFisico = $requerido ? 'pendiente' : 'no_aplica';
        $stmt->bind_param('isissi', $codVenta, $tipo, $requerido, $estadoDocumento, $estadoFisico, $codUsuario);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT d.*,
        EXISTS(SELECT 1 FROM centro_legajo_lote_detalle ld
          INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
          WHERE ld.id_documentoFK=d.id_documento AND ld.estado<>'retirado' AND lo.estado='borrador') AS en_lote_borrador
        FROM centro_legajo_documento d WHERE d.cod_ventaFK=? ORDER BY d.id_documento FOR UPDATE");
    if (!$stmt) return false;
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) { $stmt->close(); return false; }
    $documentos = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $documentos[] = $fila;
    $stmt->close();

    foreach ($documentos as $documento) {
        $tipo = (string)$documento['tipo_documento'];
        $requerido = centroLegajoTipoEsRequerido($venta, $tipo) ? 1 : 0;
        $idDocumento = intval($documento['id_documento']);
        $nuevoDocumento = (string)$documento['estado_documental'];
        $nuevoFisico = (string)$documento['estado_fisico'];
        $requiereSincronizacion = intval($documento['es_requerido']) !== $requerido;
        if ($requerido) {
            if ($nuevoDocumento === 'no_aplica') { $nuevoDocumento = 'pendiente'; $requiereSincronizacion = true; }
            if ($nuevoFisico === 'no_aplica') { $nuevoFisico = 'pendiente'; $requiereSincronizacion = true; }
        }
        if (!$requiereSincronizacion) continue;
        // El indicador vigente funciona tambien como snapshot mientras el manifiesto
        // permanece en borrador. Asi un cambio contado/credito obliga a reconstruirlo.
        if (!empty($documento['en_lote_borrador'])) continue;
        $ahora = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("UPDATE centro_legajo_documento SET es_requerido=?,estado_documental=?,estado_fisico=?,
            cod_usuarioFK_update=?,fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_documento=?");
        if (!$stmt) return false;
        $stmt->bind_param('issisi', $requerido, $nuevoDocumento, $nuevoFisico, $codUsuario, $ahora, $idDocumento);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); return false; }
        $stmt->close();
        $detalle = $requerido
            ? 'El documento pasa a ser obligatorio por el tipo actual de la venta.'
            : 'El documento deja de ser obligatorio por el tipo actual de la venta; se conservan su estado, ubicacion e historial.';
        if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'sincronizar_tipo_venta',
            $documento['estado_documental'], $nuevoDocumento, $documento['estado_fisico'], $nuevoFisico, $detalle, $codUsuario)) return false;
    }
    return true;
}

function centroLegajoAplicarResponsabilidadDocumento($documento)
{
    $estadoLote = isset($documento['estado_lote_actual']) ? (string)$documento['estado_lote_actual'] : '';
    $estadoFisico = isset($documento['estado_fisico']) ? (string)$documento['estado_fisico'] : 'pendiente';
    $responsable = '';
    $rol = 'Responsable documental';
    if ($estadoLote === 'pendiente_custodia') {
        $responsable = isset($documento['usuario_transportista_lote']) ? $documento['usuario_transportista_lote'] : '';
        $rol = 'Transportista asignado · custodia pendiente';
    } elseif ($estadoLote === 'en_transito') {
        $responsable = !empty($documento['usuario_custodia_lote']) ? $documento['usuario_custodia_lote']
            : (isset($documento['usuario_transportista_lote']) ? $documento['usuario_transportista_lote'] : '');
        $rol = 'Custodia durante el traslado';
    } elseif (in_array($estadoLote, array('recibido','recibido_parcial','observado'), true)) {
        $responsable = isset($documento['usuario_recepcion_lote']) ? $documento['usuario_recepcion_lote'] : '';
        $rol = 'Custodia en destino';
    } elseif ($estadoLote === 'borrador') {
        $responsable = isset($documento['usuario_creador_lote']) ? $documento['usuario_creador_lote'] : '';
        $rol = 'Responsable de preparación';
    } else {
        $responsable = isset($documento['ultima_accion_usuario']) ? $documento['ultima_accion_usuario'] : '';
        if ($estadoFisico === 'en_sucursal') $rol = 'Responsable en sucursal';
        elseif ($estadoFisico === 'devuelto_cliente') $rol = 'Responsable de entrega al cliente';
    }
    $documento['responsable_actual'] = $responsable !== '' ? $responsable : 'Sin asignar';
    $documento['responsable_actual_rol'] = $rol;
    if (empty($documento['ultima_accion_fecha'])) {
        $documento['ultima_accion_fecha'] = !empty($documento['fecha_actualizacion']) ? $documento['fecha_actualizacion']
            : (!empty($documento['fecha_confirmacion']) ? $documento['fecha_confirmacion'] : (!empty($documento['fecha_creacion']) ? $documento['fecha_creacion'] : null));
    }
    if (empty($documento['ultima_accion_tipo'])) $documento['ultima_accion_tipo'] = 'actualizacion_documental';
    return $documento;
}

function centroLegajoDocumentosPorVentas($mysqli, $ventas)
{
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ventas))));
    $salida = array();
    if (count($ids) < 1) return $salida;
    $resultado = $mysqli->query("SELECT d.*,v.puntoexpedicion,v.num_factura,v.cod_local,l.Nombre AS nombre_local,pc.nombre_persona AS usuario_confirmacion,
        pu.nombre_persona AS ultima_accion_usuario,COALESCE(d.fecha_actualizacion,d.fecha_confirmacion,d.fecha_creacion) AS ultima_accion_fecha,
        lo.id_lote AS id_lote_actual,lo.estado AS estado_lote_actual,
        plc.nombre_persona AS usuario_creador_lote,plt.nombre_persona AS usuario_transportista_lote,
        plct.nombre_persona AS usuario_custodia_lote,plr.nombre_persona AS usuario_recepcion_lote
        FROM centro_legajo_documento d
        INNER JOIN venta v ON v.cod_venta=d.cod_ventaFK
        INNER JOIN local l ON l.cod_local=v.cod_local
        LEFT JOIN persona pc ON pc.cod_persona=d.cod_usuario_confirmacionFK
        LEFT JOIN persona pu ON pu.cod_persona=COALESCE(d.cod_usuarioFK_update,d.cod_usuario_confirmacionFK,d.cod_usuarioFK_create)
        LEFT JOIN centro_legajo_lote_detalle ld ON ld.id_lote_detalle=(
            SELECT ld2.id_lote_detalle FROM centro_legajo_lote_detalle ld2
            INNER JOIN centro_legajo_lote lo2 ON lo2.id_lote=ld2.id_loteFK
            WHERE ld2.id_documentoFK=d.id_documento AND ld2.estado<>'retirado' AND lo2.estado<>'anulado'
            ORDER BY ld2.id_lote_detalle DESC LIMIT 1)
        LEFT JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
        LEFT JOIN persona plc ON plc.cod_persona=lo.cod_usuarioFK_create
        LEFT JOIN persona plt ON plt.cod_persona=lo.cod_usuario_transportistaFK
        LEFT JOIN persona plct ON plct.cod_persona=lo.cod_usuario_custodiaFK
        LEFT JOIN persona plr ON plr.cod_persona=lo.cod_usuario_recepcionFK
        WHERE d.cod_ventaFK IN (".implode(',', $ids).")
        ORDER BY d.cod_ventaFK,d.id_documento");
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $venta = intval($fila['cod_ventaFK']);
            if (!isset($salida[$venta])) $salida[$venta] = array();
            $fila = centroLegajoAplicarResponsabilidadDocumento(centroFacturaFilaUtf8($fila));
            $fila['codigo_legajo'] = centroLegajoCodigoLegajo($fila);
            $fila['codigo_documento'] = centroLegajoCodigoDocumento($fila, $fila['tipo_documento']);
            $salida[$venta][$fila['tipo_documento']] = $fila;
        }
    }
    return $salida;
}

function centroLegajoLotesActivosPorVentas($mysqli, $ventas)
{
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ventas))));
    $salida = array();
    if (count($ids) < 1) return $salida;
    $camposPlazo = centroFacturaPlazoTransitoDisponible($mysqli, 'centro_legajo_lote')
        ? ',lo.dias_plazo_transito,lo.fecha_limite_recepcion,lo.cod_usuario_plazo_transitoFK,lo.fecha_asignacion_plazo'
        : ',NULL AS dias_plazo_transito,NULL AS fecha_limite_recepcion,NULL AS cod_usuario_plazo_transitoFK,NULL AS fecha_asignacion_plazo';
    $sql = "SELECT d.cod_ventaFK,lo.id_lote,lo.codigo_lote,lo.estado,lo.cod_local_origenFK,lo.cod_local_destinoFK,
        lo.destino_snapshot,lo.fecha_envio,lo.fecha_recepcion".$camposPlazo.",lor.Nombre AS nombre_local_origen,lde.Nombre AS nombre_local_destino,
        pt.nombre_persona AS transportista,pc.nombre_persona AS custodio_actual,pr.nombre_persona AS receptor
      FROM centro_legajo_lote_detalle ld
      INNER JOIN centro_legajo_documento d ON d.id_documento=ld.id_documentoFK
      INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
      INNER JOIN local lor ON lor.cod_local=lo.cod_local_origenFK
      INNER JOIN local lde ON lde.cod_local=lo.cod_local_destinoFK
      LEFT JOIN persona pt ON pt.cod_persona=lo.cod_usuario_transportistaFK
      LEFT JOIN persona pc ON pc.cod_persona=lo.cod_usuario_custodiaFK
      LEFT JOIN persona pr ON pr.cod_persona=lo.cod_usuario_recepcionFK
      WHERE d.cod_ventaFK IN (".implode(',', $ids).") AND ld.estado<>'retirado' AND lo.estado<>'anulado'
      ORDER BY lo.id_lote DESC";
    $resultado = $mysqli->query($sql);
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $venta = intval($fila['cod_ventaFK']);
            if (!isset($salida[$venta])) {
                $fila['cantidad_documentos_lote'] = 1;
                $salida[$venta] = centroFacturaFilaUtf8(centroFacturaDecorarPlazoTransito($fila));
            } elseif (intval($salida[$venta]['id_lote']) === intval($fila['id_lote'])) {
                $salida[$venta]['cantidad_documentos_lote'] = intval($salida[$venta]['cantidad_documentos_lote']) + 1;
            }
        }
    }
    return $salida;
}

function centroLegajoConstruirDocumentos($venta, $existentes)
{
    $salida = array();
    $esContado = strtoupper(trim((string)$venta['tipo_venta'])) === 'CONTADO';
    foreach (centroLegajoTipos() as $tipo) {
        $requerido = centroLegajoTipoEsRequerido($venta, $tipo);
        $documento = isset($existentes[$tipo]) ? $existentes[$tipo] : array(
            'id_documento' => 0,
            'cod_ventaFK' => intval($venta['cod_venta']),
            'tipo_documento' => $tipo,
            'es_requerido' => $requerido ? 1 : 0,
            'estado_documental' => $requerido ? 'pendiente' : 'no_aplica',
            'estado_fisico' => $requerido ? 'pendiente' : 'no_aplica',
            'ubicacion_fisica' => '',
            'usuario_confirmacion' => '',
            'fecha_confirmacion' => null,
            'observaciones' => ''
        );
        $documento['es_requerido'] = $requerido ? 1 : 0;
        if ($requerido) {
            if ((string)$documento['estado_documental'] === 'no_aplica') $documento['estado_documental'] = 'pendiente';
            if ((string)$documento['estado_fisico'] === 'no_aplica') $documento['estado_fisico'] = 'pendiente';
        }
        $documento['codigo_documento'] = centroLegajoCodigoDocumento($venta, $tipo);
        $fuente = 0;
        if ($tipo === 'cedula') $fuente = intval($venta['fuente_cedula']);
        if ($tipo === 'detalle_venta') $fuente = intval($venta['fuente_detalle_venta']);
        if ($tipo === 'pagare' && !$esContado) $fuente = 1;
        $documento['fuente_disponible'] = $fuente;
        $salida[$tipo] = $documento;
    }
    return $salida;
}

function centroLegajoDecorarVenta($venta, $existentes, $loteActual)
{
    $venta['numero_venta'] = centroLegajoNumeroVenta($venta);
    $venta['sigla_local'] = centroLegajoSiglaLocal($venta['cod_local'], $venta['nombre_local']);
    $venta['codigo_legajo'] = centroLegajoCodigoLegajo($venta);
    $documentos = centroLegajoConstruirDocumentos($venta, $existentes);
    $requeridos = 0;
    $listos = 0;
    $elegibles = 0;
    $enviables = 0;
    $observado = false;
    $responsables = array();
    $rolesResponsable = array();
    $ultimaAccion = array('fecha' => '', 'usuario' => '', 'tipo' => '');
    foreach ($documentos as $documento) {
        $estadoDocumento = (string)$documento['estado_documental'];
        $estadoFisico = (string)$documento['estado_fisico'];
        $documentoConfirmado = in_array($estadoDocumento, array('disponible','validado'), true);
        $requerido = intval($documento['es_requerido']) === 1;
        if ($requerido && $documentoConfirmado && $estadoFisico === 'en_sucursal') $enviables++;
        if (!$requerido) continue;
        $requeridos++;
        if ($documentoConfirmado
            && in_array($estadoFisico, array('en_sucursal','en_lote','pendiente_custodia','en_transito','recibido','devuelto_cliente'), true)) {
            $listos++;
        }
        if ($documentoConfirmado && $estadoFisico === 'en_sucursal') $elegibles++;
        if ($estadoDocumento === 'observado' || in_array($estadoFisico, array('faltante','observado'), true)) $observado = true;
        $nombreResponsable = isset($documento['responsable_actual']) ? trim((string)$documento['responsable_actual']) : '';
        if ($nombreResponsable !== '') $responsables[$nombreResponsable] = true;
        $rolResponsable = isset($documento['responsable_actual_rol']) ? trim((string)$documento['responsable_actual_rol']) : '';
        if ($rolResponsable !== '') $rolesResponsable[$rolResponsable] = true;
        $fechaAccion = isset($documento['ultima_accion_fecha']) ? (string)$documento['ultima_accion_fecha'] : '';
        if ($fechaAccion !== '' && ($ultimaAccion['fecha'] === '' || strcmp($fechaAccion, $ultimaAccion['fecha']) > 0)) {
            $ultimaAccion['fecha'] = $fechaAccion;
            $ultimaAccion['usuario'] = isset($documento['ultima_accion_usuario']) ? (string)$documento['ultima_accion_usuario'] : '';
            $ultimaAccion['tipo'] = isset($documento['ultima_accion_tipo']) ? (string)$documento['ultima_accion_tipo'] : 'actualizacion_documental';
        }
    }
    $completo = $requeridos > 0 && $listos === $requeridos;
    $venta['documentos'] = $documentos;
    $venta['cantidad_requerida'] = $requeridos;
    $venta['cantidad_lista'] = $listos;
    $venta['cantidad_enviable'] = $enviables;
    $venta['cantidad_documentos_lote'] = $loteActual && isset($loteActual['cantidad_documentos_lote'])
        ? intval($loteActual['cantidad_documentos_lote']) : 0;
    $venta['estado_legajo'] = $observado ? 'observado' : ($completo ? 'completo' : 'incompleto');
    $venta['lote_actual'] = $loteActual ? $loteActual : array();
    $venta['elegible_lote'] = (!intval($venta['es_anulada']) && !$loteActual && $elegibles === $requeridos) ? 1 : 0;
    $venta['custodio_actual'] = '';
    $venta['ubicacion_actual'] = '';
    if ($loteActual) {
        $estado = (string)$loteActual['estado'];
        if ($estado === 'en_transito') $venta['custodio_actual'] = $loteActual['custodio_actual'] ? $loteActual['custodio_actual'] : $loteActual['transportista'];
        elseif (in_array($estado, array('recibido','recibido_parcial','observado'), true)) $venta['custodio_actual'] = $loteActual['receptor'];
        else $venta['custodio_actual'] = $loteActual['transportista'];
        $venta['ubicacion_actual'] = $loteActual['codigo_lote'].' · '.str_replace('_', ' ', $estado);
    }
    $nombresResponsables = array_keys($responsables);
    $rolesResponsables = array_keys($rolesResponsable);
    $venta['responsable_actual'] = count($nombresResponsables) === 1 ? $nombresResponsables[0]
        : (count($nombresResponsables) > 1 ? 'Varios responsables' : 'Sin asignar');
    $venta['responsable_actual_rol'] = count($rolesResponsables) === 1 ? $rolesResponsables[0]
        : (count($rolesResponsables) > 1 ? 'Responsables según cada documento' : 'Responsable documental');
    $venta['ultima_accion_usuario'] = $ultimaAccion['usuario'];
    $venta['ultima_accion_fecha'] = $ultimaAccion['fecha'];
    $venta['ultima_accion_tipo'] = $ultimaAccion['tipo'];
    return centroFacturaFilaUtf8($venta);
}

function centroLegajoCoincideFiltro($fila, $filtros, $omitirRapido)
{
    if (!empty($filtros['busqueda'])) {
        $aguja = mb_strtolower(trim((string)$filtros['busqueda']), 'UTF-8');
        $texto = mb_strtolower(implode(' ', array(
            $fila['cod_venta'], isset($fila['num_factura']) ? $fila['num_factura'] : '',
            isset($fila['codigo_legajo']) ? $fila['codigo_legajo'] : '', $fila['titular'], $fila['documento'], $fila['nombre_local'],
            isset($fila['lote_actual']['codigo_lote']) ? $fila['lote_actual']['codigo_lote'] : ''
        )), 'UTF-8');
        $agujaDocumento = preg_replace('/[\.\-\s]+/', '', $aguja);
        $documentoNormalizado = preg_replace('/[\.\-\s]+/', '', mb_strtolower((string)$fila['documento'], 'UTF-8'));
        $coincideTexto = mb_strpos($texto, $aguja, 0, 'UTF-8') !== false;
        $coincideDocumento = $agujaDocumento !== '' && mb_strpos($documentoNormalizado, $agujaDocumento, 0, 'UTF-8') !== false;
        if (!$coincideTexto && !$coincideDocumento) return false;
    }
    if ($omitirRapido) return true;
    $rapido = isset($filtros['filtro_rapido']) ? trim((string)$filtros['filtro_rapido']) : '';
    if ($rapido === 'completos' && $fila['estado_legajo'] !== 'completo') return false;
    if ($rapido === 'incompletos' && $fila['estado_legajo'] !== 'incompleto') return false;
    if ($rapido === 'observados' && $fila['estado_legajo'] !== 'observado') return false;
    if ($rapido === 'listos_envio' && !intval($fila['elegible_lote'])) return false;
    $estadoLote = isset($fila['lote_actual']['estado']) ? $fila['lote_actual']['estado'] : '';
    if ($rapido === 'en_transito' && !in_array($estadoLote, array('pendiente_custodia','en_transito'), true)) return false;
    if ($rapido === 'recibidos' && $estadoLote !== 'recibido') return false;
    return true;
}

function centroLegajoCedulaNormalizadaSql($aliasCliente = 'c')
{
    $aliasCliente = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasCliente);
    if ($aliasCliente === '') $aliasCliente = 'c';
    return "REPLACE(REPLACE(REPLACE(TRIM(IFNULL(".$aliasCliente.".ci_cliente,'')),'.',''),'-',''),' ','')";
}

function centroLegajoAlcanceListado($filtros)
{
    $filtros = (array)$filtros;
    list($desde, $hasta) = centroFacturaRangoPeriodo($filtros);
    $busqueda = isset($filtros['busqueda']) ? trim((string)$filtros['busqueda']) : '';
    $historico = $busqueda !== '';
    return array(
        'tipo' => $historico ? 'historico_busqueda' : 'periodo',
        'historico' => $historico ? 1 : 0,
        'fecha_desde' => $historico ? null : $desde,
        'fecha_hasta' => $historico ? null : $hasta
    );
}

function centroLegajoAgregarBusquedaSql($busqueda, &$condiciones, &$tipos, &$parametros)
{
    $busqueda = trim((string)$busqueda);
    if ($busqueda === '') return;
    $busquedaBase = centroFacturaTextoBaseDatos($busqueda, 100);
    $patron = '%'.$busquedaBase.'%';
    $partes = array(
        'CAST(v.cod_venta AS CHAR) LIKE ?',
        "TRIM(IFNULL(v.num_factura,'')) LIKE ?",
        "CONCAT(CASE WHEN TRIM(IFNULL(v.puntoexpedicion,''))<>'' THEN CONCAT(TRIM(v.puntoexpedicion),'-') ELSE '' END,TRIM(IFNULL(v.num_factura,''))) LIKE ?",
        'p.nombre_persona LIKE ?',
        "TRIM(IFNULL(c.ci_cliente,'')) LIKE ?",
        'l.Nombre LIKE ?',
        "EXISTS (SELECT 1 FROM centro_legajo_documento db
          INNER JOIN centro_legajo_lote_detalle ldb ON ldb.id_documentoFK=db.id_documento
          INNER JOIN centro_legajo_lote lob ON lob.id_lote=ldb.id_loteFK
          WHERE db.cod_ventaFK=v.cod_venta AND ldb.estado<>'retirado' AND lob.estado<>'anulado'
            AND lob.codigo_lote LIKE ?)"
    );
    for ($i = 0; $i < 7; $i++) {
        $tipos .= 's';
        $parametros[] = $patron;
    }
    $documento = preg_replace('/[\.\-\s]+/', '', $busquedaBase);
    if ($documento !== '') {
        $partes[] = centroLegajoCedulaNormalizadaSql('c').' LIKE ?';
        $tipos .= 's';
        $parametros[] = '%'.$documento.'%';
    }
    $condiciones[] = '('.implode(' OR ', $partes).')';
}

function centroLegajoMetricas($registros)
{
    $metricas = array('ventas_periodo' => count($registros), 'legajos_completos' => 0, 'legajos_incompletos' => 0,
        'listos_envio' => 0, 'en_transito' => 0, 'recibidos' => 0, 'observados' => 0);
    foreach ($registros as $fila) {
        if ($fila['estado_legajo'] === 'completo') $metricas['legajos_completos']++;
        if ($fila['estado_legajo'] === 'incompleto') $metricas['legajos_incompletos']++;
        if ($fila['estado_legajo'] === 'observado') $metricas['observados']++;
        if (intval($fila['elegible_lote'])) $metricas['listos_envio']++;
        $estado = isset($fila['lote_actual']['estado']) ? $fila['lote_actual']['estado'] : '';
        if (in_array($estado, array('pendiente_custodia','en_transito'), true)) $metricas['en_transito']++;
        if ($estado === 'recibido') $metricas['recibidos']++;
    }
    return $metricas;
}

function centroLegajoListar($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroLegajoPuedeVer($codUsuario)) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar legajos de ventas.');
    if (!centroLegajoEstructuraDisponible()) return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura de legajos de ventas no esta instalada.');
    $limiteSolicitado = max(1, min(150, intval($limite)));
    $offsetSolicitado = max(0, intval($offset));
    $filtros = (array)$filtros;
    list($desde, $hasta) = centroFacturaRangoPeriodo($filtros);
    $alcance = centroLegajoAlcanceListado($filtros);
    $busquedaHistorica = intval($alcance['historico']) === 1;
    $limiteRespuesta = $busquedaHistorica ? $limiteSolicitado : 0;
    $offsetRespuesta = $busquedaHistorica ? $offsetSolicitado : 0;
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    if (empty($contexto)) { $mysqli->close(); return array('ok' => false, 'codigo' => 'contexto', 'mensaje' => 'No se pudo determinar el local del usuario.'); }
    $condiciones = array();
    $tipos = '';
    $parametros = array();
    if (!$busquedaHistorica) {
        $condiciones[] = 'v.fecha_venta>=?';
        $condiciones[] = 'v.fecha_venta<=?';
        $tipos .= 'ss';
        $parametros[] = $desde;
        $parametros[] = $hasta;
    }
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $condiciones[] = "(v.cod_local=? OR EXISTS (
            SELECT 1 FROM centro_legajo_documento da
            INNER JOIN centro_legajo_lote_detalle lda ON lda.id_documentoFK=da.id_documento
            INNER JOIN centro_legajo_lote loa ON loa.id_lote=lda.id_loteFK
            WHERE da.cod_ventaFK=v.cod_venta AND lda.estado<>'retirado' AND loa.estado<>'anulado'
              AND (loa.cod_local_destinoFK=? OR loa.cod_usuario_transportistaFK=?)))";
        $tipos .= 'iii';
        $localUsuario = intval($contexto['cod_localFK']);
        $parametros[] = $localUsuario; $parametros[] = $localUsuario; $parametros[] = intval($codUsuario);
    } elseif (!empty($filtros['cod_local'])) {
        $condiciones[] = 'v.cod_local=?'; $tipos .= 'i'; $parametros[] = intval($filtros['cod_local']);
    }
    if (empty($filtros['incluir_anuladas'])) $condiciones[] = 'NOT '.centroLegajoVentaAnuladaSql('v');
    if ($busquedaHistorica) centroLegajoAgregarBusquedaSql($filtros['busqueda'], $condiciones, $tipos, $parametros);
    $anulada = centroLegajoVentaAnuladaSql('v');
    $sqlBase = "SELECT v.cod_venta,v.puntoexpedicion,v.num_factura,v.fecha_venta,v.total_venta,v.descuento,v.TipoVenta AS tipo_venta,
        v.cod_clienteFK,v.cod_local,v.cod_usuarioFK,v.estado,v.anulado,v.estadocuenta,
        p.nombre_persona AS titular,TRIM(IFNULL(c.ci_cliente,'')) AS documento,TRIM(IFNULL(c.rut_cliente,'')) AS ruc_cliente,
        l.Nombre AS nombre_local,pu.nombre_persona AS usuario_venta,
        CASE WHEN TRIM(IFNULL(c.foto1,''))<>'' OR TRIM(IFNULL(c.foto2,''))<>'' THEN 1 ELSE 0 END AS fuente_cedula,
        CASE WHEN EXISTS (SELECT 1 FROM detalle_venta dv WHERE dv.cod_ventaFK=v.cod_venta) THEN 1 ELSE 0 END AS fuente_detalle_venta,
        CASE WHEN ".$anulada." THEN 1 ELSE 0 END AS es_anulada
      FROM venta v INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      LEFT JOIN persona pu ON pu.cod_persona=v.cod_usuarioFK
      WHERE ".implode(' AND ', $condiciones);
    $rapido = isset($filtros['filtro_rapido']) ? trim((string)$filtros['filtro_rapido']) : '';
    $requiereFiltroDerivado = in_array($rapido, array('completos','incompletos','observados','listos_envio','en_transito','recibidos'), true);
    $cedulaConteoSql = centroLegajoCedulaNormalizadaSql('c');
    $cedulaRealConteoSql = "(".$cedulaConteoSql."<>'' AND ".$cedulaConteoSql."<>'0' AND ".$cedulaConteoSql." REGEXP '^[0-9]+$')";
    $sqlConteo = "SELECT COUNT(*) AS total,
        COUNT(DISTINCT CASE WHEN ".$cedulaRealConteoSql." THEN ".$cedulaConteoSql." END) AS cedulas_unicas,
        SUM(CASE WHEN ".$cedulaRealConteoSql." THEN 0 ELSE 1 END) AS registros_sin_cedula,
        COUNT(DISTINCT v.cod_local) AS cantidad_locales,
        COALESCE(SUM(GREATEST(0,IFNULL(v.total_venta,0)-IFNULL(v.descuento,0))),0) AS importe_total
      FROM venta v INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      WHERE ".implode(' AND ', $condiciones);
    $stmtConteo = $mysqli->prepare($sqlConteo);
    if (!$stmtConteo) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo contar el listado de legajos: '.$error); }
    $parametrosConteo = $parametros;
    centroFacturaBind($stmtConteo, $tipos, $parametrosConteo);
    if (!$stmtConteo->execute()) { $error = $stmtConteo->error; $stmtConteo->close(); $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo contar el listado de legajos: '.$error); }
    $filaConteo = $stmtConteo->get_result()->fetch_assoc();
    $totalBase = $filaConteo ? intval($filaConteo['total']) : 0;
    $stmtConteo->close();
    $sinCedula = $filaConteo ? intval($filaConteo['registros_sin_cedula']) : 0;
    $totales = array(
        'cedulas_unicas' => $filaConteo ? intval($filaConteo['cedulas_unicas']) : 0,
        'cantidad_locales' => $filaConteo ? intval($filaConteo['cantidad_locales']) : 0,
        'legajos_con_cedula' => max(0, $totalBase - $sinCedula),
        'registros_sin_cedula' => $sinCedula,
        'legajos_sin_cedula' => $sinCedula,
        'cantidad_ventas' => $totalBase,
        'cantidad_legajos' => $totalBase,
        'importe_total' => $filaConteo ? floatval($filaConteo['importe_total']) : 0
    );

    if (!$busquedaHistorica) $limiteRespuesta = $totalBase;
    $limiteConsulta = $requiereFiltroDerivado ? max(1, $totalBase) : max(1, $limiteRespuesta);
    $offsetConsulta = $requiereFiltroDerivado ? 0 : $offsetRespuesta;
    $sql = $sqlBase." ORDER BY v.fecha_venta DESC,v.cod_venta DESC LIMIT ".$limiteConsulta.' OFFSET '.$offsetConsulta;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo preparar el listado de legajos: '.$error); }
    centroFacturaBind($stmt, $tipos, $parametros);
    if (!$stmt->execute()) { $error = $stmt->error; $stmt->close(); $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo consultar el listado de legajos: '.$error); }
    $ventas = array();
    $ids = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $fila['importe_venta'] = max(0, floatval($fila['total_venta']) - floatval($fila['descuento']));
        $ventas[] = centroFacturaFilaUtf8($fila);
        $ids[] = intval($fila['cod_venta']);
    }
    $stmt->close();
    $documentos = centroLegajoDocumentosPorVentas($mysqli, $ids);
    $lotes = centroLegajoLotesActivosPorVentas($mysqli, $ids);
    $mysqli->close();
    $base = array();
    foreach ($ventas as $venta) {
        $id = intval($venta['cod_venta']);
        $decorada = centroLegajoDecorarVenta($venta, isset($documentos[$id]) ? $documentos[$id] : array(), isset($lotes[$id]) ? $lotes[$id] : array());
        $base[] = $decorada;
    }

    $coberturaCompleta = $offsetConsulta === 0 && $totalBase <= $limiteConsulta;
    $metricas = centroLegajoMetricas($base);
    $metricas['ventas_periodo'] = $totalBase;
    $metricas['ventas_alcance'] = $totalBase;
    if (!$coberturaCompleta) $metricas['estados_sobre_pagina'] = 1;

    if ($requiereFiltroDerivado) {
        $filtrados = array();
        foreach ($base as $fila) if (centroLegajoCoincideFiltro($fila, $filtros, false)) $filtrados[] = $fila;
        $registros = $busquedaHistorica
            ? array_slice($filtrados, $offsetSolicitado, $limiteSolicitado)
            : $filtrados;
        $totalRespuesta = count($filtrados);
    } else {
        $registros = $base;
        $totalRespuesta = $totalBase;
    }
    return array('ok' => true, 'registros' => $registros, 'total' => $totalRespuesta,
        'total_base' => $totalBase, 'limite' => $limiteRespuesta, 'offset' => $offsetRespuesta,
        'metricas' => $metricas, 'totales' => $totales, 'alcance' => $alcance,
        'busqueda_historica' => $busquedaHistorica ? 1 : 0,
        'truncado' => 0, 'total_incompleto' => 0);
}

function centroLegajoListarPorCedula($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroLegajoPuedeVer($codUsuario)) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar legajos por cedula.');
    if (!centroLegajoEstructuraDisponible()) return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura de legajos de ventas no esta instalada.');
    $filtros = (array)$filtros;
    list($desde, $hasta) = centroFacturaRangoPeriodo($filtros);
    $alcance = centroLegajoAlcanceListado($filtros);
    $busquedaHistorica = intval($alcance['historico']) === 1;
    $limiteSolicitado = $busquedaHistorica
        ? max(1, min(150, intval($limite)))
        : max(1, min(500, intval($limite)));
    $offset = $busquedaHistorica ? max(0, intval($offset)) : 0;
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    if (empty($contexto)) { $mysqli->close(); return array('ok' => false, 'codigo' => 'contexto', 'mensaje' => 'No se pudo determinar el local del usuario.'); }

    $condiciones = array();
    $tipos = '';
    $parametros = array();
    if (!$busquedaHistorica) {
        $condiciones[] = 'v.fecha_venta>=?';
        $condiciones[] = 'v.fecha_venta<=?';
        $tipos .= 'ss';
        $parametros[] = $desde;
        $parametros[] = $hasta;
    }
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $condiciones[] = "(v.cod_local=? OR EXISTS (
            SELECT 1 FROM centro_legajo_documento da
            INNER JOIN centro_legajo_lote_detalle lda ON lda.id_documentoFK=da.id_documento
            INNER JOIN centro_legajo_lote loa ON loa.id_lote=lda.id_loteFK
            WHERE da.cod_ventaFK=v.cod_venta AND lda.estado<>'retirado' AND loa.estado<>'anulado'
              AND (loa.cod_local_destinoFK=? OR loa.cod_usuario_transportistaFK=?)))";
        $tipos .= 'iii';
        $localUsuario = intval($contexto['cod_localFK']);
        $parametros[] = $localUsuario;
        $parametros[] = $localUsuario;
        $parametros[] = intval($codUsuario);
    } elseif (!empty($filtros['cod_local'])) {
        $condiciones[] = 'v.cod_local=?';
        $tipos .= 'i';
        $parametros[] = intval($filtros['cod_local']);
    }
    if (empty($filtros['incluir_anuladas'])) $condiciones[] = 'NOT '.centroLegajoVentaAnuladaSql('v');

    $cedulaSql = centroLegajoCedulaNormalizadaSql('c');
    $cedulaRealSql = "(".$cedulaSql."<>'' AND ".$cedulaSql."<>'0' AND ".$cedulaSql." REGEXP '^[0-9]+$')";
    $condicionesAgrupadas = $condiciones;
    $condicionesAgrupadas[] = $cedulaRealSql;
    $tiposAgrupados = $tipos;
    $parametrosAgrupados = $parametros;
    $having = '';
    if ($busquedaHistorica) {
        $busquedaBase = centroFacturaTextoBaseDatos($filtros['busqueda'], 100);
        $patron = '%'.$busquedaBase.'%';
        $partesBusqueda = array(
            'CAST(v.cod_venta AS CHAR) LIKE ?',
            "TRIM(IFNULL(v.num_factura,'')) LIKE ?",
            "CONCAT(CASE WHEN TRIM(IFNULL(v.puntoexpedicion,''))<>'' THEN CONCAT(TRIM(v.puntoexpedicion),'-') ELSE '' END,TRIM(IFNULL(v.num_factura,''))) LIKE ?",
            'p.nombre_persona LIKE ?',
            "TRIM(IFNULL(c.ci_cliente,'')) LIKE ?",
            'l.Nombre LIKE ?'
        );
        for ($i = 0; $i < 6; $i++) {
            $tiposAgrupados .= 's';
            $parametrosAgrupados[] = $patron;
        }
        $documento = preg_replace('/[\.\-\s]+/', '', $busquedaBase);
        if ($documento !== '') {
            $partesBusqueda[] = $cedulaSql.' LIKE ?';
            $tiposAgrupados .= 's';
            $parametrosAgrupados[] = '%'.$documento.'%';
        }
        $having = ' HAVING MAX(CASE WHEN ('.implode(' OR ', $partesBusqueda).') THEN 1 ELSE 0 END)=1';
    }

    $sqlAgrupado = "SELECT ".$cedulaSql." AS cedula,v.cod_local,l.Nombre AS nombre_local,
        SUBSTRING_INDEX(GROUP_CONCAT(p.nombre_persona ORDER BY v.fecha_venta DESC,v.cod_venta DESC SEPARATOR '|||'),'|||',1) AS titular,
        COUNT(DISTINCT v.cod_clienteFK) AS cantidad_fichas,
        COUNT(DISTINCT v.cod_venta) AS cantidad_ventas,
        COUNT(DISTINCT v.cod_venta) AS cantidad_legajos,
        MIN(v.fecha_venta) AS primera_fecha_venta,MAX(v.fecha_venta) AS ultima_fecha_venta,
        CAST(SUBSTRING_INDEX(GROUP_CONCAT(v.cod_venta ORDER BY v.fecha_venta DESC,v.cod_venta DESC),',',1) AS UNSIGNED) AS ultima_venta,
        SUBSTRING_INDEX(GROUP_CONCAT(CONCAT(CASE WHEN TRIM(IFNULL(v.puntoexpedicion,''))<>'' THEN CONCAT(TRIM(v.puntoexpedicion),'-') ELSE '' END,
          TRIM(IFNULL(v.num_factura,''))) ORDER BY v.fecha_venta DESC,v.cod_venta DESC SEPARATOR '|||'),'|||',1) AS ultima_numero_venta,
        COALESCE(SUM(GREATEST(0,IFNULL(v.total_venta,0)-IFNULL(v.descuento,0))),0) AS importe_total,
        SUM(CASE WHEN UPPER(TRIM(IFNULL(v.TipoVenta,'')))='CONTADO' THEN 1 ELSE 0 END) AS ventas_contado,
        SUM(CASE WHEN UPPER(TRIM(IFNULL(v.TipoVenta,'')))<>'CONTADO' THEN 1 ELSE 0 END) AS ventas_credito
      FROM venta v INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      WHERE ".implode(' AND ', $condicionesAgrupadas)."
      GROUP BY v.cod_local,l.Nombre,".$cedulaSql.$having;

    $sqlTotales = "SELECT COUNT(*) AS grupos_cedula_local,COUNT(DISTINCT q.cedula) AS cedulas_unicas,
        COALESCE(SUM(q.cantidad_ventas),0) AS legajos_con_cedula,
        COALESCE(SUM(q.importe_total),0) AS importe_con_cedula,
        COUNT(DISTINCT q.cod_local) AS cantidad_locales
      FROM (".$sqlAgrupado.") q";
    $stmtTotales = $mysqli->prepare($sqlTotales);
    if (!$stmtTotales) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudieron preparar los totales por cedula: '.$error); }
    $parametrosTotales = $parametrosAgrupados;
    centroFacturaBind($stmtTotales, $tiposAgrupados, $parametrosTotales);
    if (!$stmtTotales->execute()) { $error = $stmtTotales->error; $stmtTotales->close(); $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudieron calcular los totales por cedula: '.$error); }
    $totalesRaw = $stmtTotales->get_result()->fetch_assoc();
    $stmtTotales->close();

    $condicionesSinCedula = $condiciones;
    $condicionesSinCedula[] = 'NOT '.$cedulaRealSql;
    $tiposSinCedula = $tipos;
    $parametrosSinCedula = $parametros;
    if ($busquedaHistorica) centroLegajoAgregarBusquedaSql($filtros['busqueda'], $condicionesSinCedula, $tiposSinCedula, $parametrosSinCedula);
    $sqlSinCedula = "SELECT COUNT(DISTINCT v.cod_venta) AS registros_sin_cedula,
        COALESCE(SUM(GREATEST(0,IFNULL(v.total_venta,0)-IFNULL(v.descuento,0))),0) AS importe_sin_cedula
      FROM venta v INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      WHERE ".implode(' AND ', $condicionesSinCedula);
    $stmtSinCedula = $mysqli->prepare($sqlSinCedula);
    if (!$stmtSinCedula) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudieron preparar los totales sin cedula: '.$error); }
    centroFacturaBind($stmtSinCedula, $tiposSinCedula, $parametrosSinCedula);
    if (!$stmtSinCedula->execute()) { $error = $stmtSinCedula->error; $stmtSinCedula->close(); $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudieron calcular los totales sin cedula: '.$error); }
    $sinCedulaRaw = $stmtSinCedula->get_result()->fetch_assoc();
    $stmtSinCedula->close();

    $grupos = $totalesRaw ? intval($totalesRaw['grupos_cedula_local']) : 0;
    $limiteRespuesta = $busquedaHistorica ? $limiteSolicitado : $grupos;
    $limiteConsulta = max(1, $limiteRespuesta);
    $sqlPagina = $sqlAgrupado.' ORDER BY ultima_fecha_venta DESC,ultima_venta DESC LIMIT '.$limiteConsulta.' OFFSET '.$offset;
    $stmtPagina = $mysqli->prepare($sqlPagina);
    if (!$stmtPagina) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo preparar el listado por cedula: '.$error); }
    centroFacturaBind($stmtPagina, $tiposAgrupados, $parametrosAgrupados);
    if (!$stmtPagina->execute()) { $error = $stmtPagina->error; $stmtPagina->close(); $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo consultar el listado por cedula: '.$error); }
    $registros = array();
    $resultado = $stmtPagina->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $fila['cod_local'] = intval($fila['cod_local']);
        $fila['cantidad_fichas'] = intval($fila['cantidad_fichas']);
        $fila['cantidad_ventas'] = intval($fila['cantidad_ventas']);
        $fila['cantidad_legajos'] = intval($fila['cantidad_legajos']);
        $fila['ultima_venta'] = intval($fila['ultima_venta']);
        $fila['ultima_numero_venta'] = trim((string)$fila['ultima_numero_venta']);
        $fila['importe_total'] = floatval($fila['importe_total']);
        $fila['ventas_contado'] = intval($fila['ventas_contado']);
        $fila['ventas_credito'] = intval($fila['ventas_credito']);
        $fila['documento'] = $fila['cedula'];
        $registros[] = centroFacturaFilaUtf8($fila);
    }
    $stmtPagina->close();
    $mysqli->close();

    $legajosConCedula = $totalesRaw ? intval($totalesRaw['legajos_con_cedula']) : 0;
    $sinCedula = $sinCedulaRaw ? intval($sinCedulaRaw['registros_sin_cedula']) : 0;
    $importeConCedula = $totalesRaw ? floatval($totalesRaw['importe_con_cedula']) : 0;
    $importeSinCedula = $sinCedulaRaw ? floatval($sinCedulaRaw['importe_sin_cedula']) : 0;
    $totales = array(
        'grupos_cedula_local' => $grupos,
        'cedulas_unicas' => $totalesRaw ? intval($totalesRaw['cedulas_unicas']) : 0,
        'cantidad_locales' => $totalesRaw ? intval($totalesRaw['cantidad_locales']) : 0,
        'legajos_con_cedula' => $legajosConCedula,
        'registros_sin_cedula' => $sinCedula,
        'legajos_sin_cedula' => $sinCedula,
        'cantidad_ventas' => $legajosConCedula + $sinCedula,
        'cantidad_legajos' => $legajosConCedula + $sinCedula,
        'importe_total' => $importeConCedula + $importeSinCedula
    );
    return array('ok' => true, 'registros' => $registros, 'total' => $grupos,
        'limite' => $limiteRespuesta, 'offset' => $offset, 'totales' => $totales,
        'metricas' => $totales, 'alcance' => $alcance,
        'busqueda_historica' => $busquedaHistorica ? 1 : 0, 'truncado' => 0);
}

function centroLegajoDetalle($codVenta, $codUsuario)
{
    if (!centroLegajoPuedeVer($codUsuario)) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar el legajo.');
    $mysqli = conectar_al_servidor();
    if (!centroLegajoEstructuraDisponible($mysqli)) { $mysqli->close(); return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura de legajos no esta instalada.'); }
    $venta = centroLegajoVentaRaw($mysqli, $codVenta);
    if (!$venta || !centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) { $mysqli->close(); return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'El legajo no existe o pertenece a otro local.'); }
    $documentos = centroLegajoDocumentosPorVentas($mysqli, array($codVenta));
    $lotes = centroLegajoLotesActivosPorVentas($mysqli, array($codVenta));
    $id = intval($codVenta);
    $venta = centroLegajoDecorarVenta($venta, isset($documentos[$id]) ? $documentos[$id] : array(), isset($lotes[$id]) ? $lotes[$id] : array());
    $eventos = array();
    $stmt = $mysqli->prepare("SELECT e.*,p.nombre_persona AS usuario_actor,d.tipo_documento
        FROM centro_legajo_documento_evento e
        INNER JOIN centro_legajo_documento d ON d.id_documento=e.id_documentoFK
        LEFT JOIN persona p ON p.cod_persona=e.cod_usuario_actorFK
        WHERE e.cod_ventaFK=? ORDER BY e.id_evento DESC LIMIT 150");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $fila = centroFacturaFilaUtf8($fila);
        $fila['codigo_documento'] = centroLegajoCodigoDocumento($venta, $fila['tipo_documento']);
        $fila['codigo_legajo'] = $venta['codigo_legajo'];
        $eventos[] = $fila;
    }
    $stmt->close();
    $mysqli->close();
    return array('ok' => true, 'venta' => $venta, 'documentos' => $venta['documentos'],
        'lote_actual' => $venta['lote_actual'], 'eventos' => $eventos);
}

function centroLegajoRegistrarEventoDocumento($mysqli, $documento, $accion, $anteriorDocumento, $nuevoDocumento, $anteriorFisico, $nuevoFisico, $detalle, $codUsuario)
{
    $detalle = centroFacturaTextoBaseDatos($detalle, 3000, true);
    $stmt = $mysqli->prepare("INSERT INTO centro_legajo_documento_evento
        (id_documentoFK,cod_ventaFK,accion,estado_documental_anterior,estado_documental_nuevo,
         estado_fisico_anterior,estado_fisico_nuevo,detalle,cod_usuario_actorFK)
        VALUES (?,?,?,?,?,?,?,?,?)");
    if (!$stmt) return false;
    $idDocumento = intval($documento['id_documento']);
    $codVenta = intval($documento['cod_ventaFK']);
    $stmt->bind_param('iissssssi', $idDocumento, $codVenta, $accion, $anteriorDocumento, $nuevoDocumento,
        $anteriorFisico, $nuevoFisico, $detalle, $codUsuario);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centroLegajoGuardarDocumento($codVenta, $tipoDocumento, $accion, $observaciones, $codUsuario)
{
    if (!centroLegajoTienePermiso($codUsuario, 'GESTIONARLEGAJOSVENTA')) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para actualizar documentos de legajos.');
    $tipoDocumento = centroFacturaTextoBaseDatos($tipoDocumento, 30);
    $accion = centroFacturaTextoBaseDatos($accion, 30);
    $observaciones = centroFacturaTextoBaseDatos($observaciones, 3000, true);
    if (!in_array($tipoDocumento, centroLegajoTipos(), true) || !in_array($accion, array('confirmar_copia','marcar_pendiente','observar'), true)) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'La accion o el tipo documental no son validos.');
    }
    if ($accion !== 'confirmar_copia' && $observaciones === '') return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Ingrese el motivo u observacion.');
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $codVenta = intval($codVenta);
        $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM venta WHERE cod_venta=? FOR UPDATE');
        $stmt->bind_param('i', $codVenta);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo validar la venta.'); }
        if (!$stmt->get_result()->fetch_assoc()) { $stmt->close(); throw new Exception('La venta no existe.'); }
        $stmt->close();
        $venta = centroLegajoVentaRaw($mysqli, $codVenta);
        if (!$venta || !centroLegajoPuedeUsarVenta($codUsuario, $venta, $mysqli)) throw new Exception('No puede actualizar el legajo de otro local.');
        if (intval($venta['es_anulada'])) throw new Exception('Una venta anulada se conserva solo para consulta.');
        $requerido = centroLegajoTipoEsRequerido($venta, $tipoDocumento);
        $documentoPreexistente = array();
        if (!$requerido) {
            if ($accion === 'confirmar_copia') {
                throw new Exception('Este documento no es obligatorio para el tipo de venta y no admite nuevas confirmaciones.');
            }
            $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT * FROM centro_legajo_documento WHERE cod_ventaFK=? AND tipo_documento=? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('is', $codVenta, $tipoDocumento);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo validar el antecedente documental.'); }
            $documentoPreexistente = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$documentoPreexistente || !centroLegajoDocumentoConservaAntecedente($documentoPreexistente)) {
                throw new Exception('El documento no es obligatorio y no existe una copia historica que permita registrar esta accion.');
            }
        }
        if ($requerido && !centroLegajoAsegurarDocumentosVenta($mysqli, $venta, $codUsuario)) throw new Exception('No se pudieron preparar los documentos del legajo.');
        if ($documentoPreexistente) {
            $documento = $documentoPreexistente;
        } else {
            $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT * FROM centro_legajo_documento WHERE cod_ventaFK=? AND tipo_documento=? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('is', $codVenta, $tipoDocumento);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo preparar el documento.'); }
            $documento = $stmt->get_result()->fetch_assoc(); $stmt->close();
        }
        if (!$documento) throw new Exception('El documento no existe en el legajo.');
        if ($documento['estado_fisico'] === 'devuelto_cliente') throw new Exception('Un documento devuelto al cliente solo puede modificarse mediante su flujo de devolucion.');
        $idDocumento = intval($documento['id_documento']);
        $stmt = centroLegajoPrepararEscritura($mysqli, "SELECT lo.codigo_lote FROM centro_legajo_lote_detalle ld
            INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
            WHERE ld.id_documentoFK=? AND ld.estado<>'retirado' AND lo.estado<>'anulado' LIMIT 1");
        $stmt->bind_param('i', $idDocumento);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo validar la disponibilidad del documento.'); }
        $ocupado = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($ocupado) throw new Exception('El documento ya pertenece al lote '.centroFacturaValorUtf8($ocupado['codigo_lote']).'.');
        $anteriorDocumento = $documento['estado_documental'];
        $anteriorFisico = $documento['estado_fisico'];
        if ($accion === 'confirmar_copia') { $nuevoDocumento = 'disponible'; $nuevoFisico = 'en_sucursal'; $local = intval($venta['cod_local']); }
        elseif ($accion === 'marcar_pendiente') {
            $requerido = centroLegajoTipoEsRequerido($venta, $tipoDocumento);
            $nuevoDocumento = $requerido ? 'pendiente' : 'no_aplica';
            $nuevoFisico = $requerido ? 'pendiente' : 'no_aplica';
            $local = null;
        }
        else { $nuevoDocumento = 'observado'; $nuevoFisico = 'observado'; $local = intval($venta['cod_local']); }
        $ahora = date('Y-m-d H:i:s');
        $ubicacion = $local ? centroFacturaTextoBaseDatos($venta['nombre_local'], 255) : null;
        if ($accion === 'confirmar_copia') {
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_documental=?,estado_fisico=?,cod_local_ubicacionFK=?,
                ubicacion_fisica=?,observaciones=?,cod_usuario_confirmacionFK=?,fecha_confirmacion=?,cod_usuarioFK_update=?,
                fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_documento=?");
            $stmt->bind_param('ssissisisi', $nuevoDocumento, $nuevoFisico, $local, $ubicacion, $observaciones,
                $codUsuario, $ahora, $codUsuario, $ahora, $idDocumento);
        } else {
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_documental=?,estado_fisico=?,cod_local_ubicacionFK=?,
                ubicacion_fisica=?,observaciones=?,cod_usuarioFK_update=?,fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_documento=?");
            $stmt->bind_param('ssissisi', $nuevoDocumento, $nuevoFisico, $local, $ubicacion, $observaciones,
                $codUsuario, $ahora, $idDocumento);
        }
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo actualizar el documento.'); }
        $stmt->close();
        if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, $accion, $anteriorDocumento, $nuevoDocumento,
            $anteriorFisico, $nuevoFisico, $observaciones, $codUsuario)) throw new Exception('No se pudo auditar el cambio documental.');
        $mysqli->commit(); $mysqli->close();
        return array('ok' => true, 'cod_venta' => $codVenta, 'id_documento' => $idDocumento,
            'codigo_documento' => centroLegajoCodigoDocumento($venta, $tipoDocumento),
            'estado_documental' => $nuevoDocumento, 'estado_fisico' => $nuevoFisico);
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'legajo', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoLoteRaw($mysqli, $idLote, $bloquear = false)
{
    $idLote = intval($idLote);
    $stmt = $mysqli->prepare('SELECT * FROM centro_legajo_lote WHERE id_lote=? LIMIT 1'.($bloquear ? ' FOR UPDATE' : ''));
    if (!$stmt) return array();
    $stmt->bind_param('i', $idLote);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroLegajoRegistrarEventoLote($mysqli, $idLote, $tipo, $anterior, $nuevo, $detalle, $codUsuario, $codResponsable = null)
{
    $tipo = centroFacturaTextoBaseDatos($tipo, 50);
    $anterior = centroFacturaTextoBaseDatos($anterior, 30);
    $nuevo = centroFacturaTextoBaseDatos($nuevo, 30);
    $detalle = centroFacturaTextoBaseDatos($detalle, 3000, true);
    $idLote = intval($idLote);
    $responsable = $codResponsable ? intval($codResponsable) : null;
    $stmt = $mysqli->prepare("INSERT INTO centro_legajo_lote_evento
        (id_loteFK,tipo_evento,estado_anterior,estado_nuevo,detalle,cod_usuario_actorFK,cod_usuario_responsableFK)
        VALUES (?,?,?,?,?,?,?)");
    if (!$stmt) return false;
    $stmt->bind_param('issssii', $idLote, $tipo, $anterior, $nuevo, $detalle, $codUsuario, $responsable);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centroLegajoListarLotes($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroLegajoPuedeVer($codUsuario)) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar lotes de legajos.');
    if (!centroLegajoEstructuraDisponible()) return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura de lotes de legajos no esta instalada.');
    $limite = max(1, min(150, intval($limite)));
    $offset = max(0, intval($offset));
    $filtros = (array)$filtros;
    list($desde, $hasta) = centroFacturaRangoPeriodo($filtros);
    $desde .= ' 00:00:00';
    $hasta .= ' 23:59:59';
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    $condiciones = array('lo.fecha_creacion>=?', 'lo.fecha_creacion<=?');
    $tipos = 'ss';
    $parametros = array($desde, $hasta);
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $condiciones[] = '(lo.cod_local_origenFK=? OR lo.cod_local_destinoFK=? OR lo.cod_usuario_transportistaFK=?)';
        $local = intval($contexto['cod_localFK']);
        $tipos .= 'iii';
        $parametros[] = $local; $parametros[] = $local; $parametros[] = intval($codUsuario);
    } elseif (!empty($filtros['cod_local'])) {
        $condiciones[] = '(lo.cod_local_origenFK=? OR lo.cod_local_destinoFK=?)';
        $local = intval($filtros['cod_local']);
        $tipos .= 'ii'; $parametros[] = $local; $parametros[] = $local;
    }
    $estado = isset($filtros['estado']) ? trim((string)$filtros['estado']) : '';
    if ($estado === '' && isset($filtros['filtro_rapido']) && in_array($filtros['filtro_rapido'], array('borrador','pendiente_custodia','en_transito','recibido_parcial','recibido','observado','anulado'), true)) {
        $estado = $filtros['filtro_rapido'];
    }
    if (in_array($estado, array('borrador','pendiente_custodia','en_transito','recibido_parcial','recibido','observado','anulado'), true)) {
        $condiciones[] = 'lo.estado=?'; $tipos .= 's'; $parametros[] = $estado;
    }
    if (!empty($filtros['busqueda'])) {
        $patron = '%'.centroFacturaTextoBaseDatos($filtros['busqueda'], 100).'%';
        $condiciones[] = '(lo.codigo_lote LIKE ? OR lo.destino_snapshot LIKE ? OR lor.Nombre LIKE ? OR lde.Nombre LIKE ? OR pt.nombre_persona LIKE ?)';
        $tipos .= 'sssss';
        for ($i = 0; $i < 5; $i++) $parametros[] = $patron;
    }
    $sql = "SELECT lo.*,lor.Nombre AS nombre_local_origen,lde.Nombre AS nombre_local_destino,
        pc.nombre_persona AS usuario_creador,pt.nombre_persona AS usuario_transportista,
        pe.nombre_persona AS usuario_envio,pct.nombre_persona AS usuario_custodia,pr.nombre_persona AS usuario_recepcion,
        pu.nombre_persona AS usuario_ultima_accion,
        (SELECT COALESCE(SUM(GREATEST(0,IFNULL(vr.total_venta,0)-IFNULL(vr.descuento,0))),0)
         FROM venta vr WHERE EXISTS (
             SELECT 1 FROM centro_legajo_lote_detalle ldr
             WHERE ldr.id_loteFK=lo.id_lote AND ldr.cod_ventaFK=vr.cod_venta AND ldr.estado<>'retirado'
         )) AS importe_ventas,
        COUNT(DISTINCT CASE WHEN ld.estado<>'retirado' THEN ld.cod_ventaFK END) AS cantidad_legajos,
        SUM(CASE WHEN ld.estado<>'retirado' THEN 1 ELSE 0 END) AS cantidad_documentos,
        SUM(CASE WHEN ld.estado='recibido' THEN 1 ELSE 0 END) AS cantidad_recibidos,
        SUM(CASE WHEN ld.estado IN ('faltante','observado') THEN 1 ELSE 0 END) AS cantidad_observados
      FROM centro_legajo_lote lo
      INNER JOIN local lor ON lor.cod_local=lo.cod_local_origenFK
      INNER JOIN local lde ON lde.cod_local=lo.cod_local_destinoFK
      LEFT JOIN persona pc ON pc.cod_persona=lo.cod_usuarioFK_create
      LEFT JOIN persona pt ON pt.cod_persona=lo.cod_usuario_transportistaFK
      LEFT JOIN persona pe ON pe.cod_persona=lo.cod_usuario_envioFK
      LEFT JOIN persona pct ON pct.cod_persona=lo.cod_usuario_custodiaFK
      LEFT JOIN persona pr ON pr.cod_persona=lo.cod_usuario_recepcionFK
      LEFT JOIN persona pu ON pu.cod_persona=COALESCE(lo.cod_usuarioFK_update,lo.cod_usuarioFK_create)
      LEFT JOIN centro_legajo_lote_detalle ld ON ld.id_loteFK=lo.id_lote
      WHERE ".implode(' AND ', $condiciones)." GROUP BY lo.id_lote ORDER BY lo.fecha_creacion DESC,lo.id_lote DESC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { $error = $mysqli->error; $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo preparar el listado de lotes: '.$error); }
    centroFacturaBind($stmt, $tipos, $parametros);
    $stmt->execute();
    $registros = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        if ($fila['estado'] === 'en_transito') $fila['custodio_actual'] = $fila['usuario_custodia'] ? $fila['usuario_custodia'] : $fila['usuario_transportista'];
        elseif (in_array($fila['estado'], array('recibido','recibido_parcial','observado'), true)) $fila['custodio_actual'] = $fila['usuario_recepcion'];
        elseif ($fila['estado'] === 'pendiente_custodia') $fila['custodio_actual'] = 'Pendiente de aceptacion';
        else $fila['custodio_actual'] = $fila['usuario_creador'];
        if ($fila['estado'] === 'pendiente_custodia') {
            $fila['responsable_actual'] = $fila['usuario_transportista'];
            $fila['responsable_actual_rol'] = 'Transportista asignado · custodia pendiente';
        } elseif ($fila['estado'] === 'en_transito') {
            $fila['responsable_actual'] = $fila['usuario_custodia'] ? $fila['usuario_custodia'] : $fila['usuario_transportista'];
            $fila['responsable_actual_rol'] = 'Custodia durante el traslado';
        } elseif (in_array($fila['estado'], array('recibido','recibido_parcial','observado'), true)) {
            $fila['responsable_actual'] = $fila['usuario_recepcion'];
            $fila['responsable_actual_rol'] = 'Custodia en destino';
        } elseif ($fila['estado'] === 'anulado') {
            $fila['responsable_actual'] = $fila['usuario_ultima_accion'];
            $fila['responsable_actual_rol'] = 'Responsable del cierre';
        } else {
            $fila['responsable_actual'] = $fila['usuario_creador'];
            $fila['responsable_actual_rol'] = 'Responsable de preparación';
        }
        if (empty($fila['responsable_actual'])) $fila['responsable_actual'] = 'Sin asignar';
        $fila['ultima_accion_usuario'] = $fila['usuario_ultima_accion'];
        $fila['ultima_accion_fecha'] = !empty($fila['fecha_actualizacion']) ? $fila['fecha_actualizacion'] : $fila['fecha_creacion'];
        $fila['ultima_accion_tipo'] = 'estado_'.$fila['estado'];
        $fila = centroFacturaDecorarPlazoTransito($fila);
        $registros[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $mysqli->close();
    return array('ok' => true, 'registros' => array_slice($registros, $offset, $limite), 'total' => count($registros),
        'limite' => $limite, 'offset' => $offset, 'metricas' => array());
}

function centroLegajoValidarVentaParaLote($mysqli, $codVenta, $codLocal, $codUsuario)
{
    $codVenta = intval($codVenta);
    $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM venta WHERE cod_venta=? FOR UPDATE');
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) { $stmt->close(); return array('ok' => false, 'mensaje' => 'No se pudo validar una venta.'); }
    $existe = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$existe) return array('ok' => false, 'mensaje' => 'Una de las ventas no existe.');
    $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM cancelaciones WHERE cod_venta=? FOR UPDATE');
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) { $stmt->close(); return array('ok' => false, 'mensaje' => 'No se pudo validar la vigencia de una venta.'); }
    $stmt->get_result(); $stmt->close();
    $venta = centroLegajoVentaRaw($mysqli, $codVenta);
    if (!$venta || intval($venta['cod_local']) !== intval($codLocal)) return array('ok' => false, 'mensaje' => 'Todos los legajos deben pertenecer al mismo local de origen.');
    $codigoLegajo = centroLegajoCodigoLegajo($venta);
    if (intval($venta['es_anulada'])) return array('ok' => false, 'mensaje' => 'Una venta anulada no puede enviarse.');
    if (!centroLegajoAsegurarDocumentosVenta($mysqli, $venta, $codUsuario)) return array('ok' => false, 'mensaje' => 'No se pudieron preparar los documentos del legajo.');
    $stmt = centroLegajoPrepararEscritura($mysqli, "SELECT lo.codigo_lote FROM centro_legajo_lote_detalle ld
        INNER JOIN centro_legajo_documento d ON d.id_documento=ld.id_documentoFK
        INNER JOIN centro_legajo_lote lo ON lo.id_lote=ld.id_loteFK
        WHERE d.cod_ventaFK=? AND ld.estado<>'retirado' AND lo.estado<>'anulado' LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) { $stmt->close(); return array('ok' => false, 'mensaje' => 'No se pudo validar si el legajo ya pertenece a otro lote.'); }
    $ocupado = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($ocupado) return array('ok' => false, 'mensaje' => 'El Legajo '.$codigoLegajo.' ya pertenece al lote '.centroFacturaValorUtf8($ocupado['codigo_lote']).'.');
    $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT * FROM centro_legajo_documento WHERE cod_ventaFK=? ORDER BY id_documento FOR UPDATE');
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) { $stmt->close(); return array('ok' => false, 'mensaje' => 'No se pudieron validar los documentos del legajo.'); }
    $resultado = $stmt->get_result();
    $documentos = array();
    $tiposEncontrados = array();
    $idPagareConfirmado = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $tipo = (string)$fila['tipo_documento'];
        $requerido = centroLegajoTipoEsRequerido($venta, $tipo);
        $confirmadoEnSucursal = in_array($fila['estado_documental'], array('disponible','validado'), true)
            && $fila['estado_fisico'] === 'en_sucursal';
        if ($requerido && !$confirmadoEnSucursal) {
            $stmt->close();
            return array('ok' => false, 'mensaje' => 'El Legajo '.$codigoLegajo.' no esta completo o tiene copias fisicas pendientes.');
        }
        if ($requerido) $tiposEncontrados[] = $tipo;
        if ($requerido && $confirmadoEnSucursal) {
            $documentos[] = $fila;
            if ($tipo === 'pagare') $idPagareConfirmado = intval($fila['id_documento']);
        }
    }
    $stmt->close();
    if ($idPagareConfirmado > 0) {
        $solicitudPagare = centroLegajoSolicitudPagareAbiertaDocumento($mysqli, $idPagareConfirmado, true);
        if ($solicitudPagare) {
            return array('ok' => false, 'mensaje' => 'El Legajo '.$codigoLegajo.' tiene una solicitud activa de devolucion de pagare. Resuelvala o cancelela antes de formar un lote.');
        }
    }
    $tiposRequeridos = centroLegajoTiposRequeridosVenta($venta);
    sort($tiposEncontrados); sort($tiposRequeridos);
    if ($tiposEncontrados !== $tiposRequeridos) return array('ok' => false, 'mensaje' => 'El Legajo '.$codigoLegajo.' no contiene todas sus copias obligatorias.');
    return array('ok' => true, 'venta' => $venta, 'documentos' => $documentos);
}

function centroLegajoRevalidarLoteAntesEnvio($mysqli, $documentos)
{
    $porVenta = array();
    foreach ($documentos as $documento) {
        $codVenta = intval($documento['cod_ventaFK']);
        if (!isset($porVenta[$codVenta])) $porVenta[$codVenta] = array();
        $porVenta[$codVenta][] = $documento;
    }
    foreach ($porVenta as $codVenta => $documentosVenta) {
        $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM venta WHERE cod_venta=? FOR UPDATE');
        $stmt->bind_param('i', $codVenta);
        if (!$stmt->execute() || !$stmt->get_result()->fetch_assoc()) { $stmt->close(); throw new Exception('Una venta del lote ya no existe.'); }
        $stmt->close();
        $stmt = centroLegajoPrepararEscritura($mysqli, 'SELECT cod_venta FROM cancelaciones WHERE cod_venta=? FOR UPDATE');
        $stmt->bind_param('i', $codVenta);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo validar la vigencia de una venta.'); }
        $stmt->get_result(); $stmt->close();
        $venta = centroLegajoVentaRaw($mysqli, $codVenta);
        $codigoLegajo = $venta ? centroLegajoCodigoLegajo($venta) : (string)$codVenta;
        if (!$venta || intval($venta['es_anulada'])) throw new Exception('El Legajo '.$codigoLegajo.' pertenece a una venta anulada o inactiva. Retire el lote antes de enviarlo.');
        $requeridos = centroLegajoTiposRequeridosVenta($venta);
        $presentes = array();
        $incluidos = array();
        foreach ($documentosVenta as $documento) {
            $tipo = (string)$documento['tipo_documento'];
            if (!in_array($tipo, centroLegajoTipos(), true) || isset($incluidos[$tipo])) {
                throw new Exception('El tipo de venta del Legajo #'.$codVenta.' cambio despues de preparar el lote. Anule el borrador y vuelva a formarlo.');
            }
            $requeridoActual = in_array($tipo, $requeridos, true) ? 1 : 0;
            if (!$requeridoActual) {
                throw new Exception('El Legajo #'.$codVenta.' contiene una copia que ya no es obligatoria. Anule el borrador y vuelva a formarlo.');
            }
            if (intval($documento['es_requerido']) !== $requeridoActual) {
                throw new Exception('El tipo de venta del Legajo #'.$codVenta.' cambio despues de preparar el lote. Anule el borrador y vuelva a formarlo.');
            }
            if ($tipo === 'pagare') {
                $solicitudPagare = centroLegajoSolicitudPagareAbiertaDocumento($mysqli, intval($documento['id_documento']), true);
                if ($solicitudPagare) {
                    $mismoLote = (string)$solicitudPagare['estado_fisico_snapshot'] === 'en_lote'
                        && intval($solicitudPagare['id_lote_snapshotFK']) === intval($documento['id_loteFK']);
                    if (!$mismoLote) {
                        throw new Exception('El Legajo #'.$codVenta.' tiene una solicitud activa de devolucion de pagare incompatible con este envio. Anule el borrador o resuelva la solicitud.');
                    }
                }
            }
            $incluidos[$tipo] = 1;
            if (in_array($tipo, $requeridos, true)) $presentes[$tipo] = 1;
        }
        foreach ($requeridos as $tipo) {
            if (!isset($presentes[$tipo])) throw new Exception('El Legajo #'.$codVenta.' ya no contiene todos los documentos obligatorios. Anule el borrador y vuelva a formarlo.');
        }
    }
    return true;
}

function centroLegajoCrearLote($codLocal, $ventas, $datos, $codUsuario)
{
    if (!centroLegajoTienePermiso($codUsuario, 'GESTIONARLOTESLEGAJOS')) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para crear lotes de legajos.');
    $codLocal = intval($codLocal);
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ventas))));
    if (count($ids) < 1 || count($ids) > 100) return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'Seleccione entre 1 y 100 legajos.');
    $destino = isset($datos['cod_local_destino']) ? intval($datos['cod_local_destino']) : 0;
    $transportista = isset($datos['cod_usuario_transportista']) ? intval($datos['cod_usuario_transportista']) : 0;
    $observaciones = isset($datos['observaciones']) ? centroFacturaTextoBaseDatos($datos['observaciones'], 3000, true) : '';
    if (!$destino || $destino === $codLocal) return array('ok' => false, 'codigo' => 'destino', 'mensaje' => 'Seleccione un local de destino diferente al origen.');
    if (!$transportista) return array('ok' => false, 'codigo' => 'transportista', 'mensaje' => 'Seleccione el transportista que aceptara la custodia.');
    $mysqli = conectar_al_servidor();
    if (!centroFacturaPuedeUsarLocal($codUsuario, $codLocal, $mysqli)) { $mysqli->close(); return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'No puede preparar legajos de este local.'); }
    $stmt = $mysqli->prepare("SELECT cod_local FROM local WHERE cod_local=? AND estado='Activo' LIMIT 1");
    if (!$stmt) { $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo validar el local de origen.'); }
    $stmt->bind_param('i', $codLocal); $stmt->execute(); $localOrigen = $stmt->get_result()->fetch_assoc(); $stmt->close();
    $stmt = $mysqli->prepare("SELECT cod_local,Nombre FROM local WHERE cod_local=? AND estado='Activo' LIMIT 1");
    if (!$stmt) { $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo validar el local de destino.'); }
    $stmt->bind_param('i', $destino); $stmt->execute(); $localDestino = $stmt->get_result()->fetch_assoc(); $stmt->close();
    $stmt = $mysqli->prepare("SELECT u.cod_usuario FROM usuario u WHERE u.cod_usuario=? AND u.estado='Activo' LIMIT 1");
    if (!$stmt) { $mysqli->close(); return array('ok' => false, 'codigo' => 'sql', 'mensaje' => 'No se pudo validar el transportista.'); }
    $stmt->bind_param('i', $transportista); $stmt->execute(); $usuarioTransporte = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$localOrigen || !$localDestino || !$usuarioTransporte) { $mysqli->close(); return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'El origen, el destino o el transportista ya no estan activos.'); }
    if (!centroLegajoTienePermiso($transportista, 'ENVIARLOTELEGAJOS')) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'transportista_permiso', 'mensaje' => 'El transportista seleccionado no tiene permiso para aceptar la custodia de legajos.');
    }
    $mysqli->begin_transaction();
    try {
        $documentos = array();
        foreach ($ids as $codVenta) {
            $validacion = centroLegajoValidarVentaParaLote($mysqli, $codVenta, $codLocal, $codUsuario);
            if (empty($validacion['ok'])) throw new Exception($validacion['mensaje']);
            foreach ($validacion['documentos'] as $documento) $documentos[] = $documento;
        }
        $temporal = 'TMP-LV-'.date('YmdHis').'-'.mt_rand(1000, 9999);
        $destinoSnapshot = centroFacturaTextoBaseDatos($localDestino['Nombre'], 150);
        $ahora = date('Y-m-d H:i:s');
        $stmt = centroLegajoPrepararEscritura($mysqli, "INSERT INTO centro_legajo_lote
            (codigo_lote,cod_local_origenFK,cod_local_destinoFK,destino_snapshot,estado,observaciones,
             cod_usuario_transportistaFK,fecha_asignacion_transportista,cod_usuarioFK_create)
            VALUES (?,?,?,?,'borrador',?,?,?,?)");
        $stmt->bind_param('siissisi', $temporal, $codLocal, $destino, $destinoSnapshot, $observaciones, $transportista, $ahora, $codUsuario);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo crear el lote de legajos.'); }
        $idLote = intval($stmt->insert_id); $stmt->close();
        $codigo = 'LV-'.$codLocal.'-'.$destino.'-'.date('Ymd').'-'.str_pad((string)$idLote, 5, '0', STR_PAD_LEFT);
        $stmt = centroLegajoPrepararEscritura($mysqli, 'UPDATE centro_legajo_lote SET codigo_lote=? WHERE id_lote=?');
        $stmt->bind_param('si', $codigo, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo asignar el codigo del lote.'); }
        $stmt->close();
        $stmtDetalle = centroLegajoPrepararEscritura($mysqli, "INSERT INTO centro_legajo_lote_detalle
            (id_loteFK,id_documentoFK,cod_ventaFK,estado,cod_usuario_estadoFK) VALUES (?,?,?,'incluido',?)");
        $stmtDocumento = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_fisico='en_lote',cod_local_ubicacionFK=?,
            ubicacion_fisica=?,cod_usuarioFK_update=?,fecha_actualizacion=?,version_registro=version_registro+1
            WHERE id_documento=? AND es_requerido=1 AND estado_fisico='en_sucursal'");
        $origenNombre = centroFacturaTextoBaseDatos(centroLegajoVentaRaw($mysqli, $ids[0])['nombre_local'], 255);
        foreach ($documentos as $documento) {
            $idDocumento = intval($documento['id_documento']);
            $codVenta = intval($documento['cod_ventaFK']);
            $stmtDetalle->bind_param('iiii', $idLote, $idDocumento, $codVenta, $codUsuario);
            if (!$stmtDetalle->execute()) throw new Exception('No se pudo incluir un documento en el lote.');
            $stmtDocumento->bind_param('isisi', $codLocal, $origenNombre, $codUsuario, $ahora, $idDocumento);
            if (!$stmtDocumento->execute() || $stmtDocumento->affected_rows !== 1) throw new Exception('Una copia fisica dejo de estar disponible durante la preparacion.');
            if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'agregar_a_lote', $documento['estado_documental'], $documento['estado_documental'],
                $documento['estado_fisico'], 'en_lote', 'Lote '.$codigo, $codUsuario)) throw new Exception('No se pudo auditar un documento del lote.');
        }
        $stmtDetalle->close(); $stmtDocumento->close();
        if (!centroLegajoRegistrarEventoLote($mysqli, $idLote, 'crear_lote', '', 'borrador',
            count($ids).' legajos; '.count($documentos).' documentos; destino '.$destinoSnapshot, $codUsuario, $transportista)) {
            throw new Exception('No se pudo auditar la creacion del lote.');
        }
        $mysqli->commit(); $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'codigo_lote' => $codigo,
            'cantidad_legajos' => count($ids), 'cantidad_documentos' => count($documentos));
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote_legajo', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoDetalleLote($idLote, $codUsuario)
{
    if (!centroLegajoPuedeVer($codUsuario)) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar el envio.');
    $mysqli = conectar_al_servidor();
    $loteRaw = centroLegajoLoteRaw($mysqli, $idLote, false);
    if (!$loteRaw || !centroLegajoPuedeAccederLote($codUsuario, $loteRaw, $mysqli)) { $mysqli->close(); return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'El lote no existe o no esta dentro de su alcance.'); }
    $idLote = intval($idLote);
    $stmt = $mysqli->prepare("SELECT lo.*,lor.Nombre AS nombre_local_origen,lde.Nombre AS nombre_local_destino,
        pc.nombre_persona AS usuario_creador,pt.nombre_persona AS usuario_transportista,
        pe.nombre_persona AS usuario_envio,pct.nombre_persona AS usuario_custodia,pr.nombre_persona AS usuario_recepcion,
        pu.nombre_persona AS usuario_ultima_accion
      FROM centro_legajo_lote lo
      INNER JOIN local lor ON lor.cod_local=lo.cod_local_origenFK
      INNER JOIN local lde ON lde.cod_local=lo.cod_local_destinoFK
      LEFT JOIN persona pc ON pc.cod_persona=lo.cod_usuarioFK_create
      LEFT JOIN persona pt ON pt.cod_persona=lo.cod_usuario_transportistaFK
      LEFT JOIN persona pe ON pe.cod_persona=lo.cod_usuario_envioFK
      LEFT JOIN persona pct ON pct.cod_persona=lo.cod_usuario_custodiaFK
      LEFT JOIN persona pr ON pr.cod_persona=lo.cod_usuario_recepcionFK
      LEFT JOIN persona pu ON pu.cod_persona=COALESCE(lo.cod_usuarioFK_update,lo.cod_usuarioFK_create)
      WHERE lo.id_lote=? LIMIT 1");
    $stmt->bind_param('i', $idLote); $stmt->execute(); $lote = centroFacturaFilaUtf8($stmt->get_result()->fetch_assoc()); $stmt->close();
    if ($lote['estado'] === 'pendiente_custodia') {
        $lote['responsable_actual'] = $lote['usuario_transportista'];
        $lote['responsable_actual_rol'] = 'Transportista asignado · custodia pendiente';
    } elseif ($lote['estado'] === 'en_transito') {
        $lote['responsable_actual'] = $lote['usuario_custodia'] ? $lote['usuario_custodia'] : $lote['usuario_transportista'];
        $lote['responsable_actual_rol'] = 'Custodia durante el traslado';
    } elseif (in_array($lote['estado'], array('recibido','recibido_parcial','observado'), true)) {
        $lote['responsable_actual'] = $lote['usuario_recepcion'];
        $lote['responsable_actual_rol'] = 'Custodia en destino';
    } elseif ($lote['estado'] === 'anulado') {
        $lote['responsable_actual'] = $lote['usuario_ultima_accion'];
        $lote['responsable_actual_rol'] = 'Responsable del cierre';
    } else {
        $lote['responsable_actual'] = $lote['usuario_creador'];
        $lote['responsable_actual_rol'] = 'Responsable de preparación';
    }
    if (empty($lote['responsable_actual'])) $lote['responsable_actual'] = 'Sin asignar';
    $lote['ultima_accion_usuario'] = $lote['usuario_ultima_accion'];
    $lote['ultima_accion_fecha'] = !empty($lote['fecha_actualizacion']) ? $lote['fecha_actualizacion'] : $lote['fecha_creacion'];
    $lote['ultima_accion_tipo'] = 'estado_'.$lote['estado'];
    $lote = centroFacturaDecorarPlazoTransito($lote);
    $documentos = array();
    $stmt = $mysqli->prepare("SELECT ld.id_lote_detalle,ld.estado AS estado_lote,ld.observacion AS observacion_lote,ld.fecha_estado,
        ld.cod_usuario_estadoFK,pde.nombre_persona AS ultima_accion_usuario,
        d.*,v.puntoexpedicion,v.num_factura,v.cod_local,v.fecha_venta,v.TipoVenta AS tipo_venta,GREATEST(0,IFNULL(v.total_venta,0)-IFNULL(v.descuento,0)) AS importe_venta,p.nombre_persona AS titular,
        TRIM(IFNULL(c.ci_cliente,'')) AS documento_paciente,TRIM(IFNULL(c.rut_cliente,'')) AS ruc_paciente,l.Nombre AS nombre_local
      FROM centro_legajo_lote_detalle ld
      INNER JOIN centro_legajo_documento d ON d.id_documento=ld.id_documentoFK
      LEFT JOIN venta v ON v.cod_venta=ld.cod_ventaFK
      LEFT JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      LEFT JOIN persona p ON p.cod_persona=c.cod_cliente
      LEFT JOIN local l ON l.cod_local=v.cod_local
      LEFT JOIN persona pde ON pde.cod_persona=ld.cod_usuario_estadoFK
      WHERE ld.id_loteFK=? ORDER BY ld.cod_ventaFK,
        FIELD(d.tipo_documento,'contrato','pagare','cedula','consentimiento','detalle_venta'),d.id_documento");
    $stmt->bind_param('i', $idLote); $stmt->execute(); $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $fila = centroFacturaFilaUtf8($fila);
        $fila['estado_lote_actual'] = $lote['estado'];
        $fila['usuario_creador_lote'] = $lote['usuario_creador'];
        $fila['usuario_transportista_lote'] = $lote['usuario_transportista'];
        $fila['usuario_custodia_lote'] = $lote['usuario_custodia'];
        $fila['usuario_recepcion_lote'] = $lote['usuario_recepcion'];
        $fila['ultima_accion_fecha'] = $fila['fecha_estado'];
        $fila['ultima_accion_tipo'] = 'estado_'.$fila['estado_lote'];
        $fila = centroLegajoAplicarResponsabilidadDocumento($fila);
        $fila['codigo_legajo'] = centroLegajoCodigoLegajo($fila);
        $fila['codigo_documento'] = centroLegajoCodigoDocumento($fila, $fila['tipo_documento']);
        $documentos[] = $fila;
    }
    $stmt->close();
    $eventos = array();
    $stmt = $mysqli->prepare("SELECT e.*,pa.nombre_persona AS usuario_actor,pr.nombre_persona AS usuario_responsable
        FROM centro_legajo_lote_evento e
        LEFT JOIN persona pa ON pa.cod_persona=e.cod_usuario_actorFK
        LEFT JOIN persona pr ON pr.cod_persona=e.cod_usuario_responsableFK
        WHERE e.id_loteFK=? ORDER BY e.id_evento ASC");
    $stmt->bind_param('i', $idLote); $stmt->execute(); $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $eventos[] = centroFacturaFilaUtf8($fila);
    $stmt->close(); $mysqli->close();
    return array('ok' => true, 'lote' => $lote, 'documentos' => $documentos, 'eventos' => $eventos);
}

function centroLegajoDocumentosLoteBloqueados($mysqli, $idLote)
{
    $idLote = intval($idLote);
    $stmt = centroLegajoPrepararEscritura($mysqli, "SELECT ld.id_lote_detalle,ld.id_loteFK,ld.estado AS estado_lote,ld.observacion AS observacion_lote,
        d.* FROM centro_legajo_lote_detalle ld
        INNER JOIN centro_legajo_documento d ON d.id_documento=ld.id_documentoFK
        WHERE ld.id_loteFK=? AND ld.estado<>'retirado' ORDER BY ld.id_lote_detalle FOR UPDATE");
    $stmt->bind_param('i', $idLote);
    if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo bloquear el manifiesto del lote.'); }
    $documentos = array(); $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $documentos[] = $fila;
    $stmt->close();
    return $documentos;
}

function centroLegajoEnviarLote($idLote, $codUsuario, $diasPlazoTransito = 10)
{
    if (!centroLegajoTienePermiso($codUsuario, 'ENVIARLOTELEGAJOS')) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para entregar lotes de legajos.');
    try {
        $diasPlazoTransito = centroFacturaNormalizarDiasPlazoTransito($diasPlazoTransito);
    } catch (Exception $e) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => $e->getMessage());
    }
    $mysqli = conectar_al_servidor();
    if (!centroFacturaPlazoTransitoDisponible($mysqli, 'centro_legajo_lote')) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'Falta instalar la actualizacion del plazo de transito antes de entregar el lote.');
    }
    $mysqli->begin_transaction();
    try {
        $lote = centroLegajoLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] !== 'borrador' || !centroLegajoPuedeOperarOrigen($codUsuario, $lote, $mysqli)) throw new Exception('El lote no esta disponible como borrador en su local.');
        $transportista = intval($lote['cod_usuario_transportistaFK']);
        $stmt = centroLegajoPrepararEscritura($mysqli, "SELECT cod_usuario FROM usuario WHERE cod_usuario=? AND estado='Activo' LIMIT 1");
        $stmt->bind_param('i', $transportista);
        if (!$stmt->execute() || !$stmt->get_result()->fetch_assoc()) { $stmt->close(); throw new Exception('El transportista asignado ya no esta activo. Anule el borrador y vuelva a formarlo.'); }
        $stmt->close();
        if (!centroLegajoTienePermiso($transportista, 'ENVIARLOTELEGAJOS')) throw new Exception('El transportista asignado ya no puede aceptar la custodia. Anule el borrador y vuelva a formarlo.');
        $documentos = centroLegajoDocumentosLoteBloqueados($mysqli, $idLote);
        if (count($documentos) < 1) throw new Exception('El lote no contiene documentos activos.');
        centroLegajoRevalidarLoteAntesEnvio($mysqli, $documentos);
        foreach ($documentos as $documento) {
            if ($documento['estado_lote'] !== 'incluido' || !in_array($documento['estado_documental'], array('disponible','validado'), true)
                || $documento['estado_fisico'] !== 'en_lote') throw new Exception('Un documento ya no esta disponible para entregar.');
        }
        $idLote = intval($idLote); $ahora = date('Y-m-d H:i:s');
        $fechaLimite = centroFacturaCalcularFechaLimiteTransito($ahora, $diasPlazoTransito);
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote SET estado='pendiente_custodia',cod_usuario_envioFK=?,fecha_envio=?,
            cod_usuarioFK_update=?,fecha_actualizacion=?,dias_plazo_transito=?,fecha_limite_recepcion=?,
            cod_usuario_plazo_transitoFK=?,fecha_asignacion_plazo=? WHERE id_lote=?");
        $stmt->bind_param('isisisisi', $codUsuario, $ahora, $codUsuario, $ahora, $diasPlazoTransito,
            $fechaLimite, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo registrar la entrega.'); }
        $stmt->close();
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote_detalle SET estado='pendiente_custodia',fecha_estado=?,cod_usuario_estadoFK=?
            WHERE id_loteFK=? AND estado='incluido'");
        $stmt->bind_param('sii', $ahora, $codUsuario, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== count($documentos)) { $stmt->close(); throw new Exception('No se pudo actualizar todo el manifiesto del lote.'); }
        $stmt->close();
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_fisico='pendiente_custodia',cod_usuarioFK_update=?,
            fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_documento=? AND estado_fisico='en_lote'");
        foreach ($documentos as $documento) {
            $idDocumento = intval($documento['id_documento']);
            $stmt->bind_param('isi', $codUsuario, $ahora, $idDocumento);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) throw new Exception('No se pudo actualizar una copia entregada.');
            if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'entregar_transportista', $documento['estado_documental'], $documento['estado_documental'],
                $documento['estado_fisico'], 'pendiente_custodia', 'Lote '.$lote['codigo_lote'], $codUsuario)) throw new Exception('No se pudo auditar la entrega documental.');
        }
        $stmt->close();
        if (!centroLegajoRegistrarEventoLote($mysqli, $idLote, 'entregar_transportista', 'borrador', 'pendiente_custodia',
            'Entrega declarada; plazo de '.$diasPlazoTransito.' dias hasta '.$fechaLimite.'; pendiente de aceptacion del transportista asignado.',
            $codUsuario, intval($lote['cod_usuario_transportistaFK']))) throw new Exception('No se pudo auditar la entrega.');
        $mysqli->commit(); $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'estado' => 'pendiente_custodia',
            'dias_plazo_transito' => $diasPlazoTransito, 'fecha_limite_recepcion' => $fechaLimite);
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote_legajo', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoAceptarCustodia($idLote, $codUsuario)
{
    if (!centroLegajoTienePermiso($codUsuario, 'ENVIARLOTELEGAJOS')) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para aceptar la custodia.');
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    try {
        $lote = centroLegajoLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] !== 'pendiente_custodia') throw new Exception('El lote no esta pendiente de aceptacion.');
        if (intval($lote['cod_usuario_transportistaFK']) !== intval($codUsuario)) throw new Exception('Solo el transportista asignado puede aceptar esta custodia.');
        $documentos = centroLegajoDocumentosLoteBloqueados($mysqli, $idLote);
        if (count($documentos) < 1) throw new Exception('El lote no contiene documentos activos.');
        foreach ($documentos as $documento) {
            if ($documento['estado_lote'] !== 'pendiente_custodia' || $documento['estado_fisico'] !== 'pendiente_custodia') throw new Exception('Un documento no coincide con la entrega pendiente.');
        }
        $idLote = intval($idLote); $ahora = date('Y-m-d H:i:s');
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote SET estado='en_transito',cod_usuario_custodiaFK=?,fecha_aceptacion_custodia=?,
            cod_usuarioFK_update=?,fecha_actualizacion=? WHERE id_lote=?");
        $stmt->bind_param('isisi', $codUsuario, $ahora, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo aceptar la custodia.'); }
        $stmt->close();
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote_detalle SET estado='en_transito',fecha_estado=?,cod_usuario_estadoFK=?
            WHERE id_loteFK=? AND estado='pendiente_custodia'");
        $stmt->bind_param('sii', $ahora, $codUsuario, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== count($documentos)) { $stmt->close(); throw new Exception('No se pudo actualizar todo el manifiesto en custodia.'); }
        $stmt->close();
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_fisico='en_transito',cod_local_ubicacionFK=NULL,
            ubicacion_fisica='En transito',cod_usuarioFK_update=?,fecha_actualizacion=?,version_registro=version_registro+1
            WHERE id_documento=? AND estado_fisico='pendiente_custodia'");
        foreach ($documentos as $documento) {
            $idDocumento = intval($documento['id_documento']);
            $stmt->bind_param('isi', $codUsuario, $ahora, $idDocumento);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) throw new Exception('No se pudo actualizar un documento en custodia.');
            if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'aceptar_custodia', $documento['estado_documental'], $documento['estado_documental'],
                $documento['estado_fisico'], 'en_transito', 'Lote '.$lote['codigo_lote'], $codUsuario)) throw new Exception('No se pudo auditar la custodia documental.');
        }
        $stmt->close();
        if (!centroLegajoRegistrarEventoLote($mysqli, $idLote, 'aceptar_custodia', 'pendiente_custodia', 'en_transito',
            'Custodia aceptada por el transportista asignado.', $codUsuario, $codUsuario)) throw new Exception('No se pudo auditar la custodia.');
        $mysqli->commit(); $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'estado' => 'en_transito');
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote_legajo', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoRecibirLote($idLote, $recepciones, $datos, $codUsuario)
{
    if (!centroLegajoTienePermiso($codUsuario, 'RECIBIRLOTELEGAJOS')) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para recibir lotes de legajos.');
    $mapa = array();
    foreach ((array)$recepciones as $item) {
        if (!is_array($item)) continue;
        $idDocumento = isset($item['id_documento']) ? intval($item['id_documento']) : 0;
        $estado = isset($item['estado']) ? centroFacturaTextoBaseDatos($item['estado'], 20) : '';
        $observacion = isset($item['observacion']) ? centroFacturaTextoBaseDatos($item['observacion'], 255) : '';
        if ($idDocumento > 0 && in_array($estado, array('recibido','faltante','observado'), true)) $mapa[$idDocumento] = array('estado' => $estado, 'observacion' => $observacion);
    }
    if (count($mapa) < 1) return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'Indique el resultado de recepcion de los documentos.');
    $ubicacionIndicada = isset($datos['ubicacion_fisica']) ? centroFacturaTextoBaseDatos($datos['ubicacion_fisica'], 255) : '';
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    try {
        $lote = centroLegajoLoteRaw($mysqli, $idLote, true);
        if (!$lote || !in_array($lote['estado'], array('en_transito','recibido_parcial','observado'), true)
            || !centroLegajoPuedeOperarDestino($codUsuario, $lote, $mysqli)) throw new Exception('El lote no esta disponible para recepcion en su local.');
        $documentos = centroLegajoDocumentosLoteBloqueados($mysqli, $idLote);
        $porId = array();
        foreach ($documentos as $documento) $porId[intval($documento['id_documento'])] = $documento;
        foreach ($mapa as $idDocumento => $recepcion) {
            if (!isset($porId[$idDocumento])) throw new Exception('Un documento indicado no pertenece al lote.');
            if ($porId[$idDocumento]['estado_fisico'] === 'devuelto_cliente' && $recepcion['estado'] !== 'recibido') {
                throw new Exception('Un documento ya devuelto al cliente no puede corregirse desde la recepcion del lote.');
            }
            if ($recepcion['estado'] !== 'recibido' && $recepcion['observacion'] === '') throw new Exception('Describa cada documento faltante u observado.');
            if ($porId[$idDocumento]['estado_lote'] === 'recibido' && $recepcion['estado'] !== 'recibido'
                && !centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) throw new Exception('Corregir un documento ya recibido requiere permiso superior.');
        }
        $idLote = intval($idLote); $ahora = date('Y-m-d H:i:s');
        $destinoNombre = centroFacturaTextoBaseDatos($lote['destino_snapshot'], 255);
        foreach ($mapa as $idDocumento => $recepcion) {
            $documento = $porId[$idDocumento];
            if ($documento['estado_fisico'] === 'devuelto_cliente') continue;
            if ($documento['estado_lote'] === 'recibido' && $recepcion['estado'] === 'recibido') continue;
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote_detalle SET estado=?,observacion=?,fecha_estado=?,cod_usuario_estadoFK=?
                WHERE id_loteFK=? AND id_documentoFK=?");
            $stmt->bind_param('sssiii', $recepcion['estado'], $recepcion['observacion'], $ahora, $codUsuario, $idLote, $idDocumento);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo guardar un resultado de recepcion.'); }
            $stmt->close();
            $nuevoDocumento = $recepcion['estado'] === 'recibido'
                ? (in_array($documento['estado_documental'], array('disponible','validado'), true) ? $documento['estado_documental'] : 'disponible')
                : 'observado';
            $nuevoFisico = $recepcion['estado'];
            $localUbicacion = $recepcion['estado'] === 'faltante' ? null : intval($lote['cod_local_destinoFK']);
            $ubicacion = $recepcion['estado'] === 'faltante' ? null : ($ubicacionIndicada !== '' ? $ubicacionIndicada : $destinoNombre);
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_documental=?,estado_fisico=?,cod_local_ubicacionFK=?,
                ubicacion_fisica=?,observaciones=?,cod_usuarioFK_update=?,fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_documento=?");
            $stmt->bind_param('ssissisi', $nuevoDocumento, $nuevoFisico, $localUbicacion, $ubicacion, $recepcion['observacion'],
                $codUsuario, $ahora, $idDocumento);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo actualizar la ubicacion de un documento.'); }
            $stmt->close();
            if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'recibir_lote', $documento['estado_documental'], $nuevoDocumento,
                $documento['estado_fisico'], $nuevoFisico, $recepcion['observacion'].' · Lote '.$lote['codigo_lote'], $codUsuario)) throw new Exception('No se pudo auditar un documento recibido.');
        }
        $resultado = $mysqli->query("SELECT SUM(estado='recibido') recibidos,SUM(estado IN ('faltante','observado')) observados,
            SUM(estado IN ('incluido','pendiente_custodia','en_transito')) pendientes
            FROM centro_legajo_lote_detalle WHERE id_loteFK=".$idLote." AND estado<>'retirado'");
        if (!$resultado) throw new Exception('No se pudo consolidar el resultado de la recepcion.');
        $conteo = $resultado->fetch_assoc();
        if (intval($conteo['pendientes']) === 0 && intval($conteo['observados']) === 0) $estadoLote = 'recibido';
        elseif (intval($conteo['recibidos']) > 0) $estadoLote = 'recibido_parcial';
        else $estadoLote = 'observado';
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote SET estado=?,fecha_recepcion=?,cod_usuario_recepcionFK=?,
            cod_usuarioFK_update=?,fecha_actualizacion=? WHERE id_lote=?");
        $stmt->bind_param('ssiisi', $estadoLote, $ahora, $codUsuario, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo actualizar la recepcion del lote.'); }
        $stmt->close();
        if (!centroLegajoRegistrarEventoLote($mysqli, $idLote, 'recibir_lote', $lote['estado'], $estadoLote,
            'Recepcion individual: '.intval($conteo['recibidos']).' recibidos, '.intval($conteo['observados']).' con diferencias.', $codUsuario, $codUsuario)) throw new Exception('No se pudo auditar la recepcion.');
        $mysqli->commit(); $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'estado' => $estadoLote);
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote_legajo', 'mensaje' => $e->getMessage());
    }
}

function centroLegajoAnularLote($idLote, $motivo, $codUsuario)
{
    if (!centroLegajoTienePermiso($codUsuario, 'GESTIONARLOTESLEGAJOS')) return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para anular lotes de legajos.');
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    if ($motivo === '') return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Ingrese el motivo de anulacion.');
    $mysqli = conectar_al_servidor(); $mysqli->begin_transaction();
    try {
        $lote = centroLegajoLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] === 'anulado' || !centroLegajoPuedeOperarOrigen($codUsuario, $lote, $mysqli)) throw new Exception('El lote no esta disponible para anulacion.');
        if ($lote['estado'] !== 'borrador' && !centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) throw new Exception('Anular un lote ya entregado requiere permiso superior.');
        $documentos = centroLegajoDocumentosLoteBloqueados($mysqli, $idLote);
        $idLote = intval($idLote); $ahora = date('Y-m-d H:i:s');
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote SET estado='anulado',motivo_anulacion=?,cod_usuario_anulacionFK=?,
            fecha_anulacion=?,cod_usuarioFK_update=?,fecha_actualizacion=? WHERE id_lote=?");
        $stmt->bind_param('sisisi', $motivo, $codUsuario, $ahora, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo anular el lote.'); }
        $stmt->close();
        $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_lote_detalle SET estado='retirado',observacion=?,fecha_estado=?,cod_usuario_estadoFK=?
            WHERE id_loteFK=? AND estado<>'retirado'");
        $stmt->bind_param('ssii', $motivo, $ahora, $codUsuario, $idLote);
        if (!$stmt->execute() || $stmt->affected_rows !== count($documentos)) { $stmt->close(); throw new Exception('No se pudo retirar todo el manifiesto del lote.'); }
        $stmt->close();
        foreach ($documentos as $documento) {
            if (in_array($documento['estado_fisico'], array('recibido','devuelto_cliente'), true)) continue;
            $permaneceEnOrigen = in_array($lote['estado'], array('borrador','pendiente_custodia'), true);
            $nuevoDocumento = $permaneceEnOrigen ? $documento['estado_documental'] : 'observado';
            $nuevoFisico = $permaneceEnOrigen ? 'en_sucursal' : 'observado';
            $local = $permaneceEnOrigen ? intval($lote['cod_local_origenFK']) : null;
            $ubicacion = $permaneceEnOrigen ? 'Devuelto al local de origen' : 'Ubicacion por verificar tras anulacion';
            $idDocumento = intval($documento['id_documento']);
            $stmt = centroLegajoPrepararEscritura($mysqli, "UPDATE centro_legajo_documento SET estado_documental=?,estado_fisico=?,cod_local_ubicacionFK=?,
                ubicacion_fisica=?,observaciones=?,cod_usuarioFK_update=?,fecha_actualizacion=?,version_registro=version_registro+1 WHERE id_documento=?");
            $stmt->bind_param('ssissisi', $nuevoDocumento, $nuevoFisico, $local, $ubicacion, $motivo, $codUsuario, $ahora, $idDocumento);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo actualizar un documento del lote anulado.'); }
            $stmt->close();
            if (!centroLegajoRegistrarEventoDocumento($mysqli, $documento, 'anular_lote', $documento['estado_documental'], $nuevoDocumento,
                $documento['estado_fisico'], $nuevoFisico, $motivo.' · Lote '.$lote['codigo_lote'], $codUsuario)) throw new Exception('No se pudo auditar un documento del lote anulado.');
        }
        if (!centroLegajoRegistrarEventoLote($mysqli, $idLote, 'anular_lote', $lote['estado'], 'anulado', $motivo, $codUsuario, null)) throw new Exception('No se pudo auditar la anulacion.');
        $mysqli->commit(); $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'estado' => 'anulado');
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote_legajo', 'mensaje' => $e->getMessage());
    }
}

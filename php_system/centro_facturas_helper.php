<?php

date_default_timezone_set('America/Asuncion');

function centroFacturaTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '') {
        return false;
    }
    if (isset($cache[$tabla])) {
        return $cache[$tabla];
    }
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=? LIMIT 1");
    if (!$stmt) {
        $cache[$tabla] = false;
        return false;
    }
    $stmt->bind_param('s', $tabla);
    $stmt->execute();
    $cache[$tabla] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$tabla];
}

function centroFacturaColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$columna);
    $clave = $tabla.'.'.$columna;
    if ($tabla === '' || $columna === '') return false;
    if (isset($cache[$clave])) return $cache[$clave];
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=? LIMIT 1");
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $cache[$clave] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$clave];
}

function centroFacturaEstructuraDisponible($mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $tablas = array(
        'centro_factura', 'centro_factura_archivo', 'centro_factura_auditoria',
        'centro_factura_configuracion', 'centro_factura_lote',
        'centro_factura_lote_detalle', 'centro_factura_ocr_sugerencia'
    );
    $disponible = true;
    foreach ($tablas as $tabla) {
        if (!centroFacturaTablaExiste($mysqli, $tabla)) {
            $disponible = false;
            break;
        }
    }
    if ($disponible && (!centroFacturaColumnaExiste($mysqli, 'mensaje', 'tipo_adjunto')
        || !centroFacturaColumnaExiste($mysqli, 'centro_factura', 'tipo_documento'))) {
        $disponible = false;
    }
    if ($cerrar) {
        $mysqli->close();
    }
    return $disponible;
}

function centroFacturaTextoBaseDatos($valor, $limite = 0, $mantenerSaltos = false)
{
    $valor = (string)$valor;
    if (mb_check_encoding($valor, 'UTF-8')) {
        $valor = mb_convert_encoding($valor, 'ISO-8859-1', 'UTF-8');
    }
    $valor = str_replace(array("\r\n", "\r"), "\n", $valor);
    if ($mantenerSaltos) {
        $valor = preg_replace('/[\t ]+/', ' ', $valor);
        $valor = preg_replace('/\n{3,}/', "\n\n", $valor);
    } else {
        $valor = preg_replace('/\s+/', ' ', $valor);
    }
    $valor = trim($valor);
    if ($limite > 0 && mb_strlen($valor, 'ISO-8859-1') > $limite) {
        $valor = mb_substr($valor, 0, $limite, 'ISO-8859-1');
    }
    return $valor;
}

function centroFacturaTextoPlanoBaseDatos($valor, $limite = 0)
{
    $valor = html_entity_decode(strip_tags((string)$valor), ENT_QUOTES | ENT_HTML5, 'ISO-8859-1');
    return centroFacturaTextoBaseDatos($valor, $limite, false);
}

function centroFacturaValorUtf8($valor)
{
    if (is_array($valor)) {
        $salida = array();
        foreach ($valor as $clave => $item) {
            $salida[$clave] = centroFacturaValorUtf8($item);
        }
        return $salida;
    }
    if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
        return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
    }
    return $valor;
}

function centroFacturaFilaUtf8($fila)
{
    return centroFacturaValorUtf8((array)$fila);
}

function centroFacturaJsonBaseDatos($valor)
{
    $json = json_encode(centroFacturaValorUtf8($valor), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $json = '{}';
    }
    return mb_convert_encoding($json, 'ISO-8859-1', 'UTF-8');
}

function centroFacturaBind($stmt, $tipos, &$parametros)
{
    if ($tipos === '' || count($parametros) === 0) {
        return true;
    }
    $referencias = array();
    foreach ($parametros as $indice => $valor) {
        $referencias[$indice] = &$parametros[$indice];
    }
    return call_user_func_array(array($stmt, 'bind_param'), array_merge(array($tipos), $referencias));
}

function centroFacturaTienePermiso($codUsuario, $codigo)
{
    static $cache = array();
    $codUsuario = intval($codUsuario);
    $codigo = strtoupper(trim((string)$codigo));
    $clave = $codUsuario.'|'.$codigo;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    if ($codUsuario <= 0 || $codigo === '' || !function_exists('controldeaccesoacasas')) {
        $cache[$clave] = false;
        return false;
    }
    $cache[$clave] = controldeaccesoacasas($codUsuario, $codigo, " u.accion='SI' ") == 1;
    return $cache[$clave];
}

function centroFacturaPuedeVerTodosLocales($codUsuario)
{
    return centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURASTODOSLOCALES');
}

function centroFacturaContextoUsuario($codUsuario, $mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $codUsuario = intval($codUsuario);
    $stmt = $mysqli->prepare("SELECT u.cod_usuario,u.cod_localFK,u.tipo,p.nombre_persona,l.Nombre AS nombre_local
        FROM usuario u
        INNER JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN local l ON l.cod_local=u.cod_localFK
        WHERE u.cod_usuario=? AND u.estado='Activo' LIMIT 1");
    $stmt->bind_param('i', $codUsuario);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($cerrar) {
        $mysqli->close();
    }
    return $fila ? $fila : array();
}

function centroFacturaPuedeUsarLocal($codUsuario, $codLocal, $mysqli = null)
{
    if (centroFacturaPuedeVerTodosLocales($codUsuario)) {
        return true;
    }
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    return !empty($contexto) && intval($contexto['cod_localFK']) === intval($codLocal);
}

function centroFacturaConfiguracion($mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $configuracion = array(
        'dias_plazo_original' => 5,
        'requiere_archivo_manual' => 1,
        'moneda_predeterminada' => 'PYG',
        'ocr_habilitado' => 0,
        'ocr_proveedor' => ''
    );
    if (centroFacturaTablaExiste($mysqli, 'centro_factura_configuracion')) {
        $resultado = $mysqli->query("SELECT * FROM centro_factura_configuracion WHERE id_configuracion=1 LIMIT 1");
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $configuracion = array_merge($configuracion, $fila);
        }
    }
    if ($cerrar) {
        $mysqli->close();
    }
    return $configuracion;
}

function centroFacturaFechaValida($valor, $admiteVacio = true)
{
    $valor = trim((string)$valor);
    if ($valor === '' && $admiteVacio) {
        return '';
    }
    $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
    $errores = DateTime::getLastErrors();
    if (!$fecha || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
        return false;
    }
    return $fecha->format('Y-m-d');
}

function centroFacturaImporte($valor)
{
    if (is_int($valor) || is_float($valor)) {
        return round((float)$valor, 2);
    }
    $texto = trim((string)$valor);
    $texto = str_replace(array('Gs.', 'Gs', ' '), '', $texto);
    if (strpos($texto, ',') !== false) {
        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);
    }
    $texto = preg_replace('/[^0-9.\-]/', '', $texto);
    return round((float)$texto, 2);
}

function centroFacturaNormalizarClave($valor)
{
    $valor = centroFacturaValorUtf8((string)$valor);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
    if ($ascii !== false) {
        $valor = $ascii;
    }
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $valor));
}

function centroFacturaFirmaFiscal($documento, $numero, $timbrado, $fecha, $importe)
{
    $documento = centroFacturaNormalizarClave($documento);
    $numero = centroFacturaNormalizarClave($numero);
    $timbrado = centroFacturaNormalizarClave($timbrado);
    $fecha = trim((string)$fecha);
    $importe = number_format((float)$importe, 2, '.', '');
    if ($documento === '' || $numero === '') {
        return '';
    }
    return hash('sha256', implode('|', array($documento, $numero, $timbrado, $fecha, $importe)));
}

function centroFacturaAuditar($mysqli, $entidad, $idEntidad, $idFactura, $accion, $anterior, $nuevo, $motivo, $codUsuario)
{
    $entidad = centroFacturaTextoBaseDatos($entidad, 30);
    $accion = centroFacturaTextoBaseDatos($accion, 60);
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    $valorAnterior = centroFacturaJsonBaseDatos($anterior);
    $valorNuevo = centroFacturaJsonBaseDatos($nuevo);
    $idEntidad = intval($idEntidad);
    $idFactura = intval($idFactura) > 0 ? intval($idFactura) : null;
    $codUsuario = intval($codUsuario);
    $stmt = $mysqli->prepare("INSERT INTO centro_factura_auditoria
        (entidad,id_entidad,id_facturaFK,accion,valor_anterior,valor_nuevo,motivo,cod_usuarioFK)
        VALUES (?,?,?,?,?,?,?,?)");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('siissssi', $entidad, $idEntidad, $idFactura, $accion, $valorAnterior, $valorNuevo, $motivo, $codUsuario);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centroFacturaSqlEstadoPago($alias = 'cf')
{
    $alias = preg_replace('/[^A-Za-z0-9_]/', '', $alias);
    return "CASE
        WHEN $alias.estado_registro='anulado' OR $alias.estado_validacion='anulada' THEN 'Anulado'
        WHEN $alias.estado_validacion='rechazada' THEN 'Rechazado'
        WHEN $alias.idgastosFK IS NOT NULL THEN
            CASE LOWER(TRIM(IFNULL(g.estado,'')))
                WHEN 'activo' THEN 'Pagado'
                WHEN 'solicitado' THEN 'En revision'
                WHEN 'pendiente' THEN 'Pendiente'
                WHEN 'rechazado' THEN 'Rechazado'
                WHEN 'baja' THEN 'Anulado'
                WHEN 'inactivo' THEN 'Anulado'
                ELSE 'Pendiente'
            END
        WHEN $alias.cod_compraFK IS NOT NULL THEN
            CASE
                WHEN LOWER(TRIM(IFNULL(cp.estado,'')))='inactivo' THEN 'Anulado'
                WHEN IFNULL((SELECT SUM(pc.monto) FROM pagosdecompra pc WHERE pc.cod_compraFk=cp.cod_compra AND LOWER(TRIM(pc.estado))='pagado'),0)
                     >= GREATEST(IFNULL((SELECT SUM(dc.subTotal) FROM detalle_compra dc WHERE dc.cod_compraFK=cp.cod_compra),cp.total_compra)-IFNULL(cp.descuento,0),0)
                     AND GREATEST(IFNULL((SELECT SUM(dc.subTotal) FROM detalle_compra dc WHERE dc.cod_compraFK=cp.cod_compra),cp.total_compra)-IFNULL(cp.descuento,0),0)>0
                    THEN 'Pagado'
                WHEN EXISTS(SELECT 1 FROM pagosdecompra pc2 WHERE pc2.cod_compraFk=cp.cod_compra AND LOWER(TRIM(pc2.estado))='pendiente') THEN 'En revision'
                ELSE 'Pendiente'
            END
        ELSE 'Pendiente'
    END";
}

function centroFacturaSqlFechaPago($alias = 'cf')
{
    $alias = preg_replace('/[^A-Za-z0-9_]/', '', $alias);
    return "CASE
        WHEN $alias.idgastosFK IS NOT NULL AND LOWER(TRIM(IFNULL(g.estado,'')))='activo'
            THEN COALESCE(g.fecha_autoriz,CONCAT(g.fecha,' 00:00:00'))
        WHEN $alias.cod_compraFK IS NOT NULL
            THEN (SELECT MAX(CONCAT(pc.fechadelpago,' 00:00:00')) FROM pagosdecompra pc WHERE pc.cod_compraFk=cp.cod_compra AND LOWER(TRIM(pc.estado))='pagado')
        ELSE NULL
    END";
}

function centroFacturaEstadoOriginalVisual($fila)
{
    $estado = isset($fila['estado_original']) ? (string)$fila['estado_original'] : 'en_proceso';
    $validacion = isset($fila['estado_validacion']) ? (string)$fila['estado_validacion'] : 'pendiente';
    $registro = isset($fila['estado_registro']) ? (string)$fila['estado_registro'] : 'activo';
    $salida = array('codigo' => $estado, 'texto' => 'En proceso', 'clase' => 'warning', 'vencido' => 0, 'alerta' => '');
    if ($registro === 'anulado' || $validacion === 'anulada') {
        return array('codigo' => 'anulado', 'texto' => 'Anulado', 'clase' => 'muted', 'vencido' => 0, 'alerta' => '');
    }
    if ($validacion === 'rechazada') {
        return array('codigo' => 'rechazado', 'texto' => 'Rechazado', 'clase' => 'muted', 'vencido' => 0, 'alerta' => '');
    }
    if ($estado === 'recibido') {
        return array('codigo' => $estado, 'texto' => 'Recibido', 'clase' => 'success', 'vencido' => 0, 'alerta' => '');
    }
    if ($estado === 'observado') {
        return array('codigo' => $estado, 'texto' => 'Observado', 'clase' => 'danger', 'vencido' => 0, 'alerta' => 'observado');
    }
    if ($estado === 'no_requiere_original') {
        return array('codigo' => $estado, 'texto' => 'No requiere original', 'clase' => 'neutral', 'vencido' => 0, 'alerta' => '');
    }
    $limite = isset($fila['fecha_limite_original']) ? centroFacturaFechaValida($fila['fecha_limite_original'], false) : false;
    if ($limite) {
        $hoy = new DateTime('today');
        $fechaLimite = new DateTime($limite);
        $manana = new DateTime('tomorrow');
        if ($fechaLimite < $hoy) {
            return array('codigo' => 'vencido', 'texto' => 'Vencido', 'clase' => 'danger', 'vencido' => 1, 'alerta' => 'vencido');
        }
        if ($fechaLimite == $hoy) {
            return array('codigo' => 'vence_hoy', 'texto' => 'Vence hoy', 'clase' => 'danger', 'vencido' => 0, 'alerta' => 'vence_hoy');
        }
        if ($fechaLimite == $manana) {
            return array('codigo' => 'vence_manana', 'texto' => 'Vence manana', 'clase' => 'warning', 'vencido' => 0, 'alerta' => 'vence_manana');
        }
    }
    if ($estado === 'enviado_central') {
        $salida['texto'] = 'Enviado a central';
        $salida['clase'] = 'info';
    }
    return $salida;
}

function centroFacturaDecorarFila($fila)
{
    $visual = centroFacturaEstadoOriginalVisual($fila);
    $fila['estado_original_visual'] = $visual;
    $estadoPago = isset($fila['estado_pago']) ? (string)$fila['estado_pago'] : 'Pendiente';
    $fila['pagada_sin_original'] = ($estadoPago === 'Pagado'
        && in_array($fila['estado_original'], array('en_proceso','enviado_central'), true)
        && $fila['estado_registro'] === 'activo'
        && !in_array($fila['estado_validacion'], array('rechazada','anulada'), true)) ? 1 : 0;
    return $fila;
}

function centroFacturaBaseSelect()
{
    $estadoPago = centroFacturaSqlEstadoPago('cf');
    $fechaPago = centroFacturaSqlFechaPago('cf');
    return "SELECT cf.*,
        l.Nombre AS nombre_local,
        ic.asunto AS hilo_asunto,
        COALESCE(NULLIF(cf.nombre_contraparte,''),pp.nombre_persona,pf.nombre_persona,'Pendiente de completar') AS contraparte_mostrar,
        presp.nombre_persona AS responsable_envio_nombre,
        prec.nombre_persona AS usuario_recepcion_nombre,
        g.estado AS gasto_estado,g.fecha AS gasto_fecha,g.fecha_autoriz AS gasto_fecha_autoriz,g.motivo AS gasto_motivo,
        cp.estado AS compra_estado,cp.num_comprobante AS compra_comprobante,
        $estadoPago AS estado_pago,
        $fechaPago AS fecha_pago,
        CASE WHEN cf.idgastosFK IS NOT NULL THEN 'gasto' WHEN cf.cod_compraFK IS NOT NULL THEN 'compra' ELSE '' END AS tipo_referencia_pago,
        (SELECT ld.id_loteFK FROM centro_factura_lote_detalle ld
         INNER JOIN centro_factura_lote lo ON lo.id_lote=ld.id_loteFK
         WHERE ld.id_facturaFK=cf.id_factura AND ld.estado<>'retirada' AND lo.estado<>'anulado'
         ORDER BY ld.id_lote_detalle DESC LIMIT 1) AS id_lote_actual
      FROM centro_factura cf
      INNER JOIN local l ON l.cod_local=cf.cod_localFK
      LEFT JOIN interconsulta ic ON ic.cod_interConsulta=cf.cod_interConsultaFK
      LEFT JOIN proveedor pro ON pro.cod_proveedor=cf.cod_proveedorFK
      LEFT JOIN persona pp ON pp.cod_persona=pro.cod_proveedor
      LEFT JOIN usuario fun ON fun.cod_usuario=cf.cod_funcionarioFK
      LEFT JOIN persona pf ON pf.cod_persona=fun.cod_usuario
      LEFT JOIN usuario uresp ON uresp.cod_usuario=cf.cod_responsable_envioFK
      LEFT JOIN persona presp ON presp.cod_persona=uresp.cod_usuario
      LEFT JOIN usuario urec ON urec.cod_usuario=cf.cod_usuario_recepcionFK
      LEFT JOIN persona prec ON prec.cod_persona=urec.cod_usuario
      LEFT JOIN gastos g ON g.idgastos=cf.idgastosFK
      LEFT JOIN compra cp ON cp.cod_compra=cf.cod_compraFK";
}

function centroFacturaConstruirConsultaEntrantes($codUsuario, $filtros, $contar = false)
{
    $contexto = centroFacturaContextoUsuario($codUsuario);
    if (empty($contexto)) {
        return array('sql' => '', 'tipos' => '', 'parametros' => array());
    }
    $tipos = '';
    $parametros = array();
    $internas = array("cf.direccion='entrante'");
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $internas[] = 'cf.cod_localFK=?';
        $tipos .= 'i';
        $parametros[] = intval($contexto['cod_localFK']);
    } elseif (!empty($filtros['cod_local'])) {
        $internas[] = 'cf.cod_localFK=?';
        $tipos .= 'i';
        $parametros[] = intval($filtros['cod_local']);
    }
    if (empty($filtros['incluir_anuladas'])) {
        $internas[] = "cf.estado_registro='activo'";
    }
    $base = centroFacturaBaseSelect().' WHERE '.implode(' AND ', $internas);
    $externas = array('1=1');
    $busqueda = isset($filtros['busqueda']) ? centroFacturaTextoBaseDatos($filtros['busqueda'], 120) : '';
    if ($busqueda !== '') {
        $externas[] = "(q.contraparte_mostrar LIKE ? OR q.documento_contraparte LIKE ? OR q.numero_factura LIKE ? OR q.concepto LIKE ? OR q.hilo_asunto LIKE ? OR CAST(q.cod_interConsultaFK AS CHAR) LIKE ?)";
        $patron = '%'.$busqueda.'%';
        for ($i = 0; $i < 6; $i++) {
            $tipos .= 's';
            $parametros[] = $patron;
        }
    }
    $filtrosSimples = array(
        'cod_proveedor' => array('q.cod_proveedorFK','i'),
        'cod_funcionario' => array('q.cod_funcionarioFK','i'),
        'cod_responsable' => array('q.cod_responsable_envioFK','i'),
        'cod_hilo' => array('q.cod_interConsultaFK','i'),
        'estado_validacion' => array('q.estado_validacion','s'),
        'estado_pago' => array('q.estado_pago','s')
    );
    foreach ($filtrosSimples as $clave => $definicion) {
        if (isset($filtros[$clave]) && trim((string)$filtros[$clave]) !== '') {
            $externas[] = $definicion[0].'=?';
            $tipos .= $definicion[1];
            $parametros[] = $definicion[1] === 'i' ? intval($filtros[$clave]) : centroFacturaTextoBaseDatos($filtros[$clave], 40);
        }
    }
    if (!empty($filtros['estado_original'])) {
        $estadoOriginal = centroFacturaTextoBaseDatos($filtros['estado_original'], 40);
        if ($estadoOriginal === 'vencido') {
            $externas[] = "q.estado_original IN ('en_proceso','enviado_central') AND q.fecha_limite_original<CURDATE() AND q.estado_validacion NOT IN ('rechazada','anulada')";
        } elseif ($estadoOriginal === 'vence_hoy') {
            $externas[] = "q.estado_original IN ('en_proceso','enviado_central') AND q.fecha_limite_original=CURDATE() AND q.estado_validacion NOT IN ('rechazada','anulada')";
        } elseif ($estadoOriginal === 'vence_manana') {
            $externas[] = "q.estado_original IN ('en_proceso','enviado_central') AND q.fecha_limite_original=DATE_ADD(CURDATE(),INTERVAL 1 DAY) AND q.estado_validacion NOT IN ('rechazada','anulada')";
        } else {
            $externas[] = 'q.estado_original=?';
            $tipos .= 's';
            $parametros[] = $estadoOriginal;
        }
    }
    $filtroRapido = !empty($filtros['rapido']) ? $filtros['rapido']
        : (!empty($filtros['filtro_rapido']) ? $filtros['filtro_rapido'] : '');
    if ($filtroRapido !== '') {
        $rapido = centroFacturaTextoBaseDatos($filtroRapido, 40);
        if ($rapido === 'pagadas_sin_original') {
            $externas[] = "q.estado_pago='Pagado' AND q.estado_original IN ('en_proceso','enviado_central') AND q.estado_validacion NOT IN ('rechazada','anulada')";
        } elseif ($rapido === 'vencidas') {
            $externas[] = "q.estado_original IN ('en_proceso','enviado_central') AND q.fecha_limite_original<CURDATE() AND q.estado_validacion NOT IN ('rechazada','anulada')";
        } elseif ($rapido === 'vence_hoy') {
            $externas[] = "q.estado_original IN ('en_proceso','enviado_central') AND q.fecha_limite_original=CURDATE() AND q.estado_validacion NOT IN ('rechazada','anulada')";
        } elseif ($rapido === 'observadas') {
            $externas[] = "q.estado_original='observado'";
        } elseif ($rapido === 'recibidos') {
            $externas[] = "q.estado_original='recibido'";
        } elseif ($rapido === 'pendientes_revision') {
            $externas[] = "q.estado_validacion IN ('pendiente','en_revision')";
        } elseif ($rapido === 'pendientes_pago') {
            $externas[] = "q.estado_pago IN ('Pendiente','En revision')";
        }
    }
    foreach (array('fecha_desde' => '>=', 'fecha_hasta' => '<=') as $clave => $operador) {
        if (!empty($filtros[$clave])) {
            $fecha = centroFacturaFechaValida($filtros[$clave], false);
            if ($fecha) {
                $externas[] = 'DATE(q.fecha_registro_digital)'.$operador.'?';
                $tipos .= 's';
                $parametros[] = $fecha;
            }
        }
    }
    if (isset($filtros['importe_desde']) && centroFacturaImporte($filtros['importe_desde']) > 0) {
        $externas[] = 'q.importe_total>=?';
        $tipos .= 'd';
        $parametros[] = centroFacturaImporte($filtros['importe_desde']);
    }
    if (isset($filtros['importe_hasta']) && centroFacturaImporte($filtros['importe_hasta']) > 0) {
        $externas[] = 'q.importe_total<=?';
        $tipos .= 'd';
        $parametros[] = centroFacturaImporte($filtros['importe_hasta']);
    }
    $consulta = 'SELECT * FROM ('.$base.') q WHERE '.implode(' AND ', $externas);
    if ($contar) {
        $consulta = 'SELECT COUNT(*) AS total FROM ('.$consulta.') conteo';
    }
    return array('sql' => $consulta, 'tipos' => $tipos, 'parametros' => $parametros);
}

function centroFacturaRangoPeriodo($filtros)
{
    $desde = !empty($filtros['fecha_desde']) ? centroFacturaFechaValida($filtros['fecha_desde'], false) : false;
    $hasta = !empty($filtros['fecha_hasta']) ? centroFacturaFechaValida($filtros['fecha_hasta'], false) : false;
    if (!$desde || !$hasta || $desde > $hasta) {
        $desde = date('Y-m-01');
        $hasta = date('Y-m-t');
    }
    return array($desde, $hasta);
}

function centroFacturaCargarGastosEsperados($mysqli, $codUsuario, $filtros)
{
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    if (empty($contexto)) {
        return array();
    }
    list($desde, $hasta) = centroFacturaRangoPeriodo($filtros);
    $condiciones = array(
        "LOWER(TRIM(IFNULL(g.estado,'')))='activo'",
        "LOWER(TRIM(IFNULL(g.tipo,'')))='egreso'",
        "g.fecha>=?", "g.fecha<=?"
    );
    $tipos = 'ss';
    $parametros = array($desde, $hasta);
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $condiciones[] = 'g.cod_local=?';
        $tipos .= 'i';
        $parametros[] = intval($contexto['cod_localFK']);
    } elseif (!empty($filtros['cod_local'])) {
        $condiciones[] = 'g.cod_local=?';
        $tipos .= 'i';
        $parametros[] = intval($filtros['cod_local']);
    }
    $sql = "SELECT
        cf.id_factura,cf.tipo_documento,cf.fuente,cf.tipo_contraparte,cf.cod_proveedorFK,cf.cod_funcionarioFK,
        cf.nombre_contraparte,cf.documento_contraparte,cf.numero_factura,cf.timbrado,cf.fecha_emision,
        COALESCE(NULLIF(cf.importe_total,0),g.monto) AS importe_total,cf.moneda,
        COALESCE(NULLIF(cf.concepto,''),g.motivo) AS concepto,cf.observaciones,cf.estado_validacion,
        COALESCE(cf.estado_original,'en_proceso') AS estado_original,cf.fecha_registro_digital,
        cf.fecha_limite_original,cf.estado_registro,cf.idgastosFK,cf.cod_compraFK,
        cf.fecha_recepcion_fisica,cf.fecha_observacion,cf.posible_duplicado,cf.duplicado_confirmado,
        cf.version_registro,cf.cod_responsable_envioFK,
        g.idgastos AS id_gasto_esperado,g.fecha AS fecha_origen,g.monto AS importe_esperado,
        g.motivo AS gasto_motivo,g.personales AS gasto_contraparte,g.estado AS gasto_estado,
        COALESCE(cf.cod_interConsultaFK,g.cod_interConsultaFK) AS cod_interConsultaFK,
        g.cod_local AS cod_localFK,l.Nombre AS nombre_local,ic.asunto AS hilo_asunto,
        COALESCE(NULLIF(cf.nombre_contraparte,''),NULLIF(g.personales,''),'Pendiente de completar') AS contraparte_mostrar,
        'Pagado' AS estado_pago,COALESCE(g.fecha_autoriz,CONCAT(g.fecha,' 00:00:00')) AS fecha_pago,
        CASE WHEN cf.id_factura IS NOT NULL THEN 'gasto' ELSE '' END AS tipo_referencia_pago,
        (SELECT m.cod_mensaje FROM mensaje m
          WHERE m.cod_interConsultaFK=g.cod_interConsultaFK AND m.estado='activo'
            AND TRIM(IFNULL(m.url,''))<>'' AND LOWER(TRIM(IFNULL(m.tipo_adjunto,''))) IN ('factura','comprobante')
          ORDER BY m.fecha_creacion DESC,m.cod_mensaje DESC LIMIT 1) AS cod_mensaje_documento,
        (SELECT LOWER(TRIM(IFNULL(m.tipo_adjunto,''))) FROM mensaje m
          WHERE m.cod_interConsultaFK=g.cod_interConsultaFK AND m.estado='activo'
            AND TRIM(IFNULL(m.url,''))<>'' AND LOWER(TRIM(IFNULL(m.tipo_adjunto,''))) IN ('factura','comprobante')
          ORDER BY m.fecha_creacion DESC,m.cod_mensaje DESC LIMIT 1) AS tipo_adjunto_documento,
        (SELECT IFNULL(cfa.id_facturaFK,0) FROM mensaje m
          LEFT JOIN centro_factura_archivo cfa ON cfa.cod_mensajeFK=m.cod_mensaje AND cfa.estado='activo'
          WHERE m.cod_interConsultaFK=g.cod_interConsultaFK AND m.estado='activo'
            AND TRIM(IFNULL(m.url,''))<>'' AND LOWER(TRIM(IFNULL(m.tipo_adjunto,''))) IN ('factura','comprobante')
          ORDER BY m.fecha_creacion DESC,m.cod_mensaje DESC LIMIT 1) AS id_factura_documento,
        CASE
          WHEN cf.id_factura IS NOT NULL AND (cf.estado_validacion='rechazada' OR cf.estado_original='observado') THEN 'observado'
          WHEN cf.id_factura IS NOT NULL THEN 'consolidado'
          WHEN EXISTS(SELECT 1 FROM mensaje md WHERE md.cod_interConsultaFK=g.cod_interConsultaFK AND md.estado='activo'
               AND TRIM(IFNULL(md.url,''))<>'' AND LOWER(TRIM(IFNULL(md.tipo_adjunto,''))) IN ('factura','comprobante')) THEN 'por_vincular'
          ELSE 'sin_comprobante'
        END AS estado_documental,
        (SELECT ld.id_loteFK FROM centro_factura_lote_detalle ld
          INNER JOIN centro_factura_lote lo ON lo.id_lote=ld.id_loteFK
          WHERE ld.id_facturaFK=cf.id_factura AND ld.estado<>'retirada' AND lo.estado<>'anulado'
          ORDER BY ld.id_lote_detalle DESC LIMIT 1) AS id_lote_actual
      FROM gastos g
      INNER JOIN local l ON l.cod_local=g.cod_local
      LEFT JOIN interconsulta ic ON ic.cod_interConsulta=g.cod_interConsultaFK
      LEFT JOIN centro_factura cf ON cf.idgastosFK=g.idgastos AND cf.direccion='entrante' AND cf.estado_registro='activo'
      WHERE ".implode(' AND ', $condiciones)."
      ORDER BY g.fecha DESC,g.idgastos DESC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array();
    }
    centroFacturaBind($stmt, $tipos, $parametros);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $registros = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        if (empty($fila['tipo_documento'])) {
            $fila['tipo_documento'] = $fila['tipo_adjunto_documento'] === 'comprobante' ? 'recibo'
                : ($fila['tipo_adjunto_documento'] === 'factura' ? 'factura' : '');
        }
        if (empty($fila['estado_registro'])) {
            $fila['estado_registro'] = 'virtual';
        }
        if (empty($fila['estado_validacion'])) {
            $fila['estado_validacion'] = 'pendiente';
        }
        $fila = centroFacturaDecorarFila($fila);
        $registros[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    return $registros;
}

function centroFacturaCargarDocumentosSinGasto($mysqli, $codUsuario, $filtros, $hilosConGasto)
{
    $filtrosDocumentos = (array)$filtros;
    $filtrosDocumentos['filtro_rapido'] = '';
    $filtrosDocumentos['rapido'] = '';
    $definicion = centroFacturaConstruirConsultaEntrantes($codUsuario, $filtrosDocumentos, false);
    if ($definicion['sql'] === '') {
        return array();
    }
    $stmt = $mysqli->prepare($definicion['sql'].' ORDER BY q.fecha_registro_digital DESC,q.id_factura DESC');
    if (!$stmt) {
        return array();
    }
    $parametros = $definicion['parametros'];
    centroFacturaBind($stmt, $definicion['tipos'], $parametros);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $registros = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        if (intval($fila['idgastosFK']) > 0) {
            continue;
        }
        $hilo = intval($fila['cod_interConsultaFK']);
        if ($hilo > 0 && isset($hilosConGasto[$hilo])) {
            continue;
        }
        $fila['id_gasto_esperado'] = 0;
        $fila['fecha_origen'] = $fila['fecha_registro_digital'];
        $fila['importe_esperado'] = $fila['importe_total'];
        $fila['gasto_motivo'] = $fila['concepto'];
        $fila['id_factura_documento'] = intval($fila['id_factura']);
        $fila['estado_documental'] = intval($fila['cod_compraFK']) > 0 ? 'consolidado'
            : (($fila['estado_validacion'] === 'rechazada' || $fila['estado_original'] === 'observado') ? 'observado' : 'por_vincular');
        $registros[] = centroFacturaDecorarFila(centroFacturaFilaUtf8($fila));
    }
    $stmt->close();
    return $registros;
}

function centroFacturaCoincideFiltrosEsperados($fila, $filtros, $omitirRapido = false)
{
    $busqueda = isset($filtros['busqueda']) ? trim((string)$filtros['busqueda']) : '';
    if ($busqueda !== '') {
        $texto = implode(' ', array(
            isset($fila['contraparte_mostrar']) ? $fila['contraparte_mostrar'] : '',
            isset($fila['documento_contraparte']) ? $fila['documento_contraparte'] : '',
            isset($fila['numero_factura']) ? $fila['numero_factura'] : '',
            isset($fila['concepto']) ? $fila['concepto'] : '',
            isset($fila['hilo_asunto']) ? $fila['hilo_asunto'] : '',
            isset($fila['cod_interConsultaFK']) ? $fila['cod_interConsultaFK'] : '',
            isset($fila['id_gasto_esperado']) ? $fila['id_gasto_esperado'] : ''
        ));
        if (stripos(centroFacturaValorUtf8($texto), centroFacturaValorUtf8($busqueda)) === false) {
            return false;
        }
    }
    if (!empty($filtros['cod_hilo']) && intval($fila['cod_interConsultaFK']) !== intval($filtros['cod_hilo'])) {
        return false;
    }
    if (!empty($filtros['cod_proveedor']) && intval($fila['cod_proveedorFK']) !== intval($filtros['cod_proveedor'])) {
        return false;
    }
    if (!empty($filtros['cod_funcionario']) && intval($fila['cod_funcionarioFK']) !== intval($filtros['cod_funcionario'])) {
        return false;
    }
    if (!empty($filtros['estado_validacion'])) {
        if (empty($fila['id_factura']) || (string)$fila['estado_validacion'] !== (string)$filtros['estado_validacion']) return false;
    }
    if (!empty($filtros['estado_pago']) && (string)$fila['estado_pago'] !== (string)$filtros['estado_pago']) {
        return false;
    }
    if (!empty($filtros['estado_original'])) {
        if (empty($fila['id_factura'])) return false;
        $estadoOriginal = (string)$filtros['estado_original'];
        $visual = isset($fila['estado_original_visual']['codigo']) ? $fila['estado_original_visual']['codigo'] : '';
        if ((string)$fila['estado_original'] !== $estadoOriginal && $visual !== $estadoOriginal) {
            return false;
        }
    }
    $importe = isset($fila['importe_total']) ? (float)$fila['importe_total'] : 0;
    if (!empty($filtros['importe_desde']) && $importe < centroFacturaImporte($filtros['importe_desde'])) {
        return false;
    }
    if (!empty($filtros['importe_hasta']) && $importe > centroFacturaImporte($filtros['importe_hasta'])) {
        return false;
    }
    if (!$omitirRapido) {
        $rapido = !empty($filtros['filtro_rapido']) ? (string)$filtros['filtro_rapido'] : '';
        if ($rapido === 'con_factura' && !($fila['estado_documental'] === 'consolidado' && $fila['tipo_documento'] === 'factura')) return false;
        if ($rapido === 'con_recibo' && !($fila['estado_documental'] === 'consolidado' && $fila['tipo_documento'] === 'recibo')) return false;
        if ($rapido === 'por_vincular' && $fila['estado_documental'] !== 'por_vincular') return false;
        if ($rapido === 'sin_comprobante' && $fila['estado_documental'] !== 'sin_comprobante') return false;
        if ($rapido === 'observadas' && $fila['estado_documental'] !== 'observado') return false;
        if ($rapido === 'recibidos' && $fila['estado_original'] !== 'recibido') return false;
    }
    return true;
}

function centroFacturaMetricasEsperadas($registros)
{
    $metricas = array(
        'gastos_periodo' => 0, 'con_factura' => 0, 'con_recibo' => 0,
        'por_vincular' => 0, 'sin_comprobante' => 0, 'observadas' => 0,
        'originales_recibidos' => 0
    );
    foreach ($registros as $fila) {
        if (intval($fila['id_gasto_esperado']) > 0) $metricas['gastos_periodo']++;
        if ($fila['estado_documental'] === 'consolidado' && $fila['tipo_documento'] === 'factura') $metricas['con_factura']++;
        if ($fila['estado_documental'] === 'consolidado' && $fila['tipo_documento'] === 'recibo') $metricas['con_recibo']++;
        if ($fila['estado_documental'] === 'por_vincular') $metricas['por_vincular']++;
        if ($fila['estado_documental'] === 'sin_comprobante') $metricas['sin_comprobante']++;
        if ($fila['estado_documental'] === 'observado') $metricas['observadas']++;
        if (!empty($fila['id_factura']) && $fila['estado_original'] === 'recibido') $metricas['originales_recibidos']++;
    }
    return $metricas;
}

function centroFacturaListarEntrantes($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para ver el Centro de Facturas.');
    }
    $limite = max(1, min(150, intval($limite)));
    $offset = max(0, intval($offset));
    $mysqli = conectar_al_servidor();
    $filtros = (array)$filtros;
    $gastos = centroFacturaCargarGastosEsperados($mysqli, $codUsuario, $filtros);
    $hilosConGasto = array();
    foreach ($gastos as $fila) {
        if (intval($fila['cod_interConsultaFK']) > 0) {
            $hilosConGasto[intval($fila['cod_interConsultaFK'])] = true;
        }
    }
    $documentos = centroFacturaCargarDocumentosSinGasto($mysqli, $codUsuario, $filtros, $hilosConGasto);
    $mysqli->close();
    $base = array();
    foreach (array_merge($gastos, $documentos) as $fila) {
        if (centroFacturaCoincideFiltrosEsperados($fila, $filtros, true)) {
            $base[] = $fila;
        }
    }
    $metricas = centroFacturaMetricasEsperadas($base);
    $registros = array();
    foreach ($base as $fila) {
        if (centroFacturaCoincideFiltrosEsperados($fila, $filtros, false)) {
            $registros[] = $fila;
        }
    }
    usort($registros, function ($a, $b) {
        $fechaA = isset($a['fecha_origen']) ? (string)$a['fecha_origen'] : '';
        $fechaB = isset($b['fecha_origen']) ? (string)$b['fecha_origen'] : '';
        if ($fechaA === $fechaB) {
            return intval($b['id_gasto_esperado']) - intval($a['id_gasto_esperado']);
        }
        return strcmp($fechaB, $fechaA);
    });
    $total = count($registros);
    $pagina = array_slice($registros, $offset, $limite);
    return array('ok' => true, 'registros' => $pagina, 'total' => $total, 'limite' => $limite, 'offset' => $offset, 'metricas' => $metricas);
}

function centroFacturaMetricas($codUsuario)
{
    $definicion = centroFacturaConstruirConsultaEntrantes($codUsuario, array('incluir_anuladas' => 0), false);
    $metricas = array(
        'pendientes_revision' => 0, 'pendientes_pago' => 0, 'pagadas_sin_original' => 0,
        'vencen_hoy' => 0, 'originales_vencidos' => 0, 'originales_recibidos' => 0,
        'observadas' => 0, 'alertas_total' => 0
    );
    if ($definicion['sql'] === '') {
        return $metricas;
    }
    $mysqli = conectar_al_servidor();
    $sql = 'SELECT id_factura,estado_pago,estado_validacion,estado_original,fecha_limite_original,estado_registro FROM ('.$definicion['sql'].') metricas';
    $stmt = $mysqli->prepare($sql);
    $parametros = $definicion['parametros'];
    centroFacturaBind($stmt, $definicion['tipos'], $parametros);
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $fila = centroFacturaDecorarFila($fila);
            $requiereAtencion = false;
            if (in_array($fila['estado_validacion'], array('pendiente','en_revision'), true)) {
                $metricas['pendientes_revision']++;
            }
            if (in_array($fila['estado_pago'], array('Pendiente','En revision'), true)) {
                $metricas['pendientes_pago']++;
            }
            if (!empty($fila['pagada_sin_original'])) {
                $metricas['pagadas_sin_original']++;
                $requiereAtencion = true;
            }
            $codigoVisual = $fila['estado_original_visual']['codigo'];
            if ($codigoVisual === 'vence_hoy') {
                $metricas['vencen_hoy']++;
                $requiereAtencion = true;
            } elseif ($codigoVisual === 'vencido') {
                $metricas['originales_vencidos']++;
                $requiereAtencion = true;
            }
            if ($fila['estado_original'] === 'recibido') {
                $metricas['originales_recibidos']++;
            }
            if ($fila['estado_original'] === 'observado') {
                $metricas['observadas']++;
                $requiereAtencion = true;
            }
            if ($requiereAtencion) {
                $metricas['alertas_total']++;
            }
        }
    }
    $stmt->close();
    $mysqli->close();
    return $metricas;
}

function centroFacturaCatalogos($codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para ver el Centro de Facturas.');
    }
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    $todos = centroFacturaPuedeVerTodosLocales($codUsuario);
    $locales = array();
    $sqlLocales = "SELECT cod_local,Nombre FROM local WHERE estado='Activo'".($todos ? '' : ' AND cod_local=?').' ORDER BY Nombre';
    $stmt = $mysqli->prepare($sqlLocales);
    if (!$todos) {
        $localUsuario = intval($contexto['cod_localFK']);
        $stmt->bind_param('i', $localUsuario);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $locales[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $proveedores = array();
    $resultado = $mysqli->query("SELECT pro.cod_proveedor,p.nombre_persona,pro.rut_proveedor
        FROM proveedor pro INNER JOIN persona p ON p.cod_persona=pro.cod_proveedor
        WHERE pro.estado='Activo' ORDER BY p.nombre_persona LIMIT 500");
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $proveedores[] = centroFacturaFilaUtf8($fila);
        }
    }
    $funcionarios = array();
    $sqlUsuarios = "SELECT u.cod_usuario,u.cod_localFK,p.nombre_persona,u.rut_usuario
        FROM usuario u INNER JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE u.estado='Activo'".($todos ? '' : ' AND u.cod_localFK=?').' ORDER BY p.nombre_persona';
    $stmt = $mysqli->prepare($sqlUsuarios);
    if (!$todos) {
        $localUsuario = intval($contexto['cod_localFK']);
        $stmt->bind_param('i', $localUsuario);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $funcionarios[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $configuracion = centroFacturaFilaUtf8(centroFacturaConfiguracion($mysqli));
    $mysqli->close();
    $codigosPermiso = array(
        'VERCENTROFACTURAS','VERCENTROFACTURASTODOSLOCALES','REGISTRARFACTURAHILO',
        'REGISTRARFACTURAMANUAL','VINCULARPAGOFACTURA','ENVIARORIGINALFACTURA',
        'RECIBIRORIGINALFACTURA','GESTIONARLOTESFACTURAS','ADMINCENTROFACTURAS'
    );
    $permisos = array();
    foreach ($codigosPermiso as $codigo) {
        $permisos[$codigo] = centroFacturaTienePermiso($codUsuario, $codigo) ? 1 : 0;
    }
    return array(
        'ok' => true,
        'usuario' => centroFacturaFilaUtf8($contexto),
        'locales' => $locales,
        'proveedores' => $proveedores,
        'funcionarios' => $funcionarios,
        'configuracion' => $configuracion,
        'permisos' => $permisos,
        'metricas' => centroFacturaMetricas($codUsuario)
    );
}

function centroFacturaBuscarPorMensaje($codMensaje, $mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $codMensaje = intval($codMensaje);
    $stmt = $mysqli->prepare("SELECT cf.id_factura,cf.tipo_documento,cf.estado_registro,cf.estado_validacion,cf.estado_original
        FROM centro_factura_archivo a
        INNER JOIN centro_factura cf ON cf.id_factura=a.id_facturaFK
        WHERE a.cod_mensajeFK=? AND a.estado='activo' LIMIT 1");
    $stmt->bind_param('i', $codMensaje);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($cerrar) {
        $mysqli->close();
    }
    return $fila ? $fila : array();
}

function centroFacturaRegistrarDesdeMensaje($codMensaje, $codUsuario)
{
    $codMensaje = intval($codMensaje);
    $codUsuario = intval($codUsuario);
    if (!centroFacturaTienePermiso($codUsuario, 'REGISTRARFACTURAHILO')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para registrar facturas desde Hilos.');
    }
    $mysqli = conectar_al_servidor();
    if (!centroFacturaEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura del Centro de Facturas no esta instalada.');
    }
    $existente = centroFacturaBuscarPorMensaje($codMensaje, $mysqli);
    if (!empty($existente)) {
        $mysqli->close();
        return array('ok' => true, 'id_factura' => intval($existente['id_factura']), 'tipo_documento' => $existente['tipo_documento'], 'idempotente' => 1);
    }
    $stmt = $mysqli->prepare("SELECT m.cod_mensaje,m.url,m.contenido,m.tipo_adjunto,m.estado,m.cod_usuarioFK,m.cod_interConsultaFK,
            ic.estado AS hilo_estado,ic.cod_localFK,p.nombre_persona,u.rut_usuario
        FROM mensaje m
        INNER JOIN interconsulta ic ON ic.cod_interConsulta=m.cod_interConsultaFK
        LEFT JOIN usuario u ON u.cod_usuario=m.cod_usuarioFK
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE m.cod_mensaje=? LIMIT 1");
    $stmt->bind_param('i', $codMensaje);
    $stmt->execute();
    $mensaje = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$mensaje || $mensaje['estado'] !== 'activo' || $mensaje['hilo_estado'] === 'inactivo' || trim((string)$mensaje['url']) === '') {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'adjunto', 'mensaje' => 'El adjunto ya no esta disponible o el Hilo esta inactivo.');
    }
    if (!function_exists('seguimientoProgramadoPuedeAccederHilo')
        || !seguimientoProgramadoPuedeAccederHilo($mensaje['cod_interConsultaFK'], $codUsuario, true)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene acceso al Hilo de origen.');
    }
    if (!centroFacturaPuedeUsarLocal($codUsuario, $mensaje['cod_localFK'], $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'No puede registrar facturas para este local.');
    }
    $configuracion = centroFacturaConfiguracion($mysqli);
    $dias = max(1, min(60, intval($configuracion['dias_plazo_original'])));
    $fechaRegistro = date('Y-m-d H:i:s');
    $fechaLimite = date('Y-m-d', strtotime('+'.$dias.' days', strtotime($fechaRegistro)));
    $tipoDocumento = strtolower(trim((string)$mensaje['tipo_adjunto'])) === 'comprobante' ? 'recibo' : 'factura';
    $concepto = centroFacturaTextoPlanoBaseDatos($mensaje['contenido'], 255);
    if ($concepto === '') {
        $concepto = $tipoDocumento === 'recibo' ? 'Recibo registrado desde Hilo' : 'Factura registrada desde Hilo';
    }
    $extension = strtolower(pathinfo(parse_url((string)$mensaje['url'], PHP_URL_PATH), PATHINFO_EXTENSION));
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("INSERT INTO centro_factura
            (direccion,tipo_documento,fuente,cod_interConsultaFK,cod_localFK,tipo_contraparte,nombre_contraparte,
             concepto,estado_validacion,estado_original,fecha_registro_digital,dias_plazo_original,
             fecha_limite_original,cod_responsable_envioFK,cod_usuario_registroFK)
            VALUES ('entrante',?,'hilo',?,?,'otro','',?,'pendiente','en_proceso',?,?,?,?,?)");
        $hilo = intval($mensaje['cod_interConsultaFK']);
        $local = intval($mensaje['cod_localFK']);
        $stmt->bind_param('siissisii', $tipoDocumento, $hilo, $local, $concepto, $fechaRegistro, $dias, $fechaLimite, $codUsuario, $codUsuario);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo crear la factura desde el Hilo.');
        }
        $idFactura = intval($stmt->insert_id);
        $stmt->close();
        $tipoOrigen = 'mensaje_hilo';
        $nombre = basename(parse_url((string)$mensaje['url'], PHP_URL_PATH));
        $stmt = $mysqli->prepare("INSERT INTO centro_factura_archivo
            (id_facturaFK,tipo_origen,cod_mensajeFK,url,nombre_original,extension,orden_pagina,es_principal,cod_usuarioFK_create)
            VALUES (?,?,?,NULL,?,?,1,1,?)");
        $stmt->bind_param('isissi', $idFactura, $tipoOrigen, $codMensaje, $nombre, $extension, $codUsuario);
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {
                $stmt->close();
                throw new Exception('REGISTRO_DUPLICADO_ADJUNTO');
            }
            throw new Exception('No se pudo vincular el adjunto del Hilo.');
        }
        $idArchivo = intval($stmt->insert_id);
        $stmt->close();
        $tipoAdjunto = $tipoDocumento === 'recibo' ? 'comprobante' : 'factura';
        $stmt = $mysqli->prepare("UPDATE mensaje SET tipo_adjunto=? WHERE cod_mensaje=?");
        $stmt->bind_param('si', $tipoAdjunto, $codMensaje);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo clasificar el adjunto.');
        }
        $stmt->close();
        if (!centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'crear_desde_hilo', array(), array(
            'cod_mensaje' => $codMensaje,
            'cod_interConsulta' => $hilo,
            'cod_local' => $local,
            'tipo_documento' => $tipoDocumento,
            'fecha_limite_original' => $fechaLimite
        ), '', $codUsuario)) {
            throw new Exception('No se pudo auditar la factura.');
        }
        centroFacturaAuditar($mysqli, 'archivo', $idArchivo, $idFactura, 'vincular_adjunto_hilo', array(), array('cod_mensaje' => $codMensaje), '', $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_factura' => $idFactura, 'tipo_documento' => $tipoDocumento, 'idempotente' => 0, 'registro_incompleto' => 1);
    } catch (Exception $e) {
        $mysqli->rollback();
        if ($e->getMessage() === 'REGISTRO_DUPLICADO_ADJUNTO') {
            $existente = centroFacturaBuscarPorMensaje($codMensaje, $mysqli);
            $mysqli->close();
            if (!empty($existente)) {
                return array('ok' => true, 'id_factura' => intval($existente['id_factura']), 'tipo_documento' => $existente['tipo_documento'], 'idempotente' => 1);
            }
        }
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'registro', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaPrepararArchivo($archivo)
{
    $datos = isset($archivo['data']) ? (string)$archivo['data'] : '';
    $nombre = isset($archivo['nombre']) ? basename((string)$archivo['nombre'])
        : (isset($archivo['name']) ? basename((string)$archivo['name']) : 'documento');
    if ($datos === '' && !empty($archivo['tmp_name']) && is_file($archivo['tmp_name'])
        && (!isset($archivo['error']) || intval($archivo['error']) === UPLOAD_ERR_OK)) {
        if (isset($archivo['size']) && intval($archivo['size']) > 10000000) {
            return array('ok' => false, 'mensaje' => 'Cada archivo puede pesar hasta 10 MB.');
        }
        $binarioSubido = file_get_contents($archivo['tmp_name']);
        if ($binarioSubido !== false) {
            $datos = base64_encode($binarioSubido);
        }
    }
    if ($datos === '') {
        return array('ok' => false, 'mensaje' => 'El archivo esta vacio.');
    }
    if (strpos($datos, ',') !== false) {
        $datos = substr($datos, strpos($datos, ',') + 1);
    }
    if (strlen($datos) > 14000000) {
        return array('ok' => false, 'mensaje' => 'Cada archivo puede pesar hasta 10 MB.');
    }
    $binario = base64_decode($datos, true);
    if ($binario === false || $binario === '') {
        return array('ok' => false, 'mensaje' => 'El contenido del archivo no es valido.');
    }
    if (strlen($binario) > 10000000) {
        return array('ok' => false, 'mensaje' => 'Cada archivo puede pesar hasta 10 MB.');
    }
    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string)finfo_buffer($finfo, $binario);
            finfo_close($finfo);
        }
    }
    $permitidos = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf'
    );
    if (!isset($permitidos[$mime])) {
        return array('ok' => false, 'mensaje' => 'Solo se admiten imagenes JPG, PNG, WEBP, GIF o documentos PDF.');
    }
    return array(
        'ok' => true,
        'binario' => $binario,
        'mime' => $mime,
        'extension' => $permitidos[$mime],
        'nombre' => centroFacturaTextoBaseDatos($nombre, 255),
        'hash' => hash('sha256', $binario)
    );
}

function centroFacturaGuardarArchivoPreparado($idFactura, $archivo)
{
    $directorio = dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosCentroFacturas'.DIRECTORY_SEPARATOR;
    if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
        return array('ok' => false, 'mensaje' => 'No se pudo preparar la carpeta de documentos.');
    }
    try {
        $aleatorio = function_exists('random_bytes') ? bin2hex(random_bytes(8)) : str_replace('.', '', uniqid('', true));
    } catch (Exception $e) {
        $aleatorio = str_replace('.', '', uniqid('', true));
    }
    $nombreFisico = 'factura_'.intval($idFactura).'_'.$aleatorio.'.'.$archivo['extension'];
    $rutaAbsoluta = $directorio.$nombreFisico;
    if (file_put_contents($rutaAbsoluta, $archivo['binario'], LOCK_EX) === false) {
        return array('ok' => false, 'mensaje' => 'No se pudo guardar el archivo.');
    }
    return array(
        'ok' => true,
        'ruta_absoluta' => $rutaAbsoluta,
        'url' => '/GoodVentaAsisCap/fotos/fotosCentroFacturas/'.$nombreFisico
    );
}

function centroFacturaEliminarArchivoFisico($rutaAbsoluta)
{
    $base = realpath(dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosCentroFacturas');
    $archivo = realpath((string)$rutaAbsoluta);
    if ($base && $archivo && strpos($archivo, $base.DIRECTORY_SEPARATOR) === 0 && is_file($archivo)) {
        @unlink($archivo);
    }
}

function centroFacturaResolverContraparte($mysqli, $datos)
{
    $tipo = isset($datos['tipo_contraparte']) ? centroFacturaTextoBaseDatos($datos['tipo_contraparte'], 20) : 'otro';
    if (!in_array($tipo, array('proveedor','funcionario','otro'), true)) {
        $tipo = 'otro';
    }
    $codProveedor = !empty($datos['cod_proveedor']) ? intval($datos['cod_proveedor']) : null;
    $codFuncionario = !empty($datos['cod_funcionario']) ? intval($datos['cod_funcionario']) : null;
    $nombre = isset($datos['nombre_contraparte']) ? centroFacturaTextoBaseDatos($datos['nombre_contraparte'], 255) : '';
    $documento = isset($datos['documento_contraparte']) ? centroFacturaTextoBaseDatos($datos['documento_contraparte'], 45) : '';
    if ($tipo === 'proveedor') {
        if (!$codProveedor) {
            return array('ok' => false, 'mensaje' => 'Seleccione el proveedor relacionado.');
        }
        $stmt = $mysqli->prepare("SELECT p.nombre_persona,pro.rut_proveedor FROM proveedor pro INNER JOIN persona p ON p.cod_persona=pro.cod_proveedor WHERE pro.cod_proveedor=? AND pro.estado='Activo' LIMIT 1");
        $stmt->bind_param('i', $codProveedor);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$fila) {
            return array('ok' => false, 'mensaje' => 'El proveedor seleccionado no esta disponible.');
        }
        if ($nombre === '') {
            $nombre = $fila['nombre_persona'];
        }
        if ($documento === '') {
            $documento = $fila['rut_proveedor'];
        }
        $codFuncionario = null;
    } elseif ($tipo === 'funcionario') {
        if (!$codFuncionario) {
            return array('ok' => false, 'mensaje' => 'Seleccione el funcionario relacionado.');
        }
        $stmt = $mysqli->prepare("SELECT p.nombre_persona,u.rut_usuario FROM usuario u INNER JOIN persona p ON p.cod_persona=u.cod_usuario WHERE u.cod_usuario=? AND u.estado='Activo' LIMIT 1");
        $stmt->bind_param('i', $codFuncionario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$fila) {
            return array('ok' => false, 'mensaje' => 'El funcionario seleccionado no esta disponible.');
        }
        if ($nombre === '') {
            $nombre = $fila['nombre_persona'];
        }
        if ($documento === '') {
            $documento = $fila['rut_usuario'];
        }
        $codProveedor = null;
    } else {
        $codProveedor = null;
        $codFuncionario = null;
    }
    if ($nombre === '') {
        return array('ok' => false, 'mensaje' => 'Ingrese el proveedor, funcionario o responsable de la factura.');
    }
    return array(
        'ok' => true,
        'tipo' => $tipo,
        'cod_proveedor' => $codProveedor,
        'cod_funcionario' => $codFuncionario,
        'nombre' => $nombre,
        'documento' => $documento
    );
}

function centroFacturaBuscarDuplicados($mysqli, $firma, $idExcluir = 0, $tipoDocumento = 'factura')
{
    $firma = trim((string)$firma);
    if ($firma === '') {
        return array();
    }
    $idExcluir = intval($idExcluir);
    $tipoDocumento = $tipoDocumento === 'recibo' ? 'recibo' : 'factura';
    $stmt = $mysqli->prepare("SELECT cf.id_factura,cf.cod_localFK,l.Nombre AS nombre_local,cf.numero_factura,cf.timbrado,
            cf.fecha_emision,cf.importe_total,cf.estado_validacion,cf.estado_original
        FROM centro_factura cf INNER JOIN local l ON l.cod_local=cf.cod_localFK
        WHERE cf.firma_fiscal=? AND cf.tipo_documento=? AND cf.estado_registro='activo' AND cf.id_factura<>?
        ORDER BY cf.id_factura DESC LIMIT 10");
    $stmt->bind_param('ssi', $firma, $tipoDocumento, $idExcluir);
    $stmt->execute();
    $registros = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $registros[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    return $registros;
}

function centroFacturaTipoDocumento($datos)
{
    $tipo = isset($datos['tipo_documento']) ? strtolower(trim((string)$datos['tipo_documento'])) : 'factura';
    return $tipo === 'recibo' ? 'recibo' : 'factura';
}

function centroFacturaValidarDatosCompletos($mysqli, $datos)
{
    $tipoDocumento = centroFacturaTipoDocumento($datos);
    $contraparte = centroFacturaResolverContraparte($mysqli, $datos);
    if (empty($contraparte['ok'])) {
        return $contraparte;
    }
    $numero = isset($datos['numero_factura']) ? centroFacturaTextoBaseDatos($datos['numero_factura'], 80) : '';
    $fecha = isset($datos['fecha_emision']) ? centroFacturaFechaValida($datos['fecha_emision'], false) : false;
    $importe = isset($datos['importe_total']) ? centroFacturaImporte($datos['importe_total']) : 0;
    $concepto = isset($datos['concepto']) ? centroFacturaTextoBaseDatos($datos['concepto'], 255) : '';
    if ($numero === '') {
        return array('ok' => false, 'mensaje' => $tipoDocumento === 'recibo' ? 'Ingrese el numero de recibo.' : 'Ingrese el numero de factura.');
    }
    if (!$fecha) {
        return array('ok' => false, 'mensaje' => 'Ingrese una fecha de emision valida.');
    }
    if ($importe <= 0) {
        return array('ok' => false, 'mensaje' => 'El importe debe ser mayor a cero.');
    }
    if ($concepto === '') {
        return array('ok' => false, 'mensaje' => 'Ingrese el concepto del comprobante.');
    }
    $timbrado = isset($datos['timbrado']) ? centroFacturaTextoBaseDatos($datos['timbrado'], 45) : '';
    if ($tipoDocumento === 'recibo') {
        $timbrado = '';
    }
    $moneda = isset($datos['moneda']) ? strtoupper(centroFacturaTextoBaseDatos($datos['moneda'], 10)) : 'PYG';
    if ($moneda === '') {
        $moneda = 'PYG';
    }
    return array_merge($contraparte, array(
        'ok' => true,
        'tipo_documento' => $tipoDocumento,
        'numero_factura' => $numero,
        'numero_normalizado' => centroFacturaNormalizarClave($numero),
        'timbrado' => $timbrado,
        'fecha_emision' => $fecha,
        'importe_total' => $importe,
        'moneda' => $moneda,
        'concepto' => $concepto,
        'observaciones' => isset($datos['observaciones']) ? centroFacturaTextoBaseDatos($datos['observaciones'], 3000, true) : '',
        'firma_fiscal' => centroFacturaFirmaFiscal($contraparte['documento'], $numero, $timbrado, $fecha, $importe),
        'cod_responsable' => !empty($datos['cod_responsable']) ? intval($datos['cod_responsable']) : null
    ));
}

function centroFacturaRegistrarManual($datos, $archivos, $codUsuario)
{
    $codUsuario = intval($codUsuario);
    if (!centroFacturaTienePermiso($codUsuario, 'REGISTRARFACTURAMANUAL')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para registrar facturas manualmente.');
    }
    $codLocal = isset($datos['cod_local']) ? intval($datos['cod_local']) : 0;
    if ($codLocal <= 0 || !centroFacturaPuedeUsarLocal($codUsuario, $codLocal)) {
        return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'No puede registrar facturas para este local.');
    }
    if (!is_array($archivos) || count($archivos) === 0) {
        return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => 'La carga manual requiere al menos un archivo.');
    }
    if (count($archivos) > 10) {
        return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => 'Un comprobante puede contener hasta 10 archivos o paginas.');
    }
    $preparados = array();
    foreach ($archivos as $archivo) {
        $preparado = centroFacturaPrepararArchivo((array)$archivo);
        if (empty($preparado['ok'])) {
            return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => $preparado['mensaje']);
        }
        $preparados[] = $preparado;
    }
    $mysqli = conectar_al_servidor();
    $validos = centroFacturaValidarDatosCompletos($mysqli, (array)$datos);
    if (empty($validos['ok'])) {
        $mysqli->close();
        return $validos;
    }
    $duplicados = centroFacturaBuscarDuplicados($mysqli, $validos['firma_fiscal'], 0, $validos['tipo_documento']);
    $confirmarDuplicado = !empty($datos['confirmar_duplicado']);
    $motivoDuplicado = isset($datos['motivo_duplicado']) ? centroFacturaTextoBaseDatos($datos['motivo_duplicado'], 255) : '';
    if (count($duplicados) > 0 && (!$confirmarDuplicado || !centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS') || $motivoDuplicado === '')) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'posible_duplicado', 'mensaje' => 'Existe un comprobante del mismo tipo con los mismos datos principales.', 'duplicados' => $duplicados);
    }
    $configuracion = centroFacturaConfiguracion($mysqli);
    $dias = max(1, min(60, intval($configuracion['dias_plazo_original'])));
    $fechaRegistro = date('Y-m-d H:i:s');
    $fechaLimite = date('Y-m-d', strtotime('+'.$dias.' days', strtotime($fechaRegistro)));
    $rutasEscritas = array();
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("INSERT INTO centro_factura
            (direccion,tipo_documento,fuente,cod_localFK,tipo_contraparte,cod_proveedorFK,cod_funcionarioFK,nombre_contraparte,
             documento_contraparte,numero_factura,numero_factura_normalizado,timbrado,fecha_emision,importe_total,
             moneda,concepto,observaciones,estado_validacion,estado_original,fecha_registro_digital,dias_plazo_original,
             fecha_limite_original,cod_responsable_envioFK,firma_fiscal,posible_duplicado,duplicado_confirmado,
             motivo_confirmacion_duplicado,cod_usuario_confirmacion_duplicadoFK,fecha_confirmacion_duplicado,
             cod_usuario_registroFK)
            VALUES ('entrante',?,'manual',?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pendiente','en_proceso',?,?,?,?,?,?,?,?,?,?,?)");
        if (!$stmt) {
            throw new Exception('No se pudo preparar el registro de la factura: '.$mysqli->error);
        }
        $posible = count($duplicados) > 0 ? 1 : 0;
        $confirmado = ($posible && $confirmarDuplicado) ? 1 : 0;
        $usuarioDup = $confirmado ? $codUsuario : null;
        $fechaDup = $confirmado ? $fechaRegistro : null;
        $tiposManual = 'sisii'.str_repeat('s', 6).'d'.str_repeat('s', 4).'isisiisisi';
        $stmt->bind_param(
            $tiposManual,
            $validos['tipo_documento'], $codLocal, $validos['tipo'], $validos['cod_proveedor'], $validos['cod_funcionario'], $validos['nombre'],
            $validos['documento'], $validos['numero_factura'], $validos['numero_normalizado'], $validos['timbrado'],
            $validos['fecha_emision'], $validos['importe_total'], $validos['moneda'], $validos['concepto'],
            $validos['observaciones'], $fechaRegistro, $dias, $fechaLimite, $validos['cod_responsable'],
            $validos['firma_fiscal'], $posible, $confirmado, $motivoDuplicado, $usuarioDup, $fechaDup, $codUsuario
        );
        if (!$stmt->execute()) {
            throw new Exception('No se pudo crear la factura manual.');
        }
        $idFactura = intval($stmt->insert_id);
        $stmt->close();
        foreach ($preparados as $indice => $preparado) {
            $guardado = centroFacturaGuardarArchivoPreparado($idFactura, $preparado);
            if (empty($guardado['ok'])) {
                throw new Exception($guardado['mensaje']);
            }
            $rutasEscritas[] = $guardado['ruta_absoluta'];
            $tipoOrigen = 'carga_manual';
            $orden = $indice + 1;
            $principal = $indice === 0 ? 1 : 0;
            $stmt = $mysqli->prepare("INSERT INTO centro_factura_archivo
                (id_facturaFK,tipo_origen,url,nombre_original,extension,mime_type,hash_sha256,orden_pagina,es_principal,cod_usuarioFK_create)
                VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('issssssiii', $idFactura, $tipoOrigen, $guardado['url'], $preparado['nombre'], $preparado['extension'], $preparado['mime'], $preparado['hash'], $orden, $principal, $codUsuario);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo registrar una pagina de la factura.');
            }
            $idArchivo = intval($stmt->insert_id);
            $stmt->close();
            centroFacturaAuditar($mysqli, 'archivo', $idArchivo, $idFactura, 'agregar_archivo_manual', array(), array('nombre' => $preparado['nombre'], 'hash' => $preparado['hash']), '', $codUsuario);
        }
        if (!centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'crear_manual', array(), $validos, $motivoDuplicado, $codUsuario)) {
            throw new Exception('No se pudo auditar la factura manual.');
        }
        if ($confirmado) {
            centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'confirmar_no_duplicado', array('posible_duplicado' => 1), array('duplicado_confirmado' => 1), $motivoDuplicado, $codUsuario);
        }
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_factura' => $idFactura, 'posible_duplicado' => $posible);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        foreach ($rutasEscritas as $ruta) {
            centroFacturaEliminarArchivoFisico($ruta);
        }
        return array('ok' => false, 'codigo' => 'registro', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaObtenerRaw($mysqli, $idFactura, $bloquear = false)
{
    $idFactura = intval($idFactura);
    $sql = 'SELECT * FROM centro_factura WHERE id_factura=? LIMIT 1'.($bloquear ? ' FOR UPDATE' : '');
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $idFactura);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroFacturaPuedeEditarRegistro($codUsuario, $factura)
{
    if (centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) {
        return true;
    }
    if (intval($factura['cod_usuario_registroFK']) !== intval($codUsuario)
        || !in_array($factura['estado_validacion'], array('pendiente','en_revision'), true)
        || $factura['estado_registro'] !== 'activo') {
        return false;
    }
    if ($factura['fuente'] === 'hilo') {
        return centroFacturaTienePermiso($codUsuario, 'REGISTRARFACTURAHILO');
    }
    return centroFacturaTienePermiso($codUsuario, 'REGISTRARFACTURAMANUAL');
}

function centroFacturaCandidatosFinancieros($mysqli, $factura)
{
    $salida = array('gastos' => array(), 'compras' => array());
    $idFactura = intval($factura['id_factura']);
    $local = intval($factura['cod_localFK']);
    $hilo = intval($factura['cod_interConsultaFK']);
    $importe = (float)$factura['importe_total'];
    $sql = "SELECT g.idgastos,g.monto,g.motivo,g.fecha,g.estado,g.cod_interConsultaFK,
        cf.id_factura AS vinculada_factura
      FROM gastos g
      LEFT JOIN centro_factura cf ON cf.idgastosFK=g.idgastos AND cf.estado_registro='activo' AND cf.id_factura<>?
      WHERE g.cod_local=? AND LOWER(TRIM(IFNULL(g.estado,''))) NOT IN ('inactivo','baja')
        AND LOWER(TRIM(IFNULL(g.tipo,'')))<>'deposito'
        AND (g.cod_interConsultaFK=? OR ABS(IFNULL(g.monto,0)-?)<0.01)
      ORDER BY (g.cod_interConsultaFK=?) DESC,(ABS(IFNULL(g.monto,0)-?)<0.01) DESC,g.fecha DESC,g.idgastos DESC LIMIT 30";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iiidid', $idFactura, $local, $hilo, $importe, $hilo, $importe);
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $salida['gastos'][] = centroFacturaFilaUtf8($fila);
        }
    }
    $stmt->close();
    $sql = "SELECT cp.cod_compra,cp.num_comprobante,cp.fecha_compra,cp.estado,
        GREATEST(IFNULL((SELECT SUM(dc.subTotal) FROM detalle_compra dc WHERE dc.cod_compraFK=cp.cod_compra),cp.total_compra)-IFNULL(cp.descuento,0),0) AS monto,
        cf.id_factura AS vinculada_factura
      FROM compra cp
      LEFT JOIN centro_factura cf ON cf.cod_compraFK=cp.cod_compra AND cf.estado_registro='activo' AND cf.id_factura<>?
      WHERE cp.cod_local=? AND LOWER(TRIM(IFNULL(cp.estado,'')))<>'inactivo'
      ORDER BY (ABS(GREATEST(IFNULL((SELECT SUM(dc.subTotal) FROM detalle_compra dc WHERE dc.cod_compraFK=cp.cod_compra),cp.total_compra)-IFNULL(cp.descuento,0),0)-?)<0.01) DESC,cp.fecha_compra DESC LIMIT 20";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iid', $idFactura, $local, $importe);
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $salida['compras'][] = centroFacturaFilaUtf8($fila);
        }
    }
    $stmt->close();
    return $salida;
}

function centroFacturaObtenerDetalle($idFactura, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar la factura.');
    }
    $idFactura = intval($idFactura);
    $mysqli = conectar_al_servidor();
    $base = centroFacturaBaseSelect();
    $stmt = $mysqli->prepare('SELECT * FROM ('.$base.' WHERE cf.id_factura=?) detalle LIMIT 1');
    $stmt->bind_param('i', $idFactura);
    $stmt->execute();
    $factura = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$factura || !centroFacturaPuedeUsarLocal($codUsuario, $factura['cod_localFK'], $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'La factura no existe o no pertenece a un local autorizado.');
    }
    $factura = centroFacturaDecorarFila(centroFacturaFilaUtf8($factura));
    $archivos = array();
    $stmt = $mysqli->prepare("SELECT a.*,COALESCE(NULLIF(a.url,''),m.url) AS url_disponible,m.cod_interConsultaFK
        FROM centro_factura_archivo a LEFT JOIN mensaje m ON m.cod_mensaje=a.cod_mensajeFK
        WHERE a.id_facturaFK=? AND a.estado='activo' ORDER BY a.orden_pagina,a.id_archivo");
    $stmt->bind_param('i', $idFactura);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $archivos[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $lotes = array();
    $stmt = $mysqli->prepare("SELECT lo.id_lote,lo.codigo_lote,lo.estado AS lote_estado,lo.fecha_envio,lo.fecha_recepcion,
            ld.estado AS detalle_estado,ld.observacion,l.Nombre AS nombre_local
        FROM centro_factura_lote_detalle ld
        INNER JOIN centro_factura_lote lo ON lo.id_lote=ld.id_loteFK
        INNER JOIN local l ON l.cod_local=lo.cod_local_origenFK
        WHERE ld.id_facturaFK=? ORDER BY ld.id_lote_detalle DESC");
    $stmt->bind_param('i', $idFactura);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $lotes[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $auditoria = array();
    if (centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) {
        $stmt = $mysqli->prepare("SELECT a.*,p.nombre_persona AS usuario_nombre
            FROM centro_factura_auditoria a LEFT JOIN persona p ON p.cod_persona=a.cod_usuarioFK
            WHERE a.id_facturaFK=? ORDER BY a.id_auditoria DESC LIMIT 100");
        $stmt->bind_param('i', $idFactura);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $auditoria[] = centroFacturaFilaUtf8($fila);
        }
        $stmt->close();
    }
    $ocr = array();
    $stmt = $mysqli->prepare("SELECT o.* FROM centro_factura_ocr_sugerencia o
        INNER JOIN centro_factura_archivo a ON a.id_archivo=o.id_archivoFK
        WHERE a.id_facturaFK=? ORDER BY o.id_sugerencia DESC");
    $stmt->bind_param('i', $idFactura);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $ocr[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $duplicados = !empty($factura['firma_fiscal']) ? centroFacturaBuscarDuplicados($mysqli, $factura['firma_fiscal'], $idFactura, $factura['tipo_documento']) : array();
    $candidatos = centroFacturaTienePermiso($codUsuario, 'VINCULARPAGOFACTURA') ? centroFacturaCandidatosFinancieros($mysqli, $factura) : array('gastos' => array(), 'compras' => array());
    $mysqli->close();
    return array(
        'ok' => true,
        'factura' => $factura,
        'archivos' => $archivos,
        'lotes' => $lotes,
        'auditoria' => $auditoria,
        'ocr' => $ocr,
        'duplicados' => $duplicados,
        'candidatos_financieros' => $candidatos,
        'puede_editar' => centroFacturaPuedeEditarRegistro($codUsuario, $factura) ? 1 : 0
    );
}

function centroFacturaActualizarColumnas($mysqli, $tabla, $campos, $whereSql, $whereTipos, $whereParametros)
{
    $tabla = preg_replace('/[^A-Za-z0-9_]/', '', (string)$tabla);
    if ($tabla === '' || count($campos) === 0) {
        return array('ok' => false, 'mensaje' => 'No hay cambios para guardar.');
    }
    $asignaciones = array();
    $tipos = '';
    $parametros = array();
    foreach ($campos as $columna => $definicion) {
        $columna = preg_replace('/[^A-Za-z0-9_]/', '', (string)$columna);
        if ($columna === '' || !is_array($definicion) || count($definicion) < 2) {
            continue;
        }
        $asignaciones[] = '`'.$columna.'`=?';
        $tipos .= $definicion[0];
        $parametros[] = $definicion[1];
    }
    if (count($asignaciones) === 0) {
        return array('ok' => false, 'mensaje' => 'No hay cambios validos para guardar.');
    }
    foreach ((array)$whereParametros as $parametro) {
        $parametros[] = $parametro;
    }
    $tipos .= $whereTipos;
    $stmt = $mysqli->prepare('UPDATE `'.$tabla.'` SET '.implode(',', $asignaciones).' WHERE '.$whereSql);
    if (!$stmt) {
        return array('ok' => false, 'mensaje' => 'No se pudo preparar la actualizacion.');
    }
    centroFacturaBind($stmt, $tipos, $parametros);
    $ok = $stmt->execute();
    $afectadas = $stmt->affected_rows;
    $error = $stmt->error;
    $numeroError = $stmt->errno;
    $stmt->close();
    return array('ok' => $ok, 'afectadas' => $afectadas, 'mensaje' => $error, 'errno' => $numeroError);
}

function centroFacturaGuardarDatos($idFactura, $datos, $codUsuario)
{
    $idFactura = intval($idFactura);
    $codUsuario = intval($codUsuario);
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $anterior = centroFacturaObtenerRaw($mysqli, $idFactura, true);
        if (!$anterior || !centroFacturaPuedeUsarLocal($codUsuario, $anterior['cod_localFK'], $mysqli)) {
            throw new Exception('La factura no existe o no pertenece a un local autorizado.');
        }
        if (!centroFacturaPuedeEditarRegistro($codUsuario, $anterior)) {
            throw new Exception('No tiene permiso para corregir esta factura.');
        }
        $validos = centroFacturaValidarDatosCompletos($mysqli, (array)$datos);
        if (empty($validos['ok'])) {
            throw new Exception($validos['mensaje']);
        }
        if ($validos['tipo_documento'] === 'recibo' && $anterior['tipo_documento'] !== 'recibo') {
            $stmtLote = $mysqli->prepare("SELECT 1 FROM centro_factura_lote_detalle ld
                INNER JOIN centro_factura_lote lo ON lo.id_lote=ld.id_loteFK
                WHERE ld.id_facturaFK=? AND ld.estado<>'retirada' AND lo.estado<>'anulado' LIMIT 1");
            $stmtLote->bind_param('i', $idFactura);
            $stmtLote->execute();
            $estaEnLote = $stmtLote->get_result()->num_rows > 0;
            $stmtLote->close();
            if ($estaEnLote) {
                throw new Exception('Una factura incluida en un lote de originales no puede convertirse en recibo.');
            }
        }
        $duplicados = centroFacturaBuscarDuplicados($mysqli, $validos['firma_fiscal'], $idFactura, $validos['tipo_documento']);
        $confirmarDuplicado = !empty($datos['confirmar_duplicado']);
        $motivoDuplicado = isset($datos['motivo_duplicado']) ? centroFacturaTextoBaseDatos($datos['motivo_duplicado'], 255) : '';
        if (count($duplicados) > 0 && (!$confirmarDuplicado || !centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS') || $motivoDuplicado === '')) {
            $mysqli->rollback();
            $mysqli->close();
            return array('ok' => false, 'codigo' => 'posible_duplicado', 'mensaje' => 'Existe un comprobante del mismo tipo con los mismos datos principales.', 'duplicados' => $duplicados);
        }
        $versionEsperada = isset($datos['version_registro']) ? intval($datos['version_registro']) : intval($anterior['version_registro']);
        if ($versionEsperada !== intval($anterior['version_registro'])) {
            throw new Exception('La factura fue modificada por otro usuario. Actualice el detalle antes de guardar.');
        }
        $esAdmin = centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS');
        $estadoValidacion = $anterior['estado_validacion'];
        if (!empty($datos['enviar_revision']) && in_array($estadoValidacion, array('pendiente','en_revision'), true)) {
            $estadoValidacion = 'en_revision';
        }
        if ($esAdmin && isset($datos['estado_validacion']) && in_array($datos['estado_validacion'], array('pendiente','en_revision','validada'), true)) {
            $estadoValidacion = $datos['estado_validacion'];
        }
        $fechaLimite = $anterior['fecha_limite_original'];
        $motivoCambio = isset($datos['motivo_cambio']) ? centroFacturaTextoBaseDatos($datos['motivo_cambio'], 255) : '';
        if (isset($datos['fecha_limite_original']) && $datos['fecha_limite_original'] !== '') {
            $limiteSolicitado = centroFacturaFechaValida($datos['fecha_limite_original'], false);
            if (!$limiteSolicitado) {
                throw new Exception('La fecha limite no es valida.');
            }
            if ($limiteSolicitado !== $fechaLimite) {
                if (!$esAdmin || $motivoCambio === '') {
                    throw new Exception('Cambiar la fecha limite requiere permiso superior y un motivo.');
                }
                $fechaLimite = $limiteSolicitado;
            }
        }
        $posible = count($duplicados) > 0 ? 1 : 0;
        $confirmado = ($posible && $confirmarDuplicado) ? 1 : 0;
        $campos = array(
            'tipo_documento' => array('s', $validos['tipo_documento']),
            'tipo_contraparte' => array('s', $validos['tipo']),
            'cod_proveedorFK' => array('i', $validos['cod_proveedor']),
            'cod_funcionarioFK' => array('i', $validos['cod_funcionario']),
            'nombre_contraparte' => array('s', $validos['nombre']),
            'documento_contraparte' => array('s', $validos['documento']),
            'numero_factura' => array('s', $validos['numero_factura']),
            'numero_factura_normalizado' => array('s', $validos['numero_normalizado']),
            'timbrado' => array('s', $validos['timbrado']),
            'fecha_emision' => array('s', $validos['fecha_emision']),
            'importe_total' => array('d', $validos['importe_total']),
            'moneda' => array('s', $validos['moneda']),
            'concepto' => array('s', $validos['concepto']),
            'observaciones' => array('s', $validos['observaciones']),
            'estado_validacion' => array('s', $estadoValidacion),
            'fecha_limite_original' => array('s', $fechaLimite),
            'cod_responsable_envioFK' => array('i', $validos['cod_responsable']),
            'firma_fiscal' => array('s', $validos['firma_fiscal']),
            'posible_duplicado' => array('i', $posible),
            'duplicado_confirmado' => array('i', $confirmado),
            'motivo_confirmacion_duplicado' => array('s', $confirmado ? $motivoDuplicado : null),
            'cod_usuario_confirmacion_duplicadoFK' => array('i', $confirmado ? $codUsuario : null),
            'fecha_confirmacion_duplicado' => array('s', $confirmado ? date('Y-m-d H:i:s') : null),
            'cod_usuario_actualizacionFK' => array('i', $codUsuario),
            'fecha_actualizacion' => array('s', date('Y-m-d H:i:s')),
            'version_registro' => array('i', intval($anterior['version_registro']) + 1)
        );
        $actualizacion = centroFacturaActualizarColumnas($mysqli, 'centro_factura', $campos, 'id_factura=? AND version_registro=?', 'ii', array($idFactura, $versionEsperada));
        if (empty($actualizacion['ok']) || intval($actualizacion['afectadas']) !== 1) {
            throw new Exception('No se pudo guardar porque la factura cambio durante la edicion.');
        }
        $nuevo = array_merge($validos, array('estado_validacion' => $estadoValidacion, 'fecha_limite_original' => $fechaLimite));
        if (!centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'editar_datos', $anterior, $nuevo, $motivoCambio, $codUsuario)) {
            throw new Exception('No se pudo auditar la correccion.');
        }
        if ($confirmado) {
            centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'confirmar_no_duplicado', array('posible_duplicado' => 1), array('duplicado_confirmado' => 1), $motivoDuplicado, $codUsuario);
        }
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_factura' => $idFactura);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'edicion', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaAgregarArchivos($idFactura, $archivos, $codUsuario, $tipoOrigen = 'carga_manual')
{
    $idFactura = intval($idFactura);
    $codUsuario = intval($codUsuario);
    $tipoOrigen = in_array($tipoOrigen, array('carga_manual','evidencia_observacion','otro'), true) ? $tipoOrigen : 'otro';
    if (!is_array($archivos) || count($archivos) === 0 || count($archivos) > 10) {
        return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => 'Seleccione entre uno y diez archivos.');
    }
    $preparados = array();
    foreach ($archivos as $archivo) {
        $preparado = centroFacturaPrepararArchivo((array)$archivo);
        if (empty($preparado['ok'])) {
            return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => $preparado['mensaje']);
        }
        $preparados[] = $preparado;
    }
    $mysqli = conectar_al_servidor();
    $factura = centroFacturaObtenerRaw($mysqli, $idFactura, false);
    $puedeAgregar = $factura ? centroFacturaPuedeEditarRegistro($codUsuario, $factura) : false;
    if ($factura && $tipoOrigen === 'evidencia_observacion'
        && $factura['estado_registro'] === 'activo' && $factura['estado_original'] === 'observado'
        && centroFacturaTienePermiso($codUsuario, 'RECIBIRORIGINALFACTURA')) {
        $puedeAgregar = true;
    }
    if (!$factura || !centroFacturaPuedeUsarLocal($codUsuario, $factura['cod_localFK'], $mysqli) || !$puedeAgregar) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No puede agregar archivos a esta factura.');
    }
    $resultado = $mysqli->query('SELECT IFNULL(MAX(orden_pagina),0) maximo FROM centro_factura_archivo WHERE id_facturaFK='.$idFactura." AND estado='activo'");
    $orden = $resultado ? intval($resultado->fetch_assoc()['maximo']) : 0;
    $rutas = array();
    $mysqli->begin_transaction();
    try {
        foreach ($preparados as $preparado) {
            $orden++;
            $guardado = centroFacturaGuardarArchivoPreparado($idFactura, $preparado);
            if (empty($guardado['ok'])) {
                throw new Exception($guardado['mensaje']);
            }
            $rutas[] = $guardado['ruta_absoluta'];
            $stmt = $mysqli->prepare("INSERT INTO centro_factura_archivo
                (id_facturaFK,tipo_origen,url,nombre_original,extension,mime_type,hash_sha256,orden_pagina,es_principal,cod_usuarioFK_create)
                VALUES (?,?,?,?,?,?,?,?,0,?)");
            $stmt->bind_param('issssssii', $idFactura, $tipoOrigen, $guardado['url'], $preparado['nombre'], $preparado['extension'], $preparado['mime'], $preparado['hash'], $orden, $codUsuario);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo registrar el archivo adicional.');
            }
            $idArchivo = intval($stmt->insert_id);
            $stmt->close();
            centroFacturaAuditar($mysqli, 'archivo', $idArchivo, $idFactura, 'agregar_archivo', array(), array('nombre' => $preparado['nombre'], 'tipo' => $tipoOrigen), '', $codUsuario);
        }
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_factura' => $idFactura, 'archivos_agregados' => count($preparados));
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        foreach ($rutas as $ruta) {
            centroFacturaEliminarArchivoFisico($ruta);
        }
        return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaVincularMovimiento($idFactura, $tipo, $idMovimiento, $motivo, $codUsuario)
{
    $idFactura = intval($idFactura);
    $idMovimiento = intval($idMovimiento);
    $tipo = centroFacturaTextoBaseDatos($tipo, 20);
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    $codUsuario = intval($codUsuario);
    if (!centroFacturaTienePermiso($codUsuario, 'VINCULARPAGOFACTURA')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para vincular movimientos financieros.');
    }
    if (!in_array($tipo, array('gasto','compra'), true) || $idMovimiento <= 0) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'Seleccione un movimiento financiero valido.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $factura = centroFacturaObtenerRaw($mysqli, $idFactura, true);
        if (!$factura || !centroFacturaPuedeUsarLocal($codUsuario, $factura['cod_localFK'], $mysqli)) {
            throw new Exception('La factura no pertenece a un local autorizado.');
        }
        $tieneVinculo = intval($factura['idgastosFK']) > 0 || intval($factura['cod_compraFK']) > 0;
        $mismoVinculo = ($tipo === 'gasto' && intval($factura['idgastosFK']) === $idMovimiento)
            || ($tipo === 'compra' && intval($factura['cod_compraFK']) === $idMovimiento);
        if ($tieneVinculo && !$mismoVinculo && (!centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS') || $motivo === '')) {
            throw new Exception('Reemplazar un vinculo financiero requiere permiso superior y un motivo.');
        }
        if ($tipo === 'gasto') {
            $stmt = $mysqli->prepare("SELECT idgastos,cod_local,estado,monto,tipo FROM gastos WHERE idgastos=? LIMIT 1 FOR UPDATE");
        } else {
            $stmt = $mysqli->prepare("SELECT cod_compra,cod_local,estado,total_compra FROM compra WHERE cod_compra=? LIMIT 1 FOR UPDATE");
        }
        $stmt->bind_param('i', $idMovimiento);
        $stmt->execute();
        $movimiento = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$movimiento || intval($movimiento['cod_local']) !== intval($factura['cod_localFK'])) {
            throw new Exception('El movimiento no existe o pertenece a otro local.');
        }
        if ($tipo === 'gasto' && strtolower(trim((string)$movimiento['tipo'])) === 'deposito') {
            throw new Exception('Los depositos a central descuentan efectivo de caja, pero no son pagos de facturas ni egresos operativos.');
        }
        if (in_array(strtolower(trim((string)$movimiento['estado'])), array('inactivo','anulado','baja'), true)) {
            throw new Exception('El movimiento seleccionado ya no esta activo.');
        }
        if ($mismoVinculo) {
            $mysqli->commit();
            $mysqli->close();
            return array('ok' => true, 'idempotente' => 1);
        }
        $columna = $tipo === 'gasto' ? 'idgastosFK' : 'cod_compraFK';
        $otraColumna = $tipo === 'gasto' ? 'cod_compraFK' : 'idgastosFK';
        $stmt = $mysqli->prepare("SELECT id_factura FROM centro_factura WHERE $columna=? AND estado_registro='activo' AND id_factura<>? LIMIT 1 FOR UPDATE");
        $stmt->bind_param('ii', $idMovimiento, $idFactura);
        $stmt->execute();
        $yaUsado = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($yaUsado) {
            throw new Exception('El movimiento ya esta vinculado a otra factura.');
        }
        $campos = array(
            $columna => array('i', $idMovimiento),
            $otraColumna => array('i', null),
            'fecha_vinculacion_pago' => array('s', date('Y-m-d H:i:s')),
            'cod_usuario_vinculacion_pagoFK' => array('i', $codUsuario),
            'cod_usuario_actualizacionFK' => array('i', $codUsuario),
            'fecha_actualizacion' => array('s', date('Y-m-d H:i:s')),
            'version_registro' => array('i', intval($factura['version_registro']) + 1)
        );
        $actualizacion = centroFacturaActualizarColumnas($mysqli, 'centro_factura', $campos, 'id_factura=?', 'i', array($idFactura));
        if (empty($actualizacion['ok'])) {
            throw new Exception($actualizacion['errno'] == 1062 ? 'El movimiento ya esta vinculado a otra factura.' : 'No se pudo vincular el movimiento.');
        }
        centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'vincular_movimiento_financiero', array('idgastosFK' => $factura['idgastosFK'], 'cod_compraFK' => $factura['cod_compraFK']), array('tipo' => $tipo, 'id' => $idMovimiento), $motivo, $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_factura' => $idFactura, 'idempotente' => 0);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'vinculo', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaDesvincularMovimiento($idFactura, $motivo, $codUsuario)
{
    $idFactura = intval($idFactura);
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    $codUsuario = intval($codUsuario);
    if (!centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS') || $motivo === '') {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'Desvincular requiere permiso superior y un motivo.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $factura = centroFacturaObtenerRaw($mysqli, $idFactura, true);
        if (!$factura || !centroFacturaPuedeUsarLocal($codUsuario, $factura['cod_localFK'], $mysqli)) {
            throw new Exception('La factura no pertenece a un local autorizado.');
        }
        $campos = array(
            'idgastosFK' => array('i', null), 'cod_compraFK' => array('i', null),
            'fecha_vinculacion_pago' => array('s', null), 'cod_usuario_vinculacion_pagoFK' => array('i', null),
            'cod_usuario_actualizacionFK' => array('i', $codUsuario), 'fecha_actualizacion' => array('s', date('Y-m-d H:i:s')),
            'version_registro' => array('i', intval($factura['version_registro']) + 1)
        );
        $actualizacion = centroFacturaActualizarColumnas($mysqli, 'centro_factura', $campos, 'id_factura=?', 'i', array($idFactura));
        if (empty($actualizacion['ok'])) {
            throw new Exception('No se pudo desvincular el movimiento.');
        }
        centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'desvincular_movimiento_financiero', array('idgastosFK' => $factura['idgastosFK'], 'cod_compraFK' => $factura['cod_compraFK']), array(), $motivo, $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'vinculo', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaCambiarOriginal($idFactura, $accion, $datos, $codUsuario)
{
    $idFactura = intval($idFactura);
    $codUsuario = intval($codUsuario);
    $accion = centroFacturaTextoBaseDatos($accion, 40);
    $permisos = array(
        'enviar' => 'ENVIARORIGINALFACTURA',
        'recibir' => 'RECIBIRORIGINALFACTURA',
        'observar' => 'RECIBIRORIGINALFACTURA',
        'no_requiere' => 'ADMINCENTROFACTURAS',
        'revertir' => 'ADMINCENTROFACTURAS'
    );
    if (!isset($permisos[$accion]) || !centroFacturaTienePermiso($codUsuario, $permisos[$accion])) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para realizar esta accion sobre el original.');
    }
    $motivo = isset($datos['motivo']) ? centroFacturaTextoBaseDatos($datos['motivo'], 255) : '';
    if (in_array($accion, array('no_requiere','revertir'), true) && $motivo === '') {
        return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Ingrese el motivo obligatorio.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $factura = centroFacturaObtenerRaw($mysqli, $idFactura, true);
        if (!$factura || !centroFacturaPuedeUsarLocal($codUsuario, $factura['cod_localFK'], $mysqli) || $factura['estado_registro'] !== 'activo') {
            throw new Exception('La factura no esta disponible para esta accion.');
        }
        $ahora = date('Y-m-d H:i:s');
        $campos = array(
            'cod_usuario_actualizacionFK' => array('i', $codUsuario),
            'fecha_actualizacion' => array('s', $ahora),
            'version_registro' => array('i', intval($factura['version_registro']) + 1)
        );
        $accionAuditoria = '';
        if ($accion === 'enviar') {
            if (in_array($factura['estado_original'], array('recibido','no_requiere_original'), true)) {
                throw new Exception('El original ya fue recibido o no es requerido.');
            }
            $campos['estado_original'] = array('s', 'enviado_central');
            $campos['fecha_envio_central'] = array('s', $ahora);
            $campos['cod_responsable_envioFK'] = array('i', !empty($datos['cod_responsable']) ? intval($datos['cod_responsable']) : $codUsuario);
            $accionAuditoria = 'marcar_enviado_central';
        } elseif ($accion === 'recibir') {
            $campos['estado_original'] = array('s', 'recibido');
            $campos['fecha_recepcion_fisica'] = array('s', $ahora);
            $campos['cod_usuario_recepcionFK'] = array('i', $codUsuario);
            foreach (array('lote_archivo','carpeta_archivo','caja_archivo','periodo_archivo','ubicacion_fisica') as $campo) {
                $campos[$campo] = array('s', isset($datos[$campo]) ? centroFacturaTextoBaseDatos($datos[$campo], $campo === 'ubicacion_fisica' ? 255 : 100) : '');
            }
            $campos['motivo_observacion'] = array('s', null);
            $campos['comentario_observacion'] = array('s', null);
            $campos['cod_responsable_observacionFK'] = array('i', null);
            $campos['cod_usuario_observacionFK'] = array('i', null);
            $campos['fecha_observacion'] = array('s', null);
            $accionAuditoria = 'recibir_original';
        } elseif ($accion === 'observar') {
            $motivosPermitidos = array('importe_diferente','numero_diferente','factura_ilegible','documento_danado','documento_incompleto','falta_firma','factura_equivocada','duplicada','otro');
            $tipoMotivo = isset($datos['motivo_observacion']) ? centroFacturaTextoBaseDatos($datos['motivo_observacion'], 100) : '';
            $comentario = isset($datos['comentario_observacion']) ? centroFacturaTextoBaseDatos($datos['comentario_observacion'], 3000, true) : '';
            if (!in_array($tipoMotivo, $motivosPermitidos, true) || $comentario === '') {
                throw new Exception('Seleccione el motivo y describa la observacion.');
            }
            $campos['estado_original'] = array('s', 'observado');
            $campos['fecha_recepcion_fisica'] = array('s', $factura['fecha_recepcion_fisica'] ? $factura['fecha_recepcion_fisica'] : $ahora);
            $campos['cod_usuario_recepcionFK'] = array('i', $factura['cod_usuario_recepcionFK'] ? $factura['cod_usuario_recepcionFK'] : $codUsuario);
            $campos['motivo_observacion'] = array('s', $tipoMotivo);
            $campos['comentario_observacion'] = array('s', $comentario);
            $campos['cod_responsable_observacionFK'] = array('i', !empty($datos['cod_responsable']) ? intval($datos['cod_responsable']) : $factura['cod_responsable_envioFK']);
            $campos['cod_usuario_observacionFK'] = array('i', $codUsuario);
            $campos['fecha_observacion'] = array('s', $ahora);
            $accionAuditoria = 'observar_original';
            $motivo = $tipoMotivo;
        } elseif ($accion === 'no_requiere') {
            $campos['estado_original'] = array('s', 'no_requiere_original');
            $accionAuditoria = 'marcar_no_requiere_original';
        } else {
            $estadoDestino = $factura['fecha_envio_central'] ? 'enviado_central' : 'en_proceso';
            $campos['estado_original'] = array('s', $estadoDestino);
            $campos['fecha_recepcion_fisica'] = array('s', null);
            $campos['cod_usuario_recepcionFK'] = array('i', null);
            $campos['lote_archivo'] = array('s', null);
            $campos['carpeta_archivo'] = array('s', null);
            $campos['caja_archivo'] = array('s', null);
            $campos['periodo_archivo'] = array('s', null);
            $campos['ubicacion_fisica'] = array('s', null);
            $campos['motivo_observacion'] = array('s', null);
            $campos['comentario_observacion'] = array('s', null);
            $campos['cod_responsable_observacionFK'] = array('i', null);
            $campos['cod_usuario_observacionFK'] = array('i', null);
            $campos['fecha_observacion'] = array('s', null);
            $accionAuditoria = 'revertir_estado_original';
        }
        $actualizacion = centroFacturaActualizarColumnas($mysqli, 'centro_factura', $campos, 'id_factura=?', 'i', array($idFactura));
        if (empty($actualizacion['ok'])) {
            throw new Exception('No se pudo actualizar el estado del original.');
        }
        $nuevo = centroFacturaObtenerRaw($mysqli, $idFactura, false);
        if (!centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, $accionAuditoria, $factura, $nuevo, $motivo, $codUsuario)) {
            throw new Exception('No se pudo auditar el cambio del original.');
        }
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_factura' => $idFactura, 'estado_original' => $nuevo['estado_original']);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'original', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaCambiarValidacion($idFactura, $estado, $motivo, $codUsuario)
{
    $idFactura = intval($idFactura);
    $codUsuario = intval($codUsuario);
    $estado = centroFacturaTextoBaseDatos($estado, 20);
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    if (!centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para cambiar la validacion.');
    }
    if (!in_array($estado, array('pendiente','en_revision','validada','rechazada','anulada'), true)) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'El estado de validacion no es valido.');
    }
    if (in_array($estado, array('rechazada','anulada'), true) && $motivo === '') {
        return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Ingrese el motivo obligatorio.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $factura = centroFacturaObtenerRaw($mysqli, $idFactura, true);
        if (!$factura || !centroFacturaPuedeUsarLocal($codUsuario, $factura['cod_localFK'], $mysqli)) {
            throw new Exception('La factura no pertenece a un local autorizado.');
        }
        $campos = array(
            'estado_validacion' => array('s', $estado),
            'cod_usuario_actualizacionFK' => array('i', $codUsuario),
            'fecha_actualizacion' => array('s', date('Y-m-d H:i:s')),
            'version_registro' => array('i', intval($factura['version_registro']) + 1)
        );
        if ($estado === 'anulada') {
            $campos['estado_registro'] = array('s', 'anulado');
            $campos['motivo_anulacion'] = array('s', $motivo);
            $campos['cod_usuario_anulacionFK'] = array('i', $codUsuario);
            $campos['fecha_anulacion'] = array('s', date('Y-m-d H:i:s'));
        }
        $actualizacion = centroFacturaActualizarColumnas($mysqli, 'centro_factura', $campos, 'id_factura=?', 'i', array($idFactura));
        if (empty($actualizacion['ok'])) {
            throw new Exception('No se pudo cambiar la validacion.');
        }
        centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'cambiar_validacion', array('estado_validacion' => $factura['estado_validacion']), array('estado_validacion' => $estado), $motivo, $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'estado_validacion' => $estado);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'validacion', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaActualizarConfiguracion($dias, $ocrHabilitado, $ocrProveedor, $codUsuario)
{
    $codUsuario = intval($codUsuario);
    if (!centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para modificar la configuracion.');
    }
    $dias = intval($dias);
    if ($dias < 1 || $dias > 60) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'El plazo debe estar entre 1 y 60 dias corridos.');
    }
    $ocrHabilitado = intval($ocrHabilitado) === 1 ? 1 : 0;
    $ocrProveedor = centroFacturaTextoBaseDatos($ocrProveedor, 60);
    if ($ocrHabilitado && $ocrProveedor === '') {
        return array('ok' => false, 'codigo' => 'ocr', 'mensaje' => 'Para habilitar OCR debe configurarse primero un proveedor estable.');
    }
    $mysqli = conectar_al_servidor();
    $anterior = centroFacturaConfiguracion($mysqli);
    $stmt = $mysqli->prepare("UPDATE centro_factura_configuracion SET dias_plazo_original=?,ocr_habilitado=?,ocr_proveedor=?,cod_usuarioFK_update=?,fecha_actualizacion=NOW() WHERE id_configuracion=1");
    $stmt->bind_param('iisi', $dias, $ocrHabilitado, $ocrProveedor, $codUsuario);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        centroFacturaAuditar($mysqli, 'configuracion', 1, null, 'actualizar_configuracion', $anterior, array('dias_plazo_original' => $dias, 'ocr_habilitado' => $ocrHabilitado, 'ocr_proveedor' => $ocrProveedor), 'No modifica vencimientos historicos.', $codUsuario);
    }
    $mysqli->close();
    return $ok ? array('ok' => true, 'dias_plazo_original' => $dias) : array('ok' => false, 'codigo' => 'configuracion', 'mensaje' => 'No se pudo guardar la configuracion.');
}

function centroFacturaCoincideFiltrosEmitidas($fila, $filtros, $omitirRapido = false)
{
    $busqueda = isset($filtros['busqueda']) ? trim((string)$filtros['busqueda']) : '';
    if ($busqueda !== '') {
        $texto = implode(' ', array($fila['titular'], $fila['documento'], $fila['numero_factura'], $fila['cod_venta'], $fila['recibo_interno']));
        if (stripos(centroFacturaValorUtf8($texto), centroFacturaValorUtf8($busqueda)) === false) {
            return false;
        }
    }
    $importe = isset($fila['importe_evento']) ? (float)$fila['importe_evento'] : 0;
    if (!empty($filtros['importe_desde']) && $importe < centroFacturaImporte($filtros['importe_desde'])) return false;
    if (!empty($filtros['importe_hasta']) && $importe > centroFacturaImporte($filtros['importe_hasta'])) return false;
    if (!$omitirRapido) {
        $rapido = !empty($filtros['filtro_rapido']) ? (string)$filtros['filtro_rapido'] : '';
        if ($rapido === 'contado' && $fila['tipo_evento'] !== 'contado') return false;
        if ($rapido === 'cuotas' && $fila['tipo_evento'] !== 'cuota') return false;
        if ($rapido === 'con_factura' && $fila['estado_documental'] !== 'facturada') return false;
        if ($rapido === 'sin_factura' && $fila['estado_documental'] !== 'sin_factura') return false;
        if ($rapido === 'observadas' && $fila['estado_documental'] !== 'observada') return false;
        if ($rapido === 'anuladas' && empty($fila['es_anulada'])) return false;
    }
    return true;
}

function centroFacturaMetricasEmitidas($registros)
{
    $metricas = array('eventos_periodo' => 0, 'ventas_contado' => 0, 'cuotas_cobradas' => 0,
        'facturadas' => 0, 'sin_factura' => 0, 'observadas' => 0, 'anuladas' => 0);
    foreach ($registros as $fila) {
        $metricas['eventos_periodo']++;
        if ($fila['tipo_evento'] === 'contado') $metricas['ventas_contado']++;
        if ($fila['tipo_evento'] === 'cuota') $metricas['cuotas_cobradas']++;
        if ($fila['estado_documental'] === 'facturada') $metricas['facturadas']++;
        if ($fila['estado_documental'] === 'sin_factura') $metricas['sin_factura']++;
        if ($fila['estado_documental'] === 'observada') $metricas['observadas']++;
        if (!empty($fila['es_anulada'])) $metricas['anuladas']++;
    }
    return $metricas;
}

function centroFacturaListarEmitidas($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para ver las facturas emitidas.');
    }
    $limite = max(1, min(150, intval($limite)));
    $offset = max(0, intval($offset));
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    if (empty($contexto)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'contexto', 'mensaje' => 'No se pudo determinar el local del usuario.');
    }
    $filtros = (array)$filtros;
    list($desde, $hasta) = centroFacturaRangoPeriodo($filtros);
    $condicionesLocal = array();
    $tipos = 'ss';
    $parametrosBase = array($desde, $hasta);
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $condicionesLocal[] = 'v.cod_local=?';
        $tipos .= 'i';
        $parametrosBase[] = intval($contexto['cod_localFK']);
    } elseif (!empty($filtros['cod_local'])) {
        $condicionesLocal[] = 'v.cod_local=?';
        $tipos .= 'i';
        $parametrosBase[] = intval($filtros['cod_local']);
    }
    $condicionLocal = count($condicionesLocal) ? ' AND '.implode(' AND ', $condicionesLocal) : '';
    $condicionActiva = empty($filtros['incluir_anuladas'])
        ? " AND LOWER(TRIM(IFNULL(v.estado,''))) NOT IN ('inactivo','anulado') AND LOWER(TRIM(IFNULL(v.anulado,''))) NOT IN ('si','anulado','activo')"
        : '';
    $registros = array();

    $sqlContado = "SELECT v.cod_venta,v.fecha_venta AS fecha_evento,'contado' AS tipo_evento,
        0 AS id_pago,'' AS recibo_interno,GREATEST(IFNULL(v.total_venta,0)-IFNULL(v.descuento,0),0) AS importe_evento,
        p.nombre_persona AS titular,COALESCE(NULLIF(c.rut_cliente,''),c.ci_cliente) AS documento,
        l.Nombre AS nombre_local,pe.nombre_persona AS usuario_responsable,
        CASE WHEN UPPER(TRIM(IFNULL(v.tipo_comprobante,'')))='FACTURA' THEN
          CONCAT(IFNULL(NULLIF(TRIM(v.puntoexpedicion),''),''),CASE WHEN TRIM(IFNULL(v.puntoexpedicion,''))<>'' THEN '-' ELSE '' END,IFNULL(v.num_factura,''))
          ELSE '' END AS numero_factura,
        CASE WHEN UPPER(TRIM(IFNULL(v.tipo_comprobante,'')))='FACTURA' THEN v.fecha_venta ELSE NULL END AS fecha_facturado,
        nf.timbrado,
        CASE
          WHEN LOWER(TRIM(IFNULL(v.estado,''))) IN ('inactivo','anulado') OR LOWER(TRIM(IFNULL(v.anulado,''))) IN ('si','anulado','activo') THEN 'observada'
          WHEN UPPER(TRIM(IFNULL(v.tipo_comprobante,'')))='FACTURA' AND TRIM(IFNULL(v.num_factura,''))<>'' THEN 'facturada'
          ELSE 'sin_factura'
        END AS estado_documental,
        CASE WHEN LOWER(TRIM(IFNULL(v.estado,''))) IN ('inactivo','anulado') OR LOWER(TRIM(IFNULL(v.anulado,''))) IN ('si','anulado','activo') THEN 1 ELSE 0 END AS es_anulada
      FROM venta v
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      LEFT JOIN usuario ue ON ue.cod_usuario=v.cod_usuarioFK
      LEFT JOIN persona pe ON pe.cod_persona=ue.cod_usuario
      LEFT JOIN nrofactura nf ON nf.Cod_Nro=CAST(NULLIF(v.codnrofactura,'') AS UNSIGNED)
      WHERE UPPER(TRIM(IFNULL(v.TipoVenta,'')))='CONTADO' AND v.fecha_venta>=? AND v.fecha_venta<=?".$condicionLocal.$condicionActiva;
    $stmt = $mysqli->prepare($sqlContado);
    $parametros = $parametrosBase;
    if ($stmt) {
        centroFacturaBind($stmt, $tipos, $parametros);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) $registros[] = centroFacturaFilaUtf8($fila);
        }
        $stmt->close();
    }

    $condicionPagoActivo = empty($filtros['incluir_anuladas'])
        ? " AND LOWER(TRIM(IFNULL(pg.anulado,''))) NOT IN ('si','anulado','activo')"
        : '';
    $sqlCuotas = "SELECT v.cod_venta,ev.fecha_evento,'cuota' AS tipo_evento,ev.id_pago,ev.recibo_interno,
        ev.importe_evento,p.nombre_persona AS titular,COALESCE(NULLIF(c.rut_cliente,''),c.ci_cliente) AS documento,
        l.Nombre AS nombre_local,pc.nombre_persona AS usuario_responsable,
        ev.numero_factura,ev.fecha_facturado,'' AS timbrado,
        CASE
          WHEN ev.cantidad_numeros>1 THEN 'observada'
          WHEN ev.cantidad_numeros=1 AND ev.fecha_facturado IS NOT NULL THEN 'facturada'
          WHEN ev.cantidad_numeros>0 OR ev.cantidad_fechas>0 THEN 'observada'
          ELSE 'sin_factura'
        END AS estado_documental,
        CASE WHEN ev.es_anulada>0 THEN 1 ELSE 0 END AS es_anulada
      FROM (
        SELECT pg.cod_venta_fk,
          CASE WHEN TRIM(IFNULL(pg.nrofactura,'')) NOT IN ('','0','0000') THEN CONCAT('R:',TRIM(pg.nrofactura)) ELSE CONCAT('P:',pg.idPago) END AS clave_evento,
          MIN(pg.idPago) AS id_pago,MAX(pg.Fecha) AS fecha_evento,SUM(IFNULL(pg.Monto,0)) AS importe_evento,
          MAX(CASE WHEN TRIM(IFNULL(pg.nrofactura,'')) NOT IN ('','0','0000') THEN TRIM(pg.nrofactura) ELSE '' END) AS recibo_interno,
          MAX(NULLIF(TRIM(IFNULL(pg.num_comprobante,'')),'')) AS numero_factura,MAX(pg.fecha_facturado) AS fecha_facturado,
          COUNT(DISTINCT NULLIF(TRIM(IFNULL(pg.num_comprobante,'')),'')) AS cantidad_numeros,
          SUM(CASE WHEN pg.fecha_facturado IS NOT NULL THEN 1 ELSE 0 END) AS cantidad_fechas,
          MAX(pg.cod_cobradorFK) AS cod_cobradorFK,
          SUM(CASE WHEN LOWER(TRIM(IFNULL(pg.anulado,''))) IN ('si','anulado','activo') THEN 1 ELSE 0 END) AS es_anulada
        FROM pago pg
        WHERE pg.Fecha>=? AND pg.Fecha<=?".$condicionPagoActivo."
        GROUP BY pg.cod_venta_fk,clave_evento
      ) ev
      INNER JOIN venta v ON v.cod_venta=ev.cod_venta_fk
      INNER JOIN cliente c ON c.cod_cliente=v.cod_clienteFK
      INNER JOIN persona p ON p.cod_persona=c.cod_cliente
      INNER JOIN local l ON l.cod_local=v.cod_local
      LEFT JOIN usuario uc ON uc.cod_usuario=ev.cod_cobradorFK
      LEFT JOIN persona pc ON pc.cod_persona=uc.cod_usuario
      WHERE UPPER(TRIM(IFNULL(v.TipoVenta,'')))='CREDITO'".$condicionLocal.$condicionActiva;
    $stmt = $mysqli->prepare($sqlCuotas);
    $parametros = $parametrosBase;
    if ($stmt) {
        centroFacturaBind($stmt, $tipos, $parametros);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) $registros[] = centroFacturaFilaUtf8($fila);
        }
        $stmt->close();
    }
    $mysqli->close();

    $base = array();
    foreach ($registros as $fila) {
        if (centroFacturaCoincideFiltrosEmitidas($fila, $filtros, true)) $base[] = $fila;
    }
    $metricas = centroFacturaMetricasEmitidas($base);
    $filtrados = array();
    foreach ($base as $fila) {
        if (centroFacturaCoincideFiltrosEmitidas($fila, $filtros, false)) $filtrados[] = $fila;
    }
    usort($filtrados, function ($a, $b) {
        if ($a['fecha_evento'] === $b['fecha_evento']) {
            if (intval($a['cod_venta']) === intval($b['cod_venta'])) return intval($b['id_pago']) - intval($a['id_pago']);
            return intval($b['cod_venta']) - intval($a['cod_venta']);
        }
        return strcmp($b['fecha_evento'], $a['fecha_evento']);
    });
    $total = count($filtrados);
    return array('ok' => true, 'registros' => array_slice($filtrados, $offset, $limite), 'total' => $total,
        'limite' => $limite, 'offset' => $offset, 'metricas' => $metricas);
}

function centroFacturaObtenerLoteRaw($mysqli, $idLote, $bloquear = false)
{
    $idLote = intval($idLote);
    $sql = 'SELECT * FROM centro_factura_lote WHERE id_lote=? LIMIT 1'.($bloquear ? ' FOR UPDATE' : '');
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $idLote);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : array();
}

function centroFacturaListarLotes($codUsuario, $filtros, $limite = 80, $offset = 0)
{
    if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar los lotes.');
    }
    $limite = max(1, min(150, intval($limite)));
    $offset = max(0, intval($offset));
    $mysqli = conectar_al_servidor();
    $contexto = centroFacturaContextoUsuario($codUsuario, $mysqli);
    $condiciones = array('1=1');
    $tipos = '';
    $parametros = array();
    if (!centroFacturaPuedeVerTodosLocales($codUsuario)) {
        $condiciones[] = 'lo.cod_local_origenFK=?';
        $tipos .= 'i';
        $parametros[] = intval($contexto['cod_localFK']);
    } elseif (!empty($filtros['cod_local'])) {
        $condiciones[] = 'lo.cod_local_origenFK=?';
        $tipos .= 'i';
        $parametros[] = intval($filtros['cod_local']);
    }
    if (!empty($filtros['estado'])) {
        $estado = centroFacturaTextoBaseDatos($filtros['estado'], 30);
        if (in_array($estado, array('borrador','enviado','recibido_parcial','recibido','observado','anulado'), true)) {
            $condiciones[] = 'lo.estado=?';
            $tipos .= 's';
            $parametros[] = $estado;
        }
    }
    if (!empty($filtros['busqueda'])) {
        $patron = '%'.centroFacturaTextoBaseDatos($filtros['busqueda'], 100).'%';
        $condiciones[] = '(lo.codigo_lote LIKE ? OR lo.destino LIKE ? OR lo.observaciones LIKE ?)';
        $tipos .= 'sss';
        $parametros[] = $patron;
        $parametros[] = $patron;
        $parametros[] = $patron;
    }
    $base = "SELECT lo.*,l.Nombre AS nombre_local,pc.nombre_persona AS usuario_creador,
        pe.nombre_persona AS usuario_entrega,pr.nombre_persona AS usuario_recepcion,
        SUM(CASE WHEN ld.estado<>'retirada' THEN 1 ELSE 0 END) AS cantidad_facturas,
        SUM(CASE WHEN ld.estado='recibida' THEN 1 ELSE 0 END) AS cantidad_recibidas,
        SUM(CASE WHEN ld.estado IN ('faltante','observada') THEN 1 ELSE 0 END) AS cantidad_observadas,
        IFNULL(SUM(CASE WHEN ld.estado<>'retirada' THEN cf.importe_total ELSE 0 END),0) AS importe_total
      FROM centro_factura_lote lo
      INNER JOIN local l ON l.cod_local=lo.cod_local_origenFK
      LEFT JOIN persona pc ON pc.cod_persona=lo.cod_usuarioFK_create
      LEFT JOIN persona pe ON pe.cod_persona=lo.cod_usuario_entregaFK
      LEFT JOIN persona pr ON pr.cod_persona=lo.cod_usuario_recepcionFK
      LEFT JOIN centro_factura_lote_detalle ld ON ld.id_loteFK=lo.id_lote
      LEFT JOIN centro_factura cf ON cf.id_factura=ld.id_facturaFK
      WHERE ".implode(' AND ', $condiciones)." GROUP BY lo.id_lote";
    $stmt = $mysqli->prepare($base.' ORDER BY lo.fecha_creacion DESC,lo.id_lote DESC LIMIT '.$limite.' OFFSET '.$offset);
    centroFacturaBind($stmt, $tipos, $parametros);
    $stmt->execute();
    $registros = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $registros[] = centroFacturaFilaUtf8($fila);
    }
    $stmt->close();
    $parametrosConteo = $parametros;
    $stmt = $mysqli->prepare('SELECT COUNT(*) total FROM ('.$base.') lotes');
    centroFacturaBind($stmt, $tipos, $parametrosConteo);
    $stmt->execute();
    $total = intval($stmt->get_result()->fetch_assoc()['total']);
    $stmt->close();
    $mysqli->close();
    return array('ok' => true, 'registros' => $registros, 'total' => $total, 'limite' => $limite, 'offset' => $offset);
}

function centroFacturaValidarFacturaParaLote($mysqli, $idFactura, $codLocal, $idLoteExcluir = 0, $bloquear = true)
{
    $factura = centroFacturaObtenerRaw($mysqli, $idFactura, $bloquear);
    if (!$factura || intval($factura['cod_localFK']) !== intval($codLocal)) {
        return array('ok' => false, 'mensaje' => 'Una de las facturas no pertenece al local del lote.');
    }
    if ($factura['direccion'] !== 'entrante' || $factura['estado_registro'] !== 'activo'
        || in_array($factura['estado_validacion'], array('rechazada','anulada'), true)) {
        return array('ok' => false, 'mensaje' => 'Una de las facturas ya no esta activa.');
    }
    if (isset($factura['tipo_documento']) && $factura['tipo_documento'] !== 'factura') {
        return array('ok' => false, 'mensaje' => 'Los lotes de originales admiten facturas, no recibos.');
    }
    if (in_array($factura['estado_original'], array('recibido','no_requiere_original'), true)) {
        return array('ok' => false, 'mensaje' => 'Una de las facturas ya fue recibida o no requiere original.');
    }
    $idLoteExcluir = intval($idLoteExcluir);
    $stmt = $mysqli->prepare("SELECT lo.codigo_lote FROM centro_factura_lote_detalle ld
        INNER JOIN centro_factura_lote lo ON lo.id_lote=ld.id_loteFK
        WHERE ld.id_facturaFK=? AND ld.estado<>'retirada' AND lo.estado<>'anulado' AND lo.id_lote<>? LIMIT 1");
    $stmt->bind_param('ii', $idFactura, $idLoteExcluir);
    $stmt->execute();
    $ocupada = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($ocupada) {
        return array('ok' => false, 'mensaje' => 'Una de las facturas ya pertenece al lote '.centroFacturaValorUtf8($ocupada['codigo_lote']).'.');
    }
    return array('ok' => true, 'factura' => $factura);
}

function centroFacturaCrearLote($codLocal, $facturas, $datos, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'GESTIONARLOTESFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para crear lotes.');
    }
    $codLocal = intval($codLocal);
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$facturas))));
    if (count($ids) < 1 || count($ids) > 200) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'Seleccione entre 1 y 200 facturas para el lote.');
    }
    $mysqli = conectar_al_servidor();
    if (!centroFacturaPuedeUsarLocal($codUsuario, $codLocal, $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'No puede crear lotes para este local.');
    }
    $destino = isset($datos['destino']) ? centroFacturaTextoBaseDatos($datos['destino'], 150) : 'Administracion central';
    $observaciones = isset($datos['observaciones']) ? centroFacturaTextoBaseDatos($datos['observaciones'], 3000, true) : '';
    $responsable = !empty($datos['cod_usuario_entrega']) ? intval($datos['cod_usuario_entrega']) : intval($codUsuario);
    if ($destino === '') {
        $destino = 'Administracion central';
    }
    $mysqli->begin_transaction();
    try {
        foreach ($ids as $idFactura) {
            $validacion = centroFacturaValidarFacturaParaLote($mysqli, $idFactura, $codLocal, 0, true);
            if (empty($validacion['ok'])) {
                throw new Exception($validacion['mensaje']);
            }
        }
        $temporal = 'TMP-'.date('YmdHis').'-'.mt_rand(1000, 9999);
        $stmt = $mysqli->prepare("INSERT INTO centro_factura_lote
            (codigo_lote,cod_local_origenFK,estado,destino,observaciones,cod_usuario_entregaFK,cod_usuarioFK_create)
            VALUES (?,?,'borrador',?,?,?,?)");
        $stmt->bind_param('sissii', $temporal, $codLocal, $destino, $observaciones, $responsable, $codUsuario);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo crear el lote.');
        }
        $idLote = intval($stmt->insert_id);
        $stmt->close();
        $codigo = 'CF-'.$codLocal.'-'.date('Ymd').'-'.str_pad((string)$idLote, 5, '0', STR_PAD_LEFT);
        $stmt = $mysqli->prepare('UPDATE centro_factura_lote SET codigo_lote=? WHERE id_lote=?');
        $stmt->bind_param('si', $codigo, $idLote);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo generar el codigo del lote.');
        }
        $stmt->close();
        $stmt = $mysqli->prepare("INSERT INTO centro_factura_lote_detalle
            (id_loteFK,id_facturaFK,estado,cod_usuario_estadoFK) VALUES (?,?,'incluida',?)");
        foreach ($ids as $idFactura) {
            $stmt->bind_param('iii', $idLote, $idFactura, $codUsuario);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo agregar una factura al lote.');
            }
        }
        $stmt->close();
        centroFacturaAuditar($mysqli, 'lote', $idLote, null, 'crear_lote', array(), array('codigo_lote' => $codigo, 'facturas' => $ids), $observaciones, $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'codigo_lote' => $codigo);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaObtenerDetalleLote($idLote, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'VERCENTROFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para consultar el lote.');
    }
    $mysqli = conectar_al_servidor();
    $lote = centroFacturaObtenerLoteRaw($mysqli, $idLote, false);
    if (!$lote || !centroFacturaPuedeUsarLocal($codUsuario, $lote['cod_local_origenFK'], $mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'El lote no existe o pertenece a otro local.');
    }
    $stmt = $mysqli->prepare("SELECT lo.*,l.Nombre AS nombre_local,pc.nombre_persona AS usuario_creador,
        pe.nombre_persona AS usuario_entrega,ps.nombre_persona AS usuario_envio,pr.nombre_persona AS usuario_recepcion
      FROM centro_factura_lote lo INNER JOIN local l ON l.cod_local=lo.cod_local_origenFK
      LEFT JOIN persona pc ON pc.cod_persona=lo.cod_usuarioFK_create
      LEFT JOIN persona pe ON pe.cod_persona=lo.cod_usuario_entregaFK
      LEFT JOIN persona ps ON ps.cod_persona=lo.cod_usuario_envioFK
      LEFT JOIN persona pr ON pr.cod_persona=lo.cod_usuario_recepcionFK
      WHERE lo.id_lote=? LIMIT 1");
    $idLote = intval($idLote);
    $stmt->bind_param('i', $idLote);
    $stmt->execute();
    $lote = centroFacturaFilaUtf8($stmt->get_result()->fetch_assoc());
    $stmt->close();
    $facturas = array();
    $base = centroFacturaBaseSelect();
    $stmt = $mysqli->prepare("SELECT ld.id_lote_detalle,ld.estado AS estado_detalle_lote,ld.observacion AS observacion_lote,
        ld.fecha_estado,ld.cod_usuario_estadoFK,q.* FROM centro_factura_lote_detalle ld
        INNER JOIN (".$base.") q ON q.id_factura=ld.id_facturaFK
        WHERE ld.id_loteFK=? ORDER BY ld.id_lote_detalle");
    $stmt->bind_param('i', $idLote);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $facturas[] = centroFacturaDecorarFila(centroFacturaFilaUtf8($fila));
    }
    $stmt->close();
    $auditoria = array();
    if (centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) {
        $stmt = $mysqli->prepare("SELECT a.*,p.nombre_persona AS usuario_nombre FROM centro_factura_auditoria a
            LEFT JOIN persona p ON p.cod_persona=a.cod_usuarioFK
            WHERE a.entidad='lote' AND a.id_entidad=? ORDER BY a.id_auditoria DESC LIMIT 100");
        $stmt->bind_param('i', $idLote);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $auditoria[] = centroFacturaFilaUtf8($fila);
        }
        $stmt->close();
    }
    $mysqli->close();
    return array('ok' => true, 'lote' => $lote, 'facturas' => $facturas, 'auditoria' => $auditoria);
}

function centroFacturaAgregarFacturaLote($idLote, $idFactura, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'GESTIONARLOTESFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para modificar lotes.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $lote = centroFacturaObtenerLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] !== 'borrador' || !centroFacturaPuedeUsarLocal($codUsuario, $lote['cod_local_origenFK'], $mysqli)) {
            throw new Exception('Solo puede agregarse facturas a un lote borrador autorizado.');
        }
        $validacion = centroFacturaValidarFacturaParaLote($mysqli, intval($idFactura), $lote['cod_local_origenFK'], intval($idLote), true);
        if (empty($validacion['ok'])) {
            throw new Exception($validacion['mensaje']);
        }
        $stmt = $mysqli->prepare("INSERT INTO centro_factura_lote_detalle
            (id_loteFK,id_facturaFK,estado,cod_usuario_estadoFK) VALUES (?,?,'incluida',?)
            ON DUPLICATE KEY UPDATE estado='incluida',observacion=NULL,fecha_estado=NOW(),cod_usuario_estadoFK=VALUES(cod_usuario_estadoFK)");
        $idLote = intval($idLote);
        $idFactura = intval($idFactura);
        $stmt->bind_param('iii', $idLote, $idFactura, $codUsuario);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo agregar la factura.');
        }
        $stmt->close();
        centroFacturaAuditar($mysqli, 'lote', $idLote, $idFactura, 'agregar_factura_lote', array(), array('id_factura' => $idFactura), '', $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaRetirarFacturaLote($idLote, $idFactura, $motivo, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'GESTIONARLOTESFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para modificar lotes.');
    }
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $lote = centroFacturaObtenerLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] !== 'borrador' || !centroFacturaPuedeUsarLocal($codUsuario, $lote['cod_local_origenFK'], $mysqli)) {
            throw new Exception('Solo puede retirarse facturas de un lote borrador autorizado.');
        }
        $stmt = $mysqli->prepare("UPDATE centro_factura_lote_detalle SET estado='retirada',observacion=?,fecha_estado=NOW(),cod_usuario_estadoFK=?
            WHERE id_loteFK=? AND id_facturaFK=? AND estado<>'retirada'");
        $idLote = intval($idLote);
        $idFactura = intval($idFactura);
        $stmt->bind_param('siii', $motivo, $codUsuario, $idLote, $idFactura);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            throw new Exception('La factura no esta incluida en el lote.');
        }
        $stmt->close();
        centroFacturaAuditar($mysqli, 'lote', $idLote, $idFactura, 'retirar_factura_lote', array('estado' => 'incluida'), array('estado' => 'retirada'), $motivo, $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaEnviarLote($idLote, $codResponsable, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'GESTIONARLOTESFACTURAS')
        || !centroFacturaTienePermiso($codUsuario, 'ENVIARORIGINALFACTURA')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permisos para enviar el lote.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $lote = centroFacturaObtenerLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] !== 'borrador' || !centroFacturaPuedeUsarLocal($codUsuario, $lote['cod_local_origenFK'], $mysqli)) {
            throw new Exception('El lote no esta disponible como borrador.');
        }
        $idLote = intval($idLote);
        $resultado = $mysqli->query("SELECT id_facturaFK FROM centro_factura_lote_detalle WHERE id_loteFK=".$idLote." AND estado='incluida' FOR UPDATE");
        $ids = array();
        while ($fila = $resultado->fetch_assoc()) {
            $ids[] = intval($fila['id_facturaFK']);
        }
        if (count($ids) < 1) {
            throw new Exception('El lote no tiene facturas activas para enviar.');
        }
        $ahora = date('Y-m-d H:i:s');
        $responsable = intval($codResponsable) > 0 ? intval($codResponsable) : intval($codUsuario);
        $stmt = $mysqli->prepare("UPDATE centro_factura_lote SET estado='enviado',cod_usuario_entregaFK=?,fecha_envio=?,
            cod_usuario_envioFK=?,cod_usuarioFK_update=?,fecha_actualizacion=? WHERE id_lote=?");
        $stmt->bind_param('isiisi', $responsable, $ahora, $codUsuario, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo marcar el lote como enviado.');
        }
        $stmt->close();
        $stmt = $mysqli->prepare("UPDATE centro_factura_lote_detalle SET estado='enviada',fecha_estado=?,cod_usuario_estadoFK=?
            WHERE id_loteFK=? AND estado='incluida'");
        $stmt->bind_param('sii', $ahora, $codUsuario, $idLote);
        $stmt->execute();
        $stmt->close();
        $stmt = $mysqli->prepare("UPDATE centro_factura SET estado_original='enviado_central',fecha_envio_central=?,
            cod_responsable_envioFK=?,cod_usuario_actualizacionFK=?,fecha_actualizacion=?,version_registro=version_registro+1
            WHERE id_factura=? AND estado_registro='activo' AND estado_original NOT IN ('recibido','no_requiere_original')");
        foreach ($ids as $idFactura) {
            $stmt->bind_param('siisi', $ahora, $responsable, $codUsuario, $ahora, $idFactura);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo actualizar una factura del lote.');
            }
            centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'enviar_original_por_lote', array(), array('estado_original' => 'enviado_central', 'id_lote' => $idLote), '', $codUsuario);
        }
        $stmt->close();
        centroFacturaAuditar($mysqli, 'lote', $idLote, null, 'enviar_lote', array('estado' => 'borrador'), array('estado' => 'enviado', 'facturas' => $ids), '', $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'cantidad' => count($ids));
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaRecibirLote($idLote, $recepciones, $datos, $codUsuario)
{
    if (!centroFacturaTienePermiso($codUsuario, 'RECIBIRORIGINALFACTURA')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para recibir lotes.');
    }
    $mapa = array();
    foreach ((array)$recepciones as $item) {
        if (!is_array($item)) {
            continue;
        }
        $idFactura = isset($item['id_factura']) ? intval($item['id_factura']) : 0;
        $estado = isset($item['estado']) ? centroFacturaTextoBaseDatos($item['estado'], 20) : '';
        if ($idFactura > 0 && in_array($estado, array('recibida','faltante','observada'), true)) {
            $mapa[$idFactura] = array('estado' => $estado, 'observacion' => isset($item['observacion']) ? centroFacturaTextoBaseDatos($item['observacion'], 255) : '');
        }
    }
    if (count($mapa) < 1) {
        return array('ok' => false, 'codigo' => 'datos', 'mensaje' => 'Indique el resultado de recepcion de las facturas.');
    }
    $ubicacion = array();
    foreach (array('lote_archivo','carpeta_archivo','caja_archivo','periodo_archivo','ubicacion_fisica') as $campo) {
        $limite = $campo === 'ubicacion_fisica' ? 255 : 100;
        $ubicacion[$campo] = isset($datos[$campo]) ? centroFacturaTextoBaseDatos($datos[$campo], $limite) : '';
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $lote = centroFacturaObtenerLoteRaw($mysqli, $idLote, true);
        if (!$lote || !in_array($lote['estado'], array('enviado','recibido_parcial','observado'), true)
            || !centroFacturaPuedeUsarLocal($codUsuario, $lote['cod_local_origenFK'], $mysqli)) {
            throw new Exception('El lote no esta disponible para recepcion.');
        }
        $idLote = intval($idLote);
        $stmt = $mysqli->prepare("SELECT id_facturaFK FROM centro_factura_lote_detalle
            WHERE id_loteFK=? AND estado<>'retirada' FOR UPDATE");
        $stmt->bind_param('i', $idLote);
        $stmt->execute();
        $idsLote = array();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $idsLote[intval($fila['id_facturaFK'])] = true;
        }
        $stmt->close();
        foreach ($mapa as $idFactura => $recepcion) {
            if (!isset($idsLote[$idFactura])) {
                throw new Exception('Una factura indicada no pertenece al lote.');
            }
            if ($recepcion['estado'] !== 'recibida' && $recepcion['observacion'] === '') {
                throw new Exception('Describa la observacion de cada factura faltante u observada.');
            }
        }
        $ahora = date('Y-m-d H:i:s');
        foreach ($mapa as $idFactura => $recepcion) {
            $stmt = $mysqli->prepare("UPDATE centro_factura_lote_detalle SET estado=?,observacion=?,fecha_estado=?,cod_usuario_estadoFK=?
                WHERE id_loteFK=? AND id_facturaFK=?");
            $stmt->bind_param('sssiii', $recepcion['estado'], $recepcion['observacion'], $ahora, $codUsuario, $idLote, $idFactura);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar el resultado de la recepcion.');
            }
            $stmt->close();
            $factura = centroFacturaObtenerRaw($mysqli, $idFactura, true);
            $campos = array(
                'cod_usuario_actualizacionFK' => array('i', $codUsuario),
                'fecha_actualizacion' => array('s', $ahora),
                'version_registro' => array('i', intval($factura['version_registro']) + 1)
            );
            foreach ($ubicacion as $campo => $valor) {
                $campos[$campo] = array('s', $valor);
            }
            if ($recepcion['estado'] === 'recibida') {
                $campos['estado_original'] = array('s', 'recibido');
                $campos['fecha_recepcion_fisica'] = array('s', $ahora);
                $campos['cod_usuario_recepcionFK'] = array('i', $codUsuario);
                $campos['motivo_observacion'] = array('s', null);
                $campos['comentario_observacion'] = array('s', null);
                $campos['cod_usuario_observacionFK'] = array('i', null);
                $campos['fecha_observacion'] = array('s', null);
            } else {
                $campos['estado_original'] = array('s', 'observado');
                $campos['fecha_recepcion_fisica'] = array('s', $factura['fecha_recepcion_fisica'] ? $factura['fecha_recepcion_fisica'] : $ahora);
                $campos['cod_usuario_recepcionFK'] = array('i', $codUsuario);
                $campos['motivo_observacion'] = array('s', $recepcion['estado'] === 'faltante' ? 'documento_incompleto' : 'otro');
                $campos['comentario_observacion'] = array('s', $recepcion['observacion']);
                $campos['cod_responsable_observacionFK'] = array('i', $factura['cod_responsable_envioFK']);
                $campos['cod_usuario_observacionFK'] = array('i', $codUsuario);
                $campos['fecha_observacion'] = array('s', $ahora);
            }
            $actualizacion = centroFacturaActualizarColumnas($mysqli, 'centro_factura', $campos, 'id_factura=?', 'i', array($idFactura));
            if (empty($actualizacion['ok'])) {
                throw new Exception('No se pudo actualizar una factura recibida.');
            }
            centroFacturaAuditar($mysqli, 'factura', $idFactura, $idFactura, 'recibir_original_por_lote', array('estado_original' => $factura['estado_original']), array('resultado_lote' => $recepcion['estado'], 'id_lote' => $idLote), $recepcion['observacion'], $codUsuario);
        }
        $resultado = $mysqli->query("SELECT
            SUM(estado='recibida') recibidas,
            SUM(estado IN ('faltante','observada')) observadas,
            SUM(estado IN ('incluida','enviada')) pendientes
          FROM centro_factura_lote_detalle WHERE id_loteFK=".$idLote." AND estado<>'retirada'");
        $conteo = $resultado->fetch_assoc();
        if (intval($conteo['pendientes']) === 0 && intval($conteo['observadas']) === 0) {
            $estadoLote = 'recibido';
        } elseif (intval($conteo['recibidas']) > 0) {
            $estadoLote = 'recibido_parcial';
        } else {
            $estadoLote = 'observado';
        }
        $stmt = $mysqli->prepare("UPDATE centro_factura_lote SET estado=?,fecha_recepcion=?,cod_usuario_recepcionFK=?,
            cod_usuarioFK_update=?,fecha_actualizacion=? WHERE id_lote=?");
        $stmt->bind_param('ssiisi', $estadoLote, $ahora, $codUsuario, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo actualizar el estado del lote.');
        }
        $stmt->close();
        centroFacturaAuditar($mysqli, 'lote', $idLote, null, 'recibir_lote', array('estado' => $lote['estado']), array('estado' => $estadoLote, 'resultados' => $mapa), '', $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote, 'estado' => $estadoLote);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote', 'mensaje' => $e->getMessage());
    }
}

function centroFacturaAnularLote($idLote, $motivo, $codUsuario)
{
    $motivo = centroFacturaTextoBaseDatos($motivo, 255);
    if (!centroFacturaTienePermiso($codUsuario, 'GESTIONARLOTESFACTURAS')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para anular lotes.');
    }
    if ($motivo === '') {
        return array('ok' => false, 'codigo' => 'motivo', 'mensaje' => 'Ingrese el motivo de anulacion.');
    }
    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $lote = centroFacturaObtenerLoteRaw($mysqli, $idLote, true);
        if (!$lote || $lote['estado'] === 'anulado' || !centroFacturaPuedeUsarLocal($codUsuario, $lote['cod_local_origenFK'], $mysqli)) {
            throw new Exception('El lote no esta disponible.');
        }
        if ($lote['estado'] !== 'borrador' && !centroFacturaTienePermiso($codUsuario, 'ADMINCENTROFACTURAS')) {
            throw new Exception('Solo un administrador puede anular un lote que ya fue enviado.');
        }
        $idLote = intval($idLote);
        $ahora = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("UPDATE centro_factura_lote SET estado='anulado',motivo_anulacion=?,cod_usuario_anulacionFK=?,
            fecha_anulacion=?,cod_usuarioFK_update=?,fecha_actualizacion=? WHERE id_lote=?");
        $stmt->bind_param('sisisi', $motivo, $codUsuario, $ahora, $codUsuario, $ahora, $idLote);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo anular el lote.');
        }
        $stmt->close();
        centroFacturaAuditar($mysqli, 'lote', $idLote, null, 'anular_lote', array('estado' => $lote['estado']), array('estado' => 'anulado'), $motivo, $codUsuario);
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_lote' => $idLote);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'codigo' => 'lote', 'mensaje' => $e->getMessage());
    }
}

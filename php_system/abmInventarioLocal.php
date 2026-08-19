<?php

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Asuncion');

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/buscar_nivel.php';
require_once __DIR__.'/subir_foto_base64.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, private');

function inventarioLocalUtf8($valor)
{
    if (is_array($valor)) {
        $salida = array();
        foreach ($valor as $clave => $item) {
            $salida[$clave] = inventarioLocalUtf8($item);
        }
        return $salida;
    }
    if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
        return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
    }
    return $valor;
}

function inventarioLocalResponder($estado, $datos = array(), $http = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code((int)$http);
    }
    $respuesta = array('1' => $estado);
    foreach ($datos as $clave => $valor) {
        $respuesta[$clave] = $valor;
    }
    echo json_encode(inventarioLocalUtf8($respuesta), JSON_UNESCAPED_UNICODE);
    exit;
}

function responderErrorInventarioLocal($mensaje, $codigo = 'error', $http = 400)
{
    inventarioLocalResponder($codigo, array('mensaje' => $mensaje, '2' => $mensaje), $http);
}

function normalizarEnumInventarioLocal($valor, $permitidos)
{
    $valor = strtolower(trim((string)$valor));
    return in_array($valor, $permitidos, true) ? $valor : false;
}

function inventarioLocalTextoEntrada($nombre, $maximo = 255)
{
    $valor = isset($_POST[$nombre]) ? $_POST[$nombre] : '';
    if (is_array($valor) || is_object($valor)) {
        return '';
    }
    $valor = trim((string)$valor);
    if (mb_strlen($valor, 'UTF-8') > $maximo) {
        $valor = mb_substr($valor, 0, $maximo, 'UTF-8');
    }
    return mb_convert_encoding($valor, 'ISO-8859-1', 'UTF-8');
}

function inventarioLocalEnteroEntrada($nombre, $predeterminado = 0)
{
    if (!isset($_POST[$nombre]) || $_POST[$nombre] === '') {
        return (int)$predeterminado;
    }
    return is_numeric($_POST[$nombre]) ? (int)$_POST[$nombre] : (int)$predeterminado;
}

function inventarioLocalMontoEntrada($nombre)
{
    $valor = isset($_POST[$nombre]) ? trim((string)$_POST[$nombre]) : '';
    if ($valor === '') {
        return 0;
    }
    $valor = str_replace(array('.', ',', ' ', 'Gs', 'GS'), '', $valor);
    return ctype_digit($valor) ? (int)$valor : -1;
}

function inventarioLocalFechaEntrada($nombre, $permiteVacio = true)
{
    $valor = isset($_POST[$nombre]) ? trim((string)$_POST[$nombre]) : '';
    if ($valor === '') {
        return $permiteVacio ? null : false;
    }
    $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
    $errores = DateTime::getLastErrors();
    if ($fecha === false || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
        return false;
    }
    return $fecha->format('Y-m-d') === $valor ? $valor : false;
}

function inventarioLocalTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $clave = spl_object_hash($mysqli).'|'.$tabla;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    $stmt = $mysqli->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $tabla);
    $total = 0;
    if ($stmt->execute()) {
        $stmt->bind_result($total);
        $stmt->fetch();
    }
    $stmt->close();
    $cache[$clave] = (int)$total > 0;
    return $cache[$clave];
}

function inventarioLocalColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $clave = spl_object_hash($mysqli).'|'.$tabla.'|'.$columna;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    $stmt = $mysqli->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    $total = 0;
    if ($stmt->execute()) {
        $stmt->bind_result($total);
        $stmt->fetch();
    }
    $stmt->close();
    $cache[$clave] = (int)$total > 0;
    return $cache[$clave];
}

function inventarioLocalEstructuraControlDisponible($mysqli)
{
    $columnas = array(
        'cod_sectorFK', 'tipo_control', 'costo_tipo', 'fecha_adquisicion',
        'depreciacion_acumulada', 'fecha_depreciacion',
        'cod_usuarioFK_depreciacion', 'fecha_actualizacion_depreciacion',
        'frecuencia_verificacion'
    );
    foreach ($columnas as $columna) {
        if (!inventarioLocalColumnaExiste($mysqli, 'insumos_local', $columna)) {
            return false;
        }
    }
    return inventarioLocalTablaExiste($mysqli, 'inventario_local_sector')
        && inventarioLocalTablaExiste($mysqli, 'inventario_local_verificacion')
        && inventarioLocalTablaExiste($mysqli, 'inventario_local_depreciacion_historial');
}

function inventarioLocalTienePermiso($usuario, $codigos)
{
    if (!is_array($codigos)) {
        $codigos = array($codigos);
    }
    foreach ($codigos as $codigo) {
        if (controldeaccesoacasas($usuario, $codigo, " u.accion='SI' ") == 1) {
            return true;
        }
    }
    return false;
}

function inventarioLocalRequerirPermiso($usuario, $codigos)
{
    if (!inventarioLocalTienePermiso($usuario, $codigos)) {
        responderErrorInventarioLocal('No tiene permiso para realizar esta accion.', 'NI', 403);
    }
}

function inventarioLocalBind($stmt, $tipos, &$parametros)
{
    if ($tipos === '' || count($parametros) === 0) {
        return true;
    }
    $argumentos = array($tipos);
    foreach ($parametros as $indice => $valor) {
        $argumentos[] = &$parametros[$indice];
    }
    return call_user_func_array(array($stmt, 'bind_param'), $argumentos);
}

function inventarioLocalUsuarioSesion()
{
    $user = isset($_POST['useru']) ? $_POST['useru'] : '';
    $pass = isset($_POST['passu']) ? str_replace('=', '+', (string)$_POST['passu']) : '';
    $navegador = isset($_POST['navegador']) ? $_POST['navegador'] : '';
    $userIso = mb_convert_encoding((string)$user, 'ISO-8859-1', 'UTF-8');
    $navegadorIso = mb_convert_encoding((string)$navegador, 'ISO-8859-1', 'UTF-8');
    if ($userIso === '' || verificar_navegador($userIso, $navegadorIso, $pass) !== 'ok') {
        inventarioLocalResponder('UI', array('mensaje' => 'La sesion ya no es valida.'), 401);
    }
    return (int)$userIso;
}

function inventarioLocalSelectBase($mysqli)
{
    $estructura = inventarioLocalEstructuraControlDisponible($mysqli);
    $camposNuevos = $estructura
        ? "il.cod_sectorFK,il.tipo_control,il.costo_tipo,il.fecha_adquisicion,il.depreciacion_acumulada,
            il.fecha_depreciacion,il.cod_usuarioFK_depreciacion,il.fecha_actualizacion_depreciacion,
            il.frecuencia_verificacion,IFNULL(sec.nombre,'') AS nombre_sector,
            IFNULL(pd.nombre_persona,'') AS nombre_usuario_depreciacion,
            IFNULL(ud.url,'') AS avatar_usuario_depreciacion"
        : "NULL AS cod_sectorFK,'pendiente' AS tipo_control,'pendiente' AS costo_tipo,NULL AS fecha_adquisicion,
            0 AS depreciacion_acumulada,NULL AS fecha_depreciacion,NULL AS cod_usuarioFK_depreciacion,
            NULL AS fecha_actualizacion_depreciacion,'semestral' AS frecuencia_verificacion,
            '' AS nombre_sector,'' AS nombre_usuario_depreciacion,'' AS avatar_usuario_depreciacion";

    $camposVerificacion = $estructura
        ? "iv.id AS id_ultima_verificacion,iv.fecha_verificacion AS ultima_verificacion,
            iv.cantidad_esperada AS ultima_cantidad_esperada,
            iv.cantidad_encontrada AS ultima_cantidad_encontrada,
            iv.estado_fisico AS ultimo_estado_fisico,
            iv.proxima_verificacion,
            IFNULL(pv.nombre_persona,'') AS nombre_usuario_verificador,
            IFNULL(uv.url,'') AS avatar_usuario_verificador"
        : "NULL AS id_ultima_verificacion,NULL AS ultima_verificacion,
            NULL AS ultima_cantidad_esperada,NULL AS ultima_cantidad_encontrada,
            NULL AS ultimo_estado_fisico,NULL AS proxima_verificacion,
            '' AS nombre_usuario_verificador,'' AS avatar_usuario_verificador";

    $joinsNuevos = $estructura
        ? "LEFT JOIN inventario_local_sector sec ON sec.id=il.cod_sectorFK
           LEFT JOIN usuario ud ON ud.cod_usuario=il.cod_usuarioFK_depreciacion
           LEFT JOIN persona pd ON pd.cod_persona=ud.cod_usuario
           LEFT JOIN inventario_local_verificacion iv ON iv.id=(
               SELECT iv2.id FROM inventario_local_verificacion iv2
               WHERE iv2.cod_insumoFK=il.cod_insumo
               ORDER BY iv2.fecha_verificacion DESC,iv2.id DESC LIMIT 1
           )
           LEFT JOIN usuario uv ON uv.cod_usuario=iv.cod_usuarioFK_verificador
           LEFT JOIN persona pv ON pv.cod_persona=uv.cod_usuario"
        : '';

    return "SELECT
        il.cod_insumo,il.descripcion,il.nombre,il.estado,il.fecha_creacion,il.cantidad,il.costo,
        il.observacion,il.fecha_edit,il.cod_localFK,il.cod_usuarioFK_edit,
        il.url1,il.url2,il.url3,il.cod_usuario_responsableFK,il.cod_usuarioFK_create,
        il.modelo,il.nro_serie,il.cod_marcaFK,il.url_factura,il.url_compromiso,
        il.estado_fisico,il.categoria,
        IFNULL(l.Nombre,'') AS nombreLocal,IFNULL(m.descripcion,'') AS nombre_marca,
        IFNULL(pr.nombre_persona,'') AS nombre_usuario_responsable,IFNULL(ur.url,'') AS avatar_usuario_responsable,
        IFNULL(ur.rut_usuario,'') AS ci_usuario_responsable,IFNULL(pr.telefono,'') AS tel_usuario_responsable,
        IFNULL(pc.nombre_persona,'') AS nombre_usuarioFK_create,IFNULL(uc.url,'') AS avatar_usuarioFK_create,
        IFNULL(pe.nombre_persona,'') AS nombre_usuarioFK_edit,IFNULL(ue.url,'') AS avatar_usuarioFK_edit,
        ".$camposNuevos.",".$camposVerificacion."
        FROM insumos_local il
        LEFT JOIN local l ON l.cod_local=il.cod_localFK
        LEFT JOIN marcas m ON m.cod_marcas=il.cod_marcaFK
        LEFT JOIN usuario ur ON ur.cod_usuario=il.cod_usuario_responsableFK
        LEFT JOIN persona pr ON pr.cod_persona=ur.cod_usuario
        LEFT JOIN usuario uc ON uc.cod_usuario=il.cod_usuarioFK_create
        LEFT JOIN persona pc ON pc.cod_persona=uc.cod_usuario
        LEFT JOIN usuario ue ON ue.cod_usuario=il.cod_usuarioFK_edit
        LEFT JOIN persona pe ON pe.cod_persona=ue.cod_usuario
        ".$joinsNuevos;
}

function inventarioLocalFiltrosEntrada($mysqli)
{
    $filtros = array(
        'busqueda' => inventarioLocalTextoEntrada('busqueda', 120),
        'cod_local' => inventarioLocalEnteroEntrada('cod_localFK'),
        'cod_sector' => inventarioLocalEnteroEntrada('cod_sectorFK'),
        'cod_responsable' => inventarioLocalEnteroEntrada('cod_usuario_responsableFK'),
        'estado' => inventarioLocalTextoEntrada('estado', 20),
        'estado_fisico' => inventarioLocalTextoEntrada('estado_fisico', 30),
        'categoria' => inventarioLocalTextoEntrada('categoria', 30),
        'costo_tipo' => inventarioLocalTextoEntrada('costo_tipo', 20),
        'ocultar_inactivo' => isset($_POST['ocultar_inactivo']) && $_POST['ocultar_inactivo'] !== ''
    );
    if (!inventarioLocalEstructuraControlDisponible($mysqli)) {
        $filtros['cod_sector'] = 0;
        $filtros['costo_tipo'] = '';
    }
    return $filtros;
}

function inventarioLocalWhere($mysqli, $filtros, &$tipos, &$parametros)
{
    $condiciones = array();
    $tipos = '';
    $parametros = array();
    if ($filtros['busqueda'] !== '') {
        $buscar = '%'.$filtros['busqueda'].'%';
        $condiciones[] = "(CAST(il.cod_insumo AS CHAR) LIKE ? OR il.nombre LIKE ? OR IFNULL(il.descripcion,'') LIKE ?
            OR IFNULL(il.modelo,'') LIKE ? OR IFNULL(il.nro_serie,'') LIKE ? OR IFNULL(m.descripcion,'') LIKE ?)";
        for ($i = 0; $i < 6; $i++) {
            $tipos .= 's';
            $parametros[] = $buscar;
        }
    }
    if ($filtros['cod_local'] > 0) {
        $condiciones[] = 'il.cod_localFK=?';
        $tipos .= 'i';
        $parametros[] = $filtros['cod_local'];
    }
    if ($filtros['cod_sector'] > 0 && inventarioLocalEstructuraControlDisponible($mysqli)) {
        $condiciones[] = 'il.cod_sectorFK=?';
        $tipos .= 'i';
        $parametros[] = $filtros['cod_sector'];
    }
    if ($filtros['cod_responsable'] > 0) {
        $condiciones[] = 'il.cod_usuario_responsableFK=?';
        $tipos .= 'i';
        $parametros[] = $filtros['cod_responsable'];
    } elseif ($filtros['cod_responsable'] === -1) {
        $condiciones[] = '(il.cod_usuario_responsableFK IS NULL OR il.cod_usuario_responsableFK=0)';
    }
    if ($filtros['estado'] !== '') {
        $condiciones[] = 'LOWER(il.estado)=?';
        $tipos .= 's';
        $parametros[] = strtolower($filtros['estado']);
    } elseif ($filtros['ocultar_inactivo']) {
        $condiciones[] = "LOWER(IFNULL(il.estado,'activo'))<>'inactivo'";
    }
    if ($filtros['estado_fisico'] !== '') {
        $condiciones[] = 'LOWER(IFNULL(il.estado_fisico,\'\'))=?';
        $tipos .= 's';
        $parametros[] = strtolower($filtros['estado_fisico']);
    }
    if ($filtros['categoria'] !== '') {
        $condiciones[] = 'LOWER(IFNULL(il.categoria,\'\'))=?';
        $tipos .= 's';
        $parametros[] = strtolower($filtros['categoria']);
    }
    if ($filtros['costo_tipo'] !== '' && inventarioLocalEstructuraControlDisponible($mysqli)) {
        $condiciones[] = 'il.costo_tipo=?';
        $tipos .= 's';
        $parametros[] = strtolower($filtros['costo_tipo']);
    }
    return count($condiciones) ? ' WHERE '.implode(' AND ', $condiciones) : '';
}

function inventarioLocalEjecutarFilas($mysqli, $sql, $tipos, $parametros)
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderErrorInventarioLocal('No se pudo preparar la consulta de activos.');
    }
    if (!inventarioLocalBind($stmt, $tipos, $parametros) || !$stmt->execute()) {
        $stmt->close();
        responderErrorInventarioLocal('No se pudo consultar el inventario.');
    }
    $resultado = $stmt->get_result();
    $filas = array();
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    $stmt->close();
    return $filas;
}

function inventarioLocalPrepararFila($fila)
{
    $cantidad = isset($fila['cantidad']) ? max(0, (int)$fila['cantidad']) : 0;
    $costo = isset($fila['costo']) ? max(0, (int)$fila['costo']) : 0;
    $tipoCosto = isset($fila['costo_tipo']) ? $fila['costo_tipo'] : 'pendiente';
    $valorTotal = $tipoCosto === 'unitario' ? $costo * $cantidad : $costo;
    $depreciacion = isset($fila['depreciacion_acumulada']) ? max(0, (int)$fila['depreciacion_acumulada']) : 0;
    $fila['valor_total'] = $valorTotal;
    $fila['valor_contable'] = max(0, $valorTotal - $depreciacion);
    $fila['diferencia_ultima_verificacion'] = $fila['ultima_cantidad_encontrada'] === null
        ? null
        : (int)$fila['ultima_cantidad_encontrada'] - (int)$fila['ultima_cantidad_esperada'];
    $estadoFisicoUtf8 = strtolower((string)inventarioLocalUtf8($fila['estado_fisico']));
    $fila['requiere_atencion'] = in_array($estadoFisicoUtf8, array('mantenimiento', 'dañado'), true)
        || empty($fila['cod_usuario_responsableFK'])
        || empty($fila['ultima_verificacion'])
        || (!empty($fila['proxima_verificacion']) && $fila['proxima_verificacion'] < date('Y-m-d'));
    return inventarioLocalUtf8($fila);
}

function inventarioLocalBuscar($mysqli, $filtros, $pagina, $limite, $sinLimite = false)
{
    $tipos = '';
    $parametros = array();
    $where = inventarioLocalWhere($mysqli, $filtros, $tipos, $parametros);
    $base = inventarioLocalSelectBase($mysqli);
    $sqlResumen = "SELECT COUNT(*) AS registros,
        COALESCE(SUM(COALESCE(x.cantidad,0)),0) AS unidades,
        COALESCE(SUM(CASE WHEN x.costo_tipo='unitario' THEN COALESCE(x.costo,0)*COALESCE(x.cantidad,0) ELSE COALESCE(x.costo,0) END),0) AS valor_total,
        COALESCE(SUM(GREATEST((CASE WHEN x.costo_tipo='unitario' THEN COALESCE(x.costo,0)*COALESCE(x.cantidad,0) ELSE COALESCE(x.costo,0) END)-COALESCE(x.depreciacion_acumulada,0),0)),0) AS valor_contable,
        COALESCE(SUM(CASE WHEN x.costo_tipo='pendiente' THEN 1 ELSE 0 END),0) AS pendientes_validar,
        COALESCE(SUM(CASE WHEN LOWER(IFNULL(x.estado_fisico,''))<>'excelente'
            OR x.cod_usuario_responsableFK IS NULL OR x.cod_usuario_responsableFK=0
            OR x.ultima_verificacion IS NULL
            OR (x.proxima_verificacion IS NOT NULL AND x.proxima_verificacion<CURDATE()) THEN 1 ELSE 0 END),0) AS requieren_atencion,
        MIN(CASE WHEN x.proxima_verificacion>=CURDATE() THEN x.proxima_verificacion ELSE NULL END) AS proxima_verificacion
        FROM (".$base.$where.") x";
    $resumenFilas = inventarioLocalEjecutarFilas($mysqli, $sqlResumen, $tipos, $parametros);
    $resumen = count($resumenFilas) ? $resumenFilas[0] : array();
    $total = isset($resumen['registros']) ? (int)$resumen['registros'] : 0;
    $pagina = max(1, (int)$pagina);
    $limite = in_array((int)$limite, array(10, 25, 50, 100), true) ? (int)$limite : 25;
    $offset = ($pagina - 1) * $limite;
    $sqlFilas = $base.$where.' ORDER BY il.cod_insumo ASC';
    $sqlFilas .= $sinLimite ? ' LIMIT 5000' : ' LIMIT '.$limite.' OFFSET '.$offset;
    $filas = inventarioLocalEjecutarFilas($mysqli, $sqlFilas, $tipos, $parametros);
    $preparadas = array();
    foreach ($filas as $fila) {
        $preparadas[] = inventarioLocalPrepararFila($fila);
    }
    return array(
        'registros' => $preparadas,
        'resumen' => inventarioLocalUtf8($resumen),
        'pagina' => $pagina,
        'limite' => $limite,
        'total_paginas' => $limite > 0 ? max(1, (int)ceil($total / $limite)) : 1,
        'estructura_control' => inventarioLocalEstructuraControlDisponible($mysqli)
    );
}

function inventarioLocalSectorValido($mysqli, $sectorId, $localId)
{
    if ($sectorId <= 0) {
        return true;
    }
    $stmt = $mysqli->prepare("SELECT 1 FROM inventario_local_sector WHERE id=? AND cod_localFK=? AND estado='activo' LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ii', $sectorId, $localId);
    $valido = false;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $valido = $resultado && $resultado->num_rows > 0;
    }
    $stmt->close();
    return $valido;
}

function inventarioLocalGuardar($mysqli, $usuario)
{
    $codigo = inventarioLocalEnteroEntrada('cod_inventario');
    inventarioLocalRequerirPermiso($usuario, $codigo > 0 ? 'EDITARLISTADOINVENTARIOLOCAL' : 'CREARLISTADOINVENTARIOLOCAL');
    $nombre = inventarioLocalTextoEntrada('nombre', 75);
    $descripcion = inventarioLocalTextoEntrada('descripcion', 100);
    $observacion = inventarioLocalTextoEntrada('observacion', 150);
    $modelo = inventarioLocalTextoEntrada('modelo', 100);
    $serie = inventarioLocalTextoEntrada('nro_serie', 100);
    $estado = normalizarEnumInventarioLocal(inventarioLocalTextoEntrada('estado', 20), array('activo', 'inactivo'));
    $categoria = normalizarEnumInventarioLocal(inventarioLocalTextoEntrada('categoria', 30), array('mobiliario', 'medico'));
    $estadoFisico = normalizarEnumInventarioLocal(
        inventarioLocalTextoEntrada('estado_fisico', 30),
        array('excelente', 'mantenimiento', mb_convert_encoding('dañado', 'ISO-8859-1', 'UTF-8'))
    );
    $cantidad = inventarioLocalEnteroEntrada('cantidad', 1);
    $costo = inventarioLocalMontoEntrada('costo');
    $localId = inventarioLocalEnteroEntrada('cod_localFK');
    $marcaId = inventarioLocalEnteroEntrada('cod_marcaFK');
    $responsableId = inventarioLocalEnteroEntrada('cod_usuario_responsableFK');
    if ($nombre === '' || $localId <= 0 || $marcaId <= 0 || $cantidad <= 0 || $costo < 0) {
        responderErrorInventarioLocal('Complete nombre, local, marca, cantidad y costo con valores validos.');
    }
    if ($estado === false || $categoria === false || $estadoFisico === false) {
        responderErrorInventarioLocal('Seleccione estado, categoria y estado fisico validos.');
    }

    $estructura = inventarioLocalEstructuraControlDisponible($mysqli);
    $sectorId = 0;
    $tipoControl = 'pendiente';
    $tipoCosto = 'pendiente';
    $fechaAdquisicion = null;
    $depreciacion = 0;
    $fechaDepreciacion = null;
    $frecuencia = 'semestral';
    if ($estructura) {
        $sectorId = inventarioLocalEnteroEntrada('cod_sectorFK');
        $tipoControl = normalizarEnumInventarioLocal(inventarioLocalTextoEntrada('tipo_control', 20), array('pendiente', 'individual', 'lote'));
        $tipoCosto = normalizarEnumInventarioLocal(inventarioLocalTextoEntrada('costo_tipo', 20), array('pendiente', 'unitario', 'lote'));
        $frecuencia = normalizarEnumInventarioLocal(inventarioLocalTextoEntrada('frecuencia_verificacion', 20), array('mensual', 'semestral'));
        $fechaAdquisicion = inventarioLocalFechaEntrada('fecha_adquisicion');
        $fechaDepreciacion = inventarioLocalFechaEntrada('fecha_depreciacion');
        $depreciacion = inventarioLocalMontoEntrada('depreciacion_acumulada');
        if ($tipoControl === false || $tipoCosto === false || $frecuencia === false
            || $fechaAdquisicion === false || $fechaDepreciacion === false || $depreciacion < 0) {
            responderErrorInventarioLocal('Revise el control, costo, frecuencia y datos de depreciacion.');
        }
        if ($tipoControl === 'individual') {
            $cantidad = 1;
        }
        if ($sectorId <= 0) {
            responderErrorInventarioLocal('Seleccione el sector donde se encuentra el activo.');
        }
        if (!inventarioLocalSectorValido($mysqli, $sectorId, $localId)) {
            responderErrorInventarioLocal('El sector seleccionado no pertenece al local indicado.');
        }
        $valorTotal = $tipoCosto === 'unitario' ? $costo * $cantidad : $costo;
        if ($depreciacion > $valorTotal) {
            responderErrorInventarioLocal('La depreciacion acumulada no puede superar el valor total registrado.');
        }
        if ($depreciacion > 0 && empty($fechaDepreciacion)) {
            responderErrorInventarioLocal('Indique la fecha de la depreciacion manual registrada.');
        }
        if ($depreciacion === 0 && !empty($fechaDepreciacion)) {
            responderErrorInventarioLocal('Quite la fecha de depreciacion o ingrese el importe manual correspondiente.');
        }
    }

    $depreciacionAnterior = 0;
    $fechaDepreciacionAnterior = null;
    if ($codigo > 0 && $estructura) {
        $stmtAnterior = $mysqli->prepare('SELECT depreciacion_acumulada,fecha_depreciacion FROM insumos_local WHERE cod_insumo=? LIMIT 1');
        $stmtAnterior->bind_param('i', $codigo);
        if (!$stmtAnterior->execute()) {
            $stmtAnterior->close();
            responderErrorInventarioLocal('No se pudo verificar el activo antes de editarlo.');
        }
        $filaAnterior = $stmtAnterior->get_result()->fetch_assoc();
        $stmtAnterior->close();
        if (!$filaAnterior) {
            responderErrorInventarioLocal('El activo seleccionado ya no existe.');
        }
        $depreciacionAnterior = (int)$filaAnterior['depreciacion_acumulada'];
        $fechaDepreciacionAnterior = $filaAnterior['fecha_depreciacion'];
    }
    $depreciacionCambio = ($depreciacion !== $depreciacionAnterior || $fechaDepreciacion !== $fechaDepreciacionAnterior) ? 1 : 0;

    $mysqli->begin_transaction();
    try {
        $sectorNullable = $sectorId > 0 ? $sectorId : null;
        $responsableNullable = $responsableId > 0 ? $responsableId : null;
        if ($codigo <= 0) {
            if ($estructura) {
                $sql = "INSERT INTO insumos_local
                    (nombre,descripcion,estado,cantidad,costo,costo_tipo,depreciacion_acumulada,
                     fecha_depreciacion,cod_usuarioFK_depreciacion,fecha_actualizacion_depreciacion,
                     observacion,modelo,nro_serie,cod_localFK,cod_sectorFK,cod_marcaFK,
                     cod_usuario_responsableFK,cod_usuarioFK_create,estado_fisico,
                     frecuencia_verificacion,categoria,tipo_control,fecha_adquisicion)
                    VALUES (?,?,?,?,?,?,?,?,?,IF(? > 0,NOW(),NULL),?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($sql);
                $usuarioDep = $depreciacion > 0 ? $usuario : 0;
                $stmt->bind_param(
                    'sssiisisiiisssiiiiissss',
                    $nombre, $descripcion, $estado, $cantidad, $costo, $tipoCosto, $depreciacion,
                    $fechaDepreciacion, $usuarioDep, $usuarioDep, $observacion, $modelo, $serie,
                    $localId, $sectorNullable, $marcaId, $responsableNullable, $usuario,
                    $estadoFisico, $frecuencia, $categoria, $tipoControl, $fechaAdquisicion
                );
            } else {
                $sql = "INSERT INTO insumos_local
                    (nombre,descripcion,estado,cantidad,costo,observacion,modelo,nro_serie,
                     cod_localFK,cod_marcaFK,cod_usuario_responsableFK,cod_usuarioFK_create,
                     estado_fisico,categoria) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param(
                    'sssiisssiiiiss',
                    $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $modelo,
                    $serie, $localId, $marcaId, $responsableNullable, $usuario, $estadoFisico, $categoria
                );
            }
        } else {
            if ($estructura) {
                $sql = "UPDATE insumos_local SET nombre=?,descripcion=?,estado=?,cantidad=?,costo=?,costo_tipo=?,
                    cod_usuarioFK_depreciacion=IF(?=1,?,cod_usuarioFK_depreciacion),
                    fecha_actualizacion_depreciacion=IF(?=1,NOW(),fecha_actualizacion_depreciacion),
                    depreciacion_acumulada=?,fecha_depreciacion=?,
                    observacion=?,modelo=?,nro_serie=?,cod_localFK=?,cod_sectorFK=?,cod_marcaFK=?,
                    cod_usuario_responsableFK=?,cod_usuarioFK_edit=?,fecha_edit=NOW(),estado_fisico=?,
                    frecuencia_verificacion=?,categoria=?,tipo_control=?,fecha_adquisicion=? WHERE cod_insumo=?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param(
                    'sssiisiiiissssiiiiisssssi',
                    $nombre, $descripcion, $estado, $cantidad, $costo, $tipoCosto,
                    $depreciacionCambio, $usuario, $depreciacionCambio, $depreciacion, $fechaDepreciacion,
                    $observacion, $modelo, $serie, $localId, $sectorNullable, $marcaId,
                    $responsableNullable, $usuario, $estadoFisico, $frecuencia, $categoria,
                    $tipoControl, $fechaAdquisicion, $codigo
                );
            } else {
                $sql = "UPDATE insumos_local SET nombre=?,descripcion=?,estado=?,cantidad=?,costo=?,
                    observacion=?,modelo=?,nro_serie=?,cod_localFK=?,cod_marcaFK=?,
                    cod_usuario_responsableFK=?,cod_usuarioFK_edit=?,fecha_edit=NOW(),
                    estado_fisico=?,categoria=? WHERE cod_insumo=?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param(
                    'sssiisssiiiissi',
                    $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $modelo,
                    $serie, $localId, $marcaId, $responsableNullable, $usuario,
                    $estadoFisico, $categoria, $codigo
                );
            }
        }
        if (!$stmt || !$stmt->execute()) {
            throw new Exception('No se pudo guardar el activo.');
        }
        if ($codigo <= 0) {
            $codigo = (int)$stmt->insert_id;
        }
        $stmt->close();
        if ($estructura && $depreciacionCambio === 1) {
            $detalleDepreciacion = inventarioLocalTextoEntrada('observacion_depreciacion', 255);
            $stmtHistorial = $mysqli->prepare("INSERT INTO inventario_local_depreciacion_historial
                (cod_insumoFK,valor_anterior,valor_nuevo,fecha_depreciacion,observacion,cod_usuarioFK)
                VALUES (?,?,?,?,?,?)");
            $stmtHistorial->bind_param('iiissi', $codigo, $depreciacionAnterior, $depreciacion, $fechaDepreciacion, $detalleDepreciacion, $usuario);
            if (!$stmtHistorial->execute()) {
                $stmtHistorial->close();
                throw new Exception('No se pudo registrar la depreciacion.');
            }
            $stmtHistorial->close();
        }
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        responderErrorInventarioLocal($e->getMessage());
    }
    inventarioLocalResponder('exito', array('cod_inventario' => $codigo, 'estructura_control' => $estructura));
}

function inventarioLocalCargarImagen($mysqli, $usuario)
{
    $codigo = inventarioLocalEnteroEntrada('cod_inventario');
    if ($codigo <= 0) {
        responderErrorInventarioLocal('No se identifico el activo para adjuntar archivos.');
    }
    if (!inventarioLocalTienePermiso($usuario, 'EDITARLISTADOINVENTARIOLOCAL')) {
        inventarioLocalRequerirPermiso($usuario, 'CREARLISTADOINVENTARIOLOCAL');
        $stmtPropietario = $mysqli->prepare('SELECT 1 FROM insumos_local WHERE cod_insumo=? AND cod_usuarioFK_create=? AND fecha_creacion>=DATE_SUB(NOW(),INTERVAL 15 MINUTE) LIMIT 1');
        if (!$stmtPropietario) {
            responderErrorInventarioLocal('No se pudo validar el permiso para adjuntar imagenes.');
        }
        $stmtPropietario->bind_param('ii', $codigo, $usuario);
        $permitido = $stmtPropietario->execute() && $stmtPropietario->get_result()->num_rows > 0;
        $stmtPropietario->close();
        if (!$permitido) {
            responderErrorInventarioLocal('No tiene permiso para modificar los archivos de este activo.', 'NI', 403);
        }
    }
    $archivos = array(
        array('campo' => 'url1', 'foto' => isset($_POST['fotos'][0]) ? $_POST['fotos'][0] : '', 'ext' => isset($_POST['exts'][0]) ? $_POST['exts'][0] : ''),
        array('campo' => 'url2', 'foto' => isset($_POST['fotos'][1]) ? $_POST['fotos'][1] : '', 'ext' => isset($_POST['exts'][1]) ? $_POST['exts'][1] : ''),
        array('campo' => 'url3', 'foto' => isset($_POST['fotos'][2]) ? $_POST['fotos'][2] : '', 'ext' => isset($_POST['exts'][2]) ? $_POST['exts'][2] : ''),
        array('campo' => 'url_factura', 'foto' => isset($_POST['fotoFactura']) ? $_POST['fotoFactura'] : '', 'ext' => isset($_POST['extFactura']) ? $_POST['extFactura'] : ''),
        array('campo' => 'url_compromiso', 'foto' => isset($_POST['fotoCompromiso']) ? $_POST['fotoCompromiso'] : '', 'ext' => isset($_POST['extCompromiso']) ? $_POST['extCompromiso'] : '')
    );
    foreach ($archivos as $archivo) {
        if ($archivo['foto'] === '' || $archivo['ext'] === '') {
            continue;
        }
        $extSolicitada = strtolower(preg_replace('/[^a-z0-9]/i', '', (string)$archivo['ext']));
        if (!in_array($extSolicitada, array('jpg', 'jpeg', 'png', 'webp'), true)) {
            responderErrorInventarioLocal('Uno de los archivos no posee un formato de imagen permitido.');
        }
        $posicionComa = strpos((string)$archivo['foto'], ',');
        $contenido = $posicionComa === false ? (string)$archivo['foto'] : substr((string)$archivo['foto'], $posicionComa + 1);
        $binario = base64_decode($contenido, true);
        if ($binario === false || strlen($binario) > 10485760) {
            responderErrorInventarioLocal('No se pudo leer una imagen adjunta o supera el limite de 10 MB.');
        }
        $infoImagen = @getimagesizefromstring($binario);
        $tiposImagen = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png');
        if (defined('IMAGETYPE_WEBP')) {
            $tiposImagen[IMAGETYPE_WEBP] = 'webp';
        }
        if ($infoImagen === false || !isset($tiposImagen[$infoImagen[2]])) {
            responderErrorInventarioLocal('Uno de los archivos no contiene una imagen valida.');
        }
        $ext = $tiposImagen[$infoImagen[2]];
        $idFoto = subir_imagen_base64('../fotos/fotosInsumoLocal/', $binario, $codigo, $ext);
        $ruta = '/GoodVentaAsisCap/fotos/fotosInsumoLocal/'.$codigo.$idFoto.'.'.$ext;
        $campo = $archivo['campo'];
        $stmt = $mysqli->prepare('UPDATE insumos_local SET '.$campo.'=? WHERE cod_insumo=?');
        $stmt->bind_param('si', $ruta, $codigo);
        if (!$stmt->execute()) {
            $stmt->close();
            responderErrorInventarioLocal('El activo se guardo, pero no se pudo asociar una imagen.');
        }
        $stmt->close();
    }
    inventarioLocalResponder('exito', array('cod_inventario' => $codigo));
}

function inventarioLocalListarSectores($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, 'VERLISTADOINVENTARIOLOCAL');
    if (!inventarioLocalEstructuraControlDisponible($mysqli)) {
        inventarioLocalResponder('SIN_MIGRACION', array('2' => array(), 'mensaje' => 'Falta aplicar la migracion de control de activos.'));
    }
    $localId = inventarioLocalEnteroEntrada('cod_localFK');
    $sql = "SELECT s.id,s.cod_localFK,s.nombre,s.estado,IFNULL(l.Nombre,'') AS nombre_local
        FROM inventario_local_sector s LEFT JOIN local l ON l.cod_local=s.cod_localFK
        WHERE s.estado='activo'";
    $tipos = '';
    $parametros = array();
    if ($localId > 0) {
        $sql .= ' AND s.cod_localFK=?';
        $tipos = 'i';
        $parametros[] = $localId;
    }
    $sql .= ' ORDER BY l.Nombre,s.nombre';
    $filas = inventarioLocalEjecutarFilas($mysqli, $sql, $tipos, $parametros);
    inventarioLocalResponder('exito', array('2' => $filas, 'sectores' => $filas));
}

function inventarioLocalListarResponsables($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, 'VERLISTADOINVENTARIOLOCAL');
    $sql = "SELECT u.cod_usuario,IFNULL(p.nombre_persona,'') AS nombre,IFNULL(u.url,'') AS avatar
        FROM usuario u
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE LOWER(IFNULL(u.estado,'activo'))='activo'
        ORDER BY p.nombre_persona ASC,u.cod_usuario ASC";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        responderErrorInventarioLocal('No se pudo consultar la lista de responsables.');
    }
    $filas = array();
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = inventarioLocalUtf8($fila);
    }
    inventarioLocalResponder('exito', array('2' => $filas, 'responsables' => $filas));
}

function inventarioLocalGuardarSector($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, array('CREARLISTADOINVENTARIOLOCAL', 'EDITARLISTADOINVENTARIOLOCAL'));
    if (!inventarioLocalEstructuraControlDisponible($mysqli)) {
        responderErrorInventarioLocal('Falta aplicar la migracion de control de activos.', 'SIN_MIGRACION');
    }
    $localId = inventarioLocalEnteroEntrada('cod_localFK');
    $nombre = inventarioLocalTextoEntrada('nombre_sector', 100);
    if ($localId <= 0 || mb_strlen($nombre, 'ISO-8859-1') < 2) {
        responderErrorInventarioLocal('Seleccione el local e ingrese un sector valido.');
    }
    $stmtLocal = $mysqli->prepare('SELECT 1 FROM local WHERE cod_local=? LIMIT 1');
    $stmtLocal->bind_param('i', $localId);
    $stmtLocal->execute();
    $localExiste = $stmtLocal->get_result()->num_rows > 0;
    $stmtLocal->close();
    if (!$localExiste) {
        responderErrorInventarioLocal('El local seleccionado ya no existe.');
    }
    $stmt = $mysqli->prepare("INSERT INTO inventario_local_sector
        (cod_localFK,nombre,estado,cod_usuarioFK_create) VALUES (?,?,'activo',?)
        ON DUPLICATE KEY UPDATE estado='activo',cod_usuarioFK_edit=VALUES(cod_usuarioFK_create),fecha_edit=NOW()");
    $stmt->bind_param('isi', $localId, $nombre, $usuario);
    if (!$stmt->execute()) {
        $stmt->close();
        responderErrorInventarioLocal('No se pudo guardar el sector.');
    }
    $sectorId = $stmt->insert_id;
    $stmt->close();
    if (!$sectorId) {
        $stmtId = $mysqli->prepare('SELECT id FROM inventario_local_sector WHERE cod_localFK=? AND nombre=? LIMIT 1');
        $stmtId->bind_param('is', $localId, $nombre);
        $stmtId->execute();
        $fila = $stmtId->get_result()->fetch_assoc();
        $sectorId = $fila ? (int)$fila['id'] : 0;
        $stmtId->close();
    }
    inventarioLocalResponder('exito', array('id' => $sectorId, 'nombre' => $nombre));
}

function inventarioLocalRegistrarVerificacion($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, 'EDITARLISTADOINVENTARIOLOCAL');
    if (!inventarioLocalEstructuraControlDisponible($mysqli)) {
        responderErrorInventarioLocal('Falta aplicar la migracion de control de activos.', 'SIN_MIGRACION');
    }
    $codigo = inventarioLocalEnteroEntrada('cod_inventario');
    $fecha = inventarioLocalFechaEntrada('fecha_verificacion', false);
    $cantidadEncontrada = inventarioLocalEnteroEntrada('cantidad_encontrada', -1);
    $estadoFisico = normalizarEnumInventarioLocal(
        inventarioLocalTextoEntrada('estado_fisico', 30),
        array('excelente', 'mantenimiento', mb_convert_encoding('dañado', 'ISO-8859-1', 'UTF-8'))
    );
    $observacion = inventarioLocalTextoEntrada('observacion', 500);
    if ($codigo <= 0 || $fecha === false || $fecha > date('Y-m-d') || $cantidadEncontrada < 0 || $estadoFisico === false) {
        responderErrorInventarioLocal('Revise fecha, cantidad encontrada y estado fisico.');
    }
    $stmtActivo = $mysqli->prepare("SELECT cantidad,cod_localFK,cod_sectorFK,frecuencia_verificacion
        FROM insumos_local WHERE cod_insumo=? LIMIT 1");
    $stmtActivo->bind_param('i', $codigo);
    $stmtActivo->execute();
    $activo = $stmtActivo->get_result()->fetch_assoc();
    $stmtActivo->close();
    if (!$activo) {
        responderErrorInventarioLocal('El activo seleccionado ya no existe.');
    }
    $cantidadEsperada = max(0, (int)$activo['cantidad']);
    $localId = (int)$activo['cod_localFK'];
    $sectorId = !empty($activo['cod_sectorFK']) ? (int)$activo['cod_sectorFK'] : null;
    $frecuencia = $activo['frecuencia_verificacion'] === 'mensual' ? 'mensual' : 'semestral';
    $proxima = new DateTime($fecha);
    $proxima->modify($frecuencia === 'mensual' ? '+1 month' : '+6 months');
    $proximaFecha = $proxima->format('Y-m-d');
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("INSERT INTO inventario_local_verificacion
            (cod_insumoFK,fecha_verificacion,cantidad_esperada,cantidad_encontrada,estado_fisico,
             cod_localFK,cod_sectorFK,frecuencia_aplicada,proxima_verificacion,observacion,
             cod_usuarioFK_verificador) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('isiisiisssi', $codigo, $fecha, $cantidadEsperada, $cantidadEncontrada, $estadoFisico, $localId, $sectorId, $frecuencia, $proximaFecha, $observacion, $usuario);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('No se pudo guardar la verificacion.');
        }
        $verificacionId = (int)$stmt->insert_id;
        $stmt->close();
        $stmtActivoUpdate = $mysqli->prepare('UPDATE insumos_local SET estado_fisico=?,cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE cod_insumo=?');
        $stmtActivoUpdate->bind_param('sii', $estadoFisico, $usuario, $codigo);
        if (!$stmtActivoUpdate->execute()) {
            $stmtActivoUpdate->close();
            throw new Exception('No se pudo actualizar el estado fisico del activo.');
        }
        $stmtActivoUpdate->close();
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        responderErrorInventarioLocal($e->getMessage());
    }
    inventarioLocalResponder('exito', array('id' => $verificacionId, 'proxima_verificacion' => $proximaFecha));
}

function inventarioLocalListarVerificaciones($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, 'VERLISTADOINVENTARIOLOCAL');
    if (!inventarioLocalEstructuraControlDisponible($mysqli)) {
        inventarioLocalResponder('SIN_MIGRACION', array('2' => array(), 'mensaje' => 'Falta aplicar la migracion de control de activos.'));
    }
    $codigo = inventarioLocalEnteroEntrada('cod_inventario');
    $sql = "SELECT iv.id,iv.fecha_verificacion,iv.cantidad_esperada,iv.cantidad_encontrada,
        (iv.cantidad_encontrada-iv.cantidad_esperada) AS diferencia,iv.estado_fisico,
        iv.frecuencia_aplicada,iv.proxima_verificacion,iv.observacion,iv.fecha_creacion,
        IFNULL(p.nombre_persona,'') AS verificador,IFNULL(u.url,'') AS avatar_verificador
        FROM inventario_local_verificacion iv
        LEFT JOIN usuario u ON u.cod_usuario=iv.cod_usuarioFK_verificador
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE iv.cod_insumoFK=? ORDER BY iv.fecha_verificacion DESC,iv.id DESC LIMIT 100";
    $parametros = array($codigo);
    $filas = inventarioLocalEjecutarFilas($mysqli, $sql, 'i', $parametros);
    inventarioLocalResponder('exito', array('2' => $filas, 'verificaciones' => $filas));
}

function inventarioLocalHistorialResponsables($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, 'VERLISTADOINVENTARIOLOCAL');
    $codigo = inventarioLocalEnteroEntrada('cod_inventario');
    $sql = "SELECT h.id,h.fecha_creacion,
        IFNULL(p.nombre_persona,'') AS nombre_usuarioFK_responsable_anterior,
        IFNULL(pe.nombre_persona,'') AS nombre_usuarioFK_edit
        FROM historial_insumo_local h
        LEFT JOIN usuario u ON u.cod_usuario=h.cod_usuarioFK_responsable_anterior
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN usuario ue ON ue.cod_usuario=h.cod_usuarioFK_edit
        LEFT JOIN persona pe ON pe.cod_persona=ue.cod_usuario
        WHERE h.cod_insumoFK=? ORDER BY h.fecha_creacion DESC,h.id DESC";
    $parametros = array($codigo);
    $filas = inventarioLocalEjecutarFilas($mysqli, $sql, 'i', $parametros);
    $html = '';
    foreach ($filas as $fila) {
        $html .= '<tr><td style="width:10%">'.(int)$fila['id'].'</td><td style="width:55%">'
            .htmlspecialchars(inventarioLocalUtf8($fila['nombre_usuarioFK_responsable_anterior']), ENT_QUOTES, 'UTF-8')
            .'</td><td style="width:35%">'.htmlspecialchars((string)$fila['fecha_creacion'], ENT_QUOTES, 'UTF-8').'</td></tr>';
    }
    inventarioLocalResponder('exito', array('2' => $html, '3' => $filas));
}

function inventarioLocalSiguienteCodigo($mysqli, $usuario)
{
    inventarioLocalRequerirPermiso($usuario, array('VERLISTADOINVENTARIOLOCAL', 'CREARLISTADOINVENTARIOLOCAL'));
    $resultado = $mysqli->query('SELECT COALESCE(MAX(cod_insumo),0)+1 AS siguiente FROM insumos_local');
    $fila = $resultado ? $resultado->fetch_assoc() : array('siguiente' => 1);
    inventarioLocalResponder('exito', array('2' => (int)$fila['siguiente']));
}

function verificar($operacion)
{
    $usuario = inventarioLocalUsuarioSesion();
    $mysqli = conectar_al_servidor();
    if ($mysqli->connect_errno) {
        responderErrorInventarioLocal('No se pudo conectar con la base de datos.', 'error', 503);
    }
    switch ($operacion) {
        case 'buscarVista':
            inventarioLocalRequerirPermiso($usuario, 'VERLISTADOINVENTARIOLOCAL');
            $resultado = inventarioLocalBuscar($mysqli, inventarioLocalFiltrosEntrada($mysqli), inventarioLocalEnteroEntrada('pagina', 1), inventarioLocalEnteroEntrada('limite', 25), false);
            inventarioLocalResponder('exito', array(
                '2' => '', '3' => $resultado['registros'], '4' => count($resultado['registros']),
                '5' => (int)$resultado['resumen']['registros'], '6' => (int)$resultado['resumen']['valor_total'],
                'resumen' => $resultado['resumen'],
                'paginacion' => array('pagina' => $resultado['pagina'], 'limite' => $resultado['limite'], 'total_paginas' => $resultado['total_paginas']),
                'estructura_control' => $resultado['estructura_control']
            ));
            break;
        case 'buscarReporte':
            inventarioLocalRequerirPermiso($usuario, 'VERLISTADOINVENTARIOLOCAL');
            $resultado = inventarioLocalBuscar($mysqli, inventarioLocalFiltrosEntrada($mysqli), 1, 100, true);
            inventarioLocalResponder('exito', array('registros' => $resultado['registros'], 'resumen' => $resultado['resumen'], 'estructura_control' => $resultado['estructura_control']));
            break;
        case 'nuevo/editar': inventarioLocalGuardar($mysqli, $usuario); break;
        case 'cargar_imagen': inventarioLocalCargarImagen($mysqli, $usuario); break;
        case 'obtenerUltimoId': inventarioLocalSiguienteCodigo($mysqli, $usuario); break;
        case 'buscarHistorialResponsablesAnteriores': inventarioLocalHistorialResponsables($mysqli, $usuario); break;
        case 'listarSectores': inventarioLocalListarSectores($mysqli, $usuario); break;
        case 'listarResponsables': inventarioLocalListarResponsables($mysqli, $usuario); break;
        case 'guardarSector': inventarioLocalGuardarSector($mysqli, $usuario); break;
        case 'registrarVerificacion': inventarioLocalRegistrarVerificacion($mysqli, $usuario); break;
        case 'listarVerificaciones': inventarioLocalListarVerificaciones($mysqli, $usuario); break;
        default: responderErrorInventarioLocal('La operacion solicitada no esta implementada.');
    }
}

$operacion = isset($_POST['accion']) ? trim((string)$_POST['accion']) : '';
verificar($operacion);

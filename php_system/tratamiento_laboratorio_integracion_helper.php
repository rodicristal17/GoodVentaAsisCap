<?php

/**
 * Contexto clinico minimo para integrar la evolucion de un tratamiento con
 * Trabajos de laboratorio. Este helper es solo de lectura: no crea trabajos ni
 * modifica el plan clinico y puede convivir con instalaciones sin la migracion.
 */

function tratamientoLaboratorioTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = trim((string)$tabla);
    if ($tabla === '') {
        return false;
    }
    $clave = spl_object_hash($mysqli).'|'.$tabla;
    if (array_key_exists($clave, $cache)) {
        return $cache[$clave];
    }
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $stmt->bind_param('s', $tabla);
    $ok = $stmt->execute();
    $fila = $ok ? $stmt->get_result()->fetch_assoc() : null;
    $stmt->close();
    $cache[$clave] = $fila && intval($fila['total']) > 0;
    return $cache[$clave];
}

function tratamientoLaboratorioColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $tabla = trim((string)$tabla);
    $columna = trim((string)$columna);
    if ($tabla === '' || $columna === '') {
        return false;
    }
    $clave = spl_object_hash($mysqli).'|'.$tabla.'|'.$columna;
    if (array_key_exists($clave, $cache)) {
        return $cache[$clave];
    }
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    $ok = $stmt->execute();
    $fila = $ok ? $stmt->get_result()->fetch_assoc() : null;
    $stmt->close();
    $cache[$clave] = $fila && intval($fila['total']) > 0;
    return $cache[$clave];
}

function tratamientoLaboratorioTextoUtf8($valor)
{
    $valor = (string)$valor;
    if ($valor === '' || !function_exists('mb_check_encoding') || mb_check_encoding($valor, 'UTF-8')) {
        return $valor;
    }
    return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
}

function tratamientoLaboratorioBooleano($valor)
{
    if ($valor === null || $valor === '') {
        return null;
    }
    if (is_bool($valor)) {
        return $valor;
    }
    $texto = strtolower(trim((string)$valor));
    return in_array($texto, array('1', 'si', 's', 'true', 'activo'), true);
}

function tratamientoLaboratorioNumeroVenta($fila)
{
    $punto = isset($fila['puntoexpedicion']) ? trim((string)$fila['puntoexpedicion']) : '';
    $numero = isset($fila['num_factura']) ? trim((string)$fila['num_factura']) : '';
    if ($punto !== '' && $numero !== '') {
        return $punto.'-'.$numero;
    }
    if ($numero !== '') {
        return $numero;
    }
    return isset($fila['cod_ventaFK']) ? (string)$fila['cod_ventaFK'] : '';
}

function tratamientoLaboratorioListaJson($json)
{
    $salida = array();
    $json = trim((string)$json);
    if ($json === '') {
        return $salida;
    }
    $decodificado = json_decode($json, true);
    if (!is_array($decodificado)) {
        return $salida;
    }
    foreach ($decodificado as $valor) {
        if (is_array($valor)) {
            $valor = isset($valor['pieza']) ? $valor['pieza'] : '';
        }
        $valor = trim((string)$valor);
        if ($valor !== '' && !in_array($valor, $salida, true)) {
            $salida[] = $valor;
        }
    }
    return $salida;
}

function tratamientoLaboratorioUbicacionesDetalle($mysqli, $codDetalle)
{
    $resultado = array('ubicaciones' => array(), 'piezas' => array());
    if (!tratamientoLaboratorioTablaExiste($mysqli, 'odontograma_tratamiento_links')) {
        return $resultado;
    }
    $columnas = array('id', 'alcance_odontologico', 'pieza', 'piezas_json', 'superficies_json', 'arcada', 'cuadrante', 'boca_completa', 'denticion');
    foreach ($columnas as $columna) {
        if (!tratamientoLaboratorioColumnaExiste($mysqli, 'odontograma_tratamiento_links', $columna)) {
            return $resultado;
        }
    }
    $condicionActivo = tratamientoLaboratorioColumnaExiste($mysqli, 'odontograma_tratamiento_links', 'activo') ? ' AND activo=1' : '';
    $stmt = $mysqli->prepare("SELECT id,alcance_odontologico,pieza,piezas_json,superficies_json,arcada,cuadrante,boca_completa,denticion FROM odontograma_tratamiento_links WHERE detalle_venta_id=?".$condicionActivo." ORDER BY id ASC");
    if (!$stmt) {
        return $resultado;
    }
    $codDetalleTexto = (string)$codDetalle;
    $stmt->bind_param('s', $codDetalleTexto);
    if (!$stmt->execute()) {
        $stmt->close();
        return $resultado;
    }
    $consulta = $stmt->get_result();
    while ($fila = $consulta->fetch_assoc()) {
        $piezas = tratamientoLaboratorioListaJson($fila['piezas_json']);
        $pieza = trim((string)$fila['pieza']);
        if ($pieza !== '' && !in_array($pieza, $piezas, true)) {
            $piezas[] = $pieza;
        }
        foreach ($piezas as $itemPieza) {
            if (!in_array($itemPieza, $resultado['piezas'], true)) {
                $resultado['piezas'][] = $itemPieza;
            }
        }
        $resultado['ubicaciones'][] = array(
            'id' => intval($fila['id']),
            'alcance' => trim((string)$fila['alcance_odontologico']),
            'pieza' => $pieza,
            'piezas' => $piezas,
            'superficies' => tratamientoLaboratorioListaJson($fila['superficies_json']),
            'arcada' => trim((string)$fila['arcada']),
            'cuadrante' => trim((string)$fila['cuadrante']),
            'boca_completa' => intval($fila['boca_completa']) === 1,
            'denticion' => trim((string)$fila['denticion'])
        );
    }
    $stmt->close();
    return $resultado;
}

function tratamientoLaboratorioContextoEvolucion($mysqli, $codDetalle, $codVenta, $origen, $codConsulta = null, $codEvolucion = null)
{
    $codDetalle = trim((string)$codDetalle);
    $codVenta = trim((string)$codVenta);
    $contexto = array(
        'disponible' => false,
        'requiere_laboratorio' => false,
        'puede_iniciar' => false,
        'instalacion_explicita_requerida' => true,
        'cod_detalle_venta' => ctype_digit($codDetalle) ? intval($codDetalle) : 0,
        'cod_venta' => ctype_digit($codVenta) ? intval($codVenta) : 0,
        'cod_consulta_origen' => $codConsulta !== null ? intval($codConsulta) : null,
        'cod_evolucion_origen' => $codEvolucion !== null ? intval($codEvolucion) : null,
        'origen' => trim((string)$origen),
        'ubicaciones' => array(),
        'piezas' => array(),
        'cantidad_ubicaciones' => 0,
		'cantidad_trabajos_sugerida' => 1,
		'trabajo_activo' => null,
		'bloqueos' => array(),
		'acciones_permitidas' => array()
    );
    if (!($mysqli instanceof mysqli) || !ctype_digit($codDetalle) || intval($codDetalle) <= 0) {
        $contexto['codigo'] = 'detalle_invalido';
        return $contexto;
    }
    if (!tratamientoLaboratorioColumnaExiste($mysqli, 'producto', 'requiere_laboratorio')) {
        $contexto['codigo'] = 'configuracion_laboratorio_no_instalada';
        return $contexto;
    }

    $productoModo = tratamientoLaboratorioColumnaExiste($mysqli, 'producto', 'modo_individualizacion')
        ? 'pr.modo_individualizacion' : 'NULL';
    $categoriaRequiere = tratamientoLaboratorioColumnaExiste($mysqli, 'categoria', 'requiere_laboratorio')
        ? 'ca.requiere_laboratorio' : '0';
    $categoriaModo = tratamientoLaboratorioColumnaExiste($mysqli, 'categoria', 'modo_individualizacion')
        ? 'ca.modo_individualizacion' : 'NULL';
    $sql = "SELECT dv.cod_detalle,dv.cod_ventaFK,dv.cantidad_detalle,dv.cod_productoFK,
            pr.nombre_producto,pr.cod_categoriaFK,pr.requiere_laboratorio AS producto_requiere,
            ".$productoModo." AS producto_modo,
            ".$categoriaRequiere." AS categoria_requiere,
            ".$categoriaModo." AS categoria_modo,
            v.puntoexpedicion,v.num_factura,v.cod_local
        FROM detalle_venta dv
        INNER JOIN producto pr ON pr.cod_producto=dv.cod_productoFK
        INNER JOIN venta v ON v.cod_venta=dv.cod_ventaFK
        LEFT JOIN categoria ca ON ca.cod_categoria=pr.cod_categoriaFK
        WHERE dv.cod_detalle=?";
    if ($codVenta !== '' && ctype_digit($codVenta)) {
        $sql .= ' AND dv.cod_ventaFK=?';
    }
    $sql .= ' LIMIT 1';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $contexto['codigo'] = 'contexto_no_disponible';
        return $contexto;
    }
    if ($codVenta !== '' && ctype_digit($codVenta)) {
        $stmt->bind_param('ss', $codDetalle, $codVenta);
    } else {
        $stmt->bind_param('s', $codDetalle);
    }
    if (!$stmt->execute()) {
        $stmt->close();
        $contexto['codigo'] = 'contexto_no_disponible';
        return $contexto;
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        $contexto['codigo'] = 'detalle_no_encontrado';
        return $contexto;
    }

    $productoRequiere = tratamientoLaboratorioBooleano($fila['producto_requiere']);
    $categoriaRequiere = tratamientoLaboratorioBooleano($fila['categoria_requiere']);
    $requiere = $productoRequiere !== null ? $productoRequiere : ($categoriaRequiere === true);
    $modoProducto = trim((string)$fila['producto_modo']);
    $modoCategoria = trim((string)$fila['categoria_modo']);
    $modo = $modoProducto !== '' ? $modoProducto : $modoCategoria;
    $modosValidos = array('cantidad_libre', 'pieza_individual', 'multipieza', 'arcada', 'sector', 'dispositivo');
    if (!in_array($modo, $modosValidos, true)) {
        $modo = '';
    }
    $cantidad = floatval($fila['cantidad_detalle']);
    $cantidadValida = abs($cantidad - 1.0) < 0.000001;
    $ubicaciones = tratamientoLaboratorioUbicacionesDetalle($mysqli, $codDetalle);

    $contexto['disponible'] = true;
    $contexto['codigo'] = $requiere ? ($cantidadValida ? 'requiere_laboratorio' : 'cantidad_laboratorio_invalida') : 'no_requiere_laboratorio';
    $contexto['requiere_laboratorio'] = $requiere;
    $contexto['puede_iniciar'] = $requiere && $cantidadValida;
    $contexto['cantidad_detalle'] = $cantidad;
    $contexto['cantidad_valida'] = $cantidadValida;
    $contexto['cod_detalle_venta'] = intval($fila['cod_detalle']);
    $contexto['cod_venta'] = intval($fila['cod_ventaFK']);
    $contexto['cod_producto'] = (string)$fila['cod_productoFK'];
    $contexto['producto'] = tratamientoLaboratorioTextoUtf8($fila['nombre_producto']);
    $contexto['cod_categoria'] = isset($fila['cod_categoriaFK']) ? intval($fila['cod_categoriaFK']) : null;
    $contexto['modo_individualizacion'] = $modo;
    $contexto['numero_venta'] = tratamientoLaboratorioTextoUtf8(tratamientoLaboratorioNumeroVenta($fila));
    $contexto['cod_local'] = isset($fila['cod_local']) ? intval($fila['cod_local']) : null;
    $contexto['ubicaciones'] = $ubicaciones['ubicaciones'];
    $contexto['piezas'] = $ubicaciones['piezas'];
    $contexto['cantidad_ubicaciones'] = count($ubicaciones['piezas']) > 0
        ? count($ubicaciones['piezas']) : count($ubicaciones['ubicaciones']);
	$contexto['detalle'] = array(
		'cod_detalle_venta' => intval($fila['cod_detalle']),
		'cantidad' => $cantidad,
		'cod_venta' => intval($fila['cod_ventaFK']),
		'nro_venta' => $contexto['numero_venta'],
		'cod_local' => $contexto['cod_local'],
		'cod_producto' => (string)$fila['cod_productoFK'],
		'nombre_producto' => $contexto['producto'],
		'cod_categoria' => $contexto['cod_categoria'],
		'requiere_laboratorio' => $requiere,
		'modo_individualizacion' => $modo
	);
    if ($requiere && !$cantidadValida) {
        $contexto['mensaje'] = 'El trabajo de laboratorio requiere un detalle de venta con cantidad 1.';
		$contexto['bloqueos'][] = array(
			'codigo' => 'cantidad_laboratorio_invalida',
			'mensaje' => $contexto['mensaje']
		);
    }
    return $contexto;
}

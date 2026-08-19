<?php

/**
 * Verifica y, solo con confirmacion explicita, aplica la configuracion inicial
 * de ocho incrustaciones activas de OPERATORIA que requieren laboratorio.
 *
 * Solo lectura:
 *   php scripts/verificar_configuracion_laboratorio_operatoria.php
 *
 * Aplicacion controlada e idempotencia:
 *   php scripts/verificar_configuracion_laboratorio_operatoria.php --aplicar-migracion
 *
 * Compatible con PHP 7.2. No muestra pacientes, ventas ni datos de sesion.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Este verificador solo puede ejecutarse por consola.'.PHP_EOL;
    exit(1);
}

require_once dirname(__DIR__).'/php_system/conexion.php';

function verificarOperatoriaLabFallar($mensaje)
{
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
    exit(1);
}

function verificarOperatoriaLabOk($mensaje)
{
    fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
}

function verificarOperatoriaLabInfo($mensaje)
{
    fwrite(STDOUT, '[INFO] '.$mensaje.PHP_EOL);
}

function verificarOperatoriaLabAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        verificarOperatoriaLabFallar($mensaje);
    }
    verificarOperatoriaLabOk($mensaje);
}

function verificarOperatoriaLabFila($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        verificarOperatoriaLabFallar('No se pudo ejecutar una comprobacion agregada: '.$mysqli->error);
    }
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    return $fila ? $fila : array();
}

function verificarOperatoriaLabEscalar($mysqli, $sql)
{
    $fila = verificarOperatoriaLabFila($mysqli, $sql);
    $valores = array_values($fila);
    return isset($valores[0]) ? intval($valores[0]) : 0;
}

function verificarOperatoriaLabListaSql($valores)
{
    $salida = array();
    foreach ($valores as $valor) {
        $salida[] = "'".str_replace("'", "''", (string)$valor)."'";
    }
    return implode(',', $salida);
}

function verificarOperatoriaLabEjecutarMigracion($mysqli, $ruta)
{
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        verificarOperatoriaLabFallar('No se pudo leer la migracion controlada.');
    }
    $buffer = '';
    $numero = 0;
    foreach ($lineas as $linea) {
        $recortada = trim($linea);
        if ($buffer === '' && ($recortada === '' || strpos($recortada, '--') === 0)) {
            continue;
        }
        $buffer .= $linea.PHP_EOL;
        $contenido = rtrim($buffer);
        if ($contenido === '' || substr($contenido, -1) !== ';') {
            continue;
        }
        $sentencia = trim(substr($contenido, 0, -1));
        $buffer = '';
        if ($sentencia === '') {
            continue;
        }
        $numero++;
        if (!$mysqli->query($sentencia)) {
            $mysqli->rollback();
            verificarOperatoriaLabFallar('La migracion fallo en la sentencia '.$numero.': '.$mysqli->error);
        }
    }
    if (trim($buffer) !== '') {
        $mysqli->rollback();
        verificarOperatoriaLabFallar('La migracion contiene una sentencia sin delimitador final.');
    }
    verificarOperatoriaLabOk('Migracion ejecutada sin errores ('.$numero.' sentencias).');
}

function verificarOperatoriaLabFirmaProtegida($mysqli)
{
    return array(
        'producto_base' => verificarOperatoriaLabFila(
            $mysqli,
            "SELECT COUNT(*) AS cantidad,COALESCE(SUM(CRC32(CONCAT_WS('|',cod_producto,CASE WHEN cod_producto='10043' THEN 86 ELSE cod_categoriaFK END,estado,nombre_producto,precio_producto,precio_compra))),0) AS firma FROM producto"
        ),
        'stock' => verificarOperatoriaLabFila(
            $mysqli,
            "SELECT COUNT(*) AS cantidad,COALESCE(SUM(CRC32(CONCAT_WS('|',cod_productofk,cod_localfk,cantidad))),0) AS firma FROM stocklocales"
        ),
        'tratamientos' => verificarOperatoriaLabFila(
            $mysqli,
            "SELECT COUNT(*) AS cantidad,COALESCE(SUM(CRC32(CONCAT_WS('|',cod_detalle,cod_productoFK,cod_ventaFK,cantidad_detalle,estado))),0) AS firma FROM detalle_venta"
        ),
        'trabajos' => verificarOperatoriaLabFila(
            $mysqli,
            "SELECT COUNT(*) AS cantidad,COALESCE(SUM(version),0) AS firma FROM trabajo_laboratorio"
        )
    );
}

$argumentos = isset($argv) ? $argv : array();
$aplicar = in_array('--aplicar-migracion', $argumentos, true);
$ayuda = in_array('--ayuda', $argumentos, true) || in_array('-h', $argumentos, true);
foreach ($argumentos as $indice => $argumento) {
    if ($indice === 0) {
        continue;
    }
    if (!in_array($argumento, array('--aplicar-migracion', '--ayuda', '-h'), true)) {
        verificarOperatoriaLabFallar('Argumento no reconocido. Use --ayuda para consultar el uso.');
    }
}
if ($ayuda) {
    fwrite(STDOUT, "Uso:\n  php scripts/verificar_configuracion_laboratorio_operatoria.php\n  php scripts/verificar_configuracion_laboratorio_operatoria.php --aplicar-migracion\n");
    exit(0);
}

$codigos = array('10066','10128','10043','10125','10044','10126','10060','10127');
$listaCodigos = verificarOperatoriaLabListaSql($codigos);
$rutaMigracion = dirname(__DIR__).'/actualizacion_19082026_incrustaciones_operatoria_laboratorio.sql';
$fuente = file_get_contents($rutaMigracion);
verificarOperatoriaLabAfirmar($fuente !== false && trim($fuente) !== '', 'La migracion controlada esta disponible.');
$fuenteSinComentarios = preg_replace('/^\s*--.*$/m', '', $fuente);
verificarOperatoriaLabAfirmar(
    preg_match('/\b(?:DELETE|TRUNCATE|DROP|ALTER)\b/i', $fuenteSinComentarios) !== 1,
    'La migracion no elimina datos ni modifica la estructura.'
);
verificarOperatoriaLabAfirmar(
    preg_match('/\b(?:stocklocales|detalle_venta|venta|cliente|persona|trabajo_laboratorio)\b/i', $fuenteSinComentarios) !== 1,
    'La migracion no modifica stock, pacientes, ventas, tratamientos ni trabajos existentes.'
);

$fuenteProductos = file_get_contents(dirname(__DIR__).'/php_system/abmproductos.php');
$fuenteConsulta = file_get_contents(dirname(__DIR__).'/php_system/abmConsulta.php');
$fuenteInicio = file_get_contents(dirname(__DIR__).'/system/inicio.html');
$fuenteJavascript = file_get_contents(dirname(__DIR__).'/js_system/inicio.js');
verificarOperatoriaLabAfirmar(
    $fuenteProductos !== false && $fuenteConsulta !== false && $fuenteInicio !== false && $fuenteJavascript !== false,
    'Los archivos del flujo de catalogo y Consulta estan disponibles.'
);
verificarOperatoriaLabAfirmar(
    strpos($fuenteProductos, 'editar_laboratorio_producto') !== false
    && strpos($fuenteProductos, 'EDITARLISTADOPRODUCTOS') !== false
    && strpos($fuenteProductos, "'LABORATORIO'") !== false
    && strpos($fuenteProductos, 'begin_transaction') !== false,
    'El guardado rapido valida permiso, usa transaccion y deja auditoria.'
);
$selectorDetalle = '';
if (preg_match("/<select[^>]+id='inptRequiereLaboratorioProducto'[^>]*>(.*?)<\\/select>/s", $fuenteInicio, $coincidenciaSelector)) {
    $selectorDetalle = $coincidenciaSelector[1];
}
verificarOperatoriaLabAfirmar(
    $selectorDetalle !== ''
    && substr_count($selectorDetalle, '<option') === 2
    && strpos($selectorDetalle, "value='1'") !== false
    && strpos($selectorDetalle, "value='0'") !== false
    && strpos($selectorDetalle, "value=''") === false,
    'La edicion detallada ofrece solamente Si requiere y No requiere.'
);
verificarOperatoriaLabAfirmar(
    strpos($fuenteProductos, 'catalogo-laboratorio-select') !== false
    && strpos($fuenteProductos, 'td_datos_laboratorio') !== false
    && strpos($fuenteJavascript, 'guardarLaboratorioProductoCatalogo') !== false
    && strpos($fuenteJavascript, 'catalogoLaboratorioRestaurar') !== false,
    'El listado guarda en linea, informa estado y restaura el valor ante errores.'
);
verificarOperatoriaLabAfirmar(
    strpos($fuenteConsulta, 'EsTratamientoLaboratorioConsulta') !== false
    && strpos($fuenteConsulta, 'EsCategoriaProtesisLaboratorioConsulta') === false
    && strpos($fuenteConsulta, 'es_protesis_laboratorio') === false,
    'Consulta habilita el flujo por configuracion efectiva del producto y no por categoria.'
);

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    verificarOperatoriaLabFallar('No se pudo conectar con la base configurada.');
}

$enOperatoria = verificarOperatoriaLabEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM producto p INNER JOIN categoria c ON c.cod_categoria=p.cod_categoriaFK "
    ."WHERE p.cod_producto IN (".$listaCodigos.") AND p.estado='Activo' "
    ."AND c.cod_categoria=86 AND UPPER(TRIM(c.descripcion))='OPERATORIA'"
);
$inlayPendienteCategoria = verificarOperatoriaLabEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM producto p INNER JOIN categoria c ON c.cod_categoria=p.cod_categoriaFK "
    ."WHERE p.cod_producto='10043' AND p.estado='Activo' AND c.cod_categoria=91 "
    ."AND UPPER(TRIM(c.descripcion))='PROTESIS' AND UPPER(p.nombre_producto) LIKE '%INLAY%' "
    ."AND p.requiere_laboratorio IS NULL "
    ."AND (p.modo_individualizacion IS NULL OR TRIM(p.modo_individualizacion)='')"
);
verificarOperatoriaLabAfirmar(
    $enOperatoria + $inlayPendienteCategoria === 8,
    'Los ocho productos estan en OPERATORIA o en la unica normalizacion historica permitida.'
);
verificarOperatoriaLabInfo(
    'Productos ya clasificados en OPERATORIA: '.$enOperatoria
    .'; Inlay 10043 pendiente de normalizacion: '.$inlayPendienteCategoria.'.'
);
verificarOperatoriaLabAfirmar(
    verificarOperatoriaLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM producto WHERE cod_producto IN (".$listaCodigos.") "
        ."AND (UPPER(nombre_producto) LIKE '%INCRUSTACION%' OR UPPER(nombre_producto) LIKE '%INLAY%' OR UPPER(nombre_producto) LIKE '%ONLAY%' OR UPPER(nombre_producto) LIKE '%OVERLAY%')"
    ) === 8,
    'Los codigos siguen correspondiendo a incrustaciones, inlay, onlay u overlay.'
);

$conflictos = verificarOperatoriaLabEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM producto WHERE cod_producto IN (".$listaCodigos.") AND NOT ("
    ."(requiere_laboratorio IS NULL AND (modo_individualizacion IS NULL OR TRIM(modo_individualizacion)='')) OR "
    ."(requiere_laboratorio=1 AND modo_individualizacion='pieza_individual'))"
);
verificarOperatoriaLabAfirmar($conflictos === 0, 'No existen decisiones explicitas contradictorias en los ocho productos.');

$pendientes = verificarOperatoriaLabEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM producto WHERE cod_producto IN (".$listaCodigos.") "
    ."AND requiere_laboratorio IS NULL AND (modo_individualizacion IS NULL OR TRIM(modo_individualizacion)='')"
);
verificarOperatoriaLabInfo('Productos pendientes de configuracion inicial: '.$pendientes.'.');

if ($aplicar) {
    $firmaAntes = verificarOperatoriaLabFirmaProtegida($mysqli);
    verificarOperatoriaLabEjecutarMigracion($mysqli, $rutaMigracion);
    $firmaPrimera = verificarOperatoriaLabFirmaProtegida($mysqli);
    verificarOperatoriaLabAfirmar($firmaAntes === $firmaPrimera, 'La primera ejecucion preservo catalogo base, stock, tratamientos y trabajos.');
    verificarOperatoriaLabEjecutarMigracion($mysqli, $rutaMigracion);
    $firmaSegunda = verificarOperatoriaLabFirmaProtegida($mysqli);
    verificarOperatoriaLabAfirmar($firmaPrimera === $firmaSegunda, 'La segunda ejecucion fue idempotente sobre los datos protegidos.');
}

$habilitados = verificarOperatoriaLabEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM producto WHERE cod_producto IN (".$listaCodigos.") "
    ."AND cod_categoriaFK=86 AND estado='Activo' AND requiere_laboratorio=1 "
    ."AND modo_individualizacion='pieza_individual' "
    ."AND alcance_odontologico IN ('pieza_dental','pieza_superficie')"
);
if ($habilitados !== 8) {
    if (!$aplicar && $pendientes > 0) {
        verificarOperatoriaLabInfo('La configuracion aun no fue aplicada. Use --aplicar-migracion despues de revisar el SQL.');
        $mysqli->close();
        exit(0);
    }
    $mysqli->close();
    verificarOperatoriaLabFallar('No quedaron habilitados correctamente los ocho productos.');
}

verificarOperatoriaLabOk('Los ocho productos originan laboratorio por pieza sin cambiar de OPERATORIA.');
$mysqli->close();

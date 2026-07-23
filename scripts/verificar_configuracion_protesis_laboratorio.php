<?php

/**
 * Verifica y, con confirmacion explicita por argumento, aplica la
 * configuracion inicial de productos de PROTESIS para laboratorio.
 *
 * Solo lectura:
 *   php scripts/verificar_configuracion_protesis_laboratorio.php
 *
 * Aplicacion controlada e idempotencia:
 *   php scripts/verificar_configuracion_protesis_laboratorio.php --aplicar-migracion
 *
 * La salida contiene solamente estructura, codigos de catalogo y conteos.
 * No muestra pacientes, ventas, observaciones ni datos de sesion.
 * Compatible con PHP 7.2.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Este verificador solo puede ejecutarse por consola.'.PHP_EOL;
    exit(1);
}

require_once dirname(__DIR__).'/php_system/conexion.php';

function verificarProtesisLabFallar($mensaje)
{
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
    exit(1);
}

function verificarProtesisLabOk($mensaje)
{
    fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
}

function verificarProtesisLabInfo($mensaje)
{
    fwrite(STDOUT, '[INFO] '.$mensaje.PHP_EOL);
}

function verificarProtesisLabAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        verificarProtesisLabFallar($mensaje);
    }
    verificarProtesisLabOk($mensaje);
}

function verificarProtesisLabFila($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        verificarProtesisLabFallar('No se pudo ejecutar una comprobacion agregada: '.$mysqli->error);
    }
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    return $fila ? $fila : array();
}

function verificarProtesisLabEscalar($mysqli, $sql)
{
    $fila = verificarProtesisLabFila($mysqli, $sql);
    $valores = array_values($fila);
    return isset($valores[0]) ? intval($valores[0]) : 0;
}

function verificarProtesisLabColumnaExiste($mysqli, $tabla, $columna)
{
    $tablaBd = $mysqli->real_escape_string($tabla);
    $columnaBd = $mysqli->real_escape_string($columna);
    return verificarProtesisLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.COLUMNS "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$tablaBd."' "
        ."AND COLUMN_NAME='".$columnaBd."'"
    ) === 1;
}

function verificarProtesisLabListaSql($valores)
{
    $salida = array();
    foreach ($valores as $valor) {
        $salida[] = "'".str_replace("'", "''", (string)$valor)."'";
    }
    return implode(',', $salida);
}

function verificarProtesisLabEjecutarMigracion($mysqli, $ruta)
{
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        verificarProtesisLabFallar('No se pudo leer la migracion controlada.');
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
            verificarProtesisLabFallar(
                'La migracion fallo en la sentencia '.$numero.': '.$mysqli->error
            );
        }
    }
    if (trim($buffer) !== '') {
        $mysqli->rollback();
        verificarProtesisLabFallar('La migracion contiene una sentencia sin delimitador final.');
    }
    verificarProtesisLabOk('Migracion ejecutada sin errores ('.$numero.' sentencias).');
}

function verificarProtesisLabFirmaProtegida($mysqli)
{
    $firmas = array();
    $consultas = array(
        'producto_base' => "SELECT COUNT(*) AS cantidad,"
            ."COALESCE(SUM(CRC32(CONCAT_WS('|',cod_producto,cod_categoriaFK,estado,"
            ."nombre_producto))),0) AS firma FROM producto",
        'detalle_venta' => "SELECT COUNT(*) AS cantidad,"
            ."COALESCE(SUM(CRC32(CONCAT_WS('|',cod_detalle,cod_productoFK,cod_ventaFK,"
            ."cantidad_detalle,estado,COALESCE(estado_tratamiento,''),COALESCE(progreso_porcentaje,0)))),0) AS firma "
            ."FROM detalle_venta",
        'trabajo_legacy' => "SELECT COUNT(*) AS cantidad,"
            ."COALESCE(SUM(CRC32(CONCAT_WS('|',cod_trabajo_mecanico_dental,cod_ventaFK,estado))),0) AS firma "
            ."FROM trabajo_mecanico_dental",
        'trabajo_nuevo' => "SELECT COUNT(*) AS cantidad,COALESCE(SUM(version),0) AS firma FROM trabajo_laboratorio",
        'permisos' => "SELECT COUNT(*) AS cantidad,COALESCE(SUM(idlistadodeacceso),0) AS firma "
            ."FROM listadodeacceso"
    );
    foreach ($consultas as $clave => $sql) {
        $firmas[$clave] = verificarProtesisLabFila($mysqli, $sql);
    }
    return $firmas;
}

function verificarProtesisLabEstado($mysqli)
{
    return verificarProtesisLabFila(
        $mysqli,
        "SELECT COUNT(*) AS total,"
        ."SUM(CASE WHEN p.estado='Activo' THEN 1 ELSE 0 END) AS activos,"
        ."SUM(CASE WHEN p.estado<>'Activo' THEN 1 ELSE 0 END) AS inactivos,"
        ."SUM(CASE WHEN p.estado='Activo' AND p.requiere_laboratorio=1 THEN 1 ELSE 0 END) AS activos_laboratorio,"
        ."SUM(CASE WHEN p.estado='Activo' AND p.requiere_laboratorio=0 THEN 1 ELSE 0 END) AS activos_clinicos,"
        ."SUM(CASE WHEN p.estado='Activo' AND p.requiere_laboratorio IS NULL THEN 1 ELSE 0 END) AS activos_sin_config,"
        ."SUM(CASE WHEN p.estado<>'Activo' AND COALESCE(p.requiere_laboratorio,c.requiere_laboratorio,0)=1 THEN 1 ELSE 0 END) AS inactivos_habilitados "
        ."FROM producto p INNER JOIN categoria c ON c.cod_categoria=p.cod_categoriaFK "
        ."WHERE c.cod_categoria=91 AND UPPER(TRIM(c.descripcion))='PROTESIS'"
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
        verificarProtesisLabFallar('Argumento no reconocido. Use --ayuda para consultar el uso.');
    }
}
if ($ayuda) {
    fwrite(
        STDOUT,
        "Uso:\n"
        ."  php scripts/verificar_configuracion_protesis_laboratorio.php\n"
        ."  php scripts/verificar_configuracion_protesis_laboratorio.php --aplicar-migracion\n"
    );
    exit(0);
}

$rutaMigracion = dirname(__DIR__).'/actualizacion_21072026_configuracion_protesis_laboratorio.sql';
$rutaCorreccionAlcance = dirname(__DIR__).'/actualizacion_22072026_alcance_laboratorio_por_pieza.sql';
$fuente = file_get_contents($rutaMigracion);
$fuenteCorreccionAlcance = file_get_contents($rutaCorreccionAlcance);
verificarProtesisLabAfirmar(
    $fuente !== false && trim($fuente) !== ''
    && $fuenteCorreccionAlcance !== false && trim($fuenteCorreccionAlcance) !== '',
    'Las migraciones controladas de configuracion y alcance estan disponibles.'
);
$fuenteSinComentarios = preg_replace('/^\s*--.*$/m', '', $fuente."\n".$fuenteCorreccionAlcance);
verificarProtesisLabAfirmar(
    preg_match('/\b(?:DELETE|TRUNCATE|DROP|ALTER)\b/i', $fuenteSinComentarios) !== 1,
    'La migracion no elimina datos ni modifica la estructura.'
);
verificarProtesisLabAfirmar(
    preg_match('/\b(?:accesosuser|listadodeacceso|detalle_venta|venta|cliente|persona)\b/i', $fuenteSinComentarios) !== 1,
    'La migracion no modifica permisos, pacientes, ventas ni tratamientos historicos.'
);

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    verificarProtesisLabFallar('No se pudo conectar con la base local.');
}

foreach (array(
    array('categoria', 'requiere_laboratorio'),
    array('categoria', 'modo_individualizacion'),
    array('producto', 'requiere_laboratorio'),
    array('producto', 'modo_individualizacion')
) as $columna) {
    verificarProtesisLabAfirmar(
        verificarProtesisLabColumnaExiste($mysqli, $columna[0], $columna[1]),
        'Existe '.$columna[0].'.'.$columna[1].'.'
    );
}

verificarProtesisLabAfirmar(
    verificarProtesisLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM categoria WHERE cod_categoria=91 "
        ."AND UPPER(TRIM(descripcion))='PROTESIS'"
    ) === 1,
    'La categoria 91 corresponde univocamente a PROTESIS.'
);

$pieza = array(
    '10220','10147','10075','10129','10130','10083','10035','10156','10182','10145',
    '10079','10080','10081','10131','10217','10133','10135','10225','10132','10014','10134'
);
$arcada = array('10138','10137','10016','10141','10139','10223');
$sector = array('10218','10219');
$dispositivo = array('10136','10144','10140','10189','10113','10154','10146');
$clinicos = array('10172','10085','10086','10084');
$activosEsperados = array_merge($pieza, $arcada, $sector, $dispositivo, $clinicos);
$inactivosEsperados = array('10101','10076','10082','10149','10148','10142','10143','10184','10183');

verificarProtesisLabAfirmar(count(array_unique($activosEsperados)) === 40, 'El mapa contiene 40 productos activos sin duplicados.');
verificarProtesisLabAfirmar(
    verificarProtesisLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM producto WHERE cod_categoriaFK=91 AND estado='Activo' "
        ."AND cod_producto IN (".verificarProtesisLabListaSql($activosEsperados).")"
    ) === 40,
    'Los 40 productos activos esperados siguen en PROTESIS.'
);
verificarProtesisLabAfirmar(
    verificarProtesisLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM producto WHERE cod_categoriaFK=91 AND estado<>'Activo' "
        ."AND cod_producto IN (".verificarProtesisLabListaSql($inactivosEsperados).")"
    ) === 9,
    'Los nueve productos inactivos permanecen identificados como historicos.'
);

$estadoAntes = verificarProtesisLabEstado($mysqli);
verificarProtesisLabInfo(
    'Estado actual: total '.intval($estadoAntes['total'])
    .', activos '.intval($estadoAntes['activos'])
    .', laboratorio '.intval($estadoAntes['activos_laboratorio'])
    .', clinicos '.intval($estadoAntes['activos_clinicos'])
    .', pendientes '.intval($estadoAntes['activos_sin_config']).'.'
);

if ($aplicar) {
    $conflictos = verificarProtesisLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM producto WHERE cod_categoriaFK=91 AND estado='Activo' "
        ."AND cod_producto IN (".verificarProtesisLabListaSql($activosEsperados).") "
        ."AND requiere_laboratorio IS NOT NULL "
        ."AND NOT ("
        ."(cod_producto IN (".verificarProtesisLabListaSql($clinicos).") AND requiere_laboratorio=0) OR "
        ."(cod_producto IN (".verificarProtesisLabListaSql($pieza).") AND requiere_laboratorio=1 AND modo_individualizacion='pieza_individual') OR "
        ."(cod_producto IN (".verificarProtesisLabListaSql($arcada).") AND requiere_laboratorio=1 AND modo_individualizacion='arcada') OR "
        ."(cod_producto IN (".verificarProtesisLabListaSql($sector).") AND requiere_laboratorio=1 AND modo_individualizacion='sector') OR "
        ."(cod_producto IN (".verificarProtesisLabListaSql($dispositivo).") AND requiere_laboratorio=1 AND modo_individualizacion='dispositivo')"
        .")"
    );
    verificarProtesisLabAfirmar($conflictos === 0, 'No existen configuraciones explicitas contradictorias.');
    $firmaAntes = verificarProtesisLabFirmaProtegida($mysqli);
    verificarProtesisLabEjecutarMigracion($mysqli, $rutaMigracion);
    verificarProtesisLabEjecutarMigracion($mysqli, $rutaCorreccionAlcance);
    $firmaPrimera = verificarProtesisLabFirmaProtegida($mysqli);
    verificarProtesisLabAfirmar($firmaAntes === $firmaPrimera, 'La primera ejecucion preservo catalogo base, tratamientos, trabajos y permisos.');
    $estadoPrimera = verificarProtesisLabEstado($mysqli);
    verificarProtesisLabEjecutarMigracion($mysqli, $rutaMigracion);
    verificarProtesisLabEjecutarMigracion($mysqli, $rutaCorreccionAlcance);
    $firmaSegunda = verificarProtesisLabFirmaProtegida($mysqli);
    $estadoSegunda = verificarProtesisLabEstado($mysqli);
    verificarProtesisLabAfirmar($firmaPrimera === $firmaSegunda, 'La segunda ejecucion fue idempotente sobre los datos protegidos.');
    verificarProtesisLabAfirmar($estadoPrimera === $estadoSegunda, 'La configuracion no cambia al repetir la migracion.');
}

$estado = verificarProtesisLabEstado($mysqli);
if (intval($estado['activos_sin_config']) > 0) {
	if ($aplicar) {
		$mysqli->close();
		verificarProtesisLabFallar(
			'La aplicacion termino con productos activos pendientes de configuracion.'
		);
	}
    verificarProtesisLabInfo('La configuracion aun no fue aplicada. Use --aplicar-migracion despues de revisar el SQL.');
    $mysqli->close();
    exit(0);
}

verificarProtesisLabAfirmar(intval($estado['total']) === 49, 'La categoria conserva sus 49 productos.');
verificarProtesisLabAfirmar(intval($estado['activos']) === 40, 'La categoria conserva sus 40 productos activos.');
verificarProtesisLabAfirmar(intval($estado['inactivos']) === 9, 'Los nueve inactivos permanecen fuera de altas nuevas.');
verificarProtesisLabAfirmar(intval($estado['activos_laboratorio']) === 36, 'Treinta y seis productos activos originan trabajos de laboratorio.');
verificarProtesisLabAfirmar(intval($estado['activos_clinicos']) === 4, 'Los cuatro cementados permanecen como actos clinicos.');
verificarProtesisLabAfirmar(intval($estado['inactivos_habilitados']) === 0, 'Ningun producto inactivo habilita trabajos nuevos.');

$modos = verificarProtesisLabFila(
    $mysqli,
    "SELECT "
    ."SUM(CASE WHEN modo_individualizacion='pieza_individual' THEN 1 ELSE 0 END) AS pieza,"
    ."SUM(CASE WHEN modo_individualizacion='arcada' THEN 1 ELSE 0 END) AS arcada,"
    ."SUM(CASE WHEN modo_individualizacion='sector' THEN 1 ELSE 0 END) AS sector,"
    ."SUM(CASE WHEN modo_individualizacion='dispositivo' THEN 1 ELSE 0 END) AS dispositivo "
    ."FROM producto WHERE cod_categoriaFK=91 AND estado='Activo' AND requiere_laboratorio=1"
);
verificarProtesisLabAfirmar(intval($modos['pieza']) === 21, 'Veintiun productos se individualizan por pieza.');
verificarProtesisLabAfirmar(intval($modos['arcada']) === 6, 'Seis productos se individualizan por arcada.');
verificarProtesisLabAfirmar(intval($modos['sector']) === 2, 'Dos productos se individualizan por sector.');
verificarProtesisLabAfirmar(intval($modos['dispositivo']) === 7, 'Siete productos se individualizan como dispositivo.');

verificarProtesisLabAfirmar(
    verificarProtesisLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM producto WHERE cod_categoriaFK=91 AND estado='Activo' "
        ."AND cod_producto IN ('10014','10131','10132','10145','10217','10220') "
        ."AND requiere_laboratorio=1 "
        ."AND modo_individualizacion='pieza_individual' AND alcance_odontologico='pieza_dental'"
    ) === 6,
    'Los seis tratamientos unitarios usan alcance dental coherente con su individualizacion por pieza.'
);
$alcancesContradictorios = verificarProtesisLabFila(
    $mysqli,
    "SELECT COUNT(*) AS cantidad,"
    ."GROUP_CONCAT(CONCAT(cod_producto,' ',nombre_producto) ORDER BY cod_producto SEPARATOR ' | ') AS productos "
    ."FROM producto WHERE cod_categoriaFK=91 AND estado='Activo' "
    ."AND requiere_laboratorio=1 AND modo_individualizacion='pieza_individual' "
    ."AND LOWER(TRIM(COALESCE(alcance_odontologico,'')))='arcada'"
);
if (intval($alcancesContradictorios['cantidad']) > 0) {
    verificarProtesisLabInfo(
        'Productos con pieza_individual y alcance arcada: '.(string)$alcancesContradictorios['productos'].'.'
    );
}
verificarProtesisLabAfirmar(
    intval($alcancesContradictorios['cantidad']) === 0,
    'No quedan productos por pieza con alcance de arcada contradictorio.'
);

$cantidadHistorica = verificarProtesisLabEscalar(
    $mysqli,
    "SELECT COUNT(*) FROM detalle_venta dv INNER JOIN producto p ON p.cod_producto=dv.cod_productoFK "
    ."WHERE p.cod_categoriaFK=91 AND p.requiere_laboratorio=1 AND ABS(dv.cantidad_detalle-1)>0.000001"
);
verificarProtesisLabInfo(
    'Detalles historicos con cantidad distinta de 1 conservados para regularizacion: '.$cantidadHistorica.'.'
);

$mysqli->close();
verificarProtesisLabOk('Configuracion de PROTESIS verificada sin exponer datos clinicos.');

<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$raizPrivada = dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'private'
    .DIRECTORY_SEPARATOR.'GoodVentaAsisCap';
$directorio = $raizPrivada.DIRECTORY_SEPARATOR.'backups'
    .DIRECTORY_SEPARATOR.'central_telefonica_directorio_manual';
if (!is_dir($directorio) && !mkdir($directorio, 0700, true)) {
    fwrite(STDERR, "No se pudo crear el directorio privado de respaldo.\n");
    exit(2);
}
$rutaReal = realpath($directorio);
$raizReal = realpath($raizPrivada);
if ($rutaReal === false || $raizReal === false
    || strpos(strtolower($rutaReal), strtolower($raizReal)) !== 0) {
    fwrite(STDERR, "La ruta de respaldo no pertenece al directorio privado esperado.\n");
    exit(3);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(4);
}
$mysqli->set_charset('utf8mb4');

$salida = array('fecha' => date('c'), 'tablas' => array());
foreach (array('central_telefonica_directorio', 'central_telefonica_directorio_evento') as $tabla) {
    $resultadoExiste = $mysqli->query(
        "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
        ."AND table_name='".$mysqli->real_escape_string($tabla)."'"
    );
    $filaExiste = $resultadoExiste ? $resultadoExiste->fetch_assoc() : array('total' => 0);
    if (intval($filaExiste['total']) !== 1) {
        $salida['tablas'][$tabla] = array('existia' => false, 'create' => '', 'filas' => array());
        continue;
    }
    $create = '';
    $resultadoCreate = $mysqli->query('SHOW CREATE TABLE `'.$tabla.'`');
    if ($resultadoCreate && ($filaCreate = $resultadoCreate->fetch_assoc())) {
        $valoresCreate = array_values($filaCreate);
        $create = isset($valoresCreate[1]) ? (string)$valoresCreate[1] : '';
    }
    $filas = array();
    $resultado = $mysqli->query('SELECT * FROM `'.$tabla.'`');
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $filas[] = $fila;
    }
    $salida['tablas'][$tabla] = array(
        'existia' => true,
        'create' => $create,
        'filas' => $filas
    );
}
$mysqli->close();

$ruta = $directorio.DIRECTORY_SEPARATOR.'pre-migracion-'.date('Ymd-His').'.json';
$json = json_encode($salida, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($ruta, $json, LOCK_EX) === false) {
    fwrite(STDERR, "No se pudo guardar el respaldo privado.\n");
    exit(5);
}
@chmod($ruta, 0600);
fwrite(STDOUT, json_encode(array(
    'ok' => true,
    'ruta' => $ruta,
    'sha256' => hash_file('sha256', $ruta)
), JSON_UNESCAPED_UNICODE).PHP_EOL);

?>

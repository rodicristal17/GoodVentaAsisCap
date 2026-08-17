<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$raizPrivada = dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'private'
    .DIRECTORY_SEPARATOR.'GoodVentaAsisCap';
$directorio = $raizPrivada.DIRECTORY_SEPARATOR.'backups'
    .DIRECTORY_SEPARATOR.'central_telefonica_transcripcion';
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

$salida = array(
    'fecha' => date('c'),
    'codigo_permiso' => 'TRANSCRIBIRLLAMADACENTRALTELEFONICA',
    'catalogo' => array(),
    'niveles' => array(),
    'usuarios' => array(),
    'tablas_transcripcion_existentes' => array()
);
$resultado = $mysqli->query(
    "SELECT * FROM listadodeacceso "
    ."WHERE codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' AND tipo='Administrativo'"
);
while ($resultado && ($fila = $resultado->fetch_assoc())) {
    $salida['catalogo'][] = $fila;
}
if (count($salida['catalogo']) > 0) {
    $id = intval($salida['catalogo'][0]['idlistadodeacceso']);
    $resultado = $mysqli->query(
        'SELECT * FROM detallesniveles WHERE idlistadodeacceso='.$id
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $salida['niveles'][] = $fila;
    }
    $resultado = $mysqli->query(
        "SELECT * FROM accesosuser WHERE idlistadodeaccesoFK=".$id
        ." AND tipo='Administrativo'"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $salida['usuarios'][] = $fila;
    }
}
$resultado = $mysqli->query(
    "SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name IN ('central_telefonica_transcripcion',"
    ."'central_telefonica_transcripcion_evento','central_telefonica_transcripcion_servicio')"
);
while ($resultado && ($fila = $resultado->fetch_assoc())) {
    $salida['tablas_transcripcion_existentes'][] = $fila['table_name'];
}
$mysqli->close();

$ruta = $directorio.DIRECTORY_SEPARATOR.'pre-migracion-'.date('Ymd-His').'.json';
$json = json_encode($salida, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($ruta, $json, LOCK_EX) === false) {
    fwrite(STDERR, "No se pudo guardar el respaldo privado.\n");
    exit(5);
}
@chmod($ruta, 0600);
fwrite(STDOUT, $ruta.PHP_EOL);

?>

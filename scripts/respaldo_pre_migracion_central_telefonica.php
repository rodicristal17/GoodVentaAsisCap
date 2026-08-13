<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$raizPrivada = dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'private'
    .DIRECTORY_SEPARATOR.'GoodVentaAsisCap';
$directorio = $raizPrivada.DIRECTORY_SEPARATOR.'backups'
    .DIRECTORY_SEPARATOR.'central_telefonica';

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
    'tablas' => array(),
    'permisos' => array(),
    'catalogo' => array(),
    'shortcuts' => array()
);
$tablas = array(
    'listadodeacceso', 'detallesniveles', 'accesosuser',
    'dashboard_access_catalog', 'dashboard_user_shortcuts'
);
foreach ($tablas as $tabla) {
    $resultado = $mysqli->query('SELECT COUNT(*) AS total FROM '.$tabla);
    if (!$resultado) {
        fwrite(STDERR, "No se pudo verificar la tabla ".$tabla.".\n");
        exit(5);
    }
    $fila = $resultado->fetch_assoc();
    $salida['tablas'][$tabla] = intval($fila['total']);
}

$resultado = $mysqli->query(
    "SELECT * FROM listadodeacceso WHERE codigo LIKE '%CENTRALTELEFONICA%'"
);
while ($resultado && ($fila = $resultado->fetch_assoc())) {
    $salida['permisos'][] = $fila;
}

$resultado = $mysqli->query(
    "SELECT * FROM dashboard_access_catalog WHERE access_key='central_telefonica'"
);
while ($resultado && ($fila = $resultado->fetch_assoc())) {
    $salida['catalogo'][] = $fila;
}
if (count($salida['catalogo']) > 0) {
    $idCatalogo = intval($salida['catalogo'][0]['id']);
    $resultado = $mysqli->query(
        'SELECT * FROM dashboard_user_shortcuts WHERE access_id='.$idCatalogo
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $salida['shortcuts'][] = $fila;
    }
}

$ruta = $directorio.DIRECTORY_SEPARATOR.'pre-migracion-'.date('Ymd-His').'.json';
if (file_put_contents(
    $ruta,
    json_encode($salida, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
) === false) {
    fwrite(STDERR, "No se pudo guardar el respaldo privado.\n");
    exit(6);
}
$mysqli->close();
fwrite(STDOUT, $ruta.PHP_EOL);

?>

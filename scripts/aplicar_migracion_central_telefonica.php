<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$aplicar = in_array('--apply', $argv, true);
$ruta = dirname(__DIR__).'/actualizacion_12082026_central_telefonica_fase1.sql';
if (!is_file($ruta) || !is_readable($ruta)) {
    fwrite(STDERR, "No se encontro la migracion de Central Telefonica.\n");
    exit(2);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(3);
}

$resultado = $mysqli->query(
    "SELECT COUNT(*) AS total FROM information_schema.tables "
    ."WHERE table_schema=DATABASE() AND table_name IN "
    ."('central_telefonica_cdr_segmento','central_telefonica_llamada',"
    ."'central_telefonica_sincronizacion')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$antes = intval($fila['total']);

if (!$aplicar) {
    fwrite(STDOUT, json_encode(array(
        'ok' => true,
        'codigo' => 'preflight_aprobado',
        'tablas_antes' => $antes,
        'sha256' => hash_file('sha256', $ruta),
        'instruccion' => 'Ejecutar con --apply para aplicar la migracion.'
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit(0);
}

$sql = file_get_contents($ruta);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "La migracion esta vacia o no se pudo leer.\n");
    exit(4);
}

if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "No se pudo iniciar la migracion: ".$mysqli->error."\n");
    exit(5);
}

do {
    if ($resultadoActual = $mysqli->store_result()) {
        while ($resultadoActual->fetch_assoc()) {
            // Consumir resultados de verificacion sin exponer datos en consola.
        }
        $resultadoActual->free();
    }
    if (!$mysqli->more_results()) {
        break;
    }
    if (!$mysqli->next_result()) {
        fwrite(STDERR, "La migracion se interrumpio: ".$mysqli->error."\n");
        exit(6);
    }
} while (true);

$resultado = $mysqli->query(
    "SELECT COUNT(*) AS total FROM information_schema.tables "
    ."WHERE table_schema=DATABASE() AND table_name IN "
    ."('central_telefonica_cdr_segmento','central_telefonica_llamada',"
    ."'central_telefonica_sincronizacion')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$despues = intval($fila['total']);
$resultado = $mysqli->query(
    "SELECT COUNT(*) AS total FROM listadodeacceso "
    ."WHERE codigo LIKE '%CENTRALTELEFONICA%'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$permisos = intval($fila['total']);
$resultado = $mysqli->query(
    "SELECT COUNT(*) AS total FROM dashboard_access_catalog "
    ."WHERE access_key='central_telefonica' AND is_active=1"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$catalogo = intval($fila['total']);
$mysqli->close();

$ok = $despues === 3 && $permisos === 4 && $catalogo === 1;
fwrite(STDOUT, json_encode(array(
    'ok' => $ok,
    'codigo' => $ok ? 'migracion_aplicada' : 'verificacion_incompleta',
    'tablas_antes' => $antes,
    'tablas_despues' => $despues,
    'permisos' => $permisos,
    'catalogo_activo' => $catalogo
), JSON_UNESCAPED_UNICODE).PHP_EOL);
exit($ok ? 0 : 7);

?>

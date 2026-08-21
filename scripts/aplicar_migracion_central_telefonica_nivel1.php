<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_operacion_helper.php';

$aplicar = in_array('--apply', $argv, true);
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Verifica o aplica Central Telefonica Nivel 1.\n");
    fwrite(STDOUT, "Sin opciones: preflight de solo lectura. Opcion: --apply\n");
    exit(0);
}

$ruta = dirname(__DIR__).'/actualizacion_20082026_central_telefonica_nivel1.sql';
if (!is_file($ruta) || !is_readable($ruta)) {
    fwrite(STDERR, "No se encontro la migracion de Central Telefonica Nivel 1.\n");
    exit(2);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(3);
}

$dependencias = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name IN ('central_telefonica_directorio','dashboard_access_catalog',"
    ."'dashboard_user_shortcuts','cliente','persona','usuario')"
);
$filaDependencias = $dependencias ? $dependencias->fetch_assoc() : array('total' => 0);
$preflight = intval($filaDependencias['total']) === 6;

if (!$aplicar) {
    fwrite(STDOUT, json_encode(array(
        'ok' => $preflight,
        'codigo' => $preflight ? 'preflight_aprobado' : 'dependencias_pendientes',
        'dependencias' => intval($filaDependencias['total']),
        'estructura_aplicada' => centralTelefonicaOperacionEstructuraDisponible($mysqli),
        'sha256' => hash_file('sha256', $ruta),
        'instruccion' => 'Crear respaldo dirigido y ejecutar con --apply.'
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit($preflight ? 0 : 4);
}

if (!$preflight) {
    fwrite(STDERR, "Faltan dependencias de Central Telefonica Nivel 1.\n");
    $mysqli->close();
    exit(4);
}

$sql = file_get_contents($ruta);
if ($sql === false || trim($sql) === '' || !$mysqli->multi_query($sql)) {
    fwrite(STDERR, "No se pudo iniciar la migracion de Central Telefonica Nivel 1.\n");
    $mysqli->close();
    exit(5);
}
do {
    if ($resultado = $mysqli->store_result()) {
        while ($resultado->fetch_assoc()) {}
        $resultado->free();
    }
    if (!$mysqli->more_results()) {
        break;
    }
    if (!$mysqli->next_result()) {
        fwrite(STDERR, "La migracion se interrumpio.\n");
        $mysqli->close();
        exit(6);
    }
} while (true);

if (!centralTelefonicaOperacionEstructuraDisponible($mysqli)) {
    fwrite(STDERR, "La estructura operativa no quedo completa.\n");
    $mysqli->close();
    exit(7);
}

$indice = centralTelefonicaOperacionRefrescarTelefonos($mysqli);
$resultadoCatalogo = $mysqli->query(
    "SELECT COUNT(*) total FROM dashboard_access_catalog "
    ."WHERE access_key='central_telefonica' AND IFNULL(permission_key,'')='' AND is_active=1"
);
$filaCatalogo = $resultadoCatalogo
    ? $resultadoCatalogo->fetch_assoc() : array('total' => 0);
$mysqli->close();

$ok = intval($filaCatalogo['total']) === 1;
fwrite(STDOUT, json_encode(array(
    'ok' => $ok,
    'codigo' => $ok ? 'migracion_aplicada' : 'verificacion_incompleta',
    'telefonos_indexados' => intval($indice['telefonos']),
    'pacientes_procesados' => intval($indice['procesados']),
    'acceso_operativo_sin_permiso_adicional' => $ok
), JSON_UNESCAPED_UNICODE).PHP_EOL);
exit($ok ? 0 : 8);

?>

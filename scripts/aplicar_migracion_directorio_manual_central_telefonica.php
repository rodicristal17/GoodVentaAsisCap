<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$aplicar = in_array('--apply', $argv, true);
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Verifica o aplica el directorio manual de Central Telefonica.\n");
    fwrite(STDOUT, "Sin opciones: preflight de solo lectura. Opcion: --apply\n");
    exit(0);
}

$ruta = dirname(__DIR__).'/actualizacion_18082026_directorio_manual_central_telefonica.sql';
if (!is_file($ruta) || !is_readable($ruta)) {
    fwrite(STDERR, "No se encontro la migracion del directorio manual.\n");
    exit(2);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(3);
}

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name='central_telefonica_directorio'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$directorioDisponible = intval($fila['total']) === 1;

if (!$aplicar) {
    fwrite(STDOUT, json_encode(array(
        'ok' => $directorioDisponible,
        'codigo' => $directorioDisponible ? 'preflight_aprobado' : 'directorio_base_pendiente',
        'sha256' => hash_file('sha256', $ruta),
        'instruccion' => 'Crear respaldo dirigido y ejecutar con --apply.'
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit($directorioDisponible ? 0 : 4);
}

if (!$directorioDisponible) {
    fwrite(STDERR, "El directorio base de Central Telefonica no esta aplicado.\n");
    $mysqli->close();
    exit(4);
}

$sql = file_get_contents($ruta);
if ($sql === false || trim($sql) === '' || !$mysqli->multi_query($sql)) {
    fwrite(STDERR, "No se pudo iniciar la migracion del directorio manual.\n");
    $mysqli->close();
    exit(5);
}
do {
    if ($actual = $mysqli->store_result()) {
        while ($actual->fetch_assoc()) {}
        $actual->free();
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

$columna = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema=DATABASE() "
    ."AND table_name='central_telefonica_directorio' AND column_name='cargo_visible'"
);
$filaColumna = $columna ? $columna->fetch_assoc() : array('total' => 0);
$resultado = $mysqli->query(
    "SELECT COUNT(*) total,SUM(TRIM(cargo_visible)<>'') con_cargo "
    ."FROM central_telefonica_directorio WHERE extension IN "
    ."('1000','1001','1002','1003','1004','1005','1006','1007','1009','1010','1011',"
    ."'2000','2002','2003','2100','2101','2102','2200','2201','2202','2300','2301','2302')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0, 'con_cargo' => 0);
$mysqli->close();

$ok = intval($filaColumna['total']) === 1
    && intval($fila['total']) === 23
    && intval($fila['con_cargo']) === 23;
fwrite(STDOUT, json_encode(array(
    'ok' => $ok,
    'codigo' => $ok ? 'migracion_aplicada' : 'verificacion_incompleta',
    'columna_cargo' => intval($filaColumna['total']) === 1,
    'extensiones_precargadas' => intval($fila['total']),
    'cargos_precargados' => intval($fila['con_cargo'])
), JSON_UNESCAPED_UNICODE).PHP_EOL);
exit($ok ? 0 : 7);

?>

<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$aplicar = in_array('--apply', $argv, true);
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Verifica o aplica la migracion del directorio telefonico.\n");
    fwrite(STDOUT, "Sin opciones: preflight de solo lectura. Opcion: --apply\n");
    exit(0);
}
$ruta = dirname(__DIR__).'/actualizacion_18082026_central_telefonica_directorio.sql';
if (!is_file($ruta) || !is_readable($ruta)) {
    fwrite(STDERR, "No se encontro la migracion del directorio telefonico.\n");
    exit(2);
}
$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(3);
}
$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name IN ('central_telefonica_cdr_segmento','central_telefonica_llamada')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$baseDisponible = intval($fila['total']) === 2;
if (!$aplicar) {
    fwrite(STDOUT, json_encode(array(
        'ok' => $baseDisponible,
        'codigo' => $baseDisponible ? 'preflight_aprobado' : 'fase_base_pendiente',
        'sha256' => hash_file('sha256', $ruta),
        'instruccion' => 'Crear respaldo y ejecutar con --apply.'
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit($baseDisponible ? 0 : 4);
}
if (!$baseDisponible) {
    fwrite(STDERR, "La fase base de Central Telefonica no esta aplicada.\n");
    $mysqli->close();
    exit(4);
}
$sql = file_get_contents($ruta);
if ($sql === false || trim($sql) === '' || !$mysqli->multi_query($sql)) {
    fwrite(STDERR, "No se pudo iniciar la migracion.\n");
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
$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.columns WHERE table_schema=DATABASE() "
    ."AND table_name='central_telefonica_llamada' AND column_name IN "
    ."('ruta_extension','ruta_tipo','ruta_nombre','funcionario_extension',"
    ."'funcionario_nombre','funcionario_sede','funcionario_cod_usuario',"
    ."'funcionario_cod_local','funcionario_destino_extension',"
    ."'funcionario_destino_nombre','funcionario_destino_sede',"
    ."'funcionario_destino_cod_usuario','funcionario_destino_cod_local')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$columnas = intval($fila['total']);
$tabla = $mysqli->query("SHOW TABLES LIKE 'central_telefonica_directorio'");
$tablaDisponible = $tabla && $tabla->num_rows === 1;
$mysqli->close();
$ok = $tablaDisponible && $columnas === 13;
fwrite(STDOUT, json_encode(array(
    'ok' => $ok,
    'codigo' => $ok ? 'migracion_aplicada' : 'verificacion_incompleta',
    'tabla_directorio' => $tablaDisponible,
    'columnas_identidad' => $columnas
), JSON_UNESCAPED_UNICODE).PHP_EOL);
exit($ok ? 0 : 7);

?>

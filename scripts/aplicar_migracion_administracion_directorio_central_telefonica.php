<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$aplicar = in_array('--apply', $argv, true);
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Verifica o aplica la administracion del directorio telefonico.\n");
    fwrite(STDOUT, "Sin opciones: preflight de solo lectura. Opcion: --apply\n");
    exit(0);
}
$ruta = dirname(__DIR__).'/actualizacion_18082026_administracion_directorio_central_telefonica.sql';
if (!is_file($ruta) || !is_readable($ruta)) {
    fwrite(STDERR, "No se encontro la migracion de administracion del directorio.\n");
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
        'instruccion' => 'Crear respaldo y ejecutar con --apply.'
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
$tabla = $mysqli->query("SHOW TABLES LIKE 'central_telefonica_directorio_evento'");
$tablaDisponible = $tabla && $tabla->num_rows === 1;
$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM accesosuser au "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
    ."WHERE la.codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA' "
    ."AND au.tipo='Administrativo' AND UPPER(TRIM(au.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$habilitados = intval($fila['total']);
$mysqli->close();
$ok = $tablaDisponible && $habilitados === 1;
fwrite(STDOUT, json_encode(array(
    'ok' => $ok,
    'codigo' => $ok ? 'migracion_aplicada' : 'verificacion_incompleta',
    'tabla_auditoria' => $tablaDisponible,
    'usuarios_habilitados' => $habilitados
), JSON_UNESCAPED_UNICODE).PHP_EOL);
exit($ok ? 0 : 7);

?>

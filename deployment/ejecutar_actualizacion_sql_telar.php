<?php

require_once('/var/www/html/php_system/conexion.php');
$archivo = isset($argv[1]) ? $argv[1] : '';
if ($archivo === '' || !is_file($archivo)) {
    fwrite(STDERR, "Actualizacion SQL no encontrada.\n");
    exit(1);
}
$mysqli = conectar_al_servidor();
$sql = file_get_contents($archivo);
if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "Error SQL: ".$mysqli->error."\n");
    exit(2);
}
do {
    $resultado = $mysqli->store_result();
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            echo json_encode($fila)."\n";
        }
        $resultado->free();
    }
    if (!$mysqli->more_results()) {
        break;
    }
} while ($mysqli->next_result());
if ($mysqli->errno) {
    fwrite(STDERR, "Error SQL: ".$mysqli->error."\n");
    exit(3);
}
$mysqli->close();
echo "SQL_OK\n";


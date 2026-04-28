<?php
require_once("conexion.php");

$listado_procedimientos = ['actualizar_gastos_pendientes', 'reiniciar_tareas_programadas'];

function ejecutarManualmenteEvento($procedimiento) {
    $mysqli = conectar_al_servidor();

    if ($mysqli->connect_errno) {
        return array("1" => "error", "2" => "Error de conexion: " . $mysqli->connect_error);
    }

    $procedimiento = trim((string)$procedimiento);
    if ($procedimiento === "") {
        return array("1" => "error", "2" => "Debe indicar el procedimiento a ejecutar.");
    }

    $sql = "CALL `$procedimiento`();";
    if (!$mysqli->query($sql)) {
        return array("1" => "error", "2" => "Error al ejecutar '$procedimiento': " . $mysqli->error);
    }

    while ($mysqli->more_results() && $mysqli->next_result()) {
        $resultadoExtra = $mysqli->store_result();
        if ($resultadoExtra instanceof mysqli_result) {
            $resultadoExtra->free();
        }
    }

    return array("1" => "exito", "2" => "Procedimiento '$procedimiento' ejecutado correctamente.");
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $salida = "";
    $salida .= "Inicio: " . date("Y-m-d H:i:s") . "\n\n";

    foreach ($listado_procedimientos as $procedimiento) {
        $resultado = ejecutarManualmenteEvento($procedimiento);
        $salida .= "[" . strtoupper($resultado["1"]) . "] " . $resultado["2"] . "\n";
    }

    $salida .= "\nFin: " . date("Y-m-d H:i:s") . "\n";

    $rutaLog = __DIR__ . "/logs";
    if (!is_dir($rutaLog)) {
        mkdir($rutaLog, 0777, true);
    }

    $archivo = $rutaLog . "/eventos_" . date("Ymd_His") . ".log";
    file_put_contents($archivo, $salida, LOCK_EX);

    header("Content-Type: text/plain; charset=utf-8");
    echo $salida;
}
?>

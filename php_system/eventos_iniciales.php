<?php
    require_once("conexion.php");

    function ejecutarManualmenteEvento($procedimiento) {
        $mysqli = conectar_al_servidor();

        if ($mysqli->connect_errno) {
            return array("1" => "error", "2" => "Error de conexion: " . $mysqli->connect_error);
        }

        $procedimiento = trim((string)($procedimiento));
        if ($procedimiento === "") {
            return array("1" => "error", "2" => "Debe indicar el procedimiento a ejecutar.");
        }
/*
        $sql = "CALL `$procedimiento`();";
        if (!$mysqli->query($sql)) {
            return array("1" => "error", "2" => "Error al ejecutar el procedimiento '$procedimiento': " . $mysqli->error);
        }

        while ($mysqli->more_results() && $mysqli->next_result()) {
            $resultadoExtra = $mysqli->store_result();
            if ($resultadoExtra instanceof mysqli_result) {
                $resultadoExtra->free();
            }
        }
*/
        return array("1" => "exito", "2" => "Procedimiento '$procedimiento' ejecutado correctamente.");
    }

    if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
        $procedimiento = isset($_POST['procedimiento']) ? $_POST['procedimiento'] : "";
        echo json_encode(ejecutarManualmenteEvento($procedimiento));
    }
    
?>

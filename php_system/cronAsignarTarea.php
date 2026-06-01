<?php

require("conexion.php");

generarTareasDiariasAutomaticas();

function generarTareasDiariasAutomaticas()
{
    date_default_timezone_set('America/Asuncion');

    $fecha_hoy = date("Y-m-d");
    $numero_dia = date("N");

    $campo_dia = "";

    if ($numero_dia == 1) {
        $campo_dia = "lunes";
    } else if ($numero_dia == 2) {
        $campo_dia = "martes";
    } else if ($numero_dia == 3) {
        $campo_dia = "miercoles";
    } else if ($numero_dia == 4) {
        $campo_dia = "jueves";
    } else if ($numero_dia == 5) {
        $campo_dia = "viernes";
    } else if ($numero_dia == 6) {
        $campo_dia = "sabado";
    } else if ($numero_dia == 7) {
        $campo_dia = "domingo";
    }

    if ($campo_dia == "") {
        $informacion = array(
            "1" => "error",
            "mensaje" => "No se pudo determinar el día."
        );
        echo json_encode($informacion);
        exit;
    }

    $mysqli = conectar_al_servidor();

    if (!$mysqli) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "No se pudo conectar a la base de datos."
        );
        echo json_encode($informacion);
        exit;
    }

    if (method_exists($mysqli, "set_charset")) {
        $mysqli->set_charset("utf8mb4");
    }

    $sql = "SELECT 
                cod_tarea_diaria,
                cod_tareaFK,
                cod_usuarioFK,
                tipo_destino,
                rol_operativoFK,
                observacion_admin
            FROM tareas_programadas_diarias
            WHERE estado = 'Activo'
            AND ".$campo_dia." = 'Si'
            AND (fecha_inicio IS NULL OR fecha_inicio <= '".$fecha_hoy."')
            AND (fecha_fin IS NULL OR fecha_fin >= '".$fecha_hoy."') ";
 

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar generación: " . $mysqli->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    // $ss = "sss";
    // $stmt->bind_param($ss, $fecha_hoy, $fecha_hoy, $fecha_hoy);

    if (!$stmt->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al buscar reglas diarias: " . $stmt->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();

    $generadas = 0;
    $procesadas = 0;

    while ($row = mysqli_fetch_assoc($result)) {

        $procesadas++;

        $cod_tarea_diaria = $row["cod_tarea_diaria"];
        $cod_tareaFK = $row["cod_tareaFK"];
        $cod_usuarioFK = $row["cod_usuarioFK"];
        $tipo_destino = isset($row["tipo_destino"]) ? $row["tipo_destino"] : "USUARIO";
        $rol_operativoFK = isset($row["rol_operativoFK"]) ? $row["rol_operativoFK"] : "";
        $observacion_admin = $row["observacion_admin"];

        $usuariosDestino = array();

        if ($tipo_destino == "ROL") {
            $sqlUsuariosRol = "SELECT cod_usuario
                               FROM usuario
                               WHERE estado = 'Activo'
                               AND tipo = ?
                               ORDER BY cod_usuario ASC";
            $stmtUsuariosRol = $mysqli->prepare($sqlUsuariosRol);

            if (!$stmtUsuariosRol) {
                $informacion = array(
                    "1" => "error",
                    "mensaje" => "Error al preparar usuarios por rol: " . $mysqli->error,
                    "sql" => $sqlUsuariosRol
                );
                echo json_encode($informacion);
                exit;
            }

            $sRol = "s";
            $stmtUsuariosRol->bind_param($sRol, $rol_operativoFK);

            if (!$stmtUsuariosRol->execute()) {
                $informacion = array(
                    "1" => "error",
                    "mensaje" => "Error al buscar usuarios por rol: " . $stmtUsuariosRol->error,
                    "sql" => $sqlUsuariosRol
                );
                echo json_encode($informacion);
                exit;
            }

            $resultUsuariosRol = $stmtUsuariosRol->get_result();

            while ($usuarioRol = mysqli_fetch_assoc($resultUsuariosRol)) {
                $usuariosDestino[] = $usuarioRol["cod_usuario"];
            }

            $stmtUsuariosRol->close();
        } else {
            $usuariosDestino[] = $cod_usuarioFK;
            $rol_operativoFK = "";
        }

        foreach ($usuariosDestino as $cod_usuarioDestino) {
            if ($cod_usuarioDestino == "") {
                continue;
            }

        $sqlVerificar = "SELECT cod_tarea_asignada
                         FROM tareas_programadas_asignadas
                         WHERE cod_tareaFK = ?
                         AND cod_usuarioFK = ?
                         AND fecha_tarea = ?
                         LIMIT 1";

        $stmtVerificar = $mysqli->prepare($sqlVerificar);

        if (!$stmtVerificar) {
            $informacion = array(
                "1" => "error",
                "mensaje" => "Error al preparar verificación: " . $mysqli->error,
                "sql" => $sqlVerificar
            );
            echo json_encode($informacion);
            exit;
        }
		$ss="sss";
        $stmtVerificar->bind_param($ss, $cod_tareaFK, $cod_usuarioDestino, $fecha_hoy);

        if (!$stmtVerificar->execute()) {
            $informacion = array(
                "1" => "error",
                "mensaje" => "Error al verificar tarea: " . $stmtVerificar->error,
                "sql" => $sqlVerificar
            );
            echo json_encode($informacion);
            exit;
        }

        $resultVerificar = $stmtVerificar->get_result();

        if (mysqli_num_rows($resultVerificar) == 0) {

            $estado_tarea = "Pendiente";
            $visto = "No";
            $fecha_insert = date("Y-m-d H:i:s");

            $sqlInsert = "INSERT INTO tareas_programadas_asignadas
                          (
                            cod_tareaFK,
                            cod_usuarioFK,
                            tipo_asignacion,
                            rol_operativoFK,
                            estado_tarea,
                            visto,
                            fecha_tarea,
                            observacion_admin,
                            fecha_insert
                          )
                          VALUES
                          (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtInsert = $mysqli->prepare($sqlInsert);

            if (!$stmtInsert) {
                $informacion = array(
                    "1" => "error",
                    "mensaje" => "Error al preparar inserción: " . $mysqli->error,
                    "sql" => $sqlInsert
                );
                echo json_encode($informacion);
                exit;
            }

            $tipo_asignacion = $tipo_destino == "ROL" ? "ROL" : "USUARIO";
            $sssInsert = "sssssssss";

            $stmtInsert->bind_param(
                $sssInsert,
                $cod_tareaFK,
                $cod_usuarioDestino,
                $tipo_asignacion,
                $rol_operativoFK,
                $estado_tarea,
                $visto,
                $fecha_hoy,
                $observacion_admin,
                $fecha_insert
            );

            if ($stmtInsert->execute()) {
                $generadas++;
            } else {
                $informacion = array(
                    "1" => "error",
                    "mensaje" => "Error al insertar tarea asignada: " . $stmtInsert->error,
                    "sql" => $sqlInsert
                );
                echo json_encode($informacion);
                exit;
            }

            $stmtInsert->close();
        }

        $stmtVerificar->close();
        }

        $sqlUpdate = "UPDATE tareas_programadas_diarias
                      SET ultima_fecha_generada = ?,
                          fecha_update = NOW()
                      WHERE cod_tarea_diaria = ?";

        $stmtUpdate = $mysqli->prepare($sqlUpdate);

        if (!$stmtUpdate) {
            $informacion = array(
                "1" => "error",
                "mensaje" => "Error al preparar actualización: " . $mysqli->error,
                "sql" => $sqlUpdate
            );
            echo json_encode($informacion);
            exit;
        }

        $ssUpdate = "ss";
        $stmtUpdate->bind_param($ssUpdate, $fecha_hoy, $cod_tarea_diaria);

        if (!$stmtUpdate->execute()) {
            $informacion = array(
                "1" => "error",
                "mensaje" => "Error al actualizar regla diaria: " . $stmtUpdate->error,
                "sql" => $sqlUpdate
            );
            echo json_encode($informacion);
            exit;
        }

        $stmtUpdate->close();
    }

    $stmt->close();
    mysqli_close($mysqli);

    $informacion = array(
        "1" => "exito",
        "2" => $generadas,
        "procesadas" => $procesadas,
        "fecha" => $fecha_hoy,
        "dia" => $campo_dia
    );

    echo json_encode($informacion);
    exit;
}

?>

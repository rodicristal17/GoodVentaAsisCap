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
        $informacion = array("1" => "error", "mensaje" => "No se pudo determinar el día.");
        echo json_encode($informacion);
        exit;
    }

    $mysqli = conectar_al_servidor();

    $sql = "SELECT 
                cod_tarea_diaria,
                cod_tareaFK,
                cod_usuarioFK,
                observacion_admin
            FROM tareas_programadas_diarias
            WHERE estado = 'Activo'
            AND ".$campo_dia." = 'Si'
            AND (fecha_inicio IS NULL OR fecha_inicio <= ?)
            AND (fecha_fin IS NULL OR fecha_fin >= ?)
            AND (ultima_fecha_generada IS NULL OR ultima_fecha_generada <> ?)";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar generación: " . $mysqli->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }

    $ss = "sss";
    $stmt->bind_param($ss, $fecha_hoy, $fecha_hoy, $fecha_hoy);

    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al buscar reglas diarias: " . $stmt->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();
    $generadas = 0;

    while ($row = mysqli_fetch_assoc($result)) {

        $cod_tarea_diaria = $row["cod_tarea_diaria"];
        $cod_tareaFK = $row["cod_tareaFK"];
        $cod_usuarioFK = $row["cod_usuarioFK"];
        $observacion_admin = $row["observacion_admin"];

        $sqlVerificar = "SELECT cod_tarea_asignada
                         FROM tareas_programadas_asignadas
                         WHERE cod_tareaFK = ?
                         AND cod_usuarioFK = ?
                         AND fecha_tarea = ?
                         LIMIT 1";

        $stmtVerificar = $mysqli->prepare($sqlVerificar);
        $stmtVerificar->bind_param($ss, $cod_tareaFK, $cod_usuarioFK, $fecha_hoy);
        $stmtVerificar->execute();
        $resultVerificar = $stmtVerificar->get_result();

        if (mysqli_num_rows($resultVerificar) == 0) {

            $estado_tarea = "Pendiente";
            $visto = "No";
            $fecha_insert = date("Y-m-d H:i:s");

            $sqlInsert = "INSERT INTO tareas_programadas_asignadas
                          (
                            cod_tareaFK,
                            cod_usuarioFK,
                            estado_tarea,
                            visto,
                            fecha_tarea,
                            observacion_admin,
                            fecha_insert
                          )
                          VALUES
                          (?, ?, ?, ?, ?, ?, ?)";

            $stmtInsert = $mysqli->prepare($sqlInsert);
            $sssInsert = "sssssss";

            $stmtInsert->bind_param(
                $sssInsert,
                $cod_tareaFK,
                $cod_usuarioFK,
                $estado_tarea,
                $visto,
                $fecha_hoy,
                $observacion_admin,
                $fecha_insert
            );

            if ($stmtInsert->execute()) {
                $generadas++;
            }

            $stmt,
                $fecha_insert
            );

            if ($stmtInsert->execute()) {
                $generadas++;
            }

            $stmtInsert->close();
        }

        $stmtVerificar->close();

        $sqlUpdate = "UPDATE tareas_programadas_diarias
                      SET ultima_fecha_generada = ?,
                          fecha_update = NOW()
                      WHERE cod_tarea_diaria = ?";

        $stmtUpdate = $mysqli->prepare($sqlUpdate);
        $ssUpdate = "ss";
        $stmtUpdate->bind_param($ssUpdate, $fecha_hoy, $cod_tarea_diaria);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    $stmt->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "2" => $generadas);
    echo json_encode($informacion);
    exit;
}
<?php

$operacion = isset($_POST['funt']) ? $_POST['funt'] : '';
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

date_default_timezone_set('America/Asuncion');

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include("classTable.php");

function convertir_post_tarea_programada($nombreCampo, $valorDefault = '')
{
    $valor = isset($_POST[$nombreCampo]) ? $_POST[$nombreCampo] : $valorDefault;
    $valor = mb_convert_encoding((string)($valor), 'ISO-8859-1', 'UTF-8');
    return trim($valor);
}

function convertir_utf8_tarea_programada($valor)
{
    if ($valor === null) {
        return '';
    }

    return mb_convert_encoding((string)($valor), 'UTF-8', 'ISO-8859-1');
}

function limpiar_html_tarea_programada($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function validar_hora_tarea_programada($hora)
{
    $hora = trim((string)$hora);

    if ($hora == '') {
        return '';
    }

    if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
        return $hora . ':00';
    }

    if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $hora)) {
        return $hora;
    }

    return '';
}

function validar_estado_tarea_programada($estado)
{
    $estado = strtolower(trim((string)$estado));

    if ($estado != 'pendiente' && $estado != 'completada' && $estado != 'inactivo') {
        $estado = 'pendiente';
    }

    return $estado;
}

function bind_param_tarea_programada($stmt, $tipos, $parametros)
{
    $refs = array();

    foreach ($parametros as $k => $v) {
        $refs[$k] = &$parametros[$k];
    }

    return call_user_func_array(array($stmt, 'bind_param'), array_merge(array($tipos), $refs));
}

function verificar($operacion)
{
    $user = isset($_POST['useru']) ? $_POST['useru'] : '';
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

    if ($user != '') {

        $pass = isset($_POST['passu']) ? $_POST['passu'] : '';
        $pass = str_replace('=', '+', $pass);

        $navegador = isset($_POST['navegador']) ? $_POST['navegador'] : '';
        $navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');

        $resp = verificar_navegador($user, $navegador, $pass);

        if ($resp != 'ok' && $operacion != 'buscaroption') {
            $informacion = array('1' => 'UI');
            echo json_encode($informacion);
            exit;
        }
    }

    if ($operacion == 'nuevo' || $operacion == 'editar') {

        $id = convertir_post_tarea_programada('id');
        $nombre = convertir_post_tarea_programada('nombre');
        $hora = convertir_post_tarea_programada('hora');
        $tipo = convertir_post_tarea_programada('tipo');
        $estado = convertir_post_tarea_programada('estado', 'pendiente');
        $cod_usuarioFK = convertir_post_tarea_programada('cod_usuarioFK');
        $fecha_realizado = convertir_post_tarea_programada('fecha_realizado');

        abm($id, $nombre, $hora, $estado, $fecha_realizado, $cod_usuarioFK, $tipo, $operacion);
    }

    if ($operacion == 'buscar') {

        $codigo = convertir_post_tarea_programada('codigo');
        $nombre = convertir_post_tarea_programada('nombre');
        $hora = convertir_post_tarea_programada('hora');
        $tipo = convertir_post_tarea_programada('tipo');

        buscar($codigo, $nombre, $hora, $tipo);
    }

    if ($operacion == 'buscaroption') {
        buscaroption();
    }
	
	
	
	
	if($operacion=="buscarUsuariosAsignarTarea")
{
    $buscar = isset($_POST['buscar']) ? $_POST['buscar'] : "";
    $buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "";
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

    $estado = isset($_POST['estado']) ? $_POST['estado'] : "";
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

    buscarUsuariosAsignarTarea($buscar, $tipo, $estado);
}



if($operacion=="buscarTareasParaAsignarUsuario")
{
    $buscar = isset($_POST['buscar']) ? $_POST['buscar'] : "";
    $buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "";
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

    $estado = isset($_POST['estado']) ? $_POST['estado'] : "";
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

    $cod_usuario = isset($_POST['cod_usuario']) ? $_POST['cod_usuario'] : "";
    $cod_usuario = mb_convert_encoding((string)($cod_usuario), 'ISO-8859-1', 'UTF-8');

    buscarTareasParaAsignarUsuario($buscar, $tipo, $estado, $cod_usuario);
}

if($operacion=="asignarTareaAUsuario")
{
    $id_tarea = isset($_POST['id_tarea']) ? $_POST['id_tarea'] : "";
    $id_tarea = mb_convert_encoding((string)($id_tarea), 'ISO-8859-1', 'UTF-8');

    $cod_usuario = isset($_POST['cod_usuario']) ? $_POST['cod_usuario'] : "";
    $cod_usuario = mb_convert_encoding((string)($cod_usuario), 'ISO-8859-1', 'UTF-8');

    $fecha_tarea = isset($_POST['fecha_tarea']) ? $_POST['fecha_tarea'] : "";
    $fecha_tarea = mb_convert_encoding((string)($fecha_tarea), 'ISO-8859-1', 'UTF-8');

    asignarTareaAUsuario($id_tarea, $cod_usuario, $fecha_tarea);
}
	
	if($operacion=="buscarTareasPendientesAdministrador")
{
    $cod_usuario = isset($_POST['cod_usuario']) ? $_POST['cod_usuario'] : "";
    $cod_usuario = mb_convert_encoding((string)($cod_usuario), 'ISO-8859-1', 'UTF-8');

    buscarTareasPendientesAdministrador($cod_usuario);
}


if($operacion=="cambiarEstadoTareaAsignada")
{
    $cod_tarea_asignada = isset($_POST['cod_tarea_asignada']) ? $_POST['cod_tarea_asignada'] : "";
    $cod_tarea_asignada = mb_convert_encoding((string)($cod_tarea_asignada), 'ISO-8859-1', 'UTF-8');

    $estado_tarea = isset($_POST['estado_tarea']) ? $_POST['estado_tarea'] : "";
    $estado_tarea = mb_convert_encoding((string)($estado_tarea), 'ISO-8859-1', 'UTF-8');

    $cod_usuario = isset($_POST['useru']) ? $_POST['useru'] : "";
    $cod_usuario = mb_convert_encoding((string)($cod_usuario), 'ISO-8859-1', 'UTF-8');

    cambiarEstadoTareaAsignada($cod_tarea_asignada, $estado_tarea, $cod_usuario);
}


if($operacion=="buscarTareasParaAsignarDiariaUsuario")
{
    $buscar = isset($_POST['buscar']) ? $_POST['buscar'] : "";
    $buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "";
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

    $cod_usuario = isset($_POST['cod_usuario']) ? $_POST['cod_usuario'] : "";
    $cod_usuario = mb_convert_encoding((string)($cod_usuario), 'ISO-8859-1', 'UTF-8');

    buscarTareasParaAsignarDiariaUsuario($buscar, $tipo, $cod_usuario);
}

if($operacion=="guardarTareaDiariaUsuario")
{
    $cod_tareaFK = isset($_POST['cod_tareaFK']) ? $_POST['cod_tareaFK'] : "";
    $cod_tareaFK = mb_convert_encoding((string)($cod_tareaFK), 'ISO-8859-1', 'UTF-8');

    $cod_usuarioFK = isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');

    $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : "";
    $fecha_inicio = mb_convert_encoding((string)($fecha_inicio), 'ISO-8859-1', 'UTF-8');

    $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : "";
    $fecha_fin = mb_convert_encoding((string)($fecha_fin), 'ISO-8859-1', 'UTF-8');

    $lunes = isset($_POST['lunes']) ? $_POST['lunes'] : "No";
    $lunes = mb_convert_encoding((string)($lunes), 'ISO-8859-1', 'UTF-8');

    $martes = isset($_POST['martes']) ? $_POST['martes'] : "No";
    $martes = mb_convert_encoding((string)($martes), 'ISO-8859-1', 'UTF-8');

    $miercoles = isset($_POST['miercoles']) ? $_POST['miercoles'] : "No";
    $miercoles = mb_convert_encoding((string)($miercoles), 'ISO-8859-1', 'UTF-8');

    $jueves = isset($_POST['jueves']) ? $_POST['jueves'] : "No";
    $jueves = mb_convert_encoding((string)($jueves), 'ISO-8859-1', 'UTF-8');

    $viernes = isset($_POST['viernes']) ? $_POST['viernes'] : "No";
    $viernes = mb_convert_encoding((string)($viernes), 'ISO-8859-1', 'UTF-8');

    $sabado = isset($_POST['sabado']) ? $_POST['sabado'] : "No";
    $sabado = mb_convert_encoding((string)($sabado), 'ISO-8859-1', 'UTF-8');

    $domingo = isset($_POST['domingo']) ? $_POST['domingo'] : "No";
    $domingo = mb_convert_encoding((string)($domingo), 'ISO-8859-1', 'UTF-8');

    $observacion_admin = isset($_POST['observacion_admin']) ? $_POST['observacion_admin'] : "";
    $observacion_admin = mb_convert_encoding((string)($observacion_admin), 'ISO-8859-1', 'UTF-8');

    $cod_usuarioFK_create = isset($_POST['useru']) ? $_POST['useru'] : "";
    $cod_usuarioFK_create = mb_convert_encoding((string)($cod_usuarioFK_create), 'ISO-8859-1', 'UTF-8');

    guardarTareaDiariaUsuario(
        $cod_tareaFK,
        $cod_usuarioFK,
        $fecha_inicio,
        $fecha_fin,
        $lunes,
        $martes,
        $miercoles,
        $jueves,
        $viernes,
        $sabado,
        $domingo,
        $observacion_admin,
        $cod_usuarioFK_create
    );
}
	
}

function buscarTareasParaAsignarDiariaUsuario($buscar, $tipo, $cod_usuario)
{
    $mysqli = conectar_al_servidor();

    $buscar = mysqli_real_escape_string($mysqli, $buscar);
    $tipo = mysqli_real_escape_string($mysqli, $tipo);
    $cod_usuario = mysqli_real_escape_string($mysqli, $cod_usuario);

    $condicionBuscar = "";
    if ($buscar != "") {
        $condicionBuscar = " AND tp.nombre LIKE '%".$buscar."%'";
    }

    $condicionTipo = "";
    if ($tipo != "") {
        $condicionTipo = " AND tp.tipo = '".$tipo."'";
    }

    $sql = "SELECT 
                tp.id,
                tp.nombre,
                TIME_FORMAT(tp.hora, '%H:%i') AS hora_format,
                tp.tipo,
                tpd.cod_tarea_diaria
            FROM tareas_programadas tp
            LEFT JOIN tareas_programadas_diarias tpd
                ON tpd.cod_tareaFK = tp.id
                AND tpd.cod_usuarioFK = '".$cod_usuario."'
                AND tpd.estado = 'Activo'
            WHERE 1=1
            ".$condicionBuscar."
            ".$condicionTipo."
            ORDER BY 
                CASE 
                    WHEN tpd.cod_tarea_diaria IS NOT NULL THEN 1
                    ELSE 0
                END ASC,
                tp.hora ASC,
                tp.nombre ASC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar búsqueda diaria: " . $mysqli->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    if (!$stmt->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al buscar tareas diarias: " . $stmt->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();
    $nroRegistro = mysqli_num_rows($result);
    $pagina = "";

    if ($nroRegistro > 0) {

        $pagina .= "<div class='tarea-diaria-modal__grid'>";

        while ($valor = mysqli_fetch_assoc($result)) {

            $id = $valor['id'];
            $nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
            $hora = mb_convert_encoding((string)($valor['hora_format']), 'UTF-8', 'ISO-8859-1');
            $tipoTarea = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
            $cod_tarea_diaria = isset($valor['cod_tarea_diaria']) ? $valor['cod_tarea_diaria'] : "";

            $id_html = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
            $nombre_html = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
            $hora_html = htmlspecialchars($hora, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipoTarea, ENT_QUOTES, 'UTF-8');

            $claseCard = "tarea-diaria-modal__card";
            $onclick = "onclick='seleccionarTareaDiariaParaAsignar(this)'";
            $textoEstado = "Disponible";

            if ($cod_tarea_diaria != "" && $cod_tarea_diaria != NULL) {
                $claseCard .= " tarea-diaria-modal__card--asignada";
                $onclick = "";
                $textoEstado = "Ya configurada";
            }

            $pagina .= "
            <div 
                class='".$claseCard."'
                data-id='".$id_html."'
                data-nombre='".$nombre_html."'
                ".$onclick.">

                <p class='tarea-diaria-modal__card-nombre'>".$nombre_html."</p>
                <p class='tarea-diaria-modal__card-hora'>Hora: ".$hora_html."</p>

                <span class='tarea-diaria-modal__badge'>".$tipo_html." - ".$textoEstado."</span>

            </div>";
        }

        $pagina .= "</div>";

    } else {

        $pagina .= "
        <div class='tarea-diaria-modal__vacio'>
            <p>No se encontraron tareas para configurar.</p>
        </div>";
    }

    $stmt->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "2" => $pagina, "3" => $nroRegistro);
    echo json_encode($informacion);
    exit;
}

function guardarTareaDiariaUsuario(
    $cod_tareaFK,
    $cod_usuarioFK,
    $fecha_inicio,
    $fecha_fin,
    $lunes,
    $martes,
    $miercoles,
    $jueves,
    $viernes,
    $sabado,
    $domingo,
    $observacion_admin,
    $cod_usuarioFK_create
) {
    if ($cod_tareaFK == "" || $cod_usuarioFK == "" || $fecha_inicio == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if (
        $lunes == "No" &&
        $martes == "No" &&
        $miercoles == "No" &&
        $jueves == "No" &&
        $viernes == "No" &&
        $sabado == "No" &&
        $domingo == "No"
    ) {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($fecha_fin == "") {
        $fecha_fin = NULL;
    }

    $estado = "Activo";
    $fecha_insert = date("Y-m-d H:i:s");

    $mysqli = conectar_al_servidor();

    $consultaVerificar = "SELECT cod_tarea_diaria 
                          FROM tareas_programadas_diarias
                          WHERE cod_tareaFK = ?
                          AND cod_usuarioFK = ?
                          AND estado = 'Activo'
                          LIMIT 1";

    $stmtVerificar = $mysqli->prepare($consultaVerificar);

    if (!$stmtVerificar) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar verificación: " . $mysqli->error,
            "sql" => $consultaVerificar
        );
        echo json_encode($informacion);
        exit;
    }

    $ss = "ss";
    $stmtVerificar->bind_param($ss, $cod_tareaFK, $cod_usuarioFK);

    if (!$stmtVerificar->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al verificar tarea diaria: " . $stmtVerificar->error,
            "sql" => $consultaVerificar
        );
        echo json_encode($informacion);
        exit;
    }

    $resultVerificar = $stmtVerificar->get_result();

    if (mysqli_num_rows($resultVerificar) > 0) {
        $stmtVerificar->close();
        mysqli_close($mysqli);

        $informacion = array(
            "1" => "duplicado",
            "mensaje" => "Esta tarea diaria ya está configurada para este usuario."
        );
        echo json_encode($informacion);
        exit;
    }

    $stmtVerificar->close();

    $consulta1 = "INSERT INTO tareas_programadas_diarias
                  (
                    cod_tareaFK,
                    cod_usuarioFK,
                    estado,
                    fecha_inicio,
                    fecha_fin,
                    lunes,
                    martes,
                    miercoles,
                    jueves,
                    viernes,
                    sabado,
                    domingo,
                    observacion_admin,
                    ultima_fecha_generada,
                    cod_usuarioFK_create,
                    fecha_insert,
                    fecha_update
                  )
                  VALUES
                  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, NULL)";

    $stmt1 = $mysqli->prepare($consulta1);

    if (!$stmt1) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar guardado: " . $mysqli->error,
            "sql" => $consulta1
        );
        echo json_encode($informacion);
        exit;
    }

    $ss = "sssssssssssssss";

    $stmt1->bind_param(
        $ss,
        $cod_tareaFK,
        $cod_usuarioFK,
        $estado,
        $fecha_inicio,
        $fecha_fin,
        $lunes,
        $martes,
        $miercoles,
        $jueves,
        $viernes,
        $sabado,
        $domingo,
        $observacion_admin,
        $cod_usuarioFK_create,
        $fecha_insert
    );

    if (!$stmt1->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al guardar tarea diaria: " . $stmt1->error,
            "sql" => $consulta1
        );
        echo json_encode($informacion);
        exit;
    }

    $stmt1->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito");
    echo json_encode($informacion);
    exit;
}

function cambiarEstadoTareaAsignada($cod_tarea_asignada, $estado_tarea, $cod_usuario)
{
    if ($cod_tarea_asignada == "" || $estado_tarea == "" || $cod_usuario == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if (
        $estado_tarea != "Pendiente" &&
        $estado_tarea != "En Proceso" &&
        $estado_tarea != "Completada" &&
        $estado_tarea != "Cancelada"
    ) {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }
	
	 date_default_timezone_set('America/Asuncion');
	 
	 $fecha_insert = date("Y-m-d H:i:s");

    $mysqli = conectar_al_servidor();

    if ($estado_tarea == "Completada") {

        $consulta1 = "UPDATE tareas_programadas_asignadas 
                      SET estado_tarea = ?,
                          fecha_completada = '".$fecha_insert."',
                          fecha_update = NOW()
                      WHERE cod_tarea_asignada = ?
                      AND cod_usuarioFK = ?";

    } else {

        $consulta1 = "UPDATE tareas_programadas_asignadas 
                      SET estado_tarea = ?,
                          fecha_completada = '".$fecha_insert."',
                          fecha_update = NOW()
                      WHERE cod_tarea_asignada = ?
                      AND cod_usuarioFK = ?";
    }

    $stmt1 = $mysqli->prepare($consulta1);

    if (!$stmt1) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar cambio de estado: " . $mysqli->error,
            "sql" => $consulta1
        );
        echo json_encode($informacion);
        exit;
    }

    $ss = "sss";
    $stmt1->bind_param($ss, $estado_tarea, $cod_tarea_asignada, $cod_usuario);

    if (!$stmt1->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al cambiar estado: " . $stmt1->error,
            "sql" => $consulta1
        );
        echo json_encode($informacion);
        exit;
    }

    if ($stmt1->affected_rows <= 0) {
        $stmt1->close();
        mysqli_close($mysqli);

        $informacion = array(
            "1" => "error",
            "mensaje" => "No se pudo actualizar la tarea. Verifique que pertenezca al usuario."
        );
        echo json_encode($informacion);
        exit;
    }

    $stmt1->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito");
    echo json_encode($informacion);
    exit;
}


function buscarTareasPendientesAdministrador($cod_usuario)
{
    if ($cod_usuario == "") {
        $informacion = array("1" => "camposvacio", "2" => "", "3" => 0, "4" => 0, "5" => 0, "6" => 0, "7" => 0, "8" => 0);
        echo json_encode($informacion);
        exit;
    }
	 
    $mysqli = conectar_al_servidor();

    $sql = "SELECT 
                tpa.cod_tarea_asignada,
                tpa.cod_tareaFK,
                tpa.cod_usuarioFK,
                tpa.estado_tarea,
                tpa.visto,
                tpa.fecha_visto,
                tpa.observacion_admin,
                tpa.observacion_usuario,
                tpa.fecha_completada,
                DATE_FORMAT(tpa.fecha_completada, '%d/%m/%Y %H:%i') AS fecha_completada_format,
                DATE_FORMAT(tpa.fecha_insert, '%d/%m/%Y %H:%i') AS fecha_insert_format,
                tp.nombre,
                TIME_FORMAT(tp.hora, '%H:%i') AS hora_format,
                tp.tipo
            FROM tareas_programadas_asignadas tpa
            INNER JOIN tareas_programadas tp 
                ON tp.id = tpa.cod_tareaFK
            WHERE tpa.cod_usuarioFK = ? AND fecha_tarea = ?
            ORDER BY 
                CASE WHEN tp.hora IS NULL THEN 1 ELSE 0 END,
                tp.hora ASC,
                CASE 
                    WHEN tpa.estado_tarea = 'Pendiente' THEN 1
                    WHEN tpa.estado_tarea = 'En Proceso' THEN 2
                    WHEN tpa.estado_tarea = 'Completada' THEN 3
                    WHEN tpa.estado_tarea = 'Cancelada' THEN 4
                    ELSE 5
                END,
                tpa.fecha_insert DESC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar búsqueda de tareas: " . $mysqli->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }
	
	date_default_timezone_set('America/Asuncion');
	$fecha_actual = date("Y-m-d");
    $momento_actual = time();
 
    $ss = "ss";
    $stmt->bind_param($ss, $cod_usuario,$fecha_actual);

    if (!$stmt->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al buscar tareas: " . $stmt->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();

    $nroRegistro = mysqli_num_rows($result);
    $totalPendientes = 0;
    $totalProceso = 0;
    $totalCompletadas = 0;
    $totalCanceladas = 0;
    $totalAtrasadas = 0;
    $pagina = "";

    if ($nroRegistro > 0) {

        while ($valor = mysqli_fetch_assoc($result)) {

            $cod_tarea_asignada = $valor['cod_tarea_asignada'];

            $nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
            $hora = mb_convert_encoding((string)($valor['hora_format']), 'UTF-8', 'ISO-8859-1');
            $tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
            $estado_tarea = mb_convert_encoding((string)($valor['estado_tarea']), 'UTF-8', 'ISO-8859-1');
            $visto = mb_convert_encoding((string)($valor['visto']), 'UTF-8', 'ISO-8859-1');
            $observacion_admin = mb_convert_encoding((string)($valor['observacion_admin']), 'UTF-8', 'ISO-8859-1');
            $fecha_insert = mb_convert_encoding((string)($valor['fecha_insert_format']), 'UTF-8', 'ISO-8859-1');
            $fecha_completada = mb_convert_encoding((string)($valor['fecha_completada_format']), 'UTF-8', 'ISO-8859-1');

            $nombre_html = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
            $hora_html = htmlspecialchars($hora, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
            $observacion_admin_html = htmlspecialchars($observacion_admin, ENT_QUOTES, 'UTF-8');
            $fecha_insert_html = htmlspecialchars($fecha_insert, ENT_QUOTES, 'UTF-8');
            $fecha_completada_html = htmlspecialchars($fecha_completada, ENT_QUOTES, 'UTF-8');

            if ($hora_html == "") {
                $hora_html = "--:--";
            }

            $momento_tarea = false;
            if ($hora != "") {
                $momento_tarea = strtotime($fecha_actual." ".$hora.":00");
            }

            $estaAtrasada = ($estado_tarea == "Pendiente" && $momento_tarea !== false && $momento_tarea < $momento_actual);

            $claseEstado = "perfil-tareas__estado--pendiente";
            $textoEstado = "Pendiente";

            $claseItem = "perfil-tareas__item";

            if ($visto == "No") {
                $claseItem .= " perfil-tareas__item--nuevo";
            }

            if ($estado_tarea == "Pendiente") {
                $totalPendientes++;
                $claseEstado = "perfil-tareas__estado--pendiente";
                $textoEstado = "Pendiente";
            }

            if ($estado_tarea == "En Proceso") {
                $totalProceso++;
                $claseEstado = "perfil-tareas__estado--proceso";
                $textoEstado = "En proceso";
            }

            if ($estado_tarea == "Completada") {
                $totalCompletadas++;
                $claseEstado = "perfil-tareas__estado--completada";
                $textoEstado = "Completada";
                $claseItem .= " perfil-tareas__item--completada";
            }

            if ($estado_tarea == "Cancelada") {
                $totalCanceladas++;
                $claseEstado = "perfil-tareas__estado--cancelada";
                $textoEstado = "Cancelada";
                $claseItem .= " perfil-tareas__item--cancelada";
            }

            if ($estaAtrasada == true) {
                $totalAtrasadas++;
                $claseEstado = "perfil-tareas__estado--atrasada";
                $textoEstado = "Atrasada";
                $claseItem .= " perfil-tareas__item--atrasada";
            }

            $checkHtml = "";

            /*
                Solo las tareas Pendientes se pueden completar.
                Las Completadas salen checked y disabled.
                Las demás salen disabled.
            */
            if ($estado_tarea == "Pendiente") {

                $checkHtml = "
                <input 
                    type='checkbox' 
                    class='perfil-tareas__check' 
                    title='Marcar como completada'
                    aria-label='Marcar tarea como completada'
                    onclick='cambiarEstadoTareaAsignada(this, ".$cod_tarea_asignada.")' 
                />";

            } else if ($estado_tarea == "Completada") {

                $checkHtml = "
                <input 
                    type='checkbox' 
                    class='perfil-tareas__check' 
                    title='Tarea completada'
                    aria-label='Tarea completada'
                    checked 
                    disabled 
                />";

            } else {

                $checkHtml = "
                <input 
                    type='checkbox' 
                    class='perfil-tareas__check' 
                    title='Estado no editable'
                    aria-label='Estado no editable'
                    disabled 
                />";
            }

            $pagina .= "
            <div class='".$claseItem."' data-id='".$cod_tarea_asignada."'>

                <div class='perfil-tareas__hora'>
                    <span>".$hora_html."</span>
                    <small>Hora</small>
                </div>

                <div class='perfil-tareas__linea'></div>

                <div class='perfil-tareas__contenido'>

                    <div class='perfil-tareas__item-header'>
                        <p class='perfil-tareas__descripcion'>".$nombre_html."</p>
                        <span class='perfil-tareas__estado ".$claseEstado."'>
                            ".$textoEstado."
                        </span>
                    </div>

                    <p class='perfil-tareas__meta'>
                        <span>Tipo: ".$tipo_html."</span>
                        <span>Asignada: ".$fecha_insert_html."</span>
                    </p>";

                    if ($fecha_completada_html != "" && $estado_tarea == "Completada") {
                        $pagina .= "
                        <p class='perfil-tareas__meta perfil-tareas__meta--completada'>
                            Completada: ".$fecha_completada_html."
                        </p>";
                    }

                    if ($observacion_admin_html != "") {
                        $pagina .= "
                        <p class='perfil-tareas__observacion'>
                            ".$observacion_admin_html."
                        </p>";
                    }

            $pagina .= "
                </div>

                <div class='perfil-tareas__check-wrap'>
                    ".$checkHtml."
                </div>

            </div>";
        }
    }

    /*
        Marcamos como visto después de listar.
    */
    if ($nroRegistro > 0) {

        $sqlUpdate = "UPDATE tareas_programadas_asignadas
                      SET visto = 'Si',
                          fecha_visto = IF(fecha_visto IS NULL, NOW(), fecha_visto)
                      WHERE cod_usuarioFK = ?
                      AND visto = 'No'";

        $stmtUpdate = $mysqli->prepare($sqlUpdate);
		$s="s";
        if ($stmtUpdate) {
            $stmtUpdate->bind_param($s, $cod_usuario);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }

    $stmt->close();
    mysqli_close($mysqli);

    /*
        3 = cantidad pendiente para el contador
        4 = total de tareas listadas
        5 = total en proceso
        6 = total completadas
        7 = total canceladas
        8 = total atrasadas
    */
    $informacion = array(
        "1" => "exito",
        "2" => $pagina,
        "3" => $totalPendientes,
        "4" => $nroRegistro,
        "5" => $totalProceso,
        "6" => $totalCompletadas,
        "7" => $totalCanceladas,
        "8" => $totalAtrasadas
    );

    echo json_encode($informacion);
    exit;
}


function buscarTareasParaAsignarUsuario($buscar, $tipo, $estado, $cod_usuario)
{
    $mysqli = conectar_al_servidor();

    $buscar = mysqli_real_escape_string($mysqli, $buscar);
    $tipo = mysqli_real_escape_string($mysqli, $tipo);
    $estado = mysqli_real_escape_string($mysqli, $estado);
    $cod_usuario = mysqli_real_escape_string($mysqli, $cod_usuario);

    $condicionBuscar = "";
    if ($buscar != "") {
        $condicionBuscar = " AND tp.nombre LIKE '%".$buscar."%'";
    }

    $condicionTipo = "";
    if ($tipo != "") {
        $condicionTipo = " AND tp.tipo = '".$tipo."'";
    }

    /*
        Este estado corresponde a tareas_programadas.estado:
        pendiente, completada, inactivo

        Si tu filtro del modal usa:
        Pendiente, En Proceso, Completada, Cancelada
        entonces más abajo te dejo una variante.
    */
    $condicionEstado = "";
    if ($estado != "") {
        $condicionEstado = " AND tp.estado = '".$estado."'";
    }

    /*
        Trae todas las tareas base de tareas_programadas
        y revisa en tareas_programadas_asignadas si el usuario ya tiene esa tarea asignada.

        La asignación activa es:
        Pendiente o En Proceso

        Si ya está Completada o Cancelada, se permite volver a asignar.
    */
    $sql = "SELECT 
                tp.id,
                tp.nombre,
                TIME_FORMAT(tp.hora, '%H:%i') AS hora_format,
                tp.estado,
                tp.tipo,

                tpa.cod_tarea_asignada,
                tpa.estado_tarea,
                tpa.visto,
                DATE_FORMAT(tpa.fecha_insert, '%d/%m/%Y %H:%i') AS fecha_asignada_format

            FROM tareas_programadas tp

            LEFT JOIN tareas_programadas_asignadas tpa
                ON tpa.cod_tareaFK = tp.id
                AND tpa.cod_usuarioFK = '".$cod_usuario."'
                AND tpa.estado_tarea IN ('Pendiente','En Proceso')

            WHERE 1=1
            ".$condicionBuscar."
            ".$condicionTipo."
            ".$condicionEstado."

            ORDER BY 
                CASE 
                    WHEN tpa.cod_tarea_asignada IS NOT NULL THEN 1
                    ELSE 0
                END ASC,
                tp.hora ASC,
                tp.nombre ASC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar búsqueda: " . $mysqli->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    if (!$stmt->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al buscar tareas: " . $stmt->error,
            "sql" => $sql
        );
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();
    $nroRegistro = mysqli_num_rows($result);

    $pagina = "";

    if ($nroRegistro > 0) {

        $pagina .= "<div class='asignar-tarea-modal__grid'>";

        while ($valor = mysqli_fetch_assoc($result)) {

            $id = $valor['id'];

            $nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
            $hora = mb_convert_encoding((string)($valor['hora_format']), 'UTF-8', 'ISO-8859-1');
            $estadoTarea = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
            $tipoTarea = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');

            $cod_tarea_asignada = isset($valor['cod_tarea_asignada']) ? $valor['cod_tarea_asignada'] : "";
            $estado_tarea_asignada = isset($valor['estado_tarea']) ? mb_convert_encoding((string)($valor['estado_tarea']), 'UTF-8', 'ISO-8859-1') : "";
            $fecha_asignada = isset($valor['fecha_asignada_format']) ? mb_convert_encoding((string)($valor['fecha_asignada_format']), 'UTF-8', 'ISO-8859-1') : "";

            $id_html = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
            $nombre_html = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
            $hora_html = htmlspecialchars($hora, ENT_QUOTES, 'UTF-8');
            $estado_html = htmlspecialchars($estadoTarea, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipoTarea, ENT_QUOTES, 'UTF-8');
            $estado_asignado_html = htmlspecialchars($estado_tarea_asignada, ENT_QUOTES, 'UTF-8');
            $fecha_asignada_html = htmlspecialchars($fecha_asignada, ENT_QUOTES, 'UTF-8');

            $yaAsignada = false;

            if ($cod_tarea_asignada != "" && $cod_tarea_asignada != NULL) {
                $yaAsignada = true;
            }

            $claseCard = "asignar-tarea-modal__card";
            $onclick = "onclick='seleccionarTareaParaAsignar(this)'";
            $textoAsignacion = "Disponible";
            $estadoVisual = $estado_html;

            // if ($yaAsignada == true) {
                // $claseCard .= " asignar-tarea-modal__card--asignada";
                // $onclick = "";
                // $textoAsignacion = "Ya asignada";
                // $estadoVisual = $estado_asignado_html;
            // }

            $pagina .= "
            <div 
                class='".$claseCard."'
                data-id='".$id_html."'
                data-nombre='".$nombre_html."'
                ".$onclick.">

                <div class='asignar-tarea-modal__card-top'>
                    <p class='asignar-tarea-modal__card-nombre'>".$nombre_html."</p>
                    <span class='asignar-tarea-modal__badge'>".$tipo_html."</span>
                </div>

                <p class='asignar-tarea-modal__hora'>Hora: ".$hora_html."</p>

                <span class='asignar-tarea-modal__estado'>
                    ".$textoAsignacion."
                </span>";

                if ($yaAsignada == true && $fecha_asignada_html != "") {
                    $pagina .= "
                    <p class='asignar-tarea-modal__asignada-fecha'>
                        Asignada: ".$fecha_asignada_html."
                    </p>";
                }

            $pagina .= "
            </div>";
        }

        $pagina .= "</div>";

    } else {

        $pagina .= "
        <div class='asignar-tarea-modal__vacio'>
            <p>No se encontraron tareas disponibles para asignar.</p>
        </div>";
    }

    $stmt->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "2" => $pagina, "3" => $nroRegistro);
    echo json_encode($informacion);
    exit;
}
function asignarTareaAUsuario($id_tarea, $cod_usuario, $fecha_tarea)
{
    if ($id_tarea == "" || $cod_usuario == "" || $fecha_tarea == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    $mysqli = conectar_al_servidor();

    $fecha_tarea = mysqli_real_escape_string($mysqli, $fecha_tarea);

    $consultaVerificar = "SELECT cod_tarea_asignada 
                          FROM tareas_programadas_asignadas
                          WHERE cod_tareaFK = ?
                          AND cod_usuarioFK = ?
                          AND fecha_tarea = ?
                          AND estado_tarea IN ('Pendiente','En Proceso')
                          LIMIT 1";

    $stmtVerificar = $mysqli->prepare($consultaVerificar);

    if (!$stmtVerificar) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar verificación: " . $mysqli->error,
            "sql" => $consultaVerificar
        );
        echo json_encode($informacion);
        exit;
    }

    $ss = "sss";
    $stmtVerificar->bind_param($ss, $id_tarea, $cod_usuario, $fecha_tarea);

    if (!$stmtVerificar->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al verificar asignación: " . $stmtVerificar->error,
            "sql" => $consultaVerificar
        );
        echo json_encode($informacion);
        exit;
    }

    $resultVerificar = $stmtVerificar->get_result();

    if (mysqli_num_rows($resultVerificar) > 0) {
        $stmtVerificar->close();
        mysqli_close($mysqli);

        $informacion = array(
            "1" => "duplicado",
            "mensaje" => "Esta tarea ya está asignada a este usuario en la fecha seleccionada."
        );
        echo json_encode($informacion);
        exit;
    }

    $stmtVerificar->close();

    $estado_tarea = "Pendiente";
    $visto = "No";
    $fecha_insert = date("Y-m-d H:i:s");

    $consulta1 = "INSERT INTO tareas_programadas_asignadas
                  (
                    cod_tareaFK,
                    cod_usuarioFK,
                    estado_tarea,
                    visto,
                    fecha_tarea,
                    fecha_visto,
                    observacion_admin,
                    observacion_usuario,
                    fecha_completada,
                    fecha_insert,
                    fecha_update
                  )
                  VALUES
                  (?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, ?, NULL)";

    $stmt1 = $mysqli->prepare($consulta1);

    if (!$stmt1) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al preparar asignación: " . $mysqli->error,
            "sql" => $consulta1
        );
        echo json_encode($informacion);
        exit;
    }

    $ss = "ssssss";
    $stmt1->bind_param(
        $ss,
        $id_tarea,
        $cod_usuario,
        $estado_tarea,
        $visto,
        $fecha_tarea,
        $fecha_insert
    );

    if (!$stmt1->execute()) {
        $informacion = array(
            "1" => "error",
            "mensaje" => "Error al asignar tarea: " . $stmt1->error,
            "sql" => $consulta1
        );
        echo json_encode($informacion);
        exit;
    }

    $stmt1->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito");
    echo json_encode($informacion);
    exit;
}

function resolverFotoUsuarioAsignarTarea($cod_usuario, $url)
{
    $fallback = "/GoodVentaAsisCap/iconos/user.png";
    $foto = trim((string)$url);
    $baseDir = realpath(__DIR__ . "/../fotos/perfilUsuario");

    if ($baseDir === false) {
        return ($foto != "") ? $foto : $fallback;
    }

    if ($foto != "" && $foto != "null" && $foto != "undefined") {
        $pathUrl = parse_url($foto, PHP_URL_PATH);

        if ($pathUrl !== false && $pathUrl !== null && strpos($pathUrl, "/fotos/perfilUsuario/") !== false) {
            $archivo = basename($pathUrl);
            $rutaLocal = $baseDir . DIRECTORY_SEPARATOR . $archivo;

            if (is_file($rutaLocal) && filesize($rutaLocal) > 0) {
                return "/GoodVentaAsisCap/fotos/perfilUsuario/" . $archivo;
            }
        } else {
            return $foto;
        }
    }

    $extensiones = array("jpg", "jpeg", "png", "gif", "webp", "JPG", "JPEG", "PNG", "GIF", "WEBP");
    $coincidencias = array();

    foreach ($extensiones as $extension) {
        $archivos = glob($baseDir . DIRECTORY_SEPARATOR . $cod_usuario . "*." . $extension);
        if (is_array($archivos)) {
            $coincidencias = array_merge($coincidencias, $archivos);
        }
    }

    $coincidencias = array_filter($coincidencias, function ($archivo) {
        return is_file($archivo) && filesize($archivo) > 0;
    });

    if (count($coincidencias) > 0) {
        usort($coincidencias, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return "/GoodVentaAsisCap/fotos/perfilUsuario/" . basename($coincidencias[0]);
    }

    return $fallback;
}

function buscarUsuariosAsignarTarea($buscar, $tipo, $estado)
{
    $mysqli = conectar_al_servidor();

    $condicionBuscar = "";
    if ($buscar != "") {
        $condicionBuscar = " and (
            rut_usuario like '%".$buscar."%' 
            or nombre_persona like '%".$buscar."%'
            or tipo like '%".$buscar."%'
        )";
    }

    $condicionTipo = "";
    if ($tipo != "") {
        $condicionTipo = " and cod_localFK = '".$tipo."'";
    }

    $condicionEstado = "";
    if ($estado != "") {
        $condicionEstado = " and u.estado = '".$estado."'";
    }

    $sql = "SELECT 
                cod_usuario,
                rut_usuario,
                login,
                u.estado,
                acceso,
                cod_localFK,
                tipo,
                url,
				nombre_persona,
				Nombre
            FROM usuario u
			inner join persona on cod_persona=cod_usuario
			inner join local on cod_local=cod_localFK
            WHERE 1=1 
            ".$condicionBuscar."
            ".$condicionTipo."
            ".$condicionEstado."
            ORDER BY login ASC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar búsqueda: " . $mysqli->error);
        echo json_encode($informacion);
        exit;
    }

    if (!$stmt->execute()) {
        echo "Error";
        exit;
    }

    $result = $stmt->get_result();
    $nroRegistro = mysqli_num_rows($result);

    $pagina = "";

    if ($nroRegistro > 0) {

        $pagina .= "<div class='asignar-tarea__grid'>";

        while ($valor = mysqli_fetch_assoc($result)) {

            $cod_usuario = $valor['cod_usuario'];
            $rut_usuario = mb_convert_encoding((string)($valor['rut_usuario']), 'UTF-8', 'ISO-8859-1');
            $login = mb_convert_encoding((string)($valor['login']), 'UTF-8', 'ISO-8859-1');
            $nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');
            $tipoUsuario = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
            $Nombre = mb_convert_encoding((string)($valor['Nombre']), 'UTF-8', 'ISO-8859-1');
            $url = mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1');

            $url = resolverFotoUsuarioAsignarTarea($cod_usuario, $url);

            $login_html = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
            $nombre_persona_html = htmlspecialchars($nombre_persona, ENT_QUOTES, 'UTF-8');
            $rut_html = htmlspecialchars($rut_usuario, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipoUsuario, ENT_QUOTES, 'UTF-8');
            $NombreLocal_html = htmlspecialchars($Nombre, ENT_QUOTES, 'UTF-8');
            $url_html = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

            $login_js = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
            $url_js = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

            $pagina .= "
            <div 
                class='asignar-tarea__card' 
                id='usuarioAsignarTarea_".$cod_usuario."'
                onclick=\"seleccionarUsuarioAsignarTarea('".$cod_usuario."', '".$login_js."', '".$url_js."')\">

                <img src='".$url_html."' class='asignar-tarea__foto' onerror=\"this.src='/GoodVentaAsisCap/iconos/user.png'\" />

                <p class='asignar-tarea__nombre'>".$nombre_persona_html."</p>
                <p class='asignar-tarea__login'>CI: ".$rut_html."</p>
				 
                <p class='asignar-tarea__login'>LOCAL: ".$NombreLocal_html."</p>

                <div class='asignar-tarea__tipo'>
                    <span class='asignar-tarea__badge'>".$tipo_html."</span>
                </div>

            </div>";
        }

        $pagina .= "</div>";

    } else {

        $pagina .= "
        <div class='asignar-tarea__vacio'>
            <p>No se encontraron usuarios.</p>
        </div>";
    }

    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "2" => $pagina, "3" => $nroRegistro);
    echo json_encode($informacion);
    exit;
}

function abm($id, $nombre, $hora, $estado, $fecha_realizado, $cod_usuarioFK, $tipo, $operacion)
{
    if ($operacion == 'nuevo' && ($nombre == '' || $hora == '' || $tipo == '')) {
        $informacion = array('1' => 'camposvacio');
        echo json_encode($informacion);
        exit;
    }

    if ($operacion == 'editar' && $id == '') {
        $informacion = array('1' => 'camposvacio');
        echo json_encode($informacion);
        exit;
    }

    $hora = validar_hora_tarea_programada($hora);
    $estado = validar_estado_tarea_programada($estado);

    if ($hora == '') {
        $informacion = array('1' => 'camposvacio');
        echo json_encode($informacion);
        exit;
    }

    $mysqli = conectar_al_servidor();

    if ($operacion == 'nuevo') {

        $cod_usuarioFK_create = isset($_POST['useru']) ? $_POST['useru'] : '0';
        $cod_usuarioFK_create = mb_convert_encoding((string)($cod_usuarioFK_create), 'ISO-8859-1', 'UTF-8');

        if ($cod_usuarioFK == '') {
            $cod_usuarioFK = $cod_usuarioFK_create;
        }

        if ($fecha_realizado == '') {
            $fecha_realizado = NULL;
        }

        $fecha_create = date('Y-m-d H:i:s');

        $consulta1 = "Insert into tareas_programadas 
        (nombre,hora,estado,fecha_realizado,cod_usuarioFK,cod_usuarioFK_create,fecha_create,tipo)
        values(?,?,?,?,?,?,?,?)";

        $stmt1 = $mysqli->prepare($consulta1);

        if (!$stmt1) {
            $informacion = array('1' => 'error', 'mensaje' => 'Error al preparar: ' . $mysqli->error, 'sql' => $consulta1);
            echo json_encode($informacion);
            exit;
        }

        $ss = 'ssssssss';
        $stmt1->bind_param($ss, $nombre, $hora, $estado, $fecha_realizado, $cod_usuarioFK, $cod_usuarioFK_create, $fecha_create, $tipo);
    }

    if ($operacion == 'editar') {

        $parametros = array();
        $atributos = '';
        $ss = '';

        if ($nombre !== NULL) {
            $atributos .= "nombre=?";
            $ss .= "s";
            $parametros[] = $nombre;
        }

        if ($hora !== NULL) {
            $atributos .= $atributos != "" ? ",hora=?" : "hora=?";
            $ss .= "s";
            $parametros[] = $hora;
        }

        if ($tipo !== NULL) {
            $atributos .= $atributos != "" ? ",tipo=?" : "tipo=?";
            $ss .= "s";
            $parametros[] = $tipo;
        }

        if ($estado !== NULL && $estado != '') {
            $atributos .= $atributos != "" ? ",estado=?" : "estado=?";
            $ss .= "s";
            $parametros[] = $estado;
        }

        if ($cod_usuarioFK !== NULL && $cod_usuarioFK != '') {
            $atributos .= $atributos != "" ? ",cod_usuarioFK=?" : "cod_usuarioFK=?";
            $ss .= "s";
            $parametros[] = $cod_usuarioFK;
        }

        if ($fecha_realizado !== NULL && $fecha_realizado != '') {
            $atributos .= $atributos != "" ? ",fecha_realizado=?" : "fecha_realizado=?";
            $ss .= "s";
            $parametros[] = $fecha_realizado;
        }

        if ($atributos == '' || $id == '') {
            $informacion = array('1' => 'camposvacio');
            echo json_encode($informacion);
            exit;
        }

        $parametros[] = $id;
        $ss .= "s";

        $consulta1 = "Update tareas_programadas set $atributos where id=?";
        $stmt1 = $mysqli->prepare($consulta1);

        if (!$stmt1) {
            $informacion = array('1' => 'error', 'mensaje' => 'Error al preparar: ' . $mysqli->error, 'sql' => $consulta1);
            echo json_encode($informacion);
            exit;
        }

        bind_param_tarea_programada($stmt1, $ss, $parametros);
    }

    if (!$stmt1->execute()) {
        $informacion = array('1' => 'error', 'mensaje' => 'Error al guardar: ' . $stmt1->error, 'sql' => $consulta1);
        echo json_encode($informacion);
        exit;
    }

    $stmt1->close();
    mysqli_close($mysqli);

    $informacion = array('1' => 'exito');
    echo json_encode($informacion);
    exit;
}

function buscar($codigo, $nombre, $hora, $tipo)
{
    $mysqli = conectar_al_servidor();
    $pagina = '';

    $condicioncodigo = '';
    if ($codigo != '') {
        $condicioncodigo = " and id ='" . $codigo . "'";
    }

    $condicionnombre = '';
    if ($nombre != '') {
        $condicionnombre = " and nombre like '%" . $nombre . "%'";
    }

    $condicionhora = '';
    if ($hora != '') {
        $hora = substr($hora, 0, 5);
        $condicionhora = " and TIME_FORMAT(hora,'%H:%i') = '" . $hora . "'";
    }

    $condiciontipo = '';
    if ($tipo != '') {
        $condiciontipo = " and tipo like '%" . $tipo . "%'";
    }

    $sql = "Select *, TIME_FORMAT(hora,'%H:%i') as hora_format 
    from tareas_programadas 
    where 1=1 " . $condicioncodigo . $condicionnombre . $condicionhora . $condiciontipo . " 
    order by hora asc, nombre asc, id desc";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array('1' => 'error', 'mensaje' => 'Error al preparar búsqueda: ' . $mysqli->error, 'sql' => $sql);
        echo json_encode($informacion);
        exit;
    }

    if (!$stmt->execute()) {
        echo "Error";
        exit;
    }

    $result = $stmt->get_result();
    $valor = mysqli_num_rows($result);
    $nroRegistro = $valor;
    $styleName = "tableRegistroSearch";

    if ($valor > 0) {
        while ($valor = mysqli_fetch_assoc($result)) {

            $id = $valor['id'];
            $nombre = convertir_utf8_tarea_programada($valor['nombre']);
            $hora = convertir_utf8_tarea_programada($valor['hora_format']);
            $estado = convertir_utf8_tarea_programada($valor['estado']);
            $fecha_realizado = convertir_utf8_tarea_programada($valor['fecha_realizado']);
            $cod_usuarioFK = convertir_utf8_tarea_programada($valor['cod_usuarioFK']);
            $cod_usuarioFK_create = convertir_utf8_tarea_programada($valor['cod_usuarioFK_create']);
            $fecha_create = convertir_utf8_tarea_programada($valor['fecha_create']);
            $tipo = convertir_utf8_tarea_programada($valor['tipo']);

            $id_html = limpiar_html_tarea_programada($id);
            $nombre_html = limpiar_html_tarea_programada($nombre);
            $hora_html = limpiar_html_tarea_programada($hora);
            $tipo_html = limpiar_html_tarea_programada($tipo);
            $estado_html = limpiar_html_tarea_programada($estado);
            $fecha_realizado_html = limpiar_html_tarea_programada($fecha_realizado);
            $cod_usuarioFK_html = limpiar_html_tarea_programada($cod_usuarioFK);
            $cod_usuarioFK_create_html = limpiar_html_tarea_programada($cod_usuarioFK_create);
            $fecha_create_html = limpiar_html_tarea_programada($fecha_create);

            $styleName = CargarStyleTable($styleName);

            $estiloFila = "";
            if ($estado == "completada") {
                $estiloFila = "opacity:.65;text-decoration:line-through;";
            }

            $pagina .= "
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmTareaProgramada(this);verVentanaEditarTareaProgramada();' style='$estiloFila'>
<td id='td_id' style='width:10%; background-color: #efeded;color:red;text-align:center'>" . $id_html . "</td>
<td id='td_datos_1' style='width:50%;text-align:left'>" . $nombre_html . "</td>
<td id='td_datos_2' style='width:20%;text-align:center'>" . $hora_html . "</td>
<td id='td_datos_3' style='width:20%;text-align:center'>" . $tipo_html . "</td>
<td id='td_datos_4' style='display:none'>" . $estado_html . "</td>
<td id='td_datos_5' style='display:none'>" . $cod_usuarioFK_html . "</td>
<td id='td_datos_6' style='display:none'>" . $fecha_realizado_html . "</td>
<td id='td_datos_7' style='display:none'>" . $cod_usuarioFK_create_html . "</td>
<td id='td_datos_8' style='display:none'>" . $fecha_create_html . "</td>
</tr>
</table>";
        }
    }

    mysqli_close($mysqli);

    $informacion = array('1' => 'exito', '2' => $pagina, '3' => $nroRegistro);
    echo json_encode($informacion);
    exit;
}

function buscaroption()
{
    $pagina = "";

    $pagina .= "<option value='DIARIO'>DIARIO</option>";
    $pagina .= "<option value='CASUAL'>CASUAL</option>";

    $informacion = array('1' => 'exito', '2' => $pagina, '3' => 2);
    echo json_encode($informacion);
    exit;
}

verificar($operacion);
?>

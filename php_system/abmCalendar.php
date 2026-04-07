<?php
header('Content-Type: application/json; charset=utf-8');

include 'verificar_navegador.php';
include 'buscar_nivel.php';
include 'classTable.php';
include 'conexion.php';

date_default_timezone_set('America/Asuncion');

$mysqli = conectar_al_servidor();
$mysqli->set_charset("utf8");

$useru = isset($_POST['useru']) ? $_POST['useru'] : '';
$passu = isset($_POST['passu']) ? $_POST['passu'] : '';
$navegador = isset($_POST['navegador']) ? $_POST['navegador'] : '';
$funt = isset($_POST['funt']) ? $_POST['funt'] : '';

if ($useru == '' || $passu == '' || $navegador == '') {
    echo json_encode(array("1" => "UI"));
    exit;
}

$verificar = verificar_navegador($useru, $navegador, $passu);
if ($verificar != 'ok') {
    echo json_encode(array("1" => "UI"));
    exit;
}

switch ($funt) {
    case 'cargarAgenda':
        cargarAgenda($mysqli);
        break;

    case 'guardarCita':
        guardarCita($mysqli, $useru);
        break;

    case 'moverCita':
        moverCita($mysqli, $useru);
        break;

    case 'redimensionarCita':
        redimensionarCita($mysqli, $useru);
        break;
		
	case 'actualizarEstadoCita':
		actualizarEstadoCita($mysqli, $useru);
		break;

    default:
        echo json_encode(array("1" => "Función inválida"));
        break;
}

exit;

/* =========================================================
   FUNCIONES
========================================================= */


function actualizarEstadoCita($mysqli, $useru){
    $id_agenda = isset($_POST['id_agenda']) ? limpiar($mysqli, $_POST['id_agenda']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : '';

    if($id_agenda == '' || $estado == ''){
        echo json_encode(array(
            "1" => "Datos incompletos",
            "mensaje" => "Faltan datos para actualizar el estado"
        ));
        exit;
    }

    $sql = "
        UPDATE agenda SET
            estado = '".$estado."',
            creado_por = '".$useru."',
            creado_en = NOW()
        WHERE id_agenda = '".$id_agenda."'
        LIMIT 1
    ";

    if(!$mysqli->query($sql)){
        echo json_encode(array(
            "1" => "Error al actualizar estado",
            "mensaje" => "No se pudo actualizar el estado del agendamiento",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Estado actualizado correctamente"
    ));
    exit;
}








function limpiar($mysqli, $valor){
    return mysqli_real_escape_string($mysqli, trim($valor));
}

function cargarAgenda($mysqli){
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';

    if ($fecha == '') {
        $fecha = date('Y-m-d');
    }

    $consultorios = array();
    $eventos = array();

    /* ===========================
       CONSULTORIOS
    =========================== */
    $sqlConsultorios = "
        SELECT  c.id_consultorio,
            c.nombre,
            c.descripcion,
            c.color
        FROM consultorios c
        WHERE  c.estado = 'Activo'
        ORDER BY c.nombre ASC ";

    $resultConsultorios = $mysqli->query($sqlConsultorios);

    if (!$resultConsultorios) {
        echo json_encode(array(
            "1" => "Error al consultar consultorios",
            "sql" => $sqlConsultorios,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    while ($row = $resultConsultorios->fetch_assoc()) {
        $consultorios[] = array(
            "id" => (int)$row["id_consultorio"],
            "nombre" => utf8_encode($row["nombre"]),
            "color" => $row["color"] != '' ? $row["color"] : "#7c3aed",
            "descripcion" => utf8_encode($row["descripcion"])
        );
    }

    /* ===========================
       EVENTOS / AGENDAMIENTOS
    =========================== */
    $sqlEventos = "SELECT  a.id_agenda,
            a.id_consultorio,
            a.fecha,
            TIME_FORMAT(a.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(a.hora_fin, '%H:%i') AS hora_fin,
            a.estado,
            a.motivo,
            p.nombre_persona
        FROM agenda a
        INNER JOIN persona p ON p.cod_persona = a.id_paciente
        WHERE a.fecha = '".$fecha."'
        ORDER BY a.hora_inicio ASC, a.id_agenda ASC
    ";

    $resultEventos = $mysqli->query($sqlEventos);

    if (!$resultEventos) {
        echo json_encode(array(
            "1" => "Error al consultar agenda",
            "sql" => $sqlEventos,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    while ($row = $resultEventos->fetch_assoc()) {
        $eventos[] = array(
            "id" => (int)$row["id_agenda"],
            "consultorio" => (int)$row["id_consultorio"],
            "paciente" => utf8_encode($row["nombre_persona"]),
            "fecha" => $row["fecha"],
            "inicio" => $row["hora_inicio"],
            "fin" => $row["hora_fin"],
            "estado" => $row["estado"],
            "motivo" => utf8_encode($row["motivo"])
        );
    }

    echo json_encode(array(
        "1" => "exito",
        "consultorios" => $consultorios,
        "eventos" => $eventos
    ));
    exit;
}

function guardarCita($mysqli, $useru){
    $consultorio = isset($_POST['consultorio']) ? limpiar($mysqli, $_POST['consultorio']) : '';
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $inicio = isset($_POST['inicio']) ? limpiar($mysqli, $_POST['inicio']) : '';
    $fin = isset($_POST['fin']) ? limpiar($mysqli, $_POST['fin']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : 'AGENDADO';
    $motivo = isset($_POST['motivo']) ? limpiar($mysqli, $_POST['motivo']) : '';

    if ($consultorio == '' || $paciente == '' || $fecha == '' || $inicio == '' || $fin == '') {
        echo json_encode(array("1" => "Faltan datos obligatorios"));
        exit;
    }

    $sql = "
        INSERT INTO agenda_consultorios (
            cod_consultorioFK,
            cod_pacienteFK,
            fecha,
            hora_inicio,
            hora_fin,
            estado,
            motivo,
            usuario,
            fechainsert
        ) VALUES (
            '".$consultorio."',
            '".$paciente."',
            '".$fecha."',
            '".$inicio."',
            '".$fin."',
            '".$estado."',
            '".$motivo."',
            '".$useru."',
            NOW()
        )
    ";

    if (!$mysqli->query($sql)) {
        echo json_encode(array(
            "1" => "Error al guardar cita",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Cita guardada correctamente"
    ));
    exit;
}

function moverCita($mysqli, $useru){
    $id_agenda = isset($_POST['id_agenda']) ? limpiar($mysqli, $_POST['id_agenda']) : '';
    $id_consultorio = isset($_POST['id_consultorio']) ? limpiar($mysqli, $_POST['id_consultorio']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $hora_inicio = isset($_POST['hora_inicio']) ? limpiar($mysqli, $_POST['hora_inicio']) : '';
    $hora_fin = isset($_POST['hora_fin']) ? limpiar($mysqli, $_POST['hora_fin']) : '';

    if($id_agenda == '' || $id_consultorio == '' || $fecha == '' || $hora_inicio == '' || $hora_fin == ''){
        echo json_encode(array("1" => "Datos incompletos para mover cita", "mensaje" => "Faltan datos"));
        exit;
    }

    $sql = "
        UPDATE agenda SET
            id_consultorio = '".$id_consultorio."',
            fecha = '".$fecha."',
            hora_inicio = '".$hora_inicio."',
            hora_fin = '".$hora_fin."',
            creado_por = '".$useru."',
            creado_en = NOW()
        WHERE id_agenda = '".$id_agenda."'
        LIMIT 1
    ";

    if(!$mysqli->query($sql)){
        echo json_encode(array(
            "1" => "Error al mover cita",
            "mensaje" => "No se pudo mover la cita",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Cita movida correctamente"
    ));
    exit;
}
function redimensionarCita($mysqli, $useru){
    $id_agenda = isset($_POST['id_agenda']) ? limpiar($mysqli, $_POST['id_agenda']) : '';
    $hora_fin = isset($_POST['hora_fin']) ? limpiar($mysqli, $_POST['hora_fin']) : '';

    if($id_agenda == '' || $hora_fin == ''){
        echo json_encode(array("1" => "Datos incompletos para redimensionar cita", "mensaje" => "Faltan datos"));
        exit;
    }

    $sql = "
        UPDATE agenda SET
            hora_fin = '".$hora_fin."',
            creado_por = '".$useru."',
            creado_en = NOW()
        WHERE id_agenda = '".$id_agenda."'
        LIMIT 1
    ";

    if(!$mysqli->query($sql)){
        echo json_encode(array(
            "1" => "Error al redimensionar cita",
            "mensaje" => "No se pudo actualizar el horario",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Horario actualizado correctamente"
    ));
    exit;
}


?>
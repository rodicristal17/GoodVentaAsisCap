<?php

include_once 'verificar_navegador.php';
include_once 'buscar_nivel.php';
include_once 'classTable.php';
include_once 'conexion.php';
include_once 'abmAgenda.php';

date_default_timezone_set('America/Asuncion');

$useru= "";
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Content-Type: application/json; charset=utf-8');

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
    
        case 'actualizarCita':
            actualizarCita($mysqli, $useru);
            break;
    
        case 'actualizarMotivoCita':
            actualizarMotivoCita($mysqli, $useru);
            break;
    
        case 'actualizarPresupuestoAgenda':
            actualizarPresupuestoAgenda($mysqli, $useru);
            break;
            
        case 'buscarPacientesAgenda':
            buscarPacientesAgenda($mysqli);
            break;
    
        case 'guardarPacienteAgenda':
            guardarPacienteAgenda($mysqli, $useru);
            break;
    
        case 'buscarHistorialPacienteCalendario':
            buscarHistorialPacienteCalendario($mysqli);
            break;
            
    
        default:
            echo json_encode(array("1" => "Función inválida"));
            break;
    }
    
    exit;
}

/* =========================================================
   FUNCIONES
========================================================= */


function buscarPacientesAgenda($mysqli){
    $buscar = isset($_POST['buscar']) ? limpiar($mysqli, $_POST['buscar']) : '';

    $condicion = "";
    if($buscar != ''){
        $condicion = " AND (
            p.nombre_persona LIKE '%".$buscar."%' OR
            c.ci_cliente LIKE '%".$buscar."%' OR
            p.telefono LIKE '%".$buscar."%'
        )";
    }

    $sql = "SELECT
                p.cod_persona,
                p.nombre_persona,
                IFNULL(c.ci_cliente,'') AS documento,
                IFNULL(p.telefono,'') AS telefono
            FROM persona p inner join cliente c on cod_persona=cod_cliente
            WHERE 1=1
            ".$condicion."
            ORDER BY p.nombre_persona ASC
            LIMIT 100";

    $result = $mysqli->query($sql);

    if(!$result){
        echo json_encode(array(
            "1" => "Error",
            "2" => $mysqli->error
        ));
        exit;
    }

    $html = "<table class='tableRegistroSearch' style='width:100%;'>";
    $html .= "<tr class='td_registro2'>
                <td style='width:15%;'>ID</td>
                <td style='width:45%;'>PACIENTE</td>
                <td style='width:20%;'>DOCUMENTO</td>
                <td style='width:20%;'>TELÉFONO</td>
              </tr>";

    while($row = $result->fetch_assoc()){
        $nombre = normalizarTextoUtf8($row["nombre_persona"]);

        $html .= "<tr class='tr_registro' style='cursor:pointer;' onclick=\"seleccionarPacienteAgenda('".$row["cod_persona"]."', '".addslashes($nombre)."')\">";
        $html .= "<td>".$row["cod_persona"]."</td>";
        $html .= "<td>".$nombre."</td>";
        $html .= "<td>".$row["documento"]."</td>";
        $html .= "<td>".$row["telefono"]."</td>";
        $html .= "</tr>";
    }

    $html .= "</table>";

    echo json_encode(array(
        "1" => "exito",
        "2" => $html
    ));
    exit;
}

 
function guardarPacienteAgenda($mysqli, $useru){
    $nombre = isset($_POST['nombre']) ? limpiar($mysqli, $_POST['nombre']) : '';
    $documento = isset($_POST['documento']) ? limpiar($mysqli, $_POST['documento']) : '';
    $telefono = isset($_POST['telefono']) ? limpiar($mysqli, $_POST['telefono']) : '';
    $direccion = isset($_POST['direccion']) ? limpiar($mysqli, $_POST['direccion']) : '';

    if($nombre == ''){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "Debe cargar el nombre del paciente"
        ));
        exit;
    }
	 
    if($documento != ''){
        $sqlVerificar = "SELECT cod_cliente 
                         FROM cliente
                         WHERE ci_cliente = '".$documento."'
                         LIMIT 1";

        $resultVerificar = $mysqli->query($sqlVerificar);

        if(!$resultVerificar){
            echo json_encode(array(
                "1" => "Error",
                "mensaje" => "No se pudo verificar si el paciente ya existe",
                "sql" => $sqlVerificar,
                "mysql" => $mysqli->error
            ));
            exit;
        }

        if($resultVerificar->num_rows > 0){
            $rowExiste = $resultVerificar->fetch_assoc();

            echo json_encode(array(
                "1" => "Error",
                "mensaje" => "Ya existe un paciente con ese documento",
                "id_paciente" => $rowExiste["cod_cliente"] 
            ));
            exit;
        }
    }

	
	

    mysqli_begin_transaction($mysqli);

    try {
        $sqlPersona = "INSERT INTO persona (
                            nombre_persona, 
                            direccion 
                        ) VALUES (
                            '".$nombre."', 
                            '".$direccion."' 
                        )";

        if(!$mysqli->query($sqlPersona)){
            throw new Exception("Error al guardar en persona: ".$mysqli->error);
        }

        $idPersona = $mysqli->insert_id;

        $sqlCliente = "INSERT INTO cliente (
                            cod_cliente,
							ci_cliente,
                            whapp,
                            estado,
                            cod_user_insert,
                            fecha_insert,
                            accesocredito,
							idzonaFk
                        ) VALUES (
                            '".$idPersona."',
							'".$documento."',
                            '".$telefono."',
                            'Activo',
                            '".$useru."',
                            NOW(),
                            'Confirmado',
							0
                        )";

        if(!$mysqli->query($sqlCliente)){
            throw new Exception("Error al guardar en cliente: ".$mysqli->error);
        }

        mysqli_commit($mysqli);

        echo json_encode(array(
            "1" => "exito",
            "mensaje" => "Paciente guardado correctamente",
            "id_paciente" => $idPersona,
            "nombre_paciente" => $nombre
        ));
        exit;

    } catch (Exception $e) {
        mysqli_rollback($mysqli);

        echo json_encode(array(
            "1" => "Error",
            "mensaje" => $e->getMessage()
        ));
        exit;
    }
}


function buscarHistorialPacienteCalendario($mysqli){
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';

    if($paciente == ''){
        echo json_encode(array(
            "1" => "exito",
            "2" => "",
            "3" => 0
        ));
        exit;
    }

    $sql = "SELECT
                a.id_agenda,a.fecha,a.id_paciente,cl.ci_cliente,
                DATE_FORMAT(a.fecha, '%d/%m/%Y') AS fecha_formateada,
                TIME_FORMAT(a.hora_inicio, '%H:%i') AS hora_inicio,
                TIME_FORMAT(a.hora_fin, '%H:%i') AS hora_fin,
                a.estado
            FROM agenda a
            INNER JOIN persona p ON p.cod_persona = a.id_paciente
            INNER JOIN cliente cl ON cl.cod_cliente = a.id_paciente
            WHERE (
                p.nombre_persona LIKE '%".$paciente."%' OR
                cl.ci_cliente LIKE '%".$paciente."%' OR
                a.id_paciente LIKE '%".$paciente."%'
            )
            ORDER BY a.fecha DESC, a.hora_inicio DESC, a.id_agenda DESC
            LIMIT 100";

    $result = $mysqli->query($sql);

    if(!$result){
        echo json_encode(array(
            "1" => "Error",
            "2" => $mysqli->error,
            "sql" => $sql
        ));
        exit;
    }

    $html = "";
    $clientesDiferentes = array();

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $clientesDiferentes[$row["id_paciente"]] = true;
            $html .= "<table class='tableRegistroSearch2' style='width:100%;'>";
            $fechaHora = "";

            if($row["hora_inicio"] != '' || $row["hora_fin"] != ''){
                $fechaHora = $row["hora_inicio"]." - ".$row["hora_fin"];
            }

            $estado = htmlspecialchars(normalizarTextoUtf8($row["estado"]), ENT_QUOTES, 'UTF-8');
            $estado = '<span class="badge-estado-detalle badge-'.$estado.'">'.$estado.'</span>';

            $html .= "<tr id='tbSeleccRegistro' onclick='console.info(\"".$row["fecha"]."\");document.getElementById(\"inptFechaAgenda\").value=\"".$row["fecha"]."\" ;aplicarFiltrosAgenda();vercerrarModalFiltrosAgenda(false);' style='text-align: center;'>";
            $html .= "<td style='width:30%;'>".$row["fecha_formateada"]."</td>";
            $html .= "<td style='width:40%;'>".$fechaHora."</td>";
            $html .= "<td style='width:30%;'>".$estado."</td>";
            $html .= "</tr>";
            $html .= "</table>";
        }

    }

    echo json_encode(array(
        "1" => "exito",
        "2" => $html,
        "3" => count($clientesDiferentes)
    ));
    exit;
}





function guardarCita($mysqli, $useru){
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $consultorio = isset($_POST['consultorio']) ? limpiar($mysqli, $_POST['consultorio']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $inicio = isset($_POST['inicio']) ? limpiar($mysqli, $_POST['inicio']) : '';
    $fin = isset($_POST['fin']) ? limpiar($mysqli, $_POST['fin']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : 'AGENDADO';
    $motivo = isset($_POST['motivo']) ? limpiar($mysqli, $_POST['motivo']) : '';

    if($paciente == '' || $consultorio == '' || $fecha == '' || $inicio == '' || $fin == ''){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "Faltan datos obligatorios"
        ));
        exit;
    }

    $sql = "INSERT INTO agenda (
                id_paciente,
                id_consultorio,
                fecha,
                hora_inicio,
                hora_fin,
                estado,
                motivo,
                creado_por,
                creado_en
            ) VALUES (
                '".$paciente."',
                '".$consultorio."',
                '".$fecha."',
                '".$inicio."',
                '".$fin."',
                '".$estado."',
                '".$motivo."',
                '".$useru."',
                NOW()
            )";

    if(!$mysqli->query($sql)){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se pudo guardar la cita",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    $id_agenda = $mysqli->insert_id;
    crearComentario($id_agenda, "@{0}: @{".$useru."} a creado la cita.");

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Cita guardada correctamente"
    ));
    exit;
}



function actualizarCita($mysqli, $useru){
    $id_agenda = isset($_POST['id_agenda']) ? limpiar($mysqli, $_POST['id_agenda']) : '';
    $hora_inicio = isset($_POST['hora_inicio']) ? limpiar($mysqli, $_POST['hora_inicio']) : '';
    $hora_fin = isset($_POST['hora_fin']) ? limpiar($mysqli, $_POST['hora_fin']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : '';    
    $motivo = isset($_POST['motivo']) ? limpiar($mysqli, $_POST['motivo']) : '';
    $campos = array();

    if($id_agenda == ''){
        echo json_encode(array(
            "1" => "Datos incompletos",
            "mensaje" => "Falta el ID del agendamiento"
        ));
        exit;
    }

    if($hora_inicio != ''){
        $campos[] = "hora_inicio = '".$hora_inicio."'";
    }

    if($hora_fin != ''){
        $campos[] = "hora_fin = '".$hora_fin."'";
    }

    if($estado != ''){
        $campos[] = "estado = '".$estado."'";
    }

    if($motivo != ''){
        $campos[] = "motivo = '".$motivo."'";
    }

    if($hora_inicio != '' && $hora_fin != '' && strtotime($hora_fin) <= strtotime($hora_inicio)){
        echo json_encode(array(
            "1" => "Horario inválido",
            "mensaje" => "La hora fin debe ser mayor a la hora inicio"
        ));
        exit;
    }

    if(count($campos) == 0){
        echo json_encode(array(
            "1" => "Datos incompletos",
            "mensaje" => "No hay datos para actualizar"
        ));
        exit;
    }

    $agendaAnterior = obtenerAgendaAuditoria($mysqli, $id_agenda);

    $campos[] = "creado_por = '".$useru."'";
    $campos[] = "creado_en = NOW()";

    $sql = "
        UPDATE agenda SET
            ".implode(",\n            ", $campos)."
        WHERE id_agenda = '".$id_agenda."'
        LIMIT 1
    ";

    if(!$mysqli->query($sql)){
        echo json_encode(array(
            "1" => "Error al actualizar cita",
            "mensaje" => "No se pudo actualizar el agendamiento",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "hora_inicio" => $hora_inicio,
        "hora_fin" => $hora_fin,
        "estado" => $estado,
        "motivo" => $motivo
    ));

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Agendamiento actualizado correctamente"
    ));
    exit;
}

function actualizarMotivoCita($mysqli, $useru){
    actualizarCita($mysqli, $useru);
}


function actualizarPresupuestoAgenda($mysqli, $useru){
    $id_agenda = isset($_POST['id_agenda']) ? limpiar($mysqli, $_POST['id_agenda']) : '';
    $cod_presupuestoFK = isset($_POST['cod_presupuestoFK']) ? limpiar($mysqli, $_POST['cod_presupuestoFK']) : '';
    $campos = array();

    if($id_agenda == ''){
        echo json_encode(array(
            "1" => "error",
            "mensaje" => "Falta el ID del agendamiento"
        ));
        exit;
    }

    if($cod_presupuestoFK != ''){
        $campos[] = "cod_presupuestoFK = '".$cod_presupuestoFK."'";
    }

    if(count($campos) == 0){
        echo json_encode(array(
            "1" => "error",
            "mensaje" => "No hay datos para actualizar"
        ));
        exit;
    }

    $agendaAnterior = obtenerAgendaAuditoria($mysqli, $id_agenda);

    $campos[] = "estado = 'ATENDIDO'";

    $sql = "
        UPDATE agenda SET
            ".implode(",\n            ", $campos)."
        WHERE id_agenda = '".$id_agenda."'
        LIMIT 1
    ";

    if(!$mysqli->query($sql)){
        echo json_encode(array(
            "1" => "Error al actualizar presupuesto",
            "mensaje" => "No se pudo asociar el presupuesto al agendamiento",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "cod_presupuestoFK" => $cod_presupuestoFK,
    ));

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Presupuesto asociado correctamente"
    ));
    exit;
}

 

function limpiar($mysqli, $valor){
    return mysqli_real_escape_string($mysqli, trim($valor));
}

function obtenerAgendaAuditoria($mysqli, $id_agenda){
    $id_agenda = limpiar($mysqli, $id_agenda);
    $sql = "SELECT * FROM agenda WHERE id_agenda = '".$id_agenda."' LIMIT 1";
    $result = $mysqli->query($sql);

    if(!$result || $result->num_rows == 0){
        return array();
    }

    return $result->fetch_assoc();
}

function normalizarTextoUtf8($valor){
    if ($valor === null) {
        return '';
    }

    if (mb_check_encoding($valor, 'UTF-8')) {
        return $valor;
    }

    return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
}

function cargarAgenda($mysqli){
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $cod_consultorio = isset($_POST['cod_consultorio']) ? limpiar($mysqli, $_POST['cod_consultorio']) : '';
    $cod_local = isset($_POST['cod_local']) ? limpiar($mysqli, $_POST['cod_local']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : '';
    $ver_todos_consoltorios = isset($_POST['ver_todos_consoltorios']) ? limpiar($mysqli, $_POST['ver_todos_consoltorios']) : 'true';

    if ($fecha == '') {
        $fecha = date('Y-m-d');
    }

    $consultorios = array();
    $eventos = array();

    /* ===========================
       CONSULTORIOS
    =========================== */
	$sqlFiltro="";
	if($cod_local!=""){
		$sqlFiltro.=" and c.cod_localFk = '".$cod_local."'";
	}
    if ($ver_todos_consoltorios == 'false') {
        $sqlFiltro .= " AND (c.cod_doctorFK IN (SELECT cod_usuarioFK FROM usuario WHERE cod_usuario = '".$useru."'))";
    }
	
    $sqlConsultorios = "
        SELECT  c.id_consultorio,
            c.nombre,
            (SELECT nombre_persona FROM persona WHERE cod_persona= c.cod_doctorFK) AS nombre_doctor,
            c.descripcion,
            c.cod_doctorFK,
            c.color
        FROM consultorios c
        WHERE  c.estado = 'Activo' ".$sqlFiltro."
        ORDER BY cod_localFk asc ,c.nombre ASC ";

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
            "cod_doctorFK" => (int)$row["cod_doctorFK"],
            "nombre" => normalizarTextoUtf8($row["nombre"]),
            "nombre_doctor" => normalizarTextoUtf8($row["nombre_doctor"]),
            "color" => $row["color"] != '' ? $row["color"] : "#7c3aed",
            "descripcion" => normalizarTextoUtf8($row["descripcion"])
        );
    }

    /* ===========================
       EVENTOS / AGENDAMIENTOS
    =========================== */
	
	$condicion="";
	if($fecha!=""){
		$condicion.=" and a.fecha = '".$fecha."'";
	}
	
	if($paciente!=""){
		$condicion.=" and (
            p.nombre_persona like '%".$paciente."%' OR
            cl.ci_cliente like '%".$paciente."%' OR
            a.id_paciente like '%".$paciente."%'
        )";
	}
	
	if($cod_consultorio!=""){
		$condicion.=" and a.id_consultorio = '".$cod_consultorio."'";
	}
	
	if($cod_local!=""){
		$condicion.=" and c.cod_localFk = '".$cod_local."'";
	}
	
	if($estado!=""){
		$condicion.=" and a.estado = '".$estado."'";
	}	
	
    $sqlEventos = "SELECT  a.id_agenda,
            a.id_consultorio,
            a.fecha,
            TIME_FORMAT(a.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(a.hora_fin, '%H:%i') AS hora_fin,
            a.estado,
            a.motivo,
            cl.ci_cliente, cl.idzonaFk,cl.whapp, p.telefono,cl.fechanac,cl.rut_cliente,cl.cod_cliente,
            (SELECT nombre FROM zona WHERE idzona = cl.idzonaFk) AS nombre_zona,
            (SELECT nombre_persona FROM persona JOIN presupuesto ON cod_usuarioFK_create = cod_persona WHERE a.cod_presupuestoFK = id) AS nombre_doctor_presupesto,
            (SELECT fecha_create FROM presupuesto WHERE id = a.cod_presupuestoFK) AS fecha_presupuesto,
            (SELECT nombre_persona FROM persona JOIN consulta ON consulta.cod_usuarioFK = cod_persona WHERE consulta.cod_agendamientoFK is not null and a.id_agenda = consulta.cod_agendamientoFK limit 1) AS nombre_doctor_consulta,
            (SELECT fecha FROM consulta WHERE consulta.cod_agendamientoFK is not null and cod_agendamientoFK = a.id_agenda limit 1) AS fecha_consulta,
            (SELECT nombre_persona FROM persona JOIN evoluciontratamiento ON evoluciontratamiento.cod_usuraioFK = cod_persona WHERE a.id_agenda = evoluciontratamiento.cod_agendaFK) AS nombre_doctor_tratamiento,
            (SELECT fecha FROM evoluciontratamiento WHERE cod_agendaFK = a.id_agenda) AS fecha_tratamiento,
            (SELECT GROUP_CONCAT(CONCAT(p.nombre_producto, '(', et.nro,'%)') SEPARATOR '<br> ') FROM evoluciontratamiento et JOIN detalle_venta dv ON et.cod_detalle_venta = dv.cod_detalle JOIN producto p ON p.cod_producto= dv.cod_productoFK WHERE cod_agendaFK = a.id_agenda) AS nombre_tratamiento,
            p.nombre_persona
        FROM agenda a
        INNER JOIN persona p ON p.cod_persona = a.id_paciente
        INNER JOIN cliente cl ON cl.cod_cliente = a.id_paciente
        INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
        WHERE 1=1 ".$condicion."
        ORDER BY a.fecha ASC, a.id_consultorio ASC, a.hora_inicio ASC, a.id_agenda ASC
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

    $usuariosAgenda = obtenerUsuariosAgenda($mysqli);

    while ($row = $resultEventos->fetch_assoc()) {
        $motivo = normalizarTextoUtf8($row["motivo"]);
        $motivoLimpio = "";

        if(preg_match_all('/@\{(\d+)\}\s*:\s*(.*?)(?=@\{\d+\}\s*:|$)/s', $motivo, $coincidencias, PREG_SET_ORDER)){
            foreach($coincidencias as $coincidencia){
                $codUsuario = $coincidencia[1];
                $nombreUsuario = isset($usuariosAgenda[$codUsuario]) ? $usuariosAgenda[$codUsuario] : "@{".$codUsuario."}";
                $contenidoTexto = trim($coincidencia[2]);
                if($contenidoTexto == ""){
                    continue;
                }
                $contenidoMotivo = nl2br($contenidoTexto, false);

                $motivoLimpio .= '<div class="sugerencias-container" style="justify-content:flex-start;margin:0;">
                    <div class="card my-3" style="border-left:5px solid #416c8f;margin: 0px !important;margin-bottom: 7px !important;display:flex;flex-direction:column;gap:0;min-height:auto;">
                        <div class="card-header d-flex justify-content-between align-items-center" style="padding:6px 10px 4px 10px;gap:10px;min-height:auto;">
                            <span style="font-size:10pt;line-height:1.15;">'.$nombreUsuario.'</span>
                        </div>
                        <div class="card-body" style="padding:4px 10px 8px 10px;">
                            <p class="card-text" style="font-size: 10pt; text-align:justify;margin:0;line-height:1.35;"><b>Motivo consulta:</b> '.$contenidoMotivo.'</p>
                        </div>
                    </div>
                </div>';
            }
        }

        // Asigna el nombre del doctor segun la fecha
        $nombre_doctor = "";
        if ($row["fecha_consulta"] == $row["fecha"]) {
            $nombre_doctor = $row["nombre_doctor_consulta"];
        } elseif (substr($row["fecha_tratamiento"], 0, 10) == $row["fecha"]) {
            $nombre_doctor = $row["nombre_doctor_tratamiento"];
        } elseif (substr($row["fecha_presupuesto"], 0, 10) == $row["fecha"]) {
            $nombre_doctor = $row["nombre_doctor_presupesto"];
        }

        $eventos[] = array(
            "id" => (int)$row["id_agenda"],
            "consultorio" => (int)$row["id_consultorio"],
            "paciente" => normalizarTextoUtf8($row["nombre_persona"]),
            "fecha" => $row["fecha"],
            "inicio" => $row["hora_inicio"],
            "fin" => $row["hora_fin"],
            "estado" => $row["estado"],
            "ci_cliente" => $row["ci_cliente"],
            "idzonaFk" => $row["idzonaFk"],
            "whapp" => $row["whapp"],
            "telefono" => $row["telefono"],
            "fechanac" => $row["fechanac"],
            "nombre_zona" => $row["nombre_zona"],
            "rut_cliente" => $row["rut_cliente"],
            "cod_cliente" => $row["cod_cliente"],
            "nombre_doctor" => $nombre_doctor,
            "nombres_tratamiento" => $row["nombre_tratamiento"],
            "motivo" => normalizarTextoUtf8($row["motivo"]),
            "motivo_limpio" => $motivoLimpio
        );
    }

    echo json_encode(array(
        "1" => "exito",
        "consultorios" => $consultorios,
        "eventos" => $eventos
    ));
    exit;
}

function obtenerUsuariosAgenda($mysqli= null){
    if ($mysqli === null) {
        $mysqli = conectar_al_servidor();
    }

    $usuarios = array();
    $sql = "SELECT u.cod_usuario, p.nombre_persona
            FROM usuario u
            INNER JOIN persona p ON p.cod_persona = u.cod_usuario";

    $result = $mysqli->query($sql);

    if(!$result){
        return $usuarios;
    }

    while($row = $result->fetch_assoc()){
        $usuarios[$row["cod_usuario"]] = normalizarTextoUtf8($row["nombre_persona"]);
    }

    return $usuarios;
}

function buscarUsuarios() {
    $mysqli=conectar_al_servidor();

    $sql= "SELECT u.*, p.nombre_persona FROM usuario u JOIN persona p ON p.cod_persona = u.cod_usuario WHERE u.estado = 'Activo'";
    $stmt = $mysqli->prepare($sql);
    if ( !$stmt->execute()) {
        $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
        echo json_encode($informacion);	
        exit;
    }        

    $result = $stmt->get_result();
    $registros= array();
    while ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
        }
        $registros[] = $reg;
    }

    $stmt->close();
    return $registros;
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

    $agendaAnterior = obtenerAgendaAuditoria($mysqli, $id_agenda);

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

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "id_consultorio" => $id_consultorio,
        "fecha" => $fecha,
        "hora_inicio" => $hora_inicio,
        "hora_fin" => $hora_fin
    ));

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

    $agendaAnterior = obtenerAgendaAuditoria($mysqli, $id_agenda);

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

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "hora_fin" => $hora_fin
    ));

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Horario actualizado correctamente"
    ));
    exit;
}

function cargarpacientes($mysqli){
    $html = "";

    $sql = "SELECT cod_persona, nombre_persona
            FROM persona
            WHERE IFNULL(nombre_persona,'') != ''
            ORDER BY nombre_persona ASC";

    $result = $mysqli->query($sql);

    if(!$result){
        echo json_encode(array("1" => "Error", "2" => $mysqli->error));
        exit;
    }

    while($row = $result->fetch_assoc()){
        $html .= "<option data-id='".$row["cod_persona"]."' value='".normalizarTextoUtf8($row["nombre_persona"])."'></option>";
    }

    echo json_encode(array("exito" => "1", "2" => $html));
    exit;
}

function cargarespecialistas($mysqli){
    $html = "";

    $sql = "SELECT cod_consultorio, nombre
            FROM consultorio
            WHERE estado = 'Activo'
            ORDER BY nombre ASC";

    $result = $mysqli->query($sql);

    if(!$result){
        echo json_encode(array("1" => "Error", "2" => $mysqli->error));
        exit;
    }

    while($row = $result->fetch_assoc()){
        $html .= "<option data-id='".$row["cod_consultorio"]."' value='".normalizarTextoUtf8($row["nombre"])."'></option>";
    }

    echo json_encode(array("1" => "exito", "2" => $html));
    exit;
}

function guardarAgendamiento($mysqli, $useru){
    $id = isset($_POST['id_agendamiento']) ? limpiar($mysqli, $_POST['id_agendamiento']) : '';
    $id_paciente = isset($_POST['id_paciente']) ? limpiar($mysqli, $_POST['id_paciente']) : '';
    $id_especialista = isset($_POST['id_especialista']) ? limpiar($mysqli, $_POST['id_especialista']) : '';
    $fecha_recepcion = isset($_POST['fecha_recepcion']) ? limpiar($mysqli, $_POST['fecha_recepcion']) : '';
    $fecha_consulta = isset($_POST['fecha_consulta']) ? limpiar($mysqli, $_POST['fecha_consulta']) : '';
    $observacion = isset($_POST['observacion']) ? limpiar($mysqli, $_POST['observacion']) : '';

    if($id_paciente == '' || $id_especialista == '' || $fecha_recepcion == '' || $fecha_consulta == ''){
        echo json_encode(array("1" => "Error", "2" => "Faltan datos obligatorios"));
        exit;
    }

    if($id == ''){
        $sql = "INSERT INTO agendamiento (
                    cod_pacienteFK,
                    cod_consultorioFK,
                    fecha_recepcion,
                    fecha_consulta,
                    observacion,
                    estado,
                    usuario,
                    fechainsert
                ) VALUES (
                    '".$id_paciente."',
                    '".$id_especialista."',
                    '".$fecha_recepcion."',
                    '".$fecha_consulta."',
                    '".$observacion."',
                    'Pendiente',
                    '".$useru."',
                    NOW()
                )";
    }else{
        $sql = "UPDATE agendamiento SET
                    cod_pacienteFK = '".$id_paciente."',
                    cod_consultorioFK = '".$id_especialista."',
                    fecha_recepcion = '".$fecha_recepcion."',
                    fecha_consulta = '".$fecha_consulta."',
                    observacion = '".$observacion."'
                WHERE cod_agendamiento = '".$id."'
                LIMIT 1";
    }

    if(!$mysqli->query($sql)){
        echo json_encode(array("1" => "Error", "2" => $mysqli->error, "sql" => $sql));
        exit;
    }

    echo json_encode(array("1" => "exito", "2" => "Guardado correctamente"));
    exit;
}

function buscarAgendamiento($mysqli){
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $especialista = isset($_POST['especialista']) ? limpiar($mysqli, $_POST['especialista']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';

    $condPaciente = "";
    $condEspecialista = "";
    $condFecha = "";

    if($paciente != ''){
        $condPaciente = " AND p.nombre_persona LIKE '%".$paciente."%'";
    }

    if($especialista != ''){
        $condEspecialista = " AND c.nombre LIKE '%".$especialista."%'";
    }

    if($fecha != ''){
        $condFecha = " AND a.fecha_consulta = '".$fecha."'";
    }

    $sql = "SELECT
                a.cod_agendamiento,
                a.cod_pacienteFK,
                a.cod_consultorioFK,
                a.fecha_recepcion,
                a.fecha_consulta,
                a.observacion,
                a.estado,
                p.nombre_persona AS paciente,
                c.nombre AS especialista
            FROM agendamiento a
            INNER JOIN persona p ON p.cod_persona = a.cod_pacienteFK
            INNER JOIN consultorio c ON c.cod_consultorio = a.cod_consultorioFK
            WHERE 1=1
            ".$condPaciente."
            ".$condEspecialista."
            ".$condFecha."
            ORDER BY a.cod_agendamiento DESC";

    $result = $mysqli->query($sql);

    if(!$result){
        echo json_encode(array("1" => "Error", "2" => $mysqli->error, "sql" => $sql));
        exit;
    }

    $html = "<table class='tableRegistroSearch'>";

    while($row = $result->fetch_assoc()){
        $estadoColor = "#ffc107";

        if($row["estado"] == "Confirmado"){
            $estadoColor = "#32c782";
        }else if($row["estado"] == "Cancelado"){
            $estadoColor = "#858585";
        }

        $html .= "<tr class='tr_registro' "
            . "data-id='".$row["cod_agendamiento"]."' "
            . "data-id-paciente='".$row["cod_pacienteFK"]."' "
            . "data-id-especialista='".$row["cod_consultorioFK"]."' "
            . "data-paciente=\"".normalizarTextoUtf8($row["paciente"])."\" "
            . "data-especialista=\"".normalizarTextoUtf8($row["especialista"])."\" "
            . "data-fecha-recepcion='".$row["fecha_recepcion"]."' "
            . "data-fecha-consulta='".$row["fecha_consulta"]."' "
            . "data-observacion=\"".normalizarTextoUtf8($row["observacion"])."\" "
            . "onclick='seleccionarAgendamiento(this)'>";

        $html .= "<td style='width:5%;'>".$row["cod_agendamiento"]."</td>";
        $html .= "<td style='width:25%;'>".normalizarTextoUtf8($row["paciente"])."</td>";
        $html .= "<td style='width:30%;'>".normalizarTextoUtf8($row["especialista"])."</td>";
        $html .= "<td style='width:25%;'>".$row["fecha_consulta"]."</td>";
        $html .= "<td style='width:15%; color:#fff; background:".$estadoColor.";'>".$row["estado"]."</td>";
        $html .= "</tr>";
    }

    $html .= "</table>";

    echo json_encode(array("1" => "exito", "2" => $html));
    exit;
}
?>

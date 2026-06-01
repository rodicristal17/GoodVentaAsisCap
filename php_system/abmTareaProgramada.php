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

function normalizar_destino_tarea_programada($tipo_destino)
{
    $tipo_destino = strtoupper(trim((string)$tipo_destino));

    if ($tipo_destino != "ROL") {
        $tipo_destino = "USUARIO";
    }

    return $tipo_destino;
}

function buscar_usuarios_por_rol_tarea_programada($mysqli, $rol_operativo)
{
    $usuarios = array();

    $sql = "SELECT cod_usuario
            FROM usuario
            WHERE estado = 'Activo'
            AND tipo = ?
            ORDER BY cod_usuario ASC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        return $usuarios;
    }

    $s = "s";
    $stmt->bind_param($s, $rol_operativo);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        while ($fila = mysqli_fetch_assoc($result)) {
            $usuarios[] = $fila["cod_usuario"];
        }
    }

    $stmt->close();

    return $usuarios;
}

function existe_columna_tarea_programada($mysqli, $tabla, $columna)
{
    $tabla = mysqli_real_escape_string($mysqli, $tabla);
    $columna = mysqli_real_escape_string($mysqli, $columna);

    $sql = "SHOW COLUMNS FROM `".$tabla."` LIKE '".$columna."'";
    $result = $mysqli->query($sql);

    if (!$result) {
        return false;
    }

    $existe = mysqli_num_rows($result) > 0;
    $result->free();

    return $existe;
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

    $rol_operativo = isset($_POST['rol_operativo']) ? $_POST['rol_operativo'] : "";
    $rol_operativo = mb_convert_encoding((string)($rol_operativo), 'ISO-8859-1', 'UTF-8');

    buscarUsuariosAsignarTarea($buscar, $tipo, $estado, $rol_operativo);
}

if($operacion=="buscarRolesAsignarTarea")
{
    $buscar = isset($_POST['buscar']) ? $_POST['buscar'] : "";
    $buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

    $estado = isset($_POST['estado']) ? $_POST['estado'] : "";
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

    buscarRolesAsignarTarea($buscar, $estado);
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

    $tipo_destino = isset($_POST['tipo_destino']) ? $_POST['tipo_destino'] : "USUARIO";
    $tipo_destino = mb_convert_encoding((string)($tipo_destino), 'ISO-8859-1', 'UTF-8');

    $rol_operativo = isset($_POST['rol_operativo']) ? $_POST['rol_operativo'] : "";
    $rol_operativo = mb_convert_encoding((string)($rol_operativo), 'ISO-8859-1', 'UTF-8');

    buscarTareasParaAsignarUsuario($buscar, $tipo, $estado, $cod_usuario, $tipo_destino, $rol_operativo);
}

if($operacion=="asignarTareaAUsuario")
{
    $id_tarea = isset($_POST['id_tarea']) ? $_POST['id_tarea'] : "";
    $id_tarea = mb_convert_encoding((string)($id_tarea), 'ISO-8859-1', 'UTF-8');

    $cod_usuario = isset($_POST['cod_usuario']) ? $_POST['cod_usuario'] : "";
    $cod_usuario = mb_convert_encoding((string)($cod_usuario), 'ISO-8859-1', 'UTF-8');

    $fecha_tarea = isset($_POST['fecha_tarea']) ? $_POST['fecha_tarea'] : "";
    $fecha_tarea = mb_convert_encoding((string)($fecha_tarea), 'ISO-8859-1', 'UTF-8');

    $tipo_destino = isset($_POST['tipo_destino']) ? $_POST['tipo_destino'] : "USUARIO";
    $tipo_destino = mb_convert_encoding((string)($tipo_destino), 'ISO-8859-1', 'UTF-8');

    $rol_operativo = isset($_POST['rol_operativo']) ? $_POST['rol_operativo'] : "";
    $rol_operativo = mb_convert_encoding((string)($rol_operativo), 'ISO-8859-1', 'UTF-8');

    asignarTareaAUsuario($id_tarea, $cod_usuario, $fecha_tarea, $tipo_destino, $rol_operativo);
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

    $tipo_destino = isset($_POST['tipo_destino']) ? $_POST['tipo_destino'] : "USUARIO";
    $tipo_destino = mb_convert_encoding((string)($tipo_destino), 'ISO-8859-1', 'UTF-8');

    $rol_operativo = isset($_POST['rol_operativoFK']) ? $_POST['rol_operativoFK'] : "";
    $rol_operativo = mb_convert_encoding((string)($rol_operativo), 'ISO-8859-1', 'UTF-8');

    buscarTareasParaAsignarDiariaUsuario($buscar, $tipo, $cod_usuario, $tipo_destino, $rol_operativo);
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

    $tipo_destino = isset($_POST['tipo_destino']) ? $_POST['tipo_destino'] : "USUARIO";
    $tipo_destino = mb_convert_encoding((string)($tipo_destino), 'ISO-8859-1', 'UTF-8');

    $rol_operativoFK = isset($_POST['rol_operativoFK']) ? $_POST['rol_operativoFK'] : "";
    $rol_operativoFK = mb_convert_encoding((string)($rol_operativoFK), 'ISO-8859-1', 'UTF-8');

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
        $cod_usuarioFK_create,
        $tipo_destino,
        $rol_operativoFK
    );
}
	
}

function buscarTareasParaAsignarDiariaUsuario($buscar, $tipo, $cod_usuario, $tipo_destino = "USUARIO", $rol_operativo = "")
{
    $mysqli = conectar_al_servidor();

    $buscar = mysqli_real_escape_string($mysqli, $buscar);
    $tipo = mysqli_real_escape_string($mysqli, $tipo);
    $cod_usuario = mysqli_real_escape_string($mysqli, $cod_usuario);
    $tipo_destino = normalizar_destino_tarea_programada($tipo_destino);
    $rol_operativo = mysqli_real_escape_string($mysqli, $rol_operativo);
    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");

    if ($tipo_destino == "USUARIO" && $cod_usuario == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($tipo_destino == "ROL" && $rol_operativo == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    $condicionBuscar = "";
    if ($buscar != "") {
        $condicionBuscar = " AND tp.nombre LIKE '%".$buscar."%'";
    }

    $condicionTipo = "";
    if ($tipo != "") {
        $condicionTipo = " AND tp.tipo = '".$tipo."'";
    }

    if ($tipo_destino == "ROL") {
        $joinAsignacion = "AND tpd.tipo_destino = 'ROL'
                AND tpd.rol_operativoFK = '".$rol_operativo."'";
    } else {
        $joinAsignacion = "AND tpd.cod_usuarioFK = '".$cod_usuario."'
                AND tpd.tipo_destino = 'USUARIO'";
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
                ".$joinAsignacion."
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

        $paginaDisponibles = "";
        $paginaConfiguradas = "";
        $totalDisponibles = 0;
        $totalConfiguradas = 0;

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
            $checkboxHtml = "
                <label class='tarea-diaria-modal__check' onclick='event.stopPropagation();'>
                    <input type='checkbox' onchange='seleccionarTareaDiariaParaAsignar(this)' />
                    <span></span>
                </label>";

            if ($cod_tarea_diaria != "" && $cod_tarea_diaria != NULL) {
                $claseCard .= " tarea-diaria-modal__card--asignada";
                $onclick = "";
                $textoEstado = "Ya configurada";
                $checkboxHtml = "<span class='tarea-diaria-modal__check tarea-diaria-modal__check--done'>&#10003;</span>";
            }

            $cardHtml = "
            <div 
                class='".$claseCard."'
                data-id='".$id_html."'
                data-nombre='".$nombre_html."'
                data-hora='".$hora_html."'
                data-tipo='".$tipo_html."'
                ".$onclick.">

                ".$checkboxHtml."

                <div class='tarea-diaria-modal__card-copy'>
                    <p class='tarea-diaria-modal__card-nombre'>".$nombre_html."</p>
                    <p class='tarea-diaria-modal__card-hora'>Hora: ".$hora_html."</p>
                </div>

                <span class='tarea-diaria-modal__badge'>".$tipo_html."</span>
                <span class='tarea-diaria-modal__estado-card'>".$textoEstado."</span>

            </div>";

            if ($cod_tarea_diaria != "" && $cod_tarea_diaria != NULL) {
                $paginaConfiguradas .= $cardHtml;
                $totalConfiguradas++;
            } else {
                $paginaDisponibles .= $cardHtml;
                $totalDisponibles++;
            }
        }

        $pagina .= "
        <div class='tarea-diaria-modal__task-columns'>
            <section class='tarea-diaria-modal__task-column'>
                <div class='tarea-diaria-modal__task-column-header'>
                    <h4>Tareas disponibles</h4>
                    <span>".$totalDisponibles."</span>
                </div>
                <div class='tarea-diaria-modal__grid'>".
                    ($paginaDisponibles != "" ? $paginaDisponibles : "<div class='tarea-diaria-modal__vacio tarea-diaria-modal__vacio--compacto'><p>No hay tareas disponibles.</p></div>").
                "</div>
            </section>

            <section class='tarea-diaria-modal__task-column'>
                <div class='tarea-diaria-modal__task-column-header'>
                    <h4>Ya configuradas</h4>
                    <span>".$totalConfiguradas."</span>
                </div>
                <div class='tarea-diaria-modal__grid'>".
                    ($paginaConfiguradas != "" ? $paginaConfiguradas : "<div class='tarea-diaria-modal__vacio tarea-diaria-modal__vacio--compacto'><p>No hay tareas configuradas.</p></div>").
                "</div>
            </section>
        </div>";

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
    $cod_usuarioFK_create,
    $tipo_destino = "USUARIO",
    $rol_operativoFK = ""
) {
    $tipo_destino = normalizar_destino_tarea_programada($tipo_destino);

    if ($cod_tareaFK == "" || $fecha_inicio == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($tipo_destino == "USUARIO" && $cod_usuarioFK == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($tipo_destino == "ROL" && $rol_operativoFK == "") {
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

    $rol_operativoFK = mysqli_real_escape_string($mysqli, $rol_operativoFK);

    if ($tipo_destino == "ROL") {
        $consultaVerificar = "SELECT cod_tarea_diaria 
                              FROM tareas_programadas_diarias
                              WHERE cod_tareaFK = ?
                              AND tipo_destino = 'ROL'
                              AND rol_operativoFK = ?
                              AND estado = 'Activo'
                              LIMIT 1";
        $parametroDestinoVerificar = $rol_operativoFK;
    } else {
        $consultaVerificar = "SELECT cod_tarea_diaria 
                              FROM tareas_programadas_diarias
                              WHERE cod_tareaFK = ?
                              AND cod_usuarioFK = ?
                              AND tipo_destino = 'USUARIO'
                              AND estado = 'Activo'
                              LIMIT 1";
        $parametroDestinoVerificar = $cod_usuarioFK;
    }

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
    $stmtVerificar->bind_param($ss, $cod_tareaFK, $parametroDestinoVerificar);

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
            "mensaje" => $tipo_destino == "ROL" ? "Esta tarea diaria ya está configurada para este rol." : "Esta tarea diaria ya está configurada para este usuario."
        );
        echo json_encode($informacion);
        exit;
    }

    $stmtVerificar->close();

    $consulta1 = "INSERT INTO tareas_programadas_diarias
                  (
                    cod_tareaFK,
                    cod_usuarioFK,
                    tipo_destino,
                    rol_operativoFK,
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
                  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, NULL)";

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

    if ($tipo_destino == "ROL") {
        $cod_usuarioFKInsert = NULL;
    } else {
        $cod_usuarioFKInsert = $cod_usuarioFK;
        $rol_operativoFK = "";
    }

    $ss = "sssssssssssssssss";

    $stmt1->bind_param(
        $ss,
        $cod_tareaFK,
        $cod_usuarioFKInsert,
        $tipo_destino,
        $rol_operativoFK,
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
    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");

    if ($tieneColumnasAsignadasRol) {
        $condicionTareaUsuario = "AND (
                          tpa.cod_usuarioFK = ?
                          OR (
                              tpa.tipo_asignacion = 'ROL'
                              AND tpa.rol_operativoFK = (
                                  SELECT TRIM(u.tipo)
                                  FROM usuario u
                                  WHERE u.cod_usuario = ?
                                  LIMIT 1
                              )
                              AND (tpa.cod_usuarioFK IS NULL OR tpa.cod_usuarioFK = '' OR tpa.cod_usuarioFK = '0')
                          )
                      )";
    } else {
        $condicionTareaUsuario = "AND tpa.cod_usuarioFK = ?";
    }

    if ($estado_tarea == "Completada") {

        $consulta1 = "UPDATE tareas_programadas_asignadas tpa
                      SET estado_tarea = ?,
                          fecha_completada = '".$fecha_insert."',
                          fecha_update = NOW()
                      WHERE cod_tarea_asignada = ?
                      ".$condicionTareaUsuario;

    } else {

        $consulta1 = "UPDATE tareas_programadas_asignadas tpa
                      SET estado_tarea = ?,
                          fecha_completada = '".$fecha_insert."',
                          fecha_update = NOW()
                      WHERE cod_tarea_asignada = ?
                      ".$condicionTareaUsuario;
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

    if ($tieneColumnasAsignadasRol) {
        $ss = "ssss";
        $stmt1->bind_param($ss, $estado_tarea, $cod_tarea_asignada, $cod_usuario, $cod_usuario);
    } else {
        $ss = "sss";
        $stmt1->bind_param($ss, $estado_tarea, $cod_tarea_asignada, $cod_usuario);
    }

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
    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");

    if ($tieneColumnasAsignadasRol) {
        $selectAsignacionRol = ",
                tpa.tipo_asignacion,
                tpa.rol_operativoFK";
        $condicionTareasUsuario = "(
                tpa.cod_usuarioFK = ?
                OR (
                    tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = (
                        SELECT TRIM(u.tipo)
                        FROM usuario u
                        WHERE u.cod_usuario = ?
                        LIMIT 1
                    )
                    AND (tpa.cod_usuarioFK IS NULL OR tpa.cod_usuarioFK = '' OR tpa.cod_usuarioFK = '0')
                )
            )";
    } else {
        $selectAsignacionRol = ",
                'USUARIO' AS tipo_asignacion,
                '' AS rol_operativoFK";
        $condicionTareasUsuario = "tpa.cod_usuarioFK = ?";
    }

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
                ".$selectAsignacionRol."
            FROM tareas_programadas_asignadas tpa
            INNER JOIN tareas_programadas tp 
                ON tp.id = tpa.cod_tareaFK
            WHERE ".$condicionTareasUsuario."
            AND tpa.fecha_tarea = ?
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
 
    if ($tieneColumnasAsignadasRol) {
        $ss = "sss";
        $stmt->bind_param($ss, $cod_usuario, $cod_usuario, $fecha_actual);
    } else {
        $ss = "ss";
        $stmt->bind_param($ss, $cod_usuario, $fecha_actual);
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
            $tipo_asignacion = isset($valor['tipo_asignacion']) ? mb_convert_encoding((string)($valor['tipo_asignacion']), 'UTF-8', 'ISO-8859-1') : "USUARIO";
            $rol_operativoFK = isset($valor['rol_operativoFK']) ? mb_convert_encoding((string)($valor['rol_operativoFK']), 'UTF-8', 'ISO-8859-1') : "";

            $nombre_html = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
            $hora_html = htmlspecialchars($hora, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
            $observacion_admin_html = htmlspecialchars($observacion_admin, ENT_QUOTES, 'UTF-8');
            $fecha_insert_html = htmlspecialchars($fecha_insert, ENT_QUOTES, 'UTF-8');
            $fecha_completada_html = htmlspecialchars($fecha_completada, ENT_QUOTES, 'UTF-8');
            $rol_operativo_html = htmlspecialchars($rol_operativoFK, ENT_QUOTES, 'UTF-8');

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
                        <span>Asignada: ".$fecha_insert_html."</span>";

                    if ($tipo_asignacion == "ROL" && $rol_operativo_html != "") {
                        $pagina .= "
                        <span>Rol: ".$rol_operativo_html."</span>";
                    }

                    $pagina .= "
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
                      WHERE ".$condicionTareasUsuario."
                      AND tpa.fecha_tarea = ?
                      AND visto = 'No'";

        $stmtUpdate = $mysqli->prepare($sqlUpdate);
        if ($stmtUpdate) {
            if ($tieneColumnasAsignadasRol) {
                $s = "sss";
                $stmtUpdate->bind_param($s, $cod_usuario, $cod_usuario, $fecha_actual);
            } else {
                $s = "ss";
                $stmtUpdate->bind_param($s, $cod_usuario, $fecha_actual);
            }
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


function buscarTareasParaAsignarUsuario($buscar, $tipo, $estado, $cod_usuario, $tipo_destino = "USUARIO", $rol_operativo = "")
{
    $mysqli = conectar_al_servidor();

    $buscar = mysqli_real_escape_string($mysqli, $buscar);
    $tipo = mysqli_real_escape_string($mysqli, $tipo);
    $estado = mysqli_real_escape_string($mysqli, $estado);
    $cod_usuario = mysqli_real_escape_string($mysqli, $cod_usuario);
    $tipo_destino = normalizar_destino_tarea_programada($tipo_destino);
    $rol_operativo = mysqli_real_escape_string($mysqli, $rol_operativo);

    if ($tipo_destino == "USUARIO" && $cod_usuario == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($tipo_destino == "ROL" && $rol_operativo == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

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
    if ($tipo_destino == "ROL" && $tieneColumnasAsignadasRol) {
        $joinAsignacion = "LEFT JOIN (
                SELECT 
                    cod_tareaFK,
                    MIN(cod_tarea_asignada) AS cod_tarea_asignada,
                    MIN(estado_tarea) AS estado_tarea,
                    MIN(visto) AS visto,
                    MIN(fecha_insert) AS fecha_insert
                FROM tareas_programadas_asignadas
                WHERE tipo_asignacion = 'ROL'
                AND rol_operativoFK = '".$rol_operativo."'
                AND estado_tarea IN ('Pendiente','En Proceso')
                GROUP BY cod_tareaFK
            ) tpa ON tpa.cod_tareaFK = tp.id";
    } else if ($tipo_destino == "ROL") {
        $joinAsignacion = "LEFT JOIN (
                SELECT 
                    NULL AS cod_tareaFK,
                    NULL AS cod_tarea_asignada,
                    NULL AS estado_tarea,
                    NULL AS visto,
                    NULL AS fecha_insert
            ) tpa ON tpa.cod_tareaFK = tp.id";
    } else {
        $joinAsignacion = "LEFT JOIN tareas_programadas_asignadas tpa
                ON tpa.cod_tareaFK = tp.id
                AND tpa.cod_usuarioFK = '".$cod_usuario."'
                AND tpa.tipo_asignacion = 'USUARIO'
                AND tpa.estado_tarea IN ('Pendiente','En Proceso')";
    }

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

            ".$joinAsignacion."

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
function asignarTareaAUsuario($id_tarea, $cod_usuario, $fecha_tarea, $tipo_destino = "USUARIO", $rol_operativo = "")
{
    $tipo_destino = normalizar_destino_tarea_programada($tipo_destino);

    if ($id_tarea == "" || $fecha_tarea == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($tipo_destino == "USUARIO" && $cod_usuario == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    if ($tipo_destino == "ROL" && $rol_operativo == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    $mysqli = conectar_al_servidor();

    $fecha_tarea = mysqli_real_escape_string($mysqli, $fecha_tarea);
    $rol_operativo = mysqli_real_escape_string($mysqli, $rol_operativo);

    if ($tipo_destino == "ROL") {
        $usuariosDestino = buscar_usuarios_por_rol_tarea_programada($mysqli, $rol_operativo);

        if (count($usuariosDestino) == 0) {
            mysqli_close($mysqli);

            $informacion = array(
                "1" => "sinusuarios",
                "mensaje" => "No se encontraron usuarios activos para este rol."
            );
            echo json_encode($informacion);
            exit;
        }
    } else {
        $usuariosDestino = array($cod_usuario);
        $rol_operativo = "";
    }

    $estado_tarea = "Pendiente";
    $visto = "No";
    $fecha_insert = date("Y-m-d H:i:s");
    $insertados = 0;
    $duplicados = 0;

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

    $consulta1 = "INSERT INTO tareas_programadas_asignadas
                  (
                    cod_tareaFK,
                    cod_usuarioFK,
                    tipo_asignacion,
                    rol_operativoFK,
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
                  (?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, ?, NULL)";

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

    foreach ($usuariosDestino as $cod_usuario_destino) {
        $ss = "sss";
        $stmtVerificar->bind_param($ss, $id_tarea, $cod_usuario_destino, $fecha_tarea);

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
            $duplicados++;
            $resultVerificar->free();
            continue;
        }

        $resultVerificar->free();

        $ss = "ssssssss";
        $stmt1->bind_param(
            $ss,
            $id_tarea,
            $cod_usuario_destino,
            $tipo_destino,
            $rol_operativo,
            $estado_tarea,
            $visto,
            $fecha_tarea,
            $fecha_insert
        );

        if ($stmt1->execute()) {
            $insertados++;
        } else {
            $informacion = array(
                "1" => "error",
                "mensaje" => "Error al asignar tarea: " . $stmt1->error,
                "sql" => $consulta1
            );
            echo json_encode($informacion);
            exit;
        }
    }

    if ($insertados == 0) {
        $stmtVerificar->close();
        $stmt1->close();
        mysqli_close($mysqli);

        $informacion = array(
            "1" => "duplicado",
            "mensaje" => $tipo_destino == "ROL" ? "Esta tarea ya está asignada a todos los usuarios activos del rol en la fecha seleccionada." : "Esta tarea ya está asignada a este usuario en la fecha seleccionada.",
            "insertados" => $insertados,
            "duplicados" => $duplicados
        );
        echo json_encode($informacion);
        exit;
    }

    $stmtVerificar->close();
    $stmt1->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "insertados" => $insertados, "duplicados" => $duplicados);
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

function buscarUsuariosAsignarTarea($buscar, $tipo, $estado, $rol_operativo = "")
{
    $mysqli = conectar_al_servidor();

    $buscar = mysqli_real_escape_string($mysqli, $buscar);
    $tipo = mysqli_real_escape_string($mysqli, $tipo);
    $estado = mysqli_real_escape_string($mysqli, $estado);
    $rol_operativo = mysqli_real_escape_string($mysqli, $rol_operativo);

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

    $condicionRol = "";
    if ($rol_operativo != "") {
        $condicionRol = " and u.tipo like '%".$rol_operativo."%'";
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
				Nombre,
                IFNULL((
                    SELECT COUNT(*) 
                    FROM tareas_programadas_asignadas tpa 
                    WHERE tpa.cod_usuarioFK = u.cod_usuario
                    AND tpa.fecha_tarea = CURDATE()
                    AND tpa.estado_tarea IN ('Pendiente','En Proceso')
                ), 0) AS tareas_pendientes_hoy,
                IFNULL((
                    SELECT COUNT(*) 
                    FROM tareas_programadas_asignadas tpa 
                    WHERE tpa.cod_usuarioFK = u.cod_usuario
                    AND tpa.fecha_tarea = CURDATE()
                    AND tpa.estado_tarea = 'Completada'
                ), 0) AS tareas_completadas_hoy,
                IFNULL((
                    SELECT CONCAT(TIME_FORMAT(hu.hora_entrada, '%H:%i'), ' a ', TIME_FORMAT(hu.hora_salida, '%H:%i'))
                    FROM horario_usuario hu
                    WHERE hu.cod_usuarioFK = u.cod_usuario
                    AND (hu.cod_localFK = u.cod_localFK OR hu.cod_localFK IS NULL)
                    ORDER BY hu.dia_semana ASC
                    LIMIT 1
                ), '') AS horario_operativo
            FROM usuario u
			inner join persona on cod_persona=cod_usuario
			inner join local on cod_local=cod_localFK
            WHERE 1=1 
            ".$condicionBuscar."
            ".$condicionTipo."
            ".$condicionRol."
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
            $tareas_pendientes_hoy = isset($valor['tareas_pendientes_hoy']) ? (int)$valor['tareas_pendientes_hoy'] : 0;
            $tareas_completadas_hoy = isset($valor['tareas_completadas_hoy']) ? (int)$valor['tareas_completadas_hoy'] : 0;
            $horario_operativo = isset($valor['horario_operativo']) ? mb_convert_encoding((string)($valor['horario_operativo']), 'UTF-8', 'ISO-8859-1') : "";

            $url = resolverFotoUsuarioAsignarTarea($cod_usuario, $url);

            $login_html = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
            $nombre_persona_html = htmlspecialchars($nombre_persona, ENT_QUOTES, 'UTF-8');
            $rut_html = htmlspecialchars($rut_usuario, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipoUsuario, ENT_QUOTES, 'UTF-8');
            $NombreLocal_html = htmlspecialchars($Nombre, ENT_QUOTES, 'UTF-8');
            $url_html = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $horario_html = htmlspecialchars(($horario_operativo != "" ? $horario_operativo : "No configurado"), ENT_QUOTES, 'UTF-8');
            $tareas_pendientes_html = htmlspecialchars((string)$tareas_pendientes_hoy, ENT_QUOTES, 'UTF-8');
            $tareas_completadas_html = htmlspecialchars((string)$tareas_completadas_hoy, ENT_QUOTES, 'UTF-8');

            $onclick_js = "seleccionarUsuarioAsignarTarea(" .
                json_encode((string)$cod_usuario) . "," .
                json_encode($nombre_persona) . "," .
                json_encode($url) . "," .
                json_encode($rut_usuario) . "," .
                json_encode($Nombre) . "," .
                json_encode($tipoUsuario) . "," .
                json_encode(($horario_operativo != "" ? $horario_operativo : "No configurado")) . "," .
                json_encode((string)$tareas_pendientes_hoy) . "," .
                json_encode((string)$tareas_completadas_hoy) .
            ")";
            $onclick_html = htmlspecialchars($onclick_js, ENT_QUOTES, 'UTF-8');

            $pagina .= "
            <div 
                class='asignar-tarea__card asignar-tarea__user-row' 
                id='usuarioAsignarTarea_".$cod_usuario."'
                onclick=\"".$onclick_html."\">

                <div class='asignar-tarea__user-main'>
                    <img src='".$url_html."' class='asignar-tarea__foto' onerror=\"this.src='/GoodVentaAsisCap/iconos/user.png'\" />

                    <div class='asignar-tarea__user-copy'>
                        <p class='asignar-tarea__nombre'>".$nombre_persona_html."</p>
                        <p class='asignar-tarea__login'>CI: ".$rut_html."</p>
                    </div>
                </div>

                <div class='asignar-tarea__user-meta'>
                    <span class='asignar-tarea__badge'>".$tipo_html."</span>
                    <span class='asignar-tarea__chip'>".$NombreLocal_html."</span>
                    <span class='asignar-tarea__chip'>".$horario_html."</span>
                </div>

                <div class='asignar-tarea__user-stats'>
                    <span><strong>".$tareas_pendientes_html."</strong> pendientes</span>
                    <span><strong>".$tareas_completadas_html."</strong> completadas</span>
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

function buscarRolesAsignarTarea($buscar, $estado = "")
{
    $mysqli = conectar_al_servidor();

    $buscar = mysqli_real_escape_string($mysqli, $buscar);
    $estado = mysqli_real_escape_string($mysqli, $estado);
    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");
    $tieneColumnasDiariasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_diarias", "tipo_destino")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_diarias", "rol_operativoFK");

    $condicionBuscar = "";
    if ($buscar != "") {
        $condicionBuscar = " AND TRIM(u.tipo) LIKE '%".$buscar."%'";
    }

    $condicionEstado = "";
    if ($estado != "") {
        $condicionEstado = " AND u.estado = '".$estado."'";
    }

    if ($tieneColumnasAsignadasRol) {
        $selectPendientesHoy = "IFNULL((
                    SELECT COUNT(*)
                    FROM tareas_programadas_asignadas tpa
                    WHERE tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = TRIM(u.tipo)
                    AND tpa.fecha_tarea = CURDATE()
                    AND tpa.estado_tarea IN ('Pendiente','En Proceso')
                ), 0)";

        $selectCompletadasHoy = "IFNULL((
                    SELECT COUNT(*)
                    FROM tareas_programadas_asignadas tpa
                    WHERE tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = TRIM(u.tipo)
                    AND tpa.fecha_tarea = CURDATE()
                    AND tpa.estado_tarea = 'Completada'
                ), 0)";

        $selectTareasHoy = "IFNULL((
                    SELECT GROUP_CONCAT(DISTINCT CONCAT(tp.nombre, ' (', tpa.estado_tarea, ')') ORDER BY tp.hora ASC, tp.nombre ASC SEPARATOR '||')
                    FROM tareas_programadas_asignadas tpa
                    INNER JOIN tareas_programadas tp ON tp.id = tpa.cod_tareaFK
                    WHERE tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = TRIM(u.tipo)
                    AND tpa.fecha_tarea = CURDATE()
                ), '')";
    } else {
        $selectPendientesHoy = "0";
        $selectCompletadasHoy = "0";
        $selectTareasHoy = "''";
    }

    if ($tieneColumnasDiariasRol) {
        $selectTareasDiarias = "IFNULL((
                    SELECT GROUP_CONCAT(DISTINCT tp.nombre ORDER BY tp.hora ASC, tp.nombre ASC SEPARATOR '||')
                    FROM tareas_programadas_diarias tpd
                    INNER JOIN tareas_programadas tp ON tp.id = tpd.cod_tareaFK
                    WHERE tpd.tipo_destino = 'ROL'
                    AND tpd.rol_operativoFK = TRIM(u.tipo)
                    AND tpd.estado = 'Activo'
                ), '')";
    } else {
        $selectTareasDiarias = "''";
    }

    $sql = "SELECT
                TRIM(u.tipo) AS rol_operativo,
                COUNT(*) AS total_usuarios,
                SUM(CASE WHEN u.estado = 'Activo' THEN 1 ELSE 0 END) AS usuarios_activos,
                SUM(CASE WHEN u.estado = 'Inactivo' THEN 1 ELSE 0 END) AS usuarios_inactivos,
                ".$selectPendientesHoy." AS tareas_pendientes_hoy,
                ".$selectCompletadasHoy." AS tareas_completadas_hoy,
                ".$selectTareasHoy." AS tareas_hoy,
                ".$selectTareasDiarias." AS tareas_diarias
            FROM usuario u
            WHERE TRIM(u.tipo) <> ''
            ".$condicionBuscar."
            ".$condicionEstado."
            GROUP BY TRIM(u.tipo)
            ORDER BY TRIM(u.tipo) ASC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar búsqueda de roles: " . $mysqli->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }

    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al buscar roles: " . $stmt->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();
    $nroRegistro = mysqli_num_rows($result);
    $pagina = "";

    if ($nroRegistro > 0) {
        $pagina .= "<div class='asignar-tarea__grid'>";

        while ($valor = mysqli_fetch_assoc($result)) {
            $rol_operativo = mb_convert_encoding((string)($valor['rol_operativo']), 'UTF-8', 'ISO-8859-1');
            $total_usuarios = isset($valor['total_usuarios']) ? (int)$valor['total_usuarios'] : 0;
            $usuarios_activos = isset($valor['usuarios_activos']) ? (int)$valor['usuarios_activos'] : 0;
            $usuarios_inactivos = isset($valor['usuarios_inactivos']) ? (int)$valor['usuarios_inactivos'] : 0;
            $tareas_pendientes_hoy = isset($valor['tareas_pendientes_hoy']) ? (int)$valor['tareas_pendientes_hoy'] : 0;
            $tareas_completadas_hoy = isset($valor['tareas_completadas_hoy']) ? (int)$valor['tareas_completadas_hoy'] : 0;
            $tareas_hoy = isset($valor['tareas_hoy']) ? mb_convert_encoding((string)($valor['tareas_hoy']), 'UTF-8', 'ISO-8859-1') : "";
            $tareas_diarias = isset($valor['tareas_diarias']) ? mb_convert_encoding((string)($valor['tareas_diarias']), 'UTF-8', 'ISO-8859-1') : "";

            $rol_html = htmlspecialchars($rol_operativo, ENT_QUOTES, 'UTF-8');
            $tareas_hoy_texto = str_replace("||", ", ", $tareas_hoy);
            $tareas_diarias_texto = str_replace("||", ", ", $tareas_diarias);
            $tareas_hoy_html = htmlspecialchars($tareas_hoy_texto != "" ? $tareas_hoy_texto : "Sin tareas para hoy", ENT_QUOTES, 'UTF-8');
            $tareas_diarias_html = htmlspecialchars($tareas_diarias_texto != "" ? $tareas_diarias_texto : "Sin tareas diarias configuradas", ENT_QUOTES, 'UTF-8');
            $idRol = md5($rol_operativo);

            $onclick_js = "seleccionarRolAsignarTarea(" .
                json_encode($rol_operativo) . "," .
                json_encode((string)$total_usuarios) . "," .
                json_encode((string)$usuarios_activos) . "," .
                json_encode((string)$usuarios_inactivos) . "," .
                json_encode((string)$tareas_pendientes_hoy) . "," .
                json_encode((string)$tareas_completadas_hoy) . "," .
                json_encode($tareas_hoy_texto) . "," .
                json_encode($tareas_diarias_texto) .
            ")";
            $onclick_html = htmlspecialchars($onclick_js, ENT_QUOTES, 'UTF-8');

            $pagina .= "
            <div 
                class='asignar-tarea__card asignar-tarea__user-row asignar-tarea__role-row' 
                id='rolAsignarTarea_".$idRol."'
                data-rol='".$rol_html."'
                onclick=\"".$onclick_html."\">

                <div class='asignar-tarea__user-main'>
                    <div class='asignar-tarea__role-icon'>R</div>
                    <div class='asignar-tarea__user-copy'>
                        <p class='asignar-tarea__nombre'>".$rol_html."</p>
                        <p class='asignar-tarea__login'>".$usuarios_activos." activos / ".$total_usuarios." usuarios</p>
                    </div>
                </div>

                <div class='asignar-tarea__user-meta'>
                    <span class='asignar-tarea__badge'>ROL</span>
                    <span class='asignar-tarea__chip' title='".$tareas_hoy_html."'>Hoy: ".$tareas_hoy_html."</span>
                    <span class='asignar-tarea__chip' title='".$tareas_diarias_html."'>Diarias: ".$tareas_diarias_html."</span>
                </div>

                <div class='asignar-tarea__user-stats'>
                    <span><strong>".$tareas_pendientes_hoy."</strong> pendientes</span>
                    <span><strong>".$tareas_completadas_hoy."</strong> completadas</span>
                </div>

            </div>";
        }

        $pagina .= "</div>";
    } else {
        $pagina .= "
        <div class='asignar-tarea__vacio'>
            <p>No se encontraron roles.</p>
        </div>";
    }

    $stmt->close();
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

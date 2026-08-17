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

function normalizar_fecha_realizado_tarea_programada($fecha)
{
    $fecha = trim((string)$fecha);
    if ($fecha === '') {
        return null;
    }

    $fecha = str_replace('T', ' ', $fecha);
    $formatos = array('Y-m-d H:i:s', 'Y-m-d H:i');

    foreach ($formatos as $formato) {
        $fechaValida = DateTime::createFromFormat('!'.$formato, $fecha);
        $errores = DateTime::getLastErrors();
        $sinErrores = $errores === false
            || ((int)$errores['warning_count'] === 0 && (int)$errores['error_count'] === 0);

        if ($fechaValida !== false && $sinErrores && $fechaValida->format($formato) === $fecha) {
            return $fechaValida->format('Y-m-d H:i:s');
        }
    }

    return false;
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
            AND TRIM(tipo) = ?
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

function condicionar_rol_operativo_activo_tarea_programada($alias = "ln")
{
    return " AND ".$alias.".tipo = 'Administrativo'
             AND ".$alias.".estado = 'Activo'
             AND UPPER(TRIM(".$alias.".nombre)) <> 'SIN ACCESO'";
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

function normalizar_tipo_tarea_personal($tipo)
{
    $tipo = strtoupper(trim((string)$tipo));

    if ($tipo == "DIARIA") {
        $tipo = "DIARIO";
    }

    if ($tipo == "PUNTUAL") {
        $tipo = "CASUAL";
    }

    if ($tipo != "DIARIO" && $tipo != "CASUAL" && $tipo != "RAPIDA") {
        $tipo = "CASUAL";
    }

    return $tipo;
}

function etiqueta_tipo_tarea_personal($tipo, $observacion = "")
{
    $tipo = normalizar_tipo_tarea_personal($tipo);
    $observacionPlano = strtolower(str_replace("\xc3\xa1", "a", (string)$observacion));

    if ($tipo == "RAPIDA" || strpos($observacionPlano, "tarea rapida") !== false) {
        return array("clave" => "rapidas", "texto" => "Rapida");
    }

    if ($tipo == "DIARIO") {
        return array("clave" => "diarias", "texto" => "Diaria");
    }

    return array("clave" => "puntuales", "texto" => "Puntual");
}

function etiqueta_prioridad_tarea_personal($prioridad)
{
    $prioridad = strtoupper(trim((string)$prioridad));

    if ($prioridad == "IMPORTANTE" || $prioridad == "ALTA") {
        return "Importante";
    }

    if ($prioridad == "CRITICA" || $prioridad == "CRITICA" || $prioridad == "URGENTE") {
        return "Critica";
    }

    return "Normal";
}

function construir_observacion_tarea_personal($prioridad, $comentario, $origen)
{
    $partes = array();
    $origen = trim((string)$origen);
    $comentario = trim((string)$comentario);
    $prioridad = etiqueta_prioridad_tarea_personal($prioridad);

    if ($origen != "") {
        $partes[] = "Origen: ".$origen;
    }

    $partes[] = "Prioridad: ".$prioridad;

    if ($comentario != "") {
        $partes[] = "Comentario: ".$comentario;
    }

    return implode(" | ", $partes);
}

function extraer_meta_tarea_personal($observacion, $tipo_texto)
{
    $meta = array(
        "prioridad" => "Normal",
        "origen" => "",
        "comentario" => ""
    );

    $partes = explode("|", (string)$observacion);

    foreach ($partes as $parte) {
        $parte = trim($parte);
        $parteLower = strtolower($parte);

        if (strpos($parteLower, "prioridad:") === 0) {
            $meta["prioridad"] = trim(substr($parte, strlen("Prioridad:")));
        } else if (strpos($parteLower, "origen:") === 0) {
            $meta["origen"] = trim(substr($parte, strlen("Origen:")));
        } else if (strpos($parteLower, "comentario:") === 0) {
            $meta["comentario"] = trim(substr($parte, strlen("Comentario:")));
        }
    }

    if ($meta["prioridad"] == "") {
        $meta["prioridad"] = "Normal";
    }

    if ($meta["origen"] == "") {
        $meta["origen"] = strtolower($tipo_texto) == "rapida" ? "Funcionario" : "Administracion";
    }

    $meta["prioridad"] = ucfirst(strtolower($meta["prioridad"]));
    $meta["origen"] = ucfirst(strtolower($meta["origen"]));

    return $meta;
}

function obtener_html_tareas_usuario_gestion_diaria($mysqli, $cod_usuario, $nombre_responsable, $rol_operativo = "")
{
    date_default_timezone_set('America/Asuncion');

    $fecha_actual = date("Y-m-d");
    $momento_actual = time();
    $cod_usuario = trim((string)$cod_usuario);
    $rol_operativo = trim((string)$rol_operativo);
    $nombre_responsable = trim((string)$nombre_responsable);

    $resumen = array(
        "html" => "",
        "total" => 0,
        "pendientes" => 0,
        "completadas" => 0,
        "atrasadas" => 0,
        "proceso" => 0,
        "canceladas" => 0,
        "diarias" => 0,
        "puntuales" => 0,
        "rapidas" => 0
    );

    if ($cod_usuario == "") {
        return $resumen;
    }

    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");

    if ($tieneColumnasAsignadasRol) {
        $selectAsignacion = ",
                tpa.tipo_asignacion,
                tpa.rol_operativoFK";
        $condicionUsuario = "(
                tpa.cod_usuarioFK = ?
                OR (
                    tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = ?
                    AND (tpa.cod_usuarioFK IS NULL OR tpa.cod_usuarioFK = '' OR tpa.cod_usuarioFK = '0')
                )
            )";
        $tipos = "sss";
        $parametros = array($cod_usuario, $rol_operativo, $fecha_actual);
    } else {
        $selectAsignacion = ",
                'USUARIO' AS tipo_asignacion,
                '' AS rol_operativoFK";
        $condicionUsuario = "tpa.cod_usuarioFK = ?";
        $tipos = "ss";
        $parametros = array($cod_usuario, $fecha_actual);
    }

    $sql = "SELECT
                tpa.cod_tarea_asignada,
                tpa.estado_tarea,
                tpa.observacion_admin,
                tpa.observacion_usuario,
                tpa.fecha_completada,
                DATE_FORMAT(tpa.fecha_completada, '%H:%i') AS hora_completada_format,
                DATE_FORMAT(tpa.fecha_insert, '%d/%m %H:%i') AS fecha_insert_format,
                tp.nombre,
                TIME_FORMAT(tp.hora, '%H:%i') AS hora_format,
                tp.tipo
                ".$selectAsignacion."
            FROM tareas_programadas_asignadas tpa
            INNER JOIN tareas_programadas tp
                ON tp.id = tpa.cod_tareaFK
            WHERE ".$condicionUsuario."
            AND tpa.fecha_tarea = ?
            ORDER BY
                CASE
                    WHEN tpa.estado_tarea = 'Pendiente' AND tp.hora IS NOT NULL AND CONCAT(tpa.fecha_tarea, ' ', tp.hora) < NOW() THEN 1
                    WHEN tpa.estado_tarea = 'Pendiente' THEN 2
                    WHEN tpa.estado_tarea = 'En Proceso' THEN 3
                    WHEN tpa.estado_tarea = 'Completada' THEN 4
                    ELSE 5
                END,
                CASE WHEN tp.hora IS NULL THEN 1 ELSE 0 END,
                tp.hora ASC,
                tpa.fecha_insert DESC";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        return $resumen;
    }

    bind_param_tarea_programada($stmt, $tipos, $parametros);

    if (!$stmt->execute()) {
        $stmt->close();
        return $resumen;
    }

    $result = $stmt->get_result();

    $grupos = array(
        "diarias" => "",
        "puntuales" => "",
        "rapidas" => ""
    );

    while ($valor = mysqli_fetch_assoc($result)) {
        $resumen["total"]++;

        $cod_tarea_asignada = (int)$valor["cod_tarea_asignada"];
        $nombre = convertir_utf8_tarea_programada($valor["nombre"]);
        $hora = convertir_utf8_tarea_programada($valor["hora_format"]);
        $tipo = convertir_utf8_tarea_programada($valor["tipo"]);
        $estado_tarea = convertir_utf8_tarea_programada($valor["estado_tarea"]);
        $observacion_admin = convertir_utf8_tarea_programada($valor["observacion_admin"]);
        $observacion_usuario = convertir_utf8_tarea_programada($valor["observacion_usuario"]);
        $fecha_insert = convertir_utf8_tarea_programada($valor["fecha_insert_format"]);
        $hora_completada = convertir_utf8_tarea_programada($valor["hora_completada_format"]);
        $fecha_completada_raw = isset($valor["fecha_completada"]) ? (string)$valor["fecha_completada"] : "";

        if ($hora == "") {
            $hora = "--:--";
        }

        $momento_tarea = false;
        if ($hora != "--:--") {
            $momento_tarea = strtotime($fecha_actual." ".$hora.":00");
        }

        $estaAtrasada = ($estado_tarea == "Pendiente" && $momento_tarea !== false && $momento_tarea < $momento_actual);
        $tipoInfo = etiqueta_tipo_tarea_personal($tipo, $observacion_admin." ".$observacion_usuario);
        $metaTarea = extraer_meta_tarea_personal($observacion_admin != "" ? $observacion_admin : $observacion_usuario, $tipoInfo["texto"]);
        $completadaTarde = false;

        if ($estado_tarea == "Completada" && $momento_tarea !== false && $fecha_completada_raw != "") {
            $momentoCompletada = strtotime($fecha_completada_raw);
            $completadaTarde = $momentoCompletada !== false && $momentoCompletada > $momento_tarea;
        }

        if (isset($resumen[$tipoInfo["clave"]])) {
            $resumen[$tipoInfo["clave"]]++;
        }

        $textoEstado = "Pendiente";
        $claseEstado = "asignar-tarea__task-status--pendiente";
        $claseFila = "asignar-tarea__task-row";

        if ($estado_tarea == "Pendiente") {
            $resumen["pendientes"]++;
        }

        if ($estado_tarea == "En Proceso") {
            $resumen["proceso"]++;
            $textoEstado = "En proceso";
            $claseEstado = "asignar-tarea__task-status--proceso";
        }

        if ($estado_tarea == "Completada") {
            $resumen["completadas"]++;
            $textoEstado = $completadaTarde ? "Completada tarde" : "Completada";
            if ($hora_completada != "") {
                $textoEstado .= " ".$hora_completada;
            }
            $claseEstado = "asignar-tarea__task-status--completada";
            $claseFila .= " asignar-tarea__task-row--completada";
            if ($completadaTarde) {
                $claseEstado = "asignar-tarea__task-status--completada-tarde";
                $claseFila .= " asignar-tarea__task-row--completada-tarde";
            }
        }

        if ($estado_tarea == "Cancelada") {
            $resumen["canceladas"]++;
            $textoEstado = "Cancelada";
            $claseEstado = "asignar-tarea__task-status--cancelada";
            $claseFila .= " asignar-tarea__task-row--cancelada";
        }

        if ($estaAtrasada) {
            $resumen["atrasadas"]++;
            $textoEstado = "Atrasada";
            $claseEstado = "asignar-tarea__task-status--atrasada";
            $claseFila .= " asignar-tarea__task-row--atrasada";
        }

        $nombre_html = limpiar_html_tarea_programada($nombre);
        $hora_html = limpiar_html_tarea_programada($hora);
        $tipo_html = limpiar_html_tarea_programada($tipoInfo["texto"]);
        $estado_html = limpiar_html_tarea_programada($textoEstado);
        $responsable_html = limpiar_html_tarea_programada($nombre_responsable);
        $fecha_insert_html = limpiar_html_tarea_programada($fecha_insert);
        $hora_completada_html = limpiar_html_tarea_programada($hora_completada);
        $prioridad_html = limpiar_html_tarea_programada("Prioridad ".strtolower($metaTarea["prioridad"]));
        $origen_html = limpiar_html_tarea_programada($metaTarea["origen"]);
        $comentario_html = limpiar_html_tarea_programada($metaTarea["comentario"]);

        $accionHtml = "<span class='asignar-tarea__task-action asignar-tarea__task-action--done' title='Tarea completada'>&#10003;</span>";

        if ($estado_tarea != "Completada" && $estado_tarea != "Cancelada") {
            $accionHtml = "
                <label class='asignar-tarea__task-check' title='Marcar como realizada'>
                    <input type='checkbox' onchange='event.stopPropagation();cambiarEstadoTareaAsignada(this, ".$cod_tarea_asignada.", ".$cod_usuario.")'>
                    <span></span>
                </label>";
        }

        $detalleObservacion = "";
        if ($comentario_html != "") {
            $detalleObservacion = "<small class='asignar-tarea__task-note'>".$comentario_html."</small>";
        }

        $grupos[$tipoInfo["clave"]] .= "
            <div class='".$claseFila."'>
                <span class='asignar-tarea__task-time'>".$hora_html."</span>
                <div class='asignar-tarea__task-copy'>
                    <strong>".$nombre_html."</strong>
                    <small>
                        <span>".$tipo_html."</span>
                        <span>".$prioridad_html."</span>
                        <span>".$origen_html."</span>
                        <span>Responsable: ".$responsable_html."</span>
                        <span>Asignada ".$fecha_insert_html."</span>
                    </small>
                    ".$detalleObservacion."
                </div>
                <span class='asignar-tarea__task-status ".$claseEstado."'>".$estado_html."</span>
                ".$accionHtml."
            </div>";
    }

    $stmt->close();

    $secciones = array(
        array("clave" => "diarias", "titulo" => "Tareas diarias / fijas", "vacio" => "Sin tareas fijas para hoy."),
        array("clave" => "puntuales", "titulo" => "Tareas puntuales", "vacio" => "Sin tareas puntuales cargadas."),
        array("clave" => "rapidas", "titulo" => "Tareas agregadas por el funcionario", "vacio" => "Sin tareas rapidas agregadas.")
    );

    $totalDetalle = (int)$resumen["total"];
    $resumenTexto = $totalDetalle > 0
        ? $totalDetalle." tareas - ".$resumen["completadas"]." completadas - ".$resumen["pendientes"]." pendientes - ".$resumen["atrasadas"]." atrasadas"
        : "Sin tareas para hoy";
    $resumenTextoHtml = limpiar_html_tarea_programada($resumenTexto);
    $cod_usuario_html = limpiar_html_tarea_programada($cod_usuario);

    $html = "
    <div class='asignar-tarea__task-panel'>
        <div class='asignar-tarea__task-panel-head'>
            <div>
                <h3>Tareas del dia</h3>
                <span>".$resumenTextoHtml."</span>
            </div>
            <button type='button' class='asignar-tarea__inline-add-btn' onclick='event.stopPropagation();verFormularioTareaFuncionario(".$cod_usuario_html.", true)'>+ Agregar tarea</button>
        </div>

        <div class='asignar-tarea__inline-form' id='formAgregarTareaFuncionario_".$cod_usuario_html."' style='display:none;' onclick='event.stopPropagation();'>
            <div class='asignar-tarea__inline-grid'>
                <input type='text' class='inputText' id='inptTituloTareaFuncionario_".$cod_usuario_html."' placeholder='Titulo de la tarea'>
                <input type='time' class='inputText' id='inptHoraTareaFuncionario_".$cod_usuario_html."'>
                <select class='inputText' id='inptTipoTareaFuncionario_".$cod_usuario_html."'>
                    <option value='CASUAL'>Tarea puntual</option>
                    <option value='DIARIO'>Tarea diaria</option>
                </select>
                <select class='inputText' id='inptPrioridadTareaFuncionario_".$cod_usuario_html."'>
                    <option value='Normal'>Normal</option>
                    <option value='Importante'>Importante</option>
                </select>
            </div>
            <textarea class='inputText asignar-tarea__inline-textarea' id='inptComentarioTareaFuncionario_".$cod_usuario_html."' placeholder='Comentario opcional'></textarea>
            <div class='asignar-tarea__inline-actions'>
                <button type='button' onclick='guardarTareaRapidaGestion(".$cod_usuario_html.")'>Guardar</button>
                <button type='button' onclick='verFormularioTareaFuncionario(".$cod_usuario_html.", false)'>Cancelar</button>
            </div>
        </div>

        <div class='asignar-tarea__task-sections'>";

    foreach ($secciones as $seccion) {
        $clave = $seccion["clave"];
        $cantidad = isset($resumen[$clave]) ? (int)$resumen[$clave] : 0;
        $html .= "
            <section class='asignar-tarea__task-section'>
                <div class='asignar-tarea__task-section-head'>
                    <h4>".$seccion["titulo"]."</h4>
                    <span>".$cantidad."</span>
                </div>
                <div class='asignar-tarea__task-list'>".
                    ($grupos[$clave] != "" ? $grupos[$clave] : "<div class='asignar-tarea__task-empty'>".$seccion["vacio"]."</div>").
                "</div>
            </section>";
    }

    $html .= "
        </div>
    </div>";

    $resumen["html"] = $html;

    return $resumen;
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

    $cod_usuario_responsable = isset($_POST['cod_usuario_responsable']) ? $_POST['cod_usuario_responsable'] : "";
    $cod_usuario_responsable = mb_convert_encoding((string)($cod_usuario_responsable), 'ISO-8859-1', 'UTF-8');

    cambiarEstadoTareaAsignada($cod_tarea_asignada, $estado_tarea, $cod_usuario, $cod_usuario_responsable);
}

if($operacion=="crearTareaRapidaUsuario")
{
    $titulo = convertir_post_tarea_programada('titulo');
    $hora = convertir_post_tarea_programada('hora');
    $tipo_tarea = convertir_post_tarea_programada('tipo_tarea', 'RAPIDA');
    $prioridad = convertir_post_tarea_programada('prioridad', 'Normal');
    $comentario = convertir_post_tarea_programada('comentario');
    $cod_usuario_destino = convertir_post_tarea_programada('cod_usuario_destino');
    $origen = convertir_post_tarea_programada('origen', 'funcionario');
    $cod_usuario_creador = convertir_post_tarea_programada('useru');

    crearTareaRapidaUsuario($titulo, $hora, $tipo_tarea, $prioridad, $comentario, $cod_usuario_destino, $origen, $cod_usuario_creador);
}

if($operacion=="crearTareaRapidaRol")
{
    $titulo = convertir_post_tarea_programada('titulo');
    $hora = convertir_post_tarea_programada('hora');
    $tipo_tarea = convertir_post_tarea_programada('tipo_tarea', 'CASUAL');
    $prioridad = convertir_post_tarea_programada('prioridad', 'Normal');
    $comentario = convertir_post_tarea_programada('comentario');
    $rol_operativo = convertir_post_tarea_programada('rol_operativo');
    $origen = convertir_post_tarea_programada('origen', 'administracion');
    $cod_usuario_creador = convertir_post_tarea_programada('useru');

    crearTareaRapidaRol($titulo, $hora, $tipo_tarea, $prioridad, $comentario, $rol_operativo, $origen, $cod_usuario_creador);
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

function crearTareaRapidaUsuario($titulo, $hora, $tipo_tarea, $prioridad, $comentario, $cod_usuario_destino, $origen, $cod_usuario_creador)
{
    $titulo = trim((string)$titulo);
    $hora = trim((string)$hora);
    $tipo_tarea = normalizar_tipo_tarea_personal($tipo_tarea);
    $cod_usuario_destino = trim((string)$cod_usuario_destino);
    $cod_usuario_creador = trim((string)$cod_usuario_creador);
    $origen = trim((string)$origen);

    if ($titulo == "" || $cod_usuario_destino == "" || $cod_usuario_creador == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    $horaValidada = validar_hora_tarea_programada($hora);
    if ($hora != "" && $horaValidada == "") {
        $informacion = array("1" => "camposvacio", "mensaje" => "Hora invalida");
        echo json_encode($informacion);
        exit;
    }

    date_default_timezone_set('America/Asuncion');

    if ($horaValidada == "") {
        $horaValidada = date("H:i:s");
    }

    $mysqli = conectar_al_servidor();
    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");
    $tieneColumnasDiariasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_diarias", "tipo_destino")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_diarias", "rol_operativoFK");

    $estado = "pendiente";
    $fecha_realizado = NULL;
    $fecha_create = date("Y-m-d H:i:s");
    $fecha_tarea = date("Y-m-d");
    $observacion = construir_observacion_tarea_personal($prioridad, $comentario, $origen == "" ? "funcionario" : $origen);

    $consultaTarea = "INSERT INTO tareas_programadas
                      (nombre,hora,estado,fecha_realizado,cod_usuarioFK,cod_usuarioFK_create,fecha_create,tipo)
                      VALUES
                      (?,?,?,?,?,?,?,?)";

    $stmtTarea = $mysqli->prepare($consultaTarea);

    if (!$stmtTarea) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar tarea: " . $mysqli->error, "sql" => $consultaTarea);
        echo json_encode($informacion);
        exit;
    }

    $ss = "ssssssss";
    $stmtTarea->bind_param($ss, $titulo, $horaValidada, $estado, $fecha_realizado, $cod_usuario_destino, $cod_usuario_creador, $fecha_create, $tipo_tarea);

    if (!$stmtTarea->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al crear tarea: " . $stmtTarea->error, "sql" => $consultaTarea);
        echo json_encode($informacion);
        exit;
    }

    $id_tarea = $stmtTarea->insert_id;
    $stmtTarea->close();

    if ($tipo_tarea == "DIARIO") {
        if ($tieneColumnasDiariasRol) {
            $consultaDiaria = "INSERT INTO tareas_programadas_diarias
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
                              (?, ?, 'USUARIO', NULL, 'Activo', ?, NULL, 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', ?, NULL, ?, ?, NULL)";
        } else {
            $consultaDiaria = "INSERT INTO tareas_programadas_diarias
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
                              (?, ?, 'Activo', ?, NULL, 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', ?, NULL, ?, ?, NULL)";
        }

        $stmtDiaria = $mysqli->prepare($consultaDiaria);

        if ($stmtDiaria) {
            $ssDiaria = "ssssss";
            $stmtDiaria->bind_param($ssDiaria, $id_tarea, $cod_usuario_destino, $fecha_tarea, $observacion, $cod_usuario_creador, $fecha_create);
            $stmtDiaria->execute();
            $stmtDiaria->close();
        }
    }

    if ($tieneColumnasAsignadasRol) {
        $consultaAsignada = "INSERT INTO tareas_programadas_asignadas
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
                            (?, ?, 'USUARIO', NULL, 'Pendiente', 'No', ?, NULL, ?, NULL, NULL, ?, NULL)";
    } else {
        $consultaAsignada = "INSERT INTO tareas_programadas_asignadas
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
                            (?, ?, 'Pendiente', 'No', ?, NULL, ?, NULL, NULL, ?, NULL)";
    }

    $stmtAsignada = $mysqli->prepare($consultaAsignada);

    if (!$stmtAsignada) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar asignacion: " . $mysqli->error, "sql" => $consultaAsignada);
        echo json_encode($informacion);
        exit;
    }

    $ssAsignada = "sssss";
    $stmtAsignada->bind_param($ssAsignada, $id_tarea, $cod_usuario_destino, $fecha_tarea, $observacion, $fecha_create);

    if (!$stmtAsignada->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al asignar tarea: " . $stmtAsignada->error, "sql" => $consultaAsignada);
        echo json_encode($informacion);
        exit;
    }

    $id_asignada = $stmtAsignada->insert_id;
    $stmtAsignada->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "id_tarea" => $id_tarea, "id_asignada" => $id_asignada);
    echo json_encode($informacion);
    exit;
}

function crearTareaRapidaRol($titulo, $hora, $tipo_tarea, $prioridad, $comentario, $rol_operativo, $origen, $cod_usuario_creador)
{
    $titulo = trim((string)$titulo);
    $hora = trim((string)$hora);
    $tipo_tarea = normalizar_tipo_tarea_personal($tipo_tarea);
    $rol_operativo = trim((string)$rol_operativo);
    $cod_usuario_creador = trim((string)$cod_usuario_creador);
    $origen = trim((string)$origen);

    if ($titulo == "" || $rol_operativo == "" || $cod_usuario_creador == "") {
        $informacion = array("1" => "camposvacio");
        echo json_encode($informacion);
        exit;
    }

    $horaValidada = validar_hora_tarea_programada($hora);
    if ($hora != "" && $horaValidada == "") {
        $informacion = array("1" => "camposvacio", "mensaje" => "Hora invalida");
        echo json_encode($informacion);
        exit;
    }

    date_default_timezone_set('America/Asuncion');

    if ($horaValidada == "") {
        $horaValidada = date("H:i:s");
    }

    $mysqli = conectar_al_servidor();
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

    $tieneColumnasAsignadasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "tipo_asignacion")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_asignadas", "rol_operativoFK");
    $tieneColumnasDiariasRol = existe_columna_tarea_programada($mysqli, "tareas_programadas_diarias", "tipo_destino")
        && existe_columna_tarea_programada($mysqli, "tareas_programadas_diarias", "rol_operativoFK");

    $estado = "pendiente";
    $fecha_realizado = NULL;
    $fecha_create = date("Y-m-d H:i:s");
    $fecha_tarea = date("Y-m-d");
    $observacion = construir_observacion_tarea_personal($prioridad, $comentario, $origen == "" ? "administracion" : $origen);
    $cod_usuario_tarea = $cod_usuario_creador;

    $consultaTarea = "INSERT INTO tareas_programadas
                      (nombre,hora,estado,fecha_realizado,cod_usuarioFK,cod_usuarioFK_create,fecha_create,tipo)
                      VALUES
                      (?,?,?,?,?,?,?,?)";

    $stmtTarea = $mysqli->prepare($consultaTarea);

    if (!$stmtTarea) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar tarea: " . $mysqli->error, "sql" => $consultaTarea);
        echo json_encode($informacion);
        exit;
    }

    $ss = "ssssssss";
    $stmtTarea->bind_param($ss, $titulo, $horaValidada, $estado, $fecha_realizado, $cod_usuario_tarea, $cod_usuario_creador, $fecha_create, $tipo_tarea);

    if (!$stmtTarea->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al crear tarea: " . $stmtTarea->error, "sql" => $consultaTarea);
        echo json_encode($informacion);
        exit;
    }

    $id_tarea = $stmtTarea->insert_id;
    $stmtTarea->close();

    if ($tipo_tarea == "DIARIO") {
        if ($tieneColumnasDiariasRol) {
            $consultaDiaria = "INSERT INTO tareas_programadas_diarias
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
                              (?, ?, 'ROL', ?, 'Activo', ?, NULL, 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', ?, NULL, ?, ?, NULL)";

            $stmtDiaria = $mysqli->prepare($consultaDiaria);

            if ($stmtDiaria) {
                $cod_usuario_null = NULL;
                $ssDiaria = "sssssss";
                $stmtDiaria->bind_param($ssDiaria, $id_tarea, $cod_usuario_null, $rol_operativo, $fecha_tarea, $observacion, $cod_usuario_creador, $fecha_create);
                $stmtDiaria->execute();
                $stmtDiaria->close();
            }
        } else {
            $consultaDiaria = "INSERT INTO tareas_programadas_diarias
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
                              (?, ?, 'Activo', ?, NULL, 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', 'Si', ?, NULL, ?, ?, NULL)";

            $stmtDiaria = $mysqli->prepare($consultaDiaria);

            if ($stmtDiaria) {
                foreach ($usuariosDestino as $cod_usuario_destino) {
                    $ssDiaria = "ssssss";
                    $stmtDiaria->bind_param($ssDiaria, $id_tarea, $cod_usuario_destino, $fecha_tarea, $observacion, $cod_usuario_creador, $fecha_create);
                    $stmtDiaria->execute();
                }

                $stmtDiaria->close();
            }
        }
    }

    if ($tieneColumnasAsignadasRol) {
        $consultaAsignada = "INSERT INTO tareas_programadas_asignadas
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
                            (?, ?, 'ROL', ?, 'Pendiente', 'No', ?, NULL, ?, NULL, NULL, ?, NULL)";
    } else {
        $consultaAsignada = "INSERT INTO tareas_programadas_asignadas
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
                            (?, ?, 'Pendiente', 'No', ?, NULL, ?, NULL, NULL, ?, NULL)";
    }

    $stmtAsignada = $mysqli->prepare($consultaAsignada);

    if (!$stmtAsignada) {
        $informacion = array("1" => "error", "mensaje" => "Error al preparar asignacion: " . $mysqli->error, "sql" => $consultaAsignada);
        echo json_encode($informacion);
        exit;
    }

    $insertados = 0;

    foreach ($usuariosDestino as $cod_usuario_destino) {
        if ($tieneColumnasAsignadasRol) {
            $ssAsignada = "ssssss";
            $stmtAsignada->bind_param($ssAsignada, $id_tarea, $cod_usuario_destino, $rol_operativo, $fecha_tarea, $observacion, $fecha_create);
        } else {
            $ssAsignada = "sssss";
            $stmtAsignada->bind_param($ssAsignada, $id_tarea, $cod_usuario_destino, $fecha_tarea, $observacion, $fecha_create);
        }

        if ($stmtAsignada->execute()) {
            $insertados++;
        } else {
            $informacion = array("1" => "error", "mensaje" => "Error al asignar tarea: " . $stmtAsignada->error, "sql" => $consultaAsignada);
            echo json_encode($informacion);
            exit;
        }
    }

    $stmtAsignada->close();
    mysqli_close($mysqli);

    $informacion = array("1" => "exito", "id_tarea" => $id_tarea, "insertados" => $insertados);
    echo json_encode($informacion);
    exit;
}

function cambiarEstadoTareaAsignada($cod_tarea_asignada, $estado_tarea, $cod_usuario, $cod_usuario_responsable = "")
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
    $cod_usuario_validacion = trim((string)$cod_usuario_responsable) != "" ? trim((string)$cod_usuario_responsable) : $cod_usuario;
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
                          fecha_completada = NULL,
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
        $stmt1->bind_param($ss, $estado_tarea, $cod_tarea_asignada, $cod_usuario_validacion, $cod_usuario_validacion);
    } else {
        $ss = "sss";
        $stmt1->bind_param($ss, $estado_tarea, $cod_tarea_asignada, $cod_usuario_validacion);
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

    date_default_timezone_set('America/Asuncion');
    $fecha_actual = date("Y-m-d");
    $hora_actual = date("H:i:s");
    $momento_actual = time();

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
                CASE 
                    WHEN tpa.estado_tarea = 'Pendiente' AND tp.hora IS NOT NULL AND tp.hora < ? THEN 1
                    WHEN tpa.estado_tarea = 'Pendiente' THEN 2
                    WHEN tpa.estado_tarea = 'En Proceso' THEN 3
                    WHEN tpa.estado_tarea = 'Completada' THEN 4
                    WHEN tpa.estado_tarea = 'Cancelada' THEN 5
                    ELSE 6
                END,
                CASE WHEN tp.hora IS NULL THEN 1 ELSE 0 END,
                tp.hora ASC,
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
	
    if ($tieneColumnasAsignadasRol) {
        $ss = "ssss";
        $stmt->bind_param($ss, $cod_usuario, $cod_usuario, $fecha_actual, $hora_actual);
    } else {
        $ss = "sss";
        $stmt->bind_param($ss, $cod_usuario, $fecha_actual, $hora_actual);
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
            $resumenTareas = obtener_html_tareas_usuario_gestion_diaria($mysqli, $cod_usuario, $nombre_persona, $tipoUsuario);

            $login_html = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
            $nombre_persona_html = htmlspecialchars($nombre_persona, ENT_QUOTES, 'UTF-8');
            $rut_html = htmlspecialchars($rut_usuario, ENT_QUOTES, 'UTF-8');
            $tipo_html = htmlspecialchars($tipoUsuario, ENT_QUOTES, 'UTF-8');
            $NombreLocal_html = htmlspecialchars($Nombre, ENT_QUOTES, 'UTF-8');
            $url_html = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $horario_html = htmlspecialchars(($horario_operativo != "" ? $horario_operativo : "No configurado"), ENT_QUOTES, 'UTF-8');
            $totalTareasHoy = (int)$resumenTareas["total"];
            $tareas_pendientes_hoy = (int)$resumenTareas["pendientes"] + (int)$resumenTareas["proceso"];
            $tareas_completadas_hoy = (int)$resumenTareas["completadas"];
            $tareas_atrasadas_hoy = (int)$resumenTareas["atrasadas"];
            $porcentajeAvance = $totalTareasHoy > 0 ? round(($tareas_completadas_hoy * 100) / $totalTareasHoy) : 0;
            $estadoJornada = "Sin tareas";
            $claseJornada = "sin-tareas";

            if ($totalTareasHoy > 0 && $tareas_completadas_hoy >= $totalTareasHoy) {
                $estadoJornada = "Completa";
                $claseJornada = "completa";
            } else if ($tareas_atrasadas_hoy > 0) {
                $estadoJornada = "Con atrasos";
                $claseJornada = "atrasada";
            } else if ($totalTareasHoy > 0) {
                $estadoJornada = "En curso";
                $claseJornada = "en-curso";
            }

            $total_tareas_html = htmlspecialchars((string)$totalTareasHoy, ENT_QUOTES, 'UTF-8');
            $tareas_pendientes_html = htmlspecialchars((string)$tareas_pendientes_hoy, ENT_QUOTES, 'UTF-8');
            $tareas_completadas_html = htmlspecialchars((string)$tareas_completadas_hoy, ENT_QUOTES, 'UTF-8');
            $tareas_atrasadas_html = htmlspecialchars((string)$tareas_atrasadas_hoy, ENT_QUOTES, 'UTF-8');
            $porcentaje_html = htmlspecialchars((string)$porcentajeAvance, ENT_QUOTES, 'UTF-8');
            $estado_jornada_html = htmlspecialchars($estadoJornada, ENT_QUOTES, 'UTF-8');
            $clase_jornada_html = htmlspecialchars($claseJornada, ENT_QUOTES, 'UTF-8');
            $stats_funcionario_html = "";
            $progreso_funcionario_html = "";

            if ($totalTareasHoy > 0) {
                $stats_funcionario_html = "
                    <div class='asignar-tarea__user-stats asignar-tarea__user-stats--compact'>
                        <span><strong>".$total_tareas_html."</strong> tareas</span>
                        <span><strong>".$tareas_completadas_html."</strong> completadas</span>
                        <span><strong>".$tareas_pendientes_html."</strong> pendientes</span>
                        <span><strong>".$tareas_atrasadas_html."</strong> atrasadas</span>
                    </div>";

                $progreso_funcionario_html = "
                    <div class='asignar-tarea__progress'>
                        <div><i style='width:".$porcentaje_html."%'></i></div>
                        <strong>".$porcentaje_html."%</strong>
                    </div>";
            } else {
                $stats_funcionario_html = "
                    <div class='asignar-tarea__user-stats asignar-tarea__user-stats--empty'>
                        <span>Sin tareas para hoy</span>
                    </div>";
                $progreso_funcionario_html = "<div class='asignar-tarea__progress asignar-tarea__progress--empty'></div>";
            }

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
            ");toggleFuncionarioAsignarTarea(event," . json_encode((string)$cod_usuario) . ")";
            $onclick_html = htmlspecialchars($onclick_js, ENT_QUOTES, 'UTF-8');

            $pagina .= "
            <div 
                class='asignar-tarea__card asignar-tarea__user-row asignar-tarea__funcionario-card' 
                id='usuarioAsignarTarea_".$cod_usuario."'>

                <div class='asignar-tarea__funcionario-summary' onclick=\"".$onclick_html."\">
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
                        <span class='asignar-tarea__jornada-badge asignar-tarea__jornada-badge--".$clase_jornada_html."'>".$estado_jornada_html."</span>
                    </div>

                    ".$stats_funcionario_html."

                    ".$progreso_funcionario_html."

                    <span class='asignar-tarea__chevron' aria-hidden='true'></span>
                </div>

                <div class='asignar-tarea__funcionario-detail' id='detalleFuncionarioTarea_".$cod_usuario."' style='display:none;'>
                    ".$resumenTareas["html"]."
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
        $condicionBuscar = " AND TRIM(ln.nombre) LIKE '%".$buscar."%'";
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
                    AND tpa.rol_operativoFK = TRIM(ln.nombre)
                    AND tpa.fecha_tarea = CURDATE()
                    AND tpa.estado_tarea IN ('Pendiente','En Proceso')
                ), 0)";

        $selectCompletadasHoy = "IFNULL((
                    SELECT COUNT(*)
                    FROM tareas_programadas_asignadas tpa
                    WHERE tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = TRIM(ln.nombre)
                    AND tpa.fecha_tarea = CURDATE()
                    AND tpa.estado_tarea = 'Completada'
                ), 0)";

        $selectTareasHoy = "IFNULL((
                    SELECT GROUP_CONCAT(DISTINCT CONCAT(tp.nombre, ' (', tpa.estado_tarea, ')') ORDER BY tp.hora ASC, tp.nombre ASC SEPARATOR '||')
                    FROM tareas_programadas_asignadas tpa
                    INNER JOIN tareas_programadas tp ON tp.id = tpa.cod_tareaFK
                    WHERE tpa.tipo_asignacion = 'ROL'
                    AND tpa.rol_operativoFK = TRIM(ln.nombre)
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
                    AND tpd.rol_operativoFK = TRIM(ln.nombre)
                    AND tpd.estado = 'Activo'
                ), '')";
    } else {
        $selectTareasDiarias = "''";
    }

    $sql = "SELECT
                TRIM(ln.nombre) AS rol_operativo,
                COUNT(u.cod_usuario) AS total_usuarios,
                IFNULL(SUM(CASE WHEN u.estado = 'Activo' THEN 1 ELSE 0 END), 0) AS usuarios_activos,
                IFNULL(SUM(CASE WHEN u.estado = 'Inactivo' THEN 1 ELSE 0 END), 0) AS usuarios_inactivos,
                ".$selectPendientesHoy." AS tareas_pendientes_hoy,
                ".$selectCompletadasHoy." AS tareas_completadas_hoy,
                ".$selectTareasHoy." AS tareas_hoy,
                ".$selectTareasDiarias." AS tareas_diarias
            FROM listado_niveles ln
            LEFT JOIN usuario u
                ON TRIM(u.tipo) = TRIM(ln.nombre)
                ".$condicionEstado."
            WHERE 1=1
            ".condicionar_rol_operativo_activo_tarea_programada("ln")."
            ".$condicionBuscar."
            GROUP BY ln.cod_niveles, TRIM(ln.nombre)
            ORDER BY TRIM(ln.nombre) ASC";

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
            ");toggleRolAsignarTarea(event," . json_encode((string)$idRol) . ")";
            $onclick_html = htmlspecialchars($onclick_js, ENT_QUOTES, 'UTF-8');
            $idRol_js = htmlspecialchars(json_encode((string)$idRol), ENT_QUOTES, 'UTF-8');
            $rol_js = htmlspecialchars(json_encode($rol_operativo), ENT_QUOTES, 'UTF-8');
            $resumen_rol_html = htmlspecialchars("Se asignara a ".$usuarios_activos." usuarios activos del rol ".$rol_operativo.".", ENT_QUOTES, 'UTF-8');

            $pagina .= "
            <div 
                class='asignar-tarea__card asignar-tarea__user-row asignar-tarea__role-row asignar-tarea__role-card' 
                id='rolAsignarTarea_".$idRol."'
                data-rol='".$rol_html."'>

                <div class='asignar-tarea__role-summary' onclick=\"".$onclick_html."\">
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

                    <span class='asignar-tarea__chevron' aria-hidden='true'></span>
                </div>

                <div class='asignar-tarea__role-detail' id='detalleRolTarea_".$idRol."' style='display:none;'>
                    <div class='asignar-tarea__task-panel'>
                        <div class='asignar-tarea__task-panel-head'>
                            <div>
                                <h3>Tareas del rol</h3>
                                <span>".$resumen_rol_html."</span>
                            </div>
                            <button type='button' class='asignar-tarea__inline-add-btn' onclick='event.stopPropagation();verFormularioTareaRol(".$idRol_js.", true)'>+ Agregar tarea</button>
                        </div>

                        <div class='asignar-tarea__inline-form' id='formAgregarTareaRol_".$idRol."' style='display:none;' onclick='event.stopPropagation();'>
                            <div class='asignar-tarea__inline-grid'>
                                <input type='text' class='inputText' id='inptTituloTareaRol_".$idRol."' placeholder='Titulo de la tarea'>
                                <input type='time' class='inputText' id='inptHoraTareaRol_".$idRol."'>
                                <select class='inputText' id='inptTipoTareaRol_".$idRol."'>
                                    <option value='CASUAL'>Tarea puntual</option>
                                    <option value='DIARIO'>Tarea diaria</option>
                                </select>
                                <select class='inputText' id='inptPrioridadTareaRol_".$idRol."'>
                                    <option value='Normal'>Normal</option>
                                    <option value='Importante'>Importante</option>
                                </select>
                            </div>
                            <textarea class='inputText asignar-tarea__inline-textarea' id='inptComentarioTareaRol_".$idRol."' placeholder='Comentario opcional'></textarea>
                            <div class='asignar-tarea__inline-actions'>
                                <button type='button' onclick='guardarTareaRapidaRolGestion(".$idRol_js.", ".$rol_js.")'>Guardar para el rol</button>
                                <button type='button' onclick='verFormularioTareaRol(".$idRol_js.", false)'>Cancelar</button>
                            </div>
                        </div>

                        <div class='asignar-tarea__role-detail-grid'>
                            <span title='".$tareas_hoy_html."'>Hoy: ".$tareas_hoy_html."</span>
                            <span title='".$tareas_diarias_html."'>Diarias: ".$tareas_diarias_html."</span>
                            <span>".$usuarios_activos." usuarios activos recibiran la tarea.</span>
                        </div>
                    </div>
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
	$fecha_realizado = normalizar_fecha_realizado_tarea_programada($fecha_realizado);

    if ($hora == '') {
        $informacion = array('1' => 'camposvacio');
        echo json_encode($informacion);
        exit;
    }

	if ($fecha_realizado === false) {
		$informacion = array(
			'1' => 'error',
			'mensaje' => 'La fecha de realizacion no es valida. Seleccione nuevamente la fecha y la hora.'
		);
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

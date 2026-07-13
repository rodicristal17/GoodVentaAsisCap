<?php

if (!defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
    define('JSON_INVALID_UTF8_SUBSTITUTE', 0);
}

if (!defined('ABM_CALENDAR_JSON_FATAL_HANDLER')) {
    define('ABM_CALENDAR_JSON_FATAL_HANDLER', true);
    ob_start();
    register_shutdown_function(function () {
        $error = error_get_last();
        $tiposFatales = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR);
        if ($error && in_array($error['type'], $tiposFatales)) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(200);
            }
            echo json_encode(array(
                "1" => "Error",
                "mensaje" => "Error interno al procesar calendario: " . $error['message']
            ), JSON_INVALID_UTF8_SUBSTITUTE);
        }
    });
}

include_once 'verificar_navegador.php';
include_once 'buscar_nivel.php';
include_once 'classTable.php';
include_once 'conexion.php';
include_once 'solicitud_eliminado_helper.php';
include_once 'producto_riesgo_financiero_helper.php';
include_once 'abmAgenda.php';
include_once 'abmusuarios.php';

date_default_timezone_set('America/Asuncion');

function responderJsonCalendar($datos)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($datos, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

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
        case 'cargarAgendaBasica':
            cargarAgendaBasica($mysqli, $useru);
            break;

        case 'cargarAgenda':
            cargarAgenda($mysqli, $useru);
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

        case 'reprogramarCita':
            reprogramarCita($mysqli, $useru);
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

        case 'buscarVentasPacienteAgenda':
            buscarVentasPacienteAgenda($mysqli);
            break;

        case 'listarTratamientosVentaAgenda':
            listarTratamientosVentaAgenda($mysqli);
            break;

        case 'obtenerPlanMadreAgenda':
            obtenerPlanMadreAgenda($mysqli);
            break;

        case 'crearPlanMadreSugeridoAgenda':
            crearPlanMadreSugeridoAgenda($mysqli, $useru);
            break;

        case 'guardarTratamientosAgenda':
            guardarTratamientosAgenda($mysqli, $useru);
            break;

        case 'obtenerPrevisionInsumosAgenda':
            obtenerPrevisionInsumosAgendaEndpoint($mysqli);
            break;

        case 'guardarVarianteInsumoAgenda':
            guardarVarianteInsumoAgenda($mysqli, $useru);
            break;

        case 'generarInformeInsumosAgenda':
            generarInformeInsumosAgendaEndpoint($mysqli, $useru);
            break;

        case 'proyeccionInsumosConsultorioAgenda':
            proyeccionInsumosConsultorioAgendaEndpoint($mysqli);
            break;

        case 'catalogosInformeInsumosAgenda':
            catalogosInformeInsumosAgendaEndpoint($mysqli, $useru);
            break;

        case 'buscarDoctoresDisponiblesCita':
            buscarDoctoresDisponiblesCita($mysqli);
            break;

        case 'listarDiasFeriados':
            listarDiasFeriados($mysqli);
            break;

        case 'guardarDiaFeriado':
            guardarDiaFeriado($mysqli, $useru);
            break;

        case 'eliminarDiaFeriado':
            eliminarDiaFeriado($mysqli, $useru);
            break;

        case 'listarAsignacionesConsultorios':
            listarAsignacionesConsultorios($mysqli);
            break;

        case 'guardarAsignacionConsultorio':
            guardarAsignacionConsultorio($mysqli, $useru);
            break;

        case 'eliminarAsignacionConsultorio':
            eliminarAsignacionConsultorio($mysqli, $useru);
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

function asegurarEstructuraAgendaInsumos($mysqli)
{
    $sqlAgendaTratamientos = "CREATE TABLE IF NOT EXISTS agenda_tratamientos (
        id INT NOT NULL AUTO_INCREMENT,
        id_agenda INT NOT NULL,
        cod_ventaFK INT NOT NULL,
        cod_detalle_ventaFK INT NOT NULL,
        estado ENUM('previsto','realizado','pendiente','cancelado') NOT NULL DEFAULT 'previsto',
        creado_por INT NULL,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        realizado_por INT NULL,
        realizado_en DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_agenda_detalle (id_agenda, cod_detalle_ventaFK),
        KEY idx_agenda_trat_agenda (id_agenda),
        KEY idx_agenda_trat_venta (cod_ventaFK),
        KEY idx_agenda_trat_detalle (cod_detalle_ventaFK)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlAgendaTratamientos);

    $sqlBase = "CREATE TABLE IF NOT EXISTS agenda_insumo_base (
        id INT NOT NULL AUTO_INCREMENT,
        id_insumo INT NOT NULL,
        cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
        estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
        creado_por INT NULL,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_agenda_insumo_base (id_insumo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlBase);

    $sqlInsumoProducto = "CREATE TABLE IF NOT EXISTS insumo_producto (
        id INT NOT NULL AUTO_INCREMENT,
        id_insumo INT NOT NULL,
        cod_producto VARCHAR(45) NOT NULL,
        cantidad DECIMAL(12,3) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY uq_insumo_producto (id_insumo, cod_producto),
        KEY idx_insumo_producto_insumo (id_insumo),
        KEY idx_insumo_producto_producto (cod_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlInsumoProducto);

    $sqlConsumo = "CREATE TABLE IF NOT EXISTS agenda_consumo_insumos (
        id INT NOT NULL AUTO_INCREMENT,
        id_agenda INT NOT NULL,
        id_insumo INT NOT NULL,
        id_variante INT NOT NULL DEFAULT 0,
        cantidad_prevista DECIMAL(12,3) NOT NULL DEFAULT 0,
        cantidad_confirmada DECIMAL(12,3) NULL,
        unidad_medida VARCHAR(40) NULL,
        estado ENUM('previsto','confirmado','ajustado') NOT NULL DEFAULT 'previsto',
        usuario_confirmo INT NULL,
        fecha_confirmo DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_agenda_consumo (id_agenda, id_insumo),
        KEY idx_agenda_consumo_agenda (id_agenda),
        KEY idx_agenda_consumo_insumo (id_insumo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlConsumo);
    agregarColumnaAgendaSiNoExiste($mysqli, "insumosconsl", "tiene_variantes", "tiene_variantes TINYINT(1) NOT NULL DEFAULT 0");
    agregarColumnaAgendaSiNoExiste($mysqli, "insumosconsl", "tipo_variante", "tipo_variante VARCHAR(60) NULL");
    asegurarTablaVariantesAgenda($mysqli);
    agregarColumnaAgendaSiNoExiste($mysqli, "agenda_consumo_insumos", "id_variante", "id_variante INT NOT NULL DEFAULT 0");
    agregarColumnaAgendaSiNoExiste($mysqli, "agenda_consumo_insumos", "stock_descontado", "stock_descontado TINYINT(1) NOT NULL DEFAULT 0");
    agregarColumnaAgendaSiNoExiste($mysqli, "agenda_consumo_insumos", "cantidad_descontada", "cantidad_descontada DECIMAL(12,3) NOT NULL DEFAULT 0");
    agregarColumnaAgendaSiNoExiste($mysqli, "agenda_consumo_insumos", "fecha_descontado", "fecha_descontado DATETIME NULL");
    agregarColumnaAgendaSiNoExiste($mysqli, "agenda_consumo_insumos", "usuario_desconto", "usuario_desconto INT NULL");

    $sqlAjustes = "CREATE TABLE IF NOT EXISTS agenda_consumo_ajustes (
        id INT NOT NULL AUTO_INCREMENT,
        id_agenda INT NOT NULL,
        id_insumo INT NOT NULL,
        usuario_id INT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        paciente VARCHAR(160) NULL,
        venta_apodo VARCHAR(160) NULL,
        id_consultorio INT NULL,
        cantidad_anterior DECIMAL(12,3) NOT NULL DEFAULT 0,
        cantidad_nueva DECIMAL(12,3) NOT NULL DEFAULT 0,
        diferencia_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
        motivo VARCHAR(255) NOT NULL DEFAULT 'Correccion de consumo confirmado',
        PRIMARY KEY (id),
        KEY idx_agenda_ajuste_agenda (id_agenda),
        KEY idx_agenda_ajuste_insumo (id_insumo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlAjustes);

    $sqlStock = "CREATE TABLE IF NOT EXISTS insumo_stock_consultorio (
        id_stock INT NOT NULL AUTO_INCREMENT,
        id_insumo INT NOT NULL,
        id_variante INT NOT NULL DEFAULT 0,
        cod_local INT NOT NULL,
        id_consultorio INT NOT NULL,
        cantidad DECIMAL(12,3) NOT NULL DEFAULT 0,
        fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id_stock),
        UNIQUE KEY uq_insumo_local_consultorio_variante (id_insumo, id_variante, cod_local, id_consultorio),
        KEY idx_insumo_stock_local (cod_local),
        KEY idx_insumo_stock_consultorio (id_consultorio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlStock);
    agregarColumnaAgendaSiNoExiste($mysqli, "insumo_stock_consultorio", "id_variante", "id_variante INT NOT NULL DEFAULT 0");
    quitarIndiceAgendaSiExiste($mysqli, "insumo_stock_consultorio", "uq_insumo_local_consultorio");
    agregarIndiceAgendaSiNoExiste($mysqli, "insumo_stock_consultorio", "uq_insumo_local_consultorio_variante", "UNIQUE KEY uq_insumo_local_consultorio_variante (id_insumo, id_variante, cod_local, id_consultorio)");

    $sqlMovimientos = "CREATE TABLE IF NOT EXISTS movimientos_insumos (
        id_movimiento INT NOT NULL AUTO_INCREMENT,
        grupo_movimiento VARCHAR(40) NULL,
        tipo VARCHAR(20) NOT NULL,
        insumo_id INT NOT NULL,
        sucursal_id INT NOT NULL,
        consultorio_id INT NOT NULL,
        cantidad DECIMAL(12,3) NOT NULL DEFAULT 0,
        motivo VARCHAR(255) NOT NULL,
        usuario_id INT NULL,
        fecha DATETIME NOT NULL,
        PRIMARY KEY (id_movimiento),
        KEY idx_mov_insumo (insumo_id),
        KEY idx_mov_grupo (grupo_movimiento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sqlMovimientos);
    agregarColumnaAgendaSiNoExiste($mysqli, "movimientos_insumos", "grupo_movimiento", "grupo_movimiento VARCHAR(40) NULL");
    agregarColumnaAgendaSiNoExiste($mysqli, "movimientos_insumos", "id_variante", "id_variante INT NOT NULL DEFAULT 0");
}

function asegurarTablaVariantesAgenda($mysqli)
{
    $sql = "CREATE TABLE IF NOT EXISTS insumo_variantes (
        id_variante INT NOT NULL AUTO_INCREMENT,
        insumo_id INT NOT NULL,
        nombre_variante VARCHAR(120) NOT NULL,
        stock DECIMAL(12,3) NOT NULL DEFAULT 0,
        stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
        estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id_variante),
        UNIQUE KEY uq_insumo_variante (insumo_id, nombre_variante),
        KEY idx_variante_insumo (insumo_id),
        KEY idx_variante_estado (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sql);
}

function agregarColumnaAgendaSiNoExiste($mysqli, $tabla, $columna, $definicion)
{
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', $columna);
    if (!tablaAgendaExiste($mysqli, $tabla)) {
        return;
    }
    $result = $mysqli->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
    if ($result && $result->num_rows == 0) {
        $mysqli->query("ALTER TABLE `$tabla` ADD COLUMN $definicion");
    }
}

function tablaAgendaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
    if ($tabla == "") {
        return false;
    }
    if (array_key_exists($tabla, $cache)) {
        return $cache[$tabla];
    }

    try {
        $result = $mysqli->query("SHOW TABLES LIKE '".$tabla."'");
        $cache[$tabla] = $result && $result->num_rows > 0;
    } catch (Throwable $e) {
        $cache[$tabla] = false;
    }
    return $cache[$tabla];
}

function indiceAgendaExiste($mysqli, $tabla, $indice)
{
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
    $indice = preg_replace('/[^a-zA-Z0-9_]/', '', $indice);
    if (!tablaAgendaExiste($mysqli, $tabla)) {
        return false;
    }
    $result = $mysqli->query("SHOW INDEX FROM `$tabla` WHERE Key_name='".$indice."'");
    return $result && $result->num_rows > 0;
}

function quitarIndiceAgendaSiExiste($mysqli, $tabla, $indice)
{
    if (indiceAgendaExiste($mysqli, $tabla, $indice)) {
        $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
        $indice = preg_replace('/[^a-zA-Z0-9_]/', '', $indice);
        $mysqli->query("ALTER TABLE `$tabla` DROP INDEX `$indice`");
    }
}

function agregarIndiceAgendaSiNoExiste($mysqli, $tabla, $indice, $definicion)
{
    if (!indiceAgendaExiste($mysqli, $tabla, $indice)) {
        $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
        $mysqli->query("ALTER TABLE `$tabla` ADD $definicion");
    }
}

function asegurarEstructuraConsultorioDoctorAsignacion($mysqli)
{
    if (function_exists('asegurarEstructuraHorarioUsuarioEsperado')) {
        asegurarEstructuraHorarioUsuarioEsperado($mysqli);
    }

    $sql = "CREATE TABLE IF NOT EXISTS consultorio_doctor_asignacion (
        id_asignacion INT NOT NULL AUTO_INCREMENT,
        id_consultorio INT NOT NULL,
        id_horario_usuario INT NOT NULL,
        estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
        fecha_create DATETIME DEFAULT CURRENT_TIMESTAMP,
        cod_usuarioFK_create INT NULL,
        fecha_edit DATETIME NULL,
        cod_usuarioFK_edit INT NULL,
        PRIMARY KEY (id_asignacion),
        KEY idx_asig_horario_estado (id_horario_usuario, estado),
        KEY idx_asig_consultorio_estado (id_consultorio, estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
    $mysqli->query($sql);

    agregarIndiceAgendaSiNoExiste($mysqli, "consultorio_doctor_asignacion", "idx_asig_horario_estado", "KEY idx_asig_horario_estado (id_horario_usuario, estado)");
    agregarIndiceAgendaSiNoExiste($mysqli, "consultorio_doctor_asignacion", "idx_asig_consultorio_estado", "KEY idx_asig_consultorio_estado (id_consultorio, estado)");
    agregarIndiceAgendaSiNoExiste($mysqli, "horario_usuario", "idx_horario_doctor_dia", "KEY idx_horario_doctor_dia (cod_usuarioFK, dia_semana, hora_entrada, hora_salida)");
    agregarIndiceAgendaSiNoExiste($mysqli, "horario_usuario", "idx_horario_local_dia", "KEY idx_horario_local_dia (cod_localFK, dia_semana)");
}

function obtenerHorarioAsignacionConsultorio($mysqli, $idHorario)
{
    $idHorario = (int)$idHorario;
    if ($idHorario <= 0) {
        return null;
    }

    $sql = "SELECT
            hu.id,
            hu.cod_usuarioFK,
            hu.dia_semana,
            hu.hora_entrada,
            hu.hora_salida,
            hu.cod_localFK,
            p.nombre_persona
        FROM horario_usuario hu
        INNER JOIN usuario u ON u.cod_usuario = hu.cod_usuarioFK
        INNER JOIN persona p ON p.cod_persona = u.cod_usuario
        WHERE hu.id = '".$idHorario."'
        AND u.tipo = 'DOCTOR'
        AND u.estado = 'Activo'
        AND IFNULL(hu.estado_horario,'activo') = 'activo'
        LIMIT 1";

    $result = $mysqli->query($sql);
    if (!$result || !($row = $result->fetch_assoc())) {
        return null;
    }

    return $row;
}

function obtenerConsultorioAsignacion($mysqli, $idConsultorio)
{
    $idConsultorio = (int)$idConsultorio;
    if ($idConsultorio <= 0) {
        return null;
    }

    $sql = "SELECT c.id_consultorio, c.nombre, c.cod_localFk, IFNULL(l.Nombre,'') AS nombre_local
        FROM consultorios c
        LEFT JOIN local l ON l.cod_local = c.cod_localFk
        WHERE c.id_consultorio = '".$idConsultorio."'
        AND c.estado = 'Activo'
        LIMIT 1";

    $result = $mysqli->query($sql);
    if (!$result || !($row = $result->fetch_assoc())) {
        return null;
    }

    return $row;
}

function validarHorarioAsignacionConsultorio($horario)
{
    if (!$horario) {
        return "No se encontro el horario laboral seleccionado.";
    }

    if ((int)$horario["cod_localFK"] <= 0) {
        return "El horario laboral seleccionado no tiene local definido.";
    }

    if ($horario["hora_salida"] == null || $horario["hora_salida"] == "" || $horario["hora_salida"] == "00:00:00") {
        return "El horario laboral seleccionado no tiene hora de salida.";
    }

    $entrada = strtotime("2000-01-01 ".$horario["hora_entrada"]);
    $salida = strtotime("2000-01-01 ".$horario["hora_salida"]);
    if ($entrada === false || $salida === false || $salida <= $entrada) {
        return "El horario laboral seleccionado tiene un rango invalido.";
    }

    return "";
}

function buscarConflictoAsignacionConsultorio($mysqli, $consultorio, $horario, $idActual)
{
    $idActual = (int)$idActual;
    $doctor = (int)$horario["cod_usuarioFK"];
    $dia = limpiar($mysqli, $horario["dia_semana"]);
    $horaEntrada = limpiar($mysqli, $horario["hora_entrada"]);
    $horaSalida = limpiar($mysqli, $horario["hora_salida"]);
    $idConsultorio = (int)$consultorio["id_consultorio"];
    $excluir = $idActual > 0 ? " AND a.id_asignacion <> '".$idActual."' " : "";

    $sqlDoctor = "SELECT
            a.id_asignacion,
            c.nombre AS consultorio,
            IFNULL(l.Nombre,'') AS local,
            TIME_FORMAT(hu.hora_entrada, '%H:%i') AS hora_entrada,
            TIME_FORMAT(hu.hora_salida, '%H:%i') AS hora_salida
        FROM consultorio_doctor_asignacion a
        INNER JOIN horario_usuario hu ON hu.id = a.id_horario_usuario
        INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
        LEFT JOIN local l ON l.cod_local = c.cod_localFk
        WHERE a.estado = 'activo'
        ".$excluir."
        AND hu.cod_usuarioFK = '".$doctor."'
        AND hu.dia_semana = '".$dia."'
        AND IFNULL(hu.estado_horario,'activo') = 'activo'
        AND hu.hora_salida IS NOT NULL
        AND hu.hora_entrada < '".$horaSalida."'
        AND hu.hora_salida > '".$horaEntrada."'
        LIMIT 1";

    $resultDoctor = $mysqli->query($sqlDoctor);
    if ($resultDoctor && ($row = $resultDoctor->fetch_assoc())) {
        return "El doctor ya esta asignado a ".$row["consultorio"]." de ".$row["hora_entrada"]." a ".$row["hora_salida"].".";
    }

    $sqlConsultorio = "SELECT
            a.id_asignacion,
            p.nombre_persona AS doctor,
            TIME_FORMAT(hu.hora_entrada, '%H:%i') AS hora_entrada,
            TIME_FORMAT(hu.hora_salida, '%H:%i') AS hora_salida
        FROM consultorio_doctor_asignacion a
        INNER JOIN horario_usuario hu ON hu.id = a.id_horario_usuario
        INNER JOIN usuario u ON u.cod_usuario = hu.cod_usuarioFK
        INNER JOIN persona p ON p.cod_persona = u.cod_usuario
        WHERE a.estado = 'activo'
        ".$excluir."
        AND a.id_consultorio = '".$idConsultorio."'
        AND hu.dia_semana = '".$dia."'
        AND IFNULL(hu.estado_horario,'activo') = 'activo'
        AND hu.hora_salida IS NOT NULL
        AND hu.hora_entrada < '".$horaSalida."'
        AND hu.hora_salida > '".$horaEntrada."'
        LIMIT 1";

    $resultConsultorio = $mysqli->query($sqlConsultorio);
    if ($resultConsultorio && ($row = $resultConsultorio->fetch_assoc())) {
        return "El consultorio ya esta asignado a ".$row["doctor"]." de ".$row["hora_entrada"]." a ".$row["hora_salida"].".";
    }

    return "";
}

function obtenerProfesionalAsignadoConsultorioHorario($mysqli, $idConsultorio, $fecha, $horaInicio, $horaFin)
{
    asegurarEstructuraConsultorioDoctorAsignacion($mysqli);

    $idConsultorio = (int)$idConsultorio;
    $fecha = limpiar($mysqli, $fecha);
    $horaInicio = limpiar($mysqli, $horaInicio);
    $horaFin = limpiar($mysqli, $horaFin);
    $dia = obtenerDiaSemanaAgenda($fecha);

    if ($idConsultorio <= 0 || $dia == '' || $horaInicio == '' || $horaFin == '') {
        return 0;
    }

    $sql = "SELECT hu.cod_usuarioFK
        FROM consultorio_doctor_asignacion a
        INNER JOIN horario_usuario hu ON hu.id = a.id_horario_usuario
        INNER JOIN usuario u ON u.cod_usuario = hu.cod_usuarioFK
        WHERE a.estado = 'activo'
        AND a.id_consultorio = '".$idConsultorio."'
        AND hu.dia_semana = '".$dia."'
        AND IFNULL(hu.estado_horario,'activo') = 'activo'
        AND hu.hora_salida IS NOT NULL
        AND hu.hora_entrada <= '".$horaInicio."'
        AND hu.hora_salida >= '".$horaFin."'
        AND u.tipo = 'DOCTOR'
        AND u.estado = 'Activo'
        ORDER BY hu.hora_entrada DESC, a.id_asignacion DESC
        LIMIT 1";

    $result = $mysqli->query($sql);
    if ($result && ($row = $result->fetch_assoc())) {
        return (int)$row["cod_usuarioFK"];
    }

    return 0;
}

function normalizarNumeroAgenda($valor)
{
    $valor = str_replace(",", ".", trim((string)$valor));
    return is_numeric($valor) ? (float)$valor : 0;
}

function esEstadoPrimeraConsultaAgenda($estado)
{
    return strtoupper(trim((string)$estado)) === "PRIMERACONSULTA";
}

function obtenerProductoPrimeraConsultaAgenda($mysqli)
{
    $sql = "SELECT cod_producto, nombre_producto
        FROM producto
        WHERE LOWER(TRIM(nombre_producto)) = 'primera consulta'
          AND UPPER(IFNULL(estado,'ACTIVO')) NOT IN ('INACTIVO','ELIMINADO')
        ORDER BY cod_producto ASC
        LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result && ($row = $result->fetch_assoc())) {
        return array(
            "cod_producto" => (string)$row["cod_producto"],
            "nombre_producto" => normalizarTextoUtf8($row["nombre_producto"])
        );
    }
    return null;
}

function agendaEsPrimeraConsulta($mysqli, $idAgenda, $estadoForzado = "")
{
    if (esEstadoPrimeraConsultaAgenda($estadoForzado)) {
        return true;
    }
    $idAgenda = (int)$idAgenda;
    if ($idAgenda <= 0) {
        return false;
    }
    $result = $mysqli->query("SELECT estado FROM agenda WHERE id_agenda = '".$idAgenda."' LIMIT 1");
    return $result && ($row = $result->fetch_assoc()) && esEstadoPrimeraConsultaAgenda($row["estado"]);
}

function agregarInsumoPrevistoAgenda(&$insumos, $row, $cantidad)
{
    $id = (int)$row["id_insumo"];
    if (!isset($insumos[$id])) {
        $insumos[$id] = array(
            "id_insumo" => $id,
            "nombre" => isset($row["nombre"]) ? normalizarTextoUtf8($row["nombre"]) : "",
            "unidad_medida" => normalizarTextoUtf8($row["unidad_medida"]),
            "cantidad" => 0,
            "stock_minimo" => isset($row["stock_minimo"]) ? normalizarNumeroAgenda($row["stock_minimo"]) : 0,
            "stock" => null,
            "faltante" => 0,
            "tiene_variantes" => isset($row["tiene_variantes"]) ? (int)$row["tiene_variantes"] : 0,
            "tipo_variante" => isset($row["tipo_variante"]) ? normalizarTextoUtf8($row["tipo_variante"]) : "",
            "id_variante" => 0,
            "variantes" => array()
        );
    }
    $insumos[$id]["cantidad"] += normalizarNumeroAgenda($cantidad);
}

function agregarInsumosProductoPrimeraConsultaAgenda($mysqli, &$insumos, $conDetalleCompleto = false)
{
    $producto = obtenerProductoPrimeraConsultaAgenda($mysqli);
    if (!$producto) {
        return null;
    }

    $campos = $conDetalleCompleto
        ? "ip.id_insumo,
            IF(LOWER(TRIM(i.descripcion)) = 'descartable', MAX(ip.cantidad), SUM(ip.cantidad)) AS cantidad,
            i.nombre, i.unidad_medida, i.stock_minimo, i.tiene_variantes, i.tipo_variante"
        : "ip.id_insumo,
            IF(LOWER(TRIM(i.descripcion)) = 'descartable', MAX(ip.cantidad), SUM(ip.cantidad)) AS cantidad,
            i.unidad_medida";
    $grupo = $conDetalleCompleto
        ? "ip.id_insumo, i.descripcion, i.nombre, i.unidad_medida, i.stock_minimo, i.tiene_variantes, i.tipo_variante"
        : "ip.id_insumo, i.unidad_medida, i.descripcion";

    $codProducto = mysqli_real_escape_string($mysqli, $producto["cod_producto"]);
    $sql = "SELECT ".$campos."
        FROM insumo_producto ip
        INNER JOIN insumosconsl i ON i.id_insumo = ip.id_insumo
        WHERE ip.cod_producto = '".$codProducto."'
        GROUP BY ".$grupo;
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            agregarInsumoPrevistoAgenda($insumos, $row, $row["cantidad"]);
        }
    }
    return $producto;
}

function descontarInsumosAgendaAtendida($mysqli, $idAgenda, $useru, $estadoForzado = "")
{
    $idAgenda = (int)$idAgenda;
    $usuario = (int)$useru;
    if ($idAgenda <= 0) {
        throw new Exception("Falta el agendamiento para descontar insumos.");
    }
    if (!tablaAgendaExiste($mysqli, "insumosconsl")) {
        return 0;
    }

    $resultAgenda = $mysqli->query("SELECT a.id_agenda, a.id_consultorio, a.cod_ventaFK, a.cod_detalle_ventaFK, p.nombre_persona
        FROM agenda a
        LEFT JOIN persona p ON p.cod_persona = a.id_paciente
        WHERE a.id_agenda = '".$idAgenda."' LIMIT 1");
    if (!$resultAgenda || !($agenda = $resultAgenda->fetch_assoc())) {
        throw new Exception("No se encontro la cita para descontar insumos.");
    }

    $consultorio = (int)$agenda["id_consultorio"];
    $codLocal = obtenerCodLocalConsultorioAgenda($mysqli, $consultorio);
    if ($consultorio <= 0 || $codLocal <= 0) {
        throw new Exception("La cita no tiene consultorio/local valido para descontar insumos.");
    }

    $resultConsumo = $mysqli->query("SELECT COUNT(*) AS total FROM agenda_consumo_insumos WHERE id_agenda = '".$idAgenda."'");
    $totalConsumo = ($resultConsumo && ($rowTotal = $resultConsumo->fetch_assoc())) ? (int)$rowTotal["total"] : 0;
    if ($totalConsumo == 0) {
        $insumos = obtenerInsumosPrevistosAgendaSinPrepararEstructura($mysqli, $idAgenda, $estadoForzado);
        foreach ($insumos as $insumo) {
            $idInsumo = (int)$insumo["id_insumo"];
            $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
            $unidad = limpiar($mysqli, $insumo["unidad_medida"]);
            if ($idInsumo > 0 && $cantidad > 0) {
                $mysqli->query("INSERT INTO agenda_consumo_insumos (id_agenda, id_insumo, cantidad_prevista, unidad_medida)
                    VALUES ('".$idAgenda."', '".$idInsumo."', '".$cantidad."', '".$unidad."')
                    ON DUPLICATE KEY UPDATE cantidad_prevista=VALUES(cantidad_prevista), unidad_medida=VALUES(unidad_medida)");
            }
        }
    }

    $sqlConsumos = "SELECT ac.id, ac.id_insumo, IFNULL(ac.id_variante, 0) AS id_variante,
            IF(ac.cantidad_confirmada IS NULL, ac.cantidad_prevista, ac.cantidad_confirmada) AS cantidad,
            i.nombre, i.tiene_variantes, COALESCE(v.nombre_variante, '') AS nombre_variante
        FROM agenda_consumo_insumos ac
        INNER JOIN insumosconsl i ON i.id_insumo = ac.id_insumo
        LEFT JOIN insumo_variantes v ON v.id_variante = ac.id_variante
        WHERE ac.id_agenda = '".$idAgenda."'
          AND IFNULL(ac.stock_descontado, 0) = 0
          AND IF(ac.cantidad_confirmada IS NULL, ac.cantidad_prevista, ac.cantidad_confirmada) > 0
        FOR UPDATE";
    $resultConsumos = $mysqli->query($sqlConsumos);
    if (!$resultConsumos) {
        throw new Exception("No se pudieron obtener los insumos de la cita.");
    }

    $consumos = array();
    while ($row = $resultConsumos->fetch_assoc()) {
        $consumos[] = $row;
    }
    if (count($consumos) == 0) {
        return 0;
    }
    $faltanVariantes = array();
    foreach ($consumos as $consumo) {
        if ((int)$consumo["tiene_variantes"] === 1 && (int)$consumo["id_variante"] <= 0) {
            $faltanVariantes[] = normalizarTextoUtf8($consumo["nombre"]);
        }
    }
    if (count($faltanVariantes) > 0) {
        throw new Exception("Faltan seleccionar variantes de insumos: ".implode(", ", $faltanVariantes).".");
    }

    $grupoMovimiento = "agenda_".$idAgenda."_".date("YmdHis");
    $totalDescontados = 0;
    foreach ($consumos as $consumo) {
        $idConsumo = (int)$consumo["id"];
        $idInsumo = (int)$consumo["id_insumo"];
        $idVariante = (int)$consumo["id_variante"];
        $cantidad = normalizarNumeroAgenda($consumo["cantidad"]);
        $nombreInsumo = normalizarTextoUtf8($consumo["nombre"].($consumo["nombre_variante"] != "" ? " - ".$consumo["nombre_variante"] : ""));

        $resultStock = $mysqli->query("SELECT cantidad FROM insumo_stock_consultorio
            WHERE id_insumo = '".$idInsumo."'
              AND id_variante = '".$idVariante."'
              AND cod_local = '".$codLocal."'
              AND id_consultorio = '".$consultorio."'
            LIMIT 1 FOR UPDATE");
        $stockActual = 0;
        if ($resultStock && ($rowStock = $resultStock->fetch_assoc())) {
            $stockActual = normalizarNumeroAgenda($rowStock["cantidad"]);
        }
        if ($stockActual < $cantidad) {
            $mysqli->query("UPDATE agenda_consumo_insumos
                SET estado = IF(estado = 'previsto', 'confirmado', estado),
                    cantidad_confirmada = IF(cantidad_confirmada IS NULL, cantidad_prevista, cantidad_confirmada),
                    usuario_confirmo = IF(usuario_confirmo IS NULL, '".$usuario."', usuario_confirmo),
                    fecha_confirmo = IF(fecha_confirmo IS NULL, NOW(), fecha_confirmo)
                WHERE id = '".$idConsumo."' LIMIT 1");
            continue;
        }

        $nuevoStock = $stockActual - $cantidad;
        $stmtStock = $mysqli->prepare("INSERT INTO insumo_stock_consultorio (id_insumo, id_variante, cod_local, id_consultorio, cantidad)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE cantidad=VALUES(cantidad), fecha_actualizacion=CURRENT_TIMESTAMP");
        $stmtStock->bind_param("iiiid", $idInsumo, $idVariante, $codLocal, $consultorio, $nuevoStock);
        if (!$stmtStock->execute()) {
            throw new Exception("No se pudo descontar el stock de ".$nombreInsumo.".");
        }

        $motivo = "Salida automatica por cita atendida #".$idAgenda;
        $fecha = date("Y-m-d H:i:s");
        $tipo = "salida";
        $stmtMov = $mysqli->prepare("INSERT INTO movimientos_insumos (grupo_movimiento, tipo, insumo_id, id_variante, sucursal_id, consultorio_id, cantidad, motivo, usuario_id, fecha)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtMov->bind_param("ssiiiidsis", $grupoMovimiento, $tipo, $idInsumo, $idVariante, $codLocal, $consultorio, $cantidad, $motivo, $usuario, $fecha);
        if (!$stmtMov->execute()) {
            throw new Exception("No se pudo registrar el movimiento de insumo.");
        }

        $mysqli->query("UPDATE agenda_consumo_insumos
            SET stock_descontado = 1,
                cantidad_descontada = '".$cantidad."',
                fecha_descontado = NOW(),
                usuario_desconto = '".$usuario."',
                estado = IF(estado = 'previsto', 'confirmado', estado),
                cantidad_confirmada = IF(cantidad_confirmada IS NULL, cantidad_prevista, cantidad_confirmada),
                usuario_confirmo = IF(usuario_confirmo IS NULL, '".$usuario."', usuario_confirmo),
                fecha_confirmo = IF(fecha_confirmo IS NULL, NOW(), fecha_confirmo)
            WHERE id = '".$idConsumo."' LIMIT 1");
        $totalDescontados++;
    }

    return $totalDescontados;
}

function obtenerInsumosPrevistosAgendaSinPrepararEstructura($mysqli, $idAgenda, $estadoForzado = "")
{
    $idAgenda = (int)$idAgenda;
    $insumos = array();

    if (!tablaAgendaExiste($mysqli, "insumosconsl")) {
        return array();
    }

    if (tablaAgendaExiste($mysqli, "agenda_insumo_base")) {
        $resultBase = $mysqli->query("SELECT b.id_insumo, b.cantidad, i.unidad_medida
            FROM agenda_insumo_base b
            INNER JOIN insumosconsl i ON i.id_insumo = b.id_insumo
            WHERE b.estado = 'activo'");
        while ($row = $resultBase->fetch_assoc()) {
            $id = (int)$row["id_insumo"];
            if (!isset($insumos[$id])) {
                $insumos[$id] = array("id_insumo" => $id, "cantidad" => 0, "unidad_medida" => normalizarTextoUtf8($row["unidad_medida"]));
            }
            $insumos[$id]["cantidad"] += normalizarNumeroAgenda($row["cantidad"]);
        }
    }

    if (agendaEsPrimeraConsulta($mysqli, $idAgenda, $estadoForzado)) {
        agregarInsumosProductoPrimeraConsultaAgenda($mysqli, $insumos, false);
    }

    if (tablaAgendaExiste($mysqli, "agenda_tratamientos") && tablaAgendaExiste($mysqli, "insumo_producto")) {
        $sql = "SELECT ip.id_insumo,
                IF(LOWER(TRIM(i.descripcion)) = 'descartable', MAX(ip.cantidad), SUM(ip.cantidad)) AS cantidad,
                i.unidad_medida
            FROM agenda_tratamientos at
            INNER JOIN detalle_venta dv ON dv.cod_detalle = at.cod_detalle_ventaFK
            INNER JOIN insumo_producto ip ON ip.cod_producto = dv.cod_productoFK
            INNER JOIN insumosconsl i ON i.id_insumo = ip.id_insumo
            WHERE at.id_agenda = '".$idAgenda."'
              AND at.estado <> 'cancelado'
            GROUP BY ip.id_insumo, i.unidad_medida, i.descripcion";
        $resultTratamientos = $mysqli->query($sql);
        if ($resultTratamientos) {
            while ($row = $resultTratamientos->fetch_assoc()) {
                $id = (int)$row["id_insumo"];
                if (!isset($insumos[$id])) {
                    $insumos[$id] = array("id_insumo" => $id, "cantidad" => 0, "unidad_medida" => normalizarTextoUtf8($row["unidad_medida"]));
                }
                $insumos[$id]["cantidad"] += normalizarNumeroAgenda($row["cantidad"]);
            }
        }
    }

    return array_values($insumos);
}

function obtenerVariantesAgendaInsumo($mysqli, $idInsumo)
{
    $variantes = array();
    $result = $mysqli->query("SELECT id_variante, nombre_variante, stock, stock_minimo, estado
        FROM insumo_variantes
        WHERE insumo_id='".(int)$idInsumo."' AND estado='Activo'
        ORDER BY nombre_variante ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row["nombre_variante"] = normalizarTextoUtf8($row["nombre_variante"]);
            $variantes[] = $row;
        }
    }
    return $variantes;
}

function obtenerVariantesSeleccionadasAgenda($mysqli, $idAgenda)
{
    $selecciones = array();
    $result = $mysqli->query("SELECT id_insumo, id_variante FROM agenda_consumo_insumos WHERE id_agenda='".(int)$idAgenda."'");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $selecciones[(int)$row["id_insumo"]] = (int)$row["id_variante"];
        }
    }
    return $selecciones;
}

function guardarVarianteInsumoAgenda($mysqli, $useru)
{
    asegurarEstructuraAgendaInsumos($mysqli);
    $idAgenda = isset($_POST["id_agenda"]) ? (int)$_POST["id_agenda"] : 0;
    $idInsumo = isset($_POST["id_insumo"]) ? (int)$_POST["id_insumo"] : 0;
    $idVariante = isset($_POST["id_variante"]) ? (int)$_POST["id_variante"] : 0;
    if ($idAgenda <= 0 || $idInsumo <= 0 || $idVariante <= 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Seleccione una variante valida."));
    }
    $resultValida = $mysqli->query("SELECT id_variante FROM insumo_variantes WHERE id_variante='".$idVariante."' AND insumo_id='".$idInsumo."' AND estado='Activo' LIMIT 1");
    if (!$resultValida || $resultValida->num_rows == 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "La variante no corresponde al insumo."));
    }

    $insumos = obtenerInsumosPrevistosAgendaSinPrepararEstructura($mysqli, $idAgenda);
    $cantidad = 0;
    $unidad = "";
    foreach ($insumos as $insumo) {
        if ((int)$insumo["id_insumo"] === $idInsumo) {
            $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
            $unidad = limpiar($mysqli, $insumo["unidad_medida"]);
            break;
        }
    }
    if ($cantidad <= 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "El insumo no esta previsto en esta cita."));
    }

    $stmt = $mysqli->prepare("INSERT INTO agenda_consumo_insumos (id_agenda, id_insumo, id_variante, cantidad_prevista, unidad_medida, usuario_confirmo, fecha_confirmo)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE id_variante=VALUES(id_variante), cantidad_prevista=VALUES(cantidad_prevista), unidad_medida=VALUES(unidad_medida), usuario_confirmo=VALUES(usuario_confirmo), fecha_confirmo=NOW()");
    $stmt->bind_param("iiidsi", $idAgenda, $idInsumo, $idVariante, $cantidad, $unidad, $useru);
    if (!$stmt->execute()) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => $stmt->error));
    }
    responderJsonCalendar(array("1" => "exito", "mensaje" => "Variante guardada."));
}

function buscarVentasPacienteAgenda($mysqli)
{
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    if ($paciente == '') {
        responderJsonCalendar(array("1" => "exito", "ventas" => array()));
    }

    $sql = "SELECT v.cod_venta, v.num_factura, v.puntoexpedicion, v.fecha_venta, v.estadocuenta,
                   IFNULL(v.apodo,'') AS apodo, IFNULL(p.nombre_persona,'') AS paciente
            FROM venta v
            LEFT JOIN persona p ON p.cod_persona = v.cod_clienteFK
            LEFT JOIN cliente c ON c.cod_cliente = v.cod_clienteFK
            WHERE (
                    v.cod_clienteFK = '".$paciente."'
                    OR c.ci_cliente = '".$paciente."'
                    OR p.nombre_persona LIKE '%".$paciente."%'
              )
              AND UPPER(IFNULL(v.estado,'Activo')) <> 'INACTIVO'
            ORDER BY v.fecha_venta DESC, v.cod_venta DESC";
    $result = $mysqli->query($sql);
    if (!$result) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => $mysqli->error));
    }

    $ventas = array();
    while ($row = $result->fetch_assoc()) {
        $factura = trim((string)$row["puntoexpedicion"]) != '' ? $row["puntoexpedicion"]."-".$row["num_factura"] : $row["num_factura"];
        $ventas[] = array(
            "cod_venta" => (int)$row["cod_venta"],
            "num_factura" => normalizarTextoUtf8($factura),
            "fecha_venta" => $row["fecha_venta"],
            "apodo" => normalizarTextoUtf8($row["apodo"]),
            "paciente" => normalizarTextoUtf8($row["paciente"]),
            "estadocuenta" => normalizarTextoUtf8($row["estadocuenta"])
        );
    }

    responderJsonCalendar(array("1" => "exito", "ventas" => $ventas));
}

function listarTratamientosVentaAgenda($mysqli)
{
    asegurarEstructuraAgendaInsumos($mysqli);
    $venta = isset($_POST['cod_venta']) ? limpiar($mysqli, $_POST['cod_venta']) : '';
    $idAgenda = isset($_POST['id_agenda']) ? (int)$_POST['id_agenda'] : 0;
    if ($venta == '') {
        echo json_encode(array("1" => "exito", "tratamientos" => array()));
        exit;
    }

    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "p");
    $sql = "SELECT dv.cod_detalle, dv.cod_ventaFK, dv.cod_productoFK, dv.cantidad_detalle,
                   dv.estado, dv.estado_tratamiento, dv.progreso_porcentaje,
                   p.nombre_producto, p.unidad_producto,
                   ".$selectRiesgo.",
                   IF(at.cod_detalle_ventaFK IS NULL, 0, 1) AS seleccionado
            FROM detalle_venta dv
            INNER JOIN producto p ON p.cod_producto = dv.cod_productoFK
            LEFT JOIN agenda_tratamientos at
              ON at.cod_detalle_ventaFK = dv.cod_detalle
             AND at.id_agenda = '".$idAgenda."'
             AND at.estado <> 'cancelado'
            WHERE dv.cod_ventaFK = '".$venta."'
              AND IFNULL(dv.estado,'Activo') <> 'Inactivo'
              AND IFNULL(dv.estado_tratamiento,'Activo') <> 'Finalizado'
            ORDER BY p.nombre_producto ASC, dv.cod_detalle ASC";

    $result = $mysqli->query($sql);
    if (!$result) {
        echo json_encode(array("1" => "Error", "mensaje" => $mysqli->error));
        exit;
    }

    $tratamientos = array();
    while ($row = $result->fetch_assoc()) {
        $nivelRiesgo = ProductoRiesgoFinancieroNormalizar($row["nivel_riesgo_financiero"]);
        $tratamientos[] = array(
            "cod_detalle" => (int)$row["cod_detalle"],
            "cod_ventaFK" => (int)$row["cod_ventaFK"],
            "cod_productoFK" => $row["cod_productoFK"],
            "nombre_producto" => normalizarTextoUtf8($row["nombre_producto"]),
            "cantidad_detalle" => $row["cantidad_detalle"],
            "unidad_producto" => normalizarTextoUtf8($row["unidad_producto"]),
            "estado_tratamiento" => normalizarTextoUtf8($row["estado_tratamiento"]),
            "progreso_porcentaje" => (int)$row["progreso_porcentaje"],
            "nivel_riesgo_financiero" => $nivelRiesgo,
            "nivel_riesgo_texto" => ProductoRiesgoFinancieroTexto($nivelRiesgo),
            "nivel_riesgo_clase" => "riesgo-nivel-".$nivelRiesgo,
            "seleccionado" => (int)$row["seleccionado"]
        );
    }

    echo json_encode(array("1" => "exito", "tratamientos" => $tratamientos), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function planMadreTablasDisponiblesAgenda($mysqli)
{
    return tablaAgendaExiste($mysqli, "plan_definitivo_tratamiento")
        && tablaAgendaExiste($mysqli, "plan_definitivo_tratamiento_items")
        && tablaAgendaExiste($mysqli, "plan_definitivo_tratamiento_historial");
}

function productoClinicoWhereAgenda($productoAlias = "p")
{
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$productoAlias);
    if ($alias == "") { $alias = "p"; }
    return " AND UPPER(TRIM(".$alias.".nombre_producto)) NOT IN ('CREDITO','INTERES','GASTO ADMINISTRATIVO','CORRETAJE')";
}

function obtenerPacientePlanMadreAgenda($mysqli, $pacienteId)
{
    $pacienteId = (int)$pacienteId;
    if ($pacienteId <= 0) { return null; }
    $sql = "SELECT cl.cod_cliente, IFNULL(cl.ci_cliente,'') AS ci_cliente, IFNULL(p.nombre_persona,'') AS nombre_persona
            FROM cliente cl
            INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
            WHERE cl.cod_cliente = '".$pacienteId."'
            LIMIT 1";
    $result = $mysqli->query($sql);
    if (!$result || !($row = $result->fetch_assoc())) {
        return null;
    }
    return array(
        "paciente_id" => (int)$row["cod_cliente"],
        "cedula" => normalizarTextoUtf8($row["ci_cliente"]),
        "paciente" => normalizarTextoUtf8($row["nombre_persona"])
    );
}

function textoEstadoPlanMadreAgenda($estado, $version = 1)
{
    $estado = strtolower(trim((string)$estado));
    $version = (int)$version;
    if ($estado == "pendiente_validacion") { return "Vigente - Pendiente de validacion clinica"; }
    if ($estado == "definido") { return "Vigente - Validado clinicamente"; }
    if ($estado == "modificado") { return "Vigente - Modificado v".$version; }
    if ($estado == "borrador") { return "Borrador"; }
    return "Vigente";
}

function obtenerNumeroPlanMadreAgenda($mysqli, $pacienteId, $cedula, $planId)
{
    $pacienteId = (int)$pacienteId;
    $cedulaSql = limpiar($mysqli, $cedula);
    $planId = (int)$planId;
    $sql = "SELECT id
            FROM plan_definitivo_tratamiento
            WHERE activo = 1
              AND (paciente_id = '".$pacienteId."' OR cedula = '".$cedulaSql."')
            ORDER BY fecha_creacion ASC, id ASC";
    $result = $mysqli->query($sql);
    $numero = 1;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ((int)$row["id"] === $planId) {
                return $numero;
            }
            $numero++;
        }
    }
    return 1;
}

function armarPlanMadreAgenda($mysqli, $plan, $paciente)
{
    $apodo = "";
    $ventaBase = (int)$plan["venta_base_id"];
    if ($ventaBase > 0) {
        $resultVenta = $mysqli->query("SELECT IFNULL(apodo,'') AS apodo FROM venta WHERE cod_venta = '".$ventaBase."' LIMIT 1");
        if ($resultVenta && ($rowVenta = $resultVenta->fetch_assoc())) {
            $apodo = normalizarTextoUtf8($rowVenta["apodo"]);
        }
    }
    if (trim($apodo) == "") { $apodo = "Sin beneficiario"; }
    $numero = obtenerNumeroPlanMadreAgenda($mysqli, $paciente["paciente_id"], $paciente["cedula"], (int)$plan["id"]);
    $estadoPlan = strtolower(trim((string)$plan["estado"]));
    $requiereAprobacion = ($estadoPlan == "borrador" || $estadoPlan == "pendiente_validacion") ? 1 : 0;
    return array(
        "id" => (int)$plan["id"],
        "numero" => $numero,
        "label" => "Plan madre #".$numero." - ".$apodo,
        "estado" => normalizarTextoUtf8($plan["estado"]),
        "estado_texto" => textoEstadoPlanMadreAgenda($plan["estado"], $plan["version_actual"]),
        "pendiente_validacion" => $estadoPlan == "pendiente_validacion" ? 1 : 0,
        "requiere_aprobacion" => $requiereAprobacion,
        "aprobacion_texto" => $requiereAprobacion ? "Falta aprobacion del doctor de cabecera" : "",
        "version" => (int)$plan["version_actual"],
        "venta_base_id" => $ventaBase
    );
}

function obtenerPlanMadreActivoAgenda($mysqli, $paciente)
{
    if (!$paciente) { return null; }
    $pacienteId = (int)$paciente["paciente_id"];
    $cedula = limpiar($mysqli, $paciente["cedula"]);
    $sql = "SELECT pd.*
            FROM plan_definitivo_tratamiento pd
            LEFT JOIN venta vb ON vb.cod_venta = pd.venta_base_id
            WHERE pd.activo = 1
              AND (pd.paciente_id = '".$pacienteId."' OR pd.cedula = '".$cedula."')
            ORDER BY
              CASE WHEN vb.cod_venta IS NULL THEN 1 ELSE 0 END,
              vb.fecha_venta ASC,
              vb.cod_venta ASC,
              CASE
                WHEN pd.estado = 'definido' THEN 0
                WHEN pd.estado = 'modificado' THEN 1
                WHEN pd.estado = 'pendiente_validacion' THEN 2
                WHEN pd.estado = 'borrador' THEN 3
                ELSE 4
              END,
              pd.id DESC
            LIMIT 1";
    $result = $mysqli->query($sql);
    if (!$result || !($plan = $result->fetch_assoc())) {
        return null;
    }
    return $plan;
}

function obtenerPlanesMadrePacienteAgenda($mysqli, $paciente)
{
    if (!$paciente) { return array(); }
    $pacienteId = (int)$paciente["paciente_id"];
    $cedula = limpiar($mysqli, $paciente["cedula"]);
    $sql = "SELECT pd.*
            FROM plan_definitivo_tratamiento pd
            LEFT JOIN venta vb ON vb.cod_venta = pd.venta_base_id
            WHERE pd.activo = 1
              AND (pd.paciente_id = '".$pacienteId."' OR pd.cedula = '".$cedula."')
            ORDER BY
              CASE WHEN vb.cod_venta IS NULL THEN 1 ELSE 0 END,
              vb.fecha_venta ASC,
              vb.cod_venta ASC,
              CASE
                WHEN pd.estado = 'definido' THEN 0
                WHEN pd.estado = 'modificado' THEN 1
                WHEN pd.estado = 'pendiente_validacion' THEN 2
                WHEN pd.estado = 'borrador' THEN 3
                ELSE 4
              END,
              pd.id DESC";
    $result = $mysqli->query($sql);
    if (!$result) { return array(); }
    $planes = array();
    while ($row = $result->fetch_assoc()) {
        $planes[] = $row;
    }
    return $planes;
}

function normalizarAvanceTratamientoAgenda($avance)
{
    $avance = (int)$avance;
    if ($avance < 0) { $avance = 0; }
    if ($avance > 100) { $avance = 100; }
    return $avance;
}

function estadoTratamientoPlanAgenda($estado, $estadoTratamiento, $avance)
{
    $texto = strtolower(trim((string)$estado." ".(string)$estadoTratamiento));
    if ($avance >= 100 || strpos($texto, "completado") !== false || strpos($texto, "finalizado") !== false || strpos($texto, "finalizada") !== false) {
        return "completado";
    }
    if (strpos($texto, "eliminado") !== false || strpos($texto, "anulado") !== false || strpos($texto, "cancelado") !== false || strpos($texto, "inactivo") !== false) {
        return "cancelado";
    }
    if (($avance > 0 && $avance < 100) || strpos($texto, "proceso") !== false || strpos($texto, "iniciado") !== false || strpos($texto, "iniciada") !== false) {
        return "proceso";
    }
    return "pendiente";
}

function textoEstadoTratamientoPlanAgenda($estado)
{
    if ($estado == "completado") { return "Realizado"; }
    if ($estado == "proceso") { return "En proceso"; }
    if ($estado == "cancelado") { return "Anulado"; }
    return "Pendiente";
}

function grupoTratamientoPlanAgenda($estado, $avance)
{
    if ($estado == "completado" || $estado == "cancelado" || $avance >= 100) { return "finalizados"; }
    if ($estado == "proceso" || ($avance > 0 && $avance < 100)) { return "continuar"; }
    return "siguientes";
}

function compararTratamientoAgenda($a, $b, $campo, $direccion = "asc")
{
    $valorA = isset($a[$campo]) ? (float)$a[$campo] : 0;
    $valorB = isset($b[$campo]) ? (float)$b[$campo] : 0;
    if ($valorA == $valorB) { return 0; }
    $resultado = $valorA < $valorB ? -1 : 1;
    return $direccion == "desc" ? ($resultado * -1) : $resultado;
}

function ordenarTratamientosPlanAgenda($tratamientos)
{
    $grupos = array("continuar" => array(), "siguientes" => array(), "finalizados" => array());
    foreach ($tratamientos as $tratamiento) {
        $grupo = isset($tratamiento["grupo_plan"]) ? $tratamiento["grupo_plan"] : "siguientes";
        if (!isset($grupos[$grupo])) { $grupo = "siguientes"; }
        $grupos[$grupo][] = $tratamiento;
    }
    usort($grupos["continuar"], function($a, $b) {
        return compararTratamientoAgenda($a, $b, "riesgo_orden")
            ?: compararTratamientoAgenda($a, $b, "orden_natural")
            ?: compararTratamientoAgenda($a, $b, "avance", "desc");
    });
    usort($grupos["siguientes"], function($a, $b) {
        return compararTratamientoAgenda($a, $b, "riesgo_orden")
            ?: compararTratamientoAgenda($a, $b, "orden_natural")
            ?: compararTratamientoAgenda($a, $b, "precio_producto");
    });
    usort($grupos["finalizados"], function($a, $b) {
        return compararTratamientoAgenda($a, $b, "orden_natural");
    });
    $ordenados = array();
    foreach (array("continuar", "siguientes", "finalizados") as $grupo) {
        foreach ($grupos[$grupo] as $tratamiento) {
            $ordenados[] = $tratamiento;
        }
    }
    return $ordenados;
}

function obtenerTratamientosSugeridosPacienteAgenda($mysqli, $pacienteId, $idAgenda = 0)
{
    $pacienteId = (int)$pacienteId;
    $idAgenda = (int)$idAgenda;
    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "p");
    $sql = "SELECT dv.cod_detalle, dv.cod_ventaFK, dv.cod_productoFK, dv.cantidad_detalle,
                   dv.estado, dv.estado_tratamiento, dv.progreso_porcentaje,
                   p.nombre_producto, p.precio_producto, IFNULL(v.apodo,'') AS apodo,
                   ".$selectRiesgo.",
                   IF(at.cod_detalle_ventaFK IS NULL, 0, 1) AS seleccionado
            FROM detalle_venta dv
            INNER JOIN venta v ON v.cod_venta = dv.cod_ventaFK
            INNER JOIN producto p ON p.cod_producto = dv.cod_productoFK
            LEFT JOIN agenda_tratamientos at
              ON at.cod_detalle_ventaFK = dv.cod_detalle
             AND at.id_agenda = '".$idAgenda."'
             AND at.estado <> 'cancelado'
            WHERE v.cod_clienteFK = '".$pacienteId."'
              AND UPPER(IFNULL(v.estado,'Activo')) <> 'INACTIVO'
              AND IFNULL(dv.estado,'Activo') <> 'Inactivo'
              ".productoClinicoWhereAgenda("p")."
            ORDER BY v.fecha_venta ASC, dv.cod_detalle ASC";
    $result = $mysqli->query($sql);
    if (!$result) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => $mysqli->error));
    }
    $items = array();
    $ordenNatural = 0;
    while ($row = $result->fetch_assoc()) {
        $ordenNatural++;
        $avance = normalizarAvanceTratamientoAgenda($row["progreso_porcentaje"]);
        $estado = estadoTratamientoPlanAgenda($row["estado"], $row["estado_tratamiento"], $avance);
        $nivel = ProductoRiesgoFinancieroNormalizar(isset($row["nivel_riesgo_financiero"]) ? $row["nivel_riesgo_financiero"] : 1);
        $items[] = array(
            "cod_detalle" => (int)$row["cod_detalle"],
            "venta_id" => (int)$row["cod_ventaFK"],
            "producto_id" => normalizarTextoUtf8($row["cod_productoFK"]),
            "nombre_producto" => normalizarTextoUtf8($row["nombre_producto"]),
            "apodo" => normalizarTextoUtf8($row["apodo"]),
            "avance" => $avance,
            "estado_clase" => $estado,
            "estado_texto" => textoEstadoTratamientoPlanAgenda($estado),
            "grupo_plan" => grupoTratamientoPlanAgenda($estado, $avance),
            "nivel_riesgo_financiero" => $nivel,
            "nivel_riesgo_texto" => ProductoRiesgoFinancieroTexto($nivel),
            "nivel_riesgo_clase" => "riesgo-nivel-".$nivel,
            "riesgo_orden" => $nivel,
            "precio_producto" => isset($row["precio_producto"]) ? (float)$row["precio_producto"] : 0,
            "orden_natural" => $ordenNatural,
            "orden" => 0,
            "seleccionado" => (int)$row["seleccionado"],
            "seleccionable" => ($estado == "pendiente" || $estado == "proceso") ? 1 : 0,
            "origen" => "sugerido"
        );
    }
    $items = ordenarTratamientosPlanAgenda($items);
    for ($i = 0; $i < count($items); $i++) {
        $items[$i]["orden"] = $i + 1;
    }
    return $items;
}

function obtenerItemsPlanMadreAgenda($mysqli, $planId, $idAgenda = 0)
{
    $planId = (int)$planId;
    $idAgenda = (int)$idAgenda;
    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "p");
    $sql = "SELECT i.id AS plan_item_id, i.orden, i.venta_id, i.detalle_venta_id, i.producto_id,
                   COALESCE(p.nombre_producto, i.nombre_tratamiento_snapshot) AS nombre_producto,
                   dv.estado, dv.estado_tratamiento, dv.progreso_porcentaje,
                   IFNULL(v.apodo,'') AS apodo,
                   ".$selectRiesgo.",
                   IF(at.cod_detalle_ventaFK IS NULL, 0, 1) AS seleccionado
            FROM plan_definitivo_tratamiento_items i
            INNER JOIN detalle_venta dv ON dv.cod_detalle = i.detalle_venta_id
            INNER JOIN venta v ON v.cod_venta = i.venta_id
            LEFT JOIN producto p ON p.cod_producto = dv.cod_productoFK
            LEFT JOIN agenda_tratamientos at
              ON at.cod_detalle_ventaFK = i.detalle_venta_id
             AND at.id_agenda = '".$idAgenda."'
             AND at.estado <> 'cancelado'
            WHERE i.plan_definitivo_id = '".$planId."'
              AND i.activo = 1
              ".productoClinicoWhereAgenda("p")."
            ORDER BY i.orden ASC, i.id ASC";
    $result = $mysqli->query($sql);
    if (!$result) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => $mysqli->error));
    }
    $items = array();
    while ($row = $result->fetch_assoc()) {
        $avance = normalizarAvanceTratamientoAgenda($row["progreso_porcentaje"]);
        $estado = estadoTratamientoPlanAgenda($row["estado"], $row["estado_tratamiento"], $avance);
        $nivel = ProductoRiesgoFinancieroNormalizar(isset($row["nivel_riesgo_financiero"]) ? $row["nivel_riesgo_financiero"] : 1);
        $items[] = array(
            "plan_item_id" => (int)$row["plan_item_id"],
            "plan_id" => $planId,
            "cod_detalle" => (int)$row["detalle_venta_id"],
            "venta_id" => (int)$row["venta_id"],
            "producto_id" => normalizarTextoUtf8($row["producto_id"]),
            "nombre_producto" => normalizarTextoUtf8($row["nombre_producto"]),
            "apodo" => normalizarTextoUtf8($row["apodo"]),
            "avance" => $avance,
            "estado_clase" => $estado,
            "estado_texto" => textoEstadoTratamientoPlanAgenda($estado),
            "grupo_plan" => grupoTratamientoPlanAgenda($estado, $avance),
            "nivel_riesgo_financiero" => $nivel,
            "nivel_riesgo_texto" => ProductoRiesgoFinancieroTexto($nivel),
            "nivel_riesgo_clase" => "riesgo-nivel-".$nivel,
            "orden" => (int)$row["orden"],
            "seleccionado" => (int)$row["seleccionado"],
            "seleccionable" => ($estado == "pendiente" || $estado == "proceso") ? 1 : 0,
            "origen" => "plan_madre"
        );
    }
    return $items;
}

function obtenerPlanIdsDetallesAgenda($mysqli, $detalles)
{
    $ids = array();
    foreach ($detalles as $detalle) {
        $detalle = (int)$detalle;
        if ($detalle > 0) {
            $ids[] = $detalle;
        }
    }
    if (count($ids) == 0 || !tablaAgendaExiste($mysqli, "plan_definitivo_tratamiento_items")) {
        return array();
    }
    $result = $mysqli->query("SELECT DISTINCT plan_definitivo_id
        FROM plan_definitivo_tratamiento_items
        WHERE activo = 1
          AND detalle_venta_id IN (".implode(",", $ids).")");
    if (!$result) { return array(); }
    $planes = array();
    while ($row = $result->fetch_assoc()) {
        $planes[] = (int)$row["plan_definitivo_id"];
    }
    return array_values(array_unique($planes));
}

function obtenerPlanMadreAgenda($mysqli)
{
    asegurarEstructuraAgendaInsumos($mysqli);
    if (!planMadreTablasDisponiblesAgenda($mysqli)) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Debe aplicar la actualizacion SQL del plan madre."));
    }
    $pacienteId = isset($_POST["paciente"]) ? (int)$_POST["paciente"] : 0;
    $idAgenda = isset($_POST["id_agenda"]) ? (int)$_POST["id_agenda"] : 0;
    $paciente = obtenerPacientePlanMadreAgenda($mysqli, $pacienteId);
    if (!$paciente) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Seleccione un paciente valido."));
    }
    $planesPaciente = obtenerPlanesMadrePacienteAgenda($mysqli, $paciente);
    if (count($planesPaciente) > 0) {
        $planesRespuesta = array();
        $tratamientosTodos = array();
        $planPrincipal = null;
        $planPrincipalTieneSeleccion = false;
        $itemsPlanPrincipal = array();
        foreach ($planesPaciente as $planRow) {
            $planInfo = armarPlanMadreAgenda($mysqli, $planRow, $paciente);
            $itemsPlan = obtenerItemsPlanMadreAgenda($mysqli, (int)$planRow["id"], $idAgenda);
            $tieneSeleccion = false;
            foreach ($itemsPlan as $itemPlan) {
                if ((int)$itemPlan["seleccionado"] === 1) {
                    $tieneSeleccion = true;
                }
                $tratamientosTodos[] = $itemPlan;
            }
            $planInfo["tratamientos_count"] = count($itemsPlan);
            $planInfo["seleccionado_en_cita"] = $tieneSeleccion ? 1 : 0;
            if ($planPrincipal === null || ($tieneSeleccion && !$planPrincipalTieneSeleccion)) {
                $planPrincipal = $planInfo;
                $itemsPlanPrincipal = $itemsPlan;
                $planPrincipalTieneSeleccion = $tieneSeleccion;
            }
            $planesRespuesta[] = array(
                "plan" => $planInfo,
                "tratamientos" => $itemsPlan
            );
        }
        responderJsonCalendar(array(
            "1" => "exito",
            "modo" => "plan_madre",
            "paciente" => $paciente,
            "plan" => $planPrincipal,
            "planes" => $planesRespuesta,
            "tratamientos" => $tratamientosTodos,
            "tratamientos_plan_principal" => $itemsPlanPrincipal
        ));
    }
    $sugeridos = obtenerTratamientosSugeridosPacienteAgenda($mysqli, $pacienteId, $idAgenda);
    responderJsonCalendar(array(
        "1" => "exito",
        "modo" => "sin_plan",
        "paciente" => $paciente,
        "plan" => null,
        "tratamientos" => $sugeridos
    ));
}

function registrarHistorialPlanMadreAgenda($mysqli, $planId, $version, $accion, $descripcion, $valorAnterior, $valorNuevo, $usuarioId)
{
    if (!tablaAgendaExiste($mysqli, "plan_definitivo_tratamiento_historial")) {
        return;
    }
    $rol = "";
    $resultRol = $mysqli->query("SELECT IFNULL(tipo,'') AS tipo FROM usuario WHERE cod_usuario = '".(int)$usuarioId."' LIMIT 1");
    if ($resultRol && ($rowRol = $resultRol->fetch_assoc())) {
        $rol = $rowRol["tipo"];
    }
    $stmt = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento_historial
        (plan_definitivo_id, version, accion, descripcion, valor_anterior, valor_nuevo, motivo, usuario_id, rol, fecha_hora)
        VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, NOW())");
    if (!$stmt) { return; }
    $stmt->bind_param("iissssis", $planId, $version, $accion, $descripcion, $valorAnterior, $valorNuevo, $usuarioId, $rol);
    $stmt->execute();
}

function crearPlanMadreSugeridoAgenda($mysqli, $useru)
{
    asegurarEstructuraAgendaInsumos($mysqli);
    if (!planMadreTablasDisponiblesAgenda($mysqli)) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Debe aplicar la actualizacion SQL del plan madre."));
    }
    $pacienteId = isset($_POST["paciente"]) ? (int)$_POST["paciente"] : 0;
    $paciente = obtenerPacientePlanMadreAgenda($mysqli, $pacienteId);
    if (!$paciente) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Seleccione un paciente valido."));
    }
    $planExistente = obtenerPlanMadreActivoAgenda($mysqli, $paciente);
    if ($planExistente) {
        $planInfo = armarPlanMadreAgenda($mysqli, $planExistente, $paciente);
        responderJsonCalendar(array(
            "1" => "exito",
            "mensaje" => "El paciente ya tiene plan madre vigente.",
            "plan" => $planInfo,
            "tratamientos" => obtenerItemsPlanMadreAgenda($mysqli, (int)$planExistente["id"], 0)
        ));
    }
    $items = obtenerTratamientosSugeridosPacienteAgenda($mysqli, $pacienteId, 0);
    if (count($items) == 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "El paciente no tiene tratamientos disponibles para generar el plan madre."));
    }
    $ventaBase = (int)$items[0]["venta_id"];
    $cedulaSql = limpiar($mysqli, $paciente["cedula"]);
    $estado = "pendiente_validacion";
    $version = 1;
    $userInt = (int)$useru;
    $doctorCabeceraId = null;

    $mysqli->begin_transaction();
    try {
        $stmtPlan = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento
            (cedula, paciente_id, venta_base_id, doctor_cabecera_id, estado, version_actual, fecha_creacion, creado_por, fecha_actualizacion, actualizado_por, activo)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, NOW(), ?, 1)");
        if (!$stmtPlan) {
            throw new Exception("No se pudo preparar la creacion del plan madre.");
        }
        $stmtPlan->bind_param("siiisiii", $cedulaSql, $pacienteId, $ventaBase, $doctorCabeceraId, $estado, $version, $userInt, $userInt);
        if (!$stmtPlan->execute()) {
            throw new Exception("No se pudo crear el plan madre.");
        }
        $planId = (int)$mysqli->insert_id;
        $stmtItem = $mysqli->prepare("INSERT INTO plan_definitivo_tratamiento_items
            (plan_definitivo_id, venta_id, detalle_venta_id, producto_id, nombre_tratamiento_snapshot, nivel_riesgo_snapshot, orden, origen, activo, fecha_agregado, agregado_por)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'plan_principal', 1, NOW(), ?)");
        if (!$stmtItem) {
            throw new Exception("No se pudo preparar el detalle del plan madre.");
        }
        for ($i = 0; $i < count($items); $i++) {
            $orden = $i + 1;
            $ventaId = (int)$items[$i]["venta_id"];
            $detalleId = (int)$items[$i]["cod_detalle"];
            $productoId = (string)$items[$i]["producto_id"];
            $nombre = (string)$items[$i]["nombre_producto"];
            $nivel = (int)$items[$i]["nivel_riesgo_financiero"];
            $stmtItem->bind_param("iiissiii", $planId, $ventaId, $detalleId, $productoId, $nombre, $nivel, $orden, $userInt);
            if (!$stmtItem->execute()) {
                throw new Exception("No se pudieron copiar los tratamientos sugeridos.");
            }
        }
        registrarHistorialPlanMadreAgenda($mysqli, $planId, 1, "creacion_desde_agenda", "Se genero el plan madre vigente desde agenda.", "", "Pendiente de validacion clinica", $userInt);
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        responderJsonCalendar(array("1" => "Error", "mensaje" => $e->getMessage()));
    }

    $planNuevo = obtenerPlanMadreActivoAgenda($mysqli, $paciente);
    $planInfo = armarPlanMadreAgenda($mysqli, $planNuevo, $paciente);
    responderJsonCalendar(array(
        "1" => "exito",
        "mensaje" => "Plan madre vigente generado. Queda pendiente de validacion clinica.",
        "plan" => $planInfo,
        "tratamientos" => obtenerItemsPlanMadreAgenda($mysqli, (int)$planNuevo["id"], 0)
    ));
}

function obtenerCodLocalConsultorioAgenda($mysqli, $idConsultorio)
{
    $idConsultorio = (int)$idConsultorio;
    $result = $mysqli->query("SELECT cod_localFk FROM consultorios WHERE id_consultorio = '".$idConsultorio."' LIMIT 1");
    if ($result && ($row = $result->fetch_assoc())) {
        return (int)$row["cod_localFk"];
    }
    return 0;
}

function obtenerInsumosPrevistosAgenda($mysqli, $idAgenda, $codVenta = 0, $detalles = array(), $consultorio = 0, $estadoAgenda = "", $prepararEstructura = true)
{
    if ($prepararEstructura) {
        asegurarEstructuraAgendaInsumos($mysqli);
    }
    $idAgenda = (int)$idAgenda;
    $codVenta = (int)$codVenta;
    $insumos = array();
    $esPrimeraConsulta = agendaEsPrimeraConsulta($mysqli, $idAgenda, $estadoAgenda);

    if (!tablaAgendaExiste($mysqli, "insumosconsl")) {
        return array();
    }

    if ($idAgenda > 0 && count($detalles) == 0) {
        $resultDetalles = $mysqli->query("SELECT cod_detalle_ventaFK FROM agenda_tratamientos WHERE id_agenda = '".$idAgenda."' AND estado <> 'cancelado'");
        if ($resultDetalles) {
            while ($row = $resultDetalles->fetch_assoc()) {
                $detalles[] = (int)$row["cod_detalle_ventaFK"];
            }
        }
    }

    if (count($detalles) == 0 && !$esPrimeraConsulta) {
        return array();
    }

    $seleccionesVariantes = obtenerVariantesSeleccionadasAgenda($mysqli, $idAgenda);

    $resultBase = $mysqli->query("SELECT b.id_insumo, b.cantidad, i.nombre, i.unidad_medida, i.stock_minimo, i.tiene_variantes, i.tipo_variante
        FROM agenda_insumo_base b
        INNER JOIN insumosconsl i ON i.id_insumo = b.id_insumo
        WHERE b.estado = 'activo'");
    if ($resultBase) {
        while ($row = $resultBase->fetch_assoc()) {
            $id = (int)$row["id_insumo"];
            if (!isset($insumos[$id])) {
                $insumos[$id] = array(
                    "id_insumo" => $id,
                    "nombre" => normalizarTextoUtf8($row["nombre"]),
                    "unidad_medida" => normalizarTextoUtf8($row["unidad_medida"]),
                    "cantidad" => 0,
                    "stock_minimo" => normalizarNumeroAgenda($row["stock_minimo"]),
                    "stock" => null,
                    "faltante" => 0,
                    "tiene_variantes" => (int)$row["tiene_variantes"],
                    "tipo_variante" => normalizarTextoUtf8($row["tipo_variante"]),
                    "id_variante" => isset($seleccionesVariantes[$id]) ? (int)$seleccionesVariantes[$id] : 0,
                    "variantes" => obtenerVariantesAgendaInsumo($mysqli, $id)
                );
            }
            $insumos[$id]["cantidad"] += normalizarNumeroAgenda($row["cantidad"]);
        }
    }

    if ($esPrimeraConsulta) {
        agregarInsumosProductoPrimeraConsultaAgenda($mysqli, $insumos, true);
    }

    $ids = array();
    foreach ($detalles as $detalle) {
        $detalle = (int)$detalle;
        if ($detalle > 0) {
            $ids[] = $detalle;
        }
    }
    if (count($ids) > 0) {
        $sql = "SELECT ip.id_insumo,
                    IF(LOWER(TRIM(i.descripcion)) = 'descartable', MAX(ip.cantidad), SUM(ip.cantidad)) AS cantidad,
                    i.nombre, i.unidad_medida, i.stock_minimo, i.tiene_variantes, i.tipo_variante
                FROM detalle_venta dv
                INNER JOIN insumo_producto ip ON ip.cod_producto = dv.cod_productoFK
                INNER JOIN insumosconsl i ON i.id_insumo = ip.id_insumo
                WHERE dv.cod_detalle IN (".implode(",", $ids).")
                GROUP BY ip.id_insumo, i.descripcion, i.nombre, i.unidad_medida, i.stock_minimo, i.tiene_variantes, i.tipo_variante";
        $resultTrat = $mysqli->query($sql);
        if ($resultTrat) {
            while ($row = $resultTrat->fetch_assoc()) {
                $id = (int)$row["id_insumo"];
                if (!isset($insumos[$id])) {
                    $insumos[$id] = array(
                        "id_insumo" => $id,
                        "nombre" => normalizarTextoUtf8($row["nombre"]),
                        "unidad_medida" => normalizarTextoUtf8($row["unidad_medida"]),
                        "cantidad" => 0,
                        "stock_minimo" => normalizarNumeroAgenda($row["stock_minimo"]),
                        "stock" => null,
                        "faltante" => 0,
                        "tiene_variantes" => (int)$row["tiene_variantes"],
                        "tipo_variante" => normalizarTextoUtf8($row["tipo_variante"]),
                        "id_variante" => isset($seleccionesVariantes[$id]) ? (int)$seleccionesVariantes[$id] : 0,
                        "variantes" => obtenerVariantesAgendaInsumo($mysqli, $id)
                    );
                }
                $insumos[$id]["cantidad"] += normalizarNumeroAgenda($row["cantidad"]);
            }
        }
    }

    foreach ($insumos as $id => $insumo) {
        $insumos[$id]["id_variante"] = isset($seleccionesVariantes[$id]) ? (int)$seleccionesVariantes[$id] : (int)(isset($insumos[$id]["id_variante"]) ? $insumos[$id]["id_variante"] : 0);
        if ((int)(isset($insumos[$id]["tiene_variantes"]) ? $insumos[$id]["tiene_variantes"] : 0) === 1 && count($insumos[$id]["variantes"]) == 0) {
            $insumos[$id]["variantes"] = obtenerVariantesAgendaInsumo($mysqli, $id);
        }
    }

    if ($consultorio > 0) {
        $codLocal = obtenerCodLocalConsultorioAgenda($mysqli, $consultorio);
        foreach ($insumos as $id => $insumo) {
            $idVariante = (int)(isset($insumos[$id]["id_variante"]) ? $insumos[$id]["id_variante"] : 0);
            $stockMinimo = normalizarNumeroAgenda($insumos[$id]["stock_minimo"]);
            if ((int)$insumos[$id]["tiene_variantes"] === 1) {
                $stockMinimo = 0;
                $insumos[$id]["nombre_variante"] = "";
                foreach ($insumos[$id]["variantes"] as $variante) {
                    if ((int)$variante["id_variante"] === $idVariante) {
                        $stockMinimo = normalizarNumeroAgenda($variante["stock_minimo"]);
                        $insumos[$id]["nombre_variante"] = $variante["nombre_variante"];
                        break;
                    }
                }
            } else {
                $insumos[$id]["nombre_variante"] = "";
            }
            $resultStock = $mysqli->query("SELECT cantidad FROM insumo_stock_consultorio WHERE id_insumo = '".$id."' AND id_variante = '".$idVariante."' AND cod_local = '".$codLocal."' AND id_consultorio = '".(int)$consultorio."' LIMIT 1");
            $stock = 0;
            if ($resultStock && ($rowStock = $resultStock->fetch_assoc())) {
                $stock = normalizarNumeroAgenda($rowStock["cantidad"]);
            }
            $stockDespues = $stock - normalizarNumeroAgenda($insumos[$id]["cantidad"]);
            $insumos[$id]["stock"] = $stock;
            $insumos[$id]["stock_minimo"] = $stockMinimo;
            $insumos[$id]["faltante"] = $stockMinimo > 0 ? max(0, $stockMinimo - $stockDespues) : 0;
        }
    }

    return array_values($insumos);
}

function guardarTratamientosAgenda($mysqli, $useru)
{
    asegurarEstructuraAgendaInsumos($mysqli);
    $idAgenda = isset($_POST['id_agenda']) ? (int)$_POST['id_agenda'] : 0;
    $codVenta = isset($_POST['cod_venta']) ? (int)$_POST['cod_venta'] : 0;
    $detalles = isset($_POST['detalles']) ? $_POST['detalles'] : array();
    if (!is_array($detalles)) {
        $detalles = $detalles != '' ? explode(",", $detalles) : array();
    }

    if ($idAgenda <= 0) {
        echo json_encode(array("1" => "Error", "mensaje" => "Falta el agendamiento."));
        exit;
    }

    $ids = array();
    foreach ($detalles as $detalle) {
        $detalle = (int)$detalle;
        if ($detalle > 0) {
            $ids[] = $detalle;
        }
    }
    if (count($ids) > 0) {
        $resultVentaPrimerDetalle = $mysqli->query("SELECT cod_ventaFK FROM detalle_venta WHERE cod_detalle = '".$ids[0]."' LIMIT 1");
        if ($resultVentaPrimerDetalle && ($rowVentaPrimerDetalle = $resultVentaPrimerDetalle->fetch_assoc())) {
            $codVenta = (int)$rowVentaPrimerDetalle["cod_ventaFK"];
        }
    }
    $planesSeleccionados = obtenerPlanIdsDetallesAgenda($mysqli, $ids);
    if (count($planesSeleccionados) > 1) {
        echo json_encode(array("1" => "Error", "mensaje" => "La cita solo puede incluir tratamientos de un plan madre. Para otro plan madre, genere otra cita."));
        exit;
    }

    $mysqli->begin_transaction();
    try {
        $mysqli->query("DELETE FROM agenda_tratamientos WHERE id_agenda = '".$idAgenda."' AND estado = 'previsto'");
        $primerDetalle = count($ids) > 0 ? $ids[0] : "NULL";
        $codVentaSql = $codVenta > 0 ? "'".$codVenta."'" : "NULL";
        $mysqli->query("UPDATE agenda SET cod_ventaFK = ".$codVentaSql.", cod_detalle_ventaFK = ".$primerDetalle." WHERE id_agenda = '".$idAgenda."' LIMIT 1");

        foreach ($ids as $detalle) {
            $resultValida = $mysqli->query("SELECT cod_ventaFK FROM detalle_venta WHERE cod_detalle = '".$detalle."' LIMIT 1");
            if (!$resultValida || !($rowValida = $resultValida->fetch_assoc())) {
                throw new Exception("Tratamiento invalido.");
            }
            $ventaDetalle = (int)$rowValida["cod_ventaFK"];
            $mysqli->query("INSERT INTO agenda_tratamientos (id_agenda, cod_ventaFK, cod_detalle_ventaFK, creado_por)
                VALUES ('".$idAgenda."', '".$ventaDetalle."', '".$detalle."', '".(int)$useru."')
                ON DUPLICATE KEY UPDATE estado='previsto', cod_ventaFK=VALUES(cod_ventaFK)");
        }

        $mysqli->query("DELETE FROM agenda_consumo_insumos WHERE id_agenda = '".$idAgenda."' AND estado = 'previsto'");
        $agenda = obtenerAgendaAuditoria($mysqli, $idAgenda);
        $consultorio = $agenda ? (int)$agenda["id_consultorio"] : 0;
        $insumos = obtenerInsumosPrevistosAgenda($mysqli, $idAgenda, $codVenta, $ids, $consultorio);
        foreach ($insumos as $insumo) {
            $idInsumo = (int)$insumo["id_insumo"];
            $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
            $unidad = limpiar($mysqli, $insumo["unidad_medida"]);
            $mysqli->query("INSERT INTO agenda_consumo_insumos (id_agenda, id_insumo, cantidad_prevista, unidad_medida)
                VALUES ('".$idAgenda."', '".$idInsumo."', '".$cantidad."', '".$unidad."')
                ON DUPLICATE KEY UPDATE cantidad_prevista=VALUES(cantidad_prevista), unidad_medida=VALUES(unidad_medida)");
        }

        crearComentario($idAgenda, "@{0}: @{".$useru."} actualizo los tratamientos previstos de la cita.");
        $mysqli->commit();
        echo json_encode(array("1" => "exito", "mensaje" => "Tratamientos guardados.", "insumos" => $insumos), JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(array("1" => "Error", "mensaje" => $e->getMessage()));
        exit;
    }
}

function obtenerPrevisionInsumosAgendaEndpoint($mysqli)
{
    $idAgenda = isset($_POST['id_agenda']) ? (int)$_POST['id_agenda'] : 0;
    $codVenta = isset($_POST['cod_venta']) ? (int)$_POST['cod_venta'] : 0;
    $consultorio = isset($_POST['consultorio']) ? (int)$_POST['consultorio'] : 0;
    $estadoAgenda = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : "";
    $detalles = isset($_POST['detalles']) ? $_POST['detalles'] : array();
    if (!is_array($detalles)) {
        $detalles = $detalles != '' ? explode(",", $detalles) : array();
    }
    $insumos = obtenerInsumosPrevistosAgenda($mysqli, $idAgenda, $codVenta, $detalles, $consultorio, $estadoAgenda);
    $faltantes = array();
    foreach ($insumos as $insumo) {
        if (normalizarNumeroAgenda($insumo["faltante"]) > 0) {
            $faltantes[] = $insumo;
        }
    }
    echo json_encode(array("1" => "exito", "insumos" => $insumos, "faltantes" => $faltantes), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function generarInformeInsumosAgendaEndpoint($mysqli, $useru)
{
    asegurarEstructuraAgendaInsumos($mysqli);

    $periodo = isset($_POST["periodo"]) ? strtolower(limpiar($mysqli, $_POST["periodo"])) : "dia";
    $fechaBase = isset($_POST["fecha_base"]) ? limpiar($mysqli, $_POST["fecha_base"]) : date("Y-m-d");
    $tipoAlcance = isset($_POST["tipo_alcance"]) ? strtolower(limpiar($mysqli, $_POST["tipo_alcance"])) : "sucursal";
    $idSucursal = isset($_POST["id_sucursal"]) ? (int)$_POST["id_sucursal"] : 0;
    $idConsultorio = isset($_POST["id_consultorio"]) ? (int)$_POST["id_consultorio"] : 0;

    if ($periodo !== "semana") {
        $periodo = "dia";
    }
    if ($tipoAlcance !== "consultorio") {
        $tipoAlcance = "sucursal";
        $idConsultorio = 0;
    }
    if ($idSucursal <= 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Seleccione una sucursal."));
    }
    if ($tipoAlcance === "consultorio" && $idConsultorio <= 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Seleccione un consultorio."));
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaBase)) {
        $fechaBase = date("Y-m-d");
    }

    $rango = obtenerRangoInformeInsumosAgenda($fechaBase, $periodo);
    $historicoCache = array();
    $contexto = array(
        "cod_local" => $idSucursal,
        "id_consultorio" => $tipoAlcance === "consultorio" ? $idConsultorio : 0
    );

    $citasPeriodo = obtenerCitasInsumosInformeAgenda($mysqli, $rango["desde"], $rango["hasta"], $idSucursal, $idConsultorio);
    $detalleDias = array();
    $resumenPeriodo = array();
    $hayEstimadas = false;

    foreach ($citasPeriodo as $cita) {
        $insumos = obtenerInsumosPrevistosAgenda($mysqli, (int)$cita["id_agenda"], (int)$cita["cod_ventaFK"], $cita["tratamientos_ids"], (int)$cita["id_consultorio"]);
        foreach ($insumos as $insumo) {
            $items = resolverVariantesInformeInsumosAgenda($mysqli, $insumo, $contexto, $historicoCache);
            foreach ($items as $item) {
                if ($item["cantidad"] <= 0) {
                    continue;
                }
                if (!isset($detalleDias[$cita["fecha"]])) {
                    $detalleDias[$cita["fecha"]] = array();
                }
                agregarItemInformeInsumosAgenda($detalleDias[$cita["fecha"]], $item);
                agregarItemInformeInsumosAgenda($resumenPeriodo, $item);
                if ($item["estimado"]) {
                    $hayEstimadas = true;
                }
            }
        }
    }

    ksort($detalleDias);
    ordenarItemsInformeInsumosAgenda($resumenPeriodo);
    foreach ($detalleDias as $fecha => $itemsDia) {
        ordenarItemsInformeInsumosAgenda($detalleDias[$fecha]);
    }

    $hoy = date("Y-m-d");
    $ultimaFecha = obtenerUltimaFechaFuturaInformeInsumosAgenda($mysqli, $hoy, $idSucursal, $idConsultorio);
    $proyeccion = array();
    $compras = array();
    $consumoFuturo = array();
    $consumoDiarioFuturo = array();

    if ($ultimaFecha !== "") {
        $citasFuturas = obtenerCitasInsumosInformeAgenda($mysqli, $hoy, $ultimaFecha, $idSucursal, $idConsultorio);
        foreach ($citasFuturas as $cita) {
            $insumos = obtenerInsumosPrevistosAgenda($mysqli, (int)$cita["id_agenda"], (int)$cita["cod_ventaFK"], $cita["tratamientos_ids"], (int)$cita["id_consultorio"]);
            foreach ($insumos as $insumo) {
                $items = resolverVariantesInformeInsumosAgenda($mysqli, $insumo, $contexto, $historicoCache);
                foreach ($items as $item) {
                    if ($item["cantidad"] <= 0) {
                        continue;
                    }
                    agregarItemInformeInsumosAgenda($consumoFuturo, $item);
                    if (!isset($consumoDiarioFuturo[$item["clave"]])) {
                        $consumoDiarioFuturo[$item["clave"]] = array(
                            "item" => $item,
                            "dias" => array()
                        );
                    }
                    if (!isset($consumoDiarioFuturo[$item["clave"]]["dias"][$cita["fecha"]])) {
                        $consumoDiarioFuturo[$item["clave"]]["dias"][$cita["fecha"]] = 0;
                    }
                    $consumoDiarioFuturo[$item["clave"]]["dias"][$cita["fecha"]] += $item["cantidad"];
                    if ($item["estimado"]) {
                        $hayEstimadas = true;
                    }
                }
            }
        }
        ordenarItemsInformeInsumosAgenda($consumoFuturo);

        foreach ($consumoFuturo as $clave => $item) {
            $stockActual = obtenerStockActualInformeInsumosAgenda($mysqli, $item["id_insumo"], $item["id_variante"], $idSucursal, $idConsultorio);
            $stockMinimo = obtenerStockMinimoInformeInsumosAgenda($mysqli, $item["id_insumo"], $item["id_variante"]);
            $stockCorriendo = $stockActual;
            $alcanzaHasta = formatearFechaInformeInsumosAgenda($ultimaFecha);
            $dias = isset($consumoDiarioFuturo[$clave]) ? $consumoDiarioFuturo[$clave]["dias"] : array();
            ksort($dias);
            foreach ($dias as $fecha => $cantidadDia) {
                if ($stockCorriendo < $cantidadDia) {
                    $alcanzaHasta = formatearFechaInformeInsumosAgenda($fecha);
                    break;
                }
                $stockCorriendo -= $cantidadDia;
            }

            $proyeccion[$clave] = array(
                "nombre" => $item["nombre"],
                "unidad" => $item["unidad"],
                "stock_actual" => $stockActual,
                "consumo_futuro" => $item["cantidad"],
                "alcanza_hasta" => $alcanzaHasta,
                "ultima_fecha" => $ultimaFecha,
                "estimado" => $item["estimado"]
            );

            $stockFinal = $stockActual - $item["cantidad"];
            $comprar = max(0, ($item["cantidad"] + $stockMinimo) - $stockActual);
            if ($comprar > 0) {
                $faltanteAgenda = max(0, $item["cantidad"] - $stockActual);
                $paraMinimo = max(0, $stockMinimo - max(0, $stockActual - $item["cantidad"]));
                $motivo = $faltanteAgenda > 0
                    ? formatearCantidadInformeInsumosAgenda($faltanteAgenda)." para cubrir agenda + ".formatearCantidadInformeInsumosAgenda($paraMinimo)." para volver al minimo"
                    : "Al finalizar quedaria debajo del minimo";
                $compras[$clave] = array(
                    "nombre" => $item["nombre"],
                    "unidad" => $item["unidad"],
                    "stock_actual" => $stockActual,
                    "consumo_futuro" => $item["cantidad"],
                    "stock_minimo" => $stockMinimo,
                    "comprar" => $comprar,
                    "motivo" => $motivo
                );
            }
        }
        ordenarItemsInformeInsumosAgenda($proyeccion);
        ordenarItemsInformeInsumosAgenda($compras);
    }

    $meta = obtenerMetaInformeInsumosAgenda($mysqli, $idSucursal, $idConsultorio, $useru, $tipoAlcance, $periodo, $rango);
    $html = generarHtmlInformeInsumosAgenda($meta, $resumenPeriodo, $detalleDias, $proyeccion, $compras, $ultimaFecha, $hayEstimadas);
    responderJsonCalendar(array(
        "1" => "exito",
        "html" => $html,
        "archivo" => "informe_insumos_".$periodo."_".$rango["desde"].".pdf"
    ));
}

function catalogosInformeInsumosAgendaEndpoint($mysqli, $useru)
{
    $verTodos = isset($_POST['ver_todos_consoltorios']) ? limpiar($mysqli, $_POST['ver_todos_consoltorios']) : 'true';
    $condicionPermiso = $verTodos == 'false' ? " AND c.cod_doctorFK = '".(int)$useru."'" : "";

    $locales = array();
    $consultorios = array();

    $sqlConsultorios = "SELECT c.id_consultorio, c.nombre, c.cod_localFk, l.Nombre AS nombre_local
        FROM consultorios c
        LEFT JOIN local l ON l.cod_local = c.cod_localFk
        WHERE UPPER(c.estado) = 'ACTIVO' ".$condicionPermiso."
        ORDER BY l.Nombre ASC, c.nombre ASC";
    $resultConsultorios = $mysqli->query($sqlConsultorios);
    if (!$resultConsultorios) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "No se pudieron cargar los consultorios: ".$mysqli->error));
    }

    $localesVistos = array();
    while ($row = $resultConsultorios->fetch_assoc()) {
        $codLocal = (int)$row["cod_localFk"];
        $nombreLocal = normalizarTextoUtf8($row["nombre_local"]);
        if ($codLocal > 0 && !isset($localesVistos[$codLocal])) {
            $localesVistos[$codLocal] = true;
            $locales[] = array(
                "cod_local" => $codLocal,
                "Nombre" => $nombreLocal != "" ? $nombreLocal : "Sucursal ".$codLocal
            );
        }
        $consultorios[] = array(
            "id_consultorio" => (int)$row["id_consultorio"],
            "id" => (int)$row["id_consultorio"],
            "nombre" => normalizarTextoUtf8($row["nombre"]),
            "cod_localFk" => $codLocal,
            "nombre_local" => $nombreLocal
        );
    }

    responderJsonCalendar(array(
        "1" => "exito",
        "locales" => $locales,
        "consultorios" => $consultorios
    ));
}

function proyeccionInsumosConsultorioAgendaEndpoint($mysqli)
{
    $idSucursal = isset($_POST["id_sucursal"]) ? (int)$_POST["id_sucursal"] : 0;
    $idConsultorio = isset($_POST["id_consultorio"]) ? (int)$_POST["id_consultorio"] : 0;

    if ($idConsultorio <= 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "Seleccione un consultorio."));
    }

    if ($idSucursal <= 0) {
        $resultConsultorio = $mysqli->query("SELECT cod_localFk FROM consultorios WHERE id_consultorio='".(int)$idConsultorio."' LIMIT 1");
        if ($resultConsultorio && ($rowConsultorio = $resultConsultorio->fetch_assoc())) {
            $idSucursal = (int)$rowConsultorio["cod_localFk"];
        }
    }

    if ($idSucursal <= 0) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "No se pudo determinar la sucursal del consultorio."));
    }

    $historicoCache = array();
    $contexto = array(
        "cod_local" => $idSucursal,
        "id_consultorio" => $idConsultorio
    );
    $hoy = date("Y-m-d");
    $ultimaFecha = obtenerUltimaFechaFuturaInformeInsumosAgenda($mysqli, $hoy, $idSucursal, $idConsultorio);
    $proyeccion = array();
    $compras = array();
    $consumoFuturo = array();
    $consumoDiarioFuturo = array();
    $hayEstimadas = false;

    if ($ultimaFecha !== "") {
        $citasFuturas = obtenerCitasInsumosInformeAgenda($mysqli, $hoy, $ultimaFecha, $idSucursal, $idConsultorio);
        foreach ($citasFuturas as $cita) {
            $insumos = obtenerInsumosPrevistosAgenda($mysqli, (int)$cita["id_agenda"], (int)$cita["cod_ventaFK"], $cita["tratamientos_ids"], (int)$cita["id_consultorio"]);
            foreach ($insumos as $insumo) {
                $items = resolverVariantesInformeInsumosAgenda($mysqli, $insumo, $contexto, $historicoCache);
                foreach ($items as $item) {
                    if ($item["cantidad"] <= 0) {
                        continue;
                    }
                    agregarItemInformeInsumosAgenda($consumoFuturo, $item);
                    if (!isset($consumoDiarioFuturo[$item["clave"]])) {
                        $consumoDiarioFuturo[$item["clave"]] = array("dias" => array());
                    }
                    if (!isset($consumoDiarioFuturo[$item["clave"]]["dias"][$cita["fecha"]])) {
                        $consumoDiarioFuturo[$item["clave"]]["dias"][$cita["fecha"]] = 0;
                    }
                    $consumoDiarioFuturo[$item["clave"]]["dias"][$cita["fecha"]] += $item["cantidad"];
                    if ($item["estimado"]) {
                        $hayEstimadas = true;
                    }
                }
            }
        }

        ordenarItemsInformeInsumosAgenda($consumoFuturo);

        foreach ($consumoFuturo as $clave => $item) {
            $stockActual = obtenerStockActualInformeInsumosAgenda($mysqli, $item["id_insumo"], $item["id_variante"], $idSucursal, $idConsultorio);
            $stockMinimo = obtenerStockMinimoInformeInsumosAgenda($mysqli, $item["id_insumo"], $item["id_variante"]);
            $stockCorriendo = $stockActual;
            $alcanzaHasta = formatearFechaInformeInsumosAgenda($ultimaFecha);
            $dias = isset($consumoDiarioFuturo[$clave]) ? $consumoDiarioFuturo[$clave]["dias"] : array();
            ksort($dias);
            foreach ($dias as $fecha => $cantidadDia) {
                if ($stockCorriendo < $cantidadDia) {
                    $alcanzaHasta = formatearFechaInformeInsumosAgenda($fecha);
                    break;
                }
                $stockCorriendo -= $cantidadDia;
            }

            $comprar = max(0, ($item["cantidad"] + $stockMinimo) - $stockActual);
            $proyeccion[$clave] = array(
                "nombre" => $item["nombre"],
                "unidad" => $item["unidad"],
                "stock_actual" => $stockActual,
                "consumo_futuro" => $item["cantidad"],
                "alcanza_hasta" => $alcanzaHasta,
                "ultima_fecha" => $ultimaFecha,
                "estimado" => $item["estimado"],
                "requiere_compra" => $comprar > 0
            );

            if ($comprar > 0) {
                $compras[$clave] = array(
                    "nombre" => $item["nombre"],
                    "unidad" => $item["unidad"],
                    "stock_actual" => $stockActual,
                    "consumo_futuro" => $item["cantidad"],
                    "stock_minimo" => $stockMinimo,
                    "comprar" => $comprar
                );
            }
        }

        ordenarItemsInformeInsumosAgenda($proyeccion);
        ordenarItemsInformeInsumosAgenda($compras);
    }

    responderJsonCalendar(array(
        "1" => "exito",
        "ultima_fecha" => $ultimaFecha,
        "hay_estimadas" => $hayEstimadas,
        "proyeccion" => array_values($proyeccion),
        "compras" => array_values($compras)
    ));
}

function obtenerRangoInformeInsumosAgenda($fechaBase, $periodo)
{
    $fecha = DateTime::createFromFormat("Y-m-d", $fechaBase);
    if (!$fecha) {
        $fecha = new DateTime();
    }
    if ($periodo === "semana") {
        $diaSemana = (int)$fecha->format("N");
        $inicio = clone $fecha;
        $inicio->modify("-".($diaSemana - 1)." days");
        $fin = clone $inicio;
        $fin->modify("+6 days");
        return array("desde" => $inicio->format("Y-m-d"), "hasta" => $fin->format("Y-m-d"));
    }
    return array("desde" => $fecha->format("Y-m-d"), "hasta" => $fecha->format("Y-m-d"));
}

function obtenerCitasInsumosInformeAgenda($mysqli, $desde, $hasta, $codLocal, $idConsultorio)
{
    $condicionConsultorio = $idConsultorio > 0 ? " AND a.id_consultorio = '".(int)$idConsultorio."'" : "";
    $sql = "SELECT a.id_agenda, a.id_consultorio, a.cod_ventaFK, a.cod_detalle_ventaFK, a.fecha,
                   (SELECT GROUP_CONCAT(at.cod_detalle_ventaFK ORDER BY at.id ASC SEPARATOR ',')
                    FROM agenda_tratamientos at
                    WHERE at.id_agenda = a.id_agenda AND at.estado <> 'cancelado') AS tratamientos_ids
            FROM agenda a
            INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
            WHERE a.fecha BETWEEN '".$desde."' AND '".$hasta."'
              AND c.cod_localFk = '".(int)$codLocal."'
              ".$condicionConsultorio."
              AND UPPER(IFNULL(a.estado,'')) IN ('AGENDADO','CONFIRMADO','CONFIRMADOCONDEUDA','PRIMERACONSULTA')
            ORDER BY a.fecha ASC, a.hora_inicio ASC, a.id_agenda ASC";
    $result = $mysqli->query($sql);
    $citas = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ids = array();
            if ($row["tratamientos_ids"] != "") {
                foreach (explode(",", $row["tratamientos_ids"]) as $id) {
                    $id = (int)$id;
                    if ($id > 0) {
                        $ids[] = $id;
                    }
                }
            } elseif ((int)$row["cod_detalle_ventaFK"] > 0) {
                $ids[] = (int)$row["cod_detalle_ventaFK"];
            }
            if (count($ids) == 0) {
                continue;
            }
            $row["tratamientos_ids"] = $ids;
            $citas[] = $row;
        }
    }
    return $citas;
}

function resolverVariantesInformeInsumosAgenda($mysqli, $insumo, $contexto, &$historicoCache)
{
    $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
    $tieneVariantes = (int)(isset($insumo["tiene_variantes"]) ? $insumo["tiene_variantes"] : 0);
    $idVariante = (int)(isset($insumo["id_variante"]) ? $insumo["id_variante"] : 0);
    $nombreVariante = isset($insumo["nombre_variante"]) ? $insumo["nombre_variante"] : "";
    if ($tieneVariantes !== 1 || $idVariante > 0) {
        return array(crearItemInformeInsumosAgenda($insumo, $idVariante, $nombreVariante, $cantidad, false));
    }

    $idInsumo = (int)$insumo["id_insumo"];
    if (!isset($historicoCache[$idInsumo])) {
        $historicoCache[$idInsumo] = obtenerDistribucionHistoricaVariantesAgenda($mysqli, $idInsumo, $contexto);
    }
    $distribucion = $historicoCache[$idInsumo];
    $items = array();
    foreach ($distribucion as $dist) {
        $cantidadEstimada = $cantidad * normalizarNumeroAgenda($dist["ratio"]);
        if ($cantidadEstimada <= 0) {
            continue;
        }
        $items[] = crearItemInformeInsumosAgenda($insumo, (int)$dist["id_variante"], $dist["nombre_variante"], $cantidadEstimada, true);
    }
    if (count($items) == 0) {
        $items[] = crearItemInformeInsumosAgenda($insumo, 0, "", $cantidad, true);
    }
    return $items;
}

function obtenerDistribucionHistoricaVariantesAgenda($mysqli, $idInsumo, $contexto)
{
    $condicionConsultorio = ((int)$contexto["id_consultorio"] > 0) ? " AND m.consultorio_id = '".(int)$contexto["id_consultorio"]."'" : "";
    $sql = "SELECT m.id_variante, v.nombre_variante, SUM(m.cantidad) AS total
            FROM movimientos_insumos m
            INNER JOIN insumo_variantes v ON v.id_variante = m.id_variante
            WHERE m.insumo_id = '".(int)$idInsumo."'
              AND m.id_variante > 0
              AND m.sucursal_id = '".(int)$contexto["cod_local"]."'
              ".$condicionConsultorio."
              AND LOWER(m.tipo) = 'salida'
              AND m.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY m.id_variante, v.nombre_variante
            ORDER BY total DESC";
    $result = $mysqli->query($sql);
    $filas = array();
    $total = 0;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row["total"] = normalizarNumeroAgenda($row["total"]);
            $total += $row["total"];
            $filas[] = $row;
        }
    }
    if ($total > 0) {
        foreach ($filas as $i => $fila) {
            $filas[$i]["ratio"] = $fila["total"] / $total;
            $filas[$i]["nombre_variante"] = normalizarTextoUtf8($fila["nombre_variante"]);
        }
        return $filas;
    }

    $variantes = obtenerVariantesAgendaInsumo($mysqli, $idInsumo);
    $cantidadVariantes = count($variantes);
    if ($cantidadVariantes == 0) {
        return array();
    }
    $dist = array();
    foreach ($variantes as $variante) {
        $dist[] = array(
            "id_variante" => (int)$variante["id_variante"],
            "nombre_variante" => $variante["nombre_variante"],
            "ratio" => 1 / $cantidadVariantes
        );
    }
    return $dist;
}

function crearItemInformeInsumosAgenda($insumo, $idVariante, $nombreVariante, $cantidad, $estimado)
{
    $nombre = normalizarTextoUtf8($insumo["nombre"]);
    $nombreVariante = normalizarTextoUtf8($nombreVariante);
    $unidad = normalizarTextoUtf8($insumo["unidad_medida"]);
    $etiqueta = $nombre.($nombreVariante != "" ? " - ".$nombreVariante : "");
    return array(
        "clave" => (int)$insumo["id_insumo"].":".(int)$idVariante."|".$unidad,
        "id_insumo" => (int)$insumo["id_insumo"],
        "id_variante" => (int)$idVariante,
        "nombre" => $etiqueta,
        "unidad" => $unidad,
        "cantidad" => normalizarNumeroAgenda($cantidad),
        "estimado" => $estimado
    );
}

function agregarItemInformeInsumosAgenda(&$lista, $item)
{
    $clave = $item["clave"];
    if (!isset($lista[$clave])) {
        $lista[$clave] = $item;
        return;
    }
    $lista[$clave]["cantidad"] += normalizarNumeroAgenda($item["cantidad"]);
    $lista[$clave]["estimado"] = $lista[$clave]["estimado"] || $item["estimado"];
}

function ordenarItemsInformeInsumosAgenda(&$lista)
{
    uasort($lista, function ($a, $b) {
        return strcasecmp($a["nombre"], $b["nombre"]);
    });
}

function obtenerUltimaFechaFuturaInformeInsumosAgenda($mysqli, $hoy, $codLocal, $idConsultorio)
{
    $condicionConsultorio = $idConsultorio > 0 ? " AND a.id_consultorio = '".(int)$idConsultorio."'" : "";
    $sql = "SELECT MAX(a.fecha) AS ultima
            FROM agenda a
            INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
            WHERE a.fecha >= '".$hoy."'
              AND c.cod_localFk = '".(int)$codLocal."'
              ".$condicionConsultorio."
              AND UPPER(IFNULL(a.estado,'')) IN ('AGENDADO','CONFIRMADO','CONFIRMADOCONDEUDA','PRIMERACONSULTA')
              AND (
                EXISTS (SELECT 1 FROM agenda_tratamientos at WHERE at.id_agenda = a.id_agenda AND at.estado <> 'cancelado')
                OR IFNULL(a.cod_detalle_ventaFK,0) > 0
              )";
    $result = $mysqli->query($sql);
    if ($result && ($row = $result->fetch_assoc()) && $row["ultima"] != "") {
        return $row["ultima"];
    }
    return "";
}

function obtenerStockActualInformeInsumosAgenda($mysqli, $idInsumo, $idVariante, $codLocal, $idConsultorio)
{
    $condicionConsultorio = $idConsultorio > 0 ? " AND id_consultorio = '".(int)$idConsultorio."'" : "";
    $sql = "SELECT SUM(cantidad) AS stock
            FROM insumo_stock_consultorio
            WHERE id_insumo = '".(int)$idInsumo."'
              AND id_variante = '".(int)$idVariante."'
              AND cod_local = '".(int)$codLocal."'
              ".$condicionConsultorio;
    $result = $mysqli->query($sql);
    if ($result && ($row = $result->fetch_assoc())) {
        return normalizarNumeroAgenda($row["stock"]);
    }
    return 0;
}

function obtenerStockMinimoInformeInsumosAgenda($mysqli, $idInsumo, $idVariante)
{
    if ((int)$idVariante > 0) {
        $result = $mysqli->query("SELECT stock_minimo FROM insumo_variantes WHERE id_variante='".(int)$idVariante."' LIMIT 1");
        if ($result && ($row = $result->fetch_assoc())) {
            return normalizarNumeroAgenda($row["stock_minimo"]);
        }
    }
    if (!tablaAgendaExiste($mysqli, "insumosconsl")) {
        return 0;
    }
    $result = $mysqli->query("SELECT stock_minimo FROM insumosconsl WHERE id_insumo='".(int)$idInsumo."' LIMIT 1");
    if ($result && ($row = $result->fetch_assoc())) {
        return normalizarNumeroAgenda($row["stock_minimo"]);
    }
    return 0;
}

function obtenerMetaInformeInsumosAgenda($mysqli, $codLocal, $idConsultorio, $useru, $tipoAlcance, $periodo, $rango)
{
    $sucursal = "";
    $resultLocal = $mysqli->query("SELECT Nombre FROM local WHERE cod_local='".(int)$codLocal."' LIMIT 1");
    if ($resultLocal && ($row = $resultLocal->fetch_assoc())) {
        $sucursal = normalizarTextoUtf8($row["Nombre"]);
    }
    $consultorio = "";
    if ($idConsultorio > 0) {
        $resultConsultorio = $mysqli->query("SELECT nombre FROM consultorios WHERE id_consultorio='".(int)$idConsultorio."' LIMIT 1");
        if ($resultConsultorio && ($row = $resultConsultorio->fetch_assoc())) {
            $consultorio = normalizarTextoUtf8($row["nombre"]);
        }
    }
    $usuario = "";
    $resultUsuario = $mysqli->query("SELECT p.nombre_persona FROM usuario u INNER JOIN persona p ON p.cod_persona = u.cod_usuario WHERE u.cod_usuario='".(int)$useru."' LIMIT 1");
    if ($resultUsuario && ($row = $resultUsuario->fetch_assoc())) {
        $usuario = normalizarTextoUtf8($row["nombre_persona"]);
    }
    return array(
        "clinica" => "Clinident",
        "sucursal" => $sucursal,
        "consultorio" => $consultorio,
        "tipo_alcance" => $tipoAlcance,
        "periodo" => $periodo === "semana" ? "Semana" : "Dia",
        "rango" => $rango,
        "generado" => date("d/m/Y H:i"),
        "usuario" => $usuario
    );
}

function generarHtmlInformeInsumosAgenda($meta, $resumen, $detalleDias, $proyeccion, $compras, $ultimaFecha, $hayEstimadas)
{
    $periodoTexto = $meta["rango"]["desde"] == $meta["rango"]["hasta"]
        ? formatearFechaInformeInsumosAgenda($meta["rango"]["desde"])
        : formatearFechaInformeInsumosAgenda($meta["rango"]["desde"])." al ".formatearFechaInformeInsumosAgenda($meta["rango"]["hasta"]);

    $html = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Informe de insumos</title>";
    $html .= "<style>
        body{font-family:Arial,sans-serif;color:#172033;margin:0;background:#f6f7fb;}
        .page{width:210mm;min-height:297mm;margin:0 auto;background:#fff;padding:14mm;box-sizing:border-box;}
        .header{display:flex;align-items:center;gap:14px;border-bottom:2px solid #e5e7eb;padding-bottom:10px;margin-bottom:14px;}
        .header img{width:70px;max-height:54px;object-fit:contain;}
        .header h1{font-size:22px;margin:0;color:#111827;}
        .header p{margin:2px 0;font-size:12px;color:#4b5563;}
        .meta{display:grid;grid-template-columns:repeat(2,1fr);gap:5px 18px;margin:10px 0 14px;font-size:12px;color:#374151;}
        h2{font-size:15px;margin:18px 0 8px;color:#111827;border-bottom:1px solid #e5e7eb;padding-bottom:5px;}
        h3{font-size:13px;margin:13px 0 6px;color:#1f2937;}
        table{width:100%;border-collapse:collapse;margin-bottom:10px;font-size:11.5px;}
        th{background:#f3f4f6;color:#111827;text-align:left;border:1px solid #d1d5db;padding:6px;}
        td{border:1px solid #e5e7eb;padding:6px;vertical-align:top;}
        .num{text-align:right;white-space:nowrap;}
        .muted{color:#6b7280;font-size:11px;}
        .empty{padding:10px;border:1px solid #e5e7eb;background:#fafafa;color:#6b7280;font-size:12px;}
        .note{font-size:11px;color:#6b7280;margin:6px 0 12px;}
        .actions{position:sticky;top:0;background:#111827;color:#fff;padding:8px 14px;text-align:right;}
        .actions button{border:0;border-radius:6px;padding:7px 12px;cursor:pointer;}
        @media print{body{background:#fff}.actions{display:none}.page{width:auto;margin:0;padding:10mm;}}
    </style></head><body>";
    $html .= "<div class='actions'><button onclick='window.print()'>Generar PDF</button></div><div class='page'>";
    $html .= "<div class='header'><img src='/GoodVentaAsisCap/iconos/Logo.jpg' alt='Logo'><div><h1>Informe de insumos</h1><p>".escaparHtmlAgenda($meta["clinica"])."</p><p>Periodo: ".escaparHtmlAgenda($periodoTexto)."</p></div></div>";
    $html .= "<div class='meta'><div><b>Sucursal:</b> ".escaparHtmlAgenda($meta["sucursal"])."</div>";
    $html .= "<div><b>Consultorio:</b> ".escaparHtmlAgenda($meta["consultorio"] != "" ? $meta["consultorio"] : "Sucursal completa")."</div>";
    $html .= "<div><b>Generado:</b> ".escaparHtmlAgenda($meta["generado"])."</div>";
    $html .= "<div><b>Usuario:</b> ".escaparHtmlAgenda($meta["usuario"])."</div></div>";

    $html .= "<h2>Resumen de insumos del periodo seleccionado</h2>";
    $html .= tablaItemsInformeInsumosAgenda($resumen, array("Insumo / Variante", "Cantidad requerida", "Unidad"), "No se encontraron citas agendadas o confirmadas con tratamientos cargados para el periodo seleccionado.");

    $html .= "<h2>Detalle por dia</h2>";
    if (count($detalleDias) == 0) {
        $html .= "<div class='empty'>No se encontraron citas agendadas o confirmadas con tratamientos cargados para el periodo seleccionado.</div>";
    } else {
        foreach ($detalleDias as $fecha => $items) {
            $html .= "<h3>".escaparHtmlAgenda(nombreDiaInformeInsumosAgenda($fecha)." ".formatearFechaInformeInsumosAgenda($fecha))."</h3>";
            $html .= tablaItemsInformeInsumosAgenda($items, array("Insumo / Variante", "Cantidad", "Unidad"), "Sin insumos previstos para este dia.");
        }
    }

    $html .= "<h2>Proyeccion de alcance del stock</h2>";
    if ($ultimaFecha == "" || count($proyeccion) == 0) {
        $html .= "<div class='empty'>No hay insumos para proyectar segun la agenda futura cargada.</div>";
    } else {
        $html .= "<table><thead><tr><th>Insumo / Variante</th><th class='num'>Stock actual</th><th class='num'>Consumo futuro proyectado</th><th>Alcanza hasta</th><th>Ultima fecha proyectada</th></tr></thead><tbody>";
        foreach ($proyeccion as $item) {
            $html .= "<tr><td>".escaparHtmlAgenda($item["nombre"]).($item["estimado"] ? " <span class='muted'>(estimado)</span>" : "")."</td><td class='num'>".formatearCantidadInformeInsumosAgenda($item["stock_actual"])."</td><td class='num'>".formatearCantidadInformeInsumosAgenda($item["consumo_futuro"])."</td><td>".escaparHtmlAgenda($item["alcanza_hasta"])."</td><td>".escaparHtmlAgenda(formatearFechaInformeInsumosAgenda($item["ultima_fecha"]))."</td></tr>";
        }
        $html .= "</tbody></table>";
    }

    $html .= "<h2>Lista de compras sugerida</h2>";
    if (count($compras) == 0) {
        $html .= "<div class='empty'>No hay compras sugeridas segun la agenda futura y el stock minimo actual.</div>";
    } else {
        $html .= "<table><thead><tr><th>Insumo / Variante</th><th class='num'>Stock actual</th><th class='num'>Consumo futuro</th><th class='num'>Stock minimo</th><th class='num'>Comprar</th><th>Motivo</th></tr></thead><tbody>";
        foreach ($compras as $item) {
            $html .= "<tr><td>".escaparHtmlAgenda($item["nombre"])."</td><td class='num'>".formatearCantidadInformeInsumosAgenda($item["stock_actual"])."</td><td class='num'>".formatearCantidadInformeInsumosAgenda($item["consumo_futuro"])."</td><td class='num'>".formatearCantidadInformeInsumosAgenda($item["stock_minimo"])."</td><td class='num'><b>".formatearCantidadInformeInsumosAgenda($item["comprar"])."</b></td><td>".escaparHtmlAgenda($item["motivo"])."</td></tr>";
        }
        $html .= "</tbody></table>";
    }

    if ($hayEstimadas) {
        $html .= "<p class='note'>*Cantidad estimada segun consumo historico de los ultimos 30 dias porque algunas citas aun no tienen variante seleccionada.</p>";
    }
    $html .= "</div></body></html>";
    return $html;
}

function tablaItemsInformeInsumosAgenda($items, $encabezados, $mensajeVacio)
{
    if (count($items) == 0) {
        return "<div class='empty'>".escaparHtmlAgenda($mensajeVacio)."</div>";
    }
    $html = "<table><thead><tr><th>".escaparHtmlAgenda($encabezados[0])."</th><th class='num'>".escaparHtmlAgenda($encabezados[1])."</th><th>".escaparHtmlAgenda($encabezados[2])."</th></tr></thead><tbody>";
    foreach ($items as $item) {
        $html .= "<tr><td>".escaparHtmlAgenda($item["nombre"]).($item["estimado"] ? " <span class='muted'>(estimado)</span>" : "")."</td><td class='num'>".formatearCantidadInformeInsumosAgenda($item["cantidad"])."</td><td>".escaparHtmlAgenda($item["unidad"])."</td></tr>";
    }
    $html .= "</tbody></table>";
    return $html;
}

function formatearCantidadInformeInsumosAgenda($valor)
{
    $numero = normalizarNumeroAgenda($valor);
    if (abs($numero - round($numero)) < 0.0001) {
        return (string)round($numero);
    }
    return rtrim(rtrim(number_format($numero, 3, '.', ''), '0'), '.');
}

function formatearFechaInformeInsumosAgenda($fecha)
{
    $timestamp = strtotime($fecha);
    return $timestamp ? date("d/m/Y", $timestamp) : $fecha;
}

function nombreDiaInformeInsumosAgenda($fecha)
{
    $dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");
    $timestamp = strtotime($fecha);
    return $timestamp ? $dias[(int)date("w", $timestamp)] : "";
}

function obtenerDiaSemanaAgenda($fecha){
    $dias = array(
        0 => 'domingo',
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado'
    );

    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return '';
    }

    return $dias[(int)date('w', $timestamp)];
}

function normalizarDiaSemanaAgenda($dia){
    $dia = strtolower(trim((string)$dia));
    $dia = strtr($dia, array(
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u'
    ));

    $diasValidos = array('domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado');
    if (in_array($dia, $diasValidos, true)) {
        return $dia;
    }

    return '';
}

function escaparHtmlAgenda($valor){
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function generarHtmlDoctoresDisponiblesCita($doctores){
    if (count($doctores) == 0) {
        return "<div class='doctor-disponible-mensaje'>Sin doctores disponibles</div>";
    }

    $html = "";
    foreach ($doctores as $doctor) {
        $html .= "<button type='button' class='doctor-disponible-item' ";
        $html .= "data-doctor='".escaparHtmlAgenda($doctor["cod_usuario"])."' ";
        $html .= "onclick='seleccionarDoctorDisponibleNuevaCita(this)'>";
        $html .= "<span class='doctor-disponible-nombre'>".escaparHtmlAgenda($doctor["nombre"])."</span>";
        $html .= "<span class='doctor-disponible-horario'>".escaparHtmlAgenda($doctor["horarios"])."</span>";
        $html .= "</button>";
    }

    return $html;
}

function buscarDoctoresDisponiblesCita($mysqli){
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $cod_local = isset($_POST['cod_local']) ? limpiar($mysqli, $_POST['cod_local']) : '';
    $dia_semana = obtenerDiaSemanaAgenda($fecha);

    if ($dia_semana == '') {
        echo json_encode(array("1" => "Error", "mensaje" => "Fecha invalida"));
        exit;
    }

    $condicionLocal = "";
    $condicionConsultorioLocal = "";
    $condicionHorarioLocal = " AND hu.cod_localFK IS NOT NULL";
    if (function_exists('asegurarEstructuraHorarioUsuarioEsperado')) {
        asegurarEstructuraHorarioUsuarioEsperado($mysqli);
    }
    $condicionHorarioVigente = " AND IFNULL(hu.estado_horario,'activo')='activo'
        AND (hu.vigente_desde IS NULL OR hu.vigente_desde <= '".$fecha."')
        AND (hu.vigente_hasta IS NULL OR hu.vigente_hasta >= '".$fecha."')";
    if ($cod_local != "") {
        $condicionLocal = " AND c.cod_localFk = '".$cod_local."'";
        $condicionHorarioLocal = " AND hu.cod_localFK = '".$cod_local."'";
    }

    $sql = "SELECT
            u.cod_usuario,
            p.nombre_persona,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    TIME_FORMAT(hu.hora_entrada, '%H:%i'),
                    IF(hu.hora_salida IS NULL, '', CONCAT(' - ', TIME_FORMAT(hu.hora_salida, '%H:%i')))
                )
                ORDER BY hu.hora_entrada ASC
                SEPARATOR ' | '
            ) AS horarios
        FROM usuario u
        INNER JOIN persona p ON p.cod_persona = u.cod_usuario
        INNER JOIN horario_usuario hu ON hu.cod_usuarioFK = u.cod_usuario
        LEFT JOIN consultorios c ON c.cod_doctorFK = u.cod_usuario AND c.estado = 'Activo' ".$condicionLocal."
        WHERE u.tipo = 'DOCTOR'
        AND u.estado = 'Activo'
        ".$condicionHorarioLocal."
        AND hu.dia_semana = '".$dia_semana."'
        ".$condicionHorarioVigente."
        ".$condicionConsultorioLocal."
        GROUP BY u.cod_usuario, p.nombre_persona
        ORDER BY p.nombre_persona ASC";

    $result = $mysqli->query($sql);

    if (!$result) {
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se pudieron obtener los doctores disponibles",
            "sql" => $sql,
            "mysql" => $mysqli->error
        ));
        exit;
    }

    $doctores = array();
    while ($row = $result->fetch_assoc()) {
        $doctores[] = array(
            "cod_usuario" => (int)$row["cod_usuario"],
            "nombre" => normalizarTextoUtf8($row["nombre_persona"]),
            "horarios" => normalizarTextoUtf8($row["horarios"]),
        );
    }

    echo json_encode(array(
        "1" => "exito",
        "dia" => $dia_semana,
        "html" => generarHtmlDoctoresDisponiblesCita($doctores)
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

function listarDiasFeriados($mysqli){
    $fecha_desde = isset($_POST['fecha_desde']) ? limpiar($mysqli, $_POST['fecha_desde']) : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? limpiar($mysqli, $_POST['fecha_hasta']) : '';
    $cod_local = isset($_POST['cod_local']) ? limpiar($mysqli, $_POST['cod_local']) : '';
    $cod_consultorio = isset($_POST['cod_consultorio']) ? limpiar($mysqli, $_POST['cod_consultorio']) : '';

    $registros= obtenerDiasFeriados($mysqli, array(
        "fecha_desde" => $fecha_desde,
        "fecha_hasta" => $fecha_hasta,
        "cod_local" => $cod_local,
        "cod_consultorio" => $cod_consultorio
    ));
    
    $html = "";
    foreach ($registros as $row) {
        $html .= "<div class='feriado-item'>";
        $html .= "<div class='feriado-item-info'>";
        $html .= "<b>".escaparHtmlAgenda($row["fecha_formateada"])."</b>";
        $html .= "<span>".escaparHtmlAgenda(normalizarTextoUtf8($row["descripcion"]))."</span>";
        $html .= "<small>".escaparHtmlAgenda(normalizarTextoUtf8($row["local"]))."</small>";
        $html .= "</div>";
        $html .= "<button type='button' class='btn-filtro' style='background:#c94d4d;color:#fff;' onclick='eliminarDiaFeriadoAgenda(".(int)$row["id"].")'>Quitar</button>";
        $html .= "</div>";
    }

    if ($html == "") {
        $html = "<div class='feriado-item'><div class='feriado-item-info'><span>Sin feriados registrados</span></div></div>";
    }

    echo json_encode(array("1" => "exito", "html" => $html), JSON_UNESCAPED_UNICODE);
    exit;
}

function guardarDiaFeriado($mysqli, $useru){
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $descripcion = isset($_POST['descripcion']) ? limpiar($mysqli, $_POST['descripcion']) : '';
    $cod_local = isset($_POST['cod_local']) ? limpiar($mysqli, $_POST['cod_local']) : '';

    if ($fecha == '') {
        echo json_encode(array("1" => "Error", "mensaje" => "Debe cargar la fecha"));
        exit;
    }

    $cod_local_sql = $cod_local != '' ? "'".$cod_local."'" : "NULL";

    $sql = "INSERT INTO dias_feriados
            (fecha, descripcion, cod_localFK, estado, cod_usuarioFK_create, fecha_create)
            VALUES ('".$fecha."', '".$descripcion."', ".$cod_local_sql.", 'activo', '".$useru."', NOW())";

    if (!$mysqli->query($sql)) {
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se pudo guardar el feriado",
            "mysql" => $mysqli->error,
            "sql" => $sql
        ));
        exit;
    }

    echo json_encode(array("1" => "exito"));
    exit;
}

function eliminarDiaFeriado($mysqli, $useru){
    $id = isset($_POST['id']) ? limpiar($mysqli, $_POST['id']) : '';

    if ($id == '') {
        echo json_encode(array("1" => "Error", "mensaje" => "No se encontro el feriado"));
        exit;
    }

    $sql = "UPDATE dias_feriados SET estado='Inactivo', cod_usuarioFK_edit=?, fecha_edit=NOW() WHERE id=?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se pudo preparar la eliminacion."));
        exit;
    }
    $stmt->bind_param('ss', $useru, $id);
    if (!$stmt->execute()) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se pudo eliminar el feriado."));
        exit;
    }
    echo json_encode(array("1" => "exito"));
    exit;
}

function listarAsignacionesConsultorios($mysqli){
    asegurarEstructuraConsultorioDoctorAsignacion($mysqli);

    $dia = isset($_POST['dia']) ? limpiar($mysqli, $_POST['dia']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $cod_local = isset($_POST['cod_local']) ? limpiar($mysqli, $_POST['cod_local']) : '';

    $dia_semana = normalizarDiaSemanaAgenda($dia);
    if ($dia_semana == '') {
        if ($fecha == '') {
            $fecha = date('Y-m-d');
        }
        $dia_semana = obtenerDiaSemanaAgenda($fecha);
    }

    if ($dia_semana == '') {
        echo json_encode(array("1" => "Error", "mensaje" => "Dia invalido"));
        exit;
    }

    $condicionLocalConsultorio = "";
    $condicionLocalHorario = "";
    $condicionLocalAsignacion = "";
    if ($cod_local != "") {
        $condicionLocalConsultorio = " AND c.cod_localFk = '".$cod_local."' ";
        $condicionLocalHorario = " AND hu.cod_localFK = '".$cod_local."' ";
        $condicionLocalAsignacion = " AND c.cod_localFk = '".$cod_local."' ";
    }

    $consultoriosOptions = "<option value=''>Seleccionar consultorio</option>";
    $sqlConsultorios = "SELECT c.id_consultorio, c.nombre, IFNULL(l.Nombre,'') AS local
        FROM consultorios c
        LEFT JOIN local l ON l.cod_local = c.cod_localFk
        WHERE c.estado = 'Activo' ".$condicionLocalConsultorio."
        ORDER BY c.cod_localFk ASC, c.nombre ASC";
    $resultConsultorios = $mysqli->query($sqlConsultorios);
    if ($resultConsultorios) {
        while ($row = $resultConsultorios->fetch_assoc()) {
            $nombre = normalizarTextoUtf8($row["nombre"]);
            $local = normalizarTextoUtf8($row["local"]);
            $consultoriosOptions .= "<option value='".(int)$row["id_consultorio"]."'>".escaparHtmlAgenda($nombre." - ".$local)."</option>";
        }
    }

    $horariosOptions = "<option value=''>Seleccionar doctor y horario</option>";
    $sqlHorarios = "SELECT
            hu.id,
            hu.cod_usuarioFK,
            p.nombre_persona,
            IFNULL(l.Nombre,'') AS local,
            TIME_FORMAT(hu.hora_entrada, '%H:%i') AS hora_entrada,
            TIME_FORMAT(hu.hora_salida, '%H:%i') AS hora_salida
        FROM horario_usuario hu
        INNER JOIN usuario u ON u.cod_usuario = hu.cod_usuarioFK
        INNER JOIN persona p ON p.cod_persona = u.cod_usuario
        LEFT JOIN local l ON l.cod_local = hu.cod_localFK
        WHERE u.tipo = 'DOCTOR'
        AND u.estado = 'Activo'
        AND IFNULL(hu.estado_horario,'activo') = 'activo'
        AND hu.dia_semana = '".$dia_semana."'
        AND hu.cod_localFK IS NOT NULL
        AND hu.hora_salida IS NOT NULL
        AND hu.hora_salida > hu.hora_entrada
        ".$condicionLocalHorario."
        ORDER BY l.Nombre ASC, p.nombre_persona ASC, hu.hora_entrada ASC";
        //echo json_encode($sqlHorarios);exit;
    $resultHorarios = $mysqli->query($sqlHorarios);
    $totalHorarios = 0;
    if ($resultHorarios) {
        while ($row = $resultHorarios->fetch_assoc()) {
            $totalHorarios++;
            $doctor = normalizarTextoUtf8($row["nombre_persona"]);
            $local = normalizarTextoUtf8($row["local"]);
            $label = $doctor." - ".$row["hora_entrada"]." a ".$row["hora_salida"]." - ".$local;
            $horariosOptions .= "<option value='".(int)$row["id"]."'>".escaparHtmlAgenda($label)."</option>";
        }
    }
    if ($totalHorarios == 0) {
        $horariosOptions .= "<option value='' disabled>Sin horarios activos con hora de salida para este dia/local</option>";
    }

    $html = "";
    $sqlAsignaciones = "SELECT
            a.id_asignacion,
            c.nombre AS consultorio,
            IFNULL(lc.Nombre,'') AS local_consultorio,
            p.nombre_persona AS doctor,
            TIME_FORMAT(hu.hora_entrada, '%H:%i') AS hora_entrada,
            TIME_FORMAT(hu.hora_salida, '%H:%i') AS hora_salida
        FROM consultorio_doctor_asignacion a
        INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
        INNER JOIN horario_usuario hu ON hu.id = a.id_horario_usuario
        INNER JOIN usuario u ON u.cod_usuario = hu.cod_usuarioFK
        INNER JOIN persona p ON p.cod_persona = u.cod_usuario
        LEFT JOIN local lc ON lc.cod_local = c.cod_localFk
        WHERE a.estado = 'activo'
        AND c.estado = 'Activo'
        AND u.tipo = 'DOCTOR'
        AND u.estado = 'Activo'
        AND IFNULL(hu.estado_horario,'activo') = 'activo'
        AND hu.dia_semana = '".$dia_semana."'
        AND hu.hora_salida IS NOT NULL
        ".$condicionLocalAsignacion."
        ORDER BY c.cod_localFk ASC, c.nombre ASC, hu.hora_entrada ASC";
    $resultAsignaciones = $mysqli->query($sqlAsignaciones);
    if ($resultAsignaciones) {
        while ($row = $resultAsignaciones->fetch_assoc()) {
            $html .= "<div class='asignacion-consultorio-item'>";
            $html .= "<div class='asignacion-consultorio-info'>";
            $html .= "<b>".escaparHtmlAgenda(normalizarTextoUtf8($row["consultorio"]))."</b>";
            $html .= "<span>".escaparHtmlAgenda(normalizarTextoUtf8($row["doctor"]))."</span>";
            $html .= "<small>".escaparHtmlAgenda($row["hora_entrada"]." - ".$row["hora_salida"]." | ".normalizarTextoUtf8($row["local_consultorio"]))."</small>";
            $html .= "</div>";
            $html .= "<button type='button' class='btn-filtro btn-asignacion-quitar' onclick='eliminarAsignacionConsultorioAgenda(".(int)$row["id_asignacion"].")'>Quitar</button>";
            $html .= "</div>";
        }
    }

    if ($html == "") {
        $html = "<div class='asignacion-consultorio-vacia'>Sin asignaciones para ".$dia_semana.".</div>";
    }

    echo json_encode(array(
        "1" => "exito",
        "fecha" => $fecha,
        "dia" => $dia_semana,
        "consultorios_options" => $consultoriosOptions,
        "horarios_options" => $horariosOptions,
        "html" => $html
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

function guardarAsignacionConsultorio($mysqli, $useru){
    asegurarEstructuraConsultorioDoctorAsignacion($mysqli);

    $id_consultorio = isset($_POST['id_consultorio']) ? (int)$_POST['id_consultorio'] : 0;
    $id_horario_usuario = isset($_POST['id_horario_usuario']) ? (int)$_POST['id_horario_usuario'] : 0;

    if ($id_consultorio <= 0 || $id_horario_usuario <= 0) {
        echo json_encode(array("1" => "Error", "mensaje" => "Debe seleccionar consultorio y horario."));
        exit;
    }

    $consultorio = obtenerConsultorioAsignacion($mysqli, $id_consultorio);
    if (!$consultorio) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se encontro el consultorio activo."));
        exit;
    }

    $horario = obtenerHorarioAsignacionConsultorio($mysqli, $id_horario_usuario);
    $mensajeHorario = validarHorarioAsignacionConsultorio($horario);
    if ($mensajeHorario != "") {
        echo json_encode(array("1" => "Error", "mensaje" => $mensajeHorario));
        exit;
    }

    if ((int)$consultorio["cod_localFk"] != (int)$horario["cod_localFK"]) {
        echo json_encode(array("1" => "Error", "mensaje" => "El local del horario no coincide con el local del consultorio."));
        exit;
    }

    $conflicto = buscarConflictoAsignacionConsultorio($mysqli, $consultorio, $horario, 0);
    if ($conflicto != "") {
        echo json_encode(array("1" => "Error", "mensaje" => $conflicto), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $usuario = (int)$useru;
    $stmt = $mysqli->prepare("INSERT INTO consultorio_doctor_asignacion
        (id_consultorio, id_horario_usuario, estado, fecha_create, cod_usuarioFK_create)
        VALUES (?, ?, 'activo', NOW(), ?)");
    if (!$stmt) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se pudo preparar la asignacion."));
        exit;
    }
    $stmt->bind_param("iii", $id_consultorio, $id_horario_usuario, $usuario);
    if (!$stmt->execute()) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se pudo guardar la asignacion.", "mysql" => $stmt->error));
        exit;
    }
    $stmt->close();

    echo json_encode(array("1" => "exito", "mensaje" => "Asignacion guardada correctamente."));
    exit;
}

function eliminarAsignacionConsultorio($mysqli, $useru){
    asegurarEstructuraConsultorioDoctorAsignacion($mysqli);

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se encontro la asignacion."));
        exit;
    }

    $usuario = (int)$useru;
    $stmt = $mysqli->prepare("UPDATE consultorio_doctor_asignacion
        SET estado='inactivo', fecha_edit=NOW(), cod_usuarioFK_edit=?
        WHERE id_asignacion=? LIMIT 1");
    if (!$stmt) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se pudo preparar la eliminacion."));
        exit;
    }
    $stmt->bind_param("ii", $usuario, $id);
    if (!$stmt->execute()) {
        echo json_encode(array("1" => "Error", "mensaje" => "No se pudo quitar la asignacion.", "mysql" => $stmt->error));
        exit;
    }
    $stmt->close();

    echo json_encode(array("1" => "exito"));
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

            $html .= "<tr id='tbSeleccRegistro' data-agenda-id='".(int)$row["id_agenda"]."' data-fecha='".$row["fecha"]."' data-hora-inicio='".$row["hora_inicio"]."' data-hora-fin='".$row["hora_fin"]."' data-estado='".htmlspecialchars(normalizarTextoUtf8($row["estado"]), ENT_QUOTES, 'UTF-8')."' onclick='console.info(\"".$row["fecha"]."\");document.getElementById(\"inptFechaAgenda\").value=\"".$row["fecha"]."\" ;aplicarFiltrosAgenda();vercerrarModalFiltrosAgenda(false);' style='text-align: center;'>";
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
    asegurarEstructuraAgendaInsumos($mysqli);
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $consultorio = isset($_POST['consultorio']) ? limpiar($mysqli, $_POST['consultorio']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $inicio = isset($_POST['inicio']) ? limpiar($mysqli, $_POST['inicio']) : '';
    $fin = isset($_POST['fin']) ? limpiar($mysqli, $_POST['fin']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : 'AGENDADO';
    $motivo = isset($_POST['motivo']) ? limpiar($mysqli, $_POST['motivo']) : '';
    $codVenta = isset($_POST['cod_venta']) ? (int)$_POST['cod_venta'] : 0;
    $detalles = isset($_POST['detalles']) ? $_POST['detalles'] : array();
    if (!is_array($detalles)) {
        $detalles = $detalles != '' ? explode(",", $detalles) : array();
    }
    $idsDetalles = array();
    foreach ($detalles as $detalle) {
        $detalle = (int)$detalle;
        if ($detalle > 0) {
            $idsDetalles[] = $detalle;
        }
    }
    if (count($idsDetalles) > 0) {
        $resultVentaPrimerDetalle = $mysqli->query("SELECT cod_ventaFK FROM detalle_venta WHERE cod_detalle = '".$idsDetalles[0]."' LIMIT 1");
        if ($resultVentaPrimerDetalle && ($rowVentaPrimerDetalle = $resultVentaPrimerDetalle->fetch_assoc())) {
            $codVenta = (int)$rowVentaPrimerDetalle["cod_ventaFK"];
        }
    }
    $planesSeleccionados = obtenerPlanIdsDetallesAgenda($mysqli, $idsDetalles);
    if (count($planesSeleccionados) > 1) {
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "La cita solo puede incluir tratamientos de un plan madre. Para otro plan madre, genere otra cita."
        ));
        exit;
    }
    $primerDetalle = count($idsDetalles) > 0 ? $idsDetalles[0] : "NULL";
    $codVentaSql = $codVenta > 0 ? "'".$codVenta."'" : "NULL";

    if($paciente == '' || $consultorio == '' || $fecha == '' || $inicio == '' || $fin == ''){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "Faltan datos obligatorios"
        ));
        exit;
    }

    $feriado = obtenerFeriadoAgenda($mysqli, $fecha, $consultorio);
    if ($feriado !== null) {
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se puede agendar en un día feriado: ".$feriado["descripcion"]." (".$feriado["fecha_formateada"].")"
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $idProfesional = obtenerProfesionalAsignadoConsultorioHorario($mysqli, $consultorio, $fecha, $inicio, $fin);
    $idProfesionalSql = $idProfesional > 0 ? "'".$idProfesional."'" : "NULL";

    $sql = "INSERT INTO agenda (
                id_paciente,
                id_profesional,
                id_consultorio,
                fecha,
                hora_inicio,
                hora_fin,
                estado,
                motivo,
                creado_por,
                creado_en,
                cod_ventaFK,
                cod_detalle_ventaFK
            ) VALUES (
                '".$paciente."',
                ".$idProfesionalSql.",
                '".$consultorio."',
                '".$fecha."',
                '".$inicio."',
                '".$fin."',
                '".$estado."',
                '".$motivo."',
                '".$useru."',
                NOW(),
                ".$codVentaSql.",
                ".$primerDetalle."
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
    foreach ($idsDetalles as $detalle) {
        $resultValida = $mysqli->query("SELECT cod_ventaFK FROM detalle_venta WHERE cod_detalle = '".$detalle."' LIMIT 1");
        if ($resultValida && ($rowValida = $resultValida->fetch_assoc())) {
            $ventaDetalle = (int)$rowValida["cod_ventaFK"];
            $mysqli->query("INSERT INTO agenda_tratamientos (id_agenda, cod_ventaFK, cod_detalle_ventaFK, creado_por)
                VALUES ('".$id_agenda."', '".$ventaDetalle."', '".$detalle."', '".(int)$useru."')
                ON DUPLICATE KEY UPDATE estado='previsto'");
        }
    }

    $insumos = obtenerInsumosPrevistosAgenda($mysqli, $id_agenda, $codVenta, $idsDetalles, (int)$consultorio, $estado);
    foreach ($insumos as $insumo) {
        $idInsumo = (int)$insumo["id_insumo"];
        $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
        $unidad = limpiar($mysqli, $insumo["unidad_medida"]);
        $mysqli->query("INSERT INTO agenda_consumo_insumos (id_agenda, id_insumo, cantidad_prevista, unidad_medida)
            VALUES ('".$id_agenda."', '".$idInsumo."', '".$cantidad."', '".$unidad."')
            ON DUPLICATE KEY UPDATE cantidad_prevista=VALUES(cantidad_prevista), unidad_medida=VALUES(unidad_medida)");
    }

    if (strtoupper($estado) == "ATENDIDO") {
        $insumosDescontados = 0;
        try {
            $mysqli->begin_transaction();
            $insumosDescontados = descontarInsumosAgendaAtendida($mysqli, $id_agenda, $useru);
            $mysqli->commit();
        } catch (Exception $e) {
            $mysqli->rollback();
            $mysqli->query("DELETE FROM agenda_consumo_insumos WHERE id_agenda = '".$id_agenda."'");
            $mysqli->query("DELETE FROM agenda_tratamientos WHERE id_agenda = '".$id_agenda."'");
            $mysqli->query("DELETE FROM agenda WHERE id_agenda = '".$id_agenda."' LIMIT 1");
            echo json_encode(array("1" => "Error", "mensaje" => $e->getMessage()));
            exit;
        }
        if ($insumosDescontados > 0) {
            crearComentario($id_agenda, "@{0}: @{".$useru."} desconto automaticamente los insumos por cita atendida.");
        }
    }

    crearComentario($id_agenda, "@{0}: @{".$useru."} a creado la cita.");

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Cita guardada correctamente",
        "id_agenda" => $id_agenda
    ));
    exit;
}

function reprogramarCita($mysqli, $useru){
    asegurarEstructuraAgendaInsumos($mysqli);
    $id_agenda = isset($_POST['id_agenda']) ? limpiar($mysqli, $_POST['id_agenda']) : '';
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $hora_inicio = isset($_POST['hora_inicio']) ? limpiar($mysqli, $_POST['hora_inicio']) : '';
    $hora_fin = isset($_POST['hora_fin']) ? limpiar($mysqli, $_POST['hora_fin']) : '';

    if($id_agenda == '' || $fecha == '' || $hora_inicio == '' || $hora_fin == ''){
        echo json_encode(array(
            "1" => "Datos incompletos",
            "mensaje" => "Faltan datos para reprogramar la cita"
        ));
        exit;
    }

    if(strtotime($hora_fin) <= strtotime($hora_inicio)){
        echo json_encode(array(
            "1" => "Horario invalido",
            "mensaje" => "La hora fin debe ser mayor a la hora inicio"
        ));
        exit;
    }

    $agendaAnterior = obtenerAgendaAuditoria($mysqli, $id_agenda);
    if(empty($agendaAnterior)){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se encontro el agendamiento original"
        ));
        exit;
    }

    $estadoAnterior = strtoupper(trim((string)(isset($agendaAnterior["estado"]) ? $agendaAnterior["estado"] : "")));
    $estadosFinales = array("ATENDIDO", "CANCELADO", "AUSENTE", "AUSENCIA", "NO_ASISTIO", "NOASISTIO", "NO ASISTIO");
    if(in_array($estadoAnterior, $estadosFinales)){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "Esta cita ya no puede reprogramarse"
        ));
        exit;
    }

    $idPaciente = (int)$agendaAnterior["id_paciente"];
    $idConsultorio = (int)$agendaAnterior["id_consultorio"];
    $codVenta = (int)$agendaAnterior["cod_ventaFK"];
    $estadoNuevo = $estadoAnterior == "PRIMERACONSULTA" ? "PRIMERACONSULTA" : "AGENDADO";

    if($idPaciente <= 0 || $idConsultorio <= 0){
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "La cita original no tiene paciente o consultorio valido"
        ));
        exit;
    }

    $feriado = obtenerFeriadoAgenda($mysqli, $fecha, $idConsultorio);
    if ($feriado !== null) {
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se puede agendar en un dia feriado: ".$feriado["descripcion"]." (".$feriado["fecha_formateada"].")"
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $idProfesional = obtenerProfesionalAsignadoConsultorioHorario($mysqli, $idConsultorio, $fecha, $hora_inicio, $hora_fin);
    $idProfesionalSql = $idProfesional > 0 ? "'".$idProfesional."'" : "NULL";
    $motivo = limpiar($mysqli, isset($agendaAnterior["motivo"]) ? $agendaAnterior["motivo"] : "");

    $idsDetalles = array();
    $detallesUnicos = array();
    $resultDetalles = $mysqli->query("SELECT cod_detalle_ventaFK FROM agenda_tratamientos WHERE id_agenda = '".$id_agenda."' AND estado <> 'cancelado' ORDER BY id ASC");
    if($resultDetalles){
        while($rowDetalle = $resultDetalles->fetch_assoc()){
            $detalle = (int)$rowDetalle["cod_detalle_ventaFK"];
            if($detalle > 0 && !isset($detallesUnicos[$detalle])){
                $detallesUnicos[$detalle] = true;
                $idsDetalles[] = $detalle;
            }
        }
    }
    if(count($idsDetalles) == 0 && (int)$agendaAnterior["cod_detalle_ventaFK"] > 0){
        $idsDetalles[] = (int)$agendaAnterior["cod_detalle_ventaFK"];
    }
    if($codVenta <= 0 && count($idsDetalles) > 0){
        $resultVentaDetalle = $mysqli->query("SELECT cod_ventaFK FROM detalle_venta WHERE cod_detalle = '".$idsDetalles[0]."' LIMIT 1");
        if($resultVentaDetalle && ($rowVentaDetalle = $resultVentaDetalle->fetch_assoc())){
            $codVenta = (int)$rowVentaDetalle["cod_ventaFK"];
        }
    }
    $codVentaSql = $codVenta > 0 ? "'".$codVenta."'" : "NULL";
    $primerDetalleSql = count($idsDetalles) > 0 ? "'".$idsDetalles[0]."'" : "NULL";

    $idNuevaAgenda = 0;
    $mysqli->begin_transaction();
    try {
        $sqlInsert = "INSERT INTO agenda (
                id_paciente,
                id_profesional,
                id_consultorio,
                fecha,
                hora_inicio,
                hora_fin,
                estado,
                motivo,
                creado_por,
                creado_en,
                cod_ventaFK,
                cod_detalle_ventaFK
            ) VALUES (
                '".$idPaciente."',
                ".$idProfesionalSql.",
                '".$idConsultorio."',
                '".$fecha."',
                '".$hora_inicio."',
                '".$hora_fin."',
                '".$estadoNuevo."',
                '".$motivo."',
                '".$useru."',
                NOW(),
                ".$codVentaSql.",
                ".$primerDetalleSql."
            )";

        if(!$mysqli->query($sqlInsert)){
            throw new Exception($mysqli->error);
        }
        $idNuevaAgenda = (int)$mysqli->insert_id;

        foreach($idsDetalles as $detalle){
            $resultValida = $mysqli->query("SELECT cod_ventaFK FROM detalle_venta WHERE cod_detalle = '".$detalle."' LIMIT 1");
            if($resultValida && ($rowValida = $resultValida->fetch_assoc())){
                $ventaDetalle = (int)$rowValida["cod_ventaFK"];
                $mysqli->query("INSERT INTO agenda_tratamientos (id_agenda, cod_ventaFK, cod_detalle_ventaFK, creado_por)
                    VALUES ('".$idNuevaAgenda."', '".$ventaDetalle."', '".$detalle."', '".(int)$useru."')
                    ON DUPLICATE KEY UPDATE estado='previsto', cod_ventaFK=VALUES(cod_ventaFK)");
                if($mysqli->error){
                    throw new Exception($mysqli->error);
                }
            }
        }

        $seleccionesVariantes = obtenerVariantesSeleccionadasAgenda($mysqli, $id_agenda);
        $insumos = obtenerInsumosPrevistosAgenda($mysqli, $idNuevaAgenda, $codVenta, $idsDetalles, $idConsultorio, $estadoNuevo);
        foreach($insumos as $insumo){
            $idInsumo = (int)$insumo["id_insumo"];
            $idVariante = isset($seleccionesVariantes[$idInsumo]) ? (int)$seleccionesVariantes[$idInsumo] : (int)(isset($insumo["id_variante"]) ? $insumo["id_variante"] : 0);
            $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
            $unidad = limpiar($mysqli, $insumo["unidad_medida"]);
            if($idInsumo > 0 && $cantidad > 0){
                $mysqli->query("INSERT INTO agenda_consumo_insumos (id_agenda, id_insumo, id_variante, cantidad_prevista, unidad_medida)
                    VALUES ('".$idNuevaAgenda."', '".$idInsumo."', '".$idVariante."', '".$cantidad."', '".$unidad."')
                    ON DUPLICATE KEY UPDATE id_variante=VALUES(id_variante), cantidad_prevista=VALUES(cantidad_prevista), unidad_medida=VALUES(unidad_medida)");
                if($mysqli->error){
                    throw new Exception($mysqli->error);
                }
            }
        }

        $sqlUpdateOriginal = "UPDATE agenda SET
                estado = 'AUSENTE',
                creado_por = '".$useru."',
                creado_en = NOW()
            WHERE id_agenda = '".$id_agenda."'
            LIMIT 1";
        if(!$mysqli->query($sqlUpdateOriginal)){
            throw new Exception($mysqli->error);
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(array(
            "1" => "Error",
            "mensaje" => "No se pudo reprogramar la cita",
            "mysql" => $e->getMessage()
        ));
        exit;
    }

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "estado" => "AUSENTE"
    ));
    crearComentario($id_agenda, "@{0}: @{".$useru."} reprogramo la cita. Nueva cita #".$idNuevaAgenda." para ".$fecha." de ".$hora_inicio." a ".$hora_fin.".");
    crearComentario($idNuevaAgenda, "@{0}: @{".$useru."} creo esta cita por reprogramacion de la cita #".$id_agenda.".");

    echo json_encode(array(
        "1" => "exito",
        "mensaje" => "Cita reprogramada correctamente",
        "id_agenda_original" => $id_agenda,
        "id_agenda" => $idNuevaAgenda
    ));
    exit;
}

function obtenerFeriadoAgenda($mysqli, $fecha, $consultorio){
    $registros = obtenerDiasFeriados($mysqli, array(
        "fecha" => $fecha,
        "cod_consultorio" => $consultorio,
        "limite" => 1
    ));

    return count($registros) > 0 ? $registros[0] : null;
}

function obtenerDiasFeriados($mysqli, $filtros){
    $condicion = " WHERE df.estado = 'activo' ";
    $joinConsultorio = "";
    $limite = "";

    foreach ($filtros as $key => $value) {
        if ($value != '') {
            switch ($key) {
                case 'fecha':
                    $condicion .= " AND df.fecha = '".$value."' ";
                    break;
                case 'fecha_desde':
                    $condicion .= " AND df.fecha >= '".$value."' ";
                    break;
                case 'fecha_hasta':
                    $condicion .= " AND df.fecha <= '".$value."' ";
                    break;
                case 'cod_local':
                    $condicion .= " AND (df.cod_localFK = '".$value."' OR df.cod_localFK IS NULL) ";
                    break;
                case 'cod_consultorio':
                    $joinConsultorio = " LEFT JOIN consultorios c_filtro ON c_filtro.id_consultorio = '".$value."' ";
                    $condicion .= " AND (df.cod_localFK IS NULL OR df.cod_localFK = c_filtro.cod_localFk) ";
                    break;
                case 'limite':
                    $limite = " LIMIT ".(int)$value;
                    break;
                default:
                    $condicion .= " AND ".$key." = '".$value."' ";
            }
        }
    }
    
    $sql = "SELECT
                df.id,
                df.fecha,
                DATE_FORMAT(df.fecha, '%d/%m/%Y') AS fecha_formateada,
                IFNULL(df.descripcion, '') AS descripcion,
                IFNULL(df.cod_localFK, '') AS cod_localFK,
                IFNULL(l.Nombre, 'Todos') AS local
            FROM dias_feriados df
            LEFT JOIN local l ON l.cod_local = df.cod_localFK
            ".$joinConsultorio."
            ".$condicion."
            ORDER BY df.fecha ASC, df.id ASC
            ".$limite;

    $result = $mysqli->query($sql);
    
    $registros= array();
    if (!$result) {
        return $registros;
    }

    while ($row = $result->fetch_assoc()) {
        $row["descripcion"] = normalizarTextoUtf8($row["descripcion"]);
        $row["local"] = normalizarTextoUtf8($row["local"]);
        $registros[] = $row;
    }

    return $registros;
}

function actualizarCita($mysqli, $useru){
    asegurarEstructuraAgendaInsumos($mysqli);
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

    if (($hora_inicio != '' || $hora_fin != '') && !empty($agendaAnterior)) {
        $inicioProfesional = $hora_inicio != '' ? $hora_inicio : (isset($agendaAnterior["hora_inicio"]) ? $agendaAnterior["hora_inicio"] : "");
        $finProfesional = $hora_fin != '' ? $hora_fin : (isset($agendaAnterior["hora_fin"]) ? $agendaAnterior["hora_fin"] : "");
        $idProfesional = obtenerProfesionalAsignadoConsultorioHorario(
            $mysqli,
            isset($agendaAnterior["id_consultorio"]) ? $agendaAnterior["id_consultorio"] : "",
            isset($agendaAnterior["fecha"]) ? $agendaAnterior["fecha"] : "",
            $inicioProfesional,
            $finProfesional
        );
        $campos[] = "id_profesional = ".($idProfesional > 0 ? "'".$idProfesional."'" : "NULL");
    }

    $campos[] = "creado_por = '".$useru."'";
    $campos[] = "creado_en = NOW()";

    $sql = "
        UPDATE agenda SET
            ".implode(",\n            ", $campos)."
        WHERE id_agenda = '".$id_agenda."'
        LIMIT 1
    ";

    $debeDescontarInsumos = strtoupper((string)$estado) == "ATENDIDO"
        && strtoupper((string)(isset($agendaAnterior["estado"]) ? $agendaAnterior["estado"] : "")) != "ATENDIDO";

    $insumosDescontados = 0;
    $mysqli->begin_transaction();
    try {
        if(!$mysqli->query($sql)){
            throw new Exception($mysqli->error);
        }

        if ($debeDescontarInsumos) {
            $estadoAnteriorDescuento = isset($agendaAnterior["estado"]) ? $agendaAnterior["estado"] : "";
            $insumosDescontados = descontarInsumosAgendaAtendida($mysqli, $id_agenda, $useru, $estadoAnteriorDescuento);
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(array(
            "1" => "Error al actualizar cita",
            "mensaje" => "No se pudo actualizar el agendamiento",
            "sql" => $sql,
            "mysql" => $e->getMessage()
        ));
        exit;
    }

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "hora_inicio" => $hora_inicio,
        "hora_fin" => $hora_fin,
        "estado" => $estado,
        "motivo" => $motivo
    ));
    if ($insumosDescontados > 0) {
        crearComentario($id_agenda, "@{0}: @{".$useru."} desconto automaticamente los insumos por cita atendida.");
    }

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
    asegurarEstructuraAgendaInsumos($mysqli);
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

    $debeDescontarInsumos = strtoupper((string)(isset($agendaAnterior["estado"]) ? $agendaAnterior["estado"] : "")) != "ATENDIDO";

    $insumosDescontados = 0;
    $mysqli->begin_transaction();
    try {
        if(!$mysqli->query($sql)){
            throw new Exception($mysqli->error);
        }

        if ($debeDescontarInsumos) {
            $estadoAnteriorDescuento = isset($agendaAnterior["estado"]) ? $agendaAnterior["estado"] : "";
            $insumosDescontados = descontarInsumosAgendaAtendida($mysqli, $id_agenda, $useru, $estadoAnteriorDescuento);
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(array(
            "1" => "Error al actualizar presupuesto",
            "mensaje" => "No se pudo asociar el presupuesto al agendamiento",
            "sql" => $sql,
            "mysql" => $e->getMessage()
        ));
        exit;
    }

    registrarComentariosCambiosAgenda($id_agenda, $useru, $agendaAnterior, array(
        "cod_presupuestoFK" => $cod_presupuestoFK,
    ));
    if ($insumosDescontados > 0) {
        crearComentario($id_agenda, "@{0}: @{".$useru."} desconto automaticamente los insumos por cita atendida.");
    }

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

function obtenerContextoAccesoAgenda($mysqli, $useru)
{
    $useru = (int)$useru;
    $sql = "SELECT u.cod_localFK,
            MAX(CASE
                WHEN (l.codigo='VERFORMULARIOCALENDARIO' OR a.formulario='VERFORMULARIOCALENDARIO')
                    AND UPPER(TRIM(IFNULL(a.accion,'')))='SI'
                THEN 1 ELSE 0 END) AS puede_ver,
            MAX(CASE
                WHEN (l.codigo='VERTODOSLOSCONSULTORIOS' OR a.formulario='VERTODOSLOSCONSULTORIOS')
                    AND UPPER(TRIM(IFNULL(a.accion,'')))='SI'
                THEN 1 ELSE 0 END) AS puede_ver_todos
        FROM usuario u
        LEFT JOIN accesosuser a ON a.usuarios_idusario=u.cod_usuario
        LEFT JOIN listadodeacceso l ON l.idlistadodeacceso=a.idlistadodeaccesoFK
        WHERE u.cod_usuario='".$useru."'
        GROUP BY u.cod_usuario, u.cod_localFK
        LIMIT 1";
    $result = $mysqli->query($sql);
    if (!$result || !($row = $result->fetch_assoc())) {
        return array("puede_ver" => false, "puede_ver_todos" => false, "cod_local_usuario" => 0);
    }
    return array(
        "puede_ver" => (int)$row["puede_ver"] > 0,
        "puede_ver_todos" => (int)$row["puede_ver_todos"] > 0,
        "cod_local_usuario" => (int)$row["cod_localFK"]
    );
}

function condicionVisibilidadConsultorioAgenda($contexto, $aliasConsultorio, $fecha)
{
    $aliasConsultorio = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasConsultorio);
    if ($aliasConsultorio === '') {
        $aliasConsultorio = 'c';
    }
    if (!empty($contexto["puede_ver_todos"])) {
        return "1=1";
    }

    $useru = isset($contexto["useru"]) ? (int)$contexto["useru"] : 0;
    $fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fecha) ? (string)$fecha : date('Y-m-d');
    $diaSemana = addslashes(obtenerDiaSemanaAgenda($fecha));
    return "(
        ".$aliasConsultorio.".cod_doctorFK='".$useru."'
        OR EXISTS (
            SELECT 1
            FROM consultorio_doctor_asignacion a_perm
            INNER JOIN horario_usuario hu_perm ON hu_perm.id=a_perm.id_horario_usuario
            WHERE a_perm.id_consultorio=".$aliasConsultorio.".id_consultorio
                AND a_perm.estado='activo'
                AND hu_perm.cod_usuarioFK='".$useru."'
                AND hu_perm.dia_semana='".$diaSemana."'
                AND IFNULL(hu_perm.estado_horario,'activo')='activo'
                AND (hu_perm.vigente_desde IS NULL OR hu_perm.vigente_desde<='".$fecha."')
                AND (hu_perm.vigente_hasta IS NULL OR hu_perm.vigente_hasta>='".$fecha."')
                AND hu_perm.hora_salida IS NOT NULL
            LIMIT 1
        )
    )";
}

function condicionVisibilidadConsultorioAgendaPorFechaSql($contexto, $aliasConsultorio, $expresionFecha)
{
    $aliasConsultorio = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasConsultorio);
    if ($aliasConsultorio === '') {
        $aliasConsultorio = 'c';
    }
    if (!empty($contexto["puede_ver_todos"])) {
        return "1=1";
    }

    $expresionFecha = preg_replace('/[^a-zA-Z0-9_.]/', '', (string)$expresionFecha);
    if ($expresionFecha === '') {
        $expresionFecha = 'a.fecha';
    }
    $useru = isset($contexto["useru"]) ? (int)$contexto["useru"] : 0;
    $diaSemanaSql = "CASE DAYOFWEEK(".$expresionFecha.")
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END";

    return "(
        ".$aliasConsultorio.".cod_doctorFK='".$useru."'
        OR EXISTS (
            SELECT 1
            FROM consultorio_doctor_asignacion a_perm
            INNER JOIN horario_usuario hu_perm ON hu_perm.id=a_perm.id_horario_usuario
            WHERE a_perm.id_consultorio=".$aliasConsultorio.".id_consultorio
                AND a_perm.estado='activo'
                AND hu_perm.cod_usuarioFK='".$useru."'
                AND hu_perm.dia_semana=(".$diaSemanaSql.")
                AND IFNULL(hu_perm.estado_horario,'activo')='activo'
                AND (hu_perm.vigente_desde IS NULL OR hu_perm.vigente_desde<=".$expresionFecha.")
                AND (hu_perm.vigente_hasta IS NULL OR hu_perm.vigente_hasta>=".$expresionFecha.")
                AND hu_perm.hora_salida IS NOT NULL
            LIMIT 1
        )
    )";
}

function normalizarIdsConsultoriosAgenda($valor)
{
    $ids = array();
    $partes = is_array($valor) ? $valor : explode(',', (string)$valor);
    foreach ($partes as $parte) {
        $id = (int)$parte;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    return array_values($ids);
}

function obtenerCatalogoConsultoriosAgendaBasica($mysqli, $contexto, $fecha, $codLocal = '')
{
    $diaSemana = addslashes(obtenerDiaSemanaAgenda($fecha));
    $fechaSql = addslashes($fecha);
    $condicionVisibilidad = condicionVisibilidadConsultorioAgenda($contexto, 'c', $fecha);
    $filtroLocal = (int)$codLocal > 0 ? " AND c.cod_localFk='".(int)$codLocal."'" : "";
    $condicionHorario = " AND IFNULL(hu.estado_horario,'activo')='activo'
        AND (hu.vigente_desde IS NULL OR hu.vigente_desde<='".$fechaSql."')
        AND (hu.vigente_hasta IS NULL OR hu.vigente_hasta>='".$fechaSql."')";
    $sql = "SELECT c.id_consultorio, c.nombre, c.cod_localFk, l.Nombre AS nombre_local,
            IFNULL(asig.nombres_doctores, (SELECT nombre_persona FROM persona WHERE cod_persona=c.cod_doctorFK)) AS nombre_doctor,
            IFNULL(TIME_FORMAT(asig.horario_inicio_dia,'%H:%i'), (SELECT TIME_FORMAT(MIN(hu.hora_entrada),'%H:%i')
                FROM horario_usuario hu
                WHERE hu.cod_usuarioFK=c.cod_doctorFK AND hu.cod_localFK=c.cod_localFk
                    AND hu.dia_semana='".$diaSemana."' ".$condicionHorario.")) AS horario_inicio_dia,
            IFNULL(TIME_FORMAT(asig.horario_fin_dia,'%H:%i'), (SELECT TIME_FORMAT(MAX(hu.hora_salida),'%H:%i')
                FROM horario_usuario hu
                WHERE hu.cod_usuarioFK=c.cod_doctorFK AND hu.cod_localFK=c.cod_localFk
                    AND hu.dia_semana='".$diaSemana."' ".$condicionHorario." AND hu.hora_salida IS NOT NULL)) AS horario_fin_dia,
            IFNULL(asig.horarios_dia, (SELECT GROUP_CONCAT(CONCAT(TIME_FORMAT(hu.hora_entrada,'%H:%i'),
                    IF(hu.hora_salida IS NULL,'',CONCAT(' - ',TIME_FORMAT(hu.hora_salida,'%H:%i'))))
                    ORDER BY hu.hora_entrada SEPARATOR ' | ')
                FROM horario_usuario hu
                WHERE hu.cod_usuarioFK=c.cod_doctorFK AND hu.cod_localFK=c.cod_localFk
                    AND hu.dia_semana='".$diaSemana."' ".$condicionHorario.")) AS horarios_dia,
            c.descripcion, IFNULL(asig.cod_doctorFK,c.cod_doctorFK) AS cod_doctorFK, c.color
        FROM consultorios c
        LEFT JOIN local l ON l.cod_local=c.cod_localFk
        LEFT JOIN (
            SELECT a.id_consultorio,
                SUBSTRING_INDEX(GROUP_CONCAT(hu.cod_usuarioFK ORDER BY hu.hora_entrada SEPARATOR ','),',',1) AS cod_doctorFK,
                MIN(hu.hora_entrada) AS horario_inicio_dia,
                MAX(hu.hora_salida) AS horario_fin_dia,
                GROUP_CONCAT(CONCAT(p.nombre_persona,' (',TIME_FORMAT(hu.hora_entrada,'%H:%i'),' - ',TIME_FORMAT(hu.hora_salida,'%H:%i'),')')
                    ORDER BY hu.hora_entrada SEPARATOR ' | ') AS nombres_doctores,
                GROUP_CONCAT(CONCAT(TIME_FORMAT(hu.hora_entrada,'%H:%i'),' - ',TIME_FORMAT(hu.hora_salida,'%H:%i'))
                    ORDER BY hu.hora_entrada SEPARATOR ' | ') AS horarios_dia
            FROM consultorio_doctor_asignacion a
            INNER JOIN consultorios ca ON ca.id_consultorio=a.id_consultorio
            INNER JOIN horario_usuario hu ON hu.id=a.id_horario_usuario
            INNER JOIN usuario u ON u.cod_usuario=hu.cod_usuarioFK
            INNER JOIN persona p ON p.cod_persona=u.cod_usuario
            WHERE a.estado='activo' AND ca.estado='Activo' AND u.tipo='DOCTOR' AND u.estado='Activo'
                AND IFNULL(hu.estado_horario,'activo')='activo'
                AND hu.dia_semana='".$diaSemana."' AND hu.cod_localFK=ca.cod_localFk
                AND (hu.vigente_desde IS NULL OR hu.vigente_desde<='".$fechaSql."')
                AND (hu.vigente_hasta IS NULL OR hu.vigente_hasta>='".$fechaSql."')
                AND hu.hora_salida IS NOT NULL
            GROUP BY a.id_consultorio
        ) asig ON asig.id_consultorio=c.id_consultorio
        WHERE c.estado='Activo' AND ".$condicionVisibilidad.$filtroLocal."
        ORDER BY c.cod_localFk, c.nombre";
    $result = $mysqli->query($sql);
    $consultorios = array();
    if (!$result) {
        return false;
    }
    while ($row = $result->fetch_assoc()) {
        $consultorios[] = array(
            "id" => (int)$row["id_consultorio"],
            "cod_doctorFK" => (int)$row["cod_doctorFK"],
            "cod_localFk" => (int)$row["cod_localFk"],
            "nombre_local" => normalizarTextoUtf8($row["nombre_local"]),
            "horario_inicio_dia" => normalizarTextoUtf8($row["horario_inicio_dia"]),
            "horario_fin_dia" => normalizarTextoUtf8($row["horario_fin_dia"]),
            "horarios_dia" => normalizarTextoUtf8($row["horarios_dia"]),
            "nombre" => normalizarTextoUtf8($row["nombre"]),
            "nombre_doctor" => normalizarTextoUtf8($row["nombre_doctor"]),
            "color" => $row["color"] != '' ? $row["color"] : '#7c3aed',
            "descripcion" => normalizarTextoUtf8($row["descripcion"])
        );
    }
    return $consultorios;
}

function cargarAgendaBasica($mysqli, $useru)
{
    $contexto = obtenerContextoAccesoAgenda($mysqli, $useru);
    $contexto["useru"] = (int)$useru;
    if (empty($contexto["puede_ver"])) {
        responderJsonCalendar(array("1" => "NI"));
    }

    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $fecha = date('Y-m-d');
    }
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : '';
    $codLocal = isset($_POST['cod_local']) ? (int)$_POST['cod_local'] : 0;
    $codLocalPreferido = isset($_POST['cod_local_preferido']) ? (int)$_POST['cod_local_preferido'] : 0;
    if ($codLocalPreferido <= 0) {
        $codLocalPreferido = (int)$contexto['cod_local_usuario'];
    }
    $codConsultorio = isset($_POST['cod_consultorio']) ? (int)$_POST['cod_consultorio'] : 0;
    $idsSolicitados = normalizarIdsConsultoriosAgenda(isset($_POST['cod_consultorios']) ? $_POST['cod_consultorios'] : '');
    $seleccionarLocalUsuario = isset($_POST['seleccionar_local_usuario']) && $_POST['seleccionar_local_usuario'] == '1';
    $consultorios = obtenerCatalogoConsultoriosAgendaBasica($mysqli, $contexto, $fecha, $codLocal);
    if ($consultorios === false) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "No se pudieron cargar los consultorios."));
    }
    $permitidos = array();
    foreach ($consultorios as $consultorio) {
        $permitidos[(int)$consultorio['id']] = $consultorio;
    }

    if (count($idsSolicitados) === 0 && $codConsultorio > 0) {
        $idsSolicitados[] = $codConsultorio;
    }
    if (count($idsSolicitados) === 0 && $seleccionarLocalUsuario) {
        foreach ($consultorios as $consultorio) {
            if ((int)$consultorio['cod_localFk'] === $codLocalPreferido) {
                $idsSolicitados[] = (int)$consultorio['id'];
            }
        }
    }
    $idsSeleccionados = array();
    foreach ($idsSolicitados as $idSolicitado) {
        if (isset($permitidos[(int)$idSolicitado])) {
            $idsSeleccionados[] = (int)$idSolicitado;
        }
    }
    if (count($idsSeleccionados) === 0 && count($idsSolicitados) > 0 && $codLocalPreferido > 0) {
        foreach ($consultorios as $consultorio) {
            if ((int)$consultorio['cod_localFk'] === $codLocalPreferido) {
                $idsSeleccionados[] = (int)$consultorio['id'];
            }
        }
    }

    $eventos = array();
    $eventosOcupacion = array();
    if (count($idsSeleccionados) > 0) {
        $listaIds = implode(',', $idsSeleccionados);
        $sqlOcupacion = "SELECT a.id_agenda,a.id_consultorio,a.fecha,
                TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora_inicio,
                TIME_FORMAT(a.hora_fin,'%H:%i') AS hora_fin,a.estado
            FROM agenda a
            WHERE a.fecha='".$fecha."' AND a.id_consultorio IN (".$listaIds.")
            ORDER BY a.id_consultorio,a.hora_inicio,a.id_agenda";
        $resultOcupacion = $mysqli->query($sqlOcupacion);
        while ($resultOcupacion && ($rowOcupacion = $resultOcupacion->fetch_assoc())) {
            $eventosOcupacion[] = array(
                "id" => (int)$rowOcupacion['id_agenda'],
                "consultorio" => (int)$rowOcupacion['id_consultorio'],
                "fecha" => $rowOcupacion['fecha'],
                "inicio" => $rowOcupacion['hora_inicio'],
                "fin" => $rowOcupacion['hora_fin'],
                "estado" => $rowOcupacion['estado']
            );
        }

        $nivelRiesgo = ProductoRiesgoFinancieroNivelSql($mysqli, 'p_at_base');
        $badgeRiesgo = ProductoRiesgoFinancieroBadgeSql($nivelRiesgo, 'agenda-riesgo-badge');
        $condicion = " AND a.fecha='".$fecha."' AND a.id_consultorio IN (".$listaIds.")";
        if ($paciente !== '') {
            $condicion .= " AND (p.nombre_persona LIKE '%".$paciente."%' OR cl.ci_cliente LIKE '%".$paciente."%' OR a.id_paciente LIKE '%".$paciente."%')";
        }
        if ($estado !== '') {
            $condicion .= " AND a.estado='".$estado."'";
        }
        $sqlEventos = "SELECT a.id_agenda,a.id_consultorio,a.id_paciente,a.fecha,
                TIME_FORMAT(a.hora_inicio,'%H:%i') AS hora_inicio,
                TIME_FORMAT(a.hora_fin,'%H:%i') AS hora_fin,a.estado,a.motivo,
                a.cod_ventaFK,a.cod_detalle_ventaFK,IFNULL(v.apodo,'') AS venta_apodo,
                IFNULL(v.num_factura,'') AS venta_num_factura,
                cl.ci_cliente,cl.idzonaFk,cl.whapp,p.telefono,p.direccion,cl.fechanac,
                cl.rut_cliente,cl.cod_cliente,IFNULL(z.nombre,'') AS nombre_zona,
                IF(DATE(IFNULL(pr.fecha_create,'1900-01-01'))=a.fecha,IFNULL(p_pr.nombre_persona,''),'') AS nombre_doctor,
                IFNULL(trat.tratamientos_ids,IF(a.cod_detalle_ventaFK IS NULL,'',a.cod_detalle_ventaFK)) AS tratamientos_ids,
                IFNULL(trat.tratamientos_agenda,IF(p_dir.cod_producto IS NULL,'',CONCAT(p_dir.nombre_producto,' (',IFNULL(dv_dir.progreso_porcentaje,0),'%)'))) AS tratamientos_agenda,
                p.nombre_persona
            FROM agenda a
            INNER JOIN persona p ON p.cod_persona=a.id_paciente
            INNER JOIN cliente cl ON cl.cod_cliente=a.id_paciente
            LEFT JOIN zona z ON z.idzona=cl.idzonaFk
            LEFT JOIN venta v ON v.cod_venta=a.cod_ventaFK
            LEFT JOIN detalle_venta dv_dir ON dv_dir.cod_detalle=a.cod_detalle_ventaFK
            LEFT JOIN producto p_dir ON p_dir.cod_producto=dv_dir.cod_productoFK
            LEFT JOIN presupuesto pr ON pr.id=a.cod_presupuestoFK
            LEFT JOIN persona p_pr ON p_pr.cod_persona=pr.cod_usuarioFK_create
            LEFT JOIN (
                SELECT at_base.id_agenda,
                    GROUP_CONCAT(at_base.cod_detalle_ventaFK ORDER BY at_base.id SEPARATOR ',') AS tratamientos_ids,
                    GROUP_CONCAT(CONCAT(p_at_base.nombre_producto,' ',".$badgeRiesgo.",' (',IFNULL(dv_at_base.progreso_porcentaje,0),'%)')
                        ORDER BY p_at_base.nombre_producto SEPARATOR '<br>') AS tratamientos_agenda
                FROM agenda_tratamientos at_base
                INNER JOIN agenda a_at_base ON a_at_base.id_agenda=at_base.id_agenda
                INNER JOIN detalle_venta dv_at_base ON dv_at_base.cod_detalle=at_base.cod_detalle_ventaFK
                INNER JOIN producto p_at_base ON p_at_base.cod_producto=dv_at_base.cod_productoFK
                WHERE at_base.estado<>'cancelado'
                    AND a_at_base.fecha='".$fecha."'
                    AND a_at_base.id_consultorio IN (".$listaIds.")
                GROUP BY at_base.id_agenda
            ) trat ON trat.id_agenda=a.id_agenda
            WHERE 1=1 ".$condicion."
            ORDER BY a.id_consultorio,a.hora_inicio,a.id_agenda";
        $resultEventos = $mysqli->query($sqlEventos);
        if (!$resultEventos) {
            responderJsonCalendar(array("1" => "Error", "mensaje" => "No se pudo cargar la agenda."));
        }
        while ($row = $resultEventos->fetch_assoc()) {
            $esPrimera = esEstadoPrimeraConsultaAgenda($row['estado']);
            $tratamientosIds = normalizarIdsConsultoriosAgenda($row['tratamientos_ids']);
            $tratamientosTexto = $row['tratamientos_agenda'];
            if ($esPrimera && trim((string)$tratamientosTexto) === '') {
                $tratamientosTexto = 'Primera consulta';
            }
            $eventos[] = array(
                "id" => (int)$row['id_agenda'],
                "consultorio" => (int)$row['id_consultorio'],
                "id_paciente" => (int)$row['id_paciente'],
                "cod_ventaFK" => (int)$row['cod_ventaFK'],
                "cod_detalle_ventaFK" => (int)$row['cod_detalle_ventaFK'],
                "venta_apodo" => normalizarTextoUtf8($row['venta_apodo']),
                "venta_num_factura" => normalizarTextoUtf8($row['venta_num_factura']),
                "tratamientos_ids" => $tratamientosIds,
                "paciente" => normalizarTextoUtf8($row['nombre_persona']),
                "fecha" => $row['fecha'],
                "inicio" => $row['hora_inicio'],
                "fin" => $row['hora_fin'],
                "estado" => $row['estado'],
                "ci_cliente" => $row['ci_cliente'],
                "idzonaFk" => $row['idzonaFk'],
                "whapp" => $row['whapp'],
                "telefono" => $row['telefono'],
                "direccion" => normalizarTextoUtf8($row['direccion']),
                "fechanac" => $row['fechanac'],
                "nombre_zona" => normalizarTextoUtf8($row['nombre_zona']),
                "rut_cliente" => $row['rut_cliente'],
                "cod_cliente" => $row['cod_cliente'],
                "nombre_doctor" => normalizarTextoUtf8($row['nombre_doctor']),
                "nombres_tratamiento" => normalizarTextoUtf8($tratamientosTexto),
                "nombre_tratamiento_pendiente" => normalizarTextoUtf8($tratamientosTexto),
                "sin_tratamiento" => count($tratamientosIds) === 0 && !$esPrimera,
                "riesgo_insumos" => false,
                "insumos_previstos" => array(),
                "insumos_faltantes" => array(),
                "motivo" => normalizarTextoUtf8($row['motivo']),
                "motivo_limpio" => '',
                "_agendaCompleto" => false
            );
        }
    }

    $feriados = obtenerDiasFeriados($mysqli, array("fecha" => $fecha, "cod_local" => $codLocal));
    responderJsonCalendar(array(
        "1" => "exito",
        "consultorios" => $consultorios,
        "consultorios_seleccionados" => $idsSeleccionados,
        "eventos" => $eventos,
        "eventos_ocupacion" => $eventosOcupacion,
        "feriados" => $feriados,
        "fase" => "basica",
        "local_usuario" => $codLocalPreferido
    ));
}

function cargarAgenda($mysqli, $useru){
    $contextoAccesoAgenda = obtenerContextoAccesoAgenda($mysqli, $useru);
    $contextoAccesoAgenda["useru"] = (int)$useru;
    if (empty($contextoAccesoAgenda["puede_ver"])) {
        responderJsonCalendar(array("1" => "NI"));
    }
    $fecha = isset($_POST['fecha']) ? limpiar($mysqli, $_POST['fecha']) : '';
    $fecha_desde = isset($_POST['fecha_desde']) ? limpiar($mysqli, $_POST['fecha_desde']) : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? limpiar($mysqli, $_POST['fecha_hasta']) : '';
    $paciente = isset($_POST['paciente']) ? limpiar($mysqli, $_POST['paciente']) : '';
    $cod_consultorio = isset($_POST['cod_consultorio']) ? limpiar($mysqli, $_POST['cod_consultorio']) : '';
    $cod_consultorios = isset($_POST['cod_consultorios']) ? limpiar($mysqli, $_POST['cod_consultorios']) : '';
    $cod_local = isset($_POST['cod_local']) ? limpiar($mysqli, $_POST['cod_local']) : '';
    $estado = isset($_POST['estado']) ? limpiar($mysqli, $_POST['estado']) : '';
    $ver_todos_consoltorios = !empty($contextoAccesoAgenda["puede_ver_todos"]) ? 'true' : 'false';
    $solo_catalogo = isset($_POST['solo_catalogo']) ? limpiar($mysqli, $_POST['solo_catalogo']) : '0';
    $idsConsultoriosFiltro = array();
    if ($cod_consultorios != '') {
        $partesConsultorios = explode(',', $cod_consultorios);
        foreach ($partesConsultorios as $idConsultorioFiltro) {
            $idConsultorioFiltro = (int)$idConsultorioFiltro;
            if ($idConsultorioFiltro > 0) {
                $idsConsultoriosFiltro[$idConsultorioFiltro] = $idConsultorioFiltro;
            }
        }
        $idsConsultoriosFiltro = array_values($idsConsultoriosFiltro);
    }
    $teniaFiltroConsultoriosAgenda = count($idsConsultoriosFiltro) > 0;
    $sqlFiltroConsultoriosAgenda = count($idsConsultoriosFiltro) > 0 ? implode(',', $idsConsultoriosFiltro) : '';

    if ($fecha == '') {
        $fecha = date('Y-m-d');
    }
    $dia_semana_agenda = obtenerDiaSemanaAgenda($fecha);
    $condicionVisibilidadCatalogoAgenda = condicionVisibilidadConsultorioAgenda($contextoAccesoAgenda, 'c', $fecha);
    $condicionVisibilidadEventosAgenda = condicionVisibilidadConsultorioAgendaPorFechaSql($contextoAccesoAgenda, 'c', 'a.fecha');
    $condicionHorarioVigenteAgenda = " AND IFNULL(hu.estado_horario,'activo')='activo'
        AND (hu.vigente_desde IS NULL OR hu.vigente_desde <= '".$fecha."')
        AND (hu.vigente_hasta IS NULL OR hu.vigente_hasta >= '".$fecha."')";

    $consultorios = array();
    $eventos = array();
    $eventosOcupacion = array();

    /* ===========================
       CONSULTORIOS
    =========================== */
	$sqlFiltro="";
    if($cod_local!=""){
		$sqlFiltro.=" and c.cod_localFk = '".$cod_local."'";
	}
    $sqlFiltro .= " AND ".$condicionVisibilidadCatalogoAgenda;
	
    $sqlConsultorios = "
        SELECT  c.id_consultorio,
            c.nombre,
            c.cod_localFk,
            l.Nombre AS nombre_local,
            IFNULL(asig.nombres_doctores, (SELECT nombre_persona FROM persona WHERE cod_persona= c.cod_doctorFK)) AS nombre_doctor,
            IFNULL(TIME_FORMAT(asig.horario_inicio_dia, '%H:%i'), (SELECT TIME_FORMAT(MIN(hu.hora_entrada), '%H:%i')
                FROM horario_usuario hu
                WHERE hu.cod_usuarioFK = c.cod_doctorFK
                AND hu.cod_localFK = c.cod_localFk
                AND hu.dia_semana = '".$dia_semana_agenda."'
                ".$condicionHorarioVigenteAgenda.")) AS horario_inicio_dia,
            IFNULL(TIME_FORMAT(asig.horario_fin_dia, '%H:%i'), (SELECT TIME_FORMAT(MAX(hu.hora_salida), '%H:%i')
                FROM horario_usuario hu
                WHERE hu.cod_usuarioFK = c.cod_doctorFK
                AND hu.cod_localFK = c.cod_localFk
                AND hu.dia_semana = '".$dia_semana_agenda."'
                ".$condicionHorarioVigenteAgenda."
                AND hu.hora_salida IS NOT NULL)) AS horario_fin_dia,
            IFNULL(asig.horarios_dia, (SELECT GROUP_CONCAT(
                    CONCAT(
                        TIME_FORMAT(hu.hora_entrada, '%H:%i'),
                        IF(hu.hora_salida IS NULL, '', CONCAT(' - ', TIME_FORMAT(hu.hora_salida, '%H:%i')))
                    )
                    ORDER BY hu.hora_entrada ASC
                    SEPARATOR ' | '
                )
                FROM horario_usuario hu
                WHERE hu.cod_usuarioFK = c.cod_doctorFK
                AND hu.cod_localFK = c.cod_localFk
                AND hu.dia_semana = '".$dia_semana_agenda."'
                ".$condicionHorarioVigenteAgenda.")) AS horarios_dia,
            c.descripcion,
            IFNULL(asig.cod_doctorFK, c.cod_doctorFK) AS cod_doctorFK,
            c.color
        FROM consultorios c
        LEFT JOIN local l ON l.cod_local = c.cod_localFk
        LEFT JOIN (
            SELECT
                a.id_consultorio,
                SUBSTRING_INDEX(GROUP_CONCAT(hu.cod_usuarioFK ORDER BY hu.hora_entrada ASC SEPARATOR ','), ',', 1) AS cod_doctorFK,
                GROUP_CONCAT(hu.cod_usuarioFK ORDER BY hu.hora_entrada ASC SEPARATOR ',') AS cod_doctores,
                MIN(hu.hora_entrada) AS horario_inicio_dia,
                MAX(hu.hora_salida) AS horario_fin_dia,
                GROUP_CONCAT(
                    CONCAT(
                        p.nombre_persona,
                        ' (',
                        TIME_FORMAT(hu.hora_entrada, '%H:%i'),
                        ' - ',
                        TIME_FORMAT(hu.hora_salida, '%H:%i'),
                        ')'
                    )
                    ORDER BY hu.hora_entrada ASC
                    SEPARATOR ' | '
                ) AS nombres_doctores,
                GROUP_CONCAT(
                    CONCAT(
                        TIME_FORMAT(hu.hora_entrada, '%H:%i'),
                        ' - ',
                        TIME_FORMAT(hu.hora_salida, '%H:%i')
                    )
                    ORDER BY hu.hora_entrada ASC
                    SEPARATOR ' | '
                ) AS horarios_dia
            FROM consultorio_doctor_asignacion a
            INNER JOIN consultorios ca ON ca.id_consultorio = a.id_consultorio
            INNER JOIN horario_usuario hu ON hu.id = a.id_horario_usuario
            INNER JOIN usuario u ON u.cod_usuario = hu.cod_usuarioFK
            INNER JOIN persona p ON p.cod_persona = u.cod_usuario
            WHERE a.estado = 'activo'
            AND ca.estado = 'Activo'
            AND u.tipo = 'DOCTOR'
            AND u.estado = 'Activo'
            AND IFNULL(hu.estado_horario,'activo') = 'activo'
            AND hu.dia_semana = '".$dia_semana_agenda."'
            AND hu.cod_localFK = ca.cod_localFk
            AND (hu.vigente_desde IS NULL OR hu.vigente_desde <= '".$fecha."')
            AND (hu.vigente_hasta IS NULL OR hu.vigente_hasta >= '".$fecha."')
            AND hu.hora_salida IS NOT NULL
            GROUP BY a.id_consultorio
        ) asig ON asig.id_consultorio = c.id_consultorio
        WHERE  c.estado = 'Activo' ".$sqlFiltro."
        ORDER BY c.cod_localFk asc ,c.nombre ASC ";

    $resultConsultorios = $mysqli->query($sqlConsultorios);

    if (!$resultConsultorios) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "No se pudieron cargar los consultorios."));
    }

    while ($row = $resultConsultorios->fetch_assoc()) {
        $consultorios[] = array(
            "id" => (int)$row["id_consultorio"],
            "cod_doctorFK" => (int)$row["cod_doctorFK"],
            "cod_localFk" => (int)$row["cod_localFk"],
            "nombre_local" => normalizarTextoUtf8($row["nombre_local"]),
            "horario_inicio_dia" => normalizarTextoUtf8($row["horario_inicio_dia"]),
            "horario_fin_dia" => normalizarTextoUtf8($row["horario_fin_dia"]),
            "horarios_dia" => normalizarTextoUtf8($row["horarios_dia"]),
            "nombre" => normalizarTextoUtf8($row["nombre"]),
            "nombre_doctor" => normalizarTextoUtf8($row["nombre_doctor"]),
            "color" => $row["color"] != '' ? $row["color"] : "#7c3aed",
            "descripcion" => normalizarTextoUtf8($row["descripcion"])
        );
    }

    $idsPermitidosAgenda = array();
    foreach ($consultorios as $consultorioPermitidoAgenda) {
        $idsPermitidosAgenda[(int)$consultorioPermitidoAgenda["id"]] = true;
    }
    if ($teniaFiltroConsultoriosAgenda) {
        $idsConsultoriosAutorizadosAgenda = array();
        foreach ($idsConsultoriosFiltro as $idConsultorioSolicitadoAgenda) {
            if (isset($idsPermitidosAgenda[(int)$idConsultorioSolicitadoAgenda])) {
                $idsConsultoriosAutorizadosAgenda[] = (int)$idConsultorioSolicitadoAgenda;
            }
        }
        $idsConsultoriosFiltro = $idsConsultoriosAutorizadosAgenda;
        $sqlFiltroConsultoriosAgenda = count($idsConsultoriosFiltro) > 0 ? implode(',', $idsConsultoriosFiltro) : '0';
    }

    if ($solo_catalogo == '1' || (count($idsConsultoriosFiltro) == 0 && $cod_consultorio == '')) {
        echo json_encode(array(
            "1" => "exito",
            "consultorios" => $consultorios,
            "consultorios_seleccionados" => $idsConsultoriosFiltro,
            "eventos" => array(),
            "eventos_ocupacion" => array(),
            "feriados" => array(),
            "fase" => "enriquecida",
            "local_usuario" => (int)$contextoAccesoAgenda["cod_local_usuario"]
        ));
        exit;
    }

    $condicionOcupacion = "";
    if($fecha!=""){
        $condicionOcupacion.=" and a.fecha = '".$fecha."'";
    }
    if($sqlFiltroConsultoriosAgenda!=""){
        $condicionOcupacion.=" and a.id_consultorio in (".$sqlFiltroConsultoriosAgenda.")";
    }
    if($cod_local!=""){
        $condicionOcupacion.=" and c.cod_localFk = '".$cod_local."'";
    }
    $condicionOcupacion .= " AND ".$condicionVisibilidadEventosAgenda;

    $sqlEventosOcupacion = "SELECT
            a.id_agenda,
            a.id_consultorio,
            a.fecha,
            TIME_FORMAT(a.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(a.hora_fin, '%H:%i') AS hora_fin,
            a.estado
        FROM agenda a
        INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
        WHERE 1=1 ".$condicionOcupacion."
        ORDER BY a.fecha ASC, a.id_consultorio ASC, a.hora_inicio ASC, a.id_agenda ASC";

    $resultEventosOcupacion = $mysqli->query($sqlEventosOcupacion);
    if ($resultEventosOcupacion) {
        while ($rowOcupacion = $resultEventosOcupacion->fetch_assoc()) {
            $eventosOcupacion[] = array(
                "id" => (int)$rowOcupacion["id_agenda"],
                "consultorio" => (int)$rowOcupacion["id_consultorio"],
                "fecha" => $rowOcupacion["fecha"],
                "inicio" => $rowOcupacion["hora_inicio"],
                "fin" => $rowOcupacion["hora_fin"],
                "estado" => $rowOcupacion["estado"]
            );
        }
    }

    /* ===========================
       EVENTOS / AGENDAMIENTOS
    =========================== */
	
	$condicion="";
	if($fecha_desde!="" && $fecha_hasta!=""){
		$condicion.=" and a.fecha between '".$fecha_desde."' and '".$fecha_hasta."'";
	}else if($fecha!=""){
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

    if($sqlFiltroConsultoriosAgenda!=""){
        $condicion.=" and a.id_consultorio in (".$sqlFiltroConsultoriosAgenda.")";
    }
	
	if($cod_local!=""){
		$condicion.=" and c.cod_localFk = '".$cod_local."'";
	}

    $condicion .= " AND ".$condicionVisibilidadEventosAgenda;
	
	if($estado!=""){
		$condicion.=" and a.estado = '".$estado."'";
	}	

    $nivelRiesgoProducto = ProductoRiesgoFinancieroNivelSql($mysqli, "p");
    $badgeRiesgoProducto = ProductoRiesgoFinancieroBadgeSql($nivelRiesgoProducto, "agenda-riesgo-badge");
    $nivelRiesgoProductoAgenda = ProductoRiesgoFinancieroNivelSql($mysqli, "p2");
    $badgeRiesgoProductoAgenda = ProductoRiesgoFinancieroBadgeSql($nivelRiesgoProductoAgenda, "agenda-riesgo-badge");
	
    $sqlEventos = "SELECT  a.id_agenda,
            a.id_consultorio,
            a.fecha,
            TIME_FORMAT(a.hora_inicio, '%H:%i') AS hora_inicio,
            TIME_FORMAT(a.hora_fin, '%H:%i') AS hora_fin,
            a.estado,
            a.motivo,
            a.cod_ventaFK,
            a.cod_detalle_ventaFK,
            IFNULL(v.apodo,'') AS venta_apodo,
            IFNULL(v.num_factura,'') AS venta_num_factura,
            cl.ci_cliente, cl.idzonaFk,cl.whapp, p.telefono, p.direccion, cl.fechanac,cl.rut_cliente,cl.cod_cliente,
            (SELECT nombre FROM zona WHERE idzona = cl.idzonaFk) AS nombre_zona,
            (SELECT nombre_persona FROM persona JOIN presupuesto ON cod_usuarioFK_create = cod_persona WHERE a.cod_presupuestoFK = id) AS nombre_doctor_presupesto,
            (SELECT fecha_create FROM presupuesto WHERE id = a.cod_presupuestoFK) AS fecha_presupuesto,
            (SELECT nombre_persona FROM persona JOIN consulta ON consulta.cod_usuarioFK = cod_persona WHERE consulta.cod_agendamientoFK is not null and a.id_agenda = consulta.cod_agendamientoFK limit 1) AS nombre_doctor_consulta,
            (SELECT fecha FROM consulta WHERE consulta.cod_agendamientoFK is not null and cod_agendamientoFK = a.id_agenda limit 1) AS fecha_consulta,
            (SELECT nombre_persona FROM persona JOIN evoluciontratamiento et ON et.cod_usuraioFK = cod_persona WHERE a.id_agenda = et.cod_agendaFK order by et.cod_evoluciontratamiento desc limit 1) AS nombre_doctor_tratamiento,
            (SELECT fecha FROM evoluciontratamiento et WHERE et.cod_agendaFK = a.id_agenda order by et.cod_evoluciontratamiento desc limit 1) AS fecha_tratamiento,
            (SELECT GROUP_CONCAT(CONCAT(p.nombre_producto, ' ', ".$badgeRiesgoProducto.", ' (', et.nro,'%)') SEPARATOR '<br>') FROM evoluciontratamiento et JOIN detalle_venta dv ON et.cod_detalle_venta = dv.cod_detalle JOIN producto p ON p.cod_producto= dv.cod_productoFK WHERE cod_agendaFK = a.id_agenda) AS nombre_tratamiento,
            (IFNULL((SELECT CONCAT(p.nombre_producto, ' ', ".$badgeRiesgoProducto.", ' (', IFNULL(dv.progreso_porcentaje,0), '%)') FROM detalle_venta dv JOIN producto p ON p.cod_producto= dv.cod_productoFK WHERE dv.cod_detalle = a.cod_detalle_ventaFK), '')) AS nombre_tratamiento_pendiente,
            (SELECT GROUP_CONCAT(at.cod_detalle_ventaFK ORDER BY at.id ASC SEPARATOR ',') FROM agenda_tratamientos at WHERE at.id_agenda = a.id_agenda AND at.estado <> 'cancelado') AS tratamientos_ids,
            (SELECT GROUP_CONCAT(CONCAT(p2.nombre_producto, ' ', ".$badgeRiesgoProductoAgenda.", ' (', IFNULL(dv2.progreso_porcentaje,0), '%)') ORDER BY p2.nombre_producto ASC SEPARATOR '<br>') FROM agenda_tratamientos at2 JOIN detalle_venta dv2 ON dv2.cod_detalle = at2.cod_detalle_ventaFK JOIN producto p2 ON p2.cod_producto = dv2.cod_productoFK WHERE at2.id_agenda = a.id_agenda AND at2.estado <> 'cancelado') AS tratamientos_agenda,
            p.nombre_persona
        FROM agenda a
        INNER JOIN persona p ON p.cod_persona = a.id_paciente
        INNER JOIN cliente cl ON cl.cod_cliente = a.id_paciente
        INNER JOIN consultorios c ON c.id_consultorio = a.id_consultorio
        LEFT JOIN venta v ON v.cod_venta = a.cod_ventaFK
        WHERE 1=1 ".$condicion."
        ORDER BY a.fecha ASC, a.id_consultorio ASC, a.hora_inicio ASC, a.id_agenda ASC
    ";

    $resultEventos = $mysqli->query($sqlEventos);

    if (!$resultEventos) {
        responderJsonCalendar(array("1" => "Error", "mensaje" => "No se pudo cargar la agenda."));
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
        $fechaTratamiento = isset($row["fecha_tratamiento"]) ? (string)$row["fecha_tratamiento"] : "";
        $fechaPresupuesto = isset($row["fecha_presupuesto"]) ? (string)$row["fecha_presupuesto"] : "";
        if ($row["fecha_consulta"] == $row["fecha"]) {
            $nombre_doctor = $row["nombre_doctor_consulta"];
        } elseif (substr($fechaTratamiento, 0, 10) == $row["fecha"]) {
            $nombre_doctor = $row["nombre_doctor_tratamiento"];
        } elseif (substr($fechaPresupuesto, 0, 10) == $row["fecha"]) {
            $nombre_doctor = $row["nombre_doctor_presupesto"];
        }

        $esPrimeraConsultaEvento = esEstadoPrimeraConsultaAgenda($row["estado"]);
        $productoPrimeraConsultaEvento = $esPrimeraConsultaEvento ? obtenerProductoPrimeraConsultaAgenda($mysqli) : null;
        $nombrePrimeraConsultaEvento = $productoPrimeraConsultaEvento ? $productoPrimeraConsultaEvento["nombre_producto"] : "Primera consulta";
        $nombresTratamientos = $row["tratamientos_agenda"] != "" ? $row["tratamientos_agenda"] : $row["nombre_tratamiento"];
        $nombrePendiente = $row["tratamientos_agenda"] != "" ? $row["tratamientos_agenda"] : $row["nombre_tratamiento_pendiente"];
        if ($esPrimeraConsultaEvento && trim((string)$nombresTratamientos) == "") {
            $nombresTratamientos = $nombrePrimeraConsultaEvento;
        }
        if ($esPrimeraConsultaEvento && trim((string)$nombrePendiente) == "") {
            $nombrePendiente = $nombrePrimeraConsultaEvento;
        }
        $tratamientosIds = array();
        if ($row["tratamientos_ids"] != "") {
            $partesTratamientos = explode(",", $row["tratamientos_ids"]);
            foreach ($partesTratamientos as $idTrat) {
                $idTrat = (int)$idTrat;
                if ($idTrat > 0) {
                    $tratamientosIds[] = $idTrat;
                }
            }
        } elseif ((int)$row["cod_detalle_ventaFK"] > 0) {
            $tratamientosIds[] = (int)$row["cod_detalle_ventaFK"];
        }

        $eventos[] = array(
            "id" => (int)$row["id_agenda"],
            "consultorio" => (int)$row["id_consultorio"],
            "cod_ventaFK" => (int)$row["cod_ventaFK"],
            "cod_detalle_ventaFK" => (int)$row["cod_detalle_ventaFK"],
            "venta_apodo" => normalizarTextoUtf8($row["venta_apodo"]),
            "venta_num_factura" => normalizarTextoUtf8($row["venta_num_factura"]),
            "tratamientos_ids" => $tratamientosIds,
            "paciente" => normalizarTextoUtf8($row["nombre_persona"]),
            "fecha" => $row["fecha"],
            "inicio" => $row["hora_inicio"],
            "fin" => $row["hora_fin"],
            "estado" => $row["estado"],
            "ci_cliente" => $row["ci_cliente"],
            "idzonaFk" => $row["idzonaFk"],
            "whapp" => $row["whapp"],
            "telefono" => $row["telefono"],
            "direccion" => normalizarTextoUtf8($row["direccion"]),
            "fechanac" => $row["fechanac"],
            "nombre_zona" => $row["nombre_zona"],
            "rut_cliente" => $row["rut_cliente"],
            "cod_cliente" => $row["cod_cliente"],
            "nombre_doctor" => $nombre_doctor,
            "nombres_tratamiento" => normalizarTextoUtf8($nombresTratamientos),
            "nombre_tratamiento_pendiente" => normalizarTextoUtf8($nombrePendiente),
            "sin_tratamiento" => count($tratamientosIds) == 0 && !$esPrimeraConsultaEvento,
            "riesgo_insumos" => false,
            "insumos_previstos" => array(),
            "insumos_faltantes" => array(),
            "motivo" => normalizarTextoUtf8($row["motivo"]),
            "motivo_limpio" => $motivoLimpio
        );
    }

    $stockProyectado = array();
    for ($i = 0; $i < count($eventos); $i++) {
        $estadoEvento = strtoupper((string)$eventos[$i]["estado"]);
        $entraProyeccion = in_array($estadoEvento, array("AGENDADO", "CONFIRMADO", "CONFIRMADOCONDEUDA", "PRIMERACONSULTA"));
        if (count($eventos[$i]["tratamientos_ids"]) == 0 && !esEstadoPrimeraConsultaAgenda($eventos[$i]["estado"])) {
            continue;
        }

        $insumosPrevistos = obtenerInsumosPrevistosAgenda(
            $mysqli,
            (int)$eventos[$i]["id"],
            (int)$eventos[$i]["cod_ventaFK"],
            $eventos[$i]["tratamientos_ids"],
            (int)$eventos[$i]["consultorio"],
            $eventos[$i]["estado"],
            false
        );

        $faltantes = array();
        foreach ($insumosPrevistos as $indiceInsumo => $insumo) {
            $clave = $eventos[$i]["consultorio"]."-".$insumo["id_insumo"]."-".(isset($insumo["id_variante"]) ? (int)$insumo["id_variante"] : 0);
            if (!isset($stockProyectado[$clave])) {
                $stockProyectado[$clave] = normalizarNumeroAgenda($insumo["stock"]);
            }
            $cantidad = normalizarNumeroAgenda($insumo["cantidad"]);
            $stockMinimo = normalizarNumeroAgenda($insumo["stock_minimo"]);
            $faltante = 0;
            if ($entraProyeccion) {
                $stockDespues = $stockProyectado[$clave] - $cantidad;
                $faltante = $stockMinimo > 0 ? max(0, $stockMinimo - $stockDespues) : 0;
                $stockProyectado[$clave] = $stockDespues;
            } else {
                $faltante = normalizarNumeroAgenda($insumo["faltante"]);
            }
            $insumosPrevistos[$indiceInsumo]["faltante"] = $faltante;
            if ($faltante > 0) {
                $faltantes[] = $insumosPrevistos[$indiceInsumo];
            }
        }

        $eventos[$i]["insumos_previstos"] = $insumosPrevistos;
        $eventos[$i]["insumos_faltantes"] = $faltantes;
        $eventos[$i]["riesgo_insumos"] = count($faltantes) > 0;
    }

    // --- AGREGADO: Obtener los feriados del día ---
    $feriados = obtenerDiasFeriados($mysqli, array(
        "fecha" => $fecha,
        "cod_local" => $cod_local
    ));

    echo json_encode(array(
        "1" => "exito",
        "consultorios" => $consultorios,
        "consultorios_seleccionados" => $idsConsultoriosFiltro,
        "eventos" => $eventos,
        "eventos_ocupacion" => $eventosOcupacion,
        "feriados" => $feriados,
        "fase" => "enriquecida",
        "local_usuario" => (int)$contextoAccesoAgenda["cod_local_usuario"]
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
    $idProfesional = obtenerProfesionalAsignadoConsultorioHorario($mysqli, $id_consultorio, $fecha, $hora_inicio, $hora_fin);
    $idProfesionalSql = $idProfesional > 0 ? "'".$idProfesional."'" : "NULL";

    $sql = "
        UPDATE agenda SET
            id_consultorio = '".$id_consultorio."',
            id_profesional = ".$idProfesionalSql.",
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
    $idProfesional = 0;
    if (!empty($agendaAnterior)) {
        $idProfesional = obtenerProfesionalAsignadoConsultorioHorario(
            $mysqli,
            isset($agendaAnterior["id_consultorio"]) ? $agendaAnterior["id_consultorio"] : "",
            isset($agendaAnterior["fecha"]) ? $agendaAnterior["fecha"] : "",
            isset($agendaAnterior["hora_inicio"]) ? $agendaAnterior["hora_inicio"] : "",
            $hora_fin
        );
    }
    $idProfesionalSql = $idProfesional > 0 ? "'".$idProfesional."'" : "NULL";

    $sql = "
        UPDATE agenda SET
            hora_fin = '".$hora_fin."',
            id_profesional = ".$idProfesionalSql.",
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
            $estadoColor = "#6c757d";
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

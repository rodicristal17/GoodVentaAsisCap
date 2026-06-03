<?php
require_once("conexion.php");
include_once("verificar_navegador.php");

function responderInformeSolicitudEliminado($datos) {
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function verificarInformeSolicitudEliminado($accion) {
    $user = isset($_POST['useru']) ? mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8') : '';
    $pass = isset($_POST['passu']) ? $_POST['passu'] : '';
    $pass = str_replace("=", "+", $pass);
    $navegador = isset($_POST['navegador']) ? mb_convert_encoding((string)($_POST['navegador']), 'ISO-8859-1', 'UTF-8') : '';

    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != "ok") {
        responderInformeSolicitudEliminado(array("1" => "UI"));
    }

    switch ($accion) {
        case 'buscar':
            buscarInformeSolicitudEliminado();
            break;
        case 'pendientes':
            buscarSolicitudesEliminacionPendientes();
            break;
        case 'solicitar':
            crearSolicitudEliminado($user);
            break;
        case 'detalle':
            obtenerDetalleSolicitudEliminado();
            break;
        case 'aprobar':
            resolverSolicitudEliminado('aprobada', $user);
            break;
        case 'rechazar':
            resolverSolicitudEliminado('rechazada', $user);
            break;
        default:
            responderInformeSolicitudEliminado(array("1" => "error", "2" => "$accion NO IMPLEMENTADA."));
            break;
    }
}

function agregarParametroSolicitudEliminado(&$tipos, &$params, $tipo, $valor) {
    $tipos .= $tipo;
    $params[] = $valor;
}

function buscarInformeSolicitudEliminado() {
    $buscar = isset($_POST['buscar']) ? mb_convert_encoding((string)($_POST['buscar']), 'ISO-8859-1', 'UTF-8') : '';
    $usuario = isset($_POST['usuario']) ? mb_convert_encoding((string)($_POST['usuario']), 'ISO-8859-1', 'UTF-8') : '';
    $estado = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : '';
    $fecha_desde = isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : '';

    $where = array();
    $tipos = "";
    $params = array();

    if ($buscar !== "") {
        $where[] = "(se.id_solicitud_eliminado LIKE ? OR se.motivo LIKE ? OR se.tabla_nombre LIKE ? OR se.registro_pk_valor LIKE ? OR se.registro_resumen LIKE ?)";
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$buscar."%");
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$buscar."%");
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$buscar."%");
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$buscar."%");
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$buscar."%");
    }
    if ($usuario !== "") {
        $where[] = "((SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_solicitud) LIKE ? OR se.id_usuario_solicitud = ? OR (SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_aprobacion) LIKE ? OR se.id_usuario_aprobacion = ?)";
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$usuario."%");
        agregarParametroSolicitudEliminado($tipos, $params, "s", $usuario);
        agregarParametroSolicitudEliminado($tipos, $params, "s", "%".$usuario."%");
        agregarParametroSolicitudEliminado($tipos, $params, "s", $usuario);
    }
    if ($estado !== "") {
        $where[] = "se.estado = ?";
        agregarParametroSolicitudEliminado($tipos, $params, "s", $estado);
    }
    if ($fecha_desde !== "") {
        $where[] = "DATE(se.fecha_solicitud) >= ?";
        agregarParametroSolicitudEliminado($tipos, $params, "s", $fecha_desde);
    }
    if ($fecha_hasta !== "") {
        $where[] = "DATE(se.fecha_solicitud) <= ?";
        agregarParametroSolicitudEliminado($tipos, $params, "s", $fecha_hasta);
    }

    $sqlWhere = count($where) > 0 ? "WHERE ".implode(" AND ", $where) : "";
    $sql = "SELECT
                se.id_solicitud_eliminado,
                se.id_usuario_solicitud,
                se.fecha_solicitud,
                se.tabla_nombre,
                se.registro_pk_columna,
                se.registro_pk_valor,
                se.registro_resumen,
                se.motivo,
                se.estado,
                se.fecha_aprobacion,
                se.id_usuario_aprobacion,
                se.observacion_aprobacion,
                (SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_solicitud) AS usuario_solicitud,
                (SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_aprobacion) AS usuario_aprobacion
            FROM solicitud_eliminado se
            $sqlWhere
            ORDER BY se.fecha_solicitud DESC, se.id_solicitud_eliminado DESC
            LIMIT 500";

    $mysqli = conectar_al_servidor();
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo preparar la consulta.", "sql" => $sql, "3" => $mysqli->error));
    }

    if (count($params) > 0) {
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($tipos), $refs));
    }

    if (!$stmt->execute()) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo consultar las solicitudes.", "sql" => $sql, "3" => $stmt->error));
    }

    $result = $stmt->get_result();
    $registros = array();
    $pagina = "";

    while ($row = $result->fetch_assoc()) {
        $reg = array();
        foreach ($row as $key => $value) {
            $reg[$key] = mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
        }
        $registros[] = $reg;

        $id_html = htmlspecialchars($reg['id_solicitud_eliminado'], ENT_QUOTES, 'UTF-8');
        $motivo_html = nl2br(htmlspecialchars($reg['motivo'], ENT_QUOTES, 'UTF-8'));
        $tabla_html = htmlspecialchars($reg['tabla_nombre'], ENT_QUOTES, 'UTF-8');
        $pk_columna_html = htmlspecialchars($reg['registro_pk_columna'], ENT_QUOTES, 'UTF-8');
        $pk_valor_html = htmlspecialchars($reg['registro_pk_valor'], ENT_QUOTES, 'UTF-8');
        $resumen_html = htmlspecialchars($reg['registro_resumen'], ENT_QUOTES, 'UTF-8');
        $estado_html = htmlspecialchars($reg['estado'], ENT_QUOTES, 'UTF-8');
        $fecha_solicitud_html = htmlspecialchars($reg['fecha_solicitud'], ENT_QUOTES, 'UTF-8');
        $fecha_aprobacion_html = htmlspecialchars($reg['fecha_aprobacion'], ENT_QUOTES, 'UTF-8');
        $usuario_solicitud_html = htmlspecialchars($reg['usuario_solicitud'], ENT_QUOTES, 'UTF-8');
        $usuario_aprobacion_html = htmlspecialchars($reg['usuario_aprobacion'], ENT_QUOTES, 'UTF-8');
        $id_usuario_solicitud_html = htmlspecialchars($reg['id_usuario_solicitud'], ENT_QUOTES, 'UTF-8');
        $id_usuario_aprobacion_html = htmlspecialchars($reg['id_usuario_aprobacion'], ENT_QUOTES, 'UTF-8');

        if ($usuario_solicitud_html == "") {
            $usuario_solicitud_html = "Usuario ".$id_usuario_solicitud_html;
        }
        if ($usuario_aprobacion_html == "") {
            $usuario_aprobacion_html = $id_usuario_aprobacion_html != "" ? "Usuario ".$id_usuario_aprobacion_html : "Pendiente";
        }
        if ($fecha_aprobacion_html == "") {
            $fecha_aprobacion_html = "Pendiente";
        }
        if ($tabla_html == "") {
            $tabla_html = "Sin registro asociado";
        }

        $acciones_html = "";
        if ($reg['estado'] == 'pendiente') {
            $acciones_html = "<button type='button' class='solicitud-eliminado-card__action' onclick='abrirVentanaEvaluarSolicitudEliminado(\"".$id_html."\")'>Evaluar</button>";
        }

        $pagina .= "
        <article class='solicitud-eliminado-card solicitud-eliminado-card--".$estado_html."'>
            <div class='solicitud-eliminado-card__main'>
                <strong>Solicitud #".$id_html."</strong>
                <span>".$motivo_html."</span>
                <small>".$tabla_html.($pk_columna_html != "" ? " - ".$pk_columna_html.": ".$pk_valor_html : "")."</small>
                <small>".$resumen_html."</small>
            </div>
            <div class='solicitud-eliminado-card__meta'>
                <span><b>Estado</b>".$estado_html."</span>
                <span><b>Solicitante</b>".$usuario_solicitud_html."</span>
                <span><b>Fecha solicitud</b>".$fecha_solicitud_html."</span>
                <span><b>Aprobacion</b>".$usuario_aprobacion_html."<small>".$fecha_aprobacion_html."</small></span>
            </div>
            ".$acciones_html."
        </article>";
    }

    if ($pagina == "") {
        $pagina = "<div class='solicitud-eliminado-empty'>No se encontraron solicitudes de eliminacion.</div>";
    }

    $stmt->close();
    responderInformeSolicitudEliminado(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros)));
}

function buscarSolicitudesEliminacionPendientes() {
    $mysqli = conectar_al_servidor();
    $totalPendientes = 0;
    $stmtTotal = $mysqli->prepare("SELECT COUNT(*) AS total FROM solicitud_eliminado WHERE estado = 'pendiente'");
    if ($stmtTotal && $stmtTotal->execute()) {
        $resultTotal = $stmtTotal->get_result();
        if ($rowTotal = $resultTotal->fetch_assoc()) {
            $totalPendientes = intval($rowTotal['total']);
        }
        $stmtTotal->close();
    }

    $sql = "SELECT
                se.id_solicitud_eliminado,
                se.id_usuario_solicitud,
                se.fecha_solicitud,
                se.tabla_nombre,
                se.registro_pk_columna,
                se.registro_pk_valor,
                se.registro_resumen,
                se.motivo,
                (SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_solicitud) AS usuario_solicitud
            FROM solicitud_eliminado se
            WHERE se.estado = 'pendiente'
            ORDER BY se.fecha_solicitud DESC, se.id_solicitud_eliminado DESC
            LIMIT 20";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo preparar la consulta.", "3" => $mysqli->error));
    }

    if (!$stmt->execute()) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo consultar las solicitudes pendientes.", "3" => $stmt->error));
    }

    $result = $stmt->get_result();
    $pagina = "";

    while ($row = $result->fetch_assoc()) {
        $reg = array();
        foreach ($row as $key => $value) {
            $reg[$key] = mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
        }

        $id_html = htmlspecialchars($reg['id_solicitud_eliminado'], ENT_QUOTES, 'UTF-8');
        $usuario_html = htmlspecialchars($reg['usuario_solicitud'], ENT_QUOTES, 'UTF-8');
        $id_usuario_html = htmlspecialchars($reg['id_usuario_solicitud'], ENT_QUOTES, 'UTF-8');
        $fecha_html = htmlspecialchars($reg['fecha_solicitud'], ENT_QUOTES, 'UTF-8');
        $motivo_html = htmlspecialchars($reg['motivo'], ENT_QUOTES, 'UTF-8');
        $tabla_html = htmlspecialchars($reg['tabla_nombre'], ENT_QUOTES, 'UTF-8');
        $pk_columna_html = htmlspecialchars($reg['registro_pk_columna'], ENT_QUOTES, 'UTF-8');
        $pk_valor_html = htmlspecialchars($reg['registro_pk_valor'], ENT_QUOTES, 'UTF-8');
        $resumen_html = htmlspecialchars($reg['registro_resumen'], ENT_QUOTES, 'UTF-8');

        if ($usuario_html == "") {
            $usuario_html = "Usuario ".$id_usuario_html;
        }

        $pagina .= "
        <button type='button' class='solicitud-eliminacion-pendiente-item' onclick='abrirVentanaEvaluarSolicitudEliminado(\"".$id_html."\")'>
            <strong>Solicitud #".$id_html."</strong>
            <span>".$motivo_html."</span>
            <span>".$tabla_html.($pk_columna_html != "" ? " - ".$pk_columna_html.": ".$pk_valor_html : "")."</span>
            <span>".$resumen_html."</span>
            <small>".$usuario_html." - ".$fecha_html."</small>
        </button>";
    }

    if ($pagina == "") {
        $pagina = "<div class='solicitud-eliminacion-pendiente-empty'>No hay solicitudes pendientes.</div>";
    }

    $stmt->close();
    responderInformeSolicitudEliminado(array("1" => "exito", "2" => $pagina, "3" => $totalPendientes));
}

function obtenerSolicitudEliminadoPorId($mysqli, $idSolicitud) {
    $sql = "SELECT
                se.*,
                (SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_solicitud) AS usuario_solicitud,
                (SELECT nombre_persona FROM persona WHERE cod_persona = se.id_usuario_aprobacion) AS usuario_aprobacion
            FROM solicitud_eliminado se
            WHERE se.id_solicitud_eliminado = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo preparar la consulta.", "3" => $mysqli->error));
    }
    $stmt->bind_param('i', $idSolicitud);
    if (!$stmt->execute()) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo consultar la solicitud.", "3" => $stmt->error));
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se encontro la solicitud."));
    }

    $reg = array();
    foreach ($row as $key => $value) {
        $reg[$key] = mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
    }

    return $reg;
}

function obtenerDetalleSolicitudEliminado() {
    $idSolicitud = isset($_POST['id_solicitud_eliminado']) ? intval($_POST['id_solicitud_eliminado']) : 0;
    if ($idSolicitud <= 0) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "Falto seleccionar la solicitud."));
    }

    $mysqli = conectar_al_servidor();
    $solicitud = obtenerSolicitudEliminadoPorId($mysqli, $idSolicitud);
    $solicitud['puede_aprobar'] = solicitudEliminadoTieneDestino($solicitud) ? '1' : '0';

    responderInformeSolicitudEliminado(array("1" => "exito", "2" => $solicitud));
}

function crearSolicitudEliminado($codUsuarioSolicitud) {
    $tabla = isset($_POST['tabla_nombre']) ? mb_convert_encoding((string)($_POST['tabla_nombre']), 'ISO-8859-1', 'UTF-8') : '';
    $pkColumna = isset($_POST['registro_pk_columna']) ? mb_convert_encoding((string)($_POST['registro_pk_columna']), 'ISO-8859-1', 'UTF-8') : '';
    $pkValor = isset($_POST['registro_pk_valor']) ? mb_convert_encoding((string)($_POST['registro_pk_valor']), 'ISO-8859-1', 'UTF-8') : '';
    $resumen = isset($_POST['registro_resumen']) ? mb_convert_encoding((string)($_POST['registro_resumen']), 'ISO-8859-1', 'UTF-8') : '';
    $motivo = isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : '';

    if ($tabla == "" || $pkColumna == "" || $pkValor == "" || $motivo == "") {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "Faltan datos para solicitar la eliminacion."));
    }
    if (!validarIdentificadorSqlSolicitudEliminado($tabla) || !validarIdentificadorSqlSolicitudEliminado($pkColumna)) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "La tabla o columna del registro no es valida."));
    }

    $mysqli = conectar_al_servidor();
    $columnaPk = obtenerColumnaTablaSolicitudEliminado($mysqli, $tabla, $pkColumna);
    if (!$columnaPk) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se encontro la tabla o columna del registro."));
    }
    $columnaEstado = obtenerColumnaTablaSolicitudEliminado($mysqli, $tabla, 'estado');
    if (!$columnaEstado) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "La tabla ".$tabla." no tiene columna estado."));
    }

    $sql = "INSERT INTO solicitud_eliminado
            (id_usuario_solicitud, tabla_nombre, registro_pk_columna, registro_pk_valor, registro_resumen, motivo)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo preparar la solicitud.", "3" => $mysqli->error));
    }

    $stmt->bind_param('isssss', $codUsuarioSolicitud, $tabla, $pkColumna, $pkValor, $resumen, $motivo);
    if (!$stmt->execute()) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo registrar la solicitud.", "3" => $stmt->error));
    }

    $idSolicitud = $stmt->insert_id;
    $stmt->close();
    responderInformeSolicitudEliminado(array("1" => "exito", "2" => "Solicitud de eliminacion registrada.", "3" => $idSolicitud));
}

function solicitudEliminadoTieneDestino($solicitud) {
    return isset($solicitud['tabla_nombre'], $solicitud['registro_pk_columna'], $solicitud['registro_pk_valor'])
        && $solicitud['tabla_nombre'] !== ''
        && $solicitud['registro_pk_columna'] !== ''
        && $solicitud['registro_pk_valor'] !== '';
}

function validarIdentificadorSqlSolicitudEliminado($identificador) {
    return preg_match('/^[A-Za-z0-9_]+$/', $identificador);
}

function obtenerColumnaTablaSolicitudEliminado($mysqli, $tabla, $columna) {
    $sql = "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo validar la tabla.", "3" => $mysqli->error));
    }
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

function valorInactivoParaColumnaSolicitudEliminado($columnaEstado) {
    $tipo = strtolower((string)$columnaEstado['DATA_TYPE']);
    $columnType = (string)$columnaEstado['COLUMN_TYPE'];

    if (in_array($tipo, array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'bit'))) {
        return '0';
    }

    if ($tipo == 'enum') {
        if (strpos($columnType, "'Inactivo'") !== false) {
            return 'Inactivo';
        }
        if (strpos($columnType, "'inactivo'") !== false) {
            return 'inactivo';
        }
        if (strpos($columnType, "'INACTIVO'") !== false) {
            return 'INACTIVO';
        }
        return null;
    }

    return 'Inactivo';
}

function inactivarRegistroSolicitudEliminado($mysqli, $solicitud) {
    if (!solicitudEliminadoTieneDestino($solicitud)) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "La solicitud no tiene tabla, columna o codigo del registro a eliminar. No se puede aprobar."));
    }

    $tabla = $solicitud['tabla_nombre'];
    $pkColumna = $solicitud['registro_pk_columna'];
    $pkValor = $solicitud['registro_pk_valor'];

    if (!validarIdentificadorSqlSolicitudEliminado($tabla) || !validarIdentificadorSqlSolicitudEliminado($pkColumna)) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "La tabla o columna del registro no es valida."));
    }

    $columnaPk = obtenerColumnaTablaSolicitudEliminado($mysqli, $tabla, $pkColumna);
    if (!$columnaPk) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se encontro la tabla o la columna principal del registro."));
    }

    $columnaEstado = obtenerColumnaTablaSolicitudEliminado($mysqli, $tabla, 'estado');
    if (!$columnaEstado) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "La tabla ".$tabla." no tiene columna estado para inactivar el registro."));
    }

    $valorInactivo = valorInactivoParaColumnaSolicitudEliminado($columnaEstado);
    if ($valorInactivo === null) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "La columna estado de ".$tabla." no admite el valor Inactivo."));
    }
    $sql = "UPDATE `".$tabla."` SET `estado` = ? WHERE `".$pkColumna."` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo preparar la inactivacion del registro.", "3" => $mysqli->error));
    }

    $stmt->bind_param('ss', $valorInactivo, $pkValor);
    if (!$stmt->execute()) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo inactivar el registro solicitado.", "3" => $stmt->error));
    }

    $filas = $stmt->affected_rows;
    $stmt->close();

    if ($filas < 1) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se encontro el registro indicado o ya estaba inactivo."));
    }
}

function resolverSolicitudEliminado($decision, $codUsuarioAprobacion) {
    $idSolicitud = isset($_POST['id_solicitud_eliminado']) ? intval($_POST['id_solicitud_eliminado']) : 0;
    $observacion = isset($_POST['observacion']) ? mb_convert_encoding((string)($_POST['observacion']), 'ISO-8859-1', 'UTF-8') : '';

    if ($idSolicitud <= 0) {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "Falto seleccionar la solicitud."));
    }

    $mysqli = conectar_al_servidor();
    $solicitud = obtenerSolicitudEliminadoPorId($mysqli, $idSolicitud);
    if ($solicitud['estado'] != 'pendiente') {
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "Esta solicitud ya fue ".$solicitud['estado']."."));
    }

    $mysqli->begin_transaction();

    if ($decision == 'aprobada') {
        inactivarRegistroSolicitudEliminado($mysqli, $solicitud);
    }

    $fechaActual = date('Y-m-d H:i:s');
    $sql = "UPDATE solicitud_eliminado
            SET estado = ?, fecha_aprobacion = ?, id_usuario_aprobacion = ?, observacion_aprobacion = ?
            WHERE id_solicitud_eliminado = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $mysqli->rollback();
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo preparar la resolucion.", "3" => $mysqli->error));
    }

    $stmt->bind_param('ssisi', $decision, $fechaActual, $codUsuarioAprobacion, $observacion, $idSolicitud);
    if (!$stmt->execute()) {
        $mysqli->rollback();
        responderInformeSolicitudEliminado(array("1" => "error", "2" => "No se pudo actualizar la solicitud.", "3" => $stmt->error));
    }
    $stmt->close();
    $mysqli->commit();

    responderInformeSolicitudEliminado(array("1" => "exito", "2" => "Solicitud ".$decision." correctamente."));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? mb_convert_encoding((string)($_POST['accion']), 'ISO-8859-1', 'UTF-8') : '';
    verificarInformeSolicitudEliminado($accion);
}
?>

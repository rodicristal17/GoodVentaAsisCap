<?php
require_once("conexion.php");
include_once("verificar_navegador.php");

function responderLogInactivos($datos) {
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function verificarLogInactivos($accion) {
    $user = isset($_POST['useru']) ? mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8') : '';
    $pass = isset($_POST['passu']) ? $_POST['passu'] : '';
    $pass = str_replace("=", "+", $pass);
    $navegador = isset($_POST['navegador']) ? mb_convert_encoding((string)($_POST['navegador']), 'ISO-8859-1', 'UTF-8') : '';

    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != "ok") {
        responderLogInactivos(array("1" => "UI"));
    }

    switch ($accion) {
        case 'buscar':
            buscarLogRegistrosInactivos();
            break;
        default:
            responderLogInactivos(array("1" => "error", "2" => "$accion NO IMPLEMENTADA."));
            break;
    }
}

function buscarLogRegistrosInactivos() {
    $tabla = isset($_POST['tabla']) ? mb_convert_encoding((string)($_POST['tabla']), 'ISO-8859-1', 'UTF-8') : '';
    $registro = isset($_POST['registro']) ? mb_convert_encoding((string)($_POST['registro']), 'ISO-8859-1', 'UTF-8') : '';
    $usuario = isset($_POST['usuario']) ? mb_convert_encoding((string)($_POST['usuario']), 'ISO-8859-1', 'UTF-8') : '';
    $fecha_desde = isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : '';
    $fecha_hasta = isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : '';

    $where = array();
    $tipos = "";
    $params = array();

    if ($tabla !== "") {
        $where[] = "tabla_nombre LIKE ?";
        $tipos .= "s";
        $params[] = "%".$tabla."%";
    }
    if ($registro !== "") {
        $where[] = "(registro_pk_valor LIKE ? OR registro_resumen LIKE ?)";
        $tipos .= "ss";
        $params[] = "%".$registro."%";
        $params[] = "%".$registro."%";
    }
    if ($usuario !== "") {
        $where[] = "(nombre_usuario_accion LIKE ? OR cod_usuario_accion = ?)";
        $tipos .= "ss";
        $params[] = "%".$usuario."%";
        $params[] = $usuario;
    }
    if ($fecha_desde !== "") {
        $where[] = "DATE(fecha_accion) >= ?";
        $tipos .= "s";
        $params[] = $fecha_desde;
    }
    if ($fecha_hasta !== "") {
        $where[] = "DATE(fecha_accion) <= ?";
        $tipos .= "s";
        $params[] = $fecha_hasta;
    }

    $sqlWhere = count($where) > 0 ? "WHERE ".implode(" AND ", $where) : "";
    $sql = "SELECT * FROM log_registros_inactivos $sqlWhere ORDER BY fecha_accion DESC, id DESC LIMIT 500";

    $mysqli = conectar_al_servidor();
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        responderLogInactivos(array("1" => "error", "2" => "No se pudo preparar la consulta.", "sql" => $sql, "3" => $mysqli->error));
    }

    if (count($params) > 0) {
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($tipos), $refs));
    }

    if (!$stmt->execute()) {
        responderLogInactivos(array("1" => "error", "2" => "No se pudo consultar el log.", "sql" => $sql, "3" => $stmt->error));
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

        $tabla_html = htmlspecialchars($reg['tabla_nombre'], ENT_QUOTES, 'UTF-8');
        $pk_columna_html = htmlspecialchars($reg['registro_pk_columna'], ENT_QUOTES, 'UTF-8');
        $pk_valor_html = htmlspecialchars($reg['registro_pk_valor'], ENT_QUOTES, 'UTF-8');
        $resumen_html = htmlspecialchars($reg['registro_resumen'], ENT_QUOTES, 'UTF-8');
        $usuario_html = htmlspecialchars($reg['nombre_usuario_accion'], ENT_QUOTES, 'UTF-8');
        $cod_usuario_html = htmlspecialchars($reg['cod_usuario_accion'], ENT_QUOTES, 'UTF-8');
        $fecha_html = htmlspecialchars($reg['fecha_accion'], ENT_QUOTES, 'UTF-8');
        $estado_anterior_html = htmlspecialchars($reg['estado_anterior'], ENT_QUOTES, 'UTF-8');
        $usuario_bd_html = htmlspecialchars($reg['usuario_bd'], ENT_QUOTES, 'UTF-8');

        if ($usuario_html == "") {
            $usuario_html = "No registrado";
        }

        $pagina .= "
        <article class='log-inactivo-card'>
            <div class='log-inactivo-card__main'>
                <strong>".$tabla_html."</strong>
                <span>".$pk_columna_html.": ".$pk_valor_html."</span>
                <small>".$resumen_html."</small>
            </div>
            <div class='log-inactivo-card__meta'>
                <span><b>Usuario</b>".$usuario_html.($cod_usuario_html !== "" ? " (".$cod_usuario_html.")" : "")."</span>
                <span><b>Fecha</b>".$fecha_html."</span>
                <span><b>Estado anterior</b>".$estado_anterior_html."</span>
                <span><b>Usuario BD</b>".$usuario_bd_html."</span>
            </div>
        </article>";
    }

    if ($pagina == "") {
        $pagina = "<div class='log-inactivo-empty'>No se encontraron registros inactivados.</div>";
    }

    $stmt->close();
    responderLogInactivos(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? mb_convert_encoding((string)($_POST['accion']), 'ISO-8859-1', 'UTF-8') : '';
    verificarLogInactivos($accion);
}
?>

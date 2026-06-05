<?php
require_once("conexion.php");

if (!function_exists('registrarSolicitudEliminacionGenerica')) {

function solicitudEliminadoIdentificadorValido($valor) {
    return preg_match('/^[A-Za-z0-9_]+$/', (string)$valor) === 1;
}

function solicitudEliminadoValorPost($nombre, $defecto) {
    if (isset($_POST[$nombre])) {
        return mb_convert_encoding((string)$_POST[$nombre], 'ISO-8859-1', 'UTF-8');
    }
    return $defecto;
}

function solicitudEliminadoEsEstadoInactivo($estado) {
    $estado = strtolower(trim((string)$estado));
    return $estado === 'inactivo' || $estado === '0';
}

function solicitudEliminadoColumnaExiste($mysqli, $tabla, $columna) {
    if (!solicitudEliminadoIdentificadorValido($tabla) || !solicitudEliminadoIdentificadorValido($columna)) {
        return false;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return isset($row['total']) && (int)$row['total'] > 0;
}

function solicitudEliminadoTablaExiste($mysqli, $tabla) {
    if (!solicitudEliminadoIdentificadorValido($tabla)) {
        return false;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $tabla);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return isset($row['total']) && (int)$row['total'] > 0;
}

function solicitudEliminadoObtenerResumen($mysqli, $tabla, $pkColumna, $pkValor) {
    if (!solicitudEliminadoIdentificadorValido($tabla) || !solicitudEliminadoIdentificadorValido($pkColumna)) {
        return '';
    }

    $sql = "SELECT * FROM `".$tabla."` WHERE `".$pkColumna."` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param('s', $pkValor);
    if (!$stmt->execute()) {
        $stmt->close();
        return '';
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return '';
    }

    $partes = array();
    foreach ($row as $columna => $valor) {
        if ($valor === null || $valor === '') {
            continue;
        }
        $texto = trim(strip_tags((string)$valor));
        if ($texto === '' || strlen($texto) > 120) {
            continue;
        }
        $partes[] = $columna.': '.$texto;
        if (count($partes) >= 6) {
            break;
        }
    }

    return implode(' | ', $partes);
}

function registrarSolicitudEliminacionGenerica($tabla, $pkColumna, $pkValor, $motivo, $codUsuario, $registroResumen, $estadoColumna = 'estado') {
    $tabla = trim((string)$tabla);
    $pkColumna = trim((string)$pkColumna);
    $pkValor = trim((string)$pkValor);
    $motivo = trim((string)$motivo);
    $codUsuario = trim((string)$codUsuario);
    $registroResumen = trim((string)$registroResumen);
    $estadoColumna = trim((string)$estadoColumna);
    if ($estadoColumna === '') {
        $estadoColumna = 'estado';
    }

    if ($tabla === '' || $pkColumna === '' || $pkValor === '') {
        return array("1" => "error", "2" => "No se recibio el registro a eliminar.");
    }
    if (!solicitudEliminadoIdentificadorValido($tabla) || !solicitudEliminadoIdentificadorValido($pkColumna) || !solicitudEliminadoIdentificadorValido($estadoColumna)) {
        return array("1" => "error", "2" => "La tabla o columna indicada no es valida.");
    }
    if ($motivo === '') {
        $motivo = "Solicitud automatica de eliminacion.";
    }
    if ($codUsuario === '') {
        $codUsuario = solicitudEliminadoValorPost('useru', '0');
    }

    $mysqli = conectar_al_servidor();
    if (!$mysqli) {
        return array("1" => "error", "2" => "No se pudo conectar a la base de datos.");
    }

    if (!solicitudEliminadoColumnaExiste($mysqli, $tabla, $pkColumna)) {
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo validar la tabla o la columna principal.");
    }
    if (!solicitudEliminadoColumnaExiste($mysqli, $tabla, $estadoColumna)) {
        if ($estadoColumna === 'estado' && solicitudEliminadoColumnaExiste($mysqli, $tabla, 'Esado')) {
            $estadoColumna = 'Esado';
        } else {
            mysqli_close($mysqli);
            return array("1" => "error", "2" => "No se pudo validar la columna de estado.");
        }
    }

    $sql = "SELECT id_solicitud_eliminado
            FROM solicitud_eliminado
            WHERE estado = 'pendiente'
              AND tabla_nombre = ?
              AND registro_pk_columna = ?
              AND registro_pk_valor = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo preparar la validacion de solicitud pendiente: ".$error);
    }
    $stmt->bind_param('sss', $tabla, $pkColumna, $pkValor);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo validar solicitudes pendientes: ".$error);
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($row) {
        $idSolicitud = $row['id_solicitud_eliminado'];
        mysqli_close($mysqli);
        return array("1" => "exito", "2" => "Ya existe una solicitud de eliminacion pendiente.", "3" => $idSolicitud);
    }

    if ($registroResumen === '') {
        $registroResumen = solicitudEliminadoObtenerResumen($mysqli, $tabla, $pkColumna, $pkValor);
    }

    $tieneColumnaEstadoSolicitud = solicitudEliminadoColumnaExiste($mysqli, 'solicitud_eliminado', 'estado_columna');
    if ($tieneColumnaEstadoSolicitud) {
        $sql = "INSERT INTO solicitud_eliminado (
                    id_usuario_solicitud, motivo, tabla_nombre,
                    registro_pk_columna, registro_pk_valor, registro_resumen, estado_columna
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    } else {
        $sql = "INSERT INTO solicitud_eliminado (
                    id_usuario_solicitud, motivo, tabla_nombre,
                    registro_pk_columna, registro_pk_valor, registro_resumen
                ) VALUES (?, ?, ?, ?, ?, ?)";
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo preparar la solicitud de eliminacion: ".$error);
    }
    if ($tieneColumnaEstadoSolicitud) {
        $stmt->bind_param('issssss', $codUsuario, $motivo, $tabla, $pkColumna, $pkValor, $registroResumen, $estadoColumna);
    } else {
        $stmt->bind_param('isssss', $codUsuario, $motivo, $tabla, $pkColumna, $pkValor, $registroResumen);
    }
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo registrar la solicitud de eliminacion: ".$error);
    }

    $idSolicitud = $stmt->insert_id;
    $stmt->close();
    mysqli_close($mysqli);

    return array("1" => "exito", "2" => "Solicitud de eliminacion registrada.", "3" => $idSolicitud);
}

function registrarDetalleSolicitudEliminacionGenerica($idSolicitud, $tabla, $pkColumna, $pkValor, $registroResumen, $estadoColumna = 'estado', $requiereInactivacion = 1) {
    $idSolicitud = intval($idSolicitud);
    $tabla = trim((string)$tabla);
    $pkColumna = trim((string)$pkColumna);
    $pkValor = trim((string)$pkValor);
    $registroResumen = trim((string)$registroResumen);
    $estadoColumna = trim((string)$estadoColumna);
    $requiereInactivacion = intval($requiereInactivacion) == 1 ? 1 : 0;

    if ($idSolicitud <= 0 || $tabla === '' || $pkColumna === '' || $pkValor === '') {
        return array("1" => "error", "2" => "Faltan datos del registro relacionado.");
    }
    if (!solicitudEliminadoIdentificadorValido($tabla) || !solicitudEliminadoIdentificadorValido($pkColumna)) {
        return array("1" => "error", "2" => "La tabla o columna relacionada no es valida.");
    }
    if ($requiereInactivacion == 1 && ($estadoColumna === '' || !solicitudEliminadoIdentificadorValido($estadoColumna))) {
        return array("1" => "error", "2" => "La columna de estado relacionada no es valida.");
    }

    $mysqli = conectar_al_servidor();
    if (!$mysqli) {
        return array("1" => "error", "2" => "No se pudo conectar a la base de datos.");
    }

    if (!solicitudEliminadoTablaExiste($mysqli, 'solicitud_eliminado_detalle')) {
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "Falta crear la tabla solicitud_eliminado_detalle.");
    }
    if (!solicitudEliminadoColumnaExiste($mysqli, $tabla, $pkColumna)) {
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo validar el registro relacionado.");
    }
    if ($requiereInactivacion == 1 && !solicitudEliminadoColumnaExiste($mysqli, $tabla, $estadoColumna)) {
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo validar la columna de estado relacionada.");
    }
    if ($registroResumen === '') {
        $registroResumen = solicitudEliminadoObtenerResumen($mysqli, $tabla, $pkColumna, $pkValor);
    }

    $sql = "SELECT id_solicitud_eliminado_detalle
            FROM solicitud_eliminado_detalle
            WHERE id_solicitud_eliminado = ?
              AND tabla_nombre = ?
              AND registro_pk_columna = ?
              AND registro_pk_valor = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo preparar la validacion del relacionado: ".$error);
    }
    $stmt->bind_param('isss', $idSolicitud, $tabla, $pkColumna, $pkValor);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo validar el relacionado: ".$error);
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if ($row) {
        $idDetalle = $row['id_solicitud_eliminado_detalle'];
        mysqli_close($mysqli);
        return array("1" => "exito", "2" => "El registro relacionado ya estaba incluido.", "3" => $idDetalle);
    }

    $sql = "INSERT INTO solicitud_eliminado_detalle (
                id_solicitud_eliminado, tabla_nombre, registro_pk_columna,
                registro_pk_valor, estado_columna, registro_resumen, requiere_inactivacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $error = $mysqli->error;
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo preparar el registro relacionado: ".$error);
    }
    $stmt->bind_param('isssssi', $idSolicitud, $tabla, $pkColumna, $pkValor, $estadoColumna, $registroResumen, $requiereInactivacion);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        mysqli_close($mysqli);
        return array("1" => "error", "2" => "No se pudo registrar el relacionado: ".$error);
    }
    $idDetalle = $stmt->insert_id;
    $stmt->close();
    mysqli_close($mysqli);

    return array("1" => "exito", "2" => "Registro relacionado incluido.", "3" => $idDetalle);
}

function responderSolicitudEliminacionGenerica($respuesta) {
    echo json_encode($respuesta);
    exit;
}

}
?>

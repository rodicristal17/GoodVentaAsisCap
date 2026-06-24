<?php
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("producto_riesgo_financiero_helper.php");

date_default_timezone_set('America/Asuncion');

function odontoPost($key, $default = "")
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    return mb_convert_encoding((string)$_POST[$key], 'ISO-8859-1', 'UTF-8');
}

function odontoResponder($estado, $extra = array())
{
    $respuesta = array("1" => $estado);
    foreach ($extra as $key => $value) {
        $respuesta[$key] = $value;
    }
    echo json_encode($respuesta);
    exit;
}

function odontoVerificarSesion()
{
    $user = odontoPost("useru");
    $pass = str_replace("=", "+", odontoPost("passu"));
    $navegador = odontoPost("navegador");
    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != "ok") {
        odontoResponder("UI");
    }
    return $user;
}

function odontoColumnaExiste($mysqli, $tabla, $columna)
{
    $sql = "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ss", $tabla, $columna);
    if (!$stmt->execute()) {
        return false;
    }
    $row = $stmt->get_result()->fetch_assoc();
    return isset($row["total"]) && (int)$row["total"] > 0;
}

function odontoTablasDisponibles($mysqli)
{
    $tablas = array("odontogramas", "odontograma_marcas", "odontograma_tratamiento_links", "odontograma_historial");
    foreach ($tablas as $tabla) {
        $tablaSql = $mysqli->real_escape_string($tabla);
        $res = $mysqli->query("SHOW TABLES LIKE '".$tablaSql."'");
        if (!$res || $res->num_rows == 0) {
            return false;
        }
    }
    return odontoColumnaExiste($mysqli, "producto", "alcance_odontologico");
}

function odontoUtf8($valor)
{
    return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function odontoNormalizarAlcance($alcance)
{
    $alcance = strtolower(trim((string)$alcance));
    $permitidos = array("no_requiere", "boca_completa", "arcada", "cuadrante", "pieza_dental", "pieza_superficie");
    return in_array($alcance, $permitidos) ? $alcance : "pieza_dental";
}

function odontoTextoSuperficie($superficie)
{
    $mapa = array(
        "mesial" => "Mesial",
        "distal" => "Distal",
        "vestibular" => "Vestibular",
        "lingual_palatina" => "Lingual / Palatina",
        "oclusal_incisal" => "Oclusal / Incisal"
    );
    return isset($mapa[$superficie]) ? $mapa[$superficie] : $superficie;
}

function odontoNormalizarArcadaTexto($arcada)
{
    $arcada = strtolower(str_replace(" ", "_", trim((string)$arcada)));
    if ($arcada == "superior_e_inferior" || $arcada == "superior_inferior" || $arcada == "ambas_arcadas") {
        return "ambas";
    }
    return $arcada;
}

function odontoTextoArcada($arcada)
{
    $arcada = odontoNormalizarArcadaTexto($arcada);
    if ($arcada == "ambas") {
        return "Arcada superior e inferior";
    }
    if ($arcada == "superior") {
        return "Arcada superior";
    }
    if ($arcada == "inferior") {
        return "Arcada inferior";
    }
    return "Arcada ".str_replace("_", " ", $arcada);
}

function odontoUbicacionTexto($link)
{
    if (!empty($link["boca_completa"])) {
        return "Boca completa";
    }
    if (!empty($link["arcada"])) {
        return odontoTextoArcada($link["arcada"]);
    }
    if (!empty($link["cuadrante"])) {
        return "Cuadrante ".str_replace("_", " ", $link["cuadrante"]);
    }
    $texto = "";
    if (!empty($link["pieza"])) {
        $texto = "Pieza ".$link["pieza"];
    }
    $superficies = array();
    if (!empty($link["superficies_json"])) {
        $decodificado = json_decode($link["superficies_json"], true);
        if (is_array($decodificado)) {
            foreach ($decodificado as $sup) {
                $superficies[] = odontoTextoSuperficie($sup);
            }
        }
    }
    if (count($superficies) > 0) {
        $texto .= " - ".implode(", ", $superficies);
    }
    return trim($texto) != "" ? $texto : "Falta ubicar";
}

function odontoObtenerContexto($mysqli, $pacienteId, $cedula, $ventaId, $presupuestoId)
{
    $ctx = array(
        "paciente_id" => trim((string)$pacienteId),
        "cedula" => trim((string)$cedula),
        "paciente_nombre" => "",
        "venta_id" => trim((string)$ventaId),
        "presupuesto_id" => trim((string)$presupuestoId)
    );

    if ($ctx["paciente_id"] == "" && $ctx["venta_id"] != "") {
        $stmt = $mysqli->prepare("SELECT vt.cod_clienteFK, cl.ci_cliente, COALESCE(vt.apodo, p.nombre_persona) AS paciente_nombre FROM venta vt INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK INNER JOIN persona p ON p.cod_persona = cl.cod_cliente WHERE vt.cod_venta = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $ctx["venta_id"]);
            if ($stmt->execute()) {
                $row = $stmt->get_result()->fetch_assoc();
                if ($row) {
                    $ctx["paciente_id"] = (string)$row["cod_clienteFK"];
                    $ctx["cedula"] = (string)$row["ci_cliente"];
                    $ctx["paciente_nombre"] = (string)$row["paciente_nombre"];
                }
            }
        }
    }

    if ($ctx["paciente_id"] == "" && $ctx["presupuesto_id"] != "") {
        $stmt = $mysqli->prepare("SELECT pr.cod_clienteFK, cl.ci_cliente, p.nombre_persona AS paciente_nombre FROM presupuesto pr INNER JOIN cliente cl ON cl.cod_cliente = pr.cod_clienteFK INNER JOIN persona p ON p.cod_persona = cl.cod_cliente WHERE pr.id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $ctx["presupuesto_id"]);
            if ($stmt->execute()) {
                $row = $stmt->get_result()->fetch_assoc();
                if ($row) {
                    $ctx["paciente_id"] = (string)$row["cod_clienteFK"];
                    $ctx["cedula"] = (string)$row["ci_cliente"];
                    $ctx["paciente_nombre"] = (string)$row["paciente_nombre"];
                }
            }
        }
    }

    if ($ctx["paciente_id"] != "" && $ctx["paciente_nombre"] == "") {
        $stmt = $mysqli->prepare("SELECT cl.ci_cliente, p.nombre_persona AS paciente_nombre FROM cliente cl INNER JOIN persona p ON p.cod_persona = cl.cod_cliente WHERE cl.cod_cliente = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $ctx["paciente_id"]);
            if ($stmt->execute()) {
                $row = $stmt->get_result()->fetch_assoc();
                if ($row) {
                    if ($ctx["cedula"] == "") {
                        $ctx["cedula"] = (string)$row["ci_cliente"];
                    }
                    $ctx["paciente_nombre"] = (string)$row["paciente_nombre"];
                }
            }
        }
    }

    if ($ctx["paciente_id"] == "" && $ctx["cedula"] != "") {
        $stmt = $mysqli->prepare("SELECT cl.cod_cliente, cl.ci_cliente, p.nombre_persona AS paciente_nombre FROM cliente cl INNER JOIN persona p ON p.cod_persona = cl.cod_cliente WHERE cl.ci_cliente = ? AND cl.estado = 'Activo' ORDER BY cl.cod_cliente DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $ctx["cedula"]);
            if ($stmt->execute()) {
                $row = $stmt->get_result()->fetch_assoc();
                if ($row) {
                    $ctx["paciente_id"] = (string)$row["cod_cliente"];
                    $ctx["cedula"] = (string)$row["ci_cliente"];
                    $ctx["paciente_nombre"] = (string)$row["paciente_nombre"];
                }
            }
        }
    }

    return $ctx;
}

function odontoObtenerOCrear($mysqli, $ctx, $user)
{
    if ($ctx["paciente_id"] == "" && $ctx["cedula"] == "") {
        return null;
    }

    if ($ctx["paciente_id"] != "") {
        $stmt = $mysqli->prepare("SELECT * FROM odontogramas WHERE paciente_id = ? AND activo = 1 ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("s", $ctx["paciente_id"]);
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM odontogramas WHERE cedula = ? AND activo = 1 ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("s", $ctx["cedula"]);
    }
    if ($stmt && $stmt->execute()) {
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            return $row;
        }
    }

    $estado = "borrador";
    $denticion = "permanente";
    $activo = 1;
    $stmt = $mysqli->prepare("INSERT INTO odontogramas (cedula, paciente_id, paciente_nombre_snapshot, venta_base_id, presupuesto_id, denticion, estado, creado_por, actualizado_por, activo) VALUES (?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) {
        return null;
    }
    $pacienteId = $ctx["paciente_id"] != "" ? $ctx["paciente_id"] : null;
    $ventaId = $ctx["venta_id"] != "" ? $ctx["venta_id"] : null;
    $presupuestoId = $ctx["presupuesto_id"] != "" ? $ctx["presupuesto_id"] : null;
    $stmt->bind_param("sssssssiii", $ctx["cedula"], $pacienteId, $ctx["paciente_nombre"], $ventaId, $presupuestoId, $denticion, $estado, $user, $user, $activo);
    if (!$stmt->execute()) {
        return null;
    }
    $id = $stmt->insert_id;
    odontoRegistrarHistorial($mysqli, $id, 1, "crear_odontograma", "Se creo el odontograma clinico.", null, null, null, null, null, null, null, null, null, null, $user);
    $stmt = $mysqli->prepare("SELECT * FROM odontogramas WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function odontoRegistrarHistorial($mysqli, $odontogramaId, $version, $accion, $descripcion, $pieza, $superficie, $marcaId, $linkId, $tratamientoId, $ventaId, $detalleVentaId, $presupuestoId, $presupuestoItemId, $motivo, $user, $valorAnterior = null, $valorNuevo = null)
{
    $stmt = $mysqli->prepare("INSERT INTO odontograma_historial (odontograma_id, version, accion, descripcion, pieza, superficie, marca_id, link_id, tratamiento_id, venta_id, detalle_venta_id, presupuesto_id, presupuesto_item_id, valor_anterior, valor_nuevo, motivo, usuario_id, fecha_hora) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param(
        "iissssiissiiisssi",
        $odontogramaId,
        $version,
        $accion,
        $descripcion,
        $pieza,
        $superficie,
        $marcaId,
        $linkId,
        $tratamientoId,
        $ventaId,
        $detalleVentaId,
        $presupuestoId,
        $presupuestoItemId,
        $valorAnterior,
        $valorNuevo,
        $motivo,
        $user
    );
    return $stmt->execute();
}

function odontoPrepararModificacion($mysqli, &$odontograma, $user, $motivo)
{
    if (!$odontograma) {
        odontoResponder("error", array("mensaje" => "No se encontro odontograma."));
    }
    if ($odontograma["estado"] == "convalidado" && trim((string)$motivo) == "") {
        odontoResponder("requiere_motivo", array("mensaje" => "El odontograma esta convalidado. Ingrese motivo de modificacion."));
    }
    if ($odontograma["estado"] == "convalidado") {
        $stmt = $mysqli->prepare("UPDATE odontogramas SET estado = 'modificado', version_actual = version_actual + 1, actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? LIMIT 1");
        $stmt->bind_param("ii", $user, $odontograma["id"]);
        $stmt->execute();
        $odontograma["estado"] = "modificado";
        $odontograma["version_actual"] = (int)$odontograma["version_actual"] + 1;
        odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "modificar_convalidado", "Se modifico un odontograma convalidado.", null, null, null, null, null, null, null, null, null, $motivo, $user);
        return;
    }
    $stmt = $mysqli->prepare("UPDATE odontogramas SET actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("ii", $user, $odontograma["id"]);
        $stmt->execute();
    }
}

function odontoObtenerRiesgoProducto($mysqli, $productoId)
{
    if ($productoId == "") {
        return null;
    }
    $select = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
    $stmt = $mysqli->prepare("SELECT ".$select." FROM producto pr WHERE pr.cod_producto = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("s", $productoId);
    if (!$stmt->execute()) {
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    return $row && isset($row["nivel_riesgo_financiero"]) ? (int)$row["nivel_riesgo_financiero"] : null;
}

function odontoObtenerAlcanceProducto($mysqli, $productoId)
{
    $stmt = $mysqli->prepare("SELECT cod_producto, nombre_producto, alcance_odontologico FROM producto WHERE cod_producto = ? LIMIT 1");
    if (!$stmt) {
        odontoResponder("error", array("mensaje" => "No se pudo consultar el tratamiento."));
    }
    $stmt->bind_param("s", $productoId);
    if (!$stmt->execute()) {
        odontoResponder("error", array("mensaje" => "No se pudo consultar el tratamiento."));
    }
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        odontoResponder("error", array("mensaje" => "Tratamiento no encontrado."));
    }
    odontoResponder("exito", array(
        "producto" => array(
            "cod_producto" => odontoUtf8($row["cod_producto"]),
            "nombre_producto" => odontoUtf8($row["nombre_producto"]),
            "alcance_odontologico" => odontoNormalizarAlcance($row["alcance_odontologico"]),
            "nivel_riesgo_financiero" => odontoObtenerRiesgoProducto($mysqli, $row["cod_producto"])
        )
    ));
}

function odontoNormalizarSuperficies($superficie, $superficiesJson)
{
    $permitidas = array("mesial", "distal", "vestibular", "lingual_palatina", "oclusal_incisal");
    $lista = array();
    if ($superficiesJson != "") {
        $dec = json_decode($superficiesJson, true);
        if (is_array($dec)) {
            foreach ($dec as $sup) {
                $sup = strtolower(trim((string)$sup));
                if (in_array($sup, $permitidas) && !in_array($sup, $lista)) {
                    $lista[] = $sup;
                }
            }
        }
    }
    $superficie = strtolower(trim((string)$superficie));
    if ($superficie != "" && in_array($superficie, $permitidas) && !in_array($superficie, $lista)) {
        $lista[] = $superficie;
    }
    return count($lista) ? json_encode($lista) : null;
}

function odontoDatosTratamientoDesdeDetalle($mysqli, $detalleId)
{
    if ($detalleId == "") {
        return null;
    }
    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
    $stmt = $mysqli->prepare("SELECT dtv.cod_detalle, dtv.cod_ventaFK, dtv.cod_productoFK, dtv.estado_tratamiento, dtv.progreso_porcentaje, pr.nombre_producto, pr.alcance_odontologico, ".$selectRiesgo." FROM detalle_venta dtv INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK WHERE dtv.cod_detalle = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("s", $detalleId);
    if (!$stmt->execute()) {
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row : null;
}

function odontoDatosTratamientoDesdePresupuestoItem($mysqli, $itemId)
{
    if ($itemId == "") {
        return null;
    }
    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
    $stmt = $mysqli->prepare("SELECT dp.id, dp.cod_presupuestoFK, dp.cod_productoFK, pr.nombre_producto, pr.alcance_odontologico, ".$selectRiesgo." FROM detalles_presupuesto dp INNER JOIN producto pr ON pr.cod_producto = dp.cod_productoFK WHERE dp.id = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("s", $itemId);
    if (!$stmt->execute()) {
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row : null;
}

function odontoGuardarLink($mysqli, $ctx, $odontograma, $user)
{
    $motivo = odontoPost("motivo");
    odontoPrepararModificacion($mysqli, $odontograma, $user, $motivo);

    $detalleId = trim(odontoPost("detalle_venta_id"));
    $presupuestoItemId = trim(odontoPost("presupuesto_item_id"));
    $presupuestoId = trim(odontoPost("presupuesto_id", $ctx["presupuesto_id"]));
    $ventaId = trim(odontoPost("venta_id", $ctx["venta_id"]));
    $productoId = trim(odontoPost("producto_id"));
    $nombre = trim(odontoPost("nombre_tratamiento"));
    $alcance = odontoNormalizarAlcance(odontoPost("alcance_odontologico", "pieza_dental"));
    $pieza = trim(odontoPost("pieza"));
    $denticion = trim(odontoPost("denticion", "permanente"));
    $superficiesJson = odontoNormalizarSuperficies(odontoPost("superficie"), odontoPost("superficies_json"));
    $arcada = trim(odontoPost("arcada"));
    $cuadrante = trim(odontoPost("cuadrante"));
    $bocaCompleta = odontoPost("boca_completa") == "1" ? 1 : 0;
    $origen = trim(odontoPost("origen", "ficha_clinica"));
    $estadoLink = "pendiente";
    $riesgo = null;

    if ($detalleId != "") {
        $datos = odontoDatosTratamientoDesdeDetalle($mysqli, $detalleId);
        if (!$datos) {
            odontoResponder("error", array("mensaje" => "Tratamiento no encontrado."));
        }
        $productoId = (string)$datos["cod_productoFK"];
        $nombre = (string)$datos["nombre_producto"];
        $ventaId = (string)$datos["cod_ventaFK"];
        $alcance = odontoNormalizarAlcance($datos["alcance_odontologico"]);
        $riesgo = isset($datos["nivel_riesgo_financiero"]) ? (int)$datos["nivel_riesgo_financiero"] : null;
        $avance = isset($datos["progreso_porcentaje"]) ? (int)$datos["progreso_porcentaje"] : 0;
        $estadoLink = $avance >= 100 ? "completado" : ($avance > 0 ? "en_proceso" : "pendiente");
    } elseif ($presupuestoItemId != "") {
        $datos = odontoDatosTratamientoDesdePresupuestoItem($mysqli, $presupuestoItemId);
        if (!$datos) {
            odontoResponder("error", array("mensaje" => "Item de presupuesto no encontrado."));
        }
        $productoId = (string)$datos["cod_productoFK"];
        $nombre = (string)$datos["nombre_producto"];
        $presupuestoId = (string)$datos["cod_presupuestoFK"];
        $alcance = odontoNormalizarAlcance($datos["alcance_odontologico"]);
        $riesgo = isset($datos["nivel_riesgo_financiero"]) ? (int)$datos["nivel_riesgo_financiero"] : null;
    } else {
        $riesgo = odontoObtenerRiesgoProducto($mysqli, $productoId);
    }

    if ($nombre == "" && $productoId != "") {
        $stmt = $mysqli->prepare("SELECT nombre_producto, alcance_odontologico FROM producto WHERE cod_producto = ? LIMIT 1");
        $stmt->bind_param("s", $productoId);
        if ($stmt->execute()) {
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $nombre = (string)$row["nombre_producto"];
                $alcance = odontoNormalizarAlcance($row["alcance_odontologico"]);
            }
        }
    }

    if ($alcance == "boca_completa") {
        $bocaCompleta = 1;
        $pieza = null;
        $superficiesJson = null;
        $arcada = null;
        $cuadrante = null;
    } elseif ($alcance == "arcada") {
        $pieza = null;
        $superficiesJson = null;
        $cuadrante = null;
    } elseif ($alcance == "cuadrante") {
        $pieza = null;
        $superficiesJson = null;
        $arcada = null;
    } elseif ($alcance == "pieza_dental") {
        $superficiesJson = null;
    }

    if ($productoId == "") {
        odontoResponder("error", array("mensaje" => "Falta tratamiento."));
    }

    $linkExistente = null;
    if ($detalleId != "") {
        $stmt = $mysqli->prepare("SELECT * FROM odontograma_tratamiento_links WHERE odontograma_id = ? AND detalle_venta_id = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("is", $odontograma["id"], $detalleId);
    } elseif ($presupuestoItemId != "") {
        $stmt = $mysqli->prepare("SELECT * FROM odontograma_tratamiento_links WHERE odontograma_id = ? AND presupuesto_item_id = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("is", $odontograma["id"], $presupuestoItemId);
    } else {
        $stmt = null;
    }
    if ($stmt && $stmt->execute()) {
        $linkExistente = $stmt->get_result()->fetch_assoc();
    }

    $ubicacionNuevo = array(
        "pieza" => $pieza,
        "superficies_json" => $superficiesJson,
        "arcada" => $arcada,
        "cuadrante" => $cuadrante,
        "boca_completa" => $bocaCompleta
    );

    if ($linkExistente) {
        $linkId = (int)$linkExistente["id"];
        $valorAnterior = json_encode(array(
            "pieza" => $linkExistente["pieza"],
            "superficies_json" => $linkExistente["superficies_json"],
            "arcada" => $linkExistente["arcada"],
            "cuadrante" => $linkExistente["cuadrante"],
            "boca_completa" => $linkExistente["boca_completa"]
        ));
        $stmt = $mysqli->prepare("UPDATE odontograma_tratamiento_links SET venta_id=?, detalle_venta_id=?, presupuesto_id=?, presupuesto_item_id=?, producto_id=?, nombre_tratamiento_snapshot=?, nivel_riesgo_snapshot=?, alcance_odontologico=?, pieza=?, denticion=?, superficies_json=?, arcada=?, cuadrante=?, boca_completa=?, origen=?, estado_link=?, actualizado_por=?, fecha_actualizacion=NOW() WHERE id=? LIMIT 1");
        $stmt->bind_param("iiiississssssissii", $ventaId, $detalleId, $presupuestoId, $presupuestoItemId, $productoId, $nombre, $riesgo, $alcance, $pieza, $denticion, $superficiesJson, $arcada, $cuadrante, $bocaCompleta, $origen, $estadoLink, $user, $linkId);
        if (!$stmt->execute()) {
            odontoResponder("error", array("mensaje" => "No se pudo actualizar la ubicacion."));
        }
        $accion = "cambiar_ubicacion";
        $descripcion = "Se actualizo la ubicacion odontologica de ".odontoUtf8($nombre).".";
        odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], $accion, $descripcion, $pieza, null, null, $linkId, $productoId, $ventaId, $detalleId, $presupuestoId, $presupuestoItemId, $motivo, $user, $valorAnterior, json_encode($ubicacionNuevo));
    } else {
        $stmt = $mysqli->prepare("INSERT INTO odontograma_tratamiento_links (odontograma_id, venta_id, detalle_venta_id, presupuesto_id, presupuesto_item_id, producto_id, nombre_tratamiento_snapshot, nivel_riesgo_snapshot, alcance_odontologico, pieza, denticion, superficies_json, arcada, cuadrante, boca_completa, origen, estado_link, creado_por, actualizado_por) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iiiiississssssissii", $odontograma["id"], $ventaId, $detalleId, $presupuestoId, $presupuestoItemId, $productoId, $nombre, $riesgo, $alcance, $pieza, $denticion, $superficiesJson, $arcada, $cuadrante, $bocaCompleta, $origen, $estadoLink, $user, $user);
        if (!$stmt->execute()) {
            odontoResponder("error", array("mensaje" => "No se pudo guardar la ubicacion."));
        }
        $linkId = $stmt->insert_id;
        $descripcion = "Se vinculo ".odontoUtf8($nombre)." a ".odontoUbicacionTexto($ubicacionNuevo).".";
        odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "vincular_tratamiento", $descripcion, $pieza, null, null, $linkId, $productoId, $ventaId, $detalleId, $presupuestoId, $presupuestoItemId, $motivo, $user, null, json_encode($ubicacionNuevo));
    }

    odontoResponder("exito", array(
        "link_id" => $linkId,
        "ubicacion_texto" => odontoUbicacionTexto($ubicacionNuevo)
    ));
}

function odontoGuardarMarca($mysqli, $odontograma, $user)
{
    $motivo = odontoPost("motivo");
    odontoPrepararModificacion($mysqli, $odontograma, $user, $motivo);
    $pieza = trim(odontoPost("pieza"));
    $denticion = trim(odontoPost("denticion", "permanente"));
    $superficie = trim(odontoPost("superficie"));
    $tipoMarca = trim(odontoPost("tipo_marca", "caries"));
    $estadoMarca = trim(odontoPost("estado_marca", "observado"));
    $color = trim(odontoPost("color", "rojo"));
    $observacion = trim(odontoPost("observacion"));

    if ($pieza == "") {
        odontoResponder("error", array("mensaje" => "Falta pieza dental."));
    }

    $stmt = $mysqli->prepare("INSERT INTO odontograma_marcas (odontograma_id, pieza, denticion, superficie, tipo_marca, estado_marca, color, observacion, creado_por, actualizado_por) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("isssssssii", $odontograma["id"], $pieza, $denticion, $superficie, $tipoMarca, $estadoMarca, $color, $observacion, $user, $user);
    if (!$stmt->execute()) {
        odontoResponder("error", array("mensaje" => "No se pudo guardar la marca."));
    }
    $marcaId = $stmt->insert_id;
    $descripcion = "Se agrego ".$tipoMarca." en pieza ".$pieza.($superficie != "" ? ", superficie ".odontoTextoSuperficie($superficie) : "").".";
    odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "agregar_marca", $descripcion, $pieza, $superficie, $marcaId, null, null, null, null, null, null, $motivo, $user, null, json_encode(array("tipo_marca" => $tipoMarca, "estado_marca" => $estadoMarca, "color" => $color)));
    odontoResponder("exito", array("marca_id" => $marcaId));
}

function odontoEliminarMarca($mysqli, $odontograma, $user)
{
    $motivo = odontoPost("motivo");
    odontoPrepararModificacion($mysqli, $odontograma, $user, $motivo);
    $marcaId = odontoPost("marca_id");
    $stmt = $mysqli->prepare("SELECT * FROM odontograma_marcas WHERE id = ? AND odontograma_id = ? AND activo = 1 LIMIT 1");
    $stmt->bind_param("si", $marcaId, $odontograma["id"]);
    $stmt->execute();
    $marca = $stmt->get_result()->fetch_assoc();
    if (!$marca) {
        odontoResponder("error", array("mensaje" => "Marca no encontrada."));
    }
    $stmt = $mysqli->prepare("UPDATE odontograma_marcas SET activo = 0, actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? LIMIT 1");
    $stmt->bind_param("is", $user, $marcaId);
    if (!$stmt->execute()) {
        odontoResponder("error", array("mensaje" => "No se pudo eliminar la marca."));
    }
    odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "eliminar_marca", "Se elimino marca de pieza ".$marca["pieza"].".", $marca["pieza"], $marca["superficie"], $marcaId, null, null, null, null, null, null, $motivo, $user, json_encode($marca), null);
    odontoResponder("exito");
}

function odontoEliminarLink($mysqli, $odontograma, $user)
{
    $motivo = odontoPost("motivo");
    odontoPrepararModificacion($mysqli, $odontograma, $user, $motivo);
    $linkId = odontoPost("link_id");
    $stmt = $mysqli->prepare("SELECT * FROM odontograma_tratamiento_links WHERE id = ? AND odontograma_id = ? AND activo = 1 LIMIT 1");
    $stmt->bind_param("si", $linkId, $odontograma["id"]);
    $stmt->execute();
    $link = $stmt->get_result()->fetch_assoc();
    if (!$link) {
        odontoResponder("error", array("mensaje" => "Vinculo no encontrado."));
    }
    $stmt = $mysqli->prepare("UPDATE odontograma_tratamiento_links SET activo = 0, actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? LIMIT 1");
    $stmt->bind_param("is", $user, $linkId);
    if (!$stmt->execute()) {
        odontoResponder("error", array("mensaje" => "No se pudo quitar el vinculo."));
    }
    odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "quitar_vinculo_tratamiento", "Se quito ubicacion de ".odontoUtf8($link["nombre_tratamiento_snapshot"]).".", $link["pieza"], null, null, $linkId, $link["producto_id"], $link["venta_id"], $link["detalle_venta_id"], $link["presupuesto_id"], $link["presupuesto_item_id"], $motivo, $user, json_encode($link), null);
    odontoResponder("exito");
}

function odontoObtenerDatos($mysqli, $ctx, $odontograma)
{
    $odontogramaId = (int)$odontograma["id"];
    $marcas = array();
    $res = $mysqli->query("SELECT * FROM odontograma_marcas WHERE odontograma_id = ".$odontogramaId." AND activo = 1 ORDER BY pieza ASC, id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            foreach ($row as $k => $v) {
                $row[$k] = odontoUtf8($v);
            }
            $marcas[] = $row;
        }
    }

    $links = array();
    $res = $mysqli->query("SELECT l.*, vt.num_factura, IFNULL(dtv.progreso_porcentaje,0) AS progreso_actual, IFNULL(dtv.estado_tratamiento,'') AS estado_tratamiento_actual FROM odontograma_tratamiento_links l LEFT JOIN venta vt ON vt.cod_venta = l.venta_id LEFT JOIN detalle_venta dtv ON dtv.cod_detalle = l.detalle_venta_id WHERE l.odontograma_id = ".$odontogramaId." AND l.activo = 1 ORDER BY l.id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $row["ubicacion_texto"] = odontoUbicacionTexto($row);
            foreach ($row as $k => $v) {
                $row[$k] = odontoUtf8($v);
            }
            $links[] = $row;
        }
    }

    $historial = odontoObtenerHistorialArray($mysqli, $odontogramaId, 8);
    $sinUbicacion = odontoTratamientosSinUbicacion($mysqli, $ctx, $odontogramaId);

    $odo = array();
    foreach ($odontograma as $k => $v) {
        $odo[$k] = odontoUtf8($v);
    }
    $odo["version_actual"] = (int)$odontograma["version_actual"];

    odontoResponder("exito", array(
        "odontograma" => $odo,
        "contexto" => array(
            "paciente_id" => odontoUtf8($ctx["paciente_id"]),
            "cedula" => odontoUtf8($ctx["cedula"]),
            "paciente_nombre" => odontoUtf8($ctx["paciente_nombre"]),
            "venta_id" => odontoUtf8($ctx["venta_id"]),
            "presupuesto_id" => odontoUtf8($ctx["presupuesto_id"])
        ),
        "marcas" => $marcas,
        "links" => $links,
        "historial" => $historial,
        "tratamientos_sin_ubicacion" => $sinUbicacion
    ));
}

function odontoTratamientosSinUbicacion($mysqli, $ctx, $odontogramaId)
{
    if ($ctx["paciente_id"] == "") {
        return array();
    }
    $selectRiesgo = ProductoRiesgoFinancieroSelectSql($mysqli, "pr");
    $stmt = $mysqli->prepare("SELECT dtv.cod_detalle, dtv.cod_ventaFK, dtv.cod_productoFK, pr.nombre_producto, pr.alcance_odontologico, IFNULL(dtv.progreso_porcentaje,0) AS progreso_porcentaje, IFNULL(dtv.estado_tratamiento,'') AS estado_tratamiento, ".$selectRiesgo."
        FROM detalle_venta dtv
        INNER JOIN venta vt ON vt.cod_venta = dtv.cod_ventaFK
        INNER JOIN producto pr ON pr.cod_producto = dtv.cod_productoFK
        LEFT JOIN odontograma_tratamiento_links l ON l.detalle_venta_id = dtv.cod_detalle AND l.activo = 1
        WHERE vt.cod_clienteFK = ?
          AND l.id IS NULL
          AND IFNULL(pr.alcance_odontologico,'no_requiere') <> 'no_requiere'
          AND dtv.estado <> 'Eliminado'
        ORDER BY vt.cod_venta DESC, dtv.cod_detalle ASC
        LIMIT 60");
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param("s", $ctx["paciente_id"]);
    if (!$stmt->execute()) {
        return array();
    }
    $result = $stmt->get_result();
    $items = array();
    while ($row = $result->fetch_assoc()) {
        $items[] = array(
            "detalle_venta_id" => odontoUtf8($row["cod_detalle"]),
            "venta_id" => odontoUtf8($row["cod_ventaFK"]),
            "producto_id" => odontoUtf8($row["cod_productoFK"]),
            "nombre_tratamiento" => odontoUtf8($row["nombre_producto"]),
            "alcance_odontologico" => odontoNormalizarAlcance($row["alcance_odontologico"]),
            "nivel_riesgo_financiero" => isset($row["nivel_riesgo_financiero"]) ? (int)$row["nivel_riesgo_financiero"] : 1,
            "progreso" => (int)$row["progreso_porcentaje"],
            "estado_tratamiento" => odontoUtf8($row["estado_tratamiento"])
        );
    }
    return $items;
}

function odontoObtenerHistorialArray($mysqli, $odontogramaId, $limite)
{
    $limite = (int)$limite;
    if ($limite <= 0) {
        $limite = 20;
    }
    $sql = "SELECT h.*, (SELECT nombre_persona FROM persona WHERE cod_persona = h.usuario_id LIMIT 1) AS usuario_nombre FROM odontograma_historial h WHERE h.odontograma_id = ? ORDER BY h.fecha_hora DESC LIMIT ".$limite;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param("i", $odontogramaId);
    if (!$stmt->execute()) {
        return array();
    }
    $items = array();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $items[] = array(
            "id" => (int)$row["id"],
            "fecha_hora" => odontoUtf8($row["fecha_hora"]),
            "version" => (int)$row["version"],
            "accion" => odontoUtf8($row["accion"]),
            "descripcion" => odontoUtf8($row["descripcion"]),
            "pieza" => odontoUtf8($row["pieza"]),
            "superficie" => odontoUtf8($row["superficie"]),
            "marca_id" => isset($row["marca_id"]) ? (int)$row["marca_id"] : 0,
            "link_id" => isset($row["link_id"]) ? (int)$row["link_id"] : 0,
            "tratamiento_id" => isset($row["tratamiento_id"]) ? odontoUtf8($row["tratamiento_id"]) : "",
            "venta_id" => isset($row["venta_id"]) ? odontoUtf8($row["venta_id"]) : "",
            "detalle_venta_id" => isset($row["detalle_venta_id"]) ? odontoUtf8($row["detalle_venta_id"]) : "",
            "presupuesto_id" => isset($row["presupuesto_id"]) ? odontoUtf8($row["presupuesto_id"]) : "",
            "presupuesto_item_id" => isset($row["presupuesto_item_id"]) ? odontoUtf8($row["presupuesto_item_id"]) : "",
            "valor_anterior" => odontoUtf8($row["valor_anterior"]),
            "valor_nuevo" => odontoUtf8($row["valor_nuevo"]),
            "motivo" => odontoUtf8($row["motivo"]),
            "usuario_id" => isset($row["usuario_id"]) ? (int)$row["usuario_id"] : 0,
            "usuario_nombre" => odontoUtf8($row["usuario_nombre"])
        );
    }
    return $items;
}

function odontoConvalidar($mysqli, $odontograma, $user)
{
    $stmt = $mysqli->prepare("UPDATE odontogramas SET estado = 'convalidado', version_actual = version_actual + 1, convalidado_por = ?, fecha_convalidacion = NOW(), actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? LIMIT 1");
    $stmt->bind_param("iii", $user, $user, $odontograma["id"]);
    if (!$stmt->execute()) {
        odontoResponder("error", array("mensaje" => "No se pudo convalidar el odontograma."));
    }
    $version = (int)$odontograma["version_actual"] + 1;
    odontoRegistrarHistorial($mysqli, $odontograma["id"], $version, "convalidar_odontograma", "Se convalido el odontograma clinico.", null, null, null, null, null, null, null, null, null, null, $user);
    odontoResponder("exito", array("version" => $version));
}

function odontoDeshacer($mysqli, $odontograma, $user)
{
    $stmt = $mysqli->prepare("SELECT * FROM odontograma_historial WHERE odontograma_id = ? AND usuario_id = ? AND (link_id IS NOT NULL OR marca_id IS NOT NULL) ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("ii", $odontograma["id"], $user);
    if (!$stmt->execute()) {
        odontoResponder("error", array("mensaje" => "No se pudo consultar la ultima accion."));
    }
    $hist = $stmt->get_result()->fetch_assoc();
    if (!$hist) {
        odontoResponder("error", array("mensaje" => "No hay accion reciente para deshacer."));
    }
    if (!empty($hist["link_id"])) {
        $stmt = $mysqli->prepare("UPDATE odontograma_tratamiento_links SET activo = 0, actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? AND odontograma_id = ? LIMIT 1");
        $stmt->bind_param("iii", $user, $hist["link_id"], $odontograma["id"]);
        $stmt->execute();
    }
    if (!empty($hist["marca_id"])) {
        $stmt = $mysqli->prepare("UPDATE odontograma_marcas SET activo = 0, actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? AND odontograma_id = ? LIMIT 1");
        $stmt->bind_param("iii", $user, $hist["marca_id"], $odontograma["id"]);
        $stmt->execute();
    }
    odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "deshacer_accion", "Se deshizo la ultima accion registrada.", $hist["pieza"], $hist["superficie"], $hist["marca_id"], $hist["link_id"], $hist["tratamiento_id"], $hist["venta_id"], $hist["detalle_venta_id"], $hist["presupuesto_id"], $hist["presupuesto_item_id"], null, $user);
    odontoResponder("exito");
}

function odontoMigrarPresupuestoAVenta($mysqli, $ctx, $odontograma, $user)
{
    $presupuestoId = odontoPost("presupuesto_id", $ctx["presupuesto_id"]);
    $ventaId = odontoPost("venta_id", $ctx["venta_id"]);
    if ($presupuestoId == "" || $ventaId == "") {
        odontoResponder("error", array("mensaje" => "Falta presupuesto o venta."));
    }
    $res = $mysqli->query("SELECT * FROM odontograma_tratamiento_links WHERE odontograma_id = ".(int)$odontograma["id"]." AND presupuesto_id = ".(int)$presupuestoId." AND activo = 1 AND (detalle_venta_id IS NULL OR detalle_venta_id = 0) ORDER BY id ASC");
    if (!$res) {
        odontoResponder("error", array("mensaje" => "No se pudieron consultar vinculos."));
    }
    $migrados = 0;
    while ($link = $res->fetch_assoc()) {
        $stmt = $mysqli->prepare("SELECT dtv.cod_detalle FROM detalle_venta dtv LEFT JOIN odontograma_tratamiento_links l ON l.detalle_venta_id = dtv.cod_detalle AND l.activo = 1 WHERE dtv.cod_ventaFK = ? AND dtv.cod_productoFK = ? AND l.id IS NULL ORDER BY dtv.cod_detalle ASC LIMIT 1");
        $stmt->bind_param("is", $ventaId, $link["producto_id"]);
        if ($stmt->execute()) {
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $detalleId = (int)$row["cod_detalle"];
                $stmtUpd = $mysqli->prepare("UPDATE odontograma_tratamiento_links SET venta_id = ?, detalle_venta_id = ?, origen = 'venta_principal', actualizado_por = ?, fecha_actualizacion = NOW() WHERE id = ? LIMIT 1");
                $stmtUpd->bind_param("iiii", $ventaId, $detalleId, $user, $link["id"]);
                if ($stmtUpd->execute()) {
                    $migrados++;
                    odontoRegistrarHistorial($mysqli, $odontograma["id"], $odontograma["version_actual"], "migrar_presupuesto_venta", "Se vinculo ubicacion del presupuesto a la venta #".$ventaId.".", $link["pieza"], null, null, $link["id"], $link["producto_id"], $ventaId, $detalleId, $presupuestoId, $link["presupuesto_item_id"], null, $user);
                }
            }
        }
    }
    odontoResponder("exito", array("migrados" => $migrados));
}

$user = odontoVerificarSesion();
$accion = isset($_POST["accion"]) ? odontoPost("accion") : odontoPost("funt");
$mysqli = conectar_al_servidor();

if (!odontoTablasDisponibles($mysqli)) {
    odontoResponder("error", array("mensaje" => "Debe aplicar la actualizacion SQL del odontograma."));
}

if ($accion == "obtenerAlcanceProducto") {
    odontoObtenerAlcanceProducto($mysqli, odontoPost("producto_id"));
}

$ctx = odontoObtenerContexto($mysqli, odontoPost("paciente_id"), odontoPost("cedula"), odontoPost("venta_id"), odontoPost("presupuesto_id"));
$odontograma = odontoObtenerOCrear($mysqli, $ctx, $user);
if (!$odontograma) {
    odontoResponder("error", array("mensaje" => "No se pudo crear o consultar el odontograma."));
}

switch ($accion) {
    case "obtenerOdontogramaPaciente":
        odontoObtenerDatos($mysqli, $ctx, $odontograma);
        break;
    case "guardarMarcaOdontograma":
        odontoGuardarMarca($mysqli, $odontograma, $user);
        break;
    case "eliminarMarcaOdontograma":
        odontoEliminarMarca($mysqli, $odontograma, $user);
        break;
    case "guardarLinkTratamientoOdontograma":
        odontoGuardarLink($mysqli, $ctx, $odontograma, $user);
        break;
    case "eliminarLinkTratamientoOdontograma":
        odontoEliminarLink($mysqli, $odontograma, $user);
        break;
    case "convalidarOdontograma":
        odontoConvalidar($mysqli, $odontograma, $user);
        break;
    case "deshacerUltimaAccionOdontograma":
        odontoDeshacer($mysqli, $odontograma, $user);
        break;
    case "migrarLinksPresupuestoAVenta":
        odontoMigrarPresupuestoAVenta($mysqli, $ctx, $odontograma, $user);
        break;
    case "obtenerHistorialOdontograma":
        odontoResponder("exito", array("historial" => odontoObtenerHistorialArray($mysqli, (int)$odontograma["id"], 50)));
        break;
    default:
        odontoResponder("error", array("mensaje" => "Operacion de odontograma no definida."));
        break;
}
?>

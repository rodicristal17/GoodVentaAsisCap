<?php
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("buscar_nivel.php");

date_default_timezone_set("America/Asuncion");

function ri_post($key, $default = "")
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    return mb_convert_encoding((string)$_POST[$key], "ISO-8859-1", "UTF-8");
}

function ri_raw_post($key, $default = "")
{
    return isset($_POST[$key]) ? (string)$_POST[$key] : $default;
}

function ri_utf8($value)
{
    return mb_convert_encoding((string)$value, "UTF-8", "ISO-8859-1");
}

function ri_db($value)
{
    return mb_convert_encoding((string)$value, "ISO-8859-1", "UTF-8");
}

function ri_int_o_null($value)
{
    $value = trim((string)$value);
    return $value === "" ? null : (int)$value;
}

function ri_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function ri_js($value)
{
    $json = json_encode((string)$value);
    if ($json === false) {
        $json = "\"\"";
    }
    return htmlspecialchars($json, ENT_QUOTES, "UTF-8");
}

function ri_json($data)
{
    echo json_encode($data);
    exit;
}

function ri_verificar_sesion($operacion)
{
    $user = ri_post("useru");
    $pass = isset($_POST["passu"]) ? $_POST["passu"] : "";
    $pass = str_replace("=", "+", $pass);
    $navegador = ri_post("navegador");

    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != "ok") {
        ri_json(array("1" => "UI"));
    }

    ri_despachar($operacion, $user);
}

function ri_usuario_actual($user)
{
    $mysqli = conectar_al_servidor();
    $sql = "SELECT u.cod_usuario, u.tipo, u.estado, u.cod_localFK, u.url, p.nombre_persona, l.Nombre AS nombre_local
            FROM usuario u
            INNER JOIN persona p ON p.cod_persona = u.cod_usuario
            LEFT JOIN local l ON l.cod_local = u.cod_localFK
            WHERE u.cod_usuario = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user);
    if (!$stmt->execute()) {
        ri_json(array("1" => "error", "mensaje" => $stmt->error));
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $mysqli->close();

    if (!$row) {
        return array(
            "cod_usuario" => $user,
            "tipo" => "",
            "estado" => "",
            "cod_localFK" => "",
            "nombre_persona" => "",
            "nombre_local" => "",
            "foto_url" => ""
        );
    }

    return array(
        "cod_usuario" => ri_utf8($row["cod_usuario"]),
        "tipo" => ri_utf8($row["tipo"]),
        "estado" => ri_utf8($row["estado"]),
        "cod_localFK" => ri_utf8($row["cod_localFK"]),
        "nombre_persona" => ri_utf8($row["nombre_persona"]),
        "nombre_local" => ri_utf8($row["nombre_local"]),
        "foto_url" => ri_utf8($row["url"])
    );
}

function ri_permiso($user, $codigo)
{
    if ((string)$user === "2") {
        return true;
    }

    $mysqli = conectar_al_servidor();
    $codigoDb = ri_db($codigo);
    $sql = "SELECT
                COUNT(lta.idlistadodeacceso) AS existe_permiso,
                SUM(CASE WHEN au.accion = 'SI' THEN 1 ELSE 0 END) AS habilitado
            FROM listadodeacceso lta
            LEFT JOIN accesosuser au
                ON au.idlistadodeaccesoFK = lta.idlistadodeacceso
                AND au.usuarios_idusario = ?
            WHERE lta.codigo = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $user, $codigoDb);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        return true;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $mysqli->close();

    if (!$row || (int)$row["existe_permiso"] === 0) {
        return true;
    }

    return (int)$row["habilitado"] > 0;
}

function ri_puede_emitir($usuario)
{
    return ri_usuario_logueado($usuario);
}

function ri_puede_anular($usuario)
{
    return ri_usuario_activo($usuario) && ri_permiso($usuario["cod_usuario"], "ANULARRECETARIOINDICACIONES");
}

function ri_usuario_activo($usuario)
{
    $estado = strtoupper(trim($usuario["estado"]));
    return $estado === "ACTIVO";
}

function ri_usuario_logueado($usuario)
{
    return isset($usuario["cod_usuario"]) && trim((string)$usuario["cod_usuario"]) !== "";
}

function ri_es_doctor($usuario)
{
    $tipo = strtoupper(trim($usuario["tipo"]));
    return ri_usuario_activo($usuario) && $tipo === "DOCTOR";
}

function ri_emisor_recetario_autorizado($usuario)
{
    return ri_usuario_logueado($usuario);
}

function ri_mensaje_emisor_no_autorizado()
{
    return "No se pudo identificar el usuario logueado.";
}

function ri_aplicar_estado_emisor_documento(&$documento, $usuario, $user)
{
    $autorizado = ri_emisor_recetario_autorizado($usuario);
    $documento["puede_emitir"] = ri_puede_emitir($usuario) ? "SI" : "NO";
    $documento["puede_borrador"] = $autorizado ? "SI" : "NO";
    $documento["doctor_tipo"] = $usuario["tipo"];
    $documento["doctor_estado"] = $autorizado ? "Usuario del sistema" : ri_mensaje_emisor_no_autorizado();
    $documento["perfil_clinico_autorizado"] = $autorizado ? "SI" : "NO";
    $documento["permiso_emitir_recetario"] = $autorizado ? "SI" : "NO";
    $documento["motivo_emisor"] = $autorizado ? "" : ri_mensaje_emisor_no_autorizado();
    $documento["usuario_actual_id"] = $usuario["cod_usuario"];
    $documento["usuario_actual_nombre"] = $usuario["nombre_persona"];
    $documento["usuario_actual_foto_url"] = $usuario["foto_url"];
    $documento["puede_anular"] = ri_puede_anular($usuario) ? "SI" : "NO";
    $documento["puede_imprimir"] = ri_permiso($user, "IMPRIMIRRECETARIOINDICACIONES") ? "SI" : "NO";
}

function ri_despachar($operacion, $user)
{
    switch ($operacion) {
        case "obtener_contexto":
            ri_obtener_contexto_accion($user);
            break;
        case "guardar_borrador":
            ri_guardar_accion($user, "borrador");
            break;
        case "emitir":
            ri_guardar_accion($user, "emitir");
            break;
        case "listar":
            ri_listar_accion($user);
            break;
        case "detalle":
            ri_detalle_accion($user);
            break;
        case "imprimir":
            ri_imprimir_accion($user);
            break;
        case "firmar_documento":
            ri_firmar_documento_accion($user);
            break;
        case "anular":
            ri_anular_accion($user);
            break;
        case "plantillas":
            ri_plantillas_accion($user);
            break;
        default:
            ri_json(array("1" => "error", "mensaje" => "Operacion no reconocida"));
    }
}

function ri_obtener_contexto_accion($user)
{
    $ventaId = ri_post("venta_id");
    $clienteId = ri_post("cliente_id");
    $consultaId = ri_post("consulta_id");
    $hiloId = ri_post("hilo_id");
    $contexto = ri_obtener_contexto($ventaId, $clienteId, $consultaId, $hiloId, $user);

    ri_json(array(
        "1" => "exito",
        "2" => $contexto["contexto"],
        "3" => $contexto["ventas"],
        "4" => $contexto["alertas"],
        "5" => ri_obtener_plantillas()
    ));
}

function ri_obtener_contexto($ventaId, $clienteId, $consultaId, $hiloId, $user)
{
    $mysqli = conectar_al_servidor();
    $usuario = ri_usuario_actual($user);
    $consulta = array(
        "consulta_id" => "",
        "consulta_resumen" => "",
        "cod_ventaFK" => "",
        "cod_clienteFK" => "",
        "sucursal_id" => "",
        "sucursal_nombre" => ""
    );
    $hiloResumen = "";
    $hiloLocalId = "";
    $hiloLocalNombre = "";

    if ($consultaId !== "") {
        $sqlConsulta = "SELECT c.cod_consulta, c.cod_ventaFK, c.cod_clienteFK, c.trabajo_realizado, c.motivoconsulta, c.diagnostico,
                               u.cod_localFK AS consulta_local_id, IFNULL(lc.Nombre, '') AS consulta_local_nombre
                        FROM consulta c
                        LEFT JOIN usuario u ON u.cod_usuario = c.cod_usuarioFK
                        LEFT JOIN local lc ON lc.cod_local = u.cod_localFK
                        WHERE c.cod_consulta = ?
                        LIMIT 1";
        $stmtConsulta = $mysqli->prepare($sqlConsulta);
        $stmtConsulta->bind_param("i", $consultaId);
        if ($stmtConsulta->execute()) {
            $rowConsulta = $stmtConsulta->get_result()->fetch_assoc();
            if ($rowConsulta) {
                $resumen = trim(ri_utf8($rowConsulta["trabajo_realizado"]));
                if ($resumen === "") {
                    $resumen = trim(ri_utf8($rowConsulta["motivoconsulta"]));
                }
                if ($resumen === "") {
                    $resumen = trim(ri_utf8($rowConsulta["diagnostico"]));
                }
                $consulta = array(
                    "consulta_id" => ri_utf8($rowConsulta["cod_consulta"]),
                    "consulta_resumen" => $resumen,
                    "cod_ventaFK" => ri_utf8($rowConsulta["cod_ventaFK"]),
                    "cod_clienteFK" => ri_utf8($rowConsulta["cod_clienteFK"]),
                    "sucursal_id" => ri_utf8($rowConsulta["consulta_local_id"]),
                    "sucursal_nombre" => ri_utf8($rowConsulta["consulta_local_nombre"])
                );
                if ($ventaId === "" && $consulta["cod_ventaFK"] !== "") {
                    $ventaId = $consulta["cod_ventaFK"];
                }
                if ($clienteId === "" && $consulta["cod_clienteFK"] !== "") {
                    $clienteId = $consulta["cod_clienteFK"];
                }
            }
        }
        $stmtConsulta->close();
    }

    if ($hiloId !== "") {
        $sqlHilo = "SELECT ic.cod_interConsulta, ic.asunto, ic.cod_ventaFK, ic.cod_localFK,
                           IFNULL(lh.Nombre, '') AS hilo_local_nombre
                    FROM interconsulta ic
                    LEFT JOIN local lh ON lh.cod_local = ic.cod_localFK
                    WHERE ic.cod_interConsulta = ?
                    LIMIT 1";
        $stmtHilo = $mysqli->prepare($sqlHilo);
        $stmtHilo->bind_param("i", $hiloId);
        if ($stmtHilo->execute()) {
            $rowHilo = $stmtHilo->get_result()->fetch_assoc();
            if ($rowHilo) {
                $hiloResumen = "Hilo #".ri_utf8($rowHilo["cod_interConsulta"]);
                if (trim(ri_utf8($rowHilo["asunto"])) !== "") {
                    $hiloResumen .= " - ".ri_utf8($rowHilo["asunto"]);
                }
                if ($ventaId === "" && trim((string)$rowHilo["cod_ventaFK"]) !== "") {
                    $ventaId = ri_utf8($rowHilo["cod_ventaFK"]);
                }
                $hiloLocalId = ri_utf8($rowHilo["cod_localFK"]);
                $hiloLocalNombre = ri_utf8($rowHilo["hilo_local_nombre"]);
            }
        }
        $stmtHilo->close();
    }

    $venta = null;
    $ventaIdSolicitado = $ventaId;
    if ($ventaId !== "") {
        $sqlVenta = "SELECT vt.cod_venta, vt.num_factura, vt.apodo, vt.cod_clienteFK, vt.cod_local,
                            p.nombre_persona AS titular_nombre, cl.ci_cliente, IFNULL(l.Nombre, '') AS sucursal_nombre
                     FROM venta vt
                     INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
                     INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
                     LEFT JOIN local l ON l.cod_local = vt.cod_local
                     WHERE vt.cod_venta = ?
                     LIMIT 1";
        $stmtVenta = $mysqli->prepare($sqlVenta);
        $stmtVenta->bind_param("i", $ventaId);
        if ($stmtVenta->execute()) {
            $venta = $stmtVenta->get_result()->fetch_assoc();
        }
        $stmtVenta->close();
    }

    if ($ventaIdSolicitado !== "" && !$venta) {
        $ventaId = "";
    }

    if (!$venta && $clienteId !== "" && $ventaIdSolicitado === "") {
        $sqlVentaUnica = "SELECT vt.cod_venta, vt.num_factura, vt.apodo, vt.cod_clienteFK, vt.cod_local,
                                p.nombre_persona AS titular_nombre, cl.ci_cliente, IFNULL(l.Nombre, '') AS sucursal_nombre
                         FROM venta vt
                         INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
                         INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
                         LEFT JOIN local l ON l.cod_local = vt.cod_local
                         WHERE vt.cod_clienteFK = ?
                         AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones WHERE cod_venta = vt.cod_venta LIMIT 1), 0) = 0
                         ORDER BY vt.cod_venta DESC
                         LIMIT 1";
        $stmtVentaUnica = $mysqli->prepare($sqlVentaUnica);
        $stmtVentaUnica->bind_param("i", $clienteId);
        if ($stmtVentaUnica->execute()) {
            $venta = $stmtVentaUnica->get_result()->fetch_assoc();
        }
        $stmtVentaUnica->close();
    }

    $titularId = $clienteId;
    $titularNombre = "";
    $cedulaTitular = "";
    $sucursalId = "";
    $sucursalNombre = "";

    if ($venta) {
        $titularId = ri_utf8($venta["cod_clienteFK"]);
        $titularNombre = ri_utf8($venta["titular_nombre"]);
        $cedulaTitular = ri_utf8($venta["ci_cliente"]);
        $sucursalId = ri_utf8($venta["cod_local"]);
        $sucursalNombre = ri_utf8($venta["sucursal_nombre"]);
        if ($sucursalId !== "" && $sucursalNombre === "") {
            $sucursalNombre = "Local #".$sucursalId;
        }
        $ventaId = ri_utf8($venta["cod_venta"]);
    } elseif ($clienteId !== "") {
        $sqlCliente = "SELECT cl.cod_cliente, cl.ci_cliente, p.nombre_persona
                       FROM cliente cl
                       INNER JOIN persona p ON p.cod_persona = cl.cod_cliente
                       WHERE cl.cod_cliente = ?
                       LIMIT 1";
        $stmtCliente = $mysqli->prepare($sqlCliente);
        $stmtCliente->bind_param("i", $clienteId);
        if ($stmtCliente->execute()) {
            $cliente = $stmtCliente->get_result()->fetch_assoc();
            if ($cliente) {
                $titularId = ri_utf8($cliente["cod_cliente"]);
                $titularNombre = ri_utf8($cliente["nombre_persona"]);
                $cedulaTitular = ri_utf8($cliente["ci_cliente"]);
            }
        }
        $stmtCliente->close();
    }

    if ($sucursalId === "" && $consulta["sucursal_id"] !== "") {
        $sucursalId = $consulta["sucursal_id"];
        $sucursalNombre = $consulta["sucursal_nombre"];
        if ($sucursalNombre === "") {
            $sucursalNombre = "Local #".$sucursalId;
        }
    }

    if ($sucursalId === "" && $hiloLocalId !== "") {
        $sucursalId = $hiloLocalId;
        $sucursalNombre = $hiloLocalNombre;
        if ($sucursalNombre === "") {
            $sucursalNombre = "Local #".$sucursalId;
        }
    }

    if ($sucursalId === "" && $usuario["cod_localFK"] !== "") {
        $sucursalId = $usuario["cod_localFK"];
        $sucursalNombre = $usuario["nombre_local"];
        if ($sucursalNombre === "") {
            $sucursalNombre = "Local #".$sucursalId;
        }
    }

    $ventas = array();
    if ($titularId !== "") {
        $sqlVentas = "SELECT vt.cod_venta, vt.num_factura, vt.apodo, vt.cod_local, IFNULL(l.Nombre, '') AS sucursal_nombre
                      FROM venta vt
                      LEFT JOIN local l ON l.cod_local = vt.cod_local
                      WHERE vt.cod_clienteFK = ?
                      AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones WHERE cod_venta = vt.cod_venta LIMIT 1), 0) = 0
                      ORDER BY vt.cod_venta DESC
                      LIMIT 100";
        $stmtVentas = $mysqli->prepare($sqlVentas);
        $stmtVentas->bind_param("i", $titularId);
        if ($stmtVentas->execute()) {
            $resultVentas = $stmtVentas->get_result();
            while ($row = $resultVentas->fetch_assoc()) {
                $apodo = ri_utf8($row["apodo"]);
                $numero = ri_utf8($row["num_factura"]);
                $sucursalVentaId = ri_utf8($row["cod_local"]);
                $sucursalVentaNombre = ri_utf8($row["sucursal_nombre"]);
                if ($sucursalVentaId !== "" && $sucursalVentaNombre === "") {
                    $sucursalVentaNombre = "Local #".$sucursalVentaId;
                }
                $ventas[] = array(
                    "venta_id" => ri_utf8($row["cod_venta"]),
                    "numero_venta" => $numero,
                    "apodo_venta" => $apodo,
                    "sucursal_id" => $sucursalVentaId,
                    "sucursal_nombre" => $sucursalVentaNombre,
                    "rotulo" => "Nro. ".($numero !== "" ? $numero : ri_utf8($row["cod_venta"]))." - ".$titularNombre.($apodo !== "" ? " (".$apodo.")" : "")
                );
            }
        }
        $stmtVentas->close();
    }

    $numeroVenta = $venta ? ri_utf8($venta["num_factura"]) : "";
    $apodoVenta = $venta ? ri_utf8($venta["apodo"]) : "";
    $pacienteNombre = $titularNombre;
    if ($apodoVenta !== "") {
        $pacienteNombre = $titularNombre." (".$apodoVenta.")";
    }

    $emisorAutorizado = ri_emisor_recetario_autorizado($usuario);
    $contexto = array(
        "paciente_id" => $titularId,
        "beneficiario_id" => "",
        "paciente_nombre" => $pacienteNombre,
        "titular_id" => $titularId,
        "titular_nombre" => $titularNombre,
        "cedula_titular" => $cedulaTitular,
        "venta_id" => $ventaId,
        "numero_venta" => $numeroVenta,
        "apodo_venta" => $apodoVenta,
        "consulta_id" => $consulta["consulta_id"],
        "consulta_resumen" => $consulta["consulta_resumen"],
        "hilo_id" => $hiloId,
        "hilo_resumen" => $hiloResumen,
        "doctor_id" => $usuario["cod_usuario"],
        "doctor_nombre" => $usuario["nombre_persona"],
        "doctor_tipo" => $usuario["tipo"],
        "doctor_foto_url" => $usuario["foto_url"],
        "doctor_estado" => "Usuario del sistema",
        "perfil_clinico_autorizado" => $emisorAutorizado ? "SI" : "NO",
        "permiso_emitir_recetario" => $emisorAutorizado ? "SI" : "NO",
        "motivo_emisor" => $emisorAutorizado ? "" : ri_mensaje_emisor_no_autorizado(),
        "usuario_actual_id" => $usuario["cod_usuario"],
        "usuario_actual_nombre" => $usuario["nombre_persona"],
        "usuario_actual_foto_url" => $usuario["foto_url"],
        "usuario_emisor_id" => $usuario["cod_usuario"],
        "sucursal_id" => $sucursalId,
        "sucursal_nombre" => $sucursalNombre,
        "fecha_hora" => date("Y-m-d H:i:s"),
        "puede_borrador" => $emisorAutorizado ? "SI" : "NO",
        "puede_emitir" => ri_puede_emitir($usuario) ? "SI" : "NO",
        "puede_anular" => ri_puede_anular($usuario) ? "SI" : "NO",
        "puede_imprimir" => ri_permiso($user, "IMPRIMIRRECETARIOINDICACIONES") ? "SI" : "NO",
        "requiere_confirmar_venta" => count($ventas) > 1 ? "SI" : "NO"
    );

    $alertas = array();
    if (!$emisorAutorizado) {
        $alertas[] = array(
            "tipo" => "doctor",
            "texto" => ri_mensaje_emisor_no_autorizado()
        );
    }
    $mysqli->close();

    return array(
        "contexto" => $contexto,
        "ventas" => $ventas,
        "alertas" => $alertas
    );
}

function ri_obtener_alertas($clienteId, $ventaId)
{
    $alertas = array();
    if ($clienteId === "") {
        return $alertas;
    }

    $mysqli = conectar_al_servidor();
    $sql = "SELECT observacion, fecha
            FROM antecedente_paciente
            WHERE cod_clienteFK = ?
            AND (estado IS NULL OR estado = '' OR estado = 'Activo')";
    if ($ventaId !== "") {
        $sql .= " AND (cod_ventaFK = ? OR cod_ventaFK IS NULL OR cod_ventaFK = 0)";
    }
    $sql .= " ORDER BY fecha DESC, idantecedente_paciente DESC LIMIT 8";

    $stmt = $mysqli->prepare($sql);
    if ($ventaId !== "") {
        $stmt->bind_param("ii", $clienteId, $ventaId);
    } else {
        $stmt->bind_param("i", $clienteId);
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $texto = trim(ri_utf8($row["observacion"]));
            if ($texto !== "") {
                $alertas[] = array("tipo" => "antecedente", "texto" => $texto, "fecha" => ri_utf8($row["fecha"]));
            }
        }
    }
    $stmt->close();

    $sqlObs = "SELECT descripcion FROM observacion_paciente WHERE cod_pacienteFK = ? LIMIT 5";
    $stmtObs = $mysqli->prepare($sqlObs);
    $stmtObs->bind_param("i", $clienteId);
    if ($stmtObs->execute()) {
        $resultObs = $stmtObs->get_result();
        while ($rowObs = $resultObs->fetch_assoc()) {
            $texto = trim(ri_utf8($rowObs["descripcion"]));
            if ($texto !== "") {
                $alertas[] = array("tipo" => "observacion", "texto" => $texto, "fecha" => "");
            }
        }
    }
    $stmtObs->close();
    $mysqli->close();

    return $alertas;
}

function ri_obtener_plantillas()
{
    $mysqli = conectar_al_servidor();
    $plantillas = array();
    $sql = "SELECT id, nombre, categoria, tipo, contenido_json
            FROM recetario_plantillas
            WHERE activo = 1
            ORDER BY categoria ASC, nombre ASC";
    $stmt = $mysqli->prepare($sql);
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $plantillas[] = array(
                "id" => ri_utf8($row["id"]),
                "nombre" => ri_utf8($row["nombre"]),
                "categoria" => ri_utf8($row["categoria"]),
                "tipo" => ri_utf8($row["tipo"]),
                "contenido_json" => ri_utf8($row["contenido_json"])
            );
        }
        $stmt->close();
    }
    $mysqli->close();
    return $plantillas;
}

function ri_plantillas_accion($user)
{
    ri_json(array("1" => "exito", "2" => ri_obtener_plantillas()));
}

function ri_decodificar_lista($key)
{
    $raw = ri_raw_post($key, "[]");
    $lista = json_decode($raw, true);
    return is_array($lista) ? $lista : array();
}

function ri_normalizar_medicamentos($lista)
{
    $medicamentos = array();
    foreach ($lista as $item) {
        if (!is_array($item)) {
            continue;
        }
        $med = array(
            "medicamento" => trim((string)(isset($item["medicamento"]) ? $item["medicamento"] : "")),
            "presentacion" => trim((string)(isset($item["presentacion"]) ? $item["presentacion"] : "")),
            "dosis" => trim((string)(isset($item["dosis"]) ? $item["dosis"] : "")),
            "frecuencia" => trim((string)(isset($item["frecuencia"]) ? $item["frecuencia"] : "")),
            "duracion" => trim((string)(isset($item["duracion"]) ? $item["duracion"] : "")),
            "cantidad" => trim((string)(isset($item["cantidad"]) ? $item["cantidad"] : "")),
            "via" => trim((string)(isset($item["via"]) ? $item["via"] : "")),
            "observaciones" => trim((string)(isset($item["observaciones"]) ? $item["observaciones"] : ""))
        );
        if (implode("", $med) === "") {
            continue;
        }
        $medicamentos[] = $med;
    }
    return $medicamentos;
}

function ri_normalizar_indicaciones($lista)
{
    $indicaciones = array();
    foreach ($lista as $item) {
        if (!is_array($item)) {
            continue;
        }
        $indicacion = array(
            "categoria" => trim((string)(isset($item["categoria"]) ? $item["categoria"] : "")),
            "texto" => trim((string)(isset($item["texto"]) ? $item["texto"] : ""))
        );
        if ($indicacion["texto"] === "") {
            continue;
        }
        $indicaciones[] = $indicacion;
    }
    return $indicaciones;
}

function ri_tipo_documento($medicamentos, $indicaciones)
{
    if (count($medicamentos) > 0 && count($indicaciones) > 0) {
        return "receta_indicacion";
    }
    if (count($medicamentos) > 0) {
        return "receta";
    }
    return "indicacion";
}

function ri_tabla_existe($mysqli, $tabla)
{
    $tablaDb = mysqli_real_escape_string($mysqli, $tabla);
    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = '$tablaDb'";
    $result = $mysqli->query($sql);
    if (!$result) {
        return false;
    }
    $row = $result->fetch_assoc();
    return $row && (int)$row["total"] > 0;
}

function ri_doc_valor($doc, $key)
{
    return isset($doc[$key]) ? trim((string)$doc[$key]) : "";
}

function ri_hash_documento($doc)
{
    $medicamentos = array();
    if (isset($doc["medicamentos"]) && is_array($doc["medicamentos"])) {
        foreach ($doc["medicamentos"] as $med) {
            $medicamentos[] = array(
                "orden" => isset($med["orden"]) ? (int)$med["orden"] : 0,
                "medicamento" => isset($med["medicamento"]) ? trim((string)$med["medicamento"]) : "",
                "presentacion" => isset($med["presentacion"]) ? trim((string)$med["presentacion"]) : "",
                "dosis" => isset($med["dosis"]) ? trim((string)$med["dosis"]) : "",
                "frecuencia" => isset($med["frecuencia"]) ? trim((string)$med["frecuencia"]) : "",
                "duracion" => isset($med["duracion"]) ? trim((string)$med["duracion"]) : "",
                "cantidad" => isset($med["cantidad"]) ? trim((string)$med["cantidad"]) : "",
                "via" => isset($med["via"]) ? trim((string)$med["via"]) : "",
                "observaciones" => isset($med["observaciones"]) ? trim((string)$med["observaciones"]) : ""
            );
        }
    }

    $indicaciones = array();
    if (isset($doc["indicaciones"]) && is_array($doc["indicaciones"])) {
        foreach ($doc["indicaciones"] as $ind) {
            $indicaciones[] = array(
                "orden" => isset($ind["orden"]) ? (int)$ind["orden"] : 0,
                "categoria" => isset($ind["categoria"]) ? trim((string)$ind["categoria"]) : "",
                "texto" => isset($ind["texto"]) ? trim((string)$ind["texto"]) : ""
            );
        }
    }

    $payload = array(
        "id" => ri_doc_valor($doc, "id"),
        "codigo_documento" => ri_doc_valor($doc, "codigo_documento"),
        "paciente_id" => ri_doc_valor($doc, "paciente_id"),
        "paciente_nombre" => ri_doc_valor($doc, "paciente_nombre"),
        "titular_id" => ri_doc_valor($doc, "titular_id"),
        "titular_nombre" => ri_doc_valor($doc, "titular_nombre"),
        "cedula_titular" => ri_doc_valor($doc, "cedula_titular"),
        "venta_id" => ri_doc_valor($doc, "venta_id"),
        "numero_venta" => ri_doc_valor($doc, "numero_venta"),
        "apodo_venta" => ri_doc_valor($doc, "apodo_venta"),
        "sucursal_id" => ri_doc_valor($doc, "sucursal_id"),
        "sucursal_nombre" => ri_doc_valor($doc, "sucursal_nombre"),
        "doctor_id" => ri_doc_valor($doc, "doctor_id"),
        "doctor_nombre" => ri_doc_valor($doc, "doctor_nombre"),
        "usuario_emisor_id" => ri_doc_valor($doc, "usuario_emisor_id"),
        "usuario_emisor_nombre" => ri_doc_valor($doc, "usuario_emisor_nombre"),
        "tipo_documento" => ri_doc_valor($doc, "tipo_documento"),
        "estado" => ri_doc_valor($doc, "estado"),
        "fecha_documento" => ri_doc_valor($doc, "fecha_emision") !== "" ? ri_doc_valor($doc, "fecha_emision") : ri_doc_valor($doc, "created_at"),
        "observaciones_generales" => ri_doc_valor($doc, "observaciones_generales"),
        "medicamentos" => $medicamentos,
        "indicaciones" => $indicaciones
    );

    return hash("sha256", json_encode($payload));
}

function ri_obtener_firma_vigente($mysqli, $recetarioId)
{
    if (!ri_tabla_existe($mysqli, "recetario_firmas")) {
        return null;
    }
    $sql = "SELECT *
            FROM recetario_firmas
            WHERE recetario_id = ?
            AND estado = 'vigente'
            ORDER BY fecha_hora_firma DESC, id DESC
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $recetarioId);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? ri_formatear_firma($row) : null;
}

function ri_obtener_firma_documento($mysqli, $recetarioId)
{
    if (!ri_tabla_existe($mysqli, "recetario_firmas")) {
        return null;
    }
    $sql = "SELECT *
            FROM recetario_firmas
            WHERE recetario_id = ?
            ORDER BY CASE estado WHEN 'vigente' THEN 1 WHEN 'invalida' THEN 2 ELSE 3 END,
                     fecha_hora_firma DESC,
                     id DESC
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $recetarioId);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? ri_formatear_firma($row) : null;
}

function ri_formatear_firma($row)
{
    $firma = array();
    foreach ($row as $key => $value) {
        $firma[$key] = ri_utf8($value);
    }
    return $firma;
}

function ri_aplicar_estado_firma_documento(&$documento, $firma)
{
    $documento["firma"] = null;
    $documento["estado_firma"] = "sin_firma";
    $documento["estado_firma_texto"] = "Sin firma";

    if (!$firma || $firma["estado"] === "anulada") {
        return;
    }

    if ($firma["estado"] === "vigente") {
        $hashActual = ri_hash_documento($documento);
        if (trim($firma["hash_documento"]) !== "" && $firma["hash_documento"] !== $hashActual) {
            $firma["estado"] = "invalida";
            $firma["motivo_estado_visual"] = "Firma invalida por modificacion posterior";
            $documento["firma"] = $firma;
            $documento["estado_firma"] = "invalida";
            $documento["estado_firma_texto"] = "Firma invalida por modificacion posterior";
            return;
        }
        $documento["firma"] = $firma;
        $documento["estado_firma"] = "firmado";
        $documento["estado_firma_texto"] = "Firmado";
        return;
    }

    $firma["motivo_estado_visual"] = "Firma invalida por modificacion posterior";
    $documento["firma"] = $firma;
    $documento["estado_firma"] = "invalida";
    $documento["estado_firma_texto"] = "Firma invalida por modificacion posterior";
}

function ri_decodificar_firma_png($dataUrl)
{
    $dataUrl = trim((string)$dataUrl);
    if ($dataUrl === "") {
        return "";
    }
    $base64 = $dataUrl;
    if (strpos($dataUrl, "data:image/png;base64,") === 0) {
        $base64 = substr($dataUrl, strlen("data:image/png;base64,"));
    }
    $binario = base64_decode($base64, true);
    if ($binario === false || strlen($binario) < 120) {
        return "";
    }
    if (substr($binario, 0, 8) !== "\x89PNG\x0d\x0a\x1a\x0a") {
        return "";
    }
    return $binario;
}

function ri_firma_png_tiene_tinta($binario)
{
    if (!function_exists("imagecreatefromstring")) {
        return true;
    }
    $imagen = @imagecreatefromstring($binario);
    if (!$imagen) {
        return true;
    }
    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);
    $paso = max(1, (int)floor(min($ancho, $alto) / 120));
    $pixelesTinta = 0;
    for ($y = 0; $y < $alto; $y += $paso) {
        for ($x = 0; $x < $ancho; $x += $paso) {
            $color = imagecolorsforindex($imagen, imagecolorat($imagen, $x, $y));
            $alpha = isset($color["alpha"]) ? (int)$color["alpha"] : 0;
            if ($alpha < 100 && ((int)$color["red"] < 245 || (int)$color["green"] < 245 || (int)$color["blue"] < 245)) {
                $pixelesTinta++;
                if ($pixelesTinta > 12) {
                    imagedestroy($imagen);
                    return true;
                }
            }
        }
    }
    imagedestroy($imagen);
    return false;
}

function ri_guardar_imagen_firma($recetarioId, $user, $binario)
{
    $directorio = dirname(__DIR__).DIRECTORY_SEPARATOR."fotos".DIRECTORY_SEPARATOR."recetario_firmas".DIRECTORY_SEPARATOR;
    if (!is_dir($directorio) && !mkdir($directorio, 0777, true)) {
        throw new Exception("No se pudo crear la carpeta de firmas.");
    }
    $nombre = "recetario_".(int)$recetarioId."_usuario_".(int)$user."_".date("Ymd_His")."_".mt_rand(1000, 9999).".png";
    $rutaFisica = $directorio.$nombre;
    if (file_put_contents($rutaFisica, $binario) === false) {
        throw new Exception("No se pudo guardar la imagen de firma.");
    }
    return array(
        "ruta_fisica" => $rutaFisica,
        "ruta_web" => "/GoodVentaAsisCap/fotos/recetario_firmas/".$nombre
    );
}

function ri_ip_cliente()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $partes = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
        return trim($partes[0]);
    }
    return isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
}

function ri_detectar_dispositivo($userAgent)
{
    $ua = strtolower((string)$userAgent);
    if (strpos($ua, "ipad") !== false || strpos($ua, "tablet") !== false) {
        return "tablet";
    }
    if (strpos($ua, "mobile") !== false || strpos($ua, "android") !== false || strpos($ua, "iphone") !== false) {
        return "movil";
    }
    return "computadora";
}

function ri_invalidar_firma_si_cambio($recetarioId, $user, $documento)
{
    $mysqli = conectar_al_servidor();
    if (!ri_tabla_existe($mysqli, "recetario_firmas")) {
        $mysqli->close();
        return;
    }

    $firma = ri_obtener_firma_vigente($mysqli, $recetarioId);
    if (!$firma || trim($firma["hash_documento"]) === "") {
        $mysqli->close();
        return;
    }

    $hashActual = ri_hash_documento($documento);
    if ($firma["hash_documento"] === $hashActual) {
        $mysqli->close();
        return;
    }

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("UPDATE recetario_firmas SET estado = 'invalida', updated_at = NOW() WHERE id = ?");
        $firmaId = (int)$firma["id"];
        $stmt->bind_param("i", $firmaId);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
        ri_insertar_auditoria(
            $mysqli,
            $recetarioId,
            $user,
            "firma_invalidada",
            "Documento modificado despues de la firma. Requiere nueva firma.",
            "",
            json_encode($firma),
            json_encode(array("hash_actual" => $hashActual))
        );
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
    }
    $mysqli->close();
}

function ri_registrar_impresion_firmada($user, $documento)
{
    if (!isset($documento["firma"]) || !is_array($documento["firma"]) || $documento["estado_firma"] !== "firmado") {
        return;
    }

    $mysqli = conectar_al_servidor();
    try {
        ri_insertar_auditoria(
            $mysqli,
            (int)$documento["id"],
            $user,
            "firma_impresa",
            "Documento firmado enviado a impresion o PDF.",
            "",
            "",
            json_encode(array(
                "firma_id" => $documento["firma"]["id"],
                "fecha_hora_firma" => $documento["firma"]["fecha_hora_firma"]
            ))
        );
    } catch (Exception $e) {
    }
    $mysqli->close();
}

function ri_guardar_accion($user, $modo)
{
    $usuario = ri_usuario_actual($user);
    if (!ri_usuario_logueado($usuario)) {
        ri_json(array("1" => "NI", "mensaje" => ri_mensaje_emisor_no_autorizado()));
    }

    $id = ri_post("id");
    $ventaId = ri_post("venta_id");
    $clienteId = ri_post("cliente_id");
    $consultaId = ri_post("consulta_id");
    $hiloId = ri_post("hilo_id");
    $observaciones = trim(ri_raw_post("observaciones_generales", ""));
    $documentoReemplazadoId = ri_post("documento_reemplazado_id");
    $tipoCreacion = ri_post("tipo_creacion");
    $confirmacionVenta = ri_post("confirmacion_venta");
    $medicamentos = ri_normalizar_medicamentos(ri_decodificar_lista("medicamentos"));
    $indicaciones = ri_normalizar_indicaciones(ri_decodificar_lista("indicaciones"));

    if ($modo === "emitir" && count($medicamentos) === 0 && count($indicaciones) === 0) {
        ri_json(array("1" => "camposvacio", "mensaje" => "Debe cargar al menos un medicamento o una indicacion."));
    }

    $medicamentosLimpios = array();
    foreach ($medicamentos as $medicamento) {
        if ($medicamento["medicamento"] === "") {
            if ($modo === "emitir") {
                $medicamento["medicamento"] = "Medicamento";
            } else {
                continue;
            }
        }
        $medicamentosLimpios[] = $medicamento;
    }
    $medicamentos = $medicamentosLimpios;

    $contextoDatos = ri_obtener_contexto($ventaId, $clienteId, $consultaId, $hiloId, $user);
    $contexto = $contextoDatos["contexto"];

    if ((string)$contexto["usuario_emisor_id"] !== (string)$user) {
        ri_json(array("1" => "NI", "mensaje" => ri_mensaje_emisor_no_autorizado()));
    }

    $estado = "borrador";
    if ($modo === "emitir") {
        $estado = ($tipoCreacion === "complementaria" && $documentoReemplazadoId !== "") ? "complementaria" : "emitida";
    }
    $tipoDocumento = ri_tipo_documento($medicamentos, $indicaciones);
    $fechaEmision = $modo === "emitir" ? date("Y-m-d H:i:s") : null;

    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();

    try {
        if ($id !== "") {
            $existente = ri_obtener_documento_simple($mysqli, $id);
            if (!$existente) {
                throw new Exception("El documento no existe.");
            }
            if ($existente["estado"] !== "borrador") {
                throw new Exception("Una receta o indicacion emitida no puede editarse directamente.");
            }

            $sql = "UPDATE recetarios_indicaciones SET
                    paciente_id = ?, beneficiario_id = NULL, titular_id = ?, cedula_titular = ?, nombre_paciente = ?, nombre_titular = ?, venta_id = ?,
                    numero_venta = ?, apodo_venta = ?, consulta_id = ?, hilo_id = ?, doctor_id = ?,
                    usuario_emisor_id = ?, sucursal_id = ?, nombre_doctor = ?, nombre_sucursal = ?, tipo_documento = ?, estado = ?, fecha_emision = ?,
                    documento_reemplazado_id = ?, observaciones_generales = ?, cod_usuario_editFK = ?
                    WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                throw new Exception("No se pudo preparar el guardado del recetario. Verifique la migracion de contexto: ".$mysqli->error);
            }
            $cedula = ri_db($contexto["cedula_titular"]);
            $nombrePaciente = ri_db($contexto["paciente_nombre"]);
            $nombreTitular = ri_db($contexto["titular_nombre"]);
            $numeroVenta = ri_db($contexto["numero_venta"]);
            $apodoVenta = ri_db($contexto["apodo_venta"]);
            $nombreDoctor = ri_db($contexto["doctor_nombre"]);
            $nombreSucursal = ri_db($contexto["sucursal_nombre"]);
            $tipoDocumentoDb = ri_db($tipoDocumento);
            $estadoDb = ri_db($estado);
            $fechaEmisionDb = $fechaEmision;
            $observacionesDb = ri_db($observaciones);
            $pacienteIdDb = ri_int_o_null($contexto["paciente_id"]);
            $titularIdDb = ri_int_o_null($contexto["titular_id"]);
            $ventaIdDb = ri_int_o_null($contexto["venta_id"]);
            $consultaIdDb = $contexto["consulta_id"] !== "" ? (int)$contexto["consulta_id"] : null;
            $hiloIdDb = $contexto["hilo_id"] !== "" ? (int)$contexto["hilo_id"] : null;
            $doctorIdDb = ri_int_o_null($contexto["doctor_id"]);
            if ($doctorIdDb === null) {
                $doctorIdDb = (int)$user;
            }
            $usuarioEmisorIdDb = (int)$contexto["usuario_emisor_id"];
            $sucursalIdDb = ri_int_o_null($contexto["sucursal_id"]);
            $documentoReemplazadoDb = $documentoReemplazadoId !== "" ? (int)$documentoReemplazadoId : null;
            $stmt->bind_param(
                "iisssissiiiiisssssisii",
                $pacienteIdDb,
                $titularIdDb,
                $cedula,
                $nombrePaciente,
                $nombreTitular,
                $ventaIdDb,
                $numeroVenta,
                $apodoVenta,
                $consultaIdDb,
                $hiloIdDb,
                $doctorIdDb,
                $usuarioEmisorIdDb,
                $sucursalIdDb,
                $nombreDoctor,
                $nombreSucursal,
                $tipoDocumentoDb,
                $estadoDb,
                $fechaEmisionDb,
                $documentoReemplazadoDb,
                $observacionesDb,
                $user,
                $id
            );
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();

            $stmtDeleteMed = $mysqli->prepare("DELETE FROM recetario_medicamentos WHERE recetario_id = ?");
            $stmtDeleteMed->bind_param("i", $id);
            $stmtDeleteMed->execute();
            $stmtDeleteMed->close();

            $stmtDeleteInd = $mysqli->prepare("DELETE FROM recetario_indicaciones_detalle WHERE recetario_id = ?");
            $stmtDeleteInd->bind_param("i", $id);
            $stmtDeleteInd->execute();
            $stmtDeleteInd->close();

            $recetarioId = (int)$id;
        } else {
            $sql = "INSERT INTO recetarios_indicaciones (
                        paciente_id, beneficiario_id, titular_id, cedula_titular, nombre_paciente, nombre_titular, venta_id, numero_venta, apodo_venta,
                        consulta_id, hilo_id, doctor_id, usuario_emisor_id, sucursal_id, nombre_doctor, nombre_sucursal, tipo_documento, estado,
                        fecha_emision, documento_reemplazado_id, observaciones_generales, cod_usuario_createFK, cod_usuario_editFK
                    ) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                throw new Exception("No se pudo preparar el guardado del recetario. Verifique la migracion de contexto: ".$mysqli->error);
            }
            $cedula = ri_db($contexto["cedula_titular"]);
            $nombrePaciente = ri_db($contexto["paciente_nombre"]);
            $nombreTitular = ri_db($contexto["titular_nombre"]);
            $numeroVenta = ri_db($contexto["numero_venta"]);
            $apodoVenta = ri_db($contexto["apodo_venta"]);
            $nombreDoctor = ri_db($contexto["doctor_nombre"]);
            $nombreSucursal = ri_db($contexto["sucursal_nombre"]);
            $tipoDocumentoDb = ri_db($tipoDocumento);
            $estadoDb = ri_db($estado);
            $fechaEmisionDb = $fechaEmision;
            $observacionesDb = ri_db($observaciones);
            $pacienteIdDb = ri_int_o_null($contexto["paciente_id"]);
            $titularIdDb = ri_int_o_null($contexto["titular_id"]);
            $ventaIdDb = ri_int_o_null($contexto["venta_id"]);
            $consultaIdDb = $contexto["consulta_id"] !== "" ? (int)$contexto["consulta_id"] : null;
            $hiloIdDb = $contexto["hilo_id"] !== "" ? (int)$contexto["hilo_id"] : null;
            $doctorIdDb = ri_int_o_null($contexto["doctor_id"]);
            if ($doctorIdDb === null) {
                $doctorIdDb = (int)$user;
            }
            $usuarioEmisorIdDb = (int)$contexto["usuario_emisor_id"];
            $sucursalIdDb = ri_int_o_null($contexto["sucursal_id"]);
            $documentoReemplazadoDb = $documentoReemplazadoId !== "" ? (int)$documentoReemplazadoId : null;
            $stmt->bind_param(
                "iisssissiiiiisssssisii",
                $pacienteIdDb,
                $titularIdDb,
                $cedula,
                $nombrePaciente,
                $nombreTitular,
                $ventaIdDb,
                $numeroVenta,
                $apodoVenta,
                $consultaIdDb,
                $hiloIdDb,
                $doctorIdDb,
                $usuarioEmisorIdDb,
                $sucursalIdDb,
                $nombreDoctor,
                $nombreSucursal,
                $tipoDocumentoDb,
                $estadoDb,
                $fechaEmisionDb,
                $documentoReemplazadoDb,
                $observacionesDb,
                $user,
                $user
            );
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $recetarioId = $stmt->insert_id;
            $stmt->close();

            $codigoDocumento = "RI-".date("Ymd")."-".str_pad($recetarioId, 6, "0", STR_PAD_LEFT);
            $stmtCodigo = $mysqli->prepare("UPDATE recetarios_indicaciones SET codigo_documento = ? WHERE id = ?");
            $stmtCodigo->bind_param("si", $codigoDocumento, $recetarioId);
            if (!$stmtCodigo->execute()) {
                throw new Exception($stmtCodigo->error);
            }
            $stmtCodigo->close();
        }

        ri_insertar_detalles($mysqli, $recetarioId, $medicamentos, $indicaciones);
        ri_insertar_auditoria(
            $mysqli,
            $recetarioId,
            $user,
            $modo === "emitir" ? $estado : "borrador_guardado",
            $modo === "emitir" ? "Documento emitido desde Recetario e Indicaciones." : "Borrador guardado.",
            "",
            "",
            json_encode(array("confirmacion_venta" => $confirmacionVenta, "contexto" => $contexto, "medicamentos" => $medicamentos, "indicaciones" => $indicaciones))
        );

        $mysqli->commit();
        $documento = ri_obtener_documento($recetarioId);
        if ($documento) {
            ri_invalidar_firma_si_cambio($recetarioId, $user, $documento);
            $documento = ri_obtener_documento($recetarioId);
        }
        if ($documento) {
            ri_aplicar_estado_emisor_documento($documento, $usuario, $user);
        }
        ri_json(array("1" => "exito", "2" => $recetarioId, "3" => $documento));
    } catch (Exception $e) {
        $mysqli->rollback();
        ri_json(array("1" => "error", "mensaje" => $e->getMessage()));
    }
}

function ri_insertar_detalles($mysqli, $recetarioId, $medicamentos, $indicaciones)
{
    if (count($medicamentos) > 0) {
        $sqlMed = "INSERT INTO recetario_medicamentos
                    (recetario_id, medicamento, presentacion, dosis, frecuencia, duracion, cantidad, via, observaciones, orden)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtMed = $mysqli->prepare($sqlMed);
        $orden = 1;
        foreach ($medicamentos as $med) {
            $medicamento = ri_db($med["medicamento"]);
            $presentacion = ri_db($med["presentacion"]);
            $dosis = ri_db($med["dosis"]);
            $frecuencia = ri_db($med["frecuencia"]);
            $duracion = ri_db($med["duracion"]);
            $cantidad = ri_db($med["cantidad"]);
            $via = ri_db($med["via"]);
            $observaciones = ri_db($med["observaciones"]);
            $stmtMed->bind_param(
                "issssssssi",
                $recetarioId,
                $medicamento,
                $presentacion,
                $dosis,
                $frecuencia,
                $duracion,
                $cantidad,
                $via,
                $observaciones,
                $orden
            );
            if (!$stmtMed->execute()) {
                throw new Exception($stmtMed->error);
            }
            $orden++;
        }
        $stmtMed->close();
    }

    if (count($indicaciones) > 0) {
        $sqlInd = "INSERT INTO recetario_indicaciones_detalle (recetario_id, categoria, texto, orden)
                   VALUES (?, ?, ?, ?)";
        $stmtInd = $mysqli->prepare($sqlInd);
        $orden = 1;
        foreach ($indicaciones as $indicacion) {
            $categoria = ri_db($indicacion["categoria"]);
            $texto = ri_db($indicacion["texto"]);
            $stmtInd->bind_param("issi", $recetarioId, $categoria, $texto, $orden);
            if (!$stmtInd->execute()) {
                throw new Exception($stmtInd->error);
            }
            $orden++;
        }
        $stmtInd->close();
    }
}

function ri_insertar_auditoria($mysqli, $recetarioId, $user, $accion, $descripcion, $motivo, $anteriores, $nuevos)
{
    $sql = "INSERT INTO recetario_auditoria
            (recetario_id, usuario_id, accion, descripcion, motivo, fecha_hora, datos_anteriores, datos_nuevos)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $accionDb = ri_db($accion);
    $descripcionDb = ri_db($descripcion);
    $motivoDb = ri_db($motivo);
    $anterioresDb = ri_db($anteriores);
    $nuevosDb = ri_db($nuevos);
    $stmt->bind_param("iisssss", $recetarioId, $user, $accionDb, $descripcionDb, $motivoDb, $anterioresDb, $nuevosDb);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    $stmt->close();
}

function ri_firmar_documento_accion($user)
{
    $usuario = ri_usuario_actual($user);
    if (!ri_usuario_logueado($usuario)) {
        ri_json(array("1" => "NI", "mensaje" => ri_mensaje_emisor_no_autorizado()));
    }

    $id = ri_post("id");
    $firmaData = ri_raw_post("firma_imagen", "");
    if ($id === "") {
        ri_json(array("1" => "camposvacio", "mensaje" => "Debe guardar el documento antes de firmar."));
    }

    $documento = ri_obtener_documento($id);
    if (!$documento) {
        ri_json(array("1" => "NoExiste", "mensaje" => "El documento no existe."));
    }
    if ($documento["estado"] === "anulada") {
        ri_json(array("1" => "error", "mensaje" => "No se puede firmar un documento anulado."));
    }
    if (count($documento["medicamentos"]) === 0 && count($documento["indicaciones"]) === 0) {
        ri_json(array("1" => "camposvacio", "mensaje" => "Agregue al menos un medicamento o una indicacion antes de firmar."));
    }

    $binario = ri_decodificar_firma_png($firmaData);
    if ($binario === "" || !ri_firma_png_tiene_tinta($binario)) {
        ri_json(array("1" => "camposvacio", "mensaje" => "Debe realizar una firma antes de confirmar."));
    }

    $mysqli = conectar_al_servidor();
    if (!ri_tabla_existe($mysqli, "recetario_firmas")) {
        $mysqli->close();
        ri_json(array("1" => "error", "mensaje" => "Falta aplicar la migracion actualizacion_050626_recetario_firmas.sql."));
    }

    $archivo = null;
    $mysqli->begin_transaction();
    try {
        $firmaRegistrada = ri_obtener_firma_documento($mysqli, (int)$id);
        $firmaAnterior = ri_obtener_firma_vigente($mysqli, (int)$id);
        if ($firmaAnterior) {
            $firmaAnteriorId = (int)$firmaAnterior["id"];
            $stmtAnular = $mysqli->prepare("UPDATE recetario_firmas SET estado = 'anulada', updated_at = NOW() WHERE id = ?");
            $stmtAnular->bind_param("i", $firmaAnteriorId);
            if (!$stmtAnular->execute()) {
                throw new Exception($stmtAnular->error);
            }
            $stmtAnular->close();
        }

        $archivo = ri_guardar_imagen_firma($id, $user, $binario);
        $hashDocumento = ri_hash_documento($documento);
        $hashFirma = hash("sha256", $binario);
        $ip = substr(ri_ip_cliente(), 0, 45);
        $userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? substr($_SERVER["HTTP_USER_AGENT"], 0, 500) : "";
        $dispositivo = ri_detectar_dispositivo($userAgent);
        $nombreFirmante = ri_db($usuario["nombre_persona"]);
        $usuarioFirmanteId = (int)$usuario["cod_usuario"];
        $usuarioEmisorId = (int)$documento["usuario_emisor_id"];
        $nombreEmisor = ri_db($documento["usuario_emisor_nombre"] !== "" ? $documento["usuario_emisor_nombre"] : $documento["doctor_nombre"]);
        $rutaWeb = ri_db($archivo["ruta_web"]);
        $estado = "vigente";
        $firmaBase64 = null;

        $sql = "INSERT INTO recetario_firmas
                (recetario_id, usuario_firmante_id, nombre_firmante_snapshot, usuario_emisor_id, nombre_emisor_snapshot,
                 firma_imagen_path, firma_base64, fecha_hora_firma, hash_documento, hash_firma, ip, user_agent, dispositivo, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception($mysqli->error);
        }
        $stmt->bind_param(
            "iisisssssssss",
            $id,
            $usuarioFirmanteId,
            $nombreFirmante,
            $usuarioEmisorId,
            $nombreEmisor,
            $rutaWeb,
            $firmaBase64,
            $hashDocumento,
            $hashFirma,
            $ip,
            $userAgent,
            $dispositivo,
            $estado
        );
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $firmaId = $stmt->insert_id;
        $stmt->close();

        $esReemplazo = $firmaRegistrada && $firmaRegistrada["estado"] !== "anulada";
        ri_insertar_auditoria(
            $mysqli,
            (int)$id,
            $user,
            $esReemplazo ? "firma_reemplazada" : "firma_confirmada",
            $esReemplazo ? "Firma manuscrita digitalizada reemplazada." : "Firma manuscrita digitalizada confirmada.",
            "",
            $firmaRegistrada ? json_encode($firmaRegistrada) : "",
            json_encode(array("firma_id" => $firmaId, "hash_documento" => $hashDocumento, "hash_firma" => $hashFirma))
        );

        $mysqli->commit();
        $mysqli->close();

        $documentoActualizado = ri_obtener_documento($id);
        if ($documentoActualizado) {
            ri_aplicar_estado_emisor_documento($documentoActualizado, $usuario, $user);
        }
        ri_json(array("1" => "exito", "2" => $documentoActualizado, "mensaje" => "Firma guardada correctamente."));
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        if ($archivo && isset($archivo["ruta_fisica"]) && file_exists($archivo["ruta_fisica"])) {
            @unlink($archivo["ruta_fisica"]);
        }
        ri_json(array("1" => "error", "mensaje" => $e->getMessage()));
    }
}

function ri_obtener_documento_simple($mysqli, $id)
{
    $stmt = $mysqli->prepare("SELECT id, estado FROM recetarios_indicaciones WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return null;
    }
    return array("id" => ri_utf8($row["id"]), "estado" => ri_utf8($row["estado"]));
}

function ri_obtener_documento($id)
{
    $mysqli = conectar_al_servidor();
    $sql = "SELECT r.*,
                   COALESCE(NULLIF(r.nombre_paciente, ''), pt.nombre_persona) AS paciente_nombre_snapshot,
                   COALESCE(NULLIF(r.nombre_titular, ''), pt.nombre_persona) AS titular_nombre_snapshot,
                   cl.ci_cliente,
                   COALESCE(NULLIF(r.nombre_doctor, ''), pd.nombre_persona) AS doctor_nombre_snapshot,
                   ud.url AS doctor_foto_url_snapshot,
                   pe.nombre_persona AS usuario_emisor_nombre,
                   COALESCE(NULLIF(r.nombre_sucursal, ''), l.Nombre) AS sucursal_nombre_snapshot,
                   c.trabajo_realizado,
                   c.motivoconsulta,
                   c.diagnostico,
                   h.asunto AS hilo_asunto
            FROM recetarios_indicaciones r
            LEFT JOIN cliente ct ON ct.cod_cliente = r.titular_id
            LEFT JOIN persona pt ON pt.cod_persona = ct.cod_cliente
            LEFT JOIN cliente cl ON cl.cod_cliente = r.titular_id
            LEFT JOIN persona pd ON pd.cod_persona = r.doctor_id
            LEFT JOIN usuario ud ON ud.cod_usuario = r.doctor_id
            LEFT JOIN persona pe ON pe.cod_persona = r.usuario_emisor_id
            LEFT JOIN local l ON l.cod_local = r.sucursal_id
            LEFT JOIN consulta c ON c.cod_consulta = r.consulta_id
            LEFT JOIN interconsulta h ON h.cod_interConsulta = r.hilo_id
            WHERE r.id = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        ri_json(array("1" => "error", "mensaje" => $stmt->error));
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $mysqli->close();
        return null;
    }

    $documento = array();
    foreach ($row as $key => $value) {
        $documento[$key] = ri_utf8($value);
    }

    $documento["paciente_nombre"] = $documento["paciente_nombre_snapshot"];
    $documento["titular_nombre"] = $documento["titular_nombre_snapshot"];
    $documento["doctor_nombre"] = $documento["doctor_nombre_snapshot"];
    $documento["doctor_foto_url"] = $documento["doctor_foto_url_snapshot"];
    $documento["sucursal_nombre"] = $documento["sucursal_nombre_snapshot"];
    $documento["consulta_resumen"] = trim($documento["trabajo_realizado"]) !== ""
        ? $documento["trabajo_realizado"]
        : (trim($documento["motivoconsulta"]) !== "" ? $documento["motivoconsulta"] : $documento["diagnostico"]);

    $documento["medicamentos"] = array();
    $sqlMed = "SELECT medicamento, presentacion, dosis, frecuencia, duracion, cantidad, via, observaciones, orden
               FROM recetario_medicamentos
               WHERE recetario_id = ?
               ORDER BY orden ASC, id ASC";
    $stmtMed = $mysqli->prepare($sqlMed);
    $stmtMed->bind_param("i", $id);
    if ($stmtMed->execute()) {
        $resultMed = $stmtMed->get_result();
        while ($med = $resultMed->fetch_assoc()) {
            $documento["medicamentos"][] = array(
                "medicamento" => ri_utf8($med["medicamento"]),
                "presentacion" => ri_utf8($med["presentacion"]),
                "dosis" => ri_utf8($med["dosis"]),
                "frecuencia" => ri_utf8($med["frecuencia"]),
                "duracion" => ri_utf8($med["duracion"]),
                "cantidad" => ri_utf8($med["cantidad"]),
                "via" => ri_utf8($med["via"]),
                "observaciones" => ri_utf8($med["observaciones"]),
                "orden" => ri_utf8($med["orden"])
            );
        }
    }
    $stmtMed->close();

    $documento["indicaciones"] = array();
    $sqlInd = "SELECT categoria, texto, orden
               FROM recetario_indicaciones_detalle
               WHERE recetario_id = ?
               ORDER BY orden ASC, id ASC";
    $stmtInd = $mysqli->prepare($sqlInd);
    $stmtInd->bind_param("i", $id);
    if ($stmtInd->execute()) {
        $resultInd = $stmtInd->get_result();
        while ($ind = $resultInd->fetch_assoc()) {
            $documento["indicaciones"][] = array(
                "categoria" => ri_utf8($ind["categoria"]),
                "texto" => ri_utf8($ind["texto"]),
                "orden" => ri_utf8($ind["orden"])
            );
        }
    }
    $stmtInd->close();

    $documento["auditoria"] = array();
    $sqlAud = "SELECT a.accion, a.descripcion, a.motivo, a.fecha_hora, p.nombre_persona AS usuario
               FROM recetario_auditoria a
               LEFT JOIN persona p ON p.cod_persona = a.usuario_id
               WHERE a.recetario_id = ?
               ORDER BY a.fecha_hora DESC, a.id DESC
               LIMIT 30";
    $stmtAud = $mysqli->prepare($sqlAud);
    $stmtAud->bind_param("i", $id);
    if ($stmtAud->execute()) {
        $resultAud = $stmtAud->get_result();
        while ($aud = $resultAud->fetch_assoc()) {
            $documento["auditoria"][] = array(
                "accion" => ri_utf8($aud["accion"]),
                "descripcion" => ri_utf8($aud["descripcion"]),
                "motivo" => ri_utf8($aud["motivo"]),
                "fecha_hora" => ri_utf8($aud["fecha_hora"]),
                "usuario" => ri_utf8($aud["usuario"])
            );
        }
    }
    $stmtAud->close();

    $firma = ri_obtener_firma_documento($mysqli, (int)$id);
    ri_aplicar_estado_firma_documento($documento, $firma);

    $mysqli->close();

    return $documento;
}

function ri_listar_accion($user)
{
    $usuario = ri_usuario_actual($user);
    $puedeImprimir = ri_permiso($user, "IMPRIMIRRECETARIOINDICACIONES");
    $puedeEmitir = ri_puede_emitir($usuario);
    $puedeAnular = ri_puede_anular($usuario);

    $mysqli = conectar_al_servidor();
    $filtros = array("1=1");

    $ventaId = ri_post("venta_id");
    $clienteId = ri_post("cliente_id");
    $consultaId = ri_post("consulta_id");
    $hiloId = ri_post("hilo_id");
    $doctorId = ri_post("doctor_id");
    $estado = ri_post("estado");
    $tipo = ri_post("tipo_documento");
    $fechaDesde = ri_post("fecha_desde");
    $fechaHasta = ri_post("fecha_hasta");
    $buscar = ri_post("buscar");

    if ($ventaId !== "") { $filtros[] = "r.venta_id = ".(int)$ventaId; }
    if ($clienteId !== "") { $filtros[] = "(r.paciente_id = ".(int)$clienteId." OR r.titular_id = ".(int)$clienteId.")"; }
    if ($consultaId !== "") { $filtros[] = "r.consulta_id = ".(int)$consultaId; }
    if ($hiloId !== "") { $filtros[] = "r.hilo_id = ".(int)$hiloId; }
    if ($doctorId !== "") { $filtros[] = "r.doctor_id = ".(int)$doctorId; }
    if ($estado !== "") { $filtros[] = "r.estado = '".mysqli_real_escape_string($mysqli, $estado)."'"; }
    if ($tipo !== "") { $filtros[] = "r.tipo_documento = '".mysqli_real_escape_string($mysqli, $tipo)."'"; }
    if ($fechaDesde !== "") { $filtros[] = "DATE(IFNULL(r.fecha_emision, r.created_at)) >= '".mysqli_real_escape_string($mysqli, $fechaDesde)."'"; }
    if ($fechaHasta !== "") { $filtros[] = "DATE(IFNULL(r.fecha_emision, r.created_at)) <= '".mysqli_real_escape_string($mysqli, $fechaHasta)."'"; }
    if ($buscar !== "") {
        $buscarEsc = mysqli_real_escape_string($mysqli, $buscar);
        $filtros[] = "(r.codigo_documento LIKE '%$buscarEsc%' OR r.numero_venta LIKE '%$buscarEsc%' OR r.apodo_venta LIKE '%$buscarEsc%' OR r.cedula_titular LIKE '%$buscarEsc%' OR r.nombre_paciente LIKE '%$buscarEsc%' OR r.nombre_titular LIKE '%$buscarEsc%' OR r.nombre_doctor LIKE '%$buscarEsc%' OR (SELECT nombre_persona FROM persona WHERE cod_persona = r.titular_id) LIKE '%$buscarEsc%')";
    }

    $where = implode(" AND ", $filtros);
    $sql = "SELECT r.id
            FROM recetarios_indicaciones r
            WHERE $where
            ORDER BY IFNULL(r.fecha_emision, r.created_at) DESC, r.id DESC
            LIMIT 120";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        ri_json(array("1" => "error", "mensaje" => $mysqli->error, "sql" => $sql));
    }
    $result = $stmt->get_result();
    $ids = array();
    while ($row = $result->fetch_assoc()) {
        $ids[] = (int)$row["id"];
    }
    $stmt->close();
    $mysqli->close();

    $grupos = array(
        "borradores" => "",
        "emitidas" => "",
        "anuladas" => ""
    );
    $conteos = array(
        "borradores" => 0,
        "emitidas" => 0,
        "anuladas" => 0
    );
    foreach ($ids as $id) {
        $doc = ri_obtener_documento($id);
        if ($doc) {
            $grupo = ri_grupo_historial_recetario($doc);
            $grupos[$grupo] .= ri_render_card($doc, $puedeImprimir, $puedeEmitir, $puedeAnular);
            $conteos[$grupo]++;
        }
    }

    $html = "";
    if ($conteos["borradores"] > 0) {
        $html .= ri_render_grupo_historial_recetario("Borradores", $grupos["borradores"], $conteos["borradores"]);
    }
    if ($conteos["emitidas"] > 0) {
        $html .= ri_render_grupo_historial_recetario("Emitidas", $grupos["emitidas"], $conteos["emitidas"]);
    }
    if ($conteos["anuladas"] > 0) {
        $html .= ri_render_grupo_historial_recetario("Anuladas", $grupos["anuladas"], $conteos["anuladas"]);
    }

    if ($html === "") {
        $html = "<div class='recetario-empty'>No hay recetas o indicaciones con los filtros seleccionados.</div>";
    }

    ri_json(array("1" => "exito", "2" => $html, "3" => count($ids)));
}

function ri_grupo_historial_recetario($doc)
{
    if ($doc["estado"] === "borrador") {
        return "borradores";
    }
    if ($doc["estado"] === "anulada") {
        return "anuladas";
    }
    return "emitidas";
}

function ri_render_grupo_historial_recetario($titulo, $contenido, $cantidad)
{
    return "
        <section class='recetario-history-section'>
            <div class='recetario-history-section__title'>
                <strong>".ri_h($titulo)."</strong>
                <span>".(int)$cantidad."</span>
            </div>
            <div class='recetario-history-section__items'>".$contenido."</div>
        </section>";
}

function ri_render_card_firma($doc, $codigo)
{
    $firma = isset($doc["firma"]) && is_array($doc["firma"]) ? $doc["firma"] : null;
    $estadoFirma = isset($doc["estado_firma"]) ? $doc["estado_firma"] : "sin_firma";

    if (!$firma || trim($firma["firma_imagen_path"]) === "") {
        return "
            <div class='recetario-card-firma recetario-card-firma--sin'>
                <strong>Firma del emisor</strong>
                <span>Firma: Sin firma</span>
            </div>";
    }

    $estadoVisual = "Firmado";
    $claseEstado = "firmado";
    if ($estadoFirma === "invalida") {
        $estadoVisual = "Firma requiere actualizacion";
        $claseEstado = "invalida";
    } elseif ($doc["estado"] === "borrador") {
        $estadoVisual = "Firma capturada en borrador";
        $claseEstado = "borrador";
    }

    $imagen = $firma["firma_imagen_path"];
    $firmante = $firma["nombre_firmante_snapshot"];
    $fechaFirma = $firma["fecha_hora_firma"];
    $onclick = "abrirVistaFirmaRecetarioIndicaciones("
        .ri_js($imagen).","
        .ri_js($firmante).","
        .ri_js($fechaFirma).","
        .ri_js($estadoVisual).","
        .ri_js($codigo)
        .")";

    return "
        <div class='recetario-card-firma recetario-card-firma--".$claseEstado."'>
            <strong>Firma del emisor</strong>
            <button type='button' class='recetario-card-firma__preview' onclick='".$onclick."' title='Ver firma del emisor'>
                <img src='".ri_h($imagen)."' alt='Firma del emisor'>
            </button>
            <div class='recetario-card-firma__meta'>
                <em>".ri_h($estadoVisual)."</em>
                <span>".ri_h($firmante)."</span>
                <small>Firmado el ".ri_h($fechaFirma)."</small>
            </div>
        </div>";
}

function ri_render_card($doc, $puedeImprimir = true, $puedeEmitir = true, $puedeAnular = true)
{
    $estado = ri_h($doc["estado"]);
    $codigo = trim($doc["codigo_documento"]) !== "" ? $doc["codigo_documento"] : "Borrador #".$doc["id"];
    $fecha = trim($doc["fecha_emision"]) !== "" ? $doc["fecha_emision"] : $doc["created_at"];
    $fechaVisual = strlen($fecha) >= 16 ? substr($fecha, 0, 16) : $fecha;
    $venta = trim($doc["numero_venta"]) !== "" ? $doc["numero_venta"] : $doc["venta_id"];
    $apodo = trim($doc["apodo_venta"]) !== "" ? " - ".ri_h($doc["apodo_venta"]) : "";
    $firmaHtml = ri_render_card_firma($doc, $codigo);

    $acciones = "
        <button type='button' onclick='verDetalleRecetarioIndicaciones(".(int)$doc["id"].")'>Ver</button>";

    if ($puedeImprimir) {
        $acciones .= "
        <button type='button' onclick='imprimirRecetarioIndicaciones(".(int)$doc["id"].")'>Imprimir</button>
        <button type='button' onclick='imprimirRecetarioIndicaciones(".(int)$doc["id"].")'>PDF</button>";
    }

    if ($doc["estado"] !== "anulada" && $doc["estado"] !== "borrador") {
        if ($puedeAnular) {
            $acciones .= "
        <button type='button' onclick='abrirAnularRecetarioIndicaciones(".(int)$doc["id"].")'>Anular</button>";
        }
        if ($puedeEmitir) {
            $acciones .= "
        <button type='button' onclick='abrirComplementariaRecetarioIndicaciones(".(int)$doc["id"].")'>Complementaria</button>";
        }
    }

    return "
    <article class='recetario-card recetario-card--".$estado."'>
        <div class='recetario-card__header'>
            <div>
                <span>Recetario e Indicaciones</span>
                <strong>".ri_h($codigo)."</strong>
            </div>
            <em>".strtoupper($estado)."</em>
        </div>
        <div class='recetario-card__body'>
            <p><b>Paciente:</b> ".ri_h($doc["paciente_nombre"])."</p>
            <p><b>Venta:</b> Nro. ".ri_h($venta).$apodo."</p>
            <p><b>Emisor:</b> ".ri_h($doc["doctor_nombre"])."</p>
            <p><b>Fecha:</b> ".ri_h($fechaVisual)."</p>
            ".$firmaHtml."
        </div>
        <div class='recetario-card__actions'>".$acciones."</div>
    </article>";
}

function ri_detalle_accion($user)
{
    $id = ri_post("id");
    $doc = ri_obtener_documento($id);
    if (!$doc) {
        ri_json(array("1" => "NoExiste"));
    }
    $usuario = ri_usuario_actual($user);
    ri_aplicar_estado_emisor_documento($doc, $usuario, $user);
    ri_json(array("1" => "exito", "2" => $doc));
}

function ri_imprimir_accion($user)
{
    if (!ri_permiso($user, "IMPRIMIRRECETARIOINDICACIONES")) {
        ri_json(array("1" => "NI"));
    }
    $id = ri_post("id");
    $doc = ri_obtener_documento($id);
    if (!$doc) {
        ri_json(array("1" => "NoExiste"));
    }
    ri_registrar_impresion_firmada($user, $doc);
    ri_json(array("1" => "exito", "2" => ri_render_print($doc)));
}

function ri_render_print($doc)
{
    $fecha = trim($doc["fecha_emision"]) !== "" ? $doc["fecha_emision"] : $doc["created_at"];
    $venta = trim($doc["numero_venta"]) !== "" ? $doc["numero_venta"] : $doc["venta_id"];
    $apodo = trim($doc["apodo_venta"]) !== "" ? " - ".$doc["apodo_venta"] : "";
    $consulta = trim($doc["consulta_resumen"]) !== "" ? $doc["consulta_resumen"] : "Sin consulta relacionada";
    $hilo = "";
    if (trim($doc["hilo_id"]) !== "") {
        $hilo = "Hilo #".$doc["hilo_id"];
        if (trim($doc["hilo_asunto"]) !== "") {
            $hilo .= " - ".$doc["hilo_asunto"];
        }
    }

    $medicamentosHtml = "";
    if (count($doc["medicamentos"]) > 0) {
        foreach ($doc["medicamentos"] as $med) {
            $detalle = array();
            if (trim($med["presentacion"]) !== "") { $detalle[] = ri_h($med["presentacion"]); }
            if (trim($med["dosis"]) !== "") { $detalle[] = ri_h($med["dosis"]); }
            if (trim($med["frecuencia"]) !== "") { $detalle[] = ri_h($med["frecuencia"]); }
            if (trim($med["duracion"]) !== "") { $detalle[] = ri_h($med["duracion"]); }
            if (trim($med["cantidad"]) !== "") { $detalle[] = "Cant.: ".ri_h($med["cantidad"]); }
            if (trim($med["via"]) !== "") { $detalle[] = "Via: ".ri_h($med["via"]); }
            $observaciones = trim($med["observaciones"]) !== "" ? "<small>".ri_h($med["observaciones"])."</small>" : "";
            $medicamentosHtml .= "
                <tr>
                    <td>".ri_h($med["orden"])."</td>
                    <td><strong>".ri_h($med["medicamento"])."</strong><br>".$observaciones."</td>
                    <td>".implode("<br>", $detalle)."</td>
                </tr>";
        }
    } else {
        $medicamentosHtml = "<tr><td colspan='3'>Sin medicamentos cargados.</td></tr>";
    }

    $indicacionesHtml = "";
    if (count($doc["indicaciones"]) > 0) {
        foreach ($doc["indicaciones"] as $ind) {
            $categoria = trim($ind["categoria"]) !== "" ? "<strong>".ri_h($ind["categoria"])."</strong><br>" : "";
            $indicacionesHtml .= "<li>".$categoria.nl2br(ri_h($ind["texto"]))."</li>";
        }
    } else {
        $indicacionesHtml = "<li>Sin indicaciones cargadas.</li>";
    }

    $motivoAnulacion = "";
    if ($doc["estado"] === "anulada") {
        $motivoAnulacion = "<div class='ri-print-alert'><strong>Documento anulado.</strong><br>Motivo: ".ri_h($doc["motivo_anulacion"])."</div>";
    }

    $firmaHtml = "";
    if (isset($doc["firma"]) && is_array($doc["firma"]) && $doc["estado_firma"] === "firmado") {
        $firmaHtml = "
            <div class='ri-print-sign ri-print-sign--firmada'>
                <img src='".ri_h($doc["firma"]["firma_imagen_path"])."' alt='Firma del emisor'>
                <div class='ri-print-sign-line'></div>
                <strong>".ri_h($doc["firma"]["nombre_firmante_snapshot"])."</strong><br>
                Emisor del documento<br>
                <small>Firmado el ".ri_h($doc["firma"]["fecha_hora_firma"])."</small>
            </div>";
    } else {
        $estadoFirma = isset($doc["estado_firma_texto"]) && $doc["estado_firma_texto"] !== "Sin firma"
            ? "<br><small>".ri_h($doc["estado_firma_texto"])."</small>"
            : "";
        $firmaHtml = "<div class='ri-print-sign ri-print-sign--pendiente'>".ri_h($doc["doctor_nombre"])."<br>Emisor del documento".$estadoFirma."</div>";
    }

    return "
    <style>
        .ri-print { font-family: Arial, sans-serif; color:#172033; padding:22px; max-width:900px; margin:0 auto; }
        .ri-print-header { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; border-bottom:2px solid #1f7a6b; padding-bottom:14px; }
        .ri-print-header img { width:150px; max-height:90px; object-fit:contain; }
        .ri-print-title { text-align:right; }
        .ri-print-title h1 { margin:0; font-size:24px; color:#145c52; }
        .ri-print-title p { margin:4px 0; font-size:12px; }
        .ri-print-meta { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:8px 18px; margin:18px 0; font-size:13px; }
        .ri-print-meta span { border-bottom:1px solid #dbe4ea; padding-bottom:5px; }
        .ri-print-section { margin-top:18px; }
        .ri-print-section h2 { font-size:16px; margin:0 0 8px; color:#145c52; border-bottom:1px solid #dbe4ea; padding-bottom:5px; }
        .ri-print-table { width:100%; border-collapse:collapse; font-size:13px; }
        .ri-print-table th, .ri-print-table td { border:1px solid #dbe4ea; padding:8px; vertical-align:top; text-align:left; }
        .ri-print-table th { background:#eef6f5; }
        .ri-print-indicaciones { margin:0; padding-left:22px; font-size:13px; }
        .ri-print-indicaciones li { margin-bottom:10px; }
        .ri-print-footer { display:flex; justify-content:space-between; gap:22px; margin-top:32px; align-items:flex-end; }
        .ri-print-sign { width:280px; text-align:center; font-size:12px; }
        .ri-print-sign--pendiente { border-top:1px solid #172033; padding-top:8px; }
        .ri-print-sign--firmada { padding-top:0; }
        .ri-print-sign img { display:block; width:auto; max-width:240px; max-height:82px; object-fit:contain; margin:0 auto 2px; }
        .ri-print-sign-line { width:240px; margin:0 auto 7px; border-top:1px solid #172033; }
        .ri-print-sign small { color:#64748b; }
        .ri-print-qr { width:110px; height:110px; border:1px dashed #64748b; display:flex; align-items:center; justify-content:center; font-size:11px; text-align:center; color:#64748b; }
        .ri-print-alert { margin-top:12px; padding:10px; background:#fff2f2; border:1px solid #f3bbbb; color:#9d1c1c; }
        @media print { .ri-print { padding:0; } }
    </style>
    <div class='ri-print'>
        <div class='ri-print-header'>
            <img src='/GoodVentaAsisCap/iconos/Logo.jpg' alt='Clinident Salud'>
            <div class='ri-print-title'>
                <h1>Recetario e Indicaciones</h1>
                <p>Codigo interno: ".ri_h($doc["codigo_documento"])."</p>
                <p>Estado: ".ri_h($doc["estado"])."</p>
                <p>Firma: ".ri_h($doc["estado_firma_texto"])."</p>
                <p>Fecha: ".ri_h($fecha)."</p>
            </div>
        </div>
        ".$motivoAnulacion."
        <div class='ri-print-meta'>
            <span><b>Paciente/beneficiario:</b> ".ri_h($doc["paciente_nombre"])."</span>
            <span><b>Titular contractual:</b> ".ri_h($doc["titular_nombre"])."</span>
            <span><b>Cedula titular:</b> ".ri_h($doc["cedula_titular"])."</span>
            <span><b>Venta vinculada:</b> Nro. ".ri_h($venta.$apodo)."</span>
            <span><b>Sucursal:</b> ".ri_h($doc["sucursal_nombre"])."</span>
            <span><b>Emisor:</b> ".ri_h($doc["doctor_nombre"])."</span>
            <span><b>Consulta relacionada:</b> ".ri_h($consulta)."</span>
            <span><b>Usuario emisor:</b> ".ri_h($doc["usuario_emisor_nombre"])."</span>
            ".($hilo !== "" ? "<span><b>Hilo relacionado:</b> ".ri_h($hilo)."</span>" : "")."
        </div>
        <div class='ri-print-section'>
            <h2>RP / Receta</h2>
            <table class='ri-print-table'>
                <thead><tr><th>#</th><th>Medicamento</th><th>Indicacion de uso</th></tr></thead>
                <tbody>".$medicamentosHtml."</tbody>
            </table>
        </div>
        <div class='ri-print-section'>
            <h2>Indicaciones al paciente</h2>
            <ol class='ri-print-indicaciones'>".$indicacionesHtml."</ol>
        </div>
        <div class='ri-print-footer'>
            <div>
                <strong>Clinident Salud</strong><br>
                Documento generado desde el sistema clinico-administrativo.<br>
                Conservar este documento para controles posteriores.
            </div>
            ".$firmaHtml."
            <div class='ri-print-qr'>Espacio preparado<br>para QR de verificacion</div>
        </div>
    </div>";
}

function ri_anular_accion($user)
{
    $usuario = ri_usuario_actual($user);
    if (!ri_puede_anular($usuario)) {
        ri_json(array("1" => "NI", "mensaje" => "No tiene permiso para anular documentos clinicos."));
    }

    $id = ri_post("id");
    $motivo = trim(ri_raw_post("motivo"));
    if ($id === "" || $motivo === "") {
        ri_json(array("1" => "camposvacio", "mensaje" => "Debe indicar el documento y el motivo de anulacion."));
    }

    $mysqli = conectar_al_servidor();
    $mysqli->begin_transaction();
    try {
        $doc = ri_obtener_documento_simple($mysqli, $id);
        if (!$doc) {
            throw new Exception("El documento no existe.");
        }
        if ($doc["estado"] === "anulada") {
            throw new Exception("El documento ya se encuentra anulado.");
        }
        if ($doc["estado"] === "borrador") {
            throw new Exception("Un borrador no requiere anulacion formal.");
        }

        $motivoDb = ri_db($motivo);
        $stmt = $mysqli->prepare("UPDATE recetarios_indicaciones SET estado = 'anulada', motivo_anulacion = ?, cod_usuario_editFK = ? WHERE id = ?");
        $stmt->bind_param("sii", $motivoDb, $user, $id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();

        ri_insertar_auditoria($mysqli, $id, $user, "anulada", "Documento anulado con motivo obligatorio.", $motivo, json_encode($doc), "");
        $mysqli->commit();
        ri_json(array("1" => "exito", "2" => ri_obtener_documento($id)));
    } catch (Exception $e) {
        $mysqli->rollback();
        ri_json(array("1" => "error", "mensaje" => $e->getMessage()));
    }
}

$operacion = isset($_POST["funt"]) ? ri_post("funt") : "";
ri_verificar_sesion($operacion);
?>

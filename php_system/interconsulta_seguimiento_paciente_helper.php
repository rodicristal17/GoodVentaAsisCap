<?php
if (!function_exists('conectar_al_servidor')) {
    require_once(__DIR__ . "/conexion.php");
}

function seguimientoPacienteNormalizarCedula($cedula) {
    $cedula = strtoupper(trim((string)$cedula));
    $cedula = preg_replace('/[^A-Z0-9]+/', '', $cedula);
    return $cedula;
}

function seguimientoPacienteSqlNormalizar($campo) {
    return "REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(IFNULL(".$campo.", ''))), '.', ''), '-', ''), ' ', ''), '/', '')";
}

function seguimientoPacienteEntero($valor) {
    return is_numeric($valor) ? (int)$valor : 0;
}

function seguimientoPacienteTablaExiste($mysqli, $tabla) {
    static $cache = array();
    if (isset($cache[$tabla])) {
        return $cache[$tabla];
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$tabla)) {
        $cache[$tabla] = false;
        return false;
    }
    $tablaEscapada = $mysqli->real_escape_string($tabla);
    $result = $mysqli->query("SHOW TABLES LIKE '".$tablaEscapada."'");
    if (!$result) {
        $cache[$tabla] = false;
        return false;
    }
    $existe = $result && $result->num_rows > 0;
    if ($result) { $result->free(); }
    $cache[$tabla] = $existe;
    return $existe;
}

function seguimientoPacienteColumnaExiste($mysqli, $tabla, $columna) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$tabla)) { return false; }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$columna)) { return false; }
    $columnaEscapada = $mysqli->real_escape_string($columna);
    $result = $mysqli->query("SHOW COLUMNS FROM `".$tabla."` LIKE '".$columnaEscapada."'");
    if (!$result) { return false; }
    $existe = $result && $result->num_rows > 0;
    if ($result) { $result->free(); }
    return $existe;
}

function seguimientoPacienteAsegurarIndice($mysqli, $tabla, $indice, $columnas) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$tabla)
        || !preg_match('/^[a-zA-Z0-9_]+$/', (string)$indice)
        || !seguimientoPacienteTablaExiste($mysqli, $tabla)) {
        return false;
    }

    $indiceEscapado = $mysqli->real_escape_string($indice);
    $result = $mysqli->query("SHOW INDEX FROM `".$tabla."` WHERE Key_name = '".$indiceEscapado."'");
    if (!$result) { return false; }
    $existe = $result && $result->num_rows > 0;
    if ($result) { $result->free(); }
    if ($existe) {
        return true;
    }

    $columnasLimpias = array();
    foreach ((array)$columnas as $columna) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$columna)
            || !seguimientoPacienteColumnaExiste($mysqli, $tabla, $columna)) {
            return false;
        }
        $columnasLimpias[] = "`".$columna."`";
    }
    if (count($columnasLimpias) == 0) {
        return false;
    }

    return $mysqli->query("ALTER TABLE `".$tabla."` ADD INDEX `".$indice."` (".implode(",", $columnasLimpias).")") ? true : false;
}

function asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli = null) {
    static $estructuraVerificada = false;
    $cerrarConexion = false;
    if ($mysqli === null) {
        $mysqli = conectar_al_servidor();
        $cerrarConexion = true;
    }
    if (!$mysqli || $mysqli->connect_errno) { return false; }

    if ($estructuraVerificada) {
        if ($cerrarConexion) { $mysqli->close(); }
        return true;
    }

    if (seguimientoPacienteTablaExiste($mysqli, 'interconsulta_paciente')
        && seguimientoPacienteTablaExiste($mysqli, 'interconsulta_paciente_venta')) {
        $estructuraVerificada = true;
        if ($cerrarConexion) { $mysqli->close(); }
        return true;
    }

    $sqlPaciente = "CREATE TABLE IF NOT EXISTS interconsulta_paciente (
        id INT NOT NULL AUTO_INCREMENT,
        cod_interConsultaFK INT NOT NULL,
        cedula VARCHAR(60) NOT NULL,
        cedula_normalizada VARCHAR(60) NOT NULL,
        cod_clienteFK_principal INT NULL,
        nombre_paciente_snapshot VARCHAR(150) NULL,
        estado_conflicto TINYINT(1) NOT NULL DEFAULT 0,
        detalle_conflicto VARCHAR(255) NULL,
        ventas_sin_plan_madre INT NOT NULL DEFAULT 0,
        total_ventas INT NOT NULL DEFAULT 0,
        total_planes_madre INT NOT NULL DEFAULT 0,
        origen VARCHAR(30) NOT NULL DEFAULT 'automatico',
        estado VARCHAR(20) NOT NULL DEFAULT 'activo',
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        cod_usuarioFK_create INT NULL,
        fecha_actualizacion DATETIME NULL,
        cod_usuarioFK_update INT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_interconsulta_paciente_cedula (cedula_normalizada),
        KEY idx_interconsulta_paciente_hilo (cod_interConsultaFK),
        KEY idx_interconsulta_paciente_cliente (cod_clienteFK_principal),
        KEY idx_interconsulta_paciente_conflicto (estado_conflicto),
        KEY idx_interconsulta_paciente_estado (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci";

    $sqlVenta = "CREATE TABLE IF NOT EXISTS interconsulta_paciente_venta (
        id INT NOT NULL AUTO_INCREMENT,
        cod_interConsultaFK INT NOT NULL,
        cod_ventaFK INT NOT NULL,
        cedula_normalizada VARCHAR(60) NOT NULL,
        cod_clienteFK INT NULL,
        plan_madre_id INT NULL,
        estado VARCHAR(20) NOT NULL DEFAULT 'activo',
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        cod_usuarioFK_create INT NULL,
        fecha_actualizacion DATETIME NULL,
        cod_usuarioFK_update INT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_interconsulta_paciente_venta (cod_ventaFK),
        KEY idx_interconsulta_paciente_venta_hilo (cod_interConsultaFK),
        KEY idx_interconsulta_paciente_venta_cedula (cedula_normalizada),
        KEY idx_interconsulta_paciente_venta_plan (plan_madre_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci";

    $ok = $mysqli->query($sqlPaciente) && $mysqli->query($sqlVenta);
    if ($ok) {
        $estructuraVerificada = true;
    }
    if ($cerrarConexion) { $mysqli->close(); }
    return $ok;
}

function seguimientoPacienteObtenerVentaReal($mysqli, $codVenta) {
    $codVenta = seguimientoPacienteEntero($codVenta);
    if ($codVenta <= 0) {
        return array("ok" => false, "motivo" => "venta_vacia");
    }

    $exprCedula = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    $sql = "SELECT vt.cod_venta, vt.cod_clienteFK, vt.cod_usuarioFK, vt.cod_local, vt.num_factura,
            vt.fecha_venta, vt.total_venta, cl.ci_cliente, ".$exprCedula." AS cedula_normalizada,
            p.nombre_persona,
            IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta LIMIT 1),0) AS cancelado
        FROM venta vt
        INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
        LEFT JOIN persona p ON p.cod_persona = vt.cod_clienteFK
        WHERE vt.cod_venta = ?
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array("ok" => false, "motivo" => "prepare_venta");
    }
    $stmt->bind_param("i", $codVenta);
    if (!$stmt->execute()) {
        $stmt->close();
        return array("ok" => false, "motivo" => "execute_venta");
    }
    $result = $stmt->get_result();
    $venta = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$venta) { return array("ok" => false, "motivo" => "venta_no_encontrada"); }
    if ((int)$venta["cancelado"] > 0) { return array("ok" => false, "motivo" => "venta_cancelada"); }
    if ((int)$venta["cod_clienteFK"] <= 0 || (int)$venta["cod_clienteFK"] == 7) {
        return array("ok" => false, "motivo" => "cliente_no_real");
    }
    if (trim((string)$venta["cedula_normalizada"]) == "") {
        return array("ok" => false, "motivo" => "sin_cedula");
    }

    return array("ok" => true, "venta" => $venta);
}

function seguimientoPacienteObtenerClientesPorCedula($mysqli, $cedulaNormalizada) {
    $exprCedula = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    $sql = "SELECT cl.cod_cliente, cl.ci_cliente, p.nombre_persona
        FROM cliente cl
        LEFT JOIN persona p ON p.cod_persona = cl.cod_cliente
        WHERE ".$exprCedula." = ?
        ORDER BY cl.cod_cliente ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return array(); }
    $stmt->bind_param("s", $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $result = $stmt->get_result();
    $registros = array();
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    $stmt->close();
    return $registros;
}

function seguimientoPacienteContarVentasCedula($mysqli, $cedulaNormalizada) {
    $exprCedula = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    $sql = "SELECT COUNT(DISTINCT vt.cod_venta) AS total
        FROM venta vt
        INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
        WHERE vt.cod_clienteFK <> 7
        AND ".$exprCedula." = ?
        AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta LIMIT 1),0)=0";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("s", $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return isset($row["total"]) ? (int)$row["total"] : 0;
}

function seguimientoPacienteContarPlanesMadreCedula($mysqli, $cedulaNormalizada) {
    if (!seguimientoPacienteTablaExiste($mysqli, "plan_definitivo_tratamiento")) {
        return 0;
    }
    $exprCedulaPlan = seguimientoPacienteSqlNormalizar("pd.cedula");
    $exprCedulaCliente = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    $sql = "SELECT COUNT(DISTINCT pd.id) AS total
        FROM plan_definitivo_tratamiento pd
        LEFT JOIN cliente cl ON cl.cod_cliente = pd.paciente_id
        WHERE pd.activo = 1
        AND (".$exprCedulaPlan." = ? OR ".$exprCedulaCliente." = ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("ss", $cedulaNormalizada, $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return isset($row["total"]) ? (int)$row["total"] : 0;
}

function seguimientoPacientePlanVenta($mysqli, $codVenta) {
    if (!seguimientoPacienteTablaExiste($mysqli, "plan_definitivo_tratamiento")
        || !seguimientoPacienteTablaExiste($mysqli, "plan_definitivo_tratamiento_items")) {
        return 0;
    }

    $codVenta = seguimientoPacienteEntero($codVenta);
    $sql = "SELECT pd.id
        FROM plan_definitivo_tratamiento pd
        WHERE pd.activo = 1
        AND (
            pd.venta_base_id = ?
            OR EXISTS (
                SELECT 1
                FROM plan_definitivo_tratamiento_items pi
                WHERE pi.plan_definitivo_id = pd.id
                AND pi.venta_id = ?
                AND pi.activo = 1
                LIMIT 1
            )
        )
        ORDER BY pd.fecha_actualizacion DESC, pd.id DESC
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("ii", $codVenta, $codVenta);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && isset($row["id"]) ? (int)$row["id"] : 0;
}

function seguimientoPacienteContarVentasSinPlanMadreCedula($mysqli, $cedulaNormalizada) {
    $exprCedula = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    if (!seguimientoPacienteTablaExiste($mysqli, "plan_definitivo_tratamiento")
        || !seguimientoPacienteTablaExiste($mysqli, "plan_definitivo_tratamiento_items")) {
        return seguimientoPacienteContarVentasCedula($mysqli, $cedulaNormalizada);
    }

    $sql = "SELECT COUNT(DISTINCT vt.cod_venta) AS total
        FROM venta vt
        INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
        WHERE vt.cod_clienteFK <> 7
        AND ".$exprCedula." = ?
        AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta LIMIT 1),0)=0
        AND NOT EXISTS (
            SELECT 1
            FROM plan_definitivo_tratamiento pd
            WHERE pd.activo = 1
            AND (
                pd.venta_base_id = vt.cod_venta
                OR EXISTS (
                    SELECT 1
                    FROM plan_definitivo_tratamiento_items pi
                    WHERE pi.plan_definitivo_id = pd.id
                    AND pi.venta_id = vt.cod_venta
                    AND pi.activo = 1
                    LIMIT 1
                )
            )
            LIMIT 1
        )";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("s", $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return isset($row["total"]) ? (int)$row["total"] : 0;
}

function seguimientoPacienteResumenCedula($mysqli, $cedulaNormalizada, $codClientePreferido) {
    $clientes = seguimientoPacienteObtenerClientesPorCedula($mysqli, $cedulaNormalizada);
    $principal = null;
    foreach ($clientes as $cliente) {
        if ((string)$cliente["cod_cliente"] == (string)$codClientePreferido) {
            $principal = $cliente;
            break;
        }
    }
    if (!$principal && count($clientes) > 0) {
        $principal = $clientes[0];
    }

    $detalleConflicto = "";
    if (count($clientes) > 1) {
        $partes = array();
        foreach ($clientes as $cliente) {
            $partes[] = "Cliente ".$cliente["cod_cliente"]." - ".trim((string)$cliente["nombre_persona"]);
        }
        $detalleConflicto = substr(implode("; ", $partes), 0, 250);
    }

    return array(
        "cliente_principal" => $principal,
        "estado_conflicto" => count($clientes) > 1 ? 1 : 0,
        "detalle_conflicto" => $detalleConflicto,
        "total_clientes" => count($clientes),
        "total_ventas" => seguimientoPacienteContarVentasCedula($mysqli, $cedulaNormalizada),
        "ventas_sin_plan_madre" => seguimientoPacienteContarVentasSinPlanMadreCedula($mysqli, $cedulaNormalizada),
        "total_planes_madre" => seguimientoPacienteContarPlanesMadreCedula($mysqli, $cedulaNormalizada)
    );
}

function seguimientoPacienteAsuntoHilo($nombrePaciente, $cedulaNormalizada) {
    $nombrePaciente = trim(preg_replace('/\s+/', ' ', (string)$nombrePaciente));
    if ($nombrePaciente == "") {
        $nombrePaciente = "Paciente sin nombre";
    }
    $asunto = $nombrePaciente." - CI ".$cedulaNormalizada;
    return substr($asunto, 0, 100);
}

function seguimientoPacienteBuscarRelacion($mysqli, $cedulaNormalizada) {
    $sql = "SELECT * FROM interconsulta_paciente WHERE cedula_normalizada = ? AND estado = 'activo' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return null; }
    $stmt->bind_param("s", $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? $row : null;
}

function seguimientoPacienteBuscarHiloPorAsunto($mysqli, $asunto) {
    $sql = "SELECT cod_interConsulta
        FROM interconsulta
        WHERE asunto = ?
        AND TRIM(IFNULL(tipo, '')) = ''
        AND cod_ventaFK IS NULL
        ORDER BY cod_interConsulta ASC
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("s", $asunto);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && isset($row["cod_interConsulta"]) ? (int)$row["cod_interConsulta"] : 0;
}

function seguimientoPacienteBuscarHiloHistoricoVentaCedula($mysqli, $cedulaNormalizada, $codPreferido = 0) {
    $cedulaNormalizada = seguimientoPacienteNormalizarCedula($cedulaNormalizada);
    $codPreferido = seguimientoPacienteEntero($codPreferido);
    if ($cedulaNormalizada == "") {
        return 0;
    }

    $exprCedula = seguimientoPacienteSqlNormalizar("cl_hist.ci_cliente");
    $sql = "SELECT ic.cod_interConsulta
        FROM interconsulta ic
        INNER JOIN venta vt_hist ON vt_hist.cod_venta = ic.cod_ventaFK
        INNER JOIN cliente cl_hist ON cl_hist.cod_cliente = vt_hist.cod_clienteFK
        WHERE IFNULL(ic.cod_ventaFK,0) > 0
        AND vt_hist.cod_clienteFK <> 7
        AND ".$exprCedula." = ?
        ORDER BY
            CASE WHEN ic.cod_interConsulta = ? THEN 0 ELSE 1 END,
            CASE WHEN LOWER(TRIM(IFNULL(ic.estado,''))) <> 'inactivo' THEN 0 ELSE 1 END,
            ic.cod_interConsulta ASC
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("si", $cedulaNormalizada, $codPreferido);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && isset($row["cod_interConsulta"]) ? (int)$row["cod_interConsulta"] : 0;
}

function seguimientoPacienteAsegurarHiloMaestroActivo($mysqli, $codInterConsulta, $usuario) {
    $codInterConsulta = seguimientoPacienteEntero($codInterConsulta);
    $usuario = seguimientoPacienteEntero($usuario);
    if ($codInterConsulta <= 0) {
        return false;
    }

    $sql = "UPDATE interconsulta
        SET estado = 'proceso',
            cod_usuarioFK_edit = NULLIF(?,0),
            fecha_edit = NOW()
        WHERE cod_interConsulta = ?
        AND LOWER(TRIM(IFNULL(estado,''))) = 'inactivo'";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param("ii", $usuario, $codInterConsulta);
    $ok = $stmt->execute();
    $afectadas = $stmt->affected_rows;
    $stmt->close();
    return $ok && $afectadas > 0;
}

function seguimientoPacienteCrearHiloMaestro($mysqli, $asunto, $venta, $usuario) {
    $usuario = seguimientoPacienteEntero($usuario);
    if ($usuario <= 0 && isset($venta["cod_usuarioFK"])) {
        $usuario = seguimientoPacienteEntero($venta["cod_usuarioFK"]);
    }
    $codLocal = isset($venta["cod_local"]) ? seguimientoPacienteEntero($venta["cod_local"]) : 0;
    $observacion = "Hilo maestro automatico para seguimiento integral por cedula.";

    $sql = "INSERT INTO interconsulta
        (asunto, observacion, estado, tipo, cod_ventaFK, cod_usuarioFK_create, fecha_creacion, cod_localFK, monto_limite)
        VALUES (?, ?, 'proceso', '', NULL, NULLIF(?,0), NOW(), NULLIF(?,0), 0)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("ssii", $asunto, $observacion, $usuario, $codLocal);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $id = (int)$stmt->insert_id;
    $stmt->close();
    return $id;
}

function seguimientoPacienteRegistrarMensajeInicial($mysqli, $codInterConsulta, $usuario, $resumen) {
    $codInterConsulta = seguimientoPacienteEntero($codInterConsulta);
    if ($codInterConsulta <= 0) { return false; }
    $usuario = seguimientoPacienteEntero($usuario);
    $contenido = "Hilo maestro creado automaticamente para seguimiento por cedula. Ventas reales: "
        .(int)$resumen["total_ventas"].". Planes madre: "
        .(int)$resumen["total_planes_madre"].". Ventas sin plan madre: "
        .(int)$resumen["ventas_sin_plan_madre"].".";
    if ((int)$resumen["estado_conflicto"] == 1) {
        $contenido .= " Aviso: cedula asociada a mas de un cliente.";
    }
    $contenido = substr($contenido, 0, 740);

    $sql = "INSERT INTO mensaje (contenido, estado, cod_interConsultaFK, cod_usuarioFK, fecha_creacion)
        VALUES (?, 'activo', ?, NULLIF(?,0), NOW())";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param("sii", $contenido, $codInterConsulta, $usuario);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function seguimientoPacienteGuardarRelacion($mysqli, $codInterConsulta, $cedulaNormalizada, $venta, $resumen, $usuario) {
    $relacion = seguimientoPacienteBuscarRelacion($mysqli, $cedulaNormalizada);
    $usuario = seguimientoPacienteEntero($usuario);
    if ($usuario <= 0 && isset($venta["cod_usuarioFK"])) {
        $usuario = seguimientoPacienteEntero($venta["cod_usuarioFK"]);
    }

    $clientePrincipal = isset($resumen["cliente_principal"]) ? $resumen["cliente_principal"] : null;
    $codClientePrincipal = $clientePrincipal && isset($clientePrincipal["cod_cliente"]) ? seguimientoPacienteEntero($clientePrincipal["cod_cliente"]) : seguimientoPacienteEntero($venta["cod_clienteFK"]);
    $nombrePaciente = $clientePrincipal && isset($clientePrincipal["nombre_persona"]) ? trim((string)$clientePrincipal["nombre_persona"]) : trim((string)$venta["nombre_persona"]);
    $cedula = $cedulaNormalizada;
    $conflicto = (int)$resumen["estado_conflicto"];
    $detalleConflicto = (string)$resumen["detalle_conflicto"];
    $ventasSinPlan = (int)$resumen["ventas_sin_plan_madre"];
    $totalVentas = (int)$resumen["total_ventas"];
    $totalPlanes = (int)$resumen["total_planes_madre"];

    if ($relacion) {
        $sql = "UPDATE interconsulta_paciente SET
            cod_interConsultaFK = ?,
            cedula = ?,
            cod_clienteFK_principal = ?,
            nombre_paciente_snapshot = ?,
            estado_conflicto = ?,
            detalle_conflicto = ?,
            ventas_sin_plan_madre = ?,
            total_ventas = ?,
            total_planes_madre = ?,
            fecha_actualizacion = NOW(),
            cod_usuarioFK_update = NULLIF(?,0),
            estado = 'activo'
            WHERE cedula_normalizada = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) { return false; }
        $stmt->bind_param(
            "isisisiiiis",
            $codInterConsulta,
            $cedula,
            $codClientePrincipal,
            $nombrePaciente,
            $conflicto,
            $detalleConflicto,
            $ventasSinPlan,
            $totalVentas,
            $totalPlanes,
            $usuario,
            $cedulaNormalizada
        );
    } else {
        $sql = "INSERT INTO interconsulta_paciente
            (cod_interConsultaFK, cedula, cedula_normalizada, cod_clienteFK_principal, nombre_paciente_snapshot,
            estado_conflicto, detalle_conflicto, ventas_sin_plan_madre, total_ventas, total_planes_madre,
            origen, estado, fecha_creacion, cod_usuarioFK_create)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'automatico', 'activo', NOW(), NULLIF(?,0))";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) { return false; }
        $stmt->bind_param(
            "issisisiiii",
            $codInterConsulta,
            $cedula,
            $cedulaNormalizada,
            $codClientePrincipal,
            $nombrePaciente,
            $conflicto,
            $detalleConflicto,
            $ventasSinPlan,
            $totalVentas,
            $totalPlanes,
            $usuario
        );
    }
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function seguimientoPacienteVincularVenta($mysqli, $codInterConsulta, $venta, $cedulaNormalizada, $usuario) {
    $codVenta = seguimientoPacienteEntero($venta["cod_venta"]);
    $codCliente = seguimientoPacienteEntero($venta["cod_clienteFK"]);
    $usuario = seguimientoPacienteEntero($usuario);
    if ($usuario <= 0 && isset($venta["cod_usuarioFK"])) {
        $usuario = seguimientoPacienteEntero($venta["cod_usuarioFK"]);
    }
    $planMadreId = seguimientoPacientePlanVenta($mysqli, $codVenta);

    $sql = "INSERT INTO interconsulta_paciente_venta
        (cod_interConsultaFK, cod_ventaFK, cedula_normalizada, cod_clienteFK, plan_madre_id, estado, fecha_creacion, cod_usuarioFK_create)
        VALUES (?, ?, ?, ?, NULLIF(?,0), 'activo', NOW(), NULLIF(?,0))
        ON DUPLICATE KEY UPDATE
            cod_interConsultaFK = VALUES(cod_interConsultaFK),
            cedula_normalizada = VALUES(cedula_normalizada),
            cod_clienteFK = VALUES(cod_clienteFK),
            plan_madre_id = VALUES(plan_madre_id),
            estado = 'activo',
            fecha_actualizacion = NOW(),
            cod_usuarioFK_update = NULLIF(?,0)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param("iisiiii", $codInterConsulta, $codVenta, $cedulaNormalizada, $codCliente, $planMadreId, $usuario, $usuario);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function seguimientoPacienteVentasPorCedula($mysqli, $cedulaNormalizada) {
    $exprCedula = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    $sql = "SELECT vt.cod_venta, vt.cod_clienteFK, vt.cod_usuarioFK, vt.cod_local, vt.num_factura,
            vt.fecha_venta, vt.total_venta, cl.ci_cliente, ".$exprCedula." AS cedula_normalizada,
            p.nombre_persona
        FROM venta vt
        INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
        LEFT JOIN persona p ON p.cod_persona = vt.cod_clienteFK
        WHERE vt.cod_clienteFK <> 7
        AND ".$exprCedula." = ?
        AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta LIMIT 1),0)=0
        ORDER BY vt.cod_venta ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return array(); }
    $stmt->bind_param("s", $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $result = $stmt->get_result();
    $ventas = array();
    while ($row = $result->fetch_assoc()) {
        $ventas[] = $row;
    }
    $stmt->close();
    return $ventas;
}

function seguimientoPacienteVincularVentasCedula($mysqli, $codInterConsulta, $cedulaNormalizada, $usuario) {
    $ventas = seguimientoPacienteVentasPorCedula($mysqli, $cedulaNormalizada);
    $vinculadas = 0;
    foreach ($ventas as $venta) {
        if (seguimientoPacienteVincularVenta($mysqli, $codInterConsulta, $venta, $cedulaNormalizada, $usuario)) {
            $vinculadas++;
        }
    }
    return $vinculadas;
}

function seguimientoPacienteAsegurarHiloPorVentaConConexion($mysqli, $codVenta, $usuario = 0, $origen = "venta") {
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion");
    }
    if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
        return array("ok" => false, "motivo" => "estructura_no_disponible");
    }

    $datosVenta = seguimientoPacienteObtenerVentaReal($mysqli, $codVenta);
    if (!$datosVenta["ok"]) {
        return $datosVenta;
    }

    $venta = $datosVenta["venta"];
    $usuario = seguimientoPacienteEntero($usuario);
    if ($usuario <= 0 && isset($venta["cod_usuarioFK"])) {
        $usuario = seguimientoPacienteEntero($venta["cod_usuarioFK"]);
    }

    $cedulaNormalizada = seguimientoPacienteNormalizarCedula($venta["cedula_normalizada"]);
    $resumen = seguimientoPacienteResumenCedula($mysqli, $cedulaNormalizada, $venta["cod_clienteFK"]);
    $clientePrincipal = isset($resumen["cliente_principal"]) ? $resumen["cliente_principal"] : null;
    $nombrePaciente = $clientePrincipal && isset($clientePrincipal["nombre_persona"]) ? $clientePrincipal["nombre_persona"] : $venta["nombre_persona"];
    $asunto = seguimientoPacienteAsuntoHilo($nombrePaciente, $cedulaNormalizada);

    $creado = false;
    $codInterConsulta = seguimientoPacienteBuscarHiloHistoricoVentaCedula($mysqli, $cedulaNormalizada, 0);
    $relacion = seguimientoPacienteBuscarRelacion($mysqli, $cedulaNormalizada);
    if ($codInterConsulta <= 0 && $relacion) {
        $codInterConsulta = seguimientoPacienteEntero($relacion["cod_interConsultaFK"]);
    }
    if ($codInterConsulta <= 0) {
        $codInterConsulta = seguimientoPacienteBuscarHiloPorAsunto($mysqli, $asunto);
        if ($codInterConsulta <= 0) {
            $codInterConsulta = seguimientoPacienteCrearHiloMaestro($mysqli, $asunto, $venta, $usuario);
            $creado = $codInterConsulta > 0;
        }
    }

    if ($codInterConsulta <= 0) {
        return array("ok" => false, "motivo" => "no_se_pudo_crear_hilo");
    }

    $hiloReactivado = seguimientoPacienteAsegurarHiloMaestroActivo($mysqli, $codInterConsulta, $usuario);
    seguimientoPacienteGuardarRelacion($mysqli, $codInterConsulta, $cedulaNormalizada, $venta, $resumen, $usuario);
    $asuntoActualizado = seguimientoPacienteActualizarAsuntoDestino($mysqli, $codInterConsulta, $asunto, $usuario);
    $ventasVinculadas = seguimientoPacienteVincularVentasCedula($mysqli, $codInterConsulta, $cedulaNormalizada, $usuario);
    if ($creado) {
        seguimientoPacienteRegistrarMensajeInicial($mysqli, $codInterConsulta, $usuario, $resumen);
    }

    return array(
        "ok" => true,
        "cod_interConsulta" => $codInterConsulta,
        "cedula" => $cedulaNormalizada,
        "creado" => $creado,
        "conflicto" => (int)$resumen["estado_conflicto"],
        "ventas_sin_plan_madre" => (int)$resumen["ventas_sin_plan_madre"],
        "total_ventas" => (int)$resumen["total_ventas"],
        "total_planes_madre" => (int)$resumen["total_planes_madre"],
        "ventas_vinculadas" => $ventasVinculadas,
        "asunto_actualizado" => $asuntoActualizado ? 1 : 0,
        "hilo_reactivado" => $hiloReactivado ? 1 : 0,
        "origen" => $origen
    );
}

function seguimientoPacienteAsegurarHiloPorVenta($codVenta, $usuario = 0, $origen = "venta") {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion");
    }
    $resultado = seguimientoPacienteAsegurarHiloPorVentaConConexion($mysqli, $codVenta, $usuario, $origen);
    $mysqli->close();
    return $resultado;
}

function seguimientoPacienteCedulasVentasHistoricas($mysqli, $limite = 0) {
    $exprCedula = seguimientoPacienteSqlNormalizar("cl.ci_cliente");
    $limiteSql = seguimientoPacienteEntero($limite) > 0 ? " LIMIT ".seguimientoPacienteEntero($limite) : "";
    $sql = "SELECT ".$exprCedula." AS cedula_normalizada, MAX(vt.cod_venta) AS cod_venta_ref
        FROM venta vt
        INNER JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK
        WHERE vt.cod_clienteFK <> 7
        AND IFNULL((SELECT COUNT(*) FROM cancelaciones c WHERE c.cod_venta = vt.cod_venta LIMIT 1),0)=0
        GROUP BY ".$exprCedula."
        HAVING cedula_normalizada <> ''
        ORDER BY MAX(vt.cod_venta) ASC".$limiteSql;
    $result = $mysqli->query($sql);
    if (!$result) { return array(); }
    $cedulas = array();
    while ($row = $result->fetch_assoc()) {
        $cedulas[] = $row;
    }
    return $cedulas;
}

function seguimientoPacienteCrearHilosHistoricos($usuario = 0, $limite = 0) {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion");
    }
    if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
        $mysqli->close();
        return array("ok" => false, "motivo" => "estructura_no_disponible");
    }

    $cedulas = seguimientoPacienteCedulasVentasHistoricas($mysqli, $limite);
    $resumen = array(
        "ok" => true,
        "procesadas" => 0,
        "creados" => 0,
        "existentes" => 0,
        "conflictos" => 0,
        "con_ventas_sin_plan_madre" => 0,
        "hilos_unificados" => 0,
        "asuntos_actualizados" => 0,
        "ventas_vinculadas" => 0,
        "hilos_reactivados" => 0,
        "omitidos" => 0,
        "errores" => 0
    );

    foreach ($cedulas as $cedula) {
        $resumen["procesadas"]++;
        $cedulaNormalizada = seguimientoPacienteNormalizarCedula($cedula["cedula_normalizada"]);
        $codBase = seguimientoPacienteBuscarHiloHistoricoVentaCedula($mysqli, $cedulaNormalizada, 0);
        if ($codBase <= 0) {
            $relacionBase = seguimientoPacienteBuscarRelacion($mysqli, $cedulaNormalizada);
            $codBase = $relacionBase ? seguimientoPacienteEntero($relacionBase["cod_interConsultaFK"]) : 0;
        }

        if ($codBase > 0) {
            $resumenCedula = seguimientoPacienteResumenCedula($mysqli, $cedulaNormalizada, 0);
            $resultado = array(
                "ok" => true,
                "cod_interConsulta" => $codBase,
                "creado" => false,
                "conflicto" => (int)$resumenCedula["estado_conflicto"],
                "ventas_sin_plan_madre" => (int)$resumenCedula["ventas_sin_plan_madre"]
            );
        } else {
            $resultado = seguimientoPacienteAsegurarHiloPorVentaConConexion($mysqli, $cedula["cod_venta_ref"], $usuario, "historico");
        }
        if (!$resultado["ok"]) {
            $resumen["omitidos"]++;
            continue;
        }
        if (!empty($resultado["creado"])) {
            $resumen["creados"]++;
        } else {
            $resumen["existentes"]++;
        }
        $contextoNormalizacion = seguimientoPacienteContextoUnificacion($mysqli, $resultado["cod_interConsulta"]);
        if (!empty($resultado["conflicto"])) {
            $resumen["conflictos"]++;
            if (!empty($contextoNormalizacion["ok"]) && seguimientoPacienteEntero($contextoNormalizacion["cod_venta_ref"]) > 0) {
                $aseguradoConflicto = seguimientoPacienteAsegurarHiloPorVentaConConexion($mysqli, $contextoNormalizacion["cod_venta_ref"], $usuario, "historico_conflicto");
                if (!empty($aseguradoConflicto["ok"])) {
                    $resumen["asuntos_actualizados"] += isset($aseguradoConflicto["asunto_actualizado"]) ? (int)$aseguradoConflicto["asunto_actualizado"] : 0;
                    $resumen["ventas_vinculadas"] += isset($aseguradoConflicto["ventas_vinculadas"]) ? (int)$aseguradoConflicto["ventas_vinculadas"] : 0;
                    $resumen["hilos_reactivados"] += isset($aseguradoConflicto["hilo_reactivado"]) ? (int)$aseguradoConflicto["hilo_reactivado"] : 0;
                } else {
                    $resumen["errores"]++;
                }
            }
        } else {
            if (!empty($contextoNormalizacion["ok"])) {
                $normalizacion = seguimientoPacienteEjecutarUnificacionContexto($mysqli, $contextoNormalizacion, $usuario, false);
                if (!empty($normalizacion["ok"])) {
                    $resumen["hilos_unificados"] += isset($normalizacion["hilos_unificados"]) ? (int)$normalizacion["hilos_unificados"] : 0;
                    $resumen["asuntos_actualizados"] += isset($normalizacion["asunto_actualizado"]) ? (int)$normalizacion["asunto_actualizado"] : 0;
                    $resumen["ventas_vinculadas"] += isset($normalizacion["ventas_vinculadas"]) ? (int)$normalizacion["ventas_vinculadas"] : 0;
                    $resumen["hilos_reactivados"] += isset($normalizacion["hilo_reactivado"]) ? (int)$normalizacion["hilo_reactivado"] : 0;
                } else {
                    $resumen["errores"]++;
                }
            } else {
                $resumen["errores"]++;
            }
        }
        if ((int)$resultado["ventas_sin_plan_madre"] > 0) {
            $resumen["con_ventas_sin_plan_madre"]++;
        }
    }

    $mysqli->close();
    return $resumen;
}

function seguimientoPacienteValorSalidaJson($valor) {
    if (is_array($valor)) {
        $salida = array();
        foreach ($valor as $clave => $dato) {
            $salida[$clave] = seguimientoPacienteValorSalidaJson($dato);
        }
        return $salida;
    }
    if (is_string($valor)) {
        return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
    }
    return $valor;
}

function seguimientoPacienteObtenerHiloSimple($mysqli, $codInterConsulta) {
    $codInterConsulta = seguimientoPacienteEntero($codInterConsulta);
    if ($codInterConsulta <= 0) {
        return null;
    }

    $sql = "SELECT ic.cod_interConsulta, ic.asunto, ic.estado, ic.tipo, ic.cod_ventaFK,
            ic.cod_localFK, ic.fecha_creacion, ic.cod_usuarioFK_create,
            (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) AS nombre_local,
            (SELECT nombre_persona FROM persona WHERE cod_persona = ic.cod_usuarioFK_create) AS nombre_creador,
            ip.cedula_normalizada AS seguimiento_cedula,
            ip.nombre_paciente_snapshot AS seguimiento_nombre,
            ip.estado_conflicto AS seguimiento_conflicto,
            ip.detalle_conflicto AS seguimiento_detalle_conflicto
        FROM interconsulta ic
        LEFT JOIN interconsulta_paciente ip
            ON ip.cod_interConsultaFK = ic.cod_interConsulta
            AND ip.estado = 'activo'
        WHERE ic.cod_interConsulta = ?
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return null; }
    $stmt->bind_param("i", $codInterConsulta);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? $row : null;
}

function seguimientoPacienteVentaReferenciaCedula($mysqli, $cedulaNormalizada) {
    $ventas = seguimientoPacienteVentasPorCedula($mysqli, $cedulaNormalizada);
    if (count($ventas) == 0) {
        return 0;
    }
    return seguimientoPacienteEntero($ventas[0]["cod_venta"]);
}

function seguimientoPacienteRelacionVentaHilo($mysqli, $codInterConsulta) {
    $codInterConsulta = seguimientoPacienteEntero($codInterConsulta);
    if ($codInterConsulta <= 0) {
        return null;
    }

    $sql = "SELECT cod_ventaFK, cedula_normalizada
        FROM interconsulta_paciente_venta
        WHERE cod_interConsultaFK = ?
        AND estado = 'activo'
        ORDER BY cod_ventaFK ASC
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return null; }
    $stmt->bind_param("i", $codInterConsulta);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? $row : null;
}

function seguimientoPacienteContextoUnificacion($mysqli, $codInterConsulta) {
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion", "mensaje" => "No se pudo conectar a la base de datos.");
    }
    if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
        return array("ok" => false, "motivo" => "estructura_no_disponible", "mensaje" => "No esta disponible la estructura de seguimiento por paciente.");
    }

    $hilo = seguimientoPacienteObtenerHiloSimple($mysqli, $codInterConsulta);
    if (!$hilo) {
        return array("ok" => false, "motivo" => "hilo_no_encontrado", "mensaje" => "No se encontro el hilo seleccionado.");
    }

    $cedulaNormalizada = seguimientoPacienteNormalizarCedula(isset($hilo["seguimiento_cedula"]) ? $hilo["seguimiento_cedula"] : "");
    $codVentaRef = 0;
    $ventaRef = null;

    if ($cedulaNormalizada == "" && seguimientoPacienteEntero($hilo["cod_ventaFK"]) > 0) {
        $datosVenta = seguimientoPacienteObtenerVentaReal($mysqli, $hilo["cod_ventaFK"]);
        if (!$datosVenta["ok"]) {
            return array(
                "ok" => false,
                "motivo" => $datosVenta["motivo"],
                "mensaje" => "El hilo esta vinculado a una venta que no puede usarse para seguimiento por cedula."
            );
        }
        $ventaRef = $datosVenta["venta"];
        $codVentaRef = seguimientoPacienteEntero($ventaRef["cod_venta"]);
        $cedulaNormalizada = seguimientoPacienteNormalizarCedula($ventaRef["cedula_normalizada"]);
    }

    if ($cedulaNormalizada == "") {
        $relacionVenta = seguimientoPacienteRelacionVentaHilo($mysqli, $hilo["cod_interConsulta"]);
        if ($relacionVenta) {
            $cedulaNormalizada = seguimientoPacienteNormalizarCedula($relacionVenta["cedula_normalizada"]);
            $codVentaRef = seguimientoPacienteEntero($relacionVenta["cod_ventaFK"]);
        }
    }

    if ($cedulaNormalizada == "") {
        return array(
            "ok" => false,
            "motivo" => "sin_cedula",
            "mensaje" => "El hilo no tiene una venta o cedula vinculada para unificar seguimiento."
        );
    }

    if ($codVentaRef <= 0) {
        $codVentaRef = seguimientoPacienteVentaReferenciaCedula($mysqli, $cedulaNormalizada);
    }
    if ($codVentaRef > 0 && !$ventaRef) {
        $datosVentaRef = seguimientoPacienteObtenerVentaReal($mysqli, $codVentaRef);
        if ($datosVentaRef["ok"]) {
            $ventaRef = $datosVentaRef["venta"];
        }
    }

    $codClientePreferido = $ventaRef && isset($ventaRef["cod_clienteFK"]) ? seguimientoPacienteEntero($ventaRef["cod_clienteFK"]) : 0;
    $resumen = seguimientoPacienteResumenCedula($mysqli, $cedulaNormalizada, $codClientePreferido);
    $clientePrincipal = isset($resumen["cliente_principal"]) ? $resumen["cliente_principal"] : null;
    $nombrePaciente = "";
    if ($clientePrincipal && isset($clientePrincipal["nombre_persona"])) {
        $nombrePaciente = $clientePrincipal["nombre_persona"];
    } else if ($ventaRef && isset($ventaRef["nombre_persona"])) {
        $nombrePaciente = $ventaRef["nombre_persona"];
    } else if (isset($hilo["seguimiento_nombre"])) {
        $nombrePaciente = $hilo["seguimiento_nombre"];
    }
    $asuntoMaestro = seguimientoPacienteAsuntoHilo($nombrePaciente, $cedulaNormalizada);

    $codDestinoHistorico = seguimientoPacienteBuscarHiloHistoricoVentaCedula($mysqli, $cedulaNormalizada, $hilo["cod_interConsulta"]);
    $relacion = seguimientoPacienteBuscarRelacion($mysqli, $cedulaNormalizada);
    $codDestinoRelacion = $relacion ? seguimientoPacienteEntero($relacion["cod_interConsultaFK"]) : 0;
    $codDestino = $codDestinoHistorico > 0 ? $codDestinoHistorico : $codDestinoRelacion;
    if ($codDestino <= 0) {
        $codDestino = seguimientoPacienteBuscarHiloPorAsunto($mysqli, $asuntoMaestro);
    }
    $hiloDestino = $codDestino > 0 ? seguimientoPacienteObtenerHiloSimple($mysqli, $codDestino) : null;

    return array(
        "ok" => true,
        "hilo_origen" => $hilo,
        "cedula" => $cedulaNormalizada,
        "cod_venta_ref" => $codVentaRef,
        "venta_ref" => $ventaRef,
        "resumen" => $resumen,
        "nombre_paciente" => $nombrePaciente,
        "asunto_maestro" => $asuntoMaestro,
        "cod_destino_existente" => $codDestino,
        "cod_destino_historico" => $codDestinoHistorico,
        "cod_destino_relacion" => $codDestinoRelacion,
        "hilo_destino" => $hiloDestino
    );
}

function seguimientoPacienteHilosCandidatosUnificacion($mysqli, $cedulaNormalizada, $codDestino = 0) {
    $cedulaNormalizada = seguimientoPacienteNormalizarCedula($cedulaNormalizada);
    $codDestino = seguimientoPacienteEntero($codDestino);
    if ($cedulaNormalizada == "") {
        return array();
    }

    $exprCedulaDirecta = seguimientoPacienteSqlNormalizar("cl_dir.ci_cliente");
    $sql = "SELECT ic.cod_interConsulta, ic.asunto, ic.estado, ic.tipo, ic.cod_ventaFK,
            ic.cod_localFK, ic.fecha_creacion,
            (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) AS nombre_local,
            (SELECT nombre_persona FROM persona WHERE cod_persona = ic.cod_usuarioFK_create) AS nombre_creador,
            COALESCE(p_dir.nombre_persona,
                (SELECT ip.nombre_paciente_snapshot FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1),
                '') AS nombre_paciente,
            COALESCE(cl_dir.ci_cliente,
                (SELECT ip.cedula FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1),
                '') AS cedula,
            (SELECT COUNT(*) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta) AS cant_mensajes,
            (SELECT COUNT(*) FROM gastos g WHERE g.cod_interConsultaFK = ic.cod_interConsulta) AS cant_gastos,
            (SELECT COUNT(*) FROM dictamenes d WHERE d.cod_interConsultaFK = ic.cod_interConsulta) AS cant_dictamenes
        FROM interconsulta ic
        LEFT JOIN venta vt_dir ON vt_dir.cod_venta = ic.cod_ventaFK
        LEFT JOIN cliente cl_dir ON cl_dir.cod_cliente = vt_dir.cod_clienteFK
        LEFT JOIN persona p_dir ON p_dir.cod_persona = vt_dir.cod_clienteFK
        WHERE (? = 0 OR ic.cod_interConsulta <> ?)
        AND (
            (
                IFNULL(ic.cod_ventaFK,0) > 0
                AND vt_dir.cod_clienteFK <> 7
                AND ".$exprCedulaDirecta." = ?
            )
            OR EXISTS (
                SELECT 1
                FROM interconsulta_paciente_venta ipv_c
                WHERE ipv_c.cod_interConsultaFK = ic.cod_interConsulta
                AND ipv_c.estado = 'activo'
                AND ipv_c.cedula_normalizada = ?
                LIMIT 1
            )
        )
        ORDER BY ic.cod_interConsulta ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return array(); }
    $stmt->bind_param("iiss", $codDestino, $codDestino, $cedulaNormalizada, $cedulaNormalizada);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $result = $stmt->get_result();
    $registros = array();
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    $stmt->close();
    return $registros;
}

function seguimientoPacienteTotalesCandidatos($hilos) {
    $totales = array(
        "hilos" => count($hilos),
        "mensajes" => 0,
        "gastos" => 0,
        "dictamenes" => 0
    );
    foreach ($hilos as $hilo) {
        $totales["mensajes"] += isset($hilo["cant_mensajes"]) ? (int)$hilo["cant_mensajes"] : 0;
        $totales["gastos"] += isset($hilo["cant_gastos"]) ? (int)$hilo["cant_gastos"] : 0;
        $totales["dictamenes"] += isset($hilo["cant_dictamenes"]) ? (int)$hilo["cant_dictamenes"] : 0;
    }
    return $totales;
}

function seguimientoPacientePrevisualizarUnificacionHilo($codInterConsulta) {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion", "mensaje" => "No se pudo conectar a la base de datos.");
    }

    $contexto = seguimientoPacienteContextoUnificacion($mysqli, $codInterConsulta);
    if (empty($contexto["ok"])) {
        $mysqli->close();
        return $contexto;
    }

    $codDestino = seguimientoPacienteEntero($contexto["cod_destino_existente"]);
    $hilos = seguimientoPacienteHilosCandidatosUnificacion($mysqli, $contexto["cedula"], $codDestino);
    $totales = seguimientoPacienteTotalesCandidatos($hilos);
    $resumen = $contexto["resumen"];
    $hiloDestino = $contexto["hilo_destino"];
    $mysqli->close();

    return array(
        "ok" => true,
        "principal" => array(
            "cod_interConsulta" => $codDestino,
            "se_creara" => $codDestino <= 0 ? 1 : 0,
            "asunto_actual" => $hiloDestino && isset($hiloDestino["asunto"]) ? $hiloDestino["asunto"] : "",
            "asunto_maestro" => $contexto["asunto_maestro"],
            "cedula" => $contexto["cedula"],
            "nombre_paciente" => $contexto["nombre_paciente"],
            "conflicto" => (int)$resumen["estado_conflicto"],
            "detalle_conflicto" => (string)$resumen["detalle_conflicto"],
            "total_ventas" => (int)$resumen["total_ventas"],
            "total_planes_madre" => (int)$resumen["total_planes_madre"]
        ),
        "hilos" => $hilos,
        "totales" => $totales,
        "requiere_confirmacion_conflicto" => (int)$resumen["estado_conflicto"] == 1 ? 1 : 0
    );
}

function seguimientoPacienteActualizarAsuntoDestino($mysqli, $codDestino, $asuntoMaestro, $usuario) {
    $codDestino = seguimientoPacienteEntero($codDestino);
    $usuario = seguimientoPacienteEntero($usuario);
    if ($codDestino <= 0 || trim((string)$asuntoMaestro) == "") {
        return false;
    }
    $sql = "UPDATE interconsulta
        SET asunto = ?,
            cod_usuarioFK_edit = NULLIF(?,0),
            fecha_edit = NOW()
        WHERE cod_interConsulta = ?
        AND asunto <> ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param("siis", $asuntoMaestro, $usuario, $codDestino, $asuntoMaestro);
    $ok = $stmt->execute();
    $afectadas = $stmt->affected_rows;
    $stmt->close();
    return $ok && $afectadas > 0;
}

function seguimientoPacienteObtenerParticipantesHilo($mysqli, $codInterConsulta) {
    $codInterConsulta = seguimientoPacienteEntero($codInterConsulta);
    if ($codInterConsulta <= 0) {
        return array();
    }
    $sql = "SELECT DISTINCT mc.cod_usuarioFK
        FROM menciones mc
        INNER JOIN mensaje mj ON mj.cod_mensaje = mc.cod_mensajeFK
        WHERE mj.cod_interConsultaFK = ?
        AND mc.estado = 'activo'
        AND IFNULL(mc.cod_usuarioFK,0) > 0";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return array(); }
    $stmt->bind_param("i", $codInterConsulta);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $result = $stmt->get_result();
    $usuarios = array();
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = seguimientoPacienteEntero($row["cod_usuarioFK"]);
    }
    $stmt->close();
    return array_values(array_unique($usuarios));
}

function seguimientoPacienteInsertarMensajeSistemaUnificacion($mysqli, $codDestino, $usuario, $contenido, $participantes) {
    $codDestino = seguimientoPacienteEntero($codDestino);
    $usuario = seguimientoPacienteEntero($usuario);
    if ($codDestino <= 0) {
        return 0;
    }
    $sql = "INSERT INTO mensaje (contenido, estado, cod_interConsultaFK, cod_usuarioFK, fecha_creacion)
        VALUES (?, 'activo', ?, NULLIF(?,0), NOW())";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param("sii", $contenido, $codDestino, $usuario);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $codMensaje = (int)$stmt->insert_id;
    $stmt->close();

    $participantes[] = $usuario;
    $participantes = array_values(array_unique(array_filter(array_map("seguimientoPacienteEntero", $participantes))));
    foreach ($participantes as $participante) {
        if ($participante <= 0) { continue; }
        $leido = $participante == $usuario ? 1 : 0;
        $sqlMencion = "INSERT INTO menciones (cod_usuarioFK, cod_mensajeFK, isLeido, estado)
            VALUES (?, ?, ?, 'activo')";
        $stmtMencion = $mysqli->prepare($sqlMencion);
        if (!$stmtMencion) { continue; }
        $stmtMencion->bind_param("iii", $participante, $codMensaje, $leido);
        $stmtMencion->execute();
        $stmtMencion->close();
    }
    return $codMensaje;
}

function seguimientoPacienteMoverHiloUnificacion($mysqli, $hiloOrigen, $codDestino, $usuario, $cedulaNormalizada) {
    $codOrigen = seguimientoPacienteEntero($hiloOrigen["cod_interConsulta"]);
    $codDestino = seguimientoPacienteEntero($codDestino);
    $usuario = seguimientoPacienteEntero($usuario);
    if ($codOrigen <= 0 || $codDestino <= 0 || $codOrigen == $codDestino) {
        return array("ok" => false, "motivo" => "ids_invalidos");
    }

    $participantes = seguimientoPacienteObtenerParticipantesHilo($mysqli, $codOrigen);
    $asuntoOrigen = isset($hiloOrigen["asunto"]) ? trim((string)$hiloOrigen["asunto"]) : "";
    $contenido = "Unificacion automatica de seguimiento por CI ".$cedulaNormalizada
        .": el hilo #".$codOrigen
        .($asuntoOrigen != "" ? " (".$asuntoOrigen.")" : "")
        ." fue unido a este hilo maestro por @{".$usuario."}.";

    $consultas = array(
        array("UPDATE mensaje SET cod_interConsultaFK = ? WHERE cod_interConsultaFK = ?", "ii", array($codDestino, $codOrigen)),
        array("UPDATE gastos SET cod_interConsultaFK = ? WHERE cod_interConsultaFK = ?", "ii", array($codDestino, $codOrigen)),
        array("UPDATE dictamenes SET cod_interConsultaFK = ? WHERE cod_interConsultaFK = ?", "ii", array($codDestino, $codOrigen)),
        array("UPDATE interconsulta_paciente_venta SET cod_interConsultaFK = ?, estado = 'activo', fecha_actualizacion = NOW(), cod_usuarioFK_update = NULLIF(?,0) WHERE cod_interConsultaFK = ?", "iii", array($codDestino, $usuario, $codOrigen)),
        array("UPDATE interconsulta SET estado = 'inactivo', cod_usuarioFK_edit = NULLIF(?,0), fecha_edit = NOW() WHERE cod_interConsulta = ?", "ii", array($usuario, $codOrigen))
    );

    foreach ($consultas as $consulta) {
        $stmt = $mysqli->prepare($consulta[0]);
        if (!$stmt) {
            return array("ok" => false, "motivo" => "prepare", "mensaje" => $mysqli->error);
        }
        $params = $consulta[2];
        $refs = array();
        foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
        call_user_func_array(array($stmt, "bind_param"), array_merge(array($consulta[1]), $refs));
        if (!$stmt->execute()) {
            $mensaje = $stmt->error;
            $stmt->close();
            return array("ok" => false, "motivo" => "execute", "mensaje" => $mensaje);
        }
        $stmt->close();
    }

    seguimientoPacienteInsertarMensajeSistemaUnificacion($mysqli, $codDestino, $usuario, $contenido, $participantes);
    return array("ok" => true);
}

function seguimientoPacienteEjecutarUnificacionContexto($mysqli, $contexto, $usuario = 0, $confirmarConflicto = false) {
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion", "mensaje" => "No se pudo conectar a la base de datos.");
    }
    if (empty($contexto["ok"])) {
        return $contexto;
    }
    $resumen = $contexto["resumen"];
    if ((int)$resumen["estado_conflicto"] == 1 && !$confirmarConflicto) {
        return array(
            "ok" => false,
            "motivo" => "requiere_confirmacion_conflicto",
            "mensaje" => "La cedula esta asociada a mas de un paciente. Confirme la unificacion para continuar.",
            "detalle_conflicto" => (string)$resumen["detalle_conflicto"]
        );
    }

    $codVentaRef = seguimientoPacienteEntero($contexto["cod_venta_ref"]);
    if ($codVentaRef <= 0) {
        return array("ok" => false, "motivo" => "sin_venta_referencia", "mensaje" => "No se encontro una venta real para crear o actualizar el hilo maestro.");
    }

    $codDestino = isset($contexto["cod_destino_existente"]) ? seguimientoPacienteEntero($contexto["cod_destino_existente"]) : 0;
    $asuntoActualizadoAsegurado = 0;
    $hiloReactivado = 0;
    if ($codDestino <= 0) {
        $asegurado = seguimientoPacienteAsegurarHiloPorVentaConConexion($mysqli, $codVentaRef, $usuario, "unificacion");
        if (empty($asegurado["ok"])) {
            return array("ok" => false, "motivo" => "no_se_pudo_asegurar_hilo", "mensaje" => "No se pudo asegurar el hilo maestro de seguimiento.");
        }
        $asuntoActualizadoAsegurado = !empty($asegurado["asunto_actualizado"]) ? 1 : 0;
        $hiloReactivado = !empty($asegurado["hilo_reactivado"]) ? 1 : 0;
        $codDestino = seguimientoPacienteEntero($asegurado["cod_interConsulta"]);
    }
    $hilos = seguimientoPacienteHilosCandidatosUnificacion($mysqli, $contexto["cedula"], $codDestino);
    $totalesPrevios = seguimientoPacienteTotalesCandidatos($hilos);

    $mysqli->begin_transaction();
    if (seguimientoPacienteAsegurarHiloMaestroActivo($mysqli, $codDestino, $usuario)) {
        $hiloReactivado = 1;
    }
    $asuntoActualizadoDestino = seguimientoPacienteActualizarAsuntoDestino($mysqli, $codDestino, $contexto["asunto_maestro"], $usuario);

    $movidos = 0;
    foreach ($hilos as $hilo) {
        $resultadoMover = seguimientoPacienteMoverHiloUnificacion($mysqli, $hilo, $codDestino, $usuario, $contexto["cedula"]);
        if (empty($resultadoMover["ok"])) {
            $mysqli->rollback();
            return array(
                "ok" => false,
                "motivo" => "error_moviendo_hilo",
                "mensaje" => isset($resultadoMover["mensaje"]) ? $resultadoMover["mensaje"] : "No se pudo mover uno de los hilos.",
                "hilo" => isset($hilo["cod_interConsulta"]) ? $hilo["cod_interConsulta"] : 0
            );
        }
        $movidos++;
    }

    if ($contexto["venta_ref"]) {
        seguimientoPacienteGuardarRelacion($mysqli, $codDestino, $contexto["cedula"], $contexto["venta_ref"], $resumen, $usuario);
    }
    $ventasVinculadas = seguimientoPacienteVincularVentasCedula($mysqli, $codDestino, $contexto["cedula"], $usuario);

    $mysqli->commit();

    return array(
        "ok" => true,
        "cod_interConsulta_destino" => $codDestino,
        "asunto_maestro" => $contexto["asunto_maestro"],
        "cedula" => $contexto["cedula"],
        "hilos_unificados" => $movidos,
        "ventas_vinculadas" => $ventasVinculadas,
        "asunto_actualizado" => ($asuntoActualizadoAsegurado || $asuntoActualizadoDestino) ? 1 : 0,
        "hilo_reactivado" => $hiloReactivado ? 1 : 0,
        "totales" => $totalesPrevios,
        "conflicto" => (int)$resumen["estado_conflicto"]
    );
}

function seguimientoPacienteNormalizarHiloAutomatico($codInterConsulta, $usuario = 0) {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion", "mensaje" => "No se pudo conectar a la base de datos.");
    }
    if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
        $mysqli->close();
        return array("ok" => false, "motivo" => "estructura_no_disponible", "mensaje" => "No esta disponible la estructura de seguimiento por paciente.");
    }

    $contexto = seguimientoPacienteContextoUnificacion($mysqli, $codInterConsulta);
    if (empty($contexto["ok"])) {
        $mysqli->close();
        return $contexto;
    }

    $resumen = $contexto["resumen"];
    if ((int)$resumen["estado_conflicto"] == 1) {
        $codVentaRef = seguimientoPacienteEntero($contexto["cod_venta_ref"]);
        if ($codVentaRef <= 0) {
            $mysqli->close();
            return array(
                "ok" => false,
                "motivo" => "conflicto_sin_venta_referencia",
                "mensaje" => "La cedula tiene conflicto y no se encontro una venta real para asegurar el hilo maestro."
            );
        }
        $asegurado = seguimientoPacienteAsegurarHiloPorVentaConConexion($mysqli, $codVentaRef, $usuario, "normalizacion_conflicto");
        $mysqli->close();
        if (empty($asegurado["ok"])) {
            return array(
                "ok" => false,
                "motivo" => "conflicto_no_asegurado",
                "mensaje" => "La cedula tiene conflicto y no se pudo asegurar el hilo maestro."
            );
        }
        return array(
            "ok" => true,
            "motivo" => "conflicto_cedula",
            "normalizado" => 0,
            "conflicto" => 1,
            "cod_interConsulta_destino" => seguimientoPacienteEntero($asegurado["cod_interConsulta"]),
            "asunto_maestro" => $contexto["asunto_maestro"],
            "cedula" => $contexto["cedula"],
            "mensaje" => "La cedula esta asociada a mas de un paciente. Se muestra el hilo maestro con alerta de conflicto.",
            "detalle_conflicto" => (string)$resumen["detalle_conflicto"]
        );
    }

    $resultado = seguimientoPacienteEjecutarUnificacionContexto($mysqli, $contexto, $usuario, false);
    $mysqli->close();
    if (!empty($resultado["ok"])) {
        $resultado["motivo"] = "normalizado";
        $resultado["normalizado"] = 1;
    }
    return $resultado;
}

function seguimientoPacienteEjecutarUnificacionHilo($codInterConsulta, $usuario = 0, $confirmarConflicto = false) {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        return array("ok" => false, "motivo" => "sin_conexion", "mensaje" => "No se pudo conectar a la base de datos.");
    }
    if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
        $mysqli->close();
        return array("ok" => false, "motivo" => "estructura_no_disponible", "mensaje" => "No esta disponible la estructura de seguimiento por paciente.");
    }

    $contexto = seguimientoPacienteContextoUnificacion($mysqli, $codInterConsulta);
    if (empty($contexto["ok"])) {
        $mysqli->close();
        return $contexto;
    }

    $resultado = seguimientoPacienteEjecutarUnificacionContexto($mysqli, $contexto, $usuario, $confirmarConflicto);
    $mysqli->close();
    return $resultado;
}
?>

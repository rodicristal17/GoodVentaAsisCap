<?php

function clienteIdentidadPolicialTextoUtf8($valor)
{
    $valor = (string)$valor;
    if (mb_check_encoding($valor, 'UTF-8')) {
        return $valor;
    }
    return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
}

function clienteIdentidadPolicialNormalizarTexto($valor)
{
    $valor = clienteIdentidadPolicialTextoUtf8($valor);
    $valor = preg_replace('/\s+/u', ' ', trim($valor));
    return mb_strtoupper($valor, 'UTF-8');
}

function clienteIdentidadPolicialResponder($codigo, $datos = array())
{
    $respuesta = array("1" => $codigo, "codigo" => $codigo);
    foreach ($datos as $clave => $valor) {
        $respuesta[$clave] = $valor;
    }
    echo json_encode($respuesta);
    exit;
}

function clienteIdentidadPolicialObtenerComparacion($mysqli, $codCliente)
{
    $codCliente = (int)$codCliente;
    $stmt = $mysqli->prepare(
        "SELECT c.ci_cliente,p.nombre_persona,p.apellido_persona
         FROM cliente c
         INNER JOIN persona p ON p.cod_persona=c.cod_cliente
         WHERE c.cod_cliente=? LIMIT 1"
    );
    if (!$stmt) {
        return array("ok" => false, "motivo" => "cliente_no_disponible");
    }
    $stmt->bind_param("i", $codCliente);
    if (!$stmt->execute()) {
        $stmt->close();
        return array("ok" => false, "motivo" => "cliente_no_disponible");
    }
    $cliente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$cliente) {
        return array("ok" => false, "motivo" => "cliente_no_encontrado");
    }

    $ciNormalizada = preg_replace('/\D+/', '', (string)$cliente["ci_cliente"]);
    if ($ciNormalizada === '' || (int)$ciNormalizada <= 0) {
        return array("ok" => true, "encontrado" => false);
    }

    $ciNumero = (int)$ciNormalizada;
    $stmtPolicial = $mysqli->prepare(
        "SELECT nombres,apellidos
         FROM bd_personas.persona
         WHERE ci=? LIMIT 1"
    );
    if (!$stmtPolicial) {
        return array("ok" => false, "motivo" => "fuente_no_disponible");
    }
    $stmtPolicial->bind_param("i", $ciNumero);
    if (!$stmtPolicial->execute()) {
        $stmtPolicial->close();
        return array("ok" => false, "motivo" => "fuente_no_disponible");
    }
    $policial = $stmtPolicial->get_result()->fetch_assoc();
    $stmtPolicial->close();
    if (!$policial) {
        return array("ok" => true, "encontrado" => false);
    }

    $nombreActual = trim((string)$cliente["nombre_persona"]);
    $apellidoActual = trim((string)$cliente["apellido_persona"]);
    $nombrePolicial = trim((string)$policial["nombres"]);
    $apellidoPolicial = trim((string)$policial["apellidos"]);
    $coincide = clienteIdentidadPolicialNormalizarTexto($nombreActual) === clienteIdentidadPolicialNormalizarTexto($nombrePolicial)
        && clienteIdentidadPolicialNormalizarTexto($apellidoActual) === clienteIdentidadPolicialNormalizarTexto($apellidoPolicial);

    return array(
        "ok" => true,
        "encontrado" => true,
        "coincide" => $coincide,
        "ci" => $ciNormalizada,
        "nombre_actual" => $nombreActual,
        "apellido_actual" => $apellidoActual,
        "nombre_policial" => $nombrePolicial,
        "apellido_policial" => $apellidoPolicial
    );
}

function clienteIdentidadPolicialCompararDatos($mysqli, $ci, $nombre, $apellido)
{
    $ciNormalizada = preg_replace('/\D+/', '', (string)$ci);
    if ($ciNormalizada === '' || (int)$ciNormalizada <= 0) {
        return array("ok" => true, "encontrado" => false);
    }

    $ciNumero = (int)$ciNormalizada;
    $stmt = $mysqli->prepare("SELECT nombres,apellidos FROM bd_personas.persona WHERE ci=? LIMIT 1");
    if (!$stmt) {
        return array("ok" => false, "motivo" => "fuente_no_disponible");
    }
    $stmt->bind_param("i", $ciNumero);
    if (!$stmt->execute()) {
        $stmt->close();
        return array("ok" => false, "motivo" => "fuente_no_disponible");
    }
    $policial = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$policial) {
        return array("ok" => true, "encontrado" => false);
    }

    $nombreActual = trim((string)$nombre);
    $apellidoActual = trim((string)$apellido);
    $nombrePolicial = trim((string)$policial["nombres"]);
    $apellidoPolicial = trim((string)$policial["apellidos"]);

    return array(
        "ok" => true,
        "encontrado" => true,
        "coincide" => clienteIdentidadPolicialNormalizarTexto($nombreActual) === clienteIdentidadPolicialNormalizarTexto($nombrePolicial)
            && clienteIdentidadPolicialNormalizarTexto($apellidoActual) === clienteIdentidadPolicialNormalizarTexto($apellidoPolicial),
        "ci" => $ciNormalizada,
        "nombre_actual" => $nombreActual,
        "apellido_actual" => $apellidoActual,
        "nombre_policial" => $nombrePolicial,
        "apellido_policial" => $apellidoPolicial
    );
}

function clienteIdentidadPolicialPrepararDatos($mysqli, $ci, $nombre, $apellido, $confirmarCambio)
{
    $comparacion = clienteIdentidadPolicialCompararDatos($mysqli, $ci, $nombre, $apellido);
    if (empty($comparacion["ok"])) {
        clienteIdentidadPolicialResponder("IDENTIDAD_NO_DISPONIBLE", array(
            "mensaje" => "No se pudo consultar la base de identidad. Intente nuevamente."
        ));
    }
    if (empty($comparacion["encontrado"]) || !empty($comparacion["coincide"])) {
        return array("nombre" => $nombre, "apellido" => $apellido, "comparacion" => null);
    }
    if (!$confirmarCambio) {
        clienteIdentidadPolicialResponder("IDENTIDAD_DIFERENTE", array(
            "mensaje" => "El nombre o apellido no coincide con la base de identidad.",
            "cliente" => array(
                "nombre" => clienteIdentidadPolicialTextoUtf8($comparacion["nombre_actual"]),
                "apellido" => clienteIdentidadPolicialTextoUtf8($comparacion["apellido_actual"])
            ),
            "registro_policial" => array(
                "nombre" => clienteIdentidadPolicialTextoUtf8($comparacion["nombre_policial"]),
                "apellido" => clienteIdentidadPolicialTextoUtf8($comparacion["apellido_policial"])
            )
        ));
    }

    return array(
        "nombre" => $comparacion["nombre_policial"],
        "apellido" => $comparacion["apellido_policial"],
        "comparacion" => $comparacion
    );
}

function clienteIdentidadPolicialRegistrarAuditoria($mysqli, $codCliente, $usuario, $contexto, $comparacion)
{
    if (!$comparacion) {
        return true;
    }
    $contexto = substr(trim((string)$contexto), 0, 30);
    $stmt = $mysqli->prepare(
        "INSERT INTO cliente_identidad_policial_auditoria
         (cod_clienteFK,ci,nombre_anterior,apellido_anterior,nombre_nuevo,apellido_nuevo,
          contexto,cod_usuarioFK,fecha_creacion) VALUES (?,?,?,?,?,?,?,?,NOW())"
    );
    if (!$stmt) {
        return false;
    }
    $codCliente = (int)$codCliente;
    $usuario = (int)$usuario;
    $ci = (string)$comparacion["ci"];
    $nombreAnterior = (string)$comparacion["nombre_actual"];
    $apellidoAnterior = (string)$comparacion["apellido_actual"];
    $nombreNuevo = (string)$comparacion["nombre_policial"];
    $apellidoNuevo = (string)$comparacion["apellido_policial"];
    $stmt->bind_param(
        "issssssi",
        $codCliente,
        $ci,
        $nombreAnterior,
        $apellidoAnterior,
        $nombreNuevo,
        $apellidoNuevo,
        $contexto,
        $usuario
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function clienteIdentidadPolicialAplicarCambio($mysqli, $codCliente, $usuario, $contexto, $comparacion)
{
    $codCliente = (int)$codCliente;
    $usuario = (int)$usuario;
    $contexto = substr(trim((string)$contexto), 0, 30);
    $nombreActual = (string)$comparacion["nombre_actual"];
    $apellidoActual = (string)$comparacion["apellido_actual"];
    $nombreNuevo = (string)$comparacion["nombre_policial"];
    $apellidoNuevo = (string)$comparacion["apellido_policial"];
    $ci = (string)$comparacion["ci"];

    if (!$mysqli->begin_transaction()) {
        return false;
    }

    $stmtAuditoria = $mysqli->prepare(
        "INSERT INTO cliente_identidad_policial_auditoria
         (cod_clienteFK,ci,nombre_anterior,apellido_anterior,nombre_nuevo,apellido_nuevo,
          contexto,cod_usuarioFK,fecha_creacion)
         VALUES (?,?,?,?,?,?,?,?,NOW())"
    );
    if (!$stmtAuditoria) {
        $mysqli->rollback();
        return false;
    }
    $stmtAuditoria->bind_param(
        "issssssi",
        $codCliente,
        $ci,
        $nombreActual,
        $apellidoActual,
        $nombreNuevo,
        $apellidoNuevo,
        $contexto,
        $usuario
    );
    if (!$stmtAuditoria->execute()) {
        $stmtAuditoria->close();
        $mysqli->rollback();
        return false;
    }
    $stmtAuditoria->close();

    $stmtPersona = $mysqli->prepare(
        "UPDATE persona
         SET nombre_persona=?,apellido_persona=?
         WHERE cod_persona=? LIMIT 1"
    );
    if (!$stmtPersona) {
        $mysqli->rollback();
        return false;
    }
    $stmtPersona->bind_param("ssi", $nombreNuevo, $apellidoNuevo, $codCliente);
    if (!$stmtPersona->execute() || $stmtPersona->affected_rows < 0) {
        $stmtPersona->close();
        $mysqli->rollback();
        return false;
    }
    $stmtPersona->close();

    $stmtSeguimiento = $mysqli->prepare(
        "UPDATE interconsulta_paciente
         SET nombre_paciente_snapshot=TRIM(CONCAT_WS(CHAR(32),?,?)),fecha_actualizacion=NOW()
         WHERE cod_clienteFK_principal=?"
    );
    if (!$stmtSeguimiento) {
        $mysqli->rollback();
        return false;
    }
    $stmtSeguimiento->bind_param("ssi", $apellidoNuevo, $nombreNuevo, $codCliente);
    if (!$stmtSeguimiento->execute()) {
        $stmtSeguimiento->close();
        $mysqli->rollback();
        return false;
    }
    $stmtSeguimiento->close();

    $stmtHilo = $mysqli->prepare(
        "UPDATE interconsulta ic
         INNER JOIN interconsulta_paciente ip ON ip.cod_interConsultaFK=ic.cod_interConsulta
         SET ic.asunto=LEFT(CONCAT(ip.nombre_paciente_snapshot,' - CI ',ip.cedula),180),
             ic.fecha_edit=NOW()
         WHERE ip.cod_clienteFK_principal=?"
    );
    if (!$stmtHilo) {
        $mysqli->rollback();
        return false;
    }
    $stmtHilo->bind_param("i", $codCliente);
    if (!$stmtHilo->execute()) {
        $stmtHilo->close();
        $mysqli->rollback();
        return false;
    }
    $stmtHilo->close();

    return $mysqli->commit();
}

function clienteIdentidadPolicialValidarAntesGuardar($codCliente, $usuario, $contexto, $confirmarCambio)
{
    $mysqli = conectar_al_servidor();
    if ($mysqli->connect_errno) {
        clienteIdentidadPolicialResponder("IDENTIDAD_NO_DISPONIBLE", array(
            "mensaje" => "No se pudo validar la identidad del cliente. Intente nuevamente."
        ));
    }

    $comparacion = clienteIdentidadPolicialObtenerComparacion($mysqli, $codCliente);
    if (empty($comparacion["ok"])) {
        $mysqli->close();
        clienteIdentidadPolicialResponder("IDENTIDAD_NO_DISPONIBLE", array(
            "mensaje" => "No se pudo consultar la base de identidad. Intente nuevamente."
        ));
    }
    if (empty($comparacion["encontrado"]) || !empty($comparacion["coincide"])) {
        $mysqli->close();
        return true;
    }

    if (!$confirmarCambio) {
        $respuesta = array(
            "mensaje" => "El nombre o apellido no coincide con la base de identidad.",
            "cliente" => array(
                "nombre" => clienteIdentidadPolicialTextoUtf8($comparacion["nombre_actual"]),
                "apellido" => clienteIdentidadPolicialTextoUtf8($comparacion["apellido_actual"])
            ),
            "registro_policial" => array(
                "nombre" => clienteIdentidadPolicialTextoUtf8($comparacion["nombre_policial"]),
                "apellido" => clienteIdentidadPolicialTextoUtf8($comparacion["apellido_policial"])
            )
        );
        $mysqli->close();
        clienteIdentidadPolicialResponder("IDENTIDAD_DIFERENTE", $respuesta);
    }

    if (!clienteIdentidadPolicialAplicarCambio($mysqli, $codCliente, $usuario, $contexto, $comparacion)) {
        $mysqli->close();
        clienteIdentidadPolicialResponder("IDENTIDAD_CAMBIO_ERROR", array(
            "mensaje" => "No se pudo actualizar la identidad del cliente. La operacion no fue guardada."
        ));
    }
    $mysqli->close();
    return true;
}

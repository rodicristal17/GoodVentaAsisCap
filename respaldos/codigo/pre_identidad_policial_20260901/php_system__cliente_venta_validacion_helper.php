<?php

function clienteVentaResponderValidacion($codigo)
{
    echo json_encode(array("1" => $codigo));
    exit;
}

function clienteVentaValidarParaGuardar($codCliente,$operacion,$codVenta = 0)
{
    $codCliente = (int)$codCliente;
    $codVenta = (int)$codVenta;
    $mysqli = conectar_al_servidor();

    if ($operacion === "editar" && $codVenta > 0 && $codCliente === 7) {
        $stmtHistorica = $mysqli->prepare("SELECT cod_clienteFK FROM venta WHERE cod_venta=? LIMIT 1");
        if ($stmtHistorica) {
            $stmtHistorica->bind_param("i",$codVenta);
            if ($stmtHistorica->execute()) {
                $filaHistorica = $stmtHistorica->get_result()->fetch_assoc();
                if ($filaHistorica && (int)$filaHistorica["cod_clienteFK"] === 7) {
                    $stmtHistorica->close();
                    $mysqli->close();
                    return true;
                }
            }
            $stmtHistorica->close();
        }
    }

    if ($codCliente <= 0 || $codCliente === 7) {
        $mysqli->close();
        clienteVentaResponderValidacion("CLIENTE_OBLIGATORIO");
    }

    $stmt = $mysqli->prepare(
        "SELECT p.nombre_persona,p.apellido_persona,p.direccion,p.telefono,c.ci_cliente,c.idzonaFk
         FROM cliente c
         INNER JOIN persona p ON p.cod_persona=c.cod_cliente
         WHERE c.cod_cliente=? LIMIT 1"
    );
    if (!$stmt) {
        $mysqli->close();
        clienteVentaResponderValidacion("CLIENTE_INVALIDO");
    }
    $stmt->bind_param("i",$codCliente);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        clienteVentaResponderValidacion("CLIENTE_INVALIDO");
    }
    $cliente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $mysqli->close();
    if (!$cliente) {
        clienteVentaResponderValidacion("CLIENTE_INVALIDO");
    }

    $nombre = trim((string)$cliente["nombre_persona"]);
    $apellido = trim((string)$cliente["apellido_persona"]);
    if ($nombre === "" || $apellido === "") {
        clienteVentaResponderValidacion("CLIENTE_SIN_NOMBRE_APELLIDO");
    }
    if (trim((string)$cliente["ci_cliente"]) === "") {
        clienteVentaResponderValidacion("CLIENTE_SIN_DOCUMENTO");
    }
    $telefono = trim((string)$cliente["telefono"]);
    $digitos = preg_replace('/\D+/', '', $telefono);
    if (!preg_match('/^[+0-9()\s.\-]+$/', $telefono) || strlen($digitos) < 6 || strlen($digitos) > 15) {
        clienteVentaResponderValidacion("CLIENTE_SIN_TELEFONO");
    }
    if ((int)$cliente["idzonaFk"] <= 0) {
        clienteVentaResponderValidacion("CLIENTE_SIN_ZONA");
    }
    if (trim((string)$cliente["direccion"]) === "") {
        clienteVentaResponderValidacion("CLIENTE_SIN_DIRECCION");
    }
    return true;
}


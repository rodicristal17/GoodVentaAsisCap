<?php

function ueno_saldo_tabla_existe($mysqli, $tabla)
{
	$tabla = $mysqli->real_escape_string($tabla);
	$result = $mysqli->query("SHOW TABLES LIKE '$tabla'");
	return $result && $result->num_rows > 0;
}

function ueno_saldo_normalizar_comprobante($valor)
{
	return trim(str_replace(array("\r", "\n", "\t", " "), "", (string)$valor));
}

function ueno_saldo_asegurar_tabla_movimiento_pago($mysqli)
{
	if (ueno_saldo_tabla_existe($mysqli, "ueno_movimiento_pago")) {
		return true;
	}

	$sql = "CREATE TABLE IF NOT EXISTS ueno_movimiento_pago (
		id int(11) NOT NULL AUTO_INCREMENT,
		id_movimiento int(11) NOT NULL,
		cod_pagoFK int(11) NOT NULL,
		monto_aplicado int(11) NOT NULL DEFAULT 0,
		usuario_asocio int(11) NOT NULL,
		fecha_hora_asociacion datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		estado varchar(45) NOT NULL DEFAULT 'activo',
		observacion varchar(255) DEFAULT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY uk_ueno_mov_pago_activo (id_movimiento,cod_pagoFK,estado),
		KEY idx_ueno_mov_pago_pago (cod_pagoFK),
		KEY idx_ueno_mov_pago_movimiento_estado (id_movimiento,estado)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";

	return $mysqli->query($sql) && ueno_saldo_tabla_existe($mysqli, "ueno_movimiento_pago");
}

function ueno_saldo_sql_unlinked_pagos($mysqli, $comprobante, $excluirCodPago = "", $excluirControl = "")
{
	$comprobante = ueno_saldo_normalizar_comprobante($comprobante);
	if ($comprobante == "" || !ueno_saldo_tabla_existe($mysqli, "pago_transferencia_conciliacion")) {
		return 0;
	}

	$compSql = $mysqli->real_escape_string($comprobante);
	$condicionExtra = "";
	if ($excluirCodPago != "") {
		$condicionExtra .= " AND pc.cod_pagoFK!='" . $mysqli->real_escape_string($excluirCodPago) . "'";
	}
	if ($excluirControl != "") {
		$condicionExtra .= " AND pc.id!='" . $mysqli->real_escape_string($excluirControl) . "'";
	}

	$joinLink = "";
	$condicionLink = "";
	if (ueno_saldo_tabla_existe($mysqli, "ueno_movimiento_pago")) {
		$joinLink = " LEFT JOIN ueno_movimiento_pago ump ON ump.cod_pagoFK=pc.cod_pagoFK AND ump.estado='activo'";
		$condicionLink = " AND ump.id IS NULL";
	}

	$sql = "SELECT IFNULL(SUM(pc.monto_pago),0) AS total
		FROM pago_transferencia_conciliacion pc
		$joinLink
		WHERE pc.activo='SI'
		AND pc.estado_conciliacion NOT IN ('anulado','rechazado')
		AND pc.nro_comprobante_informado='$compSql'
		$condicionExtra
		$condicionLink";
	$result = $mysqli->query($sql);
	if (!$result) {
		return 0;
	}
	$row = $result->fetch_assoc();
	return $row ? (int)$row["total"] : 0;
}

function ueno_saldo_total_linkeado($mysqli, $idMovimiento)
{
	if ($idMovimiento == "" || !ueno_saldo_tabla_existe($mysqli, "ueno_movimiento_pago")) {
		return 0;
	}

	$idSql = $mysqli->real_escape_string($idMovimiento);
	$result = $mysqli->query("SELECT IFNULL(SUM(monto_aplicado),0) AS total
		FROM ueno_movimiento_pago
		WHERE id_movimiento='$idSql'
		AND estado='activo'");
	if (!$result) {
		return 0;
	}
	$row = $result->fetch_assoc();
	return $row ? (int)$row["total"] : 0;
}

function ueno_saldo_total_auditoria_cobrar($mysqli, $idMovimiento, $aplicadoLink = 0)
{
	if ($idMovimiento == "" || !ueno_saldo_tabla_existe($mysqli, "cobrar_cuota_auditoria")) {
		return 0;
	}

	$idSql = $mysqli->real_escape_string($idMovimiento);
	$result = $mysqli->query("SELECT IFNULL(SUM(monto),0) AS total
		FROM cobrar_cuota_auditoria
		WHERE id_movimiento_ueno='$idSql'
		AND accion IN ('REGISTRAR_Y_CONCILIAR_UENO','REGISTRAR_Y_CONCILIAR_UENO_MULTIPLE')
		AND estado_pago='registrado'
		AND estado_conciliacion='conciliado_ueno'
		AND monto>0");
	if (!$result) {
		return 0;
	}
	$row = $result->fetch_assoc();
	$totalAuditado = $row ? (int)$row["total"] : 0;
	$pendienteSinLink = $totalAuditado - (int)$aplicadoLink;
	return $pendienteSinLink > 0 ? $pendienteSinLink : 0;
}

function ueno_saldo_disponible_movimiento($mysqli, $movimiento, $excluirCodPago = "", $excluirControl = "")
{
	if (!$movimiento) {
		return 0;
	}
	$importe = isset($movimiento["importe_credito"]) ? (int)$movimiento["importe_credito"] : 0;
	if ($importe <= 0) {
		return 0;
	}

	$idMovimiento = isset($movimiento["id_movimiento"]) ? $movimiento["id_movimiento"] : "";
	$comprobante = isset($movimiento["nro_comprobante"]) ? $movimiento["nro_comprobante"] : "";
	$aplicadoLink = ueno_saldo_total_linkeado($mysqli, $idMovimiento);
	$aplicadoSinLink = ueno_saldo_sql_unlinked_pagos($mysqli, $comprobante, $excluirCodPago, $excluirControl);
	$aplicadoAuditoria = ueno_saldo_total_auditoria_cobrar($mysqli, $idMovimiento, $aplicadoLink);
	$disponible = $importe - $aplicadoLink - $aplicadoSinLink - $aplicadoAuditoria;
	if (isset($movimiento["monto_disponible"]) && trim((string)$movimiento["monto_disponible"]) !== "") {
		$disponibleGuardado = (int)$movimiento["monto_disponible"];
		if ($disponibleGuardado >= 0 && $disponibleGuardado < $disponible) {
			$disponible = $disponibleGuardado;
		}
	}

	if ($disponible < 0) {
		return 0;
	}
	if ($disponible > $importe) {
		return $importe;
	}
	return (int)$disponible;
}

function ueno_saldo_estado_credito($importe, $disponible)
{
	$importe = (int)$importe;
	$disponible = (int)$disponible;
	if ($importe <= 0 || $disponible <= 0) {
		return "asignado_total";
	}
	if ($disponible >= $importe) {
		return "disponible";
	}
	return "asignado_parcial";
}

function ueno_saldo_sincronizar_movimiento($mysqli, $idMovimiento, $disponible, $importeCredito = 0)
{
	if ($idMovimiento == "") {
		return;
	}
	$estado = ueno_saldo_estado_credito($importeCredito, $disponible);
	$stmt = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET monto_disponible=?, estado=? WHERE id_movimiento=?");
	if (!$stmt) {
		return;
	}
	$stmt->bind_param("sss", $disponible, $estado, $idMovimiento);
	$stmt->execute();
}

?>

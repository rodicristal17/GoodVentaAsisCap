<?php

require_once("gasto_tesoreria_helper.php");

/**
 * Vinculacion uno-a-uno de transferencias entre cuentas bancarias propias.
 * Los movimientos importados se conservan; el vinculo los neutraliza para
 * cuotas, gastos y conciliacion operativa sin alterar los extractos.
 */

function ueno_ti_estructura_disponible($mysqli)
{
	return gastoTesoreriaEstructuraDisponible($mysqli)
		&& ueno_tabla_existe($mysqli, "ueno_transferencia_interna")
		&& ueno_tabla_existe($mysqli, "ueno_transferencia_interna_evento");
}

function ueno_ti_requerir_responsable($mysqli, $usuario)
{
	if (!gastoTesoreriaEstructuraDisponible($mysqli)) {
		throw new Exception("Falta preparar la configuracion oficial de Tesoreria.");
	}
	if (!gastoTesoreriaEsResponsable($mysqli, $usuario)) {
		throw new Exception("Esta accion corresponde exclusivamente a la responsable oficial de Tesoreria.");
	}
}

function ueno_ti_fecha_movimiento($movimiento)
{
	$fecha = isset($movimiento["fecha_confirmacion"]) ? trim((string)$movimiento["fecha_confirmacion"]) : "";
	if ($fecha == "" || strpos($fecha, "0000-00-00") === 0) {
		$fecha = isset($movimiento["fecha_transaccion"]) ? trim((string)$movimiento["fecha_transaccion"]) : "";
	}
	return ueno_fecha($fecha);
}

function ueno_ti_tipo_movimiento($movimiento)
{
	$tipo = isset($movimiento["tipo_movimiento"]) ? strtolower(trim((string)$movimiento["tipo_movimiento"])) : "";
	if ($tipo == "") {
		if (isset($movimiento["importe_debito"]) && (int)$movimiento["importe_debito"] > 0) {
			return "debito";
		}
		if (isset($movimiento["importe_credito"]) && (int)$movimiento["importe_credito"] > 0) {
			return "credito";
		}
	}
	return $tipo;
}

function ueno_ti_monto_movimiento($movimiento)
{
	return ueno_ti_tipo_movimiento($movimiento) == "debito"
		? (int)$movimiento["importe_debito"]
		: (int)$movimiento["importe_credito"];
}

function ueno_ti_relacion_activa_movimiento($mysqli, $idMovimiento, $bloquear = false)
{
	$idMovimiento = (int)$idMovimiento;
	if ($idMovimiento <= 0 || !ueno_tabla_existe($mysqli, "ueno_transferencia_interna")) {
		return array();
	}
	$sql = "SELECT ti.*, IFNULL(p.nombre_persona,CONCAT('Usuario ',ti.cod_usuario_vinculaFK)) AS usuario_nombre
		FROM ueno_transferencia_interna ti
		LEFT JOIN persona p ON p.cod_persona=ti.cod_usuario_vinculaFK
		WHERE ti.id_movimiento_debitoFK=? OR ti.id_movimiento_creditoFK=?
		LIMIT 1" . ($bloquear ? " FOR UPDATE" : "");
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		throw new Exception($mysqli->error);
	}
	$stmt->bind_param("ii", $idMovimiento, $idMovimiento);
	if (!$stmt->execute()) {
		throw new Exception($stmt->error);
	}
	$fila = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	return $fila ? $fila : array();
}

function ueno_ti_movimientos_bloqueados($mysqli, $idUno, $idDos)
{
	$idUno = (int)$idUno;
	$idDos = (int)$idDos;
	$sql = "SELECT id_movimiento,id_importacion,banco_codigo,fecha_confirmacion,fecha_transaccion,
		nro_comprobante,descripcion,concepto,tipo_movimiento,importe_debito,importe_credito,monto_disponible,estado
		FROM ueno_movimiento_bancario
		WHERE id_movimiento IN (?,?)
		ORDER BY id_movimiento ASC FOR UPDATE";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		throw new Exception($mysqli->error);
	}
	$stmt->bind_param("ii", $idUno, $idDos);
	if (!$stmt->execute()) {
		throw new Exception($stmt->error);
	}
	$result = $stmt->get_result();
	$movimientos = array();
	while ($fila = $result->fetch_assoc()) {
		$movimientos[(int)$fila["id_movimiento"]] = $fila;
	}
	$stmt->close();
	if (count($movimientos) != 2 || !isset($movimientos[$idUno]) || !isset($movimientos[$idDos])) {
		throw new Exception("No se encontraron los dos movimientos bancarios seleccionados.");
	}
	return $movimientos;
}

function ueno_ti_total_aplicado_debito($mysqli, $idMovimiento)
{
	if (!ueno_tabla_existe($mysqli, "ueno_movimiento_gasto")) {
		return 0;
	}
	$idMovimiento = (int)$idMovimiento;
	return (int)ueno_scalar($mysqli, "SELECT IFNULL(SUM(monto_aplicado),0)
		FROM ueno_movimiento_gasto WHERE id_movimiento=$idMovimiento AND estado='activo'");
}

function ueno_ti_validar_movimiento_libre($mysqli, $movimiento)
{
	$idMovimiento = isset($movimiento["id_movimiento"]) ? (int)$movimiento["id_movimiento"] : 0;
	$tipo = ueno_ti_tipo_movimiento($movimiento);
	$monto = ueno_ti_monto_movimiento($movimiento);
	$estado = strtolower(trim((string)$movimiento["estado"]));
	$estadosNoDisponibles = array(
		"conciliado", "conciliada", "asignado_total", "anulado", "anulada",
		"rechazado", "rechazada", "duplicado", "ignorado"
	);
	if ($idMovimiento <= 0 || !in_array($tipo, array("debito", "credito"), true) || $monto <= 0) {
		throw new Exception("El movimiento bancario no tiene un importe valido.");
	}
	if (in_array($estado, $estadosNoDisponibles, true)) {
		throw new Exception("Uno de los movimientos ya fue utilizado o no esta disponible.");
	}
	if (ueno_ti_fecha_movimiento($movimiento) == "") {
		throw new Exception("Uno de los movimientos no tiene una fecha bancaria valida.");
	}
	if (ueno_ti_relacion_activa_movimiento($mysqli, $idMovimiento, false)) {
		throw new Exception("Uno de los movimientos ya pertenece a otra transferencia interna.");
	}
	if ($tipo == "credito") {
		$disponible = ueno_saldo_disponible_movimiento($mysqli, $movimiento);
		if ($disponible !== $monto || (int)$movimiento["monto_disponible"] !== $monto) {
			throw new Exception("El credito ya tiene aplicaciones y no puede vincularse como transferencia interna.");
		}
	} elseif (ueno_ti_total_aplicado_debito($mysqli, $idMovimiento) > 0) {
		throw new Exception("El debito ya fue aplicado a un gasto y no puede vincularse.");
	}
	return true;
}

function ueno_ti_sql_comprobante_normalizado($expresion)
{
	return "REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(" . $expresion . ",''),CHAR(13),''),CHAR(10),''),CHAR(9),''),' ','')";
}

function ueno_ti_sql_movimiento_libre($mysqli, $alias)
{
	$alias = preg_replace('/[^a-z0-9_]/i', '', (string)$alias);
	if ($alias == '') {
		throw new Exception('No se pudo preparar la deteccion de transferencias internas.');
	}
	$estadoLibre = "LOWER(TRIM(IFNULL($alias.estado,''))) NOT IN
		('conciliado','conciliada','asignado_total','anulado','anulada','rechazado','rechazada','duplicado','ignorado')";
	$debitoLibre = "$alias.tipo_movimiento='debito' AND $alias.importe_debito>0";
	if (ueno_tabla_existe($mysqli, 'ueno_movimiento_gasto')) {
		$debitoLibre .= " AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_gasto umg_libre
			WHERE umg_libre.id_movimiento=$alias.id_movimiento AND umg_libre.estado='activo')";
	}

	$creditoLibre = "$alias.tipo_movimiento='credito' AND $alias.importe_credito>0
		AND $alias.monto_disponible=$alias.importe_credito";
	$tieneMovimientosPago = ueno_tabla_existe($mysqli, 'ueno_movimiento_pago');
	if ($tieneMovimientosPago) {
		$creditoLibre .= " AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_pago ump_libre
			WHERE ump_libre.id_movimiento=$alias.id_movimiento AND ump_libre.estado='activo')";
	}
	if (ueno_tabla_existe($mysqli, 'pago_transferencia_conciliacion')) {
		$comprobanteControl = ueno_ti_sql_comprobante_normalizado('pc_libre.nro_comprobante_informado');
		$comprobanteBanco = ueno_ti_sql_comprobante_normalizado($alias . '.nro_comprobante');
		$condicionBanco = ueno_saldo_columna_existe($mysqli, 'pago_transferencia_conciliacion', 'banco_codigo')
			? " AND UPPER(IFNULL(pc_libre.banco_codigo,''))=UPPER(IFNULL($alias.banco_codigo,''))"
			: '';
		$condicionSinVinculo = $tieneMovimientosPago
			? " AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_pago ump_control
				WHERE ump_control.cod_pagoFK=pc_libre.cod_pagoFK AND ump_control.estado='activo')"
			: '';
		$creditoLibre .= " AND ($comprobanteBanco='' OR NOT EXISTS (SELECT 1 FROM pago_transferencia_conciliacion pc_libre
			WHERE pc_libre.activo='SI'
			AND pc_libre.estado_conciliacion NOT IN ('anulado','rechazado')
			AND $comprobanteControl=$comprobanteBanco
			$condicionBanco
			$condicionSinVinculo))";
	}
	if (ueno_tabla_existe($mysqli, 'cobrar_cuota_auditoria')) {
		$creditoLibre .= " AND NOT EXISTS (SELECT 1 FROM cobrar_cuota_auditoria cca_libre
			WHERE cca_libre.id_movimiento_ueno=$alias.id_movimiento
			AND cca_libre.accion IN ('REGISTRAR_Y_CONCILIAR_UENO','REGISTRAR_Y_CONCILIAR_UENO_MULTIPLE')
			AND cca_libre.estado_pago='registrado'
			AND cca_libre.estado_conciliacion='conciliado_ueno'
			AND cca_libre.monto>0)";
	}

	return "($estadoLibre AND (($debitoLibre) OR ($creditoLibre)))";
}

function ueno_ti_conteos_sugerencias_lista($mysqli, $idsMovimientos)
{
	$conteos = array();
	if (!ueno_ti_estructura_disponible($mysqli) || !is_array($idsMovimientos)) {
		return $conteos;
	}
	$ids = array();
	foreach ($idsMovimientos as $idMovimiento) {
		$idMovimiento = (int)$idMovimiento;
		if ($idMovimiento > 0) {
			$ids[$idMovimiento] = $idMovimiento;
		}
	}
	if (empty($ids)) {
		return $conteos;
	}
	$listaIds = implode(',', array_values($ids));
	$origenLibre = ueno_ti_sql_movimiento_libre($mysqli, 'mv_origen');
	$candidatoLibre = ueno_ti_sql_movimiento_libre($mysqli, 'mv_candidato');
	$fechaOrigen = "COALESCE(NULLIF(mv_origen.fecha_confirmacion,'0000-00-00'),mv_origen.fecha_transaccion)";
	$fechaCandidato = "COALESCE(NULLIF(mv_candidato.fecha_confirmacion,'0000-00-00'),mv_candidato.fecha_transaccion)";
	$sql = "SELECT mv_origen.id_movimiento,COUNT(DISTINCT mv_candidato.id_movimiento) AS total
		FROM ueno_movimiento_bancario mv_origen
		INNER JOIN ueno_movimiento_bancario mv_candidato ON mv_candidato.id_movimiento<>mv_origen.id_movimiento
			AND UPPER(mv_candidato.banco_codigo)<>UPPER(mv_origen.banco_codigo)
			AND ABS(DATEDIFF($fechaOrigen,$fechaCandidato))<=3
			AND ((mv_origen.tipo_movimiento='debito' AND mv_candidato.tipo_movimiento='credito'
				AND mv_origen.importe_debito=mv_candidato.importe_credito)
				OR (mv_origen.tipo_movimiento='credito' AND mv_candidato.tipo_movimiento='debito'
				AND mv_origen.importe_credito=mv_candidato.importe_debito))
		WHERE mv_origen.id_movimiento IN ($listaIds)
		AND $origenLibre
		AND $candidatoLibre
		AND NOT EXISTS (SELECT 1 FROM ueno_transferencia_interna ti_origen
			WHERE ti_origen.id_movimiento_debitoFK=mv_origen.id_movimiento
			OR ti_origen.id_movimiento_creditoFK=mv_origen.id_movimiento)
		AND NOT EXISTS (SELECT 1 FROM ueno_transferencia_interna ti_candidato
			WHERE ti_candidato.id_movimiento_debitoFK=mv_candidato.id_movimiento
			OR ti_candidato.id_movimiento_creditoFK=mv_candidato.id_movimiento)
		GROUP BY mv_origen.id_movimiento";
	$resultado = $mysqli->query($sql);
	if (!$resultado) {
		error_log('Telar: no se pudieron detectar posibles transferencias internas en la mesa bancaria.');
		return $conteos;
	}
	while ($fila = $resultado->fetch_assoc()) {
		$conteos[(int)$fila['id_movimiento']] = (int)$fila['total'];
	}
	return $conteos;
}

function ueno_ti_datos_movimiento($movimiento)
{
	$tipo = ueno_ti_tipo_movimiento($movimiento);
	$monto = ueno_ti_monto_movimiento($movimiento);
	return array(
		"id_movimiento" => (int)$movimiento["id_movimiento"],
		"banco_codigo" => ueno_banco_codigo($movimiento["banco_codigo"]),
		"banco_nombre" => ueno_banco_nombre($movimiento["banco_codigo"]),
		"tipo_movimiento" => $tipo,
		"tipo_texto" => $tipo == "debito" ? "Salida" : "Ingreso",
		"fecha" => ueno_ti_fecha_movimiento($movimiento),
		"nro_comprobante" => ueno_from_db($movimiento["nro_comprobante"]),
		"descripcion" => ueno_from_db($movimiento["descripcion"]),
		"concepto" => ueno_from_db($movimiento["concepto"]),
		"monto" => $monto,
		"monto_fmt" => number_format($monto, 0, ",", "."),
		"estado" => ueno_from_db($movimiento["estado"])
	);
}

function ueno_ti_buscar_sugerencias($usuario)
{
	ueno_requerir_permiso($usuario, "VERCONCILIACIONUENO");
	$mysqli = conectar_al_servidor();
	try {
		if (!ueno_ti_estructura_disponible($mysqli)) {
			throw new Exception("Falta ejecutar la actualizacion de transferencias internas bancarias.");
		}
		ueno_ti_requerir_responsable($mysqli, $usuario);
		$idMovimiento = (int)ueno_post("id_movimiento");
		if ($idMovimiento <= 0) {
			throw new Exception("Seleccione un movimiento bancario.");
		}
		$stmt = $mysqli->prepare("SELECT id_movimiento,id_importacion,banco_codigo,fecha_confirmacion,fecha_transaccion,
			nro_comprobante,descripcion,concepto,tipo_movimiento,importe_debito,importe_credito,monto_disponible,estado
			FROM ueno_movimiento_bancario WHERE id_movimiento=? LIMIT 1");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("i", $idMovimiento);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$seleccionado = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$seleccionado) {
			throw new Exception("El movimiento bancario ya no existe.");
		}
		ueno_ti_validar_movimiento_libre($mysqli, $seleccionado);
		$tipoSeleccionado = ueno_ti_tipo_movimiento($seleccionado);
		$tipoCandidato = $tipoSeleccionado == "debito" ? "credito" : "debito";
		$monto = ueno_ti_monto_movimiento($seleccionado);
		$fecha = ueno_ti_fecha_movimiento($seleccionado);
		$banco = ueno_banco_codigo($seleccionado["banco_codigo"]);
		$campoMonto = $tipoCandidato == "debito" ? "importe_debito" : "importe_credito";
		$sql = "SELECT mv.id_movimiento,mv.id_importacion,mv.banco_codigo,mv.fecha_confirmacion,mv.fecha_transaccion,
			mv.nro_comprobante,mv.descripcion,mv.concepto,mv.tipo_movimiento,mv.importe_debito,mv.importe_credito,
			mv.monto_disponible,mv.estado
			FROM ueno_movimiento_bancario mv
			LEFT JOIN ueno_transferencia_interna ti
				ON ti.id_movimiento_debitoFK=mv.id_movimiento OR ti.id_movimiento_creditoFK=mv.id_movimiento
			WHERE mv.tipo_movimiento=? AND mv.$campoMonto=? AND mv.banco_codigo<>?
			AND ABS(DATEDIFF(COALESCE(NULLIF(mv.fecha_confirmacion,'0000-00-00'),mv.fecha_transaccion),?))<=3
			AND ti.id_transferencia IS NULL
			AND LOWER(IFNULL(mv.estado,'')) NOT IN ('conciliado','conciliada','asignado_total','anulado','anulada','rechazado','rechazada','duplicado','ignorado')
			ORDER BY ABS(DATEDIFF(COALESCE(NULLIF(mv.fecha_confirmacion,'0000-00-00'),mv.fecha_transaccion),?)) ASC,
				COALESCE(NULLIF(mv.fecha_confirmacion,'0000-00-00'),mv.fecha_transaccion) ASC,mv.id_movimiento ASC
			LIMIT 20";
		$stmt = $mysqli->prepare($sql);
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("sisss", $tipoCandidato, $monto, $banco, $fecha, $fecha);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$result = $stmt->get_result();
		$candidatos = array();
		while ($fila = $result->fetch_assoc()) {
			try {
				ueno_ti_validar_movimiento_libre($mysqli, $fila);
				$candidatos[] = ueno_ti_datos_movimiento($fila);
			} catch (Exception $ignorado) {
				// Un candidato usado por otro proceso se omite de la sugerencia.
			}
		}
		$stmt->close();
		$seleccionadoDatos = ueno_ti_datos_movimiento($seleccionado);
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"seleccionado" => $seleccionadoDatos,
			"candidatos" => $candidatos,
			"total" => count($candidatos),
			"regla" => "Importe exacto, cuentas bancarias distintas y hasta 3 dias de diferencia."
		));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_ti_vincular($usuario)
{
	ueno_requerir_permiso($usuario, "VERCONCILIACIONUENO");
	$mysqli = conectar_al_servidor();
	try {
		if (!ueno_ti_estructura_disponible($mysqli)) {
			throw new Exception("Falta ejecutar la actualizacion de transferencias internas bancarias.");
		}
		ueno_ti_requerir_responsable($mysqli, $usuario);
		$idSeleccionado = (int)ueno_post("id_movimiento");
		$idCandidato = (int)ueno_post("id_contraparte");
		if ($idSeleccionado <= 0 || $idCandidato <= 0 || $idSeleccionado == $idCandidato) {
			throw new Exception("Seleccione los dos movimientos que forman la transferencia.");
		}
		$mysqli->begin_transaction();
		$movimientos = ueno_ti_movimientos_bloqueados($mysqli, $idSeleccionado, $idCandidato);
		$primero = $movimientos[$idSeleccionado];
		$segundo = $movimientos[$idCandidato];
		ueno_ti_validar_movimiento_libre($mysqli, $primero);
		ueno_ti_validar_movimiento_libre($mysqli, $segundo);
		if (ueno_ti_tipo_movimiento($primero) == ueno_ti_tipo_movimiento($segundo)) {
			throw new Exception("La transferencia debe tener una salida y un ingreso.");
		}
		$debito = ueno_ti_tipo_movimiento($primero) == "debito" ? $primero : $segundo;
		$credito = ueno_ti_tipo_movimiento($primero) == "credito" ? $primero : $segundo;
		$monto = (int)$debito["importe_debito"];
		if ($monto <= 0 || $monto !== (int)$credito["importe_credito"]) {
			throw new Exception("Los importes de salida e ingreso deben coincidir exactamente.");
		}
		$bancoOrigen = ueno_banco_codigo($debito["banco_codigo"]);
		$bancoDestino = ueno_banco_codigo($credito["banco_codigo"]);
		if ($bancoOrigen == $bancoDestino) {
			throw new Exception("Los movimientos deben pertenecer a bancos distintos.");
		}
		$fechaDebito = ueno_ti_fecha_movimiento($debito);
		$fechaCredito = ueno_ti_fecha_movimiento($credito);
		$dias = abs((int)((strtotime($fechaDebito) - strtotime($fechaCredito)) / 86400));
		if ($fechaDebito == "" || $fechaCredito == "" || $dias > 3) {
			throw new Exception("Los movimientos deben estar dentro de una ventana de tres dias.");
		}
		$estadoDebitoAnterior = (string)$debito["estado"];
		$estadoCreditoAnterior = (string)$credito["estado"];
		$disponibleCreditoAnterior = (int)$credito["monto_disponible"];
		$idDebito = (int)$debito["id_movimiento"];
		$idCredito = (int)$credito["id_movimiento"];
		$usuarioInt = (int)$usuario;
		$stmt = $mysqli->prepare("INSERT INTO ueno_transferencia_interna
			(id_movimiento_debitoFK,id_movimiento_creditoFK,banco_origen,banco_destino,monto,fecha_debito,fecha_credito,
			estado_debito_anterior,estado_credito_anterior,disponible_credito_anterior,cod_usuario_vinculaFK,fecha_hora_vinculacion)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("iississssii", $idDebito, $idCredito, $bancoOrigen, $bancoDestino, $monto, $fechaDebito, $fechaCredito,
			$estadoDebitoAnterior, $estadoCreditoAnterior, $disponibleCreditoAnterior, $usuarioInt);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$idTransferencia = (int)$stmt->insert_id;
		$stmt->close();
		$datos = array(
			"id_transferencia" => $idTransferencia,
			"debito" => ueno_ti_datos_movimiento($debito),
			"credito" => ueno_ti_datos_movimiento($credito)
		);
		$datosJson = json_encode($datos);
		$accion = "vinculada";
		$stmt = $mysqli->prepare("INSERT INTO ueno_transferencia_interna_evento
			(id_transferencia_snapshot,accion,id_movimiento_debitoFK,id_movimiento_creditoFK,banco_origen,banco_destino,monto,
			fecha_debito,fecha_credito,cod_usuario_actorFK,fecha_hora,motivo,datos_json)
			VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NULL,?)");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("isiississis", $idTransferencia, $accion, $idDebito, $idCredito, $bancoOrigen, $bancoDestino, $monto,
			$fechaDebito, $fechaCredito, $usuarioInt, $datosJson);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$stmt->close();
		$estadoInterno = "ignorado";
		$cero = 0;
		$stmtDebito = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET estado=? WHERE id_movimiento=?");
		if (!$stmtDebito) {
			throw new Exception($mysqli->error);
		}
		$stmtDebito->bind_param("si", $estadoInterno, $idDebito);
		if (!$stmtDebito->execute() || $stmtDebito->affected_rows !== 1) {
			throw new Exception("No se pudo neutralizar el debito bancario.");
		}
		$stmtDebito->close();
		$stmtCredito = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET estado=?,monto_disponible=? WHERE id_movimiento=?");
		if (!$stmtCredito) {
			throw new Exception($mysqli->error);
		}
		$stmtCredito->bind_param("sii", $estadoInterno, $cero, $idCredito);
		if (!$stmtCredito->execute() || $stmtCredito->affected_rows !== 1) {
			throw new Exception("No se pudo neutralizar el credito bancario.");
		}
		$stmtCredito->close();
		$observacion = "Transferencia interna entre cuentas propias; sin efecto contable operativo.";
		ueno_auditar_conciliacion($mysqli, "VINCULAR_TRANSFERENCIA_INTERNA", "ueno_transferencia_interna", (string)$idTransferencia,
			"", $idDebito, $estadoDebitoAnterior, $estadoInterno, $monto, $usuarioInt, $observacion, $datos);
		ueno_auditar_conciliacion($mysqli, "VINCULAR_TRANSFERENCIA_INTERNA", "ueno_transferencia_interna", (string)$idTransferencia,
			"", $idCredito, $estadoCreditoAnterior, $estadoInterno, $monto, $usuarioInt, $observacion, $datos);
		$mysqli->commit();
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "2" => "Transferencia interna vinculada y neutralizada correctamente.", "id_transferencia" => $idTransferencia));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_ti_revertir($usuario)
{
	ueno_requerir_permiso($usuario, "VERCONCILIACIONUENO");
	$mysqli = conectar_al_servidor();
	try {
		if (!ueno_ti_estructura_disponible($mysqli)) {
			throw new Exception("Falta ejecutar la actualizacion de transferencias internas bancarias.");
		}
		ueno_ti_requerir_responsable($mysqli, $usuario);
		$idTransferencia = (int)ueno_post("id_transferencia");
		$motivoUtf8 = isset($_POST["motivo"]) ? trim((string)$_POST["motivo"]) : "";
		if ($idTransferencia <= 0 || mb_strlen($motivoUtf8, "UTF-8") < 5) {
			throw new Exception("Indique un motivo de al menos 5 caracteres para revertir.");
		}
		$motivo = ueno_to_db($motivoUtf8);
		$stmt = $mysqli->prepare("SELECT id_movimiento_debitoFK,id_movimiento_creditoFK
			FROM ueno_transferencia_interna WHERE id_transferencia=? LIMIT 1");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("i", $idTransferencia);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$referencia = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$referencia) {
			throw new Exception("La transferencia ya fue revertida o no existe.");
		}
		$idDebito = (int)$referencia["id_movimiento_debitoFK"];
		$idCredito = (int)$referencia["id_movimiento_creditoFK"];
		$mysqli->begin_transaction();
		$movimientos = ueno_ti_movimientos_bloqueados($mysqli, $idDebito, $idCredito);
		$stmt = $mysqli->prepare("SELECT * FROM ueno_transferencia_interna WHERE id_transferencia=? LIMIT 1 FOR UPDATE");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("i", $idTransferencia);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$relacion = $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if (!$relacion) {
			throw new Exception("La transferencia ya fue revertida o no existe.");
		}
		if ((int)$relacion["id_movimiento_debitoFK"] !== $idDebito
			|| (int)$relacion["id_movimiento_creditoFK"] !== $idCredito) {
			throw new Exception("La transferencia cambio durante la revision; vuelva a abrirla.");
		}
		$debito = $movimientos[$idDebito];
		$credito = $movimientos[$idCredito];
		if (strtolower(trim((string)$debito["estado"])) != "ignorado"
			|| strtolower(trim((string)$credito["estado"])) != "ignorado"
			|| (int)$credito["monto_disponible"] != 0) {
			throw new Exception("La transferencia cambio desde su vinculacion y requiere revision tecnica antes de revertir.");
		}
		if (ueno_ti_total_aplicado_debito($mysqli, $idDebito) > 0
			|| ueno_saldo_total_linkeado($mysqli, $idCredito) > 0) {
			throw new Exception("Los movimientos tienen aplicaciones posteriores y no pueden restaurarse automaticamente.");
		}
		$usuarioInt = (int)$usuario;
		$accion = "revertida";
		$datos = array(
			"estado_debito_restaurado" => $relacion["estado_debito_anterior"],
			"estado_credito_restaurado" => $relacion["estado_credito_anterior"],
			"disponible_credito_restaurado" => (int)$relacion["disponible_credito_anterior"]
		);
		$datosJson = json_encode($datos);
		$bancoOrigen = (string)$relacion["banco_origen"];
		$bancoDestino = (string)$relacion["banco_destino"];
		$monto = (int)$relacion["monto"];
		$fechaDebito = (string)$relacion["fecha_debito"];
		$fechaCredito = (string)$relacion["fecha_credito"];
		$estadoDebitoAnterior = (string)$relacion["estado_debito_anterior"];
		$estadoCreditoAnterior = (string)$relacion["estado_credito_anterior"];
		$disponibleCreditoAnterior = (int)$relacion["disponible_credito_anterior"];
		$stmt = $mysqli->prepare("INSERT INTO ueno_transferencia_interna_evento
			(id_transferencia_snapshot,accion,id_movimiento_debitoFK,id_movimiento_creditoFK,banco_origen,banco_destino,monto,
			fecha_debito,fecha_credito,cod_usuario_actorFK,fecha_hora,motivo,datos_json)
			VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?,?)");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("isiissississ", $idTransferencia, $accion, $idDebito, $idCredito, $bancoOrigen,
			$bancoDestino, $monto, $fechaDebito, $fechaCredito,
			$usuarioInt, $motivo, $datosJson);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$stmt->close();
		$stmtDebito = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET estado=? WHERE id_movimiento=? AND estado='ignorado'");
		if (!$stmtDebito) {
			throw new Exception($mysqli->error);
		}
		$stmtDebito->bind_param("si", $estadoDebitoAnterior, $idDebito);
		if (!$stmtDebito->execute() || $stmtDebito->affected_rows !== 1) {
			throw new Exception("No se pudo restaurar el debito bancario.");
		}
		$stmtDebito->close();
		$stmtCredito = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET estado=?,monto_disponible=? WHERE id_movimiento=? AND estado='ignorado'");
		if (!$stmtCredito) {
			throw new Exception($mysqli->error);
		}
		$stmtCredito->bind_param("sii", $estadoCreditoAnterior, $disponibleCreditoAnterior, $idCredito);
		if (!$stmtCredito->execute() || $stmtCredito->affected_rows !== 1) {
			throw new Exception("No se pudo restaurar el credito bancario.");
		}
		$stmtCredito->close();
		$stmt = $mysqli->prepare("DELETE FROM ueno_transferencia_interna WHERE id_transferencia=?");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("i", $idTransferencia);
		if (!$stmt->execute() || $stmt->affected_rows !== 1) {
			throw new Exception("No se pudo cerrar el vinculo de transferencia interna.");
		}
		$stmt->close();
		$observacion = "Reversion de transferencia interna: " . $motivo;
		ueno_auditar_conciliacion($mysqli, "REVERTIR_TRANSFERENCIA_INTERNA", "ueno_transferencia_interna", (string)$idTransferencia,
			"", $idDebito, "ignorado", $relacion["estado_debito_anterior"], $relacion["monto"], $usuarioInt, $observacion, $datos);
		ueno_auditar_conciliacion($mysqli, "REVERTIR_TRANSFERENCIA_INTERNA", "ueno_transferencia_interna", (string)$idTransferencia,
			"", $idCredito, "ignorado", $relacion["estado_credito_anterior"], $relacion["monto"], $usuarioInt, $observacion, $datos);
		$mysqli->commit();
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "2" => "La vinculacion fue revertida y ambos movimientos recuperaron su estado anterior."));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

?>

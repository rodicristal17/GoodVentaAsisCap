<?php
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("buscar_nivel.php");

function ueno_json($informacion)
{
	echo json_encode($informacion);
	exit;
}

function ueno_bloquear_proceso_mesa()
{
	ueno_json(array("1" => "solo_consulta", "2" => "No se puede procesar pagos desde la mesa de trabajo. Utiliza el modulo de caja/cobros."));
}

set_exception_handler(function($error) {
	ueno_json(array("1" => "error", "2" => $error->getMessage()));
});

function ueno_to_db($valor)
{
	return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function ueno_from_db($valor)
{
	return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function ueno_post($clave, $defecto = "")
{
	if (!isset($_POST[$clave])) {
		return $defecto;
	}
	return ueno_to_db($_POST[$clave]);
}

function ueno_validar_sesion()
{
	if (!isset($_POST['useru']) || !isset($_POST['passu']) || !isset($_POST['navegador'])) {
		ueno_json(array("1" => "UI"));
	}

	$user = ueno_to_db($_POST['useru']);
	$pass = str_replace("=", "+", $_POST['passu']);
	$navegador = ueno_to_db($_POST['navegador']);
	$resp = verificar_navegador($user, $navegador, $pass);

	if ($resp != "ok") {
		ueno_json(array("1" => "UI"));
	}

	return $user;
}

function ueno_tabla_existe($mysqli, $tabla)
{
	$tabla = $mysqli->real_escape_string($tabla);
	$sql = "SHOW TABLES LIKE '$tabla'";
	$result = $mysqli->query($sql);
	return $result && $result->num_rows > 0;
}

function ueno_tablas_requeridas_ok($mysqli)
{
	$tablas = array(
		"ueno_importacion_extracto",
		"ueno_movimiento_bancario",
		"pago_transferencia_conciliacion",
		"ueno_movimiento_pago"
	);

	foreach ($tablas as $tabla) {
		if (!ueno_tabla_existe($mysqli, $tabla)) {
			return false;
		}
	}

	return true;
}

function ueno_monto($valor)
{
	$valor = trim((string)$valor);
	if ($valor == "") {
		return 0;
	}
	$valor = str_replace(array("Gs.", "Gs", " ", "\xc2\xa0"), "", $valor);
	$valor = str_replace(".", "", $valor);
	$valor = str_replace(",", ".", $valor);
	return (int)round((float)$valor);
}

function ueno_fecha($valor)
{
	$valor = trim((string)$valor);
	if ($valor == "") {
		return null;
	}

	if (is_numeric($valor)) {
		$dias = (int)$valor;
		if ($dias > 20000) {
			return date("Y-m-d", strtotime("1899-12-30 +" . $dias . " days"));
		}
	}

	$valor = str_replace("/", "-", $valor);
	$partes = explode("-", $valor);
	if (count($partes) == 3) {
		if (strlen($partes[0]) == 4) {
			return $partes[0] . "-" . str_pad($partes[1], 2, "0", STR_PAD_LEFT) . "-" . str_pad($partes[2], 2, "0", STR_PAD_LEFT);
		}
		return $partes[2] . "-" . str_pad($partes[1], 2, "0", STR_PAD_LEFT) . "-" . str_pad($partes[0], 2, "0", STR_PAD_LEFT);
	}

	$time = strtotime($valor);
	if ($time === false) {
		return null;
	}

	return date("Y-m-d", $time);
}

function ueno_escape_html($valor)
{
	return htmlspecialchars(ueno_from_db($valor), ENT_QUOTES, 'UTF-8');
}

function ueno_estado_texto($estado)
{
	$estado = (string)$estado;
	if ($estado == "pendiente_conciliacion") {
		return "Pendiente de conciliacion";
	}
	if ($estado == "conciliado_ueno") {
		return "Conciliado con Ueno";
	}
	if ($estado == "observado") {
		return "Observado";
	}
	if ($estado == "rechazado") {
		return "Rechazado";
	}
	if ($estado == "anulado") {
		return "Anulado";
	}
	return $estado;
}

function ueno_estado_pago_visual($estado, $monto_pago = 0, $monto_aplicado = 0)
{
	$estado = (string)$estado;
	$monto_pago = (int)$monto_pago;
	$monto_aplicado = (int)$monto_aplicado;
	if ($estado == "conciliado_ueno" || ($monto_pago > 0 && $monto_aplicado >= $monto_pago)) {
		return "Conciliado";
	}
	if ($monto_aplicado > 0 && $monto_aplicado < $monto_pago) {
		return "Parcial";
	}
	return ueno_estado_texto($estado);
}

function ueno_estado_movimiento_texto($estado, $credito = 0, $disponible = 0, $debito = 0)
{
	$estado = (string)$estado;
	$credito = (int)$credito;
	$disponible = (int)$disponible;
	$debito = (int)$debito;

	if ($credito > 0 && $disponible > $credito) {
		return "Revisar";
	}
	if ($credito > 0 && $disponible <= 0) {
		return "Conciliado";
	}
	if ($credito > 0 && $disponible < $credito) {
		return "Parcial";
	}
	if ($credito > 0) {
		return "Disponible";
	}
	if ($debito > 0) {
		return "Debito informativo";
	}
	if ($estado == "asignado_total") {
		return "Conciliado";
	}
	if ($estado == "asignado_parcial") {
		return "Parcial";
	}
	if ($estado == "disponible") {
		return "Disponible";
	}
	if ($estado == "registrado") {
		return "Registrado";
	}
	return $estado;
}

function ueno_estado_movimiento_clave($estado_visual)
{
	$estado_visual = strtolower(trim((string)$estado_visual));
	if ($estado_visual == "conciliado") {
		return "conciliado";
	}
	if ($estado_visual == "parcial") {
		return "parcial";
	}
	if ($estado_visual == "disponible") {
		return "disponible";
	}
	if (strpos($estado_visual, "debito") !== false) {
		return "debito";
	}
	if ($estado_visual == "revisar") {
		return "revisar";
	}
	return "otro";
}

function ueno_movimiento_cumple_filtro_rapido($filtro, $estado_clave, $credito, $disponible)
{
	$filtro = trim((string)$filtro);
	if ($filtro == "" || $filtro == "todos") {
		return true;
	}
	if ($filtro == "disponibles") {
		return $estado_clave == "disponible";
	}
	if ($filtro == "parciales") {
		return $estado_clave == "parcial";
	}
	if ($filtro == "conciliados") {
		return $estado_clave == "conciliado";
	}
	if ($filtro == "con_saldo") {
		return (int)$credito > 0 && (int)$disponible > 0;
	}
	return true;
}

function ueno_usuario_tiene_permiso($usuario, $codigo)
{
	if ((string)$usuario == "2") {
		return true;
	}
	if (function_exists("controldeaccesoacasas")) {
		return controldeaccesoacasas($usuario, $codigo, " u.accion='SI' ") == 1;
	}
	return true;
}

function ueno_requerir_permiso($usuario, $codigo)
{
	if (!ueno_usuario_tiene_permiso($usuario, $codigo)) {
		ueno_json(array("1" => "NI", "2" => "No tiene permiso para esta accion"));
	}
}

function ueno_requerir_algun_permiso($usuario, $codigos)
{
	foreach ($codigos as $codigo) {
		if (ueno_usuario_tiene_permiso($usuario, $codigo)) {
			return;
		}
	}
	ueno_json(array("1" => "NI", "2" => "No tiene permiso para esta accion"));
}

function ueno_auditar_conciliacion($mysqli, $accion, $tabla, $registro_id, $cod_pagoFK, $id_movimiento, $estado_anterior, $estado_nuevo, $monto, $usuario, $observacion, $datos = "")
{
	if (!ueno_tabla_existe($mysqli, "ueno_auditoria_conciliacion")) {
		return;
	}

	if (is_array($datos)) {
		$datos = json_encode($datos);
	}

	$consulta = "INSERT INTO ueno_auditoria_conciliacion
		(tabla_afectada, registro_id, cod_pagoFK, id_movimiento, accion, estado_anterior, estado_nuevo, monto, usuario, observacion, datos)
		VALUES (?,?,?,?,?,?,?,?,?,?,?)";
	$stmt = $mysqli->prepare($consulta);
	if (!$stmt) {
		throw new Exception($mysqli->error);
	}
	$stmt->bind_param(
		"sssssssssss",
		$tabla,
		$registro_id,
		$cod_pagoFK,
		$id_movimiento,
		$accion,
		$estado_anterior,
		$estado_nuevo,
		$monto,
		$usuario,
		$observacion,
		$datos
	);
	if (!$stmt->execute()) {
		throw new Exception($stmt->error);
	}
}

function ueno_resumen_conciliacion_vacio()
{
	return array(
		"procesados" => 0,
		"conciliados" => 0,
		"observados" => 0,
		"pendientes" => 0,
		"sin_movimiento" => 0,
		"duplicados_banco" => 0,
		"sin_saldo" => 0,
		"no_credito" => 0,
		"monto_conciliado" => 0,
		"mensajes" => array()
	);
}

function ueno_observar_pago($mysqli, $id_conciliacion, $usuario, $observacion)
{
	$estado = "observado";
	$consulta = "UPDATE pago_transferencia_conciliacion
		SET estado_conciliacion=?, usuario_ultima_revision=?, fecha_hora_revision=NOW(), observacion=?
		WHERE id=? AND activo='SI' AND estado_conciliacion='pendiente_conciliacion'";
	$stmt = $mysqli->prepare($consulta);
	$stmt->bind_param("ssss", $estado, $usuario, $observacion, $id_conciliacion);
	if (!$stmt->execute()) {
		throw new Exception($stmt->error);
	}
	if ($stmt->affected_rows > 0) {
		ueno_auditar_conciliacion(
			$mysqli,
			"OBSERVAR_PAGO",
			"pago_transferencia_conciliacion",
			$id_conciliacion,
			"",
			"",
			"pendiente_conciliacion",
			$estado,
			0,
			$usuario,
			$observacion
		);
	}
}

function ueno_conciliar_pago_con_movimiento($mysqli, $pago, $movimiento, $usuario, $observacion_link = "", $observacion_pago = "", $monto_aplicar = null)
{
	$monto_pago_total = (int)$pago["monto_pago"];
	$monto_pago = $monto_aplicar === null ? $monto_pago_total : (int)$monto_aplicar;
	$monto_disponible = (int)$movimiento["monto_disponible"];
	$importe_credito = isset($movimiento["importe_credito"]) ? (int)$movimiento["importe_credito"] : $monto_disponible;
	$estado_anterior_pago = isset($pago["estado_conciliacion"]) ? $pago["estado_conciliacion"] : "pendiente_conciliacion";
	$cod_pagoFK = $pago["cod_pagoFK"];
	$id_movimiento = $movimiento["id_movimiento"];
	$id_pago = $pago["id"];
	$total_aplicado_pago = (int)ueno_scalar($mysqli, "SELECT IFNULL(SUM(monto_aplicado),0) FROM ueno_movimiento_pago WHERE cod_pagoFK='" . $mysqli->real_escape_string($cod_pagoFK) . "' AND estado='activo'");
	$saldo_pago = $monto_pago_total - $total_aplicado_pago;
	if ($monto_pago <= 0 || $monto_pago_total <= 0 || $saldo_pago <= 0 || $monto_pago > $saldo_pago || $monto_disponible < $monto_pago || $monto_disponible > $importe_credito) {
		return false;
	}

	$estado_link = "activo";
	if ($observacion_link == "") {
		$observacion_link = "Conciliacion automatica por comprobante y saldo disponible";
	}
	$consultaLink = "INSERT IGNORE INTO ueno_movimiento_pago
		(id_movimiento, cod_pagoFK, monto_aplicado, usuario_asocio, estado, observacion)
		VALUES (?,?,?,?,?,?)";
	$stmtLink = $mysqli->prepare($consultaLink);
	$stmtLink->bind_param(
		"ssssss",
		$id_movimiento,
		$cod_pagoFK,
		$monto_pago,
		$usuario,
		$estado_link,
		$observacion_link
	);
	if (!$stmtLink->execute()) {
		throw new Exception($stmtLink->error);
	}
	if ($stmtLink->affected_rows <= 0) {
		throw new Exception("El pago ya tiene un vinculo activo con este movimiento Ueno");
	}

	$total_aplicado_nuevo = $total_aplicado_pago + $monto_pago;
	$nuevo_disponible = $monto_disponible - $monto_pago;
	$estado_movimiento = $nuevo_disponible <= 0 ? "asignado_total" : "asignado_parcial";
	$consultaMov = "UPDATE ueno_movimiento_bancario SET monto_disponible=?, estado=? WHERE id_movimiento=? AND monto_disponible>=?";
	$stmtMov = $mysqli->prepare($consultaMov);
	$stmtMov->bind_param("ssss", $nuevo_disponible, $estado_movimiento, $id_movimiento, $monto_pago);
	if (!$stmtMov->execute()) {
		throw new Exception($stmtMov->error);
	}
	if ($stmtMov->affected_rows <= 0) {
		throw new Exception("El saldo disponible del movimiento Ueno cambio durante la conciliacion");
	}

	$estado_pago = $total_aplicado_nuevo >= $monto_pago_total ? "conciliado_ueno" : "pendiente_conciliacion";
	if ($observacion_pago == "") {
		$observacion_pago = $estado_pago == "conciliado_ueno"
			? "Conciliado automaticamente con movimiento Ueno #" . $movimiento["id_movimiento"]
			: "Aplicacion parcial con movimiento Ueno #" . $movimiento["id_movimiento"];
	}
	$consultaPago = "UPDATE pago_transferencia_conciliacion
		SET estado_conciliacion=?, usuario_ultima_revision=?, fecha_hora_revision=NOW(), observacion=?
		WHERE id=? AND activo='SI'";
	$stmtPago = $mysqli->prepare($consultaPago);
	$stmtPago->bind_param("ssss", $estado_pago, $usuario, $observacion_pago, $id_pago);
	if (!$stmtPago->execute()) {
		throw new Exception($stmtPago->error);
	}

	ueno_auditar_conciliacion(
		$mysqli,
		"CONCILIAR_PAGO",
		"pago_transferencia_conciliacion",
		$id_pago,
		$cod_pagoFK,
		$id_movimiento,
		$estado_anterior_pago,
		$estado_pago,
		$monto_pago,
		$usuario,
		$observacion_pago,
		array(
		"monto_disponible_anterior" => $monto_disponible,
		"monto_disponible_nuevo" => $nuevo_disponible,
			"monto_pago_total" => $monto_pago_total,
			"monto_pago_aplicado_anterior" => $total_aplicado_pago,
			"monto_pago_aplicado_nuevo" => $total_aplicado_nuevo,
			"estado_movimiento" => $estado_movimiento
		)
	);

	return true;
}

function ueno_ejecutar_conciliacion($mysqli, $usuario, $id_importacion = "")
{
	$resumen = ueno_resumen_conciliacion_vacio();
	$condicionMov = "";
	if ($id_importacion != "") {
		$condicionMov = " AND id_importacion='" . $mysqli->real_escape_string($id_importacion) . "'";
	}

	$sqlPagos = "SELECT pc.id, pc.cod_pagoFK, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion, p.Fecha, p.nrofactura
		FROM pago_transferencia_conciliacion pc
		INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
		WHERE pc.activo='SI'
		AND pc.estado_conciliacion='pendiente_conciliacion'
		AND pc.nro_comprobante_informado!=''
		ORDER BY pc.id ASC
		LIMIT 1000
		FOR UPDATE";
	$stmtPagos = $mysqli->prepare($sqlPagos);
	if (!$stmtPagos->execute()) {
		throw new Exception($stmtPagos->error);
	}
	$resultPagos = $stmtPagos->get_result();

	while ($pago = mysqli_fetch_assoc($resultPagos)) {
		$resumen["procesados"]++;
		if ((int)$pago["monto_pago"] <= 0) {
			ueno_observar_pago($mysqli, $pago["id"], $usuario, "Observado automaticamente: pago por transferencia con monto cero o invalido.");
			$resumen["observados"]++;
			continue;
		}
		$comprobante = $pago["nro_comprobante_informado"];
		$comprobanteSql = $mysqli->real_escape_string($comprobante);

		$sqlMovCreditos = "SELECT id_movimiento, importe_credito, monto_disponible
			FROM ueno_movimiento_bancario
			WHERE nro_comprobante='$comprobanteSql'
			AND tipo_movimiento='credito'
			AND importe_credito>0
			AND monto_disponible>0
			$condicionMov
			ORDER BY id_movimiento ASC
			FOR UPDATE";
		$resultMovCreditos = $mysqli->query($sqlMovCreditos);
		if (!$resultMovCreditos) {
			throw new Exception($mysqli->error);
		}

		$cantidadMov = $resultMovCreditos->num_rows;
		if ($cantidadMov == 1) {
			$movimiento = $resultMovCreditos->fetch_assoc();
			if ((int)$movimiento["importe_credito"] == (int)$pago["monto_pago"] && (int)$movimiento["monto_disponible"] >= (int)$pago["monto_pago"]) {
				ueno_conciliar_pago_con_movimiento($mysqli, $pago, $movimiento, $usuario);
				$resumen["conciliados"]++;
				$resumen["monto_conciliado"] += (int)$pago["monto_pago"];
				continue;
			}

			$obs = "Observado automaticamente: monto Ueno distinto al monto del pago. Banco: " . number_format($movimiento["importe_credito"], 0, ",", ".") . " / Pago: " . number_format($pago["monto_pago"], 0, ",", ".") . ". Requiere revision manual.";
			if ((int)$movimiento["monto_disponible"] < (int)$pago["monto_pago"]) {
				$obs = "Observado automaticamente: comprobante Ueno con saldo disponible insuficiente. Saldo: " . number_format($movimiento["monto_disponible"], 0, ",", ".") . " / Pago: " . number_format($pago["monto_pago"], 0, ",", ".");
				$resumen["sin_saldo"]++;
			}
			ueno_observar_pago($mysqli, $pago["id"], $usuario, $obs);
			$resumen["observados"]++;
			continue;
		}

		if ($cantidadMov > 1) {
			$obs = "Observado automaticamente: el comprobante Ueno aparece en mas de un movimiento bancario. Requiere revision manual.";
			ueno_observar_pago($mysqli, $pago["id"], $usuario, $obs);
			$resumen["observados"]++;
			$resumen["duplicados_banco"]++;
			continue;
		}

		$sqlCualquierMov = "SELECT id_movimiento, tipo_movimiento, importe_credito, importe_debito, monto_disponible
			FROM ueno_movimiento_bancario
			WHERE nro_comprobante='$comprobanteSql'
			$condicionMov
			ORDER BY id_movimiento ASC
			LIMIT 1";
		$resultCualquierMov = $mysqli->query($sqlCualquierMov);
		if (!$resultCualquierMov) {
			throw new Exception($mysqli->error);
		}

		if ($resultCualquierMov->num_rows > 0) {
			$movCualquiera = $resultCualquierMov->fetch_assoc();
			$obs = "Observado automaticamente: comprobante encontrado en Ueno, pero no habilita saldo de credito disponible.";
			if ($movCualquiera["tipo_movimiento"] != "credito") {
				$obs = "Observado automaticamente: comprobante encontrado en Ueno, pero corresponde a un movimiento no credito.";
				$resumen["no_credito"]++;
			} else {
				$resumen["sin_saldo"]++;
			}
			ueno_observar_pago($mysqli, $pago["id"], $usuario, $obs);
			$resumen["observados"]++;
			continue;
		}

		$resumen["pendientes"]++;
		$resumen["sin_movimiento"]++;
	}

	return $resumen;
}

function ueno_conciliar_automaticamente($usuario)
{
	ueno_requerir_permiso($usuario, "CONCILIARPAGOSUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$id_importacion = ueno_post("id_importacion");
	$mysqli->begin_transaction();
	try {
		$resumen = ueno_ejecutar_conciliacion($mysqli, $usuario, $id_importacion);
		$mysqli->commit();
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}

	mysqli_close($mysqli);
	ueno_json(array("1" => "exito", "2" => $resumen));
}

function ueno_buscar_pago_manual_por_id($mysqli, $id_conciliacion, $bloquear = false)
{
	$id = (int)$id_conciliacion;
	$forUpdate = $bloquear ? " FOR UPDATE" : "";
	$sql = "SELECT pc.id, pc.cod_pagoFK, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion, pc.observacion,
		pc.fecha_hora_registro, p.Fecha, p.nrofactura, p.cod_venta_fk, p.cod_creditoFK, p.titulocuota,
		IFNULL((SELECT SUM(ump.monto_aplicado) FROM ueno_movimiento_pago ump WHERE ump.cod_pagoFK=pc.cod_pagoFK AND ump.estado='activo'),0) AS monto_aplicado_ueno,
		(SELECT nombre_persona FROM persona WHERE cod_persona=p.cod_cobradorFK) AS cobrador,
		(SELECT nombre_persona FROM persona WHERE cod_persona=(SELECT cod_clienteFK FROM venta WHERE cod_venta=p.cod_venta_fk LIMIT 1)) AS cliente,
		(SELECT ci_cliente FROM cliente WHERE cod_cliente=(SELECT cod_clienteFK FROM venta WHERE cod_venta=p.cod_venta_fk LIMIT 1)) AS cedula
		FROM pago_transferencia_conciliacion pc
		INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
		WHERE pc.activo='SI' AND pc.id=$id
		LIMIT 1$forUpdate";
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}
	if ($result->num_rows == 0) {
		throw new Exception("No se encontro el pago para asignacion manual");
	}
	return $result->fetch_assoc();
}

function ueno_etiqueta_candidato($pago, $movimiento)
{
	$partes = array();
	if ((string)$pago["nro_comprobante_informado"] != "" && (string)$pago["nro_comprobante_informado"] == (string)$movimiento["nro_comprobante"]) {
		$partes[] = "Comprobante";
	}
	if ((int)$pago["monto_pago"] == (int)$movimiento["importe_credito"] || (int)$pago["monto_pago"] == (int)$movimiento["monto_disponible"]) {
		$partes[] = "Monto";
	}
	if ((int)$movimiento["monto_disponible"] >= (int)$pago["monto_pago"]) {
		$partes[] = "Saldo";
	}
	if ($movimiento["fecha_cercana"] == "SI") {
		$partes[] = "Fecha";
	}
	if (count($partes) == 0) {
		return "Revisar";
	}
	return implode(" + ", $partes);
}

function ueno_score_candidato($pago, $movimiento)
{
	$score = 0;
	if ((string)$pago["nro_comprobante_informado"] != "" && (string)$pago["nro_comprobante_informado"] == (string)$movimiento["nro_comprobante"]) {
		$score += 60;
	}
	if ((int)$pago["monto_pago"] == (int)$movimiento["importe_credito"] || (int)$pago["monto_pago"] == (int)$movimiento["monto_disponible"]) {
		$score += 25;
	}
	if ((int)$movimiento["monto_disponible"] >= (int)$pago["monto_pago"]) {
		$score += 10;
	}
	if ($movimiento["fecha_cercana"] == "SI") {
		$score += 5;
	}
	return $score;
}

function ueno_tabla_candidatos_manual($mysqli, $pago)
{
	$comprobante = $mysqli->real_escape_string($pago["nro_comprobante_informado"]);
	$monto = (int)$pago["monto_pago"];
	$fecha_pago = ueno_fecha($pago["Fecha"]);
	$condiciones = array("mv.tipo_movimiento='credito'", "mv.importe_credito>0", "mv.monto_disponible>0");
	$condicion_asistida = array();
	if ($comprobante != "") {
		$condicion_asistida[] = "mv.nro_comprobante='$comprobante'";
		$condicion_asistida[] = "mv.nro_comprobante LIKE '%$comprobante%'";
	}
	if ($monto > 0) {
		$condicion_asistida[] = "mv.importe_credito=$monto";
		$condicion_asistida[] = "mv.monto_disponible=$monto";
	}
	if ($fecha_pago != "") {
		$fechaSql = $mysqli->real_escape_string($fecha_pago);
		$condicion_asistida[] = "mv.fecha_confirmacion BETWEEN DATE_SUB('$fechaSql', INTERVAL 3 DAY) AND DATE_ADD('$fechaSql', INTERVAL 3 DAY)";
	}
	if (count($condicion_asistida) == 0) {
		$condicion_asistida[] = "1=1";
	}
	$where = implode(" AND ", $condiciones) . " AND (" . implode(" OR ", $condicion_asistida) . ")";
	$fechaCercanaSql = "'NO' AS fecha_cercana";
	if ($fecha_pago != "") {
		$fechaSql = $mysqli->real_escape_string($fecha_pago);
		$fechaCercanaSql = "CASE WHEN mv.fecha_confirmacion BETWEEN DATE_SUB('$fechaSql', INTERVAL 3 DAY) AND DATE_ADD('$fechaSql', INTERVAL 3 DAY) THEN 'SI' ELSE 'NO' END AS fecha_cercana";
	}

	$sql = "SELECT mv.id_movimiento, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante,
		mv.descripcion, mv.concepto, mv.importe_credito, mv.monto_disponible, mv.estado, $fechaCercanaSql
		FROM ueno_movimiento_bancario mv
		WHERE $where
		ORDER BY
			CASE WHEN mv.nro_comprobante='$comprobante' THEN 0 ELSE 1 END ASC,
			CASE WHEN mv.monto_disponible >= $monto THEN 0 ELSE 1 END ASC,
			CASE WHEN mv.importe_credito=$monto OR mv.monto_disponible=$monto THEN 0 ELSE 1 END ASC,
			mv.fecha_confirmacion DESC,
			mv.id_movimiento DESC
		LIMIT 120";
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}

	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$total++;
		$score = ueno_score_candidato($pago, $row);
		$etiqueta = ueno_etiqueta_candidato($pago, $row);
		$accion = "<input type='button' value='Ver detalle' class='btn4 ueno-row-action ueno-row-action--detail' onclick='uenoVerAplicacionMovimiento(" . (int)$row["id_movimiento"] . ")'>";
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:6%;text-align:center'>" . (int)$row["id_movimiento"] . "</td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["fecha_confirmacion"]) . "</td>"
			. "<td style='width:12%'>" . ueno_escape_html($row["nro_comprobante"]) . "</td>"
			. "<td style='width:19%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
			. "<td style='width:10%;text-align:right'>" . number_format($row["importe_credito"], 0, ",", ".") . "</td>"
			. "<td style='width:10%;text-align:right'>" . number_format($row["monto_disponible"], 0, ",", ".") . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["estado"]) . "</td>"
			. "<td style='width:12%'>" . ueno_escape_html($etiqueta . " " . $score) . "</td>"
			. "<td style='width:14%;text-align:center'>" . $accion . "</td>"
			. "</tr></table>";
	}

	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr>"
			. "<td style='width:100%;text-align:center'>Sin candidatos Ueno para este pago</td>"
			. "</tr></table>";
	}

	return array("html" => $html, "total" => $total);
}

function ueno_pago_manual_json($pago)
{
	$monto_pago = (int)$pago["monto_pago"];
	$monto_aplicado = isset($pago["monto_aplicado_ueno"]) ? (int)$pago["monto_aplicado_ueno"] : 0;
	$saldo_pendiente = max(0, $monto_pago - $monto_aplicado);
	return array(
		"id" => $pago["id"],
		"cod_pagoFK" => $pago["cod_pagoFK"],
		"fecha" => ueno_from_db($pago["Fecha"]),
		"factura" => ueno_from_db($pago["nrofactura"]),
		"comprobante" => ueno_from_db($pago["nro_comprobante_informado"]),
		"monto" => number_format($monto_pago, 0, ",", "."),
		"monto_num" => $monto_pago,
		"monto_aplicado" => number_format($monto_aplicado, 0, ",", "."),
		"monto_aplicado_num" => $monto_aplicado,
		"saldo_pendiente" => number_format($saldo_pendiente, 0, ",", "."),
		"saldo_pendiente_num" => $saldo_pendiente,
		"estado" => ueno_from_db(ueno_estado_pago_visual($pago["estado_conciliacion"], $monto_pago, $monto_aplicado)),
		"cliente" => ueno_from_db($pago["cliente"]),
		"cedula" => ueno_from_db(isset($pago["cedula"]) ? $pago["cedula"] : ""),
		"venta" => ueno_from_db($pago["cod_venta_fk"]),
		"cuota" => ueno_from_db(isset($pago["titulocuota"]) && $pago["titulocuota"] != "" ? $pago["titulocuota"] : $pago["nrofactura"]),
		"cod_credito" => ueno_from_db(isset($pago["cod_creditoFK"]) ? $pago["cod_creditoFK"] : ""),
		"cobrador" => ueno_from_db($pago["cobrador"]),
		"observacion" => ueno_from_db($pago["observacion"])
	);
}

function ueno_buscar_candidatos_manual($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERASIGNACIONMANUALUENO", "ASIGNARMANUALUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	try {
		$pago = ueno_buscar_pago_manual_por_id($mysqli, ueno_post("id_conciliacion"));
		$candidatos = ueno_tabla_candidatos_manual($mysqli, $pago);
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"pago" => ueno_pago_manual_json($pago),
			"tabla" => $candidatos["html"],
			"total" => $candidatos["total"]
		));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_asignar_movimiento_manual($usuario)
{
	ueno_requerir_permiso($usuario, "ASIGNARMANUALUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$id_movimiento = (int)ueno_post("id_movimiento");
	$observacion_usuario = ueno_post("observacion");
	$monto_aplicar_post = isset($_POST["monto_aplicar"]) ? ueno_monto($_POST["monto_aplicar"]) : 0;
	$mysqli->begin_transaction();
	try {
		$pago = ueno_buscar_pago_manual_por_id($mysqli, ueno_post("id_conciliacion"), true);
		$monto_aplicar = $monto_aplicar_post > 0 ? $monto_aplicar_post : (int)$pago["monto_pago"];
		if ($pago["estado_conciliacion"] != "pendiente_conciliacion" && $pago["estado_conciliacion"] != "observado") {
			throw new Exception("El pago ya no esta pendiente de asignacion manual");
		}
		$resultMov = $mysqli->query("SELECT id_movimiento, nro_comprobante, tipo_movimiento, importe_credito, importe_debito, monto_disponible, estado
			FROM ueno_movimiento_bancario
			WHERE id_movimiento=$id_movimiento
			LIMIT 1 FOR UPDATE");
		if (!$resultMov) {
			throw new Exception($mysqli->error);
		}
		if ($resultMov->num_rows == 0) {
			throw new Exception("No se encontro el movimiento Ueno seleccionado");
		}
		$movimiento = $resultMov->fetch_assoc();
		if ($movimiento["tipo_movimiento"] != "credito" || (int)$movimiento["importe_credito"] <= 0) {
			throw new Exception("El movimiento seleccionado no es un credito Ueno");
		}
		if ((int)$movimiento["monto_disponible"] < (int)$pago["monto_pago"]) {
			if ((int)$movimiento["monto_disponible"] < $monto_aplicar) {
				throw new Exception("El movimiento Ueno no tiene saldo disponible suficiente");
			}
		}
		$monto_aplicado_pago = isset($pago["monto_aplicado_ueno"]) ? (int)$pago["monto_aplicado_ueno"] : 0;
		$saldo_pago = max(0, (int)$pago["monto_pago"] - $monto_aplicado_pago);
		if ($monto_aplicar <= 0) {
			throw new Exception("El monto a aplicar debe ser mayor a cero");
		}
		if ($monto_aplicar > $saldo_pago) {
			throw new Exception("El monto supera el saldo pendiente de la cuota GoodVenta");
		}
		$comprobanteDiferente = (string)$pago["nro_comprobante_informado"] != (string)$movimiento["nro_comprobante"];
		$montoDiferente = (int)$monto_aplicar != (int)$pago["monto_pago"] || (int)$movimiento["importe_credito"] != (int)$pago["monto_pago"];
		if (($comprobanteDiferente || $montoDiferente) && $observacion_usuario == "") {
			throw new Exception("La asignacion tiene diferencia de comprobante, monto o es parcial. Agrega una observacion de revision.");
		}

		$obsExtra = $observacion_usuario != "" ? " - " . $observacion_usuario : "";
		$observacion_link = "Asignacion manual asistida por cuota GoodVenta" . $obsExtra;
		$observacion_pago = ($monto_aplicar >= $saldo_pago ? "Conciliado" : "Aplicado parcialmente") . " manualmente con movimiento Ueno #" . $movimiento["id_movimiento"] . $obsExtra;
		if (!ueno_conciliar_pago_con_movimiento($mysqli, $pago, $movimiento, $usuario, $observacion_link, $observacion_pago, $monto_aplicar)) {
			throw new Exception("No se pudo asignar el movimiento al pago");
		}

		$mysqli->commit();
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"2" => "Credito aplicado correctamente",
			"id_movimiento" => $id_movimiento,
			"id_conciliacion" => $pago["id"],
			"monto_aplicado" => number_format($monto_aplicar, 0, ",", ".")
		));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_insertar_importacion($usuario)
{
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$cuenta = ueno_post("cuenta");
	$denominacion = ueno_post("denominacion");
	$fecha_extracto = ueno_fecha(isset($_POST["fecha_extracto"]) ? $_POST["fecha_extracto"] : "");
	$periodo_desde = ueno_fecha(isset($_POST["periodo_desde"]) ? $_POST["periodo_desde"] : "");
	$periodo_hasta = ueno_fecha(isset($_POST["periodo_hasta"]) ? $_POST["periodo_hasta"] : "");
	$nombre_archivo = ueno_post("nombre_archivo_original");
	$hash_archivo = ueno_post("hash_archivo");
	$observacion = ueno_post("observacion");
	$json = isset($_POST["movimientos_json"]) ? $_POST["movimientos_json"] : "";

	if ($cuenta == "" || $nombre_archivo == "" || $hash_archivo == "" || $json == "") {
		mysqli_close($mysqli);
		ueno_json(array("1" => "camposvacio"));
	}

	$movimientos = json_decode($json, true);
	if (!is_array($movimientos)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "jsoninvalido", "2" => "No se pudo interpretar el listado de movimientos"));
	}

	$sqlDuplicadoArchivo = "SELECT id_importacion FROM ueno_importacion_extracto WHERE hash_archivo=? LIMIT 1";
	$stmtDupArchivo = $mysqli->prepare($sqlDuplicadoArchivo);
	$stmtDupArchivo->bind_param("s", $hash_archivo);
	if (!$stmtDupArchivo->execute()) {
		ueno_json(array("1" => "error", "2" => $stmtDupArchivo->error));
	}
	$resDupArchivo = $stmtDupArchivo->get_result();
	if ($resDupArchivo && $resDupArchivo->num_rows > 0) {
		$rowDup = $resDupArchivo->fetch_assoc();
		$id_importacion_existente = $rowDup["id_importacion"];
		$tabla = ueno_tabla_movimientos($mysqli, $id_importacion_existente, "", "", "", "");
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"estado_importacion" => "duplicado_archivo",
			"2" => "El archivo ya fue importado anteriormente",
			"id_importacion" => $id_importacion_existente,
			"movimientos_leidos" => count($movimientos),
			"movimientos_nuevos" => 0,
			"movimientos_duplicados" => count($movimientos),
			"tabla" => $tabla["html"]
		));
	}

	$cantidad_movimientos = 0;
	$cantidad_creditos = 0;
	$cantidad_debitos = 0;
	$total_creditos = 0;
	$total_debitos = 0;
	$normalizados = array();

	foreach ($movimientos as $mov) {
		if (!is_array($mov)) {
			continue;
		}
		$nro = isset($mov["nro_comprobante"]) ? trim((string)$mov["nro_comprobante"]) : "";
		$credito = ueno_monto(isset($mov["importe_credito"]) ? $mov["importe_credito"] : 0);
		$debito = ueno_monto(isset($mov["importe_debito"]) ? $mov["importe_debito"] : 0);
		if ($nro == "" && $credito == 0 && $debito == 0) {
			continue;
		}

		$fecha_confirmacion = ueno_fecha(isset($mov["fecha_confirmacion"]) ? $mov["fecha_confirmacion"] : "");
		$fecha_transaccion = ueno_fecha(isset($mov["fecha_transaccion"]) ? $mov["fecha_transaccion"] : "");
		$descripcion = isset($mov["descripcion"]) ? ueno_to_db($mov["descripcion"]) : "";
		$concepto = isset($mov["concepto"]) ? ueno_to_db($mov["concepto"]) : "";
		$saldo_banco = isset($mov["saldo_banco"]) && $mov["saldo_banco"] !== "" ? ueno_monto($mov["saldo_banco"]) : null;
		$tipo_movimiento = $credito > 0 ? "credito" : ($debito > 0 ? "debito" : "otro");
		$estado = $credito > 0 ? "disponible" : "registrado";
		$monto_disponible = $credito > 0 ? $credito : 0;
		$hash_movimiento = sha1($cuenta . "|" . $nro . "|" . $fecha_confirmacion . "|" . $fecha_transaccion . "|" . $credito . "|" . $debito . "|" . $descripcion . "|" . $concepto);

		$normalizados[] = array(
			"fecha_confirmacion" => $fecha_confirmacion,
			"fecha_transaccion" => $fecha_transaccion,
			"nro_comprobante" => ueno_to_db($nro),
			"descripcion" => $descripcion,
			"concepto" => $concepto,
			"importe_debito" => $debito,
			"importe_credito" => $credito,
			"tipo_movimiento" => $tipo_movimiento,
			"saldo_banco" => $saldo_banco,
			"monto_disponible" => $monto_disponible,
			"estado" => $estado,
			"hash_movimiento" => $hash_movimiento
		);

		$cantidad_movimientos++;
		if ($credito > 0) {
			$cantidad_creditos++;
			$total_creditos += $credito;
		}
		if ($debito > 0) {
			$cantidad_debitos++;
			$total_debitos += $debito;
		}
	}

	if ($cantidad_movimientos == 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "sinmovimientos", "2" => "No se encontraron movimientos validos"));
	}

	$mysqli->begin_transaction();

	$sqlImportacion = "INSERT INTO ueno_importacion_extracto
		(cuenta, denominacion, fecha_extracto, periodo_desde, periodo_hasta, nombre_archivo_original, hash_archivo, usuario_importo,
		cantidad_movimientos, cantidad_creditos, cantidad_debitos, total_creditos, total_debitos, estado, observacion)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmtImp = $mysqli->prepare($sqlImportacion);
	$estado_importacion = "importado";
	$stmtImp->bind_param("sssssssssssssss", $cuenta, $denominacion, $fecha_extracto, $periodo_desde, $periodo_hasta, $nombre_archivo, $hash_archivo, $usuario, $cantidad_movimientos, $cantidad_creditos, $cantidad_debitos, $total_creditos, $total_debitos, $estado_importacion, $observacion);
	if (!$stmtImp->execute()) {
		$mysqli->rollback();
		ueno_json(array("1" => "error", "2" => $stmtImp->error));
	}
	$id_importacion = $mysqli->insert_id;

	$nuevos = 0;
	$duplicados = 0;
	$sqlMovimiento = "INSERT INTO ueno_movimiento_bancario
		(id_importacion, cuenta, fecha_confirmacion, fecha_transaccion, nro_comprobante, descripcion, concepto, importe_debito,
		importe_credito, tipo_movimiento, saldo_banco, monto_disponible, estado, hash_movimiento)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmtMov = $mysqli->prepare($sqlMovimiento);

	foreach ($normalizados as $mov) {
		$mov_hash_movimiento = $mov["hash_movimiento"];
		$mov_fecha_confirmacion = $mov["fecha_confirmacion"];
		$mov_fecha_transaccion = $mov["fecha_transaccion"];
		$mov_nro_comprobante = $mov["nro_comprobante"];
		$mov_descripcion = $mov["descripcion"];
		$mov_concepto = $mov["concepto"];
		$mov_importe_debito = $mov["importe_debito"];
		$mov_importe_credito = $mov["importe_credito"];
		$mov_tipo_movimiento = $mov["tipo_movimiento"];
		$mov_saldo_banco = $mov["saldo_banco"];
		$mov_monto_disponible = $mov["monto_disponible"];
		$mov_estado = $mov["estado"];

		$sqlExiste = "SELECT id_movimiento FROM ueno_movimiento_bancario WHERE hash_movimiento=? LIMIT 1";
		$stmtExiste = $mysqli->prepare($sqlExiste);
		$stmtExiste->bind_param("s", $mov_hash_movimiento);
		if (!$stmtExiste->execute()) {
			$mysqli->rollback();
			ueno_json(array("1" => "error", "2" => $stmtExiste->error));
		}
		$resExiste = $stmtExiste->get_result();
		if ($resExiste && $resExiste->num_rows > 0) {
			$duplicados++;
			continue;
		}

		$stmtMov->bind_param(
			"ssssssssssssss",
			$id_importacion,
			$cuenta,
			$mov_fecha_confirmacion,
			$mov_fecha_transaccion,
			$mov_nro_comprobante,
			$mov_descripcion,
			$mov_concepto,
			$mov_importe_debito,
			$mov_importe_credito,
			$mov_tipo_movimiento,
			$mov_saldo_banco,
			$mov_monto_disponible,
			$mov_estado,
			$mov_hash_movimiento
		);
		if (!$stmtMov->execute()) {
			$mysqli->rollback();
			ueno_json(array("1" => "error", "2" => $stmtMov->error));
		}
		$nuevos++;
	}

	ueno_auditar_conciliacion(
		$mysqli,
		"IMPORTAR_EXTRACTO",
		"ueno_importacion_extracto",
		$id_importacion,
		"",
		"",
		"",
		$estado_importacion,
		$total_creditos,
		$usuario,
		"Importacion Ueno: " . $nombre_archivo,
		array(
			"cuenta" => $cuenta,
			"movimientos_leidos" => $cantidad_movimientos,
			"movimientos_nuevos" => $nuevos,
			"movimientos_duplicados" => $duplicados,
			"total_debitos" => $total_debitos
		)
	);

	$mysqli->commit();

	$resumen_conciliacion = ueno_resumen_conciliacion_vacio();
	if (ueno_usuario_tiene_permiso($usuario, "CONCILIARPAGOSUENO")) {
		$mysqli->begin_transaction();
		try {
			$resumen_conciliacion = ueno_ejecutar_conciliacion($mysqli, $usuario, $id_importacion);
			$mysqli->commit();
		} catch (Exception $e) {
			$mysqli->rollback();
			$resumen_conciliacion["error"] = $e->getMessage();
		}
	}

	$tabla = ueno_tabla_movimientos($mysqli, $id_importacion, "", "", "", "");
	mysqli_close($mysqli);

	ueno_json(array(
		"1" => "exito",
		"estado_importacion" => "importado",
		"id_importacion" => $id_importacion,
		"movimientos_leidos" => $cantidad_movimientos,
		"movimientos_nuevos" => $nuevos,
		"movimientos_duplicados" => $duplicados,
		"cantidad_creditos" => $cantidad_creditos,
		"cantidad_debitos" => $cantidad_debitos,
		"total_creditos" => number_format($total_creditos, 0, ",", "."),
		"total_debitos" => number_format($total_debitos, 0, ",", "."),
		"conciliacion" => $resumen_conciliacion,
		"tabla" => $tabla["html"]
	));
}

function ueno_tabla_movimientos($mysqli, $id_importacion, $fecha_desde, $fecha_hasta, $comprobante, $estado, $filtro_rapido = "todos")
{
	$condicion = "";
	if ($id_importacion != "") {
		$condicion .= " AND mv.id_importacion='" . $mysqli->real_escape_string($id_importacion) . "'";
	}
	if ($fecha_desde != "") {
		$condicion .= " AND mv.fecha_confirmacion>='" . $mysqli->real_escape_string($fecha_desde) . "'";
	}
	if ($fecha_hasta != "") {
		$condicion .= " AND mv.fecha_confirmacion<='" . $mysqli->real_escape_string($fecha_hasta) . "'";
	}
	if ($comprobante != "") {
		$condicion .= " AND mv.nro_comprobante LIKE '%" . $mysqli->real_escape_string($comprobante) . "%'";
	}
	if ($estado != "") {
		$condicion .= " AND mv.estado='" . $mysqli->real_escape_string($estado) . "'";
	}

	$sql = "SELECT mv.id_movimiento, mv.id_importacion, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante, mv.descripcion,
		mv.concepto, mv.importe_debito, mv.importe_credito, mv.monto_disponible, mv.estado, imp.nombre_archivo_original
		FROM ueno_movimiento_bancario mv
		INNER JOIN ueno_importacion_extracto imp ON imp.id_importacion=mv.id_importacion
		WHERE mv.id_movimiento!='0' $condicion
		ORDER BY mv.fecha_confirmacion DESC, mv.id_movimiento DESC
		LIMIT 400";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		ueno_json(array("1" => "error", "2" => $stmt->error));
	}
	$result = $stmt->get_result();
	$html = "";
	$total = 0;
	$total_creditos = 0;
	$total_debitos = 0;
	$resumen = array(
		"total_base" => 0,
		"disponibles" => 0,
		"parciales" => 0,
		"conciliados" => 0,
		"con_saldo" => 0,
		"saldo_disponible" => 0,
		"saldo_disponible_fmt" => "0"
	);
	$styleName = "tableRegistroSearch";

	while ($row = mysqli_fetch_assoc($result)) {
		$credito = (int)$row["importe_credito"];
		$debito = (int)$row["importe_debito"];
		$disponible = (int)$row["monto_disponible"];
		$aplicado = $credito > 0 ? max(0, $credito - $disponible) : 0;
		if ($credito > 0 && $aplicado > $credito) {
			$aplicado = $credito;
		}
		$porcentaje_aplicado = $credito > 0 ? min(100, max(0, round(($aplicado * 100) / $credito))) : 0;
		$estado_visual = ueno_estado_movimiento_texto($row["estado"], $credito, $disponible, $debito);
		$estado_clave = ueno_estado_movimiento_clave($estado_visual);
		$resumen["total_base"]++;
		if ($estado_clave == "disponible") {
			$resumen["disponibles"]++;
		}
		if ($estado_clave == "parcial") {
			$resumen["parciales"]++;
		}
		if ($estado_clave == "conciliado") {
			$resumen["conciliados"]++;
		}
		if ($credito > 0 && $disponible > 0) {
			$resumen["con_saldo"]++;
			$resumen["saldo_disponible"] += $disponible;
		}
		if (!ueno_movimiento_cumple_filtro_rapido($filtro_rapido, $estado_clave, $credito, $disponible)) {
			continue;
		}
		$total++;
		$total_creditos += $credito;
		$total_debitos += $debito;
		$datos_movimiento = array(
			"id_movimiento" => (int)$row["id_movimiento"],
			"id_importacion" => (int)$row["id_importacion"],
			"fecha_confirmacion" => ueno_from_db($row["fecha_confirmacion"]),
			"fecha_transaccion" => ueno_from_db($row["fecha_transaccion"]),
			"nro_comprobante" => ueno_from_db($row["nro_comprobante"]),
			"descripcion" => ueno_from_db($row["descripcion"]),
			"concepto" => ueno_from_db($row["concepto"]),
			"importe_credito" => $credito,
			"importe_credito_fmt" => number_format($credito, 0, ",", "."),
			"importe_debito" => $debito,
			"importe_debito_fmt" => number_format($debito, 0, ",", "."),
			"monto_aplicado" => $aplicado,
			"monto_aplicado_fmt" => number_format($aplicado, 0, ",", "."),
			"monto_disponible" => $disponible,
			"monto_disponible_fmt" => number_format($disponible, 0, ",", "."),
			"estado" => $estado_visual,
			"estado_clave" => $estado_clave
		);
		$datos_js = htmlspecialchars(json_encode($datos_movimiento), ENT_QUOTES, 'UTF-8');
		if ($credito > 0 && $disponible > 0 && $estado_clave != "revisar") {
			if ($estado_clave == "parcial") {
				$accion = "<input type='button' value='Ver aplicaciones' class='btn4 ueno-row-action ueno-row-action--trace' onclick='uenoVerAplicacionMovimiento(" . (int)$row["id_movimiento"] . ")'>";
			} else {
				$accion = "<input type='button' value='Ver detalle' class='btn4 ueno-row-action ueno-row-action--detail' onclick='uenoSeleccionarMovimientoTrabajo(" . $datos_js . ")'>";
			}
		} elseif ($estado_clave == "revisar") {
			$accion = "<span class='ueno-row-note ueno-row-note--muted'>Revisar</span>";
		} elseif ($credito > 0) {
			$accion = "<input type='button' value='Ver aplicacion' class='btn4 ueno-row-action ueno-row-action--view' onclick='uenoVerAplicacionMovimiento(" . (int)$row["id_movimiento"] . ")'>";
		} elseif ($debito > 0) {
			$accion = "<input type='button' value='Ver detalle' class='btn4 ueno-row-action ueno-row-action--detail' onclick='uenoSeleccionarMovimientoTrabajo(" . $datos_js . ")'>";
		} else {
			$accion = "<span class='ueno-row-note ueno-row-note--muted'>Sin accion</span>";
		}
		$aplicado_html = $credito > 0
			? "<div class='ueno-applied-cell'><strong>" . number_format($aplicado, 0, ",", ".") . "</strong>"
				. ($estado_clave == "parcial" ? "<small>de " . number_format($credito, 0, ",", ".") . "</small><span class='ueno-progress'><i style='width:" . $porcentaje_aplicado . "%'></i></span>" : "")
				. "</div>"
			: "<span class='ueno-muted-money'>-</span>";
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' class='ueno-movimiento-row ueno-movimiento-row--" . $estado_clave . "' data-ueno-estado='" . $estado_clave . "' data-ueno-disponible='" . $disponible . "' data-ueno-aplicado='" . $aplicado . "'>"
			. "<td style='width:7%'>" . ueno_escape_html($row["fecha_confirmacion"]) . "</td>"
			. "<td style='width:7%'>" . ueno_escape_html($row["fecha_transaccion"]) . "</td>"
			. "<td style='width:11%'>" . ueno_escape_html($row["nro_comprobante"]) . "</td>"
			. "<td style='width:18%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
			. "<td style='width:11%'>" . ueno_escape_html($row["concepto"]) . "</td>"
			. "<td style='width:7%;text-align:right'>" . number_format($debito, 0, ",", ".") . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($credito, 0, ",", ".") . "</td>"
			. "<td style='width:8%;text-align:right'>" . $aplicado_html . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($disponible, 0, ",", ".") . "</td>"
			. "<td style='width:7%'><span class='ueno-status-badge ueno-status-badge--" . $estado_clave . "'>" . ueno_escape_html($estado_visual) . "</span></td>"
			. "<td style='width:8%;text-align:center'>" . $accion . "</td>"
			. "</tr></table>";
	}
	$resumen["saldo_disponible_fmt"] = number_format($resumen["saldo_disponible"], 0, ",", ".");
	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'><td style='width:100%;text-align:center'>No hay movimientos para este filtro.</td></tr></table>";
	}

	return array(
		"html" => $html,
		"total" => $total,
		"total_creditos" => $total_creditos,
		"total_debitos" => $total_debitos,
		"resumen" => $resumen
	);
}

function ueno_buscar_movimientos()
{
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$id_importacion = ueno_post("id_importacion");
	$fecha_desde = ueno_fecha(isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : "");
	$fecha_hasta = ueno_fecha(isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : "");
	$comprobante = ueno_post("nro_comprobante");
	$estado = ueno_post("estado");
	$filtro_rapido = ueno_post("filtro_rapido");

	$tabla = ueno_tabla_movimientos($mysqli, $id_importacion, $fecha_desde, $fecha_hasta, $comprobante, $estado, $filtro_rapido);
	mysqli_close($mysqli);

	ueno_json(array(
		"1" => "exito",
		"2" => $tabla["html"],
		"3" => $tabla["total"],
		"4" => number_format($tabla["total_creditos"], 0, ",", "."),
		"5" => number_format($tabla["total_debitos"], 0, ",", "."),
		"resumen" => $tabla["resumen"]
	));
}

function ueno_buscar_importaciones()
{
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$fecha_desde = ueno_fecha(isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : "");
	$fecha_hasta = ueno_fecha(isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : "");
	$condicion = "";
	if ($fecha_desde != "") {
		$condicion .= " AND fecha_extracto>='" . $mysqli->real_escape_string($fecha_desde) . "'";
	}
	if ($fecha_hasta != "") {
		$condicion .= " AND fecha_extracto<='" . $mysqli->real_escape_string($fecha_hasta) . "'";
	}

	$sql = "SELECT id_importacion, cuenta, fecha_extracto, nombre_archivo_original, fecha_hora_importacion,
		cantidad_movimientos, cantidad_creditos, cantidad_debitos, total_creditos, total_debitos, estado
		FROM ueno_importacion_extracto
		WHERE id_importacion!='0' $condicion
		ORDER BY id_importacion DESC
		LIMIT 80";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		ueno_json(array("1" => "error", "2" => $stmt->error));
	}
	$result = $stmt->get_result();
	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$total++;
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' onclick='uenoSeleccionarImportacion(" . $row["id_importacion"] . ")'>"
			. "<td style='width:8%'>" . $row["id_importacion"] . "</td>"
			. "<td style='width:12%'>" . ueno_escape_html($row["cuenta"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["fecha_extracto"]) . "</td>"
			. "<td style='width:24%'>" . ueno_escape_html($row["nombre_archivo_original"]) . "</td>"
			. "<td style='width:14%'>" . ueno_escape_html($row["fecha_hora_importacion"]) . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($row["cantidad_movimientos"], 0, ",", ".") . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($row["total_creditos"], 0, ",", ".") . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($row["total_debitos"], 0, ",", ".") . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["estado"]) . "</td>"
			. "</tr></table>";
	}
	mysqli_close($mysqli);
	ueno_json(array("1" => "exito", "2" => $html, "3" => $total));
}

function ueno_buscar_pagos_pendientes()
{
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$estado = ueno_post("estado");
	$comprobante = ueno_post("nro_comprobante");
	$cliente = ueno_post("cliente");
	$venta = ueno_post("venta");
	$monto_busqueda = isset($_POST["monto"]) ? ueno_monto($_POST["monto"]) : 0;
	$monto_referencia = isset($_POST["monto_referencia"]) ? ueno_monto($_POST["monto_referencia"]) : 0;
	$condicion = "";
	if ($estado != "") {
		$condicion .= " AND pc.estado_conciliacion='" . $mysqli->real_escape_string($estado) . "'";
	}
	if ($comprobante != "") {
		$condicion .= " AND pc.nro_comprobante_informado LIKE '%" . $mysqli->real_escape_string($comprobante) . "%'";
	}
	if ($cliente != "") {
		$clienteSql = $mysqli->real_escape_string($cliente);
		$condicion .= " AND ("
			. "(SELECT nombre_persona FROM persona WHERE cod_persona=(SELECT cod_clienteFK FROM venta WHERE cod_venta=p.cod_venta_fk LIMIT 1)) LIKE '%$clienteSql%'"
			. " OR (SELECT ci_cliente FROM cliente WHERE cod_cliente=(SELECT cod_clienteFK FROM venta WHERE cod_venta=p.cod_venta_fk LIMIT 1)) LIKE '%$clienteSql%'"
			. ")";
	}
	if ($venta != "") {
		$ventaSql = $mysqli->real_escape_string($venta);
		$condicion .= " AND (p.cod_venta_fk LIKE '%$ventaSql%' OR p.nroventa LIKE '%$ventaSql%' OR p.nrofactura LIKE '%$ventaSql%')";
	}
	if ($monto_busqueda > 0) {
		$condicion .= " AND pc.monto_pago=$monto_busqueda";
	}
	$comprobanteSql = $mysqli->real_escape_string($comprobante);

	$sql = "SELECT pc.id, pc.cod_pagoFK, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion, pc.observacion,
		pc.fecha_hora_registro, p.Fecha, p.nrofactura, p.cod_venta_fk, p.cod_creditoFK, p.titulocuota,
		IFNULL((SELECT SUM(ump.monto_aplicado) FROM ueno_movimiento_pago ump WHERE ump.cod_pagoFK=pc.cod_pagoFK AND ump.estado='activo'),0) AS monto_aplicado_ueno,
		(SELECT nombre_persona FROM persona WHERE cod_persona=p.cod_cobradorFK) AS cobrador,
		(SELECT nombre_persona FROM persona WHERE cod_persona=(SELECT cod_clienteFK FROM venta WHERE cod_venta=p.cod_venta_fk LIMIT 1)) AS cliente,
		(SELECT ci_cliente FROM cliente WHERE cod_cliente=(SELECT cod_clienteFK FROM venta WHERE cod_venta=p.cod_venta_fk LIMIT 1)) AS cedula
		FROM pago_transferencia_conciliacion pc
		INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
		WHERE pc.activo='SI' $condicion
		ORDER BY
			CASE WHEN '$comprobanteSql'<>'' AND pc.nro_comprobante_informado='$comprobanteSql' THEN 0 ELSE 1 END ASC,
			CASE WHEN $monto_referencia>0 AND pc.monto_pago=$monto_referencia THEN 0 ELSE 1 END ASC,
			pc.id DESC
		LIMIT 300";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		ueno_json(array("1" => "error", "2" => $stmt->error));
	}
	$result = $stmt->get_result();
	$html = "";
	$total = 0;
	$total_monto = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$total++;
		$monto_pago = (int)$row["monto_pago"];
		$monto_aplicado = (int)$row["monto_aplicado_ueno"];
		$saldo_pendiente = max(0, $monto_pago - $monto_aplicado);
		$total_monto += $saldo_pendiente;
		$estado_visual = ueno_estado_pago_visual($row["estado_conciliacion"], $monto_pago, $monto_aplicado);
		$coincidencias = array();
		if ($comprobante != "" && (string)$row["nro_comprobante_informado"] == (string)$comprobante) {
			$coincidencias[] = "Comprobante exacto";
		} elseif ($comprobante != "" && strpos((string)$row["nro_comprobante_informado"], (string)$comprobante) !== false) {
			$coincidencias[] = "Comprobante parcial";
		}
		if ($monto_referencia > 0 && $monto_pago == $monto_referencia) {
			$coincidencias[] = "Mismo monto";
		}
		if ($saldo_pendiente > 0 && count($coincidencias) == 0) {
			$coincidencias[] = "Posible coincidencia";
		}
		if ($saldo_pendiente <= 0) {
			$coincidencias[] = "Sin saldo";
		}
		$coincidencia = implode(" + ", $coincidencias);
		$monto_sugerido = $monto_referencia > 0 ? min($monto_referencia, $saldo_pendiente) : $saldo_pendiente;
		$datos_pago = array(
			"id" => (int)$row["id"],
			"cod_pagoFK" => (int)$row["cod_pagoFK"],
			"cliente" => ueno_from_db($row["cliente"]),
			"cedula" => ueno_from_db($row["cedula"]),
			"venta" => ueno_from_db($row["cod_venta_fk"]),
			"cuota" => ueno_from_db($row["titulocuota"] != "" ? $row["titulocuota"] : $row["nrofactura"]),
			"cod_credito" => ueno_from_db($row["cod_creditoFK"]),
			"fecha" => ueno_from_db($row["Fecha"]),
			"factura" => ueno_from_db($row["nrofactura"]),
			"comprobante" => ueno_from_db($row["nro_comprobante_informado"]),
			"monto" => number_format($monto_pago, 0, ",", "."),
			"monto_num" => $monto_pago,
			"monto_aplicado" => number_format($monto_aplicado, 0, ",", "."),
			"monto_aplicado_num" => $monto_aplicado,
			"saldo_pendiente" => number_format($saldo_pendiente, 0, ",", "."),
			"saldo_pendiente_num" => $saldo_pendiente,
			"monto_sugerido" => number_format($monto_sugerido, 0, ",", "."),
			"monto_sugerido_num" => $monto_sugerido,
			"estado" => ueno_from_db($estado_visual),
			"coincidencia" => ueno_from_db($coincidencia),
			"cobrador" => ueno_from_db($row["cobrador"]),
			"observacion" => ueno_from_db($row["observacion"])
		);
		$accion_manual = "<span class='ueno-row-note ueno-row-note--muted'>Solo consulta</span>";
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' data-ueno-pago-id='" . (int)$row["id"] . "'>"
			. "<td style='width:15%'>" . ueno_escape_html($row["cliente"]) . "</td>"
			. "<td style='width:7%'>" . ueno_escape_html($row["cedula"]) . "</td>"
			. "<td style='width:8%;text-align:center'>" . ueno_escape_html($row["cod_venta_fk"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["titulocuota"] != "" ? $row["titulocuota"] : $row["nrofactura"]) . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["Fecha"]) . "</td>"
			. "<td style='width:9%;text-align:right'>" . number_format($saldo_pendiente, 0, ",", ".") . "</td>"
			. "<td style='width:9%;text-align:right'>" . number_format($monto_sugerido, 0, ",", ".") . "</td>"
			. "<td style='width:10%'><span class='ueno-status-badge'>" . ueno_escape_html($estado_visual) . "</span></td>"
			. "<td style='width:11%'>" . ueno_escape_html($coincidencia) . "</td>"
			. "<td style='width:13%;text-align:center'>" . $accion_manual . "</td>"
			. "</tr></table>";
	}
	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr>"
			. "<td style='width:100%;text-align:center'>No encontramos cuotas candidatas. Puedes buscar por cliente, cedula, venta o comprobante.</td>"
			. "</tr></table>";
	}
	mysqli_close($mysqli);
	ueno_json(array("1" => "exito", "2" => $html, "3" => $total, "4" => number_format($total_monto, 0, ",", ".")));
}

function ueno_numero($valor)
{
	return number_format((int)$valor, 0, ",", ".");
}

function ueno_scalar($mysqli, $sql)
{
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}
	$row = $result->fetch_row();
	return $row ? (int)$row[0] : 0;
}

function ueno_turno_caja($local, $fecha_cierre)
{
	$localNormal = strtoupper((string)$local);
	if (strpos($localNormal, "CERRO") === false) {
		return "Turno unico";
	}

	if ($fecha_cierre == "") {
		return "Turno abierto";
	}

	if ($fecha_cierre == "0000-00-00 00:00:00") {
		return "Turno abierto";
	}

	$fecha = strtotime($fecha_cierre);
	if ($fecha === false) {
		return "Turno abierto";
	}

	$hora = (int)date("H", $fecha);
	if ($hora <= 18) {
		return "Turno 1";
	}
	return "Turno 2";
}

function ueno_estado_cierre_conciliacion($pendiente, $observado, $sin_comprobante, $total_transferencia)
{
	if ($sin_comprobante > 0 || $observado > 0) {
		return "Con observacion";
	}
	if ($pendiente > 0) {
		return "Pendiente";
	}
	if ($total_transferencia > 0) {
		return "Conciliado";
	}
	return "Sin transferencias";
}

function ueno_cierres_esperados($mysqli, $local)
{
	if ($local == "") {
		return 6;
	}

	$localNormal = strtoupper((string)$local);
	if (strpos($localNormal, "CERRO") !== false) {
		return 2;
	}

	$localSql = $mysqli->real_escape_string($local);
	$whereLocal = "Nombre LIKE '%$localSql%'";
	if (is_numeric($local)) {
		$whereLocal = "cod_local='$localSql' OR Nombre LIKE '%$localSql%'";
	}
	$result = $mysqli->query("SELECT Nombre FROM local WHERE $whereLocal ORDER BY cod_local ASC LIMIT 1");
	if ($result && ($row = $result->fetch_row())) {
		$nombreNormal = strtoupper((string)$row[0]);
		if (strpos($nombreNormal, "CERRO") !== false) {
			return 2;
		}
	}

	return 1;
}

function ueno_buscar_resumen_tesoreria($usuario)
{
	ueno_requerir_permiso($usuario, "VERCONCILIACIONUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$fecha_operativa = ueno_fecha(isset($_POST["fecha_operativa"]) ? $_POST["fecha_operativa"] : "");
	$fecha_bancaria = ueno_fecha(isset($_POST["fecha_bancaria"]) ? $_POST["fecha_bancaria"] : "");
	$local = ueno_post("local");
	if ($fecha_operativa == "") {
		$fecha_operativa = date("Y-m-d");
	}
	if ($fecha_bancaria == "") {
		$fecha_bancaria = $fecha_operativa;
	}

	$fecha_operativa_sql = $mysqli->real_escape_string($fecha_operativa);
	$fecha_bancaria_sql = $mysqli->real_escape_string($fecha_bancaria);
	$condicion_local = "";
	if ($local != "") {
		$localSql = $mysqli->real_escape_string($local);
		$condicion_local = " AND l.Nombre LIKE '%$localSql%'";
		if (is_numeric($local)) {
			$condicion_local = " AND (ar.cod_local='$localSql' OR l.Nombre LIKE '%$localSql%')";
		}
	}

	try {
		$total_ueno = ueno_scalar($mysqli, "SELECT IFNULL(SUM(importe_credito),0) FROM ueno_movimiento_bancario WHERE fecha_confirmacion='$fecha_bancaria_sql' AND tipo_movimiento='credito'");
		$ueno_disponible = ueno_scalar($mysqli, "SELECT IFNULL(SUM(monto_disponible),0) FROM ueno_movimiento_bancario WHERE fecha_confirmacion='$fecha_bancaria_sql' AND tipo_movimiento='credito'");
		$ueno_sin_aplicar = ueno_scalar($mysqli, "SELECT COUNT(*) FROM ueno_movimiento_bancario WHERE fecha_confirmacion='$fecha_bancaria_sql' AND tipo_movimiento='credito' AND monto_disponible>0");

		$sqlCierres = "SELECT ar.idarqueocaja, ar.fechaapertura, ar.fechacierre, ar.estado, ar.lote, ar.montoapertura, ar.montocierre,
			ar.cod_local, IFNULL(l.Nombre,'') as local_nombre, IFNULL(cj.cajanro,'') as caja_nombre,
			IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=ar.codusuarioap LIMIT 1),'') as usuario_apertura,
			IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=ar.codusuarioce LIMIT 1),'') as usuario_cierre
			FROM arqueocaja ar
			LEFT JOIN local l ON l.cod_local=ar.cod_local
			LEFT JOIN caja cj ON cj.idcaja=ar.caja_idcaja
			WHERE DATE(COALESCE(
				NULLIF(NULLIF(CAST(ar.fechacierre AS CHAR), ''), '0000-00-00 00:00:00'),
				NULLIF(NULLIF(CAST(ar.fechaapertura AS CHAR), ''), '0000-00-00 00:00:00')
			))='$fecha_operativa_sql' $condicion_local
			ORDER BY l.Nombre ASC, ar.fechaapertura ASC, ar.idarqueocaja ASC";
		$stmt = $mysqli->prepare($sqlCierres);
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$result = $stmt->get_result();
		$html = "";
		$styleName = "tableRegistroSearch";
		$cierres_realizados = 0;
		$cierres_con_observacion = 0;
		$total_gv = 0;
		$total_conciliado = 0;
		$total_pendiente = 0;
		$total_observado = 0;
		$total_sin_comprobante = 0;

		while ($row = mysqli_fetch_assoc($result)) {
			$cierres_realizados++;
			$id_arqueo = $mysqli->real_escape_string($row["idarqueocaja"]);
			$transferencia_total = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' AND p.codApertura='$id_arqueo'");
			$transferencia_conciliada = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' AND pc.estado_conciliacion='conciliado_ueno' AND p.codApertura='$id_arqueo'");
			$transferencia_pendiente = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' AND pc.estado_conciliacion='pendiente_conciliacion' AND p.codApertura='$id_arqueo'");
			$transferencia_observada = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' AND pc.estado_conciliacion IN ('observado','rechazado') AND p.codApertura='$id_arqueo'");
			$sin_comprobante = ueno_scalar($mysqli, "SELECT COUNT(*)
				FROM pago p
				INNER JOIN tipopago tp ON tp.cod_tipoPago=p.cod_tipoPagoFK
				LEFT JOIN pago_transferencia_conciliacion pc ON pc.cod_pagoFK=p.idPago AND pc.activo='SI'
				WHERE p.codApertura='$id_arqueo'
				AND UPPER(tp.nombre) LIKE '%TRANSFERENCIA%'
				AND pc.id IS NULL");

			$total_gv += $transferencia_total;
			$total_conciliado += $transferencia_conciliada;
			$total_pendiente += $transferencia_pendiente;
			$total_observado += $transferencia_observada;
			$total_sin_comprobante += $sin_comprobante;

			$estado_conciliacion = ueno_estado_cierre_conciliacion($transferencia_pendiente, $transferencia_observada, $sin_comprobante, $transferencia_total);
			if ($estado_conciliacion == "Con observacion") {
				$cierres_con_observacion++;
			}

			$turno = ueno_turno_caja($row["local_nombre"], $row["fechacierre"]);
			$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
			$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
				. "<td style='width:13%'>" . ueno_escape_html($row["local_nombre"]) . "</td>"
				. "<td style='width:7%'>" . ueno_escape_html($turno) . "</td>"
				. "<td style='width:7%'>" . ueno_escape_html($row["caja_nombre"]) . "</td>"
				. "<td style='width:7%'>" . ueno_escape_html($row["lote"]) . "</td>"
				. "<td style='width:9%'>" . ueno_escape_html($row["fechaapertura"]) . "</td>"
				. "<td style='width:9%'>" . ueno_escape_html($row["fechacierre"]) . "</td>"
				. "<td style='width:6%'>" . ueno_escape_html($row["estado"]) . "</td>"
				. "<td style='width:8%;text-align:right'>" . ueno_numero($transferencia_total) . "</td>"
				. "<td style='width:8%;text-align:right'>" . ueno_numero($transferencia_conciliada) . "</td>"
				. "<td style='width:7%;text-align:right'>" . ueno_numero($transferencia_pendiente) . "</td>"
				. "<td style='width:6%;text-align:right'>" . ueno_numero($transferencia_observada) . "</td>"
				. "<td style='width:4%;text-align:center'>" . ueno_escape_html($sin_comprobante) . "</td>"
				. "<td style='width:9%'>" . ueno_escape_html($estado_conciliacion) . "</td>"
				. "</tr></table>";
		}

		if ($html == "") {
			$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr>"
				. "<td style='width:100%;text-align:center'>Sin cierres registrados para los filtros seleccionados</td>"
				. "</tr></table>";
		}

		$cierres_esperados = ueno_cierres_esperados($mysqli, $local);
		$cierres_pendientes = $cierres_esperados - $cierres_realizados;
		if ($cierres_pendientes < 0) {
			$cierres_pendientes = 0;
		}
		$diferencia = $total_ueno - $total_gv;

		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"2" => $html,
			"fecha_operativa" => $fecha_operativa,
			"fecha_bancaria" => $fecha_bancaria,
			"cierres_esperados" => $cierres_esperados,
			"cierres_realizados" => $cierres_realizados,
			"cierres_pendientes" => $cierres_pendientes,
			"cierres_observacion" => $cierres_con_observacion,
			"total_ueno" => ueno_numero($total_ueno),
			"total_gv" => ueno_numero($total_gv),
			"diferencia" => ueno_numero($diferencia),
			"total_conciliado" => ueno_numero($total_conciliado),
			"total_pendiente" => ueno_numero($total_pendiente),
			"total_observado" => ueno_numero($total_observado),
			"total_sin_comprobante" => $total_sin_comprobante,
			"ueno_disponible" => ueno_numero($ueno_disponible),
			"ueno_sin_aplicar" => $ueno_sin_aplicar
		));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_buscar_auditoria($usuario)
{
	ueno_requerir_permiso($usuario, "VERAUDITORIAUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tabla_existe($mysqli, "ueno_auditoria_conciliacion")) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno_etapa6.sql"));
	}

	$fecha_desde = ueno_fecha(isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : "");
	$fecha_hasta = ueno_fecha(isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : "");
	$accion = ueno_post("accion");
	$condicion = "";
	if ($fecha_desde != "") {
		$condicion .= " AND DATE(a.fecha_hora)>='" . $mysqli->real_escape_string($fecha_desde) . "'";
	}
	if ($fecha_hasta != "") {
		$condicion .= " AND DATE(a.fecha_hora)<='" . $mysqli->real_escape_string($fecha_hasta) . "'";
	}
	if ($accion != "") {
		$accionSql = $mysqli->real_escape_string($accion);
		$condicion .= " AND (a.accion LIKE '%$accionSql%' OR a.observacion LIKE '%$accionSql%' OR a.id_movimiento='$accionSql')";
	}

	$sql = "SELECT a.id_auditoria, a.fecha_hora, a.accion, a.tabla_afectada, a.registro_id, a.cod_pagoFK,
		a.id_movimiento, a.estado_anterior, a.estado_nuevo, a.monto, a.usuario, a.observacion,
		IFNULL(p.nrofactura,'') AS nrofactura
		FROM ueno_auditoria_conciliacion a
		LEFT JOIN pago p ON p.idPago=a.cod_pagoFK
		WHERE a.id_auditoria!='0' $condicion
		ORDER BY a.id_auditoria DESC
		LIMIT 250";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $error));
	}
	$result = $stmt->get_result();
	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$total++;
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:5%;text-align:center'>" . (int)$row["id_auditoria"] . "</td>"
			. "<td style='width:12%'>" . ueno_escape_html($row["fecha_hora"]) . "</td>"
			. "<td style='width:14%'>" . ueno_escape_html($row["accion"]) . "</td>"
			. "<td style='width:12%'>" . ueno_escape_html($row["tabla_afectada"]) . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["nrofactura"]) . "</td>"
			. "<td style='width:7%;text-align:center'>" . ueno_escape_html($row["id_movimiento"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["estado_anterior"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["estado_nuevo"]) . "</td>"
			. "<td style='width:8%;text-align:right'>" . ueno_numero($row["monto"]) . "</td>"
			. "<td style='width:5%;text-align:center'>" . ueno_escape_html($row["usuario"]) . "</td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["observacion"]) . "</td>"
			. "</tr></table>";
	}
	mysqli_close($mysqli);
	ueno_json(array("1" => "exito", "2" => $html, "3" => $total));
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	$operacion = isset($_POST["funt"]) ? $_POST["funt"] : "";
	$usuario = ueno_validar_sesion();

	if ($operacion == "guardar_importacion") {
		ueno_insertar_importacion($usuario);
	}
	if ($operacion == "buscar_importaciones") {
		ueno_buscar_importaciones();
	}
	if ($operacion == "buscar_movimientos") {
		ueno_buscar_movimientos();
	}
	if ($operacion == "buscar_pagos_pendientes") {
		ueno_buscar_pagos_pendientes();
	}
	if ($operacion == "conciliar_automaticamente") {
		ueno_bloquear_proceso_mesa();
	}
	if ($operacion == "buscar_resumen_tesoreria") {
		ueno_buscar_resumen_tesoreria($usuario);
	}
	if ($operacion == "buscar_candidatos_manual") {
		ueno_buscar_candidatos_manual($usuario);
	}
	if ($operacion == "asignar_movimiento_manual") {
		ueno_bloquear_proceso_mesa();
	}
	if ($operacion == "buscar_auditoria") {
		ueno_buscar_auditoria($usuario);
	}

	ueno_json(array("1" => "operacioninvalida"));
}
?>

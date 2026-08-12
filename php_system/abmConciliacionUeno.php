<?php
require_once("conexion.php");
require_once("ueno_saldo_helper.php");
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

function ueno_columna_existe($mysqli, $tabla, $columna)
{
	$tabla = $mysqli->real_escape_string($tabla);
	$columna = $mysqli->real_escape_string($columna);
	$result = $mysqli->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
	return $result && $result->num_rows > 0;
}

function ueno_banco_codigo($valor = "")
{
	$valor = strtoupper(trim(ueno_from_db((string)$valor)));
	return $valor == "FAMILIAR" ? "FAMILIAR" : "UENO";
}

function ueno_banco_nombre($codigo)
{
	return ueno_banco_codigo($codigo) == "FAMILIAR" ? "Banco Familiar" : "Ueno";
}

function ueno_banco_post()
{
	return ueno_banco_codigo(isset($_POST["banco_codigo"]) ? $_POST["banco_codigo"] : "UENO");
}

function ueno_banco_filtro_codigo($valor = "UENO")
{
	$valor = strtoupper(trim(ueno_from_db((string)$valor)));
	if ($valor == "TODOS") {
		return "";
	}
	return ueno_banco_codigo($valor);
}

function ueno_banco_filtro_post()
{
	return ueno_banco_filtro_codigo(isset($_POST["banco_codigo"]) ? $_POST["banco_codigo"] : "UENO");
}

function ueno_banco_badge_html($codigo, $mostrarNombre = true)
{
	$banco = ueno_banco_codigo($codigo);
	$esFamiliar = $banco == "FAMILIAR";
	$clase = $esFamiliar ? "familiar" : "ueno";
	$marca = $esFamiliar ? "Familiar" : "ueno";
	$nombre = $esFamiliar ? "Banco Familiar" : "Ueno";
	$html = "<span class='ueno-bank-badge ueno-bank-badge--" . $clase . "' title='" . ueno_escape_html($nombre) . "'>"
		. "<span class='ueno-bank-badge-mark' aria-hidden='true'>" . ueno_escape_html($marca) . "</span>";
	if ($mostrarNombre) {
		$html .= "<span class='ueno-bank-badge-name'>" . ueno_escape_html($nombre) . "</span>";
	}
	return $html . "</span>";
}

function ueno_tablas_requeridas_ok($mysqli)
{
	ueno_saldo_asegurar_tabla_movimiento_pago($mysqli);
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
	$columnasBancarias = array(
		array("ueno_importacion_extracto", "banco_codigo"),
		array("ueno_importacion_extracto", "moneda_codigo"),
		array("ueno_importacion_extracto", "saldo_anterior"),
		array("ueno_importacion_extracto", "saldo_final"),
		array("ueno_movimiento_bancario", "banco_codigo"),
		array("pago_transferencia_conciliacion", "banco_codigo")
	);
	foreach ($columnasBancarias as $columna) {
		if (!ueno_columna_existe($mysqli, $columna[0], $columna[1])) {
			return false;
		}
	}

	return true;
}

function ueno_tablas_egreso_requeridas_ok($mysqli)
{
	return ueno_tablas_requeridas_ok($mysqli)
		&& ueno_tabla_existe($mysqli, "ueno_movimiento_gasto")
		&& ueno_tabla_existe($mysqli, "ueno_auditoria_conciliacion");
}

function ueno_tablas_migracion_requeridas_ok($mysqli)
{
	return ueno_tablas_requeridas_ok($mysqli)
		&& ueno_tabla_existe($mysqli, "migrar_caja")
		&& ueno_tabla_existe($mysqli, "ueno_movimiento_migracion_caja");
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

function ueno_escape_texto($valor)
{
	return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function ueno_comprobante_normalizado($valor)
{
	return trim(preg_replace('/\s+/', '', (string)$valor));
}

function ueno_mask_comprobante($valor)
{
	$valor = ueno_comprobante_normalizado($valor);
	$largo = strlen($valor);
	if ($largo <= 0) {
		return "";
	}
	if ($largo <= 2) {
		return "**";
	}
	if ($largo < 7) {
		return substr($valor, 0, 1) . "***" . substr($valor, -1);
	}
	return substr($valor, 0, 3) . "******" . substr($valor, -3);
}

function ueno_fecha_visual($valor)
{
	$fecha = ueno_fecha($valor);
	if ($fecha == "") {
		return "";
	}
	$time = strtotime($fecha);
	return $time ? date("d/m/Y", $time) : $fecha;
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

function ueno_estado_movimiento_debito_texto($debito, $aplicado = 0)
{
	$debito = (int)$debito;
	$aplicado = (int)$aplicado;
	if ($debito <= 0) {
		return "Sin monto";
	}
	if ($aplicado <= 0) {
		return "Sin conciliar";
	}
	if ($aplicado >= $debito) {
		return "Conciliado";
	}
	return "Parcial";
}

function ueno_estado_movimiento_texto($estado, $credito = 0, $disponible = 0, $debito = 0, $debito_aplicado = 0)
{
	$estado = (string)$estado;
	$credito = (int)$credito;
	$disponible = (int)$disponible;
	$debito = (int)$debito;
	$debito_aplicado = (int)$debito_aplicado;

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
		return ueno_estado_movimiento_debito_texto($debito, $debito_aplicado);
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
	if ($estado_visual == "parcial" || $estado_visual == "parcialmente conciliado") {
		return "parcial";
	}
	if ($estado_visual == "disponible" || $estado_visual == "sin conciliar") {
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
		return (int)$disponible > 0;
	}
	return true;
}

function ueno_clasificar_origen_movimiento($credito, $disponible, $sugerencias_deposito, $tiene_deposito_conciliado)
{
	$credito = (int)$credito;
	$disponible = (int)$disponible;
	$sugerencias_deposito = (int)$sugerencias_deposito;
	if ($tiene_deposito_conciliado) {
		return "deposito_conciliado";
	}
	if ($credito > 0 && $disponible == $credito && $sugerencias_deposito > 0) {
		return $sugerencias_deposito == 1 ? "deposito" : "revisar";
	}
	if ($credito > 0 && $disponible > 0) {
		return "cuota";
	}
	return "otro";
}

function ueno_origen_cumple_filtro($origen_probable, $clasificacion_origen)
{
	$origen_probable = trim((string)$origen_probable);
	$clasificacion_origen = trim((string)$clasificacion_origen);
	if ($origen_probable == "" || $origen_probable == "todos") {
		return true;
	}
	if ($origen_probable == "deposito") {
		return in_array($clasificacion_origen, array("deposito", "deposito_conciliado"));
	}
	return $clasificacion_origen == $origen_probable;
}

function ueno_sincronizar_accesos_usuario($usuario)
{
	static $usuariosSincronizados = array();
	$usuario = trim((string)$usuario);
	if ($usuario == "" || isset($usuariosSincronizados[$usuario])) {
		return;
	}
	$usuariosSincronizados[$usuario] = true;
	$mysqli = conectar_al_servidor();
	$sql = "INSERT INTO accesosuser (idlistadodeaccesoFK, tipo, usuarios_idusario, accion)
		SELECT lta.idlistadodeacceso, 'Administrativo', us.cod_usuario, IFNULL(dts.accion, 'NO')
		FROM usuario us
		INNER JOIN listadodeacceso lta ON lta.tipo='Administrativo'
		LEFT JOIN detallesniveles dts ON dts.idlistadodeacceso=lta.idlistadodeacceso
			AND dts.cod_nivelesfk=us.Acceso
		LEFT JOIN accesosuser acus ON acus.idlistadodeaccesoFK=lta.idlistadodeacceso
			AND acus.tipo='Administrativo'
			AND acus.usuarios_idusario=us.cod_usuario
		WHERE us.cod_usuario=?
			AND acus.idaccesosUser IS NULL";
	$stmt = $mysqli->prepare($sql);
	if ($stmt) {
		$stmt->bind_param("s", $usuario);
		$stmt->execute();
		$stmt->close();
	}
	mysqli_close($mysqli);
}

function ueno_permisos_equivalentes($codigo)
{
	$codigo = strtoupper(trim((string)$codigo));
	$mapa = array(
		"VERCONCILIACIONEGRESOUENO" => array("VERCONCILIACIONEGRESOUENO", "VERCONCILIACIONUENO"),
		"CONCILIAREGRESOUENO" => array("CONCILIAREGRESOUENO", "ASIGNARMANUALUENO"),
		"REVERTIRCONCILIACIONEGRESOUENO" => array("REVERTIRCONCILIACIONEGRESOUENO", "ASIGNARMANUALUENO"),
		"VERMIGRACIONUENO" => array("VERMIGRACIONUENO", "VERCONCILIACIONUENO", "VEREXTRACTOSUENO"),
		"CONCILIARMIGRACIONUENO" => array("CONCILIARMIGRACIONUENO", "ASIGNARMANUALUENO", "EDITARLISTADOEGRESOINGRESO")
	);
	return isset($mapa[$codigo]) ? $mapa[$codigo] : array($codigo);
}

function ueno_usuario_tiene_permiso($usuario, $codigo)
{
	if ((string)$usuario == "2") {
		return true;
	}
	if (function_exists("controldeaccesoacasas")) {
		ueno_sincronizar_accesos_usuario($usuario);
		$codigos = ueno_permisos_equivalentes($codigo);
		foreach ($codigos as $codigoEvaluar) {
			if (controldeaccesoacasas($usuario, $codigoEvaluar, " u.accion='SI' ") == 1) {
				return true;
			}
		}
		return false;
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

function ueno_estado_debito_disponible_para_egreso($estado)
{
	$estado = strtolower(trim((string)$estado));
	return in_array($estado, array("registrado", "disponible", "asignado_parcial"), true);
}

function ueno_local_usuario($mysqli, $usuario)
{
	$codUsuario = (int)$usuario;
	$stmt = $mysqli->prepare("SELECT cod_localFK FROM usuario WHERE cod_usuario=? LIMIT 1");
	if (!$stmt) {
		throw new Exception("No se pudo validar el local del usuario");
	}
	$stmt->bind_param("i", $codUsuario);
	$stmt->execute();
	$fila = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	$codLocal = $fila ? (int)$fila["cod_localFK"] : 0;
	if ($codLocal <= 0) {
		throw new Exception("El usuario no tiene un local operativo asignado");
	}
	return $codLocal;
}

function ueno_local_activo($mysqli, $codLocal)
{
	$codLocal = (int)$codLocal;
	if ($codLocal <= 0) {
		return false;
	}
	$result = $mysqli->query("SELECT cod_local FROM local WHERE cod_local=$codLocal AND LOWER(TRIM(estado))='activo' LIMIT 1");
	return $result && $result->num_rows > 0;
}

function ueno_usuario_puede_gestionar_local($mysqli, $usuario, $codLocal)
{
	$codLocal = (int)$codLocal;
	if ($codLocal <= 0 || !ueno_local_activo($mysqli, $codLocal)) {
		return false;
	}
	if ((string)$usuario === "2" || ueno_usuario_tiene_permiso($usuario, "CAMBIARLOCAL")) {
		return true;
	}
	return ueno_local_usuario($mysqli, $usuario) === $codLocal;
}

function ueno_requerir_local_gasto($mysqli, $usuario, $codLocal)
{
	if (!ueno_usuario_puede_gestionar_local($mysqli, $usuario, $codLocal)) {
		throw new Exception("No administra el local de origen del gasto seleccionado");
	}
}

function ueno_resolver_filtro_local($mysqli, $usuario, $codLocalSolicitado)
{
	$tieneCambioLocal = ((string)$usuario === "2" || ueno_usuario_tiene_permiso($usuario, "CAMBIARLOCAL"));
	$valor = trim((string)$codLocalSolicitado);
	if (!$tieneCambioLocal) {
		return ueno_local_usuario($mysqli, $usuario);
	}
	if ($valor === "") {
		return 0;
	}
	if (!ctype_digit($valor) || (int)$valor <= 0 || !ueno_local_activo($mysqli, (int)$valor)) {
		throw new Exception("El local solicitado no existe o no esta activo");
	}
	return (int)$valor;
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
	if (!ueno_saldo_asegurar_tabla_movimiento_pago($mysqli)) {
		throw new Exception("No se pudo preparar la tabla de aplicaciones Ueno.");
	}
	$monto_pago_total = (int)$pago["monto_pago"];
	$bancoPago = ueno_banco_codigo(isset($pago["banco_codigo"]) ? $pago["banco_codigo"] : "UENO");
	$bancoMovimiento = ueno_banco_codigo(isset($movimiento["banco_codigo"]) ? $movimiento["banco_codigo"] : "UENO");
	if ($bancoPago != $bancoMovimiento) {
		return false;
	}
	$monto_pago = $monto_aplicar === null ? $monto_pago_total : (int)$monto_aplicar;
	$monto_disponible = ueno_saldo_disponible_movimiento($mysqli, $movimiento, $pago["cod_pagoFK"], $pago["id"]);
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
		throw new Exception("El pago ya tiene un vinculo activo con este movimiento bancario");
	}

	$total_aplicado_nuevo = $total_aplicado_pago + $monto_pago;
	$nuevo_disponible = max(0, $monto_disponible - $monto_pago);
	$estado_movimiento = ueno_saldo_estado_credito($importe_credito, $nuevo_disponible);
	ueno_saldo_sincronizar_movimiento($mysqli, $id_movimiento, $nuevo_disponible, $importe_credito);

	$estado_pago = $total_aplicado_nuevo >= $monto_pago_total ? "conciliado_ueno" : "pendiente_conciliacion";
	if ($observacion_pago == "") {
		$observacion_pago = $estado_pago == "conciliado_ueno"
			? "Conciliado automaticamente con movimiento bancario #" . $movimiento["id_movimiento"]
			: "Aplicacion parcial con movimiento bancario #" . $movimiento["id_movimiento"];
	}
	$consultaPago = "UPDATE pago_transferencia_conciliacion
		SET banco_codigo=?, estado_conciliacion=?, usuario_ultima_revision=?, fecha_hora_revision=NOW(), observacion=?
		WHERE id=? AND activo='SI'";
	$stmtPago = $mysqli->prepare($consultaPago);
	$stmtPago->bind_param("sssss", $bancoMovimiento, $estado_pago, $usuario, $observacion_pago, $id_pago);
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

function ueno_ejecutar_conciliacion($mysqli, $usuario, $id_importacion = "", $banco = "")
{
	$resumen = ueno_resumen_conciliacion_vacio();
	$condicionMov = "";
	$condicionPago = "";
	if ($banco != "") {
		$banco = ueno_banco_codigo($banco);
		$bancoSql = $mysqli->real_escape_string($banco);
		$condicionMov .= " AND banco_codigo='$bancoSql'";
		$condicionPago .= " AND pc.banco_codigo='$bancoSql'";
	}
	if ($id_importacion != "") {
		$condicionMov = " AND id_importacion='" . $mysqli->real_escape_string($id_importacion) . "'";
	}

	$sqlPagos = "SELECT pc.id, pc.cod_pagoFK, pc.banco_codigo, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion, p.Fecha, p.nrofactura
		FROM pago_transferencia_conciliacion pc
		INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
		WHERE pc.activo='SI'
		AND pc.estado_conciliacion='pendiente_conciliacion'
		AND pc.nro_comprobante_informado!=''
		$condicionPago
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

		$sqlMovCreditos = "SELECT id_movimiento, banco_codigo, nro_comprobante, importe_credito, monto_disponible, estado
			FROM ueno_movimiento_bancario
			WHERE nro_comprobante='$comprobanteSql'
			AND tipo_movimiento='credito'
			AND importe_credito>0
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
			$movimiento["monto_disponible"] = ueno_saldo_disponible_movimiento($mysqli, $movimiento, $pago["cod_pagoFK"], $pago["id"]);
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
			$obs = "Observado automaticamente: comprobante encontrado en el banco, pero no habilita saldo de credito disponible.";
			if ($movCualquiera["tipo_movimiento"] != "credito") {
				$obs = "Observado automaticamente: comprobante encontrado en el banco, pero corresponde a un movimiento no credito.";
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
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$id_importacion = ueno_post("id_importacion");
	$mysqli->begin_transaction();
	try {
		$resumen = ueno_ejecutar_conciliacion($mysqli, $usuario, $id_importacion, ueno_banco_post());
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
	$sql = "SELECT pc.id, pc.cod_pagoFK, pc.banco_codigo, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion, pc.observacion,
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
	$bancoPago = ueno_banco_codigo(isset($pago["banco_codigo"]) ? $pago["banco_codigo"] : "UENO");
	$bancoPagoSql = $mysqli->real_escape_string($bancoPago);
	$condiciones = array("mv.tipo_movimiento='credito'", "mv.importe_credito>0", "mv.banco_codigo='$bancoPagoSql'");
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

	$sql = "SELECT mv.id_movimiento, mv.banco_codigo, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante,
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
		$row["monto_disponible"] = ueno_saldo_disponible_movimiento($mysqli, $row);
		if ((int)$row["monto_disponible"] <= 0) {
			continue;
		}
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
			. "<td style='width:100%;text-align:center'>Sin candidatos bancarios para este pago</td>"
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
		"banco_codigo" => ueno_banco_codigo(isset($pago["banco_codigo"]) ? $pago["banco_codigo"] : "UENO"),
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
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
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
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
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
		$resultMov = $mysqli->query("SELECT id_movimiento, banco_codigo, nro_comprobante, tipo_movimiento, importe_credito, importe_debito, monto_disponible, estado
			FROM ueno_movimiento_bancario
			WHERE id_movimiento=$id_movimiento
			LIMIT 1 FOR UPDATE");
		if (!$resultMov) {
			throw new Exception($mysqli->error);
		}
		if ($resultMov->num_rows == 0) {
			throw new Exception("No se encontro el movimiento bancario seleccionado");
		}
		$movimiento = $resultMov->fetch_assoc();
		if ($movimiento["tipo_movimiento"] != "credito" || (int)$movimiento["importe_credito"] <= 0) {
			throw new Exception("El movimiento seleccionado no es un credito bancario");
		}
		$movimiento["monto_disponible"] = ueno_saldo_disponible_movimiento($mysqli, $movimiento, $pago["cod_pagoFK"], $pago["id"]);
		if ((int)$movimiento["monto_disponible"] < (int)$pago["monto_pago"]) {
			if ((int)$movimiento["monto_disponible"] < $monto_aplicar) {
				throw new Exception("El movimiento bancario no tiene saldo disponible suficiente");
			}
		}
		$monto_aplicado_pago = isset($pago["monto_aplicado_ueno"]) ? (int)$pago["monto_aplicado_ueno"] : 0;
		$saldo_pago = max(0, (int)$pago["monto_pago"] - $monto_aplicado_pago);
		if ($monto_aplicar <= 0) {
			throw new Exception("El monto a aplicar debe ser mayor a cero");
		}
		if ($monto_aplicar > $saldo_pago) {
			throw new Exception("El monto supera el saldo pendiente de la cuota Telar");
		}
		$comprobanteDiferente = (string)$pago["nro_comprobante_informado"] != (string)$movimiento["nro_comprobante"];
		$montoDiferente = (int)$monto_aplicar != (int)$pago["monto_pago"] || (int)$movimiento["importe_credito"] != (int)$pago["monto_pago"];
		if (($comprobanteDiferente || $montoDiferente) && $observacion_usuario == "") {
			throw new Exception("La asignacion tiene diferencia de comprobante, monto o es parcial. Agrega una observacion de revision.");
		}

		$obsExtra = $observacion_usuario != "" ? " - " . $observacion_usuario : "";
		$observacion_link = "Asignacion manual asistida por cuota Telar" . $obsExtra;
		$observacion_pago = ($monto_aplicar >= $saldo_pago ? "Conciliado" : "Aplicado parcialmente") . " manualmente con movimiento bancario #" . $movimiento["id_movimiento"] . $obsExtra;
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

function ueno_normalizar_movimientos_importacion($movimientos, $cuenta, $banco = "UENO")
{
	$banco = ueno_banco_codigo($banco);
	$cantidad_movimientos = 0;
	$cantidad_creditos = 0;
	$cantidad_debitos = 0;
	$total_creditos = 0;
	$total_debitos = 0;
	$normalizados = array();

	foreach ($movimientos as $indice => $mov) {
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
		$baseHash = $cuenta . "|" . $nro . "|" . $fecha_confirmacion . "|" . $fecha_transaccion . "|" . $credito . "|" . $debito . "|" . $descripcion . "|" . $concepto;
		$hash_movimiento = sha1($banco == "UENO" ? $baseHash : $banco . "|" . $baseHash);

		$normalizados[] = array(
			"indice" => is_numeric($indice) ? (int)$indice : count($normalizados),
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

	return array(
		"normalizados" => $normalizados,
		"cantidad_movimientos" => $cantidad_movimientos,
		"cantidad_creditos" => $cantidad_creditos,
		"cantidad_debitos" => $cantidad_debitos,
		"total_creditos" => $total_creditos,
		"total_debitos" => $total_debitos
	);
}

function ueno_buscar_movimientos_existentes_por_hash($mysqli, $hashes)
{
	$existentes = array();
	if (!is_array($hashes) || count($hashes) == 0) {
		return $existentes;
	}

	$stmt = $mysqli->prepare("SELECT id_movimiento, id_importacion, hash_movimiento, estado, fecha_hora_registro FROM ueno_movimiento_bancario WHERE hash_movimiento=? LIMIT 1");
	if (!$stmt) {
		throw new Exception($mysqli->error);
	}
	$hashParametro = "";
	$stmt->bind_param("s", $hashParametro);
	$consultados = array();

	foreach ($hashes as $hash) {
		$hash = (string)$hash;
		if ($hash == "" || isset($consultados[$hash])) {
			continue;
		}
		$consultados[$hash] = true;
		$hashParametro = $hash;
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$result = $stmt->get_result();
		if ($result && ($row = mysqli_fetch_assoc($result))) {
			$existentes[$hash] = $row;
		}
	}

	return $existentes;
}

function ueno_clave_comprobante_monto_movimiento($mov)
{
	if (!is_array($mov)) {
		return "";
	}
	$nro = isset($mov["nro_comprobante"]) ? trim((string)$mov["nro_comprobante"]) : "";
	$credito = isset($mov["importe_credito"]) ? (int)$mov["importe_credito"] : 0;
	$debito = isset($mov["importe_debito"]) ? (int)$mov["importe_debito"] : 0;
	$monto = $credito > 0 ? $credito : $debito;
	if ($nro == "" || $monto <= 0) {
		return "";
	}
	return $nro . "|" . $monto;
}

function ueno_buscar_movimientos_existentes_por_comprobante_monto($mysqli, $movimientos, $banco = "UENO", $cuenta = "")
{
	$existentes = array();
	if (!is_array($movimientos) || count($movimientos) == 0) {
		return $existentes;
	}

	$stmt = $mysqli->prepare("SELECT mv.id_movimiento, mv.id_importacion, mv.nro_comprobante, mv.importe_credito, mv.importe_debito,
		imp.nombre_archivo_original, imp.fecha_hora_importacion
		FROM ueno_movimiento_bancario mv
		LEFT JOIN ueno_importacion_extracto imp ON imp.id_importacion=mv.id_importacion
		WHERE mv.banco_codigo=?
		AND mv.cuenta=?
		AND mv.nro_comprobante=?
		AND (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)=?
		AND (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)>0
		ORDER BY mv.id_movimiento ASC
		LIMIT 1");
	if (!$stmt) {
		throw new Exception($mysqli->error);
	}
	$bancoParametro = ueno_banco_codigo($banco);
	$cuentaParametro = (string)$cuenta;
	$nroParametro = "";
	$montoParametro = 0;
	$stmt->bind_param("sssi", $bancoParametro, $cuentaParametro, $nroParametro, $montoParametro);
	$consultados = array();

	foreach ($movimientos as $mov) {
		$clave = ueno_clave_comprobante_monto_movimiento($mov);
		if ($clave == "" || isset($consultados[$clave])) {
			continue;
		}
		$partes = explode("|", $clave);
		$nroParametro = $partes[0];
		$montoParametro = (int)$partes[1];
		$consultados[$clave] = true;
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$result = $stmt->get_result();
		if ($result && ($row = mysqli_fetch_assoc($result))) {
			$existentes[$clave] = $row;
		}
	}

	return $existentes;
}

function ueno_detalle_movimiento_duplicado_comprobante_monto($row)
{
	$detalle = "Ya importado por comprobante y monto";
	if (!is_array($row)) {
		return $detalle;
	}
	$detalle .= " en movimiento #" . $row["id_movimiento"] . " / importacion #" . $row["id_importacion"];
	if (isset($row["nombre_archivo_original"]) && trim((string)$row["nombre_archivo_original"]) != "") {
		$detalle .= " / archivo " . ueno_from_db($row["nombre_archivo_original"]);
	}
	return $detalle;
}

function ueno_validar_cuenta_familiar($mysqli, $cuenta)
{
	$cuentaNormalizada = preg_replace('/[^0-9]/', '', (string)$cuenta);
	if ($cuentaNormalizada == "") {
		throw new Exception("No se pudo identificar la cuenta corriente de Banco Familiar");
	}
	$result = $mysqli->query("SELECT cuenta FROM ueno_importacion_extracto WHERE banco_codigo='FAMILIAR' ORDER BY id_importacion ASC LIMIT 1");
	if ($result && ($row = $result->fetch_assoc())) {
		$cuentaGuardada = preg_replace('/[^0-9]/', '', (string)$row["cuenta"]);
		if ($cuentaGuardada != "" && $cuentaGuardada != $cuentaNormalizada) {
			throw new Exception("La cuenta del PDF no coincide con la cuenta corriente de Banco Familiar ya registrada en Telar");
		}
	}
}

function ueno_validar_totales_familiar($mysqli, $cuenta, $normalizacion)
{
	ueno_validar_cuenta_familiar($mysqli, $cuenta);
	$moneda = strtoupper(trim(ueno_from_db(ueno_post("moneda_codigo"))));
	$tipoCuenta = strtoupper(trim(ueno_from_db(ueno_post("tipo_cuenta"))));
	if ($moneda != "PYG" || $tipoCuenta != "CUENTA_CORRIENTE") {
		throw new Exception("El extracto de Banco Familiar debe ser de cuenta corriente en guaranies");
	}
	$requeridos = array("saldo_anterior", "saldo_final", "total_creditos_declarado", "total_debitos_declarado");
	foreach ($requeridos as $campo) {
		if (!isset($_POST[$campo]) || trim((string)$_POST[$campo]) === "") {
			throw new Exception("Faltan los totales de control declarados en el PDF de Banco Familiar");
		}
	}
	$saldoAnterior = ueno_monto($_POST["saldo_anterior"]);
	$saldoFinal = ueno_monto($_POST["saldo_final"]);
	$totalCreditosDeclarado = ueno_monto($_POST["total_creditos_declarado"]);
	$totalDebitosDeclarado = ueno_monto($_POST["total_debitos_declarado"]);
	if ($totalCreditosDeclarado != (int)$normalizacion["total_creditos"] || $totalDebitosDeclarado != (int)$normalizacion["total_debitos"]) {
		throw new Exception("Los totales extraidos no coinciden con los movimientos del PDF de Banco Familiar");
	}
	if ($saldoAnterior + $totalCreditosDeclarado - $totalDebitosDeclarado != $saldoFinal) {
		throw new Exception("Los movimientos no reconstruyen el saldo final del PDF de Banco Familiar");
	}
	$normalizados = $normalizacion["normalizados"];
	$ultimo = count($normalizados) > 0 ? $normalizados[count($normalizados) - 1] : null;
	if (!$ultimo || $ultimo["saldo_banco"] === null || (int)$ultimo["saldo_banco"] != $saldoFinal) {
		throw new Exception("El saldo del ultimo movimiento no coincide con el saldo final de Banco Familiar");
	}
	return array(
		"moneda_codigo" => "PYG",
		"saldo_anterior" => $saldoAnterior,
		"saldo_final" => $saldoFinal
	);
}

function ueno_prevalidar_importacion($usuario)
{
	ueno_requerir_permiso($usuario, "IMPORTAREXTRACTOUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$banco = ueno_banco_post();
	$cuenta = ueno_post("cuenta");
	$json = isset($_POST["movimientos_json"]) ? $_POST["movimientos_json"] : "";
	if ($cuenta == "" || $json == "") {
		mysqli_close($mysqli);
		ueno_json(array("1" => "camposvacio", "2" => "Faltan datos para verificar el extracto"));
	}

	$movimientos = json_decode($json, true);
	if (!is_array($movimientos)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "jsoninvalido", "2" => "No se pudo interpretar el listado de movimientos"));
	}

	try {
		$normalizacion = ueno_normalizar_movimientos_importacion($movimientos, $cuenta, $banco);
		if ($banco == "FAMILIAR") {
			ueno_validar_cuenta_familiar($mysqli, $cuenta);
		}
		$normalizados = $normalizacion["normalizados"];
		$hashes = array();
		foreach ($normalizados as $mov) {
			$hashes[] = $mov["hash_movimiento"];
		}
		$existentes = ueno_buscar_movimientos_existentes_por_hash($mysqli, $hashes);
		$existentesComprobanteMonto = ueno_buscar_movimientos_existentes_por_comprobante_monto($mysqli, $normalizados, $banco, $cuenta);
		$vistosArchivoHash = array();
		$vistosArchivoComprobanteMonto = array();
		$respuestaMovimientos = array();
		$nuevos = 0;
		$duplicados = 0;

		foreach ($normalizados as $mov) {
			$hash = $mov["hash_movimiento"];
			$claveComprobanteMonto = ueno_clave_comprobante_monto_movimiento($mov);
			$estado = "nuevo";
			$detalle = "Movimiento nuevo, listo para importar";
			$idMovimiento = "";
			$idImportacion = "";

			if (isset($existentes[$hash])) {
				$estado = "ya_importado";
				$idMovimiento = $existentes[$hash]["id_movimiento"];
				$idImportacion = $existentes[$hash]["id_importacion"];
				$detalle = "Ya importado en movimiento #" . $idMovimiento . " / importacion #" . $idImportacion;
				$duplicados++;
			} else if ($claveComprobanteMonto != "" && isset($existentesComprobanteMonto[$claveComprobanteMonto])) {
				$estado = "ya_importado";
				$idMovimiento = $existentesComprobanteMonto[$claveComprobanteMonto]["id_movimiento"];
				$idImportacion = $existentesComprobanteMonto[$claveComprobanteMonto]["id_importacion"];
				$detalle = ueno_detalle_movimiento_duplicado_comprobante_monto($existentesComprobanteMonto[$claveComprobanteMonto]);
				$duplicados++;
			} else if (isset($vistosArchivoHash[$hash])) {
				$estado = "repetido_archivo";
				$detalle = "Repetido dentro del mismo archivo";
				$duplicados++;
			} else if ($claveComprobanteMonto != "" && isset($vistosArchivoComprobanteMonto[$claveComprobanteMonto])) {
				$estado = "repetido_archivo";
				$detalle = "Repetido dentro del mismo archivo por comprobante y monto";
				$duplicados++;
			} else {
				$nuevos++;
			}

			$vistosArchivoHash[$hash] = true;
			if ($claveComprobanteMonto != "") {
				$vistosArchivoComprobanteMonto[$claveComprobanteMonto] = true;
			}
			$respuestaMovimientos[] = array(
				"indice" => $mov["indice"],
				"estado" => $estado,
				"detalle" => $detalle,
				"id_movimiento" => $idMovimiento,
				"id_importacion" => $idImportacion
			);
		}

		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"movimientos_leidos" => $normalizacion["cantidad_movimientos"],
			"movimientos_nuevos" => $nuevos,
			"movimientos_duplicados" => $duplicados,
			"cantidad_creditos" => $normalizacion["cantidad_creditos"],
			"cantidad_debitos" => $normalizacion["cantidad_debitos"],
			"total_creditos" => number_format($normalizacion["total_creditos"], 0, ",", "."),
			"total_debitos" => number_format($normalizacion["total_debitos"], 0, ",", "."),
			"movimientos" => $respuestaMovimientos
		));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_insertar_importacion($usuario)
{
	ueno_requerir_permiso($usuario, "IMPORTAREXTRACTOUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$banco = ueno_banco_post();
	$cuenta = ueno_post("cuenta");
	$denominacion = ueno_post("denominacion");
	$fecha_extracto = ueno_fecha(isset($_POST["fecha_extracto"]) ? $_POST["fecha_extracto"] : "");
	$periodo_desde = ueno_fecha(isset($_POST["periodo_desde"]) ? $_POST["periodo_desde"] : "");
	$periodo_hasta = ueno_fecha(isset($_POST["periodo_hasta"]) ? $_POST["periodo_hasta"] : "");
	$nombre_archivo = ueno_post("nombre_archivo_original");
	$hash_archivo = ueno_post("hash_archivo");
	$observacion = ueno_post("observacion");
	// En Banco Familiar se conserva una etiqueta neutra, no el nombre local del PDF.
	if ($banco == "FAMILIAR") {
		$nombre_archivo = "Extracto Banco Familiar";
	}
	$moneda_codigo = $banco == "FAMILIAR" ? "PYG" : strtoupper(trim(ueno_from_db(ueno_post("moneda_codigo", "PYG"))));
	if ($moneda_codigo == "") {
		$moneda_codigo = "PYG";
	}
	$saldo_anterior = null;
	$saldo_final = null;
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
		$tabla = ueno_tabla_movimientos($mysqli, $id_importacion_existente, "", "", "", "", "todos", 0, "todos", $usuario, $banco);
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

	$normalizacion = ueno_normalizar_movimientos_importacion($movimientos, $cuenta, $banco);
	if ($banco == "FAMILIAR") {
		try {
			$controlFamiliar = ueno_validar_totales_familiar($mysqli, $cuenta, $normalizacion);
			$moneda_codigo = $controlFamiliar["moneda_codigo"];
			$saldo_anterior = $controlFamiliar["saldo_anterior"];
			$saldo_final = $controlFamiliar["saldo_final"];
		} catch (Exception $e) {
			mysqli_close($mysqli);
			ueno_json(array("1" => "extractoinvalido", "2" => $e->getMessage()));
		}
	}
	$normalizados = $normalizacion["normalizados"];
	$cantidad_movimientos = $normalizacion["cantidad_movimientos"];
	$cantidad_creditos = $normalizacion["cantidad_creditos"];
	$cantidad_debitos = $normalizacion["cantidad_debitos"];
	$total_creditos = $normalizacion["total_creditos"];
	$total_debitos = $normalizacion["total_debitos"];

	if ($cantidad_movimientos == 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "sinmovimientos", "2" => "No se encontraron movimientos validos"));
	}

	$hashes = array();
	foreach ($normalizados as $mov) {
		$hashes[] = $mov["hash_movimiento"];
	}
	try {
		$existentesAntes = ueno_buscar_movimientos_existentes_por_hash($mysqli, $hashes);
		$existentesComprobanteMontoAntes = ueno_buscar_movimientos_existentes_por_comprobante_monto($mysqli, $normalizados, $banco, $cuenta);
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
	$vistosArchivoHash = array();
	$vistosArchivoComprobanteMonto = array();
	$nuevosPrevios = 0;
	foreach ($normalizados as $mov) {
		$hash = $mov["hash_movimiento"];
		$claveComprobanteMonto = ueno_clave_comprobante_monto_movimiento($mov);
		if (
			isset($existentesAntes[$hash])
			|| isset($vistosArchivoHash[$hash])
			|| ($claveComprobanteMonto != "" && isset($existentesComprobanteMontoAntes[$claveComprobanteMonto]))
			|| ($claveComprobanteMonto != "" && isset($vistosArchivoComprobanteMonto[$claveComprobanteMonto]))
		) {
			$vistosArchivoHash[$hash] = true;
			if ($claveComprobanteMonto != "") {
				$vistosArchivoComprobanteMonto[$claveComprobanteMonto] = true;
			}
			continue;
		}
		$vistosArchivoHash[$hash] = true;
		if ($claveComprobanteMonto != "") {
			$vistosArchivoComprobanteMonto[$claveComprobanteMonto] = true;
		}
		$nuevosPrevios++;
	}
	if ($nuevosPrevios == 0) {
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"estado_importacion" => "sin_movimientos_nuevos",
			"2" => "Todos los movimientos del extracto ya fueron importados",
			"id_importacion" => "",
			"movimientos_leidos" => $cantidad_movimientos,
			"movimientos_nuevos" => 0,
			"movimientos_duplicados" => $cantidad_movimientos,
			"cantidad_creditos" => $cantidad_creditos,
			"cantidad_debitos" => $cantidad_debitos,
			"total_creditos" => number_format($total_creditos, 0, ",", "."),
			"total_debitos" => number_format($total_debitos, 0, ",", "."),
			"conciliacion" => ueno_resumen_conciliacion_vacio(),
			"tabla" => ""
		));
	}

	$mysqli->begin_transaction();
	$bloqueoCuentaFamiliar = false;
	if ($banco == "FAMILIAR") {
		$bloqueoCuentaFamiliar = (int)ueno_scalar($mysqli, "SELECT GET_LOCK('telar_conciliacion_familiar_cuenta',10)") === 1;
		if (!$bloqueoCuentaFamiliar) {
			$mysqli->rollback();
			mysqli_close($mysqli);
			ueno_json(array("1" => "error", "2" => "No se pudo bloquear la validacion de la cuenta Banco Familiar. Intenta nuevamente."));
		}
		try {
			ueno_validar_cuenta_familiar($mysqli, $cuenta);
		} catch (Exception $e) {
			$mysqli->rollback();
			$mysqli->query("SELECT RELEASE_LOCK('telar_conciliacion_familiar_cuenta')");
			mysqli_close($mysqli);
			ueno_json(array("1" => "extractoinvalido", "2" => $e->getMessage()));
		}
	}

	$sqlImportacion = "INSERT INTO ueno_importacion_extracto
		(banco_codigo, cuenta, denominacion, moneda_codigo, fecha_extracto, periodo_desde, periodo_hasta, nombre_archivo_original, hash_archivo, usuario_importo,
		cantidad_movimientos, cantidad_creditos, cantidad_debitos, total_creditos, total_debitos, saldo_anterior, saldo_final, estado, observacion)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmtImp = $mysqli->prepare($sqlImportacion);
	$estado_importacion = "importado";
	$stmtImp->bind_param("sssssssssssssssssss", $banco, $cuenta, $denominacion, $moneda_codigo, $fecha_extracto, $periodo_desde, $periodo_hasta, $nombre_archivo, $hash_archivo, $usuario, $cantidad_movimientos, $cantidad_creditos, $cantidad_debitos, $total_creditos, $total_debitos, $saldo_anterior, $saldo_final, $estado_importacion, $observacion);
	if (!$stmtImp->execute()) {
		$mysqli->rollback();
		ueno_json(array("1" => "error", "2" => $stmtImp->error));
	}
	$id_importacion = $mysqli->insert_id;

	$nuevos = 0;
	$duplicados = 0;
	$vistosInsertadosHash = array();
	$vistosInsertadosComprobanteMonto = array();
	$sqlMovimiento = "INSERT INTO ueno_movimiento_bancario
		(id_importacion, banco_codigo, cuenta, fecha_confirmacion, fecha_transaccion, nro_comprobante, descripcion, concepto, importe_debito,
		importe_credito, tipo_movimiento, saldo_banco, monto_disponible, estado, hash_movimiento)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmtMov = $mysqli->prepare($sqlMovimiento);
	$sqlExisteComprobanteMonto = "SELECT id_movimiento FROM ueno_movimiento_bancario
		WHERE banco_codigo=? AND cuenta=? AND nro_comprobante=?
		AND (CASE WHEN importe_credito>0 THEN importe_credito ELSE importe_debito END)=?
		AND (CASE WHEN importe_credito>0 THEN importe_credito ELSE importe_debito END)>0
		LIMIT 1";
	$stmtExisteComprobanteMonto = $mysqli->prepare($sqlExisteComprobanteMonto);

	foreach ($normalizados as $mov) {
		$mov_hash_movimiento = $mov["hash_movimiento"];
		$mov_clave_comprobante_monto = ueno_clave_comprobante_monto_movimiento($mov);
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
			$vistosInsertadosHash[$mov_hash_movimiento] = true;
			if ($mov_clave_comprobante_monto != "") {
				$vistosInsertadosComprobanteMonto[$mov_clave_comprobante_monto] = true;
			}
			continue;
		}

		if (isset($vistosInsertadosHash[$mov_hash_movimiento])) {
			$duplicados++;
			continue;
		}

		if ($mov_clave_comprobante_monto != "") {
			if (isset($vistosInsertadosComprobanteMonto[$mov_clave_comprobante_monto])) {
				$duplicados++;
				continue;
			}
			$partesComprobanteMonto = explode("|", $mov_clave_comprobante_monto);
			$existeNroComprobante = $partesComprobanteMonto[0];
			$existeMonto = (int)$partesComprobanteMonto[1];
			$stmtExisteComprobanteMonto->bind_param("sssi", $banco, $cuenta, $existeNroComprobante, $existeMonto);
			if (!$stmtExisteComprobanteMonto->execute()) {
				$mysqli->rollback();
				ueno_json(array("1" => "error", "2" => $stmtExisteComprobanteMonto->error));
			}
			$resExisteComprobanteMonto = $stmtExisteComprobanteMonto->get_result();
			if ($resExisteComprobanteMonto && $resExisteComprobanteMonto->num_rows > 0) {
				$duplicados++;
				$vistosInsertadosHash[$mov_hash_movimiento] = true;
				$vistosInsertadosComprobanteMonto[$mov_clave_comprobante_monto] = true;
				continue;
			}
		}

		$stmtMov->bind_param(
			"sssssssssssssss",
			$id_importacion,
			$banco,
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
		$vistosInsertadosHash[$mov_hash_movimiento] = true;
		if ($mov_clave_comprobante_monto != "") {
			$vistosInsertadosComprobanteMonto[$mov_clave_comprobante_monto] = true;
		}
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
		"Importacion " . ueno_banco_nombre($banco) . ": " . $nombre_archivo,
		array(
			"banco_codigo" => $banco,
			"cuenta" => $cuenta,
			"movimientos_leidos" => $cantidad_movimientos,
			"movimientos_nuevos" => $nuevos,
			"movimientos_duplicados" => $duplicados,
			"total_debitos" => $total_debitos
		)
	);

	$mysqli->commit();
	if ($bloqueoCuentaFamiliar) {
		$mysqli->query("SELECT RELEASE_LOCK('telar_conciliacion_familiar_cuenta')");
	}

	$resumen_conciliacion = ueno_resumen_conciliacion_vacio();
	if (ueno_usuario_tiene_permiso($usuario, "CONCILIARPAGOSUENO")) {
		$mysqli->begin_transaction();
		try {
			$resumen_conciliacion = ueno_ejecutar_conciliacion($mysqli, $usuario, $id_importacion, $banco);
			$mysqli->commit();
		} catch (Exception $e) {
			$mysqli->rollback();
			$resumen_conciliacion["error"] = $e->getMessage();
		}
	}

	$tabla = ueno_tabla_movimientos($mysqli, $id_importacion, "", "", "", "", "todos", 0, "todos", $usuario, $banco);
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

function ueno_tabla_movimientos($mysqli, $id_importacion, $fecha_desde, $fecha_hasta, $comprobante, $estado, $filtro_rapido = "todos", $monto = 0, $origen_probable = "todos", $usuario = 0, $banco = "")
{
	$tieneConciliacionEgresos = ueno_tabla_existe($mysqli, "ueno_movimiento_gasto");
	$tieneConciliacionMigracion = ueno_tabla_existe($mysqli, "ueno_movimiento_migracion_caja") && ueno_tabla_existe($mysqli, "migrar_caja");
	$tieneVinculosDepositoFaraone = ueno_tabla_existe($mysqli, "ueno_movimiento_deposito");
	$tieneDepositosFaraone = $tieneVinculosDepositoFaraone && ueno_tabla_existe($mysqli, "migrar_caja");
	$condicion = "";
	$monto = (int)$monto;
	if ($banco != "") {
		$banco = ueno_banco_codigo($banco);
		$condicion .= " AND mv.banco_codigo='" . $mysqli->real_escape_string($banco) . "'";
	}
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
	if ($monto > 0) {
		$condicion .= " AND (mv.importe_debito=" . $monto
			. " OR mv.importe_credito=" . $monto
			. " OR mv.monto_disponible=" . $monto
			. " OR (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)=" . $monto . ")";
	}

	$subqueryDebitoAplicado = $tieneConciliacionEgresos
		? ", IFNULL((SELECT SUM(umg.monto_aplicado) FROM ueno_movimiento_gasto umg WHERE umg.id_movimiento=mv.id_movimiento AND umg.estado='activo'),0) AS monto_aplicado_gasto"
		: ", 0 AS monto_aplicado_gasto";
	$subqueryMigracion = $tieneConciliacionMigracion
		? ", IFNULL((SELECT SUM(umm.monto_aplicado) FROM ueno_movimiento_migracion_caja umm WHERE umm.id_movimiento=mv.id_movimiento AND umm.estado='activo'),0) AS monto_aplicado_migracion,
		IFNULL((SELECT GROUP_CONCAT(DISTINCT CONCAT('Caja / Monto migrado #', mc.idmigrar_caja, IF(IFNULL(l.Nombre,'')!='', CONCAT(' - ', l.Nombre), '')) ORDER BY umm.fecha_hora_asociacion ASC SEPARATOR ', ')
			FROM ueno_movimiento_migracion_caja umm
			INNER JOIN migrar_caja mc ON mc.idmigrar_caja=umm.idmigrar_caja
			LEFT JOIN arqueocaja ar ON ar.idarqueocaja=mc.cod_caja_desdeFK
			LEFT JOIN local l ON l.cod_local=ar.cod_local
			WHERE umm.id_movimiento=mv.id_movimiento AND umm.estado='activo'), '') AS migraciones_conciliacion,
		IFNULL((SELECT GROUP_CONCAT(DISTINCT IFNULL(per.nombre_persona, CONCAT('Usuario ', umm.usuario_asocio)) ORDER BY umm.fecha_hora_asociacion ASC SEPARATOR ', ')
			FROM ueno_movimiento_migracion_caja umm
			LEFT JOIN persona per ON per.cod_persona=umm.usuario_asocio
			WHERE umm.id_movimiento=mv.id_movimiento AND umm.estado='activo'), '') AS usuarios_migracion,
		IFNULL((SELECT COUNT(*)
			FROM migrar_caja mc_sug
			WHERE mc_sug.monto=mv.monto_disponible
			AND mc_sug.monto>0
			AND IFNULL(mc_sug.estado,'')!='Inactivo'
			AND mc_sug.fecha IS NOT NULL
			AND mv.fecha_confirmacion>='" . ueno_migracion_fecha_inicio_sistema() . "'
			AND mc_sug.fecha BETWEEN GREATEST(DATE_SUB(mv.fecha_confirmacion, INTERVAL 4 DAY), '" . ueno_migracion_fecha_inicio_sistema() . "') AND DATE_ADD(mv.fecha_confirmacion, INTERVAL 1 DAY)
			AND mv.tipo_movimiento='credito'
			AND mv.monto_disponible=mv.importe_credito
			AND mv.monto_disponible>0
			AND NOT EXISTS (
				SELECT 1
				FROM ueno_movimiento_migracion_caja umm_sug
				WHERE umm_sug.idmigrar_caja=mc_sug.idmigrar_caja
				AND umm_sug.estado='activo'
			)),0) AS sugerencias_migracion"
		: ", 0 AS monto_aplicado_migracion, '' AS migraciones_conciliacion, '' AS usuarios_migracion, 0 AS sugerencias_migracion";
	$subqueryVinculosDeposito = $tieneVinculosDepositoFaraone
		? ", IFNULL((SELECT GROUP_CONCAT(DISTINCT CONCAT(
			CASE WHEN udc.origen_tipo='migrar_caja' THEN 'Monto migrado' ELSE 'Deposito registrado' END,
			' #', udc.origen_id
		) ORDER BY udc.fecha_hora_asociacion ASC SEPARATOR ', ')
			FROM ueno_movimiento_deposito udc
			WHERE udc.id_movimiento=mv.id_movimiento AND udc.estado='activo'), '') AS depositos_conciliacion,
		IFNULL((SELECT GROUP_CONCAT(DISTINCT IFNULL(per_dep.nombre_persona, CONCAT('Usuario ', ud_usuario.usuario_asocio)) ORDER BY ud_usuario.fecha_hora_asociacion ASC SEPARATOR ', ')
			FROM ueno_movimiento_deposito ud_usuario
			LEFT JOIN persona per_dep ON per_dep.cod_persona=ud_usuario.usuario_asocio
			WHERE ud_usuario.id_movimiento=mv.id_movimiento AND ud_usuario.estado='activo'), '') AS usuarios_deposito"
		: ", '' AS depositos_conciliacion, '' AS usuarios_deposito";
	$subqueryDepositos = $tieneDepositosFaraone
		? ", ((SELECT COUNT(*) FROM gastos gd
			INNER JOIN motivos_ingreso_egreso md ON md.cod_motivo_ingreso_egreso=gd.cod_motivoIngresoEgresoFK
			WHERE UPPER(TRIM(md.descripcion))='DEPOSITO BANCARIO - FARAONE CAPITAL S.A.'
			AND LOWER(TRIM(gd.tipo)) IN ('egreso','deposito') AND LOWER(TRIM(gd.estado)) NOT IN ('inactivo','anulado','baja')
			AND gd.monto=mv.monto_disponible AND DATE(gd.fecha) BETWEEN DATE_SUB(mv.fecha_confirmacion, INTERVAL 2 DAY) AND mv.fecha_confirmacion
			AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_deposito udd WHERE udd.origen_tipo='gasto' AND udd.origen_id=gd.idgastos AND udd.estado='activo'))
		+ (SELECT COUNT(*) FROM migrar_caja mcd
			INNER JOIN persona pd ON pd.cod_persona=mcd.cod_usuRecibeFK
			WHERE mcd.fecha>='2026-06-19 00:00:00' AND UPPER(TRIM(pd.nombre_persona)) LIKE '%CARLOS%FARAONE%'
			AND IFNULL(mcd.estado,'')!='Inactivo' AND mcd.monto=mv.monto_disponible
			AND DATE(mcd.fecha) BETWEEN DATE_SUB(mv.fecha_confirmacion, INTERVAL 2 DAY) AND mv.fecha_confirmacion
			AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_deposito udm WHERE udm.origen_tipo='migrar_caja' AND udm.origen_id=mcd.idmigrar_caja AND udm.estado='activo'))
		) AS sugerencias_deposito"
		: ", 0 AS sugerencias_deposito";

	$sql = "SELECT mv.id_movimiento, mv.id_importacion, mv.banco_codigo, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante, mv.descripcion, mv.cuenta,
		mv.concepto, mv.importe_debito, mv.importe_credito, mv.monto_disponible, mv.estado, imp.nombre_archivo_original,
		IFNULL((SELECT GROUP_CONCAT(DISTINCT IFNULL(per.nombre_persona, CONCAT('Usuario ', ump.usuario_asocio)) ORDER BY ump.fecha_hora_asociacion ASC SEPARATOR ', ')
			FROM ueno_movimiento_pago ump
			LEFT JOIN persona per ON per.cod_persona=ump.usuario_asocio
			WHERE ump.id_movimiento=mv.id_movimiento AND ump.estado='activo'), '') AS usuarios_conciliacion,
		IFNULL((SELECT GROUP_CONCAT(DISTINCT CONCAT(
				IFNULL(NULLIF(cli.nombre_persona, ''), 'Cliente sin nombre'),
				' - Venta ',
				IFNULL(NULLIF(NULLIF(pag.nroventa, ''), '0'), IFNULL(NULLIF(pag.cod_venta_fk, ''), 'S/N'))
			) ORDER BY ump_cliente.fecha_hora_asociacion ASC SEPARATOR ', ')
			FROM ueno_movimiento_pago ump_cliente
			INNER JOIN pago pag ON pag.idPago=ump_cliente.cod_pagoFK
			LEFT JOIN venta vt ON vt.cod_venta=pag.cod_venta_fk
			LEFT JOIN persona cli ON cli.cod_persona=vt.cod_clienteFK
			WHERE ump_cliente.id_movimiento=mv.id_movimiento AND ump_cliente.estado='activo'), '') AS clientes_conciliacion
		$subqueryDebitoAplicado
		$subqueryMigracion
		$subqueryVinculosDeposito
		$subqueryDepositos
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
	$puedeGestionarEgresos = ueno_usuario_tiene_permiso($usuario, "CONCILIAREGRESOUENO");

	while ($row = mysqli_fetch_assoc($result)) {
		$credito = (int)$row["importe_credito"];
		$debito = (int)$row["importe_debito"];
		$aplicadoDebito = (int)$row["monto_aplicado_gasto"];
		$aplicadoMigracion = isset($row["monto_aplicado_migracion"]) ? (int)$row["monto_aplicado_migracion"] : 0;
		$sugerenciasMigracion = isset($row["sugerencias_migracion"]) ? (int)$row["sugerencias_migracion"] : 0;
		$sugerenciasDeposito = isset($row["sugerencias_deposito"]) ? (int)$row["sugerencias_deposito"] : 0;
		$depositosConciliacion = isset($row["depositos_conciliacion"]) ? trim((string)$row["depositos_conciliacion"]) : "";
		$usuariosDeposito = isset($row["usuarios_deposito"]) ? trim((string)$row["usuarios_deposito"]) : "";
		$tieneDepositoConciliado = $depositosConciliacion != "";
		$disponible = $credito > 0 ? ueno_saldo_disponible_movimiento($mysqli, $row) : (int)$row["monto_disponible"];
		$aplicado = $credito > 0 ? max(0, $credito - $disponible) : 0;
		$baseAplicacion = $credito;
		if ($debito > 0) {
			$aplicado = min($debito, max(0, $aplicadoDebito));
			$disponible = max(0, $debito - $aplicado);
			$baseAplicacion = $debito;
		}
		if ($credito > 0 && $aplicado > $credito) {
			$aplicado = $credito;
		}
		$porcentaje_aplicado = $baseAplicacion > 0 ? min(100, max(0, round(($aplicado * 100) / $baseAplicacion))) : 0;
		$estado_visual = ueno_estado_movimiento_texto($row["estado"], $credito, $disponible, $debito, $aplicadoDebito);
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
		if (($credito > 0 || $debito > 0) && $disponible > 0) {
			$resumen["con_saldo"]++;
			$resumen["saldo_disponible"] += $disponible;
		}
		if (!ueno_movimiento_cumple_filtro_rapido($filtro_rapido, $estado_clave, $credito, $disponible)) {
			continue;
		}
		$clasificacionOrigen = ueno_clasificar_origen_movimiento($credito, $disponible, $sugerenciasDeposito, $tieneDepositoConciliado);
		if (!ueno_origen_cumple_filtro($origen_probable, $clasificacionOrigen)) {
			continue;
		}
		$total++;
		$total_creditos += $credito;
		$total_debitos += $debito;
		$datos_movimiento = array(
			"id_movimiento" => (int)$row["id_movimiento"],
			"id_importacion" => (int)$row["id_importacion"],
			"banco_codigo" => ueno_banco_codigo($row["banco_codigo"]),
			"banco_nombre" => ueno_banco_nombre($row["banco_codigo"]),
			"fecha_confirmacion" => ueno_from_db($row["fecha_confirmacion"]),
			"fecha_transaccion" => ueno_from_db($row["fecha_transaccion"]),
			"nro_comprobante" => ueno_from_db($row["nro_comprobante"]),
			"cuenta" => ueno_from_db($row["cuenta"]),
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
			"saldo_disponible" => $disponible,
			"saldo_disponible_fmt" => number_format($disponible, 0, ",", "."),
			"monto_asignado_gasto" => $aplicadoDebito,
			"monto_asignado_gasto_fmt" => number_format($aplicadoDebito, 0, ",", "."),
			"monto_asignado_migracion" => $aplicadoMigracion,
			"monto_asignado_migracion_fmt" => number_format($aplicadoMigracion, 0, ",", "."),
			"sugerencias_migracion" => $sugerenciasMigracion,
			"sugerencias_deposito" => $sugerenciasDeposito,
			"depositos_conciliacion" => $depositosConciliacion,
			"estado_bancario" => strtolower(trim((string)$row["estado"])),
			"estado" => $estado_visual,
			"estado_clave" => $estado_clave
		);
		$datos_js = htmlspecialchars(json_encode($datos_movimiento), ENT_QUOTES, 'UTF-8');
		$asignacionesVisiblesDebito = 0;
		$aplicacion_contable_html = ($debito > 0 && $aplicado > 0)
			? ueno_html_aplicacion_contable_debito($mysqli, (int)$row["id_movimiento"], $usuario, $asignacionesVisiblesDebito)
			: "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
		if ($clasificacionOrigen == "deposito_conciliado") {
			$etiquetaOrigen = "Depósito Faraone conciliado";
			$claseOrigen = "deposito-conciliado";
		} elseif ($clasificacionOrigen == "deposito") {
			$etiquetaOrigen = "Posible deposito Faraone Capital";
			$claseOrigen = "deposito";
		} elseif ($clasificacionOrigen == "revisar") {
			$etiquetaOrigen = "Revisar: " . $sugerenciasDeposito . " depositos";
			$claseOrigen = "revisar";
		} elseif ($clasificacionOrigen == "cuota") {
			$etiquetaOrigen = "Posible cuota";
			$claseOrigen = "cuota";
		} else {
			$etiquetaOrigen = "";
			$claseOrigen = "otro";
		}
		$creditoHtml = number_format($credito, 0, ",", ".");
		if ($estado_clave != "conciliado" && $etiquetaOrigen != "") {
			$creditoHtml .= "<button type='button' class='ueno-origin-badge ueno-origin-badge--" . $claseOrigen . "' onclick='uenoSeleccionarMovimientoTrabajo(" . $datos_js . ")'>" . ueno_escape_html($etiquetaOrigen) . "</button>";
		}
		if ($credito > 0 && $disponible > 0 && $estado_clave != "revisar") {
			if ($estado_clave == "parcial") {
				$accion = "<input type='button' value='Ver aplicaciones' class='btn4 ueno-row-action ueno-row-action--trace' onclick='uenoVerAplicacionMovimiento(" . (int)$row["id_movimiento"] . ")'>";
			} else if ($sugerenciasMigracion > 0) {
				$accion = "<input type='button' value='Ver sugerencia' class='btn4 ueno-row-action ueno-row-action--internal' onclick='uenoSeleccionarMovimientoTrabajo(" . $datos_js . ")'>";
			} else {
				$accion = "<input type='button' value='Ver detalle' class='btn4 ueno-row-action ueno-row-action--detail' onclick='uenoSeleccionarMovimientoTrabajo(" . $datos_js . ")'>";
			}
		} elseif ($estado_clave == "revisar") {
			$accion = "<span class='ueno-row-note ueno-row-note--muted'>Revisar</span>";
		} elseif ($credito > 0) {
			$accion = "<input type='button' value='Ver aplicacion' class='btn4 ueno-row-action ueno-row-action--view' onclick='uenoVerAplicacionMovimiento(" . (int)$row["id_movimiento"] . ")'>";
		} elseif ($debito > 0) {
			$accion = "";
			if ($puedeGestionarEgresos && $disponible > 0 && ueno_estado_debito_disponible_para_egreso($row["estado"])) {
				$accion .= "<input type='button' value='Registrar gasto' class='btn4 ueno-row-action ueno-row-action--available' onclick='uenoRegistrarGastoDesdeDebito(" . $datos_js . ")'>";
			}
			if ($aplicado > 0 && $asignacionesVisiblesDebito > 0) {
				$accion .= "<input type='button' value='Ver asignaciones' class='btn4 ueno-row-action ueno-row-action--trace' onclick='conciliarEgresoUenoVerAsignacionesBanco(" . (int)$row["id_movimiento"] . ")'>";
			} else if ($accion == "") {
				$accion .= "<input type='button' value='Ver detalle' class='btn4 ueno-row-action ueno-row-action--detail' onclick='uenoSeleccionarMovimientoTrabajo(" . $datos_js . ")'>";
			}
		} else {
			$accion = "<span class='ueno-row-note ueno-row-note--muted'>Sin accion</span>";
		}
		$aplicado_html = ($credito > 0 || $debito > 0)
			? "<div class='ueno-applied-cell'><strong>" . number_format($aplicado, 0, ",", ".") . "</strong>"
				. ($estado_clave == "parcial" ? "<small>de " . number_format($baseAplicacion, 0, ",", ".") . "</small><span class='ueno-progress'><i style='width:" . $porcentaje_aplicado . "%'></i></span>" : "")
				. "</div>"
			: "<span class='ueno-muted-money'>-</span>";
		$usuarios_conciliacion = ($credito > 0 && $aplicado > 0) ? trim((string)$row["usuarios_conciliacion"]) : "";
		$usuarios_migracion = ($credito > 0 && $aplicadoMigracion > 0) ? trim((string)$row["usuarios_migracion"]) : "";
		if ($usuarios_migracion != "") {
			$usuarios_conciliacion .= ($usuarios_conciliacion != "" ? ", " : "") . $usuarios_migracion;
		}
		if ($tieneDepositoConciliado && $usuariosDeposito != "") {
			$usuarios_conciliacion .= ($usuarios_conciliacion != "" ? ", " : "") . $usuariosDeposito;
		}
		$usuarios_html = $usuarios_conciliacion != ""
			? "<span class='ueno-user-cell' title='" . ueno_escape_html($usuarios_conciliacion) . "'>" . ueno_escape_html($usuarios_conciliacion) . "</span>"
			: "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
		$clientes_conciliacion = ($credito > 0 && $aplicado > 0) ? trim((string)$row["clientes_conciliacion"]) : "";
		$migraciones_conciliacion = ($credito > 0 && $aplicadoMigracion > 0) ? trim((string)$row["migraciones_conciliacion"]) : "";
		if ($migraciones_conciliacion != "") {
			$clientes_conciliacion .= ($clientes_conciliacion != "" ? ", " : "") . $migraciones_conciliacion;
		}
		if ($clientes_conciliacion == "" && $sugerenciasMigracion > 0) {
			$clientes_conciliacion = "Posible monto migrado (" . $sugerenciasMigracion . ")";
		}
		$clientes_html = $clientes_conciliacion != ""
			? "<span class='ueno-client-cell' title='" . ueno_escape_html($clientes_conciliacion) . "'>" . ueno_escape_html($clientes_conciliacion) . "</span>"
			: "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' class='ueno-movimiento-row ueno-movimiento-row--" . $estado_clave . "' data-ueno-estado='" . $estado_clave . "' data-ueno-banco='" . ueno_escape_html($datos_movimiento["banco_codigo"]) . "' data-ueno-disponible='" . $disponible . "' data-ueno-aplicado='" . $aplicado . "'>"
			. "<td style='width:7%'>" . ueno_banco_badge_html($row["banco_codigo"]) . "</td>"
			. "<td style='width:5%'>" . ueno_escape_html($row["fecha_confirmacion"]) . "</td>"
			. "<td style='width:5%'>" . ueno_escape_html($row["fecha_transaccion"]) . "</td>"
			. "<td style='width:7%'>" . ueno_escape_html($row["nro_comprobante"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
			. "<td style='width:6%'>" . ueno_escape_html($row["concepto"]) . "</td>"
			. "<td style='width:5%;text-align:right'>" . number_format($debito, 0, ",", ".") . "</td>"
			. "<td style='width:6%;text-align:right'>" . $creditoHtml . "</td>"
			. "<td style='width:6%;text-align:right'>" . $aplicado_html . "</td>"
			. "<td style='width:5%;text-align:right'>" . number_format($disponible, 0, ",", ".") . "</td>"
			. "<td style='width:6%'><span class='ueno-status-badge ueno-status-badge--" . $estado_clave . "'>" . ueno_escape_html($estado_visual) . "</span></td>"
			. "<td style='width:14%;text-align:left'>" . $aplicacion_contable_html . "</td>"
			. "<td style='width:8%;text-align:left'>" . $clientes_html . "</td>"
			. "<td style='width:5%;text-align:center'>" . $usuarios_html . "</td>"
			. "<td style='width:5%;text-align:center'>" . $accion . "</td>"
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

function ueno_buscar_movimientos($usuario)
{
	ueno_requerir_permiso($usuario, "VERCONCILIACIONUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$id_importacion = ueno_post("id_importacion");
	$fecha_desde = ueno_fecha(isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : "");
	$fecha_hasta = ueno_fecha(isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : "");
	$comprobante = ueno_post("nro_comprobante");
	$monto = ueno_monto(ueno_post("monto"));
	$estado = ueno_post("estado");
	$filtro_rapido = ueno_post("filtro_rapido");
	$origen_probable = ueno_post("origen_probable");
	$banco = ueno_banco_filtro_post();

	$tabla = ueno_tabla_movimientos($mysqli, $id_importacion, $fecha_desde, $fecha_hasta, $comprobante, $estado, $filtro_rapido, $monto, $origen_probable, $usuario, $banco);
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

function ueno_buscar_movimientos_cobro($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERCOBRARCUOTA", "VERPAGOSCREDITO", "VERCONCILIACIONUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$comprobante = ueno_comprobante_normalizado(ueno_from_db(ueno_post("comprobante")));
	$monto = isset($_POST["monto"]) ? ueno_monto($_POST["monto"]) : 0;
	$fecha_pago = ueno_fecha(isset($_POST["fecha_pago"]) ? $_POST["fecha_pago"] : "");
	$id_movimiento = preg_replace('/[^0-9]/', '', (string)ueno_post("id_movimiento"));
	$ver_todos = isset($_POST["ver_todos"]) && $_POST["ver_todos"] == "SI";

	$condicion = "mv.tipo_movimiento='credito'
		AND mv.importe_credito>0
		AND LOWER(IFNULL(mv.estado,'')) NOT IN ('conciliado','conciliada','asignado_total','anulado','anulada','rechazado','rechazada')
		";

	if ($id_movimiento != "") {
		$condicion .= " AND mv.id_movimiento='" . $mysqli->real_escape_string($id_movimiento) . "'";
	}
	if ($comprobante != "") {
		$compSql = $mysqli->real_escape_string($comprobante);
		$condicion .= " AND mv.nro_comprobante LIKE '%$compSql%'";
	}
	$ordenFecha = "";
	if ($fecha_pago != "" && !$ver_todos) {
		$fechaSql = $mysqli->real_escape_string($fecha_pago);
		$condicion .= " AND (mv.fecha_confirmacion='$fechaSql' OR mv.fecha_transaccion='$fechaSql')";
		$ordenFecha = "CASE WHEN mv.fecha_confirmacion='$fechaSql' OR mv.fecha_transaccion='$fechaSql' THEN 0 ELSE 1 END,";
	}

	$comprobanteSql = $mysqli->real_escape_string($comprobante);
	$sql = "SELECT mv.id_movimiento, mv.banco_codigo, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante,
		mv.descripcion, mv.concepto, mv.importe_credito, mv.monto_disponible, mv.estado
		FROM ueno_movimiento_bancario mv
		WHERE $condicion
		ORDER BY
			CASE WHEN '$comprobanteSql'<>'' AND mv.nro_comprobante='$comprobanteSql' THEN 0 ELSE 1 END,
			CASE WHEN '$comprobanteSql'<>'' AND mv.nro_comprobante LIKE '$comprobanteSql%' THEN 0 ELSE 1 END,
			$ordenFecha
			CASE WHEN $monto>0 AND mv.monto_disponible=$monto THEN 0 ELSE 1 END,
			CASE WHEN $monto>0 AND mv.monto_disponible>$monto THEN 0 ELSE 1 END,
			mv.fecha_confirmacion DESC,
			mv.id_movimiento DESC
		LIMIT 80";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $error));
	}

	$result = $stmt->get_result();
	$movimientos = array();
	$movimientosVistos = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$comprobanteReal = ueno_comprobante_normalizado(ueno_from_db($row["nro_comprobante"]));
		$fechaMovimientoDb = trim((string)$row["fecha_confirmacion"]) != "" ? $row["fecha_confirmacion"] : $row["fecha_transaccion"];
		$fechaMovimiento = ueno_fecha($fechaMovimientoDb);
		$fechaMovimientoVisual = ueno_fecha_visual($fechaMovimientoDb);
		$disponible = ueno_saldo_disponible_movimiento($mysqli, $row);
		if ($disponible <= 0) {
			continue;
		}
		$importe = (int)$row["importe_credito"];
		$bancoMovimiento = ueno_banco_codigo($row["banco_codigo"]);
		$claveMovimientoVisible = $bancoMovimiento . "|" . $comprobanteReal . "|" . $fechaMovimiento . "|" . $importe;
		if ($comprobanteReal != "" && isset($movimientosVistos[$claveMovimientoVisible])) {
			continue;
		}
		$movimientosVistos[$claveMovimientoVisible] = true;
		$estado = ueno_from_db($row["estado"]);
		$estadoNormalizado = strtolower(trim($estado));
		$estadoDisponible = !in_array($estadoNormalizado, array("conciliado", "conciliada", "asignado_total", "anulado", "anulada", "rechazado", "rechazada"));
		$montoValido = ($monto > 0 && $disponible > 0 && $monto <= $disponible);
		$fechaCoincide = ($fecha_pago != "" && $fechaMovimiento != "" && $fechaMovimiento == $fecha_pago);
		$mensajeAccion = "Usar movimiento";
		if (!$estadoDisponible || $disponible <= 0) {
			$mensajeAccion = "No disponible";
		} elseif ($monto <= 0) {
			$mensajeAccion = "Ingrese monto";
		} elseif (!$montoValido) {
			$mensajeAccion = "Saldo insuficiente";
		}
		$movimientos[] = array(
			"id_movimiento" => (int)$row["id_movimiento"],
			"banco_codigo" => $bancoMovimiento,
			"banco_nombre" => ueno_banco_nombre($bancoMovimiento),
			"nro_comprobante" => $comprobanteReal,
			"comprobante_masked" => ueno_mask_comprobante($comprobanteReal),
			"fecha_confirmacion" => ueno_from_db($row["fecha_confirmacion"]),
			"fecha_transaccion" => ueno_from_db($row["fecha_transaccion"]),
			"fecha_movimiento" => $fechaMovimientoVisual,
			"descripcion" => ueno_from_db($row["descripcion"]),
			"concepto" => ueno_from_db($row["concepto"]),
			"importe_credito" => $importe,
			"importe_credito_fmt" => number_format($importe, 0, ",", "."),
			"monto_disponible" => $disponible,
			"monto_disponible_fmt" => number_format($disponible, 0, ",", "."),
			"estado" => $estado,
			"fecha_pago_coincide" => $fechaCoincide,
			"monto_valido" => $montoValido,
			"puede_usar" => $estadoDisponible && $montoValido,
			"mensaje_accion" => $mensajeAccion
		);
	}

	$total = count($movimientos);
	mysqli_close($mysqli);
	ueno_json(array(
		"1" => "exito",
		"movimientos" => $movimientos,
		"3" => $total,
		"fecha_pago" => $fecha_pago,
		"ver_todos" => $ver_todos ? "SI" : "NO"
	));
}

function ueno_buscar_importaciones($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VEREXTRACTOSUENO", "VERCONCILIACIONUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$fecha_desde = ueno_fecha(isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : "");
	$fecha_hasta = ueno_fecha(isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : "");
	$vista = ueno_post("vista");
	$comprobante = ueno_post("nro_comprobante");
	$monto = ueno_monto(ueno_post("monto"));
	$banco = ueno_banco_filtro_post();
	$condicion = $banco != "" ? " AND imp.banco_codigo='" . $mysqli->real_escape_string($banco) . "'" : "";
	if ($fecha_desde != "") {
		$condicion .= " AND imp.fecha_extracto>='" . $mysqli->real_escape_string($fecha_desde) . "'";
	}
	if ($fecha_hasta != "") {
		$condicion .= " AND imp.fecha_extracto<='" . $mysqli->real_escape_string($fecha_hasta) . "'";
	}
	$condicionMovimiento = "";
	$tieneFiltroMovimiento = ($comprobante != "" || $monto > 0);
	if ($comprobante != "") {
		$condicionMovimiento .= " AND mv.nro_comprobante LIKE '%" . $mysqli->real_escape_string($comprobante) . "%'";
	}
	if ($monto > 0) {
		$monto = (int)$monto;
		$condicionMovimiento .= " AND (mv.importe_debito=" . $monto
			. " OR mv.importe_credito=" . $monto
			. " OR mv.monto_disponible=" . $monto
			. " OR (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)=" . $monto . ")";
	}
	if ($tieneFiltroMovimiento) {
		$condicion .= " AND EXISTS (
			SELECT 1
			FROM ueno_movimiento_bancario mv
			WHERE mv.id_importacion=imp.id_importacion
			$condicionMovimiento
		)";
	}

	$sql = "SELECT imp.id_importacion, imp.banco_codigo, imp.cuenta, imp.fecha_extracto, imp.nombre_archivo_original, imp.fecha_hora_importacion,
		cantidad_movimientos, cantidad_creditos, cantidad_debitos, total_creditos, total_debitos, estado
		FROM ueno_importacion_extracto imp
		WHERE imp.id_importacion!='0' $condicion
		ORDER BY imp.id_importacion DESC
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
		$esModal = $vista == "modal";
		$accionFila = $esModal ? "uenoVerDetalleImportacionDesdeImportacion(" . (int)$row["id_importacion"] . ")" : "uenoSeleccionarImportacion(" . (int)$row["id_importacion"] . ")";
		$coincidenciaHtml = "";
		if ($esModal) {
			$coincidenciaHtml = "<span class='ueno-row-note ueno-row-note--muted'>Sin filtro</span>";
			if ($tieneFiltroMovimiento) {
				$idImportacionSql = (int)$row["id_importacion"];
				$sqlMatch = "SELECT mv.id_movimiento, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante,
					mv.importe_debito, mv.importe_credito, mv.monto_disponible, mv.estado,
					(SELECT COUNT(*) FROM ueno_movimiento_bancario mv WHERE mv.id_importacion=$idImportacionSql $condicionMovimiento) AS total_coincidencias
					FROM ueno_movimiento_bancario mv
					WHERE mv.id_importacion=$idImportacionSql $condicionMovimiento
					ORDER BY
						CASE WHEN mv.nro_comprobante REGEXP '^[0-9]+$' THEN 0 ELSE 1 END ASC,
						CASE WHEN mv.nro_comprobante REGEXP '^[0-9]+$' THEN CAST(mv.nro_comprobante AS UNSIGNED) ELSE 0 END ASC,
						mv.nro_comprobante ASC,
						mv.id_movimiento ASC
					LIMIT 1";
				$resMatch = $mysqli->query($sqlMatch);
				$match = $resMatch ? $resMatch->fetch_assoc() : null;
				if ($match) {
					$fechaMatch = trim((string)$match["fecha_confirmacion"]) != "" ? $match["fecha_confirmacion"] : $match["fecha_transaccion"];
					$importeMatch = (int)$match["importe_credito"] > 0 ? (int)$match["importe_credito"] : (int)$match["importe_debito"];
					$totalCoincidencias = (int)$match["total_coincidencias"];
					$coincidenciaTexto = "Reg. #" . (int)$match["id_movimiento"]
						. " | Comp. " . ueno_from_db($match["nro_comprobante"])
						. " | " . number_format($importeMatch, 0, ",", ".")
						. " | Disp. " . number_format($match["monto_disponible"], 0, ",", ".")
						. " | " . $fechaMatch;
					if ($totalCoincidencias > 1) {
						$coincidenciaTexto .= " | +" . ($totalCoincidencias - 1);
					}
					$coincidenciaHtml = "<span class='ueno-found-cell' title='" . ueno_escape_html($coincidenciaTexto) . "'>" . ueno_escape_html($coincidenciaTexto) . "</span>";
				}
			}
		}
		if ($esModal) {
			$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' data-ueno-importacion-id='" . (int)$row["id_importacion"] . "' data-ueno-banco='" . ueno_escape_html(ueno_banco_codigo($row["banco_codigo"])) . "' onclick='" . $accionFila . "'>"
				. "<td style='width:8%'>" . ueno_banco_badge_html($row["banco_codigo"], false) . "</td>"
				. "<td style='width:5%'>" . $row["id_importacion"] . "</td>"
				. "<td style='width:9%'>" . ueno_escape_html($row["cuenta"]) . "</td>"
				. "<td style='width:8%'>" . ueno_escape_html($row["fecha_extracto"]) . "</td>"
				. "<td style='width:17%'>" . ueno_escape_html($row["nombre_archivo_original"]) . "</td>"
				. "<td style='width:11%'>" . ueno_escape_html($row["fecha_hora_importacion"]) . "</td>"
				. "<td style='width:6%;text-align:right'>" . number_format($row["cantidad_movimientos"], 0, ",", ".") . "</td>"
				. "<td style='width:7%;text-align:right'>" . number_format($row["total_creditos"], 0, ",", ".") . "</td>"
				. "<td style='width:7%;text-align:right'>" . number_format($row["total_debitos"], 0, ",", ".") . "</td>"
				. "<td style='width:8%'>" . ueno_escape_html($row["estado"]) . "</td>"
				. "<td style='width:14%'>" . $coincidenciaHtml . "</td>"
				. "</tr></table>";
		} else {
			$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' data-ueno-importacion-id='" . (int)$row["id_importacion"] . "' data-ueno-banco='" . ueno_escape_html(ueno_banco_codigo($row["banco_codigo"])) . "' onclick='" . $accionFila . "'>"
				. "<td style='width:10%'>" . ueno_banco_badge_html($row["banco_codigo"]) . "</td>"
				. "<td style='width:6%'>" . $row["id_importacion"] . "</td>"
				. "<td style='width:10%'>" . ueno_escape_html($row["cuenta"]) . "</td>"
				. "<td style='width:9%'>" . ueno_escape_html($row["fecha_extracto"]) . "</td>"
				. "<td style='width:19%'>" . ueno_escape_html($row["nombre_archivo_original"]) . "</td>"
				. "<td style='width:13%'>" . ueno_escape_html($row["fecha_hora_importacion"]) . "</td>"
				. "<td style='width:7%;text-align:right'>" . number_format($row["cantidad_movimientos"], 0, ",", ".") . "</td>"
				. "<td style='width:9%;text-align:right'>" . number_format($row["total_creditos"], 0, ",", ".") . "</td>"
				. "<td style='width:9%;text-align:right'>" . number_format($row["total_debitos"], 0, ",", ".") . "</td>"
				. "<td style='width:8%'>" . ueno_escape_html($row["estado"]) . "</td>"
				. "</tr></table>";
		}
	}
	mysqli_close($mysqli);
	ueno_json(array("1" => "exito", "2" => $html, "3" => $total));
}

function ueno_detalle_importacion($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VEREXTRACTOSUENO", "VERCONCILIACIONUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$id_importacion = (int)ueno_post("id_importacion");
	if ($id_importacion <= 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "camposvacio", "2" => "No se pudo identificar el archivo migrado"));
	}
	$comprobante = ueno_post("nro_comprobante");
	$monto = ueno_monto(ueno_post("monto"));
	$condicionMovimiento = "";
	if ($comprobante != "") {
		$condicionMovimiento .= " AND mv.nro_comprobante LIKE '%" . $mysqli->real_escape_string($comprobante) . "%'";
	}
	if ($monto > 0) {
		$monto = (int)$monto;
		$condicionMovimiento .= " AND (mv.importe_debito=" . $monto
			. " OR mv.importe_credito=" . $monto
			. " OR mv.monto_disponible=" . $monto
			. " OR (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)=" . $monto . ")";
	}

	$sqlImportacion = "SELECT id_importacion, banco_codigo, cuenta, denominacion, moneda_codigo, fecha_extracto, periodo_desde, periodo_hasta,
		nombre_archivo_original, hash_archivo, usuario_importo, fecha_hora_importacion, cantidad_movimientos,
		cantidad_creditos, cantidad_debitos, total_creditos, total_debitos, saldo_anterior, saldo_final, estado, observacion
		FROM ueno_importacion_extracto
		WHERE id_importacion=?
		LIMIT 1";
	$stmtImp = $mysqli->prepare($sqlImportacion);
	if (!$stmtImp) {
		$error = $mysqli->error;
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $error));
	}
	$stmtImp->bind_param("i", $id_importacion);
	if (!$stmtImp->execute()) {
		$error = $stmtImp->error;
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $error));
	}
	$resImp = $stmtImp->get_result();
	if (!$resImp || $resImp->num_rows == 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "noencontrado", "2" => "No encontramos el archivo migrado seleccionado"));
	}
	$imp = $resImp->fetch_assoc();

	$sqlMovimientos = "SELECT mv.id_movimiento, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante, mv.descripcion, mv.concepto,
		mv.importe_debito, mv.importe_credito, mv.monto_disponible, mv.estado,
		(
			SELECT COUNT(*)
			FROM ueno_movimiento_bancario dup
			WHERE dup.id_movimiento<>mv.id_movimiento
			AND dup.banco_codigo=mv.banco_codigo
			AND dup.cuenta=mv.cuenta
			AND dup.nro_comprobante=mv.nro_comprobante
			AND mv.nro_comprobante!=''
			AND (CASE WHEN dup.importe_credito>0 THEN dup.importe_credito ELSE dup.importe_debito END)=
				(CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)
			AND (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)>0
		) AS duplicados_comprobante_monto,
		(
			SELECT GROUP_CONCAT(
				CONCAT(
					'#', dup.id_movimiento,
					' | Importacion ', dup.id_importacion,
					' | Archivo: ', IFNULL(imp.nombre_archivo_original, ''),
					' | Importado: ', IFNULL(imp.fecha_hora_importacion, '')
				)
				ORDER BY dup.id_movimiento DESC
				SEPARATOR '||'
			)
			FROM ueno_movimiento_bancario dup
			LEFT JOIN ueno_importacion_extracto imp ON imp.id_importacion=dup.id_importacion
			WHERE dup.id_movimiento<>mv.id_movimiento
			AND dup.banco_codigo=mv.banco_codigo
			AND dup.cuenta=mv.cuenta
			AND dup.nro_comprobante=mv.nro_comprobante
			AND mv.nro_comprobante!=''
			AND (CASE WHEN dup.importe_credito>0 THEN dup.importe_credito ELSE dup.importe_debito END)=
				(CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)
			AND (CASE WHEN mv.importe_credito>0 THEN mv.importe_credito ELSE mv.importe_debito END)>0
			LIMIT 5
		) AS detalle_duplicados
		FROM ueno_movimiento_bancario mv
		WHERE mv.id_importacion=? $condicionMovimiento
		ORDER BY
			CASE WHEN mv.nro_comprobante REGEXP '^[0-9]+$' THEN 0 ELSE 1 END ASC,
			CASE WHEN mv.nro_comprobante REGEXP '^[0-9]+$' THEN CAST(mv.nro_comprobante AS UNSIGNED) ELSE 0 END ASC,
			mv.nro_comprobante ASC,
			mv.id_movimiento ASC
		LIMIT 300";
	$stmtMov = $mysqli->prepare($sqlMovimientos);
	if (!$stmtMov) {
		$error = $mysqli->error;
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $error));
	}
	$stmtMov->bind_param("i", $id_importacion);
	if (!$stmtMov->execute()) {
		$error = $stmtMov->error;
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $error));
	}
	$resMov = $stmtMov->get_result();
	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($resMov)) {
		$total++;
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$descripcion = trim((string)$row["descripcion"]);
		$concepto = trim((string)$row["concepto"]);
		if ($concepto != "" && $concepto != $descripcion) {
			$descripcion .= ($descripcion != "" ? " / " : "") . $concepto;
		}
		$duplicados = (int)$row["duplicados_comprobante_monto"];
		$detalleDuplicado = $duplicados > 0
			? "Duplicado (" . $duplicados . ") " . $row["detalle_duplicados"]
			: "Sin duplicado";
		$claseDuplicado = $duplicados > 0 ? "ueno-row-note" : "ueno-row-note ueno-row-note--ok";
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:5%;text-align:center'>" . number_format($total, 0, ",", ".") . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["fecha_confirmacion"]) . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["fecha_transaccion"]) . "</td>"
			. "<td style='width:11%'>" . ueno_escape_html($row["nro_comprobante"]) . "</td>"
			. "<td style='width:25%'>" . ueno_escape_html($descripcion) . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($row["importe_debito"], 0, ",", ".") . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($row["importe_credito"], 0, ",", ".") . "</td>"
			. "<td style='width:8%;text-align:right'>" . number_format($row["monto_disponible"], 0, ",", ".") . "</td>"
			. "<td style='width:10%'><span class='" . $claseDuplicado . "' title='" . ueno_escape_html($detalleDuplicado) . "'>" . ($duplicados > 0 ? "Duplicado" : "OK") . "</span></td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["estado"]) . "</td>"
			. "</tr>";
		if ($duplicados > 0 && trim((string)$row["detalle_duplicados"]) != "") {
			$partesDuplicadas = explode("||", (string)$row["detalle_duplicados"]);
			$htmlDetalle = "";
			foreach ($partesDuplicadas as $parteDuplicada) {
				$parteDuplicada = trim($parteDuplicada);
				if ($parteDuplicada == "") {
					continue;
				}
				$htmlDetalle .= "<span>" . ueno_escape_html($parteDuplicada) . "</span>";
			}
			if ($htmlDetalle != "") {
				$html .= "<tr class='ueno-duplicate-detail-row'><td colspan='10'>"
					. "<div class='ueno-duplicate-detail'><b>Duplicado encontrado en:</b>" . $htmlDetalle . "</div>"
					. "</td></tr>";
			}
		}
		$html .= "</table>";
	}
	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'><td style='width:100%;text-align:center'>No hay movimientos para este filtro.</td></tr></table>";
	}
	mysqli_close($mysqli);
	ueno_json(array(
		"1" => "exito",
		"importacion" => array(
			"id_importacion" => $imp["id_importacion"],
			"banco_codigo" => ueno_banco_codigo($imp["banco_codigo"]),
			"banco_nombre" => ueno_banco_nombre($imp["banco_codigo"]),
			"cuenta" => ueno_from_db($imp["cuenta"]),
			"denominacion" => ueno_from_db($imp["denominacion"]),
			"moneda_codigo" => ueno_from_db($imp["moneda_codigo"]),
			"fecha_extracto" => ueno_from_db($imp["fecha_extracto"]),
			"periodo_desde" => ueno_from_db($imp["periodo_desde"]),
			"periodo_hasta" => ueno_from_db($imp["periodo_hasta"]),
			"archivo" => ueno_from_db($imp["nombre_archivo_original"]),
			"hash_archivo" => ueno_from_db($imp["hash_archivo"]),
			"usuario" => ueno_from_db($imp["usuario_importo"]),
			"fecha_importacion" => ueno_from_db($imp["fecha_hora_importacion"]),
			"movimientos" => number_format($imp["cantidad_movimientos"], 0, ",", "."),
			"creditos" => number_format($imp["cantidad_creditos"], 0, ",", "."),
			"debitos" => number_format($imp["cantidad_debitos"], 0, ",", "."),
			"total_creditos" => number_format($imp["total_creditos"], 0, ",", "."),
			"total_debitos" => number_format($imp["total_debitos"], 0, ",", "."),
			"saldo_anterior" => $imp["saldo_anterior"] === null ? "" : number_format($imp["saldo_anterior"], 0, ",", "."),
			"saldo_final" => $imp["saldo_final"] === null ? "" : number_format($imp["saldo_final"], 0, ",", "."),
			"estado" => ueno_from_db($imp["estado"]),
			"observacion" => ueno_from_db($imp["observacion"])
		),
		"tabla" => $html,
		"total_movimientos" => $total
	));
}

function ueno_buscar_pagos_pendientes($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERPAGOSPENDIENTESUENO", "VERCONCILIACIONUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$estado = ueno_post("estado");
	$comprobante = ueno_post("nro_comprobante");
	$cliente = ueno_post("cliente");
	$venta = ueno_post("venta");
	$monto_busqueda = isset($_POST["monto"]) ? ueno_monto($_POST["monto"]) : 0;
	$monto_referencia = isset($_POST["monto_referencia"]) ? ueno_monto($_POST["monto_referencia"]) : 0;
	$banco = ueno_banco_filtro_post();
	$condicion = $banco != "" ? " AND pc.banco_codigo='" . $mysqli->real_escape_string($banco) . "'" : "";
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

	$sql = "SELECT pc.id, pc.cod_pagoFK, pc.banco_codigo, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion, pc.observacion,
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
			"banco_codigo" => ueno_banco_codigo($row["banco_codigo"]),
			"banco_nombre" => ueno_banco_nombre($row["banco_codigo"]),
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
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' data-ueno-pago-id='" . (int)$row["id"] . "' data-ueno-banco='" . ueno_escape_html(ueno_banco_codigo($row["banco_codigo"])) . "'>"
			. "<td style='width:10%'>" . ueno_banco_badge_html($row["banco_codigo"]) . "</td>"
			. "<td style='width:13%'>" . ueno_escape_html($row["cliente"]) . "</td>"
			. "<td style='width:7%'>" . ueno_escape_html($row["cedula"]) . "</td>"
			. "<td style='width:8%;text-align:center'>" . ueno_escape_html($row["cod_venta_fk"]) . "</td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["titulocuota"] != "" ? $row["titulocuota"] : $row["nrofactura"]) . "</td>"
			. "<td style='width:8%'>" . ueno_escape_html($row["Fecha"]) . "</td>"
			. "<td style='width:9%;text-align:right'>" . number_format($saldo_pendiente, 0, ",", ".") . "</td>"
			. "<td style='width:9%;text-align:right'>" . number_format($monto_sugerido, 0, ",", ".") . "</td>"
			. "<td style='width:9%'><span class='ueno-status-badge'>" . ueno_escape_html($estado_visual) . "</span></td>"
			. "<td style='width:9%'>" . ueno_escape_html($coincidencia) . "</td>"
			. "<td style='width:9%;text-align:center'>" . $accion_manual . "</td>"
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

function ueno_dias_entre_fechas($fecha_inicial, $fecha_final)
{
	$fecha_inicial = ueno_fecha($fecha_inicial);
	$fecha_final = ueno_fecha($fecha_final);
	if ($fecha_inicial == "" || $fecha_final == "") {
		return 0;
	}
	$inicio = strtotime($fecha_inicial);
	$fin = strtotime($fecha_final);
	if ($inicio === false || $fin === false) {
		return 0;
	}
	return (int)floor(($fin - $inicio) / 86400);
}

function ueno_migracion_fecha_inicio_sistema()
{
	return "2026-06-18";
}

function ueno_migracion_rango_fechas($fecha_banco)
{
	$fecha_banco = ueno_fecha($fecha_banco);
	$fecha_inicio_sistema = ueno_migracion_fecha_inicio_sistema();
	if ($fecha_banco == "" || $fecha_banco < $fecha_inicio_sistema) {
		return array("inicio" => "", "fin" => "");
	}
	$inicio = date("Y-m-d", strtotime($fecha_banco . " -4 days"));
	$fin = date("Y-m-d", strtotime($fecha_banco . " +1 day"));
	if ($inicio < $fecha_inicio_sistema) {
		$inicio = $fecha_inicio_sistema;
	}
	return array("inicio" => $inicio, "fin" => $fin);
}

function ueno_migracion_advertencia($fecha_migracion, $fecha_banco)
{
	$dias = ueno_dias_entre_fechas($fecha_migracion, $fecha_banco);
	if ($dias < 0) {
		return array(
			"requiere" => true,
			"texto" => "El credito bancario es anterior al monto migrado. Revisar antes de conciliar.",
			"dias" => $dias
		);
	}
	if ($dias > 3) {
		return array(
			"requiere" => true,
			"texto" => "La acreditacion aparece " . $dias . " dias despues del monto migrado. Confirmar solo si Tesoreria verifico el origen.",
			"dias" => $dias
		);
	}
	return array(
		"requiere" => false,
		"texto" => $dias == 0 ? "Coincidencia del mismo dia." : "Coincidencia dentro del rango normal (" . $dias . " dia/s).",
		"dias" => $dias
	);
}

function ueno_migracion_tabla_faltante_msg()
{
	return "Falta ejecutar actualizacion_03072026_conciliacion_ueno_monto_migrado.sql";
}

function ueno_categoria_flujo_texto($categoria)
{
	$categoria = trim((string)$categoria);
	if ($categoria == "ingreso") {
		return "Ingresos";
	}
	if ($categoria == "directo") {
		return "Costos variables";
	}
	if ($categoria == "operativo") {
		return "Gastos fijos";
	}
	return "Egresos";
}

function ueno_es_local_administracion_compartida($codLocal, $localNombre)
{
	$codLocal = trim((string)$codLocal);
	$localNombre = strtoupper(trim((string)$localNombre));
	return $codLocal == "1" || (strpos($localNombre, "ADMINISTRACION") !== false && strpos($localNombre, "COMPARTIDOS") !== false);
}

function ueno_categoria_aplicacion_clave($categoria, $codLocal, $localNombre)
{
	if (ueno_es_local_administracion_compartida($codLocal, $localNombre)) {
		return "administracion";
	}
	$categoria = strtolower(trim((string)$categoria));
	if ($categoria == "ingreso") {
		return "ingresos";
	}
	if ($categoria == "directo") {
		return "variables";
	}
	if ($categoria == "operativo") {
		return "fijos";
	}
	return "sin-categoria";
}

function ueno_categoria_aplicacion_texto($categoria, $codLocal, $localNombre)
{
	if (ueno_es_local_administracion_compartida($codLocal, $localNombre)) {
		return "Administracion / Compartidos";
	}
	$categoria = strtolower(trim((string)$categoria));
	if ($categoria == "ingreso") {
		return "Ingresos";
	}
	if ($categoria == "directo") {
		return "Costos variables";
	}
	if ($categoria == "operativo") {
		return "Gastos fijos";
	}
	return "Sin categorizar";
}

function ueno_local_aplicacion_texto($codLocal, $localNombre)
{
	if (ueno_es_local_administracion_compartida($codLocal, $localNombre)) {
		return "Compartidos";
	}
	$localNombre = trim((string)$localNombre);
	if ($localNombre == "") {
		return "Sin local";
	}
	$localNombre = preg_replace('/^CLINIDENT\s+/i', '', $localNombre);
	$localNombre = preg_replace('/\s*\([^)]*\)\s*/', ' ', $localNombre);
	$localNombre = preg_replace('/\s+/', ' ', $localNombre);
	return trim($localNombre) != "" ? trim($localNombre) : "Sin local";
}

function ueno_html_aplicacion_contable_debito($mysqli, $idMovimiento, $usuario, &$totalVisible = 0)
{
	$idMovimiento = (int)$idMovimiento;
	$totalVisible = 0;
	static $tablaMovimientoGastoExiste = null;
	if ($tablaMovimientoGastoExiste === null) {
		$tablaMovimientoGastoExiste = ueno_tabla_existe($mysqli, "ueno_movimiento_gasto");
	}
	if ($idMovimiento <= 0 || !$tablaMovimientoGastoExiste) {
		return "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
	}
	$filtroLocal= "";
	$filtroLocalTotal= "";
	if ((string)$usuario !== "2" && !ueno_usuario_tiene_permiso($usuario, "CAMBIARLOCAL")) {
		$codLocalUsuario= ueno_local_usuario($mysqli, $usuario);
		if ($codLocalUsuario <= 0) {
			return "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
		}
		$filtroLocal= " AND g.cod_local=".(int)$codLocalUsuario;
		$filtroLocalTotal= " AND g_total.cod_local=".(int)$codLocalUsuario;
	}
	$sql = "SELECT umg.id, umg.monto_aplicado,
		g.idgastos, g.motivo AS descripcion, g.cod_local,
		IFNULL(m.descripcion,'') AS concepto, IFNULL(m.categoria,'') AS categoria, LOWER(TRIM(IFNULL(m.estado,''))) AS estado_concepto, IFNULL(l.Nombre,'') AS local_nombre,
		(SELECT COUNT(*) FROM ueno_movimiento_gasto umg_total INNER JOIN gastos g_total ON g_total.idgastos=umg_total.idgastos WHERE umg_total.id_movimiento=$idMovimiento AND umg_total.estado='activo'$filtroLocalTotal) AS total_asignaciones
		FROM ueno_movimiento_gasto umg
		INNER JOIN gastos g ON g.idgastos=umg.idgastos
		LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
		LEFT JOIN local l ON l.cod_local=g.cod_local
		WHERE umg.id_movimiento=$idMovimiento AND umg.estado='activo'$filtroLocal
		ORDER BY umg.id DESC
		LIMIT 2";
	$result = $mysqli->query($sql);
	if (!$result || $result->num_rows == 0) {
		return "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
	}
	$html = "<button type='button' class='ueno-accounting-cell' onclick='conciliarEgresoUenoVerAsignacionesBanco($idMovimiento)' title='Ver asignaciones contables'>";
	$totalAsignaciones = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		$totalAsignaciones = (int)$row["total_asignaciones"];
		$totalVisible++;
		$clase = ueno_categoria_aplicacion_clave($row["categoria"], $row["cod_local"], $row["local_nombre"]);
		$categoria = ueno_categoria_aplicacion_texto($row["categoria"], $row["cod_local"], $row["local_nombre"]);
		$concepto = trim((string)$row["concepto"]);
		if ($concepto == "") {
			$concepto = "Sin concepto";
		}
		$local = ueno_local_aplicacion_texto($row["cod_local"], $row["local_nombre"]);
		$monto = "Gs. " . ueno_numero($row["monto_aplicado"]);
		$detalle = $concepto . " - " . $local . " - " . $monto;
		$html .= "<span class='ueno-accounting-item ueno-accounting-item--" . ueno_escape_html($clase) . "'>"
			. "<b>" . ueno_escape_html($categoria) . "</b>"
			. "<small>" . ueno_escape_html($detalle) . "</small>"
			. "</span>";
	}
	if ($totalAsignaciones > 2) {
		$html .= "<span class='ueno-accounting-more'>+ " . ($totalAsignaciones - 2) . " asignaciones mas</span>";
	}
	$html .= "</button>";
	return $html;
}

function ueno_estado_conciliacion_monto($monto_total, $monto_aplicado)
{
	$monto_total = (int)$monto_total;
	$monto_aplicado = (int)$monto_aplicado;
	if ($monto_aplicado <= 0) {
		return "SIN_CONCILIAR";
	}
	if ($monto_aplicado >= $monto_total) {
		return "CONCILIADO";
	}
	return "PARCIALMENTE_CONCILIADO";
}

function ueno_estado_conciliacion_visual($estado)
{
	if ($estado == "CONCILIADO") {
		return "Conciliado";
	}
	if ($estado == "PARCIALMENTE_CONCILIADO") {
		return "Parcialmente conciliado";
	}
	return "Sin conciliar";
}

function ueno_buscar_gasto_egreso_por_id($mysqli, $idgastos, $bloquear = false, $validarConciliable = true)
{
	$idgastos = (int)$idgastos;
	if ($idgastos <= 0) {
		throw new Exception("Seleccione un gasto valido para conciliar");
	}
	$forUpdate = $bloquear ? " FOR UPDATE" : "";
	$sql = "SELECT g.idgastos, g.monto, g.motivo AS descripcion, g.fecha, g.estado, g.tipo, g.cod_local,
		g.cod_motivoIngresoEgresoFK, g.cod_proyecto_gastoFK, g.cod_gasto_padre, g.banco, g.nrocuenta, g.nroboleta,
		IFNULL(m.descripcion,'') AS concepto, IFNULL(m.categoria,'') AS categoria, LOWER(TRIM(IFNULL(m.estado,''))) AS estado_concepto, IFNULL(l.Nombre,'') AS local_nombre,
		IFNULL((SELECT SUM(umg.monto_aplicado) FROM ueno_movimiento_gasto umg WHERE umg.idgastos=g.idgastos AND umg.estado='activo'),0) AS monto_conciliado_ueno
		FROM gastos g
		LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
		LEFT JOIN local l ON l.cod_local=g.cod_local
		WHERE g.idgastos=$idgastos
		LIMIT 1$forUpdate";
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}
	if ($result->num_rows == 0) {
		throw new Exception("No se encontro el gasto seleccionado");
	}
	$row = $result->fetch_assoc();
	if (strtolower(trim((string)$row["tipo"])) != "egreso") {
		throw new Exception("La conciliacion bancaria de egresos solo admite movimientos de tipo EGRESO");
	}
	if ((int)$row["monto"] <= 0) {
		throw new Exception("El gasto seleccionado no tiene un monto positivo conciliable");
	}
	$estadoGasto = strtolower(trim((string)$row["estado"]));
	if ($validarConciliable && !in_array($estadoGasto, array("activo", "pendiente", "solicitado"), true)) {
		throw new Exception("El gasto seleccionado ya no se encuentra activo o pendiente de conciliacion");
	}
	if ($validarConciliable && $row["estado_concepto"] !== "activo") {
		throw new Exception("El concepto financiero es historico y el gasto es de solo lectura");
	}
	return $row;
}

function ueno_gasto_egreso_json($gasto)
{
	$monto = (int)$gasto["monto"];
	$conciliado = (int)$gasto["monto_conciliado_ueno"];
	$pendiente = max(0, $monto - $conciliado);
	$estadoConciliacion = ueno_estado_conciliacion_monto($monto, $conciliado);
	return array(
		"idgastos" => (int)$gasto["idgastos"],
		"grupo" => ueno_from_db(ueno_categoria_flujo_texto($gasto["categoria"])),
		"concepto" => ueno_from_db($gasto["concepto"]),
		"descripcion" => ueno_from_db($gasto["descripcion"]),
		"local" => ueno_from_db($gasto["local_nombre"]),
		"fecha" => ueno_from_db($gasto["fecha"]),
		"estado" => ueno_from_db($gasto["estado"]),
		"estado_conciliacion" => $estadoConciliacion,
		"estado_conciliacion_texto" => ueno_estado_conciliacion_visual($estadoConciliacion),
		"monto" => $monto,
		"monto_fmt" => ueno_numero($monto),
		"conciliado" => $conciliado,
		"conciliado_fmt" => ueno_numero($conciliado),
		"pendiente" => $pendiente,
		"pendiente_fmt" => ueno_numero($pendiente),
		"cod_motivo" => ueno_from_db($gasto["cod_motivoIngresoEgresoFK"]),
		"cod_local" => ueno_from_db($gasto["cod_local"])
	);
}

function ueno_tabla_asignaciones_gasto($mysqli, $idgastos, $usuario)
{
	$idgastos = (int)$idgastos;
	$sql = "SELECT umg.id, umg.id_movimiento, umg.monto_aplicado, umg.fecha_hora_asociacion, umg.estado, umg.observacion,
		umg.usuario_asocio, umg.fecha_hora_reversion, umg.motivo_reversion,
		mv.fecha_confirmacion, mv.cuenta, mv.nro_comprobante, mv.descripcion
		FROM ueno_movimiento_gasto umg
		INNER JOIN ueno_movimiento_bancario mv ON mv.id_movimiento=umg.id_movimiento
		WHERE umg.idgastos=$idgastos
		ORDER BY umg.estado ASC, umg.id DESC
		LIMIT 100";
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}
	$html = "";
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$accion = "<span class='ueno-row-note ueno-row-note--muted'>-</span>";
		if ($row["estado"] == "activo" && ueno_usuario_tiene_permiso($usuario, "REVERTIRCONCILIACIONEGRESOUENO")) {
			$accion = "<button type='button' class='btn4 ueno-row-action ueno-row-action--trace' onclick='conciliarEgresoUenoRevertirAsignacion(" . (int)$row["id"] . ")'>Revertir</button>";
		}
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName conciliacion-egreso-asignacion-row' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:10%'>" . ueno_escape_html($row["fecha_confirmacion"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["cuenta"]) . "</td>"
			. "<td style='width:14%'>" . ueno_escape_html($row["nro_comprobante"]) . "</td>"
			. "<td style='width:24%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
			. "<td style='width:10%;text-align:right'>" . ueno_numero($row["monto_aplicado"]) . "</td>"
			. "<td style='width:9%;text-align:center'>" . ueno_escape_html($row["usuario_asocio"]) . "</td>"
			. "<td style='width:13%'>" . ueno_escape_html($row["fecha_hora_asociacion"]) . "</td>"
			. "<td style='width:10%;text-align:center'>" . ueno_escape_html($row["estado"]) . "</td>"
			. "<td style='width:10%;text-align:center'>" . $accion . "</td>"
			. "</tr></table>";
	}
	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr><td style='width:100%;text-align:center'>Sin asignaciones bancarias registradas para este gasto.</td></tr></table>";
	}
	return $html;
}

function ueno_actualizar_estado_movimiento_debito($mysqli, $id_movimiento)
{
	$id_movimiento = (int)$id_movimiento;
	$sql = "SELECT importe_debito,
		IFNULL((SELECT SUM(monto_aplicado) FROM ueno_movimiento_gasto WHERE id_movimiento=$id_movimiento AND estado='activo'),0) AS aplicado
		FROM ueno_movimiento_bancario
		WHERE id_movimiento=$id_movimiento
		LIMIT 1";
	$result = $mysqli->query($sql);
	if (!$result || $result->num_rows == 0) {
		throw new Exception("No se encontro el movimiento bancario para recalcular");
	}
	$row = $result->fetch_assoc();
	$debito = (int)$row["importe_debito"];
	$aplicado = (int)$row["aplicado"];
	$estado = "disponible";
	if ($debito > 0 && $aplicado >= $debito) {
		$estado = "asignado_total";
	} else if ($aplicado > 0) {
		$estado = "asignado_parcial";
	}
	$stmt = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET estado=? WHERE id_movimiento=? AND tipo_movimiento='debito'");
	$stmt->bind_param("si", $estado, $id_movimiento);
	if (!$stmt->execute()) {
		throw new Exception($stmt->error);
	}
	return array("estado" => $estado, "aplicado" => $aplicado, "saldo" => max(0, $debito - $aplicado), "debito" => $debito);
}

function ueno_marcar_gasto_si_conciliado($mysqli, $idgastos, $movimiento)
{
	$gasto = ueno_buscar_gasto_egreso_por_id($mysqli, $idgastos, false);
	$monto = (int)$gasto["monto"];
	$conciliado = (int)$gasto["monto_conciliado_ueno"];
	if ($monto > 0 && $conciliado >= $monto) {
		$banco = ueno_banco_nombre(isset($movimiento["banco_codigo"]) ? $movimiento["banco_codigo"] : "UENO");
		$nrocuenta = isset($movimiento["cuenta"]) ? $movimiento["cuenta"] : "";
		$nroboleta = isset($movimiento["nro_comprobante"]) ? $movimiento["nro_comprobante"] : "";
		$estado = "Activo";
		$stmt = $mysqli->prepare("UPDATE gastos SET estado=?, banco=?, nrocuenta=?, nroboleta=? WHERE idgastos=? AND estado!='Inactivo'");
		$stmt->bind_param("ssssi", $estado, $banco, $nrocuenta, $nroboleta, $idgastos);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$gasto = ueno_buscar_gasto_egreso_por_id($mysqli, $idgastos, false);
	}
	return $gasto;
}

function ueno_restaurar_gasto_si_reversion($mysqli, $idgastos)
{
	$gasto = ueno_buscar_gasto_egreso_por_id($mysqli, $idgastos, false, false);
	$monto = (int)$gasto["monto"];
	$conciliado = (int)$gasto["monto_conciliado_ueno"];
	$banco = strtolower(trim((string)$gasto["banco"]));
	if ($monto > 0 && $conciliado < $monto && $gasto["estado"] == "Activo" && in_array($banco, array("ueno", "banco familiar", "familiar"), true)) {
		$estado = "pendiente";
		$stmt = $mysqli->prepare("UPDATE gastos SET estado=? WHERE idgastos=? AND estado='Activo'");
		$stmt->bind_param("si", $estado, $idgastos);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
	}
}

function ueno_buscar_contexto_gasto_egreso($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERCONCILIACIONEGRESOUENO", "CONCILIAREGRESOUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_egreso_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_24062026_conciliacion_egresos_ueno.sql"));
	}
	try {
		$gasto = ueno_buscar_gasto_egreso_por_id($mysqli, ueno_post("idgastos"));
		ueno_requerir_local_gasto($mysqli, $usuario, $gasto["cod_local"]);
		$gastoJson = ueno_gasto_egreso_json($gasto);
		$asignaciones = ueno_tabla_asignaciones_gasto($mysqli, $gasto["idgastos"], $usuario);
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "gasto" => $gastoJson, "asignaciones" => $asignaciones));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_tabla_egresos_bancarios_disponibles($mysqli, $filtros)
{
	$condicion = " AND mv.tipo_movimiento='debito' AND mv.importe_debito>0
		AND LOWER(TRIM(IFNULL(mv.estado,''))) IN ('registrado','disponible','asignado_parcial')";
	if ($filtros["fecha_desde"] != "") {
		$condicion .= " AND mv.fecha_confirmacion>='" . $mysqli->real_escape_string($filtros["fecha_desde"]) . "'";
	}
	if ($filtros["fecha_hasta"] != "") {
		$condicion .= " AND mv.fecha_confirmacion<='" . $mysqli->real_escape_string($filtros["fecha_hasta"]) . "'";
	}
	if ($filtros["comprobante"] != "") {
		$condicion .= " AND mv.nro_comprobante LIKE '%" . $mysqli->real_escape_string($filtros["comprobante"]) . "%'";
	}
	if ($filtros["descripcion"] != "") {
		$texto = $mysqli->real_escape_string($filtros["descripcion"]);
		$condicion .= " AND (mv.descripcion LIKE '%$texto%' OR mv.concepto LIKE '%$texto%')";
	}
	if ($filtros["cuenta"] != "") {
		$condicion .= " AND mv.cuenta LIKE '%" . $mysqli->real_escape_string($filtros["cuenta"]) . "%'";
	}
	$monto = ueno_monto($filtros["monto"]);

	$sql = "SELECT mv.id_movimiento, mv.cuenta, mv.fecha_confirmacion, mv.fecha_transaccion, mv.nro_comprobante,
		mv.descripcion, mv.concepto, mv.importe_debito, mv.estado,
		IFNULL((SELECT SUM(umg.monto_aplicado) FROM ueno_movimiento_gasto umg WHERE umg.id_movimiento=mv.id_movimiento AND umg.estado='activo'),0) AS monto_asignado_gasto
		FROM ueno_movimiento_bancario mv
		WHERE mv.id_movimiento!='0' $condicion
		ORDER BY mv.fecha_confirmacion DESC, mv.id_movimiento DESC
		LIMIT 400";
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}
	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$debito = (int)$row["importe_debito"];
		$asignado = (int)$row["monto_asignado_gasto"];
		$saldo = max(0, $debito - $asignado);
		$estadoConciliacion = ueno_estado_conciliacion_monto($debito, $asignado);
		if ($monto > 0 && $saldo < $monto) {
			continue;
		}
		if ($filtros["estado_conciliacion"] != "" && $estadoConciliacion != $filtros["estado_conciliacion"]) {
			continue;
		}
		if ($saldo <= 0 && $filtros["mostrar_todos"] != "true") {
			continue;
		}
		$total++;
		$datos = array(
			"id_movimiento" => (int)$row["id_movimiento"],
			"cuenta" => ueno_from_db($row["cuenta"]),
			"fecha_confirmacion" => ueno_from_db($row["fecha_confirmacion"]),
			"fecha_transaccion" => ueno_from_db($row["fecha_transaccion"]),
			"nro_comprobante" => ueno_from_db($row["nro_comprobante"]),
			"descripcion" => ueno_from_db($row["descripcion"]),
			"concepto" => ueno_from_db($row["concepto"]),
			"importe_debito" => $debito,
			"importe_debito_fmt" => ueno_numero($debito),
			"monto_asignado" => $asignado,
			"monto_asignado_fmt" => ueno_numero($asignado),
			"saldo_disponible" => $saldo,
			"saldo_disponible_fmt" => ueno_numero($saldo),
			"estado_conciliacion" => $estadoConciliacion,
			"estado_conciliacion_texto" => ueno_estado_conciliacion_visual($estadoConciliacion)
		);
		$datos_js = htmlspecialchars(json_encode($datos), ENT_QUOTES, 'UTF-8');
		$accion = $saldo > 0
			? "<button type='button' class='btn4 ueno-row-action ueno-row-action--available' onclick='conciliarEgresoUenoSeleccionarBanco(" . $datos_js . ")'>Seleccionar</button>"
			: "<button type='button' class='btn4 ueno-row-action ueno-row-action--trace' onclick='conciliarEgresoUenoVerAsignacionesBanco(" . (int)$row["id_movimiento"] . ")'>Ver asignaciones</button>";
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName conciliacion-egreso-banco-row' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:9%'>" . ueno_escape_html($row["fecha_confirmacion"]) . "</td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["cuenta"]) . "</td>"
			. "<td style='width:13%'>" . ueno_escape_html($row["nro_comprobante"]) . "</td>"
			. "<td style='width:22%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
			. "<td style='width:11%;text-align:right'>" . ueno_numero($debito) . "</td>"
			. "<td style='width:11%;text-align:right'>" . ueno_numero($asignado) . "</td>"
			. "<td style='width:11%;text-align:right'>" . ueno_numero($saldo) . "</td>"
			. "<td style='width:8%'><span class='ueno-status-badge ueno-status-badge--" . strtolower($estadoConciliacion) . "'>" . ueno_estado_conciliacion_visual($estadoConciliacion) . "</span></td>"
			. "<td style='width:6%;text-align:center'>" . $accion . "</td>"
			. "</tr></table>";
	}
	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr><td style='width:100%;text-align:center'>Sin egresos bancarios disponibles para los filtros seleccionados.</td></tr></table>";
	}
	return array("html" => $html, "total" => $total);
}

function ueno_buscar_egresos_bancarios_disponibles($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERCONCILIACIONEGRESOUENO", "CONCILIAREGRESOUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_egreso_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_24062026_conciliacion_egresos_ueno.sql"));
	}
	try {
		$tabla = ueno_tabla_egresos_bancarios_disponibles($mysqli, array(
			"fecha_desde" => ueno_fecha(isset($_POST["fecha_desde"]) ? $_POST["fecha_desde"] : ""),
			"fecha_hasta" => ueno_fecha(isset($_POST["fecha_hasta"]) ? $_POST["fecha_hasta"] : ""),
			"comprobante" => ueno_post("comprobante"),
			"descripcion" => ueno_post("descripcion"),
			"monto" => isset($_POST["monto"]) ? $_POST["monto"] : "",
			"cuenta" => ueno_post("cuenta"),
			"estado_conciliacion" => ueno_post("estado_conciliacion"),
			"mostrar_todos" => ueno_post("mostrar_todos")
		));
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "2" => $tabla["html"], "3" => $tabla["total"]));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_buscar_gastos_pendientes_egreso($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERCONCILIACIONEGRESOUENO", "CONCILIAREGRESOUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_egreso_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_24062026_conciliacion_egresos_ueno.sql"));
	}
	try {
		$texto = ueno_post("texto");
		$cod_motivo = ueno_post("cod_motivo");
		$cod_local = ueno_resolver_filtro_local($mysqli, $usuario, ueno_post("cod_local"));
		$condicion = " AND LOWER(TRIM(g.tipo))='egreso' AND g.monto>0 AND LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado') AND LOWER(TRIM(IFNULL(m.estado,'')))='activo'";
		if ($texto != "") {
			$textoSql = $mysqli->real_escape_string($texto);
			$condicion .= " AND (g.motivo LIKE '%$textoSql%' OR m.descripcion LIKE '%$textoSql%' OR g.idgastos='$textoSql')";
		}
		if ($cod_motivo != "" && is_numeric($cod_motivo)) {
			$condicion .= " AND g.cod_motivoIngresoEgresoFK=" . intval($cod_motivo);
		}
		if ((int)$cod_local > 0) {
			$condicion .= " AND g.cod_local=" . (int)$cod_local;
		}
		$sql = "SELECT g.idgastos, g.monto, g.motivo AS descripcion, g.fecha, g.estado, g.tipo, g.cod_local,
			g.cod_motivoIngresoEgresoFK, g.cod_proyecto_gastoFK, g.cod_gasto_padre,
			IFNULL(m.descripcion,'') AS concepto, IFNULL(m.categoria,'') AS categoria, IFNULL(l.Nombre,'') AS local_nombre,
			IFNULL((SELECT SUM(umg.monto_aplicado) FROM ueno_movimiento_gasto umg WHERE umg.idgastos=g.idgastos AND umg.estado='activo'),0) AS monto_conciliado_ueno
			FROM gastos g
			LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
			LEFT JOIN local l ON l.cod_local=g.cod_local
			WHERE g.idgastos!='0' $condicion
			ORDER BY g.fecha ASC, g.idgastos DESC
			LIMIT 250";
		$result = $mysqli->query($sql);
		if (!$result) {
			throw new Exception($mysqli->error);
		}
		$html = "";
		$total = 0;
		$styleName = "tableRegistroSearch";
		while ($row = mysqli_fetch_assoc($result)) {
			$json = ueno_gasto_egreso_json($row);
			if ((int)$json["pendiente"] <= 0) {
				continue;
			}
			$total++;
			$datos_js = htmlspecialchars(json_encode($json), ENT_QUOTES, 'UTF-8');
			$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
			$html .= "<table class='$styleName conciliacion-egreso-gasto-row' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
				. "<td style='width:7%;text-align:center'>" . (int)$row["idgastos"] . "</td>"
				. "<td style='width:13%'>" . ueno_escape_html(ueno_categoria_flujo_texto($row["categoria"])) . "</td>"
				. "<td style='width:18%'>" . ueno_escape_html($row["concepto"]) . "</td>"
				. "<td style='width:22%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
				. "<td style='width:10%'>" . ueno_escape_html($row["fecha"]) . "</td>"
				. "<td style='width:10%;text-align:right'>" . ueno_numero($row["monto"]) . "</td>"
				. "<td style='width:10%;text-align:right'>" . ueno_numero($json["pendiente"]) . "</td>"
				. "<td style='width:10%;text-align:center'><button type='button' class='btn4 ueno-row-action ueno-row-action--available' onclick='conciliarEgresoUenoAgregarGastoDistribucion(" . $datos_js . ")'>Agregar</button></td>"
				. "</tr></table>";
		}
		if ($html == "") {
			$mensaje = (int)$cod_local > 0
				? "No existen movimientos pendientes para conciliar en este concepto dentro del local seleccionado. Puede buscar en todos los locales o registrar primero el gasto utilizando el boton +."
				: "No existen movimientos pendientes para conciliar en este concepto. Registre primero el gasto utilizando el boton +.";
			$accionTodosLocales = ((int)$cod_local > 0 && ((string)$usuario === "2" || ueno_usuario_tiene_permiso($usuario, "CAMBIARLOCAL")))
				? " <button type='button' class='btn4 ueno-btn-secondary' onclick='conciliarEgresoUenoBuscarGastosTodosLocales()'>Buscar en todos los locales</button>"
				: "";
			$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr><td style='width:100%;text-align:center'>" . ueno_escape_html($mensaje) . $accionTodosLocales . "</td></tr></table>";
		}
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "2" => $html, "3" => $total));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_guardar_conciliacion_egreso($usuario)
{
	ueno_requerir_permiso($usuario, "CONCILIAREGRESOUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_egreso_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_24062026_conciliacion_egresos_ueno.sql"));
	}
	$id_movimiento = (int)ueno_post("id_movimiento");
	$observacion = ueno_post("observacion");
	$distribucion = json_decode(isset($_POST["distribucion"]) ? $_POST["distribucion"] : "[]", true);
	if (!is_array($distribucion) || count($distribucion) == 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "Agregue al menos un gasto a la distribucion"));
	}
	if ($id_movimiento <= 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "Seleccione un egreso bancario valido"));
	}
	$items = array();
	foreach ($distribucion as $item) {
		$idgastos = isset($item["idgastos"]) ? (int)$item["idgastos"] : 0;
		$monto = isset($item["monto"]) ? ueno_monto($item["monto"]) : 0;
		if ($idgastos <= 0 || $monto <= 0) {
			mysqli_close($mysqli);
			ueno_json(array("1" => "error", "2" => "La distribucion contiene gastos o montos invalidos"));
		}
		if (!isset($items[$idgastos])) {
			$items[$idgastos] = 0;
		}
		$items[$idgastos] += $monto;
	}
	ksort($items, SORT_NUMERIC);
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
	if (!$mysqli->begin_transaction()) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "No se pudo iniciar la conciliacion segura"));
	}
	try {
		$resultMov = $mysqli->query("SELECT id_movimiento, banco_codigo, cuenta, nro_comprobante, tipo_movimiento, importe_debito, importe_credito, descripcion, estado
			FROM ueno_movimiento_bancario
			WHERE id_movimiento=$id_movimiento
			LIMIT 1 FOR UPDATE");
		if (!$resultMov || $resultMov->num_rows == 0) {
			throw new Exception("No se encontro el egreso bancario seleccionado");
		}
		$movimiento = $resultMov->fetch_assoc();
		if ($movimiento["tipo_movimiento"] != "debito" || (int)$movimiento["importe_debito"] <= 0 || (int)$movimiento["importe_credito"] > 0) {
			throw new Exception("La conciliacion de egresos solo admite movimientos bancarios de tipo debito");
		}
		if (!ueno_estado_debito_disponible_para_egreso($movimiento["estado"])) {
			throw new Exception("El egreso bancario ya no se encuentra disponible para nuevas asignaciones");
		}
		$debito = (int)$movimiento["importe_debito"];
		// Orden global: movimiento bancario -> gastos por id -> vinculos. Primero se
		// fijan todos los gastos; ningun INSERT se ejecuta con la lista incompleta.
		$gastosBloqueados = array();
		foreach ($items as $idgastos => $montoAplicar) {
			$gasto = ueno_buscar_gasto_egreso_por_id($mysqli, $idgastos, true);
			ueno_requerir_local_gasto($mysqli, $usuario, $gasto["cod_local"]);
			$gastosBloqueados[$idgastos] = $gasto;
		}
		$idsGastosSql = implode(",", array_keys($gastosBloqueados));
		$resultLinks = $mysqli->query("SELECT id FROM ueno_movimiento_gasto
			WHERE estado='activo' AND (id_movimiento=$id_movimiento OR idgastos IN ($idsGastosSql))
			ORDER BY id FOR UPDATE");
		if (!$resultLinks) {
			throw new Exception("No se pudieron bloquear las asignaciones bancarias existentes");
		}
		$aplicadoBanco = ueno_scalar($mysqli, "SELECT IFNULL(SUM(monto_aplicado),0) FROM ueno_movimiento_gasto WHERE id_movimiento=$id_movimiento AND estado='activo'");
		$saldoBanco = $debito - $aplicadoBanco;
		$totalAsignar = array_sum($items);
		if ($totalAsignar <= 0 || $totalAsignar > $saldoBanco) {
			throw new Exception("El monto a asignar supera el saldo disponible del egreso bancario");
		}
		$resumenGastos = array();
		$saldoBancoRestanteAuditoria = $saldoBanco;
		foreach ($items as $idgastos => $montoAplicar) {
			$gasto = $gastosBloqueados[$idgastos];
			$gasto["monto_conciliado_ueno"] = ueno_scalar($mysqli, "SELECT IFNULL(SUM(monto_aplicado),0) FROM ueno_movimiento_gasto WHERE idgastos=" . (int)$idgastos . " AND estado='activo'");
			$saldoGasto = (int)$gasto["monto"] - (int)$gasto["monto_conciliado_ueno"];
			if ($saldoGasto <= 0) {
				throw new Exception("El gasto #" . $idgastos . " ya no tiene saldo pendiente");
			}
			if ($montoAplicar > $saldoGasto) {
				throw new Exception("El monto asignado supera el saldo pendiente del gasto #" . $idgastos);
			}
			$stmt = $mysqli->prepare("INSERT INTO ueno_movimiento_gasto (id_movimiento, idgastos, monto_aplicado, usuario_asocio, estado, observacion) VALUES (?,?,?,?,?,?)");
			$estadoLink = "activo";
			$stmt->bind_param("iiiiss", $id_movimiento, $idgastos, $montoAplicar, $usuario, $estadoLink, $observacion);
			if (!$stmt->execute()) {
				throw new Exception($stmt->error);
			}
			$idAsignacion = $stmt->insert_id;
			ueno_auditar_conciliacion(
				$mysqli,
				"CONCILIAR_EGRESO",
				"ueno_movimiento_gasto",
				$idAsignacion,
				"",
				$id_movimiento,
				ueno_estado_conciliacion_visual(ueno_estado_conciliacion_monto($gasto["monto"], $gasto["monto_conciliado_ueno"])),
				ueno_estado_conciliacion_visual(ueno_estado_conciliacion_monto($gasto["monto"], (int)$gasto["monto_conciliado_ueno"] + $montoAplicar)),
				$montoAplicar,
				$usuario,
				$observacion,
				array("idgastos" => $idgastos, "saldo_gasto_anterior" => $saldoGasto, "saldo_banco_anterior" => $saldoBancoRestanteAuditoria)
			);
			$saldoBancoRestanteAuditoria -= $montoAplicar;
			$gastoActualizado = ueno_marcar_gasto_si_conciliado($mysqli, $idgastos, $movimiento);
			$resumenGastos[] = ueno_gasto_egreso_json($gastoActualizado);
		}
		$estadoBanco = ueno_actualizar_estado_movimiento_debito($mysqli, $id_movimiento);
		if (!$mysqli->commit()) {
			throw new Exception("No se pudo confirmar la conciliacion de egresos");
		}
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"2" => "Conciliacion registrada correctamente.",
			"total_asignado" => ueno_numero($totalAsignar),
			"saldo_bancario_restante" => ueno_numero($estadoBanco["saldo"]),
			"saldo_bancario_restante_num" => $estadoBanco["saldo"],
			"estado_bancario" => ueno_estado_movimiento_debito_texto($estadoBanco["debito"], $estadoBanco["aplicado"]),
			"gastos" => $resumenGastos
		));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_revertir_conciliacion_egreso($usuario)
{
	ueno_requerir_permiso($usuario, "REVERTIRCONCILIACIONEGRESOUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_egreso_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_24062026_conciliacion_egresos_ueno.sql"));
	}
	$id = (int)ueno_post("id_asignacion");
	$motivo = ueno_post("motivo");
	if ($id <= 0 || trim($motivo) == "") {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "Indique la asignacion y el motivo de reversion"));
	}
	$resultPre = $mysqli->query("SELECT id_movimiento,idgastos FROM ueno_movimiento_gasto WHERE id=$id LIMIT 1");
	$pre = $resultPre ? $resultPre->fetch_assoc() : null;
	if (!$pre) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "No se encontro la asignacion seleccionada"));
	}
	$idMovimientoPre = (int)$pre["id_movimiento"];
	$idGastoPre = (int)$pre["idgastos"];
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
	if (!$mysqli->begin_transaction()) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "No se pudo iniciar la reversion segura"));
	}
	try {
		// Mismo orden que la conciliacion manual: banco -> gasto -> vinculo.
		$resultMov = $mysqli->query("SELECT id_movimiento,nro_comprobante,cuenta FROM ueno_movimiento_bancario WHERE id_movimiento=$idMovimientoPre LIMIT 1 FOR UPDATE");
		$movimiento = $resultMov ? $resultMov->fetch_assoc() : null;
		if (!$movimiento) {
			throw new Exception("No se encontro el movimiento bancario de la asignacion");
		}
		$gasto = ueno_buscar_gasto_egreso_por_id($mysqli, $idGastoPre, true, false);
		ueno_requerir_local_gasto($mysqli, $usuario, $gasto["cod_local"]);
		$result = $mysqli->query("SELECT * FROM ueno_movimiento_gasto
			WHERE id=$id AND id_movimiento=$idMovimientoPre AND idgastos=$idGastoPre AND estado='activo'
			LIMIT 1 FOR UPDATE");
		if (!$result || $result->num_rows == 0) {
			throw new Exception("No se encontro una asignacion activa para revertir");
		}
		$row = $result->fetch_assoc();
		$row["nro_comprobante"] = $movimiento["nro_comprobante"];
		$row["cuenta"] = $movimiento["cuenta"];
		$stmt = $mysqli->prepare("UPDATE ueno_movimiento_gasto SET estado='revertido', usuario_reversion=?, fecha_hora_reversion=NOW(), motivo_reversion=? WHERE id=? AND estado='activo'");
		$stmt->bind_param("isi", $usuario, $motivo, $id);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		ueno_actualizar_estado_movimiento_debito($mysqli, $row["id_movimiento"]);
		ueno_restaurar_gasto_si_reversion($mysqli, $row["idgastos"]);
		ueno_auditar_conciliacion(
			$mysqli,
			"REVERTIR_EGRESO",
			"ueno_movimiento_gasto",
			$id,
			"",
			$row["id_movimiento"],
			"activo",
			"revertido",
			$row["monto_aplicado"],
			$usuario,
			$motivo,
			array("idgastos" => $row["idgastos"], "nro_comprobante" => ueno_from_db($row["nro_comprobante"]))
		);
		if (!$mysqli->commit()) {
			throw new Exception("No se pudo confirmar la reversion de la asignacion");
		}
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "2" => "Asignacion revertida correctamente."));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_buscar_asignaciones_egreso_banco($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERCONCILIACIONEGRESOUENO", "CONCILIAREGRESOUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_egreso_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_24062026_conciliacion_egresos_ueno.sql"));
	}
	$id_movimiento = (int)ueno_post("id_movimiento");
	if ($id_movimiento <= 0) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => "Seleccione un egreso bancario valido"));
	}
	try {
		$codLocalFiltro = ueno_resolver_filtro_local($mysqli, $usuario, "");
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
	$condicionLocal = $codLocalFiltro > 0 ? " AND g.cod_local=" . (int)$codLocalFiltro : "";
	$sql = "SELECT umg.id, umg.monto_aplicado, umg.fecha_hora_asociacion, umg.estado, umg.usuario_asocio,
		g.idgastos, g.motivo AS descripcion, IFNULL(m.descripcion,'') AS concepto, IFNULL(l.Nombre,'') AS local_nombre
		FROM ueno_movimiento_gasto umg
		INNER JOIN gastos g ON g.idgastos=umg.idgastos
		LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
		LEFT JOIN local l ON l.cod_local=g.cod_local
		WHERE umg.id_movimiento=$id_movimiento $condicionLocal
		ORDER BY umg.estado ASC, umg.id DESC";
	$result = $mysqli->query($sql);
	if (!$result) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $mysqli->error));
	}
	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$total++;
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:8%;text-align:center'>" . (int)$row["idgastos"] . "</td>"
			. "<td style='width:20%'>" . ueno_escape_html($row["concepto"]) . "</td>"
			. "<td style='width:26%'>" . ueno_escape_html($row["descripcion"]) . "</td>"
			. "<td style='width:14%'>" . ueno_escape_html($row["local_nombre"]) . "</td>"
			. "<td style='width:12%;text-align:right'>" . ueno_numero($row["monto_aplicado"]) . "</td>"
			. "<td style='width:10%;text-align:center'>" . ueno_escape_html($row["usuario_asocio"]) . "</td>"
			. "<td style='width:10%;text-align:center'>" . ueno_escape_html($row["estado"]) . "</td>"
			. "</tr></table>";
	}
	if ($html == "") {
		$html = "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr><td style='width:100%;text-align:center'>Este egreso bancario no tiene asignaciones de gastos.</td></tr></table>";
	}
	mysqli_close($mysqli);
	ueno_json(array("1" => "exito", "2" => $html, "3" => $total));
}

function ueno_obtener_movimiento_credito_para_migracion($mysqli, $id_movimiento, $bloquear = false)
{
	$id_movimiento = (int)$id_movimiento;
	if ($id_movimiento <= 0) {
		throw new Exception("Seleccione un movimiento bancario valido.");
	}
	$forUpdate = $bloquear ? " FOR UPDATE" : "";
	$sql = "SELECT id_movimiento, banco_codigo, cuenta, fecha_confirmacion, fecha_transaccion, nro_comprobante,
		descripcion, concepto, tipo_movimiento, importe_credito, importe_debito, monto_disponible, estado
		FROM ueno_movimiento_bancario
		WHERE id_movimiento=$id_movimiento
		LIMIT 1$forUpdate";
	$result = $mysqli->query($sql);
	if (!$result || $result->num_rows == 0) {
		throw new Exception("No se encontro el movimiento bancario seleccionado.");
	}
	$movimiento = $result->fetch_assoc();
	if ($movimiento["tipo_movimiento"] != "credito" || (int)$movimiento["importe_credito"] <= 0) {
		throw new Exception("La conciliacion de monto migrado solo admite creditos bancarios.");
	}
	if ((int)$movimiento["monto_disponible"] <= 0) {
		throw new Exception("El movimiento bancario ya no tiene saldo disponible.");
	}
	if ((int)$movimiento["monto_disponible"] != (int)$movimiento["importe_credito"]) {
		throw new Exception("La primera etapa solo permite conciliar creditos completos, sin aplicaciones parciales.");
	}
	return $movimiento;
}

function ueno_migracion_caja_query_base($monto, $fecha_banco)
{
	$monto = (int)$monto;
	$fecha_banco = ueno_fecha($fecha_banco);
	$rango = ueno_migracion_rango_fechas($fecha_banco);
	$condicionFecha = "AND 1=0";
	if ($rango["inicio"] != "" && $rango["fin"] != "") {
		$condicionFecha = "AND mc.fecha BETWEEN '" . $rango["inicio"] . "' AND '" . $rango["fin"] . "'";
	}
	return "SELECT mc.idmigrar_caja, mc.obs, mc.fecha, mc.monto, mc.cod_caja_desdeFK, mc.cod_caja_hastaFK,
		mc.estado, mc.tipo, mc.cod_usuRecibeFK, mc.cod_UsuEnviaFK,
		IFNULL(per_envia.nombre_persona,'') AS usuario_envia,
		IFNULL(per_recibe.nombre_persona,'') AS usuario_recibe,
		IFNULL(ar.fechacierre, ar.fechaapertura) AS fecha_cierre_caja,
		IFNULL(ar.lote,'') AS lote,
		IFNULL(l.Nombre,'') AS local_nombre,
		IFNULL(cj.cajanro,'') AS caja_nombre
		FROM migrar_caja mc
		LEFT JOIN arqueocaja ar ON ar.idarqueocaja=mc.cod_caja_desdeFK
		LEFT JOIN local l ON l.cod_local=ar.cod_local
		LEFT JOIN caja cj ON cj.idcaja=ar.caja_idcaja
		LEFT JOIN persona per_envia ON per_envia.cod_persona=mc.cod_UsuEnviaFK
		LEFT JOIN persona per_recibe ON per_recibe.cod_persona=mc.cod_usuRecibeFK
		WHERE mc.monto=$monto
		AND mc.monto>0
		AND IFNULL(mc.estado,'')!='Inactivo'
		AND mc.fecha IS NOT NULL
		$condicionFecha
		AND NOT EXISTS (
			SELECT 1
			FROM ueno_movimiento_migracion_caja umm
			WHERE umm.idmigrar_caja=mc.idmigrar_caja
			AND umm.estado='activo'
		)";
}

function ueno_migracion_caja_por_id($mysqli, $idmigrar_caja, $monto, $fecha_banco, $bloquear = false)
{
	$idmigrar_caja = (int)$idmigrar_caja;
	if ($idmigrar_caja <= 0) {
		throw new Exception("Seleccione un monto migrado valido.");
	}
	$forUpdate = $bloquear ? " FOR UPDATE" : "";
	$sql = ueno_migracion_caja_query_base($monto, $fecha_banco) . " AND mc.idmigrar_caja=$idmigrar_caja LIMIT 1$forUpdate";
	$result = $mysqli->query($sql);
	if (!$result || $result->num_rows == 0) {
		throw new Exception("No se encontro un monto migrado pendiente que coincida exactamente con este credito.");
	}
	return $result->fetch_assoc();
}

function ueno_html_sugerencias_migracion($mysqli, $movimiento, $usuario)
{
	$monto = (int)$movimiento["monto_disponible"];
	$fecha_banco = ueno_fecha($movimiento["fecha_confirmacion"]);
	if ($monto <= 0 || $fecha_banco == "") {
		return array("html" => "", "total" => 0);
	}
	$sql = ueno_migracion_caja_query_base($monto, $fecha_banco) . "
		ORDER BY ABS(DATEDIFF('$fecha_banco', mc.fecha)) ASC, mc.idmigrar_caja DESC
		LIMIT 20";
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception($mysqli->error);
	}
	$html = "";
	$total = 0;
	$styleName = "tableRegistroSearch";
	while ($row = mysqli_fetch_assoc($result)) {
		$total++;
		$advertencia = ueno_migracion_advertencia($row["fecha"], $fecha_banco);
		$requiereAdvertencia = $advertencia["requiere"] ? "SI" : "NO";
		$claseNota = $advertencia["requiere"] ? "ueno-migration-warning" : "ueno-migration-ok";
		$contextoCaja = trim((string)$row["local_nombre"]);
		if (trim((string)$row["caja_nombre"]) != "") {
			$contextoCaja .= ($contextoCaja != "" ? " / " : "") . "Caja " . trim((string)$row["caja_nombre"]);
		}
		if (trim((string)$row["lote"]) != "") {
			$contextoCaja .= ($contextoCaja != "" ? " / " : "") . "Lote " . trim((string)$row["lote"]);
		}
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName ueno-migration-suggestion-row' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:8%;text-align:center'>" . (int)$row["idmigrar_caja"] . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["fecha"]) . "</td>"
			. "<td style='width:14%;text-align:right'>" . ueno_numero($row["monto"]) . "</td>"
			. "<td style='width:16%'>" . ueno_escape_html($row["usuario_envia"]) . "</td>"
			. "<td style='width:20%'>" . ueno_escape_html($contextoCaja) . "</td>"
			. "<td style='width:18%'><span class='" . $claseNota . "'>" . ueno_escape_html($advertencia["texto"]) . "</span></td>"
			. "<td style='width:14%;text-align:center'><button type='button' class='btn4 ueno-row-action ueno-row-action--internal' onclick='uenoConfirmarConciliacionMigracion("
			. (int)$movimiento["id_movimiento"] . "," . (int)$row["idmigrar_caja"] . ",\"" . $requiereAdvertencia . "\")'>Conciliar migracion</button></td>"
			. "</tr></table>";
	}
	if ($html == "") {
		$html = "<div class='ueno-migration-empty'>No se encontraron montos migrados pendientes con el mismo importe exacto.</div>";
	}
	return array("html" => $html, "total" => $total);
}

function ueno_buscar_sugerencias_migracion($usuario)
{
	ueno_requerir_algun_permiso($usuario, array("VERMIGRACIONUENO", "CONCILIARMIGRACIONUENO"));
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli) || !ueno_tabla_existe($mysqli, "migrar_caja") || !ueno_tabla_existe($mysqli, "ueno_movimiento_deposito")) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_11072026_depositos_faraone_ueno.sql"));
	}
	try {
		$movimiento = ueno_obtener_movimiento_credito_para_migracion($mysqli, ueno_post("id_movimiento"));
		$sugerencias = ueno_html_sugerencias_deposito($mysqli, $movimiento);
		mysqli_close($mysqli);
		ueno_json(array("1" => "exito", "2" => $sugerencias["html"], "3" => $sugerencias["total"]));
	} catch (Exception $e) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
}

function ueno_html_sugerencias_deposito($mysqli, $movimiento)
{
	$monto = (int)$movimiento["monto_disponible"];
	$fechaBanco = ueno_fecha($movimiento["fecha_confirmacion"]);
	$rango = ueno_migracion_rango_fechas($fechaBanco);
	if ($monto <= 0 || $fechaBanco == "" || $rango["inicio"] == "") {
		return array("html" => "", "total" => 0);
	}
	$inicio = date("Y-m-d", strtotime($fechaBanco . " -2 days"));
	if ($inicio < ueno_migracion_fecha_inicio_sistema()) { $inicio = ueno_migracion_fecha_inicio_sistema(); }
	$fin = $fechaBanco;
	$filtroConciliacionAnterior = ueno_tabla_existe($mysqli, "ueno_movimiento_migracion_caja")
		? "AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_migracion_caja umm WHERE umm.idmigrar_caja=mc.idmigrar_caja AND umm.estado='activo')"
		: "";
	$sql = "SELECT 'gasto' AS origen_tipo, g.idgastos AS origen_id, g.fecha, g.monto,
		IFNULL(g.motivo,'') AS detalle, IFNULL(l.Nombre,'') AS local_nombre,
		IFNULL(p.nombre_persona,'') AS usuario_nombre
		FROM gastos g
		INNER JOIN motivos_ingreso_egreso mie ON mie.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
		LEFT JOIN local l ON l.cod_local=g.cod_local
		LEFT JOIN persona p ON p.cod_persona=g.cod_usuario
		WHERE UPPER(TRIM(mie.descripcion))='DEPOSITO BANCARIO - FARAONE CAPITAL S.A.'
		AND LOWER(TRIM(g.tipo)) IN ('egreso','deposito') AND LOWER(TRIM(g.estado)) NOT IN ('inactivo','anulado','baja')
		AND g.monto=$monto AND DATE(g.fecha) BETWEEN '$inicio' AND '$fin'
		AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_deposito ud WHERE ud.origen_tipo='gasto' AND ud.origen_id=g.idgastos AND ud.estado='activo')
		UNION ALL
		SELECT 'migrar_caja', mc.idmigrar_caja, mc.fecha, mc.monto,
		CONCAT('Reclasificado: deposito a Faraone Capital S.A. / ',IFNULL(mc.obs,'')),
		IFNULL(l.Nombre,''), IFNULL(pe.nombre_persona,'')
		FROM migrar_caja mc
		INNER JOIN persona pr ON pr.cod_persona=mc.cod_usuRecibeFK
		LEFT JOIN arqueocaja ar ON ar.idarqueocaja=mc.cod_caja_desdeFK
		LEFT JOIN local l ON l.cod_local=ar.cod_local
		LEFT JOIN persona pe ON pe.cod_persona=mc.cod_UsuEnviaFK
		WHERE mc.fecha>='2026-06-19 00:00:00' AND UPPER(TRIM(pr.nombre_persona)) LIKE '%CARLOS%FARAONE%'
		AND IFNULL(mc.estado,'')!='Inactivo' AND mc.monto=$monto AND DATE(mc.fecha) BETWEEN '$inicio' AND '$fin'
		$filtroConciliacionAnterior
		AND NOT EXISTS (SELECT 1 FROM ueno_movimiento_deposito ud WHERE ud.origen_tipo='migrar_caja' AND ud.origen_id=mc.idmigrar_caja AND ud.estado='activo')
		ORDER BY fecha ASC, origen_id ASC LIMIT 20";
	$result = $mysqli->query($sql);
	if (!$result) { throw new Exception($mysqli->error); }
	$html = "";
	$total = 0;
	while ($row = $result->fetch_assoc()) {
		$total++;
		$dias = ueno_dias_entre_fechas($row["fecha"], $fechaBanco);
		$control = $dias > 2 ? "Acreditacion demorada (" . $dias . " dias)." : ($dias < 0 ? "Credito anterior al deposito." : "Dentro del plazo (" . $dias . " dia/s)." );
		$html .= "<table class='tableRegistroSearch ueno-migration-suggestion-row' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>"
			. "<td style='width:8%;text-align:center'>" . (int)$row["origen_id"] . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["fecha"]) . "</td>"
			. "<td style='width:14%;text-align:right'>" . ueno_numero($row["monto"]) . "</td>"
			. "<td style='width:16%'>" . ueno_escape_html($row["usuario_nombre"]) . "</td>"
			. "<td style='width:20%'>" . ueno_escape_html($row["local_nombre"] . " / " . $row["detalle"]) . "</td>"
			. "<td style='width:18%'>" . ueno_escape_html($control) . "</td>"
			. "<td style='width:14%;text-align:center'><button type='button' class='btn4 ueno-row-action ueno-row-action--internal' onclick='uenoConfirmarConciliacionDeposito("
			. (int)$movimiento["id_movimiento"] . ",\"" . $row["origen_tipo"] . "\"," . (int)$row["origen_id"] . ")'>Conciliar deposito</button></td></tr></table>";
	}
	if ($html == "") { $html = "<div class='ueno-migration-empty'>No se encontraron depositos pendientes con el mismo importe exacto.</div>"; }
	return array("html" => $html, "total" => $total);
}

function ueno_conciliar_deposito($usuario)
{
	ueno_requerir_permiso($usuario, "CONCILIARMIGRACIONUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tabla_existe($mysqli, "ueno_movimiento_deposito")) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_11072026_depositos_faraone_ueno.sql"));
	}
	$idMovimiento = (int)ueno_post("id_movimiento");
	$origenTipo = ueno_post("origen_tipo");
	$origenId = (int)ueno_post("origen_id");
	if (!in_array($origenTipo, array("gasto", "migrar_caja")) || $origenId <= 0) {
		mysqli_close($mysqli); ueno_json(array("1" => "error", "2" => "Origen de deposito invalido."));
	}
	$mysqli->begin_transaction();
	try {
		$mov = ueno_obtener_movimiento_credito_para_migracion($mysqli, $idMovimiento, true);
		if ($origenTipo == 'gasto') {
			$stmtOrigen= $mysqli->prepare("SELECT g.idgastos,g.monto,g.tipo,g.estado,IFNULL(mie.descripcion,'') AS concepto
				FROM gastos g
				LEFT JOIN motivos_ingreso_egreso mie ON mie.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
				WHERE g.idgastos=? LIMIT 1 FOR UPDATE");
			if (!$stmtOrigen) { throw new Exception('No se pudo bloquear el deposito seleccionado.'); }
			$stmtOrigen->bind_param('i', $origenId);
			if (!$stmtOrigen->execute()) {
				$stmtOrigen->close();
				throw new Exception('No se pudo validar el deposito seleccionado.');
			}
			$origen= $stmtOrigen->get_result()->fetch_assoc();
			$stmtOrigen->close();
			if (!$origen
				|| strtoupper(trim((string)$origen['concepto'])) != 'DEPOSITO BANCARIO - FARAONE CAPITAL S.A.'
				|| !in_array(strtolower(trim((string)$origen['tipo'])), array('egreso','deposito'), true)
				|| in_array(strtolower(trim((string)$origen['estado'])), array('inactivo','anulado','baja'), true)
				|| (int)$origen['monto'] !== (int)$mov['monto_disponible']) {
				throw new Exception('El deposito cambio, ya no esta activo o no coincide con el credito Ueno. Actualice las sugerencias.');
			}
		}
		$sugerencias = ueno_html_sugerencias_deposito($mysqli, $mov);
		$patron = "uenoConfirmarConciliacionDeposito(" . $idMovimiento . ",\"" . $origenTipo . "\"," . $origenId . ")";
		if (strpos($sugerencias["html"], $patron) === false) { throw new Exception("El deposito ya no esta disponible o no coincide exactamente con el credito."); }
		$monto = (int)$mov["monto_disponible"];
		$obs = "Conciliacion de deposito a Faraone Capital S.A. (" . $origenTipo . " #" . $origenId . ")";
		$stmt = $mysqli->prepare("INSERT INTO ueno_movimiento_deposito (id_movimiento,origen_tipo,origen_id,monto_aplicado,usuario_asocio,fecha_hora_asociacion,estado,observacion) VALUES (?,?,?,?,?,NOW(),'activo',?)");
		$stmt->bind_param("isiiis", $idMovimiento, $origenTipo, $origenId, $monto, $usuario, $obs);
		if (!$stmt->execute()) { throw new Exception($stmt->error); }
		$stmtMov = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET monto_disponible=0, estado='asignado_total' WHERE id_movimiento=? AND monto_disponible=?");
		$stmtMov->bind_param("ii", $idMovimiento, $monto);
		if (!$stmtMov->execute() || $stmtMov->affected_rows != 1) { throw new Exception("El saldo Ueno cambio durante la conciliacion."); }
		ueno_auditar_conciliacion($mysqli, "CONCILIAR_DEPOSITO_FARAONE", "ueno_movimiento_deposito", $stmt->insert_id, "", $idMovimiento, $mov["estado"], "conciliado", $monto, $usuario, $obs, array("origen_tipo"=>$origenTipo,"origen_id"=>$origenId));
		$mysqli->commit(); mysqli_close($mysqli);
		ueno_json(array("1"=>"exito","2"=>"Deposito conciliado con el credito Ueno.","monto"=>ueno_numero($monto)));
	} catch (Exception $e) {
		$mysqli->rollback(); mysqli_close($mysqli); ueno_json(array("1"=>"error","2"=>$e->getMessage()));
	}
}

function ueno_conciliar_migracion_caja($usuario)
{
	ueno_requerir_permiso($usuario, "CONCILIARMIGRACIONUENO");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_migracion_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		ueno_json(array("1" => "tablasfaltantes", "2" => ueno_migracion_tabla_faltante_msg()));
	}
	$id_movimiento = (int)ueno_post("id_movimiento");
	$idmigrar_caja = (int)ueno_post("idmigrar_caja");
	$confirmar_advertencia = strtoupper(trim((string)ueno_post("confirmar_advertencia"))) == "SI";
	$observacion = ueno_post("observacion");
	$mysqli->begin_transaction();
	try {
		$movimiento = ueno_obtener_movimiento_credito_para_migracion($mysqli, $id_movimiento, true);
		$monto = (int)$movimiento["monto_disponible"];
		$migracion = ueno_migracion_caja_por_id($mysqli, $idmigrar_caja, $monto, $movimiento["fecha_confirmacion"], true);
		$advertencia = ueno_migracion_advertencia($migracion["fecha"], $movimiento["fecha_confirmacion"]);
		if ($advertencia["requiere"] && !$confirmar_advertencia) {
			throw new Exception($advertencia["texto"]);
		}
		if ($observacion == "") {
			$observacion = "Conciliacion interna por monto migrado #" . $idmigrar_caja;
		}
		$estadoLink = "activo";
		$stmt = $mysqli->prepare("INSERT INTO ueno_movimiento_migracion_caja
			(id_movimiento, idmigrar_caja, monto_aplicado, usuario_asocio, estado, observacion, advertencia)
			VALUES (?,?,?,?,?,?,?)");
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$stmt->bind_param("iiiisss", $id_movimiento, $idmigrar_caja, $monto, $usuario, $estadoLink, $observacion, $advertencia["texto"]);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$idAsignacion = $stmt->insert_id;
		$nuevoDisponible = 0;
		$estadoMovimiento = "asignado_total";
		$stmtMov = $mysqli->prepare("UPDATE ueno_movimiento_bancario SET monto_disponible=?, estado=? WHERE id_movimiento=? AND monto_disponible=?");
		if (!$stmtMov) {
			throw new Exception($mysqli->error);
		}
		$stmtMov->bind_param("isii", $nuevoDisponible, $estadoMovimiento, $id_movimiento, $monto);
		if (!$stmtMov->execute()) {
			throw new Exception($stmtMov->error);
		}
		if ($stmtMov->affected_rows <= 0) {
			throw new Exception("El saldo disponible del movimiento bancario cambio durante la conciliacion.");
		}
		ueno_auditar_conciliacion(
			$mysqli,
			"CONCILIAR_MIGRACION_CAJA",
			"ueno_movimiento_migracion_caja",
			$idAsignacion,
			"",
			$id_movimiento,
			$movimiento["estado"],
			"conciliado_interno",
			$monto,
			$usuario,
			$observacion,
			array(
				"idmigrar_caja" => $idmigrar_caja,
				"fecha_migracion" => ueno_from_db($migracion["fecha"]),
				"fecha_banco" => ueno_from_db($movimiento["fecha_confirmacion"]),
				"dias_diferencia" => $advertencia["dias"],
				"advertencia" => ueno_from_db($advertencia["texto"])
			)
		);
		$mysqli->commit();
		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"2" => "Monto migrado conciliado internamente.",
			"monto" => ueno_numero($monto),
			"id_movimiento" => $id_movimiento,
			"idmigrar_caja" => $idmigrar_caja
		));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		ueno_json(array("1" => "error", "2" => $e->getMessage()));
	}
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
		ueno_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar las actualizaciones de conciliacion bancaria, incluida actualizacion_08082026_conciliacion_bancaria_familiar.sql"));
	}

	$fecha_operativa = ueno_fecha(isset($_POST["fecha_operativa"]) ? $_POST["fecha_operativa"] : "");
	$fecha_bancaria = ueno_fecha(isset($_POST["fecha_bancaria"]) ? $_POST["fecha_bancaria"] : "");
	$local = ueno_post("local");
	$banco = ueno_banco_filtro_post();
	if ($fecha_operativa == "") {
		$fecha_operativa = date("Y-m-d");
	}
	if ($fecha_bancaria == "") {
		$fecha_bancaria = $fecha_operativa;
	}

	$fecha_operativa_sql = $mysqli->real_escape_string($fecha_operativa);
	$fecha_bancaria_sql = $mysqli->real_escape_string($fecha_bancaria);
	$banco_sql = $mysqli->real_escape_string($banco);
	$condicionBancoMovimiento = $banco != "" ? " AND banco_codigo='$banco_sql'" : "";
	$condicionBancoMovimientoAlias = $banco != "" ? " AND mv.banco_codigo='$banco_sql'" : "";
	$condicionBancoPago = $banco != "" ? " AND pc.banco_codigo='$banco_sql'" : "";
	$condicion_local = "";
	if ($local != "") {
		$localSql = $mysqli->real_escape_string($local);
		$condicion_local = " AND l.Nombre LIKE '%$localSql%'";
		if (is_numeric($local)) {
			$condicion_local = " AND (ar.cod_local='$localSql' OR l.Nombre LIKE '%$localSql%')";
		}
	}

	try {
		$total_ueno = ueno_scalar($mysqli, "SELECT IFNULL(SUM(importe_credito),0) FROM ueno_movimiento_bancario WHERE fecha_confirmacion='$fecha_bancaria_sql' AND tipo_movimiento='credito' $condicionBancoMovimiento");
		$ueno_disponible = ueno_scalar($mysqli, "SELECT IFNULL(SUM(monto_disponible),0) FROM ueno_movimiento_bancario WHERE fecha_confirmacion='$fecha_bancaria_sql' AND tipo_movimiento='credito' $condicionBancoMovimiento");
		$ueno_sin_aplicar = ueno_scalar($mysqli, "SELECT COUNT(*) FROM ueno_movimiento_bancario WHERE fecha_confirmacion='$fecha_bancaria_sql' AND tipo_movimiento='credito' AND monto_disponible>0 $condicionBancoMovimiento");
		$total_migracion_interna = 0;
		if (ueno_tabla_existe($mysqli, "ueno_movimiento_migracion_caja")) {
			$total_migracion_interna = ueno_scalar($mysqli, "SELECT IFNULL(SUM(umm.monto_aplicado),0)
				FROM ueno_movimiento_migracion_caja umm
				INNER JOIN ueno_movimiento_bancario mv ON mv.id_movimiento=umm.id_movimiento
				WHERE umm.estado='activo'
				$condicionBancoMovimientoAlias
				AND mv.fecha_confirmacion='$fecha_bancaria_sql'
				AND mv.tipo_movimiento='credito'");
		}

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
				WHERE pc.activo='SI' $condicionBancoPago AND p.codApertura='$id_arqueo'");
			$transferencia_conciliada = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' $condicionBancoPago AND pc.estado_conciliacion='conciliado_ueno' AND p.codApertura='$id_arqueo'");
			$transferencia_pendiente = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' $condicionBancoPago AND pc.estado_conciliacion='pendiente_conciliacion' AND p.codApertura='$id_arqueo'");
			$transferencia_observada = ueno_scalar($mysqli, "SELECT IFNULL(SUM(pc.monto_pago),0)
				FROM pago_transferencia_conciliacion pc
				INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
				WHERE pc.activo='SI' $condicionBancoPago AND pc.estado_conciliacion IN ('observado','rechazado') AND p.codApertura='$id_arqueo'");
			$sin_comprobante = 0;
			if ($banco == "" || $banco == "UENO") {
				$sin_comprobante = ueno_scalar($mysqli, "SELECT COUNT(*)
					FROM pago p
					INNER JOIN tipopago tp ON tp.cod_tipoPago=p.cod_tipoPagoFK
					LEFT JOIN pago_transferencia_conciliacion pc ON pc.cod_pagoFK=p.idPago AND pc.activo='SI'
					WHERE p.codApertura='$id_arqueo'
					AND UPPER(tp.nombre) LIKE '%TRANSFERENCIA%'
					AND pc.id IS NULL");
			}

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
		$diferencia = $total_ueno - $total_gv - $total_migracion_interna;

		mysqli_close($mysqli);
		ueno_json(array(
			"1" => "exito",
			"2" => $html,
			"banco_codigo" => $banco == "" ? "TODOS" : $banco,
			"fecha_operativa" => $fecha_operativa,
			"fecha_bancaria" => $fecha_bancaria,
			"cierres_esperados" => $cierres_esperados,
			"cierres_realizados" => $cierres_realizados,
			"cierres_pendientes" => $cierres_pendientes,
			"cierres_observacion" => $cierres_con_observacion,
			"total_ueno" => ueno_numero($total_ueno),
			"total_gv" => ueno_numero($total_gv),
			"total_migracion_interna" => ueno_numero($total_migracion_interna),
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
	$estado = strtolower(trim(ueno_post("estado")));
	$banco = ueno_banco_filtro_post();
	$estadosPermitidos = array("todos", "conciliados", "pendientes", "conciliados_parciales");
	if (!in_array($estado, $estadosPermitidos, true)) {
		$estado = "todos";
	}
	$tieneAsignacionesEgreso = ueno_tabla_existe($mysqli, "ueno_movimiento_gasto");
	$aplicadoDebitoMovimiento = $tieneAsignacionesEgreso
		? "IFNULL((SELECT SUM(umg_estado.monto_aplicado)
			FROM ueno_movimiento_gasto umg_estado
			WHERE umg_estado.id_movimiento=mv_auditoria.id_movimiento
			AND umg_estado.estado='activo'),0)"
		: "0";
	$aplicadoDebitoImportacion = $tieneAsignacionesEgreso
		? "IFNULL((SELECT SUM(umg_imp.monto_aplicado)
			FROM ueno_movimiento_gasto umg_imp
			WHERE umg_imp.id_movimiento=mv_importacion.id_movimiento
			AND umg_imp.estado='activo'),0)"
		: "0";
	$montoOriginalMovimiento = "(CASE
		WHEN IFNULL(mv_auditoria.importe_credito,0)>0 THEN IFNULL(mv_auditoria.importe_credito,0)
		ELSE IFNULL(mv_auditoria.importe_debito,0)
	END)";
	$montoDisponibleMovimiento = "(CASE
		WHEN IFNULL(mv_auditoria.importe_credito,0)>0 THEN GREATEST(0,IFNULL(mv_auditoria.monto_disponible,0))
		ELSE GREATEST(0,IFNULL(mv_auditoria.importe_debito,0)-$aplicadoDebitoMovimiento)
	END)";
	$montoOriginalImportacion = "IFNULL((SELECT SUM(
		CASE WHEN IFNULL(mv_importacion.importe_credito,0)>0
			THEN IFNULL(mv_importacion.importe_credito,0)
			ELSE IFNULL(mv_importacion.importe_debito,0)
		END)
		FROM ueno_movimiento_bancario mv_importacion
		WHERE mv_importacion.id_importacion=CAST(a.registro_id AS UNSIGNED)),0)";
	$montoDisponibleImportacion = "IFNULL((SELECT SUM(
		CASE WHEN IFNULL(mv_importacion.importe_credito,0)>0
			THEN GREATEST(0,IFNULL(mv_importacion.monto_disponible,0))
			ELSE GREATEST(0,IFNULL(mv_importacion.importe_debito,0)-$aplicadoDebitoImportacion)
		END)
		FROM ueno_movimiento_bancario mv_importacion
		WHERE mv_importacion.id_importacion=CAST(a.registro_id AS UNSIGNED)),0)";
	$montoOriginalAuditoria = "(CASE WHEN a.tabla_afectada='ueno_importacion_extracto'
		THEN $montoOriginalImportacion ELSE $montoOriginalMovimiento END)";
	$montoDisponibleAuditoria = "(CASE WHEN a.tabla_afectada='ueno_importacion_extracto'
		THEN $montoDisponibleImportacion ELSE $montoDisponibleMovimiento END)";
	$bancoAuditoriaSql = "COALESCE(NULLIF(mv_auditoria.banco_codigo,''),
		(SELECT imp_banco.banco_codigo FROM ueno_importacion_extracto imp_banco
			WHERE a.tabla_afectada='ueno_importacion_extracto'
			AND imp_banco.id_importacion=CAST(a.registro_id AS UNSIGNED) LIMIT 1),
		(SELECT pc_banco.banco_codigo FROM pago_transferencia_conciliacion pc_banco
			WHERE a.tabla_afectada='pago_transferencia_conciliacion'
			AND pc_banco.id=CAST(a.registro_id AS UNSIGNED) LIMIT 1),
		'UENO')";
	$bancoSql = $mysqli->real_escape_string($banco);
	$condicion = $banco != "" ? " AND $bancoAuditoriaSql='$bancoSql'" : "";
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
	if ($estado == "conciliados") {
		$condicion .= " AND $montoOriginalAuditoria>0 AND $montoDisponibleAuditoria<=0";
	} elseif ($estado == "conciliados_parciales") {
		$condicion .= " AND $montoDisponibleAuditoria>0 AND $montoDisponibleAuditoria<$montoOriginalAuditoria";
	} elseif ($estado == "pendientes") {
		$condicion .= " AND $montoDisponibleAuditoria>0";
	}
	$tieneMigracionAuditoria = ueno_tabla_existe($mysqli, "ueno_movimiento_migracion_caja") && ueno_tabla_existe($mysqli, "migrar_caja");
	$camposMigracionAuditoria = $tieneMigracionAuditoria
		? ", IFNULL(mc.idmigrar_caja,'') AS idmigrar_caja,
		IFNULL(mc.fecha,'') AS fecha_migracion,
		IFNULL(mc.monto,0) AS monto_migracion,
		IFNULL(per_envia.nombre_persona,'') AS usuario_migracion"
		: ", '' AS idmigrar_caja, '' AS fecha_migracion, 0 AS monto_migracion, '' AS usuario_migracion";
	$joinsMigracionAuditoria = $tieneMigracionAuditoria
		? "LEFT JOIN ueno_movimiento_migracion_caja umm ON umm.id=CAST(a.registro_id AS UNSIGNED) AND a.tabla_afectada='ueno_movimiento_migracion_caja'
		LEFT JOIN migrar_caja mc ON mc.idmigrar_caja=umm.idmigrar_caja
		LEFT JOIN persona per_envia ON per_envia.cod_persona=mc.cod_UsuEnviaFK"
		: "";
	$tieneDepositoAuditoria = ueno_tabla_existe($mysqli, "ueno_movimiento_deposito");
	$camposDepositoAuditoria = $tieneDepositoAuditoria
		? ", IFNULL(ud.origen_tipo,'') AS deposito_origen_tipo, IFNULL(ud.origen_id,'') AS deposito_origen_id"
		: ", '' AS deposito_origen_tipo, '' AS deposito_origen_id";
	$joinDepositoAuditoria = $tieneDepositoAuditoria
		? "LEFT JOIN ueno_movimiento_deposito ud ON ud.id=CAST(a.registro_id AS UNSIGNED) AND a.tabla_afectada='ueno_movimiento_deposito'"
		: "";

	$sql = "SELECT a.id_auditoria, a.fecha_hora, a.accion, a.tabla_afectada, a.registro_id, a.cod_pagoFK,
		a.id_movimiento, a.estado_anterior, a.estado_nuevo, a.monto, a.usuario, a.observacion,
		$bancoAuditoriaSql AS banco_codigo,
		$montoOriginalAuditoria AS monto_original_actual,
		$montoDisponibleAuditoria AS monto_disponible_actual,
		IFNULL(p.nrofactura,'') AS nrofactura,
		IFNULL(per.nombre_persona,'') AS cliente_nombre,
		IFNULL(per_usuario.nombre_persona,'') AS usuario_nombre,
		IFNULL(cl.ci_cliente,'') AS cliente_doc,
		IF(IFNULL(p.titulocuota,'')!='', p.titulocuota, IFNULL(cr.plazo,'')) AS cuota_detalle,
		IFNULL(vt.cod_venta,'') AS cod_venta
		$camposMigracionAuditoria
		$camposDepositoAuditoria
		FROM ueno_auditoria_conciliacion a
		LEFT JOIN pago p ON p.idPago=a.cod_pagoFK
		LEFT JOIN credito cr ON cr.idcredito=p.cod_creditoFK
		LEFT JOIN venta vt ON vt.cod_venta=p.cod_venta_fk
		LEFT JOIN persona per ON per.cod_persona=vt.cod_clienteFK
		LEFT JOIN persona per_usuario ON per_usuario.cod_persona=a.usuario
		LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
		LEFT JOIN ueno_movimiento_bancario mv_auditoria ON mv_auditoria.id_movimiento=a.id_movimiento
		$joinsMigracionAuditoria
		$joinDepositoAuditoria
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
		$clienteCuota = "";
		if (trim((string)$row["cliente_nombre"]) != "") {
			$clienteCuota = trim((string)$row["cliente_nombre"]);
			if (trim((string)$row["cliente_doc"]) != "") {
				$clienteCuota .= " (" . trim((string)$row["cliente_doc"]) . ")";
			}
		}
		if (trim((string)$row["cuota_detalle"]) != "") {
			$clienteCuota .= ($clienteCuota != "" ? " / " : "") . "Cuota: " . trim((string)$row["cuota_detalle"]);
		}
		if (trim((string)$row["cod_venta"]) != "") {
			$clienteCuota .= ($clienteCuota != "" ? " / " : "") . "Venta: " . trim((string)$row["cod_venta"]);
		}
		if (trim((string)$row["idmigrar_caja"]) != "") {
			$clienteCuota = "Monto migrado #" . trim((string)$row["idmigrar_caja"]);
			if (trim((string)$row["usuario_migracion"]) != "") {
				$clienteCuota .= " / Envia: " . trim((string)$row["usuario_migracion"]);
			}
			if ((int)$row["monto_migracion"] > 0) {
				$clienteCuota .= " / " . ueno_numero($row["monto_migracion"]);
			}
		}
		if (trim((string)$row["deposito_origen_id"]) != "") {
			$tipoOrigenDeposito = $row["deposito_origen_tipo"] == "migrar_caja" ? "Monto migrado" : "Deposito registrado";
			$clienteCuota = "Depósito Faraone / " . $tipoOrigenDeposito . " #" . trim((string)$row["deposito_origen_id"]);
		}
		if ($clienteCuota == "") {
			$clienteCuota = "-";
		}
		$usuarioNombre = trim((string)$row["usuario_nombre"]);
		if ($usuarioNombre == "") {
			$usuarioNombre = trim((string)$row["usuario"]);
		}
		$montoOriginalActual = max(0, (int)$row["monto_original_actual"]);
		$montoDisponibleActual = max(0, (int)$row["monto_disponible_actual"]);
		if ($montoDisponibleActual <= 0) {
			$etiquetaDisponible = "<span class='ueno-audit-saldo ueno-audit-saldo--conciliado'>Sin saldo disponible</span>";
		} else {
			$claseSaldo = $montoOriginalActual > 0 && $montoDisponibleActual < $montoOriginalActual
				? "ueno-audit-saldo--parcial"
				: "ueno-audit-saldo--pendiente";
			$etiquetaDisponible = "<span class='ueno-audit-saldo $claseSaldo'>Disponible: " . ueno_numero($montoDisponibleActual) . "</span>";
		}
		$montoAuditoriaHtml = "<div class='ueno-audit-monto-historico'>" . ueno_numero($row["monto"]) . "</div>" . $etiquetaDisponible;
		$esRegistroImportacion = $row["tabla_afectada"] == "ueno_importacion_extracto" && (int)$row["registro_id"] > 0;
		$atributosFilaAuditoria = $esRegistroImportacion
			? " class='ueno-audit-import-row' role='button' tabindex='0' title='Ver registros del lote' onclick='uenoVerDetalleImportacionDesdeAuditoria(" . (int)$row["registro_id"] . ")' onkeydown='if(event.keyCode==13 || event.keyCode==32){event.preventDefault();uenoVerDetalleImportacionDesdeAuditoria(" . (int)$row["registro_id"] . ")}'"
			: "";
		$observacionHtml = ueno_escape_html($row["observacion"]);
		if ($esRegistroImportacion) {
			$observacionHtml .= "<span class='ueno-audit-import-link'>Ver registros del lote</span>";
		}
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' data-ueno-banco='" . ueno_escape_html(ueno_banco_codigo($row["banco_codigo"])) . "'$atributosFilaAuditoria>"
			. "<td style='width:7%'>" . ueno_banco_badge_html($row["banco_codigo"], false) . "</td>"
			. "<td style='width:4%;text-align:center'>" . (int)$row["id_auditoria"] . "</td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["fecha_hora"]) . "</td>"
			. "<td style='width:10%'>" . ueno_escape_html($row["accion"]) . "</td>"
			. "<td style='width:9%'>" . ueno_escape_html($row["tabla_afectada"]) . "</td>"
			. "<td style='width:6%'>" . ueno_escape_html($row["nrofactura"]) . "</td>"
			. "<td style='width:5%;text-align:center'>" . ueno_escape_html($row["id_movimiento"]) . "</td>"
			. "<td style='width:11%'>" . ueno_escape_html($clienteCuota) . "</td>"
			. "<td style='width:7%'>" . ueno_escape_html($row["estado_anterior"]) . "</td>"
			. "<td style='width:7%'>" . ueno_escape_html($row["estado_nuevo"]) . "</td>"
			. "<td style='width:7%;text-align:right'>" . $montoAuditoriaHtml . "</td>"
			. "<td style='width:10%;text-align:center'>" . ueno_escape_html($usuarioNombre) . "</td>"
			. "<td style='width:8%'>" . $observacionHtml . "</td>"
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
	if ($operacion == "prevalidar_importacion") {
		ueno_prevalidar_importacion($usuario);
	}
	if ($operacion == "buscar_importaciones") {
		ueno_buscar_importaciones($usuario);
	}
	if ($operacion == "detalle_importacion") {
		ueno_detalle_importacion($usuario);
	}
	if ($operacion == "buscar_movimientos") {
		ueno_buscar_movimientos($usuario);
	}
	if ($operacion == "buscar_movimientos_cobro") {
		ueno_buscar_movimientos_cobro($usuario);
	}
	if ($operacion == "buscar_pagos_pendientes") {
		ueno_buscar_pagos_pendientes($usuario);
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
	if ($operacion == "buscar_contexto_gasto_egreso") {
		ueno_buscar_contexto_gasto_egreso($usuario);
	}
	if ($operacion == "buscar_egresos_bancarios_disponibles") {
		ueno_buscar_egresos_bancarios_disponibles($usuario);
	}
	if ($operacion == "buscar_gastos_pendientes_egreso") {
		ueno_buscar_gastos_pendientes_egreso($usuario);
	}
	if ($operacion == "guardar_conciliacion_egreso") {
		ueno_guardar_conciliacion_egreso($usuario);
	}
	if ($operacion == "revertir_conciliacion_egreso") {
		ueno_revertir_conciliacion_egreso($usuario);
	}
	if ($operacion == "buscar_asignaciones_egreso_banco") {
		ueno_buscar_asignaciones_egreso_banco($usuario);
	}
	if ($operacion == "buscar_sugerencias_migracion") {
		ueno_buscar_sugerencias_migracion($usuario);
	}
	if ($operacion == "conciliar_migracion_caja") {
		ueno_conciliar_migracion_caja($usuario);
	}
	if ($operacion == "conciliar_deposito_faraone") {
		ueno_conciliar_deposito($usuario);
	}

	ueno_json(array("1" => "operacioninvalida"));
}
?>

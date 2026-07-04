<?php
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("buscar_nivel.php");

function cc_json($informacion)
{
	echo json_encode($informacion);
	exit;
}

set_exception_handler(function($error) {
	cc_json(array("1" => "error", "2" => $error->getMessage()));
});

function cc_to_db($valor)
{
	return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function cc_from_db($valor)
{
	return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function cc_post($clave, $defecto = "")
{
	if (!isset($_POST[$clave])) {
		return $defecto;
	}
	return cc_to_db($_POST[$clave]);
}

function cc_validar_sesion()
{
	if (!isset($_POST['useru']) || !isset($_POST['passu']) || !isset($_POST['navegador'])) {
		cc_json(array("1" => "UI"));
	}

	$user = cc_to_db($_POST['useru']);
	$pass = str_replace("=", "+", $_POST['passu']);
	$navegador = cc_to_db($_POST['navegador']);
	$resp = verificar_navegador($user, $navegador, $pass);

	if ($resp != "ok") {
		cc_json(array("1" => "UI"));
	}

	return $user;
}

function cc_tabla_existe($mysqli, $tabla)
{
	$tabla = $mysqli->real_escape_string($tabla);
	$result = $mysqli->query("SHOW TABLES LIKE '$tabla'");
	return $result && $result->num_rows > 0;
}

function cc_permiso_definido($mysqli, $codigo)
{
	$codigo = $mysqli->real_escape_string($codigo);
	$result = $mysqli->query("SELECT idlistadodeacceso FROM listadodeacceso WHERE codigo='$codigo' LIMIT 1");
	return $result && $result->num_rows > 0;
}

function cc_usuario_tiene_permiso($mysqli, $usuario, $codigo)
{
	if ((string)$usuario === "2") {
		return true;
	}

	$usuario = $mysqli->real_escape_string($usuario);
	$codigo = $mysqli->real_escape_string($codigo);
	$sql = "SELECT COUNT(*) AS total
		FROM accesosuser au
		INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK
		WHERE au.usuarios_idusario='$usuario'
		AND la.codigo='$codigo'
		AND au.accion='SI'";
	$result = $mysqli->query($sql);
	if (!$result) {
		return false;
	}
	$row = $result->fetch_assoc();
	return (int)$row["total"] > 0;
}

function cc_requerir_permiso($mysqli, $usuario, $codigo, $fallback = "")
{
	if (cc_permiso_definido($mysqli, $codigo)) {
		if (!cc_usuario_tiene_permiso($mysqli, $usuario, $codigo)) {
			cc_json(array("1" => "sinpermiso", "2" => "No tiene permiso para usar Cobrar cuota"));
		}
		return;
	}

	if ($fallback != "" && cc_permiso_definido($mysqli, $fallback) && !cc_usuario_tiene_permiso($mysqli, $usuario, $fallback)) {
		cc_json(array("1" => "sinpermiso", "2" => "No tiene permiso para registrar pagos"));
	}
}

function cc_monto($valor)
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

function cc_numero($valor)
{
	return number_format((int)$valor, 0, ",", ".");
}

function cc_fecha($valor)
{
	$valor = trim((string)$valor);
	if ($valor == "") {
		return "";
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
	return $time ? date("Y-m-d", $time) : "";
}

function cc_escape_html($valor)
{
	return htmlspecialchars(cc_from_db($valor), ENT_QUOTES, 'UTF-8');
}

function cc_escape_texto($valor)
{
	return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function cc_comprobante_normalizado($valor)
{
	return trim(preg_replace('/\s+/', '', (string)$valor));
}

function cc_mask_comprobante($valor)
{
	$valor = cc_comprobante_normalizado($valor);
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

function cc_fecha_visual($valor)
{
	$fecha = cc_fecha($valor);
	if ($fecha == "") {
		return "";
	}
	$time = strtotime($fecha);
	return $time ? date("d/m/Y", $time) : $fecha;
}

function cc_estado_visual($saldo_cuota, $saldo_interes, $fecha_vencimiento, $pagado_cuota = 0, $pagado_interes = 0, $estado_original = "")
{
	$estado_original = strtoupper(trim((string)$estado_original));
	if (strpos($estado_original, "ANUL") !== false) {
		return "Anulada";
	}
	if ($saldo_cuota <= 0 && $saldo_interes <= 0) {
		return "Pagada";
	}
	if (((int)$pagado_cuota + (int)$pagado_interes) > 0) {
		return "Pago parcial";
	}
	if ($fecha_vencimiento != "" && $fecha_vencimiento < date("Y-m-d")) {
		return "Vencida";
	}
	return "Pendiente";
}

function cc_documento_limpio($valor)
{
	return preg_replace('/[^0-9A-Za-z]/', '', (string)$valor);
}

function cc_saldo_credito_sql()
{
	return "(GREATEST(0, ((IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Pago Cuota'),0)))"
		. " + GREATEST(0, ((IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Interes'),0))))";
}

function cc_producto_plan_sql()
{
	return "IFNULL((SELECT GROUP_CONCAT(CONCAT(pr.nombre_producto, IF(dv.cantidad_detalle>1, CONCAT(' x', dv.cantidad_detalle), '')) SEPARATOR ', ')
			FROM detalle_venta dv
			LEFT JOIN producto pr ON pr.cod_producto=dv.cod_productoFK
			WHERE dv.cod_ventaFK=vt.cod_venta
			LIMIT 1),'')";
}

function cc_nro_venta_visible($row)
{
	if (isset($row["puntoexpedicion"]) && trim((string)$row["puntoexpedicion"]) != "") {
		return $row["puntoexpedicion"] . "-" . $row["num_factura"];
	}
	if (isset($row["num_factura"]) && trim((string)$row["num_factura"]) != "") {
		return $row["num_factura"];
	}
	return isset($row["cod_venta"]) ? $row["cod_venta"] : "";
}

function cc_buscar_clientes_cobro($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "VERCOBRARCUOTA", "VERPAGOSCREDITO");

	$query = trim(cc_post("query"));
	$query_doc = cc_documento_limpio($query);
	if ($query == "") {
		mysqli_close($mysqli);
		cc_json(array("1" => "camposvacio", "2" => "Ingresa una cedula o nombre para buscar."));
	}

	$saldoExpr = "(GREATEST(0, ((IFNULL(cr.Monto,0)-IFNULL(cr.descuento,0))-IFNULL(pg_total.total_pago_cuota,0)))"
		. " + GREATEST(0, ((IFNULL(cr.totalinteres,0)+IFNULL(cr.deudaInteres,0))-IFNULL(pg_total.total_pago_interes,0))))";
	$querySql = $mysqli->real_escape_string($query);
	$queryDocSql = $mysqli->real_escape_string($query_doc);
	$condiciones = array(
		"IFNULL(vt.anulado,'')=''",
		"IFNULL(vt.estadocuenta,'Activo')!='Anulado'",
		$saldoExpr . ">0"
	);
	$busqueda = array();
	if ($querySql != "") {
		$busqueda[] = "pe.nombre_persona LIKE '%$querySql%'";
		$busqueda[] = "IFNULL(vt.apodo,'') LIKE '%$querySql%'";
	}
	if ($queryDocSql != "") {
		$busqueda[] = "REPLACE(REPLACE(REPLACE(IFNULL(cl.ci_cliente,''),'.',''),'-',''),' ','') LIKE '%$queryDocSql%'";
	}
	if (count($busqueda) > 0) {
		$condiciones[] = "(" . implode(" OR ", $busqueda) . ")";
	}
	$where = implode(" AND ", $condiciones);
	$sql = "SELECT
		vt.cod_clienteFK,
		pe.nombre_persona AS cliente,
		pe.telefono,
		cl.ci_cliente,
		COUNT(DISTINCT vt.cod_venta) AS planes,
		SUM($saldoExpr) AS saldo_total
		FROM credito cr
		INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta
		LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
		LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
		LEFT JOIN (
			SELECT
				cod_creditoFK,
				SUM(CASE WHEN Tipo='Pago Cuota' THEN Monto ELSE 0 END) AS total_pago_cuota,
				SUM(CASE WHEN Tipo='Interes' THEN Monto ELSE 0 END) AS total_pago_interes
			FROM pago
			GROUP BY cod_creditoFK
		) pg_total ON pg_total.cod_creditoFK=cr.idcredito
		WHERE $where
		GROUP BY vt.cod_clienteFK, pe.nombre_persona, pe.telefono, cl.ci_cliente
		ORDER BY pe.nombre_persona ASC, vt.cod_clienteFK ASC
		LIMIT 30";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}
	$result = $stmt->get_result();
	$clientes = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$clientes[] = array(
			"cliente_id" => (int)$row["cod_clienteFK"],
			"cliente" => cc_from_db($row["cliente"]),
			"cedula" => cc_from_db($row["ci_cliente"]),
			"telefono" => cc_from_db($row["telefono"]),
			"planes" => (int)$row["planes"],
			"saldo_total" => (int)$row["saldo_total"],
			"saldo_total_fmt" => cc_numero($row["saldo_total"])
		);
	}
	mysqli_close($mysqli);
	cc_json(array("1" => "exito", "clientes" => $clientes, "total" => count($clientes)));
}

function cc_listar_planes_cliente($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "VERCOBRARCUOTA", "VERPAGOSCREDITO");

	$cliente_id = cc_post("cliente_id");
	$venta = cc_post("venta");
	$saldoExpr = cc_saldo_credito_sql();
	$productoExpr = cc_producto_plan_sql();
	$condiciones = array(
		"IFNULL(vt.anulado,'')=''",
		"IFNULL(vt.estadocuenta,'Activo')!='Anulado'",
		$saldoExpr . ">0"
	);
	if ($venta != "") {
		$ventaSql = $mysqli->real_escape_string($venta);
		$condiciones[] = "vt.cod_venta='$ventaSql'";
	} else if ($cliente_id != "") {
		$clienteSql = $mysqli->real_escape_string($cliente_id);
		$condiciones[] = "vt.cod_clienteFK='$clienteSql'";
	} else {
		mysqli_close($mysqli);
		cc_json(array("1" => "camposvacio", "2" => "Selecciona un cliente o venta."));
	}
	$where = implode(" AND ", $condiciones);
	$sql = "SELECT
		vt.cod_venta, vt.num_factura, vt.puntoexpedicion, vt.fecha_venta, vt.cod_clienteFK,
		vt.cod_cobradorFK, vt.cod_local, vt.apodo, vt.TipoVenta, vt.estadocuenta,
		(IFNULL(vt.total_venta,0)-IFNULL(vt.descuento,0)) AS total_venta_neta,
		pe.nombre_persona AS cliente, pe.telefono, cl.ci_cliente,
		IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=vt.cod_cobradorFK LIMIT 1),'') AS cobrador,
		IFNULL((SELECT Nombre FROM local WHERE cod_local=vt.cod_local LIMIT 1),'') AS local_nombre,
		$productoExpr AS productos,
		COUNT(DISTINCT cr.idcredito) AS cuotas_pendientes,
		SUM($saldoExpr) AS saldo_total,
		MIN(cr.fechapago) AS proximo_vencimiento
		FROM credito cr
		INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta
		LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
		LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
		WHERE $where
		GROUP BY vt.cod_venta, vt.num_factura, vt.puntoexpedicion, vt.fecha_venta, vt.cod_clienteFK,
			vt.cod_cobradorFK, vt.cod_local, vt.apodo, vt.TipoVenta, vt.estadocuenta,
			vt.total_venta, vt.descuento, pe.nombre_persona, pe.telefono, cl.ci_cliente
		ORDER BY IFNULL(vt.fecha_venta, '2999-12-31') ASC, vt.cod_venta ASC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}
	$result = $stmt->get_result();
	$planes = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$alias = trim((string)$row["apodo"]);
		$planes[] = array(
			"cod_venta" => (int)$row["cod_venta"],
			"venta_id" => (int)$row["cod_venta"],
			"venta" => cc_from_db(cc_nro_venta_visible($row)),
			"fecha_venta" => cc_from_db($row["fecha_venta"]),
			"cliente_id" => (int)$row["cod_clienteFK"],
			"cliente" => cc_from_db($row["cliente"]),
			"cedula" => cc_from_db($row["ci_cliente"]),
			"telefono" => cc_from_db($row["telefono"]),
			"alias" => cc_from_db($alias),
			"beneficiario" => cc_from_db($alias),
			"producto" => cc_from_db($row["productos"] != "" ? $row["productos"] : "Venta sin detalle visible"),
			"estado" => cc_from_db($row["estadocuenta"]),
			"tipo_venta" => cc_from_db($row["TipoVenta"]),
			"total_venta" => (int)$row["total_venta_neta"],
			"total_venta_fmt" => cc_numero($row["total_venta_neta"]),
			"cuotas_pendientes" => (int)$row["cuotas_pendientes"],
			"saldo_pendiente_total" => (int)$row["saldo_total"],
			"saldo_pendiente_total_fmt" => cc_numero($row["saldo_total"]),
			"proximo_vencimiento" => cc_from_db($row["proximo_vencimiento"]),
			"cobrador_id" => (int)$row["cod_cobradorFK"],
			"cobrador" => cc_from_db($row["cobrador"]),
			"local_id" => (int)$row["cod_local"],
			"local" => cc_from_db($row["local_nombre"])
		);
	}
	mysqli_close($mysqli);
	cc_json(array("1" => "exito", "planes" => $planes, "total" => count($planes)));
}

function cc_datos_cuota_desde_row($row)
{
	$saldo_cuota = max(0, ((int)$row["Monto"] - (int)$row["descuento"]) - (int)$row["total_pago_cuota"]);
	$saldo_interes = max(0, ((int)$row["totalinteres"] + (int)$row["deudaInteres"]) - (int)$row["total_pago_interes"]);
	$saldo_total = $saldo_cuota + $saldo_interes;
	$nro_venta = cc_nro_venta_visible($row);
	$alias = trim((string)$row["apodo"]);
	$producto = $row["productos"] != "" ? $row["productos"] : "Venta sin detalle visible";
	return array(
		"idcredito" => (int)$row["idcredito"],
		"cod_venta" => (int)$row["cod_venta"],
		"cliente_id" => (int)$row["cod_clienteFK"],
		"cliente" => cc_from_db($row["cliente"]),
		"cedula" => cc_from_db($row["ci_cliente"]),
		"telefono" => cc_from_db($row["telefono"]),
		"alias" => cc_from_db($alias),
		"beneficiario" => cc_from_db($alias),
		"venta" => cc_from_db($nro_venta),
		"venta_id" => (int)$row["cod_venta"],
		"fecha_venta" => cc_from_db(isset($row["fecha_venta"]) ? $row["fecha_venta"] : ""),
		"producto" => cc_from_db($producto),
		"cuota" => cc_from_db($row["plazo"]),
		"fecha_vencimiento" => cc_from_db($row["fechapago"]),
		"monto_cuota" => cc_numero($row["Monto"]),
		"monto_cuota_num" => (int)$row["Monto"],
		"saldo_cuota" => cc_numero($saldo_cuota),
		"saldo_cuota_num" => $saldo_cuota,
		"saldo_interes" => cc_numero($saldo_interes),
		"saldo_interes_num" => $saldo_interes,
		"saldo_pendiente" => cc_numero($saldo_total),
		"saldo_pendiente_num" => $saldo_total,
		"pagado_cuota" => cc_numero($row["total_pago_cuota"]),
		"pagado_cuota_num" => (int)$row["total_pago_cuota"],
		"pagado_interes" => cc_numero($row["total_pago_interes"]),
		"pagado_interes_num" => (int)$row["total_pago_interes"],
		"pagado_total" => cc_numero((int)$row["total_pago_cuota"] + (int)$row["total_pago_interes"]),
		"pagado_total_num" => (int)$row["total_pago_cuota"] + (int)$row["total_pago_interes"],
		"estado" => cc_estado_visual($saldo_cuota, $saldo_interes, $row["fechapago"], $row["total_pago_cuota"], $row["total_pago_interes"], $row["Esado"]),
		"cobrador_id" => (int)$row["cod_cobradorFK"],
		"cobrador" => cc_from_db($row["cobrador"]),
		"local_id" => (int)$row["cod_local"],
		"local" => cc_from_db($row["local_nombre"]),
		"tipo_venta" => cc_from_db($row["TipoVenta"]),
		"total_venta" => isset($row["total_venta_neta"]) ? (int)$row["total_venta_neta"] : 0,
		"total_venta_fmt" => isset($row["total_venta_neta"]) ? cc_numero($row["total_venta_neta"]) : "0"
	);
}

function cc_listar_cuotas_venta($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "VERCOBRARCUOTA", "VERPAGOSCREDITO");
	$venta = cc_post("venta");
	if ($venta == "") {
		mysqli_close($mysqli);
		cc_json(array("1" => "camposvacio", "2" => "Selecciona un plan para ver sus cuotas."));
	}
	$ventaSql = $mysqli->real_escape_string($venta);
	$saldoExpr = cc_saldo_credito_sql();
	$productoExpr = cc_producto_plan_sql();
	$sql = "SELECT
		cr.idcredito, cr.plazo, cr.fechapago, cr.cod_venta, cr.Monto, cr.descuento, cr.totalinteres, cr.deudaInteres, cr.Esado,
		vt.num_factura, vt.puntoexpedicion, vt.fecha_venta, vt.tipo_comprobante, vt.cod_clienteFK, vt.cod_cobradorFK, vt.cod_local, vt.apodo, vt.TipoVenta,
		(IFNULL(vt.total_venta,0)-IFNULL(vt.descuento,0)) AS total_venta_neta,
		pe.nombre_persona AS cliente, pe.telefono,
		cl.ci_cliente,
		IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=vt.cod_cobradorFK LIMIT 1),'') AS cobrador,
		IFNULL((SELECT Nombre FROM local WHERE cod_local=vt.cod_local LIMIT 1),'') AS local_nombre,
		$productoExpr AS productos,
		IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Pago Cuota'),0) AS total_pago_cuota,
		IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Interes'),0) AS total_pago_interes
		FROM credito cr
		INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta
		LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
		LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
		WHERE IFNULL(vt.anulado,'')=''
		AND IFNULL(vt.estadocuenta,'Activo')!='Anulado'
		AND vt.cod_venta='$ventaSql'
		ORDER BY
			CASE WHEN cr.plazo REGEXP '^[0-9]+' THEN CAST(SUBSTRING_INDEX(cr.plazo,'/',1) AS UNSIGNED) ELSE 999999 END ASC,
			cr.fechapago ASC,
			cr.idcredito ASC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}
	$result = $stmt->get_result();
	$cuotas = array();
	$total_saldo = 0;
	$total_pendientes = 0;
	$total_venta = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		if ($total_venta <= 0 && isset($row["total_venta_neta"])) {
			$total_venta = (int)$row["total_venta_neta"];
		}
		$cuota = cc_datos_cuota_desde_row($row);
		if ($cuota["saldo_pendiente_num"] > 0 && $cuota["estado"] != "Anulada") {
			$total_saldo += $cuota["saldo_pendiente_num"];
			$total_pendientes++;
		}
		$cuotas[] = $cuota;
	}
	mysqli_close($mysqli);
	cc_json(array(
		"1" => "exito",
		"cuotas" => $cuotas,
		"total" => $total_pendientes,
		"total_cuotas" => count($cuotas),
		"saldo_total" => cc_numero($total_saldo),
		"saldo_total_num" => $total_saldo,
		"total_venta" => $total_venta,
		"total_venta_fmt" => cc_numero($total_venta)
	));
}

function cc_buscar_cuotas($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "VERCOBRARCUOTA", "VERPAGOSCREDITO");

	$documento = cc_post("documento");
	$nombre = cc_post("nombre");
	$venta = cc_post("venta");
	$telefono = cc_post("telefono");
	$id_credito = cc_post("id_credito");
	$condiciones = array("IFNULL(vt.anulado,'')=''", "IFNULL(vt.estadocuenta,'Activo')!='Anulado'");

	if ($id_credito != "") {
		$condiciones[] = "cr.idcredito='" . $mysqli->real_escape_string($id_credito) . "'";
	}
	if ($documento != "") {
		$docSql = $mysqli->real_escape_string($documento);
		$condiciones[] = "(cl.ci_cliente LIKE '%$docSql%' OR vt.num_factura LIKE '%$docSql%')";
	}
	if ($nombre != "") {
		$nombreSql = $mysqli->real_escape_string($nombre);
		$condiciones[] = "(pe.nombre_persona LIKE '%$nombreSql%' OR vt.apodo LIKE '%$nombreSql%')";
	}
	if ($venta != "") {
		$ventaSql = $mysqli->real_escape_string($venta);
		$condiciones[] = "(vt.cod_venta LIKE '%$ventaSql%' OR vt.num_factura LIKE '%$ventaSql%' OR CONCAT(IFNULL(vt.puntoexpedicion,''),'-',IFNULL(vt.num_factura,'')) LIKE '%$ventaSql%')";
	}
	if ($telefono != "") {
		$telSql = $mysqli->real_escape_string($telefono);
		$condiciones[] = "pe.telefono LIKE '%$telSql%'";
	}
	if ($id_credito == "" && $documento == "" && $nombre == "" && $venta == "" && $telefono == "") {
		$condiciones[] = "cr.fechapago<=DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
	}

	$where = implode(" AND ", $condiciones);
	$sql = "SELECT
		cr.idcredito, cr.plazo, cr.fechapago, cr.cod_venta, cr.Monto, cr.descuento, cr.totalinteres, cr.deudaInteres, cr.Esado,
		vt.num_factura, vt.puntoexpedicion, vt.tipo_comprobante, vt.cod_clienteFK, vt.cod_cobradorFK, vt.cod_local, vt.apodo, vt.TipoVenta,
		(IFNULL(vt.total_venta,0)-IFNULL(vt.descuento,0)) AS total_venta_neta,
		pe.nombre_persona AS cliente, pe.telefono,
		cl.ci_cliente,
		IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona=vt.cod_cobradorFK LIMIT 1),'') AS cobrador,
		IFNULL((SELECT Nombre FROM local WHERE cod_local=vt.cod_local LIMIT 1),'') AS local_nombre,
		IFNULL((SELECT GROUP_CONCAT(CONCAT(pr.nombre_producto, IF(dv.cantidad_detalle>1, CONCAT(' x', dv.cantidad_detalle), '')) SEPARATOR ', ')
			FROM detalle_venta dv
			LEFT JOIN producto pr ON pr.cod_producto=dv.cod_productoFK
			WHERE dv.cod_ventaFK=vt.cod_venta
			LIMIT 1),'') AS productos,
		IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Pago Cuota'),0) AS total_pago_cuota,
		IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=cr.idcredito AND pg.Tipo='Interes'),0) AS total_pago_interes
		FROM credito cr
		INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta
		LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
		LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
		WHERE $where
		HAVING ((IFNULL(Monto,0)-IFNULL(descuento,0))-IFNULL(total_pago_cuota,0))>0
		ORDER BY cr.fechapago ASC, cr.idcredito ASC
		LIMIT 100";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}

	$result = $stmt->get_result();
	$html = "";
	$total = 0;
	$total_saldo = 0;
	$styleName = "tableRegistroSearch";

	while ($row = mysqli_fetch_assoc($result)) {
		$saldo_cuota = max(0, ((int)$row["Monto"] - (int)$row["descuento"]) - (int)$row["total_pago_cuota"]);
		$saldo_interes = max(0, ((int)$row["totalinteres"] + (int)$row["deudaInteres"]) - (int)$row["total_pago_interes"]);
		$saldo_total = $saldo_cuota + $saldo_interes;
		if ($saldo_total <= 0) {
			continue;
		}
		$total++;
		$total_saldo += $saldo_total;
		$nro_venta = $row["puntoexpedicion"] != "" ? $row["puntoexpedicion"] . "-" . $row["num_factura"] : $row["num_factura"];
		$estado = cc_estado_visual($saldo_cuota, $saldo_interes, $row["fechapago"]);
		$beneficiario = $row["apodo"] != "" ? $row["apodo"] : "Titular";
		$producto = $row["productos"] != "" ? $row["productos"] : "Venta sin detalle visible";
		$cuota = $row["plazo"];
		$datos = array(
			"idcredito" => (int)$row["idcredito"],
			"cod_venta" => (int)$row["cod_venta"],
			"cliente_id" => (int)$row["cod_clienteFK"],
			"cliente" => cc_from_db($row["cliente"]),
			"cedula" => cc_from_db($row["ci_cliente"]),
			"telefono" => cc_from_db($row["telefono"]),
			"beneficiario" => cc_from_db($beneficiario),
			"venta" => cc_from_db($nro_venta),
			"venta_id" => (int)$row["cod_venta"],
			"producto" => cc_from_db($producto),
			"cuota" => cc_from_db($cuota),
			"fecha_vencimiento" => cc_from_db($row["fechapago"]),
			"monto_cuota" => cc_numero($row["Monto"]),
			"monto_cuota_num" => (int)$row["Monto"],
			"saldo_cuota" => cc_numero($saldo_cuota),
			"saldo_cuota_num" => $saldo_cuota,
			"saldo_interes" => cc_numero($saldo_interes),
			"saldo_interes_num" => $saldo_interes,
			"saldo_pendiente" => cc_numero($saldo_total),
			"saldo_pendiente_num" => $saldo_total,
			"estado" => $estado,
			"cobrador_id" => (int)$row["cod_cobradorFK"],
			"cobrador" => cc_from_db($row["cobrador"]),
			"local_id" => (int)$row["cod_local"],
			"local" => cc_from_db($row["local_nombre"]),
			"tipo_venta" => cc_from_db($row["TipoVenta"]),
			"total_venta" => isset($row["total_venta_neta"]) ? (int)$row["total_venta_neta"] : 0,
			"total_venta_fmt" => isset($row["total_venta_neta"]) ? cc_numero($row["total_venta_neta"]) : "0"
		);
		$datos_js = htmlspecialchars(json_encode($datos), ENT_QUOTES, 'UTF-8');
		$styleName = function_exists("CargarStyleTable") ? CargarStyleTable($styleName) : $styleName;
		$html .= "<table class='$styleName cobrar-cuota__result-table' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro' class='cobrar-cuota__result-row' data-cobrar-cuota-id='" . (int)$row["idcredito"] . "'>"
			. "<td data-label='Cliente' style='width:16%'>" . cc_escape_html($row["cliente"]) . "</td>"
			. "<td data-label='Beneficiario' style='width:11%'>" . htmlspecialchars(cc_from_db($beneficiario), ENT_QUOTES, 'UTF-8') . "</td>"
			. "<td data-label='Cedula' style='width:8%'>" . cc_escape_html($row["ci_cliente"]) . "</td>"
			. "<td data-label='Venta' style='width:8%;text-align:center'>" . htmlspecialchars(cc_from_db($nro_venta), ENT_QUOTES, 'UTF-8') . "</td>"
			. "<td data-label='Producto' style='width:18%'>" . htmlspecialchars(cc_from_db($producto), ENT_QUOTES, 'UTF-8') . "</td>"
			. "<td data-label='Cuota' style='width:7%;text-align:center'>" . cc_escape_html($row["plazo"]) . "</td>"
			. "<td data-label='Vencimiento' style='width:8%;text-align:center'>" . cc_escape_html($row["fechapago"]) . "</td>"
			. "<td data-label='Saldo' style='width:9%;text-align:right'>" . cc_numero($saldo_total) . "</td>"
			. "<td data-label='Estado' style='width:7%;text-align:center'><span class='cobrar-cuota__estado cobrar-cuota__estado--" . strtolower($estado) . "'>" . $estado . "</span></td>"
			. "<td data-label='Accion' style='width:8%;text-align:center'><input type='button' value='Cobrar' class='btn4 cobrar-cuota__btn-tabla' onclick='cobrarCuotaSeleccionar(" . $datos_js . ")'></td>"
			. "</tr></table>";
	}

	if ($html == "") {
		$html = "<div class='cobrar-cuota__empty'><b>Sin cuotas pendientes</b><span>Proba con otra cedula, nombre, telefono o numero de venta.</span></div>";
	}

	mysqli_close($mysqli);
	cc_json(array("1" => "exito", "2" => $html, "3" => $total, "4" => cc_numero($total_saldo)));
}

function cc_buscar_movimiento_ueno($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "VERCOBRARCUOTA", "VERPAGOSCREDITO");
	if (!cc_tabla_existe($mysqli, "ueno_movimiento_bancario")) {
		mysqli_close($mysqli);
		cc_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar la actualizacion de Conciliacion Ueno"));
	}

	$comprobante = cc_comprobante_normalizado(cc_post("comprobante"));
	$monto = cc_monto(isset($_POST["monto"]) ? $_POST["monto"] : "");
	$fecha_pago = cc_fecha(cc_post("fecha_pago"));
	$id_movimiento = cc_post("id_movimiento");
	$condicion = "tipo_movimiento='credito'
		AND monto_disponible>0
		AND LOWER(IFNULL(estado,'')) NOT IN ('conciliado','conciliada','asignado_total','anulado','anulada','rechazado','rechazada')";
	if ($id_movimiento != "") {
		$condicion .= " AND id_movimiento='" . $mysqli->real_escape_string($id_movimiento) . "'";
	}
	if ($comprobante != "") {
		$compSql = $mysqli->real_escape_string($comprobante);
		$condicion .= " AND nro_comprobante LIKE '%$compSql%'";
	}
	$fechaSql = $mysqli->real_escape_string($fecha_pago);
	$ordenFecha = $fecha_pago != ""
		? "CASE WHEN fecha_confirmacion='$fechaSql' OR fecha_transaccion='$fechaSql' THEN 0 ELSE 1 END,"
		: "";
	$sql = "SELECT id_movimiento, nro_comprobante, fecha_confirmacion, fecha_transaccion, descripcion, concepto,
		importe_credito, monto_disponible, estado
		FROM ueno_movimiento_bancario
		WHERE $condicion
		ORDER BY
			CASE WHEN nro_comprobante='" . $mysqli->real_escape_string($comprobante) . "' THEN 0 ELSE 1 END,
			CASE WHEN '" . $mysqli->real_escape_string($comprobante) . "'<>'' AND nro_comprobante LIKE '" . $mysqli->real_escape_string($comprobante) . "%' THEN 0 ELSE 1 END,
			$ordenFecha
			CASE WHEN $monto>0 AND monto_disponible=$monto THEN 0 ELSE 1 END,
			CASE WHEN $monto>0 AND monto_disponible>$monto THEN 0 ELSE 1 END,
			fecha_confirmacion DESC,
			id_movimiento DESC
		LIMIT 80";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		$error = $stmt ? $stmt->error : $mysqli->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}
	$result = $stmt->get_result();
	$html = "";
	$filas = array();
	$primer = null;
	$tieneExacta = false;
	while ($row = mysqli_fetch_assoc($result)) {
		$filas[] = $row;
	}
	$total = count($filas);
	if ($total > 1) {
		$html .= "<div class='cobrar-cuota__ueno-warning'><b>Hay varios movimientos disponibles</b><span>Selecciona la transferencia que corresponda a este cobro.</span></div>";
		$html .= "<div class='cobrar-cuota__ueno-toolbar'><button type='button' class='cobrar-cuota__ueno-open-modal' onclick='cobrarCuotaAbrirModalUeno()' aria-haspopup='dialog'>Ver grande</button></div>";
		$html .= "<div class='cobrar-cuota__ueno-filter' role='search' aria-label='Filtrar transferencias Ueno'>"
			. "<label><b>Comprobante</b><input type='search' id='inptCobrarCuotaFiltroUenoComprobante' class='js-cobrar-cuota-ueno-filtro-comprobante' placeholder='Numero de comprobante' autocomplete='off' aria-label='Filtrar por numero de comprobante' oninput='cobrarCuotaFiltrarMovimientosUeno(this)'></label>"
			. "<label><b>Monto</b><input type='text' id='inptCobrarCuotaFiltroUenoMonto' class='js-cobrar-cuota-ueno-filtro-monto' placeholder='Monto' inputmode='numeric' autocomplete='off' aria-label='Filtrar por monto' oninput='cobrarCuotaFiltrarMovimientosUeno(this)'></label>"
			. "<button type='button' onclick='cobrarCuotaLimpiarFiltroMovimientosUeno(this)'>Limpiar</button>"
			. "<span id='lblCobrarCuotaFiltroUenoResultado' class='js-cobrar-cuota-ueno-filtro-resultado'>" . cc_numero($total) . " transferencias</span>"
			. "</div>"
			. "<div id='divCobrarCuotaFiltroUenoVacio' class='cobrar-cuota__ueno-filter-empty js-cobrar-cuota-ueno-filtro-vacio' style='display:none;'>No hay transferencias con ese comprobante o monto.</div>";
	}
	foreach ($filas as $row) {
		$comprobanteRealDb = cc_comprobante_normalizado($row["nro_comprobante"]);
		$comprobanteReal = cc_comprobante_normalizado(cc_from_db($row["nro_comprobante"]));
		$comprobanteMasked = cc_mask_comprobante($comprobanteReal);
		$coincidenciaExacta = ($comprobante != "" && (string)$comprobanteRealDb === (string)$comprobante);
		if ($coincidenciaExacta) {
			$tieneExacta = true;
		}
		$fechaMovimientoDb = trim((string)$row["fecha_confirmacion"]) != "" ? $row["fecha_confirmacion"] : $row["fecha_transaccion"];
		$fechaMovimiento = cc_fecha($fechaMovimientoDb);
		$fechaMovimientoVisual = cc_fecha_visual($fechaMovimientoDb);
		$fechaCoincide = ($fecha_pago != "" && $fechaMovimiento != "" && $fechaMovimiento == $fecha_pago);
		$disponible = (int)$row["monto_disponible"];
		$importe = (int)$row["importe_credito"];
		$estado = cc_from_db($row["estado"]);
		$estadoNormalizado = strtolower(trim($estado));
		$estadoDisponible = !in_array($estadoNormalizado, array("conciliado", "conciliada", "asignado_total", "anulado", "anulada", "rechazado", "rechazada"));
		$montoValido = ($monto > 0 && $disponible > 0 && $monto <= $disponible);
		$pagoParcialSugerido = ($monto > 0 && $disponible > 0 && $monto > $disponible);
		$puedeUsar = ($estadoDisponible && ($montoValido || $pagoParcialSugerido));
		$saldoRestante = $monto > 0 ? max(0, $disponible - min($monto, $disponible)) : $disponible;
		$mensajeAccion = "Usar este movimiento";
		if (!$estadoDisponible || $disponible <= 0) {
			$mensajeAccion = "Movimiento no disponible";
		} elseif ($monto <= 0) {
			$mensajeAccion = "Ingresa monto";
		} elseif ($monto > $disponible) {
			$mensajeAccion = "Usar como pago parcial";
		}
		$datos = array(
			"id_movimiento" => (int)$row["id_movimiento"],
			"nro_comprobante" => $comprobanteReal,
			"comprobante_masked" => $comprobanteMasked,
			"fecha_confirmacion" => cc_from_db($row["fecha_confirmacion"]),
			"fecha_transaccion" => cc_from_db($row["fecha_transaccion"]),
			"fecha_movimiento" => $fechaMovimientoVisual,
			"importe_credito" => (int)$row["importe_credito"],
			"importe_credito_fmt" => cc_numero($row["importe_credito"]),
			"monto_disponible" => (int)$row["monto_disponible"],
			"monto_disponible_fmt" => cc_numero($row["monto_disponible"]),
			"estado" => $estado,
			"coincidencia_exacta" => $coincidenciaExacta,
			"fecha_pago_coincide" => $fechaCoincide,
			"monto_valido" => $montoValido,
			"pago_parcial_sugerido" => $pagoParcialSugerido,
			"puede_usar" => $puedeUsar,
			"saldo_restante" => $saldoRestante,
			"saldo_restante_fmt" => cc_numero($saldoRestante),
			"mensaje_accion" => $mensajeAccion
		);
		if ($primer === null) {
			$primer = $datos;
		}
		$datos_js = htmlspecialchars(json_encode($datos), ENT_QUOTES, 'UTF-8');
		$comprobanteVisible = $comprobanteReal != "" ? $comprobanteReal : $comprobanteMasked;
		$badgeComprobante = $comprobante == ""
			? "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--ok'>Seleccionable</span>"
			: ($coincidenciaExacta
				? "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--ok'>Coincidencia exacta</span>"
				: "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--warn'>Similar</span>");
		$badgeFecha = $fecha_pago == ""
			? ""
			: ($fechaCoincide
				? "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--ok'>Fecha coincide</span>"
				: "<span class='cobrar-cuota__ueno-badge cobrar-cuota__ueno-badge--warn'>Fecha diferente</span>");
		$accion = $puedeUsar
			? "<input type='button' value='Usar movimiento' class='btn4 cobrar-cuota__btn-secundario' onclick='cobrarCuotaUsarMovimientoUeno(" . $datos_js . ")'>"
			: "<input type='button' value='" . cc_escape_texto($mensajeAccion) . "' class='btn4 cobrar-cuota__btn-secundario cobrar-cuota__ueno-action-disabled' disabled>";
		$atributosFiltro = " data-ueno-comprobante='" . htmlspecialchars($comprobanteReal, ENT_QUOTES, 'UTF-8') . "'"
			. " data-ueno-comprobante-mask='" . htmlspecialchars($comprobanteMasked, ENT_QUOTES, 'UTF-8') . "'"
			. " data-ueno-importe='" . (int)$importe . "'"
			. " data-ueno-disponible='" . (int)$disponible . "'"
			. " data-ueno-saldo='" . (int)$saldoRestante . "'";
		$html .= "<div class='cobrar-cuota__ueno-item'" . $atributosFiltro . ">"
			. "<div class='cobrar-cuota__ueno-card-body'>"
			. "<div class='cobrar-cuota__ueno-card-top'>"
			. "<div class='cobrar-cuota__ueno-card-head'><b class='cobrar-cuota__ueno-date-main'>" . cc_escape_texto($fechaMovimientoVisual != "" ? $fechaMovimientoVisual : "Sin fecha") . "</b><span class='cobrar-cuota__ueno-comprobante'>Comprobante <strong>" . cc_escape_texto($comprobanteVisible) . "</strong></span><div class='cobrar-cuota__ueno-badges'>" . $badgeComprobante . $badgeFecha . "</div></div>"
			. "<div class='cobrar-cuota__ueno-card-action'>" . $accion . "</div>"
			. "</div>"
			. "<div class='cobrar-cuota__ueno-card-grid'>"
			. "<span><small>Saldo restante</small><strong>" . cc_numero($saldoRestante) . "</strong></span>"
			. "<span><small>Importe</small><strong>" . cc_numero($importe) . "</strong></span>"
			. "<span><small>Disponible</small><strong>" . cc_numero($disponible) . "</strong></span>"
			. "<span><small>Estado</small><strong>" . cc_escape_texto($estado != "" ? $estado : "-") . "</strong></span>"
			. "</div>"
			. "</div>"
			. "</div>";
	}
	if ($html == "") {
		$html = "<div class='cobrar-cuota__ueno-empty cobrar-cuota__ueno-pending'>No encontramos una transferencia Ueno disponible. Ajusta el monto, comprobante o revisa los movimientos importados.</div>";
	}
	mysqli_close($mysqli);
	cc_json(array("1" => "exito", "2" => $html, "3" => $total, "4" => $primer, "5" => $tieneExacta ? "SI" : "NO"));
}

function cc_auditar_cobro($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "REGISTRARCOBRARCUOTA", "INSERTARPAGOSCREDITO");
	if (!cc_tabla_existe($mysqli, "cobrar_cuota_auditoria")) {
		mysqli_close($mysqli);
		cc_json(array("1" => "exito", "2" => "Auditoria de Cobrar cuota no instalada"));
	}

	$accion = cc_post("accion", "REGISTRAR_COBRO");
	$cod_credito = cc_post("cod_credito");
	$cod_venta = cc_post("cod_venta");
	$cod_cliente = cc_post("cod_cliente");
	$cliente = cc_post("cliente");
	$forma_pago = cc_post("forma_pago");
	$monto = cc_monto(isset($_POST["monto"]) ? $_POST["monto"] : "");
	$comprobante = cc_post("comprobante");
	$id_movimiento = cc_post("id_movimiento");
	$estado_pago = cc_post("estado_pago");
	$estado_conciliacion = cc_post("estado_conciliacion");
	$cod_local = cc_post("cod_local");
	$observacion = substr(cc_post("observacion"), 0, 255);
	$datos = cc_post("datos");

	$consulta = "INSERT INTO cobrar_cuota_auditoria
		(accion, cod_creditoFK, cod_venta, cod_cliente, cliente, forma_pago, monto, comprobante, id_movimiento_ueno,
		 estado_pago, estado_conciliacion, usuario, cod_local, observacion, datos)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmt = $mysqli->prepare($consulta);
	if (!$stmt) {
		$error = $mysqli->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}
	$stmt->bind_param(
		"sssssssssssssss",
		$accion,
		$cod_credito,
		$cod_venta,
		$cod_cliente,
		$cliente,
		$forma_pago,
		$monto,
		$comprobante,
		$id_movimiento,
		$estado_pago,
		$estado_conciliacion,
		$usuario,
		$cod_local,
		$observacion,
		$datos
	);
	if (!$stmt->execute()) {
		$error = $stmt->error;
		mysqli_close($mysqli);
		cc_json(array("1" => "error", "2" => $error));
	}
	mysqli_close($mysqli);
	cc_json(array("1" => "exito"));
}

function cc_conciliar_transferencia($usuario)
{
	$mysqli = conectar_al_servidor();
	cc_requerir_permiso($mysqli, $usuario, "CONCILIARCOBRARCUOTA", "VERCONCILIACIONUENO");
	mysqli_close($mysqli);

	require_once("abmConciliacionUeno.php");
	$mysqli = conectar_al_servidor();
	if (!ueno_tablas_requeridas_ok($mysqli)) {
		mysqli_close($mysqli);
		cc_json(array("1" => "tablasfaltantes", "2" => "Falta ejecutar actualizacion_10062026_conciliacion_ueno.sql"));
	}

	$id_movimiento = cc_post("id_movimiento");
	$comprobante = cc_post("comprobante");
	$cod_credito = cc_post("cod_credito");
	$monto = cc_monto(isset($_POST["monto"]) ? $_POST["monto"] : "");
	if ($id_movimiento != "") {
		$idMovPreview = $mysqli->real_escape_string($id_movimiento);
		$resMovPreview = $mysqli->query("SELECT nro_comprobante FROM ueno_movimiento_bancario WHERE id_movimiento='$idMovPreview' AND tipo_movimiento='credito' LIMIT 1");
		$movPreview = $resMovPreview ? $resMovPreview->fetch_assoc() : null;
		if ($movPreview) {
			$comprobante = cc_comprobante_normalizado($movPreview["nro_comprobante"]);
		}
	}
	if ($id_movimiento == "" || $comprobante == "" || $cod_credito == "" || $monto <= 0) {
		mysqli_close($mysqli);
		cc_json(array("1" => "camposvacio", "2" => "Faltan datos para conciliar con Ueno"));
	}

	$mysqli->begin_transaction();
	try {
		$compSql = $mysqli->real_escape_string($comprobante);
		$creditoSql = $mysqli->real_escape_string($cod_credito);
		$sqlPago = "SELECT pc.id, pc.cod_pagoFK, pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion,
			IFNULL((SELECT SUM(ump.monto_aplicado) FROM ueno_movimiento_pago ump WHERE ump.cod_pagoFK=pc.cod_pagoFK AND ump.estado='activo'),0) AS monto_aplicado
			FROM pago_transferencia_conciliacion pc
			INNER JOIN pago p ON p.idPago=pc.cod_pagoFK
			WHERE pc.activo='SI'
			AND pc.nro_comprobante_informado='$compSql'
			AND p.cod_creditoFK='$creditoSql'
			AND pc.estado_conciliacion IN ('pendiente_conciliacion','observado')
			ORDER BY pc.id ASC
			FOR UPDATE";
		$resPago = $mysqli->query($sqlPago);
		if (!$resPago || $resPago->num_rows == 0) {
			throw new Exception("El pago fue registrado, pero no encontramos el control Ueno pendiente para vincularlo");
		}

		$idMovSql = $mysqli->real_escape_string($id_movimiento);
		$resMov = $mysqli->query("SELECT * FROM ueno_movimiento_bancario WHERE id_movimiento='$idMovSql' AND tipo_movimiento='credito' FOR UPDATE");
		$movimiento = $resMov ? $resMov->fetch_assoc() : null;
		if (!$movimiento) {
			throw new Exception("No se encontro el movimiento Ueno seleccionado");
		}
		if (cc_comprobante_normalizado($movimiento["nro_comprobante"]) !== cc_comprobante_normalizado($comprobante)) {
			throw new Exception("El comprobante ingresado no coincide con el movimiento Ueno seleccionado. Debe revisarse desde conciliacion.");
		}
		$estadoMovimiento = strtolower(trim(cc_from_db($movimiento["estado"])));
		if (in_array($estadoMovimiento, array("conciliado", "conciliada", "asignado_total", "anulado", "anulada", "rechazado", "rechazada"))) {
			throw new Exception("El movimiento Ueno seleccionado ya no esta disponible.");
		}
		if ((int)$movimiento["monto_disponible"] <= 0) {
			throw new Exception("El movimiento Ueno seleccionado ya no tiene saldo disponible.");
		}

		$yaAplicado = 0;
		if (cc_tabla_existe($mysqli, "ueno_movimiento_pago")) {
			$resYaAplicado = $mysqli->query("SELECT COUNT(*) AS total
				FROM ueno_movimiento_pago ump
				INNER JOIN pago p ON p.idPago=ump.cod_pagoFK
				WHERE ump.estado='activo'
				AND ump.id_movimiento='$idMovSql'
				AND p.cod_creditoFK='$creditoSql'");
			if ($resYaAplicado) {
				$rowYaAplicado = $resYaAplicado->fetch_assoc();
				$yaAplicado = (int)$rowYaAplicado["total"];
			}
		}
		if ($yaAplicado > 0) {
			throw new Exception("Este movimiento Ueno ya fue aplicado a esta cuota. No se desconto nuevamente.");
		}

		$monto_restante = $monto;
		$monto_total_aplicado = 0;
		while ($pago = $resPago->fetch_assoc()) {
			if ($monto_restante <= 0 || (int)$movimiento["monto_disponible"] <= 0) {
				break;
			}
			$saldo_pago = (int)$pago["monto_pago"] - (int)$pago["monto_aplicado"];
			$monto_aplicar = min($monto_restante, $saldo_pago, (int)$movimiento["monto_disponible"]);
			if ($monto_aplicar <= 0) {
				continue;
			}
			$ok = ueno_conciliar_pago_con_movimiento(
				$mysqli,
				$pago,
				$movimiento,
				$usuario,
				"Vinculado desde Cobrar cuota",
				"Pago registrado desde Cobrar cuota y conciliado con Banco Ueno",
				$monto_aplicar
			);
			if (!$ok) {
				continue;
			}
			$monto_total_aplicado += $monto_aplicar;
			$monto_restante -= $monto_aplicar;
			$movimiento["monto_disponible"] = (int)$movimiento["monto_disponible"] - $monto_aplicar;
		}

		if ($monto_total_aplicado <= 0) {
			throw new Exception("No hay saldo disponible para conciliar este pago");
		}

		$mysqli->commit();
		mysqli_close($mysqli);
		if ($monto_total_aplicado < $monto) {
			cc_json(array(
				"1" => "pendiente",
				"2" => "Se conciliaron " . cc_numero($monto_total_aplicado) . " Gs.; el resto queda pendiente de conciliacion bancaria.",
				"monto_disponible" => (int)$movimiento["monto_disponible"],
				"monto_disponible_fmt" => cc_numero($movimiento["monto_disponible"])
			));
		}
		cc_json(array(
			"1" => "exito",
			"2" => "Pago registrado y conciliado con Banco Ueno",
			"3" => cc_numero($monto_total_aplicado),
			"monto_disponible" => (int)$movimiento["monto_disponible"],
			"monto_disponible_fmt" => cc_numero($movimiento["monto_disponible"])
		));
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		cc_json(array("1" => "pendiente", "2" => $e->getMessage()));
	}
}

$operacion = isset($_POST["funt"]) ? $_POST["funt"] : "";
$usuario = cc_validar_sesion();

if ($operacion == "buscar_cuotas") {
	cc_buscar_cuotas($usuario);
}
if ($operacion == "buscar_clientes") {
	cc_buscar_clientes_cobro($usuario);
}
if ($operacion == "listar_planes") {
	cc_listar_planes_cliente($usuario);
}
if ($operacion == "listar_cuotas") {
	cc_listar_cuotas_venta($usuario);
}
if ($operacion == "buscar_movimiento_ueno") {
	cc_buscar_movimiento_ueno($usuario);
}
if ($operacion == "auditar_cobro") {
	cc_auditar_cobro($usuario);
}
if ($operacion == "conciliar_transferencia") {
	cc_conciliar_transferencia($usuario);
}

cc_json(array("1" => "operacioninvalida"));
?>

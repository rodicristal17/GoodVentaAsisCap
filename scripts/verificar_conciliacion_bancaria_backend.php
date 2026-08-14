<?php
// Verificacion no persistente para la extension multi-banco de conciliacion.
$raiz = dirname(__DIR__);
chdir($raiz . DIRECTORY_SEPARATOR . "php_system");
require_once($raiz . DIRECTORY_SEPARATOR . "php_system" . DIRECTORY_SEPARATOR . "abmConciliacionUeno.php");

function verificar_condicion($condicion, $mensaje)
{
	if (!$condicion) {
		throw new Exception($mensaje);
	}
}

$mysqli = conectar_al_servidor();
verificar_condicion(ueno_tablas_requeridas_ok($mysqli), "El esquema de conciliacion bancaria no esta actualizado");
verificar_condicion(ueno_banco_filtro_codigo("TODOS") === "", "El filtro Todos no habilita la vista combinada");
verificar_condicion(ueno_banco_filtro_codigo("UENO") === "UENO", "El filtro Ueno no conserva su banco");
verificar_condicion(ueno_banco_filtro_codigo("FAMILIAR") === "FAMILIAR", "El filtro Familiar no conserva su banco");
verificar_condicion(strpos(ueno_banco_badge_html("UENO"), "ueno-bank-badge--ueno") !== false, "Falta la insignia Ueno");
verificar_condicion(strpos(ueno_banco_badge_html("FAMILIAR"), "ueno-bank-badge--familiar") !== false, "Falta la insignia Banco Familiar");
verificar_condicion(ueno_hash_archivo_valido(str_repeat("a", 64)), "El servidor rechazo una huella SHA-256 valida");
verificar_condicion(!ueno_hash_archivo_valido("simple-12345"), "El servidor acepto una huella simplificada insegura");
verificar_condicion(ueno_entero_nullable("") === null, "La auditoria no convierte el entero vacio a NULL");
verificar_condicion(ueno_entero_nullable(null) === null, "La auditoria no conserva el entero NULL");
verificar_condicion(ueno_entero_nullable("25") === 25, "La auditoria no normaliza un identificador numerico");
$mesaTodos = ueno_tabla_movimientos($mysqli, "", "", "", "", "", "todos", 0, "todos", 2, "");
$mesaUeno = ueno_tabla_movimientos($mysqli, "", "", "", "", "", "todos", 0, "todos", 2, "UENO");
verificar_condicion($mesaTodos["total"] >= $mesaUeno["total"], "La mesa combinada excluye movimientos Ueno");
verificar_condicion(strpos($mesaTodos["html"], "ueno-bank-badge") !== false || $mesaTodos["total"] === 0, "La mesa combinada no identifica el banco por fila");
$lockPrueba = (int)ueno_scalar($mysqli, "SELECT GET_LOCK('telar_conciliacion_bancaria_verificador',0)");
verificar_condicion($lockPrueba === 1, "MySQL no pudo adquirir el bloqueo de cuenta bancaria");
verificar_condicion((int)ueno_scalar($mysqli, "SELECT RELEASE_LOCK('telar_conciliacion_bancaria_verificador')") === 1, "MySQL no libero el bloqueo de cuenta bancaria");

$resultModoSql = $mysqli->query("SELECT @@SESSION.sql_mode AS sql_mode");
verificar_condicion($resultModoSql !== false, "No se pudo consultar el modo SQL de la sesion");
$filaModoSql = $resultModoSql->fetch_assoc();
$modoSqlOriginal = $filaModoSql ? (string)$filaModoSql["sql_mode"] : "";
$conteoAuditoriaAntes = (int)ueno_scalar($mysqli, "SELECT COUNT(*) FROM ueno_auditoria_conciliacion");
$marcadorAuditoria = "verificador-" . uniqid("", true);
$transaccionAuditoriaActiva = false;
try {
	verificar_condicion($mysqli->query("SET SESSION sql_mode='STRICT_TRANS_TABLES'") !== false, "No se pudo activar el modo SQL estricto");
	verificar_condicion($mysqli->begin_transaction(), "No se pudo iniciar la prueba transaccional de auditoria");
	$transaccionAuditoriaActiva = true;
	foreach (array("UENO", "FAMILIAR") as $bancoAuditoria) {
		ueno_auditar_conciliacion(
			$mysqli,
			"VERIFICAR_IMPORTACION_" . $bancoAuditoria,
			"verificador_conciliacion",
			$marcadorAuditoria . "-" . $bancoAuditoria,
			"",
			"",
			"",
			"importado",
			100,
			2,
			"Verificacion transaccional no persistente",
			array("banco_codigo" => $bancoAuditoria)
		);
	}
	$marcadorSql = $mysqli->real_escape_string($marcadorAuditoria . "-%");
	$resultAuditoria = $mysqli->query("SELECT COUNT(*) AS total,
		SUM(CASE WHEN cod_pagoFK IS NULL THEN 1 ELSE 0 END) AS pagos_null,
		SUM(CASE WHEN id_movimiento IS NULL THEN 1 ELSE 0 END) AS movimientos_null
		FROM ueno_auditoria_conciliacion
		WHERE tabla_afectada='verificador_conciliacion' AND registro_id LIKE '$marcadorSql'");
	verificar_condicion($resultAuditoria !== false, "No se pudo consultar la auditoria transaccional");
	$filaAuditoria = $resultAuditoria->fetch_assoc();
	verificar_condicion((int)$filaAuditoria["total"] === 2, "No se registraron ambas auditorias bancarias en modo estricto");
	verificar_condicion((int)$filaAuditoria["pagos_null"] === 2, "cod_pagoFK vacio no se guardo como NULL");
	verificar_condicion((int)$filaAuditoria["movimientos_null"] === 2, "id_movimiento vacio no se guardo como NULL");
	verificar_condicion($mysqli->rollback(), "No se pudo revertir la prueba transaccional de auditoria");
	$transaccionAuditoriaActiva = false;
} catch (Throwable $e) {
	if ($transaccionAuditoriaActiva) {
		$mysqli->rollback();
	}
	$modoSqlRestaurar = $mysqli->real_escape_string($modoSqlOriginal);
	$mysqli->query("SET SESSION sql_mode='$modoSqlRestaurar'");
	throw $e;
}
$modoSqlRestaurar = $mysqli->real_escape_string($modoSqlOriginal);
verificar_condicion($mysqli->query("SET SESSION sql_mode='$modoSqlRestaurar'") !== false, "No se pudo restaurar el modo SQL de la sesion");
$conteoAuditoriaDespues = (int)ueno_scalar($mysqli, "SELECT COUNT(*) FROM ueno_auditoria_conciliacion");
verificar_condicion($conteoAuditoriaAntes === $conteoAuditoriaDespues, "La prueba de auditoria dejo filas persistidas");

$movimientos = array(
	array(
		"fecha_confirmacion" => "2026-08-07",
		"fecha_transaccion" => "2026-08-07",
		"nro_comprobante" => "PRUEBA-CREDITO",
		"descripcion" => "Movimiento sintetico",
		"concepto" => "Verificacion",
		"importe_debito" => 0,
		"importe_credito" => 500,
		"saldo_banco" => 1500
	),
	array(
		"fecha_confirmacion" => "2026-08-08",
		"fecha_transaccion" => "2026-08-08",
		"nro_comprobante" => "PRUEBA-DEBITO",
		"descripcion" => "Movimiento sintetico",
		"concepto" => "Verificacion",
		"importe_debito" => 200,
		"importe_credito" => 0,
		"saldo_banco" => 1300
	)
);

$cuentaPrueba = "999000111";
$resultCuenta = $mysqli->query("SELECT cuenta FROM ueno_importacion_extracto WHERE banco_codigo='FAMILIAR' ORDER BY id_importacion ASC LIMIT 1");
if ($resultCuenta && ($filaCuenta = $resultCuenta->fetch_assoc()) && trim((string)$filaCuenta["cuenta"]) !== "") {
	$cuentaPrueba = (string)$filaCuenta["cuenta"];
}
$ueno = ueno_normalizar_movimientos_importacion($movimientos, $cuentaPrueba, "UENO");
$familiar = ueno_normalizar_movimientos_importacion($movimientos, $cuentaPrueba, "FAMILIAR");
verificar_condicion($ueno["cantidad_movimientos"] === 2 && $familiar["cantidad_movimientos"] === 2, "La normalizacion no conservo los movimientos");
verificar_condicion($familiar["total_creditos"] === 500 && $familiar["total_debitos"] === 200, "Los totales normalizados no coinciden");
verificar_condicion($ueno["normalizados"][0]["hash_movimiento"] !== $familiar["normalizados"][0]["hash_movimiento"], "El hash de movimiento no esta separado por banco");

$_POST["moneda_codigo"] = "PYG";
$_POST["tipo_cuenta"] = "CUENTA_CORRIENTE";
$_POST["saldo_anterior"] = "1000";
$_POST["saldo_final"] = "1300";
$_POST["total_creditos_declarado"] = "500";
$_POST["total_debitos_declarado"] = "200";
$control = ueno_validar_totales_familiar($mysqli, $cuentaPrueba, $familiar);
verificar_condicion($control["saldo_final"] === 1300, "La ecuacion de saldo de Banco Familiar no fue validada");

$falloControlado = false;
$_POST["total_debitos_declarado"] = "201";
try {
	ueno_validar_totales_familiar($mysqli, $cuentaPrueba, $familiar);
} catch (Exception $e) {
	$falloControlado = true;
}
verificar_condicion($falloControlado, "Un total alterado no fue rechazado");

$_POST["total_debitos_declarado"] = "200";
$familiarSaldoAlterado = $familiar;
$familiarSaldoAlterado["normalizados"][0]["saldo_banco"] = 1499;
$falloSaldoSecuencial = false;
try {
	ueno_validar_totales_familiar($mysqli, $cuentaPrueba, $familiarSaldoAlterado);
} catch (Exception $e) {
	$falloSaldoSecuencial = true;
}
verificar_condicion($falloSaldoSecuencial, "Un saldo secuencial alterado no fue rechazado");

$conteos = array();
foreach (array("ueno_importacion_extracto", "ueno_movimiento_bancario", "pago_transferencia_conciliacion") as $tabla) {
	$result = $mysqli->query("SELECT COUNT(*) AS total,
		SUM(CASE WHEN banco_codigo='UENO' THEN 1 ELSE 0 END) AS ueno,
		SUM(CASE WHEN banco_codigo='FAMILIAR' THEN 1 ELSE 0 END) AS familiar
		FROM `$tabla`");
	verificar_condicion($result !== false, "No se pudo verificar la tabla " . $tabla);
	$fila = $result->fetch_assoc();
	verificar_condicion((int)$fila["total"] === (int)$fila["ueno"] + (int)$fila["familiar"], "Existe una clasificacion bancaria inesperada en " . $tabla);
	$conteos[$tabla] = array("total" => (int)$fila["total"], "ueno" => (int)$fila["ueno"], "familiar" => (int)$fila["familiar"]);
}

mysqli_close($mysqli);
echo json_encode(array(
	"resultado" => "OK",
	"esquema" => "multi_banco",
	"vista_combinada_por_defecto" => true,
	"insignias_bancarias" => true,
	"mesa_combinada" => array("total" => $mesaTodos["total"], "ueno" => $mesaUeno["total"]),
	"hash_separado_por_banco" => true,
	"bloqueo_cuenta_familiar" => true,
	"auditoria_estricta_nullable" => true,
	"prueba_auditoria_revertida" => true,
	"totales_familiar_validados" => true,
	"saldos_secuenciales_validados" => true,
	"clasificacion_por_banco" => $conteos
), JSON_PRETTY_PRINT) . PHP_EOL;
?>

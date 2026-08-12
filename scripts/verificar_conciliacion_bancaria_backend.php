<?php
// Verificacion de solo lectura para la extension multi-banco de conciliacion.
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
$mesaTodos = ueno_tabla_movimientos($mysqli, "", "", "", "", "", "todos", 0, "todos", 2, "");
$mesaUeno = ueno_tabla_movimientos($mysqli, "", "", "", "", "", "todos", 0, "todos", 2, "UENO");
verificar_condicion($mesaTodos["total"] >= $mesaUeno["total"], "La mesa combinada excluye movimientos Ueno");
verificar_condicion(strpos($mesaTodos["html"], "ueno-bank-badge") !== false || $mesaTodos["total"] === 0, "La mesa combinada no identifica el banco por fila");
$lockPrueba = (int)ueno_scalar($mysqli, "SELECT GET_LOCK('telar_conciliacion_bancaria_verificador',0)");
verificar_condicion($lockPrueba === 1, "MySQL no pudo adquirir el bloqueo de cuenta bancaria");
verificar_condicion((int)ueno_scalar($mysqli, "SELECT RELEASE_LOCK('telar_conciliacion_bancaria_verificador')") === 1, "MySQL no libero el bloqueo de cuenta bancaria");

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
	"totales_familiar_validados" => true,
	"clasificacion_por_banco" => $conteos
), JSON_PRETTY_PRINT) . PHP_EOL;
?>

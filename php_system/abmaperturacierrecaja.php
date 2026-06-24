<?php
include_once('quitarseparadormiles.php');
include_once("buscar_nivel.php");
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("classTable.php");
include_once("abmpagos.php");

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok" && $operacion!="buscaroption"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}




if($operacion=="nuevo" || $operacion=="editar")
{
$idarqueocaja=$_POST['idarqueocaja'];
$idarqueocaja = mb_convert_encoding((string)($idarqueocaja), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$montoapertura=$_POST['montoapertura'];
$montoapertura = quitarseparadormiles($montoapertura);
$montocierre=$_POST['montocierre'];
$montocierre = quitarseparadormiles($montocierre);
$fechaapertura=$_POST['fechaapertura'];
$fechacierre=$_POST['fechacierre'];
$caja_idcaja=$_POST['caja_idcaja'];
$caja_idcaja = mb_convert_encoding((string)($caja_idcaja), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$codusuarioap=$_POST['codusuarioap'];
$codusuarioap = mb_convert_encoding((string)($codusuarioap), 'ISO-8859-1', 'UTF-8');
$codusuarioce = $user;
abmAperturaCierre($idarqueocaja,$cod_local,$caja_idcaja,$montoapertura,$montocierre,$fechaapertura,$fechacierre,$estado,$codusuarioap,$codusuarioce,$operacion);

}

if($operacion=="controldecaja")
{
$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$Usuario=$_POST['Usuario'];
$Usuario = mb_convert_encoding((string)($Usuario), 'ISO-8859-1', 'UTF-8');

$informacion= controldecaja($buscar,$cod_local,$Usuario);
echo json_encode($informacion);
exit;
}

if($operacion=="buscar_recaudo_opciones_pago")
{
$idArqeoFk1=$_POST['idArqeoFk1'];
$idArqeoFk1 = mb_convert_encoding((string)($idArqeoFk1), 'ISO-8859-1', 'UTF-8');

buscar_recaudo_opciones_pago($idArqeoFk1);

}	

if($operacion=="buscarmoviemientocaja")
{
$idArqeoFk=$_POST['idArqeoFk'];
$idArqeoFk = mb_convert_encoding((string)($idArqeoFk), 'ISO-8859-1', 'UTF-8');
buscarmoviemientocaja($idArqeoFk);

}	

if($operacion=="buscar_conciliaciones_ueno_cierre")
{
$idArqeoFk=$_POST['idArqeoFk'];
$idArqeoFk = mb_convert_encoding((string)($idArqeoFk), 'ISO-8859-1', 'UTF-8');
$informacion = caja_cierre_buscar_conciliaciones_ueno($idArqeoFk,$user);
echo json_encode($informacion);
exit;
}

if($operacion=="buscarvista")
{
$caja=$_POST['caja'];
$caja = mb_convert_encoding((string)($caja), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
$fechaapertura=$_POST['fechaapertura'];
$fechaapertura = mb_convert_encoding((string)($fechaapertura), 'ISO-8859-1', 'UTF-8');
$fechafin=$_POST['fechafin'];
$fechafin = mb_convert_encoding((string)($fechafin), 'ISO-8859-1', 'UTF-8');
$usuario=$_POST['usuario'];
$usuario = mb_convert_encoding((string)($usuario), 'ISO-8859-1', 'UTF-8');
$lote = $_POST['lote'];
$lote = mb_convert_encoding((string)($lote), 'ISO-8859-1', 'UTF-8');

$informacion = buscarvista($fechaapertura,$fechafin,$caja,$estado,$local,$usuario,$lote);
echo json_encode($informacion);	
exit;
}

if($operacion=="buscarcajaapp")
{
$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$cobrador=$_POST['cobrador'];
$cobrador = mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
buscarcajaapp($fecha1,$fecha2,$cobrador,$estado);

}	

}

function abmAperturaCierre($idarqueocaja,$cod_local,$caja_idcaja,$montoapertura,$montocierre,$fechaapertura,$fechacierre,$estado,$codusuarioap,$codusuarioce,$operacion)
{
	$mysqli=conectar_al_servidor();

	if($operacion=="nuevo")
	{
		$lote="LOTE ".obternerLote($cod_local,$codusuarioap);

		$consulta1="Insert into arqueocaja (cod_local,caja_idcaja,montoapertura,fechaapertura,estado,codusuarioap,montocierre,lote)
		values(?,?,?,?,?,?,'0',?)";
		$stmt1 = $mysqli->prepare($consulta1);
		$ss='sssssss';
		$stmt1->bind_param($ss,$cod_local,$caja_idcaja,$montoapertura,$fechaapertura,$estado,$codusuarioap,$lote);

		if (!$stmt1->execute()) {
			echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
			exit;
		}

		mysqli_close($mysqli);
		$informacion =array("1" => "exito","2" => $lote);
		echo json_encode($informacion);
		exit;
	}

	if($operacion=="editar")
	{
		$sql= "SELECT COUNT(*) AS total FROM gastos WHERE estado='solicitado' AND cod_usuario=?";
		$stmt = $mysqli->prepare($sql);
		$ss='s';
		$stmt->bind_param($ss,$codusuarioce);
		if ( ! $stmt->execute()) {
			echo "Error";
			exit;
		}
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$valor = (int)$row['total'];
		if ($valor > 0) {
			echo json_encode(array("1" => "error", "2" => "No se puede cerrar la caja", "3" => "Existen $valor Egresos / Ingresos que necesitan aprobacion."));
			exit;
		}

		try {
			caja_cierre_requerir_tablas($mysqli);

			$payload = caja_cierre_obtener_payload();
			$infoCaja = caja_cierre_obtener_arqueo($mysqli, $idarqueocaja);
			if (!$infoCaja) {
				throw new Exception("No se encontro la caja a cerrar.");
			}
			if ($infoCaja['estado'] != "Activo") {
				throw new Exception("Este lote de caja ya esta cerrado. No se permite cerrar dos veces.");
			}

			$cantidades = caja_cierre_obtener_denominaciones_post();
			$montocierre = caja_cierre_total_denominaciones($cantidades);
			$resumen = caja_cierre_calcular_resumen_medios($mysqli, $idarqueocaja, $infoCaja['montoapertura']);
			$efectivoEsperado = (int)$resumen['efectivo_esperado'];
			$diferencia = $montocierre - $efectivoEsperado;
			$motivoDiferencia = isset($payload['motivo_diferencia']) ? trim((string)$payload['motivo_diferencia']) : "";
			$observacionDiferencia = isset($payload['observacion_diferencia']) ? trim((string)$payload['observacion_diferencia']) : "";

			if ($efectivoEsperado > 0 && $montocierre == 0) {
				throw new Exception("Debe cargar el conteo por denominacion antes de cerrar.");
			}
			if ($diferencia != 0 && $motivoDiferencia == "") {
				throw new Exception("Debe seleccionar un motivo para cerrar con diferencia.");
			}
			if ($diferencia != 0 && strtoupper($motivoDiferencia) == "OTRO" && $observacionDiferencia == "") {
				throw new Exception("Debe detallar la observacion cuando el motivo es Otro.");
			}

			caja_cierre_validar_foto();
			caja_cierre_validar_firma();

			$archivosGuardados = array();
			$mysqli->begin_transaction();

			$consulta1="Update arqueocaja set codusuarioce=?,montocierre=?,fechacierre=?,estado='Cerrado',cant500= ?,cant1000= ?,cant2000= ?,cant5000= ?,cant10000= ?,cant20000= ?,cant50000= ?,cant100000= ? where idarqueocaja=? and estado='Activo'";
			$stmt1 = $mysqli->prepare($consulta1);
			$ss='ssssssssssss';
			$cant500 = $cantidades[500];
			$cant1000 = $cantidades[1000];
			$cant2000 = $cantidades[2000];
			$cant5000 = $cantidades[5000];
			$cant10000 = $cantidades[10000];
			$cant20000 = $cantidades[20000];
			$cant50000 = $cantidades[50000];
			$cant100000 = $cantidades[100000];
			$stmt1->bind_param($ss,$codusuarioce,$montocierre,$fechacierre,$cant500,$cant1000,$cant2000,$cant5000,$cant10000,$cant20000,$cant50000,$cant100000,$idarqueocaja);

			if (!$stmt1->execute()) {
				throw new Exception('No se pudo cerrar la caja: '.$stmt1->error);
			}
			if ($stmt1->affected_rows <= 0) {
				throw new Exception("Este lote de caja ya esta cerrado. No se permite cerrar dos veces.");
			}

			$idCierre = caja_cierre_insertar_cierre($mysqli, $idarqueocaja, $infoCaja, $codusuarioce, $fechacierre, $resumen, $montocierre, $diferencia, $motivoDiferencia, $observacionDiferencia);
			caja_cierre_insertar_denominaciones($mysqli, $idCierre, $cantidades);

			$foto = caja_cierre_guardar_foto($idCierre);
			$archivosGuardados[] = $foto['path_abs'];
			caja_cierre_insertar_evidencia($mysqli, $idCierre, $foto, $codusuarioce);

			$firma = caja_cierre_guardar_firma($idCierre);
			$archivosGuardados[] = $firma['path_abs'];
			caja_cierre_insertar_firma($mysqli, $idCierre, $firma, $codusuarioce, $infoCaja['usuarioap'], $payload);

			caja_cierre_actualizar_rutas($mysqli, $idCierre, $foto['path_rel'], $firma['path_rel']);
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "INICIO_CIERRE", "Inicio de cierre guiado", "", json_encode($payload));
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "CONTEO_GUARDADO", "Conteo por denominacion guardado", "", json_encode($cantidades));
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "RESUMEN_GENERADO", "Resumen comparativo generado", "", json_encode($resumen));
			if ($diferencia != 0) {
				caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "DIFERENCIA_DETECTADA", $motivoDiferencia." ".$observacionDiferencia, $efectivoEsperado, $montocierre);
			}
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "FOTO_ADJUNTA", "Foto de cierre adjunta", "", $foto['path_rel']);
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "FIRMA_REGISTRADA", "Firma manuscrita interna registrada", "", $firma['path_rel']);
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "CIERRE_CONFIRMADO", caja_cierre_estado_visual($diferencia), $efectivoEsperado, $montocierre);
			caja_cierre_auditar($mysqli, $idCierre, $infoCaja['lote'], $codusuarioce, "LOTE_BLOQUEADO", "Lote cerrado y bloqueado para edicion directa", "Activo", "Cerrado");

			$mysqli->commit();
			mysqli_close($mysqli);

			$informacion =array(
				"1" => "exito",
				"2" => $infoCaja['lote'],
				"3" => $idCierre,
				"4" => caja_cierre_estado_visual($diferencia),
				"5" => caja_cierre_estado_revision($diferencia),
				"6" => number_format($efectivoEsperado,'0',',','.'),
				"7" => number_format($montocierre,'0',',','.'),
				"8" => number_format($diferencia,'0',',','.')
			);
			echo json_encode($informacion);
			exit;
		} catch (Exception $e) {
			@$mysqli->rollback();
			if (isset($archivosGuardados)) {
				foreach ($archivosGuardados as $archivo) {
					if ($archivo != "" && file_exists($archivo)) {
						@unlink($archivo);
					}
				}
			}
			mysqli_close($mysqli);
			echo json_encode(array("1" => "error", "2" => "No se pudo cerrar la caja", "3" => $e->getMessage()));
			exit;
		}
	}
}

function caja_cierre_tabla_existe($mysqli, $tabla)
{
	$tabla = $mysqli->real_escape_string($tabla);
	$result = $mysqli->query("SHOW TABLES LIKE '".$tabla."'");
	return ($result && $result->num_rows > 0);
}

function caja_cierre_requerir_tablas($mysqli)
{
	$tablas = array("caja_cierres", "caja_cierre_denominaciones", "caja_cierre_evidencias", "caja_cierre_firmas", "caja_cierre_auditoria");
	foreach ($tablas as $tabla) {
		if (!caja_cierre_tabla_existe($mysqli, $tabla)) {
			throw new Exception("Falta ejecutar la migracion actualizacion_12062026_cierre_caja_seguro.sql.");
		}
	}
}

function caja_cierre_obtener_payload()
{
	if (!isset($_POST['cierre_seguro_json']) || trim((string)$_POST['cierre_seguro_json']) == "") {
		throw new Exception("Falta el resumen seguro del cierre.");
	}
	$payload = json_decode((string)$_POST['cierre_seguro_json'], true);
	if (!is_array($payload)) {
		throw new Exception("El resumen seguro del cierre no es valido.");
	}
	return $payload;
}

function caja_cierre_entero($valor)
{
	$valor = preg_replace('/[^0-9-]/', '', (string)$valor);
	if ($valor === "" || $valor === "-") {
		return 0;
	}
	$numero = (int)$valor;
	if ($numero < 0) {
		throw new Exception("No se permiten cantidades negativas en denominaciones.");
	}
	return $numero;
}

function caja_cierre_obtener_denominaciones_post()
{
	return array(
		500 => caja_cierre_entero(isset($_POST['cant500']) ? $_POST['cant500'] : 0),
		1000 => caja_cierre_entero(isset($_POST['cant1000']) ? $_POST['cant1000'] : 0),
		2000 => caja_cierre_entero(isset($_POST['cant2000']) ? $_POST['cant2000'] : 0),
		5000 => caja_cierre_entero(isset($_POST['cant5000']) ? $_POST['cant5000'] : 0),
		10000 => caja_cierre_entero(isset($_POST['cant10000']) ? $_POST['cant10000'] : 0),
		20000 => caja_cierre_entero(isset($_POST['cant20000']) ? $_POST['cant20000'] : 0),
		50000 => caja_cierre_entero(isset($_POST['cant50000']) ? $_POST['cant50000'] : 0),
		100000 => caja_cierre_entero(isset($_POST['cant100000']) ? $_POST['cant100000'] : 0)
	);
}

function caja_cierre_total_denominaciones($cantidades)
{
	$total = 0;
	foreach ($cantidades as $denominacion => $cantidad) {
		$total += ((int)$denominacion * (int)$cantidad);
	}
	return $total;
}

function caja_cierre_obtener_arqueo($mysqli, $idarqueocaja)
{
	$consulta = "Select idarqueocaja,cod_local,caja_idcaja,montoapertura,fechaapertura,fechacierre,estado,lote,codusuarioap,
	(Select nombre_persona from persona where cod_persona=codusuarioap) as usuarioap
	from arqueocaja where idarqueocaja=? limit 1";
	$stmt = $mysqli->prepare($consulta);
	$ss = 's';
	$stmt->bind_param($ss, $idarqueocaja);
	if (!$stmt->execute()) {
		throw new Exception("No se pudo leer la caja.");
	}
	$result = $stmt->get_result();
	if ($result->num_rows == 0) {
		return null;
	}
	$row = $result->fetch_assoc();
	return array(
		'idarqueocaja' => $row['idarqueocaja'],
		'cod_local' => $row['cod_local'],
		'caja_idcaja' => $row['caja_idcaja'],
		'montoapertura' => (int)$row['montoapertura'],
		'fechaapertura' => $row['fechaapertura'],
		'fechacierre' => $row['fechacierre'],
		'estado' => $row['estado'],
		'lote' => $row['lote'],
		'codusuarioap' => $row['codusuarioap'],
		'usuarioap' => mb_convert_encoding((string)$row['usuarioap'], 'UTF-8', 'ISO-8859-1')
	);
}

function caja_cierre_sumar_sql($mysqli, $sql)
{
	$result = $mysqli->query($sql);
	if (!$result) {
		throw new Exception("No se pudo calcular el resumen del cierre.");
	}
	$row = $result->fetch_assoc();
	return (int)$row['total'];
}

function caja_cierre_sumar_pagos($mysqli, $idArqeoFk, $condicion = "")
{
	$idArqeoFk = $mysqli->real_escape_string($idArqeoFk);
	$where = "pg.Monto>0 and pg.codApertura='$idArqeoFk'";
	if ($condicion != "") {
		$where .= " and (".$condicion.")";
	}
	$sql = "select IFNULL(sum(pg.Monto),0) as total
	from pago pg
	left join tipopago tp on tp.cod_tipoPago=pg.cod_tipoPagoFK
	where ".$where;
	return caja_cierre_sumar_sql($mysqli, $sql);
}

function caja_cierre_sumar_gastos($mysqli, $idArqeoFk, $tipo)
{
	$idArqeoFk = $mysqli->real_escape_string($idArqeoFk);
	$tipo = $mysqli->real_escape_string($tipo);
	$sql = "Select IFNULL(sum(monto),0) as total from gastos where codApertura='$idArqeoFk' and estado='Activo' and tipo='$tipo'";
	return caja_cierre_sumar_sql($mysqli, $sql);
}

function caja_cierre_sumar_migracion($mysqli, $idArqeoFk, $campo)
{
	$idArqeoFk = $mysqli->real_escape_string($idArqeoFk);
	$campo = $campo == "cod_caja_hastaFK" ? "cod_caja_hastaFK" : "cod_caja_desdeFK";
	$sql = "Select IFNULL(sum(monto),0) as total from migrar_caja where $campo='$idArqeoFk'";
	return caja_cierre_sumar_sql($mysqli, $sql);
}

function caja_cierre_transferencias_conciliadas($mysqli, $idArqeoFk)
{
	if (!caja_cierre_tabla_existe($mysqli, "pago_transferencia_conciliacion")) {
		return 0;
	}
	if (!caja_cierre_tabla_existe($mysqli, "ueno_movimiento_pago") || !caja_cierre_tabla_existe($mysqli, "ueno_movimiento_bancario")) {
		return 0;
	}
	$idArqeoFk = $mysqli->real_escape_string($idArqeoFk);
	$fechaAperturaUeno = "COALESCE(STR_TO_DATE(NULLIF(NULLIF(CAST(ar.fechaapertura AS CHAR), ''), '0000-00-00 00:00:00'), '%Y-%m-%d %H:%i:%s'), '1000-01-01 00:00:00')";
	$fechaCierreUeno = "COALESCE(STR_TO_DATE(NULLIF(NULLIF(CAST(ar.fechacierre AS CHAR), ''), '0000-00-00 00:00:00'), '%Y-%m-%d %H:%i:%s'), '9999-12-31 23:59:59')";
	$sql = "select IFNULL(sum(ump.monto_aplicado),0) as total
	from arqueocaja ar
	inner join pago p on p.codApertura=ar.idarqueocaja
	inner join pago_transferencia_conciliacion pc on pc.cod_pagoFK=p.idPago
	inner join ueno_movimiento_pago ump on ump.cod_pagoFK=p.idPago
	inner join ueno_movimiento_bancario umb on umb.id_movimiento=ump.id_movimiento
	where ar.idarqueocaja='$idArqeoFk'
	and pc.activo='SI'
	and pc.estado_conciliacion IN ('conciliado_ueno','pendiente_conciliacion','parcial','parcialmente_conciliado')
	and ump.estado='activo'
	and ump.usuario_asocio=ar.codusuarioap
	and umb.tipo_movimiento='credito'
	and ump.fecha_hora_asociacion>=$fechaAperturaUeno
	and ump.fecha_hora_asociacion<=$fechaCierreUeno";
	return caja_cierre_sumar_sql($mysqli, $sql);
}

function caja_cierre_ueno_texto($valor)
{
	return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function caja_cierre_ueno_numero($valor)
{
	return number_format((int)$valor, 0, ',', '.');
}

function caja_cierre_ueno_usuario_puede_ver($usuario, $infoCaja)
{
	if ((string)$usuario == (string)$infoCaja['codusuarioap']) {
		return true;
	}
	if ((string)$usuario == "2") {
		return true;
	}
	if (!function_exists("controldeaccesoacasas")) {
		return false;
	}
	$permisos = array("VERCONSULTADECAJA", "VERCONCILIACIONUENO", "VERCIERRESTESORERIA");
	foreach ($permisos as $permiso) {
		if (controldeaccesoacasas($usuario, $permiso, " u.accion='SI' ") == 1) {
			return true;
		}
	}
	return false;
}

function caja_cierre_ueno_estado_visual($estadoConciliacion, $montoPago, $montoAplicado)
{
	$estado = strtolower(trim((string)$estadoConciliacion));
	if ($montoAplicado > 0 && $montoAplicado < $montoPago) {
		return "Parcial";
	}
	if ($estado == "conciliado_ueno" || $montoAplicado >= $montoPago) {
		return "Conciliada";
	}
	if ($estado == "pendiente_conciliacion") {
		return "Parcial";
	}
	if ($estado == "observado") {
		return "Observada";
	}
	if ($estado == "rechazado") {
		return "Rechazada";
	}
	if ($estado == "anulado") {
		return "Anulada";
	}
	return $estadoConciliacion;
}

function caja_cierre_ueno_tipo_aplicacion($montoAplicado, $montoPago, $montoCuota, $cuotasMovimiento)
{
	if ((int)$cuotasMovimiento > 1) {
		return "Aplicacion a varias cuotas";
	}
	if ((int)$montoAplicado < (int)$montoPago) {
		return "Pago parcial";
	}
	if ((int)$montoCuota > 0 && (int)$montoAplicado < (int)$montoCuota) {
		return "Pago parcial";
	}
	return "Cuota completa";
}

function caja_cierre_buscar_conciliaciones_ueno($idArqeoFk, $usuarioActual)
{
	$mysqli = conectar_al_servidor();
	$respuestaVacia = array(
		"1" => "exito",
		"resumen" => array(
			"operaciones" => 0,
			"cuotas" => 0,
			"total_aplicado" => 0,
			"total_aplicado_texto" => "0"
		),
		"filas" => array()
	);

	if (!caja_cierre_tabla_existe($mysqli, "pago_transferencia_conciliacion") || !caja_cierre_tabla_existe($mysqli, "ueno_movimiento_pago") || !caja_cierre_tabla_existe($mysqli, "ueno_movimiento_bancario")) {
		mysqli_close($mysqli);
		$respuestaVacia["tablas_disponibles"] = "NO";
		return $respuestaVacia;
	}

	try {
		$infoCaja = caja_cierre_obtener_arqueo($mysqli, $idArqeoFk);
		if (!$infoCaja) {
			throw new Exception("No se encontro el lote de caja.");
		}
		if (!caja_cierre_ueno_usuario_puede_ver($usuarioActual, $infoCaja)) {
			mysqli_close($mysqli);
			return array("1" => "NI", "2" => "No tiene permiso para consultar las conciliaciones de este cierre.");
		}

		$fechaAperturaUeno = "COALESCE(STR_TO_DATE(NULLIF(NULLIF(CAST(ar.fechaapertura AS CHAR), ''), '0000-00-00 00:00:00'), '%Y-%m-%d %H:%i:%s'), '1000-01-01 00:00:00')";
		$fechaCierreUeno = "COALESCE(STR_TO_DATE(NULLIF(NULLIF(CAST(ar.fechacierre AS CHAR), ''), '0000-00-00 00:00:00'), '%Y-%m-%d %H:%i:%s'), '9999-12-31 23:59:59')";

		$sql = "SELECT
			ar.idarqueocaja, ar.lote, ar.codusuarioap, ar.fechaapertura, ar.fechacierre,
			ump.id AS id_asignacion, ump.id_movimiento, ump.cod_pagoFK, ump.monto_aplicado,
			ump.usuario_asocio, ump.fecha_hora_asociacion, ump.estado AS estado_asignacion,
			IFNULL(ump.observacion,'') AS observacion_asignacion,
			umb.nro_comprobante, umb.fecha_confirmacion, umb.fecha_transaccion,
			IFNULL(umb.descripcion,'') AS descripcion_banco, IFNULL(umb.concepto,'') AS concepto_banco,
			umb.importe_credito, umb.monto_disponible, umb.estado AS estado_movimiento,
			pc.id AS id_control_ueno, pc.grupo_pago, pc.nro_comprobante_informado, pc.monto_pago,
			pc.estado_conciliacion, IFNULL(pc.observacion,'') AS observacion_conciliacion,
			p.idPago, p.Monto AS monto_pago_real, p.Fecha AS fecha_pago, p.tipo, p.tipopago, p.nrofactura,
			p.cod_venta_fk, p.cod_creditoFK, IFNULL(p.descripcion,'') AS descripcion_pago, IFNULL(p.titulocuota,'') AS titulocuota,
			IFNULL(cr.plazo,'') AS plazo, IFNULL(cr.Monto,0) AS monto_cuota, IFNULL(cr.descuento,0) AS descuento_cuota,
			IFNULL(vt.num_factura,'') AS num_factura, IFNULL(vt.puntoexpedicion,'') AS puntoexpedicion,
			IFNULL(vt.apodo,'') AS alias_venta, IFNULL(vt.cod_clienteFK,'') AS cod_clienteFK,
			IFNULL(pe.nombre_persona,'') AS paciente, IFNULL(cl.ci_cliente,'') AS cedula,
			IFNULL(usu.nombre_persona,'') AS usuario_conciliador,
			(SELECT COUNT(DISTINCT ump2.cod_pagoFK)
				FROM ueno_movimiento_pago ump2
				INNER JOIN pago p2 ON p2.idPago=ump2.cod_pagoFK
				WHERE ump2.id_movimiento=ump.id_movimiento
				AND ump2.estado='activo'
				AND p2.codApertura=ar.idarqueocaja
				AND ump2.usuario_asocio=ar.codusuarioap
			) AS cuotas_movimiento
			FROM arqueocaja ar
			INNER JOIN pago p ON p.codApertura=ar.idarqueocaja
			INNER JOIN ueno_movimiento_pago ump ON ump.cod_pagoFK=p.idPago
			INNER JOIN ueno_movimiento_bancario umb ON umb.id_movimiento=ump.id_movimiento
			INNER JOIN pago_transferencia_conciliacion pc ON pc.cod_pagoFK=p.idPago AND pc.activo='SI'
			LEFT JOIN credito cr ON cr.idcredito=p.cod_creditoFK
			LEFT JOIN venta vt ON vt.cod_venta=p.cod_venta_fk
			LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
			LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
			LEFT JOIN persona usu ON usu.cod_persona=ump.usuario_asocio
			WHERE ar.idarqueocaja=?
			AND ump.estado='activo'
			AND umb.tipo_movimiento='credito'
			AND ump.usuario_asocio=ar.codusuarioap
			AND ump.fecha_hora_asociacion>=$fechaAperturaUeno
			AND ump.fecha_hora_asociacion<=$fechaCierreUeno
			AND pc.estado_conciliacion IN ('conciliado_ueno','pendiente_conciliacion','parcial','parcialmente_conciliado')
			ORDER BY ump.fecha_hora_asociacion DESC, umb.nro_comprobante ASC, p.cod_venta_fk ASC, cr.plazo ASC, p.idPago ASC";
		$stmt = $mysqli->prepare($sql);
		if (!$stmt) {
			throw new Exception($mysqli->error);
		}
		$ss = 's';
		$stmt->bind_param($ss, $idArqeoFk);
		if (!$stmt->execute()) {
			throw new Exception($stmt->error);
		}
		$result = $stmt->get_result();
		$filas = array();
		$operaciones = array();
		$totalAplicado = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$montoAplicado = (int)$row['monto_aplicado'];
			$montoPago = (int)$row['monto_pago'];
			$montoCuota = (int)$row['monto_cuota'] - (int)$row['descuento_cuota'];
			if ($montoCuota < 0) {
				$montoCuota = 0;
			}
			$ventaNumero = trim((string)$row['puntoexpedicion']) != "" || trim((string)$row['num_factura']) != ""
				? trim((string)$row['puntoexpedicion'])."-".trim((string)$row['num_factura'])
				: (string)$row['cod_venta_fk'];
			$cuota = trim((string)$row['titulocuota']) != "" ? $row['titulocuota'] : (trim((string)$row['plazo']) != "" ? $row['plazo'] : $row['nrofactura']);
			$estadoVisual = caja_cierre_ueno_estado_visual($row['estado_conciliacion'], $montoPago, $montoAplicado);
			$operaciones[(string)$row['id_movimiento']] = true;
			$totalAplicado += $montoAplicado;
			$filas[] = array(
				"id_asignacion" => (int)$row['id_asignacion'],
				"id_movimiento" => (int)$row['id_movimiento'],
				"cod_pago" => (int)$row['cod_pagoFK'],
				"fecha_conciliacion" => caja_cierre_ueno_texto($row['fecha_hora_asociacion']),
				"fecha_banco" => caja_cierre_ueno_texto($row['fecha_confirmacion']),
				"comprobante" => caja_cierre_ueno_texto($row['nro_comprobante'] != "" ? $row['nro_comprobante'] : $row['nro_comprobante_informado']),
				"paciente" => caja_cierre_ueno_texto($row['paciente']),
				"cedula" => caja_cierre_ueno_texto($row['cedula']),
				"venta" => caja_cierre_ueno_texto($ventaNumero),
				"alias_venta" => caja_cierre_ueno_texto($row['alias_venta']),
				"cuota" => caja_cierre_ueno_texto($cuota),
				"monto_cuota" => $montoCuota,
				"monto_cuota_texto" => caja_cierre_ueno_numero($montoCuota),
				"monto_pago" => $montoPago,
				"monto_pago_texto" => caja_cierre_ueno_numero($montoPago),
				"monto_aplicado" => $montoAplicado,
				"monto_aplicado_texto" => caja_cierre_ueno_numero($montoAplicado),
				"tipo_aplicacion" => caja_cierre_ueno_texto(caja_cierre_ueno_tipo_aplicacion($montoAplicado, $montoPago, $montoCuota, $row['cuotas_movimiento'])),
				"usuario_conciliador" => caja_cierre_ueno_texto($row['usuario_conciliador']),
				"estado" => caja_cierre_ueno_texto($estadoVisual),
				"estado_raw" => caja_cierre_ueno_texto($row['estado_conciliacion']),
				"observacion" => caja_cierre_ueno_texto($row['observacion_asignacion'] != "" ? $row['observacion_asignacion'] : $row['observacion_conciliacion'])
			);
		}

		$respuesta = array(
			"1" => "exito",
			"resumen" => array(
				"id_arqueocaja" => $infoCaja['idarqueocaja'],
				"lote" => caja_cierre_ueno_texto($infoCaja['lote']),
				"usuario_cajero" => (string)$infoCaja['usuarioap'],
				"fecha_apertura" => caja_cierre_ueno_texto($infoCaja['fechaapertura']),
				"fecha_cierre" => caja_cierre_ueno_texto($infoCaja['fechacierre']),
				"operaciones" => count($operaciones),
				"cuotas" => count($filas),
				"total_aplicado" => $totalAplicado,
				"total_aplicado_texto" => caja_cierre_ueno_numero($totalAplicado)
			),
			"filas" => $filas
		);
		mysqli_close($mysqli);
		return $respuesta;
	} catch (Exception $e) {
		mysqli_close($mysqli);
		return array("1" => "error", "2" => $e->getMessage());
	}
}

function caja_cierre_calcular_resumen_medios($mysqli, $idArqeoFk, $montoInicio)
{
	$textoPago = "UPPER(COALESCE(tp.nombre, pg.tipopago, ''))";
	$totalPagos = caja_cierre_sumar_pagos($mysqli, $idArqeoFk);
	$pagosEfectivo = caja_cierre_sumar_pagos($mysqli, $idArqeoFk, "$textoPago LIKE '%EFECTIVO%'");
	$transferencias = caja_cierre_sumar_pagos($mysqli, $idArqeoFk, "$textoPago LIKE '%TRANSFER%'");
	$tarjetas = caja_cierre_sumar_pagos($mysqli, $idArqeoFk, "$textoPago LIKE '%TARJ%' OR $textoPago LIKE '%DEBITO%' OR $textoPago LIKE '%CREDITO%'");
	$billeteras = caja_cierre_sumar_pagos($mysqli, $idArqeoFk, "$textoPago LIKE '%BILLE%' OR $textoPago LIKE '%GIRO%' OR $textoPago LIKE '%QR%'");
	$otros = $totalPagos - ($pagosEfectivo + $transferencias + $tarjetas + $billeteras);
	if ($otros < 0) {
		$otros = 0;
	}
	$ingresos = caja_cierre_sumar_gastos($mysqli, $idArqeoFk, "Ingreso");
	$egresos = caja_cierre_sumar_gastos($mysqli, $idArqeoFk, "Egreso");
	$cajaRecibida = caja_cierre_sumar_migracion($mysqli, $idArqeoFk, "cod_caja_hastaFK");
	$cajaEnviada = caja_cierre_sumar_migracion($mysqli, $idArqeoFk, "cod_caja_desdeFK");
	$movimientoEfectivo = $pagosEfectivo + $ingresos + $cajaRecibida - $egresos - $cajaEnviada;
	$efectivoEsperado = (int)$montoInicio + $movimientoEfectivo;

	return array(
		'pagos_total' => $totalPagos,
		'pagos_efectivo' => $pagosEfectivo,
		'movimiento_efectivo' => $movimientoEfectivo,
		'efectivo_esperado' => $efectivoEsperado,
		'total_transferencias' => $transferencias,
		'total_transferencias_conciliadas' => caja_cierre_transferencias_conciliadas($mysqli, $idArqeoFk),
		'total_tarjetas' => $tarjetas,
		'total_billeteras' => $billeteras,
		'total_otros' => $otros,
		'total_ingresos' => $ingresos,
		'total_egresos' => $egresos,
		'total_caja_recibida' => $cajaRecibida,
		'total_caja_enviada' => $cajaEnviada
	);
}

function caja_cierre_estado_visual($diferencia)
{
	return ((int)$diferencia === 0) ? "Caja cuadrada" : "Caja con diferencia";
}

function caja_cierre_estado_revision($diferencia)
{
	$diferenciaAbs = abs((int)$diferencia);
	if ($diferenciaAbs == 0) {
		return "Cerrada";
	}
	if ($diferenciaAbs <= 5000) {
		return "Diferencia menor";
	}
	if ($diferenciaAbs <= 50000) {
		return "Pendiente de revision";
	}
	return "Pendiente de revision urgente";
}

function caja_cierre_validar_foto()
{
	if (!isset($_FILES['cierre_foto']) || !is_array($_FILES['cierre_foto'])) {
		throw new Exception("Debe adjuntar una foto del dinero contado.");
	}
	if ($_FILES['cierre_foto']['error'] !== UPLOAD_ERR_OK) {
		throw new Exception("No se pudo recibir la foto del cierre.");
	}
	if ((int)$_FILES['cierre_foto']['size'] > 5242880) {
		throw new Exception("La foto no puede superar 5 MB.");
	}
}

function caja_cierre_validar_firma()
{
	if (!isset($_POST['cierre_firma_base64']) || trim((string)$_POST['cierre_firma_base64']) == "") {
		throw new Exception("Debe registrar la firma manuscrita interna.");
	}
	if (strpos((string)$_POST['cierre_firma_base64'], "data:image/png;base64,") !== 0) {
		throw new Exception("La firma enviada no es valida.");
	}
}

function caja_cierre_crear_directorio($directorio)
{
	if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
		throw new Exception("No se pudo crear el directorio de evidencias.");
	}
}

function caja_cierre_guardar_foto($idCierre)
{
	$archivo = $_FILES['cierre_foto'];
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $archivo['tmp_name']);
	finfo_close($finfo);
	$permitidos = array(
		'image/jpeg' => 'jpg',
		'image/jpg' => 'jpg',
		'image/png' => 'png',
		'image/webp' => 'webp'
	);
	if (!isset($permitidos[$mime])) {
		throw new Exception("La foto debe ser JPG, PNG o WEBP.");
	}
	$dirAbs = realpath(__DIR__ . "/..");
	$directorio = $dirAbs . DIRECTORY_SEPARATOR . "archivos" . DIRECTORY_SEPARATOR . "cierres_caja" . DIRECTORY_SEPARATOR . "evidencias";
	caja_cierre_crear_directorio($directorio);
	$nombreArchivo = "cierre_".$idCierre."_".date("YmdHis").".".$permitidos[$mime];
	$destino = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
	if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
		throw new Exception("No se pudo guardar la foto del cierre.");
	}
	return array(
		'path_abs' => $destino,
		'path_rel' => "archivos/cierres_caja/evidencias/".$nombreArchivo,
		'nombre_original' => basename((string)$archivo['name']),
		'mime' => $mime,
		'size' => (int)$archivo['size']
	);
}

function caja_cierre_guardar_firma($idCierre)
{
	$firmaBase64 = (string)$_POST['cierre_firma_base64'];
	$base64 = substr($firmaBase64, strlen("data:image/png;base64,"));
	$binario = base64_decode($base64, true);
	if ($binario === false || strlen($binario) < 100) {
		throw new Exception("La firma enviada no es valida.");
	}
	$dirAbs = realpath(__DIR__ . "/..");
	$directorio = $dirAbs . DIRECTORY_SEPARATOR . "archivos" . DIRECTORY_SEPARATOR . "cierres_caja" . DIRECTORY_SEPARATOR . "firmas";
	caja_cierre_crear_directorio($directorio);
	$nombreArchivo = "firma_cierre_".$idCierre."_".date("YmdHis").".png";
	$destino = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
	if (file_put_contents($destino, $binario) === false) {
		throw new Exception("No se pudo guardar la firma del cierre.");
	}
	return array(
		'path_abs' => $destino,
		'path_rel' => "archivos/cierres_caja/firmas/".$nombreArchivo
	);
}

function caja_cierre_insertar_cierre($mysqli, $idarqueocaja, $infoCaja, $codusuarioce, $fechacierre, $resumen, $efectivoContado, $diferencia, $motivo, $observacion)
{
	$estadoCierre = caja_cierre_estado_visual($diferencia);
	$estadoRevision = caja_cierre_estado_revision($diferencia);
	$rutaVacia = "";
	$adjunto = "SI";
	$lote = $infoCaja['lote'];
	$codUsuarioCajera = $infoCaja['codusuarioap'];
	$idLocal = $infoCaja['cod_local'];
	$fechaInicio = $infoCaja['fechaapertura'];
	$efectivoEsperado = $resumen['efectivo_esperado'];
	$totalTransferencias = $resumen['total_transferencias'];
	$totalTransferenciasConciliadas = $resumen['total_transferencias_conciliadas'];
	$totalTarjetas = $resumen['total_tarjetas'];
	$totalBilleteras = $resumen['total_billeteras'];
	$totalOtros = $resumen['total_otros'];
	$consulta = "Insert into caja_cierres
	(id_arqueocaja,id_lote,id_usuario_cajera,id_local,fecha_inicio_lote,fecha_cierre,efectivo_esperado,efectivo_contado,diferencia_efectivo,total_transferencias,total_transferencias_conciliadas,total_tarjetas,total_billeteras,total_otros,estado_cierre,estado_revision,motivo_diferencia,observacion_diferencia,foto_adjunta,firma_adjunta,ruta_foto,ruta_firma,cerrado_por,cerrado_en,creado_en,actualizado_en)
	values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),NOW())";
	$stmt = $mysqli->prepare($consulta);
	$ss = 'sssssssssssssssssssssss';
	$stmt->bind_param(
		$ss,
		$idarqueocaja,
		$lote,
		$codUsuarioCajera,
		$idLocal,
		$fechaInicio,
		$fechacierre,
		$efectivoEsperado,
		$efectivoContado,
		$diferencia,
		$totalTransferencias,
		$totalTransferenciasConciliadas,
		$totalTarjetas,
		$totalBilleteras,
		$totalOtros,
		$estadoCierre,
		$estadoRevision,
		$motivo,
		$observacion,
		$adjunto,
		$adjunto,
		$rutaVacia,
		$rutaVacia,
		$codusuarioce
	);
	if (!$stmt->execute()) {
		throw new Exception("No se pudo guardar el cierre seguro: ".$stmt->error);
	}
	return $mysqli->insert_id;
}

function caja_cierre_insertar_denominaciones($mysqli, $idCierre, $cantidades)
{
	$consulta = "Insert into caja_cierre_denominaciones (id_cierre,denominacion,cantidad,subtotal) values(?,?,?,?)";
	$stmt = $mysqli->prepare($consulta);
	foreach ($cantidades as $denominacion => $cantidad) {
		$subtotal = (int)$denominacion * (int)$cantidad;
		$ss = 'ssss';
		$stmt->bind_param($ss, $idCierre, $denominacion, $cantidad, $subtotal);
		if (!$stmt->execute()) {
			throw new Exception("No se pudieron guardar las denominaciones.");
		}
	}
}

function caja_cierre_insertar_evidencia($mysqli, $idCierre, $foto, $usuario)
{
	$tipo = "foto_dinero_contado";
	$ruta = $foto['path_rel'];
	$nombreOriginal = $foto['nombre_original'];
	$mime = $foto['mime'];
	$size = $foto['size'];
	$consulta = "Insert into caja_cierre_evidencias (id_cierre,tipo_evidencia,ruta_archivo,nombre_archivo,mime_type,size,usuario_carga,fecha_carga)
	values(?,?,?,?,?,?,?,NOW())";
	$stmt = $mysqli->prepare($consulta);
	$ss = 'sssssss';
	$stmt->bind_param($ss, $idCierre, $tipo, $ruta, $nombreOriginal, $mime, $size, $usuario);
	if (!$stmt->execute()) {
		throw new Exception("No se pudo guardar la evidencia fotografica.");
	}
}

function caja_cierre_insertar_firma($mysqli, $idCierre, $firma, $usuario, $nombreFirmante, $payload)
{
	$texto = isset($payload['texto_confirmacion']) ? (string)$payload['texto_confirmacion'] : "Confirmo que realice el conteo fisico del dinero y que los datos declarados son correctos.";
	$rutaFirma = $firma['path_rel'];
	$consulta = "Insert into caja_cierre_firmas (id_cierre,ruta_firma,usuario_firmante,nombre_firmante,fecha_firma,texto_confirmacion)
	values(?,?,?,?,NOW(),?)";
	$stmt = $mysqli->prepare($consulta);
	$ss = 'sssss';
	$stmt->bind_param($ss, $idCierre, $rutaFirma, $usuario, $nombreFirmante, $texto);
	if (!$stmt->execute()) {
		throw new Exception("No se pudo guardar la firma.");
	}
}

function caja_cierre_actualizar_rutas($mysqli, $idCierre, $rutaFoto, $rutaFirma)
{
	$consulta = "Update caja_cierres set ruta_foto=?, ruta_firma=?, actualizado_en=NOW() where id=?";
	$stmt = $mysqli->prepare($consulta);
	$ss = 'sss';
	$stmt->bind_param($ss, $rutaFoto, $rutaFirma, $idCierre);
	if (!$stmt->execute()) {
		throw new Exception("No se pudieron actualizar las rutas de evidencia.");
	}
}

function caja_cierre_auditar($mysqli, $idCierre, $lote, $usuario, $accion, $detalle, $valorAnterior="", $valorNuevo="")
{
	$consulta = "Insert into caja_cierre_auditoria (id_cierre,id_lote,usuario,accion,detalle,valor_anterior,valor_nuevo,fecha_hora)
	values(?,?,?,?,?,?,?,NOW())";
	$stmt = $mysqli->prepare($consulta);
	$ss = 'sssssss';
	$valorAnterior = (string)$valorAnterior;
	$valorNuevo = (string)$valorNuevo;
	$stmt->bind_param($ss, $idCierre, $lote, $usuario, $accion, $detalle, $valorAnterior, $valorNuevo);
	if (!$stmt->execute()) {
		throw new Exception("No se pudo registrar la auditoria del cierre.");
	}
}

function obternerLoteEdit($cod)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select lote 	from arqueocaja where idarqueocaja='$cod'  ";
		

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $lote= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $lote=$valor['lote'];
		  	  
		  	 			  
	  } 
	
 
 }
 
 return $lote;
 
 

}



function obternerLote($cod_local,$user)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select count(*) as contador
		from arqueocaja where  cod_local='$cod_local' and codusuarioap='$user'  ";
		

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $contador= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $contador=$valor['contador'];
		  	  
		  	 			  
	  } 
	
 
 }
 
 return $contador;
 
 

}




function controldecaja($buscar,$cod_local,$user)
{
	// Se prepara el filtro
	$sqlFiltro= " WHERE estado='Activo'";
	if ($buscar) {
		$sqlFiltro .= " and caja_idcaja='$buscar'";
	}
	if ($cod_local) {
		$sqlFiltro .= " and cod_local='$cod_local'";
	}
	if ($user) {
		$sqlFiltro .= " and codusuarioap='$user'";
	}
	
	$mysqli=conectar_al_servidor();
	
		$sql= "Select idarqueocaja, caja_idcaja, montoapertura, montocierre, fechaapertura, fechacierre, estado, codusuarioap, codusuarioce,lote,
		(Select nombre_persona from persona where cod_persona=codusuarioap) as usuarioap
		from arqueocaja $sqlFiltro ";
		 $pagina="";  

   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalRecaudado= 0;
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idarqueocaja=$valor['idarqueocaja'];
		  	  $caja_idcaja=mb_convert_encoding((string)($valor['caja_idcaja']), 'UTF-8', 'ISO-8859-1');
		  	  $montoapertura=mb_convert_encoding((string)($valor['montoapertura']), 'UTF-8', 'ISO-8859-1');
		  	  $montocierre=mb_convert_encoding((string)($valor['montocierre']), 'UTF-8', 'ISO-8859-1');
		  	  $fechaapertura=mb_convert_encoding((string)($valor['fechaapertura']), 'UTF-8', 'ISO-8859-1');
		  	  $fechacierre=mb_convert_encoding((string)($valor['fechacierre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $codusuarioap=mb_convert_encoding((string)($valor['codusuarioap']), 'UTF-8', 'ISO-8859-1');
		  	  $codusuarioce=mb_convert_encoding((string)($valor['codusuarioce']), 'UTF-8', 'ISO-8859-1');
		  	  $lote=mb_convert_encoding((string)($valor['lote']), 'UTF-8', 'ISO-8859-1');
		  	  $usuarioap=mb_convert_encoding((string)($valor['usuarioap']), 'UTF-8', 'ISO-8859-1');
		  	  $totalRecaudado=ObtenerTotalCaja($idarqueocaja,$montoapertura);
		  	  $resumenCierre=caja_cierre_calcular_resumen_medios($mysqli,$idarqueocaja,$montoapertura);
		  	 			  
	  }
	  
	  $informacion =array("1" => "exito","2" =>"1","3"=>$idarqueocaja,"4"=>$caja_idcaja,"5"=>  number_format($montoapertura,'0',',','.')
	  ,"6"=>  number_format($montocierre,'0',',','.'),"7"=>$fechaapertura,"8"=>$fechacierre,"9"=>$estado,"10"=>  number_format($totalRecaudado,'0',',','.')
	  ,"11"=>$codusuarioap ,"12"=>$usuarioap
	  ,"13"=>number_format($resumenCierre['movimiento_efectivo'],'0',',','.')
	  ,"14"=>number_format($resumenCierre['efectivo_esperado'],'0',',','.')
	  ,"15"=>number_format($resumenCierre['total_transferencias'],'0',',','.')
	  ,"16"=>number_format($resumenCierre['total_transferencias_conciliadas'],'0',',','.')
	  ,"17"=>number_format($resumenCierre['total_tarjetas'],'0',',','.')
	  ,"18"=>number_format($resumenCierre['total_billeteras'],'0',',','.')
	  ,"19"=>number_format($resumenCierre['total_otros'],'0',',','.')
	  ,"20"=>number_format($resumenCierre['total_ingresos'],'0',',','.')
	  ,"21"=>number_format($resumenCierre['total_egresos'],'0',',','.')
	  ,"22"=>$resumenCierre
	  ,"23"=>$lote);
 }else{
	 $totalRecaudado=obternerultimacajauser($cod_local,$user,$buscar);
	$informacion =array("1" => "exito","2" =>"0","3"=> number_format($totalRecaudado,'0',',','.'));
 
 }

 return $informacion;
  
}

function obternerultimacajauser($cod_local,$user,$buscar)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select idarqueocaja,montoapertura
		from arqueocaja where caja_idcaja='$buscar' and cod_local='$cod_local' and codusuarioap='$user' order by  idarqueocaja desc limit 1";
		

   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalRecaudado= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idarqueocaja=$valor['idarqueocaja'];
		  	  $montoapertura=mb_convert_encoding((string)($valor['montoapertura']), 'UTF-8', 'ISO-8859-1');
		  	  $totalRecaudado=ObtenerTotalCaja($idarqueocaja,$montoapertura);
		  	 			  
	  } 
	
 
 }
 
 return $totalRecaudado;
 
 

}


/*Buscar Registro*/
function buscarvista($fechaapertura,$fechafin,$caja,$estado,$local,$usuario,$lote)
{
$sqlFiltro= "";
	
$mysqli=conectar_al_servidor();

if ($fechaapertura != "") {
    $sqlFiltro.= " AND DATE_FORMAT(fechaapertura, '%Y-%m-%d') >= '$fechaapertura'";
}

if ($fechafin != "") {
    $fechaCierreFiltro = "NULLIF(NULLIF(CAST(fechacierre AS CHAR), ''), '0000-00-00 00:00:00')";
    $fechaAperturaFiltro = "NULLIF(NULLIF(CAST(fechaapertura AS CHAR), ''), '0000-00-00 00:00:00')";
    $sqlFiltro.= " AND DATE_FORMAT(COALESCE($fechaCierreFiltro, $fechaAperturaFiltro), '%Y-%m-%d') <= '$fechafin'";
}

if($caja!=""){
$sqlFiltro.=" and (Select cajanro from caja l where l.idcaja=caja_idcaja) like '%".$caja."%'";	
}

if($estado!=""){
$sqlFiltro.=" and estado='$estado' ";	
}

if($local!=""){
$sqlFiltro.=" and ap.cod_local='$local' ";	
}

if($usuario!=""){
$sqlFiltro.=" and (Select nombre_persona from persona where cod_persona=codusuarioap) like '%".$usuario."%'";		
}

if ($lote != "") {
    $sqlFiltro.= " AND lote like '%$lote%'";
}

$sql= "Select idarqueocaja, caja_idcaja, montoapertura, montocierre, fechaapertura, fechacierre, estado, codusuarioap, codusuarioce,cod_local,
(Select cajanro from caja l where l.idcaja=caja_idcaja) as cajanro,
(ifnull((Select sum(pg.Monto) from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk where pg.Monto>0 and pg.codApertura=idarqueocaja),0)) as cobros,
(ifnull((Select sum(pg.Monto) from pago pg left join tipopago tp on tp.cod_tipoPago=pg.cod_tipoPagoFK where pg.Monto>0 and pg.codApertura=idarqueocaja and UPPER(COALESCE(tp.nombre, pg.tipopago, '')) LIKE '%EFECTIVO%'),0)) as pagos_efectivo,
(ifnull((Select sum(monto) from gastos where codApertura=idarqueocaja and estado='Activo' and tipo='Egreso'),0)) as egreso,
(ifnull((Select sum(monto) from gastos where codApertura=idarqueocaja and estado='Activo' and tipo='Ingreso'),0)) as ingreso,
(ifnull((Select sum(monto) from gastos where codApertura=idarqueocaja and estado='Activo' and tipo='Deposito'),0)) as deposito,
(ifnull((Select sum(monto) from migrar_caja where cod_caja_desdeFK=idarqueocaja),0)) as total_migrado,
(ifnull((Select sum(monto) from migrar_caja where cod_caja_hastaFK=idarqueocaja),0)) as total_recibido,
(Select cajanro from caja l where l.idcaja=caja_idcaja) as cajanro,lote,
(Select nombre_persona from persona where cod_persona=codusuarioap) as usuarioap,
(Select nombre_persona from persona where cod_persona=codusuarioce) as usuariocie,
ap.cant500, ap.cant1000, ap.cant2000, ap.cant5000, ap.cant10000, ap.cant20000, ap.cant50000, ap.cant100000,
(Select Nombre from local l where l.cod_local=ap.cod_local) as nombrelocal
from arqueocaja ap where  estado!='Cancelado' ".$sqlFiltro." order by idarqueocaja desc limit 100  ";

$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

$Totaldiferencia = 0;
$TotalApertura = 0;
$TotalCierre = 0;

$TotalIngreso = 0;
$TotalEgreso = 0;
$TotalCobros = 0;
$TotalCobrosEfectivo = 0;
$TotalMigrado = 0;
$TotalRecibido = 0;
$TotalPendienteMigracion = 0;

$registros = array();

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$lote = mb_convert_encoding((string)($valor['lote']), 'UTF-8', 'ISO-8859-1'); 
$idarqueocaja = mb_convert_encoding((string)($valor['idarqueocaja']), 'UTF-8', 'ISO-8859-1'); 
$caja_idcaja = mb_convert_encoding((string)($valor['caja_idcaja']), 'UTF-8', 'ISO-8859-1');          
$montoapertura = mb_convert_encoding((string)($valor['montoapertura']), 'UTF-8', 'ISO-8859-1');          
$montocierre = mb_convert_encoding((string)($valor['montocierre']), 'UTF-8', 'ISO-8859-1'); 
$fechaapertura = mb_convert_encoding((string)($valor['fechaapertura']), 'UTF-8', 'ISO-8859-1'); 
$fechacierre = mb_convert_encoding((string)($valor['fechacierre']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$codusuarioap = mb_convert_encoding((string)($valor['codusuarioap']), 'UTF-8', 'ISO-8859-1'); 
$codusuarioce = mb_convert_encoding((string)($valor['codusuarioce']), 'UTF-8', 'ISO-8859-1'); 
$cod_local = mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1'); 
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'); 
$cajanro = mb_convert_encoding((string)($valor['cajanro']), 'UTF-8', 'ISO-8859-1'); 
$usuarioap = mb_convert_encoding((string)($valor['usuarioap']), 'UTF-8', 'ISO-8859-1'); 
$usuariocie = mb_convert_encoding((string)($valor['usuariocie']), 'UTF-8', 'ISO-8859-1'); 

$cant500 = mb_convert_encoding((string)($valor['cant500']), 'UTF-8', 'ISO-8859-1');
if ($cant500 == "") {$cant500=0;}
$cant1000 = mb_convert_encoding((string)($valor['cant1000']), 'UTF-8', 'ISO-8859-1');
if ($cant1000 == "") {$cant1000=0;}
$cant2000 = mb_convert_encoding((string)($valor['cant2000']), 'UTF-8', 'ISO-8859-1');
if ($cant2000 == "") {$cant2000=0;}
$cant5000 = mb_convert_encoding((string)($valor['cant5000']), 'UTF-8', 'ISO-8859-1');
if ($cant5000 == "") {$cant5000=0;}
$cant10000 = mb_convert_encoding((string)($valor['cant10000']), 'UTF-8', 'ISO-8859-1');
if ($cant10000 == "") {$cant10000=0;}
$cant20000 = mb_convert_encoding((string)($valor['cant20000']), 'UTF-8', 'ISO-8859-1');
if ($cant20000 == "") {$cant20000=0;}
$cant50000 = mb_convert_encoding((string)($valor['cant50000']), 'UTF-8', 'ISO-8859-1');
if ($cant50000 == "") {$cant50000=0;}
$cant100000 = mb_convert_encoding((string)($valor['cant100000']), 'UTF-8', 'ISO-8859-1');
if ($cant100000 == "") {$cant100000=0;}

$cobros = mb_convert_encoding((string)($valor['cobros']), 'UTF-8', 'ISO-8859-1'); 
$pagos_efectivo = mb_convert_encoding((string)($valor['pagos_efectivo']), 'UTF-8', 'ISO-8859-1'); 
$egreso = mb_convert_encoding((string)($valor['egreso']), 'UTF-8', 'ISO-8859-1'); 
$ingreso = mb_convert_encoding((string)($valor['ingreso']), 'UTF-8', 'ISO-8859-1'); 
$deposito = mb_convert_encoding((string)($valor['deposito']), 'UTF-8', 'ISO-8859-1'); 
$total_migrado = mb_convert_encoding((string)($valor['total_migrado']), 'UTF-8', 'ISO-8859-1'); 
$total_recibido = mb_convert_encoding((string)($valor['total_recibido']), 'UTF-8', 'ISO-8859-1'); 

if ($cobros == "") {$cobros=0;}
if ($pagos_efectivo == "") {$pagos_efectivo=0;}
if ($egreso == "") {$egreso=0;}
if ($ingreso == "") {$ingreso=0;}
if ($deposito == "") {$deposito=0;}
if ($total_migrado == "") {$total_migrado=0;}
if ($total_recibido == "") {$total_recibido=0;}

$TotalIngreso =  $TotalIngreso +$ingreso ;
$TotalEgreso =$TotalEgreso +$egreso ;
$TotalCobros = $TotalCobros + $cobros;
$TotalCobrosEfectivo = $TotalCobrosEfectivo + $pagos_efectivo;
$TotalMigrado = $TotalMigrado + $total_migrado;
$TotalRecibido = $TotalRecibido + $total_recibido;

$TotalApertura += $montoapertura;
$TotalCierre += $montocierre;

$efectivoEsperado = ((float)$montoapertura + (float)$pagos_efectivo + (float)$ingreso + (float)$total_recibido) - ((float)$egreso + (float)$total_migrado + (float)$deposito);
$montoBaseMigracion = ((float)$montocierre > 0) ? (float)$montocierre : $efectivoEsperado;
$diferencia = 0;
if (strtolower($estado) == "cerrado") {
	$diferencia = (float)$montocierre - $efectivoEsperado;
}
$migracionPendiente = 0;
if (strtolower($estado) == "cerrado") {
	$migracionPendiente = $montoBaseMigracion - (float)$total_migrado;
	if ($migracionPendiente < 0) {
		$migracionPendiente = 0;
	}
}
$TotalPendienteMigracion = $TotalPendienteMigracion + $migracionPendiente;

$fechaapertura2 = date("d-m-Y H:i:s", strtotime($fechaapertura));
$fechacierre2="";
if($fechacierre!=""){
	$fechacierre2 = date("d-m-Y H:i:s", strtotime($fechacierre));
}

	    	  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosaperturacierrecaja(this)'>
<td id='td_id_1' style='display:none'>".$idarqueocaja."</td>
<td id='td_id_2' style='display:none'>".$caja_idcaja."</td>
<td id='td_id_3' style='display:none'>".$codusuarioap."</td>
<td id='td_id_4' style='display:none'>".$codusuarioce."</td>
<td id='td_id_5' style='display:none'>".$cod_local."</td>
<td id='td_datos_2' style='display:none'>".$nombrelocal."</td>
<td id='td_datos_10' style='width:10%'>".$lote."</td>
<td id='td_datos_1' style='width:10%'>".$cajanro."</td>
<td id='td_datos_9' style='width:10%'>".$estado."</td>
<td id='td_datos_3' style='width:10%'>".$fechaapertura."</td>
<td id='td_datos_4' style='width:10%'>".$fechacierre."</td>
<td id='td_datos_7' style='width:10%'>".number_format($montoapertura,'0',',','.')."</td>
<td id='td_datos_8' style='width:10%'>".number_format($montocierre,'0',',','.')."</td>
<td id='td_datos_5' style='width:15%'>".$usuarioap."</td>
<td id='td_datos_2' style='width:10%'>".$nombrelocal."</td>
<td id='td_datos_6' style='display:none'>".$usuariocie."</td>
<td id='td_datos_11' style='display: none;'>$cant500</td>
<td id='td_datos_12' style='display: none;'>$cant1000</td>
<td id='td_datos_13' style='display: none;'>$cant2000</td>
<td id='td_datos_14' style='display: none;'>$cant5000</td>
<td id='td_datos_15' style='display: none;'>$cant10000</td>
<td id='td_datos_16' style='display: none;'>$cant20000</td>
<td id='td_datos_17' style='display: none;'>$cant50000</td>
<td id='td_datos_18' style='display: none;'>$cant100000</td>
</tr>
</table>";

$registros[]= array(
	'lote' => mb_convert_encoding((string)($valor['lote']), 'UTF-8', 'ISO-8859-1'),
	'idarqueocaja' => mb_convert_encoding((string)($valor['idarqueocaja']), 'UTF-8', 'ISO-8859-1'),
	'caja_idcaja' => mb_convert_encoding((string)($valor['caja_idcaja']), 'UTF-8', 'ISO-8859-1'),
	'montoapertura' => mb_convert_encoding((string)($valor['montoapertura']), 'UTF-8', 'ISO-8859-1'),
	'montocierre' => mb_convert_encoding((string)($valor['montocierre']), 'UTF-8', 'ISO-8859-1'),
	'fechaapertura' => mb_convert_encoding((string)($valor['fechaapertura']), 'UTF-8', 'ISO-8859-1'),
	'fechacierre' => mb_convert_encoding((string)($valor['fechacierre']), 'UTF-8', 'ISO-8859-1'),
	'estado' => mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'),
	'codusuarioap' => mb_convert_encoding((string)($valor['codusuarioap']), 'UTF-8', 'ISO-8859-1'),
	'codusuarioce' => mb_convert_encoding((string)($valor['codusuarioce']), 'UTF-8', 'ISO-8859-1'),
	'cod_local' => mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1'),
	'nombrelocal' => mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'),
	'cajanro' => mb_convert_encoding((string)($valor['cajanro']), 'UTF-8', 'ISO-8859-1'),
	'usuarioap' => mb_convert_encoding((string)($valor['usuarioap']), 'UTF-8', 'ISO-8859-1'),
	'usuariocie' => mb_convert_encoding((string)($valor['usuariocie']), 'UTF-8', 'ISO-8859-1'),
	'cobros' => (float)$cobros,
	'pagos_efectivo' => (float)$pagos_efectivo,
	'ingreso' => (float)$ingreso,
	'egreso' => (float)$egreso,
	'deposito' => (float)$deposito,
	'efectivo_esperado' => $efectivoEsperado,
	'diferencia' => $diferencia,
	'diferencia_abs' => abs($diferencia),
	'total_migrado' => (float)$total_migrado,
	'total_recibido' => (float)$total_recibido,
	'migracion_pendiente' => $migracionPendiente,
	'monto_base_migracion' => $montoBaseMigracion,
	'cant500' => (float)$cant500,
	'cant1000' => (float)$cant1000,
	'cant2000' => (float)$cant2000,
	'cant5000' => (float)$cant5000,
	'cant10000' => (float)$cant10000,
	'cant20000' => (float)$cant20000,
	'cant50000' => (float)$cant50000,
	'cant100000' => (float)$cant100000,
);
}
}


$Totaldiferencia = ($TotalCobrosEfectivo + $TotalIngreso) - $TotalEgreso;

return array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($Totaldiferencia,'0',',','.'),"5"=>number_format($TotalApertura,'0',',','.'),"6"=>number_format($TotalCierre,'0',',','.'),"7"=>number_format($TotalIngreso,'0',',','.'),"8"=>number_format($TotalEgreso,'0',',','.'),"9"=>number_format($TotalCobros,'0',',','.'), "10" => $registros, "11"=>number_format($TotalMigrado,'0',',','.'), "12"=>number_format($TotalRecibido,'0',',','.'), "13"=>number_format($TotalPendienteMigracion,'0',',','.'));
}

function buscarcajaapp($fecha1,$fecha2,$cobrador,$estado)
{
	
$mysqli=conectar_al_servidor();

$condicionFechas="";
if($fecha1!="" && $fecha2!=""){
$condicionFechas="and fechaapertura>='$fecha1' and fechaapertura <='$fecha2' ";	
}
$condicionCobrador="";
if($cobrador!=""){
	$condicionCobrador=" and (Select nombre_persona from persona where cod_persona=cod_cobrador) like '%".$cobrador."%' ";
}
$condicionestado="";
if($estado!=""){
	$condicionestado=" and estado='".$estado."' ";
}


$sql= "Select idaperturacajaapp, fechaapertura, fechacierre, estado, IFNULL(montocierre,0) as montocierre, cod_cobrador,
(Select nombre_persona from persona where cod_persona=cod_cobrador) as usuario
from aperturacajaapp ap where  estado!='Cancelado' ".$condicionFechas.$condicionCobrador.$condicionestado." order by idaperturacajaapp desc limit 100  ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$idaperturacajaapp = mb_convert_encoding((string)($valor['idaperturacajaapp']), 'UTF-8', 'ISO-8859-1'); 
$fechaapertura = mb_convert_encoding((string)($valor['fechaapertura']), 'UTF-8', 'ISO-8859-1');          
$fechacierre = mb_convert_encoding((string)($valor['fechacierre']), 'UTF-8', 'ISO-8859-1');          
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$montocierre = mb_convert_encoding((string)($valor['montocierre']), 'UTF-8', 'ISO-8859-1'); 
$cod_cobrador = mb_convert_encoding((string)($valor['cod_cobrador']), 'UTF-8', 'ISO-8859-1'); 
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1'); 


	    	 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosaperturacierrecajaapp(this)'>
<td id='td_id_1' style='display:none'>".$idaperturacajaapp."</td>
<td id='td_datos_1' style='width:10%'>".$usuario."</td>
<td id='td_datos_9' style='width:10%'>".$fechaapertura."</td>
<td id='td_datos_3' style='width:10%'>".$fechacierre."</td>
<td id='td_datos_7' style='width:10%'>".$montocierre."</td>
<td id='td_datos_5' style='width:10%'>".$estado."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function ObtenerTotalCaja($idArqeoFk,$montoInicio)
{
$mysqli=conectar_al_servidor();

$sql= "select  sum(pg.Monto) as Monto
 from  pago pg 
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' ";	
$Pagos = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$m = $valor['Monto'];          
$Pagos=$Pagos+$m;

	    	 


}
}

$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Egreso'";	
$MontoEgresos = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$m = $valor['monto'];          
$MontoEgresos=$MontoEgresos+$m;

	    	 


}
}


$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Ingreso'";	
$MontoIngreso= "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$m = $valor['monto'];          
$MontoIngreso=$MontoIngreso+$m;

	    	 


}
}

 

$datosdeCajaRecibir=datosdeCajaRecibir($idArqeoFk);
$datosdeCajaEnviado=datosdeCajaEnviado($idArqeoFk);



$totalIngreso=$MontoIngreso+$Pagos+$montoInicio + $datosdeCajaRecibir[1];
$Monto=$totalIngreso-($MontoEgresos  + $datosdeCajaEnviado[1]);
 

return $Monto;
}





/*Buscar */
function datosdeCajaEnviado($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK , 
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from  migrar_caja  where cod_caja_desdeFK='$idArqeoFk' ";	


 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalCaja=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$monto = mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1'); 
$usuarioRecibe = mb_convert_encoding((string)($valor['usuarioRecibe']), 'UTF-8', 'ISO-8859-1'); 

$totalCaja= $totalCaja + $monto ;
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px;line-height: 18px;' >".$usuarioRecibe."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')." </td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalCaja;
return $datos;
}



/*Buscar */
function datosdeCajaRecibir($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK , 
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from  migrar_caja  where cod_caja_hastaFK='$idArqeoFk' ";	


 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalCaja=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$monto = mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1'); 
$usuarioEnvia = mb_convert_encoding((string)($valor['usuarioEnvia']), 'UTF-8', 'ISO-8859-1'); 

$totalCaja= $totalCaja + $monto ;
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px;line-height: 18px;' >".$usuarioEnvia."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')." </td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalCaja;
return $datos;
}






function buscarmoviemientocaja($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "select Monto,tipo,cod_venta_fk,descripcion,
(Select Nombre from local l where l.cod_local=pg.codCaja) as nombrelocal
 from  pago pg 
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' ";
$totalPagado = "0";   
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          

$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'); 
$cod_venta_fk = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1'); 
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'); 
$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1'); 


$totalPagado=$totalPagado+$Monto;
if($descripcion=="ventas"){
	$descripcion=buscar_detalles_venta($cod_venta_fk)['listado'];
}
	$pagina.="
<table class='tableTicket' border='0' cellspacing='0' cellpadding='0'>
<tr >
<td id='' style='width:75%;text-align:left;padding:5px;line-height: 18px;' >".$descripcion."</td>
<td id='' style='width:25%'>". number_format($Monto,'0',',','.')."</td>
</tr>
</table>
";


	    	 


}
}

$datosdeCajaRecibir=datosdeCajaRecibir($idArqeoFk);
$datosdeCajaEnviado=datosdeCajaEnviado($idArqeoFk);


$montoapertura=Obtenermontoapertura($idArqeoFk);

$datosdeEgresos=datosdeEgresos($idArqeoFk);
$datosdeIngreso=datosdeIngreso($idArqeoFk); 
$totalPagado=($totalPagado+$datosdeIngreso[0]+$montoapertura + $datosdeCajaRecibir[1])-($datosdeEgresos[0] + $datosdeCajaEnviado[1] );
$resumenCierre=caja_cierre_calcular_resumen_medios($mysqli,$idArqeoFk,$montoapertura);
 $informacion =array(
	"1" => "exito",
	"2" =>  number_format($totalPagado,'0',',','.'),
	"3"=> $pagina,
	"4"=> number_format($resumenCierre['movimiento_efectivo'],'0',',','.'),
	"5"=> number_format($resumenCierre['efectivo_esperado'],'0',',','.'),
	"6"=> $resumenCierre
);
echo json_encode($informacion);	
exit;
}
function buscar_recaudo_opciones_pago($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "SELECT * FROM tipopago;";

$pagina = "<table style='width:98%'><tr>";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

$arrayIds = ["inptTotalEfectivoConsultaCaja","inptTotalTarjetaConsultaCaja","inptTotalDebitoConsultaCaja","inptTotalTransferenciaConsultaCaja","inptTotalBilleteraConsultaCaja","inptTotalGiroMovilConsultaCaja"];
$contador = 0;
$TotalEfectivo = 0;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          

$cod_tipoPago = mb_convert_encoding((string)($valor['cod_tipoPago']), 'UTF-8', 'ISO-8859-1'); 
$nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1'); 

$totalMonto = buscar_total_opcion_pago($idArqeoFk,$cod_tipoPago);


	$pagina.="
<td style='width:10%;text-align:left'>
<p class='pTituloC' >".$nombre.":</p>
<input class='inputTextDisable' id='".$arrayIds[$contador]."' type='text' disabled
style='width:95%;' value='".number_format($totalMonto,'0',',','.')."' />
</td>
";

if($nombre!="EFECTIVO"){
	$TotalEfectivo += $totalMonto;
}

$contador++;

}
}

$pagina.= "</tr>
</table>";   

$informacion =array("1" => "exito","2"=> $pagina,"3"=> number_format($TotalEfectivo,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscar_total_opcion_pago($idArqeoFk,$idtipopago)
{
$mysqli=conectar_al_servidor();
 

	
$sql= "select  IFNULL(Monto,0) as Monto
 from  pago pg
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' and pg.cod_tipoPagoFK='$idtipopago' ";	

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalTipoPago=0;

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{
$totalTipoPago+= mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 
}
}
  
 mysqli_close($mysqli);
return $totalTipoPago;
}

function datosdeEgresos($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Egreso' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      
		  	  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
		  	 $totalGasto=$totalGasto+$monto;
		  	 
	
			    	 
		  	  
			  
			  
	  }
 }


 $datos[0]= $totalGasto;
 return $datos;
}

function datosdeIngreso($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select monto
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Ingreso' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		   
		  	  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
		  	 $totalGasto=$totalGasto+$monto;
		  	 	 
		
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $totalGasto;
 return $datos;
}

function Obtenermontoapertura($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "Select montoapertura
from arqueocaja  where idarqueocaja='$idArqeoFk'  ";
$montoapertura = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$montoapertura = mb_convert_encoding((string)($valor['montoapertura']), 'UTF-8', 'ISO-8859-1');          

	    	 


}
}

return $montoapertura;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	$operacion = $_POST['funt'];
	$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
	verificar($operacion);
}
?>

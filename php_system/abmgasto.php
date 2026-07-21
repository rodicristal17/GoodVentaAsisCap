<?php

include_once('quitarseparadormiles.php');
include_once("buscar_nivel.php");
require_once("conexion.php");
require_once("solicitud_eliminado_helper.php");
include_once("verificar_navegador.php");
include_once("classTable.php");
include_once("subir_foto_base64.php");
include_once("abmpagos.php");
include_once("abmInterConsulta.php");
include_once("abmPresupuestoMotivoGasto.php");
include_once("abmaperturacierrecaja.php");
include_once("abmProyectoGasto.php");

date_default_timezone_set('America/Asuncion');

function gastoFechaFiltroIsoValida($valor)
{
	$valor= trim((string)$valor);
	if ($valor === '') {
		return true;
	}
	$fecha= DateTime::createFromFormat('!Y-m-d', $valor);
	return $fecha && $fecha->format('Y-m-d') === $valor;
}

function esConceptoDepositoCentral($descripcion)
{
	return strtoupper(trim((string)$descripcion)) == 'DEPOSITO BANCARIO - FARAONE CAPITAL S.A.';
}

function esTipoDepositoCentral($tipo)
{
	return strtolower(trim((string)$tipo)) == 'deposito';
}

function normalizarConceptoMovimientoInterno($descripcion)
{
	$texto= (string)$descripcion;
	if ($texto != '' && !mb_check_encoding($texto, 'UTF-8')) {
		$texto= mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
	}
	$texto= mb_strtoupper(trim($texto), 'UTF-8');
	$texto= str_replace(
		array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'),
		array('A', 'E', 'I', 'O', 'U', 'U'),
		$texto
	);
	return preg_replace('/\s+/u', ' ', $texto);
}

function esConceptoMigracionCaja($descripcion)
{
	return normalizarConceptoMovimientoInterno($descripcion) == 'MIGRACION DE CAJA';
}

function esCodigoConceptoDepositoCentral($mysqli, $codMotivo)
{
	$codMotivo= (int)$codMotivo;
	if ($codMotivo <= 0) {
		return false;
	}
	$stmt= $mysqli->prepare("SELECT descripcion FROM motivos_ingreso_egreso WHERE cod_motivo_ingreso_egreso=? LIMIT 1");
	if (!$stmt) {
		return true;
	}
	$stmt->bind_param('i', $codMotivo);
	$esDeposito= false;
	if (!$stmt->execute()) {
		$stmt->close();
		return true;
	}
	$fila= $stmt->get_result()->fetch_assoc();
	$esDeposito= $fila && esConceptoDepositoCentral($fila['descripcion']);
	$stmt->close();
	return $esDeposito;
}

function obtenerClasificacionGastoActual($idgastos)
{
	$clasificacion= array('existe' => false, 'tipo' => '', 'cod_local' => 0, 'estado' => '');
	if (!is_numeric($idgastos) || intval($idgastos) <= 0) {
		return $clasificacion;
	}
	$mysqli= conectar_al_servidor();
	$stmt= $mysqli->prepare("SELECT tipo,cod_local,estado FROM gastos WHERE idgastos=? LIMIT 1");
	if ($stmt) {
		$id= intval($idgastos);
		$stmt->bind_param('i', $id);
		if ($stmt->execute()) {
			$resultado= $stmt->get_result();
			if ($resultado && $fila= $resultado->fetch_assoc()) {
				$clasificacion['existe']= true;
				$clasificacion['tipo']= (string)$fila['tipo'];
				$clasificacion['cod_local']= intval($fila['cod_local']);
				$clasificacion['estado']= (string)$fila['estado'];
			}
		}
		$stmt->close();
	}
	mysqli_close($mysqli);
	return $clasificacion;
}

function usuarioPuedeGestionarLocalGasto($codUsuario, $codLocal)
{
	$codUsuario= intval($codUsuario);
	$codLocal= intval($codLocal);
	if ($codUsuario <= 0 || $codLocal <= 0) {
		return false;
	}
	if (intval(buscarlocaluser($codUsuario)) === $codLocal) {
		return true;
	}
	return controldeaccesoacasas($codUsuario, 'CAMBIARLOCAL', " u.accion='SI' ") == 1;
}

function gastoConceptosActivosParaIds($mysqli, $idsGastos, $bloquear = false)
{
	$idsGastos= gastoDistribucionNormalizarIdsExcluir($idsGastos);
	if (count($idsGastos) < 1) {
		return false;
	}
	$sql= "SELECT g.idgastos,LOWER(TRIM(IFNULL(m.estado,''))) AS estado_concepto
		FROM gastos g
		LEFT JOIN motivos_ingreso_egreso m ON m.cod_motivo_ingreso_egreso=g.cod_motivoIngresoEgresoFK
		WHERE g.idgastos IN (".implode(',', $idsGastos).") ORDER BY g.idgastos";
	if ($bloquear) {
		$sql .= ' FOR UPDATE';
	}
	$resultado= $mysqli->query($sql);
	if (!$resultado || $resultado->num_rows !== count($idsGastos)) {
		return false;
	}
	while ($fila= $resultado->fetch_assoc()) {
		if ($fila['estado_concepto'] !== 'activo') {
			return false;
		}
	}
	return true;
}

function gastoDistribucionLocalesGraficos()
{
	return array(3, 5, 6, 7, 9);
}

function gastoDistribucionTablaDisponible($mysqli, $tabla = 'gasto_distribucion_local')
{
	$tabla= $mysqli->real_escape_string($tabla);
	$resultado= $mysqli->query("SHOW TABLES LIKE '$tabla'");
	return $resultado && $resultado->num_rows > 0;
}

function gastoDistribucionEstructuraEscrituraDisponible($mysqli)
{
	return gastoDistribucionTablaDisponible($mysqli, 'gasto_distribucion_local')
		&& gastoDistribucionTablaDisponible($mysqli, 'gasto_distribucion_auditoria');
}

function gastoDistribucionMontoEntero($valor)
{
	if (!is_numeric($valor)) {
		throw new Exception('La distribucion contiene un monto no valido.');
	}
	$monto= (float)$valor;
	$montoRedondeado= round($monto);
	if ($monto <= 0 || abs($monto - $montoRedondeado) > 0.00001) {
		throw new Exception('Los montos distribuidos deben ser guaranies enteros y mayores a cero.');
	}
	return (int)$montoRedondeado;
}

function gastoDistribucionRepartirEquitativamente($monto, $locales)
{
	$monto= gastoDistribucionMontoEntero($monto);
	$locales= array_values(array_unique(array_map('intval', $locales)));
	sort($locales, SORT_NUMERIC);
	if (count($locales) < 1) {
		throw new Exception('Seleccione al menos una sucursal para distribuir el gasto.');
	}
	$base= intdiv($monto, count($locales));
	$residuo= $monto % count($locales);
	$asignaciones= array();
	foreach ($locales as $indice => $codLocal) {
		$asignaciones[$codLocal]= $base + ($indice < $residuo ? 1 : 0);
		if ($asignaciones[$codLocal] <= 0) {
			throw new Exception('El monto es insuficiente para repartirlo entre las sucursales seleccionadas.');
		}
	}
	return $asignaciones;
}

function gastoDistribucionValidarLocales($mysqli, $asignaciones, $codUsuario, $codLocalPago, $modo, $bloquear = false)
{
	$locales= array_keys($asignaciones);
	$codLocalPago= (int)$codLocalPago;
	$modo= strtolower(trim((string)$modo));
	if (count($locales) < 1) {
		throw new Exception('La distribucion del gasto no contiene sucursales.');
	}
	$localesValidar= array_values(array_unique(array_merge(array_map('intval', $locales), array($codLocalPago))));
	$localesValidar= array_values(array_filter($localesValidar, function($codLocal) { return $codLocal > 0; }));
	sort($localesValidar, SORT_NUMERIC);
	$ids= implode(',', $localesValidar);
	$sqlLocales= "SELECT cod_local,LOWER(TRIM(IFNULL(estado,''))) AS estado FROM local WHERE cod_local IN ($ids) ORDER BY cod_local";
	if ($bloquear) {
		$sqlLocales .= ' FOR UPDATE';
	}
	$resultado= $mysqli->query($sqlLocales);
	$activos= array();
	if ($resultado) {
		while ($fila= $resultado->fetch_assoc()) {
			if ($fila['estado'] === 'activo') {
				$activos[(int)$fila['cod_local']]= true;
			}
		}
	}
	if ($codLocalPago <= 0 || !isset($activos[$codLocalPago])) {
		throw new Exception('El local de pago ya no esta activo.');
	}
	if (!usuarioPuedeGestionarLocalGasto($codUsuario, $codLocalPago)) {
		throw new Exception('No tiene permiso para registrar movimientos en el local de pago.');
	}
	$asignacionDesdeAdministracion= $codLocalPago === 1
		&& in_array($modo, array('compartido', 'personalizado'), true)
		&& usuarioPuedeGestionarLocalGasto($codUsuario, $codLocalPago);
	foreach ($locales as $codLocal) {
		$codLocal= (int)$codLocal;
		if (!isset($activos[$codLocal])) {
			throw new Exception('Una de las sucursales seleccionadas ya no esta activa.');
		}
		if (!$asignacionDesdeAdministracion && !usuarioPuedeGestionarLocalGasto($codUsuario, $codLocal)) {
			throw new Exception('No tiene permiso para asignar gastos a una de las sucursales seleccionadas.');
		}
	}
}

function gastoDistribucionNormalizarSolicitud($mysqli, $tipo, $codLocalPago, $montoTotal, $modo, $jsonAsignaciones, $codUsuario)
{
	if (strtolower(trim((string)$tipo)) != 'egreso') {
		return array('modo' => '', 'asignaciones' => array());
	}
	if (!gastoDistribucionEstructuraEscrituraDisponible($mysqli)) {
		throw new Exception('La actualizacion de distribucion multilocal esta incompleta: faltan el detalle o su auditoria. No se guardaron cambios.');
	}
	$montoTotal= gastoDistribucionMontoEntero($montoTotal);
	$modo= strtolower(trim((string)$modo));
	if (!in_array($modo, array('local', 'compartido', 'personalizado'), true)) {
		throw new Exception('Seleccione como se distribuira el egreso.');
	}
	if ($modo == 'local' && (int)$codLocalPago === 1) {
		throw new Exception('Los egresos pagados por Administracion deben distribuirse como Administracion compartida o Personalizado para reflejarse en los graficos de sucursales.');
	}
	$asignaciones= array();
	if ($modo == 'local') {
		$asignaciones[(int)$codLocalPago]= $montoTotal;
	} elseif ($modo == 'compartido') {
		$asignaciones= gastoDistribucionRepartirEquitativamente($montoTotal, gastoDistribucionLocalesGraficos());
	} else {
		$datos= json_decode((string)$jsonAsignaciones, true);
		if (!is_array($datos)) {
			throw new Exception('La distribucion personalizada no tiene un formato valido.');
		}
		$permitidos= array_flip(gastoDistribucionLocalesGraficos());
		foreach ($datos as $item) {
			if (!is_array($item)) {
				throw new Exception('La distribucion personalizada contiene un elemento no valido.');
			}
			$codLocal= isset($item['cod_local']) ? (int)$item['cod_local'] : 0;
			if ($codLocal <= 0 || !isset($permitidos[$codLocal])) {
				throw new Exception('La distribucion personalizada contiene una sucursal no habilitada en los graficos financieros.');
			}
			if (isset($asignaciones[$codLocal])) {
				throw new Exception('Una sucursal esta repetida en la distribucion personalizada.');
			}
			$asignaciones[$codLocal]= gastoDistribucionMontoEntero(isset($item['monto']) ? $item['monto'] : '');
		}
		if (count($asignaciones) < 2) {
			throw new Exception('La distribucion personalizada requiere por lo menos dos sucursales.');
		}
	}
	ksort($asignaciones, SORT_NUMERIC);
	if (array_sum($asignaciones) !== $montoTotal) {
		throw new Exception('La suma distribuida debe coincidir exactamente con el monto total del gasto.');
	}
	gastoDistribucionValidarLocales($mysqli, $asignaciones, $codUsuario, $codLocalPago, $modo);
	return array('modo' => $modo, 'asignaciones' => $asignaciones);
}

function gastoDistribucionTieneConciliacionUeno($mysqli, $idgastos, $bloquear = false)
{
	$idgastos= (int)$idgastos;
	if ($idgastos <= 0 || !gastoDistribucionTablaDisponible($mysqli, 'ueno_movimiento_gasto')) {
		return false;
	}
	$sql= "SELECT id FROM ueno_movimiento_gasto WHERE idgastos=? AND estado='activo' ORDER BY id LIMIT 1";
	if ($bloquear) {
		$sql .= ' FOR UPDATE';
	}
	$stmt= $mysqli->prepare($sql);
	if (!$stmt) {
		throw new Exception('No se pudo comprobar la conciliacion Ueno del gasto.');
	}
	$stmt->bind_param('i', $idgastos);
	if (!$stmt->execute()) {
		$stmt->close();
		throw new Exception('No se pudo comprobar la conciliacion Ueno del gasto.');
	}
	$tieneConciliacion= $stmt->get_result()->num_rows > 0;
	$stmt->close();
	return $tieneConciliacion;
}

function gastoUenoBloquearVinculosActivos($mysqli, $idsGastos)
{
	$idsGastos= gastoDistribucionNormalizarIdsExcluir($idsGastos);
	$salida= array('ids_gastos' => array(), 'ids_movimientos' => array());
	if (count($idsGastos) < 1
		|| !gastoDistribucionTablaDisponible($mysqli, 'ueno_movimiento_gasto')
		|| !gastoDistribucionTablaDisponible($mysqli, 'ueno_movimiento_bancario')) {
		return $salida;
	}
	$listaGastos= implode(',', $idsGastos);
	$resultado= $mysqli->query("SELECT DISTINCT id_movimiento FROM ueno_movimiento_gasto WHERE estado='activo' AND idgastos IN ($listaGastos) ORDER BY id_movimiento");
	if (!$resultado) {
		throw new Exception('No se pudieron consultar las conciliaciones Ueno del gasto.');
	}
	$idsMovimientos= array();
	while ($fila= $resultado->fetch_assoc()) {
		$idMovimiento= (int)$fila['id_movimiento'];
		if ($idMovimiento > 0) {
			$idsMovimientos[$idMovimiento]= $idMovimiento;
		}
	}
	ksort($idsMovimientos, SORT_NUMERIC);
	if (count($idsMovimientos) > 0) {
		$listaMovimientos= implode(',', $idsMovimientos);
		// El flujo Ueno toma primero el movimiento bancario. Respetar ese orden
		// evita el ciclo movimiento -> gasto frente a gasto -> movimiento.
		$resultado= $mysqli->query("SELECT id_movimiento FROM ueno_movimiento_bancario WHERE id_movimiento IN ($listaMovimientos) ORDER BY id_movimiento FOR UPDATE");
		if (!$resultado) {
			throw new Exception('No se pudieron bloquear los movimientos Ueno vinculados.');
		}
	}
	$resultado= $mysqli->query("SELECT id,id_movimiento,idgastos FROM ueno_movimiento_gasto WHERE estado='activo' AND idgastos IN ($listaGastos) ORDER BY id_movimiento,idgastos,id FOR UPDATE");
	if (!$resultado) {
		throw new Exception('No se pudieron bloquear las conciliaciones Ueno del gasto.');
	}
	$idsConciliados= array();
	while ($fila= $resultado->fetch_assoc()) {
		$idGasto= (int)$fila['idgastos'];
		$idsConciliados[$idGasto]= $idGasto;
	}
	ksort($idsConciliados, SORT_NUMERIC);
	$salida['ids_gastos']= array_values($idsConciliados);
	$salida['ids_movimientos']= array_values($idsMovimientos);
	return $salida;
}

function gastoUenoConsultarIdsActivos($mysqli, $idsGastos, $bloquear = false)
{
	$idsGastos= gastoDistribucionNormalizarIdsExcluir($idsGastos);
	if (count($idsGastos) < 1 || !gastoDistribucionTablaDisponible($mysqli, 'ueno_movimiento_gasto')) {
		return array();
	}
	$sql= "SELECT DISTINCT idgastos FROM ueno_movimiento_gasto
		WHERE estado='activo' AND idgastos IN (".implode(',', $idsGastos).") ORDER BY idgastos";
	if ($bloquear) {
		$sql .= ' FOR UPDATE';
	}
	$resultado= $mysqli->query($sql);
	if (!$resultado) {
		throw new Exception('No se pudo revalidar la conciliacion Ueno del gasto.');
	}
	$salida= array();
	while ($fila= $resultado->fetch_assoc()) {
		$salida[]= (int)$fila['idgastos'];
	}
	return $salida;
}

function gastoUenoValorComparable($campo, $valor)
{
	if (in_array($campo, array('monto'), true)) {
		return (float)$valor;
	}
	if (in_array($campo, array('cod_local','cod_motivoIngresoEgresoFK','codCaja','codApertura','cod_interConsultaFK','cod_proyecto_gastoFK'), true)) {
		return (int)$valor;
	}
	$valor= trim((string)$valor);
	if (in_array($campo, array('tipo','estado','banco'), true)) {
		$valor= strtolower($valor);
	}
	return $valor;
}

function gastoUenoValidarEdicionFinanciera($gastoActual, $datosNuevos, $tieneConciliacion)
{
	if (!$tieneConciliacion) {
		return;
	}
	$camposProtegidos= array(
		'monto', 'tipo', 'estado', 'cod_local', 'fecha', 'banco', 'nrocuenta',
		'nroboleta', 'cod_motivoIngresoEgresoFK', 'codCaja', 'codApertura',
		'cod_interConsultaFK', 'cod_proyecto_gastoFK'
	);
	foreach ($camposProtegidos as $campo) {
		$anterior= isset($gastoActual[$campo]) ? $gastoActual[$campo] : null;
		$nuevo= array_key_exists($campo, $datosNuevos) ? $datosNuevos[$campo] : $anterior;
		if (gastoUenoValorComparable($campo, $anterior) !== gastoUenoValorComparable($campo, $nuevo)) {
			throw new Exception('El gasto tiene una conciliacion Ueno activa. Revierta primero la conciliacion para cambiar monto, fecha, estado, tipo, local, concepto, caja, Hilo/proyecto o referencias bancarias. Solo pueden editarse la descripcion y los adjuntos.');
		}
	}
}

function gastoUenoPreservarEstadoEdicionIndividual($estadoCalculado, $gastoActual, $tieneConciliacion, $editarCuotas)
{
	if ($tieneConciliacion && $editarCuotas !== 'true' && isset($gastoActual['estado'])) {
		return $gastoActual['estado'];
	}
	return $estadoCalculado;
}

function gastoValidarConservacionDistribucionLegacy($gastoActual, $datosNuevos)
{
	$camposProtegidos= array(
		'monto', 'tipo', 'estado', 'cod_local', 'fecha', 'banco', 'nrocuenta',
		'nroboleta', 'cod_motivoIngresoEgresoFK', 'codCaja', 'codApertura',
		'cod_interConsultaFK', 'cod_proyecto_gastoFK'
	);
	foreach ($camposProtegidos as $campo) {
		$anterior= isset($gastoActual[$campo]) ? $gastoActual[$campo] : null;
		$nuevo= array_key_exists($campo, $datosNuevos) ? $datosNuevos[$campo] : $anterior;
		if (gastoUenoValorComparable($campo, $anterior) !== gastoUenoValorComparable($campo, $nuevo)) {
			throw new Exception('Este egreso historico no admite cambiar datos financieros hasta corregir su monto y confirmar una distribucion valida. Puede conservarlo para editar solo descripcion o adjuntos.');
		}
	}
}

function gastoLocalEstaActivo($mysqli, $codLocal)
{
	$codLocal= (int)$codLocal;
	if ($codLocal <= 0) {
		return false;
	}
	$stmt= $mysqli->prepare("SELECT cod_local FROM local WHERE cod_local=? AND LOWER(TRIM(estado))='activo' LIMIT 1");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('i', $codLocal);
	$stmt->execute();
	$activo= $stmt->get_result()->num_rows > 0;
	$stmt->close();
	return $activo;
}

function gastoDistribucionObtenerFilas($mysqli, $idgastos, $bloquear = false)
{
	$salida= array('modo' => '', 'asignaciones' => array(), 'persistida' => false);
	$idgastos= (int)$idgastos;
	if ($idgastos <= 0 || !gastoDistribucionTablaDisponible($mysqli)) {
		return $salida;
	}
	$sql= "SELECT cod_localFK,monto_asignado,modo_distribucion FROM gasto_distribucion_local WHERE idgastosFK=? ORDER BY cod_localFK".($bloquear ? ' FOR UPDATE' : '');
	$stmt= $mysqli->prepare($sql);
	if (!$stmt) {
		throw new Exception('No se pudo consultar la distribucion del gasto.');
	}
	$stmt->bind_param('i', $idgastos);
	if (!$stmt->execute()) {
		$stmt->close();
		throw new Exception('No se pudo consultar la distribucion del gasto.');
	}
	$resultado= $stmt->get_result();
	while ($fila= $resultado->fetch_assoc()) {
		$salida['persistida']= true;
		$salida['modo']= (string)$fila['modo_distribucion'];
		$salida['asignaciones'][(int)$fila['cod_localFK']]= (int)round($fila['monto_asignado']);
	}
	$stmt->close();
	return $salida;
}

function gastoDistribucionObtenerEfectiva($mysqli, $idgastos, $bloquear = false)
{
	$idgastos= (int)$idgastos;
	$sql= "SELECT idgastos,monto,cod_local,tipo FROM gastos WHERE idgastos=? LIMIT 1".($bloquear ? ' FOR UPDATE' : '');
	$stmt= $mysqli->prepare($sql);
	if (!$stmt) {
		throw new Exception('No se pudo consultar el gasto para obtener su distribucion.');
	}
	$stmt->bind_param('i', $idgastos);
	if (!$stmt->execute()) {
		$stmt->close();
		throw new Exception('No se pudo consultar el gasto para obtener su distribucion.');
	}
	$gasto= $stmt->get_result()->fetch_assoc();
	$stmt->close();
	if (!$gasto) {
		throw new Exception('El gasto solicitado ya no existe.');
	}
	$distribucion= gastoDistribucionObtenerFilas($mysqli, $idgastos, $bloquear);
	if (!$distribucion['persistida'] && strtolower(trim((string)$gasto['tipo'])) == 'egreso') {
		$montoHistorico= (float)$gasto['monto'];
		$esMontoEnteroPositivo= $montoHistorico > 0 && abs($montoHistorico - round($montoHistorico)) <= 0.00001;
		$esAdministracionNoRepartible= (int)$gasto['cod_local'] === 1
			&& (int)round($montoHistorico) < count(gastoDistribucionLocalesGraficos());
		if (!$esMontoEnteroPositivo || $esAdministracionNoRepartible) {
			// Algunos registros legacy tienen monto cero o un total administrativo menor
			// que la cantidad de sucursales. Se muestran sin inventar filas de monto cero;
			// la edicion moderna puede corregir el total y materializar una distribucion valida.
			$distribucion['modo']= 'legacy_no_materializable';
			$distribucion['asignaciones']= array();
			$distribucion['legacy_no_materializable']= true;
		} elseif ((int)$gasto['cod_local'] === 1) {
			$distribucion['modo']= 'compartido';
			$distribucion['asignaciones']= gastoDistribucionRepartirEquitativamente($gasto['monto'], gastoDistribucionLocalesGraficos());
		} else {
			$distribucion['modo']= 'local';
			$distribucion['asignaciones']= array((int)$gasto['cod_local'] => gastoDistribucionMontoEntero($gasto['monto']));
		}
	}
	$distribucion['idgastos']= $idgastos;
	$distribucion['monto_total']= (int)round($gasto['monto']);
	$distribucion['cod_local_pago']= (int)$gasto['cod_local'];
	$distribucion['bloqueada_por_conciliacion']= gastoDistribucionTieneConciliacionUeno($mysqli, $idgastos);
	return $distribucion;
}

function gastoDistribucionCanonica($distribucion)
{
	$asignaciones= isset($distribucion['asignaciones']) ? $distribucion['asignaciones'] : array();
	ksort($asignaciones, SORT_NUMERIC);
	return json_encode(array('modo' => isset($distribucion['modo']) ? $distribucion['modo'] : '', 'asignaciones' => $asignaciones));
}

function gastoDistribucionGuardar($mysqli, $idgastos, $distribucionNueva, $codUsuario, $origen = 'flujo_financiero', $accion = 'editar', $forzarPersistir = false, $distribucionAnterior = null)
{
	if (!gastoDistribucionEstructuraEscrituraDisponible($mysqli)) {
		throw new Exception('La estructura de distribucion multilocal y auditoria no esta completa. No se guardaron cambios.');
	}
	$idgastos= (int)$idgastos;
	$codUsuario= (int)$codUsuario;
	$actual= is_array($distribucionAnterior)
		? $distribucionAnterior
		: gastoDistribucionObtenerEfectiva($mysqli, $idgastos, true);
	$sinCambios= gastoDistribucionCanonica($actual) === gastoDistribucionCanonica($distribucionNueva);
	$tieneConciliacionUeno= gastoDistribucionTieneConciliacionUeno($mysqli, $idgastos, true);
	if ($tieneConciliacionUeno && !$sinCambios) {
		throw new Exception('La distribucion de un gasto conciliado con Ueno no puede modificarse. Revierta primero la conciliacion bancaria.');
	}
	if (!$forzarPersistir && $sinCambios) {
		return false;
	}
	if ($tieneConciliacionUeno) {
		throw new Exception('La distribucion de un gasto conciliado con Ueno no puede modificarse. Revierta primero la conciliacion bancaria.');
	}
	$stmt= $mysqli->prepare('DELETE FROM gasto_distribucion_local WHERE idgastosFK=?');
	if (!$stmt) {
		throw new Exception('No se pudo preparar la nueva distribucion del gasto.');
	}
	$stmt->bind_param('i', $idgastos);
	if (!$stmt->execute()) {
		$mensaje= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo reemplazar la distribucion del gasto: '.$mensaje);
	}
	$stmt->close();
	$stmt= $mysqli->prepare("INSERT INTO gasto_distribucion_local (idgastosFK,cod_localFK,monto_asignado,modo_distribucion,origen,cod_usuarioFK_create) VALUES (?,?,?,?,?,?)");
	if (!$stmt) {
		throw new Exception('No se pudo preparar el detalle de distribucion del gasto.');
	}
	$modo= $distribucionNueva['modo'];
	foreach ($distribucionNueva['asignaciones'] as $codLocal => $montoAsignado) {
		$codLocal= (int)$codLocal;
		$montoAsignado= (int)$montoAsignado;
		$stmt->bind_param('iiissi', $idgastos, $codLocal, $montoAsignado, $modo, $origen, $codUsuario);
		if (!$stmt->execute()) {
			$mensaje= $stmt->error;
			$stmt->close();
			throw new Exception('No se pudo guardar la distribucion del gasto: '.$mensaje);
		}
	}
	$stmt->close();
	if (gastoDistribucionTablaDisponible($mysqli, 'gasto_distribucion_auditoria')) {
		$modoAnterior= isset($actual['modo']) ? $actual['modo'] : '';
		$anteriorJson= gastoDistribucionCanonica($actual);
		$nuevaJson= gastoDistribucionCanonica($distribucionNueva);
		$stmt= $mysqli->prepare("INSERT INTO gasto_distribucion_auditoria (idgastosFK,accion,modo_anterior,modo_nuevo,distribucion_anterior,distribucion_nueva,origen,cod_usuarioFK) VALUES (?,?,?,?,?,?,?,?)");
		if (!$stmt) {
			throw new Exception('No se pudo preparar la auditoria de la distribucion.');
		}
		$stmt->bind_param('issssssi', $idgastos, $accion, $modoAnterior, $modo, $anteriorJson, $nuevaJson, $origen, $codUsuario);
		if (!$stmt->execute()) {
			$mensaje= $stmt->error;
			$stmt->close();
			throw new Exception('No se pudo registrar la auditoria de la distribucion: '.$mensaje);
		}
		$stmt->close();
		$stmt= null;
	}
	return true;
}

function gastoDistribucionNormalizarIdsExcluir($idsExcluir)
{
	if (!is_array($idsExcluir)) {
		$idsExcluir= array($idsExcluir);
	}
	$salida= array();
	foreach ($idsExcluir as $idExcluir) {
		$idExcluir= (int)$idExcluir;
		if ($idExcluir > 0) {
			$salida[$idExcluir]= $idExcluir;
		}
	}
	ksort($salida, SORT_NUMERIC);
	return array_values($salida);
}

function gastoDistribucionMontoUsadoPresupuesto($mysqli, $codLocal, $codMotivo, $fechaDesde, $fechaHasta, $idsExcluir = array(), $bloquear = false)
{
	$codLocal= (int)$codLocal;
	$codMotivo= (int)$codMotivo;
	$idsExcluir= gastoDistribucionNormalizarIdsExcluir($idsExcluir);
	$fechaDesde= $mysqli->real_escape_string($fechaDesde);
	$fechaHasta= $mysqli->real_escape_string($fechaHasta);
	$excluir= count($idsExcluir) > 0 ? ' AND g.idgastos NOT IN ('.implode(',', $idsExcluir).')' : '';
	$estado= "LOWER(TRIM(IFNULL(g.estado,''))) IN ('activo','pendiente','solicitado')";
	$total= 0;
	$consultas= array();
	if (!gastoDistribucionTablaDisponible($mysqli)) {
		$consultas[]= "SELECT g.monto AS monto FROM gastos g
			WHERE g.cod_local=$codLocal AND g.cod_motivoIngresoEgresoFK=$codMotivo
			AND g.fecha>='$fechaDesde' AND g.fecha<='$fechaHasta'
			AND LOWER(TRIM(IFNULL(g.tipo,'')))='egreso' AND $estado $excluir
			ORDER BY g.idgastos";
	} else {
		$consultas[]= "SELECT d.monto_asignado AS monto FROM gasto_distribucion_local d INNER JOIN gastos g ON g.idgastos=d.idgastosFK
			WHERE d.cod_localFK=$codLocal AND g.cod_motivoIngresoEgresoFK=$codMotivo AND g.fecha>='$fechaDesde' AND g.fecha<='$fechaHasta' AND LOWER(TRIM(IFNULL(g.tipo,'')))='egreso' AND $estado $excluir
			ORDER BY g.idgastos,d.id_distribucion";
		$consultas[]= "SELECT g.monto AS monto FROM gastos g WHERE g.cod_local=$codLocal AND g.cod_motivoIngresoEgresoFK=$codMotivo AND g.fecha>='$fechaDesde' AND g.fecha<='$fechaHasta' AND LOWER(TRIM(IFNULL(g.tipo,'')))='egreso' AND $estado $excluir
			AND NOT EXISTS (SELECT 1 FROM gasto_distribucion_local dx WHERE dx.idgastosFK=g.idgastos)
			ORDER BY g.idgastos";
	}
	foreach ($consultas as $sql) {
		// En la ruta transaccional el limite por concepto/local ya fue tomado con
		// FOR UPDATE y la transaccion usa READ COMMITTED. No se bloquean aqui los
		// gastos para conservar el orden global Ueno -> gasto y evitar deadlocks.
		$resultado= $mysqli->query($sql);
		if (!$resultado) {
			throw new Exception('No se pudo calcular el presupuesto utilizado por la sucursal: '.$mysqli->error);
		}
		while ($fila= $resultado->fetch_assoc()) {
			$total += (int)round($fila['monto']);
		}
	}
	$localesAdministracion= gastoDistribucionLocalesGraficos();
	$indiceLocalAdministracion= array_search($codLocal, $localesAdministracion, true);
	if (gastoDistribucionTablaDisponible($mysqli) && $indiceLocalAdministracion !== false) {
		$sqlAdministracion= "SELECT g.idgastos,g.monto FROM gastos g
			WHERE g.cod_local=1 AND g.cod_motivoIngresoEgresoFK=$codMotivo
			AND g.fecha>='$fechaDesde' AND g.fecha<='$fechaHasta'
			AND LOWER(TRIM(IFNULL(g.tipo,'')))='egreso' AND $estado $excluir
			AND NOT EXISTS (SELECT 1 FROM gasto_distribucion_local dx WHERE dx.idgastosFK=g.idgastos)
			ORDER BY g.idgastos";
		$resultadoAdministracion= $mysqli->query($sqlAdministracion);
		if (!$resultadoAdministracion) {
			throw new Exception('No se pudo calcular la porcion historica de Administracion.');
		}
		$cantidadLocalesAdministracion= count($localesAdministracion);
		while ($filaAdministracion= $resultadoAdministracion->fetch_assoc()) {
			$montoAdministracion= max(0, (int)round($filaAdministracion['monto']));
			$baseAdministracion= intdiv($montoAdministracion, $cantidadLocalesAdministracion);
			$residuoAdministracion= $montoAdministracion % $cantidadLocalesAdministracion;
			$total += $baseAdministracion + ($indiceLocalAdministracion < $residuoAdministracion ? 1 : 0);
		}
	}
	return $total;
}

function gastoDistribucionBloquearPresupuestos($mysqli, $distribucion, $codMotivo)
{
	$asignaciones= isset($distribucion['asignaciones']) && is_array($distribucion['asignaciones'])
		? $distribucion['asignaciones'] : array();
	if (count($asignaciones) < 1) {
		return array();
	}
	ksort($asignaciones, SORT_NUMERIC);
	$codMotivo= (int)$codMotivo;
	$locales= array_map('intval', array_keys($asignaciones));
	$sql= "SELECT cod_monto_limite_gasto_motivo,cod_localFK,monto_limite
		FROM montos_limites_gasto_motivo
		WHERE cod_motivo_ingreso_egresoFK=$codMotivo
		AND cod_localFK IN (".implode(',', $locales).")
		ORDER BY cod_localFK,cod_monto_limite_gasto_motivo";
	// Esta es la cerradura de serializacion. Todos los gastos que compiten por
	// un mismo concepto/local toman primero estas filas en el mismo orden.
	$sql .= ' FOR UPDATE';
	$resultado= $mysqli->query($sql);
	if (!$resultado) {
		throw new Exception('No se pudieron bloquear los presupuestos por sucursal: '.$mysqli->error);
	}
	$limitesPorLocal= array();
	while ($fila= $resultado->fetch_assoc()) {
		$codLocalLimite= (int)$fila['cod_localFK'];
		if (isset($limitesPorLocal[$codLocalLimite])) {
			throw new Exception('Existe mas de un presupuesto para el mismo concepto y sucursal #'.$codLocalLimite.'. Corrija la configuracion antes de registrar movimientos.');
		}
		$limitesPorLocal[$codLocalLimite]= (int)$fila['monto_limite'];
	}
	return $limitesPorLocal;
}

function gastoDistribucionValidarPresupuestos($mysqli, $distribucion, $codMotivo, $fechaDesde, $fechaHasta, $idsExcluir = array(), $bloquear = false)
{
	$asignaciones= isset($distribucion['asignaciones']) && is_array($distribucion['asignaciones'])
		? $distribucion['asignaciones'] : array();
	if (count($asignaciones) < 1) {
		return;
	}
	ksort($asignaciones, SORT_NUMERIC);
	$codMotivo= (int)$codMotivo;
	if ($bloquear) {
		$limitesPorLocal= gastoDistribucionBloquearPresupuestos($mysqli, $distribucion, $codMotivo);
	} else {
		$locales= array_map('intval', array_keys($asignaciones));
		$resultado= $mysqli->query("SELECT cod_monto_limite_gasto_motivo,cod_localFK,monto_limite
			FROM montos_limites_gasto_motivo
			WHERE cod_motivo_ingreso_egresoFK=$codMotivo
			AND cod_localFK IN (".implode(',', $locales).")
			ORDER BY cod_localFK,cod_monto_limite_gasto_motivo");
		if (!$resultado) {
			throw new Exception('No se pudieron consultar los presupuestos por sucursal: '.$mysqli->error);
		}
		$limitesPorLocal= array();
		while ($fila= $resultado->fetch_assoc()) {
			$codLocalLimite= (int)$fila['cod_localFK'];
			if (isset($limitesPorLocal[$codLocalLimite])) {
				throw new Exception('Existe mas de un presupuesto para el mismo concepto y sucursal #'.$codLocalLimite.'. Corrija la configuracion antes de registrar movimientos.');
			}
			$limitesPorLocal[$codLocalLimite]= (int)$fila['monto_limite'];
		}
	}
	foreach ($asignaciones as $codLocal => $montoAsignado) {
		$codLocal= (int)$codLocal;
		if (!isset($limitesPorLocal[$codLocal])) {
			continue;
		}
		$limite= (int)$limitesPorLocal[$codLocal];
		if ($limite <= 0) {
			continue;
		}
		$usado= gastoDistribucionMontoUsadoPresupuesto($mysqli, $codLocal, $codMotivo, $fechaDesde, $fechaHasta, $idsExcluir, false);
		if ($usado + (int)$montoAsignado > $limite) {
			throw new Exception('La asignacion supera el presupuesto del concepto en la sucursal #'.(int)$codLocal.'. Disponible: Gs. '.number_format(max(0, $limite - $usado), 0, ',', '.').'.');
		}
	}
}

function gastoDistribucionUsuarioPuedeConciliarUeno($usuario)
{
	if ((string)$usuario === '2') {
		return true;
	}
	return controldeaccesoacasas($usuario, 'CONCILIAREGRESOUENO', " u.accion='SI' ") == 1
		|| controldeaccesoacasas($usuario, 'ASIGNARMANUALUENO', " u.accion='SI' ") == 1;
}

function gastoUenoEstadoDebitoDisponible($estado)
{
	$estado= strtolower(trim((string)$estado));
	return in_array($estado, array('registrado', 'disponible', 'asignado_parcial'), true);
}

function gastoDistribucionVincularDebitoUeno($mysqli, $idMovimiento, $idgastos, $monto, $usuario)
{
	$idMovimiento= (int)$idMovimiento;
	$idgastos= (int)$idgastos;
	$monto= gastoDistribucionMontoEntero($monto);
	$usuario= (int)$usuario;
	if ($idMovimiento <= 0) {
		return;
	}
	if (!gastoDistribucionTablaDisponible($mysqli, 'ueno_movimiento_bancario')
		|| !gastoDistribucionTablaDisponible($mysqli, 'ueno_movimiento_gasto')
		|| !gastoDistribucionTablaDisponible($mysqli, 'ueno_auditoria_conciliacion')) {
		throw new Exception('La estructura de conciliacion y auditoria Ueno no esta completa. No se guardaron cambios.');
	}
	$resultado= $mysqli->query("SELECT id_movimiento,cuenta,nro_comprobante,tipo_movimiento,importe_debito,importe_credito,estado FROM ueno_movimiento_bancario WHERE id_movimiento=$idMovimiento LIMIT 1 FOR UPDATE");
	$movimiento= $resultado ? $resultado->fetch_assoc() : null;
	if (!$movimiento || strtolower(trim((string)$movimiento['tipo_movimiento'])) != 'debito' || (int)$movimiento['importe_debito'] <= 0 || (int)$movimiento['importe_credito'] > 0) {
		throw new Exception('El movimiento Ueno seleccionado no es un debito valido.');
	}
	if (!gastoUenoEstadoDebitoDisponible($movimiento['estado'])) {
		throw new Exception('El debito Ueno seleccionado ya no esta disponible para registrar un gasto.');
	}
	$resultadoAplicado= $mysqli->query("SELECT monto_aplicado FROM ueno_movimiento_gasto WHERE id_movimiento=$idMovimiento AND estado='activo' FOR UPDATE");
	$aplicadoAnterior= 0;
	if ($resultadoAplicado) {
		while ($filaAplicado= $resultadoAplicado->fetch_assoc()) {
			$aplicadoAnterior += (int)$filaAplicado['monto_aplicado'];
		}
	}
	$saldo= (int)$movimiento['importe_debito'] - $aplicadoAnterior;
	if ($monto > $saldo) {
		throw new Exception('El monto del gasto supera el saldo disponible del debito Ueno.');
	}
	$estadoVinculo= 'activo';
	$observacion= 'Gasto creado y distribuido directamente desde debito Ueno.';
	$stmt= $mysqli->prepare('INSERT INTO ueno_movimiento_gasto (id_movimiento,idgastos,monto_aplicado,usuario_asocio,estado,observacion) VALUES (?,?,?,?,?,?)');
	if (!$stmt) {
		throw new Exception('No se pudo preparar la conciliacion del gasto con Ueno.');
	}
	$stmt->bind_param('iiiiss', $idMovimiento, $idgastos, $monto, $usuario, $estadoVinculo, $observacion);
	if (!$stmt->execute()) {
		$mensaje= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo conciliar el gasto con Ueno: '.$mensaje);
	}
	$idAsignacion= $stmt->insert_id;
	$stmt->close();
	$banco= 'Ueno';
	$cuenta= (string)$movimiento['cuenta'];
	$comprobante= (string)$movimiento['nro_comprobante'];
	$estadoGasto= 'Activo';
	$stmt= $mysqli->prepare("UPDATE gastos SET estado=?,banco=?,nrocuenta=?,nroboleta=? WHERE idgastos=? AND estado!='Inactivo'");
	$stmt->bind_param('ssssi', $estadoGasto, $banco, $cuenta, $comprobante, $idgastos);
	if (!$stmt->execute()) {
		$mensaje= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo completar el estado conciliado del gasto: '.$mensaje);
	}
	$stmt->close();
	$aplicadoNuevo= $aplicadoAnterior + $monto;
	$estadoBanco= $aplicadoNuevo >= (int)$movimiento['importe_debito'] ? 'asignado_total' : 'asignado_parcial';
	$stmt= $mysqli->prepare("UPDATE ueno_movimiento_bancario SET estado=? WHERE id_movimiento=? AND tipo_movimiento='debito'");
	$stmt->bind_param('si', $estadoBanco, $idMovimiento);
	if (!$stmt->execute()) {
		$mensaje= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo actualizar el saldo del debito Ueno: '.$mensaje);
	}
	$stmt->close();
	$tabla= 'ueno_movimiento_gasto';
	$accion= 'CONCILIAR_EGRESO_DESDE_ALTA';
	$anterior= $aplicadoAnterior > 0 ? 'ASIGNADO PARCIAL' : 'SIN CONCILIAR';
	$nuevo= $estadoBanco == 'asignado_total' ? 'CONCILIADO' : 'ASIGNADO PARCIAL';
	$datos= json_encode(array('idgastos' => $idgastos, 'saldo_banco_anterior' => $saldo));
	$codPago= '';
	$stmt= $mysqli->prepare('INSERT INTO ueno_auditoria_conciliacion (tabla_afectada,registro_id,cod_pagoFK,id_movimiento,accion,estado_anterior,estado_nuevo,monto,usuario,observacion,datos) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
	if (!$stmt) {
		throw new Exception('No se pudo preparar la auditoria de la conciliacion Ueno.');
	}
	$stmt->bind_param('sssssssssss', $tabla, $idAsignacion, $codPago, $idMovimiento, $accion, $anterior, $nuevo, $monto, $usuario, $observacion, $datos);
	if (!$stmt->execute()) {
		$mensaje= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo auditar la conciliacion Ueno: '.$mensaje);
	}
	$stmt->close();
}

function validarDepositoCentralEnCaja($idgastos, $operacion, $codApertura, $codCaja, $codLocal, $nroComprobante, $monto, $fecha, $estado = 'Activo', $mysqliExistente = null, $bloquear = false)
{
	$conexionPropia= !($mysqliExistente instanceof mysqli);
	$mysqli= $conexionPropia ? conectar_al_servidor() : $mysqliExistente;
	$error= '';
	try {
		$codApertura= (int)$codApertura;
		$codCaja= (int)$codCaja;
		$codLocal= (int)$codLocal;
		$idgastos= (int)$idgastos;
		$montoDeposito= (float)$monto;
		$nroComprobante= trim((string)$nroComprobante);
		$estadoNormalizado= strtolower(trim((string)$estado));
		$movimientoActivo= !in_array($estadoNormalizado, array('inactivo','anulado','baja'), true);

		if ($codApertura <= 0 || $codCaja <= 0 || $codLocal <= 0 || $montoDeposito <= 0) {
			throw new Exception('Los datos de caja y el monto del deposito no son validos.');
		}

		// Serializa los depositos del mismo local para evitar comprobantes duplicados
		// cuando existen varias cajas abiertas en paralelo.
		if ($bloquear) {
			$stmtLocal= $mysqli->prepare("SELECT cod_local FROM local WHERE cod_local=? LIMIT 1 FOR UPDATE");
			if (!$stmtLocal) {
				throw new Exception('No se pudo bloquear el local del deposito.');
			}
			$stmtLocal->bind_param('i', $codLocal);
			if (!$stmtLocal->execute() || $stmtLocal->get_result()->num_rows == 0) {
				$stmtLocal->close();
				throw new Exception('No se encontro el local del deposito.');
			}
			$stmtLocal->close();
		}

		$caja= caja_cierre_obtener_arqueo($mysqli, $codApertura, $bloquear);
		if (!$caja || strtolower(trim((string)$caja['estado'])) != 'activo') {
			throw new Exception('La caja seleccionada ya no esta activa. Actualice la pantalla antes de registrar el deposito.');
		}
		if ((int)$caja['cod_local'] !== $codLocal || (int)$caja['caja_idcaja'] !== $codCaja) {
			throw new Exception('El deposito debe registrarse en el local y la caja que estan activos.');
		}

		$montoActualComputable= 0;
		if ($operacion == 'editar') {
			$sqlActual= "SELECT idgastos,tipo,codApertura,codCaja,cod_local,monto,fecha,estado
				FROM gastos WHERE idgastos=? LIMIT 1".($bloquear ? " FOR UPDATE" : "");
			$stmtActual= $mysqli->prepare($sqlActual);
			if (!$stmtActual) {
				throw new Exception('No se pudo validar el deposito existente.');
			}
			$stmtActual->bind_param('i', $idgastos);
			if (!$stmtActual->execute()) {
				$stmtActual->close();
				throw new Exception('No se pudo validar el deposito existente.');
			}
			$resultadoActual= $stmtActual->get_result();
			$filaActual= $resultadoActual ? $resultadoActual->fetch_assoc() : null;
			$stmtActual->close();
			if (!$filaActual || !esTipoDepositoCentral($filaActual['tipo'])) {
				throw new Exception('El deposito que intenta editar ya no existe o cambio de clasificacion.');
			}
			if ((int)$filaActual['codApertura'] !== $codApertura
				|| (int)$filaActual['codCaja'] !== $codCaja
				|| (int)$filaActual['cod_local'] !== $codLocal) {
				throw new Exception('El deposito conserva su caja original y no puede trasladarse a una apertura diferente.');
			}
			if (strtolower(trim((string)$filaActual['estado'])) == 'activo') {
				$montoActualComputable= (float)$filaActual['monto'];
			}

			if (caja_cierre_tabla_existe($mysqli, 'ueno_movimiento_deposito')) {
				$sqlUeno= "SELECT id FROM ueno_movimiento_deposito
					WHERE origen_tipo='gasto' AND origen_id=? AND estado='activo' LIMIT 1".($bloquear ? " FOR UPDATE" : "");
				$stmtUeno= $mysqli->prepare($sqlUeno);
				if (!$stmtUeno) {
					throw new Exception('No se pudo verificar la conciliacion Ueno del deposito.');
				}
				$stmtUeno->bind_param('i', $idgastos);
				if (!$stmtUeno->execute()) {
					$stmtUeno->close();
					throw new Exception('No se pudo verificar la conciliacion Ueno del deposito.');
				}
				$tieneConciliacion= $stmtUeno->get_result()->num_rows > 0;
				$stmtUeno->close();
				if ($tieneConciliacion) {
					throw new Exception('El deposito ya esta conciliado con Ueno y no puede editarse ni inactivarse. Solicite una reversion controlada de la conciliacion.');
				}
			}
		}

		$fechaDeposito= DateTime::createFromFormat('!Y-m-d', (string)$fecha);
		if (!$fechaDeposito || $fechaDeposito->format('Y-m-d') !== (string)$fecha) {
			throw new Exception('Ingrese una fecha valida para el deposito.');
		}
		$fechaApertura= substr((string)$caja['fechaapertura'], 0, 10);
		$fechaHoy= date('Y-m-d');
		if ((string)$fecha < $fechaApertura) {
			throw new Exception('La fecha del deposito no puede ser anterior a la apertura de caja.');
		}
		if ((string)$fecha > $fechaHoy) {
			throw new Exception('La fecha del deposito no puede ser futura.');
		}

		if ($movimientoActivo) {
			$sql= "SELECT idgastos FROM gastos
				WHERE LOWER(TRIM(IFNULL(tipo,'')))='deposito'
				AND UPPER(TRIM(IFNULL(nroboleta,'')))=UPPER(TRIM(?))
				AND cod_local=?
				AND LOWER(TRIM(IFNULL(estado,''))) NOT IN ('inactivo','anulado','baja')";
			if ($operacion == 'editar') {
				$sql .= " AND idgastos<>?";
			}
			$sql .= " LIMIT 1".($bloquear ? " FOR UPDATE" : "");
			$stmt= $mysqli->prepare($sql);
			if (!$stmt) {
				throw new Exception('No se pudo validar el comprobante del deposito.');
			}
			if ($operacion == 'editar') {
				$stmt->bind_param('sii', $nroComprobante, $codLocal, $idgastos);
			} else {
				$stmt->bind_param('si', $nroComprobante, $codLocal);
			}
			if (!$stmt->execute()) {
				$stmt->close();
				throw new Exception('No se pudo validar el comprobante del deposito.');
			}
			$resultado= $stmt->get_result();
			if ($resultado && $resultado->num_rows > 0) {
				$filaDuplicada= $resultado->fetch_assoc();
				$stmt->close();
				throw new Exception('Ya existe un deposito activo con este numero de comprobante (registro #'.(int)$filaDuplicada['idgastos'].').');
			}
			$stmt->close();

			$resumenCaja= caja_cierre_calcular_resumen_medios($mysqli, $codApertura, $caja['montoapertura']);
			$efectivoDisponibleAntes= (float)$resumenCaja['efectivo_esperado'] + $montoActualComputable;
			if ($montoDeposito > $efectivoDisponibleAntes) {
				throw new Exception('El deposito supera el efectivo esperado disponible en caja (Gs. '.number_format(max(0, $efectivoDisponibleAntes), 0, ',', '.').').');
			}
		}
	} catch (Exception $e) {
		$error= $e->getMessage();
	}
	if ($conexionPropia) {
		mysqli_close($mysqli);
	}
	return $error;
}

function verificarOperacionGasto($operacion)
{
 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}

if ($operacion == 'obtener_distribucion_gasto')
{
	if (controldeaccesoacasas($user, 'VERLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1
		&& controldeaccesoacasas($user, 'EDITARLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1' => 'NI', '2' => 'No tiene permiso para consultar la distribucion del gasto.'));
		exit;
	}
	$idDistribucion= isset($_POST['idgastos']) ? (int)$_POST['idgastos'] : 0;
	$clasificacionDistribucion= obtenerClasificacionGastoActual($idDistribucion);
	if (!$clasificacionDistribucion['existe'] || !usuarioPuedeGestionarLocalGasto($user, $clasificacionDistribucion['cod_local'])) {
		echo json_encode(array('1' => 'NI', '2' => 'El gasto no existe o no pertenece a un local autorizado.'));
		exit;
	}
	$mysqliDistribucion= conectar_al_servidor();
	try {
		$distribucion= gastoDistribucionObtenerEfectiva($mysqliDistribucion, $idDistribucion);
		$items= array();
		foreach ($distribucion['asignaciones'] as $codLocalDistribucion => $montoDistribucion) {
			$codLocalDistribucion= (int)$codLocalDistribucion;
			$nombreLocalDistribucion= '';
			$stmtLocalDistribucion= $mysqliDistribucion->prepare('SELECT Nombre FROM local WHERE cod_local=? LIMIT 1');
			if ($stmtLocalDistribucion) {
				$stmtLocalDistribucion->bind_param('i', $codLocalDistribucion);
				$stmtLocalDistribucion->execute();
				$filaLocalDistribucion= $stmtLocalDistribucion->get_result()->fetch_assoc();
				$stmtLocalDistribucion->close();
				$nombreLocalDistribucion= $filaLocalDistribucion ? mb_convert_encoding((string)$filaLocalDistribucion['Nombre'], 'UTF-8', 'ISO-8859-1') : '';
			}
			$items[]= array('cod_local' => $codLocalDistribucion, 'nombre' => $nombreLocalDistribucion, 'monto' => (int)$montoDistribucion);
		}
		$mysqliDistribucion->close();
		echo json_encode(array(
			'1' => 'exito',
			'modo' => $distribucion['modo'],
			'asignaciones' => $items,
			'persistida' => $distribucion['persistida'] ? 1 : 0,
			'bloqueada_por_conciliacion' => $distribucion['bloqueada_por_conciliacion'] ? 1 : 0,
			'monto_total' => $distribucion['monto_total'],
			'cod_local_pago' => $distribucion['cod_local_pago'],
		));
	} catch (Exception $e) {
		$mysqliDistribucion->close();
		echo json_encode(array('1' => 'error', '2' => $e->getMessage()));
	}
	exit;
}

if ($operacion == 'estado_presupuesto_distribucion')
{
	if ((string)$user !== '2' && controldeaccesoacasas($user, 'VERLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1
		&& controldeaccesoacasas($user, 'EDITARLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1' => 'NI', '2' => 'No tiene permiso para consultar el presupuesto del movimiento.'));
		exit;
	}
	$idPresupuesto= isset($_POST['idgastos']) ? (int)$_POST['idgastos'] : 0;
	$clasificacionPresupuesto= obtenerClasificacionGastoActual($idPresupuesto);
	if (!$clasificacionPresupuesto['existe'] || !usuarioPuedeGestionarLocalGasto($user, $clasificacionPresupuesto['cod_local'])) {
		echo json_encode(array('1' => 'NI', '2' => 'El movimiento no existe o no pertenece a un local autorizado.'));
		exit;
	}
	$mysqliPresupuesto= conectar_al_servidor();
	try {
		$stmtPresupuesto= $mysqliPresupuesto->prepare("SELECT cod_motivoIngresoEgresoFK,fecha,tipo,estado FROM gastos WHERE idgastos=? LIMIT 1");
		$stmtPresupuesto->bind_param('i', $idPresupuesto);
		$stmtPresupuesto->execute();
		$gastoPresupuesto= $stmtPresupuesto->get_result()->fetch_assoc();
		$stmtPresupuesto->close();
		if (!$gastoPresupuesto || strtolower(trim((string)$gastoPresupuesto['tipo'])) !== 'egreso') {
			throw new Exception('El movimiento no corresponde a un egreso presupuestable.');
		}
		$fechaPresupuestoVisual= DateTime::createFromFormat('!Y-m-d', substr((string)$gastoPresupuesto['fecha'], 0, 10));
		if (!$fechaPresupuestoVisual) {
			throw new Exception('La fecha del movimiento no es valida.');
		}
		$distribucionPresupuesto= gastoDistribucionObtenerEfectiva($mysqliPresupuesto, $idPresupuesto);
		$itemsPresupuesto= array();
		foreach ($distribucionPresupuesto['asignaciones'] as $codLocalPresupuesto => $montoAsignadoPresupuesto) {
			$codLocalPresupuesto= (int)$codLocalPresupuesto;
			$codMotivoPresupuesto= (int)$gastoPresupuesto['cod_motivoIngresoEgresoFK'];
			$resultadoLimite= $mysqliPresupuesto->query("SELECT monto_limite FROM montos_limites_gasto_motivo WHERE cod_motivo_ingreso_egresoFK=$codMotivoPresupuesto AND cod_localFK=$codLocalPresupuesto ORDER BY cod_monto_limite_gasto_motivo");
			if (!$resultadoLimite) {
				throw new Exception('No se pudo consultar el presupuesto por sucursal.');
			}
			if ($resultadoLimite->num_rows > 1) {
				throw new Exception('Existe mas de un presupuesto configurado para una sucursal destino.');
			}
			$filaLimite= $resultadoLimite->fetch_assoc();
			$limiteLocal= $filaLimite ? (int)$filaLimite['monto_limite'] : 0;
			$usadoLocal= gastoDistribucionMontoUsadoPresupuesto(
				$mysqliPresupuesto,
				$codLocalPresupuesto,
				$codMotivoPresupuesto,
				$fechaPresupuestoVisual->format('Y-m-01'),
				$fechaPresupuestoVisual->format('Y-m-t')
			);
			$resultadoNombreLocal= $mysqliPresupuesto->query("SELECT Nombre FROM local WHERE cod_local=$codLocalPresupuesto LIMIT 1");
			$filaNombreLocal= $resultadoNombreLocal ? $resultadoNombreLocal->fetch_assoc() : null;
			$itemsPresupuesto[]= array(
				'cod_local' => $codLocalPresupuesto,
				'nombre' => $filaNombreLocal ? mb_convert_encoding((string)$filaNombreLocal['Nombre'], 'UTF-8', 'ISO-8859-1') : 'Sucursal #'.$codLocalPresupuesto,
				'monto_asignado' => (int)$montoAsignadoPresupuesto,
				'limite' => $limiteLocal,
				'utilizado' => $usadoLocal,
				'computable' => flujoGastoEstadoComputableResumen($gastoPresupuesto['estado']) ? 1 : 0,
			);
		}
		$mysqliPresupuesto->close();
		echo json_encode(array('1' => 'exito', 'destinos' => $itemsPresupuesto));
	} catch (Exception $e) {
		$mysqliPresupuesto->close();
		echo json_encode(array('1' => 'error', '2' => $e->getMessage()));
	}
	exit;
}

if($operacion=="obtener_crear_interconsulta_movimiento")
{
	$motivo= isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : '';
	$tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : 'Egreso';
	$cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : '';
	$cod_motivo= isset($_POST['cod_motivoFK']) ? mb_convert_encoding((string)($_POST['cod_motivoFK']), 'ISO-8859-1', 'UTF-8') : '';
	if (controldeaccesoacasas($user, 'INSERTARLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array("1" => "NI", "2" => "No tiene permiso para preparar un movimiento financiero."));
		exit;
	}
	if (!usuarioPuedeGestionarLocalGasto($user, $cod_local)) {
		echo json_encode(array("1" => "NI", "2" => "No puede preparar movimientos para el local seleccionado."));
		exit;
	}
	if (esTipoDepositoCentral($tipo)) {
		echo json_encode(array("1" => "error", "2" => "Los depositos a central no generan un Hilo financiero."));
		exit;
	}
	if (trim($motivo) == '' && is_numeric($cod_motivo)) {
		$registrosMotivo= buscarabmmotivoingresoegreso('', 'activo', $cod_motivo);
		if (isset($registrosMotivo[4][0]['descripcion'])) {
			$motivo= $registrosMotivo[4][0]['descripcion'];
		}
	}
	$cod_interConsulta= obtenerOCrearInterConsultaMovimientoFinanciero($motivo, $tipo, $user, $cod_local);
	if (!is_numeric($cod_interConsulta) || (int)$cod_interConsulta <= 0) {
		echo json_encode(array('1' => 'error', '2' => 'No se pudo obtener o crear el Hilo financiero.'));
		exit;
	}
	$informacion =array("1" => "exito", "2" => $cod_interConsulta, "3" => mb_convert_encoding((string)$motivo, 'UTF-8', 'ISO-8859-1'));
	echo json_encode($informacion);
	exit;
}

if($operacion=="nuevo" || $operacion=="editar")
{	
	$idgastos=$_POST['idgastos'];
$idgastos = mb_convert_encoding((string)($idgastos), 'ISO-8859-1', 'UTF-8');
$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);
	$motivo=$_POST['motivo'];
$motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$estado = ($estado == '' ? 'solicitado' : $estado);
$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$codcaja=$_POST['codcaja'];
$codcaja = mb_convert_encoding((string)($codcaja), 'ISO-8859-1', 'UTF-8');
$idaperturacierrecaja=$_POST['idaperturacierrecaja'];
$idaperturacierrecaja = mb_convert_encoding((string)($idaperturacierrecaja), 'ISO-8859-1', 'UTF-8');
$nroboleta=$_POST['nroboleta'];
$nroboleta = mb_convert_encoding((string)($nroboleta), 'ISO-8859-1', 'UTF-8');
$banco=$_POST['banco'];
$banco = mb_convert_encoding((string)($banco), 'ISO-8859-1', 'UTF-8');
$nrocuenta=$_POST['nrocuenta'];
$nrocuenta = mb_convert_encoding((string)($nrocuenta), 'ISO-8859-1', 'UTF-8');

$Arreglo=$_POST['Arreglo'];
$Arreglo = mb_convert_encoding((string)($Arreglo), 'ISO-8859-1', 'UTF-8');

$cod_usuario = $user;
$personales = "";

$cod_motivo= $_POST['cod_motivoFK'];
$cod_motivo= mb_convert_encoding((string)($cod_motivo), 'ISO-8859-1', 'UTF-8');

// El destino empresarial se define por el concepto, no por una persona receptora.
$esDepositoCentral= false;
if (is_numeric($cod_motivo) && (int)$cod_motivo > 0) {
	$motivoSeleccionado = buscarabmmotivoingresoegreso('', 'activo', (int)$cod_motivo);
	if (isset($motivoSeleccionado[4][0]['descripcion'])
		&& esConceptoDepositoCentral($motivoSeleccionado[4][0]['descripcion'])) {
		$esDepositoCentral= true;
		$tipo = 'Deposito';
		$motivo = 'Deposito bancario a Faraone Capital S.A.';
		$estado = ($operacion == 'editar' && strtolower(trim((string)$estado)) == 'inactivo') ? 'Inactivo' : 'Activo';
	}
}
if (esTipoDepositoCentral($tipo) && !$esDepositoCentral) {
	$informacion = array("1" => "error", "2" => "Seleccione un concepto de deposito a central habilitado.");
	echo json_encode($informacion);
	exit;
}
$permisoMovimiento= ($operacion == 'editar') ? 'EDITARLISTADOEGRESOINGRESO' : 'INSERTARLISTADOEGRESOINGRESO';
if (controldeaccesoacasas($user, $permisoMovimiento, " u.accion='SI' ") != 1) {
	$informacion= array("1" => "NI", "2" => "No tiene permiso para guardar este movimiento financiero.");
	echo json_encode($informacion);
	exit;
}
if (!usuarioPuedeGestionarLocalGasto($user, $cod_local)) {
	$informacion= array("1" => "NI", "2" => "No puede guardar movimientos para el local seleccionado.");
	echo json_encode($informacion);
	exit;
}
if ($operacion == 'editar') {
	$clasificacionActual= obtenerClasificacionGastoActual($idgastos);
	if (!$clasificacionActual['existe']) {
		$informacion= array("1" => "error", "2" => "El movimiento que intenta editar ya no existe.");
		echo json_encode($informacion);
		exit;
	}
	if (!usuarioPuedeGestionarLocalGasto($user, $clasificacionActual['cod_local'])) {
		$informacion= array("1" => "NI", "2" => "No puede editar el movimiento del local de origen.");
		echo json_encode($informacion);
		exit;
	}
	$eraDepositoCentral= esTipoDepositoCentral($clasificacionActual['tipo']);
	if ($eraDepositoCentral != $esDepositoCentral) {
		$mensajeClasificacion= $eraDepositoCentral
			? "Un deposito a central no puede convertirse en ingreso o egreso. Inactivelo y registre la correccion de forma trazable."
			: "Un movimiento existente no puede convertirse en deposito a central. Registre un deposito nuevo para conservar la trazabilidad.";
		$informacion= array("1" => "error", "2" => $mensajeClasificacion);
		echo json_encode($informacion);
		exit;
	}
}
if ($esDepositoCentral) {
	$permisoDeposito= ($operacion == 'editar') ? 'EDITARLISTADOEGRESOINGRESO' : 'INSERTARLISTADOEGRESOINGRESO';
	if (controldeaccesoacasas($user, $permisoDeposito, " u.accion='SI' ") != 1) {
		$informacion= array("1" => "NI", "2" => "No tiene permiso para registrar este deposito.");
		echo json_encode($informacion);
		exit;
	}
}
if ($esDepositoCentral && trim((string)$nroboleta) == '') {
	$informacion = array("1" => "error", "2" => "Ingrese el numero de comprobante del deposito.");
	echo json_encode($informacion);
	exit;
}
if ($esDepositoCentral && ((float)$monto <= 0 || strlen((string)$nroboleta) > 45)) {
	$informacion = array("1" => "error", "2" => ((float)$monto <= 0 ? "El monto del deposito debe ser mayor a cero." : "El numero de comprobante puede tener hasta 45 caracteres."));
	echo json_encode($informacion);
	exit;
}
if ($esDepositoCentral) {
	$errorDepositoCentral= validarDepositoCentralEnCaja($idgastos, $operacion, $idaperturacierrecaja, $codcaja, $cod_local, trim((string)$nroboleta), $monto, $fecha, $estado);
	if ($errorDepositoCentral != '') {
		$informacion= array("1" => "error", "2" => $errorDepositoCentral);
		echo json_encode($informacion);
		exit;
	}
}

$cod_interConsultaFK= $_POST['cod_interConsultaFK'];
$cod_interConsultaFK= mb_convert_encoding((string)($cod_interConsultaFK), 'ISO-8859-1', 'UTF-8');

$editar_cuotas= isset($_POST['editar_cuotas']) && (string)$_POST['editar_cuotas'] === 'true' ? 'true' : 'false';

$cod_proyecto_gastoFK= isset($_POST['cod_proyecto_gastoFK']) ? $_POST['cod_proyecto_gastoFK'] : '';
$cod_proyecto_gastoFK= mb_convert_encoding((string)($cod_proyecto_gastoFK), 'ISO-8859-1', 'UTF-8');
if (!is_numeric($cod_proyecto_gastoFK)) {
	$cod_proyecto_gastoFK= NULL;
}

$tipoAdjuntoDocumentoGasto= isset($_POST['tipo_adjunto_documento']) ? strtolower(trim((string)$_POST['tipo_adjunto_documento'])) : 'otro';
if (!in_array($tipoAdjuntoDocumentoGasto, array('factura','comprobante','otro'), true)) {
	$informacion= array("1" => "error", "2" => "El tipo de documento del movimiento no es valido.");
	echo json_encode($informacion);
	exit;
}
$datosDocumentoGasto= array();
if (isset($_POST['datos_documento']) && trim((string)$_POST['datos_documento']) != '') {
	$datosDocumentoDecodificados= json_decode((string)$_POST['datos_documento'], true);
	if (!is_array($datosDocumentoDecodificados)) {
		$informacion= array("1" => "error", "2" => "Los datos del documento no tienen un formato valido.");
		echo json_encode($informacion);
		exit;
	}
	$datosDocumentoGasto= $datosDocumentoDecodificados;
}
$fotoDocumentoGasto= isset($_POST['foto']) ? (string)$_POST['foto'] : '';
$extensionDocumentoGasto= isset($_POST['ext']) ? strtolower(trim((string)$_POST['ext'])) : '';
$nombreDocumentoGasto= isset($_POST['nombre_archivo_documento']) && trim((string)$_POST['nombre_archivo_documento']) != ''
	? (string)$_POST['nombre_archivo_documento']
	: 'documento.'.$extensionDocumentoGasto;
$hayArchivoDocumentoGasto= trim($fotoDocumentoGasto) != '' || trim($extensionDocumentoGasto) != '';
$archivoPreparadoDocumentoGasto= array();
if ($hayArchivoDocumentoGasto) {
	$archivoPreparadoDocumentoGasto= centroFacturaPrepararArchivo(array('data' => $fotoDocumentoGasto, 'nombre' => $nombreDocumentoGasto));
	if (empty($archivoPreparadoDocumentoGasto['ok'])) {
		$informacion= array("1" => "error", "2" => $archivoPreparadoDocumentoGasto['mensaje']);
		echo json_encode(centroFacturaValorUtf8($informacion), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}
	// El guardador legacy utiliza esta extension para construir una ruta publica.
	// Nunca se conserva la extension enviada por el navegador: se deriva del MIME real.
	$_POST['ext']= $archivoPreparadoDocumentoGasto['extension'];
}
$esDocumentoFinancieroGasto= in_array($tipoAdjuntoDocumentoGasto, array('factura','comprobante'), true);
if ($operacion == 'editar' && intval($idgastos) > 0 && $hayArchivoDocumentoGasto) {
	$mysqliDocumentoExistente= conectar_al_servidor();
	if (centroFacturaEstructuraDisponible($mysqliDocumentoExistente)) {
		$stmtDocumentoExistente= $mysqliDocumentoExistente->prepare("SELECT id_factura FROM centro_factura WHERE idgastosFK=? LIMIT 1");
		if (!$stmtDocumentoExistente) {
			$mysqliDocumentoExistente->close();
			echo json_encode(array("1" => "error", "2" => "No se pudo comprobar el documento existente del movimiento."));
			exit;
		}
		$idGastoDocumento= intval($idgastos);
		$stmtDocumentoExistente->bind_param('i', $idGastoDocumento);
		if (!$stmtDocumentoExistente->execute()) {
			$stmtDocumentoExistente->close();
			$mysqliDocumentoExistente->close();
			echo json_encode(array("1" => "error", "2" => "No se pudo comprobar el documento existente del movimiento."));
			exit;
		}
		$documentoExistente= $stmtDocumentoExistente->get_result()->fetch_assoc();
		$stmtDocumentoExistente->close();
		if ($documentoExistente) {
			$mysqliDocumentoExistente->close();
			echo json_encode(array(
				"1" => "error",
				"2" => "El comprobante principal esta vinculado al Centro de Facturas y Documentos. Para reemplazarlo, revise o anule primero el registro documental existente."
			));
			exit;
		}
	}
	$mysqliDocumentoExistente->close();
}
if ($esDocumentoFinancieroGasto) {
	if (strtolower(trim((string)$tipo)) != 'egreso') {
		$informacion= array("1" => "error", "2" => "Las facturas y recibos recibidos solo pueden registrarse desde un egreso.");
		echo json_encode($informacion);
		exit;
	}
	if (!centroFacturaTienePermiso($user, 'REGISTRARFACTURAMANUAL')) {
		$informacion= array("1" => "NI", "2" => "No tiene permiso para registrar documentos en el Centro de Facturas y Documentos.");
		echo json_encode($informacion);
		exit;
	}
	if (!$hayArchivoDocumentoGasto || empty($archivoPreparadoDocumentoGasto['ok'])) {
		$informacion= array("1" => "error", "2" => "Seleccione el archivo de la factura o del recibo.");
		echo json_encode($informacion);
		exit;
	}
	$mysqliValidacionDocumento= conectar_al_servidor();
	if (!centroFacturaEstructuraDisponible($mysqliValidacionDocumento)) {
		$mysqliValidacionDocumento->close();
		$informacion= array("1" => "error", "2" => "La estructura del Centro de Facturas y Documentos no esta disponible.");
		echo json_encode($informacion);
		exit;
	}
	if (!centroFacturaPuedeUsarLocal($user, $cod_local, $mysqliValidacionDocumento)) {
		$mysqliValidacionDocumento->close();
		$informacion= array("1" => "NI", "2" => "No puede registrar documentos en el Centro de Facturas y Documentos para el local seleccionado.");
		echo json_encode($informacion);
		exit;
	}
	$validacionDocumentoGasto= centroFacturaValidarAdjuntoDocumental($mysqliValidacionDocumento, $tipoAdjuntoDocumentoGasto, $datosDocumentoGasto);
	$mysqliValidacionDocumento->close();
	if (empty($validacionDocumentoGasto['ok'])) {
		$informacion= array("1" => "error", "2" => $validacionDocumentoGasto['mensaje']);
		echo json_encode(centroFacturaValorUtf8($informacion), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}
}
$fotoDocumentoFirmadoSolicitado= isset($_POST['foto_documento_firmado']) ? (string)$_POST['foto_documento_firmado'] : '';
$extensionDocumentoFirmadoSolicitada= isset($_POST['ext_documento_firmado']) ? strtolower(trim((string)$_POST['ext_documento_firmado'])) : '';
if (trim($fotoDocumentoFirmadoSolicitado) != '' || trim($extensionDocumentoFirmadoSolicitada) != '') {
	$archivoFirmadoPreparado= centroFacturaPrepararArchivo(array(
		'data' => $fotoDocumentoFirmadoSolicitado,
		'nombre' => 'documento_firmado.'.$extensionDocumentoFirmadoSolicitada
	));
	if (empty($archivoFirmadoPreparado['ok'])) {
		$informacion= array("1" => "error", "2" => $archivoFirmadoPreparado['mensaje']);
		echo json_encode(centroFacturaValorUtf8($informacion), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}
	$_POST['ext_documento_firmado']= $archivoFirmadoPreparado['extension'];
}

	// Valida la distribucion contable por sucursal y su presupuesto.
	$fechaRango= DateTime::createFromFormat('Y-m-d', $fecha);
	if (!$fechaRango || $fechaRango->format('Y-m-d') != $fecha) {
		$informacion = array("1" => "error", "2" => "Ingrese una fecha valida para el movimiento.");
		echo json_encode($informacion);
		exit;
	}
	$primerDiaMes= $fechaRango->format('Y-m-01');
	$ultimoDiaMes= $fechaRango->format('Y-m-t');
	// Compatibilidad con clientes anteriores: Administracion ya se repartia virtualmente
	// entre las cinco sucursales de los graficos. Solo aplicamos este valor por omision
	// cuando el campo no fue enviado; un modo enviado pero invalido se rechaza.
	$modoDistribucionFueEnviado= isset($_POST['modo_distribucion']);
	$conservarDistribucionLegacy= isset($_POST['conservar_distribucion_legacy']) && (string)$_POST['conservar_distribucion_legacy'] === '1';
	$modoDistribucionGasto= $modoDistribucionFueEnviado
		? (string)$_POST['modo_distribucion']
		: ((int)$cod_local === 1 ? 'compartido' : 'local');
	$jsonDistribucionGasto= isset($_POST['distribucion_locales']) ? (string)$_POST['distribucion_locales'] : '[]';
	$idMovimientoUenoGasto= isset($_POST['id_movimiento_ueno']) ? (int)$_POST['id_movimiento_ueno'] : 0;
	$distribucionGasto= array('modo' => '', 'asignaciones' => array());
	$mysqliValidacionDistribucion= conectar_al_servidor();
	try {
		$distribucionPersistidaEdicion= $operacion == 'editar'
			? gastoDistribucionObtenerFilas($mysqliValidacionDistribucion, $idgastos)
			: array('modo' => '', 'asignaciones' => array(), 'persistida' => false);
		if (!empty($distribucionPersistidaEdicion['persistida']) && strtolower(trim((string)$tipo)) != 'egreso') {
			throw new Exception('Un egreso distribuido no puede convertirse en ingreso. Registre una correccion separada para conservar su trazabilidad.');
		}
		if ($conservarDistribucionLegacy) {
			if ($operacion != 'editar' || strtolower(trim((string)$tipo)) != 'egreso') {
				throw new Exception('La conservacion legacy solo corresponde a la edicion de un egreso historico.');
			}
			$distribucionActualEdicion= gastoDistribucionObtenerEfectiva($mysqliValidacionDistribucion, $idgastos);
			if (empty($distribucionActualEdicion['legacy_no_materializable'])) {
				throw new Exception('El movimiento ya posee una distribucion materializable. Actualice la pantalla.');
			}
			$distribucionGasto= array('modo' => 'legacy_no_materializable', 'asignaciones' => array(), 'conservar_legacy' => true);
		} else if ($operacion == 'editar' && !$modoDistribucionFueEnviado && !empty($distribucionPersistidaEdicion['persistida'])) {
			$montoSolicitudDistribucion= gastoDistribucionMontoEntero($monto);
			$distribucionActualEdicion= gastoDistribucionObtenerEfectiva($mysqliValidacionDistribucion, $idgastos);
			if ($montoSolicitudDistribucion !== (int)$distribucionActualEdicion['monto_total']
				|| (int)$cod_local !== (int)$distribucionActualEdicion['cod_local_pago']) {
				throw new Exception('Este gasto ya tiene una distribucion por sucursal. Actualice la pantalla antes de cambiar su monto o local de pago.');
			}
			$distribucionGasto= array(
				'modo' => $distribucionActualEdicion['modo'],
				'asignaciones' => $distribucionActualEdicion['asignaciones'],
			);
			gastoDistribucionValidarLocales($mysqliValidacionDistribucion, $distribucionGasto['asignaciones'], $user, $cod_local, $distribucionGasto['modo']);
		} else {
			$distribucionGasto= gastoDistribucionNormalizarSolicitud(
				$mysqliValidacionDistribucion,
				$tipo,
				$cod_local,
				$monto,
				$modoDistribucionGasto,
				$jsonDistribucionGasto,
				$user
			);
		}
		// El presupuesto se revalida dentro de la misma transaccion que guarda el
		// movimiento. Esta fase solo normaliza la solicitud para responder rapido.
		if ($idMovimientoUenoGasto > 0) {
			if ($operacion != 'nuevo' || strtolower(trim((string)$tipo)) != 'egreso') {
				throw new Exception('Un debito Ueno solo puede vincularse al crear un egreso nuevo.');
			}
			if (!gastoDistribucionUsuarioPuedeConciliarUeno($user)) {
				throw new Exception('No tiene permiso para conciliar egresos con Ueno.');
			}
		}
		$mysqliValidacionDistribucion->close();
	} catch (Exception $e) {
		$mysqliValidacionDistribucion->close();
		echo json_encode(array('1' => 'error', '2' => $e->getMessage()));
		exit;
	}

	$informacion= abmGasto($Arreglo,$nroboleta, $banco , $nrocuenta ,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion,$editar_cuotas, $cod_proyecto_gastoFK, $distribucionGasto, $idMovimientoUenoGasto);
	$archivoPrincipalDisponible= !isset($informacion['archivo']) || !array_key_exists('principal_ok', $informacion['archivo']) || !empty($informacion['archivo']['principal_ok']);
	if ($esDocumentoFinancieroGasto && $archivoPrincipalDisponible && isset($informacion['1']) && $informacion['1'] == 'exito' && !empty($informacion['2'])) {
		$informacion['documento']= centroFacturaRegistrarDesdeGasto(
			$informacion['2'], $user,
			array('tipo_adjunto' => $tipoAdjuntoDocumentoGasto, 'datos_documento' => $datosDocumentoGasto),
			$archivoPreparadoDocumentoGasto
		);
		if (empty($informacion['documento']['ok'])) {
			$informacion['parcial']= 1;
		}
	}
	echo json_encode($informacion);
	exit;
}
if ($operacion=='cargar_imagen') {
	if (controldeaccesoacasas($user, 'EDITARLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array("1" => "NI", "2" => "No tiene permiso para reemplazar adjuntos de movimientos."));
		exit;
	}
	$idgastos= isset($_POST['idgastos']) ? intval($_POST['idgastos']) : 0;
	$clasificacionAdjunto= obtenerClasificacionGastoActual($idgastos);
	if (!$clasificacionAdjunto['existe'] || !usuarioPuedeGestionarLocalGasto($user, $clasificacionAdjunto['cod_local'])) {
		echo json_encode(array("1" => "NI", "2" => "El movimiento no existe o no pertenece a un local autorizado."));
		exit;
	}
	$estadoAdjuntoMovimiento= strtolower(trim((string)$clasificacionAdjunto['estado']));
	if (in_array($estadoAdjuntoMovimiento, array('inactivo','anulado','baja'), true)) {
		echo json_encode(array("1" => "error", "2" => "Un movimiento inactivo, anulado o dado de baja no admite reemplazo de adjuntos."));
		 exit;
	}
	$mysqliConceptoAdjunto= conectar_al_servidor();
	$conceptoAdjuntoActivo= gastoConceptosActivosParaIds($mysqliConceptoAdjunto, array($idgastos), false);
	$mysqliConceptoAdjunto->close();
	if (!$conceptoAdjuntoActivo) {
		echo json_encode(array("1" => "error", "2" => "El concepto financiero es historico y este movimiento es de solo lectura."));
		exit;
	}
	$foto= isset($_POST['foto']) ? (string)$_POST['foto'] : '';
	$ext= isset($_POST['ext']) ? strtolower(trim((string)$_POST['ext'])) : '';
	$foto_documento_firmado= isset($_POST['foto_documento_firmado']) ? $_POST['foto_documento_firmado'] : '';
	$ext_documento_firmado= isset($_POST['ext_documento_firmado']) ? $_POST['ext_documento_firmado'] : '';
	if (trim($foto) != '' || trim($ext) != '') {
		$mysqliVinculoDocumento= conectar_al_servidor();
		if (centroFacturaEstructuraDisponible($mysqliVinculoDocumento)) {
			$stmtVinculoDocumento= $mysqliVinculoDocumento->prepare("SELECT id_factura FROM centro_factura WHERE idgastosFK=? LIMIT 1");
			if (!$stmtVinculoDocumento) {
				$mysqliVinculoDocumento->close();
				echo json_encode(array("1" => "error", "2" => "No se pudo comprobar el vinculo documental del movimiento."));
				exit;
			}
			$stmtVinculoDocumento->bind_param('i', $idgastos);
			if (!$stmtVinculoDocumento->execute()) {
				$stmtVinculoDocumento->close();
				$mysqliVinculoDocumento->close();
				echo json_encode(array("1" => "error", "2" => "No se pudo comprobar el vinculo documental del movimiento."));
				exit;
			}
			$documentoVinculado= $stmtVinculoDocumento->get_result()->fetch_assoc();
			$stmtVinculoDocumento->close();
			if ($documentoVinculado) {
				$mysqliVinculoDocumento->close();
				echo json_encode(array("1" => "error", "2" => "El comprobante principal esta vinculado al Centro de Facturas y Documentos y no puede reemplazarse de forma silenciosa."));
				exit;
			}
		}
		$mysqliVinculoDocumento->close();
	}
	if ((trim($foto) != '' || trim($ext) != '')) {
		$archivoAdjunto= centroFacturaPrepararArchivo(array('data' => $foto, 'nombre' => 'documento.'.$ext));
		if (empty($archivoAdjunto['ok'])) {
			echo json_encode(array("1" => "error", "2" => centroFacturaValorUtf8($archivoAdjunto['mensaje'])));
			exit;
		}
		$ext= $archivoAdjunto['extension'];
	}
	if ((trim((string)$foto_documento_firmado) != '' || trim((string)$ext_documento_firmado) != '')) {
		$archivoFirmado= centroFacturaPrepararArchivo(array('data' => $foto_documento_firmado, 'nombre' => 'documento_firmado.'.$ext_documento_firmado));
		if (empty($archivoFirmado['ok'])) {
			echo json_encode(array("1" => "error", "2" => centroFacturaValorUtf8($archivoFirmado['mensaje'])));
			exit;
		}
		$ext_documento_firmado= $archivoFirmado['extension'];
	}
	if (!subirImagenGasto($idgastos, $foto, $ext)
		|| !subirDocumentoFirmadoGasto($idgastos, $foto_documento_firmado, $ext_documento_firmado)) {
		echo json_encode(array("1" => "error", "2" => "No se pudo guardar el adjunto del movimiento."));
		exit;
	}
	$informacion =array("1" => "exito", "2" => $idgastos);
	echo json_encode($informacion);	
	exit;
}

if($operacion=="buscar")
{
	if ((string)$user !== '2' && controldeaccesoacasas($user, 'VERLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1'=>'NI', '2'=>'No tiene permiso para consultar movimientos financieros.'));
		exit;
	}
	$fecha1=$_POST['fecha1'];
	$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST['fecha2'];
	$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
	$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$cod_local=$_POST['cod_local'];
	$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	$tipo=$_POST['tipo'];
	$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	$usuario=$_POST['usuario'];
	$usuario = mb_convert_encoding((string)($usuario), 'ISO-8859-1', 'UTF-8');
	$fecha=$_POST['fecha'];
	$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');

	$arreglo=$_POST['arreglo'];
	$arreglo = mb_convert_encoding((string)($arreglo), 'ISO-8859-1', 'UTF-8');
	$cod_interConsultaFK=$_POST['cod_interConsultaFK'];
	$cod_interConsultaFK = mb_convert_encoding((string)($cod_interConsultaFK), 'ISO-8859-1', 'UTF-8');
	$nombre_interConsulta=$_POST['nombre_interConsulta'];
	$nombre_interConsulta = mb_convert_encoding((string)($nombre_interConsulta), 'ISO-8859-1', 'UTF-8');
	$cod_motivoFK= $_POST['cod_motivoFK'];
	$cod_motivoFK= mb_convert_encoding((string)($cod_motivoFK), 'ISO-8859-1', 'UTF-8');
	$ocultar_inactivos= $_POST['ocultar_inactivos'];
	$ocultar_inactivos= mb_convert_encoding((string)($ocultar_inactivos), 'ISO-8859-1', 'UTF-8');
	$errorFiltroBusqueda= '';
	foreach (array('concepto' => $cod_motivoFK, 'Hilo' => $cod_interConsultaFK) as $nombreIdFiltro => $valorIdFiltro) {
		if (trim((string)$valorIdFiltro) !== '' && !preg_match('/^[0-9]+$/', trim((string)$valorIdFiltro))) {
			$errorFiltroBusqueda= 'El filtro de '.$nombreIdFiltro.' no es valido.';
			break;
		}
	}
	foreach (array('fecha desde' => $fecha1, 'fecha hasta' => $fecha2, 'fecha exacta' => $fecha) as $nombreFechaFiltro => $valorFechaFiltro) {
		$valorFechaFiltro= trim((string)$valorFechaFiltro);
		if ($valorFechaFiltro === '') {
			continue;
		}
		$fechaFiltroBusqueda= DateTime::createFromFormat('!Y-m-d', $valorFechaFiltro);
		if (!$fechaFiltroBusqueda || $fechaFiltroBusqueda->format('Y-m-d') !== $valorFechaFiltro) {
			$errorFiltroBusqueda= 'La '.$nombreFechaFiltro.' no es valida.';
			break;
		}
	}
	$estadosBusqueda= array('' => '', 'activo' => 'Activo', 'inactivo' => 'Inactivo', 'pendiente' => 'pendiente', 'solicitado' => 'solicitado', 'rechazado' => 'Rechazado', 'baja' => 'Baja');
	$tiposBusqueda= array('' => '', 'ingreso' => 'Ingreso', 'egreso' => 'Egreso', 'deposito' => 'Deposito', 'depósito' => 'Deposito');
	$arreglosBusqueda= array('' => '', 'interno' => 'INTERNO', 'externo' => 'EXTERNO');
	$claveEstadoBusqueda= strtolower(trim((string)$estado));
	$claveTipoBusqueda= strtolower(trim((string)$tipo));
	$claveArregloBusqueda= strtolower(trim((string)$arreglo));
	if (!isset($estadosBusqueda[$claveEstadoBusqueda]) || !isset($tiposBusqueda[$claveTipoBusqueda]) || !isset($arreglosBusqueda[$claveArregloBusqueda])) {
		$errorFiltroBusqueda= 'Uno de los filtros de estado, tipo u origen no es valido.';
	}
	if (!in_array(strtolower(trim((string)$ocultar_inactivos)), array('true','false',''), true)) {
		$errorFiltroBusqueda= 'El filtro de movimientos inactivos no es valido.';
	}
	if (strlen((string)$usuario) > 200 || strlen((string)$nombre_interConsulta) > 200) {
		$errorFiltroBusqueda= 'El texto de busqueda es demasiado largo.';
	}
	if ($errorFiltroBusqueda !== '') {
		echo json_encode(array('1'=>'error', '2'=>$errorFiltroBusqueda));
		exit;
	}
	$estado= $estadosBusqueda[$claveEstadoBusqueda];
	$tipo= $tiposBusqueda[$claveTipoBusqueda];
	$arreglo= $arreglosBusqueda[$claveArregloBusqueda];
	$ocultar_inactivos= strtolower(trim((string)$ocultar_inactivos));
	$controllocal= controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if ($controllocal != 1) {
		// El filtro recibido no es una autorizacion: sin CAMBIARLOCAL siempre se
		// consulta exclusivamente el local propio, aunque el POST haya sido forjado.
		$cod_local= buscarlocaluser($user);
	} elseif ($cod_local !== '') {
		$mysqliLocalBusqueda= conectar_al_servidor();
		$localValidoBusqueda= is_numeric($cod_local) && gastoLocalEstaActivo($mysqliLocalBusqueda, (int)$cod_local);
		$mysqliLocalBusqueda->close();
		if (!$localValidoBusqueda) {
			echo json_encode(array('1'=>'error', '2'=>'El local solicitado no existe o no esta activo.'));
			exit;
		}
	}
	$idgastos= "";

	$informacion = buscarGastoConMotivos($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, '', '', $idgastos, 'DESC', $user);
	echo json_encode($informacion);
	exit;
}	
if($operacion=="evaluacionGasto")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');

	buscarevaluacionGasto($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionpagosventa")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');

	evaluacionpagosventa($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionproductodcomprados")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');

	evaluacionproductodcomprados($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionproductodvendidos")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');

	evaluacionproductodvendidos($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionpagoscomprados")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');

	evaluacionpagoscomprados($fecha1,$fecha2,$local);

}

if($operacion=="evaluacion")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');

	buscarevaluacion($fecha1,$fecha2,$local);

}	

if ($operacion == "agregarLimiteCaja") {
	$limite_monto = $_POST['monto'];
	$limite_monto = quitarseparadormiles($limite_monto);

	agregarLimiteCaja($user, $limite_monto);
}

if ($operacion == "obtenerUltimoLimiteCaja") {
	$registros= obtenerLimiteCaja();
	$monto_limite = end($registros);

	$informacion =array("1" => "exito","2" => $monto_limite['limite_monto']);
	echo json_encode($informacion);
}

if($operacion=="buscarabmmotivoingresoegreso")
{


$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

	$informacion = buscarabmmotivoingresoegreso($buscar,$estado);
	echo json_encode($informacion);	
	exit;
}


if($operacion=="NuevoMotivo")
{
	$motivo=$_POST['motivo'];
$motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

$categoria=$_POST['categoria'];
$categoria = mb_convert_encoding((string)($categoria), 'ISO-8859-1', 'UTF-8');

$necesita_autorizacion= $_POST['necesita_autorizacion'];
$necesita_autorizacion = mb_convert_encoding((string)($necesita_autorizacion), 'ISO-8859-1', 'UTF-8');

	NuevoMotivo($motivo,$estado,$categoria,$necesita_autorizacion);

}

if($operacion=="editarMotivo")
{
	$motivo=$_POST['motivo'];
$motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

$idabm=$_POST['idabm'];
$idabm = mb_convert_encoding((string)($idabm), 'ISO-8859-1', 'UTF-8');

$categoria=$_POST['categoria'];
$categoria = mb_convert_encoding((string)($categoria), 'ISO-8859-1', 'UTF-8');

$necesita_autorizacion= $_POST['necesita_autorizacion'];
$necesita_autorizacion = mb_convert_encoding((string)($necesita_autorizacion), 'ISO-8859-1', 'UTF-8');


	editarMotivo($motivo,$estado,$categoria,$necesita_autorizacion, $user, $idabm);

}	

if($operacion=="buscaroption")
{
	$categoria= isset($_POST['categoria']) ? $_POST['categoria'] : '';
	$categoria= mb_convert_encoding((string)($categoria), 'ISO-8859-1', 'UTF-8');
	buscaroption($categoria);
}

if ($operacion == "aprobarMovimiento") {
	$idgastos= $_POST['idgastos'];
	$idgastos= mb_convert_encoding((string)($idgastos), 'ISO-8859-1', 'UTF-8');
	$decision= $_POST['decision'];
	$decision= mb_convert_encoding((string)($decision), 'ISO-8859-1', 'UTF-8');
	aprobarMovimiento($idgastos, $user, $decision);
}
if ($operacion == "darBajaCuotaProgramada") {
	$idgastos= isset($_POST['idgastos']) ? intval($_POST['idgastos']) : 0;
	$alcance= isset($_POST['alcance']) ? (string)$_POST['alcance'] : 'cuota';
	darBajaCuotaProgramada($idgastos, $alcance, $user);
}
if ($operacion == "combinarmotivoingresoegreso") {
	$cod_motivoIngresoEgreso= mb_convert_encoding((string)($_POST['cod_motivo_ingreso_egreso']), 'ISO-8859-1', 'UTF-8');
	$cod_motivoIngresoEgreso_dest= mb_convert_encoding((string)($_POST['cod_motivo_ingreso_egreso_destino']), 'ISO-8859-1', 'UTF-8');

	combinarMotivoIngresoEgreso($cod_motivoIngresoEgreso, $cod_motivoIngresoEgreso_dest, $user);
}
if ($operacion == "buscarResumenGastosMotivo") {
	if ((string)$user !== '2' && controldeaccesoacasas($user, 'VERLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1'=>'NI', '2'=>'No tiene permiso para consultar el resumen financiero.'));
		exit;
	}
	$fecha_inicio= mb_convert_encoding((string)($_POST['fecha_inicio']), 'ISO-8859-1', 'UTF-8');
	$fecha_fin= mb_convert_encoding((string)($_POST['fecha_fin']), 'ISO-8859-1', 'UTF-8');
	if (!gastoFechaFiltroIsoValida($fecha_inicio) || !gastoFechaFiltroIsoValida($fecha_fin)) {
		echo json_encode(array('1'=>'error', '2'=>'El rango de fechas no es valido.'));
		exit;
	}

	buscarResumenGastosMotivo($fecha_inicio, $fecha_fin);
}



if ($operacion == "buscarProximosPagos") {
	if ((string)$user !== '2' && controldeaccesoacasas($user, 'VERLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1'=>'NI', '2'=>'No tiene permiso para consultar pagos programados.'));
		exit;
	}
	$fecha_inicio= mb_convert_encoding((string)($_POST['fecha1']), 'ISO-8859-1', 'UTF-8');
	$fecha_fin= mb_convert_encoding((string)($_POST['fecha2']), 'ISO-8859-1', 'UTF-8');
	$local= mb_convert_encoding((string)($_POST['local']), 'ISO-8859-1', 'UTF-8');
	if (controldeaccesoacasas($user, 'CAMBIARLOCAL', " u.accion='SI' ") != 1 && (string)$user !== '2') {
		$local= buscarlocaluser($user);
	} else if ($local !== '') {
		$mysqliLocalProximos= conectar_al_servidor();
		$localValidoProximos= is_numeric($local) && gastoLocalEstaActivo($mysqliLocalProximos, (int)$local);
		$mysqliLocalProximos->close();
		if (!$localValidoProximos) {
			echo json_encode(array('1'=>'error', '2'=>'El local solicitado no existe o no esta activo.'));
			exit;
		}
	}
	$descripcion= mb_convert_encoding((string)($_POST['descripcion']), 'ISO-8859-1', 'UTF-8');
	$estadoFiltroPagoprogrtamado= mb_convert_encoding((string)($_POST['estadoFiltroPagoprogrtamado']), 'ISO-8859-1', 'UTF-8');
	if (!gastoFechaFiltroIsoValida($fecha_inicio) || !gastoFechaFiltroIsoValida($fecha_fin)
		|| ($fecha_inicio === '') !== ($fecha_fin === '')
		|| ($fecha_inicio !== '' && $fecha_inicio > $fecha_fin)) {
		echo json_encode(array('1'=>'error', '2'=>'El rango de fechas no es valido.'));
		exit;
	}
	if (strlen((string)$descripcion) > 200) {
		echo json_encode(array('1'=>'error', '2'=>'La descripcion de busqueda es demasiado larga.'));
		exit;
	}
	if (!in_array($estadoFiltroPagoprogrtamado, array('Todo','Pendiente'), true)) {
		echo json_encode(array('1'=>'error', '2'=>'El filtro de estado no es valido.'));
		exit;
	}

	buscarProximosPagos($fecha_inicio,$fecha_fin,$local,$descripcion,$estadoFiltroPagoprogrtamado);
}

if ($operacion == "obtenerGastosAsociados") {
	$puedeVerGastos= ((string)$user === '2'
		|| controldeaccesoacasas($user, 'VERLISTADOEGRESOINGRESO', " u.accion='SI' ") == 1
		|| controldeaccesoacasas($user, 'EDITARLISTADOEGRESOINGRESO', " u.accion='SI' ") == 1);
	if (!$puedeVerGastos) {
		echo json_encode(array('1'=>'NI', '2'=>'No tiene permiso para consultar cuotas asociadas.'));
		exit;
	}
	$idgastos= isset($_POST['idgastos']) ? (int)$_POST['idgastos'] : 0;
	$clasificacionGastoAsociado= obtenerClasificacionGastoActual($idgastos);
	if (!$clasificacionGastoAsociado['existe'] || !usuarioPuedeGestionarLocalGasto($user, $clasificacionGastoAsociado['cod_local'])) {
		echo json_encode(array('1'=>'NI', '2'=>'El movimiento no existe o no pertenece a un local autorizado.'));
		exit;
	}
	
	$gastos= obtenerGastosAsociados($idgastos);
	$gastos= array_values(array_filter($gastos, function($gasto) use ($user) {
		return isset($gasto['cod_local']) && usuarioPuedeGestionarLocalGasto($user, $gasto['cod_local']);
	}));
	$tieneSerieEditable= false;
	$mysqliSerieEditable= conectar_al_servidor();
	try {
		$serieEditable= gastoResolverSerieParaEdicion($mysqliSerieEditable, $idgastos, 'true');
		$tieneSerieEditable= count($serieEditable['ids']) > 1;
	} catch (Exception $e) {
		$tieneSerieEditable= false;
	}
	$mysqliSerieEditable->close();

	$total_pendiente= 0;
	// Prepara la vista
	$pagina= "";
	foreach ($gastos as $key => $gast) {
		$estadoOriginalGasto= strtolower(trim((string)(isset($gast['estado']) ? $gast['estado'] : '')));
		$gastoPagado= ($estadoOriginalGasto == 'activo');
		$soloLecturaHistorica= strtolower(trim((string)(isset($gast['estado_motivo']) ? $gast['estado_motivo'] : ''))) !== 'activo';
		if ($gast['estado'] == 'pendiente' || $gast['estado'] == 'solicitado') {
			$total_pendiente += $gast['monto'];
		}
		$estado= '<span style="text-transform: capitalize;" class="badge bg-';
		switch ($gast['estado']) {
			case 'Activo':
				$estado .= 'primary">Pagado</span>';
				break;
			case 'Rechazado':
				$estado .= 'secondary">'.$gast['estado'].'</span>';
				break;
			case 'Baja':
				$estado .= 'secondary">Dado de baja</span>';
				break;
			case 'pendiente':
				$fechaActual = date('Y-m-d');
				$fechaGasto = date('Y-m-d', strtotime($gast['fecha']));
				if ($fechaActual >= $fechaGasto && !$soloLecturaHistorica) {
					$estado .= 'danger">solicitado</span>'
					.'<i class="fa-solid fa-check" onclick="event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: green; padding: 2px;border-radius: 5px;margin-left: 5px;"></i>'
					.'<i class="fa-solid fa-xmark" onclick="event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: red; padding: 2px;border-radius: 5px;"></i>';
				} else {
					$estado .= 'warning">'.$gast['estado'].'</span>';
				}
				break;
			case 'solicitado':
				$fechaActual = date('Y-m-d');
				$fechaGasto = date('Y-m-d', strtotime($gast['fecha']));
				if ($fechaActual >= $fechaGasto && !$soloLecturaHistorica) {
					$estado .= 'danger">'.$gast['estado'].'</span>'
					.'<i class="fa-solid fa-check" onclick="event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: green; padding: 2px;border-radius: 5px;margin-left: 5px;"></i>'
					.'<i class="fa-solid fa-xmark" onclick="event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: red; padding: 2px;border-radius: 5px;"></i>';
				} else {
					$estado .= 'warning">Pendiente</span>';
				}
				break;
		}
		$indicadorConciliacionUeno= "";
		$botonConciliarUeno= "";
		if (!$soloLecturaHistorica && !flujoGastoEstaAnulado($gast) && !$gastoPagado) {
			$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno(isset($gast['idgastos']) ? $gast['idgastos'] : '', isset($gast['monto']) ? $gast['monto'] : 0);
			$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
			$botonConciliarUeno= construirBotonConciliarEgresoUeno($gast, 'Extracto de pago');
		}

		if ($soloLecturaHistorica) {
			$estado .= " <span class='flujo-pago-unico-solo-lectura'>Hist&oacute;rico &middot; solo lectura</span>";
		}
		$pagina .= "<table border='1' cellspacing='1' cellpadding='5' class='tableRegistroSearch2'><tr id='tbSelecRegistro' id='tbSelecRegistro' onclick='".($soloLecturaHistorica ? "" : "seleccionarGastosAsociados(this);")."' style='".($estado=="Rechazado" || $estado=="Inactivo" ? "text-decoration: line-through;" : "").";text-align: center;'>
			<td id='td_id' style='width:5%; display: none; background-color: #efeded;color:red;'>".$gast['idgastos']."</td>
			<td  style='width:10%;border: none;'>".($key + 1)."/".count($gastos)."</td>
			<td  id='td_datos_3' style='width:15%;border: none;'>".$gast['fecha']."</td>
			<td  style='border: none;'>".$gast['descripcion']."</td>
			<td  id='td_datos_5' style='width: 20%;border: none;'>".$estado."<div class='extracto-gasto-conciliar-actions'>".$botonConciliarUeno."</div></td>
			<td  id='td_datos_1' style='width: 15%;border: none;'>". number_format($gast['monto'],'0',',','.').$indicadorConciliacionUeno."</td>
			<td  id='td_datos_2' style='width:10%; display: none;'>".$gast['motivo']."</td>
			<td  id='td_datos_16' style='display: none;'>".$gast['interconsulta_nombre']."</td>
			<td  id='td_datos_21' style='display: none;'>".$gast['modalidad']."</td>
			<td  id='td_datos_6' style='display: none;'>".$gast['tipo']."</td>
			<td  id='td_datos_8' style='display: none;'>".$gast['nroboleta']."</td>
			<td  id='td_datos_9' style='display: none;'>".$gast['banco']."</td>
			<td  id='td_datos_10' style='display: none;'>".$gast['nrocuenta']."</td>
			<td  id='td_datos_11' style='display: none;'>".$gast['arreglo']."</td>
			<td  id='td_datos_21' style='display: none;'>".$gast['usuarionombre']."</td>
			<td  id='' style='display: none;'>".$gast['nombrelocal']."</td>
			<td  id='td_datos_7' style='display:none;'>".$gast['cod_local']."</td>
			<td  id='td_datos_12' style='display:none;'>".$gast['url1']."</td>
			<td  id='td_datos_25' style='display:none;'>".$gast['url_documento_firmado']."</td>
			<td  id='td_datos_13' style='display:none;'>".$gast['descripcion']."</td>
			<td  id='td_datos_14' style='display:none;'>".$gast['motivo']."</td>
			<td  id='td_datos_15' style='display:none;'>".$gast['cod_interConsultaFK']."</td>
			<td  id='td_datos_17' style='display:none;'>".$gast['cod_usuario_autoriz']."</td>
			<td  id='td_datos_18' style='display:none;'>".$gast['usuario_autoriz_nombre']."</td>
			<td  id='td_datos_19' style='display:none;'>".$gast['fecha_autoriz']."</td>
			<td  id='td_datos_20' style='display:none;'>".$gast['cod_motivoIngresoEgresoFK']."</td>
			<td  id='td_datos_22' style='display:none;'>".$gast['cod_proyecto_gastoFK']."</td>
		</tr>";
	}

	echo json_encode(array("1" => "exito", "2" => $pagina, "3" => (isset($gastos[0]) ? $gastos[0] : null), "4" => (isset($gastos[0]) ? $gastos[0]['descripcion'] : null), "5" => number_format($total_pendiente, 0, ',', '.'), "6" => count($gastos), "7" => ($tieneSerieEditable ? 1 : 0)));
	exit;
}

}


function obtenerGastosAsociados($idgastos, $codLocalDistribucion = '') {
	$result= array();
	$usarDistribucionLocal= is_numeric($codLocalDistribucion)
		&& in_array((int)$codLocalDistribucion, gastoDistribucionLocalesGraficos(), true);
	$codLocalFiltro= $usarDistribucionLocal ? (int)$codLocalDistribucion : '';
	// Obtenemos el registro base aunque este inactivo, porque desde el se resuelve el proyecto/padre.
	// La identidad base/padre se resuelve siempre por ID. El local analitico se
	// aplica recien al listar la serie; un padre valido puede tener otro origen.
	$result = buscarGasto('','','','','','','','','false','','','','','', $idgastos, 'DESC', '', false);
	if (count($result) < 1) {
		error_log('obtenerGastosAsociados: gasto base no encontrado; id='.(int)$idgastos);
		return array();
	}
	$regGasto= $result[0];

	// Se verifica si es cuota y se obtiene el gasto padre
	if ($regGasto['cod_gasto_padre']) {
		$result= buscarGasto('','','','','','','','','false','','','','','', $regGasto['cod_gasto_padre'], 'DESC', '', false);
		if (count($result) < 1) {
			error_log('obtenerGastosAsociados: gasto padre no encontrado; id='.(int)$idgastos);
			return array();
		}
		$regGasto= $result[0];
	}
	$resultadoBaseVisible= $result;
	if ($codLocalFiltro !== '') {
		$resultadoBaseVisible= buscarGasto('','','','',$codLocalFiltro,'','','','false','','','','','', $regGasto['idgastos'], 'DESC', '', $usarDistribucionLocal);
	}

	// Se verifica si tiene un proyecto asociado
	if ($regGasto['cod_proyecto_gastoFK']) {
		$gastosProyecto= buscarGasto('','','','',$codLocalFiltro,'','','','true','','','','','', '', 'ASC', $regGasto['cod_proyecto_gastoFK'], $usarDistribucionLocal);
		return (count($gastosProyecto) > 0 ? $gastosProyecto : $resultadoBaseVisible);
	}

	// Se evalua si existen gastos asociados
	$gastos_asociados= buscarGasto('','','','',$codLocalFiltro,'','','','true','','','','',$regGasto['idgastos'], '','ASC', '', $usarDistribucionLocal);

	return array_merge($resultadoBaseVisible, $gastos_asociados);
}
function darBajaCuotaProgramada($idgastos, $alcance, $codUsuario) {
	$idgastos= intval($idgastos);
	$alcance= in_array($alcance, array('serie', 'hilo')) ? $alcance : 'cuota';
	if ($idgastos <= 0) {
		echo json_encode(array('1'=>'error', '2'=>'Cuota no valida.'));
		exit;
	}
	if (controldeaccesoacasas($codUsuario, 'EDITARLISTADOEGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1'=>'NI', '2'=>'No tiene permiso para dar de baja cuotas programadas.'));
		exit;
	}
	$mysqli= conectar_al_servidor();
	$mysqli->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
	if (!$mysqli->begin_transaction()) {
		echo json_encode(array('1'=>'error', '2'=>'No se pudo iniciar la baja segura de cuotas.'));
		exit;
	}
	// Primero se resuelve el alcance sin bloquear gastos; si existen conciliaciones,
	// se bloquean movimiento/vinculo Ueno antes de tomar las filas de la serie.
	$stmt= $mysqli->prepare("SELECT idgastos,fecha,estado,modalidad,cod_gasto_padre,cod_interConsultaFK,cod_local FROM gastos WHERE idgastos=? LIMIT 1");
	$stmt->bind_param('i', $idgastos);
	$stmt->execute();
	$cuota= $stmt->get_result()->fetch_assoc();
	$stmt->close();
	$estadoActual= $cuota ? strtolower(trim((string)$cuota['estado'])) : '';
	$esCuotaProgramada= $cuota && strtolower(trim((string)$cuota['modalidad'])) == 'credito';
	$estadoPermiteBajaIndividual= ($estadoActual == 'pendiente' || $estadoActual == 'solicitado');
	if (!$esCuotaProgramada || ($alcance != 'hilo' && !$estadoPermiteBajaIndividual)) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'Solo se pueden dar de baja cuotas programadas pendientes.'));
		exit;
	}
	$ids= array($idgastos);
	$localesObjetivo= array((int)$cuota['cod_local']);
	if ($alcance == 'hilo') {
		$codInterConsulta= intval($cuota['cod_interConsultaFK']);
		if ($codInterConsulta <= 0) {
			$mysqli->rollback();
			echo json_encode(array('1'=>'error', '2'=>'La cuota no esta vinculada a un hilo.'));
			exit;
		}
		$stmt= $mysqli->prepare("SELECT idgastos,cod_local FROM gastos WHERE cod_interConsultaFK=? AND modalidad='credito' AND LOWER(TRIM(estado)) IN ('pendiente','solicitado') ORDER BY idgastos");
		$stmt->bind_param('i', $codInterConsulta);
		$stmt->execute();
		$result= $stmt->get_result();
		$ids= array();
		$localesObjetivo= array();
		while ($fila= $result->fetch_assoc()) { $ids[]= intval($fila['idgastos']); $localesObjetivo[]= intval($fila['cod_local']); }
		$stmt->close();
	} else if ($alcance == 'serie') {
		$idSerie= intval($cuota['cod_gasto_padre']);
		if ($idSerie <= 0) { $idSerie= $idgastos; }
		$fechaDesde= $cuota['fecha'];
		$stmt= $mysqli->prepare("SELECT idgastos,cod_local FROM gastos WHERE (idgastos=? OR cod_gasto_padre=?) AND fecha>=? AND LOWER(TRIM(estado)) IN ('pendiente','solicitado') ORDER BY idgastos");
		$stmt->bind_param('iis', $idSerie, $idSerie, $fechaDesde);
		$stmt->execute();
		$result= $stmt->get_result();
		$ids= array();
		$localesObjetivo= array();
		while ($fila= $result->fetch_assoc()) { $ids[]= intval($fila['idgastos']); $localesObjetivo[]= intval($fila['cod_local']); }
		$stmt->close();
	}
	if (count($ids) < 1) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'No hay cuotas pendientes para dar de baja.'));
		exit;
	}
	foreach (array_unique($localesObjetivo) as $codLocalObjetivo) {
		if (!usuarioPuedeGestionarLocalGasto($codUsuario, $codLocalObjetivo)) {
			$mysqli->rollback();
			echo json_encode(array('1'=>'NI', '2'=>'No administra el local de una de las cuotas seleccionadas.'));
			exit;
		}
	}
	$ids= gastoDistribucionNormalizarIdsExcluir($ids);
	try {
		$vinculosUeno= gastoUenoBloquearVinculosActivos($mysqli, $ids);
		if (count($vinculosUeno['ids_gastos']) > 0) {
			throw new Exception('Una o mas cuotas tienen conciliacion Ueno activa. Revierta primero la conciliacion bancaria.');
		}
	} catch (Exception $e) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>$e->getMessage()));
		exit;
	}
	$listaIds= implode(',', $ids);
	$resultadoBloqueo= $mysqli->query("SELECT idgastos,cod_local FROM gastos WHERE idgastos IN ($listaIds) ORDER BY idgastos FOR UPDATE");
	if (!$resultadoBloqueo || $resultadoBloqueo->num_rows != count($ids)) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'La serie cambio mientras se preparaba la baja. Actualice la pantalla.'));
		exit;
	}
	$localesBloqueados= array();
	while ($filaBloqueada= $resultadoBloqueo->fetch_assoc()) {
		$localesBloqueados[(int)$filaBloqueada['cod_local']]= true;
	}
	foreach (array_keys($localesBloqueados) as $codLocalBloqueado) {
		if (!usuarioPuedeGestionarLocalGasto($codUsuario, $codLocalBloqueado)) {
			$mysqli->rollback();
			echo json_encode(array('1'=>'NI', '2'=>'Una cuota cambio de local mientras se preparaba la baja. Actualice la pantalla.'));
			exit;
		}
	}
	if (!gastoConceptosActivosParaIds($mysqli, $ids, true)) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'Una o mas cuotas pertenecen a un concepto historico de solo lectura.'));
		exit;
	}
	// La lectura anterior era solo un precontrol. Se vuelve a comprobar bajo
	// bloqueo, despues de fijar las cuotas, para cerrar la carrera con una
	// conciliacion manual creada mientras se resolvia el alcance.
	try {
		$vinculosUenoActuales= gastoUenoConsultarIdsActivos($mysqli, $ids, true);
		if (count($vinculosUenoActuales) > 0) {
			throw new Exception('Una o mas cuotas tienen conciliacion Ueno activa. Revierta primero la conciliacion bancaria.');
		}
	} catch (Exception $e) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>$e->getMessage()));
		exit;
	}
	$usuarioEditor= intval($codUsuario);
	$ok= $mysqli->query("UPDATE gastos SET estado='Baja', cod_usuarioFK_edit=".$usuarioEditor." WHERE idgastos IN (".$listaIds.") AND estado IN ('pendiente','solicitado')");
	if (!$ok) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'No se pudo actualizar las cuotas.'));
		exit;
	}
	if (!$mysqli->query("UPDATE mensaje m INNER JOIN gastos g ON g.cod_mensajeFK=m.cod_mensaje SET m.estado='inactivo' WHERE g.idgastos IN (".$listaIds.")")) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'No se pudieron desactivar los recordatorios de las cuotas.'));
		exit;
	}
	if (!$mysqli->commit()) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'No se pudo confirmar la baja de cuotas.'));
		exit;
	}
	echo json_encode(array('1'=>'exito', '2'=>count($ids), '3'=>$alcance));
	exit;
}

function buscarProximosPagos($fecha_inicio,$fecha_fin,$local,$descripcion,$estadoFiltroPagoprogrtamado)
{
    date_default_timezone_set('America/Asuncion');

	$fechahoy = date("Y-m-d");

    $mysqli = conectar_al_servidor();
	
	$fecha_inicio= trim((string)$fecha_inicio);
	$fecha_fin= trim((string)$fecha_fin);
	$localTexto= trim((string)$local);
	$descripcion= trim((string)$descripcion);
	$estadoFiltroPagoprogrtamado= trim((string)$estadoFiltroPagoprogrtamado);
	if (!gastoFechaFiltroIsoValida($fecha_inicio) || !gastoFechaFiltroIsoValida($fecha_fin)
		|| ($fecha_inicio === '') !== ($fecha_fin === '')
		|| ($fecha_inicio !== '' && $fecha_inicio > $fecha_fin)
		|| ($localTexto !== '' && (!preg_match('/^[0-9]+$/', $localTexto) || (int)$localTexto <= 0))
		|| strlen($descripcion) > 200
		|| !in_array($estadoFiltroPagoprogrtamado, array('Todo','Pendiente'), true)) {
		mysqli_close($mysqli);
		echo json_encode(array('1'=>'error', '2'=>'Los filtros de pagos programados no son validos.'));
		exit;
	}
	$usarFecha= ($fecha_inicio !== '' && $fecha_fin !== '') ? 1 : 0;
	$localFiltro= $localTexto === '' ? 0 : (int)$localTexto;
	$soloPendientes= $estadoFiltroPagoprogrtamado === 'Pendiente' ? 1 : 0;
	 

    // ✅ NO TOCO TU SQL
    $sql = "
    SELECT 
		g.idgastos,
        g.monto,
        g.fecha,
        g.motivo AS detalle,
        g.estado,
        g.modalidad,
        asunto AS titulo,
		(SELECT Nombre FROM local WHERE cod_local = g.cod_local) AS Nombrelocal
    FROM gastos g
    INNER JOIN interconsulta ic 
        ON g.cod_interConsultaFK = ic.cod_interConsulta
	WHERE g.monto!=''
		AND (?=0 OR g.fecha BETWEEN ? AND ?)
		AND (?=0 OR g.cod_local=?)
		AND (?='' OR ic.asunto LIKE CONCAT('%', ?, '%'))
		AND (?=0 OR g.estado IN ('pendiente','solicitado'))
    ORDER BY g.fecha ASC ";

    $stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		mysqli_close($mysqli);
		echo json_encode(array('1'=>'error', '2'=>'No se pudo preparar la consulta de pagos programados.'));
		exit;
	}
	$stmt->bind_param('issiissi', $usarFecha, $fecha_inicio, $fecha_fin, $localFiltro, $localFiltro, $descripcion, $descripcion, $soloPendientes);
    if (!$stmt->execute()) {
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array('1'=>'error', '2'=>'No se pudo consultar los pagos programados.'));
        exit;
    }

    $result = $stmt->get_result();
    $valor = mysqli_num_rows($result);

    $pagina = "";

    // =========================
    // CONTENEDOR
    // =========================
    $pagina .= " <div class='hilosWrap'> ";

    if ($valor <= 0) {
        $pagina .= "
          <div class='section today'>
            <div class='section-head'>
              <div>
                <h3 class='section-title'>Hilos - InterConsultas</h3>
                <p class='section-sub'>Sin registros</p>
              </div>
              <div class='section-meta'>0</div>
            </div>
            <div class='grid'>
              <div class='card empty'><div class='card-body'><b>No hay datos para mostrar.</b></div></div>
            </div>
          </div>
        </div>
        ";
        $informacion = array('1' => 'exito', '2' => $pagina);
        echo json_encode($informacion);
        exit;
    }

    // =========================
    // Separar registros
    // =========================
    $hoy = new DateTime("today");
    $pasados = array();
    $hoyList = array();
    $proximos = array();
    $totalHoy = 0;
    while ($row = mysqli_fetch_assoc($result)) {

        // 🔤 Encoding como tu ejemplo
        $monto = mb_convert_encoding((string)$row['monto'], 'UTF-8', 'ISO-8859-1');
        $fecha = mb_convert_encoding((string)$row['fecha'], 'UTF-8', 'ISO-8859-1');
        $detalle = mb_convert_encoding((string)$row['detalle'], 'UTF-8', 'ISO-8859-1');
        $estado = mb_convert_encoding((string)$row['estado'], 'UTF-8', 'ISO-8859-1');
        $modalidad = mb_convert_encoding((string)$row['modalidad'], 'UTF-8', 'ISO-8859-1');
        $titulo = mb_convert_encoding((string)$row['titulo'], 'UTF-8', 'ISO-8859-1');
        $Nombrelocal = mb_convert_encoding((string)$row['Nombrelocal'], 'UTF-8', 'ISO-8859-1');
        $idgastos = mb_convert_encoding((string)$row['idgastos'], 'UTF-8', 'ISO-8859-1');
		

        // normalizar fecha día
        $f = new DateTime($fecha);
        $fDia = new DateTime($f->format("Y-m-d"));

        // total hoy
        $montoNum = (int)preg_replace('/[^\d]/', '', (string)$monto);

        $item = array(
            'monto' => $monto,
            'fecha' => $fecha,
            'detalle' => $detalle,
            'estado' => $estado,
            'modalidad' => $modalidad,
            'titulo' => $titulo,
            'Nombrelocal' => $Nombrelocal,
            'idgastos' => $idgastos
        );

        if ($fDia < $hoy) {
            $pasados[] = $item;
        } elseif ($fDia == $hoy) {
            $hoyList[] = $item;
            $totalHoy += $montoNum;
        } else {
            $proximos[] = $item;
        }
    }

    // =========================
    // helpers internos
    // =========================
    $gs = function($n){
        $num = (int)preg_replace('/[^\d]/', '', (string)$n);
        return "Gs. " . number_format($num, 0, ",", ".");
    };

    $fmtFecha = function($f){
        return date("d-m-Y", strtotime($f));
    };

    // ✅ Agrupar por día (sin tocar SQL)
    $groupByDay = function($items){
        $out = array();
        foreach ($items as $r) {
            $key = date("Y-m-d", strtotime($r['fecha']));
            if (!isset($out[$key])) $out[$key] = array();
            $out[$key][] = $r;
        }
        ksort($out); // ordena por fecha asc
        return $out;
    };

    $groupTitle = function($ymd){
        return date("d-m-Y", strtotime($ymd));
    };

    // Render cards
    $renderCard = function($r, $ponerBadgeVencido = false) use ($gs, $fmtFecha) {

        $titulo = htmlspecialchars($r['titulo'], ENT_QUOTES, 'UTF-8');
        $detalle = htmlspecialchars($r['detalle'], ENT_QUOTES, 'UTF-8');
        $estado = htmlspecialchars($r['estado'], ENT_QUOTES, 'UTF-8');
        $modalidad = htmlspecialchars($r['modalidad'], ENT_QUOTES, 'UTF-8');
        $local = htmlspecialchars($r['Nombrelocal'], ENT_QUOTES, 'UTF-8');
        $fecha = htmlspecialchars($fmtFecha($r['fecha']), ENT_QUOTES, 'UTF-8');
        $monto = htmlspecialchars($gs($r['monto']), ENT_QUOTES, 'UTF-8');
        $idgastos = htmlspecialchars($r['idgastos'], ENT_QUOTES, 'UTF-8');

        // $badge = $ponerBadgeVencido ? "<span class='badge'>Vencido</span>" : "";
        $badge = "";
		
		$claseEstado = "";

		$estadoLower = mb_strtolower($estado,'UTF-8');

		if($estadoLower == "rechazado"){
			$claseEstado = "card-rechazado";
		}

		if($estadoLower == "pendiente"){
			$claseEstado = "card-pendiente";
		}

		if($estadoLower == "solicitado"){
			$claseEstado = "card-solicitado";
		}
		if($estadoLower == "activo"){
			$claseEstado = "card-activo";
		}

        return "
          <article class='card'>
            <div class='card-body {$claseEstado}'  >
              <div class='card-top'>
                <div>
                  <p class='card-title'>{$titulo} - <span>{$modalidad}</span></p>
                </div>
              </div>

              <div class='lines'>
                <div class='line' style='display: none;'><b>IdGastos:</b> {$idgastos}</div>
                <div class='line'><b>Fecha:</b> {$fecha}</div>
                <div class='line'><b>Monto:</b> {$monto}</div>
                <div class='line'><b>Detalle:</b> {$detalle}</div>
                <div class='line'><b>Estado:</b> {$estado}</div>
                <div class='line'><b>Local:</b> {$local}</div>
                <div class='line'><b>Modalidad:</b> {$modalidad}</div>
              </div>
            </div>
            {$badge}
          </article>
        ";
    };

    // =========================
    // CSS (si ya lo tenés en otro lado, podés borrar el <style>)
    // =========================
    $pagina .= " ";

    // =========================
    // ARMAR HTML FINAL (AGRUPADO POR FECHA)
    // =========================

    // PASADOS
    $pagina .= "
      <section class='section past'>
        <div class='section-head'>
          <div>
            <h3 class='section-title'>Vencimientos Pasados</h3>
            <p class='section-sub'>Elementos vencidos (requieren acción)</p>
          </div>
          <div class='section-meta'>".count($pasados)." vencidos</div>
        </div>
        <div class='grid'>
    ";

    if (count($pasados) == 0) {
        $pagina .= "<div class='card empty'><div class='card-body'><b>No hay vencimientos pasados.</b></div></div>";
    } else {
        $pasadosG = $groupByDay($pasados);
        foreach ($pasadosG as $dia => $lista) {
            $pagina .= "<div class='group-date'>".htmlspecialchars($groupTitle($dia), ENT_QUOTES, 'UTF-8')."</div>";
            foreach ($lista as $r) {
                $pagina .= $renderCard($r, true);
            }
        }
    }
    $pagina .= "</div></section>";

    // HOY
    $pagina .= "
      <section class='section today'>
        <div class='section-head'>
          <div>
            <h3 class='section-title'>Vencimientos de HOY</h3>
            <p class='section-sub'>Vencimientos de Hoy: <b>".count($hoyList)."</b> | Total Proyectado: <b>".$gs($totalHoy)."</b></p>
          </div>
          <div class='section-meta'>Hoy</div>
        </div>
        <div class='grid'>
    ";

    if (count($hoyList) == 0) {
        $pagina .= "<div class='card empty'><div class='card-body'><b>No hay vencimientos para hoy.</b></div></div>";
    } else {
        $hoyG = $groupByDay($hoyList);
        foreach ($hoyG as $dia => $lista) {
            $pagina .= "<div class='group-date'>".htmlspecialchars($groupTitle($dia), ENT_QUOTES, 'UTF-8')."</div>";
            foreach ($lista as $r) {
                $pagina .= $renderCard($r, false);
            }
        }
    }
    $pagina .= "</div></section>";

    // PROXIMOS
    $pagina .= "
      <section class='section next'>
        <div class='section-head'>
          <div>
            <h3 class='section-title'>Próximos Vencimientos</h3>
            <p class='section-sub'>Fechas futuras</p>
          </div>
          <div class='section-meta'>".count($proximos)." futuros</div>
        </div>
        <div class='grid'>
    ";

    if (count($proximos) == 0) {
        $pagina .= "<div class='card empty'><div class='card-body'><b>No hay vencimientos futuros.</b></div></div>";
    } else {
        $proxG = $groupByDay($proximos);
        foreach ($proxG as $dia => $lista) {
            $pagina .= "<div class='group-date'>".htmlspecialchars($groupTitle($dia), ENT_QUOTES, 'UTF-8')."</div>";
            foreach ($lista as $r) {
                $pagina .= $renderCard($r, false);
            }
        }
    }
    $pagina .= "</div></section>";

    $pagina .= "</div>"; // .hilosWrap

    // =========================
    // RESPUESTA JSON como tu estilo
    // =========================
    $informacion = array("1" => "exito", "2" => $pagina);
    echo json_encode($informacion);
    exit;
}







function buscarResumenGastosMotivo($fecha_inicio, $fecha_fin) {
	$fecha_inicio= trim((string)$fecha_inicio);
	$fecha_fin= trim((string)$fecha_fin);
	$mysqli=conectar_al_servidor();
	if (!gastoFechaFiltroIsoValida($fecha_inicio) || !gastoFechaFiltroIsoValida($fecha_fin)) {
		mysqli_close($mysqli);
		echo json_encode(array('1'=>'error', '2'=>'El rango de fechas no es valido.'));
		exit;
	}
	$usarFechaInicio= $fecha_inicio !== '' ? 1 : 0;
	$usarFechaFin= $fecha_fin !== '' ? 1 : 0;
	$sql= "SELECT 
		(SELECT sum(monto) FROM gastos where cod_motivoIngresoEgresoFK = m.cod_motivo_ingreso_egreso
			AND LOWER(TRIM(IFNULL(tipo,'')))!='deposito'
			AND (?=0 OR fecha >= ?)
			AND (?=0 OR fecha <= ?)) as monto,
		m.cod_motivo_ingreso_egreso, m.descripcion 
	 FROM motivos_ingreso_egreso m where estado='activo'";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		mysqli_close($mysqli);
		echo json_encode(array('1'=>'error', '2'=>'No se pudo preparar el resumen financiero.'));
		exit;
	}
	$stmt->bind_param('isis', $usarFechaInicio, $fecha_inicio, $usarFechaFin, $fecha_fin);
	if (!$stmt->execute()) {
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array('1'=>'error', '2'=>'No se pudo consultar el resumen financiero.'));
		exit;
	}
	$pagina= "";
	$monto_total= 0;
	$registros= array();
	$result = $stmt->get_result();
	$valor= mysqli_num_rows($result);
	$nroRegistro= $valor;
	$styleName="tableRegistroSearch";
 
 	if ($valor>0) {
	  while ($valor= mysqli_fetch_assoc($result)) {
		  $styleName=CargarStyleTable($styleName);
		  $cod_motivo_ingreso_egreso=mb_convert_encoding((string)($valor['cod_motivo_ingreso_egreso']), 'UTF-8', 'ISO-8859-1');
		  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');

		  $pagina .= '<table class="'.$styleName.'" border="1" cellspacing="1" cellpadding="5"><tr>
		 	<td style="width: 10%;">'.$cod_motivo_ingreso_egreso.'</td> 
		 	<td style="width: 65%;">'.$descripcion.'</td> 
		 	<td style="width: 25%;">'.number_format(intval($monto), 0, ',', '.').'</td> 
		  </tr></table>';

		  $monto_total += intval($monto);
		  $registros[] = array(
			'cod_motivo_ingreso_egreso' => $cod_motivo_ingreso_egreso,
			'descripcion' => $descripcion,
			'monto' => $monto,
		  );
	  }
	}

	mysqli_close($mysqli);
	$informacion =array("1" => "exito", "2" => $pagina, "3" => $monto_total, "4" => $nroRegistro, "5" => $registros);
	echo json_encode($informacion);	
	exit;
}

function combinarMotivoIngresoEgreso($cod_motivoIngresoEgreso, $cod_motivoIngresoEgreso_dest, $cod_usuarioFK) {
	$fechaActual= new DateTime();
	$fechaActual=date_format($fechaActual,"Y-m-d H:i:s");

	$mysqli=conectar_al_servidor();
	if (esCodigoConceptoDepositoCentral($mysqli, $cod_motivoIngresoEgreso)
		|| esCodigoConceptoDepositoCentral($mysqli, $cod_motivoIngresoEgreso_dest)) {
		mysqli_close($mysqli);
		$informacion= array("1" => "error", "2" => "El concepto de depositos a central es reservado y no puede combinarse con otro concepto.");
		echo json_encode($informacion);
		exit;
	}

	// Se actualiza todos los registros de gastos con el motivo anterior
	$sql= "UPDATE gastos SET cod_motivoIngresoEgresoFK= ? WHERE cod_motivoIngresoEgresoFK = ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('ii',$cod_motivoIngresoEgreso_dest,$cod_motivoIngresoEgreso);
	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	// SE cambia a inactivo el motivo original
	$sql= "UPDATE motivos_ingreso_egreso SET estado='inactivo' WHERE cod_motivo_ingreso_egreso = ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('i',$cod_motivoIngresoEgreso);
	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	mysqli_close($mysqli);
	$informacion =array("1" => "exito", "2" => $cod_motivoIngresoEgreso_dest);
	echo json_encode($informacion);	
	exit;
}

function aprobarMovimiento($idgastos, $cod_usuarioFK, $decision) {
	$idgastos= (int)$idgastos;
	$cod_usuarioFK= (int)$cod_usuarioFK;
	if ($idgastos <= 0 || controldeaccesoacasas($cod_usuarioFK, 'AUTORIZAREGRESOINGRESO', " u.accion='SI' ") != 1) {
		echo json_encode(array('1'=>'NI', '2'=>'No tiene permiso para autorizar este movimiento.'));
		exit;
	}
	if ($decision !== 'true' && $decision !== 'false') {
		echo json_encode(array('1'=>'error', '2'=>'La decision recibida no es valida.'));
		exit;
	}
	$mysqli= conectar_al_servidor();
	$stmt= $mysqli->prepare('SELECT * FROM gastos WHERE idgastos=? LIMIT 1');
	$stmt->bind_param('i', $idgastos);
	$stmt->execute();
	$registroGasto= $stmt->get_result()->fetch_assoc();
	$stmt->close();
	if (!$registroGasto) {
		$mysqli->close();
		echo json_encode(array('1'=>'error', '2'=>'El movimiento no existe.'));
		exit;
	}
	if (!usuarioPuedeGestionarLocalGasto($cod_usuarioFK, $registroGasto['cod_local'])) {
		$mysqli->close();
		echo json_encode(array('1'=>'NI', '2'=>'No administra el local de origen del movimiento.'));
		exit;
	}
	if (esTipoDepositoCentral($registroGasto['tipo'])) {
		$mysqli->close();
		echo json_encode(array('1'=>'error', '2'=>'Los depositos a central no utilizan el flujo de aprobacion o rechazo.'));
		exit;
	}
	$cod_aperturaFK= $registroGasto['codApertura'];
	$cod_cajaFK= $registroGasto['codCaja'];

	// Se verifica si la caja sigue abierta, en caso contrario se actualiza basandose en el usuario creador
	$result_caja = controldecaja($registroGasto['codCaja'],$registroGasto['cod_local'],$registroGasto['cod_usuario']);
	if ($result_caja["2"] == "0" || $result_caja["3"] != $registroGasto['codApertura']) {
		$result_caja = controldecaja('',$registroGasto['cod_local'],$registroGasto['cod_usuario']);
		$cod_aperturaFK = $result_caja["3"];
		$cod_cajaFK= $result_caja["4"];
	}

	$fechaActual= new DateTime();
	$fechaActual= $fechaActual->format('Y-m-d H:i:s');
	$decision= ($decision == 'true' ? 'Activo' : 'Rechazado');
	$mysqli->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
	if (!$mysqli->begin_transaction()) {
		$mysqli->close();
		echo json_encode(array('1'=>'error', '2'=>'No se pudo iniciar la autorizacion segura.'));
		exit;
	}
	try {
		$vinculosUeno= gastoUenoBloquearVinculosActivos($mysqli, array($idgastos));
		if (count($vinculosUeno['ids_gastos']) > 0) {
			throw new Exception('El movimiento tiene una conciliacion Ueno activa. Revierta primero la conciliacion bancaria.');
		}
		$stmtBloqueo= $mysqli->prepare('SELECT * FROM gastos WHERE idgastos=? LIMIT 1 FOR UPDATE');
		$stmtBloqueo->bind_param('i', $idgastos);
		$stmtBloqueo->execute();
		$registroBloqueado= $stmtBloqueo->get_result()->fetch_assoc();
		$stmtBloqueo->close();
		if (!$registroBloqueado || !in_array(strtolower(trim((string)$registroBloqueado['estado'])), array('pendiente','solicitado'), true)) {
			throw new Exception('El movimiento ya no esta pendiente de autorizacion.');
		}
		if (!gastoConceptosActivosParaIds($mysqli, array($idgastos), true)) {
			throw new Exception('El concepto financiero es historico y este movimiento es de solo lectura.');
		}
		if (!usuarioPuedeGestionarLocalGasto($cod_usuarioFK, $registroBloqueado['cod_local'])) {
			throw new Exception('No administra el local de origen del movimiento.');
		}
		// Current read posterior al bloqueo del gasto: una asignacion parcial
		// tambien impide aprobar o rechazar hasta que sea revertida.
		$vinculosUenoActuales= gastoUenoConsultarIdsActivos($mysqli, array($idgastos), true);
		if (count($vinculosUenoActuales) > 0) {
			throw new Exception('El movimiento tiene una conciliacion Ueno activa. Revierta primero la conciliacion bancaria.');
		}
	} catch (Exception $e) {
		$mysqli->rollback();
		$mysqli->close();
		echo json_encode(array('1'=>'error', '2'=>$e->getMessage()));
		exit;
	}
	$sql= "UPDATE gastos SET cod_usuario_autoriz=?,fecha_autoriz=?,codApertura=?,codCaja=?,estado=? WHERE idgastos=?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('isiisi',$cod_usuarioFK,$fechaActual,$cod_aperturaFK,$cod_cajaFK,$decision,$idgastos);

	if (!$stmt->execute()) {
		$mysqli->rollback();
		$mensajeError= $stmt->error;
		$stmt->close();
		$mysqli->close();
		echo json_encode(array('1'=>'error', '2'=>'No se pudo actualizar la autorizacion: '.$mensajeError));
		exit;
	}
	$stmt->close();
	if (!$mysqli->commit()) {
		$mysqli->rollback();
		$mysqli->close();
		echo json_encode(array('1'=>'error', '2'=>'No se pudo confirmar la autorizacion.'));
		exit;
	}
	// El aviso no decide el resultado financiero. Se intenta despues del commit
	// para no invertir el orden de locks Hilo -> gasto de las altas/ediciones.
	if (!empty($registroGasto['cod_interConsultaFK'])) {
		try {
			$mensaje= " @{".$cod_usuarioFK."} decidio ". ($decision == 'Activo' ? ' aprobar ' : ' rechazar ') . " el movimiento con descripcion ".$registroGasto['motivo'].".";
			$mensaje= mb_convert_encoding($mensaje, 'ISO-8859-1', 'UTF-8');
			gastoMensajeCrearTransaccional($mysqli, $mensaje, $fechaActual, $registroGasto['cod_interConsultaFK'], $cod_usuarioFK);
		} catch (Exception $e) {
			error_log('No se pudo registrar el aviso de autorizacion del gasto '.$idgastos.': '.$e->getMessage());
		}
	}
	$mysqli->close();
	$informacion =array("1" => "exito", "2" => $idgastos);
	echo json_encode($informacion);	
	exit;
}

function guardarArchivoGasto($idgastos, $foto, $ext, $columna, $prefijo= '') {
	if ($columna != 'url1' && $columna != 'url_documento_firmado') {
		return false;
	}
	if (empty($foto) && empty($ext)) {
		return true;
	}
	$archivoPreparado= centroFacturaPrepararArchivo(array(
		'data' => (string)$foto,
		'nombre' => 'documento.'.strtolower(trim((string)$ext))
	));
	if (empty($archivoPreparado['ok'])) {
		return false;
	}

	$ruta= NULL;
	$foto= $archivoPreparado['binario'];
	$ext= $archivoPreparado['extension'];
	$donde = "../fotos/fotosGastos/";
	$id_foto = $prefijo.$idgastos;
	try {
		$id_f = subir_imagen_base64($donde, $foto, $id_foto, $ext);
	} catch (Exception $e) {
		return false;
	}
	$ruta = "/GoodVentaAsisCap/fotos/fotosGastos/" . $prefijo . $idgastos . $id_f . "." . $ext;
	$rutaAbsoluta= dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosGastos'.DIRECTORY_SEPARATOR.$prefijo.$idgastos.$id_f.'.'.$ext;
	clearstatcache(true, $rutaAbsoluta);
	if (!is_file($rutaAbsoluta) || filesize($rutaAbsoluta) !== strlen($foto)) {
		if (is_file($rutaAbsoluta)) { @unlink($rutaAbsoluta); }
		return false;
	}
	
	$mysqli=conectar_al_servidor();
	$consulta="Update gastos set $columna=? where idgastos=? ";	
	
	$stmt = $mysqli->prepare($consulta);
	if (!$stmt) {
		$mysqli->close();
		if (is_file($rutaAbsoluta)) { @unlink($rutaAbsoluta); }
		return false;
	}
	$stmt->bind_param('si', $ruta, $idgastos);
	if ( ! $stmt->execute()) {
		$stmt->close();
		$mysqli->close();
		if (is_file($rutaAbsoluta)) { @unlink($rutaAbsoluta); }
		return false;
	}
	$stmt->close();
	$mysqli->close();
	return true;
}

function subirImagenGasto($idgastos, $foto, $ext) {
	return guardarArchivoGasto($idgastos, $foto, $ext, 'url1');
}

function subirDocumentoFirmadoGasto($idgastos, $foto, $ext) {
	return guardarArchivoGasto($idgastos, $foto, $ext, 'url_documento_firmado', 'firmado_');
}

function sumarMesesRespetandoDia($fechaBase, $mesesASumar, $diaObjetivo) {
	$anioBase = (int)$fechaBase->format('Y');
	$mesBase = (int)$fechaBase->format('n');
	$mesTotal = $mesBase + $mesesASumar;

	$nuevoAnio = $anioBase + floor(($mesTotal - 1) / 12);
	$nuevoMes = (($mesTotal - 1) % 12) + 1;
	$ultimoDiaMes = cal_days_in_month(CAL_GREGORIAN, $nuevoMes, $nuevoAnio);
	$diaFinal = min($diaObjetivo, $ultimoDiaMes);

	return DateTime::createFromFormat('Y-n-j', $nuevoAnio . '-' . $nuevoMes . '-' . $diaFinal);
}

function calcularFechaQuincenalPorCortes($fechaBase, $indice) {
	if ($indice <= 0) {
		return clone $fechaBase;
	}

	$anio = (int)$fechaBase->format('Y');
	$mes = (int)$fechaBase->format('n');
	$dia = (int)$fechaBase->format('j');
	$ultimoDiaMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

	if ($dia < 15) {
		$fechaCuota = DateTime::createFromFormat('Y-n-j', $anio . '-' . $mes . '-15');
	} elseif ($dia < $ultimoDiaMes) {
		$fechaCuota = DateTime::createFromFormat('Y-n-j', $anio . '-' . $mes . '-' . $ultimoDiaMes);
	} else {
		$mes++;
		if ($mes > 12) {
			$mes = 1;
			$anio++;
		}
		$fechaCuota = DateTime::createFromFormat('Y-n-j', $anio . '-' . $mes . '-15');
	}

	for ($paso = 1; $paso < $indice; $paso++) {
		$anioActual = (int)$fechaCuota->format('Y');
		$mesActual = (int)$fechaCuota->format('n');
		$diaActual = (int)$fechaCuota->format('j');

		if ($diaActual === 15) {
			$ultimoDiaMesActual = cal_days_in_month(CAL_GREGORIAN, $mesActual, $anioActual);
			$fechaCuota = DateTime::createFromFormat('Y-n-j', $anioActual . '-' . $mesActual . '-' . $ultimoDiaMesActual);
		} else {
			$mesSiguiente = $mesActual + 1;
			$anioSiguiente = $anioActual;
			if ($mesSiguiente > 12) {
				$mesSiguiente = 1;
				$anioSiguiente++;
			}
			$fechaCuota = DateTime::createFromFormat('Y-n-j', $anioSiguiente . '-' . $mesSiguiente . '-15');
		}
	}

	return $fechaCuota;
}

function calcularFechaCuotaRecurrente($fechaBase, $periodicidad, $indice) {
	$fechaCuota = clone $fechaBase;
	$diaObjetivo = (int)$fechaBase->format('j');

	switch ($periodicidad) {
		case 'semanal':
			$fechaCuota->modify('+' . (7 * $indice) . ' day');
			return $fechaCuota;
		case 'quincenal':
			return calcularFechaQuincenalPorCortes($fechaBase, $indice);
		case 'mensual':
			return sumarMesesRespetandoDia($fechaBase, $indice, $diaObjetivo);
		case 'semestral':
			return sumarMesesRespetandoDia($fechaBase, 6 * $indice, $diaObjetivo);
		case 'anual':
			return sumarMesesRespetandoDia($fechaBase, 12 * $indice, $diaObjetivo);
		default:
			echo "No se encontro la periodicidad: $periodicidad";exit;
			return null;
	}
}

function gastoMensajeCrearTransaccional($mysqli, $contenido, $fechaCreacion, $codInterConsulta, $codUsuario = 0)
{
	$contenido= trim((string)$contenido);
	$codInterConsulta= (int)$codInterConsulta;
	$codUsuario= (int)$codUsuario;
	if ($contenido === '' || $codInterConsulta <= 0) {
		throw new Exception('No se recibieron los datos del recordatorio del Hilo.');
	}
	$longitud= function_exists('mb_strlen') ? mb_strlen($contenido, 'ISO-8859-1') : strlen($contenido);
	if ($longitud > 750) {
		throw new Exception('El recordatorio de la cuota supera el limite permitido.');
	}
	$stmt= $mysqli->prepare('INSERT INTO mensaje (contenido,fecha_creacion,cod_interConsultaFK,cod_usuarioFK) VALUES (?,?,?,?)');
	if (!$stmt) {
		throw new Exception('No se pudo preparar el recordatorio de la cuota.');
	}
	$stmt->bind_param('ssii', $contenido, $fechaCreacion, $codInterConsulta, $codUsuario);
	if (!$stmt->execute()) {
		$mensajeError= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo guardar el recordatorio de la cuota: '.$mensajeError);
	}
	$idMensaje= (int)$stmt->insert_id;
	$stmt->close();
	return $idMensaje;
}

function gastoProyectoVincularTransaccional($mysqli, $codInterConsulta, $codProyecto, $estado = 'activo')
{
	$codInterConsulta= (int)$codInterConsulta;
	$codProyecto= (int)$codProyecto;
	if ($codInterConsulta <= 0 || $codProyecto <= 0) {
		return;
	}
	if (!gastoDistribucionTablaDisponible($mysqli, 'interconsulta_proyecto_gasto')) {
		throw new Exception('Falta la relacion entre Hilos y proyectos de gasto. Ejecute primero la actualizacion correspondiente.');
	}
	$stmt= $mysqli->prepare("INSERT INTO interconsulta_proyecto_gasto (cod_interConsultaFK,cod_proyecto_gastoFK,estado)
		VALUES (?,?,?) ON DUPLICATE KEY UPDATE estado=VALUES(estado),fecha_edit=NOW()");
	if (!$stmt) {
		throw new Exception('No se pudo preparar el vinculo entre el Hilo y el proyecto.');
	}
	$stmt->bind_param('iis', $codInterConsulta, $codProyecto, $estado);
	if (!$stmt->execute()) {
		$mensajeError= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo vincular el Hilo con el proyecto: '.$mensajeError);
	}
	$stmt->close();
}

function gastoProyectoCrearTransaccional($mysqli, $nombre, $codInterConsulta = 0)
{
	$nombre= trim((string)$nombre);
	if ($nombre === '') {
		$nombre= 'Proyecto financiero';
	}
	$estado= 'activo';
	$stmt= $mysqli->prepare('INSERT INTO proyectos_gasto (nombre,estado) VALUES (?,?)');
	if (!$stmt) {
		throw new Exception('No se pudo preparar el proyecto del movimiento.');
	}
	$stmt->bind_param('ss', $nombre, $estado);
	if (!$stmt->execute()) {
		$mensajeError= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo crear el proyecto del movimiento: '.$mensajeError);
	}
	$idProyecto= (int)$stmt->insert_id;
	$stmt->close();
	gastoProyectoVincularTransaccional($mysqli, $codInterConsulta, $idProyecto);
	return $idProyecto;
}

function gastoRegistrarSolicitudEliminacionTransaccional($mysqli, $idgastos, $codUsuario, $estadoAnterior, $estadoNuevo, $registroResumen)
{
	if (!solicitudEliminadoEsEstadoInactivo($estadoNuevo)
		|| solicitudEliminadoEsEstadoInactivo($estadoAnterior)) {
		return 0;
	}
	if (!gastoDistribucionTablaDisponible($mysqli, 'solicitud_eliminado')) {
		throw new Exception('No esta disponible la trazabilidad de solicitudes de eliminacion.');
	}
	$tabla= 'gastos';
	$pkColumna= 'idgastos';
	$pkValor= (string)(int)$idgastos;
	$stmt= $mysqli->prepare("SELECT id_solicitud_eliminado FROM solicitud_eliminado
		WHERE estado='pendiente' AND tabla_nombre=? AND registro_pk_columna=? AND registro_pk_valor=?
		ORDER BY id_solicitud_eliminado LIMIT 1 FOR UPDATE");
	if (!$stmt) {
		throw new Exception('No se pudo comprobar la solicitud de eliminacion del gasto.');
	}
	$stmt->bind_param('sss', $tabla, $pkColumna, $pkValor);
	$stmt->execute();
	$fila= $stmt->get_result()->fetch_assoc();
	$stmt->close();
	if ($fila) {
		return (int)$fila['id_solicitud_eliminado'];
	}
	$motivoSolicitud= 'Solicitud automatica por edicion de gasto.';
	$estadoColumna= 'estado';
	$codUsuario= (int)$codUsuario;
	$stmt= $mysqli->prepare("INSERT INTO solicitud_eliminado
		(id_usuario_solicitud,motivo,tabla_nombre,registro_pk_columna,registro_pk_valor,registro_resumen,estado_columna)
		VALUES (?,?,?,?,?,?,?)");
	if (!$stmt) {
		throw new Exception('No se pudo preparar la solicitud de eliminacion del gasto.');
	}
	$stmt->bind_param('issssss', $codUsuario, $motivoSolicitud, $tabla, $pkColumna, $pkValor, $registroResumen, $estadoColumna);
	if (!$stmt->execute()) {
		$mensajeError= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo registrar la solicitud de eliminacion del gasto: '.$mensajeError);
	}
	$idSolicitud= (int)$stmt->insert_id;
	$stmt->close();
	return $idSolicitud;
}

function obtenerOCrearProyectoGastoParaCuotas($mysqli, $idBaseSerie, $motivoPrimeraCuota) {
	$codProyectoGasto= null;
	$codInterConsulta= null;

	$sql= "SELECT cod_proyecto_gastoFK, motivo, cod_interConsultaFK FROM gastos WHERE idgastos = ? LIMIT 1";
	$stmt= $mysqli->prepare($sql);
	$stmt->bind_param('i', $idBaseSerie);
	$stmt->execute();
	$result= $stmt->get_result();
	if ($row= $result->fetch_assoc()) {
		$codProyectoGasto= $row['cod_proyecto_gastoFK'];
		$codInterConsulta= $row['cod_interConsultaFK'];
		if (trim((string)$row['motivo']) != "") {
			$motivoPrimeraCuota= $row['motivo'];
		}
	}
	$stmt->close();
	if (empty($codProyectoGasto) && !empty($codInterConsulta)
		&& gastoDistribucionTablaDisponible($mysqli, 'interconsulta_proyecto_gasto')) {
		$stmt= $mysqli->prepare("SELECT cod_proyecto_gastoFK FROM interconsulta_proyecto_gasto
			WHERE cod_interConsultaFK=? AND estado='activo' ORDER BY id LIMIT 1");
		if ($stmt) {
			$stmt->bind_param('i', $codInterConsulta);
			$stmt->execute();
			$filaProyectoHilo= $stmt->get_result()->fetch_assoc();
			$stmt->close();
			if ($filaProyectoHilo) {
				$codProyectoGasto= (int)$filaProyectoHilo['cod_proyecto_gastoFK'];
			}
		}
	}

	$nombreProyecto= trim($motivoPrimeraCuota);
	if ($nombreProyecto == "") {
		$nombreProyecto= "Gasto recurrente ".$idBaseSerie;
	}

	if (!empty($codProyectoGasto)) {
		$estadoProyecto= 'activo';
		$stmt= $mysqli->prepare('UPDATE proyectos_gasto SET nombre=?,estado=? WHERE id=?');
		if (!$stmt) {
			throw new Exception('No se pudo preparar el proyecto de la serie.');
		}
		$stmt->bind_param('ssi', $nombreProyecto, $estadoProyecto, $codProyectoGasto);
		if (!$stmt->execute()) {
			$mensajeError= $stmt->error;
			$stmt->close();
			throw new Exception('No se pudo actualizar el proyecto de la serie: '.$mensajeError);
		}
		$stmt->close();
		gastoProyectoVincularTransaccional($mysqli, $codInterConsulta, $codProyectoGasto);
	} else {
		$codProyectoGasto= gastoProyectoCrearTransaccional($mysqli, $nombreProyecto." - serie ".$idBaseSerie, $codInterConsulta);
	}

	$sql= "UPDATE gastos SET cod_proyecto_gastoFK = ? WHERE idgastos = ?";
	$stmt= $mysqli->prepare($sql);
	$stmt->bind_param('ii', $codProyectoGasto, $idBaseSerie);
	if (!$stmt->execute()) {
		$mensajeError= $stmt->error;
		$stmt->close();
		throw new Exception('No se pudo vincular el gasto base con su proyecto: '.$mensajeError);
	}
	$stmt->close();

	return $codProyectoGasto;
}

function obtenerNombreProyectoGastoInterConsulta($cod_interConsultaFK, $nombreFallback= '') {
	$nombreProyecto= trim((string)$nombreFallback);
	if (!empty($cod_interConsultaFK) && function_exists('obtenerInterConsulta')) {
		$registros= obtenerInterConsulta(array(
			'cod_interConsulta' => $cod_interConsultaFK
		), 1);
		if (isset($registros[0]) && trim((string)$registros[0]['asunto']) != '') {
			$nombreProyecto= trim((string)$registros[0]['asunto']);
		}
	}
	if ($nombreProyecto == '') {
		$nombreProyecto= 'Hilo financiero '.$cod_interConsultaFK;
	}
	return $nombreProyecto;
}

function crearInterConsultaParaGasto($motivo, $tipo, $cod_usuario, $cod_local, $mysqliTransaccion = null) {
	if (!function_exists('abmInterConsulta')) {
		return '';
	}
	$asunto= trim((string)$motivo);
	if ($asunto == '') {
		$asunto= 'Movimiento financiero';
	}
	$tipoHilo= (strtolower(trim((string)$tipo)) == 'ingreso') ? 'pago' : 'egreso';
	if ($mysqliTransaccion instanceof mysqli) {
		$observacion= 'Hilo creado automaticamente desde Resumen de flujo financiero.';
		$estadoHilo= 'pendiente';
		$codVenta= 0;
		$montoLimite= 0;
		$codUsuario= (int)$cod_usuario;
		$codLocal= (int)$cod_local;
		$stmt= $mysqliTransaccion->prepare("INSERT INTO interconsulta
			(asunto,observacion,estado,tipo,cod_ventaFK,cod_usuarioFK_create,fecha_creacion,cod_localFK,monto_limite)
			VALUES (?,?,?,?,?,?,NOW(),?,?)");
		if (!$stmt) {
			throw new Exception('No se pudo preparar el Hilo del movimiento.');
		}
		$stmt->bind_param('ssssiiii', $asunto, $observacion, $estadoHilo, $tipoHilo, $codVenta, $codUsuario, $codLocal, $montoLimite);
		if (!$stmt->execute()) {
			$mensajeError= $stmt->error;
			$stmt->close();
			throw new Exception('No se pudo crear el Hilo del movimiento: '.$mensajeError);
		}
		$codInterConsulta= (int)$stmt->insert_id;
		$stmt->close();
		// abmInterConsulta crea tambien un proyecto para Hilos financieros. Se
		// replica aqui con la misma conexion para que ambos se reviertan juntos.
		gastoProyectoCrearTransaccional($mysqliTransaccion, $asunto, $codInterConsulta);
		return $codInterConsulta;
	}
	return abmInterConsulta('', $asunto, 'Hilo creado automaticamente desde Resumen de flujo financiero.', 'pendiente', $tipoHilo, NULL, $cod_usuario, $cod_usuario, $cod_local, 0);
}

function obtenerOCrearInterConsultaMovimientoFinanciero($motivo, $tipo, $cod_usuario, $cod_local) {
	$asunto= trim((string)$motivo);
	if ($asunto == '') {
		$asunto= 'Movimiento financiero';
	}
	$tipoHilo= (strtolower(trim((string)$tipo)) == 'ingreso') ? 'pago' : 'egreso';
	$mysqli= conectar_al_servidor();
	$sql= "SELECT cod_interConsulta FROM interconsulta WHERE estado <> 'inactivo' AND UPPER(TRIM(asunto)) = UPPER(TRIM(?)) AND LOWER(TRIM(IFNULL(tipo, ''))) IN (?, ?) ";
	$tipoPlural= ($tipoHilo == 'pago') ? 'pagos' : 'egresos';
	$parametros= array($asunto, $tipoHilo, $tipoPlural);
	$ss= "sss";
	if (is_numeric($cod_local) && intval($cod_local) > 0) {
		$sql .= "AND cod_localFK = ? ";
		$parametros[]= intval($cod_local);
		$ss .= "i";
	}
	$sql .= "ORDER BY cod_interConsulta DESC LIMIT 1";
	$stmt= $mysqli->prepare($sql);
	$refs= array();
	foreach ($parametros as $key => $valor) {
		$refs[$key]= &$parametros[$key];
	}
	call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
	$stmt->execute();
	$result= $stmt->get_result();
	if ($row= $result->fetch_assoc()) {
		$stmt->close();
		return $row['cod_interConsulta'];
	}
	$stmt->close();
	return crearInterConsultaParaGasto($asunto, $tipo, $cod_usuario, $cod_local);
}

function obtenerOCrearProyectoGastoParaInterConsulta($cod_interConsultaFK, $nombreFallback= '', $codProyectoSolicitado= '', $mysqliTransaccion = null) {
	if (empty($cod_interConsultaFK) || !is_numeric($cod_interConsultaFK)) {
		return (is_numeric($codProyectoSolicitado) ? $codProyectoSolicitado : '');
	}
	$codInterConsulta= (int)$cod_interConsultaFK;
	if ($mysqliTransaccion instanceof mysqli) {
		if (is_numeric($codProyectoSolicitado) && intval($codProyectoSolicitado) > 0) {
			$codProyecto= intval($codProyectoSolicitado);
			$stmt= $mysqliTransaccion->prepare("SELECT pg.id FROM proyectos_gasto pg
				INNER JOIN interconsulta_proyecto_gasto ipg ON ipg.cod_proyecto_gastoFK=pg.id
				WHERE pg.id=? AND pg.estado='activo' AND ipg.cod_interConsultaFK=? AND ipg.estado='activo' LIMIT 1");
			if (!$stmt) {
				throw new Exception('No se pudo validar el proyecto seleccionado.');
			}
			$stmt->bind_param('ii', $codProyecto, $codInterConsulta);
			$stmt->execute();
			$existeProyecto= $stmt->get_result()->num_rows > 0;
			$stmt->close();
			if (!$existeProyecto) {
				throw new Exception('El proyecto seleccionado no esta activo o no pertenece al Hilo indicado. Actualice la pantalla.');
			}
			return $codProyecto;
		}
		$nombreProyecto= trim((string)$nombreFallback);
		$stmt= $mysqliTransaccion->prepare('SELECT asunto FROM interconsulta WHERE cod_interConsulta=? LIMIT 1');
		if ($stmt) {
			$stmt->bind_param('i', $codInterConsulta);
			$stmt->execute();
			$filaHilo= $stmt->get_result()->fetch_assoc();
			$stmt->close();
			if ($filaHilo && trim((string)$filaHilo['asunto']) !== '') {
				$nombreProyecto= trim((string)$filaHilo['asunto']);
			}
		}
		if ($nombreProyecto === '') {
			$nombreProyecto= 'Hilo financiero '.$codInterConsulta;
		}
		$stmt= $mysqliTransaccion->prepare("SELECT pg.id FROM proyectos_gasto pg
			INNER JOIN interconsulta_proyecto_gasto ipg ON ipg.cod_proyecto_gastoFK=pg.id
			WHERE ipg.cod_interConsultaFK=? AND ipg.estado='activo' AND pg.nombre=?
			ORDER BY pg.id LIMIT 1");
		if (!$stmt) {
			throw new Exception('No se pudo consultar el proyecto del Hilo.');
		}
		$stmt->bind_param('is', $codInterConsulta, $nombreProyecto);
		$stmt->execute();
		$filaProyecto= $stmt->get_result()->fetch_assoc();
		$stmt->close();
		if ($filaProyecto) {
			return (int)$filaProyecto['id'];
		}
		return gastoProyectoCrearTransaccional($mysqliTransaccion, $nombreProyecto, $codInterConsulta);
	}

	if (is_numeric($codProyectoSolicitado) && intval($codProyectoSolicitado) > 0) {
		$codProyecto= intval($codProyectoSolicitado);
		if (function_exists('vincularProyectoGastoInterConsulta')) {
			vincularProyectoGastoInterConsulta($cod_interConsultaFK, $codProyecto);
		}
		return $codProyecto;
	}

	$nombreProyecto= obtenerNombreProyectoGastoInterConsulta($cod_interConsultaFK, $nombreFallback);
	$proyectos= obtenerProyectoGasto(array(
		'nombre_exacto' => $nombreProyecto,
		'cod_interConsultaFK' => $cod_interConsultaFK,
		'incluir_sin_gastos' => 'true',
	), 1);
	if (count($proyectos) > 0) {
		$codProyecto= $proyectos[0]['id'];
		if (function_exists('vincularProyectoGastoInterConsulta')) {
			vincularProyectoGastoInterConsulta($cod_interConsultaFK, $codProyecto);
		}
		return $codProyecto;
	}

	$proyectos= obtenerProyectoGasto(array(
		'nombre_exacto' => $nombreProyecto,
		'incluir_sin_gastos' => 'true',
	), 1);
	if (count($proyectos) > 0) {
		$codProyecto= $proyectos[0]['id'];
		if (function_exists('vincularProyectoGastoInterConsulta')) {
			vincularProyectoGastoInterConsulta($cod_interConsultaFK, $codProyecto);
		}
		return $codProyecto;
	}

	return abmProyectoGasto('', $nombreProyecto, 'activo', $cod_interConsultaFK);
}

function obtenerProyectoGastoSerie($mysqli, $idgastos, $codProyectoGastoSolicitado= '') {
	if (is_numeric($codProyectoGastoSolicitado) && intval($codProyectoGastoSolicitado) > 0) {
		return intval($codProyectoGastoSolicitado);
	}

	$sql= "SELECT g.cod_proyecto_gastoFK, g.cod_gasto_padre, gp.cod_proyecto_gastoFK AS cod_proyecto_padre
		FROM gastos g
		LEFT JOIN gastos gp ON gp.idgastos = g.cod_gasto_padre
		WHERE g.idgastos = ?
		LIMIT 1";
	$stmt= $mysqli->prepare($sql);
	$stmt->bind_param('i', $idgastos);
	$stmt->execute();
	$result= $stmt->get_result();
	$row= $result->fetch_assoc();
	$stmt->close();

	if ($row) {
		if (!empty($row['cod_gasto_padre']) && !empty($row['cod_proyecto_padre'])) {
			return $row['cod_proyecto_padre'];
		}
		if (!empty($row['cod_proyecto_gastoFK'])) {
			return $row['cod_proyecto_gastoFK'];
		}
	}

	return (is_numeric($codProyectoGastoSolicitado) ? $codProyectoGastoSolicitado : '');
}

function gastoValidarLimiteHiloBloqueado($mysqli, $codInterConsulta, $montoNuevo, $idsExcluir = array())
{
	$codInterConsulta= (int)$codInterConsulta;
	if ($codInterConsulta <= 0) {
		return;
	}
	$stmt= $mysqli->prepare('SELECT monto_limite FROM interconsulta WHERE cod_interConsulta=? LIMIT 1');
	if (!$stmt) {
		throw new Exception('No se pudo consultar el limite del Hilo.');
	}
	$stmt->bind_param('i', $codInterConsulta);
	$stmt->execute();
	$fila= $stmt->get_result()->fetch_assoc();
	$stmt->close();
	$limite= $fila ? (int)$fila['monto_limite'] : 0;
	if ($limite <= 0) {
		return;
	}
	$idsExcluir= gastoDistribucionNormalizarIdsExcluir($idsExcluir);
	$excluir= count($idsExcluir) > 0 ? ' AND idgastos NOT IN ('.implode(',', $idsExcluir).')' : '';
	$resultado= $mysqli->query("SELECT IFNULL(SUM(monto),0) AS total FROM gastos
		WHERE cod_interConsultaFK=$codInterConsulta
		AND LOWER(TRIM(IFNULL(tipo,'')))='egreso'
		AND LOWER(TRIM(IFNULL(estado,''))) IN ('activo','pendiente','solicitado')$excluir");
	if (!$resultado) {
		throw new Exception('No se pudo calcular el presupuesto utilizado por el Hilo.');
	}
	$filaTotal= $resultado->fetch_assoc();
	$utilizado= $filaTotal ? (int)round($filaTotal['total']) : 0;
	$montoNuevo= gastoDistribucionMontoEntero($montoNuevo);
	if ($utilizado + $montoNuevo > $limite) {
		throw new Exception('El gasto supera el monto limite del Hilo. Disponible: Gs. '.number_format(max(0, $limite - $utilizado), 0, ',', '.').'.');
	}
}

function gastoResolverSerieParaEdicion($mysqli, $idgastos, $editarCuotas)
{
	$idgastos= (int)$idgastos;
	$salida= array('gasto' => null, 'ids' => array());
	if ($idgastos <= 0) {
		return $salida;
	}
	$stmt= $mysqli->prepare('SELECT * FROM gastos WHERE idgastos=? LIMIT 1');
	if (!$stmt) {
		throw new Exception('No se pudo consultar el movimiento a editar.');
	}
	$stmt->bind_param('i', $idgastos);
	$stmt->execute();
	$gasto= $stmt->get_result()->fetch_assoc();
	$stmt->close();
	if (!$gasto) {
		throw new Exception('El movimiento a editar ya no existe.');
	}
	$salida['gasto']= $gasto;
	$salida['ids']= array($idgastos);
	if ($editarCuotas !== 'true') {
		return $salida;
	}
	$idSerie= (int)$gasto['cod_gasto_padre'];
	if ($idSerie <= 0) {
		$idSerie= $idgastos;
	}
	$resultadoHijos= $mysqli->query("SELECT idgastos FROM gastos WHERE cod_gasto_padre=$idSerie LIMIT 1");
	$tieneIdentidadSerie= (int)$gasto['cod_gasto_padre'] > 0 || ($resultadoHijos && $resultadoHijos->num_rows > 0);
	if ($tieneIdentidadSerie) {
		$stmt= $mysqli->prepare("SELECT idgastos FROM gastos WHERE (idgastos=? OR cod_gasto_padre=?) AND (idgastos=? OR LOWER(TRIM(IFNULL(estado,''))) IN ('pendiente','solicitado')) ORDER BY idgastos");
		$stmt->bind_param('iii', $idSerie, $idSerie, $idgastos);
	} else {
		// Fallback exclusivamente para series legacy que no guardaban padre.
		$codProyecto= (int)$gasto['cod_proyecto_gastoFK'];
		if ($codProyecto <= 0) {
			return $salida;
		}
		$codInterConsulta= (int)$gasto['cod_interConsultaFK'];
		$codMotivo= (int)$gasto['cod_motivoIngresoEgresoFK'];
		$codLocal= (int)$gasto['cod_local'];
		$stmt= $mysqli->prepare("SELECT idgastos FROM gastos WHERE cod_proyecto_gastoFK=?
			AND cod_interConsultaFK=? AND cod_motivoIngresoEgresoFK=? AND cod_local=?
			AND modalidad='credito' AND (idgastos=? OR LOWER(TRIM(IFNULL(estado,''))) IN ('pendiente','solicitado')) ORDER BY idgastos");
		$stmt->bind_param('iiiii', $codProyecto, $codInterConsulta, $codMotivo, $codLocal, $idgastos);
	}
	if (!$stmt || !$stmt->execute()) {
		if ($stmt) { $stmt->close(); }
		throw new Exception('No se pudo resolver la serie de cuotas a editar.');
	}
	$resultado= $stmt->get_result();
	$ids= array();
	while ($fila= $resultado->fetch_assoc()) {
		$ids[(int)$fila['idgastos']]= (int)$fila['idgastos'];
	}
	$stmt->close();
	ksort($ids, SORT_NUMERIC);
	$salida['ids']= array_values($ids);
	return $salida;
}

function gastoResolverAlcanceEdicionCuotas($editarCuotas, $idsSerie, $conservarLegacy)
{
	if ($editarCuotas !== 'true' || $conservarLegacy || !is_array($idsSerie)) {
		return 'false';
	}
	$idsUnicos= array();
	foreach ($idsSerie as $idSerie) {
		$idSerie= (int)$idSerie;
		if ($idSerie > 0) {
			$idsUnicos[$idSerie]= true;
		}
	}
	return count($idsUnicos) > 1 ? 'true' : 'false';
}

function registrarCuotasRecurrentes($mysqli, $idBaseSerie, $Arreglo, $cantCuotas, $periodicidad, $fechaBaseStr, $monto, $motivo, $cod_usuario, $personales, $cod_local, $tipo, $codcaja, $idaperturacierrecaja, $nroboleta, $banco, $nrocuenta, $cod_motivo, $cod_interConsultaFK, $codProyectoGastoFijo= '', $distribucionGasto= array()) {
	$estado= 'pendiente';
	if ($cantCuotas <= 1) {
		return;
	}

	$fechaBase = DateTime::createFromFormat('Y-m-d', $fechaBaseStr);
	if ($fechaBase === false) {
		return;
	}

	$codProyectoGasto= ($codProyectoGastoFijo != '' && is_numeric($codProyectoGastoFijo)) ? $codProyectoGastoFijo : obtenerOCrearProyectoGastoParaCuotas($mysqli, $idBaseSerie, $motivo);
		
	$consultaRecurrente = "Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta,cod_motivoIngresoEgresoFK,cod_interConsultaFK,modalidad,cod_proyecto_gastoFK,cod_gasto_padre)
	values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmtRecurrente = $mysqli->prepare($consultaRecurrente);
	if (!$stmtRecurrente) {
		throw new Exception('No se pudo preparar la serie de cuotas del gasto.');
	}
	$ssRecurrente = str_repeat('s', 19);
	$modalidadCredito = 'credito';
		
	for ($i = 1; $i < $cantCuotas; $i++) {
		$motivoCuota = 'Cuota '.($i + 1).' de '.trim($motivo).' (' . intval($idBaseSerie).')';
		$fechaCuota = calcularFechaCuotaRecurrente($fechaBase, $periodicidad, $i);
		if ($fechaCuota == null) {
			continue;
		}

		$fechaCuotaFormat = $fechaCuota->format('Y-m-d');
		if (strtolower(trim((string)$tipo)) == 'egreso'
			&& flujoGastoEstadoComputableResumen($estado)
			&& !empty($distribucionGasto['asignaciones'])) {
			gastoDistribucionValidarPresupuestos(
				$mysqli,
				$distribucionGasto,
				$cod_motivo,
				$fechaCuota->format('Y-m-01'),
				$fechaCuota->format('Y-m-t'),
				array(),
				false
			);
		}
		if (strtolower(trim((string)$tipo)) == 'egreso' && !empty($cod_interConsultaFK)) {
			gastoValidarLimiteHiloBloqueado($mysqli, $cod_interConsultaFK, $monto);
		}
		$stmtRecurrente->bind_param($ssRecurrente,$Arreglo,$monto,$motivoCuota,$fechaCuotaFormat,$estado,$cod_usuario,$personales,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$nroboleta, $banco , $nrocuenta,$cod_motivo,$cod_interConsultaFK,$modalidadCredito,$codProyectoGasto,$idBaseSerie);
		if (!$stmtRecurrente->execute()) {
			throw new Exception('No se pudo guardar una cuota de la serie: '.$stmtRecurrente->error);
		}
		$idgastos = mysqli_insert_id($mysqli);
		if (!empty($distribucionGasto['asignaciones'])) {
			gastoDistribucionGuardar($mysqli, $idgastos, $distribucionGasto, $cod_usuario, 'cuota_recurrente', 'copiar_cuota', true, array('modo' => '', 'asignaciones' => array()));
		}

		// Programa tambien el mensaje de recordatorio si tiene una interconsulta asociada
		if (!empty($cod_interConsultaFK)) {
			$cod_mensaje= gastoMensajeCrearTransaccional($mysqli, "El gasto $motivoCuota vence hoy ", $fechaCuotaFormat, $cod_interConsultaFK, $cod_usuario);
			
			// Actualiza el cod_mensaje del gasto ingresado
			$sql = "UPDATE gastos SET cod_mensajeFK = ? WHERE idgastos = ?";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param('ii', $cod_mensaje, $idgastos);
			if (!$stmt->execute()) {
				$mensajeError= $stmt->error;
				$stmt->close();
				throw new Exception('No se pudo vincular el recordatorio con la cuota: '.$mensajeError);
			}
			$stmt->close();
		}
	}

	$stmtRecurrente->close();
}

function abmGasto($Arreglo,$nroboleta, $banco , $nrocuenta,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion,$editar_cuotas= "false", $cod_proyecto_gastoFK= NULL, $distribucionGasto= array(), $idMovimientoUenoGasto= 0)
{
		
if ($codcaja == "0" || $codcaja == 0 || $idaperturacierrecaja == 0 || $idaperturacierrecaja == "0") {
	return array('1' => 'error', '2' => 'La caja o su apertura ya no son validas. Actualice la pantalla e intente nuevamente.');
}
if($monto==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$cantCuotas = isset($_POST['cantCuotas']) ? intval($_POST['cantCuotas']) : 0;
$periodicidad = isset($_POST['periodicidad']) ? mb_convert_encoding((string)$_POST['periodicidad'], 'ISO-8859-1', 'UTF-8') : '';
$idMovimientoUenoGasto= (int)$idMovimientoUenoGasto;
$editar_cuotas= ((string)$editar_cuotas === 'true') ? 'true' : 'false';
$periodicidad= strtolower(trim((string)$periodicidad));
$periodicidadesPermitidas= array('semanal','quincenal','mensual','semestral','anual');
if ($cantCuotas < 0 || $cantCuotas > 120) {
	return array('1' => 'error', '2' => 'La cantidad de cuotas debe estar entre 1 y 120.');
}
if ($cantCuotas > 1 && !in_array($periodicidad, $periodicidadesPermitidas, true)) {
	return array('1' => 'error', '2' => 'Seleccione una periodicidad valida para la serie de cuotas.');
}
if ($periodicidad !== '' && !in_array($periodicidad, $periodicidadesPermitidas, true)) {
	return array('1' => 'error', '2' => 'La periodicidad recibida no es valida.');
}
if ($idMovimientoUenoGasto > 0 && $cantCuotas > 1) {
	return array('1' => 'error', '2' => 'Un debito Ueno solo puede crear un gasto unico. Registre las cuotas futuras por separado.');
}

$proyectoSolicitado= trim((string)$cod_proyecto_gastoFK);
if (esTipoDepositoCentral($tipo)) {
	if ($operacion == 'nuevo') {
		$cod_interConsultaFK= NULL;
	}
	$cantCuotas= 0;
	$periodicidad= '';
	$proyectoSolicitado= '';
	$cod_proyecto_gastoFK= NULL;
	$estado= ($operacion == 'editar' && strtolower(trim((string)$estado)) == 'inactivo') ? 'Inactivo' : 'Activo';
}
if ($proyectoSolicitado == "0") {
	$proyectoSolicitado= "";
	$cod_proyecto_gastoFK= NULL;
}
$modalidad= (($cantCuotas > 1) ? 'credito' : 'contado');

// El concepto es una referencia contable, no un texto libre. Se valida antes
// de abrir la transaccion para no conservar locks ante una solicitud invalida.
if (!is_numeric($cod_motivo) || (int)$cod_motivo <= 0) {
	return array('1' => 'error', '2' => 'Seleccione un concepto financiero valido.');
}
$mysqliConcepto= conectar_al_servidor();
$codMotivoValidar= (int)$cod_motivo;
$stmtConcepto= $mysqliConcepto->prepare("SELECT categoria,estado FROM motivos_ingreso_egreso WHERE cod_motivo_ingreso_egreso=? LIMIT 1");
if (!$stmtConcepto) {
	$mysqliConcepto->close();
	return array('1' => 'error', '2' => 'No se pudo validar el concepto financiero.');
}
$stmtConcepto->bind_param('i', $codMotivoValidar);
$stmtConcepto->execute();
$conceptoMovimiento= $stmtConcepto->get_result()->fetch_assoc();
$stmtConcepto->close();
$permitirMotivoHistoricoEdicion= false;
if (!$conceptoMovimiento) {
	$mysqliConcepto->close();
	return array('1' => 'error', '2' => 'El concepto financiero no existe.');
}
if (strtolower(trim((string)$conceptoMovimiento['estado'])) !== 'activo') {
	$mysqliConcepto->close();
	return array('1' => 'error', '2' => 'El concepto financiero esta inactivo y sus movimientos historicos son de solo lectura.');
}
$categoriaConcepto= strtolower(trim((string)$conceptoMovimiento['categoria']));
$tipoNormalizadoGasto= strtolower(trim((string)$tipo));
if (!esTipoDepositoCentral($tipo)
	&& (($tipoNormalizadoGasto == 'ingreso' && $categoriaConcepto != 'ingreso')
		|| ($tipoNormalizadoGasto == 'egreso' && $categoriaConcepto == 'ingreso'))) {
	$mysqliConcepto->close();
	return array('1' => 'error', '2' => 'El concepto seleccionado no corresponde al tipo de movimiento.');
}
$mysqliConcepto->close();

// Define el estado definitivo antes de comparar una eventual conciliacion Ueno.
$registros_motivos= buscarabmmotivoingresoegreso('', 'activo',$cod_motivo);
$fechaGasto = DateTime::createFromFormat('!Y-m-d', substr((string)$fecha, 0, 10));
$pasadoManana = new DateTime('today');
$pasadoManana->modify('+1 day');
$necesitaAutorizacion= isset($registros_motivos['4'][0]['necesita_autorizacion'])
	? $registros_motivos['4'][0]['necesita_autorizacion'] : '0';
if (!esTipoDepositoCentral($tipo) && $estado == 'Activo' && $necesitaAutorizacion == '1') {
	$estado = ($fechaGasto && ($fechaGasto > $pasadoManana)) ? 'pendiente' : 'solicitado';
}
if ($idMovimientoUenoGasto > 0) {
	if ($operacion !== 'nuevo' || strtolower(trim((string)$tipo)) !== 'egreso') {
		return array('1' => 'error', '2' => 'El debito Ueno solo puede utilizarse al crear un egreso nuevo.');
	}
	// El vinculo bancario confirma el gasto en esta misma transaccion. Se usa
	// ese estado final desde ahora para no omitir presupuesto ni limite de Hilo.
	$estado= 'Activo';
}

$mysqli=conectar_al_servidor();
$mysqli->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
$transaccionHiloGasto= false;
$transaccionGastoActiva= false;
$distribucionAnteriorEdicion= null;
$gastoPreliminarEdicion= null;
$cod_usuario_autoriz= NULL;
$idsSerieEdicion= array();
$idsMutarSerieEdicion= array();
$gastosSerieBloqueados= array();
$idsExcluirPresupuesto= array();
$vinculosUenoEdicion= array('ids_gastos' => array(), 'ids_movimientos' => array());
$foto_documento_firmado_edicion= isset($_POST['foto_documento_firmado']) ? trim((string)$_POST['foto_documento_firmado']) : '';
$ext_documento_firmado_edicion= isset($_POST['ext_documento_firmado']) ? trim((string)$_POST['ext_documento_firmado']) : '';
$mantener_estado_por_documento_firmado= ($foto_documento_firmado_edicion != '' && $ext_documento_firmado_edicion != '');
$fechaPresupuesto= null;
if (!$mysqli->begin_transaction()) {
	$mysqli->close();
	return array("1" => "error", "2" => "No se pudo iniciar el guardado seguro del movimiento.");
}
$transaccionGastoActiva= true;
try {
	if ($operacion == 'editar') {
		$solicitoEditarSerie= ($editar_cuotas === 'true' && empty($distribucionGasto['conservar_legacy']));
		$serieEdicion= gastoResolverSerieParaEdicion($mysqli, $idgastos, ($solicitoEditarSerie ? 'true' : 'false'));
		// El cliente expresa la intencion, pero el servidor decide el alcance real.
		// Sin otra cuota editable (o al conservar un legacy) siempre es una edicion individual.
		$editar_cuotas= gastoResolverAlcanceEdicionCuotas(
			($solicitoEditarSerie ? 'true' : 'false'),
			$serieEdicion['ids'],
			!empty($distribucionGasto['conservar_legacy'])
		);
		$gastoPreliminarEdicion= $serieEdicion['gasto'];
		if (!$gastoPreliminarEdicion) {
			throw new Exception('El movimiento a editar ya no existe.');
		}
		$cod_usuario_autoriz= isset($gastoPreliminarEdicion['cod_usuario_autoriz'])
			? $gastoPreliminarEdicion['cod_usuario_autoriz'] : NULL;
		if (empty($cod_interConsultaFK)) {
			$cod_interConsultaFK= $gastoPreliminarEdicion['cod_interConsultaFK'];
		}
		// El estado usado para presupuesto, Hilo y proteccion Ueno debe ser
		// exactamente el que terminara persistido, incluso si el POST fue forjado.
		if (!empty($distribucionGasto['conservar_legacy'])) {
			$estado= $gastoPreliminarEdicion['estado'];
		} else if (esTipoDepositoCentral($tipo)) {
			$estado= (mb_strtolower((string)$estado, 'UTF-8') == 'inactivo') ? 'Inactivo' : 'Activo';
		} else if ($mantener_estado_por_documento_firmado && isset($gastoPreliminarEdicion['estado'])) {
			$estado= $gastoPreliminarEdicion['estado'];
		} else {
			$estado= (mb_strtolower((string)$estado, 'UTF-8') == 'inactivo'
				? 'Inactivo' : (($fechaGasto && ($fechaGasto > $pasadoManana)) ? 'pendiente' : 'solicitado'));
			$cod_usuario_autoriz= NULL;
		}
	}

	// Se toman primero las cerraduras de presupuesto aun cuando el estado
	// recibido parezca no computable; el estado definitivo de una edicion puede
	// depender del documento firmado y nunca debe eludir la serializacion.
	if (strtolower(trim((string)$tipo)) == 'egreso' && !empty($distribucionGasto['asignaciones'])) {
		$fechaPresupuesto= DateTime::createFromFormat('Y-m-d', substr((string)$fecha, 0, 10));
		if (!$fechaPresupuesto) {
			throw new Exception('La fecha del movimiento no es valida para comprobar el presupuesto.');
		}
		gastoDistribucionValidarLocales($mysqli, $distribucionGasto['asignaciones'], $cod_usuario, $cod_local, $distribucionGasto['modo'], true);
		gastoDistribucionBloquearPresupuestos($mysqli, $distribucionGasto, $cod_motivo);
	}
	$stmtConceptoBloqueado= $mysqli->prepare("SELECT categoria,estado FROM motivos_ingreso_egreso WHERE cod_motivo_ingreso_egreso=? LIMIT 1 FOR UPDATE");
	if (!$stmtConceptoBloqueado) {
		throw new Exception('No se pudo bloquear el concepto financiero.');
	}
	$stmtConceptoBloqueado->bind_param('i', $codMotivoValidar);
	$stmtConceptoBloqueado->execute();
	$conceptoBloqueado= $stmtConceptoBloqueado->get_result()->fetch_assoc();
	$stmtConceptoBloqueado->close();
	if (!$conceptoBloqueado || strtolower(trim((string)$conceptoBloqueado['estado'])) !== 'activo') {
		throw new Exception('El concepto financiero fue inactivado y ya no admite cambios. Actualice la pantalla.');
	}
	$categoriaConceptoBloqueada= strtolower(trim((string)$conceptoBloqueado['categoria']));
	if (!esTipoDepositoCentral($tipo)
		&& (($tipoNormalizadoGasto == 'ingreso' && $categoriaConceptoBloqueada != 'ingreso')
			|| ($tipoNormalizadoGasto == 'egreso' && $categoriaConceptoBloqueada == 'ingreso'))) {
		throw new Exception('El concepto financiero cambio de categoria y ya no corresponde al movimiento.');
	}

	if ($operacion == 'nuevo' && empty($cod_interConsultaFK) && !esTipoDepositoCentral($tipo)) {
		$cod_interConsultaFK= crearInterConsultaParaGasto($motivo, $tipo, $cod_usuario, $cod_local, $mysqli);
	}
	$hiloBloqueoGasto= intval($cod_interConsultaFK);
	if ($hiloBloqueoGasto > 0) {
		$stmtBloqueoHiloGasto= $mysqli->prepare("SELECT estado,tipo,cod_localFK FROM interconsulta WHERE cod_interConsulta=? LIMIT 1 FOR UPDATE");
		if (!$stmtBloqueoHiloGasto) {
			throw new Exception('No se pudo bloquear el Hilo del movimiento.');
		}
		$stmtBloqueoHiloGasto->bind_param('i', $hiloBloqueoGasto);
		$stmtBloqueoHiloGasto->execute();
		$hiloGasto= $stmtBloqueoHiloGasto->get_result()->fetch_assoc();
		$stmtBloqueoHiloGasto->close();
		if (!$hiloGasto || strtolower(trim((string)$hiloGasto['estado'])) === 'inactivo') {
			throw new Exception('El Hilo fue archivado y ya no admite movimientos.');
		}
		if (!usuarioPuedeGestionarLocalGasto($cod_usuario, $hiloGasto['cod_localFK'])) {
			throw new Exception('No administra el local al que pertenece el Hilo seleccionado.');
		}
		if (function_exists('obtenerCategoriaPrincipalHilo')
			&& obtenerCategoriaPrincipalHilo($hiloGasto['tipo']) != 'pagos_egresos') {
			throw new Exception('El Hilo seleccionado no corresponde a movimientos financieros.');
		}
		$transaccionHiloGasto= true;
	}

	// El proyecto final se resuelve con el Hilo ya fijado. Solo se conserva una
	// vinculacion historica no vigente cuando Hilo y proyecto quedan sin cambios.
	$hiloOriginalEdicion= $gastoPreliminarEdicion ? (int)$gastoPreliminarEdicion['cod_interConsultaFK'] : 0;
	$proyectoOriginalEdicion= $gastoPreliminarEdicion ? (int)$gastoPreliminarEdicion['cod_proyecto_gastoFK'] : 0;
	$proyectoSolicitadoId= (is_numeric($proyectoSolicitado) ? (int)$proyectoSolicitado : 0);
	$parProyectoHistoricoSinCambio= ($operacion == 'editar'
		&& $hiloOriginalEdicion === $hiloBloqueoGasto
		&& ($proyectoSolicitadoId <= 0 || $proyectoSolicitadoId === $proyectoOriginalEdicion));
	if ($parProyectoHistoricoSinCambio) {
		$cod_proyecto_gastoFK= ($proyectoOriginalEdicion > 0 ? $proyectoOriginalEdicion : NULL);
	} else if ($hiloBloqueoGasto > 0) {
		$cod_proyecto_gastoFK= obtenerOCrearProyectoGastoParaInterConsulta(
			$hiloBloqueoGasto,
			$motivo,
			($proyectoSolicitadoId > 0 ? $proyectoSolicitadoId : ''),
			$mysqli
		);
	} else {
		$cod_proyecto_gastoFK= NULL;
	}

	if ($operacion == 'editar') {
		$idGastoBloqueo= (int)$idgastos;
		$idRaizPreliminar= !empty($gastoPreliminarEdicion['cod_gasto_padre'])
			? (int)$gastoPreliminarEdicion['cod_gasto_padre'] : $idGastoBloqueo;
		$idsBloqueoInicial= gastoDistribucionNormalizarIdsExcluir(array($idRaizPreliminar, $idGastoBloqueo));
		$resultadoGastoBloqueado= $mysqli->query('SELECT * FROM gastos WHERE idgastos IN ('.implode(',', $idsBloqueoInicial).') ORDER BY idgastos FOR UPDATE');
		if (!$resultadoGastoBloqueado) {
			throw new Exception('No se pudo bloquear el movimiento a editar.');
		}
		$gastoActualBloqueado= null;
		$gastoRaizBloqueado= null;
		while ($filaGastoBloqueado= $resultadoGastoBloqueado->fetch_assoc()) {
			if ((int)$filaGastoBloqueado['idgastos'] === $idGastoBloqueo) {
				$gastoActualBloqueado= $filaGastoBloqueado;
			}
			if ((int)$filaGastoBloqueado['idgastos'] === $idRaizPreliminar) {
				$gastoRaizBloqueado= $filaGastoBloqueado;
			}
		}
		if (!$gastoActualBloqueado) {
			throw new Exception('El movimiento a editar ya no existe.');
		}
		if (!usuarioPuedeGestionarLocalGasto($cod_usuario, $gastoActualBloqueado['cod_local'])) {
			throw new Exception('No administra el local de origen actual del movimiento.');
		}
		if ($editar_cuotas === 'true') {
			if (!$gastoRaizBloqueado) {
				throw new Exception('La raiz de la serie ya no existe. Edite solamente la cuota seleccionada o regularice la serie.');
			}
			$localOrigenSerie= (int)$gastoActualBloqueado['cod_local'];
			$resultadoLocalesSerie= $mysqli->query("SELECT idgastos,cod_local FROM gastos WHERE idgastos=$idRaizPreliminar OR cod_gasto_padre=$idRaizPreliminar ORDER BY idgastos FOR UPDATE");
			if (!$resultadoLocalesSerie) {
				throw new Exception('No se pudo comprobar el alcance local de la serie.');
			}
			while ($filaLocalSerie= $resultadoLocalesSerie->fetch_assoc()) {
				$localFilaSerie= (int)$filaLocalSerie['cod_local'];
				if ($localFilaSerie !== $localOrigenSerie) {
					throw new Exception('La serie contiene cuotas de distintos locales de origen. Edite solamente la cuota seleccionada o solicite una regularizacion controlada.');
				}
				if (!usuarioPuedeGestionarLocalGasto($cod_usuario, $localFilaSerie)) {
					throw new Exception('No administra el local de origen de una de las cuotas de la serie.');
				}
			}
		}
		if ($permitirMotivoHistoricoEdicion
			&& (int)$gastoActualBloqueado['cod_motivoIngresoEgresoFK'] !== $codMotivoValidar) {
			throw new Exception('El concepto historico del movimiento cambio mientras se preparaba la edicion. Actualice la pantalla.');
		}
		if (empty($gastoPreliminarEdicion['cod_interConsultaFK'])
			&& !empty($gastoActualBloqueado['cod_interConsultaFK'])) {
			throw new Exception('El movimiento cambio de Hilo mientras se preparaba la edicion. Actualice la pantalla.');
		}

		// Se vuelve a resolver la serie despues de las cerraduras de presupuesto,
		// Hilo y raiz. Asi una segunda edicion ve los hijos creados por la primera.
		$serieEdicionActual= gastoResolverSerieParaEdicion($mysqli, $idGastoBloqueo, $editar_cuotas);
		$idsSerieEdicion= gastoDistribucionNormalizarIdsExcluir($serieEdicionActual['ids']);
		if (count($idsSerieEdicion) < 1) {
			$idsSerieEdicion= array($idGastoBloqueo);
		}
		$resultadoSerieBloqueada= $mysqli->query('SELECT idgastos,estado,cod_mensajeFK,cod_local FROM gastos WHERE idgastos IN ('.implode(',', $idsSerieEdicion).') ORDER BY idgastos FOR UPDATE');
		if (!$resultadoSerieBloqueada || $resultadoSerieBloqueada->num_rows != count($idsSerieEdicion)) {
			throw new Exception('La serie cambio mientras se preparaba la edicion. Actualice la pantalla.');
		}
		while ($filaSerieBloqueada= $resultadoSerieBloqueada->fetch_assoc()) {
			$gastosSerieBloqueados[]= $filaSerieBloqueada;
			$estadoFilaSerie= strtolower(trim((string)$filaSerieBloqueada['estado']));
			if ($editar_cuotas === 'true'
				&& ((int)$filaSerieBloqueada['cod_local'] !== (int)$gastoActualBloqueado['cod_local']
					|| !usuarioPuedeGestionarLocalGasto($cod_usuario, $filaSerieBloqueada['cod_local']))) {
				throw new Exception('La serie incluye una cuota de otro local de origen. Edite solamente la cuota seleccionada.');
			}
			if ((int)$filaSerieBloqueada['idgastos'] === $idGastoBloqueo
				|| ($editar_cuotas == 'true' && in_array($estadoFilaSerie, array('pendiente','solicitado'), true))) {
				$idsMutarSerieEdicion[]= (int)$filaSerieBloqueada['idgastos'];
			}
		}
		$idsMutarSerieEdicion= gastoDistribucionNormalizarIdsExcluir($idsMutarSerieEdicion);
		$idsExcluirPresupuesto= $idsMutarSerieEdicion;
		$idsUenoActuales= gastoUenoConsultarIdsActivos($mysqli, $idsMutarSerieEdicion, true);
		$tieneUenoActivo= in_array($idGastoBloqueo, $idsUenoActuales, true);
		$estado= gastoUenoPreservarEstadoEdicionIndividual($estado, $gastoActualBloqueado, $tieneUenoActivo, $editar_cuotas);
		if ($tieneUenoActivo && $editar_cuotas !== 'true') {
			// Una edicion individual conciliada solo puede cambiar descripcion/adjuntos.
			// Se conserva el estado real para que la normalizacion de autorizaciones no
			// transforme silenciosamente Activo en solicitado/pendiente.
			$cod_usuario_autoriz= isset($gastoActualBloqueado['cod_usuario_autoriz'])
				? $gastoActualBloqueado['cod_usuario_autoriz'] : NULL;
		}
		if ($editar_cuotas == 'true' && count($idsUenoActuales) > 0) {
			throw new Exception('La edicion afectaria una cuota conciliada con Ueno. Revierta primero la conciliacion bancaria.');
		}
		$datosFinancierosNuevos= array(
			'monto' => $monto, 'tipo' => $tipo, 'estado' => $estado, 'cod_local' => $cod_local,
			'fecha' => $fecha, 'banco' => $banco, 'nrocuenta' => $nrocuenta, 'nroboleta' => $nroboleta,
			'cod_motivoIngresoEgresoFK' => $cod_motivo, 'codCaja' => $codcaja,
			'codApertura' => $idaperturacierrecaja, 'cod_interConsultaFK' => $cod_interConsultaFK,
			'cod_proyecto_gastoFK' => $cod_proyecto_gastoFK,
		);
		if (!empty($distribucionGasto['conservar_legacy'])) {
			$distribucionLegacyActual= gastoDistribucionObtenerEfectiva($mysqli, $idGastoBloqueo, true);
			if (empty($distribucionLegacyActual['legacy_no_materializable'])) {
				throw new Exception('La distribucion historica cambio mientras se preparaba la edicion. Actualice la pantalla.');
			}
			gastoValidarConservacionDistribucionLegacy($gastoActualBloqueado, $datosFinancierosNuevos);
		}
		gastoUenoValidarEdicionFinanciera($gastoActualBloqueado, $datosFinancierosNuevos, $tieneUenoActivo);
	}

	// Se validan los consumos con el estado y el conjunto exacto de filas que se
	// actualizaran/inactivaran ya conocidos bajo current read.
	if (strtolower(trim((string)$tipo)) == 'egreso' && flujoGastoEstadoComputableResumen($estado)) {
		if ($fechaPresupuesto && !empty($distribucionGasto['asignaciones'])) {
			gastoDistribucionValidarPresupuestos($mysqli, $distribucionGasto, $cod_motivo, $fechaPresupuesto->format('Y-m-01'), $fechaPresupuesto->format('Y-m-t'), $idsExcluirPresupuesto, false);
		}
		if ($hiloBloqueoGasto > 0) {
			gastoValidarLimiteHiloBloqueado($mysqli, $hiloBloqueoGasto, $monto, $idsExcluirPresupuesto);
		}
	}
} catch (Exception $e) {
	$mysqli->rollback();
	$mysqli->close();
	return array('1' => 'error', '2' => $e->getMessage());
}

$transaccionDeposito= false;
if (esTipoDepositoCentral($tipo)) {
	$transaccionDeposito= true;
	$errorDepositoCentral= validarDepositoCentralEnCaja($idgastos, $operacion, $idaperturacierrecaja, $codcaja, $cod_local, trim((string)$nroboleta), $monto, $fecha, $estado, $mysqli, true);
	if ($errorDepositoCentral != '') {
		$mysqli->rollback();
		mysqli_close($mysqli);
		return array("1" => "error", "2" => $errorDepositoCentral);
	}
}

if($operacion=="nuevo")
{
if ($cod_proyecto_gastoFK == "0") {
	$cod_proyecto_gastoFK = NULL;
}

$consulta1="Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta,cod_motivoIngresoEgresoFK,cod_interConsultaFK,modalidad,cod_proyecto_gastoFK)
values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $mysqli->prepare($consulta1);
	if (!$stmt) {
		if ($transaccionGastoActiva) { $mysqli->rollback(); }
		$mensajeErrorGasto= $mysqli->error;
		$mysqli->close();
		return array('1' => 'error', '2' => 'No se pudo preparar el movimiento: '.$mensajeErrorGasto);
	}

$ss='ssssssssssssssssss';
$stmt->bind_param($ss,$Arreglo,$monto,$motivo,$fecha,$estado,$cod_usuario,$personales,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$nroboleta, $banco , $nrocuenta,$cod_motivo,$cod_interConsultaFK,$modalidad, $cod_proyecto_gastoFK);


}


if($operacion=="editar")
{
	if (!empty($distribucionGasto['asignaciones'])) {
		try {
			// Conserva la fotografia previa antes de modificar monto o local de pago.
			// Tambien bloquea el padre y sus asignaciones durante toda la edicion.
			$distribucionAnteriorEdicion= gastoDistribucionObtenerEfectiva($mysqli, $idgastos, true);
		} catch (Exception $e) {
			if ($transaccionGastoActiva) { $mysqli->rollback(); }
			$mysqli->close();
			return array('1' => 'error', '2' => $e->getMessage());
		}
	}

// La fila ya fue bloqueada y leida con la misma conexion/transaccion.
$datos_gasto= array($gastoActualBloqueado);
// `$estado` ya fue normalizado antes de adquirir/validar presupuesto e Hilo.

if ($estado == "Inactivo") {
	// La baja directa de gastos no esta dentro del flujo de solicitud de eliminacion permitido.
}

$parametros = array();
$atributos = "";
$ss = "";

if ($Arreglo != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "arreglo= ?";
	$ss .= "s";
	$parametros[] = $Arreglo;
}
if ($monto != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "monto= ?";
	$ss .= "s";
	$parametros[] = $monto;
}
if ($motivo != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "motivo= ?";
	$ss .= "s";
	$parametros[] = $motivo;
}
if ($fecha != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "fecha= ?";
	$ss .= "s";
	$parametros[] = $fecha;
}
if ($estado != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "estado= ?";
	$ss .= "s";
	$parametros[] = $estado;
}
if ($cod_usuario != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "cod_usuarioFK_edit= ?";
	$ss .= "s";
	$parametros[] = $cod_usuario;
}
if ($personales != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "personales= ?";
	$ss .= "s";
	$parametros[] = $personales;
}
if ($cod_local != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "cod_local= ?";
	$ss .= "s";
	$parametros[] = $cod_local;
}
if ($tipo != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "tipo= ?";
	$ss .= "s";
	$parametros[] = $tipo;
}
if ($nroboleta != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "nroboleta= ?";
	$ss .= "s";
	$parametros[] = $nroboleta;
}
if ($banco != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "banco= ?";
	$ss .= "s";
	$parametros[] = $banco;
}
if ($nrocuenta != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "nrocuenta= ?";
	$ss .= "s";
	$parametros[] = $nrocuenta;
}
if ($cod_motivo != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "cod_motivoIngresoEgresoFK= ?";
	$ss .= "s";
	$parametros[] = $cod_motivo;
}
if ($cod_interConsultaFK != NULL) {
	$atributos .= ($atributos == "" ? "" : ", ") . "cod_interConsultaFK= ?";
	$ss .= "s";
	$parametros[] = $cod_interConsultaFK;
}
if ($cod_proyecto_gastoFK != "") {
	if ($cod_proyecto_gastoFK == "0") {
		$cod_proyecto_gastoFK = NULL;
	}
	$atributos .= ($atributos == "" ? "" : ", ") . "cod_proyecto_gastoFK= ?";
	$ss .= "s";
	$parametros[] = $cod_proyecto_gastoFK;
}

if (!$mantener_estado_por_documento_firmado) {
	$atributos .= ($atributos == "" ? "" : ", ") . "cod_usuario_autoriz= ?";
	$ss .= "s";
	$parametros[] = $cod_usuario_autoriz;
}

if ($atributos == "") {
	if ($transaccionGastoActiva) {
		$mysqli->commit();
	}
	return array("1" => "exito", "2" => $idgastos);
}

$parametros[] = $idgastos;
$ss .= "i";

$consulta1="Update gastos set $atributos where idgastos=?";
$stmt = $mysqli->prepare($consulta1);
if (!$stmt) {
	if ($transaccionGastoActiva) { $mysqli->rollback(); }
	$mensajeErrorGasto= $mysqli->error;
	$mysqli->close();
	return array('1' => 'error', '2' => 'No se pudo preparar la actualizacion del movimiento: '.$mensajeErrorGasto);
}

$refs = [];
foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}
call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
}

if (!$stmt->execute()) {
	if ($transaccionGastoActiva) {
		$mysqli->rollback();
	}
	$mensajeErrorGasto= $stmt->error;
	$stmt->close();
	$mysqli->close();
	return array('1' => 'error', '2' => 'No se pudo guardar el movimiento: '.$mensajeErrorGasto);

}
$stmt->close();
$stmt= null;


if($operacion=='nuevo'){
	$idgastos = mysqli_insert_id($mysqli);
	try {
		if (!empty($distribucionGasto['asignaciones'])) {
			gastoDistribucionGuardar($mysqli, $idgastos, $distribucionGasto, $cod_usuario, ((int)$idMovimientoUenoGasto > 0 ? 'ueno' : 'flujo_financiero'), 'crear', true, array('modo' => '', 'asignaciones' => array()));
		}
		if ((int)$idMovimientoUenoGasto > 0) {
			gastoDistribucionVincularDebitoUeno($mysqli, $idMovimientoUenoGasto, $idgastos, $monto, $cod_usuario);
		}
	} catch (Exception $e) {
		if ($transaccionGastoActiva) { $mysqli->rollback(); }
		$mysqli->close();
		return array('1' => 'error', '2' => $e->getMessage());
	}
	if (intval($cantCuotas) > 1 && $periodicidad != "") {
		try {
			registrarCuotasRecurrentes($mysqli, $idgastos, $Arreglo, $cantCuotas, $periodicidad, $fecha, $monto, $motivo, $cod_usuario, $personales, $cod_local, $tipo, $codcaja, $idaperturacierrecaja, $nroboleta, $banco, $nrocuenta, $cod_motivo, $cod_interConsultaFK, $cod_proyecto_gastoFK, $distribucionGasto);
		} catch (Exception $e) {
			if ($transaccionGastoActiva) { $mysqli->rollback(); }
			$mysqli->close();
			return array('1' => 'error', '2' => $e->getMessage());
		}
	}
}

if ($operacion == 'editar' && !empty($distribucionGasto['asignaciones'])) {
	try {
		gastoDistribucionGuardar($mysqli, $idgastos, $distribucionGasto, $cod_usuario, 'flujo_financiero', 'editar', false, $distribucionAnteriorEdicion);
	} catch (Exception $e) {
		if ($transaccionGastoActiva) { $mysqli->rollback(); }
		$mysqli->close();
		return array('1' => 'error', '2' => $e->getMessage());
	}
}

if($operacion=='editar' && $editar_cuotas == "true" && empty($distribucionGasto['conservar_legacy']) && !esTipoDepositoCentral($tipo)){
	$codProyectoSerie= (is_numeric($cod_proyecto_gastoFK) && (int)$cod_proyecto_gastoFK > 0)
		? (int)$cod_proyecto_gastoFK : obtenerProyectoGastoSerie($mysqli, $idgastos, $cod_proyecto_gastoFK);
	$stmtRaizSerie= $mysqli->prepare('UPDATE gastos SET cod_gasto_padre=NULL WHERE idgastos=?');
	if (!$stmtRaizSerie) {
		$mysqli->rollback();
		$mysqli->close();
		return array('1' => 'error', '2' => 'No se pudo preparar la nueva raiz de la serie.');
	}
	$idRaizSerie= (int)$idgastos;
	$stmtRaizSerie->bind_param('i', $idRaizSerie);
	if (!$stmtRaizSerie->execute()) {
		$mensajeErrorGasto= $stmtRaizSerie->error;
		$stmtRaizSerie->close();
		$mysqli->rollback();
		$mysqli->close();
		return array('1' => 'error', '2' => 'No se pudo establecer la nueva raiz de la serie: '.$mensajeErrorGasto);
	}
	$stmtRaizSerie->close();
	if ($codProyectoSerie != '') {
		$sql = "UPDATE gastos SET cod_proyecto_gastoFK = ? WHERE idgastos = ?";
		$stmt = $mysqli->prepare($sql);
		if (!$stmt) {
			if ($transaccionGastoActiva) { $mysqli->rollback(); }
			$mensajeErrorGasto= $mysqli->error;
			$mysqli->close();
			return array('1' => 'error', '2' => 'No se pudo preparar la actualizacion del proyecto del gasto: '.$mensajeErrorGasto);
		}
		$stmt->bind_param('ii', $codProyectoSerie, $idgastos);
		if (!$stmt->execute()) {
			$mensajeErrorGasto= $stmt->error;
			$stmt->close();
			if ($transaccionGastoActiva) { $mysqli->rollback(); }
			$mysqli->close();
			return array('1' => 'error', '2' => 'No se pudo actualizar el proyecto del gasto: '.$mensajeErrorGasto);
		}
		$stmt->close();
		$stmt= null;

	}

	// La fotografia fue resuelta y bloqueada con current read antes de validar
	// presupuesto/Hilo. Reutilizarla evita operar sobre una lista obsoleta.
	$gastos_asociados= $gastosSerieBloqueados;
	$cantidadCuotasSerie= 0;
	$idsCuotasInactivar= array();
	foreach ($gastos_asociados as $value) {
		$estadoCuotaSerie= strtolower(trim((string)$value['estado']));
		if ($value['idgastos'] == $idgastos || $estadoCuotaSerie == 'pendiente' || $estadoCuotaSerie == 'solicitado') {
			$cantidadCuotasSerie++;
		}
		if ($value['idgastos'] != $idgastos && ($estadoCuotaSerie == 'pendiente' || $estadoCuotaSerie == 'solicitado')) {
			$idsCuotasInactivar[]= (int)$value['idgastos'];
		}
	}
	if (count($idsCuotasInactivar) > 0) {
		$listaInactivar= implode(',', $idsCuotasInactivar);
		$usuarioEditorSerie= (int)$cod_usuario;
		if (!$mysqli->query("UPDATE gastos SET estado='Inactivo',cod_usuarioFK_edit=$usuarioEditorSerie WHERE idgastos IN ($listaInactivar) AND LOWER(TRIM(estado)) IN ('pendiente','solicitado')")) {
			$mensajeErrorGasto= $mysqli->error;
			$mysqli->rollback();
			$mysqli->close();
			return array('1' => 'error', '2' => 'No se pudieron reemplazar las cuotas pendientes: '.$mensajeErrorGasto);
		}
		if (!$mysqli->query("UPDATE mensaje m INNER JOIN gastos g ON g.cod_mensajeFK=m.cod_mensaje SET m.estado='inactivo' WHERE g.idgastos IN ($listaInactivar)")) {
			$mensajeErrorGasto= $mysqli->error;
			$mysqli->rollback();
			$mysqli->close();
			return array('1' => 'error', '2' => 'No se pudieron desactivar los recordatorios reemplazados: '.$mensajeErrorGasto);
		}
	}

	if ($cantidadCuotasSerie > 1 && $estado != 'Inactivo') {
		try {
			registrarCuotasRecurrentes($mysqli, $idgastos, $Arreglo, $cantidadCuotasSerie, $periodicidad, $fecha, $monto, $motivo, $cod_usuario, $personales, $cod_local, $tipo, $codcaja, $idaperturacierrecaja, $nroboleta, $banco, $nrocuenta, $cod_motivo, $cod_interConsultaFK, $codProyectoSerie, $distribucionGasto);
		} catch (Exception $e) {
			if ($transaccionGastoActiva) { $mysqli->rollback(); }
			$mysqli->close();
			return array('1' => 'error', '2' => $e->getMessage());
		}
	}
	if (isset($stmt) && $stmt) {
		$stmt->close();
	}
	}

if ($operacion == 'editar' && !esTipoDepositoCentral($tipo)) {
	try {
		$resumenSolicitud= 'archivo: abmgasto.php | funcion: abmGasto | idgastos: '.(int)$idgastos
			.' | monto: '.$monto.' | motivo: '.$motivo.' | fecha: '.$fecha
			.' | estado: '.$estado.' | cod_local: '.$cod_local.' | tipo: '.$tipo;
		gastoRegistrarSolicitudEliminacionTransaccional(
			$mysqli,
			$idgastos,
			$cod_usuario,
			isset($gastoActualBloqueado['estado']) ? $gastoActualBloqueado['estado'] : '',
			$estado,
			$resumenSolicitud
		);
	} catch (Exception $e) {
		$mysqli->rollback();
		$mysqli->close();
		return array('1' => 'error', '2' => $e->getMessage());
	}
}

if ($transaccionGastoActiva) {
	if (!$mysqli->commit()) {
		$mysqli->rollback();
		$mensajeErrorGasto= $mysqli->error;
		$mysqli->close();
		return array('1' => 'error', '2' => 'No se pudo confirmar el movimiento financiero: '.$mensajeErrorGasto);
	}
	$transaccionGastoActiva= false;
	$transaccionHiloGasto= false;
	$transaccionDeposito= false;
}

$foto=$_POST['foto'];
$ext=$_POST['ext'];
$archivoPrincipalOk= subirImagenGasto($idgastos, $foto, $ext);
$foto_documento_firmado= isset($_POST['foto_documento_firmado']) ? $_POST['foto_documento_firmado'] : '';
$ext_documento_firmado= isset($_POST['ext_documento_firmado']) ? $_POST['ext_documento_firmado'] : '';
$archivoFirmadoOk= subirDocumentoFirmadoGasto($idgastos, $foto_documento_firmado, $ext_documento_firmado);

if($operacion=="editar")
{
	// Obtiene los datos actuales del gasto
	$datos_gasto_nuevo= buscarGasto('', '', '', '', '', '', '', '', 'false', '', '', '', '','',$idgastos)[0];

	// Compara los datos anteriores con los nuevos y prepara el mensaje
	$mensaje= "";
	foreach ($datos_gasto[0] as $key => $value) {
		if ($datos_gasto_nuevo[$key] != $value) {
			$mensaje .= ", el campo $key cambió de '".$value."' a '".$datos_gasto_nuevo[$key]."'";
		}
	}
	if ($mensaje && $datos_gasto_nuevo['cod_interConsultaFK']) {
		$fechaActual = new DateTime();
		$mensaje= "@{". $datos_gasto_nuevo['cod_usuarioFK_edit'] ."} modifico ". substr($mensaje, 2) . " en el movimiento con descripcion $motivo.";
		$mensaje = mb_convert_encoding($mensaje, 'ISO-8859-1', 'UTF-8');
		abmMensaje("", $mensaje, $fechaActual->format('Y-m-d H:i:s'), $datos_gasto_nuevo['cod_interConsultaFK'], "", NULL, TRUE);
	}
} else {
	// Si es nuevo, se registra la creación
	if (!empty($cod_interConsultaFK)) {
		$fechaActual = new DateTime();
		$mensaje= " @{".$cod_usuario."} creo un nuevo movimiento con descripcion ".$motivo.".";
		$mensaje = mb_convert_encoding($mensaje, 'ISO-8859-1', 'UTF-8');
		abmMensaje("", $mensaje, $fechaActual->format('Y-m-d H:i:s'), $cod_interConsultaFK, "", NULL, TRUE);
	}
}
$resultadoGasto= array("1" => "exito", "2" => $idgastos);
if (!$archivoPrincipalOk || !$archivoFirmadoOk) {
	$resultadoGasto['parcial']= 1;
	$resultadoGasto['archivo']= array(
		'ok' => false,
		'principal_ok' => $archivoPrincipalOk ? 1 : 0,
		'firmado_ok' => $archivoFirmadoOk ? 1 : 0,
		'mensaje' => !$archivoPrincipalOk
			? 'El movimiento se guardo, pero no se pudo conservar el comprobante principal. Reabra el movimiento y vuelva a adjuntarlo.'
			: 'El movimiento y su comprobante principal se guardaron, pero falta volver a adjuntar el documento firmado.'
	);
}
return $resultadoGasto;
}

function buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder= 'DESC', $cod_proyecto_gastoFK= '', $usarDistribucionLocal= false) {
	$registros= array();
	$mysqli=conectar_al_servidor();
	$filtroInvalido= false;
	$normalizarIdFiltro= function($valor, $permitirNull = false) use (&$filtroInvalido) {
		$valor= trim((string)$valor);
		if ($valor === '') {
			return '';
		}
		if ($permitirNull && strtoupper($valor) === 'NULL') {
			return 'NULL';
		}
		if (!preg_match('/^[0-9]+$/', $valor)) {
			$filtroInvalido= true;
			return 0;
		}
		return (int)$valor;
	};
	$validarFechaFiltro= function($valor) use (&$filtroInvalido) {
		$valor= trim((string)$valor);
		if ($valor === '') {
			return '';
		}
		$fechaFiltro= DateTime::createFromFormat('!Y-m-d', $valor);
		if (!$fechaFiltro || $fechaFiltro->format('Y-m-d') !== $valor) {
			$filtroInvalido= true;
			return '';
		}
		return $valor;
	};
	$cod_local= $normalizarIdFiltro($cod_local);
	$cod_motivoFK= $normalizarIdFiltro($cod_motivoFK);
	$cod_interConsultaFK= $normalizarIdFiltro($cod_interConsultaFK);
	$idgastos= $normalizarIdFiltro($idgastos);
	$cod_gasto_padre= $normalizarIdFiltro($cod_gasto_padre, true);
	$fecha1= $validarFechaFiltro($fecha1);
	$fecha2= $validarFechaFiltro($fecha2);
	$fecha= $validarFechaFiltro($fecha);
	$arreglo= $mysqli->real_escape_string((string)$arreglo);
	$estado= $mysqli->real_escape_string((string)$estado);
	$tipo= $mysqli->real_escape_string((string)$tipo);
	$usuario= $mysqli->real_escape_string((string)$usuario);
	$nombre_interConsulta= $mysqli->real_escape_string((string)$nombre_interConsulta);
	$motivo= $mysqli->real_escape_string((string)$motivo);
	$codLocalDistribucion= (int)$cod_local;
	$usarDistribucionLocal= $usarDistribucionLocal
		&& $codLocalDistribucion > 0
		&& in_array($codLocalDistribucion, gastoDistribucionLocalesGraficos(), true)
		&& gastoDistribucionTablaDisponible($mysqli);
	if ($cod_proyecto_gastoFK == "" && is_numeric($fechaOrder)) {
		$cod_proyecto_gastoFK= $fechaOrder;
		$fechaOrder= 'DESC';
	}
	$fechaOrder= strtoupper((string)$fechaOrder);
	if ($fechaOrder != 'ASC' && $fechaOrder != 'DESC') {
		$fechaOrder= 'DESC';
	}
	$cod_proyecto_gastoFK= $normalizarIdFiltro($cod_proyecto_gastoFK);

	$sqlFiltro= '';
	if($cod_local != ""){
		if ($usarDistribucionLocal) {
			$sqlFiltro .= " and ((LOWER(TRIM(IFNULL(g.tipo,'')))!='egreso' AND g.cod_local='$codLocalDistribucion') OR (LOWER(TRIM(IFNULL(g.tipo,'')))='egreso' AND (EXISTS (SELECT 1 FROM gasto_distribucion_local dlf WHERE dlf.idgastosFK=g.idgastos AND dlf.cod_localFK='$codLocalDistribucion') OR (g.cod_local='$codLocalDistribucion' AND NOT EXISTS (SELECT 1 FROM gasto_distribucion_local dlx WHERE dlx.idgastosFK=g.idgastos)))))";
		} else {
			$sqlFiltro .= " and g.cod_local=$cod_local";
		}
	}
	if($tipo!=""){
		$sqlFiltro .= " and g.tipo='$tipo'";
	}
	if($arreglo!=""){
		$sqlFiltro .=" and g.arreglo='$arreglo'";
	}
	if($fecha!=""){
		$sqlFiltro .=" and g.fecha='$fecha'";
	}
	if($usuario!=""){
		$sqlFiltro .=" and (Select nombre_persona from persona where cod_persona=g.cod_usuario) like '%".$usuario."%'";
	}
	if($fecha1!="" && $fecha2!="" ){
		$sqlFiltro .=" and g.fecha>='$fecha1' and g.fecha<='$fecha2'"; 
	}
	if ($cod_motivoFK != "") {
		$sqlFiltro .= " and g.cod_motivoIngresoEgresoFK = $cod_motivoFK";
	}
	if ($ocultar_inactivos == "true") {
		$sqlFiltro .= " and g.estado != 'Inactivo'";
	}
	if ($estado != "") {
		$sqlFiltro .= " and g.estado='$estado'";
	}
	if ($cod_interConsultaFK != "") {
		$sqlFiltro .= " and g.cod_interConsultaFK= $cod_interConsultaFK ";
	}
	if ($nombre_interConsulta != "") {
		$sqlFiltro .= " and (Select asunto from interconsulta where cod_interConsulta=g.cod_interConsultaFK) like '%".$nombre_interConsulta."%'";
	}
	if ($idgastos != "") {
		$sqlFiltro .= " and g.idgastos= $idgastos ";
	}
	if ($motivo != "") {
		$sqlFiltro .= " and g.motivo like '%$motivo%' ";
	}
	if ($cod_gasto_padre != "") {
		$sqlFiltro .= " and g.cod_gasto_padre " .($cod_gasto_padre == "NULL" ? 'IS NULL' : "= $cod_gasto_padre");
	}
	if ($cod_proyecto_gastoFK != "") {
		$sqlFiltro .= " and g.cod_proyecto_gastoFK = $cod_proyecto_gastoFK";
	}
	if ($filtroInvalido) {
		$sqlFiltro .= " and 1=0";
	}

	// Se limpia el primer ' and'
	if (strlen($sqlFiltro) > 0) {
		$sqlFiltro = "where" . substr($sqlFiltro, 4, strlen($sqlFiltro));
	}
		
	$camposDistribucion= $usarDistribucionLocal
		? ", g.monto AS monto_total_padre, IFNULL((SELECT dls.monto_asignado FROM gasto_distribucion_local dls WHERE dls.idgastosFK=g.idgastos AND dls.cod_localFK='$codLocalDistribucion' LIMIT 1),g.monto) AS monto_visible, EXISTS (SELECT 1 FROM gasto_distribucion_local dle WHERE dle.idgastosFK=g.idgastos) AS tiene_distribucion_local, (SELECT Nombre FROM local ld WHERE ld.cod_local='$codLocalDistribucion') AS nombrelocal_destino"
		: ", g.monto AS monto_total_padre, g.monto AS monto_visible, 0 AS tiene_distribucion_local, '' AS nombrelocal_destino";
	$sql= "Select g.arreglo,g.monto,g.motivo as descripcion,g.fecha,g.estado,g.cod_usuario,g.idgastos,g.tipo,g.cod_proyecto_gastoFK,
	g.cod_local,g.nroboleta,g.banco,g.nrocuenta,g.url1,g.url_documento_firmado,g.cod_interConsultaFK,g.modalidad,g.codCaja,g.codApertura,
	g.cod_usuario_autoriz, g.fecha_autoriz, g.cod_motivoIngresoEgresoFK, g.cod_usuarioFK_edit,g.cod_gasto_padre,g.cod_mensajeFK,
	(Select asunto from interconsulta where cod_interConsulta=g.cod_interConsultaFK) as interconsulta_nombre,
	(Select nombre_persona from persona where cod_persona=g.cod_usuario) as usuarionombre,
	(Select nombre_persona from persona where cod_persona=g.cod_usuarioFK_edit) as nombre_usuario_edit,
	(Select nombre_persona from persona where cod_persona=g.cod_usuario_autoriz) as usuario_autoriz_nombre,
	m.descripcion AS motivo, m.categoria, m.estado AS estado_motivo,
	(Select Nombre from local l where l.cod_local=g.cod_local) as nombrelocal $camposDistribucion
	from gastos g left join motivos_ingreso_egreso m on m.cod_motivo_ingreso_egreso = g.cod_motivoIngresoEgresoFK $sqlFiltro ORDER BY 
	FIELD(m.categoria,'','ingreso','directo','operativo'), necesita_autorizacion DESC, g.fecha $fechaOrder, g.idgastos DESC";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		if ($stmt) {
			$stmt->close();
		}
		mysqli_close($mysqli);
		return array();
	}

	$result = $stmt->get_result();
	$valor= mysqli_num_rows($result);	
	if ($valor>0) {
		while ($valor= mysqli_fetch_assoc($result)) {
			$esAsignacionLocal= $usarDistribucionLocal && !empty($valor['tiene_distribucion_local']);
			$montoVisible= $esAsignacionLocal ? $valor['monto_visible'] : $valor['monto'];
			$descripcionVisible= $valor['descripcion'];
			$categoriaVisible= $valor['categoria'];
			if ($esAsignacionLocal) {
				if ((int)$valor['cod_local'] === 1) {
					$categoriaVisible= 'administracion';
				}
				if ((int)$valor['cod_local'] !== $codLocalDistribucion || (int)round($valor['monto_visible']) !== (int)round($valor['monto_total_padre'])) {
					$descripcionVisible .= ' | Asignacion contable desde '.(string)$valor['nombrelocal'].' (gasto total '.number_format((int)round($valor['monto_total_padre']), 0, ',', '.').' Gs.)';
				}
			}
			$registros[] = array(
				'idgastos' =>mb_convert_encoding((string)($valor['idgastos']), 'UTF-8', 'ISO-8859-1'),
				'interconsulta_nombre' => mb_convert_encoding((string)($valor['interconsulta_nombre']), 'UTF-8', 'ISO-8859-1'),
				'cod_interConsultaFK' => mb_convert_encoding((string)($valor['cod_interConsultaFK']), 'UTF-8', 'ISO-8859-1'),
				'usuarionombre' => mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1'),
				'monto' => mb_convert_encoding((string)($montoVisible), 'UTF-8', 'ISO-8859-1'),
				'monto_total_padre' => mb_convert_encoding((string)($valor['monto_total_padre']), 'UTF-8', 'ISO-8859-1'),
				'motivo' => mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1'),
				'descripcion' => mb_convert_encoding((string)($descripcionVisible), 'UTF-8', 'ISO-8859-1'),
				'descripcion_original' => mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1'),
				'fecha' => mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1'),
				'tipo' => mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'),
				'estado' => mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'),
				'cod_local' => mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1'),
				'cod_usuario' => mb_convert_encoding((string)($valor['cod_usuario']), 'UTF-8', 'ISO-8859-1'),
				'codCaja' => mb_convert_encoding((string)($valor['codCaja']), 'UTF-8', 'ISO-8859-1'),
				'codApertura' => mb_convert_encoding((string)($valor['codApertura']), 'UTF-8', 'ISO-8859-1'),
				'nombrelocal' => mb_convert_encoding((string)($esAsignacionLocal ? $valor['nombrelocal_destino'] : $valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'),
				'nombrelocal_origen' => mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'),
				'nroboleta' => mb_convert_encoding((string)($valor['nroboleta']), 'UTF-8', 'ISO-8859-1'),
				'banco' => mb_convert_encoding((string)($valor['banco']), 'UTF-8', 'ISO-8859-1'),
				'nrocuenta' => mb_convert_encoding((string)($valor['nrocuenta']), 'UTF-8', 'ISO-8859-1'),
				'arreglo' => mb_convert_encoding((string)($valor['arreglo']), 'UTF-8', 'ISO-8859-1'),
				'url1' => mb_convert_encoding((string)($valor['url1']), 'UTF-8', 'ISO-8859-1'),
				'url_documento_firmado' => mb_convert_encoding((string)($valor['url_documento_firmado']), 'UTF-8', 'ISO-8859-1'),
				'categoria' => mb_convert_encoding((string)($categoriaVisible), 'UTF-8', 'ISO-8859-1'),
				'estado_motivo' => mb_convert_encoding((string)($valor['estado_motivo']), 'UTF-8', 'ISO-8859-1'),
				'es_asignacion_multilocal' => $esAsignacionLocal,
				'cod_local_destino_distribucion' => $esAsignacionLocal ? (string)$codLocalDistribucion : '',
				'cod_usuario_autoriz' => mb_convert_encoding((string)($valor['cod_usuario_autoriz']), 'UTF-8', 'ISO-8859-1'),
				'fecha_autoriz' => mb_convert_encoding((string)($valor['fecha_autoriz']), 'UTF-8', 'ISO-8859-1'),
				'usuario_autoriz_nombre' => mb_convert_encoding((string)($valor['usuario_autoriz_nombre']), 'UTF-8', 'ISO-8859-1'),
				'cod_motivoIngresoEgresoFK' => mb_convert_encoding((string)($valor['cod_motivoIngresoEgresoFK']), 'UTF-8', 'ISO-8859-1'),
				'nombre_usuario_edit' => mb_convert_encoding((string)($valor['nombre_usuario_edit']), 'UTF-8', 'ISO-8859-1'),
				'cod_usuarioFK_edit' => mb_convert_encoding((string)($valor['cod_usuarioFK_edit']), 'UTF-8', 'ISO-8859-1'),
				'modalidad' => mb_convert_encoding((string)($valor['modalidad']), 'UTF-8', 'ISO-8859-1'),
				'cod_gasto_padre' => mb_convert_encoding((string)($valor['cod_gasto_padre']), 'UTF-8', 'ISO-8859-1'),
				'cod_proyecto_gastoFK' => mb_convert_encoding((string)($valor['cod_proyecto_gastoFK']), 'UTF-8', 'ISO-8859-1'),
				'cod_mensajeFK' => mb_convert_encoding((string)($valor['cod_mensajeFK']), 'UTF-8', 'ISO-8859-1'),
			);
		}
	}

	return $registros;
}

function flujoGastoTextoSeguro($valor) {
	return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function flujoGastoTextoResumen($valor) {
	$texto= (string)$valor;
	if ($texto == '') {
		return '';
	}
	return mb_check_encoding($texto, 'UTF-8') ? $texto : mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
}

function flujoGastoNormalizarCategoriaResumen($categoria) {
	$categoria= trim((string)$categoria);
	if ($categoria == '' || strtoupper($categoria) == 'NULL') {
		return 'sinCategoria';
	}
	if ($categoria == 'ingreso' || $categoria == 'directo' || $categoria == 'operativo' || $categoria == 'administracion' || $categoria == 'deposito') {
		return $categoria;
	}
	return 'sinCategoria';
}

function flujoGastoTituloCategoriaResumen($categoria) {
	switch (flujoGastoNormalizarCategoriaResumen($categoria)) {
		case 'ingreso':
			return 'Ingresos';
		case 'directo':
			return 'Costos variables';
		case 'operativo':
			return 'Gastos fijos';
		case 'administracion':
			return 'Administracion asignada';
		case 'deposito':
			return 'Depositos a central';
		default:
			return 'Sin categorizar';
	}
}

function flujoGastoCrearResumenComposicion() {
	$resumen= array(
		'totales' => array(
			'ingresos' => 0,
			'costos_variables' => 0,
			'gastos_fijos' => 0,
			'administracion_asignada' => 0,
			'sin_categorizar' => 0,
			'egresos' => 0,
			'resultado' => 0,
		),
		'categorias' => array(),
		'administracion_compartida' => null,
	);
	foreach (array('ingreso', 'directo', 'operativo', 'administracion', 'sinCategoria') as $categoria) {
		$resumen['categorias'][$categoria]= array(
			'codigo' => $categoria,
			'titulo' => flujoGastoTituloCategoriaResumen($categoria),
			'total' => 0,
			'conceptos' => array(),
		);
	}
	return $resumen;
}

function flujoGastoAsegurarConceptoResumen(&$resumen, $categoria, $codMotivo, $nombreConcepto) {
	$categoria= flujoGastoNormalizarCategoriaResumen($categoria);
	$codMotivo= trim((string)$codMotivo);
	if ($codMotivo == '') {
		$codMotivo= 'sin_codigo';
	}
	if (!isset($resumen['categorias'][$categoria])) {
		$resumen['categorias'][$categoria]= array(
			'codigo' => $categoria,
			'titulo' => flujoGastoTituloCategoriaResumen($categoria),
			'total' => 0,
			'conceptos' => array(),
		);
	}
	if (!isset($resumen['categorias'][$categoria]['conceptos'][$codMotivo])) {
		$resumen['categorias'][$categoria]['conceptos'][$codMotivo]= array(
			'codigo' => $codMotivo,
			'nombre' => flujoGastoTextoResumen($nombreConcepto),
			'total' => 0,
			'movimientos' => array(),
		);
	}
}

function flujoGastoAgregarMovimientoResumen(&$resumen, $categoria, $codMotivo, $nombreConcepto, $movimiento) {
	$categoria= flujoGastoNormalizarCategoriaResumen($categoria);
	$codMotivo= trim((string)$codMotivo);
	if ($codMotivo == '') {
		$codMotivo= 'sin_codigo';
	}
	flujoGastoAsegurarConceptoResumen($resumen, $categoria, $codMotivo, $nombreConcepto);
	$estado= isset($movimiento['estado']) ? flujoGastoTextoResumen($movimiento['estado']) : '';
	$monto= intval(isset($movimiento['monto']) ? $movimiento['monto'] : 0);
	$montoComputable= flujoGastoEstadoComputableResumen($estado) ? $monto : 0;
	$resumen['categorias'][$categoria]['conceptos'][$codMotivo]['total'] += $montoComputable;
	$resumen['categorias'][$categoria]['conceptos'][$codMotivo]['movimientos'][]= array(
		'id' => flujoGastoTextoResumen(isset($movimiento['idgastos']) ? $movimiento['idgastos'] : ''),
		'fecha' => flujoGastoTextoResumen(isset($movimiento['fecha']) ? $movimiento['fecha'] : ''),
		'descripcion' => flujoGastoTextoResumen(isset($movimiento['descripcion']) ? $movimiento['descripcion'] : ''),
		'estado' => $estado,
		'tipo' => flujoGastoTextoResumen(isset($movimiento['tipo']) ? $movimiento['tipo'] : ''),
		'monto' => $monto,
		'monto_computable' => $montoComputable,
		'usuario' => flujoGastoTextoResumen(isset($movimiento['usuarionombre']) ? $movimiento['usuarionombre'] : ''),
		'local' => flujoGastoTextoResumen(isset($movimiento['nombrelocal']) ? $movimiento['nombrelocal'] : ''),
		'interconsulta' => flujoGastoTextoResumen(isset($movimiento['interconsulta_nombre']) ? $movimiento['interconsulta_nombre'] : ''),
	);
}

function flujoGastoFinalizarResumenComposicion($resumen, $ingresos, $costosVariables, $gastosFijos, $sinCategorizar, $administracionAsignada= 0, $administracionCompartida= null) {
	$ingresos= intval($ingresos);
	$costosVariables= intval($costosVariables);
	$gastosFijos= intval($gastosFijos);
	$sinCategorizar= intval($sinCategorizar);
	$administracionAsignada= intval($administracionAsignada);
	$egresos= $costosVariables + $gastosFijos + $administracionAsignada + $sinCategorizar;
	$resumen['totales']= array(
		'ingresos' => $ingresos,
		'costos_variables' => $costosVariables,
		'gastos_fijos' => $gastosFijos,
		'administracion_asignada' => $administracionAsignada,
		'sin_categorizar' => $sinCategorizar,
		'egresos' => $egresos,
		'resultado' => $ingresos - $egresos,
	);
	if (isset($resumen['categorias']['ingreso'])) {
		$resumen['categorias']['ingreso']['total']= $ingresos;
	}
	if (isset($resumen['categorias']['directo'])) {
		$resumen['categorias']['directo']['total']= $costosVariables;
	}
	if (isset($resumen['categorias']['operativo'])) {
		$resumen['categorias']['operativo']['total']= $gastosFijos;
	}
	if (isset($resumen['categorias']['administracion'])) {
		$resumen['categorias']['administracion']['total']= $administracionAsignada;
	}
	if (isset($resumen['categorias']['sinCategoria'])) {
		$resumen['categorias']['sinCategoria']['total']= $sinCategorizar;
	}
	$resumen['administracion_compartida']= $administracionCompartida;
	$categoriasOrdenadas= array();
	foreach (array('ingreso', 'directo', 'operativo', 'administracion', 'sinCategoria') as $categoria) {
		if (!isset($resumen['categorias'][$categoria])) {
			continue;
		}
		$datosCategoria= $resumen['categorias'][$categoria];
		$datosCategoria['conceptos']= array_values($datosCategoria['conceptos']);
		$categoriasOrdenadas[]= $datosCategoria;
	}
	$resumen['categorias']= $categoriasOrdenadas;
	return $resumen;
}

function flujoGastoEstadoComputableResumen($estado) {
	$estado= strtolower(trim((string)$estado));
	return ($estado == 'activo' || $estado == 'pendiente' || $estado == 'solicitado');
}

function flujoGastoLocalAdministracionCompartida() {
	return array(
		'codigo' => '1',
		'nombre' => 'CLINIDENT (ADMINISTRACION) COMPARTIDOS',
	);
}

function flujoGastoLocalesAdministracionDestino() {
	return array(
		'3' => 'CLINIDENT CERRO CORA (VILLARRICA)',
		'5' => 'CLINIDENT VILLA INDUSTRIAL (SAN LORENZO)',
		'6' => 'CLINIDENT PADRE MOLAS (OVIEDO)',
		'7' => 'CLINIDENT SANTA LIBRADA (VILLARRICA)',
		'9' => 'CLINIDENT VILLA MORRA',
	);
}

function flujoGastoEsLocalAdministracionCompartida($codLocal) {
	$origen= flujoGastoLocalAdministracionCompartida();
	return trim((string)$codLocal) == $origen['codigo'];
}

function flujoGastoEsLocalDestinoAdministracion($codLocal) {
	$codLocal= trim((string)$codLocal);
	$locales= flujoGastoLocalesAdministracionDestino();
	return isset($locales[$codLocal]);
}

function flujoGastoFiltrosPermitenAdministracionCompartida($arreglo, $tipo, $usuario, $fecha, $cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos) {
	$tipo= strtolower(trim((string)$tipo));
	if ($tipo != '' && $tipo != 'egreso') {
		return false;
	}
	$filtrosEspecificos= array($arreglo, $usuario, $fecha, $cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos);
	foreach ($filtrosEspecificos as $filtro) {
		if (trim((string)$filtro) != '') {
			return false;
		}
	}
	return true;
}

function flujoGastoCrearInfoAdministracionCompartida($codLocalSeleccionado) {
	$localesDestino= flujoGastoLocalesAdministracionDestino();
	$distribuciones= array();
	foreach ($localesDestino as $codigo => $nombre) {
		$distribuciones[]= array(
			'codigo' => $codigo,
			'nombre' => $nombre,
			'monto' => 0,
			'es_local_seleccionado' => trim((string)$codLocalSeleccionado) == $codigo,
		);
	}
	return array(
		'aplica' => false,
		'modo' => 'sin_asignacion',
		'local_origen' => flujoGastoLocalAdministracionCompartida(),
		'local_destino' => null,
		'cantidad_locales' => count($localesDestino),
		'total_origen' => 0,
		'monto_asignado' => 0,
		'distribuciones' => $distribuciones,
	);
}

function flujoGastoDistribuirMontoAdministracion($monto) {
	$monto= intval($monto);
	$locales= flujoGastoLocalesAdministracionDestino();
	$cantidad= count($locales);
	$distribucion= array();
	if ($cantidad <= 0) {
		return $distribucion;
	}
	$base= intdiv($monto, $cantidad);
	$residuo= $monto % $cantidad;
	$indice= 0;
	foreach ($locales as $codigo => $nombre) {
		$distribucion[$codigo]= $base + ($indice < $residuo ? 1 : 0);
		$indice++;
	}
	return $distribucion;
}

function flujoGastoPrepararMovimientoAdministracionAsignada($gasto, $montoAsignado, $codLocalDestino, $nombreLocalDestino) {
	$movimiento= $gasto;
	$montoOrigen= intval(isset($gasto['monto']) ? $gasto['monto'] : 0);
	$descripcion= trim((string)(isset($gasto['descripcion']) ? $gasto['descripcion'] : ''));
	if ($descripcion == '') {
		$descripcion= trim((string)(isset($gasto['motivo']) ? $gasto['motivo'] : 'Gasto administrativo'));
	}
	$movimiento['monto']= intval($montoAsignado);
	$movimiento['tipo']= 'Egreso';
	$movimiento['categoria']= 'administracion';
	$movimiento['nombrelocal']= $nombreLocalDestino;
	$movimiento['descripcion']= $descripcion.' | Asignacion administrativa 1/'.count(flujoGastoLocalesAdministracionDestino()).' desde '.flujoGastoLocalAdministracionCompartida()['nombre'].' (origen '.number_format($montoOrigen, 0, ',', '.').' Gs.)';
	$movimiento['es_asignacion_administrativa']= true;
	$movimiento['monto_origen_administrativo']= $montoOrigen;
	$movimiento['cod_local_origen_administrativo']= flujoGastoLocalAdministracionCompartida()['codigo'];
	$movimiento['cod_local_destino_administrativo']= $codLocalDestino;
	return $movimiento;
}

function flujoGastoCalcularAdministracionCompartida($fecha1, $fecha2, $estado, $codLocalSeleccionado, $tipo, $ocultar_inactivos, $fechaOrder) {
	$info= flujoGastoCrearInfoAdministracionCompartida($codLocalSeleccionado);
	if (!flujoGastoEsLocalDestinoAdministracion($codLocalSeleccionado) && !flujoGastoEsLocalAdministracionCompartida($codLocalSeleccionado)) {
		return $info;
	}

	$localesDestino= flujoGastoLocalesAdministracionDestino();
	$origen= flujoGastoLocalAdministracionCompartida();
	$registros= buscarGasto('', $fecha1, $fecha2, $estado, $origen['codigo'], 'Egreso', '', '', $ocultar_inactivos, '', '', '', '', '', '', $fechaOrder);
	$totalOrigen= 0;
	$gastosElegibles= array();
	$movimientosAsignados= array();
	$gastosConDistribucionPersistida= array();
	$idsAdministracion= array();
	foreach ($registros as $registroAdministracion) {
		if (!empty($registroAdministracion['idgastos'])) {
			$idsAdministracion[]= (int)$registroAdministracion['idgastos'];
		}
	}
	if (count($idsAdministracion) > 0) {
		$mysqliDistribucionAdministracion= conectar_al_servidor();
		if (gastoDistribucionTablaDisponible($mysqliDistribucionAdministracion)) {
			$idsSqlAdministracion= implode(',', array_unique($idsAdministracion));
			$resultadoDistribucionAdministracion= $mysqliDistribucionAdministracion->query("SELECT DISTINCT idgastosFK FROM gasto_distribucion_local WHERE idgastosFK IN ($idsSqlAdministracion)");
			if ($resultadoDistribucionAdministracion) {
				while ($filaDistribucionAdministracion= $resultadoDistribucionAdministracion->fetch_assoc()) {
					$gastosConDistribucionPersistida[(int)$filaDistribucionAdministracion['idgastosFK']]= true;
				}
			}
		}
		$mysqliDistribucionAdministracion->close();
	}

	foreach ($registros as $gasto) {
		if (isset($gastosConDistribucionPersistida[(int)$gasto['idgastos']])) {
			continue;
		}
		$estadoGasto= isset($gasto['estado']) ? $gasto['estado'] : '';
		if (!flujoGastoEstadoComputableResumen($estadoGasto)) {
			continue;
		}
		$monto= intval(isset($gasto['monto']) ? $gasto['monto'] : 0);
		if ($monto <= 0) {
			continue;
		}
		$categoria= flujoGastoNormalizarCategoriaResumen(isset($gasto['categoria']) ? $gasto['categoria'] : '');
		if ($categoria == 'ingreso') {
			continue;
		}
		$totalOrigen += $monto;
		$gastosElegibles[]= $gasto;
	}

	$totalesPorLocal= array();
	foreach ($localesDestino as $codigoLocalDestino => $nombreLocalDestino) {
		$totalesPorLocal[$codigoLocalDestino]= 0;
	}
	foreach ($gastosElegibles as $gastoElegible) {
		$montoGastoElegible= intval(isset($gastoElegible['monto']) ? $gastoElegible['monto'] : 0);
		$distribucionRegistro= flujoGastoDistribuirMontoAdministracion($montoGastoElegible);
		foreach ($totalesPorLocal as $codigoLocalDestino => $montoAcumulado) {
			$totalesPorLocal[$codigoLocalDestino] += isset($distribucionRegistro[$codigoLocalDestino])
				? intval($distribucionRegistro[$codigoLocalDestino]) : 0;
		}
	}
	if (flujoGastoEsLocalDestinoAdministracion($codLocalSeleccionado) && isset($totalesPorLocal[$codLocalSeleccionado])) {
		foreach ($gastosElegibles as $gastoElegible) {
			$montoGasto= intval(isset($gastoElegible['monto']) ? $gastoElegible['monto'] : 0);
			$distribucionRegistro= flujoGastoDistribuirMontoAdministracion($montoGasto);
			$montoAsignadoMovimiento= isset($distribucionRegistro[$codLocalSeleccionado])
				? intval($distribucionRegistro[$codLocalSeleccionado]) : 0;
			if ($montoAsignadoMovimiento > 0) {
				$movimientosAsignados[]= flujoGastoPrepararMovimientoAdministracionAsignada($gastoElegible, $montoAsignadoMovimiento, $codLocalSeleccionado, $localesDestino[$codLocalSeleccionado]);
			}
		}
	}

	$distribuciones= array();
	foreach ($localesDestino as $codigo => $nombre) {
		$distribuciones[]= array(
			'codigo' => $codigo,
			'nombre' => $nombre,
			'monto' => isset($totalesPorLocal[$codigo]) ? $totalesPorLocal[$codigo] : 0,
			'es_local_seleccionado' => trim((string)$codLocalSeleccionado) == $codigo,
		);
	}
	$info['aplica']= true;
	$info['modo']= flujoGastoEsLocalAdministracionCompartida($codLocalSeleccionado) ? 'origen' : 'asignado';
	$info['total_origen']= $totalOrigen;
	$info['monto_asignado']= flujoGastoEsLocalDestinoAdministracion($codLocalSeleccionado) && isset($totalesPorLocal[$codLocalSeleccionado]) ? $totalesPorLocal[$codLocalSeleccionado] : 0;
	$info['distribuciones']= $distribuciones;
	if (flujoGastoEsLocalDestinoAdministracion($codLocalSeleccionado)) {
		$info['local_destino']= array(
			'codigo' => $codLocalSeleccionado,
			'nombre' => $localesDestino[$codLocalSeleccionado],
		);
	}
	$info['movimientos_asignados']= $movimientosAsignados;
	return $info;
}

function flujoGastoEstaAnulado($gasto) {
	$estado= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
	return ($estado == 'rechazado' || $estado == 'inactivo' || $estado == 'baja');
}

function flujoGastoTablaExiste($mysqli, $tabla) {
	$tabla= $mysqli->real_escape_string($tabla);
	$result= $mysqli->query("SHOW TABLES LIKE '$tabla'");
	return $result && $result->num_rows > 0;
}

function flujoGastoResumenConciliacionUeno($idgastos, $montoTotal) {
	static $mysqliConciliacion= null;
	static $tablaDisponible= null;
	$resumen= array(
		'disponible' => false,
		'monto_total' => intval($montoTotal),
		'conciliado' => 0,
		'pendiente' => intval($montoTotal),
		'estado' => 'sin-conciliar',
		'texto' => 'Sin conciliar',
		'asignaciones' => 0,
	);
	if ($idgastos == "" || intval($idgastos) <= 0 || intval($montoTotal) <= 0) {
		return $resumen;
	}
	if ($mysqliConciliacion === null) {
		$mysqliConciliacion= conectar_al_servidor();
	}
	if ($tablaDisponible === null) {
		$tablaDisponible= flujoGastoTablaExiste($mysqliConciliacion, "ueno_movimiento_gasto");
	}
	if (!$tablaDisponible) {
		return $resumen;
	}
	$id= intval($idgastos);
	$sql= "SELECT IFNULL(SUM(monto_aplicado),0) AS conciliado, COUNT(*) AS asignaciones
		FROM ueno_movimiento_gasto
		WHERE idgastos=$id AND estado='activo'";
	$result= $mysqliConciliacion->query($sql);
	if (!$result || !($row= $result->fetch_assoc())) {
		return $resumen;
	}
	$conciliado= intval($row['conciliado']);
	$pendiente= max(0, intval($montoTotal) - $conciliado);
	$estado= 'sin-conciliar';
	$texto= 'Sin conciliar';
	if ($conciliado >= intval($montoTotal) && intval($montoTotal) > 0) {
		$estado= 'conciliado';
		$texto= 'Conciliado';
	} else if ($conciliado > 0) {
		$estado= 'parcial';
		$texto= 'Parcial';
	}
	$resumen['disponible']= true;
	$resumen['conciliado']= $conciliado;
	$resumen['pendiente']= $pendiente;
	$resumen['estado']= $estado;
	$resumen['texto']= $texto;
	$resumen['asignaciones']= intval($row['asignaciones']);
	return $resumen;
}

function construirIndicadorConciliacionUenoGasto($resumen) {
	if (!$resumen['disponible']) {
		return "";
	}
	return "<span class='flujo-ueno-status flujo-ueno-status--".$resumen['estado']."' title='Conciliado: ".number_format($resumen['conciliado'], 0, ',', '.')." Gs. | Pendiente: ".number_format($resumen['pendiente'], 0, ',', '.')." Gs.'>"
		."<b>".$resumen['texto']."</b>"
		."<small>Conc. ".number_format($resumen['conciliado'], 0, ',', '.')." / Pend. ".number_format($resumen['pendiente'], 0, ',', '.')."</small>"
		."</span>";
}

function construirBotonConciliarEgresoUeno($gasto, $grupo= '') {
	$idgastos= isset($gasto['idgastos']) ? trim((string)$gasto['idgastos']) : '';
	$tipo= strtolower(trim((string)(isset($gasto['tipo']) ? $gasto['tipo'] : '')));
	if ($idgastos == "" || $tipo != "egreso" || flujoGastoEstaAnulado($gasto)) {
		return "";
	}
	return "<button type='button' class='flujo-ueno-conciliar-btn' title='Conciliar este gasto con un egreso del extracto bancario' onclick='abrirConciliacionEgresoUenoDesdeBoton(event, this)'"
		." data-idgastos='".flujoGastoTextoSeguro($idgastos)."'"
		." data-grupo='".flujoGastoTextoSeguro($grupo)."'"
		." data-concepto='".flujoGastoTextoSeguro(isset($gasto['motivo']) ? $gasto['motivo'] : '')."'>"
		."<span>&#8644;</span><b>Conciliar</b>"
		."</button>";
}

function flujoGastoFechaObjeto($fecha) {
	if (empty($fecha)) {
		return null;
	}
	$fecha= substr((string)$fecha, 0, 10);
	$obj= DateTime::createFromFormat('!Y-m-d', $fecha);
	return ($obj === false ? null : $obj);
}

function flujoGastoFechaCorta($fecha) {
	$obj= flujoGastoFechaObjeto($fecha);
	if ($obj) {
		return $obj->format('d/m/Y');
	}
	return flujoGastoTextoSeguro($fecha);
}

function obtenerResumenCuotasProgramadas($gastosSerie) {
	$hoy= new DateTime('today');
	$total= count($gastosSerie);
	$pagadas= 0;
	$vencidas= 0;
	$futuras= 0;
	$anuladas= 0;
	$proximoFecha= null;
	$proximoTexto= "";

	foreach ($gastosSerie as $gasto) {
		$estado= strtolower(trim((string)$gasto['estado']));
		$fechaObj= flujoGastoFechaObjeto(isset($gasto['fecha']) ? $gasto['fecha'] : '');
		if (flujoGastoEstaAnulado($gasto)) {
			$anuladas++;
			continue;
		}
		if ($estado == 'activo') {
			$pagadas++;
			continue;
		}
		if ($fechaObj && $fechaObj <= $hoy) {
			$vencidas++;
			continue;
		}
		if ($fechaObj) {
			$futuras++;
			if ($proximoFecha === null || $fechaObj < $proximoFecha) {
				$proximoFecha= $fechaObj;
				$proximoTexto= $fechaObj->format('d/m/Y');
			}
		}
	}

	if ($total <= 0) {
		$tipo= 'sin-cuotas';
		$texto= 'Sin cuotas';
		$icono= '-';
	} else if ($vencidas > 0) {
		$tipo= 'vencido';
		$texto= 'Cuota vencida';
		$icono= '!';
	} else if ($futuras > 0) {
		$tipo= 'programado';
		$texto= 'Programado';
		$icono= '&#128197;';
	} else {
		$tipo= 'al-dia';
		$texto= 'Al d&iacute;a';
		$icono= '&#10003;';
	}

	return array(
		'tipo' => $tipo,
		'texto' => $texto,
		'icono' => $icono,
		'total' => $total,
		'pagadas' => $pagadas,
		'vencidas' => $vencidas,
		'futuras' => $futuras,
		'anuladas' => $anuladas,
		'proximo' => $proximoTexto,
	);
}

function obtenerEtiquetaCuotaProgramada($gasto) {
	$estado= strtolower(trim((string)$gasto['estado']));
	if ($estado == 'baja') {
		return array('tipo' => 'anulado', 'texto' => 'Dado de baja');
	}
	if (flujoGastoEstaAnulado($gasto)) {
		return array('tipo' => 'anulado', 'texto' => 'Anulado');
	}
	if ($estado == 'activo') {
		return array('tipo' => 'pagado', 'texto' => 'Pagado');
	}
	$fechaObj= flujoGastoFechaObjeto(isset($gasto['fecha']) ? $gasto['fecha'] : '');
	if ($fechaObj && $fechaObj <= new DateTime('today')) {
		return array('tipo' => 'vencido', 'texto' => 'Vencido');
	}
	return array('tipo' => 'programado', 'texto' => 'Programado');
}

function construirIndicadorCuotasProgramadas($resumen) {
	return "<span class='cuotas-programadas-badge cuotas-programadas-badge--".$resumen['tipo']."'>"
		."<span>".$resumen['icono']."</span>"
		."<b>".$resumen['texto']."</b>"
		."</span>";
}

function construirMetaCuotasProgramadas($resumen) {
	$proximo= $resumen['proximo'] ? $resumen['proximo'] : 'Sin vencimientos';
	return "<div class='cuotas-programadas-meta'>"
		."<span>Cuotas: <b>".$resumen['pagadas']."/".$resumen['total']."</b> pagadas</span>"
		."<span>Pr&oacute;ximo venc.: <b>".flujoGastoTextoSeguro($proximo)."</b></span>"
		."</div>";
}

function flujoGastoEsCuotaProgramada($gasto) {
	$modalidad= strtolower(trim((string)(isset($gasto['modalidad']) ? $gasto['modalidad'] : '')));
	$codPadre= trim((string)(isset($gasto['cod_gasto_padre']) ? $gasto['cod_gasto_padre'] : ''));
	return ($modalidad == 'credito' || ($codPadre != '' && $codPadre != '0'));
}

function filtrarGastosCuotasProgramadas($gastos) {
	$filtrados= array();
	foreach ($gastos as $gasto) {
		if (flujoGastoEsCuotaProgramada($gasto)) {
			$filtrados[]= $gasto;
		}
	}
	return $filtrados;
}

function flujoGastoNombreProyecto($codProyectoGasto) {
	static $cache= array();
	$codProyectoGasto= trim((string)$codProyectoGasto);
	if ($codProyectoGasto == "" || $codProyectoGasto == "0" || !is_numeric($codProyectoGasto)) {
		return "";
	}
	if (isset($cache[$codProyectoGasto])) {
		return $cache[$codProyectoGasto];
	}
	$nombre= "";
	if (function_exists('obtenerProyectoGasto')) {
		$proyectos= obtenerProyectoGasto(array(
			'id' => $codProyectoGasto,
			'incluir_sin_gastos' => 'true',
		), 1);
		if (isset($proyectos[0]) && isset($proyectos[0]['nombre'])) {
			$nombre= trim((string)$proyectos[0]['nombre']);
		}
	}
	if ($nombre == "") {
		$nombre= "Proyecto ".$codProyectoGasto;
	}
	$cache[$codProyectoGasto]= $nombre;
	return $nombre;
}

function construirSubgrupoFlujoConcepto($titulo, $contenido, $total, $tipo= 'proyecto', $detalle= '') {
	if (trim((string)$contenido) == "") {
		return "";
	}
	$total= intval($total);
	$badge= "Detalle";
	if ($tipo == 'pago' || $tipo == 'aislado') {
		$badge= "Pago unico";
	} else if ($tipo == 'proyecto') {
		$badge= "Proyecto";
	}
	$esProyecto= ($tipo == 'proyecto');
	return "<li class='list-group-item flujo-concepto-subgrupo flujo-concepto-subgrupo--".$tipo."'>"
		."<div class='flujo-concepto-subgrupo__head'".($esProyecto ? " onclick='alternarSubgrupoFlujoConcepto(event, this)'" : "").">"
		."<span class='flujo-concepto-subgrupo__badge'>".$badge."</span>"
		."<strong>".flujoGastoTextoSeguro($titulo)."</strong>"
		.($detalle != "" ? "<small>".flujoGastoTextoSeguro($detalle)."</small>" : "")
		."<b>".number_format($total, 0, ',', '.')." Gs.</b>"
		.($esProyecto ? "<button type='button' class='flujo-concepto-subgrupo__toggle' title='Expandir o contraer proyecto'>-</button>" : "")
		."</div>"
		."<ul class='list-group list-group-flush flujo-concepto-subgrupo__items'>".$contenido."</ul>"
		."</li>";
}

function construirBotonCrearProyectoHilo($gasto, $compacto= false) {
	$codInterConsulta= trim((string)(isset($gasto['cod_interConsultaFK']) ? $gasto['cod_interConsultaFK'] : ''));
	if ($codInterConsulta == "" || $codInterConsulta == "0") {
		return "";
	}

	$nombreHilo= trim((string)(isset($gasto['interconsulta_nombre']) ? $gasto['interconsulta_nombre'] : ''));
	$sugerencia= $nombreHilo;
	if ($sugerencia == "" && isset($gasto['descripcion'])) {
		$sugerencia= trim((string)$gasto['descripcion']);
	}
	if ($sugerencia == "" && isset($gasto['motivo'])) {
		$sugerencia= trim((string)$gasto['motivo']);
	}
	$codConcepto= trim((string)(isset($gasto['cod_motivoIngresoEgresoFK']) ? $gasto['cod_motivoIngresoEgresoFK'] : ''));
	$nombreConcepto= trim((string)(isset($gasto['motivo']) ? $gasto['motivo'] : ''));
	$tipoMovimiento= trim((string)(isset($gasto['tipo']) ? $gasto['tipo'] : 'Egreso'));
	$codLocal= trim((string)(isset($gasto['cod_local']) ? $gasto['cod_local'] : ''));

	$claseCompacto= $compacto ? " flujo-proyecto-hilo-btn--compacto" : "";
	return "<button type='button' class='flujo-proyecto-hilo-btn".$claseCompacto."' title='Crear proyecto para este hilo' onclick='crearProyectoGastoDesdeBotonHilo(event, this)'"
		." data-cod-interconsulta='".flujoGastoTextoSeguro($codInterConsulta)."'"
		." data-nombre-hilo='".flujoGastoTextoSeguro($nombreHilo)."'"
		." data-sugerencia-proyecto='".flujoGastoTextoSeguro($sugerencia)."'"
		." data-concepto-id='".flujoGastoTextoSeguro($codConcepto)."'"
		." data-concepto-nombre='".flujoGastoTextoSeguro($nombreConcepto)."'"
		." data-tipo-movimiento='".flujoGastoTextoSeguro($tipoMovimiento)."'"
		." data-local-id='".flujoGastoTextoSeguro($codLocal)."'>"
		."<span>+</span><b>Proyecto</b>"
		."</button>";
}

function flujoGastoCeldaOculta($id, $valor) {
	return "<td id='".$id."' style='display:none'>".flujoGastoTextoSeguro($valor)."</td>";
}

function construirCeldasOcultasGastoFila($gasto) {
	$montoEdicion= isset($gasto['monto_total_padre']) ? $gasto['monto_total_padre'] : (isset($gasto['monto']) ? $gasto['monto'] : 0);
	$descripcionEdicion= isset($gasto['descripcion_original']) ? $gasto['descripcion_original'] : (isset($gasto['descripcion']) ? $gasto['descripcion'] : '');
	return flujoGastoCeldaOculta('td_datos_1', number_format(intval($montoEdicion), 0, ',', '.'))
		.flujoGastoCeldaOculta('td_datos_2', isset($gasto['motivo']) ? $gasto['motivo'] : '')
		.flujoGastoCeldaOculta('td_datos_3', isset($gasto['fecha']) ? $gasto['fecha'] : '')
		.flujoGastoCeldaOculta('td_datos_5', isset($gasto['estado']) ? $gasto['estado'] : '')
		.flujoGastoCeldaOculta('td_datos_6', isset($gasto['tipo']) ? $gasto['tipo'] : '')
		.flujoGastoCeldaOculta('td_datos_7', isset($gasto['cod_local']) ? $gasto['cod_local'] : '')
		.flujoGastoCeldaOculta('td_datos_8', isset($gasto['nroboleta']) ? $gasto['nroboleta'] : '')
		.flujoGastoCeldaOculta('td_datos_9', isset($gasto['banco']) ? $gasto['banco'] : '')
		.flujoGastoCeldaOculta('td_datos_10', isset($gasto['nrocuenta']) ? $gasto['nrocuenta'] : '')
		.flujoGastoCeldaOculta('td_datos_11', isset($gasto['arreglo']) ? $gasto['arreglo'] : '')
		.flujoGastoCeldaOculta('td_datos_12', isset($gasto['url1']) ? $gasto['url1'] : '')
		.flujoGastoCeldaOculta('td_datos_13', $descripcionEdicion)
		.flujoGastoCeldaOculta('td_datos_14', isset($gasto['motivo']) ? $gasto['motivo'] : '')
		.flujoGastoCeldaOculta('td_datos_15', isset($gasto['cod_interConsultaFK']) ? $gasto['cod_interConsultaFK'] : '')
		.flujoGastoCeldaOculta('td_datos_16', isset($gasto['interconsulta_nombre']) ? $gasto['interconsulta_nombre'] : '')
		.flujoGastoCeldaOculta('td_datos_17', isset($gasto['cod_usuario_autoriz']) ? $gasto['cod_usuario_autoriz'] : '')
		.flujoGastoCeldaOculta('td_datos_18', isset($gasto['usuario_autoriz_nombre']) ? $gasto['usuario_autoriz_nombre'] : '')
		.flujoGastoCeldaOculta('td_datos_19', isset($gasto['fecha_autoriz']) ? $gasto['fecha_autoriz'] : '')
		.flujoGastoCeldaOculta('td_datos_20', isset($gasto['cod_motivoIngresoEgresoFK']) ? $gasto['cod_motivoIngresoEgresoFK'] : '')
		.flujoGastoCeldaOculta('td_datos_21', isset($gasto['usuarionombre']) ? $gasto['usuarionombre'] : '')
		.flujoGastoCeldaOculta('td_datos_22', isset($gasto['cod_proyecto_gastoFK']) ? $gasto['cod_proyecto_gastoFK'] : '')
		.flujoGastoCeldaOculta('td_datos_23', isset($gasto['modalidad']) ? $gasto['modalidad'] : '')
		.flujoGastoCeldaOculta('td_datos_24', isset($gasto['cod_gasto_padre']) ? $gasto['cod_gasto_padre'] : '')
		.flujoGastoCeldaOculta('td_datos_25', isset($gasto['url_documento_firmado']) ? $gasto['url_documento_firmado'] : '');
}

function flujoGastoConstruirReferenciasGestionOrigen($candidatos, $idsDistribuidos)
{
	$referencias= array();
	foreach ($candidatos as $candidato) {
		$idgastos= isset($candidato['idgastos']) ? (int)$candidato['idgastos'] : 0;
		if ($idgastos <= 0 || !isset($idsDistribuidos[$idgastos])) {
			continue;
		}
		$referencia= $candidato;
		$referencia['monto']= isset($referencia['monto_total_padre']) ? $referencia['monto_total_padre'] : $referencia['monto'];
		$referencia['es_referencia_gestion_origen']= true;
		$referencia['es_asignacion_multilocal']= false;
		$referencia['cod_local_destino_distribucion']= '';
		$referencia['concepto_historico']= strtolower(trim((string)(isset($referencia['estado_motivo']) ? $referencia['estado_motivo'] : ''))) !== 'activo';
		$referencias[]= $referencia;
	}
	return $referencias;
}

function flujoGastoFiltrarMovimientosAnaliticos($registros, $idsDistribuidosOrigen, $conservarPadresDistribuidos = false)
{
	$salida= array();
	foreach ($registros as $registro) {
		$idgastosRegistro= isset($registro['idgastos']) ? (int)$registro['idgastos'] : 0;
		$esPadreDistribuido= isset($idsDistribuidosOrigen[$idgastosRegistro]) && empty($registro['es_asignacion_multilocal']);
		if ($esPadreDistribuido && !$conservarPadresDistribuidos) {
			continue;
		}
		if ($esPadreDistribuido) {
			// En la vista global no existen porciones por sucursal: se conserva el
			// gasto padre una sola vez por su total y exclusivamente para consulta.
			$registro['monto']= isset($registro['monto_total_padre']) ? $registro['monto_total_padre'] : $registro['monto'];
			$registro['es_resumen_global_distribucion']= true;
		}
		$salida[]= $registro;
	}
	return $salida;
}

function flujoGastoPrepararReferenciasGestionOrigen($candidatos)
{
	$salida= array('ids_distribuidos' => array(), 'referencias' => array());
	$porId= array();
	foreach ($candidatos as $candidato) {
		$idgastos= isset($candidato['idgastos']) ? (int)$candidato['idgastos'] : 0;
		if ($idgastos > 0) {
			$porId[$idgastos]= $candidato;
		}
	}
	if (count($porId) < 1) {
		return $salida;
	}
	$mysqli= conectar_al_servidor();
	if (!gastoDistribucionTablaDisponible($mysqli)) {
		$mysqli->close();
		return $salida;
	}
	$resultado= $mysqli->query('SELECT DISTINCT idgastosFK FROM gasto_distribucion_local WHERE idgastosFK IN ('.implode(',', array_keys($porId)).') ORDER BY idgastosFK');
	if (!$resultado) {
		$mysqli->close();
		return $salida;
	}
	while ($fila= $resultado->fetch_assoc()) {
		$idgastos= (int)$fila['idgastosFK'];
		$salida['ids_distribuidos'][$idgastos]= true;
	}
	$salida['referencias']= flujoGastoConstruirReferenciasGestionOrigen($candidatos, $salida['ids_distribuidos']);
	$mysqli->close();
	return $salida;
}

function construirPanelReferenciasGestionOrigen($referencias, $puedeGestionarOrigen)
{
	if (count($referencias) < 1) {
		return '';
	}
	$filas= '';
	foreach ($referencias as $referencia) {
		$idgastos= isset($referencia['idgastos']) ? (int)$referencia['idgastos'] : 0;
		if ($idgastos <= 0) {
			continue;
		}
		$estadoConcepto= strtolower(trim((string)(isset($referencia['estado_motivo']) ? $referencia['estado_motivo'] : '')));
		$esHistorico= !empty($referencia['concepto_historico']) || $estadoConcepto !== 'activo';
		$montoTotal= (int)(isset($referencia['monto_total_padre']) ? $referencia['monto_total_padre'] : $referencia['monto']);
		$concepto= trim((string)(isset($referencia['motivo']) ? $referencia['motivo'] : ''));
		$descripcion= trim((string)(isset($referencia['descripcion_original']) ? $referencia['descripcion_original'] : (isset($referencia['descripcion']) ? $referencia['descripcion'] : '')));
		$estado= obtenerEtiquetaCuotaProgramada($referencia);
		$accion= "<span class='flujo-pago-unico-solo-lectura'>Hist&oacute;rico &middot; solo lectura</span>";
		if (!$esHistorico && $puedeGestionarOrigen) {
			$accion= "<button type='button' class='flujo-pago-unico-origen' title='Abrir el gasto padre completo' onclick='event.stopPropagation();if(confirm(\"Esta referencia no integra los totales. Se abrira el gasto padre completo por ".number_format($montoTotal, 0, ',', '.')." Gs. Desea continuar?\")){editarGastoDesdeFila(event, this);}'>Ver/gestionar gasto origen</button>";
		}
		$filas .= "<tr id='tbGestionGastoOrigen'>"
			."<td id='td_id'>".flujoGastoTextoSeguro($idgastos)."</td>"
			."<td><strong>".flujoGastoTextoSeguro($concepto != '' ? $concepto : 'Concepto historico')."</strong><small>".flujoGastoTextoSeguro($descripcion)."</small></td>"
			."<td>".number_format($montoTotal, 0, ',', '.')." Gs.<small>Monto total del gasto padre</small></td>"
			."<td>".flujoGastoFechaCorta(isset($referencia['fecha']) ? $referencia['fecha'] : '')."</td>"
			."<td><span class='cuotas-programadas-estado cuotas-programadas-estado--".$estado['tipo']."'>".$estado['texto']."</span></td>"
			."<td>".$accion."</td>"
			.construirCeldasOcultasGastoFila($referencia)
			."</tr>";
	}
	if ($filas === '') {
		return '';
	}
	return "<section class='flujo-gestion-origen'>"
		."<div class='flujo-gestion-origen__head'><div><strong>Gastos gestionados por este local</strong><p>Referencias operativas del gasto padre. No se suman al flujo, informes ni exportaciones.</p></div><span>Gesti&oacute;n</span></div>"
		."<div class='flujo-gestion-origen__table-wrap'><table><thead><tr><th>Ref.</th><th>Concepto</th><th>Total padre</th><th>Fecha</th><th>Estado</th><th>Acci&oacute;n</th></tr></thead><tbody>".$filas."</tbody></table></div>"
		."</section>";
}

function construirDetalleCuotasProgramadas($gastosSerie, $resumen) {
	if (count($gastosSerie) <= 1) {
		return "";
	}
	$total= count($gastosSerie);
	$gastoBase= isset($gastosSerie[0]) ? $gastosSerie[0] : array();
	$soloLecturaHistorica= !empty($gastoBase['concepto_historico']);
	$soloLecturaAsignacion= flujoGastoEsAsignacionMultilocalSoloLectura($gastoBase);
	$botonCrearProyectoHilo= ($soloLecturaHistorica || $soloLecturaAsignacion) ? '' : construirBotonCrearProyectoHilo($gastoBase, true);
	$filas= "";
	foreach ($gastosSerie as $indice => $gasto) {
		$estado= obtenerEtiquetaCuotaProgramada($gasto);
		$estadoOriginal= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
		$soloLecturaFila= $soloLecturaHistorica || flujoGastoEsAsignacionMultilocalSoloLectura($gasto);
		$indicadorConciliacionUeno= "";
		if (!$soloLecturaFila && !flujoGastoEstaAnulado($gasto)) {
			$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno(isset($gasto['idgastos']) ? $gasto['idgastos'] : '', isset($gasto['monto']) ? $gasto['monto'] : 0);
			$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
		}
		$acciones= $soloLecturaFila
			? "<span class='flujo-pago-unico-solo-lectura'>".($soloLecturaHistorica ? 'Hist&oacute;rico &middot; solo lectura' : 'Asignado &middot; solo lectura')."</span>"
			: "<span style='color:#4b5563;font-size:8pt;'>Cerrada</span>";
		if (!$soloLecturaFila && $estadoOriginal != 'activo') {
			$acciones= "<button type='button' title='Editar cuota' onclick='editarGastoDesdeFila(event, this)' style='border:0;background:#2f80ed;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>Editar</button>";
			if ($estadoOriginal == 'pendiente' || $estadoOriginal == 'solicitado') {
				$acciones .= " <button type='button' title='Aprobar cuota' onclick='event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)' style='border:0;background:#078b35;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>OK</button>"
					." <button type='button' title='Rechazar cuota' onclick='event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)' style='border:0;background:#c92323;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>X</button>";
			}
		}
		if (!$soloLecturaFila) {
			$acciones .= construirBotonConciliarEgresoUeno($gasto, 'Cuotas programadas');
		}
		$filas .= "<tr>"
			."<td id='td_id' style='display:none'>".flujoGastoTextoSeguro(isset($gasto['idgastos']) ? $gasto['idgastos'] : '')."</td>"
			."<td>".($indice + 1)."/".$total."</td>"
			."<td>".flujoGastoFechaCorta(isset($gasto['fecha']) ? $gasto['fecha'] : '')."</td>"
			."<td><span class='cuotas-programadas-estado cuotas-programadas-estado--".$estado['tipo']."'>".$estado['texto']."</span></td>"
			."<td>".number_format(intval($gasto['monto']), 0, ',', '.')." Gs.".$indicadorConciliacionUeno."</td>"
			."<td>".$acciones."</td>"
			.construirCeldasOcultasGastoFila($gasto)
			."</tr>";
	}

	return "<tr class='cuotas-programadas-row' style='display:none;'>"
		."<td colspan='32'>"
		."<div class='cuotas-programadas-panel'>"
		."<div class='cuotas-programadas-panel__head'>"
		."<strong>Cuotas programadas</strong>"
		."<div class='cuotas-programadas-panel__actions'>"
		.$botonCrearProyectoHilo
		.construirIndicadorCuotasProgramadas($resumen)
		."</div>"
		."</div>"
		."<table class='cuotas-programadas-table'>"
		."<thead><tr><th>Cuota</th><th>Vencimiento</th><th>Estado</th><th>Monto</th><th>Acciones</th></tr></thead>"
		."<tbody>".$filas."</tbody>"
		."</table>"
		."</div>"
		."</td>"
		."</tr>";
}

function construirTablaCuotasProyectoFlujo($gastosSerie, $resumen) {
	if (count($gastosSerie) < 1) {
		return "";
	}
	$total= count($gastosSerie);
	$gastoBase= isset($gastosSerie[0]) ? $gastosSerie[0] : array();
	$soloLecturaHistorica= !empty($gastoBase['concepto_historico']);
	$soloLecturaAsignacion= flujoGastoEsAsignacionMultilocalSoloLectura($gastoBase);
	$botonCrearProyectoHilo= ($soloLecturaHistorica || $soloLecturaAsignacion) ? '' : construirBotonCrearProyectoHilo($gastoBase, true);
	$filas= "";
	foreach ($gastosSerie as $indice => $gasto) {
		$idCuota= isset($gasto['idgastos']) ? $gasto['idgastos'] : '';
		if ($idCuota == '') {
			continue;
		}
		$estado= obtenerEtiquetaCuotaProgramada($gasto);
		$estadoOriginal= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
		$soloLecturaFila= $soloLecturaHistorica || !empty($gasto['concepto_historico']) || flujoGastoEsAsignacionMultilocalSoloLectura($gasto);
		$indicadorConciliacionUeno= "";
		if (!$soloLecturaFila && !flujoGastoEstaAnulado($gasto)) {
			$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno($idCuota, isset($gasto['monto']) ? $gasto['monto'] : 0);
			$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
		}
		$acciones= $soloLecturaFila
			? "<span class='flujo-pago-unico-solo-lectura'>".(($soloLecturaHistorica || !empty($gasto['concepto_historico'])) ? 'Hist&oacute;rico &middot; solo lectura' : 'Asignado &middot; solo lectura')."</span>"
			: "<span style='color:#4b5563;font-size:8pt;'>Cerrada</span>";
		if (!$soloLecturaFila && $estadoOriginal != 'activo') {
			$acciones= "<button type='button' title='Editar cuota' onclick='editarGastoDesdeFila(event, this)' style='border:0;background:#2f80ed;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>Editar</button>";
			if ($estadoOriginal == 'pendiente' || $estadoOriginal == 'solicitado') {
				$acciones .= " <button type='button' title='Aprobar cuota' onclick='event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)' style='border:0;background:#078b35;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>OK</button>"
					." <button type='button' title='Rechazar cuota' onclick='event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)' style='border:0;background:#c92323;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>X</button>";
			}
		}
		if (!$soloLecturaFila) {
			$acciones .= construirBotonConciliarEgresoUeno($gasto, 'Cuotas programadas');
		}
		$filas .= "<tr id='tbSelecRegistro'>"
			."<td id='td_id' style='display:none'>".flujoGastoTextoSeguro($idCuota)."</td>"
			."<td>".($indice + 1)."/".$total."</td>"
			."<td>".flujoGastoFechaCorta(isset($gasto['fecha']) ? $gasto['fecha'] : '')."</td>"
			."<td><span class='cuotas-programadas-estado cuotas-programadas-estado--".$estado['tipo']."'>".$estado['texto']."</span></td>"
			."<td>".number_format(intval($gasto['monto']), 0, ',', '.')." Gs.".$indicadorConciliacionUeno."</td>"
			."<td>".$acciones."</td>"
			.construirCeldasOcultasGastoFila($gasto)
			."</tr>";
	}
	if ($filas == "") {
		return "";
	}
	return "<div class='cuotas-programadas-panel cuotas-programadas-panel--proyecto'>"
		."<div class='cuotas-programadas-panel__head'>"
		."<strong>Cuotas del proyecto</strong>"
		."<div class='cuotas-programadas-panel__actions'>".$botonCrearProyectoHilo.construirIndicadorCuotasProgramadas($resumen)."</div>"
		."</div>"
		."<table class='cuotas-programadas-table'>"
		."<thead><tr><th>Cuota</th><th>Vencimiento</th><th>Estado</th><th>Monto</th><th>Acciones</th></tr></thead>"
		."<tbody>".$filas."</tbody>"
		."</table>"
		."</div>";
}

function construirLinkInterconsultaFlujoGasto($gasto, $soloLectura = false) {
	$codInterConsulta= trim((string)(isset($gasto['cod_interConsultaFK']) ? $gasto['cod_interConsultaFK'] : ''));
	$interconsultaNombre= trim((string)(isset($gasto['interconsulta_nombre']) ? $gasto['interconsulta_nombre'] : ''));
	if ($interconsultaNombre == "" && $codInterConsulta != "") {
		$interconsultaNombre= "Hilo ".$codInterConsulta;
	}
	if ($interconsultaNombre == "") {
		$interconsultaNombre= "Sin hilo";
	}
	$interconsultaElemento= flujoGastoTextoSeguro($interconsultaNombre);
	if ($codInterConsulta != "" && !$soloLectura) {
		$registrosMens= obtenerMensaje(array(
			'fecha_creacion' => "> '".(new DateTime())->format('Y-m-d H:i:s')."'",
			"cod_interConsultaFK" => $codInterConsulta,
		));
		foreach ($registrosMens as $valueMens) {
			if ($valueMens['estado'] == 'activo') {
				$fechaMensaje = new DateTime(substr($valueMens['fecha_creacion'], 0, 10));
				$fechaActual = new DateTime();
				$diasRestantes = $fechaMensaje->diff($fechaActual->setTime(0, 0, 0));
				$interconsultaElemento .= ' <i class="fa-solid fa-business-time" style="padding-left: 5px;font-size: 9pt;"></i>('.$diasRestantes->format('%a').')';
			}
		}
		return "<button type='button' class='flujo-pago-unico-hilo' onclick='event.stopPropagation();obtenerdatosabmGasto(this.parentElement.parentElement);ventanaAnterior.push(\"divAbmGasto1\");obtenerDatosInterConsulta(this)'>".$interconsultaElemento."</button>";
	}
	return "<span class='flujo-pago-unico-hilo flujo-pago-unico-hilo--vacio'>".$interconsultaElemento."</span>";
}

function flujoGastoEsAsignacionMultilocalSoloLectura($gasto) {
	return !empty($gasto['es_asignacion_multilocal']) || !empty($gasto['es_resumen_global_distribucion']);
}

function construirPagoUnicoFlujoConcepto($gasto, $tituloZona= '', $codUsuarioActual= 0) {
	$idGasto= isset($gasto['idgastos']) ? $gasto['idgastos'] : '';
	if ($idGasto == '') {
		return "";
	}
	$esAsignacionAdministrativa= !empty($gasto['es_asignacion_administrativa']);
	$soloLecturaHistorica= !empty($gasto['concepto_historico']);
	$esAsignacionMultilocalSoloLectura= flujoGastoEsAsignacionMultilocalSoloLectura($gasto);
	$soloLecturaMovimiento= $esAsignacionAdministrativa || $soloLecturaHistorica || $esAsignacionMultilocalSoloLectura;
	$monto= intval(isset($gasto['monto']) ? $gasto['monto'] : 0);
	$montoPadre= intval(isset($gasto['monto_total_padre']) ? $gasto['monto_total_padre'] : $monto);
	$estado= obtenerEtiquetaCuotaProgramada($gasto);
	$estadoOriginal= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
	$indicadorConciliacionUeno= "";
	if (!$soloLecturaMovimiento && !flujoGastoEstaAnulado($gasto)) {
		$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno($idGasto, $montoPadre);
		$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
	}
	$botonConciliarUeno= $soloLecturaMovimiento ? "" : construirBotonConciliarEgresoUeno($gasto, $tituloZona);
	$acciones= $soloLecturaMovimiento
		? "<span class='flujo-pago-unico-solo-lectura'>".($soloLecturaHistorica ? 'Hist&oacute;rico &middot; solo lectura' : 'Asignado &middot; solo lectura')."</span>"
		: "<button type='button' title='Editar movimiento' aria-label='Editar movimiento' onclick='editarGastoDesdeFila(event, this)' class='flujo-pago-unico-editar'>"
			."<img src='/GoodVentaAsisCap/iconos/editar.png' alt='Editar'>"
			."</button>".$botonConciliarUeno;
	if (!$soloLecturaMovimiento && ($estadoOriginal == 'pendiente' || $estadoOriginal == 'solicitado')) {
		$acciones .= "<button type='button' title='Aprobar pago' onclick='event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement.parentElement)' class='flujo-pago-unico-validar flujo-pago-unico-validar--ok'>OK</button>"
			."<button type='button' title='Rechazar pago' onclick='event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement.parentElement)' class='flujo-pago-unico-validar flujo-pago-unico-validar--rechazar'>X</button>";
	}
	$claseFila= flujoGastoEstaAnulado($gasto) ? " flujo-pago-unico-table__row--anulado" : "";
	if ($esAsignacionAdministrativa) {
		$claseFila .= " flujo-pago-unico-table__row--administracion";
	}
	$usuario= isset($gasto['usuarionombre']) ? $gasto['usuarionombre'] : '';
	$local= isset($gasto['nombrelocal']) ? $gasto['nombrelocal'] : '';
	$tipo= isset($gasto['tipo']) ? $gasto['tipo'] : '';
	$motivo= isset($gasto['motivo']) ? $gasto['motivo'] : '';
	$fecha= isset($gasto['fecha']) ? $gasto['fecha'] : '';
	$modalidadElemento= "<span class='flujo-modalidad-badge flujo-modalidad-badge--aislado'>Pago aislado</span>";
	$styleEstado= "";
	$fechaGasto= flujoGastoFechaObjeto($fecha);
	$fechaHoy= new DateTime('today');
	if (($estadoOriginal == 'solicitado' || $estadoOriginal == 'pendiente') && $fechaGasto && $fechaGasto <= $fechaHoy) {
		$styleEstado= "background-color: #ff5050;color: #ffffff;";
	} else if ($estadoOriginal == 'pendiente' || ($estadoOriginal == 'solicitado' && $fechaGasto && $fechaGasto > $fechaHoy)) {
		$styleEstado= "background-color: #585f08;color: #ffffff;";
	} else if ($estadoOriginal == 'activo') {
		$styleEstado= "background-color: #085f1c;color: #ffffff;";
	}

	return "<div class='flujo-pago-unico-card'>"
		."<table class='flujo-pago-unico-table flujo-pago-unico-table--encabezado'>"
		."<tbody><tr id='tbSelecRegistro' class='flujo-pago-unico-table__row".$claseFila."' onclick='".($soloLecturaMovimiento ? "" : "obtenerdatosabmGasto(this)")."'>"
		."<td id='td_id' class='flujo-pago-unico-ref' style='".$styleEstado."'>".flujoGastoTextoSeguro($esAsignacionAdministrativa ? "ADM ".$idGasto : $idGasto)."</td>"
		."<td class='flujo-pago-unico-concepto'>".flujoGastoTextoSeguro($motivo)."</td>"
		."<td class='flujo-pago-unico-interconsulta'>".construirLinkInterconsultaFlujoGasto($gasto, $soloLecturaMovimiento)."</td>"
		."<td class='flujo-pago-unico-monto'>".number_format($monto, 0, ',', '.').$indicadorConciliacionUeno."</td>"
		."<td class='flujo-pago-unico-estado'><span class='cuotas-programadas-estado cuotas-programadas-estado--".$estado['tipo']."'>".$estado['texto']."</span></td>"
		."<td class='flujo-pago-unico-acciones'><div class='flujo-ueno-acciones'>".$acciones."</div></td>"
		."<td class='flujo-pago-unico-modalidad'>".$modalidadElemento."</td>"
		."<td class='flujo-pago-unico-tipo'>".flujoGastoTextoSeguro($tipo)."</td>"
		."<td class='flujo-pago-unico-fecha'>".flujoGastoFechaCorta($fecha)."</td>"
		."<td class='flujo-pago-unico-usuario'>".flujoGastoTextoSeguro($usuario)."</td>"
		."<td class='flujo-pago-unico-local'>".flujoGastoTextoSeguro($local)."</td>"
		.construirCeldasOcultasGastoFila($gasto)
		."</tr></tbody>"
		."</table>"
		."</div>";
}

function buscarGastoConMotivos($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder= 'DESC', $codUsuarioActual= 0)
{
	$totalZonaIngresos= 0;
	$totalZonaCostosDirectos= 0;
	$totalZonaGastosOperativos= 0;
	$totalZonaAdministracionAsignada= 0;
	$totalZonaDepositosCentral= 0;
	$totalZonaMigracionesCaja= 0;
	$totalZonaSinCategorizar= 0;
	$totalGasto=0;
	$codLocalSeleccionadoFlujo= trim((string)$cod_local);

	$totalEstado= array();
	$totalEstado['Activo']= 0;
	$totalEstado['Inactivo']= 0;
	$totalEstado['Rechazado']= 0;
	$totalEstado['pendiente']= 0;
	$totalEstado['solicitado']= 0;

	$paginaImprimir= "";
	$pagina= "";

	$registrosZona= array();
	$registros= array();
	$resumenComposicionFlujo= flujoGastoCrearResumenComposicion();

	// Agrega el ingreso de los cierres de caja
	$registroMontosCobrados= Arqueo($fecha1,$fecha2,'','',$cod_local,"","","","",$usuario,"","","")[7];
	$registrosZona['ingreso'][-1]= array();
	foreach ($registroMontosCobrados as $key => $value) {
		// Crea un registro ficticio
		$valor= array(
			'idgastos' => "",
			'interconsulta_nombre' => "",
			'cod_interConsultaFK' => "",
			'usuarionombre' => (!empty($value['cobradornombre']) ? $value['cobradornombre'] : $value['cod_cobradorFK']),
			'monto' => $value['Monto'],
			'motivo' => "Movimiento de caja",
			'descripcion' => "Cobro realizado a ".$value['nombrecliente'] . " en formato ".$value['tipopago'],
			'fecha' => $value['Fecha'],
			'tipo' => "Ingreso",
			'estado' => "Activo",
			'cod_local' => $value['cod_local'],
			'nombrelocal' => $value['nombrelocal'],
			'nroboleta' => "",
			'cod_usuario' => "",
			'codCaja' => "",
			'codApertura' => "",
			'banco' => "",
			'nrocuenta' => "",
			'arreglo' => "",
			'url1' => "",
			'url_documento_firmado' => "",
			'categoria' => "ingreso",
			'cod_usuario_autoriz' => "",
			'fecha_autoriz' => "",
			'usuario_autoriz_nombre' => "",
			'cod_motivoIngresoEgresoFK' => -1,
			'nombre_usuario_edit' => "",
			'modalidad' => "contado",
			'cod_gasto_padre' => "",
			'cod_proyecto_gastoFK' => "",
		);
		$registrosZona['ingreso'][-1][]= $valor;
		if ($valor['estado'] == 'Activo') {
			$totalZonaIngresos += intval($valor['monto']);
		}
	}

	// Obtenemos todos los motivos del sistema
	$registrosMotivos= buscarabmmotivoingresoegreso('', 'activo')[4];
	$motivosActivosPorCodigo= array();
	foreach ($registrosMotivos as $motivoActivo) {
		$codigoMotivoActivo= (string)$motivoActivo['cod_motivo_ingreso_egreso'];
		$motivosActivosPorCodigo[$codigoMotivoActivo]= $motivoActivo;
	}

	// Preparamos las zonas de los motivos activos sin consultar gastos motivo por motivo.
	foreach($registrosMotivos as $mot) {
		// Se normaliza la categoria
		$categoria= esConceptoDepositoCentral($mot['descripcion'])
			? 'deposito'
			: (esConceptoMigracionCaja($mot['descripcion']) ? 'migracion' : $mot['categoria']);
		if (empty($categoria) || $categoria == 'NULL' || $categoria == null) {
			$categoria= "sinCategoria";
		}

		// Se crea la zona si no existe
		if (!isset($registrosZona[$categoria])) {
			$registrosZona[$categoria]= array();
		}

		// Se crea un codigo de motivo si es que no exite
		if (!isset($registrosZona[$categoria][$mot['cod_motivo_ingreso_egreso']])) {
			$registrosZona[$categoria][$mot['cod_motivo_ingreso_egreso']]= array();
		}

	}

	// Una sola consulta trae los movimientos; luego se agrupan en memoria por motivo y categoria.
	// Los conceptos inactivos se conservan para trazabilidad y totales historicos,
	// pero se renderizan sin acciones de alta, edicion ni conciliacion contextual.
	$registros= buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,'', $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder, '', true);
	$candidatosGestionOrigen= buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,'', $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder, '', false);
	$datosGestionOrigen= flujoGastoPrepararReferenciasGestionOrigen($candidatosGestionOrigen);
	$idsDistribuidosOrigen= $datosGestionOrigen['ids_distribuidos'];
	// Un padre distribuido solo es movimiento analitico cuando existe una
	// asignacion concreta para el local seleccionado. La referencia operativa
	// del origen se renderiza por separado y nunca integra los calculos.
	$esVistaGlobalFlujo= ((int)$codLocalSeleccionadoFlujo <= 0);
	$registros= flujoGastoFiltrarMovimientosAnaliticos($registros, $idsDistribuidosOrigen, $esVistaGlobalFlujo);
	$puedeGestionarOrigen= (int)$codUsuarioActual > 0
		&& (int)$codLocalSeleccionadoFlujo > 0
		&& usuarioPuedeGestionarLocalGasto($codUsuarioActual, $codLocalSeleccionadoFlujo);
	$referenciasGestionOrigen= array();
	if ($puedeGestionarOrigen) {
		foreach ($datosGestionOrigen['referencias'] as $referenciaGestion) {
			if ($cod_motivoFK !== '' && (string)$cod_motivoFK !== (string)$referenciaGestion['cod_motivoIngresoEgresoFK']) {
				continue;
			}
			$referenciasGestionOrigen[]= $referenciaGestion;
		}
	}
	$nroRegistro= count($registros);
	foreach ($registros as $valor) {
		$codMotivoRegistro= (string)$valor['cod_motivoIngresoEgresoFK'];
		$esDepositoRegistro= esTipoDepositoCentral($valor['tipo']);
		$nombreConceptoRegistro= isset($motivosActivosPorCodigo[$codMotivoRegistro]['descripcion'])
			? $motivosActivosPorCodigo[$codMotivoRegistro]['descripcion']
			: $valor['motivo'];
		$esMigracionRegistro= esConceptoMigracionCaja($nombreConceptoRegistro);
		if (!isset($motivosActivosPorCodigo[$codMotivoRegistro])) {
			$motivosActivosPorCodigo[$codMotivoRegistro]= array(
				'descripcion' => trim((string)$valor['motivo']) != ''
					? $valor['motivo']
					: ($esMigracionRegistro ? 'MIGRACION DE CAJA' : ($esDepositoRegistro ? 'Deposito bancario a central' : 'Concepto #'.$codMotivoRegistro)),
				'estado' => isset($valor['estado_motivo']) ? $valor['estado_motivo'] : 'inactivo'
			);
		}
		if ($cod_motivoFK != '' && (string)$cod_motivoFK !== $codMotivoRegistro) {
			continue;
		}
		$montoRegistro= intval($valor['monto']);
		$categoriaRegistro= $esDepositoRegistro
			? 'deposito'
			: ($esMigracionRegistro ? 'migracion' : flujoGastoNormalizarCategoriaResumen($valor['categoria']));
		if (!isset($registrosZona[$categoriaRegistro])) {
			$registrosZona[$categoriaRegistro]= array();
		}
		if (!isset($registrosZona[$categoriaRegistro][$codMotivoRegistro])) {
			$registrosZona[$categoriaRegistro][$codMotivoRegistro]= array();
		}
		$registrosZona[$categoriaRegistro][$codMotivoRegistro][]= $valor;
		if (!flujoGastoEstadoComputableResumen($valor['estado'])) {
			continue;
		}
		if ($categoriaRegistro != 'deposito' && $categoriaRegistro != 'migracion') {
			$totalGasto += $montoRegistro;
		}
		switch ($categoriaRegistro) {
			case 'ingreso':
				$totalZonaIngresos += $montoRegistro;
				break;
			case 'directo':
				$totalZonaCostosDirectos += $montoRegistro;
				break;
			case 'operativo':
				$totalZonaGastosOperativos += $montoRegistro;
				break;
			case 'administracion':
				$totalZonaAdministracionAsignada += $montoRegistro;
				break;
			case 'deposito':
				$totalZonaDepositosCentral += $montoRegistro;
				break;
			case 'migracion':
				$totalZonaMigracionesCaja += $montoRegistro;
				break;
			default:
				$totalZonaSinCategorizar += $montoRegistro;
				break;
		}
	}

	$administracionCompartida= null;
	if (flujoGastoFiltrosPermitenAdministracionCompartida($arreglo, $tipo, $usuario, $fecha, $cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos)) {
		$administracionCompartida= flujoGastoCalcularAdministracionCompartida($fecha1, $fecha2, $estado, $codLocalSeleccionadoFlujo, $tipo, $ocultar_inactivos, $fechaOrder);
		if (isset($administracionCompartida['modo']) && $administracionCompartida['modo'] == 'asignado' && intval($administracionCompartida['monto_asignado']) > 0) {
			if (!isset($registrosZona['administracion'])) {
				$registrosZona['administracion']= array();
			}
			foreach ($administracionCompartida['movimientos_asignados'] as $movimientoAdministrativo) {
				$codMotivoAdministrativo= trim((string)(isset($movimientoAdministrativo['cod_motivoIngresoEgresoFK']) ? $movimientoAdministrativo['cod_motivoIngresoEgresoFK'] : ''));
				if ($codMotivoAdministrativo == '') {
					$codMotivoAdministrativo= 'sin_codigo';
				}
				if (!isset($registrosZona['administracion'][$codMotivoAdministrativo])) {
					$registrosZona['administracion'][$codMotivoAdministrativo]= array();
				}
				$registrosZona['administracion'][$codMotivoAdministrativo][]= $movimientoAdministrativo;
			}
			$montoAdministracionHistorica= intval($administracionCompartida['monto_asignado']);
			$totalZonaAdministracionAsignada += $montoAdministracionHistorica;
			$totalGasto += $montoAdministracionHistorica;
		}
	}

	$registrosZonaOrdenados= array();
	foreach (array('ingreso', 'directo', 'operativo', 'administracion', 'sinCategoria', 'migracion') as $zonaOrdenada) {
		if (isset($registrosZona[$zonaOrdenada])) {
			$registrosZonaOrdenados[$zonaOrdenada]= $registrosZona[$zonaOrdenada];
		}
	}
	foreach ($registrosZona as $zona => $cod_motivos) {
		if ($zona == 'deposito') {
			continue;
		}
		if (!isset($registrosZonaOrdenados[$zona])) {
			$registrosZonaOrdenados[$zona]= $cod_motivos;
		}
	}
	$registrosZona= $registrosZonaOrdenados;

 $seriesCuotasRenderizadas= array();
 $styleName="tableRegistroSearch";
 foreach ($registrosZona as $zona => $cod_motivos) {
	$titulo= "";
	$totalZona= 0;
	$idZona= "";
	$styleColor= "";
	switch ($zona) {
		case 'ingreso':
			$idZona= "Ingreso";
			$titulo= "Ingresos";
			$totalZona= $totalZonaIngresos;
			$styleColor= "#75B59D;";
			$styleRegistroColor= "#8cac9c;";
			break;
		case 'directo':
			$idZona= "CostosDirectos";
			$titulo= "Costos Variables";
			$totalZona= $totalZonaCostosDirectos;
			$styleColor= "#EABA4C;";
			$styleRegistroColor= "#F4CB8D;";
			break;
		case 'operativo':
			$idZona= "GastosOperativos";
			$titulo= "Gastos Fijos";
			$totalZona= $totalZonaGastosOperativos;
			$styleColor= "#DE7258;";
			$styleRegistroColor= "#EDB5A4;";
			break;
		case 'administracion':
			$idZona= "AdministracionAsignada";
			$titulo= "Administracion asignada";
			$totalZona= $totalZonaAdministracionAsignada;
			$styleColor= "#3B6EA8;";
			$styleRegistroColor= "#BFD5EE;";
			break;
		case 'migracion':
			$idZona= "MigracionesCaja";
			$titulo= "Movimientos internos de caja";
			$totalZona= $totalZonaMigracionesCaja;
			$styleColor= "#7A8794;";
			$styleRegistroColor= "#D8DEE4;";
			break;
		default:
			$idZona= "SinCategorizar";
			$titulo= "Sin Categorizar";
			$totalZona= $totalZonaSinCategorizar;
			$styleColor= "#C4C4C4";
			$styleRegistroColor= "";
			break;
	}

	$pagina .= '<div class="card" style="width: 100%; margin: 0;gap: 0;min-height: 0px;">'.
	  '<div class="card-header" type="button" onclick="mostrarItems(\'zonaGastos'.$idZona.'\')" style="background-color: '.$styleColor.'">'.
      	'<h4><b>'.$titulo.'</b>: <span>'.number_format($totalZona, 0, ',', '.').'</span> Gs.</h4>'.
	  '</div>'.
	  '<div class="collapse show" id="zonaGastos'.$idZona.'" style=""><ul class="list-group list-group-flush">';

	  foreach ($cod_motivos as $cod_motivo => $gastos) {
		$totalMonto= 0;
		$paginaMotivo= "";
		$pagosUnicosMotivo= array();
		$gruposProyectoMotivo= array();
		$registro_autorizacion_necesario= false;
		// Obtiene el nombre del motivo
		if ($cod_motivo == -1) {
			$titulo_motivo= "Movimiento de caja";
		} else if ($cod_motivo == 'sin_codigo') {
			$titulo_motivo= "Sin concepto";
		} else {
			$titulo_motivo= isset($motivosActivosPorCodigo[(string)$cod_motivo])
				? $motivosActivosPorCodigo[(string)$cod_motivo]['descripcion']
				: "Concepto #".$cod_motivo;
		}
		$estadoMotivoActual= isset($motivosActivosPorCodigo[(string)$cod_motivo]['estado'])
			? strtolower(trim((string)$motivosActivosPorCodigo[(string)$cod_motivo]['estado'])) : 'activo';
		$esConceptoHistorico= ($cod_motivo != -1 && $estadoMotivoActual !== 'activo');
		$tituloMotivoVisual= $titulo_motivo.($esConceptoHistorico
			? " <span class='flujo-concepto-historico' title='Concepto inactivo: disponible solo para consulta'>Hist&oacute;rico &middot; solo lectura</span>" : '');
		$idMotivoCollapse= preg_replace('/[^A-Za-z0-9_-]/', '_', 'zonaMotivos'.$idZona.'_'.$cod_motivo);
		if ($zona != 'migracion') {
			flujoGastoAsegurarConceptoResumen($resumenComposicionFlujo, $zona, $cod_motivo, $titulo_motivo);
		}
		foreach ($gastos as $valor) {
			$valor['concepto_historico']= $esConceptoHistorico ? 1 : 0;
			if ($zona != 'migracion') {
				flujoGastoAgregarMovimientoResumen($resumenComposicionFlujo, $zona, $cod_motivo, $titulo_motivo, $valor);
			}
			$esAsignacionAdministrativa= !empty($valor['es_asignacion_administrativa']);
			$esAsignacionMultilocalSoloLectura= flujoGastoEsAsignacionMultilocalSoloLectura($valor);
			$montoOriginal= isset($valor['monto']) ? intval($valor['monto']) : 0;
			$estadoOriginal= isset($valor['estado']) ? $valor['estado'] : '';
			if (flujoGastoEstadoComputableResumen($estadoOriginal)) {
				$totalMonto += $montoOriginal;
			}
			if (isset($totalEstado[$estadoOriginal])) {
				$totalEstado[$estadoOriginal] += $montoOriginal;
			}

			$gastosSerieCuotas= array();
			$tieneCuotasProgramadas= false;
			$resumenCuotasProgramadas= null;
			$detalleCuotasProgramadas= "";
			$indicadorCuotasProgramadas= "";
			$metaCuotasProgramadas= "";
			$controlCuotasProgramadas= "<td class='cuotas-programadas-control'></td>";
			$codProyectoSerie= trim((string)(isset($valor['cod_proyecto_gastoFK']) ? $valor['cod_proyecto_gastoFK'] : ''));
			$esCuotaProgramada= flujoGastoEsCuotaProgramada($valor);
			if (!$esAsignacionAdministrativa && !$esAsignacionMultilocalSoloLectura && $codProyectoSerie != "" && $codProyectoSerie != "0" && $esCuotaProgramada) {
				$claveSerieRenderizada= $cod_motivo."|".$codProyectoSerie;
				if (isset($seriesCuotasRenderizadas[$claveSerieRenderizada])) {
					continue;
				}
				$gastosSerieCuotas= filtrarGastosCuotasProgramadas(obtenerGastosAsociados($valor['idgastos'], $codLocalSeleccionadoFlujo));
				if ($esConceptoHistorico) {
					foreach ($gastosSerieCuotas as $indiceCuotaHistorica => $cuotaHistorica) {
						$gastosSerieCuotas[$indiceCuotaHistorica]['concepto_historico']= 1;
					}
				}
				if (count($gastosSerieCuotas) < 1) {
					$gastosSerieCuotas= array($valor);
				}
				if (count($gastosSerieCuotas) > 0) {
					$seriesCuotasRenderizadas[$claveSerieRenderizada]= true;
					$tieneCuotasProgramadas= true;
					$valor= $gastosSerieCuotas[0];
					$resumenCuotasProgramadas= obtenerResumenCuotasProgramadas($gastosSerieCuotas);
					$detalleCuotasProgramadas= "";
					$indicadorCuotasProgramadas= construirIndicadorCuotasProgramadas($resumenCuotasProgramadas);
					$metaCuotasProgramadas= construirMetaCuotasProgramadas($resumenCuotasProgramadas);
					$controlCuotasProgramadas= "<td class='cuotas-programadas-control'><span class='cuotas-programadas-toggle' data-cuotas-toggle>+</span></td>";
				}
			}

			$idgastos=mb_convert_encoding((string)($valor['idgastos']), 'UTF-8', 'ISO-8859-1');
			$interconsulta_nombre= mb_convert_encoding((string)($valor['interconsulta_nombre']), 'UTF-8', 'ISO-8859-1');
			$cod_interConsultaFK= mb_convert_encoding((string)($valor['cod_interConsultaFK']), 'UTF-8', 'ISO-8859-1');
			$usuarionombre=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
			$monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
			$montoTotalPadre=mb_convert_encoding((string)(isset($valor['monto_total_padre']) ? $valor['monto_total_padre'] : $valor['monto']), 'UTF-8', 'ISO-8859-1');
			$motivo=mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');
			$descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
			$descripcionEdicion=mb_convert_encoding((string)(isset($valor['descripcion_original']) ? $valor['descripcion_original'] : $valor['descripcion']), 'UTF-8', 'ISO-8859-1');
			$fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
			$tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
			$estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			$cod_local=mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');
			$cod_usuario=mb_convert_encoding((string)($valor['cod_usuario']), 'UTF-8', 'ISO-8859-1');
			$codCaja=mb_convert_encoding((string)($valor['codCaja']), 'UTF-8', 'ISO-8859-1');
			$nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
			$nroboleta=mb_convert_encoding((string)($valor['nroboleta']), 'UTF-8', 'ISO-8859-1');
			$banco=mb_convert_encoding((string)($valor['banco']), 'UTF-8', 'ISO-8859-1');
			$nrocuenta=mb_convert_encoding((string)($valor['nrocuenta']), 'UTF-8', 'ISO-8859-1');
			$arreglo=mb_convert_encoding((string)($valor['arreglo']), 'UTF-8', 'ISO-8859-1');
			$url1=mb_convert_encoding((string)($valor['url1']), 'UTF-8', 'ISO-8859-1');
			$url_documento_firmado=mb_convert_encoding((string)($valor['url_documento_firmado']), 'UTF-8', 'ISO-8859-1');
			$categoria=mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1');
			$cod_usuario_autoriz = mb_convert_encoding((string)($valor['cod_usuario_autoriz']), 'UTF-8', 'ISO-8859-1');
			$fecha_autoriz = mb_convert_encoding((string)($valor['fecha_autoriz']), 'UTF-8', 'ISO-8859-1');
			$usuario_autoriz_nombre= mb_convert_encoding((string)($valor['usuario_autoriz_nombre']), 'UTF-8', 'ISO-8859-1');
			$cod_motivoIngresoEgresoFK= mb_convert_encoding((string)($valor['cod_motivoIngresoEgresoFK']), 'UTF-8', 'ISO-8859-1');
			$nombre_usuario_edit= mb_convert_encoding((string)($valor['nombre_usuario_edit']), 'UTF-8', 'ISO-8859-1');
			$modalidad= ucfirst(mb_convert_encoding((string)($valor['modalidad']), 'UTF-8', 'ISO-8859-1'));
			$cod_gasto_padre= ucfirst(mb_convert_encoding((string)($valor['cod_gasto_padre']), 'UTF-8', 'ISO-8859-1'));
			$cod_proyecto_gastoFK= ucfirst(mb_convert_encoding((string)($valor['cod_proyecto_gastoFK']), 'UTF-8', 'ISO-8859-1'));

			$funcion= "obtenerdatosabmGasto(this)";
			if ($idgastos == "") {
				$funcion= "";
			}
			if ($esAsignacionAdministrativa) {
				$funcion= "";
			}
			if ($esAsignacionMultilocalSoloLectura) {
				$funcion= "";
			}
			if ($esConceptoHistorico) {
				$funcion= "";
			}
			if ($tieneCuotasProgramadas) {
				$funcion= "alternarCuotasProgramadas(event, this)";
			}
			$resumenConciliacionUeno= ($esAsignacionAdministrativa || $esAsignacionMultilocalSoloLectura) ? array() : flujoGastoResumenConciliacionUeno($idgastos, $montoTotalPadre);
			$indicadorConciliacionUeno= ($esAsignacionAdministrativa || $esAsignacionMultilocalSoloLectura) ? "" : construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
			$botonConciliarUeno= ($esAsignacionAdministrativa || $esAsignacionMultilocalSoloLectura || $esConceptoHistorico) ? "" : construirBotonConciliarEgresoUeno($valor, $titulo);
			$botonEditarGasto= "<td style='width:4%;text-align:center;vertical-align:middle;'></td>";
			if (!$esAsignacionAdministrativa && !$esAsignacionMultilocalSoloLectura && !$esConceptoHistorico && ($idgastos != "" || $botonConciliarUeno != "")) {
				$botonEditarGasto= "<td class='flujo-ueno-acciones-cell' style='width:7%;text-align:center;vertical-align:middle;'>
					<div class='flujo-ueno-acciones'>
					<button type='button' title='Editar movimiento' aria-label='Editar movimiento' onclick='editarGastoDesdeFila(event, this)' style='border:0;background:#ffffff;border-radius:4px;width:28px;height:24px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;padding:2px;box-shadow:0 0 0 1px rgba(0,0,0,0.18);'>
						<img src='/GoodVentaAsisCap/iconos/editar.png' alt='Editar' style='width:15px;height:15px;display:block;'>
					</button>
					".$botonConciliarUeno."
					</div>
				</td>";
			}
			$styleEstado = "";
			$fechaHoy = new DateTime();
			$fechaGasto = DateTime::createFromFormat('Y-m-d', $fecha);
			if (($estado == 'solicitado' || $estado == 'pendiente') && $fechaGasto <= $fechaHoy) {
				$styleEstado= "background-color: #ff5050;color: #ffffff";
				$estado= 'solicitado';
				$registro_autorizacion_necesario= true;
			} else if ($estado == 'pendiente' || ($estado == 'solicitado' && $fechaGasto > $fechaHoy)) {
				$styleEstado= "background-color: #585f08;color: #ffffff;";
			} else if ($estado == 'Activo') {
				$styleEstado= "background-color: #085f1c;color: #ffffff;";
			}
			if ($tieneCuotasProgramadas && $resumenCuotasProgramadas && $resumenCuotasProgramadas['tipo'] == 'vencido') {
				$registro_autorizacion_necesario= true;
			}
	
			// Se formate el nombre de la interconsulta
			$interconsulta_element= $interconsulta_nombre;
			if ($cod_interConsultaFK) {
				$registrosMens= obtenerMensaje(array(
					'fecha_creacion' => "> '".(new DateTime())->format('Y-m-d H:i:s')."'",
					"cod_interConsultaFK" => $cod_interConsultaFK,
				));
				foreach ($registrosMens as $valueMens) {
					if ($valueMens['estado'] == 'activo') {
						$fechaMensaje = new DateTime(substr($valueMens['fecha_creacion'], 0, 10));
						$fechaActual = new DateTime();
						$diasRestantes = $fechaMensaje->diff($fechaActual->setTime(0, 0, 0));
						$interconsulta_element .= ' <i class="fa-solid fa-business-time" style="padding-left: 5px;font-size: 9pt;"></i>('.$diasRestantes->format('%a').')';
					}
				}
			}

			$modalidadElemento= flujoGastoTextoSeguro($modalidad);
			$modalidadLower= strtolower(trim((string)$modalidad));
			if ($tieneCuotasProgramadas) {
				$modalidadElemento= "<span class='flujo-modalidad-badge flujo-modalidad-badge--serie'>Serie de cuotas</span>";
			} else if ($modalidadLower == 'contado') {
				$modalidadElemento= "<span class='flujo-modalidad-badge flujo-modalidad-badge--aislado'>Pago aislado</span>";
			}

			$styleName=CargarStyleTable($styleName);
			$resumenCuotasFila= $tieneCuotasProgramadas
				? "<td class='cuotas-programadas-resumen'>".$indicadorCuotasProgramadas.$metaCuotasProgramadas."</td>"
				: "<td class='cuotas-programadas-resumen cuotas-programadas-resumen--vacio'></td>";
			if (flujoGastoEstadoComputableResumen($estado)) {
				$paginaImprimir .= "
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='".(($esConceptoHistorico || $esAsignacionMultilocalSoloLectura) ? "" : "obtenerdatosabmGasto(this)")."'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:10%'>".$motivo."</td>
					<td  id='td_datos_16' style='width:15%'>".$interconsulta_nombre."</td>
					<td  style='width:20%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:15%'>".$fecha."</td>
					<td  id='td_datos_8' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_21' style='width:10%'>".$usuarionombre."</td>
					<td  id='' style='width:10%'>".$nombrelocal."</td>
					<td  id='td_datos_5' style='display:none'>".$estado."</td>
					<td  id='td_datos_7' style='display:none'>".$cod_local."</td>
					<td  id='td_datos_12' style='display:none'>".$url1."</td>
					<td  id='td_datos_25' style='display:none'>".$url_documento_firmado."</td>
					<td  id='td_datos_13' style='display:none'>".$descripcionEdicion."</td>
					<td  id='td_datos_14' style='display:none'>".$motivo."</td>
					<td  id='td_datos_15' style='display:none'>".$cod_interConsultaFK."</td>
					<td  id='td_datos_17' style='display:none'>".$cod_usuario_autoriz."</td>
					<td  id='td_datos_18' style='display:none'>".$usuario_autoriz_nombre."</td>
					<td  id='td_datos_19' style='display:none'>".$fecha_autoriz."</td>
					<td  id='td_datos_20' style='display:none'>".$cod_motivoIngresoEgresoFK."</td>
					<td  id='td_datos_22' style='display:none'>".$cod_proyecto_gastoFK."</td>
					<td  id='td_datos_23' style='display:none'>".$modalidad."</td>
					<td  id='td_datos_24' style='display:none'>".$cod_gasto_padre."</td>
					<td  id='td_datos_26' style='display:none'>".$nombre_usuario_edit."</td>
					</tr>
					</table>";
			}
	
			$filaMovimientoFlujo = "<li class='list-group-item' style='padding: 0; padding-left: 0.5rem;'>
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='$funcion' style='".($estado=="Rechazado" || $estado=="Inactivo" ? "text-decoration: line-through;" : "")."'>
				".$controlCuotasProgramadas."
				<td id='td_id' style='width:5%; background-color: #efeded;color:red; $styleEstado'>".$idgastos."</td>
				<td  id='td_datos_2' style='width:10%'>".$motivo."</td>
				<td  style='width: 15%;'><div style='width: fit-content; text-decoration: underline; color: blue;' onclick='event.stopPropagation();obtenerdatosabmGasto(this.parentElement.parentElement);ventanaAnterior.push(\"divAbmGasto1\");obtenerDatosInterConsulta(this)'>".$interconsulta_element."</div></td>
				<td  id='td_datos_16' style='display: none;'>".$interconsulta_nombre."</td>
				<td  id='td_datos_1' style='display:none'>". number_format($montoTotalPadre,'0',',','.')."</td>
				<td style='width:10%'>". number_format((flujoGastoEstadoComputableResumen($estado) ? $monto : 0),'0',',','.').$indicadorConciliacionUeno."</td>
				".$resumenCuotasFila."
				".$botonEditarGasto."
				<td  id='td_datos_23' style='width:5%'>".$modalidadElemento."</td>
				<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
				<td  id='td_datos_3' style='width:15%'>".$fecha."</td>
				<td  id='td_datos_8' style='display: none;'>".$nroboleta."</td>
				<td  id='td_datos_9' style='display: none;'>".$banco."</td>
				<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
				<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
				<td  id='td_datos_21' style='width:10%'>".$usuarionombre."</td>
				<td  id='' style='width:15%'>".$nombrelocal."</td>
				<td  id='td_datos_5' style='display:none'>".$estado."</td>
				<td  id='td_datos_7' style='display:none'>".$cod_local."</td>
				<td  id='td_datos_12' style='display:none'>".$url1."</td>
				<td  id='td_datos_25' style='display:none'>".$url_documento_firmado."</td>
				<td  id='td_datos_13' style='display:none'>".$descripcionEdicion."</td>
				<td  id='td_datos_14' style='display:none'>".$motivo."</td>
				<td  id='td_datos_15' style='display:none'>".$cod_interConsultaFK."</td>
				<td  id='td_datos_17' style='display:none'>".$cod_usuario_autoriz."</td>
				<td  id='td_datos_18' style='display:none'>".$usuario_autoriz_nombre."</td>
				<td  id='td_datos_19' style='display:none'>".$fecha_autoriz."</td>
				<td  id='td_datos_20' style='display:none'>".$cod_motivoIngresoEgresoFK."</td>
				<td  id='td_datos_24' style='display:none'>".$cod_gasto_padre."</td>
				<td  id='td_datos_22' style='display:none'>".$cod_proyecto_gastoFK."</td>
				</tr>
				".$detalleCuotasProgramadas."
				</table>
			</li>";
			if ($tieneCuotasProgramadas) {
				$claveProyecto= ($cod_proyecto_gastoFK != "" && $cod_proyecto_gastoFK != "0") ? $cod_proyecto_gastoFK : "serie_".$idgastos;
				if (!isset($gruposProyectoMotivo[$claveProyecto])) {
					$nombreProyecto= ($cod_proyecto_gastoFK != "" && $cod_proyecto_gastoFK != "0")
						? flujoGastoNombreProyecto($cod_proyecto_gastoFK)
						: "Serie de cuotas ".$idgastos;
					$totalProyecto= 0;
					foreach ($gastosSerieCuotas as $gastoProyectoCuota) {
						$totalProyecto += intval(isset($gastoProyectoCuota['monto']) ? $gastoProyectoCuota['monto'] : 0);
					}
					$gruposProyectoMotivo[$claveProyecto]= array(
						'titulo' => $nombreProyecto,
						'detalle' => ($resumenCuotasProgramadas ? "Cuotas: ".$resumenCuotasProgramadas['pagadas']."/".$resumenCuotasProgramadas['total'] : ""),
						'total' => $totalProyecto,
						'html' => construirTablaCuotasProyectoFlujo($gastosSerieCuotas, $resumenCuotasProgramadas),
					);
				}
			} else {
				$pagosUnicosMotivo[]= array(
					'titulo' => "Pago unico - Ref. ".$idgastos,
					'detalle' => flujoGastoFechaCorta($fecha),
					'total' => intval($monto),
					'html' => construirPagoUnicoFlujoConcepto($valor, $titulo, $codUsuarioActual),
				);
			}
		}

		foreach ($pagosUnicosMotivo as $pagoUnicoMotivo) {
			$paginaMotivo .= construirSubgrupoFlujoConcepto($pagoUnicoMotivo['titulo'], $pagoUnicoMotivo['html'], $pagoUnicoMotivo['total'], 'pago', $pagoUnicoMotivo['detalle']);
		}
		foreach ($gruposProyectoMotivo as $grupoProyectoMotivo) {
			$paginaMotivo .= construirSubgrupoFlujoConcepto($grupoProyectoMotivo['titulo'], $grupoProyectoMotivo['html'], $grupoProyectoMotivo['total'], 'proyecto', $grupoProyectoMotivo['detalle']);
		}

		$styleRegistroColor2= $styleRegistroColor;
		if ($registro_autorizacion_necesario) {
			$styleRegistroColor2= "#ff5050;color: #ffffff;";
		}
		$botonAgregarMovimientoContextual= "";
		$botonConciliarConceptoUeno= "";
		if (!$esConceptoHistorico && $cod_motivo != -1 && $zona != 'administracion' && $zona != 'migracion') {
			$tipoMovimientoContexto= ($zona == 'ingreso') ? "Ingreso" : ($zona == 'deposito' ? "Deposito" : "Egreso");
			$botonAgregarMovimientoContextual= "<button type='button' class='flujo-concepto-add' title='Agregar movimiento a este concepto' onclick='abrirMovimientoFinancieroDesdeBotonConcepto(event, this)'"
				." data-tipo-movimiento='".flujoGastoTextoSeguro($tipoMovimientoContexto)."'"
				." data-categoria-flujo='".flujoGastoTextoSeguro($titulo)."'"
				." data-categoria-codigo='".flujoGastoTextoSeguro($zona)."'"
				." data-concepto-id='".flujoGastoTextoSeguro($cod_motivo)."'"
				." data-concepto-nombre='".flujoGastoTextoSeguro($titulo_motivo)."'>"
				."<span>+</span>"
				."</button>";
			if ($zona != 'ingreso' && $zona != 'deposito') {
				$botonConciliarConceptoUeno= "<button type='button' class='flujo-concepto-conciliar' title='Conciliar gastos pendientes de este concepto con egresos del extracto bancario' onclick='abrirConciliacionEgresoUenoDesdeConcepto(event, this)'"
					." data-cod-motivo='".flujoGastoTextoSeguro($cod_motivo)."'"
					." data-categoria-flujo='".flujoGastoTextoSeguro($titulo)."'"
					." data-concepto-nombre='".flujoGastoTextoSeguro($titulo_motivo)."'>"
					."<span>&#8644;</span>"
					."</button>";
			}
		}

 		$pagina .= '<li class="list-group-item" style="padding: 0; padding-left: 0.5rem;"><div class="card" style="width: 100%; margin: 0;gap: 0;min-height: 0;">'.
			'<div class="card-header" style="padding-bottom: 0px; padding-top: 0px;background-color: '.$styleRegistroColor2.'" type="button" onclick="mostrarItems(\''.$idMotivoCollapse.'\')">'.
				'<h6><b>'.$tituloMotivoVisual.'</b>: <span>'.number_format($totalMonto, 0, ',', '.').'</span> Gs.</h6>'.
				$botonAgregarMovimientoContextual.
				$botonConciliarConceptoUeno.
			'</div>'.
			'<div class="collapse" id="'.$idMotivoCollapse.'" style=""><ul class="list-group list-group-flush">'.
				$paginaMotivo.
			'</ul></div>'.
		'</div></li>';
	}

	$pagina .= '</ul></div>'.
		'</div>'.
	'</div>';
 }
	$pagina= construirPanelReferenciasGestionOrigen($referenciasGestionOrigen, $puedeGestionarOrigen).$pagina;
 
/*Retornamos los datos obtenidos mediante el JSON */      
$resumenComposicionFlujo= flujoGastoFinalizarResumenComposicion($resumenComposicionFlujo, $totalZonaIngresos, $totalZonaCostosDirectos, $totalZonaGastosOperativos, $totalZonaSinCategorizar, $totalZonaAdministracionAsignada, $administracionCompartida);
$informacion =array(
	"1" => "exito",
	"2" => $pagina,
	"3" => $nroRegistro,
	"4" => $totalGasto,
	"5" => $totalZonaIngresos,
	"6" => $totalZonaCostosDirectos,
	"7" => $totalZonaGastosOperativos,
	"8" => $totalZonaSinCategorizar,
	"14" => $totalZonaAdministracionAsignada,
	"15" => $totalZonaDepositosCentral,
	"9" => $registros,
	"10" => $totalEstado,
	"12" => $paginaImprimir,
	"13" => $resumenComposicionFlujo,
);
return $informacion;
}



function buscarevaluacion($fecha1,$fecha2,$cod_local)
{
	
$datosGastos=buscaregastos($fecha1,$fecha2,$cod_local);
$paginaGasto=$datosGastos[0];
$nroRegistroGasto=$datosGastos[1];
$totalGasto=$datosGastos[2];
$datosPagos=buscarpagos($fecha1,$fecha2,$cod_local);
$paginaPagos=$datosPagos[0];
$totalPagos=$datosPagos[1];
$nroRegistroPagos=$datosPagos[2];
$datosEntregas=buscarpagosEntregas($fecha1,$fecha2,$cod_local);
$paginaEntrega=$datosEntregas[0];
$totalEntrega=$datosEntregas[1];
$nroRegistroEntrega=$datosEntregas[2];
// $datosVentas=buscarproductovendidos($fecha1,$fecha2,$cod_local,"CREDITO");
// $paginaVentas=$datosVentas[0];
// $totalventas=$datosVentas[1];
// $nroRegistroVentas=$datosVentas[2];
// $datosVentasContado=buscarproductovendidos($fecha1,$fecha2,$cod_local,"CONTADO");
// $paginaVentasContado=$datosVentasContado[0];
// $totalventasContado=$datosVentasContado[1];
// $nroRegistroVentasContado=$datosVentasContado[2];
$paginaVentas=0;
$totalventas=0;
$nroRegistroVentas=0;
$paginaVentasContado=0;
$totalventasContado=0;
$nroRegistroVentasContado=0;
$datosCompras=buscarproductocomprados($fecha1,$fecha2,$cod_local);
$paginaVentasCompras=$datosCompras[0];
$totalCompras=$datosCompras[1];
$nroRegistroCompras=$datosCompras[2];
$datosProductosVen= buscarproductovendidos($fecha1,$fecha2,$cod_local);
$paginaProductosVend=$datosProductosVen[0];
$totalProductoVend=$datosProductosVen[1];
$nroRegistroProductoVend=$datosProductosVen[2];



$Saldo=($totalPagos+$totalEntrega)-$totalGasto;

$totalGasto=number_format($totalGasto,'0',',','.');
$totalPagos=number_format($totalPagos,'0',',','.');
$totalEntrega=number_format($totalEntrega,'0',',','.');
$totalventas=number_format($totalventas,'0',',','.');
$totalventasContado=number_format($totalventasContado,'0',',','.');
$totalCompras=number_format($totalCompras,'0',',','.');
$totalProductoVend=number_format($totalProductoVend,'0',',','.');
$Saldo=number_format($Saldo,'0',',','.');

  
$informacion =array("1" => "exito","2" => $paginaGasto,"3" => $totalGasto,"4" => $nroRegistroGasto
,"5" => $paginaPagos,"6" => $totalPagos,"7" => $nroRegistroPagos
,"8" => $paginaEntrega,"9" => $totalEntrega,"10" => $nroRegistroEntrega
,"11" => $paginaVentas,"12" => $totalventas,"13" => $nroRegistroVentas,"14" => $Saldo
,"15" => $paginaVentasContado,"17" => $totalventasContado,"16" => $nroRegistroVentasContado
,"18" => $paginaVentasCompras,"19" => $totalCompras,"20" => $nroRegistroCompras
,"21" => $paginaProductosVend,"22" => $totalProductoVend,"23" => $nroRegistroProductoVend);
echo json_encode($informacion);	
exit;
}

function buscarevaluacionGasto($fecha1,$fecha2,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicionCodLocal=" and g.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		$sql= "Select monto,motivo,fecha,estado,cod_usuario,idgastos,personales,cod_local,
		(Select nombre_persona from persona where cod_persona=cod_usuario) as usuarionombre,
		(Select Nombre from local l where l.cod_local=g.cod_local ) as nombrelocal
		from gastos g where fecha>='$fecha1' and fecha<='$fecha2' and estado='activo' AND LOWER(TRIM(IFNULL(tipo,'')))!='deposito' ".$condicionCodLocal;
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
		  	  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
		  	  $motivo=mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $personales=mb_convert_encoding((string)($valor['personales']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_local=mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	 $totalGasto=$totalGasto+$monto;
		  	 
			    	 
		  	  $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)'>
<td  id='td_datos_2' style='width:10%'>".$motivo."</td>
<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".$personales."</td>
<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
<td  id='td_datos_4' style='width:10%'>".$usuarionombre."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
			  
			  
	  }
 }

 
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalGasto,'0',',','.'));
echo json_encode($informacion);	
exit;

}

/*Buscar */
function evaluacionpagosventa($fecha1,$fecha2,$cod_local)
{
$mysqli=conectar_al_servidor();
 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }

	
$sql= "select pg.idPago,pg.nrofactura, pg.Fecha, pg.Monto,pg.cod_venta_fk, pg.comision, pg.lot, pg.lat,(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as nombrecliente,
(Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK) as cobradornombre,date_format(hora ,'%H:%i' ) as hora,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
vt.num_factura,vt.puntoexpedicion,
(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=pg.cod_venta_fk)) as nombrezona
 from  pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk 
 where Fecha>='$fecha1' and Fecha<='$fecha2' ".$condicionCodLocal." group by  pg.idPago ";/*Sentencia para buscar registros*/	
	




 $pagina = "";   
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$cod_venta = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');      
$nombrezona = mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1');      
$hora = mb_convert_encoding((string)($valor['hora']), 'UTF-8', 'ISO-8859-1');      
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1');      
$lot = mb_convert_encoding((string)($valor['lot']), 'UTF-8', 'ISO-8859-1');      
$lat = mb_convert_encoding((string)($valor['lat']), 'UTF-8', 'ISO-8859-1');      
$nombrecliente = mb_convert_encoding((string)($valor['nombrecliente']), 'UTF-8', 'ISO-8859-1');      
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');      
$nrofactura = mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1');      
$totalPagado=$Monto+$totalPagado;
 	$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}	

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  >
<td id='td_datos_3' style='width:10%'>".$nrof."</td>
<td id='' style='width:10%' >".$Fecha." ".$hora."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:10%'>".$nombrezona."</td>
<td id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalPagado,'0',',','.'));
echo json_encode($informacion);	
exit;
}


function evaluacionproductodcomprados($fecha1,$fecha2,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	  $condicionCodLocal=" and cpr.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		$sql= "Select sum(dc.cantidad_detalle_compra) as totalCantidad,pro.cod_producto
		,sum(dc.subTotal) as totalCompra,dc.precio_producto as precio_producto
		,dc.cod_productoFK,pro.nombre_producto
		,(select descripcion from marcas where cod_marcas= pro.cod_marcasFK limit 1 ) as NombreMarca
		,(Select Nombre from local l where l.cod_local=cpr.cod_local) as nombrelocal
		from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK inner join compra cpr on cpr.cod_compra=dc.cod_compraFK
		where fecha_compra>='".$fecha1."' and fecha_compra<='".$fecha2."'  ".$condicionCodLocal." group by pro.cod_producto,dc.precio_producto";
		$total_compra=0;
		$nroRegistro=0;
   
   
   $stmt = $mysqli->prepare($sql);
  

if ( ! $stmt->execute()) {
   echo "Error";
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
		  
		  
		      $totalCantidad=$valor['totalCantidad'];
		      $totalCompra=$valor['totalCompra'];
		  	  $nombre_producto=mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_producto=mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');
		  	  $NombreMarca=mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	  $precio_producto=mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1');
		  	
		  	
		  	 $total_compra=$totalCompra+$total_compra;
			    	 
		  	  $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='' style='width:10%'>".$cod_producto."</td>
<td  id='' style='width:15%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id=''  style='width:10%'>".number_format($totalCantidad,'2',',','.')."</td>
<td  id=''  style='width:10%'>".number_format($precio_producto,'0',',','.')."</td>
<td  id=''  style='width:10%'>".number_format($totalCompra,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
			  
			  
	  }
 }
 

 
 $informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($total_compra,'0',',','.'));
echo json_encode($informacion);	
exit;

}

function evaluacionpagoscomprados($fecha1,$fecha2,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	  $condicionCodLocal=" and cpr.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		$sql= "Select pg.monto,pg.fechadelpago,pg.fechaapagar,pg.tipo,cpr.num_comprobante
		,(Select Nombre from local l where l.cod_local=cpr.cod_local) as nombrelocal
		from pagosdecompra pg inner join compra cpr on cpr.cod_compra=pg.cod_compraFk
		where pg.fechadelpago>='".$fecha1."' and pg.fechadelpago<='".$fecha2."' and pg.estado='Pagado'  ".$condicionCodLocal."";
		
		
		$total_compra=0;
		$nroRegistro=0;
   
   
   $stmt = $mysqli->prepare($sql);
  

if ( ! $stmt->execute()) {
   echo "Error";
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
		  
		  
		      $monto=$valor['monto'];
		      $fechadelpago=mb_convert_encoding((string)($valor['fechadelpago']), 'UTF-8', 'ISO-8859-1');
		  	  $fechaapagar=mb_convert_encoding((string)($valor['fechaapagar']), 'UTF-8', 'ISO-8859-1');
		  	  $tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
		  	  $num_comprobante=mb_convert_encoding((string)($valor['num_comprobante']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	
		  	
		  	
		  	 $total_compra=$total_compra+$monto;
			    	 
		  	  $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='' style='width:10%'>".number_format($monto,'0',',','.')."</td>
<td  id='' style='width:10%'>".$fechadelpago."</td>
<td  id='' style='width:10%'>".$fechaapagar."</td>
<td  id='' style='width:10%'>".$tipo."</td>
<td  id='' style='width:10%'>".$num_comprobante."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
			  
			  
	  }
 }
 

 
 $informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($total_compra,'0',',','.'));
echo json_encode($informacion);	
exit;

}


function  evaluacionproductodvendidos($fecha1,$fecha2,$cod_local)
{
$mysqli=conectar_al_servidor();
	 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		
$sql= "select pr.cod_producto,pr.nombre_producto,
sum(dtv.cantidad_detalle) as totalCantidad,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
sum(dtv.cantidad_detalle*dtv.precio_producto) as totalVenta,
sum(dtv.cantidad_detalle*dtv.subPrecioCompra) as totalCosto,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where vt.fecha_venta>='".$fecha1."' and vt.fecha_venta<='".$fecha2."'
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
 ".$condicionCodLocal." group by pr.cod_producto ";/*Sentencia para buscar registros*/

$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$totalventas = "0";   
$totalinvertido = "0";   
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;
$styleName="tableRegistroSearch";



if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$totalCantidad = mb_convert_encoding((string)($valor['totalCantidad']), 'UTF-8', 'ISO-8859-1');          
$totalVenta = mb_convert_encoding((string)($valor['totalVenta']), 'UTF-8', 'ISO-8859-1'); 
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'); 
$totalCosto = mb_convert_encoding((string)($valor['totalCosto']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 

$totalventas=$totalVenta+$totalventas;
$totalinvertido=$totalinvertido+$totalCosto;

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'   >
<td id='' style='width:10%'>".$cod_producto."</td>
<td id='' style='width:20%'>".$nombre_producto."</td>
<td id='' style='width:15%'>".$NombreMarca."</td>
<td  id='' style='width:10%'>".number_format($totalCantidad,'2',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($totalVenta,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


}
}
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalventas,'0',',','.'));
echo json_encode($informacion);	
exit;
}


function buscarabmmotivoingresoegreso($buscar,$Estado,$cod_motivo= 0)
{
  	$ss='';
	$sqlFiltro = "";
	$parametros= array();
	$ss= "";
	if (!empty($Estado)) {
		$sqlFiltro .= " estado=? and";
		$ss .= 's';
		$parametros[] = $Estado;
	}
	if (!empty($buscar)) {
		$sqlFiltro .= " descripcion like ? and";
		$ss .= 's';
		$parametros[] = "%".$buscar."%";
	}
	if (!empty($cod_motivo) && $cod_motivo > 0) {
		$sqlFiltro .= " cod_motivo_ingreso_egreso = ? and";
		$ss .= 'i';
		$parametros[] = $cod_motivo;
	}

	// Limpia el filtro sql
	if ($sqlFiltro != "") {
		$sqlFiltro = "where ". substr($sqlFiltro, 0, -3);
	}

	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select *
        from motivos_ingreso_egreso $sqlFiltro order by FIELD(estado, 'activo','inactivo'), FIELD(categoria, 'ingreso', 'directo','operativo'), categoria IS NULL,descripcion asc ";
	$stmt = $mysqli->prepare($sql);

if ($ss != "") {
	$refs = [];
	foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}
	call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
}

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 $styleName="tableRegistroSearch";
 $registros= array();
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {		  
		      $cod_motivo_ingreso_egreso=$valor['cod_motivo_ingreso_egreso'];
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			  $categoria= mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1');
			  $necesita_autorizacion = mb_convert_encoding((string)($valor['necesita_autorizacion']), 'UTF-8', 'ISO-8859-1');

			  $registros[] = array(
					"cod_motivo_ingreso_egreso" => $cod_motivo_ingreso_egreso,
					"descripcion" => $descripcion,
					"estado" => $estado,
					"categoria" => $categoria,
					"necesita_autorizacion" => $necesita_autorizacion,
			  );

			  switch ($categoria) {
				case 'operativo':
					$categoria= "Costo fijo";
					break;
				case 'directo':
					$categoria= "Gasto Variable";
					break;
				case 'ingreso':
					$categoria= "Ingreso";
					break;
			  }
		  	 
			  $styleName=CargarStyleTable($styleName);
			  $pagina.="
			  <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' onclick='ObtenerdatosAbmMotivoEgresoIngreso(this)'>
			  <td id='td_id' style='display:none;'>".$cod_motivo_ingreso_egreso."</td>
			  <td id='td_datos_1'style='width:60%' class='tdRegistroSearch' >".$descripcion."</td>
			   <td  id='td_datos_2' style='display:none'>".$estado."</td>
			   <td id='td_datos_3' style='width:40%' class='tdRegistroSearch' >".ucfirst($categoria)."</td>
			   <td  id='td_datos_4' style='display:none'>".$necesita_autorizacion."</td>
			  </tr>
			  </table>";
			
			
	  }
 }
 
 
  $informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta, "4" => $registros);
  return $informacion;
}

function NuevoMotivo($motivo,$estado,$categoria, $necesita_autorizacion)
{
	
if($motivo==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

if (esConceptoDepositoCentral($motivo)) {
	$stmtReservado= $mysqli->prepare("SELECT cod_motivo_ingreso_egreso FROM motivos_ingreso_egreso WHERE UPPER(TRIM(descripcion))='DEPOSITO BANCARIO - FARAONE CAPITAL S.A.' LIMIT 1");
	if (!$stmtReservado || !$stmtReservado->execute()) {
		if ($stmtReservado) {
			$stmtReservado->close();
		}
		mysqli_close($mysqli);
		$informacion= array("1" => "error", "2" => "No se pudo validar el concepto reservado de depositos a central.");
		echo json_encode($informacion);
		exit;
	}
	if ($stmtReservado->get_result()->num_rows > 0) {
		$stmtReservado->close();
		mysqli_close($mysqli);
		$informacion= array("1" => "error", "2" => "El concepto de depositos a central ya existe y debe conservarse como registro unico.");
		echo json_encode($informacion);
		exit;
	}
	if ($stmtReservado) {
		$stmtReservado->close();
	}
}

$consulta1="Insert into motivos_ingreso_egreso (descripcion,estado,categoria,necesita_autorizacion) values (upper(?),?, ?, ?)";
$stmt = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt->bind_param($ss,$motivo,$estado,$categoria,$necesita_autorizacion);

if (!$stmt->execute()) {
	echo "$consulta1\n$motivo\n";
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function editarMotivo($motivo,$estado,$categoria,$necesita_autorizacion,$cod_usuarioFK,$idabm)
{
	
if($motivo==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$fechaActual= new DateTime();
$fechaActual=date_format($fechaActual,"Y-m-d H:i:s");

$mysqli=conectar_al_servidor();

$conceptoActualEsDeposito= esCodigoConceptoDepositoCentral($mysqli, $idabm);
$conceptoNuevoEsDeposito= esConceptoDepositoCentral($motivo);
if ($conceptoActualEsDeposito != $conceptoNuevoEsDeposito) {
	mysqli_close($mysqli);
	$informacion= array("1" => "error", "2" => "El nombre del concepto de depositos a central es reservado y no puede modificarse ni asignarse a otro concepto.");
	echo json_encode($informacion);
	exit;
}

$consulta1="update motivos_ingreso_egreso SET fecha_edit= '$fechaActual', cod_usuarioFK= $cod_usuarioFK, descripcion = upper('$motivo'), estado ='$estado', categoria= '$categoria', necesita_autorizacion='$necesita_autorizacion' WHERE cod_motivo_ingreso_egreso ='$idabm'";
$stmt = $mysqli->prepare($consulta1);

if (!$stmt->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function obtenerEtiquetaCategoriaMotivo($categoria)
{
	switch ($categoria) {
		case 'ingreso':
			return 'Ingresos';
		case 'directo':
			return 'Costos Variables';
		case 'operativo':
			return 'Gastos Fijos';
		default:
			return 'Sin Categorizar';
	}
}

function buscaroption($categoriaFiltro= '')
{
	$mysqli=conectar_al_servidor();
	$categoriasPermitidas= array('ingreso', 'directo', 'operativo');
	$categoriaFiltro= trim((string)$categoriaFiltro);
	if (!in_array($categoriaFiltro, $categoriasPermitidas)) {
		$categoriaFiltro= '';
	}

	$sqlFiltroCategoria= "";
	if ($categoriaFiltro != "") {
		$sqlFiltroCategoria= " and categoria=?";
	}

	$sql= "Select * from motivos_ingreso_egreso where estado='activo' $sqlFiltroCategoria order by FIELD(categoria, 'ingreso', 'directo', 'operativo'), categoria IS NULL, descripcion asc";

	$pagina="<option  value='' >SELECCIONAR</option>";
   $paginaList = "";
   $stmt = $mysqli->prepare($sql);
	if ($categoriaFiltro != "") {
		$stmt->bind_param('s', $categoriaFiltro);
	}

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $categoriaActual= "";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		      $cod_motivo_ingreso_egreso=$valor['cod_motivo_ingreso_egreso'];
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
			  $categoria= mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1');
			  if ($categoria == "") {
				$categoria= "sinCategoria";
			  }

			  if ($categoriaFiltro == "" && $categoriaActual != $categoria) {
				if ($categoriaActual != "") {
					$pagina.="</optgroup>";
				}
				$categoriaActual= $categoria;
				$pagina.="<optgroup label='".obtenerEtiquetaCategoriaMotivo($categoria)."'>";
			  }

			  $pagina.="<option  value='$cod_motivo_ingreso_egreso' data-categoria='".$categoria."' >".$descripcion."</option>";
			  
			  $paginaList.="<option id='$cod_motivo_ingreso_egreso' value='".$descripcion."'></option>";	
	  }
 }
 if ($categoriaFiltro == "" && $categoriaActual != "") {
	$pagina.="</optgroup>";
 }
 
 

 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>$paginaList);
echo json_encode($informacion);	
exit;

}

function agregarLimiteCaja($cod_usuarioF, $limite_monto) {
	$mysqli=conectar_al_servidor();

	$consulta1="Insert into limite_caja (cod_usuarioFK, limite_monto) values (?, ?)";
	$stmt = $mysqli->prepare($consulta1);
	$ss='ss';
	$stmt->bind_param($ss,$cod_usuarioF, $limite_monto);

	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	$mysqli->close();
	$informacion =array("1" => "exito");
	echo json_encode($informacion);	
	exit;
}

function obtenerLimiteCaja() {
	$mysqli=conectar_al_servidor();

	$consulta1="SELECT * FROM limite_caja ORDER BY fecha_registro DESC LIMIT 1";
	$stmt = $mysqli->prepare($consulta1);

	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	$registros=$stmt->get_result();
	$registros= $registros->fetch_all(MYSQLI_ASSOC);

	if (!($registros)) {
		$registros= array();
	}

	$mysqli->close();

	return $registros;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	$operacion = $_POST['funt'];
	$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
	verificarOperacionGasto($operacion);
}
?>

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

if($operacion=="obtener_crear_interconsulta_movimiento")
{
	$motivo= isset($_POST['motivo']) ? mb_convert_encoding((string)($_POST['motivo']), 'ISO-8859-1', 'UTF-8') : '';
	$tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : 'Egreso';
	$cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : '';
	$cod_motivo= isset($_POST['cod_motivoFK']) ? mb_convert_encoding((string)($_POST['cod_motivoFK']), 'ISO-8859-1', 'UTF-8') : '';
	if (trim($motivo) == '' && is_numeric($cod_motivo)) {
		$registrosMotivo= buscarabmmotivoingresoegreso('', 'activo', $cod_motivo);
		if (isset($registrosMotivo[4][0]['descripcion'])) {
			$motivo= $registrosMotivo[4][0]['descripcion'];
		}
	}
	$cod_interConsulta= obtenerOCrearInterConsultaMovimientoFinanciero($motivo, $tipo, $user, $cod_local);
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
if (is_numeric($cod_motivo) && (int)$cod_motivo > 0) {
	$motivoSeleccionado = buscarabmmotivoingresoegreso('', 'activo', (int)$cod_motivo);
	if (isset($motivoSeleccionado[4][0]['descripcion'])
		&& strtoupper(trim((string)$motivoSeleccionado[4][0]['descripcion'])) == 'DEPOSITO BANCARIO - FARAONE CAPITAL S.A.') {
		$tipo = 'Egreso';
		$motivo = 'Deposito bancario a Faraone Capital S.A.';
	}
}

$cod_interConsultaFK= $_POST['cod_interConsultaFK'];
$cod_interConsultaFK= mb_convert_encoding((string)($cod_interConsultaFK), 'ISO-8859-1', 'UTF-8');

$editar_cuotas= $_POST['editar_cuotas'];
$editar_cuotas= mb_convert_encoding((string)($editar_cuotas), 'ISO-8859-1', 'UTF-8');

$cod_proyecto_gastoFK= isset($_POST['cod_proyecto_gastoFK']) ? $_POST['cod_proyecto_gastoFK'] : '';
$cod_proyecto_gastoFK= mb_convert_encoding((string)($cod_proyecto_gastoFK), 'ISO-8859-1', 'UTF-8');
if (!is_numeric($cod_proyecto_gastoFK)) {
	$cod_proyecto_gastoFK= NULL;
}

	// Comprueba si esta dentro del presupuesto
	$fechaRango= DateTime::createFromFormat('Y-m-d', $fecha);
	$primerDiaMes= $fechaRango->format('Y-m-01');
	$ultimoDiaMes= $fechaRango->format('Y-m-t');

	$monto_limite = obtenerPresupuestoMotivoGasto(array(
		'cod_motivo_ingreso_egresoFK' => $cod_motivo,
		'cod_localFK' => $cod_local,
	));
	if (count($monto_limite) > 0) {
		$monto_limite= $monto_limite[0]["monto_limite"];
		$estado= ($estado == "Inactivo" ? "Inactivo" : "Activo");
		$informacion2 = buscarGastoConMotivos('', $primerDiaMes, $ultimoDiaMes, ($operacion == 'editar' ? "Activo and g.idgastos != $idgastos" : $estado), $cod_local, '', '', '','true', $cod_motivo, '', '','', '', '');
	
		if ($monto_limite && $monto_limite != '0')
		$totalGasto= intval(str_replace('.', '', $informacion2["4"])) + $monto;
		$monto_limite= intval($monto_limite);
		if ($monto_limite > 0 && $totalGasto > $monto_limite) {
			$informacion =array("1" => "exito", "2" => "El gasto supera el presupuesto establecido para el motivo.");
			echo json_encode($informacion);	
			exit;
		}
	}

	if($operacion=="editar")
	{
		registrarSolicitudEliminacionGenerica(
			"gastos",
			"idgastos",
			$idgastos,
			"Solicitud automatica por edicion de gasto.",
			$user,
			"archivo: abmgasto.php | funcion: verificarOperacionGasto | funt: editar | idgastos: ".$idgastos." | monto: ".$monto." | motivo: ".$motivo." | fecha: ".$fecha." | estado: ".$estado." | cod_local: ".$cod_local." | tipo: ".$tipo,
			"estado",
			$estado
		);
	}

	$informacion= abmGasto($Arreglo,$nroboleta, $banco , $nrocuenta ,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion,$editar_cuotas, $cod_proyecto_gastoFK);
	echo json_encode($informacion);	
	exit;
}
if ($operacion=='cargar_imagen') {
	$idgastos=$_POST['idgastos'];
	$foto=$_POST['foto'];
	$ext=$_POST['ext'];
	$foto_documento_firmado= isset($_POST['foto_documento_firmado']) ? $_POST['foto_documento_firmado'] : '';
	$ext_documento_firmado= isset($_POST['ext_documento_firmado']) ? $_POST['ext_documento_firmado'] : '';
	subirImagenGasto($idgastos, $foto, $ext);
	subirDocumentoFirmadoGasto($idgastos, $foto_documento_firmado, $ext_documento_firmado);
	$informacion =array("1" => "exito", "2" => $idgastos);
	echo json_encode($informacion);	
	exit;
}

if($operacion=="buscar")
{
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
	if($cod_local==""){
	$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
		if($controllocal==0){
			$cod_local=buscarlocaluser($user);
		}
	}
	$idgastos= "";

	$informacion = buscarGastoConMotivos($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, '', '', $idgastos);
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
	$fecha_inicio= mb_convert_encoding((string)($_POST['fecha_inicio']), 'ISO-8859-1', 'UTF-8');
	$fecha_fin= mb_convert_encoding((string)($_POST['fecha_fin']), 'ISO-8859-1', 'UTF-8');

	buscarResumenGastosMotivo($fecha_inicio, $fecha_fin);
}



if ($operacion == "buscarProximosPagos") {
	$fecha_inicio= mb_convert_encoding((string)($_POST['fecha1']), 'ISO-8859-1', 'UTF-8');
	$fecha_fin= mb_convert_encoding((string)($_POST['fecha2']), 'ISO-8859-1', 'UTF-8');
	$local= mb_convert_encoding((string)($_POST['local']), 'ISO-8859-1', 'UTF-8');
	$descripcion= mb_convert_encoding((string)($_POST['descripcion']), 'ISO-8859-1', 'UTF-8');
	$estadoFiltroPagoprogrtamado= mb_convert_encoding((string)($_POST['estadoFiltroPagoprogrtamado']), 'ISO-8859-1', 'UTF-8');

	buscarProximosPagos($fecha_inicio,$fecha_fin,$local,$descripcion,$estadoFiltroPagoprogrtamado);
}

if ($operacion == "obtenerGastosAsociados") {
	$idgastos= mb_convert_encoding((string)($_POST['idgastos']), 'ISO-8859-1', 'UTF-8');
	
	$gastos= obtenerGastosAsociados($idgastos);

	$total_pendiente= 0;
	// Prepara la vista
	$pagina= "";
	foreach ($gastos as $key => $gast) {
		$estadoOriginalGasto= strtolower(trim((string)(isset($gast['estado']) ? $gast['estado'] : '')));
		$gastoPagado= ($estadoOriginalGasto == 'activo');
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
				if ($fechaActual >= $fechaGasto) {
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
				if ($fechaActual >= $fechaGasto) {
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
		if (!flujoGastoEstaAnulado($gast) && !$gastoPagado) {
			$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno(isset($gast['idgastos']) ? $gast['idgastos'] : '', isset($gast['monto']) ? $gast['monto'] : 0);
			$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
			$botonConciliarUeno= construirBotonConciliarEgresoUeno($gast, 'Extracto de pago');
		}

		$pagina .= "<table border='1' cellspacing='1' cellpadding='5' class='tableRegistroSearch2'><tr id='tbSelecRegistro' id='tbSelecRegistro' onclick='seleccionarGastosAsociados(this);' style='".($estado=="Rechazado" || $estado=="Inactivo" ? "text-decoration: line-through;" : "").";text-align: center;'>
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

	echo json_encode(array("1" => "exito", "2" => $pagina, "3" => (isset($gastos[0]) ? $gastos[0] : null), "4" => (isset($gastos[0]) ? $gastos[0]['descripcion'] : null), "5" => number_format($total_pendiente, 0, ',', '.'), "6" => count($gastos)));
	exit;
}

}


function mostrarDiagnosticoObtenerGastosAsociados($idgastos, $motivo, $extra= array()) {
	$mysqli= conectar_al_servidor();
	$diagnostico= array(
		'motivo' => $motivo,
		'idgastos_recibido' => $idgastos,
		'idgastos_var_export' => var_export($idgastos, true),
		'idgastos_strlen' => strlen((string)$idgastos),
		'idgastos_hex' => bin2hex((string)$idgastos),
		'post' => $_POST,
		'extra' => $extra,
	);

	$consultas= array(
		'conexion' => "SELECT DATABASE() AS base_actual, @@hostname AS host, @@version AS version_mysql, CONNECTION_ID() AS connection_id",
		'gasto_por_id_string' => "SELECT idgastos, monto, motivo, fecha, estado, cod_gasto_padre, cod_proyecto_gastoFK, cod_interConsultaFK FROM gastos WHERE idgastos = ?",
		'gasto_por_id_int' => "SELECT idgastos, monto, motivo, fecha, estado, cod_gasto_padre, cod_proyecto_gastoFK, cod_interConsultaFK FROM gastos WHERE idgastos = ?",
		'gasto_con_filtro_buscarGasto' => "SELECT idgastos, monto, motivo, fecha, estado, cod_gasto_padre, cod_proyecto_gastoFK, cod_interConsultaFK FROM gastos WHERE estado != 'Inactivo' AND idgastos = ?",
	);

	foreach ($consultas as $nombre => $sql) {
		$stmt= $mysqli->prepare($sql);
		if (!$stmt) {
			$diagnostico[$nombre]= "Error prepare: ".$mysqli->error;
			continue;
		}
		if ($nombre == 'gasto_por_id_string' || $nombre == 'gasto_con_filtro_buscarGasto') {
			$stmt->bind_param('s', $idgastos);
		}
		if ($nombre == 'gasto_por_id_int') {
			$idgastosInt= intval($idgastos);
			$stmt->bind_param('i', $idgastosInt);
		}
		if (!$stmt->execute()) {
			$diagnostico[$nombre]= "Error execute: ".$stmt->error;
			$stmt->close();
			continue;
		}
		$diagnostico[$nombre]= $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
	}

	$filaGasto= isset($diagnostico['gasto_por_id_int'][0]) ? $diagnostico['gasto_por_id_int'][0] : null;
	if ($filaGasto && !empty($filaGasto['cod_proyecto_gastoFK'])) {
		$sql= "SELECT idgastos, fecha, estado, cod_gasto_padre, cod_proyecto_gastoFK, motivo FROM gastos WHERE cod_proyecto_gastoFK = ? ORDER BY fecha ASC, idgastos DESC";
		$stmt= $mysqli->prepare($sql);
		$stmt->bind_param('i', $filaGasto['cod_proyecto_gastoFK']);
		$stmt->execute();
		$diagnostico['gastos_del_proyecto']= $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
	}
	if ($filaGasto && !empty($filaGasto['cod_gasto_padre'])) {
		$sql= "SELECT idgastos, fecha, estado, cod_gasto_padre, cod_proyecto_gastoFK, motivo FROM gastos WHERE idgastos = ?";
		$stmt= $mysqli->prepare($sql);
		$stmt->bind_param('i', $filaGasto['cod_gasto_padre']);
		$stmt->execute();
		$diagnostico['gasto_padre']= $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
	}

	echo "<pre style='white-space: pre-wrap; font-size: 13px;'>";
	echo htmlspecialchars(print_r($diagnostico, true), ENT_QUOTES, 'UTF-8');
	echo "</pre>";
	exit;
}

function obtenerGastosAsociados($idgastos) {
	$result= array();
	// Obtenemos el registro base aunque este inactivo, porque desde el se resuelve el proyecto/padre.
	$result = buscarGasto('','','','','','','','','false','','','','','', $idgastos);
	if (count($result) < 1) {
		error_log("obtenerGastosAsociados sin resultado. idgastos=[".$idgastos."] POST=".json_encode($_POST));
		mostrarDiagnosticoObtenerGastosAsociados($idgastos, 'buscarGasto inicial no devolvio registros');
	}
	$regGasto= $result[0];

	// Se verifica si es cuota y se obtiene el gasto padre
	if ($regGasto['cod_gasto_padre']) {
		$result= buscarGasto('','','','','','','','','false','','','','','', $regGasto['cod_gasto_padre']);
		if (count($result) < 1) {
			error_log("obtenerGastosAsociados sin gasto padre. idgastos=[".$idgastos."] cod_gasto_padre=[".$regGasto['cod_gasto_padre']."] POST=".json_encode($_POST));
			mostrarDiagnosticoObtenerGastosAsociados($idgastos, 'buscarGasto no encontro el gasto padre', array('regGasto' => $regGasto));
		}
		$regGasto= $result[0];
	}

	// Se verifica si tiene un proyecto asociado
	if ($regGasto['cod_proyecto_gastoFK']) {
		$gastosProyecto= buscarGasto('','','','','','','','','true','','','','','', '', 'ASC', $regGasto['cod_proyecto_gastoFK']);
		return (count($gastosProyecto) > 0 ? $gastosProyecto : $result);
	}

	// Se evalua si existen gastos asociados
	$gastos_asociados= buscarGasto('','','','','','','','','true','','','','',$regGasto['idgastos'], '','ASC');

	return array_merge($result, $gastos_asociados);
}
function darBajaCuotaProgramada($idgastos, $alcance, $codUsuario) {
	$mysqli= conectar_al_servidor();
	$idgastos= intval($idgastos);
	$alcance= in_array($alcance, array('serie', 'hilo')) ? $alcance : 'cuota';
	if ($idgastos <= 0) {
		echo json_encode(array('1'=>'error', '2'=>'Cuota no valida.'));
		exit;
	}
	$mysqli->begin_transaction();
	$stmt= $mysqli->prepare("SELECT idgastos, fecha, estado, modalidad, cod_gasto_padre, cod_interConsultaFK FROM gastos WHERE idgastos=? FOR UPDATE");
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
	if ($alcance == 'hilo') {
		$codInterConsulta= intval($cuota['cod_interConsultaFK']);
		if ($codInterConsulta <= 0) {
			$mysqli->rollback();
			echo json_encode(array('1'=>'error', '2'=>'La cuota no esta vinculada a un hilo.'));
			exit;
		}
		$stmt= $mysqli->prepare("SELECT idgastos FROM gastos WHERE cod_interConsultaFK=? AND modalidad='credito' AND estado IN ('pendiente','solicitado') ORDER BY fecha,idgastos FOR UPDATE");
		$stmt->bind_param('i', $codInterConsulta);
		$stmt->execute();
		$result= $stmt->get_result();
		$ids= array();
		while ($fila= $result->fetch_assoc()) { $ids[]= intval($fila['idgastos']); }
		$stmt->close();
	} else if ($alcance == 'serie') {
		$idSerie= intval($cuota['cod_gasto_padre']);
		if ($idSerie <= 0) { $idSerie= $idgastos; }
		$fechaDesde= $cuota['fecha'];
		$stmt= $mysqli->prepare("SELECT idgastos FROM gastos WHERE (idgastos=? OR cod_gasto_padre=?) AND fecha>=? AND estado IN ('pendiente','solicitado') ORDER BY fecha,idgastos FOR UPDATE");
		$stmt->bind_param('iis', $idSerie, $idSerie, $fechaDesde);
		$stmt->execute();
		$result= $stmt->get_result();
		$ids= array();
		while ($fila= $result->fetch_assoc()) { $ids[]= intval($fila['idgastos']); }
		$stmt->close();
	}
	if (count($ids) < 1) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'No hay cuotas pendientes para dar de baja.'));
		exit;
	}
	$listaIds= implode(',', array_map('intval', $ids));
	$usuarioEditor= intval($codUsuario);
	$ok= $mysqli->query("UPDATE gastos SET estado='Baja', cod_usuarioFK_edit=".$usuarioEditor." WHERE idgastos IN (".$listaIds.") AND estado IN ('pendiente','solicitado')");
	if (!$ok) {
		$mysqli->rollback();
		echo json_encode(array('1'=>'error', '2'=>'No se pudo actualizar las cuotas.'));
		exit;
	}
	$mysqli->query("UPDATE mensaje m INNER JOIN gastos g ON g.cod_mensajeFK=m.cod_mensaje SET m.estado='inactivo' WHERE g.idgastos IN (".$listaIds.")");
	$mysqli->commit();
	echo json_encode(array('1'=>'exito', '2'=>count($ids), '3'=>$alcance));
	exit;
}

function buscarProximosPagos($fecha_inicio,$fecha_fin,$local,$descripcion,$estadoFiltroPagoprogrtamado)
{
    date_default_timezone_set('America/Asuncion');

	$fechahoy = date("Y-m-d");

    $mysqli = conectar_al_servidor();
	
	$condicionFecha="";
	if($fecha_inicio!=""  && $fecha_fin!=""){
		$condicionFecha=" and g.fecha between '$fecha_inicio' and '$fecha_fin' ";
	}
	
	$condicionLocal="";
	if($local!="" ){
		$condicionLocal=" and cod_localFK = '$local'  ";
	}
	$condiciondescripcion="";
	if($descripcion!="" ){
		$condiciondescripcion=" and asunto like '%$descripcion%'  ";
	}
	
	$condicionestadoFiltroPagoprogrtamado="";
	if($estadoFiltroPagoprogrtamado!="Todo"){
		$condicionestadoFiltroPagoprogrtamado=" and g.estado IN ('pendiente','solicitado')";
	}
	 

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
        (SELECT Nombre FROM local WHERE cod_local = cod_localFK) AS Nombrelocal
    FROM gastos g
    INNER JOIN interconsulta ic 
        ON g.cod_interConsultaFK = ic.cod_interConsulta
    WHERE g.monto!='' $condicionFecha $condicionLocal $condiciondescripcion $condicionestadoFiltroPagoprogrtamado
    ORDER BY g.fecha ASC ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        echo trigger_error('The query execution failed; MySQL said (' . $stmt->errno . ') ' . $stmt->error, E_USER_ERROR);
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
	$sqlFiltro = "";
	if ($fecha_inicio) {
		$sqlFiltro .= " and fecha >= '$fecha_inicio'";
	}
	if ($fecha_fin) {
		$sqlFiltro .= " and fecha <= '$fecha_fin'";
	}

	$mysqli=conectar_al_servidor();
	
	$sql= "SELECT 
		(SELECT sum(monto) FROM gastos where cod_motivoIngresoEgresoFK = m.cod_motivo_ingreso_egreso $sqlFiltro) as monto,
		m.cod_motivo_ingreso_egreso, m.descripcion 
	 FROM motivos_ingreso_egreso m where estado='activo'";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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
	// Obtiene los datos del gasto
	$registroGasto= buscarGasto('', '', '', '', '', '', '', '', 'false', '', '', '', '','',$idgastos)[0];
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
	$mysqli=conectar_al_servidor();

	$sql= "UPDATE gastos SET cod_usuario_autoriz= ?, fecha_autoriz= ?, codApertura= ?, codCaja= ?, estado='$decision' WHERE idgastos= ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('isiii',$cod_usuarioFK,$fechaActual,$cod_aperturaFK,$cod_cajaFK,$idgastos);

	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	// Se registra el cambio
	if (!empty($registroGasto['cod_interConsultaFK'])) {
		$fechaActual = new DateTime();
		$mensaje= " @{".$cod_usuarioFK."} decidio ". ($decision == 'Activo' ? ' aprobar ' : ' rechazar ') . " el movimiento con descripcion ".$registroGasto['motivo'].".";
		$mensaje = mb_convert_encoding($mensaje, 'ISO-8859-1', 'UTF-8');
		abmMensaje("", $mensaje, $fechaActual->format('Y-m-d H:i:s'), $registroGasto['cod_interConsultaFK'], "", NULL, TRUE);
	}

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

	$ruta= NULL;
	$foto = substr($foto, strpos($foto, ",") + 1);
	$foto = base64_decode($foto);
	$donde = "../fotos/fotosGastos/";
	$id_foto = $prefijo.$idgastos;
	$id_f = subir_imagen_base64($donde, $foto, $id_foto, $ext);
	$ruta = "/GoodVentaAsisCap/fotos/fotosGastos/" . $prefijo . $idgastos . $id_f . "." . $ext;
	
	$mysqli=conectar_al_servidor();
	$consulta="Update gastos set $columna=? where idgastos=? ";	
	
	$stmt = $mysqli->prepare($consulta);
	$stmt->bind_param('si', $ruta, $idgastos);
	if ( ! $stmt->execute()) {
		echo "Error";
		exit;
	}

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

	$nombreProyecto= trim($motivoPrimeraCuota);
	if ($nombreProyecto == "") {
		$nombreProyecto= "Gasto recurrente ".$idBaseSerie;
	}

	if (!empty($codProyectoGasto)) {
		abmProyectoGasto($codProyectoGasto, $nombreProyecto, 'activo', $codInterConsulta);
	} else {
		$codProyectoGasto= abmProyectoGasto('', $nombreProyecto." - serie ".$idBaseSerie, 'activo', $codInterConsulta);
	}

	$sql= "UPDATE gastos SET cod_proyecto_gastoFK = ? WHERE idgastos = ?";
	$stmt= $mysqli->prepare($sql);
	$stmt->bind_param('ii', $codProyectoGasto, $idBaseSerie);
	$stmt->execute();
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

function crearInterConsultaParaGasto($motivo, $tipo, $cod_usuario, $cod_local) {
	if (!function_exists('abmInterConsulta')) {
		return '';
	}
	$asunto= trim((string)$motivo);
	if ($asunto == '') {
		$asunto= 'Movimiento financiero';
	}
	$tipoHilo= (strtolower(trim((string)$tipo)) == 'ingreso') ? 'pago' : 'egreso';
	return abmInterConsulta('', $asunto, 'Hilo creado automaticamente desde Resumen de flujo financiero.', 'activo', $tipoHilo, NULL, $cod_usuario, $cod_usuario, $cod_local, 0);
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

function obtenerOCrearProyectoGastoParaInterConsulta($cod_interConsultaFK, $nombreFallback= '', $codProyectoSolicitado= '') {
	if (empty($cod_interConsultaFK) || !is_numeric($cod_interConsultaFK)) {
		return (is_numeric($codProyectoSolicitado) ? $codProyectoSolicitado : '');
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

function registrarCuotasRecurrentes($mysqli, $idBaseSerie, $Arreglo, $cantCuotas, $periodicidad, $fechaBaseStr, $monto, $motivo, $cod_usuario, $personales, $cod_local, $tipo, $codcaja, $idaperturacierrecaja, $nroboleta, $banco, $nrocuenta, $cod_motivo, $cod_interConsultaFK, $codProyectoGastoFijo= '') {
	$estado= 'pendiente';
	if ($cantCuotas <= 1) {
		return;
	}

	$fechaBase = DateTime::createFromFormat('Y-m-d', $fechaBaseStr);
	if ($fechaBase === false) {
		return;
	}

	$codProyectoGasto= ($codProyectoGastoFijo != '' && is_numeric($codProyectoGastoFijo)) ? $codProyectoGastoFijo : obtenerOCrearProyectoGastoParaCuotas($mysqli, $idBaseSerie, $motivo);
		
	$consultaRecurrente = "Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta,cod_motivoIngresoEgresoFK,cod_interConsultaFK,modalidad, cod_proyecto_gastoFK)
	values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$stmtRecurrente = $mysqli->prepare($consultaRecurrente);
	$ssRecurrente = str_repeat('s', 18);
	$modalidadCredito = 'credito';
		
	for ($i = 1; $i < $cantCuotas; $i++) {
		$motivoCuota = 'Cuota '.($i + 1).' de '.trim($motivo).' (' . intval($idBaseSerie).')';
		$fechaCuota = calcularFechaCuotaRecurrente($fechaBase, $periodicidad, $i);
		if ($fechaCuota == null) {
			continue;
		}

		$fechaCuotaFormat = $fechaCuota->format('Y-m-d');
		$stmtRecurrente->bind_param($ssRecurrente,$Arreglo,$monto,$motivoCuota,$fechaCuotaFormat,$estado,$cod_usuario,$personales,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$nroboleta, $banco , $nrocuenta,$cod_motivo,$cod_interConsultaFK,$modalidadCredito,$codProyectoGasto);
		$stmtRecurrente->execute();
		$idgastos = mysqli_insert_id($mysqli);

		// Programa tambien el mensaje de recordatorio si tiene una interconsulta asociada
		if (!empty($cod_interConsultaFK)) {
			$cod_mensaje= abmMensaje("", "El gasto $motivoCuota vence hoy ",$fechaCuotaFormat, $cod_interConsultaFK, "", NULL,TRUE);
			
			// Actualiza el cod_mensaje del gasto ingresado
			$sql = "UPDATE gastos SET cod_mensajeFK = ? WHERE idgastos = ?";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param('ii', $cod_mensaje, $idgastos);
			$stmt->execute();
			$stmt->close();
		}
	}

	$stmtRecurrente->close();
}

function abmGasto($Arreglo,$nroboleta, $banco , $nrocuenta,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion,$editar_cuotas= "true", $cod_proyecto_gastoFK= NULL)
{
		
if ($codcaja == "0" || $codcaja == 0 || $idaperturacierrecaja == 0 || $idaperturacierrecaja == "0") {
	echo "Cod caja o de apertura en 0";
	print_r($Arreglo,$nroboleta, $banco , $nrocuenta,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion,$editar_cuotas);
	exit;
}
if($monto==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

if (empty($cod_interConsultaFK)) {
	$cod_interConsultaFK= crearInterConsultaParaGasto($motivo, $tipo, $cod_usuario, $cod_local);
}

$cantCuotas = isset($_POST['cantCuotas']) ? intval($_POST['cantCuotas']) : 0;
$periodicidad = isset($_POST['periodicidad']) ? mb_convert_encoding((string)$_POST['periodicidad'], 'ISO-8859-1', 'UTF-8') : '';
$proyectoSolicitado= trim((string)$cod_proyecto_gastoFK);
if ($proyectoSolicitado == "0") {
	$proyectoSolicitado= "";
	$cod_proyecto_gastoFK= NULL;
}
if (!empty($cod_interConsultaFK) && is_numeric($proyectoSolicitado) && intval($proyectoSolicitado) > 0) {
	$cod_proyecto_gastoFK= obtenerOCrearProyectoGastoParaInterConsulta($cod_interConsultaFK, $motivo, $proyectoSolicitado);
}

// Se evalua si el monto no supera el presupuesto establecido para la interconsulta
if ($cod_interConsultaFK) {
	$registroInterConsulta= obtenerInterConsulta(array(
		'cod_interConsulta' => $cod_interConsultaFK,
	))[0];
	
	$totalMonto= intval($registroInterConsulta['total_gastos']) + intval($monto);
	if (intval($registroInterConsulta['monto_limite']) && $totalMonto > intval($registroInterConsulta['monto_limite'])) {
		$informacion =array("1" => "error", "2" => "El gasto supera el monto limite establecido por la interconsulta.");
		echo json_encode($informacion);	
		exit;
	}
}

$modalidad= (($cantCuotas > 1) ? 'credito' : 'contado');

$mysqli=conectar_al_servidor();

// Identifica si el motivo necesita autorizacion
$registros_motivos= buscarabmmotivoingresoegreso('', 'activo',$cod_motivo);

// Variable para evaluar la fecha del gasto y asignar su estado correspondiente
$fechaGasto = DateTime::createFromFormat('!Y-m-d', substr((string)$fecha, 0, 10));
$pasadoManana = new DateTime('today');
$pasadoManana->modify('+1 day');
if ($estado == 'Activo' && $registros_motivos['4'][0]['necesita_autorizacion'] == '1') {
	$estado = ($fechaGasto && ($fechaGasto > $pasadoManana)) ? 'pendiente' : 'solicitado';
}

if($operacion=="nuevo")
{
if ($cod_proyecto_gastoFK == "0") {
	$cod_proyecto_gastoFK = NULL;
}

$consulta1="Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta,cod_motivoIngresoEgresoFK,cod_interConsultaFK,modalidad,cod_proyecto_gastoFK)
values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $mysqli->prepare($consulta1);

$ss='ssssssssssssssssss';
$stmt->bind_param($ss,$Arreglo,$monto,$motivo,$fecha,$estado,$cod_usuario,$personales,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$nroboleta, $banco , $nrocuenta,$cod_motivo,$cod_interConsultaFK,$modalidad, $cod_proyecto_gastoFK);


}


if($operacion=="editar")
{

// Obtiene los datos actuales del gasto
$datos_gasto= buscarGasto('', '', '', '', '', '', '', '', 'false', '', '', '', '','',$idgastos);
$foto_documento_firmado_edicion= isset($_POST['foto_documento_firmado']) ? trim((string)$_POST['foto_documento_firmado']) : '';
$ext_documento_firmado_edicion= isset($_POST['ext_documento_firmado']) ? trim((string)$_POST['ext_documento_firmado']) : '';
$mantener_estado_por_documento_firmado= ($foto_documento_firmado_edicion != '' && $ext_documento_firmado_edicion != '');

if ($mantener_estado_por_documento_firmado && isset($datos_gasto[0]['estado'])) {
	$estado= $datos_gasto[0]['estado'];
} else {
	$estado = (mb_strtolower((string)$estado, 'UTF-8') == 'inactivo' ? "Inactivo" : (($fechaGasto && ($fechaGasto > $pasadoManana)) ? 'pendiente' : 'solicitado'));
	$cod_usuario_autoriz= NULL;
}

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
	return array("1" => "exito", "2" => $idgastos);
}

$parametros[] = $idgastos;
$ss .= "i";

$consulta1="Update gastos set $atributos where idgastos=?";
$stmt = $mysqli->prepare($consulta1);
if (!$stmt) {
	echo $mysqli->error;
}

$refs = [];
foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}
call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
}

if (!$stmt->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


if($operacion=='nuevo'){
	$idgastos = mysqli_insert_id($mysqli);
	if (intval($cantCuotas) > 1 && $periodicidad != "") {
		registrarCuotasRecurrentes($mysqli, $idgastos, $Arreglo, $cantCuotas, $periodicidad, $fecha, $monto, $motivo, $cod_usuario, $personales, $cod_local, $tipo, $codcaja, $idaperturacierrecaja, $nroboleta, $banco, $nrocuenta, $cod_motivo, $cod_interConsultaFK, $cod_proyecto_gastoFK);
	}
}

if($operacion=='editar' && $editar_cuotas == "true"){
	$codProyectoSerie= obtenerProyectoGastoSerie($mysqli, $idgastos, $cod_proyecto_gastoFK);
	if ($codProyectoSerie != '') {
		$sql = "UPDATE gastos SET cod_proyecto_gastoFK = ? WHERE idgastos = ?";
		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param('ii', $codProyectoSerie, $idgastos);
		$stmt->execute();

		if ($cod_motivo != NULL && $cod_motivo != "") {
			$sql = "UPDATE gastos SET cod_motivoIngresoEgresoFK = ?, cod_usuarioFK_edit = ? WHERE cod_proyecto_gastoFK = ?";
			$stmtActualizarConceptoProyecto = $mysqli->prepare($sql);
			$stmtActualizarConceptoProyecto->bind_param('iii', $cod_motivo, $cod_usuario, $codProyectoSerie);
			if (!$stmtActualizarConceptoProyecto->execute()) {
				echo trigger_error('The query execution failed; MySQL said ('.$stmtActualizarConceptoProyecto->errno.') '.$stmtActualizarConceptoProyecto->error, E_USER_ERROR);
				exit;
			}
			$stmtActualizarConceptoProyecto->close();
		}

		$gastos_asociados= buscarGasto('','','','','','','','','false','','','','','', '', 'ASC', $codProyectoSerie);
	} else {
		$gastos_asociados= obtenerGastosAsociados($idgastos);
	}

	$cantidadCuotasSerie= 0;
	foreach ($gastos_asociados as $value) {
		$estadoCuotaSerie= strtolower(trim((string)$value['estado']));
		if ($value['idgastos'] == $idgastos || $estadoCuotaSerie == 'pendiente' || $estadoCuotaSerie == 'solicitado') {
			$cantidadCuotasSerie++;
		}
		if ($value['idgastos'] != $idgastos && ($estadoCuotaSerie == 'pendiente' || $estadoCuotaSerie == 'solicitado')) {
			$sql = "UPDATE gastos SET estado='Inactivo', cod_usuarioFK_edit=? WHERE idgastos=?";
			$stmtInactivarCuota = $mysqli->prepare($sql);
			$idGastoCuota = $value['idgastos'];
			$stmtInactivarCuota->bind_param('ii', $cod_usuario, $idGastoCuota);
			if (!$stmtInactivarCuota->execute()) {
				echo trigger_error('The query execution failed; MySQL said ('.$stmtInactivarCuota->errno.') '.$stmtInactivarCuota->error, E_USER_ERROR);
				exit;
			}
			$stmtInactivarCuota->close();
		}
	}

	if ($cantidadCuotasSerie > 1 && $estado != 'Inactivo') {
		registrarCuotasRecurrentes($mysqli, $idgastos, $Arreglo, $cantidadCuotasSerie, $periodicidad, $fecha, $monto, $motivo, $cod_usuario, $personales, $cod_local, $tipo, $codcaja, $idaperturacierrecaja, $nroboleta, $banco, $nrocuenta, $cod_motivo, $cod_interConsultaFK, $codProyectoSerie);
	}
	if (isset($stmt) && $stmt) {
		$stmt->close();
	}
	}

$foto=$_POST['foto'];
$ext=$_POST['ext'];
subirImagenGasto($idgastos, $foto, $ext);
$foto_documento_firmado= isset($_POST['foto_documento_firmado']) ? $_POST['foto_documento_firmado'] : '';
$ext_documento_firmado= isset($_POST['ext_documento_firmado']) ? $_POST['ext_documento_firmado'] : '';
subirDocumentoFirmadoGasto($idgastos, $foto_documento_firmado, $ext_documento_firmado);

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
return array("1" => "exito", "2" => $idgastos);	
}

function buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder= 'DESC', $cod_proyecto_gastoFK= '') {
	$registros= array();
	$mysqli=conectar_al_servidor();
	if ($cod_proyecto_gastoFK == "" && is_numeric($fechaOrder)) {
		$cod_proyecto_gastoFK= $fechaOrder;
		$fechaOrder= 'DESC';
	}
	$fechaOrder= strtoupper((string)$fechaOrder);
	if ($fechaOrder != 'ASC' && $fechaOrder != 'DESC') {
		$fechaOrder= 'DESC';
	}
	if ($cod_proyecto_gastoFK != "" && !is_numeric($cod_proyecto_gastoFK)) {
		$cod_proyecto_gastoFK= "";
	}

	$sqlFiltro= '';
	if($cod_local != ""){
		$sqlFiltro .= " and g.cod_local='$cod_local'";
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

	// Se limpia el primer ' and'
	if (strlen($sqlFiltro) > 0) {
		$sqlFiltro = "where" . substr($sqlFiltro, 4, strlen($sqlFiltro));
	}
		
	$sql= "Select g.arreglo,g.monto,g.motivo as descripcion,g.fecha,g.estado,g.cod_usuario,g.idgastos,g.tipo,g.cod_proyecto_gastoFK,
	g.cod_local,g.nroboleta,g.banco,g.nrocuenta,g.url1,g.url_documento_firmado,g.cod_interConsultaFK,g.modalidad,g.codCaja,g.codApertura,
	g.cod_usuario_autoriz, g.fecha_autoriz, g.cod_motivoIngresoEgresoFK, g.cod_usuarioFK_edit,g.cod_gasto_padre,g.cod_mensajeFK,
	(Select asunto from interconsulta where cod_interConsulta=g.cod_interConsultaFK) as interconsulta_nombre,
	(Select nombre_persona from persona where cod_persona=g.cod_usuario) as usuarionombre,
	(Select nombre_persona from persona where cod_persona=g.cod_usuarioFK_edit) as nombre_usuario_edit,
	(Select nombre_persona from persona where cod_persona=g.cod_usuario_autoriz) as usuario_autoriz_nombre,
	m.descripcion AS motivo, m.categoria,
	(Select Nombre from local l where l.cod_local=g.cod_local) as nombrelocal
	from gastos g left join motivos_ingreso_egreso m on m.cod_motivo_ingreso_egreso = g.cod_motivoIngresoEgresoFK $sqlFiltro ORDER BY 
	FIELD(m.categoria,'','ingreso','directo','operativo'), necesita_autorizacion DESC, g.fecha $fechaOrder, g.idgastos DESC";

	$stmt = $mysqli->prepare($sql);
	if ( ! $stmt->execute()) {
		echo "Error";
		exit;
	}

	$result = $stmt->get_result();
	$valor= mysqli_num_rows($result);	
	if ($valor>0) {
		while ($valor= mysqli_fetch_assoc($result)) {
			$registros[] = array(
				'idgastos' =>mb_convert_encoding((string)($valor['idgastos']), 'UTF-8', 'ISO-8859-1'),
				'interconsulta_nombre' => mb_convert_encoding((string)($valor['interconsulta_nombre']), 'UTF-8', 'ISO-8859-1'),
				'cod_interConsultaFK' => mb_convert_encoding((string)($valor['cod_interConsultaFK']), 'UTF-8', 'ISO-8859-1'),
				'usuarionombre' => mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1'),
				'monto' => mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1'),
				'motivo' => mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1'),
				'descripcion' => mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1'),
				'fecha' => mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1'),
				'tipo' => mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'),
				'estado' => mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'),
				'cod_local' => mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1'),
				'cod_usuario' => mb_convert_encoding((string)($valor['cod_usuario']), 'UTF-8', 'ISO-8859-1'),
				'codCaja' => mb_convert_encoding((string)($valor['codCaja']), 'UTF-8', 'ISO-8859-1'),
				'codApertura' => mb_convert_encoding((string)($valor['codApertura']), 'UTF-8', 'ISO-8859-1'),
				'nombrelocal' => mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'),
				'nroboleta' => mb_convert_encoding((string)($valor['nroboleta']), 'UTF-8', 'ISO-8859-1'),
				'banco' => mb_convert_encoding((string)($valor['banco']), 'UTF-8', 'ISO-8859-1'),
				'nrocuenta' => mb_convert_encoding((string)($valor['nrocuenta']), 'UTF-8', 'ISO-8859-1'),
				'arreglo' => mb_convert_encoding((string)($valor['arreglo']), 'UTF-8', 'ISO-8859-1'),
				'url1' => mb_convert_encoding((string)($valor['url1']), 'UTF-8', 'ISO-8859-1'),
				'url_documento_firmado' => mb_convert_encoding((string)($valor['url_documento_firmado']), 'UTF-8', 'ISO-8859-1'),
				'categoria' => mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1'),
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
	if ($categoria == 'ingreso' || $categoria == 'directo' || $categoria == 'operativo' || $categoria == 'administracion') {
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

	foreach ($registros as $gasto) {
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

	$totalesPorLocal= flujoGastoDistribuirMontoAdministracion($totalOrigen);
	if (flujoGastoEsLocalDestinoAdministracion($codLocalSeleccionado) && isset($totalesPorLocal[$codLocalSeleccionado])) {
		$cantidadLocales= count($localesDestino);
		$montoObjetivoLocal= intval($totalesPorLocal[$codLocalSeleccionado]);
		$montosBaseMovimiento= array();
		$totalBaseLocal= 0;
		foreach ($gastosElegibles as $indiceGasto => $gastoElegible) {
			$montoGasto= intval(isset($gastoElegible['monto']) ? $gastoElegible['monto'] : 0);
			$montoBase= $cantidadLocales > 0 ? intdiv($montoGasto, $cantidadLocales) : 0;
			$montosBaseMovimiento[$indiceGasto]= $montoBase;
			$totalBaseLocal += $montoBase;
		}
		$diferenciaRedondeo= $montoObjetivoLocal - $totalBaseLocal;
		foreach ($gastosElegibles as $indiceGasto => $gastoElegible) {
			$montoAsignadoMovimiento= isset($montosBaseMovimiento[$indiceGasto]) ? $montosBaseMovimiento[$indiceGasto] : 0;
			if ($diferenciaRedondeo > 0) {
				$montoAsignadoMovimiento += 1;
				$diferenciaRedondeo--;
			}
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
	return flujoGastoCeldaOculta('td_datos_1', number_format(intval(isset($gasto['monto']) ? $gasto['monto'] : 0), 0, ',', '.'))
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
		.flujoGastoCeldaOculta('td_datos_13', isset($gasto['descripcion']) ? $gasto['descripcion'] : '')
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

function construirDetalleCuotasProgramadas($gastosSerie, $resumen) {
	if (count($gastosSerie) <= 1) {
		return "";
	}
	$total= count($gastosSerie);
	$gastoBase= isset($gastosSerie[0]) ? $gastosSerie[0] : array();
	$botonCrearProyectoHilo= construirBotonCrearProyectoHilo($gastoBase, true);
	$filas= "";
	foreach ($gastosSerie as $indice => $gasto) {
		$estado= obtenerEtiquetaCuotaProgramada($gasto);
		$estadoOriginal= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
		$indicadorConciliacionUeno= "";
		if (!flujoGastoEstaAnulado($gasto)) {
			$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno(isset($gasto['idgastos']) ? $gasto['idgastos'] : '', isset($gasto['monto']) ? $gasto['monto'] : 0);
			$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
		}
		$acciones= "<span style='color:#4b5563;font-size:8pt;'>Cerrada</span>";
		if ($estadoOriginal != 'activo') {
			$acciones= "<button type='button' title='Editar cuota' onclick='editarGastoDesdeFila(event, this)' style='border:0;background:#2f80ed;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>Editar</button>";
			if ($estadoOriginal == 'pendiente' || $estadoOriginal == 'solicitado') {
				$acciones .= " <button type='button' title='Aprobar cuota' onclick='event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)' style='border:0;background:#078b35;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>OK</button>"
					." <button type='button' title='Rechazar cuota' onclick='event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)' style='border:0;background:#c92323;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>X</button>";
			}
		}
		$acciones .= construirBotonConciliarEgresoUeno($gasto, 'Cuotas programadas');
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
	$botonCrearProyectoHilo= construirBotonCrearProyectoHilo($gastoBase, true);
	$filas= "";
	foreach ($gastosSerie as $indice => $gasto) {
		$idCuota= isset($gasto['idgastos']) ? $gasto['idgastos'] : '';
		if ($idCuota == '') {
			continue;
		}
		$estado= obtenerEtiquetaCuotaProgramada($gasto);
		$estadoOriginal= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
		$indicadorConciliacionUeno= "";
		if (!flujoGastoEstaAnulado($gasto)) {
			$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno($idCuota, isset($gasto['monto']) ? $gasto['monto'] : 0);
			$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
		}
		$acciones= "<span style='color:#4b5563;font-size:8pt;'>Cerrada</span>";
		if ($estadoOriginal != 'activo') {
			$acciones= "<button type='button' title='Editar cuota' onclick='editarGastoDesdeFila(event, this)' style='border:0;background:#2f80ed;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>Editar</button>";
			if ($estadoOriginal == 'pendiente' || $estadoOriginal == 'solicitado') {
				$acciones .= " <button type='button' title='Aprobar cuota' onclick='event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)' style='border:0;background:#078b35;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>OK</button>"
					." <button type='button' title='Rechazar cuota' onclick='event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)' style='border:0;background:#c92323;color:#fff;border-radius:4px;padding:3px 7px;font-size:8pt;cursor:pointer;'>X</button>";
			}
		}
		$acciones .= construirBotonConciliarEgresoUeno($gasto, 'Cuotas programadas');
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

function construirLinkInterconsultaFlujoGasto($gasto) {
	$codInterConsulta= trim((string)(isset($gasto['cod_interConsultaFK']) ? $gasto['cod_interConsultaFK'] : ''));
	$interconsultaNombre= trim((string)(isset($gasto['interconsulta_nombre']) ? $gasto['interconsulta_nombre'] : ''));
	if ($interconsultaNombre == "" && $codInterConsulta != "") {
		$interconsultaNombre= "Hilo ".$codInterConsulta;
	}
	if ($interconsultaNombre == "") {
		$interconsultaNombre= "Sin hilo";
	}
	$interconsultaElemento= flujoGastoTextoSeguro($interconsultaNombre);
	if ($codInterConsulta != "") {
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

function construirPagoUnicoFlujoConcepto($gasto, $tituloZona= '') {
	$idGasto= isset($gasto['idgastos']) ? $gasto['idgastos'] : '';
	if ($idGasto == '') {
		return "";
	}
	$esAsignacionAdministrativa= !empty($gasto['es_asignacion_administrativa']);
	$monto= intval(isset($gasto['monto']) ? $gasto['monto'] : 0);
	$estado= obtenerEtiquetaCuotaProgramada($gasto);
	$estadoOriginal= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
	$indicadorConciliacionUeno= "";
	if (!$esAsignacionAdministrativa && !flujoGastoEstaAnulado($gasto)) {
		$resumenConciliacionUeno= flujoGastoResumenConciliacionUeno($idGasto, $monto);
		$indicadorConciliacionUeno= construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
	}
	$botonConciliarUeno= $esAsignacionAdministrativa ? "" : construirBotonConciliarEgresoUeno($gasto, $tituloZona);
	$acciones= $esAsignacionAdministrativa
		? "<span class='flujo-pago-unico-solo-lectura'>Asignado</span>"
		: "<button type='button' title='Editar movimiento' aria-label='Editar movimiento' onclick='editarGastoDesdeFila(event, this)' class='flujo-pago-unico-editar'>"
			."<img src='/GoodVentaAsisCap/iconos/editar.png' alt='Editar'>"
			."</button>".$botonConciliarUeno;
	if (!$esAsignacionAdministrativa && ($estadoOriginal == 'pendiente' || $estadoOriginal == 'solicitado')) {
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
		."<tbody><tr id='tbSelecRegistro' class='flujo-pago-unico-table__row".$claseFila."' onclick='".($esAsignacionAdministrativa ? "" : "obtenerdatosabmGasto(this)")."'>"
		."<td id='td_id' class='flujo-pago-unico-ref' style='".$styleEstado."'>".flujoGastoTextoSeguro($esAsignacionAdministrativa ? "ADM ".$idGasto : $idGasto)."</td>"
		."<td class='flujo-pago-unico-concepto'>".flujoGastoTextoSeguro($motivo)."</td>"
		."<td class='flujo-pago-unico-interconsulta'>".construirLinkInterconsultaFlujoGasto($gasto)."</td>"
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

function buscarGastoConMotivos($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder= 'DESC')
{
	$totalZonaIngresos= 0;
	$totalZonaCostosDirectos= 0;
	$totalZonaGastosOperativos= 0;
	$totalZonaAdministracionAsignada= 0;
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
		$categoria= $mot['categoria'];
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
	// Se conserva el comportamiento historico: el resumen solo muestra motivos activos.
	$registros= buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,'', $cod_interConsultaFK, $nombre_interConsulta, $motivo, $cod_gasto_padre, $idgastos, $fechaOrder);
	$nroRegistro= count($registros);
	foreach ($registros as $valor) {
		$codMotivoRegistro= (string)$valor['cod_motivoIngresoEgresoFK'];
		if (!isset($motivosActivosPorCodigo[$codMotivoRegistro])) {
			continue;
		}
		if ($cod_motivoFK != '' && (string)$cod_motivoFK !== $codMotivoRegistro) {
			continue;
		}
		$montoRegistro= intval($valor['monto']);
		$categoriaRegistro= flujoGastoNormalizarCategoriaResumen($valor['categoria']);
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
		$totalGasto += $montoRegistro;
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
			$totalZonaAdministracionAsignada= intval($administracionCompartida['monto_asignado']);
			$totalGasto += $totalZonaAdministracionAsignada;
		}
	}

	$registrosZonaOrdenados= array();
	foreach (array('ingreso', 'directo', 'operativo', 'administracion', 'sinCategoria') as $zonaOrdenada) {
		if (isset($registrosZona[$zonaOrdenada])) {
			$registrosZonaOrdenados[$zonaOrdenada]= $registrosZona[$zonaOrdenada];
		}
	}
	foreach ($registrosZona as $zona => $cod_motivos) {
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
		$idMotivoCollapse= preg_replace('/[^A-Za-z0-9_-]/', '_', 'zonaMotivos'.$idZona.'_'.$cod_motivo);
		flujoGastoAsegurarConceptoResumen($resumenComposicionFlujo, $zona, $cod_motivo, $titulo_motivo);
		foreach ($gastos as $valor) {
			flujoGastoAgregarMovimientoResumen($resumenComposicionFlujo, $zona, $cod_motivo, $titulo_motivo, $valor);
			$esAsignacionAdministrativa= !empty($valor['es_asignacion_administrativa']);
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
			if (!$esAsignacionAdministrativa && $codProyectoSerie != "" && $codProyectoSerie != "0" && $esCuotaProgramada) {
				$claveSerieRenderizada= $cod_motivo."|".$codProyectoSerie;
				if (isset($seriesCuotasRenderizadas[$claveSerieRenderizada])) {
					continue;
				}
				$gastosSerieCuotas= filtrarGastosCuotasProgramadas(obtenerGastosAsociados($valor['idgastos']));
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
			$motivo=mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');
			$descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
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
			if ($tieneCuotasProgramadas) {
				$funcion= "alternarCuotasProgramadas(event, this)";
			}
			$resumenConciliacionUeno= $esAsignacionAdministrativa ? array() : flujoGastoResumenConciliacionUeno($idgastos, $monto);
			$indicadorConciliacionUeno= $esAsignacionAdministrativa ? "" : construirIndicadorConciliacionUenoGasto($resumenConciliacionUeno);
			$botonConciliarUeno= $esAsignacionAdministrativa ? "" : construirBotonConciliarEgresoUeno($valor, $titulo);
			$botonEditarGasto= "<td style='width:4%;text-align:center;vertical-align:middle;'></td>";
			if (!$esAsignacionAdministrativa && ($idgastos != "" || $botonConciliarUeno != "")) {
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
				<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)'>
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
					<td  id='td_datos_13' style='display:none'>".$descripcion."</td>
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
				<td  id='td_datos_1' style='display:none'>". number_format($monto,'0',',','.')."</td>
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
				<td  id='td_datos_13' style='display:none'>".$descripcion."</td>
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
					'html' => construirPagoUnicoFlujoConcepto($valor, $titulo),
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
		if ($cod_motivo != -1 && $zona != 'administracion') {
			$tipoMovimientoContexto= ($zona == 'ingreso') ? "Ingreso" : "Egreso";
			$botonAgregarMovimientoContextual= "<button type='button' class='flujo-concepto-add' title='Agregar movimiento a este concepto' onclick='abrirMovimientoFinancieroDesdeBotonConcepto(event, this)'"
				." data-tipo-movimiento='".flujoGastoTextoSeguro($tipoMovimientoContexto)."'"
				." data-categoria-flujo='".flujoGastoTextoSeguro($titulo)."'"
				." data-categoria-codigo='".flujoGastoTextoSeguro($zona)."'"
				." data-concepto-id='".flujoGastoTextoSeguro($cod_motivo)."'"
				." data-concepto-nombre='".flujoGastoTextoSeguro($titulo_motivo)."'>"
				."<span>+</span>"
				."</button>";
			if ($zona != 'ingreso') {
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
				'<h6><b>'.$titulo_motivo.'</b>: <span>'.number_format($totalMonto, 0, ',', '.').'</span> Gs.</h6>'.
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
		from gastos g where fecha>='$fecha1' and fecha<='$fecha2' and estado='activo' ".$condicionCodLocal;
		
   
   
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

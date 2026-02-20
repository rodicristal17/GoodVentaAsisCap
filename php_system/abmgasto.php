<?php

include_once('quitarseparadormiles.php');
include_once("buscar_nivel.php");
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("classTable.php");
include_once("subir_foto_base64.php");
include_once("abmpagos.php");
include_once("abmInterConsulta.php");

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

$cod_interConsultaFK= $_POST['cod_interConsultaFK'];
$cod_interConsultaFK= mb_convert_encoding((string)($cod_interConsultaFK), 'ISO-8859-1', 'UTF-8');


// Comprueba si esta dentro del presupuesto
	$fechaActual= new DateTime();
	$primerDiaMes= $fechaActual->format('Y-m-01');
	$ultimoDiaMes= $fechaActual->format('Y-m-t');

	$informacion = buscarabmmotivoingresoegreso('', 'activo', $cod_motivo);
	$informacion2 = buscarGasto('', $primerDiaMes, $ultimoDiaMes, 'Activo', $cod_local, '', '', '','true', $cod_motivo, '', '');

	if ($informacion["4"][0]["presupuesto"] && $informacion["4"][0]["presupuesto"] != '0')
	$totalGasto= intval(str_replace('.', '', $informacion2["4"])) + $monto;
	$limite_presupuesto= intval(str_replace('.', '', $informacion["4"][0]["presupuesto"]));
	if ($limite_presupuesto > 0 && $totalGasto > $limite_presupuesto) {
		$informacion =array("1" => "exito", "2" => "El gasto supera el presupuesto establecido.");
		echo json_encode($informacion);	
		exit;
	}

	abmGasto($Arreglo,$nroboleta, $banco , $nrocuenta ,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion);

}
if ($operacion=='cargar_imagen') {
	$idgastos=$_POST['idgastos'];
	$foto=$_POST['foto'];
	$ext=$_POST['ext'];
	subirImagenGasto($idgastos, $foto, $ext);
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

$informacion = buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $idgastos);
echo json_encode($informacion);
exit;
}	

if ($operacion == "verficiarLimiteMotivo") {
	$cod_motivo = $_POST['cod_motivo'];
	$cod_local = $_POST['cod_local'];
	
	// Obtiene las fechas del primer y ultimo dia del mes
	$fechaActual= new DateTime();
	$primerDiaMes= $fechaActual->format('Y-m-01');
	$ultimoDiaMes= $fechaActual->format('Y-m-t');

	$informacion = buscarabmmotivoingresoegreso('', 'activo', $cod_motivo);
	$informacion2 = buscarGasto('', $primerDiaMes, $ultimoDiaMes, 'Activo', $cod_local, '', '', '','true', $cod_motivo, '', '');

	echo json_encode(array("1" => "exito", "2" => $informacion["4"][0]["presupuesto"], "3" => number_format(intval($informacion2["4"]), 0, ',', '.')));	
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

$presupuesto= $_POST['presupuesto'];
$presupuesto = mb_convert_encoding((string)($presupuesto), 'ISO-8859-1', 'UTF-8');

	NuevoMotivo($motivo,$estado,$categoria,$necesita_autorizacion, $presupuesto);

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

$presupuesto= $_POST['presupuesto'];
$presupuesto = mb_convert_encoding((string)($presupuesto), 'ISO-8859-1', 'UTF-8');

	editarMotivo($motivo,$estado,$categoria,$necesita_autorizacion,$presupuesto, $user, $idabm);

}	

if($operacion=="buscaroption")
{
	buscaroption();
}

if ($operacion == "aprobarMovimiento") {
	$idgastos= $_POST['idgastos'];
	$idgastos= mb_convert_encoding((string)($idgastos), 'ISO-8859-1', 'UTF-8');
	$decision= $_POST['decision'];
	$decision= mb_convert_encoding((string)($decision), 'ISO-8859-1', 'UTF-8');
	aprobarMovimiento($idgastos, $user, $decision);
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
	$sql= "UPDATE motivos_ingreso_egreso SET estado= 'inactivo', cod_usuarioFK= ?, fecha_edit= ? WHERE cod_motivo_ingreso_egreso = ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('isi',$cod_usuarioFK,$fechaActual,$cod_motivoIngresoEgreso);
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
	$fechaActual= new DateTime();
	$fechaActual= $fechaActual->format('Y-m-d H:i:s');
	$decision= ($decision == 'true' ? 'Activo' : 'Rechazado');
	$mysqli=conectar_al_servidor();

	$sql= "UPDATE gastos SET cod_usuario_autoriz= ?, fecha_autoriz= ?, estado='$decision' WHERE idgastos= ?";
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('isi',$cod_usuarioFK,$fechaActual,$idgastos);

	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	// Se registra el cambio
	$registroGasto= buscarGasto('', '', '', '', '', '', '', '', 'false', '', '', $idgastos)[9][0];
	if (!empty($registroGasto['cod_interConsultaFK'])) {
		$fechaActual = new DateTime();
		$mensaje= " @{".$cod_usuarioFK."} decidio ". ($decision == 'Activo' ? ' aprobar ' : ' rechazar ') . " el movimiento con descripcion ".$registroGasto['motivo'].".";
		$mensaje = mb_convert_encoding($mensaje, 'ISO-8859-1', 'UTF-8');
		abmMensaje("", $mensaje, $fechaActual->format('Y-m-d H:i:s'), $registroGasto['cod_interConsultaFK'], "", TRUE);
	}

	$informacion =array("1" => "exito", "2" => $idgastos);
	echo json_encode($informacion);	
	exit;
}

function subirImagenGasto($idgastos, $foto, $ext) {
	$ruta= NULL;
	if (!empty($foto) || !empty($ext)) {
		$foto = substr($foto, strpos($foto, ",") + 1);
		$foto = base64_decode($foto);
		$donde = "../fotos/fotosGastos/";
		$id_foto = $idgastos;
		$id_f = subir_imagen_base64($donde, $foto, $id_foto, $ext);
		$ruta = "/GoodVentaAsisCap/fotos/fotosGastos/" . $idgastos . $id_f . "." . $ext;
	}
	
	$mysqli=conectar_al_servidor();
	$consulta="Update gastos set url1='$ruta' where idgastos='$idgastos' ";	
	
	$stmt = $mysqli->prepare($consulta);
	if ( ! $stmt->execute()) {
		echo "Error";
		exit;
	}

	return true;
}

function abmGasto($Arreglo,$nroboleta, $banco , $nrocuenta,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion)
{
		
if($monto==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

// Identifica si el motivo necesita autorizacion
$registros_motivos= buscarabmmotivoingresoegreso('', 'activo',$cod_motivo);

if($operacion=="nuevo")
{

if ($estado == 'Activo' && $registros_motivos['4'][0]['necesita_autorizacion'] == '1') {
	$estado = "solicitado";
}

$consulta1="Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta,cod_motivoIngresoEgresoFK,cod_interConsultaFK)
values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $mysqli->prepare($consulta1);

$ss='ssssssssssssssss';
$stmt->bind_param($ss,$Arreglo,$monto,$motivo,$fecha,$estado,$cod_usuario,$personales,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$nroboleta, $banco , $nrocuenta,$cod_motivo,$cod_interConsultaFK);


}


if($operacion=="editar")
{

// Obtiene los datos actuales del gasto
$datos_gasto= buscarGasto('', '', '', '', '', '', '', '', 'false', '', '', $idgastos)[9];

if ($estado == 'Activo' && !empty($cod_usuario_autoriz)) {
	$estado = "pendiente";
}

$consulta1="Update gastos set arreglo=?, monto=?,motivo=?,fecha=?,estado=?,cod_usuarioFK_edit=?,
personales=?,cod_local=?,tipo=?,nroboleta=?,banco=?,nrocuenta=?, cod_motivo=?, cod_interConsultaFK=?, cod_usuario_autoriz=? where idgastos=?";
$stmt = $mysqli->prepare($consulta1);
$ss='sssssssssssssssi';
if (!$stmt) {
	echo $mysqli->error;
}
$stmt->bind_param($ss,$Arreglo,$monto,$motivo,$fecha,$estado,$cod_usuario,$personales,$cod_local,$tipo,$nroboleta,$banco,$nrocuenta,$cod_motivo,$cod_interConsultaFK,$cod_usuario_autoriz,$idgastos); 

}

if (!$stmt->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


if($operacion=='nuevo'){
	$idgastos = mysqli_insert_id($mysqli);
}
$foto=$_POST['foto'];
$ext=$_POST['ext'];
subirImagenGasto($idgastos, $foto, $ext);

if($operacion=="editar")
{
	// Obtiene los datos actuales del gasto
	$datos_gasto_nuevo= buscarGasto('', '', '', '', '', '', '', '', 'false', '', '', $idgastos)[9][0];

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
		abmMensaje("", $mensaje, $fechaActual->format('Y-m-d H:i:s'), $datos_gasto_nuevo['cod_interConsultaFK'], "", TRUE);
	}
}
$informacion =array("1" => "exito", "2" => $idgastos);
echo json_encode($informacion);	
exit;
	
}

function buscarGasto($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK, $cod_interConsultaFK, $idgastos)
{
	$totalZonaIngresos= 0;
	$totalZonaCostosDirectos= 0;
	$totalZonaGastosOperativos= 0;
	$totalZonaSinCategorizar= 0;
	$totalGasto=0;

	$paginaImprimir= "";
	$pagina= "";

	$registrosZona= array();
	$registros= array();

	// Primero obtenemos todos los motivos del sistema
	$registrosMotivos= buscarabmmotivoingresoegreso('', 'activo')[4];

	// Recorremos los motivos y armamos la tabla base
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

		// Busca por motivo
		$mysqli=conectar_al_servidor();

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
		if ($idgastos != "") {
			$sqlFiltro .= " and g.idgastos= $idgastos ";
		}
		$sqlFiltro .= " and g.cod_motivoIngresoEgresoFK = ".$mot['cod_motivo_ingreso_egreso'];

		// Se limpia el primer ' and'
		if (strlen($sqlFiltro) > 0) {
			$sqlFiltro = "where" . substr($sqlFiltro, 4, strlen($sqlFiltro));
		}
			
		$sql= "Select g.arreglo,g.monto,g.motivo as descripcion,g.fecha,g.estado,g.cod_usuario,g.idgastos,g.tipo,
		g.cod_local,g.nroboleta,g.banco,g.nrocuenta,g.url1,g.cod_interConsultaFK,
		g.cod_usuario_autoriz, g.fecha_autoriz, g.cod_motivoIngresoEgresoFK, g.cod_usuarioFK_edit,
		(Select asunto from interconsulta where cod_interConsulta=g.cod_interConsultaFK) as interconsulta_nombre,
		(Select nombre_persona from persona where cod_persona=g.cod_usuario) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=g.cod_usuarioFK_edit) as nombre_usuario_edit,
		(Select nombre_persona from persona where cod_persona=g.cod_usuario_autoriz) as usuario_autoriz_nombre,
		m.descripcion AS motivo, m.categoria,
		(Select Nombre from local l where l.cod_local=g.cod_local) as nombrelocal
		from gastos g left join motivos_ingreso_egreso m on m.cod_motivo_ingreso_egreso = g.cod_motivoIngresoEgresoFK $sqlFiltro ORDER BY necesita_autorizacion DESC, g.idgastos DESC";

		$stmt = $mysqli->prepare($sql);
		if ( ! $stmt->execute()) {
			echo "Error";
			exit;
		}

		$result = $stmt->get_result();
		$valor= mysqli_num_rows($result);
		$nroRegistro= $valor;
		$registroZona = array();

		$styleName="tableRegistroSearch";
		
		if ($valor>0) {
			while ($valor= mysqli_fetch_assoc($result)) {
				$monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
				$categoria=mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1');
				$cod_motivoIngresoEgresoFK= mb_convert_encoding((string)($valor['cod_motivoIngresoEgresoFK']), 'UTF-8', 'ISO-8859-1');
				$cod_usuarioFK_edit= mb_convert_encoding((string)($valor['cod_usuarioFK_edit']), 'UTF-8', 'ISO-8859-1');
				
				if (empty($categoria)) {
					$categoria= "sinCategoria";
				}
				
				$registrosZona[$categoria][$cod_motivoIngresoEgresoFK][]= $valor;
				if ($valor['estado'] == 'Activo') {
					$totalGasto += $monto;
				}
				
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
					'nombrelocal' => mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'),
					'nroboleta' => mb_convert_encoding((string)($valor['nroboleta']), 'UTF-8', 'ISO-8859-1'),
					'banco' => mb_convert_encoding((string)($valor['banco']), 'UTF-8', 'ISO-8859-1'),
					'nrocuenta' => mb_convert_encoding((string)($valor['nrocuenta']), 'UTF-8', 'ISO-8859-1'),
					'arreglo' => mb_convert_encoding((string)($valor['arreglo']), 'UTF-8', 'ISO-8859-1'),
					'url1' => mb_convert_encoding((string)($valor['url1']), 'UTF-8', 'ISO-8859-1'),
					'categoria' => mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1'),
					'cod_usuario_autoriz' => mb_convert_encoding((string)($valor['cod_usuario_autoriz']), 'UTF-8', 'ISO-8859-1'),
					'fecha_autoriz' => mb_convert_encoding((string)($valor['fecha_autoriz']), 'UTF-8', 'ISO-8859-1'),
					'usuario_autoriz_nombre' => mb_convert_encoding((string)($valor['usuario_autoriz_nombre']), 'UTF-8', 'ISO-8859-1'),
					'cod_motivoIngresoEgresoFK' => mb_convert_encoding((string)($valor['cod_motivoIngresoEgresoFK']), 'UTF-8', 'ISO-8859-1'),
					'nombre_usuario_edit' => mb_convert_encoding((string)($valor['nombre_usuario_edit']), 'UTF-8', 'ISO-8859-1'),
					'cod_usuarioFK_edit' => mb_convert_encoding((string)($valor['cod_usuarioFK_edit']), 'UTF-8', 'ISO-8859-1'),
				);

				if ($valor['estado'] == 'Activo') {
					switch ($categoria) {
						case 'ingreso':
							$totalZonaIngresos += $monto;
							break;
						case 'directo':
							$totalZonaCostosDirectos += $monto;
							break;
						case 'operativo':
							$totalZonaGastosOperativos += $monto;
							break;
						default:
							$totalZonaSinCategorizar += $monto;
							break;
					}
				}
			}
		}
	}

	// Agrega el ingreso de los cierres de caja
	$registroMontosCobrados= Arqueo($fecha1,$fecha2,$cod_local,"","","","","","","")[7];
	$registrosZona['ingreso'][-1]= array();
	foreach ($registroMontosCobrados as $key => $value) {
		// Crea un registro ficticio
		$valor= array(
			'idgastos' => "",
			'interconsulta_nombre' => "",
			'cod_interConsultaFK' => "",
			'usuarionombre' => $value['cod_cobradorFK'],
			'monto' => $value['Monto'],
			'motivo' => "Movimiento de caja",
			'descripcion' => "Cobro realizado a ".$value['nombrecliente'] . " en formato ".$value['tipopago'],
			'fecha' => $value['Fecha'],
			'tipo' => "Ingreso",
			'estado' => "activo",
			'cod_local' => $value['cod_local'],
			'nombrelocal' => $value['nombrelocal'],
			'nroboleta' => "",
			'banco' => "",
			'nrocuenta' => "",
			'arreglo' => "",
			'url1' => "",
			'categoria' => "ingreso",
			'cod_usuario_autoriz' => "",
			'fecha_autoriz' => "",
			'usuario_autoriz_nombre' => "",
			'cod_motivoIngresoEgresoFK' => -1,
			'nombre_usuario_edit' => "",
		);
		$registrosZona['ingreso'][-1][]= $valor;
		if ($valor['estado'] == 'Activo') {
			$totalZonaIngresos += intval($valor['monto']);
		}
	}
 
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
			$titulo= "Costos Directos";
			$totalZona= $totalZonaCostosDirectos;
			$styleColor= "#EABA4C;";
			$styleRegistroColor= "#F4CB8D;";
			break;
		case 'operativo':
			$idZona= "GastosOperativos";
			$titulo= "Gastos Operativos";
			$totalZona= $totalZonaGastosOperativos;
			$styleColor= "#DE7258;";
			$styleRegistroColor= "#EDB5A4;";
			break;
		default:
			$idZona= "SinCategorizar";
			$titulo= "Sin Categorizar";
			$totalZona= $totalZonaSinCategorizar;
			$styleColor= "#C4C4C4";
			$styleRegistroColor= "";
			break;
	}

	$pagina .= '<div class="card" style="width: 100%; margin: 0;">'.
	  '<div class="card-header" type="button" onclick="mostrarItems(\'zonaGastos'.$idZona.'\')" style="background-color: '.$styleColor.'">'.
      	'<h4><b>'.$titulo.'</b>: <span>'.number_format($totalZona, 0, ',', '.').'</span> Gs.</h4>'.
	  '</div>'.
	  '<div class="collapse show" id="zonaGastos'.$idZona.'" style=""><ul class="list-group list-group-flush">';

	  foreach ($cod_motivos as $cod_motivo => $gastos) {
		$totalMonto= 0;
		$paginaMotivo= "";
		$registro_autorizacion_necesario= false;
		// Obtiene el nombre del motivo
		if ($cod_motivo == -1) {
			$titulo_motivo= "Movimiento de caja";
		} else {
			$titulo_motivo= buscarabmmotivoingresoegreso('', 'activo', $cod_motivo)[4][0]["descripcion"];
		}
		foreach ($gastos as $valor) {
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
			$nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
			$nroboleta=mb_convert_encoding((string)($valor['nroboleta']), 'UTF-8', 'ISO-8859-1');
			$banco=mb_convert_encoding((string)($valor['banco']), 'UTF-8', 'ISO-8859-1');
			$nrocuenta=mb_convert_encoding((string)($valor['nrocuenta']), 'UTF-8', 'ISO-8859-1');
			$arreglo=mb_convert_encoding((string)($valor['arreglo']), 'UTF-8', 'ISO-8859-1');
			$url1=mb_convert_encoding((string)($valor['url1']), 'UTF-8', 'ISO-8859-1');
			$categoria=mb_convert_encoding((string)($valor['categoria']), 'UTF-8', 'ISO-8859-1');
			$cod_usuario_autoriz = mb_convert_encoding((string)($valor['cod_usuario_autoriz']), 'UTF-8', 'ISO-8859-1');
			$fecha_autoriz = mb_convert_encoding((string)($valor['fecha_autoriz']), 'UTF-8', 'ISO-8859-1');
			$usuario_autoriz_nombre= mb_convert_encoding((string)($valor['usuario_autoriz_nombre']), 'UTF-8', 'ISO-8859-1');
			$cod_motivoIngresoEgresoFK= mb_convert_encoding((string)($valor['cod_motivoIngresoEgresoFK']), 'UTF-8', 'ISO-8859-1');
			$nombre_usuario_edit= mb_convert_encoding((string)($valor['nombre_usuario_edit']), 'UTF-8', 'ISO-8859-1');

			$funcion= "obtenerdatosabmGasto(this)";
			if ($idgastos == "") {
				$funcion= "";
			}

			if ($estado == 'Activo') {
				$totalMonto += intval($monto);
			}
			$styleEstado = "";
			if ($estado == 'solicitado') {
				$styleEstado= "background-color: #ff5050;color: #ffffff";
				$registro_autorizacion_necesario= true;
			} else if ($estado == 'pendiente') {
				$styleEstado= "background-color: #b1b1b1a1;";
			}
	
			$styleName=CargarStyleTable($styleName);
			if ($estado == 'Activo') {
				$paginaImprimir .= "
					<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
					<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
					<td  style='width:25%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
					<td  id='td_datos_8' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_21' style='width:20%'>".$usuarionombre."</td>
					<td  id='' style='width:10%'>".$nombrelocal."</td>
					<td  id='td_datos_5' style='display:none'>".$estado."</td>
					<td  id='td_datos_7' style='display:none'>".$cod_local."</td>
					<td  id='td_datos_12' style='display:none'>".$url1."</td>
					<td  id='td_datos_13' style='display:none'>".$descripcion."</td>
					<td  id='td_datos_14' style='display:none'>".$motivo."</td>
					<td  id='td_datos_15' style='display:none'>".$cod_interConsultaFK."</td>
					<td  id='td_datos_16' style='display:none'>".$interconsulta_nombre."</td>
					<td  id='td_datos_17' style='display:none'>".$cod_usuario_autoriz."</td>
					<td  id='td_datos_18' style='display:none'>".$usuario_autoriz_nombre."</td>
					<td  id='td_datos_19' style='display:none'>".$fecha_autoriz."</td>
					<td  id='td_datos_20' style='display:none'>".$cod_motivoIngresoEgresoFK."</td>
					<td  id='td_datos_22' style='display:none'>".$nombre_usuario_edit."</td>
					</tr>
					</table>";
			}
	
			$paginaMotivo .= "<li class='list-group-item' style='padding: 0; padding-left: 0.5rem;'>
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='$funcion' style='".($estado=="Rechazado" || $estado=="Inactivo" ? "text-decoration: line-through;" : "")."'>
				<td id='td_id' style='width:5%; background-color: #efeded;color:red; $styleEstado'>".$idgastos."</td>
				<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
				<td  style='width:15%'>".$descripcion."</td>
				<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
				<td  id='td_datos_6' style='width:10%'>".$tipo."</td>
				<td  id='td_datos_3' style='width:15%'>".$fecha."</td>
				<td  id='td_datos_8' style='display: none;'>".$nroboleta."</td>
				<td  id='td_datos_9' style='display: none;'>".$banco."</td>
				<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
				<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
				<td  id='td_datos_21' style='width:20%'>".$usuarionombre."</td>
				<td  id='' style='width:10%'>".$nombrelocal."</td>
				<td  id='td_datos_5' style='display:none'>".$estado."</td>
				<td  id='td_datos_7' style='display:none'>".$cod_local."</td>
				<td  id='td_datos_12' style='display:none'>".$url1."</td>
				<td  id='td_datos_13' style='display:none'>".$descripcion."</td>
				<td  id='td_datos_14' style='display:none'>".$motivo."</td>
				<td  id='td_datos_15' style='display:none'>".$cod_interConsultaFK."</td>
				<td  id='td_datos_16' style='display:none'>".$interconsulta_nombre."</td>
				<td  id='td_datos_17' style='display:none'>".$cod_usuario_autoriz."</td>
				<td  id='td_datos_18' style='display:none'>".$usuario_autoriz_nombre."</td>
				<td  id='td_datos_19' style='display:none'>".$fecha_autoriz."</td>
				<td  id='td_datos_20' style='display:none'>".$cod_motivoIngresoEgresoFK."</td>
				</tr>
				</table>
			</li>";
		}

		$styleRegistroColor2= $styleRegistroColor;
		if ($registro_autorizacion_necesario) {
			$styleRegistroColor2= "#ff5050;color: #ffffff";
		}

 		$pagina .= '<li class="list-group-item" style="padding: 0; padding-left: 0.5rem;"><div class="card" style="width: 100%; margin: 0;">'.
			'<div class="card-header" style="padding-bottom: 0px; padding-top: 0px;background-color: '.$styleRegistroColor2.'" type="button" onclick="mostrarItems(\'zonaMotivos'.$cod_motivo.'\')">'.
				'<h6><b>'.$titulo_motivo.'</b>: <span>'.number_format($totalMonto, 0, ',', '.').'</span> Gs.</h6>'.
				($cod_motivo == -1 ? '' : '<img src="/GoodVentaAsisCap/iconos/add.png" class="iconoBtn" style="height: 35px; width: 35px;" title="Añadir registro" onclick="verCerrarVentanaAbmGasto(\'1\',\'1\');document.getElementById(\'inptMotivoMisGastos\').value= \''.$titulo_motivo.'\';">').
			'</div>'.
			'<div class="collapse" id="zonaMotivos'.$cod_motivo.'" style=""><ul class="list-group list-group-flush">'.
				$paginaMotivo.
			'</ul></div>'.
		'</div></li>';
	}

	$pagina .= '</ul></div>'.
		'</div>'.
	'</div>';
 }
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array(
	"1" => "exito",
	"2" => $pagina,
	"3" => $nroRegistro,
	"4" => $totalGasto,
	"5" => $totalZonaIngresos,
	"6" => $totalZonaCostosDirectos,
	"7" => $totalZonaGastosOperativos,
	"8" => $totalZonaSinCategorizar,
	"9" => $registros,
	"12" => $paginaImprimir,
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
			  $presupuesto= intval(mb_convert_encoding((string)($valor['presupuesto']), 'UTF-8', 'ISO-8859-1'));

			  $registros[] = array(
					"cod_motivo_ingreso_egreso" => $cod_motivo_ingreso_egreso,
					"descripcion" => $descripcion,
					"estado" => $estado,
					"categoria" => $categoria,
					"necesita_autorizacion" => $necesita_autorizacion,
					"presupuesto" => $presupuesto
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
			   <td  id='td_datos_5' style='display:none'>".number_format($presupuesto, 0, ',','.')."</td>
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

$consulta1="Insert into motivos_ingreso_egreso (descripcion,estado,categoria,necesita_autorizacion,presupuesto) values (upper(?),?, ?, ?, ?)";
$stmt = $mysqli->prepare($consulta1);
$ss='ssssi';
$stmt->bind_param($ss,$motivo,$estado,$categoria,$necesita_autorizacion,$presupuesto);

if (!$stmt->execute()) {
	echo "$consulta1\n$motivo\n";
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function editarMotivo($motivo,$estado,$categoria,$necesita_autorizacion,$presupuesto,$cod_usuarioFK,$idabm)
{
	
if($motivo==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$fechaActual= new DateTime();
$fechaActual=date_format($fechaActual,"Y-m-d H:i:s");

$mysqli=conectar_al_servidor();

$consulta1="update motivos_ingreso_egreso SET fecha_edit= '$fechaActual', cod_usuarioFK= $cod_usuarioFK, presupuesto= $presupuesto, descripcion = upper('$motivo'), estado ='$estado', categoria= '$categoria', necesita_autorizacion='$necesita_autorizacion' WHERE cod_motivo_ingreso_egreso ='$idabm'";
$stmt = $mysqli->prepare($consulta1);

if (!$stmt->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function buscaroption()
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from motivos_ingreso_egreso where estado='activo' order by descripcion asc  ";
		
		
		 $pagina="<option  value='' >SELECCIONAR</option>";       
   $paginaList = "";
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		      $cod_motivo_ingreso_egreso=$valor['cod_motivo_ingreso_egreso'];
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
			    	
			  $pagina.="<option  value='$cod_motivo_ingreso_egreso' >".$descripcion."</option>";     
			  
			  $paginaList.="<option id='$cod_motivo_ingreso_egreso' value='".$descripcion."'></option>";	
	  }
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
<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include('quitarseparadormiles.php');
include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include("classTable.php");
include("subir_foto_base64.php");

date_default_timezone_set('America/Asuncion');

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}
	
if($operacion=="nuevo" || $operacion=="editar")
{	
	$idgastos=$_POST['idgastos'];
$idgastos = utf8_decode($idgastos);
$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);
	$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);
	$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$codcaja=$_POST['codcaja'];
$codcaja = utf8_decode($codcaja);
$idaperturacierrecaja=$_POST['idaperturacierrecaja'];
$idaperturacierrecaja = utf8_decode($idaperturacierrecaja);
$nroboleta=$_POST['nroboleta'];
$nroboleta = utf8_decode($nroboleta);
$banco=$_POST['banco'];
$banco = utf8_decode($banco);
$nrocuenta=$_POST['nrocuenta'];
$nrocuenta = utf8_decode($nrocuenta);

$Arreglo=$_POST['Arreglo'];
$Arreglo = utf8_decode($Arreglo);

$cod_usuario = $user;
$personales = "";

$cod_motivo= $_POST['cod_motivoFK'];
$cod_motivo= utf8_decode($cod_motivo);

$cod_interConsultaFK= $_POST['cod_interConsultaFK'];
$cod_interConsultaFK= utf8_decode($cod_interConsultaFK);


// Comprueba si esta dentro del presupuesto
	$fechaActual= new DateTime();
	$primerDiaMes= $fechaActual->format('Y-m-01');
	$ultimoDiaMes= $fechaActual->format('Y-m-t');

	$informacion = buscarabmmotivoingresoegreso('', 'activo', $cod_motivo);
	$informacion2 = buscar('', $primerDiaMes, $ultimoDiaMes, 'Activo', $cod_local, '', '', '','true', $cod_motivo);

	if ($informacion["4"]["presupuesto"] && $informacion["4"]["presupuesto"] != '0')
	$totalGasto= intval(str_replace('.', '', $informacion2["4"])) + $monto;
	$limite_presupuesto= intval(str_replace('.', '', $informacion["4"]["presupuesto"]));
	if ($limite_presupuesto > 0 && $totalGasto > $limite_presupuesto) {
		$informacion =array("1" => "exito", "2" => "El gasto supera el presupuesto establecido.");
		echo json_encode($informacion);	
		exit;
	}

	abm($Arreglo,$nroboleta, $banco , $nrocuenta ,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion);

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
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);
$usuario=$_POST['usuario'];
$usuario = utf8_decode($usuario);
$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);

$arreglo=$_POST['arreglo'];
$arreglo = utf8_decode($arreglo);

$cod_motivoFK= $_POST['cod_motivoFK'];
$cod_motivoFK= utf8_decode($cod_motivoFK);
$ocultar_inactivos= $_POST['ocultar_inactivos'];
$ocultar_inactivos= utf8_decode($ocultar_inactivos);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
$informacion = buscar($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK);
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
	$informacion2 = buscar('', $primerDiaMes, $ultimoDiaMes, 'Activo', $cod_local, '', '', '','true', $cod_motivo);

	echo json_encode(array("1" => "exito", "2" => $informacion["4"]["presupuesto"], "3" => $informacion2["4"]));	
	exit;
}

if($operacion=="evaluacionGasto")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);

	buscarevaluacionGasto($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionpagosventa")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);

	evaluacionpagosventa($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionproductodcomprados")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);

	evaluacionproductodcomprados($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionproductodvendidos")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);

	evaluacionproductodvendidos($fecha1,$fecha2,$local);

}
if($operacion=="evaluacionpagoscomprados")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);

	evaluacionpagoscomprados($fecha1,$fecha2,$local);

}

if($operacion=="evaluacion")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);

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
$buscar = utf8_decode($buscar);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

	$informacion = buscarabmmotivoingresoegreso($buscar,$estado);
	echo json_encode($informacion);	
	exit;
}


if($operacion=="NuevoMotivo")
{
	$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

$categoria=$_POST['categoria'];
$categoria = utf8_decode($categoria);

$necesita_autorizacion= $_POST['necesita_autorizacion'];
$necesita_autorizacion = utf8_decode($necesita_autorizacion);

$presupuesto= $_POST['presupuesto'];
$presupuesto = utf8_decode($presupuesto);

	NuevoMotivo($motivo,$estado,$categoria,$necesita_autorizacion, $presupuesto);

}

if($operacion=="editarMotivo")
{
	$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

$idabm=$_POST['idabm'];
$idabm = utf8_decode($idabm);

$categoria=$_POST['categoria'];
$categoria = utf8_decode($categoria);

$necesita_autorizacion= $_POST['necesita_autorizacion'];
$necesita_autorizacion = utf8_decode($necesita_autorizacion);

$presupuesto= $_POST['presupuesto'];
$presupuesto = utf8_decode($presupuesto);

	editarMotivo($motivo,$estado,$categoria,$necesita_autorizacion,$presupuesto, $user, $idabm);

}	

if($operacion=="buscaroption")
{
	buscaroption();
}

if ($operacion == "aprobarMovimiento") {
	$idgastos= $_POST['idgastos'];
	$idgastos= utf8_decode($idgastos);
	aprobarMovimiento($idgastos, $user);
}
if ($operacion == "combinarmotivoingresoegreso") {
	$cod_motivoIngresoEgreso= utf8_decode($_POST['cod_motivo_ingreso_egreso']);
	$cod_motivoIngresoEgreso_dest= utf8_decode($_POST['cod_motivo_ingreso_egreso_destino']);

	combinarMotivoIngresoEgreso($cod_motivoIngresoEgreso, $cod_motivoIngresoEgreso_dest, $user);
}
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
	$sql= "UPDATE motivos_ingreso_egreso SET estado= '', cod_usuarioFK= ?, fecha_edit= ? WHERE cod_motivo_ingreso_egreso = ?";
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

function aprobarMovimiento($idgastos, $cod_usuarioFK) {
	$fechaActual= new DateTime();
	$fechaActual= $fechaActual->format('Y-m-d H:i:s');

	$mysqli=conectar_al_servidor();

	$sql= "UPDATE gastos SET cod_usuario_autoriz= ?, fecha_autoriz= ?, estado='Activo' WHERE idgastos= ?";
	$stmt = $mysqli->prepare($sql);

	$stmt->bind_param('isi',$cod_usuarioFK,$fechaActual,$idgastos);

	if (!$stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
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

function abm($Arreglo,$nroboleta, $banco , $nrocuenta,$idgastos,$monto,$motivo,$fecha,$estado,$personales,$cod_usuario,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$cod_motivo,$cod_interConsultaFK,$operacion)
{
		
if($monto==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

// Identifica si el motivo necesita autorizacion
$registros_motivos= buscarabmmotivoingresoegreso('%', 'activo',$cod_motivo);

if($operacion=="nuevo")
{

if ($estado == 'Activo' && $registros_motivos['4']['necesita_autorizacion'] == '1') {
	$estado = "pendiente";
}

$consulta1="Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta,cod_motivoIngresoEgresoFK,cod_interConsultaFK)
values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $mysqli->prepare($consulta1);

$ss='ssssssssssssssss';
$stmt->bind_param($ss,$Arreglo,$monto,$motivo,$fecha,$estado,$cod_usuario,$personales,$cod_local,$tipo,$codcaja,$idaperturacierrecaja,$nroboleta, $banco , $nrocuenta,$cod_motivo,$cod_interConsultaFK);


}


if($operacion=="editar")
{

if ($estado == 'Activo' && !empty($cod_usuario_autoriz)) {
	$estado = "pendiente";
}

$consulta1="Update gastos set arreglo=?, monto=?,motivo=?,fecha=?,estado=?,cod_usuario=?,
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

$informacion =array("1" => "exito", "2" => $idgastos);
echo json_encode($informacion);	
exit;
	
}

function buscar($arreglo,$fecha1,$fecha2,$estado,$cod_local,$tipo,$usuario,$fecha,$ocultar_inactivos,$cod_motivoFK)
{
	$mysqli=conectar_al_servidor();
	$pagina='';

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

	// Se limpia el primer ' and'
	if (strlen($sqlFiltro) > 0) {
		$sqlFiltro = "where" . substr($sqlFiltro, 4, strlen($sqlFiltro));
	}
		 
	$sql= "Select g.arreglo,g.monto,g.motivo as descripcion,g.fecha,g.estado,g.cod_usuario,g.idgastos,g.tipo,
	g.cod_local,g.nroboleta,g.banco,g.nrocuenta,g.url1,g.cod_interConsultaFK,
	g.cod_usuario_autoriz, g.fecha_autoriz,
	(Select asunto from interconsulta where cod_interConsulta=g.cod_interConsultaFK) as interconsulta_nombre,
	(Select nombre_persona from persona where cod_persona=g.cod_usuario) as usuarionombre,
	(Select nombre_persona from persona where cod_persona=g.cod_usuario_autoriz) as usuario_autoriz_nombre,
	m.descripcion AS motivo, m.categoria,
	(Select Nombre from local l where l.cod_local=g.cod_local) as nombrelocal
	from gastos g left join motivos_ingreso_egreso m on m.cod_motivo_ingreso_egreso = g.cod_motivoIngresoEgresoFK $sqlFiltro ORDER BY necesita_autorizacion DESC, g.idgastos DESC";

   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$totalZonaIngresos= 0;
$totalZonaCostosDirectos= 0;
$totalZonaGastosOperativos= 0;
$totalZonaSinCategorizar= 0;

$elementosZonaIngresos= "";
$elementosZonaCostosDirectos= "";
$elementosZonaGastosOperativos= "";
$paginaImprimir= "";

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	$interconsulta_nombre= utf8_encode($valor['interconsulta_nombre']);
			$cod_interConsultaFK= utf8_encode($valor['cod_interConsultaFK']);
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $monto=utf8_encode($valor['monto']);
		  	  $motivo=utf8_encode($valor['motivo']);
			  $descripcion=utf8_encode($valor['descripcion']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $nroboleta=utf8_encode($valor['nroboleta']);
		  	  $banco=utf8_encode($valor['banco']);
		  	  $nrocuenta=utf8_encode($valor['nrocuenta']);
			  $arreglo=utf8_encode($valor['arreglo']);
			  $url1=utf8_encode($valor['url1']);
			  $categoria=utf8_encode($valor['categoria']);
			  $cod_usuario_autoriz = utf8_encode($valor['cod_usuario_autoriz']);
			  $fecha_autoriz = utf8_encode($valor['fecha_autoriz']);
			  $usuario_autoriz_nombre= utf8_encode($valor['usuario_autoriz_nombre']);
			  
		  	 $totalGasto=$totalGasto+$monto;
			   
			 $styleEstado = "";
			 if ($estado == 'pendiente') {
				$styleEstado= "background-color: #ff5050;color: #ffffff";
			 }

		  	  $styleName=CargarStyleTable($styleName);
			$paginaImprimir .= "
					<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
					<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
					<td  style='width:25%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
					<td  id='td_datos_3' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_8' style='width:20%'>".$usuarionombre."</td>
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
					</tr>
					</table>";
		switch ($categoria) {
			case 'ingreso':
				$totalZonaIngresos += $monto;
				$elementosZonaIngresos .= "<li class='list-group-item'>
					<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
					<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)' style='$styleEstado'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
					<td  style='width:25%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
					<td  id='td_datos_3' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_8' style='width:20%'>".$usuarionombre."</td>
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
					</tr>
					</table>
				</li>";
				break;
			case 'directo':
				$totalZonaCostosDirectos += $monto;
				$elementosZonaCostosDirectos .= "<li class='list-group-item'>
					<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
					<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)' style='$styleEstado'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
					<td  style='width:25%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
					<td  id='td_datos_3' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_8' style='width:20%'>".$usuarionombre."</td>
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
					</tr>
					</table>
				</li>";
				break;
			case 'operativo':
				$totalZonaGastosOperativos += $monto;
				$elementosZonaGastosOperativos .= "<li class='list-group-item'>
					<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
					<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)' style='$styleEstado'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
					<td  style='width:25%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
					<td  id='td_datos_3' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_8' style='width:20%'>".$usuarionombre."</td>
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
					</tr>
					</table>
				</li>";
				break;
			default:
				$totalZonaSinCategorizar += $monto;
				$pagina.="
					<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
					<tr id='tbSelecRegistro' onclick='obtenerdatosabmGasto(this)' style='$styleEstado'>
					<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idgastos."</td>
					<td  id='td_datos_2' style='width:15%'>".$motivo."</td>
					<td  style='width:25%'>".$descripcion."</td>
					<td  id='td_datos_1' style='width:10%'>". number_format($monto,'0',',','.')."</td>
					<td  id='td_datos_6' style='width:5%'>".$tipo."</td>
					<td  id='td_datos_3' style='width:10%'>".$fecha."</td>
					<td  id='td_datos_3' style='display: none;'>".$nroboleta."</td>
					<td  id='td_datos_9' style='display: none;'>".$banco."</td>
					<td  id='td_datos_10' style='display: none;'>".$nrocuenta."</td>
					<td  id='td_datos_11' style='display: none;'>".$arreglo."</td>
					<td  id='td_datos_8' style='width:20%'>".$usuarionombre."</td>
					<td  id='' style='width:10%'>".$nombrelocal."</td>
					<td  id='td_datos_5' style='display:none'>".$estado."</td>
					<td  id='td_datos_7' style='display:none'>".$cod_local."</td>
					<td  id='td_datos_12' style='display:none'>".$url1."</td>
					<td  id='td_datos_13' style='display:none'>".$descripcion."</td>
					<td  id='td_datos_14' style='display:none'>".$motivo."</td>
					<td  id='td_datos_15' style='display:none'>".$categoria."</td>
					<td  id='td_datos_15' style='display:none'>".$cod_interConsultaFK."</td>
					<td  id='td_datos_16' style='display:none'>".$interconsulta_nombre."</td>
					</tr>
					</table>";
				break;
		}
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array(
	"1" => "exito",
	"2" => $pagina,
	"3" => $nroRegistro,
	"4" => number_format($totalGasto,'0',',','.'),
	"5" => number_format($totalZonaIngresos, '0', ',', '.'),
	"6" => number_format($totalZonaCostosDirectos, '0', ',', '.'),
	"7" => number_format($totalZonaGastosOperativos, '0', ',', '.'),
	"8" => $elementosZonaIngresos,
	"9" => $elementosZonaCostosDirectos,
	"10" => $elementosZonaGastosOperativos,
	"11" => number_format($totalZonaSinCategorizar, '0', ',', '.'),
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
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $monto=utf8_encode($valor['monto']);
		  	  $motivo=utf8_encode($valor['motivo']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $personales=utf8_encode($valor['personales']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
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



$idPago = utf8_encode($valor['idPago']);    
$num_factura = utf8_encode($valor['num_factura']);    
$Monto = utf8_encode($valor['Monto']);      
$Fecha = utf8_encode($valor['Fecha']);      
$cobradornombre = utf8_encode($valor['cobradornombre']);      
$cod_venta = utf8_encode($valor['cod_venta_fk']);      
$nombrezona = utf8_encode($valor['nombrezona']);      
$hora = utf8_encode($valor['hora']);      
$comision = utf8_encode($valor['comision']);      
$lot = utf8_encode($valor['lot']);      
$lat = utf8_encode($valor['lat']);      
$nombrecliente = utf8_encode($valor['nombrecliente']);      
$nombrelocal = utf8_encode($valor['nombrelocal']);      
$nrofactura = utf8_encode($valor['nrofactura']);      
$totalPagado=$Monto+$totalPagado;
 	$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);   
			
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
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cod_producto=utf8_encode($valor['cod_producto']);
		  	  $NombreMarca=utf8_encode($valor['NombreMarca']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $precio_producto=utf8_encode($valor['precio_producto']);
		  	
		  	
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
		      $fechadelpago=utf8_encode($valor['fechadelpago']);
		  	  $fechaapagar=utf8_encode($valor['fechaapagar']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	
		  	
		  	
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



$cod_producto = utf8_encode($valor['cod_producto']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$totalCantidad = utf8_encode($valor['totalCantidad']);          
$totalVenta = utf8_encode($valor['totalVenta']); 
$nombrelocal = utf8_encode($valor['nombrelocal']); 
$totalCosto = utf8_encode($valor['totalCosto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 

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
  	$s='ss';
	$sqlFiltro = "descripcion like ?  and estado=?";
	if (!empty($cod_motivo) && $cod_motivo > 0) {
		$sqlFiltro .= " and cod_motivo_ingreso_egreso = ?";
		$s .= 's';
	}
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select *
        from motivos_ingreso_egreso where $sqlFiltro order by descripcion asc ";

   $stmt = $mysqli->prepare($sql);
$buscar1="%".$buscar."%";
if (!empty($cod_motivo) && $cod_motivo > 0) {
	$stmt->bind_param($s,$buscar1,$Estado, $cod_motivo);
} else {
	$stmt->bind_param($s,$buscar1,$Estado);
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
		  	  $descripcion=utf8_encode($valor['descripcion']);
		  	  $estado=utf8_encode($valor['estado']);
			  $categoria= utf8_encode($valor['categoria']);
			  $necesita_autorizacion = utf8_encode($valor['necesita_autorizacion']);
			  $presupuesto= intval(utf8_encode($valor['presupuesto']));
		  	 
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
			
			$registros = array(
				"cod_motivo_ingreso_egreso" => $cod_motivo_ingreso_egreso,
				"descripcion" => $descripcion,
				"estado" => $estado,
				"categoria" => $categoria,
				"necesita_autorizacion" => $necesita_autorizacion,
				"presupuesto" => $presupuesto
			);
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

$consulta1="update motivos_ingreso_egreso SET fecha_edit= $fechaActual, cod_usuarioFK= $cod_usuarioFK, presupuesto= $presupuesto, descripcion = upper('$motivo'), estado ='$estado', categoria= '$categoria', necesita_autorizacion='$necesita_autorizacion' WHERE cod_motivo_ingreso_egreso ='$idabm'";
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
		  	  $descripcion=utf8_encode($valor['descripcion']);
			    	
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

verificar($operacion);
?>
<?php
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("subir_foto_base64.php");
include("quitarseparadormiles.php");
include("classTable.php");
$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

function ObtenerDatos($operacion)
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



if($operacion=="nuevo" || $operacion=="editar" )
{


$idAbm=$_POST['idAbm'];
$idAbm = mb_convert_encoding((string)($idAbm), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$idAbmCliente=$_POST['idAbmCliente'];
$idAbmCliente = mb_convert_encoding((string)($idAbmCliente), 'ISO-8859-1', 'UTF-8');
$cod_garanteFK=$_POST['cod_garanteFK'];
$cod_garanteFK = mb_convert_encoding((string)($cod_garanteFK), 'ISO-8859-1', 'UTF-8');
$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = mb_convert_encoding((string)($cod_cobradorFK), 'ISO-8859-1', 'UTF-8');

$cod_localFK=$_POST['cod_localFK'];
$cod_localFK = mb_convert_encoding((string)($cod_localFK), 'ISO-8859-1', 'UTF-8');
$user=$_POST['useru'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

$observacion=$_POST['observacion'];
$observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');

abm($idAbm,$estado,$idAbmCliente,$cod_garanteFK,$cod_cobradorFK,$cod_localFK,$user,$observacion,$operacion);

}




if($operacion=="EditarCliente" )
{



$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');
$direccion=$_POST['direccion'];
$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
$telefono=$_POST['telefono'];
$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');
$email=$_POST['email'];
$email = mb_convert_encoding((string)($email), 'ISO-8859-1', 'UTF-8');
$cod_cliente=$cod_persona;
$whapp=$_POST['whapp'];
$whapp = mb_convert_encoding((string)($whapp), 'ISO-8859-1', 'UTF-8');
$idzonaFk=$_POST['idzonaFk'];
$idzonaFk = mb_convert_encoding((string)($idzonaFk), 'ISO-8859-1', 'UTF-8');
$lugardetrabajo=$_POST['lugardetrabajo'];
$lugardetrabajo = mb_convert_encoding((string)($lugardetrabajo), 'ISO-8859-1', 'UTF-8');
$salario=$_POST['salario'];
$salario = quitarseparadormiles($salario);
$antiguedad=$_POST['antiguedad'];
$antiguedad = mb_convert_encoding((string)($antiguedad), 'ISO-8859-1', 'UTF-8');
$teleftrab1=$_POST['teleftrab1'];
$teleftrab1 = mb_convert_encoding((string)($teleftrab1), 'ISO-8859-1', 'UTF-8');
$teleftrab2=$_POST['teleftrab2'];
$teleftrab2 = mb_convert_encoding((string)($teleftrab2), 'ISO-8859-1', 'UTF-8');
$direcciontrab=$_POST['direcciontrab'];
$direcciontrab = mb_convert_encoding((string)($direcciontrab), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

$obsTrabajo=$_POST['obsTrabajo'];
$obsTrabajo = mb_convert_encoding((string)($obsTrabajo), 'ISO-8859-1', 'UTF-8');

abmCliente($idzonaFk,$whapp,$cod_persona,$direccion,$telefono,$email,$cod_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$estado,$obsTrabajo,$operacion);

}

 
 if($operacion=="addmasreferencias"){
 	$totalCargado=$_POST["totalCargado"];
 	$totalCargado=mb_convert_encoding((string)($totalCargado), 'ISO-8859-1', 'UTF-8');
	$idcliente=$_POST["idcliente"];
 	$idcliente=mb_convert_encoding((string)($idcliente), 'ISO-8859-1', 'UTF-8');
 	addmasreferencias($totalCargado,$idcliente);
 }
 
  if($operacion=="BuscarImprimirSolicitudCredito"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	 BuscarImprimirSolicitudCredito($buscar);
 }



 if($operacion=="buscarmasreferencias"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarmasreferencias($buscar);
 }
 
  if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
	$codlocal=$_POST["codlocal"];
 	$codlocal=mb_convert_encoding((string)($codlocal), 'ISO-8859-1', 'UTF-8');
 	buscarvista($buscar,$codlocal);
 }
 
 
  if($operacion=="addProductoCredito"){
 	$totalCargado=$_POST["totalCargado"];
 	$totalCargado=mb_convert_encoding((string)($totalCargado), 'ISO-8859-1', 'UTF-8');
	$idSolicitudCredito=$_POST["idSolicitudCredito"];
 	$idSolicitudCredito=mb_convert_encoding((string)($idSolicitudCredito), 'ISO-8859-1', 'UTF-8');
 	addProductoCredito($totalCargado,$idSolicitudCredito);
 }
 
 
  if($operacion=="buscarProductoSolicitud"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarProductoSolicitud($buscar);
 }
 
 
 
  if($operacion=="eliminar"){
 	$idSolicitudCredito=$_POST["idSolicitudCredito"];
 	$idSolicitudCredito=mb_convert_encoding((string)($idSolicitudCredito), 'ISO-8859-1', 'UTF-8');
 	eliminar($idSolicitudCredito);
 }
 



	 
  if($operacion=="buscarSolicitudCredito"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST["fecha2"];
 	$fecha2=mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$documento=$_POST["documento"];
 	$documento=mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$cliente=$_POST["cliente"];
 	$cliente=mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	$zona=$_POST["zona"];
 	$zona=mb_convert_encoding((string)($zona), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$local=$_POST["local"];
 	$local=mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
	
	$vendedor=$_POST["vendedor"];
 	$vendedor=mb_convert_encoding((string)($vendedor), 'ISO-8859-1', 'UTF-8');

 	BuscarRegistro($fecha1,$fecha2,$documento,$cliente,$zona,$estado,$local,$vendedor);
 }
 
 
 
  if($operacion=="buscarFotosGaleria"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarFotosGaleria($buscar);
 }
 
 
   if($operacion=="buscarProductoSolicitudVista"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarProductoSolicitudVista($buscar);
 }
 
 
   if($operacion=="buscarmasreferenciasVista"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarmasreferenciasVista($buscar);
 }
 
 

}


function buscarProductoSolicitud($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select iddetallesolicitud, cantidad, codProducto,cuotas, plan, idSolicitudCreditoFK ,(select nombre_producto from producto where codProducto=cod_producto) as producto
,(select cod_barra from producto where codProducto=cod_producto) as cod_Barra from detallesolicitud where idSolicitudCreditoFK='$buscar' ";

// echo($sql);
// exit;
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$totalVenta=0;
$cuotas =1;
$Cuotero =0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$cuotas = mb_convert_encoding((string)($valor['cuotas']), 'UTF-8', 'ISO-8859-1');
$iddetallesolicitud = mb_convert_encoding((string)($valor['iddetallesolicitud']), 'UTF-8', 'ISO-8859-1');
$cantidad = mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1');     
$codProducto = mb_convert_encoding((string)($valor['codProducto']), 'UTF-8', 'ISO-8859-1');          
$plan = mb_convert_encoding((string)($valor['plan']), 'UTF-8', 'ISO-8859-1');          
$idSolicitudCreditoFK = mb_convert_encoding((string)($valor['idSolicitudCreditoFK']), 'UTF-8', 'ISO-8859-1'); 
$producto = mb_convert_encoding((string)($valor['producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_Barra = mb_convert_encoding((string)($valor['cod_Barra']), 'UTF-8', 'ISO-8859-1'); 

$plan = quitarseparadormiles($plan);

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosProductoCredito(this)'  name='tdDetalleSolicitudCredito'>
<td  id='td_id_1' style='display:none'>".$codProducto."</td>
<td  id='td_datos_1' style='width:20%'>".$cod_Barra."</td>
<td  id='td_datos_2' style='width:40%'>".$producto."</td>
<td id='td_datos_3' style='width:10%'>".$cantidad."</td>
<td id='td_datos_4' style='width:20%'>".number_format($plan,'0',',','.')."</td>
<td id='td_id_2' style='display:none'>".$iddetallesolicitud."</td>
<td id='td_datos_5' style='width:10%'>".$cuotas."</td>
</tr>
</table>";

$totalVenta= $totalVenta + ($cantidad * quitarseparadormiles($plan)) ;

if( $cuotas==""){
	 $cuotas=1;
}
 $Cuotero = $totalVenta / $cuotas;
}
}

$ResultadoTotal= "<p>".number_format($totalVenta,'0',',','.') ."</p><br> <p style='font-size: 17px; margin-top: -20px;' >".$cuotas." * ".number_format(round($Cuotero),'0',',','.')."</p>";

    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina ,"3" => $ResultadoTotal );
echo json_encode($informacion);	
exit;
}





/*Buscar Registro en vista*/
function buscarFotosGaleria($codigo)
{
$mysqli=conectar_al_servidor();



$sql= "select  cl.cod_cliente ,foto1 , foto2
 from   cliente cl  
where  cod_cliente='".$codigo."' ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);


// echo($sql);
// exit;

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


$cod_cliente = mb_convert_encoding((string)($valor['cod_cliente']), 'UTF-8', 'ISO-8859-1'); 
$foto1 = ($valor['foto1']);  
$foto2 = ($valor['foto2']);         

		$pagina .= buscarFotosGaleriaDetalle($cod_cliente);
		 


}
}

 
$informacion =array("1" => "exito","2" => $pagina ,"3" => $foto1 ,"4" => $foto2);
echo json_encode($informacion);	
exit;
}




function buscarFotosGaleriaDetalle($codigo)
{
$mysqli=conectar_al_servidor();



$sql= "select  * from   fotos_cliente cl   where  cod_clienteFK='".$codigo."' ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);


// echo($sql);
// exit;

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


$url = mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1'); 
$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');  


$pagina .= " 
<table class='tableabm'>
<tr>
<td style='width:100%;'>
<center>
<div style=' width: 90%; height: 90%;' class='imgFotoCi'>
<p class='pTituloRepor' style='width:97%'>".$descripcion."</p>
 <IMG style='width: 100%;' SRC=".$url."  >
</div>
</center>
</td>
</tr>
</table>
		 
"; 

}
}

 return $pagina;
}








function eliminar($idSolicitudCredito)
{

$user = solicitudEliminadoValorPost('useru', '0');
$respuesta = registrarSolicitudEliminacionGenerica(
	'solicitudcredito',
	'idSolicitudCredito',
	$idSolicitudCredito,
	'Solicitud de eliminacion de solicitud de credito.',
	$user,
	'Solicitud credito: '.$idSolicitudCredito
);
if (isset($respuesta["1"]) && $respuesta["1"] != "exito") {
	echo json_encode($respuesta);
	exit;
}

$informacion =array("1" => "exito", "2" => "Solicitud de eliminacion registrada.");
echo json_encode($informacion);	
exit;

}







function abmCliente($idzonaFk,$whapp,$cod_persona,$direccion,$telefono,$email,$cod_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$estado,$obsTrabajo,$operacion)
{

$mysqli=conectar_al_servidor(); 


$consulta1="Update persona set direccion=Upper(?),telefono=Upper(?),email=Upper(?) where cod_persona=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$direccion,$telefono,$email,$cod_persona);

if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


if($estado=="APROBADO"){
	
	$consulta2="update cliente set whapp=?,lugardetrabajo=?,salario=?,antiguedad=?,teleftrab1=?,teleftrab2=?,direcciontrab=? ,accesocredito='Confirmado',obsTrabajo=?  where cod_cliente=$cod_persona ";	

$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssss';
$stmt2->bind_param($ss,$whapp,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$obsTrabajo);
	
}else{
	
	$consulta2="update cliente set whapp=?,lugardetrabajo=?,salario=?,antiguedad=?,teleftrab1=?,teleftrab2=?,direcciontrab=? ,obsTrabajo=? where cod_cliente=$cod_persona ";	

$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssss';
$stmt2->bind_param($ss,$whapp,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$obsTrabajo);
	
}


if (!$stmt2->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt2->errno.') '.$stmt2->error, E_USER_ERROR);
exit;

}

// echo($consulta2);
// exit;


 mysqli_close($mysqli);
$informacion =array("1" => "exito","2"=>$cod_persona);
echo json_encode($informacion);	
exit;

}




function abm($idAbm,$estado,$idAbmCliente,$cod_garanteFK,$cod_cobradorFK,$cod_localFK,$cod_usu,$observacion,$operacion)
{

$mysqli=conectar_al_servidor(); 

date_default_timezone_set('America/Anguilla');    
$fecha_inser = date('Y-m-d', time()); 
	

if($operacion=="nuevo") 
{

$consulta1=" Insert into solicitudcredito ( fecha, estado, cod_clienteFK, cod_codeudorFK, cod_cobradorFK,cod_localFK,observacion)
values('$fecha_inser','PENDIENTE',$idAbmCliente,$cod_garanteFK,$cod_cobradorFK,$cod_localFK,'$observacion')";
$stmt1 = $mysqli->prepare($consulta1);


}


if($operacion=="editar")
{
if (solicitudEliminadoEsEstadoInactivo($estado)) {
	$respuesta = registrarSolicitudEliminacionGenerica(
		'solicitudcredito',
		'idSolicitudCredito',
		$idAbm,
		'Solicitud de eliminacion de solicitud de credito.',
		$cod_usu,
		'Solicitud credito: '.$idAbm
	);
	echo json_encode($respuesta);
	exit;
}

$consulta1="Update solicitudcredito set  estado=Upper(?),cod_localFK=Upper(?), cod_clienteFK=Upper(?), cod_codeudorFK=Upper(?), cod_usuarioFK=$cod_usu ,observacion='$observacion' where idSolicitudCredito=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$estado,$cod_localFK,$idAbmCliente,$cod_garanteFK,$idAbm);

}


if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


if($operacion=="nuevo") {
	$idAbm=obtenerUltimaId();
}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2"=>$idAbm);
echo json_encode($informacion);	
exit;

}

function addmasreferencias($totalCargado,$cod_cliente)
{

if($cod_cliente=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
$control=1;	
if($totalCargado>0){
	
$consulta= "delete from referenciascliente where cod_clienteFk='$cod_cliente' "; 


$stmt1 = $mysqli->prepare($consulta);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
}

while($control<=$totalCargado){

$observacion=$_POST['observacion'.$control];
$observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');

$telef=$_POST['telefono'.$control];
$telef = mb_convert_encoding((string)($telef), 'ISO-8859-1', 'UTF-8');

$direccion=$_POST['direccion'.$control];
$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');

$referencias=$_POST['referencia'.$control];
$referencias = mb_convert_encoding((string)($referencias), 'ISO-8859-1', 'UTF-8');

$Tipo=$_POST['Tipo'.$control];
$Tipo = mb_convert_encoding((string)($Tipo), 'ISO-8859-1', 'UTF-8');

$obs=$_POST['obs'.$control];
$obs = mb_convert_encoding((string)($obs), 'ISO-8859-1', 'UTF-8');

$consulta="Insert into referenciascliente ( telef, direccion, referencias, observacion, cod_clienteFk, tipo,obs)
values(?,?,?,?,?,?,?)";

$stmt1 = $mysqli->prepare($consulta);
$ss='sssssss';
$stmt1->bind_param($ss,$telef,$direccion,$referencias,$observacion, $cod_cliente,$Tipo,$obs);

if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$control=$control+1;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


function obtenerUltimaId()
{
	$cod_persona ="";
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $sql= "Select idSolicitudCredito from solicitudcredito  order by idSolicitudCredito desc limit 1";
	
   $stmt = $mysqli->prepare($sql); 
 if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_persona=$valor['idSolicitudCredito'];
		   	 
			  
	  }
 }
 
  mysqli_close($mysqli);
 return $cod_persona;
}

/*Buscar Registro en vista*/
function BuscarRegistro($fecha1,$fecha2,$documento,$cliente,$zona,$estado,$local,$vendedor)
{
$mysqli=conectar_al_servidor();

 $condicionVendedor="";
	 if($vendedor!=""){
	   $condicionVendedor="and  cod_cobradorFK ='".$vendedor."'";		
	 }
	 

$condicionFecha="";
if($fecha1!="" || $fecha2!=""){
$condicionFecha="and fecha between '$fecha1' and '$fecha2' ";
}
$condiciondocumento="";
if($documento!=""){
$condiciondocumento="and cl.ci_cliente= '".$documento."' ";
}
$condicioncliente="";
if($cliente!=""){
$condicioncliente="and (Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK ) like '%".$cliente."%' ";
}
$condicionzona="";
if($zona!=""){
$condicionzona="and cl.idzonaFk= '".$zona."' ";
}
$condicionlocal="";
if($local!=""){
$condicionlocal="and cod_localFK= '".$local."' ";
}

$condicionestado="";
if($estado!=""){
$condicionestado="and sc.estado= '".$estado."' ";
}

$sql= "select observacion,idSolicitudCredito,detalleVenta, fecha, sc.estado, cod_clienteFK, cod_codeudorFK, cod_cobradorFK,cod_usuarioFK,
(Select nombre from zona where idzonaFk=idzona )as zona,
(Select nombre_persona from persona pra where pra.cod_persona =cod_cobradorFK )as UsuarioIngresa,
(Select nombre_persona from persona pra where pra.cod_persona = cod_usuarioFK )as Usuarioaprueba,
cl.whapp,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.obsTrabajo,
cl.idzonaFk,cl.lugardetrabajo,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.direcciontrab,cl.fechanac,
(Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK )as cliente,
(Select nombre_persona from persona pra where pra.cod_persona=cod_codeudorFK )as garante
 from solicitudcredito sc
 inner join  cliente cl on cl.cod_cliente=sc.cod_clienteFK 
 inner join   persona pr on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionFecha.$condicionlocal.$condicionestado.$condicionVendedor."  limit 100";
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

$obsTrabajo = mb_convert_encoding((string)($valor['obsTrabajo']), 'UTF-8', 'ISO-8859-1'); 
$cod_usuarioFK = mb_convert_encoding((string)($valor['cod_usuarioFK']), 'UTF-8', 'ISO-8859-1'); 
$UsuarioIngresa = mb_convert_encoding((string)($valor['UsuarioIngresa']), 'UTF-8', 'ISO-8859-1'); 
$Usuarioaprueba = mb_convert_encoding((string)($valor['Usuarioaprueba']), 'UTF-8', 'ISO-8859-1'); 
$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1'); 
$cod_codeudorFK = mb_convert_encoding((string)($valor['cod_codeudorFK']), 'UTF-8', 'ISO-8859-1');   
$garante = mb_convert_encoding((string)($valor['garante']), 'UTF-8', 'ISO-8859-1');   
$idSolicitudCredito = mb_convert_encoding((string)($valor['idSolicitudCredito']), 'UTF-8', 'ISO-8859-1');  
$cod_clienteFK = mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');   
$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 

$detalleVenta = mb_convert_encoding((string)($valor['detalleVenta']), 'UTF-8', 'ISO-8859-1'); 

$Aprueba="";
if($cod_usuarioFK!="" && $estado!="PENDIENTE" ){
	$Aprueba="<br>".$Usuarioaprueba;
}

$producto=buscarDetalleProductoSolicitud($idSolicitudCredito);

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosSolicitudCredito(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idSolicitudCredito."</td>
<td  id='td_datos_1' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_2' style='display:none'>".$rut_cliente."</td>
<td  id='td_datos_3' style='width:20%'>".$nombre_persona."</td>
<td  id='td_datos_4' style='width:10%'>".$zona."</td>
<td  id='td_datos_5' style='display:none'>".$telefono."</td>
<td  id='td_datos_6' style='display:none'>".$direccion."</td>
<td  id='td_datos_7' style='display:none'>".$email."</td>
<td  id='td_datos_8' style='display:none'>".$whapp."</td>
<td  id='' style='width:10%'>".$estado.$Aprueba."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='width:10%'>".$UsuarioIngresa."</td>
<td  id='td_datos_25' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_12' style='display:none'>".number_format($salario,'0',',','.')."</td>
<td  id='td_datos_13' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_14' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_15' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_16' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_17' style='display:none'>".$fechanac."</td>
<td  id='td_datos_18' style='display:none'>".$garante."</td>
<td  id='td_datos_19' style='display:none'>".$cod_codeudorFK."</td>
<td  id='td_datos_20' style='width:30%'>".$producto."</td>
<td  id='td_datos_21' style='display:none'>".$cod_clienteFK."</td>
<td  id='td_datos_22' style='display:none'>".$detalleVenta."</td>
<td  id='td_datos_23' style='display:none'>".$observacion."</td>
<td  id='td_datos_24' style='display:none'>".$obsTrabajo."</td>
</tr>
</table>";


}
}



    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina),"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function BuscarMasRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito,$registrocargado)
{
$mysqli=conectar_al_servidor();

$condicionCodigo="";
if($codigo!=""){
$condicionCodigo="and pr.cod_persona = '".$codigo."' ";
}
$condiciondocumento="";
if($documento!=""){
$condiciondocumento="and cl.ci_cliente= '".$documento."' ";
}
$condicioncliente="";
if($cliente!=""){
$condicioncliente="and pr.nombre_persona like '%".$cliente."%' ";
}
$condicionzona="";
if($zona!=""){
$condicionzona="and cl.idzonaFk= '".$zona."' ";
}

$condicionaccesocredito="";
if($accesocredito!=""){
$condicionaccesocredito="and cl.accesocredito= '".$accesocredito."' ";
}

$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,cl.accesocredito,
(Select nombre from zona where idzonaFk=idzona )as zona,cl.fecha_insert,cl.fecha_edit,cl.sms,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado=? ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionCodigo.$condicionaccesocredito." order by pr.nombre_persona limit ".$registrocargado." , 100 ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$estado);

if ( ! $stmt->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$sms = mb_convert_encoding((string)($valor['sms']), 'UTF-8', 'ISO-8859-1'); 
$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$Calificacion = mb_convert_encoding((string)($valor['Calificacion']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$foto1 = mb_convert_encoding((string)($valor['foto1']), 'UTF-8', 'ISO-8859-1'); 
$foto2 = mb_convert_encoding((string)($valor['foto2']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 
$insertadopor = mb_convert_encoding((string)($valor['insertadopor']), 'UTF-8', 'ISO-8859-1'); 
$editadopor = mb_convert_encoding((string)($valor['editadopor']), 'UTF-8', 'ISO-8859-1'); 
$fecha_insert = mb_convert_encoding((string)($valor['fecha_insert']), 'UTF-8', 'ISO-8859-1'); 
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 
$fecha_edit = mb_convert_encoding((string)($valor['fecha_edit']), 'UTF-8', 'ISO-8859-1'); 
 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmCliente(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_persona."</td>
<td  id='td_datos_13' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_2' style='display:none'>".$rut_cliente."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_10' style='width:10%'>".$zona."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_21' style='width:10%'>".$accesocredito."</td>
<td  id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_15' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_16' style='display:none'>".$salario."</td>
<td  id='td_datos_17' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_18' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_20' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
<td  id='td_datos_104' style='display:none'>".$sms."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina),"3" => number_format($nroRegistro,'0',',','.'),"99" =>$nroRegistro );
echo json_encode($informacion);	
exit;
}



/*Buscar Registro en vista*/
function buscarmasreferencias($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select tipo, idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk , obs from referenciascliente where cod_clienteFk='$buscar' order by tipo asc ";

// echo($sql);
// exit;
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

$obs = mb_convert_encoding((string)($valor['obs']), 'UTF-8', 'ISO-8859-1');
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
$telef = mb_convert_encoding((string)($valor['telef']), 'UTF-8', 'ISO-8859-1');     
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$referencias = mb_convert_encoding((string)($valor['referencias']), 'UTF-8', 'ISO-8859-1');          
$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1'); 
$cod_clienteFk = mb_convert_encoding((string)($valor['cod_clienteFk']), 'UTF-8', 'ISO-8859-1'); 
$idreferenciascliente = mb_convert_encoding((string)($valor['idreferenciascliente']), 'UTF-8', 'ISO-8859-1'); 

 $styleName=CargarStyleTable($styleName);
 $estilo="";
 if($obs ==""){
	 
	 $estilo ="style='background-color:#ff9090'";
 }
	  $pagina.="
<table class='$styleName' $estilo border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosRefSolicitudCredito(this)'  name='tdMasReferenciasSolicitudCredito'>
<td  id='td_datos_1' style='width:10%'>".$observacion."</td>
<td  id='td_datos_2' style='width:10%'>".$telef."</td>
<td id='td_datos_3' style='width:10%'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$referencias."</td>
<td id='td_datos_5' style='width:10%'>".$tipo."</td>
<td id='td_id' style='display:none'>".$idreferenciascliente."</td>
<td id='td_datos_6' style='display:none'>".$obs."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}

function addProductoCredito($totalCargado,$idSolicitudCredito)
{

if($idSolicitudCredito=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
$control=1;	
if($totalCargado>0){
	
$consulta= "delete from detallesolicitud where idSolicitudCreditoFK='$idSolicitudCredito' "; 
$stmt1 = $mysqli->prepare($consulta);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
}

while($control<=$totalCargado){

$cod_Producto=$_POST['cod_Producto'.$control];
$cod_Producto = mb_convert_encoding((string)($cod_Producto), 'ISO-8859-1', 'UTF-8');

$cantidad=$_POST['cantidad'.$control];
$cantidad = mb_convert_encoding((string)($cantidad), 'ISO-8859-1', 'UTF-8');

$precio=$_POST['precio'.$control];
$precio = quitarseparadormiles($precio);

$cuotas=$_POST['cuotas'.$control];
$cuotas = mb_convert_encoding((string)($cuotas), 'ISO-8859-1', 'UTF-8');


$consulta="Insert into detallesolicitud ( cantidad, codProducto, plan,cuotas, idSolicitudCreditoFK)
values($cantidad,'$cod_Producto','$precio','$cuotas',$idSolicitudCredito)";



$stmt1 = $mysqli->prepare($consulta);

if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$control=$control+1;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}



function buscarDetalleProductoSolicitud($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select iddetallesolicitud,cuotas, cantidad, codProducto, plan, idSolicitudCreditoFK ,(select nombre_producto from producto where codProducto=cod_producto) as producto
,(select cod_barra from producto where codProducto=cod_producto) as cod_Barra from detallesolicitud where idSolicitudCreditoFK='$buscar' ";

// echo($sql);
// exit;
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

$cuotas = mb_convert_encoding((string)($valor['cuotas']), 'UTF-8', 'ISO-8859-1');
$producto = mb_convert_encoding((string)($valor['producto']), 'UTF-8', 'ISO-8859-1');
$cantidad = mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1');     
$plan = mb_convert_encoding((string)($valor['plan']), 'UTF-8', 'ISO-8859-1');    

if( $cuotas==""){
	 $cuotas=1;
}

$Cuotero = (quitarseparadormiles($plan)/$cantidad) / $cuotas;

$Respuesta = (quitarseparadormiles($plan)*$cantidad);

	  $pagina.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td  id='td_datos_1' style='width:20%'>".$cantidad."</td>
<td  id='td_datos_2' style='width:80%'>".$producto."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$cuotas." * ".number_format(round($Cuotero),'0',',','.')." = ".number_format($Respuesta,'0',',','.')."Gs.</td>
</tr>
</table>";


}
}

    mysqli_close($mysqli);  
return $pagina;
}


/*Buscar Registro en vista*/
function buscarvista($buscar,$codlocal)
{
$mysqli=conectar_al_servidor();


$condicioncliente="";
if($buscar!=""){
$condicioncliente="and ((Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK ) like '%".$buscar."%' || cl.ci_cliente= '".$buscar."' ) ";
}

$condicionlocal="";
if($codlocal!=""){
$condicionlocal="and cod_localFK= '".$codlocal."' ";
}

$sql= "select idSolicitudCredito, fecha, sc.estado, cod_clienteFK, cod_codeudorFK, cod_cobradorFK,
(Select nombre from zona where idzonaFk=idzona )as zona,cl.accesocredito,
cl.whapp,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,
cl.idzonaFk,cl.lugardetrabajo,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.direcciontrab,cl.fechanac,
(Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK )as cliente,
(Select nombre_persona from persona pra where pra.cod_persona=cod_codeudorFK )as garante
 from solicitudcredito sc
 inner join  cliente cl on cl.cod_cliente=sc.cod_clienteFK 
 inner join   persona pr on cl.cod_cliente=pr.cod_persona 
where sc.estado='APROBADO' ".$condicioncliente.$condicionlocal."  limit 100";
$pagina = "";   

// echo($sql);
// exit;
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
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 
$cod_codeudorFK = mb_convert_encoding((string)($valor['cod_codeudorFK']), 'UTF-8', 'ISO-8859-1');   
$garante = mb_convert_encoding((string)($valor['garante']), 'UTF-8', 'ISO-8859-1');   
$idSolicitudCredito = mb_convert_encoding((string)($valor['idSolicitudCredito']), 'UTF-8', 'ISO-8859-1');  
$cod_clienteFK = mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');   
$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 

$producto=buscarDetalleProductoSolicitud($idSolicitudCredito);

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaSolicitudCreditoVenta(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idSolicitudCredito."</td>
<td  id='td_datos_1' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_2' style='display:none'>".$rut_cliente."</td>
<td  id='td_datos_3' style='width:25%'>".$nombre_persona."</td>
<td  id='td_datos_4' style='display:none'>".$zona."</td>
<td  id='td_datos_5' style='display:none'>".$telefono."</td>
<td  id='td_datos_6' style='display:none'>".$direccion."</td>
<td  id='td_datos_7' style='display:none'>".$email."</td>
<td  id='td_datos_8' style='display:none'>".$whapp."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_12' style='display:none'>".$salario."</td>
<td  id='td_datos_13' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_14' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_15' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_16' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_17' style='display:none'>".$fechanac."</td>
<td  id='td_datos_18' style='width:25%'>".$garante."</td>
<td  id='td_datos_19' style='display:none'>".$cod_codeudorFK."</td>
<td  id='td_datos_20' style='width:30%'>".$producto."</td>
<td  id='td_datos_21' style='display:none'>".$cod_clienteFK."</td>
<td  id='td_datos_22' style='display:none'>".$accesocredito."</td>
</tr>
</table>";


}
}



    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina),"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}





/*Buscar Registro en vista*/
function BuscarImprimirSolicitudCredito($buscar)
{
$mysqli=conectar_al_servidor();



$sql= "select idSolicitudCredito, fecha, sc.estado, cod_clienteFK, cod_codeudorFK, cod_cobradorFK,
(Select nombre from zona where idzonaFk=idzona )as zona,cl.accesocredito,
cl.whapp,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,
cl.idzonaFk,cl.lugardetrabajo,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.direcciontrab,cl.fechanac,
(Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK )as cliente,
(Select nombre_persona from persona pra where pra.cod_persona=cod_codeudorFK )as garante,
(Select ci_cliente from cliente where cod_cliente=cod_codeudorFK )as cigarante,
(Select direccion from persona pra where pra.cod_persona=cod_codeudorFK )as Direcciongarante,
(Select email from persona pra where pra.cod_persona=cod_codeudorFK )as Referenciagarante,
(Select telefono from persona pra where pra.cod_persona=cod_codeudorFK )as NroTelgarante,
(Select lugardetrabajo from cliente where cod_cliente=cod_codeudorFK )as LugarTrabajogarante,
(Select antiguedad from  cliente where cod_cliente=cod_codeudorFK )as Antiguedadgarante,
(Select salario from cliente where cod_cliente=cod_codeudorFK )as Salariogarante
 from solicitudcredito sc
 inner join  cliente cl on cl.cod_cliente=sc.cod_clienteFK 
 inner join   persona pr on cl.cod_cliente=pr.cod_persona 
where  idSolicitudCredito=".$buscar."  limit 100";
$pagina = "";   

// echo($sql);
// exit;
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
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 
$cod_codeudorFK = mb_convert_encoding((string)($valor['cod_codeudorFK']), 'UTF-8', 'ISO-8859-1');   
$garante = mb_convert_encoding((string)($valor['garante']), 'UTF-8', 'ISO-8859-1');   
$idSolicitudCredito = mb_convert_encoding((string)($valor['idSolicitudCredito']), 'UTF-8', 'ISO-8859-1');  
$cod_clienteFK = mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');   
$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 

$cigarante = mb_convert_encoding((string)($valor['cigarante']), 'UTF-8', 'ISO-8859-1'); 
$Direcciongarante = mb_convert_encoding((string)($valor['Direcciongarante']), 'UTF-8', 'ISO-8859-1'); 
$Referenciagarante = mb_convert_encoding((string)($valor['Referenciagarante']), 'UTF-8', 'ISO-8859-1'); 
$NroTelgarante = mb_convert_encoding((string)($valor['NroTelgarante']), 'UTF-8', 'ISO-8859-1'); 
$LugarTrabajogarante = mb_convert_encoding((string)($valor['LugarTrabajogarante']), 'UTF-8', 'ISO-8859-1'); 
$Antiguedadgarante = mb_convert_encoding((string)($valor['Antiguedadgarante']), 'UTF-8', 'ISO-8859-1'); 
$Salariogarante = mb_convert_encoding((string)($valor['Salariogarante']), 'UTF-8', 'ISO-8859-1'); 

$EstadoCivil = ""; 
$Vivienda = ""; 
$Cargo = ""; 

$producto=buscarDetalleProductoSolicitud($idSolicitudCredito);

$DatosReferencia=buscarDetalleReferencia($cod_clienteFK);
$Comercial=$DatosReferencia[0];
$Personal=$DatosReferencia[1];

$edad="";
if($fechanac=="0000-00-00"){
	$fechanac="";
}else{
	$edad=edad($fechanac);
}


}
}

if($Salariogarante!=""){
	$Salariogarante= number_format($Salariogarante,'0',',','.');
}
if($salario!=""){
	$salario= number_format($salario,'0',',','.');
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($producto),"3" => $nombre_persona,"4" => $ci_cliente,"5" => $direccion,"6" => $email,"7" => $fechanac,"8" => $zona,"9" => $telefono,"10" => $whapp,"11" => $edad,"12" => $EstadoCivil,"13" => $Vivienda,"14" => $lugardetrabajo,"15" => $direcciontrab,"16" => $teleftrab1,"17" => $Cargo,"18" => $salario,"19" => $antiguedad,"20" => $garante,"21" => $cigarante,"22" => $Direcciongarante,"23" => $Referenciagarante,"24" => $NroTelgarante,"25" => $LugarTrabajogarante,"26" => $Antiguedadgarante,"27" => $Salariogarante  ,"28" => $Comercial,"29" => $Personal);
echo json_encode($informacion);	
exit;
}


function edad($edad){
    $nacimiento = new DateTime($edad);
    $ahora = new DateTime(date("Y-m-d"));
    $diferencia = $ahora->diff($nacimiento);
    return $diferencia->format("%y");
}


function buscarDetalleReferencia($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk, tipo from referenciascliente where cod_clienteFk='$buscar' ";

// echo($sql);
// exit;
$pagina1 ="<table style='border: none;' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td   style='width:100%'><p class='pTituloW' style='text-align: center;' ><b >REFERENCIA PERSONAL</b> </p> </td>
</tr>
</table>";
$pagina2 = "<table style='border: none;' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td   style='width:100%'><p class='pTituloW' style='text-align: center;' ><b >REFERENCIA COMERCIAL</b> </p> </td>
</tr>
</table>";   

$Datos=null;
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


$telef = mb_convert_encoding((string)($valor['telef']), 'UTF-8', 'ISO-8859-1');
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');     
$referencias = mb_convert_encoding((string)($valor['referencias']), 'UTF-8', 'ISO-8859-1');  
$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1');     
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');          

if($tipo=="PERSONAL"){
	  $pagina1.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td   style='width:40%'><p class='pTituloW' >Nombre: <b >".$referencias."</b> </p> </td>
<td   style='width:20%'><p class='pTituloW' >Telefono: <b >".$telef."</b> </p> </td>
<td   style='width:40%'><p class='pTituloW' >Obs. : <b >".$observacion."</b> </p> </td>
</tr>
</table>";
}
if($tipo=="COMERCIAL"){
	  $pagina2.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td   style='width:40%'><p class='pTituloW' >Nombre: <b >".$referencias."</b> </p> </td>
<td   style='width:20%'><p class='pTituloW' >Telefono: <b >".$telef."</b> </p> </td>
<td   style='width:40%'><p class='pTituloW' >Obs. : <b >".$observacion."</b> </p> </td>
</tr>
</table>";
}
}
}

$Datos[0]=$pagina1;
$Datos[1]=$pagina2;
    mysqli_close($mysqli);  
return $Datos;
}




function buscarProductoSolicitudVista($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select iddetallesolicitud, cantidad, codProducto,cuotas, plan, idSolicitudCreditoFK ,(select nombre_producto from producto where codProducto=cod_producto) as producto
,(select cod_barra from producto where codProducto=cod_producto) as cod_Barra from detallesolicitud where idSolicitudCreditoFK='$buscar' ";

// echo($sql);
// exit;
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$totalVenta=0;
$cuotas =1;
$Cuotero =0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$cuotas = mb_convert_encoding((string)($valor['cuotas']), 'UTF-8', 'ISO-8859-1');
$iddetallesolicitud = mb_convert_encoding((string)($valor['iddetallesolicitud']), 'UTF-8', 'ISO-8859-1');
$cantidad = mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1');     
$codProducto = mb_convert_encoding((string)($valor['codProducto']), 'UTF-8', 'ISO-8859-1');          
$plan = mb_convert_encoding((string)($valor['plan']), 'UTF-8', 'ISO-8859-1');          
$idSolicitudCreditoFK = mb_convert_encoding((string)($valor['idSolicitudCreditoFK']), 'UTF-8', 'ISO-8859-1'); 
$producto = mb_convert_encoding((string)($valor['producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_Barra = mb_convert_encoding((string)($valor['cod_Barra']), 'UTF-8', 'ISO-8859-1'); 

$plan = quitarseparadormiles($plan);

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td  id='td_id_1' style='display:none'>".$codProducto."</td>
<td  id='td_datos_1' style='width:20%'>".$cod_Barra."</td>
<td  id='td_datos_2' style='width:40%'>".$producto."</td>
<td id='td_datos_3' style='width:10%'>".$cantidad."</td>
<td id='td_datos_4' style='width:20%'>".number_format($plan,'0',',','.')."</td>
<td id='td_id_2' style='display:none'>".$iddetallesolicitud."</td>
<td id='td_datos_5' style='width:10%'>".$cuotas."</td>
</tr>
</table>";

$totalVenta= $totalVenta + ($cantidad * quitarseparadormiles($plan)) ;

if( $cuotas==""){
	 $cuotas=1;
}
 $Cuotero = $totalVenta / $cuotas;
}
}

$ResultadoTotal= "<p>".number_format($totalVenta,'0',',','.') ."</p><br> <p style='font-size: 17px; margin-top: -20px;' >".$cuotas." * ".number_format(round($Cuotero),'0',',','.')."</p>";

    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina ,"3" => $ResultadoTotal );
echo json_encode($informacion);	
exit;
}



/*Buscar Registro en vista*/
function buscarmasreferenciasVista($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select tipo, idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk , obs from referenciascliente where cod_clienteFk='$buscar' order by tipo asc ";

// echo($sql);
// exit;
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

$obs = mb_convert_encoding((string)($valor['obs']), 'UTF-8', 'ISO-8859-1');
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
$telef = mb_convert_encoding((string)($valor['telef']), 'UTF-8', 'ISO-8859-1');     
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$referencias = mb_convert_encoding((string)($valor['referencias']), 'UTF-8', 'ISO-8859-1');          
$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1'); 
$cod_clienteFk = mb_convert_encoding((string)($valor['cod_clienteFk']), 'UTF-8', 'ISO-8859-1'); 
$idreferenciascliente = mb_convert_encoding((string)($valor['idreferenciascliente']), 'UTF-8', 'ISO-8859-1'); 

 $styleName=CargarStyleTable($styleName);
 $estilo="";
 if($obs ==""){
	 
	 $estilo ="style='background-color:#ff9090'";
 }
	  $pagina.="
<table class='$styleName' $estilo border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosRefSolicitudCredito(this)'>
<td  id='td_datos_1' style='width:10%'>".$observacion."</td>
<td  id='td_datos_2' style='width:10%'>".$telef."</td>
<td id='td_datos_3' style='width:10%'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$referencias."</td>
<td id='td_datos_5' style='width:10%'>".$tipo."</td>
<td id='td_id' style='display:none'>".$idreferenciascliente."</td>
<td id='td_datos_6' style='display:none'>".$obs."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}




ObtenerDatos($operacion);

?>

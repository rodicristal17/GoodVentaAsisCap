<?php
require("conexion.php");
include("verificar_navegador.php");
include("subir_foto_base64.php");
include("quitarseparadormiles.php");
$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

function ObtenerDatos($operacion)
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



if($operacion=="nuevo" || $operacion=="editar" )
{


$idAbm=$_POST['idAbm'];
$idAbm = utf8_decode($idAbm);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$idAbmCliente=$_POST['idAbmCliente'];
$idAbmCliente = utf8_decode($idAbmCliente);
$cod_garanteFK=$_POST['cod_garanteFK'];
$cod_garanteFK = utf8_decode($cod_garanteFK);
$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = utf8_decode($cod_cobradorFK);

$cod_localFK=$_POST['cod_localFK'];
$cod_localFK = utf8_decode($cod_localFK);
$user=$_POST['useru'];
$user = utf8_decode($user);

$observacion=$_POST['observacion'];
$observacion = utf8_decode($observacion);

abm($idAbm,$estado,$idAbmCliente,$cod_garanteFK,$cod_cobradorFK,$cod_localFK,$user,$observacion,$operacion);

}




if($operacion=="EditarCliente" )
{



$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);
$direccion=$_POST['direccion'];
$direccion = utf8_decode($direccion);
$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);
$email=$_POST['email'];
$email = utf8_decode($email);
$cod_cliente=$cod_persona;
$whapp=$_POST['whapp'];
$whapp = utf8_decode($whapp);
$idzonaFk=$_POST['idzonaFk'];
$idzonaFk = utf8_decode($idzonaFk);
$lugardetrabajo=$_POST['lugardetrabajo'];
$lugardetrabajo = utf8_decode($lugardetrabajo);
$salario=$_POST['salario'];
$salario = quitarseparadormiles($salario);
$antiguedad=$_POST['antiguedad'];
$antiguedad = utf8_decode($antiguedad);
$teleftrab1=$_POST['teleftrab1'];
$teleftrab1 = utf8_decode($teleftrab1);
$teleftrab2=$_POST['teleftrab2'];
$teleftrab2 = utf8_decode($teleftrab2);
$direcciontrab=$_POST['direcciontrab'];
$direcciontrab = utf8_decode($direcciontrab);

abmCliente($idzonaFk,$whapp,$cod_persona,$direccion,$telefono,$email,$cod_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$operacion);

}

 

 
 
 if($operacion=="addmasreferencias"){
 	$totalCargado=$_POST["totalCargado"];
 	$totalCargado=utf8_decode($totalCargado);
	$idcliente=$_POST["idcliente"];
 	$idcliente=utf8_decode($idcliente);
 	addmasreferencias($totalCargado,$idcliente);
 }
 
  if($operacion=="BuscarImprimirSolicitudCredito"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	 BuscarImprimirSolicitudCredito($buscar);
 }



 if($operacion=="buscarmasreferencias"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarmasreferencias($buscar);
 }
 
  if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$codlocal=$_POST["codlocal"];
 	$codlocal=utf8_decode($codlocal);
 	buscarvista($buscar,$codlocal);
 }
 
 
  if($operacion=="addProductoCredito"){
 	$totalCargado=$_POST["totalCargado"];
 	$totalCargado=utf8_decode($totalCargado);
	$idSolicitudCredito=$_POST["idSolicitudCredito"];
 	$idSolicitudCredito=utf8_decode($idSolicitudCredito);
 	addProductoCredito($totalCargado,$idSolicitudCredito);
 }
 
 
  if($operacion=="buscarProductoSolicitud"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarProductoSolicitud($buscar);
 }
 



	 
  if($operacion=="buscarSolicitudCredito"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$documento=$_POST["documento"];
 	$documento=utf8_decode($documento);
	$cliente=$_POST["cliente"];
 	$cliente=utf8_decode($cliente);
	$zona=$_POST["zona"];
 	$zona=utf8_decode($zona);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	
	$cod_cobradorFK=$_POST["cod_cobradorFK"];
 	$cod_cobradorFK=utf8_decode($cod_cobradorFK);

 	BuscarRegistro($fecha1,$fecha2,$documento,$cliente,$zona,$estado,$local,$cod_cobradorFK);
 }
 
 
     if($operacion=="buscarvistaventaSolicitud"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistaventaSolicitud($buscar,$local);
 }
 
 
 

}




function abmCliente($idzonaFk,$whapp,$cod_persona,$direccion,$telefono,$email,$cod_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$operacion)
{



$mysqli=conectar_al_servidor(); 


$consulta1="Update persona set direccion=Upper(?),telefono=Upper(?),email=Upper(?) where cod_persona=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$direccion,$telefono,$email,$cod_persona);


$consulta2="update cliente set whapp=?,idzonaFk=?,lugardetrabajo=?,salario=?,antiguedad=?,teleftrab1=?,teleftrab2=?,direcciontrab=? where cod_cliente=? ";	

$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssssss';
$stmt2->bind_param($ss,$whapp,$idzonaFk,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$cod_persona);


if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


if (!$stmt2->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




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

$consulta1="Insert into solicitudcredito ( fecha, estado, cod_clienteFK, cod_codeudorFK, cod_cobradorFK,cod_localFK,observacion)
values('$fecha_inser','PENDIENTE',$idAbmCliente,$cod_garanteFK,$cod_cobradorFK,$cod_localFK,'$observacion')";
$stmt1 = $mysqli->prepare($consulta1);

// echo($consulta1);
// exit;

}


if($operacion=="editar")
{

$consulta1="Update solicitudcredito set  estado=Upper(?),cod_localFK=Upper(?), cod_clienteFK=Upper(?), cod_codeudorFK=Upper(?), cod_cobradorFK=Upper(?) ,cod_usuarioFK=$cod_usu ,observacion='$observacion' where idSolicitudCredito=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$estado,$cod_localFK,$idAbmCliente,$cod_garanteFK,$cod_cobradorFK,$idAbm);

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
$observacion = utf8_decode($observacion);

$telef=$_POST['telefono'.$control];
$telef = utf8_decode($telef);

$direccion=$_POST['direccion'.$control];
$direccion = utf8_decode($direccion);

$referencias=$_POST['referencia'.$control];
$referencias = utf8_decode($referencias);

$Tipo=$_POST['Tipo'.$control];
$Tipo = utf8_decode($Tipo);

$consulta="Insert into referenciascliente ( telef, direccion, referencias, observacion, cod_clienteFk, tipo)
values(?,?,?,?,?,?)";

$stmt1 = $mysqli->prepare($consulta);
$ss='ssssss';
$stmt1->bind_param($ss,$telef,$direccion,$referencias,$observacion, $cod_cliente,$Tipo);

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
function BuscarRegistro($fecha1,$fecha2,$documento,$cliente,$zona,$estado,$local,$cod_cobradorFK)
{
$mysqli=conectar_al_servidor();

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

$sql= "select observacion,idSolicitudCredito,detalleVenta, fecha, sc.estado, cod_clienteFK, cod_codeudorFK, cod_cobradorFK,
(Select nombre from zona where idzonaFk=idzona )as zona,cod_localFK,
(Select Nombre from local where cod_local=cod_localFK ) as local,
cl.whapp,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,
cl.idzonaFk,cl.lugardetrabajo,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.direcciontrab,cl.fechanac,
(Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK )as cliente,
(Select nombre_persona from persona pra where pra.cod_persona=cod_codeudorFK )as garante
 from solicitudcredito sc
 inner join  cliente cl on cl.cod_cliente=sc.cod_clienteFK 
 inner join   persona pr on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' and cod_cobradorFK=$cod_cobradorFK ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionFecha.$condicionlocal.$condicionestado."  limit 100";
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$observacion = utf8_encode($valor['observacion']); 
$cod_codeudorFK = utf8_encode($valor['cod_codeudorFK']);   
$garante = utf8_encode($valor['garante']);   
$idSolicitudCredito = utf8_encode($valor['idSolicitudCredito']);  
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);   
$fechanac = utf8_encode($valor['fechanac']);     
$nombre_persona = utf8_encode($valor['cliente']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_cliente = utf8_encode($valor['rut_cliente']); 
$whapp = utf8_encode($valor['whapp']); 
$estado = utf8_encode($valor['estado']); 
$idzonaFk = utf8_encode($valor['idzonaFk']); 
$zona = utf8_encode($valor['zona']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$local = utf8_encode($valor['local']); 

$detalleVenta = utf8_encode($valor['detalleVenta']); 



$producto=buscarDetalleProductoSolicitud($idSolicitudCredito);


	  $pagina.="<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
<tr id='tbSelecRegistro' onclick='obtenerdatosSolicitudCredito(this)' >
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idSolicitudCredito."</td>
<td id='td_17' style='display:none' class='td_search'></td>
<td id='' style='width:100%;'>
<table style='width:100%' class='tableRegistroSearchC'>
<tr>
<td id='' style='width:75%;' class='td_search'>".$nombre_persona."</td>
<td  style='width:25%;'class='td_search' >". $ci_cliente."</td>
</tr>
</table>
<table style='width:100%' class='tableRegistroSearchD'>
<tr>
<td id='' style='width:100%;' class='td_search'>".$producto."</td>

</tr>
</table>
<table style='width:100%' class='tableRegistroSearchD'>
<tr>
<td  style='width:33%;'class='td_search' >E:&nbsp".$estado."</td>
<td  style='width:33%;'class='td_search' >Z:&nbsp".$zona."</td>
<td  style='width:33%;' class='td_search'>NRO:&nbsp".$whapp."</td>
</tr>
</table>
</td>

<td  id='td_datos_1' style='display:none'>".$ci_cliente."</td>
<td  id='td_datos_2' style='display:none'>".$rut_cliente."</td>
<td  id='td_datos_3' style='display:none'>".$nombre_persona."</td>
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
<td  id='td_datos_18' style='display:none'>".$garante."</td>
<td  id='td_datos_19' style='display:none'>".$cod_codeudorFK."</td>
<td  id='td_datos_20' style='display:none'>".$producto."</td>
<td  id='td_datos_21' style='display:none'>".$cod_clienteFK."</td>
<td  id='td_datos_22' style='display:none'>".$detalleVenta."</td>
<td  id='td_datos_23' style='display:none'>".$observacion."</td>
<td  id='td_datos_24' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_25' style='display:none'>".$local."</td>
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


$sms = utf8_encode($valor['sms']); 
$cod_persona = utf8_encode($valor['cod_persona']);     
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_cliente = utf8_encode($valor['rut_cliente']); 
$Calificacion = utf8_encode($valor['Calificacion']); 
$whapp = utf8_encode($valor['whapp']); 
$estado = utf8_encode($valor['estado']); 
$idzonaFk = utf8_encode($valor['idzonaFk']); 
$zona = utf8_encode($valor['zona']); 
$foto1 = utf8_encode($valor['foto1']); 
$foto2 = utf8_encode($valor['foto2']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 
$insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$accesocredito = utf8_encode($valor['accesocredito']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
 
	  $pagina.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>
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


function buscarProductoSolicitud($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select iddetallesolicitud, cantidad, codProducto, plan, idSolicitudCreditoFK ,(select nombre_producto from producto where codProducto=cod_producto) as producto
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
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$iddetallesolicitud = utf8_encode($valor['iddetallesolicitud']);
$cantidad = utf8_encode($valor['cantidad']);     
$codProducto = utf8_encode($valor['codProducto']);          
$plan = utf8_encode($valor['plan']);          
$idSolicitudCreditoFK = utf8_encode($valor['idSolicitudCreditoFK']); 
$producto = utf8_encode($valor['producto']); 
$cod_Barra = utf8_encode($valor['cod_Barra']); 


	  $pagina.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosProductoCredito(this)'  name='tdDetalleSolicitudCredito'>
<td  id='td_id_1' style='display:none'>".$codProducto."</td>
<td  id='td_datos_1' style='width:20%'>".$cod_Barra."</td>
<td  id='td_datos_2' style='width:40%'>".$producto."</td>
<td id='td_datos_3' style='width:20%'>".$cantidad."</td>
<td id='td_datos_4' style='width:20%'>".$plan."</td>
<td id='td_id_2' style='display:none'>".$iddetallesolicitud."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}

/*Buscar Registro en vista*/
function buscarmasreferencias($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select tipo, idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk from referenciascliente where cod_clienteFk='$buscar' order by tipo asc ";

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


$tipo = utf8_encode($valor['tipo']);
$telef = utf8_encode($valor['telef']);     
$direccion = utf8_encode($valor['direccion']);          
$referencias = utf8_encode($valor['referencias']);          
$observacion = utf8_encode($valor['observacion']); 
$cod_clienteFk = utf8_encode($valor['cod_clienteFk']); 
$idreferenciascliente = utf8_encode($valor['idreferenciascliente']); 


	  $pagina.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosRefSolicitudCredito(this)'  name='tdMasReferenciasSolicitudCredito'>
<td  id='td_datos_1' style='width:10%'>".$observacion."</td>
<td  id='td_datos_2' style='width:10%'>".$telef."</td>
<td  id='td_datos_4' style='width:10%'>".$referencias."</td>
<td id='td_datos_3' style='width:10%'>".$direccion."</td>
<td id='td_datos_5' style='width:10%'>".$tipo."</td>
<td id='td_id' style='display:none'>".$idreferenciascliente."</td>
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
$cod_Producto = utf8_decode($cod_Producto);

$cantidad=$_POST['cantidad'.$control];
$cantidad = utf8_decode($cantidad);

$precio=$_POST['precio'.$control];
$precio = utf8_decode($precio);

$cuotas=$_POST['cuotas'.$control];
$cuotas = utf8_decode($cuotas);


$consulta="Insert into detallesolicitud ( cantidad, codProducto, plan,cuotas, idSolicitudCreditoFK)
values(?,?,?,?,?)";

$stmt1 = $mysqli->prepare($consulta);
$ss='sssss';
$stmt1->bind_param($ss,$cantidad,$cod_Producto,$precio,$cuotas,$idSolicitudCredito);

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
$sql= "select iddetallesolicitud, cantidad, codProducto, plan, idSolicitudCreditoFK ,(select nombre_producto from producto where codProducto=cod_producto) as producto
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


$producto = utf8_encode($valor['producto']);
$cantidad = utf8_encode($valor['cantidad']);     
$plan = utf8_encode($valor['plan']);          

	  $pagina.="
<table style='border: none;' class='tableRegistroSearchD' border='1' cellspacing='1' cellpadding='5' >
<tr   id='tbSelecRegistro'  >
<td    id='td_datos_1' style='width:20%;border: none;'>".$cantidad."</td>
<td    id='td_datos_2' style='width:80%;border: none;'>".$producto."</td>
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
$condicioncliente="and ((Select nombre_persona from persona pra where pra.cod_persona=cod_clienteFK ) like '%".$buscar."%' || and cl.ci_cliente= '".$buscar."' ) ";
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
$accesocredito = utf8_encode($valor['accesocredito']); 
$cod_codeudorFK = utf8_encode($valor['cod_codeudorFK']);   
$garante = utf8_encode($valor['garante']);   
$idSolicitudCredito = utf8_encode($valor['idSolicitudCredito']);  
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);   
$fechanac = utf8_encode($valor['fechanac']);     
$nombre_persona = utf8_encode($valor['cliente']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_cliente = utf8_encode($valor['rut_cliente']); 
$whapp = utf8_encode($valor['whapp']); 
$estado = utf8_encode($valor['estado']); 
$idzonaFk = utf8_encode($valor['idzonaFk']); 
$zona = utf8_encode($valor['zona']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 

$producto=buscarDetalleProductoSolicitud($idSolicitudCredito);


	  $pagina.="
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5' >
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
$accesocredito = utf8_encode($valor['accesocredito']); 
$cod_codeudorFK = utf8_encode($valor['cod_codeudorFK']);   
$garante = utf8_encode($valor['garante']);   
$idSolicitudCredito = utf8_encode($valor['idSolicitudCredito']);  
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);   
$fechanac = utf8_encode($valor['fechanac']);     
$nombre_persona = utf8_encode($valor['cliente']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_cliente = utf8_encode($valor['rut_cliente']); 
$whapp = utf8_encode($valor['whapp']); 
$estado = utf8_encode($valor['estado']); 
$idzonaFk = utf8_encode($valor['idzonaFk']); 
$zona = utf8_encode($valor['zona']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 


$cigarante = utf8_encode($valor['cigarante']); 
$Direcciongarante = utf8_encode($valor['Direcciongarante']); 
$Referenciagarante = utf8_encode($valor['Referenciagarante']); 
$NroTelgarante = utf8_encode($valor['NroTelgarante']); 
$LugarTrabajogarante = utf8_encode($valor['LugarTrabajogarante']); 
$Antiguedadgarante = utf8_encode($valor['Antiguedadgarante']); 
$Salariogarante = utf8_encode($valor['Salariogarante']); 

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


$telef = utf8_encode($valor['telef']);
$direccion = utf8_encode($valor['direccion']);     
$referencias = utf8_encode($valor['referencias']);  
$observacion = utf8_encode($valor['observacion']);     
$tipo = utf8_encode($valor['tipo']);          

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


function  buscarvistaventaSolicitud($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}


$CondicionBuscador1="";
$CondicionBuscador2="";
$CondicionBuscadorTotal1="";
$CondicionBuscadorTotal2="";
$CondicionBuscadorTotalResyltado="";


if($buscar!=""){
$Buscador = explode ( ' ', $buscar );
$total = count($Buscador);
$contador=0;

while(($contador < $total)){
	if($Buscador[$contador]!=""){
	$CondicionBuscador1=" and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$Buscador[$contador]."%' ";	
	$CondicionBuscadorTotal1.=$CondicionBuscador1;
	
	$CondicionBuscador2="";
	$CondicionBuscadorTotal2.=$CondicionBuscador2;
}
	$contador++;
}
$CondicionBuscadorTotalResyltado=$CondicionBuscadorTotal1.$CondicionBuscadorTotal2;

}else{
	$CondicionBuscadorTotalResyltado=" and concat(pr.nombre_producto,' ',descripcion_producto) like '%%'";	
}


	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  pr.estado='Activo' ".$condicionLocal.$CondicionBuscadorTotalResyltado." limit 50";
	


$pagina = "";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$control=0;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($cod_producto=="13603"){
	$stock_producto = "1";
}
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
$paginapreciosb=buscardetallespreciossolicitud($cod_producto);
if($paginapreciosb==""){
$paginapreciosb="Sin Credito";	
}

	  $pagina.="
	  
	  
	  <table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdeSolicitudCredito(this)' name='trVistaProducto_".$cod_barra."' style='$styleProveedor' >
<td  style='width:80%;'>
<table style='width:100%;' class='tableRegistroSearchE' >
<tr>
<td  style='width:85%;'class='td_search' >".$nombre_producto."</td>
<td  style='width:15%;'class='td_search' >". number_format($stock_producto,'0',',','.')."</td>
</tr>
</table>

<table style='width:100%;' class='tableRegistroSearchF' >
<tr>
<td  style='width:100%;'class='td_search' >Marca: ".$NombreMarca."</td>
</tr>
</table>

<table style='width:100%;' class='tableRegistroSearchF' >
<tr>
<td  style='width:100%;'class='td_search' >Cod.: ".$cod_barra." .</td>
</tr>
</table>

<table style='width:100%;' class='tableRegistroSearchF' >
<tr>
<td  style='width:100%;'class='td_search' >Precio: ".number_format($precio_producto,'0',',','.')." Gs.</td>
</tr>
</table>



</td>

<td id='td_datos_13' style='display:none'>".$cod_barra."</td>
<td  style='display:none; background-color: #efeded;color:red'>".$cod_barra."
<br><input style='outline:none;height: 0px;padding: 0px;' type='button' class='$nroRegistro' value='$control' name='$cod_barra' id='btnfocusProducto' onfocus='recorrerFocusTableProductoVenta(this)' ></td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='display:none'>".$nombre_producto."</td>
<td  id='td_datos_14' style='display:none'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_precio_contado' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_precios_creditos' style='display:none;    line-height: 18px;    font-size: 9px;'>".$paginapreciosb."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='display:none'>".$stock_producto."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
<td  id='td_datos_15' style='display:none'>".$stock_producto."</td>
</tr>
</table>";
	 
$control=$control+1;

}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscardetallesprecios($buscar,$preciocontado,$comisioncontado)
{
$mysqli=conectar_al_servidor();

$sql= "select (select porcentaje from producto p where p.cod_producto=dt.cod_producto) as porcentajeContado , precio,Porcentaje as porcen,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio dt
where cod_producto=? ";
 $pagina="";  
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$buscar);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$Porcentaje = 26;  
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$porcentajeContado = utf8_encode($valor['porcentajeContado']);  
$Porcentaje = utf8_encode($valor['porcen']);  
$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


	  $pagina.="<option id='$Cuota' style='$porcentajeContado' class='$Porcentaje' url='$preciocontado' name='$comision' value='".number_format($precio,'0',',','.')."'>".$descripcion."</option>";



}
}
$pagina.="<option name='$comisioncontado' style='$porcentajeContado' class='$Porcentaje' url='$preciocontado'  value='".number_format($preciocontado,'0',',','.')."'  style='display:none' id='contado' >Contado</option>";  
return $pagina;
}


function  buscardetallespreciossolicitud($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,Porcentaje,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio 
where cod_producto=? ";
 $pagina="";  
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$buscar);
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

$Porcentaje = utf8_encode($valor['Porcentaje']); 
$precio = utf8_encode($valor['precio']);
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


	  $pagina.=" ".$Cuota." *<b>".number_format($precio,'0',',','.')."Gs</b><br>";



}
}

return $pagina;
}



ObtenerDatos($operacion);

?>
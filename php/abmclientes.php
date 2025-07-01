<?php
require("conexion.php");
include("verificar_navegador.php");
include("subir_foto_base64.php");
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


if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar")
{


$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);
$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = utf8_decode($nombre_persona);
$direccion=$_POST['direccion'];
$direccion = utf8_decode($direccion);
$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);
$email=$_POST['email'];
$email = utf8_decode($email);
$cod_cliente=$cod_persona;
$rut_cliente=$_POST['rut_cliente'];
$rut_cliente = utf8_decode($rut_cliente);
$Calificacion=$_POST['Calificacion'];
$Calificacion = utf8_decode($Calificacion);
$idZona=$_POST['idZona'];
$idZona = utf8_decode($idZona);
$lugardetrabajo=$_POST['lugardetrabajo'];
$lugardetrabajo = utf8_decode($lugardetrabajo);
$direcciontrab=$_POST['direcciontrab'];
$direcciontrab = utf8_decode($direcciontrab);
$salario=$_POST['salario'];
$salario = utf8_decode($salario);
$antiguedad=$_POST['antiguedad'];
$antiguedad = utf8_decode($antiguedad);
$teleftrab1=$_POST['teleftrab1'];
$teleftrab1 = utf8_decode($teleftrab1);
$teleftrab2=$_POST['teleftrab2'];
$teleftrab2 = utf8_decode($teleftrab2);
$ci_cliente=$_POST['ci_cliente'];
$ci_cliente = utf8_decode($ci_cliente);
$lat=$_POST['latitudCliente'];
$long=$_POST['longitudCliente'];

abm($lugardetrabajo,$direcciontrab,$salario,$antiguedad,$teleftrab1,$teleftrab2,$lat,$long,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_cliente,$rut_cliente,$ci_cliente,$Calificacion,$idZona,$operacion);

}

 if($operacion=="addImagenes")
{
$idclientefk=$_POST['idclientefk'];
$idclientefk = utf8_decode($idclientefk);
addImagenes($idclientefk);
}

if($operacion=="buscarDocumentos")
{
$idcontrato=$_POST['idcliente'];
$idcontrato = utf8_decode($idcontrato);
buscarDocumentos($idcontrato);
}

if($operacion=="eliminardocumento")
{
$idcontrato=$_POST['idcliente'];
$idcontrato = utf8_decode($idcontrato);
$iddocumento=$_POST['iddocumento'];
$iddocumento = utf8_decode($iddocumento);
$urldocumento=$_POST['urldocumento'];
$urldocumento = utf8_decode($urldocumento);
EliminarDocumento($idcontrato,$iddocumento,$urldocumento);

}
 
 if($operacion=="buscarporvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }
 
 
  
 if($operacion=="BuscarRegistroEnVistaArray"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVistaArray($buscar);
 }

if($operacion=="addmasreferencias"){
	
	$idcliente=$_POST["idcliente"];
 	$idcliente=utf8_decode($idcliente);
	
	
	$observacion=$_POST['observacion'];
	$observacion = utf8_decode($observacion);

	$telef=$_POST['telefono'];
	$telef = utf8_decode($telef);

	$direccion=$_POST['direccion'];
	$direccion = utf8_decode($direccion);

	$referencias=$_POST['referencia'];
	$referencias = utf8_decode($referencias);
	
	
 	addmasreferencias($idcliente,$observacion,$telef,$direccion,$referencias);
 }
 if($operacion=="buscarmasreferencias"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarmasreferencias($buscar);
 }
 
 
  if($operacion=="buscarmasreferenciasVista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarmasreferenciasVista($buscar);
 }
 
  if($operacion=="EliminarReferencia"){
 	$idreferenciascliente=$_POST["idreferenciascliente"];
 	$idreferenciascliente=utf8_decode($idreferenciascliente);
 	EliminarReferencia($idreferenciascliente);
 }
 
   if($operacion=="cargarFotos"){
 	$cod_persona=$_POST["cod_persona"];
 	$cod_persona=utf8_decode($cod_persona);
 	cargarFotos2($cod_persona);
 }



 if($operacion=="buscarvista"){
	 
	

	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroCredito($buscar);
 }



}


function BuscarRegistroCredito($buscar)
{
$mysqli=conectar_al_servidor();
$condicioncliente="";
if($buscar!=""){
	$condicioncliente=" and concat( pr.nombre_persona,' ',cl.ci_cliente ,' ',cl.rut_cliente,' ', pr.telefono)   like '%$buscar%'";
}


$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente
,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,cl.fechanac,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,
(Select nombre from zona where idzonaFk=idzona )as zona
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' ".$condicioncliente." order by pr.nombre_persona limit 500";
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




$fechanac = utf8_encode($valor['fechanac']);  
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
$accesocredito = utf8_encode($valor['accesocredito']); 

$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 

$stylefondo="";
if($accesocredito=="Denegado"){
$stylefondo="background-color:#ff5722;color:#fff";	
}
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tableRegistroSearch' onclick='obtenerdatosvistacliente(this)' style='$stylefondo'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_2' style='display:none'>".$ci_cliente."</td>
<td  id='td_datos_13' style='display:none'>".$rut_cliente."</td>
<td id='td_datos_1' style='display:none'>".$nombre_persona."</td>
<td id='' style='width:50%;border: none;'  class='td_search' >".$nombre_persona." <br> ".$ci_cliente."</td>
<td  id='td_datos_10' style='display:none'>".$zona."</td>
<td  id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_4' style='width:40%;border: none;'  class='td_search' >".$telefono."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_14' style='display:none'>".$accesocredito."</td>
<td  id='td_datos_22' style='display:none'>".$fechanac."</td>


<td  id='td_datos_15' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_16' style='display:none'>".$salario."</td>
<td  id='td_datos_17' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_18' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_20' style='display:none'>".$direcciontrab."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}





function abm($lugardetrabajo,$direcciontrab,$salario,$antiguedad,$teleftrab1,$teleftrab2,$lat,$long,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_cliente,$rut_cliente,$ci_cliente,$Calificacion,$idzonaFk,$operacion)
{

if($nombre_persona==""  || $ci_cliente=="" || $idzonaFk==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor(); 

date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);

if($operacion=="nuevo")
	{
	$consulta= "Select count(*) from cliente where ci_cliente=$ci_cliente  and estado ='Activo' ";
	
	
		$stmt = $mysqli->prepare($consulta);



if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nro_total=$result->fetch_row();
 $valor=$nro_total[0];
if($valor>=1)
{
	$informacion =array("1" => "duplicado");
	echo json_encode($informacion);	
	exit;
}   
	}

if($operacion=="nuevo") 
{


$consulta1="Insert into persona (nombre_persona,direccion,telefono,email)
values(Upper(?),Upper(?),?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email);

$consulta2="Insert into cliente (rut_cliente,Calificacion,cod_cliente,lat,lot,idzonaFk,ci_cliente,accesocredito,lugardetrabajo,direcciontrab,salario,antiguedad,teleftrab1,teleftrab2,fecha_insert,cod_user_insert)
values(?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,'Denegado',?,?,?,?,?,?,'$fecha_inser_edit','$user')";
$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssssssss';
$stmt2->bind_param($ss,$rut_cliente,$Calificacion,$lat,$long,$idzonaFk,$ci_cliente,$lugardetrabajo,$direcciontrab,$salario,$antiguedad,$teleftrab1,$teleftrab2);

}


if($operacion=="editar2")
{

$consulta1="Update persona set nombre_persona=Upper(?),direccion=Upper(?),telefono=?,email=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email,$cod_persona); 


$consulta2="update cliente set rut_cliente=?,Calificacion=?,lat=?,lot=?,ci_cliente=?,lugardetrabajo=?,direcciontrab=?,salario=?,antiguedad=?,teleftrab1=?,teleftrab2=?,fecha_edit='$fecha_inser_edit',fecha_edicion_referencia='$fecha_inser_edit',cod_user_edit='$user' where cod_cliente=? ";
$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssssssss';
$stmt2->bind_param($ss,$rut_cliente,$Calificacion,$lat,$long,$ci_cliente,$lugardetrabajo,$direcciontrab,$salario,$antiguedad,$teleftrab1,$teleftrab2,$cod_persona);


}




if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


if (!$stmt2->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

if($operacion=="nuevo") {
	$cod_persona=obtenerUltimaId();
}
cargarFotos($cod_persona);
 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2"=>$cod_persona);
echo json_encode($informacion);	
exit;

}

function obtenerUltimaId()
{
	$cod_persona ="";
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $sql= "Select cod_cliente from cliente where estado='Activo'  order by cod_cliente desc limit 1";
	
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
		  
		  
		      $cod_persona=$valor['cod_cliente'];
		   	 
			  
	  }
 }
 
  mysqli_close($mysqli); 
 return $cod_persona;
}

function addmasreferencias($idcliente,$observacion,$telefono,$direccion,$referencia)
{

if($idcliente=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

$consulta="Insert into referenciascliente ( telef, direccion, referencias, observacion, cod_clienteFk)
values('$telefono','$direccion','$referencia','$observacion',$idcliente)";

$stmt1 = $mysqli->prepare($consulta);



if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}



function EliminarReferencia($idreferenciascliente)
{

$mysqli=conectar_al_servidor(); 

$consulta= "delete from referenciascliente where idreferenciascliente='$idreferenciascliente' "; 
$stmt1 = $mysqli->prepare($consulta);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}




function buscarmasreferencias($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk from referenciascliente where cod_clienteFk='$buscar' ";
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



$telef = utf8_encode($valor['telef']);     
$direccion = utf8_encode($valor['direccion']);          
$referencias = utf8_encode($valor['referencias']);          
$observacion = utf8_encode($valor['observacion']); 
$cod_clienteFk = utf8_encode($valor['cod_clienteFk']); 
$idreferenciascliente = utf8_encode($valor['idreferenciascliente']); 

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
<tr id='tbSelecRegistro' onclick='obtenerdatosmasreferencias(this)'  name='tdMasReferencias'>
<td  id='td_datos_1' style='display:none'>".$observacion."</td>
<td  id='td_datos_2' style='width:35%'>".$telef."</td>
<td  id='td_datos_4' style='width:65%'>".$referencias."</td>
<td id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_5' style='display:none'>".$idreferenciascliente."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}






function buscarmasreferenciasVista($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk from referenciascliente where cod_clienteFk='$buscar' ";
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



$telef = utf8_encode($valor['telef']);     
$direccion = utf8_encode($valor['direccion']);          
$referencias = utf8_encode($valor['referencias']);          
$observacion = utf8_encode($valor['observacion']); 
$cod_clienteFk = utf8_encode($valor['cod_clienteFk']); 
$idreferenciascliente = utf8_encode($valor['idreferenciascliente']); 

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
<tr id='tbSelecRegistro' onclick='obtenerdatosmasreferenciasVista(this)'  name='tdMasReferencias'>
<td  id='td_datos_1' style='display:none'>".$observacion."</td>
<td  id='td_datos_2' style='width:35%'>".$telef."</td>
<td  id='td_datos_4' style='width:65%'>".$referencias."</td>
<td id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_5' style='display:none'>".$idreferenciascliente."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}



function cargarFotos($cod_persona){
	
$ext1=$_POST['ext1'];
$ext1 = utf8_decode($ext1);

$ext2=$_POST['ext2'];
$ext2 = utf8_decode($ext2);

if($ext1!=""){
	$foto1=substr($_POST['foto1'], strpos($_POST['foto1'], ",") + 1);;
$foto1 = base64_decode($foto1);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto1,$id_foto,$ext1);
$ruta="/GoodVentaElim/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext1;
CargaFoto("foto1",$ruta,$cod_persona);
}
if($ext2!=""){
	$foto2=substr($_POST['foto2'], strpos($_POST['foto2'], ",") + 1);;
$foto2 = base64_decode($foto2);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto2,$id_foto,$ext2);
$ruta="/GoodVentaElim/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext2;
CargaFoto("foto2",$ruta,$cod_persona);
}


}

function CargaFoto($tableName,$Urlfoto,$cod_cliente){
	$mysqli=conectar_al_servidor();
	$consulta="Update cliente set ".$tableName."=? where cod_cliente=? ";	

	$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$Urlfoto,$cod_cliente); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

 mysqli_close($mysqli); 
	
}





function BuscarRegistroEnVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select cl.lat,cl.lot,pr.cod_persona,cl.ci_cliente,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.rut_cliente,cl.Calificacion,cl.idzonaFk,foto1,foto2,cl.lugardetrabajo,cl.direcciontrab,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.fecha_edicion_referencia
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona where estado='Activo' and concat(pr.nombre_persona,' ',cl.ci_cliente) like ? 
order by pr.nombre_persona asc ";
$pagina = "";   
$buscar="%".$buscar."%";
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



$cod_persona = utf8_encode($valor['cod_persona']);  
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_cliente = utf8_encode($valor['rut_cliente']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$Calificacion = utf8_encode($valor['Calificacion']); 
$lat = utf8_encode($valor['lat']); 
$long = utf8_encode($valor['lot']); 
$idzonaFk = utf8_encode($valor['idzonaFk']); 
$foto1 = utf8_encode($valor['foto1']); 
$foto2 = utf8_encode($valor['foto2']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$fecha_edicion_referencia = utf8_encode($valor['fecha_edicion_referencia']); 


$pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatoscliente(this)'>
<td id='td_1' style='display:none; background-color: #efeded;color:red'>".$cod_persona."</td>
<td  id='td_5' style='display:none'>".$ci_cliente."</td>
<td  id='td_15' style='display:none'>".$rut_cliente."</td>
<td id='td_2' style='width:60%' class='td_search'>".$nombre_persona."</td>
<td  id='td_4' style='width:40%' class='td_search'>".$telefono."</td>
<td  id='td_3' style='display:none'>".$direccion."</td>
<td  id='td_6' style='display:none'>".$Calificacion."</td>
<td id='td_7' style='display:none'>".$lat."</td>
<td id='td_8' style='display:none'>".$long."</td>
<td id='td_9' style='display:none'>".$lat.','.$long."</td>
<td  id='td_12' style='display:none'>".$idzonaFk."</td>
<td  id='td_13' style='display:none'>".$foto1."</td>
<td  id='td_14' style='display:none'>".$foto2."</td>
<td  id='td_16' style='display:none'>".$email."</td>
<td  id='td_17' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_18' style='display:none'>".$direcciontrab."</td>
<td  id='td_19' style='display:none'>".$salario."</td>
<td  id='td_20' style='display:none'>".$antiguedad."</td>
<td  id='td_21' style='display:none'>".$teleftrab1."</td>
<td  id='td_22' style='display:none'>".$teleftrab2."</td>
<td  id='td_24' style='display:none'>".$fecha_edicion_referencia."</td>
</tr>";


}
}
 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}





function BuscarRegistroEnVistaArray($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select cl.lat,cl.lot,pr.cod_persona,cl.ci_cliente,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.rut_cliente,cl.Calificacion,cl.idzonaFk,foto1,foto2,cl.lugardetrabajo,cl.direcciontrab,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.fecha_edicion_referencia
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona where cod_cliente = ? 
order by pr.nombre_persona asc ";
$pagina = "";   
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



$cod_persona = utf8_encode($valor['cod_persona']);  
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_cliente = utf8_encode($valor['rut_cliente']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$Calificacion = utf8_encode($valor['Calificacion']); 
$lat = utf8_encode($valor['lat']); 
$long = utf8_encode($valor['lot']); 
$idzonaFk = utf8_encode($valor['idzonaFk']); 
$foto1 = utf8_encode($valor['foto1']); 
$foto2 = utf8_encode($valor['foto2']); 
$ci_cliente = utf8_encode($valor['ci_cliente']); 
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']); 
$direcciontrab = utf8_encode($valor['direcciontrab']); 
$salario = utf8_encode($valor['salario']); 
$antiguedad = utf8_encode($valor['antiguedad']); 
$teleftrab1 = utf8_encode($valor['teleftrab1']); 
$teleftrab2 = utf8_encode($valor['teleftrab2']); 
$fecha_edicion_referencia = utf8_encode($valor['fecha_edicion_referencia']); 


			  $registro = array(
			  "cod_persona"=>$cod_persona,
			  "nombre_persona"=>$nombre_persona,
			  "direccion"=>$direccion,
			  "telefono"=>$telefono,
			  "email"=>$email,
			  "rut_cliente"=>$rut_cliente,
			  "ci_cliente"=>$ci_cliente,
			  "Calificacion"=>$Calificacion,
			  "lat"=>$lat,
			  "long"=>$long,
			  "idzonaFk"=>$idzonaFk,
			  "foto1"=>$foto1,
			  "foto2"=>$foto2,
			  "lugardetrabajo"=>$lugardetrabajo,
			  "direcciontrab"=>$direcciontrab,
			  "salario"=>$salario,
			  "antiguedad"=>$antiguedad,
			  "teleftrab1"=>$teleftrab1,
			  "teleftrab2"=>$teleftrab2,
			  "ubucacion"=>$lat.','.$long,
			  "fecha_edicion_referencia"=>$fecha_edicion_referencia );

	  }
 }else{
	mysqli_close($mysqli);
	$informacion =array("1" => "RN");
	echo json_encode($informacion);	
	exit;
 }
 
 
mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $registro);
echo json_encode($informacion);	
exit;


}




function cargarFotos2($cod_persona){
	
	$mysqli=conectar_al_servidor();
	
$ext1=$_POST['ext1'];
$ext1 = utf8_decode($ext1);

$ext2=$_POST['ext2'];
$ext2 = utf8_decode($ext2);

if($ext1!=""){
	$foto1=substr($_POST['foto1'], strpos($_POST['foto1'], ",") + 1);;
$foto1 = base64_decode($foto1);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto1,$id_foto,$ext1);
				
$ruta="/GoodVentaElim/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext1;

	$consulta="Update cliente set foto1=? where cod_cliente=? ";	

	$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$ruta,$cod_persona); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
}
if($ext2!=""){
	$foto2=substr($_POST['foto2'], strpos($_POST['foto2'], ",") + 1);;
$foto2 = base64_decode($foto2);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto2,$id_foto,$ext2);
$ruta="/GoodVentaElim/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext2;

$consulta="Update cliente set foto2=? where cod_cliente=? ";	

	$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$ruta,$cod_persona); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

}


mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;


}
function addImagenes($idcontratofk){
$control=1;
$totalregistrodoc=$_POST['totalregistro'];
$totalregistrodoc = utf8_decode($totalregistrodoc);

$mysqli=conectar_al_servidor();
while($control<=$totalregistrodoc){

$archivo=$_POST['archivo'.$control];
$archivo = utf8_decode($archivo);

$ext=$_POST['ext'.$control];
$ext = utf8_decode($ext);

$descripcion=$_POST['descripcion'.$control];
$descripcion = utf8_decode($descripcion);

$fecha=$_POST['fecha'.$control];
$fecha = utf8_decode($fecha);

$control++;

insertardocumento($idcontratofk,$ext,$archivo,$descripcion,$fecha);
}

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}
function insertardocumento($cod_detalle,$exte,$archivo,$descripcion,$fecha)
{
	$documento=substr($archivo, strpos($archivo, ",") + 1);;
	$documento = base64_decode($documento);
	
	$id_documento=rand(10,5000);		  
	$donde="../fotos/FotosDocumento/";
	$id_documento=$cod_detalle;
	
	$id_f=subir_imagen_base64($donde,$documento,$id_documento,$exte);
	$ruta="/GoodVentaElim/fotos/FotosDocumento/".$cod_detalle.$id_f.'.'.$exte;
	
	CargaDocumento($ruta,$cod_detalle,$descripcion,$fecha);
}
function CargaDocumento($Urldoc,$idcontratofk,$descripcion,$fecha){
	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO fotos_cliente (url,cod_clienteFK,descripcion,fecha) VALUES ('$Urldoc','$idcontratofk','$descripcion','$fecha') ";
	
$stmt = $mysqli->prepare($consulta);



if ( ! $stmt->execute()) {
   echo "Error";
}
	
}
function buscarDocumentos($codigo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "SELECT *
				FROM fotos_cliente where cod_clienteFK='$codigo'";
  
   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $iddocumento=$valor['idfotos_cliente'];
		  	  $archivourl=utf8_encode($valor['url']);
		  	  $descripcion=utf8_encode($valor['descripcion']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $idcontratoFK=$valor['cod_clienteFK'];
		  	 
		  	 
			  $codigo= substr(str_shuffle($permitted_chars), 0, 5);
			  
			  
		  	  $pagina.="
<table id='$codigo' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistroImagen' onclick='SeleccionarItemImagen(this)' name='tdDetalleItemImagen'>
<td id='td_id_1' style='display:none'>".$codigo."</td>
<td id='td_id_2' style='display:none'>".$iddocumento."</td>
<td id='td_id_3' style='display:none'>".$idcontratoFK."</td>
<td id='td_datos_1' style='display:none'>".$archivourl."</td>
<td id='' style='width:20%'>IMAGEN</td>
<td id='td_datos_2' style='width:60%'>".$descripcion."</td>
<td id='td_datos_3' style='width:20%'>".$fecha."</td>
</tr>
</table>";
			  
			  $codigo="";
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}
function EliminarDocumento($idcontratoFK,$iddocumento,$urldocumento)
{
	$mysqli=conectar_al_servidor();
	$sql= "DELETE FROM fotos_cliente WHERE cod_clienteFK='$idcontratoFK' and idfotos_cliente='$iddocumento'";
 
 
 $file_delete = dirname(__FILE__, 2) . $urldocumento;
 
  
  $control = "Fracaso al borrar";
 
 if (file_exists($file_delete)) {
	 if(unlink($file_delete)){
		 $control = "exito";
	 }
	 }
   

   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	
 
mysqli_close($mysqli);
 $informacion =array("1" => $control);
echo json_encode($informacion);	
exit;


}





ObtenerDatos($operacion);

?>
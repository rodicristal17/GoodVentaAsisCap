<?php
require("conexion.php");
include("verificar_navegador.php");
include("subir_foto_base64.php");
include("quitarseparadormiles.php");
include("classTable.php");
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


$sms=$_POST['sms'];
$sms = utf8_decode($sms);

$FechaNac=$_POST['FechaNac'];
$FechaNac = utf8_decode($FechaNac);

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
$ci_cliente=$_POST['ci_cliente'];
$ci_cliente = utf8_decode($ci_cliente);
$Calificacion=$_POST['Calificacion'];
$Calificacion = utf8_decode($Calificacion);
$whapp=$_POST['whapp'];
$whapp = utf8_decode($whapp);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
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
$accesocredito=$_POST['accesocredito'];
$accesocredito = utf8_decode($accesocredito);

abm($FechaNac,$sms,$accesocredito,$idzonaFk,$whapp,$estado,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_cliente,$rut_cliente,$ci_cliente,$Calificacion,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$operacion);

}

 
 
 if($operacion=="addmasreferencias"){
 	$totalCargado=$_POST["totalCargado"];
 	$totalCargado=utf8_decode($totalCargado);
	$idcliente=$_POST["idcliente"];
 	$idcliente=utf8_decode($idcliente);
 	addmasreferencias($totalCargado,$idcliente);
 } 
 
 
 if($operacion=="buscar_antecedente_consulta"){
	$cod_clienteFK=$_POST["cod_clienteFK"];
 	$cod_clienteFK=utf8_decode($cod_clienteFK);
	$cod_ventaFK=$_POST["cod_ventaFK"];
 	$cod_ventaFK=utf8_decode($cod_ventaFK);
 	buscar_antecedente_consulta($cod_clienteFK,$cod_ventaFK);
 }
 
 
 if($operacion=="buscar_antecedente_resumen_consulta"){
	$cod_clienteFK=$_POST["cod_clienteFK"];
 	$cod_clienteFK=utf8_decode($cod_clienteFK);
	$cod_ventaFK=$_POST["cod_ventaFK"];
 	$cod_ventaFK=utf8_decode($cod_ventaFK);
 	buscar_antecedente_resumen_consulta($cod_clienteFK,$cod_ventaFK);
 }

 if($operacion=="buscar"){
	 

 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$documento=$_POST["documento"];
 	$documento=utf8_decode($documento);
	$cliente=$_POST["cliente"];
 	$cliente=utf8_decode($cliente);
	$zona=$_POST["zona"];
 	$zona=utf8_decode($zona);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$accesocredito=$_POST["accesocredito"];
 	$accesocredito=utf8_decode($accesocredito);
 	BuscarRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito);
 }
 
 if($operacion=="buscarmas"){
	 

 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$documento=$_POST["documento"];
 	$documento=utf8_decode($documento);
	$cliente=$_POST["cliente"];
 	$cliente=utf8_decode($cliente);
	$zona=$_POST["zona"];
 	$zona=utf8_decode($zona);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$accesocredito=$_POST["accesocredito"];
 	$accesocredito=utf8_decode($accesocredito);
	$registrocargado=$_POST["registrocargado"];
 	$registrocargado=utf8_decode($registrocargado);
 	BuscarMasRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito,$registrocargado);
 }

if($operacion=="addImagenes"){
$idclientefk=$_POST['idclientefk'];
$idclientefk = utf8_decode($idclientefk);
addImagenes($idclientefk);
}

if($operacion=="buscarDocumentos"){
$idcontrato=$_POST['idcliente'];
$idcontrato = utf8_decode($idcontrato);
buscarDocumentos($idcontrato);
}

if($operacion=="eliminardocumento"){
$idcontrato=$_POST['idcliente'];
$idcontrato = utf8_decode($idcontrato);
$iddocumento=$_POST['iddocumento'];
$iddocumento = utf8_decode($iddocumento);
$urldocumento=$_POST['urldocumento'];
$urldocumento = utf8_decode($urldocumento);
EliminarDocumento($idcontrato,$iddocumento,$urldocumento);

}

 if($operacion=="buscarmasreferencias"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarmasreferencias($buscar);
 }
 
  if($operacion=="cargar_antecedente_paciente"){
 	$cod_ventaFK=$_POST["cod_ventaFK"];
 	$cod_ventaFK=utf8_decode($cod_ventaFK);
	$cod_clienteFK=$_POST["cod_clienteFK"];
 	$cod_clienteFK=utf8_decode($cod_clienteFK);
	$observacion=$_POST["observacion"];
 	$observacion=utf8_decode($observacion);
 	cargar_antecedente_paciente($cod_ventaFK,$cod_clienteFK,$observacion);
 }


 if($operacion=="buscarvista"){
	 
	
 	$ruc=$_POST["ruc"];
 	$ruc=utf8_decode($ruc);
	$documento=$_POST["documento"];
 	$documento=utf8_decode($documento);
	$cliente=$_POST["cliente"];
 	$cliente=utf8_decode($cliente);
	$telef=$_POST["telef"];
 	$telef=utf8_decode($telef);
 	BuscarRegistroEnVista($ruc,$documento,$cliente,$telef);
 }

 if($operacion=="buscarporci"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarporci($buscar);
 }

 if($operacion=="buscarmensajes"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarmensajes($buscar);
 }

 if($operacion=="buscarcumpleCliente"){
 	$Fecha=$_POST["Fecha"];
 	$Fecha=utf8_decode($Fecha);
	$Zona=$_POST["Zona"];
 	$Zona=utf8_decode($Zona);
 	 buscarcumpleCliente($Fecha,$Zona);
 }
 
 if($operacion=="buscarcuentaImpago"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$local=$_POST["local"];
 	$local=utf8_decode($local);	
	$zona=$_POST["zona"];
 	$zona=utf8_decode($zona);
	$cliente=$_POST["cliente"];
 	$cliente=utf8_decode($cliente);
	$cobrador=$_POST["cobrador"];
 	$cobrador=utf8_decode($cobrador);
	
	$tipo=$_POST["tipo"];
 	$tipo=utf8_decode($tipo);
	
 	buscarcuentaImpago($tipo,$fecha1,$fecha2,$local,$zona,$cliente,$cobrador);
 }
 
if($operacion=="buscarGeolocalizacion"){
$idcontrato=$_POST['idcliente'];
$idcontrato = utf8_decode($idcontrato);
buscarGeolocalizacion($idcontrato);
}

if($operacion=="InsertarGeo"){
$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);
$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$descripcion=$_POST['descripcion'];
$descripcion = utf8_decode($descripcion);

$latitudGeo=$_POST['latitudGeo'];
$latitudGeo = utf8_decode($latitudGeo);
$longitudGeo=$_POST['longitudGeo'];
$longitudGeo = utf8_decode($longitudGeo);

InsertarGeo($cod_persona,$fecha,$descripcion,$latitudGeo,$longitudGeo);

}

if($operacion=="EliminarGeo"){
$CodGeoLocalizacion=$_POST['CodGeoLocalizacion'];
$CodGeoLocalizacion = utf8_decode($CodGeoLocalizacion);

EliminarGeo($CodGeoLocalizacion);

}



if($operacion=="buscarDatalis")
{
	
	buscarDatalis();

}

if($operacion=="buscarDocumentosPrincipal"){
$idcliente=$_POST['idcliente'];
$idcliente = utf8_decode($idcliente);

$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);

buscarDocumentosPrincipal($idcliente,$cod_ventaFK);
}



if($operacion=="buscarDocumentosGaleriaFoto"){
$idcliente=$_POST['idcliente'];
$idcliente = utf8_decode($idcliente);

$descripcion=$_POST['descripcion'];
$descripcion = utf8_decode($descripcion);
buscarDocumentosGaleriaFoto($idcliente,$descripcion);
}


}




function buscarDocumentosGaleriaFoto($idcliente,$descripcion)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 $condicionDescripcion="";
	 if($descripcion!=""){
		 $condicionDescripcion=" and descripcion like '%".$descripcion."%'";
	 }
	 
	$condicionVenta= "";
	if (isset($_POST['codVenta']) && !empty($_POST['codVenta'])) {
		$condicionVenta= " and cod_ventaFK = ".$_POST['codVenta'];
	}
		$sql= "SELECT *
				FROM fotos_cliente where cod_clienteFK='$idcliente' ".$condicionDescripcion.$condicionVenta." order by idfotos_cliente asc ";
  
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
			  
			  
			   $pagina.="<div class='divFloat2' style='width: 24%;margin: 4px;'>
			  <center>
			  <table class='divMenub2'  id='$codigo'  style='  width: 100%;  height: 230px;  border: 1px solid #aba6a6;'>
				<tr id='tbSelecRegistroImagen' onclick='SeleccionarItemImagenGaleriaFoto(this)' >
				<td>
				<div  class='imgFotoCi' style='background-image: url(".$archivourl.")'></div>
				<center>
		<p class='pTituloC' >".$descripcion."</p>
		<p class='pTituloC'>".$fecha."</p>
		 </center>
				</td>
				
				<td id='td_id_1' style='display:none'>".$codigo."</td>
				<td id='td_id_2' style='display:none'>".$iddocumento."</td>
				<td id='td_id_3' style='display:none'>".$idcontratoFK."</td>
				<td id='td_datos_1' style='display:none'>".$archivourl."</td>
				<td id='td_datos_2' style='display:none'>".$descripcion."</td>
				<td id='td_datos_3' style='display:none'>".$fecha."</td>
				</tr>
				</table>
				</center>
				</div>";
			  
			  // $pagina.=$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina;
			  
			  $codigo="";
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}







function buscarDocumentosPrincipal($codigo,$cod_ventaFK)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "SELECT * FROM fotos_cliente where cod_clienteFK='$codigo' and cod_ventaFK='$cod_ventaFK'";
  
   // echo($sql);
   // exit;
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
<tr id='tbSelecRegistroImagen' onclick='SeleccionarItemImagenPrincipal(this)' name='tdBDClienteFoto' >
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



function buscarDatalis()
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,
(Select nombre from zona where idzonaFk=idzona )as zona ,
cl.totaldias,
cl.lugardetrabajo,
cl.salario,
cl.antiguedad,
cl.teleftrab1,
cl.fechanac,
cl.teleftrab2,
cl.direcciontrab
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' order by pr.nombre_persona ";
		
   
   $stmt = $mysqli->prepare($sql);
 if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_persona=$valor['cod_persona'];
		  	  $ci_cliente=utf8_encode($valor['ci_cliente']);
		  	  $nombre_persona=utf8_encode($valor['nombre_persona']);			  
		  	 
			  $pagina.="<option id='$cod_persona' > ".$ci_cliente." - ".$nombre_persona."</option>";		  	
			  
			  
	  }
 }
 
 
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}



function EliminarGeo($CodGeoLocalizacion)
{

if($CodGeoLocalizacion=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor(); 


$consulta1=" delete from ubicaciones where idubicaciones='".$CodGeoLocalizacion."' ";
$stmt1 = $mysqli->prepare($consulta1); 
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}






function InsertarGeo($cod_persona,$fecha,$descripcion,$latitudGeo,$longitudGeo)
{

if($cod_persona==""  || $latitudGeo=="" || $longitudGeo==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor(); 


$consulta1="Insert into ubicaciones (lat,lot,descripcion,cod_clienteFk,fecha)
values(?,?,?,?,now())";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$latitudGeo,$longitudGeo,$descripcion,$cod_persona);



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2"=>$cod_persona);
echo json_encode($informacion);	
exit;

}




function buscarGeolocalizacion($codigo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "SELECT *	FROM ubicaciones where cod_clienteFk='$codigo'";
  
   
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
		  
		  
		      $idubicaciones=$valor['idubicaciones'];
		  	  $lat=utf8_encode($valor['lat']);
		  	  $lot=utf8_encode($valor['lot']);
		  	  $descripcion=utf8_encode($valor['descripcion']);
		  	  $fecha=$valor['fecha'];
		  	 
		  	
			  
			  
		  	  $pagina.="
<table  class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosGeoLocalizcion(this)' >
<td id='td_id_1' style='display:none'>".$idubicaciones."</td>
<td id='td_id_2' style='display:none'>".$lat."</td>
<td id='td_id_3' style='display:none'>".$lot."</td>
<td id='td_datos_1' class='td_search' style='width:60%'>".$descripcion."</td>
<td id='td_datos_2' class='td_search' style='width:40%'>".$fecha."</td>
</tr>
</table>";






			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}





function abm($FechaNac,$sms,$accesocredito,$idzonaFk,$whapp,$estado,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_cliente,$rut_cliente,$ci_cliente,$Calificacion,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$operacion)
{

if($nombre_persona==""  || $idzonaFk=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo")
	{
				$consulta= "Select count(*) from cliente where ci_cliente=? and estado ='Activo' ";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='s';
$stmt->bind_param($ss, $ci_cliente); 


if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nro_total=$result->fetch_row();
 $valor=$nro_total[0];
if($valor>=1)
{
	$informacion =array("1" => "EX");
	echo json_encode($informacion);	
	exit;
}   
	}
	/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);

if($operacion=="nuevo") 
{


$consulta1="Insert into persona (nombre_persona,direccion,telefono,email)
values(Upper(?),Upper(?),Upper(?),Upper(?))";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email);

$consulta2="Insert into cliente (fechanac,rut_cliente,Calificacion,cod_cliente,whapp,estado,idzonaFk,ci_cliente,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,cod_user_insert,fecha_insert,accesocredito,sms)
values(?,?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssssssssssssss';
$stmt2->bind_param($ss,$FechaNac,$rut_cliente,$Calificacion,$whapp,$estado,$idzonaFk,$ci_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$user,$fecha_inser_edit,$accesocredito,$sms);

}


if($operacion=="editar")
{

$consulta1="Update persona set nombre_persona=Upper(?),direccion=Upper(?),telefono=Upper(?),email=Upper(?) where cod_persona=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email,$cod_persona);


$consulta2="update cliente set fechanac=?,rut_cliente=?,Calificacion=?,whapp=?,estado=?,idzonaFk=?,ci_cliente=?,lugardetrabajo=?,salario=?,antiguedad=?,teleftrab1=?,teleftrab2=?,direcciontrab=?,cod_user_edit=?,fecha_edit=?,accesocredito=?,sms=? where cod_cliente=? ";	

$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssssssssssssss';
$stmt2->bind_param($ss,$FechaNac,$rut_cliente,$Calificacion,$whapp,$estado,$idzonaFk,$ci_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$user,$fecha_inser_edit,$accesocredito,$sms,$cod_persona);


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
$stmt1->bind_param($ss,$telef,$direccion,$referencias,$observacion, $cod_cliente, $Tipo);

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

function guardarmensaje($fecha,$hora,$idcliente)
{


$mysqli=conectar_al_servidor(); 

$consulta= "Select count(*) from mensajesenviados where idcliente='$idcliente' ";

$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nro_total=$result->fetch_row();
 $valor=$nro_total[0];
if($valor==0){
	$consulta1="Insert into mensajesenviados (fecha,hora,idcliente)
values(?,?,?)";
	
}else{
	
	$consulta1="update mensajesenviados set fecha=?,hora=? where idcliente=?";
}

$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$fecha,$hora,$idcliente);



if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

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

function cargarFotos($cod_persona){
	
$ext1=$_POST['ext1'];
$ext1 = utf8_decode($ext1);

$ext2=$_POST['ext2'];
$ext2 = utf8_decode($ext2);

$ext2=$_POST['ext2'];
$ext2 = utf8_decode($ext2);

if($ext1!=""){
	$foto1=substr($_POST['foto1'], strpos($_POST['foto1'], ",") + 1);;
$foto1 = base64_decode($foto1);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto1,$id_foto,$ext1);
$ruta="/GoodVentaAsisCap/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext1;
CargaFoto("foto1",$ruta,$cod_persona);
}
if($ext2!=""){
	$foto2=substr($_POST['foto2'], strpos($_POST['foto2'], ",") + 1);;
$foto2 = base64_decode($foto2);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto2,$id_foto,$ext2);
$ruta="/GoodVentaAsisCap/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext2;
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



/*Buscar Registro en vista*/
function BuscarRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito)
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


$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,
cl.estado,cl.idzonaFk,foto1,foto2,cl.lugardetrabajo,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.direcciontrab,cl.accesocredito,cl.fechanac,
(Select nombre from zona where idzonaFk=idzona )as zona,cl.fecha_insert,cl.fecha_edit,cl.sms,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado=? ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionCodigo.$condicionaccesocredito." order by pr.nombre_persona limit 100";
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
$nroRegistro=$valor;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$fechanac = utf8_encode($valor['fechanac']); 
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
$fecha_edit = utf8_encode($valor['fecha_edit']); 
$accesocredito = utf8_encode($valor['accesocredito']); 
 $styleName=CargarStyleTable($styleName);
 
$StyleFoto="";
 if($foto1=="" || $foto2=="" ){
	 $StyleFoto=" style='background-color: #ff6b6b;color:white;' ";	 
 }
 
 
 
 
	  $pagina.="
<table class='$styleName' $StyleFoto border='1' cellspacing='1' cellpadding='5' >
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
<td  id='td_datos_105' style='display:none'>".$fechanac."</td>
</tr>
</table>";


}
}

$sql= "select cl.whapp
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado=? ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionCodigo.$condicionaccesocredito." order by pr.nombre_persona";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($s,$estado);
if ( ! $stmt->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;

    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina),"3" => number_format($nroRegistro,'0',',','.'),"99" =>$nroRegistro,"100" =>$totalregistro );
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


$StyleFoto="";
 if($foto1=="" || $foto2=="" ){
	 $StyleFoto=" style='background-color: #ff6b6b;color:white;' ";	 
 }
 





 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' $StyleFoto border='1' cellspacing='1' cellpadding='5'>
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
$sql= "select idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk , tipo from referenciascliente where cod_clienteFk='$buscar' ";
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

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosmasreferencias(this)'  name='tdMasReferencias'>
<td  id='td_datos_1' style='width:10%'>".$observacion."</td>
<td  id='td_datos_2' style='width:10%'>".$telef."</td>
<td  id='td_datos_4' style='width:10%'>".$referencias."</td>
<td id='td_datos_3' style='width:10%'>".$direccion."</td>
<td id='td_datos_5' style='width:10%'>".$tipo."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}



function BuscarRegistroEnVista($ruc,$documento,$cliente,$telef)
{
$mysqli=conectar_al_servidor();
$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and pr.nombre_persona like '%$cliente%'";
}
$condiciondocumento="";
if($documento!=""){
	$condiciondocumento=" and cl.ci_cliente like '%$documento%'";
}
$condicionruc="";
if($ruc!=""){
	$condicionruc=" and cl.rut_cliente like '%$ruc%'";
}
$condiciontelef="";
if($telef!=""){
	$condiciontelef=" and pr.telefono like '%$telef%'";
}

$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente
,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,cl.fechanac,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,
(Select nombre from zona where idzonaFk=idzona )as zona
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' ".$condicioncliente.$condiciondocumento.$condicionruc.$condiciontelef." order by pr.nombre_persona limit 500";
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
 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistacliente(this)' style='$stylefondo'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_2' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_13' style='width:10%'>".$rut_cliente."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_10' style='display:none'>".$zona."</td>
<td  id='td_datos_3' style='width:10%'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
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

function  buscarporci($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,
(Select nombre from zona where idzonaFk=idzona )as zona ,
cl.totaldias,
cl.lugardetrabajo,
cl.salario,
cl.antiguedad,
cl.teleftrab1,
cl.fechanac,
cl.teleftrab2,
cl.direcciontrab
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' and cl.ci_cliente='$buscar' order by pr.nombre_persona limit 1";
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


$fechanac = utf8_encode($valor['fechanac']);  
$totaldias = utf8_encode($valor['totaldias']);  
$lugardetrabajo = utf8_encode($valor['lugardetrabajo']);  
$salario = utf8_encode($valor['salario']);  
$antiguedad = utf8_encode($valor['antiguedad']);  
$teleftrab1 = utf8_encode($valor['teleftrab1']);  
$teleftrab2 = utf8_encode($valor['teleftrab2']);  
$direcciontrab = utf8_encode($valor['direcciontrab']);  
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
 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr class='tableRegistroSelec' id='trdatoClienteCi' onclick='obtenerdatosvistacliente(this)'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_2' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_13' style='width:10%'>".$rut_cliente."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_10' style='display:none'>".$zona."</td>
<td  id='td_datos_3' style='width:10%'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_14' style='display:none'>".$accesocredito."</td>
<td  id='td_datos_15' style='display:none'>".$totaldias."</td>
<td  id='td_datos_16' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_17' style='display:none'>".$salario."</td>
<td  id='td_datos_18' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_20' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_21' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_22' style='display:none'>".$fechanac."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarmensajes($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select fecha,hora from mensajesenviados where idcliente='$buscar' limit 100 ";
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



$fecha = utf8_encode($valor['fecha']);     
$hora = utf8_encode($valor['hora']); 

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td  style='width:80%'>".$fecha."</td>
<td   style='width:20%'>".$hora."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarcuentaImpago($tipo,$fecha1,$fecha2,$local,$zona,$cliente,$cobrador)
{
$mysqli=conectar_al_servidor();


$condicionfecha="";
if($fecha1!="" || $fecha2!=""){
	if($tipo=="compromiso" ){
	$condicionfecha=" and fechaCompro between '$fecha1' and '$fecha2' ";
}else{
	$condicionfecha=" and fecha between '$fecha1' and '$fecha2 23:59:00' ";
}
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (select nombre_persona from persona where cod_persona = cod_clienteFK) like '%$cliente%'";
}
$condicioncobrador="";
if($cobrador!=""){
	$condicioncobrador=" and (select nombre_persona from persona where cod_persona = cod_cobradorFK) like '%$cobrador%'";
}
$condicionzona="";
if($zona!=""){
	$condicionzona=" and (select idzonaFk from cliente where cod_cliente = cod_clienteFK) = '$zona'";
}





$sql= "select fechaCompro, cod_VisitasCliente, fecha, Motivo, cod_clienteFK, cod_cobradorFK ,(select nombre_persona from persona where cod_persona = cod_cobradorFK) as cobrador , (select nombre_persona from persona where cod_persona = cod_clienteFK) as cliente , (select nombre from zona where idzona=(select idzonaFk from cliente where cod_cliente = cod_clienteFK)) as zona  from visitascliente  where cod_VisitasCliente!=''
".$condicioncliente.$condicioncobrador.$condicionzona.$condicionfecha." order by fechaCompro asc ";

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



$Motivo = utf8_encode($valor['Motivo']);     
$fecha = utf8_encode($valor['fecha']); 
$cliente = utf8_encode($valor['cliente']);     
$zona = utf8_encode($valor['zona']); 
$cobrador = utf8_encode($valor['cobrador']);  
$fechaCompro = utf8_encode($valor['fechaCompro']);    


 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td  style='width:15%'>".$fecha."</td>
<td   style='width:25%'>".$cliente."</td>
<td  style='width:30%'>".$Motivo."</td>
<td   style='width:15%'>".$cobrador."</td>
<td   style='width:15%'>".$fechaCompro."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}




function buscarcumpleCliente($Fecha,$Zona)
{
	
$mysqli=conectar_al_servidor();

$condicionFecha="";
if($Fecha!=""){
	$Fecha2=substr($Fecha, 5, 2);
	$condicionFecha=" and  DATE_FORMAT(fechanac, '%m') = $Fecha2 ";
}
$condicionZona="";
if($Zona!=""){
	$condicionZona=" and idzonaFk = '$Zona'";
}

$sql= "SELECT DATE_FORMAT(fechanac, '%m-%d') as FechaNac,DATE_FORMAT(fechanac, '%m') as mesNacimiento, accesocredito,
				(select nombre_persona from persona where cod_cliente=cod_persona) as Nombrecliente ,
				(Select telefono from persona where cod_persona=cod_cliente) as Telefono,
			(select concat(puntoexpedicion,'-',num_factura) from venta where cod_cliente=cod_clienteFK order by fecha_venta desc limit 1) as Venta1 ,
			(select total_venta from venta where cod_cliente=cod_clienteFK order by fecha_venta desc limit 1) as Venta2 ,sms,
				(select nombre from zona where idzona=idzonaFk) as Zona ,fechanac,CONCAT(  case
                  when MONTH(fechanac) < MONTH(CURDATE()) then YEAR(CURDATE()) + 1
                  when MONTH(fechanac) > MONTH(CURDATE()) then YEAR(CURDATE())
                  when DAY(fechanac) <= DAY(CURDATE()) then YEAR(CURDATE()) + 1
                  else YEAR(CURDATE())
                end
              , '-', MONTH(fechanac)
              , '-', DATE_FORMAT(fechanac, '%d')
             ) as cumple 
  FROM cliente WHERE fechanac != '0000-00-00' ".$condicionFecha.$condicionZona." order by cumple asc";
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
 
$MensajeFelicita="";
$MensajePromo="";
 
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$FechaNac = utf8_encode($valor['FechaNac']);
$accesocredito = utf8_encode($valor['accesocredito']);
$mesNacimiento = utf8_encode($valor['mesNacimiento']); 
$Nombrecliente = utf8_encode($valor['Nombrecliente']);     
$cumple = utf8_encode($valor['cumple']);          
$fechanac = utf8_encode($valor['fechanac']);          
$Zona = utf8_encode($valor['Zona']); 
$Venta1 = utf8_encode($valor['Venta1']); 
$Venta2 = utf8_encode($valor['Venta2']); 
$Telefono = utf8_encode($valor['Telefono']); 
$sms = utf8_encode($valor['sms']); 


		if($Telefono!=""){
			$condicion=$Telefono[0];
		}else{
			$condicion="";
		}
		
$codigo="595";
if($condicion=="+"){
	$codigo="";
}

if($Telefono!="0" && $Telefono!=""){
	
	$Telefono = substr($Telefono, 1);
	
$searchString = " ";
$replaceString = "";
 
$Telefono = str_replace($searchString, $replaceString, $Telefono); 
	
if($sms=="SI"){
	
	if($Fecha==""){
		$fechacumple=date('m-d');
	}else{
		$fechacumple=substr($Fecha, 5, 5);
	}
	if($FechaNac==$fechacumple && $accesocredito=="Confirmado"){
	$Mensaje1="";
	 $MensajeFelicita.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td  style='width:30%'>".$codigo.$Telefono."</td>
<td   style='width:30%'></td>
<td   style='width:40%'>".$Mensaje1."</td>
</tr>
</table>";
	}
	
	if($Fecha==""){
		$Mescumple=date('m');
	}else{
		$Mescumple=substr($Fecha, 5, 2);
	}
	
	if($accesocredito=="Confirmado" && $mesNacimiento==$Mescumple ){
	$Mensaje2="";
	$MensajePromo.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td  style='width:30%'>".$codigo.$Telefono."</td>
<td   style='width:30%'></td>
<td   style='width:40%'>".$Mensaje2."</td>
</tr>
</table>";
}
	}
}
	 $styleName=CargarStyleTable($styleName);
	 
	 if($Venta2!=""){
		 $Venta2=number_format($Venta2,'0',',','.');
	 }
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td  id='td_datos_1' style='width:20%'>".$fechanac."</td>
<td  id='td_datos_2' style='width:30%'>".$Nombrecliente."</td>
<td  id='td_datos_3' style='width:20%'>".$cumple."</td>
<td id='td_datos_4' style='width:15%'>".$Zona."</td>
<td id='td_datos_5' style='width:15%'>".$Venta1."/".$Venta2."</td>
</tr>
</table>";
	
	
}


}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) ,"3" => ($nroRegistro),"4" => ($MensajeFelicita) ,"5" => ($MensajePromo) );
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
	$ruta="/GoodVentaAsisCap/fotos/FotosDocumento/".$cod_detalle.$id_f.'.'.$exte;
	
	CargaDocumento($ruta,$cod_detalle,$descripcion,$fecha);
}
function CargaDocumento($Urldoc,$idcontratofk,$descripcion,$fecha){
	$params= "url,cod_clienteFK,descripcion,fecha";
	$valores= "'$Urldoc','$idcontratofk','$descripcion','$fecha'";

	if (isset($_POST['codVenta']) && !empty($_POST['codVenta'])) {
		$params .= ',cod_ventaFK';
		$valores .= ", ".$_POST['codVenta'];
	}

	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO fotos_cliente ($params) VALUES ($valores)";
	
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

function  buscar_antecedente_consulta($cod_clienteFK,$cod_ventaFK)
{
$mysqli=conectar_al_servidor();

$sql="SELECT observacion , estado ,(select nombre_persona from persona where cod_persona=cod_usuario) as usuario,  fecha FROM antecedente_paciente WHERE cod_clienteFK = '$cod_clienteFK' and cod_ventaFK = '$cod_ventaFK'";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$observacion = utf8_encode($valor['observacion']);   
$estado = utf8_encode($valor['estado']);   
$usuario = utf8_encode($valor['usuario']);   
$fecha = utf8_encode($valor['fecha']);   
 

	  $pagina.="
<style>
.timeline {
  position: relative;
  margin: 2px 0;
  padding-left: 5px;
  border-left: 3px solid #4a90e2;
}
.timeline-item {
  position: relative;
  margin-bottom: 2px;
}
.timeline-item::before {
  content: '';
  position: absolute;
  left: -8px;
  top: 4px;
  width: 14px;
  height: 14px;
  background-color: #4a90e2;
  border-radius: 50%;
}
.timeline-content {
  background-color: #f9f9f9;
  padding: 5px 7px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
.timeline-content .description {
  font-weight: bold;
  margin-bottom: 2px;
}
.timeline-content .meta {
  font-size: 12px;
  color: #666;
  border-top: 1px solid #ddd;
  margin-top: 2px;
  padding-top: 2px;
}
</style>

<div class='timeline'>
  <div class='timeline-item'>
    <div class='timeline-content'>
      <div class='description'>
         ".htmlspecialchars($observacion)."
      </div>
	  <div class='meta'>
       ".htmlspecialchars($usuario)." - ".htmlspecialchars($fecha)."
      </div>
    </div>
  </div>
 
</div>

"; 
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}

function cargar_antecedente_paciente($cod_ventaFK,$cod_clienteFK,$observacion){
	
	$user=$_POST['useru'];
    $user = utf8_decode($user);

	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO antecedente_paciente (cod_ventaFK,cod_clienteFK,observacion,cod_usuario,estado,fecha) values ('$cod_ventaFK','$cod_clienteFK','$observacion','$user','Activo',now())";
	
$stmt = $mysqli->prepare($consulta);
 
if ( ! $stmt->execute()) {
   echo "Error";
}
 
	mysqli_close($mysqli);
 $informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function  buscar_antecedente_resumen_consulta($cod_clienteFK,$cod_ventaFK)
{
$mysqli=conectar_al_servidor();

$sql="SELECT observacion , estado ,(select nombre_persona from persona where cod_persona=cod_usuario) as usuario,  fecha FROM antecedente_paciente WHERE cod_clienteFK = '$cod_clienteFK' and cod_ventaFK = '$cod_ventaFK' ";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$observacion = utf8_encode($valor['observacion']);   
$estado = utf8_encode($valor['estado']);   
$usuario = utf8_encode($valor['usuario']);   
$fecha = utf8_encode($valor['fecha']);   
 
 

	  $pagina.="<p class='tarjeta-consulta consulta-item' style='border-left: 5px solid #ff5722;'> <b> 
         ".htmlspecialchars($observacion)." </b>  <br>".htmlspecialchars($usuario)." - ".htmlspecialchars($fecha)." </p>

"; 
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}


ObtenerDatos($operacion);

?>
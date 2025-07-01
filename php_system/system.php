<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok" && $operacion!="buscaroption"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}



//CONTROL DE ACCESO




if($operacion=="asistenciadeactualizacion")
{
	$codigopc=$_POST['codigopc'];
$codigopc = utf8_decode($codigopc);
$codigodeactualizacion=$_POST['codigodeactualizacion'];
$codigodeactualizacion = utf8_decode($codigodeactualizacion);
	asistenciadeactualizacion($codigopc,$codigodeactualizacion);

}	

if($operacion=="registrardispositivo")
{
	$codigopc=$_POST['codigopc'];
$codigopc = utf8_decode($codigopc);
	registrardispositivo($codigopc);

}	

if($operacion=="registraractualizacion")
{
	$codigopc=$_POST['codigopc'];
$codigopc = utf8_decode($codigopc);
	registraractualizacion($codigopc);

}	




}

function registrardispositivo($codigopc)
{
	
	

$mysqli=conectar_al_servidor();



$consulta1="Insert into dispositivos (codigo)
values(?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$codigopc);





if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function registraractualizacion($codigopc)
{
	
	

$mysqli=conectar_al_servidor();


$codigodeActualizacion=buscarultimocodactualizacion();

$consulta1="Update dispositivos set codigo_so=? where codigo=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$codigodeActualizacion,$codigopc);





if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}


function asistenciadeactualizacion($codigopc,$codigodeactualizacion)
{
	$mysqli=conectar_al_servidor();

//$codigoactualizacion=buscarultimocodactualizacion();

	
		$sql= "Select codigo from historialactualizacion where estado='Activo' and codigo='$codigodeactualizacion' limit 1";
		
   
   
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
		  
		  
		      $codigo=$valor['codigo'];
		  	
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 
if($nroRegistro==0){
$tituloasistencia="no";
}else{
	$tituloasistencia="si";
}

 $informacion =array("1" => "exito","2" => $tituloasistencia);
echo json_encode($informacion);	
exit;


}

function buscarultimocodactualizacion()
{
	$mysqli=conectar_al_servidor();
      $codigo="";
		$sql= "Select codigo from historialactualizacion where estado='Activo' order by idhistorialactualizacion desc limit 1 ";
   
   
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
		  
		  
		      $codigo=$valor['codigo'];
		  	  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 return $codigo;


}



verificar($operacion);
?>
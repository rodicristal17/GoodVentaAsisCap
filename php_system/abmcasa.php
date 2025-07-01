<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include("classTable.php");

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);	
if($user!=""){

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
}


//CONTROL DE ACCESO



	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$nombre=$_POST['nombre'];
$nombre = utf8_decode($nombre);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
	$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
	abm($nombre,$estado,$cod_local,$operacion);

}

if($operacion=="buscar")
{
	$codigo=$_POST['codigo'];
$codigo = utf8_decode($codigo);
$nombre=$_POST['nombre'];
$nombre = utf8_decode($nombre);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
	buscar($codigo,$nombre,$estado);

}	


if($operacion=="buscaroption")
{

	buscaroption($user);

}	
if($operacion=="buscaroptionlogin")
{

	buscaroptionlogin();

}	

}

function abm($nombre,$estado,$cod_local,$operacion)
{
	
	
if($nombre==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{


$consulta1="Insert into local (Nombre,estado)
values(?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$nombre,$estado);


}


if($operacion=="editar")
{

$consulta1="Update local set Nombre=?,estado=? where cod_local=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$nombre,$estado,$cod_local); 

}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function buscar($codigo,$nombre,$estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 	$condicioncodigo="";
if($codigo!=""){
	$condicioncodigo=" and cod_local ='".$codigo."'";
}
$condicionnombre="";
if($nombre!=""){
	$condicionnombre=" and Nombre  like '%".$nombre."%'";
}
		$sql= "Select * from local where estado=? ".$condicioncodigo.$condicionnombre;
		
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$stmt->bind_param($s,$estado);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_local=$valor['cod_local'];
		  	  $nombre=utf8_encode($valor['Nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmCasa(this)'>
<td id='td_id' style='width:15%; background-color: #efeded;color:red'>".$cod_local."</td>
<td  id='td_datos_1' style='width:85%'>".$nombre."</td>
<td  id='td_datos_2' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroption($user)
{
	
	$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$codlocal=buscarlocaluser($user);
		$sql= "Select * from local where estado='Activo' and cod_local='$codlocal' ";
	}else{
		$sql= "Select * from local where estado='Activo' ";
	}
	
	
	$mysqli=conectar_al_servidor();
	
		
		 $pagina="";  

   
   
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
		  
		  
		      $cod_local=$valor['cod_local'];
		  	  $nombre=utf8_encode($valor['Nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	
			  $pagina.="<option  value='$cod_local' >".$nombre."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroptionlogin()
{
	
	$sql= "Select * from local where estado='Activo' ";
	$mysqli=conectar_al_servidor();
	
		
		 $pagina="";  

   
   
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
		  
		  
		      $cod_local=$valor['cod_local'];
		  	  $nombre=utf8_encode($valor['Nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	
			  $pagina.="<option  value='$cod_local' >".$nombre."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




verificar($operacion);
?>
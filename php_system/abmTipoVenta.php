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
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok" && $operacion!="buscaroptionlogin"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}


	
if($operacion=="nuevo" || $operacion=="editar")
{
	
$cod_tipoPago=$_POST['cod_tipoPago'];
$cod_tipoPago = utf8_decode($cod_tipoPago);
$nombre=$_POST['nombre'];
$nombre = utf8_decode($nombre);
$datos=$_POST['datos'];
$datos = utf8_decode($datos);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);

	abm($cod_tipoPago,$nombre,$datos,$estado,$operacion);

}

if($operacion=="buscar")
{

$nombre=$_POST['nombre'];
$nombre = utf8_decode($nombre);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
	buscar($nombre,$estado);

}	


if($operacion=="buscaroption")
{
	buscaroption();

}	

}

function abm($cod_tipoPago,$nombre,$datos,$estado,$operacion)
{
	
	
if($nombre==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

	
if($operacion=="nuevo")
{


$consulta1="Insert into tipopago (nombre,datos,estado)
values(upper('$nombre'),'$datos','$estado')";
$stmt1 = $mysqli->prepare($consulta1);

// echo($consulta1);
// exit;

}


if($operacion=="editar")
{

$consulta1="Update tipopago set nombre=?,datos=?,estado=? where cod_tipoPago=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nombre,$datos,$estado,$cod_tipoPago); 

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

function buscar($nombre,$estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicioncodigo="";

	 $condicionNombre="";
	  if($nombre!=""){
		 $condicionNombre=" and nombre like '%".$nombre."%'"; 
	 }
	 
		$sql= "Select cod_tipoPago, nombre, datos, estado
		from tipopago where estado=? ".$condicionNombre;   
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
		  
		  
		      $cod_tipoPago=$valor['cod_tipoPago'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $datos=utf8_encode($valor['datos']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			  $styleName=CargarStyleTable($styleName);  	 
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmTipoPago(this)'>
<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$cod_tipoPago."</td>
<td  id='td_datos_1' style='width:70%'>".$nombre."</td>
<td  id='td_datos_2' style='width:20%'>".$datos."</td>
<td  id='td_datos_3' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroption()
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from `tipopago` where estado='Activo' ";
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
		  
		  
		      $codPago=$valor['cod_tipoPago'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $datos=utf8_encode($valor['datos']);
				  	 
		  	 
			    	
			  $pagina.="<option id='$datos' value='$codPago' >".$nombre."</option>";    
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}




verificar($operacion);
?>
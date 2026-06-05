<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

include("buscar_nivel.php");
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("classTable.php");

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');	
if($user!=""){

	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
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
$nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	abm($nombre,$estado,$cod_local,$operacion);

}

if($operacion=="buscar")
{
	$codigo=$_POST['codigo'];
$codigo = mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
$nombre=$_POST['nombre'];
$nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	buscar($codigo,$nombre,$estado);

}	

if ($operacion=="relacionarProductosLocal"){
	$cod_local=$_POST['cod_local'];
	$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	relacionar_productos_local($cod_local);
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
if (solicitudEliminadoEsEstadoInactivo($estado)) {
	$user = solicitudEliminadoValorPost('useru', '0');
	$respuesta = registrarSolicitudEliminacionGenerica(
		'local',
		'cod_local',
		$cod_local,
		'Solicitud de eliminacion de local.',
		$user,
		'Local: '.$nombre
	);
	echo json_encode($respuesta);
	exit;
}

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
		  	  $nombre=mb_convert_encoding((string)($valor['Nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
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
		  	  $nombre=mb_convert_encoding((string)($valor['Nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	
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
		  	  $nombre=mb_convert_encoding((string)($valor['Nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	
			  $pagina.="<option  value='$cod_local' >".$nombre."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function relacionar_productos_local($cod_local)
{
	$mysqli = conectar_al_servidor();

	$sql = "SELECT cod_producto FROM producto";

	$stmt = $mysqli->prepare($sql);
	if (! $stmt->execute()) {
		echo "Error";
		exit;
	}
	$result = $stmt->get_result();
	$valor = mysqli_num_rows($result);

	mysqli_close($mysqli);
	if ($valor > 0) {
		while ($valor = mysqli_fetch_assoc($result)) {
			$cod_producto = $valor['cod_producto'];
			comprobar_relacion($cod_producto, $cod_local);
		}
	}

	$informacion = array("1" => "exito");
	echo json_encode($informacion);
	exit;
}

function comprobar_relacion($cod_productoFK,$cod_local)
{
	$mysqli=conectar_al_servidor();
	
	
	$sql= "Select * from stocklocales WHERE cod_productofk = '$cod_productoFK' and cod_localfk = '$cod_local' ";
		
   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
mysqli_close($mysqli);

 if ($valor<=0)
 {
	  insert_stock_local($cod_productoFK,$cod_local);
 }
 
 return true;

}

function insert_stock_local($cod_productoFK,$cod_local)
{
if($cod_productoFK=="" || $cod_local == ''  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();



$consulta1="INSERT INTO stocklocales (cantidad,cod_productofk,cod_localfk) VALUES ('0','$cod_productoFK','$cod_local')";
$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

mysqli_close($mysqli);
return true;
}

verificar($operacion);
?>

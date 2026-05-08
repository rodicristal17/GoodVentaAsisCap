<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

include("buscar_nivel.php");
require("conexion.php");
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
	$cod_Consultorio=$_POST['cod_Consultorio'];
$cod_Consultorio = mb_convert_encoding((string)($cod_Consultorio), 'ISO-8859-1', 'UTF-8');
	
	$nombre=$_POST['nombre'];
$nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');

$descripcion=$_POST['descripcion'];
$descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');

$color=$_POST['color'];
$color = mb_convert_encoding((string)($color), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');

$cod_doctor=$_POST['cod_doctor'];
$cod_doctor = mb_convert_encoding((string)($cod_doctor), 'ISO-8859-1', 'UTF-8');
	
	abm($cod_Consultorio,$nombre,$descripcion,$color,$estado,$cod_local,$cod_doctor,$operacion);

}

if($operacion=="buscar")
{
	$codigo=$_POST['codigo'];
$codigo = mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
$nombre=$_POST['nombre'];
$nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$descripcion=$_POST['descripcion'];
$descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
$NombreLocal=$_POST['NombreLocal'];
$NombreLocal = mb_convert_encoding((string)($NombreLocal), 'ISO-8859-1', 'UTF-8');
	buscar($codigo,$nombre,$estado,$descripcion,$NombreLocal);

}	

 

if($operacion=="buscaroption")
{
	buscaroption();
}	


}

function abm($cod_Consultorio,$nombre,$descripcion,$color,$estado,$cod_local,$cod_doctor,$operacion)
{
	
	
if($nombre==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{


$consulta1="Insert into consultorios (nombre,descripcion,color,estado,cod_localFk,cod_doctorFK)
values(?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$nombre,$descripcion,$color,$estado,$cod_local,$cod_doctor);


}


if($operacion=="editar")
{

$consulta1="Update consultorios set nombre=?,descripcion=?,color=?,estado=?,cod_localFk=?,cod_doctorFK=? where id_consultorio=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssis';
$stmt1->bind_param($ss,$nombre,$descripcion,$color,$estado,$cod_local,$cod_doctor,$cod_Consultorio); 

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

function buscar($codigo,$nombre,$estado,$descripcion,$NombreLocal)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 	$condicioncodigo="";
if($codigo!=""){
	$condicioncodigo=" and id_consultorio ='".$codigo."'";
}
$condicionnombre="";
if($nombre!=""){
	$condicionnombre=" and nombre  like '%".$nombre."%'";
}

$condiciondescripcion="";
if($descripcion!=""){
	$condiciondescripcion=" and descripcion  like '%".$descripcion."%'";
}

$condicionNombreLocal="";
if($NombreLocal!=""){
	$condicionNombreLocal=" and cod_localFk ='".$NombreLocal."'";
}

		$sql= "Select *,
(select  Nombre from local where cod_local=cod_localFk) as NombreLocal from consultorios where estado=? ".$condicioncodigo.$condicionnombre.$condiciondescripcion.$condicionNombreLocal;
		
   
   
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
		  
		  
		      $id_consultorio=$valor['id_consultorio'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $color=mb_convert_encoding((string)($valor['color']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_localFk=mb_convert_encoding((string)($valor['cod_localFk']), 'UTF-8', 'ISO-8859-1');
		  	  $NombreLocal=mb_convert_encoding((string)($valor['NombreLocal']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_doctorFK=mb_convert_encoding((string)($valor['cod_doctorFK']), 'UTF-8', 'ISO-8859-1');
		  	 
			  $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' style='background-color:$color' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmConsultorio(this);verVentanaEditarConsultorio();'>
<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$id_consultorio."</td>
<td  id='td_datos_1' style='width:30%'>".$nombre."</td>
<td  id='td_datos_2' style='width:30%'>".$descripcion."</td>
<td  id='td_datos_6' style='width:30%'>".$NombreLocal."</td>
<td  id='td_datos_3' style='display:none'>".$color."</td>
<td  id='td_datos_4' style='display:none'>".$estado."</td>
<td  id='td_datos_5' style='display:none'>".$cod_localFk."</td>
<td  id='td_datos_7' style='display:none'>".$cod_doctorFK."</td>
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
 
verificar($operacion);
?>
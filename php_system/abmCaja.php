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
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok" && $operacion!="buscaroptionlogin"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}


	
if($operacion=="nuevo" || $operacion=="editar")
{
	
$idcaja=$_POST['idcaja'];
$idcaja = mb_convert_encoding((string)($idcaja), 'ISO-8859-1', 'UTF-8');
$cajanro=$_POST['cajanro'];
$cajanro = mb_convert_encoding((string)($cajanro), 'ISO-8859-1', 'UTF-8');
$puntoexpedicion=$_POST['puntoexpedicion'];
$puntoexpedicion = mb_convert_encoding((string)($puntoexpedicion), 'ISO-8859-1', 'UTF-8');
$cod_localFK=$_POST['cod_localFK'];
$cod_localFK = mb_convert_encoding((string)($cod_localFK), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

if($operacion=="editar")
{
	registrarSolicitudEliminacionGenerica(
		"caja",
		"idcaja",
		$idcaja,
		"Solicitud automatica por edicion de caja.",
		$user,
		"archivo: abmCaja.php | funcion: verificar | funt: editar | idcaja: ".$idcaja." | cajanro: ".$cajanro." | puntoexpedicion: ".$puntoexpedicion." | cod_localFK: ".$cod_localFK." | estado: ".$estado
	);
}

	abm($idcaja,$cajanro,$puntoexpedicion,$cod_localFK,$estado,$operacion);

}

if($operacion=="buscar")
{
	$codigo=$_POST['codigo'];
$codigo = mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
$descrip=$_POST['descrip'];
$descrip = mb_convert_encoding((string)($descrip), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	buscar($codigo,$descrip,$estado);

}	


if($operacion=="buscaroption")
{
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	buscaroption($cod_local);

}	

if($operacion=="buscaroptionlogin")
{
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	buscaroptionlogin($cod_local);

}	

}

function abm($idcaja,$cajanro,$puntoexpedicion,$cod_localFK,$estado,$operacion)
{
	
	
if($cajanro==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();
	/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	
if($operacion=="nuevo")
{


$consulta1="Insert into caja (cajanro,puntoexpedicion,cod_localFK,estado,cod_user_insert,fecha_insert)
values(?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$cajanro,$puntoexpedicion,$cod_localFK,$estado,$user,$fecha_inser_edit);


}


if($operacion=="editar")
{
$consulta1="Update caja set cajanro=?,puntoexpedicion=?,cod_localFK=?,estado=?,cod_user_edit=?,fecha_edit=?  where idcaja=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$cajanro,$puntoexpedicion,$cod_localFK,$estado,$user,$fecha_inser_edit,$idcaja); 

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

function buscar($codigo,$descrip,$estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicioncodigo="";
	 if($codigo!=""){
		 $condicioncodigo=" and idcaja='".$codigo."'"; 
	 }
	 $condiciondescripcion="";
	  if($descrip!=""){
		 $condiciondescripcion=" and cajanro like '%".$descrip."%'"; 
	 }
	 
		$sql= "Select idcaja, cajanro, puntoexpedicion, estado, cod_localFK,
		(Select Nombre from local l where l.cod_local=cod_localFK) as nombrelocal,fecha_insert,fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
		from caja where estado=? ".$condicioncodigo.$condiciondescripcion;   
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
		  
		  
		      $idcaja=$valor['idcaja'];
		  	  $cajanro=mb_convert_encoding((string)($valor['cajanro']), 'UTF-8', 'ISO-8859-1');
		  	  $puntoexpedicion=mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_localFK=mb_convert_encoding((string)($valor['cod_localFK']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	 $insertadopor = mb_convert_encoding((string)($valor['insertadopor']), 'UTF-8', 'ISO-8859-1'); 
$editadopor = mb_convert_encoding((string)($valor['editadopor']), 'UTF-8', 'ISO-8859-1'); 
$fecha_insert = mb_convert_encoding((string)($valor['fecha_insert']), 'UTF-8', 'ISO-8859-1'); 
$fecha_edit = mb_convert_encoding((string)($valor['fecha_edit']), 'UTF-8', 'ISO-8859-1'); 
		  	 
			  $styleName=CargarStyleTable($styleName);  	 
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmCaja(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idcaja."</td>
<td  id='td_datos_1' style='width:20%'>".$cajanro."</td>
<td  id='td_datos_2' style='width:20%'>".$puntoexpedicion."</td>
<td  id='' style='width:20%'>".$nombrelocal."</td>
<td  id='td_datos_3' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_4' style='display:none'>".$estado."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroption($cod_local)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from caja where cod_localFK='$cod_local' and estado='Activo' ";
		 $pagina="";  
		 $paginaexp="";  

   
   
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
		  
		  
		      $idcaja=$valor['idcaja'];
		  	  $cajanro=mb_convert_encoding((string)($valor['cajanro']), 'UTF-8', 'ISO-8859-1');
		  	  $puntoexpedicion=mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
				  	 
		  	 
			    	
			  $pagina.="<option  value='$idcaja' >".$cajanro."</option>";   
			  $paginaexp.="<option  value='$idcaja' >".$puntoexpedicion."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => $paginaexp);
echo json_encode($informacion);	
exit;


}

function buscaroptionlogin($cod_local)
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from caja where cod_localFK='$cod_local' and estado='Activo' ";
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
		  
		  
		      $idcaja=$valor['idcaja'];
		  	  $cajanro=mb_convert_encoding((string)($valor['cajanro']), 'UTF-8', 'ISO-8859-1');
				  	 
		  	 
			    	
			  $pagina.="<option  value='$idcaja' >".$cajanro."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




verificar($operacion);
?>

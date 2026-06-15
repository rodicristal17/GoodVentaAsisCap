<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

function verificar($operacion)
{
	
	
	if($operacion=="buscaroption")
{

	buscaroption();

}else {	
	
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



//CONTROL DE ACCESO



	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$nombre=$_POST['nombre'];
$nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$encargado=$_POST['encargado'];
$encargado = mb_convert_encoding((string)($encargado), 'ISO-8859-1', 'UTF-8');
$idzona=$_POST['idzona'];
$idzona = mb_convert_encoding((string)($idzona), 'ISO-8859-1', 'UTF-8');
abm($nombre,$estado,$encargado,$idzona,$operacion);

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
if($operacion=="buscarvista")
{
	$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
	buscarvista($buscar);

}
}

}

function abm($nombre,$estado,$encargado,$idzona,$operacion)
{
	
	
if($nombre=="" || $encargado==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo")
{


$consulta1="Insert into zona (nombre,estado,encargado)
values(?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$nombre,$estado,$encargado);


}


if($operacion=="editar")
{

$consulta1="Update zona set nombre=?,estado=?,encargado=? where idzona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nombre,$estado,$encargado,$idzona);

}

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


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
	$condicioncodigo=" and idzona ='".$codigo."'";
}
$condicionnombre="";
if($nombre!=""){
	$condicionnombre=" and nombre  like '%".$nombre."%'";
}
		$sql= "Select idzona,nombre,estado,encargado,
		(Select count(cod_cliente) from cliente where idzonaFk=idzona) as nroCliente
		from zona  where estado=? ".$condicioncodigo.$condicionnombre;
		
   
   
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
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $nroCliente=mb_convert_encoding((string)($valor['nroCliente']), 'UTF-8', 'ISO-8859-1');
		  	  $encargado=mb_convert_encoding((string)($valor['encargado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			  $styleName=CargarStyleTable($styleName);  	 
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmZona(this)'>
<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$idzona."</td>
<td  id='td_datos_1' style='width:50%'>".$nombre."</td>
<td  id='td_datos_3' style='width:30%'>".$encargado."</td>
<td  id='' style='width:10%'>".number_format($nroCliente,'0',',','.')."</td>
<td  id='td_datos_2' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscarvista($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select * from zona where nombre like ?  and estado='Activo' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$buscar="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

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
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	 $styleName=CargarStyleTable($styleName);  
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosVistaZona(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idzona."</td>
<td  id='td_datos_1' style='width:50%'>".$nombre."</td>
<td  id='td_datos_2' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroption()
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from zona where estado='Activo' ";
		 $pagina="<option  value='' >SELECCIONAR</option>";  

   
   
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
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	
			  $pagina.="<option  value='$idzona' >".$nombre."</option>";   
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




verificar($operacion);
?>

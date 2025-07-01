<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
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

//CONTROL DE ACCESO



if($operacion=="nuevo" || $operacion=="editar" )
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

$cod_proveedor=$cod_persona;

$rut_proveedor=$_POST['rut_proveedor'];
$rut_proveedor = utf8_decode($rut_proveedor);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

abm($estado,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_proveedor,$rut_proveedor,$operacion);

}

 
 
 if($operacion=="buscar"){
 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$ruc=$_POST["ruc"];
 	$ruc=utf8_decode($ruc);
	$proveedor=$_POST["proveedor"];
 	$proveedor=utf8_decode($proveedor);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
 	BuscarRegistro($codigo,$ruc,$proveedor,$estado);
 } 
 if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }





}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($estado,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_proveedor,$rut_proveedor,$operacion)
{

if($nombre_persona==""  || $rut_proveedor=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);
if($operacion=="nuevo") 
{
$consulta1="Insert into persona (nombre_persona,direccion,telefono,email)
values(?,?,?,?)";

$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email);

$consulta2="Insert into proveedor (rut_proveedor,cod_proveedor,estado,cod_user_insert,fecha_insert)
values(?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?)";
$stmt2 = $mysqli->prepare($consulta2);
$ss='ssss';
$stmt2->bind_param($ss,$rut_proveedor,$estado,$user,$fecha_inser_edit);

}


if($operacion=="editar")
{

$consulta1="Update persona set nombre_persona=?,direccion=?,telefono=?,email=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email,$cod_persona);


$consulta2="update proveedor set rut_proveedor=?,estado=?,cod_user_edit=?,fecha_edit=? where cod_proveedor=? ";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssss';
$stmt2->bind_param($ss,$rut_proveedor,$estado,$user,$fecha_inser_edit,$cod_persona);


}
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

if (!$stmt2->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function BuscarRegistro($codigo,$ruc,$proveedor,$estado)
{
$mysqli=conectar_al_servidor();
$condicioncod="";
if($codigo!=""){
	$condicioncod=" and pr.cod_persona = '".$codigo."'";
}
$condicionruc="";
if($ruc!=""){
	$condicionruc=" and cl.rut_proveedor = '".$ruc."'";
}

$condicionproveedor="";
if($proveedor!=""){
	$condicionproveedor=" and pr.nombre_persona like '%".$proveedor."%'";
}
$sql= "select pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.rut_proveedor,cl.estado,
cl.fecha_insert,cl.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
from  persona pr inner join  proveedor cl on cl.cod_proveedor=pr.cod_persona 
where cl.estado=? ".$condicioncod.$condicionruc.$condicionproveedor;
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$estado);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cod_persona = utf8_encode($valor['cod_persona']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_proveedor = utf8_encode($valor['rut_proveedor']); 
$estado = utf8_encode($valor['estado']); 
$insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmProveedor(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_persona."</td>
<td  id='td_datos_2' style='width:10%'>".$rut_proveedor."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

/*Buscar Registro en vista*/
function BuscarRegistroEnVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.rut_proveedor,cl.estado 
from  persona pr inner join  proveedor cl on cl.cod_proveedor=pr.cod_persona 
where concat(pr.nombre_persona,' ',cl.rut_proveedor) like ? and cl.estado='Activo' ";/*Sentencia para buscar registros*/
$pagina = "";   
$buscar="%".$buscar."%";
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
$s='s';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt->bind_param($s,$buscar);/*Se cargar los paramentros a la sentencia preparada*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cod_persona = utf8_encode($valor['cod_persona']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$direccion = utf8_encode($valor['direccion']);          
$telefono = utf8_encode($valor['telefono']); 
$email = utf8_encode($valor['email']); 
$rut_proveedor = utf8_encode($valor['rut_proveedor']); 
$estado = utf8_encode($valor['estado']); 

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaProveedor(this)'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_2' style='width:10%'>".$rut_proveedor."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


ObtenerDatos($operacion);

?>
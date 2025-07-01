<?php
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
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


$idpedidos=$_POST['idpedidos'];
$idpedidos = utf8_decode($idpedidos);

$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);

$costo=$_POST['costo'];
$costo = quitarseparadormiles($costo);

$cod_clienteFK=$_POST['cod_clienteFK'];
$cod_clienteFK = utf8_decode($cod_clienteFK);

$cantidad=$_POST['cantidad'];
$cantidad = quitarseparadormiles($cantidad);





abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$cantidad,$operacion);

}

 
 if($operacion=="buscarporvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }





}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$cantidad,$operacion)
{

if($cod_productoFK==""  || $costo==""  || $cod_clienteFK==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo")
{


$consulta1="Insert into pedidos (cod_productoFK,costo,cod_clienteFK,fecha,estado,cantidad)
values(?,?,?,CURRENT_TIMESTAMP,'ACTIVO',?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK,$cantidad);


}


if($operacion=="editar")
{

$consulta1="Update pedidos set cod_productoFK=?,costo=?,cod_clienteFK=?,fecha=CURRENT_TIMESTAMP,estado='ACTIVO',cantidad=? where idpedidos=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK,$cantidad,$idpedidos); 




}




if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function BuscarRegistroEnVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_persona,pr.nombre_persona,cl.rut_cliente,pd.cantidad,
pd.idpedidos,pd.costo,pd.cod_productoFK,pro.nombre_producto,
pro.precio_producto
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona
 inner join pedidos pd on pd.cod_clienteFK=cl.cod_cliente
 inner join producto pro on pro.cod_producto=pd.cod_productoFK
 where concat(pr.nombre_persona,' ',cl.rut_cliente) like ? limit 300 ";

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



$idpedidos = utf8_encode($valor['idpedidos']); 
$cod_productoFK = utf8_encode($valor['cod_productoFK']);  
$cod_persona = utf8_encode($valor['cod_persona']);  
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$rut_cliente = utf8_encode($valor['rut_cliente']);          
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$costo = utf8_encode($valor['costo']); 
$precio_producto = utf8_encode($valor['precio_producto']);          
$cantidad = utf8_encode($valor['cantidad']);          
 $paginaSelecc=buscardetallesprecios($cod_productoFK,$precio_producto);

  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatospedidos(this)'>
<td id='' style='width:50%;' class='td_search'>".$nombre_persona."</td>
<td id='' style='width:50%;' class='td_search'>".$nombre_producto."</td>
<td id='td_1' style='display:none' >".$idpedidos."</td>
<td id='td_2' style='display:none' >".$cod_productoFK."</td>
<td id='td_3' style='display:none'>".$cod_persona."</td>
<td id='td_4' style='display:none'>".$nombre_persona."</td>
<td id='td_5' style='display:none'>".$rut_cliente."</td>
<td id='td_6' style='display:none'>".$nombre_producto."</td>
<td id='td_7' style='display:none'>".$costo."</td>
<td id='td_8' style='display:none'>".$precio_producto."</td>
<td id='td_9' style='display:none'>".$paginaSelecc."</td>
<td id='td_10' style='display:none'>".number_format($cantidad,'2',',','.')."</td>
</tr>
</table>";


}
}

    
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscardetallesprecios($buscar,$precio)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio
 from  detallesprecio 
where cod_producto=? ";
 $pagina="<option value='".number_format($precio,'0',',','.')."'  >Contado</option>";  
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



$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          


	  $pagina.="<option value='".number_format($precio,'0',',','.')."'>".$descripcion."</option>";



}
}

return $pagina;
}



ObtenerDatos($operacion);

?>
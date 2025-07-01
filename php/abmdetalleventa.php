<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
}






	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);
$cantidad_detalle=$_POST['cantidad_detalle'];
$cantidad_detalle = quitarseparadormiles($cantidad_detalle);
	$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);
$precio_producto=$_POST['precio_producto'];
$precio_producto = quitarseparadormiles($precio_producto);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
$subtotal=$_POST['subtotal'];
$subtotal = quitarseparadormiles($subtotal);
$subPrecioCompra=obtenerCostoProducto($cod_productoFK);
$num_factura=$_POST['num_factura'];
$num_factura = utf8_decode($num_factura);
if($cod_ventaFK==""){
	$cod_ventaFK=iniciarVenta($user,$num_factura);
	
}


abm($cod_detalle,$cantidad_detalle,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$operacion);

}

if($operacion=="eliminar")
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
$cantida=$_POST['cantida'];
$cantida = utf8_decode($cantida);
$codProducto=$_POST['codProducto'];
$codProducto = utf8_decode($codProducto);
quitarproducto($cod_detalle,$cod_ventaFK,$cantida,$codProducto);

}

if($operacion=="buscar")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
	BuscarRegistro($cod_ventaFK);

}	
	

}

function abm($cod_detalle,$cantidad_detalle,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$operacion)
{
	
	
if($cantidad_detalle=="" || $cod_productoFK=="" || $cod_ventaFK==""  ){
$inforOacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into detalle_venta (cantidad_detalle,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado)
values(?,?,?,?,?,?,'Activo')";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='ssssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cantidad_detalle,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra);/*Se cargar los paramentros a la sentencia preparada*/


}


/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

editar_cantidad($cod_productoFK,$cantidad_detalle,"resta");

$subtotal=obtenerTotal($cod_ventaFK);
actualizarTotal($cod_ventaFK,$subtotal);

$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'),"3" => $cod_ventaFK);/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
	
}

function quitarproducto($cod_detalle,$cod_ventaFK,$cantida,$codProducto)
{
	
	
if($cod_detalle=="" ||  $cod_ventaFK==""  ){
$inforOacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */



$consulta1="delete from detalle_venta where cod_detalle=? ";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='s';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cod_detalle);/*Se cargar los paramentros a la sentencia preparada*/


/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

editar_cantidad($codProducto,$cantida,"suma");

$subtotal=obtenerTotal($cod_ventaFK);
actualizarTotal($cod_ventaFK,$subtotal);

$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'),"3" => $cod_ventaFK);/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
	
}

	function editar_cantidad($idproductos,$cantidad,$t){
       $mysqli=conectar_al_servidor(); 
 
	    if($t=="resta"){
			$consulta="Update producto set stock_producto=(stock_producto-$cantidad)  where cod_producto='".$idproductos."'";
		// if((obtenerStockActual($idproductos)-$cantidad)>0){
			   // $consulta="Update producto set stock_producto=(stock_producto-$cantidad)  where cod_producto='".$idproductos."'";
		   // }else{
			   // $consulta="Update producto set stock_producto=0  where cod_producto='".$idproductos."'";	
		   // }
				

	}else{
		 $consulta="Update producto set stock_producto=(stock_producto+$cantidad)  where cod_producto='".$idproductos."'";	
           // if(obtenerStockActual($idproductos)>0){
			   // $consulta="Update producto set stock_producto=(stock_producto+$cantidad)  where cod_producto='".$idproductos."'";	
		   // }else{
			   // $consulta="Update producto set stock_producto=$cantidad  where cod_producto='".$idproductos."'";	
		   // }
			

	}
	

	$stmt = $mysqli->prepare($consulta);


	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


    }
	
	function obtenerStockActual($codProducto)
{
	$mysqli=conectar_al_servidor();
	 $Stock='';
		$sql= "Select stock_producto
		from producto where cod_producto='$codProducto' ";
		

   
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
		  
		  
		      $Stock=$valor['stock_producto'];
		  	 
			  
	  }
 }
 
 
  return $Stock;

}

function obtenerTotal($cod_ventaFK)
{
	$mysqli=conectar_al_servidor();
	 $subtotal='';
	$sql= "Select sum(subtotal) as subtotal from detalle_venta where cod_ventaFK='$cod_ventaFK'  ";
		
   
   
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
		  
		  
		      $subtotal=$valor['subtotal'];
		  	
			  
			  
	  }
 }
 
 
return $subtotal;


}


function obtenerCostoProducto($cod_producto)
{
	$mysqli=conectar_al_servidor();
	 $precio_compra='';
	$sql= "Select precio_compra from producto where cod_producto='$cod_producto'  ";
		
   
   
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
		  
		  
		      $precio_compra=$valor['precio_compra'];
		  	
			  
			  
	  }
 }
 
 
return $precio_compra;


}

function actualizarTotal($cod_venta,$total){
	
	$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */
	$consulta1="Update venta set total_venta=? where cod_venta=?";	/*Sentencia para editar registros*/
/*La sentencia update es utilizado para editar datos y esta compuesto por 
update elnombredelatabla set (indicar que atributos se van a modificar igualando al simbolo ? separados entre comas) 
where nombreatributo=? condición para editar solo el registro seleccionado
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='ss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$total,$cod_venta); /*Se cargar los paramentros a la sentencia preparada*/

if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


}


function iniciarVenta($cod_usuarioFK,$num_factura){
		$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

	$consulta1="Insert into venta (fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor)
values(Current_Date(),'0','$cod_usuarioFK','43','$num_factura','44','Contado','Corrido','')";/*Sentencia para insertar registros*/	
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
   return obtenerId('43',$cod_usuarioFK,$num_factura);
}

function obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura)
{
	$mysqli=conectar_al_servidor();
	 $cod_venta='';
		$sql= "Select cod_venta from venta where cod_clienteFK='$cod_clienteFK' and cod_usuarioFK='$cod_usuarioFK' and num_factura='$num_factura' ";
		
   
   
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
		  
		  
		      $cod_venta=$valor['cod_venta'];
		  	
			  
			  
	  }
 }
 
 
return $cod_venta;


}


function  BuscarRegistro($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,
IFNULL((select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta),0) as totalpagado,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";/*Sentencia para buscar registros*/

$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cod_producto = utf8_encode($valor['cod_producto']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$cod_detalle = utf8_encode($valor['cod_detalle']);          
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']); 
$cod_productoFK = utf8_encode($valor['cod_productoFK']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$cod_ventaFK = utf8_encode($valor['cod_ventaFK']); 
$subPrecioCompra = utf8_encode($valor['subPrecioCompra']); 
$subtotal = utf8_encode($valor['subtotal']); 
$totalventa = utf8_encode($valor['total_venta']); 
$totalpagado = utf8_encode($valor['totalpagado']); 


	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetalleventa(this)'>
<td id='td_id_1' style='display:none'>".$cod_producto."</td>
<td id='td_id_2' style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_1' style='width:10%'>".$nombre_producto."</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalventa,'0',',','.'),"4" => number_format($totalpagado,'0',',','.'));
echo json_encode($informacion);	
exit;
}


verificar($operacion);
?>
<?php
require("conexion.php");
$operacion = $_POST['funt'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$operacion = utf8_decode($operacion);/*Función para cambiar letras que esten codificadas por la cadificación de caracteres */
/*Función principal del php que se ejecutal cargar todo el archivo php y llamado al final de php.*/
function ObtenerDatos($operacion)
{


 
 
 if($operacion=="buscarporpedido"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnPedidos($buscar);
 }

 if($operacion=="buscarpordevolucion"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$idCliente=$_POST["idCliente"];
 	$idCliente=utf8_decode($idCliente);
 	BuscarRegistroEnDevoluciones($buscar,$idCliente);
 }





}


/*Buscar Registro en vista*/
function BuscarRegistroEnPedidos($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,pr.precio_producto,pr.precio_producto2,pr.precio_producto4,
pr.precio_producto5,pr.precio_producto6,pr.precio_producto9,pr.precio_producto10,pr.precio_producto12,pr.precio_producto15 from  producto
 pr  where concat(pr.nombre_producto,' ',pr.descripcion_producto) like ? ";/*Sentencia para buscar registros*/
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cod_producto = utf8_decode($valor['cod_producto']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = utf8_decode($valor['nombre_producto']);          
$precio_producto = utf8_decode($valor['precio_producto']);          
$precio_producto2 = utf8_decode($valor['precio_producto2']); 
$precio_producto4 = utf8_decode($valor['precio_producto4']); 
$precio_producto5 = utf8_decode($valor['precio_producto5']); 
$precio_producto6 = utf8_decode($valor['precio_producto6']); 
$precio_producto9 = utf8_decode($valor['precio_producto9']); 
$precio_producto10 = utf8_decode($valor['precio_producto10']); 
$precio_producto12 = utf8_decode($valor['precio_producto12']); 
$stock_producto = utf8_decode($valor['stock_producto']); 


$pagina.="
<table class='tableBuscado' >
<tr onclick='obtenerdatosProductospedidos(this)' >
<td style='width: 5%;'><img src='/GoodVentaElim/iconos/productos.png' class='imgAbmBuscado' /></td>
<td style='width: 95%;text-align: left;'><p class='pTituloDatos'><b>Producto: </b>".$nombre_producto."<br><br>
<b>Stock: </b>". number_format($stock_producto,'0',',','.')."<br>
<b>Costo: </b>". number_format($precio_producto,'0',',','.')." Gs.</p></td>
<td id='td_1' style='display:none' >".$cod_producto."</td>
<td id='td_2' style='display:none' >".$nombre_producto."</td>
<td id='td_3' style='display:none'>".$precio_producto."</td>
<td id='td_4' style='display:none'>".$precio_producto2."</td>
<td id='td_5' style='display:none'>".$precio_producto4."</td>
<td id='td_6' style='display:none'>".$precio_producto5."</td>
<td id='td_7' style='display:none'>".$precio_producto6."</td>
<td id='td_8' style='display:none'>".$precio_producto9."</td>
<td id='td_9' style='display:none'>".$precio_producto10."</td>
<td id='td_10' style='display:none'>".$precio_producto12."</td>
</tr>
</table>
";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

/*Buscar Registro en vista*/
function BuscarRegistroEnDevoluciones($buscar,$idCliente)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.cod_venta,dtv.precio_producto,vt.fecha_venta
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
 where concat(pr.nombre_producto,' ',pr.descripcion_producto) like ?  and  
 (select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta)<vt.total_venta 
and cod_clienteFK=? ";
 /*Sentencia para buscar registros*/
$pagina = "";   
$buscar="%".$buscar."%";
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
$s='ss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt->bind_param($s,$buscar,$idCliente);/*Se cargar los paramentros a la sentencia preparada*/
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



$cod_producto = utf8_decode($valor['cod_producto']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = utf8_decode($valor['nombre_producto']);          
$cod_detalle = utf8_decode($valor['cod_detalle']);          
$cod_venta = utf8_decode($valor['cod_venta']); 
$total_venta = utf8_decode($valor['precio_producto']); 
$fecha_venta = utf8_decode($valor['fecha_venta']); 

$pagina.="
<table class='tableBuscado' >
<tr onclick='obtenerdatosProductoDevoluciones(this)' >
<td style='width: 5%;'><img src='/GoodVentaElim/iconos/productos.png' class='imgAbmBuscado' /></td>
<td style='width: 95%;text-align: left;'><p class='pTituloDatos'><b>Produc. : </b>".$nombre_producto."<br><b>Total : </b>". number_format($total_venta,'0',',','.') ."Gs. <br><b>Fecha Vent : </b>".$fecha_venta." Gs.</p></td>
<td id='td_1' style='display:none' >".$cod_producto."</td>
<td id='td_2' style='display:none' >".$nombre_producto."</td>
<td id='td_3' style='display:none'>".$cod_detalle."</td>
<td id='td_4' style='display:none'>".$cod_venta."</td>
</tr>
</table>
";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


ObtenerDatos($operacion);

?>
<?php
require("conexion.php");
include("classTable.php");
$operacion = $_POST['funt'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$operacion = utf8_decode($operacion);

function ObtenerDatos($operacion)
{


 
 
 if($operacion=="buscarporpedido"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
 	BuscarRegistroEnPedidos($buscar,$local);
 }
 if($operacion=="buscarlista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
 	BuscarRegistroEnLista($buscar,$local);
 }

 if($operacion=="buscarpordevolucion"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$idCliente=$_POST["idCliente"];
 	$idCliente=utf8_decode($idCliente);
 	BuscarRegistroEnDevoluciones($buscar,$idCliente,$local);
 }
 
 if($operacion=="buscarprecios"){
 
	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarprecios($buscar);
 }





}
function  buscarprecios($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Porcentaje,
(select precio_producto from producto pr where pr.cod_producto=dp.cod_producto ) as precioContado , 
(select concat(nombre_producto,' ',descripcion_producto) from producto pr where pr.cod_producto=dp.cod_producto )  as Producto
 from  detallesprecio  dp
where cod_producto=? ";
$pagina = "";   
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
$precioContado = utf8_encode($valor['precioContado']); 
$Producto = utf8_encode($valor['Producto']); 
$cod_producto = utf8_encode($valor['cod_producto']); 
$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Porcentaje = utf8_encode($valor['Porcentaje']);   


$precioCredito = buscardetallespreciosimprimir($cod_producto);       


	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' >
<td  id='td_datos_1' style='width:70%'>".number_format($precio,'0',',','.') ."</td>
<td  id='td_datos_2' style='width:30%'>".$descripcion."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro ,"4" => $Producto ,"5" => number_format($precioContado,'0',',','.')  ,"6" => $precioCredito);
echo json_encode($informacion);	
exit;
}



function  buscardetallespreciosimprimir($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select (select porcentaje from producto p where p.cod_producto=dt.cod_producto) as porcentajeContado , preciocuota,Porcentaje as porcen,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio dt
where cod_producto=? ";
 $pagina=" <b class='pTitulo2' style='font-size: 13px;padding: 5px;' > <u>EN CUOTAS:</u>  </b>";  
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$buscar);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$Porcentaje = 26;  
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

 $styleName="tableRegistroSearch";
 
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$porcentajeContado = utf8_encode($valor['porcentajeContado']);  
$Porcentaje = utf8_encode($valor['porcen']);  
$preciocuota = utf8_encode($valor['preciocuota']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


// $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' style='height: 20px; font-size: 11px;padding: 0px;' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td   style='width:100%;padding: 0px;'>&nbsp;".$Cuota."&nbsp; X&nbsp; <b>&nbsp;&nbsp;<u> Gs.&nbsp;&nbsp;&nbsp; ".number_format($preciocuota,'0',',','.')." </u></b></td>
</tr>
</table>";
// $pagina="aa";
}
}
 
return $pagina;
}






/*Buscar Registro en vista*/
function BuscarRegistroEnPedidos($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK ='$local'";
}
$sql= "select pr.cod_producto,pr.nombre_producto,pr.precio_producto,stk.cantidad as stock_producto,cod_barra,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as localnombre,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
 where concat(pr.nombre_producto,' ',pr.cod_producto,' ',pr.descripcion_producto,' ',(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1),' ',cod_barra) like ? and pr.estado='Activo' ".$condicionLocal." limit 100 ";
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



$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$precio_producto = utf8_encode($valor['precio_producto']);  
$stock_producto = utf8_encode($valor['stock_producto']); 
$localnombre = utf8_encode($valor['localnombre']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
 $paginaSelecc=buscardetallesprecios($cod_producto,$precio_producto);


  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosProductospedidos(this)'>
<td  style='width:80%;'>

<table style='width:100%;' class='tableRegistroSearchE' >
<tr>
<td  style='width:85%;'class='td_search' >".$nombre_producto."</td>
<td  style='width:15%;'class='td_search' >". number_format($stock_producto,'0',',','.')."</td>
</tr>
</table>

<table style='width:100%;' class='tableRegistroSearchF' >
<tr>
<td  style='width:100%;'class='td_search' >Marca: ".$NombreMarca." Gs.</td>
</tr>
</table>

<table style='width:100%;' class='tableRegistroSearchF' >
<tr>
<td  style='width:100%;'class='td_search' >Cod.: ".$cod_barra." .</td>
</tr>
</table>

<table style='width:100%;' class='tableRegistroSearchF' >
<tr>
<td  style='width:100%;'class='td_search' >Precio: ".number_format($precio_producto,'0',',','.')." Gs.</td>
</tr>
</table>



</td>
<td  id='td_datos_2' style='display:none' class='td_search'>". number_format($stock_producto,'0',',','.')."</td>
<td id='td_datos_1' style='display:none' class='td_search'>". number_format($precio_producto,'0',',','.')."</td>
<td id='' style='display:none' >".$localnombre."</td>
<td id='td_1' style='display:none' >".$cod_producto."</td>
<td id='td_2' style='display:none' >".$nombre_producto."</td>
<td id='td_3' style='display:none'>".$precio_producto."</td>
<td id='td_4' style='display:none'>".$paginaSelecc."</td>
<td id='td_5' style='display:none'>".$NombreMarca."</td>
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
function BuscarRegistroEnLista($buscar,$local)
{
$mysqli=conectar_al_servidor();
if($local!=""){
	$condicionLocal="and stk.cod_localFK='".$local."' ";
}
$sql= "select pr.cod_producto,pr.nombre_producto,pr.precio_producto,stk.cantidad as stock_producto,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as localnombre
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where concat(pr.nombre_producto,' ',pr.descripcion_producto) like ? and pr.estado='Activo' ".$condicionLocal." limit 100 ";
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



$cod_producto = utf8_encode($valor['cod_producto']);      
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$precio_producto = utf8_encode($valor['precio_producto']);  
$stock_producto = utf8_encode($valor['stock_producto']); 
$localnombre = utf8_encode($valor['localnombre']); 
 $paginaSelecc=buscardetallesprecios($cod_producto,$precio_producto);


  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosProductoslista(this)'>
<td id='td_id' style='width:40%;' class='td_search'>".$nombre_producto."</td>
<td  id='td_datos_2' style='width:20%' class='td_search'>". number_format($stock_producto,'0',',','.')."</td>
<td id='td_datos_1' style='width:20%' class='td_search'>". number_format($precio_producto,'0',',','.')."</td>
<td id='' style='display:none' >".$localnombre."</td>
<td id='td_1' style='display:none' >".$cod_producto."</td>
<td id='td_2' style='display:none' >".$nombre_producto."</td>
<td id='td_3' style='display:none'>".$precio_producto."</td>
<td id='td_4' style='display:none'>".$paginaSelecc."</td>
</tr>
</table>";

}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}




function BuscarRegistroEnDevoluciones($buscar,$idCliente,$local)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.cod_venta,dtv.precio_producto,vt.fecha_venta,
(select Nombre from local where cod_local= pr.cod_localFK limit 1 ) as localnombre
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
 where concat(pr.nombre_producto,' ',pr.descripcion_producto) like ?  and  
 (select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta)<vt.total_venta 
and cod_clienteFK=? ".$condicionLocal;
$pagina = "";   
$buscar="%".$buscar."%";
$stmt = $mysqli->prepare($sql);
$s='ss';
$stmt->bind_param($s,$buscar,$idCliente);
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



$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$cod_detalle = utf8_encode($valor['cod_detalle']);          
$cod_venta = utf8_encode($valor['cod_venta']); 
$total_venta = utf8_encode($valor['precio_producto']); 
$fecha_venta = utf8_encode($valor['fecha_venta']); 

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

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscardetallesprecios($buscar,$precio)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio
 from  detallesprecio where cod_producto=? ";
 $pagina="<option value='".number_format($precio,'0',',','.')."'  >Contado</option>";  
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
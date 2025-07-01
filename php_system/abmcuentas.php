<?php
require("conexion.php");
include("verificar_navegador.php");
$operacion = $_POST['funt'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$operacion = utf8_decode($operacion);/*Función para cambiar letras que esten codificadas por la cadificación de caracteres */
/*Función principal del php que se ejecutal cargar todo el archivo php y llamado al final de php.*/
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


$informacion =array("1" => "UI");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
}


/*operacion de condiciones anidadas por la operación OR*/	
if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar")
{


$idpedidos=$_POST['idpedidos'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$idpedidos = utf8_decode($idpedidos);/*Función para cambiar letras que esten codificadas por la cadificación de caracteres */

$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);

$costo=$_POST['costo'];
$costo = utf8_decode($costo);

$cod_clienteFK=$_POST['cod_clienteFK'];
$cod_clienteFK = utf8_decode($cod_clienteFK);





abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$operacion);

}

 
 
 if($operacion=="buscar"){
 	$lat=$_POST["lat"];
 	$lot=$_POST["lot"];
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	$buscarpor=$_POST["buscarpor"];
 	$buscarpor=utf8_decode($buscarpor);
 	buscarregistro($buscar,$lat,$lot,$buscarpor,$user);
 }

 if($operacion=="buscarDetalle"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscardetalle($buscar);
 }





}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$operacion)
{

if($cod_productoFK==""  || $costo==""  || $cod_clienteFK==""){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into pedidos (cod_productoFK,costo,cod_clienteFK,fecha,estado)
values(?,?,?,CURRENT_TIMESTAMP,'ACTIVO')";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK);/*Se cargar los paramentros a la sentencia preparada*/


}


if($operacion=="editar")
{

$consulta1="Update pedidos set cod_productoFK=?,costo=?,cod_clienteFK=?,fecha=CURRENT_TIMESTAMP,estado='ACTIVO' where idpedidos=?";	/*Sentencia para editar registros*/
/*La sentencia update es utilizado para editar datos y esta compuesto por 
update elnombredelatabla set (indicar que atributos se van a modificar igualando al simbolo ? separados entre comas) 
where nombreatributo=? condición para editar solo el registro seleccionado
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='ssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK,$idpedidos); /*Se cargar los paramentros a la sentencia preparada*/




}



/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function buscarregistro($buscar,$lat,$lot,$buscarpor,$cod_cobradorFK)
{
$mysqli=conectar_al_servidor();


	 $sqlcoordenads=",(6371 * ACOS(SIN(RADIANS(cl.lat)) * SIN(RADIANS(".$lat."))+COS(RADIANS(cl.lot - ".$lot.")) * COS(RADIANS(cl.lat))* COS(RADIANS(".$lat.")))) AS distance ";
	$oderbycoordenadas=" order by distance asc";
if($lat=="" || $lot==""){
	$sqlcoordenads="";
	$oderbycoordenadas="";
}

if($buscarpor=="cliente"){
	
	$sql= "select cl.lat,cl.lot,pr.cod_persona,pr.nombre_persona,cl.ci_cliente,vt.cod_venta,(vt.total_venta-vt.descuento) as total,pago,vt.descuento,vt.total_venta,vt.descuento,
(select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta) as totalpagado,
(select fechapago from  credito cr1 where vt.cod_venta=cr1.cod_venta and IFNULL((select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta),0)< cr1.Monto order by cr1.fechapago asc limit 1 ) as fechaPago ".$sqlcoordenads."
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona
 inner join venta vt on vt.cod_clienteFK=cl.cod_cliente
 where concat(pr.nombre_persona,' ',cl.ci_cliente) like ? and (vt.total_venta-vt.descuento)>IFNULL((select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta),0)  and vt.cod_cobradorFK='$cod_cobradorFK'
 ".$oderbycoordenadas;/*Sentencia para buscar registros*/

 $stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
 $buscar="%".$buscar."%";
 $s='s';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt->bind_param($s,$buscar);/*Se cargar los paramentros a la sentencia preparada*/
}else{
		$sql= "select cl.lat,cl.lot,pr.cod_persona,pr.nombre_persona,cl.ci_cliente,vt.cod_venta,(vt.total_venta-vt.descuento) as total,pago,vt.descuento,vt.total_venta,vt.descuento,
(select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta) as totalpagado,
(select fechapago from  credito cr1 where vt.cod_venta=cr1.cod_venta and IFNULL((select sum(pg.Monto) from pago pg inner join credito cr on pg.cod_creditoFK=cr.idcredito where vt.cod_venta=cr.cod_venta),0) < cr1.Monto order by cr1.fechapago asc limit 1 ) as fechaPago ".$sqlcoordenads."
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona
 inner join venta vt on vt.cod_clienteFK=cl.cod_cliente
 where (select count(fechapago) from  credito cr where vt.cod_venta=cr.cod_venta and cr.fechapago<='$buscar')>0 and vt.cod_cobradorFK='$cod_cobradorFK'
 ".$oderbycoordenadas;/*Sentencia para buscar registros*/

	$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
}

$pagina = "";   



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



$cod_venta = utf8_encode($valor['cod_venta']);/*Obtenemos el registro mediante el nombre del atributo */      
$cod_persona = utf8_encode($valor['cod_persona']);  
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$ci_cliente = utf8_encode($valor['ci_cliente']);          
$total = utf8_encode($valor['total']); 
$total_venta = utf8_encode($valor['total_venta']);          
$descuento = utf8_encode($valor['descuento']); 
$pago = utf8_encode($valor['totalpagado']); 
$lat = utf8_encode($valor['lat']); 
$long = utf8_encode($valor['lot']); 
$fechaPago = utf8_encode($valor['fechaPago']); 
if($sqlcoordenads!=""){
$distance = number_format($valor['distance'],'2',',','.');
}else{
	$distance = "Ubic. no Selecc.";
}
 
if($pago==""){
	$pago=0;
}
$diff= buscarDiasAtra($cod_venta );
$pagina.="
<table class='tableBuscado' >
<tr onclick='obtenerdatoscuentas(this)' >
<td style='width: 5%;'><img src='/GoodVentaAsisCap/iconos/productos.png' class='imgAbmBuscado' /></td>
<td style='width: 95%;text-align: left;'><p class='pTituloDatos'><b>Cliente : </b>".$ci_cliente." - ".$nombre_persona."
<br><b>a Km : </b>".$distance."
<br><b>F. Pago: </b>".$fechaPago." <b style='margin-left:2%;'> D. Atras.: ".$diff." Día(s)</b>
</p></td>
<td id='td_1' style='display:none' >".$cod_venta."</td>
<td id='td_2' style='display:none' >".$cod_persona."</td>
<td id='td_3' style='display:none'>".$nombre_persona."</td>
<td id='td_4' style='display:none'>".$ci_cliente."</td>
<td id='td_5' style='display:none'>". number_format($total,'0',',','.')."</td>
<td id='td_6' style='display:none'>". number_format($total_venta,'0',',','.')."</td>
<td id='td_7' style='display:none'>".$descuento."</td>
<td id='td_8' style='display:none'>". number_format($pago,'0',',','.')."</td>
<td id='td_9' style='display:none'>".$lat."</td>
<td id='td_10' style='display:none'>".$long."</td>
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

/*Buscar Registro en detalle*/
function buscardetalle($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,'".$fechahoy."') as diff,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' ";/*Sentencia para buscar registros*/
 


 
$pagina = "";  
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
$controlStyle="";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$idcredito = utf8_encode($valor['idcredito']);/*Obtenemos el registro mediante el nombre del atributo */      
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_decode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$totalPago = utf8_encode($valor['totalPago']); 
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$diff = utf8_encode($valor['diff']);
$deudaActua=$Monto-$totalPago;
/*$diff=0;

$datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy))));
$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));
$interval=$datetime2->diff($datetime1);
$diff=$interval->format('%a');*/
if($diff<0){
	$diff=$diff*-1;
}else{
		$diff=0;
}
$event="obtenerdatoscuotero(this)";
	$stylecolor=" color: #FFEB3B;";

if($Esado=="Pagado" && $deudaActua<=0){
$event="";	
	$stylecolor="color: #03A9F4;";
}else{
	$Esado="Pendiente";
	if($controlStyle=="off"){
	$stylecolor="";
}
if($stylecolor!=""){
	$controlStyle="off";
}

}


$pagina.="
<table class='tableB' >
<tr onclick='$event'  style='$stylecolor'>
<td id='td_1' style='display:none' >".$idcredito."</td>
<td id='td_2' style='width:5%' >".$plazo."</td>
<td id='td_3' style='width:18%'>".$fechapago."</td>
<td id='td_4' style='display:none'>".$cod_venta."</td>
<td id='td_5' style='display:none'>". number_format($Monto,'0',',','.')."</td>
<td id='td_9' style='width:10%'>". number_format($deudaActua,'0',',','.')."</td>
<td id='td_6' style='width:10%'>".$Esado."</td>
<td id='td_7' style='display:none'>".$Nro_recibo."</td>
<td id='td_8' style='display:none'>".$diff."</td>
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


/*Buscar */
function buscarDiasAtra($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select datediff(cr.fechapago,'".$fechahoy."') as diff from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' and cr.Monto>IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) order by cr.fechapago asc limit 1";/*Sentencia para buscar registros*/
 



 
$pagina = "";  
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
$diff=0;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  


$diff = utf8_decode($valor['diff']);          
if($diff<0){
	$diff=$diff*-1;
}else{
		$diff=0;
}




}
}

return $diff;
}



/*Buscar */
function buscartotalpago($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select (select sum(Monto) from pago pg where pg.cod_creditoFK=cr.idcredito) as totalPago
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' ";
 echo $sql;
 exit;
 /*Sentencia para buscar registros*/
$totalpago = 0;   
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



$totalpago = utf8_decode($valor['totalPago']);/*Obtenemos el registro mediante el nombre del atributo */      




}
}

return $totalpago;
}

ObtenerDatos($operacion);

?>
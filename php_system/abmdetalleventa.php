<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
include("buscar_nivel.php");
include("BuscarNroFactura.php");
include("calcularintereses.php");
// include("calcularInteresDirecto.php");
include("classTable.php");



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
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}


	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	

$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);

$num_factura=$_POST['num_factura'];
$num_factura = utf8_decode($num_factura);

if($cod_ventaFK==""){
$fecha_venta=$_POST['fecha_venta'];
$fecha_venta = utf8_decode($fecha_venta);
$cod_usuarioFK=$user;
$cod_usuarioFK = utf8_decode($cod_usuarioFK);
$cod_clienteFK=$_POST['cod_clienteFK'];
$cod_clienteFK = utf8_decode($cod_clienteFK);
$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = utf8_decode($cod_cobradorFK);
$TipoVenta=$_POST['TipoVenta'];
$TipoVenta = utf8_decode($TipoVenta);
$TipoPago=$_POST['TipoPago'];
$TipoPago = utf8_decode($TipoPago);
$vendedor1=$_POST['vendedor1'];
$vendedor1 = utf8_decode($vendedor1);
$vendedor2=$_POST['vendedor2'];
$vendedor2 = utf8_decode($vendedor2);
$comisioncobrador=$_POST['comisioncobrador'];
$comisioncobrador = utf8_decode($comisioncobrador);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$idGaranteFk=$_POST['idGaranteFk'];
$idGaranteFk = utf8_decode($idGaranteFk);
$tipo_comprobante=$_POST['tipo_comprobante'];
$tipo_comprobante = utf8_decode($tipo_comprobante);
$puntoexpedicion=$_POST['puntoexpedicion'];
$puntoexpedicion = utf8_decode($puntoexpedicion);

$codSolicitudCreditoFK=$_POST['codSolicitudCreditoFK'];
$codSolicitudCreditoFK = utf8_decode($codSolicitudCreditoFK);

$datosventa=iniciarVenta($codSolicitudCreditoFK,$puntoexpedicion,$tipo_comprobante,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comisioncobrador,$cod_local,$idGaranteFk);
$cod_ventaFK=$datosventa[0];
$num_factura=$datosventa[1];
}
abm($cod_ventaFK,$num_factura,$operacion);

}

if($operacion=="cambio" )
{
	
	
$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
$cantidaCambio=$_POST['cantidaCambio'];
$cantidaCambio = quitarseparadormiles($cantidaCambio);
$CodProductocompraCambio=$_POST['CodProductocompraCambio'];
$CodProductocompraCambio = utf8_decode($CodProductocompraCambio);
$MetodoPagoCambio=$_POST['MetodoPagoCambio'];
$MetodoPagoCambio = utf8_decode($MetodoPagoCambio);
$Local_FK=$_POST['Local_FK'];
$Local_FK = utf8_decode($Local_FK);
cambiar($cod_detalle,$cod_ventaFK,$cantidaCambio,$CodProductocompraCambio,$MetodoPagoCambio,$user,$Local_FK);

}

if($operacion=="quitarDevolucion" )
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);

	$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);

$cantidaCambio=$_POST['cantidaCambio'];
$cantidaCambio = quitarseparadormiles($cantidaCambio);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);

$Local_FK=$_POST['Local_FK'];
$Local_FK = utf8_decode($Local_FK);

quitarDevolucion($cod_detalle,$cod_productoFK,$cod_ventaFK,$motivo,$cantidaCambio,$Local_FK);

}

if($operacion=="NuevoGarantia" )
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);
	$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
$observacion=$_POST['observacion'];
$observacion = utf8_decode($observacion);
$fecharecibido=$_POST['fecharecibido'];
$fecharecibido = utf8_decode($fecharecibido);
$telefonoaviso=$_POST['telefonoaviso'];
$telefonoaviso = utf8_decode($telefonoaviso);


usodegarantia($telefonoaviso,$observacion,$fecharecibido,$cod_detalle,$cod_productoFK,$cod_ventaFK,$user,$operacion);

}

if($operacion=="editarusogarantia" )
{
	
$idgarantia=$_POST['idgarantia'];
$idgarantia = utf8_decode($idgarantia);

$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

editarusogarantia($idgarantia,$fecha,$estado,$user);

}

if($operacion=="eliminar")
{
	
	
$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
$cantida=$_POST['cantida'];
$cantida = quitarseparadormiles($cantida);
$codProducto=$_POST['codProducto'];
$codProducto = utf8_decode($codProducto);
$operacion=$_POST['operacion_stock'];
$operacion = utf8_decode($operacion);
$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);

$Local_FK=$_POST['Local_FK'];
$Local_FK = utf8_decode($Local_FK);

quitarproducto($cod_detalle,$cod_ventaFK,$cantida,$codProducto,$operacion,$motivo,$Local_FK);


}
if($operacion=="quitardegarantia")
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = utf8_decode($cod_detalle);
quitardegarantia($cod_detalle);

}

if($operacion=="buscar")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
	BuscarRegistro($cod_ventaFK);

}	

if($operacion=="productosCompradoscliente")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
	productosCompradoscliente($cod_ventaFK);

}	

if($operacion=="productosCompradosclienteInactivo")
{
	$codCliente=$_POST['codCliente'];
$codCliente = utf8_decode($codCliente);
	productosCompradosclienteInactivo($codCliente);

}	

if($operacion=="detalleenhistorial")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
	BuscarRegistroEnHistorilaVenta($cod_ventaFK);

}	

if($operacion=="buscarproductovendidos")
{
	
	$codigo=$_POST['codigo'];
$codigo = utf8_decode($codigo);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);

$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
	$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$categoria=$_POST['categoria'];
$categoria = utf8_decode($categoria);
$marca=$_POST['marca'];
$marca = utf8_decode($marca);
$agrupacionproductovendidoinforme=$_POST['agrupacionproductovendidoinforme'];
$agrupacionproductovendidoinforme = utf8_decode($agrupacionproductovendidoinforme);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	buscarproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$agrupacionproductovendidoinforme);

}

if($operacion=="buscarmasproductovendidos")
{
	
	$codigo=$_POST['codigo'];
$codigo = utf8_decode($codigo);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);

$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
	$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$categoria=$_POST['categoria'];
$categoria = utf8_decode($categoria);
$marca=$_POST['marca'];
$marca = utf8_decode($marca);
$totalventa=$_POST['totalventa'];
$totalventa = quitarseparadormiles($totalventa);
$totalinvertido=$_POST['totalinvertido'];
$totalinvertido = quitarseparadormiles($totalinvertido);
$agrupacionproductovendidoinforme=$_POST['agrupacionproductovendidoinforme'];
$agrupacionproductovendidoinforme = utf8_decode($agrupacionproductovendidoinforme);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	buscarmasproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$totalventa,$totalinvertido,$registrocargado,$agrupacionproductovendidoinforme);

}

if($operacion=="buscarHistorialGarantia")
{

$nrofactura=$_POST['nrofactura'];
$nrofactura = utf8_decode($nrofactura);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
buscarHistorialGarantia($nrofactura,$cod_local,$documento,$cliente,$estado);

}	


if($operacion=="comisionvendedor")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$Descuento=$_POST['Descuento'];
$Descuento = utf8_decode($Descuento);
$Flete=$_POST['Flete'];
$Flete = utf8_decode($Flete);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);

$Local=$_POST['Local'];
$Local = utf8_decode($Local);

 comisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$Descuento,$Flete,$cliente,$Local,$producto);

}	

if($operacion=="mascomisionvendedor")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
$totalcomision=$_POST['totalcomision'];
$totalcomision = quitarseparadormiles($totalcomision);
$totalventa=$_POST['totalventa'];
$totalventa = quitarseparadormiles($totalventa);
$registroscargados=$_POST['registroscargados'];
$registroscargados = quitarseparadormiles($registroscargados);
$Descuento=$_POST['Descuento'];
$Descuento = utf8_decode($Descuento);
$Flete=$_POST['Flete'];
$Flete = utf8_decode($Flete); 
$totalDescuento=$_POST['totalDescuento'];
$totalDescuento = utf8_decode($totalDescuento); 
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$Local=$_POST['Local'];
$Local = utf8_decode($Local);
 mascomisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$registrocargado,$totalcomision,$totalventa,$registroscargados,$Descuento,$Flete,$producto,$totalDescuento,$cliente,$Local);

}	


if($operacion=="detallesventadevolucion")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
	
 BuscarRegistroDevolucion($buscar,$cod_local);

}

if($operacion=="buscarexpedientes")
{
	$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
	buscarexpedientes($cliente);

}	



if($operacion=="detallePedido")
{
	$cod_ventaFK=$_POST['buscar'];
	$cod_ventaFK = utf8_decode($cod_ventaFK);
	
	echo("hola");
	exit;
	detallePedido($cod_ventaFK);

}		
	

}




function  detallePedido($buscar){
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,dtv.estado,detalleproducto,dtv.descuento,dtv.comision,vt.cod_venta,vt.TipoPago,vt.num_factura,vt.puntoexpedicion,vt.fecha_venta,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select count(idgarantia) from garantias gt where gt.cod_detalle_venta_fk=dtv.cod_detalle and (gt.estado='Pendiente a verificar' or gt.estado='verificacion') limit 1),0) as nroGarantia,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";



$pagina="";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$cod_producto = utf8_encode($valor['cod_producto']); 
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$cod_detalle = utf8_encode($valor['cod_detalle']);          
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']); 
$cod_productoFK = utf8_encode($valor['cod_productoFK']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$subPrecioCompra = utf8_encode($valor['subPrecioCompra']); 
$subtotal = utf8_encode($valor['subtotal']); 
$estado = utf8_encode($valor['estado']); 
$nroGarantia = utf8_encode($valor['nroGarantia']); 
$impuesto = utf8_encode($valor['impuesto']); 
$descuento = utf8_encode($valor['descuento']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$comision = utf8_encode($valor['comision']); 
$cod_venta = utf8_encode($valor['cod_venta']); 
$TipoPago = utf8_encode($valor['TipoPago']); 
 $num_factura=utf8_encode($valor['num_factura']);
$puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
$NombreMarca=utf8_encode($valor['NombreMarca']);
$fecha_venta=utf8_encode($valor['fecha_venta']);
$telefono=utf8_encode($valor['telefono']);


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  name='tdDetalleVenta' >
<td  id='td_datos_1' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_2' style='display:none'>".$nombre_producto." *".$NombreMarca."*</td>
<td   style='width:20%;>".$nombre_producto." *".$NombreMarca."</td>
<td  id='td_datos_3' style='display:none'>".$detalleproducto."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}





function abm($cod_ventaFK,$num_factura,$operacion)
{
	
$mysqli=conectar_al_servidor(); 
$control=1;	
$totalRegistro=$_POST['totalRegistro'];
$totalRegistro = utf8_decode($totalRegistro);



while($control<=$totalRegistro){

$cod_productoFK=$_POST['cod_productoFK'.$control];
$cod_productoFK = utf8_decode($cod_productoFK);

$cantidad_detalle=$_POST['cantidad_detalle'.$control];
$cantidad_detalle = quitarseparadormiles($cantidad_detalle);

$precio_producto=$_POST['precio_producto'.$control];
$precio_producto = quitarseparadormiles($precio_producto);

$subtotal=$_POST['subtotal'.$control];
$subtotal = quitarseparadormiles($subtotal);

$comision=$_POST['comision'.$control];
$comision = quitarseparadormiles($comision);

$descuento=$_POST['descuento'.$control];
$descuento = quitarseparadormiles($descuento);

$detalleproducto=$_POST['detalleproducto'.$control];
$detalleproducto = utf8_decode($detalleproducto);
	
$subPrecioCompra=obtenerCostoProducto($cod_productoFK);	
	
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);


$cod_aperturaCajaFK=$_POST['cod_aperturaCajaFK'];
$cod_aperturaCajaFK = utf8_decode($cod_aperturaCajaFK);


if($cod_productoFK!="10001"){
	$cod_aperturaCajaFK="0";
}


if($cantidad_detalle!="" || $cod_productoFK!="" || $cod_ventaFK!=""  ){

$consulta1="Insert into detalle_venta (cantidad_detalle,descuento,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado,comision,detalleproducto,cod_aperturaCajaFK)
values(?,?,?,?,?,?,?,'Activo',?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cantidad_detalle,$descuento,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$comision,$detalleproducto,$cod_aperturaCajaFK);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


editar_cantidad($cod_productoFK,$cantidad_detalle,"resta",$cod_local);
$control=$control+1;
}	
	
}




$subtotal=obtenerTotal($cod_ventaFK);
actualizarTotal($cod_ventaFK,$subtotal);

$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'),"3" => $cod_ventaFK,"4" => $num_factura);
echo json_encode($informacion);	
exit;
	
}

function cambiar($cod_detalle,$cod_ventaFK,$cantidaCambio,$CodProductoCambio,$metodopago,$cod_usuarioFK,$Local_FK)
{
	
	
if($cod_detalle=="" || $cod_ventaFK==""  ){
$inforOacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

editar_cantidad($CodProductoCambio,$cantidaCambio,"suma",$Local_FK);

$consulta1="delete from detalle_venta where cod_detalle=? ";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$cod_detalle);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$consulta1="Insert into cambiarproducto (fecha,cod_productoFK,cod_ventaFK,cod_usuarioFK)
values(Current_Date,?,?,?)";

$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$CodProductoCambio,$cod_ventaFK,$cod_usuarioFK);


if (!$stmt1->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

$control=1;	
$totalRegistro=$_POST['TotalRegistro'];
$totalRegistro = utf8_decode($totalRegistro);

$motivo='Cambio';

while($control<=$totalRegistro){
	
	
$cod_productoFK=$_POST['cod_productoFK'.$control];
$cod_productoFK = utf8_decode($cod_productoFK);

$cantidad_detalle=$_POST['cantidad_detalle'.$control];
$cantidad_detalle = quitarseparadormiles($cantidad_detalle);

$precio_producto=$_POST['precio_producto'.$control];
$precio_producto = quitarseparadormiles($precio_producto);

$subtotal=$_POST['subtotal'.$control];
$subtotal = quitarseparadormiles($subtotal);

$comision=$_POST['comision'.$control];
$comision = quitarseparadormiles($comision);

$descuento=$_POST['descuento'.$control];
$descuento = quitarseparadormiles($descuento);

$detalleproducto=$_POST['detalleproducto'.$control];
$detalleproducto = utf8_decode($detalleproducto);
	
$subPrecioCompra=obtenerCostoProducto($cod_productoFK);	
	

	
if($cantidad_detalle!="" || $cod_productoFK!="" || $cod_ventaFK!=""  ){

$consulta1="Insert into detalle_venta (cantidad_detalle,descuento,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado,comision,detalleproducto)
values(?,?,?,?,?,?,?,'Activo',?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssss';
$stmt1->bind_param($ss,$cantidad_detalle,$descuento,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$comision,$detalleproducto);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


editar_cantidad($cod_productoFK,$cantidad_detalle,"resta",$Local_FK);


$consulta1="Insert into detallescambio (cant,cod_productoFK,idcambiarproductoFK)
values(?,?,(select idcambiarproducto from cambiarproducto where cod_ventaFK='$cod_ventaFK' order by  idcambiarproducto desc limit 1 ))";

$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$cantidad_detalle,$cod_productoFK);


if (!$stmt1->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}



}	
	
$control=$control+1;
	
	
}

$subtotal=obtenerTotal($cod_ventaFK);
 actualizarTotal($cod_ventaFK,$subtotal);
 refinanciarencambio($cod_ventaFK,$subtotal,$metodopago);
$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'));
echo json_encode($informacion);	
exit;
	
}


function quitarDevolucion($cod_detalle,$cod_productoFK,$cod_ventaFK,$motivo,$cantidaCambio,$Local_FK)
{
	
	
	
if($cod_detalle=="" || $cod_productoFK=="" || $cod_ventaFK==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}
$motivo="Devolucion";

$mysqli=conectar_al_servidor(); 



// $consulta1="delete from detalle_venta where cod_detalle=? ";
// $stmt1 = $mysqli->prepare($consulta1);
// $ss='s';
// $stmt1->bind_param($ss,$cod_detalle);
// if (!$stmt1->execute()) {
	
// echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
// exit;

// }


$consulta1="Insert into cambios (motivo,fecha,cant,cod_producto,cod_venta,coddetalleventa)
values('$motivo',Current_Date,'$cantidaCambio','$cod_productoFK','$cod_ventaFK','$cod_detalle')";


$stmt1 = $mysqli->prepare($consulta1);


if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

editar_cantidad($cod_productoFK,$cantidaCambio,"suma",$Local_FK);

$subtotal=obtenerTotal($cod_ventaFK);


$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'));
echo json_encode($informacion);	
exit;
}


function quitardegarantia($cod_detalle)
{
	
	
	
if($cod_detalle==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor();
$consulta1="update detalle_venta set estado='Activo' where cod_detalle=? ";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$cod_detalle);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}



$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function usodegarantia($telefonoaviso,$observacion,$fecharecibido,$cod_detalle,$cod_productoFK,$cod_ventaFK,$cod_usuarioFK,$operacion)
{
	
	
	
if($cod_detalle=="" || $cod_productoFK=="" || $cod_ventaFK==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}
$motivo="Devolucion";

$mysqli=conectar_al_servidor(); 



// $consulta1="update detalle_venta set estado='Garantia' where cod_detalle=? ";
// $stmt1 = $mysqli->prepare($consulta1);
// $ss='s';
// $stmt1->bind_param($ss,$cod_detalle);

// if (!$stmt1->execute()) {
// echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
// exit;

// }






$consulta1="Insert into garantias (fecharecibido,observacion,estado,cod_productoFK,cod_ventaFK,cod_usuarioFKRecibido,cod_detalle_venta_fk,telefonoaviso)
values('$fecharecibido','$observacion','Pendiente a verificar','$cod_productoFK','$cod_ventaFK','$cod_usuarioFK','$cod_detalle','$telefonoaviso')";

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}

function editarusogarantia($idgarantia,$fecha,$estado,$codUsuarioFk)
{
	
	
	
if($idgarantia=="" || $fecha=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}
$motivo="Devolucion";

$mysqli=conectar_al_servidor(); 

if($estado=="verificacion"){
	$consulta1="update  garantias set estado='verificacion',fechaenvio='$fecha',cod_usuarioFkEnvio='$codUsuarioFk' where idgarantia='$idgarantia' ";
}
if($estado=="listo"){
	$consulta1="update  garantias set estado='listo',fechadevuelto='$fecha',cod_usuarioFkDevuelto='$codUsuarioFk' where idgarantia='$idgarantia' ";
}
if($estado=="entregado"){
	$consulta1="update  garantias set estado='entregado',fechaentrega='$fecha',cod_usuarioFkEntrega='$codUsuarioFk' where idgarantia='$idgarantia' ";
}



$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function quitarproducto($cod_detalle,$cod_ventaFK,$cantida,$codProducto,$operacion,$motivo,$Local_FK)
{
	
	
if($cod_detalle=="" ||  $cod_ventaFK==""  ){
$inforOacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();

/*AUDITORIA*/
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$fecha = date('Y-m-d', time()); 
$user=$_POST['useru'];
$user = utf8_decode($user);

$consulta="Insert into detallesventaeliminado (cod_producto,motivo,fecha,cod_user_insert,fecha_insert)
values(?,?,?,?,?)";
$stmt = $mysqli->prepare($consulta);
$ss='sssss';
$stmt->bind_param($ss,$codProducto,$motivo,$fecha,$user,$fecha_inser_edit);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}


$consulta1="delete from detalle_venta where cod_detalle=? ";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$cod_detalle);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
if($operacion=="1"){
editar_cantidad($codProducto,$cantida,"suma",$Local_FK);
}
$subtotal=obtenerTotal($cod_ventaFK);
 eliminarestecreditos($cod_ventaFK);	
actualizarTotal($cod_ventaFK,$subtotal); 
 


$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'),"3" => $cod_ventaFK);
echo json_encode($informacion);	
exit;
	
}


function eliminarestecreditos($cod_venta){
		$mysqli=conectar_al_servidor();
			$consulta="delete from credito where  cod_venta='$cod_venta' ";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);
}


function editar_cantidad($idproductos,$cantidad,$t,$cod_localfk){
      
	  $mysqli=conectar_al_servidor(); 

	    if($t=="resta"){
			$consulta="Update stocklocales set cantidad=(cantidad-$cantidad)  where cod_productofk='".$idproductos."' and cod_localfk='".$cod_localfk."'";
		
				

	}else{
		 $consulta="Update stocklocales set cantidad=(cantidad+$cantidad)  where cod_productofk='".$idproductos."' and cod_localfk='".$cod_localfk."'";
          
			

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
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set total_venta=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$total,$cod_venta); 

if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


}




function EditarSolicitud($idAbm,$detalleVenta,$cod_usu)
{

$mysqli=conectar_al_servidor(); 


$consulta1="Update solicitudcredito set  estado='FINALIZADO' , detalleVenta='$detalleVenta'   where idSolicitudCredito=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss ,$idAbm);

if (!$stmt1->execute()) {	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);

}




function iniciarVenta($codSolicitudCreditoFK,$puntoexpedicion,$tipo_comprobante,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comisioncobrador,$cod_local,$idGaranteFk){
	
	$mysqli=conectar_al_servidor(); 
	
	if($num_factura==""){
	if($tipo_comprobante=="FACTURA"){
	$datos=buscarcodNroFactura($cod_local,$puntoexpedicion);
	$num_factura=buscarnrofactura($datos[0],$datos[1]);
	$codnrofactura=$datos[0];
	}else{
		$num_factura=buscarnroventab();
		$puntoexpedicion="";
	   $codnrofactura="";
	}
	}
		/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);


	$consulta1="Insert into venta (idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2,comision,cod_local,tipo_comprobante,puntoexpedicion,codnrofactura,cod_user_insert,fecha_insert,codSolicitudCreditoFK)
values($idGaranteFk,'$fecha_venta','0',$cod_usuarioFK,$cod_clienteFK,'$num_factura',$cod_cobradorFK,'$TipoVenta','$TipoPago','$vendedor1','$vendedor2','$comisioncobrador',$cod_local,'$tipo_comprobante','$puntoexpedicion','$codnrofactura','$user','$fecha_inser_edit','$codSolicitudCreditoFK')";

// echo($consulta1);
// exit;
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

if($codSolicitudCreditoFK!="" || $codSolicitudCreditoFK!="0" ){
	$detalleVenta=$puntoexpedicion.'-'.$num_factura;
	EditarSolicitud($codSolicitudCreditoFK,$detalleVenta,$cod_usuarioFK);
}
   $datos[0]=obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura);
   $datos[1]=$num_factura;
   return $datos;
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

$sql= "select dtv.descripcion , pr.cod_producto,pr.cod_barra,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,vt.num_factura,vt.puntoexpedicion,concat(vt.puntoexpedicion,'-',vt.num_factura) as fac,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select concat(direccion,'-',email) from persona where  cod_persona=cod_clienteFK) as zonaCliente,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as clientetelefono,
(Select concat(direccion,'-',email) from persona where  cod_persona=vt.idGaranteFk) as zonaGarante,
(Select telefono from persona where cod_persona=vt.idGaranteFk) as Garantetelefono,
(Select ci_cliente from cliente where cod_cliente=vt.idGaranteFk) as nrodocgarante,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
(Select rut_cliente from cliente where cod_cliente=vt.cod_clienteFK) as ruccliente,vt.TipoVenta,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,dtv.descuento,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";

// echo($sql);
// exit;
$clientenombre = ""; 
$clientedireccion = ""; 
$clientetelefono = ""; 
$nrodocliente = ""; 
$nrodocgarante = ""; 
$zonaCliente = ""; 
$Garantetelefono = ""; 
$zonaGarante = ""; 
$TipoVenta = ""; 

$pagina = "";   
$paginarecibo = "";      
$ruccliente = "";      
$paginatickect = "";      
$totalventa = "0";   
$totalpagado = "0";   
$nroFactura = "0";   
$nroVenta = "0";   
$nroCouta = "1";   
$fac="";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$SubTotalestotalIva10=0;
$SubTotalestotalIva5=0;
$totalIvaEx=0;
$totalDescuentoDetalles=0;
$totales10=0;
$totales5=0;
$totalesExt=0;
$totalesiva=0;
$plazo=1;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$descripcion = utf8_encode($valor['descripcion']); 
$TipoVenta = utf8_encode($valor['TipoVenta']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
$cod_producto = utf8_encode($valor['cod_producto']); 
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
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$impuesto = utf8_encode($valor['impuesto']); 
$clientenombre = utf8_encode($valor['clientenombre']); 
$clientedireccion = utf8_encode($valor['clientedireccion']); 
$clientetelefono = utf8_encode($valor['clientetelefono']); 
$nrodocliente = utf8_encode($valor['nrodocliente']); 
$plazo = utf8_encode($valor['plazo']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$num_factura = utf8_encode($valor['num_factura']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']); 
$Garantetelefono = utf8_encode($valor['Garantetelefono']); 
$zonaGarante = utf8_encode($valor['zonaGarante']); 
$zonaCliente = utf8_encode($valor['zonaCliente']); 
$ruccliente = utf8_encode($valor['ruccliente']); 
$nroCouta = utf8_encode($valor['nroCouta']); 
$descuento = utf8_encode($valor['descuento']); 
$nrodocgarante = utf8_encode($valor['nrodocgarante']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$fac = utf8_encode($valor['fac']); 

$totalDescuentoDetalles = $totalDescuentoDetalles+$descuento; 

if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}

$iva10porciento=0;
$iva5porciento=0;
$ivaexcentas=0;
$subtotalIva5=0;
$subtotalIva10=0;
$subtotalIvaext=0;
if($impuesto==11){
	$iva10porciento= $subtotal;
$subtotalIva10=($subtotal/$impuesto);
$totalesiva=$totalesiva+$subtotalIva10;
$totales10=$totales10+$subtotalIva10;
$SubTotalestotalIva10=$SubTotalestotalIva10+$subtotal;

}
if($impuesto==21){
	$iva5porciento= $subtotal;
$subtotalIva5=($subtotal/$impuesto);
$totalesiva=$totalesiva+$subtotalIva5;
$totales5=$totales5+$subtotalIva5;
$SubTotalestotalIva5=$SubTotalestotalIva5+$subtotal;
}
if($impuesto==1){
	$ivaexcentas= $subtotal;
$subtotalIvaext=$subtotal;
$totalesExt=$totalesExt+$subtotalIvaext;
}


$styleG=""; 
$styleDetalle=""; 
if($totalpagado>0){
	$eventos="";
}else{
	$eventos="obtenerdatosabmdetalleventa(this)";
}



	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='$eventos' class='$styleDetalle'  name='tdDetalleVenta'>
<td id='td_id_1' style='display:none'>".$cod_producto."</td>
<td id='td_id_2' style='display:none'>".$cod_detalle."</td>
<td  style='width:5%'>".$cod_barra."</td>
<td  id='td_datos_1' style='width:20%;".$styleG."'>".$nombre_producto." *".$NombreMarca."*</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_4' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_5' style='width:10%;display:none'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
<td  id='td_datos_6' style='display:none'>".number_format($comision,'0',',','.')."</td>
</tr>
</table>";


$descripcionDetalleVenta=buscardescripcionDetalleVenta($cod_detalle);


$paginarecibo.="
<table class='tableReporRecibo' >
<tr >
<td  style='width:35px'>".$cod_barra."</td>
<td  style='width:35px;text-aling:center'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  style='width:355px'>$nombre_producto * $NombreMarca * $descripcion <br> $descripcionDetalleVenta</td>
<td  style='width:75px'>".number_format($precio_producto,'0',',','.') ."</td>
<td  style='width:50px;text-aling:center'>".number_format($ivaexcentas,'0',',','.') ."</td>
<td  style='width:50px;text-aling:center'>".number_format($iva5porciento,'0',',','.') ."</td>
<td  style='width:65px;text-aling:center'>".number_format($iva10porciento,'0',',','.') ."</td>
</tr>
</table>";

// $paginatickect.="<table class='tableTicket'>
// <tr>
// <td style='width:10%'>".number_format($cantidad_detalle,'0',',','.')."</td>
// <td style='width:50%'>".$nombre_producto." *".$NombreMarca."*</td>

// <td style='width:15%'>".number_format($precio_producto,'0',',','.')."</td>
// <td style='width:25%'>".number_format($subtotal,'0',',','.')."</td>
// </tr>
// </table>";
}
}

$paginatickect="Factura nro: ".$nroFactura;

$datos=buscardatoscuenta($buscar); 
$idcredito=$datos[0];    
$plazo=$datos[1];  
$fechapago=$datos[2];          
$cod_venta=$datos[3];    
$Monto=$datos[4]; 
$totalPago=$datos[5]; 
$Esado=$datos[6] ;          
$Nro_recibo=$datos[7] ;
$TipoPago=$datos[8];
$nroCuota=$datos[9];
$dias=$datos[10];
$interes=$datos[12] ;
$entrega=$datos[13] ;
$controlMonto=$datos[14] ;
$ultimafechapago=$datos[15] ;

$datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
$totalEnDescuento=$datos[0];
$totalInteres=$datos[12];
$deuda=$datos[4];
$diasatrasado=$datos[5];
$acobrar=$datos[8];
$totalCredito=$datos[11];
$totalDescuentosAplicado=$totalDescuentoDetalles+$totalEnDescuento;
if($totalCredito>0){
	$Subttotalventa=$totalCredito+$totalDescuentoDetalles;
	$totalventa=$totalCredito-$totalEnDescuento;
}else{
	$Subttotalventa=$totalventa+$totalDescuentoDetalles;
	$totalventa=$totalventa-$totalEnDescuento;
}

$plazoPago = buscarpagosTitulo($cod_ventaFK);

$cuotas=buscarcantidadcuotapagados($cod_venta)."/".$nroCouta;
$informacion =array("1" => "exito","2" => $pagina,"5" => $paginarecibo,"14" => $paginatickect,"3" => number_format($totalventa,'0',',','.'),"4" => number_format($totalpagado,'0',',','.')
,"6" => number_format($SubTotalestotalIva5,'0',',','.'),"7" => number_format($SubTotalestotalIva10,'0',',','.'),"8" => number_format($totales10,'0',',','.')
,"9" => number_format($totales5,'0',',','.'),"10" =>$clientenombre ,"11" => $clientedireccion ,"12" => $clientetelefono ,"13" => $nrodocliente
,"15" => $plazo ,"16" => $fechapago ,"23" => number_format($Monto,'0',',','.')  ,"18" => $Nro_recibo ,"19" => $nroCuota ,"20" =>$dias ,"21" => number_format($interes,'2',',','.')  ,"22" => $TipoPago ,"17" =>number_format($entrega,'0',',','.')
,"24"=>$controlMonto,
"25"=>$fac,
"26"=>$ultimafechapago,
"27"=>$zonaCliente,
"28"=>$Garantetelefono,
"29"=>$zonaGarante,
"30"=>number_format($totalInteres,'0',',','.'),
"31"=>number_format($deuda,'0',',','.'),
"32"=>$diasatrasado,
"33"=>$ruccliente,
"34"=> number_format($totalEnDescuento,'0',',','.'),
"35"=>$cuotas,
"36"=>number_format($totalesiva,'0',',','.'),
"37"=>number_format($totalDescuentosAplicado,'0',',','.'),
"38"=>number_format($Subttotalventa,'0',',','.'),
"39"=>$nrodocgarante,"40"=>$TipoVenta ,"41"=>$plazoPago[2] );
echo json_encode($informacion);	
exit;
}



function buscarpagosTitulo($CodVenta)
{
$mysqli=conectar_al_servidor();


$sql= "select cr.fechapago,cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,vt.TipoVenta,vt.total_venta
 from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk
 inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where pg.cod_venta_fk='$CodVenta'  order by pg.idPago  ";
 
 // echo($sql);
 // exit;
 
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$pagina2 = ""; 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$totalPagado=0;
$datos[0]="";
$datos[1]="";
$datos[2]="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
     
$plazo = utf8_encode($valor['plazo']);      
$tipo = utf8_encode($valor['tipo']);  
$Monto = utf8_encode($valor['Monto']);  
$Fecha = utf8_encode($valor['Fecha']); 
$fechapago = utf8_encode($valor['fechapago']);  

if($plazo=="Contado"){
	$tipo="";
}


if($tipo=="Interes"){
	$tipo="INTERES";
}
if($tipo=="Pago Cuota"){
	$tipo="PAGO DE CUOTA";
}

$totalPagado=$Monto+$totalPagado;
$pagina.="<table style='font-family: arial;font-size: 11px;' >
<tr>
<td style='width:10%'>".$plazo."</td>
<td style='width:50%'>".$tipo."</td>
<td style='width:40%'>".number_format($Monto,'0',',','.')."</td>
</tr>
</table>";

$pagina2.="<table class='tableTicket' style='border: solid 1px #a1a1a1;'>
<tr>
<td style='width:20%'>".$Fecha."</td>
<td style='width:20%'>".$fechapago."</td>
<td style='width:40%'>".$tipo."--".$plazo."</td>
<td style='width:20%'>".number_format($Monto,'0',',','.')."</td>
</tr>
</table>";

}

$pagina2.="<table class='tableTicket' style='border: solid 1px #a1a1a1;'>
<tr>
<td style='width:70%'></td>
<td style='width:30%'>TOTAL : ".number_format($totalPagado,'0',',','.')." Gs.</td>
</tr>
</table>";


}
$datos[0]=$pagina;
$datos[1]=$totalPagado;
$datos[2]=$pagina2;
return $datos;	

}




function buscardescripcionDetalleVenta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select nombre
 from  descripcionventa 
 where cod_detalleFK='$buscar' ";
 

$pagina="";
 
$cuotas = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlVentas="";



if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$nombre = utf8_encode($valor['nombre']);
$pagina.="<p style='font-size: 9px;'>$nombre</p>";

}
}
 mysqli_close($mysqli); 
return $pagina;

}




function buscarcantidadcuotapagados($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select count(vt.num_factura) as cuotas
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar'
 and  ((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))<=0
 and plazo!='ENTREGA'";
 


 
$cuotas = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlVentas="";



if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$cuotas = utf8_encode($valor['cuotas']);

}
}
 mysqli_close($mysqli); 
return $cuotas;

}

function  productosCompradoscliente($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,vt.num_factura,vt.puntoexpedicion,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.cod_clienteFK limit 1) limit 1) as zonaCliente,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as clientetelefono,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.idGaranteFk limit 1) limit 1) as zonaGarante,
(Select telefono from persona where cod_persona=vt.idGaranteFk) as Garantetelefono,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
(Select rut_cliente from cliente where cod_cliente=vt.cod_clienteFK) as ruccliente,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";
$clientenombre = ""; 
$clientedireccion = ""; 
$clientetelefono = ""; 
$nrodocliente = ""; 
$zonaCliente = ""; 
$Garantetelefono = ""; 
$zonaGarante = ""; 

$pagina = "";   
$paginarecibo = "";      
$ruccliente = "";      
$paginatickect = "";      
$totalventa = "0";   
$totalpagado = "0";   
$nroFactura = "0";   
$nroVenta = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$SubTotalestotalIva10=0;
$SubTotalestotalIva5=0;
$totalIvaEx=0;

$totales10=0;
$totales5=0;
$totalesExt=0;
$plazo=1;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']); 
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
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$impuesto = utf8_encode($valor['impuesto']); 
$clientenombre = utf8_encode($valor['clientenombre']); 
$clientedireccion = utf8_encode($valor['clientedireccion']); 
$clientetelefono = utf8_encode($valor['clientetelefono']); 
$nrodocliente = utf8_encode($valor['nrodocliente']); 
$plazo = utf8_encode($valor['plazo']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$num_factura = utf8_encode($valor['num_factura']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']); 
$Garantetelefono = utf8_encode($valor['Garantetelefono']); 
$zonaGarante = utf8_encode($valor['zonaGarante']); 
$zonaCliente = utf8_encode($valor['zonaCliente']); 
$ruccliente = utf8_encode($valor['ruccliente']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 

if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}

$subtotalIva5=0;
$subtotalIva10=0;
$subtotalIvaext=0;
if($impuesto==11){
$subtotalIva10=($subtotal*($impuesto/100));
$totales10=$totales10+$subtotalIva10;
$subtotalIva10=$subtotal;
$SubTotalestotalIva10=$SubTotalestotalIva10+$subtotalIva10;
}
if($impuesto==21){
$subtotalIva5=($subtotal*($impuesto/100));
$totales5=$totales5+$subtotalIva5;
$subtotalIva5=$subtotal;
$SubTotalestotalIva5=$SubTotalestotalIva5+$subtotalIva5;

}
if($impuesto==1){
$subtotalIvaext=$subtotal;
$totalesExt=$totalesExt+$subtotalIvaext;
}


$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventa(this)";


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  class='$styleDetalle'  name='tdDetalleVenta'>
<td id='td_id_1' style='display:none'>".$cod_producto."</td>
<td id='td_id_2' style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_1' style='width:20%;".$styleG."'>".$nombre_producto." *".$NombreMarca."* </td>
<td  id='td_datos_4' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";

}
}

$datocuenta=calcularintereses2($buscar,0,0,"2","2","2","no");
$totalInteres=$datocuenta[12];
$totalPagado=$datocuenta[3];
$acobrar=$datocuenta[4];
$deuda=$datocuenta[4];
$porinteres=$datocuenta[14];

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalventa,'0',',','.'),"4" => number_format($totalPagado,'0',',','.') ,"5" =>   number_format($acobrar,'0',',','.'),"6" => $nroFactura );
echo json_encode($informacion);	
exit;
}

function  productosCompradosclienteInactivo($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select vt.fecha_venta,pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,vt.num_factura,vt.puntoexpedicion,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.cod_clienteFK limit 1) limit 1) as zonaCliente,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as clientetelefono,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.idGaranteFk limit 1) limit 1) as zonaGarante,
(Select telefono from persona where cod_persona=vt.idGaranteFk) as Garantetelefono,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
(Select rut_cliente from cliente where cod_cliente=vt.cod_clienteFK) as ruccliente,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where vt.cod_clienteFK='$buscar' order by fecha_venta desc";
$pagina = "";    
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']); 
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
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']);  
$impuesto = utf8_encode($valor['impuesto']); 
$clientenombre = utf8_encode($valor['clientenombre']); 
$clientedireccion = utf8_encode($valor['clientedireccion']); 
$clientetelefono = utf8_encode($valor['clientetelefono']); 
$nrodocliente = utf8_encode($valor['nrodocliente']); 
$plazo = utf8_encode($valor['plazo']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$num_factura = utf8_encode($valor['num_factura']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']); 
$Garantetelefono = utf8_encode($valor['Garantetelefono']); 
$zonaGarante = utf8_encode($valor['zonaGarante']); 
$zonaCliente = utf8_encode($valor['zonaCliente']); 
$ruccliente = utf8_encode($valor['ruccliente']); 
$fecha_venta = utf8_encode($valor['fecha_venta']); 

if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}



$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventa(this)";


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  class='$styleDetalle'  name='tdDetalleVenta'>
<td  id='td_datos_1' style='width:70%;".$styleG."'>".$nombre_producto."</td>
<td  id='td_datos_1' style='width:30%;'>".$fecha_venta."</td>
</tr>
</table>";

}
}


$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}

function  BuscarRegistroEnHistorilaVenta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,dtv.estado,detalleproducto,dtv.descuento,dtv.comision,vt.cod_venta,vt.TipoPago,vt.num_factura,vt.puntoexpedicion,vt.fecha_venta,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select count(idgarantia) from garantias gt where gt.cod_detalle_venta_fk=dtv.cod_detalle and (gt.estado='Pendiente a verificar' or gt.estado='verificacion') limit 1),0) as nroGarantia,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";
$pagina="";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalIva10=0;
$totalIva5=0;
$totalIvaEx=0;

$totales10=0;
$totales5=0;
$totalesExt=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']); 
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$cod_detalle = utf8_encode($valor['cod_detalle']);          
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']); 
$cod_productoFK = utf8_encode($valor['cod_productoFK']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$subPrecioCompra = utf8_encode($valor['subPrecioCompra']); 
$subtotal = utf8_encode($valor['subtotal']); 
$estado = utf8_encode($valor['estado']); 
$nroGarantia = utf8_encode($valor['nroGarantia']); 
$impuesto = utf8_encode($valor['impuesto']); 
$descuento = utf8_encode($valor['descuento']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$comision = utf8_encode($valor['comision']); 
$cod_venta = utf8_encode($valor['cod_venta']); 
$TipoPago = utf8_encode($valor['TipoPago']); 
 $num_factura=utf8_encode($valor['num_factura']);
$puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
$NombreMarca=utf8_encode($valor['NombreMarca']);
$fecha_venta=utf8_encode($valor['fecha_venta']);
$telefono=utf8_encode($valor['telefono']);
		  	    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

$subtotalIva5=0;
$subtotalIva10=0;
$subtotalIvaext=0;
if($impuesto==11){
$subtotalIva10=($subtotal*$impuesto)/100;
$subtotalIva10=$subtotal-$subtotalIva10;
$totalIva10=$totalIva10+$subtotalIva10;
$subtotalIva10=$subtotal;
$totales10=$totales10+$subtotalIva10;
}
if($impuesto==21){
$subtotalIva5=($subtotal*$impuesto)/100;
$subtotalIva5=$subtotal-$subtotalIva5;	
$totalIva5=$totalIva5+$subtotalIva5;
$subtotalIva5=$subtotal;
$totales5=$totales5+$subtotalIva5;
}
if($impuesto==1){
$subtotalIvaext=$subtotal;
$totalesExt=$totalesExt+$subtotalIvaext;
}


$styleG=""; 
$styleDetalle=""; 
$tituloext=""; 
$eventos="obtenerdatosabmdetalleventaDevoluciones(this)";
if($nroGarantia>0){
	$eventos="";
	$tituloext=" <BR> <b><i>(PROCESO DE GARANTIA)<i><b>";
}


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle'  name='tdDetalleVenta' onclick='$eventos' >
<td  id='td_datos_1' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_2' style='display:none'>".$nombre_producto." *".$NombreMarca."*</td>
<td   style='width:20%;".$styleG."'>".$nombre_producto." *".$NombreMarca."*".$tituloext."</td>
<td  id='td_datos_3' style='display:none'>".$detalleproducto."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_10' style='display:none'>".$cod_venta."</td>
<td  id='td_datos_11' style='display:none'>".$TipoPago."</td>
<td  id='td_datos_12' style='display:none'>".$nrof."</td>
<td  id='td_datos_13' style='display:none'>".$fecha_venta."</td>
<td  id='td_datos_14' style='display:none'>".$telefono."</td>
</tr>
</table>";




}
}


$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}


function buscardatoscuenta($buscar)
{
	

$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,vt.TipoPago,dias,cr.descuento,cr.interes,cr.tipo,
(select fechapago from credito cr where cr.cod_venta='$buscar' order by  fechapago desc limit 1) as ultimaFechaPago
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' order by  fechapago ";
 
$datos;
$idcredito = "";    
$plazo = "";  
$fechapago = "";          
$cod_venta ="";          
$MontoControl = "0"; 
$controlMonto = 0; 
$Monto = "0"; 
$totalPago = "0"; 
$Esado = "";          
$Nro_recibo = "";
$TipoPago ="";
$nroCuota ="0";
$dias ="10";
$interes ="0.10";
$descuento ="0";
$entrega ="0";
$ultimaFechaPago ="0";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$nroCuotas=0;
$controlStyle="";
if ($valor>0)
{
	$nroCuota=0;
while ($valor= mysqli_fetch_assoc($result))
{  

$tipo = utf8_encode($valor['tipo']); 
$ultimaFechaPago = utf8_encode($valor['ultimaFechaPago']);   
if($tipo!="ENTREGA"){
	$nroCuota++;
if($fechapago==""){
    
$idcredito = utf8_encode($valor['idcredito']);     
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);      
   
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$TipoPago = utf8_encode($valor['TipoPago']);
$dias = utf8_encode($valor['dias']);
$descuento = utf8_encode($valor['descuento']);
$interes = utf8_encode($valor['interes']);
}
$nm = utf8_encode($valor['Monto']); 

if($MontoControl!=$nm){
	$MontoControl=$nm;
	$controlMonto=$controlMonto+1;
}
 
}else{
	$entrega = utf8_encode($valor['Monto']);
}

$nroCuotas=$nroCuotas+1;
}
}

 mysqli_close($mysqli);
$datos[0]=$idcredito;    
$datos[1]=$nroCuotas;  
$datos[2]=$fechapago;          
$datos[3]=$cod_venta;          
$datos[4]=$Monto; 
$datos[5]=$totalPago ; 
$datos[6]=$Esado ;          
$datos[7]=$Nro_recibo ;
$datos[8]=$TipoPago;
$datos[9]=$nroCuota;
$datos[10]=$dias;
$datos[11]=$descuento ;
$datos[12]=$interes ;
$datos[13]=$entrega ;
$datos[14]=$controlMonto ;
$datos[15]=$ultimaFechaPago ;
return $datos;


}

function  buscarproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$agrupacionproductovendidoinforme)
{
$mysqli=conectar_al_servidor();
	 $condicionfecha="and vt.fecha_venta>='".$fecha1."' and vt.fecha_venta<='".$fecha2."'";
		 if($fecha1=="" && $fecha2==""){
			$condicionfecha=" "; 
		 }
		 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		 $condicionCategoria=" and pr.cod_categoriaFK='$categoria' ";
		 if($categoria==""){
			$condicionCategoria=""; 
		 }
		 $condicionMarca=" and pr.cod_marcasFK='$marca' ";
		 if($marca==""){
			$condicionMarca=""; 
		 }
		 $condicioncodigo=" and pr.cod_barra='$codigo' ";
		 if($codigo==""){
			$condicioncodigo=""; 
		 }
		 $condicionproducto="and concat(pr.nombre_producto,' ',pr.cod_producto) like '%".$producto."%' ";
		 if($producto==""){
			$condicionproducto=""; 
		 }
    
	$condiciongroupby="";
	if($agrupacionproductovendidoinforme=="1"){
		$condiciongroupby=" group by pr.cod_producto ";
	}
	if($agrupacionproductovendidoinforme=="2"){
		$condiciongroupby= " group by dtv.cod_detalle  ";
	}
		
$sql= "select pr.cod_barra,pr.nombre_producto,concat(puntoexpedicion,'-',num_factura) as nroventa,
sum(dtv.cantidad_detalle) as totalCantidad,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
sum(dtv.subtotal) as totalVenta,
sum(dtv.cantidad_detalle*dtv.subPrecioCompra) as totalCosto,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where  IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
 ".$condicionfecha.$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicioncodigo.$condicionproducto.$condiciongroupby." 
 order by totalCantidad desc limit 50";



$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$totalventas = "0";   
$totalinvertido = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_barra']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$totalCantidad = utf8_encode($valor['totalCantidad']);          
$totalVenta = utf8_encode($valor['totalVenta']); 
$nombrelocal = utf8_encode($valor['nombrelocal']); 
$totalCosto = utf8_encode($valor['totalCosto']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$nroventa = utf8_encode($valor['nroventa']); 

$totalventas=$totalVenta+$totalventas;
$totalinvertido=$totalinvertido+$totalCosto;
$nroventas="";
if($agrupacionproductovendidoinforme=="2"){
		$nroventas="<br><b><i>".$nroventa."</i></b>";
	}


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'   >
<td id='' style='width:10%'>".$cod_producto."</td>
<td id='' style='width:20%'>".$nombre_producto.$nroventas."</td>
<td id='' style='width:10%'>".$NombreMarca."</td>
<td id='' style='width:10%'>".$NombreCategoria."</td>
<td  id='' style='width:10%'>".number_format($totalCantidad,'2',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($totalVenta,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


}
}

		
$sql= "select pr.cod_barra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where  IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
 ".$condicionfecha.$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicioncodigo.$condicionproducto.$condiciongroupby; 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregisto=$valor;

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5" => number_format($totalinvertido,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregisto);
echo json_encode($informacion);	
exit;
}

function  buscarmasproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$totalventa,$totalinvertido,$registrocargado,$agrupacionproductovendidoinforme)
{
$mysqli=conectar_al_servidor();
	 $condicionfecha="and vt.fecha_venta>='".$fecha1."' and vt.fecha_venta<='".$fecha2."'";
		 if($fecha1=="" && $fecha2==""){
			$condicionfecha=" "; 
		 }
		 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		 $condicionCategoria=" and pr.cod_categoriaFK='$categoria' ";
		 if($categoria==""){
			$condicionCategoria=""; 
		 }
		 $condicionMarca=" and pr.cod_marcasFK='$marca' ";
		 if($marca==""){
			$condicionMarca=""; 
		 }
		 $condicioncodigo=" and pr.cod_barra='$codigo' ";
		 if($codigo==""){
			$condicioncodigo=""; 
		 }
		 $condicionproducto="and concat(pr.nombre_producto,' ',pr.cod_producto) like '%".$producto."%' ";
		 if($producto==""){
			$condicionproducto=""; 
		 }

$condiciongroupby="";
	if($agrupacionproductovendidoinforme=="1"){
		$condiciongroupby=" group by pr.cod_producto ";
	}
	if($agrupacionproductovendidoinforme=="2"){
		$condiciongroupby= " group by dtv.cod_detalle  ";
	}
		
$sql= "select pr.cod_barra,pr.nombre_producto,concat(puntoexpedicion,'-',num_factura) as nroventa,
sum(dtv.cantidad_detalle) as totalCantidad,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
sum(dtv.subtotal) as totalVenta,
sum(dtv.cantidad_detalle*dtv.subPrecioCompra) as totalCosto,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where  IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
 ".$condicionfecha.$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicioncodigo.$condicionproducto.$condiciongroupby." order by totalCantidad desc limit ".$registrocargado.", 50 ";
 



$pagina = "";   

$totalpagado = "0";   
$totalventas = $totalventa;   
$totalinvertido = $totalinvertido;   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_barra']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$totalCantidad = utf8_encode($valor['totalCantidad']);          
$totalVenta = utf8_encode($valor['totalVenta']); 
$nombrelocal = utf8_encode($valor['nombrelocal']); 
$totalCosto = utf8_encode($valor['totalCosto']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$nroventa = utf8_encode($valor['nroventa']); 

$totalventas=$totalVenta+$totalventas;
$totalinvertido=$totalinvertido+$totalCosto;
$nroventas="";
if($agrupacionproductovendidoinforme=="2"){
		$nroventas="<br><b><i>".$nroventa."</i></b>";
	}
	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'   >
<td id='' style='width:10%'>".$cod_producto."</td>
<td id='' style='width:20%'>".$nombre_producto.$nroventas."</td>
<td id='' style='width:10%'>".$NombreMarca."</td>
<td id='' style='width:10%'>".$NombreCategoria."</td>
<td  id='' style='width:10%'>".number_format($totalCantidad,'2',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($totalVenta,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


}
}

  
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5" => number_format($totalinvertido,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}


function  BuscarRegistroDevolucion($buscar,$cod_local)
{
$mysqli=conectar_al_servidor();
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,dtv.detalleproducto,vt.total_venta,dtv.comision,vt.puntoexpedicion,vt.num_factura,vt.fecha_venta,vt.TipoPago,dtv.estado,IFNULL(dtv.descuento,0) as descuento,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
 where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  and concat(pr.cod_producto,' ',pr.nombre_producto,' ',vt.num_factura,' ',(Select telefono from persona where cod_persona=cod_clienteFK),' ',(Select nombre_persona from persona where cod_persona=cod_clienteFK),' ',(Select ci_cliente from cliente where cod_cliente=cod_clienteFK)) like '%".$buscar."%' ".$condicionCodLocal." 
 order by vt.cod_venta desc limit 500";

$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']);   
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
$comision = utf8_encode($valor['comision']); 
$num_factura = utf8_encode($valor['num_factura']); 
$fecha_venta = utf8_encode($valor['fecha_venta']); 
$clientenombre = utf8_encode($valor['clientenombre']); 
$cantidadcuota = utf8_encode($valor['cantidadcuota']); 
$fechaprimerpago = utf8_encode($valor['fechaprimerpago']); 
$Monto = utf8_encode($valor['Monto']); 
$estado = utf8_encode($valor['estado']); 
$TipoPago = utf8_encode($valor['TipoPago']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$descuento = utf8_encode($valor['descuento']); 
$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventaDevoluciones(this)";

  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle' onclick='$eventos'>
<td id='td_datos_9'  style='width:10%'>".$clientenombre."</td>
<td id='td_datos_18'  style='width:10%'>".$nrof."</td>
<td id='td_datos_1'  style='display:none'>".$num_factura."</td>
<td id='td_datos_2'  style='width:10%'>".$fecha_venta."</td>
<td id='td_datos_3'  style='display:none'>".$cod_producto."</td>
<td id='td_datos_4'  style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_5' style='width:20%;".$styleG."'>".$nombre_producto."</td>
<td  id='td_datos_6' style='width:5%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_7' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_20' style='width:5%'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_8' style='width:5%'>".number_format($subtotal,'0',',','.')."</td>
<td  id='td_datos_10' style='display:none'>".$comision."</td>
<td  id='td_datos_11' style='display:none'>".number_format($totalpagado,'0',',','.')."</td>
<td  id='td_datos_12' style='display:none'>".number_format($totalventa,'0',',','.')."</td>
<td  id='td_datos_15' style='display:none'>".number_format($Monto,'0',',','.')."</td>
<td  id='td_datos_13' style='display:none'>".$cantidadcuota."</td>
<td  id='td_datos_14' style='display:none'>".$fechaprimerpago."</td>
<td  id='td_datos_16' style='display:none'>".$TipoPago."</td>
<td  id='td_datos_17' style='display:none'>".$cod_ventaFK."</td>
<td  id='td_datos_19' style='display:none'>".$detalleproducto."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalventa,'0',',','.'),"4" => number_format($totalpagado,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function  buscarexpedientes($cliente)
{
$mysqli=conectar_al_servidor();

$sql= "select vt.puntoexpedicion,vt.num_factura,pr.cod_producto,pr.nombre_producto,dtv.descuento,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,dtv.detalleproducto,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where vt.cod_clienteFK='$cliente'";
$controlVentas="";
$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']); 
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
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$num_factura = utf8_encode($valor['num_factura']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']); 
$descuento = utf8_encode($valor['descuento']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$styleG=""; 
$styleDetalle=""; 



$tituloPagos="";
if($controlVentas!=$cod_ventaFK){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$num_factura."</p>";
	$controlVentas=$cod_ventaFK;
}

  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}


$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  class='$styleDetalle' >
<td id=''  style='width:10%'>".$nrof."</td>
<td  id='' style='width:20%'>".$nombre_producto." *".$NombreMarca."*</td>
<td  id='' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($descuento,'0',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($subtotal,'0',',','.') ."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function comisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$Descuento,$Flete,$cliente,$Local,$producto)
{
$mysqli=conectar_al_servidor();

$condicionfecha="";
if($fecha1 !="" && $fecha2 !=""){
$condicionfecha=" and vt.fecha_venta>='$fecha1' and vt.fecha_venta<='$fecha2' ";	
}
$condicionfechafiltro="";
if($fechafiltro !="" ){
$condicionfechafiltro=" and vt.fecha_venta='$fechafiltro' ";	
}
$condicionfechaVendedor="";
if($vendedor !="" ){
$condicionfechaVendedor=" and (Vendedor1='$vendedor' or Vendedor2='$vendedor')";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%' ";
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) like '%".$cliente."%' ";
}

$condicionLocal="";
if($Local!=""){
	$condicionLocal=" and vt.cod_local like '%".$Local."%' ";
}


$condicionDescuento="";
if($Descuento==""){
	$condicionDescuento=" and pr.cod_producto != '13603' ";
}

$condicionFlete="";
if($Flete==""){
	$condicionFlete=" and pr.cod_producto != '13753' ";
}


$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.puntoexpedicion,vt.total_venta,dtv.comision,vt.num_factura,vt.fecha_venta,vt.Vendedor1,vt.Vendedor2,dtv.estado,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as Cliente,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr 
 inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where  dtv.cod_detalle!='0' and IFNULL((Select count(fecha) from cancelaciones cl where cl.cod_venta=vt.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionfechafiltro.$condicionfechaVendedor.$condicionproducto.$condicionDescuento.$condicionFlete.$condicioncliente.$condicionLocal." group by dtv.cod_detalle limit 100";

$pagina = "";   
$totalacobrar = "0";   
$totalventas = "0";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$registrocargados=0;
$styleName="tableRegistroSearch";
$acobrar="";
$styleDetalle=""; 
$styleG=""; 
$TotalDescuento="0";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$Cliente = utf8_encode($valor['Cliente']);
$cod_producto = utf8_encode($valor['cod_producto']);
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
$comision = utf8_encode($valor['comision']); 
$num_factura = utf8_encode($valor['num_factura']); 
$fecha_venta = utf8_encode($valor['fecha_venta']); 
$Vendedor1 = utf8_encode($valor['Vendedor1']); 
$Vendedor2 = utf8_encode($valor['Vendedor2']); 
$estado = utf8_encode($valor['estado']);
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);   
$nombrevendedor1 = utf8_encode($valor['nombrevendedor1']);   
$nombrevendedor2 = utf8_encode($valor['nombrevendedor2']);  
$nombrelocal = utf8_encode($valor['nombrelocal']);  
$vendedores=$nombrevendedor1;
$vendedores.="<br>".$nombrevendedor2;
$totalventa=$precio_producto*$cantidad_detalle;


if($comision>0){


$comisionmonto=($subtotal*$comision)/100;
$styleG=""; 
$styleDetalle=""; 


$controlComision=0;
if($Vendedor1!=""){
$controlComision=$controlComision+1;	
}
if($Vendedor2!=""){
$controlComision=$controlComision+2;	
}
if($controlComision==0){
$controlComision=1;
}
$totalVentaDetalle=$precio_producto*$cantidad_detalle;
$acobrar=$comisionmonto/$controlComision;

}
$totalventas=$totalventas+$totalventa;
$totalacobrar=$totalacobrar+$acobrar;
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle' >
<td  id='' style='width:15%'>".$vendedores."</td>
<td  id='' style='width:12%'>".$nrof."</td>
<td  id='' style='width:23%'>".$Cliente."</td>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td  id='' style='width:15%;".$styleG."'>".$nombre_producto."</td>
<td  id='' style='width:7%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='' style='width:7%'>".number_format($cantidad_detalle,'0',',','.')." </td>
<td  id='' style='width:11%'>".$nombrelocal." </td>
</tr>
</table>";
$registrocargados=$registrocargados+1;


if($cod_producto=="13603"){
	$TotalDescuento = $TotalDescuento + $precio_producto ;
}

}
}

$sql= "select pr.cod_producto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where  dtv.cod_detalle!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionfechafiltro.$condicionfechaVendedor.$condicionproducto.$condicionDescuento.$condicionFlete.$condicioncliente.$condicionLocal." group by dtv.cod_detalle "; 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistros=$valor;

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalacobrar,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5"=>$nroRegistro,"99"=>$registrocargados,"100"=>$totalregistros,"101"=> number_format($TotalDescuento,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function mascomisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$registrocargado,$totalcomision,$totalventa,$registroscargados,$Descuento,$Flete,$producto,$totalDescuento,$cliente,$Local)
{
$mysqli=conectar_al_servidor();

$condicionfecha="";
if($fecha1 !="" && $fecha2 !=""){
$condicionfecha=" and vt.fecha_venta>='$fecha1' and vt.fecha_venta<='$fecha2' ";	
}
$condicionfechafiltro="";
if($fechafiltro !="" ){
$condicionfechafiltro=" and vt.fecha_venta='$fechafiltro' ";	
}
$condicionfechaVendedor="";
if($vendedor !="" ){
$condicionfechaVendedor=" and (Vendedor1='$vendedor' or Vendedor2='$vendedor')";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%' ";
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) like '%".$cliente."%' ";
}
$condicionLocal="";
if($Local!=""){
	$condicionLocal=" and vt.cod_local like '%".$Local."%' ";
}

$condicionDescuento="";
if($Descuento==""){
	$condicionDescuento=" and pr.cod_producto != '13603' ";
}

$condicionFlete="";
if($Flete==""){
	$condicionFlete=" and pr.cod_producto != '13753' ";
}

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.puntoexpedicion,vt.total_venta,dtv.comision,vt.num_factura,vt.fecha_venta,vt.Vendedor1,vt.Vendedor2,dtv.estado,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as Cliente,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where  dtv.cod_detalle!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionfechafiltro.$condicionfechaVendedor.$condicionproducto.$condicionDescuento.$condicionFlete.$condicioncliente.$condicionLocal." group by dtv.cod_detalle limit ".$registrocargado." , 100 ";

$pagina = "";   
$acobrar="";
$styleDetalle=""; 
$styleG=""; 

$totalacobrar =$totalcomision;   
$totalventas = $totalventa;   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$registrocargados=$registroscargados;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$Cliente = utf8_encode($valor['Cliente']);
$cod_producto = utf8_encode($valor['cod_producto']);
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
$comision = utf8_encode($valor['comision']); 
$num_factura = utf8_encode($valor['num_factura']); 
$fecha_venta = utf8_encode($valor['fecha_venta']); 
$Vendedor1 = utf8_encode($valor['Vendedor1']); 
$Vendedor2 = utf8_encode($valor['Vendedor2']); 
$estado = utf8_encode($valor['estado']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);   
$nombrevendedor1 = utf8_encode($valor['nombrevendedor1']);   
$nombrevendedor2 = utf8_encode($valor['nombrevendedor2']);  
$nombrelocal = utf8_encode($valor['nombrelocal']);  
$totalventa=$precio_producto*$cantidad_detalle;
$vendedores=$nombrevendedor1;
$vendedores.="<br>".$nombrevendedor2;
if($comision>0){


$comisionmonto=($subtotal*$comision)/100;
$styleG=""; 
$styleDetalle=""; 


$controlComision=0;
if($Vendedor1!=""){
$controlComision=$controlComision+1;	
}
if($Vendedor2!=""){
$controlComision=$controlComision+2;	
}
if($controlComision==0){
$controlComision=1;
}
$totalVentaDetalle=$precio_producto*$cantidad_detalle;
$acobrar=$comisionmonto/$controlComision;

}
$totalventas=$totalventas+$totalventa;
$totalacobrar=$totalacobrar+$acobrar;
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle' >
<td  id='' style='width:15%'>".$vendedores."</td>
<td  id='' style='width:12%'>".$nrof."</td>
<td  id='' style='width:23%'>".$Cliente."</td>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td  id='' style='width:15%;".$styleG."'>".$nombre_producto."</td>
<td  id='' style='width:7%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='' style='width:7%'>".number_format($cantidad_detalle,'0',',','.')." </td>
<td  id='' style='width:11%'>".$nombrelocal." </td>
</tr>
</table>";
$registrocargados=$registrocargados+1;

if($cod_producto=="13603"){
	$totalDescuento = $totalDescuento + $precio_producto ;
}
}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalacobrar,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5"=>$registrocargados,"99"=>$nroRegistro , "101"=>number_format($totalDescuento,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarnroventab()
{
	
	
	$mysqli=conectar_al_servidor();
	 $sql= "Select count(cod_venta) from venta ";
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$NroVenta=$result->fetch_row();
  $NroVenta=$NroVenta[0];
  $NroVenta=$NroVenta;
 if($NroVenta<10){
	 $NroVenta="0000".$NroVenta;
 }else{
 if($NroVenta<100){
	 $NroVenta="000".$NroVenta;
 }else{
	 if($NroVenta<1000){
	 $NroVenta="00".$NroVenta;
    } 
 }
 }
  mysqli_close($mysqli); 
 return $NroVenta;

}

function buscarHistorialGarantia($nrofactura,$cod_local,$documento,$cliente,$estado)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicionestado="";
if($estado!=""){
$condicionestado=" and gt.estado='$estado' ";
}
$condicionnrofactura="";
if($nrofactura!=""){
$condicionnrofactura=" and vt.num_factura like '%".$nrofactura."%' ";
}
$condicionCodLocal=" "; 
if($cod_local!=""){
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
}
$condiciondocumento="";
if($documento!=""){
$condiciondocumento=" and (Select ci_cliente from cliente where cod_cliente=cod_clienteFK ) = '".$documento."' ";
}
$condicioncliente="";
if($cliente!=""){
$condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%' ";
}

$sql= "select gt.idgarantia,gt.fecharecibido,gt.fechaenvio,gt.fechaentrega,gt.fechadevuelto,gt.observacion,gt.estado,gt.cod_productoFK,gt.cod_ventaFK,
pr.cod_producto,pr.nombre_producto,vt.puntoexpedicion,vt.num_factura,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK ) as nrodocliente,
 telefonoaviso as telefono,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select nombre_persona from persona where cod_persona=cod_usuarioFKRecibido) as usuariorecibidopor,
(Select nombre_persona from persona where cod_persona=cod_usuarioFkEnvio) as usuarioenviado,
(Select nombre_persona from persona where cod_persona=cod_usuarioFkDevuelto) as usuariolisto,
(Select nombre_persona from persona where cod_persona=cod_usuarioFkEntrega) as usuarioentrega
from garantias gt inner join venta vt on vt.cod_venta=gt.cod_ventaFK 
inner join detalle_venta dtv on dtv.cod_ventaFK=vt.cod_venta
inner join producto pr on dtv.cod_productoFK=pr.cod_producto 
where gt.idgarantia!='' ".$condicionestado.$condicionnrofactura.$condicionCodLocal.$condiciondocumento.$condicioncliente." group by gt.idgarantia  order by gt.idgarantia desc";
 
$pagina="";

$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlVentas="";
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$idgarantia = utf8_encode($valor['idgarantia']); 
$fecharecibido = utf8_encode($valor['fecharecibido']);  
$fechaenvio = utf8_encode($valor['fechaenvio']);          
$fechadevuelto = utf8_encode($valor['fechadevuelto']);          
$fechaentrega = utf8_encode($valor['fechaentrega']);          
$observacion = utf8_encode($valor['observacion']);          
$estado = utf8_encode($valor['estado']); 
$estadox = utf8_encode($valor['estado']); 
$cod_productoFK = utf8_encode($valor['cod_productoFK']); 
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);
$num_factura = utf8_encode($valor['num_factura']);
$nrodocliente = utf8_encode($valor['nrodocliente']);
$clientenombre = utf8_encode($valor['clientenombre']);
$telefono = utf8_encode($valor['telefono']);
$usuariorecibidopor = utf8_encode($valor['usuariorecibidopor']);
$usuarioenviado = utf8_encode($valor['usuarioenviado']);
$usuariolisto = utf8_encode($valor['usuariolisto']);
$usuarioentrega = utf8_encode($valor['usuarioentrega']);


if($estado=="Pendiente a verificar"){
	$estado="PENDIENTE A VERIFICAR";
}
if($estado=="verificacion"){
	$estado="EN VERIFICACION";
}
if($estado=="entregado"){
		$estado="ENTREGADO";
}
if($estado=="listo"){
		$estado="LISTO PARA ENTREGAR";
}

$tituloUsuarios="
Cargado: ".$usuariorecibidopor."
<br>
A verificacion: ".$usuarioenviado."
<br>
Listo para entregar: ".$usuariolisto."
<br>
Entregado por : ".$usuarioentrega;

$tituloFechas="
Cargado : ".$fecharecibido."
<br>
A verificacion : ".$fechaenvio."
<br>
Listo para entregar : ".$fechadevuelto."
<br>
Entregado por : ".$fechaentrega;


			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductosgarantia(this)' >
<td id='td_id_1' style='display:none' >".$idgarantia."</td>
<td id='td_datos_1' style='width:10%' >".$nrof."</td>
<td id='td_datos_3' style='width:10%' >".$nrodocliente."</td>
<td id='td_datos_4' style='width:10%' >".$clientenombre."</td>
<td id='td_datos_4' style='width:10%' >".$telefono."</td>
<td id='td_datos_5' style='width:10%' >".$nombre_producto."</td>
<td id='td_datos_6' style='width:10%' >".$observacion."</td>
<td id='' style='width:10%' >".$estado."</td>
<td id='td_datos_7' style='width:10%' >".$tituloFechas."</td>
<td id='td_datos_10' style='width:10%' >".$tituloUsuarios."</td>
<td id='td_datos_9' style='display:none' >".$estadox."</td>
</tr>
</table>
";



}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;


}

function verificar_fecha_expiracion($fecha)
{ 


	$fecha=date_create($fecha);
	$fecha=date_format($fecha,"Y-m-d H:i:s");
	$fecha = strtotime($fecha);

	$fecha_2 = date('Y-m-d H:i:s');
 
$fecha_2=strtotime($fecha_2);

 if($fecha_2>$fecha)
 {
	 return "si";
 }else
 {
	 return "no";
 }

}

function editarDiasAtrazados($codCliente,$nroDias)
{
	
$mysqli=conectar_al_servidor(); 
$consulta1="Update cliente set totaldias='$nroDias' where cod_cliente='$codCliente' and totaldias<'$nroDias' ";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	
}


function refinanciarencambio($cod_venta,$totalActual,$metodopago){
	
	
	$mysqli=conectar_al_servidor();
	$sql= "Select idcredito,Monto,descuento,fechapago,dias,interes,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
	from credito cr
	where cr.cod_venta='$cod_venta' ";
	
	$descuento=0;  
	$totalenCuotas=0;
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $F=0;
 $cont=0;
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     
			 $cuota=utf8_encode($valor['Monto']);
			 $fechapago=utf8_encode($valor['fechapago']);
			 $dias=utf8_encode($valor['dias']);
			 $interes=utf8_encode($valor['interes']);
			 $totalenCuotas=$totalenCuotas+$cuota;				 
			 $cont=$cont+1;
			
			  
	  }
 }
 
		
       $sobranteTotales=$totalActual-$totalenCuotas;
	   if($sobranteTotales<0){
		$sobranteTotales=$sobranteTotales*-1;
		   
   $sql= "Select idcredito,Monto,descuento,fechapago,dias,interes,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
	from credito cr	where cr.cod_venta='$cod_venta'  
	and (IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)+descuento)<Monto order by fechapago desc ";
	

$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     
			 $cuota=utf8_encode($valor['Monto']);
			 $idcredito=utf8_encode($valor['idcredito']);
			 $dias=utf8_encode($valor['dias']);
			 $interes=utf8_encode($valor['interes']);
			 if($sobranteTotales>0){
				 
			 if($sobranteTotales>$cuota){
				 $consulta="Delete From credito Where idcredito='$idcredito'";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$sobranteTotales=$sobranteTotales-$cuota;

			 }else{
				
 $consulta="Update credito set Monto='$sobranteTotales'  Where idcredito='$idcredito'";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$sobranteTotales=$sobranteTotales-$cuota;
				
			 }
			 
			 }
			
			  
	  }
 }
		   
		   
		   
		   

	   }else{
		 $fechaInicio=$fechapago;
		 if($metodopago=="Mensual")	{
			$F=$F+1; 
			$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
		 }
		 if($metodopago=="Semanal")	{
			 $F=$F+7;
			 $fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
		 }
		if($metodopago=="Quincenal")	{
			 $F=$F+15;
			 $fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
		 }
		 $fechapago=date("Y-m-d H:i:s",$fecha);
		 $plazo=($cont+1)."/".($cont+1);
		 $consulta="Insert into credito (plazo,fechapago, cod_venta, Monto, Esado,Nro_recibo,dias,interes,total,descuento)
			values('$plazo','$fechapago','$cod_venta','$sobranteTotales','Pendiente','0','$dias','$interes','$sobranteTotales','0')";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
		   
	   }
		
		
	
			  mysqli_close($mysqli);
			 $informacion =array("1" => "exito" );
echo json_encode($informacion);	
exit;
	
		
}



verificar($operacion);
?>
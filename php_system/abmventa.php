<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

//cargar achivos importantes
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



//CONTROL DE ACCESO




	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$fecha_venta=$_POST['fecha_venta'];
$fecha_venta = utf8_decode($fecha_venta);
$cod_usuarioFK=$_POST['cod_usuarioFK'];
$cod_usuarioFK = utf8_decode($cod_usuarioFK);
$cod_clienteFK=$_POST['cod_clienteFK'];
$cod_clienteFK = utf8_decode($cod_clienteFK);
$num_factura=$_POST['num_factura'];
$num_factura = utf8_decode($num_factura);
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
$comision=$_POST['comision'];
$comision = utf8_decode($comision);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$idGaranteFk=$_POST['idGaranteFk'];
$idGaranteFk = utf8_decode($idGaranteFk);
$tipo_comprobante=$_POST['tipo_comprobante'];
$tipo_comprobante = utf8_decode($tipo_comprobante);
$puntoexpedicion=$_POST['puntoexpedicion'];
$puntoexpedicion = utf8_decode($puntoexpedicion);

abm($puntoexpedicion,$tipo_comprobante,$cod_venta,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$cod_local,$idGaranteFk,$operacion);

}

if($operacion=="historialventa")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);
$tipoventa=$_POST['tipoventa'];
$tipoventa = utf8_decode($tipoventa);
$estadocuenta=$_POST['estadocuenta'];
$estadocuenta = utf8_decode($estadocuenta);
$local=$_POST['local'];
$local = utf8_decode($local);

$tipoComprobante=$_POST['tipoComprobante'];
$tipoComprobante = utf8_decode($tipoComprobante);

$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);


$AgenteCredito=$_POST['AgenteCredito'];
$AgenteCredito = utf8_decode($AgenteCredito);


if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
	historialventa($AgenteCredito,$fecha1,$fecha2,$fechafiltro,$nroventa,$documento,$cliente,$telefono,$tipoventa,$estadocuenta,$local,$tipoComprobante,$vendedor);

}

if($operacion=="mashistorialventa")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);
$tipoventa=$_POST['tipoventa'];
$tipoventa = utf8_decode($tipoventa);
$estadocuenta=$_POST['estadocuenta'];
$estadocuenta = utf8_decode($estadocuenta);
$local=$_POST['local'];
$local = utf8_decode($local);
$totalventa=$_POST['totalventa'];
$totalventa = quitarseparadormiles($totalventa);
$totalpagado=$_POST['totalpagado'];
$totalpagado = quitarseparadormiles($totalpagado);
$totalpendiente=$_POST['totalpendiente'];
$totalpendiente = quitarseparadormiles($totalpendiente);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);

$tipoComprobante=$_POST['tipoComprobante'];
$tipoComprobante = utf8_decode($tipoComprobante);

$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);

if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}


$AgenteCredito =$_POST['AgenteCredito'];
$AgenteCredito = utf8_decode($AgenteCredito);

mashistorialventa($AgenteCredito,$fecha1,$fecha2,$fechafiltro,$nroventa,$documento,$cliente,$telefono,$tipoventa,$estadocuenta,$local,$totalventa,$totalpagado,$totalpendiente,$registrocargado,$tipoComprobante,$vendedor);

}

if($operacion=="buscarnroventa")
{
$puntoExpedicion=$_POST['puntoExpedicion'];
$puntoExpedicion = utf8_decode($puntoExpedicion);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipo_comprobante=$_POST['tipo_comprobante'];
$tipo_comprobante = utf8_decode($tipo_comprobante);

$datos=buscarcodNroFactura($cod_local,$puntoExpedicion);
$num_factura=buscarnrofactura($datos[0],$datos[1]);

if($tipo_comprobante=="FACTURA"){
	$datos=buscarcodNroFactura($cod_local,$puntoExpedicion);
	$num_factura=buscarnrofactura($datos[0],$datos[1]);
	$codnrofactura=$datos[0];
	}else{
		$num_factura=buscarnroventab();
		
	}

$informacion =array("1" => "exito","2" => $num_factura );
echo json_encode($informacion);	
exit;
}

if($operacion=="historialvistaventa")
{
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$filtro=$_POST['filtro'];
$filtro = utf8_decode($filtro);
	historialvistaventa($buscar,$filtro);

}
if($operacion=="buscardatosVenta")
{
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscardatosVenta($buscar);

}
if($operacion=="buscarexpedientes")
{

$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
	buscarexpedientes($cliente);

}
if($operacion=="buscarclientesincativos")
{

$Local=$_POST['Local'];
$Local = utf8_decode($Local);
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$nrotelefono=$_POST['nrotelefono'];
$nrotelefono = utf8_decode($nrotelefono);
$Vendedor=$_POST['Vendedor'];
$Vendedor = utf8_decode($Vendedor);
	buscarclientesincativos($buscar,$documento,$cliente,$nrotelefono,$Vendedor,$Local);
}
if($operacion=="buscarmasclientesincativos")
{
$Local=$_POST['Local'];
$Local = utf8_decode($Local);
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$nrotelefono=$_POST['nrotelefono'];
$nrotelefono = utf8_decode($nrotelefono);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
$Vendedor=$_POST['Vendedor'];
$Vendedor = utf8_decode($Vendedor);
buscarmasclientesincativos($buscar,$documento,$cliente,$nrotelefono,$registrocargado,$Vendedor,$Local);

}

if($operacion=="cuentasMoroso")
{

$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$filtro=$_POST['filtro'];
$filtro = utf8_decode($filtro);
$zona=$_POST['zona'];
$zona = utf8_decode($zona);
$Local=$_POST['Local'];
$Local = utf8_decode($Local);
historialFiltroMorosos($buscar,$filtro,$zona,$Local);
}



if($operacion=="buscarCuentasCanceladas")
{

$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarCuentasCanceladas($buscar);

}
if($operacion=="buscarCuentasPendientes")
{

$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarCuentasPendientes($buscar);

}
if($operacion=="ganaciaventa")
{
	$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$nrodocumento=$_POST['nrodocumento'];
$nrodocumento = utf8_decode($nrodocumento);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipoventa=$_POST['tipoventa'];
$tipoventa = utf8_decode($tipoventa);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	ganaciaventa($nroventa,$fecha1,$fecha2,$cliente,$nrodocumento,$fechafiltro,$cod_local,$tipoventa);

}
if($operacion=="masganaciaventa")
{
	$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);

$costoTotal=$_POST['costoTotal'];
$costoTotal = quitarseparadormiles($costoTotal);

$VentaTotal=$_POST['VentaTotal'];
$VentaTotal = quitarseparadormiles($VentaTotal);

$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$nrodocumento=$_POST['nrodocumento'];
$nrodocumento = utf8_decode($nrodocumento);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipoventa=$_POST['tipoventa'];
$tipoventa = utf8_decode($tipoventa);
$totalcostos=$_POST['totalcostos'];
$totalcostos = quitarseparadormiles($totalcostos);
$totalcomision=$_POST['totalcomision'];
$totalcomision = quitarseparadormiles($totalcomision);
$totalpagado=$_POST['totalpagado'];
$totalpagado = quitarseparadormiles($totalpagado);
$totalevaluacion=$_POST['totalevaluacion'];
$totalevaluacion = quitarseparadormiles($totalevaluacion);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	masganaciaventa($costoTotal,$VentaTotal,$nroventa,$fecha1,$fecha2,$cliente,$nrodocumento,$fechafiltro,$cod_local,$tipoventa,$totalcostos,$totalcomision,$totalpagado,$totalevaluacion,$registrocargado);

}
if($operacion=="ganaciaventacalculo")
{
		$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$nrodocumento=$_POST['nrodocumento'];
$nrodocumento = utf8_decode($nrodocumento);
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipoventa=$_POST['tipoventa'];
$tipoventa = utf8_decode($tipoventa);
	ganaciaventacalculo($nroventa,$fecha1,$fecha2,$cliente,$nrodocumento,$fechafiltro,$cod_local,$tipoventa);

}



if($operacion=="buscarCambiosRealizados")
{
	$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = utf8_decode($fechafiltro);
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$nrofactura=$_POST['nrofactura'];
$nrofactura = utf8_decode($nrofactura);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
	buscarCambiosRealizados($fechafiltro,$fecha1,$fecha2,$nrofactura,$cod_local);

}

if($operacion=="eliminarVenta")
{
	$codventa=$_POST['codventa'];
$codventa = utf8_decode($codventa);
$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);
$nroFactura=$_POST['nroFactura'];
$nroFactura = utf8_decode($nroFactura);
	
	eliminarventa($codventa,$motivo,$nroFactura);

}	

if($operacion=="buscarexpedientescambios")
{
	$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);
	 buscarCambiosRealizadosExt($cliente,$motivo);

}
if($operacion=="cancelarventa")
{

$montodevuelto=$_POST['montodevuelto'];
$montodevuelto = quitarseparadormiles($montodevuelto);
$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);
$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
abmcancelarventa($montodevuelto,$motivo,$fecha,$cod_venta,"nuevo");

}
if($operacion=="refinanciartotalventa")
{

$total=$_POST['total'];
$total = quitarseparadormiles($total);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);
abmactualizarTotal($total,$cod_ventaFK);

}
	if($operacion=="buscarexpedientescancelados")
{
	$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$zona=$_POST['zona'];
$zona = utf8_decode($zona);
	 buscarexpedientescancelados($cliente,$zona);

}

if($operacion=="historialventacancelado")
{

$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$codlocal=$_POST['codlocal'];
$codlocal = utf8_decode($codlocal);
if($codlocal==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$codlocal=buscarlocaluser($user);
	}
}
	historialventacancelado($filtrofecha,$fecha1,$fecha2,$nroventa,$cliente,$codlocal);

}
if($operacion=="mashistorialventacancelado")
{

$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$codlocal=$_POST['codlocal'];
$codlocal = utf8_decode($codlocal);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
if($codlocal==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$codlocal=buscarlocaluser($user);
	}
}
mashistorialventacancelado($filtrofecha,$fecha1,$fecha2,$nroventa,$cliente,$codlocal,$registrocargado);

}

if($operacion=="actualizarnrofactura")
{

$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$puntoexpedicion=$_POST['puntoexpedicion'];
$puntoexpedicion = utf8_decode($puntoexpedicion);
$nrofactura=$_POST['nrofactura'];
$nrofactura = utf8_decode($nrofactura);

$TipoRecibo =$_POST['TipoRecibo'];
$TipoRecibo = utf8_decode($TipoRecibo);

actualizarnrofactura($TipoRecibo,$cod_venta,$puntoexpedicion,$nrofactura);

}


if($operacion=="actualizarCobrador")
{

$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);

$cobrador =$_POST['cobrador'];
$cobrador = utf8_decode($cobrador);

actualizarCobrador($cod_venta,$cobrador);

}



if($operacion=="buscarClienteFiel"){
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$local=$_POST['local'];
$local = utf8_decode($local);
$zona=$_POST['zona'];
$zona = utf8_decode($zona);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);
$condicion=$_POST['condicion'];
$condicion = utf8_decode($condicion);
	buscarClienteFiel($fecha1,$fecha2,$local,$zona,$cliente,$vendedor,$condicion);
}




if($operacion=="AsignarCobrador")
{

$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);

$cobrador =$_POST['cobrador'];
$cobrador = utf8_decode($cobrador);

AsignarCobrador($cod_venta,$cobrador);

}



}


function AsignarCobrador($cod_venta,$cobrador)
{
	
	
if($cod_venta=="" || $cobrador==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

$consulta1="Update venta set cob_ex=?  where cod_venta=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$cobrador,$cod_venta);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}




function actualizarCobrador($cod_venta,$cobrador)
{
	
	
if($cod_venta=="" || $cobrador==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

$consulta1="Update venta set cod_cobradorFK=?  where cod_venta=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$cobrador,$cod_venta);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}


function abm($puntoexpedicion,$tipo_comprobante,$cod_venta,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$cod_local,$idGaranteFk,$operacion)
{
	
	
if($fecha_venta=="" || $cod_usuarioFK=="" || $cod_clienteFK==""|| $cod_cobradorFK=="" || $comision==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo")
{

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

$consulta1="Insert into venta (idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2,comision,cod_local,tipo_comprobante,puntoexpedicion,codnrofactura)
values(?,?,'0',?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssssssss';
$stmt1->bind_param($ss,$idGaranteFk,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$cod_local,$tipo_comprobante,$puntoexpedicion,$codnrofactura);


}


if($operacion=="editar")
{

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
	
	$consulta1="Update venta set codnrofactura=?,idGaranteFk=?,fecha_venta=?,cod_usuarioFK=?,cod_clienteFK=?,num_factura=?,cod_cobradorFK=?,TipoVenta=?,TipoPago=?,Vendedor1=?,Vendedor2=?,comision=?,cod_local=?,tipo_comprobante=?,puntoexpedicion=? where cod_venta=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssssssssss';
$stmt1->bind_param($ss,$codnrofactura,$idGaranteFk,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$cod_local,$tipo_comprobante,$puntoexpedicion,$cod_venta);

	
	}else{
	
$consulta1="Update venta set idGaranteFk=?,fecha_venta=?,cod_usuarioFK=?,cod_clienteFK=?,num_factura=?,cod_cobradorFK=?,TipoVenta=?,TipoPago=?,Vendedor1=?,Vendedor2=?,comision=?,cod_local=?,tipo_comprobante=?,puntoexpedicion=? where cod_venta=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssssssss';
$stmt1->bind_param($ss,$idGaranteFk,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$cod_local,$tipo_comprobante,$puntoexpedicion,$cod_venta);

	
	}


}

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
if($operacion=="nuevo")
{
$cod_venta=obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura,$cod_local);
}
 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $cod_venta );
echo json_encode($informacion);	
exit;
	
}

function actualizarnrofactura($TipoRecibo,$cod_venta,$puntoexpedicion,$nrofactura)
{
	
	
if($cod_venta=="" || $puntoexpedicion=="" || $nrofactura==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

$consulta1="Update venta set num_factura=?,puntoexpedicion=?,tipo_comprobante=? where cod_venta=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$nrofactura,$puntoexpedicion,$TipoRecibo,$cod_venta);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function abmcancelarventa($montodevuelto,$motivo,$fecha,$cod_venta,$operacion)
{
	
	
if($cod_venta=="" || $montodevuelto=="" || $fecha==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
if($operacion=="nuevo") 
{
$consulta1="Insert into cancelaciones (montodevuelto,motivo,fecha,cod_venta)
values(?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$montodevuelto,$motivo,$fecha,$cod_venta);
}

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 buscardetalleventacancelaciones($cod_venta);
  mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $cod_venta );
echo json_encode($informacion);	
exit;
	
}


/*Buscar */
function buscardetalleventacancelaciones($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select dtv.cod_productoFK,dtv.cantidad_detalle,vt.cod_local
 from detalle_venta dtv inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
 where dtv.cod_ventaFK ='$buscar' ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$a=1;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_productoFK = utf8_decode($valor['cod_productoFK']);    
$cantidad_detalle = utf8_decode($valor['cantidad_detalle']);      
$cod_local = utf8_decode($valor['cod_local']);      
	
editar_cantidad($cod_productoFK,$cantidad_detalle,"sumar",$cod_local);
}
}


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
	


function abmactualizarTotal($total,$cod_venta)
{
	
	
if($total=="" || $cod_venta==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
$consulta1="Update venta set total_venta=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$total,$cod_venta); 
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 insertarHistorialRefinanciar($cod_venta);
 
  mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $cod_venta );
echo json_encode($informacion);	
exit;
	
}

function  insertarHistorialRefinanciar($cod_venta){
	  
	  $user=$_POST['useru'];
    $user = utf8_decode($user);
	$mysqli=conectar_al_servidor();
	$consulta="Insert into refinanciamentos (fecha,cod_venta,cod_usuario) 
	values(current_date(),'$cod_venta','$user')";	
	
	$stmt = $mysqli->prepare($consulta);
	

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli); 
}

function eliminarventa($cod_venta,$motivo,$nroFactura)
{
	
	
if($cod_venta==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

eliminarpagos($cod_venta);
eliminarcreditos($cod_venta);

$mysqli=conectar_al_servidor();

/*AUDITORIA*/
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$fecha = date('Y-m-d', time()); 
$user=$_POST['useru'];
$user = utf8_decode($user);

$consulta="Insert into ventaseliminadas (nrofactura,motivo,fecha,cod_user_insert,fecha_insert)
values(?,?,?,?,?)";
$stmt = $mysqli->prepare($consulta);
$ss='sssss';
$stmt->bind_param($ss,$nrofactura,$motivo,$fecha,$user,$fecha_inser_edit);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$consulta="delete from venta where cod_venta='$cod_venta'";	
$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito" );
echo json_encode($informacion);	
exit;
	
}

function eliminarcreditos($cod_venta){
		$mysqli=conectar_al_servidor();
			$consulta="delete from credito where  cod_venta='$cod_venta'";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli); 
}
function eliminarpagos($cod_venta){
		$mysqli=conectar_al_servidor();
			$consulta="delete from pago where cod_venta_fk='$cod_venta' ";

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli); 
}
function obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $cod_venta='';
		$sql= "Select cod_venta from venta where cod_clienteFK='$cod_clienteFK' and cod_usuarioFK='$cod_usuarioFK' and num_factura='$num_factura' and cod_local='$cod_local' ";
		
   
   
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
 
  mysqli_close($mysqli); 
return $cod_venta;


}


function historialventa($AgenteCredito,$fecha1,$fecha2,$fechafiltro,$nroventa,$documento,$cliente,$telefono,$tipoventa,$estadocuenta,$cod_local,$tipoComprobante,$vendedor){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	 
	  $condicionVendedor="";
	 if($vendedor!=""){
	   $condicionVendedor="and  Vendedor1 ='".$vendedor."'";		
	 }
	 
	 
	 $condicionAgenteCredito="";
	 if($AgenteCredito!=""){
	   $condicionAgenteCredito="and  cod_cobradorFK ='".$AgenteCredito."'";		
	 }
	 
	 
	 
	 $condiciontipoComprobante="";
	 if($tipoComprobante!=""){
		 $condiciontipoComprobante="and tipo_comprobante='".$tipoComprobante."'";
	 }
	 
	 
	  $condicionfecha="";
	 if($fecha1!="" && $fecha2!=""){
		 $condicionfecha="and fecha_venta>='".$fecha1."' and fecha_venta<='".$fecha2."'";
	 }
	 $condicionfechafiltro="";
	 if($fechafiltro!=""){
	   $condicionfechafiltro="and fecha_venta='".$fechafiltro."'";		
	 }
	 $condicionnroventa="";
	 if($nroventa!=""){
	   $condicionnroventa="and num_factura like '%".$nroventa."%'";		
	 }
	 $condiciondocumento="";
	 if($documento!=""){
	   $condiciondocumento="and (Select ci_cliente from cliente where cod_cliente=cod_clienteFK limit 1)='".$documento."'";		
	 }
	 $condicioncliente="";
	 if($cliente!=""){
	   $condicioncliente="and (Select nombre_persona from persona where cod_persona=cod_clienteFK limit 1) like '%".$cliente."%'";		
	 }
	 $condiciontelef="";
	 if($telefono!=""){
	   $condiciontelef="and (Select telefono from persona where cod_persona=vt.cod_clienteFK limit 1) like '%".$telefono."%'";		
	 }
	 $condicionCuenta=" "; 
		 $condiciontipoventa=" "; 
		 if($estadocuenta=="1"){
			$condicionCuenta=" and (IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) + IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0))<total_venta"; 
		 }
		 if($estadocuenta=="2"){
			$condicionCuenta=" and (IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) + IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0))>=total_venta"; 
		 }
		  if($estadocuenta=="3"){
			$condicionCuenta=" and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)>0"; 
		 }
		 if($tipoventa!=""){
			$condiciontipoventa=" and TipoVenta='$tipoventa'"; 
		 }
	 
	 $condicionCodLocal=" "; 
		 if($cod_local!=""){
			
			 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 }
		 
		 $sql= "Select tipo_comprobante,puntoexpedicion,idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,cod_local,pago,
		 (Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as nrodogarante,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select dias from credito where cod_venta=vt.cod_venta limit 1),0) as diasgracia,
		IFNULL((Select interes from credito where cod_venta=vt.cod_venta limit 1),0) as intereses,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
		IFNULL((Select sum(precio_producto*cantidad_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalventadetalle,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
				IFNULL((Select sum(descuento) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalDescuentodetalle,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,vt.fecha_insert,vt.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor,
		(Select accesocredito from cliente where cod_cliente=cod_clienteFK) as accesocredito , cob_ex
		from  venta vt where cod_venta!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  ".$condicionfecha.$condicionAgenteCredito.$condiciontipoComprobante.$condicionfechafiltro.$condicionnroventa.$condiciondocumento.$condicioncliente.$condiciontelef.$condicionCuenta.$condiciontipoventa.$condicionCodLocal.$condicionVendedor."  order by vt.cod_venta asc limit 50" ;
	
		
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
  $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
				$cob_ex=$valor['cob_ex'];
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $Vendedor1=utf8_encode($valor['Vendedor1']);
		  	  $Vendedor2=utf8_encode($valor['Vendedor2']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $cobradornombre=utf8_encode($valor['cobradornombre']);
		  	  $nroCancelado=utf8_encode($valor['nroCancelado']);
		  	  $montodevuelto=utf8_encode($valor['montodevuelto']);
			  $nombrevendedor1=utf8_encode($valor['nombrevendedor1']);
		  	  $nombrevendedor2=utf8_encode($valor['nombrevendedor2']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $nrodetalle=($valor['nrodetalle']);
		  	  $nroCouta=($valor['nroCouta']);
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $Garante=utf8_encode($valor['Garante']);
			  $nrodocliente=utf8_encode($valor['nrodocliente']);
			  $nrodogarante=utf8_encode($valor['nrodogarante']);
			  $diasgracia=utf8_encode($valor['diasgracia']);
			  $intereses=utf8_encode($valor['intereses']);
			  $telefono=utf8_encode($valor['telefono']);
			  $tipo_comprobante=utf8_encode($valor['tipo_comprobante']);
			  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			  $totalDescuentodetalle=utf8_encode($valor['totalDescuentodetalle']);
			  $totalventadetalle=utf8_encode($valor['totalventadetalle']);
			   $insertadopor = utf8_encode($valor['insertadopor']); 
				$editadopor = utf8_encode($valor['editadopor']); 
				$fecha_insert = utf8_encode($valor['fecha_insert']); 
				$fecha_edit = utf8_encode($valor['fecha_edit']);
				$accesocredito = utf8_encode($valor['accesocredito']);
			   $controlFecha=date('Y-m-d');
			 
	  $FacturaActual=  $num_factura;
			 
			    $datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
				$totaldescuento=$datos[0]+$totalDescuentodetalle;
                $totalintereses=$datos[1];
                //$datos[2]=$TotalEnDeuda;
                $totalpagado=$datos[3];
                //$datos[4]=$TotalAPagar;
                // $datos[5]=$TotalDiasAtrasado;
                // $datos[6]=$nrodecuotasatrazado;
                // $datos[7]=$TotalApagarSinInteres;
                $deuda=$datos[8];
                $SubTotalDeuda=$datos[11];
				 if($SubTotalDeuda==0){
				 $SubTotalDeuda=$total_venta;
			 }
			 $subtotalventa=$totalventadetalle-$totaldescuento;
                $totalinterespadado=$datos[12];
                $TotalPagoSininteres=$datos[13];
			    $styleCancelado="";
			 $totalpagado=$totalpagado+$pago;	
 $deudapendiente=$total_venta-$totalpagado;			 
			   if($nroCancelado==0){
			  $TotalVentas=$total_venta+$TotalVentas;
              $TotalPagos= $TotalPagos+$totalpagado;
              $TotalDeuda= $TotalDeuda+$deuda;
			   }else{
				   $deudapendiente=0;
				   $totalpagado=($totalpagado-$montodevuelto);
				  if($totalpagado<0){
					  $totalpagado=0;
				  }
				   $TotalPagos= $TotalPagos+$totalpagado;
				   $TotalVentas=$total_venta+$TotalVentas;
				   $styleCancelado="background-color: #FFEB3B;color:#000";
			   }
if($TipoVenta=="CREDITO"){
 $cuotas=$nroCouta."/".buscarcantidadcuotapagados($cod_venta);
}else{
	$cuotas="CONTADO";
}
if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}


if($cob_ex!="Local"){
	 $styleCancelado="background-color: #4caf50; color: #f7f4f4;";
}


 $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerelementohistroialventa(this)' style='$styleCancelado' >
<td  id='td_datos_1'  style='width:4%'>".$fecha_venta."</td>
<td                   style='width:5%'>".$nrof."</td>
<td  id='td_datos_40' style='width:4%'>".$tipo_comprobante ."</td>
<td                   style='width:5%'>".$nrodocliente."</td>
<td  id='td_datos_2'  style='width:8%'>".$clientenombre."</td>
<td  id='td_datos_34' style='width:4%'>".$telefono."</td>

<td  id='td_datos_12' style='width:5%'>".$TipoVenta ."</td>
<td  id='td_datos_5'  style='width:5%'>". number_format($totalventadetalle,'0',',','.') ."</td>
<td  id='td_datos_29' style='width:5%'>". number_format($totaldescuento,'0',',','.') ."</td>
<td  id='td_datos_38' style='width:5%'>". number_format($subtotalventa,'0',',','.') ."</td>
<td  id=''            style='width:5%'>". number_format($totalinterespadado,'0',',','.') ."</td>
<td  id=''            style='width:5%'>". number_format($TotalPagoSininteres,'0',',','.') ."</td>
<td  id='td_datos_6'  style='width:5%'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='td_datos_24' style='width:5%'>". number_format($totalintereses,'0',',','.') ."</td>
<td  id='td_datos_7'  style='width:5%'>". number_format($deuda,'0',',','.') ."</td>
<td  id=''            style='width:5%'>".$cuotas ."</td>
<td  id='td_datos_4'  style='width:5%'>".$cobradornombre."</td>
<td  id=''            style='width:5%'>".$nombrelocal ."</td>
<td  id='td_datos_15' style='width:5%'>".$nombrevendedor1."</td>
<td  id='td_datos_42' style='width:5%'>".$cob_ex."</td>



<td id='td_datos_13'  style='display:none'>".$num_factura."</td>
<td  id=''            style='display:none'>".$nombrevendedor1." - ".$nombrevendedor2."</td>
<td  id='td_datos_3'  style='display:none'>".$Vendedor1."</td>
<td  id='td_datos_14' style='display:none'>".$Vendedor2."</td>
<td  id='td_datos_16' style='display:none'>".$nombrevendedor2."</td>
<td  id='td_datos_8'  style='display:none'>".$cod_venta ."</td>
<td  id='td_datos_9'  style='display:none'>".$cod_usuarioFK ."</td>
<td  id='td_datos_10' style='display:none'>".$cod_clienteFK ."</td>
<td  id='td_datos_11' style='display:none'>".$cod_cobradorFK ."</td>
<td  id='td_datos_18' style='display:none'>".$TipoPago ."</td>
<td  id='td_datos_19' style='display:none'>".$cantidadcuota ."</td>
<td  id='td_datos_20' style='display:none'>". number_format($Monto,'0',',','.') ."</td>
<td  id='td_datos_21' style='display:none'>".$fechaprimerpago ."</td>
<td  id='td_datos_22' style='display:none'>".$comision ."</td>
<td  id='td_datos_23' style='display:none'>".$cod_local ."</td>
<td  id='td_datos_25' style='display:none'>".number_format($intereses,'0',',','.')."</td>
<td  id='td_datos_26' style='display:none'>".$diasgracia ."</td>
<td  id='td_datos_27' style='display:none'>".$nrodetalle ."</td>
<td  id='td_datos_30' style='display:none'>".$idGaranteFk ."</td>
<td  id='td_datos_31' style='display:none'>".$Garante ."</td>
<td  id='td_datos_32' style='display:none'>".$nrodocliente ."</td>
<td  id='td_datos_33' style='display:none'>".$nrodogarante ."</td>
<td  id='td_datos_35' style='display:none'>".$tipo_comprobante ."</td>
<td  id='td_datos_36' style='display:none'>".$puntoexpedicion ."</td>
<td  id='td_datos_37' style='display:none'>".number_format($deudapendiente,'0',',','.') ."</td>
<td  id='td_datos_39' style='display:none'>".$accesocredito."</td>



<td  id='td_datos_41' style='display:none'>".$FacturaActual."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";

	  
	  }
 }

 $sql= "Select tipo_comprobante
		from venta vt where cod_venta!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0   ".$condicionfecha.$condicionAgenteCredito.$condiciontipoComprobante.$condicionfechafiltro.$condicionnroventa.$condiciondocumento.$condicioncliente.$condiciontelef.$condicionCuenta.$condiciontipoventa.$condicionCodLocal.$condicionVendedor."  order by vt.cod_venta asc " ;
   $stmt = $mysqli->prepare($sql);  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregistro= $valor;
 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}


function mashistorialventa($AgenteCredito,$fecha1,$fecha2,$fechafiltro,$nroventa,$documento,$cliente,$telefono,$tipoventa,$estadocuenta,$cod_local,$totalventa,$totalpagado,$totalpendiente,$registrocargado,$tipoComprobante,$vendedor){
	
	$mysqli=conectar_al_servidor();
	 $totalRegistro=0;
	 $pagina="";
	 
	  $condicionVendedor="";
	 if($vendedor!=""){
	   $condicionVendedor="and  Vendedor1 ='".$vendedor."'";		
	 }
	 
	 
	 $condicionAgenteCredito="";
	 if($AgenteCredito!=""){
	   $condicionAgenteCredito="and  cod_cobradorFK ='".$AgenteCredito."'";		
	 }
	 
	 
	 $condiciontipoComprobante="";
	 if($tipoComprobante!=""){
		 $condiciontipoComprobante="and tipo_comprobante='".$tipoComprobante."'";
	 }
	 
	  $condicionfecha="";
	 if($fecha1!="" && $fecha2!=""){
		 $condicionfecha="and fecha_venta>='".$fecha1."' and fecha_venta<='".$fecha2."'";
	 }
	 $condicionfechafiltro="";
	 if($fechafiltro!=""){
	   $condicionfechafiltro="and fecha_venta='".$fechafiltro."'";		
	 }
	 $condicionnroventa="";
	 if($nroventa!=""){
	   $condicionnroventa="and num_factura like '%".$nroventa."%'";		
	 }
	 $condiciondocumento="";
	 if($documento!=""){
	   $condiciondocumento="and (Select ci_cliente from cliente where cod_cliente=cod_clienteFK limit 1)='".$documento."'";		
	 }
	 $condicioncliente="";
	 if($cliente!=""){
	   $condicioncliente="and (Select nombre_persona from persona where cod_persona=cod_clienteFK limit 1) like '%".$cliente."%'";		
	 }
	 $condiciontelef="";
	 if($telefono!=""){
	   $condiciontelef="and (Select telefono from persona where cod_persona=vt.cod_clienteFK limit 1) like '%".$telefono."%'";		
	 }
	 $condicionCuenta=" "; 
		 $condiciontipoventa=" "; 
		 if($estadocuenta=="1"){
			$condicionCuenta=" and (IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) + IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0))<total_venta"; 
		 }
		 if($estadocuenta=="2"){
			$condicionCuenta=" and (IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) + IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0))>=total_venta"; 
		 }
		  if($estadocuenta=="3"){
			$condicionCuenta=" and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)>0"; 
		 }
		 if($tipoventa!=""){
			$condiciontipoventa=" and TipoVenta='$tipoventa'"; 
		 }
	 
	 $condicionCodLocal=" "; 
		 if($cod_local!=""){
			
			 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 }
		 
		 $sql= "Select tipo_comprobante,puntoexpedicion,idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,cod_local,pago,
		 (Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as nrodogarante,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select dias from credito where cod_venta=vt.cod_venta limit 1),0) as diasgracia,
		IFNULL((Select interes from credito where cod_venta=vt.cod_venta limit 1),0) as intereses,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
		IFNULL((Select sum(precio_producto*cantidad_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalventadetalle,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
				IFNULL((Select sum(descuento) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalDescuentodetalle,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,vt.fecha_insert,vt.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor,
		(Select accesocredito from cliente where cod_cliente=cod_clienteFK) as accesocredito , cob_ex
		from venta vt where cod_venta!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0   ".$condicionfecha.$condicionAgenteCredito.$condiciontipoComprobante.$condicionfechafiltro.$condicionnroventa.$condiciondocumento.$condicioncliente.$condiciontelef.$condicionCuenta.$condiciontipoventa.$condicionCodLocal.$condicionVendedor."  order by vt.cod_venta asc limit ".$registrocargado.", 35" ;
	
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor+$registrocargado;
 $TotalVentas= $totalventa;
 $TotalPagos= $totalpagado;
 $TotalDeuda= $totalpendiente;
  $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
			$cob_ex=$valor['cob_ex'];
			
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $Vendedor1=utf8_encode($valor['Vendedor1']);
		  	  $Vendedor2=utf8_encode($valor['Vendedor2']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $cobradornombre=utf8_encode($valor['cobradornombre']);
		  	  $nroCancelado=utf8_encode($valor['nroCancelado']);
		  	  $montodevuelto=utf8_encode($valor['montodevuelto']);
			  $nombrevendedor1=utf8_encode($valor['nombrevendedor1']);
		  	  $nombrevendedor2=utf8_encode($valor['nombrevendedor2']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $nrodetalle=($valor['nrodetalle']);
		  	  $nroCouta=($valor['nroCouta']);
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $Garante=utf8_encode($valor['Garante']);
			  $nrodocliente=utf8_encode($valor['nrodocliente']);
			  $nrodogarante=utf8_encode($valor['nrodogarante']);
			  $diasgracia=utf8_encode($valor['diasgracia']);
			  $intereses=utf8_encode($valor['intereses']);
			  $telefono=utf8_encode($valor['telefono']);
			  $tipo_comprobante=utf8_encode($valor['tipo_comprobante']);
			  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			  $totalDescuentodetalle=utf8_encode($valor['totalDescuentodetalle']);
			  $totalventadetalle=utf8_encode($valor['totalventadetalle']);
			   $insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']);
$accesocredito = utf8_encode($valor['accesocredito']);
			   $controlFecha=date('Y-m-d');
			 
	  $FacturaActual=  $num_factura;
			 
			    $datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
				$totaldescuento=$datos[0]+$totalDescuentodetalle;
                $totalintereses=$datos[1];
                //$datos[2]=$TotalEnDeuda;
                $totalpagado=$datos[3];
                //$datos[4]=$TotalAPagar;
                // $datos[5]=$TotalDiasAtrasado;
                // $datos[6]=$nrodecuotasatrazado;
                // $datos[7]=$TotalApagarSinInteres;
                $deuda=$datos[8];
                $SubTotalDeuda=$datos[11];
				 if($SubTotalDeuda==0){
				 $SubTotalDeuda=$total_venta;
			 }
			 $subtotalventa=$totalventadetalle-$totaldescuento;
                $totalinterespadado=$datos[12];
                $TotalPagoSininteres=$datos[13];
			    $styleCancelado="";
			 $totalpagado=$totalpagado+$pago;	
 $deudapendiente=$total_venta-$totalpagado;			 
			   if($nroCancelado==0){
			  $TotalVentas=$total_venta+$TotalVentas;
              $TotalPagos= $TotalPagos+$totalpagado;
              $TotalDeuda= $TotalDeuda+$deuda;
			   }else{
				   $deudapendiente=0;
				   $totalpagado=($totalpagado-$montodevuelto);
				  if($totalpagado<0){
					  $totalpagado=0;
				  }
				   $TotalPagos= $TotalPagos+$totalpagado;
				   $TotalVentas=$total_venta+$TotalVentas;
				   $styleCancelado="background-color: #FFEB3B;color:#000";
			   }
if($TipoVenta=="CREDITO"){
 $cuotas=$nroCouta."/".buscarcantidadcuotapagados($cod_venta);
}else{
	$cuotas="CONTADO";
}
if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}


if($cob_ex!="Local"){
	 $styleCancelado="background-color: #4caf50; color: #f7f4f4;";
}


 $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerelementohistroialventa(this)' style='$styleCancelado' >
<td  id='td_datos_1'  style='width:4%'>".$fecha_venta."</td>
<td                   style='width:5%'>".$nrof."</td>
<td  id='td_datos_40' style='width:4%'>".$tipo_comprobante ."</td>
<td                   style='width:5%'>".$nrodocliente."</td>
<td  id='td_datos_2'  style='width:8%'>".$clientenombre."</td>
<td  id='td_datos_34' style='width:4%'>".$telefono."</td>

<td  id='td_datos_12' style='width:5%'>".$TipoVenta ."</td>
<td  id='td_datos_5'  style='width:5%'>". number_format($totalventadetalle,'0',',','.') ."</td>
<td  id='td_datos_29' style='width:5%'>". number_format($totaldescuento,'0',',','.') ."</td>
<td  id='td_datos_38' style='width:5%'>". number_format($subtotalventa,'0',',','.') ."</td>
<td  id=''            style='width:5%'>". number_format($totalinterespadado,'0',',','.') ."</td>
<td  id=''            style='width:5%'>". number_format($TotalPagoSininteres,'0',',','.') ."</td>
<td  id='td_datos_6'  style='width:5%'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='td_datos_24' style='width:5%'>". number_format($totalintereses,'0',',','.') ."</td>
<td  id='td_datos_7'  style='width:5%'>". number_format($deuda,'0',',','.') ."</td>
<td  id=''            style='width:5%'>".$cuotas ."</td>
<td  id='td_datos_4'  style='width:5%'>".$cobradornombre."</td>
<td  id=''            style='width:5%'>".$nombrelocal ."</td>
<td  id='td_datos_15' style='width:5%'>".$nombrevendedor1."</td>
<td  id='td_datos_42' style='width:5%'>".$cob_ex."</td>



<td id='td_datos_13'  style='display:none'>".$num_factura."</td>
<td  id=''            style='display:none'>".$nombrevendedor1." - ".$nombrevendedor2."</td>
<td  id='td_datos_3'  style='display:none'>".$Vendedor1."</td>
<td  id='td_datos_14' style='display:none'>".$Vendedor2."</td>
<td  id='td_datos_16' style='display:none'>".$nombrevendedor2."</td>
<td  id='td_datos_8'  style='display:none'>".$cod_venta ."</td>
<td  id='td_datos_9'  style='display:none'>".$cod_usuarioFK ."</td>
<td  id='td_datos_10' style='display:none'>".$cod_clienteFK ."</td>
<td  id='td_datos_11' style='display:none'>".$cod_cobradorFK ."</td>
<td  id='td_datos_18' style='display:none'>".$TipoPago ."</td>
<td  id='td_datos_19' style='display:none'>".$cantidadcuota ."</td>
<td  id='td_datos_20' style='display:none'>". number_format($Monto,'0',',','.') ."</td>
<td  id='td_datos_21' style='display:none'>".$fechaprimerpago ."</td>
<td  id='td_datos_22' style='display:none'>".$comision ."</td>
<td  id='td_datos_23' style='display:none'>".$cod_local ."</td>
<td  id='td_datos_25' style='display:none'>".number_format($intereses,'0',',','.')."</td>
<td  id='td_datos_26' style='display:none'>".$diasgracia ."</td>
<td  id='td_datos_27' style='display:none'>".$nrodetalle ."</td>
<td  id='td_datos_30' style='display:none'>".$idGaranteFk ."</td>
<td  id='td_datos_31' style='display:none'>".$Garante ."</td>
<td  id='td_datos_32' style='display:none'>".$nrodocliente ."</td>
<td  id='td_datos_33' style='display:none'>".$nrodogarante ."</td>
<td  id='td_datos_35' style='display:none'>".$tipo_comprobante ."</td>
<td  id='td_datos_36' style='display:none'>".$puntoexpedicion ."</td>
<td  id='td_datos_37' style='display:none'>".number_format($deudapendiente,'0',',','.') ."</td>
<td  id='td_datos_39' style='display:none'>".$accesocredito."</td>



<td  id='td_datos_41'  style='display:none'>".$FacturaActual."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";

		
			  
			  
	  }
 }

 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}


function buscarexpedientescancelados($cliente,$buscar){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	  $sql= "Select fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,cod_local,pago,
		 (Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
			IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select motivo from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as motivo,
		IFNULL((Select fecha from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as fechacancelacion,
		IFNULL((Select sum(precio_producto*cantidad_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalventadetalle,
		IFNULL((Select sum(descuento) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalDescuentodetalle,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt where vt.cod_clienteFK ='".$cliente."' 
        and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)>0 		";
		
		
		  
   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
  $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $Vendedor1=utf8_encode($valor['Vendedor1']);
		  	  $Vendedor2=utf8_encode($valor['Vendedor2']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $cobradornombre=utf8_encode($valor['cobradornombre']);
		  	  
			  $nombrevendedor1=utf8_encode($valor['nombrevendedor1']);
		  	  $nombrevendedor2=utf8_encode($valor['nombrevendedor2']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $nrodetalle=($valor['nrodetalle']);
			  $totalpagado=utf8_encode($valor['totalpagado']);
			  $nroCouta=utf8_encode($valor['nroCouta']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $montodevuelto=utf8_encode($valor['montodevuelto']);
			  $motivo=utf8_encode($valor['motivo']);
			  $fechacancelacion=utf8_encode($valor['fechacancelacion']);
			  $totalventadetalle=utf8_encode($valor['totalventadetalle']);
			  $totalDescuentodetalle=utf8_encode($valor['totalDescuentodetalle']);
			   
			  $totalRegistro=$totalRegistro+1;
			$totalpagado=$totalpagado+$pago;
			 
			  $totaldescuentos=$totalDescuentodetalle+$totaldescuento;
			  $subtotalventa=$totalventadetalle-$totaldescuentos;
			  $deuda=$subtotalventa-$totalpagado;
			
			  $totalRegistro=$totalRegistro+1;
			  if($deuda<0){
				$deuda=0;  
			  }else{
				 
		$deuda=$deuda-$totaldescuento;
			  }
		
		
		 $TotalVentas=$subtotalventa+$TotalVentas;
 $TotalPagos= $TotalPagos+$totalpagado;
 $TotalDeuda= $TotalDeuda+$deuda;
  if($TipoVenta=="CREDITO"){
 $cuotas=$nroCouta."/".buscarcantidadcuotapagados($cod_venta);
}else{
	$cuotas="CONTADO";
}
 $styleName=CargarStyleTable($styleName);
				  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td id='' style='width:10%'>".$num_factura."</td>
<td  id='' style='width:5%'>".$cuotas ."</td>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td  id='' style='width:10%'>".$fechacancelacion."</td>
<td  id='' style='width:10%'>".$motivo."</td>
<td  id='' style='width:5%'>". number_format($montodevuelto,'0',',','.') ."</td>
<td  id='' style='width:5%'>". number_format($totalventadetalle,'0',',','.') ."</td>
<td  id='' style='width:5%'>". number_format($totaldescuentos,'0',',','.') ."</td>
<td  id='' style='width:5%'>". number_format($subtotalventa,'0',',','.') ."</td>
<td  id='' style='width:5%'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='' style='width:5%'>". number_format($deuda,'0',',','.') ."</td>
</tr>
</table>";

		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function historialventacancelado($filtrofecha,$fecha1,$fecha2,$nroventa,$cliente,$codlocal){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	 $condicionfecha="";
	if($fecha1!="" && $fecha2!=""){
		 $condicionfecha=" and cl.fecha>='$fecha1' and cl.fecha<='$fecha2'";
	}
	$condicionfiltrofecha="";
	if($filtrofecha!=""){
		$condicionfiltrofecha="and cl.fecha='$fecha1'";
	}
	$condicioncodlocal="";
	if($codlocal!=""){
		$condicioncodlocal="and vt.cod_local='$codlocal'";
	}
	$condicionnroventa="";
	if($nroventa!=""){
		$condicionnroventa="and vt.num_factura like '%".$nroventa."%'";
	}
	$condicioncliente="";
	if($cliente!=""){
		$condicioncliente="and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	}
		 $sql= "Select vt.puntoexpedicion,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.cod_venta,vt.comision,vt.pago,
		 (Select nombre from vendedor where idvendedor=vt.Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=vt.Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=vt.cod_usuarioFK) as usuarionombre,
		(Select Calificacion from cliente where cod_cliente=vt.cod_clienteFK) as Calificacion,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA') as cantidadcuota,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
			(Select count(cod_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta) as nrodetalle,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		cl.montodevuelto,cl.motivo,cl.fecha as fechacancelacion,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt inner join cancelaciones cl on cl.cod_venta=vt.cod_venta 
		where vt.fecha_venta!='0' ".$condicioncliente.$condicionnroventa.$condicioncodlocal.$condicionfiltrofecha.$condicionfecha." limit 50 ";
	


   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
  $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $cantidadcambio=utf8_encode($valor['cantidadcambio']);
			    $nrodetalle=utf8_encode($valor['nrodetalle']);
			    $cantidaddevuelto=utf8_encode($valor['cantidaddevuelto']);
			    $fechaultimopago=utf8_encode($valor['fechaultimopago']);
			    $Calificacion=utf8_encode($valor['Calificacion']);
			    $nroCouta=utf8_encode($valor['nroCouta']);
			    $totaldescuento=utf8_encode($valor['totaldescuento']);
			    $montodevuelto=utf8_encode($valor['montodevuelto']);
			    $motivo=utf8_encode($valor['motivo']);
			    $fechacancelacion=utf8_encode($valor['fechacancelacion']);
			    $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			    $nombrelocal=utf8_encode($valor['nombrelocal']);
			$totalRegistro=$totalRegistro+1;
			$totalpagado=$totalpagado+$pago;
			
			   
			  $deuda=$total_venta-$totalpagado;
			
			  $totalRegistro=$totalRegistro+1;
			  if($deuda<0){
				$deuda=0;  
			  }else{
		$deuda=$deuda-$totaldescuento;
			  }
		
		
		 $TotalVentas=$total_venta+$TotalVentas;
 $TotalPagos= $TotalPagos+$totalpagado;
 $TotalDeuda= $TotalDeuda+$deuda;
 if($TipoVenta=="CREDITO"){
 $cuotas=$nroCouta."/".buscarcantidadcuotapagados($cod_venta);
}else{
	$cuotas="CONTADO";
}
	  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

 $styleName=CargarStyleTable($styleName);
				  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td  id='' style='width:10%'>".$clientenombre ."</td>
<td  id='' style='width:10%'>".$cuotas ."</td>
<td  id='' style='width:10%'>".$fechacancelacion."</td>


<td  id='' style='width:10%'>". number_format($total_venta,'0',',','.') ."</td>
<td  id='' style='width:10%'>". number_format($totalpagado,'0',',','.') ."</td>

<td  id='' style='width:10%'>". number_format($montodevuelto,'0',',','.') ."</td>
<td  id='' style='width:10%'>".$motivo."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


		
			  
			  
	  }
 }
 
  $sql= "Select vt.puntoexpedicion,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.cod_venta,vt.comision,vt.pago,
		 (Select nombre from vendedor where idvendedor=vt.Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=vt.Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=vt.cod_usuarioFK) as usuarionombre,
		(Select Calificacion from cliente where cod_cliente=vt.cod_clienteFK) as Calificacion,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA') as cantidadcuota,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
			(Select count(cod_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta) as nrodetalle,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		cl.montodevuelto,cl.motivo,cl.fecha as fechacancelacion,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt inner join cancelaciones cl on cl.cod_venta=vt.cod_venta 
		where vt.fecha_venta!='0' ".$condicioncliente.$condicionnroventa.$condicioncodlocal.$condicionfiltrofecha.$condicionfecha;
	  $stmt = $mysqli->prepare($sql);  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregistro= $valor;
 
  mysqli_close($mysqli); 
   
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" =>  number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function mashistorialventacancelado($filtrofecha,$fecha1,$fecha2,$nroventa,$cliente,$codlocal,$registrocargado){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	 $condicionfecha="";
	if($fecha1!="" && $fecha2!=""){
		 $condicionfecha=" and cl.fecha>='$fecha1' and cl.fecha<='$fecha2'";
	}
	$condicionfiltrofecha="";
	if($filtrofecha!=""){
		$condicionfiltrofecha="and cl.fecha='$fecha1'";
	}
	$condicioncodlocal="";
	if($codlocal!=""){
		$condicioncodlocal="and vt.cod_local='$codlocal'";
	}
	$condicionnroventa="";
	if($nroventa!=""){
		$condicionnroventa="and vt.num_factura like '%".$nroventa."%'";
	}
	$condicioncliente="";
	if($cliente!=""){
		$condicioncliente="and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	}
		 $sql= "Select vt.puntoexpedicion,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.cod_venta,vt.comision,vt.pago,
		 (Select nombre from vendedor where idvendedor=vt.Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=vt.Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=vt.cod_usuarioFK) as usuarionombre,
		(Select Calificacion from cliente where cod_cliente=vt.cod_clienteFK) as Calificacion,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA') as cantidadcuota,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
			(Select count(cod_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta) as nrodetalle,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		cl.montodevuelto,cl.motivo,cl.fecha as fechacancelacion,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt inner join cancelaciones cl on cl.cod_venta=vt.cod_venta 
		where vt.fecha_venta!='0' ".$condicioncliente.$condicionnroventa.$condicioncodlocal.$condicionfiltrofecha.$condicionfecha." limit ".$registrocargado.", 50 ";
	


   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $styleName="tableRegistroSearch";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor+$registrocargado;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $cantidadcambio=utf8_encode($valor['cantidadcambio']);
			    $nrodetalle=utf8_encode($valor['nrodetalle']);
			    $cantidaddevuelto=utf8_encode($valor['cantidaddevuelto']);
			    $fechaultimopago=utf8_encode($valor['fechaultimopago']);
			    $Calificacion=utf8_encode($valor['Calificacion']);
			    $nroCouta=utf8_encode($valor['nroCouta']);
			    $totaldescuento=utf8_encode($valor['totaldescuento']);
			    $montodevuelto=utf8_encode($valor['montodevuelto']);
			    $motivo=utf8_encode($valor['motivo']);
			    $fechacancelacion=utf8_encode($valor['fechacancelacion']);
			    $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			    $nombrelocal=utf8_encode($valor['nombrelocal']);
			$totalRegistro=$totalRegistro+1;
			$totalpagado=$totalpagado+$pago;
			
			   
			  $deuda=$total_venta-$totalpagado;
			
			  $totalRegistro=$totalRegistro+1;
			  if($deuda<0){
				$deuda=0;  
			  }else{
		$deuda=$deuda-$totaldescuento;
			  }
		
		
		 $TotalVentas=$total_venta+$TotalVentas;
 $TotalPagos= $TotalPagos+$totalpagado;
 $TotalDeuda= $TotalDeuda+$deuda;
 if($TipoVenta=="CREDITO"){
 $cuotas=$nroCouta."/".buscarcantidadcuotapagados($cod_venta);
}else{
	$cuotas="CONTADO";
}
	  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
 $styleName=CargarStyleTable($styleName);
				  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td  id='' style='width:10%'>".$clientenombre ."</td>
<td  id='' style='width:10%'>".$cuotas ."</td>
<td  id='' style='width:10%'>".$fechacancelacion."</td>


<td  id='' style='width:10%'>". number_format($total_venta,'0',',','.') ."</td>
<td  id='' style='width:10%'>". number_format($totalpagado,'0',',','.') ."</td>

<td  id='' style='width:10%'>". number_format($montodevuelto,'0',',','.') ."</td>
<td  id='' style='width:10%'>".$motivo."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" =>  number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarclientesincativos($zona,$documento,$cliente,$nrotelefono,$Vendedor,$Local){
	$mysqli=conectar_al_servidor();
	 $totalRegistro=0;
	 $pagina="";
	 $fechahoy=date('Y-m-d');
	 $condicionVendedor="";
	 if($Vendedor!=""){
		  $condicionVendedor=" and (Select idvendedor from vendedor where idvendedor=vt.Vendedor1 )= $Vendedor ";
	 }
	 
	  $condicionlocal="";
	 if($Local!="SELECCIONAR"){
		  $condicionlocal=" and (Select cod_local from local where cod_local=vt.cod_local )= $Local ";
	 }
	 
	 $condicionzona="";
	 if($zona!=""){
		  $condicionzona=" and (Select count(cod_cliente) from cliente where cod_cliente=cod_clienteFK  and idzonaFk='$zona') > 0";
	 }
	 $condiciondocumento="";
	 if($documento!=""){
		  $condiciondocumento=" and (Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK ) = '$documento'";
	 }
	 $condicioncliente="";
	 if($cliente!=""){
		  $condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	 }
	 $condicionnrotele="";
	 if($nrotelefono!=""){
		  $condicionnrotele=" and (Select telefono from persona where cod_persona=vt.cod_clienteFK)= '$nrotelefono'";
	 }
	 
	  $sql= "Select vt.fecha_venta,(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,vt.cod_clienteFK,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK ) as nrodocliente,
(Select whapp from cliente where cod_cliente=vt.cod_clienteFK ) as whapp,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
(Select fecha_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ) as UltimaVenta
from  venta vt
inner join pago pg on pg.cod_venta_fk=vt.cod_venta
where
vt.cod_clienteFK!='10' and pg.Fecha < date_format(date_add(NOW(), INTERVAL -2 MONTH), '%Y/%m/%d') and  
(select sum(Monto-descuento) from credito where cod_venta=
(Select cod_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ))-
(select sum(Monto) from pago where cod_venta_fk=
(Select cod_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ) and Tipo='Pago Cuota')=0  ".$condicionzona.$condiciondocumento.$condicioncliente.$condicionnrotele.$condicionVendedor.$condicionlocal."
group by vt.cod_clienteFK  limit 50";
		
		// echo($sql);
		// exit;
		  
    $styleName="tableRegistroSearch";
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $nrodocliente=utf8_encode($valor['nrodocliente']);
		  	  $whapp=utf8_encode($valor['whapp']);
		  	  $telefono=utf8_encode($valor['telefono']);
			   $UltimaVenta=utf8_encode($valor['UltimaVenta']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	 
		 $styleName=CargarStyleTable($styleName);	  
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistrosms' name='trEnviosms' onclick='obtenerdatosvistaclienteinactivo(this)'>
<td  id='td_id' style='display:none'>".$cod_clienteFK."</td>
<td  style='width:20%'>".$nrodocliente."</td>
<td  style='width:20%'>".$clientenombre."</td>
<td id='td_nro' style='width:20%'>".$telefono."</td>
<td  style='width:20%'>".$whapp."</td>
<td  style='width:20%'>".$UltimaVenta."</td>
</tr>
</table>";

		
			  
			  
	  }
 }
 $sql= "Select vt.fecha_venta
from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta 
inner join pago pg on pg.cod_venta_fk=vt.cod_venta
where
vt.cod_clienteFK!='10' and pg.Fecha < date_format(date_add(NOW(), INTERVAL -2 MONTH), '%Y/%m/%d') and  
(select sum(Monto-descuento) from credito where cod_venta=
(Select cod_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ))-
(select sum(Monto) from pago where cod_venta_fk=
(Select cod_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ) and Tipo='Pago Cuota')=0
 ".$condicionzona.$condiciondocumento.$condicioncliente.$condicionnrotele.$condicionVendedor.$condicionlocal."
group by vt.cod_clienteFK ";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregistro= $valor;
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function buscarmasclientesincativos($zona,$documento,$cliente,$nrotelefono,$registrocargado,$Vendedor,$Local){
	$mysqli=conectar_al_servidor();
	 $totalRegistro=0;
	 $pagina="";
	 
	 $condicionVendedor="";
	 if($Vendedor!=""){
		   $condicionVendedor=" and (Select idvendedor from vendedor where idvendedor=vt.Vendedor1 )= $Vendedor ";
	 }
	 
	  $condicionlocal="";
	 if($Local!="SELECCIONAR"){
		  $condicionlocal=" and (Select cod_local from local where cod_local=vt.cod_local )= $Local ";
	 }
	 
	 $condicionzona="";
	 if($zona!=""){
		  $condicionzona=" and (Select count(cod_cliente) from cliente where cod_cliente=cod_clienteFK  and idzonaFk='$zona') > 0";
	 }
	 $condiciondocumento="";
	 if($documento!=""){
		  $condiciondocumento=" and (Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK ) = '$documento'";
	 }
	 $condicioncliente="";
	 if($cliente!=""){
		  $condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	 }
	 $condicionnrotele="";
	 if($nrotelefono!=""){
		  $condicionnrotele=" and (Select telefono from persona where cod_persona=vt.cod_clienteFK)= '$nrotelefono'";
	 }
	 
	  $sql= "Select vt.fecha_venta,(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,vt.cod_clienteFK,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK ) as nrodocliente,
(Select whapp from cliente where cod_cliente=vt.cod_clienteFK ) as whapp,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
(Select fecha_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ) as UltimaVenta
from  venta vt
inner join pago pg on pg.cod_venta_fk=vt.cod_venta
where
vt.cod_clienteFK!='10' and pg.Fecha < date_format(date_add(NOW(), INTERVAL -2 MONTH), '%Y/%m/%d') and  
(select sum(Monto-descuento) from credito where cod_venta=
(Select cod_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ))-
(select sum(Monto) from pago where cod_venta_fk=
(Select cod_venta from venta v where v.cod_clienteFK=vt.cod_clienteFK order by fecha_venta desc limit 1 ) and Tipo='Pago Cuota')=0 
 ".$condicionzona.$condiciondocumento.$condicioncliente.$condicionnrotele.$condicionVendedor.$condicionlocal."
group by vt.cod_clienteFK order by fecha_venta desc limit ".$registrocargado.", 50";
		
		
		  
   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor+$registrocargado;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
  $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $nrodocliente=utf8_encode($valor['nrodocliente']);
		  	  $whapp=utf8_encode($valor['whapp']);
			  $UltimaVenta=utf8_encode($valor['UltimaVenta']);
		  	  $telefono=utf8_encode($valor['telefono']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	 
			   $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName'  border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistrosms' name='trEnviosms' onclick='obtenerdatosvistaclienteinactivo(this)'>
<td  id='td_id' style='display:none'>".$cod_clienteFK."</td>
<td  style='width:20%'>".$nrodocliente."</td>
<td  style='width:20%'>".$clientenombre."</td>
<td id='td_nro' style='width:20%'>".$telefono."</td>
<td  style='width:20%'>".$whapp."</td>
<td  style='width:20%'>".$UltimaVenta."</td>
</tr>
</table>";

		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarexpedientes($buscar){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	
    	$sql= "Select tipo_comprobante,puntoexpedicion,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,cod_local,pago,
		 (Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as GaranteDocumento,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		IFNULL((Select totalinteres from totalesdeudaventa where cod_venta=cod_ventaFk),0) as totalinteres,
		IFNULL((Select deudaactual from totalesdeudaventa where cod_venta=cod_ventaFk),0) as deudaactual,
		(Select fechaactualizacion from totalesdeudaventa where cod_venta=cod_ventaFk) as fechaactualizacion,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select motivo from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as motivo,
		IFNULL((Select fecha from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as fechacancelacion,
			IFNULL((Select sum(precio_producto*cantidad_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalventadetalle,
		IFNULL((Select sum(descuento) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalDescuentodetalle,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt where vt.cod_clienteFK ='".$buscar."'  and
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 ";
	
		
		
		  
   
    $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalVenta=0;
 $totalPagado=0;
 $totalDeuda=0;
 $controlVentas="";
  $styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $Vendedor1=utf8_encode($valor['Vendedor1']);
		  	  $Vendedor2=utf8_encode($valor['Vendedor2']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $cobradornombre=utf8_encode($valor['cobradornombre']);
		  	  
			  $nombrevendedor1=utf8_encode($valor['nombrevendedor1']);
		  	  $nombrevendedor2=utf8_encode($valor['nombrevendedor2']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $nrodetalle=($valor['nrodetalle']);
			  $totalpagado=utf8_encode($valor['totalpagado']);
			  $cantidadcambio=utf8_encode($valor['cantidadcambio']);
			  $cantidaddevuelto=utf8_encode($valor['cantidaddevuelto']);
			  $fechaultimopago=utf8_encode($valor['fechaultimopago']);
			  $nroCouta=utf8_encode($valor['nroCouta']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $Garante=utf8_encode($valor['Garante']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			     $totalinteres=utf8_encode($valor['totalinteres']);
			  $deudaactual=utf8_encode($valor['deudaactual']);
			  $fechaactualizacion=utf8_encode($valor['fechaactualizacion']);
			  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			  $totalventadetalle=utf8_encode($valor['totalventadetalle']);
			  $totalDescuentodetalle=utf8_encode($valor['totalDescuentodetalle']);
			  $GaranteDocumento=utf8_encode($valor['GaranteDocumento']);
			  
			  $totalRegistro=$totalRegistro+1;	

			  
			   $datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
				$totaldescuento=$datos[0];
                $totalintereses=$datos[1];
                //$datos[2]=$TotalEnDeuda;
                $totalpagado=$datos[3];
                //$datos[4]=$TotalAPagar;
                // $datos[5]=$TotalDiasAtrasado;
                // $datos[6]=$nrodecuotasatrazado;
                // $datos[7]=$TotalApagarSinInteres;
                $deuda=$datos[8];
		 $totaldescuentos=$totalDescuentodetalle+$totaldescuento;
		 $subTotal=$totalventadetalle-$totaldescuentos;
		 $totalVenta=$subTotal+$totalVenta;
 $totalPagado= $totalPagado+$totalpagado;
 $totalDeuda= $totalDeuda+$deuda;
		  	  	$tituloPagos="";
if($controlVentas!=$cod_venta){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$num_factura."</p>";
	$controlVentas=$cod_venta;
}
if($TipoVenta=="CREDITO"){
 $cuotas=$nroCouta."/".buscarcantidadcuotapagados($cod_venta);
}else{
	$cuotas="CONTADO";
}
	$styleGrilla="";
	  if($deuda>0){
		  $styleGrilla="background-color:#FF5722;color:#fff";
	  }
	  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
 $styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' style='$styleGrilla' >
<td  id='' style='width:8%'>".$fecha_venta."</td>
<td  id='' style='width:8%'>".$nrof."</td>
<td  id='' style='width:8%'>".$GaranteDocumento." - ".$Garante."</td>
<td  id='' style='width:8%'>". number_format($totalventadetalle,'0',',','.') ."</td>
<td  id='' style='width:8%'>". number_format($totaldescuentos,'0',',','.') ."</td>
<td  id='' style='width:8%'>". number_format($subTotal,'0',',','.') ."</td>
<td  id='' style='width:8%'>". number_format($totalintereses,'0',',','.') ."</td>
<td  id='' style='width:8%'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='' style='width:8%'>". number_format($deuda,'0',',','.') ."</td>
<td  id='' style='width:8%'>".$cuotas."</td>
<td  id='' style='width:8%'>".$nombrelocal ."</td>
</tr>
</table>";

		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($totalVenta,'0',',','.'),"5" =>  number_format($totalPagado,'0',',','.'),"6" => number_format($totalDeuda,'0',',','.'));
echo json_encode($informacion);	
exit;
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

function actualizarTotalEnVenta($idcredito,$total,$totalinteres,$totaldeuda){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update credito set total=?,totalinteres=?,totaldeuda=? where idcredito=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$total,$totalinteres,$totaldeuda,$idcredito); 
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
 mysqli_close($mysqli); 

}

function ganaciaventa($nroventa,$fecha1,$fecha2,$cliente,$nrodocumento,$fechafiltro,$cod_local,$tipoventa){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	
	   $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		  $condiciontipoventa="";
		 if($tipoventa!=""){
			$condiciontipoventa=" and TipoVenta='$tipoventa'"; 
		 }
		 $condicioncliente="";
		 if($cliente!=""){
			$condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'"; 
		 }
		 $condicionnroventa="";
		 if($nroventa!=""){
			$condicionnroventa=" and num_factura like '%".$nroventa."%'"; 
		 }
		 $condicionrodocumento="";
		 if($nrodocumento!=""){
			$condicionrodocumento=" and (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) = '".$nrodocumento."'"; 
		 }

		 $condicionfechafiltro="";
		 if($fechafiltro!=""){
			$condicionfechafiltro=" and fecha_venta>='".$fechafiltro."' "; 
		 }
		 $condicionfecha="";
		 if($fecha1!="" && $fecha2!=""){
			$condicionfecha="and fecha_venta>='".$fecha1."' and fecha_venta<='".$fecha2."'"; 
		 }
		 
	 
	 $sql= "Select fecha_venta,total_venta,num_factura,puntoexpedicion,vt.TipoVenta,
	 (Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
	  (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		IFNULL((select sum(dtv1.comision) from detalle_venta dtv1  where vt.cod_venta=dtv1.cod_ventaFK),0) as comisionvendedor,
				IFNULL((select sum(dtv2.subPrecioCompra*dtv2.cantidad_detalle) from detalle_venta dtv2  where vt.cod_venta=dtv2.cod_ventaFK),0) as costototal,
			(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		IFNULL((select sum(pg1.Monto) from pago pg1  where vt.cod_venta=pg1.cod_venta_fk and tipo='Pago Cuota'),0) as totalpagado,
		IFNULL((select sum((pg2.comision*pg2.monto)/100) from pago pg2  where vt.cod_venta=pg2.cod_venta_fk),0) as comisioncobrador
		from venta vt  where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
		".$condicioncliente.$condicionnroventa.$condicionrodocumento.$condicionfechafiltro.$condicionfecha.$condicionCodLocal.$condiciontipoventa."
		limit 50";
   $totalescosto=0;
   $totalescomision=0;
   $totalespagado=0;
   $totalesevaluacion=0;
   
  $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 
  $TotalVenta=0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $comisionvendedor=utf8_encode($valor['comisionvendedor']);
		  	  $costototal=utf8_encode($valor['costototal']);
		  	  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $comisioncobrador=utf8_encode($valor['comisioncobrador']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $nrodocliente=utf8_encode($valor['nrodocliente']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
			    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
		  	  $evaluacion=$totalpagado-($costototal+$comisionvendedor+$comisioncobrador);
			  if($evaluacion<0){
				  $evaluacion=0;
			  }
			  $totalescosto=$totalescosto+$costototal;
			  $totalescomision=$totalescomision+$comisioncobrador+$comisionvendedor;
			  $totalespagado=$totalespagado+$totalpagado;
			  $totalesevaluacion=$totalesevaluacion+$evaluacion;
			  $TotalVenta= $TotalVenta + $total_venta;
			   $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'  >
<td id='' style='width:7%'>".$nrof."</td>
<td id='' style='width:12%'>".$clientenombre."</td>
<td id='' style='width:6%'>".$nrodocliente."</td>
<td  id='' style='width:7%'>".$fecha_venta."</td>
<td  id='' style='width:7%'>".number_format($comisionvendedor,'0',',','.')."</td>
<td  id='' style='width:7%'>".number_format($comisioncobrador,'0',',','.')."</td>
<td  id='' style='width:7%'>".number_format($costototal,'0',',','.')."</td>
<td  id='' style='width:6%'>".number_format($totalpagado,'0',',','.')."</td>
<td  id='' style='width:6%'>".number_format($total_venta,'0',',','.')."</td>
<td  id='' style='width:7%'>".number_format($evaluacion,'0',',','.')."</td>
<td  id='' style='width:5%'>".$TipoVenta."</td>
<td  id='' style='width:7%'>".$nombrelocal."</td>
</tr>
</table>";

	  
	  }
  
 }
 $sql= "Select fecha_venta,total_venta,num_factura,puntoexpedicion,vt.TipoVenta,
	 (Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
	  (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		IFNULL((select sum(dtv1.comision) from detalle_venta dtv1  where vt.cod_venta=dtv1.cod_ventaFK),0) as comisionvendedor,
				IFNULL((select sum(dtv2.subPrecioCompra*dtv2.cantidad_detalle) from detalle_venta dtv2  where vt.cod_venta=dtv2.cod_ventaFK),0) as costototal,
			(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		IFNULL((select sum(pg1.Monto) from pago pg1  where vt.cod_venta=pg1.cod_venta_fk and tipo='Pago Cuota'),0) as totalpagado,
		IFNULL((select sum((pg2.comision*pg2.monto)/100) from pago pg2  where vt.cod_venta=pg2.cod_venta_fk),0) as comisioncobrador
		from venta vt  where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
		".$condicioncliente.$condicionnroventa.$condicionrodocumento.$condicionfechafiltro.$condicionfecha.$condicionCodLocal.$condiciontipoventa;
  $stmt = $mysqli->prepare($sql);  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregistro= $valor;

 mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalescosto,'0',',','.'),"4" => number_format($totalescomision,'0',',','.'),"5" => number_format($totalespagado,'0',',','.'),"6" => number_format($totalesevaluacion,'0',',','.'),"7"=>$nroRegistro,"8"=>number_format($TotalVenta,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function masganaciaventa($costoTotal,$VentaTotal,$nroventa,$fecha1,$fecha2,$cliente,$nrodocumento,$fechafiltro,$cod_local,$tipoventa,$totalcostos,$totalcomision,$totalpagado,$totalevaluacion,$registrocargado){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	
	   $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		  $condiciontipoventa="";
		 if($tipoventa!=""){
			$condiciontipoventa=" and TipoVenta='$tipoventa'"; 
		 }
		 $condicioncliente="";
		 if($cliente!=""){
			$condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'"; 
		 }
		 $condicionnroventa="";
		 if($nroventa!=""){
			$condicionnroventa=" and num_factura like '%".$nroventa."%'"; 
		 }
		 $condicionrodocumento="";
		 if($nrodocumento!=""){
			$condicionrodocumento=" and (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) = '".$nrodocumento."'"; 
		 }

		 $condicionfechafiltro="";
		 if($fechafiltro!=""){
			$condicionfechafiltro=" and fecha_venta>='".$fechafiltro."' "; 
		 }
		 $condicionfecha="";
		 if($fecha1!="" && $fecha2!=""){
			$condicionfecha="and fecha_venta>='".$fecha1."' and fecha_venta<='".$fecha2."'"; 
		 }
		 
	 
	 $sql= "Select fecha_venta,total_venta,num_factura,puntoexpedicion,vt.TipoVenta,
	 (Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
	  (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		IFNULL((select sum(dtv1.comision) from detalle_venta dtv1  where vt.cod_venta=dtv1.cod_ventaFK),0) as comisionvendedor,
				IFNULL((select sum(dtv2.subPrecioCompra*dtv2.cantidad_detalle) from detalle_venta dtv2  where vt.cod_venta=dtv2.cod_ventaFK),0) as costototal,
			(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		IFNULL((select sum(pg1.Monto) from pago pg1  where vt.cod_venta=pg1.cod_venta_fk and tipo='Pago Cuota'),0) as totalpagado,
		IFNULL((select sum((pg2.comision*pg2.monto)/100) from pago pg2  where vt.cod_venta=pg2.cod_venta_fk),0) as comisioncobrador
		from venta vt  where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
		".$condicioncliente.$condicionnroventa.$condicionrodocumento.$condicionfechafiltro.$condicionfecha.$condicionCodLocal.$condiciontipoventa."
		limit ".$registrocargado." , 50 ";
	 
	
	
		
	
		
   $totalescosto=$totalcostos;
   $totalescomision=$totalcomision;
   $totalespagado=$totalpagado;
   $totalesevaluacion=$totalevaluacion;
   
  $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor+$registrocargado;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $comisionvendedor=utf8_encode($valor['comisionvendedor']);
		  	  $costototal=utf8_encode($valor['costototal']);
		  	  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $comisioncobrador=utf8_encode($valor['comisioncobrador']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $nrodocliente=utf8_encode($valor['nrodocliente']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
			  $VentaTotal=$VentaTotal + $total_venta;
			    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
		  	  $evaluacion=$totalpagado-($costototal+$comisionvendedor+$comisioncobrador);
			  if($evaluacion<0){
				  $evaluacion=0;
			  }
			  $totalescosto=$totalescosto+$costototal;
			  $totalescomision=$totalescomision+$comisioncobrador+$comisionvendedor;
			  $totalespagado=$totalespagado+$totalpagado;
			  $totalesevaluacion=$totalesevaluacion+$evaluacion;
			   $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'  >
<td id='' style='width:7%'>".$nrof."</td>
<td id='' style='width:12%'>".$clientenombre."</td>
<td id='' style='width:6%'>".$nrodocliente."</td>
<td  id='' style='width:7%'>".$fecha_venta."</td>
<td  id='' style='width:7%'>".number_format($comisionvendedor,'0',',','.')."</td>
<td  id='' style='width:7%'>".number_format($comisioncobrador,'0',',','.')."</td>
<td  id='' style='width:7%'>".number_format($costototal,'0',',','.')."</td>
<td  id='' style='width:6%'>".number_format($totalpagado,'0',',','.')."</td>
<td  id='' style='width:6%'>".number_format($total_venta,'0',',','.')."</td>
<td  id='' style='width:7%'>".number_format($evaluacion,'0',',','.')."</td>
<td  id='' style='width:5%'>".$TipoVenta."</td>
<td  id='' style='width:7%'>".$nombrelocal."</td>
</tr>
</table>";

	  
	  }
	  
  
 }
 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalescosto,'0',',','.'),"4" => number_format($totalescomision,'0',',','.'),"5" => number_format($totalespagado,'0',',','.'),"6" => number_format($totalesevaluacion,'0',',','.'),"8" => number_format($VentaTotal,'0',',','.'),"7"=>$nroRegistro,"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}


function buscarCambiosRealizados($fechafiltro,$fecha1,$fecha2,$nrofactura,$cod_local)
{
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	$condicionCodLocal=" and (Select cod_local from venta where cam.cod_venta=venta.cod_venta)='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		 $condicionfecha="";
		 if($fecha1!="" && $fecha2!=""){
			  $condicionfecha="and fecha>='".$fecha1."' and fecha<='".$fecha2."'";
		 }
		 $condicionFiltrofecha="";
		 if($fechafiltro!=""){
			  $condicionFiltrofecha="and fecha='".$fecha1."'";
		 }
		 $condicionnrofactura="";
		 if($nrofactura!=""){
			  $condicionnrofactura="and (Select num_factura from venta where cam.cod_venta=venta.cod_venta) like '%".$nrofactura."%'";
		 }
		 
	
		 $sql= "Select idcambios,motivo,fecha,cant,cod_producto,cod_venta,
		 (Select Nombre from local l where l.cod_local=(Select cod_local from venta where cam.cod_venta=venta.cod_venta)) as nombrelocal,
	   (Select num_factura from venta where cam.cod_venta=venta.cod_venta) as num_factura,
	   (Select puntoexpedicion from venta where cam.cod_venta=venta.cod_venta) as puntoexpedicion,
	   (Select nombre_producto from producto where producto.cod_producto=cam.cod_producto) as nombreproducto
		 from cambios cam
		 where cod_venta!='-1' ".$condicionCodLocal.$condicionfecha.$condicionFiltrofecha.$condicionnrofactura." group by cod_producto,cod_venta ";
	 
		
		
		  
   
   $stmt = $mysqli->prepare($sql);
  
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
		  
		  
		      $idcambios=$valor['idcambios'];
		  	  $motivo=$valor['motivo'];
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $cod_producto=utf8_encode($valor['cod_producto']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $nombreproducto=utf8_encode($valor['nombreproducto']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $puntoexpedicion = utf8_encode($valor['puntoexpedicion']);   
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
		if($buscar=="Cambio"){
			 $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr  >
<td id='td_datos_13' style='width:10%'>".$nrof."</td>
<td  id='td_datos_1' style='width:10%'>".$fecha."</td>
<td  id='td_datos_2' style='width:10%'>".$nombreproducto."</td>
<td  id='td_datos_2' style='width:10%'>".buscarDatosDetallesVentaHistorialCambios($cod_venta)."</td>
<td  id='td_datos_3' style='width:10%'>".$motivo."</td>
<td  id='td_datos_3' style='width:10%'>".$nombrelocal."</td>
</tr>
		</table>";}
	if($buscar=="Garantia" || $buscar=="Devolucion"){
		 $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr  >
<td id='td_datos_13' style='width:10%'>".$nrof."</td>
<td  id='td_datos_1' style='width:10%'>".$fecha."</td>
<td  id='td_datos_2' style='width:10%'>".$nombreproducto."</td>
<td  id='td_datos_3' style='width:10%'>".$motivo."</td>
<td  id='td_datos_3' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
		}

		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarCambiosRealizadosExt($buscar,$motivo){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	
	
		 $sql= "Select cmp.fecha,vt.num_factura,vt.puntoexpedicion,pr.nombre_producto,dt.cant,
		 (Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		 (Select nombre_producto from producto pr1 where pr1.cod_producto=cmp.cod_productoFK) as nombreproductocambiado
		 from detallescambio dt inner join cambiarproducto cmp on cmp.idcambiarproducto=dt.idcambiarproductoFK
		 inner join producto pr on pr.cod_producto=dt.cod_productoFK 
		 inner join venta vt on vt.cod_venta=cmp.cod_ventaFK";
	 
	
	 
		
		
		  
   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $styleName="tableRegistroSearch";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $controlNombres="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cant=utf8_encode($valor['cant']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
		  	  $nombreproductocambiado=utf8_encode($valor['nombreproductocambiado']);
		  	    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

if($controlNombres!=$nombre_producto){
	$tituloProducto=$nombre_producto;
	$controlNombres=$nombre_producto;
}else{
	$tituloProducto="### ### ###";
}
	 $styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr  >
<td id='' style='width:10%'>".$nrof."</td>
<td  id='' style='width:10%'>".$fecha."</td>
<td  id='' style='width:10%'>".$tituloProducto."</td>
<td  id='' style='width:10%'>".$nombreproductocambiado."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
		</table>";
		


		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarDatosDetallesVentaHistorialCambios($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";

$productos = "";   

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



   
$nombre_producto = utf8_encode($valor['nombre_producto']);      
if($productos=="") {
	$productos=$nombre_producto;
}else{
	$productos=$productos.", ".$nombre_producto;
}



}
}
 mysqli_close($mysqli); 
return $productos;

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

function buscarnroventa()
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
  $informacion =array("1" => "exito","2" => $NroVenta);
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
	 $NroVenta="000000".$NroVenta;
 }else{
 if($NroVenta<100){
	 $NroVenta="00000".$NroVenta;
 }else{
	 if($NroVenta<1000){
	 $NroVenta="0000".$NroVenta;
    }else{
		if($NroVenta<10000){
	 $NroVenta="000".$NroVenta;
     }else{
		if($NroVenta<100000){
	 $NroVenta="00".$NroVenta;
     }else{
		if($NroVenta<1000000){
	 $NroVenta="0".$NroVenta;
     }
	 } 
	 }
	} 
 }
 }
  mysqli_close($mysqli); 
 return $NroVenta;

}


function buscarticket($cod_venta){
	
	$totalPagado=buscartotalpagob($cod_venta);
	$totalEntrega=buscartotalpagoc($cod_venta);
	$totalPagado=$totalPagado+$totalEntrega;
$totalVenta=buscartotalventa($cod_venta);
$paginaticket=buscar_detalles_venta($cod_venta);
 $datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
 $totalDeuda=$datos[4];
$datosventa=buscardatosventaticket($cod_venta);
$tituloCuota=$datosventa[0];
$monto=$datosventa[1];
$informacion =array("1" => "exito","2" =>number_format($totalPagado,'0',',','.') ,"3" =>  number_format($totalVenta,'0',',','.') ,"4" =>  number_format($totalDeuda,'0',',','.'),"5"=> $paginaticket,"6"=> $tituloCuota,"7"=> $monto,"8" =>  number_format($totalEntrega,'0',',','.'));
echo json_encode($informacion);	
exit;
}

/*Buscar */
function buscartotalventa($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select (total_venta-descuento) as totalVenta from venta where cod_venta='$buscar'";/*Sentencia para buscar registros*/
$totalVenta = 0;   
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



$totalVenta = utf8_encode($valor['totalVenta']);/*Obtenemos el registro mediante el nombre del atributo */      




}
}
 mysqli_close($mysqli); 
return $totalVenta;
}


function buscartotalpagob($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select sum(pg.Monto) as totalpago
 from pago pg 
 where pg.cod_venta_fk='$buscar'";/*Sentencia para buscar registros*/

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



$totalpago = utf8_decode($valor['totalpago']);/*Obtenemos el registro mediante el nombre del atributo */      



}
}
 mysqli_close($mysqli); 
return $totalpago;
}


function buscartotalpagoc($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select vt.pago as totalEntrega
 from venta vt 
 where vt.cod_venta='$buscar'";/*Sentencia para buscar registros*/

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



$totalpago = utf8_decode($valor['totalEntrega']);/*Obtenemos el registro mediante el nombre del atributo */      




}
}
 mysqli_close($mysqli); 
return $totalpago;
}


function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,
 IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0) as nroDevoluciones,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0) as nroCambios,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0) as nroGarantia
 from
 venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
 inner join producto pr on pr.cod_producto=dtv.cod_productoFK
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$nombre_producto = utf8_encode($valor['nombre_producto']);      
$nroDevoluciones = utf8_decode($valor['nroDevoluciones']);      
$nroCambios = utf8_decode($valor['nroCambios']);      
$nroGarantia = utf8_decode($valor['nroGarantia']);      
if($nroDevoluciones==0 && $nroCambios==0){
  $pagina.="<table class='tableTicket'>
<tr>
<td style='width:100%'>".$nombre_producto."</td>
</tr>
</table>";
}

}
}
 mysqli_close($mysqli); 
return $pagina;
}



function buscardatosventaticket($buscar){
	$mysqli=conectar_al_servidor();
	 
		 $sql= "Select 
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta
		from venta vt where vt.cod_venta=?";

		
		   $datos[0]="";
		   $datos[1]="";
		  	 
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

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
		  
		  
		      $nroCouta=$valor['nroCouta'];
		  	  $Monto=number_format($valor['Monto'],'0',',','.');
		  	 
		     $cuotaspagadas=buscarcantidadcuotapagados($buscar);
		  	 $datos[0]=$nroCouta."/".$cuotaspagadas;
		  	 $datos[1]=$Monto;
		
			  
			  
	  }
 }
  mysqli_close($mysqli); 
 return $datos;
 

}

function historialvistaventa($buscar,$filtro){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	 $condicion="";
	 if($filtro=="1"){
		 $condicion=" (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$buscar."%' ";
	 }
	 if($filtro=="2"){
		 $condicion=" (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) like '%".$buscar."%' ";
	 }
	 if($filtro=="3"){
		  $condicion=" (Select rut_cliente from cliente where cod_cliente=cod_clienteFK) like '%".$buscar."%' ";
	 }
	 if($filtro=="4"){
		 $condicion=" (Select telefono from persona where cod_persona=cod_clienteFK) like '%".$buscar."%' ";
	 }
	 if($filtro=="5"){
		 $condicion=" num_factura like '%".$buscar."%' ";
	 }
	 
	  $sql= "Select puntoexpedicion,tipo_comprobante,idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,cod_local,pago,
	   (Select sum(Monto) from credito where cod_venta=vt.cod_venta and plazo='ENTREGA' ) as entrega,
		 (Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocumento,
		(Select accesocredito from cliente where cod_cliente=cod_clienteFK) as accesocredito,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		IFNULL((select sum(dtv.descuento) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK),0) as totaldescuentodetalles,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		IFNULL((Select sum(precio_producto*cantidad_detalle) from detalle_venta where cod_ventaFK=vt.cod_venta limit 1),0) as totalventadetalle,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt where ".$condicion." order by fecha_venta desc limit 100 ";
		 
		 
		 // echo($sql);
		 // exit;
   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $styleName="tableRegistroSearch";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
			  $entrega=$valor['entrega'];
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $Vendedor1=utf8_encode($valor['Vendedor1']);
		  	  $Vendedor2=utf8_encode($valor['Vendedor2']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $cobradornombre=utf8_encode($valor['cobradornombre']);
		  	   $nombrevendedor1=utf8_encode($valor['nombrevendedor1']);
		  	  $nombrevendedor2=utf8_encode($valor['nombrevendedor2']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $nrodetalle=($valor['nrodetalle']);
			  $totalpagado=utf8_encode($valor['totalpagado']);
			  $nroCouta=utf8_encode($valor['nroCouta']);
			  $totaldescuentodetalles=utf8_encode($valor['totaldescuentodetalles']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $totaldescuentoaplicados=$totaldescuentodetalles+$totaldescuento;
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $Garante=utf8_encode($valor['Garante']);
			  $nrodocumento=utf8_encode($valor['nrodocumento']);
			  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			  $tipo_comprobante=utf8_encode($valor['tipo_comprobante']);
			  $totalventadetalle=utf8_encode($valor['totalventadetalle']);
			  $accesocredito=utf8_encode($valor['accesocredito']);
			  $totalpagado=$totalpagado+$pago;
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

$subtotal=$totalventadetalle-$totaldescuentoaplicados;
 $styleName=CargarStyleTable($styleName);
		  	 	  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaventa(this)'  >
<td style='width:15%'>".$nrof."</td>
<td  id='td_datos_1' style='width:10%'>".$fecha_venta."</td>
<td id='td_datos_13' style='display:none'>".$num_factura."</td>
<td id='td_datos_12' style='width:5%'>".$TipoVenta."</td>
<td  id='td_datos_2' style='width:20%'>".$clientenombre."</td>
<td  id='td_datos_2' style='width:10%'>".$nrodocumento."</td>
<td  id='td_datos_3' style='display:none'>".$Vendedor1."</td>
<td  id='td_datos_14' style='display:none'>".$Vendedor2."</td>
<td  id='td_datos_15' style='display:none'>".$nombrevendedor1."</td>
<td  id='td_datos_16' style='display:none'>".$nombrevendedor2."</td>
<td  id='td_datos_4' style='display:none'>".$cobradornombre."</td>
<td  id='' style='width:10%'>". number_format($totalventadetalle,'0',',','.') ."</td>
<td  id='' style='width:10%'>". number_format($totaldescuentoaplicados,'0',',','.') ."</td>
<td  id='' style='width:10%'>". number_format($subtotal,'0',',','.') ."</td>
<td  id='' style='display:none'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='td_datos_8' style='display:none'>".$cod_venta."</td>
<td  id='td_datos_9' style='display:none'>".$cod_usuarioFK ."</td>
<td  id='td_datos_10' style='display:none'>".$cod_clienteFK ."</td>
<td  id='td_datos_11' style='display:none'>".$cod_cobradorFK ."</td>
<td  id='td_datos_18' style='display:none'>".$TipoPago ."</td>
<td  id='td_datos_19' style='display:none'>".$cantidadcuota ."</td>
<td  id='td_datos_20' style='display:none'>". number_format($Monto,'0',',','.') ."</td>
<td  id='td_datos_21' style='display:none'>".$fechaprimerpago ."</td>
<td  id='td_datos_22' style='display:none'>".$comision ."</td>
<td  id='td_datos_23' style='display:none'>".$cod_local ."</td>
<td  id='td_datos_27' style='display:none'>".$nrodetalle ."</td>
<td  id='td_datos_30' style='display:none'>".$idGaranteFk ."</td>
<td  id='td_datos_31' style='display:none'>".$Garante ."</td>
<td  id='td_datos_32' style='display:none'>".$tipo_comprobante ."</td>
<td  id='td_datos_33' style='display:none'>".$puntoexpedicion ."</td>
<td  id='td_datos_34' style='display:none'>".$accesocredito ."</td>
<td  id='td_datos_35' style='display:none'>".number_format($entrega,'0',',','.')."</td>
</tr>
</table>";


		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.'));
echo json_encode($informacion);	
exit;
}


function buscardatosVenta($buscar){
	$mysqli=conectar_al_servidor();

	 $pagina="";
	  $sql= "Select vt.tipo_comprobante,vt.puntoexpedicion,idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,cod_local,pago,
		 (Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocumento,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Cambio' group by cambios.cod_venta) as cantidadcambio,
		(Select count(cant) from cambios where cambios.cod_venta=vt.cod_venta and motivo='Devolucion' ) as cantidaddevuelto,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago desc limit 1) as fechaultimopago,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from venta vt where 
		vt.cod_venta='$buscar' limit 1 ";
		
		
		  
   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $fecha_venta=$valor['fecha_venta'];
		  	  $total_venta=$valor['total_venta'];
		  	  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
		  	  $cod_clienteFK=utf8_encode($valor['cod_clienteFK']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
		  	  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
		  	  $TipoVenta=utf8_encode($valor['TipoVenta']);
		  	  $TipoPago=utf8_encode($valor['TipoPago']);
		  	  $Vendedor1=utf8_encode($valor['Vendedor1']);
		  	  $Vendedor2=utf8_encode($valor['Vendedor2']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $cobradornombre=utf8_encode($valor['cobradornombre']);
		  	   $nombrevendedor1=utf8_encode($valor['nombrevendedor1']);
		  	  $nombrevendedor2=utf8_encode($valor['nombrevendedor2']);
		  	  $cantidadcuota=utf8_encode($valor['cantidadcuota']);
		  	  $Monto=utf8_encode($valor['Monto']);
		  	  $fechaprimerpago=utf8_encode($valor['fechaprimerpago']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $pago=utf8_encode($valor['pago']);
		  	  $nrodetalle=($valor['nrodetalle']);
			  $totalpagado=utf8_encode($valor['totalpagado']);
			  $nroCouta=utf8_encode($valor['nroCouta']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $Garante=utf8_encode($valor['Garante']);
			  $nrodocumento=utf8_encode($valor['nrodocumento']);
			  $tipo_comprobante=utf8_encode($valor['tipo_comprobante']);
			  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
			  $totalpagado=$totalpagado+$pago;
			   
		  	 	  	   $pagina.="
<table style='display:none'>
<tr id='datos_venta_".$cod_venta."'   >
<td id='td_datos_13' style='width:10%'>".$num_factura."</td>
<td  id='td_datos_2' style='width:25%'>".$clientenombre."</td>
<td  id='td_datos_2' style='width:10%'>".$nrodocumento."</td>
<td  id='td_datos_1' style='width:10%'>".$fecha_venta."</td>
<td  id='td_datos_3' style='display:none'>".$Vendedor1."</td>
<td  id='td_datos_14' style='display:none'>".$Vendedor2."</td>
<td  id='td_datos_15' style='display:none'>".$nombrevendedor1."</td>
<td  id='td_datos_16' style='display:none'>".$nombrevendedor2."</td>
<td  id='td_datos_4' style='display:none'>".$cobradornombre."</td>
<td  id='td_datos_5' style='width:10%'>". number_format($total_venta,'0',',','.') ."</td>
<td  id='td_datos_29' style='display:none'>". number_format($totaldescuento,'0',',','.') ."</td>
<td  id='td_datos_6' style='display:none'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='td_datos_8' style='display:none'>".$cod_venta ."</td>
<td  id='td_datos_9' style='display:none'>".$cod_usuarioFK ."</td>
<td  id='td_datos_10' style='display:none'>".$cod_clienteFK ."</td>
<td  id='td_datos_11' style='display:none'>".$cod_cobradorFK ."</td>
<td  id='td_datos_12' style='display:none'>".$TipoVenta ."</td>
<td  id='td_datos_18' style='display:none'>".$TipoPago ."</td>
<td  id='td_datos_19' style='display:none'>".$cantidadcuota ."</td>
<td  id='td_datos_20' style='display:none'>". number_format($Monto,'0',',','.') ."</td>
<td  id='td_datos_21' style='display:none'>".$fechaprimerpago ."</td>
<td  id='td_datos_22' style='display:none'>".$comision ."</td>
<td  id='td_datos_23' style='display:none'>".$cod_local ."</td>
<td  id='td_datos_27' style='display:none'>".$nrodetalle ."</td>
<td  id='td_datos_30' style='display:none'>".$idGaranteFk ."</td>
<td  id='td_datos_31' style='display:none'>".$Garante ."</td>
<td  id='td_datos_32' style='display:none'>".$tipo_comprobante ."</td>
<td  id='td_datos_33' style='display:none'>".$puntoexpedicion ."</td>
</tr>
</table>";


		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}


//Cuentas Pagadas del Cliente
function buscarCuentasCanceladas($buscar){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	
    	$sql= "Select vt.cod_clienteFK,vt.cod_venta,vt.puntoexpedicion,vt.num_factura,vt.fecha_venta,datediff(cr.fechapago,(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1)) as diff
		from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where vt.cod_clienteFK ='".$buscar."'  and
		(IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) + IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0))>=total_venta
        and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0		
		order by (diff*-1) desc limit 5";
		  
   
    $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $styleName="tableRegistroSearch";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalVenta=0;
 $totalPagado=0;
 $totalDeuda=0;
 $controlVentas="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $puntoexpedicion=$valor['puntoexpedicion'];
		  	  $num_factura=$valor['num_factura'];
		  	  $fecha_venta=$valor['fecha_venta'];
		  	  $cod_venta=$valor['cod_venta'];
		  	  $diff=$valor['diff'];
		  	  $cod_clienteFK=$valor['cod_clienteFK'];
		  	  if($diff<0){
				 $diff=$diff*-1;
				  editarDiasAtrazados($cod_clienteFK,$diff);
			 }else{
				 $diff=0;
			 }

	  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
 $styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='ObtenerdatosCuentaCliente(this)' >
<td  id='td_id' style='display:none'>".$cod_venta."</td>
<td  id='' style='width:40%'>".$nrof."</td>
<td  id='' style='width:30%'>".$fecha_venta."</td>
<td  id='' style='width:10%'>". number_format($diff,'0',',','.') ."</td>
</tr>
</table>";

		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}

//Cuentas pedientes del Cliente
function buscarCuentasPendientes($buscar){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	$fechahoy=date('Y-m-d');	
    	$sql= "Select vt.cod_clienteFK,vt.cod_venta,vt.puntoexpedicion,vt.num_factura,vt.fecha_venta,datediff(cr.fechapago,'".$fechahoy."') as diff,cr.fechapago
		from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where vt.cod_clienteFK ='".$buscar."'  and
		IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)< ((cr.Monto+totalinteres)-cr.descuento)
        and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0		
		order by (diff*-1) desc limit 5";
		  
   
    $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
  $styleName="tableRegistroSearch";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalVenta=0;
 $totalPagado=0;
 $totalDeuda=0;
 $controlVentas="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $puntoexpedicion=$valor['puntoexpedicion'];
		  	  $num_factura=$valor['num_factura'];
		  	  $fecha_venta=$valor['fechapago'];
		  	  $cod_clienteFK=$valor['cod_clienteFK'];
		  	  $diff=$valor['diff'];
		  	  $cod_venta=$valor['cod_venta'];
		  	 if($diff<0){
				 $diff=$diff*-1;
				 editarDiasAtrazados($cod_clienteFK,$diff);
			 }else{
				 $diff=0;
			 }

	  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
 $styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='ObtenerdatosCuentaCliente(this)'  >
<td  id='td_id' style='display:none'>".$cod_venta."</td>
<td  id='' style='width:40%'>".$nrof."</td>
<td  id='' style='width:30%'>".$fecha_venta."</td>
<td  id='' style='width:10%'>". number_format($diff,'0',',','.') ."</td>
</tr>
</table>";

		
			  
			  
	  }
 }
 
  mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
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





function historialFiltroMorosos($buscar,$filtro,$zona,$Local){
	$mysqli=conectar_al_servidor();

	 $totalRegistro=0;
	 $pagina="";
	 $paginaWhatsapp="";
	 $paginaGarante="";
	 $fechahoy=date('Y-m-d');	
	 	
	$condicionCuenta=" and IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk and Tipo='Pago Cuota'),0) <
	(select sum(cr.Monto) from credito cr where cr.cod_venta=vt.cod_venta)-(select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta)"; 
		 
		$CondiciónFiltro="";
		if($filtro=="1"){
			$CondiciónFiltro=" and DATEDIFF('".$fechahoy."',(select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1))>=30 and  DATEDIFF('".$fechahoy."',(select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1)) < 60";
		}
		if($filtro=="2"){
			$CondiciónFiltro=" and DATEDIFF('".$fechahoy."',(select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1))>=60 and  DATEDIFF('".$fechahoy."',(select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1)) < 90 ";
		}
		if($filtro=="3"){
			$CondiciónFiltro=" and DATEDIFF('".$fechahoy."',(select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1))>=90";
		}
		if($filtro=="4"){
			$CondiciónFiltro="  and  (select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1)<= '".$fechahoy."' ";
		}
		

		  $condicionZona=" ";
		 if($zona!=""){
			$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=cod_clienteFK  and idzonaFk='$zona') > 0"; 
		 }
		 
		 $condicionLocal="";
		 if($Local!=""){
			$condicionLocal=" and (Select cod_local from local l where l.cod_local=vt.cod_local)='$Local' "; 
		 }
		 $sql= "Select vt.puntoexpedicion,vt.num_factura,vt.tipo_comprobante,fecha_venta,(select sum(Monto) from credito c where  vt.cod_venta=c.cod_venta)as total_venta,vt.cod_venta,
		  (Select sms from cliente where cod_cliente=cod_clienteFK) as sms,
		 (select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1) as FechapagoDeuda,
		 (select plazo from credito c  where vt.cod_venta=c.cod_venta and (Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota')!=(c.Monto - c.descuento) order by idcredito asc limit 1) as plazo,
		 IFNULL((select sum(deudaInteres) from credito c  where vt.cod_venta=c.cod_venta),0) as deudaInteres,
		 (select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and (Select count(*) from pago where cod_creditoFK = idcredito)=0 order by idcredito asc limit 1) as FechapagoDeuda2,
		(Select telefono from persona where cod_persona=cod_clienteFK) as Telefono,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garantenombre,idGaranteFk,	
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as cicliente,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as Local,
		( select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=cod_clienteFK)) as zona,
		(Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1) as nroCancelado,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado ,
		DATEDIFF('".$fechahoy."',(select (c.fechapago) from credito c  where vt.cod_venta=c.cod_venta and
		IFNULL((Select sum(Monto) from pago where cod_creditoFK = idcredito and Tipo='Pago Cuota'),0)<(c.Monto - c.descuento) 
		order by idcredito asc limit 1)) as dias
		from  venta vt where (select count(*) from cancelaciones c where vt.cod_venta=c.cod_venta)=0 and  (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$buscar."%' ".$CondiciónFiltro.$condicionZona.$condicionCuenta.$condicionLocal." order by dias desc";
	 
		
		  // echo($sql);
		  // exit;
   
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $Telefono=utf8_encode($valor['Telefono']);
			  
			  $fecha_venta=utf8_encode($valor['fecha_venta']);
			  $cicliente=utf8_encode($valor['cicliente']);
			  
			  $zona=utf8_encode($valor['zona']);
		  	  $total_venta=utf8_encode($valor['total_venta']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
			  $puntoexp=utf8_encode($valor['puntoexpedicion']);	
			  $tipo_comprobante=utf8_encode($valor['tipo_comprobante']);	
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
		  	  $Local=utf8_encode($valor['Local']);
		  	  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $dias=utf8_encode($valor['dias']);
			  $FechapagoDeuda=utf8_encode($valor['FechapagoDeuda']);
			  $FechapagoDeuda2=utf8_encode($valor['FechapagoDeuda2']);			  
			  $Garantenombre=utf8_encode($valor['Garantenombre']);
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $plazo=utf8_encode($valor['plazo']);
			  $deudaInteres=utf8_encode($valor['deudaInteres']);
			  $nroCouta=utf8_encode($valor['nroCouta']);
			  $nroCancelado=utf8_encode($valor['nroCancelado']);
			  $montodevuelto=utf8_encode($valor['montodevuelto']);
			  $sms=utf8_encode($valor['sms']);
			  
			  
			  
			  $datos=calcularintereses2($cod_venta,0,0,"3","2","2","no");
		
		$interes=$datos[1] + $deudaInteres;
		$deuda2=$datos[2]+ $deudaInteres;	
		$TotalAPagar=$datos[7];
		$TotalInteresAnterior=$datos[16];
		$TotalDeuda= $TotalDeuda+$deuda2 ;
			
			  if($FechapagoDeuda==""){
				  $FechapagoDeuda= $FechapagoDeuda2;
			  }
			  
			  
			    
			  
			  $deuda=($total_venta+ $interes )-($totalpagado+$totaldescuento);
			  $totalRegistro=$totalRegistro+1;
			  if($deuda<0){
				$deuda=0;  
			  }
			 
			  if($nroCancelado==0){
              $TotalVentas=$total_venta+$TotalVentas;
              $TotalPagos= $TotalPagos+$totalpagado;
              
			  
			  }else{
				  $totalpagado=($totalpagado-$montodevuelto);
				  if($totalpagado<0){
					  $totalpagado=0;
				  }
				   $TotalPagos= $TotalPagos+$totalpagado;
				   $TotalVentas=$total_venta+$TotalVentas;
				  
			  }
			  
			  if($tipo_comprobante=="FACTURA"){
				 $num_factura= $puntoexp."-".$num_factura;
			  } 

$contrlPlazo=strlen($plazo);

if($contrlPlazo<=2 && $plazo!=""){
	$plazo=$plazo."/".$nroCouta;
}
if($plazo==""){
	$plazo=buscarcantidadcuotapagados($cod_venta)+1;
	$plazo=$plazo."/".$nroCouta;
}
		// 
		if($Telefono!=""){
			$condicion=$Telefono[0];
		}else{
			$condicion="";
		}
		
$codigo="595";
if($condicion=="+"){
	$codigo="";
}
		
		
		  	   $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td id='td_datos_13' style='width:9%'>".$num_factura."</td>
<td  id='td_datos_1' style='width:7%'>".$fecha_venta."</td>
<td  id='td_datos_2' style='width:20%'>".$cicliente."-".$clientenombre." <br><b>".$Telefono."</b></td>
<td  id='' style='width:5%'>".$plazo."</td>
<td  id='td_datos_5' style='width:7%'>". number_format($total_venta,'0',',','.') ."</td>
<td  id='td_datos_6' style='width:7%'>". number_format($totalpagado,'0',',','.') ."</td>
<td  id='td_datos_28' style='width:6%'>". number_format($totaldescuento,'0',',','.') ."</td>
<td  id='td_datos_7' style='width:7%'>". number_format($deuda2,'0',',','.') ."</td>
<td id='td_datos_8' style='width:7%'>".$FechapagoDeuda."</td>
<td  id='td_datos_0' style='width:5%'>". number_format($interes ,'0',',','.')."</td>
<td  id='td_datos_0' style='width:5%'>".$dias."</td>
<td  id='td_datos_0' style='width:5%'>".$Local."</td>
<td  id='td_datos_0' style='width:7%'>".$zona."</td>



</tr>
</table>";
$CondicionMensaje= " y posee ".$dias." dias de atraso";
if($dias<=0){
	$CondicionMensaje="";
}
$Mensaje="Estimado(a): ".$clientenombre." Le notificamos que tiene una cuota pendiente de pago de la fecha ".$FechapagoDeuda.$CondicionMensaje." , favor realizar el pago correspondiente o ponerse en contacto con su cobrador. Att: Area Administrativa *B&R EMPRENDIMIENTOS S.A.*";



if($Telefono!="0" && $Telefono!=""){
	
	$Telefono = substr($Telefono, 1);
	
$searchString = " ";
$replaceString = "";
 
$Telefono = str_replace($searchString, $replaceString, $Telefono); 
	
	if($sms=="SI"){
	 $paginaWhatsapp.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td id='td_datos_13' style='width:30%'>".$codigo.$Telefono."</td>
<td  id='td_datos_1' style='width:30%'></td>
<td  id='td_datos_2' style='width:40%'>".$Mensaje."</td>
</tr>
</table>";
	}
}


if($idGaranteFk!="6"){
$MensajeGarante="Estimado(a): Garante ".$Garantenombre." Le notificamos que ".$clientenombre." tiene una cuota pendiente de pago de la fecha ".$FechapagoDeuda.$CondicionMensaje.", favor ponerse en contacto con el area Administrativa de *B&R EMPRENDIMIENTOS S.A.*";

 $paginaGarante.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td id='td_datos_13' style='width:30%'>595".$Telefono."</td>
<td  id='td_datos_1' style='width:30%'></td>
<td  id='td_datos_2' style='width:40%'>".$MensajeGarante."</td>
</tr>
</table>";
}



		
			  
			  
	  }
 }
 $paginaWhatsapp.=$paginaGarante;
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalVentas,'0',',','.'),"5" => number_format($TotalPagos,'0',',','.'),"6" => number_format($TotalDeuda,'0',',','.') ,"7" => $paginaWhatsapp);
echo json_encode($informacion);	
exit;
}




// function buscarClienteFiel($fecha1,$fecha2,$local,$zona,$cliente,$vendedor,$condicion){
	// $mysqli=conectar_al_servidor();
	 // $totalRegistro=0;
	 // $pagina="";
	 // $fechahoy=date('Y-m-d');
	 
	 // $condicionFecha="";
	 // if($fecha1!="" || $fecha2!=""){
		  // $condicionFecha=" and vt.fecha_venta between '$fecha1' and '$fecha2'";
	 // }
	 
	 // $condicionVendedor="";
	 // if($vendedor!=""){
		  // $condicionVendedor=" and (Select nombre from vendedor where idvendedor=vt.Vendedor1 )like '%$vendedor%' ";
	 // }
	 
	  // $condicionlocal="";
	 // if($local!=""){
		  // $condicionlocal=" and vt.cod_local = $local ";
	 // }
	 // $condicioncliente="";
	 // if($cliente!=""){
		  // $condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	 // }
	 // $condicionzona="";
	 // if($zona!=""){
		  // $condicionzona=" and (Select count(cod_cliente) from cliente where cod_cliente=cod_clienteFK  and idzonaFk='$zona') > 0";
	 // }
	 
	 // $condicioncondicion="";
	 // if($condicion=="1"){
		 // $condicioncondicion="and (select  (select count(*)  from credito c where c.cod_venta = vt.cod_venta and plazo!='Entrega' and  (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ) IS NULL ) from venta v where  v.cod_venta= vt.cod_venta )<=3";
	 // }
	 // if($condicion=="2"){
		  // $condicioncondicion="and (select  (select count(*)  from credito c where c.cod_venta = vt.cod_venta and plazo!='Entrega' and  (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ) IS NULL ) from venta v where  v.cod_venta= vt.cod_venta )<=2";
	 // }
	 // if($condicion=="3"){
		  // $condicioncondicion="and (select  (select count(*)  from credito c where c.cod_venta = vt.cod_venta and plazo!='Entrega' and  (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ) IS NULL ) from venta v where  v.cod_venta= vt.cod_venta )<=1";
	 // }
	 // if($condicion=="4"){
		  // $condicioncondicion="and (select  (select count(*)  from credito c where c.cod_venta = vt.cod_venta and plazo!='Entrega' and  (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ) IS NULL ) from venta v where  v.cod_venta= vt.cod_venta )<=0";
	 // }
	  
	  // $sql= "Select vt.fecha_venta,vt.cod_venta, vt.total_venta, vt.puntoexpedicion, vt.num_factura,
	  // (Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,vt.cod_clienteFK,
	  // (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocumento,
	  // (select count(*) from credito c where vt.cod_venta=c.cod_venta and plazo!='Entrega') as contadorCredito,
	  // ifnull((select (Select plazo from credito cr where cr.idcredito=pg.cod_creditoFK ) from pago pg where vt.cod_venta=pg.cod_venta_fk and Tipo='Pago Cuota' order by idPago desc limit 1 ),concat('0/',(select count(*) from credito c where vt.cod_venta=c.cod_venta and plazo!='Entrega'))) as plazo,
		// (Select nombre from vendedor where idvendedor=vt.Vendedor1 ) as Vendedor,
	  // (select DATEDIFF( (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ),fechapago) 
 // from credito c where c.idcredito= cr.idcredito )as fecha 
// from  venta vt inner join credito cr on cr.cod_venta= vt.cod_venta  
// where (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ) IS NOT NULL and (select DATEDIFF( (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ),fechapago) 
 // from credito c where c.idcredito= cr.idcredito )<=15 ".$condicionzona.$condicioncondicion.$condicioncliente.$condicionFecha.$condicionVendedor.$condicionlocal." order by fecha desc  ";
		

		  
    // $styleName="tableRegistroSearch";
   // $stmt = $mysqli->prepare($sql);
  
// if ( ! $stmt->execute()) {
   // echo "Error";
   // exit;
// }
 
	// $result = $stmt->get_result();
 // $valor= mysqli_num_rows($result);
 // $nroRegistro= $valor;
 // $TotalVentas= 0;
 // $TotalPagos= 0;
 // $TotalDeuda= 0;
 // $cod = [];
 // $i=0;
 // if ($valor>0)
 // {
	  // while ($valor= mysqli_fetch_assoc($result))
	  // {
			  // $contadorCredito=$valor['contadorCredito'];
			  // $fecha=$valor['fecha'];
			  // $plazo=$valor['plazo'];
			  // $nrodocumento=$valor['nrodocumento'];
		      // $fecha_venta=$valor['fecha_venta'];
		  	  // $clientenombre=utf8_encode($valor['clientenombre']);
		  	  // $total_venta=utf8_encode($valor['total_venta']);
		  	  // $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
		  	  // $num_factura=utf8_encode($valor['num_factura']);
			  // $Vendedor=utf8_encode($valor['Vendedor']);
		  	  // $cod_venta=utf8_encode($valor['cod_venta']);
			  // if($fecha<="0"){
				  // $fecha=0;
			  // }		
	
// if (in_array($cod_venta, $cod)) {
   
// }else{
	
	  // $datos=calcularintereses($cod_venta,0,0,"2","2","2","no");
				// $totalDeuda=$datos[4];
				// $totalPagado=$datos[3];
			  
			  // $detallleProducto=buscar_detalles_venta_producto($cod_venta);
			      // if($puntoexpedicion!=""){
						// $nrof=$puntoexpedicion."-".$num_factura;
					// }else{
						// $nrof=$num_factura;
					// }
		  	 
		 // $styleName=CargarStyleTable($styleName);	  
		  	   // $pagina.="
// <table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
// <tr id='tbSelecRegistro' >
// <td  style='width:10%'>".$fecha_venta."</td>
// <td  style='width:10%'>".$nrof."</td>
// <td  style='width:15%'>".$clientenombre."<br>*".$nrodocumento."*</td>
// <td  style='width:18%'>".$detallleProducto."</td>
// <td  style='width:10%'>VNT:".number_format($total_venta,'0',',','.')."<br> <br> PG:".number_format($totalPagado,'0',',','.')."</td>
// <td  style='width:10%'>".$plazo."</td>
// <td  style='width:7%'>".$fecha."</td>
// <td  style='width:10%'>".number_format($totalDeuda,'0',',','.')."</td>
// <td  style='width:10%'>".$Vendedor."</td>
// </tr>
// </table>";

	 // $cod += [$cod_venta];
	 // $i++;
	 // $cod += array("$i" =>$cod_venta);
// }	  
			  
	  // }
 // }

// /*Retornamos los datos obtenidos mediante el JSON */      
// $informacion =array("1" => "exito","2" => $pagina,"3" => $i,"4" => $cod);
// echo json_encode($informacion);	
// exit;
// }


function buscar_detalles_venta_producto($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,dtv.detalleproducto,
 IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0) as nroDevoluciones,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0) as nroCambios,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0) as nroGarantia
 from
 venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
 inner join producto pr on pr.cod_producto=dtv.cod_productoFK
 where vt.cod_venta='$buscar' ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$a=1;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$nombre_producto = utf8_decode($valor['nombre_producto']);      
$nroDevoluciones = utf8_decode($valor['nroDevoluciones']);      
$nroCambios = utf8_decode($valor['nroCambios']);      
$nroGarantia = utf8_decode($valor['nroGarantia']);      
$detalleproducto = utf8_decode($valor['detalleproducto']);      
if($nroDevoluciones==0 && $nroCambios==0){
	if($pagina==""){
	$pagina.=$a.") &nbsp".$nombre_producto;	
	}else{
		$pagina.="<br>".$a.") &nbsp".$nombre_producto;	
	}
  $a=$a+1;
}

}
}
 mysqli_close($mysqli);
return utf8_decode($pagina);
}









function buscarClienteFiel($fecha1,$fecha2,$local,$zona,$cliente,$vendedor,$condicion){
	$mysqli=conectar_al_servidor();
	 $totalRegistro=0;
	 $pagina="";
	 $fechahoy=date('Y-m-d');
	 
	 	 $condicionFecha="";
	 if($fecha1!="" || $fecha2!=""){
		  $condicionFecha=" and vt.fecha_venta between '$fecha1' and '$fecha2'";
	 }
	 
	 $condicionVendedor="";
	 if($vendedor!=""){
		  $condicionVendedor=" and (Select nombre from vendedor where idvendedor=vt.Vendedor1 )like '%$vendedor%' ";
	 }
	 
	  $condicionlocal="";
	 if($local!=""){
		  $condicionlocal=" and vt.cod_local = $local ";
	 }
	 $condicioncliente="";
	 if($cliente!=""){
		  $condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	 }
	 $condicionzona="";
	 if($zona!=""){
		  $condicionzona=" and (Select count(cod_cliente) from cliente where cod_cliente=cod_clienteFK  and idzonaFk='$zona') > 0";
	 }
	 
	
	  
	  $sql= "Select  cod_clienteFK  from  venta vt where estado='Activo'  ".$condicionzona.$condicioncliente.$condicionFecha.$condicionVendedor.$condicionlocal."  group by  cod_clienteFK order by fecha_venta desc  ";
		
// echo($sql);
		// exit;
		  
    $styleName="tableRegistroSearch";
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 $cod = [];
 $i=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
			  $cod_clienteFK=$valor['cod_clienteFK'];
			  
			  $Datos=buscarClienteFielDetalle($cod_clienteFK,$fecha1,$fecha2,$local,$zona,$cliente,$vendedor,$condicion);
			 
			 $pagina.=$Datos[0];
			 if($Datos[1]=="SI"){
				 $i= $i + 1;
			 }
			  
		  
	  }
 }

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $i,"4" => $cod);
echo json_encode($informacion);	
exit;
}







function buscarClienteFielDetalle($cod_clienteFK,$fecha1,$fecha2,$local,$zona,$cliente,$vendedor,$condicion){
	$mysqli=conectar_al_servidor();
	 $totalRegistro=0;
	 $pagina="";
	 $fechahoy=date('Y-m-d');
	 
	 
	 
	 	 $condicionFecha="";
	 if($fecha1!="" || $fecha2!=""){
		  $condicionFecha=" and vt.fecha_venta between '$fecha1' and '$fecha2'";
	 }
	 
	 $condicionVendedor="";
	 if($vendedor!=""){
		  $condicionVendedor=" and (Select nombre from vendedor where idvendedor=vt.Vendedor1 )like '%$vendedor%' ";
	 }
	 
	  $condicionlocal="";
	 if($local!=""){
		  $condicionlocal=" and vt.cod_local = $local ";
	 }
	 $condicioncliente="";
	 if($cliente!=""){
		  $condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%'";
	 }
	 $condicionzona="";
	 if($zona!=""){
		  $condicionzona=" and (Select count(cod_cliente) from cliente where cod_cliente=cod_clienteFK  and idzonaFk='$zona') > 0";
	 }
	 
	 
	
	 
	 $condicioncondicion="";
	 if($condicion=="1"){
		  $condicioncondicion=3;
	 }
	 if($condicion=="2"){
		  $condicioncondicion=2;
	 }
	 if($condicion=="3"){
		  $condicioncondicion=1;
	 }
	 if($condicion=="4"){
		  $condicioncondicion=0;
	 }
	  
	  $sql= "Select vt.fecha_venta,vt.cod_venta, vt.total_venta, vt.puntoexpedicion, vt.num_factura,
	  (Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
	  (Select telefono from persona where cod_persona=cod_clienteFK) as clienteTelefono,vt.cod_clienteFK,
	  (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocumento,
	  (select count(*) from credito c where vt.cod_venta=c.cod_venta and plazo!='Entrega') as contadorCredito,
	  ifnull((select (Select plazo from credito cr where cr.idcredito=pg.cod_creditoFK ) from pago pg where vt.cod_venta=pg.cod_venta_fk and Tipo='Pago Cuota' order by idPago desc limit 1 ),concat('0/',(select count(*) from credito c where vt.cod_venta=c.cod_venta and plazo!='Entrega'))) as plazo,
		(Select nombre from vendedor where idvendedor=vt.Vendedor1 ) as Vendedor,
	  (select DATEDIFF( (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ),fechapago) 
 from credito c where c.idcredito= cr.idcredito )as fecha,
 (select  (select count(*)  from credito c where c.cod_venta = vt.cod_venta and plazo!='Entrega' and  (select Fecha from pago where cod_creditoFK=idcredito order by Fecha desc limit 1 ) IS NULL ) from venta v where  v.cod_venta= vt.cod_venta ) as deudaContador ,
 $condicion as condicion 
from  venta vt inner join credito cr on cr.cod_venta= vt.cod_venta  
where  cod_clienteFK = ".$cod_clienteFK."   ".$condicionzona.$condicioncliente.$condicionFecha.$condicionVendedor.$condicionlocal." order by deudaContador  , fecha desc   ";
		
		
		// echo($sql);
		// exit;

		  
    $styleName="tableRegistroSearch";
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $TotalVentas= 0;
 $TotalPagos= 0;
 $TotalDeuda= 0;
 $cod =[];
 $i="NO";
  $c=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
			  $clienteTelefono=utf8_encode($valor['clienteTelefono']);
			  $contadorCredito=utf8_encode($valor['contadorCredito']);
			  $fecha=utf8_encode($valor['fecha']);
			  $plazo=utf8_encode($valor['plazo']);
			  $nrodocumento=utf8_encode($valor['nrodocumento']);
		      $fecha_venta=utf8_encode($valor['fecha_venta']);
		  	  $clientenombre=utf8_encode($valor['clientenombre']);
		  	  $total_venta=utf8_encode($valor['total_venta']);
		  	  $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
		  	  $num_factura=utf8_encode($valor['num_factura']);
			  $Vendedor=utf8_encode($valor['Vendedor']);
		  	  $cod_venta=utf8_encode($valor['cod_venta']);
			  
			  $deudaContador=utf8_encode($valor['deudaContador']);
			  
			    if($fecha<="0"){
				  $fecha=0;
			  }		
			  
	if($deudaContador<=$condicioncondicion){
		
		
	
if (in_array($cod_clienteFK, $cod)) {

}else{	
$datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
			   	$totalDeuda=$datos[4];
				$totalPagado=$datos[3];
if($fecha<=15 && $totalPagado > $totalDeuda){
				  

			  $detallleProducto=buscar_detalles_venta_producto($cod_venta);
			      if($puntoexpedicion!=""){
						$nrof=$puntoexpedicion."-".$num_factura;
					}else{
						$nrof=$num_factura;
					}
					
				
		 $styleName=CargarStyleTable($styleName);	  
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td  style='width:10%'>".$fecha_venta."</td>
<td  style='width:10%'>".$nrof."</td>
<td  style='width:15%'>".$clientenombre."<br>*".$nrodocumento."* <b>".$clienteTelefono."</b> </td>
<td  style='width:18%'>".$detallleProducto."</td>
<td  style='width:10%'>VNT:".number_format($total_venta,'0',',','.')."<br> <br> PG:".number_format($totalPagado,'0',',','.')."</td>
<td  style='width:10%'>".$plazo."</td>
<td  style='width:7%'>".$fecha."</td>
<td  style='width:10%'>".number_format($totalDeuda,'0',',','.')."</td>
<td  style='width:10%'>".$Vendedor."</td>
</tr>
</table>";

// echo($cod_venta."-");

  $i="SI";  
}
 $c++;
 $cod += array("$c" =>$cod_clienteFK);

}			  
				
			  }else{
				  $pagina="";
				   $i="NO";  
			  }
	
	  }
 }
 
 
 
  $Datos[0]= $pagina;
  $Datos[1]= $i ;
 
 
 return $Datos;


}









verificar($operacion);
?>
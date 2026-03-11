<?php
require_once("conexion.php");
include_once('quitarseparadormiles.php');
include_once("verificar_navegador.php");
include_once("buscar_nivel.php");
include_once("calcularintereses.php");
// include_once("calcularInteresDirecto.php");
include_once("classTable.php");

function ObtenerDatos($operacion)
{


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




if($operacion=="nuevo" )
{


$cod_creditoFK=$_POST['cod_creditoFK'];
$cod_creditoFK = mb_convert_encoding((string)($cod_creditoFK), 'ISO-8859-1', 'UTF-8');


$Fecha=$_POST['Fecha'];
$Fecha = mb_convert_encoding((string)($Fecha), 'ISO-8859-1', 'UTF-8');

$cod_venta=$_POST['cod_venta'];
$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

$totalDeudaCuota=$_POST['totalDeudaCuota'];
$totalDeudaCuota = quitarseparadormiles($totalDeudaCuota);

$totalInteres=$_POST['totalInteres'];
$totalInteres = quitarseparadormiles($totalInteres);

$MontoCobrado=$_POST['MontoCobrado'];
$MontoCobrado = quitarseparadormiles($MontoCobrado);

$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);

$MontoTarjeta=$_POST['MontoTarjeta'];
$MontoTarjeta = quitarseparadormiles($MontoTarjeta);

$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = mb_convert_encoding((string)($cod_cobradorFK), 'ISO-8859-1', 'UTF-8');

$nrofactura=$_POST['nrofactura'];
$nrofactura = mb_convert_encoding((string)($nrofactura), 'ISO-8859-1', 'UTF-8');

$cajapredeterminada=$_POST['codcaja'];
$cajapredeterminada = mb_convert_encoding((string)($cajapredeterminada), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');

$CargoAdministrativo=$_POST['CargoAdministrativo'];
$CargoAdministrativo = quitarseparadormiles($CargoAdministrativo);

abm($CargoAdministrativo,$cajapredeterminada,$codApertura,$cod_creditoFK,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$MontoCobrado,$MontoTarjeta,$descuento,$nrofactura,$operacion,1,0);



}


if($operacion=="cargarpago" )
{

$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);

$MontoTarjeta=$_POST['MontoTarjeta'];
$MontoTarjeta = quitarseparadormiles($MontoTarjeta);

$Descuento=$_POST['Descuento'];
$Descuento = quitarseparadormiles($Descuento);

$Fecha=$_POST['Fecha'];
$Fecha = mb_convert_encoding((string)($Fecha), 'ISO-8859-1', 'UTF-8');

$cod_venta=$_POST['cod_venta'];
$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = mb_convert_encoding((string)($cod_cobradorFK), 'ISO-8859-1', 'UTF-8');

$controlfecha=$_POST['controlfecha'];
$controlfecha = mb_convert_encoding((string)($controlfecha), 'ISO-8859-1', 'UTF-8');

$nrofactura=$_POST['nrofactura'];
$nrofactura = mb_convert_encoding((string)($nrofactura), 'ISO-8859-1', 'UTF-8');

$cajapredeterminada=$_POST['codcaja'];
$cajapredeterminada = mb_convert_encoding((string)($cajapredeterminada), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');


$CargoAdministrativo =$_POST['CargoAdministrativo'];
$CargoAdministrativo = quitarseparadormiles($CargoAdministrativo);

if($nrofactura==""){
	$nrofactura=buscarnrofactura();
}

cargarpagos($CargoAdministrativo,$Monto,$MontoTarjeta,$Descuento,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$nrofactura,$cajapredeterminada,$codApertura,1,0);

}

if($operacion=="cargaropcionpagoparcial" )
{

$MontoTarjeta=$_POST['MontoTarjeta'];
$MontoTarjeta = quitarseparadormiles($MontoTarjeta);

$Descuento=$_POST['Descuento'];
$Descuento = quitarseparadormiles($Descuento);

$Fecha=$_POST['Fecha'];
$Fecha = mb_convert_encoding((string)($Fecha), 'ISO-8859-1', 'UTF-8');

$cod_venta=$_POST['cod_venta'];
$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = mb_convert_encoding((string)($cod_cobradorFK), 'ISO-8859-1', 'UTF-8');

$controlfecha=$_POST['controlfecha'];
$controlfecha = mb_convert_encoding((string)($controlfecha), 'ISO-8859-1', 'UTF-8');

$nrofactura=$_POST['nrofactura'];
$nrofactura = mb_convert_encoding((string)($nrofactura), 'ISO-8859-1', 'UTF-8');

$cajapredeterminada=$_POST['codcaja'];
$cajapredeterminada = mb_convert_encoding((string)($cajapredeterminada), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');

$totalregistro=$_POST['totalregistro'];
$totalregistro = mb_convert_encoding((string)($totalregistro), 'ISO-8859-1', 'UTF-8');

$CargoAdministrativo =$_POST['CargoAdministrativo'];
$CargoAdministrativo = quitarseparadormiles($CargoAdministrativo);

addPagosCreditoParcial($CargoAdministrativo,$MontoTarjeta,$Descuento,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$nrofactura,$cajapredeterminada,$codApertura,$totalregistro);

}

if($operacion=="eliminar" )
{


$cod_creditoFK=$_POST['cod_creditoFK'];
$cod_creditoFK = mb_convert_encoding((string)($cod_creditoFK), 'ISO-8859-1', 'UTF-8');
$motivo=$_POST['motivo'];
$motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
$monto=$_POST['monto'];
$monto = mb_convert_encoding((string)($monto), 'ISO-8859-1', 'UTF-8');
$cuota=$_POST['cuota'];
$cuota = mb_convert_encoding((string)($cuota), 'ISO-8859-1', 'UTF-8');
$idFkVenta=$_POST['idFkVenta'];
$idFkVenta = mb_convert_encoding((string)($idFkVenta), 'ISO-8859-1', 'UTF-8');
$nrofactura=$_POST['nrofactura'];
$nrofactura = mb_convert_encoding((string)($nrofactura), 'ISO-8859-1', 'UTF-8');
quitarpago($idFkVenta,$cod_creditoFK,$motivo,$monto,$cuota,$nrofactura,$user);

}

if($operacion=="editarcomision" )
{


$comision=$_POST['comision'];
$comision = quitarseparadormiles($comision);

$idPagoComision=$_POST['idPagoComision'];


cambiarcomision($idPagoComision,$comision);

}
if($operacion=="eliminarhistorialpago" )
{


$codPago=$_POST['codPago'];
$codPago = mb_convert_encoding((string)($codPago), 'ISO-8859-1', 'UTF-8');

$codVenta=$_POST['codVenta'];
$codVenta = mb_convert_encoding((string)($codVenta), 'ISO-8859-1', 'UTF-8');

quitarhistorialpago($codPago,$codVenta);

}

if($operacion=="buscarHistorial" )
{


$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

buscarhistorialpagos($buscar);

}
if($operacion=="buscarHistorialPagosAReimprimir" )
{


$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');

buscarHistorialPagosAReimprimir($buscar);

}
if($operacion=="buscarpagoseliminados" )
{


$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
buscarpagoseliminados($fecha1,$fecha2);

}
if($operacion=="arqueo" )
{

$cedula=$_POST['cedula'];
$cedula = mb_convert_encoding((string)($cedula), 'ISO-8859-1', 'UTF-8');
$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
$factura=$_POST['factura'];
$factura = mb_convert_encoding((string)($factura), 'ISO-8859-1', 'UTF-8');
$cliente=$_POST['cliente'];
$cliente = mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
$fechafija=$_POST['fechafija'];
$fechafija = mb_convert_encoding((string)($fechafija), 'ISO-8859-1', 'UTF-8');
$cobrador=$_POST['cobrador'];
$cobrador = mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
$metodo=$_POST['metodo'];
$metodo = mb_convert_encoding((string)($metodo), 'ISO-8859-1', 'UTF-8');
$codCaja=$_POST['codCaja'];
$codCaja = mb_convert_encoding((string)($codCaja), 'ISO-8859-1', 'UTF-8');


$condicion =$_POST['condicion'];
$condicion = mb_convert_encoding((string)($condicion), 'ISO-8859-1', 'UTF-8');

$desde=$_POST['desde'];
$desde = mb_convert_encoding((string)($desde), 'ISO-8859-1', 'UTF-8');
if($desde=="arqueo2"){
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
	//	$local=buscarlocaluser($user);
	}
}
}

$informacion =Arqueo($fecha1,$fecha2,$local,$factura,$cliente,$cedula,$fechafija,$cobrador,$metodo,$codCaja,$condicion);
echo json_encode($informacion);	
exit;
}
if($operacion=="reeimpresionrecibo" )
{


$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
$factura=$_POST['factura'];
$factura = mb_convert_encoding((string)($factura), 'ISO-8859-1', 'UTF-8');
$cliente=$_POST['cliente'];
$cliente = mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = mb_convert_encoding((string)($fechafiltro), 'ISO-8859-1', 'UTF-8');
$cobrador=$_POST['cobrador'];
$cobrador = mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
$metodo=$_POST['metodo'];
$metodo = mb_convert_encoding((string)($metodo), 'ISO-8859-1', 'UTF-8');


reeimpresionrecibo($fecha1,$fecha2,$local,$factura,$cliente,$fechafiltro,$cobrador,$metodo);

}

if($operacion=="vistacajaapp" )
{


$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$local=$_POST['local'];
$local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
$cobrador=$_POST['cobrador'];
$cobrador = mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
vistacajaapp($fecha1,$fecha2,$local,$cobrador);

}

if($operacion=="comisioncobrador" )
{


$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$zona=$_POST['zona'];
$zona = mb_convert_encoding((string)($zona), 'ISO-8859-1', 'UTF-8');
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = mb_convert_encoding((string)($fechafiltro), 'ISO-8859-1', 'UTF-8');
$cobrado=$_POST['cobrado'];
$cobrado = mb_convert_encoding((string)($cobrado), 'ISO-8859-1', 'UTF-8');
comisioncobrador($fecha1,$fecha2,$zona,$fechafiltro,$cobrado);

}
if($operacion=="mascomisioncobrador" )
{


$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$zona=$_POST['zona'];
$zona = mb_convert_encoding((string)($zona), 'ISO-8859-1', 'UTF-8');
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = mb_convert_encoding((string)($fechafiltro), 'ISO-8859-1', 'UTF-8');
$cobrado=$_POST['cobrado'];
$cobrado = mb_convert_encoding((string)($cobrado), 'ISO-8859-1', 'UTF-8');
$totalrecaudacion=$_POST['totalrecaudacion'];
$totalrecaudacion = quitarseparadormiles($totalrecaudacion);
$totalcomision=$_POST['totalcomision'];
$totalcomision = quitarseparadormiles($totalcomision);
$registrocargado=$_POST['registrocargado'];
$registrocargado = mb_convert_encoding((string)($registrocargado), 'ISO-8859-1', 'UTF-8');
mascomisioncobrador($fecha1,$fecha2,$zona,$fechafiltro,$cobrado,$totalrecaudacion,$totalcomision,$registrocargado);

}

 if($operacion=="pagocontado" )
{




$cod_venta=$_POST['cod_venta'];
$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

$cajapredeterminada=$_POST['codcaja'];
$cajapredeterminada = mb_convert_encoding((string)($cajapredeterminada), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');

$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);

$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);

$montotarjerta=$_POST['montotarjerta'];
$montotarjerta = quitarseparadormiles($montotarjerta);



abmcontado($cod_venta,$descuento,$monto,$montotarjerta,$cajapredeterminada,$codApertura,1,0,0);

}


 if($operacion=="cargartipospagosventas" )
{

$cod_venta=$_POST['idventa_fk'];
$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

$cajapredeterminada=$_POST['codcaja'];
$cajapredeterminada = mb_convert_encoding((string)($cajapredeterminada), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');



addPagos($cod_venta,$cajapredeterminada,$codApertura);

}

if($operacion=="cargartipospagoscredito" )
{

$cod_creditoFK=$_POST['cod_creditoFK'];
$cod_creditoFK = mb_convert_encoding((string)($cod_creditoFK), 'ISO-8859-1', 'UTF-8');


$Fecha=$_POST['Fecha'];
$Fecha = mb_convert_encoding((string)($Fecha), 'ISO-8859-1', 'UTF-8');

$cod_venta=$_POST['cod_venta'];
$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

$totalDeudaCuota=$_POST['totalDeudaCuota'];
$totalDeudaCuota = quitarseparadormiles($totalDeudaCuota);

$totalInteres=$_POST['totalInteres'];
$totalInteres = quitarseparadormiles($totalInteres);

$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);

$MontoTarjeta=$_POST['MontoTarjeta'];
$MontoTarjeta = quitarseparadormiles($MontoTarjeta);

$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = mb_convert_encoding((string)($cod_cobradorFK), 'ISO-8859-1', 'UTF-8');

$nrofactura=$_POST['nrofactura'];
$nrofactura = mb_convert_encoding((string)($nrofactura), 'ISO-8859-1', 'UTF-8');

$cajapredeterminada=$_POST['codcaja'];
$cajapredeterminada = mb_convert_encoding((string)($cajapredeterminada), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');

$codApertura=$_POST['codApertura'];
$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');

$CargoAdministrativo =$_POST['CargoAdministrativo'];
$CargoAdministrativo = quitarseparadormiles($CargoAdministrativo);


addPagosCredito($CargoAdministrativo,$cajapredeterminada,$codApertura,$cod_creditoFK,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$MontoTarjeta,$descuento,$nrofactura,$operacion);

}



if($operacion=="buscarImprimirTicketVentaContado" )
{

	$cod_venta=$_POST['cod_venta'];
	$cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8');

	buscarImprimirTicketVentaContado($cod_venta);

}


}




/*Buscar */
function buscarImprimirTicketVentaContado($cod_venta)
{
$pagina="";

$detalleVenta= buscar_detalles_venta($cod_venta) ;
$Pagos=buscarpagosTituloContado($cod_venta);

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($Pagos[1],'0',',','.'),"4" =>$detalleVenta,"5" =>$Pagos[2] ,"6" =>number_format($Pagos[3],'0',',','.') ,"7" =>$Pagos[4]  ,"8" =>$Pagos[5]  );
echo json_encode($informacion);	
exit;
}





/*Funcion para insertar,modificar o eliminar registros*/
function abm($CargoAdministrativo,$codCaja,$codApertura,$cod_creditoFK,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$MontoCobrado,$MontoTarjeta,$descuento,$nrofactura,$operacion,$cod_TipoPago,$controlTipoPago)
{
	
if($cod_creditoFK==""  || $totalDeudaCuota==""  || $Fecha=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


GuardarDeudaInteres("0",$cod_creditoFK);

$datoVenta=buscardatosventa($cod_venta);
$nrof=$datoVenta[20];
$datosCredito=buscardatosdelcredito($cod_creditoFK);
$montocredito=$datosCredito[0];
$descuentocredito=$datosCredito[1];
$totalPagado=$datosCredito[2];
$totalpagacredito=$datosCredito[3];
$totalpagainteres=$datosCredito[4];
$montoInteres=0;
$interespagados=0;
$mysqli=conectar_al_servidor(); 
if($nrofactura==""){
$nrofactura=buscarnrofactura();
}
if($MontoCobrado>0){
$tipopago='Efectivo';
if($totalInteres>0){
$totalDeudaCuotaControl=$totalDeudaCuota;



if($totalDeudaCuotaControl>($MontoCobrado+$descuento)){
$pago=($MontoCobrado*50)/100;
$MontoCobrado=$MontoCobrado-$pago;
}else{
$pago=$totalInteres;
$MontoCobrado=$MontoCobrado-$pago;	
}
if($pago>0){
$descripcion="Pago de intereses, Factura Nro: *".$nrof."*";
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Interes',?,?,?,?,'$cod_TipoPago')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cod_creditoFK,$pago,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura,$tipopago,$codCaja,$codApertura,$descripcion);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
}
$interespagados=$pago;

}

$totalDeudaCuota=$montocredito-$totalpagacredito;


if($totalDeudaCuota>0 && $MontoCobrado>0){
	
if($MontoCobrado>=$totalDeudaCuota){
	$Montopagado=$totalDeudaCuota;
}else{
	$Montopagado=$MontoCobrado;
}

 $descripcion="Pago de cuotas, Factura Nro: *".$nrof."*";
if($Montopagado>0){
	
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Pago Cuota',?,?,?,?,'$cod_TipoPago')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cod_creditoFK,$Montopagado,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura,$tipopago,$codCaja,$codApertura,$descripcion);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

}

}
}

if($MontoTarjeta>0){
	$tipopago='Tarjeta';
	$totalInteres2=$totalInteres-$interespagados;
if($totalInteres2>0){
$totalDeudaCuotaControl=$totalDeudaCuota;
 
if($totalDeudaCuotaControl>($MontoTarjeta+$descuento)){
$pago=($MontoTarjeta*50)/100;
$MontoTarjeta=$MontoTarjeta-$pago;
}else{
$pago=$totalInteres2;
$MontoTarjeta=$MontoTarjeta-$pago;	
}
if($pago>0){

$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Interes',?,?,?,?,'$cod_TipoPago')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cod_creditoFK,$pago,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura,$tipopago,$codCaja,$codApertura,$descripcion);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
}

}

$totalDeudaCuota=$montocredito-$totalpagacredito;

if($totalDeudaCuota>0 && $MontoTarjeta>0){
	
if($MontoTarjeta>=$totalDeudaCuota){
	$Montopagado=$totalDeudaCuota;
}else{
	$Montopagado=$MontoTarjeta;
}


if($Montopagado>0){
	
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Pago Cuota',?,?,?,?,'$cod_TipoPago')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cod_creditoFK,$Montopagado,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura,$tipopago,$codCaja,$codApertura,$descripcion);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

}

}
}



actualizarDescuento($cod_creditoFK,$descuento);
actualizarTotalCuota($cod_creditoFK,($totalDeudaCuota+$totalPagado),$totalInteres,$totalDeudaCuota);

if($CargoAdministrativo==""){
	$CargoAdministrativo="0";
}

if( $CargoAdministrativo!="0"){
$descripcion="Pago de Cargo Administrativo, Factura Nro: *".$nrof."*";
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'CARGO ADMINISTRATIVO',?,?,?,?,'$cod_TipoPago')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cod_creditoFK,$CargoAdministrativo,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura,$tipopago,$codCaja,$codApertura,$descripcion);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}

}

if($controlTipoPago == 0){
	

	$titulopago=buscarpagosTituloCreditoDirecto($cod_venta,$nrofactura,$cod_creditoFK);	
	$datosTicket=calcularintereses2($cod_venta,0,0,"2","2","2","no");
$totalDescuento=$datosTicket[0];
$interesespagado=$datosTicket[12];
$totalpagado=$datosTicket[3];
$acobrar=$datosTicket[4];
$deuda=$datosTicket[4];
 $totalDeuda=$datosTicket[4];
 $totalVenta=$datosTicket[11];
 $InteresActual=$datosTicket[10];
 $totalsininteres=$datosTicket[7];
$totalPagado=buscartotalpagob($cod_venta);
 

$paginaticket= buscarDetalleVentaImprimir($cod_venta);
$paginaticket2="Factura nro: ".$paginaticket[2];
 $datoVenta=buscardatosventa($cod_venta);
//addMasCuotas($cod_venta,$totalPagado);
$informacion =array("1" => "exito","2" =>number_format($totalPagado,'0',',','.') ,"3" =>  number_format($totalVenta,'0',',','.') ,"4" =>  number_format($totalDeuda,'0',',','.')
,"5"=> $paginaticket2 ,"6"=> $datoVenta[11] ,"7"=> $datoVenta[19],"8"=>$nrofactura ,"9"=>$datoVenta[6]
,"11"=> number_format($interesespagado,'0',',','.') ,"12"=> number_format($deuda,'0',',','.')  ,"13"=> number_format($totalpagado,'0',',','.') 
,"14"=> number_format($totalDescuento,'0',',','.')  ,"15"=> number_format($InteresActual,'0',',','.') ,"16"=> number_format($totalsininteres,'0',',','.'),"17"=> $titulopago[2] ,"18"=>$Fecha );
echo json_encode($informacion);	
exit;
}

}

function  GuardarDeudaInteres($Monto,$idcredito){
	  
	$mysqli=conectar_al_servidor();
	$consulta="Update credito set deudaInteres=$Monto where idcredito='$idcredito'";	
	
	if($Monto>=0){
	
	$stmt = $mysqli->prepare($consulta);
	

if ( ! $stmt->execute()) {
   /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
	}
	}

}

/* function abmcontado($cod_venta,$descuento,$monto,$montotarjerta,$cajapredeterminada,$codApertura,$tipopago,$controlTipoPago,$controlTotal)
{
if($cod_venta==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$datosventa=buscardatosventa($cod_venta);
$nrofactura=buscarnrofactura();
$descripcion="ventas";

$mysqli=conectar_al_servidor(); 

if($controlTipoPago != 0 && $controlTotal != 0){
	$controlTotal = $controlTotal - $controlTipoPago;
}


if($controlTipoPago == $controlTotal){
$consulta="delete from credito where  cod_venta='$cod_venta'";
$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo,descuento)
			values('Contado','$datosventa[0]','$cod_venta','$datosventa[1]','Pendiente','0','$descuento')";	
$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
}


if($monto>0){
	
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK,nrofactura)
values((select idcredito from credito where cod_venta='$cod_venta' and plazo='Contado' limit 1),?,?,?,?,?,'Pago Cuota','Efectivo','$cajapredeterminada','$codApertura','$descripcion','$tipopago','$nrofactura')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$monto,$datosventa[0],$datosventa[5],$cod_venta,$datosventa[18]);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}
}



if($montotarjerta>0){
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,tipo,tipopago,codCaja,codApertura,descripcion)
values((select idcredito from credito where cod_venta='$cod_venta' and plazo='Contado' limit 1),?,?,?,?,?,'Pago Cuota','Tarjeta','$cajapredeterminada','$codApertura','$descripcion')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$montotarjerta,$datosventa[0],$datosventa[5],$cod_venta,$datosventa[18]);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}
}


editarDetallesVenta($cod_venta," *Contado ".$monto);
actualizarMetodo($cod_venta,"Corrido");


if($controlTipoPago == 0){
	$titulopago=buscarpagosTitulo($cod_venta,$nrofactura);
$paginaticket=buscar_detalles_venta($cod_venta);
$informacion =array("1" => "exito","2" => number_format($datosventa[1],'0',',','.'),"3"=>$paginaticket,"4"=>$titulopago[2] );
echo json_encode($informacion);	
exit;
}

}
 */
 
 function abmcontado($cod_venta,$descuento,$monto,$montotarjerta,$cajapredeterminada,$codApertura,$tipopago,$controlTipoPago,$controlTotal)
{
if($cod_venta==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$datosventa=buscardatosventa($cod_venta);
$nrofactura=buscarnrofactura();
$descripcion="ventas";

$mysqli=conectar_al_servidor(); 
 

// if($controlTotal == "1"){
// $consulta="delete from credito where  cod_venta='$cod_venta'";
// $stmt = $mysqli->prepare($consulta);
// if ( ! $stmt->execute()) {
// echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
// exit;
// }

// $consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo,descuento)
			// values('Contado','$datosventa[0]','$cod_venta','$datosventa[1]','Pendiente','0','$descuento')";

// $stmt = $mysqli->prepare($consulta);
// if ( ! $stmt->execute()) {
   // echo "Error";
   // exit;
// }
// }
 
if($monto>0){
	
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK,nrofactura)
values((select idcredito from credito where cod_venta='$cod_venta' and plazo='Contado' limit 1),'$monto',CURDATE(),'$datosventa[5]','$cod_venta','$datosventa[18]','Pago Cuota','Efectivo','$cajapredeterminada','$codApertura','$descripcion','$tipopago','$nrofactura')";



$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}
}
 


editarDetallesVenta($cod_venta," *Contado ".$monto);
actualizarMetodo($cod_venta,"Corrido");


if($controlTipoPago == 0){
	$titulopago=buscarpagosTitulo($cod_venta,$nrofactura);
$paginaticket=buscar_detalles_venta($cod_venta);
$informacion =array("1" => "exito","2" => number_format($datosventa[1],'0',',','.'),"3"=>$paginaticket,"4"=>$titulopago[2] );
echo json_encode($informacion);	
exit;
}

}

 
function actualizarMetodo($cod_venta,$Metodo){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set TipoPago=?,TipoVenta='CONTADO' where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$Metodo,$cod_venta); 

if (!$stmt1->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
 mysqli_close($mysqli);

}

function cambiarcomision($idPago,$comision)
{
	
	

if($idPago==""  || $comision=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor();


$consulta1="update pago set comision=? where idPago=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$comision,$idPago);




if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function cargarpagos($CargoAdministrativo, $MontoEfectivo, $MontoTarjeta, $MontDescuento, $Fecha, $cod_cobradorFK, $cod_venta, $controlfecha, $nrofactura, $codCaja, $codApertura, $codTipoPago, $controlTipoPago)
{
	$mysqli = conectar_al_servidor();




	$sql = "Select Monto,idcredito,cr.fechapago,total,plazo,(totalinteres + cr.deudaInteres) as interes,cr.descuento,vt.num_factura,vt.puntoexpedicion,
	(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
	(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and tipo='Pago Cuota'),0) as totalPago,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and tipo='Interes'),0) as totalPagoInteres
	from credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
	where cr.cod_venta='$cod_venta' and IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and tipo='Pago Cuota'),0) < (cr.Monto-cr.descuento)order by cr.idcredito asc";
	$pagado = $MontoTarjeta + $MontoEfectivo;
	$clientenombre = "";
	$nrodocliente = "";
	$stmt = $mysqli->prepare($sql);

	if (! $stmt->execute()) {
		echo trigger_error('The query execution failed; MySQL said (' . $stmt1->errno . ') ' . $stmt1->error, E_USER_ERROR);
		exit;
	}

	$result = $stmt->get_result();
	$valor = mysqli_num_rows($result);
	$nroRegistro = $valor;
	$montoDescuento = 0;

	$ControlPagoCargoAdmin = 0;

	$plazo = '';
	if ($valor > 0) {
		while ($valor = mysqli_fetch_assoc($result)) {

			$idcredito = $valor['idcredito'];
			$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');
			$totalinteres = mb_convert_encoding((string)($valor['interes']), 'UTF-8', 'ISO-8859-1');
			$totalPago = mb_convert_encoding((string)($valor['totalPago']), 'UTF-8', 'ISO-8859-1');
			$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1');
			$nrodocliente = mb_convert_encoding((string)($valor['nrodocliente']), 'UTF-8', 'ISO-8859-1');
			$totalpagainteres = mb_convert_encoding((string)($valor['totalPagoInteres']), 'UTF-8', 'ISO-8859-1');
			$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1');
			$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
			$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
			if ($puntoexpedicion != "") {
				$nrof = $puntoexpedicion . "-" . $num_factura;
			} else {
				$nrof = $num_factura;
			}




			if ($CargoAdministrativo == "") {
				$CargoAdministrativo = "0";
			}

			if ($CargoAdministrativo != "0" && $pagado != "0" && $ControlPagoCargoAdmin == 0) {
				$descripcion = "Pago de Cargo Administrativo, Factura Nro: *" . $nrof . "*";
				$consulta1 = "Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'CARGO ADMINISTRATIVO','Efectivo',?,?,?,'$codTipoPago')";
				$stmt1 = $mysqli->prepare($consulta1);
				$ss = 'sssssssss';
				$stmt1->bind_param($ss, $idcredito, $CargoAdministrativo, $Fecha, $cod_cobradorFK, $cod_venta, $nrofactura, $codCaja, $codApertura, $descripcion);

				if (!$stmt1->execute()) {
					echo trigger_error('The query execution failed; MySQL said (' . $stmt1->errno . ') ' . $stmt1->error, E_USER_ERROR);
					exit;
				}

				$pagado = $pagado - $CargoAdministrativo;
				$ControlPagoCargoAdmin = 1;
			}


			$montoDescuento = 0;
			if ($MontDescuento > 0) {

				$controldescuento = ($Monto + $totalinteres) - ($totalPago + $descuento);
				if ($controldescuento < 0) {
					$controldescuento = 0;
				}
				if ($MontDescuento > $controldescuento) {
					$sobrantedescuento = $MontDescuento - $controldescuento;
					$MontDescuento = $sobrantedescuento;
					$montoDescuento = ($Monto + $totalinteres) - ($totalPago + $descuento);
				} else {
					$montoDescuento = $MontDescuento;
					$MontDescuento = 0;
				}
			}



			if ($totalinteres > 0) {

				$totaldeudacontrol = ($Monto + $totalinteres) - ($totalPago + $descuento + $montoDescuento);


				if ($totaldeudacontrol > ($pagado + $MontDescuento)) {
					// REGLA DE TRES PARA CALCULAR INTERES
					// deudacuota=totalInteres;
					// pagado=x
					$deudacuota = $Monto - $totalPago;
					if ($totalinteres >= $pagado) {
						$interescobrar = $pagado;

						$MontoInteresGuardar = $totalinteres - $pagado;

						if ($pagado != 0) {
							GuardarDeudaInteres($MontoInteresGuardar, $idcredito);
						}
					} else {
						$interescobrar = $totalinteres;
						GuardarDeudaInteres("0", $idcredito);
					}
					//$interescobrar=($pagado*$totalinteres)/$deudacuota;
					$pago = $interescobrar;
					$pagado = $pagado - $pago;
				} else {
					$pago = $totalinteres;
					$pagado = $pagado - $pago;
				}

				$descripcion = "Pago de intereses, Factura Nro: *" . $nrof . "*";
				// if( $MontoTarjeta>0){

				// if($pago>$MontoTarjeta){
				// $pago1=$MontoTarjeta;
				// cargarPagosDeudas($pago1,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$nrofactura,"Interes","Tarjeta",$codCaja,$codApertura,$descripcion,$codTipoPago);
				// $pago2=$pago-$MontoTarjeta;
				// cargarPagosDeudas($pago2,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$nrofactura,"Interes","Efectivo",$codCaja,$codApertura,$descripcion,$codTipoPago);
				// }else{
				// $MontoTarjeta=$MontoTarjeta-$pago;
				// cargarPagosDeudas($pago,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$nrofactura,"Interes","Tarjeta",$codCaja,$codApertura,$descripcion,$codTipoPago);
				// }
				// }else{
				cargarPagosDeudas($pago, $Fecha, $cod_cobradorFK, $idcredito, $cod_venta, $nrofactura, "Interes", "Efectivo", $codCaja, $codApertura, $descripcion, $codTipoPago);
				// }


				$totalPago = $totalPago + $pago;
			}
			$deuda = ($Monto + $totalinteres) - ($totalPago + $descuento + $montoDescuento);
			$c = 1;
			if ($pagado <= 0) {
				$c = 0;
				$pago = 0;
			}
			$control = $pagado - $deuda;
			if ($control <= 0) {
				$pago = $pagado;
				$pagado = 0;
			} else {
				$pago = $deuda;
				$pagado = $pagado - $deuda;
			}
			if ($controlfecha == "2") {
				$Fecha = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');
			}

			if ($pago > 0 && $c == 1) {
				GuardarDeudaInteres("0", $idcredito);
				$descripcion = "Pago de Cuotas, Factura Nro: *" . $nrof . "*";
				// if( $MontoTarjeta>0){


				// if($pago>$MontoTarjeta){
				// $pago1=$MontoTarjeta;
				// cargarPagosDeudas($pago1,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$nrofactura,"Pago Cuota","Tarjeta",$codCaja,$codApertura,$descripcion,$codTipoPago);
				// $pago2=$pago-$MontoTarjeta;
				// cargarPagosDeudas($pago2,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$nrofactura,"Pago Cuota","Efectivo",$codCaja,$codApertura,$descripcion,$codTipoPago);
				// }else{
				// $MontoTarjeta=$MontoTarjeta-$pago;
				// cargarPagosDeudas($pago,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$nrofactura,"Pago Cuota","Tarjeta",$codCaja,$codApertura,$descripcion,$codTipoPago);
				// }
				// }else{
				cargarPagosDeudas($pago, $Fecha, $cod_cobradorFK, $idcredito, $cod_venta, $nrofactura, "Pago Cuota", "Efectivo", $codCaja, $codApertura, $descripcion, $codTipoPago);
				// }


				if (($pago + $montoDescuento) >= $deuda) {
					if ($plazo != "") {
						$plazo .= ", " . mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');
					} else {
						$plazo .= mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');
					}
				} else {
					if ($plazo != "") {
						$plazo .= " y pago parcial en cuota " . mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1') . " ";
					} else {
						$plazo .= "Pago parcial en cuota " . mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1') . " ";
					}
				}
			}

			if ($montoDescuento > 0) {
				actualizarDescuento($idcredito, $montoDescuento);
			}
		}
	}

	if ($controlTipoPago == 0) {
		$datosTicket = calcularintereses2($cod_venta, 0, 0, "2", "2", "2", "no");

		$totalDescuento = $datosTicket[0];
		$totalinteresespagado = $datosTicket[12];
		$totalpagado = $datosTicket[3];
		$acobrar = $datosTicket[4];
		$deuda = $datosTicket[4];
		$totalDeuda = $datosTicket[4];
		$totalVenta = $datosTicket[11];
		$InteresActual = $datosTicket[10];
		$totalsininteres = $datosTicket[7];

		$totalPagado = buscartotalpagob($cod_venta);
		//$totalVenta=buscartotalventa($cod_venta);
		// $paginaticket=buscar_detalles_venta($cod_venta);

		$paginaticket = buscarDetalleVentaImprimir($cod_venta);
		$paginaticket2 = "Factura nro: " . $paginaticket[2];

		$titulopago = buscarpagosTitulo($cod_venta, $nrofactura);

		$datoVenta = buscardatosventa($cod_venta);
		//addMasCuotas($cod_venta,$totalPagado);
		$informacion = array(
			"1" => "exito",
			"2" => number_format($totalPagado, '0', ',', '.'),
			"3" =>  number_format($totalVenta, '0', ',', '.'),
			"4" =>  number_format($totalDeuda, '0', ',', '.'),
			"5" => $paginaticket2,
			"6" => $plazo,
			"7" => $clientenombre,
			"8" => $nrodocliente,
			"9" => $nrofactura,
			"10" => $datoVenta[6],
			"11" =>  number_format($totalinteresespagado, '0', ',', '.'),
			"12" =>  number_format($deuda, '0', ',', '.'),
			"13" => number_format($totalpagado, '0', ',', '.'),
			"14" => number_format($totalDescuento, '0', ',', '.'),
			"15" => number_format($InteresActual, '0', ',', '.'),
			"16" => number_format($totalsininteres, '0', ',', '.'),
			"17" => $datoVenta[2],
			"18" => $Fecha,
			"19" => $titulopago[2]
		);
		echo json_encode($informacion);
		exit;
	}
}

function actualizarDescuento($idcredito,$descuento){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update credito set descuento=descuento+'$descuento' where idcredito='$idcredito'";	

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
 mysqli_close($mysqli);

}

function  cargarPagosDeudas($Monto,$Fecha,$cod_cobradorFK,$cod_creditoFK,$cod_venta,$nrofactura,$tipo,$tipopago,$codCaja,$codApertura,$descripcion,$codtipoPago){
	  
	  
	 if($Monto!="0"){
		  	  
	$mysqli=conectar_al_servidor();
	$consulta="Insert into pago (Monto,Fecha,cod_creditoFK,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,tipopago,codCaja,codApertura,descripcion,cod_tipoPagoFK) 
	values('$Monto','$Fecha','$cod_creditoFK','$cod_cobradorFK','$cod_venta',(select comision from venta where cod_venta='$cod_venta'),'$nrofactura','$tipo','$tipopago','$codCaja','$codApertura','$descripcion','$codtipoPago')";	
	
	$stmt = $mysqli->prepare($consulta);
	
if ( ! $stmt->execute()) {
   /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	  }

}
// 3897087
// cristian gobzalez monjelos
// function buscarnrofactura()
// {
	
	
	// $mysqli=conectar_al_servidor();
	 // $sql= "Select count(cod_creditoFK) from pago ";
   // $stmt = $mysqli->prepare($sql);

// if ( ! $stmt->execute()) {
   // echo "Error";
   // exit;
// }

// $result = $stmt->get_result();
// $NroFactura=$result->fetch_row();
  // $NroFactura=$NroFactura[0];
  
 // if($NroFactura<10){
	 // $NroFactura="0000".$NroFactura;
 // }else{
 // if($NroFactura<100){
	 // $NroFactura="000".$NroFactura;
 // }else{
	 // if($NroFactura<1000){
	 // $NroFactura="00".$NroFactura;
    // } 
 // }
 // }
  // mysqli_close($mysqli); 
  
 // return $NroFactura;


// }



function buscarnrofactura()
{
	
	
	$mysqli=conectar_al_servidor();
	 $sql= " Select Fecha, cod_creditoFK, nrofactura ,(CAST(nrofactura AS UNSIGNED) ) as nro  from pago where length(nrofactura)=7 
	 group by  nrofactura order by idPago desc limit 1 ";
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

// $result = $stmt->get_result();
// $NroFactura=$result->fetch_row();
  

  $result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

$NroFactura=1;

 if ($valor>0){
	  while ($valor= mysqli_fetch_assoc($result))
		{
			 $NroFactura=$valor['nro'];
			 $NroFactura ++;
		}
 
 } 
  
  
  
 if($NroFactura<10){
	 $NroFactura="000000".$NroFactura;
 }
 if($NroFactura<100 && $NroFactura>=10){
	 $NroFactura="00000".$NroFactura;
 }
 if($NroFactura<1000 && $NroFactura>=100){
	 $NroFactura="0000".$NroFactura;
  } 
  
  if($NroFactura<10000 && $NroFactura>=1000){
	 $NroFactura="000".$NroFactura;
  } 
  
  if($NroFactura<100000 && $NroFactura>=10000){
	 $NroFactura="00".$NroFactura;
  } 
  if($NroFactura<1000000 && $NroFactura>=100000){
	 $NroFactura="0".$NroFactura;
  } 
 
 
  mysqli_close($mysqli); 
  
 return $NroFactura;


}




/*Funcion para insertar,modificar o eliminar registros*/
function quitarpago($idFkVenta,$cod_creditoFK,$motivo,$monto,$cuota,$nrofactura,$user)
{
	
$datosPagos=buscardatospagos($cod_creditoFK,"2");

$mysqli=conectar_al_servidor(); 
$consulta1="delete from pago where cod_creditoFK='$cod_creditoFK' ";	

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$consulta1="Insert into pagoseliminados (motivo, monto, cuota, fecha, cod_usuario, nroventa)
values('$motivo','$monto','$cuota',CURRENT_TIMESTAMP,'$user','$nrofactura')";
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}




function quitarhistorialpago($cod_pago,$codVenta)
{
	

$datosPagos=buscardatospagos($cod_pago,"1");


$mysqli=conectar_al_servidor(); 
$consulta1="delete from pago where idPago='$cod_pago' ";
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

$user=$_POST['useru'];

$MontoPago=number_format($datosPagos[1],'0',',','.');
$consulta1="Insert into pagoseliminados (motivo, monto, cuota, fecha, cod_usuario, nroventa,cod_ventaFK)
values('Eliminado Desde Arqueo - Sistema','$MontoPago','XX',CURRENT_TIMESTAMP,'$user','$datosPagos[2]',$codVenta)";
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}
$DetalleCreditoPagoInteres=buscardatospagosTipo($datosPagos[3],"1");
$DetalleCreditoPagoCuota=buscardatospagosTipo($datosPagos[3],"2");
$DetalleCreditoPagoTotal=buscardatospagosTipo($datosPagos[3],"3");

$datos=calcularintereses2($datosPagos[3],0,0,"2","2","1","no");

$condicionDeudaInteres=buscardatosCreditoDeuda($datosPagos[3]);

$total_interes=$datos[1];
if($datosPagos[4]=='Interes' ){
		
	if($DetalleCreditoPagoInteres=="0" && $DetalleCreditoPagoCuota!="0" ){
	$consulta1="update credito set deudaInteres= ((deudaInteres + ".$datosPagos[1].")) where idcredito=$datosPagos[3] ";
 
		$stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
		exit;
		}		
	}
	
	if($DetalleCreditoPagoInteres!="0" ){
		$consulta1="update credito set deudaInteres=(deudaInteres + ".$datosPagos[1]." - $total_interes) where idcredito=$datosPagos[3] ";
		$stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
		exit;
		}		
	}	

	if($DetalleCreditoPagoCuota==0 && $condicionDeudaInteres!=0 ){
	$consulta1="update credito set deudaInteres= (deudaInteres + ".$datosPagos[1]."  - $total_interes )  where idcredito=$datosPagos[3] ";
		// echo($consulta1);
		// exit;
		
		$stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
		exit;
		}

	}
	
	if($DetalleCreditoPagoCuota==0 && $condicionDeudaInteres==0){
			$consulta1="update credito set deudaInteres=0 , totalinteres=0 , totaldeuda=0, total=0 where idcredito=$datosPagos[3] ";
		
		$stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
		exit;
		}
	}
	
	
}



if($datosPagos[4]!='Interes' ){
	
	
	if($DetalleCreditoPagoCuota==0){
			$consulta1="update credito set deudaInteres=0 , totalinteres=0 , totaldeuda=0, total=0 where idcredito=$datosPagos[3] ";
		
		$stmt1 = $mysqli->prepare($consulta1);
		if (!$stmt1->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
		exit;
		}
	}
	
}
	
	

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


/*Buscar */
function buscartotalpago($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select IFNULL(sum(Monto),0) as totalpago from pago where cod_creditoFK='$buscar'";/*Sentencia para buscar registros*/
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



$totalpago = mb_convert_encoding((string)($valor['totalpago']), 'ISO-8859-1', 'UTF-8');/*Obtenemos el registro mediante el nombre del atributo */      




}
}

return $totalpago;
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



$totalVenta = mb_convert_encoding((string)($valor['totalVenta']), 'UTF-8', 'ISO-8859-1');/*Obtenemos el registro mediante el nombre del atributo */      




}
}

return $totalVenta;
}




/*Buscar */
function buscartotalpagob($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select sum(pg.Monto) as totalpago,vt.pago as totalEntrega
 from pago pg inner join venta vt  on vt.cod_venta=pg.cod_venta_fk 
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



$totalpago = mb_convert_encoding((string)($valor['totalpago']), 'ISO-8859-1', 'UTF-8');/*Obtenemos el registro mediante el nombre del atributo */      
$totalEntrega = mb_convert_encoding((string)($valor['totalEntrega']), 'ISO-8859-1', 'UTF-8');      
$totalpago=$totalpago+$totalEntrega ;



}
}

return $totalpago;
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




/*Buscar */
function buscarhistorialpagos($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select idPago,Fecha,Monto,nrofactura,tipo, cod_venta_fk as cod_venta ,
(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre
 from pago where cod_creditoFK='$buscar'";

 $pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$cod_venta = mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1'); 
$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$nrofactura = mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');      
$totalPagado=$Monto+$totalPagado;
 	
$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatospagos(this)'>
<td id='td_datos_1' style='display:none' >".$idPago."</td>
<td id='td_datos_6' style='width:10%' >".$nrofactura."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_2' style='width:10%' >".$Fecha."</td>
<td id='td_datos_7' style='width:10%'>".$tipo."</td>
<td id='td_datos_3' style='width:10%'>".$cobradornombre."</td>

<td id='td_datos_10' style='display:none'>".$cod_venta."</td>

</tr>
</table>
";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"4" =>$nroRegistro  );
echo json_encode($informacion);	
exit;
}

/*Buscar */
function buscarHistorialPagosAReimprimir($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select vt.cod_venta, cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,
vt.TipoVenta,vt.total_venta,pg.titulocuota,vt.puntoexpedicion,vt.num_factura,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as clientedoc,
datediff(pg.Fecha,(select cr.fechapago from credito cr where pg.cod_creditoFK=cr.idcredito limit 1)) as diff,
(Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK) as cobradornombre
 from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk
 inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where pg.cod_venta_fk='$buscar' and pg.Monto!=0 order by pg.idPago ";
 
 // echo($sql);
 // exit;

$datos=calcularintereses2($buscar,0,0,"2","2","2","no");
$totalEnDescuento=$datos[0];
$totalapagarinteres=$datos[1];
$totalInteres=$datos[12];
$totalApagar=$datos[4];
$diasatrasado=$datos[5];
$acobrar=$datos[8];
$totalCredito=$datos[11];
$totalpagado=$datos[3];
$TotalPagadoSinInteres=$datos[13];
$TotalApagarSinInteres=$datos[7];

 $pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlNroFactura="";
$titulopago="";
$montopago="0";
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$cod_venta = mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1');  
$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$nrofactura = mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');      
$diff = mb_convert_encoding((string)($valor['diff']), 'UTF-8', 'ISO-8859-1');      
$clientedoc = mb_convert_encoding((string)($valor['clientedoc']), 'UTF-8', 'ISO-8859-1');      
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1');      
$TipoVenta = mb_convert_encoding((string)($valor['TipoVenta']), 'UTF-8', 'ISO-8859-1');      
$total_venta = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1');      
$titulocuota = mb_convert_encoding((string)($valor['titulocuota']), 'UTF-8', 'ISO-8859-1');      
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$montocredito = mb_convert_encoding((string)($valor['montocredito']), 'UTF-8', 'ISO-8859-1'); 

$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');  

if($diff<=0){
$diff=0;	
}
   $DetalleDescripcionVenta=buscarDetalleVentaImprimir($cod_venta);


$montopago=0;
$montopago=$montopago+$Monto;
$titulopago=buscarpagosTitulo($cod_venta,$nrofactura);
$totalPagado=$titulopago[1];
$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerPagosReImprimir(this)'>
<td id='' style='display:none' >".$idPago."</td>
<td id='td_datos_1' style='width:10%' >".$nrofactura."</td>
<td id='td_datos_2' style='width:10%'>". number_format($montopago,'0',',','.')."</td>
<td id='td_datos_21' style='width:10%' >".$plazo."</td>
<td id='td_datos_22' style='width:15%' >".$tipo."</td>
<td id='td_datos_3' style='width:15%' >".$Fecha."</td>
<td id='td_datos_4' style='width:40%'>".$cobradornombre."</td>
<td id='td_datos_5' style='display:none'>".$diff."</td>
<td id='td_datos_6' style='display:none'>".$clientedoc."</td>
<td id='td_datos_7' style='display:none'>".$clientenombre."</td>
<td id='td_datos_8' style='display:none'>".$TipoVenta."</td>
<td id='td_datos_9' style='display:none'>".  number_format($totalEnDescuento,'0',',','.')."</td>
<td id='td_datos_10' style='display:none'>". number_format($totalInteres,'0',',','.')."</td>
<td id='td_datos_11' style='display:none'>". number_format($totalApagar,'0',',','.') ."</td>
<td id='td_datos_12' style='display:none'>".$diasatrasado."</td>
<td id='td_datos_13' style='display:none'>". number_format($acobrar,'0',',','.') ."</td>
<td id='td_datos_14' style='display:none'>". number_format($totalCredito,'0',',','.')  ."</td>
<td id='td_datos_15' style='display:none'>". number_format($totalPagado,'0',',','.')  ."</td>
<td id='td_datos_16' style='display:none'>". number_format($total_venta,'0',',','.')  ."</td>
<td id='td_datos_17' style='display:none'>". number_format($TotalPagadoSinInteres,'0',',','.') ."</td>
<td id='td_datos_18' style='display:none'>". number_format($TotalApagarSinInteres,'0',',','.')  ."</td>
<td id='td_datos_20' style='display:none'>". number_format($totalapagarinteres,'0',',','.') ."</td>
<td id='td_datos_19' style='display:none'>". $titulopago[0] ."</td>
<td id='td_datos_23' style='display:none'>". $puntoexpedicion."-".$num_factura."</td>
<td id='td_datos_24' style='display:none'>". $titulopago[2] ."</td>
<td id='td_datos_25' style='display:none'>Factura nro: ". $DetalleDescripcionVenta[2] ."</td>
</tr>
</table>
";



if($controlNroFactura==""){
$controlNroFactura=$nrofactura;
}
/*if($controlNroFactura!=$nrofactura){

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerPagosReImprimir(this)'>
<td id='' style='display:none' >".$idPago."</td>
<td id='td_datos_1' style='width:10%' >".$nrofactura."</td>
<td id='td_datos_2' style='width:10%'>". number_format($montopago,'0',',','.')."</td>
<td id='td_datos_3' style='width:10%' >".$Fecha."</td>
<td id='td_datos_4' style='width:10%'>".$cobradornombre."</td>
<td id='td_datos_5' style='display:none'>".$diff."</td>
<td id='td_datos_6' style='display:none'>".$clientedoc."</td>
<td id='td_datos_7' style='display:none'>".$clientenombre."</td>
<td id='td_datos_8' style='display:none'>".$TipoVenta."</td>
<td id='td_datos_9' style='display:none'>".  number_format($totalEnDescuento,'0',',','.')."</td>
<td id='td_datos_10' style='display:none'>". number_format($totalInteres,'0',',','.')."</td>
<td id='td_datos_11' style='display:none'>". number_format($totalApagar,'0',',','.') ."</td>
<td id='td_datos_12' style='display:none'>".$diasatrasado."</td>
<td id='td_datos_13' style='display:none'>". number_format($acobrar,'0',',','.') ."</td>
<td id='td_datos_14' style='display:none'>". number_format($totalCredito,'0',',','.')  ."</td>
<td id='td_datos_15' style='display:none'>". number_format($totalpagado,'0',',','.')  ."</td>
<td id='td_datos_16' style='display:none'>". number_format($total_venta,'0',',','.')  ."</td>
<td id='td_datos_17' style='display:none'>". number_format($TotalPagadoSinInteres,'0',',','.') ."</td>
<td id='td_datos_18' style='display:none'>". number_format($TotalApagarSinInteres,'0',',','.')  ."</td>
<td id='td_datos_20' style='display:none'>". number_format($totalapagarinteres,'0',',','.') ."</td>
<td id='td_datos_19' style='display:none'>". $titulopago ."</td>
</tr>
</table>
";
$titulopago="";

 if($montocredito>$Monto){
		if($titulopago!=""){
			$titulopago.=", Cuota Parcial en ".$plazo;
		}else{
			$titulopago.="Cuota Parcial en ".$plazo;
		}	
}else{
	if($titulopago!=""){
			$titulopago.=",".$plazo;
		}else{
			$titulopago.=$plazo;
		}	
} 
$controlNroFactura=$nrofactura;

}else{
   $montopago=$montopago+$Monto;
	if($montocredito>$Monto){
		if($titulopago!=""){
			$titulopago.=",Cuota Parcial en ".$plazo;
		}else{
			$titulopago.="Cuota Parcial en ".$plazo;
		}	
}else{
	if($titulopago!=""){
			$titulopago.=",".$plazo;
		}else{
			$titulopago.=$plazo;
		}	
}

}*/

}

//$titulopago=buscarpagosTitulo($CodVenta,$NroFactura);
/*if($titulopago!=""){
	
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerPagosReImprimir(this)'>
<td id='' style='display:none' >".$idPago."</td>
<td id='td_datos_1' style='width:10%' >".$nrofactura."</td>
<td id='td_datos_2' style='width:10%'>". number_format($montopago,'0',',','.')."</td>
<td id='td_datos_3' style='width:10%' >".$Fecha."</td>
<td id='td_datos_4' style='width:10%'>".$cobradornombre."</td>
<td id='td_datos_5' style='display:none'>".$diff."</td>
<td id='td_datos_6' style='display:none'>".$clientedoc."</td>
<td id='td_datos_7' style='display:none'>".$clientenombre."</td>
<td id='td_datos_8' style='display:none'>".$TipoVenta."</td>
<td id='td_datos_9' style='display:none'>".  number_format($totalEnDescuento,'0',',','.')."</td>
<td id='td_datos_10' style='display:none'>". number_format($totalInteres,'0',',','.')."</td>
<td id='td_datos_11' style='display:none'>". number_format($totalApagar,'0',',','.') ."</td>
<td id='td_datos_12' style='display:none'>".$diasatrasado."</td>
<td id='td_datos_13' style='display:none'>".  number_format($acobrar,'0',',','.') ."</td>
<td id='td_datos_14' style='display:none'>".  number_format($totalCredito,'0',',','.')  ."</td>
<td id='td_datos_15' style='display:none'>". number_format($totalpagado,'0',',','.')  ."</td>
<td id='td_datos_16' style='display:none'>". number_format($total_venta,'0',',','.')  ."</td>
<td id='td_datos_17' style='display:none'>". number_format($TotalPagadoSinInteres,'0',',','.') ."</td>
<td id='td_datos_18' style='display:none'>".  number_format($TotalApagarSinInteres,'0',',','.')  ."</td>
<td id='td_datos_20' style='display:none'>". number_format($totalapagarinteres,'0',',','.') ."</td>
<td id='td_datos_19' style='display:none'>". $titulopago ."</td>
</tr>
</table>
";
	
}
*/




}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"4" =>$nroRegistro  );
echo json_encode($informacion);	
exit;
}








function buscarpagoseliminados($fecha1,$fecha2)
{
$mysqli=conectar_al_servidor();
$condicionfecha="";
if($fecha1!="" && $fecha2!=""){
	$condicionfecha=" and fecha>='$fecha1 00:00:00'and fecha<='$fecha2 23:59:59'";
}
$sql= "select idpagoseliminados, motivo, monto, cuota, fecha, cod_usuario, nroventa,
(Select nombre_persona from persona where cod_persona=cod_usuario) as nombreusuario
 from pagoseliminados where idpagoseliminados!='0' ".$condicionfecha;
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



$idpagoseliminados = mb_convert_encoding((string)($valor['idpagoseliminados']), 'UTF-8', 'ISO-8859-1');    
$motivo = mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');      
$monto = mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');      
$cuota = mb_convert_encoding((string)($valor['cuota']), 'UTF-8', 'ISO-8859-1');      
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');      
$nombreusuario = mb_convert_encoding((string)($valor['nombreusuario']), 'UTF-8', 'ISO-8859-1');      
$nroventa = mb_convert_encoding((string)($valor['nroventa']), 'UTF-8', 'ISO-8859-1');


   
$styleName=CargarStyleTable($styleName);
$pagina.="<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  >
<td style='width:10%'>".$nroventa."</td>
<td style='width:10%'>".$monto."</td>
<td style='width:10%'>".$cuota."</td>
<td style='width:10%'>".$motivo."</td>
<td style='width:10%'>".$fecha."</td>
<td style='width:10%'>".$nombreusuario."</td>
</tr>
</table>";

}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($nroRegistro,'0',',','.') );
echo json_encode($informacion);	
exit;
}






/*Buscar */
function Arqueo($fecha1,$fecha2,$local,$factura,$cliente,$cedula,$fechafija,$cobrador,$metodo,$codCaja,$condicion)
{

$mysqli=conectar_al_servidor();

 $totalRegistro=0;
	 $pagina="";

	 $sqlFiltro= "";
	 if($fecha1!="" && $fecha2!=""){
		 $sqlFiltro .=" and pg.Fecha between'".$fecha1."' and '".$fecha2."'";
	 }
	 if($fechafija!=""){
	   $sqlFiltro .=" and pg.Fecha='".$fechafija."'";		
	 }
	 if($factura!=""){
	   $sqlFiltro .=" and vt.num_factura like '%".$factura."%'";		
	 }
	 if($metodo!=""){
	   $sqlFiltro .=" and pg.tipopago = '".$metodo."'";		
	 }
	 if($local!=""){
	   $sqlFiltro .=" and (Select l.cod_local from local l  where l.cod_local= vt.cod_local limit 1)='".$local."'";		
	 }
	 if($cliente!=""){
	   $sqlFiltro .=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK limit 1) like '%".$cliente."%'";		
	 }
	 if($cobrador!=""){
	   $sqlFiltro .=" and (Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK limit 1) like '%".$cobrador."%'";		
	 }
	 if($codCaja!=""){
	   $sqlFiltro .=" and codAperturaApp = '".$codCaja."'";		
	 }
	 if($cedula) {
	   $sqlFiltro .=" and (Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK limit 1) like '%".$cedula."%'";
	 };
 
	 if($condicion!=""){
	   $sqlFiltro .=" and vt.TipoVenta = '".$condicion."'";		
	 }

	$sql= "select  vt.TipoVenta,vt.puntoexpedicion,vt.tipo_comprobante,pg.idPago,pg.tipo, pg.Fecha, sum(pg.Monto) as Monto,pg.cod_venta_fk,pg.tipopago,
	vt.cod_local, pg.cod_cobradorFK,
	pg.comision,pg.nrofactura,pg.lot, pg.lat,(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as nombrecliente,
	(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as documento,
	(Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK) as cobradornombre,date_format(hora ,'%H:%i' ) as hora,
	(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
	(Select plazo from credito l where l.idcredito=pg.cod_creditoFK) as plazo,
	IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
	vt.num_factura,
	(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=pg.cod_venta_fk)) as nombrezona
	from  pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk  
	where pg.Monto>0 ".$sqlFiltro." group by  pg.idPago limit 2500";
	

$registros= array();
 $pagina = "";   
 $paginaentrega = "";   
 $paginacuota = "";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$totalPagadoEfectivo=0;
$totalPagadoTarjeta=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$TipoVenta = mb_convert_encoding((string)($valor['TipoVenta']), 'UTF-8', 'ISO-8859-1'); 
$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$cod_cobradorFK = mb_convert_encoding((string)($valor['cod_cobradorFK']), 'UTF-8', 'ISO-8859-1');      
$cod_venta = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');      
$nombrezona = mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1');      
$hora = mb_convert_encoding((string)($valor['hora']), 'UTF-8', 'ISO-8859-1');      
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1');      
$lot = mb_convert_encoding((string)($valor['lot']), 'UTF-8', 'ISO-8859-1');      
$lat = mb_convert_encoding((string)($valor['lat']), 'UTF-8', 'ISO-8859-1');      
$nombrecliente = mb_convert_encoding((string)($valor['nombrecliente']), 'UTF-8', 'ISO-8859-1');      
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');      
$cod_local = mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');      
$nrofactura = mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1');      
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$tipo_comprobante = mb_convert_encoding((string)($valor['tipo_comprobante']), 'UTF-8', 'ISO-8859-1');      
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
$tipo_comprobante=mb_convert_encoding((string)($valor['tipo_comprobante']), 'UTF-8', 'ISO-8859-1');
$tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
$nroCancelado=mb_convert_encoding((string)($valor['nroCancelado']), 'UTF-8', 'ISO-8859-1');
$tipopago=mb_convert_encoding((string)($valor['tipopago']), 'UTF-8', 'ISO-8859-1');
$documento=mb_convert_encoding((string)($valor['documento']), 'UTF-8', 'ISO-8859-1');

$registros[]= array(
	'TipoVenta' => mb_convert_encoding((string)($valor['TipoVenta']), 'UTF-8', 'ISO-8859-1'),
	'idPago' => mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1'),
	'num_factura' => mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'),
	'Monto' => mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'),
	'Fecha' => mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1'),
	'cobradornombre' => mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1'),
	'cod_cobradorFK' => mb_convert_encoding((string)($valor['cod_cobradorFK']), 'UTF-8', 'ISO-8859-1'),
	'cod_venta' => mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1'),
	'nombrezona' => mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1'),
	'hora' => mb_convert_encoding((string)($valor['hora']), 'UTF-8', 'ISO-8859-1'),
	'comision' => mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'),
	'lot' => mb_convert_encoding((string)($valor['lot']), 'UTF-8', 'ISO-8859-1'),
	'lat' => mb_convert_encoding((string)($valor['lat']), 'UTF-8', 'ISO-8859-1'),
	'nombrecliente' => mb_convert_encoding((string)($valor['nombrecliente']), 'UTF-8', 'ISO-8859-1'),
	'nombrelocal' => mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'),
	'cod_local' => mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1'),
	'nrofactura' => mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1'),
	'plazo' => mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1'),
	'tipo_comprobante' => mb_convert_encoding((string)($valor['tipo_comprobante']), 'UTF-8', 'ISO-8859-1'),
	'puntoexpedicion' => mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'),
	'tipo_comprobante'=>mb_convert_encoding((string)($valor['tipo_comprobante']), 'UTF-8', 'ISO-8859-1'),
	'tipo'=>mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'),
	'nroCancelado'=>mb_convert_encoding((string)($valor['nroCancelado']), 'UTF-8', 'ISO-8859-1'),
	'tipopago'=>mb_convert_encoding((string)($valor['tipopago']), 'UTF-8', 'ISO-8859-1'),
	'documento'=>mb_convert_encoding((string)($valor['documento']), 'UTF-8', 'ISO-8859-1'),
);

if($tipopago=="Efectivo"){
	$totalPagadoEfectivo=$totalPagadoEfectivo+$Monto;
}else{
	$totalPagadoTarjeta=$totalPagadoTarjeta+$Monto;
}

$style='';
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}   
if($nroCancelado==0){
$totalPagado=$Monto+$totalPagado;
}else{
	$style='background-color: #FFEB3B;color:#000';
}

if($plazo!="ENTREGA"){
	$styleName=CargarStyleTable($styleName);
$paginacuota.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'onclick='obtenerdatospagos(this)' style='$style'  >
<td id='td_datos_1' style='display:none' >".$idPago."</td>
<td id='td_datos_3' style='display:none'>".$num_factura."</td>

<td id='td_datos_9' style='width:15%'>*".$documento."*<br>".$nombrecliente." </td>
<td style='width:15%'>".$documento." </td>
<td id=''			 style='width:10%'>".$nrof."</td>
<td id='td_datos_2' style='display:none' >".$Fecha."</td>
<td id='' 			style='width:10%' >".$Fecha." ".$hora."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id=''			 style='width:10%'>".$tipopago."</td>
<td id=''		 	style='width:8%'>".$tipo."</td>
<td id='td_datos_4' style='width:7%'>".$plazo."</td>
<td id='td_datos_4' style='width:10%'>".$TipoVenta."</td>
<td id='td_datos_4' style='width:10%'>".$cobradornombre."</td>
<td id='' style='display:none'>".$nombrezona."</td>

<td id='td_datos_10' style='display:none'>".$cod_venta."</td>

<td id='td_datos_6' style='display:none'>".$comision."</td>
<td id='td_datos_7' style='display:none'>".$lot."</td>
<td id='td_datos_8' style='display:none'>".$lat."</td>
</tr>
</table>";

}else{
	$styleName=CargarStyleTable($styleName);
	$paginaentrega.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatospagos(this)' >
<td id='td_datos_1' style='display:none' >".$idPago."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td id='td_datos_3' style='display:none'>".$num_factura."</td>
<td id='td_datos_9' style='width:15%'>".$nombrecliente."</td>
<td style='width:15%'>".$documento."</td>
<td id='td_datos_2' style='display:none' >".$Fecha."</td>
<td id='' style='width:10%' >".$Fecha." ".$hora."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id=''			 style='width:10%'>".$tipopago."</td>
<td id=''		 	style='width:8%'>".$tipo."</td>
<td id='td_datos_4' style='width:5%'>".$plazo."</td>
<td id='td_datos_4' style='width:10%'>".$TipoVenta."</td>
<td id='td_datos_4' style='width:10%'>".$cobradornombre."</td>
<td id='' style='display:none'>".$nombrezona."</td>

<td id='td_datos_10' style='display:none'>".$cod_venta."</td>

<td id='td_datos_6' style='display:none'>".$comision."</td>
<td id='td_datos_7' style='display:none'>".$lot."</td>
<td id='td_datos_8' style='display:none'>".$lat."</td>
</tr>
</table>";
}


}
}
if($paginaentrega!="" && $paginacuota!=""){
	$pagina="<p class='ptituloZ'>Cobros de Entregas</p>".$paginaentrega."<p class='ptituloZ'>Cobros de Cuotas</p>".$paginacuota;
}
if($paginaentrega!="" && $paginacuota==""){
	$pagina="<p class='ptituloZ'>Cobros de Entregas</p>".$paginaentrega;
}
if($paginaentrega=="" && $paginacuota!=""){
	$pagina="<p class='ptituloZ'>Cobros de Cuotas</p>".$paginacuota;
}
   
return array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"4"=>$nroRegistro
,"5"=>number_format($totalPagadoEfectivo,'0',',','.'),"6"=>number_format($totalPagadoTarjeta,'0',',','.'), "7" => $registros);
}

function reeimpresionrecibo($fecha1,$fecha2,$local,$factura,$cliente,$fechafiltro,$cobrador,$metodo)
{

$mysqli=conectar_al_servidor();

 $totalRegistro=0;
	 $pagina="";
	  $condicionfecha="";
	 if($fecha1!="" && $fecha2!=""){
		 $condicionfecha=" and pg.Fecha between'".$fecha1."' and '".$fecha2."'";
	 }
	 $condicionfechafiltro="";
	 if($fechafiltro!=""){
	   $condicionfechafiltro=" and pg.Fecha='".$fechafiltro."'";		
	 }
	 $condicionfactura="";
	 if($factura!=""){
	   $condicionfactura=" and vt.num_factura like '%".$factura."%'";		
	 }
	 $condicionmetodo="";
	 if($metodo!=""){
	   $condicionmetodo=" and pg.tipopago = '".$metodo."'";		
	 }
	 $condicionlocal="";
	 if($local!=""){
	   $condicionlocal=" and (Select l.cod_local from local l  where l.cod_local= vt.cod_local limit 1)='".$local."'";		
	 }
	 $condicioncliente="";
	 if($cliente!=""){
	   $condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK limit 1) like '%".$cliente."%'";		
	 }
	 $condicioncobrador="";
	 if($cobrador!=""){
	   $condicioncobrador=" and (Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK limit 1) like '%".$cobrador."%'";		
	 }
	



	
			$sql= "select  vt.puntoexpedicion,vt.tipo_comprobante,pg.idPago,pg.tipo, pg.Fecha, sum(pg.Monto) as Monto,pg.cod_venta_fk,pg.tipopago,
			pg.comision,pg.nrofactura,pg.lot, pg.lat,(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as nombrecliente,
			(Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK) as cobradornombre,date_format(hora ,'%H:%i' ) as hora,
			(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
			(Select plazo from credito l where l.idcredito=pg.cod_creditoFK) as plazo,
			IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
			vt.num_factura,
			(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=pg.cod_venta_fk)) as nombrezona
			from  pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk  
			where pg.Monto>0 ".$condicionmetodo.$condicionfecha.$condicionfechafiltro.$condicionfactura.$condicionlocal.$condicioncliente.$condicioncobrador." group by pg.nrofactura,pg.Fecha limit 800";/*Sentencia para buscar registros*/	
	


 $pagina = "";   
 $paginaentrega = "";   
 $paginacuota = "";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$totalPagadoEfectivo=0;
$totalPagadoTarjeta=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$cod_venta = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');      
$nombrezona = mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1');      
$hora = mb_convert_encoding((string)($valor['hora']), 'UTF-8', 'ISO-8859-1');      
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1');      
$lot = mb_convert_encoding((string)($valor['lot']), 'UTF-8', 'ISO-8859-1');      
$lat = mb_convert_encoding((string)($valor['lat']), 'UTF-8', 'ISO-8859-1');      
$nombrecliente = mb_convert_encoding((string)($valor['nombrecliente']), 'UTF-8', 'ISO-8859-1');      
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');      
$nrofactura = mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1');      
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$tipo_comprobante = mb_convert_encoding((string)($valor['tipo_comprobante']), 'UTF-8', 'ISO-8859-1');      
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
$tipo_comprobante=mb_convert_encoding((string)($valor['tipo_comprobante']), 'UTF-8', 'ISO-8859-1');
$tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
$nroCancelado=mb_convert_encoding((string)($valor['nroCancelado']), 'UTF-8', 'ISO-8859-1');
$tipopago=mb_convert_encoding((string)($valor['tipopago']), 'UTF-8', 'ISO-8859-1');
if($tipopago=="Efectivo"){
	$totalPagadoEfectivo=$totalPagadoEfectivo+$Monto;
}else{
	$totalPagadoTarjeta=$totalPagadoTarjeta+$Monto;
}

$style='';
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}   
if($nroCancelado==0){
$totalPagado=$Monto+$totalPagado;
}else{
	$style='background-color: #FFEB3B;color:#000';
}

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'onclick='obtenerdatospagos(this)' style='$style'  >
<td id='td_datos_1' style='display:none' >".$idPago."</td>
<td id='td_datos_3' style='display:none'>".$num_factura."</td>
<td id='td_datos_9' style='width:15%'>".$nombrecliente."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td id='td_datos_2' style='display:none' >".$Fecha."</td>
<td id='' style='width:10%' >".$Fecha." ".$hora."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:7%'>".$tipopago."</td>
<td id='' style='display:none'>".$tipo."</td>
<td id='td_datos_4' style='display:none'>".$plazo."</td>
<td id='td_datos_4' style='width:10%'>".$cobradornombre."</td>
<td id='' style='display:none'>".$nombrezona."</td>

<td id='td_datos_10' style='display:none'>".$cod_venta."</td>

<td id='td_datos_6' style='display:none'>".$comision."</td>
<td id='td_datos_7' style='display:none'>".$lot."</td>
<td id='td_datos_8' style='display:none'>".$lat."</td>
</tr>
</table>";



}
}

$informacion =array("1" => "exito","2" => $pagina,"4"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function comisioncobrador($fecha1,$fecha2,$zona,$fechafiltro,$cobrado)
{
$mysqli=conectar_al_servidor();

	
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=(Select cod_clienteFK From venta vt where vt.cod_venta=cod_venta_fk)  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
    $condicionfecha="";
	if($fecha1!="" && $fecha2!=""){
		 $condicionfecha="and  Fecha>='$fecha1' and Fecha<='$fecha2'";
	}
	$condicioncobrador="";
	if($cobrado!=""){
		$condicioncobrador="and (Select nombre_persona from persona where cod_persona=cod_cobradorFK) like '%".$cobrado."%'";
	}
	$condicionfechafiltro="";
	if($fechafiltro!=""){
		$condicionfechafiltro="and Fecha='$fechafiltro'";
	}
	
$sql= "select idPago,Fecha,Monto,cod_venta_fk,comision,
(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
(Select num_factura From venta vt where vt.cod_venta=cod_venta_fk) as num_factura,
(Select puntoexpedicion From venta vt where vt.cod_venta=cod_venta_fk) as puntoexpedicion,
(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cod_venta_fk)) as nombrezona
 from pago where cod_cobradorFK!='01'  ".$condicionfechafiltro.$condicioncobrador.$condicionfecha.$condicionZona." limit 100";	
	



 $pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$totalcomisiones=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$cod_venta = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');      
$nombrezona = mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1');      
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}   
$totalPagado=$Monto+$totalPagado;
 	

$totalcomision=($comision*$Monto)/100;     
$totalPagado=$Monto+$totalPagado;
 $totalcomisiones=$totalcomisiones+$totalcomision;

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatoscomisioncobrador(this)'  >
<td id='td_id_1' style='display:none'>".$idPago."</td>
<td id='' style='width:10%'>".$cobradornombre."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td id='td_datos_1' style='display:none'>".$num_factura."</td>
<td id='td_datos_2' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_3' style='width:10%' >".$Fecha."</td>
<td id='td_datos_4' style='width:10%'>".$nombrezona."</td>
<td id='td_datos_5' style='width:10%'>".$comision."</td>
<td id='td_datos_6' style='width:10%'>". number_format($totalcomision,'0',',','.') ."</td>
</tr>
</table>";


}
}

$sql= "select idPago,Fecha,Monto,cod_venta_fk,comision,
(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
(Select num_factura From venta vt where vt.cod_venta=cod_venta_fk) as num_factura,
(Select puntoexpedicion From venta vt where vt.cod_venta=cod_venta_fk) as puntoexpedicion,
(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cod_venta_fk)) as nombrezona
 from pago where cod_cobradorFK!='01'  ".$condicionfechafiltro.$condicioncobrador.$condicionfecha.$condicionZona;	
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"5" =>number_format($totalcomisiones,'0',',','.'),"4"=>$nroRegistro,"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}


function mascomisioncobrador($fecha1,$fecha2,$zona,$fechafiltro,$cobrado,$totalrecaudacion,$totalcomision,$registrocargado)
{
$mysqli=conectar_al_servidor();

	
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=(Select cod_clienteFK From venta vt where vt.cod_venta=cod_venta_fk)  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
    $condicionfecha="";
	if($fecha1!="" && $fecha2!=""){
		 $condicionfecha="and  Fecha>='$fecha1' and Fecha<='$fecha2'";
	}
	$condicioncobrador="";
	if($cobrado!=""){
		$condicioncobrador="and (Select nombre_persona from persona where cod_persona=cod_cobradorFK) like '%".$cobrado."%'";
	}
	$condicionfechafiltro="";
	if($fechafiltro!=""){
		$condicionfechafiltro="and Fecha='$fechafiltro'";
	}
	
$sql= "select idPago,Fecha,Monto,cod_venta_fk,comision,
(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
(Select num_factura From venta vt where vt.cod_venta=cod_venta_fk) as num_factura,
(Select puntoexpedicion From venta vt where vt.cod_venta=cod_venta_fk) as puntoexpedicion,
(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cod_venta_fk)) as nombrezona
 from pago where cod_cobradorFK!='01'  ".$condicionfechafiltro.$condicioncobrador.$condicionfecha.$condicionZona." limit ".$registrocargado.", 100 ";	
	



 $pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=$totalrecaudacion;
$totalcomisiones=$totalcomision;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$cod_venta = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');      
$nombrezona = mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1');      
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}   
$totalPagado=$Monto+$totalPagado;
 	

$totalcomision=($comision*$Monto)/100;     
$totalPagado=$Monto+$totalPagado;
 $totalcomisiones=$totalcomisiones+$totalcomision;

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatoscomisioncobrador(this)'  >
<td id='td_id_1' style='display:none'>".$idPago."</td>
<td id='' style='width:10%'>".$cobradornombre."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td id='td_datos_1' style='display:none'>".$num_factura."</td>
<td id='td_datos_2' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_3' style='width:10%' >".$Fecha."</td>
<td id='td_datos_4' style='width:10%'>".$nombrezona."</td>
<td id='td_datos_5' style='width:10%'>".$comision."</td>
<td id='td_datos_6' style='width:10%'>". number_format($totalcomision,'0',',','.') ."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"5" =>number_format($totalcomisiones,'0',',','.'),"4"=>$nroRegistro,"99"=>$nroRegistro  );
echo json_encode($informacion);	
exit;
}


function vistacajaapp($fecha1,$fecha2,$codlocal,$cobrado)
{
$mysqli=conectar_al_servidor();

	
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=(Select cod_clienteFK From venta vt where vt.cod_venta=cod_venta_fk)  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
    $condicionfecha="";
	if($fecha1!="" && $fecha2!=""){
		 $condicionfecha="and  Fecha>='$fecha1' and Fecha<='$fecha2'";
	}
	$condicioncobrador="";
	if($cobrado!=""){
		$condicioncobrador="and (Select nombre_persona from persona where cod_persona=cod_cobradorFK) like '%".$cobrado."%'";
	}
	$condicionfechafiltro="";
	if($fechafiltro!=""){
		$condicionfechafiltro="and Fecha='$fechafiltro'";
	}
	
$sql= "select idPago,Fecha,Monto,cod_venta_fk,comision,
(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
(Select num_factura From venta vt where vt.cod_venta=cod_venta_fk) as num_factura,
(Select puntoexpedicion From venta vt where vt.cod_venta=cod_venta_fk) as puntoexpedicion,
(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cod_venta_fk)) as nombrezona
 from pago where cod_cobradorFK!='01'  ".$condicionfechafiltro.$condicioncobrador.$condicionfecha.$condicionZona." limit 1000";	
	



 $pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$totalcomisiones=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idPago = mb_convert_encoding((string)($valor['idPago']), 'UTF-8', 'ISO-8859-1');    
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');      
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');      
$cobradornombre = mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');      
$cod_venta = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');      
$nombrezona = mb_convert_encoding((string)($valor['nombrezona']), 'UTF-8', 'ISO-8859-1');      
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}   
$totalPagado=$Monto+$totalPagado;
 	

$totalcomision=($comision*$Monto)/100;     
$totalPagado=$Monto+$totalPagado;
 $totalcomisiones=$totalcomisiones+$totalcomision;

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatoscomisioncobrador(this)'  >
<td id='td_id_1' style='display:none'>".$idPago."</td>
<td id='' style='width:10%'>".$cobradornombre."</td>
<td id='' style='width:10%'>".$nrof."</td>
<td id='td_datos_1' style='display:none'>".$num_factura."</td>
<td id='td_datos_2' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_3' style='width:10%' >".$Fecha."</td>
<td id='td_datos_4' style='width:10%'>".$nombrezona."</td>
<td id='td_datos_5' style='width:10%'>".$comision."</td>
<td id='td_datos_6' style='width:10%'>". number_format($totalcomision,'0',',','.') ."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"5" =>number_format($totalcomisiones,'0',',','.'),"4"=>$nroRegistro  );
echo json_encode($informacion);	
exit;
}



function addMasCuotas($cod_venta,$totalPago){
	
	$datosVenta=buscardatosventa($cod_venta);
	
	if($totalPago<$datosVenta[1]){
		
	$pendiente=$datosVenta[1]-$totalPago;
	
	
	$fechaInicio=date("Y-m-d");
	$controlPago=0;
	
	while($controlPago==0)
	{
		
	
	
	if($datosVenta[7]=="Mensual")	{
		  $F=$F+1;
			$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
			
		}
		if($datosVenta[7]=="Semanal")	{
			$F=$F+7;
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			
		}
		if($datosVenta[7]=="Quincenal")	{
				$F=$F+15;
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
		
		}
	
	
	if($pendiente>$datosVenta[17]){
		$cuotaSobrante=$pendiente-$datosVenta[17];
	$cuotaSobrante=$pendiente-$cuotaSobrante;
	}else{
	$cuotaSobrante=$pendiente;
	$pendiente=0;
	}
	if($controlPago==0){
		insertarcuotas(($datosVenta[16]+1)."/".($datosVenta[16]+1),$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ");
	}else{
		
	}
	
	$pendiente=$pendiente-$cuotaSobrante;
	if($pendiente<=0){
		$controlPago=1;
	}
	
	 
	
	 }
				 
	}
}

function insertarcuotas($plazo, $fechapago, $cod_venta, $Monto, $Esado,$Nro_recibo){
		$mysqli=conectar_al_servidor();
			$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo)
			values('$plazo','$fechapago','$cod_venta','$Monto','$Esado','$Nro_recibo')";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
}


function buscardatosventa($codVenta){
	$mysqli=conectar_al_servidor();
	 
		$sql= "Select fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,puntoexpedicion,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,
		(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto
		from venta vt where cod_venta=?  ";
		
		     $datosVenta;
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
//$buscar="".$buscar."";
$stmt->bind_param($s,$codVenta);

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
		  
		  
		      $datosVenta[0]=$valor['fecha_venta'];
		  	  $datosVenta[1]=mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[2]=mb_convert_encoding((string)($valor['cod_usuarioFK']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[3]=mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[4]=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[5]=mb_convert_encoding((string)($valor['cod_cobradorFK']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[6]=mb_convert_encoding((string)($valor['TipoVenta']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[7]=mb_convert_encoding((string)($valor['TipoPago']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[8]=mb_convert_encoding((string)($valor['Vendedor1']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[9]=mb_convert_encoding((string)($valor['Vendedor2']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[10]=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[11]=mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[12]=mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[13]=mb_convert_encoding((string)($valor['cobradornombre']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[14]=mb_convert_encoding((string)($valor['nombrevendedor1']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[15]=mb_convert_encoding((string)($valor['nombrevendedor2']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[16]=mb_convert_encoding((string)($valor['cantidadcuota']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[17]=mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[18]=mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1');
		  	  $datosVenta[19]=mb_convert_encoding((string)($valor['nrodocliente']), 'UTF-8', 'ISO-8859-1');
		  	  $puntoexpedicion=mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
		  	  $num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
		
		  	  if($puntoexpedicion!=""){
				  $nrof=$puntoexpedicion."-".$num_factura;
				  }else{
				  $nrof=$num_factura;
			  }
		  	 	   $datosVenta[20]=$nrof;	
		
			  
			  
	  }
 }
 
 
return $datosVenta;
}



/*Buscar */
function buscardatospagosTipo($buscar,$Condicon)
{
$mysqli=conectar_al_servidor();

if($Condicon=="1"){
	$Condicon=" and pg.Tipo='Interes'";
}

if($Condicon=="2"){
	$Condicon=" and pg.Tipo='Pago Cuota'";
}
if($Condicon=="3"){
	$Condicon=" ";
}

$sql= "select pg.Tipo, pg.cod_creditoFK,pg.cod_venta_fk ,sum(pg.Monto) as Monto
 from  pago pg 
 where pg.cod_creditoFK='$buscar' ".$Condicon;


 $datos= "0";   

$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

 $datos= mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');    
 
}
}
return $datos;
}



/*Buscar */
function buscardatosCreditoDeuda($buscar)
{
$mysqli=conectar_al_servidor();



$sql= "select cr.deudaInteres  from  credito cr  where cr.idcredito='$buscar' ";


 $datos= 0;   

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

 $datos= mb_convert_encoding((string)($valor['deudaInteres']), 'UTF-8', 'ISO-8859-1');    
 
}
}
return $datos;
}




/*Buscar */
function buscardatospagos($buscar,$condicion)
{
$mysqli=conectar_al_servidor();
if($condicion=="1"){
	$condicion=" pg.idPago='$buscar'";
}
if($condicion=="2"){
	$condicion=" pg.cod_creditoFK='$buscar'";
}
	
$sql= "select pg.Tipo, pg.cod_creditoFK,pg.cod_venta_fk ,pg.Monto, vt.num_factura
 from  pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk 
 where ".$condicion;


 $datos[0] = "0";   
 $datos[1] = "0";   
 $datos[2] = "0";  
 $datos[3] = "0"; 
 $datos[4] = "0";  
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



 $datos[0]= mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1');    
 $datos[1]= mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');    
 $datos[2]= mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');    
 $datos[3]= mb_convert_encoding((string)($valor['cod_creditoFK']), 'UTF-8', 'ISO-8859-1');    
 $datos[4]= mb_convert_encoding((string)($valor['Tipo']), 'UTF-8', 'ISO-8859-1');    



}
}
return $datos;
}



function buscardatosdelcredito($codcredito)
{
$mysqli=conectar_al_servidor();

$sql= "select cr.Monto,cr.descuento,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0) as totalPagoCredito,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres
 from  credito cr  where cr.idcredito='$codcredito' ";
 

$Monto=0;
$descuento=0;
$totalPago=0;
$totalPagoCredito=0;
$totalPagoInteres=0;

$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');     
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1');  
$totalPago = mb_convert_encoding((string)($valor['totalPago']), 'UTF-8', 'ISO-8859-1');  
$totalPagoCredito = mb_convert_encoding((string)($valor['totalPagoCredito']), 'UTF-8', 'ISO-8859-1');  
$totalPagoInteres = mb_convert_encoding((string)($valor['totalPagoInteres']), 'UTF-8', 'ISO-8859-1');  


}

}

$datos[0]=$Monto;
$datos[1]=$descuento;
$datos[2]=$totalPago;
$datos[3]=$totalPagoCredito;
$datos[4]=$totalPagoInteres;


 mysqli_close($mysqli);
return $datos;
}

/*Buscar */
function editarDetallesVenta($buscar,$observacion)
{
$mysqli=conectar_al_servidor();

$sql= "select dtv.cod_detalle,dtv.detalleproducto
 from venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
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



$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'ISO-8859-1', 'UTF-8'); 
if($observacion!=""){
	if($valor['detalleproducto']!=""){
	$detalleproducto = $observacion." *".mb_convert_encoding((string)($valor['detalleproducto']), 'ISO-8859-1', 'UTF-8');
	}else{
	$detalleproducto = $observacion;
	}
}else{
	$detalleproducto = " *".mb_convert_encoding((string)($valor['detalleproducto']), 'ISO-8859-1', 'UTF-8');
}
     
editardetallesventacredito($detalleproducto,$cod_detalle);

}
}
 mysqli_close($mysqli);
return mb_convert_encoding((string)($pagina), 'ISO-8859-1', 'UTF-8');
}

function editardetallesventacredito($detalleproducto,$cod_detalle)
{
	
$mysqli=conectar_al_servidor(); 
$consulta1="Update detalle_venta set detalleproducto='$detalleproducto' where cod_detalle='$cod_detalle'  ";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	
}


function addPagos($cod_venta,$cajapredeterminada,$codApertura){
	$control=1;	
$totalregistro=$_POST['totalregistro'];
$totalregistro = mb_convert_encoding((string)($totalregistro), 'ISO-8859-1', 'UTF-8');

$controlTipoPago = $totalregistro;

$montotarjerta = 0;
$descuento = 0;


$mysqli=conectar_al_servidor();

while($control<=$totalregistro){

$idtipopago=$_POST['idtipopago'.$control];
$idtipopago = mb_convert_encoding((string)($idtipopago), 'ISO-8859-1', 'UTF-8');

$monto=$_POST['monto'.$control];
$monto = quitarseparadormiles($monto);


$control=$control+1;
$controlTipoPago = $controlTipoPago - 1;


abmcontado($cod_venta,$descuento,$monto,$montotarjerta,$cajapredeterminada,$codApertura,$idtipopago,$controlTipoPago,$totalregistro);
} 


}

function addPagosCredito($CargoAdministrativo,$cajapredeterminada,$codApertura,$cod_creditoFK,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$MontoTarjeta,$descuento,$nrofactura,$operacion){

$control=1;	
$totalregistro=$_POST['totalregistro'];
$totalregistro = mb_convert_encoding((string)($totalregistro), 'ISO-8859-1', 'UTF-8');
$controlTipoPago = $totalregistro;

$ControlMonto=0;

$controlMontoCiclo=1;

while($controlMontoCiclo<=$totalregistro){ 

$monto=$_POST['monto'.$control];
$monto = quitarseparadormiles($monto);

			if($monto>$ControlMonto){
				$ControlMonto = $monto;
			}
$controlMontoCiclo=$controlMontoCiclo+1;
}


$mysqli=conectar_al_servidor();

$ControlRestaMonto=0;

while($control<=$totalregistro){

$idtipopago=$_POST['idtipopago'.$control];
$idtipopago = mb_convert_encoding((string)($idtipopago), 'ISO-8859-1', 'UTF-8');

$monto=$_POST['monto'.$control];
$monto = quitarseparadormiles($monto);

$ControlGA=$CargoAdministrativo;

$valor=$_POST['valor'.$control];
$valor = mb_convert_encoding((string)($valor), 'ISO-8859-1', 'UTF-8');
	   
	   if($valor=="SI"){
		
		$monto=$_POST['monto'.$control];
		$monto = quitarseparadormiles($monto);
		
		$MotivoDeposito=$_POST['MotivoDeposito'.$control];
		$MotivoDeposito = mb_convert_encoding((string)($MotivoDeposito), 'ISO-8859-1', 'UTF-8');

		$nroCuentaDeposito=$_POST['nroCuentaDeposito'.$control];
		$nroCuentaDeposito = mb_convert_encoding((string)($nroCuentaDeposito), 'ISO-8859-1', 'UTF-8');

		$BancoDeposito=$_POST['BancoDeposito'.$control];
		$BancoDeposito = mb_convert_encoding((string)($BancoDeposito), 'ISO-8859-1', 'UTF-8');

		$NroBoletaDeposito=$_POST['NroBoletaDeposito'.$control];
		$NroBoletaDeposito = mb_convert_encoding((string)($NroBoletaDeposito), 'ISO-8859-1', 'UTF-8');
		
		$codApertura=$_POST['codApertura'];
		$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');
		
		$codcaja=$_POST['codcaja'];
		$codcaja = mb_convert_encoding((string)($codcaja), 'ISO-8859-1', 'UTF-8');
		
		$cod_local=$_POST['cod_local'];
		$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');

		InsertarPagoEgreso($monto,$MotivoDeposito,$cod_local,$codcaja,$codApertura,$NroBoletaDeposito,$BancoDeposito,$nroCuentaDeposito);
	   }


$control=$control+1;
$controlTipoPago = $controlTipoPago - 1;


if($ControlMonto==$monto && $ControlRestaMonto==0 ){
	
	$monto = $monto - $ControlGA ;	
	$CargoAdministrativo = $ControlGA;	
	$ControlRestaMonto =1;	
}else{
	$CargoAdministrativo = "0";
}


abm($CargoAdministrativo,$cajapredeterminada,$codApertura,$cod_creditoFK,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$monto,$MontoTarjeta,$descuento,$nrofactura,$operacion,$idtipopago,$controlTipoPago);
} 
}



function addPagosCreditoParcial($CargoAdministrativo,$MontoTarjeta,$MontDescuento,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$nrofactura,$codCaja,$codApertura,$totalregistro){

if($nrofactura==""){
	$nrofactura=buscarnrofactura();
}

$control=1;	
$controlTipoPago = $totalregistro;

$mysqli=conectar_al_servidor();

$ControlMonto=0;

$controlMontoCiclo=1;

/* while($controlMontoCiclo<=$totalregistro){ 

$monto=$_POST['monto'.$control];
$monto = quitarseparadormiles($monto);

			if($monto>$ControlMonto){
				$ControlMonto = $monto;
			}
$controlMontoCiclo=$controlMontoCiclo+1;
} */


$ControlRestaMonto=0;

$ControlGA=$CargoAdministrativo;

while($control<=$totalregistro){

$idtipopago=$_POST['idtipopago'.$control];
$idtipopago = mb_convert_encoding((string)($idtipopago), 'ISO-8859-1', 'UTF-8');

$monto=$_POST['monto'.$control];
$monto = quitarseparadormiles($monto);


$valor=$_POST['valor'.$control];
$valor = mb_convert_encoding((string)($valor), 'ISO-8859-1', 'UTF-8');
	   
	   if($valor=="SI"){
		
		$monto=$_POST['monto'.$control];
		$monto = quitarseparadormiles($monto);
		
		$MotivoDeposito=$_POST['MotivoDeposito'.$control];
		$MotivoDeposito = mb_convert_encoding((string)($MotivoDeposito), 'ISO-8859-1', 'UTF-8');

		$nroCuentaDeposito=$_POST['nroCuentaDeposito'.$control];
		$nroCuentaDeposito = mb_convert_encoding((string)($nroCuentaDeposito), 'ISO-8859-1', 'UTF-8');

		$BancoDeposito=$_POST['BancoDeposito'.$control];
		$BancoDeposito = mb_convert_encoding((string)($BancoDeposito), 'ISO-8859-1', 'UTF-8');

		$NroBoletaDeposito=$_POST['NroBoletaDeposito'.$control];
		$NroBoletaDeposito = mb_convert_encoding((string)($NroBoletaDeposito), 'ISO-8859-1', 'UTF-8');
		
		$codApertura=$_POST['codApertura'];
		$codApertura = mb_convert_encoding((string)($codApertura), 'ISO-8859-1', 'UTF-8');
		
		$codcaja=$_POST['codcaja'];
		$codcaja = mb_convert_encoding((string)($codcaja), 'ISO-8859-1', 'UTF-8');
		
		$cod_local=$_POST['cod_local'];
		$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');

		InsertarPagoEgreso($monto,$MotivoDeposito,$cod_local,$codcaja,$codApertura,$NroBoletaDeposito,$BancoDeposito,$nroCuentaDeposito);
	   }



$control=$control+1;
$controlTipoPago = $controlTipoPago - 1;


/* if($ControlMonto==$monto && $ControlRestaMonto==0 ){
	
	$monto = $monto  ;	
	$CargoAdministrativo = $ControlGA;	
	$ControlRestaMonto =1;	
}else{
	$CargoAdministrativo = "0";
} */




cargarpagos($CargoAdministrativo,$monto,$MontoTarjeta,$MontDescuento,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$nrofactura,$codCaja,$codApertura,$idtipopago,$controlTipoPago);




} 


}




function InsertarPagoEgreso($monto,$motivo,$cod_local,$codcaja,$idaperturacierrecaja,$nroboleta,$banco,$nrocuenta)
{
	
$mysqli=conectar_al_servidor(); 

date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$user=$_POST['useru'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

$consulta1="Insert into gastos (arreglo,monto,motivo,fecha,estado,cod_usuario,personales,cod_local,tipo,codCaja,codApertura,nroboleta,banco,nrocuenta)
values('INTERNO',?,?,'$fecha_inser_edit','Activo','$user','',?,'Egreso',?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssss';
$stmt1->bind_param($ss,$monto,$motivo,$cod_local,$codcaja,$idaperturacierrecaja,$nroboleta,$banco,$nrocuenta);


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	
}





function buscarDetalleVentaImprimir($CodVenta)
{
$mysqli=conectar_al_servidor();


$sql= "select  cantidad_detalle , precio_producto,subtotal , puntoexpedicion , num_factura,
(select nombre_producto from producto where cod_producto=cod_productoFK) as NombreProducto
from detalle_venta inner join venta on cod_venta=cod_ventaFK
 where cod_ventaFK='$CodVenta'  ";
 
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
$totalPagado=0;
$datos[0]="";
$datos[1]="";

$nroFactura = "";   
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
     
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1');      
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1');  
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1');  
$NombreProducto = mb_convert_encoding((string)($valor['NombreProducto']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');  


if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}


$pagina.="<table class='tableTicket' style='border: solid 1px #a1a1a1;' >
<tr>
<td style='width:10%'>".$cantidad_detalle."</td>
<td style='width:50%'>".$NombreProducto."</td>
<td style='width:15%'>".number_format($precio_producto,'0',',','.')."</td>
<td style='width:25%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";

}
}
$datos[0]=$pagina;
$datos[1]=$totalPagado;
$datos[2]=$nroFactura;
return $datos;	

}




function buscarpagosTitulo($CodVenta,$NroFactura)
{
$mysqli=conectar_al_servidor();


$sql= "select cr.fechapago,cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,vt.TipoVenta,vt.total_venta,
(SELECT nombre FROM tipopago where cod_tipoPago = pg.cod_tipoPagoFK) as tipopg
 from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk
 inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where pg.cod_venta_fk='$CodVenta' and pg.nrofactura='$NroFactura' order by pg.idPago  ";
 
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
     
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');  
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');  
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1'); 
$fechapago = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');  
$tipopg = mb_convert_encoding((string)($valor['tipopg']), 'UTF-8', 'ISO-8859-1');  

if($plazo=="Contado"){
	$tipo="";
}



if($tipo=="Interes"){
	$tipo="INTERES"."--".$plazo;
}
if($tipo=="Pago Cuota"){
	$tipo="PAGO DE CUOTA"."--".$plazo;
}

if($tipo=="CARGO ADMINISTRATIVO"){
	$tipo="CARGO ADMINISTRATIVO";
	$fechapago="";
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
<td style='width:30%'>".$tipo."</td>
<td style='width:10%'>".$tipopg."</td>
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




/*Buscar */
function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,dtv.detalleproducto,
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


$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');      
$nroDevoluciones = mb_convert_encoding((string)($valor['nroDevoluciones']), 'ISO-8859-1', 'UTF-8');      
$nroCambios = mb_convert_encoding((string)($valor['nroCambios']), 'ISO-8859-1', 'UTF-8');      
$nroGarantia = mb_convert_encoding((string)($valor['nroGarantia']), 'ISO-8859-1', 'UTF-8');      
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'ISO-8859-1', 'UTF-8');      
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'ISO-8859-1', 'UTF-8');      
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'ISO-8859-1', 'UTF-8');      
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'ISO-8859-1', 'UTF-8');      
if($nroDevoluciones==0 && $nroCambios==0){
 $pagina.="<table class='tableTicket' style='border: solid 1px #a1a1a1;' >
<tr>
<td style='width:10%'>".$cantidad_detalle."</td>
<td style='width:50%'>".$nombre_producto."</td>
<td style='width:15%'>".number_format($precio_producto,'0',',','.')."</td>
<td style='width:25%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";
}

}
}

return $pagina;
}


function buscarpagosTituloContado($CodVenta)
{
$mysqli=conectar_al_servidor();


$sql= "select cr.fechapago,cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,vt.TipoVenta,vt.total_venta
,vt.fecha_venta,
(SELECT nombre FROM tipopago where cod_tipoPago = pg.cod_tipoPagoFK) as tipopg
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
$total_venta=0;
$fecha_venta="";
$nrofactura="";
$datos[0]="";
$datos[1]="";
$datos[2]="";
$datos[3]="";
$datos[4]="";
$datos[5]="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
     
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');  
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');  
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1'); 
$fechapago = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');  
$total_venta = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1');  
$fecha_venta = mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1');  
$nrofactura = mb_convert_encoding((string)($valor['nrofactura']), 'UTF-8', 'ISO-8859-1');  
$tipopg = mb_convert_encoding((string)($valor['tipopg']), 'UTF-8', 'ISO-8859-1');  


if($tipo=="Interes"){
	$tipo="INTERES"."--".$plazo;
}
if($tipo=="Pago Cuota"){
	$tipo="PAGO DE CUOTA"."--".$plazo;
}

if($tipo=="CARGO ADMINISTRATIVO"){
	$tipo="CARGO ADMINISTRATIVO";
	$fechapago="";
}

if($plazo=="Contado"){
	$tipo="CONTADO";
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
<td style='width:30%'>CONTADO</td>
<td style='width:10%'>".$tipopg."</td>
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
$datos[3]=$total_venta;
$datos[4]=$fecha_venta;
$datos[5]=$nrofactura;
return $datos;	

}



function buscarpagosTituloCreditoDirecto($CodVenta,$NroFactura,$cod_creditoFK)
{
$mysqli=conectar_al_servidor();


$sql= "select cr.fechapago,cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,vt.TipoVenta,vt.total_venta
 from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk
 inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where pg.cod_venta_fk='$CodVenta'  and pg.cod_creditoFK='$cod_creditoFK' and pg.nrofactura='$NroFactura' order by pg.idPago  ";
 
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
     
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');  
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');  
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1'); 
$fechapago = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');  

if($plazo=="Contado"){
	$tipo="";
}


if($tipo=="Interes"){
	$tipo="INTERES"."--".$plazo;
}
if($tipo=="Pago Cuota"){
	$tipo="PAGO DE CUOTA"."--".$plazo;
}

if($tipo=="CARGO ADMINISTRATIVO"){
	$tipo="CARGO ADMINISTRATIVO";
	$fechapago="";
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
<td style='width:40%'>".$tipo."</td>
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

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	$operacion = $_POST['funt'];
	$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
	ObtenerDatos($operacion);
}

?>
<?php


function calcularintereses2($buscar,$fecha1,$fecha2,$filtro1,$filtro2,$filtro3,$actualizar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicion="";

if($filtro1=="1"){
$condicion=" and cr.fechapago>='$fecha1' and cr.fechapago<='$fecha2'";
}
if($filtro1=="3"){
$condicion=" and cr.fechapago<='$fechahoy'";
}
$condicionpago="";
if($filtro2=="1"){
	//condicion para saber si esta pagado
$condicionpago=" and ((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))<=0";
}
if($filtro2=="3"){
$condicionpago=" and ((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))>0";
}
$condicioncodigo="";
if($filtro3=="1"){
$condicioncodigo=" and cr.idcredito='$buscar'";
}
if($filtro3=="2"){
$condicioncodigo=" and vt.cod_venta='$buscar'";
}
	
$sql= "select vt.cod_clienteFK,vt.TipoVenta,vt.puntoexpedicion,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,
cr.Nro_recibo,datediff(cr.fechapago,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1)) as diff,
vt.total_venta,interes,dias,vt.pago as entrega,cr.deudaInteres,
total,(totalinteres + deudaInteres) as totalinteres,totaldeuda,vt.num_factura,cr.descuento,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha desc limit 1),0) as FechaUltimoPago,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito  and Monto!='0' order by pg.Fecha asc limit 1),0) as FechaPagoCredito,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0) as totalPagoCredito,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=cr.cod_venta),0) as totalPagoVenta,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as documentocliente,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1) as fechapagado,
(select count(pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito ) as cantidad
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0
    ".$condicioncodigo.$condicionpago.$condicion." and esado != 'eliminado' and esado != 'inactivo' order by cr.idcredito asc";
	
	//echo "$sql\n\n";exit;

$pagina="";
$totalEnDescuento=0;
$TotalEnInteres=0;
$TotalEnDeuda=0;
$totalInteresActual=0;
$TotalEnPagado=0;
$TotalAPagar=0;
$TotalPagadoSinInteres=0;
$DeudaPendiente=0;
$TotalApagarSinInteres=0;
$TotalDiasAtrasado=0;
$TotalInteresesPagado=0;
$SubTotal=0;
$dias =0;
$nrodecuotasatrazado =0;
$MontoCuotas =0;
$totalPagado = "0";  
$deuda = "0";  
$tinteres = "0,1";  
$stylecolor="";
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
$diff2=0;
$DiasAtrazo="";
$SumadeudaInteres=0;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$FechaUltimoPago = mb_convert_encoding((string)($valor['FechaUltimoPago']), 'UTF-8', 'ISO-8859-1');
$FechaPagoCredito = mb_convert_encoding((string)($valor['FechaPagoCredito']), 'UTF-8', 'ISO-8859-1');  
$deudaInteres = mb_convert_encoding((string)($valor['deudaInteres']), 'UTF-8', 'ISO-8859-1');
$idcredito = mb_convert_encoding((string)($valor['idcredito']), 'UTF-8', 'ISO-8859-1');     
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');  
$fechapago = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');          
$cod_venta = mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1');          
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 
$totalPago = mb_convert_encoding((string)($valor['totalPago']), 'UTF-8', 'ISO-8859-1'); 
$Esado = mb_convert_encoding((string)($valor['Esado']), 'UTF-8', 'ISO-8859-1');          
$Nro_recibo = mb_convert_encoding((string)($valor['Nro_recibo']), 'UTF-8', 'ISO-8859-1');
$diff = mb_convert_encoding((string)($valor['diff']), 'UTF-8', 'ISO-8859-1');
$total_venta = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1');
$interes = mb_convert_encoding((string)($valor['interes']), 'UTF-8', 'ISO-8859-1');
$dias = mb_convert_encoding((string)($valor['dias']), 'UTF-8', 'ISO-8859-1');
$total = mb_convert_encoding((string)($valor['total']), 'UTF-8', 'ISO-8859-1');
$tinteres = mb_convert_encoding((string)($valor['totalinteres']), 'UTF-8', 'ISO-8859-1');
$totaldeuda = mb_convert_encoding((string)($valor['totaldeuda']), 'UTF-8', 'ISO-8859-1');
$entrega = mb_convert_encoding((string)($valor['entrega']), 'UTF-8', 'ISO-8859-1');
$fechapagado = mb_convert_encoding((string)($valor['fechapagado']), 'UTF-8', 'ISO-8859-1');
$cantidad = mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1');
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1');
$nroCancelado = mb_convert_encoding((string)($valor['nroCancelado']), 'UTF-8', 'ISO-8859-1');
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1');
$documentocliente = mb_convert_encoding((string)($valor['documentocliente']), 'UTF-8', 'ISO-8859-1');
$nroCouta = mb_convert_encoding((string)($valor['nroCouta']), 'UTF-8', 'ISO-8859-1');
$TipoVenta = mb_convert_encoding((string)($valor['TipoVenta']), 'UTF-8', 'ISO-8859-1');
$totalPagoVenta = mb_convert_encoding((string)($valor['totalPagoVenta']), 'UTF-8', 'ISO-8859-1');
$totalPagoCredito = mb_convert_encoding((string)($valor['totalPagoCredito']), 'UTF-8', 'ISO-8859-1');//TOTAL PAGADO EN CUOTA
$totalPagoInteres = mb_convert_encoding((string)($valor['totalPagoInteres']), 'UTF-8', 'ISO-8859-1');//TOTAL PAGADO EN INTERESES
$cod_clienteFK = mb_convert_encoding((string)($valor['cod_clienteFK']), 'UTF-8', 'ISO-8859-1');

$SumadeudaInteres= $SumadeudaInteres +$deudaInteres;



//CALCULAR EL MONTO CON DESCUENTO
$MontoConDescuento=$Monto-$descuento;
/*CALCULAMOS EL MONTO CON DESCUENTO*/
$MontoSobrante=$MontoConDescuento-$totalPago;
if($MontoCuotas==0){
$MontoCuotas=$MontoConDescuento-$totalPago;
}
/*VACIAMOS ALGUNAS VARIABLES*/
$deudaActua=0;
$total_interes=0;
$stylecolor=" ";
//CONDICION PARA SABER SI EL CREDITO ES UNA VENTA CANCELADA
if($nroCancelado==0){
	//CONDICION PARA SABER SI YA SE PAGO TODO
	if(($Monto+$totalPagoInteres)>($totalPago+$descuento)){
			//ESTADO DEL PAGO
	$Esado="Pendiente";
	//CONDICION PARA SABER SI HAY DIAS ATRAZADOS
	if($diff<0 && $diff!=""){
	$diff=$diff*-1;
	editarDiasAtrazadosdesdecalcularcredito($cod_clienteFK,$diff,$mysqli);
	actualizardiasatrazadocredito($idcredito,$diff,$mysqli);
	}else{
	$diff=0;
    }
	
	$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
	$TotalApagarSinInteres=$TotalApagarSinInteres+($MontoConDescuento-($totalPagoCredito));
	//CALCULAMOS EL NRO DE CUOTAS ATRAZADAS
	$nrodecuotasatrazado=$nrodecuotasatrazado+1;
	//CONDICION PARA SABER SI HAY INTERESES EN %
	if($interes!=0){
		/*CALCULAMOS EL DIA DE GRACIA*/
	$fechahoy=date('Y-m-d');	
	$datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy)))); 
	$datetime3= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));	
	$Fecha1=strtotime($FechaUltimoPago);
	$Fecha2=strtotime($fechapago);
	if($FechaPagoCredito=="0" ){
		$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));	
	}else{
		if($Fecha1 < $Fecha2){
				$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));		
			}else{
				$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$FechaUltimoPago))));		
			}		
	}
	$interval=$datetime2->diff($datetime1);
    $diff=$interval->format('%a');
	
	
	
	$interval2=$datetime3->diff($datetime1);
    $diff2=$interval2->format('%a');

	
	$diasGracia=$diff2-$dias;
	if($diasGracia>0){
		//CALCULAMOS EL MONTO SOBRANTE
	$montoIn=$MontoConDescuento-$totalPagoCredito;	
	/*CALCULAMOS EL INTERES*/
	$i=($interes*($Monto - $totalPagoCredito))/100;//                                                       aca modifique para que me salga bien el interes
	$total_interes=($i*$diff);
	//CALCUMOS EL TOTAL A PAGAR
	$total=$montoIn+$total_interes;
	$deudaActua=$montoIn+$total_interes + $deudaInteres;
	
	if($actualizar=="si"){
	actualizarTotalCuota($idcredito,$total,$total_interes,$total,$mysqli);
	}
	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	if($actualizar=="si"){
    actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento,$mysqli);
	}
	}	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;
	 if($actualizar=="si"){
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento,$mysqli);
	}		
	}
			$DeudaPendiente=$DeudaPendiente+$deudaActua;
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	if($actualizar=="si"){
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento,$mysqli);
	}
	}
	
	
	
	}else{
	$Esado="Pagado";
	$stylecolor="background-color: #ccc;color:#000";
	$deudaActua=0;
	$total=0;
	$diff2=0;
	
	}
	
	}else{
	
	
	if(($MontoConDescuento+$tinteres)>$totalPago){
	 $Esado="Pendiente";
	 $diff2=0;
     $deudaActua=($MontoConDescuento+$tinteres)-$totalPago + $deudaInteres;
	 $total=$MontoConDescuento-$totalPago;
	 $stylecolor="text-decoration: line-through;";
	
	}else{
		$Esado="Pagado";
	$stylecolor="background-color: #ccc;color:#000";
	$deudaActua=0;
	$diff2=0;
	$total=0;
	}
    	
	
}

 

$totalInteresActual=$totalInteresActual+$total_interes + $deudaInteres;

$deuda=$deuda+$deudaActua;

if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}


$TotalPagadoSinInteres=$TotalPagadoSinInteres+$totalPagoCredito;
$TotalInteresesPagado=$TotalInteresesPagado+$totalPagoInteres;
$SubTotal=$SubTotal+$Monto;
$totalEnDescuento=$totalEnDescuento+$descuento;
$TotalEnInteres=$TotalEnInteres+$total_interes;
$TotalEnDeuda=$TotalEnDeuda+$total;
$TotalEnPagado=$TotalEnPagado+$totalPago;
$TotalAPagar=$TotalAPagar+$deudaActua;
$TotalDiasAtrasado=$TotalDiasAtrasado+$diff2;
if($DiasAtrazo==""){
	$DiasAtrazo=$diff2;
	
}


}
if($DeudaPendiente==0){
	$TotalApagarSinInteres=$MontoCuotas;
	$MontoCuota=$MontoCuotas;
	$DeudaPendiente=$MontoCuotas;
}
if($DeudaPendiente<0){
	$DeudaPendiente=0;
}
if($TotalApagarSinInteres<0){
	$TotalApagarSinInteres=0;
}

}



$datos[0]=$totalEnDescuento;
$datos[1]=$TotalEnInteres;
$datos[2]=$TotalEnDeuda;
$datos[3]=$TotalEnPagado;
$datos[4]=$TotalAPagar;
$datos[5]=$TotalDiasAtrasado;
$datos[6]=$nrodecuotasatrazado;
$datos[7]=$TotalApagarSinInteres;
$datos[8]=$DeudaPendiente;
$datos[9]=$stylecolor;
$datos[10]=$totalInteresActual;
$datos[11]=$SubTotal;
$datos[12]=$TotalInteresesPagado;
$datos[13]=$TotalPagadoSinInteres;
$datos[14]=$tinteres;
$datos[15]=$DiasAtrazo;
$datos[16]=$SumadeudaInteres;

 mysqli_close($mysqli);
return $datos;
}

function actualizarTotalCuota($idcredito,$total,$totalinteres,$totaldeuda,$mysqli=null){
	
	$cerrarConexion=false;
	if($mysqli==null){
	$mysqli=conectar_al_servidor();
	$cerrarConexion=true;
	}
	$consulta1="Update credito set total=?,totalinteres=?,totaldeuda=? 
	where idcredito='$idcredito' and (totalinteres!='$totalinteres' or total!='$total' or totaldeuda!='$totaldeuda') ";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$total,$totalinteres,$totaldeuda); 

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

 $stmt1->close();
 if($cerrarConexion==true){
 mysqli_close($mysqli);
 }
}
function editarDiasAtrazadosdesdecalcularcredito($codCliente,$nroDias,$mysqli=null)
{
	
$cerrarConexion=false;
if($mysqli==null){
$mysqli=conectar_al_servidor();
$cerrarConexion=true;
}
$consulta1="Update cliente set totaldias='$nroDias' where cod_cliente='$codCliente' and totaldias<'$nroDias' ";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$stmt1->close();
if($cerrarConexion==true){
mysqli_close($mysqli);
}
	
}
function actualizardiasatrazadocredito($idcredito,$nroDias,$mysqli=null)
{
	
$cerrarConexion=false;
if($mysqli==null){
$mysqli=conectar_al_servidor();
$cerrarConexion=true;
}
$consulta1="Update credito set diasatrasados='$nroDias' where idcredito='$idcredito' ";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$stmt1->close();
if($cerrarConexion==true){
mysqli_close($mysqli);
}
	
}
?>

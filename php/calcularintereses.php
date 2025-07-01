<?php


function calcularintereses($buscar,$fecha1,$fecha2,$filtro1,$filtro2,$filtro3,$actualizar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicion="";

if($filtro1=="1"){
$condicion=" and cr.fechapago>='$fecha1' and cr.fechapago<='$fecha2'";
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
	
$sql= "select vt.cod_clienteFK,vt.TipoVenta,vt.puntoexpedicion,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1)) as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,totaldeuda,vt.num_factura,cr.descuento
,cr.deudaInteres,(totalinteres + deudaInteres) as totalinteres,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha asc limit 1),0) as FechaPagoCredito,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha desc limit 1),0) as FechaUltimoPago,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
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
    ".$condicioncodigo.$condicionpago.$condicion."  order by cr.idcredito asc";
	
	

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
$stylecolor="";
$diff2=0;
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

$FechaUltimoPago = utf8_encode($valor['FechaUltimoPago']);
$FechaPagoCredito = utf8_encode($valor['FechaPagoCredito']);  
$deudaInteres = utf8_encode($valor['deudaInteres']);
$idcredito = utf8_encode($valor['idcredito']);     
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$totalPago = utf8_encode($valor['totalPago']); 
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$diff = utf8_encode($valor['diff']);
$total_venta = utf8_encode($valor['total_venta']);
$interes = utf8_encode($valor['interes']);
$dias = utf8_encode($valor['dias']);
$total = utf8_encode($valor['total']);
$tinteres = utf8_encode($valor['totalinteres']);
$totaldeuda = utf8_encode($valor['totaldeuda']);
$entrega = utf8_encode($valor['entrega']);
$fechapagado = utf8_encode($valor['fechapagado']);
$cantidad = utf8_encode($valor['cantidad']);
$num_factura = utf8_encode($valor['num_factura']);
$descuento = utf8_encode($valor['descuento']);
$nroCancelado = utf8_encode($valor['nroCancelado']);
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);
$clientenombre = utf8_encode($valor['clientenombre']);
$documentocliente = utf8_encode($valor['documentocliente']);
$nroCouta = utf8_encode($valor['nroCouta']);
$TipoVenta = utf8_encode($valor['TipoVenta']);
$totalPagoVenta = utf8_encode($valor['totalPagoVenta']);
$totalPagoCredito = utf8_encode($valor['totalPagoCredito']);
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$MontoConDescuento=$Monto-$descuento;
$MontoSobrante=$MontoConDescuento-$totalPago;
if($MontoCuotas==0){
$MontoCuotas=$MontoConDescuento-$totalPago;
}
$deudaActua=0;
$total_interes=0;
$stylecolor=" ";
if($nroCancelado==0){
	
if(($Monto+$totalPagoInteres)>($totalPago+$descuento)){
		
	$Esado="Pendiente";
	
	if($diff<0 && $diff!=""){
	$diff=$diff*-1;
	editarDiasAtrazadosdesdecalcularcredito($cod_clienteFK,$diff);
	}else{
	$diff=0;
    }
	$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
	$TotalApagarSinInteres=$TotalApagarSinInteres+($MontoConDescuento-($totalPagoCredito));
	$nrodecuotasatrazado=$nrodecuotasatrazado+1;
	if($interes!=0){
		
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
		
		
	// $fechahoy=date('Y-m-d');	
	// $datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy))));
	// $datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));
	// $interval=$datetime2->diff($datetime1);
    // $diff=$interval->format('%a');
	
	
	$diasGracia=$diff-$dias;
	if($diasGracia>0){
	$montoIn=$MontoConDescuento-$totalPagoCredito;	
	$i=($interes*($Monto - $totalPagoCredito))/100;
	$total_interes=($i*$diff);
	$t=$montoIn+$total_interes;
	$deudaActua=$montoIn+$total_interes + $deudaInteres;
	$total=$t;	
	// $t=$MontoConDescuento+$total_interes;
	// $total=$t;
	// $deudaActua=$t-$totalPagoCredito-$totalPagoInteres;
	if($actualizar=="si"){
	actualizarTotalCuota($idcredito,$total,$total_interes,$t);
	}

	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	if($actualizar=="si"){
    actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	}
	}	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	 if($actualizar=="si"){
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	}		
	}
			
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	if($actualizar=="si"){
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	}
	}
	
	$DeudaPendiente=$DeudaPendiente+$deudaActua;
	
	}else{
	$Esado="Pagado";
	$stylecolor="background-color: #ccc;color:#000";
	$deudaActua=0;
	$total=0;
	$diff=0;
	
	}
	
	}else{
	
	
	if(($MontoConDescuento+$tinteres)>$totalPago){
	 $Esado="Pendiente";
	 $diff="0";
     $deudaActua=($MontoConDescuento+$tinteres)-$totalPago + $deudaInteres;
	 $total=$MontoConDescuento-$totalPago;
	 $stylecolor="text-decoration: line-through;";
	
	}else{
		$Esado="Pagado";
	$stylecolor="background-color: #ccc;color:#000";
	$deudaActua=0;
	$diff=0;
	$total=0;
	}
    	
	
}

 

$totalInteresActual=$totalInteresActual+$total_interes;
//$total_interes=$total_interes+$totalPagoInteres;
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
$TotalDiasAtrasado=$TotalDiasAtrasado+$diff;

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
$datos[14]=$deudaInteres;

 mysqli_close($mysqli);
return $datos;
}

function actualizarTotalCuota($idcredito,$total,$totalinteres,$totaldeuda){
	
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
function editarDiasAtrazadosdesdecalcularcredito($codCliente,$nroDias)
{
	
$mysqli=conectar_al_servidor(); 
$consulta1="Update cliente set totaldias='$nroDias' where cod_cliente='$codCliente' and totaldias<'$nroDias' ";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	
}
?>
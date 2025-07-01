<?php
require("conexion.php");
include("verificar_navegador.php");
include("calcularintereses.php");
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
	$idzona=$_POST["idzona"];
 	$idzona=utf8_decode($idzona);
	$buscar2=$_POST["buscar2"];
 	$buscar2=utf8_decode($buscar2);
	$tipo=$_POST["tipo"];
 	$tipo=utf8_decode($tipo);
 	buscarregistro($buscar,$lat,$lot,$buscarpor,$buscar2,$tipo,$idzona,$user);
 }
 
 if($operacion=="buscarlistaasinconexion"){
 	$fecha1=$_POST["fecha1"];
 	$fecha2=$_POST["fecha2"];
	$idzona=$_POST["idzona"];
 	$idzona=utf8_decode($idzona);
 	buscarregistrosinconexion($fecha1,$fecha2,$idzona,$user);
 }

 if($operacion=="buscarDetalle"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscardetalle($buscar);
 }
 if($operacion=="buscarDetalleProductos"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarDetalleProductos($buscar);
 }
 if($operacion=="buscarDetallePagosrealizados"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarDetallePagosrealizados($buscar);
 }





}


function abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$operacion)
{

if($cod_productoFK==""  || $costo==""  || $cod_clienteFK==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo") 
{


$consulta1="Insert into pedidos (cod_productoFK,costo,cod_clienteFK,fecha,estado)
values(?,?,?,CURRENT_TIMESTAMP,'ACTIVO')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK);


}


if($operacion=="editar")
{

$consulta1="Update pedidos set cod_productoFK=?,costo=?,cod_clienteFK=?,fecha=CURRENT_TIMESTAMP,estado='ACTIVO' where idpedidos=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK,$idpedidos);




}

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function buscarregistro($buscar,$lat,$lot,$buscarpor,$buscar2,$tipo,$idzona,$cod_cobradorFK)
{
$mysqli=conectar_al_servidor();
$condicionzona="";
$condicionCuenta="";
if($idzona!=""){
	$condicionzona=" and cl.idzonaFk='$idzona' ";
}

	 $sqlcoordenads=",(6371 * ACOS(SIN(RADIANS(cl.lat)) * SIN(RADIANS(".$lat."))+COS(RADIANS(cl.lot - ".$lot.")) * COS(RADIANS(cl.lat))* COS(RADIANS(".$lat.")))) AS distance ";
	$oderbycoordenadas=" order by distance asc ";
if($lat=="" || $lot==""){
	$sqlcoordenads="";
	$oderbycoordenadas="";
}

$condicionCobrado=" and vt.cod_cobradorFK='$cod_cobradorFK' ";
if($cod_cobradorFK=="454"){
	$condicionCobrado="";
}
$condicionCobrado="";

if($tipo=="1"){
	$condicionCuenta="  (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)>0  and"; 
}else{
		$condicionCuenta="  (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)<=0  and"; 
}

if($buscarpor=="cliente"){
	
	
	
	 $sql= "Select vt.idGaranteFk,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,
	 vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.Vendedor1,vt.Vendedor2 ,vt.cod_venta,vt.comision,vt.cod_local,vt.pago ".$sqlcoordenads."
		 ,cl.lat,cl.lot,cl.ci_cliente,pr.nombre_persona as clientenombre,
		(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select direccion from persona where cod_persona=cod_clienteFK) as direccion,
		(Select telefono from persona where cod_persona=cod_clienteFK) as telefono,
		(Select email from persona where cod_persona=cod_clienteFK) as email,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as nrodogarante,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta dtv1 where dtv1.cod_ventaFK=vt.cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito cr1 where cr1.cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select Monto from credito  cr1 where cr1.cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito  cr1 where cr1.cod_venta=vt.cod_venta and plazo!='ENTREGA' limit 1) as nroCouta,
		(Select fechapago from credito cr1 where cr1.cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		IFNULL((select sum(cr.descuento) from credito cr1 where cr1.cod_venta=vt.cod_venta limit 1),0) as totaldescuento,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk limit 1),0) as totalpagado
		,cr.deudaInteres,(totalinteres + deudaInteres) as totalinteres
		from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
		inner join venta vt  on vt.cod_clienteFK=cl.cod_cliente
		 inner join credito cr on vt.cod_venta=cr.cod_venta
		where IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 and ".$condicionCuenta."  concat(pr.nombre_persona,' ',cl.ci_cliente,' ',pr.telefono) like '%".$buscar."%' ".$condicionzona." group by  vt.cod_venta ".$oderbycoordenadas." limit 300";
	
	


}

if($buscarpor=="entrefecha"){
	
	
	 $sql= "Select vt.idGaranteFk,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,
	 vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.Vendedor1,vt.Vendedor2 ,vt.cod_venta,vt.comision,vt.cod_local,vt.pago ".$sqlcoordenads."
		 ,cl.lat,cl.lot,cl.ci_cliente,pr.nombre_persona as clientenombre,
		((Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select direccion from persona where cod_persona=cod_clienteFK) as direccion,
		(Select telefono from persona where cod_persona=cod_clienteFK) as telefono,
		(Select email from persona where cod_persona=cod_clienteFK) as email,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as nrodogarante,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta dtv1 where dtv1.cod_ventaFK=vt.cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito cr1 where cr1.cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select Monto from credito  cr1 where cr1.cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito  cr1 where cr1.cod_venta=vt.cod_venta and plazo!='ENTREGA' limit 1) as nroCouta,
		(Select fechapago from credito cr1 where cr1.cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		IFNULL((select sum(cr.descuento) from credito cr1 where cr1.cod_venta=vt.cod_venta limit 1),0) as totaldescuento,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk limit 1),0) as totalpagado
		from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
		inner join venta vt  on vt.cod_clienteFK=cl.cod_cliente
		 inner join credito cr on vt.cod_venta=cr.cod_venta
		where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 and ".$condicionCuenta."  (select count(fechapago) from  credito cr where vt.cod_venta=cr.cod_venta and cr.fechapago>='$buscar' and cr.fechapago<='$buscar2' and cr.Monto-cr.descuento>IFNULL((select sum(pg.Monto) from pago pg where cr.idcredito=pg.cod_creditoFK),0))>0  "
		.$condicionzona." group by  vt.cod_venta ".$oderbycoordenadas." limit 300";
	
	

}


if($buscarpor=="visitas"){
	if($oderbycoordenadas==""){
		$oderbycoordenadas==" order by vs.idvisitas asc";
			
	}else{
		$oderbycoordenadas==",vs.idvisitas asc";
	}
	

	$sql= "Select vt.idGaranteFk,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,
	 vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.Vendedor1,vt.Vendedor2 ,vt.cod_venta,vt.comision,vt.cod_local,vt.pago ".$sqlcoordenads."
		 ,cl.lat,cl.lot,cl.ci_cliente,pr.nombre_persona as clientenombre,
	(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select direccion from persona where cod_persona=cod_clienteFK) as direccion,
		(Select telefono from persona where cod_persona=cod_clienteFK) as telefono,
		(Select email from persona where cod_persona=cod_clienteFK) as email,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as nrodogarante,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta dtv1 where dtv1.cod_ventaFK=vt.cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito cr1 where cr1.cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select Monto from credito  cr1 where cr1.cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito  cr1 where cr1.cod_venta=vt.cod_venta and plazo!='ENTREGA' limit 1) as nroCouta,
		(Select fechapago from credito cr1 where cr1.cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		IFNULL((select sum(cr.descuento) from credito cr1 where cr1.cod_venta=vt.cod_venta limit 1),0) as totaldescuento,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk limit 1),0) as totalpagado
		from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
		inner join venta vt  on vt.cod_clienteFK=cl.cod_cliente
		inner join visitas vs on vt.cod_venta=vs.cod_venta
		 inner join credito cr on vt.cod_venta=cr.cod_venta
		where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  and ".$condicionCuenta." vs.fecha>='$buscar' and vs.fecha <='$buscar2'  "
		.$condicionzona." group by  vt.cod_venta ".$oderbycoordenadas." limit 300";
	

		
}



$pagina = "";   

 $stmt = $mysqli->prepare($sql);


if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=0;
 $TotalVentas=0;
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
		  	  $ci_cliente=utf8_encode($valor['ci_cliente']);
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
			  $pagado=utf8_encode($valor['totalpagado']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $Garante=utf8_encode($valor['Garante']);
			  $nrodocliente=utf8_encode($valor['nrodocliente']);
			  $nrodogarante=utf8_encode($valor['nrodogarante']);
			  $direccion=utf8_encode($valor['direccion']);
			  $referencia=utf8_encode($valor['email']);
			  $telefono=utf8_encode($valor['telefono']);
			   $totalpagado=$pagado+$pago;
			   
			    $styleCancelado="";
				
				  $datos=calcularintereses($cod_venta,0,0,"2","2","2","si");
				$totaldescuento=$datos[0];
                $totalinteres=$datos[12];
                $totalpagado=$datos[3];
                $totalInteresActual=$datos[14] + $datos[10];
                $deudaTotal=$datos[4];
                $deudaActual=$datos[8];
			 $SubTotalDeuda=$datos[11];
			 $TotalApagarSinInteres=$datos[7];
			 if($SubTotalDeuda==0){
				 $SubTotalDeuda=$total_venta;
				  $total=($total_venta-$totaldescuento)+$totalinteres;
			 }else{
				  $total=$SubTotalDeuda;
			 }
				
		        
			  
			  
			
			
		 $TotalVentas=$total_venta+$TotalVentas;
 $TotalPagos= $TotalPagos+$totalpagado;
 $TotalDeuda= $TotalDeuda+$deudaActual;
			   
$lat = utf8_encode($valor['lat']); 
$long = utf8_encode($valor['lot']); 
if($sqlcoordenads!=""){
$distance = number_format($valor['distance'],'2',',','.');
}else{
	$distance = "Ubic. no Selecc.";
}
 
$datosDetalle=buscar_detalles_venta($cod_venta);
$diff= buscarDiasAtra($cod_venta);
  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
<tr id='tbSelecRegistro' onclick='obtenerdatoscuentas(this)' name='tb_offline_".$cod_venta."' >
<td id='td_17' style='display:none' class='td_search'>".$num_factura."</td>
<td id='' style='width:100%;'>
<table style='width:100%' class='tableRegistroSearchC'>
<tr>
<td id='' style='width:75%;' class='td_search'>".$clientenombre."</td>
<td  style='width:25%;'class='td_search' >". number_format($SubTotalDeuda,'0',',','.')."</td>
</tr>
</table>
<table style='width:100%' class='tableRegistroSearchD'>
<tr>
<td id='' style='width:100%;' class='td_search'>".$datosDetalle[1]."</td>

</tr>
</table>
<table style='width:100%' class='tableRegistroSearchD'>
<tr>
<td  style='width:33%;'class='td_search' >I:&nbsp". number_format($totalInteresActual,'0',',','.')."Gs.</td>
<td  style='width:33%;'class='td_search' >P:&nbsp". number_format($totalpagado,'0',',','.')."Gs.</td>
<td  style='width:33%;' class='td_search'>D:&nbsp". number_format($deudaTotal,'0',',','.')."Gs.</td>
</tr>
</table>
</td>
<td id='td_15' style='display:none;'class='td_search' >". number_format($totalpagado,'0',',','.')."</td>
<td id='td_14' style='display:none;' class='td_search'>". number_format($deudaActual,'0',',','.')."</td>
<td id='td_30' style='display:none;' class='td_search'>". number_format($deudaTotal,'0',',','.')."</td>
<td id='td_1' style='display:none' >".$cod_venta."</td>
<td id='td_2' style='display:none' >".$cod_clienteFK."</td>
<td id='td_3' style='display:none'>".$clientenombre."</td>
<td id='td_4' style='display:none'>".$ci_cliente."</td>
<td id='td_5' style='display:none'>". number_format($total,'0',',','.')."</td>
<td id='td_6' style='display:none'>". number_format($SubTotalDeuda,'0',',','.')."</td>
<td id='td_7' style='display:none'>".number_format($totaldescuento,'0',',','.')."</td>
<td id='td_8' style='display:none'>". number_format($pagado,'0',',','.')."</td>
<td id='td_16' style='display:none'>". number_format($pago,'0',',','.')."</td>
<td id='td_20' style='display:none'>". number_format($totalinteres,'0',',','.')."</td>
<td id='td_21' style='display:none'>". number_format($totalInteresActual,'0',',','.')."</td>
<td id='td_22' style='display:none'>". number_format($TotalApagarSinInteres,'0',',','.')."</td>
<td id='td_9' style='display:none'>".$lat."</td>
<td id='td_10' style='display:none'>".$long."</td>
<td id='td_11' style='display:none'>".$diff."</td>
<td id='td_19' style='display:none'>".$direccion."</td>
<td id='td_18' style='display:none'>".$referencia."</td>
<td id='td_12' style='display:none'>".$telefono."</td>
<td id='td_13' style='display:none'>".$datosDetalle[0]."</td>
<td id='td_40' style='display:none'>".$nrodogarante." - ".$Garante."</td>
</tr>
</table>";

/*buscardetalleoffline($cod_venta)*/
}
}
 mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}


/*Buscar Registro en vista*/
function buscarregistrosinconexion($fecha1,$fecha2,$idzona,$cod_cobradorFK)
{
$mysqli=conectar_al_servidor();
$condicionzona="";
if($idzona!=""){
	$condicionzona=" and cl.idzonaFk='$idzona' ";
}
	$condicionCuenta="  IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk and pg.tipo='Pago Cuota'),0) + IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0)<IFNULL((select sum(cr.Monto) from credito cr where cr.cod_venta=vt.cod_venta),vt.total_venta)  and"; 

	
	 $sql= "Select vt.idGaranteFk,vt.fecha_venta,vt.total_venta,vt.cod_usuarioFK,vt.cod_clienteFK,vt.num_factura,
	 vt.cod_cobradorFK,vt.TipoVenta,vt.TipoPago,vt.Vendedor1,vt.Vendedor2 ,vt.cod_venta,vt.comision,vt.cod_local,vt.pago 
		 ,cl.lat,cl.lot,cl.ci_cliente,pr.nombre_persona as clientenombre,
		(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
		(Select ci_cliente from cliente where cod_cliente=idGaranteFk) as nrodogarante,
		(Select direccion from persona where cod_persona=cod_clienteFK) as direccion,
		(Select telefono from persona where cod_persona=cod_clienteFK) as telefono,
		(Select email from persona where cod_persona=cod_clienteFK) as email,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=idGaranteFk) as Garante,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(cod_detalle) from detalle_venta where cod_ventaFK=cod_venta) as nrodetalle,
		(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
		IFNULL((Select montodevuelto from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as montodevuelto,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
		(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
		IFNULL((select sum(cr.descuento) from credito cr where cr.cod_venta=vt.cod_venta),0) as totaldescuento,
		IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado
		from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
		inner join venta vt  on vt.cod_clienteFK=cl.cod_cliente
		where IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  and  ".$condicionCuenta."  (select count(fechapago) from  credito cr where vt.cod_venta=cr.cod_venta and cr.fechapago>='$fecha1' and cr.fechapago<='$fecha2' and cr.Monto-cr.descuento>IFNULL((select sum(pg.Monto) from pago pg where cr.idcredito=pg.cod_creditoFK and pg.tipo='Pago Cuota'),0))>0  "
		.$condicionzona." limit 300";
		
	
	

$pagina = "";   

 $stmt = $mysqli->prepare($sql);


if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
 $TotalVentas=0;
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
		  	  $ci_cliente=utf8_encode($valor['ci_cliente']);
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
			  $pagado=utf8_encode($valor['totalpagado']);
			  $totaldescuento=utf8_encode($valor['totaldescuento']);
			  $idGaranteFk=utf8_encode($valor['idGaranteFk']);
			  $Garante=utf8_encode($valor['Garante']);
			  $nrodocliente=utf8_encode($valor['nrodocliente']);
			  $nrodogarante=utf8_encode($valor['nrodogarante']);
			  $direccion=utf8_encode($valor['direccion']);
			  $referencia=utf8_encode($valor['email']);
			  $telefono=utf8_encode($valor['telefono']);
			   $totalpagado=$pagado+$pago;
			   
			    $styleCancelado="";
				
				 
			 $datos=calcularintereses($cod_venta,0,0,"2","2","2","si");
				$totaldescuento=$datos[0];
                $totalinteres=$datos[12];
                $totalpagado=$datos[3];
                $totalInteresActual=$datos[10] + $datos[14];
                $deudaTotal=$datos[4];
                $deudaActual=$datos[8];
			 $SubTotalDeuda=$datos[11];
			 $TotalApagarSinInteres=$datos[7];
			 if($SubTotalDeuda==0){
				 $SubTotalDeuda=$total_venta;
				  $total=($total_venta-$totaldescuento)+$totalinteres;
			 }else{
				  $total=$SubTotalDeuda;
			 }
			 
		

 
$datosDetalle=buscar_detalles_venta($cod_venta);
$diff= buscarDiasAtra($cod_venta);
  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' id='tableCuentaOffline_".$cod_venta."'  name='tableCuentaOffline' >
<tr id='tbSelecRegistro' onclick='obtenerdatoscuentasOfline(this)' name='tb_offline_".$cod_venta."' >
<td id='td_17' style='display:none' class='td_search'>".$num_factura."</td>
<td id='' style='width:100%;'>
<table style='width:100%' class='tableRegistroSearchC' id='tableCuentaOffline2_".$cod_venta."'>
<tr>
<td id='' style='width:75%;' class='td_search'>".$clientenombre."</td>
<td  style='width:25%;'class='td_search' >". number_format($SubTotalDeuda,'0',',','.')."</td>
</tr>
</table>
<table style='width:100%' class='tableRegistroSearchD'>
<tr>
<td id='' style='width:100%;' class='td_search'>".$datosDetalle[1]."</td>

</tr>
</table>
<table style='width:100%;' class='tableRegistroSearchD' >
<tr>
<td  style='width:33%;'class='td_search' >I:&nbsp". number_format($totalInteresActual,'0',',','.')."Gs.</td>
<td  style='width:33%;'class='td_search' >P:&nbsp". number_format($totalpagado,'0',',','.')."Gs.</td>
<td  style='width:33%;' class='td_search'>D:&nbsp". number_format($deudaTotal,'0',',','.')."Gs.</td>
</tr>
</table>
<table style='width:100%;display:none' class='tableRegistroSearchD' id='tableCuentaOffline3_".$cod_venta."'>
<tr>
<td  style='width:70%;'class='td_search' >P. Offline:</td>
<td  style='width:30%;' class='td_search' id='tb_pagado_offline2_".$cod_venta."'   name='td_25'  >0</td>
</tr>
</table>
</td>
<td id='td_15' style='display:none' class='td_search' >". number_format($totalpagado,'0',',','.')."</td>
<td id='td_14' style='display:none;' class='td_search'>". number_format($deudaActual,'0',',','.')."</td>
<td id='td_30' style='display:none;' class='td_search'>". number_format($deudaTotal,'0',',','.')."</td>
<td  style='display:none' class='td_search' id='tb_pagado_offline_".$cod_venta."'   name='td_25'  >0</td>
<td id='td_1' style='display:none' >".$cod_venta."</td>
<td id='td_2' style='display:none' >".$cod_clienteFK."</td>
<td id='td_3' style='display:none'>".$clientenombre."</td>
<td id='td_4' style='display:none'>".$ci_cliente."</td>
<td  style='display:none' class='td_search' id='tb_pagado_offline3_".$cod_venta."'   name='td_26'  >0</td>
<td id='td_5' style='display:none'>". number_format($total,'0',',','.')."</td>
<td id='td_6' style='display:none'>". number_format($SubTotalDeuda,'0',',','.')."</td>
<td id='td_7' style='display:none'>".number_format($totaldescuento,'0',',','.')."</td>
<td id='td_8' style='display:none'>". number_format($pagado,'0',',','.')."</td>
<td id='td_16' style='display:none'>". number_format($pago,'0',',','.')."</td>
<td id='td_20' style='display:none'>". number_format($totalinteres,'0',',','.')."</td>
<td id='td_27' style='display:none'>". number_format($totalInteresActual,'0',',','.')."</td>
<td id='td_28' style='display:none'>". number_format($TotalApagarSinInteres,'0',',','.')."</td>
<td id='td_11' style='display:none'>".$diff."</td>
<td id='td_19' style='display:none'>".$direccion."</td>
<td id='td_18' style='display:none'>".$referencia."</td>
<td id='td_12' style='display:none'>".$telefono."</td>
<td id='td_21' style='display:none'>".buscardetalleoffline($cod_venta)."</td>
<td id='td_22' style='display:none'>".$datosDetalle[0]."</td>
<td id='td_23' style='display:none'>".buscarDetallePagosrealizadosoffline($cod_venta)."</td>
<td id='td_24' style='display:none'>".buscarDetalleProductosOffline($cod_venta)."</td>
<td id='td_40' style='display:none'>".$nrodogarante." - ".$Garante."</td>
</tr>
</table>
<div style='display:none' name='Cuentasoffline' id='$cod_venta'>".$clientenombre."</div>";

/*buscardetalleoffline($cod_venta)*/
}
}
 mysqli_close($mysqli); 
    
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}






function buscardetalleoffline($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	

$sql= "select vt.cod_clienteFK,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,totaldeuda,cr.descuento
,cr.deudaInteres,(totalinteres + deudaInteres) as totalinteres,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha asc limit 1),0) as FechaPagoCredito,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.Monto!=0 order by pg.Fecha desc limit 1),0) as FechaUltimoPago,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota' ),0) as totalPagoCuota,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' ";
 
 
$pagina = "";  
$paginaextracto = "";  
$interes = "0";  
$diasatrazado = "0";  
$dias = "0";  
$totalPagado = "0";  
$total_venta = "0";  
$deuda = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
$entrega = "0";  
$TotalCuotasPendientes = "0";   
$TotalInteresActual = "0";   
$MontoCuota = "0";   
$MontoCuotas = "0";   
$SubTotalAPagar = "0";  
$TotalAPagar = "0";  
$MontoCuotas = "0";   
$SubTotalAPagar = "0";  
$TotalAPagar = "0";  
$TotalApagarSinInteres = "0";  
$nrodecuotasatrazado = "0";  
$DeudaPendiente=0;  
$ControlPago=0;
$PoderPAgar="NO";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=0;
$controlStyle="";
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
$descuento = utf8_encode($valor['descuento']);
$nroCancelado = utf8_encode($valor['nroCancelado']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$totalPagoCredito = utf8_encode($valor['totalPagoCuota']);
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);


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
	
	if($diff<0){
	$diff=$diff*-1;
	editarDiasAtrazadosdesdecalcularcredito($cod_clienteFK,$diff);
	}else{
	$diff=0;
    }
	$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
	$TotalApagarSinInteres=$TotalApagarSinInteres+($MontoConDescuento-($totalPagoCredito+$descuento));
	$nrodecuotasatrazado=$nrodecuotasatrazado+1;
	if($interes!=0){
		
		$fechahoy=date('Y-m-d');	
	$datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy)))); 
	$datetime3= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));	
	if($FechaPagoCredito=="0"){
		$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));		
	}else{
		$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$FechaUltimoPago))));		
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
	 $diasGracia=$diff2-$dias;
		
	if($diasGracia>0){
	$montoIn=$MontoConDescuento-$totalPagoCredito;	
	$i=($interes*($Monto - $totalPagoCredito ))/100;                                // aca modifique
	$total_interes=($i*$diff);
	$t=$montoIn+$total_interes;
	$deudaActua=$montoIn+$total_interes;
	$total=$t;
	
	// $t=$MontoConDescuento+$total_interes;
	// $deudaActua=$t-$totalPagoCredito-$totalPagoInteres;
	
	actualizarTotalCuota($idcredito,$total,$total_interes,$t);

	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;
	
    actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	
	}	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;
	 
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
		
	}
			
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;
	
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	
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
     $deudaActua=($MontoConDescuento+$tinteres)-$totalPago;
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



$totalInteres=$totalInteres+$totalPagoInteres+$total_interes;
$TotalInteresActual=$TotalInteresActual+$total_interes;
$deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$diff;




//                                                                                        Este agregue yo ahora  y tambien agregue en el td
$DeudaInteres=$total_interes + $deudaInteres ;
if($DeudaInteres<=0){
	$DeudaInteres=0;
}
$TotalDeuda=$DeudaInteres+ ($Monto-($totalPagoCredito+$descuento)) ;

if($TotalDeuda!=0){
if($ControlPago==0){
	$PoderPAgar="SI";
}else{
	$PoderPAgar="NO";
}

$ControlPago=$ControlPago+1;

}


$pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' style='$stylecolor' >
<tr id='tbSelecRegistro' onclick='obtenerdatoscuoterooffline(this)'   name='pagoscuenta_".$cod_venta."'>
<td id='td_1' style='display:none' >".$idcredito."</td>
<td id='td_2' style='width:5%'  class='td_search'>".$plazo."</td>
<td id='td_3' style='width:15%' class='td_search'>".$fechapago."</td>
<td id='td_4' style='display:none'>".$cod_venta."</td>
<td id='td_10' style='display:none'>". number_format($MontoConDescuento,'0',',','.')."</td>
<td id='td_11' style='display:none'>". number_format($DeudaInteres,'0',',','.')."</td>
<td id='td_5' style='display:none'>". number_format($Monto,'0',',','.')."</td>
<td id='td_9' style='width:10%' class='td_search'>". number_format($TotalDeuda,'0',',','.')."</td>
<td id='td_6' style='display:none' class='td_search'>".$Esado."</td>
<td id='td_7' style='display:none'>".$Nro_recibo."</td>
<td id='td_8' style='display:none'>".$diff."</td>
<td id='td_12' style='display:none'>".number_format($totalPago,'0',',','.')."</td>
<td id='td_13' style='display:none'>".$PoderPAgar."</td>
</tr>
</table>
";











}
}

 mysqli_close($mysqli); 
return $pagina;
 
}
/*Buscar */
function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
 IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0) as nroDevoluciones,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0) as nroCambios,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0) as nroGarantia
 from
 venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
 inner join producto pr on pr.cod_producto=dtv.cod_productoFK
 where vt.cod_venta='$buscar' ";
$pagina1 = "";   
$pagina2 = "";   
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



$nombre_producto = utf8_encode($valor['nombre_producto']);      
$nroDevoluciones = utf8_decode($valor['nroDevoluciones']);      
$nroCambios = utf8_decode($valor['nroCambios']);      
$nroGarantia = utf8_decode($valor['nroGarantia']);      
$cantidad_detalle = utf8_decode($valor['cantidad_detalle']);      
$precio_producto = utf8_decode($valor['precio_producto']);      
$subtotal = utf8_decode($valor['subtotal']);      
if($nroDevoluciones==0 && $nroCambios==0){
  $pagina1.="<table class='tableTicket'>
<tr>
<td style='width:100%'>".$nombre_producto."</td>
</tr>
</table>";
$pagina1.="<table class='tableTicket'>
<tr>
<td style='width:33%'>".number_format($cantidad_detalle,'0',',','.')."</td>
<td style='width:33%'>".number_format($precio_producto,'0',',','.')."</td>
<td style='width:33%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";
$pagina2=$a.") ".$nombre_producto;
$a=$a+1;
}


}
}
$datos[0]=$pagina1;
$datos[1]=$pagina2;
 mysqli_close($mysqli); 
return $datos;
}

function buscardetalle($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	


$sql= "select vt.cod_clienteFK,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,totaldeuda,cr.descuento
,cr.deudaInteres,(totalinteres + deudaInteres) as totalinteres,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha asc limit 1),0) as FechaPagoCredito,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha desc limit 1),0) as FechaUltimoPago,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota' ),0) as totalPagoCuota,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' ";
 
 
$pagina = "";  
$paginaextracto = "";  
$interes = "0";  
$diasatrazado = "0";  
$dias = "0";  
$totalPagado = "0";  
$total_venta = "0";  
$deuda = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
$entrega = "0";  
$TotalCuotasPendientes = "0";   
$TotalInteresActual = "0";   
$MontoCuota = "0";   
$MontoCuotas = "0";   
$SubTotalAPagar = "0";  
$TotalAPagar = "0";  
$TotalApagarSinInteres = "0";  
$nrodecuotasatrazado = "0";  
$DeudaPendiente=0;   
$diff2=0;
$Fecha1=0;
$ControlPago=0;
$PoderPAgar="NO";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=0;
$controlStyle="";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$diff2=0;
$Fecha1=0;
$Fecha2=0;
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
$descuento = utf8_encode($valor['descuento']);
$nroCancelado = utf8_encode($valor['nroCancelado']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$totalPagoCredito = utf8_encode($valor['totalPagoCuota']);
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);
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
	
	if($diff<0){
	$diff=$diff*-1;
	editarDiasAtrazadosdesdecalcularcredito($cod_clienteFK,$diff);
	}else{
	$diff=0;
    }
	$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
	$TotalApagarSinInteres=$TotalApagarSinInteres+($Monto-($totalPagoCredito+$descuento));
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
	
	
	$diasGracia=$diff2-$dias;
	
	if($diasGracia>0){
	$montoIn=$Monto-$totalPagoCredito;
	$i=($interes*($Monto - $totalPagoCredito ))/100;
	$total_interes=($i*$diff);
	$t=$montoIn+$total_interes;
	$deudaActua=$montoIn+$total_interes + $deudaInteres;
	$total=$t;
	
	// $t=$MontoConDescuento+$total_interes;
	// $deudaActua=$t-$totalPagoCredito-$totalPagoInteres;
	
	actualizarTotalCuota($idcredito,$total,$total_interes,$t);


	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	
    actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	
	}	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	 
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
		
	}
			
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito + $deudaInteres;
	$total=$deudaActua;
	
	 actualizarTotalCuota($idcredito,$total,0,$MontoConDescuento);
	
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



$totalInteres=$totalInteres+$totalPagoInteres+$total_interes;
$TotalInteresActual=$TotalInteresActual+$total_interes;
$deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$diff;

//                                                                                        Este agregue yo ahora  y tambien agregue en el td
$DeudaInteres=$total_interes  ;
if($DeudaInteres<=0){
	$DeudaInteres=0;
}
$TotalDeuda=$DeudaInteres+ ($Monto-($totalPagoCredito+$descuento)) + $deudaInteres;

$DeudaInteres=$total_interes + $deudaInteres;

if($TotalDeuda<=0){
	$TotalDeuda=0;
}
if($TotalDeuda!=0){
if($ControlPago==0){
	$PoderPAgar="SI";
}else{
	$PoderPAgar="NO";
}

$ControlPago=$ControlPago+1;

}

$pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' style='$stylecolor' >
<tr id='tbSelecRegistro' onclick='obtenerdatoscuotero(this)'   name='pagoscuenta_".$cod_venta."'>
<td id='td_1' style='display:none' >".$idcredito."</td>
<td id='td_2' style='width:5%'  class='td_search'>".$plazo."</td>
<td id='td_3' style='width:15%' class='td_search'>".$fechapago."</td>
<td id='td_4' style='display:none'>".$cod_venta."</td>
<td id='td_10' style='display:none'>". number_format($MontoConDescuento,'0',',','.')."</td>
<td id='td_11' style='display:none'>". number_format($DeudaInteres,'0',',','.')."</td>
<td id='td_5' style='display:none'>". number_format($Monto,'0',',','.')."</td>
<td id='td_9' style='width:10%' class='td_search'>". number_format($TotalDeuda,'0',',','.')."</td>
<td id='td_6' style='display:none' class='td_search'>".$Esado."</td>
<td id='td_7' style='display:none'>".$Nro_recibo."</td>
<td id='td_8' style='width:5%'>".$diff2."</td>
<td id='td_12' style='display:none'>".number_format($totalPago,'0',',','.')."</td>
<td id='td_13' style='display:none'>".$PoderPAgar."</td>
</tr>
</table>
";











}
}
 mysqli_close($mysqli); 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalPagado,'0',',','.'),"4" => number_format($totalInteres,'0',',','.'));
echo json_encode($informacion);	
exit;
}



function buscarDiasAtra($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select datediff(cr.fechapago,'".$fechahoy."') as diff from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' and (cr.Monto - cr.descuento )>IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) order by cr.fechapago asc limit 1";/*Sentencia para buscar registros*/
 



 
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
 mysqli_close($mysqli); 
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



$totalpago = utf8_encode($valor['totalPago']);/*Obtenemos el registro mediante el nombre del atributo */      




}
}
 mysqli_close($mysqli); 
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
 

function  buscarDetalleProductos($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select telefono from persona where cod_persona=cod_clienteFK) as clientetelefono,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0) as nroDevoluciones,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0) as nroCambios,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0) as nroGarantia,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";

$clientenombre = ""; 
$clientedireccion = ""; 
$clientetelefono = ""; 
$nrodocliente = ""; 

$pagina = "";   
$paginarecibo = "";      
$paginatickect = "";      
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
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
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;

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
$nroDevoluciones = utf8_encode($valor['nroDevoluciones']); 
$nroCambios = utf8_encode($valor['nroCambios']); 
$nroGarantia = utf8_encode($valor['nroGarantia']); 
$impuesto = utf8_encode($valor['impuesto']); 
$clientenombre = utf8_encode($valor['clientenombre']); 
$clientedireccion = utf8_encode($valor['clientedireccion']); 
$clientetelefono = utf8_encode($valor['clientetelefono']); 
$nrodocliente = utf8_encode($valor['nrodocliente']); 
$plazo = utf8_encode($valor['plazo']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 

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
if($estado=='Garantia'){
		$styleG="color:#000"; 
$eventos="obtenerdatosabmGarantia(this)";
	$nombre_producto=$nombre_producto." (EN GARANTIA)";
}
if($nroDevoluciones>0){
	$eventos="";
	$nombre_producto=$nombre_producto." (DEVUELTO)";
	
	$styleDetalle="background-color:#FFEB3B;color:#000;"; 
}
if($nroCambios>0){
	$eventos="";
	$nombre_producto=$nombre_producto." (CAMBIADO)";
	$styleDetalle="background-color:#FFEB3B;color:#000;"; 
}

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' style='$styleDetalle'  '>
<td  id='td_datos_1' style='width:18%;".$styleG."'>".$nombre_producto."</td>
<td  id='td_datos_4' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}

function  buscarDetalleProductosOffline($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select telefono from persona where cod_persona=cod_clienteFK) as clientetelefono,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as nrodocliente,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0) as nroDevoluciones,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0) as nroCambios,
IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0) as nroGarantia,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";

$clientenombre = ""; 
$clientedireccion = ""; 
$clientetelefono = ""; 
$nrodocliente = ""; 

$pagina = "";   
$paginarecibo = "";      
$paginatickect = "";      
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
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
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;

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
$nroDevoluciones = utf8_encode($valor['nroDevoluciones']); 
$nroCambios = utf8_encode($valor['nroCambios']); 
$nroGarantia = utf8_encode($valor['nroGarantia']); 
$impuesto = utf8_encode($valor['impuesto']); 
$clientenombre = utf8_encode($valor['clientenombre']); 
$clientedireccion = utf8_encode($valor['clientedireccion']); 
$clientetelefono = utf8_encode($valor['clientetelefono']); 
$nrodocliente = utf8_encode($valor['nrodocliente']); 
$plazo = utf8_encode($valor['plazo']); 
$detalleproducto = utf8_encode($valor['detalleproducto']); 

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
if($estado=='Garantia'){
		$styleG="color:#000"; 
$eventos="obtenerdatosabmGarantia(this)";
	$nombre_producto=$nombre_producto." (EN GARANTIA)";
}
if($nroDevoluciones>0){
	$eventos="";
	$nombre_producto=$nombre_producto." (DEVUELTO)";
	
	$styleDetalle="background-color:#FFEB3B;color:#000;"; 
}
if($nroCambios>0){
	$eventos="";
	$nombre_producto=$nombre_producto." (CAMBIADO)";
	$styleDetalle="background-color:#FFEB3B;color:#000;"; 
}

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' style='$styleDetalle'  '>
<td  id='td_datos_1' style='width:18%;".$styleG."'>".$nombre_producto."</td>
<td  id='td_datos_4' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
</tr>
</table>";


}
}

return $pagina;
}


/*Buscar */
function buscarDetallePagosrealizados($buscar)
{
$mysqli=conectar_al_servidor();
$paginatickect=buscar_detalles_venta($buscar);
$sql= "select cr.plazo,pg.idPago,pg.Fecha,sum(pg.Monto) as Monto,pg.nrofactura,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0) as totalPagoCredito,pg.cod_creditoFK,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres,
((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)) as controlpago,
(Select nombre_persona from persona where cod_persona=pg.cod_cobradorFK) as cobradornombre
 from pago pg inner join credito cr on cr.idcredito=pg.cod_creditoFK  where pg.cod_venta_fk='$buscar' group by pg.nrofactura,pg.Fecha,pg.cod_creditoFK order by pg.idPago asc ";
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idPago = utf8_encode($valor['idPago']);    
$Monto = utf8_encode($valor['Monto']);      
$Fecha = utf8_encode($valor['Fecha']);      
$nrofactura = utf8_encode($valor['nrofactura']);      
$cobradornombre = utf8_encode($valor['cobradornombre']);      
$plazo = utf8_encode($valor['plazo']);      
$totalPagoCredito = utf8_encode($valor['totalPagoCredito']);      
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);      
$controlpago = utf8_encode($valor['controlpago']);      
$cod_creditoFK = utf8_encode($valor['cod_creditoFK']);      
$totalPagado=$Monto+$totalPagado;
 if($controlpago>0)	{
	$plazo='Pago Parcial en '.$plazo;
 }

$pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' >
<td id='td_1_".$cod_creditoFK.$Fecha."' style='display:none' >".$idPago."</td>
<td id='td_2_".$cod_creditoFK.$Fecha."' style='width:10%' >".$nrofactura."</td>
<td id='td_3_".$cod_creditoFK.$Fecha."'' style='width:10%' >".$Fecha."</td>
<td id='td_4_".$cod_creditoFK.$Fecha."' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_5_".$cod_creditoFK.$Fecha."' style='display:none'>".$cobradornombre."</td>
<td id='td_6_".$cod_creditoFK.$Fecha."' style='display:none'>".$paginatickect[0]."</td>
<td id='td_7_".$cod_creditoFK.$Fecha."' style='display:none'>".$plazo."</td>
<td id='td_8_".$cod_creditoFK.$Fecha."' style='display:none'>". number_format($totalPagoCredito,'0',',','.')."</td>
<td id='td_9_".$cod_creditoFK.$Fecha."' style='display:none'>". number_format($totalPagoInteres,'0',',','.')."</td>
<td  style='width:5%'><img src='/GoodVentaElim/iconos/imprimir.png' style='width:20px' onclick='ReimprimirDiv(this)' id='".$cod_creditoFK."".$Fecha."' /></td>
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
function buscarDetallePagosrealizadosoffline($buscar)
{
$mysqli=conectar_al_servidor();
$paginatickect=buscar_detalles_venta($buscar);
$sql= "select idPago,Fecha,Monto,nrofactura,
(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre
 from pago where cod_venta_fk='$buscar' group by nrofactura,Fecha ";
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idPago = utf8_encode($valor['idPago']);    
$Monto = utf8_encode($valor['Monto']);      
$Fecha = utf8_encode($valor['Fecha']);      
$nrofactura = utf8_encode($valor['nrofactura']);      
$cobradornombre = utf8_encode($valor['cobradornombre']);      
$totalPagado=$Monto+$totalPagado;
 	

$pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' >
<td id='td_1_".$nrofactura."' style='display:none' >".$idPago."</td>
<td id='td_2_".$nrofactura."' style='width:10%' >".$nrofactura."</td>
<td id='td_3_".$nrofactura."'' style='width:10%' >".$Fecha."</td>
<td id='td_4_".$nrofactura."' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_5_".$nrofactura."' style='display:none'>".$cobradornombre."</td>
<td id='td_6_".$nrofactura."' style='display:none'>".$paginatickect[0]."</td>
<td  style='width:5%'><img src='/GoodVentaElim/iconos/imprimir.png' style='width:20px' onclick='ReimprimirDiv(this)' id='$nrofactura' /></td>
</tr>
</table>
";


}
}

return $pagina;
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

ObtenerDatos($operacion);

?>
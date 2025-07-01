<?php
require("conexion.php");
include('quitarseparadormiles.php');
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



if($operacion=="nuevo" )
{


$cod_creditoFK=$_POST['cod_creditoFK'];
$cod_creditoFK = utf8_decode($cod_creditoFK);

$codAperturaApp=$_POST['codAperturaApp'];
$codAperturaApp = utf8_decode($codAperturaApp);

$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);

$Fecha=$_POST['Fecha'];
$Fecha = utf8_decode($Fecha);

$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);

$cuotanro=$_POST['cuotanro'];
$cuotanro = utf8_decode($cuotanro);

$totalInteres=$_POST['totalInteres'];
$totalInteres = quitarseparadormiles($totalInteres);

$nrorecibo=$_POST['nrorecibo'];
$nrorecibo = utf8_decode($nrorecibo);

$totalDeudaCuota=$_POST['totalDeudaCuota'];
$totalDeudaCuota = quitarseparadormiles($totalDeudaCuota);

$cod_cobradorFK=$user;
$lot=$_POST['lot'];
$lot = utf8_decode($lot);
$lat=$_POST['lat'];
$lat = utf8_decode($lat);

insertarpago($codAperturaApp,$cod_creditoFK,$Monto,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$cuotanro,$nrorecibo,$lot,$lat,$operacion);

}

if($operacion=="cargarpago" )
{

$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);



$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);

$nrorecibo=$_POST['nrorecibo'];
$nrorecibo = utf8_decode($nrorecibo);

$Fecha=$_POST['fecha'];
$Fecha = utf8_decode($Fecha);

$codAperturaApp=$_POST['codAperturaApp'];
$codAperturaApp = utf8_decode($codAperturaApp);

$cod_cobradorFK=$user;

$controlfecha="1";
if($Fecha==""){
	$Fecha=date("Y-m-d");
}
$lot=$_POST['lot'];
$lot = utf8_decode($lot);
$lat=$_POST['lat'];
$lat = utf8_decode($lat);
cargarpagos($codAperturaApp,$Monto,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$lot,$lat,$nrorecibo);

}
if($operacion=="cargarpagooffline" )
{

$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);



$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);

$Fecha=$_POST['fecha'];
$Fecha = utf8_decode($Fecha);

$cod_cobradorFK=$user;

$controlfecha="1";
if($Fecha==""){
	$Fecha=date("Y-m-d");
}

$lot=$_POST['lot'];
$lot = utf8_decode($lot);
$lat=$_POST['lat'];
$lat = utf8_decode($lat);
$nrorecibo=$_POST['nrorecibo'];
$nrorecibo = utf8_decode($nrorecibo);

cargarpagooffline($Monto,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$lot,$lat,$nrorecibo);

}

if($operacion=="buscarmisrecaudaciones" )
{

$fecha1=$_POST['buscar'];
MisRecaudaciones($fecha1,$user);

}

 


}

/*Funcion para insertar,modificar o eliminar registros*/
function insertarpago($codAperturaApp,$cod_creditoFK,$MontoCobrado,$Fecha,$cod_cobradorFK,$cod_venta,$totalDeudaCuota,$totalInteres,$cuotanro,$nrofactura,$lot,$lat,$operacion)
{
	
if($cod_creditoFK==""  || $totalDeudaCuota==""  || $Fecha=="" || $MontoCobrado==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}
$datosCredito=buscardatosdelcredito($cod_creditoFK);
$montocredito=$datosCredito[0];
$descuentocredito=$datosCredito[1];
$totalPagado=$datosCredito[2];
$totalpagacredito=$datosCredito[3];
$totalpagainteres=$datosCredito[4];
$montoInteres=0;
GuardarDeudaInteres("0",$cod_creditoFK);
$mysqli=conectar_al_servidor(); 
if($nrofactura==""){
$nrofactura=buscarnrofactura();
}
if($totalInteres>0){
// $totalDeudaCuotaControl=$totalDeudaCuota;
// if($totalDeudaCuotaControl>$MontoCobrado){
// $montocontrol=$montocredito-$totalpagacredito;
// $interescobrar=($MontoCobrado*$totalInteres)/$montocontrol;
// $pago=$interescobrar;
// $MontoCobrado=$MontoCobrado-$pago;	
// }else{
// $pago=$totalInteres;
// $MontoCobrado=$MontoCobrado-$pago;	
// }	
	

if($totalDeudaCuota>$MontoCobrado){
$pago=($MontoCobrado*30)/100;
$MontoCobrado=$MontoCobrado-$pago;
}else{
$pago=$totalInteres;
$MontoCobrado=$MontoCobrado-$pago;	
}	


	
if($pago>0){
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,codAperturaApp)
values($cod_creditoFK,$pago,'$Fecha',$cod_cobradorFK,$cod_venta,(select comision from venta where cod_venta='$cod_venta'),'$nrofactura','Interes','$codAperturaApp')";

$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
}
}





$totalDeudaCuota=$montocredito-$totalpagacredito;

if($totalDeudaCuota>0 && $MontoCobrado>0){
	
if($MontoCobrado>=$totalDeudaCuota){
	$Montopagado=$totalDeudaCuota;
}else{
	$Montopagado=$MontoCobrado;
}


if($Montopagado>0){
	
$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo,codAperturaApp)
values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Pago Cuota','$codAperturaApp')";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$cod_creditoFK,$Montopagado,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

}

}



// $totalPagado=buscartotalpago($cod_creditoFK);

// $mysqli=conectar_al_servidor(); 

// if($operacion=="nuevo") 
// {

// if($MontoCobrado>=$totalDeudaCuota){
	// $Montopagado=$totalDeudaCuota;
// }else{
	// $Montopagado=$MontoCobrado;
// }
// if($nrofactura==""){
	// $nrofactura=buscarnrofactura();
// }

// if($totalInteres>0){
// $control=$Montopagado-$totalInteres;
// if($control<=0){
// $pago=$Montopagado;
// $Montopagado=0;
// }else{
// $pago=$totalInteres;
// $Montopagado=$Montopagado-$totalInteres;
// }
			  

	
// $consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo)
// values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Interes')";
// $stmt1 = $mysqli->prepare($consulta1);
// $ss='ssssss';
// $stmt1->bind_param($ss,$cod_creditoFK,$pago,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura);

// if (!$stmt1->execute()) {
	
// echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
// exit;

// }
// }

// if($Montopagado>0){
	
// $consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision,nrofactura,tipo)
// values(?,?,?,?,?,(select comision from venta where cod_venta='$cod_venta'),?,'Pago Cuota')";
// $stmt1 = $mysqli->prepare($consulta1);
// $ss='ssssss';
// $stmt1->bind_param($ss,$cod_creditoFK,$Montopagado,$Fecha,$cod_cobradorFK,$cod_venta,$nrofactura);

// if (!$stmt1->execute()) {
	
// echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
// exit;

// }

// }

// }

$paginaticket=buscar_detalles_venta($cod_venta);
$datosTicket=calcularintereses($cod_venta,0,0,"2","2","2","no");
$totalDescuento=$datosTicket[0];
$totalInteres=$datosTicket[12];
$totalpagado=$datosTicket[3];
$totalDeuda=$datosTicket[4];
 $totalDeuda=$datosTicket[4];
 $totalVenta=$datosTicket[11];
 $InteresActual=$datosTicket[10];
 $totalsininteres=$datosTicket[7];
//addMasCuotas($cod_venta,$totalPagado);



$informacion =array("1" => "exito","2" =>number_format($totalpagado,'0',',','.') ,"3" =>  number_format($totalVenta,'0',',','.') ,"4" =>  number_format($totalDeuda,'0',',','.'),"5"=> $paginaticket
,"6"=> $cuotanro,"7"=> $nrofactura,"8"=>number_format($totalVenta,'0',',','.')
,"11"=> number_format($totalInteres,'0',',','.') ,"12"=> number_format($totalDeuda,'0',',','.')  ,"13"=> number_format($totalpagado,'0',',','.') 
,"14"=> number_format($totalDescuento,'0',',','.')  ,"15"=> number_format($InteresActual,'0',',','.') ,"16"=> number_format($totalsininteres,'0',',','.'));
echo json_encode($informacion);	
exit;

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


function cargarpagos($codAperturaApp,$pagado,$Fecha,$cod_cobradorFK,$cod_venta,$controlfecha,$lot,$lat,$nrofactura){
	
	$mysqli=conectar_al_servidor();
	
	if($nrofactura==""){
	$nrofactura=buscarnrofactura();
}


	$sql= "Select Monto,idcredito,cr.fechapago,total,plazo,(totalinteres + cr.deudaInteres) as totalinteres,cr.descuento,
	(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
	(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and tipo='Pago Cuota'),0) as totalPago,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and tipo='Interes'),0) as totalPagoInteres
	from credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
	where cr.cod_venta='$cod_venta' and IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and tipo='Pago Cuota'),0) < (cr.Monto-cr.descuento) order by cr.idcredito asc";
		  
$clientenombre="";
$nrodocliente="";
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro=$valor;
 $montoDescuento=0;
 

 $plazo='';
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     $idcredito=$valor['idcredito'];
			  $Monto=utf8_encode($valor['Monto']);
			  $totalinteres=utf8_encode($valor['totalinteres']);
			  $totalPago=utf8_encode($valor['totalPago']);
			  $clientenombre=utf8_encode($valor['clientenombre']);
			  $nrodocliente=utf8_encode($valor['nrodocliente']);
			  $descuento=utf8_encode($valor['descuento']);
			  $totalpagainteres=utf8_encode($valor['totalPagoInteres']);
			  if($totalinteres>0){
							  
			  
			  
			  				  
			//$totaldeudacontrol=($Monto+$totalinteres)-($totalPago+$descuento+$montoDescuento);	
			
	 
// if($totaldeudacontrol>($pagado+$montoDescuento)){
// $deudacuota=$Monto-$totalPago;
// $interescobrar=($pagado*$totalinteres)/$deudacuota;
// $pago=$interescobrar;
// $pagado=$pagado-$pago;
// }else{
// $pago=$pagado;
// $pagado=$pagado-$pago;	
// }
		
if($totalinteres>=($pagado+$descuento)){
										
if($totalinteres>=$pagado){
	$interescobrar=$pagado;
	
	$MontoInteresGuardar=$totalinteres-$pagado;
	
	if($pagado!=0){
	GuardarDeudaInteres($MontoInteresGuardar,$idcredito);
	}
	 $plazo.="Interes en Cuota ".utf8_encode($valor['plazo']);
}else{
	$interescobrar=$totalinteres;
	GuardarDeudaInteres("0",$idcredito);
}
		

$pago=$interescobrar;
$pagado=$pagado-$pago;
}else{
$pago=$totalinteres;
$pagado=$pagado-$pago;			
					
					
					
               // $pago=($pagado*30)/100;
               // $pagado=$pagado-$pago;
              // }else{
              // $pago=$totalinteres;
              // $pagado=$pagado-$pago;	
               }
			  
			    cargarPagosDeudas($pago,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$lot,$lat,$nrofactura,"Interes",$codAperturaApp);
			   $totalPago=$totalPago+$pago;
				  
			  }
			   $deuda=($Monto+$totalinteres)-($totalPago+$descuento+$montoDescuento);
				$c=1;
			 if($pagado<=0){
				  $c=0;
				  $pago=0;
			  }
			  $control=$pagado-$deuda;
			  if($control<=0){
				 $pago=$pagado;
				 $pagado=0;
			  }else{
				  $pago=$deuda;
				  $pagado=$pagado-$deuda;
			  }
			  if($controlfecha=="2"){
				  $Fecha=utf8_encode($valor['fechapago']);
			  }
			  
					if($pago>0 && $c==1){
						GuardarDeudaInteres("0",$idcredito);
						 cargarPagosDeudas($pago,$Fecha,$cod_cobradorFK,$idcredito,$cod_venta,$lot,$lat,$nrofactura,"Pago Cuota",$codAperturaApp);
						
						 if(($pago+$montoDescuento)>=$deuda){
							 if($plazo!=""){
							 $plazo.=", ".utf8_encode($valor['plazo']);
							 }else{
							 $plazo.=utf8_encode($valor['plazo']);
							 }
						 }else{
							 if($plazo!=""){
							 $plazo.=" y pago parcial en cuota ".utf8_encode($valor['plazo'])." ";
							 }else{
							$plazo.="Pago parcial en cuota ".utf8_encode($valor['plazo'])." ";
							 }
							
							 
						 }
						  
					}		 
				 
			 
			  
			  
	  }
 }
 
$plazo = buscarpagosTitulo($cod_venta,$nrofactura);

$plazo2= $plazo[0];

$paginaticket=buscar_detalles_venta($cod_venta);
$datosTicket=calcularintereses($cod_venta,0,0,"2","2","2","no");
$totalDescuento=$datosTicket[0];
$totalInteres=$datosTicket[12];
$totalpagado=$datosTicket[3];
$totalDeuda=$datosTicket[4];
$deudatotal=$datosTicket[4];
$deudaActual=$datosTicket[8];
 $totalVenta=$datosTicket[11];
 $InteresActual=$datosTicket[14];
 $totalsininteres=$datosTicket[7];
//addMasCuotas($cod_venta,$totalPagado);

$informacion =array("1" => "exito","2" =>number_format($totalpagado,'0',',','.') ,"3" =>  number_format($totalVenta,'0',',','.') ,"4" =>  number_format($totalDeuda,'0',',','.'),"5"=> $paginaticket
,"6"=> $plazo2,"7"=> $nrofactura,"8"=>number_format($totalVenta,'0',',','.')
,"11"=> number_format($totalInteres,'0',',','.') ,"12"=> number_format($deudatotal,'0',',','.')  ,"13"=> number_format($totalpagado,'0',',','.') 
,"14"=> number_format($totalDescuento,'0',',','.')  ,"15"=> number_format($InteresActual,'0',',','.') 
,"16"=> number_format($totalsininteres,'0',',','.'),"17"=> number_format($deudaActual,'0',',','.'));
echo json_encode($informacion);	
exit;

		
}



function buscarpagosTitulo($CodVenta,$NroFactura)
{
$mysqli=conectar_al_servidor();


$sql= "select cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,vt.TipoVenta,vt.total_venta
 from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk
 inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where pg.cod_venta_fk='$CodVenta' and pg.nrofactura='$NroFactura' order by pg.idPago  ";
 
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
     
$plazo = utf8_encode($valor['plazo']);      
$tipo = utf8_encode($valor['tipo']);  
$Monto = utf8_encode($valor['Monto']);  

$totalPagado=$Monto+$totalPagado;
$pagina.="<table style='font-family: arial;font-size: 11px;' >
<tr>
<td style='width:10%'>".$plazo."</td>
<td style='width:50%'>".$tipo."</td>
<td style='width:40%'>".number_format($Monto,'0',',','.')."</td>
</tr>
</table>";

}
}
$datos[0]=$pagina;
$datos[1]=$totalPagado;
return $datos;	

}





function buscarnrofactura()
{
	
	
	$mysqli=conectar_al_servidor();
	 $sql= "Select count(cod_creditoFK) from pago ";
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$NroFactura=$result->fetch_row();
  $NroFactura=$NroFactura[0];
  
 if($NroFactura<10){
	 $NroFactura="0000".$NroFactura;
 }else{
 if($NroFactura<100){
	 $NroFactura="000".$NroFactura;
 }else{
	 if($NroFactura<1000){
	 $NroFactura="00".$NroFactura;
    } 
 }
 }
  mysqli_close($mysqli); 
  
 return $NroFactura;


}

function  cargarPagosDeudas($Monto,$Fecha,$cod_cobradorFK,$cod_creditoFK,$cod_venta,$lot,$lat,$nrofactura,$tipo,$codAperturaApp){
	  
	  
	  if($Monto!="0"){
	$mysqli=conectar_al_servidor();
	$consulta="Insert into pago (tipo,Monto,Fecha,cod_creditoFK,cod_cobradorFK,cod_venta_fk,comision,lot,lat,nrofactura,codAperturaApp) 
	values('$tipo','$Monto','$Fecha','$cod_creditoFK','$cod_cobradorFK','$cod_venta',(select comision from venta where cod_venta='$cod_venta'),'$lot','$lat','$nrofactura','$codAperturaApp')";	
	

	
	$stmt = $mysqli->prepare($consulta);
	

if ( ! $stmt->execute()) {
   /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	  }

}


/*Buscar */
function buscartotalpago($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select sum(pg.Monto) as totalpago from pago pg inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where cr.cod_venta='$buscar'";/*Sentencia para buscar registros*/
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

return $totalpago;
}

/*Buscar */
function buscartotalventa($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select (total_venta-descuento) as totalVenta from venta where cod_venta='$buscar'";
$totalVenta = 0;   
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



$totalVenta = utf8_encode($valor['totalVenta']);




}
}

return $totalVenta;
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
$cantidad_detalle = utf8_decode($valor['cantidad_detalle']);      
$precio_producto = utf8_decode($valor['precio_producto']);      
$subtotal = utf8_decode($valor['subtotal']);      
if($nroDevoluciones==0 && $nroCambios==0){
  $pagina.="<table class='tableTicket'>
<tr>
<td style='width:100%'>".$nombre_producto."</td>
</tr>
</table>";
$pagina.="<table class='tableTicket'>
<tr>
<td style='width:33%'>".number_format($cantidad_detalle,'0',',','.')."</td>
<td style='width:33%'>".number_format($precio_producto,'0',',','.')."</td>
<td style='width:33%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";
}

}
}

return $pagina;
}


/*Buscar */
function MisRecaudaciones($fecha1,$cobradorarqueo)
{
$mysqli=conectar_al_servidor();

	
$sql= "select idPago,Fecha,Monto,cod_venta_fk,
(Select nombre_persona from persona where cod_persona=(select cod_clienteFK from venta where venta.cod_venta=cod_venta_fk)) as cliente,
(Select num_factura From venta vt where vt.cod_venta=cod_venta_fk) as num_factura,
(Select plazo from credito l where l.idcredito=pg.cod_creditoFK) as plazo,
(Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cod_venta_fk)) as nombrezona
 from pago pg where pg.Monto>0 and cod_cobradorFK='$cobradorarqueo' and  Fecha='$fecha1' ";/*Sentencia para buscar registros*/	
	




 $paginacuota = "";   
 $paginaentrega = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cliente = utf8_encode($valor['cliente']);    
$num_factura = utf8_encode($valor['num_factura']);    
$Monto = utf8_encode($valor['Monto']);      
$Fecha = utf8_encode($valor['Fecha']);   
$plazo = utf8_encode($valor['plazo']);   
 	$totalPagado=$totalPagado+$Monto;
if($plazo!="ENTREGA"){
$paginacuota.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' name='tdRecaudacionCargada' >
<td id='td_1' style='width:25%' class='td_search'>".$cliente."</td>
<td id='td_2' style='width:25%' class='td_search'>".$num_factura."</td>
<td id='td_3' style='width:25%' class='td_search'>".$Fecha."</td>
<td id='td_4' style='width:25%' class='td_search'>". number_format($Monto,'0',',','.')."</td>
</tr>
</table>";
}else{
	$paginaentrega.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' name='tdRecaudacionCargada' >
<td id='td_1' style='width:25%' class='td_search'>".$cliente."</td>
<td id='td_2' style='width:25%' class='td_search'>".$num_factura."</td>
<td id='td_3' style='width:25%' class='td_search'>".$Fecha."</td>
<td id='td_4' style='width:25%' class='td_search'>". number_format($Monto,'0',',','.')."</td>
</tr>
</table>";
}

}
}

$pagina="";

if($paginaentrega!="" && $paginacuota!=""){
	$pagina="<p class='ptituloZ'>Cobros de Entregas</p>".$paginaentrega."<p class='ptituloZ'>Cobros de Cuotas</p>".$paginacuota;
}
if($paginaentrega!="" && $paginacuota==""){
	$pagina="<p class='ptituloZ'>Cobros de Entregas</p>".$paginaentrega;
}
if($paginaentrega=="" && $paginacuota!=""){
	$pagina="<p class='ptituloZ'>Cobros de Cuotas</p>".$paginacuota;
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.'),"4"=>$nroRegistro );
echo json_encode($informacion);	
exit;
}

function addMasCuotas($cod_venta,$totalPago){
	
	$datosVenta=buscardatosventa($cod_venta);
	echo $datosVenta[16]." - ".$datosVenta[20];
	exit;
	if($totalPago<$datosVenta[1] && $datosVenta[16]>$datosVenta[20]){
	$pendiente=$datosVenta[1]-$totalPago;
	
	
	$fechaInicio=date("Y-m-d");
	$controlPago=0;
	$F=0;
	while($controlPago<=0)
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
	
	echo $cuotaSobrante;
	exit;
	if($controlPago==0){
		//insertarcuotas(($datosVenta[16]+1)."/".($datosVenta[16]+1),$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ")
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
	 
		$sql= "Select fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,
		(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
		(Select nombre_persona from persona where cod_persona=cod_cobradorFK) as cobradornombre,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
		(Select count(fechapago) from credito where cod_venta=vt.cod_venta and Monto>=IFNULL((select count(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) ) as cuotasCobradas,
		IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
		IFNULL((select count(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as nropagos
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
		  	  $datosVenta[1]=utf8_encode($valor['total_venta']);
		  	  $datosVenta[2]=utf8_encode($valor['cod_usuarioFK']);
		  	  $datosVenta[3]=utf8_encode($valor['cod_clienteFK']);
		  	  $datosVenta[4]=utf8_encode($valor['num_factura']);
		  	  $datosVenta[5]=utf8_encode($valor['cod_cobradorFK']);
		  	  $datosVenta[6]=utf8_encode($valor['TipoVenta']);
		  	  $datosVenta[7]=utf8_encode($valor['TipoPago']);
		  	  $datosVenta[8]=utf8_encode($valor['Vendedor1']);
		  	  $datosVenta[9]=utf8_encode($valor['Vendedor2']);
		  	  $datosVenta[10]=utf8_encode($valor['usuarionombre']);
		  	  $datosVenta[11]=utf8_encode($valor['clientenombre']);
		  	  $datosVenta[12]=utf8_encode($valor['cod_venta']);
		  	  $datosVenta[13]=utf8_encode($valor['cobradornombre']);
		  	  $datosVenta[14]=utf8_encode($valor['nombrevendedor1']);
		  	  $datosVenta[15]=utf8_encode($valor['nombrevendedor2']);
		  	  $datosVenta[16]=utf8_encode($valor['cantidadcuota']);
		  	  $datosVenta[17]=utf8_encode($valor['Monto']);
		  	  $datosVenta[18]=utf8_encode($valor['nropagos']);
		  	  $datosVenta[20]=utf8_encode($valor['cuotasCobradas']);
		
		  	 
		
			  
			  
	  }
 }
 
 
return $datosVenta;
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
	
$sql= "select  pg.cod_venta_fk 
 from  pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk 
 where ".$condicion;


 $datos[0] = "";   
 
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



 $datos[0]= utf8_encode($valor['cod_venta_fk']);    



}
}
return $datos[0];
}





function actualizarTotalVenta($totalinteres, $acobrar, $deudaactual, $fechaactualizacion, $cod_ventaFk){
	
$mysqli=conectar_al_servidor(); 

$consulta1="delete from totalesdeudaventa where cod_ventaFk=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$cod_ventaFk); 
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$consulta1="insert into totalesdeudaventa (totalinteres, acobrar, deudaactual, fechaactualizacion, cod_ventaFk) values (?,?,?,?,?)";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$totalinteres, $acobrar, $deudaactual, $fechaactualizacion, $cod_ventaFk); 
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

 mysqli_close($mysqli);   

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


$Monto = utf8_encode($valor['Monto']);     
$descuento = utf8_encode($valor['descuento']);  
$totalPago = utf8_encode($valor['totalPago']);  
$totalPagoCredito = utf8_encode($valor['totalPagoCredito']);  
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);  


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




ObtenerDatos($operacion);

?>
<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include('quitarseparadormiles.php');
//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
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
$informacion =array("1" => "UI");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
}



	
	
	$idclienteVenta=$_POST['idclienteVenta'];
$idclienteVenta = utf8_decode($idclienteVenta);
$idCobradorVenta=$_POST['idCobradorVenta'];
$idCobradorVenta = utf8_decode($idCobradorVenta);
$idvendedor1=$_POST['idvendedor1'];
$idvendedor1 = utf8_decode($idvendedor1);
	$cod_cobrador=$_POST['cod_cobrador'];
$cod_cobrador = utf8_decode($cod_cobrador);
	$idvendedor2=$_POST['idvendedor2'];
$idvendedor2 = utf8_decode($idvendedor2);
$nrocuota=$_POST['nrocuota'];
$nrocuota = utf8_decode($nrocuota);
$nroventa=$_POST['nroventa'];
$nroventa = utf8_decode($nroventa);
$entrega=$_POST['entrega'];
$entrega = quitarseparadormiles($entrega);
$total=$_POST['total'];
$total = quitarseparadormiles($total);
$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);
$totalb=$_POST['totalb'];
$totalb = quitarseparadormiles($totalb);
$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$tipoventa=$_POST['tipoventa'];
$tipoventa = utf8_decode($tipoventa);
$metodo=$_POST['metodo'];
$metodo = utf8_decode($metodo);
$lot=$_POST['lot'];
$lat=$_POST['lat'];
$fecha_venta='current_date';
$codventa=abm($fecha_venta,"42",$idclienteVenta,$nroventa,$cod_cobrador,$tipoventa,$metodo,$idvendedor1,$idvendedor2,"10",$total);
$nrodetalle=$_POST['nrodetalle'];
$nrodetalle = utf8_decode($nrodetalle);
$EntregaCobrado=$_POST['EntregaCobrado'];//Utilizado como monto pagado
$EntregaCobrado = quitarseparadormiles($EntregaCobrado);
insertardetalle($codventa,$nrodetalle);
if($tipoventa=="CREDITO"){
	generarCuotas($codventa,$monto,$metodo,$fecha,$nrocuota,$totalb,$entrega,$EntregaCobrado,$lot,$lat);

}else{
	abmcontado($codventa);
}






	


}

function abm($fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$total_venta)
{
	
if($fecha_venta=="" || $cod_usuarioFK=="" || $cod_clienteFK==""|| $cod_cobradorFK=="" || $comision==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

$consulta1="Insert into venta (fecha_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2,comision,total_venta)
values(CURRENT_DATE(),?,?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comision,$total_venta);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

$cod_venta=obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura);
return $cod_venta;

	
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



function insertardetalle($cod_ventaFK,$nro_total)
{
	

$mysqli=conectar_al_servidor(); 

$nro_carga=0;
		
		while($nro_carga<$nro_total){
		
		 
       $idlistado = $_POST['idlistado'.$nro_carga];
       $cod_productoFK = $_POST['codProduc'.$nro_carga];
       $cant = $_POST['cant'.$nro_carga];
       $precio = $_POST['precio'.$nro_carga];
       $comision = $_POST['comision'.$nro_carga];
       $costo = $_POST['costo'.$nro_carga];
       
$cantidad_detalle = quitarseparadormiles($cant);
$precio_producto = quitarseparadormiles($precio);
$comision = quitarseparadormiles($comision);
$subPrecioCompra = quitarseparadormiles($costo);
$subtotal=$cantidad_detalle*$precio_producto;
	   
		


$consulta1="Insert into detalle_venta (cantidad_detalle,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado,comision)
values(?,?,?,?,?,?,'Activo',?)";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$cantidad_detalle,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$comision);

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
editar_cantidad($cod_productoFK,$cantidad_detalle,"resta");
editarcantidadlistado($idlistado,$cantidad_detalle);
$nro_carga=$nro_carga+1;

		}


	
}

function editar_cantidad($idproductos,$cantidad,$t){
      
	  $mysqli=conectar_al_servidor(); 

	    if($t=="resta"){
			 $consulta="Update producto set stock_producto=(stock_producto-$cantidad)  where cod_producto='".$idproductos."'";
		// if((obtenerStockActual($idproductos)-$cantidad)>0){
			   // $consulta="Update producto set stock_producto=(stock_producto-$cantidad)  where cod_producto='".$idproductos."'";
		   // }else{
			   // $consulta="Update producto set stock_producto=0  where cod_producto='".$idproductos."'";	
		   // }
				

	}else{
		 $consulta="Update producto set stock_producto=(stock_producto+$cantidad)  where cod_producto='".$idproductos."'";	
           // if(obtenerStockActual($idproductos)>0){
			   // $consulta="Update producto set stock_producto=(stock_producto+$cantidad)  where cod_producto='".$idproductos."'";	
		   // }else{
			   // $consulta="Update producto set stock_producto=$cantidad  where cod_producto='".$idproductos."'";	
		   // }
			

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


function editarcantidadlistado($idlistado,$cantidad){
      
	  $mysqli=conectar_al_servidor(); 

			   $consulta="Update listado set cantvendido=(cantvendido+$cantidad)  where idlistado='".$idlistado."'";	
		 
	
	
	$stmt = $mysqli->prepare($consulta);
	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


    }

function abmcontado($cod_venta)
{
	

if($cod_venta==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}
$datosventa=buscardatosventa($cod_venta);



$mysqli=conectar_al_servidor(); 


	$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo)
			values('Contado','$datosventa[0]','$cod_venta','$datosventa[1]','Pendiente','0')";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}



$consulta1="Insert into pago (cod_creditoFK,Monto,Fecha,cod_cobradorFK,cod_venta_fk,comision)
values((select idcredito from credito where cod_venta='$cod_venta' and plazo='Contado' limit 1),?,?,?,?,?)";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$datosventa[1],$datosventa[0],$datosventa[5],$cod_venta,$datosventa[18]);/*Se cargar los paramentros a la sentencia preparada*/


/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}



$totalPagado=buscartotalpago($cod_venta);
$totalVenta=buscartotalventa($cod_venta);

$paginaticket=buscar_detalles_venta($cod_venta);
$totalDeuda=$totalVenta-$totalPagado;
//addMasCuotas($cod_venta,$totalPagado);

$informacion =array("1" => "exito","2" =>number_format($totalPagado,'0',',','.') ,"3" =>  number_format($totalVenta,'0',',','.') ,"4" =>  number_format($totalDeuda,'0',',','.'),"5"=> $paginaticket);/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;

}

	
	

function generarCuotas($cod_venta,$Monto,$metodopago,$iniciopago,$nroCuota,$total,$entrega,$EntregaCobrado,$lot,$lat){
	
	
	$sobrante=0;
	$totalPagado=$EntregaCobrado;
	actualizarEntrega2($cod_venta,$entrega);
	 $a=0;
			 $F=0;
			 $fechaInicio=$iniciopago;
		     $cantidad=$nroCuota;
				$pendiente=$total;
				
			 while ($a<$cantidad){
		if($metodopago=="Mensual")	{
			$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
			$F=$F+1;
		}
		if($metodopago=="Semanal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			$F=$F+7;
		}
		if($metodopago=="Quincenal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			$F=$F+15;
		}
	
	$fecha=date("Y-m-d H:i:s",$fecha);
	 if($pendiente>$Monto){
	$cuotaSobrante=$pendiente-$Monto;
	$cuotaSobrante=$pendiente-$cuotaSobrante;
	}else{
	$cuotaSobrante=$pendiente;
	}
	
	insertarcuotas(($a+1)."/".$nroCuota,$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ");
	$pendiente=$pendiente-$cuotaSobrante;
	
				  $a++;
			 
			 }
			 
			 if($pendiente>0){
				 if($metodopago=="Mensual")	{
			$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
			$F=$F+1;
		}
		if($metodopago=="Semanal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			$F=$F+7;
		}
		if($metodopago=="Quincenal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			$F=$F+15;
		}
		$fecha=date("Y-m-d H:i:s",$fecha);
				 insertarcuotas(($a+1)."/".($nroCuota+1),$fecha, $cod_venta, $pendiente, "Pendiente"," ");
			 }
			 
	if($sobrante>0){
cargarpagos($sobrante,$cod_venta,$lot,$lat);
	}
			

$totalPagado=buscartotalpago($cod_venta);
$totalVenta=buscartotalventa($cod_venta);

$paginaticket=buscar_detalles_venta($cod_venta);
$totalDeuda=$totalVenta-$totalPagado;
//addMasCuotas($cod_venta,$totalPagado);

$informacion =array("1" => "exito","2" =>number_format($totalPagado,'0',',','.') ,"3" =>  number_format($totalVenta,'0',',','.') ,"4" =>  number_format($totalDeuda,'0',',','.'),"5"=> $paginaticket);/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;

	
	
}

function actualizarEntrega2($cod_venta,$entrega){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set pago=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$entrega,$cod_venta); 

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


}

function cargarpagos($pagado,$cod_venta,$lot,$lat){
	
	$mysqli=conectar_al_servidor();
	$sql= "Select Monto,idcredito,cr.fechapago,cr.plazo,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
	from credito cr
	where cr.cod_venta='$cod_venta' and IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) < Monto order by cr.idcredito asc";
		
		$plazo="";
   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
 /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     $idcredito=$valor['idcredito'];
			  $Monto=utf8_encode($valor['Monto']);
			  $totalPago=utf8_encode($valor['totalPago']);
			 
			  $deuda=$Monto-$totalPago;
			 
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
			
			  
					if($pago>0 && $c==1){
						 cargarPagosDeudas($pago,$idcredito,$cod_venta,$lot,$lat);
						  $plazo.=utf8_encode($valor['plazo'])." ";
					}		 
				 
			  
			  
			  
			  
	  }
 }


		
}
function  cargarPagosDeudas($Monto,$cod_creditoFK,$cod_venta,$lot,$lat){
	  
	$mysqli=conectar_al_servidor();
	$consulta="Insert into pago (Monto,Fecha,cod_creditoFK,cod_cobradorFK,cod_venta_fk,comision,lot,lat) 
	values('$Monto',CURRENT_DATE(),'$cod_creditoFK','(select cod_cobradorFK from venta where cod_venta='$cod_venta' limit 1)','$cod_venta',(select comision from venta where cod_venta='$cod_venta'),'$lot','$lat')";	
	
	
	
	$stmt = $mysqli->prepare($consulta);
	

if ( ! $stmt->execute()) {
   /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
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
	
function actualizarEntrega($plazo,$fechapago,$cod_venta,$Monto,$Esado,$Nro_recibo,$dias,$interes,$total,$entrega){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set pago=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$entrega,$cod_venta); 

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo)
			values('$plazo','$fechapago','$cod_venta','$Monto','$Esado','$Nro_recibo')";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


}
	
/*Buscar */
function buscartotalpago($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select IFNULL(sum(pg.Monto),0) as totalpago from pago pg inner join credito cr on cr.idcredito=pg.cod_creditoFK
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

return $totalVenta;
}


/*Buscar */
function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto from
 venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
 inner join producto pr on pr.cod_producto=dtv.cod_productoFK
 where vt.cod_venta='$buscar'";/*Sentencia para buscar registros*/
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



$nombre_producto = utf8_encode($valor['nombre_producto']);/*Obtenemos el registro mediante el nombre del atributo */      

  $pagina.="<table class='tableTicket'>
<tr>
<td style='width:100%'>".$nombre_producto."</td>
</tr>
</table>";


}
}

return $pagina;
}

	
function buscardatosventa($codVenta){
	$mysqli=conectar_al_servidor();
	 
		$sql= "Select fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2 ,cod_venta,comision,
		(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
		(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
		(Select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuarionombre,
		(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
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
		  	  $datosVenta[18]=utf8_encode($valor['comision']);
		
		  	 
		
			  
			  
	  }
 }
 
 
return $datosVenta;
}


	
verificar($operacion);
?>
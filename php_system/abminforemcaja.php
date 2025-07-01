<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
include("buscar_nivel.php");
include("BuscarNroFactura.php");
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




if($operacion=="buscarinformecaja")
{
	
$idArqeoFk=$_POST['idArqeoFk1'];
$idArqeoFk = utf8_decode($idArqeoFk);
generarinforme($idArqeoFk);

}



}

function generarinforme($idArqeoFk){
	$styleName="tableRegistroSearch";
	$pagina="";
	$datosventas=datosdepagosventas($idArqeoFk);
	if($datosventas[0]==""){
	$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>PAGOS REALIZADOS</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>PAGOS REALIZADOS</p>".$datosventas[0];
	}
	$totalpagos=$datosventas[1];
	$totaltarjeta=$datosventas[2];
	$totalefectivo=$datosventas[3];
	
	
$datosIngreso=datosdeIngreso($idArqeoFk);
if($datosIngreso[0]==""){
	
	$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>INGRESOS A CAJA</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>INGRESOS A CAJA</p>".$datosIngreso[0];
	}

$totalingreso=$datosIngreso[1];
	

$datosEgreso=datosdeEgresos($idArqeoFk);
	
	if($datosEgreso[0]==""){
		$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>EGRESOS DE CAJA</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>EGRESOS DE CAJA</p>".$datosEgreso[0];
	}
$totalegreso=$datosEgreso[1];

/////////////desde aca ya es desembolso

$datosDesembolso=datosdeDesembolso($idArqeoFk);
	
	if($datosDesembolso[0]==""){
		$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>DESEMBOLSOS A CLIENTE</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>DESEMBOLSOS A CLIENTE</p>".$datosDesembolso[0];
	}
$totalDesembolso=$datosDesembolso[1];




$montoinicio=ObtenerTotalCaja($idArqeoFk);

$ingresos=$totalingreso;
$egresos=$totalegreso;
$Desembolso=$totalDesembolso;
$total=($ingresos+$totalpagos)-($egresos+ $Desembolso);
$total=$montoinicio+$total;
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($ingresos,'0',',','.'),"4" => number_format($egresos,'0',',','.')
,"5" => number_format($total,'0',',','.'),"6" => number_format($totaltarjeta,'0',',','.'),"7" => number_format($totalefectivo,'0',',','.'),"8" => number_format($Desembolso,'0',',','.'));
echo json_encode($informacion);	
exit;
}



function datosdeDesembolso($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "SELECT concat('DESEMBOLSO - ',(select (select nombre_persona from persona where cod_clienteFK=cod_persona)
  from venta where cod_venta=cod_ventaFK )) as motivo , precio_producto  FROM detalle_venta where cod_aperturaCajaFK='$idArqeoFk' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalMonto=0;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		   
		  	  $precio_producto=utf8_encode($valor['precio_producto']);
		  	  $motivo=utf8_encode($valor['motivo']);  
		  	 $totalMonto=$totalMonto+$precio_producto;
		  	 
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px' >".$motivo."</td>
<td id='' style='width:20%'>". number_format($precio_producto,'0',',','.')."</td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $pagina;
 $datos[1]= $totalMonto;
 return $datos;
}



/*Buscar */
function datosdepagosventas($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select  sum(pg.Monto) as Monto ,tipo,cod_venta_fk,descripcion,pg.tipopago,
(Select Nombre from local l where l.cod_local=pg.codCaja) as nombrelocal,
(Select nombre_persona from persona pr where pr.cod_persona=vt.cod_clienteFK) as cliente 
 from  pago pg inner join venta vt on cod_venta=pg.cod_venta_fk 
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' group by nrofactura,cod_venta_fk asc  ";	


// echo($sql);
// exit;
 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$totaltarjeta=0;
$totalefectivo=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cliente = utf8_encode($valor['cliente']); 
$Monto = utf8_encode($valor['Monto']); 
$nombrelocal = utf8_encode($valor['nombrelocal']); 
$cod_venta_fk = utf8_encode($valor['cod_venta_fk']); 
$tipo = utf8_encode($valor['tipo']); 
$descripcion = utf8_encode($valor['descripcion']); 
$tipopago = utf8_encode($valor['tipopago']); 

	if($tipo=="Tarjeta"){
$totaltarjeta=$totaltarjeta+$Monto;
}else{
$totalefectivo=$totalefectivo+$Monto;	
} 
$totalPagado=$totalPagado+$Monto;
if($descripcion=="ventas"){
	$descripcion=buscar_detalles_venta($cod_venta_fk);
}
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px;line-height: 18px;' >".$descripcion."-".$cliente."</td>
<td id='' style='width:20%'>". number_format($Monto,'0',',','.')." &nbsp&nbsp(".$tipopago.")</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalPagado;
$datos[2]=$totaltarjeta;
$datos[3]=$totalefectivo;
return $datos;
}


function datosdeEgresos($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "Select monto,motivo,fecha,estado,cod_usuario,idgastos,personales,cod_local,
		(Select nombre_persona from persona where cod_persona=cod_usuario) as usuarionombre,
		(Select Nombre from local l where l.cod_local=g.cod_local ) as nombrelocal
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Egreso' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $monto=utf8_encode($valor['monto']);
		  	  $motivo=utf8_encode($valor['motivo']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $personales=utf8_encode($valor['personales']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	 $totalGasto=$totalGasto+$monto;
		  	 
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px' >".$motivo."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')."</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $pagina;
 $datos[1]= $totalGasto;
 return $datos;
}

function datosdeIngreso($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select monto,motivo,fecha,estado,cod_usuario,idgastos,personales,cod_local,
		(Select nombre_persona from persona where cod_persona=cod_usuario) as usuarionombre,
		(Select Nombre from local l where l.cod_local=g.cod_local ) as nombrelocal
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Ingreso' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $monto=utf8_encode($valor['monto']);
		  	  $motivo=utf8_encode($valor['motivo']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $personales=utf8_encode($valor['personales']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	 $totalGasto=$totalGasto+$monto;
			 
			 
		  	 	 	$styleName=CargarStyleTable($styleName);
					$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px' >".$motivo."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')."</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";
		
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $pagina;
 $datos[1]= $totalGasto;
 return $datos;
}

/*Buscar */
function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,dtv.detalleproducto
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



$nombre_producto = utf8_encode($valor['nombre_producto']);       
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']);       
$detalleproducto = utf8_encode($valor['detalleproducto']);       
$subtotal = utf8_decode($valor['subtotal']);      
if($pagina==""){
	$pagina.=$a.") &nbsp".$nombre_producto.",&nbsp&nbsp".number_format($cantidad_detalle,'2',',','.')."(".$detalleproducto.")";	
	}else{
		$pagina.="<br>".$a.") &nbsp".$nombre_producto.",&nbsp&nbsp".number_format($cantidad_detalle,'2',',','.')."(".$detalleproducto.")";	
	}


}
}

return $pagina;
}


function ObtenerTotalCaja($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "Select montoapertura
from arqueocaja  where idarqueocaja='$idArqeoFk'  ";
$montoapertura = "0";   
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
          
$montoapertura = utf8_encode($valor['montoapertura']);          

	    	 


}
}

return $montoapertura;
}



verificar($operacion);
?>
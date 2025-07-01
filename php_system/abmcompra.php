<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
include("buscar_nivel.php");
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
	
	
	$cod_compra=$_POST['cod_compra'];
$cod_compra = utf8_decode($cod_compra);
$fecha_compra=$_POST['fecha_compra'];
$fecha_compra = utf8_decode($fecha_compra);
	$cod_proveedorFK=$_POST['cod_proveedorFK'];
$cod_proveedorFK = utf8_decode($cod_proveedorFK);
	$num_comprobante=$_POST['num_comprobante'];
$num_comprobante = utf8_decode($num_comprobante);
	$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
	$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);
	$pagado1=$_POST['pagado1'];
$pagado1 = quitarseparadormiles($pagado1);
	$pagado2=$_POST['pagado2'];
$pagado2 = quitarseparadormiles($pagado2);
	abm($cod_compra,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2,$operacion);

}

if($operacion=="eliminarcompra")
{
	$idAbmCompra=$_POST['idAbmCompra'];
$idAbmCompra = utf8_decode($idAbmCompra);
$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);
eliminarcompra($idAbmCompra,$motivo);

}
if($operacion=="buscarpagoscompra")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
buscarpagoscompra($buscar);

}
if($operacion=="buscarpagoscomprahistorial")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
buscarpagoscomprahistorial($buscar);

}
if($operacion=="buscarcod")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
buscarcod($buscar,$cod_local);

}

if($operacion=="nuevopago" || $operacion=="editarpago" || $operacion=="eliminarpago")
{
	$codpago=$_POST['codpago'];
$codpago = utf8_decode($codpago);
	$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);
	$fechaapagar=$_POST['fechaapagar'];
$fechaapagar = utf8_decode($fechaapagar);
$fechadelpago=$_POST['fechadelpago'];
$fechadelpago = utf8_decode($fechadelpago);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$nrocheque=$_POST['nrocheque'];
$nrocheque = utf8_decode($nrocheque);
$cod_compraFk=$_POST['cod_compraFk'];
$cod_compraFk = utf8_decode($cod_compraFk);

addPagos($codpago,$nrocheque,$monto,$fechaapagar,$fechadelpago,$tipo,$estado,$cod_compraFk,$operacion);

}

if($operacion=="buscar")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$nrocompra=$_POST['nrocompra'];
$nrocompra = utf8_decode($nrocompra);
$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$proveedor=$_POST['proveedor'];
$proveedor = utf8_decode($proveedor);
$estadopago=$_POST['estadopago'];
$estadopago = utf8_decode($estadopago);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);

if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
buscar($fecha1,$fecha2,$nrocompra,$filtrofecha,$proveedor,$estadopago,$cod_local);

}

if($operacion=="buscarcompraseliminados")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$nrocompra=$_POST['nrocompra'];
$nrocompra = utf8_decode($nrocompra);
buscarcompraseliminados($fecha1,$fecha2,$nrocompra);

}

if($operacion=="buscarmas")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$nrocompra=$_POST['nrocompra'];
$nrocompra = utf8_decode($nrocompra);
$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$proveedor=$_POST['proveedor'];
$proveedor = utf8_decode($proveedor);
$estadopago=$_POST['estadopago'];
$estadopago = utf8_decode($estadopago);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
$totalCompra=$_POST['totalCompra'];
$totalCompra = quitarseparadormiles($totalCompra);
$totalDescuento=$_POST['totalDescuento'];
$totalDescuento = quitarseparadormiles($totalDescuento);
$totalPendiente=$_POST['totalPendiente'];
$totalPendiente = quitarseparadormiles($totalPendiente);
$totalPagado=$_POST['totalPagado'];
$totalPagado = quitarseparadormiles($totalPagado);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
buscarmas($fecha1,$fecha2,$nrocompra,$filtrofecha,$proveedor,$estadopago,$cod_local,$registrocargado,$totalCompra,$totalDescuento,$totalPendiente,$totalPagado);

}

if($operacion=="buscarcuentasapagar")
{
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$proveedor=$_POST['proveedor'];
$proveedor = utf8_decode($proveedor);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$nrofactura=$_POST['nrofactura'];
$nrofactura = utf8_decode($nrofactura);
$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$nrocheque=$_POST['nrocheque'];
$nrocheque = utf8_decode($nrocheque);
buscarcuentasapagar($fecha1,$fecha2,$proveedor,$cod_local,$nrofactura,$filtrofecha,$nrocheque);

}	

if($operacion=="buscarvista")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$local=$_POST['local'];
$local = utf8_decode($local);
if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
buscarvista($buscar,$local);

}	
if($operacion=="buscarnro")
{
	
buscarnro();

}

}

function abm($cod_compra,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2,$operacion)
{
	
	
if($cod_proveedorFK=="" || $num_comprobante==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}

$mysqli=conectar_al_servidor(); 

/*AUDITORIA*/
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$user=$_POST['useru'];
$user = utf8_decode($user);

if($operacion=="nuevo")
{
$consulta1="Insert into compra (fecha_compra,cod_proveedorFK,num_comprobante,cod_local,descuento,pagado1,pagado2,cod_user_insert,fecha_insert)
values(?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssss';
$stmt1->bind_param($ss,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2,$user,$fecha_inser_edit);
}


if($operacion=="editar")
{
$consulta1="Update compra set fecha_compra=?,cod_proveedorFK=?,num_comprobante=?,cod_local=?,descuento=?,pagado1=?,pagado2=?,cod_user_edit=?,fecha_edit=? where cod_compra=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2,$user,$fecha_inser_edit,$cod_compra);
}


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

if($operacion=="nuevo"){
	$cod_compra=obtenerId($cod_proveedorFK,$num_comprobante,$cod_local);
}
 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $cod_compra);
echo json_encode($informacion);	
exit;
	
}

function  eliminarcompra($cod_compra,$motivo)
{
	
	
if($cod_compra=="" || $motivo==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}

$mysqli=conectar_al_servidor(); 

/*AUDITORIA*/
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$user=$_POST['useru'];
$user = utf8_decode($user);


$consulta1="Update compra set motivoeliminar=?,cod_user_edit=?,fecha_edit=?,estado='Inactivo' where cod_compra=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$motivo,$user,$fecha_inser_edit,$cod_compra);



if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

recorredetalles($cod_compra);

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $cod_compra);
echo json_encode($informacion);	
exit;
	
}

function addPagos($codpago,$nrocheque,$monto,$fechaapagar,$fechadelpago,$tipo,$estado,$cod_compraFk,$operacion)
{
	
	
if($cod_compraFk=="" || $monto=="" || $tipo==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}

$mysqli=conectar_al_servidor(); 
/*AUDITORIA*/
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$user=$_POST['useru'];
$user = utf8_decode($user);


if($operacion=="nuevopago")
{


$consulta1="Insert into pagosdecompra (nrocheque,monto,fechaapagar,fechadelpago,tipo,estado,cod_compraFk,cod_user_insert,fecha_insert)
values(?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssss';
$stmt1->bind_param($ss,$nrocheque,$monto,$fechaapagar,$fechadelpago,$tipo,$estado,$cod_compraFk,$user,$fecha_inser_edit);


}


if($operacion=="editarpago")
{

$consulta1="Update pagosdecompra set monto=?,nrocheque=?,fechaapagar=?,fechadelpago=?,tipo=?,estado=?,cod_compraFk=?,cod_user_edit=?,fecha_edit=? where codpago=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$monto,$nrocheque,$fechaapagar,$fechadelpago,$tipo,$estado,$cod_compraFk,$user,$fecha_inser_edit,$codpago);

}

if($operacion=="eliminarpago")
{

$consulta1="delete from pagosdecompra where codpago=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$codpago);

}


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}


function obtenerId($cod_proveedorFK,$num_comprobante,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $cod_compra='';
		$sql= "Select cod_compra from compra where cod_proveedorFK='$cod_proveedorFK' and num_comprobante='$num_comprobante' and cod_local='$cod_local' order by fecha_compra desc limit 1   ";
		
   
   
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
		  
		  
		      $cod_compra=$valor['cod_compra'];
		  	
			  
			  
	  }
 }
 
  mysqli_close($mysqli);
return $cod_compra;


}



function buscarcod($buscar,$cod_local){
	$mysqli=conectar_al_servidor();
	 
		$sql= "Select cod_compra,fecha_compra,cod_proveedorFK,num_comprobante,cod_local,descuento,pagado1,pagado2,
		IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk),0) as totalpagados,
		IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and tipo='Cheque'),0) as pagosencheque,
		IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and tipo='Efectivo' ),0) as pagosenefectivo,
		(Select nombre_persona from persona where cod_persona=cod_proveedorFK) as proveedor
		from compra   where  estado='Activo' (cod_compra=? or num_comprobante=?) and cod_local=? ";
		
		     $fecha_compra="";
		  	  $cod_proveedorFK="";
		  	  $num_comprobante="";
		  	  $cod_compra="";
		  	  $proveedor="";
		  	  $cod_local="";
		  	  $descuento="0";
		  	  $pagado1="0";
		  	  $pagado2="0";
		  	  $totalpagados="0";
		  	    
   $stmt = $mysqli->prepare($sql);
  	$s='sss';
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar,$buscar,$cod_local);

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
		  
		  
		      $cod_compra=$valor['cod_compra'];
		      $fecha_compra=$valor['fecha_compra'];
		  	  $cod_proveedorFK=utf8_encode($valor['cod_proveedorFK']);
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $proveedor=utf8_encode($valor['proveedor']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $descuento=utf8_encode($valor['descuento']);
		  	  $pagado1=utf8_encode($valor['pagado1']);
		  	  $pagado2=utf8_encode($valor['pagado2']);
		  	  $pagosencheque=utf8_encode($valor['pagosencheque']);
		  	  $pagosenefectivo=utf8_encode($valor['pagosenefectivo']);
		  	  $totalpagados=utf8_encode($valor['totalpagados']);
		  	  
		
		  	 
		
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
$informacion =array("0" => "exito","1" => $fecha_compra,"2" => $cod_proveedorFK,"3" => $num_comprobante,"4" => $proveedor,"5" => $cod_compra,"6" => $cod_local,"7" => number_format($descuento,'0',',','.'),"8" => number_format($pagado1,'0',',','.'),"9" => number_format($pagado2,'0',',','.'),"10" => number_format($totalpagados,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarcompraseliminados($fecha1,$fecha2,$nrocompra)
{
	
	$mysqli=conectar_al_servidor();
	$condicionNroCompra="";
	if($nrocompra!=""){
		$condicionNroCompra="and num_comprobante like %'".$nrocompra."'%";
	}
	$condicionfecha="";
	if($fecha1!="" || $fecha2!=""){
		$condicionfecha="and date(fecha_edit)>='".$fecha1."' and date(fecha_edit)<='".$fecha2."' ";
	}

		
		$sql= "Select cod_compra,fecha_compra,num_comprobante,cp.motivoeliminar,
		(Select Nombre from local l where l.cod_local=cp.cod_local) as nombrelocal,
				cp.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
		from compra cp  where  cp.estado='Inactivo' and cod_compra!='0'  ".$condicionNroCompra.$condicionfecha;
	  
	
	  
	 
		     $pagina="";
		  	
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_compra=$valor['cod_compra'];
		      $fecha_compra=$valor['fecha_compra'];
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $motivoeliminar=utf8_encode($valor['motivoeliminar']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $fecha_edit=utf8_encode($valor['fecha_edit']);
		  	  $editadopor=utf8_encode($valor['editadopor']);
				
				$styleName=CargarStyleTable($styleName);
		  	   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='td_datos_1' style='width:10%'>".$num_comprobante."</td>
<td  id='td_datos_3' style='width:10%'>".$motivoeliminar."</td>
<td  id='td_datos_3' style='width:10%'>".$fecha_edit."</td>
<td  id='td_datos_3' style='width:10%'>".$editadopor."</td>
<td  id='td_datos_3' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 
 
 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscar($fecha1,$fecha2,$nrocompra,$filtrofecha,$proveedor,$estadopago,$cod_local)
{
	
	$mysqli=conectar_al_servidor();
	 $condicionCodLocal="";
		 if($cod_local!=""){
			 $condicionCodLocal=" and cp.cod_local='$cod_local' ";
		 }

		 $condicionFecha="";
		 if($fecha1!="" && $fecha2!=""){
			 $condicionFecha=" and fecha_compra between '".$fecha1."' and  '".$fecha2."' ";
		 }
		 
		 $condicionfechafiltro="";
		 if($filtrofecha!=""){
			 $condicionfechafiltro=" and fecha_compra='".$filtrofecha."'  ";
		 }
		 
		 $condicionproveedor="";
		 if($proveedor!=""){
			 $condicionproveedor=" and (Select nombre_persona from persona where cod_persona=cod_proveedorFK) like '%".$proveedor."%' ";
		 }
		 
		  $condicionnrocomprobante="";
		 if($nrocompra!=""){
			 $condicionnrocomprobante=" and num_comprobante='".$nrocompra."'  ";
		 }
		 
		 
		 $condicionpagos="";
		  if($estadopago=="1"){
		$condicionpagos="  and ((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK) - descuento)>(IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0)) "; 
	 }
	  if($estadopago=="2"){
		$condicionpagos="  and ((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK) - descuento)<=(IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0)) "; 
	 }
	 
		$sql= "Select cod_compra,fecha_compra,cod_proveedorFK,num_comprobante,cod_local,descuento,pagado1,pagado2,
		(Select nombre_persona from persona where cod_persona=cod_proveedorFK) as proveedor,
		(Select Nombre from local l where l.cod_local=cp.cod_local) as nombrelocal,
		IFNULL((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK),0) as totalcompra,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0) as totalpagado,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pendiente'),0) as totalPendiente,
			    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk ),0) as totalpagos,
				cp.fecha_insert,cp.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
		from compra cp  where  cp.estado='Activo' and cod_compra!='0'  ".$condicionCodLocal.$condicionFecha.$condicionfechafiltro.$condicionproveedor.$condicionnrocomprobante.$condicionpagos ." limit 50";
	  
	
	  // echo($sql);
	  // exit;
	 
		     $pagina="";
		  	
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalCompas=0;
 $totalDesc=0;
 $TotalesPagago=0;
 $TotalesPendiente=0;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_compra=$valor['cod_compra'];
		      $fecha_compra=$valor['fecha_compra'];
		  	  $cod_proveedorFK=utf8_encode($valor['cod_proveedorFK']);
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $proveedor=utf8_encode($valor['proveedor']);
		  	  $subtotalcompra=utf8_encode($valor['totalcompra']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	 $descuento=utf8_encode($valor['descuento']);
			  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $totalPendiente=utf8_encode($valor['totalPendiente']);
		  	  $totalpagos=utf8_encode($valor['totalpagos']);
			  $insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
		  	  $totalcompra=$subtotalcompra-$descuento;
		  	  

  $totalCompas=$totalCompas+$subtotalcompra;
 $totalDesc=$descuento+$totalDesc;
 $TotalesPendiente=$TotalesPendiente+$totalPendiente;
 $TotalesPagago=$TotalesPagago+$totalpagado;
 
		  	   $styleName=CargarStyleTable($styleName);
			   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosacompra(this)'>
<td  id='td_datos_1' style='width:10%'>".$num_comprobante."</td>
<td  id='td_datos_2' style='width:10%'>".$fecha_compra."</td>
<td  id='td_datos_3' style='width:10%'>".$proveedor."</td>
<td  id='td_datos_7' style='width:10%'>". number_format($subtotalcompra,'0',',','.')."</td>
<td  id='td_datos_8' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>". number_format($totalcompra,'0',',','.')."</td>
<td  id='td_datos_9' style='width:10%'>". number_format($totalpagado,'0',',','.')."</td>
<td  id='td_datos_10' style='width:10%'>". number_format($totalPendiente,'0',',','.')."</td>
<td  id='' style='width:10%'>". $nombrelocal."</td>
<td  id='td_datos_5' style='display:none'>".$cod_compra."</td>
<td  id='td_datos_6' style='display:none'>".$cod_proveedorFK."</td>
<td  id='td_datos_11' style='display:none'>".$cod_local."</td>
<td  id='td_datos_12' style='display:none'>".number_format($totalpagos,'0',',','.')."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 
 $sql= "Select cod_compra
		from compra cp  where cp.estado='Activo' and cod_compra!='0'  ".$condicionCodLocal.$condicionFecha.$condicionfechafiltro.$condicionproveedor.$condicionnrocomprobante.$condicionpagos;	 
		$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregisto= $valor;
 
 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($totalCompas,'0',',','.'),"5"=>number_format($totalDesc,'0',',','.'),"6"=>number_format($TotalesPendiente,'0',',','.'),"7"=>number_format($TotalesPagago,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregisto);
echo json_encode($informacion);	
exit;
}

function buscarmas($fecha1,$fecha2,$nrocompra,$filtrofecha,$proveedor,$estadopago,$cod_local,$registrocargado,$totalCompra,$totalDescuento,$totalPendiente,$totalPagado)
{
	
	$mysqli=conectar_al_servidor();
	 $condicionCodLocal="";
		 if($cod_local!=""){
			 $condicionCodLocal=" and cp.cod_local='$cod_local' ";
		 }

		 $condicionFecha="";
		 if($fecha1!="" && $fecha2!=""){
			 $condicionFecha=" and fecha_compra>='".$fecha1."' and fecha_compra<='".$fecha2."' ";
		 }
		 
		 $condicionfechafiltro="";
		 if($filtrofecha!=""){
			 $condicionfechafiltro=" and fecha_compra='".$filtrofecha."'  ";
		 }
		 
		 $condicionproveedor="";
		 if($proveedor!=""){
			 $condicionproveedor=" and (Select nombre_persona from persona where cod_persona=cod_proveedorFK) like '%".$proveedor."%' ";
		 }
		 
		  $condicionnrocomprobante="";
		 if($nrocompra!=""){
			 $condicionnrocomprobante=" and num_comprobante='".$nrocompra."'  ";
		 }
		 
		 
		 $condicionpagos="";
		  if($estadopago=="1"){
		$condicionpagos="  and ((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK) - descuento)>(IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0)) "; 
	 }
	  if($estadopago=="2"){
		$condicionpagos="  and ((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK) - descuento)<=(IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0)) "; 
	 }
	 
		$sql= "Select cod_compra,fecha_compra,cod_proveedorFK,num_comprobante,cod_local,descuento,pagado1,pagado2,
		(Select nombre_persona from persona where cod_persona=cod_proveedorFK) as proveedor,
		(Select Nombre from local l where l.cod_local=cp.cod_local) as nombrelocal,
		IFNULL((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK),0) as totalcompra,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0) as totalpagado,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pendiente'),0) as totalPendiente,
			    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk ),0) as totalpagos,
				cp.fecha_insert,cp.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
		from compra cp  where  cp.estado='Activo' and cod_compra!='0'  ".$condicionCodLocal.$condicionFecha.$condicionfechafiltro.$condicionproveedor.$condicionnrocomprobante.$condicionpagos." limit ".$registrocargado." , 50 ";
	  
	
	  
	 
		     $pagina="";
		  	
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor+$registrocargado;
 $totalCompas=$totalCompra;
 $totalDesc=$totalDescuento;
 $TotalesPagago=$totalPagado;
 $TotalesPendiente=$totalPendiente;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_compra=$valor['cod_compra'];
		      $fecha_compra=$valor['fecha_compra'];
		  	  $cod_proveedorFK=utf8_encode($valor['cod_proveedorFK']);
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $proveedor=utf8_encode($valor['proveedor']);
		  	  $subtotalcompra=utf8_encode($valor['totalcompra']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	 $descuento=utf8_encode($valor['descuento']);
			  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $totalPendiente=utf8_encode($valor['totalPendiente']);
		  	  $totalpagos=utf8_encode($valor['totalpagos']);
			  $insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
		  	  $totalcompra=$subtotalcompra-$descuento;
		  	  

  $totalCompas=$totalCompas+$subtotalcompra;
 $totalDesc=$descuento+$totalDesc;
 $TotalesPendiente=$TotalesPendiente+$totalPendiente;
 $TotalesPagago=$TotalesPagago+$totalpagado;
 
		  	   $styleName=CargarStyleTable($styleName);
			   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosacompra(this)'>
<td  id='td_datos_1' style='width:10%'>".$num_comprobante."</td>
<td  id='td_datos_2' style='width:10%'>".$fecha_compra."</td>
<td  id='td_datos_3' style='width:10%'>".$proveedor."</td>
<td  id='td_datos_7' style='width:10%'>". number_format($subtotalcompra,'0',',','.')."</td>
<td  id='td_datos_8' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>". number_format($totalcompra,'0',',','.')."</td>
<td  id='td_datos_9' style='width:10%'>". number_format($totalpagado,'0',',','.')."</td>
<td  id='td_datos_10' style='width:10%'>". number_format($totalPendiente,'0',',','.')."</td>
<td  id='' style='width:10%'>". $nombrelocal."</td>
<td  id='td_datos_5' style='display:none'>".$cod_compra."</td>
<td  id='td_datos_6' style='display:none'>".$cod_proveedorFK."</td>
<td  id='td_datos_11' style='display:none'>".$cod_local."</td>
<td  id='td_datos_12' style='display:none'>".number_format($totalpagos,'0',',','.')."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($totalCompas,'0',',','.'),"5"=>number_format($totalDesc,'0',',','.'),"6"=>number_format($TotalesPendiente,'0',',','.'),"7"=>number_format($TotalesPagago,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarvista($buscar,$local){
	
	$mysqli=conectar_al_servidor();
	 $condicionlocal="";
	 if($local!=""){
		$condicionlocal=" and cp.cod_local='$local'  "; 
	 }
	   	$sql= "Select tipo_compra,timbrado,tipoFactura,cod_compra,fecha_compra,cod_proveedorFK,num_comprobante,cod_local,descuento,pagado1,pagado2,
		(Select nombre_persona from persona where cod_persona=cod_proveedorFK) as proveedor,
		(Select Nombre from local l where l.cod_local=cp.cod_local) as nombrelocal,
		IFNULL((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK),0) as totalcompra,
		IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk),0) as totalpagados
		from compra cp  where cp.estado='Activo' and concat(cod_compra,' ',num_comprobante,' ',(Select nombre_persona from persona where cod_persona=cod_proveedorFK)) like '%".$buscar."%' ".$condicionlocal." order by fecha_compra asc limit 500  ";
	    $pagina="";
		  	
			
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
 $totalCompas=0;
 $totalDesc=0;
 $totalEfectivo=0;
 $totalCheque=0;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
			   $tipo_compra=$valor['tipo_compra'];
			   $timbrado=$valor['timbrado'];
			   $tipoFactura=$valor['tipoFactura'];
		  
		      $cod_compra=$valor['cod_compra'];
		      $fecha_compra=$valor['fecha_compra'];
		  	  $cod_proveedorFK=utf8_encode($valor['cod_proveedorFK']);
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $proveedor=utf8_encode($valor['proveedor']);
		  	  $subtotalcompra=utf8_encode($valor['totalcompra']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	 $descuento=utf8_encode($valor['descuento']);
		  	 $pagado1=utf8_encode($valor['pagado1']);
		  	  $pagado2=utf8_encode($valor['pagado2']);
		  	  $totalpagados=utf8_encode($valor['totalpagados']);
		  	  $totalcompra=$subtotalcompra-$descuento;
		  	  
		 $totalCompas=$totalCompas+$totalcompra;
 $totalDesc=$descuento+$totalDesc;
 $totalEfectivo=$pagado1+$totalEfectivo;
 $totalCheque=$pagado2+$totalCheque;
 $totalpagago=$pagado1+$pagado2;
		  	   
			    $styleName=CargarStyleTable($styleName);
			   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosacompravista(this)'>
<td  id='td_datos_1' style='width:10%'>".$num_comprobante."</td>
<td  id='td_datos_3' style='width:30%'>".$proveedor."</td>
<td  id='' style='width:10%'>". $nombrelocal."</td>
<td  id='td_datos_2' style='width:10%'>".$fecha_compra."</td>
<td  id='td_datos_7' style='display:none'>". number_format($subtotalcompra,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>". number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>". number_format($totalcompra,'0',',','.')."</td>
<td  id='td_datos_9' style='display:none'>". number_format($pagado1,'0',',','.')."</td>
<td  id='td_datos_10' style='display:none'>". number_format($pagado2,'0',',','.')."</td>
<td  id='td_datos_12' style='display:none'>". number_format($totalpagados,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".$cod_compra."</td>
<td  id='td_datos_6' style='display:none'>".$cod_proveedorFK."</td>
<td  id='td_datos_11' style='display:none'>".$cod_local."</td>
<td  id='td_datos_13' style='width:10%'>".$tipo_compra."</td>
<td  id='td_datos_14' style='width:10%'>".$timbrado."</td>
<td  id='td_datos_15' style='width:10%'>".$tipoFactura."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($totalCompas,'0',',','.'),"5"=>number_format($totalDesc,'0',',','.'),"6"=>number_format($totalEfectivo,'0',',','.'),"7"=>number_format($totalCheque,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarpagoscompra($buscar){
	
	$mysqli=conectar_al_servidor();
	 
	   	$sql= "Select pg.codpago,pg.monto,pg.fechaapagar,pg.fechadelpago,pg.tipo,pg.estado,pg.cod_compraFk,pg.nrocheque,pg.fecha_insert,pg.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=pg.cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=pg.cod_user_edit )as editadopor
		from compra cp inner join pagosdecompra pg on pg.cod_compraFk=cp.cod_compra
		where cp.estado='Activo' and cp.cod_compra='$buscar' order by fechadelpago";
	    $pagina="";
		 
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $Totales=0;
 $TotalPagado=0;
 $TotalPendiente=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $codpago=$valor['codpago'];
		      $monto=$valor['monto'];
		  	  $fechaapagar=utf8_encode($valor['fechaapagar']);
		  	  $fechadelpago=utf8_encode($valor['fechadelpago']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_compraFk=utf8_encode($valor['cod_compraFk']);
		  	  $nrocheque=utf8_encode($valor['nrocheque']);
		  $insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
		  	  $Totales=$Totales+$monto;
			  if($estado=="Pagado"){
				   $TotalPagado=$TotalPagado+$monto;
			  }else{
				   $TotalPendiente=$TotalPendiente+$monto;
			  }
		 
		  	   $styleName=CargarStyleTable($styleName);
			   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatoshistorialpago(this)'>
<td  id='td_datos_1' style='display:none'>".$codpago."</td>
<td  id='td_datos_7' style='width:15%;'>".$nrocheque."</td>
<td  id='td_datos_2' style='width:15%;'>". number_format($monto,'0',',','.')."</td>
<td  id='td_datos_3' style='width:15%;'>".$tipo."</td>
<td  id='td_datos_4' style='width:15%;'>".$fechaapagar."</td>
<td  id='td_datos_5' style='width:15%;'>".$fechadelpago."</td>
<td  id='td_datos_6' style='width:15%;'>".$estado."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($Totales,'0',',','.'),"5"=>number_format($TotalPagado,'0',',','.'),"6"=>number_format($TotalPendiente,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarcuentasapagar($fecha1,$fecha2,$proveedor,$cod_local,$nrofactura,$filtrofecha,$nrocheque){
	
	$mysqli=conectar_al_servidor();
	 $condicionFecha="";
	 if($fecha1!="" && $fecha2!=""  ){
	 $condicionFecha=" and pg.fechaapagar>='$fecha1' and pg.fechaapagar<='$fecha1' ";
	 }
	 $condicionproveedor="";
	 if($proveedor=="2"){
	 $condicionproveedor=" and (Select nombre_persona from persona where cod_persona=cod_proveedorFK)  like '%".$proveedor."%'";
	 }
	 $condicionlocal="";
	 if($cod_local=="2"){
	 $condicionlocal=" and cp.cod_local='$cod_local'";
	 }
	 $condicionnrofactura="";
	 if($nrofactura=="2"){
	 $condicionnrofactura=" and cp.num_comprobante='$nrofactura'";
	 }
	 $condicionfiltrofecha="";
	 if($filtrofecha=="2"){
	 $condicionfiltrofecha=" and pg.fechaapagar='$filtrofecha'";
	 }
	 $condicionnrocheque="";
	 if($nrocheque=="2"){
	 $condicionnrocheque=" and pg.nrocheque like '%".$nrocheque."%'";
	 }
	
	 
	   	$sql= "Select pg.codpago,pg.monto,pg.fechaapagar,pg.fechadelpago,pg.tipo,pg.estado,pg.cod_compraFk,pg.nrocheque,
		cp.cod_compra,cp.fecha_compra,cp.cod_proveedorFK,cp.num_comprobante,cp.cod_local,cp.descuento,cp.pagado1,cp.pagado2,
		(Select nombre_persona from persona where cod_persona=cod_proveedorFK) as proveedor,
		(Select Nombre from local l where l.cod_local=cp.cod_local) as nombrelocal,
		IFNULL((select sum(subTotal) from detalle_compra where cod_compra=cod_compraFK),0) as totalcompra,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pagado'),0) as totalpagado,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk and estado='Pendiente'),0) as totalPendiente,
	    IFNULL((select sum(monto) from pagosdecompra where cod_compra=cod_compraFk ),0) as totalpagos
		from compra cp inner join pagosdecompra pg on pg.cod_compraFk=cp.cod_compra
		where cp.estado='Activo' and pg.estado!='Pagado'  ".$condicionFecha.$condicionproveedor.$condicionlocal.$condicionnrofactura.$condicionfiltrofecha.$condicionnrocheque." order by pg.fechadelpago";
	    $pagina="";
		 
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $Totales=0;
 $TotalPagado=0;
 $TotalPendiente=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $codpago=$valor['codpago'];
		      $cod_compra=$valor['cod_compra'];
		      $monto=$valor['monto'];
		  	  $fechaapagar=utf8_encode($valor['fechaapagar']);
		  	  $fechadelpago=utf8_encode($valor['fechadelpago']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_compraFk=utf8_encode($valor['cod_compraFk']);
		  	  $fecha_compra=utf8_encode($valor['fecha_compra']);
		  	  $cod_proveedorFK=utf8_encode($valor['cod_proveedorFK']);
		  	  $num_comprobante=utf8_encode($valor['num_comprobante']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  $descuento=utf8_encode($valor['descuento']);
		  	  $pagado1=utf8_encode($valor['pagado1']);
		  	  $pagado2=utf8_encode($valor['pagado2']);
		  	  $proveedor=utf8_encode($valor['proveedor']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $totalcompra=utf8_encode($valor['totalcompra']);
		  	  $totalpagado=utf8_encode($valor['totalpagado']);
		  	  $totalPendiente=utf8_encode($valor['totalPendiente']);
		  	  $totalpagos=utf8_encode($valor['totalpagos']);
			  $subtotalcompra=utf8_encode($valor['totalcompra']);
			  $nrocheque=utf8_encode($valor['nrocheque']);
		  
		  	  $Totales=$Totales+$monto;
			  if($estado=="Pagado"){
				   $TotalPagado=$TotalPagado+$monto;
			  }else{
				   $TotalPendiente=$TotalPendiente+$monto;
			  }
			  

		  	   $styleName=CargarStyleTable($styleName);
			   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosacompra(this)'>
<td  id='' style='display:none'>".$cod_compra."</td>
<td  id='' style='width:10%;'>".$num_comprobante."</td>
<td  id='' style='width:15%;'>".$proveedor."</td>
<td  id='' style='width:10%;'>".$fechaapagar."</td>
<td  id='' style='width:10%;'>".$nrocheque."</td>
<td  id='' style='width:10%;'>". number_format($monto,'0',',','.')."</td>
<td  id='' style='width:5%;'>".$tipo."</td>
<td  id='td_datos_1' style='display:none'>".$num_comprobante."</td>
<td  id='td_datos_2' style='display:none'>".$fecha_compra."</td>
<td  id='td_datos_3' style='display:none'>".$proveedor."</td>
<td  id='td_datos_7' style='display:none'>". number_format($subtotalcompra,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>". number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_4' style='display:none'>". number_format($totalcompra,'0',',','.')."</td>
<td  id='td_datos_9' style='display:none'>". number_format($totalpagado,'0',',','.')."</td>
<td  id='td_datos_10' style='display:none'>". number_format($totalPendiente,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".$cod_compra."</td>
<td  id='td_datos_6' style='display:none'>".$cod_proveedorFK."</td>
<td  id='td_datos_11' style='display:none'>".$cod_local."</td>
<td  id='td_datos_12' style='display:none'>".number_format($totalpagos,'0',',','.')."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($Totales,'0',',','.'),"5"=>number_format($TotalPagado,'0',',','.'),"6"=>number_format($TotalPendiente,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarpagoscomprahistorial($buscar){
	
	$mysqli=conectar_al_servidor();
	 
	   	$sql= "Select pg.codpago,pg.monto,pg.fechaapagar,pg.fechadelpago,pg.tipo,pg.estado,pg.cod_compraFk,pg.fecha_insert,pg.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=pg.cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=pg.cod_user_edit )as editadopor
		from compra cp inner join pagosdecompra pg on pg.cod_compraFk=cp.cod_compra
		where cp.estado='Activo' and cp.cod_compra='$buscar' order by fechadelpago";
	    $pagina="";
		 
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $Totales=0;
 $TotalPagado=0;
 $TotalPendiente=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $codpago=$valor['codpago'];
		      $monto=$valor['monto'];
		  	  $fechaapagar=utf8_encode($valor['fechaapagar']);
		  	  $fechadelpago=utf8_encode($valor['fechadelpago']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_compraFk=utf8_encode($valor['cod_compraFk']);
		  	 $insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
		  	  $Totales=$Totales+$monto;
			  if($estado=="Pagado"){
				   $TotalPagado=$TotalPagado+$monto;
			  }else{
				   $TotalPendiente=$TotalPendiente+$monto;
			  }
		 
		  	   $styleName=CargarStyleTable($styleName);
			   $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatospagohistorial(this)' >
<td  id='td_datos_1' style='display:none'>".$codpago."</td>
<td  id='td_datos_2' style='width:15%;'>". number_format($monto,'0',',','.')."</td>
<td  id='td_datos_3' style='width:15%;'>".$tipo."</td>
<td  id='td_datos_4' style='width:15%;'>".$fechaapagar."</td>
<td  id='td_datos_5' style='width:15%;'>".$fechadelpago."</td>
<td  id='td_datos_6' style='width:15%;'>".$estado."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";
		
			  
			  
	  }
 }
 
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4"=>number_format($Totales,'0',',','.'),"5"=>number_format($TotalPagado,'0',',','.'),"6"=>number_format($TotalPendiente,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarnro(){
	
	$mysqli=conectar_al_servidor();
	 
	   	$sql= "Select count(cod_compra)
		from compra cp ";
	    $pagina="";
		  	
		  	 
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nroOrden=$result->fetch_row();
  $nroOrden=$nroOrden[0];
  $nroOrden=$nroOrden+1;
 if($nroOrden<10){
	 $nroOrden="000".$nroOrden;
 }else{
 if($nroOrden<100){
	 $nroOrden="00".$nroOrden;
 }else{
	 if($nroOrden<1000){
	 $nroOrden="0".$nroOrden;
    } 
 }
 }
 mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => $nroOrden);
echo json_encode($informacion);	
exit;
}

function recorredetalles($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select dc.cod_detalle_compra,dc.cantidad_detalle_compra,dc.precio_producto,dc.subTotal,dc.cod_productoFK,pro.nombre_producto,dc.cod_compraFK,
		(select cod_local from compra where cod_compra=cod_compraFK) as cod_local
		from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK
		where dc.cod_compraFK = ? ";
		
   
   
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
 $nroRegistro=$valor;
  $controlDescuento="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_detalle_compra=$valor['cod_detalle_compra'];
		  	  $cantidad_detalle_compra=utf8_encode($valor['cantidad_detalle_compra']);
		  	  $precio_producto=utf8_encode($valor['precio_producto']);
		  	  $subTotal=utf8_encode($valor['subTotal']);
		  	  $cod_productoFK=utf8_encode($valor['cod_productoFK']);
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cod_compraFK=utf8_encode($valor['cod_compraFK']);
		  	  $cod_local=utf8_encode($valor['cod_local']);
		  	  editar_cantidad($cod_productoFK,$cantidad_detalle_compra,"resta",$cod_local);
			 
			  
			  
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


verificar($operacion);
?>
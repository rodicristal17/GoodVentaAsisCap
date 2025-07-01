<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include('quitarseparadormiles.php');
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
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


	
if($operacion=="nuevo")
{
	
	
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);
$metodopago=$_POST['metodopago'];
$metodopago = utf8_decode($metodopago);
$iniciopago=$_POST['iniciopago'];
$iniciopago = utf8_decode($iniciopago);
$nroCuota=$_POST['nroCuota'];
$nroCuota = utf8_decode($nroCuota);
$total=$_POST['total'];
$total = quitarseparadormiles($total);
$interes=$_POST['interes'];
$interes = quitarseparadormiles($interes);
$entrega=$_POST['entrega'];
$entrega = quitarseparadormiles($entrega);
$dias=$_POST['dias'];


generarCuotas($cod_venta,$Monto,$metodopago,$iniciopago,$nroCuota,$interes,$dias,$entrega,$total);

}

if($operacion=="nuevodesdeventa")
{
	
	
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);
$metodopago=$_POST['metodopago'];
$metodopago = utf8_decode($metodopago);
$iniciopago=$_POST['iniciopago'];
$iniciopago = utf8_decode($iniciopago);
$nroCuota=$_POST['nroCuota'];
$nroCuota = utf8_decode($nroCuota);
$pagoentrega=$_POST['pagoentrega'];
$pagoentrega = utf8_decode($pagoentrega);
$idGaranteFk=$_POST['idGaranteFk'];
$idGaranteFk = utf8_decode($idGaranteFk);
$interes=$_POST['interes'];
$interes = quitarseparadormiles($interes);
$entrega=$_POST['entrega'];
$entrega = quitarseparadormiles($entrega);
$dias=$_POST['dias'];


generarCuotasdesdeventa($idGaranteFk,$pagoentrega,$cod_venta,$Monto,$metodopago,$iniciopago,$nroCuota,$interes,$dias,$entrega);

}



if($operacion=="buscardatoscuenta")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscardatoscuenta($buscar);

}

	if($operacion=="buscar")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarcreditos($buscar);

}	

if($operacion=="buscarcreditoseditar")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarcreditoseditar($buscar);

}	

	if($operacion=="buscarcreditoenrenfi")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarcreditoenrenfi($buscar);

}

	if($operacion=="creditoshistorialventa")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarcreditoshistorialventa($buscar);

}

	if($operacion=="cuentasacobrar")
{
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);
$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$codlocal=$_POST['codlocal'];
$codlocal = utf8_decode($codlocal);
$filtro=$_POST['filtro'];
$filtro = utf8_decode($filtro);

$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);

// if($codlocal==""){
// $controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	// if($controllocal==0){
		// $codlocal=buscarlocaluser($user);
	// }
// }
cuentasacobrar($filtro,$fecha1,$fecha2,$cliente,$documento,$telefono,$producto,$filtrofecha,$codlocal,$vendedor);

}
	if($operacion=="mascuentasacobrar")
{
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);
$documento=$_POST['documento'];
$documento = utf8_decode($documento);
$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);
$filtrofecha=$_POST['filtrofecha'];
$filtrofecha = utf8_decode($filtrofecha);
$codlocal=$_POST['codlocal'];
$codlocal = utf8_decode($codlocal);
$filtro=$_POST['filtro'];
$filtro = utf8_decode($filtro);
$totalcobrar=$_POST['totalcobrar'];
$totalcobrar = quitarseparadormiles($totalcobrar);
$totaldeuda=$_POST['totaldeuda'];
$totaldeuda = quitarseparadormiles($totaldeuda);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);

$vendedor=$_POST['vendedor'];
$vendedor = utf8_decode($vendedor);
// if($codlocal==""){
// $controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	// if($controllocal==0){
		// $codlocal=buscarlocaluser($user);
	// }
// }
mascuentasacobrar($filtro,$fecha1,$fecha2,$cliente,$documento,$telefono,$producto,$filtrofecha,$codlocal,$registrocargado,$totalcobrar,$totaldeuda,$vendedor);

}

	if($operacion=="cuentasacobrardetallado")
{

$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$zona=$_POST['zona'];
$zona = utf8_decode($zona);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);

$cliente=$_POST['cliente'];
$cliente = utf8_decode($cliente);

$cobrador =$_POST['cobrador'];
$cobrador = utf8_decode($cobrador);

if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	cuentasacobrardetallado($cobrador,$cliente,$fecha1,$fecha2,$zona,$cod_local,$tipo);

}
if($operacion=="mascuentasacobrardetallado")
{

$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$zona=$_POST['zona'];
$zona = utf8_decode($zona);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);


$cliente =$_POST['cliente'];
$cliente = utf8_decode($cliente);

$cobrador =$_POST['cobrador'];
$cobrador = utf8_decode($cobrador);


$totalACobrar=$_POST['totalACobrar'];
$totalACobrar = quitarseparadormiles($totalACobrar);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	mascuentasacobrardetallado($cobrador,$cliente,$fecha1,$fecha2,$zona,$cod_local,$tipo,$totalACobrar,$registrocargado);

}

if($operacion=="editarestecredito")
{
	$codCredito=$_POST['codCredito'];
$codCredito = utf8_decode($codCredito);
$date=$_POST['date'];
$date = utf8_decode($date);
$monto=$_POST['monto'];
$monto = quitarseparadormiles($monto);
$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);
$interes=$_POST['interes'];
$interes = quitarseparadormiles($interes);
$dias=$_POST['dias'];
$dias = utf8_decode($dias);
	editarestecredito($codCredito,$date,$monto,$descuento,$interes,$dias);

}


if($operacion=="cuentasacobrarwhat")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$filtro=$_POST['filtro'];
$filtro = utf8_decode($filtro);
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$zona=$_POST['zona'];
$zona = utf8_decode($zona);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
	cuentasacobrarwhat($buscar,$filtro,$fecha1,$fecha2,$zona,$cod_local);

}	



if($operacion=="refinanciarencambio")
{
	
	
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$metodopago=$_POST['metodopago'];
$metodopago = utf8_decode($metodopago);
$iniciopago=$_POST['iniciopago'];
$iniciopago = utf8_decode($iniciopago);
$nroCuota=$_POST['nroCuota'];
$nroCuota = utf8_decode($nroCuota);
$dias=$_POST['dias'];
$dias = quitarseparadormiles($dias);
$interes=$_POST['interes'];
$interes = quitarseparadormiles($interes);
$total=$_POST['total'];
$total = quitarseparadormiles($total);
$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);

refinanciarencambio($cod_venta,$metodopago,$iniciopago,$nroCuota,$total,$Monto,$dias,$interes);

}

if($operacion=="editarcuenta")
{
	
	
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$idcredito=$_POST['idcredito'];
$idcredito = utf8_decode($idcredito);
$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);

editarcuota($cod_venta,$idcredito,$fecha,$tipo,$descuento);

}

if($operacion=="buscarcuentasExpCobrados")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	cuentasExpCobrados($buscar);

}
if($operacion=="cuentasClientesCobrados")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	cuentasClientesCobrados($buscar);

}
if($operacion=="cuentasClientesPendientes")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	cuentasClientesPendientes($buscar);

}
if($operacion=="buscarccuentasExpPendientes")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	cuentasExpPendientes($buscar);

}

if($operacion=="eliminarcreditorefin")
{
	
	
$codcredito=$_POST['codcredito'];
$codcredito = utf8_decode($codcredito);
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
eliminarcreditorefin($codcredito,$cod_venta);

}
if($operacion=="nuevocreditorefin" || $operacion=="editarcreditorefin")
{
	
	
$plazo=$_POST['plazo'];
$plazo = utf8_decode($plazo);
$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);
$fechapago=$_POST['fechapago'];
$fechapago = utf8_decode($fechapago);
$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);
$interes=$_POST['interes'];
$interes = quitarseparadormiles($interes);
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$idcredito=$_POST['idcredito'];
$idcredito = utf8_decode($idcredito);
$dias=$_POST['dias'];
$dias = utf8_decode($dias);
abmcreditorefin($plazo,$Monto,$fechapago,$descuento,$interes,$dias,$cod_venta,$idcredito,$operacion);

}

if($operacion=="refinanciarcuotas")
{
	
	
$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);
$Monto=$_POST['Monto'];
$Monto = quitarseparadormiles($Monto);
$metodopago=$_POST['metodopago'];
$metodopago = utf8_decode($metodopago);
$iniciopago=$_POST['iniciopago'];
$iniciopago = utf8_decode($iniciopago);
$nroCuota=$_POST['nroCuota'];
$nroCuota = utf8_decode($nroCuota);
$dias=$_POST['dias'];
$dias = utf8_decode($dias);
$total=$_POST['total'];
$total = quitarseparadormiles($total);
$interes=$_POST['interes'];
$interes = quitarseparadormiles($interes);
$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);
RefinanciarCuotasRestantes($cod_venta,$iniciopago,$nroCuota,$total,$Monto,$descuento,$interes,$dias,$metodopago);

}


}


function abmcreditorefin($plazo,$Monto,$fechapago,$descuento,$interes,$dias,$cod_venta,$idcredito,$operacion)
{

if($plazo==""  || $Monto=="" || $fechapago==""|| $cod_venta=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 



if($operacion=="nuevocreditorefin") 
{


$consulta1="Insert into credito (plazo,fechapago,cod_venta,Monto,descuento,Esado,Nro_recibo,interes,dias,deudaInteres)
values(?,?,?,?,?,'Pendiente','0',?,?,0)";

$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$plazo,$fechapago,$cod_venta,$Monto,$descuento,$interes,$dias);



}


if($operacion=="editarcreditorefin")
{

$consulta1="Update credito set plazo=?,fechapago=?,cod_venta=?,Monto=?,descuento=?,interes=?,dias=? where idcredito=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssss';
$stmt1->bind_param($ss,$plazo,$fechapago,$cod_venta,$Monto,$descuento,$interes,$dias,$idcredito);




}




if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
cambiarplazos($cod_venta);
 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function editarestecredito($codCredito,$fechapago,$Monto,$descuento,$interes,$dias)
{

if($codCredito==""  || $Monto=="" || $fechapago=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 



$consulta1="Update credito set fechapago='$fechapago',Monto='$Monto',descuento='$descuento',interes='$interes',dias='$dias' where idcredito='$codCredito'";	


$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function RefinanciarCuotasRestantes($cod_venta,$iniciopago,$nroCuota,$total,$Monto,$descuento,$interes,$dias,$metodopago){
	$mysqli=conectar_al_servidor();
			
	
	$sql= "Select idcredito,Monto,descuento,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.Tipo='Pago Cuota'),0) as totalPago
	from credito cr
	where cr.cod_venta='$cod_venta' ";
		
		if($descuento>0){
			$descuento=$descuento/$nroCuota;
		}else{
			$descuento=0;
		}
   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $F=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     $idcredito=$valor['idcredito'];
			  $totalPago=utf8_encode($valor['totalPago']);
			 $cuota=utf8_encode($valor['Monto']);
			  $descuentocuota=utf8_encode($valor['descuento']);
			if($totalPago<=0){
				eliminarestecreditos($idcredito);
			}else{
				if($cuota>$totalPago){
				$totalPago=$descuentocuota+$totalPago;
				 editarestacuota($idcredito,$totalPago);
				}
				
			}
			  
			  
			  
	  }
 }
		

		
		 $a=0;
			 $F=0;
			 $fechaInicio=$iniciopago;
		     $cantidad=$nroCuota;
				$pendiente=$total;
				
	$fecha = strtotime($iniciopago);
	$diacredito = date("d",$fecha);//dia
	$anhocredito = date("Y",$fecha);//AÑO
	$mescredito = date("m",$fecha) + 1;//mes
if($mescredito=="13"){$mescredito ="01";}	
			
	$contarDias=UltimoDia($anhocredito,$mescredito);			
				
				
				
			 while ($a<$cantidad){
		if($metodopago=="Mensual")	{
			if($contarDias<$diacredito || $contarDias==""){
				
					if($F>=1){		
						$RestarF= $F -1 ; 
						$fecha = strtotime('+'.$RestarF." month",strtotime($fechaInicio));
						
						$fecha = strtotime('+'.$contarDias." day",strtotime(date("d-m-Y",$fecha)));
					}else{
						$fecha =  strtotime($fechaInicio) ;
						// $fecha = strtotime('+ '.$contarDias." day",strtotime($fechaInicio));
					}
									
				
			}else{
				$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
				
			}
				$F=$F+1;
				
			$diacredito = date("d",$fecha);//dia
			$anhocredito = date("Y",$fecha);//AÑO
			$mescredito = date("m",$fecha) + 1;//mes
			if($mescredito=="13"){$mescredito ="01";}
			
			$contarDias=UltimoDia($anhocredito,$mescredito);
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

	insertarcuotas(($a+1)."/".$nroCuota,$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ",$descuento,$dias,$interes, $cuotaSobrante);
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
			
				 insertarcuotas(($a+1)."/".($nroCuota+1),$fecha, $cod_venta, $pendiente, "Pendiente"," ",$descuento,$dias,$interes, $pendiente);
			 }
			 
			 
			 actualizarMetodo($cod_venta,$metodopago);
			 cambiarplazos($cod_venta);
			  mysqli_close($mysqli);
			 $informacion =array("1" => "exito" );
echo json_encode($informacion);	
exit;
		
}

function eliminarestecreditos($idcredito){
		$mysqli=conectar_al_servidor();
			$consulta="delete from pago where  cod_creditoFK='$idcredito' ";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
echo $mysqli->error;
exit;
}

$consulta="delete from credito where  idcredito='$idcredito' ";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
echo $mysqli->error;
exit;
}
 mysqli_close($mysqli);
}


function editarestacuota($idcredito,$monto) {

	

	$mysqli=conectar_al_servidor();
$consulta1="update credito set Monto=?,totaldeuda=?,totalinteres=0 where idcredito=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$monto,$monto,$idcredito);
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);


}


function editarcuota($cod_venta,$idcredito,$fecha,$tipo,$descuento) {

	if($cod_venta==""  || $idcredito==""  || $fecha==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

if($tipo=="1"){
	$mysqli=conectar_al_servidor();
$consulta1="update credito set fechapago=?,descuento=? where idcredito=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$fecha,$descuento,$idcredito);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}
 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}else{
	
	cambiarfechas($cod_venta,$idcredito,$fecha);
	
}



}


function eliminarcreditorefin($idcredito,$cod_venta) {

	if($idcredito==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}


	$mysqli=conectar_al_servidor();
$consulta1="delete from credito where idcredito=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$idcredito);
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}
cambiarplazos($cod_venta);
 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function cambiarfechas($cod_venta,$codcredito,$fecha){

	$mysqli=conectar_al_servidor();
	$sql= "Select idcredito,fechapago,
	(select TipoPago from venta where cod_venta=cr.cod_venta) as TipoPago
	from credito cr
	where cr.idcredito>='$codcredito' and cr.cod_venta='$cod_venta' ";
		
		
   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $F=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     $idcredito=$valor['idcredito'];
			  $fechapago=utf8_encode($valor['fechapago']);
			  $metodopago=utf8_encode($valor['TipoPago']);
			  
		 if($metodopago=="Mensual")	{
			$fecha = strtotime('+'.$F." month",strtotime($fecha));
			$F=$F+1;
		}
		if($metodopago=="Semanal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fecha));
			$F=$F+7;
		}
		if($metodopago=="Quincenal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fecha));
			$F=$F+15;
		}
	 $fecha=date("Y-m-d H:i:s",$fecha);
		editarcuotafechas($idcredito,$fecha);	  
			  
			  
			  
	  }
 }
	 mysqli_close($mysqli);	
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

		
}

function editarcuotafechas($idcredito,$fecha) {



	$mysqli=conectar_al_servidor();
$consulta1="update credito set fechapago=? where idcredito=?";
$stmt1 = $mysqli->prepare($consulta1);
$stmt1->bind_param($ss,$fecha,$idcredito);
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);


}

function generarCuotas($cod_venta,$Monto,$metodopago,$iniciopago,$nroCuota,$interes,$dias,$entrega,$total){
	$nc=0;
	if($entrega>0){
		
			$cuota="1/".$nroCuota;
			
		$mysqli=conectar_al_servidor(); 
	$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo,tipo,dias,interes , deudaInteres)
			values('$cuota',(select fecha_venta from venta where cod_venta='$cod_venta' limit 1),'$cod_venta','$entrega','Pendiente','0','ENTREGA','$dias','$interes',0)";		

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 mysqli_close($mysqli);	
	$nroCuota=$nroCuota-1;
		

	$nc=1;
	}
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
	
	insertarcuotas(($nc+1)."/".$nroCuota,$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ",0,$dias,$interes,$cuotaSobrante);
	$pendiente=$pendiente-$cuotaSobrante;
	
				  $a++;
				  $nc++;
			 
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
				 insertarcuotas(($nc+1)."/".($nroCuota+1),$fecha, $cod_venta, $pendiente, "Pendiente"," ",0,$dias,$interes,$pendiente);
			 }
			 
			 
			 actualizarMetodo($cod_venta,$metodopago);
		 mysqli_close($mysqli);	 
			 $informacion =array("1" => "exito","2"=> $paginaticket);/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
	
	
}

function generarCuotasdesdeventa($idGaranteFk,$pagoentrega,$cod_venta,$Monto,$metodopago,$iniciopago,$nroCuota,$interes,$dias,$entrega){
	eliminarcreditos($cod_venta);
	$totalcuotas=$nroCuota;
	$observacion="";
	$cuotas="";
	 $a=0;
	 $F=0;
	 $nc=0;
	if($entrega>0){
		
			$cuotas="Entrega";
			
		$mysqli=conectar_al_servidor(); 
	$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo,tipo,dias,interes,total,totaldeuda,deudaInteres)
			values('$cuotas',(select fecha_venta from venta where cod_venta='$cod_venta' limit 1),'$cod_venta','$entrega','Pendiente','0','ENTREGA','$dias','$interes','$entrega','$entrega',0)";

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


 mysqli_close($mysqli);	
 
	 $observacion=" *Entrega :".$entrega." Gs.";
	}
	$nc=1;
			 $fechaInicio=$iniciopago;
		     $cantidad=$nroCuota;
			$pendiente=buscartotalventa($cod_venta);
			$pendiente=$pendiente-$entrega;
				
	$fecha = strtotime($iniciopago);

	$diacredito = date("d",$fecha);//dia
	$anhocredito = date("Y",$fecha);//AÑO
	$mescredito = date("m",$fecha)  +1 ;//mes
			if($mescredito=="13"){$mescredito ="01";}
			
	$contarDias=UltimoDia($anhocredito,$mescredito);				
				

	$consola="";	
				
 while ($a<$cantidad){
	 
	
		if($metodopago=="Mensual")	{
			
	
			
			if($contarDias<$diacredito || $contarDias==""){
				
					if($F>=1){		
						$RestarF= $F -1 ; 
						$fecha = strtotime('+'.$RestarF." month",strtotime($fechaInicio));
						
						$fecha = strtotime('+'.$contarDias." day",strtotime(date("d-m-Y",$fecha)));
					}else{
						$fecha =  strtotime($fechaInicio) ;
						// $fecha = strtotime('+ '.$contarDias." day",strtotime($fechaInicio));
					}
									
				
			}else{
				$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
				
			}
				$F=$F+1;
			$diacredito = date("d",$fecha);//dia
			$anhocredito = date("Y",$fecha);//AÑO
			$mescredito = date("m",$fecha) +1 ;//mes
			if($mescredito=="13"){$mescredito ="01";}
			
			$contarDias=UltimoDia($anhocredito,$mescredito);		

		}
		if($metodopago=="Semanal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			$F=$F+7;
		}
		if($metodopago=="Quincenal")	{
			$fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
			$F=$F+15;
		}
	
	$consola.= $mescredito."-" ;
	
	$fecha=date("Y-m-d H:i:s",$fecha);
	
	

	 if($pendiente>$Monto){
	$cuotaSobrante=$pendiente-$Monto;
	$cuotaSobrante=$pendiente-$cuotaSobrante;
	}else{
	$cuotaSobrante=$pendiente;
	}
	if(($a+1)>=$cantidad){
	$s=$pendiente-$cuotaSobrante;
	if($s>0){
		$cuotaSobrante=$cuotaSobrante+$s;
	}
	}
	insertarcuotas(($nc)."/".$totalcuotas,$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ",0,$dias,$interes,$cuotaSobrante);
	$pendiente=$pendiente-$cuotaSobrante;
	
				  $a++;
				  $nc++;
			 
			 }
			 
			 if($pendiente>0){
			if($metodopago=="Mensual")	{
				
			if($contarDias<$diacredito || $contarDias==""){
				
					if($F>=1){		
						$RestarF= $F -1 ; 
						$fecha = strtotime('+'.$RestarF." month",strtotime($fechaInicio));
						
						$fecha = strtotime('+'.$contarDias." day",strtotime(date("d-m-Y",$fecha)));
					}else{
						$fecha =  strtotime($fechaInicio) ;
						// $fecha = strtotime('+ '.$contarDias." day",strtotime($fechaInicio));
					}
									
				
			}else{
				$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
				
			}
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
				 insertarcuotas(($nc+1)."/".($nroCuota+1),$fecha, $cod_venta, $pendiente, "Pendiente"," ",0,$dias,$interes,$pendiente);
			 }
// echo($consola);
// exit;			 

if($observacion!=""){
$observacion.=" *Cuotas: ".$nroCuota." X ".$Monto." Gs.";
}else{
$observacion=" *Cuotas: ".$nroCuota." X ".$Monto." Gs.";
}
editarDetallesVenta($cod_venta,$observacion);
		
actualizarMetodo($cod_venta,$metodopago);
actualizarGarante($cod_venta,$idGaranteFk);
$datos=buscardatoscuentacreditosventa($cod_venta); 
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
$descuento=$datos[11] ;
$interes=$datos[12] ;
$entrega=$datos[13] ;


$datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
$totalEnDescuento=$datos[0];
$totalInteres=$datos[12];
$deuda=$datos[4];
$diasatrasado=$datos[5];
$acobrar=$datos[8];
$totalCredito=$datos[11];
$totalpagado=$datos[3];
if($totalCredito>0){
	$totalventa=$totalCredito;
}

$informacion =array("1" => "exito","15" => $plazo ,"16" => $fechapago 
,"23" => number_format($Monto,'0',',','.')  ,"18" => $Nro_recibo ,"19" => $nroCuota ,"20" => $dias
,"21" => number_format($interes,'2',',','.')  ,"22" => $TipoPago ,"17" =>number_format($entrega,'0',',','.'),
"24" =>number_format($totalInteres,'0',',','.'),"27" =>number_format($totalpagado,'0',',','.'),
"25" =>number_format($deuda,'0',',','.'),"26" =>$diasatrasado,"28"=>$cuotas);
echo json_encode($informacion);	
exit;
	
	
}


function UltimoDia($anho,$mes){
   if (((fmod($anho,4)==0) and (fmod($anho,100)!=0)) or (fmod($anho,400)==0)) {
       $dias_febrero = 29;
   } else {
       $dias_febrero = 28;
   }
   // echo($mes);
   switch($mes) {
       case 01: return 31; break;
       case 02: return $dias_febrero; break;
       case 03: return 31; break;
       case 04: return 30; break;
       case 05: return 31; break;
       case 06: return 30; break;
       case 07: return 31; break;
       case 10: return 31; break;
       case 11: return 30; break;
       case 12: return 31; break;
   }
   if($mes==8){
	   return 31;	   
   }
   
   if($mes==9){
	   return 30;	   
   }
}




function buscardatoscuentacreditosventa($buscar)
{
	

$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,vt.TipoPago,dias,cr.descuento,cr.interes,cr.tipo
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar'  order by  fechapago asc ";
 
$datos;
$idcredito = "";    
$plazo = "";  
$fechapago = "";          
$cod_venta ="";          
$Monto = "0"; 
$totalPago = "0"; 
$Esado = "";          
$Nro_recibo = "";
$TipoPago ="";
$nroCuota ="";
$dias ="10";
$interes ="0.01";
$descuento ="0";
$entrega ="0";
$nroCuotas ="0";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
if ($valor>0)
{
	$nroCuota=$valor;
while ($valor= mysqli_fetch_assoc($result))
{  

$tipo = utf8_encode($valor['tipo']); 
if($tipo!="ENTREGA"){
    
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

}else{
	$entrega = utf8_encode($valor['Monto']);
}

$nroCuotas=$nroCuotas+1;
}
}

 mysqli_close($mysqli);
$datos[0]=$idcredito;    
$datos[1]=$plazo;  
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
return $datos;


}



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



function refinanciarencambio($cod_venta,$metodopago,$iniciopago,$nroCuota,$total,$Monto,$dias,$interes){
	
	
	$mysqli=conectar_al_servidor();
	$sql= "Select idcredito,Monto,descuento,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.Tipo='Pago Cuota'),0) as totalPago
	from credito cr
	where cr.cod_venta='$cod_venta' ";
	
	$descuento=0;  
	
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $F=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     $idcredito=$valor['idcredito'];
			  $totalPago=utf8_encode($valor['totalPago']);
			 $cuota=utf8_encode($valor['Monto']);
			  $descuentocuota=utf8_encode($valor['descuento']);
			if($totalPago<=0){
				eliminarestecreditos($idcredito);
			}else{
				if($cuota>$totalPago){
				$totalPago=$descuentocuota+$totalPago;
				 editarestacuota($idcredito,$totalPago);
				}
				
			}
			  
			  
			  
	  }
 }
		

		
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
   if(($a+1)>=$cantidad){
	$s=$pendiente-$cuotaSobrante;
	if($s>0){
		$cuotaSobrante=$cuotaSobrante+$s;
	}
	}
	insertarcuotas(($a+1)."/".$nroCuota,$fecha, $cod_venta, $cuotaSobrante, "Pendiente"," ",$descuento,$dias,$interes, $cuotaSobrante);
	$pendiente=$pendiente-$cuotaSobrante;
	
				  $a++;
			 
			 }
			
			 
			 
			 actualizarMetodo($cod_venta,$metodopago);
			 cambiarplazos($cod_venta);
			  mysqli_close($mysqli);
			 $informacion =array("1" => "exito" );
echo json_encode($informacion);	
exit;
	
		
}


function cargarpagos($pagado,$cod_venta){
	$mysqli=conectar_al_servidor();
	$sql= "Select Monto,idcredito,fechapago,
	(select cod_cobradorFK from venta where cr.cod_venta=venta.cod_venta) as cod_cobradorFK,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
	from credito cr
	where cr.cod_venta='$cod_venta' and IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) < Monto order by cr.idcredito asc";
		
		
   
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
			  $cod_cobradorFK=utf8_encode($valor['cod_cobradorFK']);
			  $fechapago=utf8_encode($valor['fechapago']);
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
						 cargarPagosDeudas($pago,$fechapago,$cod_cobradorFK,$idcredito,$cod_venta);
					}		 
				 
			  
			  
			  
			  
	  }
 }
		
 mysqli_close($mysqli);

		
}

function  cargarPagosDeudas($Monto,$Fecha,$cod_cobradorFK,$cod_creditoFK,$cod_venta){
	  
	  
	 if($Monto!="0"){
	$mysqli=conectar_al_servidor();
	$consulta="Insert into pago (Monto,Fecha,cod_creditoFK,cod_cobradorFK,cod_venta_fk,comision,tipo) 
	values('$Monto','$Fecha','$cod_creditoFK','$cod_cobradorFK','$cod_venta',(select comision from venta where cod_venta='$cod_venta'),'Pago Cuota')";	
	$stmt = $mysqli->prepare($consulta);
	

if ( ! $stmt->execute()) {
   /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);
		 }
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

 mysqli_close($mysqli);
}


function actualizarEntrega($cod_venta,$entrega){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set pago=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$entrega,$cod_venta); 

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
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
			$consulta="delete from pago where cod_venta_fk='$cod_venta' ";/*Sentencia para insertar registros*/		

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
 /*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);
}
	
function insertarcuotas($plazo, $fechapago, $cod_venta, $Monto, $Esado,$Nro_recibo,$descuento,$dias,$interes,$total){
		$mysqli=conectar_al_servidor();
			$consulta="Insert into credito (plazo, 	fechapago, cod_venta, Monto, Esado,Nro_recibo,dias,interes,total,descuento,deudaInteres)
			values('$plazo','$fechapago','$cod_venta','$Monto','$Esado','$Nro_recibo','$dias','$interes','$total','$descuento',0)";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 mysqli_close($mysqli);
}
	


function buscardatoscuenta($buscar)
{
	

$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,vt.TipoPago,dias,cr.descuento
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' order by  fechapago asc ";
 

$idcredito = "";  
$plazo = "";  
$fechapago = "";          
$cod_venta ="";          
$Monto = "0"; 
$totalPago = "0"; 
$Esado = "";          
$Nro_recibo = "";
$TipoPago ="";
$nroCuota ="";
$dias ="";
$descuento ="0";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
if ($valor>0)
{
	$nroCuota=$valor;
while ($valor= mysqli_fetch_assoc($result))
{  


if($idcredito==""){
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
}


}
}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $fechapago,"3" => number_format($Monto,'0',',','.')   ,"4" => $TipoPago,"5" => $nroCuota,"6" => $dias,"7" => number_format($descuento,'0',',','.'));
echo json_encode($informacion);	
exit;



}


/*Buscar Registro en detalle*/
function buscarcreditos($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select vt.cod_clienteFK,cr.plazo,cr.deudaInteres,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,
datediff(cr.fechapago,'".$fechahoy."') as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,(totalinteres + deudaInteres) as totalinteres ,totaldeuda,cr.descuento,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha desc limit 1),0) as FechaUltimoPago,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito  and Monto!='0' order by pg.Fecha asc limit 1),0) as FechaPagoCredito,
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
$DeudaPendiente = "0";  
$TotalAPagar = "0";  
$TotalPagoEnInteres = "0";  
$TotalApagarSinInteres = "0";  
$nrodecuotasatrazado = "0";  
$TotalInteresApagar="0";




$paginaExtractoDetalle="";


$diff2="0";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";

$nombreClienteImprimir="";
	$NroVentaClienteImprimir="";
	$DetalleVentaClienteImprimir="";
	$TipoVentaClienteImprimir="";
	$FechaClienteImprimir="";
	
	$cod_venta="";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$diff2="0";
$FechaUltimoPago = utf8_encode($valor['FechaUltimoPago']);
$idcredito = utf8_encode($valor['idcredito']);
$deudaInteres = utf8_encode($valor['deudaInteres']);
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$totalPago = utf8_encode($valor['totalPago']); //TOTAL PAGO DEL CREDITO
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$diff = utf8_encode($valor['diff']);
$total_venta = utf8_encode($valor['total_venta']);
$interes = utf8_encode($valor['interes']);
$dias = utf8_encode($valor['dias']);
$total = utf8_encode($valor['total']);
$FechaPagoCredito = utf8_encode($valor['FechaPagoCredito']);  
$tinteres = utf8_encode($valor['totalinteres']);
$totaldeuda = utf8_encode($valor['totaldeuda']);
$entrega = utf8_encode($valor['entrega']);
$descuento = utf8_encode($valor['descuento']);// TOTAL DESCUENTO
$nroCancelado = utf8_encode($valor['nroCancelado']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$totalPagoCredito = utf8_encode($valor['totalPagoCuota']);//TOTAL PAGADO DE CUOTAS
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);// TOTAL PAGADO DE INTERES
$MontoConDescuento=$Monto-$descuento;//OBTENEMOS EL MONTO CON DESCUENTO
$totalDescuento=$totalDescuento+$descuento;//CALCULAMOS EL TOTAL DESCUENTO
/*CALCULAMOS EL SOBRANTE*/
$MontoSobrante=$Monto-$totalPagoCredito;
if($MontoCuotas==0){
$MontoCuotas=$Monto-$totalPagoCredito;
}
/*INICIALIZAMOS LAS VARIABLES*/
$deudaActua=0;
$total_interes=0;
$TotalSinInteres=0;
$deuda_Actual_interes=0;
$stylecolor=" ";
$event=" ";
//CONDICION PARA SABER SI ESE UN CREDITO CANCELDADO
if($nroCancelado==0){
	//CONDICION PARA SABER SI YA SE PAGO TODO
	if(($Monto+$totalPagoInteres)>($totalPago+$descuento)){
	//ESTADO DEL PAGO
	$Esado="Pendiente";
	//CALCULAMOS EL TOTAL SIN INTERES 
	$TotalSinInteres=$Monto-($totalPagoCredito+$descuento);	
	//CONDICION PARA SABER SI HAY DIAS ATRAZADOS
	if($diff<0){
	$diff=$diff*-1;
	editarDiasAtrazadosdesdecalcularcredito($cod_clienteFK,$diff);
	actualizardiasatrazadocredito($idcredito,$diff);
	$stylecolor=" background-color: #df4444;color:#ffffff";
	}else{
	$diff=0;
    }
	$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
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
	$i=($interes*($Monto - $totalPagoCredito))/100; //   	aca modifique para que me salga bien el interes
	$total_interes=($i*$diff);
	//CALCUMOS EL TOTAL A PAGAR
	$total=$montoIn+$total_interes;
	$deudaActua=$montoIn+$total_interes;
	//CARGAMOS EL TOTAL DEUDA SIN INTERES
	$deuda_Actual_interes=$total_interes;	
	actualizarTotalCuota($idcredito,$total,$total_interes,$total);	
	
	
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
	$event="obtenerdatosabmpagos(this)";
	
	}else{
	$Esado="Pagado";
	$stylecolor="background-color: #4caf50;color:#ffffff";
	$deudaActua=0;
	$total=0;
	$diff=0;
	$event="obtenerdatosabmpagosopciones(this)";
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
	$stylecolor="background-color: #4caf50;color:#ffffff";
	$deudaActua=0;
	$diff=0;
	$total=0;
	}
    	
	
}

 
$totalInteres=$totalInteres+$totalPagoInteres+$total_interes;
$TotalInteresActual=$TotalInteresActual+$total_interes;
// $deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$diff;
$totalPagado=$totalPagado+$totalPago;
$styleName="tableRegistroSearch";


//                                                                                        Este agregue yo ahora  y tambien agregue en el td
$DeudaInteres= $total_interes + $deudaInteres;


if($DeudaInteres<=0){
	$DeudaInteres=0;
}
$TotalDeuda=$DeudaInteres+ $TotalSinInteres;
$deuda=$deuda+$TotalDeuda;

$TotalInteresApagar = $TotalInteresApagar + $DeudaInteres;


$styleName=CargarStyleTable($styleName);

$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='$event' style='$stylecolor'>
<td id='td_datos_1' style='display:none' >".$idcredito."</td>
<td id='td_datos_2' style='width:5%' >".$plazo."</td>
<td id='td_datos_3' style='width:5%'>".$fechapago."</td>
<td id='td_datos_14' style='width:3%'>".$diff2."</td>
<td id='td_datos_15' style='width:3%'>".$diff."</td>
<td id='td_datos_5' style='width:5%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_4' style='display:none'>".$cod_venta."</td>
<td id='td_datos_10' style='display:none'>". number_format($totalPago,'0',',','.')."</td>
<td id='td_datos_11' style='width:5%'>". number_format($total_interes,'0',',','.')."</td>
<td id='td_datos_12' style='width:5%'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($total,'0',',','.')."</td>
<td id='td_datos_13' style='width:5%'>". number_format($totalPago,'0',',','.')."</td>
<td id='' style='width:5%'>". number_format($totalPagoCredito,'0',',','.')."</td>
<td id='' style='width:5%'>". number_format($totalPagoInteres,'0',',','.')."</td>
<td id='td_datos_20' style='width:5%'>". number_format($DeudaInteres,'0',',','.')."</td>
<td id='' style='width:5%'>". number_format($TotalSinInteres,'0',',','.')."</td>
<td id='td_datos_6' style='width:5%'>". number_format($TotalDeuda,'0',',','.')."</td>
<td id='td_datos_7' style='width:5%'>".$Esado."</td>
<td id='td_datos_8' style='display:none'>".$Nro_recibo."</td>
<td id='td_datos_9' style='display:none'>".$diff."</td>
</tr>
</table>
";
$paginaextracto.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='td_datos_2' style='width:10%' >".$plazo."</td>
<td id='td_datos_3' style='width:10%'>".$fechapago."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_11' style='width:10%'>". number_format($total_interes,'0',',','.')."</td>
<td id='td_datos_12' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($totalPago,'0',',','.')."</td>
<td id='td_datos_6' style='width:10%'>". number_format($TotalDeuda,'0',',','.')."</td>
<td id='td_datos_7' style='width:10%'>".$Esado."</td>
</tr>
</table>
";

}

if($DeudaPendiente==0){
	$SubTotalAPagar=$MontoCuotas;
	$MontoCuota=$MontoCuotas;
	$DeudaPendiente=$MontoCuotas;
}


}


$paginaExtractoDetalle= buscarDetalleVentaImprimir($cod_venta) ;


//                                                                                     edite la matriz 7


$DatosImprimir = BuscarDetalleVentaCredito($cod_venta);


	$nombreClienteImprimir=$DatosImprimir[0];
	$NroVentaClienteImprimir=$DatosImprimir[1];
	$DetalleVentaClienteImprimir=$DatosImprimir[2];
	$TipoVentaClienteImprimir=$DatosImprimir[3];
	$FechaClienteImprimir=$DatosImprimir[4];


 mysqli_close($mysqli);    
$informacion =array("1" => "exito","2" => $pagina,"12" => $paginaextracto,"3" =>number_format($totalPagado,'0',',','.') ,"4" =>number_format($deuda,'0',',','.'),"5" =>number_format($interes,'2',',','.'),"6" =>$dias, "7" =>number_format($TotalInteresApagar,'0',',','.')
, "9" => number_format($entrega,'0',',','.'),"8" => $diasatrazado, "11" => number_format($totalDescuento,'0',',','.')
, "13" => number_format($SubTotalAPagar,'0',',','.'),"14" => number_format($TotalCuotasPendientes,'0',',','.') ,
"15" => number_format($MontoCuota,'0',',','.'),"16" => number_format($totalInteres,'0',',','.') 
,"17" => number_format($DeudaPendiente,'0',',','.') ,"18" => number_format($TotalInteresActual,'0',',','.'),"19" => number_format($TotalPagoEnInteres,'0',',','.') ,"20" => $nombreClienteImprimir ,"21" => $NroVentaClienteImprimir ,"22" => $paginaExtractoDetalle ,"23" => $TipoVentaClienteImprimir ,"24" => $FechaClienteImprimir );
echo json_encode($informacion);	
exit;
}





function BuscarDetalleVentaCredito($cod_venta){
	$mysqli=conectar_al_servidor();	
	 
$sql= "Select  (select nombre_persona from persona where cod_clienteFK=cod_persona) as nombreCliente ,
 TipoVenta , puntoexpedicion , num_factura , fecha_venta , nombre_producto , cantidad_detalle , d.precio_producto, subtotal from venta
 inner join detalle_venta d on cod_venta=cod_ventaFK 
 inner join producto on cod_producto=cod_productoFK  where  cod_venta='".$cod_venta."'";
 
 // echo($sql);
 // exit;

   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $contador=0;
 $pagina="";
	$nombreClienteImprimir="";
	$NroVentaClienteImprimir="";
	$DetalleVentaClienteImprimir="";
	$TipoVentaClienteImprimir="";
	$FechaClienteImprimir="";
	
	$styleName="tableRegistroSearch";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		$nombreClienteImprimir=utf8_encode($valor['nombreCliente']);
		$TipoVentaClienteImprimir=utf8_encode($valor['TipoVenta']);
		$FechaClienteImprimir=utf8_encode($valor['fecha_venta']);
		
		$puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
		$num_factura=utf8_encode($valor['num_factura']);
		
		$nombre_producto=utf8_encode($valor['nombre_producto']);
		$cantidad_detalle=utf8_encode($valor['cantidad_detalle']);
		$precio_producto=utf8_encode($valor['precio_producto']);
		$subtotal=utf8_encode($valor['subtotal']);
		
		if($puntoexpedicion!=""){
			$NroVentaClienteImprimir = $puntoexpedicion."-".$num_factura;
		}else{
			$NroVentaClienteImprimir =  $num_factura;
		}
		$styleName=CargarStyleTable($styleName);
		 $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  >
<td id='td_datos_2' style='width:10%' >".$nombre_producto."</td>
<td id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.')."</td>
<td id='td_datos_5' style='width:10%'>". $cantidad_detalle."</td>
<td id='td_datos_4' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>
";

	  }
 }
 
	$datos[0]=$nombreClienteImprimir ;
	$datos[1]=$NroVentaClienteImprimir ;
	$datos[2]=$pagina ;
	$datos[3]=$TipoVentaClienteImprimir ;
	$datos[4]=$FechaClienteImprimir ;
	
 return $datos;

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


$nroFactura = "";   
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
     
$cantidad_detalle = utf8_encode($valor['cantidad_detalle']);      
$precio_producto = utf8_encode($valor['precio_producto']);  
$subtotal = utf8_encode($valor['subtotal']);  
$NombreProducto = utf8_encode($valor['NombreProducto']); 
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']); 
$num_factura = utf8_encode($valor['num_factura']);  


if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}


$pagina.="<table class='tableRegistroSearch2' style='border: solid 1px #a1a1a1;' >
<tr>
<td style='width:70%'>".$NombreProducto."</td>
<td style='width:30%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";

}
}
$datos[0]=$pagina;
return $datos;	

}




/*Buscar Registro en detalle*/
function buscarcreditoseditar($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,totalinteres,totaldeuda,cr.descuento,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
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
$controlStyle="";
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

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

$enabled="";
if($totalPago>0){
	$enabled=" disabled";
}
$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr  >
<td  style='width:10%' >".$plazo."</td>
<td  style='width:20%'><input id='inptDate_$idcredito' type='date' value='$fechapago' class='inputText'  ".$enabled." /></td>
<td  style='width:20%'><input name='inptMontoCreditoEditar' id='inptMonto_$idcredito' type='text' value='". number_format($Monto,'0',',','.')."' class='inputText' onkeyup='separadordemiles(this)' ".$enabled." /></td>
<td  style='width:20%'><input name='inptDescuentoCreditoEditar' id='inptDescuento_$idcredito' type='text' value='". number_format($descuento,'0',',','.')."' class='inputText'  onkeyup='separadordemiles(this)' /></td>
<td  style='width:10%'><input id='inptDias_$idcredito' type='text' value='".$dias."' class='inputText'  /></td>
<td  style='width:10%'><input id='inptInteres_$idcredito' type='text' value='".number_format($interes,'2',',','.')."' class='inputText'  /></td>
<td  style='width:10%' ><input type='Button'  value='Guardar' class='btn4' id='$idcredito' onclick='EditarEsteCredito(this)' style='background-color: #2196F3;'  /></td>
</tr>
</table>
";

}
}

 mysqli_close($mysqli);    
$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}


/*Buscar Registro en detalle*/
//NO UTILIZADO VERIFICAR 
function buscarcreditoenrenfi($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.total_venta,interes,dias,vt.pago as entrega,vt.TipoPago,
total,totalinteres,totaldeuda,cr.descuento,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' ";
 
$pagina = "";  
$interes = "0";  
$diasatrazado = "0";  
$dias = "0";  
$totalPagado = "0";  
$total_venta = "0";  
$deuda = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
$entrega = "0";  
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
$controlStyle="";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$idcredito = utf8_encode($valor['idcredito']);/*Obtenemos el registro mediante el nombre del atributo */      
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
$TipoPago = utf8_encode($valor['TipoPago']);
$descuento = utf8_encode($valor['descuento']);
$nroCancelado = utf8_encode($valor['nroCancelado']);
$totalDescuento=$totalDescuento+$descuento;
$submonto=$Monto-$descuento;
$deudaActua=0;
$total_interes=$tinteres;
$totalPagado=$totalPagado+$totalPago;
$stylecolor=" ";
$styleName="tableRegistroSearch";

if($nroCancelado==0){
if(($submonto+$tinteres)>$totalPago){

if($diff<0){
	$diff=$diff*-1;
	
}else{
		$diff=0;
}

	
	$Esado="Pendiente";

if($totalPago>0){
$event="obtenerDatosCreditosRefinEditar(this)";

	}else{
		
$event="obtenerDatosCreditosRefinanciacion(this)";
		}

if($Esado=="Pendiente"){
$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
					
			
				$fechahoy=date('Y-m-d');	
				$datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy))));
				$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));
				$interval=$datetime2->diff($datetime1);

		$diff=$interval->format('%a');
	$diasGracia=$diff-$dias;
	
			   if($diasGracia>0){
				    if($interes!=0){
				  
			    $montoIn=$Monto-$totalPago;
			  $i=($interes*($Monto))/100;
			 
			  $total_interes=($i*$diff);
			  $t=($submonto)+$total_interes;
			  $totalInteres=$totalInteres+$total_interes;
			  $total=$t;
		    $deudaActua=$t-$totalPago;
			actualizarTotalCuota($idcredito,$total,$total_interes,$t);
					}else{
						//ejecuta cuando no tiene interes
						 $interes=0;
						$deudaActua=$submonto-$totalPago;
						 $total=$submonto-$totalPago;
					actualizarTotalCuota($idcredito,$total,0,$submonto);
					}
			   }else{
				      $interes=0;
                    $total=$submonto-$totalPago;
					$deudaActua=$total;
					actualizarTotalCuota($idcredito,$total,0,$submonto);
					 $diff="0";
				 
			   }
				}else{
					$interes=0;
                    $total=$submonto-$totalPago;
					$deudaActua=$total;
					actualizarTotalCuota($idcredito,$total,0,$submonto);
				}
}else{
	
}

}else{
	$event="obtenerPagosCreditosRefinanciacion(this)";
	$stylecolor="background-color: #ccc;color:#000";
	$Esado="Pagado";
	$deudaActua=0;
	$diff=0;
}

}else{
	if(($submonto+$tinteres)>$totalPago){

	 $diff="0";
	
	 $deudaActua=($submonto+$tinteres)-$totalPago;
	 $total=$submonto-$totalPago;
	 $stylecolor="text-decoration: line-through;";
	 if($totalPago>0){
$event="obtenerDatosCreditosRefinEditar(this)";

	}else{
		
$event="obtenerDatosCreditosRefinanciacion(this)";
		}
	}else{
		$Esado="Pagado";
	$stylecolor="background-color: #ccc;color:#000";
	$deudaActua=0;
	$diff=0;
	$event="obtenerPagosCreditosRefinanciacion(this)";
	}
	$event="";
}

$deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$diff;

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='$event' style='$stylecolor'>
<td id='td_datos_1' style='display:none' >".$idcredito."</td>
<td id='td_datos_2' style='width:10%' >".$plazo."</td>
<td id='td_datos_3' style='width:10%'>".$fechapago."</td>
<td id='td_datos_5' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_4' style='display:none'>".$cod_venta."</td>
<td id='td_datos_10' style='display:none'>". number_format($totalPago,'0',',','.')."</td>

<td id='td_datos_11' style='width:10%'>". number_format($total_interes,'0',',','.')."</td>
<td id='td_datos_12' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($totalPago,'0',',','.')."</td>
<td id='td_datos_6' style='width:10%'>". number_format($deudaActua,'0',',','.')."</td>
<td id='td_datos_7' style='display:none'>".$Esado."</td>
<td id='td_datos_8' style='display:none'>".$Nro_recibo."</td>
<td id='td_datos_9' style='display:none'>".$diff."</td>
<td id='td_datos_13' style='display:none'>".$TipoPago."</td>
<td id='td_datos_15' style='display:none'>".$dias."</td>
<td id='td_datos_14' style='display:none'>".number_format($interes,'2',',','.')."</td>
</tr>
</table>
";


}
}
 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.') ,"4" =>number_format($deuda,'0',',','.'),"5" =>number_format($interes,'2',',','.'),"6" =>$dias, "7" =>number_format($totalInteres,'0',',','.'), "9" => number_format($entrega,'0',',','.'),"8" => $diasatrazado, "11" => number_format($totalDescuento,'0',',','.'));
echo json_encode($informacion);	
exit;
}

/*Buscar Registro en detalle*/
function buscarcreditoshistorialventa($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,vt.total_venta,interes,dias,vt.pago as entrega,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1) as fechapagado
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' ";
 $pagina = "";  
$interes = "0";  
$diasatrazado = "0";  
$dias = "0";  
$totalPagado = "0";  
$total_venta = "0";  
$deuda = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
$entrega = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$idcredito = utf8_encode($valor['idcredito']);
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$total_venta = utf8_encode($valor['total_venta']);
$interes = utf8_encode($valor['interes']);
$dias = utf8_encode($valor['dias']);
$entrega = utf8_encode($valor['entrega']);
$fechapagado = utf8_encode($valor['fechapagado']);
$nroCancelado = utf8_encode($valor['nroCancelado']);

$datos=calcularintereses2($idcredito,0,0,"2","2","1","no");
$descuento=$datos[0];
$total_interes=$datos[1];
$total=$datos[2];
$totalPago=$datos[3];
$deudaActua=$datos[4];
$TotalDiasAtrasado=$datos[5];
//$datos[6]=$nrodecuotasatrazado;
//$datos[7]=$TotalApagarSinInteres;
//$datos[8]=$DeudaPendiente;
$stylecolor=$datos[9];
$totalDescuento=$totalDescuento+$descuento;
$totalPagado=$totalPagado+$totalPago;

$deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$TotalDiasAtrasado;


$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' style='".$stylecolor."' >
<td id='' style='width:10%' >".$plazo."</td>
<td id='' style='width:10%'>".$fechapago."</td>
<td id='' style='width:10%'>".$fechapagado."</td>
<td id='' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='display:none'>".$cod_venta."</td>
<td id='' style='display:none'>". number_format($totalPago,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total_interes,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($totalPago,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($deudaActua,'0',',','.')."</td>
</tr>
</table>
";


}
}
$deuda=$total_venta-($totalPagado+$entrega);
 mysqli_close($mysqli);    
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($totalPagado,'0',',','.') ,"4" =>number_format($deuda,'0',',','.'),"5" =>number_format($interes,'2',',','.'),"6" =>$dias, "7" =>number_format($totalInteres,'0',',','.'), "9" => number_format($entrega,'0',',','.'),"8" => $diasatrazado);
echo json_encode($informacion);	
exit;
}


function cuentasacobrardetallado($cobrador,$cliente,$fecha1,$fecha2,$zona,$cod_local,$tipo)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicionCodLocal=" "; 
if($cod_local!=""){
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
 }
	$condicionZona=" ";
	if($zona!=""){
	 $condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=vt.cod_clienteFK  and idzonaFk='$zona') > 0 ";
	}


	$condicioncliente="";
	if($cliente!=""){
	 $condicioncliente=" and (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) like '%$cliente%' ";
	}
	
	
	$condicioncobrador="";
	if($cobrador!=""){
	 $condicioncobrador=" and vt.cob_ex = '$cobrador' ";
	}



	$condicionFiltro=" and cr.fechapago between '$fecha1' and '$fecha2'";



	$sql= "select (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,vt.cod_clienteFK,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as documento,
IFNULL((Select referencias from referenciascliente rf where rf.cod_clienteFk=vt.cod_clienteFK order by idreferenciascliente desc limit 1),'') as referencia,
 (Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cr.cod_venta)) as nombrezona,
(Select direccion from persona where cod_persona=vt.cod_clienteFK) as direccion,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 
 where IFNULL((select sum(pg.Monto) from pago pg  where cr.idcredito=pg.cod_creditoFK and Tipo='Pago Cuota'),0) <
	(cr.Monto - cr.descuento) and
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  
".$condicionFiltro.$condicionZona.$condicionCodLocal.$condicioncliente.$condicioncobrador." group by vt.cod_clienteFK order by vt.cod_venta desc limit 5";
 
// echo($sql);
// exit;

 
$pagina = "";  
$totalPagado = "0";  
$deuda = "0";  
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
$detallesventa="";
$totaldeudas="0";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$referencia = utf8_encode($valor['referencia']);
$documento = utf8_encode($valor['documento']);
$clientenombre = utf8_encode($valor['clientenombre']);
$direccion = utf8_encode($valor['direccion']);
$nombrezona = utf8_encode($valor['nombrezona']);
$telefono = utf8_encode($valor['telefono']);
$telefono = utf8_encode($valor['telefono']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);

$detallesventas=buscarcreditospendientesclientes($cod_clienteFK,$tipo,$condicionFiltro,$condicionZona,$condicionCodLocal);

$referenciaCliente=buscarReferenciaCliente($cod_clienteFK);

$pagina.="
<table class='tableRegistroSearch1' >
<tbody>
<tr >
<td  style='width:10%;'><b>DOCUMENTO:</b> <br>".$documento."</td>
<td  style='width:20%;'><b>CLIENTE:</b> <br> ".$clientenombre."</td>
<td  style='width:20%;'><b>DIRECCIÓN:</b>  <br>".$direccion.".</td>
<td  style='width:25%;'><b>REFERENCIA:</b>  <br>".$referenciaCliente.".</td>
<td  style='width:15%;'><b>ZONA:</b> <br> ".$nombrezona."</td>
<td  style='width:10%;'><b>TELEF.:</b> <br>".$telefono.".</td>
</tr>
</tbody>
</table>".$detallesventas[0];
$totaldeudas=$totaldeudas+$detallesventas[1];

}
}



	$sql= "select vt.cod_clienteFK
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)>0 and
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  
".$condicionFiltro.$condicionZona.$condicionCodLocal.$condicioncliente.$condicioncobrador." group by vt.cod_clienteFK ";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;

 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3"=> number_format($nroRegistro,'0',',','.'),"4"=> number_format($totaldeudas,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function mascuentasacobrardetallado($cobrador,$cliente,$fecha1,$fecha2,$zona,$cod_local,$tipo,$totalACobrar,$registrocargado)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicionCodLocal=" "; 
if($cod_local!=""){
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
 }
	$condicionZona=" ";
	if($zona!=""){
	 $condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=vt.cod_clienteFK  and idzonaFk='$zona') > 0 ";
	}
	
		$condicioncliente="";
	if($cliente!=""){
	 $condicioncliente=" and (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) like '%$cliente%' ";
	}
	
	$condicioncobrador="";
	if($cobrador!=""){
	 $condicioncobrador=" and vt.cob_ex = '$cobrador' ";
	}


$condicionFiltro="";


$condicionFiltro=" and cr.fechapago between '$fecha1' and '$fecha2'";



	$sql= "select (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,vt.cod_clienteFK,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as documento,
IFNULL((Select referencias from referenciascliente rf where rf.cod_clienteFk=vt.cod_clienteFK order by idreferenciascliente desc limit 1),'') as referencia,
 (Select nombre from zona z where z.idzona=(Select idzonaFk from cliente pr inner join venta vt on vt.cod_clienteFK=pr.cod_cliente where vt.cod_venta=cr.cod_venta)) as nombrezona,
(Select direccion from persona where cod_persona=vt.cod_clienteFK) as direccion,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)>0 and
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  
".$condicionFiltro.$condicionZona.$condicionCodLocal.$condicioncliente.$condicioncobrador." group by vt.cod_clienteFK order by vt.cod_venta desc   limit ".$registrocargado." , 5 ";
 


 
$pagina = "";  
$totalPagado = "0";  
$deuda = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$controlStyle="";
$controlVentas="";
$detallesventa="";
$totaldeudas=$totalACobrar;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$referencia = utf8_encode($valor['referencia']);
$documento = utf8_encode($valor['documento']);
$clientenombre = utf8_encode($valor['clientenombre']);
$direccion = utf8_encode($valor['direccion']);
$nombrezona = utf8_encode($valor['nombrezona']);
$telefono = utf8_encode($valor['telefono']);
$telefono = utf8_encode($valor['telefono']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);

$referenciaCliente=buscarReferenciaCliente($cod_clienteFK);

$detallesventas=buscarcreditospendientesclientes($cod_clienteFK,$tipo,$condicionFiltro,$condicionZona,$condicionCodLocal);
$pagina.="
<table class='tableRegistroSearch1' >
<tbody>
<tr >
<td  style='width:10%;'><b>DOCUMENTO:</b> <br>".$documento."</td>
<td  style='width:20%;'><b>CLIENTE:</b> <br> ".$clientenombre."</td>
<td  style='width:20%;'><b>DIRECCIÓN:</b>  <br>".$direccion.".</td>
<td  style='width:25%;'><b>REFERENCIA:</b>  <br>".$referenciaCliente.".</td>
<td  style='width:15%;'><b>ZONA:</b> <br> ".$nombrezona."</td>
<td  style='width:10%;'><b>TELEF.:</b> <br>".$telefono.".</td>
</tr>
</tbody>
</table>".$detallesventas[0];
$totaldeudas=$totaldeudas+$detallesventas[1];

}
}

 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3"=> number_format($nroRegistro,'0',',','.'),"4"=> number_format($totaldeudas,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarReferenciaCliente($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "Select concat(direccion,' - ',referencias) as referencias from referenciascliente rf where rf.cod_clienteFk=$buscar  ";
 

$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$pagina="";
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

$style='font-size: 11px; font-family: "Merriweather Sans", Arial;';

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$referencias = utf8_encode($valor['referencias']);

$pagina.="<table style='font-family: arial;font-size: 11px;' >
<tr >
<td style='width:10%;color: rgb(27, 27, 27); $style'>".$referencias."</td>
</tr>
</table>";

}
}
mysqli_close($mysqli); 
return $pagina;

}



/*Buscar Registro en detalle*/
function buscarcreditospendientesclientes($buscar,$tipo,$condicionFiltro,$condicionZona,$condicionCodLocal)
{
$mysqli=conectar_al_servidor();
if($tipo=="2"){
	$condicionFiltro="";
}
$fechahoy=date('Y-m-d');	
$sql= "select vt.cod_clienteFK,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.total_venta,interes,dias,vt.pago as entrega,cr.deudaInteres,
total,(totalinteres + deudaInteres) as totalinteres,totaldeuda,cr.descuento,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito and Monto!='0' order by pg.Fecha desc limit 1),0) as FechaUltimoPago,IFNULL((select (pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito  and Monto!='0' order by pg.Fecha asc limit 1),0) as FechaPagoCredito,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota' ),0) as totalPagoCuota,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_clienteFK='$buscar' and ((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))>0 ".$condicionFiltro.$condicionZona.$condicionCodLocal." "; 
 
 
 // echo($sql);
 // exit;
 
$pagina = "<table class='tableCabeceraRegistro' style='width:100%'>
<tbody>
<tr>
<td class='td_registro' style='width:35%;'>
DETALLES DE CUOTAS
</td>
<td class='td_registro' style='width:5%;'>
CUOTA
</td>
<td class='td_registro' style='width:5%;'>
VENC.
</td>
<td class='td_registro' style='width:5%;'>
MONTO
</td>
<td class='td_registro' style='width:5%;'>
PAGADO
</td>
<td class='td_registro' style='width:5%;'>
SALDO
</td>
</tr>
</tbody>
</table>";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlventa="";
$paginadetalle="";
$totaldeudas="0";
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$deudaInteres = utf8_encode($valor['deudaInteres']);
$FechaPagoCredito = utf8_encode($valor['FechaPagoCredito']);  
$FechaUltimoPago = utf8_encode($valor['FechaUltimoPago']);
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
$deudaActua=0;
$total_interes=0;
$TotalSinInteres=0;
$deuda_Actual_interes=0;
$stylecolor=" ";
$event=" ";
$i=0;
if($nroCancelado==0){
	
	if(($Monto+$totalPagoInteres)>($totalPago+$descuento)){
	$Esado="Pendiente";
	$TotalSinInteres=$Monto-($totalPagoCredito+$descuento);	
	if($diff<0){
	$diff=$diff*-1;
	$stylecolor=" background-color: #313030;color:#FFEB3B";
	}else{
	$diff=0;
    }
	$control=verificar_fecha_expiracion($fechapago);
	if($control=="si"){
	if($interes!=0){
	/*$fechahoy=date('Y-m-d');	
	$datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy))));
	// $datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));
	// $interval=$datetime2->diff($datetime1);
    // $diff=$interval->format('%a');
	
	
	$datetime3= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));	
	$Fecha1=strtotime($FechaUltimoPago);
	$Fecha2=strtotime($fechapago);
	if($FechaPagoCredito=="0" ){
		$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));	
	}else{
		if($Fecha1 < $Fecha2){
			
				$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));		
			}else{
				$dias=0;
				$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$FechaUltimoPago))));		
			}		
	}
								$interval=$datetime2->diff($datetime1);
								$diffDias=$interval->format('%a');						
								
								
								$interval2=$datetime3->diff($datetime1);
								$diffDias2=$interval2->format('%a');
								
								
								
								$interval=$datetime2->diff($datetime1);
								$diffmes=$interval->format('%m');
								$diffanho=$interval->format('%y')*12;
								
								$diffmes=$diffmes + $diffanho ;
								
								$diasGracia=$diffDias-$dias;
								
								if($diasGracia>0){
									// if($diffmes==0){
										$diffmes= $diffmes + 1 ;
									// }
									
								}*/
								
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
		
	$montoIn=$MontoConDescuento-$totalPagoCredito;	
	/*CALCULAMOS EL INTERES*/
	$i=($interes*($Monto - $totalPagoCredito))/100;//                                                       aca modifique para que me salga bien el interes
	$total_interes=($i*($diff -1));
	//CALCUMOS EL TOTAL A PAGAR
	$total=$montoIn+$total_interes;
	$deudaActua=$total_interes+$montoIn  + $deudaInteres;//+
		
	// $montoIn=$Monto-$totalPagoCredito;	
	// $i=($interes*($montoIn))/100;
	// $total_interes=($i*$diff);
	// $t=$montoIn+$total_interes;
	// $deudaActua=($montoIn+$total_interes)-$descuento;
	 // $total=$t;
	// //$deudaActua=$t-$totalPagoCredito-$totalPagoInteres;
	// $deuda_Actual_interes=$total_interes;	
	}else{
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;		
	}	
	}else{	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;	
	}
			
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;	
	}
	
	if($controlventa!=$cod_venta){
		$paginadetalle=buscar_detalles_venta_en_cuentas_a_cobrar($cod_venta);
		$controlventa=$cod_venta;
	}
	
	
	$styleName=CargarStyleTable($styleName);
	$pagina.="<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td id='' style='width:35%;text-align:left' >".$paginadetalle."</td>
<td id='' style='width:5%' >".$plazo."</td>
<td id='' style='width:5%'>".$fechapago."</td>
<td id='' style='width:5%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($total_interes,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='width:5%'>". number_format($totalPago,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($totalPagoCredito,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($totalPagoInteres,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($deuda_Actual_interes,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($TotalSinInteres,'0',',','.')."</td>
<td id='' style='width:5%'>". number_format($deudaActua,'0',',','.')."</td>
</tr>
</table>";

	$totaldeudas=$totaldeudas+$deudaActua;
	
	
	
	}	
	}
    }
	  
	  $pagina.="<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>
<tr>
<td id='' style='width:59%;text-align:left' ></td>
<td id='' style='width:5%'>". number_format($totaldeudas,'0',',','.')."</td>
</tr>
</table>";
	
    }

 mysqli_close($mysqli);    
 $datos[0]=$pagina;
 $datos[1]=$totaldeudas;
 return $datos;
 
}


function cuentasacobrar($filtro,$fecha1,$fecha2,$cliente,$documento,$telefono,$producto,$filtrofecha,$codlocal,$vendedor)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	


	$condicionVendedor="";
	 if($vendedor!=""){
	   $condicionVendedor="and  Vendedor1 ='".$vendedor."'";		
	 }


$condicionCodLocal=" "; 
if($codlocal!=""){
$condicionCodLocal=" and vt.cod_local='$codlocal' ";
 }
	$condicioncliente=" ";
	if($cliente!=""){
	 $condicioncliente=" and  (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%' ";
	}	
	$condiciondocumento=" ";
	if($documento!=""){
	 $condiciondocumento=" and  (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) = '".$documento."' ";
	}	
	$condiciontelefono=" ";
	if($telefono!=""){
	  $condiciontelefono=" and  (Select telefono from persona where cod_persona=cod_clienteFK) like '%".$telefono."%' ";
	}	
	
	$condicionfechafiltro=" ";
	if($filtrofecha!=""){
	 $condicionfechafiltro=" and  cr.fechapago='$filtrofecha' ";
	}

$condicionFecha="";
if($filtro=="1")
{
$condicionFecha=" and cr.fechapago>='$fecha1' and cr.fechapago<='$fecha2'";

}	
if($filtro=="3")
{
$condicionFecha=" and cr.fechapago<='$fecha1' ";
	
}
if($filtro=="4")
{
$condicionFecha=" and cr.fechapago>='$fecha1' ";
	
}	

	$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.tipo_comprobante,vt.puntoexpedicion,vt.cod_cobradorFK,vt.num_factura,vt.total_venta,vt.num_factura,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as totalPago,cr.totalinteres,cr.descuento,vt.TipoVenta,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as creditopagado,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as documento,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
		(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK) as cobradornombre
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)>0 and
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  
".$condicionCodLocal.$condicioncliente.$condiciondocumento.$condiciontelefono.$condicionfechafiltro.$condicionFecha.$condicionVendedor."  group by cr.cod_venta order by cr.fechapago asc , vt.cod_venta asc limit 300 ";
 

// echo($sql);
// exit;

 
$pagina = "";  
$totalPagado = "0";  
$totalacobrar = "0";  
$deuda = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$nombrevendedor1 = utf8_encode($valor['nombrevendedor1']); 
$idcredito = utf8_encode($valor['idcredito']);    
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$totalPago = utf8_encode($valor['totalPago']); 
$creditopagado = utf8_encode($valor['creditopagado']); 
$diff = utf8_encode($valor['diff']);
$clientenombre = utf8_encode($valor['clientenombre']);
$cobradornombre = utf8_encode($valor['cobradornombre']);
$cod_cobradorFK = utf8_encode($valor['cod_cobradorFK']);
$total_venta = utf8_encode($valor['total_venta']);
$num_factura = utf8_encode($valor['num_factura']);
$nombrelocal = utf8_encode($valor['nombrelocal']);
$telefono = utf8_encode($valor['telefono']);
$descuento = utf8_encode($valor['descuento']);
$tipo_comprobante = utf8_encode($valor['tipo_comprobante']);
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);
$nroCouta = utf8_encode($valor['nroCouta']);
$TipoVenta = utf8_encode($valor['TipoVenta']);
$documento = utf8_encode($valor['documento']);

$datos=calcularintereses2($cod_venta,0,0,"2","3","2","si");

$deuda=$deuda+$datos[4];
$totalEnDescuento=$datos[0];
$TotalEnInteres=$datos[1];
$TotalEnDeuda=$datos[2];
$TotalEnPagado=$datos[3];
$TotalAPagar=$datos[4];
$TotalDiasAtrasado=$datos[15];
$cuotasatrazadas=$datos[6];
$TotalApagarSinInteres=$datos[7];
$DeudaPendiente=$datos[8];
$TotalInteresPagado=$datos[12];
$TotalPagadoSinInteres=$datos[13];


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
  $totalacobrar=$totalacobrar+$DeudaPendiente;

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatoscuentaacobrar(this)' >
<td id='td_datos_1' style='display:none' >".$cod_venta."</td>
<td id='td_datos_2' style='display:none' >".$num_factura."</td>
<td id='' style='display:none' >".$plazo."</td>
<td id='td_datos_24' style='width:10%;' >".$clientenombre."</td>
<td id='td_datos_25' style='width:5%;' >".$documento."</td>
<td id='' style='width:5%;' >".$telefono."</td>
<td id='' style='width:5%;' >".$nrof."</td>
<td id='' style='width:10%;text-align:center' >".buscar_detalles_venta_en_cuentas_a_cobrar($cod_venta)."</td>
<td id='td_datos_5' style='display:none' >".$cobradornombre."</td>
<td id='td_datos_12' style='display:none'>". number_format($total_venta,'0',',','.')."</td>
<td id='td_datos_3' style='width:5%' >".$fechapago."</td>
<td id='td_datos_19' style='display:none' >".$cuotas."</td>
<td id='td_datos_6' style='display:none'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_18' style='display:none'>". number_format($totalEnDescuento,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($TotalInteresPagado,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($TotalPagadoSinInteres,'0',',','.')."</td>
<td id='td_datos_13' style='display:none'>". number_format($TotalEnPagado,'0',',','.')."</td>
<td id='td_datos_17' style='display:none'>". number_format($TotalEnInteres,'0',',','.')."</td>
<td id='td_datos_20' style='width:3%'>".$cuotasatrazadas."</td>
<td id='td_datos_10' style='width:3%'>".$TotalDiasAtrasado."</td>
<td id='td_datos_22' style='width:5%'>". number_format($DeudaPendiente,'0',',','.')."</td>
<td id='td_datos_11' style='display:none'>". number_format($TotalEnDeuda,'0',',','.')."</td>
<td id='td_datos_14' style='width:5%'>". number_format($TotalAPagar,'0',',','.')."</td>
<td id='td_datos_7' style='display:none'>". number_format($totalPagado,'0',',','.')."</td>
<td id='td_datos_8' style='display:none'>". number_format($total_venta,'0',',','.')."</td>
<td id='td_datos_9' style='display:none'>".$cod_cobradorFK."</td>
<td id='' style='width:5%'>". $nombrelocal."</td>
<td id='td_datos_15' style='display:none'>". $tipo_comprobante."</td>
<td id='td_datos_16' style='display:none'>". $puntoexpedicion."</td>
<td id='td_datos_21' style='display:none'>".  number_format($TotalApagarSinInteres,'0',',','.')."</td>
<td id='td_datos_23' style='width:5%'>". $nombrevendedor1."</td>
</tr>
</table>";




}
}


$sql= "select cr.plazo
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)>0 and
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  
".$condicionCodLocal.$condicioncliente.$condiciondocumento.$condiciontelefono.$condicionfechafiltro.$condicionFecha.$condicionVendedor."  group by cr.cod_venta order by cr.fechapago asc ";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;

 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($nroRegistro,'0',',','.') ,"4" =>number_format($deuda,'0',',','.'),"5" =>number_format($totalacobrar,'0',',','.'),"99"=> $nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function mascuentasacobrar($filtro,$fecha1,$fecha2,$cliente,$documento,$telefono,$producto,$filtrofecha,$codlocal,$registrocargado,$totalcobrar,$totaldeuda,$vendedor)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	

$condicionVendedor="";
	 if($vendedor!=""){
	   $condicionVendedor="and  Vendedor1 ='".$vendedor."'";		
	 }


$condicionCodLocal=" "; 
if($codlocal!=""){
$condicionCodLocal=" and vt.cod_local='$codlocal' ";
 }
	$condicioncliente=" ";
	if($cliente!=""){
	 $condicioncliente=" and  (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%' ";
	}	
	$condiciondocumento=" ";
	if($documento!=""){
	 $condiciondocumento=" and  (Select ci_cliente from cliente where cod_cliente=cod_clienteFK) = '".$documento."' ";
	}	
	$condiciontelefono=" ";
	if($telefono!=""){
	 $condiciontelefono=" and  (Select telefono from persona where cod_persona=cod_clienteFK) like '%".$telefono."%' ";
	}	
	
	$condicionfechafiltro=" ";
	if($filtrofecha!=""){
	 $condicionfechafiltro=" and  cr.fechapago='$filtrofecha' ";
	}

$condicionFecha="";
if($filtro=="1")
{
$condicionFecha=" and cr.fechapago>='$fecha1' and cr.fechapago<='$fecha2'";

}	
if($filtro=="3")
{
$condicionFecha=" and cr.fechapago<='$fecha1' ";
	
}
if($filtro=="4")
{
$condicionFecha=" and cr.fechapago>='$fecha1' ";
	
}	

	

	$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.tipo_comprobante,vt.puntoexpedicion,vt.cod_cobradorFK,vt.num_factura,vt.total_venta,vt.num_factura,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as totalPago,cr.totalinteres,cr.descuento,vt.TipoVenta,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as creditopagado,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK) as documento,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
		(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK) as cobradornombre
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where (IFNULL((select sum(pg.Monto) from credito pg where pg.idcredito=cr.idcredito),0)- IFNULL((select sum(pg.descuento) from credito pg where pg.idcredito=cr.idcredito),0))-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0)>0 and
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  
".$condicionCodLocal.$condicioncliente.$condiciondocumento.$condiciontelefono.$condicionfechafiltro.$condicionFecha.$condicionVendedor."  group by cr.cod_venta order by cr.fechapago asc , vt.cod_venta asc limit ".$registrocargado.", 100 ";
 


 
$pagina = "";  
$totalPagado = "0";  
$deuda = $totaldeuda;  
$totalacobrar = $totalcobrar;  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$controlStyle="";
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$nombrevendedor1 = utf8_encode($valor['nombrevendedor1']);    
$idcredito = utf8_encode($valor['idcredito']);    
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$totalPago = utf8_encode($valor['totalPago']); 
$creditopagado = utf8_encode($valor['creditopagado']); 
$diff = utf8_encode($valor['diff']);
$clientenombre = utf8_encode($valor['clientenombre']);
$cobradornombre = utf8_encode($valor['cobradornombre']);
$cod_cobradorFK = utf8_encode($valor['cod_cobradorFK']);
$total_venta = utf8_encode($valor['total_venta']);
$num_factura = utf8_encode($valor['num_factura']);
$nombrelocal = utf8_encode($valor['nombrelocal']);
$telefono = utf8_encode($valor['telefono']);
$descuento = utf8_encode($valor['descuento']);
$tipo_comprobante = utf8_encode($valor['tipo_comprobante']);
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);
$nroCouta = utf8_encode($valor['nroCouta']);
$TipoVenta = utf8_encode($valor['TipoVenta']);
$documento = utf8_encode($valor['documento']);

$datos=calcularintereses2($cod_venta,0,0,"2","3","2","si");

$deuda=$deuda+$datos[4];
$totalEnDescuento=$datos[0];
$TotalEnInteres=$datos[1];
$TotalEnDeuda=$datos[2];
$TotalEnPagado=$datos[3];
$TotalAPagar=$datos[4];
$TotalDiasAtrasado=$datos[15];
$cuotasatrazadas=$datos[6];
$TotalApagarSinInteres=$datos[7];
$DeudaPendiente=$datos[8];
$TotalInteresPagado=$datos[12];
$TotalPagadoSinInteres=$datos[13];


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
 
$totalacobrar=$totalacobrar+$DeudaPendiente;

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatoscuentaacobrar(this)' >
<td id='td_datos_1' style='display:none' >".$cod_venta."</td>
<td id='td_datos_2' style='display:none' >".$num_factura."</td>
<td id='' style='display:none' >".$plazo."</td>
<td id='td_datos_24' style='width:10%;' >".$clientenombre."</td>
<td id='td_datos_25' style='width:5%;' >".$documento."</td>
<td id='' style='width:5%;' >".$telefono."</td>
<td id='' style='width:5%;' >".$nrof."</td>
<td id='' style='width:10%;text-align:center' >".buscar_detalles_venta_en_cuentas_a_cobrar($cod_venta)."</td>
<td id='td_datos_5' style='display:none' >".$cobradornombre."</td>
<td id='td_datos_12' style='display:none'>". number_format($total_venta,'0',',','.')."</td>
<td id='td_datos_3' style='width:5%' >".$fechapago."</td>
<td id='td_datos_19' style='display:none' >".$cuotas."</td>
<td id='td_datos_6' style='display:none'>". number_format($Monto,'0',',','.')."</td>
<td id='td_datos_18' style='display:none'>". number_format($totalEnDescuento,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($TotalInteresPagado,'0',',','.')."</td>
<td id='' style='display:none'>". number_format($TotalPagadoSinInteres,'0',',','.')."</td>
<td id='td_datos_13' style='display:none'>". number_format($TotalEnPagado,'0',',','.')."</td>
<td id='td_datos_17' style='display:none'>". number_format($TotalEnInteres,'0',',','.')."</td>
<td id='td_datos_20' style='width:3%'>".$cuotasatrazadas."</td>
<td id='td_datos_10' style='width:3%'>".$TotalDiasAtrasado."</td>
<td id='td_datos_22' style='width:5%'>". number_format($DeudaPendiente,'0',',','.')."</td>
<td id='td_datos_11' style='display:none'>". number_format($TotalEnDeuda,'0',',','.')."</td>
<td id='td_datos_14' style='width:5%'>". number_format($TotalAPagar,'0',',','.')."</td>
<td id='td_datos_7' style='display:none'>". number_format($totalPagado,'0',',','.')."</td>
<td id='td_datos_8' style='display:none'>". number_format($total_venta,'0',',','.')."</td>
<td id='td_datos_9' style='display:none'>".$cod_cobradorFK."</td>
<td id='' style='width:5%'>". $nombrelocal."</td>
<td id='td_datos_15' style='display:none'>". $tipo_comprobante."</td>
<td id='td_datos_16' style='display:none'>". $puntoexpedicion."</td>
<td id='td_datos_21' style='display:none'>".  number_format($TotalApagarSinInteres,'0',',','.')."</td>
<td id='td_datos_23' style='width:5%'>". $nombrevendedor1."</td>
</tr>
</table>";




}
}


 mysqli_close($mysqli);   
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($nroRegistro,'0',',','.') ,"4" =>number_format($deuda,'0',',','.'),"5" =>number_format($totalacobrar,'0',',','.'),"99" =>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function cuentasExpCobrados($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	

$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,dias,vt.pago as entrega,vt.num_factura,vt.puntoexpedicion,
IF(datediff((select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1),cr.fechapago)<=0,0,datediff((select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1),cr.fechapago)) as diff,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1) as fechapagado,
(select count(pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito ) as cantidad
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where
((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto)
 from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))<=0 and vt.cod_clienteFK='".$buscar."'
 group by cr.idcredito order by cr.cod_venta asc,cr.fechapago asc ";


 
$pagina = "";  
$totalPagado = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
$deuda = "0";  
$diasatrazado = "0";  
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


$idcredito = utf8_encode($valor['idcredito']);/*Obtenemos el registro mediante el nombre del atributo */      
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$dias = utf8_encode($valor['dias']);
$entrega = utf8_encode($valor['entrega']);
$fechapagado = utf8_encode($valor['fechapagado']);
$cantidad = utf8_encode($valor['cantidad']);
$num_factura = utf8_encode($valor['num_factura']);
$nroCancelado = utf8_encode($valor['nroCancelado']);
$diff = utf8_encode($valor['diff']);

 $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);

$datos=calcularintereses2($idcredito,0,0,"2","2","1","no");
$descuento=$datos[0];
$total_interes=$datos[1];
$total=$datos[2];
$totalPago=$datos[3];
$deudaActua=$datos[4];
$TotalDiasAtrasado=$datos[5];
//$datos[6]=$nrodecuotasatrazado;
//$datos[7]=$TotalApagarSinInteres;
//$datos[8]=$DeudaPendiente;
$stylecolor=$datos[9];



  	$stylecancel="";
	if($nroCancelado>0){
		$stylecancel="text-decoration: line-through; ";
	}else{
		$totalDescuento=$totalDescuento+$descuento;
$totalPagado=$totalPagado+$totalPago;
$totalInteres=$totalInteres+$total_interes;
$deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$TotalDiasAtrasado;
	}
		  	 
		  	    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
	$tituloPagos="";
if($controlVentas!=$cod_venta){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$nrof."</p>";
	$controlVentas=$cod_venta;
}

$styleName=CargarStyleTable($styleName);
$pagina.=$tituloPagos."
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' style='$stylecancel' >
<td id='' style='width:10%' >".$plazo."</td>
<td id='' style='width:10%' >".$fechapago."</td>
<td id='' style='width:10%' >".$fechapagado."</td>
<td id='' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total_interes,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total,'0',',','.')."</td>
<td id='' style='width:10%'>".$diff."</td>
<td id='' style='width:10%'>".$cantidad."</td>

</tr>
</table>
";

}
}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $pagina,"3"=>$nroRegistro
,"4"=> number_format($totalPagado,'0',',','.'),"6"=> number_format($totalInteres,'0',',','.') ,"5"=> number_format($totalDescuento,'0',',','.') );
echo json_encode($informacion);	
exit;
}

function cuentasExpPendientes($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,dias,vt.pago as entrega,vt.num_factura,vt.puntoexpedicion,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1) as fechapagado,
(select count(pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito ) as cantidad
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where
((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))>0 and vt.cod_clienteFK='".$buscar."'
 group by cr.idcredito order by cr.cod_venta asc,cr.fechapago asc ";


 
$pagina = "";  
$totalPagado = "0";  
$deuda = "0";  
$diasatrazado = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
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

$idcredito = utf8_encode($valor['idcredito']);/*Obtenemos el registro mediante el nombre del atributo */      
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$Esado = utf8_encode($valor['Esado']);          
$Nro_recibo = utf8_encode($valor['Nro_recibo']);
$dias = utf8_encode($valor['dias']);
$entrega = utf8_encode($valor['entrega']);
$fechapagado = utf8_encode($valor['fechapagado']);
$cantidad = utf8_encode($valor['cantidad']);
$num_factura = utf8_encode($valor['num_factura']);
$nroCancelado = utf8_encode($valor['nroCancelado']);
 $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);

$datos=calcularintereses2($idcredito,0,0,"2","2","1","no");
$descuento=$datos[0];
$total_interes=$datos[1];
$total=$datos[2];
$totalPago=$datos[3];
$deudaActua=$datos[4];
$TotalDiasAtrasado=$datos[5];
//$datos[6]=$nrodecuotasatrazado;
//$datos[7]=$TotalApagarSinInteres;
//$datos[8]=$DeudaPendiente;
$stylecolor=$datos[9];
if($nroCancelado==0){
$totalDescuento=$totalDescuento+$descuento;
$totalPagado=$totalPagado+$totalPago;
$totalInteres=$totalInteres+$total_interes;
$deuda=$deuda+$deudaActua;
$diasatrazado=$diasatrazado+$TotalDiasAtrasado;
}
		  	    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
	$tituloPagos="";
if($controlVentas!=$cod_venta){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$nrof."</p>";
	$controlVentas=$cod_venta;
}

$styleName=CargarStyleTable($styleName);
$pagina.=$tituloPagos."
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' style='$stylecolor' >
<td id='' style='width:10%' >".$plazo."</td>
<td id='' style='width:10%' >".$fechapago."</td>
<td id='' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($descuento,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total_interes,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($totalPago,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($deudaActua,'0',',','.')."</td>
<td id='' style='width:10%'>".$TotalDiasAtrasado."</td>
<td id='' style='width:10%'>".$cantidad."</td>

</tr>
</table>
";

}
}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $pagina,"3"=>$nroRegistro,"4"=> number_format($deuda,'0',',','.') );
echo json_encode($informacion);	
exit;
}

function cuentasClientesCobrados($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	

$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1)) as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,totalinteres,totaldeuda,vt.num_factura,cr.descuento,vt.puntoexpedicion,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1) as fechapagado,
(select count(pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito ) as cantidad
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)>= ((cr.Monto+totalinteres)-cr.descuento) and vt.cod_venta='".$buscar."'
 group by cr.idcredito order by cr.cod_venta asc,cr.fechapago asc ";

 
$pagina = "";  
$totalPagado = "0";  
$totalInteres = "0";  
$totalDescuento = "0";  
$deuda = "0";
$styleName="tableRegistroSearch";



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


$idcredito = utf8_encode($valor['idcredito']);/*Obtenemos el registro mediante el nombre del atributo */      
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
 $puntoexpedicion=utf8_encode($valor['puntoexpedicion']);
$deudaActua=$total-($totalPago+$descuento);
$deuda=$deuda+$deudaActua;

$totalPagado=$totalPagado+$totalPago;
$totalInteres=$totalInteres+$tinteres;
$totalDescuento=$totalDescuento+$descuento;


/*$diff=0;

$datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy))));
$datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));
$interval=$datetime2->diff($datetime1);
$diff=$interval->format('%a');*/
$stylecolor=" ";
if($diff<0){
	$diff=$diff*-1;
	$stylecolor=" background-color: red;color:#fff";
}else{
		$diff=0;
}




	

if($deudaActua<=0){

	$stylecolor="background-color: #ccc;color:#000";
	$Esado="Pagado";
}else{
	$Esado="Pendiente";
	if($controlStyle==""){
	$stylecolor=" background-color: #2e70e8;color:#fff";
	$controlStyle="off";
}


}
$total_interes=$tinteres;
// if($Esado=="Pendiente"){
// $control=verificar_fecha_expiracion($fechapago);
	// if($control=="si"){
					
			
				// $fechahoy=date('Y-m-d');	
				// $datetime1= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechahoy))));
				// $datetime2= new DateTime(date('y-m-d',strtotime(str_replace('/','-',$fechapago))));
				// $interval=$datetime2->diff($datetime1);

		// $diff=$interval->format('%a');
	// $diasGracia=$diff-$dias;
	
			   // if($diasGracia>0){
				    // if($interes!=0){
				  
			  // $i=($interes*($Monto))/100;
			 
			  // $total_interes=($i*$diff);
			  // $t=($Monto)+$total_interes;
			  // $totalInteres=$totalInteres+$total_interes;
			  // $total=$t;
		    // $deudaActua=$t-$totalPago;
			// actualizarTotalCuota($idcredito,$total,$total_interes,$t);
					// }
			   // }else{
				   
					 // $diff="0";
				 
			   // }
				// }
// }else{
	
// }
  	$stylecancel="";
	if($nroCancelado>0){
		$stylecancel="text-decoration: line-through; ";
	}
		  	 
		  	    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
	$tituloPagos="";
if($controlVentas!=$cod_venta){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$nrof."</p>";
	$controlVentas=$cod_venta;
}

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' style='$stylecancel' >
<td id='' style='width:5%' >".$plazo."</td>
<td id='' style='width:20%' >".$fechapago."</td>
<td id='' style='width:20%' >".$fechapagado."</td>
<td id='' style='width:5%'>".$diff."</td>
<td id='' style='width:12%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:13%'>". number_format($total_interes,'0',',','.')."</td>
<td id='' style='width:12%'>". number_format($descuento,'0',',','.')."</td>
</tr>
</table>
";

}
}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $pagina,"3"=>$nroRegistro
,"4"=> number_format($totalPagado,'0',',','.'),"5"=> number_format($totalInteres,'0',',','.') ,"6"=> number_format($totalDescuento,'0',',','.') );
echo json_encode($informacion);	
exit;
}

function cuentasClientesPendientes($buscar)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	

$sql= "select vt.puntoexpedicion,cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,datediff(cr.fechapago,(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1)) as diff,vt.total_venta,interes,dias,vt.pago as entrega,
total,totalinteres,totaldeuda,vt.num_factura,cr.descuento,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago,
IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0) as nroCancelado,
(select pg.Fecha from pago pg where pg.cod_creditoFK=cr.idcredito order by pg.Fecha desc limit 1) as fechapagado,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0) as totalPagoCredito,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Interes'),0) as totalPagoInteres,
(select count(pg.Fecha) from pago pg where pg.cod_creditoFK=cr.idcredito ) as cantidad
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta where
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)< ((cr.Monto+totalinteres)-cr.descuento) and vt.cod_venta='".$buscar."'
 group by cr.idcredito order by cr.cod_venta asc,cr.fechapago asc ";



 
$pagina = "";  
$totalPagado = "0";  
$deuda = "0";  
$MontoCuotas = "0";  
$nrodecuotasatrazado = "0";  
$TotalApagarSinInteres = "0";
$styleName="tableRegistroSearch";


  
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
$totalPagoCredito = utf8_encode($valor['totalPagoCredito']);
$totalPagoInteres = utf8_encode($valor['totalPagoInteres']);
$MontoConDescuento=$Monto-$descuento;
$MontoSobrante=$MontoConDescuento-$totalPago;
if($MontoCuotas==0){
$MontoCuotas=$MontoConDescuento-$totalPago;
}
$deudaActua=0;
$total_interes=0;
$TotalSinInteres=0;
$deuda_Actual_interes=0;
$DeudaPendiente=0;
$stylecolor=" ";
$event=" ";
if($nroCancelado==0){
	
	if(($Monto+$tinteres)>($totalPago+$descuento)){
	$Esado="Pendiente";
	$TotalSinInteres=$Monto-($totalPagoCredito+$descuento);	
	if($diff<0){
	$diff=$diff*-1;
	editarDiasAtrazadosdesdecalcularcredito($cod_clienteFK,$diff);
	actualizardiasatrazadocredito($idcredito,$diff);
	$stylecolor=" background-color: #313030;color:#FFEB3B";
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
								$diffDias=$interval->format('%a');						
								
								
								$interval2=$datetime3->diff($datetime1);
								$diffDias2=$interval2->format('%a');
								
								
								
								$interval=$datetime2->diff($datetime1);
								$diffmes=$interval->format('%m');
								$diffanho=$interval->format('%y')*12;
								
								$diffmes=$diffmes + $diffanho ;
								
																
								$diasGracia=$diffDias-$dias;
								
								if($diasGracia>0){
									// if($diffmes==0){
										$diffmes= $diffmes + 1 ;
									// }
									
								}
	if($diasGracia>0){
	$montoIn=$Monto-$totalPagoCredito;
	
	$i=($interes*($Monto))/100;
	$total_interes=($i*$diffmes);
	$t=$MontoConDescuento+$total_interes;
	$total=$t;
	$deudaActua=$t-$totalPagoCredito-$totalPagoInteres;
	$deuda_Actual_interes=$total_interes-$totalPagoInteres;	

	$total_interes=$total_interes-$totalPagoInteres;
	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;	
   
	
	}	
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;	
	 
			
	}
			
	}else{
	
	$deudaActua=$MontoConDescuento-$totalPagoCredito;
	$total=$deudaActua;	
	
	
	}
	
	
	
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


if($puntoexpedicion!=""){
$nrof=$puntoexpedicion."-".$num_factura;
}else{
$nrof=$num_factura;
}
	$tituloPagos="";
if($controlVentas!=$cod_venta){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$nrof."</p>";
	$controlVentas=$cod_venta;
}

$deuda=$deuda+$deudaActua;


$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' style='$stylecolor' >
<td id='' style='width:10%' >".$plazo."</td>
<td id='' style='width:12%' >".$fechapago."</td>
<td id='' style='width:10%'>".$diff."</td>
<td id='' style='width:10%'>". number_format($Monto,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($total_interes,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($totalPago,'0',',','.')."</td>
<td id='' style='width:10%'>". number_format($deudaActua,'0',',','.')."</td>
</tr>
</table>
";



}
}

 mysqli_close($mysqli);
$informacion =array("1" => "exito","2" => $pagina,"3"=>$nroRegistro,"4"=> number_format($deuda,'0',',','.') );
echo json_encode($informacion);	
exit;
}

//VERIFICAR USO DEL CODIGO
function cuentasacobrarwhat($buscar,$filtro,$fecha1,$fecha2,$zona,$cod_local)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
if($filtro=="1"){
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=vt.cod_clienteFK  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
	$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.cod_cobradorFK,vt.num_factura,vt.total_venta,vt.num_factura,vt.cod_clienteFK,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as totalPago,cr.descuento,cr.totalinteres,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as creditopagado,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select fecha from mensajesenviados where idcliente=vt.cod_clienteFK limit 1) as fechaenviado,
(Select whapp from cliente where cod_cliente=vt.cod_clienteFK) as whapp,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK) as cobradornombre
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where  IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)< ((cr.Monto+totalinteres)-cr.descuento)
 and cr.fechapago>='$fecha1' and cr.fechapago<='$fecha2' and  
 IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 and 
  (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
 concat((Select nombre_persona from persona where cod_persona=vt.cod_clienteFK),' ',(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK),' ',vt.num_factura) like '%".$buscar."%' ".$condicionZona.$condicionCodLocal." group by vt.cod_clienteFK order by cr.fechapago asc ";
 
}
if($filtro=="2"){
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=vt.cod_clienteFK  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
	$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.cod_cobradorFK,vt.num_factura,vt.total_venta,vt.num_factura,vt.cod_clienteFK,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as totalPago,cr.descuento,cr.totalinteres,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as creditopagado,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select fecha from mensajesenviados where idcliente=vt.cod_clienteFK limit 1) as fechaenviado,
(Select whapp from cliente where cod_cliente=vt.cod_clienteFK) as whapp,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK) as cobradornombre
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where   IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)< ((cr.Monto+totalinteres)-cr.descuento)
 and cr.fechapago<='$fecha1' and 
 IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 and 
  (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
 concat((Select nombre_persona from persona where cod_persona=vt.cod_clienteFK),' ',(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK),' ',vt.num_factura) like '%".$buscar."%' ".$condicionZona.$condicionCodLocal." group by vt.cod_clienteFK order by cr.fechapago asc ";
 
}
if($filtro=="3"){
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=vt.cod_clienteFK  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
	$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.cod_cobradorFK,vt.num_factura,vt.total_venta,vt.num_factura,vt.cod_clienteFK,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as totalPago,cr.descuento,cr.totalinteres,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as creditopagado,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select whapp from cliente where cod_cliente=vt.cod_clienteFK) as whapp,
(Select fecha from mensajesenviados where idcliente=vt.cod_clienteFK limit 1) as fechaenviado,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK) as cobradornombre
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where  IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)< ((cr.Monto+totalinteres)-cr.descuento)
 and cr.fechapago>='$fecha1' and 
 IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 and 
  (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
 concat((Select nombre_persona from persona where cod_persona=vt.cod_clienteFK),' ',(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK),' ',vt.num_factura) like '%".$buscar."%' ".$condicionZona.$condicionCodLocal."  group by vt.cod_clienteFK order by cr.fechapago asc ";
 
}
if($filtro=="4"){
	$condicionZona=" and (Select count(cod_cliente) from cliente where cod_cliente=vt.cod_clienteFK  and idzonaFk='$zona') > 0 ";
	if($zona==""){
	$condicionZona="";
	}
	$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,datediff(cr.fechapago,'".$fechahoy."') as diff,vt.cod_cobradorFK,vt.num_factura,vt.total_venta,vt.num_factura,vt.cod_clienteFK,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_venta_fk=vt.cod_venta),0) as totalPago,cr.descuento,cr.totalinteres,
IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as creditopagado,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select whapp from cliente where cod_cliente=vt.cod_clienteFK) as whapp,
(Select fecha from mensajesenviados where idcliente=vt.cod_clienteFK limit 1) as fechaenviado,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
		(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK) as cobradornombre
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where  IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)< ((cr.Monto+totalinteres)-cr.descuento) and 
 IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 and 
 (select count(dtv.estado) from detalle_venta dtv where vt.cod_venta=dtv.cod_ventaFK and dtv.estado='Garantia')=0 and
concat((Select nombre_persona from persona where cod_persona=vt.cod_clienteFK),' ',(Select nombre_persona from persona where cod_persona=vt.cod_cobradorFK),' ',vt.num_factura) like '%".$buscar."%' ".$condicionZona.$condicionCodLocal." group by vt.cod_clienteFK order by cr.fechapago asc ";
 
}


 
$pagina = "";  
$totalPagado = "0";  
$deuda = "0";  
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
$controlStyle="";
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$idcredito = utf8_encode($valor['idcredito']);/*Obtenemos el registro mediante el nombre del atributo */      
$plazo = utf8_encode($valor['plazo']);  
$fechapago = utf8_encode($valor['fechapago']);          
$cod_venta = utf8_encode($valor['cod_venta']);          
$Monto = utf8_encode($valor['Monto']); 
$totalPago = utf8_encode($valor['creditopagado']); 
$diff = utf8_encode($valor['diff']);
$clientenombre = utf8_encode($valor['clientenombre']);
$cobradornombre = utf8_encode($valor['cobradornombre']);
$cod_cobradorFK = utf8_encode($valor['cod_cobradorFK']);
$total_venta = utf8_encode($valor['total_venta']);
$num_factura = utf8_encode($valor['num_factura']);
$nombrelocal = utf8_encode($valor['nombrelocal']);
$whapp = utf8_encode($valor['whapp']);
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$fechaenviado = utf8_encode($valor['fechaenviado']);
$datos=buscardatoscuentasacobrarporcliente($cod_clienteFK,$fecha1,$fecha2,$filtro);

if($whapp!=""){
$deudaActua=$total_venta-$totalPago;
$totalPagado=$totalPagado+$totalPago;



  	$deuda=$deuda+$datos[1];
$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' id='Princ_What_".$cod_clienteFK."'>
<tr  id='What_".$idcredito."' >
<td id='' style='width:10%' >".$fechaenviado."</td>
<td id='' style='width:10%' >".$datos[3]."</td>
<td id='td_datos_2' style='width:10%' >".$whapp."</td>
<td id='' style='width:10%' >".$clientenombre."</td>
<td id='td_datos_3' style='width:10%' >Hola, ".$clientenombre."  tienes una cuenta pendiente de ". number_format($datos[1],'0',',','.')."Gs en Multi Cell </td>
<td id='' style='width:10%' ><input type='button' value='Enviar' id='$idcredito' onclick='EnviarWhat(this)' style='cursor:pointer;height:30px;margin:5px;' /></td>
<td id='td_datos_1' style='display:none'>".$cod_clienteFK."</td>
<td id='' style='width:10%;text-aling:center' ><input type='checkbox' id='$cod_clienteFK' onclick='marcarcomoenviado(this)'  /></td>
</tr>
</table>
";
}


}
}

 mysqli_close($mysqli);     
$informacion =array("1" => "exito","2" => $pagina,"3" =>number_format($nroRegistro,'0',',','.') ,"4" =>number_format($deuda,'0',',','.') );
echo json_encode($informacion);	
exit;
}








function actualizarMetodo($cod_venta,$Metodo){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set TipoPago=?,TipoVenta='CREDITO' where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$Metodo,$cod_venta); 

if (!$stmt1->execute()) {

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
 mysqli_close($mysqli);

}

function actualizarGarante($cod_venta,$idGaranteFk){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set idGaranteFk=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$idGaranteFk,$cod_venta); 

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


function cambiarplazos($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select idcredito  from credito where cod_venta='$buscar' and plazo!='ENTREGA' and  plazo!='Contado'  "; 
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$nro=1;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$idcredito = utf8_encode($valor['idcredito']);
$plazo=$nro."/".$nroRegistro;
actualizarplazocredito($idcredito,$plazo);
$nro=$nro+1;

}
}
 mysqli_close($mysqli);
}
function actualizarplazocredito($idcredito,$plazo){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update credito set plazo=? where idcredito=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$plazo,$idcredito);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
}

/*Buscar */
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



$nombre_producto = utf8_decode($valor['nombre_producto']);      
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
return utf8_decode($pagina);
}

/*Buscar */
function buscar_detalles_venta_en_cuentas_a_cobrar($buscar)
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

function buscarcantidadcuotapagados($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select count(vt.num_factura) as cuotas
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar'
 and  IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)>= ((cr.Monto+totalinteres)-cr.descuento)
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



$cod_detalle = utf8_decode($valor['cod_detalle']); 
if($observacion!=""){
	if($valor['detalleproducto']!=""){
	$detalleproducto = $observacion." *".utf8_decode($valor['detalleproducto']);
	}else{
	$detalleproducto = $observacion;
	}
}else{
	$detalleproducto = " *".utf8_decode($valor['detalleproducto']);
}
     
editardetallesventacredito($detalleproducto,$cod_detalle);

}
}
 mysqli_close($mysqli);
return utf8_decode($pagina);
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







verificar($operacion);
?>
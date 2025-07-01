<?php


$funt = $_POST['funt'];
$funt = utf8_decode($funt);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
function verificar($funt)
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


	
	




	
if($funt=="nuevo" )
{
	
	
	$codigo=$_POST['codigo'];
    $codigo = utf8_decode($codigo);
	$tipo=$_POST['tipo'];
    $tipo = utf8_decode($tipo);
	$estado=$_POST['estado'];
    $estado = utf8_decode($estado);
	$caja=$_POST['caja'];
    $caja = utf8_decode($caja);
	$local=$_POST['local'];
    $local = utf8_decode($local);
	$diasa=$_POST['diasa'];
    $diasa = utf8_decode($diasa);
	$subtotal=$_POST['subtotal'];
    $subtotal = utf8_decode($subtotal);
	$descuento=$_POST['descuento'];
    $descuento = utf8_decode($descuento);
	$totalpagado=$_POST['totalpagado'];
    $totalpagado = utf8_decode($totalpagado);
	$interespagado=$_POST['interespagado'];
    $interespagado = utf8_decode($interespagado);
	$totalInteres=$_POST['totalInteres'];
    $totalInteres = utf8_decode($totalInteres);
	$saldointeres=$_POST['saldointeres'];
    $saldointeres = utf8_decode($saldointeres);
	$saldo=$_POST['saldo'];
    $saldo = utf8_decode($saldo);
	$NroCuotas=$_POST['NroCuotas'];
    $NroCuotas = utf8_decode($NroCuotas);
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = utf8_decode($cod_usuarioFK); 
	$montopagado=$_POST['montopagado'];
    $montopagado = utf8_decode($montopagado); 
	$nrorecibopago=$_POST['nrorecibopago'];
    $nrorecibopago = utf8_decode($nrorecibopago);     
	abm($codigo,$tipo,$estado,$caja,$local,$diasa,$subtotal,$descuento,$totalpagado,$interespagado,$totalInteres,$saldointeres,$saldo,$NroCuotas,$montopagado,$nrorecibopago,$cod_usuarioFK);

}




}

function abm($codigo,$tipo,$estado,$caja,$local,$diasa,$subtotal,$descuento,$totalpagado,$interespagado,$totalInteres,$saldointeres,$saldo,$NroCuotas,$montopagado,$nrorecibopago,$cod_usuarioFK)
{
	
	

	$mysqli=conectar_al_servidor();


	
    
    $consulta="insert into imprimir (codigo,montopagado,tipo,estado,caja,local,diasa,subtotal,descuento,totalpagado,interespagado,totalInteres,saldointeres,saldo,NroCuotas,nrorecibopago,cod_usuarioFK) 
	values ('$codigo','$montopagado','$tipo','$estado','$caja','$local','$diasa','$subtotal','$descuento','$totalpagado','$interespagado','$totalInteres','$saldointeres','$saldo','$NroCuotas','$nrorecibopago','$cod_usuarioFK')";	
     $stmt = $mysqli->prepare($consulta);
   
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}



$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}











verificar($funt);
?>
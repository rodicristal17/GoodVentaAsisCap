<?php


$funt = $_POST['funt'];
$funt = mb_convert_encoding((string)($funt), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
function verificar($funt)
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


	
	




	
if($funt=="nuevo" )
{
	
	
	$codigo=$_POST['codigo'];
    $codigo = mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
	$tipo=$_POST['tipo'];
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$caja=$_POST['caja'];
    $caja = mb_convert_encoding((string)($caja), 'ISO-8859-1', 'UTF-8');
	$local=$_POST['local'];
    $local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
	$diasa=$_POST['diasa'];
    $diasa = mb_convert_encoding((string)($diasa), 'ISO-8859-1', 'UTF-8');
	$subtotal=$_POST['subtotal'];
    $subtotal = mb_convert_encoding((string)($subtotal), 'ISO-8859-1', 'UTF-8');
	$descuento=$_POST['descuento'];
    $descuento = mb_convert_encoding((string)($descuento), 'ISO-8859-1', 'UTF-8');
	$totalpagado=$_POST['totalpagado'];
    $totalpagado = mb_convert_encoding((string)($totalpagado), 'ISO-8859-1', 'UTF-8');
	$interespagado=$_POST['interespagado'];
    $interespagado = mb_convert_encoding((string)($interespagado), 'ISO-8859-1', 'UTF-8');
	$totalInteres=$_POST['totalInteres'];
    $totalInteres = mb_convert_encoding((string)($totalInteres), 'ISO-8859-1', 'UTF-8');
	$saldointeres=$_POST['saldointeres'];
    $saldointeres = mb_convert_encoding((string)($saldointeres), 'ISO-8859-1', 'UTF-8');
	$saldo=$_POST['saldo'];
    $saldo = mb_convert_encoding((string)($saldo), 'ISO-8859-1', 'UTF-8');
	$NroCuotas=$_POST['NroCuotas'];
    $NroCuotas = mb_convert_encoding((string)($NroCuotas), 'ISO-8859-1', 'UTF-8');
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8'); 
	$montopagado=$_POST['montopagado'];
    $montopagado = mb_convert_encoding((string)($montopagado), 'ISO-8859-1', 'UTF-8'); 
	$nrorecibopago=$_POST['nrorecibopago'];
    $nrorecibopago = mb_convert_encoding((string)($nrorecibopago), 'ISO-8859-1', 'UTF-8');     
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
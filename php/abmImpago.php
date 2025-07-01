<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

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


	
if($operacion=="nuevo")
{
	$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);

$cod_cobrador=$_POST['cod_cobrador'];
$cod_cobrador = utf8_decode($cod_cobrador);

$cod_cliente=$_POST['cod_cliente'];
$cod_cliente = utf8_decode($cod_cliente);


$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);

	abmImpago($fecha,$motivo,$cod_cobrador,$cod_cliente);

}	

  if($operacion=="busvarImpago"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	busvarImpago($buscar);
 }
 

}


function abmImpago($fecha,$motivo,$cod_cobrador,$cod_cliente)
{


$mysqli=conectar_al_servidor(); 

$consulta="Insert into visitascliente   (Motivo, cod_clienteFK, cod_cobradorFK,fechaCompro)
values('$motivo',$cod_cliente,$cod_cobrador,'$fecha')";

// echo($consulta);
// exit;

$stmt1 = $mysqli->prepare($consulta);


if (!$stmt1->execute()) {
	

echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}




function busvarImpago($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "SELECT fecha, Motivo , fechaCompro FROM visitascliente  where cod_clienteFK='$buscar' ";
$pagina = "";   
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



$fecha = utf8_encode($valor['fecha']);     
$Motivo = utf8_encode($valor['Motivo']);  
$fechaCompro = utf8_encode($valor['fechaCompro']);          

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
<tr id='tbSelecRegistro'   name='tdMasReferencias'>
<td  id='td_datos_2' style='width:30%'>".$fecha."</td>
<td  id='td_datos_4' style='width:50%'>".$Motivo."</td>
<td  id='td_datos_5' style='width:20%'>".$fechaCompro."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}










verificar($operacion);
?>
<?php


//cargar achivos importantes
require("conexion.php");

function verificar()
{
	
	
	
		$user=$_POST['useru'];
$user = utf8_decode($user);

	cerrar_sesion($user);

}


function cerrar_sesion($user)
{
	$mysqli=conectar_al_servidor();


	$consulta="DELETE FROM seguridad where id_usuario=?";
 
  $stmt = $mysqli->prepare($consulta);


$ss='s';

$stmt->bind_param($ss,$usuario); 


if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$informacion =array("1" => "exito");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;

}


verificar();
?>
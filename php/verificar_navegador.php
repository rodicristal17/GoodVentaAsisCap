<?php
//Verificar usuario blue app
function verificar_navegador($usuario,$navegador,$contra)
{
	
	$mysqli=conectar_al_servidor();

	
	
$stmt = $mysqli->prepare('Select count(*) from seguridad where id_usuario=? and pass=? and navegador=?');


$ss='sss';

$stmt->bind_param($ss, $usuario,$contra,$navegador); 


if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nro_total=$result->fetch_row();
   $valor=$nro_total[0];
if ($valor==1)
{
	return "ok";
}
else
{
	return "no";
		
	
}

}
?>
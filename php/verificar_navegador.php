<?php
//Verificar usuario blue app
function verificar_navegador($usuario,$navegador,$contra)
{
	
	$mysqli=conectar_al_servidor();

	if (!$mysqli || $mysqli->connect_errno) {
		error_log('verificar_navegador: error de conexion DB: ' . ($mysqli ? $mysqli->connect_error : 'sin objeto mysqli'));
		return "no";
	}

$stmt = $mysqli->prepare('SELECT count(*) FROM seguridad WHERE id_usuario=? AND pass=? AND navegador=?');

	if (!$stmt) {
		error_log('verificar_navegador: no se pudo preparar consulta de sesion: ' . $mysqli->error);
		return "no";
	}


$ss='sss';

$stmt->bind_param($ss, $usuario,$contra,$navegador); 


if ( ! $stmt->execute()) {
	error_log('verificar_navegador: no se pudo ejecutar consulta de sesion: ' . $stmt->error);
	$stmt->close();
	return "no";
}

$result = $stmt->get_result();
if (!$result) {
	error_log('verificar_navegador: no se pudo obtener resultado de sesion: ' . $stmt->error);
	$stmt->close();
	return "no";
}
$nro_total=$result->fetch_row();
$stmt->close();
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

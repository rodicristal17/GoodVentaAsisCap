<?php
//Verificar usuario blue app
function verificar_navegador($usuario,$navegador,$contra)
{
	
	$mysqli=conectar_al_servidor();

	if (!$mysqli || $mysqli->connect_errno) {
		error_log('verificar_navegador: error de conexion DB: ' . ($mysqli ? $mysqli->connect_error : 'sin objeto mysqli'));
		return "no";
	}

$stmt = $mysqli->prepare('SELECT count(*) FROM seguridad s INNER JOIN usuario u ON u.cod_usuario=s.id_usuario WHERE s.id_usuario=? AND s.pass=? AND s.navegador=? AND u.estado=?');

	if (!$stmt) {
		error_log('verificar_navegador: no se pudo preparar consulta de sesion: ' . $mysqli->error);
		return "no";
	}


$estadoActivo='Activo';
$ss='ssss';

$stmt->bind_param($ss, $usuario,$contra,$navegador,$estadoActivo); 


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

<?php
require_once(__DIR__ . "/auditoria_permisos_helper.php");

function conectar_al_servidor(){

/*SERVIDOR,NOMBRE USUARIO,CONTRASEÑA USUARIO,NOMBRE DE LA BASE DE DATOS*/
// $mysqli = new mysqli('localhost','gbqjfbzl_fley','gbqjfbzl_fley','gbqjfbzl_fley');

if (function_exists('mysqli_report')) {
	mysqli_report(MYSQLI_REPORT_OFF);
}
$mysqli = new mysqli('localhost','root','','syscvxco_ac');
if ($mysqli->connect_errno) {
	return  $mysqli;
}
$mysqli->set_charset("latin1");
// Solo establece variables MySQL cuando un flujo autenticado de permisos
// activo previamente el contexto. Las conexiones normales no ejecutan SQL extra.
aplicarContextoAuditoriaPermisos($mysqli);
return  $mysqli;

}


?>

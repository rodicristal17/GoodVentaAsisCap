<?php
require_once(__DIR__ . "/auditoria_permisos_helper.php");

function conectar_al_servidor(){

/*SERVIDOR,NOMBRE USUARIO,CONTRASEÑA USUARIO,NOMBRE DE LA BASE DE DATOS*/
// $mysqli = new mysqli('localhost','gbqjfbzl_fley','gbqjfbzl_fley','gbqjfbzl_fley');

if (function_exists('mysqli_report')) {
	mysqli_report(MYSQLI_REPORT_OFF);
}
$dbHost = getenv('TELAR_DB_HOST') !== false ? getenv('TELAR_DB_HOST') : 'localhost';
$dbUser = getenv('TELAR_DB_USER') !== false ? getenv('TELAR_DB_USER') : 'root';
$dbPass = getenv('TELAR_DB_PASSWORD') !== false ? getenv('TELAR_DB_PASSWORD') : '';
$dbName = getenv('TELAR_DB_NAME') !== false ? getenv('TELAR_DB_NAME') : 'syscvxco_ac';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
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

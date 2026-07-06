<?php

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
return  $mysqli;

}

// ricardo centurion
// jaquelin marin
// belen estigarribia
// michael macoritto

?>

<?php

function conectar_al_servidor(){

// $mysqli = new mysqli('localhost','root','','syscvxco_bremprendiemiento');
$dbHost = getenv('TELAR_DB_HOST') !== false ? getenv('TELAR_DB_HOST') : 'localhost';
$dbUser = getenv('TELAR_DB_USER') !== false ? getenv('TELAR_DB_USER') : 'root';
$dbPass = getenv('TELAR_DB_PASSWORD') !== false ? getenv('TELAR_DB_PASSWORD') : '';
$dbName = getenv('TELAR_DB_NAME') !== false ? getenv('TELAR_DB_NAME') : 'syscvxco_ac';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset("latin1");
return  $mysqli;

}
?>

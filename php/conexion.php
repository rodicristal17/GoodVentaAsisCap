<?php

function conectar_al_servidor(){

// $mysqli = new mysqli('localhost','root','','syscvxco_bremprendiemiento');
$mysqli = new mysqli('localhost','root','','syscvxco_ac');
$mysqli->set_charset("latin1");
return  $mysqli;

}
?>

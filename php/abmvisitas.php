<?php
require("conexion.php");
include("verificar_navegador.php");
$operacion = $_POST['funt'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$operacion = utf8_decode($operacion);/*Función para cambiar letras que esten codificadas por la cadificación de caracteres */
/*Función principal del php que se ejecutal cargar todo el archivo php y llamado al final de php.*/
function ObtenerDatos($operacion)
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


/*operacion de condiciones anidadas por la operación OR*/	
if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar")
{


$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);

$cod_venta=$_POST['cod_venta'];
$cod_venta = utf8_decode($cod_venta);

$cod_cobrador=$_POST['cod_cobrador'];
$cod_cobrador = utf8_decode($cod_cobrador);


abm($fecha,$cod_venta,$cod_cobrador,$operacion);

}

 
 
 if($operacion=="buscar"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }





}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($fecha,$cod_venta,$cod_cobrador,$operacion)
{

if($fecha==""  || $cod_venta==""  || $cod_cobrador=="" ){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into visitas (fecha,cod_venta,cod_cobrador)
values(?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$fecha,$cod_venta,$cod_cobrador);


}




if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function BuscarRegistroEnVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select vs.fecha,vs.cod_venta,vs.cod_cobrador,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as clientenombre,
(Select nombre_persona from persona where cod_persona=vs.cod_cobrador) as cobradornombre
from visitas vs inner join venta vt on vt.cod_venta=vs.cod_venta 
where vt.cod_venta='$buscar'";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$fecha = utf8_encode($valor['fecha']);     
$cod_venta = utf8_encode($valor['cod_venta']);          
$cod_cobrador = utf8_encode($valor['cod_cobrador']);          
$clientenombre = utf8_encode($valor['clientenombre']); 
$cobradornombre = utf8_encode($valor['cobradornombre']); 




$pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' >
<td id='' style='width:33%' class='td_search'>".$clientenombre."</td>
<td id='' style='width:33%' class='td_search'>".$fecha."</td>
<td id='' style='width:33%' class='td_search'>".$cobradornombre."</td>
</tr></table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


ObtenerDatos($operacion);

?>
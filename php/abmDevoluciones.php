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
if($operacion=="nuevo" || $operacion=="editar" )
{


$Cod_Garantia=$_POST['Cod_Garantia'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$Cod_Garantia = utf8_decode($Cod_Garantia);/*Función para cambiar letras que esten codificadas por la cadificación de caracteres */

$Fecha=$_POST['Fecha'];
$Fecha = utf8_decode($Fecha);

$Motivo=$_POST['Motivo'];
$Motivo = utf8_decode($Motivo);

$cod_detalleFK=$_POST['cod_detalleFK'];
$cod_detalleFK = utf8_decode($cod_detalleFK);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);



$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = utf8_decode($cod_ventaFK);



abm($Cod_Garantia,$Fecha,$Motivo,$cod_detalleFK,$estado,$cod_ventaFK,$operacion);

}

 
 
 if($operacion=="buscarporvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }





}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($Cod_Garantia,$Fecha,$Motivo,$cod_detalleFK,$estado,$cod_ventaFK,$operacion)
{

if($Fecha==""  || $cod_detalleFK==""  || $estado==""){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into Garantia (Fecha,Motivo,cod_detalleFK)
values(?,?,?)";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$Fecha,$Motivo,$cod_detalleFK);/*Se cargar los paramentros a la sentencia preparada*/


}


if($operacion=="editar")
{

$consulta1="Update Garantia set Fecha=?,Motivo=? where Cod_Garantia=?";	/*Sentencia para editar registros*/
/*La sentencia update es utilizado para editar datos y esta compuesto por 
update elnombredelatabla set (indicar que atributos se van a modificar igualando al simbolo ? separados entre comas) 
where nombreatributo=? condición para editar solo el registro seleccionado
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$Fecha,$direccion,$Motivo,$Cod_Garantia); /*Se cargar los paramentros a la sentencia preparada*/

}



/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




$informacion =array("1" => "exito");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function BuscarRegistroEnVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.cod_venta,dtv.precio_producto,gt.Motivo,gt.Fecha
 from producto pr
inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
inner join Garantia gt on gt.cod_detalleFK=dtv.cod_detalle
where vt.cod_clienteFK=? ";/*Sentencia para buscar registros*/
$pagina = "";   
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
$s='s';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt->bind_param($s,$buscar);/*Se cargar los paramentros a la sentencia preparada*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
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



$cod_producto = utf8_encode($valor['cod_producto']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$cod_detalle = utf8_encode($valor['cod_detalle']);          
$cod_venta = utf8_encode($valor['cod_venta']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$Motivo = utf8_encode($valor['Motivo']); 
$Fecha = utf8_encode($valor['Fecha']); 



$pagina.="
<table class='tableB' >
<tr  >
<td id='td_1' style='display:none' >".$cod_producto."</td>
<td id='td_2' style='width: 33%;text-align:center' >".$nombre_producto."</td>
<td id='td_3' style='display:none'>".$cod_detalle."</td>
<td id='td_4' style='display:none'>".$cod_venta."</td>
<td id='td_5' style='display:none'>".$precio_producto."</td>
<td id='td_7' style='width: 33%;text-align:center'>".$Fecha."</td>
<td id='td_6' style='width: 33%;text-align:center'>".$Motivo."</td>

</tr>
</table>
";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


ObtenerDatos($operacion);

?>
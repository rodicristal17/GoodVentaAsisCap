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


$idpedidos=$_POST['idpedidos'];/*Función para capturar datos enviados desde la función de AJAX desde el javascript*/
$idpedidos = utf8_decode($idpedidos);/*Función para cambiar letras que esten codificadas por la cadificación de caracteres */

$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);

$costo=$_POST['costo'];
$costo = utf8_decode($costo);

$cod_clienteFK=$_POST['cod_clienteFK'];
$cod_clienteFK = utf8_decode($cod_clienteFK);





abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$operacion);

}

 
 
 if($operacion=="buscarporvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }





}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($idpedidos,$cod_productoFK,$costo,$cod_clienteFK,$operacion)
{

if($cod_productoFK==""  || $costo==""  || $cod_clienteFK==""){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into pedidos (cod_productoFK,costo,cod_clienteFK,fecha,estado)
values(?,?,?,CURRENT_TIMESTAMP,'ACTIVO')";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK);/*Se cargar los paramentros a la sentencia preparada*/


}


if($operacion=="editar")
{

$consulta1="Update pedidos set cod_productoFK=?,costo=?,cod_clienteFK=?,fecha=CURRENT_TIMESTAMP,estado='ACTIVO' where idpedidos=?";	/*Sentencia para editar registros*/
/*La sentencia update es utilizado para editar datos y esta compuesto por 
update elnombredelatabla set (indicar que atributos se van a modificar igualando al simbolo ? separados entre comas) 
where nombreatributo=? condición para editar solo el registro seleccionado
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='ssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cod_productoFK,$costo,$cod_clienteFK,$idpedidos); /*Se cargar los paramentros a la sentencia preparada*/




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

$sql= "select pr.cod_persona,pr.nombre_persona,cl.ci_cliente,
pd.idpedidos,pd.costo,pd.cod_productoFK,pro.nombre_producto,
pro.precio_producto,pro.precio_producto2,pro.precio_producto4,
pro.precio_producto5,pro.precio_producto6,pro.precio_producto9,pro.precio_producto10,pro.precio_producto12,pro.precio_producto15
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona
 inner join pedidos pd on pd.cod_clienteFK=cl.cod_cliente
 inner join producto pro on pro.cod_producto=pd.cod_productoFK
 where concat(pr.nombre_persona,' ',cl.ci_cliente) like ? ";/*Sentencia para buscar registros*/
$pagina = "";   
$buscar="%".$buscar."%";
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



$idpedidos = utf8_encode($valor['idpedidos']);/*Obtenemos el registro mediante el nombre del atributo */      
$cod_productoFK = utf8_encode($valor['cod_productoFK']);  
$cod_persona = utf8_encode($valor['cod_persona']);  
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$ci_cliente = utf8_encode($valor['ci_cliente']);          
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$costo = utf8_encode($valor['costo']); 
$precio_producto = utf8_encode($valor['precio_producto']);          
$precio_producto2 = utf8_encode($valor['precio_producto2']); 
$precio_producto4 = utf8_encode($valor['precio_producto4']); 
$precio_producto5 = utf8_encode($valor['precio_producto5']); 
$precio_producto6 = utf8_encode($valor['precio_producto6']); 
$precio_producto9 = utf8_encode($valor['precio_producto9']); 
$precio_producto10 = utf8_encode($valor['precio_producto10']); 
$precio_producto12 = utf8_encode($valor['precio_producto12']); 

$pagina.="
<table class='tableBuscado' >
<tr onclick='obtenerdatospedidos(this)' >
<td style='width: 5%;'><img src='/GoodVentaAsisCap/iconos/personas.png' class='imgAbmBuscado' /></td>
<td style='width: 95%;text-align: left;'><p class='pTituloDatos'><b>Cliente : </b>".$ci_cliente." - ".$nombre_persona."<br><b>".$nombre_producto."</b></p></td>
<td id='td_1' style='display:none' >".$idpedidos."</td>
<td id='td_2' style='display:none' >".$cod_productoFK."</td>
<td id='td_3' style='display:none'>".$cod_persona."</td>
<td id='td_4' style='display:none'>".$nombre_persona."</td>
<td id='td_5' style='display:none'>".$ci_cliente."</td>
<td id='td_6' style='display:none'>".$nombre_producto."</td>
<td id='td_7' style='display:none'>".$costo."</td>
<td id='td_8' style='display:none'>".$precio_producto."</td>
<td id='td_9' style='display:none'>".$precio_producto2."</td>
<td id='td_10' style='display:none'>".$precio_producto4."</td>
<td id='td_11' style='display:none'>".$precio_producto5."</td>
<td id='td_12' style='display:none'>".$precio_producto6."</td>
<td id='td_13' style='display:none'>".$precio_producto9."</td>
<td id='td_14' style='display:none'>".$precio_producto10."</td>
<td id='td_15' style='display:none'>".$precio_producto12."</td>
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
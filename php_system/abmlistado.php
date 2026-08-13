<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
include('quitarseparadormiles.php');
//cargar achivos importantes
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
}






	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$idlistado=$_POST['idlistado'];
$idlistado = mb_convert_encoding((string)($idlistado), 'ISO-8859-1', 'UTF-8');
$cant=$_POST['cant'];
$cant = quitarseparadormiles($cant);
	$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$cod_producto=$_POST['cod_producto'];
$cod_producto = mb_convert_encoding((string)($cod_producto), 'ISO-8859-1', 'UTF-8');
$cod_cobrador=$_POST['cod_cobrador'];
$cod_cobrador = mb_convert_encoding((string)($cod_cobrador), 'ISO-8859-1', 'UTF-8');
$cantvendido=$_POST['cantvendido'];
$cantvendido = quitarseparadormiles($cantvendido);
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	abm($idlistado,$cant,$fecha,$estado,$cod_producto,$cod_cobrador,$cantvendido,$cod_local,$operacion);

}

if($operacion=="buscar")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
buscar($fecha1,$fecha2,$estado,$buscar,$cod_local);

}	

	


}

function abm($idlistado,$cant,$fecha,$estado,$cod_producto,$cod_cobrador,$cantvendido,$cod_local,$operacion)
{
	
	
	
if($cant=="" || $cod_producto=="" || $cod_cobrador==""  ){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into listado (cant,fecha,estado,cod_producto,cod_cobrador,cantvendido)
values(?,?,?,?,?,?)";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='ssssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cant,$fecha,$estado,$cod_producto,$cod_cobrador,$cantvendido);/*Se cargar los paramentros a la sentencia preparada*/


}


if($operacion=="editar")
{

$consulta1="Update listado set cant=?,fecha=?,estado=?,cod_producto=?,cod_cobrador=?,cantvendido=? where idlistado=?";	/*Sentencia para editar registros*/
/*La sentencia update es utilizado para editar datos y esta compuesto por 
update elnombredelatabla set (indicar que atributos se van a modificar igualando al simbolo ? separados entre comas) 
where nombreatributo=? condición para editar solo el registro seleccionado
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sssssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cant,$fecha,$estado,$cod_producto,$cod_cobrador,$cantvendido,$idlistado); /*Se cargar los paramentros a la sentencia preparada*/

}



/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
	
}

function buscar($fecha1,$fecha2,$estado,$buscar,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicionCodLocal=" and lt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
			$sql= "Select lt.idlistado,lt.cant,lt.fecha,lt.estado,lt.cod_producto,lt.cod_cobrador,lt.cantvendido,cod_local,
		(Select nombre_persona from persona where cod_persona=lt.cod_cobrador limit 1) as cobrador,
			(Select Nombre from local l where l.cod_local=lt.cod_local) as nombrelocal,
		pr.nombre_producto as nombreproducto
		from listado lt inner join  producto pr on pr.cod_producto=lt.cod_producto  where fecha>='$fecha1' and fecha<='$fecha2' and lt.estado='$estado' and 
		(Select nombre_persona from persona where cod_persona=lt.cod_cobrador limit 1) like '%".$buscar."%' ".$condicionCodLocal." GROUP by lt.idlistado";
		
 
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idlistado=$valor['idlistado'];
		  	  $nombreproducto=mb_convert_encoding((string)($valor['nombreproducto']), 'UTF-8', 'ISO-8859-1');
		  	  $cobrador=mb_convert_encoding((string)($valor['cobrador']), 'UTF-8', 'ISO-8859-1');
		  	  $cant=mb_convert_encoding((string)($valor['cant']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_producto=mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_cobrador=mb_convert_encoding((string)($valor['cod_cobrador']), 'UTF-8', 'ISO-8859-1');
		  	  $cantvendido=mb_convert_encoding((string)($valor['cantvendido']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_local=mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	
			    	 
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmListado(this)'>
<td id='td_id' style='display:none'>".$idlistado."</td>
<td  id='td_datos_1' style='width:10%'>".$cod_producto."</td>
<td  id='td_datos_2' style='width:10%'>".$nombreproducto."</td>
<td  id='td_datos_3' style='width:10%'>". number_format($cant,'0',',','.')."</td>
<td  id='td_datos_8' style='width:10%'>". number_format($cantvendido,'0',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>".$fecha."</td>
<td  id='td_datos_5' style='width:10%'>".$cobrador."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
<td  id='td_datos_7' style='display:none'>".$cod_cobrador."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$cod_local."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" =>  number_format($totalGasto,'0',',','.'));
echo json_encode($informacion);	
exit;


}



verificar($operacion);
?>

<?php
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
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






	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$idlistado=$_POST['idlistado'];
$idlistado = utf8_decode($idlistado);
$cant=$_POST['cant'];
$cant = quitarseparadormiles($cant);
	$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
	$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$cod_producto=$_POST['cod_producto'];
$cod_producto = utf8_decode($cod_producto);
$cod_cobrador=$_POST['cod_cobrador'];
$cod_cobrador = utf8_decode($cod_cobrador);
$acceso=$_POST['acceso'];
$acceso = utf8_decode($acceso);
$cantvendido=$_POST['cantvendido'];
$cantvendido = quitarseparadormiles($cantvendido);


	abm($acceso,$idlistado,$cant,$fecha,$estado,$cod_producto,$cod_cobrador,$cantvendido,$operacion);

}

if($operacion=="buscar")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscar($fecha1,$fecha2,$estado,$buscar);

}	

if($operacion=="buscarmislista")
{
	
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarmislista($user,$buscar);

}	


}


/*Funcion para insertar,modificar o eliminar registros*/
function abm($acceso,$idlistado,$cant,$fecha,$estado,$cod_producto,$cod_cobrador,$cantvendido,$operacion)
{
	
if($cant=="" || $cod_producto=="" || $cod_cobrador==""  ){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta="Insert into listado (cant,fecha,estado,cod_producto,cod_cobrador,cantvendido)
values('$cant','$fecha','$estado','$cod_producto','$cod_cobrador','$cantvendido')";
$stmt1 = $mysqli->prepare($consulta);



}


if($operacion=="editar")
{

$consulta="Update listado set cant='$cant',fecha='$fecha',estado='$estado',cod_producto='$cod_producto',cod_cobrador='$cod_cobrador',cantvendido='$cantvendido',visible='$visible' where idlistado='$idlistado'";	/*Sentencia para editar registros*/

$stmt1 = $mysqli->prepare($consulta);/*Se prepara la sentencia sql con el objeto prepare*/

}



if ($stmt1 === false) {
    echo "SQL Error: " . $mysqli->error;
}

/*Función para ejecutar sentencias sql*/
if (!$stmt1->execute()) {
	
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
	
}


function buscar($fecha1,$fecha2,$estado,$buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
			$sql= "Select lt.idlistado,lt.cant,lt.fecha,lt.estado,lt.cod_producto,lt.cod_cobrador,lt.cantvendido,lt.visible,
		(Select nombre_persona from persona where cod_persona=lt.cod_cobrador limit 1) as cobrador,
		pr.nombre_producto as nombreproducto
		from listado lt inner join  producto pr on pr.cod_producto=lt.cod_producto  where fecha>='$fecha1' and fecha<='$fecha2' and lt.estado='$estado' and 
		(Select nombre_persona from persona where cod_persona=lt.cod_cobrador limit 1) like '%".$buscar."%'  GROUP by lt.idlistado";

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
		  	  $nombreproducto=utf8_encode($valor['nombreproducto']);
		  	  $cobrador=utf8_encode($valor['cobrador']);
		  	  $cant=utf8_encode($valor['cant']);
		  	  $cod_producto=utf8_encode($valor['cod_producto']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_cobrador=utf8_encode($valor['cod_cobrador']);
		  	  $cantvendido=utf8_encode($valor['cantvendido']);
		  	  $visible=utf8_encode($valor['visible']);
		  	
			    	 
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmListado(this)'>
<td id='td_id' style='display:none'>".$idlistado."</td>
<td  id='td_datos_1' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_2' style='width:35%' class='td_search'>".$nombreproducto."</td>
<td  id='td_datos_3' style='width:10%' class='td_search'>". number_format($cant,'0',',','.')."</td>
<td  id='td_datos_8' style='width:10%' class='td_search'>". number_format($cantvendido,'0',',','.')."</td>
<td  id='td_datos_4' style='display:none'>".$fecha."</td>
<td  id='td_datos_5' style='width:35%' class='td_search'>".$cobrador."</td>
<td  id='td_datos_7' style='display:none'>".$cod_cobrador."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$visible."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" =>  number_format($totalGasto,'0',',','.'));
echo json_encode($informacion);	
exit;


}

function buscarmislista($user,$buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		 $sql= "Select lt.idlistado,lt.cant,lt.fecha,lt.estado,lt.cod_producto,lt.cod_cobrador,lt.cantvendido,
		(Select nombre_persona from persona where cod_persona=lt.cod_cobrador limit 1) as cobrador,
		pr.nombre_producto as nombreproducto,pr.precio_producto,pr.comision,pr.precio_compra
		from listado lt inner join  producto pr on pr.cod_producto=lt.cod_producto  where  lt.visible='SI' and 
		lt.cod_cobrador='".$user."' and pr.nombre_producto like '%".$buscar."%' GROUP by lt.idlistado";
 
   
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
		  	  $nombreproducto=utf8_encode($valor['nombreproducto']);
		  	  $cobrador=utf8_encode($valor['cobrador']);
		  	  $cant=utf8_encode($valor['cant']);
		  	  $cod_producto=utf8_encode($valor['cod_producto']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_cobrador=utf8_encode($valor['cod_cobrador']);
		  	  $cantvendido=utf8_encode($valor['cantvendido']);
		  	  $precio_producto=utf8_encode($valor['precio_producto']);
		  	  $comision=utf8_encode($valor['comision']);
		  	  $precio_compra=utf8_encode($valor['precio_compra']);
		  	$cant=$cant-$cantvendido;
			    $paginacosto=buscardetallesprecios($cod_producto,$precio_producto,$comision);
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='seleccionarproductodesdelista(this)'>
<td id='td_id' style='display:none'>".$idlistado."</td>
<td  id='td_datos_1' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_2' style='width:33%' class='td_search'>".$nombreproducto."</td>
<td  id='td_datos_3' style='width:33%' class='td_search'>". number_format($cant,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>". number_format($cantvendido,'0',',','.')."</td>
<td  id='td_datos_9' style='width:33%' class='td_search'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_11' style='display:none'>". number_format($comision,'0',',','.')."</td>
<td  id='td_datos_4' style='display:none'>".$fecha."</td>
<td  id='td_datos_5' style='display:none'>".$cobrador."</td>
<td  id='td_datos_7' style='display:none'>".$cod_cobrador."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$paginacosto."</td>
<td  id='td_datos_12' style='display:none'>".$precio_compra."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" =>  number_format($totalGasto,'0',',','.'));
echo json_encode($informacion);	
exit;


}

function  buscardetallesprecios($buscar,$precio,$comision)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision
 from  detallesprecio where cod_producto=? ";/*Sentencia para buscar registros*/
 $pagina="<option value='".number_format($precio,'0',',','.')."' name='".number_format($comision,'0',',','.')."'  >Contado</option>";  
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



$precio = utf8_encode($valor['precio']);/*Obtenemos el registro mediante el nombre del atributo */      
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          


	  $pagina.="<option value='".number_format($precio,'0',',','.')."' name='".number_format($comision,'0',',','.')."' >".$descripcion."</option>";



}
}

return $pagina;
}



ObtenerDatos($operacion);

?>
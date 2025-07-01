<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include('quitarseparadormiles.php');
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}





	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$idsolicitud=$_POST['idsolicitud'];
$idsolicitud = utf8_decode($idsolicitud);
$cant=$_POST['cant'];
$cant = quitarseparadormiles($cant);
	$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
	$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$cod_producto1=$_POST['cod_producto1'];
$cod_producto1 = utf8_decode($cod_producto1);
$cod_producto2=$_POST['cod_producto2'];
$cod_producto2 = utf8_decode($cod_producto2);
$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);
$local1=$_POST['local1'];
$local1 = utf8_decode($local1);
$local2=$_POST['local2'];
$local2 = utf8_decode($local2);


	abm($idsolicitud,$cant,$fecha,$estado,$cod_producto1,$cod_producto2,$cod_persona,$local1,$local2,$operacion);

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
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
buscar($fecha1,$fecha2,$estado,$buscar,$cod_local);

}	

	


}

function abm($idsolicitud,$cant,$fecha,$estado,$cod_producto1,$cod_producto2,$cod_persona,$local1,$local2,$operacion)
{
	
	
	
if($cant=="" || $cod_producto1==""  || $cod_producto2==""  || $cod_persona==""  ){
$informacion =array("1" => "camposvacio");/*Array tipo JSON, utilizado en php para devolver respuesta al javascript quien lo invoco*/
echo json_encode($informacion);	/*Con esta funcion se retorna el array typo JSON al cliente */
exit;
}

$mysqli=conectar_al_servidor(); /*Función para conectarse al servidor de mysql, que se encuentra en el archivo >>conexion.php<< */

if($operacion=="nuevo") /*Condición para conocer que operacion queremos realizar*/
{


$consulta1="Insert into solicitud (cant,fecha,estado,cod_producto1,cod_producto2,cod_persona,local1,local2)
values(?,?,?,?,?,?,?,?)";/*Sentencia para insertar registros*/	
/*La sentencia insert es utilizado para insertar datos y esta compuesto por 
insert into elnombredelatabla (atributos de la tabla que se quiere insertar separados entre comas) values (los valores que vamos inserta interpretado como este simbolo ?)
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='ssssssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cant,$fecha,$estado,$cod_producto1,$cod_producto2,$cod_persona,$local1,$local2);/*Se cargar los paramentros a la sentencia preparada*/


}


if($operacion=="editar")
{

$consulta1="Update solicitud set cant=?,fecha=?,estado=?,cod_producto1=?,cod_producto2=?,cod_persona=?,local1=?,local2=? where idsolicitud=?";	/*Sentencia para editar registros*/
/*La sentencia update es utilizado para editar datos y esta compuesto por 
update elnombredelatabla set (indicar que atributos se van a modificar igualando al simbolo ? separados entre comas) 
where nombreatributo=? condición para editar solo el registro seleccionado
*/
$stmt1 = $mysqli->prepare($consulta1);/*Se prepara la sentencia sql con el objeto prepare*/
$ss='sssssssss';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt1->bind_param($ss,$cant,$fecha,$estado,$cod_producto1,$cod_producto2,$cod_persona,$local1,$local2,$idsolicitud); /*Se cargar los paramentros a la sentencia preparada*/
}

if($estado=="Atendido"){
	
		editar_cantidad($cod_producto1,$cant,"resta");
		editar_cantidad($cod_producto2,$cant,"suma");
	
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


function editar_cantidad($idproductos,$cantidad,$t){
      
	$mysqli=conectar_al_servidor(); 

	  if($t=="resta"){
		   $consulta="Update producto set stock_producto=(stock_producto-$cantidad)  where cod_producto='".$idproductos."'";
	  // if((obtenerStockActual($idproductos)-$cantidad)>0){
			 // $consulta="Update producto set stock_producto=(stock_producto-$cantidad)  where cod_producto='".$idproductos."'";
		 // }else{
			 // $consulta="Update producto set stock_producto=0  where cod_producto='".$idproductos."'";	
		 // }
		

  }else{
	  $consulta="Update producto set stock_producto=(stock_producto+$cantidad)  where cod_producto='".$idproductos."'";	
		 // if(obtenerStockActual($idproductos)>0){
			 // $consulta="Update producto set stock_producto=(stock_producto+$cantidad)  where cod_producto='".$idproductos."'";	
		 // }else{
			 // $consulta="Update producto set stock_producto=$cantidad  where cod_producto='".$idproductos."'";	
		 // }
	  

  }
  
  
  $stmt = $mysqli->prepare($consulta);
  
if ( ! $stmt->execute()) {
 echo "Error";
 exit;
}


  }
  
  function obtenerStockActual($codProducto)
{
  $mysqli=conectar_al_servidor();
   $Stock='';
	  $sql= "Select stock_producto
	  from producto where cod_producto='$codProducto' ";
	  

 
 $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
 echo "Error";
 exit;
}

  $result = $stmt->get_result();
$valor= mysqli_num_rows($result);

if ($valor>0)
{
	while ($valor= mysqli_fetch_assoc($result))
	{
		
		
			$Stock=$valor['stock_producto'];
			 
			
	}
}


return $Stock;

}



function buscar($fecha1,$fecha2,$estado,$buscar,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicionCodLocal=" and lt.local1='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
			$sql= "Select lt.idsolicitud,lt.cant,lt.fecha,lt.estado,lt.cod_producto1,lt.cod_producto2,lt.cod_persona,lt.local1,lt.local2,
		(Select nombre_persona from persona where cod_persona=lt.cod_persona limit 1) as Encargado,
            (Select Nombre from local l where l.cod_local=lt.local1) as nombrelocal1,
			(Select Nombre from local l where l.cod_local=lt.local2) as nombrelocal2,
			(Select nombre_producto from producto p where p.cod_producto=lt.cod_producto1) as producto1,
			(Select nombre_producto from producto p where p.cod_producto=lt.cod_producto2) as producto2
		from solicitud lt where fecha>='$fecha1' and fecha<='$fecha2' and lt.estado='$estado' and 
		(Select nombre_persona from persona where cod_persona=lt.cod_persona limit 1) like '%".$buscar."%' ".$condicionCodLocal." GROUP by lt.idsolicitud";
		
	
   
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
		  
		  
		      $idsolicitud=$valor['idsolicitud'];
				$producto1=utf8_encode($valor['producto1']);
				$producto2=utf8_encode($valor['producto2']);
		  	  $Encargado=utf8_encode($valor['Encargado']);
		  	  $cant=utf8_encode($valor['cant']);
				$cod_producto1=utf8_encode($valor['cod_producto1']);
				$cod_producto2=utf8_encode($valor['cod_producto2']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $cod_persona=utf8_encode($valor['cod_persona']);
		  	  $local1=utf8_encode($valor['local1']);
		  	  $local2=utf8_encode($valor['local2']);
		  	  $nombrelocal1=utf8_encode($valor['nombrelocal1']);
              $nombrelocal2=utf8_encode($valor['nombrelocal2']);
			$eventos='obtenerdatosabmSolicitud(this)';
			if($estado=="Atendido" || $estado=="Cancelado"){
				$eventos="";
			}
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='$eventos'>
<td id='td_id' style='display:none'>".$idsolicitud."</td>
<td  id='td_datos_1' style='display:none'>".$cod_producto1."</td>
<td  id='td_datos_11' style='display:none'>".$cod_producto2."</td>
<td  id='td_datos_2' style='width:10%'>".$producto1."</td>
<td  id='td_datos_10' style='width:10%'>".$producto2."</td>
<td  id='td_datos_3' style='width:10%'>". number_format($cant,'0',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>".$fecha."</td>
<td  id='td_datos_5' style='width:10%'>".$Encargado."</td>
<td  id='' style='width:10%'>".$nombrelocal1."</td>
<td  id='' style='width:10%'>".$nombrelocal2."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_7' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_8' style='display:none'>".$local1."</td>
<td  id='td_datos_9' style='display:none'>".$local2."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}



verificar($operacion);
?>
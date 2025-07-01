<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
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
$informacion =array("1" => "UI");/*Retornamos una respuesta exito con el Array JSON si todo esta correcto al final de nuestra funcion*/
echo json_encode($informacion);	
exit;
}






	
if($operacion=="buscarvista")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarvista($buscar);

}	

}


function buscarvista($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select * from vendedor where nombre like ?  and estado='Activo' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$buscar="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  $idvendedor=$valor['idvendedor'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $nrotelef=utf8_encode($valor['nrotelef']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	 
		  	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistavendedor(this)'>
<td id='td_id' style='width:8%; background-color: #efeded;color:red' class='td_search'>".$idvendedor."</td>
<td  id='td_datos_1' style='width:62%' class='td_search'>".$nombre."</td>
<td  id='td_datos_2' style='width:30%' class='td_search'>".$nrotelef."</td>
<td  id='td_datos_3' style='display:none'>".$estado."</td>
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
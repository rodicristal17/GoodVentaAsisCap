<?php



require("conexion.php");
function verificar()
{
buscaroption();
}

function buscaroption()
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from local where estado='Activo' ";
		 $pagina="";  

   
   
   $stmt = $mysqli->prepare($sql);

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
		  
		  
		      $cod_local=$valor['cod_local'];
		  	  $nombre=utf8_encode($valor['Nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	
			  $pagina.="<option  value='$cod_local' >".$nombre."</option>";   
			  
	  }
 }
 
 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




verificar();
?>
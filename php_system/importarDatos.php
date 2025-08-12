<?php


function verificar()
{
	
 
Producto();
 
}

function conectar_al_servidor(){

$mysqli = new mysqli('localhost','syscvxco_ac','syscvxco_ac','syscvxco_ac');
$mysqli->set_charset("latin1");
return  $mysqli;

}

 
 

///////////////PRODUCTO//////////////

function Producto()
{
	$mysqli=conectar_al_servidor(); 		  
	
	
	$sql= "SELECT * FROM producto";  
 // echo $sql ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
 
		$cod_producto=($valor['cod_producto']); 
		InsertarProductoStock($cod_producto,"5");
	  }
	  
 } 
  mysqli_close($mysqli); 
}




function InsertarProductoStock($cod_producto,$Cod_localFK)
{
	$mysqli=conectar_al_servidor();	
    $consulta="INSERT INTO stocklocales (cod_productofk,cantidad,cod_localfk) VALUES ('$cod_producto','0','$Cod_localFK')";	
	
	echo($consulta),"<br>";
     $stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}




 



verificar();
?>
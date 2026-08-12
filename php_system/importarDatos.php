<?php


function verificar()
{
	
 
Producto();
 
}

function conectar_al_servidor(){

$dbHost = getenv('TELAR_DB_HOST') !== false ? getenv('TELAR_DB_HOST') : 'localhost';
$dbUser = getenv('TELAR_DB_USER') !== false ? getenv('TELAR_DB_USER') : 'syscvxco_ac';
$dbPass = getenv('TELAR_DB_PASSWORD') !== false ? getenv('TELAR_DB_PASSWORD') : 'syscvxco_ac';
$dbName = getenv('TELAR_DB_NAME') !== false ? getenv('TELAR_DB_NAME') : 'syscvxco_ac';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
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

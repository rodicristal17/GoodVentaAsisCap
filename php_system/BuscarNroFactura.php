<?php

function buscarnrofactura($codNro,$nroOrdenConf)
{
	
	
	$mysqli=conectar_al_servidor();
	 $sql= "Select count(cod_venta) from venta where codnrofactura='$codNro' ";
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nroOrden=$result->fetch_row();
  $nroOrden=$nroOrden[0];
  $nroOrden=$nroOrden+$nroOrdenConf+1;
 if($nroOrden<10){
	 $nroOrden="000000".$nroOrden;
 }else{
 if($nroOrden<100){
	 $nroOrden="00000".$nroOrden;
 }else{
	 if($nroOrden<1000){
	 $nroOrden="0000".$nroOrden;
    }else{
		if($nroOrden<10000){
	 $nroOrden="000".$nroOrden;
     }else{
		if($nroOrden<100000){
	 $nroOrden="00".$nroOrden;
     }else{
		if($nroOrden<1000000){
	 $nroOrden="0".$nroOrden;
     }
	 } 
	 }
	} 
 }
 }
return $nroOrden;

}

function buscarcodNroFactura($codLocal,$puntoexpedicion)
{
	$mysqli=conectar_al_servidor();
$datos[0]='';
$datos[1]='0';
		$sql= "Select Cod_Nro,nro
        from nrofactura  where cod_localfk='$codLocal' and nrocaja='$puntoexpedicion' and estado='Activo' order by Cod_Nro desc limit 1 ";
		
 
   
   $stmt = $mysqli->prepare($sql);
  	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  $Cod_Nro=$valor['Cod_Nro'];
		  	  $nro=mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');
		  	 $datos[0]=$Cod_Nro;
			$datos[1]=$nro;
			    	 
		  	
			  
			  
	  }
 }
 
 
return $datos;

}



?>
<?php

function buscarnivel($user,$formulario,$accion)
{
	$control=0;
	$mysqli=conectar_al_servidor();
	
		$sql= "Select u.usuarios_idusario from  accesosuser u  where u.usuarios_idusario='$user' and formulario='$formulario' and ".$accion." order by usuarios_idusario asc limit 1";

   $stmt = $mysqli->prepare($sql);
  	

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 
 if ($valor>0)
 {
	  
 }else{
	$informacion =array("1" => "NI");
echo json_encode($informacion);	
exit;
 }
 
 

}
function controldeaccesoacasas($user,$formulario,$accion)
{
	$control=0;
	$mysqli=conectar_al_servidor();
	
		$sql= "Select lta.codigo from  accesosuser u inner join listadodeacceso lta on lta.idlistadodeacceso=u.idlistadodeaccesoFK  
where u.usuarios_idusario='$user' and lta.codigo='$formulario' and ".$accion."  order by usuarios_idusario asc limit 1;";

   $stmt = $mysqli->prepare($sql);
  	

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 
 if ($valor>0)
 {
	  $control=1;
 }else{
	$control=0;
 }
 
 return $control;

}

function buscarlocaluser($user)
{
	$mysqli=conectar_al_servidor();
	 $cod_localFK='';
		$sql= "Select us.cod_localFK from  persona pr inner join usuario us on us.cod_usuario=pr.cod_persona  where cod_persona=? ";

   $stmt = $mysqli->prepare($sql);
  	$s='s';

//$buscar="".$buscar."";
$stmt->bind_param($s,$user);

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
		
		  	 
		  	  $cod_localFK=utf8_encode($valor['cod_localFK']);
		  	
		      
			  
	  }
 }
		      
 
 return $cod_localFK;
 
 

}







?>
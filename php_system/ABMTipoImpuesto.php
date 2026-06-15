<?php


$funt = $_POST['funt'];
$funt = mb_convert_encoding((string)($funt), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

function verificar($funt)
{
	
	
	$user=$_POST['useru'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['passu'];
	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){

			  $informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}


	





	
if($funt=="nuevo" || $funt=="editar")
{
	
	
	$cod_Impuesto=$_POST['idabm'];
    $cod_Impuesto = mb_convert_encoding((string)($cod_Impuesto), 'ISO-8859-1', 'UTF-8');
	$descripcion=$_POST['descripcion'];
    $descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
	$monto_impuesto=$_POST['monto_impuesto'];
    $monto_impuesto = mb_convert_encoding((string)($monto_impuesto), 'ISO-8859-1', 'UTF-8');
	$Estado=$_POST['Estado'];
    $Estado = mb_convert_encoding((string)($Estado), 'ISO-8859-1', 'UTF-8');

    
    
	abm($cod_Impuesto,$descripcion,$monto_impuesto,$Estado,$funt);

}

if($funt=="buscar")
{
	$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$Estado=$_POST['estado'];
$Estado = mb_convert_encoding((string)($Estado), 'ISO-8859-1', 'UTF-8');
	buscar($buscar,$Estado);

}	


}

function abm($cod_Impuesto,$descripcion,$monto_impuesto,$Estado,$funt)
{
	
	if($descripcion=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

	if($funt=="nuevo")
	{
				$consulta= "Select count(*) from impuesto where descripcion=? and Estado ='Activo' ";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='s';
$stmt->bind_param($ss, $descripcion); 


if ( ! $stmt->execute()) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

$valor = 0;
$stmt->bind_result($valor);
while ($stmt->fetch()) { 
   
	 $valor =$valor;
}

if($valor==1)
{
	$informacion =array("1" => "EX");
	echo json_encode($informacion);	
	exit;
}   
	}
	if($funt=="nuevo")
	{
	
    
    $consulta="insert into impuesto (descripcion,Estado,monto_impuesto) values (?,?,?)";	
     $stmt = $mysqli->prepare($consulta);
    $ss='sss';
    $stmt->bind_param($ss,$descripcion,$Estado,$monto_impuesto); 
        
 
	}
	if($funt=="editar")
	{
        
        
    
    $consulta="Update impuesto set descripcion=?,Estado=?,monto_impuesto=? where cod_Impuesto=?";	

	$stmt = $mysqli->prepare($consulta);
        


    $ss='ssss';
        
    $stmt->bind_param($ss,$descripcion,$Estado,$monto_impuesto,$cod_Impuesto); 
        
	
       
	}
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}



$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}




function buscar($buscar,$Estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select cod_Impuesto,descripcion,Estado,monto_impuesto
        from impuesto where descripcion like ?  and Estado=? order by descripcion asc ";
		
 
   
   $stmt = $mysqli->prepare($sql);
  	$s='ss';
$buscar1="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar1,$Estado);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		      $cod_Impuesto=$valor['cod_Impuesto'];
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $monto_impuesto=mb_convert_encoding((string)($valor['monto_impuesto']), 'UTF-8', 'ISO-8859-1');
		  	  $Estado=mb_convert_encoding((string)($valor['Estado']), 'UTF-8', 'ISO-8859-1');
		  	 
			  
		  	 
			  $styleName=CargarStyleTable($styleName);
			  $pagina.="
			  <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' onclick='ObtenerdatosAbmTipoImpuesto(this)'>
			  <td id='td_id' style='display:none;'>".$cod_Impuesto."</td>
			  <td id='td_datos_1'style='width:50%' class='tdRegistroSearch' >".$descripcion."</td>
			  <td id='td_datos_3'style='width:50%' class='tdRegistroSearch' >".$monto_impuesto."</td>
			   <td  id='td_datos_2' style='display:none'>".$Estado."</td>
			  </tr>
			  </table>";
			    	 
		  	
			  
			  
	  }
 }
 
 
  $informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta);
echo json_encode($informacion);	
exit;


}






verificar($funt);
?>

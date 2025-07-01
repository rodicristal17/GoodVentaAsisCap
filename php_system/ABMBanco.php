<?php


$funt = $_POST['funt'];
$funt = utf8_decode($funt);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");
function verificar($funt)
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


	
if($funt=="nuevo" || $funt=="editar")
{
	
	
	$cod_Banco=$_POST['cod_Banco'];
    $cod_Banco = utf8_decode($cod_Banco);
	$nombre=$_POST['nombre'];
    $nombre = utf8_decode($nombre);
	$estado=$_POST['estado'];
    $estado = utf8_decode($estado);

    
    
	abm($cod_Banco,$nombre,$estado,$funt);

}

if($funt=="buscar")
{
	$nombre=$_POST['nombre'];
$nombre = utf8_decode($nombre);
$Estado=$_POST['estado'];
$Estado = utf8_decode($Estado);
	buscar($nombre,$Estado);

}	

if($funt=="buscarOption")
{

	buscarOption();

}	


}

function abm($cod_Banco,$nombre,$estado,$funt)
{
	
	if($nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

	if($funt=="nuevo")
	{
				$consulta= "Select count(*) from banco where nombre=? and estado ='Activo' ";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='s';
$stmt->bind_param($ss, $nombre); 


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
	
    
    $consulta="insert into banco (nombre,estado) values (upper(?),?)";	
     $stmt = $mysqli->prepare($consulta);
    $ss='ss';
    $stmt->bind_param($ss,$nombre,$estado); 
        
 
	}
	if($funt=="editar")
	{
    
    $consulta="Update banco set nombre=upper('$nombre'),estado='$estado' where idbanco=$cod_Banco";	

	$stmt = $mysqli->prepare($consulta);
	
	// echo($consulta);
	// exit;

       
	}
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}
function buscar($buscar,$Estado)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select idbanco,nombre,estado
        from banco where nombre like ?  and Estado=? order by nombre asc ";
		
 
   
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
		  
		  
		  
		      $idbanco=$valor['idbanco'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
			  
		  	 $styleName=CargarStyleTable($styleName);
			  $pagina.="
			  <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' onclick='ObtenerdatosAbmBanco(this)'>
			  <td id='td_id' style='display:none;'>".$idbanco."</td>
			  <td id='td_datos_1'style='width:25%' class='tdRegistroSearch' >".$nombre."</td>
			   <td  id='td_datos_2' style='display:none'>".$estado."</td>
			  </tr>
			  </table>";
			    	 
		  	
			  
			  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina,"3"=> $totalresouesta);
echo json_encode($informacion);	
exit;


}
function buscarOption()
{
	$mysqli=conectar_al_servidor();
	 $pagina="<option value='' >TODOS</option>";  
		$sql= "Select idbanco,nombre,estado
        from banco where estado='Activo' order by nombre asc ";
		   
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
		   
		  
		      $idbanco=$valor['idbanco'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  // $Estado=utf8_encode($valor['Estado']);
		  	 
			    $pagina.="<option value='$idbanco' >$nombre</option>";
		  	 
	  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}






verificar($funt);
?>
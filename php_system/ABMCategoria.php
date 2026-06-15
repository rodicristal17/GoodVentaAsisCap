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
	
	
	$cod_categoria=$_POST['idabm'];
    $cod_categoria = mb_convert_encoding((string)($cod_categoria), 'ISO-8859-1', 'UTF-8');
	$descripcion=$_POST['descripcion'];
    $descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
	$Estado=$_POST['Estado'];
    $Estado = mb_convert_encoding((string)($Estado), 'ISO-8859-1', 'UTF-8');

    
    
	abm($cod_categoria,$descripcion,$Estado,$funt);

}

if($funt=="buscar")
{
	$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$Estado=$_POST['estado'];
$Estado = mb_convert_encoding((string)($Estado), 'ISO-8859-1', 'UTF-8');
	buscar($buscar,$Estado);

}	

if($funt=="buscarOption")
{

	buscarOption();

}	


}

function abm($cod_categoria,$descripcion,$Estado,$funt)
{
	
	if($descripcion=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

	if($funt=="nuevo")
	{
				$consulta= "Select count(*) from categoria where descripcion=? and Estado ='Activo' ";
	
	
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
	
    
    $consulta="insert into categoria (descripcion,Estado) values (?,?)";	
     $stmt = $mysqli->prepare($consulta);
    $ss='ss';
    $stmt->bind_param($ss,$descripcion,$Estado); 
        
 
	}
	if($funt=="editar")
	{
        
        
    
    $consulta="Update categoria set descripcion=?,Estado=? where cod_categoria=?";	

	$stmt = $mysqli->prepare($consulta);
        


    $ss='sss';
        
    $stmt->bind_param($ss,$descripcion,$Estado,$cod_categoria); 
        
	
       
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
		$sql= "Select cod_categoria,descripcion,Estado
        from categoria where descripcion like ?  and Estado=? order by descripcion asc ";
		
 
   
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
		  
		  
		  
		      $cod_categoria=$valor['cod_categoria'];
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $Estado=mb_convert_encoding((string)($valor['Estado']), 'UTF-8', 'ISO-8859-1');
		  	 
			  
		  	 $styleName=CargarStyleTable($styleName);
			  $pagina.="
			  <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' onclick='ObtenerdatosAbmCategoria(this)'>
			  <td id='td_id' style='display:none;'>".$cod_categoria."</td>
			  <td id='td_datos_1'style='width:25%' class='tdRegistroSearch' >".$descripcion."</td>
			   <td  id='td_datos_2' style='display:none'>".$Estado."</td>
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
		$sql= "Select cod_categoria,descripcion,Estado
        from categoria where Estado='Activo' order by descripcion asc ";
		   
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
		   
		  
		      $cod_categoria=$valor['cod_categoria'];
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $Estado=mb_convert_encoding((string)($valor['Estado']), 'UTF-8', 'ISO-8859-1');
		  	 
			    $pagina.="<option value='$cod_categoria' >$descripcion</option>";
		  	 
			 
			    	 
		  	
			  
			  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}






verificar($funt);
?>

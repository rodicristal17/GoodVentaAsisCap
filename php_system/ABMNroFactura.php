<?php
$funt = $_POST['funt'];
$funt = mb_convert_encoding((string)($funt), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include('quitarseparadormiles.php');
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


	
	//CONTROL DE ACCESO





	
if($funt=="nuevo" || $funt=="editar")
{
	
	
	$Cod_Nro=$_POST['idabm'];
    $Cod_Nro = mb_convert_encoding((string)($Cod_Nro), 'ISO-8859-1', 'UTF-8');
	$nro=$_POST['nro'];
    $nro = quitarseparadormiles($nro);
	$fecha=$_POST['fecha'];
    $fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
	$cod_localfk=$_POST['cod_localfk'];
    $cod_localfk = mb_convert_encoding((string)($cod_localfk), 'ISO-8859-1', 'UTF-8');
	$nrocaja=$_POST['nrocaja'];
    $nrocaja = mb_convert_encoding((string)($nrocaja), 'ISO-8859-1', 'UTF-8');

    
    
	abm($Cod_Nro,$nro,$fecha,$cod_localfk,$nrocaja,$user,$funt);

}

if($funt=="buscar")
{
	
	buscar();

}	
	

}

function abm($Cod_Nro,$nro,$fecha,$cod_localfk,$nrocaja,$cod_usuarioFk,$funt)
{
	
	if($nro=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();


	$consulta= "update nrofactura set estado='Inactivo' where cod_localfk=?  and nrocaja=? ";
$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$cod_localfk,$nrocaja); 


if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	
    
    $consulta="insert into nrofactura (nro,fecha,cod_localfk,cod_usuarioFk,nrocaja,estado) values (?,?,?,?,?,'Activo')";	
     $stmt = $mysqli->prepare($consulta);
    $ss='sssss';
    $stmt->bind_param($ss,$nro,$fecha,$cod_localfk,$cod_usuarioFk,$nrocaja); 
        
 
	
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}



$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}




function buscar()
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select Cod_Nro,nro,fecha,nrocaja,
		(Select Nombre from local l where l.cod_local=cod_localfk) as nombrelocal
        from nrofactura  where estado='Activo' order by Cod_Nro desc ";
		
 
   
   $stmt = $mysqli->prepare($sql);
  	
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
		  
		  
		  
		      $Cod_Nro=$valor['Cod_Nro'];
		  	  $nro=mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	  $nrocaja=mb_convert_encoding((string)($valor['nrocaja']), 'UTF-8', 'ISO-8859-1');
		  	 
			  
		  	 $styleName=CargarStyleTable($styleName);
			  $pagina.="
			  <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
			  <tr id='tbSelecRegistro' >
			  <td id='td_id' style='display:none;'>".$Cod_Nro."</td>
			  <td  id='td_datos_4' style='width:25%' class='tdRegistroSearch' >".$nrocaja."</td>
			  <td id='td_datos_1'style='width:25%' class='tdRegistroSearch' >". number_format($nro,'0',',','.')."</td>
			   <td  id='td_datos_2' style='width:25%' class='tdRegistroSearch' >".$fecha."</td>
			   <td  id='td_datos_3' style='width:25%' class='tdRegistroSearch' >".$nombrelocal."</td>
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
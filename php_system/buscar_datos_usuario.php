<?php



//cargar achivos importantes
require("conexion.php");

include("verificar_navegador.php");
function verificar()
{
	
	
	
	$user=$_POST['user'];
$user = utf8_decode($user);
	$pass=$_POST['pass'];
	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);

if($resp=="ok"){
	buscardatos($user);
}else{
	
		  $informacion =array("1" =>"UI" );
echo json_encode($informacion);	
exit;
}
	

}


function buscardatos($user)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select pr.nombre_persona,us.acceso,us.cod_localFK,
		IFNULL((Select cdu.cod_cobradorFk from cobradorusuario cdu where cdu.cod_usuarioFk=us.cod_usuario),0) as ControlCobra
		from  persona pr inner join usuario us on us.cod_usuario=pr.cod_persona  where cod_persona=? ";

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
		
		  	  $nombre_persona=utf8_encode($valor['nombre_persona']);
		  	  $acceso=utf8_encode($valor['acceso']);
		  	  $cod_localFK=utf8_encode($valor['cod_localFK']);
		  	  $ControlCobra=utf8_encode($valor['ControlCobra']);
		   $accesos=buscaracceso($user);
		  
		  $informacion =array("1" =>"exito","2" => $nombre_persona,"3" => $acceso,"4" => $cod_localFK ,"5" => $accesos,"6" => $ControlCobra ,"7" => $ControlCobra );
echo json_encode($informacion);	
exit;
		      
			  
	  }
 }else{
	  
		  $informacion =array("1" =>"UI" );
echo json_encode($informacion);	
exit;
		      
 }
 
 
 

}


function buscaracceso($buscar)
{
	$mysqli=conectar_al_servidor();
	 $datos[0]="";
			$sql= "Select lta.nro,lta.formulario,lta.codigo,lta.nombre,acus.idaccesosUser,acus.accion,acus.usuarios_idusario,lta.formulario
		from accesosuser acus inner join listadodeacceso lta on lta.idlistadodeacceso=acus.idlistadodeaccesoFK
		where usuarios_idusario = '$buscar' and acus.tipo='Administrativo' order by lta.nro asc";
		

   
   $stmt = $mysqli->prepare($sql);
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
		  
		     $idaccesosUser=$valor['idaccesosUser'];
			  $accion=utf8_encode($valor['accion']);
			  $usuarios_idusario=utf8_encode($valor['usuarios_idusario']);
			  $codigo=utf8_encode($valor['codigo']);
		  	 $datos[$codigo]['accion']=$accion;
			    	 
		  	 
			  
			  
	  }
 }
  mysqli_close($mysqli); 
return $datos;


}




verificar();
?>
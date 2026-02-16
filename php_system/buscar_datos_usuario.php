<?php



//cargar achivos importantes
require("conexion.php");

include("verificar_navegador.php");
function verificar()
{
	
	
	
	$user=$_POST['user'];
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['pass'];
	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
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
		$sql= "Select pr.nombre_persona,us.acceso,us.cod_localFK,url,pr.telefono,pr.direccion,pr.tipo_relacion,pr.telefono_referencia,us.fecha_creacion,
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
		
		  	  $nombre_persona=mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');
		  	  $acceso=mb_convert_encoding((string)($valor['acceso']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_localFK=mb_convert_encoding((string)($valor['cod_localFK']), 'UTF-8', 'ISO-8859-1');
		  	  $ControlCobra=mb_convert_encoding((string)($valor['ControlCobra']), 'UTF-8', 'ISO-8859-1');
		   $accesos=buscaracceso($user);
		  $url= mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1');
		  $telefono= mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1');
		  $direccion= mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');
		  $telefono_referencia= mb_convert_encoding((string)($valor['telefono_referencia']), 'UTF-8', 'ISO-8859-1');
		  $tipo_relacion= mb_convert_encoding((string)($valor['tipo_relacion']), 'UTF-8', 'ISO-8859-1');
		  $fecha_creacion= mb_convert_encoding((string)($valor['fecha_creacion']), 'UTF-8', 'ISO-8859-1');
		  
		  $informacion =array(
			"1" =>"exito",
		  	"2" => $nombre_persona,
		  	"3" => $acceso,
		  	"4" => $cod_localFK,
		  	"5" => $accesos,
		  	"6" => $ControlCobra,
		  	"7" => $ControlCobra, 
		  	"8" => $url, 
		  	"9" => $telefono, 
		  	"10" => $direccion, 
		  	"11" => $tipo_relacion, 
		  	"12" => $telefono_referencia,
			"13" => $fecha_creacion
		);
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
			  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
			  $usuarios_idusario=mb_convert_encoding((string)($valor['usuarios_idusario']), 'UTF-8', 'ISO-8859-1');
			  $codigo=mb_convert_encoding((string)($valor['codigo']), 'UTF-8', 'ISO-8859-1');
		  	 $datos[$codigo]['accion']=$accion;
			    	 
		  	 
			  
			  
	  }
 }
  mysqli_close($mysqli); 
return $datos;


}




verificar();
?>
<?php


//cargar achivos importantes
require("conexion.php");

function verificar()
{
	
	
	
	$user=$_POST['user'];
$user = utf8_decode($user);
	$pass=$_POST['pass'];
$pass = utf8_decode($pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);

	login($user,$pass,$navegador);

}


function login($user,$pass,$navegador)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select * from cobrador where  usu='$user' and con='$pass'";
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
		   $iduser=$valor['cod_cobrador'];
		  cargar_datos_de_seguridad($iduser,$navegador);
		  
		      
			  
	  }
 }else{
	 echo "UI";
	 exit;
 }
 
 
 

}

function cargar_datos_de_seguridad($usuario,$nav)
{
		$id_na = rand(100,5000);
		$mysqli=conectar_al_servidor();


	$consulta="DELETE FROM seguridad where id_usuario=?";
 
  $stmt = $mysqli->prepare($consulta);


$ss='s';

$stmt->bind_param($ss,$usuario); 


if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$id_na=base64_encode($id_na);
$id_na = str_replace("=","+",$id_na); 	
$consulta="Insert into seguridad (id_usuario,navegador,pass) values(?,?,?)";

$stmt = $mysqli->prepare($consulta);


$ss='sss';

$stmt->bind_param($ss,$usuario,$nav,$id_na); 


if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


$informacion =array("1" => $id_na,"2" => $usuario );
echo json_encode($informacion);	
exit;



}




verificar();
?>
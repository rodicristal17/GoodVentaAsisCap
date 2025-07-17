<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

function ObtenerDatos($operacion)
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



if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar")
{


$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);

$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = utf8_decode($nombre_persona);

$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);

$rut_usuario=$_POST['rut_usuario'];
$rut_usuario = utf8_decode($rut_usuario);

$cod_usuario=$cod_persona;

$login=$_POST['login'];
$login = utf8_decode($login);

$password=$_POST['password'];
$password = utf8_decode($password);


$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);

$estado=$_POST['estado'];

$acceso=$_POST['acceso'];

$cod_localFK=$_POST['cod_localFK'];




abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$operacion);

}

 
 
 if($operacion=="buscar"){
 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$documento=$_POST["documento"];
 	$documento=utf8_decode($documento);
	$usuario=$_POST["usuario"];
 	$usuario=utf8_decode($usuario);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
 	BuscarRegistro($codigo,$documento,$usuario,$estado,$local);
 }

 if($operacion=="buscarfuncionario"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$tipo=$_POST["tipo"];
 	$tipo=utf8_decode($tipo);

 	buscarfuncionario($buscar,$tipo);
 }


	
if($operacion=="editarMisDatos")
{
	
	
	$Cod_Usuario=$_POST['useru'];
    $Cod_Usuario = utf8_decode($Cod_Usuario);
	$user=$_POST['user'];
    $user = utf8_decode($user);
    $pass=$_POST['pass'];
    $pass = utf8_decode($pass);  
	$local=$_POST['local'];
    $local = utf8_decode($local); 
	$nombre=$_POST['nombre'];
    $nombre = utf8_decode($nombre);   
	editarmisdatos($Cod_Usuario,$user,$pass,$local,$nombre);

}


 if($operacion=="obtenermedicos"){ 
    $cod_venta=$_POST['cod_venta'];
    $cod_venta = utf8_decode($cod_venta); 
 	obtenermedicos($cod_venta);
 }

}



function obtenermedicos($cod_venta)
{
	$mysqli=conectar_al_servidor();
	 $pagina="";  
	
	$condicionLocal="";
	if($cod_venta!=""){
		$condicionLocal=" and cod_localFK='".$cod_venta."'";
	}
	
		$sql= "Select cod_usuario , nombre_persona from usuario inner join persona on cod_usuario=cod_persona where tipo='DOCTOR' ".$condicionLocal." order by nombre_persona asc ";
 
		   
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
		   
		  
		      $cod_usuario=$valor['cod_usuario'];
		  	  $nombre_persona=utf8_encode($valor['nombre_persona']); 
		  	 		  	 
			  $pagina.="<option id='$cod_usuario' name='".$nombre_persona."' value='".$cod_usuario."' >$nombre_persona</option>";
			
		  	  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}







function editarmisdatos($Cod_Usuario,$user,$pass,$local,$nombre)
{
	
	if($Cod_Usuario=="" || $user=="" || $pass==""|| $local==""|| $nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

	
	$consulta= "Select count(*) from usuario where login='$user' and password='$pass' and cod_localFK='$local' and Cod_Usuario!='$Cod_Usuario' ";

	$stmt = $mysqli->prepare($consulta);
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

if($valor>0)
{
	$informacion =array("1" => "CI");
	echo json_encode($informacion);	
	exit;
}   
	

        
        
    
    $consulta="Update usuario set  login=?, password=?	where Cod_Usuario=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='sss';        
    $stmt->bind_param($ss,$user,$pass,$Cod_Usuario);        
	
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}


$consulta1="Update persona set nombre_persona=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$nombre,$Cod_Usuario);

if ( ! $stmt1->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}


function abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$operacion)
{



if($nombre_persona==""  || $rut_usuario==""  || $login=="" || $password==""){
$informacion =array("1" => "CAMPOSVACIOS");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

$consulta= "Select count(*) from usuario where login=? and password=? and cod_localFK=?  and Cod_Usuario!=?";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='ssss';
$stmt->bind_param($ss,$login,$password,$cod_localFK,$Cod_Usuario); 


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

if($valor>0)
{
	$informacion =array("1" => "CI");
	echo json_encode($informacion);	
	exit;
}   
	


if($operacion=="nuevo") 
{


$consulta1="Insert into persona (nombre_persona,telefono)
values(?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$nombre_persona,$telefono);

$consulta2="Insert into usuario (rut_usuario,login,cod_usuario,password,estado,acceso,cod_localFK,tipo)
values(?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?)";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssss';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo);

$con=rand(5, 1500);

$consulta3="Insert into cobrador (idzona,usu,cod_cobrador,con,estado)
values('1',?,(select cod_persona from persona order by cod_persona desc limit 1),?,'Activo')";
$stmt3 = $mysqli->prepare($consulta3);
$ss='ss';
$stmt3->bind_param($ss,$login,$con);


$consulta4="Insert into cobradorusuario (cod_usuarioFk,cod_cobradorFk)
values((select cod_persona from persona order by cod_persona desc limit 1),(select cod_persona from persona order by cod_persona desc limit 1))";
$stmt4 = $mysqli->prepare($consulta4);

}


if($operacion=="editar")
{

$consulta1="Update persona set nombre_persona=?,telefono=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$cod_persona);


$consulta2="update usuario set rut_usuario=?,login=?,password=?,estado=?,acceso=?,cod_localFK=?,tipo=? where cod_usuario=? ";
$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssss';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo,$cod_usuario);




}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


if (!$stmt2->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt2->errno.') '.$stmt2->error, E_USER_ERROR);
exit;

}

if($operacion=="nuevo") 
{
	
if (!$stmt3->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt3->errno.') '.$stmt3->error, E_USER_ERROR);
exit;
}
if (!$stmt4->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt4->errno.') '.$stmt4->error, E_USER_ERROR);
exit;
}

}

if($operacion=="nuevo"){
$cod_usuario=obtenerUltimaid();
EliminarAccesos($cod_usuario);
generarKEYS($acceso,$cod_usuario,'Administrativo');
}else{
EliminarAccesos($cod_usuario);
generarKEYS($acceso,$cod_usuario,'Administrativo');
}

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function obtenerUltimaid()
{
	$mysqli=conectar_al_servidor();
	 $idusario='';
	$sql= "Select cod_persona from persona order by cod_persona desc limit 1";
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
		  
		  
		      $idusario=$valor['cod_persona'];
		  	 
			  
			  
	  }
 }
 
 
return $idusario;


}

function BuscarRegistro($codigo,$documento,$usuario,$estado,$local)
{
$mysqli=conectar_al_servidor();

$condicioncodigo="";
if($codigo!=""){
	$condicioncodigo=" and us.cod_usuario = '".$codigo."' ";
}
$condiciondocumento="";
if($documento!=""){
	$condiciondocumento=" and us.rut_usuario = '".$documento."' ";
}

$condicionusuario="";
if($usuario!=""){
	$condicionusuario=" and pr.nombre_persona like '%".$usuario."%' ";
}





$sql= "select us.cod_usuario,us.rut_usuario,us.login,us.password,us.estado,us.acceso,us.cod_localFK,pr.nombre_persona,pr.telefono,
(select Nombre from local where cod_local= us.cod_localFK limit 1 ) as local,tipo
 from  persona pr inner join  usuario us on us.cod_usuario=pr.cod_persona where 
 us.estado=? and us.cod_localFK=? ".$condicioncodigo.$condiciondocumento.$condicionusuario;
 
 
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='ss';
$stmt->bind_param($s,$estado,$local);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$cod_usuario = utf8_encode($valor['cod_usuario']); 
$rut_usuario = utf8_encode($valor['rut_usuario']);          
$login = utf8_encode($valor['login']);          
$password = utf8_encode($valor['password']); 
$estado = utf8_encode($valor['estado']); 
$acceso = utf8_encode($valor['acceso']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$nombre_persona = utf8_encode($valor['nombre_persona']); 
$telefono = utf8_encode($valor['telefono']); 
$local = utf8_encode($valor['local']); 
$tipo = utf8_encode($valor['tipo']); 


	    	 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmusuario(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_usuario."</td>
<td  id='td_datos_2' style='width:10%'>".$rut_usuario."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_3' style='display:none'>".$login."</td>
<td  id='td_datos_4' style='display:none'>".$password."</td>
<td  id='td_datos_5' style='display:none'>".$estado."</td>
<td  id='td_datos_6' style='display:none'>".$acceso."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$telefono."</td>
<td  id='td_datos_9' style='width:10%'>".$local."</td>
<td  id='td_datos_10' style='display:none'>".$tipo."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarfuncionario($buscar,$tipo)
{
$mysqli=conectar_al_servidor();
if($tipo=="1"){

$sql= "select pr.cod_persona as cod ,pr.nombre_persona as nombre
 from  persona pr inner join  cobrador us on us.cod_cobrador=pr.cod_persona 
 where pr.nombre_persona like ?  and us.estado='Activo' ";
	
}
if($tipo=="2"){

$sql= "select pr.idvendedor as cod,pr.nombre as nombre
 from  vendedor pr  
 where pr.nombre like ?  and pr.estado='Activo' ";
	
}
$pagina = "";   
$buscar="%".$buscar."%";
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$buscar);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod = utf8_encode($valor['cod']);
$nombre = utf8_encode($valor['nombre']);          

$styleName=CargarStyleTable($styleName);
 $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistafuncionario(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod."</td>
<td  id='td_datos_1' style='width:90%'>".$nombre."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function generarKEYS($cod_nivelesFk,$usuarios_idusario,$tipo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select dts.iddetallesniveles,lta.nro,lta.formulario,lta.codigo,lta.nombre,dts.accion ,lta.idlistadodeacceso
        from listado_niveles lts inner join detallesniveles dts on dts.cod_nivelesfk=lts.cod_niveles 
		inner join listadodeacceso lta on lta.idlistadodeacceso=dts.idlistadodeacceso
		where cod_nivelesfk='".$cod_nivelesFk."' order by lta.nro asc";
		 
   $stmt = $mysqli->prepare($sql);
  if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$controltitulo="";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		     $iddetallesniveles=$valor['iddetallesniveles'];
		  	  $nro=utf8_encode($valor['nro']);
		  	  $formulario=utf8_encode($valor['formulario']);
		  	  $codigo=utf8_encode($valor['codigo']);
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $accion=utf8_encode($valor['accion']);
		  	  $idlistadodeacceso=utf8_encode($valor['idlistadodeacceso']);		  	 
			  generarAccesos($idlistadodeacceso,$accion,$usuarios_idusario,$tipo);
			    	 
		  	
			  
			  
	  }
	  
 }
 
  mysqli_close($mysqli); 


}



function generarAccesos($idlistadodeaccesoFK,$accion,$usuarios_idusario,$tipo){

	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion) VALUES ('$idlistadodeaccesoFK','$tipo','$usuarios_idusario','$accion')";	

	$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

	 mysqli_close($mysqli); 
	
}

function EliminarAccesos($usuarios_idusario){
	
	
	
	$mysqli=conectar_al_servidor();
	$consulta="Delete from accesosuser where usuarios_idusario='$usuarios_idusario' ";	
	$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	
	 mysqli_close($mysqli); 
}

ObtenerDatos($operacion);

?>
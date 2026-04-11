<?php
require("conexion.php");
include("verificar_navegador.php");
include("subir_foto_base64.php");
include("buscar_nivel.php");
include("classTable.php");

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

function ObtenerDatos($operacion)
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



if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar")
{


$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');

$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = mb_convert_encoding((string)($nombre_persona), 'ISO-8859-1', 'UTF-8');

$telefono=$_POST['telefono'];
$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');

$rut_usuario=$_POST['rut_usuario'];
$rut_usuario = mb_convert_encoding((string)($rut_usuario), 'ISO-8859-1', 'UTF-8');

$cod_usuario=$cod_persona;

$login=$_POST['login'];
$login = mb_convert_encoding((string)($login), 'ISO-8859-1', 'UTF-8');

$password=$_POST['password'];
$password = mb_convert_encoding((string)($password), 'ISO-8859-1', 'UTF-8');


$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];

$acceso=$_POST['acceso'];

$cod_localFK=$_POST['cod_localFK'];

$foto=$_POST['foto'];
$foto = mb_convert_encoding((string)($foto), 'ISO-8859-1', 'UTF-8');
$ext=$_POST['ext'];
$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
$telefono_referencia= $_POST['telefono_referencia'];
$telefono_referencia = mb_convert_encoding((string)($telefono_referencia), 'ISO-8859-1', 'UTF-8');
$direccion= $_POST['direccion'];
$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
$tipo_relacion=$_POST['tipo_relacion'];
$tipo_relacion = mb_convert_encoding((string)($tipo_relacion), 'ISO-8859-1', 'UTF-8');
$fecha_creacion = $_POST['fecha_creacion'];
$fecha_creacion = mb_convert_encoding((string)($fecha_creacion), 'ISO-8859-1', 'UTF-8');

$hora_entrada_lunes= (isset($_POST['hora_entrada_lunes']) && !empty($_POST['hora_entrada_lunes'])) ? mb_convert_encoding((string)($_POST['hora_entrada_lunes']), 'ISO-8859-1', 'UTF-8') : NULL;
$hora_entrada_martes= (isset($_POST['hora_entrada_martes']) && !empty($_POST['hora_entrada_martes'])) ? mb_convert_encoding((string)($_POST['hora_entrada_martes']), 'ISO-8859-1', 'UTF-8') : NULL;
$hora_entrada_miercoles= (isset($_POST['hora_entrada_miercoles']) && !empty($_POST['hora_entrada_miercoles'])) ? mb_convert_encoding((string)($_POST['hora_entrada_miercoles']), 'ISO-8859-1', 'UTF-8') : NULL;
$hora_entrada_jueves= (isset($_POST['hora_entrada_jueves']) && !empty($_POST['hora_entrada_jueves'])) ? mb_convert_encoding((string)($_POST['hora_entrada_jueves']), 'ISO-8859-1', 'UTF-8') : NULL;
$hora_entrada_viernes= (isset($_POST['hora_entrada_viernes']) && !empty($_POST['hora_entrada_viernes'])) ? mb_convert_encoding((string)($_POST['hora_entrada_viernes']), 'ISO-8859-1', 'UTF-8') : NULL;
$hora_entrada_sabado= (isset($_POST['hora_entrada_sabado']) && !empty($_POST['hora_entrada_sabado'])) ? mb_convert_encoding((string)($_POST['hora_entrada_sabado']), 'ISO-8859-1', 'UTF-8') : NULL;

abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$foto,$ext,$telefono_referencia,$direccion,$tipo_relacion,$fecha_creacion,$hora_entrada_lunes, $hora_entrada_martes, $hora_entrada_miercoles, $hora_entrada_jueves, $hora_entrada_viernes, $hora_entrada_sabado, $operacion);
}

 
 
 if($operacion=="buscar"){
 	$codigo=$_POST["codigo"];
 	$codigo=mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
	$documento=$_POST["documento"];
 	$documento=mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$usuario=$_POST["usuario"];
 	$usuario=mb_convert_encoding((string)($usuario), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$local=$_POST["local"];
 	$local=mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
 	BuscarRegistro($codigo,$documento,$usuario,$estado,$local);
 }

 if($operacion=="buscarfuncionario"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
	$tipo=$_POST["tipo"];
 	$tipo=mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

 	buscarfuncionario($buscar,$tipo);
 }


	
if($operacion=="editarMisDatos")
{
	$Cod_Usuario=$_POST['useru'];
    $Cod_Usuario = mb_convert_encoding((string)($Cod_Usuario), 'ISO-8859-1', 'UTF-8');
	$user=$_POST['user'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
    $pass=$_POST['pass'];
    $pass = mb_convert_encoding((string)($pass), 'ISO-8859-1', 'UTF-8');  
	$local=$_POST['local'];
    $local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8'); 
	$nombre=$_POST['nombre'];
    $nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');   
	$cedula=$_POST['cedula'];
	$cedula = mb_convert_encoding((string)($cedula), 'ISO-8859-1', 'UTF-8');
	$foto=$_POST['foto'];
	$foto = mb_convert_encoding((string)($foto), 'ISO-8859-1', 'UTF-8');
	$ext=$_POST['ext'];
	$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8'); 
	$telefono_referencia=$_POST['telefono_referencia'];
	$telefono_referencia = mb_convert_encoding((string)($telefono_referencia), 'ISO-8859-1', 'UTF-8');
	$telefono=$_POST['telefono'];
	$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');
	$direccion=$_POST['direccion'];
	$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
	
	editarmisdatos($Cod_Usuario,$user,$pass,$local,$nombre, $foto, $ext, $telefono, $direccion,$telefono_referencia,$cedula);

}
if ($operacion == "obtenerHistorialUsuario" || $operacion == "obtenerHistorialUsuarios") {
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');

	$result= obtenerUsuariosAnteriores(array('cod_usuarioFK' => $cod_usuarioFK));
	$pagina= "";
	foreach ($result as $valor) {
		$pagina .= '<tr>
			<td style="width: 10%;">'.$valor["id"].'</td>
			<td style="width: 50%;">'.$valor["nombre_persona"].'</td>
			<td style="width: 40%;">'.$valor["telefono"].'</td>
			<td style="width: 40%;">'.$valor["fecha_cambio"].'</td>
		</tr>';
	}

	echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $result));
	exit;
}

 if($operacion=="obtenermedicos"){ 
    $cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
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
		  	  $nombre_persona=mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1'); 
		  	 		  	 
			  $pagina.="<option id='$cod_usuario' name='".$nombre_persona."' value='".$cod_usuario."' >$nombre_persona</option>";
			
		  	  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}







function editarmisdatos($Cod_Usuario,$user,$pass,$local,$nombre,$foto,$ext,$telefono,$direccion,$telefono_referencia,$cedula)
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
	

        
        
    
    $consulta="Update usuario set  login=?, password=?, rut_usuario=?	where Cod_Usuario=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='ssss';        
    $stmt->bind_param($ss,$user,$pass,$cedula,$Cod_Usuario);        
	
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}


$consulta1="Update persona set nombre_persona=?, telefono=?, direccion=?, telefono_referencia=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre,$telefono,$direccion,$telefono_referencia,$Cod_Usuario);

if ( ! $stmt1->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

// Copia la imagen al servidor y genera el enlace
if (!(empty($ext))) {
	$foto=substr($foto, strpos($foto, ",") + 1);
	$foto = base64_decode($foto);
	$id_foto="";		  
	$donde="../fotos/perfilUsuario/";
	$id_foto=$Cod_Usuario;
	$id_f=subir_imagen_base64($donde,$foto,$id_foto,$ext);
	$ruta="/GoodVentaAsisCap/fotos/perfilUsuario/".$Cod_Usuario.$id_f.'.'.$ext;
	CargaFoto("url",$ruta,$Cod_Usuario);
}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}


function abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$foto,$ext,$telefono_referencia,$direccion,$tipo_relacion,$fecha_creacion,$hora_entrada_lunes,$hora_entrada_martes,$hora_entrada_miercoles,$hora_entrada_jueves, $hora_entrada_viernes,$hora_entrada_sabado,$operacion)
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


$consulta1="Insert into persona (nombre_persona,telefono,telefono_referencia,direccion,tipo_relacion)
values(?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion);

$consulta2="Insert into usuario (rut_usuario,login,cod_usuario,password,estado,acceso,cod_localFK,tipo,fecha_creacion,hora_entrada_lunes,hora_entrada_martes,hora_entrada_miercoles,hora_entrada_jueves,hora_entrada_viernes,hora_entrada_sabado)
values(?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?, NOW(),?,?,?,?,?,?)";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssssssssss';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo,$hora_entrada_lunes,$hora_entrada_martes,$hora_entrada_miercoles,$hora_entrada_jueves,$hora_entrada_viernes,$hora_entrada_sabado);

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

$consulta1="Update persona set nombre_persona=?,telefono=?, telefono_referencia=?, direccion=?, tipo_relacion=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion,$cod_persona);

$consulta2="update usuario set rut_usuario=?,login=?,password=?,estado=?,acceso=?,cod_localFK=?,tipo=?,fecha_creacion=?,hora_entrada_lunes=?, hora_entrada_martes=?, hora_entrada_miercoles=?, hora_entrada_jueves=?, hora_entrada_viernes=? , hora_entrada_sabado=? where cod_usuario=? ";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssissssssssi';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo,$fecha_creacion,$hora_entrada_lunes,$hora_entrada_martes,$hora_entrada_miercoles,$hora_entrada_jueves,$hora_entrada_viernes,$hora_entrada_sabado,$cod_usuario);

}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


if (!$stmt2->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt2->errno.') '.$stmt2->error, E_USER_ERROR);
exit;

}

// Recupera la id del usuario de la ultima insercion
$cod_usuario= empty($cod_usuario) ?  : $cod_usuario;

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

// Copia la imagen al servidor y genera el enlace
if (!(empty($ext))) {
	$foto=substr($_POST['foto'], strpos($_POST['foto'], ",") + 1);
	$foto = base64_decode($foto);
	$id_foto="";		  
	$donde="../fotos/perfilUsuario/";
	$id_foto=$cod_usuario;
	$id_f=subir_imagen_base64($donde,$foto,$id_foto,$ext);
	$ruta="/GoodVentaElim/fotos/perfilUsuario/".$cod_persona.$id_f.'.'.$ext;
	CargaFoto("url",$ruta,$cod_persona);
}

if($operacion=="nuevo"){
$cod_usuario=obtenerUltimaid();
EliminarAccesos($cod_usuario);
generarKEYS($acceso,$cod_usuario,'Administrativo');
}else{
EliminarAccesos($cod_usuario);
generarKEYS($acceso,$cod_usuario,'Administrativo');
}


cargarFotos($cod_persona);

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


function cargarFotos($cod_persona){
	
$ext=$_POST['ext'];
$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
 

if($ext!=""){
	$foto=substr($_POST['foto'], strpos($_POST['foto'], ",") + 1);;
$foto = base64_decode($foto);
$id_foto="";		  
		     $donde="../fotos/perfilUsuario/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto,$id_foto,$ext);
$ruta="/GoodVentaAsisCap/fotos/perfilUsuario/".$cod_persona.$id_f.'.'.$ext;
CargaFoto("url",$ruta,$cod_persona);
}
 



}

function CargaFoto($tableName,$Urlfoto,$cod_cliente){
	$mysqli=conectar_al_servidor();
	$consulta="Update usuario set ".$tableName."=? where cod_usuario=? ";	

	$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$Urlfoto,$cod_cliente); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	 mysqli_close($mysqli);
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

$sqlFiltro= "where us.estado= '$estado' ";
if($codigo!=""){
	$sqlFiltro.= " and us.cod_usuario = '".$codigo."' ";
}
if($documento!=""){
	$sqlFiltro.=" and us.rut_usuario = '".$documento."' ";
}
if($usuario!=""){
	$sqlFiltro.=" and pr.nombre_persona like '%".$usuario."%' ";
}
if($local!=""){
	$sqlFiltro.=" and us.cod_localFK = '".$local."' ";
}



$sql= "select us.cod_usuario,us.rut_usuario,us.login,us.password,us.estado,us.acceso,us.cod_localFK,pr.nombre_persona,pr.telefono,
pr.tipo_relacion, pr.direccion,pr.telefono_referencia,us.fecha_creacion,us.hora_entrada_lunes,us.hora_entrada_martes,us.hora_entrada_miercoles,us.hora_entrada_jueves,us.hora_entrada_viernes,us.hora_entrada_sabado,
(select Nombre from local where cod_local= us.cod_localFK limit 1 ) as local,tipo,url
 from  persona pr inner join  usuario us on us.cod_usuario=pr.cod_persona ".$sqlFiltro;
 
$pagina = "";
$paginaSelect= "";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$registros= array();

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$cod_usuario = mb_convert_encoding((string)($valor['cod_usuario']), 'UTF-8', 'ISO-8859-1'); 
$rut_usuario = mb_convert_encoding((string)($valor['rut_usuario']), 'UTF-8', 'ISO-8859-1');          
$login = mb_convert_encoding((string)($valor['login']), 'UTF-8', 'ISO-8859-1');          
$password = mb_convert_encoding((string)($valor['password']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$acceso = mb_convert_encoding((string)($valor['acceso']), 'UTF-8', 'ISO-8859-1'); 
$cod_localFK = mb_convert_encoding((string)($valor['cod_localFK']), 'UTF-8', 'ISO-8859-1'); 
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1'); 
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$local = mb_convert_encoding((string)($valor['local']), 'UTF-8', 'ISO-8859-1'); 
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'); 
$url = mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1'); 
$telefono_referencia = mb_convert_encoding((string)($valor['telefono_referencia']), 'UTF-8', 'ISO-8859-1');
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');
$tipo_relacion = mb_convert_encoding((string)($valor['tipo_relacion']), 'UTF-8', 'ISO-8859-1');
$fecha_creacion = mb_convert_encoding((string)($valor['fecha_creacion']), 'UTF-8', 'ISO-8859-1');

$hora_entrada_lunes = mb_convert_encoding((string)($valor['hora_entrada_lunes']), 'UTF-8', 'ISO-8859-1');
$hora_entrada_martes = mb_convert_encoding((string)($valor['hora_entrada_martes']), 'UTF-8', 'ISO-8859-1');
$hora_entrada_miercoles = mb_convert_encoding((string)($valor['hora_entrada_miercoles']), 'UTF-8', 'ISO-8859-1');
$hora_entrada_jueves = mb_convert_encoding((string)($valor['hora_entrada_jueves']), 'UTF-8', 'ISO-8859-1');
$hora_entrada_viernes = mb_convert_encoding((string)($valor['hora_entrada_viernes']), 'UTF-8', 'ISO-8859-1');
$hora_entrada_sabado = mb_convert_encoding((string)($valor['hora_entrada_sabado']), 'UTF-8', 'ISO-8859-1');

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
<td  id='td_datos_11' style='display:none'>".$url."</td>
<td  id='td_datos_12' style='display:none'>".$telefono_referencia."</td>
<td  id='td_datos_13' style='display:none'>".$direccion."</td>
<td  id='td_datos_14' style='display:none'>".$tipo_relacion."</td>
<td  id='td_datos_15' style='display:none'>".$fecha_creacion."</td>
<td id='td_datos_16' style='display: none'>".$hora_entrada_lunes."</td>
<td id='td_datos_17' style='display: none'>".$hora_entrada_martes."</td>
<td id='td_datos_18' style='display: none'>".$hora_entrada_miercoles."</td>
<td id='td_datos_19' style='display: none'>".$hora_entrada_jueves."</td>
<td id='td_datos_20' style='display: none'>".$hora_entrada_viernes."</td>
<td id='td_datos_21' style='display: none'>".$hora_entrada_sabado."</td>
</tr>
</table>";

$paginaSelect .= '<option value="'.$cod_usuario.'">'.$nombre_persona.'</option>';

$registros[] = array(
	'cod_usuario' => $cod_usuario,
	'rut_usuario' => $rut_usuario,
	'login' => $login,
	//'password' => $password,
	'estado' => $estado,
	'acceso' => $acceso,
	'cod_localFK' => $cod_localFK,
	'nombre_persona' => $nombre_persona,
	'telefono' => $telefono,
	'local' => $local,
	'tipo' => $tipo,
	'url' => $url,
	'telefono_referencia' => $telefono_referencia,
	'direccion' => $direccion,
	'tipo_relacion' => $tipo_relacion,
	'fecha_creacion' => $fecha_creacion,
);
}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro, "4" => $registros, "5" => $paginaSelect);
echo json_encode($informacion);	
exit;
}

function obtenerUsuariosAnteriores($filtros = array())
{
	$mysqli=conectar_al_servidor();

	$sqlFiltro= "";
	foreach ($filtros as $key => $value) {
		if ($value === null || $value === "") {continue;}
		if ($sqlFiltro == "") {
			$sqlFiltro .= "WHERE ";
		} else {
			$sqlFiltro .= " AND ";
		}

		if (is_numeric($value)) {
			$sqlFiltro .= "hpu.$key = $value";
		} else {
			$sqlFiltro .= "hpu.$key like '%$value%'";
		}
	}

	$sql= "SELECT
		hpu.*
		FROM historial_personas_usuario hpu
		$sqlFiltro
		ORDER BY hpu.fecha_cambio DESC, hpu.id DESC";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		echo json_encode(array("1" => "error", "2" => "Error al obtener historial de usuarios", "sql" => $sql, "detalle" => $stmt->error));
		exit;
	}

	$result = $stmt->get_result();
	$registros= array();
	while ($row = $result->fetch_assoc()) {
		$reg= array();
		foreach ($row as $key => $value) {
			$reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
		}
		$registros[] = $reg;
	}

	$stmt->close();
	return $registros;
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



$cod = mb_convert_encoding((string)($valor['cod']), 'UTF-8', 'ISO-8859-1');
$nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');          

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
		  	  $nro=mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');
		  	  $formulario=mb_convert_encoding((string)($valor['formulario']), 'UTF-8', 'ISO-8859-1');
		  	  $codigo=mb_convert_encoding((string)($valor['codigo']), 'UTF-8', 'ISO-8859-1');
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
		  	  $idlistadodeacceso=mb_convert_encoding((string)($valor['idlistadodeacceso']), 'UTF-8', 'ISO-8859-1');		  	 
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

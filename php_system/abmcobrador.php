<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include("classTable.php");

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

//CONTROL DE ACCESO


if($operacion=="nuevo" || $operacion=="editar" )
{


$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);

$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = utf8_decode($nombre_persona);


$telefono=$_POST['telefono'];
$telefono = utf8_decode($telefono);



$cod_cobrador=$cod_persona;

$idzona=$_POST['idzona'];
$idzona = utf8_decode($idzona);

$usu=$_POST['usu'];
$usu = utf8_decode($usu);

$con=$_POST['con'];
$con = utf8_decode($con);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

$accesocliente=$_POST['accesocliente'];
$accesocliente = utf8_decode($accesocliente);

$accesoproducto=$_POST['accesoproducto'];
$accesoproducto = utf8_decode($accesoproducto);

$accesocuentas=$_POST['accesocuentas'];
$accesocuentas = utf8_decode($accesocuentas);

$modosinconexion=$_POST['modosinconexion'];
$modosinconexion = utf8_decode($modosinconexion);

$realizarcobranzas=$_POST['realizarcobranzas'];
$realizarcobranzas = utf8_decode($realizarcobranzas);






abm($accesocliente,$accesoproducto,$accesocuentas,$modosinconexion,$realizarcobranzas,$estado,$cod_persona,$nombre_persona,$telefono,$cod_cobrador,$idzona,$usu,$con,$operacion);

}

 
 
 if($operacion=="buscar"){
 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$cobrador=$_POST["cobrador"];
 	$cobrador=utf8_decode($cobrador);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
 	BuscarRegistro($codigo,$cobrador,$estado);
 }

 
 if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroVista($buscar);
 }



if($operacion=="buscaroption")
{

	buscaroption($user);

}


 
 



}





function abm($accesocliente,$accesoproducto,$accesocuentas,$modosinconexion,$realizarcobranzas,$estado,$cod_persona,$nombre_persona,$telefono,$cod_cobrador,$idzona,$usu,$con,$operacion)
{

if($usu==""  || $nombre_persona==""  || $con=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}

$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{


$consulta1="Insert into persona (nombre_persona,telefono)
values(?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$nombre_persona,$telefono);

$consulta2="Insert into cobrador (idzona,usu,cod_cobrador,con,estado,accesocliente,accesoproducto,accesocuentas,modosinconexion,realizarcobranzas)
values(?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?,?,?)";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssssss';
$stmt2->bind_param($ss,$idzona,$usu,$con,$estado,$accesocliente,$accesoproducto,$accesocuentas,$modosinconexion,$realizarcobranzas);

}


if($operacion=="editar")
{

$consulta1="Update persona set nombre_persona=?,telefono=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$cod_persona); 


$consulta2="update cobrador set idzona=?,usu=?,con=?,estado=?,accesocliente=? ,accesoproducto=? ,accesocuentas=? , modosinconexion=?, realizarcobranzas=? where cod_cobrador=? ";
$stmt2 = $mysqli->prepare($consulta2);
$ss='ssssssssss';
$stmt2->bind_param($ss,$idzona,$usu,$con,$estado,$accesocliente,$accesoproducto,$accesocuentas,$modosinconexion,$realizarcobranzas,$cod_persona);


}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

if (!$stmt2->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


/*Buscar Registro en vista*/
function BuscarRegistro($codigo,$cobrador,$estado)
{
$mysqli=conectar_al_servidor();
$condicioncodigo="";
if($codigo!=""){
	$condicioncodigo=" and pr.cod_persona ='".$codigo."'";
}
$condicioncobrador="";
if($cobrador!=""){
	$condicioncobrador=" and pr.nombre_persona  like '%".$cobrador."%'";
}
$sql= "select pr.cod_persona,pr.nombre_persona,pr.telefono,cl.idzona,cl.usu,cl.con,cl.estado,zn.nombre
,cl.accesocliente,cl.accesoproducto,cl.accesocuentas,cl.modosinconexion,cl.realizarcobranzas
 from  persona pr inner join  cobrador cl on cl.cod_cobrador=pr.cod_persona 
 inner join zona  zn on zn.idzona=cl.idzona
where cl.estado=? ".$condicioncodigo.$condicioncobrador;
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$estado);
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



$cod_persona = utf8_encode($valor['cod_persona']);
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$zona = utf8_encode($valor['nombre']);          
$telefono = utf8_encode($valor['telefono']); 
$usu = utf8_encode($valor['usu']); 
$con = utf8_encode($valor['con']); 
$idzona = utf8_encode($valor['idzona']); 
$estado = utf8_encode($valor['estado']); 
$accesocliente = utf8_encode($valor['accesocliente']); 
$accesoproducto = utf8_encode($valor['accesoproducto']); 
$accesocuentas = utf8_encode($valor['accesocuentas']); 
$modosinconexion = utf8_encode($valor['modosinconexion']); 
$realizarcobranzas = utf8_encode($valor['realizarcobranzas']); 


		$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmCobrador(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_persona."</td>
<td  id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_2' style='width:10%'>".$telefono."</td>
<td  id='td_datos_3' style='display:none'>".$zona."</td>
<td  id='td_datos_4' style='display:none'>".$usu."</td>
<td  id='td_datos_5' style='display:none'>".$con."</td>
<td  id='td_datos_6' style='display:none'>".$idzona."</td>
<td  id='td_datos_7' style='display:none'>".$estado."</td>
<td  id='td_datos_8' style='display:none'>".$accesocliente."</td>
<td  id='td_datos_9' style='display:none'>".$accesoproducto."</td>
<td  id='td_datos_10' style='display:none'>".$accesocuentas."</td>
<td  id='td_datos_11' style='display:none'>".$modosinconexion."</td>
<td  id='td_datos_12' style='display:none'>".$realizarcobranzas."</td>
</tr>
</table>";


}
}

 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

/*Buscar Registro en vista*/
function  BuscarRegistroVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_persona,pr.nombre_persona,pr.telefono,cl.idzona,cl.usu,cl.con,cl.estado,zn.nombre
 from  persona pr inner join  cobrador cl on cl.cod_cobrador=pr.cod_persona 
 inner join zona  zn on zn.idzona=cl.idzona
where concat(pr.nombre_persona,' ',zn.nombre) like ? and cl.estado='Activo' ";
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



$cod_persona = utf8_encode($valor['cod_persona']);  
$nombre_persona = utf8_encode($valor['nombre_persona']);          
$zona = utf8_encode($valor['nombre']);          
$telefono = utf8_encode($valor['telefono']); 
$usu = utf8_encode($valor['usu']); 
$con = utf8_encode($valor['con']); 
$idzona = utf8_encode($valor['idzona']); 
$estado = utf8_encode($valor['estado']); 

		$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistacobrador(this)'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_1' style='width:30%'>".$nombre_persona."</td>
<td  id='td_datos_2' style='width:30%'>".$telefono."</td>
<td  id='td_datos_3' style='display:none'>".$zona."</td>
<td  id='td_datos_4' style='display:none'>".$usu."</td>
<td  id='td_datos_5' style='display:none'>".$con."</td>
<td  id='td_datos_6' style='display:none'>".$idzona."</td>
<td  id='td_datos_7' style='display:none'>".$estado."</td>
</tr>
</table>";


}
}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}





function buscaroption($user)
{
	

		$sql= "Select  (Select nombre_persona from persona pra where pra.cod_persona =cod_cobrador ) as nombre , cod_cobrador , estado  from cobrador where estado='Activo' ";

	$mysqli=conectar_al_servidor();
	
		
		 $pagina= "<option  value='' >SELECCIONAR</option>";   
   
   
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_cobrador=$valor['cod_cobrador'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	
			  $pagina.="<option  value='$cod_cobrador' >".$nombre."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




ObtenerDatos($operacion);

?>
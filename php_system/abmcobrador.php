<?php
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
include("classTable.php");

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

//CONTROL DE ACCESO


if($operacion=="nuevo" || $operacion=="editar" )
{


$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');

$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = mb_convert_encoding((string)($nombre_persona), 'ISO-8859-1', 'UTF-8');


$telefono=$_POST['telefono'];
$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');



$cod_cobrador=$cod_persona;

$idzona=$_POST['idzona'];
$idzona = mb_convert_encoding((string)($idzona), 'ISO-8859-1', 'UTF-8');

$usu=$_POST['usu'];
$usu = mb_convert_encoding((string)($usu), 'ISO-8859-1', 'UTF-8');

$con=$_POST['con'];
$con = mb_convert_encoding((string)($con), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

$accesocliente=$_POST['accesocliente'];
$accesocliente = mb_convert_encoding((string)($accesocliente), 'ISO-8859-1', 'UTF-8');

$accesoproducto=$_POST['accesoproducto'];
$accesoproducto = mb_convert_encoding((string)($accesoproducto), 'ISO-8859-1', 'UTF-8');

$accesocuentas=$_POST['accesocuentas'];
$accesocuentas = mb_convert_encoding((string)($accesocuentas), 'ISO-8859-1', 'UTF-8');

$modosinconexion=$_POST['modosinconexion'];
$modosinconexion = mb_convert_encoding((string)($modosinconexion), 'ISO-8859-1', 'UTF-8');

$realizarcobranzas=$_POST['realizarcobranzas'];
$realizarcobranzas = mb_convert_encoding((string)($realizarcobranzas), 'ISO-8859-1', 'UTF-8');






abm($accesocliente,$accesoproducto,$accesocuentas,$modosinconexion,$realizarcobranzas,$estado,$cod_persona,$nombre_persona,$telefono,$cod_cobrador,$idzona,$usu,$con,$operacion);

}

 
 
 if($operacion=="buscar"){
 	$codigo=$_POST["codigo"];
 	$codigo=mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
	$cobrador=$_POST["cobrador"];
 	$cobrador=mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
 	BuscarRegistro($codigo,$cobrador,$estado);
 }

 
 if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
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



$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$zona = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$usu = mb_convert_encoding((string)($valor['usu']), 'UTF-8', 'ISO-8859-1'); 
$con = mb_convert_encoding((string)($valor['con']), 'UTF-8', 'ISO-8859-1'); 
$idzona = mb_convert_encoding((string)($valor['idzona']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$accesocliente = mb_convert_encoding((string)($valor['accesocliente']), 'UTF-8', 'ISO-8859-1'); 
$accesoproducto = mb_convert_encoding((string)($valor['accesoproducto']), 'UTF-8', 'ISO-8859-1'); 
$accesocuentas = mb_convert_encoding((string)($valor['accesocuentas']), 'UTF-8', 'ISO-8859-1'); 
$modosinconexion = mb_convert_encoding((string)($valor['modosinconexion']), 'UTF-8', 'ISO-8859-1'); 
$realizarcobranzas = mb_convert_encoding((string)($valor['realizarcobranzas']), 'UTF-8', 'ISO-8859-1'); 


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



$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');  
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$zona = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$usu = mb_convert_encoding((string)($valor['usu']), 'UTF-8', 'ISO-8859-1'); 
$con = mb_convert_encoding((string)($valor['con']), 'UTF-8', 'ISO-8859-1'); 
$idzona = mb_convert_encoding((string)($valor['idzona']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 

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
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	 
		  	 
			    	
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
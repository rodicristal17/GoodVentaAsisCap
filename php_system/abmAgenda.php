<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

function verificar($operacion)
{
	
	
	if($operacion=="buscaroption")
{

	buscaroption();

}else {	
	
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



	
if($operacion=="nuevo" || $operacion=="editar")
{
	$idAgenda=$_POST['idAgenda'];
$idAgenda = utf8_decode($idAgenda);
	$motivo=$_POST['motivo'];
$motivo = utf8_decode($motivo);
	$fechaCompromiso=$_POST['fechaCompromiso'];
$fechaCompromiso = utf8_decode($fechaCompromiso);
	$estado=$_POST['estado'];
$estado = utf8_decode($estado);
	$Cod_cobrador=$_POST['Cod_cobrador'];
$Cod_cobrador = utf8_decode($Cod_cobrador);
	$cod_clienteAgenda=$_POST['cod_clienteAgenda'];
$cod_clienteAgenda = utf8_decode($cod_clienteAgenda);

abm($idAgenda,$motivo,$fechaCompromiso,$estado,$Cod_cobrador,$cod_clienteAgenda,$operacion);

}


if($operacion=="buscar")
{
	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	
	$cliente=$_POST["cliente"];
 	$cliente=utf8_decode($cliente);
	$cobrador=$_POST["cobrador"];
 	$cobrador=utf8_decode($cobrador);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$tipo=$_POST["tipo"];
 	$tipo=utf8_decode($tipo);
	
 	buscar($estado,$tipo,$fecha1,$fecha2,$cliente,$cobrador);

}	
if($operacion=="buscarvista")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscarvista($buscar);

}
}

}

function abm($idAgenda,$motivo,$fechaCompromiso,$estado,$Cod_cobrador,$cod_clienteAgenda,$operacion)
{
	
	
if($motivo=="" || $Cod_cobrador==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo")
{


$consulta1="Insert into visitascliente (fecha,Motivo,cod_clienteFK,cod_cobradorFK,fechaCompro,estado) values (NOW(),'$motivo',$cod_clienteAgenda,$Cod_cobrador,'$fechaCompromiso','$estado')";

// echo($consulta1);
// exit;
$stmt1 = $mysqli->prepare($consulta1);
}


if($operacion=="editar")
{

$consulta1="Update visitascliente set Motivo=?,cod_clienteFK=?,cod_cobradorFK=?,fechaCompro=?,estado=? where cod_VisitasCliente=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$motivo,$cod_clienteAgenda,$Cod_cobrador,$fechaCompromiso,$estado,$idAgenda);

}

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

function buscar($estado,$tipo,$fecha1,$fecha2,$cliente,$cobrador)
{
$mysqli=conectar_al_servidor();


$condicionfecha="";
if($fecha1!="" || $fecha2!=""){
	if($tipo=="compromiso" ){
		$condicionfecha=" and fechaCompro between '$fecha1' and '$fecha2' ";
	}else{
		$condicionfecha=" and fecha between '$fecha1' and '$fecha2 23:59:00' ";
}
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (select nombre_persona from persona where cod_persona = cod_clienteFK) like '%$cliente%'";
}
$condicioncobrador="";
if($cobrador!=""){
	$condicioncobrador=" and (select nombre_persona from persona where cod_persona = cod_cobradorFK) like '%$cobrador%'";
}
$condicionestado="";
if($estado!=""){
	$condicionestado=" and estado = '$estado'";
}





$sql= "select estado,fechaCompro, cod_VisitasCliente, fecha, Motivo, cod_clienteFK, cod_cobradorFK ,(select nombre_persona from persona where cod_persona = cod_cobradorFK) as cobrador , (select nombre_persona from persona where cod_persona = cod_clienteFK) as cliente , (select nombre from zona where idzona=(select idzonaFk from cliente where cod_cliente = cod_clienteFK)) as zona  from visitascliente  where cod_VisitasCliente!=''
".$condicioncliente.$condicioncobrador.$condicionestado.$condicionfecha." limit 500";

// echo($sql);
// exit;
$pagina = "";   
$stmt = $mysqli->prepare($sql);
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
$estado = utf8_encode($valor['estado']);  
$cod_clienteFK = utf8_encode($valor['cod_clienteFK']);
$cod_VisitasCliente = utf8_encode($valor['cod_VisitasCliente']);
$Motivo = utf8_encode($valor['Motivo']);     
$fecha = utf8_encode($valor['fecha']); 
$cliente = utf8_encode($valor['cliente']);     
$zona = utf8_encode($valor['zona']); 
$cobrador = utf8_encode($valor['cobrador']);  
$fechaCompro = utf8_encode($valor['fechaCompro']);    
  


 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmAgenda(this)'>
<td  id='td_datos_1' style='width:15%'>".$fecha."</td>
<td  id='td_datos_2' style='width:25%'>".$cliente."</td>
<td  id='td_datos_3' style='width:30%'>".$Motivo."</td>
<td  id='td_datos_4' style='width:15%'>".$cobrador."</td>
<td  id='td_datos_5' style='width:15%'>".$fechaCompro."</td>
<td  id='td_id' style='display:none'>".$cod_VisitasCliente."</td>
<td  id='td_datos_6' style='display:none'>".$estado."</td>
<td  id='td_datos_7' style='display:none'>".$cod_clienteFK."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}



function buscarvista($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select * from zona where nombre like ?  and estado='Activo' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
  	$s='s';
$buscar="%".$buscar."%";
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
  $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	 $styleName=CargarStyleTable($styleName);  
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosVistaZona(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$idzona."</td>
<td  id='td_datos_1' style='width:50%'>".$nombre."</td>
<td  id='td_datos_2' style='display:none'>".$estado."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}

function buscaroption()
{
	$mysqli=conectar_al_servidor();
	
		$sql= "Select * from zona where estado='Activo' ";
		 $pagina="<option  value='' >SELECCIONAR</option>";  

   
   
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
		  
		  
		      $idzona=$valor['idzona'];
		  	  $nombre=utf8_encode($valor['nombre']);
		  	  $estado=utf8_encode($valor['estado']);
		  	 
		  	 
			    	
			  $pagina.="<option  value='$idzona' >".$nombre."</option>";   
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




verificar($operacion);
?>
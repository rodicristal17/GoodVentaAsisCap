<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include("classTable.php");

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = utf8_decode($user);
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = utf8_decode($navegador);
$resp=verificar_navegador($user,$navegador,$pass);


if($resp!="ok" && $operacion!="buscaroption"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}
	
if($operacion=="nuevo" )
{
	
	
	$Cantidad=$_POST['Cantidad'];
	$Cantidad = utf8_decode($Cantidad);
	$Motivo=$_POST['Motivo'];
	$Motivo = utf8_decode($Motivo);
	$Cod_usuarioFK=$_POST['Cod_usuarioFK'];
	$Cod_usuarioFK = utf8_decode($Cod_usuarioFK);
	$Cod_productoFK=$_POST['Cod_productoFK'];
	$Cod_productoFK = utf8_decode($Cod_productoFK);
	abm($Cantidad,$Motivo,$Cod_usuarioFK,$Cod_productoFK,$operacion);

}

if($operacion=="buscar")
{
$Fecha1=$_POST['Fecha1'];
$Fecha1 = utf8_decode($Fecha1);
$Fechafijo=$_POST['Fechafijo'];
$Fechafijo = utf8_decode($Fechafijo);
$Fecha2=$_POST['Fecha2'];
$Fecha2 = utf8_decode($Fecha2);
$Nombre=$_POST['Nombre'];
$Nombre = utf8_decode($Nombre);
$Usuario=$_POST['Usuario'];
$Usuario = utf8_decode($Usuario);
$Cod_productoFK=$_POST['Cod_productoFK'];
$Cod_productoFK = utf8_decode($Cod_productoFK);
	buscar($Fechafijo,$Fecha1,$Fecha2,$Nombre,$Usuario,$Cod_productoFK);

}	

if($operacion=="buscarmas")
{
$Fecha1=$_POST['Fecha1'];
$Fecha1 = utf8_decode($Fecha1);
$Fechafijo=$_POST['Fechafijo'];
$Fechafijo = utf8_decode($Fechafijo);
$Fecha2=$_POST['Fecha2'];
$Fecha2 = utf8_decode($Fecha2);
$Nombre=$_POST['Nombre'];
$Nombre = utf8_decode($Nombre);
$Cod_productoFK=$_POST['Cod_productoFK'];
$Cod_productoFK = utf8_decode($Cod_productoFK);
$Usuario=$_POST['Usuario'];
$Usuario = utf8_decode($Usuario);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
buscarmas($Fechafijo,$Fecha1,$Fecha2,$Nombre,$Usuario,$Cod_productoFK,$registrocargado);

}	




}
function descontarStock($Cod_productoFK,$Cantidad){
	
	$mysqli=conectar_al_servidor();
	
	$consulta1="update  producto set stock_producto=(stock_producto - ? ) where cod_producto=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$Cantidad,$Cod_productoFK);


if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
	
	
}

function abm($Cantidad,$Motivo,$Cod_usuarioFK,$Cod_productoFK,$operacion)
{
	
	
if($Cantidad==""  || $Motivo==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{


$consulta1="Insert into debaja (Cantidad,Motivo,Cod_usuarioFK,Cod_productoFK)
values(?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$Cantidad,$Motivo,$Cod_usuarioFK,$Cod_productoFK);


}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
descontarStock($Cod_productoFK,$Cantidad);
echo json_encode($informacion);	
exit;
	
}



function buscar($Fechafijo,$Fecha1,$Fecha2,$Nombre,$Usuario,$Cod_productoFK)
{
	
	
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicionNombre="";
	 if($Nombre!=""){
		 $condicionNombre=" and  (select nombre_producto from producto where cod_producto= cod_productoFK ) like '%$Nombre%' ";
	 }
	 $condicionUsuario="";
	 if($Usuario!=""){
		 $condicionUsuario=" and  (select nombre_persona from persona where cod_persona= Cod_usuarioFK ) like '%".$Usuario."%' ";
	 }
	 
	 $condicionFechafijo="";
	  if($Fechafijo!=""){
		 $condicionFechafijo=" and  Fecha = '".$Fechafijo."' ";
	 }
	 $condicionFechadesdehasta="";
	  if($Fecha1!="" || $Fecha2!="" ){
		 $condicionFechadesdehasta=" and  Fecha  between  '".$Fecha1."' and   '".$Fecha2." 23:59:59' ";
	 }
	 $condicionCod_productoFK="";
	  if($Cod_productoFK!=""){
		 $condicionCod_productoFK=" and  cod_productoFK = '".$Cod_productoFK."' ";
	 }
		$sql= "SELECT  cod_productoFK ,(select nombre_producto from producto where cod_producto= cod_productoFK ) as producto
 , Cantidad, Motivo,Fecha ,
 (select nombre_persona from persona where cod_persona= Cod_usuarioFK ) as Usuario FROM debaja
 where idDebaja!=0 ".$condicionNombre.$condicionUsuario.$condicionFechafijo.$condicionFechadesdehasta.$condicionCod_productoFK." limit 50 ";
		
   
   
   $stmt = $mysqli->prepare($sql);
  	

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
		  
		  
		      $cod_productoFK=$valor['cod_productoFK'];
			  $producto=$valor['producto'];
		  	  $Cantidad=utf8_encode($valor['Cantidad']);
		  	  $Motivo=utf8_encode($valor['Motivo']);
			  $Fecha=utf8_encode($valor['Fecha']);
			  $Usuario=utf8_encode($valor['Usuario']);
		  	 
			 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='obtenerdatosabmCasa(this)'>
				<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$cod_productoFK."</td>
				<td  id='td_datos_1' style='width:25%'>".$producto."</td>
				<td  id='td_datos_2' style='width:10%'>".$Cantidad."</td>
				<td  id='td_datos_3' style='width:25%'>".$Motivo."</td>
				<td  id='td_datos_4' style='width:10%'>".$Fecha."</td>
				<td  id='td_datos_5' style='width:20%'>".$Usuario."</td>
				</tr>
				</table>";
			  
			  
	  }
 }
 
 $sql= "SELECT  cod_productoFK ,(select nombre_producto from producto where cod_producto= cod_productoFK ) as producto
 , Cantidad, Motivo,Fecha ,
 (select nombre_persona from persona where cod_persona= Cod_usuarioFK ) as Usuario FROM debaja
 where idDebaja!=0 ".$condicionNombre.$condicionUsuario.$condicionFechafijo.$condicionFechadesdehasta.$condicionCod_productoFK;   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
} 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregistro= $valor; 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"99" => $nroRegistro,"100" => $totalregistro);
echo json_encode($informacion);	
exit;


}

function buscarmas($Fechafijo,$Fecha1,$Fecha2,$Nombre,$Usuario,$Cod_productoFK,$registrocargado)
{
	
	
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condicionNombre="";
	 if($Nombre!=""){
		 $condicionNombre=" and  (select nombre_producto from producto where cod_producto= cod_productoFK ) like '%$Nombre%' ";
	 }
	 $condicionUsuario="";
	 if($Usuario!=""){
		 $condicionUsuario=" and  (select nombre_persona from persona where cod_persona= Cod_usuarioFK ) like '%".$Usuario."%' ";
	 }
	 
	 $condicionFechafijo="";
	  if($Fechafijo!=""){
		 $condicionFechafijo=" and  Fecha = '".$Fechafijo."' ";
	 }
	 $condicionFechadesdehasta="";
	  if($Fecha1!="" || $Fecha2!="" ){
		 $condicionFechadesdehasta=" and  Fecha  between  '".$Fecha1."' and   '".$Fecha2." 23:59:59' ";
	 }
	 $condicionCod_productoFK="";
	  if($Cod_productoFK!=""){
		 $condicionCod_productoFK=" and  cod_productoFK = '".$Cod_productoFK."' ";
	 }
		$sql= "SELECT  cod_productoFK ,(select nombre_producto from producto where cod_producto= cod_productoFK ) as producto
 , Cantidad, Motivo,Fecha ,
 (select nombre_persona from persona where cod_persona= Cod_usuarioFK ) as Usuario FROM debaja
 where idDebaja!=0 ".$condicionNombre.$condicionUsuario.$condicionFechafijo.$condicionFechadesdehasta.$condicionCod_productoFK." limit ".$registrocargado." , 50 ";
		
   
   
   $stmt = $mysqli->prepare($sql);
  	

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor+$registrocargado;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_productoFK=$valor['cod_productoFK'];
			  $producto=$valor['producto'];
		  	  $Cantidad=utf8_encode($valor['Cantidad']);
		  	  $Motivo=utf8_encode($valor['Motivo']);
			  $Fecha=utf8_encode($valor['Fecha']);
			  $Usuario=utf8_encode($valor['Usuario']);
		  	 
			 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='obtenerdatosabmCasa(this)'>
				<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$cod_productoFK."</td>
				<td  id='td_datos_1' style='width:25%'>".$producto."</td>
				<td  id='td_datos_2' style='width:10%'>".$Cantidad."</td>
				<td  id='td_datos_3' style='width:25%'>".$Motivo."</td>
				<td  id='td_datos_4' style='width:10%'>".$Fecha."</td>
				<td  id='td_datos_5' style='width:20%'>".$Usuario."</td>
				</tr>
				</table>";
			  
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;


}


verificar($operacion);
?>
<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include("classTable.php");

function verificar($operacion)
{
	
 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=$_POST['passu'];	
	  $pass = str_replace("=","+",$pass);
$navegador=$_POST['navegador'];
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);


if($resp!="ok" && $operacion!="buscaroption"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}
	
if($operacion=="nuevo" )
{
	
	
	$Cantidad=$_POST['Cantidad'];
	$Cantidad = mb_convert_encoding((string)($Cantidad), 'ISO-8859-1', 'UTF-8');
	$Motivo=$_POST['Motivo'];
	$Motivo = mb_convert_encoding((string)($Motivo), 'ISO-8859-1', 'UTF-8');
	$Cod_usuarioFK=$_POST['Cod_usuarioFK'];
	$Cod_usuarioFK = mb_convert_encoding((string)($Cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$Cod_productoFK=$_POST['Cod_productoFK'];
	$Cod_productoFK = mb_convert_encoding((string)($Cod_productoFK), 'ISO-8859-1', 'UTF-8');
	abm($Cantidad,$Motivo,$Cod_usuarioFK,$Cod_productoFK,$operacion);

}

if($operacion=="buscar")
{
$Fecha1=$_POST['Fecha1'];
$Fecha1 = mb_convert_encoding((string)($Fecha1), 'ISO-8859-1', 'UTF-8');
$Fechafijo=$_POST['Fechafijo'];
$Fechafijo = mb_convert_encoding((string)($Fechafijo), 'ISO-8859-1', 'UTF-8');
$Fecha2=$_POST['Fecha2'];
$Fecha2 = mb_convert_encoding((string)($Fecha2), 'ISO-8859-1', 'UTF-8');
$Nombre=$_POST['Nombre'];
$Nombre = mb_convert_encoding((string)($Nombre), 'ISO-8859-1', 'UTF-8');
$Usuario=$_POST['Usuario'];
$Usuario = mb_convert_encoding((string)($Usuario), 'ISO-8859-1', 'UTF-8');
$Cod_productoFK=$_POST['Cod_productoFK'];
$Cod_productoFK = mb_convert_encoding((string)($Cod_productoFK), 'ISO-8859-1', 'UTF-8');
	buscar($Fechafijo,$Fecha1,$Fecha2,$Nombre,$Usuario,$Cod_productoFK);

}	

if($operacion=="buscarmas")
{
$Fecha1=$_POST['Fecha1'];
$Fecha1 = mb_convert_encoding((string)($Fecha1), 'ISO-8859-1', 'UTF-8');
$Fechafijo=$_POST['Fechafijo'];
$Fechafijo = mb_convert_encoding((string)($Fechafijo), 'ISO-8859-1', 'UTF-8');
$Fecha2=$_POST['Fecha2'];
$Fecha2 = mb_convert_encoding((string)($Fecha2), 'ISO-8859-1', 'UTF-8');
$Nombre=$_POST['Nombre'];
$Nombre = mb_convert_encoding((string)($Nombre), 'ISO-8859-1', 'UTF-8');
$Cod_productoFK=$_POST['Cod_productoFK'];
$Cod_productoFK = mb_convert_encoding((string)($Cod_productoFK), 'ISO-8859-1', 'UTF-8');
$Usuario=$_POST['Usuario'];
$Usuario = mb_convert_encoding((string)($Usuario), 'ISO-8859-1', 'UTF-8');
$registrocargado=$_POST['registrocargado'];
$registrocargado = mb_convert_encoding((string)($registrocargado), 'ISO-8859-1', 'UTF-8');
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
		  	  $Cantidad=mb_convert_encoding((string)($valor['Cantidad']), 'UTF-8', 'ISO-8859-1');
		  	  $Motivo=mb_convert_encoding((string)($valor['Motivo']), 'UTF-8', 'ISO-8859-1');
			  $Fecha=mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');
			  $Usuario=mb_convert_encoding((string)($valor['Usuario']), 'UTF-8', 'ISO-8859-1');
		  	 
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
		  	  $Cantidad=mb_convert_encoding((string)($valor['Cantidad']), 'UTF-8', 'ISO-8859-1');
		  	  $Motivo=mb_convert_encoding((string)($valor['Motivo']), 'UTF-8', 'ISO-8859-1');
			  $Fecha=mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1');
			  $Usuario=mb_convert_encoding((string)($valor['Usuario']), 'UTF-8', 'ISO-8859-1');
		  	 
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
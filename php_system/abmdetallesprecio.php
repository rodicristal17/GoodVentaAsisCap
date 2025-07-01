<?php
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
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

//CONTROL DE ACCESO


if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar" )
{


$iddetallesprecio=$_POST['iddetallesprecio'];
$iddetallesprecio = utf8_decode($iddetallesprecio);

$precio=$_POST['precio'];
$precio = quitarseparadormiles($precio);


$descripcion=$_POST['descripcion'];
$descripcion = utf8_decode($descripcion);

$cod_producto=$_POST['cod_producto'];
$cod_producto = utf8_decode($cod_producto);

$Porcentaje=$_POST['Porcentaje'];
$Porcentaje = quitarseparadormiles($Porcentaje);

$Cuota=$_POST['Cuota'];
$Cuota = utf8_decode($Cuota);

$preciocuota=$_POST['preciocuota'];
$preciocuota = quitarseparadormiles($preciocuota);

$comision=$_POST['comision'];
$comision = quitarseparadormiles($comision);



$precio_ventaDetalle=$_POST['precio_ventaDetalle'];
$precio_ventaDetalle = quitarseparadormiles($precio_ventaDetalle);

$userid=$_POST['userid'];
$userid = utf8_decode($userid);



abm($userid,$precio_ventaDetalle,$iddetallesprecio,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota,$operacion);

}



 
 
 if($operacion=="buscar"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistro($buscar);
 }
 
 if($operacion=="editarestePrecio"){
 	$codDetalle=$_POST["codDetalle"];
 	$codDetalle=utf8_decode($codDetalle);
	
	$porcentaje=$_POST["porcentaje"];
 	$porcentaje=utf8_decode($porcentaje);
	
	$precioCompra=$_POST["precioCompra"];
 	$precioCompra=utf8_decode($precioCompra);
	
	$cuotas=$_POST["cuotas"];
 	$cuotas=utf8_decode($cuotas);
	
	$PrecioContado=$_POST["PrecioContado"];
 	$PrecioContado=utf8_decode($PrecioContado);
	
	$PorcenContado=$_POST["PorcenContado"];
 	$PorcenContado=utf8_decode($PorcenContado);
	
		$precio_ventaDetalle=$_POST['precio_ventaDetalle'];
		$precio_ventaDetalle = quitarseparadormiles($precio_ventaDetalle);

		$userid=$_POST['userid'];
		$userid = utf8_decode($userid);
		
		$Cod_producto=$_POST['Cod_producto'];
		$Cod_producto = utf8_decode($Cod_producto);
	
	EditarDetallePrecio($Cod_producto,$userid,$precio_ventaDetalle,$codDetalle,$porcentaje,$cuotas,$precioCompra,$PrecioContado,$PorcenContado);
}
 
 
 
  if($operacion=="buscarTabla"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroTabla($buscar);
 }
 
 
 
 
 if($operacion=="buscarvistacompra"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarvistacompra($buscar);
 }

 if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	BuscarRegistroEnVista($buscar);
 }

 if($operacion=="buscarabmproductos"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarabmproductos($buscar);
 }
 
 
 
  if($operacion=="buscarTablapresupuesto"){
	  
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$entrega=$_POST['entrega'];
$entrega = utf8_decode($entrega);


$Total=$_POST['Total'];
$Total = quitarseparadormiles($Total);
	
 	BuscarRegistroPresupuesto($buscar,$entrega,$Total);
 }
 
 
 
 
 if($operacion=="nuevoTablaDetallePrecio" )
{


$cod_producto=$_POST['cod_producto'];
$cod_producto = ($cod_producto);

$contado=$_POST['contado'];
$contado = quitarseparadormiles($contado);

nuevoTablaDetallePrecio($cod_producto,$contado);

}





}

Function nuevoTablaDetallePrecio($cod_producto,$PrecioContado)
{

$PorcenContado=1;


	exit;
	$Resultado=45 ;
	$cuota=2;
	$precioCuotas=($PrecioContado)/$cuota;
	$descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	$TotalPrecio=($PrecioContado);

	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,2,$precioCuotas,"nuevo");
	
	
	$Resultado=45;
	$cuota=3;
	$precioCuotas=($PrecioContado)/$cuota;
	$descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	$TotalPrecio=($PrecioContado);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,3,$precioCuotas,"nuevo");
	
	
	$porcentaje=45;
	$cuota=4;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,4,$precioCuotas,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	
	$porcentaje=45;
	$cuota=5;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,5,$precioCuotas,"nuevo");
	

	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	$porcentaje=45;
	$cuota=6;
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,6,$precioCuotas,"nuevo");
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	$porcentaje=45;
	$cuota=8;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,8,$precioCuotas,"nuevo");
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=45;
	$cuota=10;
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,10,$precioCuotas,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=45;
	$cuota=12;
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,12,$precioCuotas,"nuevo");
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=45;
	$cuota=15;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,15,$precioCuotas,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=45;
	$cuota=18;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,18,$precioCuotas,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	// $porcentaje=110;
	// $cuota=24;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	// $PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	// $precio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	// abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,110,24,$precioCuotas,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}

/*Funcion para insertar,modificar o eliminar registros*/
function abmTabla($iddetallesprecio,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota,$operacion)
{

// if($precio==""  || $descripcion=="" || $cod_producto=="" || $comision==""   ){
// $informacion =array("1" => "camposvacio");
// echo json_encode($informacion);	
// exit;
// }

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo") 
{


$consulta1="Insert into detallesprecio (precio,descripcion,cod_producto,comision,Porcentaje,Cuota,preciocuota)
values($precio,'$descripcion',$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota)";
$stmt1 = $mysqli->prepare($consulta1);

echo($consulta1);
exit;
}


if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




}





/*Funcion para insertar,modificar o eliminar registros*/
function EditarDetallePrecio($Cod_producto,$userid,$precio_ventaDetalle,$codDetalle,$porcentaje,$cuota,$precioCompra,$PrecioContado,$PorcenContado)
{

$mysqli=conectar_al_servidor(); 
$Resultado=$porcentaje-$PorcenContado;
if($Resultado==0){
	$Resultado=1;
	
$PrecioCuota=($PrecioContado)/$cuota;
$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
$precio=($PrecioContado);
}else{
	
$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
$precio=$precioCompra + round(($precioCompra * $porcentaje)/100);


// $precio=$precioCompra;
}




$consulta1="Update detallesprecio set precio=".$precio.",descripcion='".$descripcion."',Porcentaje=".$porcentaje.",preciocuota=".$PrecioCuota." where iddetallesprecio=".$codDetalle."";
$stmt1 = $mysqli->prepare($consulta1);

// echo($consulta1);
// exit;

if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

$fechahoy=date('Y-m-d');

if($precio_ventaDetalle==$precio){
	$precio_ventaDetalle==0;
	$precio==0;
}
// abmAuditoria("","0",$precio,"0","","","0",$precio_ventaDetalle,"0","",$fechahoy,$userid,"Editar Detalle precio",$Cod_producto);


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}








/*Funcion para insertar,modificar o eliminar registros*/
function abm($userid,$precio_ventaDetalle,$iddetallesprecio,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota,$operacion)
{

if($precio==""  || $descripcion=="" || $cod_producto=="" || $comision==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo") 
{


$consulta1="Insert into detallesprecio (precio,descripcion,cod_producto,comision,Porcentaje,Cuota,preciocuota)
values(?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota);


}


if($operacion=="editar")
{

$consulta1="Update detallesprecio set precio=?,descripcion=?,comision=?,Porcentaje=?,Cuota=?,preciocuota=? where iddetallesprecio=?";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$precio,$descripcion,$comision,$Porcentaje,$Cuota,$preciocuota,$iddetallesprecio); 

$fechahoy=date('Y-m-d');

if($precio_ventaDetalle==$precio){
	$precio_ventaDetalle==0;
	$precio==0;
}
abmAuditoria("","0",$precio,"0","","","0",$precio_ventaDetalle,"0","",$fechahoy,$userid,"Editar Detalle precio",$cod_producto);



}

if($operacion=="eliminar")
{

$consulta1="delete from detallesprecio  where iddetallesprecio=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$iddetallesprecio); 




}


if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


function  BuscarRegistro($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Porcentaje,Cuota,preciocuota
 from  detallesprecio 
where cod_producto=? ";
$pagina = "";   
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



$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Porcentaje = utf8_encode($valor['Porcentaje']);          
$Cuota = utf8_encode($valor['Cuota']);          
$preciocuota = utf8_encode($valor['preciocuota']);          

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetallesprecio(this)'>
<td  id='td_datos_1' style='width:50%'>".number_format($precio,'0',',','.') ."</td>
<td  id='td_datos_2' style='width:50%'>".$descripcion."</td>
<td  id='td_datos_3' style='display:none'>".$iddetallesprecio."</td>
<td  id='td_datos_4' style='display:none'>".$comision."</td>
<td  id='td_datos_5' style='display:none'>".number_format($Porcentaje,'1',',','.') ."</td>
<td  id='td_datos_6' style='display:none'>".$Cuota."</td>
<td  id='td_datos_7' style='display:none'>".number_format($preciocuota,'0',',','.') ."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarvistacompra($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Porcentaje,Cuota,preciocuota
 from  detallesprecio 
where cod_producto=? ";
$pagina = "";   
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



$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Porcentaje = utf8_encode($valor['Porcentaje']);          
$Cuota = utf8_encode($valor['Cuota']);          
$preciocuota = utf8_encode($valor['preciocuota']);          

	 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetallespreciocompra(this)'>
<td  id='td_datos_1' style='width:50%'>".number_format($precio,'0',',','.') ."</td>
<td  id='td_datos_2' style='width:50%'>".$descripcion."</td>
<td  id='td_datos_3' style='display:none'>".$iddetallesprecio."</td>
<td  id='td_datos_4' style='display:none'>".$comision."</td>
<td  id='td_datos_5' style='display:none'>".number_format($Porcentaje,'1',',','.') ."</td>
<td  id='td_datos_6' style='display:none'>".$Cuota."</td>
<td  id='td_datos_7' style='display:none'>".number_format($preciocuota,'0',',','.') ."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  BuscarRegistroEnVista($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Porcentaje
 from  detallesprecio 
where cod_producto=? ";
$pagina = "";   
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



$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Porcentaje = utf8_encode($valor['Porcentaje']);          


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='td_datos_1' style='width:50%'>".number_format($precio,'0',',','.') ."</td>
<td  id='td_datos_2' style='width:50%'>".$descripcion."</td>
<td  id='td_datos_3' style='display:none'>".$iddetallesprecio."</td>
<td  id='td_datos_4' style='display:none'>".$comision."</td>
<td  id='td_datos_5' style='display:none'>".$Porcentaje."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarabmproductos($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Porcentaje
 from  detallesprecio 
where cod_producto=? ";
$pagina = "";   
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



$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Porcentaje = utf8_encode($valor['Porcentaje']);             


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='td_datos_2' style='width:70%'>".$descripcion."</td>
<td  id='td_datos_7' style='width:30%'>".number_format($precio,'0',',','.') ."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}



function  BuscarRegistroTabla($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,precio_compra,precio_producto,p.porcentaje as porcen,d.cod_producto,iddetallesprecio,
d.comision,d.Porcentaje,Cuota,preciocuota
 from  detallesprecio d inner join producto p on p.cod_producto= d.cod_producto
where d.cod_producto='$buscar' ";



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


$cod_producto = utf8_encode($valor['cod_producto']); 
$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Porcentaje = utf8_encode($valor['Porcentaje']);          
$Cuota = utf8_encode($valor['Cuota']);          
$preciocuota = utf8_encode($valor['preciocuota']);     
$precio_compra = utf8_encode($valor['precio_compra']);   
$precio_producto = utf8_encode($valor['precio_producto']);   
$porcentaje = utf8_encode($valor['porcen']);  


$ImputCuotas="<input id='inptCuotas_$iddetallesprecio' type='text' value='$Cuota' class='inputText'  />";  
$ImputPrecioContado="<input id='inptPrecioContado_$iddetallesprecio' type='text' value='$precio_producto' class='inputText'  />";  
$ImputPorcentajeContado="<input id='inptPorcenContado_$iddetallesprecio' type='text' value=$porcentaje class='inputText'  />";  

$ImputPrecioAntes="<input id='ImputPrecioAntes_$iddetallesprecio' type='text' value=$precio class='inputText'  />";  
$ImputCod_producto="<input id='ImputCod_producto_$iddetallesprecio' type='text' value=$cod_producto class='inputText'  />";  

$ImputPorcentaje="<input id='inptPor_$iddetallesprecio' type='text' value='$Porcentaje' class='inputText'  />";
$Accion="<input type='Button'  value='Guardar' class='btn4' id='$iddetallesprecio' name='$precio_compra' onclick='EditarEstePrecioDetalleTabla(this)' style='background-color: #2196F3;'  />";     

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetallesprecio(this)'>
<td  id='td_datos_1' style='width:10%'>".$ImputPorcentaje."</td>
<td  id='td_datos_2' style='width:10%'>".$Cuota."</td>
<td  id='td_datos_3' style='width:20%'>".number_format($preciocuota,'0',',','.')."</td>
<td  id='td_datos_6' style='width:20%'>".$descripcion."</td>
<td  id='td_datos_4' style='width:20%'>".number_format($precio,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".$Accion."</td>
<td  id='td_datos_8' style='display:none'>".$ImputCuotas."</td>
<td  id='td_datos_9' style='display:none'>".$ImputPrecioContado."</td>
<td  id='td_datos_10' style='display:none'>".$ImputPorcentajeContado."</td>
<td  id='td_datos_10' style='display:none'>".$ImputPrecioAntes."</td>
<td  id='td_datos_10' style='display:none'>".$ImputCod_producto."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}







function  BuscarRegistroPresupuesto($buscar,$Entrega,$Total)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,precio_compra,precio_producto,p.porcentaje as porcen,d.cod_producto,iddetallesprecio,
d.comision,d.Porcentaje as porcenta,Cuota,preciocuota
 from  detallesprecio d inner join producto p on p.cod_producto= d.cod_producto group by Cuota asc   ";

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
$contdor=0;
$precio_producto =0;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$precio = utf8_encode($valor['precio']);             
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$porcenta = utf8_encode($valor['porcenta']);          
$Cuota = utf8_encode($valor['Cuota']);          
$preciocuota = utf8_encode($valor['preciocuota']);     
$precio_compra = utf8_encode($valor['precio_compra']);   
$precio_producto = $Total;
$porcen = utf8_encode($valor['porcen']);  

$Entrega = quitarseparadormiles($Entrega);

$Resultado=$porcenta-$porcen;

if($contdor==0){
	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetallesprecio(this)'>
<td  id='td_datos_2' style='width:20%'>CONTADO</td>
<td  id='td_datos_3' style='width:20%'>".number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_6' style='width:40%'>1 x ".number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_4' style='width:20%'>".number_format($precio_producto,'0',',','.')."</td>
</tr>
</table>";
}

	$precioCuotas=(($precio_producto - $Entrega)+round((($precio_producto - $Entrega) * $porcenta)/100))/$Cuota;
	$descripcion=$Cuota." x ".number_format($precioCuotas,'0',',','.');
	$TotalPrecio=(($precio_producto - $Entrega)+round((($precio_producto - $Entrega) * $porcenta)/100));


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetallesprecio(this)'>
<td  id='td_datos_2' style='width:20%'>".$Cuota."</td>
<td  id='td_datos_3' style='width:20%'>".number_format($precioCuotas,'0',',','.')."</td>
<td  id='td_datos_6' style='width:40%'>".$descripcion."</td>
<td  id='td_datos_4' style='width:20%'>".number_format($TotalPrecio,'0',',','.')."</td>
</tr>
</table>";

$contdor++;
}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" => number_format($TotalPrecio,'0',',','.'));
echo json_encode($informacion);	
exit;
}


function abmAuditoria($nombre_descripcion,$precio_compra,$precio_venta,$stock,$cod_barra,$nombredescripcionAnt,$precio_compraAnt,$precio_ventaAnt,$stockAnt,$cod_barraAnt,$fecha,$cod_usuarioFK,$Accion,$cod_productoFK)
{
	
	$cod_local=$_POST["cod_local"];
 	$cod_local=utf8_decode($cod_local);
	
	
	if($nombre_descripcion=="" && $precio_compra=="0" && $precio_venta==0 && $stock=="0" && $cod_barra==""){
		
	}else{	
$mysqli=conectar_al_servidor(); 


$consulta1="Insert into auditoriaProducto (nombre_descripcion, precio_compra, precio_venta, stock, cod_barra, nombredescripcionAnt, precio_compraAnt, precio_ventaAnt, stockAnt, cod_barraAnt, fecha, cod_usuarioFK,accion,cod_productoFK,cod_localfk)
values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssssssss';
$stmt1->bind_param($ss,$nombre_descripcion,$precio_compra,$precio_venta,$stock,$cod_barra,$nombredescripcionAnt,$precio_compraAnt,$precio_ventaAnt,$stockAnt,$cod_barraAnt,$fecha,$cod_usuarioFK,$Accion,$cod_productoFK,$cod_local);


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
	}
	
}









ObtenerDatos($operacion);

?>
<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);
include('quitarseparadormiles.php');
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
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
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}





	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$idsueldo=$_POST['idsueldo'];
$idsueldo = utf8_decode($idsueldo);
$comision=$_POST['comision'];
$comision = quitarseparadormiles($comision);
$totalrecaudado=$_POST['totalrecaudado'];
$totalrecaudado = quitarseparadormiles($totalrecaudado);
$sueldo=$_POST['sueldo'];
$sueldo = quitarseparadormiles($sueldo);
	$fecha=$_POST['fecha'];
$fecha = utf8_decode($fecha);
	$cod_persona=$_POST['cod_persona'];
$cod_persona = utf8_decode($cod_persona);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);
$tipouser=$_POST['tipouser'];
$tipouser = utf8_decode($tipouser);


	abm($idsueldo,$comision,$totalrecaudado,$sueldo,$fecha,$cod_persona,$estado,$tipo,$tipouser,$operacion);

}

if($operacion=="buscar")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$estado=$_POST['estado'];
$estado = utf8_decode($estado);
$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$tipo=$_POST['tipo'];
$tipo = utf8_decode($tipo);
	buscar($fecha1,$fecha2,$estado,$buscar,$tipo);

}	




}

function abm($idsueldo,$comision,$totalrecaudado,$sueldo,$fecha,$cod_persona,$estado,$tipo,$tipouser,$operacion)
{
	
	
if( $sueldo=="" || $fecha==""  || $cod_persona==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}



$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo") 
{


$consulta1="Insert into sueldo (comision,totalrecaudado,sueldo,fecha,codpersona,estado,tipo,tipouser)
values(?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssss';
$stmt1->bind_param($ss,$comision,$totalrecaudado,$sueldo,$fecha,$cod_persona,$estado,$tipo,$tipouser);


}


if($operacion=="editar")
{

$consulta1="Update sueldo set comision=?,totalrecaudado=?,sueldo=?,fecha=?,codpersona=?,estado=?,tipo=?,tipouser=? where idsueldo=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssss';
$stmt1->bind_param($ss,$comision,$totalrecaudado,$sueldo,$fecha,$cod_persona,$estado,$tipo,$tipouser,$idsueldo); 

}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}



function buscar($fecha1,$fecha2,$estado,$buscar,$tipo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $condiciontipo="";
	 if($tipo!=""){
		$condiciontipo="and tipo='$tipo'"; 
	 }
	
		 $sql= "Select idsueldo,comision,totalrecaudado,sueldo,fecha,codpersona,estado,tipo,tipouser,
		IF(tipouser='1',(Select nombre_persona from persona where codpersona=cod_persona),(Select nombre from vendedor where codpersona=idvendedor)) as usuarionombre
		from sueldo where IF(tipouser='1',(Select nombre_persona from persona where codpersona=cod_persona),(Select nombre from vendedor where codpersona=idvendedor)) like '%".$buscar."%' 
		and fecha>='$fecha1' and fecha<='$fecha2' and estado='$estado'  ".$condiciontipo;
	 
	
		
		   
  
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $total=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idsueldo=$valor['idsueldo'];
		      $comision=$valor['comision'];
		  	  $totalrecaudado=utf8_encode($valor['totalrecaudado']);
		  	  $sueldo=utf8_encode($valor['sueldo']);
		  	  $cod_persona=utf8_encode($valor['codpersona']);
		  	  $fecha=utf8_encode($valor['fecha']);
		  	  $estado=utf8_encode($valor['estado']);
		  	  $tipo=utf8_encode($valor['tipo']);
		  	  $usuarionombre=utf8_encode($valor['usuarionombre']);
		  	  $tipouser=utf8_encode($valor['tipouser']);
			  
		  	 $total=$total+$sueldo;
		  	 
			    	 
		  	  $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmSueldo(this)'>
<td id='td_id' style='display:none'>".$idsueldo."</td>
<td  id='td_datos_1' style='width:10%'>".$usuarionombre."</td>
<td  id='td_datos_2' style='display:none'>". number_format($totalrecaudado,'0',',','.')."</td>
<td  id='td_datos_3' style='display:none'>". number_format($comision,'0',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>". number_format($sueldo,'0',',','.')."</td>
<td  id='td_datos_5' style='width:10%'>".$fecha."</td>
<td  id='' style='width:10%'>".$tipo."</td>
<td  id='td_datos_6' style='display:none'>".$tipo."</td>
<td  id='td_datos_7' style='display:none'>".$estado."</td>
<td  id='td_datos_8' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_9' style='display:none'>".$tipouser."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro,"4" =>  number_format($total,'0',',','.'));
echo json_encode($informacion);	
exit;


}


function buscarevaluacion($fecha1,$fecha2)
{
	
$totalgastos=buscaregastos($fecha1,$fecha2);
//$totalcompras=buscarcompras($fecha1,$fecha2);
$totalpagos=buscarpagos($fecha1,$fecha2);
$ganancia=$totalpagos-$totalgastos;
$styleName="tableRegistroSearch";


  $styleName=CargarStyleTable($styleName);
  $pagina="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<td  id='' style='width:10%'>". number_format($totalgastos,'0',',','.')."</td>
<td  id='' style='width:10%'>". number_format($totalpagos,'0',',','.')."</td>
<td  id='' style='width:10%'>". number_format($ganancia,'0',',','.')."</td>
</tr>
</table>";
/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}

function buscaregastos($fecha1,$fecha2)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select sum(monto) as total from gastos where fecha>='$fecha1' and fecha<='$fecha2' and estado='Activo' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

 $total=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $total=$valor['total'];
		  	
		  
	  }
 }
 
 
return $total;


}

function buscarcompras($fecha1,$fecha2)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select sum(total_compra) as total from compra where fecha_compra>='$fecha1' and fecha_compra<='$fecha2' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

 $total=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $total=$valor['total'];
		  	
		  
	  }
 }
 
 
return $total;


}

function buscarpagos($fecha1,$fecha2)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select sum(Monto) as total from pago where Fecha>='$fecha1' and Fecha<='$fecha2' ";
		
   
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

 $total=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $total=$valor['total'];
		  	
		  
	  }
 }
 
 
return $total;


}



verificar($operacion);
?>
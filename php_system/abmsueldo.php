<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
include('quitarseparadormiles.php');
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
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
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}





	
if($operacion=="nuevo" || $operacion=="editar")
{
	
	
	$idsueldo=$_POST['idsueldo'];
$idsueldo = mb_convert_encoding((string)($idsueldo), 'ISO-8859-1', 'UTF-8');
$comision=$_POST['comision'];
$comision = quitarseparadormiles($comision);
$totalrecaudado=$_POST['totalrecaudado'];
$totalrecaudado = quitarseparadormiles($totalrecaudado);
$sueldo=$_POST['sueldo'];
$sueldo = quitarseparadormiles($sueldo);
	$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
	$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
$tipouser=$_POST['tipouser'];
$tipouser = mb_convert_encoding((string)($tipouser), 'ISO-8859-1', 'UTF-8');


	abm($idsueldo,$comision,$totalrecaudado,$sueldo,$fecha,$cod_persona,$estado,$tipo,$tipouser,$operacion);

}

if($operacion=="buscar")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
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
		  	  $totalrecaudado=mb_convert_encoding((string)($valor['totalrecaudado']), 'UTF-8', 'ISO-8859-1');
		  	  $sueldo=mb_convert_encoding((string)($valor['sueldo']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_persona=mb_convert_encoding((string)($valor['codpersona']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $tipo=mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');
		  	  $usuarionombre=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
		  	  $tipouser=mb_convert_encoding((string)($valor['tipouser']), 'UTF-8', 'ISO-8859-1');
			  
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
<?php


$funt = $_POST['funt'];
$funt = utf8_decode($funt);

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");
function verificar($funt)
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


	
if($funt=="nuevo" || $funt=="editar")
{
	
	
	$cod_categoria=$_POST['idabm'];
    $cod_categoria = utf8_decode($cod_categoria);
	$descripcion=$_POST['descripcion'];
    $descripcion = utf8_decode($descripcion);
	$Estado=$_POST['Estado'];
    $Estado = utf8_decode($Estado);

    
    
	abm($cod_categoria,$descripcion,$Estado,$funt);

}

if($funt=="buscar")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
$monto=$_POST['monto'];
$monto = utf8_decode($monto);
$nroid=$_POST['nroid'];
$nroid = utf8_decode($nroid);
	buscar($buscar,$monto,$nroid);

}	

if($funt=="buscarOption")
{

	buscarOption();

}	


}

function abm($cod_categoria,$descripcion,$Estado,$funt)
{
	
	if($descripcion=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();

	if($funt=="nuevo")
	{
				$consulta= "Select count(*) from categoria where descripcion=? and Estado ='Activo' ";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='s';
$stmt->bind_param($ss, $descripcion); 


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

if($valor==1)
{
	$informacion =array("1" => "EX");
	echo json_encode($informacion);	
	exit;
}   
	}
	if($funt=="nuevo")
	{
	
    
    $consulta="insert into categoria (descripcion,Estado) values (?,?)";	
     $stmt = $mysqli->prepare($consulta);
    $ss='ss';
    $stmt->bind_param($ss,$descripcion,$Estado); 
        
 
	}
	if($funt=="editar")
	{
        
        
    
    $consulta="Update categoria set descripcion=?,Estado=? where cod_categoria=?";	

	$stmt = $mysqli->prepare($consulta);
        


    $ss='sss';
        
    $stmt->bind_param($ss,$descripcion,$Estado,$cod_categoria); 
        
	
       
	}
	
if ( ! $stmt->execute() ) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}


function buscar($buscar,$monto,$nroid)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= " SELECT iddescripcionpr, nombre , porcentaje , 
		(select monto_impuesto from impuesto where cod_impuestoFK = cod_Impuesto) as impuesto ,
		estado FROM  descripcionpr where estado='Activo' and cod_productoFK='$buscar' and porcentaje!=0 ";
		
   $stmt = $mysqli->prepare($sql);
  
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 $styleName="tableRegistroSearch";
 
 $TotalCalculo=0;
 $totalInteres="";
 
 $TotalivaInteres="0";
 
$obtenerValor=0;

$asignarValor=0;
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
				$iddescripcionpr=$valor['iddescripcionpr']; 
		      $nombre=$valor['nombre'];
		  	  $porcentaje=utf8_encode($valor['porcentaje']);
		  	  $impuesto=utf8_encode($valor['impuesto']);
			  
			  $ResultadoCalculo= $monto * $porcentaje;
			  
			  $totalInteres= $totalInteres +  $ResultadoCalculo/11;
			  
			  $cod_producto="";
			  $cod_barra="";
			  if($iddescripcionpr=="1"){
				  $cod_producto="10002";
				  $cod_barra=" 002";
				   $TotalivaInteres= $totalInteres;
			  }
			  
			  if($iddescripcionpr=="2"){
				  $cod_producto="10003";
				  $cod_barra=" 003";
				  
				  if($ResultadoCalculo>="500000"){
					  
					  $obtenerValor= $ResultadoCalculo ;
					  $ResultadoCalculo=500000;
					  
					  $asignarValor=  $obtenerValor -  $ResultadoCalculo ;
					  
				  }
				  
			  }
			  
			  
			  if($iddescripcionpr=="4"){
				  $cod_producto="10005";
				  $cod_barra=" 005";
			  }
			  
			
			  
			   if($iddescripcionpr=="3"){
				  $cod_producto="10004";
				  $cod_barra=" 004";
				  
				  $ResultadoCalculo = $ResultadoCalculo + $asignarValor;
				  
				 $ResultadoCalculo= $ResultadoCalculo + ($totalInteres - $TotalivaInteres )  ; 
					
				 }
			 
			 $TotalCalculo=  $TotalCalculo +  number_format($ResultadoCalculo,'0','','') ;
			  
		  	 $styleName=CargarStyleTable($styleName);
			  $pagina.=" <table id='tdDetalleVenta_".$nroid."' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='SeleccionarProductoVentaOffline(this)'  name='tdDetalleVentaOffline'>
<td id='td_id_1' style='display:none'>$cod_producto</td>
<td id='td_id_2' style='display:none'>".$nroid."</td>
<td  id='td_datos_8' style='width:5%'>$cod_barra</td>
<td  id='td_datos_1' style='width:20%;'>".$nombre."</td>
<td  id='td_datos_6' style='display:none'></td>
<td  id='td_datos_3' style='width:10%'>".number_format($ResultadoCalculo,'0',',','.')."</td>
<td  id='td_datos_4' style='width:5%'>1</td>
<td  id='td_datos_9' style='display:none'>0</td>
<td  id='td_datos_5' style='width:10%'>".number_format($ResultadoCalculo,'0',',','.')."</td>
<td  id='td_datos_7'  style='display:none'>0</td>
<td  id='td_datos_10' style='display:none'>1</td>
<td  id='td_datos_11' style='display:none'>0</td>
<td  id='td_datos_12' style='display:none'>0</td>
<td  id='td_datos_13' style='display:none'>0</td>
<td  id='td_datos_14' style='display:none'>0</td>
<td  id='td_datos_15' style='display:none'>".number_format($ResultadoCalculo,'0','','')."</td>
</tr>
</table>";
			  
	  }
 }
 
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina,"3"=> $TotalCalculo);
echo json_encode($informacion);	
exit;


}



verificar($funt);
?>
<?php
include('quitarseparadormiles.php');

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
if($resp!="ok"){
$informacion =array("1" => "UI");
echo json_encode($informacion);	
exit;
}


	


	
if($operacion=="nuevo"  || $operacion=='quitar')
{
	
	
$cantidad_detalle_compra=$_POST['cantidad_detalle_compra'];
$cantidad_detalle_compra = quitarseparadormiles($cantidad_detalle_compra);
$precio_producto=$_POST['precio_producto'];
$precio_producto = quitarseparadormiles($precio_producto);
$subTotal=$_POST['subTotal'];
$subTotal = quitarseparadormiles($subTotal);
$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = utf8_decode($cod_productoFK);
$cod_compraFK=$_POST['cod_compraFK'];
$cod_compraFK = utf8_decode($cod_compraFK);
$cod_detalle_compra=$_POST['cod_detalle_compra'];
$cod_detalle_compra = utf8_decode($cod_detalle_compra);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);

$editPrecioLista=$_POST['editPrecioLista'];
$editPrecioLista = utf8_decode($editPrecioLista);

$precioLista=$_POST['precioLista'];
$precioLista = quitarseparadormiles($precioLista);

if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}

if($cod_compraFK==""){
	
	$tipocompra=$_POST['tipocompra'];
$tipocompra = utf8_decode($tipocompra);

$timbrado=$_POST['timbrado'];
$timbrado = utf8_decode($timbrado);

$tipofactura=$_POST['tipofactura'];
$tipofactura = utf8_decode($tipofactura);

$fecha_compra=$_POST['fecha_compra'];
$fecha_compra = utf8_decode($fecha_compra);
$cod_proveedorFK=$_POST['cod_proveedorFK'];
$cod_proveedorFK = utf8_decode($cod_proveedorFK);
$num_comprobante=$_POST['num_comprobante'];
$num_comprobante = utf8_decode($num_comprobante);

$descuento=$_POST['descuento'];
$descuento = quitarseparadormiles($descuento);
$pagado1=$_POST['pagado1'];
$pagado1 = quitarseparadormiles($pagado1);
$pagado2=$_POST['pagado2'];
$pagado2 = quitarseparadormiles($pagado2);



$cod_compraFK=insertarDatosCompras($tipocompra,$timbrado,$tipofactura,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2);

}
	 abm($editPrecioLista,$precioLista,$cantidad_detalle_compra,$precio_producto,$subTotal,$cod_productoFK,$cod_compraFK,$cod_detalle_compra,$cod_local,$operacion);

}

if($operacion=="buscar")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	buscar($buscar);

}	

if($operacion=="detalleenhistorial")
{
	$buscar=$_POST['buscar'];
$buscar = utf8_decode($buscar);
	detalleenhistorial($buscar);

}	

if($operacion=="buscarproductocomprados")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$marca=$_POST['marca'];
$marca = utf8_decode($marca);
	$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$categoria=$_POST['categoria'];
$categoria = utf8_decode($categoria);
$codigo=$_POST['codigo'];
$codigo = utf8_decode($codigo);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	buscarproductocomprados($marca,$fecha1,$fecha2,$cod_local,$categoria,$codigo,$producto);
}	
if($operacion=="buscarmasproductocomprados")
{
$fecha1=$_POST['fecha1'];
$fecha1 = utf8_decode($fecha1);
$fecha2=$_POST['fecha2'];
$fecha2 = utf8_decode($fecha2);
$marca=$_POST['marca'];
$marca = utf8_decode($marca);
$cod_local=$_POST['cod_local'];
$cod_local = utf8_decode($cod_local);
$categoria=$_POST['categoria'];
$categoria = utf8_decode($categoria);
$codigo=$_POST['codigo'];
$codigo = utf8_decode($codigo);
$producto=$_POST['producto'];
$producto = utf8_decode($producto);
$registrocargado=$_POST['registrocargado'];
$registrocargado = utf8_decode($registrocargado);
$totalcompra=$_POST['totalcompra'];
$totalcompra = utf8_decode($totalcompra);
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	buscarmasproductocomprados($marca,$fecha1,$fecha2,$cod_local,$categoria,$codigo,$producto,$registrocargado,$totalcompra);

}	



}


function insertarDatosCompras($tipocompra,$timbrado,$tipofactura,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2)
{
	
	
$mysqli=conectar_al_servidor(); 


/*AUDITORIA*/
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
$user=$_POST['useru'];
$user = utf8_decode($user);

$consulta1="Insert into compra (fecha_compra,cod_proveedorFK,num_comprobante,cod_local,descuento,pagado1,pagado2,cod_user_insert,fecha_insert,estado,tipo_compra,timbrado,tipoFactura)
values(?,?,?,?,?,?,?,?,?,'Activo',?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssssss';
$stmt1->bind_param($ss,$fecha_compra,$cod_proveedorFK,$num_comprobante,$cod_local,$descuento,$pagado1,$pagado2,$user,$fecha_inser_edit,$tipocompra,$timbrado,$tipofactura);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}

	$cod_compra=obtenerIdCompra($cod_proveedorFK,$num_comprobante,$cod_local);

	return $cod_compra;
	
}


function obtenerIdCompra($cod_proveedorFK,$num_comprobante,$cod_local)
{
	$mysqli=conectar_al_servidor();
	 $cod_compra='';
		$sql= "Select cod_compra from compra where cod_proveedorFK='$cod_proveedorFK' and num_comprobante='$num_comprobante' and cod_local='$cod_local' order by fecha_compra desc limit 1   ";
		
   
   
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
		  
		  
		      $cod_compra=$valor['cod_compra'];
		  	
			  
			  
	  }
 }
 
 
return $cod_compra;


}



function  abm($editPrecioLista,$precioLista,$cantidad_detalle_compra,$precio_producto,$subTotal,$cod_productoFK,$cod_compraFK,$cod_detalle_compra,$cod_local,$operacion)
{
	
	if($cod_productoFK=="" || $cod_compraFK==""){
	 $informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();
$subTotal=$cantidad_detalle_compra*$precio_producto;
	  
	
	if($operacion=="nuevo")
	{
		

	$consulta="Insert into detalle_compra (cantidad_detalle_compra,cod_productoFK,precio_producto,cod_compraFK,subTotal,precio_compra,cantidadCompra) values(?,?,?,?,?,?,?)";	

	$stmt = $mysqli->prepare($consulta);


$ss='sssssss';

$stmt->bind_param($ss,$cantidad_detalle_compra,$cod_productoFK,$precio_producto,$cod_compraFK,$subTotal,$precioLista,$cantidad_detalle_compra); 

	}
	
	if($operacion=="quitar")
	{
	$consulta="delete  from detalle_compra  where cod_detalle_compra=$cod_detalle_compra";	
	

	$stmt = $mysqli->prepare($consulta);

	}
	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$total_compra=0;
if($operacion=="nuevo"){
editar_cantidad($cod_productoFK,$cantidad_detalle_compra,"suma",$cod_local);
if($editPrecioLista=="si"){
	editar_costos($cod_productoFK,$precioLista);
	BuscarRegistroDetallePrecio($cod_productoFK);
}

}
if($operacion=="quitar"){
editar_cantidad($cod_productoFK,$cantidad_detalle_compra,"resta",$cod_local);
}


  $informacion =array("1" => "exito","2" => $cod_compraFK);
echo json_encode($informacion);	
exit;

}


function  BuscarRegistroDetallePrecio($cod_producto)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion, (select porcentaje from producto p where p.cod_producto=dp.cod_producto)as porcentajeContado ,(select precio_producto from producto p where p.cod_producto=dp.cod_producto)as PrecioContado ,cod_producto,iddetallesprecio,comision,Porcentaje,Cuota,preciocuota
 from  detallesprecio  dp where cod_producto=? ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$cod_producto);
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
$porcentajeContado = utf8_encode($valor['porcentajeContado']);  

$PrecioContado = utf8_encode($valor['PrecioContado']);  


$Resultado=$Porcentaje-$porcentajeContado;
	$cuota=$Cuota;
	$precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	$TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));

        
EditTablaPrecios($iddetallesprecio,$TotalPrecio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$precioCuotas,"");

}
}

}





function EditTablaPrecios($iddetallesprecio,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota,$operacion)
{

if( $cod_producto=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 



$consulta1="update detallesprecio set  precio=$precio ,descripcion='$descripcion' ,cod_producto=$cod_producto ,comision=$comision ,Porcentaje=$Porcentaje ,Cuota=$Cuota ,preciocuota=$preciocuota where iddetallesprecio=$iddetallesprecio ";
$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}


}







	function editar_cantidad($idproductos,$cantidad,$t,$cod_localfk){
       $mysqli=conectar_al_servidor(); 
 
	    if($t=="resta"){
			$consulta="Update stocklocales set cantidad=(cantidad-$cantidad)  where cod_productofk='".$idproductos."' and cod_localfk='".$cod_localfk."'";
		
				

	}else{
		 $consulta="Update stocklocales set cantidad=(cantidad+$cantidad)  where cod_productofk='".$idproductos."' and cod_localfk='".$cod_localfk."'";
          
			

	}


	$stmt = $mysqli->prepare($consulta);


	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


    }
	
	function obtenerStockActual($codProducto)
{
	$mysqli=conectar_al_servidor();
	 $Stock='';
		$sql= "Select stock_producto
		from producto where cod_producto='$codProducto' ";
		

   
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
		  
		  
		      $Stock=$valor['stock_producto'];
		  	 
			  
	  }
 }
 
 
  return $Stock;

}

	
	function editar_costos($idproductos,$costo){
       $mysqli=conectar_al_servidor(); 

			$consulta="Update producto set precio_compra='$costo'  where cod_producto='".$idproductos."'";	

	


	$stmt = $mysqli->prepare($consulta);


	
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}


    }



function buscar($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select dc.cod_detalle_compra,dc.cantidad_detalle_compra,dc.precio_producto,dc.subTotal,dc.cod_productoFK,pro.nombre_producto,dc.cod_compraFK,
		(select descuento from compra where cod_compra=cod_compraFK) as descuento
		from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK
		where dc.cod_compraFK = ? ";
		$total_compra=0;
		$totaldescuento=0;
		$nroRegistro=0;
   
   
   if ($stmt = $mysqli->prepare($sql)) 
  	$s='s';
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro=$valor;
  $controlDescuento="";
  $styleName="tableRegistroSearch";
  
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_detalle_compra=$valor['cod_detalle_compra'];
		  	  $cantidad_detalle_compra=utf8_encode($valor['cantidad_detalle_compra']);
		  	  $precio_producto=utf8_encode($valor['precio_producto']);
		  	  $subTotal=utf8_encode($valor['subTotal']);
		  	  $cod_productoFK=utf8_encode($valor['cod_productoFK']);
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cod_compraFK=utf8_encode($valor['cod_compraFK']);
		  	  $descuento=utf8_encode($valor['descuento']);
		  	 if($controlDescuento!=$cod_compraFK){
				  
				  $controlDescuento=$cod_compraFK;
				   $totaldescuento=$totaldescuento+$descuento;
			  }
		  	 
		  	 $total_compra=$subTotal+$total_compra;
			    	   $totaldetalle=$subTotal;
			   $styleName=CargarStyleTable($styleName); 	 
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmdetallecompra(this)'>
<td  id='td_datos_1' style='width:10%'>".$nombre_producto."</td>
<td  id='td_datos_2'  style='width:10%'>".number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_3'  style='width:10%'>".number_format($cantidad_detalle_compra,'2',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($subTotal,'0',',','.')."</td>
<td  id='td_id_1' style='display:none'>".$cod_productoFK."</td>
<td  id='td_id_2' style='display:none'>".$cod_detalle_compra."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
 $subtotalcompra=$total_compra;
 $total_compra=$total_compra-$totaldescuento;
 if($total_compra<0){
	$total_compra=0; 
 }
 
  $informacion =array("1" => "exito","2" => $pagina,"3" => number_format($total_compra,'0',',','.'),"4" => number_format($nroRegistro,'0',',','.'),"5" => number_format($subtotalcompra,'0',',','.'),"6" => number_format($totaldescuento,'0',',','.'));
echo json_encode($informacion);	
exit;

}

function detalleenhistorial($buscar)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "Select dc.cod_detalle_compra,dc.cantidad_detalle_compra,dc.precio_producto,dc.subTotal,dc.cod_productoFK,pro.nombre_producto,dc.cod_compraFK,
		(select descuento from compra where cod_compra=cod_compraFK) as descuento
		from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK
		where dc.cod_compraFK = ? ";
		$total_compra=0;
		$totaldescuento=0;
		$nroRegistro=0;
   
   
   if ($stmt = $mysqli->prepare($sql)) 
  	$s='s';
//$buscar="".$buscar."";
$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro=$valor;
  $controlDescuento="";
  $styleName="tableRegistroSearch";
  
  
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_detalle_compra=$valor['cod_detalle_compra'];
		  	  $cantidad_detalle_compra=utf8_encode($valor['cantidad_detalle_compra']);
		  	  $precio_producto=utf8_encode($valor['precio_producto']);
		  	  $subTotal=utf8_encode($valor['subTotal']);
		  	  $cod_productoFK=utf8_encode($valor['cod_productoFK']);
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cod_compraFK=utf8_encode($valor['cod_compraFK']);
		  	  $descuento=utf8_encode($valor['descuento']);
		  	 if($controlDescuento!=$cod_compraFK){
				  
				  $controlDescuento=$cod_compraFK;
				   $totaldescuento=$totaldescuento+$descuento;
			  }
		  	 
		  	 $total_compra=$subTotal+$total_compra;
			    	   $totaldetalle=$subTotal;
			  $styleName=CargarStyleTable($styleName);  	 
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='td_datos_1' style='width:10%'>".$cod_productoFK."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='td_datos_2'  style='width:10%'>".number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_3'  style='width:10%'>".number_format($cantidad_detalle_compra,'2',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($subTotal,'0',',','.')."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
 
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;

}

	
function buscarproductocomprados($marca,$fecha1,$fecha2,$cod_local,$categoria,$codigo,$producto)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	  $condicionCodLocal=" and cpr.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		  $condicionCategoria=" and pro.cod_categoriaFK='$categoria' ";
		 if($categoria==""){
			$condicionCategoria=""; 
		 }
		 $condicionMarca=" and pro.cod_marcasFK='$marca' ";
		 if($marca==""){
			$condicionMarca=""; 
		 }
		 $condicionCodigo=" and pro.cod_barra='$codigo' ";
		 if($codigo==""){
			$condicionCodigo=""; 
		 }
		 $condicionproducto=" and concat(pro.nombre_producto,' ',pro.cod_producto) like '%".$producto."%' ";
		 if($producto==""){
			$condicionproducto=""; 
		 }
		 $condicionfecha=" and fecha_compra>='".$fecha1."' and fecha_compra<='".$fecha2."' ";
		 if($fecha1=="" && $fecha2=="" ){
			$condicionfecha=""; 
		 }
		$sql= "Select sum(dc.cantidad_detalle_compra) as totalCantidad,pro.cod_barra
		,sum(dc.subTotal) as totalCompra
		,dc.cod_productoFK,pro.nombre_producto
		,(select descripcion from marcas where cod_marcas= pro.cod_marcasFK limit 1 ) as NombreMarca
	   ,(select descripcion from categoria where cod_categoria= pro.cod_categoriaFK limit 1 ) as NombreCategoria
		,(Select Nombre from local l where l.cod_local=cpr.cod_local) as nombrelocal
		from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK inner join compra cpr on cpr.cod_compra=dc.cod_compraFK
		where pro.cod_barra!='0' and cpr.estado='Activo'
		 ".$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicionCodigo.$condicionproducto.$condicionfecha." group by pro.cod_producto
		 order by totalCantidad desc limit 50 ";
		 
		$total_compra=0;
		$nroRegistro=0;
   
   
   $stmt = $mysqli->prepare($sql);
  

if ( ! $stmt->execute()) {
   echo "Error";
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
		  
		  
		      $totalCantidad=$valor['totalCantidad'];
		      $totalCompra=$valor['totalCompra'];
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cod_producto=utf8_encode($valor['cod_barra']);
		  	  $NombreMarca=utf8_encode($valor['NombreMarca']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $NombreCategoria=utf8_encode($valor['NombreCategoria']);
		  	
		  	
		  	 $total_compra=$totalCompra+$total_compra;
			    	 
		  	  $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='' style='width:10%'>".$cod_producto."</td>
<td  id='' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='' style='width:10%'>".$NombreCategoria."</td>
<td  id=''  style='width:10%'>".number_format($totalCantidad,'2',',','.')."</td>
<td  id=''  style='width:10%'>".number_format($totalCompra,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
 $sql= "Select pro.cod_barra
 from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK inner join compra cpr on cpr.cod_compra=dc.cod_compraFK 
		where cpr.estado='Activo' and pro.cod_barra!='0'
		 ".$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicionCodigo.$condicionproducto.$condicionfecha." group by pro.cod_producto ";   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalregistros=$valor;
 
  $informacion =array("1" => "exito","2" => $pagina,"3" => number_format($total_compra,'0',',','.'),"4" => number_format($nroRegistro,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistros);
echo json_encode($informacion);	
exit;


}
	
function buscarmasproductocomprados($marca,$fecha1,$fecha2,$cod_local,$categoria,$codigo,$producto,$registrocargado,$totalcompra)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	  $condicionCodLocal=" and cpr.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		  $condicionCategoria=" and pro.cod_categoriaFK='$categoria' ";
		 if($categoria==""){
			$condicionCategoria=""; 
		 }
		 $condicionMarca=" and pro.cod_marcasFK='$marca' ";
		 if($marca==""){
			$condicionMarca=""; 
		 }
		 $condicionCodigo=" and pro.cod_barra='$codigo' ";
		 if($codigo==""){
			$condicionCodigo=""; 
		 }
		 $condicionproducto=" and concat(pro.nombre_producto,' ',pro.cod_producto) like '%".$producto."%' ";
		 if($producto==""){
			$condicionproducto=""; 
		 }
		 $condicionfecha=" and fecha_compra>='".$fecha1."' and fecha_compra<='".$fecha2."' ";
		 if($fecha1=="" && $fecha2=="" ){
			$condicionfecha=""; 
		 }
		$sql= "Select sum(dc.cantidad_detalle_compra) as totalCantidad,pro.cod_barra
		,sum(dc.subTotal) as totalCompra
		,dc.cod_productoFK,pro.nombre_producto
		,(select descripcion from marcas where cod_marcas= pro.cod_marcasFK limit 1 ) as NombreMarca
	   ,(select descripcion from categoria where cod_categoria= pro.cod_categoriaFK limit 1 ) as NombreCategoria
		,(Select Nombre from local l where l.cod_local=cpr.cod_local) as nombrelocal
		from detalle_compra dc inner join producto pro on pro.cod_producto=dc.cod_productoFK inner join compra cpr on cpr.cod_compra=dc.cod_compraFK
		where pro.cod_barra!='0'  and cpr.estado='Activo'
		 ".$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicionCodigo.$condicionproducto.$condicionfecha." group by pro.cod_producto
		 order by totalCantidad desc limit ".$registrocargado." , 50 ";
		 
		$total_compra=$totalcompra;
		$nroRegistro=0;
   
   
   $stmt = $mysqli->prepare($sql);
  

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro=$valor+$registrocargado;
  $styleName="tableRegistroSearch";

 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $totalCantidad=$valor['totalCantidad'];
		      $totalCompra=$valor['totalCompra'];
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $cod_producto=utf8_encode($valor['cod_barra']);
		  	  $NombreMarca=utf8_encode($valor['NombreMarca']);
		  	  $nombrelocal=utf8_encode($valor['nombrelocal']);
		  	  $NombreCategoria=utf8_encode($valor['NombreCategoria']);
		  	
		  	
		  	 $total_compra=$totalCompra+$total_compra;
			    	 
		  	  $styleName=CargarStyleTable($styleName);
			  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  id='' style='width:10%'>".$cod_producto."</td>
<td  id='' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='' style='width:10%'>".$NombreCategoria."</td>
<td  id=''  style='width:10%'>".number_format($totalCantidad,'2',',','.')."</td>
<td  id=''  style='width:10%'>".number_format($totalCompra,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";
			  
			  
	  }
 }
 
 
  $informacion =array("1" => "exito","2" => $pagina,"3" => number_format($total_compra,'0',',','.'),
  "4" => number_format($nroRegistro,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;


}


verificar($operacion);
?>
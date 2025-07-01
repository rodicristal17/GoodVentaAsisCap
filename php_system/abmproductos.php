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



if($operacion=="nuevo" || $operacion=="editar" )
{


$cod_producto=$_POST['cod_producto'];
$cod_producto = utf8_decode($cod_producto);

$nombre_producto=$_POST['nombre_producto'];
$nombre_producto = utf8_decode($nombre_producto);


$descripcion_producto=$_POST['descripcion_producto'];
$descripcion_producto = utf8_decode($descripcion_producto);

$unidad_producto=$_POST['unidad_producto'];
$unidad_producto = utf8_decode($unidad_producto);

$precio_producto=$_POST['precio_producto'];
$precio_producto = quitarseparadormiles($precio_producto);

$precio_compra=$_POST['precio_compra'];
$precio_compra = quitarseparadormiles($precio_compra);

$cod_localFK=$_POST['cod_localFK'];
$cod_localFK = utf8_decode($cod_localFK);

$comision=$_POST['comision'];
$comision = utf8_decode($comision);

$stock_producto=$_POST['stock_producto'];
$stock_producto = quitarseparadormiles($stock_producto);

$estado=$_POST['estado'];
$estado = utf8_decode($estado);

$cod_categoriaFK=$_POST['cod_categoriaFK'];
$cod_categoriaFK = utf8_decode($cod_categoriaFK);

$cod_marcasFK=$_POST['cod_marcasFK'];
$cod_marcasFK = utf8_decode($cod_marcasFK);

$cod_ImpuestoFK=$_POST['cod_ImpuestoFK'];
$cod_ImpuestoFK = utf8_decode($cod_ImpuestoFK);


$porcentaje=$_POST['porcentaje'];
$porcentaje = utf8_decode($porcentaje);

$codBarras=$_POST['codBarras'];
$codBarras = utf8_decode($codBarras);

$tipoproducto=$_POST['tipoproducto'];
$tipoproducto = utf8_decode($tipoproducto);

$CodProveedorFK=$_POST['CodProveedorFK'];
$CodProveedorFK = utf8_decode($CodProveedorFK);

$codFabricaFK=$_POST['codFabricaFK'];
$codFabricaFK = utf8_decode($codFabricaFK);

$linkproducto=$_POST['linkproducto'];
$linkproducto = utf8_decode($linkproducto);

$nombredescripcionAnt=$_POST['nombredescripcionAnt'];
$nombredescripcionAnt = utf8_decode($nombredescripcionAnt);

$precio_compraAnt=$_POST['precio_compraAnt'];
$precio_compraAnt = quitarseparadormiles($precio_compraAnt);

$precio_ventaAnt=$_POST['precio_ventaAnt'];
$precio_ventaAnt = quitarseparadormiles($precio_ventaAnt);

$stockAnt=$_POST['stockAnt'];
$stockAnt = quitarseparadormiles($stockAnt);

$cod_barraAnt=$_POST['cod_barraAnt'];
$cod_barraAnt = utf8_decode($cod_barraAnt);


abm($nombredescripcionAnt,$precio_compraAnt,$precio_ventaAnt,$stockAnt,$cod_barraAnt,$linkproducto,$codFabricaFK,$CodProveedorFK,$tipoproducto,$cod_producto,$codBarras,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$porcentaje,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$cod_localFK,$comision,$stock_producto,$estado,$operacion);

}

 
 
 if($operacion=="EnviarProductoA"){
	 
	 
 	$stock=$_POST["stock"];
 	$stock=quitarseparadormiles($stock);
	$cod_local_a=$_POST["cod_local_a"];
 	$cod_local_a=utf8_decode($cod_local_a);
	$cod_local_de=$_POST["cod_local_de"];
 	$cod_local_de=utf8_decode($cod_local_de);
	$fecha=$_POST["fecha"];
 	$fecha=utf8_decode($fecha);
	$cod_producto_fk=$_POST["cod_producto_fk"];
 	$cod_producto_fk=utf8_decode($cod_producto_fk);
	$cod_ext=$_POST["cod_ext"];
 	$cod_ext=utf8_decode($cod_ext);
	
 	EnviarProductoA($stock,$cod_local_a,$cod_local_de,$fecha,$cod_producto_fk,$cod_ext,$user);
 }
 if($operacion=="SalidaDeposito"){
	 
	 
 	$stock=$_POST["stock"];
 	$stock=quitarseparadormiles($stock);
	$cod_local_deposito=$_POST["cod_local_deposito"];
 	$cod_local_deposito=utf8_decode($cod_local_deposito);
	$fecha=$_POST["fecha"];
 	$fecha=utf8_decode($fecha);
	$cod_producto_fk=$_POST["cod_producto_fk"];
 	$cod_producto_fk=utf8_decode($cod_producto_fk);
	$cod_ext=$_POST["cod_ext"];
 	$cod_ext=utf8_decode($cod_ext);
	
 	SalidaDeposito($stock,$cod_local_deposito,$fecha,$cod_producto_fk,$cod_ext,$user);
 }

 if($operacion=="anulardespacho"){
	$cod_ext=$_POST["cod_ext"];
 	$cod_ext=utf8_decode($cod_ext);
	anulardespacho($cod_ext);
 }
 if($operacion=="anularsalidaProducto"){
	$cod_ext=$_POST["cod_ext"];
 	$cod_ext=utf8_decode($cod_ext);
	anularsalidaProducto($cod_ext);
 }

 if($operacion=="buscar"){
	 
	 
 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);
	$marca=$_POST["marca"];
 	$marca=utf8_decode($marca);
	$categoria=$_POST["categoria"];
 	$categoria=utf8_decode($categoria);
	$stock=$_POST["stock"];
 	$stock=utf8_decode($stock);
	$proveedor=$_POST["proveedor"];
 	$proveedor=utf8_decode($proveedor);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	
	$ConStock=$_POST["ConStock"];
 	$ConStock=utf8_decode($ConStock);
	
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
 	BuscarRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local,$ConStock);
 }
 if($operacion=="buscarmas"){
	 
	 
 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);
	$marca=$_POST["marca"];
 	$marca=utf8_decode($marca);
	$categoria=$_POST["categoria"];
 	$categoria=utf8_decode($categoria);
	$stock=$_POST["stock"];
 	$stock=utf8_decode($stock);
	$proveedor=$_POST["proveedor"];
 	$proveedor=utf8_decode($proveedor);
	$estado=$_POST["estado"];
 	$estado=utf8_decode($estado);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$ConStock=$_POST["ConStock"];
 	$ConStock=utf8_decode($ConStock);
	
	$registrocargado=$_POST["registrocargado"];
 	$registrocargado=utf8_decode($registrocargado);
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
 	BuscarMasRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local,$registrocargado,$ConStock);
 }

 if($operacion=="buscarporcodigoeditar"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
 	buscarporcodigoeditar($buscar);
 }


 if($operacion=="buscarvista"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$Categoria=$_POST["Categoria"];
 	$Categoria=utf8_decode($Categoria);
	$Marca=$_POST["Marca"];
 	$Marca=utf8_decode($Marca);
	$codProveedor=$_POST["codProveedor"];
 	$codProveedor=utf8_decode($codProveedor);
	buscarvista($buscar,$local,$Categoria,$Marca,$codProveedor);
 }
 
  if($operacion=="AuditoriaProducto"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$usuario=$_POST["usuario"];
 	$usuario=utf8_decode($usuario);
	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);
	AuditoriaProducto($fecha1,$fecha2,$local,$usuario,$producto);
 }
 
   if($operacion=="buscarvistaventa"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistaVenta($buscar,$local);
 }
 
    if($operacion=="buscarvistaventaSolicitud"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistaventaSolicitud($buscar,$local);
 }
 
 
 if($operacion=="buscarvistacompras"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistacompras($buscar,$local);
 }
 if($operacion=="buscarvistalistadodespacho"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistalistadodespacho($buscar,$local);
 }
 if($operacion=="buscarvistasalidadeposito"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistasalidadeposito($buscar,$local);
 }
 
 if($operacion=="buscarporcodigo"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
 	buscarporcodigo($buscar,$local);
 }
 
 if($operacion=="buscarconsultarprecios"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$categoria=$_POST["categoria"];
 	$categoria=utf8_decode($categoria);
	$marca=$_POST["marca"];
 	$marca=utf8_decode($marca);
 	buscarconsultarprecios($buscar,$local,$categoria,$marca);
 }

 if($operacion=="buscarcodBarra"){
 	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);
	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
 	buscarcodBarra($producto,$codigo,$local);
 }

 if($operacion=="buscarInventario"){
 	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);
	$codproducto=$_POST["codproducto"];
 	$codproducto=utf8_decode($codproducto);
	$stock=$_POST["stock"];
 	$stock=utf8_decode($stock);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$Categoria=$_POST['Categoria'];
	$Categoria = utf8_decode($Categoria);
	$Marcas=$_POST['Marcas'];
	$Marcas = utf8_decode($Marcas);
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
 	buscarInventario($producto,$codproducto,$stock,$local,$Categoria,$Marcas);
 }
 
 if($operacion=="buscarMasInventario"){
 	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);
	$codproducto=$_POST["codproducto"];
 	$codproducto=utf8_decode($codproducto);
	$stock=$_POST["stock"];
 	$stock=utf8_decode($stock);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$Categoria=$_POST['Categoria'];
	$Categoria = utf8_decode($Categoria);
	$Marcas=$_POST['Marcas'];
	$Marcas = utf8_decode($Marcas);
	$totalcostos=$_POST['totalcostos'];
	$totalcostos = quitarseparadormiles($totalcostos);
	$registrocargados=$_POST['registrocargados'];
	$registrocargados = utf8_decode($registrocargados);
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
 	buscarMasInventario($producto,$codproducto,$stock,$local,$Categoria,$Marcas,$totalcostos,$registrocargados);
 }

 if($operacion=="buscarCatalogo"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
 	buscarCatalogo($buscar,$local);
 }

 if($operacion=="buscarMasCatalogo"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	$registrocargado=$_POST["registrocargado"];
 	$registrocargado=utf8_decode($registrocargado);
 	buscarMasCatalogo($buscar,$local,$registrocargado);
 }

 if($operacion=="editarpreciocontado"){
 	$precioventa=$_POST["precioventa"];
 	$precioventa=quitarseparadormiles($precioventa);
	$Porcentaje=$_POST["Porcentaje"];
 	$Porcentaje=quitarseparadormiles($Porcentaje);
	$cod_producto=$_POST["cod_producto"];
 	$cod_producto=utf8_decode($cod_producto);
 	editarpreciocontado($precioventa,$Porcentaje,$cod_producto);
 }


 if($operacion=="historialdespachado"){
	 
	 
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$codlocal1=$_POST["codlocal1"];
 	$codlocal1=utf8_decode($codlocal1);
	$codlocal2=$_POST["codlocal2"];
 	$codlocal2=utf8_decode($codlocal2);
	$cod_producto=$_POST["cod_producto"];
 	$cod_producto=utf8_decode($cod_producto);
	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);	
 	historialdespachado($fecha1,$fecha2,$codlocal1,$codlocal2,$cod_producto,$producto);
 }
 if($operacion=="historialmasdespachado"){
	 
	 
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$codlocal1=$_POST["codlocal1"];
 	$codlocal1=utf8_decode($codlocal1);
	$codlocal2=$_POST["codlocal2"];
 	$codlocal2=utf8_decode($codlocal2);
	$cod_producto=$_POST["cod_producto"];
 	$cod_producto=utf8_decode($cod_producto);
	$producto=$_POST["producto"];
 	$producto=utf8_decode($producto);	
	$registrocargado=$_POST["registrocargado"];
 	$registrocargado=utf8_decode($registrocargado);	
 	historialmasdespachado($fecha1,$fecha2,$codlocal1,$codlocal2,$cod_producto,$producto,$registrocargado);
 }

 if($operacion=="comprobar_codigo"){
 	$codigo=$_POST["codigo"];
 	$codigo=utf8_decode($codigo);
 	comprobarduplicado($codigo);
 }


   if($operacion=="buscarpresupuesto"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarpresupuesto($buscar,$local);
 }



if($operacion=="ContabilidadVenta"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	ContabilidadVenta($fecha1,$fecha2,$local);

}

if($operacion=="ContabilidadCompra"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=utf8_decode($fecha1);
	$fecha2=$_POST["fecha2"];
 	$fecha2=utf8_decode($fecha2);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	ContabilidadCompra($fecha1,$fecha2,$local);

}
 
 
 if($operacion=="nuevoTablaDetallePrecio" )
{
$cod_producto=$_POST['cod_producto'];
$cod_producto = ($cod_producto);

$contado=$_POST['contado'];
$contado = quitarseparadormiles($contado);

$porcentaje=$_POST['porcentaje'];
$porcentaje = ($porcentaje);

nuevoTablaDetallePrecio($cod_producto,$contado,$porcentaje);
 }
 
  if($operacion=="EliminarProducto"){
	$cod_producto=$_POST["cod_producto"];
 	$cod_producto=utf8_decode($cod_producto);
	EliminarProducto($cod_producto);
 }
 
 
 
   if($operacion=="buscaroption"){
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscaroption($local);
 }
 
 
  
   if($operacion=="buscarporcodigoPresupuesto"){
	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	buscarporcodigoPresupuesto($buscar);
 }
 
 
 


}







function  buscarporcodigoPresupuesto($buscar)
{
$mysqli=conectar_al_servidor();

		$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,cod_barra,pr.porcentaje,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= pr.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.cod_barra = ? and pr.estado='Activo'  limit 1 ";


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



$cod_producto = utf8_encode($valor['cod_producto']);
$cod_barra = utf8_encode($valor['cod_barra']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$porcentaje = utf8_encode($valor['porcentaje']); 
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);


}
}
    
$informacion =array("1" => "exito","2" => $cod_producto,"3" => $nombre_producto,"4" => $precio_producto,"5" => $cod_barra);
echo json_encode($informacion);	
exit;
}








function EliminarProducto($cod_producto)
{
$mysqli=conectar_al_servidor(); 

$consulta1="update producto set estado='Inactivo' where cod_producto='$cod_producto'";
$stmt1 = $mysqli->prepare($consulta1);


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$informacion =array("1" => "exito","2" => $cod_producto);
echo json_encode($informacion);	
exit;


}




Function nuevoTablaDetallePrecio($cod_producto,$precioCompra,$porcentaje)
{

$PorcenContado=$porcentaje;

	if($PorcenContado=="" || $PorcenContado=="0"){
		$PorcenContado=1;
	}

	
	$porcentaje=$PorcenContado;
	$cuota=2;
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,2,$PrecioCuota,"nuevo");
	
	
	$porcentaje=$PorcenContado;
	$cuota=3;
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,3,$PrecioCuota,"nuevo");
	
	
	// $precioCuotas=($PrecioContado)/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado);
	// abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,25,3,$precioCuotas,"nuevo");
	
	
	
	
	$porcentaje=$PorcenContado;
	$cuota=4;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,4,$PrecioCuota,"nuevo");
	
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	$porcentaje=$PorcenContado;
	$cuota=5;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,5,$PrecioCuota,"nuevo");
	
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	$porcentaje=$PorcenContado;
	$cuota=6;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,6,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	
	$porcentaje=$PorcenContado;
	$cuota=8;
	
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,8,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=$PorcenContado;
	$cuota=10;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,10,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=$PorcenContado;
	$cuota=12;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,12,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=$PorcenContado;
	$cuota=15;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,15,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=$PorcenContado;
	$cuota=18;	
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,18,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	// $porcentaje=110;
	// $cuota=24;
	

	
}

/*Funcion para insertar,modificar o eliminar registros*/
function abmTabla($iddetallesprecio,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota,$operacion)
{

if( $cod_producto=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 



$consulta1="Insert into detallesprecio (precio,descripcion,cod_producto,comision,Porcentaje,Cuota,preciocuota)
values(?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

}




function  BuscarRegistroDetallePrecio($cod_producto)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion, (select porcentaje from producto p where p.cod_producto=dp.cod_producto)as porcentajeContado ,(select precio_compra from producto p where p.cod_producto=dp.cod_producto)as precio_compra ,cod_producto,iddetallesprecio,comision,Porcentaje,Cuota,preciocuota
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

$precio_compra = utf8_encode($valor['precio_compra']);  


$Resultado=$Porcentaje;
	$cuota=$Cuota;
	$precioCuotas=($precio_compra+round(($precio_compra * $Resultado)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	$TotalPrecio=($precio_compra+round(($precio_compra * $Resultado)/100));

        
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



$consulta1="update detallesprecio set  precio=$precio ,descripcion='$descripcion' ,cod_producto='$cod_producto' ,comision=$comision ,Porcentaje=$Porcentaje ,Cuota=$Cuota ,preciocuota=$preciocuota where iddetallesprecio=$iddetallesprecio ";
$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}


}




function abm($nombredescripcionAnt,$precio_compraAnt,$precio_ventaAnt,$stockAnt,$cod_barraAnt,$linkproducto,$codFabricaFK,$CodProveedorFK,$tipo,$cod_producto,$cod_barra,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$porcentaje,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$cod_localFK,$comision,$stock_producto,$estado,$operacion)
{

if($nombre_producto==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);

$mysqli=conectar_al_servidor(); 

if($operacion=="nuevo") 
{


$consulta= "Select count(*) from producto where cod_barra=?  ";
$stmt = $mysqli->prepare($consulta);
$ss='s';
$stmt->bind_param($ss,$cod_barra); 
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

if($valor>0)
{
	$informacion =array("1" => "EXPR");
	echo json_encode($informacion);	
	exit;
}  


	
$cod_producto=buscarCodigoProductos();

$consulta1="Insert into producto (CodProveedor,cod_barra,cod_producto,porcentaje,cod_categoriaFK,cod_marcasFK,cod_ImpuestoFK,nombre_producto,descripcion_producto,unidad_producto,precio_producto,precio_compra,comision,estado,tipo,cod_user_insert,fecha_insert,codFabricaFK,link)
values(?,?,?,?,?,?,?,upper(?),?,?,?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssssssssssss';
$stmt1->bind_param($ss,$CodProveedorFK,$cod_barra,$cod_producto,$porcentaje,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$comision,$estado,$tipo,$user,$fecha_inser_edit,$codFabricaFK,$linkproducto);

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

nuevoTablaDetallePrecio($cod_producto,$precio_compra,$porcentaje);

}


if($operacion=="editar")
{
$consulta1="Update producto set CodProveedor=?,tipo=?,cod_barra=?,nombre_producto=upper(?),porcentaje=?,cod_categoriaFK=?,cod_marcasFK=?,cod_ImpuestoFK=?,descripcion_producto=?,unidad_producto=?,precio_producto=?,precio_compra=?,comision=?,estado=?,cod_user_edit=?,fecha_edit=?,link=? where cod_producto=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssssssssssss';
$stmt1->bind_param($ss,$CodProveedorFK,$tipo,$cod_barra,$nombre_producto,$porcentaje,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$comision,$estado,$user,$fecha_inser_edit,$linkproducto,$cod_producto); 

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
}




if($operacion=="nuevo"){
	buscarlocalesproductos($stock_producto,$cod_producto,$cod_localFK);
}

if($operacion=="editar"){
	EditarStockA($stock_producto,$cod_producto,$cod_localFK);
	BuscarRegistroDetallePrecio($cod_producto);
	EditarStockA($stock_producto,$cod_producto,$cod_localFK);
	$fechahoy=date('Y-m-d');
	$produc=$nombre_producto."-".$descripcion_producto;
	if($produc==$nombredescripcionAnt){
		$produc="";
		$nombredescripcionAnt="";
	}	
	if($precio_compra==$precio_compraAnt){
		$precio_compra="0";
		$precio_compraAnt="0";
	}	
	if($precio_producto==$precio_ventaAnt){
		$precio_producto="0";
		$precio_ventaAnt="0";
	}
	
	if($stock_producto==$stockAnt){
		$stock_producto="0";
		$stockAnt="0";
	}
	
	if($cod_barra==$cod_barraAnt){
		$cod_barra="";
		$cod_barraAnt="";
	}
	abmAuditoria($produc,$precio_compra,$precio_producto,$stock_producto,$cod_barra,$nombredescripcionAnt,$precio_compraAnt,$precio_ventaAnt,$stockAnt,$cod_barraAnt,$fechahoy,$user,"ABM Producto",$cod_producto,$cod_localFK);
}

$informacion =array("1" => "exito","2" => $cod_producto);
echo json_encode($informacion);	
exit;

}

function editarpreciocontado($precio_producto,$porcentaje,$cod_producto)
{

if($cod_producto=="" || $porcentaje=="" || $precio_producto==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);

$mysqli=conectar_al_servidor(); 


$consulta1="Update producto set  porcentaje=?,precio_producto=?,cod_user_edit=?,fecha_edit=? where cod_producto=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$porcentaje,$precio_producto,$user,$fecha_inser_edit,$cod_producto); 


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function buscarlocalesproductos($stockproducto,$cod_productofk,$cod_localFK)
{
	$mysqli=conectar_al_servidor();	
	$sql= "Select * from local where estado='Activo' ";	  
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
		  
		  
		      $cod_local=$valor['cod_local'];
			  if($cod_localFK==$cod_local){
				  $cantidad=$stockproducto;
			  }else{
				   $cantidad=0;
			  }
			  anhadirStockA($cantidad,$cod_productofk,$cod_local);
	
	}
 }
 
 
 mysqli_close($mysqli);


}

function  buscarCodigoProductos()
{
$mysqli=conectar_al_servidor();


$sql= "select count(pr.cod_producto)+10000
 from  producto pr ";

$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nroOrden=$result->fetch_row();
$nroOrden=$nroOrden[0];
$nroOrden=$nroOrden+1;
return $nroOrden;

}

function BuscarRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local,$ConStock)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
if($local!=""){
$condicionLocal="and stk.cod_localFK='".$local."' ";
}
$condicionCategria="";
if($categoria!=""){
$condicionCategria="and pr.cod_categoriaFK='".$categoria."' ";
}

$condicionMarca="";
if($marca!=""){
$condicionMarca="and pr.cod_marcasFK='".$marca."' ";
}
$condicionCodigo="";
if($codigo!=""){
$condicionCodigo="and pr.cod_barra = '".$codigo."' ";
}
$condicionProducto="";
if($producto!=""){
$condicionProducto="and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$producto."%' ";
}
$condicionstock="";
if($stock!=""){
$condicionstock="and stk.cantidad <= '".$stock."' ";
}
$condicionproveedor="";
if($proveedor!=""){
$condicionproveedor="and (Select nombre_persona from persona where cod_persona=pr.CodProveedor limit 1) like  '%".$proveedor."%' ";
}

$condicionstockCondi="";
if($ConStock=="constock"){
$condicionstockCondi="and stk.cantidad > 0 ";
}
if($ConStock=="sinstock"){
$condicionstockCondi="and stk.cantidad <= 0 ";
}




$sql= "select pr.tipo,pr.cod_barra,pr.porcentaje,pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,
pr.unidad_producto,stk.cod_localFK,cod_categoriaFK,cod_marcasFK,cod_ImpuestoFK,pr.link,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,pr.comision,pr.estado,pr.CodProveedor,
(Select nombre_persona from persona where cod_persona=pr.CodProveedor limit 1) as proveedor,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as localnombre,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
pr.fecha_insert,pr.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='".$estado."'  ".$condicionMarca.$condicionCategria.$condicionLocal.$condicionCodigo.$condicionProducto.$condicionstock.$condicionproveedor.$condicionstockCondi." order by pr.nombre_producto asc limit 100 "; 	
$stmt = $mysqli->prepare($sql);
$pagina = "";   
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


$link = utf8_encode($valor['link']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['cod_localFK']); 
$localnombre = utf8_encode($valor['localnombre']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$cod_categoriaFK = utf8_encode($valor['cod_categoriaFK']); 
$cod_marcasFK = utf8_encode($valor['cod_marcasFK']); 
$cod_ImpuestoFK = utf8_encode($valor['cod_ImpuestoFK']); 
$porcentaje = utf8_encode($valor['porcentaje']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
$tipo = utf8_encode($valor['tipo']); 
$CodProveedorFK = utf8_encode($valor['CodProveedor']); 
$proveedor = utf8_encode($valor['proveedor']); 
$insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
//anhadirStockA($stock_producto,$cod_producto,$cod_localFK);
$totalcostos=$precio_compra*$stock_producto;
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosabmProducto(this)'>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td id='td_datos_19' style='width:10%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreImpuesto."</td>
<td  id='td_datos_13' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_11' style='width:10%'>".$NombreCategoria."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($stock_producto,'2',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_18' style='display:none'>".number_format($totalcostos,'0',',','.')."</td>
<td  id='td_datos_22' style='width:10%'>".$proveedor."</td>
<td  id='' style='width:10%'>".$localnombre."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_14' style='display:none'>".$cod_categoriaFK."</td>
<td  id='td_datos_15' style='display:none'>".$cod_marcasFK."</td>
<td  id='td_datos_16' style='display:none'>".$cod_ImpuestoFK."</td>
<td  id='td_datos_17' style='display:none'>".$porcentaje."</td>
<td  id='td_datos_20' style='display:none'>".$tipo."</td>
<td  id='td_datos_23' style='display:none'>".$CodProveedorFK."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
<td  id='td_datos_104' style='display:none'>".$link."</td>
</tr>
</table>";


}
}

$sql= "select pr.tipo
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='".$estado."'  ".$condicionMarca.$condicionCategria.$condicionLocal.$condicionCodigo.$condicionProducto.$condicionstock.$condicionproveedor.$condicionstockCondi." order by pr.nombre_producto asc  "; 	
  $stmt = $mysqli->prepare($sql); 
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($valor,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function BuscarMasRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local,$registrocargado,$ConStock)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
if($local!=""){
$condicionLocal="and stk.cod_localFK='".$local."' ";
}
$condicionCategria="";
if($categoria!=""){
$condicionCategria="and pr.cod_categoriaFK='".$categoria."' ";
}

$condicionMarca="";
if($marca!=""){
$condicionMarca="and pr.cod_marcasFK='".$marca."' ";
}
$condicionCodigo="";
if($codigo!=""){
$condicionCodigo="and pr.cod_barra = '".$codigo."' ";
}
$condicionProducto="";
if($producto!=""){
$condicionProducto="and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$producto."%' ";
}
$condicionstock="";
if($stock!=""){
$condicionstock="and stk.cantidad <= '".$stock."' ";
}
$condicionproveedor="";
if($proveedor!=""){
$condicionproveedor="and (Select nombre_persona from persona where cod_persona=pr.CodProveedor limit 1) like  '%".$proveedor."%' ";
}

$condicionstockCondi="";
if($ConStock=="constock"){
$condicionstockCondi="and stk.cantidad > 0 ";
}
if($ConStock=="sinstock"){
$condicionstockCondi="and stk.cantidad <= 0 ";
}



$sql= "select pr.tipo,pr.cod_barra,pr.porcentaje,pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,stk.cod_localFK,cod_categoriaFK,cod_marcasFK,cod_ImpuestoFK,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,pr.comision,pr.estado,pr.CodProveedor,
(Select nombre_persona from persona where cod_persona=pr.CodProveedor limit 1) as proveedor,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as localnombre,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
pr.fecha_insert,pr.fecha_edit,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='".$estado."'  ".$condicionMarca.$condicionCategria.$condicionLocal.$condicionCodigo.$condicionProducto.$condicionstock.$condicionproveedor.$condicionstockCondi." order by pr.nombre_producto asc limit ".$registrocargado.", 100 "; 	
$stmt = $mysqli->prepare($sql);
$pagina = "";   
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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



$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['cod_localFK']); 
$localnombre = utf8_encode($valor['localnombre']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$cod_categoriaFK = utf8_encode($valor['cod_categoriaFK']); 
$cod_marcasFK = utf8_encode($valor['cod_marcasFK']); 
$cod_ImpuestoFK = utf8_encode($valor['cod_ImpuestoFK']); 
$porcentaje = utf8_encode($valor['porcentaje']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
$tipo = utf8_encode($valor['tipo']); 
$CodProveedorFK = utf8_encode($valor['CodProveedor']); 
$proveedor = utf8_encode($valor['proveedor']); 
$insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
//anhadirStockA($stock_producto,$cod_producto,$cod_localFK);
$totalcostos=$precio_compra*$stock_producto;
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosabmProducto(this)'>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td id='td_datos_19' style='width:10%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreImpuesto."</td>
<td  id='td_datos_13' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_11' style='width:10%'>".$NombreCategoria."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($stock_producto,'2',',','.')."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_18' style='display:none'>".number_format($totalcostos,'0',',','.')."</td>
<td  id='td_datos_22' style='width:10%'>".$proveedor."</td>
<td  id='' style='width:10%'>".$localnombre."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_14' style='display:none'>".$cod_categoriaFK."</td>
<td  id='td_datos_15' style='display:none'>".$cod_marcasFK."</td>
<td  id='td_datos_16' style='display:none'>".$cod_ImpuestoFK."</td>
<td  id='td_datos_17' style='display:none'>".$porcentaje."</td>
<td  id='td_datos_20' style='display:none'>".$tipo."</td>
<td  id='td_datos_23' style='display:none'>".$CodProveedorFK."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
</tr>
</table>";


}
}

    
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($valor,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function EnviarProductoA($stock,$cod_local_a,$cod_local_de,$fecha,$cod_producto_fk,$cod_ext,$cod_usuario_fk)
{
	
$mysqli=conectar_al_servidor();
$consulta1="Insert into historialdespacho (stock,cod_local_a,cod_local_de,fecha,cod_producto_fk,cod_usuario_fk,cod_ext) values(?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssss';
$stmt1->bind_param($ss,$stock,$cod_local_a,$cod_local_de,$fecha,$cod_producto_fk,$cod_usuario_fk,$cod_ext);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$consulta= "Select count(*) from stocklocales where cod_productofk='$cod_producto_fk' and cod_localfk ='$cod_local_a' ";
$stmt = $mysqli->prepare($consulta);
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
	SumarRestarStockA($stock,$cod_producto_fk,$cod_local_a,"suma");
	SumarRestarStockA($stock,$cod_producto_fk,$cod_local_de,"resta");
}else{
	anhadirStockA($stock,$cod_producto_fk,$cod_local_a);
	SumarRestarStockA($stock,$cod_producto_fk,$cod_local_de,"resta");
}   


 mysqli_close($mysqli);
 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}

function SalidaDeposito($stock,$cod_local_deposito,$fecha,$cod_producto_fk,$cod_ext,$cod_usuario_fk)
{
	
$mysqli=conectar_al_servidor();
$consulta1="Insert into historialsalidadeposito (stock,cod_local_deposito,fecha,cod_producto_fk,cod_usuario_fk,cod_ext)
 values(?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$stock,$cod_local_deposito,$fecha,$cod_producto_fk,$cod_usuario_fk,$cod_ext);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

 mysqli_close($mysqli);
 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}

function anhadirStockA($cantidad,$cod_productofk,$cod_localfk)
{
$mysqli=conectar_al_servidor();
$consulta1="Insert into stocklocales (cantidad,cod_productofk,cod_localfk) values(?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$cantidad,$cod_productofk,$cod_localfk);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);

}

function EditarStockA($cantidad,$cod_productofk,$cod_localfk)
{
$mysqli=conectar_al_servidor();
$consulta1="update stocklocales set cantidad='$cantidad' where cod_productofk='$cod_productofk' and cod_localfk='$cod_localfk' ";

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);

}

function SumarRestarStockA($cantidad,$cod_productofk,$cod_localfk,$operacion)
{
$mysqli=conectar_al_servidor();
if($operacion=="suma"){
$consulta1="update stocklocales set cantidad=cantidad+$cantidad where cod_productofk=? and cod_localfk=?";
}else{
$consulta1="update stocklocales set cantidad=cantidad-$cantidad where cod_productofk=? and cod_localfk=?";
}
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$cod_productofk,$cod_localfk);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);

}

function  buscarporcodigoeditar($buscar)
{
$mysqli=conectar_al_servidor();
$sql= "select pr.tipo,pr.cod_barra,pr.porcentaje,pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,cod_categoriaFK
,cod_marcasFK,cod_ImpuestoFK,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,pr.CodProveedor,
(Select nombre_persona from persona where cod_persona=pr.CodProveedor limit 1) as proveedor,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as localnombre,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.cod_producto='$buscar' order by pr.nombre_producto asc limit 1 ";
$stmt = $mysqli->prepare($sql);
$pagina = "";   
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['cod_localFK']); 
$localnombre = utf8_encode($valor['localnombre']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$cod_categoriaFK = utf8_encode($valor['cod_categoriaFK']); 
$cod_marcasFK = utf8_encode($valor['cod_marcasFK']); 
$cod_ImpuestoFK = utf8_encode($valor['cod_ImpuestoFK']); 
$porcentaje = utf8_encode($valor['porcentaje']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
$tipo = utf8_encode($valor['tipo']); 
$CodProveedorFK = utf8_encode($valor['CodProveedor']); 
$proveedor = utf8_encode($valor['proveedor']); 

$totalcostos=$precio_compra*$stock_producto;

	  $pagina.="
<table style='display:none'>
<tr id='tbRegistroCodProducto' >
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td id='td_datos_19' style='width:10%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreImpuesto."</td>
<td  id='td_datos_13' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_11' style='width:10%'>".$NombreCategoria."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($stock_producto,'2',',','.')."</td>
<td  id='td_datos_18' style='display:none'>".number_format($totalcostos,'0',',','.')."</td>
<td  id='td_datos_22' style='width:10%'>".$proveedor."</td>
<td  id='' style='width:10%'>".$localnombre."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_14' style='display:none'>".$cod_categoriaFK."</td>
<td  id='td_datos_15' style='display:none'>".$cod_marcasFK."</td>
<td  id='td_datos_16' style='display:none'>".$cod_ImpuestoFK."</td>
<td  id='td_datos_17' style='display:none'>".$porcentaje."</td>
<td  id='td_datos_20' style='display:none'>".$tipo."</td>
<td  id='td_datos_23' style='display:none'>".$CodProveedorFK."</td>

</tr>
</table>";


}
}

$sql= "select count(pr.cod_producto) from  producto pr where  pr.estado='Activo' ";	
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$result = $stmt->get_result();
$nro_total=$result->fetch_row();
$valor=$nro_total[0];

    
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($valor,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarInventario($producto,$codproducto,$stock,$local,$Categoria,$Marcas)
{
$mysqli=conectar_al_servidor();
$condicioncategoria="";
if($Categoria!=""){
	$condicioncategoria=" and pr.cod_categoriaFK='$Categoria'";
}
$condicionmarca="";
if($Marcas!=""){
	$condicionmarca=" and pr.cod_marcasFK='$Marcas'";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%'";
}
$condicionstock="";
if($stock!=""){
	$condicionstock=" and stk.cantidad <= '".$stock."'";
}
$condicioncodproducto="";
if($codproducto!=""){
	$condicioncodproducto=" and pr.cod_barra = '".$codproducto."'";
}
$condicionlocal="";
if($local!=""){
	$condicionlocal=" and stk.cod_localFK = '".$local."'";
}
$sql= "select pr.cod_barra,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,(pr.precio_producto-pr.precio_compra) as ganancia,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='Activo' ".$condicionproducto.$condicionstock.$condicioncodproducto.$condicioncategoria.$condicionmarca.$condicionlocal." limit 50";


$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$costototales=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_barra']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$ganancia = utf8_encode($valor['ganancia']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$totalcostos=$precio_compra*$stock_producto;
$costototales=$costototales+$totalcostos;
$styleFondo="";
if($stock_producto<0){
$styleFondo="background-color:#FF5722;color:#fff";	
}
//$paginaprecios=number_format($precio_producto,'0',',','.')."Gs, Contado <br>".buscardetallespreciosb($cod_producto, $precio_producto,$comision);
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' style='$styleFondo'>
<tr id='tbSelecRegistro' >
<td  style='width:10%'>".$cod_producto."</td>
<td  style='width:20%'>".$nombre_producto."</td>
<td  style='width:10%'>".$NombreCategoria."</td>
<td  style='width:10%'>".$NombreMarca."</td>
<td  style='width:5%;'>".number_format($stock_producto,'2',',','.')."</td>
<td  style='width:5%'>".$local."</td>
</tr>
</tr>
</table>";


}
}
$sql= "select pr.cod_barra
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='Activo' ".$condicionproducto.$condicionstock.$condicioncodproducto.$condicioncategoria.$condicionmarca.$condicionlocal." ";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;    

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($costototales,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function buscarMasInventario($producto,$codproducto,$stock,$local,$Categoria,$Marcas,$totalcostos,$registrocargados)
{
$mysqli=conectar_al_servidor();
$condicioncategoria="";
if($Categoria!=""){
	$condicioncategoria=" and pr.cod_categoriaFK='$Categoria'";
}
$condicionmarca="";
if($Marcas!=""){
	$condicionmarca=" and pr.cod_marcasFK='$Marcas'";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%'";
}
$condicionstock="";
if($stock!=""){
	$condicionstock=" and stk.cantidad <= '".$stock."'";
}
$condicioncodproducto="";
if($codproducto!=""){
	$condicioncodproducto=" and pr.cod_barra = '".$codproducto."'";
}
$condicionlocal="";
if($local!=""){
	$condicionlocal=" and stk.cod_localFK = '".$local."'";
}
$sql= "select pr.cod_barra,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,(pr.precio_producto-pr.precio_compra) as ganancia,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='Activo' ".$condicionproducto.$condicionstock.$condicioncodproducto.$condicioncategoria.$condicionmarca.$condicionlocal." limit ".$registrocargados." , 50 ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargados;
$costototales=$totalcostos;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_barra']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$ganancia = utf8_encode($valor['ganancia']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$totalcostos=$precio_compra*$stock_producto;
$costototales=$costototales+$totalcostos;
$styleFondo="";
if($stock_producto<0){
$styleFondo="background-color:#FF5722;color:#fff";	
}
//$paginaprecios=number_format($precio_producto,'0',',','.')."Gs, Contado <br>".buscardetallespreciosb($cod_producto, $precio_producto,$comision);
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' style='$styleFondo'>
<tr id='tbSelecRegistro' >
<td  style='width:10%'>".$cod_producto."</td>
<td  style='width:20%'>".$nombre_producto."</td>
<td  style='width:10%'>".$NombreCategoria."</td>
<td  style='width:10%'>".$NombreMarca."</td>
<td  style='width:5%;'>".number_format($stock_producto,'2',',','.')."</td>
<td  style='width:5%'>".$local."</td>
</tr>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($costototales,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function anulardespacho($cod_ext)
{
$mysqli=conectar_al_servidor();
$sql= "select htd.idhistorialdespacho,htd.stock,htd.fecha, htd.cod_local_de, htd.cod_local_a, htd.cod_producto_fk, htd.cod_usuario_fk,
pr.nombre_producto,pr.cod_barra
from  producto pr inner join historialdespacho htd on htd.cod_producto_fk=pr.cod_producto
where htd.cod_ext='".$cod_ext."' limit 1";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$idhistorialdespacho = utf8_encode($valor['idhistorialdespacho']);
$stock = utf8_encode($valor['stock']);          
$cod_local_de = utf8_encode($valor['cod_local_de']); 
$cod_local_a = utf8_encode($valor['cod_local_a']); 
$cod_producto_fk = utf8_encode($valor['cod_producto_fk']); 
SumarRestarStockA($stock,$cod_producto_fk,$cod_local_a,"restar");
SumarRestarStockA($stock,$cod_producto_fk,$cod_local_de,"suma");
EditarHistorialDespacho($idhistorialdespacho);


}
}
 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}

function anularsalidaProducto($cod_ext)
{
$mysqli=conectar_al_servidor();

	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);
$mysqli=conectar_al_servidor();
$consulta1="update historialsalidadeposito set estado='Inactivo',cod_anulado_por='$user',fecha_anulacion='$fecha_inser_edit' where cod_ext='$cod_ext'";

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}

function EditarHistorialDespacho($idhistorialdespacho)
{
	/*AUDITORIA*/
	date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time()); 
	 $user=$_POST['useru'];
    $user = utf8_decode($user);
$mysqli=conectar_al_servidor();
$consulta1="update historialdespacho set estado='Inactivo',cod_anulado_por='$user',fecha_anulacion='$fecha_inser_edit' where idhistorialdespacho='$idhistorialdespacho'";

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 mysqli_close($mysqli);

}


function historialdespachado($fecha1,$fecha2,$codlocal1,$codlocal2,$cod_producto,$producto)
{
$mysqli=conectar_al_servidor();
$condiciofecha="";
if($fecha1!="" && $fecha2!=""){
	$condiciofecha=" and htd.fecha>='$fecha1' and htd.fecha>='$fecha2'";
}
$condicionlocal1="";
if($codlocal1!=""){
	$condicionlocal1=" and htd.cod_local_a='$codlocal1'";
}
$condicionlocal2="";
if($codlocal2!=""){
	$condicionlocal2=" and htd.cod_local_a='$codlocal2'";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%'";
}
$condicioncodproducto="";
if($cod_producto!=""){
	$condicioncodproducto=" and pr.cod_barra = '".$cod_producto."'";
}

$sql= "select htd.idhistorialdespacho,htd.stock,htd.fecha, htd.cod_local_de, htd.cod_local_a, htd.cod_producto_fk, htd.cod_usuario_fk,
pr.nombre_producto,pr.cod_barra,
(select Nombre from local where cod_local=htd.cod_local_de limit 1 ) as localde,
(select Nombre from local where cod_local=htd.cod_local_a limit 1 ) as locala,
(Select nombre_persona from persona where cod_persona=htd.cod_usuario_fk) as usuarionombre
from  producto pr inner join historialdespacho htd on htd.cod_producto_fk=pr.cod_producto
where pr.estado='Activo' and htd.estado='Activo' ".$condiciofecha.$condicionlocal1.$condicionlocal2.$condicionproducto.$condicioncodproducto." limit 50";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$costototales=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$idhistorialdespacho = utf8_encode($valor['idhistorialdespacho']);
$stock = utf8_encode($valor['stock']);          
$fecha = utf8_encode($valor['fecha']);          
$cod_local_de = utf8_encode($valor['cod_local_de']); 
$cod_local_a = utf8_encode($valor['cod_local_a']); 
$cod_producto_fk = utf8_encode($valor['cod_producto_fk']); 
$cod_usuario_fk = utf8_encode($valor['cod_usuario_fk']); 
$nombre_producto = utf8_encode($valor['nombre_producto']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
$localde = utf8_encode($valor['localde']); 
$locala = utf8_encode($valor['locala']); 
$usuarionombre = utf8_encode($valor['usuarionombre']); 
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td  style='width:10%'>".$fecha."</td>
<td  style='width:10%'>".$localde."</td>
<td  style='width:10%'>".$locala."</td>
<td  style='width:10%'>".$cod_barra."</td>
<td  style='width:10%'>".$nombre_producto."</td>
<td  style='width:10%;'>".number_format($stock,'2',',','.')."</td>
<td  style='width:10%'>".$usuarionombre."</td>
</tr>
</tr>
</table>";


}
}
$sql= "select htd.idhistorialdespacho,htd.stock,htd.fecha, htd.cod_local_de, htd.cod_local_a, htd.cod_producto_fk, htd.cod_usuario_fk,
pr.nombre_producto,pr.cod_barra,
(select Nombre from local where cod_local=htd.cod_local_de limit 1 ) as localde,
(select Nombre from local where cod_local=htd.cod_local_a limit 1 ) as locala,
(Select nombre_persona from persona where cod_persona=htd.cod_usuario_fk) as usuarionombre
from  producto pr inner join historialdespacho htd on htd.cod_producto_fk=pr.cod_producto
where pr.estado='Activo' and htd.estado='Activo' ".$condiciofecha.$condicionlocal1.$condicionlocal2.$condicionproducto.$condicioncodproducto;
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistros=$valor;

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregistros);
echo json_encode($informacion);	
exit;
}

function historialmasdespachado($fecha1,$fecha2,$codlocal1,$codlocal2,$cod_producto,$producto,$registrocargado)
{
$mysqli=conectar_al_servidor();
$condiciofecha="";
if($fecha1!="" && $fecha2!=""){
	$condiciofecha=" and htd.fecha>='$fecha1' and htd.fecha>='$fecha2'";
}
$condicionlocal1="";
if($codlocal1!=""){
	$condicionlocal1=" and htd.cod_local_a='$codlocal1'";
}
$condicionlocal2="";
if($codlocal2!=""){
	$condicionlocal2=" and htd.cod_local_a='$codlocal2'";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%'";
}
$condicioncodproducto="";
if($cod_producto!=""){
	$condicioncodproducto=" and pr.cod_barra = '".$cod_producto."'";
}

$sql= "select htd.idhistorialdespacho,htd.stock,htd.fecha, htd.cod_local_de, htd.cod_local_a, htd.cod_producto_fk, htd.cod_usuario_fk,
pr.nombre_producto,pr.cod_barra,
(select Nombre from local where cod_local=htd.cod_local_de limit 1 ) as localde,
(select Nombre from local where cod_local=htd.cod_local_a limit 1 ) as locala,
(Select nombre_persona from persona where cod_persona=htd.cod_usuario_fk) as usuarionombre
from  producto pr inner join historialdespacho htd on htd.cod_producto_fk=pr.cod_producto
where pr.estado='Activo'  and htd.estado='Activo' ".$condiciofecha.$condicionlocal1.$condicionlocal2.$condicionproducto.$condicioncodproducto." limit ".$registrocargado.", 50 ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$costototales=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$idhistorialdespacho = utf8_encode($valor['idhistorialdespacho']);
$stock = utf8_encode($valor['stock']);          
$fecha = utf8_encode($valor['fecha']);          
$cod_local_de = utf8_encode($valor['cod_local_de']); 
$cod_local_a = utf8_encode($valor['cod_local_a']); 
$cod_producto_fk = utf8_encode($valor['cod_producto_fk']); 
$cod_usuario_fk = utf8_encode($valor['cod_usuario_fk']); 
$nombre_producto = utf8_encode($valor['nombre_producto']); 
$cod_barra = utf8_encode($valor['cod_barra']); 
$localde = utf8_encode($valor['localde']); 
$locala = utf8_encode($valor['locala']); 
$usuarionombre = utf8_encode($valor['usuarionombre']); 
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  style='width:10%'>".$fecha."</td>
<td  style='width:10%'>".$localde."</td>
<td  style='width:10%'>".$locala."</td>
<td  style='width:10%'>".$cod_barra."</td>
<td  style='width:10%'>".$nombre_producto."</td>
<td  style='width:10%;'>".number_format($stock,'2',',','.')."</td>
<td  style='width:10%'>".$usuarionombre."</td>
</tr>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function buscarCatalogo($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionlocal="";
if($local!=""){
	$condicionlocal=" and stk.cod_localFK='$local'";
}
$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,(pr.precio_producto-pr.precio_compra) as ganancia,
pr.precio_producto,pr.precio_compra,pr.stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  concat(pr.nombre_producto,' ',pr.cod_producto,' ',pr.descripcion_producto) like '%".$buscar."%' and pr.estado='Activo' ".$condicionlocal." limit 100";


$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$costototales=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$ganancia = utf8_encode($valor['ganancia']); 

$paginaprecios=buscardetallespreciosb($cod_producto);
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  style='width:20%'>".$nombre_producto."</td>
<td  style='width:20%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  style='width:20%;'>".$paginaprecios."</td>
<td  style='width:20%;'>".$local."</td>
</tr>
</tr>
</table>";


}
}

$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,(pr.precio_producto-pr.precio_compra) as ganancia,
pr.precio_producto,pr.precio_compra,pr.stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  concat(pr.nombre_producto,' ',pr.cod_producto,' ',pr.descripcion_producto) like '%".$buscar."%' and pr.estado='Activo' ".$condicionlocal;
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"99"=>$nroRegistro,"100"=>$totalregistro);
echo json_encode($informacion);	
exit;
}

function buscarMasCatalogo($buscar,$local,$registrocargado)
{
$mysqli=conectar_al_servidor();
$condicionlocal="";
if($local!=""){
	$condicionlocal=" and stk.cod_localFK='$local'";
}
$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,(pr.precio_producto-pr.precio_compra) as ganancia,
pr.precio_producto,pr.precio_compra,pr.stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  concat(pr.nombre_producto,' ',pr.cod_producto,' ',pr.descripcion_producto) like '%".$buscar."%' and pr.estado='Activo' ".$condicionlocal." limit ".$registrocargado.", 100 ";


$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$costototales=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$ganancia = utf8_encode($valor['ganancia']); 

$paginaprecios=buscardetallespreciosb($cod_producto);
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' >
<td  style='width:20%'>".$nombre_producto."</td>
<td  style='width:20%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  style='width:20%;'>".$paginaprecios."</td>
<td  style='width:20%;'>".$local."</td>
</tr>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarvista($buscar,$local,$Categoria,$Marca,$codProveedor)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and pr.cod_localFK='$local' ";
}
if($Categoria!=""){
	$condicionCategria=" and pr.cod_categoriaFK='$Categoria' ";
}
if($Marca!=""){
	$condicionMarca=" and pr.cod_marcasFK='$Marca' ";
}
$orderby="";
if($codProveedor!=""){
	$orderby=" order by codProveedor='$codProveedor' desc ";
}


	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,pr.stock_producto,pr.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= pr.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr
where concat(pr.nombre_producto,' ',pr.cod_barra,' ',pr.descripcion_producto) like ? 
and pr.estado='Activo' ".$condicionLocal.$condicionCategria.$condicionMarca.$orderby." limit 250";/*Sentencia para buscar registros*/
	


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



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($codProveedorFK==$codProveedor && $codProveedorFK!=""){
$styleProveedor="background-color: #efeded;color:#000";	
}
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproducto(this)' name='trVistaProducto_".$cod_barra."' style='$styleProveedor' >
<td id='td_datos_13' style='width:10%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='width:20%'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='display:none'>".number_format($stock_producto,'2',',','.')."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
</tr>
</table>";





}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarporcodigo($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionlocal="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}
		$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,cod_barra,pr.porcentaje,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= pr.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.cod_barra = ? and pr.estado='Activo' ".$condicionLocal." limit 1 ";


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



$cod_producto = utf8_encode($valor['cod_producto']);
$cod_barra = utf8_encode($valor['cod_barra']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$porcentaje = utf8_encode($valor['porcentaje']); 
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
if($cod_producto=="13603"){
	$stock_producto = "1";
}
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproducto(this)' name='trVistaProducto_".$cod_barra."' >
<td id='td_datos_13' style='width:10%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='width:20%'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='display:none'>".number_format($stock_producto,'2',',','.')."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
<td  id='td_datos_14' style='display:none'>".$porcentaje."</td>
<td  id='td_datos_15' style='display:none'>".$stock_producto."</td>
</tr>
</table>";

}
}
    
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}







function buscarcodBarra($producto,$codigo,$local)
{
$mysqli=conectar_al_servidor();
$condicionlocal="";
if($local!=""){
	$condicionlocal= " and pr.cod_localFK='$local' ";
}
$condicioncodigo="";
if($codigo!=""){
	$condicioncodigo= " and pr.cod_barra='$codigo' ";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto= " and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$producto."%'";
}
		
		
		
		$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,cod_barra,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='Activo' ".$condicionlocal.$condicioncodigo.$condicionproducto;


$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  



$cod_producto = utf8_encode($valor['cod_producto']);/*Obtenemos el registro mediante el nombre del atributo */      
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$cod_barra = utf8_encode($valor['cod_barra']); 

$paginaprecios=buscardetallespreciosimprimir($cod_producto, $precio_producto,$comision);

	 
$styleName=CargarStyleTable($styleName);
	  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tr_Codigo_barras' >
<td id='td_datos_6' style='width:10%'><input id='btnCheck' type='checkbox'   /></td>
<td id='td_datos_1' style='width:15%'>".$cod_barra."</td>
<td id='td_datos_2'  style='width:35%'>".$nombre_producto."</td>
<td  id='td_datos_3' style='width:20%'>".number_format($precio_producto,'0',',','.')."</td>
<td id='td_datos_5' style='width:10%'><input id='inptCantidad' type='text' value='' class='input5' /></td>
<td id='' style='width:10%'>".$local."</td>
<td id='td_datos_7' style='display:none'>".$paginaprecios."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarconsultarprecios($buscar,$loca,$categoria,$marcal)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
if($loca!=""){
$condicionLocal=" and pr.cod_localFK='$loca' ";
}
$condicionCategria="";
if($categoria!=""){
$condicionCategria=" and pr.cod_categoriaFK='$categoria' ";
}
$condicionMarca="";
if($marcal!=""){
$condicionMarca="and pr.cod_marcasFK='$marcal' ";
}

		$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,
pr.precio_producto,pr.precio_compra,pr.stock_producto,pr.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= pr.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr
where concat(pr.nombre_producto,' ',pr.cod_barra,' ',pr.descripcion_producto) like ? and pr.estado='Activo' 
".$condicionLocal.$condicionCategria.$condicionMarca."
limit 500";/*Sentencia para buscar registros*/


$pagina = "";   
$buscar="%".$buscar."%";
$stmt = $mysqli->prepare($sql);/*Se prepara la sentencia sql con el objeto prepare*/
$s='s';/*Variable que indica la cantidad paramentros a cargar en la sentencia, guiarse por la cantidad de ? que se encuentra en la sentencia*/
$stmt->bind_param($s,$buscar);/*Se cargar los paramentros a la sentencia preparada*/
/*Función para ejecutar sentencias sql*/
if ( ! $stmt->execute()) {
/*Si la sentencia prepara retorna un false entra esta funcion y capturamos el error y lo devolvemos con un echo*/
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);/*Utilizado para cargar variables tipo resultset que nos permite recorrer las fila o filas obtenida mendiante el nombre del atributo*/
$nroRegistro=$valor;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))/*bucle para recorrer la fila o filas obtenidas*/
{  




$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 

$styleName=CargarStyleTable($styleName);

	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosConsultarPrecioProducto(this)' name='trVistaProducto_".$cod_barra."' >
<td id='td_datos_13' style='width:15%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:35%'>".$nombre_producto."</td>
<td  id='td_datos_14' style='display:none'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%;'>".number_format($stock_producto,'2',',','.')."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='width:15%;'>".$local."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscardetallesprecios($buscar,$preciocontado,$comisioncontado)
{
$mysqli=conectar_al_servidor();

$sql= "select (select porcentaje from producto p where p.cod_producto=dt.cod_producto) as porcentajeContado , precio,Porcentaje as porcen,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio dt
where cod_producto=? ";
 $pagina="";  
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$buscar);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$Porcentaje = 26;  
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$porcentajeContado=0;
$Porcentaje=0;
$preciocontado=0;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$porcentajeContado = utf8_encode($valor['porcentajeContado']);  
$Porcentaje = utf8_encode($valor['porcen']);  
$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


	  $pagina.="<option id='$Cuota' style='$porcentajeContado' class='$Porcentaje' url='$preciocontado' name='$comision' value='".number_format($precio,'0',',','.')."'>".$descripcion."</option>";



}
}
$pagina.="<option name='$comisioncontado' style='$porcentajeContado' class='$Porcentaje' url='$preciocontado'  value='".number_format($preciocontado,'0',',','.')."'  style='display:none' id='contado' >Contado</option>";  
return $pagina;
}


function  buscardetallespreciosb($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,Porcentaje,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio 
where cod_producto=? ";
 $pagina="";  
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$Porcentaje = utf8_encode($valor['Porcentaje']); 
$precio = utf8_encode($valor['precio']);
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


	  $pagina.="Cuota Nro: ".$Cuota." =<b>".number_format($precio,'0',',','.')."Gs</b><br>";



}
}

return $pagina;
}


function  buscarvistaventaSolicitud($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}


$CondicionBuscador1="";
$CondicionBuscador2="";
$CondicionBuscadorTotal1="";
$CondicionBuscadorTotal2="";
$CondicionBuscadorTotalResyltado="";


if($buscar!=""){
$Buscador = explode ( ' ', $buscar );
$total = count($Buscador);
$contador=0;

while(($contador < $total)){
	if($Buscador[$contador]!=""){
	$CondicionBuscador1=" and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$Buscador[$contador]."%' ";	
	$CondicionBuscadorTotal1.=$CondicionBuscador1;
	
	$CondicionBuscador2="";
	$CondicionBuscadorTotal2.=$CondicionBuscador2;
}
	$contador++;
}
$CondicionBuscadorTotalResyltado=$CondicionBuscadorTotal1.$CondicionBuscadorTotal2;

}else{
	$CondicionBuscadorTotalResyltado=" and concat(pr.nombre_producto,' ',descripcion_producto) like '%%'";	
}


	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  pr.estado='Activo' ".$condicionLocal.$CondicionBuscadorTotalResyltado." limit 50";
	


$pagina = "";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($cod_producto=="13603"){
	$stock_producto = "1";
}
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
$paginapreciosb=buscardetallespreciossolicitud($cod_producto);
if($paginapreciosb==""){
$paginapreciosb="Sin Credito";	
}
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdeSolicitudCredito(this)' name='trVistaProducto_".$cod_barra."' style='$styleProveedor' >
<td id='td_datos_13' style='display:none'>".$cod_barra."</td>
<td  style='width:15%; background-color: #efeded;color:red'>".$cod_barra."
<br><input style='outline:none;height: 0px;padding: 0px;' type='button' class='$nroRegistro' value='$control' name='$cod_barra' id='btnfocusProducto' onfocus='recorrerFocusTableProductoVenta(this)' ></td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:40%'>".$nombre_producto."</td>
<td  id='' style='width:20%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_precio_contado' style='display:none;'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_precios_creditos' style='display:none;    line-height: 18px;    font-size: 9px;'>".$paginapreciosb."</td>
<td  id='td_datos_4' style='width:15%'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".$stock_producto."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
<td  id='td_datos_15' style='display:none'>".$stock_producto."</td>
</tr>
</table>";
	 
$control=$control+1;




}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarvistaVenta($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}


$CondicionBuscador1="";
$CondicionBuscador2="";
$CondicionBuscadorTotal1="";
$CondicionBuscadorTotal2="";
$CondicionBuscadorTotalResyltado="";


if($buscar!=""){
$Buscador = explode ( ' ', $buscar );
$total = count($Buscador);
$contador=0;

while(($contador < $total)){
	if($Buscador[$contador]!=""){
	$CondicionBuscador1=" and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$Buscador[$contador]."%' ";	
	$CondicionBuscadorTotal1.=$CondicionBuscador1;
	
	$CondicionBuscador2="";
	$CondicionBuscadorTotal2.=$CondicionBuscador2;
}
	$contador++;
}
$CondicionBuscadorTotalResyltado=$CondicionBuscadorTotal1.$CondicionBuscadorTotal2;

}else{
	$CondicionBuscadorTotalResyltado=" and concat(pr.nombre_producto,' ',descripcion_producto) like '%%'";	
}


	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  pr.estado='Activo' ".$condicionLocal.$CondicionBuscadorTotalResyltado." limit 50";
	


$pagina = "";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($cod_producto=="13603"){
	$stock_producto = "1";
}
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
$paginapreciosb=buscardetallespreciosb($cod_producto);
if($paginapreciosb==""){
$paginapreciosb="Sin Credito";	
}
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdeventa(this)' name='trVistaProducto_".$cod_barra."' style='$styleProveedor' >
<td id='td_datos_13' style='display:none'>".$cod_barra."</td>
<td  style='width:12%; background-color: #efeded;color:red'>".$cod_barra."
<br><input style='outline:none;height: 0px;padding: 0px;' type='button' class='$nroRegistro' value='$control' name='$cod_barra' id='btnfocusProducto' onfocus='recorrerFocusTableProductoVenta(this)' ></td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_precio_contado' style='width:10%'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_precios_creditos' style='width:10%;display:none;    line-height: 18px;    font-size: 9px;'>".$paginapreciosb."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".$stock_producto."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
<td  id='td_datos_15' style='display:none'>".$stock_producto."</td>
</tr>
</table>";
	 
$control=$control+1;




}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarvistalistadodespacho($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}



	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where concat(pr.nombre_producto,' ',pr.cod_barra,' ',pr.descripcion_producto) like ? 
and pr.estado='Activo' ".$condicionLocal." limit 50";
	


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
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($cod_producto=="13603"){
	$stock_producto = "1";
}

$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdelistadodespacho(this)'>
<td id='td_datos_13'  style='width:12%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_6' style='width:10%'>".$stock_producto."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_15' style='display:none'>".$stock_producto."</td>
</tr>
</table>";
	 
$control=$control+1;




}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarvistasalidadeposito($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}



	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where concat(pr.nombre_producto,' ',pr.cod_barra,' ',pr.descripcion_producto) like ? 
and pr.estado='Activo' ".$condicionLocal." limit 50";
	


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
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($cod_producto=="13603"){
	$stock_producto = "1";
}

$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdeSalidadDeposito(this)'>
<td id='td_datos_13'  style='width:12%; background-color: #efeded;color:red'>".$cod_barra."</td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_6' style='width:10%'>".$stock_producto."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_15' style='display:none'>".$stock_producto."</td>
</tr>
</table>";
	 
$control=$control+1;




}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function  buscarvistacompras($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}



	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,pr.porcentaje,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where concat(pr.nombre_producto,' ',pr.cod_barra,' ',pr.descripcion_producto) like ? 
and pr.estado='Activo' ".$condicionLocal." limit 50";
	


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
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$porcentaje = utf8_encode($valor['porcentaje']); 
$styleProveedor="";

$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdecompra(this)' name='trVistaProducto_".$cod_barra."' style='$styleProveedor' >
<td id='td_datos_13' style='display:none'>".$cod_barra."</td>
<td  style='width:12%; background-color: #efeded;color:red'>".$cod_barra."
<br><input style='outline:none;height: 0px;padding: 0px;' type='button' class='$nroRegistro' value='$control' name='$cod_barra' id='btnfocusProductocompra' onfocus='recorrerFocusTableProductoCompra(this)' ></td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='width:10%'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($stock_producto,'2',',','.')." (".$unidad_producto.")</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_10' style='display:none'>".$local."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
<td  id='td_datos_14' style='display:none'>".$porcentaje."</td>
</tr>
</table>";
$control=$control+1;




}
}
    
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarpresupuesto($buscar,$local)
{
$mysqli=conectar_al_servidor();
$condicionLocal="";
$condicionCategria="";
$condicionMarca="";
if($local!=""){
	$condicionLocal=" and stk.cod_localFK='$local' ";
}


$CondicionBuscador1="";
$CondicionBuscador2="";
$CondicionBuscadorTotal1="";
$CondicionBuscadorTotal2="";
$CondicionBuscadorTotalResyltado="";


if($buscar!=""){
$Buscador = explode ( ' ', $buscar );
$total = count($Buscador);
$contador=0;

while(($contador < $total)){
	if($Buscador[$contador]!=""){
	$CondicionBuscador1=" and concat(pr.nombre_producto,' ',pr.descripcion_producto) like '%".$Buscador[$contador]."%' ";	
	$CondicionBuscadorTotal1.=$CondicionBuscador1;
	
	$CondicionBuscador2="";
	$CondicionBuscadorTotal2.=$CondicionBuscador2;
}
	$contador++;
}
$CondicionBuscadorTotalResyltado=$CondicionBuscadorTotal1.$CondicionBuscadorTotal2;

}else{
	$CondicionBuscadorTotalResyltado=" and concat(pr.nombre_producto,' ',descripcion_producto) like '%%'";	
}


	$sql= "select pr.cod_producto,pr.nombre_producto,pr.descripcion_producto,pr.unidad_producto,pr.cod_barra,pr.codProveedor,
pr.precio_producto,pr.precio_compra,stk.cantidad as stock_producto,stk.cod_localFK,pr.comision,pr.estado,
(select Nombre from local where cod_local= stk.cod_localFK limit 1 ) as local,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where  pr.estado='Activo' ".$condicionLocal.$CondicionBuscadorTotalResyltado." limit 50";
	


$pagina = "";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precio_producto = utf8_encode($valor['precio_producto']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$stock_producto = utf8_encode($valor['stock_producto']); 
$cod_localFK = utf8_encode($valor['cod_localFK']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$local = utf8_encode($valor['local']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$styleProveedor="";
if($cod_producto=="13603"){
	$stock_producto = "1";
}
$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);
$paginapreciosb=buscardetallespreciosb($cod_producto);
if($paginapreciosb==""){
$paginapreciosb="Sin Credito";	
}
$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdePresupuesto(this)' name='trVistaProducto_".$cod_barra."' style='$styleProveedor' >
<td id='td_datos_13' style='display:none'>".$cod_barra."</td>
<td  style='width:12%; background-color: #efeded;color:red'>".$cod_barra."
<br><input style='outline:none;height: 0px;padding: 0px;' type='button' class='$nroRegistro' value='$control' name='$cod_barra' id='btnfocusProducto' onfocus='recorrerFocusTableProductoVenta(this)' ></td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:20%'>".$nombre_producto."</td>
<td  id='' style='width:10%'>".$NombreMarca."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_precio_contado' style='width:10%'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_precios_creditos' style='width:10%;display:none;    line-height: 18px;    font-size: 9px;'>".$paginapreciosb."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precio_producto,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".$stock_producto."</td>
<td  id='td_datos_11' style='display:none'>".$paginaprecios."</td>
</tr>
</table>";
	 
$control=$control+1;




}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function abmAuditoria($nombre_descripcion,$precio_compra,$precio_venta,$stock,$cod_barra,$nombredescripcionAnt,$precio_compraAnt,$precio_ventaAnt,$stockAnt,$cod_barraAnt,$fecha,$cod_usuarioFK,$Accion,$cod_productoFK,$cod_localFK)
{

	
	if($nombre_descripcion=="" && $precio_compra=="0" && $precio_venta=="0" && $stock=="0" && $cod_barra==""){
		
	}else{	
$mysqli=conectar_al_servidor(); 


$consulta1="Insert into auditoriaproducto (nombre_descripcion, precio_compra, precio_venta, stock, cod_barra, nombredescripcionAnt, precio_compraAnt, precio_ventaAnt, stockAnt, cod_barraAnt, fecha, cod_usuarioFK,accion,cod_productoFK,cod_localfk)
values('$nombre_descripcion',$precio_compra,$precio_venta,$stock,'$cod_barra','$nombredescripcionAnt',$precio_compraAnt,$precio_ventaAnt,$stockAnt,'$cod_barraAnt','$fecha',$cod_usuarioFK,'$Accion','$cod_productoFK',$cod_localFK)";
$stmt1 = $mysqli->prepare($consulta1);


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}
	}
	
}


function AuditoriaProducto($fecha1,$fecha2,$local,$usuario,$producto){
	

$mysqli=conectar_al_servidor();

$condicionFecha="";
if($fecha1!="" || $fecha2!=""){
	$condicionFecha=" and  fecha between '$fecha1' and  '$fecha2' ";
}

$condicionlocal="";
if($local!=""){
	$condicionlocal=" and cod_localfk='$local' ";
}

$condicionusuario="";
if($usuario!=""){
	$condicionusuario=" and (select nombre_persona from persona where cod_persona=cod_usuarioFK) like '%$usuario%' ";
}

$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and concat((select nombre_producto from producto where cod_producto=cod_productoFK),' ',(select cod_barra from producto where cod_producto=cod_productoFK)) like '%$producto%' ";
}



	$sql= "select idauditoriaProducto, nombre_descripcion, precio_compra, precio_venta, stock, cod_barra, nombredescripcionAnt, 
	precio_compraAnt, precio_ventaAnt, stockAnt, cod_barraAnt, fecha, cod_usuarioFK, accion, cod_productoFK, cod_localfk,
(select Nombre from local where cod_local= cod_localfk limit 1 ) as local ,
(select nombre_persona from persona where cod_persona=cod_usuarioFK) as usuario,
(select nombre_producto from producto where cod_producto=cod_productoFK) as nombre_producto,
(select cod_barra from producto where cod_producto=cod_productoFK) as cod_barrap
from  auditoriaproducto 
where idauditoriaProducto!='' ".$condicionFecha.$condicionlocal.$condicionusuario.$condicionproducto."";
	
	
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
$control=0;
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$nombre_descripcion = utf8_encode($valor['nombre_producto']);
$precio_compra = utf8_encode($valor['precio_compra']);
$precio_venta = utf8_encode($valor['precio_venta']);          
$stock = utf8_encode($valor['stock']);          
$cod_barra = utf8_encode($valor['cod_barrap']); 
$nombredescripcionAnt = utf8_encode($valor['nombredescripcionAnt']); 
$precio_compraAnt = utf8_encode($valor['precio_compraAnt']); 
$precio_ventaAnt = utf8_encode($valor['precio_ventaAnt']); 
$stockAnt = utf8_encode($valor['stockAnt']); 
$cod_barraAnt = utf8_encode($valor['cod_barraAnt']); 
$fecha = utf8_encode($valor['fecha']); 
$local = utf8_encode($valor['local']); 
$accion = utf8_encode($valor['accion']); 
$usuario = utf8_encode($valor['usuario']); 

if($precio_compra!="0"){
	$precio_compra=number_format($precio_compra,'0',',','.');
}else{$precio_compra="";}

if($precio_venta!="0"){
	$precio_venta=number_format($precio_venta,'0',',','.');
}else{$precio_venta="";}

if($stock!="0"){
	$stock=number_format($stock,'0',',','.');
}else{$stock="";}

if($precio_compraAnt!="0"){
	$precio_compraAnt=number_format($precio_compraAnt,'0',',','.');
}else{$precio_compraAnt="";}

if($precio_ventaAnt!="0"){
	$precio_ventaAnt=number_format($precio_ventaAnt,'0',',','.');
}else{$precio_ventaAnt="";}

if($stockAnt!="0"){
	$stockAnt=number_format($stockAnt,'0',',','.');
}else{$stockAnt="";}



$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td  id='td_datos_13' style='width:8%'>".$accion."</td>
<td  id='td_id' style='width:6%'>".$fecha."</td>
<td  id='td_datos_1' style='width:8%'>".$usuario."</td>
<td  id='' style='width:5%'>".$local."</td>
<td  id='td_datos_2' style='width:7%'>".$cod_barra."</td>
<td  id='td_datos_12' style='width:12%'>".$nombre_descripcion."</td>
<td  id='td_datos_6' style='width:7%'>".$cod_barraAnt."</td>
<td  id='td_datos_7' style='width:12%'>".$nombredescripcionAnt."</td>
<td  id='td_datos_3' style='width:6%'>".$precio_compra."</td>
<td  id='td_datos_8' style='width:6%'>".$precio_compraAnt."</td>
<td  id='td_datos_4' style='width:6%'>".$precio_venta."</td>
<td  id='td_datos_9' style='width:6%'>".$precio_ventaAnt."</td>
<td  id='td_datos_5' style='width:5%'>".$stock."</td>
<td  id='td_datos_10' style='width:5%'>".$stockAnt."</td>
</tr>
</table>";
$control=$control+1;




}
}
    
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}




function  buscardetallespreciossolicitud($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,Porcentaje,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio 
where cod_producto=? ";
 $pagina="";  
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

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$Porcentaje = utf8_encode($valor['Porcentaje']); 
$precio = utf8_encode($valor['precio']);
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


	  $pagina.=" ".$Cuota." *<b>".number_format($precio,'0',',','.')."Gs</b><br>";



}
}

return $pagina;
}


function comprobarduplicado($cod_barra){
	
	$mysqli=conectar_al_servidor(); 
	$consulta= "Select count(*) as contador from producto where cod_barra='$cod_barra'  ";
	
	// echo($consulta);
	// exit;
	
	$stmt = $mysqli->prepare($consulta);


if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$control = 0;
$contador=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
if($valor>=1)
{
	while ($valor= mysqli_fetch_assoc($result))
{  

$contador = utf8_encode($valor['contador']);
}
	$control = $contador;
}

 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $control);
echo json_encode($informacion);	
exit;
}





function ContabilidadVenta($fecha1,$fecha2,$Local){
	
	$mysqli=conectar_al_servidor(); 
	
	$condicionLocal="";
	if($Local!=""){
		$condicionLocal=" and v.cod_local = '$Local' ";
	}
	
	$condicionFecha="";
if($fecha1!="" && $fecha2!="" ){
	$condicionFecha=" and  v.fecha_venta between '".$fecha1."' and '".$fecha2."'";
}

	
	$consulta=  " SELECT (select timbrado from nrofactura where Cod_Nro=codnrofactura) as timbrado, v.TipoVenta , 
                (select ire from nrofactura where Cod_Nro=codnrofactura) as ire,
                (select irp from nrofactura where Cod_Nro=codnrofactura) as irp,
                (select iva from nrofactura where Cod_Nro=codnrofactura) as iva,
				(SELECT ci_cliente FROM cliente  WHERE v.cod_clienteFK = cod_cliente ) AS CI ,
                v.cod_venta ,v.estado , date_format(v.fecha_venta,'%d/%m/%Y') as fecha,  v.total_venta,v.tipo_comprobante, 
                 substring_index((SELECT p.rut_cliente FROM cliente p WHERE v.cod_clienteFK = p.cod_cliente ),'-',1) AS RUC1 
                ,if((SELECT p.rut_cliente FROM cliente p WHERE v.cod_clienteFK = p.cod_cliente ) like '%-%',
                substring_index((SELECT p.rut_cliente FROM cliente p WHERE v.cod_clienteFK = p.cod_cliente ),'-',-1),0) AS RUC2 

                ,round((select (sum(if((select (select monto_impuesto from impuesto where cod_Impuesto=cod_ImpuestoFK) FROM producto  WHERE cod_producto = cod_productoFK)=11,((d.subtotal)),0))) from detalle_venta d WHERE v.cod_venta = d.cod_ventaFK)) as totalIva10
                ,round((select (sum(if((select (select monto_impuesto from impuesto where cod_Impuesto=cod_ImpuestoFK) FROM producto  WHERE cod_producto = cod_productoFK)=21,((d.subtotal)),0))) from detalle_venta d WHERE v.cod_venta = d.cod_ventaFK)) as totalIva5
                ,round((select (sum(if((select (select monto_impuesto from impuesto where cod_Impuesto=cod_ImpuestoFK) FROM producto  WHERE cod_producto = cod_productoFK)=0,((d.subtotal)),0))) from detalle_venta d WHERE v.cod_venta = d.cod_ventaFK)) as Excentas 
                ,(SELECT p.nombre_persona FROM persona p WHERE v.cod_clienteFK = p.cod_persona ) AS nombreCliente
                ,Cast( sum(d.cantidad_detalle) as Decimal(10,2)) as cantidad  ,v.num_factura , v.puntoexpedicion
                 FROM  detalle_venta d   INNER JOIN venta v  ON v.cod_venta = d.cod_ventaFK WHERE  v.cod_venta!='' ".$condicionFecha.$condicionLocal." and tipo_comprobante='FACTURA'   group by v.cod_venta ";

    	$stmt = $mysqli->prepare($consulta);
		
		

 $pagina="";  
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

$RUC1 = utf8_encode($valor['RUC1']); 
$CI = utf8_encode($valor['CI']); 
$nombreCliente = utf8_encode($valor['nombreCliente']);
$fecha = utf8_encode($valor['fecha']);          
$timbrado = utf8_encode($valor['timbrado']);          
$num_factura = utf8_encode($valor['num_factura']);          
$totalIva10 = utf8_encode($valor['totalIva10']);  
$totalIva5 = utf8_encode($valor['totalIva5']);  
$Excentas = utf8_encode($valor['Excentas']);  
$total_venta = utf8_encode($valor['total_venta']);  
$TipoVenta = utf8_encode($valor['TipoVenta']);  
$puntoexpedicion = utf8_encode($valor['puntoexpedicion']);  

$iva = utf8_encode($valor['iva']);  
$ire = utf8_encode($valor['ire']);  
$irp = utf8_encode($valor['irp']);  
 if ($iva=="SI") {
		$iva = "S";
     } else {
        $iva = "N";
     }

if ($ire=="SI") {
		$ire = "S";
	} else {
		$ire = "N";
	}
if ($irp=="SI") {
		$irp = "S";
	} else {
		$irp = "N";
	}
	
$codigoTIpoID="";
if ($RUC1=="") {
		$codigoTIpoID = "12";
	} else {
		$codigoTIpoID ="11";
	}

if ($RUC1=="") {
		$RUC1 = $CI;
	}	
	

if ($TipoVenta=="CONTADO") {
		$TipoVenta = "1";
	} else {
		$TipoVenta = "2";
	}
if ($nombreCliente=="CLIENTE OCASIONAL") {
		$nombreCliente = "SIN NOMBRE";
		$RUC1 = "x";
		$codigoTIpoID ="15";
	}

$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td   style='width:4%'>1</td>
<td   style='width:5%'>".$codigoTIpoID."</td>
<td   style='width:5%'>".quitarseparadormiles($RUC1)."</td>
<td   style='width:20%'>".$nombreCliente."</td>
<td   style='width:5%'>109</td>
<td   style='width:5%'>".$fecha."</td>
<td   style='width:5%'>".$timbrado."</td>
<td   style='width:10%'>".$puntoexpedicion."-".$num_factura."</td>
<td   style='width:5%'>".$totalIva10."</td>
<td   style='width:5%'>".$totalIva5."</td>
<td   style='width:5%'>".$Excentas."</td>
<td   style='width:5%'>".$total_venta."</td>
<td   style='width:5%'>".$TipoVenta."</td>
<td   style='width:3%'>N</td>
<td   style='width:3%'>".$iva."</td>
<td   style='width:3%'>".$ire."</td>
<td   style='width:3%'>".$irp."</td>
<td   style='width:2%'></td>
<td   style='width:2%'></td>
</tr>
</table>";


}
}
    
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}




function ContabilidadCompra($fecha1,$fecha2,$Local){
	
	$mysqli=conectar_al_servidor(); 
	
	$condicionLocal="";
	if($Local!=""){
		$condicionLocal=" and v.cod_local = '$Local' ";
	}
	
	$condicionFecha="";
if($fecha1!="" && $fecha2!="" ){
	$condicionFecha=" and  v.fecha_compra between '".$fecha1."' and '".$fecha2."'";
}

	
	$consulta=  " SELECT  timbrado, tipo_compra, v.tipoFactura ,v.cod_compra ,v.estado , date_format(v.fecha_compra,'%d/%m/%Y') as fecha,  
    substring_index((SELECT p.rut_proveedor FROM proveedor p WHERE v.cod_proveedorFK = p.cod_proveedor ),'-',1) AS RUC1
     ,(select sum(precio_producto * cantidad_detalle_compra) from detalle_compra where v.cod_compra = cod_compraFK ) as total_compra
    ,round((select (sum(if((select (select monto_impuesto from impuesto where cod_Impuesto=cod_ImpuestoFK) FROM producto  WHERE cod_producto = cod_productoFK)=11,((d.subtotal)),0))) from detalle_compra d WHERE v.cod_compra = d.cod_compraFK)) as totalIva10
	,round((select (sum(if((select (select monto_impuesto from impuesto where cod_Impuesto=cod_ImpuestoFK) FROM producto  WHERE cod_producto = cod_productoFK)=21,((d.subtotal)),0))) from detalle_compra d WHERE v.cod_compra = d.cod_compraFK)) as totalIva5
	,round((select (sum(if((select (select monto_impuesto from impuesto where cod_Impuesto=cod_ImpuestoFK) FROM producto  WHERE cod_producto = cod_productoFK)=0,((d.subtotal)),0))) from detalle_compra d WHERE v.cod_compra = d.cod_compraFK)) as Excentas 
                ,(SELECT p.nombre_persona FROM persona p WHERE v.cod_proveedorFK = p.cod_persona ) AS nombreProveedor
                ,Cast( sum(d.cantidad_detalle_compra) as Decimal(10,2)) as cantidad  ,v.num_comprobante , v.tipo_comprobante
                 FROM  detalle_compra d   INNER JOIN compra v  ON v.cod_compra = d.cod_compraFK WHERE  v.cod_compra!='' ".$condicionFecha.$condicionLocal."   group by v.cod_compra ";

    	$stmt = $mysqli->prepare($consulta);
		
		

 $pagina="";  
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

$RUC1 = utf8_encode($valor['RUC1']); 
// $CI = utf8_encode($valor['CI']); 
$nombreProveedor = utf8_encode($valor['nombreProveedor']);
$fecha = utf8_encode($valor['fecha']);          
$timbrado = utf8_encode($valor['timbrado']);          
$tipoFactura = utf8_encode($valor['tipoFactura']);          
$totalIva10 = utf8_encode($valor['totalIva10']);  
$totalIva5 = utf8_encode($valor['totalIva5']);  
$Excentas = utf8_encode($valor['Excentas']);  
$total_compra = utf8_encode($valor['total_compra']);  
$tipo_compra = utf8_encode($valor['tipo_compra']);  
$num_comprobante = utf8_encode($valor['num_comprobante']);  

$iva ="S";  
$ire ="N"; 
$irp ="N"; 

	
$codigoTIpoID="";
if ($RUC1=="") {
		$codigoTIpoID = "12";
	} else {
		$codigoTIpoID ="11";
	}


if ($tipo_compra=="CONTADO") {
		$tipo_compra = "1";
	} else {
		$tipo_compra = "2";
	}


if ($tipoFactura=="FACTURA LEGAL") {
		$tipoFactura = "109";
	} else {
		$tipoFactura = "";
	}

$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td   style='width:4%'>1</td>
<td   style='width:5%'>".$codigoTIpoID."</td>
<td   style='width:5%'>".quitarseparadormiles($RUC1)."</td>
<td   style='width:20%'>".$nombreProveedor."</td>
<td   style='width:5%'>".$tipoFactura."</td>
<td   style='width:5%'>".$fecha."</td>
<td   style='width:5%'>".$timbrado."</td>
<td   style='width:10%'>".$num_comprobante."</td>
<td   style='width:5%'>".$totalIva10."</td>
<td   style='width:5%'>".$totalIva5."</td>
<td   style='width:5%'>".$Excentas."</td>
<td   style='width:5%'>".$total_compra."</td>
<td   style='width:5%'>".$tipo_compra."</td>
<td   style='width:3%'>N</td>
<td   style='width:3%'>".$iva."</td>
<td   style='width:3%'>".$ire."</td>
<td   style='width:3%'>".$irp."</td>
<td   style='width:3%'>N</td>
<td   style='width:2%'></td>
<td   style='width:2%'></td>
</tr>
</table>";


}
}
    
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}



function  buscardetallespreciosimprimir($buscar,$preciocontado,$comisioncontado)
{
$mysqli=conectar_al_servidor();

$sql= "select (select porcentaje from producto p where p.cod_producto=dt.cod_producto) as porcentajeContado , preciocuota,Porcentaje as porcen,descripcion,cod_producto,iddetallesprecio,comision,Cuota
 from  detallesprecio dt
where cod_producto=? ";
 $pagina=" <b class='pTitulo2' style='font-size: 13px;padding: 5px;' > <u>EN CUOTAS:</u>  </b>";  
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$buscar);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$Porcentaje = 26;  
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$porcentajeContado=0;
$Porcentaje=0;
$preciocontado=0;

 $styleName="tableRegistroSearch";
 
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$porcentajeContado = utf8_encode($valor['porcentajeContado']);  
$Porcentaje = utf8_encode($valor['porcen']);  
$preciocuota = utf8_encode($valor['preciocuota']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' style='height: 20px; font-size: 11px;padding: 0px;' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' >
<td   style='width:100%;padding: 0px;'>&nbsp;".$Cuota."&nbsp; X&nbsp; <b>&nbsp;&nbsp;<u> Gs.&nbsp;&nbsp;&nbsp; ".number_format($preciocuota,'0',',','.')." </u></b></td>
</tr>
</table>";
// $pagina="aa";
}
}
 
return $pagina;
}





function buscaroption($local)
{
	

		$sql= "Select *  from producto pr inner join stocklocales sl on cod_productofk=cod_producto 
		where estado='Activo' and sl.cod_localfk='$local' order by nombre_producto asc ";

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
		  
		  
		      $cod_producto=$valor['cod_producto'];
		  	  $nombre_producto=utf8_encode($valor['nombre_producto']);
		  	  $descripcion_producto=utf8_encode($valor['descripcion_producto']);
			  $cod_barra=utf8_encode($valor['cod_barra']);
		  	 
		  	 
			    	
			  $pagina.="<option id='$cod_barra' value='$cod_producto' name='0' >".$nombre_producto." ".$descripcion_producto."</option>";   
			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}






ObtenerDatos($operacion);

?>
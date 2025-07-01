<?php
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
include("buscar_nivel.php");
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


abm($CodProveedorFK,$tipoproducto,$cod_producto,$codBarras,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$porcentaje,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$cod_localFK,$comision,$stock_producto,$estado,$operacion);

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
	
 	EnviarProductoA($stock,$cod_local_a,$cod_local_de,$fecha,$cod_producto_fk,$user);
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
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
 	BuscarRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local);
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
	$registrocargado=$_POST["registrocargado"];
 	$registrocargado=utf8_decode($registrocargado);
	if($local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$local=buscarlocaluser($user);
	}
}
 	BuscarMasRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local,$registrocargado);
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
 
   if($operacion=="buscarvistaventa"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistaVenta($buscar,$local);
 }
 if($operacion=="buscarvistacompras"){
 	$buscar=$_POST["buscar"];
 	$buscar=utf8_decode($buscar);
	$local=$_POST["local"];
 	$local=utf8_decode($local);
	buscarvistacompras($buscar,$local);
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


}

function abm($CodProveedorFK,$tipo,$cod_producto,$cod_barra,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$porcentaje,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$cod_localFK,$comision,$stock_producto,$estado,$operacion)
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
	$informacion =array("1" => "EX");
	echo json_encode($informacion);	
	exit;
}  


	
$cod_producto=buscarCodigoProductos();

$consulta1="Insert into producto (CodProveedor,cod_barra,cod_producto,porcentaje,cod_categoriaFK,cod_marcasFK,cod_ImpuestoFK,nombre_producto,descripcion_producto,unidad_producto,precio_producto,precio_compra,comision,estado,tipo,cod_user_insert,fecha_insert)
values(?,?,?,?,?,?,?,upper(?),?,?,?,?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssssssssss';
$stmt1->bind_param($ss,$CodProveedorFK,$cod_barra,$cod_producto,$porcentaje,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$comision,$estado,$tipo,$user,$fecha_inser_edit);


}


if($operacion=="editar")
{
$consulta1="Update producto set CodProveedor=?,tipo=?,cod_barra=?,nombre_producto=upper(?),porcentaje=?,cod_categoriaFK=?,cod_marcasFK=?,cod_ImpuestoFK=?,descripcion_producto=?,unidad_producto=?,precio_producto=?,precio_compra=?,comision=?,estado=?,cod_user_edit=?,fecha_edit=? where cod_producto=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssssssssss';
$stmt1->bind_param($ss,$CodProveedorFK,$tipo,$cod_barra,$nombre_producto,$porcentaje,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$comision,$estado,$user,$fecha_inser_edit,$cod_producto); 
}

if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

if($operacion=="nuevo"){
	buscarlocalesproductos($stock_producto,$cod_producto,$cod_localFK);
}

if($operacion=="editar"){
	EditarStockA($stock_producto,$cod_producto,$cod_localFK);
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

function BuscarRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local)
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
where pr.estado='".$estado."'  ".$condicionMarca.$condicionCategria.$condicionLocal.$condicionCodigo.$condicionProducto.$condicionstock.$condicionproveedor." order by pr.nombre_producto asc limit 100 "; 	
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
$insertadopor = utf8_encode($valor['insertadopor']); 
$editadopor = utf8_encode($valor['editadopor']); 
$fecha_insert = utf8_encode($valor['fecha_insert']); 
$fecha_edit = utf8_encode($valor['fecha_edit']); 
//anhadirStockA($stock_producto,$cod_producto,$cod_localFK);
$totalcostos=$precio_compra*$stock_producto;

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

$sql= "select pr.tipo
 from  producto pr inner join stocklocales stk on stk.cod_productofk=pr.cod_producto
where pr.estado='".$estado."'  ".$condicionMarca.$condicionCategria.$condicionLocal.$condicionCodigo.$condicionProducto.$condicionstock.$condicionproveedor." order by pr.nombre_producto asc  "; 	
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

function BuscarMasRegistro($codigo,$producto,$marca,$categoria,$stock,$proveedor,$estado,$local,$registrocargado)
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
where pr.estado='".$estado."'  ".$condicionMarca.$condicionCategria.$condicionLocal.$condicionCodigo.$condicionProducto.$condicionstock.$condicionproveedor." order by pr.nombre_producto asc limit ".$registrocargado.", 100 "; 	
$stmt = $mysqli->prepare($sql);
$pagina = "";   
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;

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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

function EnviarProductoA($stock,$cod_local_a,$cod_local_de,$fecha,$cod_producto_fk,$cod_usuario_fk)
{
	
$mysqli=conectar_al_servidor();
$consulta1="Insert into historialdespacho (stock,cod_local_a,cod_local_de,fecha,cod_producto_fk,cod_usuario_fk) values(?,?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$stock,$cod_local_a,$cod_local_de,$fecha,$cod_producto_fk,$cod_usuario_fk);
if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$consulta= "Select count(*) from stocklocales where cod_productofk='$cod_usuario_fk' and cod_localfk ='$cod_local_a' ";
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' style='$styleFondo'>
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' style='$styleFondo'>
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
where pr.estado='Activo' ".$condiciofecha.$condicionlocal1.$condicionlocal2.$condicionproducto.$condicioncodproducto." limit 50";
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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
where pr.estado='Activo' ".$condiciofecha.$condicionlocal1.$condicionlocal2.$condicionproducto.$condicioncodproducto;
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
where pr.estado='Activo' ".$condiciofecha.$condicionlocal1.$condicionlocal2.$condicionproducto.$condicioncodproducto." limit ".$registrocargado.", 50 ";
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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
	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

$paginaprecios=buscardetallesprecios($cod_producto, $precio_producto,$comision);

	 

	  	  $pagina.="
<table class='tableRegistroSearch'  border='0' cellspacing='0' cellpadding='0'>
<tr id='tr_Codigo_barras' >
<td id='td_datos_6' style='width:10%'><input id='btnCheck' type='checkbox'   /></td>
<td id='td_datos_1' style='width:15%'>".$cod_barra."</td>
<td id='td_datos_2' style='width:35%'>".$nombre_producto."</td>
<td  id='td_datos_3' style='width:20%'>".number_format($precio_producto,'0',',','.')."</td>
<td id='td_datos_5' style='width:10%'><input id='inptCantidad' type='text' value='' class='input5' /></td>
<td id='' style='width:10%'>".$local."</td>
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



	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
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

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Cuota
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



$precio = utf8_encode($valor['precio']);     
$descripcion = utf8_encode($valor['descripcion']);          
$iddetallesprecio = utf8_encode($valor['iddetallesprecio']);          
$comision = utf8_encode($valor['comision']);          
$Cuota = utf8_encode($valor['Cuota']);          


	  $pagina.="<option id='$Cuota' name='$comision' value='".number_format($precio,'0',',','.')."'>".$descripcion."</option>";



}
}
$pagina.="<option name='$comisioncontado' value='".number_format($preciocontado,'0',',','.')."'  style='display:none' id='contado' >Contado</option>";  
return $pagina;
}


function  buscardetallespreciosb($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select precio,descripcion,cod_producto,iddetallesprecio,comision,Cuota
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

function  buscarvistaVenta($buscar,$local)
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
	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
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

	  $pagina.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0' >
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


ObtenerDatos($operacion);

?>
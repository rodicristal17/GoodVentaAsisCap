<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
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
	
	
	$CodUsu=$_POST['CodUsu'];
	$CodUsu = mb_convert_encoding((string)($CodUsu), 'ISO-8859-1', 'UTF-8');
	$cod_ProductoFK=$_POST['cod_ProductoFK'];
	$cod_ProductoFK = mb_convert_encoding((string)($cod_ProductoFK), 'ISO-8859-1', 'UTF-8');
	$cantidad=$_POST['cantidad'];
	$cantidad = mb_convert_encoding((string)($cantidad), 'ISO-8859-1', 'UTF-8');
	$precio=$_POST['precio'];
	$precio = quitarseparadormiles($precio);
	
	
	
	
	abm($CodUsu,$cod_ProductoFK,$cantidad,$precio,$operacion);

}


if($operacion=="buscarDescuento")
{

	buscar();

}	

if($operacion=="buscarSoliDescuento")
{
	$fecha1=$_POST['fecha1'];
	$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST['fecha2'];
	$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$UsuSoli=$_POST['UsuSoli'];
	$UsuSoli = mb_convert_encoding((string)($UsuSoli), 'ISO-8859-1', 'UTF-8');
	$UsuApro=$_POST['UsuApro'];
	$UsuApro = mb_convert_encoding((string)($UsuApro), 'ISO-8859-1', 'UTF-8');
	$producto=$_POST['producto'];
	$producto = mb_convert_encoding((string)($producto), 'ISO-8859-1', 'UTF-8');
	buscarSoliDescuento($fecha1,$fecha2,$UsuSoli,$UsuApro,$producto);

}	

if($operacion=="buscarDescuentovista")
{
	
	$buscar=$_POST['buscar'];
	$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
	$UsuarioFK=$_POST['UsuarioFK'];
	$UsuarioFK = mb_convert_encoding((string)($UsuarioFK), 'ISO-8859-1', 'UTF-8');
	buscarvistaVenta($buscar,$UsuarioFK);

}	


if($operacion=="EditarAprobado")
{
	$idABM=$_POST['idABM'];
	$idABM = mb_convert_encoding((string)($idABM), 'ISO-8859-1', 'UTF-8');
	EditarAprobado($idABM);

}	


if($operacion=="Editar" )
{
	
	
	$CodUsu=$_POST['CodUsu'];
	$CodUsu = mb_convert_encoding((string)($CodUsu), 'ISO-8859-1', 'UTF-8');
	$cod_ProductoFK=$_POST['cod_ProductoFK'];
	$cod_ProductoFK = mb_convert_encoding((string)($cod_ProductoFK), 'ISO-8859-1', 'UTF-8');
	$cantidad=$_POST['cantidad'];
	$cantidad = mb_convert_encoding((string)($cantidad), 'ISO-8859-1', 'UTF-8');
	$precio=$_POST['precio'];
	$precio = quitarseparadormiles($precio);
	$estado=$_POST['estado'];
	$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$idABM=$_POST['idABM'];
	$idABM = mb_convert_encoding((string)($idABM), 'ISO-8859-1', 'UTF-8');	
	
	
	Editar($CodUsu,$cod_ProductoFK,$cantidad,$precio,$estado,$idABM,$operacion);

}


}

function Editar($CodUsu,$cod_ProductoFK,$cantidad,$precio,$estado,$idABM,$operacion)
{
	
	
if($cantidad==""  || $precio==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor();

$fechahoy=date('Y-m-d');

$consulta1="update solicituddescuendo set estado='$estado',  cod_UsuAprobado='$CodUsu', cantidad='$cantidad', precioDescuento='$precio' where idsolicituddescuendo= $idABM";
$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}


function EditarAprobado($idABM)
{
	

$mysqli=conectar_al_servidor();

$fechahoy=date('Y-m-d');

$consulta1="update solicituddescuendo set estado='Finalizado' where idsolicituddescuendo= $idABM";
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}



function abm($CodUsu,$cod_ProductoFK,$cantidad,$precio,$operacion)
{
	
	
if($cantidad==""  || $precio==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor();

if($operacion=="nuevo")
{
$fechahoy=date('Y-m-d');

$consulta1="Insert into solicituddescuendo (estado, fecha, cod_UsuAprobado, cod_productoFK, cod_usuarioFK, cantidad, precioDescuento)
values('Pendiente','$fechahoy',0,'$cod_ProductoFK','$CodUsu','$cantidad','$precio')";
$stmt1 = $mysqli->prepare($consulta1);

}
// echo($consulta1);
// exit;



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
	
}



function buscar()
{
	
	
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $pag2="";
	
		$sql= "SELECT  cod_productoFK ,(select nombre_producto from producto where cod_producto= cod_productoFK ) as producto
		,(select cod_barra from producto where cod_producto= cod_productoFK ) as codBarra
		,(select nombre_persona from persona where cod_persona= cod_usuarioFK ) as usuario
 , idsolicituddescuendo, estado, fecha, cod_UsuAprobado, cantidad, precioDescuento, cod_usuarioFK FROM solicituddescuendo where  estado='Pendiente' ";
		
   // echo($sql);
   // exit;
   
   $stmt = $mysqli->prepare($sql);
   
   $Style="background: none 0px 0px repeat scroll #2196f3;
   border: 2px solid #ffffff;
   border-radius: 6px;
   cursor: pointer;
   margin-top:2px;
   ";
  	

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 $estadoSoli="";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
			  $codBarra=$valor['codBarra'];
		      $cod_productoFK=$valor['cod_productoFK'];
			  $producto=$valor['producto'];
		  	  $idsolicituddescuendo=mb_convert_encoding((string)($valor['idsolicituddescuendo']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
			  $cod_UsuAprobado=mb_convert_encoding((string)($valor['cod_UsuAprobado']), 'UTF-8', 'ISO-8859-1');
			  $cantidad=mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1');
			  $precioDescuento=mb_convert_encoding((string)($valor['precioDescuento']), 'UTF-8', 'ISO-8859-1');
			  $cod_usuarioFK=mb_convert_encoding((string)($valor['cod_usuarioFK']), 'UTF-8', 'ISO-8859-1');
			  $usuario=mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');
			  
			  if($estado=="Aprobado"){
				  $estadoSoli="SI";
			  }else{
				  $estadoSoli="NO";
			  }
		  	 
			 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='obtenerdatosMensajeDetalle(this)'>
				<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$cod_productoFK."</td>
				<td  id='td_datos_1' style='width:25%'>".$producto."</td>
				<td  id='td_datos_2' style='width:10%'>".$cantidad."</td>
				<td  id='td_datos_3' style='width:25%'>".$precioDescuento."</td>
				<td  id='td_datos_4' style='width:10%'>".$fecha."</td>
				<td  id='td_datos_5' style='width:20%'>".$usuario."</td>
				</tr>
				</table>";
				
				$pag2.="<div id='DivMensaje_$idsolicituddescuendo' style='$Style'>
				<table style='width:100%;' >
				<tr id='tbSelecRegistro' onclick='obtenerdatosMensajeDetalle(this)'>
				<td style='width:95%;'>
				<p class='pTituloB' style='font-size: 12px;  color: #ffffff;'>Hay Solicitud de Descuento Pendiente==>  <b style='font-size: 18px;' >'".$producto."'</b>  </p>
				</td>				
				<td  id='td_datos_1' style='display:none'>".$producto."</td>
				<td  id='td_datos_2' style='display:none'>".$cantidad."</td>
				<td  id='td_datos_3' style='display:none'>".number_format($precioDescuento,'0',',','.')."</td>
				<td  id='td_datos_4' style='display:none'>".$fecha."</td>
				<td  id='td_datos_5' style='display:none'>".$usuario."</td>
				<td  id='td_datos_6' style='display:none'>".$idsolicituddescuendo."</td>
				<td  id='td_datos_7' style='display:none'>".$estado."</td>
				<td  id='td_datos_8' style='display:none'>".$codBarra."</td>
				<td  id='td_datos_9' style='display:none'>".$cod_productoFK."</td>
				<td style='width:5%'>
				<img src='/GoodVentaAsisCap/iconos/botonCerrar.png' class='iconoBtn' title='Cerrar Ventana' onclick='verCerrarMensajeDescuentoDetalle($idsolicituddescuendo)' />
				</td>
								
				</tr>
				</table>
				</div>";
			  
			  
	  }
 }
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro ,"4" => $pag2 ,"5" => $estadoSoli);
echo json_encode($informacion);	
exit;


}



function buscarSoliDescuento($fecha1,$fecha2,$UsuSoli,$UsuApro,$producto)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 
	 $condicionFecha="";
if($fecha1!="" && $fecha2!=""){
	$condicionFecha=" and fecha between '$fecha1' and '$fecha2' ";
}
$condicionUsuSoli="";
if($UsuSoli!=""){
	$condicionUsuSoli=" and usuariosoli like '%$UsuSoli%' ";
}
$condicionUsuApro="";
if($UsuApro!=""){
	$condicionUsuApro=" and usuarioapro like '%$UsuApro%' ";
}

$condicionproducto="";
if($producto!=""){
	$condicionproducto=" producto like '%".$producto."%'";
}



	
		$sql= "SELECT  cod_productoFK ,(select nombre_producto from producto where cod_producto= cod_productoFK ) as producto
		,(select cod_barra from producto where cod_producto= cod_productoFK ) as codBarra
		,(select nombre_persona from persona where cod_persona= cod_usuarioFK ) as usuariosoli
		,(select nombre_persona from persona where cod_persona= cod_UsuAprobado ) as usuarioapro
 , idsolicituddescuendo, estado, fecha, cod_UsuAprobado, cantidad, precioDescuento, cod_usuarioFK FROM solicituddescuendo where 
 estado!='' ".$condicionFecha.$condicionUsuSoli.$condicionUsuApro.$condicionproducto." ";
		
   // echo($sql);
   // exit;
   
   $stmt = $mysqli->prepare($sql);
   

  	

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 $estadoSoli="";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
			  $codBarra=$valor['codBarra'];
		      $cod_productoFK=$valor['cod_productoFK'];
			  $producto=$valor['producto'];
		  	  $idsolicituddescuendo=mb_convert_encoding((string)($valor['idsolicituddescuendo']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
			  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
			  $cod_UsuAprobado=mb_convert_encoding((string)($valor['cod_UsuAprobado']), 'UTF-8', 'ISO-8859-1');
			  $cantidad=mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1');
			  $precioDescuento=mb_convert_encoding((string)($valor['precioDescuento']), 'UTF-8', 'ISO-8859-1');
			  $cod_usuarioFK=mb_convert_encoding((string)($valor['cod_usuarioFK']), 'UTF-8', 'ISO-8859-1');
			  $usuariosoli=mb_convert_encoding((string)($valor['usuariosoli']), 'UTF-8', 'ISO-8859-1');
			  $usuarioapro=mb_convert_encoding((string)($valor['usuarioapro']), 'UTF-8', 'ISO-8859-1');
			
		  	 
			 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
				<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
				<tr id='tbSelecRegistro' onclick='obtenerdatosSolicitudDescuento(this)'>
				<td id='td_id' style='width:10%; background-color: #efeded;color:red'>".$codBarra."</td>
				<td  id='td_datos_1' style='width:30%'>".$producto."</td>
				<td  id='td_datos_2' style='width:10%'>".$cantidad."</td>
				<td  id='td_datos_3' style='width:10%'>".number_format($precioDescuento,'0',',','.')."</td>
				<td  id='td_datos_4' style='width:12%'>".$usuariosoli."</td>
				<td  id='td_datos_5' style='width:8%'>".$fecha."</td>
				<td  id='td_datos_6' style='width:8%'>".$estado."</td>
				<td  id='td_datos_7' style='width:12%'>".$usuarioapro."</td>				
				<td  id='td_datos_8' style='display:none'>".$idsolicituddescuendo."</td>
				</tr>
				</table>";
				
		
			  
			  
	  }
 }
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}




function  buscarvistaVenta($buscar,$cod_usuarioFK)
{
$mysqli=conectar_al_servidor();



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
pr.precio_producto,pr.precio_compra,pr.comision,pr.estado,sd.cantidad,sd.precioDescuento,sd.estado as est,sd.fecha,sd.idsolicituddescuendo,
(select nombre_persona from persona where cod_persona= sd.cod_UsuAprobado limit 1 ) as aprobadoPor ,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from impuesto where cod_Impuesto= pr.cod_ImpuestoFK limit 1 ) as NombreImpuesto,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join solicituddescuendo sd on sd.cod_productoFK=pr.cod_producto
where  pr.estado='Activo' and sd.estado='Aprobado' and  cod_usuarioFK=".$cod_usuarioFK."  ".$CondicionBuscadorTotalResyltado." limit 50";
	
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
$idsolicituddescuendo = mb_convert_encoding((string)($valor['idsolicituddescuendo']), 'UTF-8', 'ISO-8859-1'); 
$est = mb_convert_encoding((string)($valor['est']), 'UTF-8', 'ISO-8859-1'); 
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1'); 
$aprobadoPor = mb_convert_encoding((string)($valor['aprobadoPor']), 'UTF-8', 'ISO-8859-1'); 
$cod_barra = mb_convert_encoding((string)($valor['cod_barra']), 'UTF-8', 'ISO-8859-1');
$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$descripcion_producto = mb_convert_encoding((string)($valor['descripcion_producto']), 'UTF-8', 'ISO-8859-1');          
$unidad_producto = mb_convert_encoding((string)($valor['unidad_producto']), 'UTF-8', 'ISO-8859-1'); 
$precioDescuento = mb_convert_encoding((string)($valor['precioDescuento']), 'UTF-8', 'ISO-8859-1'); 
$precio_compra = mb_convert_encoding((string)($valor['precio_compra']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$NombreCategoria = mb_convert_encoding((string)($valor['NombreCategoria']), 'UTF-8', 'ISO-8859-1'); 
$NombreImpuesto = mb_convert_encoding((string)($valor['NombreImpuesto']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 
$codProveedorFK = mb_convert_encoding((string)($valor['codProveedor']), 'UTF-8', 'ISO-8859-1'); 
$cantidad = mb_convert_encoding((string)($valor['cantidad']), 'UTF-8', 'ISO-8859-1'); 

$styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductodesdeventaDescuento(this)' name='trVistaProductoDescuento_".$cod_barra."'  >
<td id='td_datos_13' style='display:none'>".$cod_barra."</td>
<td  style='width:7%; background-color: #efeded;color:red'>".$cod_barra." </td>
<td id='td_id' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_1' style='width:28%'>".$nombre_producto."*".$NombreMarca."</td>
<td  id='td_datos_10' style='width:5%'>".$cantidad."</td>
<td  id='td_datos_2' style='display:none'>".$descripcion_producto."</td>
<td  id='td_datos_12' style='display:none'>".$NombreCategoria."</td>
<td  id='td_datos_3' style='display:none'>".$unidad_producto."</td>
<td  id='td_datos_precio_contado' style='width:10%'>". number_format($precioDescuento,'0',',','.')."</td>
<td   style='width:20%'>".$aprobadoPor."</td>
<td  id='td_datos_11' style='width:15%'>".$fecha."</td>
<td   style='width:15%'>".$est."</td>
<td  id='td_datos_4' style='display:none'>". number_format($precioDescuento,'0',',','.')."</td>
<td  id='td_datos_5' style='display:none'>".number_format($precio_compra,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$estado."</td>
<td  id='td_datos_20' style='display:none'>".$idsolicituddescuendo."</td>
</tr>
</table>";
	 





}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}




verificar($operacion);
?>
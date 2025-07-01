<?php

$operacion = $_POST['funt'];
$operacion = utf8_decode($operacion);

include("buscar_nivel.php");
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
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
	
	
	$CodUsu=$_POST['CodUsu'];
	$CodUsu = utf8_decode($CodUsu);
	$cod_ProductoFK=$_POST['cod_ProductoFK'];
	$cod_ProductoFK = utf8_decode($cod_ProductoFK);
	$cantidad=$_POST['cantidad'];
	$cantidad = utf8_decode($cantidad);
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
	$fecha1 = utf8_decode($fecha1);
	$fecha2=$_POST['fecha2'];
	$fecha2 = utf8_decode($fecha2);
	$UsuSoli=$_POST['UsuSoli'];
	$UsuSoli = utf8_decode($UsuSoli);
	$UsuApro=$_POST['UsuApro'];
	$UsuApro = utf8_decode($UsuApro);
	$producto=$_POST['producto'];
	$producto = utf8_decode($producto);
	buscarSoliDescuento($fecha1,$fecha2,$UsuSoli,$UsuApro,$producto);

}	

if($operacion=="buscarDescuentovista")
{
	
	$buscar=$_POST['buscar'];
	$buscar = utf8_decode($buscar);
	$UsuarioFK=$_POST['UsuarioFK'];
	$UsuarioFK = utf8_decode($UsuarioFK);
	buscarvistaVenta($buscar,$UsuarioFK);

}	


if($operacion=="EditarAprobado")
{
	$idABM=$_POST['idABM'];
	$idABM = utf8_decode($idABM);
	EditarAprobado($idABM);

}	


if($operacion=="Editar" )
{
	
	
	$CodUsu=$_POST['CodUsu'];
	$CodUsu = utf8_decode($CodUsu);
	$cod_ProductoFK=$_POST['cod_ProductoFK'];
	$cod_ProductoFK = utf8_decode($cod_ProductoFK);
	$cantidad=$_POST['cantidad'];
	$cantidad = utf8_decode($cantidad);
	$precio=$_POST['precio'];
	$precio = quitarseparadormiles($precio);
	$estado=$_POST['estado'];
	$estado = utf8_decode($estado);
	$idABM=$_POST['idABM'];
	$idABM = utf8_decode($idABM);	
	
	
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
		  	  $idsolicituddescuendo=utf8_encode($valor['idsolicituddescuendo']);
		  	  $estado=utf8_encode($valor['estado']);
			  $fecha=utf8_encode($valor['fecha']);
			  $cod_UsuAprobado=utf8_encode($valor['cod_UsuAprobado']);
			  $cantidad=utf8_encode($valor['cantidad']);
			  $precioDescuento=utf8_encode($valor['precioDescuento']);
			  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
			  $usuario=utf8_encode($valor['usuario']);
			  
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
		  	  $idsolicituddescuendo=utf8_encode($valor['idsolicituddescuendo']);
		  	  $estado=utf8_encode($valor['estado']);
			  $fecha=utf8_encode($valor['fecha']);
			  $cod_UsuAprobado=utf8_encode($valor['cod_UsuAprobado']);
			  $cantidad=utf8_encode($valor['cantidad']);
			  $precioDescuento=utf8_encode($valor['precioDescuento']);
			  $cod_usuarioFK=utf8_encode($valor['cod_usuarioFK']);
			  $usuariosoli=utf8_encode($valor['usuariosoli']);
			  $usuarioapro=utf8_encode($valor['usuarioapro']);
			
		  	 
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
$idsolicituddescuendo = utf8_encode($valor['idsolicituddescuendo']); 
$est = utf8_encode($valor['est']); 
$fecha = utf8_encode($valor['fecha']); 
$aprobadoPor = utf8_encode($valor['aprobadoPor']); 
$cod_barra = utf8_encode($valor['cod_barra']);
$cod_producto = utf8_encode($valor['cod_producto']);
$nombre_producto = utf8_encode($valor['nombre_producto']);          
$descripcion_producto = utf8_encode($valor['descripcion_producto']);          
$unidad_producto = utf8_encode($valor['unidad_producto']); 
$precioDescuento = utf8_encode($valor['precioDescuento']); 
$precio_compra = utf8_encode($valor['precio_compra']); 
$comision = utf8_encode($valor['comision']); 
$estado = utf8_encode($valor['estado']); 
$NombreCategoria = utf8_encode($valor['NombreCategoria']); 
$NombreImpuesto = utf8_encode($valor['NombreImpuesto']); 
$NombreMarca = utf8_encode($valor['NombreMarca']); 
$codProveedorFK = utf8_encode($valor['codProveedor']); 
$cantidad = utf8_encode($valor['cantidad']); 

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
<?php


function verificar()
{
	
// ObtenerCodVentaCredito();
// ObtenerCodVentaPago();
// ObtenerCodCreditoPago();
// ObtenerCodVentaDetalleVenta();

// Producto();
// DetalleVanta();
// PrecioProducto();

EditarDetallePrecio();
}

function conectar_al_servidor(){

$mysqli = new mysqli('localhost','root','','elimviejo');
$mysqli->set_charset("latin1");
return  $mysqli;

}


function conectar_al_servidor2(){

$mysqli = new mysqli('localhost','syscvxco_elimelectro','syscvxco_elimelectro','syscvxco_elimelectro');
$mysqli->set_charset("latin1");
return  $mysqli;

}


function EditarDetallePrecio()
{
	$mysqli=conectar_al_servidor2(); 		  
	
	
	$sql= "SELECT * FROM producto  ";  
 // echo $sql ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {

		  	   $cod_producto=($valor['cod_producto']);
			  
			
				BuscarRegistroDetallePrecio($cod_producto);

	  }
	  
 } 
  mysqli_close($mysqli); 
}



///////////////PRODUCTO//////////////




function  BuscarRegistroDetallePrecio($cod_producto)
{
$mysqli=conectar_al_servidor2();

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

$mysqli=conectar_al_servidor2(); 



$consulta1="update detallesprecio set  precio=$precio ,descripcion='$descripcion' ,cod_producto='$cod_producto' ,comision=$comision ,Porcentaje=$Porcentaje ,Cuota=$Cuota ,preciocuota=$preciocuota where iddetallesprecio=$iddetallesprecio ";
$stmt1 = $mysqli->prepare($consulta1);



if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}


}



function PrecioProducto()
{
	$mysqli=conectar_al_servidor2(); 		  
	
	
	$sql= "SELECT * FROM producto p where (select count(*) from detallesprecio dp where p.cod_producto = dp.cod_producto)=0 ";  
 // echo $sql ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  $concidion++;
		  	   $cod_producto=($valor['cod_producto']);
			   $precio_producto=($valor['precio_producto']);
			   $precio_compra=($valor['precio_compra']);
			   $porcentaje=($valor['porcentaje']);
			
			
		nuevoTablaDetallePrecio($cod_producto,$precio_producto,$porcentaje);

	  }
	  
 } 
  mysqli_close($mysqli); 
}




Function nuevoTablaDetallePrecio($cod_producto,$PrecioContado,$porcentaje)
{

$PorcenContado=$porcentaje;

	

	
	$porcentaje=$PorcenContado;
	$cuota=2;
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,2,$PrecioCuota,"nuevo");
	
	
	$porcentaje=$PorcenContado;
	$cuota=3;
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,$PorcenContado,3,$PrecioCuota,"nuevo");
	
	
	// $precioCuotas=($PrecioContado)/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado);
	// abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,25,3,$precioCuotas,"nuevo");
	
	
	
	
	$porcentaje=35;
	$cuota=4;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,35,4,$PrecioCuota,"nuevo");
	
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	$porcentaje=45;
	$cuota=5;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,45,5,$PrecioCuota,"nuevo");
	
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	$porcentaje=50;
	$cuota=6;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,50,6,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	
	
	$porcentaje=60;
	$cuota=8;
	
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,60,8,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=65;
	$cuota=10;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,65,10,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=75;
	$cuota=12;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,75,12,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=85;
	$cuota=15;
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,85,15,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	$porcentaje=95;
	$cuota=18;	
	
	$precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	$PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	$descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	$TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,95,18,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
	// $porcentaje=110;
	// $cuota=24;
	
	// $precioCompra= round(($PrecioContado * 100) / ($PorcenContado+ 100));	
	// $PrecioCuota=( $precioCompra +  round(($precioCompra * $porcentaje)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($PrecioCuota,'0',',','.');
	// $TotalPrecio=$precioCompra + round(($precioCompra * $porcentaje)/100);
	// abmTabla(0,$TotalPrecio,$descripcion,$cod_producto,0,110,24,$PrecioCuota,"nuevo");
	
	// $precioCuotas=($PrecioContado+round(($PrecioContado * $Resultado)/100))/$cuota;
	// $descripcion=$cuota." x ".number_format($precioCuotas,'0',',','.');
	// $TotalPrecio=($PrecioContado+round(($PrecioContado * $Resultado)/100));
	
// $informacion =array("1" => "exito");
// echo json_encode($informacion);	
// exit;
	
}

/*Funcion para insertar,modificar o eliminar registros*/
function abmTabla($iddetallesprecio,$precio,$descripcion,$cod_producto,$comision,$Porcentaje,$Cuota,$preciocuota,$operacion)
{

if( $cod_producto=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor2(); 



$consulta1="Insert into detallesprecio (precio,descripcion,cod_producto,comision,Porcentaje,Cuota,preciocuota)
values('$precio','$descripcion','$cod_producto','$comision','$Porcentaje','$Cuota','$preciocuota')";



$stmt1 = $mysqli->prepare($consulta1);


if (!$stmt1->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}




}






//////////////     Pago   /////////////////////

function ObtenerCodVentaCredito()
{
	$mysqli=conectar_al_servidor(); 		  
	
	$sql= " SELECT * FROM credito  where cod_venta=437 limit 5000"  ;
		 
    $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $idcredito=($valor['idcredito']);
			   $nroventa=($valor['nroventa']);
			   $cod_venta=ObtenerCod_ventaVenta($nroventa);
				EditarCreditoCodVenta($cod_venta,$idcredito);
			   echo $idcredito."<BR>";
	  }
	  
 } 
  mysqli_close($mysqli); 
}

function ObtenerCod_ventaVenta($nroventa)
{
	$mysqli=conectar_al_servidor(); 		  
	
	$sql= " SELECT cod_venta from venta where  concat(puntoexpedicion,'-',num_factura)='$nroventa'" ;
		
		// echo($sql);
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $cod_venta="0";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $cod_venta=($valor['cod_venta']);
			
	  }
	  
 } 
	mysqli_close($mysqli);
	
	return  $cod_venta;
}


function EditarCreditoCodVenta($cod_venta,$idcredito)
{
	$mysqli=conectar_al_servidor();	
    $consulta="update credito set cod_venta='$cod_venta' where  idcredito=$idcredito";	
     $stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}





//////////////     Pago   /////////////////////

function ObtenerCodVentaPago()
{
	$mysqli=conectar_al_servidor(); 		  
	
	
	$sql= " SELECT * FROM pago where cod_venta_fk=10911 and anulado='0' limit 5000" ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $idPago=($valor['idPago']);
			   $nroventa=($valor['nroventa']);
			$cod_venta=Obteneridcredito($nroventa);
			   EditarPago($cod_venta,$idPago);
			   echo $cod_venta."<BR>";
	  }
	  
 } 
  mysqli_close($mysqli); 
}

function Obteneridcredito($nroventa)
{
	$mysqli=conectar_al_servidor(); 		  
	
	$sql= " SELECT cod_venta from credito where  concat(nroventa,'-',plazo)='$nroventa'" ;
		
		// echo($sql)."<br>";
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $cod_venta="0";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $cod_venta=($valor['cod_venta']);
			
	  }
	  
 } 
	mysqli_close($mysqli);
	
	return  $cod_venta;
}


function EditarPago($cod_venta,$idPago)
{
	$mysqli=conectar_al_servidor();	
    $consulta="update pago set  cod_venta_fk='$cod_venta' where idPago=$idPago ";
	// echo ($consulta);
     $stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}









//////////////     Detalle venta     /////////////////////
function ObtenerCodVentaDetalleVenta()
{
	$mysqli=conectar_al_servidor2(); 		  
	
	
	$sql= " SELECT * FROM detalle_venta where cod_ventaFK=10911 limit 5000"  ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	    $cod_detalle=($valor['cod_detalle']);
			    $nroventa=($valor['nroventa']);
				$cod_venta=ObteneridVenta($nroventa);
			   EditarCodVenta($cod_venta,$cod_detalle);
			   // echo $cod_venta."<BR>";
	  }
	  
 } 
  mysqli_close($mysqli); 
}

function ObteneridVenta($nroventa)
{
	$mysqli=conectar_al_servidor2(); 		  
	
	$sql= " SELECT cod_venta from venta where concat(puntoexpedicion,'-',num_factura) ='$nroventa' " ;
		
		// echo($sql);
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $cod_venta="0";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $cod_venta=($valor['cod_venta']);
			
	  }
	  
 } 
	mysqli_close($mysqli);
	
	return  $cod_venta;
}

function EditarCodVenta($cod_venta,$cod_detalle)
{
	$mysqli=conectar_al_servidor2();	
    $consulta="update detalle_venta set  cod_ventaFK='$cod_venta' where cod_detalle='$cod_detalle' ";	
     $stmt = $mysqli->prepare($consulta);
	 echo($consulta)."<BR>";
	 // exit;

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}





//////////////     Pago COD CREDITO   /////////////////////

function ObtenerCodCreditoPago()
{
	$mysqli=conectar_al_servidor(); 		  
	
	
	$sql= " SELECT * FROM pago where cod_creditoFK=56715 and anulado='0' limit 5000" ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion="";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $idPago=($valor['idPago']);
			   $nroventa=($valor['nroventa']);
			$cod_creditoFK=ObtenerCodcredito($nroventa);
			   EditarPago2($cod_creditoFK,$idPago);
			   echo $cod_creditoFK."<BR>";
	  }
	  
 } 
  mysqli_close($mysqli); 
}

function ObtenerCodcredito($nroventa)
{
	$mysqli=conectar_al_servidor(); 		  
	
	$sql= " SELECT idcredito from credito where  concat(nroventa,'-',plazo)='$nroventa'" ;
		
		// echo($sql);
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $idcredito="0";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $idcredito=($valor['idcredito']);
			
	  }
	  
 } 
	mysqli_close($mysqli);
	
	return  $idcredito;
}


function EditarPago2($cod_creditoFK,$idPago)
{
	$mysqli=conectar_al_servidor();	
    $consulta="update pago set  cod_creditoFK='$cod_creditoFK' where idPago=$idPago ";	
	
	// echo($consulta),"<br>";
     $stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}



///////////////PRODUCTO//////////////

function Producto()
{
	$mysqli=conectar_al_servidor(); 		  
	
	
	$sql= "SELECT codart as cod_producto , vd.nomart as nombre_producto , '' as descripcion_producto , 'Unidad' as unidad_producto , 
venta1 as precio_producto, costogs as precio_compra , existe as stock_producto , '11' as iva , '' as imagen , '' as tamano ,
'1' as cod_localFK , '0' as comision , 'Activo' as estado ,'1' as cod_categoriaFK , 
(select codmar from marca n where n.nommar=d.nommar limit 1) as cod_marcasFK ,'3' as  cod_ImpuestoFK , '25' as porcentaje , 
 vd.codepart as cod_barra , 'VENTA DIRECTA' as tipo  FROM  ventadet vd inner join depart d on  d.codepart= vd.codepart group by vd.nomart asc";  
 // echo $sql ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  $concidion++;
		  	   $cod_producto=($valor['cod_producto']);
			   $nombre_producto=($valor['nombre_producto']);
			   $descripcion_producto=($valor['descripcion_producto']);
			   $unidad_producto=($valor['unidad_producto']);
			   $precio_producto=($valor['precio_producto']);
			   $precio_compra=($valor['precio_compra']);
			   $stock_producto=($valor['stock_producto']);
			   $iva=($valor['iva']);
			   $imagen=($valor['imagen']);
			   $tamaño=($valor['tamano']);
			   $cod_localFK=($valor['cod_localFK']);
			   $comision=($valor['comision']);
			   $estado=($valor['estado']);
			   $cod_categoriaFK=($valor['cod_categoriaFK']);
			   $cod_marcasFK=($valor['cod_marcasFK']);
			   $cod_ImpuestoFK=($valor['cod_ImpuestoFK']);
			   $porcentaje=($valor['porcentaje']);
			   $cod_barra=($valor['cod_barra']);
			   $tipo=($valor['tipo']);
		InsertarProducto($concidion,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$stock_producto,$iva,$imagen,$tamaño,$cod_localFK,$comision,$estado,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$porcentaje,$cod_barra,$tipo);

	  }
	  
 } 
  mysqli_close($mysqli); 
}




function InsertarProducto($cod_producto,$nombre_producto,$descripcion_producto,$unidad_producto,$precio_producto,$precio_compra,$stock_producto,$iva,$imagen,$tamaño,$cod_localFK,$comision,$estado,$cod_categoriaFK,$cod_marcasFK,$cod_ImpuestoFK,$porcentaje,$cod_barra,$tipo)
{
	$mysqli=conectar_al_servidor2();	
    $consulta="INSERT INTO producto (cod_producto,nombre_producto,descripcion_producto,unidad_producto,precio_producto,precio_compra,stock_producto,iva,imagen,cod_localFK,comision,estado,cod_categoriaFK,cod_marcasFK,cod_ImpuestoFK,porcentaje,cod_barra,tipo) VALUES ('$cod_producto','$nombre_producto','$descripcion_producto','$unidad_producto','$precio_producto','$precio_compra','$stock_producto','$iva','$imagen','$cod_localFK','$comision','$estado','$cod_categoriaFK','$cod_marcasFK','$cod_ImpuestoFK','$porcentaje','$cod_barra','$tipo')";	
	
	echo($consulta),"<br>";
     $stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}








///////////////Detalle Venta//////////////

function DetalleVanta()
{
	$mysqli=conectar_al_servidor(); 		  
	
	
	$sql= "SELECT nomart,cantid as cantidad_detalle , codepart as cod_productoFK , precio as precio_producto , 10911 as cod_ventaFK , 
 (precio * cantid) as subtotal , (costo * cantid) as subPrecioCompra , 'Activo' as estado , 0 as comision , nomart as detalleproducto,
 0 as descuento , succajfac as nroventa FROM elimviejo.ventadet";  
 // echo $sql ;
		
		 
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $concidion=0;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  $concidion++;
		  	   $nomart=($valor['nomart']);
			   $cantidad_detalle=($valor['cantidad_detalle']);
			   $cod_productoFK=($valor['cod_productoFK']);
			   $precio_producto=($valor['precio_producto']);
			   $cod_ventaFK=($valor['cod_ventaFK']);
			   $subtotal=($valor['subtotal']);
			   $subPrecioCompra=($valor['subPrecioCompra']);
			   $estado=($valor['estado']);
			   $comision=($valor['comision']);
			   $detalleproducto=($valor['detalleproducto']);
			   $descuento=($valor['descuento']);
			   $nroventa=($valor['nroventa']);
			   
			   $Cod=ObtenerCodProducto($nomart);
			   
		InsertarDetalleVanta($cantidad_detalle,$Cod,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$estado,$comision,$detalleproducto,$descuento,$nroventa);

	  }
	  
 } 
  mysqli_close($mysqli); 
}





function ObtenerCodProducto($nombre)
{
	$mysqli=conectar_al_servidor2(); 		  
	
	$sql= " SELECT cod_producto from producto where  nombre_producto='$nombre'" ;
		
		// echo($sql);
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {  
   echo "Error";
   exit;
}

	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $cod_producto="0";
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  	   $cod_producto=($valor['cod_producto']);
			
	  }
	  
 } 
	mysqli_close($mysqli);
	
	return  $cod_producto;
}





function InsertarDetalleVanta($cantidad_detalle,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$estado,$comision,$detalleproducto,$descuento,$nroventa)
{
	$mysqli=conectar_al_servidor2();	
    $consulta="INSERT INTO detalle_venta(cantidad_detalle,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado,comision,detalleproducto,descuento,nroventa) VALUES ('$cantidad_detalle','$cod_productoFK','$precio_producto','$cod_ventaFK','$subtotal','$subPrecioCompra','$estado','$comision','$detalleproducto','$descuento','$nroventa')";
	
	echo($consulta),"<br>";
     $stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute() ) {
	$informacion =array("1" => $mysqli->error);
	echo json_encode($informacion);	
	exit;
}

 mysqli_close($mysqli); 

}







verificar();
?>
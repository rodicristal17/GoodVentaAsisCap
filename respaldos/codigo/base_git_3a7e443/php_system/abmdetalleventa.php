<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
include("buscar_nivel.php");
include("BuscarNroFactura.php");
include("calcularintereses.php");
// include("calcularInteresDirecto.php");
include("classTable.php");
require_once("interconsulta_seguimiento_paciente_helper.php");
require_once("trabajo_laboratorio_helper.php");
require_once("cliente_venta_validacion_helper.php");

function validarCantidadUnitariaTratamientoVenta($mysqli,$codProducto,$cantidad,$responder = true)
{
	if (!function_exists("trabajoLaboratorioObtenerConfiguracionProducto")) {
		return true;
	}
	$configuracion = trabajoLaboratorioObtenerConfiguracionProducto($mysqli,$codProducto);
	$requiereLaboratorio = !empty($configuracion["ok"])
		&& !empty($configuracion["requiere_laboratorio"]);
	$esTratamientoIndividualizable = !empty($configuracion["ok"])
		&& $configuracion["modo_individualizacion"] !== "cantidad_libre";
	if (empty($configuracion["ok"]) || (!$requiereLaboratorio && !$esTratamientoIndividualizable)) {
		return true;
	}
	$cantidadNormalizada = function_exists("quitarseparadormiles")
		? quitarseparadormiles(trim((string)$cantidad))
		: str_replace(",", ".", trim((string)$cantidad));
	$cantidadNumerica = is_numeric($cantidadNormalizada) ? (float)$cantidadNormalizada : 0.0;
	if ($cantidadNumerica === 1.0) {
		return true;
	}
	if ($responder) {
		echo json_encode(array(
			"1" => "error",
			"codigo" => "cantidad_tratamiento_unitaria",
			"mensaje" => "Los tratamientos clinicos deben venderse con cantidad 1 por fila. Cree filas independientes para trabajos distintos o conserve una sola fila y seleccione varias piezas cuando sea un unico tratamiento."
		));
		exit;
	}
	return false;
}

function claveDetalleHistoricoTratamiento($codProducto,$cantidad,$desdeBaseDatos = false)
{
	$cantidadNormalizada = $desdeBaseDatos
		? trim((string)$cantidad)
		: (function_exists("quitarseparadormiles")
		? quitarseparadormiles(trim((string)$cantidad))
		: str_replace(",", ".", trim((string)$cantidad)));
	$cantidadNumerica = is_numeric($cantidadNormalizada) ? (float)$cantidadNormalizada : 0.0;
	return trim((string)$codProducto)."|".number_format($cantidadNumerica,6,".","");
}

/**
 * Valida todo el lote antes de que la operacion legacy elimine o inserte
 * detalles. En una edicion se reconocen las filas historicas sin cambios para
 * no bloquear ventas antiguas que ya fueron guardadas con cantidad mayor a 1.
 */
function validarDetallesUnitariosVenta($claveTotal,$codVentaHistorica = 0,$preservarHistoricos = false)
{
	$totalRegistro = isset($_POST[$claveTotal]) ? (int)$_POST[$claveTotal] : 0;
	if ($totalRegistro <= 0) {
		return;
	}
	$mysqli = conectar_al_servidor();
	$detallesHistoricos = array();
	$codVentaHistorica = (int)$codVentaHistorica;
	if ($preservarHistoricos && $codVentaHistorica > 0) {
		$stmtHistoricos = $mysqli->prepare(
			"SELECT cod_productoFK,cantidad_detalle
			 FROM detalle_venta
			 WHERE cod_ventaFK=? AND estado='Activo'"
		);
		if ($stmtHistoricos) {
			$stmtHistoricos->bind_param("i",$codVentaHistorica);
			if ($stmtHistoricos->execute()) {
				$resultadoHistoricos = $stmtHistoricos->get_result();
				while ($detalleHistorico = $resultadoHistoricos->fetch_assoc()) {
					$claveHistorica = claveDetalleHistoricoTratamiento(
						$detalleHistorico["cod_productoFK"],
						$detalleHistorico["cantidad_detalle"],
						true
					);
					$detallesHistoricos[$claveHistorica] = isset($detallesHistoricos[$claveHistorica])
						? $detallesHistoricos[$claveHistorica] + 1
						: 1;
				}
			}
			$stmtHistoricos->close();
		}
	}
	for ($indice = 1; $indice <= $totalRegistro; $indice++) {
		$producto = isset($_POST["cod_productoFK".$indice]) ? $_POST["cod_productoFK".$indice] : "";
		$cantidad = isset($_POST["cantidad_detalle".$indice]) ? $_POST["cantidad_detalle".$indice] : "";
		if ($producto !== "") {
			$claveHistorica = claveDetalleHistoricoTratamiento($producto,$cantidad);
			if (isset($detallesHistoricos[$claveHistorica]) && $detallesHistoricos[$claveHistorica] > 0) {
				$detallesHistoricos[$claveHistorica]--;
				continue;
			}
			validarCantidadUnitariaTratamientoVenta($mysqli,$producto,$cantidad,true);
		}
	}
	$mysqli->close();
}



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
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	validarDetallesUnitariosVenta(
		"totalRegistro",
		$operacion==="editar" ? $cod_ventaFK : 0,
		$operacion==="editar"
	);

$num_factura=$_POST['num_factura'];
$num_factura = mb_convert_encoding((string)($num_factura), 'ISO-8859-1', 'UTF-8');

$nro_comprobante= $_POST['nro_comprobante'];
$nro_comprobante = mb_convert_encoding((string)($nro_comprobante), 'ISO-8859-1', 'UTF-8');

if($cod_ventaFK==""){
$fecha_venta=$_POST['fecha_venta'];
$fecha_venta = mb_convert_encoding((string)($fecha_venta), 'ISO-8859-1', 'UTF-8');
$cod_usuarioFK=$user;
$cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
$cod_clienteFK=$_POST['cod_clienteFK'];
$cod_clienteFK = mb_convert_encoding((string)($cod_clienteFK), 'ISO-8859-1', 'UTF-8');
$cod_cobradorFK=$_POST['cod_cobradorFK'];
$cod_cobradorFK = mb_convert_encoding((string)($cod_cobradorFK), 'ISO-8859-1', 'UTF-8');
$TipoVenta=$_POST['TipoVenta'];
$TipoVenta = mb_convert_encoding((string)($TipoVenta), 'ISO-8859-1', 'UTF-8');
$TipoPago=$_POST['TipoPago'];
$TipoPago = mb_convert_encoding((string)($TipoPago), 'ISO-8859-1', 'UTF-8');
$vendedor1=$_POST['vendedor1'];
$vendedor1 = mb_convert_encoding((string)($vendedor1), 'ISO-8859-1', 'UTF-8');
$vendedor2=$_POST['vendedor2'];
$vendedor2 = mb_convert_encoding((string)($vendedor2), 'ISO-8859-1', 'UTF-8');
$comisioncobrador=$_POST['comisioncobrador'];
$comisioncobrador = mb_convert_encoding((string)($comisioncobrador), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$idGaranteFk=$_POST['idGaranteFk'];
$idGaranteFk = mb_convert_encoding((string)($idGaranteFk), 'ISO-8859-1', 'UTF-8');
$tipo_comprobante=$_POST['tipo_comprobante'];
$tipo_comprobante = mb_convert_encoding((string)($tipo_comprobante), 'ISO-8859-1', 'UTF-8');
$puntoexpedicion=$_POST['puntoexpedicion'];
$puntoexpedicion = mb_convert_encoding((string)($puntoexpedicion), 'ISO-8859-1', 'UTF-8');

$codSolicitudCreditoFK=$_POST['codSolicitudCreditoFK'];
$codSolicitudCreditoFK = mb_convert_encoding((string)($codSolicitudCreditoFK), 'ISO-8859-1', 'UTF-8');

$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

$descuento=$_POST['descuento'];
$descuento = mb_convert_encoding((string)($descuento), 'ISO-8859-1', 'UTF-8');

clienteVentaValidarParaGuardar($cod_clienteFK,"nuevo",0);
$datosventa=iniciarVenta($codSolicitudCreditoFK,$puntoexpedicion,$tipo_comprobante,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comisioncobrador,$descuento,$cod_local,$idGaranteFk);
$cod_ventaFK=$datosventa[0];
$num_factura=$datosventa[1];
}
if($operacion=="editar" && $cod_ventaFK!="")
{
	registrarSolicitudEliminacionGenerica(
		"venta",
		"cod_venta",
		$cod_ventaFK,
		"Solicitud automatica por edicion de detalle de venta.",
		$user,
		"archivo: abmdetalleventa.php | funcion: verificar | funt: editar | cod_ventaFK: ".$cod_ventaFK." | num_factura: ".$num_factura." | nro_comprobante: ".$nro_comprobante,
		"estado",
		"Activo"
	);
}
abm($fecha_venta,$tipo,$cod_ventaFK,$num_factura,$nro_comprobante,$operacion);

}

if($operacion=="cambio" )
{
	
	
$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = mb_convert_encoding((string)($cod_detalle), 'ISO-8859-1', 'UTF-8');
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
$cantidaCambio=$_POST['cantidaCambio'];
$cantidaCambio = quitarseparadormiles($cantidaCambio);
$CodProductocompraCambio=$_POST['CodProductocompraCambio'];
$CodProductocompraCambio = mb_convert_encoding((string)($CodProductocompraCambio), 'ISO-8859-1', 'UTF-8');
$MetodoPagoCambio=$_POST['MetodoPagoCambio'];
$MetodoPagoCambio = mb_convert_encoding((string)($MetodoPagoCambio), 'ISO-8859-1', 'UTF-8');
$Local_FK=$_POST['Local_FK'];
$Local_FK = mb_convert_encoding((string)($Local_FK), 'ISO-8859-1', 'UTF-8');
validarDetallesUnitariosVenta("TotalRegistro",0,false);
registrarSolicitudEliminacionGenerica(
	"detalle_venta",
	"cod_detalle",
	$cod_detalle,
	"Solicitud automatica por cambio de producto en venta.",
	$user,
	"archivo: abmdetalleventa.php | funcion: verificar | funt: cambio | cod_detalle: ".$cod_detalle." | cod_ventaFK: ".$cod_ventaFK." | cantidaCambio: ".$cantidaCambio." | CodProductocompraCambio: ".$CodProductocompraCambio." | MetodoPagoCambio: ".$MetodoPagoCambio,
	"estado",
	"Activo"
);
cambiar($cod_detalle,$cod_ventaFK,$cantidaCambio,$CodProductocompraCambio,$MetodoPagoCambio,$user,$Local_FK);

}

if($operacion=="quitarDevolucion" )
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = mb_convert_encoding((string)($cod_detalle), 'ISO-8859-1', 'UTF-8');

	$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = mb_convert_encoding((string)($cod_productoFK), 'ISO-8859-1', 'UTF-8');

$cantidaCambio=$_POST['cantidaCambio'];
$cantidaCambio = quitarseparadormiles($cantidaCambio);
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
$motivo=$_POST['motivo'];
$motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');

$Local_FK=$_POST['Local_FK'];
$Local_FK = mb_convert_encoding((string)($Local_FK), 'ISO-8859-1', 'UTF-8');

quitarDevolucion($cod_detalle,$cod_productoFK,$cod_ventaFK,$motivo,$cantidaCambio,$Local_FK);

}

if($operacion=="NuevoGarantia" )
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = mb_convert_encoding((string)($cod_detalle), 'ISO-8859-1', 'UTF-8');
	$cod_productoFK=$_POST['cod_productoFK'];
$cod_productoFK = mb_convert_encoding((string)($cod_productoFK), 'ISO-8859-1', 'UTF-8');
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
$observacion=$_POST['observacion'];
$observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
$fecharecibido=$_POST['fecharecibido'];
$fecharecibido = mb_convert_encoding((string)($fecharecibido), 'ISO-8859-1', 'UTF-8');
$telefonoaviso=$_POST['telefonoaviso'];
$telefonoaviso = mb_convert_encoding((string)($telefonoaviso), 'ISO-8859-1', 'UTF-8');


usodegarantia($telefonoaviso,$observacion,$fecharecibido,$cod_detalle,$cod_productoFK,$cod_ventaFK,$user,$operacion);

}

if($operacion=="editarusogarantia" )
{
	
$idgarantia=$_POST['idgarantia'];
$idgarantia = mb_convert_encoding((string)($idgarantia), 'ISO-8859-1', 'UTF-8');

$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

registrarSolicitudEliminacionGenerica(
	"garantias",
	"idgarantia",
	$idgarantia,
	"Solicitud automatica por edicion de uso de garantia.",
	$user,
	"archivo: abmdetalleventa.php | funcion: verificar | funt: editarusogarantia | idgarantia: ".$idgarantia." | fecha: ".$fecha." | estado: ".$estado,
	"estado",
	$estado
);

editarusogarantia($idgarantia,$fecha,$estado,$user);

}

if($operacion=="eliminar")
{
	
$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = mb_convert_encoding((string)($cod_detalle), 'ISO-8859-1', 'UTF-8');
$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
$codProducto=$_POST['codProducto'];
$codProducto = mb_convert_encoding((string)($codProducto), 'ISO-8859-1', 'UTF-8');
$motivo=$_POST['motivo'];
$motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
$monto= $_POST['monto'];
$monto = mb_convert_encoding((string)($monto), 'ISO-8859-1', 'UTF-8');
$monto_original= $_POST['monto_original'];
$monto_original = mb_convert_encoding((string)($monto_original), 'ISO-8859-1', 'UTF-8');
// Limpia los numeros
$monto = str_replace('.','',$monto);
$monto_original = str_replace('.','',$monto_original);
$monto= intval($monto_original) - intval($monto);
quitarproducto($cod_detalle,$cod_ventaFK,$codProducto,$motivo,$monto,$monto_original);


}
if($operacion=="quitardegarantia")
{
	
	
	$cod_detalle=$_POST['cod_detalle'];
$cod_detalle = mb_convert_encoding((string)($cod_detalle), 'ISO-8859-1', 'UTF-8');
quitardegarantia($cod_detalle);

}

if($operacion=="buscar")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	BuscarRegistro($cod_ventaFK);

}	

if($operacion=="buscarDatosPagare")
{
	$cod_ventaFK=$_POST['cod_venta'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	BuscarDatosPagare($cod_ventaFK);

}	

if($operacion=="productosCompradoscliente")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	productosCompradoscliente($cod_ventaFK);

}	

if($operacion=="productosCompradosclienteInactivo")
{
	$codCliente=$_POST['codCliente'];
$codCliente = mb_convert_encoding((string)($codCliente), 'ISO-8859-1', 'UTF-8');
	productosCompradosclienteInactivo($codCliente);

}	

if($operacion=="detalleenhistorial")
{
	$cod_ventaFK=$_POST['buscar'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	BuscarRegistroEnHistorilaVenta($cod_ventaFK);

}	

if($operacion=="buscarproductovendidos")
{
	
	$codigo=$_POST['codigo'];
$codigo = mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
$producto=$_POST['producto'];
$producto = mb_convert_encoding((string)($producto), 'ISO-8859-1', 'UTF-8');

$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$categoria=$_POST['categoria'];
$categoria = mb_convert_encoding((string)($categoria), 'ISO-8859-1', 'UTF-8');
$marca=$_POST['marca'];
$marca = mb_convert_encoding((string)($marca), 'ISO-8859-1', 'UTF-8');
$agrupacionproductovendidoinforme=$_POST['agrupacionproductovendidoinforme'];
$agrupacionproductovendidoinforme = mb_convert_encoding((string)($agrupacionproductovendidoinforme), 'ISO-8859-1', 'UTF-8');
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	buscarproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$agrupacionproductovendidoinforme);

}

if($operacion=="buscarmasproductovendidos")
{
	
	$codigo=$_POST['codigo'];
$codigo = mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
$producto=$_POST['producto'];
$producto = mb_convert_encoding((string)($producto), 'ISO-8859-1', 'UTF-8');

$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$categoria=$_POST['categoria'];
$categoria = mb_convert_encoding((string)($categoria), 'ISO-8859-1', 'UTF-8');
$marca=$_POST['marca'];
$marca = mb_convert_encoding((string)($marca), 'ISO-8859-1', 'UTF-8');
$totalventa=$_POST['totalventa'];
$totalventa = quitarseparadormiles($totalventa);
$totalinvertido=$_POST['totalinvertido'];
$totalinvertido = quitarseparadormiles($totalinvertido);
$agrupacionproductovendidoinforme=$_POST['agrupacionproductovendidoinforme'];
$agrupacionproductovendidoinforme = mb_convert_encoding((string)($agrupacionproductovendidoinforme), 'ISO-8859-1', 'UTF-8');
$registrocargado=$_POST['registrocargado'];
$registrocargado = mb_convert_encoding((string)($registrocargado), 'ISO-8859-1', 'UTF-8');
if($cod_local==""){
$controllocal=controldeaccesoacasas($user,"CAMBIARLOCAL"," u.accion='SI' ");
	if($controllocal==0){
		$cod_local=buscarlocaluser($user);
	}
}
	buscarmasproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$totalventa,$totalinvertido,$registrocargado,$agrupacionproductovendidoinforme);

}

if($operacion=="buscarHistorialGarantia")
{

$nrofactura=$_POST['nrofactura'];
$nrofactura = mb_convert_encoding((string)($nrofactura), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
$documento=$_POST['documento'];
$documento = mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
$cliente=$_POST['cliente'];
$cliente = mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
buscarHistorialGarantia($nrofactura,$cod_local,$documento,$cliente,$estado);

}	


if($operacion=="comisionvendedor")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$vendedor=$_POST['vendedor'];
$vendedor = mb_convert_encoding((string)($vendedor), 'ISO-8859-1', 'UTF-8');
$producto=$_POST['producto'];
$producto = mb_convert_encoding((string)($producto), 'ISO-8859-1', 'UTF-8');
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = mb_convert_encoding((string)($fechafiltro), 'ISO-8859-1', 'UTF-8');
$Descuento=$_POST['Descuento'];
$Descuento = mb_convert_encoding((string)($Descuento), 'ISO-8859-1', 'UTF-8');
$Flete=$_POST['Flete'];
$Flete = mb_convert_encoding((string)($Flete), 'ISO-8859-1', 'UTF-8');
$cliente=$_POST['cliente'];
$cliente = mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');

$Local=$_POST['Local'];
$Local = mb_convert_encoding((string)($Local), 'ISO-8859-1', 'UTF-8');

 comisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$Descuento,$Flete,$cliente,$Local,$producto);

}	

if($operacion=="mascomisionvendedor")
{
	$fecha1=$_POST['fecha1'];
$fecha1 = mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST['fecha2'];
$fecha2 = mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
$vendedor=$_POST['vendedor'];
$vendedor = mb_convert_encoding((string)($vendedor), 'ISO-8859-1', 'UTF-8');
$fechafiltro=$_POST['fechafiltro'];
$fechafiltro = mb_convert_encoding((string)($fechafiltro), 'ISO-8859-1', 'UTF-8');
$producto=$_POST['producto'];
$producto = mb_convert_encoding((string)($producto), 'ISO-8859-1', 'UTF-8');
$registrocargado=$_POST['registrocargado'];
$registrocargado = mb_convert_encoding((string)($registrocargado), 'ISO-8859-1', 'UTF-8');
$totalcomision=$_POST['totalcomision'];
$totalcomision = quitarseparadormiles($totalcomision);
$totalventa=$_POST['totalventa'];
$totalventa = quitarseparadormiles($totalventa);
$registroscargados=$_POST['registroscargados'];
$registroscargados = quitarseparadormiles($registroscargados);
$Descuento=$_POST['Descuento'];
$Descuento = mb_convert_encoding((string)($Descuento), 'ISO-8859-1', 'UTF-8');
$Flete=$_POST['Flete'];
$Flete = mb_convert_encoding((string)($Flete), 'ISO-8859-1', 'UTF-8'); 
$totalDescuento=$_POST['totalDescuento'];
$totalDescuento = mb_convert_encoding((string)($totalDescuento), 'ISO-8859-1', 'UTF-8'); 
$cliente=$_POST['cliente'];
$cliente = mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
$Local=$_POST['Local'];
$Local = mb_convert_encoding((string)($Local), 'ISO-8859-1', 'UTF-8');
 mascomisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$registrocargado,$totalcomision,$totalventa,$registroscargados,$Descuento,$Flete,$producto,$totalDescuento,$cliente,$Local);

}	


if($operacion=="detallesventadevolucion")
{
	$buscar=$_POST['buscar'];
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
	
 BuscarRegistroDevolucion($buscar,$cod_local);

}

if($operacion=="buscarexpedientes")
{
	$cliente=$_POST['cliente'];
$cliente = mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	buscarexpedientes($cliente);

}	



if($operacion=="detallePedido")
{
	$cod_ventaFK=$_POST['buscar'];
	$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	
	echo("hola");
	exit;
	detallePedido($cod_ventaFK);

}		
	

}




function  detallePedido($buscar){
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,dtv.estado,detalleproducto,dtv.descuento,dtv.comision,vt.cod_venta,vt.TipoPago,vt.num_factura,vt.puntoexpedicion,vt.fecha_venta,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select count(idgarantia) from garantias gt where gt.cod_detalle_venta_fk=dtv.cod_detalle and (gt.estado='Pendiente a verificar' or gt.estado='verificacion') limit 1),0) as nroGarantia,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";



$pagina="";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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

$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$nroGarantia = mb_convert_encoding((string)($valor['nroGarantia']), 'UTF-8', 'ISO-8859-1'); 
$impuesto = mb_convert_encoding((string)($valor['impuesto']), 'UTF-8', 'ISO-8859-1'); 
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$cod_venta = mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1'); 
$TipoPago = mb_convert_encoding((string)($valor['TipoPago']), 'UTF-8', 'ISO-8859-1'); 
 $num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
$puntoexpedicion=mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
$NombreMarca=mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1');
$fecha_venta=mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1');
$telefono=mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1');


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  name='tdDetalleVenta' >
<td  id='td_datos_1' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_2' style='display:none'>".$nombre_producto." *".$NombreMarca."*</td>
<td   style='width:20%;>".$nombre_producto." *".$NombreMarca."</td>
<td  id='td_datos_3' style='display:none'>".$detalleproducto."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}




function obtenerNroVentaDetalle($mysqli,$cod_venta,$fallback){
	$fallback=trim((string)$fallback);
	if($fallback!=""){
		return $fallback;
	}

	$cod_venta_sql=$mysqli->real_escape_string($cod_venta);
	$sql="select concat(ifnull(puntoexpedicion,''),if(ifnull(puntoexpedicion,'')!='','-',''),ifnull(num_factura,'')) as nroventa from venta where cod_venta='$cod_venta_sql' limit 1";
	$stmt=$mysqli->prepare($sql);
	if($stmt && $stmt->execute()){
		$result=$stmt->get_result();
		if($valor=mysqli_fetch_assoc($result)){
			$nroventa=trim((string)$valor['nroventa']);
			if($nroventa!=""){
				return $nroventa;
			}
		}
	}

	return $cod_venta;
}




function abm($fecha_venta,$tipo,$cod_ventaFK,$num_factura,$nro_comprobante,$operacion)
{
	
$mysqli=conectar_al_servidor(); 
$control=1;	
$totalRegistro=$_POST['totalRegistro'];
$totalRegistro = mb_convert_encoding((string)($totalRegistro), 'ISO-8859-1', 'UTF-8');
$nroventaDetalle=obtenerNroVentaDetalle($mysqli,$cod_ventaFK,$nro_comprobante);



while($control<=$totalRegistro){

$cod_productoFK=$_POST['cod_productoFK'.$control];
$cod_productoFK = mb_convert_encoding((string)($cod_productoFK), 'ISO-8859-1', 'UTF-8');

$cantidad_detalle=$_POST['cantidad_detalle'.$control];
$cantidad_detalle = quitarseparadormiles($cantidad_detalle);

$precio_producto=$_POST['precio_producto'.$control];
$precio_producto = quitarseparadormiles($precio_producto);

$subtotal=$_POST['subtotal'.$control];
$subtotal = quitarseparadormiles($subtotal);

$comision=$_POST['comision'.$control];
$comision = quitarseparadormiles($comision);

$descuento=$_POST['descuento'.$control];
$descuento = quitarseparadormiles($descuento);

$detalleproducto=$_POST['detalleproducto'.$control];
$detalleproducto = mb_convert_encoding((string)($detalleproducto), 'ISO-8859-1', 'UTF-8');
	
$subPrecioCompra=obtenerCostoProducto($cod_productoFK);	
$subtotal=($cantidad_detalle*$precio_producto)-$descuento;
	
$cod_local=$_POST['cod_local'];
$cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');


$cod_aperturaCajaFK=$_POST['cod_aperturaCajaFK'];
$cod_aperturaCajaFK = mb_convert_encoding((string)($cod_aperturaCajaFK), 'ISO-8859-1', 'UTF-8');


if($cod_productoFK!="10001"){
	$cod_aperturaCajaFK="0";
}


if($cantidad_detalle!="" || $cod_productoFK!="" || $cod_ventaFK!=""  ){

$consulta1="Insert into detalle_venta (cantidad_detalle,descuento,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado,comision,detalleproducto,cod_aperturaCajaFK,nroventa)
values(?,?,?,?,?,?,?,'Activo',?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssssssssss';
$stmt1->bind_param($ss,$cantidad_detalle,$descuento,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$comision,$detalleproducto,$cod_aperturaCajaFK,$nroventaDetalle);

if (!$stmt1->execute()) {
	
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


editar_cantidad($cod_productoFK,$cantidad_detalle,"resta",$cod_local);
$control=$control+1;
}	
	
}




$subtotal=obtenerTotal($cod_ventaFK);
actualizarTotal($cod_ventaFK,$subtotal);

funcionCrearCredito($tipo,$fecha_venta,$cod_ventaFK,$subtotal,0,$nro_comprobante);

$seguimientoPaciente = array("ok" => false, "motivo" => "no_ejecutado");
if (function_exists("seguimientoPacienteAsegurarHiloPorVenta")) {
	try {
		$usuarioSeguimiento = isset($_POST['useru']) ? $_POST['useru'] : "";
		$seguimientoPaciente = seguimientoPacienteAsegurarHiloPorVenta($cod_ventaFK, $usuarioSeguimiento, "detalle_venta");
	} catch (Throwable $e) {
		$seguimientoPaciente = array("ok" => false, "motivo" => "error_no_bloqueante", "mensaje" => $e->getMessage());
	}
}

$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'),"3" => $cod_ventaFK,"4" => $num_factura, "seguimiento_paciente" => $seguimientoPaciente);
echo json_encode($informacion);	
exit;
	
}

function funcionCrearCredito($tipo,$fecha_venta,$cod_venta,$Monto,$descuento,$nro_comprobante){
 
if($tipo == "1"){
	
	$mysqli=conectar_al_servidor(); 
$consulta="delete from credito where  cod_venta='$cod_venta'";
$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$nroventaCredito = trim((string)$nro_comprobante) != "" ? $nro_comprobante : $cod_venta;
$consulta="Insert into credito (plazo,fechapago,cod_venta,Monto,Esado,Nro_recibo,dias,interes,totalinteres,totaldeuda,total,descuento,deudaInteres,nroventa)
			values('Contado','$fecha_venta','$cod_venta','$Monto','Pendiente','0','0','0','0','$Monto','$Monto','$descuento',0,'$nroventaCredito')";
			
$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
}	
	
	
}


function cambiar($cod_detalle,$cod_ventaFK,$cantidaCambio,$CodProductoCambio,$metodopago,$cod_usuarioFK,$Local_FK)
{
	
	
if($cod_detalle=="" || $cod_ventaFK==""  ){
$inforOacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

editar_cantidad($CodProductoCambio,$cantidaCambio,"suma",$Local_FK);

$consulta1="delete from detalle_venta where cod_detalle=? ";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$cod_detalle);

if (!$stmt1->execute()) {
	
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$consulta1="Insert into cambiarproducto (fecha,cod_productoFK,cod_ventaFK,cod_usuarioFK)
values(Current_Date,?,?,?)";

$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$CodProductoCambio,$cod_ventaFK,$cod_usuarioFK);


if (!$stmt1->execute()) {

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

$control=1;	
$totalRegistro=$_POST['TotalRegistro'];
$totalRegistro = mb_convert_encoding((string)($totalRegistro), 'ISO-8859-1', 'UTF-8');

$motivo='Cambio';
$nroventaDetalle=obtenerNroVentaDetalle($mysqli,$cod_ventaFK,"");

while($control<=$totalRegistro){
	
	
$cod_productoFK=$_POST['cod_productoFK'.$control];
$cod_productoFK = mb_convert_encoding((string)($cod_productoFK), 'ISO-8859-1', 'UTF-8');

$cantidad_detalle=$_POST['cantidad_detalle'.$control];
$cantidad_detalle = quitarseparadormiles($cantidad_detalle);

$precio_producto=$_POST['precio_producto'.$control];
$precio_producto = quitarseparadormiles($precio_producto);

$subtotal=$_POST['subtotal'.$control];
$subtotal = quitarseparadormiles($subtotal);

$comision=$_POST['comision'.$control];
$comision = quitarseparadormiles($comision);

$descuento=$_POST['descuento'.$control];
$descuento = quitarseparadormiles($descuento);

$detalleproducto=$_POST['detalleproducto'.$control];
$detalleproducto = mb_convert_encoding((string)($detalleproducto), 'ISO-8859-1', 'UTF-8');
	
$subPrecioCompra=obtenerCostoProducto($cod_productoFK);	
$subtotal=($cantidad_detalle*$precio_producto)-$descuento;
	

	
if($cantidad_detalle!="" || $cod_productoFK!="" || $cod_ventaFK!=""  ){

$consulta1="Insert into detalle_venta (cantidad_detalle,descuento,cod_productoFK,precio_producto,cod_ventaFK,subtotal,subPrecioCompra,estado,comision,detalleproducto,cod_aperturaCajaFK,nroventa)
values(?,?,?,?,?,?,?,'Activo',?,?,0,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssssssss';
$stmt1->bind_param($ss,$cantidad_detalle,$descuento,$cod_productoFK,$precio_producto,$cod_ventaFK,$subtotal,$subPrecioCompra,$comision,$detalleproducto,$nroventaDetalle);

if (!$stmt1->execute()) {
	
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


editar_cantidad($cod_productoFK,$cantidad_detalle,"resta",$Local_FK);


$consulta1="Insert into detallescambio (cant,cod_productoFK,idcambiarproductoFK)
values(?,?,(select idcambiarproducto from cambiarproducto where cod_ventaFK='$cod_ventaFK' order by  idcambiarproducto desc limit 1 ))";

$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$cantidad_detalle,$cod_productoFK);


if (!$stmt1->execute()) {

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}



}	
	
$control=$control+1;
	
	
}

$subtotal=obtenerTotal($cod_ventaFK);
 actualizarTotal($cod_ventaFK,$subtotal);
 refinanciarencambio($cod_ventaFK,$subtotal,$metodopago);
$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'));
echo json_encode($informacion);	
exit;
	
}


function quitarDevolucion($cod_detalle,$cod_productoFK,$cod_ventaFK,$motivo,$cantidaCambio,$Local_FK)
{
	
	
	
if($cod_detalle=="" || $cod_productoFK=="" || $cod_ventaFK==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);
exit;
}
$motivo="Devolucion";

registrarSolicitudEliminacionGenerica(
	"detalle_venta",
	"cod_detalle",
	$cod_detalle,
	"Solicitud automatica por devolucion de detalle de venta.",
	isset($_POST['useru']) ? mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8') : "0",
	"archivo: abmdetalleventa.php | funcion: quitarDevolucion | funt: quitarDevolucion | cod_detalle: ".$cod_detalle." | cod_productoFK: ".$cod_productoFK." | cod_ventaFK: ".$cod_ventaFK." | cantidaCambio: ".$cantidaCambio,
	"estado",
	"Activo"
);

$mysqli=conectar_al_servidor(); 



// $consulta1="delete from detalle_venta where cod_detalle=? ";
// $stmt1 = $mysqli->prepare($consulta1);
// $ss='s';
// $stmt1->bind_param($ss,$cod_detalle);
// if (!$stmt1->execute()) {
	
// echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
// exit;

// }


$consulta1="Insert into cambios (motivo,fecha,cant,cod_producto,cod_venta,coddetalleventa)
values('$motivo',Current_Date,'$cantidaCambio','$cod_productoFK','$cod_ventaFK','$cod_detalle')";


$stmt1 = $mysqli->prepare($consulta1);


if (!$stmt1->execute()) {
	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

editar_cantidad($cod_productoFK,$cantidaCambio,"suma",$Local_FK);

$subtotal=obtenerTotal($cod_ventaFK);


$informacion =array("1" => "exito","2" => number_format($subtotal,'0',',','.'));
echo json_encode($informacion);	
exit;
}


function quitardegarantia($cod_detalle)
{
	
	
	
if($cod_detalle==""  ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor();
registrarSolicitudEliminacionGenerica(
	"detalle_venta",
	"cod_detalle",
	$cod_detalle,
	"Solicitud automatica por quitar detalle de garantia.",
	isset($_POST['useru']) ? mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8') : "0",
	"archivo: abmdetalleventa.php | funcion: quitardegarantia | funt: quitardegarantia | cod_detalle: ".$cod_detalle,
	"estado",
	"Activo"
);
$consulta1="update detalle_venta set estado='Activo' where cod_detalle=? ";
$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss,$cod_detalle);
if (!$stmt1->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}



$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function usodegarantia($telefonoaviso,$observacion,$fecharecibido,$cod_detalle,$cod_productoFK,$cod_ventaFK,$cod_usuarioFK,$operacion)
{
	
	
	
if($cod_detalle=="" || $cod_productoFK=="" || $cod_ventaFK==""   ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}
$motivo="Devolucion";

$mysqli=conectar_al_servidor(); 



// $consulta1="update detalle_venta set estado='Garantia' where cod_detalle=? ";
// $stmt1 = $mysqli->prepare($consulta1);
// $ss='s';
// $stmt1->bind_param($ss,$cod_detalle);

// if (!$stmt1->execute()) {
// echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
// exit;

// }






$consulta1="Insert into garantias (fecharecibido,observacion,estado,cod_productoFK,cod_ventaFK,cod_usuarioFKRecibido,cod_detalle_venta_fk,telefonoaviso)
values('$fecharecibido','$observacion','Pendiente a verificar','$cod_productoFK','$cod_ventaFK','$cod_usuarioFK','$cod_detalle','$telefonoaviso')";

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}

function editarusogarantia($idgarantia,$fecha,$estado,$codUsuarioFk)
{
	
	
	
if($idgarantia=="" || $fecha=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}
$motivo="Devolucion";

$mysqli=conectar_al_servidor(); 

if($estado=="verificacion"){
	$consulta1="update  garantias set estado='verificacion',fechaenvio='$fecha',cod_usuarioFkEnvio='$codUsuarioFk' where idgarantia='$idgarantia' ";
}
if($estado=="listo"){
	$consulta1="update  garantias set estado='listo',fechadevuelto='$fecha',cod_usuarioFkDevuelto='$codUsuarioFk' where idgarantia='$idgarantia' ";
}
if($estado=="entregado"){
	$consulta1="update  garantias set estado='entregado',fechaentrega='$fecha',cod_usuarioFkEntrega='$codUsuarioFk' where idgarantia='$idgarantia' ";
}



$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1->execute()) {
	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function quitarproducto($cod_detalle, $cod_ventaFK, $codProducto, $motivo, $descuento, $monto)
{
	if ($cod_detalle == "" ||  $cod_ventaFK == "") {
		$informacion = array("1" => "camposvacio");
		echo json_encode($informacion);
		exit;
	}

	$user = $_POST['useru'];
	$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

	// Limpia el monto
	$monto= str_replace('.','',$monto);
	$descuento= str_replace('.','',$descuento);
	$monto= intval($monto) - intval($descuento);

	$mysqli=conectar_al_servidor();
	$consulta1="SELECT dtv.cantidad_detalle, dtv.cod_productoFK, vt.cod_local
		from detalle_venta dtv inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
		where dtv.cod_detalle=? limit 1";
	$stmt1 = $mysqli->prepare($consulta1);
	$ss='s';
	$stmt1->bind_param($ss,$cod_detalle);
	if (!$stmt1->execute()) {
	echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
	exit;
	}
	$result = $stmt1->get_result();
	$detalle = $result ? $result->fetch_assoc() : null;
	$stmt1->close();
	if (!$detalle) {
		$informacion = array("1" => "camposvacio");
		echo json_encode($informacion);
		exit;
	}

	$codProductoEliminado = $codProducto != "" ? $codProducto : $detalle['cod_productoFK'];
	registrarSolicitudEliminacionGenerica(
		"detalle_venta",
		"cod_detalle",
		$cod_detalle,
		$motivo,
		$user,
		"archivo: abmdetalleventa.php | funcion: quitarproducto | funt: eliminar | cod_detalle: ".$cod_detalle." | cod_ventaFK: ".$cod_ventaFK." | codProducto: ".$codProductoEliminado." | cantidad: ".$detalle['cantidad_detalle']." | cod_local: ".$detalle['cod_local'],
		"estado",
		"Activo"
	);

	$consulta1="delete from detalle_venta where cod_detalle=? ";
	$stmt1 = $mysqli->prepare($consulta1);
	$stmt1->bind_param($ss,$cod_detalle);
	if (!$stmt1->execute()) {
	echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
	exit;
	}
	$stmt1->close();
	mysqli_close($mysqli);

	editar_cantidad($codProductoEliminado,$detalle['cantidad_detalle'],"suma",$detalle['cod_local']);
	registrarDetalleVentaEliminado($motivo,$codProductoEliminado,$user,$cod_ventaFK);

	$subtotal = obtenerTotal($cod_ventaFK);
	actualizarTotal($cod_ventaFK,$subtotal);

	$informacion = array("1" => "exito", "2" => number_format($subtotal, '0', ',', '.'), "3" => $cod_ventaFK, "4" => "");
	echo json_encode($informacion);
	exit;
}

function registrarDetalleVentaEliminado($motivo,$codProducto,$user,$cod_ventaFK)
{
	$mysqli=conectar_al_servidor();
	$consulta1="Insert into detallesventaeliminado (motivo,fecha,cod_producto,cod_user_insert,fecha_insert,cod_ventaFK)
	values(?,Current_Date,?,?,CURRENT_TIMESTAMP,?)";
	$stmt1 = $mysqli->prepare($consulta1);
	$ss='ssss';
	$stmt1->bind_param($ss,$motivo,$codProducto,$user,$cod_ventaFK);
	if (!$stmt1->execute()) {
	echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
	exit;
	}
	mysqli_close($mysqli);
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


function obtenerTotal($cod_ventaFK)
{
	$mysqli=conectar_al_servidor();
	 $subtotal='';
	$sql= "Select sum(subtotal) as subtotal from detalle_venta where cod_ventaFK='$cod_ventaFK'  ";
		
   
   
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
		  
		  
		      $subtotal=$valor['subtotal'];
		  	
			  
			  
	  }
 }
 
 
return $subtotal;


}


function obtenerCostoProducto($cod_producto)
{
	$mysqli=conectar_al_servidor();
	 $precio_compra='';
	$sql= "Select precio_compra from producto where cod_producto='$cod_producto'  ";
		
   
   
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
		  
		  
		      $precio_compra=$valor['precio_compra'];
		  	
			  
			  
	  }
 }
 
 
return $precio_compra;


}

function actualizarTotal($cod_venta,$total){
	
	$mysqli=conectar_al_servidor(); 
	$consulta1="Update venta set total_venta=? where cod_venta=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ss';
$stmt1->bind_param($ss,$total,$cod_venta); 
if (!$stmt1->execute()) {
	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


}




function EditarSolicitud($idAbm,$detalleVenta,$cod_usu)
{

$mysqli=conectar_al_servidor(); 


$consulta1="Update solicitudcredito set  estado='FINALIZADO' , detalleVenta='$detalleVenta'   where idSolicitudCredito=?";	

$stmt1 = $mysqli->prepare($consulta1);
$ss='s';
$stmt1->bind_param($ss ,$idAbm);

if (!$stmt1->execute()) {	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}

 mysqli_close($mysqli);

}




function iniciarVenta($codSolicitudCreditoFK,$puntoexpedicion,$tipo_comprobante,$fecha_venta,$cod_usuarioFK,$cod_clienteFK,$num_factura,$cod_cobradorFK,$TipoVenta,$TipoPago,$vendedor1,$vendedor2,$comisioncobrador,$descuento,$cod_local,$idGaranteFk){
	
	$mysqli=conectar_al_servidor(); 
	$codSolicitudCreditoFK = trim((string)$codSolicitudCreditoFK);
	if($codSolicitudCreditoFK=="" || !is_numeric($codSolicitudCreditoFK)){
		$codSolicitudCreditoFK="0";
	}
	
	if($num_factura==""){
	if($tipo_comprobante=="FACTURA"){
	$datos=buscarcodNroFactura($cod_local,$puntoexpedicion);
	$num_factura=buscarnrofactura($datos[0],$datos[1]);
	$codnrofactura=$datos[0];
	}else{
		$num_factura=buscarnroventab();
		$puntoexpedicion="";
	   $codnrofactura="";
	}
	}
		/*AUDITORIA*/
	date_default_timezone_set('America/Asuncion');    
$fecha_inser_edit = date('Y-m-d H:i:s', time()); 
	 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');


	$consulta1="Insert into venta (idGaranteFk,fecha_venta,total_venta,cod_usuarioFK,cod_clienteFK,num_factura,pago,cod_cobradorFK,TipoVenta,TipoPago,Vendedor1,Vendedor2,comision,descuento,cod_local,tipo_comprobante,puntoexpedicion,codnrofactura,cod_user_insert,fecha_insert,codSolicitudCreditoFK,apodo)
values($idGaranteFk,'$fecha_venta','0',$cod_usuarioFK,$cod_clienteFK,'$num_factura',0,$cod_cobradorFK,'$TipoVenta','$TipoPago','$vendedor1','$vendedor2','$comisioncobrador','$descuento',$cod_local,'$tipo_comprobante','$puntoexpedicion','$codnrofactura','$user','$fecha_inser_edit','$codSolicitudCreditoFK','')";

// echo($consulta1);
// exit;
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}

if($codSolicitudCreditoFK!="" && $codSolicitudCreditoFK!="0" ){
	$detalleVenta=$puntoexpedicion.'-'.$num_factura;
	EditarSolicitud($codSolicitudCreditoFK,$detalleVenta,$cod_usuarioFK);
}
   $datos[0]=obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura);
   $datos[1]=$num_factura;
   return $datos;
}

function obtenerId($cod_clienteFK,$cod_usuarioFK,$num_factura)
{
	$mysqli=conectar_al_servidor();
	 $cod_venta='';
		$sql= "Select cod_venta from venta where cod_clienteFK='$cod_clienteFK' and cod_usuarioFK='$cod_usuarioFK' and num_factura='$num_factura' ";
		
   
   
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
		  
		  
		      $cod_venta=$valor['cod_venta'];
		  	
			  
			  
	  }
 }
 
 
return $cod_venta;


}


function BuscarDatosPagare($cod_venta)
{
	if($cod_venta==""){
		$informacion =array("1" => "camposvacio");
		echo json_encode($informacion);
		exit;
	}

	$mysqli=conectar_al_servidor();
	$sql= "select vt.cod_venta, vt.num_factura, vt.puntoexpedicion, vt.total_venta, vt.descuento, vt.pago,
	(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as cliente,
	(Select direccion from persona where cod_persona=vt.cod_clienteFK) as cliente_direccion,
	(Select email from persona where cod_persona=vt.cod_clienteFK) as cliente_referencia,
	(Select telefono from persona where cod_persona=vt.cod_clienteFK) as cliente_telefono,
	(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as cliente_documento,
	(Select nombre_persona from persona where cod_persona=vt.idGaranteFk) as garante,
	(Select direccion from persona where cod_persona=vt.idGaranteFk) as garante_direccion,
	(Select email from persona where cod_persona=vt.idGaranteFk) as garante_referencia,
	(Select telefono from persona where cod_persona=vt.idGaranteFk) as garante_telefono,
	(Select ci_cliente from cliente where cod_cliente=vt.idGaranteFk) as garante_documento,
	IFNULL((Select sum(cr.Monto) from credito cr where cr.cod_venta=vt.cod_venta and cr.tipo='ENTREGA'),0) as entrega_credito,
	(Select fechapago from credito cr where cr.cod_venta=vt.cod_venta and IFNULL(cr.tipo,'')!='ENTREGA' order by cr.fechapago desc limit 1) as vencimiento
	from venta vt where vt.cod_venta=? limit 1";

	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('s', $cod_venta);
	if ( ! $stmt->execute()) {
		echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
		exit;
	}

	$result = $stmt->get_result();
	if (mysqli_num_rows($result)==0) {
		mysqli_close($mysqli);
		$informacion =array("1" => "N");
		echo json_encode($informacion);
		exit;
	}

	$valor= mysqli_fetch_assoc($result);
	$puntoexpedicion=mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
	$num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
	$factura = $puntoexpedicion!="" ? $puntoexpedicion."-".$num_factura : $num_factura;
	$entrega = floatval($valor['entrega_credito']) > 0 ? $valor['entrega_credito'] : $valor['pago'];
	$zonaCliente = trim(mb_convert_encoding((string)($valor['cliente_direccion']), 'UTF-8', 'ISO-8859-1')."-".mb_convert_encoding((string)($valor['cliente_referencia']), 'UTF-8', 'ISO-8859-1'), "-");
	$zonaGarante = trim(mb_convert_encoding((string)($valor['garante_direccion']), 'UTF-8', 'ISO-8859-1')."-".mb_convert_encoding((string)($valor['garante_referencia']), 'UTF-8', 'ISO-8859-1'), "-");

	$datos = array(
		"cod_venta" => mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1'),
		"factura" => $factura,
		"total" => number_format($valor['total_venta'],'0',',','.'),
		"entrega" => number_format($entrega,'0',',','.'),
		"vencimiento" => mb_convert_encoding((string)($valor['vencimiento']), 'UTF-8', 'ISO-8859-1'),
		"cliente" => mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1'),
		"cliente_documento" => mb_convert_encoding((string)($valor['cliente_documento']), 'UTF-8', 'ISO-8859-1'),
		"cliente_direccion" => $zonaCliente,
		"cliente_telefono" => mb_convert_encoding((string)($valor['cliente_telefono']), 'UTF-8', 'ISO-8859-1'),
		"garante" => mb_convert_encoding((string)($valor['garante']), 'UTF-8', 'ISO-8859-1'),
		"garante_documento" => mb_convert_encoding((string)($valor['garante_documento']), 'UTF-8', 'ISO-8859-1'),
		"garante_direccion" => $zonaGarante,
		"garante_telefono" => mb_convert_encoding((string)($valor['garante_telefono']), 'UTF-8', 'ISO-8859-1')
	);

	mysqli_close($mysqli);
	$informacion =array("1" => "exito","2" => $datos);
	echo json_encode($informacion);
	exit;
}


function  BuscarRegistro($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select dtv.descripcion , pr.cod_producto,pr.cod_barra,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,vt.num_factura,vt.puntoexpedicion,concat(vt.puntoexpedicion,'-',vt.num_factura) as fac,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select concat(direccion,'-',email) from persona where  cod_persona=cod_clienteFK) as zonaCliente,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as clientetelefono,
(Select concat(direccion,'-',email) from persona where  cod_persona=vt.idGaranteFk) as zonaGarante,
(Select telefono from persona where cod_persona=vt.idGaranteFk) as Garantetelefono,
(Select ci_cliente from cliente where cod_cliente=vt.idGaranteFk) as nrodocgarante,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
(Select rut_cliente from cliente where cod_cliente=vt.cod_clienteFK) as ruccliente,vt.TipoVenta,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta and plazo!='ENTREGA' ) as nroCouta,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,dtv.descuento,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";

// echo($sql);
// exit;
$clientenombre = ""; 
$clientedireccion = ""; 
$clientetelefono = ""; 
$nrodocliente = ""; 
$nrodocgarante = ""; 
$zonaCliente = ""; 
$Garantetelefono = ""; 
$zonaGarante = ""; 
$TipoVenta = ""; 

$pagina = "";   
$paginarecibo = "";      
$ruccliente = "";      
$paginatickect = "";      
$totalventa = "0";   
$totalpagado = "0";   
$nroFactura = "0";   
$nroVenta = "0";   
$nroCouta = "1";   
$fac="";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$SubTotalestotalIva10=0;
$SubTotalestotalIva5=0;
$totalIvaEx=0;
$totalDescuentoDetalles=0;
$totales10=0;
$totales5=0;
$totalesExt=0;
$totalesiva=0;
$plazo=1;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
 
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1'); 
$TipoVenta = mb_convert_encoding((string)($valor['TipoVenta']), 'UTF-8', 'ISO-8859-1'); 
$cod_barra = mb_convert_encoding((string)($valor['cod_barra']), 'UTF-8', 'ISO-8859-1'); 
$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$impuesto = mb_convert_encoding((string)($valor['impuesto']), 'UTF-8', 'ISO-8859-1'); 
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1'); 
$clientedireccion = mb_convert_encoding((string)($valor['clientedireccion']), 'UTF-8', 'ISO-8859-1'); 
$clientetelefono = mb_convert_encoding((string)($valor['clientetelefono']), 'UTF-8', 'ISO-8859-1'); 
$nrodocliente = mb_convert_encoding((string)($valor['nrodocliente']), 'UTF-8', 'ISO-8859-1'); 
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$Garantetelefono = mb_convert_encoding((string)($valor['Garantetelefono']), 'UTF-8', 'ISO-8859-1'); 
$zonaGarante = mb_convert_encoding((string)($valor['zonaGarante']), 'UTF-8', 'ISO-8859-1'); 
$zonaCliente = mb_convert_encoding((string)($valor['zonaCliente']), 'UTF-8', 'ISO-8859-1'); 
$ruccliente = mb_convert_encoding((string)($valor['ruccliente']), 'UTF-8', 'ISO-8859-1'); 
$nroCouta = mb_convert_encoding((string)($valor['nroCouta']), 'UTF-8', 'ISO-8859-1'); 
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1'); 
$nrodocgarante = mb_convert_encoding((string)($valor['nrodocgarante']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 
$fac = mb_convert_encoding((string)($valor['fac']), 'UTF-8', 'ISO-8859-1'); 

$totalDescuentoDetalles = $totalDescuentoDetalles+$descuento; 

if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}

$iva10porciento=0;
$iva5porciento=0;
$ivaexcentas=0;
$subtotalIva5=0;
$subtotalIva10=0;
$subtotalIvaext=0;

if($impuesto==11){
	$iva10porciento= $subtotal;
$subtotalIva10=($subtotal/$impuesto);
$totalesiva=$totalesiva+$subtotalIva10;
$totales10=$totales10+$subtotalIva10;
$SubTotalestotalIva10=$SubTotalestotalIva10+$subtotal;

}
if($impuesto==21){
	$iva5porciento= $subtotal;
$subtotalIva5=($subtotal/$impuesto);
$totalesiva=$totalesiva+$subtotalIva5;
$totales5=$totales5+$subtotalIva5;
$SubTotalestotalIva5=$SubTotalestotalIva5+$subtotal;
}
if($impuesto==1){
	$ivaexcentas= $subtotal;
$subtotalIvaext=$subtotal;
$totalesExt=$totalesExt+$subtotalIvaext;
}

$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventa(this)";

$monto_total= intval($subtotal) + intval($descuento);
if ($estado == 'eliminado') {
	$styleDetalle .= "text-decoration: line-through;";

	// iva recibo
	$iva10porciento= $monto_total;
	$iva5porciento= $monto_total;
	$ivaexcentas= $monto_total;
}

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='$eventos' style='$styleDetalle'  name='tdDetalleVenta'>
<td id='td_id_1' style='display:none'>".$cod_producto."</td>
<td id='td_id_2' style='display:none'>".$cod_detalle."</td>
<td  style='width:5%'>".$cod_barra."</td>
<td  id='td_datos_1' style='width:20%;".$styleG."'>".$nombre_producto." *".$NombreMarca."*</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_4' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_7' style='width:10%;display:none'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($monto_total,'0',',','.')."</td>
<td  id='td_datos_6' style='display:none'>".number_format($comision,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
</tr>
</table>";

$descripcionDetalleVenta=buscardescripcionDetalleVenta($cod_detalle);

$paginarecibo.="
<table class='tableReporRecibo' >
<tr style='$styleDetalle>
<td  style='width:35px'>".$cod_barra."</td>
<td  style='width:35px;text-aling:center'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  style='width:355px'>$nombre_producto * $NombreMarca * $descripcion <br> $descripcionDetalleVenta</td>
<td  style='width:75px'>".number_format($precio_producto,'0',',','.') ."</td>
<td  style='width:50px;text-aling:center'>".number_format($ivaexcentas,'0',',','.') ."</td>
<td  style='width:50px;text-aling:center'>".number_format($iva5porciento,'0',',','.') ."</td>
<td  style='width:65px;text-aling:center'>".number_format($iva10porciento,'0',',','.') ."</td>
</tr>
</table>";

if ($estado == 'eliminado') {
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' name='tdDetalleVenta'>
<td id='td_id_1' style='display:none'>".$cod_producto."</td>
<td id='td_id_2' style='display:none'>".$cod_detalle."</td>
<td  style='width:5%'>".$cod_barra."</td>
<td  id='td_datos_1' style='width:20%;".$styleG."'>Cancelacion por ".$nombre_producto."</td>
<td  id='td_datos_3' style='width:10%'>".number_format($subtotal,'0',',','.') ."</td>
<td  id='td_datos_4' style='width:5%'>1</td>
<td  id='td_datos_5' style='width:10%;display:none'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
<td  id='td_datos_6' style='display:none'>".number_format($comision,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
</tr>
</table>";

$iva10porciento= $subtotal;
	$iva5porciento= $subtotal;
	$ivaexcentas= $subtotal;

$paginarecibo.="
<table class='tableReporRecibo' >
<tr>
<td  style='width:35px'>".$cod_barra."</td>
<td  style='width:35px;text-aling:center'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  style='width:355px'> Cancelacion <br> $nombre_producto * $NombreMarca</td>
<td  style='width:75px'>".number_format($subtotal,'0',',','.') ."</td>
<td  style='width:50px;text-aling:center'>".number_format($ivaexcentas,'0',',','.') ."</td>
<td  style='width:50px;text-aling:center'>".number_format($iva5porciento,'0',',','.') ."</td>
<td  style='width:65px;text-aling:center'>".number_format($iva10porciento,'0',',','.') ."</td>
</tr>
</table>";
}

// $paginatickect.="<table class='tableTicket'>
// <tr>
// <td style='width:10%'>".number_format($cantidad_detalle,'0',',','.')."</td>
// <td style='width:50%'>".$nombre_producto." *".$NombreMarca."*</td>

// <td style='width:15%'>".number_format($precio_producto,'0',',','.')."</td>
// <td style='width:25%'>".number_format($subtotal,'0',',','.')."</td>
// </tr>
// </table>";
}
}

$paginatickect="Factura nro: ".$nroFactura;

$datos=buscardatoscuenta($buscar); 
$idcredito=$datos[0];    
$plazo=$datos[1];  
$fechapago=$datos[2];          
$cod_venta=$datos[3];    
$Monto=$datos[4]; 
$totalPago=$datos[5]; 
$Esado=$datos[6] ;          
$Nro_recibo=$datos[7] ;
$TipoPago=$datos[8];
$nroCuota=$datos[9];
$dias=$datos[10];
$interes=$datos[12] ;
$entrega=$datos[13] ;
$controlMonto=$datos[14] ;
$ultimafechapago=$datos[15] ;

$datos=calcularintereses2($cod_venta,0,0,"2","2","2","no");
$totalEnDescuento=$datos[0];
$totalInteres=$datos[12];
$deuda=$datos[4];
$diasatrasado=$datos[5];
$acobrar=$datos[8];
$totalCredito=$datos[11];
$totalDescuentosAplicado=$totalDescuentoDetalles+$totalEnDescuento;
if($totalCredito>0){
	$Subttotalventa=$totalCredito+$totalDescuentoDetalles;
	$totalventa=$totalCredito-$totalEnDescuento;
}else{
	$Subttotalventa=$totalventa+$totalDescuentoDetalles;
	$totalventa=$totalventa-$totalEnDescuento;
}

$plazoPago = buscarpagosTitulo($cod_venta);

$cuotas=buscarcantidadcuotapagados($cod_venta)."/".$nroCouta;
$informacion =array(
	"1" => "exito","2" => $pagina,
	"5" => $paginarecibo,
	"14" => $paginatickect,
	"3" => number_format($totalventa,'0',',','.'),
	"4" => number_format($totalpagado,'0',',','.')
,"6" => number_format($SubTotalestotalIva5,'0',',','.'),
"7" => number_format($SubTotalestotalIva10,'0',',','.'),
"8" => number_format($totales10,'0',',','.')
,"9" => number_format($totales5,'0',',','.'),
"10" =>$clientenombre ,
"11" => $clientedireccion ,
"12" => $clientetelefono ,
"13" => $nrodocliente
,"15" => $plazo ,
"16" => $fechapago ,
"23" => number_format($Monto,'0',',','.')  ,
"18" => $Nro_recibo ,
"19" => $nroCuota ,
"20" =>$dias ,
"21" => number_format($interes,'2',',','.')  ,
"22" => $TipoPago ,
"17" =>number_format($entrega,'0',',','.'),
"24"=>$controlMonto,
"25"=>$fac,
"26"=>$ultimafechapago,
"27"=>$zonaCliente,
"28"=>$Garantetelefono,
"29"=>$zonaGarante,
"30"=>number_format($totalInteres,'0',',','.'),
"31"=>number_format($deuda,'0',',','.'),
"32"=>$diasatrasado,
"33"=>$ruccliente,
"34"=> number_format($totalEnDescuento,'0',',','.'),
"35"=>$cuotas,
"36"=>number_format($totalesiva,'0',',','.'),
"37"=>number_format($totalDescuentosAplicado,'0',',','.'),
"38"=>number_format($Subttotalventa,'0',',','.'),
"39"=>$nrodocgarante,
"40"=>$TipoVenta ,
"41"=>$plazoPago[2] );
echo json_encode($informacion);	
exit;
}



function buscarpagosTitulo($CodVenta)
{
$mysqli=conectar_al_servidor();


$sql= "select cr.fechapago,cr.plazo,cr.Monto as montocredito,pg.idPago,pg.Fecha,pg.Monto,pg.nrofactura,pg.tipo,vt.TipoVenta,vt.total_venta
 from pago pg inner join venta vt on vt.cod_venta=pg.cod_venta_fk
 inner join credito cr on cr.idcredito=pg.cod_creditoFK
 where pg.cod_venta_fk='$CodVenta'  order by pg.idPago  ";
 
 // echo($sql);
 // exit;
 
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$pagina2 = ""; 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$totalPagado=0;
$datos[0]="";
$datos[1]="";
$datos[2]="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
     
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');      
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1');  
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');  
$Fecha = mb_convert_encoding((string)($valor['Fecha']), 'UTF-8', 'ISO-8859-1'); 
$fechapago = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');  

if($plazo=="Contado"){
	$tipo="";
}


if($tipo=="Interes"){
	$tipo="INTERES";
}
if($tipo=="Pago Cuota"){
	$tipo="PAGO DE CUOTA";
}

$totalPagado=$Monto+$totalPagado;
$pagina.="<table style='font-family: arial;font-size: 11px;' >
<tr>
<td style='width:10%'>".$plazo."</td>
<td style='width:50%'>".$tipo."</td>
<td style='width:40%'>".number_format($Monto,'0',',','.')."</td>
</tr>
</table>";

$pagina2.="<table class='tableTicket' style='border: solid 1px #a1a1a1;'>
<tr>
<td style='width:20%'>".$Fecha."</td>
<td style='width:20%'>".$fechapago."</td>
<td style='width:40%'>".$tipo."--".$plazo."</td>
<td style='width:20%'>".number_format($Monto,'0',',','.')."</td>
</tr>
</table>";

}

$pagina2.="<table class='tableTicket' style='border: solid 1px #a1a1a1;'>
<tr>
<td style='width:70%'></td>
<td style='width:30%'>TOTAL : ".number_format($totalPagado,'0',',','.')." Gs.</td>
</tr>
</table>";


}
$datos[0]=$pagina;
$datos[1]=$totalPagado;
$datos[2]=$pagina2;
return $datos;	

}




function buscardescripcionDetalleVenta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select nombre
 from  descripcionventa 
 where cod_detalleFK='$buscar' ";
 

$pagina="";
 
$cuotas = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlVentas="";



if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
$pagina.="<p style='font-size: 9px;'>$nombre</p>";

}
}
 mysqli_close($mysqli); 
return $pagina;

}




function buscarcantidadcuotapagados($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select count(vt.num_factura) as cuotas
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar'
 and  ((cr.Monto-cr.descuento)-IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito and pg.tipo='Pago Cuota'),0))<=0
 and plazo!='ENTREGA'";
 


 
$cuotas = "0";  
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlVentas="";



if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$cuotas = mb_convert_encoding((string)($valor['cuotas']), 'UTF-8', 'ISO-8859-1');

}
}
 mysqli_close($mysqli); 
return $cuotas;

}

function  productosCompradoscliente($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,vt.num_factura,vt.puntoexpedicion,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.cod_clienteFK limit 1) limit 1) as zonaCliente,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as clientetelefono,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.idGaranteFk limit 1) limit 1) as zonaGarante,
(Select telefono from persona where cod_persona=vt.idGaranteFk) as Garantetelefono,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
(Select rut_cliente from cliente where cod_cliente=vt.cod_clienteFK) as ruccliente,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";
$clientenombre = ""; 
$clientedireccion = ""; 
$clientetelefono = ""; 
$nrodocliente = ""; 
$zonaCliente = ""; 
$Garantetelefono = ""; 
$zonaGarante = ""; 

$pagina = "";   
$paginarecibo = "";      
$ruccliente = "";      
$paginatickect = "";      
$totalventa = "0";   
$totalpagado = "0";   
$nroFactura = "0";   
$nroVenta = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$SubTotalestotalIva10=0;
$SubTotalestotalIva5=0;
$totalIvaEx=0;

$totales10=0;
$totales5=0;
$totalesExt=0;
$plazo=1;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$impuesto = mb_convert_encoding((string)($valor['impuesto']), 'UTF-8', 'ISO-8859-1'); 
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1'); 
$clientedireccion = mb_convert_encoding((string)($valor['clientedireccion']), 'UTF-8', 'ISO-8859-1'); 
$clientetelefono = mb_convert_encoding((string)($valor['clientetelefono']), 'UTF-8', 'ISO-8859-1'); 
$nrodocliente = mb_convert_encoding((string)($valor['nrodocliente']), 'UTF-8', 'ISO-8859-1'); 
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$Garantetelefono = mb_convert_encoding((string)($valor['Garantetelefono']), 'UTF-8', 'ISO-8859-1'); 
$zonaGarante = mb_convert_encoding((string)($valor['zonaGarante']), 'UTF-8', 'ISO-8859-1'); 
$zonaCliente = mb_convert_encoding((string)($valor['zonaCliente']), 'UTF-8', 'ISO-8859-1'); 
$ruccliente = mb_convert_encoding((string)($valor['ruccliente']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 

if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}

$subtotalIva5=0;
$subtotalIva10=0;
$subtotalIvaext=0;
if($impuesto==11){
$subtotalIva10=($subtotal*($impuesto/100));
$totales10=$totales10+$subtotalIva10;
$subtotalIva10=$subtotal;
$SubTotalestotalIva10=$SubTotalestotalIva10+$subtotalIva10;
}
if($impuesto==21){
$subtotalIva5=($subtotal*($impuesto/100));
$totales5=$totales5+$subtotalIva5;
$subtotalIva5=$subtotal;
$SubTotalestotalIva5=$SubTotalestotalIva5+$subtotalIva5;

}
if($impuesto==1){
$subtotalIvaext=$subtotal;
$totalesExt=$totalesExt+$subtotalIvaext;
}


$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventa(this)";


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  class='$styleDetalle'  name='tdDetalleVenta'>
<td id='td_id_1' style='display:none'>".$cod_producto."</td>
<td id='td_id_2' style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_1' style='width:20%;".$styleG."'>".$nombre_producto." *".$NombreMarca."* </td>
<td  id='td_datos_4' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_3' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
</tr>
</table>";

}
}

$datocuenta=calcularintereses2($buscar,0,0,"2","2","2","no");
$totalInteres=$datocuenta[12];
$totalPagado=$datocuenta[3];
$acobrar=$datocuenta[4];
$deuda=$datocuenta[4];
$porinteres=$datocuenta[14];

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalventa,'0',',','.'),"4" => number_format($totalPagado,'0',',','.') ,"5" =>   number_format($acobrar,'0',',','.'),"6" => $nroFactura );
echo json_encode($informacion);	
exit;
}

function  productosCompradosclienteInactivo($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select vt.fecha_venta,pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,detalleproducto,vt.num_factura,vt.puntoexpedicion,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select direccion from persona where cod_persona=cod_clienteFK) as clientedireccion,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.cod_clienteFK limit 1) limit 1) as zonaCliente,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as clientetelefono,
(Select nombre from zona where idzona=(Select idzonaFk from cliente where cod_cliente=vt.idGaranteFk limit 1) limit 1) as zonaGarante,
(Select telefono from persona where cod_persona=vt.idGaranteFk) as Garantetelefono,
(Select ci_cliente from cliente where cod_cliente=vt.cod_clienteFK) as nrodocliente,
(Select rut_cliente from cliente where cod_cliente=vt.cod_clienteFK) as ruccliente,
IFNULL((select count(cr.plazo) from  credito cr where vt.cod_venta=cr.cod_venta),1) as plazo,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where vt.cod_clienteFK='$buscar' order by fecha_venta desc";
$pagina = "";    
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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



$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');  
$impuesto = mb_convert_encoding((string)($valor['impuesto']), 'UTF-8', 'ISO-8859-1'); 
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1'); 
$clientedireccion = mb_convert_encoding((string)($valor['clientedireccion']), 'UTF-8', 'ISO-8859-1'); 
$clientetelefono = mb_convert_encoding((string)($valor['clientetelefono']), 'UTF-8', 'ISO-8859-1'); 
$nrodocliente = mb_convert_encoding((string)($valor['nrodocliente']), 'UTF-8', 'ISO-8859-1'); 
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$Garantetelefono = mb_convert_encoding((string)($valor['Garantetelefono']), 'UTF-8', 'ISO-8859-1'); 
$zonaGarante = mb_convert_encoding((string)($valor['zonaGarante']), 'UTF-8', 'ISO-8859-1'); 
$zonaCliente = mb_convert_encoding((string)($valor['zonaCliente']), 'UTF-8', 'ISO-8859-1'); 
$ruccliente = mb_convert_encoding((string)($valor['ruccliente']), 'UTF-8', 'ISO-8859-1'); 
$fecha_venta = mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1'); 

if($puntoexpedicion!=""){
	$nroFactura=$puntoexpedicion."-".$num_factura;
}else{
	$nroFactura=$num_factura;
}



$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventa(this)";


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  class='$styleDetalle'  name='tdDetalleVenta'>
<td  id='td_datos_1' style='width:70%;".$styleG."'>".$nombre_producto."</td>
<td  id='td_datos_1' style='width:30%;'>".$fecha_venta."</td>
</tr>
</table>";

}
}


$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}

function  BuscarRegistroEnHistorilaVenta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,dtv.estado,detalleproducto,dtv.descuento,dtv.comision,vt.cod_venta,vt.TipoPago,vt.num_factura,vt.puntoexpedicion,vt.fecha_venta,
(Select telefono from persona where cod_persona=vt.cod_clienteFK) as telefono,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.subtotal,dtv.subPrecioCompra,
IFNULL((Select count(idgarantia) from garantias gt where gt.cod_detalle_venta_fk=dtv.cod_detalle and (gt.estado='Pendiente a verificar' or gt.estado='verificacion') limit 1),0) as nroGarantia,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
IFNULL((Select monto_impuesto from impuesto ipt where ipt.cod_Impuesto=pr.cod_ImpuestoFK and ipt.Estado='Activo' limit 1),1) as impuesto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where dtv.cod_ventaFK='$buscar'";
$pagina="";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalIva10=0;
$totalIva5=0;
$totalIvaEx=0;

$totales10=0;
$totales5=0;
$totalesExt=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$nroGarantia = mb_convert_encoding((string)($valor['nroGarantia']), 'UTF-8', 'ISO-8859-1'); 
$impuesto = mb_convert_encoding((string)($valor['impuesto']), 'UTF-8', 'ISO-8859-1'); 
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$cod_venta = mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1'); 
$TipoPago = mb_convert_encoding((string)($valor['TipoPago']), 'UTF-8', 'ISO-8859-1'); 
 $num_factura=mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
$puntoexpedicion=mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
$NombreMarca=mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1');
$fecha_venta=mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1');
$telefono=mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1');
		  	    if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

$subtotalIva5=0;
$subtotalIva10=0;
$subtotalIvaext=0;
if($impuesto==11){
$subtotalIva10=($subtotal*$impuesto)/100;
$subtotalIva10=$subtotal-$subtotalIva10;
$totalIva10=$totalIva10+$subtotalIva10;
$subtotalIva10=$subtotal;
$totales10=$totales10+$subtotalIva10;
}
if($impuesto==21){
$subtotalIva5=($subtotal*$impuesto)/100;
$subtotalIva5=$subtotal-$subtotalIva5;	
$totalIva5=$totalIva5+$subtotalIva5;
$subtotalIva5=$subtotal;
$totales5=$totales5+$subtotalIva5;
}
if($impuesto==1){
$subtotalIvaext=$subtotal;
$totalesExt=$totalesExt+$subtotalIvaext;
}


$styleG=""; 
$styleDetalle=""; 
$tituloext=""; 
$eventos="obtenerdatosabmdetalleventaDevoluciones(this)";
if($nroGarantia>0){
	$eventos="";
	$tituloext=" <BR> <b><i>(PROCESO DE GARANTIA)<i><b>";
}


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle'  name='tdDetalleVenta' onclick='$eventos' >
<td  id='td_datos_1' style='display:none'>".$cod_producto."</td>
<td  id='td_datos_2' style='display:none'>".$nombre_producto." *".$NombreMarca."*</td>
<td   style='width:20%;".$styleG."'>".$nombre_producto." *".$NombreMarca."*".$tituloext."</td>
<td  id='td_datos_3' style='display:none'>".$detalleproducto."</td>
<td  id='td_datos_4' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_5' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_6' style='width:10%'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_7' style='width:10%'>".number_format($subtotal,'0',',','.')."</td>
<td  id='td_datos_8' style='display:none'>".$comision."</td>
<td  id='td_datos_9' style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_10' style='display:none'>".$cod_venta."</td>
<td  id='td_datos_11' style='display:none'>".$TipoPago."</td>
<td  id='td_datos_12' style='display:none'>".$nrof."</td>
<td  id='td_datos_13' style='display:none'>".$fecha_venta."</td>
<td  id='td_datos_14' style='display:none'>".$telefono."</td>
</tr>
</table>";




}
}


$informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;
}


function buscardatoscuenta($buscar)
{
	

$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$sql= "select cr.plazo,cr.fechapago,cr.cod_venta,cr.Monto,cr.idcredito,cr.Esado,cr.Nro_recibo,vt.TipoPago,dias,cr.descuento,cr.interes,cr.tipo,
(select fechapago from credito cr where cr.cod_venta='$buscar' order by  fechapago desc limit 1) as ultimaFechaPago
 from  credito cr inner join venta vt on vt.cod_venta=cr.cod_venta
 where vt.cod_venta='$buscar' order by  fechapago ";
 
$datos;
$idcredito = "";    
$plazo = "";  
$fechapago = "";          
$cod_venta ="";          
$MontoControl = "0"; 
$controlMonto = 0; 
$Monto = "0"; 
$totalPago = "0"; 
$Esado = "";          
$Nro_recibo = "";
$TipoPago ="";
$nroCuota ="0";
$dias ="10";
$interes ="0.10";
$descuento ="0";
$entrega ="0";
$ultimaFechaPago ="0";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$nroCuotas=0;
$controlStyle="";
if ($valor>0)
{
	$nroCuota=0;
while ($valor= mysqli_fetch_assoc($result))
{  

$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'); 
$ultimaFechaPago = mb_convert_encoding((string)($valor['ultimaFechaPago']), 'UTF-8', 'ISO-8859-1');   
if($tipo!="ENTREGA"){
	$nroCuota++;
if($fechapago==""){
    
$idcredito = mb_convert_encoding((string)($valor['idcredito']), 'UTF-8', 'ISO-8859-1');     
$plazo = mb_convert_encoding((string)($valor['plazo']), 'UTF-8', 'ISO-8859-1');  
$fechapago = mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');      
   
$cod_venta = mb_convert_encoding((string)($valor['cod_venta']), 'UTF-8', 'ISO-8859-1');          
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 
$Esado = mb_convert_encoding((string)($valor['Esado']), 'UTF-8', 'ISO-8859-1');          
$Nro_recibo = mb_convert_encoding((string)($valor['Nro_recibo']), 'UTF-8', 'ISO-8859-1');
$TipoPago = mb_convert_encoding((string)($valor['TipoPago']), 'UTF-8', 'ISO-8859-1');
$dias = mb_convert_encoding((string)($valor['dias']), 'UTF-8', 'ISO-8859-1');
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1');
$interes = mb_convert_encoding((string)($valor['interes']), 'UTF-8', 'ISO-8859-1');
}
$nm = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 

if($MontoControl!=$nm){
	$MontoControl=$nm;
	$controlMonto=$controlMonto+1;
}
 
}else{
	$entrega = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');
}

$nroCuotas=$nroCuotas+1;
}
}

 mysqli_close($mysqli);
$datos[0]=$idcredito;    
$datos[1]=$nroCuotas;  
$datos[2]=$fechapago;          
$datos[3]=$cod_venta;          
$datos[4]=$Monto; 
$datos[5]=$totalPago ; 
$datos[6]=$Esado ;          
$datos[7]=$Nro_recibo ;
$datos[8]=$TipoPago;
$datos[9]=$nroCuota;
$datos[10]=$dias;
$datos[11]=$descuento ;
$datos[12]=$interes ;
$datos[13]=$entrega ;
$datos[14]=$controlMonto ;
$datos[15]=$ultimaFechaPago ;
return $datos;


}

function  buscarproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$agrupacionproductovendidoinforme)
{
$mysqli=conectar_al_servidor();
	 $condicionfecha="and vt.fecha_venta>='".$fecha1."' and vt.fecha_venta<='".$fecha2."'";
		 if($fecha1=="" && $fecha2==""){
			$condicionfecha=" "; 
		 }
		 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		 $condicionCategoria=" and pr.cod_categoriaFK='$categoria' ";
		 if($categoria==""){
			$condicionCategoria=""; 
		 }
		 $condicionMarca=" and pr.cod_marcasFK='$marca' ";
		 if($marca==""){
			$condicionMarca=""; 
		 }
		 $condicioncodigo=" and pr.cod_barra='$codigo' ";
		 if($codigo==""){
			$condicioncodigo=""; 
		 }
		 $condicionproducto="and concat(pr.nombre_producto,' ',pr.cod_producto) like '%".$producto."%' ";
		 if($producto==""){
			$condicionproducto=""; 
		 }
    
	$condiciongroupby="";
	if($agrupacionproductovendidoinforme=="1"){
		$condiciongroupby=" group by pr.cod_producto ";
	}
	if($agrupacionproductovendidoinforme=="2"){
		$condiciongroupby= " group by dtv.cod_detalle  ";
	}
		
$sql= "select pr.cod_barra,pr.nombre_producto,concat(puntoexpedicion,'-',num_factura) as nroventa,
sum(dtv.cantidad_detalle) as totalCantidad,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
sum(dtv.subtotal) as totalVenta,
sum(dtv.cantidad_detalle*dtv.subPrecioCompra) as totalCosto,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where  IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
 ".$condicionfecha.$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicioncodigo.$condicionproducto.$condiciongroupby." 
 order by totalCantidad desc limit 50";



$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$totalventas = "0";   
$totalinvertido = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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



$cod_producto = mb_convert_encoding((string)($valor['cod_barra']), 'UTF-8', 'ISO-8859-1');
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$totalCantidad = mb_convert_encoding((string)($valor['totalCantidad']), 'UTF-8', 'ISO-8859-1');          
$totalVenta = mb_convert_encoding((string)($valor['totalVenta']), 'UTF-8', 'ISO-8859-1'); 
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'); 
$totalCosto = mb_convert_encoding((string)($valor['totalCosto']), 'UTF-8', 'ISO-8859-1'); 
$NombreCategoria = mb_convert_encoding((string)($valor['NombreCategoria']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 
$nroventa = mb_convert_encoding((string)($valor['nroventa']), 'UTF-8', 'ISO-8859-1'); 

$totalventas=$totalVenta+$totalventas;
$totalinvertido=$totalinvertido+$totalCosto;
$nroventas="";
if($agrupacionproductovendidoinforme=="2"){
		$nroventas="<br><b><i>".$nroventa."</i></b>";
	}


	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'   >
<td id='' style='width:10%'>".$cod_producto."</td>
<td id='' style='width:20%'>".$nombre_producto.$nroventas."</td>
<td id='' style='width:10%'>".$NombreMarca."</td>
<td id='' style='width:10%'>".$NombreCategoria."</td>
<td  id='' style='width:10%'>".number_format($totalCantidad,'2',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($totalVenta,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


}
}

		
$sql= "select pr.cod_barra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where  IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
 ".$condicionfecha.$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicioncodigo.$condicionproducto.$condiciongroupby; 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregisto=$valor;

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5" => number_format($totalinvertido,'0',',','.'),"99"=>$nroRegistro,"100"=>$totalregisto);
echo json_encode($informacion);	
exit;
}

function  buscarmasproductovendidos($codigo,$producto,$fecha1,$fecha2,$cod_local,$categoria,$marca,$totalventa,$totalinvertido,$registrocargado,$agrupacionproductovendidoinforme)
{
$mysqli=conectar_al_servidor();
	 $condicionfecha="and vt.fecha_venta>='".$fecha1."' and vt.fecha_venta<='".$fecha2."'";
		 if($fecha1=="" && $fecha2==""){
			$condicionfecha=" "; 
		 }
		 $condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
		 $condicionCategoria=" and pr.cod_categoriaFK='$categoria' ";
		 if($categoria==""){
			$condicionCategoria=""; 
		 }
		 $condicionMarca=" and pr.cod_marcasFK='$marca' ";
		 if($marca==""){
			$condicionMarca=""; 
		 }
		 $condicioncodigo=" and pr.cod_barra='$codigo' ";
		 if($codigo==""){
			$condicioncodigo=""; 
		 }
		 $condicionproducto="and concat(pr.nombre_producto,' ',pr.cod_producto) like '%".$producto."%' ";
		 if($producto==""){
			$condicionproducto=""; 
		 }

$condiciongroupby="";
	if($agrupacionproductovendidoinforme=="1"){
		$condiciongroupby=" group by pr.cod_producto ";
	}
	if($agrupacionproductovendidoinforme=="2"){
		$condiciongroupby= " group by dtv.cod_detalle  ";
	}
		
$sql= "select pr.cod_barra,pr.nombre_producto,concat(puntoexpedicion,'-',num_factura) as nroventa,
sum(dtv.cantidad_detalle) as totalCantidad,
(select descripcion from categoria where cod_categoria= pr.cod_categoriaFK limit 1 ) as NombreCategoria,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca,
sum(dtv.subtotal) as totalVenta,
sum(dtv.cantidad_detalle*dtv.subPrecioCompra) as totalCosto,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK 
where  IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Devolucion' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Cambio' limit 1),0)=0
and IFNULL((Select count(fecha) from cambios where coddetalleventa=dtv.cod_detalle and motivo='Garantia' limit 1),0)=0
and  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 
 ".$condicionfecha.$condicionCodLocal.$condicionCategoria.$condicionMarca.$condicioncodigo.$condicionproducto.$condiciongroupby." order by totalCantidad desc limit ".$registrocargado.", 50 ";
 



$pagina = "";   

$totalpagado = "0";   
$totalventas = $totalventa;   
$totalinvertido = $totalinvertido;   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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



$cod_producto = mb_convert_encoding((string)($valor['cod_barra']), 'UTF-8', 'ISO-8859-1');
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$totalCantidad = mb_convert_encoding((string)($valor['totalCantidad']), 'UTF-8', 'ISO-8859-1');          
$totalVenta = mb_convert_encoding((string)($valor['totalVenta']), 'UTF-8', 'ISO-8859-1'); 
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'); 
$totalCosto = mb_convert_encoding((string)($valor['totalCosto']), 'UTF-8', 'ISO-8859-1'); 
$NombreCategoria = mb_convert_encoding((string)($valor['NombreCategoria']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 
$nroventa = mb_convert_encoding((string)($valor['nroventa']), 'UTF-8', 'ISO-8859-1'); 

$totalventas=$totalVenta+$totalventas;
$totalinvertido=$totalinvertido+$totalCosto;
$nroventas="";
if($agrupacionproductovendidoinforme=="2"){
		$nroventas="<br><b><i>".$nroventa."</i></b>";
	}
	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'   >
<td id='' style='width:10%'>".$cod_producto."</td>
<td id='' style='width:20%'>".$nombre_producto.$nroventas."</td>
<td id='' style='width:10%'>".$NombreMarca."</td>
<td id='' style='width:10%'>".$NombreCategoria."</td>
<td  id='' style='width:10%'>".number_format($totalCantidad,'2',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($totalVenta,'0',',','.')."</td>
<td  id='' style='width:10%'>".$nombrelocal."</td>
</tr>
</table>";


}
}

  
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5" => number_format($totalinvertido,'0',',','.'),"99"=>$nroRegistro);
echo json_encode($informacion);	
exit;
}


function  BuscarRegistroDevolucion($buscar,$cod_local)
{
$mysqli=conectar_al_servidor();
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
		 if($cod_local==""){
			$condicionCodLocal=" "; 
		 }
$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,dtv.detalleproducto,vt.total_venta,dtv.comision,vt.puntoexpedicion,vt.num_factura,vt.fecha_venta,vt.TipoPago,dtv.estado,IFNULL(dtv.descuento,0) as descuento,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select fechapago from credito where cod_venta=vt.cod_venta order by fechapago asc limit 1) as fechaprimerpago,
IFNULL((Select Monto from credito where cod_venta=vt.cod_venta  limit 1),0) as Monto,
(Select count(fechapago) from credito where cod_venta=vt.cod_venta) as cantidadcuota,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
 where  IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0  and concat(pr.cod_producto,' ',pr.nombre_producto,' ',vt.num_factura,' ',(Select telefono from persona where cod_persona=cod_clienteFK),' ',(Select nombre_persona from persona where cod_persona=cod_clienteFK),' ',(Select ci_cliente from cliente where cod_cliente=cod_clienteFK)) like '%".$buscar."%' ".$condicionCodLocal." 
 order by vt.cod_venta desc limit 500";

$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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



$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');   
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$fecha_venta = mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1'); 
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1'); 
$cantidadcuota = mb_convert_encoding((string)($valor['cantidadcuota']), 'UTF-8', 'ISO-8859-1'); 
$fechaprimerpago = mb_convert_encoding((string)($valor['fechaprimerpago']), 'UTF-8', 'ISO-8859-1'); 
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$TipoPago = mb_convert_encoding((string)($valor['TipoPago']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1'); 
$styleG=""; 
$styleDetalle=""; 
$eventos="obtenerdatosabmdetalleventaDevoluciones(this)";

  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle' onclick='$eventos'>
<td id='td_datos_9'  style='width:10%'>".$clientenombre."</td>
<td id='td_datos_18'  style='width:10%'>".$nrof."</td>
<td id='td_datos_1'  style='display:none'>".$num_factura."</td>
<td id='td_datos_2'  style='width:10%'>".$fecha_venta."</td>
<td id='td_datos_3'  style='display:none'>".$cod_producto."</td>
<td id='td_datos_4'  style='display:none'>".$cod_detalle."</td>
<td  id='td_datos_5' style='width:20%;".$styleG."'>".$nombre_producto."</td>
<td  id='td_datos_6' style='width:5%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='td_datos_7' style='width:5%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='td_datos_20' style='width:5%'>".number_format($descuento,'0',',','.')."</td>
<td  id='td_datos_8' style='width:5%'>".number_format($subtotal,'0',',','.')."</td>
<td  id='td_datos_10' style='display:none'>".$comision."</td>
<td  id='td_datos_11' style='display:none'>".number_format($totalpagado,'0',',','.')."</td>
<td  id='td_datos_12' style='display:none'>".number_format($totalventa,'0',',','.')."</td>
<td  id='td_datos_15' style='display:none'>".number_format($Monto,'0',',','.')."</td>
<td  id='td_datos_13' style='display:none'>".$cantidadcuota."</td>
<td  id='td_datos_14' style='display:none'>".$fechaprimerpago."</td>
<td  id='td_datos_16' style='display:none'>".$TipoPago."</td>
<td  id='td_datos_17' style='display:none'>".$cod_ventaFK."</td>
<td  id='td_datos_19' style='display:none'>".$detalleproducto."</td>
</tr>
</table>";


}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalventa,'0',',','.'),"4" => number_format($totalpagado,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function  buscarexpedientes($cliente)
{
$mysqli=conectar_al_servidor();

$sql= "select vt.puntoexpedicion,vt.num_factura,pr.cod_producto,pr.nombre_producto,dtv.descuento,dtv.cod_detalle,vt.total_venta,IFNULL(dtv.comision,0) as comision,dtv.estado,dtv.detalleproducto,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,
(select descripcion from marcas where cod_marcas= pr.cod_marcasFK limit 1 ) as NombreMarca
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where vt.cod_clienteFK='$cliente'";
$controlVentas="";
$pagina = "";   
$totalventa = "0";   
$totalpagado = "0";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
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



$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1'); 
$descuento = mb_convert_encoding((string)($valor['descuento']), 'UTF-8', 'ISO-8859-1'); 
$NombreMarca = mb_convert_encoding((string)($valor['NombreMarca']), 'UTF-8', 'ISO-8859-1'); 
$styleG=""; 
$styleDetalle=""; 



$tituloPagos="";
if($controlVentas!=$cod_ventaFK){
	$tituloPagos="<p class='ptituloZ'>Nro de Factura: ".$num_factura."</p>";
	$controlVentas=$cod_ventaFK;
}

  if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}


$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'  class='$styleDetalle' >
<td id=''  style='width:10%'>".$nrof."</td>
<td  id='' style='width:20%'>".$nombre_producto." *".$NombreMarca."*</td>
<td  id='' style='width:10%'>".number_format($cantidad_detalle,'2',',','.')."</td>
<td  id='' style='width:10%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($descuento,'0',',','.') ."</td>
<td  id='' style='width:10%'>".number_format($subtotal,'0',',','.') ."</td>
</tr>
</table>";


}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function comisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$Descuento,$Flete,$cliente,$Local,$producto)
{
$mysqli=conectar_al_servidor();

$condicionfecha="";
if($fecha1 !="" && $fecha2 !=""){
$condicionfecha=" and vt.fecha_venta>='$fecha1' and vt.fecha_venta<='$fecha2' ";	
}
$condicionfechafiltro="";
if($fechafiltro !="" ){
$condicionfechafiltro=" and vt.fecha_venta='$fechafiltro' ";	
}
$condicionfechaVendedor="";
if($vendedor !="" ){
$condicionfechaVendedor=" and (Vendedor1='$vendedor' or Vendedor2='$vendedor')";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%' ";
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) like '%".$cliente."%' ";
}

$condicionLocal="";
if($Local!=""){
	$condicionLocal=" and vt.cod_local like '%".$Local."%' ";
}


$condicionDescuento="";
if($Descuento==""){
	$condicionDescuento=" and pr.cod_producto != '13603' ";
}

$condicionFlete="";
if($Flete==""){
	$condicionFlete=" and pr.cod_producto != '13753' ";
}


$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.puntoexpedicion,vt.total_venta,dtv.comision,vt.num_factura,vt.fecha_venta,vt.Vendedor1,vt.Vendedor2,dtv.estado,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as Cliente,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr 
 inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where  dtv.cod_detalle!='0' and IFNULL((Select count(fecha) from cancelaciones cl where cl.cod_venta=vt.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionfechafiltro.$condicionfechaVendedor.$condicionproducto.$condicionDescuento.$condicionFlete.$condicioncliente.$condicionLocal." group by dtv.cod_detalle limit 100";

$pagina = "";   
$totalacobrar = "0";   
$totalventas = "0";   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$registrocargados=0;
$styleName="tableRegistroSearch";
$acobrar="";
$styleDetalle=""; 
$styleG=""; 
$TotalDescuento="0";


if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$Cliente = mb_convert_encoding((string)($valor['Cliente']), 'UTF-8', 'ISO-8859-1');
$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$fecha_venta = mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1'); 
$Vendedor1 = mb_convert_encoding((string)($valor['Vendedor1']), 'UTF-8', 'ISO-8859-1'); 
$Vendedor2 = mb_convert_encoding((string)($valor['Vendedor2']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
$nombrevendedor1 = mb_convert_encoding((string)($valor['nombrevendedor1']), 'UTF-8', 'ISO-8859-1');   
$nombrevendedor2 = mb_convert_encoding((string)($valor['nombrevendedor2']), 'UTF-8', 'ISO-8859-1');  
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');  
$vendedores=$nombrevendedor1;
$vendedores.="<br>".$nombrevendedor2;
$totalventa=$precio_producto*$cantidad_detalle;


if($comision>0){


$comisionmonto=($subtotal*$comision)/100;
$styleG=""; 
$styleDetalle=""; 


$controlComision=0;
if($Vendedor1!=""){
$controlComision=$controlComision+1;	
}
if($Vendedor2!=""){
$controlComision=$controlComision+2;	
}
if($controlComision==0){
$controlComision=1;
}
$totalVentaDetalle=$precio_producto*$cantidad_detalle;
$acobrar=$comisionmonto/$controlComision;

}
$totalventas=$totalventas+$totalventa;
$totalacobrar=$totalacobrar+$acobrar;
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle' >
<td  id='' style='width:15%'>".$vendedores."</td>
<td  id='' style='width:12%'>".$nrof."</td>
<td  id='' style='width:23%'>".$Cliente."</td>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td  id='' style='width:15%;".$styleG."'>".$nombre_producto."</td>
<td  id='' style='width:7%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='' style='width:7%'>".number_format($cantidad_detalle,'0',',','.')." </td>
<td  id='' style='width:11%'>".$nombrelocal." </td>
</tr>
</table>";
$registrocargados=$registrocargados+1;


if($cod_producto=="13603"){
	$TotalDescuento = $TotalDescuento + $precio_producto ;
}

}
}

$sql= "select pr.cod_producto
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where  dtv.cod_detalle!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionfechafiltro.$condicionfechaVendedor.$condicionproducto.$condicionDescuento.$condicionFlete.$condicioncliente.$condicionLocal." group by dtv.cod_detalle "; 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistros=$valor;

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalacobrar,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5"=>$nroRegistro,"99"=>$registrocargados,"100"=>$totalregistros,"101"=> number_format($TotalDescuento,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function mascomisionvendedor($fecha1,$fecha2,$vendedor,$fechafiltro,$registrocargado,$totalcomision,$totalventa,$registroscargados,$Descuento,$Flete,$producto,$totalDescuento,$cliente,$Local)
{
$mysqli=conectar_al_servidor();

$condicionfecha="";
if($fecha1 !="" && $fecha2 !=""){
$condicionfecha=" and vt.fecha_venta>='$fecha1' and vt.fecha_venta<='$fecha2' ";	
}
$condicionfechafiltro="";
if($fechafiltro !="" ){
$condicionfechafiltro=" and vt.fecha_venta='$fechafiltro' ";	
}
$condicionfechaVendedor="";
if($vendedor !="" ){
$condicionfechaVendedor=" and (Vendedor1='$vendedor' or Vendedor2='$vendedor')";
}
$condicionproducto="";
if($producto!=""){
	$condicionproducto=" and pr.nombre_producto like '%".$producto."%' ";
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) like '%".$cliente."%' ";
}
$condicionLocal="";
if($Local!=""){
	$condicionLocal=" and vt.cod_local like '%".$Local."%' ";
}

$condicionDescuento="";
if($Descuento==""){
	$condicionDescuento=" and pr.cod_producto != '13603' ";
}

$condicionFlete="";
if($Flete==""){
	$condicionFlete=" and pr.cod_producto != '13753' ";
}

$sql= "select pr.cod_producto,pr.nombre_producto,dtv.cod_detalle,vt.puntoexpedicion,vt.total_venta,dtv.comision,vt.num_factura,vt.fecha_venta,vt.Vendedor1,vt.Vendedor2,dtv.estado,
IFNULL((select sum(pg.Monto) from pago pg  where vt.cod_venta=pg.cod_venta_fk),0) as totalpagado,
(Select nombre from vendedor where idvendedor=Vendedor1) as nombrevendedor1,
(Select nombre from vendedor where idvendedor=Vendedor2) as nombrevendedor2,
(Select nombre_persona from persona where cod_persona=vt.cod_clienteFK) as Cliente,
(Select Nombre from local l where l.cod_local=vt.cod_local) as nombrelocal,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra
 from  producto pr inner join detalle_venta dtv on dtv.cod_productoFK=pr.cod_producto
 inner join venta vt on vt.cod_venta=dtv.cod_ventaFK
where  dtv.cod_detalle!='0' and IFNULL((Select count(fecha) from cancelaciones where cod_venta=vt.cod_venta limit 1),0)=0 ".$condicionfecha.$condicionfechafiltro.$condicionfechaVendedor.$condicionproducto.$condicionDescuento.$condicionFlete.$condicioncliente.$condicionLocal." group by dtv.cod_detalle limit ".$registrocargado." , 100 ";

$pagina = "";   
$acobrar="";
$styleDetalle=""; 
$styleG=""; 

$totalacobrar =$totalcomision;   
$totalventas = $totalventa;   
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor+$registrocargado;
$registrocargados=$registroscargados;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$Cliente = mb_convert_encoding((string)($valor['Cliente']), 'UTF-8', 'ISO-8859-1');
$cod_producto = mb_convert_encoding((string)($valor['cod_producto']), 'UTF-8', 'ISO-8859-1');
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$cod_detalle = mb_convert_encoding((string)($valor['cod_detalle']), 'UTF-8', 'ISO-8859-1');          
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$precio_producto = mb_convert_encoding((string)($valor['precio_producto']), 'UTF-8', 'ISO-8859-1'); 
$cod_ventaFK = mb_convert_encoding((string)($valor['cod_ventaFK']), 'UTF-8', 'ISO-8859-1'); 
$subPrecioCompra = mb_convert_encoding((string)($valor['subPrecioCompra']), 'UTF-8', 'ISO-8859-1'); 
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'UTF-8', 'ISO-8859-1'); 
$totalventa = mb_convert_encoding((string)($valor['total_venta']), 'UTF-8', 'ISO-8859-1'); 
$totalpagado = mb_convert_encoding((string)($valor['totalpagado']), 'UTF-8', 'ISO-8859-1'); 
$comision = mb_convert_encoding((string)($valor['comision']), 'UTF-8', 'ISO-8859-1'); 
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1'); 
$fecha_venta = mb_convert_encoding((string)($valor['fecha_venta']), 'UTF-8', 'ISO-8859-1'); 
$Vendedor1 = mb_convert_encoding((string)($valor['Vendedor1']), 'UTF-8', 'ISO-8859-1'); 
$Vendedor2 = mb_convert_encoding((string)($valor['Vendedor2']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');   
$nombrevendedor1 = mb_convert_encoding((string)($valor['nombrevendedor1']), 'UTF-8', 'ISO-8859-1');   
$nombrevendedor2 = mb_convert_encoding((string)($valor['nombrevendedor2']), 'UTF-8', 'ISO-8859-1');  
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');  
$totalventa=$precio_producto*$cantidad_detalle;
$vendedores=$nombrevendedor1;
$vendedores.="<br>".$nombrevendedor2;
if($comision>0){


$comisionmonto=($subtotal*$comision)/100;
$styleG=""; 
$styleDetalle=""; 


$controlComision=0;
if($Vendedor1!=""){
$controlComision=$controlComision+1;	
}
if($Vendedor2!=""){
$controlComision=$controlComision+2;	
}
if($controlComision==0){
$controlComision=1;
}
$totalVentaDetalle=$precio_producto*$cantidad_detalle;
$acobrar=$comisionmonto/$controlComision;

}
$totalventas=$totalventas+$totalventa;
$totalacobrar=$totalacobrar+$acobrar;
			
			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}
	  $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' class='$styleDetalle' >
<td  id='' style='width:15%'>".$vendedores."</td>
<td  id='' style='width:12%'>".$nrof."</td>
<td  id='' style='width:23%'>".$Cliente."</td>
<td  id='' style='width:10%'>".$fecha_venta."</td>
<td  id='' style='width:15%;".$styleG."'>".$nombre_producto."</td>
<td  id='' style='width:7%'>".number_format($precio_producto,'0',',','.') ."</td>
<td  id='' style='width:7%'>".number_format($cantidad_detalle,'0',',','.')." </td>
<td  id='' style='width:11%'>".$nombrelocal." </td>
</tr>
</table>";
$registrocargados=$registrocargados+1;

if($cod_producto=="13603"){
	$totalDescuento = $totalDescuento + $precio_producto ;
}
}
}

/*Retornamos los datos obtenidos mediante el JSON */      
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($totalacobrar,'0',',','.'),"4" => number_format($totalventas,'0',',','.'),"5"=>$registrocargados,"99"=>$nroRegistro , "101"=>number_format($totalDescuento,'0',',','.'));
echo json_encode($informacion);	
exit;
}

function buscarnroventab()
{
	
	
	$mysqli=conectar_al_servidor();
	 $sql= "Select count(cod_venta) from venta ";
   $stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$NroVenta=$result->fetch_row();
  $NroVenta=$NroVenta[0];
  $NroVenta=$NroVenta;
 if($NroVenta<10){
	 $NroVenta="0000".$NroVenta;
 }else{
 if($NroVenta<100){
	 $NroVenta="000".$NroVenta;
 }else{
	 if($NroVenta<1000){
	 $NroVenta="00".$NroVenta;
    } 
 }
 }
  mysqli_close($mysqli); 
 return $NroVenta;

}

function buscarHistorialGarantia($nrofactura,$cod_local,$documento,$cliente,$estado)
{
$mysqli=conectar_al_servidor();
$fechahoy=date('Y-m-d');	
$condicionestado="";
if($estado!=""){
$condicionestado=" and gt.estado='$estado' ";
}
$condicionnrofactura="";
if($nrofactura!=""){
$condicionnrofactura=" and vt.num_factura like '%".$nrofactura."%' ";
}
$condicionCodLocal=" "; 
if($cod_local!=""){
$condicionCodLocal=" and vt.cod_local='$cod_local' ";
}
$condiciondocumento="";
if($documento!=""){
$condiciondocumento=" and (Select ci_cliente from cliente where cod_cliente=cod_clienteFK ) = '".$documento."' ";
}
$condicioncliente="";
if($cliente!=""){
$condicioncliente=" and (Select nombre_persona from persona where cod_persona=cod_clienteFK) like '%".$cliente."%' ";
}

$sql= "select gt.idgarantia,gt.fecharecibido,gt.fechaenvio,gt.fechaentrega,gt.fechadevuelto,gt.observacion,gt.estado,gt.cod_productoFK,gt.cod_ventaFK,
pr.cod_producto,pr.nombre_producto,vt.puntoexpedicion,vt.num_factura,
(Select ci_cliente from cliente where cod_cliente=cod_clienteFK ) as nrodocliente,
 telefonoaviso as telefono,
(Select nombre_persona from persona where cod_persona=cod_clienteFK) as clientenombre,
(Select nombre_persona from persona where cod_persona=cod_usuarioFKRecibido) as usuariorecibidopor,
(Select nombre_persona from persona where cod_persona=cod_usuarioFkEnvio) as usuarioenviado,
(Select nombre_persona from persona where cod_persona=cod_usuarioFkDevuelto) as usuariolisto,
(Select nombre_persona from persona where cod_persona=cod_usuarioFkEntrega) as usuarioentrega
from garantias gt inner join venta vt on vt.cod_venta=gt.cod_ventaFK 
inner join detalle_venta dtv on dtv.cod_ventaFK=vt.cod_venta
inner join producto pr on dtv.cod_productoFK=pr.cod_producto 
where gt.idgarantia!='' ".$condicionestado.$condicionnrofactura.$condicionCodLocal.$condiciondocumento.$condicioncliente." group by gt.idgarantia  order by gt.idgarantia desc";
 
$pagina="";

$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$controlStyle="";
$controlVentas="";
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  


$idgarantia = mb_convert_encoding((string)($valor['idgarantia']), 'UTF-8', 'ISO-8859-1'); 
$fecharecibido = mb_convert_encoding((string)($valor['fecharecibido']), 'UTF-8', 'ISO-8859-1');  
$fechaenvio = mb_convert_encoding((string)($valor['fechaenvio']), 'UTF-8', 'ISO-8859-1');          
$fechadevuelto = mb_convert_encoding((string)($valor['fechadevuelto']), 'UTF-8', 'ISO-8859-1');          
$fechaentrega = mb_convert_encoding((string)($valor['fechaentrega']), 'UTF-8', 'ISO-8859-1');          
$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1');          
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$estadox = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$cod_productoFK = mb_convert_encoding((string)($valor['cod_productoFK']), 'UTF-8', 'ISO-8859-1'); 
$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');          
$puntoexpedicion = mb_convert_encoding((string)($valor['puntoexpedicion']), 'UTF-8', 'ISO-8859-1');
$num_factura = mb_convert_encoding((string)($valor['num_factura']), 'UTF-8', 'ISO-8859-1');
$nrodocliente = mb_convert_encoding((string)($valor['nrodocliente']), 'UTF-8', 'ISO-8859-1');
$clientenombre = mb_convert_encoding((string)($valor['clientenombre']), 'UTF-8', 'ISO-8859-1');
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1');
$usuariorecibidopor = mb_convert_encoding((string)($valor['usuariorecibidopor']), 'UTF-8', 'ISO-8859-1');
$usuarioenviado = mb_convert_encoding((string)($valor['usuarioenviado']), 'UTF-8', 'ISO-8859-1');
$usuariolisto = mb_convert_encoding((string)($valor['usuariolisto']), 'UTF-8', 'ISO-8859-1');
$usuarioentrega = mb_convert_encoding((string)($valor['usuarioentrega']), 'UTF-8', 'ISO-8859-1');


if($estado=="Pendiente a verificar"){
	$estado="PENDIENTE A VERIFICAR";
}
if($estado=="verificacion"){
	$estado="EN VERIFICACION";
}
if($estado=="entregado"){
		$estado="ENTREGADO";
}
if($estado=="listo"){
		$estado="LISTO PARA ENTREGAR";
}

$tituloUsuarios="
Cargado: ".$usuariorecibidopor."
<br>
A verificacion: ".$usuarioenviado."
<br>
Listo para entregar: ".$usuariolisto."
<br>
Entregado por : ".$usuarioentrega;

$tituloFechas="
Cargado : ".$fecharecibido."
<br>
A verificacion : ".$fechaenvio."
<br>
Listo para entregar : ".$fechadevuelto."
<br>
Entregado por : ".$fechaentrega;


			   if($puntoexpedicion!=""){
	$nrof=$puntoexpedicion."-".$num_factura;
}else{
	$nrof=$num_factura;
}

$styleName=CargarStyleTable($styleName);
$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistaproductosgarantia(this)' >
<td id='td_id_1' style='display:none' >".$idgarantia."</td>
<td id='td_datos_1' style='width:10%' >".$nrof."</td>
<td id='td_datos_3' style='width:10%' >".$nrodocliente."</td>
<td id='td_datos_4' style='width:10%' >".$clientenombre."</td>
<td id='td_datos_4' style='width:10%' >".$telefono."</td>
<td id='td_datos_5' style='width:10%' >".$nombre_producto."</td>
<td id='td_datos_6' style='width:10%' >".$observacion."</td>
<td id='' style='width:10%' >".$estado."</td>
<td id='td_datos_7' style='width:10%' >".$tituloFechas."</td>
<td id='td_datos_10' style='width:10%' >".$tituloUsuarios."</td>
<td id='td_datos_9' style='display:none' >".$estadox."</td>
</tr>
</table>
";



}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($nroRegistro,'0',',','.'));
echo json_encode($informacion);	
exit;


}

function verificar_fecha_expiracion($fecha)
{ 


	$fecha=date_create($fecha);
	$fecha=date_format($fecha,"Y-m-d H:i:s");
	$fecha = strtotime($fecha);

	$fecha_2 = date('Y-m-d H:i:s');
 
$fecha_2=strtotime($fecha_2);

 if($fecha_2>$fecha)
 {
	 return "si";
 }else
 {
	 return "no";
 }

}

function editarDiasAtrazados($codCliente,$nroDias)
{
	
$mysqli=conectar_al_servidor(); 
$consulta1="Update cliente set totaldias='$nroDias' where cod_cliente='$codCliente' and totaldias<'$nroDias' ";	
$stmt1 = $mysqli->prepare($consulta1);

if (!$stmt1->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
	
}


function refinanciarencambio($cod_venta,$totalActual,$metodopago){
	
	
	$mysqli=conectar_al_servidor();
	$sql= "Select idcredito,Monto,descuento,fechapago,dias,interes,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
	from credito cr
	where cr.cod_venta='$cod_venta' ";
	
	$descuento=0;  
	$totalenCuotas=0;
$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $F=0;
 $cont=0;
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     
			 $cuota=mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');
			 $fechapago=mb_convert_encoding((string)($valor['fechapago']), 'UTF-8', 'ISO-8859-1');
			 $dias=mb_convert_encoding((string)($valor['dias']), 'UTF-8', 'ISO-8859-1');
			 $interes=mb_convert_encoding((string)($valor['interes']), 'UTF-8', 'ISO-8859-1');
			 $totalenCuotas=$totalenCuotas+$cuota;				 
			 $cont=$cont+1;
			
			  
	  }
 }
 
		
       $sobranteTotales=$totalActual-$totalenCuotas;
	   if($sobranteTotales<0){
		$sobranteTotales=$sobranteTotales*-1;
		   
   $sql= "Select idcredito,Monto,descuento,fechapago,dias,interes,
	IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0) as totalPago
	from credito cr	where cr.cod_venta='$cod_venta'  
	and (IFNULL((select sum(pg.Monto) from pago pg where pg.cod_creditoFK=cr.idcredito),0)+descuento)<Monto order by fechapago desc ";
	

$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);

 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		     
			 $cuota=mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1');
			 $idcredito=mb_convert_encoding((string)($valor['idcredito']), 'UTF-8', 'ISO-8859-1');
			 $dias=mb_convert_encoding((string)($valor['dias']), 'UTF-8', 'ISO-8859-1');
			 $interes=mb_convert_encoding((string)($valor['interes']), 'UTF-8', 'ISO-8859-1');
			 if($sobranteTotales>0){
				 
			 if($sobranteTotales>$cuota){
				 $consulta="Delete From credito Where idcredito='$idcredito'";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$sobranteTotales=$sobranteTotales-$cuota;

			 }else{
				
 $consulta="Update credito set Monto='$sobranteTotales'  Where idcredito='$idcredito'";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$sobranteTotales=$sobranteTotales-$cuota;
				
			 }
			 
			 }
			
			  
	  }
 }
		   
		   
		   
		   

	   }else{
		 $fechaInicio=$fechapago;
		 if($metodopago=="Mensual")	{
			$F=$F+1; 
			$fecha = strtotime('+'.$F." month",strtotime($fechaInicio));
		 }
		 if($metodopago=="Semanal")	{
			 $F=$F+7;
			 $fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
		 }
		if($metodopago=="Quincenal")	{
			 $F=$F+15;
			 $fecha = strtotime('+'.$F." day",strtotime($fechaInicio));
		 }
		 $fechapago=date("Y-m-d H:i:s",$fecha);
		 $plazo=($cont+1)."/".($cont+1);
		 $consulta="Insert into credito (plazo,fechapago,cod_venta,Monto,Esado,Nro_recibo,dias,interes,totalinteres,totaldeuda,total,descuento,deudaInteres,nroventa)
			values('$plazo','$fechapago','$cod_venta','$sobranteTotales','Pendiente','0','$dias','$interes','0','$sobranteTotales','$sobranteTotales','0',0,'$cod_venta')";	

	$stmt = $mysqli->prepare($consulta);

if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
		   
	   }
		
		
	
			  mysqli_close($mysqli);
			 $informacion =array("1" => "exito" );
echo json_encode($informacion);	
exit;
	
		
}



verificar($operacion);
?>

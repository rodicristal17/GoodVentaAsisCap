<?php

$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include('quitarseparadormiles.php');
include("buscar_nivel.php");
include("BuscarNroFactura.php");
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




if($operacion=="buscarinformecaja")
{
	
$idArqeoFk=$_POST['idArqeoFk1'];
$idArqeoFk = mb_convert_encoding((string)($idArqeoFk), 'ISO-8859-1', 'UTF-8');
generarinforme($idArqeoFk);

}



}

function generarinforme($idArqeoFk){
	$styleName="tableRegistroSearch";
	$pagina="";
	$datosventas=datosdepagosventas($idArqeoFk);
	if($datosventas[0]==""){
	$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>COBRO DE CUOTAS</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>COBRO DE CUOTAS</p>".$datosventas[0];
	}
	$totalpagos=$datosventas[1];
	$totaltarjeta=$datosventas[2];
	$totalefectivo=$datosventas[3];
	
	$datosUeno=datosdeCobrosConciliadosUeno($idArqeoFk);
	if($datosUeno[0]==""){
	$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>COBROS CONCILIADOS CON UENO BANK</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>COBROS CONCILIADOS CON UENO BANK</p>".$datosUeno[0];
	}
	
	
$datosIngreso=datosdeIngreso($idArqeoFk);
if($datosIngreso[0]==""){
	
	$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>INGRESOS A CAJA</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>INGRESOS A CAJA</p>".$datosIngreso[0];
	}

$totalingreso=$datosIngreso[1];
	

$datosEgreso=datosdeEgresos($idArqeoFk);
	
	if($datosEgreso[0]==""){
		$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>EGRESOS DE CAJA</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>EGRESOS DE CAJA</p>".$datosEgreso[0];
	}
$totalegreso=$datosEgreso[1];

//deposito
$datosdeDeposito=datosdeDeposito($idArqeoFk);
	
	if($datosdeDeposito[0]==""){
		$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>DEPOSITO</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>DEPOSITO</p>".$datosdeDeposito[0];
	}
$totalDeposito=$datosdeDeposito[1];
 

//////Caja Migrado

$datosdeCajaEnviado=datosdeCajaEnviado($idArqeoFk);
	
	if($datosdeCajaEnviado[0]==""){
		$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>CAJA MIGRADO</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>CAJA MIGRADO</p>".$datosdeCajaEnviado[0];
	}
$totalCajaEnviado=$datosdeCajaEnviado[1];

//////Caja Recibido

$datosdeCajaRecibir=datosdeCajaRecibir($idArqeoFk);
	
	if($datosdeCajaRecibir[0]==""){
		$styleName=CargarStyleTable($styleName);
	$pagina.="<p class='ptituloZ'>CAJA RECIBIDO</p>
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr >
<td id='' style='width:30%' >NO SE ENCONTRARON REGISTROS</td>
</tr>
</table>";
	}else{
		$pagina.="<p class='ptituloZ'>CAJA RECIBIDO</p>".$datosdeCajaRecibir[0];
	}
$totalCajaRecibido=$datosdeCajaRecibir[1];
$totalUenoConciliado=$datosUeno[1];
$operacionesUeno=$datosUeno[2];
$cuotasUeno=$datosUeno[3];


$montoinicio=ObtenerTotalCaja($idArqeoFk);

$ingresos=$totalingreso;
$egresos=$totalegreso;
$Desembolso=0; 
$totalPagosCaja=$totalpagos-$totalUenoConciliado;
if($totalPagosCaja<0){
	$totalPagosCaja=0;
}
$total=($ingresos+$totalPagosCaja+ $totalCajaRecibido)-($egresos + $totalCajaEnviado + $totalDeposito);

$total=$montoinicio+$total;
$informacion =array("1" => "exito","2" => $pagina,"3" => number_format($ingresos,'0',',','.'),"4" => number_format($egresos,'0',',','.')
,"5" => number_format($total,'0',',','.'),"6" => number_format($totaltarjeta,'0',',','.'),"7" => number_format($totalefectivo,'0',',','.'),
"8" => number_format($Desembolso,'0',',','.'),"9" => number_format($totalCajaEnviado,'0',',','.'),"10" => number_format($totalCajaRecibido,'0',',','.'),"11" => number_format($totalpagos,'0',',','.'),"12" => number_format($montoinicio,'0',',','.'),"13" => number_format($totalUenoConciliado,'0',',','.'),"14" => $operacionesUeno,"15" => $cuotasUeno,"16" => "ueno_descontado");
echo json_encode($informacion);	
exit;
}

 

/*Buscar */
function datosdepagosventas($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select  sum(pg.Monto) as Monto ,tipo,cod_venta_fk,descripcion,(SELECT nombre FROM tipopago WHERE cod_tipoPago =pg.cod_tipoPagoFK) as tipopago,
(Select Nombre from local l where l.cod_local=pg.codCaja) as nombrelocal,
(Select nombre_persona from persona pr where pr.cod_persona=vt.cod_clienteFK) as cliente 
 from  pago pg inner join venta vt on cod_venta=pg.cod_venta_fk 
 where pg.Monto>0 and pg.codApertura='$idArqeoFk' group by nrofactura,cod_venta_fk order by cod_venta_fk asc";	


// echo($sql);
// exit;
 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalPagado=0;
$totaltarjeta=0;
$totalefectivo=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$cliente = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1'); 
$Monto = mb_convert_encoding((string)($valor['Monto']), 'UTF-8', 'ISO-8859-1'); 
$nombrelocal = mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1'); 
$cod_venta_fk = mb_convert_encoding((string)($valor['cod_venta_fk']), 'UTF-8', 'ISO-8859-1'); 
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'); 
$descripcion = mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1'); 
$tipopago = mb_convert_encoding((string)($valor['tipopago']), 'UTF-8', 'ISO-8859-1'); 

	if($tipo=="Tarjeta"){
$totaltarjeta=$totaltarjeta+$Monto;
}else{
$totalefectivo=$totalefectivo+$Monto;	
} 
$totalPagado=$totalPagado+$Monto;
if($descripcion=="ventas"){
	$descripcion=buscar_detalles_venta($cod_venta_fk);
}
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px;line-height: 18px;' >".$descripcion."-".$cliente."</td>
<td id='' style='width:20%'>". number_format($Monto,'0',',','.')." &nbsp&nbsp(".$tipopago.")</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalPagado;
$datos[2]=$totaltarjeta;
$datos[3]=$totalefectivo;
return $datos;
}

function informe_caja_tabla_existe($mysqli, $tabla)
{
	$tabla = $mysqli->real_escape_string($tabla);
	$sql = "SHOW TABLES LIKE '$tabla'";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt || !$stmt->execute()) {
		return false;
	}
	$result = $stmt->get_result();
	return mysqli_num_rows($result) > 0;
}

function informe_caja_ueno_texto($valor)
{
	return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function informe_caja_ueno_escape($valor)
{
	return htmlspecialchars(informe_caja_ueno_texto($valor), ENT_QUOTES, 'UTF-8');
}

function informe_caja_ueno_numero($valor)
{
	return number_format((int)$valor, 0, ',', '.');
}

function informe_caja_ueno_estado_visual($estadoConciliacion, $montoPago, $montoAplicado)
{
	$estado = strtolower(trim((string)$estadoConciliacion));
	if ($montoAplicado > 0 && $montoAplicado < $montoPago) {
		return "Parcial";
	}
	if ($estado == "conciliado_ueno" || $montoAplicado >= $montoPago) {
		return "Conciliada";
	}
	if ($estado == "pendiente_conciliacion") {
		return "Parcial";
	}
	if ($estado == "observado") {
		return "Observada";
	}
	if ($estado == "rechazado") {
		return "Rechazada";
	}
	if ($estado == "anulado") {
		return "Anulada";
	}
	return $estadoConciliacion;
}

function informe_caja_ueno_tipo_aplicacion($montoAplicado, $montoPago, $montoCuota, $cuotasMovimiento)
{
	if ((int)$cuotasMovimiento > 1) {
		return "Aplicacion a varias cuotas";
	}
	if ((int)$montoAplicado < (int)$montoPago) {
		return "Pago parcial";
	}
	if ((int)$montoCuota > 0 && (int)$montoAplicado < (int)$montoCuota) {
		return "Pago parcial";
	}
	return "Cuota completa";
}

function datosdeCobrosConciliadosUeno($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	$datos = array("", 0, 0, 0);
	if (!informe_caja_tabla_existe($mysqli, "pago_transferencia_conciliacion") || !informe_caja_tabla_existe($mysqli, "ueno_movimiento_pago") || !informe_caja_tabla_existe($mysqli, "ueno_movimiento_bancario")) {
		return $datos;
	}

	$sql = "SELECT
		ump.id AS id_asignacion, ump.id_movimiento, ump.cod_pagoFK, ump.monto_aplicado,
		ump.fecha_hora_asociacion, umb.nro_comprobante, umb.fecha_confirmacion,
		pc.nro_comprobante_informado, pc.monto_pago, pc.estado_conciliacion,
		p.idPago, p.Monto AS monto_pago_real, p.nrofactura, p.cod_venta_fk, p.cod_creditoFK, IFNULL(p.titulocuota,'') AS titulocuota,
		IFNULL(cr.plazo,'') AS plazo, IFNULL(cr.Monto,0) AS monto_cuota, IFNULL(cr.descuento,0) AS descuento_cuota,
		IFNULL(vt.num_factura,'') AS num_factura, IFNULL(vt.puntoexpedicion,'') AS puntoexpedicion,
		IFNULL(vt.apodo,'') AS alias_venta, IFNULL(pe.nombre_persona,'') AS paciente, IFNULL(cl.ci_cliente,'') AS cedula,
		IFNULL(usu.nombre_persona,'') AS usuario_conciliador,
		(SELECT COUNT(DISTINCT ump2.cod_pagoFK)
			FROM ueno_movimiento_pago ump2
			INNER JOIN pago p2 ON p2.idPago=ump2.cod_pagoFK
			WHERE ump2.id_movimiento=ump.id_movimiento
			AND ump2.estado='activo'
			AND p2.codApertura=ar.idarqueocaja
		) AS cuotas_movimiento
		FROM arqueocaja ar
		INNER JOIN pago p ON p.codApertura=ar.idarqueocaja
		INNER JOIN ueno_movimiento_pago ump ON ump.cod_pagoFK=p.idPago
		INNER JOIN ueno_movimiento_bancario umb ON umb.id_movimiento=ump.id_movimiento
		INNER JOIN pago_transferencia_conciliacion pc ON pc.cod_pagoFK=p.idPago AND pc.activo='SI'
		LEFT JOIN credito cr ON cr.idcredito=p.cod_creditoFK
		LEFT JOIN venta vt ON vt.cod_venta=p.cod_venta_fk
		LEFT JOIN persona pe ON pe.cod_persona=vt.cod_clienteFK
		LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
		LEFT JOIN persona usu ON usu.cod_persona=ump.usuario_asocio
		WHERE ar.idarqueocaja=?
		AND ump.estado='activo'
		AND umb.tipo_movimiento='credito'
		AND pc.estado_conciliacion IN ('conciliado_ueno','pendiente_conciliacion','parcial','parcialmente_conciliado')
		ORDER BY ump.fecha_hora_asociacion DESC, umb.nro_comprobante ASC, p.cod_venta_fk ASC, cr.plazo ASC, p.idPago ASC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return $datos;
	}
	$ss = 's';
	$stmt->bind_param($ss, $idArqeoFk);
	if (!$stmt->execute()) {
		return $datos;
	}

	$result = $stmt->get_result();
	$valor= mysqli_num_rows($result);
	if ($valor<=0) {
		return $datos;
	}

	$styleName="tableRegistroSearch";
	$pagina = "";
	$totalAplicado = 0;
	$operaciones = array();
	$filas = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$montoAplicado = (int)$row['monto_aplicado'];
		$montoPago = (int)$row['monto_pago'];
		$montoCuota = (int)$row['monto_cuota'] - (int)$row['descuento_cuota'];
		if ($montoCuota < 0) {
			$montoCuota = 0;
		}
		$ventaNumero = trim((string)$row['puntoexpedicion']) != "" || trim((string)$row['num_factura']) != ""
			? trim((string)$row['puntoexpedicion'])."-".trim((string)$row['num_factura'])
			: (string)$row['cod_venta_fk'];
		$cuota = trim((string)$row['titulocuota']) != "" ? $row['titulocuota'] : (trim((string)$row['plazo']) != "" ? $row['plazo'] : $row['nrofactura']);
		$estadoVisual = informe_caja_ueno_estado_visual($row['estado_conciliacion'], $montoPago, $montoAplicado);
		$tipoAplicacion = informe_caja_ueno_tipo_aplicacion($montoAplicado, $montoPago, $montoCuota, $row['cuotas_movimiento']);
		$operaciones[(string)$row['id_movimiento']] = true;
		$totalAplicado += $montoAplicado;
		$filas[] = array(
			"fecha" => $row['fecha_hora_asociacion'],
			"comprobante" => $row['nro_comprobante'] != "" ? $row['nro_comprobante'] : $row['nro_comprobante_informado'],
			"paciente" => $row['paciente'],
			"cedula" => $row['cedula'],
			"venta" => $ventaNumero,
			"alias_venta" => $row['alias_venta'],
			"cuota" => $cuota,
			"monto_cuota" => $montoCuota,
			"monto_aplicado" => $montoAplicado,
			"tipo" => $tipoAplicacion,
			"usuario" => $row['usuario_conciliador'],
			"estado" => $estadoVisual
		);
	}

	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:60%;text-align:left;padding:5px;line-height:18px;' ><b>Resumen de conciliaciones Ueno</b><br>".count($operaciones)." operaciones / ".count($filas)." cuotas conciliadas. No forma parte del efectivo a rendir.</td>
<td id='' style='width:20%'>".informe_caja_ueno_numero($totalAplicado)." </td>
<td id='' style='width:20%'>Ueno Bank</td>
</tr>
</table>";

	foreach ($filas as $fila) {
		$styleName=CargarStyleTable($styleName);
		$paciente = trim((string)$fila['paciente']) != "" ? $fila['paciente'] : "Sin paciente";
		if (trim((string)$fila['cedula']) != "") {
			$paciente .= " - ".$fila['cedula'];
		}
		$venta = trim((string)$fila['venta']) != "" ? $fila['venta'] : "-";
		if (trim((string)$fila['alias_venta']) != "") {
			$venta .= " / ".$fila['alias_venta'];
		}
		$detalle = "<b>".informe_caja_ueno_escape($fila['fecha'])."</b><br>"
			."Comprobante: ".informe_caja_ueno_escape($fila['comprobante'])."<br>"
			."Paciente: ".informe_caja_ueno_escape($paciente)."<br>"
			."Venta: ".informe_caja_ueno_escape($venta)." | Cuota: ".informe_caja_ueno_escape($fila['cuota'])."<br>"
			."Tipo: ".informe_caja_ueno_escape($fila['tipo'])." | Estado: ".informe_caja_ueno_escape($fila['estado']);
		$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:60%;text-align:left;padding:5px;line-height:18px;' >".$detalle."</td>
<td id='' style='width:20%'>".informe_caja_ueno_numero($fila['monto_aplicado'])." &nbsp&nbsp(UENO)</td>
<td id='' style='width:20%;line-height:18px;'>Cuota: ".informe_caja_ueno_numero($fila['monto_cuota'])."<br>".informe_caja_ueno_escape($fila['usuario'])."</td>
</tr>
</table>";
	}

	$datos[0]=$pagina;
	$datos[1]=$totalAplicado;
	$datos[2]=count($operaciones);
	$datos[3]=count($filas);
	return $datos;
}


function datosdeEgresos($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "Select monto,motivo,fecha,estado,cod_usuario,idgastos,personales,cod_local,
		(Select nombre_persona from persona where cod_persona=cod_usuario) as usuarionombre,
		(Select Nombre from local l where l.cod_local=g.cod_local ) as nombrelocal
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Egreso' ";
		
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
		  	  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
		  	  $motivo=mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $personales=mb_convert_encoding((string)($valor['personales']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_local=mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	 $totalGasto=$totalGasto+$monto;
		  	 
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px' >".$motivo."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')."</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $pagina;
 $datos[1]= $totalGasto;
 return $datos;
}

function datosdeIngreso($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select monto,motivo,fecha,estado,cod_usuario,idgastos,personales,cod_local,
		(Select nombre_persona from persona where cod_persona=cod_usuario) as usuarionombre,
		(Select Nombre from local l where l.cod_local=g.cod_local ) as nombrelocal
		from gastos g where codApertura='$idArqeoFk' and estado='Activo' and tipo='Ingreso' ";
		
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
		  	  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
		  	  $motivo=mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $personales=mb_convert_encoding((string)($valor['personales']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_local=mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	 $totalGasto=$totalGasto+$monto;
			 
			 
		  	 	 	$styleName=CargarStyleTable($styleName);
					$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:30%;text-align:left;padding:5px' >".$motivo."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')."</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";
		
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $pagina;
 $datos[1]= $totalGasto;
 return $datos;
}

/*Buscar */
function buscar_detalles_venta($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select pr.nombre_producto,
dtv.cantidad_detalle,dtv.cod_productoFK,dtv.precio_producto,dtv.cod_ventaFK,dtv.subtotal,dtv.subPrecioCompra,dtv.detalleproducto
 from
 venta vt inner join detalle_venta dtv on vt.cod_venta=dtv.cod_ventaFK 
 inner join producto pr on pr.cod_producto=dtv.cod_productoFK
 where vt.cod_venta='$buscar' ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$a=1;
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$nombre_producto = mb_convert_encoding((string)($valor['nombre_producto']), 'UTF-8', 'ISO-8859-1');       
$cantidad_detalle = mb_convert_encoding((string)($valor['cantidad_detalle']), 'UTF-8', 'ISO-8859-1');       
$detalleproducto = mb_convert_encoding((string)($valor['detalleproducto']), 'UTF-8', 'ISO-8859-1');       
$subtotal = mb_convert_encoding((string)($valor['subtotal']), 'ISO-8859-1', 'UTF-8');      
if($pagina==""){
	$pagina.=$a.") &nbsp".$nombre_producto.",&nbsp&nbsp".number_format($cantidad_detalle,'2',',','.')."(".$detalleproducto.")";	
	}else{
		$pagina.="<br>".$a.") &nbsp".$nombre_producto.",&nbsp&nbsp".number_format($cantidad_detalle,'2',',','.')."(".$detalleproducto.")";	
	}


}
}

return $pagina;
}


function ObtenerTotalCaja($idArqeoFk)
{
$mysqli=conectar_al_servidor();

$sql= "Select montoapertura
from arqueocaja  where idarqueocaja='$idArqeoFk'  ";
$montoapertura = "0";   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
          
$montoapertura = mb_convert_encoding((string)($valor['montoapertura']), 'UTF-8', 'ISO-8859-1');          

	    	 


}
}

return $montoapertura;
}

function datosdeDeposito($idArqeoFk)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
		$sql= "Select monto,motivo,fecha,estado,cod_usuario,idgastos,personales,cod_local,
		(Select nombre_persona from persona where cod_persona=cod_usuario) as usuarionombre,
		 (Select Nombre from local l where l.cod_local=g.cod_local ) as nombrelocal
		 from gastos g where codApertura='$idArqeoFk' and estado='Activo' and  tipo='Deposito' ";
		
   
   $stmt = $mysqli->prepare($sql);
 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $totalGasto=0;
 $styleName="tableRegistroSearch";
 
 
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idgastos=$valor['idgastos'];
		  	  $usuarionombre=mb_convert_encoding((string)($valor['usuarionombre']), 'UTF-8', 'ISO-8859-1');
		  	  $monto=mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1');
		  	  $motivo=mb_convert_encoding((string)($valor['motivo']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $personales=mb_convert_encoding((string)($valor['personales']), 'UTF-8', 'ISO-8859-1');
		  	  $estado=mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');
		  	  $cod_local=mb_convert_encoding((string)($valor['cod_local']), 'UTF-8', 'ISO-8859-1');
		  	  $nombrelocal=mb_convert_encoding((string)($valor['nombrelocal']), 'UTF-8', 'ISO-8859-1');
		  	 $totalGasto=$totalGasto+$monto;
		  	 
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:60%;text-align:left;padding:5px' >".$motivo."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')."</td>
<td id='' style='width:20%'>". $nombrelocal."</td>
</tr>
</table>
";
			    	 
		  	  
			  
			  
	  }
 }

 $datos[0]= $pagina;
 $datos[1]= $totalGasto;
 return $datos;
}

/*Buscar */
function datosdeCajaEnviado($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK , 
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from  migrar_caja  where cod_caja_desdeFK='$idArqeoFk' ";	


 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalCaja=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
 
$obs = mb_convert_encoding((string)($valor['obs']), 'UTF-8', 'ISO-8859-1'); 
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1'); 
$monto = mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1'); 
$usuarioRecibe = mb_convert_encoding((string)($valor['usuarioRecibe']), 'UTF-8', 'ISO-8859-1'); 

$totalCaja= $totalCaja + $monto ;
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:60%;text-align:left;padding:5px;line-height: 18px;' >".$usuarioRecibe."<b> **$obs - $fecha**</b></td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')." </td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalCaja;
return $datos;
}

 

/*Buscar */
function datosdeCajaRecibir($idArqeoFk)
{
$mysqli=conectar_al_servidor();
 
	
$sql= "select idmigrar_caja, obs, fecha, monto, cod_caja_desdeFK, cod_caja_hastaFK, estado, tipo, cod_usuRecibeFK, cod_UsuEnviaFK , 
				(select nombre_persona from persona where cod_persona=cod_usuRecibeFK) as usuarioRecibe  ,
				(select nombre_persona from persona where cod_persona=cod_UsuEnviaFK) as usuarioEnvia from  migrar_caja  where cod_caja_hastaFK='$idArqeoFk' ";	


 $pagina="";
 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$totalCaja=0;
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  



$monto = mb_convert_encoding((string)($valor['monto']), 'UTF-8', 'ISO-8859-1'); 
$usuarioEnvia = mb_convert_encoding((string)($valor['usuarioEnvia']), 'UTF-8', 'ISO-8859-1'); 

$totalCaja= $totalCaja + $monto ;
	$styleName=CargarStyleTable($styleName);
	$pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td id='' style='width:60%;text-align:left;padding:5px;line-height: 18px;' >".$usuarioEnvia."</td>
<td id='' style='width:20%'>". number_format($monto,'0',',','.')." </td>
<td id='' style='width:20%'> </td>
</tr>
</table>
";




}
}
   
$datos[0]=$pagina;
$datos[1]=$totalCaja;
return $datos;
}
 

verificar($operacion);
?>

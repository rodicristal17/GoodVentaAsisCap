<?php
require("conexion.php");
require_once("solicitud_eliminado_helper.php");
include("verificar_navegador.php");
include("subir_foto_base64.php");
include("quitarseparadormiles.php");
include("classTable.php");
$operacion = $_POST['funt'];
$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

function ObtenerDatos($operacion)
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



if($operacion=="nuevo" || $operacion=="editar" )
{


$sms=$_POST['sms'];
$sms = mb_convert_encoding((string)($sms), 'ISO-8859-1', 'UTF-8');

$FechaNac=$_POST['FechaNac'];
$FechaNac = mb_convert_encoding((string)($FechaNac), 'ISO-8859-1', 'UTF-8');
$FechaNac = cliente_normalizar_fecha_mysql($FechaNac);

$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');
$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = mb_convert_encoding((string)($nombre_persona), 'ISO-8859-1', 'UTF-8');
$direccion=$_POST['direccion'];
$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
$telefono=$_POST['telefono'];
$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');
$email=$_POST['email'];
$email = mb_convert_encoding((string)($email), 'ISO-8859-1', 'UTF-8');
$cod_cliente=$cod_persona;
$rut_cliente=$_POST['rut_cliente'];
$rut_cliente = mb_convert_encoding((string)($rut_cliente), 'ISO-8859-1', 'UTF-8');
$ci_cliente=$_POST['ci_cliente'];
$ci_cliente = mb_convert_encoding((string)($ci_cliente), 'ISO-8859-1', 'UTF-8');
$Calificacion=$_POST['Calificacion'];
$Calificacion = mb_convert_encoding((string)($Calificacion), 'ISO-8859-1', 'UTF-8');
$whapp=$_POST['whapp'];
$whapp = mb_convert_encoding((string)($whapp), 'ISO-8859-1', 'UTF-8');
$estado=$_POST['estado'];
$estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
$idzonaFk=$_POST['idzonaFk'];
$idzonaFk = mb_convert_encoding((string)($idzonaFk), 'ISO-8859-1', 'UTF-8');
$lugardetrabajo=$_POST['lugardetrabajo'];
$lugardetrabajo = mb_convert_encoding((string)($lugardetrabajo), 'ISO-8859-1', 'UTF-8');
$salario=$_POST['salario'];
$salario = cliente_normalizar_numero_nullable($salario);
$antiguedad=$_POST['antiguedad'];
$antiguedad = mb_convert_encoding((string)($antiguedad), 'ISO-8859-1', 'UTF-8');
$teleftrab1=$_POST['teleftrab1'];
$teleftrab1 = mb_convert_encoding((string)($teleftrab1), 'ISO-8859-1', 'UTF-8');
$teleftrab2=$_POST['teleftrab2'];
$teleftrab2 = mb_convert_encoding((string)($teleftrab2), 'ISO-8859-1', 'UTF-8');
$direcciontrab=$_POST['direcciontrab'];
$direcciontrab = mb_convert_encoding((string)($direcciontrab), 'ISO-8859-1', 'UTF-8');
$accesocredito=$_POST['accesocredito'];
$accesocredito = mb_convert_encoding((string)($accesocredito), 'ISO-8859-1', 'UTF-8');

abm($FechaNac,$sms,$accesocredito,$idzonaFk,$whapp,$estado,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_cliente,$rut_cliente,$ci_cliente,$Calificacion,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$operacion);

}

 
 
 if($operacion=="addmasreferencias"){
 	$totalCargado=$_POST["totalCargado"];
 	$totalCargado=mb_convert_encoding((string)($totalCargado), 'ISO-8859-1', 'UTF-8');
	$idcliente=$_POST["idcliente"];
 	$idcliente=mb_convert_encoding((string)($idcliente), 'ISO-8859-1', 'UTF-8');
 	addmasreferencias($totalCargado,$idcliente);
 }  
 
 
 if($operacion=="cambiar_estado_antecedente_consulta"){
 	$cod_antecedente_paciente=$_POST["cod_antecedente_paciente"];
 	$cod_antecedente_paciente=mb_convert_encoding((string)($cod_antecedente_paciente), 'ISO-8859-1', 'UTF-8');

 	cambiar_estado_antecedente_consulta($cod_antecedente_paciente);
 } 
 
 
 if($operacion=="buscar_antecedente_consulta"){
	$cod_clienteFK=$_POST["cod_clienteFK"];
 	$cod_clienteFK=mb_convert_encoding((string)($cod_clienteFK), 'ISO-8859-1', 'UTF-8');
	$cod_ventaFK=$_POST["cod_ventaFK"];
 	$cod_ventaFK=mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
 	buscar_antecedente_consulta($cod_clienteFK,$cod_ventaFK);
 }
 
 
 if($operacion=="buscar_antecedente_resumen_consulta"){
	$cod_clienteFK=$_POST["cod_clienteFK"];
 	$cod_clienteFK=mb_convert_encoding((string)($cod_clienteFK), 'ISO-8859-1', 'UTF-8');
	$cod_ventaFK=$_POST["cod_ventaFK"];
 	$cod_ventaFK=mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
 	buscar_antecedente_resumen_consulta($cod_clienteFK,$cod_ventaFK);
 }

 if($operacion=="buscar"){
	 

 	$codigo=$_POST["codigo"];
 	$codigo=mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
	$documento=$_POST["documento"];
 	$documento=mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$cliente=$_POST["cliente"];
 	$cliente=mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	$zona=$_POST["zona"];
 	$zona=mb_convert_encoding((string)($zona), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$accesocredito=$_POST["accesocredito"];
 	$accesocredito=mb_convert_encoding((string)($accesocredito), 'ISO-8859-1', 'UTF-8');
 	BuscarRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito);
 }
 
 if($operacion=="buscarmas"){
	 

 	$codigo=$_POST["codigo"];
 	$codigo=mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
	$documento=$_POST["documento"];
 	$documento=mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$cliente=$_POST["cliente"];
 	$cliente=mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	$zona=$_POST["zona"];
 	$zona=mb_convert_encoding((string)($zona), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$accesocredito=$_POST["accesocredito"];
 	$accesocredito=mb_convert_encoding((string)($accesocredito), 'ISO-8859-1', 'UTF-8');
	$registrocargado=$_POST["registrocargado"];
 	$registrocargado=mb_convert_encoding((string)($registrocargado), 'ISO-8859-1', 'UTF-8');
 	BuscarMasRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito,$registrocargado);
 }

if($operacion=="addImagenes"){
$idclientefk=$_POST['idclientefk'];
$idclientefk = mb_convert_encoding((string)($idclientefk), 'ISO-8859-1', 'UTF-8');
addImagenes($idclientefk);
}

if($operacion=="buscarDocumentos"){
$idcontrato=$_POST['idcliente'];
$idcontrato = mb_convert_encoding((string)($idcontrato), 'ISO-8859-1', 'UTF-8');
buscarDocumentos($idcontrato);
}

if($operacion=="eliminardocumento"){
$idcontrato=$_POST['idcliente'];
$idcontrato = mb_convert_encoding((string)($idcontrato), 'ISO-8859-1', 'UTF-8');
$iddocumento=$_POST['iddocumento'];
$iddocumento = mb_convert_encoding((string)($iddocumento), 'ISO-8859-1', 'UTF-8');
$urldocumento=$_POST['urldocumento'];
$urldocumento = mb_convert_encoding((string)($urldocumento), 'ISO-8859-1', 'UTF-8');
EliminarDocumento($idcontrato,$iddocumento,$urldocumento);

}

 if($operacion=="buscarmasreferencias"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarmasreferencias($buscar);
 }
 
  if($operacion=="cargar_antecedente_paciente"){
 	$cod_ventaFK=$_POST["cod_ventaFK"];
 	$cod_ventaFK=mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');
	$cod_clienteFK=$_POST["cod_clienteFK"];
 	$cod_clienteFK=mb_convert_encoding((string)($cod_clienteFK), 'ISO-8859-1', 'UTF-8');
	$observacion=$_POST["observacion"];
 	$observacion=mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
 	cargar_antecedente_paciente($cod_ventaFK,$cod_clienteFK,$observacion);
 }


 if($operacion=="buscarvista"){
	 
	
 	$ruc=$_POST["ruc"];
 	$ruc=mb_convert_encoding((string)($ruc), 'ISO-8859-1', 'UTF-8');
	$documento=$_POST["documento"];
 	$documento=mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$cliente=$_POST["cliente"];
 	$cliente=mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	$telef=$_POST["telef"];
 	$telef=mb_convert_encoding((string)($telef), 'ISO-8859-1', 'UTF-8');
 	$informacion = BuscarRegistroEnVista($ruc,$documento,$cliente,$telef);
	echo json_encode($informacion);	
	exit;
 }

 if($operacion=="buscarporci"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarporci($buscar);
 }

 if($operacion=="buscarmensajes"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
 	buscarmensajes($buscar);
 }

 if($operacion=="buscarcumpleCliente"){
 	$Fecha=$_POST["Fecha"];
 	$Fecha=mb_convert_encoding((string)($Fecha), 'ISO-8859-1', 'UTF-8');
	$Zona=$_POST["Zona"];
 	$Zona=mb_convert_encoding((string)($Zona), 'ISO-8859-1', 'UTF-8');
 	 buscarcumpleCliente($Fecha,$Zona);
 }
 
 if($operacion=="buscarcuentaImpago"){
 	$fecha1=$_POST["fecha1"];
 	$fecha1=mb_convert_encoding((string)($fecha1), 'ISO-8859-1', 'UTF-8');
	$fecha2=$_POST["fecha2"];
 	$fecha2=mb_convert_encoding((string)($fecha2), 'ISO-8859-1', 'UTF-8');
	$local=$_POST["local"];
 	$local=mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');	
	$zona=$_POST["zona"];
 	$zona=mb_convert_encoding((string)($zona), 'ISO-8859-1', 'UTF-8');
	$cliente=$_POST["cliente"];
 	$cliente=mb_convert_encoding((string)($cliente), 'ISO-8859-1', 'UTF-8');
	$cobrador=$_POST["cobrador"];
 	$cobrador=mb_convert_encoding((string)($cobrador), 'ISO-8859-1', 'UTF-8');
	
	$tipo=$_POST["tipo"];
 	$tipo=mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	
 	buscarcuentaImpago($tipo,$fecha1,$fecha2,$local,$zona,$cliente,$cobrador);
 }
 
if($operacion=="buscarGeolocalizacion"){
$idcontrato=$_POST['idcliente'];
$idcontrato = mb_convert_encoding((string)($idcontrato), 'ISO-8859-1', 'UTF-8');
buscarGeolocalizacion($idcontrato);
}

if($operacion=="InsertarGeo"){
$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');
$fecha=$_POST['fecha'];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
$descripcion=$_POST['descripcion'];
$descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');

$latitudGeo=$_POST['latitudGeo'];
$latitudGeo = mb_convert_encoding((string)($latitudGeo), 'ISO-8859-1', 'UTF-8');
$longitudGeo=$_POST['longitudGeo'];
$longitudGeo = mb_convert_encoding((string)($longitudGeo), 'ISO-8859-1', 'UTF-8');

InsertarGeo($cod_persona,$fecha,$descripcion,$latitudGeo,$longitudGeo);

}

if($operacion=="EliminarGeo"){
$CodGeoLocalizacion=$_POST['CodGeoLocalizacion'];
$CodGeoLocalizacion = mb_convert_encoding((string)($CodGeoLocalizacion), 'ISO-8859-1', 'UTF-8');

EliminarGeo($CodGeoLocalizacion);

}

if ($operacion == "obtenerCliente") {
	$filtro = array(
		'c.cod_cliente' => isset($_POST['cod_cliente']) ? mb_convert_encoding((string)($_POST['cod_cliente']), 'ISO-8859-1', 'UTF-8') : null,
		'p.cod_persona' => isset($_POST['cod_persona']) ? mb_convert_encoding((string)($_POST['cod_persona']), 'ISO-8859-1', 'UTF-8') : null,
		'c.ci_cliente' => isset($_POST['ci_cliente']) ? mb_convert_encoding((string)($_POST['ci_cliente']), 'ISO-8859-1', 'UTF-8') : null,
		'c.rut_cliente' => isset($_POST['rut_cliente']) ? mb_convert_encoding((string)($_POST['rut_cliente']), 'ISO-8859-1', 'UTF-8') : null,
		'p.nombre_persona' => isset($_POST['nombre_persona']) ? mb_convert_encoding((string)($_POST['nombre_persona']), 'ISO-8859-1', 'UTF-8') : null,
		'nombre_cedula_cliente' => isset($_POST['nombre_cedula_cliente']) ? mb_convert_encoding((string)($_POST['nombre_cedula_cliente']), 'ISO-8859-1', 'UTF-8') : null,
		'c.telefono' => isset($_POST['telefono']) ? mb_convert_encoding((string)($_POST['telefono']), 'ISO-8859-1', 'UTF-8') : null,
		'c.direccion' => isset($_POST['direccion']) ? mb_convert_encoding((string)($_POST['direccion']), 'ISO-8859-1', 'UTF-8') : null,
		'c.estado' => isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null,
		'c.accesocredito' => isset($_POST['accesocredito']) ? mb_convert_encoding((string)($_POST['accesocredito']), 'ISO-8859-1', 'UTF-8') : null,
		'c.idzonaFk' => isset($_POST['idzonaFk']) ? mb_convert_encoding((string)($_POST['idzonaFk']), 'ISO-8859-1', 'UTF-8') : null,
		'cedula' => isset($_POST['cedula']) ? mb_convert_encoding((string)($_POST['cedula']), 'ISO-8859-1', 'UTF-8') : null,
	);
	$limite = isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

	$resultado = obtenerCliente($filtro);
	$totalRegistros = count($resultado);
	$resultado = obtenerCliente($filtro, $limite);

	$informacion = array("1" => "exito", "2" => $resultado, "3" => count($resultado), "4" => $totalRegistros);
	echo json_encode($informacion);
	exit;
}

if($operacion=="buscarDatalis")
{
	
	buscarDatalis();

}

if($operacion=="buscarDocumentosPrincipal"){
$idcliente=$_POST['idcliente'];
$idcliente = mb_convert_encoding((string)($idcliente), 'ISO-8859-1', 'UTF-8');

$cod_ventaFK=$_POST['cod_ventaFK'];
$cod_ventaFK = mb_convert_encoding((string)($cod_ventaFK), 'ISO-8859-1', 'UTF-8');

buscarDocumentosPrincipal($idcliente,$cod_ventaFK);
}



if($operacion=="buscarDocumentosGaleriaFoto"){
$idcliente=$_POST['idcliente'];
$idcliente = mb_convert_encoding((string)($idcliente), 'ISO-8859-1', 'UTF-8');

$descripcion=$_POST['descripcion'];
$descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
buscarDocumentosGaleriaFoto($idcliente,$descripcion);
}


}


function cliente_responder_error($mensaje, $detalle = "")
{
	$informacion = array("1" => "error", "2" => $mensaje);
	if ($detalle != "") {
		$informacion["3"] = $detalle;
	}
	echo json_encode($informacion);
	exit;
}

function cliente_normalizar_fecha_mysql($fecha)
{
	$fecha = trim((string)$fecha);
	if ($fecha == "") {
		return "0000-00-00";
	}
	if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
		return $fecha;
	}
	if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $partes)) {
		return $partes[3] . "-" . $partes[2] . "-" . $partes[1];
	}
	if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $fecha, $partes)) {
		return $partes[3] . "-" . $partes[2] . "-" . $partes[1];
	}
	return $fecha;
}

function cliente_normalizar_numero_nullable($valor)
{
	$valor = trim((string)$valor);
	if ($valor == "") {
		return null;
	}
	return quitarseparadormiles($valor);
}


function renderImagenPacienteCard($codigo,$iddocumento,$idcontratoFK,$archivourl,$descripcion,$fecha,$onclick,$rowName="",$rowId="tbSelecRegistroImagen")
{
	$codigoHtml=htmlspecialchars((string)$codigo, ENT_QUOTES, 'UTF-8');
	$iddocumentoHtml=htmlspecialchars((string)$iddocumento, ENT_QUOTES, 'UTF-8');
	$idcontratoFKHtml=htmlspecialchars((string)$idcontratoFK, ENT_QUOTES, 'UTF-8');
	$archivourlHtml=htmlspecialchars((string)$archivourl, ENT_QUOTES, 'UTF-8');
	$descripcionTexto=trim((string)$descripcion) != "" ? (string)$descripcion : "Sin descripción";
	$descripcionHtml=htmlspecialchars($descripcionTexto, ENT_QUOTES, 'UTF-8');
	$fechaTexto=trim((string)$fecha) != "" ? (string)$fecha : "Sin fecha";
	$fechaHtml=htmlspecialchars($fechaTexto, ENT_QUOTES, 'UTF-8');
	$onclickHtml=htmlspecialchars((string)$onclick, ENT_QUOTES, 'UTF-8');
	$rowNameHtml=$rowName != "" ? " name='".htmlspecialchars((string)$rowName, ENT_QUOTES, 'UTF-8')."'" : "";
	$rowIdHtml=htmlspecialchars((string)$rowId, ENT_QUOTES, 'UTF-8');

	return "
<table id='$codigoHtml' class='clinical-image-card' data-description='$descripcionHtml' border='0' cellspacing='0' cellpadding='0'>
<tr id='$rowIdHtml' onclick='$onclickHtml'$rowNameHtml>
<td class='clinical-image-card__content'>
<div class='clinical-image-thumb' style=\"background-image:url('$archivourlHtml')\"></div>
<div class='clinical-image-card__body'>
<div class='clinical-image-card__description'>$descripcionHtml</div>
<div class='clinical-image-card__date'>$fechaHtml</div>
<div class='clinical-image-card__actions'><span>Ver imagen</span><i class='fa-regular fa-eye'></i></div>
</div>
</td>
<td id='td_id_1' style='display:none'>$codigoHtml</td>
<td id='td_id_2' style='display:none'>$iddocumentoHtml</td>
<td id='td_id_3' style='display:none'>$idcontratoFKHtml</td>
<td id='td_datos_1' style='display:none'>$archivourlHtml</td>
<td id='td_datos_2' style='display:none'>$descripcionHtml</td>
<td id='td_datos_3' style='display:none'>$fechaHtml</td>
</tr>
</table>";
}




function buscarDocumentosGaleriaFoto($idcliente,$descripcion)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 
	 $condicionDescripcion="";
	 if($descripcion!=""){
		 $condicionDescripcion=" and descripcion like '%".$descripcion."%'";
	 }
	 
	$condicionVenta= "";
	if (isset($_POST['codVenta']) && !empty($_POST['codVenta'])) {
		$condicionVenta= " and cod_ventaFK = ".$_POST['codVenta'];
	}
		$sql= "SELECT *
				FROM fotos_cliente where cod_clienteFK='$idcliente' ".$condicionDescripcion.$condicionVenta." order by idfotos_cliente asc ";
  
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $iddocumento=$valor['idfotos_cliente'];
		  	  $archivourl=mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1');
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $idcontratoFK=$valor['cod_clienteFK'];
		  	 
		  	 
			  $codigo= substr(str_shuffle($permitted_chars), 0, 5);
			  
			  
			   $pagina.=renderImagenPacienteCard(
				   $codigo,
				   $iddocumento,
				   $idcontratoFK,
				   $archivourl,
				   $descripcion,
				   $fecha,
				   'SeleccionarItemImagenGaleriaFoto(this,"divAbmConsulta");',
				   "",
				   "tbSelecRegistroImagen"
			   );
			  
			  // $pagina.=$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina.$pagina;
			  
			  $codigo="";
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}







function buscarDocumentosPrincipal($codigo,$cod_ventaFK)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "SELECT * FROM fotos_cliente where cod_clienteFK='$codigo' and cod_ventaFK='$cod_ventaFK'";
  
   // echo($sql);
   // exit;
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $iddocumento=$valor['idfotos_cliente'];
		  	  $archivourl=mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1');
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $idcontratoFK=$valor['cod_clienteFK'];
		  	 
		  	 
			  $codigo= substr(str_shuffle($permitted_chars), 0, 5);
			  
			  
		  	  $pagina.=renderImagenPacienteCard(
				  $codigo,
				  $iddocumento,
				  $idcontratoFK,
				  $archivourl,
				  $descripcion,
				  $fecha,
				  "SeleccionarItemImagenPrincipal(this)",
				  "tdBDClienteFoto",
				  "tbSelecRegistroImagenPrincipal"
			  );
			  
			  $codigo="";
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}



function buscarDatalis()
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,
(Select nombre from zona where idzonaFk=idzona )as zona ,
cl.totaldias,
cl.lugardetrabajo,
cl.salario,
cl.antiguedad,
cl.teleftrab1,
cl.fechanac,
cl.teleftrab2,
cl.direcciontrab
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' order by pr.nombre_persona ";
		
   
   $stmt = $mysqli->prepare($sql);
 if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $cod_persona=$valor['cod_persona'];
		  	  $ci_cliente=mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1');
		  	  $nombre_persona=mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');			  
		  	 
			  $pagina.="<option id='$cod_persona' > ".$ci_cliente." - ".$nombre_persona."</option>";		  	
			  
			  
	  }
 }
 
 
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}



function EliminarGeo($CodGeoLocalizacion)
{

if($CodGeoLocalizacion=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor(); 


$consulta1=" delete from ubicaciones where idubicaciones='".$CodGeoLocalizacion."' ";
$stmt1 = $mysqli->prepare($consulta1); 
if (!$stmt1->execute()) {
	
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}






function InsertarGeo($cod_persona,$fecha,$descripcion,$latitudGeo,$longitudGeo)
{

if($cod_persona==""  || $latitudGeo=="" || $longitudGeo==""){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}


$mysqli=conectar_al_servidor(); 


$consulta1="Insert into ubicaciones (lat,lot,descripcion,cod_clienteFk,fecha)
values(?,?,?,?,now())";
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssss';
$stmt1->bind_param($ss,$latitudGeo,$longitudGeo,$descripcion,$cod_persona);



if (!$stmt1->execute()) {
	
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


 mysqli_close($mysqli); 
$informacion =array("1" => "exito","2"=>$cod_persona);
echo json_encode($informacion);	
exit;

}




function buscarGeolocalizacion($codigo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "SELECT *	FROM ubicaciones where cod_clienteFk='$codigo'";
  
   
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
		  
		  
		      $idubicaciones=$valor['idubicaciones'];
		  	  $lat=mb_convert_encoding((string)($valor['lat']), 'UTF-8', 'ISO-8859-1');
		  	  $lot=mb_convert_encoding((string)($valor['lot']), 'UTF-8', 'ISO-8859-1');
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=$valor['fecha'];
		  	 
		  	
			  
			  
		  	  $pagina.="
<table  class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro' onclick='obtenerdatosGeoLocalizcion(this)' >
<td id='td_id_1' style='display:none'>".$idubicaciones."</td>
<td id='td_id_2' style='display:none'>".$lat."</td>
<td id='td_id_3' style='display:none'>".$lot."</td>
<td id='td_datos_1' class='td_search' style='width:60%'>".$descripcion."</td>
<td id='td_datos_2' class='td_search' style='width:40%'>".$fecha."</td>
</tr>
</table>";






			  
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}





function abm($FechaNac,$sms,$accesocredito,$idzonaFk,$whapp,$estado,$cod_persona,$nombre_persona,$direccion,$telefono,$email,$cod_cliente,$rut_cliente,$ci_cliente,$Calificacion,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$operacion)
{

if($nombre_persona==""  || $idzonaFk=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 

// Verificar si ya existe el cliente
$result= BuscarRegistroEnVista("", $ci_cliente, "", "");
if(count($result[4]) > 0) {
	if($operacion=="nuevo") {
		$informacion =array("1" => "EX", "2" => $result[4][0]);
		echo json_encode($informacion);	
		exit;
	} else {
		// Comprueba si el que existe coincide con cod_cliente
		$es_mismo_registro= false;
		foreach ($result[4] as $reg) {
			if ($reg['cod_persona'] == $cod_persona) {
				$es_mismo_registro= true;
			}
		}

		if (!$es_mismo_registro) {
			$informacion =array("1" => "EX", "2" => $result[4][0]);
			echo json_encode($informacion);	
			exit;
		}
	}  
}
	/*AUDITORIA*/
	date_default_timezone_set('America/Asuncion');    
$fecha_inser_edit = date('Y-m-d H:i:s', time()); 
	 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

if($operacion=="nuevo") 
{


$consulta1="Insert into persona (nombre_persona,direccion,telefono,email)
values(Upper(?),Upper(?),Upper(?),Upper(?))";
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1) {
	cliente_responder_error("No se pudo preparar el guardado de persona.", $mysqli->error);
}
$ss='ssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email);

$consulta2="Insert into cliente (fechanac,rut_cliente,Calificacion,cod_cliente,whapp,estado,idzonaFk,ci_cliente,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,cod_user_insert,fecha_insert,accesocredito,sms,foto1,foto2,fecha_edicion_referencia,obsTrabajo)
values(?,?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt2 = $mysqli->prepare($consulta2);
if (!$stmt2) {
	cliente_responder_error("No se pudo preparar el guardado del cliente.", $mysqli->error);
}
$foto1Inicial = "";
$foto2Inicial = "";
$obsTrabajoInicial = "";
$ss='sssssssssssssssssssss';
$stmt2->bind_param($ss,$FechaNac,$rut_cliente,$Calificacion,$whapp,$estado,$idzonaFk,$ci_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$user,$fecha_inser_edit,$accesocredito,$sms,$foto1Inicial,$foto2Inicial,$fecha_inser_edit,$obsTrabajoInicial);

}


if($operacion=="editar")
{


$consulta1="Update persona set nombre_persona=Upper(?),direccion=Upper(?),telefono=Upper(?),email=Upper(?) where cod_persona=?";	

$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1) {
	cliente_responder_error("No se pudo preparar la actualizacion de persona.", $mysqli->error);
}
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$direccion,$telefono,$email,$cod_persona);


$consulta2="update cliente set fechanac=?,rut_cliente=?,Calificacion=?,whapp=?,estado=?,idzonaFk=?,ci_cliente=?,lugardetrabajo=?,salario=?,antiguedad=?,teleftrab1=?,teleftrab2=?,direcciontrab=?,cod_user_edit=?,fecha_edit=?,accesocredito=?,sms=? where cod_cliente=? ";	

$stmt2 = $mysqli->prepare($consulta2);
if (!$stmt2) {
	cliente_responder_error("No se pudo preparar la actualizacion del cliente.", $mysqli->error);
}
$ss='ssssssssssssssssss';
$stmt2->bind_param($ss,$FechaNac,$rut_cliente,$Calificacion,$whapp,$estado,$idzonaFk,$ci_cliente,$lugardetrabajo,$salario,$antiguedad,$teleftrab1,$teleftrab2,$direcciontrab,$user,$fecha_inser_edit,$accesocredito,$sms,$cod_persona);


}




if (!$stmt1->execute()) {
	cliente_responder_error("No se pudo guardar los datos personales del cliente.", $stmt1->error);
}


if (!$stmt2->execute()) {
	cliente_responder_error("No se pudo guardar los datos del cliente.", $stmt2->error);
}

if($operacion=="nuevo") {
	$cod_persona=obtenerUltimaId();
}
cargarFotos($cod_persona);



 mysqli_close($mysqli);
$informacion =array("1" => "exito","2"=>$cod_persona);
echo json_encode($informacion);	
exit;

}

function addmasreferencias($totalCargado,$cod_cliente)
{

if($cod_cliente=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
$control=1;	
if($totalCargado>0){
	
$consulta= "delete from referenciascliente where cod_clienteFk='$cod_cliente' "; 
$stmt1 = $mysqli->prepare($consulta);
if (!$stmt1->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
}
while($control<=$totalCargado){

$observacion=$_POST['observacion'.$control];
$observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');

$telef=$_POST['telefono'.$control];
$telef = mb_convert_encoding((string)($telef), 'ISO-8859-1', 'UTF-8');

$direccion=$_POST['direccion'.$control];
$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');

$referencias=$_POST['referencia'.$control];
$referencias = mb_convert_encoding((string)($referencias), 'ISO-8859-1', 'UTF-8');

$Tipo=$_POST['Tipo'.$control];
$Tipo = mb_convert_encoding((string)($Tipo), 'ISO-8859-1', 'UTF-8');

$consulta="Insert into referenciascliente ( telef, direccion, referencias, observacion, cod_clienteFk, tipo)
values(?,?,?,?,?,?)";

$stmt1 = $mysqli->prepare($consulta);
$ss='ssssss';
$stmt1->bind_param($ss,$telef,$direccion,$referencias,$observacion, $cod_cliente, $Tipo);

if (!$stmt1->execute()) {
	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}


$control=$control+1;

}


 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}
function cambiar_estado_antecedente_consulta($cod_antecedente_paciente)
{

if($cod_antecedente_paciente=="" ){
$informacion =array("1" => "camposvacio");
echo json_encode($informacion);	
exit;
}

 $user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	
date_default_timezone_set('America/Anguilla');    
$fecha_inser_edit = date('Y-m-d | h:i:sa', time());	

$mysqli=conectar_al_servidor();
$consulta1="Update antecedente_paciente set estado='Inactivo', cod_usuario=? where idantecedente_paciente=?";
$stmt1 = $mysqli->prepare($consulta1);
$stmt1->bind_param('ss',$user,$cod_antecedente_paciente);
if (!$stmt1->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;
}
mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);
exit;

}

function guardarmensaje($fecha,$hora,$idcliente)
{


$mysqli=conectar_al_servidor(); 

$consulta= "Select count(*) from mensajesenviados where idcliente='$idcliente' ";

$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

$result = $stmt->get_result();
$nro_total=$result->fetch_row();
 $valor=$nro_total[0];
if($valor==0){
	$consulta1="Insert into mensajesenviados (fecha,hora,idcliente)
values(?,?,?)";
	
}else{
	
	$consulta1="update mensajesenviados set fecha=?,hora=? where idcliente=?";
}

$stmt1 = $mysqli->prepare($consulta1);
$ss='sss';
$stmt1->bind_param($ss,$fecha,$hora,$idcliente);



if (!$stmt1->execute()) {
	

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;

}




 mysqli_close($mysqli);
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}

function obtenerUltimaId()
{
	$cod_persona ="";
	$mysqli=conectar_al_servidor();
	 $pagina='';
	 $sql= "Select cod_cliente from cliente where estado='Activo'  order by cod_cliente desc limit 1";
	
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
		  
		  
		      $cod_persona=$valor['cod_cliente'];
		   	 
			  
	  }
 }
 
  mysqli_close($mysqli);
 return $cod_persona;
}

function cargarFotos($cod_persona){
	
$ext1=$_POST['ext1'];
$ext1 = mb_convert_encoding((string)($ext1), 'ISO-8859-1', 'UTF-8');

$ext2=$_POST['ext2'];
$ext2 = mb_convert_encoding((string)($ext2), 'ISO-8859-1', 'UTF-8');

$ext2=$_POST['ext2'];
$ext2 = mb_convert_encoding((string)($ext2), 'ISO-8859-1', 'UTF-8');

if($ext1!=""){
	$foto1=substr($_POST['foto1'], strpos($_POST['foto1'], ",") + 1);;
$foto1 = base64_decode($foto1);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto1,$id_foto,$ext1);
$ruta="/GoodVentaAsisCap/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext1;
CargaFoto("foto1",$ruta,$cod_persona);
}
if($ext2!=""){
	$foto2=substr($_POST['foto2'], strpos($_POST['foto2'], ",") + 1);;
$foto2 = base64_decode($foto2);
$id_foto="";		  
		     $donde="../fotos/fotoCedula/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto2,$id_foto,$ext2);
$ruta="/GoodVentaAsisCap/fotos/fotoCedula/".$cod_persona.$id_f.'.'.$ext2;
CargaFoto("foto2",$ruta,$cod_persona);
}




}

function CargaFoto($tableName,$Urlfoto,$cod_cliente){
	$mysqli=conectar_al_servidor();
	$consulta="Update cliente set ".$tableName."=? where cod_cliente=? ";	

	$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$Urlfoto,$cod_cliente); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	 mysqli_close($mysqli);
}

function obtenerCliente($filtros = array(), $limite = 0)
{
	$mysqli = conectar_al_servidor();
	$sqlFiltro = "";

	foreach ($filtros as $key => $value) {
		if ($value === null || $value === "") {
			continue;
		}

		if ($sqlFiltro == "") {
			$sqlFiltro .= "WHERE ";
		} else {
			$sqlFiltro .= " AND ";
		}

		switch ($key) {
			case 'cedula':
				$sqlFiltro .= "c.ci_cliente LIKE '%$value%' OR c.rut_cliente LIKE '%$value%'";
				break;
			case 'nombre_cedula_cliente':
				$sqlFiltro .= "(p.nombre_persona LIKE '%$value%' OR c.ci_cliente LIKE '%$value%' OR c.rut_cliente LIKE '%$value%')";
				break;
			case 'fecha_inicio':
				$sqlFiltro .= "c.fecha_insert >= '$value'";
				break;
			case 'fecha_fin':
				$sqlFiltro .= "c.fecha_insert <= '$value'";
				break;
			default:
				if (is_numeric($value)) {
					$sqlFiltro .= "$key = $value";
				} else {
					$sqlFiltro .= "$key LIKE '%$value%'";
				}
				break;
		}
	}

	if ($limite == 0) {
		$limite = '';
	} else {
		$limite = "LIMIT " . intval($limite);
	}

	$sql = "SELECT
			c.*,
			p.cod_persona,
			p.nombre_persona,
			p.direccion,
			p.telefono,
			p.email,
			(SELECT nombre FROM zona WHERE idzona = c.idzonaFk LIMIT 1) as zona,
			(SELECT nombre_persona FROM persona pra WHERE pra.cod_persona = c.cod_user_insert LIMIT 1) as insertadopor,
			(SELECT nombre_persona FROM persona pra WHERE pra.cod_persona = c.cod_user_edit LIMIT 1) as editadopor
			FROM cliente c
			INNER JOIN persona p ON c.cod_cliente = p.cod_persona
			$sqlFiltro ORDER BY c.cod_cliente DESC $limite";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		$informacion = array("1" => "error", "mensaje" => "Error al obtener cliente: " . $stmt->error, "sql" => $sql);
		echo json_encode($informacion);
		exit;
	}

	$result = $stmt->get_result();
	$registros = array();
	while ($row = $result->fetch_assoc()) {
		$reg = array();
		foreach ($row as $key => $value) {
			$reg[$key] = mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
		}
		$registros[] = $reg;
	}

	$stmt->close();
	mysqli_close($mysqli);
	return $registros;
};

function obtenerPaginaClienteVista($registros)
{
	$pagina = "";

	foreach ($registros as $value) {
		$stylefondo = "";
		if (isset($value['accesocredito']) && $value['accesocredito'] == "Denegado") {
			$stylefondo = "background-color:#ff5722;color:#fff";
		}

		$pagina .= "
<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>
<tr class='vista-cliente-row' id='trdatoClienteCi' onclick='obtenerdatosvistacliente(this)' style='$stylefondo'>
<td id='td_id' style='display:none'>".$value['cod_persona']."</td>
<td id='td_datos_2' data-label='Doc.'>".$value['ci_cliente']."</td>
<td id='td_datos_13' data-label='RUC'>".$value['rut_cliente']."</td>
<td id='td_datos_1' data-label='Cliente'>".$value['nombre_persona']."</td>
<td id='td_datos_10' style='display:none'>".$value['zona']."</td>
<td id='td_datos_3' data-label='Direccion'>".$value['direccion']."</td>
<td id='td_datos_4' data-label='Nro. telef.'>".$value['telefono']."</td>
<td id='td_datos_5' style='display:none'>".$value['email']."</td>
<td id='td_datos_6' style='display:none'>".$value['Calificacion']."</td>
<td id='td_datos_7' style='display:none'>".$value['whapp']."</td>
<td id='td_datos_8' style='display:none'>".$value['estado']."</td>
<td id='td_datos_9' style='display:none'>".$value['idzonaFk']."</td>
<td id='td_datos_11' style='display:none'>".$value['foto1']."</td>
<td id='td_datos_12' style='display:none'>".$value['foto2']."</td>
<td id='td_datos_14' style='display:none'>".$value['accesocredito']."</td>
<td id='td_datos_15' style='display:none'>".$value['totaldias']."</td>
<td id='td_datos_16' style='display:none'>".$value['lugardetrabajo']."</td>
<td id='td_datos_17' style='display:none'>".$value['salario']."</td>
<td id='td_datos_18' style='display:none'>".$value['antiguedad']."</td>
<td id='td_datos_19' style='display:none'>".$value['teleftrab1']."</td>
<td id='td_datos_20' style='display:none'>".$value['teleftrab2']."</td>
<td id='td_datos_21' style='display:none'>".$value['direcciontrab']."</td>
<td id='td_datos_22' style='display:none'>".$value['fechanac']."</td>
</tr>
</table>";
	}

	return $pagina;
}



/*Buscar Registro en vista*/
function BuscarRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito)
{
$mysqli=conectar_al_servidor();

$condicionCodigo="";
if($codigo!=""){
$condicionCodigo="and pr.cod_persona = '".$codigo."' ";
}
$condiciondocumento="";
if($documento!=""){
$condiciondocumento="and cl.ci_cliente= '".$documento."' ";
}
$condicioncliente="";
if($cliente!=""){
$condicioncliente="and pr.nombre_persona like '%".$cliente."%' ";
}
$condicionzona="";
if($zona!=""){
$condicionzona="and cl.idzonaFk= '".$zona."' ";
}
$condicionaccesocredito="";
if($accesocredito!=""){
$condicionaccesocredito="and cl.accesocredito= '".$accesocredito."' ";
}


$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,
cl.estado,cl.idzonaFk,foto1,foto2,cl.lugardetrabajo,cl.salario,cl.antiguedad,cl.teleftrab1,cl.teleftrab2,cl.direcciontrab,cl.accesocredito,cl.fechanac,
(Select nombre from zona where idzonaFk=idzona )as zona,cl.fecha_insert,cl.fecha_edit,cl.sms,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado=? ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionCodigo.$condicionaccesocredito." order by pr.nombre_persona limit 100";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$estado);

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


$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1'); 
$sms = mb_convert_encoding((string)($valor['sms']), 'UTF-8', 'ISO-8859-1');  
$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$Calificacion = mb_convert_encoding((string)($valor['Calificacion']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$foto1 = mb_convert_encoding((string)($valor['foto1']), 'UTF-8', 'ISO-8859-1'); 
$foto2 = mb_convert_encoding((string)($valor['foto2']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 
$insertadopor = mb_convert_encoding((string)($valor['insertadopor']), 'UTF-8', 'ISO-8859-1'); 
$editadopor = mb_convert_encoding((string)($valor['editadopor']), 'UTF-8', 'ISO-8859-1'); 
$fecha_insert = mb_convert_encoding((string)($valor['fecha_insert']), 'UTF-8', 'ISO-8859-1'); 
$fecha_edit = mb_convert_encoding((string)($valor['fecha_edit']), 'UTF-8', 'ISO-8859-1'); 
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 
 $styleName=CargarStyleTable($styleName);
 
$StyleFoto="";
 if($foto1=="" || $foto2=="" ){
	 $StyleFoto=" style='background-color: #ff6b6b;color:white;' ";	 
 }
 
 
 
 
	  $pagina.="
<table class='$styleName' $StyleFoto border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosabmCliente(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_persona."</td>
<td  id='td_datos_13' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_2' style='display:none'>".$rut_cliente."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_10' style='width:10%'>".$zona."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_21' style='width:10%'>".$accesocredito."</td>
<td  id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_15' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_16' style='display:none'>".$salario."</td>
<td  id='td_datos_17' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_18' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_20' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
<td  id='td_datos_104' style='display:none'>".$sms."</td>
<td  id='td_datos_105' style='display:none'>".$fechanac."</td>
</tr>
</table>";


}
}

$sql= "select cl.whapp
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado=? ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionCodigo.$condicionaccesocredito." order by pr.nombre_persona";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($s,$estado);
if ( ! $stmt->execute()) {

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$totalregistro=$valor;

    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina),"3" => number_format($nroRegistro,'0',',','.'),"99" =>$nroRegistro,"100" =>$totalregistro );
echo json_encode($informacion);	
exit;
}

function BuscarMasRegistro($codigo,$documento,$cliente,$zona,$estado,$accesocredito,$registrocargado)
{
$mysqli=conectar_al_servidor();

$condicionCodigo="";
if($codigo!=""){
$condicionCodigo="and pr.cod_persona = '".$codigo."' ";
}
$condiciondocumento="";
if($documento!=""){
$condiciondocumento="and cl.ci_cliente= '".$documento."' ";
}
$condicioncliente="";
if($cliente!=""){
$condicioncliente="and pr.nombre_persona like '%".$cliente."%' ";
}
$condicionzona="";
if($zona!=""){
$condicionzona="and cl.idzonaFk= '".$zona."' ";
}

$condicionaccesocredito="";
if($accesocredito!=""){
$condicionaccesocredito="and cl.accesocredito= '".$accesocredito."' ";
}

$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,cl.accesocredito,
(Select nombre from zona where idzonaFk=idzona )as zona,cl.fecha_insert,cl.fecha_edit,cl.sms,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_insert )as insertadopor,
(Select nombre_persona from persona pra where pra.cod_persona=cod_user_edit )as editadopor
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado=? ".$condiciondocumento.$condicioncliente.$condicionzona.$condicionCodigo.$condicionaccesocredito." order by pr.nombre_persona limit ".$registrocargado." , 100 ";
$pagina = "";   
$stmt = $mysqli->prepare($sql);
$s='s';
$stmt->bind_param($s,$estado);

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


$sms = mb_convert_encoding((string)($valor['sms']), 'UTF-8', 'ISO-8859-1'); 
$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$Calificacion = mb_convert_encoding((string)($valor['Calificacion']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$foto1 = mb_convert_encoding((string)($valor['foto1']), 'UTF-8', 'ISO-8859-1'); 
$foto2 = mb_convert_encoding((string)($valor['foto2']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 
$insertadopor = mb_convert_encoding((string)($valor['insertadopor']), 'UTF-8', 'ISO-8859-1'); 
$editadopor = mb_convert_encoding((string)($valor['editadopor']), 'UTF-8', 'ISO-8859-1'); 
$fecha_insert = mb_convert_encoding((string)($valor['fecha_insert']), 'UTF-8', 'ISO-8859-1'); 
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 
$fecha_edit = mb_convert_encoding((string)($valor['fecha_edit']), 'UTF-8', 'ISO-8859-1'); 


$StyleFoto="";
 if($foto1=="" || $foto2=="" ){
	 $StyleFoto=" style='background-color: #ff6b6b;color:white;' ";	 
 }
 





 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' $StyleFoto border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmCliente(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_persona."</td>
<td  id='td_datos_13' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_2' style='display:none'>".$rut_cliente."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_10' style='width:10%'>".$zona."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_21' style='width:10%'>".$accesocredito."</td>
<td  id='td_datos_3' style='display:none'>".$direccion."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_15' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_16' style='display:none'>".$salario."</td>
<td  id='td_datos_17' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_18' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_20' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_100' style='display:none'>".$insertadopor."</td>
<td  id='td_datos_101' style='display:none'>".$editadopor."</td>
<td  id='td_datos_102' style='display:none'>".$fecha_insert."</td>
<td  id='td_datos_103' style='display:none'>".$fecha_edit."</td>
<td  id='td_datos_104' style='display:none'>".$sms."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina),"3" => number_format($nroRegistro,'0',',','.'),"99" =>$nroRegistro );
echo json_encode($informacion);	
exit;
}


/*Buscar Registro en vista*/
function buscarmasreferencias($buscar)
{
	
$mysqli=conectar_al_servidor();
$sql= "select idreferenciascliente, telef, direccion, referencias, observacion, cod_clienteFk , tipo from referenciascliente where cod_clienteFk='$buscar' ";
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


$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'); 
$telef = mb_convert_encoding((string)($valor['telef']), 'UTF-8', 'ISO-8859-1');     
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$referencias = mb_convert_encoding((string)($valor['referencias']), 'UTF-8', 'ISO-8859-1');          
$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1'); 
$cod_clienteFk = mb_convert_encoding((string)($valor['cod_clienteFk']), 'UTF-8', 'ISO-8859-1'); 

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosmasreferencias(this)'  name='tdMasReferencias'>
<td  id='td_datos_1' style='width:10%'>".$observacion."</td>
<td  id='td_datos_2' style='width:10%'>".$telef."</td>
<td  id='td_datos_4' style='width:10%'>".$referencias."</td>
<td id='td_datos_3' style='width:10%'>".$direccion."</td>
<td id='td_datos_5' style='width:10%'>".$tipo."</td>
</tr>
</table>";


}
}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) );
echo json_encode($informacion);	
exit;
}



function BuscarRegistroEnVista($ruc,$documento,$cliente,$telef)
{
$mysqli=conectar_al_servidor();
$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and pr.nombre_persona like '%$cliente%'";
}
$condiciondocumento="";
if($documento!=""){
	$condiciondocumento=" and cl.ci_cliente = '$documento'";
}
$condicionruc="";
if($ruc!=""){
	$condicionruc=" and cl.rut_cliente like '%$ruc%'";
}
$condiciontelef="";
if($telef!=""){
	$condiciontelef=" and pr.telefono like '%$telef%'";
}

$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente
,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,cl.fechanac,lugardetrabajo,salario,antiguedad,teleftrab1,teleftrab2,direcciontrab,
(Select nombre from zona where idzonaFk=idzona )as zona
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' ".$condicioncliente.$condiciondocumento.$condicionruc.$condiciontelef." order by pr.nombre_persona limit 500";
$pagina = "";   

$stmt = $mysqli->prepare($sql);

if ( ! $stmt->execute()) {

echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$registros= array();
 $styleName="tableRegistroSearch";
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1');  
$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$Calificacion = mb_convert_encoding((string)($valor['Calificacion']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$foto1 = mb_convert_encoding((string)($valor['foto1']), 'UTF-8', 'ISO-8859-1'); 
$foto2 = mb_convert_encoding((string)($valor['foto2']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 

$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1'); 
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1'); 
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1'); 
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1'); 
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1'); 

$registros[] = array(
	'fechanac' => $fechanac,
	'cod_persona' => $cod_persona,
	'nombre_persona' => $nombre_persona,
	'direccion' => $direccion,
	'telefono' => $telefono,
	'email' => $email,
	'rut_cliente' => $rut_cliente,
	'Calificacion' => $Calificacion,
	'whapp' => $whapp,
	'estado' => $estado,
	'idzonaFk' => $idzonaFk,
	'zona' => $zona,
	'foto1' => $foto1,
	'foto2' => $foto2,
	'ci_cliente' => $ci_cliente,
	'accesocredito' => $accesocredito,
	'lugardetrabajo' => $lugardetrabajo,
	'salario' => $salario,
	'antiguedad' => $antiguedad,
	'teleftrab1' => $teleftrab1,
	'teleftrab2' => $teleftrab2,
	'direcciontrab' => $direcciontrab,
);
$stylefondo="";
if($accesocredito=="Denegado"){
$stylefondo="background-color:#ff5722;color:#fff";	
}
 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro' onclick='obtenerdatosvistacliente(this)' style='$stylefondo'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_2' data-label='Doc.'>".$ci_cliente."</td>
<td  id='td_datos_13' data-label='RUC'>".$rut_cliente."</td>
<td id='td_datos_1' data-label='Cliente'>".$nombre_persona."</td>
<td  id='td_datos_10' style='display:none'>".$zona."</td>
<td  id='td_datos_3' data-label='Direccion'>".$direccion."</td>
<td  id='td_datos_4' data-label='Nro. telef.'>".$telefono."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_14' style='display:none'>".$accesocredito."</td>
<td  id='td_datos_22' style='display:none'>".$fechanac."</td>


<td  id='td_datos_15' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_16' style='display:none'>".$salario."</td>
<td  id='td_datos_17' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_18' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_20' style='display:none'>".$direcciontrab."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
return array("1" => "exito","2" =>($pagina),"3" => $nroRegistro, "4" => $registros);
}

function  buscarporci($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select cl.whapp,pr.cod_persona,pr.nombre_persona,pr.direccion,pr.telefono,pr.email,cl.ci_cliente,cl.rut_cliente,cl.Calificacion,cl.estado,cl.idzonaFk,foto1,foto2,cl.accesocredito,
(Select nombre from zona where idzonaFk=idzona )as zona ,
cl.totaldias,
cl.lugardetrabajo,
cl.salario,
cl.antiguedad,
cl.teleftrab1,
cl.fechanac,
cl.teleftrab2,
cl.direcciontrab
 from  persona pr inner join  cliente cl on cl.cod_cliente=pr.cod_persona 
where cl.estado='Activo' and cl.ci_cliente='$buscar' order by pr.nombre_persona limit 1";
$pagina = "";   

// echo($sql);
// exit;

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


$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1');  
$totaldias = mb_convert_encoding((string)($valor['totaldias']), 'UTF-8', 'ISO-8859-1');  
$lugardetrabajo = mb_convert_encoding((string)($valor['lugardetrabajo']), 'UTF-8', 'ISO-8859-1');  
$salario = mb_convert_encoding((string)($valor['salario']), 'UTF-8', 'ISO-8859-1');  
$antiguedad = mb_convert_encoding((string)($valor['antiguedad']), 'UTF-8', 'ISO-8859-1');  
$teleftrab1 = mb_convert_encoding((string)($valor['teleftrab1']), 'UTF-8', 'ISO-8859-1');  
$teleftrab2 = mb_convert_encoding((string)($valor['teleftrab2']), 'UTF-8', 'ISO-8859-1');  
$direcciontrab = mb_convert_encoding((string)($valor['direcciontrab']), 'UTF-8', 'ISO-8859-1');  
$cod_persona = mb_convert_encoding((string)($valor['cod_persona']), 'UTF-8', 'ISO-8859-1');     
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1');          
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');          
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$email = mb_convert_encoding((string)($valor['email']), 'UTF-8', 'ISO-8859-1'); 
$rut_cliente = mb_convert_encoding((string)($valor['rut_cliente']), 'UTF-8', 'ISO-8859-1'); 
$Calificacion = mb_convert_encoding((string)($valor['Calificacion']), 'UTF-8', 'ISO-8859-1'); 
$whapp = mb_convert_encoding((string)($valor['whapp']), 'UTF-8', 'ISO-8859-1'); 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$idzonaFk = mb_convert_encoding((string)($valor['idzonaFk']), 'UTF-8', 'ISO-8859-1'); 
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$foto1 = mb_convert_encoding((string)($valor['foto1']), 'UTF-8', 'ISO-8859-1'); 
$foto2 = mb_convert_encoding((string)($valor['foto2']), 'UTF-8', 'ISO-8859-1'); 
$ci_cliente = mb_convert_encoding((string)($valor['ci_cliente']), 'UTF-8', 'ISO-8859-1'); 
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1'); 
 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr class='tableRegistroSelec' id='trdatoClienteCi' onclick='obtenerdatosvistacliente(this)'>
<td id='td_id' style='display:none'>".$cod_persona."</td>
<td  id='td_datos_2' style='width:10%'>".$ci_cliente."</td>
<td  id='td_datos_13' style='width:10%'>".$rut_cliente."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_10' style='display:none'>".$zona."</td>
<td  id='td_datos_3' style='width:10%'>".$direccion."</td>
<td  id='td_datos_4' style='width:10%'>".$telefono."</td>
<td  id='td_datos_5' style='display:none'>".$email."</td>
<td  id='td_datos_6' style='display:none'>".$Calificacion."</td>
<td  id='td_datos_7' style='display:none'>".$whapp."</td>
<td  id='td_datos_8' style='display:none'>".$estado."</td>
<td  id='td_datos_9' style='display:none'>".$idzonaFk."</td>
<td  id='td_datos_11' style='display:none'>".$foto1."</td>
<td  id='td_datos_12' style='display:none'>".$foto2."</td>
<td  id='td_datos_14' style='display:none'>".$accesocredito."</td>
<td  id='td_datos_15' style='display:none'>".$totaldias."</td>
<td  id='td_datos_16' style='display:none'>".$lugardetrabajo."</td>
<td  id='td_datos_17' style='display:none'>".$salario."</td>
<td  id='td_datos_18' style='display:none'>".$antiguedad."</td>
<td  id='td_datos_19' style='display:none'>".$teleftrab1."</td>
<td  id='td_datos_20' style='display:none'>".$teleftrab2."</td>
<td  id='td_datos_21' style='display:none'>".$direcciontrab."</td>
<td  id='td_datos_22' style='display:none'>".$fechanac."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarmensajes($buscar)
{
$mysqli=conectar_al_servidor();

$sql= "select fecha,hora from mensajesenviados where idcliente='$buscar' limit 100 ";
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



$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');     
$hora = mb_convert_encoding((string)($valor['hora']), 'UTF-8', 'ISO-8859-1'); 

 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td  style='width:80%'>".$fecha."</td>
<td   style='width:20%'>".$hora."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}


function  buscarcuentaImpago($tipo,$fecha1,$fecha2,$local,$zona,$cliente,$cobrador)
{
$mysqli=conectar_al_servidor();


$condicionfecha="";
if($fecha1!="" || $fecha2!=""){
	if($tipo=="compromiso" ){
	$condicionfecha=" and fechaCompro between '$fecha1' and '$fecha2' ";
}else{
	$condicionfecha=" and fecha between '$fecha1' and '$fecha2 23:59:00' ";
}
}

$condicioncliente="";
if($cliente!=""){
	$condicioncliente=" and (select nombre_persona from persona where cod_persona = cod_clienteFK) like '%$cliente%'";
}
$condicioncobrador="";
if($cobrador!=""){
	$condicioncobrador=" and (select nombre_persona from persona where cod_persona = cod_cobradorFK) like '%$cobrador%'";
}
$condicionzona="";
if($zona!=""){
	$condicionzona=" and (select idzonaFk from cliente where cod_cliente = cod_clienteFK) = '$zona'";
}





$sql= "select fechaCompro, cod_VisitasCliente, fecha, Motivo, cod_clienteFK, cod_cobradorFK ,(select nombre_persona from persona where cod_persona = cod_cobradorFK) as cobrador , (select nombre_persona from persona where cod_persona = cod_clienteFK) as cliente , (select nombre from zona where idzona=(select idzonaFk from cliente where cod_cliente = cod_clienteFK)) as zona  from visitascliente  where cod_VisitasCliente!=''
".$condicioncliente.$condicioncobrador.$condicionzona.$condicionfecha." order by fechaCompro asc ";

// echo($sql);
// exit;
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



$Motivo = mb_convert_encoding((string)($valor['Motivo']), 'UTF-8', 'ISO-8859-1');     
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1'); 
$cliente = mb_convert_encoding((string)($valor['cliente']), 'UTF-8', 'ISO-8859-1');     
$zona = mb_convert_encoding((string)($valor['zona']), 'UTF-8', 'ISO-8859-1'); 
$cobrador = mb_convert_encoding((string)($valor['cobrador']), 'UTF-8', 'ISO-8859-1');  
$fechaCompro = mb_convert_encoding((string)($valor['fechaCompro']), 'UTF-8', 'ISO-8859-1');    


 $styleName=CargarStyleTable($styleName);
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro'>
<td  style='width:15%'>".$fecha."</td>
<td   style='width:25%'>".$cliente."</td>
<td  style='width:30%'>".$Motivo."</td>
<td   style='width:15%'>".$cobrador."</td>
<td   style='width:15%'>".$fechaCompro."</td>
</tr>
</table>";


}
}
     mysqli_close($mysqli);
$informacion =array("1" => "exito","2" =>($pagina),"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}




function buscarcumpleCliente($Fecha,$Zona)
{
	
$mysqli=conectar_al_servidor();

$condicionFecha="";
if($Fecha!=""){
	$Fecha2=substr($Fecha, 5, 2);
	$condicionFecha=" and  DATE_FORMAT(fechanac, '%m') = $Fecha2 ";
}
$condicionZona="";
if($Zona!=""){
	$condicionZona=" and idzonaFk = '$Zona'";
}

$sql= "SELECT DATE_FORMAT(fechanac, '%m-%d') as FechaNac,DATE_FORMAT(fechanac, '%m') as mesNacimiento, accesocredito,
				(select nombre_persona from persona where cod_cliente=cod_persona) as Nombrecliente ,
				(Select telefono from persona where cod_persona=cod_cliente) as Telefono,
			(select concat(puntoexpedicion,'-',num_factura) from venta where cod_cliente=cod_clienteFK order by fecha_venta desc limit 1) as Venta1 ,
			(select total_venta from venta where cod_cliente=cod_clienteFK order by fecha_venta desc limit 1) as Venta2 ,sms,
				(select nombre from zona where idzona=idzonaFk) as Zona ,fechanac,CONCAT(  case
                  when MONTH(fechanac) < MONTH(CURDATE()) then YEAR(CURDATE()) + 1
                  when MONTH(fechanac) > MONTH(CURDATE()) then YEAR(CURDATE())
                  when DAY(fechanac) <= DAY(CURDATE()) then YEAR(CURDATE()) + 1
                  else YEAR(CURDATE())
                end
              , '-', MONTH(fechanac)
              , '-', DATE_FORMAT(fechanac, '%d')
             ) as cumple 
  FROM cliente WHERE fechanac != '0000-00-00' ".$condicionFecha.$condicionZona." order by cumple asc";
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
 
$MensajeFelicita="";
$MensajePromo="";
 
if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$FechaNac = mb_convert_encoding((string)($valor['FechaNac']), 'UTF-8', 'ISO-8859-1');
$accesocredito = mb_convert_encoding((string)($valor['accesocredito']), 'UTF-8', 'ISO-8859-1');
$mesNacimiento = mb_convert_encoding((string)($valor['mesNacimiento']), 'UTF-8', 'ISO-8859-1'); 
$Nombrecliente = mb_convert_encoding((string)($valor['Nombrecliente']), 'UTF-8', 'ISO-8859-1');     
$cumple = mb_convert_encoding((string)($valor['cumple']), 'UTF-8', 'ISO-8859-1');          
$fechanac = mb_convert_encoding((string)($valor['fechanac']), 'UTF-8', 'ISO-8859-1');          
$Zona = mb_convert_encoding((string)($valor['Zona']), 'UTF-8', 'ISO-8859-1'); 
$Venta1 = mb_convert_encoding((string)($valor['Venta1']), 'UTF-8', 'ISO-8859-1'); 
$Venta2 = mb_convert_encoding((string)($valor['Venta2']), 'UTF-8', 'ISO-8859-1'); 
$Telefono = mb_convert_encoding((string)($valor['Telefono']), 'UTF-8', 'ISO-8859-1'); 
$sms = mb_convert_encoding((string)($valor['sms']), 'UTF-8', 'ISO-8859-1'); 


		if($Telefono!=""){
			$condicion=$Telefono[0];
		}else{
			$condicion="";
		}
		
$codigo="595";
if($condicion=="+"){
	$codigo="";
}

if($Telefono!="0" && $Telefono!=""){
	
	$Telefono = substr($Telefono, 1);
	
$searchString = " ";
$replaceString = "";
 
$Telefono = str_replace($searchString, $replaceString, $Telefono); 
	
if($sms=="SI"){
	
	if($Fecha==""){
		$fechacumple=date('m-d');
	}else{
		$fechacumple=substr($Fecha, 5, 5);
	}
	if($FechaNac==$fechacumple && $accesocredito=="Confirmado"){
	$Mensaje1="";
	 $MensajeFelicita.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td  style='width:30%'>".$codigo.$Telefono."</td>
<td   style='width:30%'></td>
<td   style='width:40%'>".$Mensaje1."</td>
</tr>
</table>";
	}
	
	if($Fecha==""){
		$Mescumple=date('m');
	}else{
		$Mescumple=substr($Fecha, 5, 2);
	}
	
	if($accesocredito=="Confirmado" && $mesNacimiento==$Mescumple ){
	$Mensaje2="";
	$MensajePromo.="
<table class='tableRegistroSearch' border='0' cellspacing='0' cellpadding='0'>
<tr id='tbSelecRegistro'   >
<td  style='width:30%'>".$codigo.$Telefono."</td>
<td   style='width:30%'></td>
<td   style='width:40%'>".$Mensaje2."</td>
</tr>
</table>";
}
	}
}
	 $styleName=CargarStyleTable($styleName);
	 
	 if($Venta2!=""){
		 $Venta2=number_format($Venta2,'0',',','.');
	 }
	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5' >
<tr id='tbSelecRegistro'>
<td  id='td_datos_1' style='width:20%'>".$fechanac."</td>
<td  id='td_datos_2' style='width:30%'>".$Nombrecliente."</td>
<td  id='td_datos_3' style='width:20%'>".$cumple."</td>
<td id='td_datos_4' style='width:15%'>".$Zona."</td>
<td id='td_datos_5' style='width:15%'>".$Venta1."/".$Venta2."</td>
</tr>
</table>";
	
	
}


}


    mysqli_close($mysqli);  
$informacion =array("1" => "exito","2" => ($pagina) ,"3" => ($nroRegistro),"4" => ($MensajeFelicita) ,"5" => ($MensajePromo) );
echo json_encode($informacion);	
exit;
}


function addImagenes($idcontratofk){
$control=1;
$totalregistrodoc=$_POST['totalregistro'];
$totalregistrodoc = mb_convert_encoding((string)($totalregistrodoc), 'ISO-8859-1', 'UTF-8');

$mysqli=conectar_al_servidor();
while($control<=$totalregistrodoc){

$archivo=$_POST['archivo'.$control];
$archivo = mb_convert_encoding((string)($archivo), 'ISO-8859-1', 'UTF-8');

$ext=$_POST['ext'.$control];
$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');

$descripcion=$_POST['descripcion'.$control];
$descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');

$fecha=$_POST['fecha'.$control];
$fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');

$control++;

insertardocumento($idcontratofk,$ext,$archivo,$descripcion,$fecha);
}

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function insertardocumento($cod_detalle,$exte,$archivo,$descripcion,$fecha)
{
	$documento=substr($archivo, strpos($archivo, ",") + 1);;
	$documento = base64_decode($documento);
	
	$id_documento=rand(10,5000);		  
	$donde="../fotos/FotosDocumento/";
	$id_documento=$cod_detalle;
	
	$id_f=subir_imagen_base64($donde,$documento,$id_documento,$exte);
	$ruta="/GoodVentaAsisCap/fotos/FotosDocumento/".$cod_detalle.$id_f.'.'.$exte;
	
	CargaDocumento($ruta,$cod_detalle,$descripcion,$fecha);
}
function CargaDocumento($Urldoc,$idcontratofk,$descripcion,$fecha){
	$params= "url,cod_clienteFK,descripcion,fecha";
	$valores= "'$Urldoc','$idcontratofk','$descripcion','$fecha'";

	if (isset($_POST['codVenta']) && !empty($_POST['codVenta'])) {
		$params .= ',cod_ventaFK';
		$valores .= ", ".$_POST['codVenta'];
	}

	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO fotos_cliente ($params) VALUES ($valores)";
	
$stmt = $mysqli->prepare($consulta);



if ( ! $stmt->execute()) {
   echo "Error";
}
	
}



function buscarDocumentos($codigo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
		$sql= "SELECT *
				FROM fotos_cliente where cod_clienteFK='$codigo'";
  
   
   $stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $nroRegistro= $valor;
 $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $iddocumento=$valor['idfotos_cliente'];
		  	  $archivourl=mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1');
		  	  $descripcion=mb_convert_encoding((string)($valor['descripcion']), 'UTF-8', 'ISO-8859-1');
		  	  $fecha=mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');
		  	  $idcontratoFK=$valor['cod_clienteFK'];
		  	 
		  	 
			  $codigo= substr(str_shuffle($permitted_chars), 0, 5);
			  
			  
		  	  $pagina.="
<table id='$codigo' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistroImagen' onclick='SeleccionarItemImagen(this)' name='tdDetalleItemImagen'>
<td id='td_id_1' style='display:none'>".$codigo."</td>
<td id='td_id_2' style='display:none'>".$iddocumento."</td>
<td id='td_id_3' style='display:none'>".$idcontratoFK."</td>
<td id='td_datos_1' style='display:none'>".$archivourl."</td>
<td id='' style='width:20%'>IMAGEN</td>
<td id='td_datos_2' style='width:60%'>".$descripcion."</td>
<td id='td_datos_3' style='width:20%'>".$fecha."</td>
</tr>
</table>";
			  
			  $codigo="";
	  }
 }
 
 
 mysqli_close($mysqli);
 $informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;


}


function EliminarDocumento($idcontratoFK,$iddocumento,$urldocumento)
{
	$mysqli=conectar_al_servidor();
	$sql= "DELETE FROM fotos_cliente WHERE cod_clienteFK='$idcontratoFK' and idfotos_cliente='$iddocumento'";
 
 
 $file_delete = dirname(__FILE__, 2) . $urldocumento;
 
  
  $control = "Fracaso al borrar";
 
 if (file_exists($file_delete)) {
	 if(unlink($file_delete)){
		 $control = "exito";
	 }
	 }
   

   
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
 
	
 
mysqli_close($mysqli);
 $informacion =array("1" => $control);
echo json_encode($informacion);	
exit;


}

function  buscar_antecedente_consulta($cod_clienteFK,$cod_ventaFK)
{
$mysqli=conectar_al_servidor();

$sql="SELECT observacion, idantecedente_paciente , estado ,(select nombre_persona from persona where cod_persona=cod_usuario) as usuario,  fecha FROM antecedente_paciente WHERE cod_clienteFK = '$cod_clienteFK' and cod_ventaFK = '$cod_ventaFK'";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1');   
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');   
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');   
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');   
$idantecedente_paciente = mb_convert_encoding((string)($valor['idantecedente_paciente']), 'UTF-8', 'ISO-8859-1');   
 
 

	 $button ="<button class='btn btn-success' id='$idantecedente_paciente' onclick='cambiarEstadoAntecedenteConsulta(this)' value=''>X</button>";
	 $styletext='';
 
 
 if($estado =='Inactivo'){
	$styletext="style='text-decoration: line-through;'";
	$button = "";
 }


	  $pagina.="
<style>
.timeline {
  position: relative;
  margin: 2px 0;
  padding-left: 5px;
  border-left: 3px solid #4a90e2;
}
.timeline-item {
  position: relative;
  margin-bottom: 2px;
  
  display:flex;
  justify-content: space-between;
}
.timeline-item::before {
  content: '';
  position: absolute;
  left: -8px;
  top: 4px;
  width: 14px;
  height: 14px;
  background-color: #4a90e2;
  border-radius: 50%;
}
.timeline-content {
  background-color: #f9f9f9;
  padding: 5px 7px;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
  
  flex: 1;
}
.timeline-content .description {
  font-weight: bold;
  margin-bottom: 2px;
}
.timeline-content .meta {
  font-size: 12px;
  color: #666;
  border-top: 1px solid #ddd;
  margin-top: 2px;
  padding-top: 2px;
}
</style>

<div class='timeline'>
  <div class='timeline-item'>
    <div class='timeline-content'>
      <div class='description'  $styletext >
         ".htmlspecialchars($observacion)."
      </div>
	  <div class='meta'>
       ".htmlspecialchars($usuario)." - ".htmlspecialchars($fecha)."
      </div>
    </div>
	
	$button
	
  </div>
</div>

"; 
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}

function cargar_antecedente_paciente($cod_ventaFK,$cod_clienteFK,$observacion){
	
	$user=$_POST['useru'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');

	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO antecedente_paciente (cod_ventaFK,cod_clienteFK,observacion,cod_usuario,estado,fecha) values ('$cod_ventaFK','$cod_clienteFK','$observacion','$user','Activo',now())";
	
$stmt = $mysqli->prepare($consulta);
 
if ( ! $stmt->execute()) {
   echo "Error";
}
 
	mysqli_close($mysqli);
 $informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;
}


function  buscar_antecedente_resumen_consulta($cod_clienteFK,$cod_ventaFK)
{
$mysqli=conectar_al_servidor();

$sql="SELECT observacion , estado ,(select nombre_persona from persona where cod_persona=cod_usuario) as usuario,  fecha FROM antecedente_paciente WHERE cod_clienteFK = '$cod_clienteFK' and cod_ventaFK = '$cod_ventaFK' and estado = 'Activo'";

 
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo telar_trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}
 
$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$pagina="";

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  

$observacion = mb_convert_encoding((string)($valor['observacion']), 'UTF-8', 'ISO-8859-1');   
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1');   
$usuario = mb_convert_encoding((string)($valor['usuario']), 'UTF-8', 'ISO-8859-1');   
$fecha = mb_convert_encoding((string)($valor['fecha']), 'UTF-8', 'ISO-8859-1');   
 
 

	  $pagina.="<p class='tarjeta-consulta consulta-item' style='border-left: 5px solid #ff5722;'> <b> 
         ".htmlspecialchars($observacion)." </b>  <br>".htmlspecialchars($usuario)." - ".htmlspecialchars($fecha)." </p>

"; 
 
}
}
 
$informacion =array("1" => "exito","2" => $pagina );
echo json_encode($informacion);	
exit;
}


ObtenerDatos($operacion);

?>

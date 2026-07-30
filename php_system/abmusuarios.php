<?php
require_once("conexion.php");
require_once("solicitud_eliminado_helper.php");
include_once("verificar_navegador.php");
include_once("subir_foto_base64.php");
include_once("buscar_nivel.php");
include_once("classTable.php");

date_default_timezone_set('America/Asuncion');

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

if($operacion=="buscarMecanicosDisponiblesAlta"){
	buscarMecanicosDisponiblesAltaFuncionario($user);
}



if($operacion=="nuevo" || $operacion=="editar" || $operacion=="eliminar")
{


$cod_persona=$_POST['cod_persona'];
$cod_persona = mb_convert_encoding((string)($cod_persona), 'ISO-8859-1', 'UTF-8');

$nombre_persona=$_POST['nombre_persona'];
$nombre_persona = mb_convert_encoding((string)($nombre_persona), 'ISO-8859-1', 'UTF-8');

$telefono=$_POST['telefono'];
$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');

$rut_usuario=$_POST['rut_usuario'];
$rut_usuario = mb_convert_encoding((string)($rut_usuario), 'ISO-8859-1', 'UTF-8');

$cod_usuario=$cod_persona;

$login=$_POST['login'];
$login = mb_convert_encoding((string)($login), 'ISO-8859-1', 'UTF-8');

$password=$_POST['password'];
$password = mb_convert_encoding((string)($password), 'ISO-8859-1', 'UTF-8');


$tipo=$_POST['tipo'];
$tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

$estado=$_POST['estado'];

$acceso=$_POST['acceso'];

$cod_localFK=$_POST['cod_localFK'];

$foto=$_POST['foto'];
$foto = mb_convert_encoding((string)($foto), 'ISO-8859-1', 'UTF-8');
$ext=$_POST['ext'];
$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
$telefono_referencia= $_POST['telefono_referencia'];
$telefono_referencia = mb_convert_encoding((string)($telefono_referencia), 'ISO-8859-1', 'UTF-8');
$direccion= $_POST['direccion'];
$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
$tipo_relacion=$_POST['tipo_relacion'];
$tipo_relacion = mb_convert_encoding((string)($tipo_relacion), 'ISO-8859-1', 'UTF-8');
$fecha_creacion = $_POST['fecha_creacion'];
$fecha_creacion = mb_convert_encoding((string)($fecha_creacion), 'ISO-8859-1', 'UTF-8');
$fecha_vencimiento_contrato = isset($_POST['fecha_vencimiento_contrato']) ? $_POST['fecha_vencimiento_contrato'] : "";
$fecha_vencimiento_contrato = mb_convert_encoding((string)($fecha_vencimiento_contrato), 'ISO-8859-1', 'UTF-8');
$mecanico_vinculo = isset($_POST['mecanico_vinculo']) ? $_POST['mecanico_vinculo'] : "";
$mecanico_vinculo = mb_convert_encoding((string)($mecanico_vinculo), 'ISO-8859-1', 'UTF-8');

$horarios_usuario = obtenerHorariosUsuarioPost();

abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$foto,$ext,$telefono_referencia,$direccion,$tipo_relacion,$fecha_creacion,$fecha_vencimiento_contrato,$horarios_usuario,$user,$operacion,$mecanico_vinculo);
}

 
 
 if($operacion=="buscar"){
 	$codigo=$_POST["codigo"];
 	$codigo=mb_convert_encoding((string)($codigo), 'ISO-8859-1', 'UTF-8');
	$documento=$_POST["documento"];
 	$documento=mb_convert_encoding((string)($documento), 'ISO-8859-1', 'UTF-8');
	$usuario=$_POST["usuario"];
 	$usuario=mb_convert_encoding((string)($usuario), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST["estado"];
 	$estado=mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$local=$_POST["local"];
 	$local=mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8');
 	BuscarRegistro($codigo,$documento,$usuario,$estado,$local);
 }

 if($operacion=="buscarfuncionario"){
 	$buscar=$_POST["buscar"];
 	$buscar=mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
	$tipo=$_POST["tipo"];
 	$tipo=mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');

 	buscarfuncionario($buscar,$tipo);
 }


	
if($operacion=="editarMisDatos")
{
	$Cod_Usuario=$_POST['useru'];
    $Cod_Usuario = mb_convert_encoding((string)($Cod_Usuario), 'ISO-8859-1', 'UTF-8');
	$user=$_POST['user'];
    $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
    $pass=$_POST['pass'];
    $pass = mb_convert_encoding((string)($pass), 'ISO-8859-1', 'UTF-8');  
	$local=$_POST['local'];
    $local = mb_convert_encoding((string)($local), 'ISO-8859-1', 'UTF-8'); 
	$nombre=$_POST['nombre'];
    $nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');   
	$cedula=$_POST['cedula'];
	$cedula = mb_convert_encoding((string)($cedula), 'ISO-8859-1', 'UTF-8');
	$foto=$_POST['foto'];
	$foto = mb_convert_encoding((string)($foto), 'ISO-8859-1', 'UTF-8');
	$ext=$_POST['ext'];
	$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8'); 
	$telefono_referencia=$_POST['telefono_referencia'];
	$telefono_referencia = mb_convert_encoding((string)($telefono_referencia), 'ISO-8859-1', 'UTF-8');
	$telefono=$_POST['telefono'];
	$telefono = mb_convert_encoding((string)($telefono), 'ISO-8859-1', 'UTF-8');
	$direccion=$_POST['direccion'];
	$direccion = mb_convert_encoding((string)($direccion), 'ISO-8859-1', 'UTF-8');
	
	editarmisdatos($Cod_Usuario,$user,$pass,$local,$nombre, $foto, $ext, $telefono, $direccion,$telefono_referencia,$cedula);

}
if ($operacion == "obtenerHistorialUsuario" || $operacion == "obtenerHistorialUsuarios") {
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');

	$result= obtenerUsuariosAnteriores(array('cod_usuarioFK' => $cod_usuarioFK));
	$pagina= "";
	foreach ($result as $valor) {
		$pagina .= '<tr>
			<td style="width: 10%;">'.$valor["id"].'</td>
			<td style="width: 50%;">'.$valor["nombre_persona"].'</td>
			<td style="width: 40%;">'.$valor["telefono"].'</td>
			<td style="width: 40%;">'.$valor["fecha_cambio"].'</td>
		</tr>';
	}

	echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $result));
	exit;
}

if ($operacion == "obtenerHistorialCambiosUsuario") {
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	obtenerHistorialCambiosUsuario($cod_usuarioFK);
}

if ($operacion == "buscarDocumentosLegajoUsuario") {
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	buscarDocumentosLegajoUsuario($cod_usuarioFK);
}

if ($operacion == "buscarMisDocumentosLegajoUsuario") {
	buscarDocumentosLegajoUsuario($user);
}

if ($operacion == "guardarDocumentoLegajoUsuario") {
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$tipo_documento=$_POST['tipo_documento'];
    $tipo_documento = mb_convert_encoding((string)($tipo_documento), 'ISO-8859-1', 'UTF-8');
	$nombre_documento=$_POST['nombre_documento'];
    $nombre_documento = mb_convert_encoding((string)($nombre_documento), 'ISO-8859-1', 'UTF-8');
	$nombre_archivo=$_POST['nombre_archivo'];
    $nombre_archivo = mb_convert_encoding((string)($nombre_archivo), 'ISO-8859-1', 'UTF-8');
	$archivo=$_POST['archivo'];
	$ext=$_POST['ext'];
    $ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$observacion=$_POST['observacion'];
    $observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
	$fecha_inicio_contrato=isset($_POST['fecha_inicio_contrato']) ? $_POST['fecha_inicio_contrato'] : "";
    $fecha_inicio_contrato = mb_convert_encoding((string)($fecha_inicio_contrato), 'ISO-8859-1', 'UTF-8');
	$fecha_fin_contrato=isset($_POST['fecha_fin_contrato']) ? $_POST['fecha_fin_contrato'] : "";
    $fecha_fin_contrato = mb_convert_encoding((string)($fecha_fin_contrato), 'ISO-8859-1', 'UTF-8');
	$contrato_sin_vencimiento=isset($_POST['contrato_sin_vencimiento']) ? $_POST['contrato_sin_vencimiento'] : "0";
    $contrato_sin_vencimiento = mb_convert_encoding((string)($contrato_sin_vencimiento), 'ISO-8859-1', 'UTF-8');
	guardarDocumentoLegajoUsuario($cod_usuarioFK,$tipo_documento,$nombre_documento,$nombre_archivo,$archivo,$ext,$estado,$observacion,$user,"Legajo RRHH",$fecha_inicio_contrato,$fecha_fin_contrato,$contrato_sin_vencimiento);
}

if ($operacion == "guardarMiDocumentoLegajoUsuario") {
	$tipo_documento=$_POST['tipo_documento'];
    $tipo_documento = mb_convert_encoding((string)($tipo_documento), 'ISO-8859-1', 'UTF-8');
	$nombre_documento=$_POST['nombre_documento'];
    $nombre_documento = mb_convert_encoding((string)($nombre_documento), 'ISO-8859-1', 'UTF-8');
	$nombre_archivo=$_POST['nombre_archivo'];
    $nombre_archivo = mb_convert_encoding((string)($nombre_archivo), 'ISO-8859-1', 'UTF-8');
	$archivo=$_POST['archivo'];
	$ext=$_POST['ext'];
    $ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
	$observacion=isset($_POST['observacion']) ? $_POST['observacion'] : "";
    $observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
	$fecha_inicio_contrato=isset($_POST['fecha_inicio_contrato']) ? $_POST['fecha_inicio_contrato'] : "";
    $fecha_inicio_contrato = mb_convert_encoding((string)($fecha_inicio_contrato), 'ISO-8859-1', 'UTF-8');
	$fecha_fin_contrato=isset($_POST['fecha_fin_contrato']) ? $_POST['fecha_fin_contrato'] : "";
    $fecha_fin_contrato = mb_convert_encoding((string)($fecha_fin_contrato), 'ISO-8859-1', 'UTF-8');
	$contrato_sin_vencimiento=isset($_POST['contrato_sin_vencimiento']) ? $_POST['contrato_sin_vencimiento'] : "0";
    $contrato_sin_vencimiento = mb_convert_encoding((string)($contrato_sin_vencimiento), 'ISO-8859-1', 'UTF-8');
	guardarDocumentoLegajoUsuario($user,$tipo_documento,$nombre_documento,$nombre_archivo,$archivo,$ext,"En revision",$observacion,$user,"Mi perfil",$fecha_inicio_contrato,$fecha_fin_contrato,$contrato_sin_vencimiento);
}

if ($operacion == "actualizarEstadoDocumentoLegajoUsuario") {
	$id_documento=$_POST['id_documento'];
    $id_documento = mb_convert_encoding((string)($id_documento), 'ISO-8859-1', 'UTF-8');
	$cod_usuarioFK=$_POST['cod_usuarioFK'];
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$estado=$_POST['estado'];
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	actualizarEstadoDocumentoLegajoUsuario($id_documento,$cod_usuarioFK,$estado,$user);
}

if ($operacion == "buscarSolicitudesAusenciaUsuario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	buscarSolicitudesAusenciaUsuario($cod_usuarioFK,$user);
}

if ($operacion == "buscarMisSolicitudesAusenciaUsuario") {
	buscarSolicitudesAusenciaUsuario($user,$user);
}

if ($operacion == "guardarSolicitudAusenciaUsuario" || $operacion == "guardarMiSolicitudAusenciaUsuario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) && $_POST['cod_usuarioFK'] !== "" ? $_POST['cod_usuarioFK'] : $user;
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$tipo=isset($_POST['tipo']) ? $_POST['tipo'] : "";
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	$fecha_desde=isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : "";
    $fecha_desde = mb_convert_encoding((string)($fecha_desde), 'ISO-8859-1', 'UTF-8');
	$fecha_hasta=isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : "";
    $fecha_hasta = mb_convert_encoding((string)($fecha_hasta), 'ISO-8859-1', 'UTF-8');
	$hora_desde=isset($_POST['hora_desde']) ? $_POST['hora_desde'] : "";
    $hora_desde = mb_convert_encoding((string)($hora_desde), 'ISO-8859-1', 'UTF-8');
	$hora_hasta=isset($_POST['hora_hasta']) ? $_POST['hora_hasta'] : "";
    $hora_hasta = mb_convert_encoding((string)($hora_hasta), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : "";
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	$nombre_archivo=isset($_POST['nombre_archivo']) ? $_POST['nombre_archivo'] : "";
    $nombre_archivo = mb_convert_encoding((string)($nombre_archivo), 'ISO-8859-1', 'UTF-8');
	$archivo=isset($_POST['archivo']) ? $_POST['archivo'] : "";
	$ext=isset($_POST['ext']) ? $_POST['ext'] : "";
    $ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
	guardarSolicitudAusenciaUsuario($cod_usuarioFK,$tipo,$fecha_desde,$fecha_hasta,$hora_desde,$hora_hasta,$motivo,$nombre_archivo,$archivo,$ext,$user,$operacion == "guardarMiSolicitudAusenciaUsuario" ? "Mi perfil" : "Legajo RRHH");
}

if ($operacion == "responderSolicitudAusenciaUsuario") {
	$id_solicitud=isset($_POST['id_solicitud']) ? $_POST['id_solicitud'] : "";
    $id_solicitud = mb_convert_encoding((string)($id_solicitud), 'ISO-8859-1', 'UTF-8');
	$estado=isset($_POST['estado']) ? $_POST['estado'] : "";
    $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');
	$observacion=isset($_POST['observacion']) ? $_POST['observacion'] : "";
    $observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
	responderSolicitudAusenciaUsuario($id_solicitud,$estado,$observacion,$user);
}

if ($operacion == "cancelarSolicitudAusenciaUsuario") {
	$id_solicitud=isset($_POST['id_solicitud']) ? $_POST['id_solicitud'] : "";
    $id_solicitud = mb_convert_encoding((string)($id_solicitud), 'ISO-8859-1', 'UTF-8');
	cancelarSolicitudAusenciaUsuario($id_solicitud,$user);
}

if ($operacion == "buscarSancionesFuncionario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	buscarSancionesFuncionario($cod_usuarioFK,$user);
}

if ($operacion == "guardarSancionFuncionario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$fecha=isset($_POST['fecha']) ? $_POST['fecha'] : "";
    $fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
	$tipo=isset($_POST['tipo']) ? $_POST['tipo'] : "";
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : "";
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	$descripcion=isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
    $descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
	$observaciones=isset($_POST['observaciones']) ? $_POST['observaciones'] : "";
    $observaciones = mb_convert_encoding((string)($observaciones), 'ISO-8859-1', 'UTF-8');
	$notificacion=isset($_POST['notificacion']) ? $_POST['notificacion'] : "pendiente_firma";
    $notificacion = mb_convert_encoding((string)($notificacion), 'ISO-8859-1', 'UTF-8');
	$nombre_archivo=isset($_POST['nombre_archivo']) ? $_POST['nombre_archivo'] : "";
    $nombre_archivo = mb_convert_encoding((string)($nombre_archivo), 'ISO-8859-1', 'UTF-8');
	$archivo=isset($_POST['archivo']) ? $_POST['archivo'] : "";
	$ext=isset($_POST['ext']) ? $_POST['ext'] : "";
    $ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
	guardarSancionFuncionario($cod_usuarioFK,$fecha,$tipo,$motivo,$descripcion,$observaciones,$notificacion,$nombre_archivo,$archivo,$ext,$user);
}

if ($operacion == "editarSancionFuncionario") {
	$id_sancion=isset($_POST['id_sancion']) ? $_POST['id_sancion'] : "";
    $id_sancion = mb_convert_encoding((string)($id_sancion), 'ISO-8859-1', 'UTF-8');
	$fecha=isset($_POST['fecha']) ? $_POST['fecha'] : "";
    $fecha = mb_convert_encoding((string)($fecha), 'ISO-8859-1', 'UTF-8');
	$tipo=isset($_POST['tipo']) ? $_POST['tipo'] : "";
    $tipo = mb_convert_encoding((string)($tipo), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : "";
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	$descripcion=isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
    $descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
	$observaciones=isset($_POST['observaciones']) ? $_POST['observaciones'] : "";
    $observaciones = mb_convert_encoding((string)($observaciones), 'ISO-8859-1', 'UTF-8');
	$notificacion=isset($_POST['notificacion']) ? $_POST['notificacion'] : "pendiente_firma";
    $notificacion = mb_convert_encoding((string)($notificacion), 'ISO-8859-1', 'UTF-8');
	editarSancionFuncionario($id_sancion,$fecha,$tipo,$motivo,$descripcion,$observaciones,$notificacion,$user);
}

if ($operacion == "anularSancionFuncionario") {
	$id_sancion=isset($_POST['id_sancion']) ? $_POST['id_sancion'] : "";
    $id_sancion = mb_convert_encoding((string)($id_sancion), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : "";
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	anularSancionFuncionario($id_sancion,$motivo,$user);
}

if ($operacion == "buscarSeguimientoFuncionario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	buscarSeguimientoFuncionario($cod_usuarioFK,$user);
}

if ($operacion == "vincularSeguimientoFuncionario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$cod_interConsultaFK=isset($_POST['cod_interConsultaFK']) ? $_POST['cod_interConsultaFK'] : "";
    $cod_interConsultaFK = mb_convert_encoding((string)($cod_interConsultaFK), 'ISO-8859-1', 'UTF-8');
	$observacion=isset($_POST['observacion']) ? $_POST['observacion'] : "";
    $observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : "";
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	vincularSeguimientoFuncionario($cod_usuarioFK,$cod_interConsultaFK,$observacion,$motivo,$user);
}

if ($operacion == "crearSeguimientoFuncionario") {
	$cod_usuarioFK=isset($_POST['cod_usuarioFK']) ? $_POST['cod_usuarioFK'] : "";
    $cod_usuarioFK = mb_convert_encoding((string)($cod_usuarioFK), 'ISO-8859-1', 'UTF-8');
	$asunto=isset($_POST['asunto']) ? $_POST['asunto'] : "";
    $asunto = mb_convert_encoding((string)($asunto), 'ISO-8859-1', 'UTF-8');
	$observacion=isset($_POST['observacion']) ? $_POST['observacion'] : "";
    $observacion = mb_convert_encoding((string)($observacion), 'ISO-8859-1', 'UTF-8');
	$motivo=isset($_POST['motivo']) ? $_POST['motivo'] : "";
    $motivo = mb_convert_encoding((string)($motivo), 'ISO-8859-1', 'UTF-8');
	crearSeguimientoFuncionario($cod_usuarioFK,$asunto,$observacion,$motivo,$user);
}

 if($operacion=="obtenermedicos"){ 
    $cod_venta=$_POST['cod_venta'];
    $cod_venta = mb_convert_encoding((string)($cod_venta), 'ISO-8859-1', 'UTF-8'); 
 	obtenermedicos($cod_venta);
 }

}



function obtenermedicos($cod_venta)
{
	$mysqli=conectar_al_servidor();
	 $pagina="";  
	
	$condicionLocal="";
	if($cod_venta!=""){
		$condicionLocal=" and cod_localFK='".$cod_venta."'";
	}
	
		$sql= "Select cod_usuario , nombre_persona from usuario inner join persona on cod_usuario=cod_persona where tipo='DOCTOR' ".$condicionLocal." order by nombre_persona asc ";
 
		   
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
		   
		  
		      $cod_usuario=$valor['cod_usuario'];
		  	  $nombre_persona=mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1'); 
		  	 		  	 
			  $pagina.="<option id='$cod_usuario' name='".$nombre_persona."' value='".$cod_usuario."' >$nombre_persona</option>";
			
		  	  
	  }
 }
 
  mysqli_close($mysqli);
  $informacion =array("1" => "exito","2" => $pagina);
echo json_encode($informacion);	
exit;


}







function editarmisdatos($Cod_Usuario,$user,$pass,$local,$nombre,$foto,$ext,$telefono,$direccion,$telefono_referencia,$cedula)
{
	
	if($Cod_Usuario=="" || $user=="" || $local==""|| $nombre=="" ){
$informacion =array("1" => "DI");
echo json_encode($informacion);	
exit;
	}

	$mysqli=conectar_al_servidor();
	$datosAnterioresAuditoria=obtenerDatosUsuarioAuditoria($mysqli,$Cod_Usuario);
	if($pass==""){
		$pass=isset($datosAnterioresAuditoria["password"]) ? $datosAnterioresAuditoria["password"] : "";
	}

	
	$consulta= "Select count(*) from usuario where login='$user' and password='$pass' and cod_localFK='$local' and Cod_Usuario!='$Cod_Usuario' ";

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

if($valor>0)
{
	$informacion =array("1" => "CI");
	echo json_encode($informacion);	
	exit;
}   
	

        
        
    
    $consulta="Update usuario set  login=?, password=?, rut_usuario=?	where Cod_Usuario=?";	
	$stmt = $mysqli->prepare($consulta);
    $ss='ssss';        
    $stmt->bind_param($ss,$user,$pass,$cedula,$Cod_Usuario);        
	
	
if ( ! $stmt->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}


$consulta1="Update persona set nombre_persona=?, telefono=?, direccion=?, telefono_referencia=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre,$telefono,$direccion,$telefono_referencia,$Cod_Usuario);

if ( ! $stmt1->execute() ) {
	$informacion =array("1" => "error");
	echo json_encode($informacion);	
	exit;
}

registrarHistorialCambiosUsuario($mysqli,$Cod_Usuario,$datosAnterioresAuditoria,array(
	"nombre_persona" => $nombre,
	"telefono" => $telefono,
	"direccion" => $direccion,
	"telefono_referencia" => $telefono_referencia,
	"rut_usuario" => $cedula,
	"login" => $user,
	"password" => $pass
),$Cod_Usuario,"Mi perfil");

// Copia la imagen al servidor y genera el enlace
if (!(empty($ext))) {
	$foto=substr($foto, strpos($foto, ",") + 1);
	$foto = base64_decode($foto);
	$id_foto="";		  
	$donde="../fotos/perfilUsuario/";
	$id_foto=$Cod_Usuario;
	$id_f=subir_imagen_base64($donde,$foto,$id_foto,$ext);
	$ruta="/GoodVentaAsisCap/fotos/perfilUsuario/".$Cod_Usuario.$id_f.'.'.$ext;
	CargaFoto("url",$ruta,$Cod_Usuario);
}

 mysqli_close($mysqli); 
$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

	
	
	
	
}

function normalizarFechaUsuarioContrato($fecha)
{
	$fecha = trim((string)$fecha);
	return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : NULL;
}

function asegurarCampoVencimientoContratoUsuario($mysqli)
{
	$sqlExiste = "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'usuario' AND column_name = ?";
	$stmt = $mysqli->prepare($sqlExiste);
	if (!$stmt) { return; }
	$columna = "fecha_vencimiento_contrato";
	$s = 's';
	$stmt->bind_param($s, $columna);
	if (!$stmt->execute()) {
		$stmt->close();
		return;
	}
	$total = 0;
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	if ((int)$total == 0) {
		$mysqli->query("ALTER TABLE usuario ADD COLUMN fecha_vencimiento_contrato DATE DEFAULT NULL AFTER fecha_creacion");
	}
}


function normalizarHoraUsuario($hora)
{
	$hora = trim((string)$hora);

	if ($hora == "") {
		return "";
	}

	if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
		return $hora.":00";
	}

	if (preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $hora)) {
		return $hora;
	}

	return "";
}

function asegurarEstructuraHorarioUsuarioEsperado($mysqli)
{
	$columnas = array(
		"tipo_jornada" => "VARCHAR(30) DEFAULT 'parcial'",
		"descanso_inicio" => "TIME DEFAULT NULL",
		"descanso_fin" => "TIME DEFAULT NULL",
		"horas_esperadas_minutos" => "INT DEFAULT NULL",
		"jornada_equivalente" => "DECIMAL(6,2) DEFAULT NULL",
		"vigente_desde" => "DATE DEFAULT NULL",
		"vigente_hasta" => "DATE DEFAULT NULL",
		"estado_horario" => "VARCHAR(15) DEFAULT 'activo'",
		"observacion" => "VARCHAR(255) DEFAULT NULL"
	);

	foreach ($columnas as $columna => $definicion) {
		$sqlExiste = "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'horario_usuario' AND column_name = ?";
		$stmt = $mysqli->prepare($sqlExiste);
		if (!$stmt) { continue; }
		$s = 's';
		$stmt->bind_param($s, $columna);
		if (!$stmt->execute()) {
			$stmt->close();
			continue;
		}
		$total = 0;
		$stmt->bind_result($total);
		$stmt->fetch();
		$stmt->close();
		if ((int)$total == 0) {
			$mysqli->query("ALTER TABLE horario_usuario ADD COLUMN ".$columna." ".$definicion);
		}
	}
}

function normalizarFechaHorarioUsuario($fecha)
{
	$fecha = trim((string)$fecha);
	return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : null;
}

function normalizarTipoJornadaUsuario($tipo)
{
	$tipo = trim((string)$tipo);
	$permitidos = array("completa", "medio_dia_manana", "medio_dia_tarde", "parcial", "noche", "especial", "no_laboral");
	return in_array($tipo, $permitidos, true) ? $tipo : "parcial";
}

function normalizarEstadoHorarioUsuario($estado)
{
	$estado = trim((string)$estado);
	return $estado == "inactivo" ? "inactivo" : "activo";
}

function calcularMinutosJornadaUsuario($hora_entrada, $hora_salida, $descanso_inicio, $descanso_fin, $tipo_jornada)
{
	if ($tipo_jornada == "no_laboral") {
		return 0;
	}

	$entrada = strtotime("2000-01-01 ".$hora_entrada);
	$salida = strtotime("2000-01-01 ".$hora_salida);
	if ($entrada === false || $salida === false) {
		return 0;
	}
	if ($salida <= $entrada && $tipo_jornada == "noche") {
		$salida = strtotime("2000-01-02 ".$hora_salida);
	}
	if ($salida <= $entrada) {
		return 0;
	}

	$minutos = (int)floor(($salida - $entrada) / 60);
	if ($descanso_inicio != "" && $descanso_fin != "") {
		$descansoInicio = strtotime("2000-01-01 ".$descanso_inicio);
		$descansoFin = strtotime("2000-01-01 ".$descanso_fin);
		if ($descansoInicio !== false && $descansoFin !== false && $descansoFin > $descansoInicio && $descansoInicio >= $entrada && $descansoFin <= $salida) {
			$minutos -= (int)floor(($descansoFin - $descansoInicio) / 60);
		}
	}

	return max(0, $minutos);
}

function obtenerHorariosUsuarioPost()
{
	$horariosJson = isset($_POST["horarios_usuario_json"]) ? json_decode((string)$_POST["horarios_usuario_json"], true) : array();
	$horarios = array();

	if (!is_array($horariosJson)) {
		return $horarios;
	}

	foreach ($horariosJson as $horario) {
		if (!is_array($horario)) {
			continue;
		}

		$dia = isset($horario["dia"]) ? (string)$horario["dia"] : "";
		$cod_localFK = isset($horario["cod_localFK"]) ? (string)$horario["cod_localFK"] : "";
		$tipo_jornada = normalizarTipoJornadaUsuario(isset($horario["tipo_jornada"]) ? $horario["tipo_jornada"] : "");
		$hora_entrada = isset($horario["hora_entrada"]) ? normalizarHoraUsuario($horario["hora_entrada"]) : "";
		$hora_salida = isset($horario["hora_salida"]) ? normalizarHoraUsuario($horario["hora_salida"]) : "";
		$descanso_inicio = isset($horario["descanso_inicio"]) ? normalizarHoraUsuario($horario["descanso_inicio"]) : "";
		$descanso_fin = isset($horario["descanso_fin"]) ? normalizarHoraUsuario($horario["descanso_fin"]) : "";
		$vigente_desde = normalizarFechaHorarioUsuario(isset($horario["vigente_desde"]) ? $horario["vigente_desde"] : "");
		$vigente_hasta = normalizarFechaHorarioUsuario(isset($horario["vigente_hasta"]) ? $horario["vigente_hasta"] : "");
		$estado_horario = normalizarEstadoHorarioUsuario(isset($horario["estado_horario"]) ? $horario["estado_horario"] : "");
		$observacion = isset($horario["observacion"]) ? substr((string)$horario["observacion"], 0, 255) : "";

		if ($tipo_jornada == "no_laboral") {
			$hora_entrada = $hora_entrada != "" ? $hora_entrada : "00:00:00";
			$hora_salida = $hora_salida != "" ? $hora_salida : "00:00:00";
		}
		if ($hora_entrada == "") {
			continue;
		}
		$minutos = calcularMinutosJornadaUsuario($hora_entrada, $hora_salida, $descanso_inicio, $descanso_fin, $tipo_jornada);
		$jornada_equivalente = $minutos > 0 ? round($minutos / 480, 2) : 0;

		$horarios[] = array(
			"dia_semana" => $dia,
			"cod_localFK" => $cod_localFK,
			"hora_entrada" => $hora_entrada,
			"hora_salida" => $hora_salida != "" ? $hora_salida : NULL,
			"tipo_jornada" => $tipo_jornada,
			"descanso_inicio" => $descanso_inicio != "" ? $descanso_inicio : NULL,
			"descanso_fin" => $descanso_fin != "" ? $descanso_fin : NULL,
			"horas_esperadas_minutos" => $minutos,
			"jornada_equivalente" => $jornada_equivalente,
			"vigente_desde" => $vigente_desde,
			"vigente_hasta" => $vigente_hasta,
			"estado_horario" => $estado_horario,
			"observacion" => $observacion
		);
	}

	return $horarios;
}

function responderErrorAbmUsuario($mensaje,$detalle="")
{
	$informacion = array("1" => "error", "mensaje" => $mensaje);
	if ($detalle != "") {
		$informacion["detalle"] = $detalle;
	}
	echo json_encode($informacion);
	exit;
}

class ExcepcionAltaFuncionario extends Exception
{
	public $campo;
	public $codigoAlta;

	public function __construct($mensaje,$campo="",$codigoAlta="error_alta_funcionario")
	{
		parent::__construct($mensaje);
		$this->campo=$campo;
		$this->codigoAlta=$codigoAlta;
	}
}

function responderErrorAltaFuncionario($mensaje,$campo="",$codigo="error_alta_funcionario",$extra=array())
{
	$informacion=array(
		"1"=>"error",
		"mensaje"=>$mensaje,
		"campo"=>$campo,
		"codigo"=>$codigo
	);
	if(is_array($extra)){
		foreach($extra as $clave=>$valor){
			$informacion[$clave]=$valor;
		}
	}
	echo json_encode($informacion);
	exit;
}

function usuarioPuedeCrearFuncionarioTelar($cod_usuario)
{
	return controldeaccesoacasas($cod_usuario,"INSERTARLISTADOUSUARIO"," u.accion='SI' ")==1;
}

function tipoFuncionarioEsMecanicoDental($tipo)
{
	$tipo=strtoupper(trim((string)$tipo));
	$tipo=str_replace(array('Á','É','Í','Ó','Ú'),array('A','E','I','O','U'),$tipo);
	return $tipo==='MECANICO DENTAL';
}

function obtenerNivelActivoAltaFuncionario($mysqli,$cod_nivel)
{
	$sql="SELECT cod_niveles,nombre,estado FROM listado_niveles WHERE cod_niveles=? AND estado='Activo' LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return null;}
	$cod_nivel=(int)$cod_nivel;
	$stmt->bind_param('i',$cod_nivel);
	if(!$stmt->execute()){$stmt->close();return null;}
	$result=$stmt->get_result();
	$fila=$result ? $result->fetch_assoc() : null;
	$stmt->close();
	return $fila;
}

function obtenerLocalActivoAltaFuncionario($mysqli,$cod_local)
{
	$sql="SELECT cod_local,Nombre,estado FROM local WHERE cod_local=? AND estado='Activo' LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return null;}
	$cod_local=(int)$cod_local;
	$stmt->bind_param('i',$cod_local);
	if(!$stmt->execute()){$stmt->close();return null;}
	$result=$stmt->get_result();
	$fila=$result ? $result->fetch_assoc() : null;
	$stmt->close();
	return $fila;
}

function nivelMecanicoDentalTienePermisosMinimos($mysqli,$cod_nivel)
{
	$sql="SELECT
		COUNT(DISTINCT CASE WHEN d.accion='SI' AND la.codigo IN
		('VERTRABAJOSLABORATORIO','RECIBIRTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO','EVIDENCIATRABAJOLABORATORIO')
		THEN la.codigo END) AS requeridos,
		COUNT(DISTINCT CASE WHEN d.accion='SI' AND la.codigo NOT IN
		('VERTRABAJOSLABORATORIO','RECIBIRTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO','EVIDENCIATRABAJOLABORATORIO')
		THEN la.codigo END) AS adicionales
		FROM detallesniveles d
		INNER JOIN listadodeacceso la ON la.idlistadodeacceso=d.idlistadodeacceso
		WHERE d.cod_nivelesfk=?";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return false;}
	$cod_nivel=(int)$cod_nivel;
	$stmt->bind_param('i',$cod_nivel);
	if(!$stmt->execute()){$stmt->close();return false;}
	$result=$stmt->get_result();
	$fila=$result ? $result->fetch_assoc() : null;
	$stmt->close();
	return $fila && (int)$fila['requeridos']===4 && (int)$fila['adicionales']===0;
}

function buscarMecanicosDisponiblesAltaFuncionario($cod_usuario_accion)
{
	if(!usuarioPuedeCrearFuncionarioTelar($cod_usuario_accion)){
		echo json_encode(array("1"=>"NI"));
		exit;
	}
	$mysqli=conectar_al_servidor();
	if(!columnaUsuarioExiste($mysqli,'mecanico_dental','cod_usuarioFK')){
		responderErrorAltaFuncionario(
			'La vinculacion de mecanicos con cuentas Telar aun no esta disponible.',
			'inptMecanicoDentalVinculoUsuario',
			'vinculo_mecanico_no_disponible'
		);
	}
	$sql="SELECT md.cod_mecanico_dental,p.nombre_persona,IFNULL(p.telefono,'') AS telefono
		FROM mecanico_dental md
		INNER JOIN persona p ON p.cod_persona=md.cod_personaFK
		WHERE md.estado='activo' AND (md.cod_usuarioFK IS NULL OR md.cod_usuarioFK=0)
		ORDER BY p.nombre_persona ASC";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt || !$stmt->execute()){
		responderErrorAltaFuncionario(
			'No se pudo consultar la lista de mecanicos dentales.',
			'inptMecanicoDentalVinculoUsuario',
			'error_consulta_mecanicos'
		);
	}
	$result=$stmt->get_result();
	$registros=array();
	while($fila=$result->fetch_assoc()){
		$registros[]=array(
			'cod_mecanico_dental'=>(int)$fila['cod_mecanico_dental'],
			'nombre_persona'=>mb_convert_encoding((string)$fila['nombre_persona'],'UTF-8','ISO-8859-1'),
			'telefono'=>mb_convert_encoding((string)$fila['telefono'],'UTF-8','ISO-8859-1')
		);
	}
	$stmt->close();
	$mysqli->close();
	echo json_encode(array("1"=>"exito","2"=>$registros));
	exit;
}

function crearFuncionarioGuiadoTelar(
	$tipo,$nombre_persona,$telefono,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,
	$telefono_referencia,$direccion,$tipo_relacion,$fecha_vencimiento_contrato,$cod_usuario_accion,
	$mecanico_vinculo,$foto,$ext
){
	if(!usuarioPuedeCrearFuncionarioTelar($cod_usuario_accion)){
		echo json_encode(array("1"=>"NI"));
		exit;
	}
	$nombre_persona=trim((string)$nombre_persona);
	$rut_usuario=trim((string)$rut_usuario);
	$login=trim((string)$login);
	$password=(string)$password;
	$tipo=trim((string)$tipo);
	$acceso=(int)$acceso;
	$cod_localFK=(int)$cod_localFK;
	$mecanico_vinculo=trim((string)$mecanico_vinculo);
	$estado='Activo';
	if($nombre_persona===''){
		responderErrorAltaFuncionario('Ingrese el nombre y apellido del funcionario.','inptNombreApellidoUsuario','nombre_requerido');
	}
	if($rut_usuario===''){
		responderErrorAltaFuncionario('Ingrese el numero de documento.','inptNroDocUsuario','documento_requerido');
	}
	if($tipo===''){
		responderErrorAltaFuncionario('Seleccione el tipo de funcionario o cargo.','inptTipoUsuUser','tipo_requerido');
	}
	if($login===''){
		responderErrorAltaFuncionario('Ingrese el usuario para acceder a Telar.','inptClaveAcceso','login_requerido');
	}
	if($password===''){
		responderErrorAltaFuncionario('Genere o ingrese una contrasena temporal.','inptContrasenhaUserTemporal','password_requerido');
	}
	if(strlen($password)>45){
		responderErrorAltaFuncionario('La contrasena temporal no puede superar 45 caracteres.','inptContrasenhaUserTemporal','password_demasiado_largo');
	}
	if($cod_localFK<=0){
		responderErrorAltaFuncionario('Seleccione el local principal.','inptlocaluser','local_requerido');
	}
	if($acceso<=0){
		responderErrorAltaFuncionario('Seleccione el rol de acceso.','inptAccesoUser','rol_requerido');
	}
	$esMecanico=tipoFuncionarioEsMecanicoDental($tipo);
	if($esMecanico && $mecanico_vinculo===""){
		responderErrorAltaFuncionario(
			'Seleccione un mecanico existente o indique que debe crearse uno nuevo.',
			'inptMecanicoDentalVinculoUsuario',
			'vinculo_mecanico_requerido'
		);
	}
	$mysqli=conectar_al_servidor();
	$rol=obtenerNivelActivoAltaFuncionario($mysqli,$acceso);
	if(!$rol){
		responderErrorAltaFuncionario('El rol seleccionado no existe o no esta activo.','inptAccesoUser','rol_invalido');
	}
	$local=obtenerLocalActivoAltaFuncionario($mysqli,$cod_localFK);
	if(!$local){
		responderErrorAltaFuncionario('El local seleccionado no existe o no esta activo.','inptlocaluser','local_invalido');
	}
	if($esMecanico){
		$nombreRol=strtoupper(trim((string)$rol['nombre']));
		if($nombreRol!=='MECANICO DENTAL / LABORATORIO' || !nivelMecanicoDentalTienePermisosMinimos($mysqli,$acceso)){
			responderErrorAltaFuncionario(
				'El mecanico dental debe utilizar el perfil MECANICO DENTAL / LABORATORIO con permisos operativos minimos.',
				'inptAccesoUser',
				'rol_mecanico_invalido'
			);
		}
		if(!columnaUsuarioExiste($mysqli,'mecanico_dental','cod_usuarioFK')){
			responderErrorAltaFuncionario(
				'La vinculacion de mecanicos con cuentas Telar aun no esta disponible.',
				'inptMecanicoDentalVinculoUsuario',
				'vinculo_mecanico_no_disponible'
			);
		}
	}
	$fecha_vencimiento_contrato=normalizarFechaUsuarioContrato($fecha_vencimiento_contrato);
	$mysqli->begin_transaction();
	try{
		$sqlDuplicado="SELECT u.cod_usuario,u.rut_usuario,u.login,u.estado,p.nombre_persona
			FROM usuario u INNER JOIN persona p ON p.cod_persona=u.cod_usuario
			WHERE TRIM(u.rut_usuario)=? OR LOWER(TRIM(u.login))=LOWER(?)
			ORDER BY u.cod_usuario DESC LIMIT 1 FOR UPDATE";
		$stmtDuplicado=$mysqli->prepare($sqlDuplicado);
		if(!$stmtDuplicado){throw new Exception($mysqli->error);}
		$stmtDuplicado->bind_param('ss',$rut_usuario,$login);
		if(!$stmtDuplicado->execute()){throw new Exception($stmtDuplicado->error);}
		$resultDuplicado=$stmtDuplicado->get_result();
		$duplicado=$resultDuplicado ? $resultDuplicado->fetch_assoc() : null;
		$stmtDuplicado->close();
		if($duplicado){
			$esLogin=strtolower(trim((string)$duplicado['login']))===strtolower($login);
			$campo=$esLogin ? 'inptClaveAcceso' : 'inptNroDocUsuario';
			$mensaje=$esLogin
				? 'El usuario de ingreso ya pertenece a otro funcionario.'
				: 'El numero de documento ya pertenece a otro funcionario.';
			$mysqli->rollback();
			responderErrorAltaFuncionario($mensaje,$campo,'funcionario_duplicado',array(
				'funcionario_existente'=>array(
					'cod_usuario'=>(int)$duplicado['cod_usuario'],
					'nombre_persona'=>mb_convert_encoding((string)$duplicado['nombre_persona'],'UTF-8','ISO-8859-1'),
					'estado'=>mb_convert_encoding((string)$duplicado['estado'],'UTF-8','ISO-8859-1')
				)
			));
		}

		$sqlPersona="INSERT INTO persona (nombre_persona,telefono,telefono_referencia,direccion,tipo_relacion) VALUES (?,?,?,?,?)";
		$stmtPersona=$mysqli->prepare($sqlPersona);
		if(!$stmtPersona){throw new Exception($mysqli->error);}
		$stmtPersona->bind_param('sssss',$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion);
		if(!$stmtPersona->execute()){throw new Exception($stmtPersona->error);}
		$codUsuario=(int)$stmtPersona->insert_id;
		$stmtPersona->close();

		$sqlUsuario="INSERT INTO usuario
			(rut_usuario,login,cod_usuario,password,estado,acceso,cod_localFK,tipo,fecha_creacion,fecha_vencimiento_contrato)
			VALUES (?,?,?,?,?,?,?,?,CURDATE(),?)";
		$stmtUsuario=$mysqli->prepare($sqlUsuario);
		if(!$stmtUsuario){throw new Exception($mysqli->error);}
		$stmtUsuario->bind_param('ssissiiss',$rut_usuario,$login,$codUsuario,$password,$estado,$acceso,$cod_localFK,$tipo,$fecha_vencimiento_contrato);
		if(!$stmtUsuario->execute()){throw new Exception($stmtUsuario->error);}
		$stmtUsuario->close();

		$con=rand(5,1500);
		$sqlCobrador="INSERT INTO cobrador (idzona,usu,cod_cobrador,con,estado) VALUES ('1',?,?,?,'Activo')";
		$stmtCobrador=$mysqli->prepare($sqlCobrador);
		if(!$stmtCobrador){throw new Exception($mysqli->error);}
		$stmtCobrador->bind_param('sii',$login,$codUsuario,$con);
		if(!$stmtCobrador->execute()){throw new Exception($stmtCobrador->error);}
		$stmtCobrador->close();

		$sqlVinculoCobrador="INSERT INTO cobradorusuario (cod_usuarioFk,cod_cobradorFk) VALUES (?,?)";
		$stmtVinculoCobrador=$mysqli->prepare($sqlVinculoCobrador);
		if(!$stmtVinculoCobrador){throw new Exception($mysqli->error);}
		$stmtVinculoCobrador->bind_param('ii',$codUsuario,$codUsuario);
		if(!$stmtVinculoCobrador->execute()){throw new Exception($stmtVinculoCobrador->error);}
		$stmtVinculoCobrador->close();

		$sqlAccesos="INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
			SELECT d.idlistadodeacceso,'Administrativo',?,d.accion
			FROM detallesniveles d WHERE d.cod_nivelesfk=?";
		$stmtAccesos=$mysqli->prepare($sqlAccesos);
		if(!$stmtAccesos){throw new Exception($mysqli->error);}
		$stmtAccesos->bind_param('ii',$codUsuario,$acceso);
		if(!$stmtAccesos->execute() || $stmtAccesos->affected_rows<=0){
			throw new ExcepcionAltaFuncionario('El rol seleccionado no tiene permisos configurados.','inptAccesoUser','rol_sin_configuracion');
		}
		$stmtAccesos->close();

		$codMecanico=0;
		if($esMecanico){
			if($mecanico_vinculo==='__nuevo__'){
				$sqlMecanico="INSERT INTO mecanico_dental (cod_personaFK,estado,cod_usuarioFK) VALUES (?,'activo',?)";
				$stmtMecanico=$mysqli->prepare($sqlMecanico);
				if(!$stmtMecanico){throw new Exception($mysqli->error);}
				$stmtMecanico->bind_param('ii',$codUsuario,$codUsuario);
				if(!$stmtMecanico->execute()){throw new Exception($stmtMecanico->error);}
				$codMecanico=(int)$stmtMecanico->insert_id;
				$stmtMecanico->close();
			}else{
				$codMecanico=(int)$mecanico_vinculo;
				if($codMecanico<=0){
					throw new ExcepcionAltaFuncionario('Seleccione un mecanico dental valido.','inptMecanicoDentalVinculoUsuario','mecanico_invalido');
				}
				$sqlMecanico="UPDATE mecanico_dental SET cod_usuarioFK=?
					WHERE cod_mecanico_dental=? AND estado='activo' AND (cod_usuarioFK IS NULL OR cod_usuarioFK=0) LIMIT 1";
				$stmtMecanico=$mysqli->prepare($sqlMecanico);
				if(!$stmtMecanico){throw new Exception($mysqli->error);}
				$stmtMecanico->bind_param('ii',$codUsuario,$codMecanico);
				if(!$stmtMecanico->execute() || $stmtMecanico->affected_rows!==1){
					throw new ExcepcionAltaFuncionario(
						'El mecanico seleccionado ya fue vinculado o dejo de estar disponible.',
						'inptMecanicoDentalVinculoUsuario',
						'mecanico_no_disponible'
					);
				}
				$stmtMecanico->close();
			}
		}

		registrarHistorialCambiosUsuario($mysqli,$codUsuario,array(),array(
			'nombre_persona'=>$nombre_persona,
			'telefono'=>$telefono,
			'telefono_referencia'=>$telefono_referencia,
			'direccion'=>$direccion,
			'tipo_relacion'=>$tipo_relacion,
			'rut_usuario'=>$rut_usuario,
			'login'=>$login,
			'password'=>$password,
			'estado'=>$estado,
			'acceso'=>$acceso,
			'cod_localFK'=>$cod_localFK,
			'tipo'=>$tipo,
			'fecha_creacion'=>date('Y-m-d'),
			'fecha_vencimiento_contrato'=>$fecha_vencimiento_contrato,
			'horarios_usuario'=>'[]'
		),$cod_usuario_accion,'Alta guiada Telar');

		$mysqli->commit();
		if(!empty($ext)){
			cargarFotos($codUsuario);
		}
		echo json_encode(array(
			'1'=>'exito',
			'cod_usuario'=>$codUsuario,
			'estado'=>'Activo',
			'mecanico'=>$codMecanico>0 ? $codMecanico : null,
			'rol_nombre'=>mb_convert_encoding((string)$rol['nombre'],'UTF-8','ISO-8859-1'),
			'local_nombre'=>mb_convert_encoding((string)$local['Nombre'],'UTF-8','ISO-8859-1')
		));
		exit;
	}catch(Exception $e){
		$mysqli->rollback();
		if($e instanceof ExcepcionAltaFuncionario){
			responderErrorAltaFuncionario($e->getMessage(),$e->campo,$e->codigoAlta);
		}
		responderErrorAltaFuncionario(
			'No se pudo crear el funcionario. No se guardo ningun registro parcial.',
			'',
			'error_guardado_funcionario'
		);
	}
}

function usuarioEstaInactivo($estado)
{
	return strtolower(trim((string)$estado)) == "inactivo";
}

function bloquearAccesosUsuarioInactivo($mysqli,$cod_usuario)
{
	$cod_usuario=(int)$cod_usuario;
	$stmt=$mysqli->prepare("UPDATE accesosuser
		SET accion='NO'
		WHERE usuarios_idusario=?
			AND UPPER(TRIM(IFNULL(accion,'')))<>'NO'");
	if(!$stmt){
		responderErrorAbmUsuario("El usuario quedo inactivo, pero no se pudo preparar la revocacion de sus permisos.",$mysqli->error);
	}
	$stmt->bind_param('i',$cod_usuario);
	if(!$stmt->execute()){
		$error=$stmt->error;
		$stmt->close();
		responderErrorAbmUsuario("El usuario quedo inactivo, pero no se pudieron bloquear todos sus permisos.",$error);
	}
	$stmt->close();
}

function cerrarSesionesUsuarioInactivo($mysqli,$cod_usuario)
{
	$cod_usuario=(int)$cod_usuario;
	$stmt=$mysqli->prepare("DELETE FROM seguridad WHERE id_usuario=?");
	if(!$stmt){
		responderErrorAbmUsuario("El usuario quedo inactivo, pero no se pudo preparar el cierre de sus sesiones.",$mysqli->error);
	}
	$stmt->bind_param('i',$cod_usuario);
	if(!$stmt->execute()){
		$error=$stmt->error;
		$stmt->close();
		responderErrorAbmUsuario("El usuario quedo inactivo, pero no se pudieron cerrar todas sus sesiones.",$error);
	}
	$stmt->close();
}

function abmHorarioUsuario($mysqli,$cod_usuario,$horarios_usuario,$cod_usuario_accion,$cod_localFK)
{
	asegurarEstructuraHorarioUsuarioEsperado($mysqli);
	if (!is_array($horarios_usuario)) {
		$horarios_usuario = array();
	}

	$consultaInactivar = "UPDATE horario_usuario
		SET estado_horario='inactivo',
			vigente_hasta=IF(vigente_hasta IS NULL, DATE_SUB(CURDATE(), INTERVAL 1 DAY), vigente_hasta),
			fecha_edit=NOW(),
			cod_usuarioFK_edit=?
		WHERE cod_usuarioFK=?
		AND cod_localFK IS NOT NULL
		AND (estado_horario IS NULL OR estado_horario='activo')";

	$stmtInactivar = $mysqli->prepare($consultaInactivar);
	if (!$stmtInactivar) {
		responderErrorAbmUsuario("No se pudo preparar la actualizacion de horarios del funcionario.", $mysqli->error);
	}

	$ss='ss';
	$stmtInactivar->bind_param($ss,$cod_usuario_accion,$cod_usuario);

	if (!$stmtInactivar->execute()) {
		responderErrorAbmUsuario("No se pudieron actualizar los horarios anteriores del funcionario.", $stmtInactivar->error);
	}

	$stmtInactivar->close();

	if (count($horarios_usuario) == 0) {
		return;
	}

	$consultaInsert = "INSERT INTO horario_usuario
		(cod_usuarioFK,dia_semana,hora_entrada,hora_salida,cod_localFK,cod_usuarioFK_create,
		tipo_jornada,descanso_inicio,descanso_fin,horas_esperadas_minutos,jornada_equivalente,
		vigente_desde,vigente_hasta,estado_horario,observacion)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

	$stmtInsert = $mysqli->prepare($consultaInsert);
	if (!$stmtInsert) {
		responderErrorAbmUsuario("No se pudo preparar el guardado de horarios del funcionario.", $mysqli->error);
	}

	foreach ($horarios_usuario as $horario) {
		$dia_semana = $horario["dia_semana"];
		$cod_local_horario = isset($horario["cod_localFK"]) && $horario["cod_localFK"] != "" ? $horario["cod_localFK"] : $cod_localFK;
		$hora_entrada = $horario["hora_entrada"];
		$hora_salida = $horario["hora_salida"];
		$tipo_jornada = isset($horario["tipo_jornada"]) ? $horario["tipo_jornada"] : "parcial";
		$descanso_inicio = isset($horario["descanso_inicio"]) ? $horario["descanso_inicio"] : NULL;
		$descanso_fin = isset($horario["descanso_fin"]) ? $horario["descanso_fin"] : NULL;
		$horas_esperadas_minutos = isset($horario["horas_esperadas_minutos"]) ? (int)$horario["horas_esperadas_minutos"] : NULL;
		$jornada_equivalente = isset($horario["jornada_equivalente"]) ? (float)$horario["jornada_equivalente"] : NULL;
		$vigente_desde = isset($horario["vigente_desde"]) && $horario["vigente_desde"] != "" ? $horario["vigente_desde"] : date('Y-m-d');
		$vigente_hasta = isset($horario["vigente_hasta"]) && $horario["vigente_hasta"] != "" ? $horario["vigente_hasta"] : NULL;
		$estado_horario = isset($horario["estado_horario"]) ? $horario["estado_horario"] : "activo";
		$observacion = isset($horario["observacion"]) ? $horario["observacion"] : "";

		$ss='sssssssssidssss';
		$stmtInsert->bind_param(
			$ss,
			$cod_usuario,
			$dia_semana,
			$hora_entrada,
			$hora_salida,
			$cod_local_horario,
			$cod_usuario_accion,
			$tipo_jornada,
			$descanso_inicio,
			$descanso_fin,
			$horas_esperadas_minutos,
			$jornada_equivalente,
			$vigente_desde,
			$vigente_hasta,
			$estado_horario,
			$observacion
		);

		if (!$stmtInsert->execute()) {
			responderErrorAbmUsuario("No se pudo guardar un horario del funcionario.", $stmtInsert->error);
		}
	}

	$stmtInsert->close();
}

function buscarHorariosUsuario($mysqli,$cod_usuario)
{
	if (!$mysqli) {
		$mysqli = conectar_al_servidor();
	}
	asegurarEstructuraHorarioUsuarioEsperado($mysqli);

	$horarios = array();

		$consulta = "SELECT
			dia_semana,
			cod_localFK,
			TIME_FORMAT(hora_entrada,'%H:%i') AS hora_entrada,
			TIME_FORMAT(hora_salida,'%H:%i') AS hora_salida,
			IFNULL(tipo_jornada,'parcial') AS tipo_jornada,
			TIME_FORMAT(descanso_inicio,'%H:%i') AS descanso_inicio,
			TIME_FORMAT(descanso_fin,'%H:%i') AS descanso_fin,
			IFNULL(horas_esperadas_minutos,0) AS horas_esperadas_minutos,
			IFNULL(jornada_equivalente,0) AS jornada_equivalente,
			IFNULL(vigente_desde,'') AS vigente_desde,
			IFNULL(vigente_hasta,'') AS vigente_hasta,
			IFNULL(estado_horario,'activo') AS estado_horario,
			IFNULL(observacion,'') AS observacion
		FROM horario_usuario
		WHERE cod_usuarioFK=? AND cod_localFK IS NOT NULL
		AND (estado_horario IS NULL OR estado_horario='activo')
		ORDER BY FIELD(dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), hora_entrada ASC, id ASC";

	$stmt = $mysqli->prepare($consulta);
	if (!$stmt) {
		return "[]";
	}

	$ss='s';
	$stmt->bind_param($ss,$cod_usuario);

	if (!$stmt->execute()) {
		$stmt->close();
		return "[]";
	}

	$result = $stmt->get_result();
	while ($valor = mysqli_fetch_assoc($result)) {
		$horarios[] = array(
			"dia" => mb_convert_encoding((string)($valor["dia_semana"]), 'UTF-8', 'ISO-8859-1'),
			"cod_localFK" => mb_convert_encoding((string)($valor["cod_localFK"]), 'UTF-8', 'ISO-8859-1'),
			"hora_entrada" => mb_convert_encoding((string)($valor["hora_entrada"]), 'UTF-8', 'ISO-8859-1'),
			"hora_salida" => mb_convert_encoding((string)($valor["hora_salida"]), 'UTF-8', 'ISO-8859-1'),
			"tipo_jornada" => mb_convert_encoding((string)($valor["tipo_jornada"]), 'UTF-8', 'ISO-8859-1'),
			"descanso_inicio" => mb_convert_encoding((string)($valor["descanso_inicio"]), 'UTF-8', 'ISO-8859-1'),
			"descanso_fin" => mb_convert_encoding((string)($valor["descanso_fin"]), 'UTF-8', 'ISO-8859-1'),
			"horas_esperadas_minutos" => mb_convert_encoding((string)($valor["horas_esperadas_minutos"]), 'UTF-8', 'ISO-8859-1'),
			"jornada_equivalente" => mb_convert_encoding((string)($valor["jornada_equivalente"]), 'UTF-8', 'ISO-8859-1'),
			"vigente_desde" => mb_convert_encoding((string)($valor["vigente_desde"]), 'UTF-8', 'ISO-8859-1'),
			"vigente_hasta" => mb_convert_encoding((string)($valor["vigente_hasta"]), 'UTF-8', 'ISO-8859-1'),
			"estado_horario" => mb_convert_encoding((string)($valor["estado_horario"]), 'UTF-8', 'ISO-8859-1'),
			"observacion" => mb_convert_encoding((string)($valor["observacion"]), 'UTF-8', 'ISO-8859-1')
		);
	}

	$stmt->close();
	return $horarios;
}

function textoUtf8Usuario($valor)
{
	return mb_convert_encoding((string)($valor), 'UTF-8', 'ISO-8859-1');
}

function textoIsoUsuario($valor)
{
	return mb_convert_encoding((string)($valor), 'ISO-8859-1', 'UTF-8');
}

function asegurarTablaSolicitudesAusenciaUsuario($mysqli)
{
	$sql="CREATE TABLE IF NOT EXISTS solicitudes_ausencia (
		id INT NOT NULL AUTO_INCREMENT,
		cod_usuarioFK INT NOT NULL,
		tipo VARCHAR(30) NOT NULL,
		fecha_desde DATE NOT NULL,
		fecha_hasta DATE NOT NULL,
		hora_desde TIME DEFAULT NULL,
		hora_hasta TIME DEFAULT NULL,
		motivo VARCHAR(500) DEFAULT NULL,
		archivo_url VARCHAR(255) DEFAULT NULL,
		archivo_nombre VARCHAR(180) DEFAULT NULL,
		estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
		creado_por INT DEFAULT NULL,
		aprobado_por INT DEFAULT NULL,
		fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		fecha_aprobacion DATETIME DEFAULT NULL,
		observacion_aprobacion VARCHAR(500) DEFAULT NULL,
		origen VARCHAR(80) DEFAULT NULL,
		visto_rrhh TINYINT(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (id),
		KEY idx_solicitudes_ausencia_usuario_fecha (cod_usuarioFK, fecha_desde, fecha_hasta),
		KEY idx_solicitudes_ausencia_estado (estado),
		KEY idx_solicitudes_ausencia_tipo (tipo)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	$mysqli->query($sql);

	$columnas=array(
		"archivo_nombre" => "VARCHAR(180) DEFAULT NULL",
		"origen" => "VARCHAR(80) DEFAULT NULL",
		"visto_rrhh" => "TINYINT(1) NOT NULL DEFAULT 0"
	);
	foreach($columnas as $columna=>$definicion){
		if(function_exists("columnaUsuarioExiste") && !columnaUsuarioExiste($mysqli,"solicitudes_ausencia",$columna)){
			$mysqli->query("ALTER TABLE solicitudes_ausencia ADD COLUMN ".$columna." ".$definicion);
		}
	}
}

function usuarioPuedeAdministrarAusencias($cod_usuario)
{
	if (function_exists('controldeaccesoacasas')) {
		if (controldeaccesoacasas($cod_usuario, "EDITARLISTADOUSUARIO", " u.accion='SI' ") == 1) {
			return true;
		}
		if (controldeaccesoacasas($cod_usuario, "VERLISTADOUSUARIO", " u.accion='SI' ") == 1) {
			return true;
		}
	}
	return false;
}

function normalizarTipoSolicitudAusenciaUsuario($tipo)
{
	$tipo=strtolower(trim((string)$tipo));
	$tipo=str_replace(array("reposo medico","reposo-medico","reposo"),"reposo_medico",$tipo);
	$permitidos=array("reposo_medico","permiso","vacaciones");
	return in_array($tipo,$permitidos,true) ? $tipo : "";
}

function normalizarEstadoSolicitudAusenciaUsuario($estado)
{
	$estado=strtolower(trim((string)$estado));
	$estado=str_replace(array("aprobada","aprobado"),"aprobado",$estado);
	$estado=str_replace(array("rechazada","rechazado"),"rechazado",$estado);
	$permitidos=array("pendiente","aprobado","rechazado","pendiente_documento","cancelado","por_validar");
	return in_array($estado,$permitidos,true) ? $estado : "pendiente";
}

function normalizarFechaSolicitudAusenciaUsuario($fecha)
{
	$fecha=trim((string)$fecha);
	return preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha) ? $fecha : "";
}

function normalizarHoraSolicitudAusenciaUsuario($hora)
{
	$hora=trim((string)$hora);
	if($hora==""){return null;}
	if(preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/',$hora)){return $hora.":00";}
	if(preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',$hora)){return $hora;}
	return null;
}

function extensionSolicitudAusenciaPermitida($ext)
{
	$ext=strtolower(trim((string)$ext));
	return in_array($ext,array("pdf","jpg","jpeg","png","webp","doc","docx"),true);
}

function guardarArchivoSolicitudAusenciaUsuario($cod_usuario,$archivo,$ext,$nombre_archivo)
{
	$archivo=trim((string)$archivo);
	$ext=strtolower(trim((string)$ext));
	if($archivo=="" || $ext==""){
		return array("url"=>"","nombre"=>"");
	}
	if(!extensionSolicitudAusenciaPermitida($ext)){
		throw new Exception("Formato de adjunto no permitido.");
	}
	$base=realpath(__DIR__.DIRECTORY_SEPARATOR."..");
	if($base===false){
		throw new Exception("No se pudo ubicar la carpeta base.");
	}
	$directorio=$base.DIRECTORY_SEPARATOR."archivos".DIRECTORY_SEPARATOR."funcionarios".DIRECTORY_SEPARATOR."ausencias".DIRECTORY_SEPARATOR.(int)$cod_usuario;
	if(!is_dir($directorio) && !mkdir($directorio,0775,true)){
		throw new Exception("No se pudo crear la carpeta de solicitudes.");
	}
	$contenido=substr($archivo,strpos($archivo,",")!==false ? strpos($archivo,",")+1 : 0);
	$binario=base64_decode($contenido,true);
	if($binario===false){
		throw new Exception("No se pudo leer el adjunto.");
	}
	if(strlen($binario)>15728640){
		throw new Exception("El adjunto no puede superar 15 MB.");
	}
	$nombreSeguro=preg_replace('/[^a-zA-Z0-9_\.-]+/','_',pathinfo((string)$nombre_archivo,PATHINFO_FILENAME));
	if($nombreSeguro==""){$nombreSeguro="solicitud";}
	$nombre=$nombreSeguro."_".date("Ymd_His")."_".rand(1000,9999).".".$ext;
	$ruta=$directorio.DIRECTORY_SEPARATOR.$nombre;
	if(file_put_contents($ruta,$binario)===false){
		throw new Exception("No se pudo guardar el adjunto.");
	}
	return array(
		"url"=>"/GoodVentaAsisCap/archivos/funcionarios/ausencias/".(int)$cod_usuario."/".$nombre,
		"nombre"=>$nombre_archivo!="" ? $nombre_archivo : $nombre
	);
}

function guardarSolicitudAusenciaUsuario($cod_usuario,$tipo,$fecha_desde,$fecha_hasta,$hora_desde,$hora_hasta,$motivo,$nombre_archivo,$archivo,$ext,$user,$origen)
{
	try{
		$mysqli=conectar_al_servidor();
		asegurarTablaSolicitudesAusenciaUsuario($mysqli);
		$tipo=normalizarTipoSolicitudAusenciaUsuario($tipo);
		$fecha_desde=normalizarFechaSolicitudAusenciaUsuario($fecha_desde);
		$fecha_hasta=normalizarFechaSolicitudAusenciaUsuario($fecha_hasta);
		$hora_desde=normalizarHoraSolicitudAusenciaUsuario($hora_desde);
		$hora_hasta=normalizarHoraSolicitudAusenciaUsuario($hora_hasta);
		if($tipo=="" || $fecha_desde=="" || $fecha_hasta==""){
			throw new Exception("Faltan datos de la solicitud.");
		}
		if(strtotime($fecha_hasta)<strtotime($fecha_desde)){
			throw new Exception("La fecha hasta no puede ser menor a la fecha desde.");
		}
		if((string)$cod_usuario !== (string)$user && !usuarioPuedeAdministrarAusencias($user)){
			throw new Exception("No tiene permiso para cargar solicitudes de otro funcionario.");
		}
		if($tipo!="permiso"){
			$hora_desde=null;
			$hora_hasta=null;
		}
		$adjunto=guardarArchivoSolicitudAusenciaUsuario($cod_usuario,$archivo,$ext,$nombre_archivo);
		$archivo_url=$adjunto["url"];
		$archivo_nombre=$adjunto["nombre"];
		$estado=($tipo=="reposo_medico" && $adjunto["url"]=="") ? "pendiente_documento" : "pendiente";
		$motivo=substr((string)$motivo,0,500);
		$origen=substr((string)$origen,0,80);

		$sql="INSERT INTO solicitudes_ausencia
			(cod_usuarioFK,tipo,fecha_desde,fecha_hasta,hora_desde,hora_hasta,motivo,archivo_url,archivo_nombre,estado,creado_por,origen)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo preparar la solicitud.");}
		$ss='isssssssssis';
		$stmt->bind_param($ss,$cod_usuario,$tipo,$fecha_desde,$fecha_hasta,$hora_desde,$hora_hasta,$motivo,$archivo_url,$archivo_nombre,$estado,$user,$origen);
		if(!$stmt->execute()){throw new Exception("No se pudo guardar la solicitud.");}
		$id=$stmt->insert_id;
		$stmt->close();
		echo json_encode(array("1"=>"exito","2"=>"Solicitud registrada.","3"=>$id));
		exit;
	}catch(Exception $e){
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function obtenerSolicitudAusenciaPorId($mysqli,$id)
{
	asegurarTablaSolicitudesAusenciaUsuario($mysqli);
	$sql="SELECT * FROM solicitudes_ausencia WHERE id=? LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return null;}
	$id=(int)$id;
	$stmt->bind_param('i',$id);
	if(!$stmt->execute()){
		$stmt->close();
		return null;
	}
	$result=$stmt->get_result();
	$row=$result->fetch_assoc();
	$stmt->close();
	return $row;
}

function responderSolicitudAusenciaUsuario($id_solicitud,$estado,$observacion,$user)
{
	try{
		$mysqli=conectar_al_servidor();
		$solicitud=obtenerSolicitudAusenciaPorId($mysqli,$id_solicitud);
		if(!$solicitud){throw new Exception("Solicitud no encontrada.");}
		if(!usuarioPuedeAdministrarAusencias($user)){
			throw new Exception("No tiene permiso para responder solicitudes.");
		}
		$estado=normalizarEstadoSolicitudAusenciaUsuario($estado);
		if(!in_array($estado,array("aprobado","rechazado","pendiente_documento","por_validar"),true)){
			throw new Exception("Estado no permitido.");
		}
		$observacion=substr((string)$observacion,0,500);
		$sql="UPDATE solicitudes_ausencia
			SET estado=?, aprobado_por=?, fecha_aprobacion=NOW(), observacion_aprobacion=?
			WHERE id=?";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo preparar la respuesta.");}
		$ss='sisi';
		$id=(int)$id_solicitud;
		$stmt->bind_param($ss,$estado,$user,$observacion,$id);
		if(!$stmt->execute()){throw new Exception("No se pudo actualizar la solicitud.");}
		$stmt->close();
		echo json_encode(array("1"=>"exito","2"=>"Solicitud actualizada."));
		exit;
	}catch(Exception $e){
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function cancelarSolicitudAusenciaUsuario($id_solicitud,$user)
{
	try{
		$mysqli=conectar_al_servidor();
		$solicitud=obtenerSolicitudAusenciaPorId($mysqli,$id_solicitud);
		if(!$solicitud){throw new Exception("Solicitud no encontrada.");}
		$esPropia=(string)$solicitud['cod_usuarioFK']===(string)$user;
		if(!$esPropia && !usuarioPuedeAdministrarAusencias($user)){
			throw new Exception("No tiene permiso para cancelar esta solicitud.");
		}
		if(!in_array($solicitud['estado'],array("pendiente","pendiente_documento","por_validar"),true) && !$esPropia){
			throw new Exception("La solicitud ya fue gestionada.");
		}
		if($esPropia && !in_array($solicitud['estado'],array("pendiente","pendiente_documento","por_validar"),true)){
			throw new Exception("Solo se pueden cancelar solicitudes pendientes.");
		}
		$id=(int)$id_solicitud;
		$estado="cancelado";
		$sql="UPDATE solicitudes_ausencia SET estado=?, fecha_aprobacion=NOW(), observacion_aprobacion='Cancelada por el usuario.' WHERE id=?";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo preparar la cancelacion.");}
		$stmt->bind_param('si',$estado,$id);
		if(!$stmt->execute()){throw new Exception("No se pudo cancelar la solicitud.");}
		$stmt->close();
		echo json_encode(array("1"=>"exito","2"=>"Solicitud cancelada."));
		exit;
	}catch(Exception $e){
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function obtenerSolicitudesAusenciaUsuario($mysqli,$cod_usuario,$desde="",$hasta="",$incluirCanceladas=false)
{
	asegurarTablaSolicitudesAusenciaUsuario($mysqli);
	$condiciones=array("cod_usuarioFK=?");
	$tipos="i";
	$parametros=array((int)$cod_usuario);
	if($desde!="" && $hasta!=""){
		$condiciones[]="fecha_desde <= ? AND fecha_hasta >= ?";
		$tipos.="ss";
		$parametros[]=$hasta;
		$parametros[]=$desde;
	}
	if(!$incluirCanceladas){
		$condiciones[]="estado <> 'cancelado'";
	}
	$sql="SELECT * FROM solicitudes_ausencia WHERE ".implode(" AND ",$condiciones)." ORDER BY fecha_desde DESC, id DESC";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return array();}
	$refs=array();
	$refs[]=$tipos;
	foreach($parametros as $key=>$value){
		$refs[]=&$parametros[$key];
	}
	call_user_func_array(array($stmt,'bind_param'),$refs);
	if(!$stmt->execute()){
		$stmt->close();
		return array();
	}
	$result=$stmt->get_result();
	$registros=array();
	while($row=$result->fetch_assoc()){
		$reg=array();
		foreach($row as $key=>$value){
			$reg[$key]=textoUtf8Usuario($value);
		}
		$registros[]=$reg;
	}
	$stmt->close();
	return $registros;
}

function buscarSolicitudesAusenciaUsuario($cod_usuario,$user)
{
	if($cod_usuario==""){
		echo json_encode(array("1"=>"error","2"=>"No se pudo identificar el funcionario."));
		exit;
	}
	if((string)$cod_usuario !== (string)$user && !usuarioPuedeAdministrarAusencias($user)){
		echo json_encode(array("1"=>"NI","2"=>"Sin permiso."));
		exit;
	}
	$mysqli=conectar_al_servidor();
	$desde=date('Y-m-01',strtotime('-2 months'));
	$hasta=date('Y-m-d',strtotime('+90 days'));
	$registros=obtenerSolicitudesAusenciaUsuario($mysqli,$cod_usuario,$desde,$hasta,true);
	echo json_encode(array("1"=>"exito","2"=>$registros,"3"=>renderSolicitudesAusenciaUsuario($registros,$user,$cod_usuario)),JSON_UNESCAPED_UNICODE);
	exit;
}

function renderSolicitudesAusenciaUsuario($registros,$user,$cod_usuario)
{
	if(count($registros)==0){
		return "<div class='funcionario-empty-state'>Sin solicitudes registradas.</div>";
	}
	$html="<div class='solicitudes-ausencia-lista'>";
	foreach($registros as $solicitud){
		$estado=$solicitud['estado'];
		$tipo=textoTipoSolicitudAusencia($solicitud['tipo']);
		$duracion=formatearDuracionSolicitudAusencia($solicitud);
		$acciones="";
		if((string)$cod_usuario===(string)$user && in_array($estado,array("pendiente","pendiente_documento","por_validar"),true)){
			$acciones="<button type='button' onclick='cancelarSolicitudAusenciaMiPerfil(\"".htmlspecialchars($solicitud['id'],ENT_QUOTES)."\")'>Cancelar</button>";
		}
		$html.="<div class='solicitudes-ausencia-row solicitudes-ausencia-row--".htmlspecialchars(claseEstadoSolicitudAusencia($estado),ENT_QUOTES)."'>"
			."<strong>".htmlspecialchars($tipo,ENT_QUOTES)."</strong>"
			."<span>".htmlspecialchars(formatearRangoSolicitudAusencia($solicitud),ENT_QUOTES)."</span>"
			."<small>".htmlspecialchars(textoEstadoSolicitudAusencia($estado)." · ".$duracion,ENT_QUOTES)."</small>"
			.$acciones
			."</div>";
	}
	$html.="</div>";
	return $html;
}

function textoTipoSolicitudAusencia($tipo)
{
	if($tipo=="reposo_medico"){return "Reposo medico";}
	if($tipo=="vacaciones"){return "Vacaciones";}
	return "Permiso";
}

function textoEstadoSolicitudAusencia($estado)
{
	switch($estado){
		case "aprobado": return "Aprobada";
		case "rechazado": return "Rechazada";
		case "pendiente_documento": return "Pendiente de documento";
		case "cancelado": return "Cancelada";
		case "por_validar": return "Por validar";
		default: return "Pendiente";
	}
}

function claseEstadoSolicitudAusencia($estado)
{
	switch($estado){
		case "aprobado": return "ok";
		case "rechazado": return "danger";
		case "pendiente_documento":
		case "por_validar":
		case "pendiente": return "warning";
		case "cancelado": return "muted";
		default: return "muted";
	}
}

function diasSolicitudAusencia($desde,$hasta)
{
	$inicio=strtotime($desde);
	$fin=strtotime($hasta);
	if($inicio===false || $fin===false || $fin<$inicio){return 0;}
	return (int)floor(($fin-$inicio)/86400)+1;
}

function horasSolicitudAusencia($hora_desde,$hora_hasta)
{
	if($hora_desde=="" || $hora_hasta==""){return 0;}
	$desde=strtotime("2000-01-01 ".$hora_desde);
	$hasta=strtotime("2000-01-01 ".$hora_hasta);
	if($desde===false || $hasta===false || $hasta<=$desde){return 0;}
	return round(($hasta-$desde)/3600,1);
}

function formatearDuracionSolicitudAusencia($solicitud)
{
	if($solicitud['tipo']=="permiso"){
		$horas=horasSolicitudAusencia($solicitud['hora_desde'],$solicitud['hora_hasta']);
		if($horas>0 && $solicitud['fecha_desde']==$solicitud['fecha_hasta']){
			return rtrim(rtrim(number_format($horas,1,'.',''),'0'),'.')."h";
		}
	}
	return diasSolicitudAusencia($solicitud['fecha_desde'],$solicitud['fecha_hasta'])."d";
}

function formatearRangoSolicitudAusencia($solicitud)
{
	$desde=date('d/m',strtotime($solicitud['fecha_desde']));
	$hasta=date('d/m',strtotime($solicitud['fecha_hasta']));
	if($desde==$hasta){
		$texto=$desde;
	}else{
		$texto=$desde." al ".$hasta;
	}
	if($solicitud['tipo']=="permiso" && $solicitud['hora_desde']!="" && $solicitud['hora_hasta']!=""){
		$texto.=" ".substr($solicitud['hora_desde'],0,5)."-".substr($solicitud['hora_hasta'],0,5);
	}
	return $texto;
}

function solicitudAusenciaCubreFecha($solicitud,$fecha,$tipo=null,$estado=null)
{
	if($tipo!==null && $solicitud['tipo']!=$tipo){return false;}
	if($estado!==null && $solicitud['estado']!=$estado){return false;}
	return $solicitud['fecha_desde'] <= $fecha && $solicitud['fecha_hasta'] >= $fecha;
}

function minutosDesdeHoraAusenciaUsuario($hora)
{
	$hora=substr((string)$hora,0,5);
	if(!preg_match('/^\d{2}:\d{2}$/',$hora)){return null;}
	$partes=explode(':',$hora);
	return ((int)$partes[0]*60)+(int)$partes[1];
}

function horarioAplicaFechaAusenciaUsuario($horario,$fecha)
{
	if(isset($horario['estado_horario']) && $horario['estado_horario']=="inactivo"){
		return false;
	}
	if(!empty($horario['vigente_desde']) && $horario['vigente_desde']>$fecha){
		return false;
	}
	if(!empty($horario['vigente_hasta']) && $horario['vigente_hasta']<$fecha){
		return false;
	}
	return true;
}

function nombreDiaAusenciaUsuario($fecha)
{
	$dias=array("domingo","lunes","martes","miercoles","jueves","viernes","sabado");
	return $dias[(int)date('w',strtotime($fecha))];
}

function horariosEsperadosFechaAusenciaUsuario($horarios,$fecha)
{
	$dia=nombreDiaAusenciaUsuario($fecha);
	$salida=array();
	foreach($horarios as $horario){
		if(!horarioAplicaFechaAusenciaUsuario($horario,$fecha)){continue;}
		if(!isset($horario['dia']) || $horario['dia']!=$dia){continue;}
		if(isset($horario['tipo_jornada']) && $horario['tipo_jornada']=="no_laboral"){continue;}
		$entrada=minutosDesdeHoraAusenciaUsuario(isset($horario['hora_entrada']) ? $horario['hora_entrada'] : "");
		$salidaHora=minutosDesdeHoraAusenciaUsuario(isset($horario['hora_salida']) ? $horario['hora_salida'] : "");
		$minutos=isset($horario['horas_esperadas_minutos']) ? (int)$horario['horas_esperadas_minutos'] : 0;
		if($entrada===null){continue;}
		if($minutos<=0 && $salidaHora!==null && $salidaHora>$entrada){
			$minutos=$salidaHora-$entrada;
		}
		if($minutos<=0){continue;}
		$salida[]=array(
			"entrada"=>substr($horario['hora_entrada'],0,5),
			"entrada_min"=>$entrada,
			"minutos"=>$minutos
		);
	}
	usort($salida,function($a,$b){return $a['entrada_min']-$b['entrada_min'];});
	return $salida;
}

function obtenerAsistenciasUsuarioPeriodoAusencia($mysqli,$cod_usuario,$desde,$hasta)
{
	$sql="SELECT cod_asistencia,cod_usuarioFK,fecha,TIME_FORMAT(hora_entrada,'%H:%i') AS hora_entrada,TIME_FORMAT(hora_salida,'%H:%i') AS hora_salida,IFNULL(justificacion,'') AS justificacion
		FROM asistencia
		WHERE cod_usuarioFK=? AND DATE(fecha)>=? AND DATE(fecha)<=?
		ORDER BY fecha ASC,hora_entrada ASC";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return array();}
	$cod=(int)$cod_usuario;
	$stmt->bind_param('iss',$cod,$desde,$hasta);
	if(!$stmt->execute()){
		$stmt->close();
		return array();
	}
	$result=$stmt->get_result();
	$agrupados=array();
	while($row=$result->fetch_assoc()){
		$fecha=substr($row['fecha'],0,10);
		if(!isset($agrupados[$fecha])){$agrupados[$fecha]=array();}
		$reg=array();
		foreach($row as $key=>$value){$reg[$key]=textoUtf8Usuario($value);}
		$agrupados[$fecha][]=$reg;
	}
	$stmt->close();
	return $agrupados;
}

function primeraEntradaRegistradaAusencia($registros)
{
	$min=null;
	foreach($registros as $registro){
		$m=minutosDesdeHoraAusenciaUsuario($registro['hora_entrada']);
		if($m===null){continue;}
		if($min===null || $m<$min){$min=$m;}
	}
	return $min;
}

function solicitudJustificadaAprobadaFecha($solicitudes,$fecha)
{
	$prioridad=array("reposo_medico","vacaciones","permiso");
	foreach($prioridad as $tipo){
		foreach($solicitudes as $solicitud){
			if(solicitudAusenciaCubreFecha($solicitud,$fecha,$tipo,"aprobado")){
				return $solicitud;
			}
		}
	}
	return null;
}

function calcularResumenAsistenciaTableroUsuario($mysqli,$cod_usuario,$horarios,$solicitudes,$desde,$hasta)
{
	$hoy=date('Y-m-d');
	$ahoraMin=((int)date('H')*60)+(int)date('i');
	$hastaConteo=$hoy<$hasta ? $hoy : $hasta;
	$registrosPeriodo=obtenerAsistenciasUsuarioPeriodoAusencia($mysqli,$cod_usuario,$desde,$hastaConteo);
	$tardanzas=0;
	$ausencias=0;
	$fecha=new DateTime($desde);
	$fechaFin=new DateTime($hastaConteo);
	while($fecha<=$fechaFin){
		$fechaTexto=$fecha->format('Y-m-d');
		$esperados=horariosEsperadosFechaAusenciaUsuario($horarios,$fechaTexto);
		if(count($esperados)>0){
			$registros=isset($registrosPeriodo[$fechaTexto]) ? $registrosPeriodo[$fechaTexto] : array();
			$entrada=primeraEntradaRegistradaAusencia($registros);
			if($entrada!==null){
				if($entrada>$esperados[0]['entrada_min']+10){$tardanzas++;}
			}else if(solicitudJustificadaAprobadaFecha($solicitudes,$fechaTexto)==null){
				if($fechaTexto<$hoy || ($fechaTexto==$hoy && $ahoraMin>=$esperados[0]['entrada_min'])){
					$ausencias++;
				}
			}
		}
		$fecha->modify('+1 day');
	}

	$esperadosHoy=horariosEsperadosFechaAusenciaUsuario($horarios,$hoy);
	$registrosHoy=isset($registrosPeriodo[$hoy]) ? $registrosPeriodo[$hoy] : array();
	$entradaHoy=primeraEntradaRegistradaAusencia($registrosHoy);
	$justificada=solicitudJustificadaAprobadaFecha($solicitudes,$hoy);
	$estado="dia_libre";
	$texto=count($horarios)==0 ? "Sin horario" : "Dia libre";
	$clase="muted";
	$icono="calendar";
	$contador="0";
	$tooltip=$texto;

	if(count($esperadosHoy)>0){
		if($entradaHoy!==null){
			if($entradaHoy>$esperadosHoy[0]['entrada_min']+10){
				$estado="tardanza";
				$texto="Tardanza";
				$clase="orange";
				$icono="clock";
				$contador=(string)$tardanzas;
			}else{
				$estado="presente";
				$texto="Presente";
				$clase="ok";
				$icono="check";
			}
			$tooltip="Entrada registrada hoy.";
		}else if($justificada!=null){
			$estado="justificado";
			$texto="Justificado";
			$clase="info";
			$icono="shield";
			$tooltip="Justificado por ".textoTipoSolicitudAusencia($justificada['tipo']).".";
		}else if($ahoraMin<$esperadosHoy[0]['entrada_min']){
			$estado="programada";
			$texto="Programada";
			$clase="info";
			$icono="calendar";
			$tooltip="Jornada esperada desde ".$esperadosHoy[0]['entrada'].".";
		}else{
			$estado="ausente";
			$texto="Ausente";
			$clase="danger";
			$icono="x";
			$contador=(string)$ausencias;
			$tooltip="Sin entrada registrada ni solicitud aprobada.";
		}
	}

	return array(
		"estado"=>$estado,
		"texto"=>$texto,
		"clase"=>$clase,
		"icono"=>$icono,
		"contador"=>$contador,
		"contador_periodo"=>$contador,
		"tardanzas_periodo"=>$tardanzas,
		"ausencias_periodo"=>$ausencias,
		"tooltip"=>$tooltip
	);
}

function ordenarSolicitudesPorPrioridadFecha($a,$b)
{
	$prioridad=array("aprobado"=>1,"pendiente_documento"=>2,"por_validar"=>3,"pendiente"=>4,"rechazado"=>5,"cancelado"=>9);
	$pa=isset($prioridad[$a['estado']]) ? $prioridad[$a['estado']] : 8;
	$pb=isset($prioridad[$b['estado']]) ? $prioridad[$b['estado']] : 8;
	if($pa!=$pb){return $pa-$pb;}
	return strcmp($a['fecha_desde'],$b['fecha_desde']);
}

function calcularResumenReposoUsuario($solicitudes,$desde,$hasta)
{
	$hoy=date('Y-m-d');
	$reposos=array();
	foreach($solicitudes as $solicitud){
		if($solicitud['tipo']=="reposo_medico"){$reposos[]=$solicitud;}
	}
	usort($reposos,'ordenarSolicitudesPorPrioridadFecha');
	$acumulado=0;
	foreach($reposos as $solicitud){
		if($solicitud['estado']=="aprobado" && $solicitud['fecha_desde']<=$hasta && $solicitud['fecha_hasta']>=$desde){
			$inicio=max(strtotime($solicitud['fecha_desde']),strtotime($desde));
			$fin=min(strtotime($solicitud['fecha_hasta']),strtotime($hasta));
			$acumulado+=diasSolicitudAusencia(date('Y-m-d',$inicio),date('Y-m-d',$fin));
		}
	}
	foreach($reposos as $solicitud){
		if(!solicitudAusenciaCubreFecha($solicitud,$hoy)){continue;}
		if($solicitud['estado']=="aprobado"){
			return resumenSolicitudControl("reposo_aprobado","Reposo aprobado","info","shield",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
		if($solicitud['estado']=="pendiente_documento"){
			return resumenSolicitudControl("certificado_pendiente","Cert. pendiente","warning","alert",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
		if($solicitud['estado']=="por_validar"){
			return resumenSolicitudControl("por_validar","Por validar","warning","clock",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
		if($solicitud['estado']=="pendiente"){
			return resumenSolicitudControl("reposo_pendiente","Reposo pendiente","warning","clock",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
	}
	foreach($reposos as $solicitud){
		if(in_array($solicitud['estado'],array("pendiente_documento","por_validar","pendiente"),true)){
			$texto=$solicitud['estado']=="pendiente_documento" ? "Cert. pendiente" : ($solicitud['estado']=="por_validar" ? "Por validar" : "Reposo pendiente");
			return resumenSolicitudControl($solicitud['estado'],$texto,"warning","clock",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
	}
	return array(
		"estado"=>"sin_reposo",
		"texto"=>"Sin reposo",
		"clase"=>"ok",
		"icono"=>"plus",
		"contador"=>$acumulado>0 ? $acumulado."d" : "0",
		"tooltip"=>$acumulado>0 ? $acumulado." dias aprobados en el periodo." : "Sin reposos activos.",
		"fecha_desde"=>"",
		"fecha_hasta"=>""
	);
}

function calcularResumenPermisoVacacionUsuario($solicitudes,$desde,$hasta)
{
	$hoy=date('Y-m-d');
	$items=array();
	foreach($solicitudes as $solicitud){
		if($solicitud['tipo']=="permiso" || $solicitud['tipo']=="vacaciones"){$items[]=$solicitud;}
	}
	usort($items,'ordenarSolicitudesPorPrioridadFecha');
	foreach($items as $solicitud){
		if(!solicitudAusenciaCubreFecha($solicitud,$hoy)){continue;}
		if($solicitud['estado']=="aprobado"){
			if($solicitud['tipo']=="vacaciones"){
				return resumenSolicitudControl("en_vacaciones","En vacaciones","info","plane",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
			}
			return resumenSolicitudControl("permiso_hoy","Permiso hoy","info","user",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
		if(in_array($solicitud['estado'],array("pendiente","por_validar"),true)){
			return resumenSolicitudControl("pendiente","Pendiente","warning","clock",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
	}
	foreach($items as $solicitud){
		if($solicitud['estado']=="aprobado" && $solicitud['fecha_desde']>$hoy){
			$texto=$solicitud['tipo']=="vacaciones" ? "Vacaciones" : "Permiso aprobado";
			return resumenSolicitudControl($solicitud['tipo'],$texto,"info",$solicitud['tipo']=="vacaciones" ? "plane" : "user",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
	}
	foreach($items as $solicitud){
		if(in_array($solicitud['estado'],array("pendiente","por_validar"),true)){
			return resumenSolicitudControl("pendiente","Pendiente","warning","clock",formatearDuracionSolicitudAusencia($solicitud),$solicitud);
		}
	}
	return array(
		"estado"=>"sin_permiso",
		"texto"=>"Sin permiso",
		"clase"=>"ok",
		"icono"=>"calendar",
		"contador"=>"0",
		"tooltip"=>"Sin permisos ni vacaciones activas.",
		"fecha_desde"=>"",
		"fecha_hasta"=>"",
		"tipo"=>""
	);
}

function resumenSolicitudControl($estado,$texto,$clase,$icono,$contador,$solicitud)
{
	return array(
		"estado"=>$estado,
		"texto"=>$texto,
		"clase"=>$clase,
		"icono"=>$icono,
		"contador"=>$contador,
		"tooltip"=>textoTipoSolicitudAusencia($solicitud['tipo'])." ".formatearRangoSolicitudAusencia($solicitud)." - ".textoEstadoSolicitudAusencia($solicitud['estado']),
		"fecha_desde"=>$solicitud['fecha_desde'],
		"fecha_hasta"=>$solicitud['fecha_hasta'],
		"tipo"=>$solicitud['tipo'],
		"id"=>$solicitud['id']
	);
}

function calcularResumenesControlFuncionario($mysqli,$cod_usuario,$horarios)
{
	$periodoDesde=date('Y-m-01');
	$periodoHasta=date('Y-m-t');
	$solicitudes=obtenerSolicitudesAusenciaUsuario($mysqli,$cod_usuario,date('Y-m-01'),date('Y-m-d',strtotime('+90 days')),false);
	$asistencia=calcularResumenAsistenciaTableroUsuario($mysqli,$cod_usuario,$horarios,$solicitudes,$periodoDesde,$periodoHasta);
	$reposo=calcularResumenReposoUsuario($solicitudes,$periodoDesde,$periodoHasta);
	$permiso=calcularResumenPermisoVacacionUsuario($solicitudes,$periodoDesde,$periodoHasta);
	$pendientes=0;
	$proximas=array();
	foreach($solicitudes as $solicitud){
		if(in_array($solicitud['estado'],array("pendiente","pendiente_documento","por_validar"),true)){
			$pendientes++;
		}
		if($solicitud['estado']=="aprobado" && $solicitud['fecha_desde']>=date('Y-m-d')){
			$proximas[]=$solicitud;
		}
	}
	return array(
		"asistencia_resumen"=>$asistencia,
		"reposo_resumen"=>$reposo,
		"permiso_vacacion_resumen"=>$permiso,
		"solicitudes_ausencia"=>$solicitudes,
		"solicitudes_ausencia_pendientes"=>$pendientes,
		"proximas_ausencias"=>array_slice($proximas,0,4)
	);
}

function abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$foto,$ext,$telefono_referencia,$direccion,$tipo_relacion,$fecha_creacion,$fecha_vencimiento_contrato,$horarios_usuario,$cod_usuario_accion,$operacion,$mecanico_vinculo="")
{



if($operacion!="nuevo" && $operacion!="editar"){
	responderErrorAbmUsuario("No se pudo guardar el funcionario porque la operacion solicitada no es valida.");
}

if($operacion=="editar" && usuarioEstaInactivo($estado)
	&& (string)$cod_usuario===(string)$cod_usuario_accion){
	responderErrorAbmUsuario("No puede inactivar su propio usuario mientras tiene la sesion iniciada.");
}

if($operacion==="nuevo"){
	crearFuncionarioGuiadoTelar(
		$tipo,$nombre_persona,$telefono,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,
		$telefono_referencia,$direccion,$tipo_relacion,$fecha_vencimiento_contrato,$cod_usuario_accion,
		$mecanico_vinculo,$foto,$ext
	);
}

if($nombre_persona==""  || $rut_usuario==""  || $login=="" || ($operacion=="nuevo" && $password=="")){
$informacion =array("1" => "CAMPOSVACIOS");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
asegurarCampoVencimientoContratoUsuario($mysqli);
$fecha_vencimiento_contrato = normalizarFechaUsuarioContrato($fecha_vencimiento_contrato);
if (usuarioEstaInactivo($estado)) {
	$acceso = "4";
	$horarios_usuario = array();
}
$datosAnterioresAuditoria=array();
if($operacion=="editar"){
	$datosAnterioresAuditoria=obtenerDatosUsuarioAuditoria($mysqli,$cod_usuario);
	if($password==""){
		$password=isset($datosAnterioresAuditoria["password"]) ? $datosAnterioresAuditoria["password"] : "";
	}
}

$consulta= "Select count(*) from usuario where login=? and password=? and cod_localFK=?  and Cod_Usuario!=?";
	
	
		$stmt = $mysqli->prepare($consulta);
if (!$stmt) {
	responderErrorAbmUsuario("No se pudo preparar la validacion del acceso del funcionario.", $mysqli->error);
}
$ss='ssss';
$stmt->bind_param($ss,$login,$password,$cod_localFK,$cod_usuario);


if ( ! $stmt->execute()) {
	responderErrorAbmUsuario("No se pudo validar si el usuario ya existe.", $stmt->error);
}

$valor = 0;
$stmt->bind_result($valor);
while ($stmt->fetch()) { 
   
	 $valor =$valor;
}

if($valor>0)
{
	$informacion =array("1" => "CI");
	echo json_encode($informacion);	
	exit;
}   

if($operacion=="nuevo") 
{


$consulta1="Insert into persona (nombre_persona,telefono,telefono_referencia,direccion,tipo_relacion)
values(?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1) {
	responderErrorAbmUsuario("No se pudo preparar el guardado de los datos personales del funcionario.", $mysqli->error);
}
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion);

$consulta2="Insert into usuario (rut_usuario,login,cod_usuario,password,estado,acceso,cod_localFK,tipo,fecha_creacion,fecha_vencimiento_contrato)
values(?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?, NOW(),?)";
$stmt2 = $mysqli->prepare($consulta2);
if (!$stmt2) {
	responderErrorAbmUsuario("No se pudo preparar el guardado del acceso del funcionario.", $mysqli->error);
}
$ss='ssssssss';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo,$fecha_vencimiento_contrato);

$con=rand(5, 1500);

$consulta3="Insert into cobrador (idzona,usu,cod_cobrador,con,estado)
values('1',?,(select cod_persona from persona order by cod_persona desc limit 1),?,'Activo')";
$stmt3 = $mysqli->prepare($consulta3);
if (!$stmt3) {
	responderErrorAbmUsuario("No se pudo preparar el vinculo de cobrador del funcionario.", $mysqli->error);
}
$ss='ss';
$stmt3->bind_param($ss,$login,$con);


$consulta4="Insert into cobradorusuario (cod_usuarioFk,cod_cobradorFk)
values((select cod_persona from persona order by cod_persona desc limit 1),(select cod_persona from persona order by cod_persona desc limit 1))";
$stmt4 = $mysqli->prepare($consulta4);
if (!$stmt4) {
	responderErrorAbmUsuario("No se pudo preparar el vinculo de cobrador y usuario.", $mysqli->error);
}

}


if($operacion=="editar")
{

$consulta1="Update persona set nombre_persona=?,telefono=?, telefono_referencia=?, direccion=?, tipo_relacion=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
if (!$stmt1) {
	responderErrorAbmUsuario("No se pudo preparar la actualizacion de los datos personales del funcionario.", $mysqli->error);
}
$ss='ssssss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion,$cod_persona);

$consulta2="update usuario set rut_usuario=?,login=?,password=?,estado=?,acceso=?,cod_localFK=?,tipo=?,fecha_creacion=?,fecha_vencimiento_contrato=? where cod_usuario=? ";
$stmt2 = $mysqli->prepare($consulta2);
if (!$stmt2) {
	responderErrorAbmUsuario("No se pudo preparar la actualizacion del acceso del funcionario.", $mysqli->error);
}
$ss='sssssisssi';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo,$fecha_creacion,$fecha_vencimiento_contrato,$cod_usuario);

}



if (!$stmt1->execute()) {
	responderErrorAbmUsuario("No se pudieron guardar los datos personales del funcionario.", $stmt1->error);
}


if (!$stmt2->execute()) {
	responderErrorAbmUsuario("No se pudo guardar el acceso del funcionario.", $stmt2->error);
}

// Recupera la id del usuario de la ultima insercion
$cod_usuario= empty($cod_usuario) ?  : $cod_usuario;

if($operacion=="nuevo") 
{
	
if (!$stmt3->execute()) {
	responderErrorAbmUsuario("No se pudo guardar el vinculo de cobrador del funcionario.", $stmt3->error);
}
if (!$stmt4->execute()) {
	responderErrorAbmUsuario("No se pudo guardar el vinculo de cobrador y usuario.", $stmt4->error);
}

}

// Copia la imagen al servidor y genera el enlace
if (!(empty($ext))) {
	$foto=substr($_POST['foto'], strpos($_POST['foto'], ",") + 1);
	$foto = base64_decode($foto);
	$id_foto="";		  
	$donde="../fotos/perfilUsuario/";
	$id_foto=$cod_usuario;
	$id_f=subir_imagen_base64($donde,$foto,$id_foto,$ext);
	$ruta="/GoodVentaElim/fotos/perfilUsuario/".$cod_persona.$id_f.'.'.$ext;
	CargaFoto("url",$ruta,$cod_persona);
}

if($operacion=="nuevo"){
$cod_usuario=obtenerUltimaid();
abmHorarioUsuario($mysqli,$cod_usuario,$horarios_usuario,$cod_usuario_accion,$cod_localFK);
if(usuarioEstaInactivo($estado)){
	bloquearAccesosUsuarioInactivo($mysqli,$cod_usuario);
	cerrarSesionesUsuarioInactivo($mysqli,$cod_usuario);
}else{
	EliminarAccesos($cod_usuario);
	generarKEYS($acceso,$cod_usuario,'Administrativo');
}
}else{
abmHorarioUsuario($mysqli,$cod_usuario,$horarios_usuario,$cod_usuario_accion,$cod_localFK);
if(usuarioEstaInactivo($estado)){
	bloquearAccesosUsuarioInactivo($mysqli,$cod_usuario);
	cerrarSesionesUsuarioInactivo($mysqli,$cod_usuario);
}else{
	EliminarAccesos($cod_usuario);
	generarKEYS($acceso,$cod_usuario,'Administrativo');
}
}

if($operacion=="editar"){
	registrarHistorialCambiosUsuario($mysqli,$cod_usuario,$datosAnterioresAuditoria,array(
		"nombre_persona" => $nombre_persona,
		"telefono" => $telefono,
		"telefono_referencia" => $telefono_referencia,
		"direccion" => $direccion,
		"tipo_relacion" => $tipo_relacion,
		"rut_usuario" => $rut_usuario,
		"login" => $login,
		"password" => $password,
		"estado" => $estado,
		"acceso" => $acceso,
		"cod_localFK" => $cod_localFK,
		"tipo" => $tipo,
		"fecha_creacion" => $fecha_creacion,
		"fecha_vencimiento_contrato" => $fecha_vencimiento_contrato,
		"horarios_usuario" => json_encode($horarios_usuario)
	),$cod_usuario_accion,"Administracion");
}


cargarFotos($cod_persona);

$informacion =array("1" => "exito");
echo json_encode($informacion);	
exit;

}


function cargarFotos($cod_persona){
	
$ext=$_POST['ext'];
$ext = mb_convert_encoding((string)($ext), 'ISO-8859-1', 'UTF-8');
 

if($ext!=""){
	$foto=substr($_POST['foto'], strpos($_POST['foto'], ",") + 1);;
$foto = base64_decode($foto);
$id_foto="";		  
		     $donde="../fotos/perfilUsuario/";
			  $id_foto=$cod_persona;
                $id_f=subir_imagen_base64($donde,$foto,$id_foto,$ext);
$ruta="/GoodVentaAsisCap/fotos/perfilUsuario/".$cod_persona.$id_f.'.'.$ext;
CargaFoto("url",$ruta,$cod_persona);
}
 



}

function CargaFoto($tableName,$Urlfoto,$cod_cliente){
	$mysqli=conectar_al_servidor();
	$consulta="Update usuario set ".$tableName."=? where cod_usuario=? ";	

	$stmt = $mysqli->prepare($consulta);
$ss='ss';
$stmt->bind_param($ss,$Urlfoto,$cod_cliente); 
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	 mysqli_close($mysqli);
}



function obtenerUltimaid()
{
	$mysqli=conectar_al_servidor();
	 $idusario='';
	$sql= "Select cod_persona from persona order by cod_persona desc limit 1";
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
		  
		  
		      $idusario=$valor['cod_persona'];
		  	 
			  
			  
	  }
 }
 
 
return $idusario;


}

function usuarioPuedeAdministrarSancionesFuncionario($cod_usuario)
{
	if (function_exists('controldeaccesoacasas')) {
		if (controldeaccesoacasas($cod_usuario, "EDITARLISTADOUSUARIO", " u.accion='SI' ") == 1) {
			return true;
		}
		if (controldeaccesoacasas($cod_usuario, "VERLISTADOUSUARIO", " u.accion='SI' ") == 1) {
			return true;
		}
	}
	return false;
}

function asegurarTablaSancionesFuncionario($mysqli)
{
	$sql="CREATE TABLE IF NOT EXISTS funcionario_sanciones (
		id INT NOT NULL AUTO_INCREMENT,
		cod_usuarioFK INT NOT NULL,
		fecha DATE NOT NULL,
		tipo VARCHAR(80) NOT NULL,
		motivo VARCHAR(180) NOT NULL,
		descripcion TEXT DEFAULT NULL,
		documento_url VARCHAR(255) DEFAULT NULL,
		documento_nombre VARCHAR(180) DEFAULT NULL,
		estado VARCHAR(30) NOT NULL DEFAULT 'activa',
		notificacion_estado VARCHAR(40) NOT NULL DEFAULT 'pendiente_firma',
		observaciones VARCHAR(500) DEFAULT NULL,
		creado_por INT DEFAULT NULL,
		fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		editado_por INT DEFAULT NULL,
		fecha_edicion DATETIME DEFAULT NULL,
		anulado_por INT DEFAULT NULL,
		fecha_anulacion DATETIME DEFAULT NULL,
		motivo_anulacion VARCHAR(500) DEFAULT NULL,
		PRIMARY KEY (id),
		KEY idx_funcionario_sanciones_usuario (cod_usuarioFK, fecha),
		KEY idx_funcionario_sanciones_estado (estado),
		KEY idx_funcionario_sanciones_notificacion (notificacion_estado)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if (!$mysqli->query($sql)) {
		return false;
	}
	$columnas=array(
		"documento_url" => "VARCHAR(255) DEFAULT NULL",
		"documento_nombre" => "VARCHAR(180) DEFAULT NULL",
		"notificacion_estado" => "VARCHAR(40) NOT NULL DEFAULT 'pendiente_firma'",
		"editado_por" => "INT DEFAULT NULL",
		"fecha_edicion" => "DATETIME DEFAULT NULL",
		"anulado_por" => "INT DEFAULT NULL",
		"fecha_anulacion" => "DATETIME DEFAULT NULL",
		"motivo_anulacion" => "VARCHAR(500) DEFAULT NULL"
	);
	foreach($columnas as $columna=>$definicion){
		if(function_exists("columnaUsuarioExiste") && !columnaUsuarioExiste($mysqli,"funcionario_sanciones",$columna)){
			$mysqli->query("ALTER TABLE funcionario_sanciones ADD COLUMN ".$columna." ".$definicion);
		}
	}
	return true;
}

function normalizarFechaSancionFuncionario($fecha)
{
	$fecha=trim((string)$fecha);
	return preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha) ? $fecha : date('Y-m-d');
}

function normalizarNotificacionSancionFuncionario($estado)
{
	$estado=strtolower(trim((string)$estado));
	$estado=str_replace(array("pendiente firma","pendiente-firma"),"pendiente_firma",$estado);
	$estado=str_replace(array("pendiente revision","pendiente-revision"),"pendiente_revision",$estado);
	$permitidos=array("pendiente_firma","pendiente_revision","notificada","no_corresponde");
	return in_array($estado,$permitidos,true) ? $estado : "pendiente_firma";
}

function guardarArchivoSancionFuncionario($cod_usuario,$archivo,$ext,$nombre_archivo)
{
	$archivo=trim((string)$archivo);
	$ext=strtolower(trim((string)$ext));
	if($archivo=="" || $ext==""){
		return array("url"=>"","nombre"=>"");
	}
	if(!extensionSolicitudAusenciaPermitida($ext)){
		throw new Exception("Formato de evidencia no permitido.");
	}
	$base=realpath(__DIR__.DIRECTORY_SEPARATOR."..");
	if($base===false){
		throw new Exception("No se pudo ubicar la carpeta base.");
	}
	$directorio=$base.DIRECTORY_SEPARATOR."archivos".DIRECTORY_SEPARATOR."funcionarios".DIRECTORY_SEPARATOR."sanciones".DIRECTORY_SEPARATOR.(int)$cod_usuario;
	if(!is_dir($directorio) && !mkdir($directorio,0775,true)){
		throw new Exception("No se pudo crear la carpeta de sanciones.");
	}
	$contenido=substr($archivo,strpos($archivo,",")!==false ? strpos($archivo,",")+1 : 0);
	$binario=base64_decode($contenido,true);
	if($binario===false){
		throw new Exception("No se pudo leer la evidencia.");
	}
	if(strlen($binario)>15728640){
		throw new Exception("La evidencia no puede superar 15 MB.");
	}
	$nombreSeguro=preg_replace('/[^a-zA-Z0-9_\.-]+/','_',pathinfo((string)$nombre_archivo,PATHINFO_FILENAME));
	if($nombreSeguro==""){$nombreSeguro="sancion";}
	$nombre=$nombreSeguro."_".date("Ymd_His")."_".rand(1000,9999).".".$ext;
	$ruta=$directorio.DIRECTORY_SEPARATOR.$nombre;
	if(file_put_contents($ruta,$binario)===false){
		throw new Exception("No se pudo guardar la evidencia.");
	}
	return array(
		"url"=>"/GoodVentaAsisCap/archivos/funcionarios/sanciones/".(int)$cod_usuario."/".$nombre,
		"nombre"=>$nombre_archivo!="" ? $nombre_archivo : $nombre
	);
}

function convertirSancionFuncionarioUtf8($row)
{
	$registro=array();
	foreach($row as $key=>$value){
		$registro[$key]=mb_convert_encoding((string)$value,'UTF-8','ISO-8859-1');
	}
	return $registro;
}

function obtenerSancionesFuncionario($mysqli,$cod_usuario,$limite=0)
{
	if(!asegurarTablaSancionesFuncionario($mysqli)){
		return array();
	}
	$sql="SELECT id,cod_usuarioFK,fecha,tipo,motivo,descripcion,documento_url,documento_nombre,estado,notificacion_estado,observaciones,creado_por,fecha_creacion,editado_por,fecha_edicion,anulado_por,fecha_anulacion,motivo_anulacion
		FROM funcionario_sanciones
		WHERE cod_usuarioFK=?
		ORDER BY fecha DESC,id DESC";
	if((int)$limite>0){
		$sql.=" LIMIT ".(int)$limite;
	}
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return array();}
	$s='s';
	$stmt->bind_param($s,$cod_usuario);
	if(!$stmt->execute()){
		$stmt->close();
		return array();
	}
	$result=$stmt->get_result();
	$registros=array();
	while($row=$result->fetch_assoc()){
		$registros[]=convertirSancionFuncionarioUtf8($row);
	}
	$stmt->close();
	return $registros;
}

function obtenerResumenSancionesFuncionario($mysqli,$cod_usuario)
{
	if(!asegurarTablaSancionesFuncionario($mysqli)){
		return array("total"=>0,"activas"=>0,"pendientes"=>0,"recientes"=>array());
	}
	$registros=obtenerSancionesFuncionario($mysqli,$cod_usuario,5);
	$total=0;
	$activas=0;
	$pendientes=0;
	$sql="SELECT
			COUNT(*) AS total,
			SUM(CASE WHEN estado='activa' THEN 1 ELSE 0 END) AS activas,
			SUM(CASE WHEN notificacion_estado IN ('pendiente_firma','pendiente_revision') THEN 1 ELSE 0 END) AS pendientes
		FROM funcionario_sanciones
		WHERE cod_usuarioFK=? AND estado NOT IN ('anulada','archivada')";
	$stmt=$mysqli->prepare($sql);
	if($stmt){
		$s='s';
		$stmt->bind_param($s,$cod_usuario);
		if($stmt->execute()){
			$result=$stmt->get_result();
			$row=$result->fetch_assoc();
			$total=isset($row['total']) ? (int)$row['total'] : 0;
			$activas=isset($row['activas']) ? (int)$row['activas'] : 0;
			$pendientes=isset($row['pendientes']) ? (int)$row['pendientes'] : 0;
		}
		$stmt->close();
	}
	return array(
		"total"=>$total,
		"activas"=>$activas,
		"pendientes"=>$pendientes,
		"recientes"=>$registros
	);
}

function buscarSancionesFuncionario($cod_usuario,$user)
{
	if(!usuarioPuedeAdministrarSancionesFuncionario($user)){
		echo json_encode(array("1"=>"NI","2"=>"Sin permiso."));
		exit;
	}
	$mysqli=conectar_al_servidor();
	$registros=obtenerSancionesFuncionario($mysqli,$cod_usuario,0);
	mysqli_close($mysqli);
	echo json_encode(array("1"=>"exito","2"=>$registros));
	exit;
}

function guardarSancionFuncionario($cod_usuario,$fecha,$tipo,$motivo,$descripcion,$observaciones,$notificacion,$nombre_archivo,$archivo,$ext,$user)
{
	try{
		if(!usuarioPuedeAdministrarSancionesFuncionario($user)){
			throw new Exception("Sin permiso.");
		}
		$cod_usuario=trim((string)$cod_usuario);
		$tipo=substr(trim((string)$tipo),0,80);
		$motivo=substr(trim((string)$motivo),0,180);
		$descripcion=substr((string)$descripcion,0,5000);
		$observaciones=substr((string)$observaciones,0,500);
		if($cod_usuario=="" || $tipo=="" || $motivo==""){
			throw new Exception("Faltan datos de la sancion.");
		}
		$mysqli=conectar_al_servidor();
		if(!asegurarTablaSancionesFuncionario($mysqli)){
			throw new Exception("No se pudo preparar la tabla de sanciones.");
		}
		$adjunto=guardarArchivoSancionFuncionario($cod_usuario,$archivo,$ext,$nombre_archivo);
		$fecha=normalizarFechaSancionFuncionario($fecha);
		$estado="activa";
		$notificacion=normalizarNotificacionSancionFuncionario($notificacion);
		$sql="INSERT INTO funcionario_sanciones
			(cod_usuarioFK,fecha,tipo,motivo,descripcion,documento_url,documento_nombre,estado,notificacion_estado,observaciones,creado_por)
			VALUES (?,?,?,?,?,?,?,?,?,?,?)";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo preparar la sancion.");}
		$ss='isssssssssi';
		$codInt=(int)$cod_usuario;
		$userInt=(int)$user;
		$stmt->bind_param($ss,$codInt,$fecha,$tipo,$motivo,$descripcion,$adjunto["url"],$adjunto["nombre"],$estado,$notificacion,$observaciones,$userInt);
		if(!$stmt->execute()){throw new Exception("No se pudo guardar la sancion.");}
		$id=$stmt->insert_id;
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array("1"=>"exito","2"=>"Sancion registrada.","3"=>$id));
		exit;
	}catch(Exception $e){
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function editarSancionFuncionario($id_sancion,$fecha,$tipo,$motivo,$descripcion,$observaciones,$notificacion,$user)
{
	try{
		if(!usuarioPuedeAdministrarSancionesFuncionario($user)){
			throw new Exception("Sin permiso.");
		}
		$tipo=substr(trim((string)$tipo),0,80);
		$motivo=substr(trim((string)$motivo),0,180);
		$descripcion=substr((string)$descripcion,0,5000);
		$observaciones=substr((string)$observaciones,0,500);
		if($tipo=="" || $motivo==""){
			throw new Exception("Faltan datos de la sancion.");
		}
		$mysqli=conectar_al_servidor();
		if(!asegurarTablaSancionesFuncionario($mysqli)){
			throw new Exception("No se pudo preparar la tabla de sanciones.");
		}
		$fecha=normalizarFechaSancionFuncionario($fecha);
		$notificacion=normalizarNotificacionSancionFuncionario($notificacion);
		$sql="UPDATE funcionario_sanciones
			SET fecha=?, tipo=?, motivo=?, descripcion=?, notificacion_estado=?, observaciones=?, editado_por=?, fecha_edicion=NOW()
			WHERE id=? AND estado<>'anulada'";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo preparar la edicion.");}
		$userInt=(int)$user;
		$idInt=(int)$id_sancion;
		$stmt->bind_param('ssssssii',$fecha,$tipo,$motivo,$descripcion,$notificacion,$observaciones,$userInt,$idInt);
		if(!$stmt->execute()){throw new Exception("No se pudo editar la sancion.");}
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array("1"=>"exito","2"=>"Sancion actualizada."));
		exit;
	}catch(Exception $e){
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function anularSancionFuncionario($id_sancion,$motivo,$user)
{
	try{
		if(!usuarioPuedeAdministrarSancionesFuncionario($user)){
			throw new Exception("Sin permiso.");
		}
		$motivo=substr(trim((string)$motivo),0,500);
		if($motivo==""){
			throw new Exception("Indique el motivo de anulacion.");
		}
		$mysqli=conectar_al_servidor();
		if(!asegurarTablaSancionesFuncionario($mysqli)){
			throw new Exception("No se pudo preparar la tabla de sanciones.");
		}
		$sql="UPDATE funcionario_sanciones
			SET estado='anulada', anulado_por=?, fecha_anulacion=NOW(), motivo_anulacion=?, editado_por=?, fecha_edicion=NOW()
			WHERE id=? AND estado<>'anulada'";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo preparar la anulacion.");}
		$userInt=(int)$user;
		$idInt=(int)$id_sancion;
		$stmt->bind_param('isii',$userInt,$motivo,$userInt,$idInt);
		if(!$stmt->execute()){throw new Exception("No se pudo anular la sancion.");}
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array("1"=>"exito","2"=>"Sancion anulada."));
		exit;
	}catch(Exception $e){
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function usuarioPuedeAdministrarSeguimientoFuncionario($cod_usuario)
{
	if (function_exists('controldeaccesoacasas')) {
		if (controldeaccesoacasas($cod_usuario, "EDITARLISTADOUSUARIO", " u.accion='SI' ") == 1) {
			return true;
		}
	}
	return false;
}

function usuarioPuedeVerSeguimientoFuncionario($cod_usuario)
{
	if (usuarioPuedeAdministrarSeguimientoFuncionario($cod_usuario)) {
		return true;
	}
	if (function_exists('controldeaccesoacasas')) {
		if (controldeaccesoacasas($cod_usuario, "VERLISTADOUSUARIO", " u.accion='SI' ") == 1) {
			return true;
		}
	}
	return false;
}

function asegurarTablaSeguimientoFuncionario($mysqli)
{
	static $preparada=false;
	if($preparada){return true;}
	$sql="CREATE TABLE IF NOT EXISTS funcionario_hilo_principal (
		id INT NOT NULL AUTO_INCREMENT,
		cod_usuarioFK INT NOT NULL,
		cod_interConsultaFK INT NOT NULL,
		estado VARCHAR(30) NOT NULL DEFAULT 'activo',
		observacion VARCHAR(500) DEFAULT NULL,
		motivo_cambio VARCHAR(500) DEFAULT NULL,
		cod_usuarioFK_vinculo INT DEFAULT NULL,
		fecha_vinculacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		cod_usuarioFK_edit INT DEFAULT NULL,
		fecha_edit DATETIME DEFAULT NULL,
		PRIMARY KEY (id),
		KEY idx_funcionario_hilo_usuario (cod_usuarioFK, estado, id),
		KEY idx_funcionario_hilo_interconsulta (cod_interConsultaFK),
		KEY idx_funcionario_hilo_fecha (fecha_vinculacion)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
	if(!$mysqli->query($sql)){
		return false;
	}
	$columnas=array(
		"observacion" => "VARCHAR(500) DEFAULT NULL",
		"motivo_cambio" => "VARCHAR(500) DEFAULT NULL",
		"cod_usuarioFK_vinculo" => "INT DEFAULT NULL",
		"fecha_vinculacion" => "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
		"cod_usuarioFK_edit" => "INT DEFAULT NULL",
		"fecha_edit" => "DATETIME DEFAULT NULL"
	);
	foreach($columnas as $columna=>$definicion){
		if(function_exists("columnaUsuarioExiste") && !columnaUsuarioExiste($mysqli,"funcionario_hilo_principal",$columna)){
			$mysqli->query("ALTER TABLE funcionario_hilo_principal ADD COLUMN ".$columna." ".$definicion);
		}
	}
	$preparada=true;
	return true;
}

function convertirSeguimientoFuncionarioUtf8($row)
{
	$registro=array();
	foreach($row as $key=>$value){
		$registro[$key]=mb_convert_encoding((string)$value,'UTF-8','ISO-8859-1');
	}
	return $registro;
}

function obtenerDatosFuncionarioSeguimiento($mysqli,$cod_usuario)
{
	$datos=array("nombre_persona"=>"Funcionario","cod_localFK"=>null);
	if(!function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"usuario") || !tablaUsuarioExiste($mysqli,"persona")){
		return $datos;
	}
	$sql="SELECT IFNULL(pr.nombre_persona,'Funcionario') AS nombre_persona, us.cod_localFK
		FROM usuario us
		LEFT JOIN persona pr ON pr.cod_persona=us.cod_usuario
		WHERE us.cod_usuario=? LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return $datos;}
	$codInt=(int)$cod_usuario;
	$stmt->bind_param('i',$codInt);
	if($stmt->execute()){
		$result=$stmt->get_result();
		if($row=$result->fetch_assoc()){
			$datos=$row;
		}
	}
	$stmt->close();
	return $datos;
}

function obtenerHiloPrincipalActivoFuncionario($mysqli,$cod_usuario)
{
	if(!asegurarTablaSeguimientoFuncionario($mysqli) || !function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"interconsulta")){
		return null;
	}
	$sql="SELECT fhp.id,fhp.cod_usuarioFK,fhp.cod_interConsultaFK,fhp.estado,fhp.observacion,fhp.motivo_cambio,
			fhp.cod_usuarioFK_vinculo,fhp.fecha_vinculacion,fhp.cod_usuarioFK_edit,fhp.fecha_edit,
			ic.asunto,ic.estado AS hilo_estado,ic.tipo,ic.fecha_creacion AS hilo_fecha_creacion,
			ic.fecha_edit AS hilo_fecha_edit,
			(SELECT nombre_persona FROM persona WHERE cod_persona=ic.cod_usuarioFK_create LIMIT 1) AS hilo_creador
		FROM funcionario_hilo_principal fhp
		LEFT JOIN interconsulta ic ON ic.cod_interConsulta=fhp.cod_interConsultaFK
		WHERE fhp.cod_usuarioFK=? AND fhp.estado='activo'
		ORDER BY fhp.id DESC LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return null;}
	$codInt=(int)$cod_usuario;
	$stmt->bind_param('i',$codInt);
	$row=null;
	if($stmt->execute()){
		$result=$stmt->get_result();
		$row=$result->fetch_assoc();
	}
	$stmt->close();
	return $row;
}

function contarPendientesSeguimientoFuncionario($mysqli,$cod_usuario,$cod_interConsulta)
{
	if((int)$cod_interConsulta<=0 || !function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"menciones") || !tablaUsuarioExiste($mysqli,"mensaje")){
		return 0;
	}
	$sql="SELECT COUNT(*) AS total
		FROM menciones mc
		INNER JOIN mensaje mj ON mj.cod_mensaje=mc.cod_mensajeFK
		WHERE mc.cod_usuarioFK=?
		AND mj.cod_interConsultaFK=?
		AND IFNULL(mc.isLeido,0)=0
		AND IFNULL(mj.estado,'activo')='activo'";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return 0;}
	$codUsuarioInt=(int)$cod_usuario;
	$codInterInt=(int)$cod_interConsulta;
	$stmt->bind_param('ii',$codUsuarioInt,$codInterInt);
	$total=0;
	if($stmt->execute()){
		$result=$stmt->get_result();
		$row=$result->fetch_assoc();
		$total=isset($row['total']) ? (int)$row['total'] : 0;
	}
	$stmt->close();
	return $total;
}

function contarMensajesSeguimientoFuncionario($mysqli,$cod_interConsulta)
{
	if((int)$cod_interConsulta<=0 || !function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"mensaje")){
		return 0;
	}
	$sql="SELECT COUNT(*) AS total FROM mensaje WHERE cod_interConsultaFK=? AND IFNULL(estado,'activo')='activo'";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return 0;}
	$codInterInt=(int)$cod_interConsulta;
	$stmt->bind_param('i',$codInterInt);
	$total=0;
	if($stmt->execute()){
		$result=$stmt->get_result();
		$row=$result->fetch_assoc();
		$total=isset($row['total']) ? (int)$row['total'] : 0;
	}
	$stmt->close();
	return $total;
}

function textoPendientesSeguimientoFuncionario($pendientes)
{
	$pendientes=(int)$pendientes;
	if($pendientes<=0){return "Seguimiento vinculado";}
	return $pendientes==1 ? "1 mensaje sin leer" : $pendientes." mensajes sin leer";
}

function obtenerResumenSeguimientoFuncionario($mysqli,$cod_usuario)
{
	$resumen=array(
		"vinculado"=>false,
		"cod_interConsulta"=>"",
		"asunto"=>"",
		"estado"=>"",
		"tipo"=>"",
		"texto"=>"Sin seguimiento vinculado",
		"clase"=>"muted",
		"pendientes"=>0,
		"mensajes"=>0,
		"fecha_vinculacion"=>"",
		"fecha_actualizacion"=>"",
		"creador"=>"",
		"observacion"=>""
	);
	if(!asegurarTablaSeguimientoFuncionario($mysqli)){
		return $resumen;
	}
	$hilo=obtenerHiloPrincipalActivoFuncionario($mysqli,$cod_usuario);
	if(!$hilo || empty($hilo['cod_interConsultaFK'])){
		return $resumen;
	}
	$codInter=(int)$hilo['cod_interConsultaFK'];
	$pendientes=contarPendientesSeguimientoFuncionario($mysqli,$cod_usuario,$codInter);
	$mensajes=contarMensajesSeguimientoFuncionario($mysqli,$codInter);
	$estado=isset($hilo['hilo_estado']) ? strtolower(trim((string)$hilo['hilo_estado'])) : "";
	$texto=textoPendientesSeguimientoFuncionario($pendientes);
	if($estado=="inactivo"){
		$texto="Seguimiento inactivo";
	}
	$clase=$pendientes>0 ? "info" : ($estado=="inactivo" ? "muted" : "ok");
	return array(
		"vinculado"=>true,
		"cod_interConsulta"=>$codInter,
		"asunto"=>mb_convert_encoding((string)($hilo['asunto'] ?? ""),'UTF-8','ISO-8859-1'),
		"estado"=>mb_convert_encoding((string)($hilo['hilo_estado'] ?? ""),'UTF-8','ISO-8859-1'),
		"tipo"=>mb_convert_encoding((string)($hilo['tipo'] ?? ""),'UTF-8','ISO-8859-1'),
		"texto"=>$texto,
		"clase"=>$clase,
		"pendientes"=>$pendientes,
		"mensajes"=>$mensajes,
		"fecha_vinculacion"=>mb_convert_encoding((string)($hilo['fecha_vinculacion'] ?? ""),'UTF-8','ISO-8859-1'),
		"fecha_actualizacion"=>mb_convert_encoding((string)(!empty($hilo['hilo_fecha_edit']) ? $hilo['hilo_fecha_edit'] : ($hilo['hilo_fecha_creacion'] ?? "")),'UTF-8','ISO-8859-1'),
		"creador"=>mb_convert_encoding((string)($hilo['hilo_creador'] ?? ""),'UTF-8','ISO-8859-1'),
		"observacion"=>mb_convert_encoding((string)($hilo['observacion'] ?? ""),'UTF-8','ISO-8859-1')
	);
}

function insertarMensajeSistemaSeguimientoFuncionario($mysqli,$cod_usuario,$cod_interConsulta,$contenido,$user)
{
	if(!function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"mensaje") || !tablaUsuarioExiste($mysqli,"menciones")){
		return "";
	}
	$contenido=substr(trim((string)$contenido),0,5000);
	if($contenido==""){
		$contenido="Actualizacion de seguimiento administrativo.";
	}
	$sql="INSERT INTO mensaje (contenido, fecha_creacion, cod_interConsultaFK, cod_usuarioFK, cod_dictamenFK)
		VALUES (?, NOW(), ?, ?, NULL)";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){throw new Exception("No se pudo registrar el mensaje de seguimiento.");}
	$codInterInt=(int)$cod_interConsulta;
	$userInt=(int)$user;
	$stmt->bind_param('sii',$contenido,$codInterInt,$userInt);
	if(!$stmt->execute()){throw new Exception("No se pudo registrar el mensaje de seguimiento.");}
	$codMensaje=$stmt->insert_id;
	$stmt->close();

	$usuarios=array_unique(array((int)$cod_usuario,(int)$user));
	foreach($usuarios as $codMencionado){
		if($codMencionado<=0){continue;}
		$sqlMencion="INSERT INTO menciones (cod_usuarioFK, cod_mensajeFK, isLeido) VALUES (?, ?, 1)";
		$stmtMencion=$mysqli->prepare($sqlMencion);
		if($stmtMencion){
			$stmtMencion->bind_param('ii',$codMencionado,$codMensaje);
			$stmtMencion->execute();
			$stmtMencion->close();
		}
	}
	return $codMensaje;
}

function asegurarParticipacionSeguimientoFuncionario($mysqli,$cod_usuario,$cod_interConsulta,$user,$motivo)
{
	if(!function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"menciones") || !tablaUsuarioExiste($mysqli,"mensaje")){
		return;
	}
	$sql="SELECT COUNT(*) AS total
		FROM menciones mc
		INNER JOIN mensaje mj ON mj.cod_mensaje=mc.cod_mensajeFK
		WHERE mj.cod_interConsultaFK=? AND mc.cod_usuarioFK=?";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return;}
	$codInterInt=(int)$cod_interConsulta;
	$codUsuarioInt=(int)$cod_usuario;
	$stmt->bind_param('ii',$codInterInt,$codUsuarioInt);
	$total=0;
	if($stmt->execute()){
		$result=$stmt->get_result();
		$row=$result->fetch_assoc();
		$total=isset($row['total']) ? (int)$row['total'] : 0;
	}
	$stmt->close();
	if($total>0){return;}
	$datosFuncionario=obtenerDatosFuncionarioSeguimiento($mysqli,$cod_usuario);
	$nombre=$datosFuncionario['nombre_persona'] ?? "Funcionario";
	$contenido="Funcionario vinculado al seguimiento administrativo: ".$nombre;
	if(trim((string)$motivo)!=""){
		$contenido.=". Motivo: ".trim((string)$motivo);
	}
	insertarMensajeSistemaSeguimientoFuncionario($mysqli,$cod_usuario,$cod_interConsulta,$contenido,$user);
}

function validarInterConsultaSeguimientoFuncionario($mysqli,$cod_interConsulta)
{
	if(!function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"interconsulta")){
		throw new Exception("El modulo de hilos no esta disponible.");
	}
	$sql="SELECT cod_interConsulta,asunto,estado,tipo FROM interconsulta WHERE cod_interConsulta=? LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){throw new Exception("No se pudo validar el hilo.");}
	$codInterInt=(int)$cod_interConsulta;
	$stmt->bind_param('i',$codInterInt);
	$row=null;
	if($stmt->execute()){
		$result=$stmt->get_result();
		$row=$result->fetch_assoc();
	}
	$stmt->close();
	if(!$row){
		throw new Exception("El hilo indicado no existe.");
	}
	if(strtolower(trim((string)$row['estado']))=="inactivo"){
		throw new Exception("No se puede vincular un hilo inactivo.");
	}
	return $row;
}

function guardarVinculoSeguimientoFuncionarioInterno($mysqli,$cod_usuario,$cod_interConsulta,$observacion,$motivo,$user)
{
	if(!asegurarTablaSeguimientoFuncionario($mysqli)){
		throw new Exception("No se pudo preparar la tabla de seguimiento.");
	}
	$codUsuarioInt=(int)$cod_usuario;
	$codInterInt=(int)$cod_interConsulta;
	$userInt=(int)$user;
	$observacion=substr(trim((string)$observacion),0,500);
	$motivo=substr(trim((string)$motivo),0,500);
	$stmtBloqueo=$mysqli->prepare("SELECT cod_usuario FROM usuario WHERE cod_usuario=? FOR UPDATE");
	if(!$stmtBloqueo){throw new Exception("No se pudo bloquear el colaborador para evitar hilos duplicados.");}
	$stmtBloqueo->bind_param('i',$codUsuarioInt);
	if(!$stmtBloqueo->execute() || $stmtBloqueo->get_result()->num_rows<1){$stmtBloqueo->close();throw new Exception("El colaborador indicado no existe.");}
	$stmtBloqueo->close();
	$actual=obtenerHiloPrincipalActivoFuncionario($mysqli,$codUsuarioInt);
	if($actual && (int)$actual['cod_interConsultaFK']==$codInterInt){
		$sql="UPDATE funcionario_hilo_principal
			SET observacion=?, cod_usuarioFK_edit=?, fecha_edit=NOW(), motivo_cambio=?
			WHERE id=?";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo actualizar el vinculo de seguimiento.");}
		$idActual=(int)$actual['id'];
		$stmt->bind_param('sisi',$observacion,$userInt,$motivo,$idActual);
		if(!$stmt->execute()){throw new Exception("No se pudo actualizar el vinculo de seguimiento.");}
		$stmt->close();
		return obtenerResumenSeguimientoFuncionario($mysqli,$codUsuarioInt);
	}
	if($actual && $motivo==""){
		throw new Exception("Indique el motivo para cambiar el seguimiento principal.");
	}
	if($actual){
		$sql="UPDATE funcionario_hilo_principal
			SET estado='inactivo', cod_usuarioFK_edit=?, fecha_edit=NOW(), motivo_cambio=?
			WHERE cod_usuarioFK=? AND estado='activo'";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){throw new Exception("No se pudo cerrar el vinculo anterior.");}
		$stmt->bind_param('isi',$userInt,$motivo,$codUsuarioInt);
		if(!$stmt->execute()){throw new Exception("No se pudo cerrar el vinculo anterior.");}
		$stmt->close();
	}
	$sql="INSERT INTO funcionario_hilo_principal
		(cod_usuarioFK,cod_interConsultaFK,estado,observacion,motivo_cambio,cod_usuarioFK_vinculo,fecha_vinculacion)
		VALUES (?,?,'activo',?,?,?,NOW())";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){throw new Exception("No se pudo guardar el seguimiento principal.");}
	$stmt->bind_param('iissi',$codUsuarioInt,$codInterInt,$observacion,$motivo,$userInt);
	if(!$stmt->execute()){throw new Exception("No se pudo guardar el seguimiento principal.");}
	$stmt->close();
	return obtenerResumenSeguimientoFuncionario($mysqli,$codUsuarioInt);
}

function buscarSeguimientoFuncionario($cod_usuario,$user)
{
	if(!usuarioPuedeVerSeguimientoFuncionario($user)){
		echo json_encode(array("1"=>"NI","2"=>"Sin permiso."));
		exit;
	}
	$mysqli=conectar_al_servidor();
	$resumen=obtenerResumenSeguimientoFuncionario($mysqli,$cod_usuario);
	mysqli_close($mysqli);
	echo json_encode(array("1"=>"exito","2"=>$resumen));
	exit;
}

function vincularSeguimientoFuncionario($cod_usuario,$cod_interConsulta,$observacion,$motivo,$user)
{
	$mysqli=null;
	try{
		if(!usuarioPuedeAdministrarSeguimientoFuncionario($user)){
			throw new Exception("Sin permiso.");
		}
		if((int)$cod_usuario<=0 || (int)$cod_interConsulta<=0){
			throw new Exception("Indique funcionario e hilo valido.");
		}
		$mysqli=conectar_al_servidor();
		if(method_exists($mysqli,'begin_transaction')){$mysqli->begin_transaction();}else{$mysqli->autocommit(false);}
		validarInterConsultaSeguimientoFuncionario($mysqli,$cod_interConsulta);
		$codInterNormalizar=(int)$cod_interConsulta;
		$stmtTipo=$mysqli->prepare("UPDATE interconsulta SET tipo='colaborador',cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE cod_interConsulta=?");
		if(!$stmtTipo){throw new Exception("No se pudo ubicar el hilo en Pagos y Egresos.");}
		$userTipo=(int)$user;
		$stmtTipo->bind_param('ii',$userTipo,$codInterNormalizar);
		if(!$stmtTipo->execute()){$stmtTipo->close();throw new Exception("No se pudo ubicar el hilo en Pagos y Egresos.");}
		$stmtTipo->close();
		$resumen=guardarVinculoSeguimientoFuncionarioInterno($mysqli,$cod_usuario,$cod_interConsulta,$observacion,$motivo,$user);
		asegurarParticipacionSeguimientoFuncionario($mysqli,$cod_usuario,$cod_interConsulta,$user,$motivo);
		$resumen=obtenerResumenSeguimientoFuncionario($mysqli,$cod_usuario);
		$mysqli->commit();
		mysqli_close($mysqli);
		echo json_encode(array("1"=>"exito","2"=>"Seguimiento vinculado.","3"=>$resumen));
		exit;
	}catch(Exception $e){
		if($mysqli){$mysqli->rollback();mysqli_close($mysqli);}
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function crearSeguimientoFuncionario($cod_usuario,$asunto,$observacion,$motivo,$user)
{
	$mysqli=null;
	try{
		if(!usuarioPuedeAdministrarSeguimientoFuncionario($user)){
			throw new Exception("Sin permiso.");
		}
		if((int)$cod_usuario<=0){
			throw new Exception("Indique un funcionario valido.");
		}
		$mysqli=conectar_al_servidor();
		if(!function_exists("tablaUsuarioExiste") || !tablaUsuarioExiste($mysqli,"interconsulta") || !tablaUsuarioExiste($mysqli,"mensaje") || !tablaUsuarioExiste($mysqli,"menciones")){
			throw new Exception("El modulo de hilos no esta disponible.");
		}
		if(method_exists($mysqli,'begin_transaction')){$mysqli->begin_transaction();}else{$mysqli->autocommit(false);}
		$codUsuarioBloqueo=(int)$cod_usuario;
		$stmtBloqueo=$mysqli->prepare("SELECT cod_usuario FROM usuario WHERE cod_usuario=? AND estado='Activo' FOR UPDATE");
		if(!$stmtBloqueo){throw new Exception("No se pudo bloquear el colaborador.");}
		$stmtBloqueo->bind_param('i',$codUsuarioBloqueo);
		if(!$stmtBloqueo->execute() || $stmtBloqueo->get_result()->num_rows<1){$stmtBloqueo->close();throw new Exception("El colaborador no esta activo.");}
		$stmtBloqueo->close();
		$hiloExistente=obtenerHiloPrincipalActivoFuncionario($mysqli,$cod_usuario);
		if($hiloExistente && (int)$hiloExistente['cod_interConsultaFK']>0 && strtolower(trim((string)$hiloExistente['hilo_estado']))!='inactivo'){
			$codExistente=(int)$hiloExistente['cod_interConsultaFK'];
			$stmtTipo=$mysqli->prepare("UPDATE interconsulta SET tipo='colaborador',cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE cod_interConsulta=?");
			if(!$stmtTipo){throw new Exception("No se pudo normalizar el hilo existente.");}
			$userTipo=(int)$user;
			$stmtTipo->bind_param('ii',$userTipo,$codExistente);
			if(!$stmtTipo->execute()){$stmtTipo->close();throw new Exception("No se pudo normalizar el hilo existente.");}
			$stmtTipo->close();
			$resumen=obtenerResumenSeguimientoFuncionario($mysqli,$cod_usuario);
			$mysqli->commit();
			mysqli_close($mysqli);
			echo json_encode(array("1"=>"exito","2"=>"El colaborador ya tiene un hilo principal; no se creo un duplicado.","3"=>$resumen));
			exit;
		}
		$datosFuncionario=obtenerDatosFuncionarioSeguimiento($mysqli,$cod_usuario);
		$nombreFuncionario=$datosFuncionario['nombre_persona'] ?? "Funcionario";
		$asunto=substr(trim((string)$asunto),0,180);
		if($asunto==""){
			$asunto="Colaborador - ".$nombreFuncionario;
		}
		$observacion=substr(trim((string)$observacion),0,500);
		if($observacion==""){
			$observacion="Hilo principal de seguimiento administrativo del funcionario.";
		}
		$estado="proceso";
		$tipo="colaborador";
		$userInt=(int)$user;
		$codLocal=isset($datosFuncionario['cod_localFK']) && (int)$datosFuncionario['cod_localFK']>0 ? (int)$datosFuncionario['cod_localFK'] : null;
		if($codLocal){
			$sql="INSERT INTO interconsulta (asunto,observacion,estado,tipo,cod_ventaFK,cod_usuarioFK_create,fecha_creacion,cod_localFK,monto_limite)
				VALUES (?,?,?,?,NULL,?,NOW(),?,NULL)";
			$stmt=$mysqli->prepare($sql);
			if(!$stmt){throw new Exception("No se pudo crear el seguimiento.");}
			$stmt->bind_param('ssssii',$asunto,$observacion,$estado,$tipo,$userInt,$codLocal);
		}else{
			$sql="INSERT INTO interconsulta (asunto,observacion,estado,tipo,cod_ventaFK,cod_usuarioFK_create,fecha_creacion,cod_localFK,monto_limite)
				VALUES (?,?,?,?,NULL,?,NOW(),NULL,NULL)";
			$stmt=$mysqli->prepare($sql);
			if(!$stmt){throw new Exception("No se pudo crear el seguimiento.");}
			$stmt->bind_param('ssssi',$asunto,$observacion,$estado,$tipo,$userInt);
		}
		if(!$stmt->execute()){throw new Exception("No se pudo crear el seguimiento.");}
		$codInter=$stmt->insert_id;
		$stmt->close();
		$contenido="Seguimiento administrativo creado para ".$nombreFuncionario.".";
		if($observacion!=""){
			$contenido.=" ".$observacion;
		}
		insertarMensajeSistemaSeguimientoFuncionario($mysqli,$cod_usuario,$codInter,$contenido,$user);
		$resumen=guardarVinculoSeguimientoFuncionarioInterno($mysqli,$cod_usuario,$codInter,$observacion,$motivo,$user);
		$mysqli->commit();
		mysqli_close($mysqli);
		echo json_encode(array("1"=>"exito","2"=>"Seguimiento creado.","3"=>$resumen));
		exit;
	}catch(Exception $e){
		if($mysqli){$mysqli->rollback();mysqli_close($mysqli);}
		echo json_encode(array("1"=>"error","2"=>$e->getMessage()));
		exit;
	}
}

function obtenerPendientesHiloFuncionario($mysqli,$cod_usuario)
{
	$resumen=obtenerResumenSeguimientoFuncionario($mysqli,$cod_usuario);
	return isset($resumen['pendientes']) ? (int)$resumen['pendientes'] : 0;
}

function BuscarRegistro($codigo,$documento,$usuario,$estado,$local)
{
$mysqli=conectar_al_servidor();
asegurarTablaUsuarioDocumentos($mysqli);

$sqlFiltro= "where us.estado= '$estado' ";
if($codigo!=""){
	$sqlFiltro.= " and us.cod_usuario = '".$codigo."' ";
}
if($documento!=""){
	$sqlFiltro.=" and us.rut_usuario = '".$documento."' ";
}
if($usuario!=""){
	$sqlFiltro.=" and pr.nombre_persona like '%".$usuario."%' ";
}
if($local!=""){
	$sqlFiltro.=" and us.cod_localFK = '".$local."' ";
}



$sql= "select us.cod_usuario,us.rut_usuario,us.login,us.estado,us.acceso,us.cod_localFK,pr.nombre_persona,pr.telefono,
pr.tipo_relacion, pr.direccion,pr.telefono_referencia,us.fecha_creacion,
(select ud.fecha_inicio_contrato from usuario_documentos ud where ud.cod_usuarioFK=us.cod_usuario and ud.tipo_documento in ('contrato_laboral','contrato_firmado') and IFNULL(ud.estado,'')<>'Inactivo' order by ud.fecha_carga desc,ud.id desc limit 1) as fecha_inicio_contrato,
(select ud.fecha_fin_contrato from usuario_documentos ud where ud.cod_usuarioFK=us.cod_usuario and ud.tipo_documento in ('contrato_laboral','contrato_firmado') and IFNULL(ud.estado,'')<>'Inactivo' order by ud.fecha_carga desc,ud.id desc limit 1) as fecha_fin_contrato,
(select ud.contrato_sin_vencimiento from usuario_documentos ud where ud.cod_usuarioFK=us.cod_usuario and ud.tipo_documento in ('contrato_laboral','contrato_firmado') and IFNULL(ud.estado,'')<>'Inactivo' order by ud.fecha_carga desc,ud.id desc limit 1) as contrato_sin_vencimiento,
(select Nombre from local where cod_local= us.cod_localFK limit 1 ) as local,tipo,url
 from  persona pr inner join  usuario us on us.cod_usuario=pr.cod_persona ".$sqlFiltro;
 
$pagina = "";
$paginaSelect= "";
$stmt = $mysqli->prepare($sql);
if ( ! $stmt->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
exit;
}

$result = $stmt->get_result();
$valor= mysqli_num_rows($result);
$nroRegistro=$valor;
$styleName="tableRegistroSearch";
$registros= array();

if ($valor>0)
{
while ($valor= mysqli_fetch_assoc($result))
{  
$cod_usuario = mb_convert_encoding((string)($valor['cod_usuario']), 'UTF-8', 'ISO-8859-1'); 
$rut_usuario = mb_convert_encoding((string)($valor['rut_usuario']), 'UTF-8', 'ISO-8859-1');          
$login = mb_convert_encoding((string)($valor['login']), 'UTF-8', 'ISO-8859-1');          
$password = ""; 
$estado = mb_convert_encoding((string)($valor['estado']), 'UTF-8', 'ISO-8859-1'); 
$acceso = mb_convert_encoding((string)($valor['acceso']), 'UTF-8', 'ISO-8859-1'); 
$cod_localFK = mb_convert_encoding((string)($valor['cod_localFK']), 'UTF-8', 'ISO-8859-1'); 
$nombre_persona = mb_convert_encoding((string)($valor['nombre_persona']), 'UTF-8', 'ISO-8859-1'); 
$telefono = mb_convert_encoding((string)($valor['telefono']), 'UTF-8', 'ISO-8859-1'); 
$local = mb_convert_encoding((string)($valor['local']), 'UTF-8', 'ISO-8859-1'); 
$tipo = mb_convert_encoding((string)($valor['tipo']), 'UTF-8', 'ISO-8859-1'); 
$url = mb_convert_encoding((string)($valor['url']), 'UTF-8', 'ISO-8859-1'); 
$telefono_referencia = mb_convert_encoding((string)($valor['telefono_referencia']), 'UTF-8', 'ISO-8859-1');
$direccion = mb_convert_encoding((string)($valor['direccion']), 'UTF-8', 'ISO-8859-1');
$tipo_relacion = mb_convert_encoding((string)($valor['tipo_relacion']), 'UTF-8', 'ISO-8859-1');
$fecha_creacion = mb_convert_encoding((string)($valor['fecha_creacion']), 'UTF-8', 'ISO-8859-1');
$fecha_inicio_contrato = mb_convert_encoding((string)($valor['fecha_inicio_contrato']), 'UTF-8', 'ISO-8859-1');
$fecha_fin_contrato = mb_convert_encoding((string)($valor['fecha_fin_contrato']), 'UTF-8', 'ISO-8859-1');
$contrato_sin_vencimiento = mb_convert_encoding((string)($valor['contrato_sin_vencimiento']), 'UTF-8', 'ISO-8859-1');

$horarios_usuario_json = buscarHorariosUsuario($mysqli,$cod_usuario);
$resumenes_control = calcularResumenesControlFuncionario($mysqli,$cod_usuario,$horarios_usuario_json);
$sanciones_resumen = obtenerResumenSancionesFuncionario($mysqli,$cod_usuario);
$seguimiento_resumen = obtenerResumenSeguimientoFuncionario($mysqli,$cod_usuario);
$hilo_pendientes = isset($seguimiento_resumen['pendientes']) ? (int)$seguimiento_resumen['pendientes'] : 0;
$horarios_usuario_json = json_encode($horarios_usuario_json);

	    	 $styleName=CargarStyleTable($styleName);
		  	  $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosabmusuario(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod_usuario."</td>
<td  id='td_datos_2' style='width:10%'>".$rut_usuario."</td>
<td id='td_datos_1' style='width:10%'>".$nombre_persona."</td>
<td  id='td_datos_3' style='display:none'>".$login."</td>
<td  id='td_datos_4' style='display:none'>".$password."</td>
<td  id='td_datos_5' style='display:none'>".$estado."</td>
<td  id='td_datos_6' style='display:none'>".$acceso."</td>
<td  id='td_datos_7' style='display:none'>".$cod_localFK."</td>
<td  id='td_datos_8' style='display:none'>".$telefono."</td>
<td  id='td_datos_9' style='width:10%'>".$local."</td>
<td  id='td_datos_10' style='display:none'>".$tipo."</td>
<td  id='td_datos_11' style='display:none'>".$url."</td>
<td  id='td_datos_12' style='display:none'>".$telefono_referencia."</td>
<td  id='td_datos_13' style='display:none'>".$direccion."</td>
<td  id='td_datos_14' style='display:none'>".$tipo_relacion."</td>
<td  id='td_datos_15' style='display:none'>".$fecha_creacion."</td>
<td  id='td_datos_23' style='display:none'>".$fecha_inicio_contrato."</td>
<td  id='td_datos_24' style='display:none'>".$fecha_fin_contrato."</td>
<td  id='td_datos_25' style='display:none'>".$contrato_sin_vencimiento."</td>
<td id='td_datos_22' style='display: none'>".$horarios_usuario_json."</td>
</tr>
</table>";

$paginaSelect .= '<option value="'.$cod_usuario.'">'.$nombre_persona.'</option>';

$registros[] = array(
	'cod_usuario' => $cod_usuario,
	'rut_usuario' => $rut_usuario,
	'login' => $login,
	//'password' => $password,
	'estado' => $estado,
	'acceso' => $acceso,
	'cod_localFK' => $cod_localFK,
	'nombre_persona' => $nombre_persona,
	'telefono' => $telefono,
	'local' => $local,
	'tipo' => $tipo,
	'url' => $url,
	'telefono_referencia' => $telefono_referencia,
	'direccion' => $direccion,
	'tipo_relacion' => $tipo_relacion,
	'fecha_creacion' => $fecha_creacion,
	'fecha_inicio_contrato' => $fecha_inicio_contrato,
	'fecha_fin_contrato' => $fecha_fin_contrato,
	'contrato_sin_vencimiento' => $contrato_sin_vencimiento,
	'horarios_usuario' => json_decode($horarios_usuario_json, true),
	'asistencia_resumen' => $resumenes_control['asistencia_resumen'],
	'reposo_resumen' => $resumenes_control['reposo_resumen'],
	'permiso_vacacion_resumen' => $resumenes_control['permiso_vacacion_resumen'],
	'solicitudes_ausencia' => $resumenes_control['solicitudes_ausencia'],
	'solicitudes_ausencia_pendientes' => $resumenes_control['solicitudes_ausencia_pendientes'],
	'proximas_ausencias' => $resumenes_control['proximas_ausencias'],
	'solicitudes_pendientes' => $resumenes_control['solicitudes_ausencia_pendientes'],
	'hilo_pendientes' => $hilo_pendientes,
	'seguimiento_resumen' => $seguimiento_resumen,
	'ausencias_sin_justificar' => isset($resumenes_control['asistencia_resumen']['ausencias_periodo']) ? $resumenes_control['asistencia_resumen']['ausencias_periodo'] : 0,
	'sanciones_resumen' => $sanciones_resumen,
	'sanciones_recientes' => isset($sanciones_resumen['recientes']) ? $sanciones_resumen['recientes'] : array(),
);
}
}


$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro, "4" => $registros, "5" => $paginaSelect);
echo json_encode($informacion);	
exit;
}

function obtenerUsuariosAnteriores($filtros = array())
{
	$mysqli=conectar_al_servidor();

	$sqlFiltro= "";
	foreach ($filtros as $key => $value) {
		if ($value === null || $value === "") {continue;}
		if ($sqlFiltro == "") {
			$sqlFiltro .= "WHERE ";
		} else {
			$sqlFiltro .= " AND ";
		}

		if (is_numeric($value)) {
			$sqlFiltro .= "hpu.$key = $value";
		} else {
			$sqlFiltro .= "hpu.$key like '%$value%'";
		}
	}

	$sql= "SELECT
		hpu.*
		FROM historial_personas_usuario hpu
		$sqlFiltro
		ORDER BY hpu.fecha_cambio DESC, hpu.id DESC";

	$stmt = $mysqli->prepare($sql);
	if (!$stmt->execute()) {
		echo json_encode(array("1" => "error", "2" => "Error al obtener historial de usuarios", "sql" => $sql, "detalle" => $stmt->error));
		exit;
	}

	$result = $stmt->get_result();
	$registros= array();
	while ($row = $result->fetch_assoc()) {
		$reg= array();
		foreach ($row as $key => $value) {
			$reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
		}
		$registros[] = $reg;
	}

	$stmt->close();
	return $registros;
}

function tablaUsuarioExiste($mysqli,$tabla)
{
	$sql="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return false;}
	$s='s';
	$stmt->bind_param($s,$tabla);
	if(!$stmt->execute()){
		$stmt->close();
		return false;
	}
	$total=0;
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	return ((int)$total)>0;
}

function columnaUsuarioExiste($mysqli,$tabla,$columna)
{
	$sql="SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return false;}
	$s='ss';
	$stmt->bind_param($s,$tabla,$columna);
	if(!$stmt->execute()){
		$stmt->close();
		return false;
	}
	$total=0;
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	return ((int)$total)>0;
}

function obtenerDatosUsuarioAuditoria($mysqli,$cod_usuario)
{
	asegurarCampoVencimientoContratoUsuario($mysqli);
	$datos=array();
	$sql="SELECT pr.nombre_persona,pr.telefono,pr.telefono_referencia,pr.direccion,pr.tipo_relacion,
		us.rut_usuario,us.login,us.password,us.estado,us.acceso,us.cod_localFK,us.tipo,us.fecha_creacion,IFNULL(us.fecha_vencimiento_contrato,'') as fecha_vencimiento_contrato
		FROM persona pr INNER JOIN usuario us ON us.cod_usuario=pr.cod_persona
		WHERE us.cod_usuario=? LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return $datos;}
	$s='s';
	$stmt->bind_param($s,$cod_usuario);
	if(!$stmt->execute()){
		$stmt->close();
		return $datos;
	}
	$result=$stmt->get_result();
	if($row=$result->fetch_assoc()){
		foreach($row as $key=>$value){
			$datos[$key]=(string)$value;
		}
	}
	$stmt->close();
	$datos["horarios_usuario"] = json_encode(buscarHorariosUsuario($mysqli,$cod_usuario));
	return $datos;
}

function registrarHistorialCambiosUsuario($mysqli,$cod_usuario,$anterior,$nuevo,$cod_usuario_modifico,$origen)
{
	if(!tablaUsuarioExiste($mysqli,"usuario_historial_cambios")){
		return;
	}
	$campos=array(
		"nombre_persona" => "Nombre completo",
		"telefono" => "Telefono/WhatsApp",
		"telefono_referencia" => "Contacto de emergencia",
		"direccion" => "Direccion",
		"tipo_relacion" => "Tipo de relacion",
		"rut_usuario" => "Documento/cedula",
		"login" => "Login",
		"password" => "Contrasena",
		"estado" => "Estado laboral",
		"acceso" => "Rol de acceso",
		"cod_localFK" => "Sucursal principal",
		"tipo" => "Tipo de usuario",
		"fecha_creacion" => "Fecha de ingreso/creacion",
		"fecha_vencimiento_contrato" => "Contrato vigente hasta",
		"horarios_usuario" => "Jornada laboral esperada"
	);
	$sql="INSERT INTO usuario_historial_cambios
		(cod_usuarioFK,campo,valor_anterior,valor_nuevo,fecha_hora,cod_usuario_modifico,origen,estado)
		VALUES (?,?,?,?,NOW(),?,?,?)";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return;}
	foreach($campos as $campo=>$etiqueta){
		$valorAnterior=isset($anterior[$campo]) ? (string)$anterior[$campo] : "";
		$valorNuevo=isset($nuevo[$campo]) ? (string)$nuevo[$campo] : "";
		if($valorAnterior===$valorNuevo){
			continue;
		}
		if($campo=="password"){
			$valorAnterior=$valorAnterior!="" ? "[protegido]" : "";
			$valorNuevo=$valorNuevo!="" ? "[actualizada]" : "";
		}
		$estado="Registrado";
		$ss='sssssss';
		$stmt->bind_param($ss,$cod_usuario,$etiqueta,$valorAnterior,$valorNuevo,$cod_usuario_modifico,$origen,$estado);
		$stmt->execute();
	}
	$stmt->close();
}

function obtenerHistorialCambiosUsuario($cod_usuario)
{
	$mysqli=conectar_al_servidor();
	if(!tablaUsuarioExiste($mysqli,"usuario_historial_cambios")){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "exito", "2" => "<div class='funcionario-empty-state'>La tabla de auditoria aun no fue creada. Ejecuta la migracion aditiva para comenzar a registrar cambios.</div>"));
		exit;
	}
	$sql="SELECT uhc.fecha_hora,uhc.campo,uhc.valor_anterior,uhc.valor_nuevo,uhc.origen,uhc.estado,
		IFNULL(pr.nombre_persona,uhc.cod_usuario_modifico) AS modificado_por
		FROM usuario_historial_cambios uhc
		LEFT JOIN persona pr ON pr.cod_persona=uhc.cod_usuario_modifico
		WHERE uhc.cod_usuarioFK=?
		ORDER BY uhc.fecha_hora DESC, uhc.id DESC
		LIMIT 60";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error"));
		exit;
	}
	$s='s';
	$stmt->bind_param($s,$cod_usuario);
	if(!$stmt->execute()){
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error"));
		exit;
	}
	$result=$stmt->get_result();
	$pagina="<table class='funcionario-ficha__table'><thead><tr><th>Fecha</th><th>Campo</th><th>Valor anterior</th><th>Valor nuevo</th><th>Modificado por</th><th>Origen</th><th>Estado</th></tr></thead><tbody>";
	$total=0;
	while($valor=$result->fetch_assoc()){
		$total++;
		$fecha=htmlspecialchars(mb_convert_encoding((string)$valor["fecha_hora"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$campo=htmlspecialchars(mb_convert_encoding((string)$valor["campo"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$anterior=htmlspecialchars(mb_convert_encoding((string)$valor["valor_anterior"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$nuevo=htmlspecialchars(mb_convert_encoding((string)$valor["valor_nuevo"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$modificado=htmlspecialchars(mb_convert_encoding((string)$valor["modificado_por"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$origen=htmlspecialchars(mb_convert_encoding((string)$valor["origen"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$estado=htmlspecialchars(mb_convert_encoding((string)$valor["estado"], 'UTF-8', 'ISO-8859-1'),ENT_QUOTES,'UTF-8');
		$pagina.="<tr><td>".$fecha."</td><td>".$campo."</td><td><s>".$anterior."</s></td><td>".$nuevo."</td><td>".$modificado."</td><td>".$origen."</td><td>".$estado."</td></tr>";
	}
	$pagina.="</tbody></table>";
	if($total==0){
		$pagina="<div class='funcionario-empty-state'>Todavia no hay cambios registrados en la auditoria.</div>";
	}
	$stmt->close();
	mysqli_close($mysqli);
	echo json_encode(array("1" => "exito", "2" => $pagina));
	exit;
}

function asegurarTablaUsuarioDocumentos($mysqli)
{
	$sql="CREATE TABLE IF NOT EXISTS usuario_documentos (
	  id INT(11) NOT NULL AUTO_INCREMENT,
	  cod_usuarioFK INT(11) NOT NULL,
	  tipo_documento VARCHAR(80) NOT NULL,
	  nombre_archivo VARCHAR(180) NOT NULL,
	  url_archivo VARCHAR(255) NOT NULL,
	  estado VARCHAR(45) DEFAULT 'Activo',
	  fecha_carga DATETIME DEFAULT CURRENT_TIMESTAMP,
	  cod_usuarioFK_carga INT(11) DEFAULT NULL,
	  observacion VARCHAR(255) DEFAULT NULL,
	  fecha_inicio_contrato DATE DEFAULT NULL,
	  fecha_fin_contrato DATE DEFAULT NULL,
	  contrato_sin_vencimiento TINYINT(1) NOT NULL DEFAULT 0,
	  PRIMARY KEY (id),
	  KEY idx_usuario_documentos_usuario (cod_usuarioFK),
	  KEY idx_usuario_documentos_tipo (tipo_documento)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	if(!$mysqli->query($sql)){
		return false;
	}
	$columnas=array(
		"fecha_inicio_contrato" => "DATE DEFAULT NULL",
		"fecha_fin_contrato" => "DATE DEFAULT NULL",
		"contrato_sin_vencimiento" => "TINYINT(1) NOT NULL DEFAULT 0"
	);
	foreach($columnas as $columna => $definicion){
		if(!columnaUsuarioExiste($mysqli,"usuario_documentos",$columna)){
			$mysqli->query("ALTER TABLE usuario_documentos ADD COLUMN ".$columna." ".$definicion);
		}
	}
	return true;
}

function normalizarTipoDocumentoLegajoPhp($tipo)
{
	$tipo=strtolower(trim((string)$tipo));
	$tipo=preg_replace('/[^a-z0-9]+/','_',$tipo);
	return trim($tipo,'_');
}

function esDocumentoContratoLegajoPhp($tipo)
{
	$tipo=normalizarTipoDocumentoLegajoPhp($tipo);
	return $tipo=="contrato_laboral" || $tipo=="contrato_firmado";
}

function normalizarFechaContratoLegajoPhp($fecha)
{
	$fecha=trim((string)$fecha);
	if($fecha==""){
		return null;
	}
	if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fecha)){
		throw new Exception("Formato de fecha de contrato no valido.");
	}
	$partes=explode("-",$fecha);
	if(!checkdate((int)$partes[1],(int)$partes[2],(int)$partes[0])){
		throw new Exception("Fecha de contrato no valida.");
	}
	return $fecha;
}

function extensionDocumentoLegajoPermitida($ext)
{
	$ext=strtolower(trim((string)$ext));
	$permitidos=array("pdf","jpg","jpeg","png","webp","doc","docx");
	return in_array($ext,$permitidos,true);
}

function guardarArchivoLegajoUsuario($cod_usuario,$tipo_documento,$archivo,$ext)
{
	$ext=strtolower(trim((string)$ext));
	if(!extensionDocumentoLegajoPermitida($ext)){
		throw new Exception("Formato no permitido.");
	}
	$base64=(string)$archivo;
	$pos=strpos($base64,",");
	if($pos!==false){
		$base64=substr($base64,$pos+1);
	}
	$binario=base64_decode($base64,true);
	if($binario===false || strlen($binario)<20){
		throw new Exception("El archivo enviado no es valido.");
	}
	if(strlen($binario)>15728640){
		throw new Exception("El documento no puede superar 15 MB.");
	}
	$base=realpath(__DIR__."/..");
	if($base===false){
		throw new Exception("No se pudo resolver la ruta base.");
	}
	$directorio=$base.DIRECTORY_SEPARATOR."archivos".DIRECTORY_SEPARATOR."funcionarios".DIRECTORY_SEPARATOR."legajo".DIRECTORY_SEPARATOR.(int)$cod_usuario;
	if(!is_dir($directorio) && !mkdir($directorio,0775,true)){
		throw new Exception("No se pudo crear la carpeta del legajo.");
	}
	$tipoSeguro=normalizarTipoDocumentoLegajoPhp($tipo_documento);
	$nombre=$tipoSeguro."_".date("Ymd_His")."_".mt_rand(1000,9999).".".$ext;
	$destino=$directorio.DIRECTORY_SEPARATOR.$nombre;
	if(file_put_contents($destino,$binario)===false){
		throw new Exception("No se pudo guardar el documento.");
	}
	return "/GoodVentaAsisCap/archivos/funcionarios/legajo/".(int)$cod_usuario."/".$nombre;
}

function registrarEventoLegajoUsuario($mysqli,$cod_usuario,$campo,$valor_nuevo,$cod_usuario_modifico,$origen)
{
	if(!tablaUsuarioExiste($mysqli,"usuario_historial_cambios")){
		return;
	}
	$valor_anterior="";
	$estado="Registrado";
	$sql="INSERT INTO usuario_historial_cambios
		(cod_usuarioFK,campo,valor_anterior,valor_nuevo,fecha_hora,cod_usuario_modifico,origen,estado)
		VALUES (?,?,?,?,NOW(),?,?,?)";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){return;}
	$s='sssssss';
	$stmt->bind_param($s,$cod_usuario,$campo,$valor_anterior,$valor_nuevo,$cod_usuario_modifico,$origen,$estado);
	$stmt->execute();
	$stmt->close();
}

function buscarDocumentosLegajoUsuario($cod_usuario)
{
	$mysqli=conectar_al_servidor();
	if(!asegurarTablaUsuarioDocumentos($mysqli)){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => "No se pudo preparar la tabla de documentos."));
		exit;
	}
	$sql="SELECT id,cod_usuarioFK,tipo_documento,nombre_archivo,url_archivo,estado,fecha_carga,cod_usuarioFK_carga,observacion,fecha_inicio_contrato,fecha_fin_contrato,contrato_sin_vencimiento
		FROM usuario_documentos
		WHERE cod_usuarioFK=? AND IFNULL(estado,'')<>'Inactivo'
		ORDER BY fecha_carga DESC,id DESC";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => "No se pudo buscar documentos."));
		exit;
	}
	$s='s';
	$stmt->bind_param($s,$cod_usuario);
	if(!$stmt->execute()){
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => "No se pudo buscar documentos."));
		exit;
	}
	$result=$stmt->get_result();
	$registros=array();
	while($row=$result->fetch_assoc()){
		$registro=array();
		foreach($row as $key=>$value){
			$registro[$key]=mb_convert_encoding((string)$value,'UTF-8','ISO-8859-1');
		}
		$registros[]=$registro;
	}
	$stmt->close();
	mysqli_close($mysqli);
	echo json_encode(array("1" => "exito", "2" => $registros));
	exit;
}

function guardarDocumentoLegajoUsuario($cod_usuario,$tipo_documento,$nombre_documento,$nombre_archivo,$archivo,$ext,$estado,$observacion,$user,$origen="Legajo RRHH",$fecha_inicio_contrato="",$fecha_fin_contrato="",$contrato_sin_vencimiento="0")
{
	$mysqli=conectar_al_servidor();
	try{
		if(!asegurarTablaUsuarioDocumentos($mysqli)){
			throw new Exception("No se pudo preparar la tabla de documentos.");
		}
		if(trim((string)$cod_usuario)=="" || trim((string)$tipo_documento)==""){
			throw new Exception("Faltan datos del funcionario o documento.");
		}
		$fechaInicioContrato=normalizarFechaContratoLegajoPhp($fecha_inicio_contrato);
		$fechaFinContrato=normalizarFechaContratoLegajoPhp($fecha_fin_contrato);
		$sinVencimiento=(trim((string)$contrato_sin_vencimiento)=="1" || strtolower(trim((string)$contrato_sin_vencimiento))=="true") ? 1 : 0;
		if(!esDocumentoContratoLegajoPhp($tipo_documento)){
			$fechaInicioContrato=null;
			$fechaFinContrato=null;
			$sinVencimiento=0;
		}else{
			if($fechaInicioContrato===null){
				throw new Exception("Carga la fecha de inicio del contrato.");
			}
			if($sinVencimiento==0 && $fechaFinContrato===null){
				throw new Exception("Carga la fecha de vencimiento del contrato o marca sin vencimiento.");
			}
			if($fechaInicioContrato!==null && $fechaFinContrato!==null && strtotime($fechaFinContrato)<strtotime($fechaInicioContrato)){
				throw new Exception("La fecha de vencimiento no puede ser anterior al inicio.");
			}
			if($sinVencimiento==1){
				$fechaFinContrato=null;
			}
		}
		$ruta=guardarArchivoLegajoUsuario($cod_usuario,$tipo_documento,$archivo,$ext);
		$nombreFinal=trim((string)$nombre_archivo)!="" ? $nombre_archivo : $nombre_documento.".".$ext;
		$estadoFinal=trim((string)$estado)!="" ? $estado : "En revision";
		$sql="INSERT INTO usuario_documentos
			(cod_usuarioFK,tipo_documento,nombre_archivo,url_archivo,estado,fecha_carga,cod_usuarioFK_carga,observacion,fecha_inicio_contrato,fecha_fin_contrato,contrato_sin_vencimiento)
			VALUES (?,?,?,?,?,NOW(),?,?,?,?,?)";
		$stmt=$mysqli->prepare($sql);
		if(!$stmt){
			throw new Exception("No se pudo preparar el guardado.");
		}
		$s='sssssssssi';
		$stmt->bind_param($s,$cod_usuario,$tipo_documento,$nombreFinal,$ruta,$estadoFinal,$user,$observacion,$fechaInicioContrato,$fechaFinContrato,$sinVencimiento);
		if(!$stmt->execute()){
			$stmt->close();
			throw new Exception("No se pudo guardar el documento.");
		}
		$id=$stmt->insert_id;
		$stmt->close();
		$detalleEvento=$estadoFinal;
		if(esDocumentoContratoLegajoPhp($tipo_documento)){
			$detalleEvento.=" | Inicio: ".($fechaInicioContrato!==null ? $fechaInicioContrato : "-")." | ".($sinVencimiento==1 ? "Sin vencimiento" : "Vence: ".($fechaFinContrato!==null ? $fechaFinContrato : "-"));
		}
		registrarEventoLegajoUsuario($mysqli,$cod_usuario,"Documento legajo: ".$nombre_documento,$detalleEvento,$user,$origen);
		mysqli_close($mysqli);
		echo json_encode(array("1" => "exito", "2" => $id, "3" => $ruta));
		exit;
	}catch(Exception $e){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => $e->getMessage()));
		exit;
	}
}

function actualizarEstadoDocumentoLegajoUsuario($id_documento,$cod_usuario,$estado,$user)
{
	$mysqli=conectar_al_servidor();
	if(!asegurarTablaUsuarioDocumentos($mysqli)){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => "No se pudo preparar la tabla de documentos."));
		exit;
	}
	$sql="UPDATE usuario_documentos SET estado=? WHERE id=? AND cod_usuarioFK=? LIMIT 1";
	$stmt=$mysqli->prepare($sql);
	if(!$stmt){
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => "No se pudo actualizar el documento."));
		exit;
	}
	$s='sss';
	$stmt->bind_param($s,$estado,$id_documento,$cod_usuario);
	if(!$stmt->execute()){
		$stmt->close();
		mysqli_close($mysqli);
		echo json_encode(array("1" => "error", "2" => "No se pudo actualizar el documento."));
		exit;
	}
	$stmt->close();
	registrarEventoLegajoUsuario($mysqli,$cod_usuario,"Validacion documento legajo",$estado,$user,"Legajo RRHH");
	mysqli_close($mysqli);
	echo json_encode(array("1" => "exito"));
	exit;
}

function buscarfuncionario($buscar,$tipo)
{
$mysqli=conectar_al_servidor();
if($tipo=="1"){

$sql= "select pr.cod_persona as cod ,pr.nombre_persona as nombre
 from  persona pr inner join  cobrador us on us.cod_cobrador=pr.cod_persona 
 where pr.nombre_persona like ?  and us.estado='Activo' ";
	
}
if($tipo=="2"){

$sql= "select pr.idvendedor as cod,pr.nombre as nombre
 from  vendedor pr  
 where pr.nombre like ?  and pr.estado='Activo' ";
	
}
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



$cod = mb_convert_encoding((string)($valor['cod']), 'UTF-8', 'ISO-8859-1');
$nombre = mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');          

$styleName=CargarStyleTable($styleName);
 $pagina.="
<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
<tr id='tbSelecRegistro' onclick='obtenerdatosvistafuncionario(this)'>
<td id='td_id' style='width:5%; background-color: #efeded;color:red'>".$cod."</td>
<td  id='td_datos_1' style='width:90%'>".$nombre."</td>
</tr>
</table>";


}
}

$informacion =array("1" => "exito","2" => $pagina,"3" => $nroRegistro);
echo json_encode($informacion);	
exit;
}

function generarKEYS($cod_nivelesFk,$usuarios_idusario,$tipo)
{
	$mysqli=conectar_al_servidor();
	 $pagina='';
	
		$sql= "Select dts.iddetallesniveles,lta.nro,lta.formulario,lta.codigo,lta.nombre,dts.accion ,lta.idlistadodeacceso
        from listado_niveles lts inner join detallesniveles dts on dts.cod_nivelesfk=lts.cod_niveles 
		inner join listadodeacceso lta on lta.idlistadodeacceso=dts.idlistadodeacceso
		where cod_nivelesfk='".$cod_nivelesFk."' order by lta.nro asc";
		 
   $stmt = $mysqli->prepare($sql);
  if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
$controltitulo="";
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalresouesta= $valor;
 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		  
		     $iddetallesniveles=$valor['iddetallesniveles'];
		  	  $nro=mb_convert_encoding((string)($valor['nro']), 'UTF-8', 'ISO-8859-1');
		  	  $formulario=mb_convert_encoding((string)($valor['formulario']), 'UTF-8', 'ISO-8859-1');
		  	  $codigo=mb_convert_encoding((string)($valor['codigo']), 'UTF-8', 'ISO-8859-1');
		  	  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
		  	  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
		  	  $idlistadodeacceso=mb_convert_encoding((string)($valor['idlistadodeacceso']), 'UTF-8', 'ISO-8859-1');		  	 
			  generarAccesos($idlistadodeacceso,$accion,$usuarios_idusario,$tipo);
			    	 
		  	
			  
			  
	  }
	  
 }
 
  mysqli_close($mysqli); 


}



function generarAccesos($idlistadodeaccesoFK,$accion,$usuarios_idusario,$tipo){

	$mysqli=conectar_al_servidor();
	$consulta="INSERT INTO accesosuser (idlistadodeaccesoFK,tipo,usuarios_idusario,accion) VALUES ('$idlistadodeaccesoFK','$tipo','$usuarios_idusario','$accion')";	

	$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}

	 mysqli_close($mysqli); 
	
}

function EliminarAccesos($usuarios_idusario){
	
	
	
	$mysqli=conectar_al_servidor();
	$consulta="Delete from accesosuser where usuarios_idusario='$usuarios_idusario' ";	
	$stmt = $mysqli->prepare($consulta);
if ( ! $stmt->execute()) {
   echo "Error";
   exit;
}
	
	 mysqli_close($mysqli); 
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
	$operacion = $_POST['funt'];
	$operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');

	ObtenerDatos($operacion);
}

?>

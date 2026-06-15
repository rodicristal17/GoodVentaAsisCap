<?php
require_once("conexion.php");
require_once("solicitud_eliminado_helper.php");
include_once("verificar_navegador.php");
include_once("subir_foto_base64.php");
include_once("buscar_nivel.php");
include_once("classTable.php");

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

$horarios_usuario = obtenerHorariosUsuarioPost();

abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$foto,$ext,$telefono_referencia,$direccion,$tipo_relacion,$fecha_creacion,$horarios_usuario,$user,$operacion);
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

function abmHorarioUsuario($mysqli,$cod_usuario,$horarios_usuario,$cod_usuario_accion,$cod_localFK)
{
	asegurarEstructuraHorarioUsuarioEsperado($mysqli);

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
		echo trigger_error('The query preparation failed; MySQL said ('.$mysqli->errno.') '.$mysqli->error, E_USER_ERROR);
		exit;
	}

	$ss='ss';
	$stmtInactivar->bind_param($ss,$cod_usuario_accion,$cod_usuario);

	if (!$stmtInactivar->execute()) {
		echo trigger_error('The query execution failed; MySQL said ('.$stmtInactivar->errno.') '.$stmtInactivar->error, E_USER_ERROR);
		exit;
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
		echo trigger_error('The query preparation failed; MySQL said ('.$mysqli->errno.') '.$mysqli->error, E_USER_ERROR);
		exit;
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
			echo trigger_error('The query execution failed; MySQL said ('.$stmtInsert->errno.') '.$stmtInsert->error, E_USER_ERROR);
			exit;
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

function abm($tipo,$cod_persona,$nombre_persona,$telefono,$rut_usuario,$cod_usuario,$login,$password,$estado,$acceso,$cod_localFK,$foto,$ext,$telefono_referencia,$direccion,$tipo_relacion,$fecha_creacion,$horarios_usuario,$cod_usuario_accion,$operacion)
{



if($nombre_persona==""  || $rut_usuario==""  || $login=="" || ($operacion=="nuevo" && $password=="")){
$informacion =array("1" => "CAMPOSVACIOS");
echo json_encode($informacion);	
exit;
}

$mysqli=conectar_al_servidor(); 
$datosAnterioresAuditoria=array();
if($operacion=="editar"){
	$datosAnterioresAuditoria=obtenerDatosUsuarioAuditoria($mysqli,$cod_usuario);
	if($password==""){
		$password=isset($datosAnterioresAuditoria["password"]) ? $datosAnterioresAuditoria["password"] : "";
	}
}

$consulta= "Select count(*) from usuario where login=? and password=? and cod_localFK=?  and Cod_Usuario!=?";
	
	
		$stmt = $mysqli->prepare($consulta);
$ss='ssss';
$stmt->bind_param($ss,$login,$password,$cod_localFK,$cod_usuario);


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

if($operacion=="nuevo") 
{


$consulta1="Insert into persona (nombre_persona,telefono,telefono_referencia,direccion,tipo_relacion)
values(?,?,?,?,?)";
$stmt1 = $mysqli->prepare($consulta1);
$ss='sssss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion);

$consulta2="Insert into usuario (rut_usuario,login,cod_usuario,password,estado,acceso,cod_localFK,tipo,fecha_creacion)
values(?,?,(select cod_persona from persona order by cod_persona desc limit 1),?,?,?,?,?, NOW())";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssss';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo);

$con=rand(5, 1500);

$consulta3="Insert into cobrador (idzona,usu,cod_cobrador,con,estado)
values('1',?,(select cod_persona from persona order by cod_persona desc limit 1),?,'Activo')";
$stmt3 = $mysqli->prepare($consulta3);
$ss='ss';
$stmt3->bind_param($ss,$login,$con);


$consulta4="Insert into cobradorusuario (cod_usuarioFk,cod_cobradorFk)
values((select cod_persona from persona order by cod_persona desc limit 1),(select cod_persona from persona order by cod_persona desc limit 1))";
$stmt4 = $mysqli->prepare($consulta4);

}


if($operacion=="editar")
{

$consulta1="Update persona set nombre_persona=?,telefono=?, telefono_referencia=?, direccion=?, tipo_relacion=? where cod_persona=?";	
$stmt1 = $mysqli->prepare($consulta1);
$ss='ssssss';
$stmt1->bind_param($ss,$nombre_persona,$telefono,$telefono_referencia,$direccion,$tipo_relacion,$cod_persona);

$consulta2="update usuario set rut_usuario=?,login=?,password=?,estado=?,acceso=?,cod_localFK=?,tipo=?,fecha_creacion=? where cod_usuario=? ";
$stmt2 = $mysqli->prepare($consulta2);
$ss='sssssissi';
$stmt2->bind_param($ss,$rut_usuario,$login,$password,$estado,$acceso,$cod_localFK,$tipo,$fecha_creacion,$cod_usuario);

}



if (!$stmt1->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt1->errno.') '.$stmt1->error, E_USER_ERROR);
exit;

}


if (!$stmt2->execute()) {
	
echo trigger_error('The query execution failed; MySQL said ('.$stmt2->errno.') '.$stmt2->error, E_USER_ERROR);
exit;

}

// Recupera la id del usuario de la ultima insercion
$cod_usuario= empty($cod_usuario) ?  : $cod_usuario;

if($operacion=="nuevo") 
{
	
if (!$stmt3->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt3->errno.') '.$stmt3->error, E_USER_ERROR);
exit;
}
if (!$stmt4->execute()) {
echo trigger_error('The query execution failed; MySQL said ('.$stmt4->errno.') '.$stmt4->error, E_USER_ERROR);
exit;
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
EliminarAccesos($cod_usuario);
generarKEYS($acceso,$cod_usuario,'Administrativo');
}else{
abmHorarioUsuario($mysqli,$cod_usuario,$horarios_usuario,$cod_usuario_accion,$cod_localFK);
EliminarAccesos($cod_usuario);
generarKEYS($acceso,$cod_usuario,'Administrativo');
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

function BuscarRegistro($codigo,$documento,$usuario,$estado,$local)
{
$mysqli=conectar_al_servidor();

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

$horarios_usuario_json = buscarHorariosUsuario($mysqli,$cod_usuario);
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
	'horarios_usuario' => json_decode($horarios_usuario_json, true),
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

function obtenerDatosUsuarioAuditoria($mysqli,$cod_usuario)
{
	$datos=array();
	$sql="SELECT pr.nombre_persona,pr.telefono,pr.telefono_referencia,pr.direccion,pr.tipo_relacion,
		us.rut_usuario,us.login,us.password,us.estado,us.acceso,us.cod_localFK,us.tipo,us.fecha_creacion
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

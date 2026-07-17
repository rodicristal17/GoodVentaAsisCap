<?php
$funt = isset($_POST['funt']) ? $_POST['funt'] : '';
//cargar achivos importantes
require("conexion.php");
include("verificar_navegador.php");
include("buscar_nivel.php");
include("classTable.php");

// Cuenta de recuperacion confirmada para Clinident. La comprobacion no se basa
// solamente en el id: tambien valida login, identidad, estado y rol para evitar
// que una restauracion de base de datos entregue privilegios a otra persona.
define('CLINIDENT_SUPERADMIN_PROTEGIDO_ID', 5994);

function responderAccesos($codigo, $datos = array())
{
	$informacion = array("1" => $codigo);
	foreach ($datos as $clave => $valor) {
		$informacion[$clave] = $valor;
	}
	echo json_encode($informacion);
	exit;
}

function esSuperAdministradorProtegido($usuario, $mysqli = null)
{
	$usuario = trim((string)$usuario);
	if ($usuario !== (string)CLINIDENT_SUPERADMIN_PROTEGIDO_ID) {
		return false;
	}

	$conexionPropia = $mysqli === null;
	if ($conexionPropia) {
		$mysqli = conectar_al_servidor();
	}
	$sql = "SELECT 1
		FROM usuario u
		INNER JOIN persona p ON p.cod_persona = u.cod_usuario
		WHERE u.cod_usuario = ?
			AND LOWER(TRIM(IFNULL(u.login,''))) = 'cf'
			AND UPPER(TRIM(IFNULL(u.tipo,''))) = 'ADMINISTRATIVO'
			AND UPPER(TRIM(IFNULL(u.estado,''))) = 'ACTIVO'
			AND UPPER(TRIM(IFNULL(p.nombre_persona,''))) LIKE 'CARLOS FARAONE CLINIDENT%'
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	$protegido = false;
	if ($stmt) {
		$stmt->bind_param('i', $usuario);
		if ($stmt->execute()) {
			$resultado = $stmt->get_result();
			$protegido = $resultado && mysqli_num_rows($resultado) === 1;
		}
		$stmt->close();
	}
	if ($conexionPropia) {
		mysqli_close($mysqli);
	}
	return $protegido;
}

function recuperarPermisosSuperAdministradorProtegido($usuario)
{
	$mysqli = conectar_al_servidor();
	if (!esSuperAdministradorProtegido($usuario, $mysqli)) {
		mysqli_close($mysqli);
		return false;
	}

	$stmt = $mysqli->prepare("UPDATE accesosuser
		SET accion = 'SI'
		WHERE usuarios_idusario = ?
			AND tipo = 'Administrativo'
			AND UPPER(TRIM(IFNULL(accion,''))) <> 'SI'");
	if (!$stmt) {
		mysqli_close($mysqli);
		return false;
	}
	$stmt->bind_param('i', $usuario);
	$ok = $stmt->execute();
	$afectados = $ok ? intval($stmt->affected_rows) : 0;
	$stmt->close();
	mysqli_close($mysqli);
	return $ok ? $afectados : false;
}

function obtenerCodigoPermisoAcceso($mysqli, $idAcceso, $usuarioObjetivo)
{
	$sql = "SELECT UPPER(TRIM(IFNULL(lta.codigo,''))) AS codigo
		FROM accesosuser acus
		INNER JOIN listadodeacceso lta ON lta.idlistadodeacceso = acus.idlistadodeaccesoFK
		WHERE acus.idaccesosUser = ?
			AND acus.usuarios_idusario = ?
			AND acus.tipo = 'Administrativo'
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return null;
	}
	$stmt->bind_param('ii', $idAcceso, $usuarioObjetivo);
	if (!$stmt->execute()) {
		$stmt->close();
		return null;
	}
	$resultado = $stmt->get_result();
	$fila = $resultado ? $resultado->fetch_assoc() : null;
	$stmt->close();
	return $fila ? $fila['codigo'] : null;
}

function usuarioPuedeAdministrarAccesos($usuario)
{
	if (esSuperAdministradorProtegido($usuario)) {
		return true;
	}
	// Compatibilidad con el administrador principal, que ya posee este acceso
	// implicito en el cliente legacy.
	if ((string)$usuario === '2') {
		return true;
	}

	$mysqli = conectar_al_servidor();
	$sql = "SELECT 1
		FROM accesosuser acus
		INNER JOIN listadodeacceso lta ON lta.idlistadodeacceso = acus.idlistadodeaccesoFK
		WHERE acus.usuarios_idusario = ?
			AND UPPER(TRIM(IFNULL(lta.codigo,''))) = 'VERACCESOSUARIOS'
			AND UPPER(TRIM(IFNULL(acus.accion,''))) = 'SI'
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		mysqli_close($mysqli);
		return false;
	}
	$stmt->bind_param('s', $usuario);
	if (!$stmt->execute()) {
		$stmt->close();
		mysqli_close($mysqli);
		return false;
	}
	$result = $stmt->get_result();
	$permitido = $result && mysqli_num_rows($result) > 0;
	$stmt->close();
	mysqli_close($mysqli);
	return $permitido;
}

function verificar($funt)
{
	
	
	$user=isset($_POST['useru']) ? $_POST['useru'] : '';
$user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
	$pass=isset($_POST['passu']) ? $_POST['passu'] : '';
	
	  $pass = str_replace("=","+",$pass);
$navegador=isset($_POST['navegador']) ? $_POST['navegador'] : '';
$navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
$resp=verificar_navegador($user,$navegador,$pass);
if($resp!="ok"){
	responderAccesos("UI");
}

	// Recuperacion idempotente: solo puede ejecutarse despues de autenticar la
	// sesion y solo para la identidad protegida confirmada arriba.
	$esSuperAdministradorProtegido = esSuperAdministradorProtegido($user);
	$recuperacionSuperAdministradorResultado = 0;
	if ($esSuperAdministradorProtegido) {
		$recuperacionSuperAdministradorResultado = recuperarPermisosSuperAdministradorProtegido($user);
	}
	if ($funt === "recuperarSuperAdministrador") {
		if (!$esSuperAdministradorProtegido) {
			responderAccesos("NI");
		}
		if ($recuperacionSuperAdministradorResultado === false) {
			responderAccesos("Error");
		}
		responderAccesos("exito", array(
			"superadmin_protegido" => 1,
			"permisos_recuperados" => intval($recuperacionSuperAdministradorResultado)
		));
	}

	if (!usuarioPuedeAdministrarAccesos($user)) {
		responderAccesos("NI");
	}
	
if($funt=="editar")
{

$acciones=isset($_POST['acciones']) ? $_POST['acciones'] : '';
$acciones = mb_convert_encoding((string)($acciones), 'ISO-8859-1', 'UTF-8');
$idAbmUsuario=isset($_POST['idAbmUsuario']) ? $_POST['idAbmUsuario'] : '';
$idAbmUsuario = mb_convert_encoding((string)($idAbmUsuario), 'ISO-8859-1', 'UTF-8');
$idabm=isset($_POST['idabm']) ? $_POST['idabm'] : '';
$idabm = mb_convert_encoding((string)($idabm), 'ISO-8859-1', 'UTF-8');
abm($acciones,$idabm,$funt,$idAbmUsuario,$user);
}

if($funt=="buscar")
{
$buscador=isset($_POST['buscador']) ? $_POST['buscador'] : '';
$buscador = mb_convert_encoding((string)($buscador), 'ISO-8859-1', 'UTF-8');
$buscar=isset($_POST['buscar']) ? $_POST['buscar'] : '';
$buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
buscar($buscar,$buscador,$user);
}

responderAccesos("DI");

}

function abm($acciones,$idabm,$funt,$user,$usuarioSesion)
{
	$acciones = strtoupper(trim((string)$acciones));
	$idabm = trim((string)$idabm);
	$user = trim((string)$user);
	if ($idabm === "" || $user === "" || !ctype_digit($idabm) || !ctype_digit($user)
		|| ($acciones !== "SI" && $acciones !== "NO")) {
		responderAccesos("DI");
	}

	$mysqli=conectar_al_servidor();
	$codigoPermiso = obtenerCodigoPermisoAcceso($mysqli, intval($idabm), intval($user));
	if ($codigoPermiso === null) {
		mysqli_close($mysqli);
		responderAccesos("NI");
	}
	if ($acciones === "NO" && esSuperAdministradorProtegido($user, $mysqli)) {
		mysqli_close($mysqli);
		responderAccesos("PROTEGIDO", array("2" => "La cuenta superadministradora no admite restricciones."));
	}
	if ($acciones === "NO"
		&& (string)$user === (string)$usuarioSesion
		&& $codigoPermiso === "VERACCESOSUARIOS") {
		mysqli_close($mysqli);
		responderAccesos("PROTEGIDO", array("2" => "Este permiso no puede quitarse desde la misma cuenta administradora."));
	}

	if($funt=="editar")
	{
		$consulta="UPDATE accesosuser
			SET accion = ?
			WHERE idaccesosUser = ?
				AND usuarios_idusario = ?
				AND tipo = 'Administrativo'";
		$stmt = $mysqli->prepare($consulta);
		if (!$stmt) {
			mysqli_close($mysqli);
			responderAccesos("Error");
		}
		$stmt->bind_param('sss',$acciones,$idabm,$user);

	}
	
	if (!$stmt->execute()) {
		$stmt->close();
		mysqli_close($mysqli);
		responderAccesos("Error");
	}
	$afectados = $stmt->affected_rows;
	$stmt->close();

	// affected_rows puede ser cero si el valor ya era el solicitado. En ese
	// caso se comprueba la pertenencia para conservar un guardado idempotente.
	if ($afectados === 0) {
		$consultaControl = "SELECT 1 FROM accesosuser
			WHERE idaccesosUser = ? AND usuarios_idusario = ? AND tipo = 'Administrativo'
			LIMIT 1";
		$stmtControl = $mysqli->prepare($consultaControl);
		if (!$stmtControl) {
			mysqli_close($mysqli);
			responderAccesos("Error");
		}
		$stmtControl->bind_param('ss', $idabm, $user);
		if (!$stmtControl->execute()) {
			$stmtControl->close();
			mysqli_close($mysqli);
			responderAccesos("Error");
		}
		$resultControl = $stmtControl->get_result();
		$pertenece = $resultControl && mysqli_num_rows($resultControl) > 0;
		$stmtControl->close();
		if (!$pertenece) {
			mysqli_close($mysqli);
			responderAccesos("NI");
		}
	}
	mysqli_close($mysqli);
	$porcentaje=ObtenerPorcentaje($user);
	responderAccesos("exito", array("2" => $porcentaje));


	
	
	
	
}

function ObtenerPorcentaje($buscar)
{
	$mysqli=conectar_al_servidor();
	$sql= "Select lta.nro,lta.formulario,lta.codigo,lta.nombre,acus.idaccesosUser,acus.accion,acus.usuarios_idusario,lta.formulario
	from accesosuser acus inner join listadodeacceso lta on lta.idlistadodeacceso=acus.idlistadodeaccesoFK
	where usuarios_idusario = ? and acus.tipo='Administrativo' order by lta.nro asc,lta.orden asc";
		
   $nrodeactivos=0;
   $totalactivos=0;
 
   
   $stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		mysqli_close($mysqli);
		responderAccesos("Error");
	}
  	$s='s';

$stmt->bind_param($s,$buscar);

if ( ! $stmt->execute()) {
	$stmt->close();
	mysqli_close($mysqli);
	responderAccesos("Error");
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalactivos=$valor;
 $controltitulo="";
 

 if ($valor>0)
 {
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
		   if($accion=="SI"){
			  $nrodeactivos=$nrodeactivos+1;	
          	
			 }
          		
			 
			    	 		  
	  }
 }
  $stmt->close();
  mysqli_close($mysqli);
 
  if ($totalactivos > 0) {
	$nrodeactivos=($nrodeactivos*100)/$totalactivos;
  } else {
	$nrodeactivos=0;
  }
  return number_format($nrodeactivos,'0',',','.');


}
function buscar($buscar,$buscador,$usuarioSesion)
{
	$buscar = trim((string)$buscar);
	$buscador = trim((string)$buscador);
	if ($buscar === '' || !ctype_digit($buscar)) {
		responderAccesos("DI");
	}
	$mysqli=conectar_al_servidor();
	$objetivoProtegido=esSuperAdministradorProtegido($buscar, $mysqli);
	if ($objetivoProtegido) {
		recuperarPermisosSuperAdministradorProtegido($buscar);
	}
	 $pagina1='';
	 $pagina2='';
	 $pagina3='';
		$sql= "Select lta.nro,lta.formulario,lta.codigo,lta.nombre,acus.idaccesosUser,acus.accion,acus.usuarios_idusario,lta.formulario
		from accesosuser acus inner join listadodeacceso lta on lta.idlistadodeacceso=acus.idlistadodeaccesoFK
		where usuarios_idusario = ? and acus.tipo='Administrativo'
			and concat_ws(' ',ifnull(lta.nombre,''),ifnull(lta.formulario,''),ifnull(lta.codigo,'')) like ?
		order by lta.nro asc,lta.orden asc";
		
   $nrodeactivos=0;
   $totalactivos=0;
 
   
   $stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		mysqli_close($mysqli);
		responderAccesos("Error");
	}
	$busquedaLike = "%".$buscador."%";
	$stmt->bind_param('ss',$buscar,$busquedaLike);

if ( ! $stmt->execute()) {
	$stmt->close();
	mysqli_close($mysqli);
	responderAccesos("Error");
}
 
	$result = $stmt->get_result();
 $valor= mysqli_num_rows($result);
 $totalactivos=$valor;
 $controltitulo="";
$styleName="tableRegistroSearch";

 if ($valor>0)
 {
	  $pagina1.="<table class='accesos-list-table'>
	  <tbody>";
	  while ($valor= mysqli_fetch_assoc($result))
	  {
		  
		  
		      $idaccesosUser=$valor['idaccesosUser'];
			  $accion=mb_convert_encoding((string)($valor['accion']), 'UTF-8', 'ISO-8859-1');
			  $usuarios_idusario=mb_convert_encoding((string)($valor['usuarios_idusario']), 'UTF-8', 'ISO-8859-1');
			  $nombre=mb_convert_encoding((string)($valor['nombre']), 'UTF-8', 'ISO-8859-1');
			  $codigo=mb_convert_encoding((string)($valor['codigo']), 'UTF-8', 'ISO-8859-1');
			  $formulario=mb_convert_encoding((string)($valor['formulario']), 'UTF-8', 'ISO-8859-1');
			  $nombreSeguro=htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
			  $codigoSeguro=htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');
			  $formularioSeguro=htmlspecialchars($formulario, ENT_QUOTES, 'UTF-8');
			  $habilitado=strtoupper(trim($accion))==="SI";
			  $estadoFila=$habilitado ? "is-enabled" : "is-disabled";
			  $checked=$habilitado ? " checked" : "";
			  $estadoGuardado=$habilitado ? "SI" : "NO";
			  $textoEstado=$habilitado ? "Habilitado" : "Bloqueado";
			  $codigoHtml=$codigoSeguro!="" ? "<span class='accesos-item-code'>Codigo: ".$codigoSeguro."</span>" : "";
			  $tituloacceso="";
			 if($controltitulo!=$formulario){
				   $tituloacceso="<tr class='accesos-group'>
				   <th colspan='2'>".$formularioSeguro."</th>
				   </tr>";
				   $controltitulo=$formulario;
			 }
				  $esPermisoCriticoPropio=((string)$buscar === (string)$usuarioSesion && strtoupper(trim($codigo)) === "VERACCESOSUARIOS");
				  $bloqueoPermanente=$objetivoProtegido || ($esPermisoCriticoPropio && $habilitado);
				  $atributosProteccion=$bloqueoPermanente ? " disabled data-acceso-protegido='1' title='Permiso protegido contra auto-revocacion'" : "";
				  $textoEstado=$objetivoProtegido ? "Protegido" : $textoEstado;
				  $inputcheck="<label class='accesos-switch'>
				 <input id='".$idaccesosUser."' type='checkbox'".$checked.$atributosProteccion." data-acceso-codigo='".$codigoSeguro."' data-estado-guardado='".$estadoGuardado."' aria-label='Cambiar permiso ".$nombreSeguro."' onclick='abmacceso(this)' />
			 <span class='accesos-switch-track'></span>
			 <span class='accesos-switch-text' aria-live='polite'>".$textoEstado."</span>
			 </label>";
			 if($habilitado){
            $nrodeactivos=$nrodeactivos+1;			
			 }
			    	 
$styleName=CargarStyleTable($styleName);		  	  
$pagina1.=$tituloacceso."
<tr id='tbSelecRegistro' class='accesos-item-row ".$estadoFila."'>
<td id='td_datos_7' class='accesos-item-info'>
<span class='accesos-item-title'>".$nombreSeguro."</span>
<span class='accesos-item-meta'>".$codigoHtml."</span>
</td>
<td id='td_datos_2' class='accesos-item-action'>".$inputcheck."</td>
</tr>";
	 
			  
	  }
	  $pagina1.="</tbody></table>";
 }
 else
 {
	  $pagina1="<div class='accesos-empty'>No se encontraron permisos con ese criterio de busqueda.</div>";
 }
  $stmt->close();
  mysqli_close($mysqli);
 
  if($totalactivos>0){
  $nrodeactivos=($nrodeactivos*100)/$totalactivos;
  }else{
  $nrodeactivos=0;
  }
 
  $informacion =array("1" => 'exito',"2" => $pagina1,"3"=>number_format($nrodeactivos,'0',',','.'));
echo json_encode($informacion);	
exit;


}


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
	verificar($funt);
}
?>

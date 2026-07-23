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

function gestorAccesosUtf8($valor)
{
	return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function gestorAccesosTablaExiste($mysqli, $tabla)
{
	$sql = "SELECT COUNT(*) FROM information_schema.tables
		WHERE table_schema = DATABASE() AND table_name = ?";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('s', $tabla);
	if (!$stmt->execute()) {
		$stmt->close();
		return false;
	}
	$total = 0;
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	return intval($total) > 0;
}

function gestorAccesosIconoFormulario($formulario)
{
	$texto = strtoupper(gestorAccesosUtf8($formulario));
	$mapa = array(
		'LABORATORIO' => '/GoodVentaAsisCap/iconos/telar-loader.svg?v=20260721-2',
		'USUARIO' => '/GoodVentaAsisCap/iconos/usuariosacceso.png',
		'ACCESO' => '/GoodVentaAsisCap/iconos/acceso.png',
		'NIVELES' => '/GoodVentaAsisCap/iconos/nivelesacceso.png',
		'CALENDARIO' => '/GoodVentaAsisCap/iconos/calendarioAgenda.png',
		'AGENDA' => '/GoodVentaAsisCap/iconos/agenda.png',
		'CONSULTORIO' => '/GoodVentaAsisCap/iconos/consultamedica.png',
		'HISTORIAL' => '/GoodVentaAsisCap/iconos/historialmedico.png',
		'VENTA' => '/GoodVentaAsisCap/iconos/venta.png',
		'COMPRA' => '/GoodVentaAsisCap/iconos/compra.png',
		'PRODUCTO' => '/GoodVentaAsisCap/iconos/productos.png',
		'INVENTARIO' => '/GoodVentaAsisCap/iconos/inventario.png',
		'INSUMO' => '/GoodVentaAsisCap/iconos/inventario.png',
		'CAJA' => '/GoodVentaAsisCap/iconos/caja.png',
		'CUENTA' => '/GoodVentaAsisCap/iconos/informedecaja.png',
		'INFORME' => '/GoodVentaAsisCap/iconos/etiquetadocumento.png',
		'TAREA' => '/GoodVentaAsisCap/iconos/lista-de-tareas.png'
	);
	foreach ($mapa as $palabra => $icono) {
		if (strpos($texto, $palabra) !== false) {
			return $icono;
		}
	}
	return '/GoodVentaAsisCap/iconos/home.png';
}

function gestorAccesosObtenerRol($mysqli, $rolId)
{
	$sql = "SELECT cod_niveles,nombre,estado
		FROM listado_niveles
		WHERE cod_niveles=? AND tipo='Administrativo'
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return null;
	}
	$stmt->bind_param('i', $rolId);
	if (!$stmt->execute()) {
		$stmt->close();
		return null;
	}
	$resultado = $stmt->get_result();
	$fila = $resultado ? $resultado->fetch_assoc() : null;
	$stmt->close();
	return $fila;
}

function gestorAccesosObtenerUsuario($mysqli, $usuarioId)
{
	$sql = "SELECT u.cod_usuario,u.acceso,u.tipo,u.estado,
			IFNULL(p.nombre_persona,u.login) AS nombre_persona
		FROM usuario u
		LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
		WHERE u.cod_usuario=?
		LIMIT 1";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return null;
	}
	$stmt->bind_param('i', $usuarioId);
	if (!$stmt->execute()) {
		$stmt->close();
		return null;
	}
	$resultado = $stmt->get_result();
	$fila = $resultado ? $resultado->fetch_assoc() : null;
	$stmt->close();
	return $fila;
}

function gestorAccesosCatalogoBase($mysqli)
{
	$roles = array();
	$sqlRoles = "SELECT ln.cod_niveles,ln.nombre,ln.estado,
			COUNT(DISTINCT u.cod_usuario) AS usuarios
		FROM listado_niveles ln
		LEFT JOIN usuario u ON u.acceso=CAST(ln.cod_niveles AS CHAR)
		WHERE ln.tipo='Administrativo'
		GROUP BY ln.cod_niveles,ln.nombre,ln.estado
		ORDER BY CASE WHEN UPPER(TRIM(IFNULL(ln.estado,'')))='ACTIVO' THEN 0 ELSE 1 END,ln.nombre";
	$resultRoles = $mysqli->query($sqlRoles);
	if ($resultRoles) {
		while ($fila = $resultRoles->fetch_assoc()) {
			$roles[] = array(
				'id' => intval($fila['cod_niveles']),
				'nombre' => gestorAccesosUtf8($fila['nombre']),
				'estado' => gestorAccesosUtf8($fila['estado']),
				'usuarios' => intval($fila['usuarios'])
			);
		}
	}

	$usuarios = array();
	$tipos = array();
	$sqlUsuarios = "SELECT u.cod_usuario,u.acceso,u.tipo,u.estado,
			IFNULL(p.nombre_persona,u.login) AS nombre_persona,
			IFNULL(ln.nombre,'Sin rol') AS rol_nombre
		FROM usuario u
		LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
		LEFT JOIN listado_niveles ln ON ln.cod_niveles=CAST(u.acceso AS UNSIGNED)
		ORDER BY CASE WHEN UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO' THEN 0 ELSE 1 END,
			IFNULL(p.nombre_persona,u.login)";
	$resultUsuarios = $mysqli->query($sqlUsuarios);
	if ($resultUsuarios) {
		while ($fila = $resultUsuarios->fetch_assoc()) {
			$tipo = trim(gestorAccesosUtf8($fila['tipo']));
			$usuarios[] = array(
				'id' => intval($fila['cod_usuario']),
				'nombre' => gestorAccesosUtf8($fila['nombre_persona']),
				'tipo' => $tipo,
				'estado' => gestorAccesosUtf8($fila['estado']),
				'rol_id' => intval($fila['acceso']),
				'rol_nombre' => gestorAccesosUtf8($fila['rol_nombre'])
			);
			if ($tipo !== '') {
				$tipos[strtoupper($tipo)] = $tipo;
			}
		}
	}
	ksort($tipos);

	return array(
		'roles' => $roles,
		'usuarios' => $usuarios,
		'tipos_usuario' => array_values($tipos)
	);
}

function gestorAccesosPermisos($mysqli, $usuarioId, $rolId)
{
	$sql = "SELECT lta.idlistadodeacceso,lta.nro,lta.formulario,lta.codigo,lta.nombre,lta.orden,
			IF(MAX(CASE WHEN UPPER(TRIM(IFNULL(au.accion,'')))='SI' THEN 1 ELSE 0 END)=1,'SI','NO') AS usuario_accion,
			IF(MAX(CASE WHEN UPPER(TRIM(IFNULL(dn.accion,'')))='SI' THEN 1 ELSE 0 END)=1,'SI','NO') AS rol_accion
		FROM listadodeacceso lta
		LEFT JOIN accesosuser au ON au.idlistadodeaccesoFK=lta.idlistadodeacceso
			AND au.usuarios_idusario=? AND au.tipo='Administrativo'
		LEFT JOIN detallesniveles dn ON dn.idlistadodeacceso=lta.idlistadodeacceso
			AND dn.cod_nivelesfk=?
		WHERE lta.tipo='Administrativo'
		GROUP BY lta.idlistadodeacceso,lta.nro,lta.formulario,lta.codigo,lta.nombre,lta.orden
		ORDER BY lta.nro ASC,CAST(lta.orden AS DECIMAL(10,2)) ASC,lta.idlistadodeacceso ASC";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('ii', $usuarioId, $rolId);
	if (!$stmt->execute()) {
		$stmt->close();
		return false;
	}
	$resultado = $stmt->get_result();
	$grupos = array();
	$ordenGrupos = array();
	while ($fila = $resultado->fetch_assoc()) {
		$formulario = trim(gestorAccesosUtf8($fila['formulario']));
		if ($formulario === '') {
			$formulario = 'Otros accesos';
		}
		$claveGrupo = strtoupper($formulario);
		if (!isset($grupos[$claveGrupo])) {
			$grupos[$claveGrupo] = array(
				'clave' => md5($claveGrupo),
				'nombre' => $formulario,
				'icono' => gestorAccesosIconoFormulario($formulario),
				'permiso_principal_id' => 0,
				'_permiso_principal_codigo' => '',
				'permisos' => array()
			);
			$ordenGrupos[] = $claveGrupo;
		}
		$codigo = trim(gestorAccesosUtf8($fila['codigo']));
		$permiso = array(
			'id' => intval($fila['idlistadodeacceso']),
			'codigo' => $codigo,
			'nombre' => gestorAccesosUtf8($fila['nombre']),
			'usuario' => $fila['usuario_accion'] === 'SI' ? 'SI' : 'NO',
			'rol' => $fila['rol_accion'] === 'SI' ? 'SI' : 'NO'
		);
		$grupos[$claveGrupo]['permisos'][] = $permiso;
		if ($grupos[$claveGrupo]['permiso_principal_id'] === 0
			|| (strpos(strtoupper($codigo), 'VER') === 0
				&& strpos(
					strtoupper($grupos[$claveGrupo]['_permiso_principal_codigo']),
					'VER'
				) !== 0)) {
			$grupos[$claveGrupo]['permiso_principal_id'] = intval($fila['idlistadodeacceso']);
			$grupos[$claveGrupo]['_permiso_principal_codigo'] = $codigo;
		}
	}
	$stmt->close();

	$salida = array();
	foreach ($ordenGrupos as $claveGrupo) {
		unset($grupos[$claveGrupo]['_permiso_principal_codigo']);
		$salida[] = $grupos[$claveGrupo];
	}
	return $salida;
}

function gestorAccesosCargar($usuarioObjetivo, $usuarioSesion, $rolObjetivo = '')
{
	$usuarioObjetivo = trim((string)$usuarioObjetivo);
	if ($usuarioObjetivo === '' || !ctype_digit($usuarioObjetivo)) {
		$usuarioObjetivo = trim((string)$usuarioSesion);
	}
	$mysqli = conectar_al_servidor();
	$usuario = gestorAccesosObtenerUsuario($mysqli, intval($usuarioObjetivo));
	if (!$usuario) {
		mysqli_close($mysqli);
		responderAccesos("DI", array("2" => "El usuario seleccionado no existe."));
	}
	$rolId = intval($usuario['acceso']);
	$rolObjetivo = trim((string)$rolObjetivo);
	if ($rolObjetivo !== '' && ctype_digit($rolObjetivo)) {
		$rolConsultado = gestorAccesosObtenerRol($mysqli, intval($rolObjetivo));
		if ($rolConsultado) {
			$rolId = intval($rolObjetivo);
		}
	}
	$catalogo = gestorAccesosCatalogoBase($mysqli);
	$grupos = gestorAccesosPermisos($mysqli, intval($usuarioObjetivo), $rolId);
	if ($grupos === false) {
		mysqli_close($mysqli);
		responderAccesos("Error");
	}
	$total = 0;
	$habilitados = 0;
	$excepciones = 0;
	foreach ($grupos as $grupo) {
		foreach ($grupo['permisos'] as $permiso) {
			$total++;
			if ($permiso['usuario'] === 'SI') {
				$habilitados++;
			}
			if ($permiso['usuario'] !== $permiso['rol']) {
				$excepciones++;
			}
		}
	}
	$catalogo['seleccion'] = array(
		'usuario_id' => intval($usuario['cod_usuario']),
		'nombre' => gestorAccesosUtf8($usuario['nombre_persona']),
		'tipo' => gestorAccesosUtf8($usuario['tipo']),
		'estado' => gestorAccesosUtf8($usuario['estado']),
		'rol_id' => intval($usuario['acceso']),
		'rol_consultado_id' => $rolId,
		'protegido' => esSuperAdministradorProtegido($usuarioObjetivo, $mysqli) ? 1 : 0
	);
	$catalogo['grupos'] = $grupos;
	$catalogo['resumen'] = array(
		'total' => $total,
		'habilitados' => $habilitados,
		'excepciones' => $excepciones,
		'porcentaje' => $total > 0 ? intval(round(($habilitados * 100) / $total)) : 0
	);
	$catalogo['usuario_sesion'] = intval($usuarioSesion);
	mysqli_close($mysqli);
	responderAccesos("exito", array("datos" => $catalogo, "2" => $catalogo));
}

function gestorAccesosNormalizarEstados($mysqli, $json, $totalEsperado)
{
	$entrada = json_decode((string)$json, true);
	if (!is_array($entrada)) {
		return false;
	}
	$validos = array();
	$resultado = $mysqli->query("SELECT idlistadodeacceso
		FROM listadodeacceso WHERE tipo='Administrativo'
		ORDER BY idlistadodeacceso");
	if (!$resultado) {
		return false;
	}
	while ($fila = $resultado->fetch_assoc()) {
		$validos[intval($fila['idlistadodeacceso'])] = 'NO';
	}
	if (intval($totalEsperado) !== count($validos) || count($entrada) !== count($validos)) {
		return null;
	}
	$recibidos = array();
	foreach ($entrada as $permiso) {
		$id = isset($permiso['id']) ? intval($permiso['id']) : 0;
		$accion = isset($permiso['accion']) ? strtoupper(trim((string)$permiso['accion'])) : '';
		if ($id <= 0 || !isset($validos[$id]) || isset($recibidos[$id])
			|| ($accion !== 'SI' && $accion !== 'NO')) {
			return false;
		}
		$validos[$id] = $accion;
		$recibidos[$id] = true;
	}
	return $validos;
}

function gestorAccesosCodigoId($mysqli, $codigo)
{
	$stmt = $mysqli->prepare("SELECT idlistadodeacceso FROM listadodeacceso
		WHERE UPPER(TRIM(IFNULL(codigo,'')))=? AND tipo='Administrativo'
		ORDER BY idlistadodeacceso ASC LIMIT 1");
	if (!$stmt) {
		return 0;
	}
	$codigo = strtoupper(trim((string)$codigo));
	$stmt->bind_param('s', $codigo);
	if (!$stmt->execute()) {
		$stmt->close();
		return 0;
	}
	$resultado = $stmt->get_result();
	$fila = $resultado ? $resultado->fetch_assoc() : null;
	$stmt->close();
	return $fila ? intval($fila['idlistadodeacceso']) : 0;
}

function gestorAccesosResumenEstados($mysqli, $estados)
{
	$habilitados = array();
	if (!is_array($estados) || count($estados) === 0) {
		return json_encode($habilitados);
	}
	$ids = implode(',', array_map('intval', array_keys($estados)));
	$resultado = $mysqli->query("SELECT idlistadodeacceso,codigo FROM listadodeacceso
		WHERE idlistadodeacceso IN (".$ids.") ORDER BY idlistadodeacceso");
	if ($resultado) {
		while ($fila = $resultado->fetch_assoc()) {
			$id = intval($fila['idlistadodeacceso']);
			if (isset($estados[$id]) && $estados[$id] === 'SI') {
				$habilitados[] = gestorAccesosUtf8($fila['codigo']);
			}
		}
	}
	return json_encode($habilitados);
}

function gestorAccesosEstadosUsuario($mysqli, $usuarioId)
{
	$estados = array();
	$stmt = $mysqli->prepare("SELECT idlistadodeaccesoFK,
			IF(MAX(CASE WHEN UPPER(TRIM(IFNULL(accion,'')))='SI' THEN 1 ELSE 0 END)=1,'SI','NO') AS accion
		FROM accesosuser
		WHERE usuarios_idusario=? AND tipo='Administrativo'
		GROUP BY idlistadodeaccesoFK");
	if (!$stmt) {
		return $estados;
	}
	$stmt->bind_param('i', $usuarioId);
	if ($stmt->execute()) {
		$resultado = $stmt->get_result();
		while ($fila = $resultado->fetch_assoc()) {
			$estados[intval($fila['idlistadodeaccesoFK'])] = $fila['accion'] === 'SI' ? 'SI' : 'NO';
		}
	}
	$stmt->close();
	return $estados;
}

function gestorAccesosRegistrarAuditoria($mysqli, $usuarioId, $campo, $anterior, $nuevo, $actor, $origen)
{
	if (!gestorAccesosTablaExiste($mysqli, 'usuario_historial_cambios')) {
		return true;
	}
	$sql = "INSERT INTO usuario_historial_cambios
		(cod_usuarioFK,campo,valor_anterior,valor_nuevo,fecha_hora,cod_usuario_modifico,origen,estado)
		VALUES (?,?,?,?,NOW(),?,?,'Registrado')";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('isssis', $usuarioId, $campo, $anterior, $nuevo, $actor, $origen);
	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}

function gestorAccesosReemplazarUsuario($mysqli, $usuarioId, $estados)
{
	$stmtDelete = $mysqli->prepare("DELETE FROM accesosuser
		WHERE usuarios_idusario=? AND tipo='Administrativo'");
	if (!$stmtDelete) {
		return false;
	}
	$stmtDelete->bind_param('i', $usuarioId);
	if (!$stmtDelete->execute()) {
		$stmtDelete->close();
		return false;
	}
	$stmtDelete->close();

	$stmtInsert = $mysqli->prepare("INSERT INTO accesosuser
		(idlistadodeaccesoFK,tipo,usuarios_idusario,accion)
		VALUES (?,'Administrativo',?,?)");
	if (!$stmtInsert) {
		return false;
	}
	foreach ($estados as $idAcceso => $accion) {
		$idAcceso = intval($idAcceso);
		$stmtInsert->bind_param('iis', $idAcceso, $usuarioId, $accion);
		if (!$stmtInsert->execute()) {
			$stmtInsert->close();
			return false;
		}
	}
	$stmtInsert->close();
	return true;
}

function gestorAccesosGuardarUsuario($usuarioSesion)
{
	$usuarioId = isset($_POST['usuario_id']) ? trim((string)$_POST['usuario_id']) : '';
	$rolId = isset($_POST['rol_id']) ? trim((string)$_POST['rol_id']) : '';
	$total = isset($_POST['catalogo_total']) ? intval($_POST['catalogo_total']) : 0;
	$json = isset($_POST['permisos']) ? $_POST['permisos'] : '';
	if (!ctype_digit($usuarioId) || !ctype_digit($rolId)) {
		responderAccesos("DI");
	}
	$mysqli = conectar_al_servidor();
	$usuario = gestorAccesosObtenerUsuario($mysqli, intval($usuarioId));
	$rol = gestorAccesosObtenerRol($mysqli, intval($rolId));
	if (!$usuario || !$rol || strtoupper(trim((string)$rol['estado'])) !== 'ACTIVO') {
		mysqli_close($mysqli);
		responderAccesos("DI", array("2" => "El usuario o el rol ya no se encuentra disponible."));
	}
	if (esSuperAdministradorProtegido($usuarioId, $mysqli)) {
		mysqli_close($mysqli);
		responderAccesos("PROTEGIDO", array("2" => "La cuenta superadministradora no admite cambios de rol ni restricciones."));
	}
	$estados = gestorAccesosNormalizarEstados($mysqli, $json, $total);
	if ($estados === null) {
		mysqli_close($mysqli);
		responderAccesos("DESACTUALIZADO", array("2" => "El catalogo de permisos cambio. Vuelva a cargar antes de guardar."));
	}
	if ($estados === false) {
		mysqli_close($mysqli);
		responderAccesos("DI", array("2" => "La matriz de permisos recibida no es valida."));
	}
	$idAdministrar = gestorAccesosCodigoId($mysqli, 'VERACCESOSUARIOS');
	if ((string)$usuarioId === (string)$usuarioSesion
		&& ($idAdministrar <= 0 || !isset($estados[$idAdministrar]) || $estados[$idAdministrar] !== 'SI')) {
		mysqli_close($mysqli);
		responderAccesos("PROTEGIDO", array("2" => "No puede quitarse su propio permiso para administrar accesos."));
	}

	$anteriores = gestorAccesosEstadosUsuario($mysqli, intval($usuarioId));
	$resumenAnterior = gestorAccesosResumenEstados($mysqli, $anteriores);
	$resumenNuevo = gestorAccesosResumenEstados($mysqli, $estados);
	$rolAnterior = gestorAccesosObtenerRol($mysqli, intval($usuario['acceso']));
	if (!$mysqli->begin_transaction()) {
		mysqli_close($mysqli);
		responderAccesos("Error", array("2" => "No se pudo iniciar la actualizacion de permisos."));
	}
	try {
		if ((string)$usuario['acceso'] !== (string)$rolId) {
			$stmtRol = $mysqli->prepare("UPDATE usuario SET acceso=? WHERE cod_usuario=? LIMIT 1");
			if (!$stmtRol) {
				throw new Exception('No se pudo preparar el cambio de rol.');
			}
			$stmtRol->bind_param('ii', $rolId, $usuarioId);
			if (!$stmtRol->execute()) {
				$stmtRol->close();
				throw new Exception('No se pudo cambiar el rol.');
			}
			$stmtRol->close();
			$rolAnteriorNombre = $rolAnterior ? gestorAccesosUtf8($rolAnterior['nombre']) : (string)$usuario['acceso'];
			$rolNuevoNombre = gestorAccesosUtf8($rol['nombre']);
			if (!gestorAccesosRegistrarAuditoria(
				$mysqli, intval($usuarioId), 'Rol de acceso',
				$rolAnteriorNombre, $rolNuevoNombre, intval($usuarioSesion), 'Administrador visual de accesos'
			)) {
				throw new Exception('No se pudo auditar el cambio de rol.');
			}
		}
		if (!gestorAccesosReemplazarUsuario($mysqli, intval($usuarioId), $estados)) {
			throw new Exception('No se pudo reemplazar la matriz de permisos.');
		}
		if ($resumenAnterior !== $resumenNuevo
			&& !gestorAccesosRegistrarAuditoria(
				$mysqli, intval($usuarioId), 'Permisos efectivos',
				$resumenAnterior, $resumenNuevo, intval($usuarioSesion), 'Administrador visual de accesos'
			)) {
			throw new Exception('No se pudo auditar la matriz de permisos.');
		}
		if (!$mysqli->commit()) {
			throw new Exception('No se pudo confirmar la actualizacion.');
		}
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderAccesos("Error", array("2" => "No se guardaron los cambios."));
	}
	mysqli_close($mysqli);
	responderAccesos("exito", array(
		"2" => "Permisos del usuario actualizados.",
		"usuario_id" => intval($usuarioId),
		"rol_id" => intval($rolId),
		"sesion_afectada" => (string)$usuarioId === (string)$usuarioSesion ? 1 : 0
	));
}

function gestorAccesosImpactoRol($rolId)
{
	$rolId = trim((string)$rolId);
	if ($rolId === '' || !ctype_digit($rolId)) {
		responderAccesos("DI");
	}
	$mysqli = conectar_al_servidor();
	$rol = gestorAccesosObtenerRol($mysqli, intval($rolId));
	if (!$rol) {
		mysqli_close($mysqli);
		responderAccesos("DI");
	}
	$stmt = $mysqli->prepare("SELECT COUNT(*) FROM usuario WHERE acceso=?");
	$stmt->bind_param('s', $rolId);
	$stmt->execute();
	$total = 0;
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	mysqli_close($mysqli);
	responderAccesos("exito", array(
		"rol_id" => intval($rolId),
		"rol_nombre" => gestorAccesosUtf8($rol['nombre']),
		"usuarios_afectados" => intval($total)
	));
}

function gestorAccesosGuardarRol($usuarioSesion)
{
	$rolId = isset($_POST['rol_id']) ? trim((string)$_POST['rol_id']) : '';
	$total = isset($_POST['catalogo_total']) ? intval($_POST['catalogo_total']) : 0;
	$json = isset($_POST['permisos']) ? $_POST['permisos'] : '';
	if (!ctype_digit($rolId)) {
		responderAccesos("DI");
	}
	$mysqli = conectar_al_servidor();
	$rol = gestorAccesosObtenerRol($mysqli, intval($rolId));
	if (!$rol || strtoupper(trim((string)$rol['estado'])) !== 'ACTIVO') {
		mysqli_close($mysqli);
		responderAccesos("DI", array("2" => "El rol ya no se encuentra activo."));
	}
	$estados = gestorAccesosNormalizarEstados($mysqli, $json, $total);
	if ($estados === null) {
		mysqli_close($mysqli);
		responderAccesos("DESACTUALIZADO", array("2" => "El catalogo de permisos cambio. Vuelva a cargar antes de guardar."));
	}
	if ($estados === false) {
		mysqli_close($mysqli);
		responderAccesos("DI", array("2" => "La matriz de permisos recibida no es valida."));
	}
	$idAdministrar = gestorAccesosCodigoId($mysqli, 'VERACCESOSUARIOS');
	$usuarioActual = gestorAccesosObtenerUsuario($mysqli, intval($usuarioSesion));
	if ($usuarioActual && (string)$usuarioActual['acceso'] === (string)$rolId
		&& ($idAdministrar <= 0 || !isset($estados[$idAdministrar]) || $estados[$idAdministrar] !== 'SI')) {
		mysqli_close($mysqli);
		responderAccesos("PROTEGIDO", array("2" => "No puede quitar al rol actual su propio permiso para administrar accesos."));
	}

	$usuariosRol = array();
	$stmtUsuarios = $mysqli->prepare("SELECT cod_usuario FROM usuario WHERE acceso=? ORDER BY cod_usuario");
	$stmtUsuarios->bind_param('s', $rolId);
	$stmtUsuarios->execute();
	$resultUsuarios = $stmtUsuarios->get_result();
	while ($fila = $resultUsuarios->fetch_assoc()) {
		$usuariosRol[] = intval($fila['cod_usuario']);
	}
	$stmtUsuarios->close();
	$resumenNuevo = gestorAccesosResumenEstados($mysqli, $estados);

	if (!$mysqli->begin_transaction()) {
		mysqli_close($mysqli);
		responderAccesos("Error", array("2" => "No se pudo iniciar la actualizacion del rol."));
	}
	try {
		$stmtDelete = $mysqli->prepare("DELETE FROM detallesniveles WHERE cod_nivelesfk=?");
		if (!$stmtDelete) {
			throw new Exception('No se pudo preparar la plantilla.');
		}
		$stmtDelete->bind_param('i', $rolId);
		if (!$stmtDelete->execute()) {
			$stmtDelete->close();
			throw new Exception('No se pudo reemplazar la plantilla.');
		}
		$stmtDelete->close();

		$stmtInsert = $mysqli->prepare("INSERT INTO detallesniveles
			(cod_nivelesfk,idlistadodeacceso,accion) VALUES (?,?,?)");
		if (!$stmtInsert) {
			throw new Exception('No se pudo preparar la plantilla.');
		}
		foreach ($estados as $idAcceso => $accion) {
			$idAcceso = intval($idAcceso);
			$stmtInsert->bind_param('iis', $rolId, $idAcceso, $accion);
			if (!$stmtInsert->execute()) {
				$stmtInsert->close();
				throw new Exception('No se pudo guardar la plantilla.');
			}
		}
		$stmtInsert->close();

		foreach ($usuariosRol as $usuarioId) {
			$anteriores = gestorAccesosEstadosUsuario($mysqli, $usuarioId);
			$resumenAnterior = gestorAccesosResumenEstados($mysqli, $anteriores);
			if (!gestorAccesosReemplazarUsuario($mysqli, $usuarioId, $estados)) {
				throw new Exception('No se pudo sincronizar un usuario del rol.');
			}
			if ($resumenAnterior !== $resumenNuevo
				&& !gestorAccesosRegistrarAuditoria(
					$mysqli, $usuarioId, 'Permisos heredados del rol',
					$resumenAnterior, $resumenNuevo, intval($usuarioSesion), 'Administrador visual de accesos'
				)) {
				throw new Exception('No se pudo auditar un usuario del rol.');
			}
		}
		if (!$mysqli->commit()) {
			throw new Exception('No se pudo confirmar la plantilla.');
		}
	} catch (Exception $e) {
		$mysqli->rollback();
		mysqli_close($mysqli);
		responderAccesos("Error", array("2" => "No se guardo la plantilla ni se modificaron sus usuarios."));
	}

	if (in_array(CLINIDENT_SUPERADMIN_PROTEGIDO_ID, $usuariosRol, true)
		&& esSuperAdministradorProtegido(CLINIDENT_SUPERADMIN_PROTEGIDO_ID, $mysqli)) {
		$mysqli->query("UPDATE accesosuser SET accion='SI'
			WHERE usuarios_idusario=".intval(CLINIDENT_SUPERADMIN_PROTEGIDO_ID)."
				AND tipo='Administrativo'");
	}
	mysqli_close($mysqli);
	responderAccesos("exito", array(
		"2" => "Plantilla del rol actualizada.",
		"rol_id" => intval($rolId),
		"usuarios_actualizados" => count($usuariosRol),
		"sesion_afectada" => in_array(intval($usuarioSesion), $usuariosRol, true) ? 1 : 0
	));
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

	if ($funt === "cargarGestor") {
		$buscar = isset($_POST['usuario_id']) ? $_POST['usuario_id'] : '';
		$rolId = isset($_POST['rol_id']) ? $_POST['rol_id'] : '';
		gestorAccesosCargar($buscar, $user, $rolId);
	}

	if ($funt === "guardarUsuarioGestor") {
		gestorAccesosGuardarUsuario($user);
	}

	if ($funt === "impactoRolGestor") {
		$rolId = isset($_POST['rol_id']) ? $_POST['rol_id'] : '';
		gestorAccesosImpactoRol($rolId);
	}

	if ($funt === "guardarRolGestor") {
		gestorAccesosGuardarRol($user);
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

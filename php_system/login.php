<?php

//cargar achivos importantes
require_once("conexion.php");

function responder_login($codigo, $mensaje = '')
{
	$informacion = array("1" => $codigo);
	if ($mensaje !== '') {
		$informacion["mensaje"] = $mensaje;
	}
	echo json_encode($informacion);
	exit;
}

function verificar_conexion_login($mysqli)
{
	if (!$mysqli || $mysqli->connect_errno) {
		error_log('Login DB connection error: ' . ($mysqli ? $mysqli->connect_error : 'sin objeto mysqli'));
		responder_login('error', 'No se pudo conectar con la base de datos.');
	}
}

function preparar_login($mysqli, $consulta, $contexto)
{
	$stmt = $mysqli->prepare($consulta);
	if (!$stmt) {
		error_log('Login prepare error [' . $contexto . ']: ' . $mysqli->error);
		responder_login('error', 'No se pudo iniciar sesion. Verifique la estructura de la base de datos.');
	}
	return $stmt;
}

function ejecutar_login($stmt, $contexto, $mensaje = 'No se pudo completar el inicio de sesion.')
{
	if (!$stmt->execute()) {
		error_log('Login execute error [' . $contexto . ']: ' . $stmt->error);
		responder_login('error', $mensaje);
	}
}

function post_login($nombre)
{
	$valor = isset($_POST[$nombre]) ? $_POST[$nombre] : '';
	return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function verificar()
{
	$user = post_login('user');
	$local = post_login('local');
	$pass = post_login('pass');
	$navegador = post_login('navegador');

	if ($user === '' || $pass === '' || $local === '') {
		responder_login('UI');
	}

	login($user, $pass, $local, $navegador);
}


function login($user, $pass, $local, $navegador)
{
	$mysqli = conectar_al_servidor();
	verificar_conexion_login($mysqli);

	$sql = "SELECT cod_usuario FROM usuario WHERE estado = 'Activo' AND login = ? AND password = ? AND cod_localFK = ? LIMIT 1";
	$stmt = preparar_login($mysqli, $sql, 'buscar usuario');
	$tipos = 'sss';
	$stmt->bind_param($tipos, $user, $pass, $local);
	ejecutar_login($stmt, 'buscar usuario', 'No se pudieron verificar las credenciales.');

	$result = $stmt->get_result();
	if (!$result || mysqli_num_rows($result) === 0) {
		responder_login('UI');
	}

	$valor = mysqli_fetch_assoc($result);
	$iduser = $valor['cod_usuario'];
	cargar_datos_de_seguridad($mysqli, $iduser, $navegador);
}


function cargar_datos_de_seguridad($mysqli, $usuario, $nav)
{
	$id_na = rand(100, 5000);

	$consulta = "DELETE FROM seguridad WHERE id_usuario = ?";
	$stmt = preparar_login($mysqli, $consulta, 'limpiar seguridad');
	$tipos = 's';
	$stmt->bind_param($tipos, $usuario);
	ejecutar_login($stmt, 'limpiar seguridad', 'No se pudo limpiar la sesion anterior. Revise permisos DELETE sobre la tabla seguridad.');

	$id_na = base64_encode($id_na);
	$id_na = str_replace("=", "+", $id_na);
	$consulta = "INSERT INTO seguridad (id_usuario, navegador, pass) VALUES (?, ?, ?)";

	$stmt = preparar_login($mysqli, $consulta, 'crear seguridad');
	$tipos = 'sss';
	$stmt->bind_param($tipos, $usuario, $nav, $id_na);
	ejecutar_login($stmt, 'crear seguridad', 'No se pudo crear la sesion de seguridad. Revise AUTO_INCREMENT y permisos INSERT sobre la tabla seguridad.');

	$informacion = array("1" => $id_na, "2" => $usuario);
	echo json_encode($informacion);
	exit;
}


verificar();
?>

<?php

function iniciarContextoAuditoriaPermisos($actor, $operacion)
{
	$endpoint = isset($_SERVER['PHP_SELF']) ? substr((string)$_SERVER['PHP_SELF'], 0, 180) : '';
	$operacion = substr((string)$operacion, 0, 80);
	$GLOBALS['goodventa_contexto_auditoria_permisos'] = array(
		'actor' => intval($actor),
		'origen' => trim($endpoint . ($operacion !== '' ? ' | ' . $operacion : '')),
		'ip' => isset($_SERVER['REMOTE_ADDR']) ? substr((string)$_SERVER['REMOTE_ADDR'], 0, 45) : '',
		'navegador' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 500) : '',
		'grupo' => str_replace('.', '', uniqid('perm_', true))
	);
}

function aplicarContextoAuditoriaPermisos($mysqli)
{
	if (!isset($GLOBALS['goodventa_contexto_auditoria_permisos'])
		|| !is_array($GLOBALS['goodventa_contexto_auditoria_permisos'])) {
		return true;
	}
	$contexto = $GLOBALS['goodventa_contexto_auditoria_permisos'];
	$stmt = $mysqli->prepare("SET @gv_audit_actor=?, @gv_audit_origen=?, @gv_audit_ip=?, @gv_audit_navegador=?, @gv_audit_grupo=?");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param(
		'issss',
		$contexto['actor'],
		$contexto['origen'],
		$contexto['ip'],
		$contexto['navegador'],
		$contexto['grupo']
	);
	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}


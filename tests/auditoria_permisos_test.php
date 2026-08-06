<?php
$_POST['useru'] = '2';
$_POST['funt'] = 'PRUEBA_AUDITORIA_PERMISOS';
$_SERVER['PHP_SELF'] = '/GoodVentaAsisCap/tests/auditoria_permisos_test.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'GoodVenta auditoria test';

require_once dirname(__DIR__) . '/php_system/abmAccesos.php';

$conexionNormal = conectar_al_servidor();
$sinContexto = $conexionNormal->query("SELECT @gv_audit_actor IS NULL AS sin_contexto")->fetch_assoc();
$conexionNormal->close();
if (!$sinContexto || intval($sinContexto['sin_contexto']) !== 1) {
	fwrite(STDERR, "Una conexion normal activo indebidamente la auditoria.\n");
	exit(1);
}

iniciarContextoAuditoriaPermisos(2, 'PRUEBA_AUDITORIA_PERMISOS');
$mysqli = conectar_al_servidor();
if ($mysqli->connect_errno) {
	fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
	exit(1);
}
$fila = $mysqli->query("SELECT idlistadodeaccesoFK,usuarios_idusario,accion
	FROM accesosuser WHERE accion IN ('SI','NO') LIMIT 1")->fetch_assoc();
if (!$fila) {
	fwrite(STDERR, "No existe un permiso apto para la prueba.\n");
	exit(1);
}
$usuarioId = intval($fila['usuarios_idusario']);
$idAcceso = intval($fila['idlistadodeaccesoFK']);
$anterior = $fila['accion'];
$nuevo = $anterior === 'SI' ? 'NO' : 'SI';
$mysqli->begin_transaction();
$ok = gestorAccesosAuditarDiferenciasPermisos(
	$mysqli,
	$usuarioId,
	array($idAcceso => $anterior),
	array($idAcceso => $nuevo)
);
$resultado = $mysqli->query("SELECT cod_usuario_actor,origen,ip,navegador,accion_anterior,accion_nueva
	FROM accesosuser_auditoria
	WHERE origen LIKE '%PRUEBA_AUDITORIA_PERMISOS%'
	ORDER BY id DESC LIMIT 1");
$auditada = $resultado ? $resultado->fetch_assoc() : null;
$mysqli->rollback();
$mysqli->close();

if (!$ok || !$auditada
	|| intval($auditada['cod_usuario_actor']) !== 2
	|| $auditada['accion_anterior'] !== $anterior
	|| $auditada['accion_nueva'] !== $nuevo
	|| $auditada['ip'] !== '127.0.0.1') {
	fwrite(STDERR, "La auditoria no preservo el contexto esperado.\n");
	exit(1);
}

echo "OK: auditoria de permisos con contexto y rollback seguro.\n";

<?php

require_once dirname(__DIR__).'/php_system/conexion.php';

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo conectar a la base local.\n");
    exit(2);
}

$esperadas = array(
    'cartera_configuracion',
    'cartera_equipo',
    'cartera_asignacion',
    'cartera_gestion',
    'cartera_compromiso',
    'cartera_evento'
);
$errores = 0;
foreach ($esperadas as $tabla) {
    $tablaSql = $mysqli->real_escape_string($tabla);
    $resultado = $mysqli->query(
        "SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() "
        ."AND table_name='".$tablaSql."' LIMIT 1"
    );
    $ok = $resultado && $resultado->num_rows === 1;
    echo ($ok ? '[OK] ' : '[ERROR] ').$tabla.PHP_EOL;
    if (!$ok) { $errores++; }
}

$resultado = $mysqli->query(
    "SELECT access_key,is_active,permission_key FROM dashboard_access_catalog "
    ."WHERE access_key='mi_cartera' LIMIT 1"
);
$fila = $resultado ? $resultado->fetch_assoc() : null;
$catalogoOk = $fila && intval($fila['is_active']) === 1 && trim((string)$fila['permission_key']) === '';
echo ($catalogoOk ? '[OK] ' : '[ERROR] ').'catalogo mi_cartera'.PHP_EOL;
if (!$catalogoOk) { $errores++; }

$resultado = $mysqli->query(
    "SELECT dias_prevencion,dias_escalamiento,intentos_escalamiento,activo "
    ."FROM cartera_configuracion WHERE id_configuracion=1 LIMIT 1"
);
$fila = $resultado ? $resultado->fetch_assoc() : null;
$reglasOk = $fila && intval($fila['dias_prevencion']) === 7
    && intval($fila['dias_escalamiento']) === 30
    && intval($fila['intentos_escalamiento']) === 2
    && intval($fila['activo']) === 1;
echo ($reglasOk ? '[OK] ' : '[ERROR] ').'reglas 7 dias / 30 dias / 2 intentos'.PHP_EOL;
if (!$reglasOk) { $errores++; }

$mysqli->close();
exit($errores > 0 ? 1 : 0);

?>

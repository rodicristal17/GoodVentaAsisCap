<?php

require_once dirname(__DIR__).'/php_system/conexion.php';

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo conectar con la base de datos.\n");
    exit(2);
}

$tablas = array(
    'gohighlevel_permiso_usuario',
    'gohighlevel_vinculo_contacto',
    'gohighlevel_evento'
);
$faltantes = array();
foreach ($tablas as $tabla) {
    $stmt = $mysqli->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?'
    );
    $stmt->bind_param('s', $tabla);
    $total = 0;
    $stmt->execute();
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    if (intval($total) !== 1) {
        $faltantes[] = $tabla;
    }
}

$catalogo = $mysqli->query(
    "SELECT COUNT(*) total FROM dashboard_access_catalog WHERE access_key='gohighlevel' AND is_active=1"
);
$fila = $catalogo ? $catalogo->fetch_assoc() : array('total' => 0);
if (intval($fila['total']) !== 1) {
    $faltantes[] = 'dashboard_access_catalog:gohighlevel';
}

$propietario = $mysqli->query(
    "SELECT COUNT(*) total FROM gohighlevel_permiso_usuario "
    ."WHERE cod_usuarioFK=5994 AND puede_ver=1 AND puede_configurar=1 AND activo=1"
);
$fila = $propietario ? $propietario->fetch_assoc() : array('total' => 0);
if (intval($fila['total']) !== 1) {
    $faltantes[] = 'permiso propietario';
}

if (count($faltantes) > 0) {
    fwrite(STDERR, 'GoHighLevel DB incompleta: '.implode(', ', $faltantes)."\n");
    exit(1);
}

echo "GoHighLevel DB: estructura y acceso inicial correctos.\n";
exit(0);

?>

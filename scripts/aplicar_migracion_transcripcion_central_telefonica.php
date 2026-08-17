<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';

$aplicar = in_array('--apply', $argv, true);
$ruta = dirname(__DIR__).'/actualizacion_17082026_central_telefonica_transcripcion_openai.sql';
if (!is_file($ruta) || !is_readable($ruta)) {
    fwrite(STDERR, "No se encontro la migracion de transcripciones.\n");
    exit(2);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(3);
}

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM usuario u INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
    ."WHERE u.cod_usuario=5994 AND LOWER(TRIM(IFNULL(u.login,'')))='cf' "
    ."AND UPPER(TRIM(IFNULL(u.tipo,'')))='ADMINISTRATIVO' "
    ."AND UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO' "
    ."AND UPPER(TRIM(IFNULL(p.nombre_persona,''))) LIKE 'CARLOS FARAONE CLINIDENT%'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$cuentaProtegida = intval($fila['total']) === 1;

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name IN ('central_telefonica_transcripcion',"
    ."'central_telefonica_transcripcion_evento','central_telefonica_transcripcion_servicio')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$tablasAntes = intval($fila['total']);

if (!$aplicar) {
    fwrite(STDOUT, json_encode(array(
        'ok' => $cuentaProtegida,
        'codigo' => $cuentaProtegida ? 'preflight_aprobado' : 'cuenta_protegida_no_confirmada',
        'tablas_antes' => $tablasAntes,
        'sha256' => hash_file('sha256', $ruta),
        'instruccion' => 'Crear respaldo y ejecutar con --apply.'
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit($cuentaProtegida ? 0 : 4);
}

if (!$cuentaProtegida) {
    fwrite(STDERR, "No se confirmo la identidad protegida de Carlos Faraone.\n");
    $mysqli->close();
    exit(4);
}

$sql = file_get_contents($ruta);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "La migracion esta vacia o no se pudo leer.\n");
    $mysqli->close();
    exit(5);
}
if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "No se pudo iniciar la migracion: ".$mysqli->error."\n");
    $mysqli->close();
    exit(6);
}
do {
    if ($actual = $mysqli->store_result()) {
        while ($actual->fetch_assoc()) {
            // Consumir verificaciones sin exponer datos.
        }
        $actual->free();
    }
    if (!$mysqli->more_results()) {
        break;
    }
    if (!$mysqli->next_result()) {
        fwrite(STDERR, "La migracion se interrumpio: ".$mysqli->error."\n");
        $mysqli->close();
        exit(7);
    }
} while (true);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name IN ('central_telefonica_transcripcion',"
    ."'central_telefonica_transcripcion_evento','central_telefonica_transcripcion_servicio')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$tablasDespues = intval($fila['total']);
$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM accesosuser au "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
    ."WHERE la.codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' "
    ."AND au.tipo='Administrativo' AND UPPER(TRIM(au.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$habilitados = intval($fila['total']);
$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM accesosuser au "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
    ."WHERE la.codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' "
    ."AND au.usuarios_idusario=5994 AND au.tipo='Administrativo' "
    ."AND UPPER(TRIM(au.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$carlos = intval($fila['total']);
$mysqli->close();

$ok = $tablasDespues === 3 && $habilitados === 1 && $carlos === 1;
fwrite(STDOUT, json_encode(array(
    'ok' => $ok,
    'codigo' => $ok ? 'migracion_aplicada' : 'verificacion_incompleta',
    'tablas_antes' => $tablasAntes,
    'tablas_despues' => $tablasDespues,
    'usuarios_habilitados' => $habilitados,
    'carlos_habilitado' => $carlos === 1
), JSON_UNESCAPED_UNICODE).PHP_EOL);
exit($ok ? 0 : 8);

?>

<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('America/Asuncion');
ini_set('display_errors', '0');

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_sync_helper.php';

$aplicar = in_array('--apply', $argv, true);
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Reconstruye los snapshots de identidad desde los CDR locales.\n");
    fwrite(STDOUT, "Sin opciones: preflight de solo lectura. Opcion: --apply\n");
    exit(0);
}
$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDOUT, json_encode(array('ok' => false, 'codigo' => 'conexion_telar_no_disponible')).PHP_EOL);
    exit(2);
}
if (!centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
    fwrite(STDOUT, json_encode(array('ok' => false, 'codigo' => 'directorio_migracion_pendiente')).PHP_EOL);
    $mysqli->close();
    exit(3);
}
$resultado = $mysqli->query(
    "SELECT COUNT(DISTINCT grupo_clave) total FROM central_telefonica_cdr_segmento"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
$total = intval($fila['total']);
if (!$aplicar) {
    fwrite(STDOUT, json_encode(array(
        'ok' => true,
        'codigo' => 'preflight_aprobado',
        'grupos' => $total,
        'instruccion' => 'Crear respaldo y ejecutar con --apply.'
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit(0);
}

$bloqueo = false;
try {
    $rb = $mysqli->query("SELECT GET_LOCK('telar_central_telefonica_reconstruir_identidad',0) adquirido");
    $fb = $rb ? $rb->fetch_assoc() : null;
    $bloqueo = $fb && intval($fb['adquirido']) === 1;
    if (!$bloqueo) {
        throw new Exception('reconstruccion_en_curso');
    }
    $config = centralTelefonicaCargarConfiguracionIssabel();
    $gruposResultado = $mysqli->query(
        "SELECT DISTINCT grupo_clave FROM central_telefonica_cdr_segmento ORDER BY grupo_clave"
    );
    $grupos = array();
    while ($gruposResultado && ($grupo = $gruposResultado->fetch_assoc())) {
        $grupos[] = $grupo['grupo_clave'];
    }
    $mysqli->begin_transaction();
    $procesados = 0;
    foreach ($grupos as $grupo) {
        $segmentos = centralTelefonicaSyncSegmentosGrupo($mysqli, $grupo);
        $consolidado = centralTelefonicaConstruirConsolidado($segmentos, $config);
        if (!$consolidado) {
            continue;
        }
        $consolidado = centralTelefonicaDirectorioEnriquecerConsolidado(
            $mysqli,
            $consolidado,
            $segmentos,
            $config
        );
        centralTelefonicaSyncGuardarConsolidado($mysqli, $consolidado);
        $procesados++;
    }
    $mysqli->commit();
    $mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_reconstruir_identidad')");
    $bloqueo = false;
    $mysqli->close();
    fwrite(STDOUT, json_encode(array(
        'ok' => $procesados === $total,
        'codigo' => $procesados === $total ? 'identidades_reconstruidas' : 'reconstruccion_incompleta',
        'grupos' => $total,
        'procesados' => $procesados
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit($procesados === $total ? 0 : 4);
} catch (Exception $e) {
    @$mysqli->rollback();
    if ($bloqueo) {
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_reconstruir_identidad')");
    }
    error_log('[CentralTelefonicaIdentidad] '.$e->getMessage());
    @$mysqli->close();
    fwrite(STDOUT, json_encode(array('ok' => false, 'codigo' => 'error_reconstruccion')).PHP_EOL);
    exit(5);
} catch (Throwable $e) {
    @$mysqli->rollback();
    if ($bloqueo) {
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_reconstruir_identidad')");
    }
    error_log('[CentralTelefonicaIdentidad] '.$e->getMessage());
    @$mysqli->close();
    fwrite(STDOUT, json_encode(array('ok' => false, 'codigo' => 'error_reconstruccion')).PHP_EOL);
    exit(5);
}

?>

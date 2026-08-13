<?php

/**
 * Sincronizador incremental Issabel CDR -> Sistema Telar.
 *
 * Uso:
 *   php scripts/sincronizar_central_telefonica.php
 *   php scripts/sincronizar_central_telefonica.php --dry-run --limit=100
 *
 * El usuario configurado en Issabel debe poseer solamente SELECT sobre la
 * tabla CDR. Este proceso nunca ejecuta escrituras en Issabel.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('America/Asuncion');
ini_set('display_errors', '0');

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_sync_helper.php';

function centralTelefonicaCliOpciones($argumentos)
{
    $opciones = array('dry_run' => false, 'limite' => 0, 'help' => false);
    foreach ($argumentos as $indice => $argumento) {
        if ($indice === 0) {
            continue;
        }
        if ($argumento === '--dry-run') {
            $opciones['dry_run'] = true;
        } elseif ($argumento === '--help' || $argumento === '-h') {
            $opciones['help'] = true;
        } elseif (strpos($argumento, '--limit=') === 0) {
            $opciones['limite'] = intval(substr($argumento, 8));
        }
    }
    return $opciones;
}

function centralTelefonicaCliSalida($datos, $codigo)
{
    fwrite(STDOUT, json_encode($datos, JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit(intval($codigo));
}

$opciones = centralTelefonicaCliOpciones($argv);
if ($opciones['help']) {
    fwrite(STDOUT, "Sincroniza CDR de Issabel en modo incremental.\n");
    fwrite(STDOUT, "Opciones: --dry-run --limit=N\n");
    exit(0);
}

$mysqli = null;
$bloqueo = false;
try {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        centralTelefonicaCliSalida(array(
            'ok' => false,
            'codigo' => 'conexion_telar_no_disponible'
        ), 2);
    }

    $resultadoBloqueo = $mysqli->query("SELECT GET_LOCK('telar_central_telefonica_sync',0) AS adquirido");
    $filaBloqueo = $resultadoBloqueo ? $resultadoBloqueo->fetch_assoc() : null;
    $bloqueo = $filaBloqueo && intval($filaBloqueo['adquirido']) === 1;
    if (!$bloqueo) {
        centralTelefonicaCliSalida(array(
            'ok' => true,
            'codigo' => 'sincronizacion_en_curso'
        ), 0);
    }

    $config = centralTelefonicaCargarConfiguracionIssabel();
    if ($opciones['limite'] <= 0) {
        $opciones['limite'] = intval($config['batch_limit']);
    }
    $resultado = centralTelefonicaEjecutarSincronizacion($mysqli, $config, $opciones);
    $mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_sync')");
    $bloqueo = false;
    $mysqli->close();
    centralTelefonicaCliSalida(array(
        'ok' => true,
        'codigo' => $opciones['dry_run'] ? 'lectura_validada' : 'sincronizacion_completada',
        'resultado' => $resultado
    ), 0);
} catch (CentralTelefonicaSyncExcepcion $e) {
    if ($mysqli instanceof mysqli) {
        if ($bloqueo) {
            @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_sync')");
        }
        @$mysqli->close();
    }
    centralTelefonicaCliSalida(array(
        'ok' => false,
        'codigo' => $e->codigoOperacion
    ), 2);
} catch (Exception $e) {
    error_log('[CentralTelefonicaSync] '.get_class($e).': '.$e->getMessage());
    if ($mysqli instanceof mysqli) {
        if ($bloqueo) {
            @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_sync')");
        }
        @$mysqli->close();
    }
    centralTelefonicaCliSalida(array(
        'ok' => false,
        'codigo' => 'error_sincronizacion'
    ), 2);
} catch (Throwable $e) {
    error_log('[CentralTelefonicaSync] '.get_class($e).': '.$e->getMessage());
    if ($mysqli instanceof mysqli) {
        if ($bloqueo) {
            @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_sync')");
        }
        @$mysqli->close();
    }
    centralTelefonicaCliSalida(array(
        'ok' => false,
        'codigo' => 'error_sincronizacion'
    ), 2);
}

?>

<?php

/**
 * Worker CLI de transcripciones bajo demanda.
 *
 * Procesa como maximo una solicitud por ejecucion. Docker mantiene el ciclo y
 * GET_LOCK impide que dos instancias trabajen simultaneamente.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('America/Asuncion');
ini_set('display_errors', '0');

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_transcripcion_helper.php';

function centralTelefonicaTranscripcionCliSalida($datos, $codigo)
{
    fwrite(STDOUT, json_encode($datos, JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit(intval($codigo));
}

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Procesa una transcripcion pendiente de Central Telefonica.\n");
    fwrite(STDOUT, "No recibe audio, rutas ni credenciales por argumentos.\n");
    exit(0);
}

$mysqli = null;
$bloqueo = false;
try {
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        centralTelefonicaTranscripcionCliSalida(array(
            'ok' => false,
            'codigo' => 'conexion_telar_no_disponible'
        ), 2);
    }
    $mysqli->set_charset('utf8mb4');

    $resultadoBloqueo = $mysqli->query(
        "SELECT GET_LOCK('telar_central_telefonica_transcripcion',0) AS adquirido"
    );
    $filaBloqueo = $resultadoBloqueo ? $resultadoBloqueo->fetch_assoc() : null;
    $bloqueo = $filaBloqueo && intval($filaBloqueo['adquirido']) === 1;
    if (!$bloqueo) {
        centralTelefonicaTranscripcionCliSalida(array(
            'ok' => true,
            'codigo' => 'worker_en_curso'
        ), 0);
    }

    $config = centralTelefonicaTranscripcionConfig();
    $resultado = centralTelefonicaTranscripcionProcesarSiguiente($mysqli, $config);
    $mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_transcripcion')");
    $bloqueo = false;
    $mysqli->close();
    centralTelefonicaTranscripcionCliSalida(array(
        'ok' => !isset($resultado['ok']) || $resultado['ok'] !== false,
        'codigo' => isset($resultado['codigo']) ? $resultado['codigo'] : 'worker_completado',
        'procesado' => !empty($resultado['procesado']),
        'id_transcripcion' => isset($resultado['id_transcripcion'])
            ? intval($resultado['id_transcripcion']) : null
    ), isset($resultado['ok']) && $resultado['ok'] === false ? 2 : 0);
} catch (CentralTelefonicaTranscripcionExcepcion $e) {
    if ($mysqli instanceof mysqli) {
        if ($bloqueo) {
            @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_transcripcion')");
        }
        @$mysqli->close();
    }
    centralTelefonicaTranscripcionCliSalida(array(
        'ok' => false,
        'codigo' => $e->codigoOperacion
    ), 2);
} catch (Exception $e) {
    error_log('[CentralTelefonicaTranscripcion] '.get_class($e).': '.$e->getMessage());
    if ($mysqli instanceof mysqli) {
        if ($bloqueo) {
            @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_transcripcion')");
        }
        @$mysqli->close();
    }
    centralTelefonicaTranscripcionCliSalida(array(
        'ok' => false,
        'codigo' => 'error_worker_transcripcion'
    ), 2);
} catch (Throwable $e) {
    error_log('[CentralTelefonicaTranscripcion] '.get_class($e).': '.$e->getMessage());
    if ($mysqli instanceof mysqli) {
        if ($bloqueo) {
            @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_transcripcion')");
        }
        @$mysqli->close();
    }
    centralTelefonicaTranscripcionCliSalida(array(
        'ok' => false,
        'codigo' => 'error_worker_transcripcion'
    ), 2);
}

?>

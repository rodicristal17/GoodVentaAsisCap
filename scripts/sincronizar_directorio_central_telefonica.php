<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('America/Asuncion');
ini_set('display_errors', '0');

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_directorio_helper.php';

$soloLectura = in_array('--dry-run', $argv, true);
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    fwrite(STDOUT, "Sincroniza el directorio de extensiones de Issabel.\n");
    fwrite(STDOUT, "Opciones: --dry-run\n");
    exit(0);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDOUT, json_encode(array(
        'ok' => false,
        'codigo' => 'conexion_telar_no_disponible'
    )).PHP_EOL);
    exit(2);
}

$bloqueo = false;
try {
    $resultadoBloqueo = $mysqli->query(
        "SELECT GET_LOCK('telar_central_telefonica_directorio',0) adquirido"
    );
    $filaBloqueo = $resultadoBloqueo ? $resultadoBloqueo->fetch_assoc() : null;
    $bloqueo = $filaBloqueo && intval($filaBloqueo['adquirido']) === 1;
    if (!$bloqueo) {
        fwrite(STDOUT, json_encode(array(
            'ok' => true,
            'codigo' => 'directorio_en_curso'
        )).PHP_EOL);
        $mysqli->close();
        exit(0);
    }
    $configBase = centralTelefonicaCargarConfiguracionIssabel();
    $resultado = centralTelefonicaDirectorioSincronizar(
        $mysqli,
        $configBase,
        $soloLectura
    );
    $mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
    $bloqueo = false;
    $mysqli->close();
    fwrite(STDOUT, json_encode(array(
        'ok' => true,
        'codigo' => $soloLectura ? 'directorio_lectura_validada' : 'directorio_sincronizado',
        'resultado' => $resultado
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit(0);
} catch (CentralTelefonicaDirectorioExcepcion $e) {
    if ($bloqueo) {
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
    }
    @$mysqli->close();
    fwrite(STDOUT, json_encode(array(
        'ok' => false,
        'codigo' => $e->codigoOperacion
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    exit(2);
} catch (Exception $e) {
    error_log('[CentralTelefonicaDirectorio] '.get_class($e).': '.$e->getMessage());
    if ($bloqueo) {
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
    }
    @$mysqli->close();
    fwrite(STDOUT, json_encode(array(
        'ok' => false,
        'codigo' => 'error_directorio'
    )).PHP_EOL);
    exit(2);
} catch (Throwable $e) {
    error_log('[CentralTelefonicaDirectorio] '.get_class($e).': '.$e->getMessage());
    if ($bloqueo) {
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
    }
    @$mysqli->close();
    fwrite(STDOUT, json_encode(array(
        'ok' => false,
        'codigo' => 'error_directorio'
    )).PHP_EOL);
    exit(2);
}

?>

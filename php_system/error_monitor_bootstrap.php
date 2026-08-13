<?php

if (PHP_VERSION_ID >= 80000 && is_file(__DIR__.'/../php_compat/encoding.php')) {
    require_once(__DIR__.'/../php_compat/encoding.php');
}

if (!defined('TELAR_ERROR_MONITOR_ACTIVO')) {
    define('TELAR_ERROR_MONITOR_ACTIVO', true);
    date_default_timezone_set('America/Asuncion');

    $telarErrorMarker = '/var/log/telar/.ultima_limpieza';
    if (!is_file($telarErrorMarker) || @filemtime($telarErrorMarker) < time() - 86400) {
        foreach ((array)glob('/var/log/telar/telar-errors-*.log') as $telarErrorArchivo) {
            if (@filemtime($telarErrorArchivo) < time() - 30 * 86400) @unlink($telarErrorArchivo);
        }
        @touch($telarErrorMarker);
    }

    function telar_error_monitor_tipo($tipo)
    {
        $mapa = array(
            E_ERROR => 'fatal', E_PARSE => 'parse', E_CORE_ERROR => 'core',
            E_COMPILE_ERROR => 'compile', E_USER_ERROR => 'user_error',
            E_WARNING => 'warning', E_USER_WARNING => 'user_warning',
            E_NOTICE => 'notice', E_USER_NOTICE => 'user_notice',
            E_DEPRECATED => 'deprecated', E_USER_DEPRECATED => 'user_deprecated'
        );
        return isset($mapa[$tipo]) ? $mapa[$tipo] : 'php_error';
    }

    function telar_error_monitor_limpiar($valor, $limite)
    {
        $valor = preg_replace('/[\r\n\t]+/', ' ', (string)$valor);
        $valor = preg_replace('/(?i)(password|passu|token|authorization|cookie|privatekey)\s*[=:]\s*[^\s,;]+/', '$1=[PROTEGIDO]', $valor);
        return mb_substr($valor, 0, $limite, 'UTF-8');
    }

    function telar_error_monitor_registrar($tipo, $mensaje, $archivo, $linea)
    {
        static $registrando = false;
        if ($registrando) return;
        $registrando = true;
        try {
            $directorio = '/var/log/telar';
            if (!is_dir($directorio)) @mkdir($directorio, 0770, true);
            $uri = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : 'cli';
            $registro = array(
                'id' => 'TELAR-'.date('Ymd-His').'-'.strtoupper(substr(hash('sha256', microtime(true).mt_rand()), 0, 6)),
                'fecha' => date('c'),
                'nivel' => telar_error_monitor_tipo($tipo),
                'mensaje' => telar_error_monitor_limpiar($mensaje, 800),
                'archivo' => telar_error_monitor_limpiar(str_replace('/var/www/html/', '', (string)$archivo), 220),
                'linea' => (int)$linea,
                'metodo' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI',
                'ruta' => telar_error_monitor_limpiar($uri, 240),
                'ip_hash' => isset($_SERVER['REMOTE_ADDR']) ? substr(hash('sha256', $_SERVER['REMOTE_ADDR']), 0, 12) : '',
                'php' => PHP_VERSION
            );
            @file_put_contents($directorio.'/telar-errors-'.date('Y-m-d').'.log', json_encode($registro, JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (Throwable $ignorado) {
        }
        $registrando = false;
    }

    set_error_handler(function ($tipo, $mensaje, $archivo, $linea) {
        if (!(error_reporting() & $tipo)) return false;
        telar_error_monitor_registrar($tipo, $mensaje, $archivo, $linea);
        return false;
    });

    register_shutdown_function(function () {
        $error = error_get_last();
        if (!$error) return;
        if (in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
            telar_error_monitor_registrar($error['type'], $error['message'], $error['file'], $error['line']);
        }
    });
}

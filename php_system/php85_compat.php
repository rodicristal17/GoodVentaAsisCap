<?php

if (!function_exists('telar_trigger_error')) {
    /**
     * Compatibilidad PHP 7.2/8.5 para los errores fatales legacy.
     * PHP 8.4 depreco telar_trigger_error(..., E_USER_ERROR).
     */
    function telar_trigger_error($mensaje, $nivel = E_USER_NOTICE)
    {
        if ($nivel !== E_USER_ERROR) {
            return trigger_error($mensaje, $nivel);
        }

        error_log((string)$mensaje);

        if (PHP_SAPI === 'cli') {
            throw new RuntimeException((string)$mensaje);
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode(array(
            '1' => 'error',
            '2' => 'Error interno al procesar la operacion.'
        ));
        exit;
    }
}

if (!function_exists('telar_dias_en_mes')) {
    function telar_dias_en_mes($mes, $anio)
    {
        $mes = (int)$mes;
        $anio = (int)$anio;
        if ($mes < 1 || $mes > 12 || $anio < 1) {
            return 0;
        }

        $fecha = DateTime::createFromFormat('!Y-n-j', $anio.'-'.$mes.'-1');
        return $fecha ? (int)$fecha->format('t') : 0;
    }
}

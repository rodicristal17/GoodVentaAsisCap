<?php

/**
 * Compatibilidad del contrato latin1 historico de GoodVenta para PHP 8.2+.
 * En la imagen PHP 8 se deshabilitan las funciones nativas obsoletas antes
 * de cargar este archivo. En PHP 7 las funciones nativas permanecen intactas.
 */

if (!function_exists('utf8_encode')) {
    function utf8_encode($valor)
    {
        return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
    }
}

if (!function_exists('utf8_decode')) {
    function utf8_decode($valor)
    {
        return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
    }
}

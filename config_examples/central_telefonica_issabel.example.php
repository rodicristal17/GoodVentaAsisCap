<?php

/**
 * Ejemplo de configuracion privada de Central Telefonica.
 *
 * Copiar fuera del arbol publico a:
 * C:\wamp64\private\GoodVentaAsisCap\central_telefonica_issabel.php
 *
 * No guardar credenciales reales en Git ni en el vault.
 */
return array(
    'host' => '10.220.100.230',
    'port' => 3306,
    'database' => 'asteriskcdrdb',
    'table' => 'cdr',
    'user' => 'telar_cdr_readonly',
    'password' => 'REEMPLAZAR_EN_ARCHIVO_PRIVADO',
    'charset' => 'utf8',
    'initial_days' => 30,
    'overlap_minutes' => 10,
    'batch_limit' => 5000,

    // Ajustar estas reglas despues de confirmar extensiones, contextos y trunks.
    'extension_patterns' => array('/^[1-9][0-9]{2,4}$/'),
    'service_patterns' => array('/^[*#]/', '/^(s|h|i|t)$/i'),
    'inbound_context_patterns' => array('/from-trunk/i', '/from-pstn/i', '/from-gsm/i'),
    'outbound_context_patterns' => array('/from-internal/i', '/outbound/i'),
    'trunk_patterns' => array('/trunk/i', '/gateway/i', '/gsm/i', '/to-gw/i', '/from-gw/i')
);

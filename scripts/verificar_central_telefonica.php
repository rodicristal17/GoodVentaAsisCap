<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/central_telefonica_sync_helper.php';

$aprobadas = 0;
$fallidas = 0;

function centralTelefonicaPrueba($condicion, $mensaje)
{
    global $aprobadas, $fallidas;
    if ($condicion) {
        $aprobadas++;
        fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
        return;
    }
    $fallidas++;
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
}

$config = centralTelefonicaCargarConfiguracionIssabel();

centralTelefonicaPrueba(
    centralTelefonicaSyncNormalizarLimiteLectura(10) === 10
        && centralTelefonicaSyncNormalizarLimiteLectura(0) === 1
        && centralTelefonicaSyncNormalizarLimiteLectura(25000) === 20000,
    'Respeta lotes pequenos solicitados y conserva el limite maximo seguro.'
);

centralTelefonicaPrueba(
    centralTelefonicaNormalizarTelefono('0981123456') === '+595981123456',
    'Normaliza un celular paraguayo con cero inicial.'
);
centralTelefonicaPrueba(
    centralTelefonicaNormalizarTelefono('595981123456') === '+595981123456',
    'Reconoce un celular paraguayo con codigo de pais.'
);
centralTelefonicaPrueba(
    centralTelefonicaNormalizarTelefono('+595 981 123456') === '+595981123456',
    'Conserva equivalencia de un numero internacional formateado.'
);
centralTelefonicaPrueba(
    centralTelefonicaMascararTelefono('+595981123456') === '+5959*****456',
    'Enmascara telefonos cuando falta el permiso de visualizacion completa.'
);

$saliente = array(array(
    'cdr_uniqueid' => '1.1',
    'cdr_linkedid' => '1.1',
    'fecha_inicio' => '2026-08-12 10:00:00',
    'origen_original' => '1009',
    'destino_original' => '0981123456',
    'contexto' => 'from-internal',
    'canal' => 'PJSIP/1009-0001',
    'canal_destino' => 'SIP/to-gw-gsm-0002',
    'disposicion' => 'ANSWERED',
    'duracion_seg' => 100,
    'hablado_seg' => 82,
    'grabacion_disponible' => 1,
    'grabacion_referencia' => 'audio.wav'
));
$clasificacionSaliente = centralTelefonicaClasificarSegmentos($saliente, $config);
centralTelefonicaPrueba(
    $clasificacionSaliente['tipo'] === 'saliente_externa',
    'Clasifica extension hacia trunk como llamada saliente externa.'
);

$entrante = array(array(
    'cdr_uniqueid' => '2.1',
    'cdr_linkedid' => '2.1',
    'fecha_inicio' => '2026-08-12 10:05:00',
    'origen_original' => '0972123456',
    'destino_original' => '1007',
    'contexto' => 'from-trunk-gsm',
    'canal' => 'SIP/from-gw-gsm-0003',
    'canal_destino' => 'PJSIP/1007-0004',
    'disposicion' => 'NO ANSWER',
    'duracion_seg' => 24,
    'hablado_seg' => 0,
    'grabacion_disponible' => 0,
    'grabacion_referencia' => ''
));
$clasificacionEntrante = centralTelefonicaClasificarSegmentos($entrante, $config);
centralTelefonicaPrueba(
    $clasificacionEntrante['tipo'] === 'entrante_externa',
    'Clasifica trunk hacia extension como llamada entrante externa.'
);

$interna = array(array(
    'cdr_uniqueid' => '3.1',
    'cdr_linkedid' => '3.1',
    'fecha_inicio' => '2026-08-12 10:10:00',
    'origen_original' => '1005',
    'destino_original' => '1009',
    'contexto' => 'from-internal',
    'canal' => 'PJSIP/1005-0005',
    'canal_destino' => 'PJSIP/1009-0006',
    'disposicion' => 'ANSWERED',
    'duracion_seg' => 45,
    'hablado_seg' => 40,
    'grabacion_disponible' => 0,
    'grabacion_referencia' => ''
));
$clasificacionInterna = centralTelefonicaClasificarSegmentos($interna, $config);
centralTelefonicaPrueba(
    $clasificacionInterna['tipo'] === 'interna',
    'Clasifica extension a extension como llamada interna.'
);

$segmentoTransferido = $entrante[0];
$segmentoTransferido['cdr_uniqueid'] = '2.2';
$segmentoTransferido['destino_original'] = '1010';
$segmentoTransferido['canal_destino'] = 'PJSIP/1010-0007';
$segmentoTransferido['disposicion'] = 'ANSWERED';
$segmentoTransferido['duracion_seg'] = 75;
$segmentoTransferido['hablado_seg'] = 61;
$consolidada = centralTelefonicaConstruirConsolidado(
    array($entrante[0], $segmentoTransferido),
    $config
);
centralTelefonicaPrueba(
    $consolidada['cantidad_segmentos'] === 2
        && $consolidada['estado'] === 'contestada'
        && $consolidada['tipo'] === 'entrante_externa',
    'Consolida dos segmentos del mismo linkedid en una llamada contestada.'
);
centralTelefonicaPrueba(
    centralTelefonicaClaveGrupo($entrante[0]) === centralTelefonicaClaveGrupo($segmentoTransferido),
    'Dos segmentos con el mismo linkedid comparten la clave de grupo.'
);
centralTelefonicaPrueba(
    centralTelefonicaClaveSegmento($entrante[0]) === centralTelefonicaClaveSegmento($entrante[0]),
    'La clave idempotente de un segmento es estable.'
);
centralTelefonicaPrueba(
    centralTelefonicaNormalizarDisposicion('CONGESTION') === 'congestion'
        && centralTelefonicaNormalizarDisposicion('BUSY') === 'ocupada'
        && centralTelefonicaNormalizarDisposicion('NO ANSWER') === 'no_contestada',
    'Normaliza los estados principales del CDR.'
);

$rutaCompose = dirname(__DIR__).'/deploy/production/compose.yml';
$compose = is_readable($rutaCompose) ? file_get_contents($rutaCompose) : '';
centralTelefonicaPrueba(
    strpos($compose, 'central-telefonica-sync:') !== false
        && strpos($compose, 'TELAR_CENTRAL_SYNC_INTERVAL_SECONDS:-300') !== false
        && strpos($compose, 'sincronizar_central_telefonica.php') !== false
        && strpos($compose, 'if [ "$$interval" -lt 60 ]') !== false,
    'El servicio programado ejecuta el sincronizador cada cinco minutos y rechaza intervalos inseguros.'
);
centralTelefonicaPrueba(
    strpos($compose, '../../:/var/www/html:ro') !== false
        && strpos($compose, 'read_only: true') !== false
        && strpos($compose, 'no-new-privileges:true') !== false
        && strpos($compose, 'max-size: 10m') !== false
        && strpos($compose, 'max-file: "3"') !== false,
    'El sincronizador queda aislado, con codigo de solo lectura y rotacion de logs.'
);

$rutaJs = dirname(__DIR__).'/js_system/central_telefonica.js';
$rutaCss = dirname(__DIR__).'/css_system/central_telefonica.css';
$rutaInicio = dirname(__DIR__).'/system/inicio.html';
$js = is_readable($rutaJs) ? file_get_contents($rutaJs) : '';
$css = is_readable($rutaCss) ? file_get_contents($rutaCss) : '';
$inicio = is_readable($rutaInicio) ? file_get_contents($rutaInicio) : '';

centralTelefonicaPrueba(
    strpos($js, "data-central-action='toggle-summary'") !== false
        && strpos($js, "aria-controls='centralTelefonicaSummaryCards'") !== false
        && strpos($js, 'centralTelefonicaCompact_total') !== false
        && strpos($js, 'centralTelefonicaCompact_no_contestadas') !== false
        && strpos($js, 'centralTelefonicaCompact_tiempo_hablado_texto') !== false,
    'Los indicadores ofrecen resumen compacto y control accesible para mostrar el detalle.'
);
centralTelefonicaPrueba(
    strpos($js, 'SUMMARY_PREFERENCE_KEY') !== false
        && strpos($js, 'localStorage.setItem') !== false
        && strpos($js, 'setSummaryCollapsed(readSummaryPreference(), false)') !== false,
    'La preferencia de indicadores se conserva y el estado inicial permanece contraido.'
);
centralTelefonicaPrueba(
    strpos($js, "data-central-action='toggle-filters'") !== false
        && strpos($js, 'collapseFiltersOnCompactViewport') !== false
        && strpos($js, 'centralTelefonicaFiltersToggleText') !== false
        && strpos($css, 'grid-template-columns: minmax(112px, .82fr)') !== false
        && strpos($css, '@media (max-width: 1100px)') !== false
        && strpos($css, '.central-telefonica-filters-panel--expanded .central-telefonica-filters') !== false,
    'Los filtros usan una sola barra en escritorio y un panel colapsable en tablet.'
);
centralTelefonicaPrueba(
    strpos($inicio, 'central_telefonica.css?v=20260817-02') !== false
        && strpos($inicio, 'central_telefonica.js?x=20260817-02') !== false,
    'La pantalla principal publica la version compacta sin reutilizar recursos en cache.'
);

fwrite(STDOUT, 'Aprobadas: '.$aprobadas.' | Fallidas: '.$fallidas.PHP_EOL);
exit($fallidas > 0 ? 1 : 0);

?>

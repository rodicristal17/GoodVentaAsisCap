<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/central_telefonica_ami_helper.php';

$raiz = dirname(__DIR__);
$aprobadas = 0;
$fallidas = 0;

function centralTelefonicaNivel1Prueba($condicion, $mensaje)
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

$migracion = file_get_contents($raiz.'/actualizacion_20082026_central_telefonica_nivel1.sql');
$rollback = file_get_contents($raiz.'/actualizacion_20082026_central_telefonica_nivel1_rollback.sql');
$endpoint = file_get_contents($raiz.'/php_system/abmCentralTelefonicaOperacion.php');
$helper = file_get_contents($raiz.'/php_system/central_telefonica_operacion_helper.php');
$ami = file_get_contents($raiz.'/php_system/central_telefonica_ami_helper.php');
$worker = file_get_contents($raiz.'/scripts/procesar_central_telefonica_tiempo_real.php');
$js = file_get_contents($raiz.'/js_system/central_telefonica_operacion.js');
$jsHistorico = file_get_contents($raiz.'/js_system/central_telefonica.js');
$css = file_get_contents($raiz.'/css_system/central_telefonica.css');
$inicio = file_get_contents($raiz.'/system/inicio.html');
$compose = file_get_contents($raiz.'/deploy/production/compose.yml');

centralTelefonicaNivel1Prueba(
    substr_count($migracion, 'CREATE TABLE IF NOT EXISTS central_telefonica_') === 5,
    'La migracion crea cinco estructuras aditivas sin tocar el CDR.'
);
centralTelefonicaNivel1Prueba(
    strpos($rollback, "estado='deshabilitado'") !== false
        && strpos($rollback, "permission_key='VERCENTRALTELEFONICA'") !== false
        && strpos($rollback, 'DROP TABLE') === false,
    'La reversion deshabilita la capa nueva y conserva la auditoria.'
);
centralTelefonicaNivel1Prueba(
    strpos($endpoint, 'centralTelefonicaOperacionContextoUsuario') !== false
        && strpos($endpoint, 'VERCENTRALTELEFONICA') === false,
    'Las llamadas requieren usuario activo y no un permiso funcional nuevo.'
);
centralTelefonicaNivel1Prueba(
    strpos($helper, 'centralTelefonicaOperacionSaldoSql') !== false
        && strpos($helper, 'detalle_venta') === false
        && strpos($helper, 'producto') === false,
    'La tarjeta entrante contiene resumen financiero y no datos clinicos.'
);
centralTelefonicaNivel1Prueba(
    strpos($ami, "'read'") === false
        && strpos($ami, "'Command'") === false
        && strpos($ami, 'TELAR_ISSABEL_AMI_EVENT_SECRET_FILE') !== false
        && strpos($ami, 'TELAR_ISSABEL_AMI_ORIGINATE_SECRET_FILE') !== false,
    'El cliente AMI no ejecuta comandos y admite secretos fuera del repositorio.'
);
centralTelefonicaNivel1Prueba(
    strpos($worker, 'centralTelefonicaAmiOriginar') !== false
        && strpos($worker, 'centralTelefonicaLiveGuardarEvento') !== false
        && strpos($worker, 'GET_LOCK') !== false,
    'El worker procesa origenacion, eventos y evita instancias duplicadas.'
);
centralTelefonicaNivel1Prueba(
    strpos($helper, 'COUNT(DISTINCT table_name) total') !== false
        && substr_count($helper, 'centralTelefonicaTablaExiste(') === 0,
    'La comprobacion operativa usa una sola consulta de estructura por solicitud.'
);
centralTelefonicaNivel1Prueba(
    strpos($worker, 'centralTelefonicaLiveCerrarSolicitudesInterrumpidas') !== false
        && strpos($worker, 'evitar una llamada duplicada') !== false,
    'Una solicitud interrumpida queda trazada y no se reintenta automaticamente.'
);
centralTelefonicaNivel1Prueba(
    strpos($js, 'Solo información administrativa y financiera') !== false
        && strpos($js, 'data-operational-action=\'call\'') !== false
        && strpos($js, 'centralTelefonicaInboundPopup') !== false,
    'La interfaz ofrece llamar y un aviso entrante no clinico.'
);
centralTelefonicaNivel1Prueba(
    strpos($jsHistorico, 'hasHistoryPermission') !== false
        && strpos($jsHistorico, 'data-central-history') !== false,
    'El historial conserva una barrera separada de la gestion operativa.'
);
centralTelefonicaNivel1Prueba(
    strpos($css, '.central-telefonica-inbound') !== false
        && strpos($css, '@media (max-width: 800px)') !== false,
    'La nueva interfaz posee estilos aislados y adaptacion para tablet.'
);
centralTelefonicaNivel1Prueba(
    strpos($inicio, 'central_telefonica_operacion.js?x=20260821-01') !== false
        && strpos($compose, 'central-telefonica-live:') !== false
        && strpos($compose, 'issabel_ami_event_secret') !== false,
    'La pantalla y el servicio aislado estan incorporados al despliegue.'
);

$fixture = array(
    'event' => 'DialBegin',
    'channel' => 'SIP/trunk-0001',
    'destchannel' => 'PJSIP/1005-0002',
    'calleridnum' => '0981000000',
    'linkedid' => 'fixture.1',
    'uniqueid' => 'fixture.2'
);
$config = array('extension_patterns' => array('/^[1-9][0-9]{2,4}$/'));
$evento = centralTelefonicaAmiAnalizarEvento($fixture, $config);
centralTelefonicaNivel1Prueba(
    $evento && $evento['direccion'] === 'entrante'
        && $evento['extension'] === '1005'
        && $evento['telefono'] === '+595981000000',
    'El analizador reconoce una llamada entrante hacia una extension.'
);

$fixtureSalida = array(
    'event' => 'DialBegin',
    'channel' => 'Local/1005@from-internal-0001;1',
    'destchannel' => 'SIP/trunk-0002',
    'dialstring' => '0981000000',
    'linkedid' => 'fixture.3',
    'uniqueid' => 'fixture.4'
);
$eventoSalida = centralTelefonicaAmiAnalizarEvento($fixtureSalida, $config);
centralTelefonicaNivel1Prueba(
    $eventoSalida && $eventoSalida['direccion'] === 'saliente'
        && $eventoSalida['extension'] === '1005',
    'El analizador reconoce la llamada saliente iniciada desde Telar.'
);

fwrite(STDOUT, 'Aprobadas: '.$aprobadas.'; Fallidas: '.$fallidas.PHP_EOL);
exit($fallidas === 0 ? 0 : 1);

?>

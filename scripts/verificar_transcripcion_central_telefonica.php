<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/central_telefonica_transcripcion_helper.php';

$aprobadas = 0;
$fallidas = 0;
function centralTelefonicaTranscripcionPrueba($condicion, $mensaje)
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

centralTelefonicaTranscripcionPrueba(
    centralTelefonicaTranscripcionValidarReferencia('out-123.45.wav') === 'out-123.45.wav'
        && centralTelefonicaTranscripcionValidarReferencia('in_test-1.MP3') === 'in_test-1.MP3',
    'Admite unicamente nombres de grabacion con extensiones soportadas.'
);
centralTelefonicaTranscripcionPrueba(
    centralTelefonicaTranscripcionValidarReferencia('../audio.wav') === ''
        && centralTelefonicaTranscripcionValidarReferencia('2026/08/audio.wav') === ''
        && centralTelefonicaTranscripcionValidarReferencia('audio.php') === '',
    'Rechaza traversal, rutas y formatos no admitidos antes de invocar SSH.'
);

$config = array(
    'openai_input_usd_million' => 2.50,
    'openai_output_usd_million' => 10.00
);
$respuesta = array(
    'duration' => 8.4,
    'text' => 'Buenas tardes. Queria consultar por mi turno.',
    'segments' => array(
        array('id' => '1', 'start' => 0, 'end' => 3.1, 'speaker' => 'A', 'text' => 'Buenas tardes.'),
        array('id' => '2', 'start' => 3.2, 'end' => 8.4, 'speaker' => 'B', 'text' => 'Queria consultar por mi turno.')
    ),
    'usage' => array('input_tokens' => 1000, 'output_tokens' => 100, 'total_tokens' => 1100)
);
$normalizada = centralTelefonicaTranscripcionNormalizarRespuesta(
    $respuesta,
    'saliente_externa',
    $config
);
centralTelefonicaTranscripcionPrueba(
    count($normalizada['segmentos']) === 2
        && $normalizada['roles']['A'] === 'funcionario'
        && $normalizada['roles']['B'] === 'paciente',
    'Conserva la diarizacion y propone roles orientativos para una llamada saliente.'
);
centralTelefonicaTranscripcionPrueba(
    abs($normalizada['costo_estimado_usd'] - 0.0035) < 0.00000001
        && $normalizada['total_tokens'] === 1100,
    'Calcula el costo estimado desde el uso informado por OpenAI.'
);
$rolesEntrante = centralTelefonicaTranscripcionRolesSugeridos(
    $normalizada['segmentos'],
    'entrante_externa'
);
$rolesInterna = centralTelefonicaTranscripcionRolesSugeridos(
    $normalizada['segmentos'],
    'interna'
);
centralTelefonicaTranscripcionPrueba(
    $rolesEntrante['A'] === 'paciente' && $rolesEntrante['B'] === 'funcionario'
        && $rolesInterna['A'] === 'funcionario' && $rolesInterna['B'] === 'funcionario',
    'Distingue las sugerencias de rol para llamadas entrantes e internas.'
);

$raiz = dirname(__DIR__);
$helper = file_get_contents($raiz.'/php_system/central_telefonica_transcripcion_helper.php');
$endpoint = file_get_contents($raiz.'/php_system/abmCentralTelefonica.php');
$js = file_get_contents($raiz.'/js_system/central_telefonica.js');
$compose = file_get_contents($raiz.'/deploy/production/compose.yml');
$dockerfile = file_get_contents($raiz.'/deploy/production/Dockerfile');
$scriptIssabel = file_get_contents($raiz.'/config_examples/issabel_telar_read_recording.sh');
$migracion = file_get_contents($raiz.'/actualizacion_17082026_central_telefonica_transcripcion_openai.sql');
$rollback = file_get_contents($raiz.'/actualizacion_17082026_central_telefonica_transcripcion_openai_rollback.sql');

centralTelefonicaTranscripcionPrueba(
    strpos($helper, "curl_init('https://api.openai.com/v1/audio/transcriptions')") !== false
        && strpos($helper, "'response_format' => 'diarized_json'") !== false
        && strpos($helper, "'chunking_strategy' => 'auto'") !== false
        && strpos($helper, "' -- '.escapeshellarg") === false
        && strpos($helper, 'if (PHP_VERSION_ID < 80500)') !== false
        && strpos($helper, "finally {") !== false
        && strpos($helper, '@unlink($temporal)') !== false,
    'El worker evita la obsolescencia PHP 8.5, usa SSH limpio y elimina el temporal.'
);
centralTelefonicaTranscripcionPrueba(
    strpos($endpoint, "case 'solicitar_transcripcion':") !== false
        && strpos($endpoint, "case 'actualizar_roles_transcripcion':") !== false
        && strpos($endpoint, 'TRANSCRIBIRLLAMADACENTRALTELEFONICA') !== false
        && strpos($endpoint, '$cuentaTranscripcionProtegida') !== false
        && strpos($endpoint, "'CARLOS FARAONE CLINIDENT'") !== false
        && strpos($js, "data-central-action='transcribe'") !== false
        && strpos($js, "data-central-action='save-speaker-roles'") !== false,
    'La interfaz y el endpoint cubren el flujo y aplican la identidad protegida de Carlos.'
);

preg_match('/^  web:\R(.*?)(?=^  [a-z0-9-]+:\R)/ms', $compose, $coincidenciaWeb);
preg_match('/^  central-telefonica-transcription:\R(.*?)(?=^  [a-z0-9-]+:\R)/ms', $compose, $coincidenciaWorker);
$web = isset($coincidenciaWeb[1]) ? $coincidenciaWeb[1] : '';
$worker = isset($coincidenciaWorker[1]) ? $coincidenciaWorker[1] : '';
centralTelefonicaTranscripcionPrueba(
    $web !== '' && $worker !== ''
        && strpos($web, 'TELAR_OPENAI_API_KEY') === false
        && strpos($web, 'TELAR_ISSABEL_AUDIO_KEY_PATH') === false
        && strpos($worker, 'TELAR_OPENAI_API_KEY') !== false
        && strpos($worker, './secrets:/run/secrets:ro') !== false
        && strpos($worker, 'read_only: true') !== false
        && strpos($worker, 'tmpfs:') !== false
        && strpos($worker, 'mem_limit: 256m') !== false
        && strpos($worker, 'cpus: 0.50') !== false,
    'Las credenciales y los limites de recursos quedan aislados en el worker.'
);
centralTelefonicaTranscripcionPrueba(
    strpos($dockerfile, 'libcurl4-openssl-dev') !== false
        && strpos($dockerfile, 'openssh-client') !== false
        && strpos($dockerfile, 'curl gd mbstring mysqli pdo_mysql zip') !== false,
    'La imagen incorpora cURL de PHP y el cliente SSH necesarios.'
);
centralTelefonicaTranscripcionPrueba(
    strpos($scriptIssabel, 'SSH_ORIGINAL_COMMAND') !== false
        && strpos($scriptIssabel, '*/*|*\\\\*|*..*') !== false
        && strpos($scriptIssabel, '/var/spool/asterisk/monitor') !== false
        && strpos($scriptIssabel, 'exec /bin/cat -- "$archivo"') !== false,
    'El comando forzado de Issabel solo permite leer una grabacion validada.'
);
centralTelefonicaTranscripcionPrueba(
    substr_count($migracion, 'CREATE TABLE IF NOT EXISTS central_telefonica_transcripcion') === 3
        && strpos($migracion, "u.cod_usuario=5994") !== false
        && strpos($migracion, "LOWER(TRIM(IFNULL(u.login,'')))='cf'") !== false
        && strpos($rollback, 'DROP TABLE') === false
        && strpos($rollback, "estado='deshabilitado'") !== false,
    'La migracion es aditiva, limita el permiso a Carlos y la reversion conserva la trazabilidad.'
);

fwrite(STDOUT, 'Aprobadas: '.$aprobadas.' | Fallidas: '.$fallidas.PHP_EOL);
exit($fallidas > 0 ? 1 : 0);

?>

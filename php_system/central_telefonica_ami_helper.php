<?php

/**
 * Cliente AMI minimo para el conector de Central Telefonica.
 * Compatible con PHP 7.2. No registra secretos ni respuestas AMI completas.
 */

require_once __DIR__.'/central_telefonica_operacion_helper.php';

function centralTelefonicaAmiEntorno($nombre, $predeterminado = '')
{
    $valor = getenv($nombre);
    return $valor !== false && trim((string)$valor) !== ''
        ? trim((string)$valor) : $predeterminado;
}

function centralTelefonicaAmiSecreto($nombre, $nombreArchivo)
{
    $ruta = centralTelefonicaAmiEntorno($nombreArchivo);
    if ($ruta !== '' && is_file($ruta) && is_readable($ruta)) {
        $valor = file_get_contents($ruta);
        if ($valor !== false && trim($valor) !== '') {
            return trim($valor);
        }
    }
    return centralTelefonicaAmiEntorno($nombre);
}

function centralTelefonicaAmiConfiguracion()
{
    $configCdr = centralTelefonicaCargarConfiguracionIssabel();
    $hostPredeterminado = isset($configCdr['host']) ? (string)$configCdr['host'] : '';
    $config = array(
        'host' => centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_HOST', $hostPredeterminado),
        'port' => intval(centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_PORT', '5038')),
        'event_user' => centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_EVENT_USER'),
        'event_secret' => centralTelefonicaAmiSecreto(
            'TELAR_ISSABEL_AMI_EVENT_SECRET',
            'TELAR_ISSABEL_AMI_EVENT_SECRET_FILE'
        ),
        'originate_user' => centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_ORIGINATE_USER'),
        'originate_secret' => centralTelefonicaAmiSecreto(
            'TELAR_ISSABEL_AMI_ORIGINATE_SECRET',
            'TELAR_ISSABEL_AMI_ORIGINATE_SECRET_FILE'
        ),
        'channel_template' => centralTelefonicaAmiEntorno(
            'TELAR_ISSABEL_AMI_CHANNEL_TEMPLATE',
            'Local/{extension}@from-internal/n'
        ),
        'context' => centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_OUTBOUND_CONTEXT', 'from-internal'),
        'number_mode' => strtolower(centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_NUMBER_MODE', 'local')),
        'timeout_ms' => intval(centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_ORIGINATE_TIMEOUT_MS', '30000')),
        'connect_timeout' => intval(centralTelefonicaAmiEntorno('TELAR_ISSABEL_AMI_CONNECT_TIMEOUT', '5')),
        'extension_patterns' => isset($configCdr['extension_patterns'])
            ? $configCdr['extension_patterns'] : array('/^[1-9][0-9]{2,4}$/')
    );
    $config['port'] = max(1, min(65535, $config['port']));
    $config['timeout_ms'] = max(10000, min(60000, $config['timeout_ms']));
    $config['connect_timeout'] = max(2, min(15, $config['connect_timeout']));
    if (!in_array($config['number_mode'], array('local', 'e164'), true)) {
        $config['number_mode'] = 'local';
    }
    return $config;
}

function centralTelefonicaAmiEventosConfigurados($config)
{
    return trim((string)$config['host']) !== ''
        && trim((string)$config['event_user']) !== ''
        && trim((string)$config['event_secret']) !== '';
}

function centralTelefonicaAmiOrigenacionConfigurada($config)
{
    return trim((string)$config['host']) !== ''
        && trim((string)$config['originate_user']) !== ''
        && trim((string)$config['originate_secret']) !== ''
        && strpos((string)$config['channel_template'], '{extension}') !== false
        && trim((string)$config['context']) !== '';
}

function centralTelefonicaAmiAbrir($config, $usuario, $secreto, $eventos)
{
    $errno = 0;
    $error = '';
    $socket = @stream_socket_client(
        'tcp://'.$config['host'].':'.$config['port'],
        $errno,
        $error,
        $config['connect_timeout'],
        STREAM_CLIENT_CONNECT
    );
    if (!$socket) {
        return array('ok' => false, 'codigo' => 'conexion_ami_no_disponible', 'socket' => null);
    }
    stream_set_timeout($socket, $config['connect_timeout']);
    $saludo = fgets($socket, 1024);
    if ($saludo === false || stripos($saludo, 'Asterisk Call Manager') === false) {
        fclose($socket);
        return array('ok' => false, 'codigo' => 'saludo_ami_invalido', 'socket' => null);
    }
    $actionId = 'telar-login-'.str_replace('.', '', uniqid('', true));
    $accion = array(
        'Action' => 'Login',
        'Username' => $usuario,
        'Secret' => $secreto,
        'Events' => $eventos ? 'on' : 'off',
        'ActionID' => $actionId
    );
    if (!centralTelefonicaAmiEscribir($socket, $accion)) {
        fclose($socket);
        return array('ok' => false, 'codigo' => 'login_ami_no_enviado', 'socket' => null);
    }
    $respuesta = centralTelefonicaAmiLeerRespuesta($socket, $actionId, $config['connect_timeout']);
    if (!$respuesta || strtolower(isset($respuesta['response']) ? $respuesta['response'] : '') !== 'success') {
        fclose($socket);
        return array('ok' => false, 'codigo' => 'login_ami_rechazado', 'socket' => null);
    }
    stream_set_blocking($socket, false);
    return array('ok' => true, 'codigo' => 'ami_conectado', 'socket' => $socket);
}

function centralTelefonicaAmiEscribir($socket, $campos)
{
    $texto = '';
    foreach ($campos as $clave => $valor) {
        $clave = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$clave);
        $valor = str_replace(array("\r", "\n"), '', (string)$valor);
        if ($clave !== '') {
            $texto .= $clave.': '.$valor."\r\n";
        }
    }
    $texto .= "\r\n";
    $largo = strlen($texto);
    $enviado = 0;
    while ($enviado < $largo) {
        $escritos = @fwrite($socket, substr($texto, $enviado));
        if ($escritos === false || $escritos === 0) {
            return false;
        }
        $enviado += $escritos;
    }
    return true;
}

function centralTelefonicaAmiPaquete($texto)
{
    $salida = array();
    foreach (preg_split('/\r?\n/', trim((string)$texto)) as $linea) {
        $posicion = strpos($linea, ':');
        if ($posicion === false) {
            continue;
        }
        $clave = strtolower(trim(substr($linea, 0, $posicion)));
        $valor = trim(substr($linea, $posicion + 1));
        if ($clave !== '') {
            $salida[$clave] = $valor;
        }
    }
    return $salida;
}

function centralTelefonicaAmiLeerRespuesta($socket, $actionId, $timeout)
{
    $limite = microtime(true) + max(1, intval($timeout));
    $buffer = '';
    stream_set_blocking($socket, true);
    while (microtime(true) < $limite) {
        $linea = fgets($socket, 4096);
        if ($linea === false) {
            $meta = stream_get_meta_data($socket);
            if (!empty($meta['timed_out']) || feof($socket)) {
                break;
            }
            usleep(20000);
            continue;
        }
        $buffer .= $linea;
        while (($posicion = strpos($buffer, "\r\n\r\n")) !== false) {
            $texto = substr($buffer, 0, $posicion);
            $buffer = substr($buffer, $posicion + 4);
            $paquete = centralTelefonicaAmiPaquete($texto);
            $esRespuesta = isset($paquete['response']);
            $coincide = isset($paquete['actionid'])
                && hash_equals((string)$actionId, (string)$paquete['actionid']);
            if ($esRespuesta && ($actionId === '' || $coincide)) {
                return $paquete;
            }
        }
    }
    return null;
}

function centralTelefonicaAmiNumeroMarcacion($normalizado, $config)
{
    $normalizado = centralTelefonicaNormalizarTelefono($normalizado);
    if ($config['number_mode'] === 'e164') {
        return preg_replace('/[^0-9]/', '', $normalizado);
    }
    if (substr($normalizado, 0, 4) === '+595') {
        return '0'.substr($normalizado, 4);
    }
    return preg_replace('/[^0-9*#]/', '', $normalizado);
}

function centralTelefonicaAmiOriginar($config, $solicitud)
{
    if (!centralTelefonicaAmiOrigenacionConfigurada($config)) {
        return array('ok' => false, 'codigo' => 'origenacion_no_configurada');
    }
    $extension = preg_replace('/[^0-9]/', '', (string)$solicitud['extension']);
    $numero = centralTelefonicaAmiNumeroMarcacion($solicitud['telefono_normalizado'], $config);
    if ($extension === '' || $numero === '') {
        return array('ok' => false, 'codigo' => 'destino_invalido');
    }
    $conexion = centralTelefonicaAmiAbrir(
        $config,
        $config['originate_user'],
        $config['originate_secret'],
        false
    );
    if (!$conexion['ok']) {
        return array('ok' => false, 'codigo' => $conexion['codigo']);
    }
    $socket = $conexion['socket'];
    $actionId = 'telar-'.intval($solicitud['id_solicitud']).'-'.substr((string)$solicitud['token'], 0, 8);
    $canal = str_replace('{extension}', $extension, (string)$config['channel_template']);
    $ok = centralTelefonicaAmiEscribir($socket, array(
        'Action' => 'Originate',
        'Channel' => $canal,
        'Context' => $config['context'],
        'Exten' => $numero,
        'Priority' => '1',
        'CallerID' => 'Telar <'.$extension.'>',
        'Timeout' => $config['timeout_ms'],
        'Async' => 'true',
        'Variable' => 'TELAR_REQUEST_ID='.intval($solicitud['id_solicitud']),
        'ActionID' => $actionId
    ));
    $respuesta = $ok
        ? centralTelefonicaAmiLeerRespuesta($socket, $actionId, $config['connect_timeout'])
        : null;
    centralTelefonicaAmiEscribir($socket, array('Action' => 'Logoff'));
    fclose($socket);
    $aceptada = $respuesta
        && strtolower(isset($respuesta['response']) ? $respuesta['response'] : '') === 'success';
    return array(
        'ok' => $aceptada,
        'codigo' => $aceptada ? 'origenacion_aceptada' : 'origenacion_rechazada',
        'action_id' => $actionId
    );
}

function centralTelefonicaAmiExtraerExtensionCanal($canal, $config)
{
    $canal = (string)$canal;
    if (preg_match('/(?:PJSIP|SIP|IAX2|Local)\/([0-9]{3,5})(?:[-@\/]|$)/i', $canal, $m)) {
        return centralTelefonicaNumeroEsExtension($m[1], $config) ? $m[1] : '';
    }
    return '';
}

function centralTelefonicaAmiAnalizarEvento($evento, $config)
{
    $tipo = strtolower(isset($evento['event']) ? $evento['event'] : '');
    $permitidos = array(
        'newchannel', 'newcallerid', 'dialbegin', 'dialend',
        'newstate', 'bridgeenter', 'hangup'
    );
    if (!in_array($tipo, $permitidos, true)) {
        return null;
    }
    $extensionOrigen = centralTelefonicaAmiExtraerExtensionCanal(
        isset($evento['channel']) ? $evento['channel'] : '',
        $config
    );
    $extensionDestino = centralTelefonicaAmiExtraerExtensionCanal(
        isset($evento['destchannel']) ? $evento['destchannel'] : '',
        $config
    );
    $camposExtension = array('exten', 'connectedlinenum', 'destcalleridnum');
    foreach ($camposExtension as $campo) {
        if ($extensionDestino === '' && isset($evento[$campo])) {
            $valor = preg_replace('/[^0-9]/', '', (string)$evento[$campo]);
            if (centralTelefonicaNumeroEsExtension($valor, $config)) {
                $extensionDestino = $valor;
            }
        }
    }
    $extension = $extensionDestino !== '' ? $extensionDestino : $extensionOrigen;
    $candidatos = array('calleridnum', 'destcalleridnum', 'connectedlinenum', 'exten', 'dialstring');
    $telefono = '';
    foreach ($candidatos as $campo) {
        if (!isset($evento[$campo])) {
            continue;
        }
        $valor = centralTelefonicaNormalizarTelefono($evento[$campo]);
        $digitos = preg_replace('/[^0-9]/', '', $valor);
        if ($valor !== '' && strlen($digitos) >= 6
            && !centralTelefonicaNumeroEsExtension($digitos, $config)) {
            $telefono = $valor;
            break;
        }
    }
    if ($extension === '' || $telefono === '') {
        return null;
    }
    $direccion = $extensionDestino !== '' && $extensionOrigen === ''
        ? 'entrante' : 'saliente';
    $estado = 'detectada';
    if ($tipo === 'dialbegin' || $tipo === 'newchannel' || $tipo === 'newcallerid') {
        $estado = 'sonando';
    }
    $estadoCanal = strtolower(isset($evento['channelstatedesc']) ? $evento['channelstatedesc'] : '');
    if ($tipo === 'bridgeenter' || ($tipo === 'newstate' && $estadoCanal === 'up')) {
        $estado = 'conectada';
    }
    if ($tipo === 'hangup') {
        $estado = 'finalizada';
    }
    if ($tipo === 'dialend') {
        $resultado = strtolower(isset($evento['dialstatus']) ? $evento['dialstatus'] : '');
        if ($resultado === 'answer') {
            $estado = 'conectada';
        } elseif ($resultado === 'busy') {
            $estado = 'ocupada';
        } elseif ($resultado === 'noanswer' || $resultado === 'cancel') {
            $estado = 'no_contestada';
        } else {
            $estado = 'finalizada';
        }
    }
    $linkedid = isset($evento['linkedid']) ? (string)$evento['linkedid'] : '';
    $uniqueid = isset($evento['uniqueid']) ? (string)$evento['uniqueid'] : '';
    if ($linkedid === '') {
        $linkedid = $uniqueid;
    }
    if ($linkedid === '') {
        return null;
    }
    return array(
        'tipo_evento' => $tipo,
        'linkedid' => centralTelefonicaOperacionTexto($linkedid, 80),
        'uniqueid' => centralTelefonicaOperacionTexto($uniqueid, 80),
        'direccion' => $direccion,
        'telefono' => $telefono,
        'extension' => $extension,
        'estado' => $estado,
        'clave' => hash('sha256', $linkedid.'|'.$extension)
    );
}

?>

<?php

/**
 * Transcripcion bajo demanda de Central Telefonica.
 *
 * El navegador nunca recibe rutas de Issabel ni credenciales. El worker CLI
 * obtiene un unico audio mediante una clave SSH restringida, llama a OpenAI y
 * elimina siempre la copia temporal. Compatible con PHP 7.2.
 */

require_once __DIR__.'/central_telefonica_helper.php';

class CentralTelefonicaTranscripcionExcepcion extends Exception
{
    public $codigoOperacion;

    public function __construct($codigo, $mensaje)
    {
        parent::__construct($mensaje);
        $this->codigoOperacion = (string)$codigo;
    }
}

function centralTelefonicaTranscripcionEstructuraDisponible($mysqli)
{
    return centralTelefonicaTablaExiste($mysqli, 'central_telefonica_transcripcion')
        && centralTelefonicaTablaExiste($mysqli, 'central_telefonica_transcripcion_evento')
        && centralTelefonicaTablaExiste($mysqli, 'central_telefonica_transcripcion_servicio');
}

function centralTelefonicaTranscripcionEnv($nombre, $predeterminado = '')
{
    $valor = getenv($nombre);
    if ($valor === false || trim((string)$valor) === '') {
        return $predeterminado;
    }
    return trim((string)$valor);
}

function centralTelefonicaTranscripcionConfig()
{
    return array(
        'audio_host' => centralTelefonicaTranscripcionEnv(
            'TELAR_ISSABEL_AUDIO_HOST',
            centralTelefonicaTranscripcionEnv('TELAR_ISSABEL_DB_HOST', '')
        ),
        'audio_port' => max(1, intval(centralTelefonicaTranscripcionEnv('TELAR_ISSABEL_AUDIO_PORT', '22'))),
        'audio_user' => centralTelefonicaTranscripcionEnv('TELAR_ISSABEL_AUDIO_USER', ''),
        'audio_key_path' => centralTelefonicaTranscripcionEnv(
            'TELAR_ISSABEL_AUDIO_KEY_PATH',
            '/run/secrets/issabel_audio_ssh_key'
        ),
        'audio_known_hosts_path' => centralTelefonicaTranscripcionEnv(
            'TELAR_ISSABEL_AUDIO_KNOWN_HOSTS_PATH',
            '/run/secrets/issabel_known_hosts'
        ),
        'audio_timeout_seconds' => max(
            15,
            min(180, intval(centralTelefonicaTranscripcionEnv('TELAR_ISSABEL_AUDIO_TIMEOUT_SECONDS', '60')))
        ),
        'max_audio_bytes' => max(
            1048576,
            min(26214400, intval(centralTelefonicaTranscripcionEnv('TELAR_OPENAI_MAX_AUDIO_BYTES', '26214400')))
        ),
        'openai_api_key' => centralTelefonicaTranscripcionEnv('TELAR_OPENAI_API_KEY', ''),
        'openai_project' => centralTelefonicaTranscripcionEnv('TELAR_OPENAI_PROJECT', ''),
        'openai_organization' => centralTelefonicaTranscripcionEnv('TELAR_OPENAI_ORGANIZATION', ''),
        'openai_model' => centralTelefonicaTranscripcionEnv(
            'TELAR_OPENAI_TRANSCRIPTION_MODEL',
            'gpt-4o-transcribe-diarize'
        ),
        'openai_timeout_seconds' => max(
            60,
            min(1800, intval(centralTelefonicaTranscripcionEnv('TELAR_OPENAI_TIMEOUT_SECONDS', '900')))
        ),
        'openai_input_usd_million' => max(
            0,
            floatval(centralTelefonicaTranscripcionEnv('TELAR_OPENAI_INPUT_USD_MILLION', '2.50'))
        ),
        'openai_output_usd_million' => max(
            0,
            floatval(centralTelefonicaTranscripcionEnv('TELAR_OPENAI_OUTPUT_USD_MILLION', '10.00'))
        ),
        'language' => 'es'
    );
}

function centralTelefonicaTranscripcionConfigValida($config)
{
    return is_array($config)
        && trim((string)$config['audio_host']) !== ''
        && trim((string)$config['audio_user']) !== ''
        && is_file($config['audio_key_path'])
        && is_readable($config['audio_key_path'])
        && is_file($config['audio_known_hosts_path'])
        && is_readable($config['audio_known_hosts_path'])
        && trim((string)$config['openai_api_key']) !== ''
        && (string)$config['openai_model'] === 'gpt-4o-transcribe-diarize'
        && function_exists('curl_init')
        && function_exists('exec');
}

function centralTelefonicaTranscripcionValidarReferencia($referencia)
{
    $referencia = trim(str_replace('\\', '/', (string)$referencia));
    if ($referencia === '' || strpos($referencia, '/') !== false || strpos($referencia, '..') !== false) {
        return '';
    }
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._@+\-]{0,240}\.(wav|mp3|m4a|ogg|flac)$/i', $referencia)) {
        return '';
    }
    return $referencia;
}

function centralTelefonicaTranscripcionTextoSeguro($valor, $maximo)
{
    if (is_array($valor) || is_object($valor)) {
        return '';
    }
    $texto = trim((string)$valor);
    if (mb_strlen($texto, 'UTF-8') > intval($maximo)) {
        $texto = mb_substr($texto, 0, intval($maximo), 'UTF-8');
    }
    return $texto;
}

function centralTelefonicaTranscripcionRolesSugeridos($segmentos, $tipoLlamada)
{
    $hablantes = array();
    foreach ((array)$segmentos as $segmento) {
        $hablante = isset($segmento['speaker'])
            ? centralTelefonicaTranscripcionTextoSeguro($segmento['speaker'], 40) : '';
        if ($hablante !== '' && !in_array($hablante, $hablantes, true)) {
            $hablantes[] = $hablante;
        }
    }

    $roles = array();
    foreach ($hablantes as $indice => $hablante) {
        $rol = 'otro';
        if ($tipoLlamada === 'saliente_externa') {
            $rol = $indice === 0 ? 'funcionario' : ($indice === 1 ? 'paciente' : 'otro');
        } elseif ($tipoLlamada === 'entrante_externa') {
            $rol = $indice === 0 ? 'paciente' : ($indice === 1 ? 'funcionario' : 'otro');
        } elseif ($tipoLlamada === 'interna') {
            $rol = 'funcionario';
        }
        $roles[$hablante] = $rol;
    }
    return $roles;
}

function centralTelefonicaTranscripcionNormalizarRespuesta($respuesta, $tipoLlamada, $config)
{
    if (!is_array($respuesta)) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'respuesta_openai_invalida',
            'OpenAI no devolvio una transcripcion valida.'
        );
    }

    $texto = centralTelefonicaTranscripcionTextoSeguro(
        isset($respuesta['text']) ? $respuesta['text'] : '',
        2000000
    );
    $segmentos = array();
    $origenSegmentos = isset($respuesta['segments']) && is_array($respuesta['segments'])
        ? $respuesta['segments'] : array();
    foreach ($origenSegmentos as $indice => $segmento) {
        if (!is_array($segmento)) {
            continue;
        }
        $contenido = centralTelefonicaTranscripcionTextoSeguro(
            isset($segmento['text']) ? $segmento['text'] : '',
            20000
        );
        if ($contenido === '') {
            continue;
        }
        $hablante = centralTelefonicaTranscripcionTextoSeguro(
            isset($segmento['speaker']) ? $segmento['speaker'] : 'A',
            40
        );
        if ($hablante === '') {
            $hablante = 'A';
        }
        $inicio = max(0, floatval(isset($segmento['start']) ? $segmento['start'] : 0));
        $fin = max($inicio, floatval(isset($segmento['end']) ? $segmento['end'] : $inicio));
        $segmentos[] = array(
            'id' => centralTelefonicaTranscripcionTextoSeguro(
                isset($segmento['id']) ? $segmento['id'] : (string)$indice,
                80
            ),
            'start' => round($inicio, 3),
            'end' => round($fin, 3),
            'speaker' => $hablante,
            'text' => $contenido
        );
    }
    if ($texto === '' && count($segmentos) > 0) {
        $partes = array();
        foreach ($segmentos as $segmento) {
            $partes[] = $segmento['text'];
        }
        $texto = trim(implode(' ', $partes));
    }
    if ($texto === '') {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'transcripcion_vacia',
            'La llamada no produjo texto reconocible.'
        );
    }
    if (count($segmentos) === 0) {
        $segmentos[] = array(
            'id' => '0',
            'start' => 0,
            'end' => max(0, floatval(isset($respuesta['duration']) ? $respuesta['duration'] : 0)),
            'speaker' => 'A',
            'text' => $texto
        );
    }

    $uso = isset($respuesta['usage']) && is_array($respuesta['usage'])
        ? $respuesta['usage'] : array();
    $entrada = max(0, intval(isset($uso['input_tokens']) ? $uso['input_tokens'] : 0));
    $salida = max(0, intval(isset($uso['output_tokens']) ? $uso['output_tokens'] : 0));
    $total = max($entrada + $salida, intval(isset($uso['total_tokens']) ? $uso['total_tokens'] : 0));
    $costo = (($entrada * floatval($config['openai_input_usd_million']))
        + ($salida * floatval($config['openai_output_usd_million']))) / 1000000;

    return array(
        'texto' => $texto,
        'segmentos' => $segmentos,
        'roles' => centralTelefonicaTranscripcionRolesSugeridos($segmentos, $tipoLlamada),
        'duracion' => max(0, floatval(isset($respuesta['duration']) ? $respuesta['duration'] : 0)),
        'uso' => $uso,
        'entrada_tokens' => $entrada,
        'salida_tokens' => $salida,
        'total_tokens' => $total,
        'costo_estimado_usd' => round($costo, 8)
    );
}

function centralTelefonicaTranscripcionJson($valor)
{
    $json = json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'serializacion_invalida',
            'No se pudo conservar el resultado de la transcripcion.'
        );
    }
    return $json;
}

function centralTelefonicaTranscripcionEvento(
    $mysqli,
    $idTranscripcion,
    $estado,
    $codigo = '',
    $detalle = '',
    $actor = null
) {
    $stmt = $mysqli->prepare(
        'INSERT INTO central_telefonica_transcripcion_evento '
        .'(id_transcripcion,estado,codigo,detalle,actor_usuario,fecha_evento) '
        .'VALUES (?,?,?,?,?,NOW())'
    );
    if (!$stmt) {
        return false;
    }
    $id = intval($idTranscripcion);
    $estado = centralTelefonicaTranscripcionTextoSeguro($estado, 30);
    $codigo = centralTelefonicaTranscripcionTextoSeguro($codigo, 80);
    $detalle = centralTelefonicaTranscripcionTextoSeguro($detalle, 255);
    $actorValor = $actor === null ? null : intval($actor);
    $stmt->bind_param('isssi', $id, $estado, $codigo, $detalle, $actorValor);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centralTelefonicaTranscripcionServicio(
    $mysqli,
    $estado,
    $config,
    $codigoError = '',
    $ultimaTranscripcion = null
) {
    $stmt = $mysqli->prepare(
        'INSERT INTO central_telefonica_transcripcion_servicio '
        .'(id_servicio,estado,proveedor,modelo,ultima_actividad,ultima_transcripcion_id,codigo_error) '
        .'VALUES (1,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE estado=VALUES(estado),'
        .'proveedor=VALUES(proveedor),modelo=VALUES(modelo),ultima_actividad=VALUES(ultima_actividad),'
        .'ultima_transcripcion_id=COALESCE(VALUES(ultima_transcripcion_id),ultima_transcripcion_id),'
        .'codigo_error=VALUES(codigo_error)'
    );
    if (!$stmt) {
        return false;
    }
    $proveedor = 'openai';
    $modelo = isset($config['openai_model'])
        ? centralTelefonicaTranscripcionTextoSeguro($config['openai_model'], 80)
        : 'gpt-4o-transcribe-diarize';
    $ahora = date('Y-m-d H:i:s');
    $id = $ultimaTranscripcion === null ? null : intval($ultimaTranscripcion);
    $codigo = centralTelefonicaTranscripcionTextoSeguro($codigoError, 80);
    $stmt->bind_param('ssssis', $estado, $proveedor, $modelo, $ahora, $id, $codigo);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centralTelefonicaTranscripcionRecuperarInterrumpidas($mysqli)
{
    $estados = "'obteniendo_audio','transcribiendo'";
    $resultado = $mysqli->query(
        "SELECT id_transcripcion FROM central_telefonica_transcripcion "
        ."WHERE estado IN (".$estados.") AND fecha_inicio<DATE_SUB(NOW(),INTERVAL 30 MINUTE)"
    );
    $ids = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $ids[] = intval($fila['id_transcripcion']);
    }
    if (count($ids) === 0) {
        return 0;
    }
    $mysqli->query(
        "UPDATE central_telefonica_transcripcion SET estado='error',fecha_fin=NOW(),"
        ."codigo_error='procesamiento_interrumpido',"
        ."mensaje_error='El procesamiento anterior fue interrumpido. Puede reintentarlo.' "
        ."WHERE id_transcripcion IN (".implode(',', $ids).")"
    );
    foreach ($ids as $id) {
        centralTelefonicaTranscripcionEvento(
            $mysqli,
            $id,
            'error',
            'procesamiento_interrumpido',
            'El worker recupero una solicitud interrumpida.'
        );
    }
    return count($ids);
}

function centralTelefonicaTranscripcionTomarTrabajo($mysqli)
{
    $mysqli->begin_transaction();
    $sql = "SELECT t.id_transcripcion,t.id_llamada,l.grupo_clave,l.tipo,l.grabacion_disponible "
        ."FROM central_telefonica_transcripcion t "
        ."INNER JOIN central_telefonica_llamada l ON l.id_llamada=t.id_llamada "
        ."WHERE t.estado='en_cola' ORDER BY t.fecha_solicitud,t.id_transcripcion LIMIT 1 FOR UPDATE";
    $resultado = $mysqli->query($sql);
    $trabajo = $resultado ? $resultado->fetch_assoc() : null;
    if (!$trabajo) {
        $mysqli->commit();
        return null;
    }
    $id = intval($trabajo['id_transcripcion']);
    $stmt = $mysqli->prepare(
        "UPDATE central_telefonica_transcripcion SET estado='obteniendo_audio',"
        ."fecha_inicio=NOW(),fecha_fin=NULL,intentos=intentos+1,codigo_error=NULL,mensaje_error=NULL "
        ."WHERE id_transcripcion=? AND estado='en_cola'"
    );
    if (!$stmt) {
        $mysqli->rollback();
        throw new CentralTelefonicaTranscripcionExcepcion(
            'cola_no_disponible',
            'No se pudo reservar la solicitud pendiente.'
        );
    }
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    if (!$ok) {
        $mysqli->rollback();
        return null;
    }
    $mysqli->commit();
    $trabajo['id_transcripcion'] = $id;
    $trabajo['id_llamada'] = intval($trabajo['id_llamada']);
    centralTelefonicaTranscripcionEvento(
        $mysqli,
        $id,
        'obteniendo_audio',
        'trabajo_iniciado',
        'Solicitud tomada por el worker.'
    );
    return $trabajo;
}

function centralTelefonicaTranscripcionReferenciaGrabacion($mysqli, $trabajo)
{
    $stmt = $mysqli->prepare(
        "SELECT grabacion_referencia FROM central_telefonica_cdr_segmento "
        ."WHERE grupo_clave=? AND grabacion_disponible=1 "
        ."AND grabacion_referencia IS NOT NULL AND TRIM(grabacion_referencia)<>'' "
        ."ORDER BY hablado_seg DESC,duracion_seg DESC,id_segmento DESC LIMIT 1"
    );
    if (!$stmt) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'grabacion_no_disponible',
            'No se pudo localizar la grabacion de la llamada.'
        );
    }
    $grupo = (string)$trabajo['grupo_clave'];
    $stmt->bind_param('s', $grupo);
    $referencia = '';
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        $referencia = $fila ? (string)$fila['grabacion_referencia'] : '';
    }
    $stmt->close();
    $referencia = centralTelefonicaTranscripcionValidarReferencia($referencia);
    if ($referencia === '') {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'grabacion_no_disponible',
            'La llamada no tiene una referencia de grabacion valida.'
        );
    }
    return $referencia;
}

function centralTelefonicaTranscripcionCopiarAudio($config, $referencia, $destino)
{
    $errorTemporal = tempnam(sys_get_temp_dir(), 'cterr_');
    if ($errorTemporal === false) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'temporal_no_disponible',
            'No se pudo preparar el archivo temporal.'
        );
    }
    @chmod($errorTemporal, 0600);
    $destinoArg = escapeshellarg($destino);
    $errorArg = escapeshellarg($errorTemporal);
    $host = escapeshellarg($config['audio_user'].'@'.$config['audio_host']);
    $comando = 'umask 077; timeout '.intval($config['audio_timeout_seconds']).' '
        .'ssh -T -p '.intval($config['audio_port']).' -i '.escapeshellarg($config['audio_key_path']).' '
        .'-o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes '
        .'-o UserKnownHostsFile='.escapeshellarg($config['audio_known_hosts_path']).' '
        .'-o ConnectTimeout=10 '.$host.' '.escapeshellarg($referencia)
        .' > '.$destinoArg.' 2> '.$errorArg;
    $salida = array();
    $codigo = 1;
    exec($comando, $salida, $codigo);
    @unlink($errorTemporal);
    clearstatcache(true, $destino);
    if ($codigo !== 0 || !is_file($destino) || !is_readable($destino)) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'audio_issabel_no_disponible',
            'No se pudo obtener la grabacion protegida desde Issabel.'
        );
    }
    $tamano = filesize($destino);
    if ($tamano === false || $tamano < 44 || $tamano > intval($config['max_audio_bytes'])) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'audio_tamano_invalido',
            'La grabacion no tiene un tamano admitido para transcripcion.'
        );
    }
    @chmod($destino, 0600);
    return intval($tamano);
}

function centralTelefonicaTranscripcionMime($referencia)
{
    $extension = strtolower(pathinfo($referencia, PATHINFO_EXTENSION));
    $tipos = array(
        'wav' => 'audio/wav',
        'mp3' => 'audio/mpeg',
        'm4a' => 'audio/mp4',
        'ogg' => 'audio/ogg',
        'flac' => 'audio/flac'
    );
    return isset($tipos[$extension]) ? $tipos[$extension] : 'application/octet-stream';
}

function centralTelefonicaTranscripcionEnviarOpenAI($config, $rutaAudio, $referencia)
{
    $cabecerasRespuesta = array();
    $curl = curl_init('https://api.openai.com/v1/audio/transcriptions');
    if ($curl === false) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'openai_no_disponible',
            'No se pudo iniciar la conexion segura con OpenAI.'
        );
    }
    $cabeceras = array('Authorization: Bearer '.$config['openai_api_key']);
    if ($config['openai_project'] !== '') {
        $cabeceras[] = 'OpenAI-Project: '.$config['openai_project'];
    }
    if ($config['openai_organization'] !== '') {
        $cabeceras[] = 'OpenAI-Organization: '.$config['openai_organization'];
    }
    $archivo = curl_file_create(
        $rutaAudio,
        centralTelefonicaTranscripcionMime($referencia),
        $referencia
    );
    $formulario = array(
        'file' => $archivo,
        'model' => $config['openai_model'],
        'language' => $config['language'],
        'response_format' => 'diarized_json',
        'chunking_strategy' => 'auto'
    );
    curl_setopt_array($curl, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $formulario,
        CURLOPT_HTTPHEADER => $cabeceras,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => intval($config['openai_timeout_seconds']),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HEADERFUNCTION => function ($recurso, $linea) use (&$cabecerasRespuesta) {
            $longitud = strlen($linea);
            $partes = explode(':', $linea, 2);
            if (count($partes) === 2) {
                $cabecerasRespuesta[strtolower(trim($partes[0]))] = trim($partes[1]);
            }
            return $longitud;
        }
    ));
    if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
    }
    $cuerpo = curl_exec($curl);
    $errorCurl = curl_errno($curl);
    $estadoHttp = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    curl_close($curl);

    if ($cuerpo === false || $errorCurl !== 0) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'openai_no_disponible',
            'OpenAI no respondio dentro del tiempo esperado.'
        );
    }
    if ($estadoHttp < 200 || $estadoHttp >= 300) {
        $codigo = 'openai_solicitud_rechazada';
        $mensaje = 'OpenAI rechazo temporalmente la transcripcion.';
        if ($estadoHttp === 401 || $estadoHttp === 403) {
            $codigo = 'openai_no_autorizado';
            $mensaje = 'La credencial privada de OpenAI necesita revision.';
        } elseif ($estadoHttp === 429) {
            $codigo = 'openai_limite_temporal';
            $mensaje = 'OpenAI alcanzo un limite temporal. Puede reintentar mas tarde.';
        } elseif ($estadoHttp >= 500) {
            $codigo = 'openai_no_disponible';
            $mensaje = 'OpenAI no esta disponible temporalmente.';
        } elseif ($estadoHttp === 400 || $estadoHttp === 413 || $estadoHttp === 415) {
            $codigo = 'audio_rechazado';
            $mensaje = 'OpenAI no pudo procesar el formato o tamano de la grabacion.';
        }
        throw new CentralTelefonicaTranscripcionExcepcion($codigo, $mensaje);
    }
    $respuesta = json_decode($cuerpo, true);
    if (!is_array($respuesta)) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'respuesta_openai_invalida',
            'OpenAI devolvio una respuesta que no se pudo interpretar.'
        );
    }
    return array(
        'respuesta' => $respuesta,
        'request_id' => isset($cabecerasRespuesta['x-request-id'])
            ? centralTelefonicaTranscripcionTextoSeguro($cabecerasRespuesta['x-request-id'], 160) : ''
    );
}

function centralTelefonicaTranscripcionMarcarEstado($mysqli, $id, $estado)
{
    $stmt = $mysqli->prepare(
        'UPDATE central_telefonica_transcripcion SET estado=? WHERE id_transcripcion=?'
    );
    if (!$stmt) {
        return false;
    }
    $id = intval($id);
    $stmt->bind_param('si', $estado, $id);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        centralTelefonicaTranscripcionEvento($mysqli, $id, $estado, 'estado_actualizado', '');
    }
    return $ok;
}

function centralTelefonicaTranscripcionGuardarResultado($mysqli, $trabajo, $normalizada, $requestId)
{
    $stmt = $mysqli->prepare(
        "UPDATE central_telefonica_transcripcion SET estado='completada',fecha_fin=NOW(),"
        ."transcripcion_texto=?,segmentos_json=?,roles_hablantes_json=?,roles_fuente='sugerido',"
        ."roles_actualizados_por=NULL,roles_fecha_actualizacion=NULL,duracion_audio_seg=?,"
        ."uso_entrada_tokens=?,uso_salida_tokens=?,uso_total_tokens=?,uso_json=?,"
        ."costo_estimado_usd=?,proveedor_request_id=?,codigo_error=NULL,mensaje_error=NULL "
        ."WHERE id_transcripcion=?"
    );
    if (!$stmt) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'resultado_no_guardado',
            'No se pudo preparar el guardado de la transcripcion.'
        );
    }
    $texto = $normalizada['texto'];
    $segmentos = centralTelefonicaTranscripcionJson($normalizada['segmentos']);
    $roles = centralTelefonicaTranscripcionJson($normalizada['roles']);
    $duracion = floatval($normalizada['duracion']);
    $entrada = intval($normalizada['entrada_tokens']);
    $salida = intval($normalizada['salida_tokens']);
    $total = intval($normalizada['total_tokens']);
    $uso = centralTelefonicaTranscripcionJson($normalizada['uso']);
    $costo = floatval($normalizada['costo_estimado_usd']);
    $requestId = centralTelefonicaTranscripcionTextoSeguro($requestId, 160);
    $id = intval($trabajo['id_transcripcion']);
    $stmt->bind_param(
        'sssdiiisdsi',
        $texto,
        $segmentos,
        $roles,
        $duracion,
        $entrada,
        $salida,
        $total,
        $uso,
        $costo,
        $requestId,
        $id
    );
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'resultado_no_guardado',
            'No se pudo conservar la transcripcion en Telar.'
        );
    }
    centralTelefonicaTranscripcionEvento(
        $mysqli,
        $id,
        'completada',
        'transcripcion_completada',
        'Transcripcion diarizada guardada correctamente.'
    );
}

function centralTelefonicaTranscripcionGuardarError($mysqli, $id, $codigo, $mensaje)
{
    $stmt = $mysqli->prepare(
        "UPDATE central_telefonica_transcripcion SET estado='error',fecha_fin=NOW(),"
        ."codigo_error=?,mensaje_error=? WHERE id_transcripcion=?"
    );
    if (!$stmt) {
        return false;
    }
    $codigo = centralTelefonicaTranscripcionTextoSeguro($codigo, 80);
    $mensaje = centralTelefonicaTranscripcionTextoSeguro($mensaje, 255);
    $id = intval($id);
    $stmt->bind_param('ssi', $codigo, $mensaje, $id);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        centralTelefonicaTranscripcionEvento($mysqli, $id, 'error', $codigo, $mensaje);
    }
    return $ok;
}

function centralTelefonicaTranscripcionProcesarSiguiente($mysqli, $config)
{
    if (!centralTelefonicaTranscripcionEstructuraDisponible($mysqli)) {
        throw new CentralTelefonicaTranscripcionExcepcion(
            'migracion_pendiente',
            'La migracion de transcripciones todavia no esta aplicada.'
        );
    }
    if (!centralTelefonicaTranscripcionConfigValida($config)) {
        centralTelefonicaTranscripcionServicio($mysqli, 'sin_configurar', $config, 'configuracion_incompleta');
        throw new CentralTelefonicaTranscripcionExcepcion(
            'configuracion_incompleta',
            'El worker de transcripcion necesita configuracion privada.'
        );
    }

    centralTelefonicaTranscripcionServicio($mysqli, 'disponible', $config, '');
    centralTelefonicaTranscripcionRecuperarInterrumpidas($mysqli);
    $trabajo = centralTelefonicaTranscripcionTomarTrabajo($mysqli);
    if (!$trabajo) {
        return array('procesado' => false, 'codigo' => 'sin_trabajos');
    }

    $temporal = tempnam(sys_get_temp_dir(), 'ctaudio_');
    if ($temporal === false) {
        centralTelefonicaTranscripcionGuardarError(
            $mysqli,
            $trabajo['id_transcripcion'],
            'temporal_no_disponible',
            'No se pudo preparar el archivo temporal.'
        );
        return array('procesado' => true, 'ok' => false, 'codigo' => 'temporal_no_disponible');
    }
    @chmod($temporal, 0600);

    try {
        $referencia = centralTelefonicaTranscripcionReferenciaGrabacion($mysqli, $trabajo);
        centralTelefonicaTranscripcionCopiarAudio($config, $referencia, $temporal);
        centralTelefonicaTranscripcionMarcarEstado(
            $mysqli,
            $trabajo['id_transcripcion'],
            'transcribiendo'
        );
        $openai = centralTelefonicaTranscripcionEnviarOpenAI($config, $temporal, $referencia);
        $normalizada = centralTelefonicaTranscripcionNormalizarRespuesta(
            $openai['respuesta'],
            $trabajo['tipo'],
            $config
        );
        centralTelefonicaTranscripcionGuardarResultado(
            $mysqli,
            $trabajo,
            $normalizada,
            $openai['request_id']
        );
        centralTelefonicaTranscripcionServicio(
            $mysqli,
            'disponible',
            $config,
            '',
            $trabajo['id_transcripcion']
        );
        return array(
            'procesado' => true,
            'ok' => true,
            'codigo' => 'transcripcion_completada',
            'id_transcripcion' => intval($trabajo['id_transcripcion'])
        );
    } catch (CentralTelefonicaTranscripcionExcepcion $e) {
        centralTelefonicaTranscripcionGuardarError(
            $mysqli,
            $trabajo['id_transcripcion'],
            $e->codigoOperacion,
            $e->getMessage()
        );
        $estadoServicio = in_array(
            $e->codigoOperacion,
            array('openai_no_autorizado', 'configuracion_incompleta'),
            true
        ) ? 'error' : 'disponible';
        centralTelefonicaTranscripcionServicio(
            $mysqli,
            $estadoServicio,
            $config,
            $e->codigoOperacion,
            $trabajo['id_transcripcion']
        );
        return array(
            'procesado' => true,
            'ok' => false,
            'codigo' => $e->codigoOperacion,
            'id_transcripcion' => intval($trabajo['id_transcripcion'])
        );
    } finally {
        if (is_file($temporal)) {
            @unlink($temporal);
        }
    }
}

?>

<?php

/**
 * Adjuntos persistentes y asistente DeepSeek para GoHighLevel.
 * Este archivo se carga desde gohighlevel_helper.php y conserva compatibilidad PHP 7.2.
 */

function goHighLevelConfiguracionAdjuntos()
{
    $directorio = trim((string)getenv('TELAR_GOHIGHLEVEL_ATTACHMENT_DIR'));
    if ($directorio === '') {
        $directorio = '/var/lib/telar/gohighlevel_adjuntos';
    }
    $claveArchivo = trim((string)getenv('TELAR_GOHIGHLEVEL_ATTACHMENT_KEY_FILE'));
    if ($claveArchivo === '') {
        $claveArchivo = '/run/secrets/gohighlevel_attachment_signing_key';
    }
    $hostsEntrada = trim((string)getenv('TELAR_GOHIGHLEVEL_ATTACHMENT_HOSTS'));
    if ($hostsEntrada === '') {
        $hostsEntrada = 'api-crm.fivox.app,firebasestorage.googleapis.com';
    }
    $hosts = array();
    foreach (explode(',', $hostsEntrada) as $host) {
        $host = strtolower(trim($host));
        if (preg_match('/^[a-z0-9.-]{3,253}$/', $host)) {
            $hosts[$host] = true;
        }
    }
    $maximo = intval(getenv('TELAR_GOHIGHLEVEL_ATTACHMENT_MAX_BYTES'));
    if ($maximo < 1048576 || $maximo > 52428800) {
        $maximo = 20971520;
    }
    $clave = '';
    if (is_file($claveArchivo) && is_readable($claveArchivo)) {
        $clave = trim((string)@file_get_contents($claveArchivo));
    }
    return array(
        'directorio' => rtrim($directorio, '/\\'),
        'clave' => strlen($clave) >= 32 ? $clave : '',
        'clave_archivo' => $claveArchivo,
        'hosts' => $hosts,
        'maximo_bytes' => $maximo
    );
}

function goHighLevelAdjuntoHostPermitido($host, $config)
{
    $host = strtolower(trim((string)$host));
    return $host !== '' && isset($config['hosts'][$host]);
}

function goHighLevelAdjuntoUrlValida($url, $config)
{
    $url = trim((string)$url);
    if ($url === '' || strlen($url) > 8000) {
        return false;
    }
    $partes = @parse_url($url);
    return is_array($partes)
        && isset($partes['scheme'], $partes['host'])
        && strtolower($partes['scheme']) === 'https'
        && (!isset($partes['port']) || intval($partes['port']) === 443)
        && goHighLevelAdjuntoHostPermitido($partes['host'], $config);
}

function goHighLevelAdjuntoMetadatosUrl($url)
{
    $partes = @parse_url((string)$url);
    $ruta = is_array($partes) && isset($partes['path']) ? rawurldecode((string)$partes['path']) : '';
    $nombre = trim((string)basename($ruta));
    $nombre = preg_replace('/[^A-Za-z0-9._ -]+/u', '_', $nombre);
    $nombre = goHighLevelTexto($nombre, 180);
    $extension = strtolower((string)pathinfo($nombre, PATHINFO_EXTENSION));
    $extension = preg_match('/^[a-z0-9]{1,12}$/', $extension) ? $extension : '';
    $imagenes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $videos = array('mp4', 'webm', 'mov');
    $audios = array('mp3', 'wav', 'ogg', 'm4a', 'aac');
    $tipo = in_array($extension, $imagenes, true) ? 'imagen'
        : (in_array($extension, $videos, true) ? 'video'
        : (in_array($extension, $audios, true) ? 'audio' : 'documento'));
    return array(
        'nombre' => $nombre !== '' ? $nombre : 'Adjunto',
        'extension' => $extension,
        'tipo' => $tipo
    );
}

function goHighLevelAdjuntoFirma($idAdjunto, $vence, $clave)
{
    return hash_hmac('sha256', intval($idAdjunto).'|'.intval($vence), (string)$clave);
}

function goHighLevelAdjuntoUrlLocal($idAdjunto, $clave)
{
    if (intval($idAdjunto) <= 0 || strlen((string)$clave) < 32) {
        return '';
    }
    $vence = time() + 3600;
    $firma = goHighLevelAdjuntoFirma($idAdjunto, $vence, $clave);
    return '/GoodVentaAsisCap/php_system/gohighlevel_adjunto.php?id='.intval($idAdjunto)
        .'&vence='.$vence.'&firma='.$firma;
}

function goHighLevelRegistrarAdjuntosMensaje($mysqli, $conversationId, $messageId, $fecha, $adjuntos)
{
    if (!$mysqli || !goHighLevelTablaExiste($mysqli, 'gohighlevel_adjunto_cache')) {
        return array();
    }
    $conversationId = goHighLevelIdSeguro($conversationId);
    $messageId = goHighLevelIdSeguro($messageId);
    if ($conversationId === '' || $messageId === '' || !is_array($adjuntos)) {
        return array();
    }
    $config = goHighLevelConfiguracionAdjuntos();
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_adjunto_cache "
        ."(ghl_conversation_id,ghl_message_id,indice,url_origen,url_hash,nombre_origen,fecha_origen,"
        ."fecha_creacion,fecha_actualizacion) VALUES (?,?,?,?,?,?,?,NOW(),NOW()) "
        ."ON DUPLICATE KEY UPDATE id_adjunto=LAST_INSERT_ID(id_adjunto),"
        ."estado=IF(url_hash<>VALUES(url_hash) AND estado<>'listo','pendiente',estado),"
        ."codigo_error=IF(url_hash<>VALUES(url_hash) AND estado<>'listo','',codigo_error),"
        ."url_origen=VALUES(url_origen),url_hash=VALUES(url_hash),nombre_origen=VALUES(nombre_origen),"
        ."fecha_origen=VALUES(fecha_origen),fecha_actualizacion=NOW()"
    );
    if (!$stmt) {
        return array();
    }
    $salida = array();
    $indice = 0;
    foreach (array_slice($adjuntos, 0, 20) as $adjunto) {
        $url = is_array($adjunto)
            ? goHighLevelValor($adjunto, array('url', 'fileUrl', 'src', 'attachmentUrl'), '')
            : $adjunto;
        $url = trim((string)$url);
        if (!goHighLevelAdjuntoUrlValida($url, $config)) {
            $indice++;
            continue;
        }
        $meta = goHighLevelAdjuntoMetadatosUrl($url);
        $hash = hash('sha256', $url);
        $nombre = $meta['nombre'];
        $fechaSegura = goHighLevelTexto($fecha, 40);
        $stmt->bind_param('ssissss', $conversationId, $messageId, $indice, $url, $hash, $nombre, $fechaSegura);
        if (!$stmt->execute()) {
            $indice++;
            continue;
        }
        $idAdjunto = intval($mysqli->insert_id);
        if ($idAdjunto <= 0) {
            $resultadoId = $mysqli->query(
                "SELECT id_adjunto FROM gohighlevel_adjunto_cache WHERE ghl_message_id='"
                .$mysqli->real_escape_string($messageId)."' AND indice=".intval($indice)." LIMIT 1"
            );
            $filaId = $resultadoId ? $resultadoId->fetch_assoc() : null;
            $idAdjunto = $filaId ? intval($filaId['id_adjunto']) : 0;
        }
        if ($idAdjunto > 0) {
            $urlLocal = goHighLevelAdjuntoUrlLocal($idAdjunto, $config['clave']);
            $salida[] = array(
                'id' => $idAdjunto,
                'nombre' => $meta['nombre'],
                'extension' => $meta['extension'],
                'tipo' => $meta['tipo'],
                'url' => $urlLocal,
                'disponible' => $urlLocal !== ''
            );
        }
        $indice++;
    }
    $stmt->close();
    return $salida;
}

function goHighLevelObtenerAdjuntoCache($mysqli, $idAdjunto)
{
    $stmt = $mysqli->prepare(
        "SELECT id_adjunto,url_origen,nombre_origen,mime_type,extension,ruta_relativa,"
        ."tamano_bytes,estado FROM gohighlevel_adjunto_cache WHERE id_adjunto=? LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    $idAdjunto = intval($idAdjunto);
    $stmt->bind_param('i', $idAdjunto);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    return $fila;
}

function goHighLevelIpPublica($ip)
{
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) !== false;
}

function goHighLevelMimeAdjuntoPermitido($mime)
{
    $permitidos = array(
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime',
        'audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/aac',
        'application/pdf', 'text/plain', 'text/csv',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );
    return in_array(strtolower(trim((string)$mime)), $permitidos, true);
}

function goHighLevelExtensionMime($mime, $predeterminada)
{
    $mapa = array(
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
        'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov',
        'audio/mpeg' => 'mp3', 'audio/mp4' => 'm4a', 'audio/ogg' => 'ogg',
        'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/aac' => 'aac',
        'application/pdf' => 'pdf', 'text/plain' => 'txt', 'text/csv' => 'csv',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
    );
    $mime = strtolower(trim((string)$mime));
    if (isset($mapa[$mime])) {
        return $mapa[$mime];
    }
    return preg_match('/^[a-z0-9]{1,12}$/', (string)$predeterminada) ? (string)$predeterminada : 'bin';
}

function goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, $codigo)
{
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_adjunto_cache SET estado='error',codigo_error=?,"
        ."fecha_actualizacion=NOW() WHERE id_adjunto=? LIMIT 1"
    );
    if ($stmt) {
        $codigo = goHighLevelTexto($codigo, 48);
        $idAdjunto = intval($idAdjunto);
        $stmt->bind_param('si', $codigo, $idAdjunto);
        $stmt->execute();
        $stmt->close();
    }
}

function goHighLevelDescargarAdjunto($mysqli, $fila, $config)
{
    $idAdjunto = intval($fila['id_adjunto']);
    $url = (string)$fila['url_origen'];
    if (!goHighLevelAdjuntoUrlValida($url, $config) || !function_exists('curl_init')) {
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'origen_no_permitido');
        return null;
    }
    $partes = parse_url($url);
    $host = strtolower((string)$partes['host']);
    $ips = gethostbynamel($host);
    $ipSegura = '';
    foreach ((array)$ips as $ip) {
        if (goHighLevelIpPublica($ip)) {
            $ipSegura = $ip;
            break;
        }
    }
    if ($ipSegura === '') {
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'dns_no_seguro');
        return null;
    }
    $directorioTemporal = $config['directorio'].'/tmp';
    if ((!is_dir($directorioTemporal) && !@mkdir($directorioTemporal, 0770, true)) || !is_writable($directorioTemporal)) {
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'almacenamiento_no_disponible');
        return null;
    }
    $temporal = tempnam($directorioTemporal, 'ghl_');
    $archivo = $temporal ? @fopen($temporal, 'wb') : false;
    if (!$archivo) {
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'temporal_no_disponible');
        return null;
    }
    $bytes = 0;
    $excedido = false;
    $maximo = intval($config['maximo_bytes']);
    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_RESOLVE => array($host.':443:'.$ipSegura),
        CURLOPT_HTTPHEADER => array('Accept: */*', 'User-Agent: Sistema-Telar-GoHighLevel-Adjuntos/1.0'),
        CURLOPT_WRITEFUNCTION => function ($curlHandle, $datos) use ($archivo, &$bytes, &$excedido, $maximo) {
            $longitud = strlen($datos);
            $bytes += $longitud;
            if ($bytes > $maximo) {
                $excedido = true;
                return 0;
            }
            return fwrite($archivo, $datos);
        }
    ));
    $ok = curl_exec($curl);
    $estado = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    $mimeCabecera = goHighLevelTexto(curl_getinfo($curl, CURLINFO_CONTENT_TYPE), 120);
    $errorNumero = curl_errno($curl);
    if (PHP_VERSION_ID < 80500) {
        curl_close($curl);
    } else {
        unset($curl);
    }
    fclose($archivo);
    if ($ok === false || $errorNumero !== 0 || $estado < 200 || $estado >= 300 || $excedido || $bytes <= 0) {
        @unlink($temporal);
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, $excedido ? 'archivo_demasiado_grande' : 'descarga_fallida');
        return null;
    }
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $mime = $finfo ? (string)finfo_file($finfo, $temporal) : strtok($mimeCabecera, ';');
    if ($finfo && PHP_VERSION_ID < 80500) {
        finfo_close($finfo);
    } elseif ($finfo) {
        unset($finfo);
    }
    $mime = strtolower(trim((string)$mime));
    if (!goHighLevelMimeAdjuntoPermitido($mime)) {
        @unlink($temporal);
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'tipo_no_permitido');
        return null;
    }
    $meta = goHighLevelAdjuntoMetadatosUrl($url);
    $extension = goHighLevelExtensionMime($mime, $meta['extension']);
    $subdirectorio = sprintf('%02x', $idAdjunto % 256);
    $destinoDirectorio = $config['directorio'].'/'.$subdirectorio;
    if ((!is_dir($destinoDirectorio) && !@mkdir($destinoDirectorio, 0770, true)) || !is_writable($destinoDirectorio)) {
        @unlink($temporal);
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'almacenamiento_no_disponible');
        return null;
    }
    $rutaRelativa = $subdirectorio.'/'.$idAdjunto.'.'.$extension;
    $destino = $config['directorio'].'/'.$rutaRelativa;
    if (!@rename($temporal, $destino)) {
        @unlink($temporal);
        goHighLevelMarcarErrorAdjunto($mysqli, $idAdjunto, 'almacenamiento_no_disponible');
        return null;
    }
    @chmod($destino, 0640);
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_adjunto_cache SET mime_type=?,extension=?,ruta_relativa=?,"
        ."tamano_bytes=?,estado='listo',codigo_error='',fecha_descarga=NOW(),"
        ."fecha_ultima_vista=NOW(),fecha_actualizacion=NOW() WHERE id_adjunto=? LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('sssii', $mime, $extension, $rutaRelativa, $bytes, $idAdjunto);
        $stmt->execute();
        $stmt->close();
    }
    return goHighLevelObtenerAdjuntoCache($mysqli, $idAdjunto);
}

function goHighLevelDeepSeekConfiguracion()
{
    $archivo = trim((string)getenv('TELAR_DEEPSEEK_API_KEY_FILE'));
    if ($archivo === '') {
        $archivo = '/run/secrets/deepseek_api_key';
    }
    $clave = '';
    if (is_file($archivo) && is_readable($archivo)) {
        $clave = trim((string)@file_get_contents($archivo));
    }
    $modelo = trim((string)getenv('TELAR_DEEPSEEK_MODEL'));
    if (!in_array($modelo, array('deepseek-v4-flash', 'deepseek-v4-pro'), true)) {
        $modelo = 'deepseek-v4-flash';
    }
    $auto = strtolower(trim((string)getenv('TELAR_DEEPSEEK_AUTO_REPLY_ENABLED')));
    $automatico = goHighLevelAutomaticoConfiguracion();
    return array(
        'base' => 'https://api.deepseek.com',
        'clave' => strlen($clave) >= 20 ? $clave : '',
        'clave_archivo' => $archivo,
        'modelo' => $modelo,
        'automatico_servidor' => in_array($auto, array('1', 'true', 'yes', 'on'), true),
        'automatico_alcance' => $automatico['alcance'],
        'automatico_retardo_segundos' => $automatico['retardo_segundos'],
        'automatico_contactos_piloto' => $automatico['contactos_piloto'],
        'automatico_piloto_configurado' => count($automatico['contactos_piloto']) > 0
    );
}

function goHighLevelAutomaticoConfiguracion()
{
    $alcance = strtolower(trim((string)getenv('TELAR_DEEPSEEK_AUTO_SCOPE')));
    if (!in_array($alcance, array('pilot', 'all'), true)) {
        $alcance = 'pilot';
    }
    $retardo = intval(getenv('TELAR_DEEPSEEK_AUTO_REPLY_DELAY_SECONDS'));
    if ($retardo <= 0) {
        $retardo = 120;
    }
    $retardo = max(60, min(1800, $retardo));
    $archivo = trim((string)getenv('TELAR_DEEPSEEK_PILOT_CONTACT_IDS_FILE'));
    if ($archivo === '') {
        $archivo = '/run/secrets/deepseek_pilot_contact_ids';
    }
    $contactos = array();
    if (is_file($archivo) && is_readable($archivo)) {
        $contenido = trim((string)@file_get_contents($archivo));
        foreach (preg_split('/[\s,;]+/', $contenido, -1, PREG_SPLIT_NO_EMPTY) as $valor) {
            $id = goHighLevelIdSeguro($valor);
            if ($id !== '') {
                $contactos[$id] = true;
            }
        }
    }
    return array(
        'alcance' => $alcance,
        'retardo_segundos' => $retardo,
        'contactos_piloto' => array_keys($contactos),
        'archivo_piloto' => $archivo
    );
}

function goHighLevelAutomaticoContactoPermitido($automatico, $contactId)
{
    $contactId = goHighLevelIdSeguro($contactId);
    if ($contactId === '') {
        return false;
    }
    if (isset($automatico['alcance']) && $automatico['alcance'] === 'all') {
        return true;
    }
    $permitidos = isset($automatico['contactos_piloto']) && is_array($automatico['contactos_piloto'])
        ? $automatico['contactos_piloto'] : array();
    return in_array($contactId, $permitidos, true);
}

function goHighLevelAutomaticoMensajeListo($mensaje, $retardoSegundos, $ahora = null)
{
    if (!is_array($mensaje)) {
        return false;
    }
    $direccion = strtolower(trim((string)goHighLevelValor($mensaje, array('direccion', 'direction'), '')));
    $tipo = strtolower(trim((string)goHighLevelValor($mensaje, array('tipo', 'messageType', 'type'), '')));
    $messageId = goHighLevelIdSeguro(goHighLevelValor($mensaje, array('id', '_id'), ''));
    $fecha = goHighLevelSegundos(goHighLevelValor($mensaje, array('fecha', 'dateAdded', 'createdAt'), ''));
    $ahora = $ahora === null ? time() : intval($ahora);
    $retardoSegundos = max(60, min(1800, intval($retardoSegundos)));
    return $direccion === 'inbound' && strpos($tipo, 'whatsapp') !== false
        && $messageId !== '' && $fecha > 0 && $fecha <= ($ahora - $retardoSegundos);
}

function goHighLevelIaConfiguracionLocal($mysqli)
{
    $deepseek = goHighLevelDeepSeekConfiguracion();
    $resultado = $mysqli->query(
        "SELECT asistente_habilitado,automatico_habilitado,modelo,prompt_base,informacion_clinica,"
        ."preguntas_frecuentes,tono,reglas_derivacion,fecha_actualizacion "
        ."FROM gohighlevel_ia_config WHERE id_config=1 LIMIT 1"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if (!$fila) {
        goHighLevelLanzar('ia_no_instalada', 'La configuracion de IA todavia no esta disponible.', array(), 503);
    }
    return array(
        'asistente_habilitado' => intval($fila['asistente_habilitado']) === 1,
        'automatico_habilitado' => intval($fila['automatico_habilitado']) === 1,
        'modelo' => in_array($fila['modelo'], array('deepseek-v4-flash', 'deepseek-v4-pro'), true)
            ? $fila['modelo'] : $deepseek['modelo'],
        'prompt_base' => (string)$fila['prompt_base'],
        'informacion_clinica' => (string)$fila['informacion_clinica'],
        'preguntas_frecuentes' => (string)$fila['preguntas_frecuentes'],
        'tono' => (string)$fila['tono'],
        'reglas_derivacion' => (string)$fila['reglas_derivacion'],
        'clave_configurada' => $deepseek['clave'] !== '',
        'automatico_servidor' => !empty($deepseek['automatico_servidor']),
        'automatico_alcance' => (string)$deepseek['automatico_alcance'],
        'automatico_retardo_segundos' => intval($deepseek['automatico_retardo_segundos']),
        'automatico_piloto_configurado' => !empty($deepseek['automatico_piloto_configurado']),
        'fecha_actualizacion' => (string)$fila['fecha_actualizacion']
    );
}

function goHighLevelGuardarConfiguracionIa($mysqli, $contexto, $parametros)
{
    if (empty($contexto['puede_configurar'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para configurar la IA.', array(), 403);
    }
    $deepseek = goHighLevelDeepSeekConfiguracion();
    $asistente = intval(goHighLevelValor($parametros, array('asistente_habilitado'), 0)) === 1 ? 1 : 0;
    $automatico = intval(goHighLevelValor($parametros, array('automatico_habilitado'), 0)) === 1 ? 1 : 0;
    if ($asistente && $deepseek['clave'] === '') {
        goHighLevelLanzar('deepseek_no_configurado', 'Instale primero la clave privada de DeepSeek en el servidor.', array(), 422);
    }
    if ($automatico && (!$asistente || empty($deepseek['automatico_servidor']))) {
        goHighLevelLanzar(
            'automatico_no_habilitado',
            'Las respuestas automaticas requieren el asistente activo y la autorizacion operativa del servidor.',
            array(),
            422
        );
    }
    $modelo = goHighLevelTexto(goHighLevelValor($parametros, array('modelo'), $deepseek['modelo']), 64);
    if (!in_array($modelo, array('deepseek-v4-flash', 'deepseek-v4-pro'), true)) {
        $modelo = $deepseek['modelo'];
    }
    $prompt = goHighLevelTexto(goHighLevelValor($parametros, array('prompt_base'), ''), 12000);
    $informacion = goHighLevelTexto(goHighLevelValor($parametros, array('informacion_clinica'), ''), 20000);
    $faq = goHighLevelTexto(goHighLevelValor($parametros, array('preguntas_frecuentes'), ''), 30000);
    $tono = goHighLevelTexto(goHighLevelValor($parametros, array('tono'), ''), 255);
    $reglas = goHighLevelTexto(goHighLevelValor($parametros, array('reglas_derivacion'), ''), 12000);
    $actor = intval($contexto['cod_usuario']);
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_ia_config SET asistente_habilitado=?,automatico_habilitado=?,modelo=?,"
        ."prompt_base=?,informacion_clinica=?,preguntas_frecuentes=?,tono=?,reglas_derivacion=?,"
        ."cod_usuario_actualizaFK=?,fecha_actualizacion=NOW() WHERE id_config=1 LIMIT 1"
    );
    if (!$stmt) {
        goHighLevelLanzar('ia_no_guardada', 'No se pudo preparar la configuracion de IA.', array(), 500);
    }
    $stmt->bind_param('iissssssi', $asistente, $automatico, $modelo, $prompt, $informacion, $faq, $tono, $reglas, $actor);
    if (!$stmt->execute()) {
        $stmt->close();
        goHighLevelLanzar('ia_no_guardada', 'No se pudo guardar la configuracion de IA.', array(), 500);
    }
    $stmt->close();
    goHighLevelRegistrarEvento($mysqli, $contexto, 'ia_config_actualizada', 'modulo', 'gohighlevel',
        'Asistente: '.$asistente.'; automatico: '.$automatico.'; modelo: '.$modelo);
    return goHighLevelIaConfiguracionLocal($mysqli);
}

function goHighLevelAnonimizarTextoIa($texto)
{
    $texto = goHighLevelTexto($texto, 4000);
    $texto = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '[correo]', $texto);
    $texto = preg_replace('/(?<!\d)(?:\+?\d[\d\s().-]{6,}\d)(?!\d)/u', '[telefono o identificador]', $texto);
    return trim((string)$texto);
}

function goHighLevelRiesgoIa($mensajes)
{
    $textoRiesgo = '';
    $tieneAdjunto = false;
    $inboundRevisados = 0;
    for ($i = count($mensajes) - 1; $i >= 0; $i--) {
        $mensaje = $mensajes[$i];
        if (!is_array($mensaje)) {
            continue;
        }
        $direccion = strtolower((string)$mensaje['direccion']);
        if ($direccion === 'outbound') {
            break;
        }
        if ($direccion !== 'inbound') {
            continue;
        }
        $inboundRevisados++;
        if (!empty($mensaje['adjuntos']) || !empty($mensaje['archivos'])) {
            $tieneAdjunto = true;
        }
        $textoRiesgo .= ' '.mb_strtolower((string)$mensaje['cuerpo'], 'UTF-8');
        if ($inboundRevisados >= 4) {
            break;
        }
    }
    if ($tieneAdjunto) {
        return 'El mensaje contiene un archivo y requiere revision humana.';
    }
    $patron = '/(dolor|sangrad|fiebre|inflam|medic|receta|diagn[oó]st|tratamiento|alerg|embaraz|urgenc|pago|pagu[eé]|transfer|comprobante|cuota|saldo|factura|reclamo|queja|denuncia|abogad|legal|judicial|informconf)/iu';
    if (trim($textoRiesgo) !== '' && preg_match($patron, $textoRiesgo)) {
        return 'La consulta puede involucrar salud, pagos, reclamos o asuntos legales y requiere revision humana.';
    }
    return '';
}

function goHighLevelRegistrarOperacionIa($mysqli, $contexto, $token, $tipo, $conversationId, $messageId, $modelo, $estado, $codigo, $intencion, $confianza, $humano, $entrada, $salida)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_ia_operacion "
        ."(token_cliente,cod_usuarioFK,tipo_operacion,ghl_conversation_id,ghl_message_id,modelo,estado,"
        ."codigo_resultado,intencion,confianza,requiere_humano,caracteres_entrada,caracteres_salida,"
        ."fecha_creacion,fecha_actualizacion) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW()) "
        ."ON DUPLICATE KEY UPDATE estado=VALUES(estado),codigo_resultado=VALUES(codigo_resultado),"
        ."intencion=VALUES(intencion),confianza=VALUES(confianza),requiere_humano=VALUES(requiere_humano),"
        ."caracteres_entrada=VALUES(caracteres_entrada),caracteres_salida=VALUES(caracteres_salida),"
        ."fecha_actualizacion=NOW()"
    );
    if (!$stmt) {
        return;
    }
    $actor = intval($contexto['cod_usuario']);
    $confianza = max(0, min(1, floatval($confianza)));
    $humano = $humano ? 1 : 0;
    $entrada = max(0, intval($entrada));
    $salida = max(0, intval($salida));
    $stmt->bind_param(
        'sisssssssdiii',
        $token, $actor, $tipo, $conversationId, $messageId, $modelo, $estado,
        $codigo, $intencion, $confianza, $humano, $entrada, $salida
    );
    $stmt->execute();
    $stmt->close();
}

function goHighLevelDecodificarRespuestaIa($contenido)
{
    $contenido = trim((string)$contenido);
    $contenido = preg_replace('/^```(?:json)?\s*|\s*```$/iu', '', $contenido);
    $resultado = json_decode($contenido, true);
    if (is_array($resultado)) {
        return $resultado;
    }
    $inicio = strpos($contenido, '{');
    $fin = strrpos($contenido, '}');
    if ($inicio === false || $fin === false || $fin <= $inicio) {
        return array();
    }
    $resultado = json_decode(substr($contenido, $inicio, ($fin - $inicio) + 1), true);
    return is_array($resultado) ? $resultado : array();
}

function goHighLevelDeepSeekSolicitar($deepseek, $modelo, $sistema, $usuario, $intento = 0)
{
    if ($deepseek['clave'] === '' || !function_exists('curl_init')) {
        goHighLevelLanzar('deepseek_no_configurado', 'DeepSeek todavia no esta configurado en el servidor.', array(), 503);
    }
    $entrada = json_encode(array(
        'model' => $modelo,
        'messages' => array(
            array('role' => 'system', 'content' => $sistema),
            array('role' => 'user', 'content' => $usuario)
        ),
        'response_format' => array('type' => 'json_object'),
        'temperature' => 0.2,
        'max_tokens' => 600,
        'stream' => false
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($entrada)) {
        goHighLevelLanzar('ia_solicitud_invalida', 'No se pudo preparar la consulta de IA.', array(), 500);
    }
    $curl = curl_init($deepseek['base'].'/chat/completions');
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $entrada,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json', 'Content-Type: application/json',
            'Authorization: Bearer '.$deepseek['clave'],
            'User-Agent: Sistema-Telar-DeepSeek/1.0'
        )
    ));
    $cuerpo = curl_exec($curl);
    $estado = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    $errorNumero = curl_errno($curl);
    if (PHP_VERSION_ID < 80500) {
        curl_close($curl);
    } else {
        unset($curl);
    }
    if ($cuerpo === false || $errorNumero !== 0 || $estado < 200 || $estado >= 300) {
        error_log('DeepSeek: solicitud fallida (HTTP '.$estado.', curl '.$errorNumero.')');
        goHighLevelLanzar('deepseek_no_disponible', 'La IA no pudo responder en este momento.', array(), 502);
    }
    $datos = json_decode($cuerpo, true);
    $contenido = is_array($datos) && isset($datos['choices'][0]['message']['content'])
        ? trim((string)$datos['choices'][0]['message']['content']) : '';
    $resultado = goHighLevelDecodificarRespuestaIa($contenido);
    if (!is_array($resultado)) {
        $resultado = array();
    }
    if (count($resultado) === 0 && intval($intento) < 1) {
        return goHighLevelDeepSeekSolicitar(
            $deepseek,
            $modelo,
            $sistema."\nDevolve exclusivamente un objeto JSON valido, sin bloques Markdown ni texto adicional.",
            $usuario,
            intval($intento) + 1
        );
    }
    if (count($resultado) === 0) {
        goHighLevelLanzar('deepseek_respuesta_invalida', 'La IA no devolvio una sugerencia valida.', array(), 502);
    }
    return $resultado;
}

function goHighLevelSugerirRespuestaIa($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_responder'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para solicitar sugerencias.', array(), 403);
    }
    $ia = goHighLevelIaConfiguracionLocal($mysqli);
    if (empty($ia['asistente_habilitado']) || empty($ia['clave_configurada'])) {
        goHighLevelLanzar('ia_no_habilitada', 'El asistente de IA esta apagado o sin clave privada.', array(), 503);
    }
    $tipoOperacion = goHighLevelValor($parametros, array('tipo_operacion'), '') === 'automatico'
        ? 'automatico' : 'sugerencia';
    if ($tipoOperacion === 'automatico'
        && (empty($contexto['es_propietario']) || empty($ia['automatico_habilitado'])
            || empty($ia['automatico_servidor']))) {
        goHighLevelLanzar('automatico_no_habilitado', 'La respuesta automatica no esta habilitada.', array(), 403);
    }
    $conversationId = goHighLevelIdSeguro(goHighLevelValor($parametros, array('conversation_id'), ''));
    $token = goHighLevelTexto(goHighLevelValor($parametros, array('token_ia'), ''), 64);
    if ($conversationId === '' || !preg_match('/^[A-Za-z0-9_-]{16,64}$/', $token)) {
        goHighLevelLanzar('ia_solicitud_invalida', 'No se pudo preparar la sugerencia.', array(), 400);
    }
    $historial = goHighLevelListarMensajesConversacion($config, array(
        'conversation_id' => $conversationId,
        'limite' => 50
    ));
    if (empty($historial['ventana_whatsapp']['abierta'])) {
        goHighLevelLanzar('ventana_cerrada', 'Fuera de 24 horas corresponde usar una plantilla aprobada.', array(), 409);
    }
    $mensajes = isset($historial['items']) ? $historial['items'] : array();
    $ultimoMensajeId = '';
    for ($i = count($mensajes) - 1; $i >= 0; $i--) {
        if (strtolower((string)$mensajes[$i]['direccion']) === 'inbound') {
            $ultimoMensajeId = goHighLevelIdSeguro($mensajes[$i]['id']);
            break;
        }
    }
    $riesgo = goHighLevelRiesgoIa($mensajes);
    if ($riesgo !== '') {
        goHighLevelRegistrarOperacionIa($mysqli, $contexto, $token, $tipoOperacion, $conversationId,
            $ultimoMensajeId, $ia['modelo'], 'derivada', 'requiere_humano', 'sensible', 0, true, 0, 0);
        return array('respuesta' => '', 'intencion' => 'requiere_humano', 'confianza' => 0,
            'requiere_humano' => true, 'motivo' => $riesgo);
    }
    $lineas = array();
    foreach (array_slice($mensajes, -12) as $mensaje) {
        $texto = goHighLevelAnonimizarTextoIa(isset($mensaje['cuerpo']) ? $mensaje['cuerpo'] : '');
        if ($texto === '') {
            continue;
        }
        $rol = strtolower((string)$mensaje['direccion']) === 'outbound' ? 'CLINICA' : 'CONTACTO';
        $lineas[] = $rol.': '.$texto;
    }
    $contextoTexto = implode("\n", $lineas);
    if ($contextoTexto === '') {
        goHighLevelLanzar('ia_sin_contexto', 'No hay texto suficiente para preparar una sugerencia.', array(), 422);
    }
    $sistema = "Sos un asistente administrativo de una clinica odontologica. "
        ."Solo redactas una respuesta breve para que un funcionario la revise; nunca la envias. "
        ."No diagnostiques, no indiques medicacion, no confirmes pagos, no resuelvas reclamos o asuntos legales. "
        ."Si falta informacion o el caso es sensible, marca requiere_humano=true y deja respuesta vacia. "
        ."No inventes horarios, precios, servicios ni disponibilidad. No pidas datos medicos. "
        ."Devolve exclusivamente JSON con respuesta, intencion, confianza, requiere_humano y motivo.\n"
        ."TONO:\n".$ia['tono']."\nPROMPT LOCAL:\n".$ia['prompt_base']
        ."\nINFORMACION AUTORIZADA:\n".$ia['informacion_clinica']
        ."\nPREGUNTAS FRECUENTES:\n".$ia['preguntas_frecuentes']
        ."\nREGLAS DE DERIVACION:\n".$ia['reglas_derivacion'];
    $usuario = "Historial anonimizado, del mas antiguo al mas reciente:\n".$contextoTexto;
    $deepseek = goHighLevelDeepSeekConfiguracion();
    try {
        $resultado = goHighLevelDeepSeekSolicitar($deepseek, $ia['modelo'], $sistema, $usuario);
    } catch (GoHighLevelExcepcion $e) {
        goHighLevelRegistrarOperacionIa($mysqli, $contexto, $token, $tipoOperacion, $conversationId,
            $ultimoMensajeId, $ia['modelo'], 'error', $e->codigoOperacion, '', 0, true,
            mb_strlen($contextoTexto, 'UTF-8'), 0);
        throw $e;
    }
    $respuesta = goHighLevelTexto(goHighLevelValor($resultado, array('respuesta'), ''), 1000);
    $intencion = goHighLevelTexto(goHighLevelValor($resultado, array('intencion'), 'general'), 80);
    $confianza = max(0, min(1, floatval(goHighLevelValor($resultado, array('confianza'), 0))));
    $requiereHumanoValor = goHighLevelValor($resultado, array('requiere_humano'), true);
    $requiereHumano = $requiereHumanoValor === true || $requiereHumanoValor === 1
        || strtolower((string)$requiereHumanoValor) === 'true' || $confianza < 0.72 || $respuesta === '';
    $motivo = goHighLevelTexto(goHighLevelValor($resultado, array('motivo'), ''), 300);
    if ($requiereHumano) {
        $respuesta = '';
    }
    goHighLevelRegistrarOperacionIa($mysqli, $contexto, $token, $tipoOperacion, $conversationId,
        $ultimoMensajeId, $ia['modelo'], 'completada', $requiereHumano ? 'requiere_humano' : 'sugerencia_lista',
        $intencion, $confianza, $requiereHumano, mb_strlen($contextoTexto, 'UTF-8'),
        mb_strlen($respuesta, 'UTF-8'));
    return array(
        'respuesta' => $respuesta,
        'intencion' => $intencion,
        'confianza' => $confianza,
        'requiere_humano' => $requiereHumano,
        'motivo' => $motivo !== '' ? $motivo : ($requiereHumano ? 'La consulta requiere revision humana.' : '')
    );
}

?>

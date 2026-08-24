<?php

/**
 * Integracion entre Sistema Telar y GoHighLevel.
 * La consulta es general. Las escrituras permitidas son respuestas manuales,
 * plantillas aprobadas y tareas de contactos; todas quedan limitadas y auditadas.
 * Compatible con PHP 7.2. Los tokens se leen desde un archivo privado.
 */

class GoHighLevelExcepcion extends Exception
{
    public $codigoOperacion;
    public $datosOperacion;
    public $estadoHttp;

    public function __construct($codigo, $mensaje, $datos = array(), $estadoHttp = 422)
    {
        parent::__construct($mensaje);
        $this->codigoOperacion = (string)$codigo;
        $this->datosOperacion = is_array($datos) ? $datos : array();
        $this->estadoHttp = intval($estadoHttp);
    }
}

function goHighLevelLanzar($codigo, $mensaje, $datos = array(), $estadoHttp = 422)
{
    throw new GoHighLevelExcepcion($codigo, $mensaje, $datos, $estadoHttp);
}

function goHighLevelTexto($valor, $maximo)
{
    $texto = trim((string)$valor);
    if ($maximo > 0 && mb_strlen($texto, 'UTF-8') > $maximo) {
        $texto = mb_substr($texto, 0, $maximo, 'UTF-8');
    }
    return $texto;
}

function goHighLevelValor($datos, $claves, $predeterminado = '')
{
    if (!is_array($datos)) {
        return $predeterminado;
    }
    foreach ((array)$claves as $clave) {
        if (array_key_exists($clave, $datos) && $datos[$clave] !== null) {
            return $datos[$clave];
        }
    }
    return $predeterminado;
}

function goHighLevelTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $clave = spl_object_hash($mysqli).'|'.(string)$tabla;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    $stmt = $mysqli->prepare(
        'SELECT COUNT(*) FROM information_schema.tables '
        .'WHERE table_schema=DATABASE() AND table_name=?'
    );
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $tabla);
    $total = 0;
    if ($stmt->execute()) {
        $stmt->bind_result($total);
        $stmt->fetch();
    }
    $stmt->close();
    $cache[$clave] = intval($total) > 0;
    return $cache[$clave];
}

function goHighLevelEstructuraDisponible($mysqli)
{
    return goHighLevelTablaExiste($mysqli, 'gohighlevel_permiso_usuario')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_vinculo_contacto')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_evento')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_envio_manual')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_plantilla_config')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_envio_plantilla')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_usuario_vinculo')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_tarea_cache')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_tarea_sync')
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_tarea_operacion');
}

function goHighLevelContextoUsuario($mysqli, $codUsuario)
{
    $codUsuario = intval($codUsuario);
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,IFNULL(p.nombre_persona,u.login) nombre,IFNULL(u.url,'') avatar,"
        ."IFNULL(g.puede_ver,0) puede_ver,IFNULL(g.puede_responder,0) puede_responder,"
        ."IFNULL(g.puede_enviar_plantilla,0) puede_enviar_plantilla,"
        ."IFNULL(g.puede_ver_tareas,0) puede_ver_tareas,"
        ."IFNULL(g.puede_ver_equipo,0) puede_ver_equipo,"
        ."IFNULL(g.puede_gestionar_tareas,0) puede_gestionar_tareas,"
        ."IFNULL(g.puede_configurar,0) puede_configurar,IFNULL(uv.ghl_user_id,'') ghl_user_id "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN gohighlevel_permiso_usuario g ON g.cod_usuarioFK=u.cod_usuario AND g.activo=1 "
        ."LEFT JOIN gohighlevel_usuario_vinculo uv ON uv.cod_usuarioFK=u.cod_usuario AND uv.estado='vinculado' "
        ."WHERE u.cod_usuario=? AND UPPER(TRIM(u.estado))='ACTIVO' LIMIT 1"
    );
    if (!$stmt) {
        goHighLevelLanzar('acceso_no_disponible', 'No se pudo comprobar el acceso al modulo.', array(), 500);
    }
    $stmt->bind_param('i', $codUsuario);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    if (!$fila) {
        goHighLevelLanzar('usuario_no_disponible', 'El usuario no esta activo.', array(), 403);
    }
    $esPropietario = $codUsuario === 5994;
    $puedeVer = $esPropietario || intval($fila['puede_ver']) === 1;
    $puedeResponder = $esPropietario || intval($fila['puede_responder']) === 1;
    $puedeEnviarPlantilla = $esPropietario || intval($fila['puede_enviar_plantilla']) === 1;
    $puedeVerTareas = $esPropietario || intval($fila['puede_ver_tareas']) === 1;
    $puedeVerEquipo = $esPropietario || intval($fila['puede_ver_equipo']) === 1;
    $puedeGestionarTareas = $esPropietario || intval($fila['puede_gestionar_tareas']) === 1;
    $puedeConfigurar = $esPropietario || intval($fila['puede_configurar']) === 1;
    if (!$puedeVer) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene acceso al modulo GoHighLevel.', array(), 403);
    }
    return array(
        'cod_usuario' => $codUsuario,
        'nombre' => (string)$fila['nombre'],
        'avatar' => (string)$fila['avatar'],
        'puede_ver' => $puedeVer,
        'puede_responder' => $puedeResponder,
        'puede_enviar_plantilla' => $puedeEnviarPlantilla,
        'puede_ver_tareas' => $puedeVerTareas,
        'puede_ver_equipo' => $puedeVerEquipo,
        'puede_gestionar_tareas' => $puedeGestionarTareas,
        'puede_configurar' => $puedeConfigurar,
        'ghl_user_id' => (string)$fila['ghl_user_id'],
        'es_propietario' => $esPropietario
    );
}

function goHighLevelConfiguracion()
{
    $base = trim((string)getenv('TELAR_GOHIGHLEVEL_API_BASE'));
    if ($base === '') {
        $base = 'https://services.leadconnectorhq.com';
    }
    $partes = @parse_url($base);
    if (!is_array($partes) || !isset($partes['scheme']) || !isset($partes['host'])
        || strtolower($partes['scheme']) !== 'https'
        || strtolower($partes['host']) !== 'services.leadconnectorhq.com') {
        $base = 'https://services.leadconnectorhq.com';
    }
    $locationId = trim((string)getenv('TELAR_GOHIGHLEVEL_LOCATION_ID'));
    $companyId = trim((string)getenv('TELAR_GOHIGHLEVEL_COMPANY_ID'));
    $tokenFile = trim((string)getenv('TELAR_GOHIGHLEVEL_TOKEN_FILE'));
    if ($tokenFile === '') {
        $tokenFile = '/run/secrets/gohighlevel_readonly_token';
    }
    $version = trim((string)getenv('TELAR_GOHIGHLEVEL_API_VERSION'));
    if ($version === '') {
        $version = '2021-07-28';
    }
    $token = '';
    if (is_file($tokenFile) && is_readable($tokenFile)) {
        $token = trim((string)@file_get_contents($tokenFile));
    }
    $writeEnabled = strtolower(trim((string)getenv('TELAR_GOHIGHLEVEL_WRITE_ENABLED')));
    $taskWriteEnabled = strtolower(trim((string)getenv('TELAR_GOHIGHLEVEL_TASK_WRITE_ENABLED')));
    return array(
        'base' => rtrim($base, '/'),
        'location_id' => preg_match('/^[A-Za-z0-9_-]{8,80}$/', $locationId) ? $locationId : '',
        'company_id' => preg_match('/^[A-Za-z0-9_-]{8,80}$/', $companyId) ? $companyId : '',
        'token' => strlen($token) >= 20 ? $token : '',
        'version' => preg_match('/^[A-Za-z0-9._-]{1,32}$/', $version) ? $version : '2021-07-28',
        'token_file' => $tokenFile,
        'write_enabled' => in_array($writeEnabled, array('1', 'true', 'yes', 'on'), true),
        'task_write_enabled' => in_array($taskWriteEnabled, array('1', 'true', 'yes', 'on'), true)
    );
}

function goHighLevelConfigurado($config)
{
    return is_array($config)
        && trim((string)$config['location_id']) !== ''
        && trim((string)$config['token']) !== '';
}

function goHighLevelApiGet($config, $ruta, $parametros, $versionForzada = '')
{
    if (!goHighLevelConfigurado($config)) {
        goHighLevelLanzar(
            'integracion_no_configurada',
            'La conexion privada con GoHighLevel todavia no esta disponible.',
            array(),
            503
        );
    }
    $rutasPermitidas = array(
        '/contacts/' => true,
        '/conversations/search' => true,
        '/opportunities/search' => true,
        '/opportunities/pipelines' => true,
        '/calendars/' => true,
        '/users/' => true,
        '/users/search' => true
    );
    $rutaMensajes = preg_match('#^/conversations/[A-Za-z0-9_-]{8,80}/messages$#', $ruta) === 1;
    $rutaConversacion = preg_match('#^/conversations/[A-Za-z0-9_-]{8,80}$#', $ruta) === 1;
    $rutaPlantillas = preg_match('#^/locations/[A-Za-z0-9_-]{8,80}/templates$#', $ruta) === 1;
    $rutaTareas = preg_match('#^/contacts/[A-Za-z0-9_-]{8,80}/tasks$#', $ruta) === 1;
    if (!isset($rutasPermitidas[$ruta]) && !$rutaMensajes && !$rutaConversacion && !$rutaPlantillas && !$rutaTareas) {
        goHighLevelLanzar('ruta_no_permitida', 'La consulta solicitada no esta permitida.', array(), 400);
    }
    if (!function_exists('curl_init')) {
        goHighLevelLanzar('cliente_http_no_disponible', 'El servidor no tiene habilitado el cliente seguro.', array(), 503);
    }
    $url = $config['base'].$ruta;
    if (is_array($parametros) && count($parametros) > 0) {
        $url .= '?'.http_build_query($parametros, '', '&');
    }
    $versionApi = trim((string)$versionForzada);
    if (!preg_match('/^[A-Za-z0-9._-]{1,32}$/', $versionApi)) {
        $versionApi = $config['version'];
    }
    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Authorization: Bearer '.$config['token'],
            'Version: '.$versionApi,
            'User-Agent: Sistema-Telar-GoHighLevel/1.0'
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
    if ($cuerpo === false || $errorNumero !== 0) {
        error_log('GoHighLevel: fallo de red en '.$ruta.' (curl '.$errorNumero.')');
        goHighLevelLanzar('gohighlevel_no_disponible', 'No se pudo conectar con GoHighLevel.', array(), 502);
    }
    $datos = json_decode($cuerpo, true);
    if ($estado < 200 || $estado >= 300 || !is_array($datos)) {
        error_log('GoHighLevel: respuesta HTTP '.$estado.' en '.$ruta);
        $mensaje = $estado === 401 || $estado === 403
            ? 'La integracion de GoHighLevel no tiene autorizacion para esta consulta.'
            : 'GoHighLevel no pudo completar la consulta en este momento.';
        goHighLevelLanzar('gohighlevel_respuesta_invalida', $mensaje, array('estado' => $estado), 502);
    }
    return $datos;
}

function goHighLevelApiBuscarTareas($config, $limite, $skip)
{
    if (!goHighLevelConfigurado($config)) {
        goHighLevelLanzar(
            'integracion_no_configurada',
            'La conexion privada con GoHighLevel todavia no esta disponible.',
            array(),
            503
        );
    }
    if (!function_exists('curl_init')) {
        goHighLevelLanzar('cliente_http_no_disponible', 'El servidor no tiene habilitado el cliente seguro.', array(), 503);
    }
    $locationId = goHighLevelIdSeguro(isset($config['location_id']) ? $config['location_id'] : '');
    if ($locationId === '') {
        goHighLevelLanzar('integracion_no_configurada', 'La subcuenta de GoHighLevel no es valida.', array(), 503);
    }
    $limite = max(1, min(100, intval($limite)));
    $skip = max(0, min(10000000, intval($skip)));
    $ruta = '/locations/'.rawurlencode($locationId).'/tasks/search';
    $entrada = json_encode(array('limit' => $limite, 'skip' => $skip));
    if (!is_string($entrada)) {
        goHighLevelLanzar('tareas_no_disponibles', 'No se pudo preparar la consulta de tareas.', array(), 500);
    }
    $curl = curl_init($config['base'].$ruta);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $entrada,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '.$config['token'],
            'Version: v3',
            'User-Agent: Sistema-Telar-GoHighLevel/3.1'
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
    if ($cuerpo === false || $errorNumero !== 0) {
        error_log('GoHighLevel: fallo de red al buscar tareas (curl '.$errorNumero.')');
        goHighLevelLanzar('tareas_no_disponibles', 'No se pudo conectar con GoHighLevel para consultar tareas.', array(), 502);
    }
    $datos = json_decode($cuerpo, true);
    if (!in_array($estado, array(200, 201), true) || !is_array($datos)) {
        error_log('GoHighLevel: respuesta HTTP '.$estado.' al buscar tareas');
        $mensaje = $estado === 401 || $estado === 403
            ? 'La integracion privada no tiene el permiso locations/tasks.readonly.'
            : 'GoHighLevel no pudo consultar las tareas en este momento.';
        goHighLevelLanzar('tareas_no_disponibles', $mensaje, array('estado' => $estado), 502);
    }
    return $datos;
}

function goHighLevelApiPostMensaje($config, $entrada)
{
    if (!goHighLevelConfigurado($config) || empty($config['write_enabled'])) {
        goHighLevelLanzar(
            'envio_no_habilitado',
            'Las respuestas manuales todavia no estan habilitadas en la conexion privada.',
            array(),
            503
        );
    }
    if (!function_exists('curl_init')) {
        goHighLevelLanzar('cliente_http_no_disponible', 'El servidor no tiene habilitado el cliente seguro.', array(), 503);
    }
    $cuerpoJson = json_encode($entrada, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($cuerpoJson)) {
        goHighLevelLanzar('mensaje_invalido', 'No se pudo preparar el mensaje.', array(), 400);
    }
    $ruta = '/conversations/messages';
    $curl = curl_init($config['base'].$ruta);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $cuerpoJson,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '.$config['token'],
            'Version: '.$config['version'],
            'User-Agent: Sistema-Telar-GoHighLevel/2.0'
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
    if ($cuerpo === false || $errorNumero !== 0) {
        error_log('GoHighLevel: fallo de red al enviar mensaje (curl '.$errorNumero.')');
        goHighLevelLanzar('envio_no_disponible', 'No se pudo conectar con GoHighLevel para enviar.', array(), 502);
    }
    $datos = json_decode($cuerpo, true);
    if ($estado < 200 || $estado >= 300 || !is_array($datos)) {
        error_log('GoHighLevel: respuesta HTTP '.$estado.' al enviar mensaje');
        $mensaje = $estado === 401 || $estado === 403
            ? 'La integracion privada no tiene el permiso conversations/message.write.'
            : 'GoHighLevel no pudo enviar el mensaje en este momento.';
        goHighLevelLanzar('envio_rechazado', $mensaje, array('estado' => $estado), 502);
    }
    return $datos;
}

function goHighLevelApiEscribirTarea($config, $metodo, $contactId, $taskId, $completar, $entrada)
{
    if (!goHighLevelConfigurado($config) || empty($config['task_write_enabled'])) {
        goHighLevelLanzar(
            'tareas_no_habilitadas',
            'La gestion de tareas todavia no esta habilitada en la conexion privada.',
            array(),
            503
        );
    }
    $metodo = strtoupper(trim((string)$metodo));
    $contactId = goHighLevelIdSeguro($contactId);
    $taskId = goHighLevelIdSeguro($taskId);
    if ($contactId === '' || !in_array($metodo, array('POST', 'PUT'), true)) {
        goHighLevelLanzar('tarea_invalida', 'La operacion de tarea no es valida.', array(), 400);
    }
    if ($metodo === 'POST') {
        $ruta = '/contacts/'.rawurlencode($contactId).'/tasks';
    } else {
        if ($taskId === '') {
            goHighLevelLanzar('tarea_invalida', 'La tarea seleccionada no es valida.', array(), 400);
        }
        $ruta = '/contacts/'.rawurlencode($contactId).'/tasks/'.rawurlencode($taskId)
            .($completar ? '/completed' : '');
    }
    if (!function_exists('curl_init')) {
        goHighLevelLanzar('cliente_http_no_disponible', 'El servidor no tiene habilitado el cliente seguro.', array(), 503);
    }
    $cuerpoJson = json_encode($entrada, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($cuerpoJson)) {
        goHighLevelLanzar('tarea_invalida', 'No se pudo preparar la tarea.', array(), 400);
    }
    $curl = curl_init($config['base'].$ruta);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_POSTFIELDS => $cuerpoJson,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '.$config['token'],
            'Version: v3',
            'User-Agent: Sistema-Telar-GoHighLevel/3.0'
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
    if ($cuerpo === false || $errorNumero !== 0) {
        error_log('GoHighLevel: fallo de red en operacion de tarea (curl '.$errorNumero.')');
        goHighLevelLanzar('tarea_no_disponible', 'No se pudo conectar con GoHighLevel para gestionar la tarea.', array(), 502);
    }
    $datos = json_decode($cuerpo, true);
    if ($estado >= 200 && $estado < 300 && trim((string)$cuerpo) === '') {
        $datos = array(
            'id' => $taskId,
            'contactId' => $contactId,
            'completed' => !empty($entrada['completed'])
        );
    }
    if ($estado < 200 || $estado >= 300 || !is_array($datos)) {
        error_log('GoHighLevel: respuesta HTTP '.$estado.' en operacion de tarea');
        $mensaje = $estado === 401 || $estado === 403
            ? 'La integracion privada no tiene el permiso contacts.write.'
            : 'GoHighLevel no pudo completar la operacion de tarea.';
        goHighLevelLanzar('tarea_rechazada', $mensaje, array('estado' => $estado), 502);
    }
    return $datos;
}

function goHighLevelItems($respuesta, $claves)
{
    foreach ((array)$claves as $clave) {
        if (isset($respuesta[$clave]) && is_array($respuesta[$clave])) {
            return $respuesta[$clave];
        }
    }
    return array();
}

function goHighLevelTotal($respuesta, $items)
{
    $meta = isset($respuesta['meta']) && is_array($respuesta['meta']) ? $respuesta['meta'] : array();
    $total = goHighLevelValor($meta, array('total', 'totalItems', 'count'), null);
    if ($total === null) {
        $total = goHighLevelValor($respuesta, array('total', 'count'), count($items));
    }
    return max(0, intval($total));
}

function goHighLevelLimite($valor, $predeterminado)
{
    $limite = intval($valor);
    if ($limite <= 0) {
        $limite = intval($predeterminado);
    }
    return max(1, min(100, $limite));
}

function goHighLevelBusqueda($parametros)
{
    return goHighLevelTexto(isset($parametros['buscar']) ? $parametros['buscar'] : '', 75);
}

function goHighLevelIdSeguro($valor)
{
    $id = trim((string)$valor);
    return preg_match('/^[A-Za-z0-9_-]{8,80}$/', $id) ? $id : '';
}

function goHighLevelMarcaTiempo($valor)
{
    $texto = trim((string)$valor);
    if (preg_match('/^[0-9]{10,16}$/', $texto)) {
        return $texto;
    }
    $tiempo = $texto !== '' ? strtotime($texto) : false;
    return $tiempo !== false ? (string)($tiempo * 1000) : '';
}

function goHighLevelSegundos($valor)
{
    $marca = goHighLevelMarcaTiempo($valor);
    if ($marca === '' || !is_numeric($marca)) {
        return 0;
    }
    $segundos = (float)$marca;
    while ($segundos > 20000000000) {
        $segundos = floor($segundos / 1000);
    }
    $segundos = intval($segundos);
    return $segundos >= 946684800 && $segundos <= 4102444800 ? $segundos : 0;
}

function goHighLevelVentanaWhatsApp($mensajes)
{
    $ultimoInbound = 0;
    foreach ((array)$mensajes as $mensaje) {
        if (!is_array($mensaje)) {
            continue;
        }
        $direccion = strtolower(trim((string)goHighLevelValor($mensaje, array('direccion', 'direction'), '')));
        $tipo = strtolower(trim((string)goHighLevelValor($mensaje, array('tipo', 'messageType', 'type'), '')));
        if ($direccion !== 'inbound' || strpos($tipo, 'whatsapp') === false) {
            continue;
        }
        $fecha = goHighLevelSegundos(goHighLevelValor($mensaje, array('fecha', 'dateAdded', 'createdAt'), ''));
        if ($fecha > $ultimoInbound) {
            $ultimoInbound = $fecha;
        }
    }
    $ahora = time();
    $vence = $ultimoInbound > 0 ? $ultimoInbound + 86400 : 0;
    $abierta = $ultimoInbound > 0 && $ultimoInbound <= ($ahora + 300) && $vence > $ahora;
    return array(
        'abierta' => $abierta,
        'ultimo_inbound' => $ultimoInbound > 0 ? date('c', $ultimoInbound) : '',
        'vence' => $vence > 0 ? date('c', $vence) : '',
        'segundos_restantes' => $abierta ? max(0, $vence - $ahora) : 0,
        'requiere_plantilla' => !$abierta
    );
}

function goHighLevelHayMas($total, $cantidad, $limite)
{
    return $cantidad >= $limite && ($total <= 0 || $cantidad < $total);
}

function goHighLevelEmailNormalizado($valor)
{
    $email = strtolower(trim((string)$valor));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function goHighLevelUsuariosApi($config)
{
    $items = array();
    $companyId = isset($config['company_id']) ? goHighLevelIdSeguro($config['company_id']) : '';
    if ($companyId !== '') {
        try {
            $skip = 0;
            do {
                $respuesta = goHighLevelApiGet($config, '/users/search', array(
                    'companyId' => $companyId,
                    'locationId' => $config['location_id'],
                    'skip' => $skip,
                    'limit' => 100
                ), 'v3');
                $pagina = goHighLevelItems($respuesta, array('users'));
                foreach ($pagina as $item) {
                    if (is_array($item)) {
                        $items[] = $item;
                    }
                }
                $skip += count($pagina);
                $total = goHighLevelTotal($respuesta, $pagina);
            } while (count($pagina) === 100 && $skip < $total && $skip < 1000);
            return $items;
        } catch (GoHighLevelExcepcion $e) {
            error_log('GoHighLevel: busqueda v3 de usuarios no disponible; se intenta la ruta compatible');
        }
    }
    $respuesta = goHighLevelApiGet($config, '/users/', array(
        'locationId' => $config['location_id']
    ), '2021-07-28');
    return goHighLevelItems($respuesta, array('users'));
}

function goHighLevelUsuariosTelarActivos($mysqli)
{
    $resultado = $mysqli->query(
        "SELECT u.cod_usuario,IFNULL(p.nombre_persona,u.login) nombre,IFNULL(p.email,'') email,"
        ."IFNULL(u.url,'') avatar,IFNULL(l.Nombre,'') local "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."WHERE UPPER(TRIM(u.estado))='ACTIVO' ORDER BY nombre"
    );
    if (!$resultado) {
        goHighLevelLanzar('usuarios_telar_no_disponibles', 'No se pudo cargar el equipo de Telar.', array(), 500);
    }
    $items = array();
    while ($fila = $resultado->fetch_assoc()) {
        $items[] = array(
            'cod_usuario' => intval($fila['cod_usuario']),
            'nombre' => (string)$fila['nombre'],
            'email' => goHighLevelEmailNormalizado($fila['email']),
            'avatar' => (string)$fila['avatar'],
            'local' => (string)$fila['local']
        );
    }
    return $items;
}

function goHighLevelSincronizarUsuarios($mysqli, $config, $contexto)
{
    $usuariosGhl = goHighLevelUsuariosApi($config);
    $usuariosTelar = goHighLevelUsuariosTelarActivos($mysqli);
    $porCorreo = array();
    foreach ($usuariosTelar as $usuarioTelar) {
        $email = $usuarioTelar['email'];
        if ($email === '') {
            continue;
        }
        if (!isset($porCorreo[$email])) {
            $porCorreo[$email] = array();
        }
        $porCorreo[$email][] = $usuarioTelar;
    }
    $manuales = array();
    $usados = array();
    $resultadoManual = $mysqli->query(
        "SELECT ghl_user_id,cod_usuarioFK,estado,fuente FROM gohighlevel_usuario_vinculo"
    );
    while ($resultadoManual && ($filaManual = $resultadoManual->fetch_assoc())) {
        if ((string)$filaManual['fuente'] === 'manual') {
            $manuales[(string)$filaManual['ghl_user_id']] = $filaManual;
            if (intval($filaManual['cod_usuarioFK']) > 0) {
                $usados[intval($filaManual['cod_usuarioFK'])] = true;
            }
        }
    }
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_usuario_vinculo "
        ."(ghl_user_id,cod_usuarioFK,nombre_ghl,email_hash,avatar_ghl,estado,fuente,"
        ."cod_usuario_actualizaFK,fecha_vinculacion,fecha_creacion,fecha_actualizacion) "
        ."VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE "
        ."cod_usuarioFK=VALUES(cod_usuarioFK),nombre_ghl=VALUES(nombre_ghl),"
        ."email_hash=VALUES(email_hash),avatar_ghl=VALUES(avatar_ghl),estado=VALUES(estado),"
        ."fuente=VALUES(fuente),cod_usuario_actualizaFK=VALUES(cod_usuario_actualizaFK),"
        ."fecha_vinculacion=VALUES(fecha_vinculacion),fecha_actualizacion=NOW()"
    );
    if (!$stmt) {
        goHighLevelLanzar('usuarios_ghl_no_disponibles', 'No se pudo preparar la vinculacion de usuarios.', array(), 500);
    }
    $actor = intval($contexto['cod_usuario']);
    foreach ($usuariosGhl as $usuarioGhl) {
        if (!is_array($usuarioGhl) || !empty($usuarioGhl['deleted'])) {
            continue;
        }
        $id = goHighLevelIdSeguro(goHighLevelValor($usuarioGhl, array('id', '_id'), ''));
        if ($id === '') {
            continue;
        }
        $nombre = trim((string)goHighLevelValor($usuarioGhl, array('name'), ''));
        if ($nombre === '') {
            $nombre = trim((string)goHighLevelValor($usuarioGhl, array('firstName'), '').' '
                .(string)goHighLevelValor($usuarioGhl, array('lastName'), ''));
        }
        $nombre = goHighLevelTexto($nombre !== '' ? $nombre : 'Usuario GoHighLevel', 180);
        $email = goHighLevelEmailNormalizado(goHighLevelValor($usuarioGhl, array('email'), ''));
        $emailHash = $email !== '' ? hash('sha256', $email) : '';
        $avatar = goHighLevelTexto(goHighLevelValor($usuarioGhl, array('profilePhoto', 'avatar', 'photo')), 500);
        $codUsuario = null;
        $estado = 'sin_coincidencia';
        $fuente = 'correo_exacto';
        if (isset($manuales[$id])) {
            $codManual = intval($manuales[$id]['cod_usuarioFK']);
            $codUsuario = $codManual > 0 ? $codManual : null;
            $estado = $codUsuario ? 'vinculado' : 'sin_coincidencia';
            $fuente = 'manual';
        } elseif ($email !== '' && isset($porCorreo[$email]) && count($porCorreo[$email]) === 1) {
            $candidato = intval($porCorreo[$email][0]['cod_usuario']);
            if (!isset($usados[$candidato])) {
                $codUsuario = $candidato;
                $estado = 'vinculado';
                $usados[$candidato] = true;
            } else {
                $estado = 'ambiguo';
            }
        } elseif ($email !== '' && isset($porCorreo[$email]) && count($porCorreo[$email]) > 1) {
            $estado = 'ambiguo';
        }
        $fechaVinculo = $estado === 'vinculado' ? date('Y-m-d H:i:s') : null;
        $stmt->bind_param(
            'sisssssis',
            $id,
            $codUsuario,
            $nombre,
            $emailHash,
            $avatar,
            $estado,
            $fuente,
            $actor,
            $fechaVinculo
        );
        if (!$stmt->execute()) {
            $stmt->close();
            goHighLevelLanzar('usuarios_ghl_no_disponibles', 'No se pudo actualizar la vinculacion de usuarios.', array(), 500);
        }
    }
    $stmt->close();
}

function goHighLevelCatalogoUsuariosLocal($mysqli, $contexto, $incluirTelar = false)
{
    $resultado = $mysqli->query(
        "SELECT uv.ghl_user_id,uv.nombre_ghl,uv.avatar_ghl,uv.estado,uv.fuente,"
        ."IFNULL(uv.cod_usuarioFK,0) cod_usuario,"
        ."IFNULL(p.nombre_persona,u.login) nombre_telar,IFNULL(u.url,'') avatar_telar,"
        ."IFNULL(l.Nombre,'') local "
        ."FROM gohighlevel_usuario_vinculo uv "
        ."LEFT JOIN usuario u ON u.cod_usuario=uv.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."ORDER BY uv.nombre_ghl"
    );
    if (!$resultado) {
        goHighLevelLanzar('usuarios_ghl_no_disponibles', 'No se pudo cargar el equipo de GoHighLevel.', array(), 500);
    }
    $items = array();
    $actual = '';
    while ($fila = $resultado->fetch_assoc()) {
        $codUsuario = intval($fila['cod_usuario']);
        if ($codUsuario === intval($contexto['cod_usuario']) && (string)$fila['estado'] === 'vinculado') {
            $actual = (string)$fila['ghl_user_id'];
        }
        $items[] = array(
            'id' => (string)$fila['ghl_user_id'],
            'nombre' => (string)$fila['nombre_ghl'],
            'avatar' => (string)$fila['avatar_ghl'],
            'estado' => (string)$fila['estado'],
            'fuente' => (string)$fila['fuente'],
            'cod_usuario' => $codUsuario,
            'nombre_telar' => (string)$fila['nombre_telar'],
            'avatar_telar' => (string)$fila['avatar_telar'],
            'local' => (string)$fila['local']
        );
    }
    $salida = array('items' => $items, 'usuario_actual_ghl_id' => $actual);
    if ($incluirTelar) {
        $salida['usuarios_telar'] = array_map(function ($usuario) {
            unset($usuario['email']);
            return $usuario;
        }, goHighLevelUsuariosTelarActivos($mysqli));
    }
    return $salida;
}

function goHighLevelCatalogoUsuarios($mysqli, $config, $contexto, $incluirTelar = false)
{
    $advertencia = '';
    try {
        goHighLevelSincronizarUsuarios($mysqli, $config, $contexto);
    } catch (GoHighLevelExcepcion $e) {
        $advertencia = $e->getMessage();
    }
    $salida = goHighLevelCatalogoUsuariosLocal($mysqli, $contexto, $incluirTelar);
    if ($advertencia !== '') {
        $salida['advertencia'] = $advertencia;
    }
    return $salida;
}

function goHighLevelGuardarVinculosUsuarios($mysqli, $config, $contexto, $entrada)
{
    if (empty($contexto['puede_configurar'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para vincular usuarios.', array(), 403);
    }
    $lista = json_decode((string)$entrada, true);
    if (!is_array($lista) || count($lista) > 250) {
        goHighLevelLanzar('vinculos_invalidos', 'La vinculacion de usuarios no es valida.', array(), 400);
    }
    $validos = array();
    $resultado = $mysqli->query("SELECT ghl_user_id FROM gohighlevel_usuario_vinculo");
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $validos[(string)$fila['ghl_user_id']] = true;
    }
    $usuariosActivos = array();
    foreach (goHighLevelUsuariosTelarActivos($mysqli) as $usuario) {
        $usuariosActivos[intval($usuario['cod_usuario'])] = true;
    }
    $asignados = array();
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_usuario_vinculo SET cod_usuarioFK=?,estado=?,fuente='manual',"
        ."cod_usuario_actualizaFK=?,fecha_vinculacion=?,fecha_actualizacion=NOW() "
        ."WHERE ghl_user_id=? LIMIT 1"
    );
    if (!$stmt) {
        goHighLevelLanzar('vinculos_no_guardados', 'No se pudo preparar la vinculacion de usuarios.', array(), 500);
    }
    $actor = intval($contexto['cod_usuario']);
    $procesables = array();
    foreach ($lista as $item) {
        $id = goHighLevelIdSeguro(goHighLevelValor($item, array('ghl_user_id'), ''));
        $codUsuario = intval(goHighLevelValor($item, array('cod_usuario'), 0));
        if ($id === '' || !isset($validos[$id])) {
            continue;
        }
        if ($codUsuario > 0 && (!isset($usuariosActivos[$codUsuario]) || isset($asignados[$codUsuario]))) {
            $stmt->close();
            goHighLevelLanzar(
                'vinculos_no_guardados',
                'La vinculacion contiene usuarios repetidos o inactivos.',
                array(),
                422
            );
        }
        if ($codUsuario > 0) {
            $asignados[$codUsuario] = true;
        }
        $procesables[] = array('id' => $id, 'cod_usuario' => $codUsuario);
    }
    $mysqli->begin_transaction();
    try {
        $limpiar = $mysqli->prepare(
            "UPDATE gohighlevel_usuario_vinculo SET cod_usuarioFK=NULL,estado='sin_coincidencia',"
            ."fuente='manual',cod_usuario_actualizaFK=?,fecha_vinculacion=NULL,fecha_actualizacion=NOW() "
            ."WHERE ghl_user_id=? LIMIT 1"
        );
        if (!$limpiar) {
            throw new Exception('No se pudieron preparar los vinculos existentes.');
        }
        foreach ($procesables as $item) {
            $id = $item['id'];
            $limpiar->bind_param('is', $actor, $id);
            if (!$limpiar->execute()) {
                throw new Exception('No se pudo preparar un vinculo existente.');
            }
        }
        $limpiar->close();
        foreach ($procesables as $item) {
            $id = $item['id'];
            $codUsuario = intval($item['cod_usuario']);
            $codGuardar = $codUsuario > 0 ? $codUsuario : null;
            $estado = $codUsuario > 0 ? 'vinculado' : 'sin_coincidencia';
            $fecha = $codUsuario > 0 ? date('Y-m-d H:i:s') : null;
            $stmt->bind_param('isiss', $codGuardar, $estado, $actor, $fecha, $id);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar un vinculo.');
            }
        }
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'usuarios_vinculados',
            'usuarios_ghl',
            $config['location_id'],
            'Vinculos revisados: '.count($procesables)
        );
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        $stmt->close();
        goHighLevelLanzar('vinculos_no_guardados', $e->getMessage(), array(), 422);
    }
    $stmt->close();
    return goHighLevelCatalogoUsuariosLocal($mysqli, $contexto, true);
}

function goHighLevelNombreContacto($contacto)
{
    $nombre = goHighLevelTexto(goHighLevelValor($contacto, array('contactName', 'fullName', 'name')), 180);
    if ($nombre !== '') {
        return $nombre;
    }
    $partes = array(
        goHighLevelTexto(goHighLevelValor($contacto, array('firstName', 'first_name')), 90),
        goHighLevelTexto(goHighLevelValor($contacto, array('lastName', 'last_name')), 90)
    );
    $nombre = trim(implode(' ', array_filter($partes)));
    return $nombre !== '' ? $nombre : 'Contacto sin nombre';
}

function goHighLevelBuscarPacienteTelefono($mysqli, $telefono)
{
    $normalizado = centralTelefonicaNormalizarTelefono($telefono);
    $salida = array(
        'estado' => 'sin_coincidencia',
        'coincidencias' => 0,
        'telefono_normalizado' => $normalizado,
        'paciente' => null
    );
    if ($normalizado === '' || !goHighLevelTablaExiste($mysqli, 'central_telefonica_paciente_telefono')) {
        return $salida;
    }
    $stmt = $mysqli->prepare(
        "SELECT DISTINCT ct.cod_clienteFK,IFNULL(p.nombre_persona,'') nombre "
        ."FROM central_telefonica_paciente_telefono ct "
        ."INNER JOIN cliente c ON c.cod_cliente=ct.cod_clienteFK "
        ."INNER JOIN persona p ON p.cod_persona=c.cod_cliente "
        ."WHERE ct.telefono_normalizado=? AND ct.activo=1 LIMIT 3"
    );
    if (!$stmt) {
        return $salida;
    }
    $stmt->bind_param('s', $normalizado);
    $filas = array();
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($resultado && ($fila = $resultado->fetch_assoc())) {
            $filas[] = $fila;
        }
    }
    $stmt->close();
    $salida['coincidencias'] = count($filas);
    if (count($filas) === 1) {
        $salida['estado'] = 'vinculado';
        $salida['paciente'] = array(
            'cod_cliente' => intval($filas[0]['cod_clienteFK']),
            'nombre' => (string)$filas[0]['nombre'],
            'avatar' => ''
        );
    } elseif (count($filas) > 1) {
        $salida['estado'] = 'ambiguo';
    }
    return $salida;
}

function goHighLevelGuardarVinculo($mysqli, $contactId, $vinculo)
{
    if (!goHighLevelTablaExiste($mysqli, 'gohighlevel_vinculo_contacto')) {
        return;
    }
    $contactId = goHighLevelTexto($contactId, 80);
    if ($contactId === '') {
        return;
    }
    $cliente = isset($vinculo['paciente']['cod_cliente']) ? intval($vinculo['paciente']['cod_cliente']) : 0;
    $clienteSql = $cliente > 0 ? (string)$cliente : 'NULL';
    $telefono = goHighLevelTexto($vinculo['telefono_normalizado'], 32);
    $estado = goHighLevelTexto($vinculo['estado'], 24);
    $coincidencias = max(0, intval($vinculo['coincidencias']));
    $sql = "INSERT INTO gohighlevel_vinculo_contacto "
        ."(ghl_contact_id,cod_clienteFK,telefono_normalizado,estado,coincidencias,fuente,fecha_vinculacion,fecha_actualizacion) "
        ."VALUES (?,".$clienteSql.",?,?,?,'telefono_exacto',IF(?='vinculado',NOW(),NULL),NOW()) "
        ."ON DUPLICATE KEY UPDATE cod_clienteFK=VALUES(cod_clienteFK),"
        ."telefono_normalizado=VALUES(telefono_normalizado),estado=VALUES(estado),"
        ."coincidencias=VALUES(coincidencias),fuente=VALUES(fuente),"
        ."fecha_vinculacion=IF(VALUES(estado)='vinculado',IFNULL(fecha_vinculacion,NOW()),NULL),"
        ."fecha_actualizacion=NOW()";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('sssis', $contactId, $telefono, $estado, $coincidencias, $estado);
    $stmt->execute();
    $stmt->close();
}

function goHighLevelFormatearContacto($mysqli, $contacto, $guardarVinculo)
{
    $id = goHighLevelTexto(goHighLevelValor($contacto, array('id', '_id', 'contactId')), 80);
    $telefono = goHighLevelTexto(goHighLevelValor($contacto, array('phone', 'phoneNumber')), 60);
    $vinculo = goHighLevelBuscarPacienteTelefono($mysqli, $telefono);
    if ($guardarVinculo && $id !== '') {
        goHighLevelGuardarVinculo($mysqli, $id, $vinculo);
    }
    $etiquetas = goHighLevelValor($contacto, array('tags'), array());
    if (!is_array($etiquetas)) {
        $etiquetas = array();
    }
    return array(
        'id' => $id,
        'nombre' => goHighLevelNombreContacto($contacto),
        'telefono' => $telefono,
        'email' => goHighLevelTexto(goHighLevelValor($contacto, array('email')), 180),
        'avatar' => goHighLevelTexto(goHighLevelValor($contacto, array('profilePhoto', 'profilePhotoUrl', 'photo')), 500),
        'etiquetas' => array_values(array_slice($etiquetas, 0, 12)),
        'fecha_alta' => goHighLevelTexto(goHighLevelValor($contacto, array('dateAdded', 'createdAt')), 40),
        'vinculo' => $vinculo
    );
}

function goHighLevelListarContactos($mysqli, $config, $parametros)
{
    $limite = goHighLevelLimite(isset($parametros['limite']) ? $parametros['limite'] : 50, 50);
    $buscar = goHighLevelBusqueda($parametros);
    $apiParametros = array(
        'locationId' => $config['location_id'],
        'limit' => $limite
    );
    if ($buscar !== '') {
        $apiParametros['query'] = $buscar;
    }
    $cursorFecha = goHighLevelMarcaTiempo(isset($parametros['cursor_fecha']) ? $parametros['cursor_fecha'] : '');
    $cursorId = goHighLevelIdSeguro(isset($parametros['cursor_id']) ? $parametros['cursor_id'] : '');
    if ($cursorFecha !== '' && $cursorId !== '') {
        $apiParametros['startAfter'] = $cursorFecha;
        $apiParametros['startAfterId'] = $cursorId;
    }
    $respuesta = goHighLevelApiGet($config, '/contacts/', $apiParametros);
    $items = goHighLevelItems($respuesta, array('contacts'));
    $contactos = array();
    foreach ($items as $item) {
        if (is_array($item)) {
            $contactos[] = goHighLevelFormatearContacto($mysqli, $item, true);
        }
    }
    $total = goHighLevelTotal($respuesta, $items);
    $ultimo = count($items) > 0 && is_array($items[count($items) - 1]) ? $items[count($items) - 1] : array();
    $siguienteFecha = goHighLevelMarcaTiempo(goHighLevelValor($ultimo, array('dateAdded', 'createdAt'), ''));
    $siguienteId = goHighLevelIdSeguro(goHighLevelValor($ultimo, array('id', '_id', 'contactId'), ''));
    $hayMas = goHighLevelHayMas($total, count($items), $limite) && $siguienteFecha !== '' && $siguienteId !== '';
    return array(
        'items' => $contactos,
        'total' => $total,
        'busqueda' => $buscar,
        'paginacion' => array(
            'hay_mas' => $hayMas,
            'cursor_fecha' => $hayMas ? $siguienteFecha : '',
            'cursor_id' => $hayMas ? $siguienteId : '',
            'mostrados' => count($contactos)
        )
    );
}

function goHighLevelContactosPorId($contactos)
{
    $mapa = array();
    foreach ($contactos as $contacto) {
        if (isset($contacto['id']) && $contacto['id'] !== '') {
            $mapa[$contacto['id']] = $contacto;
        }
    }
    return $mapa;
}

function goHighLevelMapaUsuariosLocal($mysqli)
{
    $mapa = array();
    $resultado = $mysqli->query(
        "SELECT uv.ghl_user_id,uv.nombre_ghl,uv.avatar_ghl,uv.estado,"
        ."IFNULL(uv.cod_usuarioFK,0) cod_usuario,IFNULL(u.url,'') avatar_telar,"
        ."IFNULL(p.nombre_persona,u.login) nombre_telar "
        ."FROM gohighlevel_usuario_vinculo uv "
        ."LEFT JOIN usuario u ON u.cod_usuario=uv.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $id = (string)$fila['ghl_user_id'];
        $nombreTelar = trim((string)$fila['nombre_telar']);
        $mapa[$id] = array(
            'id' => $id,
            'nombre' => $nombreTelar !== '' ? $nombreTelar : (string)$fila['nombre_ghl'],
            'avatar' => (string)$fila['avatar_telar'] !== ''
                ? (string)$fila['avatar_telar'] : (string)$fila['avatar_ghl'],
            'cod_usuario' => intval($fila['cod_usuario']),
            'vinculado' => (string)$fila['estado'] === 'vinculado'
        );
    }
    return $mapa;
}

function goHighLevelRegistrarUsuarioObservado($mysqli, $id, $nombre)
{
    $id = goHighLevelIdSeguro($id);
    $nombre = goHighLevelTexto($nombre, 180);
    if ($id === '' || $nombre === '') {
        return;
    }
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_usuario_vinculo "
        ."(ghl_user_id,cod_usuarioFK,nombre_ghl,email_hash,avatar_ghl,estado,fuente,"
        ."cod_usuario_actualizaFK,fecha_vinculacion,fecha_creacion,fecha_actualizacion) "
        ."VALUES (?,NULL,?,'','','sin_coincidencia','observado',NULL,NULL,NOW(),NOW()) "
        ."ON DUPLICATE KEY UPDATE nombre_ghl=IF(nombre_ghl='',VALUES(nombre_ghl),nombre_ghl),"
        ."fecha_actualizacion=NOW()"
    );
    if ($stmt) {
        $stmt->bind_param('ss', $id, $nombre);
        $stmt->execute();
        $stmt->close();
    }
}

function goHighLevelFiltroResponsableConversacion($mysqli, $contexto, $valor)
{
    $valor = trim((string)$valor);
    $actual = isset($contexto['ghl_user_id']) ? goHighLevelIdSeguro($contexto['ghl_user_id']) : '';
    if (empty($contexto['puede_ver_equipo'])) {
        if ($valor === 'unassigned') {
            return 'unassigned';
        }
        return $actual !== '' ? $actual.',unassigned' : 'unassigned';
    }
    if ($valor === '' || $valor === 'all') {
        return '';
    }
    if ($valor === 'mine') {
        return $actual !== '' ? $actual : 'unassigned';
    }
    if ($valor === 'unassigned') {
        return 'unassigned';
    }
    $id = goHighLevelIdSeguro($valor);
    if ($id === '') {
        return '';
    }
    $stmt = $mysqli->prepare("SELECT COUNT(*) total FROM gohighlevel_usuario_vinculo WHERE ghl_user_id=?");
    $total = 0;
    if ($stmt) {
        $stmt->bind_param('s', $id);
        if ($stmt->execute()) {
            $stmt->bind_result($total);
            $stmt->fetch();
        }
        $stmt->close();
    }
    return intval($total) > 0 ? $id : '';
}

function goHighLevelListarConversaciones($mysqli, $config, $parametros, $contexto)
{
    $limite = goHighLevelLimite(isset($parametros['limite']) ? $parametros['limite'] : 40, 40);
    $buscar = goHighLevelBusqueda($parametros);
    $filtroResponsable = goHighLevelFiltroResponsableConversacion(
        $mysqli,
        $contexto,
        isset($parametros['assigned_to']) ? $parametros['assigned_to'] : ''
    );
    $filtroEstado = strtolower(trim((string)(isset($parametros['estado']) ? $parametros['estado'] : 'all')));
    if (!in_array($filtroEstado, array('all', 'read', 'unread', 'starred', 'recents'), true)) {
        $filtroEstado = 'all';
    }
    $apiParametros = array(
        'locationId' => $config['location_id'],
        'limit' => $limite,
        'sort' => 'desc'
    );
    if ($buscar !== '') {
        $apiParametros['query'] = $buscar;
    }
    if ($filtroResponsable !== '') {
        $apiParametros['assignedTo'] = $filtroResponsable;
    }
    if ($filtroEstado !== 'all') {
        $apiParametros['status'] = $filtroEstado;
    }
    $cursorFecha = goHighLevelMarcaTiempo(isset($parametros['cursor_fecha']) ? $parametros['cursor_fecha'] : '');
    if ($cursorFecha !== '') {
        $apiParametros['startAfterDate'] = $cursorFecha;
    }
    $respuesta = goHighLevelApiGet($config, '/conversations/search', $apiParametros);
    $items = goHighLevelItems($respuesta, array('conversations'));
    $usuarios = goHighLevelMapaUsuariosLocal($mysqli);
    $conversaciones = array();
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $contactId = goHighLevelTexto(goHighLevelValor($item, array('contactId', 'contact_id')), 80);
        $contactoCrudo = $item;
        if ($contactId !== '') {
            $contactoCrudo['id'] = $contactId;
        }
        $contacto = goHighLevelFormatearContacto($mysqli, $contactoCrudo, $contactId !== '');
        $nombre = $contacto['nombre'];
        $responsableId = goHighLevelIdSeguro(goHighLevelValor($item, array('assignedTo', 'userId'), ''));
        $responsableApi = goHighLevelTexto(goHighLevelValor($item, array('assignedUserName')), 120);
        goHighLevelRegistrarUsuarioObservado($mysqli, $responsableId, $responsableApi);
        $responsable = isset($usuarios[$responsableId]) ? $usuarios[$responsableId] : array();
        $conversaciones[] = array(
            'id' => goHighLevelTexto(goHighLevelValor($item, array('id', '_id')), 80),
            'contact_id' => $contactId,
            'nombre' => $nombre,
            'telefono' => $contacto['telefono'],
            'avatar' => $contacto['avatar'],
            'vinculo' => $contacto['vinculo'],
            'ultimo_mensaje' => goHighLevelTexto(goHighLevelValor($item, array('lastMessageBody', 'lastMessage', 'message')), 500),
            'fecha' => goHighLevelTexto(goHighLevelValor($item, array('lastMessageDate', 'dateUpdated', 'updatedAt')), 40),
            'canal' => goHighLevelTexto(goHighLevelValor($item, array('lastMessageType', 'type', 'channel')), 40),
            'no_leidos' => max(0, intval(goHighLevelValor($item, array('unreadCount'), 0))),
            'responsable_id' => $responsableId,
            'responsable' => isset($responsable['nombre']) ? $responsable['nombre'] : $responsableApi,
            'responsable_avatar' => isset($responsable['avatar']) ? $responsable['avatar'] : '',
            'responsable_vinculado' => !empty($responsable['vinculado']),
            'estado' => goHighLevelTexto(goHighLevelValor($item, array('status')), 40)
        );
    }
    $total = goHighLevelTotal($respuesta, $items);
    $ultimo = count($items) > 0 && is_array($items[count($items) - 1]) ? $items[count($items) - 1] : array();
    $siguienteFecha = goHighLevelMarcaTiempo(goHighLevelValor($ultimo, array('lastMessageDate', 'dateUpdated', 'updatedAt'), ''));
    $hayMas = goHighLevelHayMas($total, count($items), $limite) && $siguienteFecha !== '';
    return array(
        'items' => $conversaciones,
        'total' => $total,
        'busqueda' => $buscar,
        'filtro_responsable' => $filtroResponsable,
        'filtro_estado' => $filtroEstado,
        'usuarios' => array_values(goHighLevelMapaUsuariosLocal($mysqli)),
        'paginacion' => array(
            'hay_mas' => $hayMas,
            'cursor_fecha' => $hayMas ? $siguienteFecha : '',
            'cursor_id' => '',
            'mostrados' => count($conversaciones)
        )
    );
}

function goHighLevelListarOportunidades($mysqli, $config, $parametros)
{
    $limite = goHighLevelLimite(isset($parametros['limite']) ? $parametros['limite'] : 60, 60);
    $buscar = goHighLevelBusqueda($parametros);
    $pagina = isset($parametros['pagina']) ? intval($parametros['pagina']) : 1;
    $pagina = max(1, min(100000, $pagina));
    $pipelinesRespuesta = goHighLevelApiGet($config, '/opportunities/pipelines', array(
        'locationId' => $config['location_id']
    ));
    $pipelinesItems = goHighLevelItems($pipelinesRespuesta, array('pipelines'));
    $pipelines = array();
    foreach ($pipelinesItems as $pipeline) {
        if (!is_array($pipeline)) {
            continue;
        }
        $etapas = array();
        foreach ((array)goHighLevelValor($pipeline, array('stages'), array()) as $etapa) {
            if (is_array($etapa)) {
                $etapas[] = array(
                    'id' => goHighLevelTexto(goHighLevelValor($etapa, array('id', '_id')), 80),
                    'nombre' => goHighLevelTexto(goHighLevelValor($etapa, array('name')), 140)
                );
            }
        }
        $pipelines[] = array(
            'id' => goHighLevelTexto(goHighLevelValor($pipeline, array('id', '_id')), 80),
            'nombre' => goHighLevelTexto(goHighLevelValor($pipeline, array('name')), 140),
            'etapas' => $etapas
        );
    }
    $apiParametros = array(
        'location_id' => $config['location_id'],
        'limit' => $limite,
        'page' => $pagina
    );
    if ($buscar !== '') {
        $apiParametros['q'] = $buscar;
    }
    $respuesta = goHighLevelApiGet($config, '/opportunities/search', $apiParametros);
    $items = goHighLevelItems($respuesta, array('opportunities'));
    $oportunidades = array();
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $oportunidades[] = array(
            'id' => goHighLevelTexto(goHighLevelValor($item, array('id', '_id')), 80),
            'nombre' => goHighLevelTexto(goHighLevelValor($item, array('name', 'contactName')), 180),
            'contact_id' => goHighLevelTexto(goHighLevelValor($item, array('contactId', 'contact_id')), 80),
            'pipeline_id' => goHighLevelTexto(goHighLevelValor($item, array('pipelineId', 'pipeline_id')), 80),
            'etapa_id' => goHighLevelTexto(goHighLevelValor($item, array('pipelineStageId', 'stageId')), 80),
            'estado' => goHighLevelTexto(goHighLevelValor($item, array('status')), 40),
            'valor' => floatval(goHighLevelValor($item, array('monetaryValue', 'value'), 0)),
            'fecha' => goHighLevelTexto(goHighLevelValor($item, array('updatedAt', 'dateUpdated', 'createdAt')), 40)
        );
    }
    $total = goHighLevelTotal($respuesta, $items);
    return array(
        'items' => $oportunidades,
        'total' => $total,
        'pipelines' => $pipelines,
        'busqueda' => $buscar,
        'paginacion' => array(
            'hay_mas' => count($items) >= $limite && ($pagina * $limite) < $total,
            'pagina' => $pagina,
            'siguiente_pagina' => $pagina + 1,
            'mostrados' => count($oportunidades)
        )
    );
}

function goHighLevelListarMensajesConversacion($config, $parametros)
{
    $conversationId = goHighLevelIdSeguro(isset($parametros['conversation_id']) ? $parametros['conversation_id'] : '');
    if ($conversationId === '') {
        goHighLevelLanzar('conversacion_invalida', 'La conversacion seleccionada no es valida.', array(), 400);
    }
    $limite = goHighLevelLimite(isset($parametros['limite']) ? $parametros['limite'] : 50, 50);
    $apiParametros = array('limit' => $limite);
    $ultimoMensajeId = goHighLevelIdSeguro(isset($parametros['last_message_id']) ? $parametros['last_message_id'] : '');
    if ($ultimoMensajeId !== '') {
        $apiParametros['lastMessageId'] = $ultimoMensajeId;
    }
    $respuesta = goHighLevelApiGet(
        $config,
        '/conversations/'.rawurlencode($conversationId).'/messages',
        $apiParametros
    );
    $contenedor = isset($respuesta['messages']) && is_array($respuesta['messages'])
        ? $respuesta['messages'] : array();
    $items = isset($contenedor['messages']) && is_array($contenedor['messages'])
        ? $contenedor['messages'] : array();
    $mensajes = array();
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $cuerpo = (string)goHighLevelValor($item, array('body', 'message', 'text'), '');
        $cuerpo = html_entity_decode(strip_tags($cuerpo), ENT_QUOTES, 'UTF-8');
        $adjuntos = goHighLevelValor($item, array('attachments'), array());
        if (!is_array($adjuntos)) {
            $adjuntos = array();
        }
        $mensajes[] = array(
            'id' => goHighLevelTexto(goHighLevelValor($item, array('id', '_id')), 80),
            'cuerpo' => goHighLevelTexto($cuerpo, 4000),
            'direccion' => goHighLevelTexto(goHighLevelValor($item, array('direction')), 16),
            'tipo' => goHighLevelTexto(goHighLevelValor($item, array('messageType', 'type')), 60),
            'estado' => goHighLevelTexto(goHighLevelValor($item, array('status')), 40),
            'fecha' => goHighLevelTexto(goHighLevelValor($item, array('dateAdded', 'createdAt')), 40),
            'adjuntos' => min(20, count($adjuntos))
        );
    }
    usort($mensajes, function ($a, $b) {
        $fechaA = (float)goHighLevelMarcaTiempo($a['fecha']);
        $fechaB = (float)goHighLevelMarcaTiempo($b['fecha']);
        if ($fechaA === $fechaB) {
            return strcmp((string)$a['id'], (string)$b['id']);
        }
        return $fechaA < $fechaB ? -1 : 1;
    });
    return array(
        'conversation_id' => $conversationId,
        'items' => $mensajes,
        'ventana_whatsapp' => goHighLevelVentanaWhatsApp($mensajes),
        'paginacion' => array(
            'hay_mas' => !empty($contenedor['nextPage']),
            'last_message_id' => goHighLevelIdSeguro(goHighLevelValor($contenedor, array('lastMessageId'), ''))
        )
    );
}

function goHighLevelFechaTareaUtc($valor)
{
    $segundos = goHighLevelSegundos($valor);
    if ($segundos <= 0) {
        $texto = trim((string)$valor);
        $segundos = $texto !== '' ? strtotime($texto) : false;
    }
    return $segundos ? gmdate('Y-m-d H:i:s', intval($segundos)) : null;
}

function goHighLevelFormatearTarea($item, $contactId = '')
{
    $contacto = goHighLevelIdSeguro(goHighLevelValor($item, array('contactId', 'contact_id'), $contactId));
    $valorCompletada = goHighLevelValor($item, array('completed', 'isCompleted'), false);
    $completada = $valorCompletada === true || $valorCompletada === 1
        || strtolower(trim((string)$valorCompletada)) === 'true';
    return array(
        'id' => goHighLevelIdSeguro(goHighLevelValor($item, array('id', '_id'), '')),
        'contact_id' => $contacto,
        'titulo' => goHighLevelTexto(goHighLevelValor($item, array('title', 'name'), ''), 255),
        'descripcion' => goHighLevelTexto(goHighLevelValor($item, array('body', 'description'), ''), 4000),
        'assigned_to' => goHighLevelIdSeguro(goHighLevelValor($item, array('assignedTo', 'assigned_to'), '')),
        'fecha_vencimiento' => goHighLevelTexto(goHighLevelValor($item, array('dueDate', 'due_date'), ''), 40),
        'fecha_vencimiento_utc' => goHighLevelFechaTareaUtc(goHighLevelValor($item, array('dueDate', 'due_date'), '')),
        'completada' => $completada,
        'fecha_origen' => goHighLevelTexto(goHighLevelValor($item, array('dateAdded', 'createdAt', 'updatedAt'), ''), 40)
    );
}

function goHighLevelGuardarCacheTareas($mysqli, $contactId, $contactoNombre, $tareas, $marcarAusentes)
{
    $contactId = goHighLevelIdSeguro($contactId);
    if ($contactId === '') {
        return 0;
    }
    if ($marcarAusentes) {
        $stmtAusentes = $mysqli->prepare(
            "UPDATE gohighlevel_tarea_cache SET eliminada=1,fecha_sincronizacion=NOW() WHERE ghl_contact_id=?"
        );
        if ($stmtAusentes) {
            $stmtAusentes->bind_param('s', $contactId);
            $stmtAusentes->execute();
            $stmtAusentes->close();
        }
    }
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_tarea_cache "
        ."(ghl_task_id,ghl_contact_id,ghl_assigned_user_id,titulo,descripcion,contacto_nombre,"
        ."fecha_vencimiento_utc,completada,eliminada,fecha_origen,fecha_sincronizacion) "
        ."VALUES (?,?,?,?,?,?,?, ?,0,?,NOW()) ON DUPLICATE KEY UPDATE "
        ."ghl_contact_id=VALUES(ghl_contact_id),ghl_assigned_user_id=VALUES(ghl_assigned_user_id),"
        ."titulo=VALUES(titulo),descripcion=VALUES(descripcion),contacto_nombre=VALUES(contacto_nombre),"
        ."fecha_vencimiento_utc=VALUES(fecha_vencimiento_utc),completada=VALUES(completada),"
        ."eliminada=0,fecha_origen=VALUES(fecha_origen),fecha_sincronizacion=NOW()"
    );
    if (!$stmt) {
        goHighLevelLanzar('cache_tareas_no_disponible', 'No se pudo preparar el indice de tareas.', array(), 500);
    }
    $guardadas = 0;
    $limiteCompletadas = time() - (90 * 86400);
    $contactoNombre = goHighLevelTexto($contactoNombre, 255);
    foreach ((array)$tareas as $tarea) {
        if (!is_array($tarea)) {
            continue;
        }
        $id = goHighLevelIdSeguro(goHighLevelValor($tarea, array('id'), ''));
        if ($id === '') {
            continue;
        }
        $completada = !empty($tarea['completada']) ? 1 : 0;
        $fechaUtc = isset($tarea['fecha_vencimiento_utc']) ? $tarea['fecha_vencimiento_utc'] : null;
        $fechaSegundos = $fechaUtc ? strtotime($fechaUtc.' UTC') : 0;
        if ($completada && $fechaSegundos > 0 && $fechaSegundos < $limiteCompletadas) {
            continue;
        }
        $assignedTo = goHighLevelIdSeguro(goHighLevelValor($tarea, array('assigned_to'), ''));
        $titulo = goHighLevelTexto(goHighLevelValor($tarea, array('titulo'), ''), 255);
        $descripcion = goHighLevelTexto(goHighLevelValor($tarea, array('descripcion'), ''), 4000);
        $fechaOrigen = goHighLevelTexto(goHighLevelValor($tarea, array('fecha_origen'), ''), 40);
        $stmt->bind_param(
            'sssssssis',
            $id,
            $contactId,
            $assignedTo,
            $titulo,
            $descripcion,
            $contactoNombre,
            $fechaUtc,
            $completada,
            $fechaOrigen
        );
        if (!$stmt->execute()) {
            $stmt->close();
            goHighLevelLanzar('cache_tareas_no_disponible', 'No se pudo actualizar el indice de tareas.', array(), 500);
        }
        $guardadas += 1;
    }
    $stmt->close();
    return $guardadas;
}

function goHighLevelGuardarCacheTareasGlobal($mysqli, $items)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_tarea_cache "
        ."(ghl_task_id,ghl_contact_id,ghl_assigned_user_id,titulo,descripcion,contacto_nombre,"
        ."fecha_vencimiento_utc,completada,eliminada,fecha_origen,fecha_sincronizacion) "
        ."VALUES (?,?,?,?,?,?,?, ?,0,?,NOW()) ON DUPLICATE KEY UPDATE "
        ."ghl_contact_id=VALUES(ghl_contact_id),ghl_assigned_user_id=VALUES(ghl_assigned_user_id),"
        ."titulo=VALUES(titulo),descripcion=VALUES(descripcion),contacto_nombre=VALUES(contacto_nombre),"
        ."fecha_vencimiento_utc=VALUES(fecha_vencimiento_utc),completada=VALUES(completada),"
        ."eliminada=0,fecha_origen=VALUES(fecha_origen),fecha_sincronizacion=NOW()"
    );
    if (!$stmt) {
        goHighLevelLanzar('cache_tareas_no_disponible', 'No se pudo preparar el indice de tareas.', array(), 500);
    }
    $guardadas = 0;
    $limiteCompletadas = time() - (90 * 86400);
    foreach ((array)$items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $eliminada = goHighLevelValor($item, array('deleted'), false);
        if ($eliminada === true || $eliminada === 1 || strtolower(trim((string)$eliminada)) === 'true') {
            continue;
        }
        $tarea = goHighLevelFormatearTarea($item);
        $id = $tarea['id'];
        $contactId = $tarea['contact_id'];
        if ($id === '' || $contactId === '') {
            continue;
        }
        $completada = !empty($tarea['completada']) ? 1 : 0;
        $fechaUtc = $tarea['fecha_vencimiento_utc'];
        $fechaSegundos = $fechaUtc ? strtotime($fechaUtc.' UTC') : 0;
        if ($completada && $fechaSegundos > 0 && $fechaSegundos < $limiteCompletadas) {
            continue;
        }
        $contactoDetalles = goHighLevelValor($item, array('contactDetails'), array());
        $contactoNombre = is_array($contactoDetalles) ? goHighLevelNombreContacto($contactoDetalles) : 'Contacto sin nombre';
        $usuarioDetalles = goHighLevelValor($item, array('assignedToUserDetails'), array());
        if (is_array($usuarioDetalles)) {
            $usuarioId = goHighLevelIdSeguro(goHighLevelValor($usuarioDetalles, array('id', '_id'), $tarea['assigned_to']));
            $usuarioNombre = goHighLevelNombreContacto($usuarioDetalles);
            goHighLevelRegistrarUsuarioObservado($mysqli, $usuarioId, $usuarioNombre);
        }
        $assignedTo = $tarea['assigned_to'];
        $titulo = $tarea['titulo'];
        $descripcion = $tarea['descripcion'];
        $fechaOrigen = $tarea['fecha_origen'];
        $stmt->bind_param(
            'sssssssis',
            $id,
            $contactId,
            $assignedTo,
            $titulo,
            $descripcion,
            $contactoNombre,
            $fechaUtc,
            $completada,
            $fechaOrigen
        );
        if (!$stmt->execute()) {
            $stmt->close();
            goHighLevelLanzar('cache_tareas_no_disponible', 'No se pudo actualizar el indice de tareas.', array(), 500);
        }
        $guardadas += 1;
    }
    $stmt->close();
    return $guardadas;
}

function goHighLevelTareaVisibleParaUsuario($tarea, $contexto)
{
    if (!empty($contexto['puede_ver_equipo'])) {
        return true;
    }
    $asignado = goHighLevelIdSeguro(goHighLevelValor($tarea, array('assigned_to'), ''));
    $actual = goHighLevelIdSeguro(goHighLevelValor($contexto, array('ghl_user_id'), ''));
    return $asignado === '' || ($actual !== '' && $asignado === $actual);
}

function goHighLevelListarTareasContacto($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_ver_tareas'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para consultar tareas de GoHighLevel.', array(), 403);
    }
    $contactId = goHighLevelIdSeguro(isset($parametros['contact_id']) ? $parametros['contact_id'] : '');
    if ($contactId === '') {
        goHighLevelLanzar('contacto_invalido', 'El contacto seleccionado no es valido.', array(), 400);
    }
    $respuesta = goHighLevelApiGet(
        $config,
        '/contacts/'.rawurlencode($contactId).'/tasks',
        array(),
        'v3'
    );
    $itemsApi = goHighLevelItems($respuesta, array('tasks'));
    $todas = array();
    $visibles = array();
    foreach ($itemsApi as $item) {
        if (!is_array($item)) {
            continue;
        }
        $tarea = goHighLevelFormatearTarea($item, $contactId);
        if ($tarea['id'] === '') {
            continue;
        }
        $todas[] = $tarea;
        if (goHighLevelTareaVisibleParaUsuario($tarea, $contexto)) {
            $visibles[] = $tarea;
        }
    }
    $contactoNombre = goHighLevelTexto(
        isset($parametros['contacto_nombre']) ? $parametros['contacto_nombre'] : '',
        255
    );
    goHighLevelGuardarCacheTareas($mysqli, $contactId, $contactoNombre, $todas, true);
    $usuarios = goHighLevelMapaUsuariosLocal($mysqli);
    foreach ($visibles as $indice => $tareaVisible) {
        $asignado = isset($tareaVisible['assigned_to']) ? (string)$tareaVisible['assigned_to'] : '';
        $visibles[$indice]['responsable'] = isset($usuarios[$asignado]) ? $usuarios[$asignado]['nombre'] : '';
        $visibles[$indice]['responsable_avatar'] = isset($usuarios[$asignado]) ? $usuarios[$asignado]['avatar'] : '';
    }
    return array(
        'contact_id' => $contactId,
        'items' => $visibles,
        'total' => count($visibles),
        'puede_gestionar' => !empty($contexto['puede_gestionar_tareas']) && !empty($config['task_write_enabled']),
        'permiso_gestionar' => !empty($contexto['puede_gestionar_tareas']),
        'gestion_habilitada' => !empty($config['task_write_enabled']),
        'puede_sincronizar' => !empty($contexto['puede_gestionar_tareas']) || !empty($contexto['puede_configurar']),
        'usuarios' => array_values($usuarios)
    );
}

function goHighLevelFiltroResponsableTarea($mysqli, $contexto, $valor, $incluirPropiasYSinAsignar)
{
    $valor = trim((string)$valor);
    $actual = goHighLevelIdSeguro(goHighLevelValor($contexto, array('ghl_user_id'), ''));
    if (empty($contexto['puede_ver_equipo'])) {
        if ($valor === 'unassigned') {
            return array('modo' => 'unassigned', 'id' => '');
        }
        if ($incluirPropiasYSinAsignar) {
            return array('modo' => 'mine_or_unassigned', 'id' => $actual);
        }
        return array('modo' => 'mine', 'id' => $actual);
    }
    if ($valor === '' || $valor === 'all') {
        return array('modo' => 'all', 'id' => '');
    }
    if ($valor === 'mine') {
        return array('modo' => 'mine', 'id' => $actual);
    }
    if ($valor === 'unassigned') {
        return array('modo' => 'unassigned', 'id' => '');
    }
    $id = goHighLevelIdSeguro($valor);
    $mapa = goHighLevelMapaUsuariosLocal($mysqli);
    return isset($mapa[$id])
        ? array('modo' => 'user', 'id' => $id)
        : array('modo' => 'all', 'id' => '');
}

function goHighLevelEstadoTareaSql($estado)
{
    if ($estado === 'completed') {
        return 't.completada=1';
    }
    if ($estado === 'overdue') {
        return 't.completada=0 AND t.fecha_vencimiento_utc<UTC_TIMESTAMP()';
    }
    if ($estado === 'today') {
        return 't.completada=0 AND DATE(t.fecha_vencimiento_utc)=UTC_DATE()';
    }
    if ($estado === 'upcoming') {
        return 't.completada=0 AND t.fecha_vencimiento_utc>UTC_TIMESTAMP()';
    }
    if ($estado === 'all') {
        return '1=1';
    }
    return 't.completada=0';
}

function goHighLevelEstadoSyncTareas($mysqli, $locationId)
{
    $locationId = $mysqli->real_escape_string($locationId);
    $resultado = $mysqli->query(
        "SELECT en_curso,contactos_procesados,tareas_procesadas,codigo_estado,"
        ."fecha_inicio,fecha_ultima_ejecucion,fecha_completa FROM gohighlevel_tarea_sync "
        ."WHERE location_id='".$locationId."' LIMIT 1"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    return $fila ? array(
        'en_curso' => intval($fila['en_curso']) === 1,
        'contactos_procesados' => intval($fila['contactos_procesados']),
        'registros_procesados' => intval($fila['contactos_procesados']),
        'tareas_procesadas' => intval($fila['tareas_procesadas']),
        'metodo' => 'busqueda_directa',
        'codigo' => (string)$fila['codigo_estado'],
        'fecha_inicio' => (string)$fila['fecha_inicio'],
        'fecha_ultima' => (string)$fila['fecha_ultima_ejecucion'],
        'fecha_completa' => (string)$fila['fecha_completa']
    ) : array(
        'en_curso' => false,
        'contactos_procesados' => 0,
        'registros_procesados' => 0,
        'tareas_procesadas' => 0,
        'metodo' => 'busqueda_directa',
        'codigo' => 'pendiente',
        'fecha_inicio' => '',
        'fecha_ultima' => '',
        'fecha_completa' => ''
    );
}

function goHighLevelListarTareasCache($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_ver_tareas'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para consultar tareas de GoHighLevel.', array(), 403);
    }
    $buscar = goHighLevelBusqueda($parametros);
    $estado = strtolower(trim((string)(isset($parametros['estado']) ? $parametros['estado'] : 'pending')));
    if (!in_array($estado, array('pending', 'overdue', 'today', 'upcoming', 'completed', 'all'), true)) {
        $estado = 'pending';
    }
    $pagina = max(1, min(100000, intval(isset($parametros['pagina']) ? $parametros['pagina'] : 1)));
    $limite = goHighLevelLimite(isset($parametros['limite']) ? $parametros['limite'] : 30, 30);
    $filtro = goHighLevelFiltroResponsableTarea(
        $mysqli,
        $contexto,
        isset($parametros['assigned_to']) ? $parametros['assigned_to'] : '',
        true
    );
    $condiciones = array('t.eliminada=0', goHighLevelEstadoTareaSql($estado));
    $idEscapado = $mysqli->real_escape_string($filtro['id']);
    if ($filtro['modo'] === 'mine_or_unassigned') {
        $condiciones[] = $idEscapado !== ''
            ? "(t.ghl_assigned_user_id='' OR t.ghl_assigned_user_id='".$idEscapado."')"
            : "t.ghl_assigned_user_id=''";
    } elseif ($filtro['modo'] === 'mine' || $filtro['modo'] === 'user') {
        $condiciones[] = $idEscapado !== ''
            ? "t.ghl_assigned_user_id='".$idEscapado."'"
            : "t.ghl_assigned_user_id=''";
    } elseif ($filtro['modo'] === 'unassigned') {
        $condiciones[] = "t.ghl_assigned_user_id=''";
    }
    if ($buscar !== '') {
        $q = $mysqli->real_escape_string($buscar);
        $condiciones[] = "(t.titulo LIKE '%".$q."%' OR t.descripcion LIKE '%".$q."%' "
            ."OR t.contacto_nombre LIKE '%".$q."%')";
    }
    $where = implode(' AND ', $condiciones);
    $total = 0;
    $resultadoTotal = $mysqli->query("SELECT COUNT(*) total FROM gohighlevel_tarea_cache t WHERE ".$where);
    if ($resultadoTotal && ($filaTotal = $resultadoTotal->fetch_assoc())) {
        $total = intval($filaTotal['total']);
    }
    $offset = ($pagina - 1) * $limite;
    $resultado = $mysqli->query(
        "SELECT t.ghl_task_id,t.ghl_contact_id,t.ghl_assigned_user_id,t.titulo,t.descripcion,"
        ."t.contacto_nombre,t.fecha_vencimiento_utc,t.completada,t.fecha_origen,"
        ."IFNULL(uv.nombre_ghl,'') responsable,IFNULL(uv.avatar_ghl,'') responsable_avatar "
        ."FROM gohighlevel_tarea_cache t LEFT JOIN gohighlevel_usuario_vinculo uv "
        ."ON uv.ghl_user_id=t.ghl_assigned_user_id WHERE ".$where." "
        ."ORDER BY t.completada ASC,t.fecha_vencimiento_utc ASC,t.ghl_task_id ASC "
        ."LIMIT ".$offset.",".$limite
    );
    if (!$resultado) {
        goHighLevelLanzar('tareas_no_disponibles', 'No se pudo cargar el indice de tareas.', array(), 500);
    }
    $items = array();
    while ($fila = $resultado->fetch_assoc()) {
        $items[] = array(
            'id' => (string)$fila['ghl_task_id'],
            'contact_id' => (string)$fila['ghl_contact_id'],
            'assigned_to' => (string)$fila['ghl_assigned_user_id'],
            'titulo' => (string)$fila['titulo'],
            'descripcion' => (string)$fila['descripcion'],
            'contacto_nombre' => (string)$fila['contacto_nombre'],
            'fecha_vencimiento' => $fila['fecha_vencimiento_utc'] !== null
                ? str_replace(' ', 'T', (string)$fila['fecha_vencimiento_utc']).'Z' : '',
            'completada' => intval($fila['completada']) === 1,
            'fecha_origen' => (string)$fila['fecha_origen'],
            'responsable' => (string)$fila['responsable'],
            'responsable_avatar' => (string)$fila['responsable_avatar']
        );
    }
    return array(
        'items' => $items,
        'total' => $total,
        'busqueda' => $buscar,
        'estado' => $estado,
        'filtro_responsable' => $filtro,
        'puede_gestionar' => !empty($contexto['puede_gestionar_tareas']) && !empty($config['task_write_enabled']),
        'permiso_gestionar' => !empty($contexto['puede_gestionar_tareas']),
        'gestion_habilitada' => !empty($config['task_write_enabled']),
        'puede_sincronizar' => !empty($contexto['puede_gestionar_tareas']) || !empty($contexto['puede_configurar']),
        'puede_ver_equipo' => !empty($contexto['puede_ver_equipo']),
        'usuarios' => array_values(goHighLevelMapaUsuariosLocal($mysqli)),
        'sincronizacion' => goHighLevelEstadoSyncTareas($mysqli, $config['location_id']),
        'paginacion' => array(
            'pagina' => $pagina,
            'hay_mas' => ($pagina * $limite) < $total,
            'siguiente_pagina' => $pagina + 1,
            'mostrados' => count($items)
        )
    );
}

function goHighLevelSincronizarTareasPaso($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_configurar']) && empty($contexto['puede_gestionar_tareas'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para sincronizar tareas.', array(), 403);
    }
    $locationId = $config['location_id'];
    $reiniciar = intval(isset($parametros['reiniciar']) ? $parametros['reiniciar'] : 0) === 1;
    $locationSql = $mysqli->real_escape_string($locationId);
    if ($reiniciar) {
        $mysqli->query(
            "INSERT INTO gohighlevel_tarea_sync "
            ."(location_id,en_curso,contactos_procesados,tareas_procesadas,codigo_estado,"
            ."cod_usuario_iniciaFK,fecha_inicio,fecha_ultima_ejecucion) VALUES ('".$locationSql."',1,0,0,'en_curso',"
            .intval($contexto['cod_usuario']).",NOW(),NOW()) ON DUPLICATE KEY UPDATE "
            ."cursor_fecha='',cursor_id='0',en_curso=1,contactos_procesados=0,tareas_procesadas=0,"
            ."codigo_estado='en_curso',cod_usuario_iniciaFK=VALUES(cod_usuario_iniciaFK),"
            ."fecha_inicio=NOW(),fecha_ultima_ejecucion=NOW(),fecha_completa=NULL"
        );
    } else {
        $mysqli->query(
            "INSERT IGNORE INTO gohighlevel_tarea_sync "
            ."(location_id,en_curso,codigo_estado,cod_usuario_iniciaFK,fecha_inicio,fecha_ultima_ejecucion) "
            ."VALUES ('".$locationSql."',1,'en_curso',".intval($contexto['cod_usuario']).",NOW(),NOW())"
        );
    }
    $resultadoEstado = $mysqli->query(
        "SELECT cursor_fecha,cursor_id,en_curso,contactos_procesados,tareas_procesadas "
        ."FROM gohighlevel_tarea_sync WHERE location_id='".$locationSql."' LIMIT 1"
    );
    $estado = $resultadoEstado ? $resultadoEstado->fetch_assoc() : null;
    if (!$estado) {
        goHighLevelLanzar('sincronizacion_no_disponible', 'No se pudo preparar la sincronizacion de tareas.', array(), 500);
    }
    if (!$reiniciar && intval($estado['en_curso']) !== 1) {
        return goHighLevelEstadoSyncTareas($mysqli, $locationId);
    }
    $skip = max(0, min(10000000, intval($estado['cursor_id'])));
    $respuesta = goHighLevelApiBuscarTareas($config, 100, $skip);
    $items = goHighLevelItems($respuesta, array('tasks'));
    if ($reiniciar) {
        if (!$mysqli->query("UPDATE gohighlevel_tarea_cache SET eliminada=1,fecha_sincronizacion=NOW()")) {
            goHighLevelLanzar('cache_tareas_no_disponible', 'No se pudo reiniciar el indice de tareas.', array(), 500);
        }
    }
    $registrosPaso = count($items);
    $tareasPaso = goHighLevelGuardarCacheTareasGlobal($mysqli, $items);
    $siguienteSkip = $skip + $registrosPaso;
    $hayMas = $registrosPaso === 100;
    $cursorFecha = '';
    $cursorId = $hayMas ? (string)$siguienteSkip : '';
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_tarea_sync SET cursor_fecha=?,cursor_id=?,en_curso=?,"
        ."contactos_procesados=contactos_procesados+?,tareas_procesadas=tareas_procesadas+?,"
        ."codigo_estado=?,fecha_ultima_ejecucion=NOW(),"
        ."fecha_completa=IF(?=0,NOW(),fecha_completa) WHERE location_id=? LIMIT 1"
    );
    if (!$stmt) {
        goHighLevelLanzar('sincronizacion_no_disponible', 'No se pudo guardar el avance de tareas.', array(), 500);
    }
    $enCurso = $hayMas ? 1 : 0;
    $codigo = $hayMas ? 'en_curso' : 'completa';
    $stmt->bind_param(
        'ssiiisis',
        $cursorFecha,
        $cursorId,
        $enCurso,
        $registrosPaso,
        $tareasPaso,
        $codigo,
        $enCurso,
        $locationId
    );
    if (!$stmt->execute()) {
        $stmt->close();
        goHighLevelLanzar('sincronizacion_no_disponible', 'No se pudo guardar el avance de tareas.', array(), 500);
    }
    $stmt->close();
    return goHighLevelEstadoSyncTareas($mysqli, $locationId);
}

function goHighLevelResponsableTareaPermitido($mysqli, $contexto, $valor)
{
    $actual = goHighLevelIdSeguro(goHighLevelValor($contexto, array('ghl_user_id'), ''));
    if (empty($contexto['puede_ver_equipo'])) {
        if ($actual === '') {
            goHighLevelLanzar('usuario_no_vinculado', 'Su usuario de Telar no esta vinculado con GoHighLevel.', array(), 422);
        }
        return $actual;
    }
    $id = goHighLevelIdSeguro($valor);
    if ($id === '') {
        return '';
    }
    $mapa = goHighLevelMapaUsuariosLocal($mysqli);
    if (!isset($mapa[$id])) {
        goHighLevelLanzar('responsable_invalido', 'El responsable seleccionado no pertenece a la subcuenta.', array(), 422);
    }
    return $id;
}

function goHighLevelRegistrarOperacionTarea($mysqli, $contexto, $token, $accion, $taskId, $contactId, $assignedTo)
{
    $token = trim((string)$token);
    if (!preg_match('/^[A-Za-z0-9_-]{16,64}$/', $token)) {
        goHighLevelLanzar('token_tarea_invalido', 'La solicitud de tarea no es valida.', array(), 400);
    }
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_tarea_operacion "
        ."(token_cliente,cod_usuarioFK,accion,ghl_task_id,ghl_contact_id,ghl_assigned_user_id,"
        ."estado,fecha_creacion,fecha_actualizacion) VALUES (?,?,?,?,?,?,'procesando',NOW(),NOW())"
    );
    if (!$stmt) {
        goHighLevelLanzar('auditoria_no_disponible', 'No se pudo preparar la operacion de tarea.', array(), 500);
    }
    $actor = intval($contexto['cod_usuario']);
    $stmt->bind_param('sissss', $token, $actor, $accion, $taskId, $contactId, $assignedTo);
    if ($stmt->execute()) {
        $stmt->close();
        return array('repetida' => false, 'task_id' => $taskId);
    }
    $stmt->close();
    $consulta = $mysqli->prepare(
        "SELECT estado,ghl_task_id FROM gohighlevel_tarea_operacion "
        ."WHERE token_cliente=? AND cod_usuarioFK=? LIMIT 1"
    );
    if ($consulta) {
        $consulta->bind_param('si', $token, $actor);
        if ($consulta->execute()) {
            $consulta->bind_result($estado, $taskExistente);
            if ($consulta->fetch() && $estado === 'exito') {
                $consulta->close();
                return array('repetida' => true, 'task_id' => (string)$taskExistente);
            }
        }
        $consulta->close();
    }
    goHighLevelLanzar('tarea_en_proceso', 'Esta operacion ya fue recibida. Actualice la lista antes de repetirla.', array(), 409);
}

function goHighLevelActualizarOperacionTarea($mysqli, $token, $estado, $taskId, $codigo)
{
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_tarea_operacion SET estado=?,ghl_task_id=?,codigo_resultado=?,"
        ."fecha_actualizacion=NOW() WHERE token_cliente=? LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('ssss', $estado, $taskId, $codigo, $token);
        $stmt->execute();
        $stmt->close();
    }
}

function goHighLevelGestionarTarea($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_gestionar_tareas'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para gestionar tareas.', array(), 403);
    }
    $accion = strtolower(trim((string)(isset($parametros['operacion']) ? $parametros['operacion'] : '')));
    if (!in_array($accion, array('crear', 'actualizar', 'completar'), true)) {
        goHighLevelLanzar('tarea_invalida', 'La operacion de tarea no es valida.', array(), 400);
    }
    $contactId = goHighLevelIdSeguro(isset($parametros['contact_id']) ? $parametros['contact_id'] : '');
    $taskId = goHighLevelIdSeguro(isset($parametros['task_id']) ? $parametros['task_id'] : '');
    if ($contactId === '' || ($accion !== 'crear' && $taskId === '')) {
        goHighLevelLanzar('tarea_invalida', 'La tarea o el contacto no son validos.', array(), 400);
    }
    $assignedTo = goHighLevelResponsableTareaPermitido(
        $mysqli,
        $contexto,
        isset($parametros['assigned_to']) ? $parametros['assigned_to'] : ''
    );
    $token = trim((string)(isset($parametros['token_cliente']) ? $parametros['token_cliente'] : ''));
    $registro = goHighLevelRegistrarOperacionTarea(
        $mysqli,
        $contexto,
        $token,
        $accion,
        $taskId,
        $contactId,
        $assignedTo
    );
    if (!empty($registro['repetida'])) {
        return array('id' => $registro['task_id'], 'repetida' => true);
    }
    $entrada = array();
    $metodo = $accion === 'crear' ? 'POST' : 'PUT';
    $completar = $accion === 'completar';
    if ($completar) {
        $entrada['completed'] = intval(isset($parametros['completada']) ? $parametros['completada'] : 1) === 1;
    } else {
        $titulo = goHighLevelTexto(isset($parametros['titulo']) ? $parametros['titulo'] : '', 180);
        $descripcion = goHighLevelTexto(isset($parametros['descripcion']) ? $parametros['descripcion'] : '', 1000);
        $fechaEntrada = trim((string)(isset($parametros['fecha_vencimiento']) ? $parametros['fecha_vencimiento'] : ''));
        $fecha = $fechaEntrada !== '' ? strtotime($fechaEntrada) : false;
        if ($titulo === '' || !$fecha) {
            goHighLevelActualizarOperacionTarea($mysqli, $token, 'fallo', $taskId, 'datos_invalidos');
            goHighLevelLanzar('tarea_invalida', 'Indique un titulo y una fecha de vencimiento valida.', array(), 422);
        }
        $entrada = array(
            'title' => $titulo,
            'body' => $descripcion,
            'dueDate' => date('c', $fecha),
            'completed' => intval(isset($parametros['completada']) ? $parametros['completada'] : 0) === 1
        );
        if ($assignedTo !== '' || !empty($contexto['puede_ver_equipo'])) {
            $entrada['assignedTo'] = $assignedTo;
        }
    }
    try {
        $respuesta = goHighLevelApiEscribirTarea(
            $config,
            $metodo,
            $contactId,
            $taskId,
            $completar,
            $entrada
        );
    } catch (GoHighLevelExcepcion $e) {
        goHighLevelActualizarOperacionTarea($mysqli, $token, 'fallo', $taskId, $e->codigoOperacion);
        throw $e;
    }
    $item = isset($respuesta['task']) && is_array($respuesta['task']) ? $respuesta['task'] : $respuesta;
    $tarea = goHighLevelFormatearTarea($item, $contactId);
    if ($tarea['id'] === '') {
        $tarea['id'] = $taskId;
    }
    if ($completar) {
        $tarea['completada'] = !empty($entrada['completed']);
    }
    if ($completar && $tarea['id'] !== '') {
        $stmtCompleta = $mysqli->prepare(
            "UPDATE gohighlevel_tarea_cache SET completada=?,eliminada=0,fecha_sincronizacion=NOW() "
            ."WHERE ghl_task_id=? AND ghl_contact_id=? LIMIT 1"
        );
        if ($stmtCompleta) {
            $valorCompleta = !empty($entrada['completed']) ? 1 : 0;
            $stmtCompleta->bind_param('iss', $valorCompleta, $tarea['id'], $contactId);
            $stmtCompleta->execute();
            $stmtCompleta->close();
        }
    } elseif ($tarea['id'] !== '') {
        goHighLevelGuardarCacheTareas(
            $mysqli,
            $contactId,
            isset($parametros['contacto_nombre']) ? $parametros['contacto_nombre'] : '',
            array($tarea),
            false
        );
    }
    goHighLevelActualizarOperacionTarea($mysqli, $token, 'exito', $tarea['id'], 'aceptada');
    goHighLevelRegistrarEvento(
        $mysqli,
        $contexto,
        'tarea_'.$accion,
        'tarea_ghl',
        $tarea['id'],
        'Contacto GHL: '.$contactId.'; responsable configurado: '.($assignedTo !== '' ? 'si' : 'no')
    );
    $tarea['repetida'] = false;
    return $tarea;
}

function goHighLevelObtenerConversacion($config, $conversationId)
{
    $conversationId = goHighLevelIdSeguro($conversationId);
    if ($conversationId === '') {
        goHighLevelLanzar('conversacion_invalida', 'La conversacion seleccionada no es valida.', array(), 400);
    }
    $respuesta = goHighLevelApiGet($config, '/conversations/'.rawurlencode($conversationId), array());
    $conversacion = isset($respuesta['conversation']) && is_array($respuesta['conversation'])
        ? $respuesta['conversation'] : $respuesta;
    $contactId = goHighLevelIdSeguro(goHighLevelValor($conversacion, array('contactId', 'contact_id'), ''));
    if ($contactId === '') {
        goHighLevelLanzar('contacto_no_disponible', 'La conversacion no tiene un contacto valido para responder.', array(), 422);
    }
    return array(
        'id' => $conversationId,
        'contact_id' => $contactId,
        'canal' => goHighLevelTexto(goHighLevelValor($conversacion, array('lastMessageType', 'type', 'channel')), 40)
    );
}

function goHighLevelControlFrecuenciaEnvio($mysqli, $contexto, $conversationId)
{
    $actor = intval($contexto['cod_usuario']);
    $stmt = $mysqli->prepare(
        "SELECT "
        ."((SELECT COUNT(*) FROM gohighlevel_envio_manual WHERE cod_usuarioFK=? AND fecha_creacion>=DATE_SUB(NOW(),INTERVAL 1 MINUTE))"
        ."+(SELECT COUNT(*) FROM gohighlevel_envio_plantilla WHERE cod_usuarioFK=? AND fecha_creacion>=DATE_SUB(NOW(),INTERVAL 1 MINUTE))) actor_minuto,"
        ."((SELECT COUNT(*) FROM gohighlevel_envio_manual WHERE ghl_conversation_id=? AND fecha_creacion>=DATE_SUB(NOW(),INTERVAL 5 SECOND))"
        ."+(SELECT COUNT(*) FROM gohighlevel_envio_plantilla WHERE ghl_conversation_id=? AND fecha_creacion>=DATE_SUB(NOW(),INTERVAL 5 SECOND))) conversacion_reciente"
    );
    if (!$stmt) {
        goHighLevelLanzar('auditoria_no_disponible', 'No se pudo comprobar la frecuencia de envio.', array(), 500);
    }
    $stmt->bind_param('iiss', $actor, $actor, $conversationId, $conversationId);
    $actorMinuto = 0;
    $conversacionReciente = 0;
    if ($stmt->execute()) {
        $stmt->bind_result($actorMinuto, $conversacionReciente);
        $stmt->fetch();
    }
    $stmt->close();
    if (intval($actorMinuto) >= 20 || intval($conversacionReciente) > 0) {
        goHighLevelLanzar(
            'envio_demasiado_rapido',
            'Espere unos segundos antes de volver a enviar en esta conversacion.',
            array(),
            429
        );
    }
}

function goHighLevelRegistrarIntentoEnvio($mysqli, $contexto, $token, $conversationId, $contactId, $longitud, $ultimoInbound)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_envio_manual "
        ."(token_cliente,cod_usuarioFK,ghl_conversation_id,ghl_contact_id,canal,estado,longitud_mensaje,"
        ."fecha_ultimo_inbound,fecha_creacion,fecha_actualizacion) "
        ."VALUES (?,?,?,?,'WhatsApp','procesando',?,FROM_UNIXTIME(?),NOW(),NOW())"
    );
    if (!$stmt) {
        goHighLevelLanzar('auditoria_no_disponible', 'No se pudo preparar la auditoria del envio.', array(), 500);
    }
    $actor = intval($contexto['cod_usuario']);
    $ultimoInbound = intval($ultimoInbound);
    $stmt->bind_param('sissii', $token, $actor, $conversationId, $contactId, $longitud, $ultimoInbound);
    $ok = $stmt->execute();
    $errno = intval($stmt->errno);
    $stmt->close();
    if ($ok) {
        return null;
    }
    if ($errno !== 1062) {
        goHighLevelLanzar('auditoria_no_disponible', 'No se pudo registrar el intento de envio.', array(), 500);
    }
    $consulta = $mysqli->prepare(
        "SELECT estado,ghl_message_id FROM gohighlevel_envio_manual "
        ."WHERE token_cliente=? AND cod_usuarioFK=? LIMIT 1"
    );
    if (!$consulta) {
        goHighLevelLanzar('envio_duplicado', 'Este envio ya fue procesado.', array(), 409);
    }
    $consulta->bind_param('si', $token, $actor);
    $estado = '';
    $messageId = '';
    if ($consulta->execute()) {
        $consulta->bind_result($estado, $messageId);
        $consulta->fetch();
    }
    $consulta->close();
    if ($estado === 'enviado') {
        return array('message_id' => (string)$messageId, 'duplicado' => true);
    }
    goHighLevelLanzar('envio_duplicado', 'Este envio ya esta siendo procesado o fallo anteriormente.', array(), 409);
}

function goHighLevelActualizarIntentoEnvio($mysqli, $token, $estado, $messageId, $codigo)
{
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_envio_manual SET estado=?,ghl_message_id=?,codigo_resultado=?,"
        ."fecha_actualizacion=NOW() WHERE token_cliente=? LIMIT 1"
    );
    if (!$stmt) {
        return false;
    }
    $estado = goHighLevelTexto($estado, 16);
    $messageId = goHighLevelTexto($messageId, 80);
    $codigo = goHighLevelTexto($codigo, 48);
    $stmt->bind_param('ssss', $estado, $messageId, $codigo, $token);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function goHighLevelEnviarRespuestaManual($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_responder'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para responder conversaciones.', array(), 403);
    }
    if (empty($config['write_enabled'])) {
        goHighLevelLanzar('envio_no_habilitado', 'El envio manual todavia no fue habilitado por el administrador.', array(), 503);
    }
    if (intval(goHighLevelValor($parametros, array('confirmar_reglas'), 0)) !== 1) {
        goHighLevelLanzar('confirmacion_requerida', 'Debe confirmar las reglas antes de enviar.', array(), 400);
    }
    $conversationId = goHighLevelIdSeguro(goHighLevelValor($parametros, array('conversation_id'), ''));
    $token = goHighLevelTexto(goHighLevelValor($parametros, array('token_envio'), ''), 64);
    $mensaje = trim((string)goHighLevelValor($parametros, array('mensaje'), ''));
    if ($conversationId === '' || !preg_match('/^[A-Za-z0-9_-]{16,64}$/', $token)) {
        goHighLevelLanzar('envio_invalido', 'La solicitud de envio no es valida.', array(), 400);
    }
    $longitud = mb_strlen($mensaje, 'UTF-8');
    if ($longitud < 1 || $longitud > 2000) {
        goHighLevelLanzar('mensaje_invalido', 'El mensaje debe tener entre 1 y 2000 caracteres.', array(), 400);
    }
    $conversacion = goHighLevelObtenerConversacion($config, $conversationId);
    $historial = goHighLevelListarMensajesConversacion($config, array(
        'conversation_id' => $conversationId,
        'limite' => 100
    ));
    $ventana = isset($historial['ventana_whatsapp']) && is_array($historial['ventana_whatsapp'])
        ? $historial['ventana_whatsapp'] : array('abierta' => false, 'segundos_restantes' => 0);
    if (empty($ventana['abierta'])) {
        goHighLevelLanzar(
            'ventana_whatsapp_cerrada',
            'La ventana de 24 horas esta cerrada. Se requiere una plantilla aprobada.',
            array('requiere_plantilla' => true),
            409
        );
    }
    goHighLevelControlFrecuenciaEnvio($mysqli, $contexto, $conversationId);
    $ultimoInbound = goHighLevelSegundos(isset($ventana['ultimo_inbound']) ? $ventana['ultimo_inbound'] : '');
    $duplicado = goHighLevelRegistrarIntentoEnvio(
        $mysqli,
        $contexto,
        $token,
        $conversationId,
        $conversacion['contact_id'],
        $longitud,
        $ultimoInbound
    );
    if (is_array($duplicado)) {
        return array(
            'enviado' => true,
            'duplicado' => true,
            'conversation_id' => $conversationId,
            'message_id' => (string)$duplicado['message_id'],
            'ventana_whatsapp' => $ventana
        );
    }
    try {
        $respuesta = goHighLevelApiPostMensaje($config, array(
            'type' => 'WhatsApp',
            'contactId' => $conversacion['contact_id'],
            'message' => $mensaje,
            'status' => 'pending'
        ));
        $messageId = goHighLevelIdSeguro(goHighLevelValor($respuesta, array('messageId'), ''));
        goHighLevelActualizarIntentoEnvio($mysqli, $token, 'enviado', $messageId, 'aceptado');
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'respuesta_manual_enviada',
            'conversacion',
            $conversationId,
            'Canal: WhatsApp; caracteres: '.$longitud.'; texto no almacenado'
        );
        return array(
            'enviado' => true,
            'duplicado' => false,
            'conversation_id' => goHighLevelIdSeguro(goHighLevelValor($respuesta, array('conversationId'), $conversationId)),
            'message_id' => $messageId,
            'ventana_whatsapp' => $ventana
        );
    } catch (GoHighLevelExcepcion $e) {
        goHighLevelActualizarIntentoEnvio($mysqli, $token, 'fallido', '', $e->codigoOperacion);
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'respuesta_manual_fallida',
            'conversacion',
            $conversationId,
            'Codigo: '.$e->codigoOperacion.'; caracteres: '.$longitud.'; texto no almacenado'
        );
        throw $e;
    }
}

function goHighLevelPlantillaIdSeguro($valor)
{
    $id = trim((string)$valor);
    return preg_match('/^[A-Za-z0-9_.-]{8,120}$/', $id) ? $id : '';
}

function goHighLevelPlantillaCuerpo($item)
{
    if (!is_array($item)) {
        return '';
    }
    $detalle = goHighLevelValor($item, array('template', 'templateData', 'content'), array());
    if (!is_array($detalle)) {
        $detalle = array();
    }
    $cuerpo = goHighLevelValor($detalle, array('body', 'text', 'message'), '');
    if (trim((string)$cuerpo) === '') {
        $cuerpo = goHighLevelValor($item, array('body', 'text', 'message'), '');
    }
    if (trim((string)$cuerpo) === '') {
        $componentes = goHighLevelValor($detalle, array('components'), array());
        if (!is_array($componentes)) {
            $componentes = goHighLevelValor($item, array('components'), array());
        }
        foreach ((array)$componentes as $componente) {
            if (!is_array($componente)) {
                continue;
            }
            $tipo = strtolower(trim((string)goHighLevelValor($componente, array('type'), '')));
            if ($tipo === 'body') {
                $cuerpo = goHighLevelValor($componente, array('text', 'body'), '');
                break;
            }
        }
    }
    $cuerpo = html_entity_decode(strip_tags((string)$cuerpo), ENT_QUOTES, 'UTF-8');
    return goHighLevelTexto($cuerpo, 4000);
}

function goHighLevelPlantillaValor($item, $claves, $predeterminado = '')
{
    $valor = goHighLevelValor($item, $claves, null);
    if ($valor !== null && !is_array($valor)) {
        return $valor;
    }
    foreach (array('template', 'templateData', 'meta', 'whatsappTemplate') as $contenedor) {
        if (!isset($item[$contenedor]) || !is_array($item[$contenedor])) {
            continue;
        }
        $valor = goHighLevelValor($item[$contenedor], $claves, null);
        if ($valor !== null && !is_array($valor)) {
            return $valor;
        }
    }
    return $predeterminado;
}

function goHighLevelPlantillaEsSensibleDetectada($nombre, $cuerpo)
{
    $texto = mb_strtolower((string)$nombre.' '.(string)$cuerpo, 'UTF-8');
    foreach (array('informconf', 'judicial', 'juridic', 'area legal', 'área legal', 'demanda', 'mora_90', '90_dias') as $marca) {
        if (mb_strpos($texto, $marca, 0, 'UTF-8') !== false) {
            return true;
        }
    }
    return false;
}

function goHighLevelNormalizarPlantilla($item)
{
    $id = goHighLevelPlantillaIdSeguro(goHighLevelPlantillaValor($item, array('id', '_id', 'templateId'), ''));
    $nombre = goHighLevelTexto(goHighLevelPlantillaValor($item, array('name', 'templateName'), ''), 200);
    $idioma = goHighLevelTexto(goHighLevelPlantillaValor($item, array('language', 'languageCode', 'locale'), ''), 32);
    $categoria = goHighLevelTexto(goHighLevelPlantillaValor($item, array('category'), ''), 32);
    $estado = goHighLevelTexto(goHighLevelPlantillaValor($item, array('status', 'state'), ''), 32);
    $tipo = goHighLevelTexto(goHighLevelPlantillaValor($item, array('type', 'channel'), 'whatsapp'), 32);
    $cuerpo = goHighLevelPlantillaCuerpo($item);
    $estadoNormalizado = mb_strtolower($estado, 'UTF-8');
    $idiomaNormalizado = mb_strtolower(str_replace('-', '_', $idioma), 'UTF-8');
    $categoriaNormalizada = mb_strtolower($categoria, 'UTF-8');
    $tipoNormalizado = mb_strtolower($tipo, 'UTF-8');
    $estadoActivo = in_array($estadoNormalizado, array('active', 'activo', 'approved', 'aprobado'), true);
    $idiomaEspanol = $idiomaNormalizado === 'spanish' || $idiomaNormalizado === 'es'
        || strpos($idiomaNormalizado, 'es_') === 0;
    $categoriaUtilidad = in_array($categoriaNormalizada, array('utility', 'utilidad'), true);
    $esWhatsApp = $tipoNormalizado === '' || strpos($tipoNormalizado, 'whatsapp') !== false;
    $tieneVariables = preg_match('/\{\{[^{}]+\}\}/u', $cuerpo) === 1;
    $elegible = $id !== '' && $nombre !== '' && $cuerpo !== '' && $estadoActivo
        && $idiomaEspanol && $categoriaUtilidad && $esWhatsApp;
    $motivo = '';
    if (!$estadoActivo) {
        $motivo = 'La plantilla no esta activa o aprobada.';
    } elseif (!$idiomaEspanol) {
        $motivo = 'La plantilla no esta en español.';
    } elseif (!$categoriaUtilidad) {
        $motivo = 'Solo se habilitan plantillas de utilidad.';
    } elseif (!$esWhatsApp) {
        $motivo = 'La plantilla no corresponde a WhatsApp.';
    } elseif ($cuerpo === '') {
        $motivo = 'La plantilla no tiene una vista previa disponible.';
    } elseif ($tieneVariables) {
        $motivo = 'Contiene variables que requieren resolucion manual.';
    }
    return array(
        'id' => $id,
        'nombre' => $nombre !== '' ? $nombre : 'Plantilla sin nombre',
        'idioma' => $idioma,
        'categoria' => $categoria,
        'estado' => $estado,
        'tipo' => $tipo,
        'cuerpo' => $cuerpo,
        'tiene_variables' => $tieneVariables,
        'elegible' => $elegible && !$tieneVariables,
        'bloqueada_motivo' => $motivo,
        'sensible_detectada' => goHighLevelPlantillaEsSensibleDetectada($nombre, $cuerpo)
    );
}

function goHighLevelSincronizarPlantillasLocales($mysqli, $plantillas)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_plantilla_config "
        ."(ghl_template_id,nombre,idioma,categoria,estado,habilitada,sensible_manual,tiene_variables,"
        ."cod_usuario_actualizaFK,fecha_ultima_consulta,fecha_creacion,fecha_actualizacion) "
        ."VALUES (?,?,?,?,?,?,0,?,NULL,NOW(),NOW(),NOW()) ON DUPLICATE KEY UPDATE "
        ."nombre=VALUES(nombre),idioma=VALUES(idioma),categoria=VALUES(categoria),estado=VALUES(estado),"
        ."tiene_variables=VALUES(tiene_variables),fecha_ultima_consulta=NOW(),fecha_actualizacion=NOW()"
    );
    if (!$stmt) {
        goHighLevelLanzar('catalogo_local_no_disponible', 'No se pudo preparar el catalogo local de plantillas.', array(), 500);
    }
    foreach ((array)$plantillas as $plantilla) {
        if (!is_array($plantilla) || $plantilla['id'] === '') {
            continue;
        }
        $id = $plantilla['id'];
        $nombre = $plantilla['nombre'];
        $idioma = $plantilla['idioma'];
        $categoria = $plantilla['categoria'];
        $estado = $plantilla['estado'];
        $habilitada = !empty($plantilla['elegible']) ? 1 : 0;
        $variables = !empty($plantilla['tiene_variables']) ? 1 : 0;
        $stmt->bind_param('sssssii', $id, $nombre, $idioma, $categoria, $estado, $habilitada, $variables);
        if (!$stmt->execute()) {
            $stmt->close();
            goHighLevelLanzar('catalogo_local_no_disponible', 'No se pudo actualizar el catalogo local de plantillas.', array(), 500);
        }
    }
    $stmt->close();
}

function goHighLevelConfiguracionPlantillasLocales($mysqli)
{
    $salida = array();
    $resultado = $mysqli->query(
        "SELECT ghl_template_id,habilitada,sensible_manual,tiene_variables "
        ."FROM gohighlevel_plantilla_config"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $salida[(string)$fila['ghl_template_id']] = array(
            'habilitada' => intval($fila['habilitada']) === 1,
            'sensible_manual' => intval($fila['sensible_manual']) === 1,
            'tiene_variables' => intval($fila['tiene_variables']) === 1
        );
    }
    return $salida;
}

function goHighLevelListarPlantillasWhatsApp($mysqli, $config, $parametros = array())
{
    $plantillas = array();
    $totalRemoto = 0;
    $salto = 0;
    $limite = 100;
    do {
        $respuesta = goHighLevelApiGet(
            $config,
            '/locations/'.rawurlencode($config['location_id']).'/templates',
            array(
                'deleted' => 'false',
                'skip' => (string)$salto,
                'limit' => (string)$limite,
                'type' => 'whatsapp',
                'originId' => $config['location_id']
            ),
            'v3'
        );
        $items = goHighLevelItems($respuesta, array('templates'));
        $totalRemoto = max($totalRemoto, intval(goHighLevelValor($respuesta, array('totalCount', 'total'), count($items))));
        foreach ($items as $item) {
            if (is_array($item)) {
                $normalizada = goHighLevelNormalizarPlantilla($item);
                if ($normalizada['id'] !== '') {
                    $plantillas[$normalizada['id']] = $normalizada;
                }
            }
        }
        $salto += count($items);
        if (count($items) < $limite || $salto >= $totalRemoto || $salto >= 500) {
            break;
        }
    } while (true);
    $plantillas = array_values($plantillas);
    goHighLevelSincronizarPlantillasLocales($mysqli, $plantillas);
    $locales = goHighLevelConfiguracionPlantillasLocales($mysqli);
    $soloHabilitadas = intval(goHighLevelValor($parametros, array('solo_habilitadas'), 0)) === 1;
    $salida = array();
    $habilitadas = 0;
    $sensibles = 0;
    $bloqueadas = 0;
    foreach ($plantillas as $plantilla) {
        $local = isset($locales[$plantilla['id']]) ? $locales[$plantilla['id']] : array();
        $plantilla['habilitada'] = !empty($local['habilitada']) && !empty($plantilla['elegible']);
        $plantilla['sensible_manual'] = !empty($local['sensible_manual']);
        $plantilla['sensible'] = !empty($plantilla['sensible_detectada']) || $plantilla['sensible_manual'];
        if ($plantilla['habilitada']) {
            $habilitadas++;
        }
        if ($plantilla['sensible']) {
            $sensibles++;
        }
        if (empty($plantilla['elegible'])) {
            $bloqueadas++;
        }
        if (!$soloHabilitadas || $plantilla['habilitada']) {
            $salida[] = $plantilla;
        }
    }
    usort($salida, function ($a, $b) {
        if (!empty($a['sensible']) !== !empty($b['sensible'])) {
            return !empty($a['sensible']) ? 1 : -1;
        }
        return strcasecmp((string)$a['nombre'], (string)$b['nombre']);
    });
    return array(
        'items' => $salida,
        'total_remoto' => $totalRemoto,
        'total_catalogado' => count($plantillas),
        'habilitadas' => $habilitadas,
        'sensibles' => $sensibles,
        'bloqueadas' => $bloqueadas,
        'criterio_inicial' => 'Activas, en español, de utilidad y sin variables manuales.',
        'administracion_externa' => 'https://crm.fivox.app/v2/location/'.$config['location_id'].'/settings/whatsapp?tab=templates'
    );
}

function goHighLevelGuardarConfiguracionPlantillas($mysqli, $config, $contexto, $entrada)
{
    if (empty($contexto['puede_configurar'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para administrar plantillas.', array(), 403);
    }
    $lista = json_decode((string)$entrada, true);
    if (!is_array($lista) || count($lista) > 500) {
        goHighLevelLanzar('plantillas_invalidas', 'La configuracion de plantillas no es valida.', array(), 400);
    }
    $catalogo = goHighLevelListarPlantillasWhatsApp($mysqli, $config, array());
    $remotas = array();
    foreach ($catalogo['items'] as $plantilla) {
        $remotas[$plantilla['id']] = $plantilla;
    }
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_plantilla_config SET habilitada=?,sensible_manual=?,"
        ."cod_usuario_actualizaFK=?,fecha_actualizacion=NOW() WHERE ghl_template_id=? LIMIT 1"
    );
    if (!$stmt) {
        goHighLevelLanzar('catalogo_local_no_disponible', 'No se pudo preparar la configuracion de plantillas.', array(), 500);
    }
    $actor = intval($contexto['cod_usuario']);
    $guardadas = 0;
    $mysqli->begin_transaction();
    try {
        foreach ($lista as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = goHighLevelPlantillaIdSeguro(goHighLevelValor($item, array('id'), ''));
            if ($id === '' || !isset($remotas[$id])) {
                continue;
            }
            $habilitada = intval(goHighLevelValor($item, array('habilitada'), 0)) === 1
                && !empty($remotas[$id]['elegible']) ? 1 : 0;
            $sensibleManual = intval(goHighLevelValor($item, array('sensible_manual'), 0)) === 1 ? 1 : 0;
            $stmt->bind_param('iiis', $habilitada, $sensibleManual, $actor, $id);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar una plantilla.');
            }
            $guardadas++;
        }
        $stmt->close();
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'plantillas_configuradas',
            'plantilla_whatsapp',
            '',
            'Plantillas revisadas: '.$guardadas.'; cuerpos no almacenados'
        );
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        goHighLevelLanzar('plantillas_no_guardadas', 'No se pudo guardar la configuracion de plantillas.', array(), 500);
    }
    return array('guardadas' => $guardadas);
}

function goHighLevelRegistrarIntentoPlantilla($mysqli, $contexto, $token, $conversationId, $contactId, $plantilla)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_envio_plantilla "
        ."(token_cliente,cod_usuarioFK,ghl_conversation_id,ghl_contact_id,ghl_template_id,nombre_plantilla,"
        ."idioma,categoria,sensible,estado,fecha_creacion,fecha_actualizacion) "
        ."VALUES (?,?,?,?,?,?,?,?,?,'procesando',NOW(),NOW())"
    );
    if (!$stmt) {
        goHighLevelLanzar('auditoria_no_disponible', 'No se pudo preparar la auditoria de la plantilla.', array(), 500);
    }
    $actor = intval($contexto['cod_usuario']);
    $sensible = !empty($plantilla['sensible']) ? 1 : 0;
    $id = $plantilla['id'];
    $nombre = $plantilla['nombre'];
    $idioma = $plantilla['idioma'];
    $categoria = $plantilla['categoria'];
    $stmt->bind_param('sissssssi', $token, $actor, $conversationId, $contactId, $id, $nombre, $idioma, $categoria, $sensible);
    $ok = $stmt->execute();
    $errno = intval($stmt->errno);
    $stmt->close();
    if ($ok) {
        return null;
    }
    if ($errno !== 1062) {
        goHighLevelLanzar('auditoria_no_disponible', 'No se pudo registrar el intento de plantilla.', array(), 500);
    }
    $consulta = $mysqli->prepare(
        "SELECT estado,ghl_message_id FROM gohighlevel_envio_plantilla "
        ."WHERE token_cliente=? AND cod_usuarioFK=? LIMIT 1"
    );
    if (!$consulta) {
        goHighLevelLanzar('envio_duplicado', 'Este envio ya fue procesado.', array(), 409);
    }
    $consulta->bind_param('si', $token, $actor);
    $estado = '';
    $messageId = '';
    if ($consulta->execute()) {
        $consulta->bind_result($estado, $messageId);
        $consulta->fetch();
    }
    $consulta->close();
    if ($estado === 'enviado') {
        return array('message_id' => (string)$messageId, 'duplicado' => true);
    }
    goHighLevelLanzar('envio_duplicado', 'Este envio ya esta siendo procesado o fallo anteriormente.', array(), 409);
}

function goHighLevelActualizarIntentoPlantilla($mysqli, $token, $estado, $messageId, $codigo)
{
    $stmt = $mysqli->prepare(
        "UPDATE gohighlevel_envio_plantilla SET estado=?,ghl_message_id=?,codigo_resultado=?,"
        ."fecha_actualizacion=NOW() WHERE token_cliente=? LIMIT 1"
    );
    if (!$stmt) {
        return false;
    }
    $estado = goHighLevelTexto($estado, 16);
    $messageId = goHighLevelTexto($messageId, 80);
    $codigo = goHighLevelTexto($codigo, 48);
    $stmt->bind_param('ssss', $estado, $messageId, $codigo, $token);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function goHighLevelEnviarPlantillaWhatsApp($mysqli, $config, $contexto, $parametros)
{
    if (empty($contexto['puede_enviar_plantilla'])) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para enviar plantillas.', array(), 403);
    }
    if (empty($config['write_enabled'])) {
        goHighLevelLanzar('envio_no_habilitado', 'El envio de WhatsApp todavia no fue habilitado por el administrador.', array(), 503);
    }
    if (intval(goHighLevelValor($parametros, array('confirmar_reglas'), 0)) !== 1) {
        goHighLevelLanzar('confirmacion_requerida', 'Debe confirmar las reglas antes de enviar.', array(), 400);
    }
    $conversationId = goHighLevelIdSeguro(goHighLevelValor($parametros, array('conversation_id'), ''));
    $templateId = goHighLevelPlantillaIdSeguro(goHighLevelValor($parametros, array('template_id'), ''));
    $token = goHighLevelTexto(goHighLevelValor($parametros, array('token_envio'), ''), 64);
    if ($conversationId === '' || $templateId === '' || !preg_match('/^[A-Za-z0-9_-]{16,64}$/', $token)) {
        goHighLevelLanzar('envio_invalido', 'La solicitud de plantilla no es valida.', array(), 400);
    }
    $conversacion = goHighLevelObtenerConversacion($config, $conversationId);
    $historial = goHighLevelListarMensajesConversacion($config, array(
        'conversation_id' => $conversationId,
        'limite' => 100
    ));
    $ventana = isset($historial['ventana_whatsapp']) && is_array($historial['ventana_whatsapp'])
        ? $historial['ventana_whatsapp'] : array('abierta' => false);
    if (!empty($ventana['abierta'])) {
        goHighLevelLanzar(
            'ventana_whatsapp_abierta',
            'La ventana esta abierta. Responda con texto libre para mantener el flujo normal.',
            array(),
            409
        );
    }
    $catalogo = goHighLevelListarPlantillasWhatsApp($mysqli, $config, array());
    $plantilla = null;
    foreach ($catalogo['items'] as $item) {
        if ((string)$item['id'] === $templateId) {
            $plantilla = $item;
            break;
        }
    }
    if (!$plantilla || empty($plantilla['elegible']) || empty($plantilla['habilitada']) || !empty($plantilla['tiene_variables'])) {
        goHighLevelLanzar(
            'plantilla_no_disponible',
            'La plantilla ya no esta aprobada, habilitada o requiere variables manuales.',
            array(),
            409
        );
    }
    if (!empty($plantilla['sensible'])
        && intval(goHighLevelValor($parametros, array('confirmar_sensible'), 0)) !== 1) {
        goHighLevelLanzar(
            'confirmacion_sensible_requerida',
            'Debe confirmar expresamente el envio del aviso sensible.',
            array(),
            400
        );
    }
    goHighLevelControlFrecuenciaEnvio($mysqli, $contexto, $conversationId);
    $duplicado = goHighLevelRegistrarIntentoPlantilla(
        $mysqli,
        $contexto,
        $token,
        $conversationId,
        $conversacion['contact_id'],
        $plantilla
    );
    if (is_array($duplicado)) {
        return array(
            'enviado' => true,
            'duplicado' => true,
            'conversation_id' => $conversationId,
            'message_id' => (string)$duplicado['message_id'],
            'plantilla' => array('id' => $plantilla['id'], 'nombre' => $plantilla['nombre'], 'sensible' => $plantilla['sensible']),
            'ventana_whatsapp' => $ventana
        );
    }
    try {
        $respuesta = goHighLevelApiPostMensaje($config, array(
            'type' => 'WhatsApp',
            'contactId' => $conversacion['contact_id'],
            'templateId' => $plantilla['id'],
            'status' => 'pending'
        ));
        $messageId = goHighLevelIdSeguro(goHighLevelValor($respuesta, array('messageId'), ''));
        goHighLevelActualizarIntentoPlantilla($mysqli, $token, 'enviado', $messageId, 'aceptado');
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'plantilla_whatsapp_enviada',
            'conversacion',
            $conversationId,
            'Plantilla: '.$plantilla['nombre'].'; categoria: '.$plantilla['categoria'].'; cuerpo no almacenado'
        );
        return array(
            'enviado' => true,
            'duplicado' => false,
            'conversation_id' => goHighLevelIdSeguro(goHighLevelValor($respuesta, array('conversationId'), $conversationId)),
            'message_id' => $messageId,
            'plantilla' => array('id' => $plantilla['id'], 'nombre' => $plantilla['nombre'], 'sensible' => $plantilla['sensible']),
            'ventana_whatsapp' => $ventana
        );
    } catch (GoHighLevelExcepcion $e) {
        goHighLevelActualizarIntentoPlantilla($mysqli, $token, 'fallido', '', $e->codigoOperacion);
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'plantilla_whatsapp_fallida',
            'conversacion',
            $conversationId,
            'Plantilla: '.$plantilla['nombre'].'; codigo: '.$e->codigoOperacion.'; cuerpo no almacenado'
        );
        throw $e;
    }
}

function goHighLevelListarCalendarios($config)
{
    $respuesta = goHighLevelApiGet($config, '/calendars/', array('locationId' => $config['location_id']));
    $items = goHighLevelItems($respuesta, array('calendars'));
    $calendarios = array();
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $calendarios[] = array(
            'id' => goHighLevelTexto(goHighLevelValor($item, array('id', '_id')), 80),
            'nombre' => goHighLevelTexto(goHighLevelValor($item, array('name')), 180),
            'descripcion' => goHighLevelTexto(goHighLevelValor($item, array('description')), 400),
            'activo' => (bool)goHighLevelValor($item, array('isActive', 'active'), true),
            'slug' => goHighLevelTexto(goHighLevelValor($item, array('slug')), 160)
        );
    }
    return array('items' => $calendarios, 'total' => count($calendarios));
}

function goHighLevelResumen($mysqli, $config)
{
    $contactos = goHighLevelListarContactos($mysqli, $config, array('limite' => 1));
    $conversaciones = goHighLevelApiGet($config, '/conversations/search', array(
        'locationId' => $config['location_id'], 'limit' => 1
    ));
    $conversacionesItems = goHighLevelItems($conversaciones, array('conversations'));
    $oportunidades = goHighLevelApiGet($config, '/opportunities/search', array(
        'location_id' => $config['location_id'], 'limit' => 1
    ));
    $oportunidadesItems = goHighLevelItems($oportunidades, array('opportunities'));
    $calendarios = goHighLevelListarCalendarios($config);
    return array(
        'contactos' => intval($contactos['total']),
        'conversaciones' => goHighLevelTotal($conversaciones, $conversacionesItems),
        'oportunidades' => goHighLevelTotal($oportunidades, $oportunidadesItems),
        'calendarios' => intval($calendarios['total'])
    );
}

function goHighLevelEstadoSincronizacion($mysqli, $config)
{
    $resumen = array('vinculados' => 0, 'ambiguos' => 0, 'sin_coincidencia' => 0, 'actualizados' => 0);
    $resultado = $mysqli->query(
        "SELECT estado,COUNT(*) total FROM gohighlevel_vinculo_contacto GROUP BY estado"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $estado = (string)$fila['estado'];
        if (isset($resumen[$estado])) {
            $resumen[$estado] = intval($fila['total']);
        }
        $resumen['actualizados'] += intval($fila['total']);
    }
    $modo = !empty($config['task_write_enabled'])
        ? 'gestion_tareas'
        : (!empty($config['write_enabled']) ? 'respuestas' : 'solo_lectura');
    $protecciones = array(
        'No mueve oportunidades ni dispara automatizaciones.',
        'Solo vincula un paciente cuando existe una coincidencia telefonica unica.'
    );
    if (!empty($config['task_write_enabled'])) {
        array_unshift(
            $protecciones,
            'La escritura de contactos se limita a crear, editar o completar tareas; no elimina tareas.'
        );
    } else {
        array_unshift($protecciones, 'No crea ni actualiza contactos en GoHighLevel.');
    }
    return array(
        'configurado' => goHighLevelConfigurado($config),
        'modo' => $modo,
        'location_id' => (string)$config['location_id'],
        'vinculos' => $resumen,
        'protecciones' => $protecciones
    );
}

function goHighLevelUsuariosPermisos($mysqli, $contexto)
{
    if (!$contexto['puede_configurar']) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para configurar el modulo.', array(), 403);
    }
    $sql = "SELECT u.cod_usuario,IFNULL(p.nombre_persona,u.login) nombre,IFNULL(u.url,'') avatar,"
        ."IFNULL(l.Nombre,'') local,IFNULL(g.puede_ver,0) puede_ver,"
        ."IFNULL(g.puede_responder,0) puede_responder,"
        ."IFNULL(g.puede_enviar_plantilla,0) puede_enviar_plantilla,"
        ."IFNULL(g.puede_ver_tareas,0) puede_ver_tareas,"
        ."IFNULL(g.puede_ver_equipo,0) puede_ver_equipo,"
        ."IFNULL(g.puede_gestionar_tareas,0) puede_gestionar_tareas,"
        ."IFNULL(g.puede_configurar,0) puede_configurar "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."LEFT JOIN gohighlevel_permiso_usuario g ON g.cod_usuarioFK=u.cod_usuario AND g.activo=1 "
        ."WHERE UPPER(TRIM(u.estado))='ACTIVO' ORDER BY nombre";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        goHighLevelLanzar('permisos_no_disponibles', 'No se pudo cargar el equipo.', array(), 500);
    }
    $usuarios = array();
    while ($fila = $resultado->fetch_assoc()) {
        $id = intval($fila['cod_usuario']);
        $usuarios[] = array(
            'cod_usuario' => $id,
            'nombre' => (string)$fila['nombre'],
            'avatar' => (string)$fila['avatar'],
            'local' => (string)$fila['local'],
            'puede_ver' => $id === 5994 || intval($fila['puede_ver']) === 1,
            'puede_responder' => $id === 5994 || intval($fila['puede_responder']) === 1,
            'puede_enviar_plantilla' => $id === 5994 || intval($fila['puede_enviar_plantilla']) === 1,
            'puede_ver_tareas' => $id === 5994 || intval($fila['puede_ver_tareas']) === 1,
            'puede_ver_equipo' => $id === 5994 || intval($fila['puede_ver_equipo']) === 1,
            'puede_gestionar_tareas' => $id === 5994 || intval($fila['puede_gestionar_tareas']) === 1,
            'puede_configurar' => $id === 5994 || intval($fila['puede_configurar']) === 1,
            'bloqueado' => $id === 5994
        );
    }
    return array('usuarios' => $usuarios);
}

function goHighLevelRegistrarEvento($mysqli, $contexto, $tipo, $entidad, $entidadId, $detalle)
{
    $stmt = $mysqli->prepare(
        "INSERT INTO gohighlevel_evento "
        ."(cod_usuario_actorFK,tipo_evento,entidad,entidad_id,detalle_seguro,ip_solicitud,fecha_evento) "
        ."VALUES (?,?,?,?,?,?,NOW())"
    );
    if (!$stmt) {
        return;
    }
    $actor = intval($contexto['cod_usuario']);
    $tipo = goHighLevelTexto($tipo, 40);
    $entidad = goHighLevelTexto($entidad, 40);
    $entidadId = goHighLevelTexto($entidadId, 80);
    $detalle = goHighLevelTexto($detalle, 1000);
    $ip = isset($_SERVER['REMOTE_ADDR']) ? goHighLevelTexto($_SERVER['REMOTE_ADDR'], 45) : '';
    $stmt->bind_param('isssss', $actor, $tipo, $entidad, $entidadId, $detalle, $ip);
    $stmt->execute();
    $stmt->close();
}

function goHighLevelGuardarPermisos($mysqli, $contexto, $entrada)
{
    if (!$contexto['puede_configurar']) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para configurar el modulo.', array(), 403);
    }
    $lista = json_decode((string)$entrada, true);
    if (!is_array($lista) || count($lista) > 250) {
        goHighLevelLanzar('permisos_invalidos', 'La configuracion de permisos no es valida.');
    }
    $permisos = array(5994 => array(
        'ver' => 1,
        'responder' => 1,
        'plantilla' => 1,
        'ver_tareas' => 1,
        'ver_equipo' => 1,
        'gestionar_tareas' => 1,
        'configurar' => 1
    ));
    foreach ($lista as $item) {
        if (!is_array($item)) {
            continue;
        }
        $id = intval(goHighLevelValor($item, array('cod_usuario'), 0));
        if ($id <= 0) {
            continue;
        }
        $configurar = intval(goHighLevelValor($item, array('puede_configurar'), 0)) === 1 ? 1 : 0;
        $responder = intval(goHighLevelValor($item, array('puede_responder'), 0)) === 1 ? 1 : 0;
        $plantilla = intval(goHighLevelValor($item, array('puede_enviar_plantilla'), 0)) === 1 ? 1 : 0;
        $verTareas = intval(goHighLevelValor($item, array('puede_ver_tareas'), 0)) === 1 ? 1 : 0;
        $verEquipo = intval(goHighLevelValor($item, array('puede_ver_equipo'), 0)) === 1 ? 1 : 0;
        $gestionarTareas = intval(goHighLevelValor($item, array('puede_gestionar_tareas'), 0)) === 1 ? 1 : 0;
        if ($gestionarTareas) {
            $verTareas = 1;
        }
        $ver = ($configurar || $responder || $plantilla || $verTareas || $verEquipo
            || intval(goHighLevelValor($item, array('puede_ver'), 0)) === 1) ? 1 : 0;
        if ($id === 5994) {
            $ver = 1;
            $responder = 1;
            $plantilla = 1;
            $verTareas = 1;
            $verEquipo = 1;
            $gestionarTareas = 1;
            $configurar = 1;
        }
        $permisos[$id] = array(
            'ver' => $ver,
            'responder' => $responder,
            'plantilla' => $plantilla,
            'ver_tareas' => $verTareas,
            'ver_equipo' => $verEquipo,
            'gestionar_tareas' => $gestionarTareas,
            'configurar' => $configurar
        );
    }
    $actor = intval($contexto['cod_usuario']);
    $mysqli->begin_transaction();
    try {
        if (!$mysqli->query(
            "UPDATE gohighlevel_permiso_usuario SET puede_ver=0,puede_responder=0,puede_enviar_plantilla=0,"
            ."puede_ver_tareas=0,puede_ver_equipo=0,puede_gestionar_tareas=0,puede_configurar=0,activo=0,"
            ."cod_usuario_actualizaFK=".$actor.",fecha_actualizacion=NOW()"
        )) {
            throw new Exception('No se pudieron preparar los permisos.');
        }
        $stmt = $mysqli->prepare(
            "INSERT INTO gohighlevel_permiso_usuario "
            ."(cod_usuarioFK,puede_ver,puede_responder,puede_enviar_plantilla,puede_ver_tareas,puede_ver_equipo,"
            ."puede_gestionar_tareas,puede_configurar,activo,cod_usuario_actualizaFK,fecha_creacion,fecha_actualizacion) "
            ."VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE "
            ."puede_ver=VALUES(puede_ver),puede_responder=VALUES(puede_responder),"
            ."puede_enviar_plantilla=VALUES(puede_enviar_plantilla),"
            ."puede_ver_tareas=VALUES(puede_ver_tareas),puede_ver_equipo=VALUES(puede_ver_equipo),"
            ."puede_gestionar_tareas=VALUES(puede_gestionar_tareas),"
            ."puede_configurar=VALUES(puede_configurar),activo=VALUES(activo),"
            ."cod_usuario_actualizaFK=VALUES(cod_usuario_actualizaFK),fecha_actualizacion=NOW()"
        );
        if (!$stmt) {
            throw new Exception('No se pudieron preparar los permisos.');
        }
        foreach ($permisos as $id => $permiso) {
            $activo = $permiso['ver'] ? 1 : 0;
            $idUsuario = intval($id);
            $puedeVer = intval($permiso['ver']);
            $puedeResponder = intval($permiso['responder']);
            $puedePlantilla = intval($permiso['plantilla']);
            $puedeVerTareas = intval($permiso['ver_tareas']);
            $puedeVerEquipo = intval($permiso['ver_equipo']);
            $puedeGestionarTareas = intval($permiso['gestionar_tareas']);
            $puedeConfigurar = intval($permiso['configurar']);
            $stmt->bind_param(
                'iiiiiiiiii',
                $idUsuario,
                $puedeVer,
                $puedeResponder,
                $puedePlantilla,
                $puedeVerTareas,
                $puedeVerEquipo,
                $puedeGestionarTareas,
                $puedeConfigurar,
                $activo,
                $actor
            );
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar un permiso.');
            }
        }
        $stmt->close();
        $catalogo = $mysqli->query(
            "SELECT id FROM dashboard_access_catalog WHERE access_key='gohighlevel' AND is_active=1 LIMIT 1"
        );
        $filaCatalogo = $catalogo ? $catalogo->fetch_assoc() : null;
        if ($filaCatalogo) {
            $accessId = intval($filaCatalogo['id']);
            $mysqli->query(
                "UPDATE dashboard_user_shortcuts s INNER JOIN gohighlevel_permiso_usuario g "
                ."ON g.cod_usuarioFK=s.user_id SET s.is_visible=0,s.updated_at=NOW() "
                ."WHERE s.access_id=".$accessId." AND (g.activo=0 OR g.puede_ver=0)"
            );
            foreach ($permisos as $id => $permiso) {
                if (!$permiso['ver']) {
                    continue;
                }
                $idUsuarioAcceso = intval($id);
                $existenteResultado = $mysqli->query(
                    "SELECT id FROM dashboard_user_shortcuts WHERE user_id=".$idUsuarioAcceso
                    ." AND access_id=".$accessId." LIMIT 1"
                );
                $existente = $existenteResultado && $existenteResultado->num_rows === 1;
                $ordenResultado = $mysqli->query(
                    "SELECT COUNT(*) total,IFNULL(MAX(shortcut_order),0)+1 orden "
                    ."FROM dashboard_user_shortcuts WHERE user_id=".$idUsuarioAcceso." AND is_visible=1"
                );
                $ordenFila = $ordenResultado
                    ? $ordenResultado->fetch_assoc() : array('total' => 20, 'orden' => 1);
                if ($existente) {
                    $mysqli->query(
                        "UPDATE dashboard_user_shortcuts SET is_visible=1,updated_at=NOW() "
                        ."WHERE user_id=".$idUsuarioAcceso." AND access_id=".$accessId
                    );
                } elseif (intval($ordenFila['total']) < 20) {
                    $orden = max(1, intval($ordenFila['orden']));
                    $mysqli->query(
                        "INSERT INTO dashboard_user_shortcuts (user_id,access_id,shortcut_order,is_visible) "
                        ."VALUES (".$idUsuarioAcceso.",".$accessId.",".$orden.",1)"
                    );
                }
            }
        }
        goHighLevelRegistrarEvento(
            $mysqli,
            $contexto,
            'permisos_actualizados',
            'modulo',
            'gohighlevel',
            'Usuarios configurados: '.count($permisos)
        );
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        goHighLevelLanzar('permisos_no_guardados', 'No se pudieron guardar los permisos.', array(), 500);
    }
    return goHighLevelUsuariosPermisos($mysqli, $contexto);
}

?>

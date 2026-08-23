<?php

/**
 * Integracion de solo lectura entre Sistema Telar y GoHighLevel.
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
        && goHighLevelTablaExiste($mysqli, 'gohighlevel_evento');
}

function goHighLevelContextoUsuario($mysqli, $codUsuario)
{
    $codUsuario = intval($codUsuario);
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,IFNULL(p.nombre_persona,u.login) nombre,IFNULL(u.url,'') avatar,"
        ."IFNULL(g.puede_ver,0) puede_ver,IFNULL(g.puede_configurar,0) puede_configurar "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN gohighlevel_permiso_usuario g ON g.cod_usuarioFK=u.cod_usuario AND g.activo=1 "
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
    $puedeConfigurar = $esPropietario || intval($fila['puede_configurar']) === 1;
    if (!$puedeVer) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene acceso al modulo GoHighLevel.', array(), 403);
    }
    return array(
        'cod_usuario' => $codUsuario,
        'nombre' => (string)$fila['nombre'],
        'avatar' => (string)$fila['avatar'],
        'puede_ver' => $puedeVer,
        'puede_configurar' => $puedeConfigurar,
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
    return array(
        'base' => rtrim($base, '/'),
        'location_id' => preg_match('/^[A-Za-z0-9_-]{8,80}$/', $locationId) ? $locationId : '',
        'token' => strlen($token) >= 20 ? $token : '',
        'version' => preg_match('/^[A-Za-z0-9._-]{1,32}$/', $version) ? $version : '2021-07-28',
        'token_file' => $tokenFile
    );
}

function goHighLevelConfigurado($config)
{
    return is_array($config)
        && trim((string)$config['location_id']) !== ''
        && trim((string)$config['token']) !== '';
}

function goHighLevelApiGet($config, $ruta, $parametros)
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
        '/calendars/' => true
    );
    $rutaMensajes = preg_match('#^/conversations/[A-Za-z0-9_-]{8,80}/messages$#', $ruta) === 1;
    if (!isset($rutasPermitidas[$ruta]) && !$rutaMensajes) {
        goHighLevelLanzar('ruta_no_permitida', 'La consulta solicitada no esta permitida.', array(), 400);
    }
    if (!function_exists('curl_init')) {
        goHighLevelLanzar('cliente_http_no_disponible', 'El servidor no tiene habilitado el cliente seguro.', array(), 503);
    }
    $url = $config['base'].$ruta;
    if (is_array($parametros) && count($parametros) > 0) {
        $url .= '?'.http_build_query($parametros, '', '&');
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
            'Version: '.$config['version'],
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

function goHighLevelHayMas($total, $cantidad, $limite)
{
    return $cantidad >= $limite && ($total <= 0 || $cantidad < $total);
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

function goHighLevelListarConversaciones($mysqli, $config, $parametros)
{
    $limite = goHighLevelLimite(isset($parametros['limite']) ? $parametros['limite'] : 40, 40);
    $buscar = goHighLevelBusqueda($parametros);
    $apiParametros = array(
        'locationId' => $config['location_id'],
        'limit' => $limite,
        'sort' => 'desc'
    );
    if ($buscar !== '') {
        $apiParametros['query'] = $buscar;
    }
    $cursorFecha = goHighLevelMarcaTiempo(isset($parametros['cursor_fecha']) ? $parametros['cursor_fecha'] : '');
    if ($cursorFecha !== '') {
        $apiParametros['startAfterDate'] = $cursorFecha;
    }
    $respuesta = goHighLevelApiGet($config, '/conversations/search', $apiParametros);
    $items = goHighLevelItems($respuesta, array('conversations'));
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
            'responsable' => goHighLevelTexto(goHighLevelValor($item, array('assignedTo', 'assignedUserName')), 120),
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
        'paginacion' => array(
            'hay_mas' => !empty($contenedor['nextPage']),
            'last_message_id' => goHighLevelIdSeguro(goHighLevelValor($contenedor, array('lastMessageId'), ''))
        )
    );
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
    return array(
        'configurado' => goHighLevelConfigurado($config),
        'modo' => 'solo_lectura',
        'location_id' => (string)$config['location_id'],
        'vinculos' => $resumen,
        'protecciones' => array(
            'No crea ni actualiza contactos en GoHighLevel.',
            'No mueve oportunidades ni dispara automatizaciones.',
            'Solo vincula un paciente cuando existe una coincidencia telefonica unica.'
        )
    );
}

function goHighLevelUsuariosPermisos($mysqli, $contexto)
{
    if (!$contexto['puede_configurar']) {
        goHighLevelLanzar('accion_no_autorizada', 'No tiene permiso para configurar el modulo.', array(), 403);
    }
    $sql = "SELECT u.cod_usuario,IFNULL(p.nombre_persona,u.login) nombre,IFNULL(u.url,'') avatar,"
        ."IFNULL(l.Nombre,'') local,IFNULL(g.puede_ver,0) puede_ver,"
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
    $permisos = array(5994 => array('ver' => 1, 'configurar' => 1));
    foreach ($lista as $item) {
        if (!is_array($item)) {
            continue;
        }
        $id = intval(goHighLevelValor($item, array('cod_usuario'), 0));
        if ($id <= 0) {
            continue;
        }
        $configurar = intval(goHighLevelValor($item, array('puede_configurar'), 0)) === 1 ? 1 : 0;
        $ver = ($configurar || intval(goHighLevelValor($item, array('puede_ver'), 0)) === 1) ? 1 : 0;
        if ($id === 5994) {
            $ver = 1;
            $configurar = 1;
        }
        $permisos[$id] = array('ver' => $ver, 'configurar' => $configurar);
    }
    $actor = intval($contexto['cod_usuario']);
    $mysqli->begin_transaction();
    try {
        if (!$mysqli->query(
            "UPDATE gohighlevel_permiso_usuario SET puede_ver=0,puede_configurar=0,activo=0,"
            ."cod_usuario_actualizaFK=".$actor.",fecha_actualizacion=NOW()"
        )) {
            throw new Exception('No se pudieron preparar los permisos.');
        }
        $stmt = $mysqli->prepare(
            "INSERT INTO gohighlevel_permiso_usuario "
            ."(cod_usuarioFK,puede_ver,puede_configurar,activo,cod_usuario_actualizaFK,fecha_creacion,fecha_actualizacion) "
            ."VALUES (?,?,?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE "
            ."puede_ver=VALUES(puede_ver),puede_configurar=VALUES(puede_configurar),activo=VALUES(activo),"
            ."cod_usuario_actualizaFK=VALUES(cod_usuario_actualizaFK),fecha_actualizacion=NOW()"
        );
        if (!$stmt) {
            throw new Exception('No se pudieron preparar los permisos.');
        }
        foreach ($permisos as $id => $permiso) {
            $activo = $permiso['ver'] ? 1 : 0;
            $idUsuario = intval($id);
            $puedeVer = intval($permiso['ver']);
            $puedeConfigurar = intval($permiso['configurar']);
            $stmt->bind_param('iiiii', $idUsuario, $puedeVer, $puedeConfigurar, $activo, $actor);
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

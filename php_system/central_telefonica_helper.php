<?php

/**
 * Funciones compartidas del monitor CDR de Central Telefonica.
 *
 * Este archivo no abre conexiones a Issabel ni ejecuta operaciones por si solo.
 * Es compatible con PHP 7.2 y puede reutilizarse desde el endpoint y el proceso
 * de sincronizacion por linea de comandos.
 */

function centralTelefonicaUtf8($valor)
{
    if (is_array($valor)) {
        $salida = array();
        foreach ($valor as $clave => $item) {
            $salida[$clave] = centralTelefonicaUtf8($item);
        }
        return $salida;
    }

    if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
        return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
    }

    return $valor;
}

function centralTelefonicaTablaExiste($mysqli, $tabla)
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

function centralTelefonicaEstructuraDisponible($mysqli)
{
    return centralTelefonicaTablaExiste($mysqli, 'central_telefonica_cdr_segmento')
        && centralTelefonicaTablaExiste($mysqli, 'central_telefonica_llamada')
        && centralTelefonicaTablaExiste($mysqli, 'central_telefonica_sincronizacion');
}

function centralTelefonicaTienePermiso($mysqli, $codUsuario, $codigo)
{
    static $cache = array();
    $codigo = strtoupper(trim((string)$codigo));
    $clave = intval($codUsuario).'|'.$codigo;

    if (isset($cache[$clave])) {
        return $cache[$clave];
    }

    $sql = "SELECT 1
        FROM accesosuser au
        INNER JOIN listadodeacceso la
            ON la.idlistadodeacceso=au.idlistadodeaccesoFK
        WHERE au.usuarios_idusario=?
          AND UPPER(TRIM(la.codigo))=?
          AND UPPER(TRIM(au.accion))='SI'
          AND au.tipo='Administrativo'
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }

    $codigoIso = mb_convert_encoding($codigo, 'ISO-8859-1', 'UTF-8');
    $usuario = intval($codUsuario);
    $stmt->bind_param('is', $usuario, $codigoIso);
    $permitido = false;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $permitido = $resultado && $resultado->num_rows > 0;
    }
    $stmt->close();
    $cache[$clave] = $permitido;
    return $permitido;
}

function centralTelefonicaRutaConfiguracionPrivada()
{
    $rutaEntorno = getenv('TELAR_ISSABEL_CDR_CONFIG');
    if (is_string($rutaEntorno) && trim($rutaEntorno) !== '') {
        return trim($rutaEntorno);
    }

    $raizWamp = dirname(dirname(dirname(__DIR__)));
    return $raizWamp.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR
        .'GoodVentaAsisCap'.DIRECTORY_SEPARATOR.'central_telefonica_issabel.php';
}

function centralTelefonicaCargarConfiguracionIssabel()
{
    $config = array(
        'host' => '',
        'port' => 3306,
        'database' => 'asteriskcdrdb',
        'table' => 'cdr',
        'user' => '',
        'password' => '',
        'charset' => 'utf8',
        'initial_days' => 30,
        'overlap_minutes' => 10,
        'batch_limit' => 5000,
        'extension_patterns' => array('/^[1-9][0-9]{2,4}$/'),
        'service_patterns' => array('/^[*#]/', '/^(s|h|i|t)$/i'),
        'inbound_context_patterns' => array('/from-trunk/i', '/from-pstn/i', '/from-gsm/i'),
        'outbound_context_patterns' => array('/from-internal/i', '/outbound/i'),
        'trunk_patterns' => array('/trunk/i', '/gateway/i', '/gsm/i', '/to-gw/i', '/from-gw/i')
    );

    $ruta = centralTelefonicaRutaConfiguracionPrivada();
    if (is_file($ruta) && is_readable($ruta)) {
        $privada = include $ruta;
        if (is_array($privada)) {
            $config = array_replace_recursive($config, $privada);
        }
    }

    $entorno = array(
        'host' => 'TELAR_ISSABEL_DB_HOST',
        'port' => 'TELAR_ISSABEL_DB_PORT',
        'database' => 'TELAR_ISSABEL_DB_NAME',
        'table' => 'TELAR_ISSABEL_CDR_TABLE',
        'user' => 'TELAR_ISSABEL_DB_USER',
        'password' => 'TELAR_ISSABEL_DB_PASSWORD'
    );
    foreach ($entorno as $clave => $nombre) {
        $valor = getenv($nombre);
        if ($valor !== false && trim((string)$valor) !== '') {
            $config[$clave] = $clave === 'port' ? intval($valor) : (string)$valor;
        }
    }

    $config['port'] = max(1, intval($config['port']));
    $config['initial_days'] = max(1, min(90, intval($config['initial_days'])));
    $config['overlap_minutes'] = max(1, min(60, intval($config['overlap_minutes'])));
    $config['batch_limit'] = max(100, min(20000, intval($config['batch_limit'])));
    return $config;
}

function centralTelefonicaConfiguracionDisponible($config)
{
    return is_array($config)
        && trim((string)$config['host']) !== ''
        && trim((string)$config['database']) !== ''
        && trim((string)$config['table']) !== ''
        && trim((string)$config['user']) !== ''
        && trim((string)$config['password']) !== '';
}

function centralTelefonicaNormalizarTelefono($valor)
{
    $original = trim((string)$valor);
    if ($original === '') {
        return '';
    }

    $digitos = preg_replace('/[^0-9]/', '', $original);
    if ($digitos === '') {
        return preg_replace('/\s+/', '', $original);
    }

    if (substr($digitos, 0, 2) === '00') {
        $digitos = substr($digitos, 2);
    }
    if (substr($digitos, 0, 4) === '5950') {
        $digitos = '595'.substr($digitos, 4);
    }
    if (substr($digitos, 0, 3) === '595' && strlen($digitos) >= 11) {
        return '+'.$digitos;
    }
    if (strlen($digitos) === 10 && substr($digitos, 0, 1) === '0') {
        return '+595'.substr($digitos, 1);
    }
    if (strlen($digitos) === 9 && substr($digitos, 0, 1) === '9') {
        return '+595'.$digitos;
    }
    if (substr($original, 0, 1) === '+' && strlen($digitos) >= 8) {
        return '+'.$digitos;
    }

    return $digitos;
}

function centralTelefonicaMascararTelefono($valor)
{
    $texto = trim((string)$valor);
    if ($texto === '') {
        return '';
    }

    $prefijo = substr($texto, 0, 1) === '+' ? '+' : '';
    $digitos = preg_replace('/[^0-9]/', '', $texto);
    $largo = strlen($digitos);
    if ($largo <= 4) {
        return str_repeat('*', max(3, $largo));
    }

    $visiblesInicio = $largo >= 10 ? 4 : 2;
    return $prefijo.substr($digitos, 0, $visiblesInicio)
        .str_repeat('*', max(3, $largo - $visiblesInicio - 3))
        .substr($digitos, -3);
}

function centralTelefonicaCoincidePatrones($valor, $patrones)
{
    $valor = (string)$valor;
    if (!is_array($patrones)) {
        return false;
    }

    foreach ($patrones as $patron) {
        if (!is_string($patron) || $patron === '') {
            continue;
        }
        $resultado = @preg_match($patron, $valor);
        if ($resultado === 1) {
            return true;
        }
    }
    return false;
}

function centralTelefonicaNumeroEsExtension($numero, $config)
{
    $numero = preg_replace('/[^0-9]/', '', (string)$numero);
    if ($numero === '') {
        return false;
    }
    $patrones = isset($config['extension_patterns']) ? $config['extension_patterns'] : array();
    return centralTelefonicaCoincidePatrones($numero, $patrones);
}

function centralTelefonicaNumeroEsServicio($numero, $config)
{
    $numero = trim((string)$numero);
    if ($numero === '') {
        return true;
    }
    $patrones = isset($config['service_patterns']) ? $config['service_patterns'] : array();
    return centralTelefonicaCoincidePatrones($numero, $patrones);
}

function centralTelefonicaNormalizarDisposicion($valor)
{
    $texto = strtoupper(trim((string)$valor));
    $texto = strtr($texto, array(
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U'
    ));

    if (in_array($texto, array('ANSWERED', 'CONTESTADO', 'CONTESTADA'), true)) {
        return 'contestada';
    }
    if (in_array($texto, array('BUSY', 'OCUPADO', 'OCUPADA'), true)) {
        return 'ocupada';
    }
    if (in_array($texto, array('FAILED', 'FAIL', 'FALLIDO', 'FALLIDA'), true)) {
        return 'fallida';
    }
    if (in_array($texto, array('CONGESTION', 'CONGESTIONADO', 'CHANUNAVAIL'), true)) {
        return 'congestion';
    }
    if (in_array($texto, array('NO ANSWER', 'NOANSWER', 'NO CONTESTADO', 'NO CONTESTADA'), true)) {
        return 'no_contestada';
    }
    return $texto === '' ? 'sin_estado' : strtolower(str_replace(' ', '_', $texto));
}

function centralTelefonicaEstadoConsolidado($segmentos)
{
    $prioridad = array(
        'contestada' => 60,
        'ocupada' => 50,
        'congestion' => 40,
        'fallida' => 30,
        'no_contestada' => 20,
        'sin_estado' => 0
    );
    $elegido = 'sin_estado';
    $puntaje = -1;

    foreach ($segmentos as $segmento) {
        $estado = centralTelefonicaNormalizarDisposicion(
            isset($segmento['disposicion']) ? $segmento['disposicion'] : ''
        );
        $hablado = isset($segmento['hablado_seg']) ? intval($segmento['hablado_seg']) : 0;
        if ($hablado > 0) {
            $estado = 'contestada';
        }
        $actual = isset($prioridad[$estado]) ? $prioridad[$estado] : 10;
        if ($actual > $puntaje) {
            $puntaje = $actual;
            $elegido = $estado;
        }
    }

    return $elegido;
}

function centralTelefonicaClasificarSegmentos($segmentos, $config)
{
    $puntajes = array(
        'entrante_externa' => 0,
        'saliente_externa' => 0,
        'interna' => 0,
        'servicio_prueba' => 0
    );
    $motivos = array();
    $patronesTrunk = isset($config['trunk_patterns']) ? $config['trunk_patterns'] : array();
    $patronesEntrada = isset($config['inbound_context_patterns']) ? $config['inbound_context_patterns'] : array();
    $patronesSalida = isset($config['outbound_context_patterns']) ? $config['outbound_context_patterns'] : array();

    foreach ($segmentos as $segmento) {
        $origen = isset($segmento['origen_original']) ? $segmento['origen_original'] : '';
        $destino = isset($segmento['destino_original']) ? $segmento['destino_original'] : '';
        $contexto = isset($segmento['contexto']) ? $segmento['contexto'] : '';
        $canal = isset($segmento['canal']) ? $segmento['canal'] : '';
        $canalDestino = isset($segmento['canal_destino']) ? $segmento['canal_destino'] : '';
        $origenInterno = centralTelefonicaNumeroEsExtension($origen, $config);
        $destinoInterno = centralTelefonicaNumeroEsExtension($destino, $config);
        $origenServicio = centralTelefonicaNumeroEsServicio($origen, $config);
        $destinoServicio = centralTelefonicaNumeroEsServicio($destino, $config);
        $canalTrunk = centralTelefonicaCoincidePatrones($canal, $patronesTrunk);
        $destinoTrunk = centralTelefonicaCoincidePatrones($canalDestino, $patronesTrunk);

        if ($origenInterno && $destinoInterno) {
            $puntajes['interna'] += 5;
            $motivos['interna'] = 'origen y destino corresponden a extensiones internas';
        }
        if ($origenInterno && !$destinoInterno && !$destinoServicio) {
            $puntajes['saliente_externa'] += 6;
            $motivos['saliente_externa'] = 'una extension interna origina la llamada hacia un numero externo';
        }
        if (!$origenInterno && !$origenServicio && $destinoInterno) {
            $puntajes['entrante_externa'] += 6;
            $motivos['entrante_externa'] = 'un numero externo ingresa hacia una extension interna';
        }
        if (centralTelefonicaCoincidePatrones($contexto, $patronesEntrada)) {
            $puntajes['entrante_externa'] += 5;
            $motivos['entrante_externa'] = 'el contexto corresponde a una ruta de entrada';
        }
        if (centralTelefonicaCoincidePatrones($contexto, $patronesSalida)) {
            $puntajes['saliente_externa'] += 4;
            $motivos['saliente_externa'] = 'el contexto corresponde a una ruta de salida';
        }
        if ($origenInterno && $destinoTrunk) {
            $puntajes['saliente_externa'] += 4;
            $motivos['saliente_externa'] = 'el canal de destino utiliza un trunk externo';
        }
        if ($canalTrunk && $destinoInterno) {
            $puntajes['entrante_externa'] += 4;
            $motivos['entrante_externa'] = 'el canal de origen utiliza un trunk externo';
        }
        if ($origenServicio || $destinoServicio) {
            $puntajes['servicio_prueba'] += 2;
            $motivos['servicio_prueba'] = 'el origen o destino coincide con un codigo de servicio';
        }
    }

    $orden = array('entrante_externa', 'saliente_externa', 'interna', 'servicio_prueba');
    $elegido = 'sin_clasificar';
    $mayor = 0;
    foreach ($orden as $tipo) {
        if ($puntajes[$tipo] > $mayor) {
            $mayor = $puntajes[$tipo];
            $elegido = $tipo;
        }
    }

    return array(
        'tipo' => $elegido,
        'motivo' => $elegido === 'sin_clasificar'
            ? 'no existen suficientes datos para clasificar con seguridad'
            : $motivos[$elegido],
        'puntajes' => $puntajes
    );
}

function centralTelefonicaClaveSegmento($segmento)
{
    $partes = array(
        isset($segmento['cdr_uniqueid']) ? $segmento['cdr_uniqueid'] : '',
        isset($segmento['cdr_sequence']) ? $segmento['cdr_sequence'] : '',
        isset($segmento['fecha_inicio']) ? $segmento['fecha_inicio'] : '',
        isset($segmento['origen_original']) ? $segmento['origen_original'] : '',
        isset($segmento['destino_original']) ? $segmento['destino_original'] : '',
        isset($segmento['canal']) ? $segmento['canal'] : '',
        isset($segmento['canal_destino']) ? $segmento['canal_destino'] : '',
        isset($segmento['disposicion']) ? $segmento['disposicion'] : '',
        isset($segmento['duracion_seg']) ? intval($segmento['duracion_seg']) : 0,
        isset($segmento['hablado_seg']) ? intval($segmento['hablado_seg']) : 0
    );
    return hash('sha256', implode('|', $partes));
}

function centralTelefonicaClaveGrupo($segmento)
{
    $linkedid = trim((string)(isset($segmento['cdr_linkedid']) ? $segmento['cdr_linkedid'] : ''));
    if ($linkedid !== '') {
        return 'linkedid:'.$linkedid;
    }
    $uniqueid = trim((string)(isset($segmento['cdr_uniqueid']) ? $segmento['cdr_uniqueid'] : ''));
    if ($uniqueid !== '') {
        return 'uniqueid:'.$uniqueid;
    }
    return 'segmento:'.centralTelefonicaClaveSegmento($segmento);
}

function centralTelefonicaSumarSegundos($fecha, $segundos)
{
    $marca = strtotime((string)$fecha);
    if ($marca === false) {
        return (string)$fecha;
    }
    return date('Y-m-d H:i:s', $marca + max(0, intval($segundos)));
}

function centralTelefonicaExtensionDesdeCanal($canal, $config)
{
    $canal = trim((string)$canal);
    if ($canal === '') {
        return '';
    }
    $patrones = array(
        '/^(?:PJSIP|SIP|IAX2)\/([0-9]{2,8})(?:[-@\/]|$)/i',
        '/^Local\/([0-9]{2,8})(?:@|[-\/]|$)/i'
    );
    foreach ($patrones as $patron) {
        if (preg_match($patron, $canal, $coincidencia)
            && centralTelefonicaNumeroEsExtension($coincidencia[1], $config)) {
            return preg_replace('/[^0-9]/', '', $coincidencia[1]);
        }
    }
    return '';
}

function centralTelefonicaIdentidadOperativa($segmentos, $tipo, $config)
{
    $identidad = array(
        'ruta_extension' => '',
        'funcionario_extension' => '',
        'funcionario_destino_extension' => ''
    );
    foreach ((array)$segmentos as $segmento) {
        $origen = isset($segmento['origen_original']) ? $segmento['origen_original'] : '';
        $destino = isset($segmento['destino_original']) ? $segmento['destino_original'] : '';
        $origenExtension = centralTelefonicaNumeroEsExtension($origen, $config)
            ? preg_replace('/[^0-9]/', '', $origen) : '';
        $destinoExtension = centralTelefonicaNumeroEsExtension($destino, $config)
            ? preg_replace('/[^0-9]/', '', $destino) : '';
        $canalDestinoExtension = centralTelefonicaExtensionDesdeCanal(
            isset($segmento['canal_destino']) ? $segmento['canal_destino'] : '',
            $config
        );
        $estado = centralTelefonicaNormalizarDisposicion(
            isset($segmento['disposicion']) ? $segmento['disposicion'] : ''
        );
        $contestado = $estado === 'contestada'
            || intval(isset($segmento['hablado_seg']) ? $segmento['hablado_seg'] : 0) > 0;

        if ($tipo === 'saliente_externa' && $identidad['funcionario_extension'] === ''
            && $origenExtension !== '') {
            $identidad['funcionario_extension'] = $origenExtension;
        }
        if ($tipo === 'entrante_externa') {
            if ($identidad['ruta_extension'] === '' && $destinoExtension !== '') {
                $identidad['ruta_extension'] = $destinoExtension;
            }
            if ($contestado && $canalDestinoExtension !== ''
                && $canalDestinoExtension !== $identidad['ruta_extension']) {
                $identidad['funcionario_extension'] = $canalDestinoExtension;
            } elseif ($contestado && $destinoExtension !== ''
                && $destinoExtension !== $identidad['ruta_extension']) {
                $identidad['funcionario_extension'] = $destinoExtension;
            }
        }
        if ($tipo === 'interna') {
            if ($identidad['funcionario_extension'] === '' && $origenExtension !== '') {
                $identidad['funcionario_extension'] = $origenExtension;
            }
            if ($identidad['funcionario_destino_extension'] === ''
                && $destinoExtension !== '') {
                $identidad['funcionario_destino_extension'] = $destinoExtension;
            }
        }
    }
    if ($tipo === 'entrante_externa' && $identidad['funcionario_extension'] === '') {
        $identidad['funcionario_extension'] = $identidad['ruta_extension'];
    }
    return $identidad;
}

function centralTelefonicaConstruirConsolidado($segmentos, $config)
{
    if (!is_array($segmentos) || count($segmentos) === 0) {
        return null;
    }

    usort($segmentos, function ($a, $b) {
        $fechaA = isset($a['fecha_inicio']) ? $a['fecha_inicio'] : '';
        $fechaB = isset($b['fecha_inicio']) ? $b['fecha_inicio'] : '';
        if ($fechaA === $fechaB) {
            return intval(isset($a['id_segmento']) ? $a['id_segmento'] : 0)
                - intval(isset($b['id_segmento']) ? $b['id_segmento'] : 0);
        }
        return strcmp($fechaA, $fechaB);
    });

    $primero = $segmentos[0];
    $clasificacion = centralTelefonicaClasificarSegmentos($segmentos, $config);
    $tipo = $clasificacion['tipo'];
    $fechaInicio = isset($primero['fecha_inicio']) ? $primero['fecha_inicio'] : '';
    $duracion = 0;
    $hablado = 0;
    $grabacion = 0;
    $segmentoGrabacion = null;
    $extension = '';
    $origen = isset($primero['origen_original']) ? $primero['origen_original'] : '';
    $destino = isset($primero['destino_original']) ? $primero['destino_original'] : '';

    foreach ($segmentos as $segmento) {
        $duracion = max($duracion, intval(isset($segmento['duracion_seg']) ? $segmento['duracion_seg'] : 0));
        $hablado = max($hablado, intval(isset($segmento['hablado_seg']) ? $segmento['hablado_seg'] : 0));
        if (!empty($segmento['grabacion_disponible']) || trim((string)(isset($segmento['grabacion_referencia']) ? $segmento['grabacion_referencia'] : '')) !== '') {
            $grabacion = 1;
            if ($segmentoGrabacion === null && isset($segmento['id_segmento'])) {
                $segmentoGrabacion = intval($segmento['id_segmento']);
            }
        }

        $src = isset($segmento['origen_original']) ? $segmento['origen_original'] : '';
        $dst = isset($segmento['destino_original']) ? $segmento['destino_original'] : '';
        if ($tipo === 'saliente_externa' && centralTelefonicaNumeroEsExtension($src, $config)) {
            $extension = $extension === '' ? preg_replace('/[^0-9]/', '', $src) : $extension;
            if (!centralTelefonicaNumeroEsExtension($dst, $config)) {
                $destino = $dst;
            }
        } elseif ($tipo === 'entrante_externa' && centralTelefonicaNumeroEsExtension($dst, $config)) {
            $extension = $extension === '' ? preg_replace('/[^0-9]/', '', $dst) : $extension;
            if (!centralTelefonicaNumeroEsExtension($src, $config)) {
                $origen = $src;
            }
        } elseif ($tipo === 'interna' && $extension === '') {
            $extension = preg_replace('/[^0-9]/', '', $src);
        }
    }

    $grupo = centralTelefonicaClaveGrupo($primero);
    $identidad = centralTelefonicaIdentidadOperativa($segmentos, $tipo, $config);
    return array(
        'llamada_clave' => hash('sha256', 'issabel|'.$grupo),
        'grupo_clave' => $grupo,
        'cdr_linkedid' => isset($primero['cdr_linkedid']) ? $primero['cdr_linkedid'] : '',
        'cdr_uniqueid_principal' => isset($primero['cdr_uniqueid']) ? $primero['cdr_uniqueid'] : '',
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => centralTelefonicaSumarSegundos($fechaInicio, $duracion),
        'tipo' => $tipo,
        'estado' => centralTelefonicaEstadoConsolidado($segmentos),
        'origen_original' => $origen,
        'destino_original' => $destino,
        'origen_normalizado' => centralTelefonicaNormalizarTelefono($origen),
        'destino_normalizado' => centralTelefonicaNormalizarTelefono($destino),
        'extension' => $extension,
        'ruta_extension' => $identidad['ruta_extension'],
        'funcionario_extension' => $identidad['funcionario_extension'],
        'funcionario_destino_extension' => $identidad['funcionario_destino_extension'],
        'duracion_seg' => $duracion,
        'hablado_seg' => $hablado,
        'cantidad_segmentos' => count($segmentos),
        'grabacion_disponible' => $grabacion,
        'grabacion_segmento_id' => $segmentoGrabacion,
        'clasificacion_motivo' => $clasificacion['motivo']
    );
}

function centralTelefonicaFormatearDuracion($segundos)
{
    $segundos = max(0, intval($segundos));
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    $resto = $segundos % 60;
    if ($horas > 0) {
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $resto);
    }
    return sprintf('%02d:%02d', $minutos, $resto);
}

function centralTelefonicaErrorSeguro($codigo)
{
    $permitidos = array(
        'configuracion_no_disponible',
        'conexion_issabel_no_disponible',
        'tabla_cdr_no_disponible',
        'esquema_cdr_incompatible',
        'consulta_cdr_no_disponible',
        'persistencia_no_disponible'
    );
    return in_array($codigo, $permitidos, true) ? $codigo : 'error_sincronizacion';
}

?>

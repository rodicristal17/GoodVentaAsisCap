<?php

require_once __DIR__.'/central_telefonica_helper.php';

class CentralTelefonicaDirectorioExcepcion extends Exception
{
    public $codigoOperacion;

    public function __construct($codigo, $mensaje)
    {
        parent::__construct($mensaje);
        $this->codigoOperacion = $codigo;
    }
}

function centralTelefonicaDirectorioLanzar($codigo, $mensaje)
{
    throw new CentralTelefonicaDirectorioExcepcion($codigo, $mensaje);
}

function centralTelefonicaDirectorioColumnaExiste($mysqli, $tabla, $columna)
{
    $stmt = $mysqli->prepare(
        'SELECT COUNT(*) total FROM information_schema.columns '
        .'WHERE table_schema=DATABASE() AND table_name=? AND column_name=?'
    );
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $fila && intval($fila['total']) === 1;
}

function centralTelefonicaDirectorioEstructuraDisponible($mysqli)
{
    static $cache = array();
    $claveCache = is_object($mysqli) ? spl_object_hash($mysqli) : 'sin_conexion';
    if (array_key_exists($claveCache, $cache)) {
        return $cache[$claveCache];
    }
    $columnas = array(
        'ruta_extension', 'ruta_tipo', 'ruta_nombre',
        'funcionario_extension', 'funcionario_nombre', 'funcionario_sede',
        'funcionario_cod_usuario', 'funcionario_cod_local',
        'funcionario_destino_extension', 'funcionario_destino_nombre',
        'funcionario_destino_sede', 'funcionario_destino_cod_usuario',
        'funcionario_destino_cod_local'
    );
    if (!centralTelefonicaTablaExiste($mysqli, 'central_telefonica_directorio')) {
        $cache[$claveCache] = false;
        return false;
    }
    foreach ($columnas as $columna) {
        if (!centralTelefonicaDirectorioColumnaExiste(
            $mysqli,
            'central_telefonica_llamada',
            $columna
        )) {
            $cache[$claveCache] = false;
            return false;
        }
    }
    $cache[$claveCache] = true;
    return true;
}

function centralTelefonicaDirectorioManualDisponible($mysqli)
{
    return centralTelefonicaDirectorioEstructuraDisponible($mysqli)
        && centralTelefonicaDirectorioColumnaExiste(
            $mysqli,
            'central_telefonica_directorio',
            'cargo_visible'
        );
}

function centralTelefonicaDirectorioConfig($configBase)
{
    $directorio = array(
        'enabled' => false,
        'host' => isset($configBase['host']) ? $configBase['host'] : '',
        'port' => isset($configBase['port']) ? intval($configBase['port']) : 3306,
        'database' => 'asterisk',
        'user' => '',
        'password' => '',
        'charset' => 'utf8',
        'users_table' => 'users',
        'devices_table' => 'devices',
        'queues_table' => 'queues_config'
    );
    if (isset($configBase['directory']) && is_array($configBase['directory'])) {
        $directorio = array_replace($directorio, $configBase['directory']);
    }
    $entorno = array(
        'host' => 'TELAR_ISSABEL_DIRECTORY_DB_HOST',
        'port' => 'TELAR_ISSABEL_DIRECTORY_DB_PORT',
        'database' => 'TELAR_ISSABEL_DIRECTORY_DB_NAME',
        'user' => 'TELAR_ISSABEL_DIRECTORY_DB_USER',
        'password' => 'TELAR_ISSABEL_DIRECTORY_DB_PASSWORD',
        'users_table' => 'TELAR_ISSABEL_DIRECTORY_USERS_TABLE',
        'devices_table' => 'TELAR_ISSABEL_DIRECTORY_DEVICES_TABLE',
        'queues_table' => 'TELAR_ISSABEL_DIRECTORY_QUEUES_TABLE'
    );
    foreach ($entorno as $clave => $nombre) {
        $valor = getenv($nombre);
        if ($valor !== false && trim((string)$valor) !== '') {
            $directorio[$clave] = $clave === 'port' ? intval($valor) : (string)$valor;
        }
    }
    $habilitado = getenv('TELAR_ISSABEL_DIRECTORY_ENABLED');
    if ($habilitado !== false && trim((string)$habilitado) !== '') {
        $directorio['enabled'] = in_array(
            strtolower(trim((string)$habilitado)),
            array('1', 'true', 'si', 'yes'),
            true
        );
    }
    $directorio['port'] = max(1, intval($directorio['port']));
    return $directorio;
}

function centralTelefonicaDirectorioConfiguracionDisponible($config)
{
    return is_array($config)
        && !empty($config['enabled'])
        && trim((string)$config['host']) !== ''
        && trim((string)$config['database']) !== ''
        && trim((string)$config['user']) !== ''
        && trim((string)$config['password']) !== '';
}

function centralTelefonicaDirectorioIdentificador($valor)
{
    $valor = trim((string)$valor);
    if ($valor === '' || !preg_match('/^[A-Za-z0-9_]+$/', $valor)) {
        centralTelefonicaDirectorioLanzar(
            'directorio_configuracion_invalida',
            'La configuracion del directorio contiene un identificador no valido.'
        );
    }
    return '`'.$valor.'`';
}

function centralTelefonicaDirectorioConectarFuente($config)
{
    if (!centralTelefonicaDirectorioConfiguracionDisponible($config)) {
        centralTelefonicaDirectorioLanzar(
            'directorio_no_configurado',
            'El directorio de Issabel todavia no esta configurado.'
        );
    }
    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }
    $conexion = @new mysqli(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database'],
        intval($config['port'])
    );
    if ($conexion->connect_errno) {
        centralTelefonicaDirectorioLanzar(
            'directorio_conexion_no_disponible',
            'No se pudo consultar el directorio de Issabel.'
        );
    }
    if (!$conexion->set_charset(isset($config['charset']) ? $config['charset'] : 'utf8')) {
        $conexion->close();
        centralTelefonicaDirectorioLanzar(
            'directorio_charset_no_disponible',
            'No se pudo preparar la lectura del directorio de Issabel.'
        );
    }
    return $conexion;
}

function centralTelefonicaDirectorioLeerTabla(
    $conexion,
    $tabla,
    $columnaExtension,
    $columnaNombre,
    $tipo
) {
    $sql = 'SELECT '.centralTelefonicaDirectorioIdentificador($columnaExtension)
        .' AS extension,'.centralTelefonicaDirectorioIdentificador($columnaNombre)
        .' AS nombre FROM '.centralTelefonicaDirectorioIdentificador($tabla);
    $resultado = $conexion->query($sql);
    if (!$resultado) {
        centralTelefonicaDirectorioLanzar(
            'directorio_esquema_incompatible',
            'El directorio de Issabel no contiene las columnas esperadas.'
        );
    }
    $filas = array();
    while ($fila = $resultado->fetch_assoc()) {
        $extension = preg_replace('/[^0-9]/', '', (string)$fila['extension']);
        if ($extension === '') {
            continue;
        }
        $filas[] = array(
            'extension' => $extension,
            'tipo' => $tipo,
            'nombre' => trim((string)$fila['nombre']),
            'descripcion' => trim((string)$fila['nombre'])
        );
    }
    $resultado->free();
    return $filas;
}

function centralTelefonicaDirectorioLeerFuente($conexion, $config)
{
    $mapa = array();
    $grupos = array(
        array($config['devices_table'], 'id', 'description', 'interna'),
        array($config['users_table'], 'extension', 'name', 'funcionario'),
        array($config['queues_table'], 'extension', 'descr', 'cola')
    );
    foreach ($grupos as $grupo) {
        $filas = centralTelefonicaDirectorioLeerTabla(
            $conexion,
            $grupo[0],
            $grupo[1],
            $grupo[2],
            $grupo[3]
        );
        foreach ($filas as $fila) {
            $mapa[$fila['extension']] = $fila;
        }
    }
    ksort($mapa, SORT_NATURAL);
    return array_values($mapa);
}

function centralTelefonicaDirectorioResolver($mysqli, $extensiones)
{
    $mapa = array();
    if (!centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
        return $mapa;
    }
    $limpias = array();
    foreach ((array)$extensiones as $extension) {
        $extension = preg_replace('/[^0-9]/', '', (string)$extension);
        if ($extension !== '') {
            $limpias[$extension] = true;
        }
    }
    if (!$limpias) {
        return $mapa;
    }
    $valores = array_keys($limpias);
    $marcas = implode(',', array_fill(0, count($valores), '?'));
    $cargoCampo = centralTelefonicaDirectorioManualDisponible($mysqli)
        ? 'd.cargo_visible' : "''";
    $sql = "SELECT d.extension,d.tipo,d.nombre,d.sede_nombre,d.cod_usuarioFK,d.cod_localFK,"
        .$cargoCampo." cargo_visible,IFNULL(u.url,'') avatar_visible,"
        ."COALESCE(NULLIF(p.nombre_persona,''),NULLIF(d.nombre,''),'') nombre_visible,"
        ."COALESCE(NULLIF(l.Nombre,''),NULLIF(d.sede_nombre,''),'') sede_visible "
        ."FROM central_telefonica_directorio d "
        ."LEFT JOIN usuario u ON u.cod_usuario=d.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=d.cod_localFK "
        ."WHERE d.activo=1 AND d.extension IN (".$marcas.")";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return $mapa;
    }
    $tipos = str_repeat('s', count($valores));
    $referencias = array(&$tipos);
    foreach ($valores as $indice => $valor) {
        $referencias[] = &$valores[$indice];
    }
    call_user_func_array(array($stmt, 'bind_param'), $referencias);
    if (!$stmt->execute()) {
        $stmt->close();
        return $mapa;
    }
    $resultado = $stmt->get_result();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $mapa[$fila['extension']] = array(
            'extension' => $fila['extension'],
            'tipo' => $fila['tipo'],
            'nombre' => $fila['nombre_visible'],
            'nombre_tecnico' => $fila['nombre'],
            'cargo' => $fila['cargo_visible'],
            'sede' => $fila['sede_visible'],
            'avatar' => trim((string)$fila['avatar_visible']) !== ''
                ? (string)$fila['avatar_visible'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
            'cod_usuario' => $fila['cod_usuarioFK'] === null ? 0 : intval($fila['cod_usuarioFK']),
            'cod_local' => $fila['cod_localFK'] === null ? 0 : intval($fila['cod_localFK']),
            'sin_renombrar' => trim((string)$fila['cargo_visible']) === ''
        );
    }
    $stmt->close();
    return $mapa;
}

function centralTelefonicaDirectorioEnriquecerConsolidado(
    $mysqli,
    $llamada,
    $segmentos,
    $config
) {
    $identidad = centralTelefonicaIdentidadOperativa(
        $segmentos,
        isset($llamada['tipo']) ? $llamada['tipo'] : '',
        $config
    );
    $tipo = isset($llamada['tipo']) ? $llamada['tipo'] : '';
    $rutaExtension = $identidad['ruta_extension'];
    $funcionarioExtension = $identidad['funcionario_extension'];
    $funcionarioDestino = $identidad['funcionario_destino_extension'];
    $directorio = centralTelefonicaDirectorioResolver(
        $mysqli,
        array($rutaExtension, $funcionarioExtension, $funcionarioDestino)
    );
    $ruta = isset($directorio[$rutaExtension]) ? $directorio[$rutaExtension] : null;
    $funcionario = isset($directorio[$funcionarioExtension])
        ? $directorio[$funcionarioExtension] : null;
    $destino = isset($directorio[$funcionarioDestino])
        ? $directorio[$funcionarioDestino] : null;

    $llamada['ruta_extension'] = $rutaExtension;
    $llamada['ruta_tipo'] = $tipo === 'saliente_externa' ? 'salida'
        : ($tipo === 'interna' ? 'interna' : ($ruta ? $ruta['tipo'] : ''));
    $llamada['ruta_nombre'] = $tipo === 'saliente_externa' ? 'Salida directa'
        : ($tipo === 'interna' ? 'Llamada interna' : ($ruta ? $ruta['nombre'] : ''));

    if ($tipo === 'entrante_externa' && $funcionarioExtension === $rutaExtension
        && (!$ruta || $llamada['ruta_tipo'] === 'cola')) {
        $funcionarioExtension = '';
        $funcionario = null;
    }
    if ($tipo === 'entrante_externa' && $funcionarioExtension === ''
        && $rutaExtension !== '' && $ruta
        && in_array($ruta['tipo'], array('funcionario', 'interna'), true)) {
        $funcionarioExtension = $rutaExtension;
        $funcionario = $ruta;
    }

    $llamada['funcionario_extension'] = $funcionarioExtension;
    $llamada['funcionario_nombre'] = $funcionario ? $funcionario['nombre'] : '';
    $llamada['funcionario_sede'] = $funcionario ? $funcionario['sede'] : '';
    $llamada['funcionario_cod_usuario'] = $funcionario ? $funcionario['cod_usuario'] : null;
    $llamada['funcionario_cod_local'] = $funcionario ? $funcionario['cod_local'] : null;
    $llamada['funcionario_destino_extension'] = $funcionarioDestino;
    $llamada['funcionario_destino_nombre'] = $destino ? $destino['nombre'] : '';
    $llamada['funcionario_destino_sede'] = $destino ? $destino['sede'] : '';
    $llamada['funcionario_destino_cod_usuario'] = $destino ? $destino['cod_usuario'] : null;
    $llamada['funcionario_destino_cod_local'] = $destino ? $destino['cod_local'] : null;
    return $llamada;
}

function centralTelefonicaDirectorioCompletarSnapshots($mysqli)
{
    if (!centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
        return false;
    }
    $nombre = "COALESCE(NULLIF(p.nombre_persona,''),NULLIF(d.nombre,''),'')";
    $sede = "COALESCE(NULLIF(l.Nombre,''),NULLIF(d.sede_nombre,''),'')";
    $actualizaciones = array(
        "UPDATE central_telefonica_llamada c "
            ."INNER JOIN central_telefonica_directorio d ON d.extension=c.ruta_extension AND d.activo=1 "
            ."SET c.ruta_tipo=IF(d.tipo='cola','cola',IF(c.ruta_tipo='',d.tipo,c.ruta_tipo)),"
            ."c.ruta_nombre=IF(d.tipo='cola',d.nombre,IF(c.ruta_nombre='',d.nombre,c.ruta_nombre)) "
            ."WHERE c.ruta_extension<>''",
        "UPDATE central_telefonica_llamada c "
            ."INNER JOIN central_telefonica_directorio d ON d.extension=c.ruta_extension AND d.activo=1 "
            ."SET c.funcionario_extension=c.ruta_extension "
            ."WHERE c.tipo='entrante_externa' AND c.funcionario_extension='' "
            ."AND d.tipo IN ('funcionario','interna')",
        "UPDATE central_telefonica_llamada c "
            ."SET c.funcionario_extension='',c.funcionario_nombre='',c.funcionario_sede='',"
            ."c.funcionario_cod_usuario=NULL,c.funcionario_cod_local=NULL "
            ."WHERE c.tipo='entrante_externa' AND c.ruta_tipo='cola' "
            ."AND c.funcionario_extension=c.ruta_extension",
        "UPDATE central_telefonica_llamada c "
            ."INNER JOIN central_telefonica_directorio d ON d.extension=c.funcionario_extension AND d.activo=1 "
            ."LEFT JOIN usuario u ON u.cod_usuario=d.cod_usuarioFK "
            ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
            ."LEFT JOIN local l ON l.cod_local=d.cod_localFK "
            ."SET c.funcionario_nombre=IF(c.funcionario_nombre='',".$nombre.",c.funcionario_nombre),"
            ."c.funcionario_sede=IF(c.funcionario_sede='',".$sede.",c.funcionario_sede),"
            ."c.funcionario_cod_usuario=IFNULL(c.funcionario_cod_usuario,d.cod_usuarioFK),"
            ."c.funcionario_cod_local=IFNULL(c.funcionario_cod_local,d.cod_localFK) "
            ."WHERE c.funcionario_extension<>''",
        "UPDATE central_telefonica_llamada c "
            ."INNER JOIN central_telefonica_directorio d ON d.extension=c.funcionario_destino_extension AND d.activo=1 "
            ."LEFT JOIN usuario u ON u.cod_usuario=d.cod_usuarioFK "
            ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
            ."LEFT JOIN local l ON l.cod_local=d.cod_localFK "
            ."SET c.funcionario_destino_nombre=IF(c.funcionario_destino_nombre='',".$nombre.",c.funcionario_destino_nombre),"
            ."c.funcionario_destino_sede=IF(c.funcionario_destino_sede='',".$sede.",c.funcionario_destino_sede),"
            ."c.funcionario_destino_cod_usuario=IFNULL(c.funcionario_destino_cod_usuario,d.cod_usuarioFK),"
            ."c.funcionario_destino_cod_local=IFNULL(c.funcionario_destino_cod_local,d.cod_localFK) "
            ."WHERE c.funcionario_destino_extension<>''"
    );
    foreach ($actualizaciones as $sql) {
        if (!$mysqli->query($sql)) {
            return false;
        }
    }
    return true;
}

function centralTelefonicaDirectorioSincronizar($mysqli, $configBase, $soloLectura)
{
    if (!centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
        centralTelefonicaDirectorioLanzar(
            'directorio_migracion_pendiente',
            'La migracion del directorio telefonico todavia no esta aplicada.'
        );
    }
    $config = centralTelefonicaDirectorioConfig($configBase);
    $conexion = centralTelefonicaDirectorioConectarFuente($config);
    try {
        $filas = centralTelefonicaDirectorioLeerFuente($conexion, $config);
        $conexion->close();
        if (count($filas) === 0) {
            centralTelefonicaDirectorioLanzar(
                'directorio_fuente_vacia',
                'Issabel no devolvio extensiones o colas para el directorio.'
            );
        }
        if ($soloLectura) {
            return array('consultados' => count($filas), 'guardados' => 0, 'dry_run' => true);
        }
        $mysqli->begin_transaction();
        if (!$mysqli->query(
            "UPDATE central_telefonica_directorio SET activo=0 "
            ."WHERE fuente='issabel' AND activo=1"
        )) {
            centralTelefonicaDirectorioLanzar(
                'directorio_persistencia_no_disponible',
                'No se pudo preparar la actualizacion del directorio.'
            );
        }
        $sql = "INSERT INTO central_telefonica_directorio "
            ."(extension,tipo,nombre,descripcion,fuente,activo,fecha_ultima_fuente,fecha_creacion,fecha_actualizacion) "
            ."VALUES (?,?,?,?, 'issabel',1,NOW(),NOW(),NOW()) "
            ."ON DUPLICATE KEY UPDATE tipo=VALUES(tipo),nombre=VALUES(nombre),"
            ."descripcion=VALUES(descripcion),fuente='issabel',activo=1,"
            ."fecha_ultima_fuente=NOW(),fecha_actualizacion=NOW()";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            centralTelefonicaDirectorioLanzar(
                'directorio_persistencia_no_disponible',
                'No se pudo preparar el guardado del directorio.'
            );
        }
        foreach ($filas as $fila) {
            $stmt->bind_param(
                'ssss',
                $fila['extension'],
                $fila['tipo'],
                $fila['nombre'],
                $fila['descripcion']
            );
            if (!$stmt->execute()) {
                $stmt->close();
                centralTelefonicaDirectorioLanzar(
                    'directorio_persistencia_no_disponible',
                    'No se pudo guardar el directorio telefonico.'
                );
            }
        }
        $stmt->close();
        if (!centralTelefonicaDirectorioCompletarSnapshots($mysqli)) {
            centralTelefonicaDirectorioLanzar(
                'directorio_snapshots_no_disponibles',
                'No se pudieron completar las llamadas con el directorio.'
            );
        }
        if (!$mysqli->commit()) {
            centralTelefonicaDirectorioLanzar(
                'directorio_persistencia_no_disponible',
                'No se pudo confirmar la actualizacion del directorio.'
            );
        }
        return array(
            'consultados' => count($filas),
            'guardados' => count($filas),
            'dry_run' => false
        );
    } catch (Exception $e) {
        if ($conexion instanceof mysqli) {
            @$conexion->close();
        }
        if (!$soloLectura) {
            @$mysqli->rollback();
        }
        throw $e;
    }
}

function centralTelefonicaDirectorioAdministracionDisponible($mysqli)
{
    return centralTelefonicaDirectorioManualDisponible($mysqli)
        && centralTelefonicaTablaExiste($mysqli, 'central_telefonica_directorio_evento');
}

function centralTelefonicaDirectorioTextoAdministracion($valor, $maximo)
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

function centralTelefonicaDirectorioFilaAdministracion($fila)
{
    $codUsuario = $fila['cod_usuarioFK'] === null ? 0 : intval($fila['cod_usuarioFK']);
    $codLocal = $fila['cod_localFK'] === null ? 0 : intval($fila['cod_localFK']);
    $sedeAlternativa = trim((string)$fila['sede_nombre']);
    $renombrada = trim((string)$fila['cargo_visible']) !== ''
        || ((string)$fila['tipo'] === 'cola' && trim((string)$fila['nombre']) !== '');
    return array(
        'extension' => (string)$fila['extension'],
        'tipo' => (string)$fila['tipo'],
        'nombre_issabel' => (string)$fila['nombre'],
        'descripcion' => (string)$fila['descripcion'],
        'cargo' => (string)$fila['cargo_visible'],
        'cod_usuario' => $codUsuario,
        'funcionario_nombre' => (string)$fila['funcionario_nombre'],
        'cod_local' => $codLocal,
        'sede_nombre' => (string)$fila['local_nombre'],
        'sede_nombre_alternativa' => $sedeAlternativa,
        'asignada' => $codUsuario > 0,
        'renombrada' => $renombrada,
        'persistida' => true,
        'fuente' => (string)$fila['fuente'],
        'fecha_ultima_fuente' => $fila['fecha_ultima_fuente']
    );
}

function centralTelefonicaDirectorioDetectadasDesdeLlamadas($mysqli)
{
    $detectadas = array();
    $sql = "SELECT DISTINCT extension FROM ("
        ."SELECT funcionario_extension extension FROM central_telefonica_llamada "
        ."UNION ALL SELECT funcionario_destino_extension FROM central_telefonica_llamada "
        ."UNION ALL SELECT IF(tipo IN ('saliente_externa','interna'),extension,'') "
        ."FROM central_telefonica_llamada "
        ."UNION ALL SELECT IF(tipo='interna',destino_original,'') "
        ."FROM central_telefonica_llamada"
        .") x WHERE extension REGEXP '^[1-9][0-9]{2,4}$' "
        ."ORDER BY CAST(extension AS UNSIGNED),extension";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        return $detectadas;
    }
    while ($fila = $resultado->fetch_assoc()) {
        $extension = trim((string)$fila['extension']);
        if ($extension !== '') {
            $detectadas[$extension] = true;
        }
    }
    $resultado->free();
    return array_keys($detectadas);
}

function centralTelefonicaDirectorioAdministracionListar($mysqli)
{
    if (!centralTelefonicaDirectorioAdministracionDisponible($mysqli)) {
        centralTelefonicaDirectorioLanzar(
            'administracion_directorio_pendiente',
            'La administracion del directorio telefonico todavia no esta instalada.'
        );
    }
    $extensiones = array();
    $sql = "SELECT d.extension,d.tipo,d.nombre,d.descripcion,d.cargo_visible,"
        ."d.cod_usuarioFK,d.cod_localFK,d.fuente,"
        ."d.sede_nombre,d.fecha_ultima_fuente,IFNULL(p.nombre_persona,'') funcionario_nombre,"
        ."IFNULL(l.Nombre,'') local_nombre FROM central_telefonica_directorio d "
        ."LEFT JOIN usuario u ON u.cod_usuario=d.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=d.cod_localFK "
        ."WHERE d.activo=1 ORDER BY IF(d.tipo='cola',1,0),CAST(d.extension AS UNSIGNED),d.extension";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        centralTelefonicaDirectorioLanzar(
            'directorio_no_disponible',
            'No se pudieron consultar las extensiones vigentes.'
        );
    }
    while ($fila = $resultado->fetch_assoc()) {
        $extensiones[] = centralTelefonicaDirectorioFilaAdministracion($fila);
    }
    $resultado->free();

    $porExtension = array();
    foreach ($extensiones as $extension) {
        $porExtension[$extension['extension']] = $extension;
    }
    foreach (centralTelefonicaDirectorioDetectadasDesdeLlamadas($mysqli) as $extensionDetectada) {
        if (isset($porExtension[$extensionDetectada])) {
            continue;
        }
        $porExtension[$extensionDetectada] = array(
            'extension' => $extensionDetectada,
            'tipo' => 'interna',
            'nombre_issabel' => '',
            'descripcion' => '',
            'cargo' => '',
            'cod_usuario' => 0,
            'funcionario_nombre' => '',
            'cod_local' => 0,
            'sede_nombre' => '',
            'sede_nombre_alternativa' => '',
            'asignada' => false,
            'renombrada' => false,
            'persistida' => false,
            'fuente' => 'cdr',
            'fecha_ultima_fuente' => null
        );
    }
    $extensiones = array_values($porExtension);
    usort($extensiones, function ($a, $b) {
        return strnatcasecmp((string)$a['extension'], (string)$b['extension']);
    });

    $funcionarios = array();
    $resultado = $mysqli->query(
        "SELECT u.cod_usuario,u.cod_localFK,u.tipo,p.nombre_persona,IFNULL(l.Nombre,'') sede_principal,"
        ."IFNULL((SELECT MIN(d2.extension) FROM central_telefonica_directorio d2 "
        ."WHERE d2.cod_usuarioFK=u.cod_usuario AND d2.activo=1),'') extension_asignada "
        ."FROM usuario u INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."WHERE UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO' "
        ."AND TRIM(IFNULL(p.nombre_persona,''))<>'' ORDER BY p.nombre_persona,u.cod_usuario"
    );
    if (!$resultado) {
        centralTelefonicaDirectorioLanzar(
            'funcionarios_no_disponibles',
            'No se pudo preparar el listado de funcionarios.'
        );
    }
    while ($fila = $resultado->fetch_assoc()) {
        $funcionarios[] = array(
            'cod_usuario' => intval($fila['cod_usuario']),
            'nombre' => (string)$fila['nombre_persona'],
            'tipo' => (string)$fila['tipo'],
            'cod_local_principal' => $fila['cod_localFK'] === null ? 0 : intval($fila['cod_localFK']),
            'sede_principal' => (string)$fila['sede_principal'],
            'extension_asignada' => (string)$fila['extension_asignada']
        );
    }
    $resultado->free();

    $sedes = array();
    $resultado = $mysqli->query(
        "SELECT cod_local,Nombre FROM local "
        ."WHERE UPPER(TRIM(IFNULL(estado,'')))='ACTIVO' ORDER BY Nombre,cod_local"
    );
    if (!$resultado) {
        centralTelefonicaDirectorioLanzar(
            'sedes_no_disponibles',
            'No se pudo preparar el listado de sedes.'
        );
    }
    while ($fila = $resultado->fetch_assoc()) {
        $sedes[] = array(
            'cod_local' => intval($fila['cod_local']),
            'nombre' => (string)$fila['Nombre']
        );
    }
    $resultado->free();

    $asignadas = 0;
    foreach ($extensiones as $extension) {
        if ($extension['asignada']) {
            $asignadas++;
        }
    }
    return array(
        'extensiones' => $extensiones,
        'funcionarios' => $funcionarios,
        'sedes' => $sedes,
        'resumen' => array(
            'total' => count($extensiones),
            'asignadas' => $asignadas,
            'sin_asignar' => count($extensiones) - $asignadas,
            'sin_renombrar' => count(array_filter($extensiones, function ($extension) {
                return empty($extension['renombrada']);
            }))
        )
    );
}

function centralTelefonicaDirectorioAdministracionGuardar(
    $mysqli,
    $extension,
    $cargo,
    $codUsuario,
    $codLocal,
    $sedeNombre,
    $quitar,
    $crear,
    $actor,
    $ipAccion
) {
    if (!centralTelefonicaDirectorioAdministracionDisponible($mysqli)) {
        centralTelefonicaDirectorioLanzar(
            'administracion_directorio_pendiente',
            'La administracion del directorio telefonico todavia no esta instalada.'
        );
    }
    $extension = trim((string)$extension);
    if (!preg_match('/^[1-9][0-9]{2,4}$/', $extension)) {
        centralTelefonicaDirectorioLanzar(
            'extension_invalida',
            'Ingrese una extension interna valida de 3 a 5 digitos.'
        );
    }
    $cargoOriginal = trim((string)$cargo);
    if (mb_strlen($cargoOriginal, 'UTF-8') > 100) {
        centralTelefonicaDirectorioLanzar(
            'cargo_invalido',
            'El cargo visible admite hasta 100 caracteres.'
        );
    }
    $cargo = centralTelefonicaDirectorioTextoAdministracion($cargoOriginal, 100);
    $codUsuario = max(0, intval($codUsuario));
    $codLocal = max(0, intval($codLocal));
    $actor = intval($actor);
    $sedeOriginal = trim((string)$sedeNombre);
    if (mb_strlen($sedeOriginal, 'UTF-8') > 100) {
        centralTelefonicaDirectorioLanzar(
            'sede_invalida',
            'El nombre alternativo de la sede admite hasta 100 caracteres.'
        );
    }
    $sedeNombre = centralTelefonicaDirectorioTextoAdministracion($sedeOriginal, 100);
    $ipAccion = centralTelefonicaDirectorioTextoAdministracion($ipAccion, 45);
    $quitar = $quitar ? true : false;
    $crear = $crear ? true : false;
    if ($quitar) {
        $codUsuario = 0;
    }

    $resultadoBloqueo = $mysqli->query(
        "SELECT GET_LOCK('telar_central_telefonica_directorio',3) adquirido"
    );
    $filaBloqueo = $resultadoBloqueo ? $resultadoBloqueo->fetch_assoc() : null;
    $bloqueo = $filaBloqueo && intval($filaBloqueo['adquirido']) === 1;
    if (!$bloqueo) {
        centralTelefonicaDirectorioLanzar(
            'directorio_en_actualizacion',
            'El directorio se esta actualizando. Intente nuevamente en unos segundos.'
        );
    }

    $transaccion = false;
    try {
        if (!$mysqli->begin_transaction()) {
            centralTelefonicaDirectorioLanzar(
                'asignacion_no_guardada',
                'No se pudo iniciar la actualizacion de la asignacion.'
            );
        }
        $transaccion = true;
        $stmt = $mysqli->prepare(
            "SELECT extension,tipo,nombre,cargo_visible,cod_usuarioFK,cod_localFK,sede_nombre,activo "
            ."FROM central_telefonica_directorio WHERE extension=? LIMIT 1 FOR UPDATE"
        );
        if (!$stmt) {
            centralTelefonicaDirectorioLanzar(
                'directorio_no_disponible',
                'No se pudo preparar la extension seleccionada.'
            );
        }
        $stmt->bind_param('s', $extension);
        $fila = null;
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            $fila = $resultado ? $resultado->fetch_assoc() : null;
        }
        $stmt->close();
        $nueva = !$fila;
        if ($nueva && !$crear) {
            centralTelefonicaDirectorioLanzar(
                'extension_no_disponible',
                'La extension no existe en el directorio. Vuelva a cargar el panel.'
            );
        }
        if (!$nueva && intval($fila['activo']) !== 1 && !$crear) {
            centralTelefonicaDirectorioLanzar(
                'extension_no_disponible',
                'La extension ya no se encuentra vigente.'
            );
        }
        if ($nueva) {
            $fila = array(
                'extension' => $extension,
                'tipo' => 'interna',
                'nombre' => '',
                'cargo_visible' => '',
                'cod_usuarioFK' => null,
                'cod_localFK' => null,
                'sede_nombre' => '',
                'activo' => 0
            );
        }

        $tipo = trim((string)$fila['tipo']);
        if ($tipo === '') {
            $tipo = 'interna';
        }
        if ($quitar) {
            $cargo = trim((string)$fila['cargo_visible']);
            $codLocal = $fila['cod_localFK'] === null ? 0 : intval($fila['cod_localFK']);
            $sedeNombre = (string)$fila['sede_nombre'];
        }
        $funcionarioNombre = '';
        $localNombre = '';
        if (!$quitar && $tipo === 'cola' && $codUsuario > 0) {
            centralTelefonicaDirectorioLanzar(
                'cola_sin_funcionario',
                'Una cola no puede asignarse directamente a un funcionario.'
            );
        }
        if ($tipo === 'cola') {
            $codUsuario = 0;
        } elseif (!$quitar && $codUsuario <= 0) {
            centralTelefonicaDirectorioLanzar(
                'funcionario_requerido',
                'Seleccione el funcionario que utiliza esta extension.'
            );
        }
        if (!$quitar && $tipo !== 'cola' && $cargo === '') {
            centralTelefonicaDirectorioLanzar(
                'cargo_requerido',
                'Ingrese el cargo que se mostrara para esta extension.'
            );
        }

        if ($codUsuario > 0) {
            $stmt = $mysqli->prepare(
                "SELECT u.cod_usuario,p.nombre_persona FROM usuario u "
                ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
                ."WHERE u.cod_usuario=? AND UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO' LIMIT 1"
            );
            if (!$stmt) {
                centralTelefonicaDirectorioLanzar(
                    'funcionario_no_disponible',
                    'No se pudo validar el funcionario seleccionado.'
                );
            }
            $stmt->bind_param('i', $codUsuario);
            $funcionario = null;
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $funcionario = $resultado ? $resultado->fetch_assoc() : null;
            }
            $stmt->close();
            if (!$funcionario) {
                centralTelefonicaDirectorioLanzar(
                    'funcionario_no_disponible',
                    'El funcionario seleccionado no esta activo.'
                );
            }
            $funcionarioNombre = (string)$funcionario['nombre_persona'];

            $stmt = $mysqli->prepare(
                "SELECT extension FROM central_telefonica_directorio "
                ."WHERE cod_usuarioFK=? AND activo=1 AND extension<>? LIMIT 1 FOR UPDATE"
            );
            if (!$stmt) {
                centralTelefonicaDirectorioLanzar(
                    'funcionario_no_disponible',
                    'No se pudo validar la asignacion exclusiva del funcionario.'
                );
            }
            $stmt->bind_param('is', $codUsuario, $extension);
            $extensionAsignada = null;
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $extensionAsignada = $resultado ? $resultado->fetch_assoc() : null;
            }
            $stmt->close();
            if ($extensionAsignada) {
                centralTelefonicaDirectorioLanzar(
                    'funcionario_ya_asignado',
                    'El funcionario ya utiliza la extension '
                        .(string)$extensionAsignada['extension'].'. Quite primero esa asignacion.'
                );
            }
        }
        if ($codLocal > 0 && !$quitar) {
            $stmt = $mysqli->prepare(
                "SELECT cod_local,Nombre FROM local WHERE cod_local=? "
                ."AND UPPER(TRIM(IFNULL(estado,'')))='ACTIVO' LIMIT 1"
            );
            if (!$stmt) {
                centralTelefonicaDirectorioLanzar(
                    'sede_no_disponible',
                    'No se pudo validar la sede seleccionada.'
                );
            }
            $stmt->bind_param('i', $codLocal);
            $local = null;
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $local = $resultado ? $resultado->fetch_assoc() : null;
            }
            $stmt->close();
            if (!$local) {
                centralTelefonicaDirectorioLanzar(
                    'sede_no_disponible',
                    'La sede seleccionada no esta activa.'
                );
            }
            $localNombre = (string)$local['Nombre'];
            $sedeNombre = '';
        }

        $anterior = $nueva ? null : array(
            'extension' => (string)$fila['extension'],
            'tipo' => $tipo,
            'cargo' => (string)$fila['cargo_visible'],
            'cod_usuario' => $fila['cod_usuarioFK'] === null ? 0 : intval($fila['cod_usuarioFK']),
            'cod_local' => $fila['cod_localFK'] === null ? 0 : intval($fila['cod_localFK']),
            'sede_nombre' => (string)$fila['sede_nombre']
        );
        $nuevo = array(
            'extension' => $extension,
            'tipo' => $tipo,
            'cargo' => $cargo,
            'cod_usuario' => $codUsuario,
            'funcionario_nombre' => $funcionarioNombre,
            'cod_local' => $codLocal,
            'sede_nombre' => $codLocal > 0 ? $localNombre : $sedeNombre
        );
        if ($nueva) {
            $stmt = $mysqli->prepare(
                "INSERT INTO central_telefonica_directorio "
                ."(extension,tipo,nombre,descripcion,cargo_visible,cod_usuarioFK,cod_localFK,"
                ."sede_nombre,fuente,activo,fecha_ultima_fuente,fecha_creacion,fecha_actualizacion) "
                ."VALUES (?,'interna','','',?,NULLIF(?,0),NULLIF(?,0),?,'telar',1,NULL,NOW(),NOW())"
            );
        } else {
            $stmt = $mysqli->prepare(
                "UPDATE central_telefonica_directorio SET cargo_visible=?,"
                ."cod_usuarioFK=NULLIF(?,0),cod_localFK=NULLIF(?,0),sede_nombre=?,"
                ."activo=1,fecha_actualizacion=NOW() WHERE extension=? LIMIT 1"
            );
        }
        if (!$stmt) {
            centralTelefonicaDirectorioLanzar(
                'asignacion_no_guardada',
                'No se pudo preparar la asignacion.'
            );
        }
        if ($nueva) {
            $stmt->bind_param('ssiis', $extension, $cargo, $codUsuario, $codLocal, $sedeNombre);
        } else {
            $stmt->bind_param('siiss', $cargo, $codUsuario, $codLocal, $sedeNombre, $extension);
        }
        $ok = $stmt->execute();
        $stmt->close();
        if (!$ok) {
            centralTelefonicaDirectorioLanzar(
                'asignacion_no_guardada',
                'No se pudo guardar la asignacion.'
            );
        }

        $accion = $nueva ? 'crear_extension'
            : ($quitar ? 'quitar_asignacion' : 'guardar_asignacion');
        $datosAnteriores = json_encode($anterior, JSON_UNESCAPED_UNICODE);
        $datosNuevos = json_encode($nuevo, JSON_UNESCAPED_UNICODE);
        $stmt = $mysqli->prepare(
            "INSERT INTO central_telefonica_directorio_evento "
            ."(extension,accion,datos_anteriores,datos_nuevos,cod_usuarioFK_accion,ip_accion,fecha_evento) "
            ."VALUES (?,?,?,?,?,?,NOW())"
        );
        if (!$stmt) {
            centralTelefonicaDirectorioLanzar(
                'auditoria_no_guardada',
                'No se pudo preparar la auditoria de la asignacion.'
            );
        }
        $stmt->bind_param(
            'ssssis',
            $extension,
            $accion,
            $datosAnteriores,
            $datosNuevos,
            $actor,
            $ipAccion
        );
        $ok = $stmt->execute();
        $stmt->close();
        if (!$ok) {
            centralTelefonicaDirectorioLanzar(
                'auditoria_no_guardada',
                'No se pudo guardar la auditoria de la asignacion.'
            );
        }
        if (!$quitar && !centralTelefonicaDirectorioCompletarSnapshots($mysqli)) {
            centralTelefonicaDirectorioLanzar(
                'directorio_snapshots_no_disponibles',
                'La asignacion no pudo aplicarse a las llamadas aun no identificadas.'
            );
        }
        if (!$mysqli->commit()) {
            centralTelefonicaDirectorioLanzar(
                'asignacion_no_guardada',
                'No se pudo confirmar la asignacion.'
            );
        }
        $transaccion = false;
        $mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
        return $nuevo;
    } catch (Exception $e) {
        if ($transaccion) {
            @$mysqli->rollback();
        }
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
        throw $e;
    } catch (Throwable $e) {
        if ($transaccion) {
            @$mysqli->rollback();
        }
        @$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_directorio')");
        throw $e;
    }
}

?>

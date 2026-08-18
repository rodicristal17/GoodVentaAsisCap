<?php

/**
 * Endpoint de consulta de Central Telefonica.
 *
 * Lee exclusivamente las tablas normalizadas de Telar. La conexion a Issabel
 * queda reservada al sincronizador CLI y nunca se expone al navegador.
 */

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Asuncion');

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/central_telefonica_helper.php';
require_once __DIR__.'/central_telefonica_directorio_helper.php';
require_once __DIR__.'/central_telefonica_transcripcion_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, private');

class CentralTelefonicaExcepcion extends Exception
{
    public $codigoOperacion;
    public $datosOperacion;

    public function __construct($codigo, $mensaje, $datos = array())
    {
        parent::__construct($mensaje);
        $this->codigoOperacion = $codigo;
        $this->datosOperacion = $datos;
    }
}

function centralTelefonicaLanzar($codigo, $mensaje, $datos = array())
{
    throw new CentralTelefonicaExcepcion($codigo, $mensaje, $datos);
}

function centralTelefonicaResponder($ok, $codigo, $mensaje, $datos = array(), $estadoHttp = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(intval($estadoHttp));
    }
    echo json_encode(
        centralTelefonicaUtf8(array(
            'ok' => $ok ? true : false,
            'codigo' => (string)$codigo,
            'mensaje' => (string)$mensaje,
            'datos' => $datos
        )),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function centralTelefonicaParametro($nombre, $predeterminado = '')
{
    if (isset($_POST[$nombre])) {
        return $_POST[$nombre];
    }
    if (isset($_GET[$nombre])) {
        return $_GET[$nombre];
    }
    return $predeterminado;
}

function centralTelefonicaTextoEntrada($valor, $maximo = 150)
{
    if (is_array($valor) || is_object($valor)) {
        return '';
    }
    $texto = trim((string)$valor);
    if (mb_strlen($texto, 'UTF-8') > $maximo) {
        $texto = mb_substr($texto, 0, $maximo, 'UTF-8');
    }
    return $texto;
}

function centralTelefonicaFechaValida($valor)
{
    $fecha = DateTime::createFromFormat('!Y-m-d', (string)$valor);
    return $fecha && $fecha->format('Y-m-d') === (string)$valor;
}

function centralTelefonicaRangoEntrada($entrada)
{
    $hoy = new DateTime('today');
    $desde = isset($entrada['desde']) ? trim((string)$entrada['desde']) : $hoy->format('Y-m-d');
    $hasta = isset($entrada['hasta']) ? trim((string)$entrada['hasta']) : $hoy->format('Y-m-d');

    if (!centralTelefonicaFechaValida($desde) || !centralTelefonicaFechaValida($hasta)) {
        centralTelefonicaLanzar('rango_invalido', 'Seleccione un rango de fechas valido.');
    }

    $inicio = DateTime::createFromFormat('!Y-m-d', $desde);
    $fin = DateTime::createFromFormat('!Y-m-d', $hasta);
    if ($fin < $inicio) {
        centralTelefonicaLanzar('rango_invalido', 'La fecha final no puede ser anterior a la inicial.');
    }
    $dias = intval($inicio->diff($fin)->format('%a')) + 1;
    if ($dias > 31) {
        centralTelefonicaLanzar('rango_demasiado_amplio', 'La consulta admite hasta 31 dias por vez.');
    }

    $finExclusivo = clone $fin;
    $finExclusivo->modify('+1 day');
    return array(
        'desde' => $desde,
        'hasta' => $hasta,
        'inicio' => $inicio->format('Y-m-d 00:00:00'),
        'fin_exclusivo' => $finExclusivo->format('Y-m-d 00:00:00'),
        'dias' => $dias
    );
}

function centralTelefonicaPrepararWhere($entrada, $rango, $directorioDisponible = false)
{
    $where = array('l.fecha_inicio>=?', 'l.fecha_inicio<?');
    $tipos = 'ss';
    $parametros = array($rango['inicio'], $rango['fin_exclusivo']);
    $tipo = centralTelefonicaTextoEntrada(isset($entrada['tipo']) ? $entrada['tipo'] : '', 40);
    $estado = centralTelefonicaTextoEntrada(isset($entrada['estado']) ? $entrada['estado'] : '', 40);
    $extension = preg_replace('/[^0-9]/', '', centralTelefonicaTextoEntrada(
        isset($entrada['extension']) ? $entrada['extension'] : '',
        20
    ));
    $telefono = preg_replace('/[^0-9]/', '', centralTelefonicaTextoEntrada(
        isset($entrada['telefono']) ? $entrada['telefono'] : '',
        40
    ));
    $funcionario = preg_replace('/[^0-9]/', '', centralTelefonicaTextoEntrada(
        isset($entrada['funcionario']) ? $entrada['funcionario'] : '',
        20
    ));
    $cola = preg_replace('/[^0-9]/', '', centralTelefonicaTextoEntrada(
        isset($entrada['cola']) ? $entrada['cola'] : '',
        20
    ));
    $sedeTexto = preg_replace('/[^0-9]/', '', centralTelefonicaTextoEntrada(
        isset($entrada['sede']) ? $entrada['sede'] : '',
        12
    ));
    $sede = $sedeTexto === '' ? 0 : intval($sedeTexto);
    $tiposPermitidos = array(
        'entrante_externa', 'saliente_externa', 'interna',
        'servicio_prueba', 'sin_clasificar'
    );
    $estadosPermitidos = array(
        'contestada', 'no_contestada', 'ocupada', 'fallida',
        'congestion', 'sin_estado'
    );

    if ($tipo !== '') {
        if (!in_array($tipo, $tiposPermitidos, true)) {
            centralTelefonicaLanzar('filtro_invalido', 'El tipo de llamada seleccionado no es valido.');
        }
        $where[] = 'l.tipo=?';
        $tipos .= 's';
        $parametros[] = $tipo;
    }
    if ($estado !== '') {
        if (!in_array($estado, $estadosPermitidos, true)) {
            centralTelefonicaLanzar('filtro_invalido', 'El estado seleccionado no es valido.');
        }
        $where[] = 'l.estado=?';
        $tipos .= 's';
        $parametros[] = $estado;
    }
    if ($extension !== '') {
        $where[] = 'l.extension=?';
        $tipos .= 's';
        $parametros[] = $extension;
    }
    if (($cola !== '' || $sede > 0) && !$directorioDisponible) {
        centralTelefonicaLanzar(
            'directorio_no_disponible',
            'Los filtros de sede y cola requieren la migracion del directorio.'
        );
    }
    if ($funcionario !== '') {
        if ($directorioDisponible) {
            $where[] = '(l.funcionario_extension=? OR l.funcionario_destino_extension=?)';
            $tipos .= 'ss';
            $parametros[] = $funcionario;
            $parametros[] = $funcionario;
        } else {
            $where[] = 'l.extension=?';
            $tipos .= 's';
            $parametros[] = $funcionario;
        }
    }
    if ($sede > 0) {
        $where[] = '(l.funcionario_cod_local=? OR l.funcionario_destino_cod_local=?)';
        $tipos .= 'ii';
        $parametros[] = $sede;
        $parametros[] = $sede;
    }
    if ($cola !== '') {
        $where[] = "l.ruta_extension=? AND l.ruta_tipo='cola'";
        $tipos .= 's';
        $parametros[] = $cola;
    }
    if ($telefono !== '') {
        $where[] = "(REPLACE(l.origen_normalizado,'+','') LIKE ?
            OR REPLACE(l.destino_normalizado,'+','') LIKE ?
            OR l.origen_original LIKE ? OR l.destino_original LIKE ?)";
        $buscar = '%'.$telefono.'%';
        $tipos .= 'ssss';
        $parametros[] = $buscar;
        $parametros[] = $buscar;
        $parametros[] = $buscar;
        $parametros[] = $buscar;
    }

    return array(
        'sql' => implode(' AND ', $where),
        'tipos' => $tipos,
        'parametros' => $parametros
    );
}

function centralTelefonicaBindDinamico($stmt, $tipos, $parametros)
{
    if ($tipos === '') {
        return true;
    }
    $enlaces = array();
    $enlaces[] = &$tipos;
    foreach ($parametros as $indice => $valor) {
        $parametros[$indice] = $valor;
        $enlaces[] = &$parametros[$indice];
    }
    return call_user_func_array(array($stmt, 'bind_param'), $enlaces);
}

function centralTelefonicaEjecutarConsulta($mysqli, $sql, $tipos, $parametros)
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        centralTelefonicaLanzar('consulta_no_disponible', 'No se pudo preparar la consulta telefonica.');
    }
    if (!centralTelefonicaBindDinamico($stmt, $tipos, $parametros) || !$stmt->execute()) {
        $stmt->close();
        centralTelefonicaLanzar('consulta_no_disponible', 'No se pudo consultar la informacion telefonica.');
    }
    return $stmt;
}

function centralTelefonicaContexto($mysqli, $codUsuario)
{
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,u.estado,u.tipo,u.login,p.nombre_persona "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."WHERE u.cod_usuario=? LIMIT 1"
    );
    if (!$stmt) {
        centralTelefonicaLanzar('usuario_no_disponible', 'No se pudo validar el usuario.');
    }
    $stmt->bind_param('i', $codUsuario);
    $usuario = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $usuario = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();

    if (!$usuario || strtoupper(trim((string)$usuario['estado'])) !== 'ACTIVO') {
        centralTelefonicaLanzar('usuario_no_disponible', 'El usuario autenticado no esta activo.');
    }

    $cuentaTranscripcionProtegida = intval($usuario['cod_usuario']) === 5994
        && strtolower(trim((string)$usuario['login'])) === 'cf'
        && strtoupper(trim((string)$usuario['tipo'])) === 'ADMINISTRATIVO'
        && strpos(
            strtoupper(trim((string)$usuario['nombre_persona'])),
            'CARLOS FARAONE CLINIDENT'
        ) === 0;

    $permisos = array(
        'ver' => centralTelefonicaTienePermiso($mysqli, $codUsuario, 'VERCENTRALTELEFONICA'),
        'telefonos_completos' => centralTelefonicaTienePermiso(
            $mysqli,
            $codUsuario,
            'VERTELEFONOSCOMPLETOSCENTRALTELEFONICA'
        ),
        'datos_tecnicos' => centralTelefonicaTienePermiso(
            $mysqli,
            $codUsuario,
            'VERDATOSTECNICOSCENTRALTELEFONICA'
        ),
        'escuchar_grabaciones' => centralTelefonicaTienePermiso(
            $mysqli,
            $codUsuario,
            'ESCUCHARGRABACIONCENTRALTELEFONICA'
        ),
        'transcribir_llamadas' => $cuentaTranscripcionProtegida
            && centralTelefonicaTienePermiso(
                $mysqli,
                $codUsuario,
                'TRANSCRIBIRLLAMADACENTRALTELEFONICA'
            ),
        'administrar_directorio' => $cuentaTranscripcionProtegida
            && centralTelefonicaTienePermiso(
                $mysqli,
                $codUsuario,
                'ADMINISTRARDIRECTORIOCENTRALTELEFONICA'
            )
    );
    if (!$permisos['ver']) {
        centralTelefonicaLanzar('acceso_no_autorizado', 'No tiene permiso para ver Central Telefonica.');
    }

    return array(
        'cod_usuario' => intval($usuario['cod_usuario']),
        'tipo_usuario' => $usuario['tipo'],
        'permisos' => $permisos
    );
}

function centralTelefonicaTelefonoVisible($valor, $contexto)
{
    if (!empty($contexto['permisos']['telefonos_completos'])) {
        return (string)$valor;
    }
    return centralTelefonicaMascararTelefono($valor);
}

function centralTelefonicaTranscripcionEstadoFila($fila, $contexto, $estructuraDisponible)
{
    if (empty($contexto['permisos']['transcribir_llamadas'])) {
        return null;
    }
    if (!$estructuraDisponible) {
        return array(
            'estado' => 'migracion_pendiente',
            'solicitada' => false,
            'actualizada' => null,
            'mensaje_error' => null
        );
    }
    $estado = isset($fila['transcripcion_estado']) && $fila['transcripcion_estado'] !== null
        ? (string)$fila['transcripcion_estado'] : '';
    return array(
        'estado' => $estado === '' ? 'sin_solicitar' : $estado,
        'solicitada' => $estado !== '',
        'actualizada' => isset($fila['transcripcion_actualizada'])
            ? $fila['transcripcion_actualizada'] : null,
        'mensaje_error' => $estado === 'error' && isset($fila['transcripcion_mensaje_error'])
            ? $fila['transcripcion_mensaje_error'] : null
    );
}

function centralTelefonicaDirectorioExigirAdministracion($mysqli, $contexto)
{
    if (empty($contexto['permisos']['administrar_directorio'])) {
        centralTelefonicaLanzar(
            'directorio_no_autorizado',
            'No tiene permiso para administrar el directorio telefonico.'
        );
    }
    if (!centralTelefonicaDirectorioAdministracionDisponible($mysqli)) {
        centralTelefonicaLanzar(
            'administracion_directorio_pendiente',
            'La administracion del directorio telefonico todavia no esta instalada.'
        );
    }
}

function centralTelefonicaDirectorioAdministrarListar($mysqli, $contexto)
{
    centralTelefonicaDirectorioExigirAdministracion($mysqli, $contexto);
    return centralTelefonicaDirectorioAdministracionListar($mysqli);
}

function centralTelefonicaDirectorioAdministrarGuardar($mysqli, $contexto, $entrada)
{
    centralTelefonicaDirectorioExigirAdministracion($mysqli, $contexto);
    $quitarTexto = strtolower(centralTelefonicaTextoEntrada(
        isset($entrada['quitar']) ? $entrada['quitar'] : '',
        10
    ));
    $quitar = in_array($quitarTexto, array('1', 'true', 'si', 'yes'), true);
    $ipAccion = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
    $asignacion = centralTelefonicaDirectorioAdministracionGuardar(
        $mysqli,
        centralTelefonicaTextoEntrada(isset($entrada['extension']) ? $entrada['extension'] : '', 20),
        intval(isset($entrada['cod_usuario']) ? $entrada['cod_usuario'] : 0),
        intval(isset($entrada['cod_local']) ? $entrada['cod_local'] : 0),
        centralTelefonicaTextoEntrada(
            isset($entrada['sede_nombre']) ? $entrada['sede_nombre'] : '',
            101
        ),
        $quitar,
        intval($contexto['cod_usuario']),
        $ipAccion
    );
    return array('asignacion' => $asignacion);
}

function centralTelefonicaFilaVisible(
    $fila,
    $contexto,
    $estructuraTranscripcion = false
)
{
    $tipo = isset($fila['tipo']) ? $fila['tipo'] : '';
    $origen = centralTelefonicaTelefonoVisible($fila['origen_original'], $contexto);
    $destino = centralTelefonicaTelefonoVisible($fila['destino_original'], $contexto);
    $numero = $tipo === 'entrante_externa' ? $origen
        : ($tipo === 'saliente_externa' ? $destino : $origen.' → '.$destino);
    $rutaExtension = isset($fila['ruta_extension']) ? trim((string)$fila['ruta_extension']) : '';
    $rutaTipo = isset($fila['ruta_tipo']) ? trim((string)$fila['ruta_tipo']) : '';
    $rutaNombre = isset($fila['ruta_nombre']) ? trim((string)$fila['ruta_nombre']) : '';
    $funcionarioExtension = isset($fila['funcionario_extension'])
        ? trim((string)$fila['funcionario_extension']) : '';
    $funcionarioDestinoExtension = isset($fila['funcionario_destino_extension'])
        ? trim((string)$fila['funcionario_destino_extension']) : '';
    if ($tipo === 'entrante_externa' && $rutaExtension === '') {
        $rutaExtension = trim((string)$fila['extension']);
        $rutaTipo = $rutaTipo === '' ? 'interna' : $rutaTipo;
    }
    if ($tipo === 'saliente_externa') {
        $rutaTipo = 'salida';
        $rutaNombre = $rutaNombre === '' ? 'Salida directa' : $rutaNombre;
        if ($funcionarioExtension === '') {
            $funcionarioExtension = trim((string)$fila['extension']);
        }
    }
    if ($tipo === 'interna') {
        $rutaTipo = 'interna';
        $rutaNombre = $rutaNombre === '' ? 'Llamada interna' : $rutaNombre;
        if ($funcionarioExtension === '') {
            $funcionarioExtension = preg_replace('/[^0-9]/', '', (string)$fila['origen_original']);
        }
        if ($funcionarioDestinoExtension === '') {
            $funcionarioDestinoExtension = preg_replace('/[^0-9]/', '', (string)$fila['destino_original']);
        }
    }
    if ($rutaTipo === 'cola' && $funcionarioExtension === $rutaExtension) {
        $funcionarioExtension = '';
    }

    return array(
        'id_llamada' => intval($fila['id_llamada']),
        'fecha_inicio' => $fila['fecha_inicio'],
        'fecha' => substr((string)$fila['fecha_inicio'], 0, 10),
        'hora' => substr((string)$fila['fecha_inicio'], 11, 8),
        'tipo' => $tipo,
        'estado' => $fila['estado'],
        'origen' => $origen,
        'destino' => $destino,
        'numero_principal' => $numero,
        'extension' => $fila['extension'],
        'ruta' => array(
            'extension' => $rutaExtension,
            'tipo' => $rutaTipo,
            'nombre' => $rutaNombre
        ),
        'funcionario' => array(
            'extension' => $funcionarioExtension,
            'nombre' => isset($fila['funcionario_nombre']) ? $fila['funcionario_nombre'] : '',
            'sede' => isset($fila['funcionario_sede']) ? $fila['funcionario_sede'] : ''
        ),
        'funcionario_destino' => array(
            'extension' => $funcionarioDestinoExtension,
            'nombre' => isset($fila['funcionario_destino_nombre'])
                ? $fila['funcionario_destino_nombre'] : '',
            'sede' => isset($fila['funcionario_destino_sede'])
                ? $fila['funcionario_destino_sede'] : ''
        ),
        'duracion_seg' => intval($fila['duracion_seg']),
        'duracion_texto' => centralTelefonicaFormatearDuracion($fila['duracion_seg']),
        'hablado_seg' => intval($fila['hablado_seg']),
        'hablado_texto' => centralTelefonicaFormatearDuracion($fila['hablado_seg']),
        'cantidad_segmentos' => intval($fila['cantidad_segmentos']),
        'grabacion_disponible' => intval($fila['grabacion_disponible']) === 1,
        'transcripcion' => centralTelefonicaTranscripcionEstadoFila(
            $fila,
            $contexto,
            $estructuraTranscripcion
        ),
        'paciente' => null
    );
}

function centralTelefonicaTranscripcionServicioVisible($mysqli, $contexto, $estructuraDisponible)
{
    if (empty($contexto['permisos']['transcribir_llamadas'])) {
        return null;
    }
    if (!$estructuraDisponible) {
        return array(
            'disponible' => false,
            'estado' => 'migracion_pendiente',
            'mensaje' => 'La transcripcion todavia no esta instalada.'
        );
    }
    $resultado = $mysqli->query(
        "SELECT estado,proveedor,modelo,ultima_actividad,codigo_error "
        ."FROM central_telefonica_transcripcion_servicio WHERE id_servicio=1 LIMIT 1"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if (!$fila) {
        return array(
            'disponible' => false,
            'estado' => 'sin_configurar',
            'mensaje' => 'El worker de transcripcion todavia no esta configurado.'
        );
    }
    $reciente = false;
    if (!empty($fila['ultima_actividad'])) {
        $marca = strtotime((string)$fila['ultima_actividad']);
        $reciente = $marca !== false && $marca >= time() - 180;
    }
    $disponible = $fila['estado'] === 'disponible' && $reciente;
    $mensaje = $disponible
        ? 'OpenAI disponible para procesar una llamada por vez.'
        : ($fila['estado'] === 'error'
            ? 'El servicio de transcripcion necesita revision.'
            : 'El worker de transcripcion no esta activo o no esta configurado.');
    return array(
        'disponible' => $disponible,
        'estado' => $reciente ? $fila['estado'] : 'sin_actividad',
        'proveedor' => $fila['proveedor'],
        'modelo' => $fila['modelo'],
        'ultima_actividad' => $fila['ultima_actividad'],
        'codigo_error' => $fila['codigo_error'],
        'mensaje' => $mensaje
    );
}

function centralTelefonicaResumen($mysqli, $where)
{
    $sql = "SELECT
            COUNT(*) total,
            COALESCE(SUM(l.tipo='entrante_externa'),0) entrantes,
            COALESCE(SUM(l.tipo='saliente_externa'),0) salientes,
            COALESCE(SUM(l.estado='contestada'),0) contestadas,
            COALESCE(SUM(l.estado='no_contestada'),0) no_contestadas,
            COALESCE(SUM(l.hablado_seg),0) tiempo_hablado,
            COALESCE(SUM(l.tipo='interna'),0) internas,
            COALESCE(SUM(l.tipo='servicio_prueba'),0) servicios,
            COALESCE(SUM(l.tipo='sin_clasificar'),0) sin_clasificar
        FROM central_telefonica_llamada l
        WHERE ".$where['sql'];
    $stmt = centralTelefonicaEjecutarConsulta(
        $mysqli,
        $sql,
        $where['tipos'],
        $where['parametros']
    );
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : array();
    $stmt->close();
    return array(
        'total' => intval(isset($fila['total']) ? $fila['total'] : 0),
        'entrantes' => intval(isset($fila['entrantes']) ? $fila['entrantes'] : 0),
        'salientes' => intval(isset($fila['salientes']) ? $fila['salientes'] : 0),
        'contestadas' => intval(isset($fila['contestadas']) ? $fila['contestadas'] : 0),
        'no_contestadas' => intval(isset($fila['no_contestadas']) ? $fila['no_contestadas'] : 0),
        'tiempo_hablado_seg' => intval(isset($fila['tiempo_hablado']) ? $fila['tiempo_hablado'] : 0),
        'tiempo_hablado_texto' => centralTelefonicaFormatearDuracion(
            isset($fila['tiempo_hablado']) ? $fila['tiempo_hablado'] : 0
        ),
        'internas' => intval(isset($fila['internas']) ? $fila['internas'] : 0),
        'servicios' => intval(isset($fila['servicios']) ? $fila['servicios'] : 0),
        'sin_clasificar' => intval(isset($fila['sin_clasificar']) ? $fila['sin_clasificar'] : 0)
    );
}

function centralTelefonicaEstadoSincronizacion($mysqli)
{
    $config = centralTelefonicaCargarConfiguracionIssabel();
    $configurada = centralTelefonicaConfiguracionDisponible($config);
    $sql = "SELECT id_sincronizacion,fecha_inicio,fecha_fin,estado,
            registros_consultados,registros_nuevos,registros_actualizados,
            duracion_ms,codigo_error
        FROM central_telefonica_sincronizacion
        ORDER BY id_sincronizacion DESC LIMIT 1";
    $resultado = $mysqli->query($sql);
    $fila = $resultado ? $resultado->fetch_assoc() : null;

    if (!$fila) {
        return array(
            'configurada' => $configurada,
            'estado' => $configurada ? 'pendiente' : 'sin_configurar',
            'ultima_sincronizacion' => null,
            'registros_consultados' => 0,
            'registros_nuevos' => 0,
            'duracion_ms' => 0,
            'frecuencia_minutos' => 5
        );
    }

    return array(
        'configurada' => $configurada,
        'estado' => $fila['estado'],
        'ultima_sincronizacion' => $fila['estado'] === 'exitosa'
            ? $fila['fecha_fin'] : $fila['fecha_inicio'],
        'registros_consultados' => intval($fila['registros_consultados']),
        'registros_nuevos' => intval($fila['registros_nuevos']),
        'registros_actualizados' => intval($fila['registros_actualizados']),
        'duracion_ms' => intval($fila['duracion_ms']),
        'codigo_error' => $fila['codigo_error'],
        'frecuencia_minutos' => 5
    );
}

function centralTelefonicaExtensiones($mysqli, $rango)
{
    $sql = "SELECT DISTINCT extension
        FROM central_telefonica_llamada
        WHERE fecha_inicio>=? AND fecha_inicio<? AND extension<>''
        ORDER BY CAST(extension AS UNSIGNED),extension LIMIT 200";
    $stmt = centralTelefonicaEjecutarConsulta(
        $mysqli,
        $sql,
        'ss',
        array($rango['inicio'], $rango['fin_exclusivo'])
    );
    $resultado = $stmt->get_result();
    $items = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $items[] = $fila['extension'];
    }
    $stmt->close();
    return $items;
}

function centralTelefonicaCatalogosDirectorio($mysqli, $rango, $disponible)
{
    $funcionarios = array();
    $colas = array();
    $sedes = array();
    if (!$disponible) {
        foreach (centralTelefonicaExtensiones($mysqli, $rango) as $extension) {
            $funcionarios[$extension] = array(
                'extension' => $extension,
                'nombre' => '',
                'sede' => '',
                'cod_local' => 0,
                'tipo' => 'interna'
            );
        }
        return array(
            'funcionarios' => array_values($funcionarios),
            'sedes' => array(),
            'colas' => array()
        );
    }

    $sql = "SELECT extension,MAX(nombre) nombre,MAX(sede) sede,"
        ."MAX(cod_local) cod_local FROM ("
        ."SELECT funcionario_extension extension,funcionario_nombre nombre,"
        ."funcionario_sede sede,IFNULL(funcionario_cod_local,0) cod_local "
        ."FROM central_telefonica_llamada WHERE fecha_inicio>=? AND fecha_inicio<? "
        ."UNION ALL "
        ."SELECT funcionario_destino_extension,funcionario_destino_nombre,"
        ."funcionario_destino_sede,IFNULL(funcionario_destino_cod_local,0) "
        ."FROM central_telefonica_llamada WHERE fecha_inicio>=? AND fecha_inicio<?"
        .") x WHERE extension<>'' GROUP BY extension";
    $stmt = centralTelefonicaEjecutarConsulta(
        $mysqli,
        $sql,
        'ssss',
        array($rango['inicio'], $rango['fin_exclusivo'], $rango['inicio'], $rango['fin_exclusivo'])
    );
    $resultado = $stmt->get_result();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $funcionarios[$fila['extension']] = array(
            'extension' => $fila['extension'],
            'nombre' => $fila['nombre'],
            'sede' => $fila['sede'],
            'cod_local' => intval($fila['cod_local']),
            'tipo' => 'funcionario'
        );
    }
    $stmt->close();

    $sqlDirectorio = "SELECT d.extension,d.tipo,"
        ."COALESCE(NULLIF(p.nombre_persona,''),NULLIF(d.nombre,''),'') nombre,"
        ."COALESCE(NULLIF(l.Nombre,''),NULLIF(d.sede_nombre,''),'') sede,"
        ."IFNULL(d.cod_localFK,0) cod_local "
        ."FROM central_telefonica_directorio d "
        ."LEFT JOIN usuario u ON u.cod_usuario=d.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=d.cod_localFK WHERE d.activo=1";
    $resultadoDirectorio = $mysqli->query($sqlDirectorio);
    while ($resultadoDirectorio && ($fila = $resultadoDirectorio->fetch_assoc())) {
        $item = array(
            'extension' => $fila['extension'],
            'nombre' => $fila['nombre'],
            'sede' => $fila['sede'],
            'cod_local' => intval($fila['cod_local']),
            'tipo' => $fila['tipo']
        );
        if ($fila['tipo'] === 'cola') {
            $colas[$fila['extension']] = $item;
        } else {
            if (!isset($funcionarios[$fila['extension']])) {
                $funcionarios[$fila['extension']] = $item;
            } else {
                foreach (array('nombre', 'sede', 'cod_local', 'tipo') as $clave) {
                    if ($funcionarios[$fila['extension']][$clave] === ''
                        || $funcionarios[$fila['extension']][$clave] === 0) {
                        $funcionarios[$fila['extension']][$clave] = $item[$clave];
                    }
                }
            }
        }
    }

    $stmt = centralTelefonicaEjecutarConsulta(
        $mysqli,
        "SELECT ruta_extension extension,MAX(ruta_nombre) nombre "
        ."FROM central_telefonica_llamada WHERE fecha_inicio>=? AND fecha_inicio<? "
        ."AND ruta_tipo='cola' AND ruta_extension<>'' GROUP BY ruta_extension",
        'ss',
        array($rango['inicio'], $rango['fin_exclusivo'])
    );
    $resultado = $stmt->get_result();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        if (!isset($colas[$fila['extension']])) {
            $colas[$fila['extension']] = array(
                'extension' => $fila['extension'],
                'nombre' => $fila['nombre'],
                'sede' => '',
                'cod_local' => 0,
                'tipo' => 'cola'
            );
        }
    }
    $stmt->close();

    foreach ($funcionarios as $item) {
        if (intval($item['cod_local']) > 0 && trim((string)$item['sede']) !== '') {
            $sedes[intval($item['cod_local'])] = array(
                'cod_local' => intval($item['cod_local']),
                'nombre' => $item['sede']
            );
        }
    }
    $ordenar = function ($a, $b) {
        return strnatcasecmp(
            trim((string)$a['nombre']).' '.trim((string)$a['extension']),
            trim((string)$b['nombre']).' '.trim((string)$b['extension'])
        );
    };
    $funcionarios = array_values($funcionarios);
    $colas = array_values($colas);
    $sedes = array_values($sedes);
    usort($funcionarios, $ordenar);
    usort($colas, $ordenar);
    usort($sedes, function ($a, $b) {
        return strnatcasecmp((string)$a['nombre'], (string)$b['nombre']);
    });
    return array(
        'funcionarios' => $funcionarios,
        'sedes' => $sedes,
        'colas' => $colas
    );
}

function centralTelefonicaListar($mysqli, $contexto, $entrada)
{
    $rango = centralTelefonicaRangoEntrada($entrada);
    $directorioDisponible = centralTelefonicaDirectorioEstructuraDisponible($mysqli);
    $where = centralTelefonicaPrepararWhere($entrada, $rango, $directorioDisponible);
    $estructuraTranscripcion = centralTelefonicaTranscripcionEstructuraDisponible($mysqli);
    $pagina = max(1, intval(isset($entrada['pagina']) ? $entrada['pagina'] : 1));
    $limite = intval(isset($entrada['limite']) ? $entrada['limite'] : 50);
    $limite = max(10, min(100, $limite));
    $offset = ($pagina - 1) * $limite;

    $stmtTotal = centralTelefonicaEjecutarConsulta(
        $mysqli,
        'SELECT COUNT(*) total FROM central_telefonica_llamada l WHERE '.$where['sql'],
        $where['tipos'],
        $where['parametros']
    );
    $filaTotal = $stmtTotal->get_result()->fetch_assoc();
    $stmtTotal->close();
    $total = intval($filaTotal['total']);

    $camposTranscripcion = $estructuraTranscripcion
        ? ",t.estado AS transcripcion_estado,t.fecha_actualizacion AS transcripcion_actualizada,"
            ."t.mensaje_error AS transcripcion_mensaje_error"
        : ",NULL AS transcripcion_estado,NULL AS transcripcion_actualizada,"
            ."NULL AS transcripcion_mensaje_error";
    $unionTranscripcion = $estructuraTranscripcion
        ? ' LEFT JOIN central_telefonica_transcripcion t ON t.id_llamada=l.id_llamada '
        : ' ';
    $camposDirectorio = $directorioDisponible
        ? ",l.ruta_extension,l.ruta_tipo,l.ruta_nombre,"
            ."l.funcionario_extension,l.funcionario_nombre,l.funcionario_sede,"
            ."l.funcionario_destino_extension,l.funcionario_destino_nombre,"
            ."l.funcionario_destino_sede"
        : ",'' AS ruta_extension,'' AS ruta_tipo,'' AS ruta_nombre,"
            ."'' AS funcionario_extension,'' AS funcionario_nombre,"
            ."'' AS funcionario_sede,'' AS funcionario_destino_extension,"
            ."'' AS funcionario_destino_nombre,'' AS funcionario_destino_sede";
    $sql = "SELECT l.id_llamada,l.fecha_inicio,l.tipo,l.estado,
            l.origen_original,l.destino_original,l.extension,
            l.duracion_seg,l.hablado_seg,l.cantidad_segmentos,
            l.grabacion_disponible".$camposDirectorio.$camposTranscripcion."
        FROM central_telefonica_llamada l
        ".$unionTranscripcion."
        WHERE ".$where['sql']."
        ORDER BY l.fecha_inicio DESC,l.id_llamada DESC
        LIMIT ? OFFSET ?";
    $tipos = $where['tipos'].'ii';
    $parametros = $where['parametros'];
    $parametros[] = $limite;
    $parametros[] = $offset;
    $stmt = centralTelefonicaEjecutarConsulta($mysqli, $sql, $tipos, $parametros);
    $resultado = $stmt->get_result();
    $items = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $items[] = centralTelefonicaFilaVisible(
            $fila,
            $contexto,
            $estructuraTranscripcion
        );
    }
    $stmt->close();

    return array(
        'rango' => array('desde' => $rango['desde'], 'hasta' => $rango['hasta']),
        'resumen' => centralTelefonicaResumen($mysqli, $where),
        'llamadas' => $items,
        'extensiones' => centralTelefonicaExtensiones($mysqli, $rango),
        'catalogos' => centralTelefonicaCatalogosDirectorio(
            $mysqli,
            $rango,
            $directorioDisponible
        ),
        'directorio_disponible' => $directorioDisponible,
        'paginacion' => array(
            'pagina' => $pagina,
            'limite' => $limite,
            'total' => $total,
            'paginas' => max(1, intval(ceil($total / $limite)))
        ),
        'sincronizacion' => centralTelefonicaEstadoSincronizacion($mysqli),
        'transcripcion_servicio' => centralTelefonicaTranscripcionServicioVisible(
            $mysqli,
            $contexto,
            $estructuraTranscripcion
        ),
        'permisos' => $contexto['permisos'],
        'hora_servidor' => date('Y-m-d H:i:s')
    );
}

function centralTelefonicaTranscripcionExigirPermiso($contexto)
{
    if (empty($contexto['permisos']['transcribir_llamadas'])) {
        centralTelefonicaLanzar(
            'transcripcion_no_autorizada',
            'No tiene permiso para transcribir ni consultar conversaciones.'
        );
    }
}

function centralTelefonicaTranscripcionDetalleVisible($mysqli, $contexto, $idLlamada)
{
    if (empty($contexto['permisos']['transcribir_llamadas'])
        || !centralTelefonicaTranscripcionEstructuraDisponible($mysqli)) {
        return null;
    }

    $mysqli->set_charset('utf8mb4');
    $stmt = $mysqli->prepare(
        "SELECT id_transcripcion,estado,proveedor,modelo,idioma,fecha_solicitud,"
        ."fecha_inicio,fecha_fin,intentos,transcripcion_texto,segmentos_json,"
        ."roles_hablantes_json,roles_fuente,roles_fecha_actualizacion,"
        ."duracion_audio_seg,uso_entrada_tokens,uso_salida_tokens,uso_total_tokens,"
        ."costo_estimado_usd,codigo_error,mensaje_error,fecha_actualizacion "
        ."FROM central_telefonica_transcripcion WHERE id_llamada=? LIMIT 1"
    );
    if (!$stmt) {
        $mysqli->set_charset('latin1');
        centralTelefonicaLanzar(
            'transcripcion_no_disponible',
            'No se pudo consultar la transcripcion de la llamada.'
        );
    }
    $id = intval($idLlamada);
    $stmt->bind_param('i', $id);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    if (!$fila) {
        $mysqli->set_charset('latin1');
        return array(
            'estado' => 'sin_solicitar',
            'solicitada' => false,
            'segmentos' => array(),
            'roles_hablantes' => array(),
            'eventos' => array()
        );
    }

    $segmentos = json_decode((string)$fila['segmentos_json'], true);
    $roles = json_decode((string)$fila['roles_hablantes_json'], true);
    $segmentos = is_array($segmentos) ? $segmentos : array();
    $roles = is_array($roles) ? $roles : array();
    $eventos = array();
    $stmtEventos = $mysqli->prepare(
        "SELECT estado,codigo,detalle,actor_usuario,fecha_evento "
        ."FROM central_telefonica_transcripcion_evento "
        ."WHERE id_transcripcion=? ORDER BY id_evento DESC LIMIT 20"
    );
    if ($stmtEventos) {
        $idTranscripcion = intval($fila['id_transcripcion']);
        $stmtEventos->bind_param('i', $idTranscripcion);
        if ($stmtEventos->execute()) {
            $resultadoEventos = $stmtEventos->get_result();
            while ($resultadoEventos && ($evento = $resultadoEventos->fetch_assoc())) {
                $evento['actor_usuario'] = $evento['actor_usuario'] === null
                    ? null : intval($evento['actor_usuario']);
                $eventos[] = $evento;
            }
        }
        $stmtEventos->close();
    }
    $mysqli->set_charset('latin1');

    return array(
        'id_transcripcion' => intval($fila['id_transcripcion']),
        'estado' => $fila['estado'],
        'solicitada' => true,
        'proveedor' => $fila['proveedor'],
        'modelo' => $fila['modelo'],
        'idioma' => $fila['idioma'],
        'fecha_solicitud' => $fila['fecha_solicitud'],
        'fecha_inicio' => $fila['fecha_inicio'],
        'fecha_fin' => $fila['fecha_fin'],
        'intentos' => intval($fila['intentos']),
        'texto' => $fila['estado'] === 'completada' ? (string)$fila['transcripcion_texto'] : '',
        'segmentos' => $fila['estado'] === 'completada' ? $segmentos : array(),
        'roles_hablantes' => $fila['estado'] === 'completada' ? $roles : array(),
        'roles_fuente' => $fila['roles_fuente'],
        'roles_fecha_actualizacion' => $fila['roles_fecha_actualizacion'],
        'duracion_audio_seg' => $fila['duracion_audio_seg'] === null
            ? null : floatval($fila['duracion_audio_seg']),
        'uso' => array(
            'entrada_tokens' => intval($fila['uso_entrada_tokens']),
            'salida_tokens' => intval($fila['uso_salida_tokens']),
            'total_tokens' => intval($fila['uso_total_tokens'])
        ),
        'costo_estimado_usd' => $fila['costo_estimado_usd'] === null
            ? null : floatval($fila['costo_estimado_usd']),
        'codigo_error' => $fila['codigo_error'],
        'mensaje_error' => $fila['mensaje_error'],
        'fecha_actualizacion' => $fila['fecha_actualizacion'],
        'eventos' => $eventos
    );
}

function centralTelefonicaSolicitarTranscripcion($mysqli, $contexto, $entrada)
{
    centralTelefonicaTranscripcionExigirPermiso($contexto);
    if (!centralTelefonicaTranscripcionEstructuraDisponible($mysqli)) {
        centralTelefonicaLanzar(
            'transcripcion_no_instalada',
            'La migracion de transcripciones todavia no esta aplicada.'
        );
    }
    $servicio = centralTelefonicaTranscripcionServicioVisible($mysqli, $contexto, true);
    if (!$servicio || empty($servicio['disponible'])) {
        centralTelefonicaLanzar(
            'transcripcion_no_configurada',
            isset($servicio['mensaje'])
                ? $servicio['mensaje'] : 'El servicio de transcripcion no esta disponible.'
        );
    }
    $idLlamada = intval(isset($entrada['id_llamada']) ? $entrada['id_llamada'] : 0);
    if ($idLlamada <= 0) {
        centralTelefonicaLanzar('llamada_invalida', 'No se pudo identificar la llamada.');
    }

    $stmtLlamada = centralTelefonicaEjecutarConsulta(
        $mysqli,
        'SELECT id_llamada,grabacion_disponible FROM central_telefonica_llamada WHERE id_llamada=? LIMIT 1',
        'i',
        array($idLlamada)
    );
    $filaLlamada = $stmtLlamada->get_result()->fetch_assoc();
    $stmtLlamada->close();
    if (!$filaLlamada) {
        centralTelefonicaLanzar('llamada_no_encontrada', 'La llamada ya no esta disponible.');
    }
    if (intval($filaLlamada['grabacion_disponible']) !== 1) {
        centralTelefonicaLanzar(
            'grabacion_no_disponible',
            'Esta llamada no tiene una grabacion disponible para transcribir.'
        );
    }

    $mysqli->begin_transaction();
    $stmt = $mysqli->prepare(
        'SELECT id_transcripcion,estado FROM central_telefonica_transcripcion '
        .'WHERE id_llamada=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        $mysqli->rollback();
        centralTelefonicaLanzar('cola_no_disponible', 'No se pudo consultar la cola de transcripcion.');
    }
    $stmt->bind_param('i', $idLlamada);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    $idTranscripcion = 0;
    $codigo = 'transcripcion_encolada';
    if ($fila) {
        $idTranscripcion = intval($fila['id_transcripcion']);
        if (in_array($fila['estado'], array('en_cola', 'obteniendo_audio', 'transcribiendo'), true)) {
            $mysqli->commit();
            return array(
                'id_transcripcion' => $idTranscripcion,
                'estado' => $fila['estado'],
                'codigo' => 'transcripcion_en_curso'
            );
        }
        if ($fila['estado'] === 'completada') {
            $mysqli->commit();
            return array(
                'id_transcripcion' => $idTranscripcion,
                'estado' => 'completada',
                'codigo' => 'transcripcion_existente'
            );
        }
        $stmtActualizar = $mysqli->prepare(
            "UPDATE central_telefonica_transcripcion SET estado='en_cola',"
            ."solicitado_por=?,fecha_solicitud=NOW(),fecha_inicio=NULL,fecha_fin=NULL,"
            ."codigo_error=NULL,mensaje_error=NULL WHERE id_transcripcion=?"
        );
        if (!$stmtActualizar) {
            $mysqli->rollback();
            centralTelefonicaLanzar('cola_no_disponible', 'No se pudo reintentar la transcripcion.');
        }
        $actor = intval($contexto['cod_usuario']);
        $stmtActualizar->bind_param('ii', $actor, $idTranscripcion);
        $ok = $stmtActualizar->execute();
        $stmtActualizar->close();
        if (!$ok) {
            $mysqli->rollback();
            centralTelefonicaLanzar('cola_no_disponible', 'No se pudo reintentar la transcripcion.');
        }
        $codigo = 'transcripcion_reencolada';
    } else {
        $stmtInsertar = $mysqli->prepare(
            "INSERT INTO central_telefonica_transcripcion "
            ."(id_llamada,estado,proveedor,modelo,idioma,solicitado_por,fecha_solicitud) "
            ."VALUES (?,'en_cola','openai','gpt-4o-transcribe-diarize','es',?,NOW())"
        );
        if (!$stmtInsertar) {
            $mysqli->rollback();
            centralTelefonicaLanzar('cola_no_disponible', 'No se pudo crear la solicitud de transcripcion.');
        }
        $actor = intval($contexto['cod_usuario']);
        $stmtInsertar->bind_param('ii', $idLlamada, $actor);
        $ok = $stmtInsertar->execute();
        $idTranscripcion = intval($stmtInsertar->insert_id);
        $stmtInsertar->close();
        if (!$ok || $idTranscripcion <= 0) {
            $mysqli->rollback();
            centralTelefonicaLanzar('cola_no_disponible', 'No se pudo crear la solicitud de transcripcion.');
        }
    }
    $mysqli->commit();
    centralTelefonicaTranscripcionEvento(
        $mysqli,
        $idTranscripcion,
        'en_cola',
        $codigo,
        'Solicitud creada desde Central Telefonica.',
        intval($contexto['cod_usuario'])
    );
    return array(
        'id_transcripcion' => $idTranscripcion,
        'estado' => 'en_cola',
        'codigo' => $codigo
    );
}

function centralTelefonicaActualizarRolesTranscripcion($mysqli, $contexto, $entrada)
{
    centralTelefonicaTranscripcionExigirPermiso($contexto);
    if (!centralTelefonicaTranscripcionEstructuraDisponible($mysqli)) {
        centralTelefonicaLanzar(
            'transcripcion_no_instalada',
            'La migracion de transcripciones todavia no esta aplicada.'
        );
    }
    $idLlamada = intval(isset($entrada['id_llamada']) ? $entrada['id_llamada'] : 0);
    $rolesEntrada = isset($entrada['roles_json']) ? json_decode((string)$entrada['roles_json'], true) : null;
    if ($idLlamada <= 0 || !is_array($rolesEntrada)) {
        centralTelefonicaLanzar('roles_invalidos', 'No se pudo interpretar la asignacion de hablantes.');
    }

    $mysqli->set_charset('utf8mb4');
    $stmt = $mysqli->prepare(
        "SELECT id_transcripcion,segmentos_json FROM central_telefonica_transcripcion "
        ."WHERE id_llamada=? AND estado='completada' LIMIT 1"
    );
    if (!$stmt) {
        $mysqli->set_charset('latin1');
        centralTelefonicaLanzar('transcripcion_no_disponible', 'No se pudo consultar la transcripcion.');
    }
    $stmt->bind_param('i', $idLlamada);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    if (!$fila) {
        $mysqli->set_charset('latin1');
        centralTelefonicaLanzar('transcripcion_no_disponible', 'La llamada aun no tiene una transcripcion completa.');
    }
    $segmentos = json_decode((string)$fila['segmentos_json'], true);
    $hablantes = array();
    foreach ((array)$segmentos as $segmento) {
        $hablante = isset($segmento['speaker']) ? (string)$segmento['speaker'] : '';
        if ($hablante !== '' && !in_array($hablante, $hablantes, true)) {
            $hablantes[] = $hablante;
        }
    }
    $roles = array();
    $permitidos = array('funcionario', 'paciente', 'otro');
    foreach ($hablantes as $hablante) {
        $rol = isset($rolesEntrada[$hablante]) ? (string)$rolesEntrada[$hablante] : '';
        if (!in_array($rol, $permitidos, true)) {
            $mysqli->set_charset('latin1');
            centralTelefonicaLanzar('roles_invalidos', 'Asigne un rol valido a cada hablante.');
        }
        $roles[$hablante] = $rol;
    }
    if (count($roles) === 0) {
        $mysqli->set_charset('latin1');
        centralTelefonicaLanzar('roles_invalidos', 'La transcripcion no contiene hablantes editables.');
    }
    $rolesJson = centralTelefonicaTranscripcionJson($roles);
    $idTranscripcion = intval($fila['id_transcripcion']);
    $actor = intval($contexto['cod_usuario']);
    $stmtActualizar = $mysqli->prepare(
        "UPDATE central_telefonica_transcripcion SET roles_hablantes_json=?,"
        ."roles_fuente='manual',roles_actualizados_por=?,roles_fecha_actualizacion=NOW() "
        ."WHERE id_transcripcion=?"
    );
    $ok = false;
    if ($stmtActualizar) {
        $stmtActualizar->bind_param('sii', $rolesJson, $actor, $idTranscripcion);
        $ok = $stmtActualizar->execute();
        $stmtActualizar->close();
    }
    $mysqli->set_charset('latin1');
    if (!$ok) {
        centralTelefonicaLanzar('roles_no_guardados', 'No se pudo guardar la asignacion de hablantes.');
    }
    centralTelefonicaTranscripcionEvento(
        $mysqli,
        $idTranscripcion,
        'completada',
        'roles_actualizados',
        'La asignacion de hablantes fue corregida manualmente.',
        $actor
    );
    return array('id_transcripcion' => $idTranscripcion, 'roles_hablantes' => $roles);
}

function centralTelefonicaDetalle($mysqli, $contexto, $entrada)
{
    $id = intval(isset($entrada['id_llamada']) ? $entrada['id_llamada'] : 0);
    if ($id <= 0) {
        centralTelefonicaLanzar('llamada_invalida', 'No se pudo identificar la llamada.');
    }

    $estructuraTranscripcion = centralTelefonicaTranscripcionEstructuraDisponible($mysqli);
    $directorioDisponible = centralTelefonicaDirectorioEstructuraDisponible($mysqli);
    $camposTranscripcion = $estructuraTranscripcion
        ? ",t.estado AS transcripcion_estado,t.fecha_actualizacion AS transcripcion_actualizada,"
            ."t.mensaje_error AS transcripcion_mensaje_error"
        : ",NULL AS transcripcion_estado,NULL AS transcripcion_actualizada,"
            ."NULL AS transcripcion_mensaje_error";
    $unionTranscripcion = $estructuraTranscripcion
        ? ' LEFT JOIN central_telefonica_transcripcion t ON t.id_llamada=l.id_llamada '
        : ' ';
    $camposDirectorio = $directorioDisponible
        ? ",l.ruta_extension,l.ruta_tipo,l.ruta_nombre,"
            ."l.funcionario_extension,l.funcionario_nombre,l.funcionario_sede,"
            ."l.funcionario_destino_extension,l.funcionario_destino_nombre,"
            ."l.funcionario_destino_sede"
        : ",'' AS ruta_extension,'' AS ruta_tipo,'' AS ruta_nombre,"
            ."'' AS funcionario_extension,'' AS funcionario_nombre,"
            ."'' AS funcionario_sede,'' AS funcionario_destino_extension,"
            ."'' AS funcionario_destino_nombre,'' AS funcionario_destino_sede";
    $sql = "SELECT l.id_llamada,l.grupo_clave,l.cdr_linkedid,l.cdr_uniqueid_principal,
            l.fecha_inicio,l.fecha_fin,l.tipo,l.estado,l.origen_original,l.destino_original,
            l.extension,l.duracion_seg,l.hablado_seg,l.cantidad_segmentos,
            l.grabacion_disponible,l.clasificacion_motivo".$camposDirectorio.$camposTranscripcion."
        FROM central_telefonica_llamada l ".$unionTranscripcion."
        WHERE l.id_llamada=? LIMIT 1";
    $stmt = centralTelefonicaEjecutarConsulta($mysqli, $sql, 'i', array($id));
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$fila) {
        centralTelefonicaLanzar('llamada_no_encontrada', 'La llamada ya no esta disponible.');
    }

    $visible = centralTelefonicaFilaVisible($fila, $contexto, $estructuraTranscripcion);
    $visible['fecha_fin'] = $fila['fecha_fin'];
    $visible['clasificacion_motivo'] = $fila['clasificacion_motivo'];
    $visible['datos_tecnicos'] = null;
    $visible['segmentos'] = array();

    if (!empty($contexto['permisos']['datos_tecnicos'])) {
        $visible['datos_tecnicos'] = array(
            'linkedid' => $fila['cdr_linkedid'],
            'uniqueid_principal' => $fila['cdr_uniqueid_principal'],
            'cantidad_segmentos' => intval($fila['cantidad_segmentos'])
        );
        $sqlSegmentos = "SELECT id_segmento,cdr_uniqueid,cdr_linkedid,cdr_sequence,
                fecha_inicio,origen_original,destino_original,extension,contexto,
                canal,canal_destino,disposicion,duracion_seg,hablado_seg,
                grabacion_disponible
            FROM central_telefonica_cdr_segmento
            WHERE grupo_clave=? ORDER BY fecha_inicio,id_segmento";
        $stmtSegmentos = centralTelefonicaEjecutarConsulta(
            $mysqli,
            $sqlSegmentos,
            's',
            array($fila['grupo_clave'])
        );
        $resultadoSegmentos = $stmtSegmentos->get_result();
        while ($resultadoSegmentos && ($segmento = $resultadoSegmentos->fetch_assoc())) {
            $segmento['id_segmento'] = intval($segmento['id_segmento']);
            $segmento['cdr_sequence'] = $segmento['cdr_sequence'] === null
                ? null : intval($segmento['cdr_sequence']);
            $segmento['duracion_seg'] = intval($segmento['duracion_seg']);
            $segmento['hablado_seg'] = intval($segmento['hablado_seg']);
            $segmento['grabacion_disponible'] = intval($segmento['grabacion_disponible']) === 1;
            $segmento['origen_original'] = centralTelefonicaTelefonoVisible(
                $segmento['origen_original'],
                $contexto
            );
            $segmento['destino_original'] = centralTelefonicaTelefonoVisible(
                $segmento['destino_original'],
                $contexto
            );
            $visible['segmentos'][] = $segmento;
        }
        $stmtSegmentos->close();
    }

    $transcripcion = centralTelefonicaTranscripcionDetalleVisible(
        $mysqli,
        $contexto,
        $id
    );
    if ($transcripcion !== null) {
        $visible['transcripcion'] = $transcripcion;
    }

    return array(
        'llamada' => $visible,
        'permisos' => $contexto['permisos'],
        'transcripcion_servicio' => centralTelefonicaTranscripcionServicioVisible(
            $mysqli,
            $contexto,
            $estructuraTranscripcion
        )
    );
}

try {
    $accion = centralTelefonicaTextoEntrada(centralTelefonicaParametro('accion', ''), 50);
    if ($accion === '') {
        centralTelefonicaLanzar('accion_requerida', 'No se indico la accion solicitada.');
    }

    $codUsuario = intval(centralTelefonicaParametro('useru', 0));
    $pass = str_replace('=', '+', (string)centralTelefonicaParametro('passu', ''));
    $navegador = centralTelefonicaTextoEntrada(centralTelefonicaParametro('navegador', ''), 100);
    if ($codUsuario <= 0 || $pass === '' || $navegador === '') {
        centralTelefonicaResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    if (verificar_navegador($codUsuario, $navegador, $pass) !== 'ok') {
        centralTelefonicaResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }

    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        centralTelefonicaResponder(false, 'conexion_no_disponible', 'No se pudo conectar con Telar.', array(), 500);
    }
    if (!centralTelefonicaEstructuraDisponible($mysqli)) {
        centralTelefonicaResponder(
            false,
            'modulo_no_instalado',
            'Central Telefonica todavia no tiene aplicada su migracion.',
            array(),
            503
        );
    }

    $contexto = centralTelefonicaContexto($mysqli, $codUsuario);
    $entrada = $_POST;
    switch ($accion) {
        case 'listar':
            centralTelefonicaResponder(
                true,
                'llamadas_obtenidas',
                'Movimientos telefonicos obtenidos.',
                centralTelefonicaListar($mysqli, $contexto, $entrada)
            );
            break;
        case 'detalle':
            centralTelefonicaResponder(
                true,
                'detalle_obtenido',
                'Detalle telefonico obtenido.',
                centralTelefonicaDetalle($mysqli, $contexto, $entrada)
            );
            break;
        case 'listar_directorio':
            centralTelefonicaResponder(
                true,
                'directorio_obtenido',
                'Extensiones vigentes obtenidas.',
                centralTelefonicaDirectorioAdministrarListar($mysqli, $contexto)
            );
            break;
        case 'guardar_directorio':
            centralTelefonicaResponder(
                true,
                'directorio_actualizado',
                'La asignacion de la extension fue actualizada.',
                centralTelefonicaDirectorioAdministrarGuardar($mysqli, $contexto, $entrada)
            );
            break;
        case 'solicitar_transcripcion':
            centralTelefonicaResponder(
                true,
                'transcripcion_solicitada',
                'La llamada fue agregada a la cola de transcripcion.',
                centralTelefonicaSolicitarTranscripcion($mysqli, $contexto, $entrada)
            );
            break;
        case 'actualizar_roles_transcripcion':
            centralTelefonicaResponder(
                true,
                'roles_actualizados',
                'La asignacion de hablantes fue actualizada.',
                centralTelefonicaActualizarRolesTranscripcion($mysqli, $contexto, $entrada)
            );
            break;
        default:
            centralTelefonicaLanzar('accion_no_reconocida', 'La accion solicitada no existe.');
    }
} catch (CentralTelefonicaExcepcion $e) {
    $estado = in_array(
        $e->codigoOperacion,
        array(
            'acceso_no_autorizado',
            'transcripcion_no_autorizada',
            'directorio_no_autorizado'
        ),
        true
    ) ? 403 : 422;
    centralTelefonicaResponder(
        false,
        $e->codigoOperacion,
        $e->getMessage(),
        $e->datosOperacion,
        $estado
    );
} catch (CentralTelefonicaDirectorioExcepcion $e) {
    $estado = $e->codigoOperacion === 'extension_no_disponible' ? 404
        : ($e->codigoOperacion === 'directorio_en_actualizacion' ? 409 : 422);
    centralTelefonicaResponder(
        false,
        $e->codigoOperacion,
        $e->getMessage(),
        array(),
        $estado
    );
} catch (Exception $e) {
    error_log('[CentralTelefonica] '.get_class($e).': '.$e->getMessage());
    centralTelefonicaResponder(false, 'error_interno', 'No se pudo completar la consulta.', array(), 500);
} catch (Throwable $e) {
    error_log('[CentralTelefonica] '.get_class($e).': '.$e->getMessage());
    centralTelefonicaResponder(false, 'error_interno', 'No se pudo completar la consulta.', array(), 500);
}

?>

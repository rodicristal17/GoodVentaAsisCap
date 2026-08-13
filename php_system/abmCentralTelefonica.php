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

function centralTelefonicaPrepararWhere($entrada, $rango)
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
        "SELECT cod_usuario,estado,tipo FROM usuario WHERE cod_usuario=? LIMIT 1"
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

function centralTelefonicaFilaVisible($fila, $contexto)
{
    $tipo = isset($fila['tipo']) ? $fila['tipo'] : '';
    $origen = centralTelefonicaTelefonoVisible($fila['origen_original'], $contexto);
    $destino = centralTelefonicaTelefonoVisible($fila['destino_original'], $contexto);
    $numero = $tipo === 'entrante_externa' ? $origen
        : ($tipo === 'saliente_externa' ? $destino : $origen.' → '.$destino);

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
        'duracion_seg' => intval($fila['duracion_seg']),
        'duracion_texto' => centralTelefonicaFormatearDuracion($fila['duracion_seg']),
        'hablado_seg' => intval($fila['hablado_seg']),
        'hablado_texto' => centralTelefonicaFormatearDuracion($fila['hablado_seg']),
        'cantidad_segmentos' => intval($fila['cantidad_segmentos']),
        'grabacion_disponible' => intval($fila['grabacion_disponible']) === 1,
        'paciente' => null
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

function centralTelefonicaListar($mysqli, $contexto, $entrada)
{
    $rango = centralTelefonicaRangoEntrada($entrada);
    $where = centralTelefonicaPrepararWhere($entrada, $rango);
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

    $sql = "SELECT l.id_llamada,l.fecha_inicio,l.tipo,l.estado,
            l.origen_original,l.destino_original,l.extension,
            l.duracion_seg,l.hablado_seg,l.cantidad_segmentos,
            l.grabacion_disponible
        FROM central_telefonica_llamada l
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
        $items[] = centralTelefonicaFilaVisible($fila, $contexto);
    }
    $stmt->close();

    return array(
        'rango' => array('desde' => $rango['desde'], 'hasta' => $rango['hasta']),
        'resumen' => centralTelefonicaResumen($mysqli, $where),
        'llamadas' => $items,
        'extensiones' => centralTelefonicaExtensiones($mysqli, $rango),
        'paginacion' => array(
            'pagina' => $pagina,
            'limite' => $limite,
            'total' => $total,
            'paginas' => max(1, intval(ceil($total / $limite)))
        ),
        'sincronizacion' => centralTelefonicaEstadoSincronizacion($mysqli),
        'permisos' => $contexto['permisos'],
        'hora_servidor' => date('Y-m-d H:i:s')
    );
}

function centralTelefonicaDetalle($mysqli, $contexto, $entrada)
{
    $id = intval(isset($entrada['id_llamada']) ? $entrada['id_llamada'] : 0);
    if ($id <= 0) {
        centralTelefonicaLanzar('llamada_invalida', 'No se pudo identificar la llamada.');
    }

    $sql = "SELECT id_llamada,grupo_clave,cdr_linkedid,cdr_uniqueid_principal,
            fecha_inicio,fecha_fin,tipo,estado,origen_original,destino_original,
            extension,duracion_seg,hablado_seg,cantidad_segmentos,
            grabacion_disponible,clasificacion_motivo
        FROM central_telefonica_llamada WHERE id_llamada=? LIMIT 1";
    $stmt = centralTelefonicaEjecutarConsulta($mysqli, $sql, 'i', array($id));
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$fila) {
        centralTelefonicaLanzar('llamada_no_encontrada', 'La llamada ya no esta disponible.');
    }

    $visible = centralTelefonicaFilaVisible($fila, $contexto);
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

    return array('llamada' => $visible, 'permisos' => $contexto['permisos']);
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
        default:
            centralTelefonicaLanzar('accion_no_reconocida', 'La accion solicitada no existe.');
    }
} catch (CentralTelefonicaExcepcion $e) {
    $estado = $e->codigoOperacion === 'acceso_no_autorizado' ? 403 : 422;
    centralTelefonicaResponder(
        false,
        $e->codigoOperacion,
        $e->getMessage(),
        $e->datosOperacion,
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

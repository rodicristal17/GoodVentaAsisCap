<?php

/**
 * Nucleo de dominio para trabajos de laboratorio dental de Sistema Telar.
 *
 * Este archivo no emite respuestas HTTP. Puede reutilizarse desde Consulta,
 * Productos u otros endpoints legacy. Todas las escrituras operativas se
 * realizan mediante comandos transaccionales e idempotentes.
 *
 * Compatible con PHP 7.2.
 */

// Paraguay mantiene UTC-3. PHP 7.2 puede traer una base horaria anterior que
// todavia aplique UTC-4 en 2026, por eso este modulo usa el desplazamiento fijo.
date_default_timezone_set('Etc/GMT+3');

if (!class_exists('TrabajoLaboratorioExcepcion')) {
    class TrabajoLaboratorioExcepcion extends Exception
    {
        public $codigoOperacion;
        public $datosOperacion;

        public function __construct($codigo, $mensaje, $datos = array())
        {
            parent::__construct((string)$mensaje);
            $this->codigoOperacion = (string)$codigo;
            $this->datosOperacion = is_array($datos) ? $datos : array();
        }
    }
}

function trabajoLaboratorioLanzar($codigo, $mensaje, $datos = array())
{
    throw new TrabajoLaboratorioExcepcion($codigo, $mensaje, $datos);
}

function trabajoLaboratorioRespuesta($ok, $codigo, $mensaje, $datos = array(), $version = null)
{
    return array(
        'ok' => $ok ? true : false,
        'codigo' => (string)$codigo,
        'mensaje' => (string)$mensaje,
        'datos' => is_array($datos) ? $datos : array(),
        'version' => $version === null ? null : intval($version)
    );
}

function trabajoLaboratorioEntero($valor)
{
    if (is_int($valor)) {
        return $valor;
    }
    $valor = trim((string)$valor);
    return preg_match('/^-?[0-9]+$/', $valor) ? intval($valor) : 0;
}

function trabajoLaboratorioIdEntrada($entrada)
{
    if (!is_array($entrada)) {
        return 0;
    }
    if (isset($entrada['id_trabajo'])) {
        return trabajoLaboratorioEntero($entrada['id_trabajo']);
    }
    if (isset($entrada['id'])) {
        return trabajoLaboratorioEntero($entrada['id']);
    }
    if (isset($entrada['cod_trabajo_laboratorio'])) {
        return trabajoLaboratorioEntero($entrada['cod_trabajo_laboratorio']);
    }
    return 0;
}

function trabajoLaboratorioTextoEntrada($valor, $maximo = 0)
{
    if (is_array($valor) || is_object($valor)) {
        return '';
    }
    $texto = trim((string)$valor);
    $texto = str_replace("\0", '', $texto);
    if ($maximo > 0) {
        if (function_exists('mb_substr')) {
            $texto = mb_substr($texto, 0, intval($maximo), 'UTF-8');
        } else {
            $texto = substr($texto, 0, intval($maximo));
        }
    }
    return $texto;
}

function trabajoLaboratorioTextoBaseDatos($valor, $maximo = 0)
{
    $texto = trabajoLaboratorioTextoEntrada($valor, $maximo);
    if ($texto !== '' && function_exists('mb_convert_encoding')) {
        $texto = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
    return $texto;
}

function trabajoLaboratorioTextoUtf8($valor)
{
    if ($valor === null) {
        return null;
    }
    $texto = (string)$valor;
    if ($texto === '' || preg_match('//u', $texto)) {
        return $texto;
    }
    if (function_exists('mb_convert_encoding')) {
        $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
    }
    return $texto;
}

function trabajoLaboratorioTimestampSistema($valor)
{
    $texto = trim((string)$valor);
    if ($texto === '') {
        return false;
    }
    static $zona = null;
    if ($zona === null) {
        try {
            $zona = new DateTimeZone('Etc/GMT+3');
        } catch (Exception $e) {
            return false;
        }
    }
    $formatos = array('!Y-m-d H:i:s', '!Y-m-d H:i', '!Y-m-d');
    foreach ($formatos as $formato) {
        $fecha = DateTime::createFromFormat($formato, $texto, $zona);
        if ($fecha instanceof DateTime) {
            $errores = DateTime::getLastErrors();
            if ($errores === false
                || (intval($errores['warning_count']) === 0 && intval($errores['error_count']) === 0)) {
                return $fecha->getTimestamp();
            }
        }
    }
    return false;
}

function trabajoLaboratorioUtf8($valor)
{
    if (is_array($valor)) {
        $salida = array();
        foreach ($valor as $clave => $item) {
            $salida[$clave] = trabajoLaboratorioUtf8($item);
        }
        return $salida;
    }
    if (is_string($valor)) {
        return trabajoLaboratorioTextoUtf8($valor);
    }
    return $valor;
}

function trabajoLaboratorioNormalizarClave($valor)
{
    $texto = trabajoLaboratorioTextoEntrada($valor, 100);
    if ($texto === '' || !preg_match('/^[A-Za-z0-9._:-]{8,100}$/', $texto)) {
        trabajoLaboratorioLanzar(
            'clave_idempotencia_invalida',
            'La operacion necesita una clave de idempotencia de 8 a 100 caracteres.'
        );
    }
    return $texto;
}

function trabajoLaboratorioDecodificarJson($valor, $porDefecto = array())
{
    if (is_array($valor)) {
        return $valor;
    }
    $texto = trim((string)$valor);
    if ($texto === '') {
        return $porDefecto;
    }
    $datos = json_decode($texto, true);
    return is_array($datos) ? $datos : $porDefecto;
}

function trabajoLaboratorioNormalizarTexto($valor)
{
    $texto = strtolower(trim((string)$valor));
    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($convertido !== false && $convertido !== '') {
            $texto = $convertido;
        }
    }
    $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
    return trim($texto, '_');
}

function trabajoLaboratorioTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '') {
        return false;
    }
    if (isset($cache[$tabla])) {
        return $cache[$tabla];
    }
    $stmt = $mysqli->prepare(
        'SELECT 1 FROM information_schema.TABLES '
        .'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1'
    );
    if (!$stmt) {
        $cache[$tabla] = false;
        return false;
    }
    $stmt->bind_param('s', $tabla);
    $stmt->execute();
    $cache[$tabla] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$tabla];
}

function trabajoLaboratorioColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$columna);
    $clave = $tabla.'.'.$columna;
    if ($tabla === '' || $columna === '') {
        return false;
    }
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    $stmt = $mysqli->prepare(
        'SELECT 1 FROM information_schema.COLUMNS '
        .'WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1'
    );
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $cache[$clave] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$clave];
}

function trabajoLaboratorioEstructuraDisponible($mysqli)
{
    $tablas = array(
        'trabajo_laboratorio',
        'trabajo_laboratorio_ciclo',
        'trabajo_laboratorio_transferencia',
        'trabajo_laboratorio_idempotencia',
        'trabajo_laboratorio_evento',
        'trabajo_laboratorio_ubicacion',
        'trabajo_laboratorio_media'
    );
    foreach ($tablas as $tabla) {
        if (!trabajoLaboratorioTablaExiste($mysqli, $tabla)) {
            return false;
        }
    }
    return trabajoLaboratorioConfiguracionDisponible($mysqli)
        && trabajoLaboratorioColumnaExiste($mysqli, 'mecanico_dental', 'cod_usuarioFK');
}

/**
 * Funciones publicas de configuracion reutilizables desde ABM Producto.
 */
function trabajoLaboratorioConfiguracionDisponible($mysqli)
{
    return trabajoLaboratorioColumnaExiste($mysqli, 'categoria', 'requiere_laboratorio')
        && trabajoLaboratorioColumnaExiste($mysqli, 'categoria', 'modo_individualizacion')
        && trabajoLaboratorioColumnaExiste($mysqli, 'producto', 'requiere_laboratorio')
        && trabajoLaboratorioColumnaExiste($mysqli, 'producto', 'modo_individualizacion');
}

function trabajoLaboratorioModosIndividualizacion()
{
    return array(
        'cantidad_libre',
        'pieza_individual',
        'multipieza',
        'arcada',
        'sector',
        'dispositivo'
    );
}

function trabajoLaboratorioModoIndividualizacionValido($modo)
{
    return in_array(trabajoLaboratorioNormalizarTexto($modo), trabajoLaboratorioModosIndividualizacion(), true);
}

function trabajoLaboratorioObtenerConfiguracionProducto($mysqli, $codProducto)
{
    $codProductoEntrada = trabajoLaboratorioTextoEntrada($codProducto, 45);
    if ($codProductoEntrada === '') {
        return array('ok' => false, 'codigo' => 'producto_invalido', 'mensaje' => 'No se indico el producto.');
    }
    if (!trabajoLaboratorioConfiguracionDisponible($mysqli)) {
        return array(
            'ok' => false,
            'codigo' => 'configuracion_laboratorio_no_instalada',
            'mensaje' => 'La configuracion de laboratorio todavia no esta instalada.'
        );
    }

    $codProductoBd = trabajoLaboratorioTextoBaseDatos($codProductoEntrada, 45);
    $stmt = $mysqli->prepare(
        'SELECT p.cod_producto,p.cod_categoriaFK,p.requiere_laboratorio AS producto_requiere,'
        .'p.modo_individualizacion AS producto_modo,c.requiere_laboratorio AS categoria_requiere,'
        .'c.modo_individualizacion AS categoria_modo '
        .'FROM producto p INNER JOIN categoria c ON c.cod_categoria=p.cod_categoriaFK '
        .'WHERE p.cod_producto=? LIMIT 1'
    );
    if (!$stmt) {
        return array('ok' => false, 'codigo' => 'consulta_configuracion', 'mensaje' => 'No se pudo consultar la configuracion del producto.');
    }
    $stmt->bind_param('s', $codProductoBd);
    if (!$stmt->execute()) {
        $stmt->close();
        return array('ok' => false, 'codigo' => 'consulta_configuracion', 'mensaje' => 'No se pudo consultar la configuracion del producto.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        return array('ok' => false, 'codigo' => 'producto_no_encontrado', 'mensaje' => 'El producto no existe.');
    }

    $productoDefineRequiere = $fila['producto_requiere'] !== null && $fila['producto_requiere'] !== '';
    $requiere = $productoDefineRequiere
        ? intval($fila['producto_requiere']) === 1
        : intval($fila['categoria_requiere']) === 1;
    $origenRequiere = $productoDefineRequiere ? 'producto' : 'categoria';

    $modoProducto = trabajoLaboratorioNormalizarTexto($fila['producto_modo']);
    $modoCategoria = trabajoLaboratorioNormalizarTexto($fila['categoria_modo']);
    if ($modoProducto !== '') {
        $modo = $modoProducto;
        $origenModo = 'producto';
    } elseif ($modoCategoria !== '') {
        $modo = $modoCategoria;
        $origenModo = 'categoria';
    } else {
        $modo = 'cantidad_libre';
        $origenModo = 'predeterminado';
    }

    if (!trabajoLaboratorioModoIndividualizacionValido($modo)) {
        return array(
            'ok' => false,
            'codigo' => 'modo_individualizacion_invalido',
            'mensaje' => 'El modo de individualizacion configurado no es valido.',
            'cod_producto' => trabajoLaboratorioTextoUtf8($fila['cod_producto']),
            'cod_categoria' => intval($fila['cod_categoriaFK'])
        );
    }

    return array(
        'ok' => true,
        'codigo' => 'configuracion_disponible',
        'mensaje' => '',
        'cod_producto' => trabajoLaboratorioTextoUtf8($fila['cod_producto']),
        'cod_categoria' => intval($fila['cod_categoriaFK']),
        'requiere_laboratorio' => $requiere,
        'origen_requiere_laboratorio' => $origenRequiere,
        'modo_individualizacion' => $modo,
        'origen_modo_individualizacion' => $origenModo
    );
}

function trabajoLaboratorioTienePermiso($mysqli, $codUsuario, $codigo)
{
    static $cache = array();
    $codUsuario = intval($codUsuario);
    $codigo = strtoupper(trim((string)$codigo));
    $clave = $codUsuario.'|'.$codigo;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    if ($codUsuario <= 0 || $codigo === '') {
        $cache[$clave] = false;
        return false;
    }
    $stmt = $mysqli->prepare(
        "SELECT 1 FROM accesosuser au "
        ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
        ."WHERE au.usuarios_idusario=? AND la.codigo=? AND au.accion='SI' LIMIT 1"
    );
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $stmt->bind_param('is', $codUsuario, $codigo);
    $stmt->execute();
    $cache[$clave] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$clave];
}

function trabajoLaboratorioUsuario($mysqli, $codUsuario)
{
    static $cache = array();
    $codUsuario = intval($codUsuario);
    if (isset($cache[$codUsuario])) {
        return $cache[$codUsuario];
    }
    if ($codUsuario <= 0) {
        return null;
    }
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,u.cod_localFK,u.tipo,u.estado,u.url,p.nombre_persona "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."WHERE u.cod_usuario=? AND u.estado='Activo' LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $codUsuario);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        $cache[$codUsuario] = null;
        return null;
    }
    $fila['cod_usuario'] = intval($fila['cod_usuario']);
    $fila['cod_localFK'] = intval($fila['cod_localFK']);
    $cache[$codUsuario] = $fila;
    return $fila;
}

function trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario)
{
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    return $usuario && trabajoLaboratorioNormalizarTexto($usuario['tipo']) === 'doctor';
}

function trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
{
    return trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'AUDITARTRABAJOLABORATORIO');
}

function trabajoLaboratorioUsuarioPuedeTodosLocales($mysqli, $codUsuario)
{
    return trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
        || trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CAMBIARLOCAL');
}

function trabajoLaboratorioUsuarioPuedeLocal($mysqli, $codUsuario, $codLocal)
{
    $codLocal = intval($codLocal);
    if ($codLocal <= 0) {
        return false;
    }
    if (trabajoLaboratorioUsuarioPuedeTodosLocales($mysqli, $codUsuario)) {
        return true;
    }
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    return $usuario && intval($usuario['cod_localFK']) === $codLocal;
}

/* CAMBIARLOCAL conserva el alcance global de consulta del sistema legacy,
   pero una accion fisica o clinica se ejecuta en la sucursal propia. Las
   excepciones globales quedan reservadas al permiso auditor. */
function trabajoLaboratorioUsuarioPuedeOperarLocal($mysqli, $codUsuario, $codLocal)
{
    $codLocal = intval($codLocal);
    if ($codLocal <= 0) {
        return false;
    }
    if (trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        return true;
    }
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    return $usuario && intval($usuario['cod_localFK']) === $codLocal;
}

function trabajoLaboratorioUsuarioPerteneceLocal($mysqli, $codUsuario, $codLocal)
{
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    return $usuario && intval($usuario['cod_localFK']) === intval($codLocal);
}

function trabajoLaboratorioTecnicosDisponibles($mysqli, $soloHabilitados = false)
{
    if (!trabajoLaboratorioColumnaExiste($mysqli, 'mecanico_dental', 'cod_usuarioFK')) {
        return array();
    }
    $sql = "SELECT md.cod_mecanico_dental,u.cod_usuario,u.url,p.nombre_persona,u.cod_localFK,l.Nombre AS nombre_local "
        ."FROM mecanico_dental md "
        ."INNER JOIN usuario u ON u.cod_usuario=md.cod_usuarioFK AND u.estado='Activo' "
        ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."WHERE md.estado='activo' ORDER BY p.nombre_persona ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        return array();
    }
    $resultado = $stmt->get_result();
    $tecnicos = array();
    while ($fila = $resultado->fetch_assoc()) {
        $puedeVer = trabajoLaboratorioTienePermiso(
            $mysqli,
            intval($fila['cod_usuario']),
            'VERTRABAJOSLABORATORIO'
        );
        $puedeRecibir = trabajoLaboratorioTienePermiso(
            $mysqli,
            intval($fila['cod_usuario']),
            'RECIBIRTRABAJOLABORATORIO'
        );
        $puedeEntregar = trabajoLaboratorioTienePermiso(
            $mysqli,
            intval($fila['cod_usuario']),
            'ENTREGARTRABAJOLABORATORIO'
        );
        $habilitadoFlujo = $puedeVer && $puedeRecibir && $puedeEntregar;
        if ($soloHabilitados && !$habilitadoFlujo) {
            continue;
        }
        $tecnicos[] = array(
            'cod_usuario' => intval($fila['cod_usuario']),
            'cod_mecanico_dental' => intval($fila['cod_mecanico_dental']),
            'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_persona']),
            'avatar' => trabajoLaboratorioTextoUtf8($fila['url']),
            'cod_local' => intval($fila['cod_localFK']),
            'nombre_local' => trabajoLaboratorioTextoUtf8($fila['nombre_local']),
            'habilitado_flujo' => $habilitadoFlujo,
            'puede_ver_trabajos' => $puedeVer,
            'puede_recibir_trabajos' => $puedeRecibir,
            'puede_entregar_trabajos' => $puedeEntregar
        );
    }
    $stmt->close();
    return $tecnicos;
}

function trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, $bloquear = false)
{
    static $cache = array();
    $codUsuario = intval($codUsuario);
    if (!$bloquear && array_key_exists($codUsuario, $cache)) {
        return $cache[$codUsuario];
    }
    if ($codUsuario <= 0 || !trabajoLaboratorioColumnaExiste($mysqli, 'mecanico_dental', 'cod_usuarioFK')) {
        if (!$bloquear) {
            $cache[$codUsuario] = null;
        }
        return null;
    }
    $sql = "SELECT md.cod_mecanico_dental,md.cod_usuarioFK,u.cod_localFK,u.tipo,u.url,p.nombre_persona "
        ."FROM mecanico_dental md "
        ."INNER JOIN usuario u ON u.cod_usuario=md.cod_usuarioFK AND u.estado='Activo' "
        ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."WHERE md.cod_usuarioFK=? AND md.estado='activo' LIMIT 1";
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $codUsuario);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        if (!$bloquear) {
            $cache[$codUsuario] = null;
        }
        return null;
    }
    $fila['cod_mecanico_dental'] = intval($fila['cod_mecanico_dental']);
    $fila['cod_usuarioFK'] = intval($fila['cod_usuarioFK']);
    $fila['cod_localFK'] = intval($fila['cod_localFK']);
    if (!$bloquear) {
        $cache[$codUsuario] = $fila;
    }
    return $fila;
}

function trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, $codVenta, $bloquear = false)
{
    $codVenta = intval($codVenta);
    if ($codVenta <= 0
        || !trabajoLaboratorioTablaExiste($mysqli, 'interconsulta_paciente_venta')
        || !trabajoLaboratorioTablaExiste($mysqli, 'interconsulta')) {
        return null;
    }
    $sql = "SELECT ipv.cod_interConsultaFK,ipv.cedula_normalizada,ic.estado,ic.cod_localFK "
        ."FROM interconsulta_paciente_venta ipv "
        ."INNER JOIN interconsulta ic ON ic.cod_interConsulta=ipv.cod_interConsultaFK "
        ."WHERE ipv.cod_ventaFK=? AND ipv.estado='activo' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $codVenta);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        return null;
    }
    $fila['cod_interConsultaFK'] = intval($fila['cod_interConsultaFK']);
    $fila['cod_localFK'] = intval($fila['cod_localFK']);
    if (!$bloquear) {
        return $fila;
    }

    /* La fusion segura bloquea primero los Hilos y recien despues mueve la
       relacion de la venta. Para conservar ese mismo orden no se debe hacer
       FOR UPDATE sobre el JOIN anterior: podria bloquear primero la relacion
       y formar un ciclo con una fusion concurrente. Se bloquea el Hilo por su
       PK y, si ya fue fusionado, se sigue la cadena auditada hasta el maestro
       vigente. Solo entonces se bloquea y confirma la relacion de la venta. */
    $hiloCandidato = intval($fila['cod_interConsultaFK']);
    $visitados = array();
    for ($intento = 0; $intento < 25; $intento++) {
        if ($hiloCandidato <= 0 || isset($visitados[$hiloCandidato])) {
            return null;
        }
        $visitados[$hiloCandidato] = true;

        $stmtHilo = $mysqli->prepare(
            "SELECT cod_interConsulta,estado,cod_localFK FROM interconsulta "
            ."WHERE cod_interConsulta=? LIMIT 1 FOR UPDATE"
        );
        if (!$stmtHilo) {
            return null;
        }
        $stmtHilo->bind_param('i', $hiloCandidato);
        if (!$stmtHilo->execute()) {
            $stmtHilo->close();
            return null;
        }
        $hiloBloqueado = $stmtHilo->get_result()->fetch_assoc();
        $stmtHilo->close();
        if (!$hiloBloqueado) {
            return null;
        }

        if (strtolower(trim((string)$hiloBloqueado['estado'])) !== 'inactivo') {
            $stmtVinculo = $mysqli->prepare(
                "SELECT cod_interConsultaFK,cedula_normalizada "
                ."FROM interconsulta_paciente_venta "
                ."WHERE cod_ventaFK=? AND estado='activo' LIMIT 1 FOR UPDATE"
            );
            if (!$stmtVinculo) {
                return null;
            }
            $stmtVinculo->bind_param('i', $codVenta);
            if (!$stmtVinculo->execute()) {
                $stmtVinculo->close();
                return null;
            }
            $vinculoVigente = $stmtVinculo->get_result()->fetch_assoc();
            $stmtVinculo->close();
            if (!$vinculoVigente
                || intval($vinculoVigente['cod_interConsultaFK']) !== $hiloCandidato) {
                return null;
            }
            return array(
                'cod_interConsultaFK' => $hiloCandidato,
                'cedula_normalizada' => $vinculoVigente['cedula_normalizada'],
                'estado' => $hiloBloqueado['estado'],
                'cod_localFK' => intval($hiloBloqueado['cod_localFK'])
            );
        }

        if (!trabajoLaboratorioTablaExiste($mysqli, 'interconsulta_fusion')) {
            return null;
        }
        $stmtFusion = $mysqli->prepare(
            "SELECT cod_interConsulta_destinoFK FROM interconsulta_fusion "
            ."WHERE cod_interConsulta_origenFK=? AND estado='aplicada' "
            ."ORDER BY id_fusion DESC LIMIT 1 FOR UPDATE"
        );
        if (!$stmtFusion) {
            return null;
        }
        $stmtFusion->bind_param('i', $hiloCandidato);
        if (!$stmtFusion->execute()) {
            $stmtFusion->close();
            return null;
        }
        $fusion = $stmtFusion->get_result()->fetch_assoc();
        $stmtFusion->close();
        if (!$fusion) {
            return null;
        }
        $hiloCandidato = intval($fusion['cod_interConsulta_destinoFK']);
    }
    return null;
}

function trabajoLaboratorioSiglaLocal($codLocal, $nombreLocal)
{
    if (function_exists('centroLegajoSiglaLocal')) {
        return centroLegajoSiglaLocal($codLocal, $nombreLocal);
    }
    $siglas = array(1 => 'ADM', 3 => 'CC', 5 => 'VI', 6 => 'PM', 7 => 'SL', 8 => 'CQ', 9 => 'VM');
    $codLocal = intval($codLocal);
    if (isset($siglas[$codLocal])) {
        return $siglas[$codLocal];
    }
    $nombre = strtoupper(trabajoLaboratorioTextoUtf8($nombreLocal));
    if (function_exists('iconv')) {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
        if ($ascii !== false) {
            $nombre = $ascii;
        }
    }
    $palabras = preg_split('/[^A-Z0-9]+/', $nombre, -1, PREG_SPLIT_NO_EMPTY);
    $sigla = '';
    foreach ((array)$palabras as $palabra) {
        $sigla .= substr($palabra, 0, 1);
        if (strlen($sigla) >= 3) {
            break;
        }
    }
    return $sigla !== '' ? $sigla : ($codLocal > 0 ? 'L'.$codLocal : 'LOC');
}

function trabajoLaboratorioNumeroVenta($venta)
{
    $numeroFactura = isset($venta['num_factura']) ? trim((string)$venta['num_factura']) : '';
    $numeroDetalle = isset($venta['nroventa']) ? trim((string)$venta['nroventa']) : '';
    if ($numeroFactura === '' && $numeroDetalle !== '') {
        return preg_replace('/\s+/', '', $numeroDetalle);
    }
    if (function_exists('centroLegajoNumeroVenta')) {
        return centroLegajoNumeroVenta($venta);
    }
    $numero = $numeroFactura;
    $punto = isset($venta['puntoexpedicion']) ? trim((string)$venta['puntoexpedicion']) : '';
    if ($numero === '') {
        return isset($venta['cod_venta']) ? (string)intval($venta['cod_venta']) : '0';
    }
    $numero = preg_replace('/\s+/', '', $numero);
    $punto = preg_replace('/\s+/', '', $punto);
    return $punto !== '' && stripos($numero, $punto.'-') !== 0 ? $punto.'-'.$numero : $numero;
}

function trabajoLaboratorioCodigoVisible($venta, $idTrabajo)
{
    $numero = strtoupper(trabajoLaboratorioNumeroVenta($venta));
    $sigla = strtoupper(trabajoLaboratorioSiglaLocal(
        isset($venta['cod_local']) ? $venta['cod_local'] : 0,
        isset($venta['nombre_local']) ? $venta['nombre_local'] : ''
    ));
    $numero = preg_replace('/[^A-Z0-9_-]+/', '', $numero);
    $sigla = preg_replace('/[^A-Z0-9]+/', '', $sigla);
    if ($numero === '') {
        $numero = (string)intval(isset($venta['cod_venta']) ? $venta['cod_venta'] : 0);
    }
    if ($sigla === '') {
        $sigla = 'LOC';
    }
    if (!preg_match('/-'.preg_quote($sigla, '/').'$/', $numero)) {
        $numero .= '-'.$sigla;
    }
    return $numero.'-LAB-'.intval($idTrabajo);
}

function trabajoLaboratorioListaJson($valor)
{
    $lista = trabajoLaboratorioDecodificarJson($valor, array());
    $salida = array();
    foreach ($lista as $item) {
        if (is_array($item)) {
            $item = isset($item['pieza']) ? $item['pieza'] : '';
        }
        $item = trabajoLaboratorioTextoEntrada($item, 30);
        if ($item !== '' && !in_array($item, $salida, true)) {
            $salida[] = $item;
        }
    }
    return $salida;
}

function trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, $codDetalle)
{
    $codDetalle = intval($codDetalle);
    if ($codDetalle <= 0 || !trabajoLaboratorioTablaExiste($mysqli, 'odontograma_tratamiento_links')) {
        return array();
    }
    $columnas = array('id', 'detalle_venta_id', 'pieza', 'piezas_json', 'superficies_json',
        'arcada', 'cuadrante', 'boca_completa', 'denticion', 'alcance_odontologico');
    foreach ($columnas as $columna) {
        if (!trabajoLaboratorioColumnaExiste($mysqli, 'odontograma_tratamiento_links', $columna)) {
            return array();
        }
    }
    $condicionActivo = trabajoLaboratorioColumnaExiste($mysqli, 'odontograma_tratamiento_links', 'activo')
        ? ' AND activo=1' : '';
    $stmt = $mysqli->prepare(
        'SELECT id,pieza,piezas_json,superficies_json,arcada,cuadrante,boca_completa,denticion,alcance_odontologico '
        .'FROM odontograma_tratamiento_links WHERE detalle_venta_id=?'.$condicionActivo.' ORDER BY id ASC'
    );
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $ubicaciones = array();
    while ($fila = $resultado->fetch_assoc()) {
        $pieza = trabajoLaboratorioTextoUtf8($fila['pieza']);
        $piezas = trabajoLaboratorioListaJson(trabajoLaboratorioTextoUtf8($fila['piezas_json']));
        if ($pieza !== null && trim($pieza) !== '' && !in_array($pieza, $piezas, true)) {
            array_unshift($piezas, $pieza);
        }
        $ubicaciones[] = array(
            'id' => intval($fila['id']),
            'pieza' => $pieza,
            'piezas' => $piezas,
            'superficies' => trabajoLaboratorioListaJson(trabajoLaboratorioTextoUtf8($fila['superficies_json'])),
            'arcada' => trabajoLaboratorioTextoUtf8($fila['arcada']),
            'cuadrante' => trabajoLaboratorioTextoUtf8($fila['cuadrante']),
            'boca_completa' => intval($fila['boca_completa']) === 1,
            'denticion' => trabajoLaboratorioTextoUtf8($fila['denticion']),
            'alcance' => trabajoLaboratorioTextoUtf8($fila['alcance_odontologico'])
        );
    }
    $stmt->close();
    return $ubicaciones;
}

function trabajoLaboratorioValidarUbicacionesModo($modo, $ubicaciones)
{
    $modo = trabajoLaboratorioNormalizarTexto($modo);
    $piezas = array();
    $tieneArcada = false;
    $tieneSector = false;
    foreach ((array)$ubicaciones as $ubicacion) {
        $lista = isset($ubicacion['piezas']) && is_array($ubicacion['piezas']) ? $ubicacion['piezas'] : array();
        if (isset($ubicacion['pieza']) && trim((string)$ubicacion['pieza']) !== '') {
            $lista[] = $ubicacion['pieza'];
        }
        foreach ($lista as $pieza) {
            $pieza = trim((string)$pieza);
            if ($pieza !== '' && !in_array($pieza, $piezas, true)) {
                $piezas[] = $pieza;
            }
        }
        if (!empty($ubicacion['arcada'])) {
            $tieneArcada = true;
        }
        if (!empty($ubicacion['cuadrante'])
            || trabajoLaboratorioNormalizarTexto(isset($ubicacion['alcance']) ? $ubicacion['alcance'] : '') === 'sector') {
            $tieneSector = true;
        }
    }
    if ($modo === 'pieza_individual' && count($piezas) !== 1) {
        return array('codigo' => 'pieza_individual_invalida', 'mensaje' => 'El modo pieza individual debe resolver exactamente una pieza dental.');
    }
    if ($modo === 'multipieza' && count($piezas) < 2) {
        return array('codigo' => 'multipieza_invalida', 'mensaje' => 'El modo multipieza debe conservar dos o mas piezas en la misma ubicacion clinica.');
    }
    if ($modo === 'arcada' && !$tieneArcada) {
        return array('codigo' => 'arcada_requerida', 'mensaje' => 'El modo arcada necesita una arcada registrada.');
    }
    if ($modo === 'sector' && !$tieneSector && count($piezas) < 2) {
        return array('codigo' => 'sector_requerido', 'mensaje' => 'El modo sector necesita un sector, cuadrante o conjunto de piezas registrado.');
    }
    return null;
}

function trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, $bloquear = false)
{
    $codDetalle = intval($codDetalle);
    if ($codDetalle <= 0) {
        return null;
    }
    $sql = 'SELECT dv.cod_detalle,dv.cantidad_detalle,dv.estado AS estado_detalle,'
        .'dv.estado_tratamiento,dv.progreso_porcentaje,dv.cod_productoFK,dv.cod_ventaFK,dv.nroventa,'
        .'p.nombre_producto,p.cod_categoriaFK,c.descripcion AS nombre_categoria,'
        .'v.cod_clienteFK,v.cod_local,v.num_factura,v.puntoexpedicion,v.estado AS estado_venta,'
        .'l.Nombre AS nombre_local,pc.nombre_persona AS nombre_paciente '
        .'FROM detalle_venta dv INNER JOIN producto p ON p.cod_producto=dv.cod_productoFK '
        .'LEFT JOIN categoria c ON c.cod_categoria=p.cod_categoriaFK '
        .'INNER JOIN venta v ON v.cod_venta=dv.cod_ventaFK '
        .'LEFT JOIN local l ON l.cod_local=v.cod_local '
        .'LEFT JOIN persona pc ON pc.cod_persona=v.cod_clienteFK WHERE dv.cod_detalle=? LIMIT 1';
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('consulta_detalle_no_disponible', 'No se pudo consultar el detalle clinico.');
    }
    $stmt->bind_param('i', $codDetalle);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('consulta_detalle_no_disponible', 'No se pudo consultar el detalle clinico.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        return null;
    }
    $configuracion = trabajoLaboratorioObtenerConfiguracionProducto($mysqli, $fila['cod_productoFK']);
    $fila['configuracion_laboratorio'] = $configuracion;
    return $fila;
}

function trabajoLaboratorioObtenerTrabajoActivoDetalle($mysqli, $codDetalle)
{
    if (!trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio')) {
        return null;
    }
    $codDetalle = intval($codDetalle);
    $stmt = $mysqli->prepare(
        'SELECT id,codigo_visible,estado_derivado,version,cod_tecnico_usuarioFK,fecha_objetivo '
        .'FROM trabajo_laboratorio WHERE cod_detalle_activo_unico=? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        return null;
    }
    return array(
        'id' => intval($fila['id']),
        'codigo_visible' => trabajoLaboratorioTextoUtf8($fila['codigo_visible']),
        'estado_derivado' => $fila['estado_derivado'],
        'version' => intval($fila['version']),
        'cod_tecnico_usuario' => intval($fila['cod_tecnico_usuarioFK']),
        'fecha_objetivo' => $fila['fecha_objetivo']
    );
}

function trabajoLaboratorioValidarOrigenClinico($mysqli, $codDetalle, $codVenta, $codConsulta, $codEvolucion, $obligatorio)
{
    $codDetalle = intval($codDetalle);
    $codVenta = intval($codVenta);
    $codConsulta = intval($codConsulta);
    $codEvolucion = intval($codEvolucion);
    if ($obligatorio && $codEvolucion <= 0) {
        trabajoLaboratorioLanzar(
            'evolucion_origen_requerida',
            'La instalacion debe vincularse expresamente a una evolucion del mismo tratamiento.'
        );
    }
    if ($codConsulta > 0) {
        $stmt = $mysqli->prepare(
            'SELECT 1 FROM consulta WHERE cod_consulta=? AND cod_ventaFK=? '
            .'AND (cod_detalle_ventaFK=? OR cod_detalle_ventaFK IS NULL) LIMIT 1'
        );
        if (!$stmt) {
            trabajoLaboratorioLanzar('origen_clinico_no_disponible', 'No se pudo validar la consulta clinica.');
        }
        $stmt->bind_param('iii', $codConsulta, $codVenta, $codDetalle);
        $stmt->execute();
        $valido = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$valido) {
            trabajoLaboratorioLanzar('consulta_origen_invalida', 'La consulta no corresponde a esta venta y tratamiento.');
        }
    }
    if ($codEvolucion > 0) {
        $stmt = $mysqli->prepare(
            'SELECT 1 FROM evoluciontratamiento WHERE cod_evoluciontratamiento=? AND cod_detalle_venta=? LIMIT 1'
        );
        if (!$stmt) {
            trabajoLaboratorioLanzar('origen_clinico_no_disponible', 'No se pudo validar la evolucion clinica.');
        }
        $stmt->bind_param('ii', $codEvolucion, $codDetalle);
        $stmt->execute();
        $valido = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$valido) {
            trabajoLaboratorioLanzar('evolucion_origen_invalida', 'La evolucion no corresponde a este tratamiento.');
        }
    }
    return array(
        'cod_consulta_origen' => $codConsulta > 0 ? $codConsulta : null,
        'cod_evolucion_origen' => $codEvolucion > 0 ? $codEvolucion : null
    );
}

function trabajoLaboratorioValidarEvolucionInstalacion(
    $mysqli,
    $trabajo,
    $codUsuario,
    $codConsulta,
    $codEvolucion,
    $esExcepcionAuditor
) {
    $origen = trabajoLaboratorioValidarOrigenClinico(
        $mysqli,
        intval($trabajo['cod_detalle_ventaFK']),
        intval($trabajo['cod_ventaFK']),
        $codConsulta,
        $codEvolucion,
        true
    );
    $codEvolucion = intval($origen['cod_evolucion_origen']);
    $codConsulta = intval($origen['cod_consulta_origen']);
    $codDetalle = intval($trabajo['cod_detalle_ventaFK']);
    $stmt = $mysqli->prepare(
        'SELECT et.cod_usuraioFK,et.fecha,et.cod_agendaFK,'
        .'c.cod_agendamientoFK AS cod_agenda_consulta,c.cod_ventaFK AS cod_venta_consulta,'
        .'c.cod_detalle_ventaFK AS cod_detalle_consulta '
        .'FROM evoluciontratamiento et '
        .'LEFT JOIN consulta c ON c.cod_consulta=? '
        .'WHERE et.cod_evoluciontratamiento=? AND et.cod_detalle_venta=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'origen_clinico_no_disponible',
            'No se pudo verificar la evolucion utilizada para la instalacion.'
        );
    }
    $stmt->bind_param('iii', $codConsulta, $codEvolucion, $codDetalle);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'origen_clinico_no_disponible',
            'No se pudo verificar la evolucion utilizada para la instalacion.'
        );
    }
    $evolucion = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$evolucion) {
        trabajoLaboratorioLanzar(
            'evolucion_origen_invalida',
            'La evolucion no corresponde al tratamiento que se desea instalar.'
        );
    }

    if ($codConsulta > 0
        && (intval($evolucion['cod_venta_consulta']) !== intval($trabajo['cod_ventaFK'])
            || ($evolucion['cod_detalle_consulta'] !== null
                && intval($evolucion['cod_detalle_consulta']) !== $codDetalle))) {
        trabajoLaboratorioLanzar(
            'consulta_origen_invalida',
            'La consulta ya no corresponde a la venta y tratamiento que se desea instalar.'
        );
    }

    $fechaAprobacion = isset($trabajo['fecha_completado'])
        ? trim((string)$trabajo['fecha_completado']) : '';
    $fechaEvolucion = trim((string)$evolucion['fecha']);
    if ($fechaAprobacion === '' || $fechaEvolucion === '' || strcmp($fechaEvolucion, $fechaAprobacion) < 0) {
        trabajoLaboratorioLanzar(
            'evolucion_instalacion_anterior_aprobacion',
            'Guarde una nueva evolucion clinica luego de aprobar el trabajo para registrar la instalacion.'
        );
    }

    $agendaConsulta = intval($evolucion['cod_agenda_consulta']);
    $agendaEvolucion = intval($evolucion['cod_agendaFK']);
    if ($codConsulta > 0 && $agendaConsulta > 0 && $agendaEvolucion !== $agendaConsulta) {
        trabajoLaboratorioLanzar(
            'evolucion_instalacion_consulta_invalida',
            'La evolucion no pertenece a la atencion clinica desde la que se registra la instalacion.'
        );
    }

    if (!$esExcepcionAuditor) {
        if (intval($evolucion['cod_usuraioFK']) !== intval($codUsuario)) {
            trabajoLaboratorioLanzar(
                'evolucion_instalacion_otro_profesional',
                'La evolucion de instalacion debe haber sido registrada por el profesional que confirma la accion.'
            );
        }
    }

    $tipoInstalacion = 'instalacion_registrada';
    $stmt = $mysqli->prepare(
        'SELECT id,id_trabajoFK FROM trabajo_laboratorio_evento '
        .'WHERE cod_evolucion_origenFK=? AND tipo_evento=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'origen_clinico_no_disponible',
            'No se pudo comprobar la trazabilidad de la evolucion clinica.'
        );
    }
    $stmt->bind_param('is', $codEvolucion, $tipoInstalacion);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'origen_clinico_no_disponible',
            'No se pudo comprobar la trazabilidad de la evolucion clinica.'
        );
    }
    $eventoExistente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($eventoExistente) {
        trabajoLaboratorioLanzar(
            'evolucion_instalacion_ya_utilizada',
            'Esta evolucion clinica ya fue utilizada para registrar una instalacion.'
        );
    }

    $origen['fecha_evolucion'] = $fechaEvolucion;
    $origen['cod_usuario_evolucion'] = intval($evolucion['cod_usuraioFK']);
    $origen['excepcion_auditor'] = $esExcepcionAuditor ? 1 : 0;
    return $origen;
}

function trabajoLaboratorioPuedeVer($mysqli, $codUsuario, $trabajo)
{
    if (!$trabajo) {
        return false;
    }
    if (trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        return true;
    }
    $asignado = intval($trabajo['cod_tecnico_usuarioFK']) === intval($codUsuario);
    $custodio = intval($trabajo['cod_custodio_actualFK']) === intval($codUsuario);
    if (trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false)) {
        return trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO')
            && ($asignado || $custodio);
    }
    if ($custodio || intval($trabajo['cod_usuarioFK_create']) === intval($codUsuario)) {
        return true;
    }
    return trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO')
        && trabajoLaboratorioUsuarioPuedeLocal($mysqli, $codUsuario, intval($trabajo['cod_localFK']));
}

function trabajoLaboratorioEstadoPermiteAccion($estado, $accion)
{
    $estado = (string)$estado;
    $mapa = array(
        'iniciarTransferencia' => array('pendiente_entrega_mecanico', 'ajuste_solicitado'),
        'confirmarRecepcion' => array('en_transferencia_mecanico'),
        'iniciarDevolucion' => array('en_laboratorio'),
        'confirmarDevolucion' => array('en_transferencia_clinica'),
        'solicitarAjuste' => array('pendiente_revision'),
        'aprobarTrabajo' => array('pendiente_revision'),
        'registrarInstalacion' => array('listo_instalacion')
    );
    if ($accion === 'agregarEvidencia' || $accion === 'agregarNota' || $accion === 'cancelarTrabajo') {
        return in_array(
            $estado,
            array(
                'pendiente_entrega_mecanico', 'en_transferencia_mecanico', 'en_laboratorio',
                'en_transferencia_clinica', 'pendiente_revision', 'ajuste_solicitado',
                'listo_instalacion'
            ),
            true
        );
    }
    return isset($mapa[$accion]) && in_array($estado, $mapa[$accion], true);
}

function trabajoLaboratorioResolverAcciones($estado, $contexto)
{
    $acciones = array(
        'iniciarTransferencia' => false,
        'confirmarRecepcion' => false,
        'agregarEvidencia' => false,
        'agregarNota' => false,
        'iniciarDevolucion' => false,
        'confirmarDevolucion' => false,
        'solicitarAjuste' => false,
        'aprobarTrabajo' => false,
        'registrarInstalacion' => false,
        'cancelarTrabajo' => false
    );
    $contexto = is_array($contexto) ? $contexto : array();
    $permisos = isset($contexto['permisos']) && is_array($contexto['permisos'])
        ? $contexto['permisos'] : array();
    $auditor = !empty($contexto['auditor']);
    $local = !empty($contexto['local']);
    $custodio = !empty($contexto['custodio']);
    $tecnico = !empty($contexto['tecnico']);
    $tecnicoFormal = !empty($contexto['tecnico_formal']) || $tecnico;
    $doctor = !empty($contexto['doctor']);
    $tiene = function ($codigo) use ($permisos) {
        return !empty($permisos[$codigo]);
    };

    $acciones['iniciarTransferencia'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'iniciarTransferencia')
        && ($custodio || $auditor || ($local && !$tecnicoFormal))
        && $tiene('ENTREGARTRABAJOLABORATORIO');
    $acciones['confirmarRecepcion'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'confirmarRecepcion')
        && ($tecnico || $auditor)
        && $tiene('RECIBIRTRABAJOLABORATORIO');
    $acciones['agregarEvidencia'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'agregarEvidencia')
        && ($custodio || $tecnico || ($auditor && $local))
        && $tiene('EVIDENCIATRABAJOLABORATORIO');
    $acciones['agregarNota'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'agregarNota')
        && ($custodio || $tecnico || ($auditor && $local))
        && $tiene('EVIDENCIATRABAJOLABORATORIO');
    $acciones['iniciarDevolucion'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'iniciarDevolucion')
        && ($tecnico || $auditor)
        && $tiene('ENTREGARTRABAJOLABORATORIO');
    $acciones['confirmarDevolucion'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'confirmarDevolucion')
        && (($local && !$tecnicoFormal) || $auditor)
        && $tiene('RECIBIRTRABAJOLABORATORIO');
    $acciones['solicitarAjuste'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'solicitarAjuste')
        && (($doctor && $local) || $auditor)
        && $tiene('AJUSTARTRABAJOLABORATORIO');
    $acciones['aprobarTrabajo'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'aprobarTrabajo')
        && (($doctor && $local) || $auditor)
        && $tiene('APROBARTRABAJOLABORATORIO');
    $acciones['registrarInstalacion'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'registrarInstalacion')
        && (($doctor && $local) || $auditor)
        && $tiene('INSTALARTRABAJOLABORATORIO');
    $acciones['cancelarTrabajo'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'cancelarTrabajo')
        && (($local && !$tecnicoFormal) || $auditor)
        && $tiene('CANCELARTRABAJOLABORATORIO');
    return $acciones;
}

function trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajo)
{
    $acciones = array(
        'iniciarTransferencia' => false,
        'confirmarRecepcion' => false,
        'agregarEvidencia' => false,
        'agregarNota' => false,
        'iniciarDevolucion' => false,
        'confirmarDevolucion' => false,
        'solicitarAjuste' => false,
        'aprobarTrabajo' => false,
        'registrarInstalacion' => false,
        'cancelarTrabajo' => false
    );
    if (!$trabajo || !trabajoLaboratorioPuedeVer($mysqli, $codUsuario, $trabajo)) {
        return $acciones;
    }
    $auditor = trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario);
    $local = trabajoLaboratorioUsuarioPuedeOperarLocal($mysqli, $codUsuario, intval($trabajo['cod_localFK']));
    $custodio = intval($trabajo['cod_custodio_actualFK']) === intval($codUsuario);
    $tecnicoFormal = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false) ? true : false;
    $tecnico = intval($trabajo['cod_tecnico_usuarioFK']) === intval($codUsuario)
        && $tecnicoFormal;
    $doctor = trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario);
    $codigosPermiso = array(
        'ENTREGARTRABAJOLABORATORIO',
        'RECIBIRTRABAJOLABORATORIO',
        'EVIDENCIATRABAJOLABORATORIO',
        'AJUSTARTRABAJOLABORATORIO',
        'APROBARTRABAJOLABORATORIO',
        'INSTALARTRABAJOLABORATORIO',
        'CANCELARTRABAJOLABORATORIO'
    );
    $permisos = array();
    foreach ($codigosPermiso as $codigoPermiso) {
        $permisos[$codigoPermiso] = trabajoLaboratorioTienePermiso($mysqli, $codUsuario, $codigoPermiso);
    }
    return trabajoLaboratorioResolverAcciones(
        isset($trabajo['estado_derivado']) ? $trabajo['estado_derivado'] : '',
        array(
            'auditor' => $auditor,
            'local' => $local,
            'custodio' => $custodio,
            'tecnico' => $tecnico,
            'tecnico_formal' => $tecnicoFormal,
            'doctor' => $doctor,
            'permisos' => $permisos
        )
    );
}

function trabajoLaboratorioObtenerContextoDetalle($mysqli, $codUsuario, $codDetalle)
{
    $bloqueos = array();
    if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
        return array(
            'detalle' => null,
            'ubicaciones' => array(),
            'trabajo_activo' => null,
            'tecnicos_disponibles' => array(),
            'puede_iniciar' => false,
            'bloqueos' => array(array(
                'codigo' => 'estructura_laboratorio_no_instalada',
                'mensaje' => 'El modulo de trabajos de laboratorio todavia no esta instalado.'
            )),
            'acciones_permitidas' => array()
        );
    }
    $fila = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, false);
    if (!$fila) {
        trabajoLaboratorioLanzar('detalle_no_encontrado', 'No se encontro el detalle de tratamiento.');
    }
    $config = $fila['configuracion_laboratorio'];
    $cantidad = floatval($fila['cantidad_detalle']);
    $cantidadValida = abs($cantidad - 1.0) < 0.000001;
    $ubicaciones = trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, $codDetalle);
    $trabajoActivo = trabajoLaboratorioObtenerTrabajoActivoDetalle($mysqli, $codDetalle);
    $trabajoActivoCompleto = null;
    if ($trabajoActivo) {
        $trabajoActivoCompleto = trabajoLaboratorioObtenerTrabajo($mysqli, intval($trabajoActivo['id']), false);
        if (!trabajoLaboratorioPuedeVer($mysqli, $codUsuario, $trabajoActivoCompleto)) {
            trabajoLaboratorioLanzar('trabajo_no_autorizado', 'El usuario no puede acceder al trabajo activo de este detalle.');
        }
    } else {
        if (!trabajoLaboratorioUsuarioPuedeOperarLocal($mysqli, $codUsuario, intval($fila['cod_local']))) {
            trabajoLaboratorioLanzar('local_no_autorizado', 'El usuario no puede consultar el local de esta venta.');
        }
        if ((!trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario)
                && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario))
            || !trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CREARTRABAJOLABORATORIO')) {
            trabajoLaboratorioLanzar('creacion_no_autorizada', 'El usuario no puede iniciar un trabajo para este detalle.');
        }
    }
    $tecnicos = trabajoLaboratorioTecnicosDisponibles($mysqli, true);
    $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($fila['cod_ventaFK']), false);
    $requiere = !empty($config['ok']) && !empty($config['requiere_laboratorio']);
    $modo = !empty($config['modo_individualizacion']) ? $config['modo_individualizacion'] : '';
    $detalleInactivo = in_array(
        trabajoLaboratorioNormalizarTexto($fila['estado_detalle']),
        array('eliminado', 'inactivo', 'anulado'),
        true
    ) || in_array(
        trabajoLaboratorioNormalizarTexto($fila['estado_venta']),
        array('inactivo', 'anulado'),
        true
    );

    if (!$requiere) {
        $bloqueos[] = array('codigo' => 'producto_no_requiere_laboratorio', 'mensaje' => 'El producto no esta configurado para laboratorio.');
    }
    if (!$cantidadValida) {
        $bloqueos[] = array('codigo' => 'cantidad_laboratorio_invalida', 'mensaje' => 'Cada detalle clinico de laboratorio debe tener cantidad 1.');
    }
    if ($detalleInactivo) {
        $bloqueos[] = array('codigo' => 'detalle_venta_inactivo', 'mensaje' => 'No se puede iniciar un trabajo sobre una venta o detalle inactivo.');
    }
    $bloqueoUbicacion = trabajoLaboratorioValidarUbicacionesModo($modo, $ubicaciones);
    if ($bloqueoUbicacion) {
        $bloqueos[] = $bloqueoUbicacion;
    }
    if ($trabajoActivo) {
        $bloqueos[] = array('codigo' => 'trabajo_activo_existente', 'mensaje' => 'Este detalle ya tiene un trabajo de laboratorio activo.');
    }
    if (!$hilo) {
        $bloqueos[] = array(
            'codigo' => 'hilo_unico_no_vinculado',
            'mensaje' => 'La venta no tiene vinculado su hilo maestro de seguimiento.',
            'accion_sugerida' => 'vincular_hilo_maestro'
        );
    }
    if (count($tecnicos) === 0) {
        $bloqueos[] = array(
            'codigo' => 'tecnicos_formales_no_disponibles',
            'mensaje' => 'No hay tecnicos activos con cuenta formal y permisos de acceso, recepcion y entrega.'
        );
    }
    if (!$trabajoActivo && !trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario) && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        $bloqueos[] = array('codigo' => 'rol_clinico_requerido', 'mensaje' => 'Solo un profesional autorizado puede iniciar el trabajo.');
    }
    if (!$trabajoActivo && !trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CREARTRABAJOLABORATORIO')) {
        $bloqueos[] = array('codigo' => 'permiso_creacion_requerido', 'mensaje' => 'El usuario no tiene permiso para iniciar trabajos de laboratorio.');
    }

    $puedeAsegurarHilo = !$trabajoActivo && !$hilo
        && trabajoLaboratorioUsuarioPuedeOperarLocal($mysqli, $codUsuario, intval($fila['cod_local']))
        && (trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario)
            || trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario))
        && trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CREARTRABAJOLABORATORIO');

    return array(
        'detalle' => array(
            'cod_detalle_venta' => intval($fila['cod_detalle']),
            'cantidad' => $cantidad,
            'estado_tratamiento' => trabajoLaboratorioTextoUtf8($fila['estado_tratamiento']),
            'progreso_porcentaje' => intval($fila['progreso_porcentaje']),
            'cod_venta' => intval($fila['cod_ventaFK']),
            'nro_venta' => trabajoLaboratorioNumeroVenta($fila),
            'cod_cliente' => intval($fila['cod_clienteFK']),
            'nombre_paciente' => trabajoLaboratorioTextoUtf8($fila['nombre_paciente']),
            'paciente' => trabajoLaboratorioTextoUtf8($fila['nombre_paciente']),
            'cod_local' => intval($fila['cod_local']),
            'nombre_local' => trabajoLaboratorioTextoUtf8($fila['nombre_local']),
            'cod_producto' => trabajoLaboratorioTextoUtf8($fila['cod_productoFK']),
            'nombre_producto' => trabajoLaboratorioTextoUtf8($fila['nombre_producto']),
            'cod_categoria' => intval($fila['cod_categoriaFK']),
            'nombre_categoria' => trabajoLaboratorioTextoUtf8($fila['nombre_categoria']),
            'requiere_laboratorio' => $requiere,
            'origen_requiere_laboratorio' => isset($config['origen_requiere_laboratorio']) ? $config['origen_requiere_laboratorio'] : null,
            'modo_individualizacion' => $modo
        ),
        'ubicaciones' => $ubicaciones,
        'trabajo_activo' => $trabajoActivo,
        'tecnicos_disponibles' => $tecnicos,
        'puede_iniciar' => count($bloqueos) === 0,
        'puede_asegurar_hilo' => $puedeAsegurarHilo,
        'bloqueos' => $bloqueos,
        'acciones_permitidas' => $trabajoActivo
            ? trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajoActivoCompleto)
            : array('iniciarTrabajo' => count($bloqueos) === 0)
    );
}

/**
 * Prepara el Hilo maestro de una venta historica antes de iniciar el trabajo.
 * Reutiliza el nucleo existente de seguimiento por cedula y verifica al final
 * que la venta concreta haya quedado vinculada. Repetir la accion no duplica
 * el Hilo ni la relacion de la venta.
 */
function trabajoLaboratorioAsegurarHiloDetalle($mysqli, $codUsuario, $codDetalle)
{
    $codDetalle = intval($codDetalle);
    $fila = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, false);
    if (!$fila) {
        trabajoLaboratorioLanzar('detalle_no_encontrado', 'No se encontro el detalle de tratamiento.');
    }
    if (!trabajoLaboratorioUsuarioPuedeOperarLocal($mysqli, $codUsuario, intval($fila['cod_local']))) {
        trabajoLaboratorioLanzar('local_no_autorizado', 'El usuario no puede operar sobre el local de esta venta.');
    }
    if ((!trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario)
            && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario))
        || !trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CREARTRABAJOLABORATORIO')) {
        trabajoLaboratorioLanzar('creacion_no_autorizada', 'El usuario no puede preparar el seguimiento de este trabajo.');
    }
    if (in_array(trabajoLaboratorioNormalizarTexto($fila['estado_detalle']), array('eliminado', 'inactivo', 'anulado'), true)
        || in_array(trabajoLaboratorioNormalizarTexto($fila['estado_venta']), array('inactivo', 'anulado'), true)) {
        trabajoLaboratorioLanzar('detalle_venta_inactivo', 'No se puede preparar un trabajo sobre una venta o detalle inactivo.');
    }

    $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($fila['cod_ventaFK']), false);
    if (!$hilo) {
        if (!function_exists('asegurarEstructuraSeguimientoPacienteInterConsulta')
            || !function_exists('seguimientoPacienteAsegurarHiloPorVentaConConexion')) {
            trabajoLaboratorioLanzar('hilo_maestro_no_disponible', 'No esta disponible el proceso seguro para preparar el Hilo maestro.');
        }
        /* La verificacion de estructura se ejecuta antes de la transaccion para
           evitar que un eventual DDL produzca una confirmacion implicita. */
        if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
            trabajoLaboratorioLanzar('hilo_maestro_no_disponible', 'No esta disponible la estructura del Hilo maestro.');
        }
        if (!$mysqli->begin_transaction()) {
            trabajoLaboratorioLanzar(
                'transaccion_no_iniciada',
                'No se pudo iniciar la vinculacion segura del Hilo maestro.'
            );
        }
        try {
            $resultado = seguimientoPacienteAsegurarHiloPorVentaConConexion(
                $mysqli,
                intval($fila['cod_ventaFK']),
                intval($codUsuario),
                'trabajo_laboratorio'
            );
            if (empty($resultado['ok'])) {
                trabajoLaboratorioLanzar(
                    'hilo_maestro_no_preparado',
                    'No se pudo preparar el Hilo maestro de esta venta historica.'
                );
            }
            $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($fila['cod_ventaFK']), true);
            if (!$hilo) {
                trabajoLaboratorioLanzar(
                    'hilo_maestro_no_vinculado',
                    'El Hilo maestro no quedo vinculado a la venta solicitada.'
                );
            }
            if (!$mysqli->commit()) {
                trabajoLaboratorioLanzar('hilo_maestro_no_confirmado', 'No se pudo confirmar la preparacion del Hilo maestro.');
            }
        } catch (Exception $e) {
            $mysqli->rollback();
            throw $e;
        } catch (Throwable $e) {
            $mysqli->rollback();
            throw $e;
        }
    }

    $contexto = trabajoLaboratorioObtenerContextoDetalle($mysqli, $codUsuario, $codDetalle);
    return trabajoLaboratorioRespuesta(
        true,
        'hilo_maestro_preparado',
        'El Hilo maestro quedo vinculado. Ya puede iniciar el trabajo.',
        array('contexto' => $contexto, 'cod_interConsulta' => intval($hilo['cod_interConsultaFK'])),
        null
    );
}

function trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, $bloquear = false)
{
    $idTrabajo = intval($idTrabajo);
    if ($idTrabajo <= 0 || !trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio')) {
        return null;
    }
    $sql = 'SELECT tl.*,p.nombre_producto,l.Nombre AS nombre_local,pc.nombre_persona AS nombre_paciente,'
        .'pt.nombre_persona AS nombre_tecnico,pcu.nombre_persona AS nombre_custodio,'
        .'pdoc.nombre_persona AS nombre_doctor,'
        .'(SELECT MAX(ev.fecha_servidor) FROM trabajo_laboratorio_evento ev '
        .'WHERE ev.id_trabajoFK=tl.id AND ev.cod_custodio_nuevoFK=tl.cod_custodio_actualFK) AS fecha_custodio_actual,'
        .'(SELECT mm.id FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS id_media_principal,'
        .'(SELECT mm.miniatura_relativa FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS miniatura_relativa_principal,'
        .'(SELECT mm.ruta_relativa FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS ruta_relativa_principal,'
        .'(SELECT mm.mime FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS mime_media_principal,'
        .'tt.descripcion AS tipo_trabajo '
        .'FROM trabajo_laboratorio tl '
        .'INNER JOIN producto p ON p.cod_producto=tl.cod_productoFK '
        .'LEFT JOIN local l ON l.cod_local=tl.cod_localFK '
        .'LEFT JOIN persona pc ON pc.cod_persona=tl.cod_clienteFK '
        .'LEFT JOIN persona pt ON pt.cod_persona=tl.cod_tecnico_usuarioFK '
        .'LEFT JOIN persona pcu ON pcu.cod_persona=tl.cod_custodio_actualFK '
        .'LEFT JOIN persona pdoc ON pdoc.cod_persona=tl.cod_usuarioFK_create '
        .'LEFT JOIN tipo_trabajo_mecanico_dental tt ON tt.cod_tipo_trabajo_mecanico_dental=tl.cod_tipo_trabajoFK '
        .'WHERE tl.id=? LIMIT 1';
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('consulta_trabajo_no_disponible', 'No se pudo consultar el trabajo de laboratorio.');
    }
    $stmt->bind_param('i', $idTrabajo);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('consulta_trabajo_no_disponible', 'No se pudo consultar el trabajo de laboratorio.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ?: null;
}

function trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajo)
{
    if (!$trabajo) {
        trabajoLaboratorioLanzar('trabajo_no_encontrado', 'No se encontro el trabajo de laboratorio.');
    }
    if (!trabajoLaboratorioPuedeVer($mysqli, $codUsuario, $trabajo)) {
        trabajoLaboratorioLanzar('trabajo_no_autorizado', 'El usuario no puede acceder a este trabajo.');
    }
}

function trabajoLaboratorioAccionNaturalUsuario($mysqli, $codUsuario, $trabajo, $accion)
{
    $localPropio = trabajoLaboratorioUsuarioPerteneceLocal(
        $mysqli,
        $codUsuario,
        intval($trabajo['cod_localFK'])
    );
    $custodio = intval($trabajo['cod_custodio_actualFK']) === intval($codUsuario);
    $tecnicoFormal = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false);
    $tecnico = $tecnicoFormal
        && intval($trabajo['cod_tecnico_usuarioFK']) === intval($codUsuario);
    $doctor = trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario);
    if ($accion === 'iniciarTrabajo') {
        return $doctor && $localPropio;
    }
    if ($accion === 'iniciarTransferencia') {
        return $custodio || ($localPropio && !$tecnicoFormal);
    }
    if ($accion === 'confirmarRecepcion' || $accion === 'iniciarDevolucion') {
        return $tecnico;
    }
    if ($accion === 'agregarEvidencia' || $accion === 'agregarNota') {
        return $custodio || $tecnico;
    }
    if ($accion === 'confirmarDevolucion') {
        return $localPropio && !$tecnicoFormal;
    }
    if ($accion === 'solicitarAjuste' || $accion === 'aprobarTrabajo'
        || $accion === 'registrarInstalacion') {
        return $doctor && $localPropio;
    }
    if ($accion === 'cancelarTrabajo') {
        return $localPropio && !$tecnicoFormal;
    }
    return false;
}

function trabajoLaboratorioExigirMotivoExcepcionAuditor($mysqli, $codUsuario, $trabajo, $accion, $entrada)
{
    if (!trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        return '';
    }
    $motivo = isset($entrada['motivo_excepcion'])
        ? trabajoLaboratorioTextoEntrada($entrada['motivo_excepcion'], 750) : '';
    if (trabajoLaboratorioAccionNaturalUsuario($mysqli, $codUsuario, $trabajo, $accion)
        && $motivo === '') {
        return '';
    }
    if (strlen($motivo) < 5) {
        trabajoLaboratorioLanzar(
            'motivo_excepcion_auditor_requerido',
            'La intervencion excepcional del auditor requiere una justificacion de al menos cinco caracteres.'
        );
    }
    return $motivo;
}

function trabajoLaboratorioExigirAccion($mysqli, $codUsuario, $trabajo, $accion, $entrada = array())
{
    trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajo);
    $acciones = trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajo);
    if (empty($acciones[$accion])) {
        trabajoLaboratorioLanzar(
            'accion_no_permitida',
            'La accion no esta permitida para el estado, asignacion o permisos actuales.',
            array('estado_derivado' => $trabajo['estado_derivado'], 'accion' => $accion)
        );
    }
    return trabajoLaboratorioExigirMotivoExcepcionAuditor(
        $mysqli,
        $codUsuario,
        $trabajo,
        $accion,
        is_array($entrada) ? $entrada : array()
    );
}

function trabajoLaboratorioMetadataExcepcionAuditor($metadata, $motivo)
{
    $metadata = is_array($metadata) ? $metadata : array();
    $motivo = trabajoLaboratorioTextoEntrada($motivo, 750);
    if ($motivo !== '') {
        $metadata['excepcion_auditor'] = 1;
        $metadata['motivo_excepcion_auditor'] = $motivo;
    }
    return $metadata;
}

function trabajoLaboratorioExigirVersion($trabajo, $entrada)
{
    $version = isset($entrada['version_esperada']) ? trabajoLaboratorioEntero($entrada['version_esperada'])
        : (isset($entrada['version']) ? trabajoLaboratorioEntero($entrada['version']) : 0);
    if ($version <= 0) {
        trabajoLaboratorioLanzar('version_requerida', 'Debe indicar la version actual del trabajo.');
    }
    if ($version !== intval($trabajo['version'])) {
        trabajoLaboratorioLanzar(
            'version_desactualizada',
            'El trabajo cambio desde la ultima consulta. Actualice la vista antes de continuar.',
            array('version_actual' => intval($trabajo['version']))
        );
    }
    return $version;
}

function trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo)
{
    $idTrabajo = intval($trabajo['id']);
    $numero = intval($trabajo['ciclo_actual']);
    $stmt = $mysqli->prepare(
        'SELECT * FROM trabajo_laboratorio_ciclo WHERE id_trabajoFK=? AND numero_ciclo=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('ciclo_no_disponible', 'No se pudo consultar el ciclo del trabajo.');
    }
    $stmt->bind_param('ii', $idTrabajo, $numero);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar('ciclo_inconsistente', 'El trabajo no tiene un ciclo activo valido.');
    }
    return $fila;
}

function trabajoLaboratorioCatalogos($mysqli, $codUsuario = 0)
{
    if (!trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
        && !trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO')
        && !trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CREARTRABAJOLABORATORIO')) {
        trabajoLaboratorioLanzar('catalogos_no_autorizados', 'El usuario no puede consultar los catalogos de laboratorio.');
    }
    $tipos = array();
    $stmt = $mysqli->prepare(
        "SELECT cod_tipo_trabajo_mecanico_dental,descripcion FROM tipo_trabajo_mecanico_dental "
        ."WHERE estado='activo' ORDER BY descripcion ASC"
    );
    if ($stmt && $stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $tipos[] = array(
                'id' => intval($fila['cod_tipo_trabajo_mecanico_dental']),
                'descripcion' => trabajoLaboratorioTextoUtf8($fila['descripcion'])
            );
        }
        $stmt->close();
    }
    $tecnicos = trabajoLaboratorioTecnicosDisponibles($mysqli);
    $locales = array();
    $doctores = array();
    $custodios = array();
    $productos = array();
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    $esTecnicoCatalogo = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false) ? true : false;
    $todosLocales = (!$esTecnicoCatalogo || trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario))
        && trabajoLaboratorioUsuarioPuedeTodosLocales($mysqli, $codUsuario);
    $localUsuario = $usuario ? intval($usuario['cod_localFK']) : 0;
    $sqlLocal = "SELECT cod_local,Nombre FROM local WHERE estado='Activo'";
    if (!$todosLocales) {
        $sqlLocal .= ' AND cod_local=?';
    }
    $sqlLocal .= ' ORDER BY Nombre ASC';
    $stmt = $mysqli->prepare($sqlLocal);
    if ($stmt) {
        if (!$todosLocales) {
            $stmt->bind_param('i', $localUsuario);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $locales[] = array('cod_local' => intval($fila['cod_local']), 'nombre' => trabajoLaboratorioTextoUtf8($fila['Nombre']));
        }
        $stmt->close();
    }
    $sqlUsuarios = "SELECT u.cod_usuario,u.tipo,u.url,p.nombre_persona FROM usuario u "
        ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario WHERE u.estado='Activo'";
    if (!$todosLocales) {
        $sqlUsuarios .= ' AND u.cod_localFK=?';
    }
    $sqlUsuarios .= ' ORDER BY p.nombre_persona ASC';
    $stmt = $mysqli->prepare($sqlUsuarios);
    if ($stmt) {
        if (!$todosLocales) {
            $stmt->bind_param('i', $localUsuario);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $item = array(
                'cod_usuario' => intval($fila['cod_usuario']),
                'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_persona']),
                'rol' => trabajoLaboratorioTextoUtf8($fila['tipo']),
                'avatar' => trabajoLaboratorioTextoUtf8($fila['url'])
            );
            $custodios[] = $item;
            if (trabajoLaboratorioNormalizarTexto($fila['tipo']) === 'doctor') {
                $doctores[] = $item;
            }
        }
        $stmt->close();
    }
    if (trabajoLaboratorioConfiguracionDisponible($mysqli)) {
        $sqlProductos = 'SELECT p.cod_producto,p.nombre_producto FROM producto p '
            .'LEFT JOIN categoria c ON c.cod_categoria=p.cod_categoriaFK '
            ."WHERE p.estado='Activo' AND COALESCE(p.requiere_laboratorio,c.requiere_laboratorio,0)=1";
        if (!$todosLocales) {
            $sqlProductos .= ' AND p.cod_localFK=?';
        }
        $sqlProductos .= ' ORDER BY p.nombre_producto ASC';
        $stmt = $mysqli->prepare($sqlProductos);
        if ($stmt) {
            if (!$todosLocales) {
                $stmt->bind_param('i', $localUsuario);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = array(
                    'cod_producto' => trabajoLaboratorioTextoUtf8($fila['cod_producto']),
                    'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_producto'])
                );
            }
            $stmt->close();
        }
    }
    $situaciones = array(
        array('codigo' => 'pendiente_entrega_mecanico', 'nombre' => 'Pendiente de entrega'),
        array('codigo' => 'en_transferencia_mecanico', 'nombre' => 'En traslado al laboratorio'),
        array('codigo' => 'en_laboratorio', 'nombre' => 'En laboratorio'),
        array('codigo' => 'en_transferencia_clinica', 'nombre' => 'En retorno a clinica'),
        array('codigo' => 'pendiente_revision', 'nombre' => 'Pendiente de revision'),
        array('codigo' => 'ajuste_solicitado', 'nombre' => 'Ajuste solicitado'),
        array('codigo' => 'listo_instalacion', 'nombre' => 'Listo para instalar'),
        array('codigo' => 'instalado', 'nombre' => 'Instalado'),
        array('codigo' => 'cancelado', 'nombre' => 'Cancelado'),
        array('codigo' => 'atrasado', 'nombre' => 'Fuera del plazo')
    );
    return array(
        'tipos_trabajo' => $tipos,
        'tecnicos_disponibles' => $tecnicos,
        'tecnicos' => $tecnicos,
        'mecanicos' => $tecnicos,
        'locales' => $locales,
        'doctores' => $doctores,
        'custodios' => $custodios,
        'productos' => $productos,
        'situaciones' => $situaciones,
        'modos_individualizacion' => trabajoLaboratorioModosIndividualizacion(),
        'motivos_ajuste' => array(
            'adaptacion', 'oclusion', 'color', 'forma', 'medida_tamano', 'terminacion',
            'fractura_dano', 'instruccion_clinica_incompleta', 'otro'
        ),
        'estados' => array(
            'pendiente_entrega_mecanico', 'en_transferencia_mecanico', 'en_laboratorio',
            'en_transferencia_clinica', 'pendiente_revision', 'ajuste_solicitado',
            'listo_instalacion', 'instalado', 'cancelado'
        )
    );
}

function trabajoLaboratorioDestinatariosClinica($mysqli, $codLocal)
{
    $codLocal = intval($codLocal);
    if ($codLocal <= 0) {
        return array();
    }
    $stmt = $mysqli->prepare(
        "SELECT DISTINCT u.cod_usuario,u.tipo,u.url,p.nombre_persona FROM usuario u "
        ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."INNER JOIN accesosuser au ON au.usuarios_idusario=u.cod_usuario AND au.accion='SI' "
        ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
        ."AND la.codigo='RECIBIRTRABAJOLABORATORIO' "
        ."WHERE u.estado='Activo' AND u.cod_localFK=? "
        ."AND EXISTS (SELECT 1 FROM accesosuser auv "
        ."INNER JOIN listadodeacceso lav ON lav.idlistadodeacceso=auv.idlistadodeaccesoFK "
        ."WHERE auv.usuarios_idusario=u.cod_usuario AND auv.accion='SI' "
        ."AND lav.codigo='VERTRABAJOSLABORATORIO') "
        ."AND NOT EXISTS (SELECT 1 FROM mecanico_dental md "
        ."WHERE md.cod_usuarioFK=u.cod_usuario AND md.estado='activo') "
        ."ORDER BY p.nombre_persona ASC"
    );
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('i', $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $salida = array();
    while ($fila = $resultado->fetch_assoc()) {
        $salida[] = array(
            'cod_usuario' => intval($fila['cod_usuario']),
            'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_persona']),
            'rol' => trabajoLaboratorioTextoUtf8($fila['tipo']),
            'avatar' => trabajoLaboratorioTextoUtf8($fila['url'])
        );
    }
    $stmt->close();
    return $salida;
}

function trabajoLaboratorioDestinatariosPermitidos($mysqli, $trabajo)
{
    $tecnico = array();
    $tecnicoFormal = trabajoLaboratorioObtenerTecnicoFormal(
        $mysqli, intval($trabajo['cod_tecnico_usuarioFK']), false
    );
    if ($tecnicoFormal) {
        $tecnico[] = array(
            'cod_usuario' => intval($tecnicoFormal['cod_usuarioFK']),
            'cod_mecanico_dental' => intval($tecnicoFormal['cod_mecanico_dental']),
            'nombre' => trabajoLaboratorioTextoUtf8($tecnicoFormal['nombre_persona']),
            'rol' => 'Mecanico dental',
            'avatar' => trabajoLaboratorioTextoUtf8($tecnicoFormal['url'])
        );
    }
    $clinica = trabajoLaboratorioDestinatariosClinica($mysqli, intval($trabajo['cod_localFK']));
    $estado = (string)$trabajo['estado_derivado'];
    $actuales = in_array($estado, array('en_laboratorio', 'en_transferencia_clinica'), true)
        ? $clinica : $tecnico;
    return array(
        'actuales' => $actuales,
        'ida_laboratorio' => $tecnico,
        'retorno_clinica' => $clinica
    );
}

function trabajoLaboratorioOrdenarPayload($valor)
{
    if (!is_array($valor)) {
        return $valor;
    }
    $esLista = array_keys($valor) === range(0, count($valor) - 1);
    if (!$esLista) {
        ksort($valor);
    }
    foreach ($valor as $clave => $item) {
        $valor[$clave] = trabajoLaboratorioOrdenarPayload($item);
    }
    return $valor;
}

function trabajoLaboratorioHashPayload($entrada)
{
    $payload = is_array($entrada) ? $entrada : array();
    foreach (array('useru', 'passu', 'navegador', 'accion', 'funt', 'clave_idempotencia', 'idempotency_key') as $clave) {
        unset($payload[$clave]);
    }
    $payload = trabajoLaboratorioOrdenarPayload($payload);
    return hash('sha256', json_encode($payload));
}

function trabajoLaboratorioPrepararIdempotencia($mysqli, $codUsuario, $accion, $clave, $payloadHash)
{
    $estado = 'pendiente';
    $stmt = $mysqli->prepare(
        'INSERT INTO trabajo_laboratorio_idempotencia '
        .'(cod_usuarioFK,accion,clave,payload_hash,estado,fecha_creacion) VALUES (?,?,?,?,?,NOW())'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('idempotencia_no_disponible', 'No se pudo preparar la operacion segura.');
    }
    $stmt->bind_param('issss', $codUsuario, $accion, $clave, $payloadHash, $estado);
    if ($stmt->execute()) {
        $id = intval($stmt->insert_id);
        $stmt->close();
        return array('id' => $id, 'repetida' => false, 'respuesta' => null);
    }
    $errno = intval($stmt->errno);
    $stmt->close();
    if ($errno !== 1062) {
        trabajoLaboratorioLanzar('idempotencia_no_disponible', 'No se pudo preparar la operacion segura.');
    }
    $stmt = $mysqli->prepare(
        'SELECT id,id_trabajoFK,payload_hash,estado,respuesta_json FROM trabajo_laboratorio_idempotencia '
        .'WHERE cod_usuarioFK=? AND accion=? AND clave=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('idempotencia_no_disponible', 'No se pudo comprobar la operacion anterior.');
    }
    $stmt->bind_param('iss', $codUsuario, $accion, $clave);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila || !hash_equals((string)$fila['payload_hash'], (string)$payloadHash)) {
        trabajoLaboratorioLanzar(
            'clave_idempotencia_reutilizada',
            'La clave de idempotencia ya fue utilizada con otros datos.'
        );
    }
    if ($fila['estado'] !== 'completada' || trim((string)$fila['respuesta_json']) === '') {
        trabajoLaboratorioLanzar('operacion_en_proceso', 'La misma operacion se encuentra en proceso.');
    }
    $respuesta = json_decode($fila['respuesta_json'], true);
    if (!is_array($respuesta)) {
        trabajoLaboratorioLanzar('respuesta_idempotente_invalida', 'No se pudo recuperar el resultado anterior.');
    }
    return array(
        'id' => intval($fila['id']),
        'id_trabajo' => intval($fila['id_trabajoFK']),
        'repetida' => true,
        'respuesta' => $respuesta
    );
}

function trabajoLaboratorioRespuestaIdempotenciaProtegida($valor)
{
    if (!is_array($valor)) {
        return is_string($valor) && strpos($valor, 'data:image/') === 0 ? null : $valor;
    }
    $salida = array();
    $clavesMedia = array(
        'miniatura_url', 'url_visualizacion', 'url_original_autorizada',
        'data_base64', 'base64', 'imagen_principal', 'evidencia_principal', 'foto'
    );
    foreach ($valor as $clave => $item) {
        if (in_array((string)$clave, $clavesMedia, true)
            || (is_string($item) && strpos($item, 'data:image/') === 0)) {
            continue;
        }
        $salida[$clave] = trabajoLaboratorioRespuestaIdempotenciaProtegida($item);
    }
    return $salida;
}

function trabajoLaboratorioCompletarIdempotencia($mysqli, $idIdempotencia, $idTrabajo, $respuesta)
{
    $json = json_encode(trabajoLaboratorioUtf8(
        trabajoLaboratorioRespuestaIdempotenciaProtegida($respuesta)
    ));
    $estado = 'completada';
    $stmt = $mysqli->prepare(
        'UPDATE trabajo_laboratorio_idempotencia SET id_trabajoFK=?,estado=?,respuesta_json=?,fecha_completado=NOW() '
        .'WHERE id=? AND estado=\'pendiente\' LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('idempotencia_no_completada', 'No se pudo completar la operacion segura.');
    }
    $idTrabajo = intval($idTrabajo);
    $idIdempotencia = intval($idIdempotencia);
    $stmt->bind_param('issi', $idTrabajo, $estado, $json, $idIdempotencia);
    $ok = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    if (!$ok) {
        trabajoLaboratorioLanzar('idempotencia_no_completada', 'No se pudo completar la operacion segura.');
    }
}

function trabajoLaboratorioEjecutarComando($mysqli, $codUsuario, $accion, $entrada, $callback)
{
    $clave = isset($entrada['clave_idempotencia']) ? $entrada['clave_idempotencia']
        : (isset($entrada['idempotency_key']) ? $entrada['idempotency_key'] : '');
    $clave = trabajoLaboratorioNormalizarClave($clave);
    $payloadHash = trabajoLaboratorioHashPayload($entrada);
    $contexto = new stdClass();
    $contexto->archivos_creados = array();
    $commitIniciado = false;
    if (!$mysqli->begin_transaction()) {
        trabajoLaboratorioLanzar(
            'transaccion_no_iniciada',
            'No se pudo iniciar la operacion segura de laboratorio.'
        );
    }
    try {
        $idem = trabajoLaboratorioPrepararIdempotencia($mysqli, intval($codUsuario), $accion, $clave, $payloadHash);
        if ($idem['repetida']) {
            $respuestaRepetida = $idem['respuesta'];
            if (!empty($idem['id_trabajo']) && trabajoLaboratorioEstructuraDisponible($mysqli)) {
                $respuestaRepetida = trabajoLaboratorioRespuestaActualizada(
                    $mysqli,
                    $codUsuario,
                    intval($idem['id_trabajo']),
                    isset($idem['respuesta']['codigo']) ? $idem['respuesta']['codigo'] : 'operacion_repetida',
                    isset($idem['respuesta']['mensaje']) ? $idem['respuesta']['mensaje'] : 'La operacion ya habia sido registrada.'
                );
            }
            $commitIniciado = true;
            if (!$mysqli->commit()) {
                trabajoLaboratorioLanzar('operacion_no_confirmada', 'No se pudo confirmar la operacion idempotente.');
            }
            return $respuestaRepetida;
        }
        $resultado = call_user_func($callback, intval($idem['id']), $contexto);
        if (!is_array($resultado) || !isset($resultado['respuesta']) || !isset($resultado['id_trabajo'])) {
            trabajoLaboratorioLanzar('resultado_comando_invalido', 'La operacion no produjo un resultado valido.');
        }
        trabajoLaboratorioCompletarIdempotencia(
            $mysqli,
            intval($idem['id']),
            intval($resultado['id_trabajo']),
            $resultado['respuesta']
        );
        $commitIniciado = true;
        if (!$mysqli->commit()) {
            trabajoLaboratorioLanzar('operacion_no_confirmada', 'No se pudo confirmar la operacion de laboratorio.');
        }
        return $resultado['respuesta'];
    } catch (Exception $e) {
        $mysqli->rollback();
        if (!$commitIniciado) {
            foreach ($contexto->archivos_creados as $archivo) {
                if (is_string($archivo) && is_file($archivo)) {
                    @unlink($archivo);
                }
            }
        }
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        if (!$commitIniciado) {
            foreach ($contexto->archivos_creados as $archivo) {
                if (is_string($archivo) && is_file($archivo)) {
                    @unlink($archivo);
                }
            }
        }
        throw $e;
    }
}

function trabajoLaboratorioDirectorioMedia()
{
    $configurado = getenv('TELAR_LAB_MEDIA_DIR');
    if ($configurado !== false && trim($configurado) !== ''
        && !preg_match('#^(?:[A-Za-z]:[\\/]|\\\\|/)#', trim($configurado))) {
        trabajoLaboratorioLanzar('directorio_media_inseguro', 'El almacenamiento protegido debe usar una ruta absoluta fuera del directorio publico.');
    }
    $base = $configurado !== false && trim($configurado) !== ''
        ? trim($configurado)
        : dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR
            .'GoodVentaAsisCap'.DIRECTORY_SEPARATOR.'trabajos_laboratorio';
    $proyecto = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__));
    $raizPublica = str_replace('\\', '/', realpath(dirname(dirname(__DIR__))) ?: dirname(dirname(__DIR__)));
    $normalizado = str_replace('\\', '/', $base);
    if (stripos(rtrim($normalizado, '/').'/', rtrim($proyecto, '/').'/') === 0
        || stripos(rtrim($normalizado, '/').'/', rtrim($raizPublica, '/').'/') === 0) {
        trabajoLaboratorioLanzar('directorio_media_inseguro', 'El almacenamiento de evidencias debe estar fuera del directorio publico.');
    }
    if (!is_dir($base) && !@mkdir($base, 0770, true)) {
        trabajoLaboratorioLanzar('directorio_media_no_disponible', 'No se pudo habilitar el almacenamiento protegido.');
    }
    $real = realpath($base);
    if ($real === false) {
        trabajoLaboratorioLanzar('directorio_media_no_disponible', 'No se pudo resolver el almacenamiento protegido.');
    }
    $realNormalizado = rtrim(str_replace('\\', '/', $real), '/').'/';
    if (stripos($realNormalizado, rtrim($proyecto, '/').'/') === 0
        || stripos($realNormalizado, rtrim($raizPublica, '/').'/') === 0) {
        trabajoLaboratorioLanzar('directorio_media_inseguro', 'El almacenamiento de evidencias debe estar fuera del directorio publico.');
    }
    return $real;
}

function trabajoLaboratorioNormalizarEvidencias($entrada, $clave = 'evidencias')
{
    $valor = isset($entrada[$clave]) ? $entrada[$clave] : array();
    $datos = trabajoLaboratorioDecodificarJson($valor, array());
    if (isset($datos['data_base64'])) {
        $datos = array($datos);
    }
    return is_array($datos) ? $datos : array();
}

function trabajoLaboratorioBytesConfiguracion($valor)
{
    $valor = trim((string)$valor);
    if ($valor === '' || $valor === '-1') {
        return -1;
    }
    $unidad = strtolower(substr($valor, -1));
    $numero = floatval($valor);
    if ($unidad === 'g') {
        $numero *= 1024 * 1024 * 1024;
    } elseif ($unidad === 'm') {
        $numero *= 1024 * 1024;
    } elseif ($unidad === 'k') {
        $numero *= 1024;
    }
    return intval($numero);
}

function trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto)
{
    if (!is_array($evidencia)) {
        trabajoLaboratorioLanzar('evidencia_invalida', 'La evidencia enviada no es valida.');
    }
    $base64 = isset($evidencia['data_base64']) ? trim((string)$evidencia['data_base64']) : '';
    if ($base64 === '' || strlen($base64) > 15000000) {
        trabajoLaboratorioLanzar('evidencia_invalida', 'La imagen esta vacia o supera el limite permitido.');
    }
    if (preg_match('#^data:([^;]+);base64,(.*)$#s', $base64, $coincidencia)) {
        $base64 = $coincidencia[2];
    }
    $binario = base64_decode(preg_replace('/\s+/', '', $base64), true);
    if ($binario === false || strlen($binario) === 0 || strlen($binario) > 10485760) {
        trabajoLaboratorioLanzar('evidencia_invalida', 'La imagen no es valida o supera 10 MB.');
    }
    if (!class_exists('finfo')) {
        trabajoLaboratorioLanzar('validacion_media_no_disponible', 'El servidor no puede validar la imagen.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($binario);
    $extensiones = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
    if (!isset($extensiones[$mime])) {
        trabajoLaboratorioLanzar('formato_media_no_permitido', 'Solo se admiten imagenes JPG, PNG o WEBP.');
    }
    if (!function_exists('getimagesizefromstring')) {
        trabajoLaboratorioLanzar('validacion_media_no_disponible', 'El servidor no puede validar las dimensiones de la imagen.');
    }
    $dimensiones = @getimagesizefromstring($binario);
    $tiposImagen = array(IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png');
    if (defined('IMAGETYPE_WEBP')) {
        $tiposImagen[IMAGETYPE_WEBP] = 'image/webp';
    }
    $anchoValidado = is_array($dimensiones) && isset($dimensiones[0]) ? intval($dimensiones[0]) : 0;
    $altoValidado = is_array($dimensiones) && isset($dimensiones[1]) ? intval($dimensiones[1]) : 0;
    $tipoValidado = is_array($dimensiones) && isset($dimensiones[2]) ? intval($dimensiones[2]) : 0;
    $pixeles = $anchoValidado * $altoValidado;
    if ($anchoValidado <= 0 || $altoValidado <= 0 || $anchoValidado > 12000 || $altoValidado > 12000
        || $pixeles > 20000000
        || !isset($tiposImagen[$tipoValidado]) || $tiposImagen[$tipoValidado] !== $mime) {
        trabajoLaboratorioLanzar('imagen_media_invalida', 'La imagen esta danada o sus dimensiones no son seguras.');
    }
    if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
        $limiteMemoria = trabajoLaboratorioBytesConfiguracion(ini_get('memory_limit'));
        $memoriaEstimada = memory_get_usage(true) + intval($pixeles * 6)
            + strlen($binario) + 8388608;
        if ($limiteMemoria > 0 && $memoriaEstimada > intval($limiteMemoria * 0.90)) {
            trabajoLaboratorioLanzar(
                'imagen_media_demasiado_grande',
                'La resolucion de la imagen supera la capacidad segura del servidor. Reduzca el tamano y vuelva a intentar.'
            );
        }
    }
    $base = trabajoLaboratorioDirectorioMedia();
    $relDir = date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.'trabajo_'.intval($idTrabajo);
    $directorio = $base.DIRECTORY_SEPARATOR.$relDir;
    if (!is_dir($directorio) && !@mkdir($directorio, 0770, true)) {
        trabajoLaboratorioLanzar('directorio_media_no_disponible', 'No se pudo preparar el almacenamiento de la evidencia.');
    }
    $nombreSeguro = bin2hex(random_bytes(24)).'.'.$extensiones[$mime];
    $absoluta = $directorio.DIRECTORY_SEPARATOR.$nombreSeguro;
    if (@file_put_contents($absoluta, $binario, LOCK_EX) !== strlen($binario)) {
        @unlink($absoluta);
        trabajoLaboratorioLanzar('media_no_guardada', 'No se pudo guardar la evidencia.');
    }
    $contexto->archivos_creados[] = $absoluta;
    $miniaturaRelativa = null;
    if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
        $origen = @imagecreatefromstring($binario);
        if ($origen !== false) {
            $ancho = imagesx($origen);
            $alto = imagesy($origen);
            $maximo = 480;
            $escala = max($ancho, $alto) > $maximo ? $maximo / max($ancho, $alto) : 1;
            $nuevoAncho = max(1, intval(round($ancho * $escala)));
            $nuevoAlto = max(1, intval(round($alto * $escala)));
            $miniatura = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            if ($miniatura !== false) {
                if ($mime === 'image/png' || $mime === 'image/webp') {
                    imagealphablending($miniatura, false);
                    imagesavealpha($miniatura, true);
                    $transparente = imagecolorallocatealpha($miniatura, 0, 0, 0, 127);
                    imagefilledrectangle($miniatura, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);
                }
                imagecopyresampled($miniatura, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                $nombreMiniatura = bin2hex(random_bytes(20)).'_thumb.'.$extensiones[$mime];
                $absolutaMiniatura = $directorio.DIRECTORY_SEPARATOR.$nombreMiniatura;
                $guardada = false;
                if ($mime === 'image/jpeg' && function_exists('imagejpeg')) {
                    $guardada = @imagejpeg($miniatura, $absolutaMiniatura, 78);
                } elseif ($mime === 'image/png' && function_exists('imagepng')) {
                    $guardada = @imagepng($miniatura, $absolutaMiniatura, 7);
                } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
                    $guardada = @imagewebp($miniatura, $absolutaMiniatura, 78);
                }
                if ($guardada && is_file($absolutaMiniatura)) {
                    $contexto->archivos_creados[] = $absolutaMiniatura;
                    $miniaturaRelativa = str_replace(
                        '\\', '/', $relDir.DIRECTORY_SEPARATOR.$nombreMiniatura
                    );
                } else {
                    @unlink($absolutaMiniatura);
                }
                imagedestroy($miniatura);
            }
            imagedestroy($origen);
        }
    }
    return array(
        'ruta_relativa' => str_replace('\\', '/', $relDir.DIRECTORY_SEPARATOR.$nombreSeguro),
        'miniatura_relativa' => $miniaturaRelativa,
        'nombre_original' => trabajoLaboratorioTextoEntrada(isset($evidencia['nombre_archivo']) ? $evidencia['nombre_archivo'] : '', 255),
        'mime' => $mime,
        'extension' => $extensiones[$mime],
        'tamano_bytes' => strlen($binario),
        'sha256' => hash('sha256', $binario),
        'descripcion' => trabajoLaboratorioTextoEntrada(isset($evidencia['descripcion']) ? $evidencia['descripcion'] : '', 255)
    );
}

function trabajoLaboratorioRegistrarMensajeHilo($mysqli, $trabajo, $codUsuario, $tipoEvento)
{
    if (in_array($tipoEvento, array('evidencia_agregada', 'nota_agregada'), true)) {
        return null;
    }
    $etiquetas = array(
        'trabajo_iniciado' => 'trabajo iniciado',
        'transferencia_mecanico_iniciada' => 'envio al laboratorio iniciado',
        'recepcion_mecanico_confirmada' => 'recepcion por el tecnico confirmada',
        'evidencia_agregada' => 'evidencia agregada',
        'nota_agregada' => 'nota de seguimiento agregada',
        'devolucion_iniciada' => 'devolucion a la clinica iniciada',
        'devolucion_confirmada' => 'recepcion en clinica confirmada',
        'ajuste_solicitado' => 'ajuste solicitado',
        'trabajo_aprobado' => 'trabajo aprobado',
        'instalacion_registrada' => 'instalacion registrada',
        'trabajo_cancelado' => 'trabajo cancelado'
    );
    $texto = isset($etiquetas[$tipoEvento]) ? $etiquetas[$tipoEvento] : $tipoEvento;
    $actor = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    $nombreActor = $actor && isset($actor['nombre_persona'])
        ? trabajoLaboratorioTextoUtf8($actor['nombre_persona']) : 'Usuario autorizado';
    $rolActor = $actor && isset($actor['tipo']) ? trabajoLaboratorioTextoUtf8($actor['tipo']) : 'usuario';
    $producto = isset($trabajo['nombre_producto'])
        ? trabajoLaboratorioTextoUtf8($trabajo['nombre_producto'])
        : trabajoLaboratorioTextoUtf8($trabajo['cod_productoFK']);
    $local = isset($trabajo['nombre_local'])
        ? trabajoLaboratorioTextoUtf8($trabajo['nombre_local'])
        : trabajoLaboratorioTextoUtf8($trabajo['sigla_local_snapshot']);
    $contenido = trabajoLaboratorioTextoBaseDatos(
        '[TRABAJO_LAB:'.intval($trabajo['id']).'] '.$trabajo['codigo_visible'].' - '.$texto
        .'. Producto: '.$producto.'. Sucursal: '.$local.'. Ciclo: '.intval($trabajo['ciclo_actual'])
        .'. Actor: '.$nombreActor.' ('.$rolActor.').',
        750
    );
    $estado = 'activo';
    $hilo = intval($trabajo['cod_interConsultaFK']);
    $stmt = $mysqli->prepare(
        'INSERT INTO mensaje (contenido,estado,cod_interConsultaFK,cod_usuarioFK,fecha_creacion) '
        .'VALUES (?,?,?,?,NOW())'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('mensaje_hilo_no_guardado', 'No se pudo registrar el movimiento en el hilo maestro.');
    }
    $stmt->bind_param('ssii', $contenido, $estado, $hilo, $codUsuario);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('mensaje_hilo_no_guardado', 'No se pudo registrar el movimiento en el hilo maestro.');
    }
    $id = intval($stmt->insert_id);
    $stmt->close();
    return $id;
}

function trabajoLaboratorioRegistrarEvento($mysqli, $trabajo, $idCiclo, $idIdempotencia, $tipoEvento,
    $codUsuario, $versionResultante, $observacion = '', $metadata = array(), $idTransferencia = null,
    $custodioAnterior = null, $custodioNuevo = null, $remitente = null, $destinatario = null,
    $codConsulta = null, $codEvolucion = null)
{
    $motivoExcepcion = isset($metadata['motivo_excepcion_auditor'])
        ? trabajoLaboratorioTextoEntrada($metadata['motivo_excepcion_auditor'], 750) : '';
    if ($motivoExcepcion !== '') {
        $observacion = trabajoLaboratorioTextoEntrada($observacion, 750);
        if ($observacion === '') {
            $observacion = 'Excepcion de auditoria: '.$motivoExcepcion;
        } elseif (strpos($observacion, $motivoExcepcion) === false) {
            $observacion = trabajoLaboratorioTextoEntrada(
                $observacion."\nExcepcion de auditoria: ".$motivoExcepcion,
                750
            );
        }
    }
    /* Una fusion de Hilos puede mover la relacion de la venta a otro maestro.
       El trabajo mantiene un puntero operativo actualizable; los eventos ya
       emitidos conservan su Hilo historico inmutable. */
    $hiloVigente = trabajoLaboratorioObtenerHiloUnicoVenta(
        $mysqli,
        intval($trabajo['cod_ventaFK']),
        true
    );
    if (!$hiloVigente) {
        trabajoLaboratorioLanzar(
            'hilo_maestro_no_vinculado',
            'La venta ya no tiene un Hilo maestro activo para registrar el evento.'
        );
    }
    $idHiloVigente = intval($hiloVigente['cod_interConsultaFK']);
    if ($idHiloVigente !== intval($trabajo['cod_interConsultaFK'])) {
        $idTrabajoActualizar = intval($trabajo['id']);
        $stmtHilo = $mysqli->prepare(
            'UPDATE trabajo_laboratorio SET cod_interConsultaFK=? WHERE id=? LIMIT 1'
        );
        if (!$stmtHilo) {
            trabajoLaboratorioLanzar('hilo_maestro_no_actualizado', 'No se pudo actualizar el Hilo maestro vigente.');
        }
        $stmtHilo->bind_param('ii', $idHiloVigente, $idTrabajoActualizar);
        if (!$stmtHilo->execute() || $stmtHilo->affected_rows !== 1) {
            $stmtHilo->close();
            trabajoLaboratorioLanzar('hilo_maestro_no_actualizado', 'No se pudo actualizar el Hilo maestro vigente.');
        }
        $stmtHilo->close();
        $trabajo['cod_interConsultaFK'] = $idHiloVigente;
    }
    $idMensaje = trabajoLaboratorioRegistrarMensajeHilo($mysqli, $trabajo, $codUsuario, $tipoEvento);
    $observacionBd = trabajoLaboratorioTextoBaseDatos($observacion, 750);
    $metadataJson = count($metadata) > 0 ? json_encode(trabajoLaboratorioUtf8($metadata)) : null;
    $idTrabajo = intval($trabajo['id']);
    $idCiclo = intval($idCiclo);
    $idIdempotencia = intval($idIdempotencia);
    $idTransferencia = $idTransferencia === null ? null : intval($idTransferencia);
    $codConsulta = $codConsulta === null ? null : intval($codConsulta);
    $codEvolucion = $codEvolucion === null ? null : intval($codEvolucion);
    $custodioAnterior = $custodioAnterior === null ? null : intval($custodioAnterior);
    $custodioNuevo = $custodioNuevo === null ? null : intval($custodioNuevo);
    $remitente = $remitente === null ? null : intval($remitente);
    $destinatario = $destinatario === null ? null : intval($destinatario);
    $codLocal = intval($trabajo['cod_localFK']);
    $hilo = intval($trabajo['cod_interConsultaFK']);
    $stmt = $mysqli->prepare(
        'INSERT INTO trabajo_laboratorio_evento '
        .'(id_trabajoFK,id_cicloFK,id_transferenciaFK,id_idempotenciaFK,cod_consulta_origenFK,'
        .'cod_evolucion_origenFK,tipo_evento,cod_usuario_actorFK,cod_custodio_anteriorFK,'
        .'cod_custodio_nuevoFK,cod_remitenteFK,cod_destinatario_previstoFK,cod_localFK,fecha_servidor,'
        .'observacion,metadata_json,version_resultante,cod_interConsultaFK,cod_mensaje_hiloFK) '
        .'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?)'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('evento_no_guardado', 'No se pudo registrar la trazabilidad del trabajo.');
    }
    $stmt->bind_param(
        'iiiiiisiiiiiissiii',
        $idTrabajo, $idCiclo, $idTransferencia, $idIdempotencia, $codConsulta,
        $codEvolucion, $tipoEvento, $codUsuario, $custodioAnterior, $custodioNuevo,
        $remitente, $destinatario, $codLocal, $observacionBd, $metadataJson,
        $versionResultante, $hilo, $idMensaje
    );
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('evento_no_guardado', 'No se pudo registrar la trazabilidad del trabajo.');
    }
    $id = intval($stmt->insert_id);
    $stmt->close();
    return $id;
}

function trabajoLaboratorioInsertarMedia($mysqli, $trabajo, $idCiclo, $idEvento, $codUsuario, $media, $tipo)
{
    $idTrabajo = intval($trabajo['id']);
    $idCiclo = intval($idCiclo);
    $idEvento = intval($idEvento);
    $tipo = trabajoLaboratorioTextoEntrada($tipo, 30);
    $ruta = trabajoLaboratorioTextoBaseDatos($media['ruta_relativa'], 500);
    $miniatura = trabajoLaboratorioTextoBaseDatos(isset($media['miniatura_relativa']) ? $media['miniatura_relativa'] : '', 500);
    if ($miniatura === '') {
        $miniatura = null;
    }
    $nombre = trabajoLaboratorioTextoBaseDatos($media['nombre_original'], 255);
    $mime = $media['mime'];
    $extension = $media['extension'];
    $tamano = intval($media['tamano_bytes']);
    $sha = $media['sha256'];
    $descripcion = trabajoLaboratorioTextoBaseDatos($media['descripcion'], 255);
    $visibilidad = 'autorizados_trabajo';
    $stmt = $mysqli->prepare(
        'INSERT INTO trabajo_laboratorio_media '
        .'(id_trabajoFK,id_cicloFK,id_eventoFK,cod_usuarioFK_upload,tipo_media,ruta_relativa,'
        .'miniatura_relativa,nombre_original,mime,extension,tamano_bytes,sha256,descripcion,visibilidad,fecha_creacion) '
        .'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('media_no_registrada', 'No se pudo registrar la evidencia.');
    }
    $stmt->bind_param(
        'iiiissssssisss',
        $idTrabajo, $idCiclo, $idEvento, $codUsuario, $tipo, $ruta, $miniatura, $nombre, $mime,
        $extension, $tamano, $sha, $descripcion, $visibilidad
    );
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('media_no_registrada', 'No se pudo registrar la evidencia.');
    }
    $id = intval($stmt->insert_id);
    $stmt->close();
    return $id;
}

function trabajoLaboratorioDataUriMedia($rutaRelativa, $mime)
{
    $relativa = str_replace('\\', '/', (string)$rutaRelativa);
    if ($relativa === '' || strpos($relativa, '..') !== false || strpos($relativa, '/') === 0) {
        return null;
    }
    try {
        $base = trabajoLaboratorioDirectorioMedia();
    } catch (Exception $e) {
        return null;
    }
    $archivo = realpath($base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativa));
    $baseNormal = rtrim(str_replace('\\', '/', $base), '/').'/';
    if ($archivo === false || stripos(str_replace('\\', '/', $archivo), $baseNormal) !== 0 || !is_file($archivo)) {
        return null;
    }
    $contenido = @file_get_contents($archivo);
    if ($contenido === false) {
        return null;
    }
    return 'data:'.preg_replace('/[^a-zA-Z0-9.+\/-]/', '', (string)$mime).';base64,'.base64_encode($contenido);
}

function trabajoLaboratorioCalcularIndicadoresPlazo(
    $estado,
    $fechaCreacion,
    $fechaObjetivo,
    $fechaCustodio,
    $ahora = null
) {
    $ahora = $ahora === null ? time() : intval($ahora);
    $creacion = trabajoLaboratorioTimestampSistema($fechaCreacion);
    $objetivo = trabajoLaboratorioTimestampSistema($fechaObjetivo);
    $custodio = trabajoLaboratorioTimestampSistema($fechaCustodio);
    if ($custodio === false) {
        $custodio = $creacion;
    }
    $diasTotales = $creacion === false ? 0 : max(0, intval(floor(($ahora - $creacion) / 86400)));
    $diasCustodio = $custodio === false ? 0 : max(0, intval(floor(($ahora - $custodio) / 86400)));
    $terminal = in_array((string)$estado, array('instalado', 'cancelado'), true);
    if ($terminal) {
        $semaforo = array('codigo' => 'finalizado', 'nivel' => 'ok', 'texto' => 'Finalizado');
    } elseif ($diasTotales > 30) {
        $semaforo = array('codigo' => 'atrasado', 'nivel' => 'danger', 'texto' => 'Fuera del plazo');
    } elseif ($diasTotales >= 20) {
        $semaforo = array('codigo' => 'advertencia', 'nivel' => 'warning', 'texto' => 'Proximo al limite');
    } else {
        $semaforo = array('codigo' => 'en_plazo', 'nivel' => 'ok', 'texto' => 'Dentro del plazo');
    }
    return array(
        'tiempo_restante_segundos' => $objetivo === false ? null : $objetivo - $ahora,
        'dias_totales' => $diasTotales,
        'dias_custodio_actual' => $diasCustodio,
        'sla_vencido' => !$terminal && $diasTotales > 30,
        'semaforo' => $semaforo
    );
}

function trabajoLaboratorioFormatearTrabajo($mysqli, $codUsuario, $trabajo, $completo = false)
{
    $indicadores = trabajoLaboratorioCalcularIndicadoresPlazo(
        isset($trabajo['estado_derivado']) ? $trabajo['estado_derivado'] : '',
        isset($trabajo['fecha_creacion']) ? $trabajo['fecha_creacion'] : '',
        isset($trabajo['fecha_objetivo']) ? $trabajo['fecha_objetivo'] : '',
        isset($trabajo['fecha_custodio_actual']) ? $trabajo['fecha_custodio_actual'] : '',
        time()
    );
    $miniaturaRuta = !empty($trabajo['miniatura_relativa_principal'])
        ? $trabajo['miniatura_relativa_principal'] : '';
    $miniaturaUrl = $miniaturaRuta !== ''
        ? trabajoLaboratorioDataUriMedia($miniaturaRuta, isset($trabajo['mime_media_principal']) ? $trabajo['mime_media_principal'] : 'image/jpeg')
        : null;
    $salida = array(
        'id' => intval($trabajo['id']),
        'codigo_visible' => trabajoLaboratorioTextoUtf8($trabajo['codigo_visible']),
        'cod_venta' => intval($trabajo['cod_ventaFK']),
        'nro_venta' => trabajoLaboratorioTextoUtf8($trabajo['numero_venta_snapshot']),
        'cod_detalle_venta' => intval($trabajo['cod_detalle_ventaFK']),
        'cod_cliente' => intval($trabajo['cod_clienteFK']),
        'paciente' => isset($trabajo['nombre_paciente'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_paciente']) : null,
        'nombre_paciente' => isset($trabajo['nombre_paciente'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_paciente']) : null,
        'cod_producto' => trabajoLaboratorioTextoUtf8($trabajo['cod_productoFK']),
        'producto' => isset($trabajo['nombre_producto']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_producto']) : null,
        'tipo_trabajo' => isset($trabajo['tipo_trabajo']) ? trabajoLaboratorioTextoUtf8($trabajo['tipo_trabajo']) : null,
        'cod_local' => intval($trabajo['cod_localFK']),
        'local' => isset($trabajo['nombre_local']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_local']) : null,
        'cod_tecnico_usuario' => intval($trabajo['cod_tecnico_usuarioFK']),
        'tecnico' => isset($trabajo['nombre_tecnico']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_tecnico']) : null,
        'cod_custodio_actual' => intval($trabajo['cod_custodio_actualFK']),
        'custodio_actual' => isset($trabajo['nombre_custodio']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_custodio']) : null,
        'doctor' => isset($trabajo['nombre_doctor']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_doctor']) : null,
        'estado_derivado' => $trabajo['estado_derivado'],
        'ciclo_actual' => intval($trabajo['ciclo_actual']),
        'fecha_objetivo' => $trabajo['fecha_objetivo'],
        'tiempo_restante_segundos' => $indicadores['tiempo_restante_segundos'],
        'sla_vencido' => $indicadores['sla_vencido'],
        'fecha_retiro' => $trabajo['fecha_retiro'],
        'fecha_entrega' => $trabajo['fecha_entrega'],
        'fecha_creacion' => $trabajo['fecha_creacion'],
        'fecha_actualizacion' => $trabajo['fecha_actualizacion'],
        'dias_totales' => $indicadores['dias_totales'],
        'dias_custodio_actual' => $indicadores['dias_custodio_actual'],
        'cantidad_ajustes' => max(0, intval($trabajo['ciclo_actual']) - 1),
        'ciclo_etiqueta' => intval($trabajo['ciclo_actual']) <= 1
            ? 'Original' : 'Ajuste '.intval($trabajo['ciclo_actual'] - 1),
        'semaforo' => $indicadores['semaforo'],
        'transferencia_pendiente' => intval($trabajo['id_transferencia_pendienteFK']) > 0,
        'miniatura_media_id' => isset($trabajo['id_media_principal']) ? intval($trabajo['id_media_principal']) : null,
        'miniatura_url' => $miniaturaUrl,
        'miniatura_fallback_original' => false,
        'version' => intval($trabajo['version']),
        'acciones_permitidas' => trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajo)
    );
    if ($completo) {
        $salida['colorimetro'] = trabajoLaboratorioTextoUtf8($trabajo['colorimetro']);
        $salida['instrucciones'] = trabajoLaboratorioTextoUtf8($trabajo['instrucciones']);
        if (trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
            $salida['costo_estimado'] = $trabajo['costo_estimado'] === null ? null : intval($trabajo['costo_estimado']);
        }
        $salida['cod_consulta_origen'] = $trabajo['cod_consulta_origenFK'] === null
            ? null : intval($trabajo['cod_consulta_origenFK']);
        $salida['cod_evolucion_origen'] = $trabajo['cod_evolucion_origenFK'] === null
            ? null : intval($trabajo['cod_evolucion_origenFK']);
        $salida['cod_interconsulta'] = intval($trabajo['cod_interConsultaFK']);
        $salida['motivo_cancelacion'] = trabajoLaboratorioTextoUtf8($trabajo['motivo_cancelacion']);
    }
    return $salida;
}

function trabajoLaboratorioObtenerDetalleTrabajo($mysqli, $codUsuario, $idTrabajo)
{
    $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, false);
    trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajo);
    $ciclos = array();
    $stmt = $mysqli->prepare(
        'SELECT c.*,p.nombre_persona AS solicitante FROM trabajo_laboratorio_ciclo c '
        .'LEFT JOIN persona p ON p.cod_persona=c.cod_usuario_solicitanteFK '
        .'WHERE c.id_trabajoFK=? ORDER BY c.numero_ciclo ASC'
    );
    $idTrabajo = intval($idTrabajo);
    if ($stmt) {
        $stmt->bind_param('i', $idTrabajo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $ciclos[] = array(
                'id' => intval($fila['id']),
                'numero' => intval($fila['numero_ciclo']),
                'tipo' => $fila['tipo'],
                'motivo' => trabajoLaboratorioTextoUtf8($fila['motivo']),
                'justificacion' => trabajoLaboratorioTextoUtf8($fila['justificacion']),
                'fecha_objetivo' => $fila['fecha_objetivo'],
                'solicitante' => trabajoLaboratorioTextoUtf8($fila['solicitante']),
                'fecha_creacion' => $fila['fecha_creacion']
            );
        }
        $stmt->close();
    }
    $eventos = array();
    $notas = array();
    $auditoria = array();
    $stmt = $mysqli->prepare(
        'SELECT e.*,pa.nombre_persona AS actor,ua.tipo AS actor_rol,ua.url AS actor_avatar,'
        .'pr.nombre_persona AS remitente,pd.nombre_persona AS destinatario,'
        .'pca.nombre_persona AS custodio_anterior,pcn.nombre_persona AS custodio_nuevo,'
        .'l.Nombre AS nombre_local,c.numero_ciclo,c.tipo AS tipo_ciclo,'
        .'me.id AS id_media_evento,me.ruta_relativa AS ruta_media_evento,'
        .'me.miniatura_relativa AS miniatura_media_evento,me.mime AS mime_media_evento '
        .'FROM trabajo_laboratorio_evento e '
        .'LEFT JOIN persona pa ON pa.cod_persona=e.cod_usuario_actorFK '
        .'LEFT JOIN usuario ua ON ua.cod_usuario=e.cod_usuario_actorFK '
        .'LEFT JOIN persona pr ON pr.cod_persona=e.cod_remitenteFK '
        .'LEFT JOIN persona pd ON pd.cod_persona=e.cod_destinatario_previstoFK '
        .'LEFT JOIN persona pca ON pca.cod_persona=e.cod_custodio_anteriorFK '
        .'LEFT JOIN persona pcn ON pcn.cod_persona=e.cod_custodio_nuevoFK '
        .'LEFT JOIN local l ON l.cod_local=e.cod_localFK '
        .'LEFT JOIN trabajo_laboratorio_ciclo c ON c.id=e.id_cicloFK '
        .'LEFT JOIN trabajo_laboratorio_media me ON me.id=('
        .'SELECT MIN(me2.id) FROM trabajo_laboratorio_media me2 WHERE me2.id_eventoFK=e.id) '
        .'WHERE e.id_trabajoFK=? ORDER BY e.fecha_servidor ASC,e.id ASC'
    );
    if ($stmt) {
        $stmt->bind_param('i', $idTrabajo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fechaNodoAnterior = null;
        while ($fila = $resultado->fetch_assoc()) {
            $metadata = json_decode(trabajoLaboratorioTextoUtf8($fila['metadata_json']), true);
            $numeroCiclo = intval($fila['numero_ciclo']);
            $cicloEtiqueta = $numeroCiclo <= 1 ? 'Original' : 'Ajuste '.intval($numeroCiclo - 1);
            $rutaMiniatura = trim((string)$fila['miniatura_media_evento']) !== ''
                ? $fila['miniatura_media_evento'] : '';
            $miniaturaUrl = $rutaMiniatura
                ? trabajoLaboratorioDataUriMedia($rutaMiniatura, $fila['mime_media_evento']) : null;
            $fechaActual = trabajoLaboratorioTimestampSistema($fila['fecha_servidor']);
            $diasDesdeAnterior = $fechaNodoAnterior && $fechaActual
                ? max(0, intval(floor(($fechaActual - $fechaNodoAnterior) / 86400))) : 0;
            $evento = array(
                'id' => intval($fila['id']),
                'id_ciclo' => intval($fila['id_cicloFK']),
                'tipo' => $fila['tipo_evento'],
                'tipo_evento' => $fila['tipo_evento'],
                'actor' => array(
                    'nombre' => trabajoLaboratorioTextoUtf8($fila['actor']),
                    'rol' => trabajoLaboratorioTextoUtf8($fila['actor_rol']),
                    'avatar' => trabajoLaboratorioTextoUtf8($fila['actor_avatar'])
                ),
                'remitente' => trabajoLaboratorioTextoUtf8($fila['remitente']),
                'destinatario' => trabajoLaboratorioTextoUtf8($fila['destinatario']),
                'fecha' => $fila['fecha_servidor'],
                'fecha_hora' => $fila['fecha_servidor'],
                'local' => trabajoLaboratorioTextoUtf8($fila['nombre_local']),
                'custodio_anterior' => array('nombre' => trabajoLaboratorioTextoUtf8($fila['custodio_anterior'])),
                'custodio_nuevo' => array('nombre' => trabajoLaboratorioTextoUtf8($fila['custodio_nuevo'])),
                'ciclo_etiqueta' => $cicloEtiqueta,
                'dias_desde_anterior' => $diasDesdeAnterior,
                'observacion' => trabajoLaboratorioTextoUtf8($fila['observacion']),
                'metadata' => is_array($metadata) ? $metadata : array(),
                'miniatura_media_id' => $fila['id_media_evento'] === null ? null : intval($fila['id_media_evento']),
                'miniatura_url' => $miniaturaUrl,
                'pendiente' => in_array($fila['tipo_evento'], array('transferencia_mecanico_iniciada', 'devolucion_iniciada'), true),
                'version_resultante' => intval($fila['version_resultante']),
                'cod_consulta_origen' => $fila['cod_consulta_origenFK'] === null ? null : intval($fila['cod_consulta_origenFK']),
                'cod_evolucion_origen' => $fila['cod_evolucion_origenFK'] === null ? null : intval($fila['cod_evolucion_origenFK'])
            );
            $auditoria[] = $evento;
            if ($fila['tipo_evento'] === 'nota_agregada') {
                $notas[] = array(
                    'id' => intval($fila['id']),
                    'nota' => trabajoLaboratorioTextoUtf8($fila['observacion']),
                    'actor' => $evento['actor'],
                    'fecha' => $fila['fecha_servidor'],
                    'ciclo_etiqueta' => $cicloEtiqueta
                );
                continue;
            }
            if ($fila['tipo_evento'] === 'evidencia_agregada') {
                continue;
            }
            $eventos[] = $evento;
            if ($fechaActual) {
                $fechaNodoAnterior = $fechaActual;
            }
        }
        $stmt->close();
    }
    $ubicaciones = array();
    $stmt = $mysqli->prepare(
        'SELECT * FROM trabajo_laboratorio_ubicacion WHERE id_trabajoFK=? ORDER BY id ASC'
    );
    if ($stmt) {
        $stmt->bind_param('i', $idTrabajo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $ubicaciones[] = array(
                'id' => intval($fila['id']),
                'pieza' => trabajoLaboratorioTextoUtf8($fila['pieza']),
                'piezas' => trabajoLaboratorioListaJson(trabajoLaboratorioTextoUtf8($fila['piezas_json'])),
                'superficies' => trabajoLaboratorioListaJson(trabajoLaboratorioTextoUtf8($fila['superficies_json'])),
                'denticion' => trabajoLaboratorioTextoUtf8($fila['denticion']),
                'arcada' => trabajoLaboratorioTextoUtf8($fila['arcada']),
                'cuadrante' => trabajoLaboratorioTextoUtf8($fila['cuadrante']),
                'boca_completa' => intval($fila['boca_completa']) === 1,
                'alcance' => trabajoLaboratorioTextoUtf8($fila['alcance_odontologico'])
            );
        }
        $stmt->close();
    }
    $media = array();
    $stmt = $mysqli->prepare(
        'SELECT m.id,m.id_cicloFK,m.id_eventoFK,m.tipo_media,m.ruta_relativa,m.miniatura_relativa,'
        .'m.nombre_original,m.mime,m.tamano_bytes,m.sha256,m.descripcion,m.fecha_creacion,'
        .'c.numero_ciclo FROM trabajo_laboratorio_media m '
        .'LEFT JOIN trabajo_laboratorio_ciclo c ON c.id=m.id_cicloFK '
        .'WHERE m.id_trabajoFK=? ORDER BY m.fecha_creacion ASC,m.id ASC'
    );
    if ($stmt) {
        $stmt->bind_param('i', $idTrabajo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $rutaMiniatura = trim((string)$fila['miniatura_relativa']) !== ''
                ? $fila['miniatura_relativa'] : '';
            $miniaturaUrl = $rutaMiniatura !== ''
                ? trabajoLaboratorioDataUriMedia($rutaMiniatura, $fila['mime']) : null;
            $media[] = array(
                'id' => intval($fila['id']),
                'id_ciclo' => intval($fila['id_cicloFK']),
                'id_evento' => intval($fila['id_eventoFK']),
                'tipo' => $fila['tipo_media'],
                'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_original']),
                'nombre_original' => trabajoLaboratorioTextoUtf8($fila['nombre_original']),
                'ciclo_etiqueta' => intval($fila['numero_ciclo']) <= 1
                    ? 'Original' : 'Ajuste '.intval(intval($fila['numero_ciclo']) - 1),
                'mime' => $fila['mime'],
                'tamano_bytes' => intval($fila['tamano_bytes']),
                'sha256' => $fila['sha256'],
                'descripcion' => trabajoLaboratorioTextoUtf8($fila['descripcion']),
                'fecha' => $fila['fecha_creacion'],
                'miniatura_url' => $miniaturaUrl,
                'url_visualizacion' => $miniaturaUrl,
                'miniatura_fallback_original' => false
            );
        }
        $stmt->close();
    }
    $destinatarios = trabajoLaboratorioDestinatariosPermitidos($mysqli, $trabajo);
    $trabajoFormateado = trabajoLaboratorioFormatearTrabajo($mysqli, $codUsuario, $trabajo, true);
    if (!empty($media)) {
        $trabajoFormateado['miniatura_media_id'] = intval($media[0]['id']);
        $trabajoFormateado['miniatura_url'] = $media[0]['miniatura_url'];
        $trabajoFormateado['miniatura_fallback_original'] = !empty($media[0]['miniatura_fallback_original']);
    }
    return array(
        'trabajo' => $trabajoFormateado,
        'ciclos' => $ciclos,
        'eventos' => $eventos,
        'notas' => $notas,
        'auditoria' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario) ? $auditoria : array(),
        'puede_ver_auditoria' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario),
        'ubicaciones' => $ubicaciones,
        'media' => $media,
        'destinatarios_permitidos' => $destinatarios['actuales'],
        'destinatarios_por_tipo' => $destinatarios,
        'acciones_permitidas' => trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajo)
    );
}

function trabajoLaboratorioCondicionAccesoListado($mysqli, $codUsuario, &$tipos, &$valores)
{
    $auditor = trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario);
    $puedeVer = trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO');
    $esTecnico = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false) ? true : false;
    if ($auditor) {
        return '1=1';
    }
    if ($esTecnico) {
        $tipos .= 'ii';
        $valores[] = intval($codUsuario);
        $valores[] = intval($codUsuario);
        return '(tl.cod_tecnico_usuarioFK=? OR tl.cod_custodio_actualFK=?)';
    }
    if (!$puedeVer) {
        return '0=1';
    }
    if (trabajoLaboratorioUsuarioPuedeTodosLocales($mysqli, $codUsuario)) {
        return '1=1';
    }
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    $local = $usuario ? intval($usuario['cod_localFK']) : 0;
    $tipos .= 'iii';
    $valores[] = $local;
    $valores[] = intval($codUsuario);
    $valores[] = intval($codUsuario);
    return '(tl.cod_localFK=? OR tl.cod_tecnico_usuarioFK=? OR tl.cod_custodio_actualFK=?)';
}

function trabajoLaboratorioVincularParametros($stmt, $tipos, &$valores)
{
    if ($tipos === '') {
        return;
    }
    $referencias = array($tipos);
    foreach ($valores as $indice => $valor) {
        $referencias[] =& $valores[$indice];
    }
    call_user_func_array(array($stmt, 'bind_param'), $referencias);
}

function trabajoLaboratorioListar($mysqli, $codUsuario, $entrada)
{
    if (!trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO')
        && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        trabajoLaboratorioLanzar('listado_no_autorizado', 'El usuario no puede listar trabajos de laboratorio.');
    }
    $pagina = max(1, trabajoLaboratorioEntero(isset($entrada['pagina']) ? $entrada['pagina'] : 1));
    $porPagina = trabajoLaboratorioEntero(isset($entrada['por_pagina']) ? $entrada['por_pagina']
        : (isset($entrada['limite']) ? $entrada['limite'] : 20));
    $porPagina = max(5, min(100, $porPagina));
    $offset = ($pagina - 1) * $porPagina;
    $estado = trabajoLaboratorioTextoEntrada(isset($entrada['estado']) ? $entrada['estado'] : '', 40);
    $situacion = trabajoLaboratorioNormalizarTexto(isset($entrada['situacion']) ? $entrada['situacion'] : '');
    $grupo = trabajoLaboratorioNormalizarTexto(isset($entrada['grupo_operativo']) ? $entrada['grupo_operativo'] : '');
    $vista = trabajoLaboratorioNormalizarTexto(isset($entrada['vista']) ? $entrada['vista'] : '');
    $bandeja = trabajoLaboratorioNormalizarTexto(isset($entrada['bandeja']) ? $entrada['bandeja'] : '');
    $busqueda = trabajoLaboratorioTextoBaseDatos(isset($entrada['busqueda']) ? $entrada['busqueda'] : '', 100);
    $localFiltro = trabajoLaboratorioEntero(isset($entrada['cod_local']) ? $entrada['cod_local'] : 0);
    if ($localFiltro > 0 && !trabajoLaboratorioUsuarioPuedeLocal($mysqli, $codUsuario, $localFiltro)) {
        trabajoLaboratorioLanzar('local_no_autorizado', 'El usuario no puede consultar el local solicitado.');
    }
    $tipos = '';
    $valores = array();
    $condiciones = array(trabajoLaboratorioCondicionAccesoListado($mysqli, $codUsuario, $tipos, $valores));
    if ($estado !== '') {
        $condiciones[] = 'tl.estado_derivado=?';
        $tipos .= 's';
        $valores[] = $estado;
    }
    $estadosValidos = array(
        'pendiente_entrega_mecanico', 'en_transferencia_mecanico', 'en_laboratorio',
        'en_transferencia_clinica', 'pendiente_revision', 'ajuste_solicitado',
        'listo_instalacion', 'instalado', 'cancelado'
    );
    if ($situacion !== '') {
        if (in_array($situacion, $estadosValidos, true)) {
            $condiciones[] = 'tl.estado_derivado=?';
            $tipos .= 's';
            $valores[] = $situacion;
        } elseif ($situacion === 'atrasado') {
            $condiciones[] = "TIMESTAMPDIFF(DAY,tl.fecha_creacion,NOW())>30 AND tl.estado_derivado NOT IN ('instalado','cancelado')";
        } elseif ($situacion === 'advertencia') {
            $condiciones[] = "TIMESTAMPDIFF(DAY,tl.fecha_creacion,NOW()) BETWEEN 20 AND 30 AND tl.estado_derivado NOT IN ('instalado','cancelado')";
        } elseif ($situacion === 'en_plazo') {
            $condiciones[] = "TIMESTAMPDIFF(DAY,tl.fecha_creacion,NOW())<20 AND tl.estado_derivado NOT IN ('instalado','cancelado')";
        }
    }
    $gruposEstados = array(
        'pendientes_entrega' => array('pendiente_entrega_mecanico', 'ajuste_solicitado'),
        'en_laboratorio' => array('en_transferencia_mecanico', 'en_laboratorio', 'en_transferencia_clinica'),
        'pendientes_revision' => array('pendiente_revision', 'listo_instalacion'),
        'finalizados' => array('instalado', 'cancelado')
    );
    if ($grupo !== '' && isset($gruposEstados[$grupo])) {
        $marcas = array();
        foreach ($gruposEstados[$grupo] as $estadoGrupo) {
            $marcas[] = '?';
            $tipos .= 's';
            $valores[] = $estadoGrupo;
        }
        $condiciones[] = 'tl.estado_derivado IN ('.implode(',', $marcas).')';
    }
    if ($vista === 'mecanico') {
        $condiciones[] = '(tl.cod_tecnico_usuarioFK=? OR tl.cod_custodio_actualFK=?)';
        $tipos .= 'ii';
        $valores[] = intval($codUsuario);
        $valores[] = intval($codUsuario);
        if ($bandeja === 'por_recibir') {
            $condiciones[] = "tl.estado_derivado='en_transferencia_mecanico' AND tl.cod_tecnico_usuarioFK=?";
            $tipos .= 'i';
            $valores[] = intval($codUsuario);
        } elseif ($bandeja === 'en_mi_poder') {
            $condiciones[] = "tl.estado_derivado='en_laboratorio' AND tl.cod_custodio_actualFK=?";
            $tipos .= 'i';
            $valores[] = intval($codUsuario);
        } elseif ($bandeja === 'ajuste_solicitado') {
            $condiciones[] = "tl.estado_derivado='ajuste_solicitado' AND tl.cod_tecnico_usuarioFK=?";
            $tipos .= 'i';
            $valores[] = intval($codUsuario);
        } elseif ($bandeja === 'listos_entregar') {
            $condiciones[] = "tl.estado_derivado='en_laboratorio' AND tl.cod_tecnico_usuarioFK=?";
            $tipos .= 'i';
            $valores[] = intval($codUsuario);
        } elseif ($bandeja === 'finalizados') {
            $condiciones[] = "tl.estado_derivado IN ('instalado','cancelado') AND tl.cod_tecnico_usuarioFK=?";
            $tipos .= 'i';
            $valores[] = intval($codUsuario);
        }
    }
    if ($localFiltro > 0) {
        $condiciones[] = 'tl.cod_localFK=?';
        $tipos .= 'i';
        $valores[] = $localFiltro;
    }
    $codTecnico = trabajoLaboratorioEntero(isset($entrada['cod_tecnico_usuario'])
        ? $entrada['cod_tecnico_usuario'] : (isset($entrada['cod_mecanico']) ? $entrada['cod_mecanico'] : 0));
    if ($codTecnico > 0) {
        $condiciones[] = 'tl.cod_tecnico_usuarioFK=?';
        $tipos .= 'i';
        $valores[] = $codTecnico;
    }
    $codMecanicoDental = trabajoLaboratorioEntero(isset($entrada['cod_mecanico_dental'])
        ? $entrada['cod_mecanico_dental'] : 0);
    if ($codMecanicoDental > 0) {
        $condiciones[] = 'tl.cod_mecanico_dentalFK=?';
        $tipos .= 'i';
        $valores[] = $codMecanicoDental;
    }
    $codDoctor = trabajoLaboratorioEntero(isset($entrada['cod_doctor']) ? $entrada['cod_doctor'] : 0);
    if ($codDoctor > 0) {
        $condiciones[] = 'tl.cod_usuarioFK_create=?';
        $tipos .= 'i';
        $valores[] = $codDoctor;
    }
    $codCustodio = trabajoLaboratorioEntero(isset($entrada['cod_custodio']) ? $entrada['cod_custodio'] : 0);
    if ($codCustodio > 0) {
        $condiciones[] = 'tl.cod_custodio_actualFK=?';
        $tipos .= 'i';
        $valores[] = $codCustodio;
    }
    $codProducto = trabajoLaboratorioTextoEntrada(isset($entrada['cod_producto']) ? $entrada['cod_producto'] : '', 45);
    if ($codProducto !== '') {
        $condiciones[] = 'tl.cod_productoFK=?';
        $tipos .= 's';
        $valores[] = trabajoLaboratorioTextoBaseDatos($codProducto, 45);
    }
    $codTipoTrabajo = trabajoLaboratorioEntero(isset($entrada['cod_tipo_trabajo'])
        ? $entrada['cod_tipo_trabajo'] : 0);
    if ($codTipoTrabajo > 0) {
        $condiciones[] = 'tl.cod_tipo_trabajoFK=?';
        $tipos .= 'i';
        $valores[] = $codTipoTrabajo;
    }
    $plazo = trabajoLaboratorioNormalizarTexto(isset($entrada['plazo']) ? $entrada['plazo'] : '');
    if ($plazo === 'atrasado') {
        $condiciones[] = "TIMESTAMPDIFF(DAY,tl.fecha_creacion,NOW())>30 AND tl.estado_derivado NOT IN ('instalado','cancelado')";
    } elseif ($plazo === 'advertencia') {
        $condiciones[] = "TIMESTAMPDIFF(DAY,tl.fecha_creacion,NOW()) BETWEEN 20 AND 30 AND tl.estado_derivado NOT IN ('instalado','cancelado')";
    } elseif ($plazo === 'en_plazo') {
        $condiciones[] = "TIMESTAMPDIFF(DAY,tl.fecha_creacion,NOW())<20 AND tl.estado_derivado NOT IN ('instalado','cancelado')";
    }
    $ajustesDesde = trabajoLaboratorioEntero(isset($entrada['ajustes_desde']) ? $entrada['ajustes_desde'] : -1);
    if ($ajustesDesde >= 0 && isset($entrada['ajustes_desde']) && trim((string)$entrada['ajustes_desde']) !== '') {
        $condiciones[] = 'tl.ciclo_actual>=?';
        $tipos .= 'i';
        $valores[] = $ajustesDesde + 1;
    }
    $fechaDesde = trabajoLaboratorioTextoEntrada(isset($entrada['fecha_desde']) ? $entrada['fecha_desde'] : '', 10);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $condiciones[] = 'tl.fecha_creacion>=?';
        $tipos .= 's';
        $valores[] = $fechaDesde.' 00:00:00';
    }
    $fechaHasta = trabajoLaboratorioTextoEntrada(isset($entrada['fecha_hasta']) ? $entrada['fecha_hasta'] : '', 10);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $condiciones[] = 'tl.fecha_creacion<DATE_ADD(?,INTERVAL 1 DAY)';
        $tipos .= 's';
        $valores[] = $fechaHasta.' 00:00:00';
    }
    if (!empty($entrada['transferencia_pendiente']) && trabajoLaboratorioEntero($entrada['transferencia_pendiente']) === 1) {
        $condiciones[] = 'tl.id_transferencia_pendienteFK IS NOT NULL';
    }
    if ($busqueda !== '') {
        $condiciones[] = '(tl.codigo_visible LIKE CONCAT(\'%\',?,\'%\') '
            .'OR tl.numero_venta_snapshot LIKE CONCAT(\'%\',?,\'%\') '
            .'OR p.nombre_producto LIKE CONCAT(\'%\',?,\'%\') '
            .'OR pp.nombre_persona LIKE CONCAT(\'%\',?,\'%\'))';
        $tipos .= 'ssss';
        $valores[] = $busqueda;
        $valores[] = $busqueda;
        $valores[] = $busqueda;
        $valores[] = $busqueda;
    }
    $where = implode(' AND ', $condiciones);
    $base = ' FROM trabajo_laboratorio tl INNER JOIN producto p ON p.cod_producto=tl.cod_productoFK '
        .'LEFT JOIN persona pp ON pp.cod_persona=tl.cod_clienteFK '
        .'LEFT JOIN persona pt ON pt.cod_persona=tl.cod_tecnico_usuarioFK '
        .'LEFT JOIN persona pcu ON pcu.cod_persona=tl.cod_custodio_actualFK '
        .'LEFT JOIN persona pdoc ON pdoc.cod_persona=tl.cod_usuarioFK_create '
        .'LEFT JOIN local l ON l.cod_local=tl.cod_localFK '
        .'LEFT JOIN tipo_trabajo_mecanico_dental tt ON tt.cod_tipo_trabajo_mecanico_dental=tl.cod_tipo_trabajoFK '
        .'WHERE '.$where;
    $stmtTotal = $mysqli->prepare('SELECT COUNT(*) AS total'.$base);
    if (!$stmtTotal) {
        trabajoLaboratorioLanzar('listado_no_disponible', 'No se pudo preparar el listado.');
    }
    $valoresTotal = $valores;
    trabajoLaboratorioVincularParametros($stmtTotal, $tipos, $valoresTotal);
    $stmtTotal->execute();
    $total = intval($stmtTotal->get_result()->fetch_assoc()['total']);
    $stmtTotal->close();

    $sql = 'SELECT tl.*,p.nombre_producto,l.Nombre AS nombre_local,pp.nombre_persona AS nombre_paciente,'
        .'pt.nombre_persona AS nombre_tecnico,pcu.nombre_persona AS nombre_custodio,'
        .'pdoc.nombre_persona AS nombre_doctor,tt.descripcion AS tipo_trabajo,'
        .'(SELECT MAX(ev.fecha_servidor) FROM trabajo_laboratorio_evento ev '
        .'WHERE ev.id_trabajoFK=tl.id AND ev.cod_custodio_nuevoFK=tl.cod_custodio_actualFK) AS fecha_custodio_actual,'
        .'(SELECT mm.id FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS id_media_principal,'
        .'(SELECT mm.miniatura_relativa FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS miniatura_relativa_principal,'
        .'(SELECT mm.ruta_relativa FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS ruta_relativa_principal,'
        .'(SELECT mm.mime FROM trabajo_laboratorio_media mm WHERE mm.id_trabajoFK=tl.id '
        .'ORDER BY mm.fecha_creacion ASC,mm.id ASC LIMIT 1) AS mime_media_principal'
        .$base.' ORDER BY tl.fecha_actualizacion DESC,tl.id DESC LIMIT ?,?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('listado_no_disponible', 'No se pudo preparar el listado.');
    }
    $tiposLista = $tipos.'ii';
    $valoresLista = $valores;
    $valoresLista[] = $offset;
    $valoresLista[] = $porPagina;
    trabajoLaboratorioVincularParametros($stmt, $tiposLista, $valoresLista);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('listado_no_disponible', 'No se pudo consultar el listado.');
    }
    $items = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $items[] = trabajoLaboratorioFormatearTrabajo($mysqli, $codUsuario, $fila, false);
    }
    $stmt->close();
    $hayMas = ($offset + count($items)) < $total;
    return array(
        'items' => $items,
        'trabajos' => $items,
        'total' => $total,
        'hay_mas' => $hayMas,
        'paginacion' => array(
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total' => $total,
            'total_paginas' => $total > 0 ? intval(ceil($total / $porPagina)) : 0
        )
    );
}

function trabajoLaboratorioResumen($mysqli, $codUsuario, $entrada)
{
    if (!trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO')
        && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        trabajoLaboratorioLanzar('resumen_no_autorizado', 'El usuario no puede consultar el resumen de laboratorio.');
    }
    $tipos = '';
    $valores = array();
    $condicion = trabajoLaboratorioCondicionAccesoListado($mysqli, $codUsuario, $tipos, $valores);
    $local = trabajoLaboratorioEntero(isset($entrada['cod_local']) ? $entrada['cod_local'] : 0);
    if ($local > 0) {
        if (!trabajoLaboratorioUsuarioPuedeLocal($mysqli, $codUsuario, $local)) {
            trabajoLaboratorioLanzar('local_no_autorizado', 'El usuario no puede consultar el local solicitado.');
        }
        $condicion .= ' AND tl.cod_localFK=?';
        $tipos .= 'i';
        $valores[] = $local;
    }
    $sqlResumen = "SELECT COUNT(*) AS total,"
        ."SUM(estado_derivado IN ('pendiente_entrega_mecanico','ajuste_solicitado')) AS pendientes_entrega,"
        ."SUM(estado_derivado IN ('en_transferencia_mecanico','en_laboratorio','en_transferencia_clinica')) AS en_laboratorio,"
        ."SUM(estado_derivado IN ('pendiente_revision','listo_instalacion')) AS pendientes_revision,"
        ."SUM(ciclo_actual>1 AND estado_derivado NOT IN ('instalado','cancelado')) AS ajustes_activos,"
        ."SUM(TIMESTAMPDIFF(DAY,fecha_creacion,NOW())>30 AND estado_derivado NOT IN ('instalado','cancelado')) AS fuera_plazo,"
        ."SUM(estado_derivado IN ('instalado','cancelado') AND fecha_actualizacion>=DATE_SUB(NOW(),INTERVAL 30 DAY)) AS finalizados_recientes,"
        ."SUM(estado_derivado IN ('instalado','cancelado')) AS finalizados "
        .'FROM trabajo_laboratorio tl WHERE '.$condicion;
    $stmt = $mysqli->prepare($sqlResumen);
    if (!$stmt) {
        trabajoLaboratorioLanzar('resumen_no_disponible', 'No se pudo preparar el resumen.');
    }
    trabajoLaboratorioVincularParametros($stmt, $tipos, $valores);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $resumen = array(
        'total' => intval($fila['total']),
        'pendientes_entrega' => intval($fila['pendientes_entrega']),
        'en_laboratorio' => intval($fila['en_laboratorio']),
        'pendientes_revision' => intval($fila['pendientes_revision']),
        'ajustes_activos' => intval($fila['ajustes_activos']),
        'fuera_plazo' => intval($fila['fuera_plazo']),
        'finalizados_recientes' => intval($fila['finalizados_recientes'])
    );
    $resumen['grupos'] = array(
        'pendientes_entrega' => $resumen['pendientes_entrega'],
        'en_laboratorio' => $resumen['en_laboratorio'],
        'pendientes_revision' => $resumen['pendientes_revision'],
        'finalizados' => intval($fila['finalizados'])
    );

    $tiposBandeja = 'iiiii'.$tipos.'ii';
    $valoresBandeja = array();
    for ($i = 0; $i < 5; $i++) {
        $valoresBandeja[] = intval($codUsuario);
    }
    foreach ($valores as $valorResumen) {
        $valoresBandeja[] = $valorResumen;
    }
    $valoresBandeja[] = intval($codUsuario);
    $valoresBandeja[] = intval($codUsuario);
    $stmt = $mysqli->prepare(
        "SELECT "
        ."SUM(estado_derivado='en_transferencia_mecanico' AND cod_tecnico_usuarioFK=?) AS por_recibir,"
        ."SUM(estado_derivado='en_laboratorio' AND cod_custodio_actualFK=?) AS en_mi_poder,"
        ."SUM(estado_derivado='ajuste_solicitado' AND cod_tecnico_usuarioFK=?) AS ajuste_solicitado,"
        ."SUM(estado_derivado='en_laboratorio' AND cod_tecnico_usuarioFK=?) AS listos_entregar,"
        ."SUM(estado_derivado IN ('instalado','cancelado') AND cod_tecnico_usuarioFK=?) AS finalizados "
        .'FROM trabajo_laboratorio tl WHERE '.$condicion
        .' AND (tl.cod_tecnico_usuarioFK=? OR tl.cod_custodio_actualFK=?)'
    );
    if ($stmt) {
        trabajoLaboratorioVincularParametros($stmt, $tiposBandeja, $valoresBandeja);
        $stmt->execute();
        $bandeja = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $bandeja = array();
    }
    $resumen['bandeja_mecanico'] = array(
        'por_recibir' => intval(isset($bandeja['por_recibir']) ? $bandeja['por_recibir'] : 0),
        'en_mi_poder' => intval(isset($bandeja['en_mi_poder']) ? $bandeja['en_mi_poder'] : 0),
        'ajuste_solicitado' => intval(isset($bandeja['ajuste_solicitado']) ? $bandeja['ajuste_solicitado'] : 0),
        'listos_entregar' => intval(isset($bandeja['listos_entregar']) ? $bandeja['listos_entregar'] : 0),
        'finalizados' => intval(isset($bandeja['finalizados']) ? $bandeja['finalizados'] : 0)
    );
    return $resumen;
}

function trabajoLaboratorioDescargarMedia($mysqli, $codUsuario, $idMedia, $miniatura = false)
{
    $idMedia = intval($idMedia);
    $stmt = $mysqli->prepare(
        'SELECT m.*,m.id AS id_media,tl.id AS id_trabajo FROM trabajo_laboratorio_media m '
        .'INNER JOIN trabajo_laboratorio tl ON tl.id=m.id_trabajoFK WHERE m.id=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('media_no_disponible', 'No se pudo consultar la evidencia.');
    }
    $stmt->bind_param('i', $idMedia);
    $stmt->execute();
    $media = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$media) {
        trabajoLaboratorioLanzar('media_no_encontrada', 'No se encontro la evidencia.');
    }
    $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, intval($media['id_trabajo']), false);
    trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajo);
    $usarMiniatura = $miniatura && trim((string)$media['miniatura_relativa']) !== '';
    $relativa = str_replace('\\', '/', (string)($usarMiniatura ? $media['miniatura_relativa'] : $media['ruta_relativa']));
    if ($relativa === '' || strpos($relativa, '..') !== false || strpos($relativa, '/') === 0) {
        trabajoLaboratorioLanzar('ruta_media_invalida', 'La ruta de la evidencia no es valida.');
    }
    $base = trabajoLaboratorioDirectorioMedia();
    $archivo = realpath($base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativa));
    $baseNormal = rtrim(str_replace('\\', '/', $base), '/').'/';
    if ($archivo === false || stripos(str_replace('\\', '/', $archivo), $baseNormal) !== 0 || !is_file($archivo)) {
        trabajoLaboratorioLanzar('archivo_media_no_encontrado', 'El archivo protegido no esta disponible.');
    }
    $contenido = file_get_contents($archivo);
    if ($contenido === false) {
        trabajoLaboratorioLanzar('archivo_media_no_disponible', 'No se pudo leer la evidencia protegida.');
    }
    return array(
        'id' => intval($media['id_media']),
        'id_trabajo' => intval($media['id_trabajo']),
        'nombre' => trabajoLaboratorioTextoUtf8($media['nombre_original']),
        'mime' => $media['mime'],
        'tamano_bytes' => strlen($contenido),
        'es_miniatura' => $usarMiniatura,
        'sha256' => $media['sha256'],
        'data_base64' => base64_encode($contenido)
    );
}

function trabajoLaboratorioRespuestaActualizada($mysqli, $codUsuario, $idTrabajo, $codigo, $mensaje)
{
    $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, false);
    trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajo);
    $acciones = trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajo);
    return trabajoLaboratorioRespuesta(
        true,
        $codigo,
        $mensaje,
        array(
            'id' => intval($trabajo['id']),
            'id_trabajo' => intval($trabajo['id']),
            'cod_trabajo_laboratorio' => intval($trabajo['id']),
            'trabajo' => trabajoLaboratorioFormatearTrabajo($mysqli, $codUsuario, $trabajo, true),
            'acciones_permitidas' => $acciones
        ),
        intval($trabajo['version'])
    );
}

function trabajoLaboratorioValidarTipoTrabajo($mysqli, $codTipo)
{
    $codTipo = intval($codTipo);
    if ($codTipo <= 0) {
        return null;
    }
    $stmt = $mysqli->prepare(
        "SELECT 1 FROM tipo_trabajo_mecanico_dental WHERE cod_tipo_trabajo_mecanico_dental=? "
        ."AND estado='activo' LIMIT 1"
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('tipo_trabajo_no_disponible', 'No se pudo validar el tipo de trabajo.');
    }
    $stmt->bind_param('i', $codTipo);
    $stmt->execute();
    $valido = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if (!$valido) {
        trabajoLaboratorioLanzar('tipo_trabajo_invalido', 'El tipo de trabajo no existe o esta inactivo.');
    }
    return $codTipo;
}

function trabajoLaboratorioIniciar($mysqli, $codUsuario, $entrada)
{
    if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
        trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
    }
    return trabajoLaboratorioEjecutarComando(
        $mysqli,
        $codUsuario,
        'iniciarTrabajo',
        $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $codDetalle = trabajoLaboratorioEntero(isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta'] : 0);
            $codTecnico = trabajoLaboratorioEntero(isset($entrada['cod_tecnico_usuario'])
                ? $entrada['cod_tecnico_usuario']
                : (isset($entrada['cod_tecnico_usuarioFK']) ? $entrada['cod_tecnico_usuarioFK'] : 0));
            $detalle = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, true);
            if (!$detalle) {
                trabajoLaboratorioLanzar('detalle_no_encontrado', 'No se encontro el detalle de tratamiento.');
            }
            if (in_array(trabajoLaboratorioNormalizarTexto($detalle['estado_detalle']), array('eliminado', 'inactivo', 'anulado'), true)
                || in_array(trabajoLaboratorioNormalizarTexto($detalle['estado_venta']), array('inactivo', 'anulado'), true)) {
                trabajoLaboratorioLanzar('detalle_venta_inactivo', 'No se puede iniciar un trabajo sobre una venta o detalle inactivo.');
            }
            if (!trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'CREARTRABAJOLABORATORIO')
                || (!trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario)
                    && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario))) {
                trabajoLaboratorioLanzar('creacion_no_autorizada', 'El usuario no puede iniciar trabajos de laboratorio.');
            }
            if (!trabajoLaboratorioUsuarioPuedeOperarLocal($mysqli, $codUsuario, intval($detalle['cod_local']))) {
                trabajoLaboratorioLanzar('local_no_autorizado', 'El usuario no puede operar sobre el local de esta venta.');
            }
            $motivoExcepcionInicio = trabajoLaboratorioExigirMotivoExcepcionAuditor(
                $mysqli,
                $codUsuario,
                array(
                    'cod_localFK' => intval($detalle['cod_local']),
                    'cod_custodio_actualFK' => intval($codUsuario),
                    'cod_tecnico_usuarioFK' => 0
                ),
                'iniciarTrabajo',
                $entrada
            );
            $config = $detalle['configuracion_laboratorio'];
            if (empty($config['ok']) || empty($config['requiere_laboratorio'])) {
                trabajoLaboratorioLanzar('producto_no_requiere_laboratorio', 'El producto no esta configurado para laboratorio.');
            }
            if (abs(floatval($detalle['cantidad_detalle']) - 1.0) >= 0.000001) {
                trabajoLaboratorioLanzar('cantidad_laboratorio_invalida', 'Cada detalle clinico de laboratorio debe tener cantidad 1.');
            }
            $ubicaciones = trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, $codDetalle);
            $bloqueoUbicacion = trabajoLaboratorioValidarUbicacionesModo($config['modo_individualizacion'], $ubicaciones);
            if ($bloqueoUbicacion) {
                trabajoLaboratorioLanzar($bloqueoUbicacion['codigo'], $bloqueoUbicacion['mensaje']);
            }
            if (trabajoLaboratorioObtenerTrabajoActivoDetalle($mysqli, $codDetalle)) {
                trabajoLaboratorioLanzar('trabajo_activo_existente', 'Este detalle ya tiene un trabajo de laboratorio activo.');
            }
            $tecnico = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codTecnico, true);
            if (!$tecnico) {
                trabajoLaboratorioLanzar('tecnico_formal_invalido', 'Seleccione un tecnico activo vinculado a un usuario formal.');
            }
            if (!trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'VERTRABAJOSLABORATORIO')
                || !trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'RECIBIRTRABAJOLABORATORIO')
                || !trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'ENTREGARTRABAJOLABORATORIO')) {
                trabajoLaboratorioLanzar(
                    'tecnico_sin_acceso_laboratorio',
                    'El tecnico seleccionado necesita permisos de acceso, recepcion y entrega para completar el circuito.'
                );
            }
            $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($detalle['cod_ventaFK']), true);
            if (!$hilo) {
                trabajoLaboratorioLanzar(
                    'hilo_unico_no_vinculado',
                    'La venta no tiene vinculado su hilo maestro de seguimiento.',
                    array('accion_sugerida' => 'vincular_hilo_maestro')
                );
            }
            $origen = trabajoLaboratorioValidarOrigenClinico(
                $mysqli,
                $codDetalle,
                intval($detalle['cod_ventaFK']),
                isset($entrada['cod_consulta_origen']) ? $entrada['cod_consulta_origen'] : 0,
                isset($entrada['cod_evolucion_origen']) ? $entrada['cod_evolucion_origen'] : 0,
                false
            );
            $codTipo = trabajoLaboratorioValidarTipoTrabajo(
                $mysqli,
                isset($entrada['cod_tipo_trabajo']) ? $entrada['cod_tipo_trabajo'] : 0
            );
            $evidencia = trabajoLaboratorioDecodificarJson(
                isset($entrada['evidencia_inicial']) ? $entrada['evidencia_inicial'] : array(),
                array()
            );
            if (!isset($evidencia['data_base64'])) {
                trabajoLaboratorioLanzar('evidencia_inicial_requerida', 'Debe adjuntar una foto inicial del trabajo.');
            }
            $evidenciasAdicionales = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            if (count($evidenciasAdicionales) > 4) {
                trabajoLaboratorioLanzar('cantidad_evidencias_invalida', 'Puede adjuntar hasta cinco imagenes al iniciar el trabajo.');
            }
            $color = trabajoLaboratorioTextoEntrada(isset($entrada['colorimetro']) ? $entrada['colorimetro'] : '', 30);
            $instrucciones = trabajoLaboratorioTextoEntrada(isset($entrada['instrucciones'])
                ? $entrada['instrucciones']
                : (isset($entrada['observacion']) ? $entrada['observacion'] : ''), 1000);
            $costo = isset($entrada['costo_estimado']) && trim((string)$entrada['costo_estimado']) !== ''
                ? trabajoLaboratorioEntero($entrada['costo_estimado']) : null;
            if ($costo !== null && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
                trabajoLaboratorioLanzar('costo_no_autorizado', 'Solo un auditor autorizado puede registrar el costo estimado.');
            }
            if ($costo !== null && $costo < 0) {
                trabajoLaboratorioLanzar('costo_invalido', 'El costo estimado no puede ser negativo.');
            }

            $ventaCodigo = array(
                'cod_venta' => intval($detalle['cod_ventaFK']),
                'num_factura' => $detalle['num_factura'],
                'puntoexpedicion' => $detalle['puntoexpedicion'],
                'nroventa' => $detalle['nroventa'],
                'cod_local' => intval($detalle['cod_local']),
                'nombre_local' => $detalle['nombre_local']
            );
            $numeroVenta = trabajoLaboratorioNumeroVenta($ventaCodigo);
            $sigla = trabajoLaboratorioSiglaLocal(intval($detalle['cod_local']), $detalle['nombre_local']);
            $observacionLegacy = trabajoLaboratorioTextoBaseDatos($instrucciones, 150);
            $colorBd = trabajoLaboratorioTextoBaseDatos($color, 12);
            $estadoLegacy = 'pendiente';
            $codVenta = intval($detalle['cod_ventaFK']);
            $codLocal = intval($detalle['cod_local']);
            $codMecanico = intval($tecnico['cod_mecanico_dental']);
            $codEspecialista = intval($codUsuario);
            $valoresLegacy = array(
                $codVenta, $codTipo, $observacionLegacy, $colorBd, $costo,
                $estadoLegacy, $codUsuario, $codEspecialista, $codLocal, $codMecanico
            );
            $stmt = $mysqli->prepare(
                'INSERT INTO trabajo_mecanico_dental '
                .'(cod_ventaFK,cod_tipo_trabajoFK,observacion,colorimetro,costo,estado,'
                .'cod_usuarioFK_create,cod_especialistaFK,cod_localFK,cod_mecanicoDentalFK) '
                .'VALUES (?,?,?,?,?,?,?,?,?,?)'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar('legado_no_guardado', 'No se pudo conservar el registro compatible del trabajo.');
            }
            trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valoresLegacy)), $valoresLegacy);
            if (!$stmt->execute()) {
                $stmt->close();
                trabajoLaboratorioLanzar('legado_no_guardado', 'No se pudo conservar el registro compatible del trabajo.');
            }
            $idLegacy = intval($stmt->insert_id);
            $stmt->close();

            $numeroBd = trabajoLaboratorioTextoBaseDatos($numeroVenta, 45);
            $siglaBd = trabajoLaboratorioTextoBaseDatos($sigla, 10);
            $productoBd = trabajoLaboratorioTextoBaseDatos($detalle['cod_productoFK'], 45);
            $estado = 'pendiente_entrega_mecanico';
            $dias = 30;
            $instruccionesBd = trabajoLaboratorioTextoBaseDatos($instrucciones, 1000);
            $codConsulta = $origen['cod_consulta_origen'];
            $codEvolucion = $origen['cod_evolucion_origen'];
            $valores = array(
                $idLegacy, $codVenta, $numeroBd, $siglaBd, $codDetalle, $codDetalle,
                intval($detalle['cod_clienteFK']), $productoBd, $codTipo, $codConsulta,
                $codEvolucion, intval($hilo['cod_interConsultaFK']), $codLocal, $codMecanico,
                $codTecnico, $codUsuario, $estado, $dias, trabajoLaboratorioTextoBaseDatos($color, 30),
                $instruccionesBd, $costo, $codUsuario, $codUsuario
            );
            $stmt = $mysqli->prepare(
                'INSERT INTO trabajo_laboratorio '
                .'(cod_trabajo_mecanico_legacyFK,cod_ventaFK,numero_venta_snapshot,sigla_local_snapshot,'
                .'cod_detalle_ventaFK,cod_detalle_activo_unico,cod_clienteFK,cod_productoFK,cod_tipo_trabajoFK,'
                .'cod_consulta_origenFK,cod_evolucion_origenFK,cod_interConsultaFK,cod_localFK,'
                .'cod_mecanico_dentalFK,cod_tecnico_usuarioFK,cod_custodio_actualFK,ciclo_actual,estado_derivado,'
                .'fecha_objetivo,colorimetro,instrucciones,costo_estimado,version,fecha_creacion,cod_usuarioFK_create,'
                .'fecha_actualizacion,cod_usuarioFK_update) '
                .'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,DATE_ADD(NOW(),INTERVAL ? DAY),?,?,?,1,NOW(),?,NOW(),?)'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar('trabajo_no_guardado', 'No se pudo crear el trabajo de laboratorio.');
            }
            trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valores)), $valores);
            if (!$stmt->execute()) {
                $errno = intval($stmt->errno);
                $stmt->close();
                if ($errno === 1062) {
                    trabajoLaboratorioLanzar('trabajo_activo_existente', 'Este detalle ya tiene un trabajo de laboratorio activo.');
                }
                trabajoLaboratorioLanzar('trabajo_no_guardado', 'No se pudo crear el trabajo de laboratorio.');
            }
            $idTrabajo = intval($stmt->insert_id);
            $stmt->close();

            $codigo = trabajoLaboratorioCodigoVisible($ventaCodigo, $idTrabajo);
            $codigoBd = trabajoLaboratorioTextoBaseDatos($codigo, 100);
            $stmt = $mysqli->prepare('UPDATE trabajo_laboratorio SET codigo_visible=? WHERE id=? LIMIT 1');
            $stmt->bind_param('si', $codigoBd, $idTrabajo);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('codigo_no_guardado', 'No se pudo asignar el codigo trazable del trabajo.');
            }
            $stmt->close();

            $stmt = $mysqli->prepare(
                "INSERT INTO trabajo_laboratorio_ciclo "
                ."(id_trabajoFK,numero_ciclo,tipo,fecha_objetivo,cod_usuario_solicitanteFK,fecha_creacion) "
                ."VALUES (?,1,'original',DATE_ADD(NOW(),INTERVAL ? DAY),?,NOW())"
            );
            $stmt->bind_param('iii', $idTrabajo, $dias, $codUsuario);
            if (!$stmt->execute()) {
                $stmt->close();
                trabajoLaboratorioLanzar('ciclo_no_guardado', 'No se pudo crear el ciclo inicial del trabajo.');
            }
            $idCiclo = intval($stmt->insert_id);
            $stmt->close();

            foreach ($ubicaciones as $ubicacion) {
                $idLink = intval($ubicacion['id']);
                $pieza = trabajoLaboratorioTextoBaseDatos($ubicacion['pieza'], 5);
                $piezasJson = trabajoLaboratorioTextoBaseDatos(json_encode($ubicacion['piezas']));
                $superficiesJson = trabajoLaboratorioTextoBaseDatos(json_encode($ubicacion['superficies']));
                $denticion = trabajoLaboratorioTextoBaseDatos($ubicacion['denticion'], 20);
                $arcada = trabajoLaboratorioTextoBaseDatos($ubicacion['arcada'], 30);
                $cuadrante = trabajoLaboratorioTextoBaseDatos($ubicacion['cuadrante'], 30);
                $boca = !empty($ubicacion['boca_completa']) ? 1 : 0;
                $alcance = trabajoLaboratorioTextoBaseDatos($ubicacion['alcance'], 40);
                $stmt = $mysqli->prepare(
                    'INSERT INTO trabajo_laboratorio_ubicacion '
                    .'(id_trabajoFK,id_odontograma_link_origenFK,pieza,piezas_json,superficies_json,denticion,'
                    .'arcada,cuadrante,boca_completa,alcance_odontologico,fecha_creacion,cod_usuarioFK_create) '
                    .'VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)'
                );
                $valoresUbicacion = array(
                    $idTrabajo, $idLink, $pieza, $piezasJson, $superficiesJson, $denticion,
                    $arcada, $cuadrante, $boca, $alcance, $codUsuario
                );
                trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valoresUbicacion)), $valoresUbicacion);
                if (!$stmt->execute()) {
                    $stmt->close();
                    trabajoLaboratorioLanzar('ubicacion_no_guardada', 'No se pudo conservar la ubicacion clinica del trabajo.');
                }
                $stmt->close();
            }

            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacionInicio = isset($entrada['observacion'])
                ? trabajoLaboratorioTextoEntrada($entrada['observacion'], 750) : '';
            if ($observacionInicio === '' && $motivoExcepcionInicio !== '') {
                $observacionInicio = $motivoExcepcionInicio;
            }
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $trabajo, $idCiclo, $idIdempotencia, 'trabajo_iniciado', $codUsuario, 1,
                $observacionInicio,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array('modo_individualizacion' => $config['modo_individualizacion']),
                    $motivoExcepcionInicio
                ),
                null, null, $codUsuario, $codUsuario, $codTecnico, $codConsulta, $codEvolucion
            );
            $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
            trabajoLaboratorioInsertarMedia($mysqli, $trabajo, $idCiclo, $idEvento, $codUsuario, $media, 'inicial');
            foreach ($evidenciasAdicionales as $evidenciaAdicional) {
                $mediaAdicional = trabajoLaboratorioGuardarMediaProtegida($evidenciaAdicional, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli, $trabajo, $idCiclo, $idEvento, $codUsuario, $mediaAdicional, 'inicial_adicional'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'trabajo_iniciado', 'El trabajo de laboratorio fue iniciado.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioInsertarTransferencia($mysqli, $trabajo, $idCiclo, $tipo, $codUsuario,
    $codDestinatario, $codLocalDestino, $observacion)
{
    $idTrabajo = intval($trabajo['id']);
    $idCiclo = intval($idCiclo);
    $custodio = intval($trabajo['cod_custodio_actualFK']);
    $usuarioCustodio = trabajoLaboratorioUsuario($mysqli, $custodio);
    $localOrigen = $usuarioCustodio && intval($usuarioCustodio['cod_localFK']) > 0
        ? intval($usuarioCustodio['cod_localFK']) : intval($trabajo['cod_localFK']);
    $tipoBd = trabajoLaboratorioTextoBaseDatos($tipo, 35);
    $observacionBd = trabajoLaboratorioTextoBaseDatos($observacion, 500);
    $stmt = $mysqli->prepare(
        'INSERT INTO trabajo_laboratorio_transferencia '
        .'(id_trabajoFK,id_cicloFK,tipo,cod_custodio_anteriorFK,cod_remitenteFK,'
        .'cod_destinatario_previstoFK,cod_local_origenFK,cod_local_destinoFK,observacion,'
        .'cod_usuarioFK_create,fecha_creacion) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('transferencia_no_guardada', 'No se pudo iniciar la transferencia.');
    }
    $stmt->bind_param(
        'iisiiiiisi',
        $idTrabajo, $idCiclo, $tipoBd, $custodio, $custodio, $codDestinatario,
        $localOrigen, $codLocalDestino, $observacionBd, $codUsuario
    );
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('transferencia_no_guardada', 'No se pudo iniciar la transferencia.');
    }
    $id = intval($stmt->insert_id);
    $stmt->close();
    return $id;
}

function trabajoLaboratorioObtenerTransferenciaPendiente($mysqli, $trabajo)
{
    $id = intval($trabajo['id_transferencia_pendienteFK']);
    if ($id <= 0) {
        trabajoLaboratorioLanzar('transferencia_pendiente_no_encontrada', 'El trabajo no tiene una transferencia pendiente.');
    }
    $stmt = $mysqli->prepare(
        'SELECT * FROM trabajo_laboratorio_transferencia WHERE id=? AND id_trabajoFK=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('transferencia_no_disponible', 'No se pudo consultar la transferencia.');
    }
    $idTrabajo = intval($trabajo['id']);
    $stmt->bind_param('ii', $id, $idTrabajo);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar('transferencia_pendiente_no_encontrada', 'No se encontro la transferencia pendiente.');
    }
    return $fila;
}

function trabajoLaboratorioActualizarLegado($mysqli, $trabajo, $tipoEvento, $codUsuario)
{
    $idLegacy = intval($trabajo['cod_trabajo_mecanico_legacyFK']);
    if ($idLegacy <= 0) {
        return;
    }
    if ($tipoEvento === 'recepcion_mecanico_confirmada') {
        $sql = "UPDATE trabajo_mecanico_dental SET estado='retirado',fecha_retiro=COALESCE(fecha_retiro,CURDATE()),"
            .'fecha_edit=NOW(),cod_usuarioFK_edit=? WHERE cod_trabajo_mecanico_dental=? LIMIT 1';
    } elseif ($tipoEvento === 'devolucion_iniciada') {
        $sql = "UPDATE trabajo_mecanico_dental SET estado='entregado',fecha_entrega=COALESCE(fecha_entrega,CURDATE()),"
            .'fecha_edit=NOW(),cod_usuarioFK_edit=? WHERE cod_trabajo_mecanico_dental=? LIMIT 1';
    } elseif ($tipoEvento === 'trabajo_cancelado') {
        $sql = "UPDATE trabajo_mecanico_dental SET estado='inactivo',fecha_edit=NOW(),cod_usuarioFK_edit=? "
            .'WHERE cod_trabajo_mecanico_dental=? LIMIT 1';
    } else {
        return;
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('legado_no_actualizado', 'No se pudo actualizar la compatibilidad del trabajo.');
    }
    $stmt->bind_param('ii', $codUsuario, $idLegacy);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('legado_no_actualizado', 'No se pudo actualizar la compatibilidad del trabajo.');
    }
    $stmt->close();
}

function trabajoLaboratorioIniciarTransferencia($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'iniciarTransferencia', $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'iniciarTransferencia', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $tecnico = trabajoLaboratorioObtenerTecnicoFormal($mysqli, intval($trabajo['cod_tecnico_usuarioFK']), true);
            if (!$tecnico) {
                trabajoLaboratorioLanzar('tecnico_formal_invalido', 'El tecnico asignado ya no esta disponible.');
            }
            if (!trabajoLaboratorioTienePermiso($mysqli, intval($tecnico['cod_usuarioFK']), 'VERTRABAJOSLABORATORIO')
                || !trabajoLaboratorioTienePermiso($mysqli, intval($tecnico['cod_usuarioFK']), 'RECIBIRTRABAJOLABORATORIO')) {
                trabajoLaboratorioLanzar(
                    'tecnico_sin_permisos_recepcion',
                    'El tecnico asignado necesita permisos de acceso y recepcion antes de iniciar la entrega.'
                );
            }
            $codDestinatarioEntrada = trabajoLaboratorioEntero(isset($entrada['cod_destinatario'])
                ? $entrada['cod_destinatario'] : 0);
            if ($codDestinatarioEntrada <= 0
                || $codDestinatarioEntrada !== intval($tecnico['cod_usuarioFK'])) {
                trabajoLaboratorioLanzar('destinatario_no_autorizado', 'Seleccione al tecnico asignado como destinatario fisico.');
            }
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            if (count($evidencias) < 1 || count($evidencias) > 5) {
                trabajoLaboratorioLanzar('evidencia_transferencia_requerida', 'Adjunte entre una y cinco fotos de la entrega.');
            }
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            $localDestino = intval($tecnico['cod_localFK']) > 0
                ? intval($tecnico['cod_localFK']) : intval($trabajo['cod_localFK']);
            $idTransferencia = trabajoLaboratorioInsertarTransferencia(
                $mysqli, $trabajo, intval($ciclo['id']), 'clinica_a_laboratorio', $codUsuario,
                intval($tecnico['cod_usuarioFK']), $localDestino, $observacion
            );
            $versionNueva = intval($trabajo['version']) + 1;
            $estado = 'en_transferencia_mecanico';
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,id_transferencia_pendienteFK=?,'
                .'version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? WHERE id=? AND version=? LIMIT 1'
            );
            $versionAnterior = intval($trabajo['version']);
            $stmt->bind_param('siiiii', $estado, $idTransferencia, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de iniciar la transferencia.');
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia,
                'transferencia_mecanico_iniciada', $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(array(), $motivoExcepcion),
                $idTransferencia, intval($trabajo['cod_custodio_actualFK']),
                null, intval($trabajo['cod_custodio_actualFK']), intval($tecnico['cod_usuarioFK'])
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli, $actual, intval($ciclo['id']), $idEvento, $codUsuario, $media, 'entrega_laboratorio'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'transferencia_iniciada', 'El envio al laboratorio fue iniciado.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioConfirmarRecepcion($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'confirmarRecepcion', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'confirmarRecepcion', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $transferencia = trabajoLaboratorioObtenerTransferenciaPendiente($mysqli, $trabajo);
            if ($transferencia['tipo'] !== 'clinica_a_laboratorio') {
                trabajoLaboratorioLanzar('tipo_transferencia_invalido', 'La transferencia pendiente no corresponde al ingreso al laboratorio.');
            }
            if (intval($transferencia['cod_destinatario_previstoFK']) !== intval($codUsuario)
                && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
                trabajoLaboratorioLanzar('destinatario_no_autorizado', 'Solo el tecnico destinatario puede confirmar la recepcion.');
            }
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionNueva = intval($trabajo['version']) + 1;
            $estado = 'en_laboratorio';
            $nuevoCustodio = intval($transferencia['cod_destinatario_previstoFK']);
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,cod_custodio_actualFK=?,id_transferencia_pendienteFK=NULL,'
                .'fecha_retiro=COALESCE(fecha_retiro,NOW()),version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                .'WHERE id=? AND version=? LIMIT 1'
            );
            $versionAnterior = intval($trabajo['version']);
            $stmt->bind_param('siiiii', $estado, $nuevoCustodio, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de confirmar la recepcion.');
            }
            $stmt->close();
            trabajoLaboratorioActualizarLegado($mysqli, $trabajo, 'recepcion_mecanico_confirmada', $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia,
                'recepcion_mecanico_confirmada', $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(array(), $motivoExcepcion),
                intval($transferencia['id']), intval($trabajo['cod_custodio_actualFK']),
                $nuevoCustodio, intval($transferencia['cod_remitenteFK']), $nuevoCustodio
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'recepcion_confirmada', 'La recepcion en laboratorio fue confirmada.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioIncrementarVersion($mysqli, $trabajo, $codUsuario)
{
    $idTrabajo = intval($trabajo['id']);
    $versionAnterior = intval($trabajo['version']);
    $versionNueva = $versionAnterior + 1;
    $stmt = $mysqli->prepare(
        'UPDATE trabajo_laboratorio SET version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
        .'WHERE id=? AND version=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('trabajo_no_actualizado', 'No se pudo actualizar el trabajo.');
    }
    $stmt->bind_param('iiii', $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de completar la operacion.');
    }
    $stmt->close();
    return $versionNueva;
}

function trabajoLaboratorioAgregarEvidencia($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'agregarEvidencia', $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            if (count($evidencias) === 0 && isset($entrada['evidencia'])) {
                $singular = trabajoLaboratorioDecodificarJson($entrada['evidencia'], array());
                if (isset($singular['data_base64'])) {
                    $evidencias = array($singular);
                }
            }
            if (count($evidencias) < 1 || count($evidencias) > 5) {
                trabajoLaboratorioLanzar('cantidad_evidencias_invalida', 'Adjunte entre una y cinco imagenes por operacion.');
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'agregarEvidencia', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionNueva = trabajoLaboratorioIncrementarVersion($mysqli, $trabajo, $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'evidencia_agregada',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array('cantidad' => count($evidencias)),
                    $motivoExcepcion
                )
            );
            $tipo = trabajoLaboratorioNormalizarTexto(isset($entrada['tipo_media']) ? $entrada['tipo_media'] : 'progreso');
            if ($tipo === '') {
                $tipo = 'progreso';
            }
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli, $actual, intval($ciclo['id']), $idEvento, $codUsuario, $media, $tipo
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'evidencia_agregada', 'La evidencia fue agregada al historial.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioAgregarNota($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'agregarNota', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $nota = trabajoLaboratorioTextoEntrada(isset($entrada['nota']) ? $entrada['nota']
                : (isset($entrada['observacion']) ? $entrada['observacion'] : ''), 750);
            if ($nota === '') {
                trabajoLaboratorioLanzar('nota_requerida', 'Escriba la nota que desea registrar.');
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'agregarNota', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionNueva = trabajoLaboratorioIncrementarVersion($mysqli, $trabajo, $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'nota_agregada',
                $codUsuario, $versionNueva, $nota,
                trabajoLaboratorioMetadataExcepcionAuditor(array(), $motivoExcepcion)
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'nota_agregada', 'La nota fue agregada al historial.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioEvidenciasFinales($entrada)
{
    $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
    foreach (array('evidencia_final', 'evidencia_completado', 'evidencia') as $clave) {
        if (count($evidencias) > 0 || !isset($entrada[$clave])) {
            continue;
        }
        $singular = trabajoLaboratorioDecodificarJson($entrada[$clave], array());
        if (isset($singular['data_base64'])) {
            $evidencias = array($singular);
        }
    }
    return $evidencias;
}

function trabajoLaboratorioIniciarDevolucion($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'iniciarDevolucion', $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $evidencias = trabajoLaboratorioEvidenciasFinales($entrada);
            if (count($evidencias) < 1 || count($evidencias) > 5) {
                trabajoLaboratorioLanzar('evidencia_final_requerida', 'Adjunte entre una y cinco fotos del trabajo terminado.');
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'iniciarDevolucion', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $codDestinatario = trabajoLaboratorioEntero(isset($entrada['cod_destinatario_usuario'])
                ? $entrada['cod_destinatario_usuario']
                : (isset($entrada['cod_destinatario']) ? $entrada['cod_destinatario'] : $trabajo['cod_usuarioFK_create']));
            $destinatario = trabajoLaboratorioUsuario($mysqli, $codDestinatario);
            if (!$destinatario || intval($destinatario['cod_localFK']) !== intval($trabajo['cod_localFK'])
                || trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codDestinatario, false)
                || !trabajoLaboratorioTienePermiso($mysqli, $codDestinatario, 'VERTRABAJOSLABORATORIO')
                || !trabajoLaboratorioTienePermiso($mysqli, $codDestinatario, 'RECIBIRTRABAJOLABORATORIO')) {
                trabajoLaboratorioLanzar('destinatario_clinica_invalido', 'Seleccione un usuario activo de la sucursal de origen.');
            }
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            $idTransferencia = trabajoLaboratorioInsertarTransferencia(
                $mysqli, $trabajo, intval($ciclo['id']), 'laboratorio_a_clinica', $codUsuario,
                $codDestinatario, intval($trabajo['cod_localFK']), $observacion
            );
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'en_transferencia_clinica';
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,id_transferencia_pendienteFK=?,'
                .'fecha_entrega=COALESCE(fecha_entrega,NOW()),version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                .'WHERE id=? AND version=? LIMIT 1'
            );
            $stmt->bind_param('siiiii', $estado, $idTransferencia, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de iniciar la devolucion.');
            }
            $stmt->close();
            trabajoLaboratorioActualizarLegado($mysqli, $trabajo, 'devolucion_iniciada', $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'devolucion_iniciada',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array('cantidad_evidencias' => count($evidencias)),
                    $motivoExcepcion
                ),
                $idTransferencia, intval($trabajo['cod_custodio_actualFK']),
                null, intval($trabajo['cod_custodio_actualFK']), $codDestinatario
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli, $actual, intval($ciclo['id']), $idEvento, $codUsuario, $media, 'trabajo_terminado'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'devolucion_iniciada', 'La devolucion a la clinica fue iniciada.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioConfirmarDevolucion($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'confirmarDevolucion', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'confirmarDevolucion', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $transferencia = trabajoLaboratorioObtenerTransferenciaPendiente($mysqli, $trabajo);
            if ($transferencia['tipo'] !== 'laboratorio_a_clinica') {
                trabajoLaboratorioLanzar('tipo_transferencia_invalido', 'La transferencia pendiente no corresponde al retorno a la clinica.');
            }
            $actuaEnRepresentacion = intval($transferencia['cod_destinatario_previstoFK']) !== intval($codUsuario);
            $representanteClinica = trabajoLaboratorioUsuarioPuedeOperarLocal(
                $mysqli,
                $codUsuario,
                intval($trabajo['cod_localFK'])
            ) && !trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false);
            if ($actuaEnRepresentacion && !$representanteClinica
                && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
                trabajoLaboratorioLanzar(
                    'destinatario_no_autorizado',
                    'Solo el destinatario previsto o un receptor autorizado de la sucursal puede confirmar la devolucion.'
                );
            }
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'pendiente_revision';
            $nuevoCustodio = intval($transferencia['cod_destinatario_previstoFK']);
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,cod_custodio_actualFK=?,'
                .'id_transferencia_pendienteFK=NULL,version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                .'WHERE id=? AND version=? LIMIT 1'
            );
            $stmt->bind_param('siiiii', $estado, $nuevoCustodio, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de confirmar la devolucion.');
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'devolucion_confirmada',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array('actuo_en_representacion' => $actuaEnRepresentacion ? 1 : 0),
                    $motivoExcepcion
                ),
                intval($transferencia['id']),
                intval($trabajo['cod_custodio_actualFK']), $nuevoCustodio,
                intval($transferencia['cod_remitenteFK']), $nuevoCustodio
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'devolucion_confirmada', 'La clinica confirmo la recepcion del trabajo.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioSolicitarAjuste($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'solicitarAjuste', $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $motivo = trabajoLaboratorioNormalizarTexto(isset($entrada['motivo']) ? $entrada['motivo'] : '');
            $permitidos = array(
                'adaptacion', 'oclusion', 'color', 'forma', 'medida_tamano', 'terminacion',
                'fractura_dano', 'instruccion_clinica_incompleta', 'otro',
                /* Alias aceptados para clientes previos; se conserva trazabilidad sin reescribir. */
                'ajuste_color', 'ajuste_forma', 'ajuste_tamano', 'ajuste_adaptacion', 'reparacion', 'repeticion'
            );
            if (!in_array($motivo, $permitidos, true)) {
                trabajoLaboratorioLanzar('motivo_ajuste_invalido', 'Seleccione un motivo de ajuste valido.');
            }
            $motivoOtro = trabajoLaboratorioTextoEntrada(isset($entrada['motivo_otro']) ? $entrada['motivo_otro'] : '', 80);
            if ($motivo === 'otro' && $motivoOtro === '') {
                trabajoLaboratorioLanzar('motivo_ajuste_requerido', 'Especifique el motivo del ajuste.');
            }
            $motivoGuardado = $motivo === 'otro' ? $motivoOtro : $motivo;
            $justificacion = trabajoLaboratorioTextoEntrada(isset($entrada['justificacion']) ? $entrada['justificacion'] : '', 500);
            if (strlen($justificacion) < 5) {
                trabajoLaboratorioLanzar('justificacion_ajuste_requerida', 'Explique brevemente que debe ajustarse.');
            }
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            if (count($evidencias) > 5) {
                trabajoLaboratorioLanzar('cantidad_evidencias_invalida', 'Puede adjuntar hasta cinco imagenes por ajuste.');
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'solicitarAjuste', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $nuevoCiclo = intval($trabajo['ciclo_actual']) + 1;
            $dias = 30;
            $tipo = 'ajuste';
            $motivoBd = trabajoLaboratorioTextoBaseDatos($motivoGuardado, 80);
            $justificacionBd = trabajoLaboratorioTextoBaseDatos($justificacion, 500);
            $stmt = $mysqli->prepare(
                'INSERT INTO trabajo_laboratorio_ciclo '
                .'(id_trabajoFK,numero_ciclo,tipo,motivo,justificacion,fecha_objetivo,'
                .'cod_usuario_solicitanteFK,fecha_creacion) '
                .'VALUES (?,?,?,?,?,DATE_ADD(NOW(),INTERVAL ? DAY),?,NOW())'
            );
            $stmt->bind_param('iisssii', $idTrabajo, $nuevoCiclo, $tipo, $motivoBd, $justificacionBd, $dias, $codUsuario);
            if (!$stmt->execute()) {
                $stmt->close();
                trabajoLaboratorioLanzar('ciclo_ajuste_no_guardado', 'No se pudo crear el ciclo de ajuste.');
            }
            $idCiclo = intval($stmt->insert_id);
            $stmt->close();
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'ajuste_solicitado';
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,ciclo_actual=?,'
                .'version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? WHERE id=? AND version=? LIMIT 1'
            );
            $stmt->bind_param(
                'siiiii', $estado, $nuevoCiclo, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de registrar el ajuste.');
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, $idCiclo, $idIdempotencia, 'ajuste_solicitado', $codUsuario,
                $versionNueva, $justificacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array('motivo' => $motivoGuardado),
                    $motivoExcepcion
                )
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli, $actual, $idCiclo, $idEvento, $codUsuario, $media, 'ajuste_solicitado'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'ajuste_solicitado', 'El ajuste fue registrado como un ciclo nuevo.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioAprobar($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'aprobarTrabajo', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'aprobarTrabajo', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'listo_instalacion';
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,'
                .'fecha_completado=COALESCE(fecha_completado,NOW()),version=?,fecha_actualizacion=NOW(),'
                .'cod_usuarioFK_update=? WHERE id=? AND version=? LIMIT 1'
            );
            $stmt->bind_param('siiii', $estado, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de aprobarlo.');
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'trabajo_aprobado',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(array(), $motivoExcepcion)
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'trabajo_aprobado', 'El trabajo fue aprobado y esta listo para instalar.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioRegistrarInstalacion($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'registrarInstalacion', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'registrarInstalacion', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $esExcepcionAuditor = trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
                && $motivoExcepcion !== '';
            if (trabajoLaboratorioEntero(isset($entrada['cod_evolucion_origen'])
                ? $entrada['cod_evolucion_origen'] : 0) <= 0) {
                trabajoLaboratorioLanzar(
                    'evolucion_origen_requerida',
                    'La instalacion debe registrarse expresamente dentro de una evolucion clinica.'
                );
            }
            $origen = trabajoLaboratorioValidarEvolucionInstalacion(
                $mysqli,
                $trabajo,
                $codUsuario,
                isset($entrada['cod_consulta_origen']) ? $entrada['cod_consulta_origen'] : 0,
                isset($entrada['cod_evolucion_origen']) ? $entrada['cod_evolucion_origen'] : 0,
                $esExcepcionAuditor
            );
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'instalado';
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,cod_custodio_actualFK=?,'
                .'cod_detalle_activo_unico=NULL,fecha_instalado=NOW(),version=?,fecha_actualizacion=NOW(),'
                .'cod_usuarioFK_update=? WHERE id=? AND version=? LIMIT 1'
            );
            $stmt->bind_param('siiiii', $estado, $codUsuario, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de registrar la instalacion.');
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'instalacion_registrada',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array(
                        'evolucion_clinica_explicita' => 1,
                        'cod_usuario_evolucion' => intval($origen['cod_usuario_evolucion'])
                    ),
                    $motivoExcepcion
                ),
                null,
                intval($trabajo['cod_custodio_actualFK']), $codUsuario, null, null,
                $origen['cod_consulta_origen'], $origen['cod_evolucion_origen']
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'instalacion_registrada', 'La instalacion quedo vinculada a la evolucion clinica.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioCancelar($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'cancelarTrabajo', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $motivo = trabajoLaboratorioTextoEntrada(isset($entrada['justificacion']) ? $entrada['justificacion']
                : (isset($entrada['motivo']) ? $entrada['motivo'] : ''), 500);
            if (strlen($motivo) < 5) {
                trabajoLaboratorioLanzar('motivo_cancelacion_requerido', 'Explique el motivo de la cancelacion.');
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'cancelarTrabajo', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'cancelado';
            $motivoBd = trabajoLaboratorioTextoBaseDatos($motivo, 500);
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,cod_detalle_activo_unico=NULL,'
                .'id_transferencia_pendienteFK=NULL,fecha_cancelado=NOW(),motivo_cancelacion=?,version=?,'
                .'fecha_actualizacion=NOW(),cod_usuarioFK_update=? WHERE id=? AND version=? LIMIT 1'
            );
            $stmt->bind_param('ssiiii', $estado, $motivoBd, $versionNueva, $codUsuario, $idTrabajo, $versionAnterior);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de cancelarlo.');
            }
            $stmt->close();
            trabajoLaboratorioActualizarLegado($mysqli, $trabajo, 'trabajo_cancelado', $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'trabajo_cancelado',
                $codUsuario, $versionNueva, $motivo,
                trabajoLaboratorioMetadataExcepcionAuditor(array(), $motivoExcepcion)
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'trabajo_cancelado', 'El trabajo fue cancelado sin eliminar su trazabilidad.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

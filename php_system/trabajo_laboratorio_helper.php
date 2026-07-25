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
        'trabajo_laboratorio_media',
        'trabajo_laboratorio_regularizacion',
        'trabajo_laboratorio_regularizacion_unidad'
    );
    foreach ($tablas as $tabla) {
        if (!trabajoLaboratorioTablaExiste($mysqli, $tabla)) {
            return false;
        }
    }
    return trabajoLaboratorioConfiguracionDisponible($mysqli)
        && trabajoLaboratorioColumnaExiste($mysqli, 'mecanico_dental', 'cod_usuarioFK')
        && trabajoLaboratorioColumnaExiste($mysqli, 'trabajo_laboratorio', 'cod_especialistaFK')
        && trabajoLaboratorioColumnaExiste($mysqli, 'trabajo_laboratorio', 'codigo_origen')
        && trabajoLaboratorioColumnaExiste($mysqli, 'trabajo_laboratorio', 'unidad_origen')
        && trabajoLaboratorioColumnaExiste($mysqli, 'trabajo_laboratorio', 'cantidad_unidades_origen')
        && trabajoLaboratorioColumnaExiste($mysqli, 'trabajo_laboratorio', 'id_regularizacion_unidadFK');
}

function trabajoLaboratorioHiloCustodiaDisponible($mysqli)
{
    return trabajoLaboratorioColumnaExiste(
        $mysqli,
        'trabajo_laboratorio',
        'id_evento_custodia_actualFK'
    ) && trabajoLaboratorioColumnaExiste(
        $mysqli,
        'trabajo_laboratorio_evento',
        'id_evento_custodiaFK'
    ) && trabajoLaboratorioColumnaExiste(
        $mysqli,
        'trabajo_laboratorio_evento',
        'actor_nombre_snapshot'
    ) && trabajoLaboratorioColumnaExiste(
        $mysqli,
        'trabajo_laboratorio_evento',
        'actor_rol_snapshot'
    ) && trabajoLaboratorioColumnaExiste(
        $mysqli,
        'trabajo_laboratorio_evento',
        'local_nombre_snapshot'
    );
}

function trabajoLaboratorioExigirHiloCustodiaDisponible($mysqli)
{
    if (!trabajoLaboratorioHiloCustodiaDisponible($mysqli)) {
        trabajoLaboratorioLanzar(
            'hilo_custodia_no_instalado',
            'El seguimiento de custodia todavia no esta instalado en la base de datos.'
        );
    }
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
        "SELECT u.cod_usuario,u.cod_localFK,u.tipo,u.estado,u.url,p.nombre_persona,l.Nombre AS nombre_local "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
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

function trabajoLaboratorioUsuarioPuedeGestionarCosto($mysqli, $codUsuario)
{
    if (trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)) {
        return true;
    }
    if (trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false)) {
        return false;
    }
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    $rol = $usuario ? trabajoLaboratorioNormalizarTexto($usuario['tipo']) : '';
    return in_array($rol, array('administrativo', 'administrador'), true);
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

function trabajoLaboratorioUsuarioEsDestinoTransferenciaPendiente($mysqli, $codUsuario, $trabajo)
{
    if (!$trabajo || !trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_transferencia')) {
        return false;
    }
    $idTransferencia = isset($trabajo['id_transferencia_pendienteFK'])
        ? intval($trabajo['id_transferencia_pendienteFK']) : 0;
    $idTrabajo = isset($trabajo['id']) ? intval($trabajo['id']) : 0;
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    $codLocal = $usuario ? intval($usuario['cod_localFK']) : 0;
    if ($idTransferencia <= 0 || $idTrabajo <= 0 || $codLocal <= 0) {
        return false;
    }
    $stmt = $mysqli->prepare(
        'SELECT 1 FROM trabajo_laboratorio_transferencia '
        .'WHERE id=? AND id_trabajoFK=? AND cod_local_destinoFK=? LIMIT 1'
    );
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('iii', $idTransferencia, $idTrabajo, $codLocal);
    $stmt->execute();
    $esDestino = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $esDestino;
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
    if ($modo === 'pieza_individual' && count($piezas) < 1) {
        return array('codigo' => 'pieza_individual_invalida', 'mensaje' => 'Debe seleccionar al menos una pieza dental para este tratamiento.');
    }
    if ($modo === 'multipieza' && count($piezas) < 2) {
        return array('codigo' => 'multipieza_invalida', 'mensaje' => 'El modo multipieza debe conservar dos o mas piezas en la misma ubicacion clinica.');
    }
    if ($modo === 'arcada' && !$tieneArcada && count($piezas) < 1) {
        return array('codigo' => 'arcada_requerida', 'mensaje' => 'El modo arcada necesita una arcada registrada.');
    }
    if ($modo === 'sector' && !$tieneSector && count($piezas) < 2) {
        return array('codigo' => 'sector_requerido', 'mensaje' => 'El modo sector necesita un sector, cuadrante o conjunto de piezas registrado.');
    }
    return null;
}

function trabajoLaboratorioCantidadAgrupadaDetalle($detalle)
{
    $cantidad = isset($detalle['cantidad_detalle']) ? floatval($detalle['cantidad_detalle']) : 0;
    $entero = intval(round($cantidad));
    if ($entero < 2 || $entero > 32 || abs($cantidad - $entero) >= 0.000001) {
        return 0;
    }
    return $entero;
}

function trabajoLaboratorioPiezaDentalValida($pieza)
{
    $pieza = trim((string)$pieza);
    if (!preg_match('/^[1-8][1-8]$/', $pieza)) {
        return false;
    }
    $cuadrante = intval(substr($pieza, 0, 1));
    $posicion = intval(substr($pieza, 1, 1));
    return ($cuadrante <= 4 && $posicion <= 8)
        || ($cuadrante >= 5 && $cuadrante <= 8 && $posicion <= 5);
}

function trabajoLaboratorioNormalizarUnidadesRegularizacion($valor, $cantidad, $modo)
{
    $unidades = trabajoLaboratorioDecodificarJson($valor, array());
    if (!is_array($unidades) || count($unidades) !== intval($cantidad)) {
        trabajoLaboratorioLanzar(
            'unidades_regularizacion_incompletas',
            'Debe designar las piezas de cada trabajo antes de confirmar la regularizacion.'
        );
    }
    $salida = array();
    $numeros = array();
    foreach ($unidades as $indice => $unidad) {
        if (!is_array($unidad)) {
            trabajoLaboratorioLanzar('unidad_regularizacion_invalida', 'Una de las unidades seleccionadas no es valida.');
        }
        $numero = trabajoLaboratorioEntero(isset($unidad['numero_unidad'])
            ? $unidad['numero_unidad'] : (isset($unidad['unidad']) ? $unidad['unidad'] : $indice + 1));
        if ($numero < 1 || $numero > intval($cantidad) || isset($numeros[$numero])) {
            trabajoLaboratorioLanzar('unidad_regularizacion_invalida', 'La numeracion de los trabajos no es valida.');
        }
        $numeros[$numero] = true;
        $piezasEntrada = isset($unidad['piezas']) ? $unidad['piezas'] : array();
        if (!is_array($piezasEntrada)) {
            $piezasEntrada = trabajoLaboratorioDecodificarJson($piezasEntrada, array());
        }
        if (isset($unidad['pieza']) && trim((string)$unidad['pieza']) !== '') {
            array_unshift($piezasEntrada, $unidad['pieza']);
        }
        $piezas = array();
        foreach ($piezasEntrada as $piezaEntrada) {
            $pieza = trim((string)$piezaEntrada);
            if (!trabajoLaboratorioPiezaDentalValida($pieza)) {
                trabajoLaboratorioLanzar(
                    'pieza_regularizacion_invalida',
                    'Una de las piezas seleccionadas no pertenece al odontograma.'
                );
            }
            if (!in_array($pieza, $piezas, true)) {
                $piezas[] = $pieza;
            }
        }
        $denticion = trabajoLaboratorioTextoEntrada(isset($unidad['denticion']) ? $unidad['denticion'] : '', 20);
        if (!in_array($denticion, array('permanente', 'temporal', 'mixta'), true)) {
            $denticion = 'permanente';
        }
        $ubicacion = array(
            'id' => null,
            'numero_unidad' => $numero,
            'pieza' => count($piezas) === 1 ? $piezas[0] : null,
            'piezas' => $piezas,
            'superficies' => array(),
            'denticion' => $denticion,
            'arcada' => null,
            'cuadrante' => null,
            'boca_completa' => false,
            'alcance' => count($piezas) > 1 ? 'piezas_multiples' : 'pieza_dental'
        );
        $bloqueo = trabajoLaboratorioValidarUbicacionesModo($modo, array($ubicacion));
        if ($bloqueo) {
            trabajoLaboratorioLanzar($bloqueo['codigo'], 'Trabajo '.$numero.' de '.$cantidad.': '.$bloqueo['mensaje']);
        }
        $salida[] = $ubicacion;
    }
    usort($salida, function ($a, $b) {
        return intval($a['numero_unidad']) - intval($b['numero_unidad']);
    });
    for ($i = 0; $i < intval($cantidad); $i++) {
        if (intval($salida[$i]['numero_unidad']) !== $i + 1) {
            trabajoLaboratorioLanzar('unidad_regularizacion_invalida', 'Falta completar uno de los trabajos del detalle agrupado.');
        }
    }
    return $salida;
}

function trabajoLaboratorioCodigoOrigenUnidades($codVenta, $codDetalle, $clave)
{
    return trabajoLaboratorioTextoBaseDatos(
        'V'.intval($codVenta).'-D'.intval($codDetalle).'-'.strtoupper(substr(hash('sha256', (string)$clave), 0, 10)),
        100
    );
}

function trabajoLaboratorioObtenerRegularizacionPendiente($mysqli, $codDetalle, $bloquear = false)
{
    if (!trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_regularizacion')
        || !trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_regularizacion_unidad')) {
        return null;
    }
    $sql = 'SELECT r.*,p.nombre_persona AS usuario_asignacion '
        .'FROM trabajo_laboratorio_regularizacion r '
        .'LEFT JOIN persona p ON p.cod_persona=r.cod_usuarioFK_create '
        .'WHERE r.cod_detalle_pendiente_unico=? AND r.estado=\'pendiente_preparacion\' LIMIT 1';
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $codDetalle = intval($codDetalle);
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        return null;
    }
    $stmt = $mysqli->prepare(
        'SELECT id,numero_unidad,pieza,piezas_json,denticion,alcance_odontologico '
        .'FROM trabajo_laboratorio_regularizacion_unidad WHERE id_regularizacionFK=? ORDER BY numero_unidad ASC'
    );
    $unidades = array();
    if ($stmt) {
        $idRegularizacion = intval($fila['id']);
        $stmt->bind_param('i', $idRegularizacion);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($unidad = $resultado->fetch_assoc()) {
            $piezas = json_decode((string)$unidad['piezas_json'], true);
            $unidades[] = array(
                'id' => intval($unidad['id']),
                'numero_unidad' => intval($unidad['numero_unidad']),
                'pieza' => trabajoLaboratorioTextoUtf8($unidad['pieza']),
                'piezas' => is_array($piezas) ? $piezas : array(),
                'denticion' => trabajoLaboratorioTextoUtf8($unidad['denticion']),
                'alcance' => trabajoLaboratorioTextoUtf8($unidad['alcance_odontologico'])
            );
        }
        $stmt->close();
    }
    return array(
        'id' => intval($fila['id']),
        'codigo_origen' => trabajoLaboratorioTextoUtf8($fila['codigo_origen']),
        'cod_detalle_venta' => intval($fila['cod_detalle_ventaFK']),
        'cantidad_unidades' => intval($fila['cantidad_unidades']),
        'estado' => trabajoLaboratorioTextoUtf8($fila['estado']),
        'fecha_asignacion' => $fila['fecha_creacion'],
        'cod_usuario_asignacion' => intval($fila['cod_usuarioFK_create']),
        'usuario_asignacion' => trabajoLaboratorioTextoUtf8($fila['usuario_asignacion']),
        'version' => intval($fila['version']),
        'unidades' => $unidades
    );
}

function trabajoLaboratorioGuardarRegularizacionUnidades($mysqli, $codUsuario, $entrada)
{
    if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
        trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'La regularizacion por unidades todavia no esta instalada.');
    }
    $clave = trabajoLaboratorioNormalizarClave(isset($entrada['clave_idempotencia']) ? $entrada['clave_idempotencia'] : '');
    $payloadHash = trabajoLaboratorioHashPayload($entrada);
    if (!$mysqli->begin_transaction()) {
        trabajoLaboratorioLanzar('transaccion_no_iniciada', 'No se pudo iniciar la regularizacion segura.');
    }
    try {
        $stmt = $mysqli->prepare(
            'SELECT id,payload_hash,cod_detalle_ventaFK FROM trabajo_laboratorio_regularizacion '
            .'WHERE cod_usuarioFK_create=? AND clave_idempotencia=? LIMIT 1 FOR UPDATE'
        );
        $stmt->bind_param('is', $codUsuario, $clave);
        $stmt->execute();
        $repetida = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($repetida) {
            if (!hash_equals((string)$repetida['payload_hash'], (string)$payloadHash)) {
                trabajoLaboratorioLanzar('clave_idempotencia_reutilizada', 'La clave segura ya fue utilizada con otra seleccion.');
            }
            $regularizacion = trabajoLaboratorioObtenerRegularizacionPendiente(
                $mysqli,
                intval($repetida['cod_detalle_ventaFK']),
                false
            );
            if (!$mysqli->commit()) {
                trabajoLaboratorioLanzar('regularizacion_no_confirmada', 'No se pudo confirmar la regularizacion existente.');
            }
            return trabajoLaboratorioRespuesta(
                true,
                'regularizacion_unidades_existente',
                'Las ubicaciones de los trabajos ya estaban guardadas.',
                array('regularizacion' => $regularizacion, 'cod_detalle_venta' => intval($repetida['cod_detalle_ventaFK'])),
                $regularizacion ? $regularizacion['version'] : null
            );
        }
        $codDetalle = trabajoLaboratorioEntero(isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta'] : 0);
        $detalle = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, true);
        if (!$detalle || !trabajoLaboratorioDetalleClinicoActivo($detalle)) {
            trabajoLaboratorioLanzar('detalle_venta_inactivo', 'No se puede regularizar una venta o tratamiento inactivo o finalizado.');
        }
        $config = $detalle['configuracion_laboratorio'];
        if (empty($config['ok']) || empty($config['requiere_laboratorio'])) {
            trabajoLaboratorioLanzar('producto_no_requiere_laboratorio', 'El producto no esta configurado para laboratorio.');
        }
        $cantidad = trabajoLaboratorioCantidadAgrupadaDetalle($detalle);
        if ($cantidad <= 0) {
            trabajoLaboratorioLanzar('cantidad_regularizacion_invalida', 'La cantidad agrupada debe ser un numero entero entre 2 y 32.');
        }
        if (!trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERFORMULARIOCONSULTORIO')
            || !trabajoLaboratorioUsuarioPuedeLocal($mysqli, $codUsuario, intval($detalle['cod_local']))) {
            trabajoLaboratorioLanzar('regularizacion_no_autorizada', 'Necesita acceso a Consulta y al local de esta venta para designar las piezas.');
        }
        if (trabajoLaboratorioObtenerTrabajoActivoDetalle($mysqli, $codDetalle)) {
            trabajoLaboratorioLanzar('trabajo_activo_existente', 'Este detalle ya posee trabajos activos.');
        }
        if (trabajoLaboratorioAntecedenteHistoricoDetalle($mysqli, $codDetalle, true)) {
            trabajoLaboratorioLanzar('antecedente_historico_existente', 'La venta posee un antecedente legacy y debe regularizarse desde Historicos.');
        }
        $pendiente = trabajoLaboratorioObtenerRegularizacionPendiente($mysqli, $codDetalle, true);
        if ($pendiente) {
            trabajoLaboratorioLanzar('regularizacion_unidades_existente', 'Este detalle ya posee ubicaciones pendientes de preparacion.');
        }
        $unidades = trabajoLaboratorioNormalizarUnidadesRegularizacion(
            isset($entrada['unidades_json']) ? $entrada['unidades_json'] : array(),
            $cantidad,
            $config['modo_individualizacion']
        );
        $codigoOrigen = trabajoLaboratorioCodigoOrigenUnidades(intval($detalle['cod_ventaFK']), $codDetalle, $clave);
        $estado = 'pendiente_preparacion';
        $stmt = $mysqli->prepare(
            'INSERT INTO trabajo_laboratorio_regularizacion '
            .'(codigo_origen,cod_detalle_ventaFK,cod_detalle_pendiente_unico,cantidad_unidades,estado,'
            .'clave_idempotencia,payload_hash,fecha_creacion,cod_usuarioFK_create,version) '
            .'VALUES (?,?,?,?,?,?,?,NOW(),?,1)'
        );
        if (!$stmt) {
            trabajoLaboratorioLanzar('regularizacion_no_guardada', 'No se pudo preparar la regularizacion de unidades.');
        }
        $stmt->bind_param('siiisssi', $codigoOrigen, $codDetalle, $codDetalle, $cantidad, $estado, $clave, $payloadHash, $codUsuario);
        if (!$stmt->execute()) {
            $stmt->close();
            trabajoLaboratorioLanzar('regularizacion_no_guardada', 'No se pudo guardar la regularizacion de unidades.');
        }
        $idRegularizacion = intval($stmt->insert_id);
        $stmt->close();
        foreach ($unidades as $unidad) {
            $numero = intval($unidad['numero_unidad']);
            $pieza = trabajoLaboratorioTextoBaseDatos($unidad['pieza'], 5);
            $piezasJson = trabajoLaboratorioTextoBaseDatos(json_encode($unidad['piezas']));
            $denticion = trabajoLaboratorioTextoBaseDatos($unidad['denticion'], 20);
            $alcance = trabajoLaboratorioTextoBaseDatos($unidad['alcance'], 40);
            $stmt = $mysqli->prepare(
                'INSERT INTO trabajo_laboratorio_regularizacion_unidad '
                .'(id_regularizacionFK,numero_unidad,pieza,piezas_json,denticion,alcance_odontologico,'
                .'fecha_creacion,cod_usuarioFK_create) VALUES (?,?,?,?,?,?,NOW(),?)'
            );
            $stmt->bind_param('iissssi', $idRegularizacion, $numero, $pieza, $piezasJson, $denticion, $alcance, $codUsuario);
            if (!$stmt->execute()) {
                $stmt->close();
                trabajoLaboratorioLanzar('regularizacion_no_guardada', 'No se pudo guardar una de las ubicaciones seleccionadas.');
            }
            $stmt->close();
        }
        if (!$mysqli->commit()) {
            trabajoLaboratorioLanzar('regularizacion_no_confirmada', 'No se pudo confirmar la regularizacion de unidades.');
        }
        $regularizacion = trabajoLaboratorioObtenerRegularizacionPendiente($mysqli, $codDetalle, false);
        return trabajoLaboratorioRespuesta(
            true,
            'regularizacion_unidades_guardada',
            'Las ubicaciones quedaron guardadas. Ahora puede preparar los '.$cantidad.' trabajos.',
            array('regularizacion' => $regularizacion, 'cod_detalle_venta' => $codDetalle),
            1
        );
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function trabajoLaboratorioObjetivoAvanceClinicoEvento($tipoEvento)
{
    $tipoEvento = trabajoLaboratorioNormalizarTexto($tipoEvento);
    $objetivos = array(
        'trabajo_iniciado' => 25,
        'recepcion_mecanico_confirmada' => 50,
        'devolucion_confirmada' => 75,
        'ajuste_solicitado' => 75,
        'trabajo_aprobado' => 75,
        'instalacion_registrada' => 100
    );
    return isset($objetivos[$tipoEvento]) ? intval($objetivos[$tipoEvento]) : 0;
}

function trabajoLaboratorioObjetivoAvanceClinicoGrupo($mysqli, $trabajo, $objetivoEvento)
{
    $cantidad = isset($trabajo['cantidad_unidades_origen'])
        ? intval($trabajo['cantidad_unidades_origen']) : 1;
    $codigoOrigen = isset($trabajo['codigo_origen']) ? trim((string)$trabajo['codigo_origen']) : '';
    if ($cantidad <= 1 || $codigoOrigen === '') {
        return intval($objetivoEvento);
    }
    $stmt = $mysqli->prepare(
        'SELECT estado_derivado FROM trabajo_laboratorio WHERE codigo_origen=? ORDER BY unidad_origen ASC'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('avance_grupal_no_disponible', 'No se pudo revisar el avance conjunto de los trabajos.');
    }
    $stmt->bind_param('s', $codigoOrigen);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $pisos = array();
    $porEstado = array(
        'pendiente_tecnico' => 25,
        'pendiente_entrega_mecanico' => 25,
        'en_transferencia_mecanico' => 25,
        'en_laboratorio' => 50,
        'en_transferencia_clinica' => 50,
        'pendiente_revision' => 75,
        'ajuste_solicitado' => 75,
        'listo_instalacion' => 75,
        'cancelado' => 75,
        'instalado' => 100
    );
    while ($fila = $resultado->fetch_assoc()) {
        $estado = trabajoLaboratorioNormalizarTexto($fila['estado_derivado']);
        $pisos[] = isset($porEstado[$estado]) ? intval($porEstado[$estado]) : 0;
    }
    $stmt->close();
    if (count($pisos) < $cantidad) {
        return 0;
    }
    return count($pisos) > 0 ? min($pisos) : 0;
}

function trabajoLaboratorioProgresoClinicoDetalle($mysqli, $codDetalle)
{
    $codDetalle = intval($codDetalle);
    if ($codDetalle <= 0) {
        return 0;
    }
    $stmt = $mysqli->prepare(
        'SELECT IFNULL(progreso_porcentaje,0) AS progreso_porcentaje '
        .'FROM detalle_venta WHERE cod_detalle=? LIMIT 1'
    );
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $codDetalle);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $porcentaje = $fila ? intval($fila['progreso_porcentaje']) : 0;
    return max(0, min(100, $porcentaje));
}

function trabajoLaboratorioDetalleClinicoActivo($detalle)
{
    if (!is_array($detalle)) {
        return false;
    }
    $estadoDetalle = trabajoLaboratorioNormalizarTexto(isset($detalle['estado_detalle'])
        ? $detalle['estado_detalle'] : (isset($detalle['estado']) ? $detalle['estado'] : ''));
    $estadoTratamiento = trabajoLaboratorioNormalizarTexto(isset($detalle['estado_tratamiento'])
        ? $detalle['estado_tratamiento'] : '');
    $estadoVenta = trabajoLaboratorioNormalizarTexto(isset($detalle['estado_venta'])
        ? $detalle['estado_venta'] : '');
    $texto = trim($estadoDetalle.' '.$estadoTratamiento);
    foreach (array('eliminado', 'inactivo', 'anulado', 'cancelado', 'completado', 'finalizado', 'terminado', 'realizado') as $estadoFinal) {
        if (strpos($texto, $estadoFinal) !== false) {
            return false;
        }
    }
    if (in_array($estadoVenta, array('inactivo', 'anulado', 'cancelado'), true)) {
        return false;
    }
    $progreso = isset($detalle['progreso_porcentaje'])
        ? intval($detalle['progreso_porcentaje']) : 0;
    return $progreso < 100;
}

function trabajoLaboratorioSincronizarAvanceClinico($mysqli, $trabajo, $codUsuario, $tipoEvento)
{
    $objetivo = trabajoLaboratorioObjetivoAvanceClinicoEvento($tipoEvento);
    if ($objetivo > 0) {
        $objetivo = trabajoLaboratorioObjetivoAvanceClinicoGrupo($mysqli, $trabajo, $objetivo);
    }
    $codDetalle = isset($trabajo['cod_detalle_ventaFK'])
        ? intval($trabajo['cod_detalle_ventaFK']) : 0;
    if ($objetivo <= 0 || $codDetalle <= 0) {
        return array('actualizado' => false, 'porcentaje' => 0);
    }
    $stmt = $mysqli->prepare(
        'SELECT IFNULL(progreso_porcentaje,0) AS progreso_porcentaje '
        .'FROM detalle_venta WHERE cod_detalle=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'avance_clinico_no_disponible',
            'No se pudo validar el avance clinico vinculado al trabajo.'
        );
    }
    $stmt->bind_param('i', $codDetalle);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'avance_clinico_no_disponible',
            'No se pudo validar el avance clinico vinculado al trabajo.'
        );
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar(
            'detalle_no_encontrado',
            'No se encontro el tratamiento vinculado al trabajo de laboratorio.'
        );
    }
    $anterior = max(0, min(100, intval($fila['progreso_porcentaje'])));
    if ($anterior >= $objetivo) {
        return array(
            'actualizado' => false,
            'porcentaje_anterior' => $anterior,
            'porcentaje' => $anterior
        );
    }

    $stmt = $mysqli->prepare(
        'UPDATE detalle_venta SET progreso_porcentaje=? WHERE cod_detalle=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'avance_clinico_no_guardado',
            'No se pudo preparar la actualizacion del avance clinico.'
        );
    }
    $stmt->bind_param('ii', $objetivo, $codDetalle);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'avance_clinico_no_guardado',
            'No se pudo actualizar el avance clinico del tratamiento.'
        );
    }
    $stmt->close();

    if (!trabajoLaboratorioTablaExiste($mysqli, 'evoluciontratamiento')) {
        trabajoLaboratorioLanzar(
            'historial_evolucion_no_disponible',
            'No se pudo registrar la trazabilidad del avance clinico automatico.'
        );
    }
    $observacion = trabajoLaboratorioTextoBaseDatos(
        'Avance automatico por hito de laboratorio: '.str_replace('_', ' ', $tipoEvento).'.',
        255
    );
    $codAgenda = null;
    if (trabajoLaboratorioColumnaExiste($mysqli, 'evoluciontratamiento', 'porcentaje_anterior')
        && trabajoLaboratorioColumnaExiste($mysqli, 'evoluciontratamiento', 'observacion')) {
        $stmt = $mysqli->prepare(
            'INSERT INTO evoluciontratamiento '
            .'(cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK,porcentaje_anterior,observacion) '
            .'VALUES (?,?,?,NOW(),?,?,?)'
        );
        if ($stmt) {
            $stmt->bind_param('iiiiis', $codDetalle, $codUsuario, $objetivo, $codAgenda, $anterior, $observacion);
        }
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO evoluciontratamiento '
            .'(cod_detalle_venta,cod_usuraioFK,nro,fecha,cod_agendaFK) VALUES (?,?,?,NOW(),?)'
        );
        if ($stmt) {
            $stmt->bind_param('iiii', $codDetalle, $codUsuario, $objetivo, $codAgenda);
        }
    }
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        trabajoLaboratorioLanzar(
            'historial_evolucion_no_guardado',
            'No se pudo registrar la trazabilidad del avance clinico automatico.'
        );
    }
    $codEvolucion = intval($stmt->insert_id);
    $stmt->close();
    return array(
        'actualizado' => true,
        'porcentaje_anterior' => $anterior,
        'porcentaje' => $objetivo,
        'cod_evolucion' => $codEvolucion
    );
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

function trabajoLaboratorioObtenerTrabajosActivosDetalle($mysqli, $codDetalle)
{
    if (!trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio')) {
        return array();
    }
    $codDetalle = intval($codDetalle);
    $stmt = $mysqli->prepare(
        'SELECT id,codigo_visible,codigo_origen,unidad_origen,cantidad_unidades_origen,'
        .'estado_derivado,version,cod_tecnico_usuarioFK,fecha_objetivo '
        .'FROM trabajo_laboratorio WHERE cod_detalle_activo_unico=? '
        .'ORDER BY codigo_origen ASC,unidad_origen ASC,id ASC'
    );
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $trabajos = array();
    while ($fila = $resultado->fetch_assoc()) {
        $trabajos[] = array(
            'id' => intval($fila['id']),
            'codigo_visible' => trabajoLaboratorioTextoUtf8($fila['codigo_visible']),
            'codigo_origen' => trabajoLaboratorioTextoUtf8($fila['codigo_origen']),
            'unidad_origen' => intval($fila['unidad_origen']),
            'cantidad_unidades_origen' => intval($fila['cantidad_unidades_origen']),
            'estado_derivado' => $fila['estado_derivado'],
            'version' => intval($fila['version']),
            'cod_tecnico_usuario' => intval($fila['cod_tecnico_usuarioFK']),
            'fecha_objetivo' => $fila['fecha_objetivo']
        );
    }
    $stmt->close();
    return $trabajos;
}

function trabajoLaboratorioObtenerTrabajoActivoDetalle($mysqli, $codDetalle)
{
    $trabajos = trabajoLaboratorioObtenerTrabajosActivosDetalle($mysqli, $codDetalle);
    return count($trabajos) > 0 ? $trabajos[0] : null;
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
    /* La cadena completa es compartida entre quienes poseen
       VERTRABAJOSLABORATORIO; el auditor conserva su acceso transversal. */
    return trabajoLaboratorioTienePermiso(
        $mysqli,
        intval($codUsuario),
        'VERTRABAJOSLABORATORIO'
    ) || trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario));
}

function trabajoLaboratorioEstadoPermiteAccion($estado, $accion)
{
    $estado = (string)$estado;
    $estadosActivos = array(
        'pendiente_tecnico', 'pendiente_entrega_mecanico', 'en_transferencia_mecanico', 'en_laboratorio',
        'en_transferencia_clinica', 'pendiente_revision', 'ajuste_solicitado',
        'listo_instalacion'
    );
    $mapa = array(
        'asignarTecnico' => array('pendiente_tecnico'),
        'iniciarTransferencia' => array('pendiente_entrega_mecanico', 'ajuste_solicitado'),
        'confirmarRecepcion' => array('en_transferencia_mecanico'),
        'iniciarDevolucion' => array('en_laboratorio'),
        'confirmarDevolucion' => array('en_transferencia_clinica'),
        'solicitarAjuste' => array('pendiente_revision'),
        'aprobarTrabajo' => array('pendiente_revision')
    );
    if ($accion === 'registrarInstalacion') {
        return in_array($estado, $estadosActivos, true);
    }
    if (in_array($accion, array(
        'agregarEvidencia',
        'agregarNota',
        'cancelarTrabajo',
        'tomarHilo',
        'registrarNovedad',
        'rectificarCustodia'
    ), true)) {
        return in_array($estado, $estadosActivos, true);
    }
    return isset($mapa[$accion]) && in_array($estado, $mapa[$accion], true);
}

function trabajoLaboratorioResolverAcciones($estado, $contexto)
{
    $acciones = array(
        'asignarTecnico' => false,
        'iniciarTransferencia' => false,
        'tomarHilo' => false,
        'confirmarRecepcion' => false,
        'agregarEvidencia' => false,
        'agregarNota' => false,
        'registrarNovedad' => false,
        'rectificarCustodia' => false,
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
    $acciones['asignarTecnico'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'asignarTecnico')
        && (($doctor && $local) || $auditor)
        && $tiene('CREARTRABAJOLABORATORIO');
    $acciones['iniciarTransferencia'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'iniciarTransferencia')
        && ($custodio || $auditor || ($local && !$tecnicoFormal))
        && $tiene('ENTREGARTRABAJOLABORATORIO');
    $acciones['tomarHilo'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'tomarHilo')
        && !$custodio;
    /* Los nombres legacy siguen aceptados por HTTP, pero no se anuncian como
       acciones independientes para evitar dos botones que ejecuten lo mismo. */
    $acciones['confirmarRecepcion'] = false;
    $acciones['agregarEvidencia'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'agregarEvidencia')
        && ($custodio || $tecnico || ($auditor && $local))
        && $tiene('EVIDENCIATRABAJOLABORATORIO');
    $acciones['agregarNota'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'agregarNota')
        && ($custodio || $tecnico || ($auditor && $local))
        && $tiene('EVIDENCIATRABAJOLABORATORIO');
    $acciones['registrarNovedad'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'registrarNovedad')
        && $custodio
        && $tiene('VERTRABAJOSLABORATORIO');
    $acciones['rectificarCustodia'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'rectificarCustodia')
        && $auditor;
    $acciones['iniciarDevolucion'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'iniciarDevolucion')
        && ($tecnico || $auditor)
        && $tiene('ENTREGARTRABAJOLABORATORIO');
    $acciones['confirmarDevolucion'] = false;
    $acciones['solicitarAjuste'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'solicitarAjuste')
        && (($doctor && $local) || $auditor)
        && $tiene('AJUSTARTRABAJOLABORATORIO');
    $acciones['aprobarTrabajo'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'aprobarTrabajo')
        && (($doctor && $local) || $auditor)
        && $tiene('APROBARTRABAJOLABORATORIO');
    $acciones['registrarInstalacion'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'registrarInstalacion')
        && $custodio;
    $acciones['cancelarTrabajo'] = trabajoLaboratorioEstadoPermiteAccion($estado, 'cancelarTrabajo')
        && (($local && !$tecnicoFormal) || $auditor)
        && $tiene('CANCELARTRABAJOLABORATORIO');
    return $acciones;
}

function trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajo)
{
    $acciones = array(
        'asignarTecnico' => false,
        'iniciarTransferencia' => false,
        'tomarHilo' => false,
        'confirmarRecepcion' => false,
        'agregarEvidencia' => false,
        'agregarNota' => false,
        'registrarNovedad' => false,
        'rectificarCustodia' => false,
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
        'VERTRABAJOSLABORATORIO',
        'CREARTRABAJOLABORATORIO',
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
    $acciones = trabajoLaboratorioResolverAcciones(
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
    if (!trabajoLaboratorioHiloCustodiaDisponible($mysqli)) {
        $acciones['tomarHilo'] = false;
        $acciones['registrarNovedad'] = false;
        $acciones['rectificarCustodia'] = false;
    }
    return trabajoLaboratorioAccionesConMotivoAuditor(
        $mysqli,
        $codUsuario,
        $trabajo,
        $acciones
    );
}

/**
 * Reduce los hitos operativos a informacion apta para un resumen clinico. No
 * expone observaciones, metadata ni referencias a evidencias.
 */
function trabajoLaboratorioHitosResumen($recorrido, $limite = 3)
{
    $limite = max(1, min(3, intval($limite)));
    $nodos = array_values(is_array($recorrido) ? $recorrido : array());
    if (count($nodos) > $limite) {
        $nodos = array_slice($nodos, count($nodos) - $limite);
    }
    $salida = array();
    foreach ($nodos as $nodo) {
        $actor = isset($nodo['actor']) && is_array($nodo['actor'])
            ? $nodo['actor'] : array();
        $salida[] = array(
            'id_evento' => isset($nodo['id_evento']) ? intval($nodo['id_evento']) : null,
            'tipo_evento' => isset($nodo['tipo_evento'])
                ? trabajoLaboratorioTextoUtf8($nodo['tipo_evento']) : null,
            'titulo' => isset($nodo['titulo'])
                ? trabajoLaboratorioTextoUtf8($nodo['titulo']) : null,
            'fecha_servidor' => isset($nodo['fecha_servidor']) ? $nodo['fecha_servidor'] : null,
            'actor' => array(
                'nombre' => isset($actor['nombre'])
                    ? trabajoLaboratorioTextoUtf8($actor['nombre']) : null,
                'rol' => isset($actor['rol'])
                    ? trabajoLaboratorioTextoUtf8($actor['rol']) : null,
                'avatar' => isset($actor['avatar'])
                    ? trabajoLaboratorioTextoUtf8($actor['avatar']) : null
            ),
            'local' => isset($nodo['local'])
                ? trabajoLaboratorioTextoUtf8($nodo['local']) : null,
            'ciclo_etiqueta' => isset($nodo['ciclo_etiqueta'])
                ? trabajoLaboratorioTextoUtf8($nodo['ciclo_etiqueta']) : null,
            'estado' => isset($nodo['estado'])
                ? trabajoLaboratorioTextoUtf8($nodo['estado']) : null,
            'estado_nombre' => isset($nodo['estado_nombre'])
                ? trabajoLaboratorioTextoUtf8($nodo['estado_nombre']) : null,
            'estado_semantico' => isset($nodo['estado_semantico'])
                ? trabajoLaboratorioTextoUtf8($nodo['estado_semantico']) : null,
            'color_semantico' => isset($nodo['color_semantico'])
                ? trabajoLaboratorioTextoUtf8($nodo['color_semantico']) : null,
            'pendiente' => !empty($nodo['pendiente'])
        );
    }
    return $salida;
}

function trabajoLaboratorioResumenTrabajoActivo($mysqli, $trabajo, $recorrido = null)
{
    if (!is_array($trabajo) || intval(isset($trabajo['id']) ? $trabajo['id'] : 0) <= 0) {
        return null;
    }
    $idTrabajo = intval($trabajo['id']);
    $estado = isset($trabajo['estado_derivado']) ? (string)$trabajo['estado_derivado'] : '';
    $presentacion = trabajoLaboratorioPresentacionEstadoRecorrido($estado);
    if (!is_array($recorrido)) {
        $recorridos = trabajoLaboratorioRecorridosPorTrabajos($mysqli, array($trabajo));
        $recorrido = isset($recorridos[$idTrabajo]) ? $recorridos[$idTrabajo] : array();
    }
    $numeroCiclo = max(1, intval(isset($trabajo['ciclo_actual']) ? $trabajo['ciclo_actual'] : 1));
    $etiquetaCiclo = $numeroCiclo <= 1 ? 'Original' : 'Ajuste '.intval($numeroCiclo - 1);
    $tecnicoPendiente = $estado === 'pendiente_tecnico';
    $nombreTecnicoResumen = isset($trabajo['nombre_tecnico']) && trim((string)$trabajo['nombre_tecnico']) !== ''
        ? $trabajo['nombre_tecnico'] : ($tecnicoPendiente ? 'Tecnico pendiente' : null);
    $rolTecnicoResumen = isset($trabajo['tecnico_rol']) && trim((string)$trabajo['tecnico_rol']) !== ''
        ? $trabajo['tecnico_rol'] : ($tecnicoPendiente ? 'Asignacion pendiente' : null);
    return array(
        'id' => $idTrabajo,
        'codigo_visible' => isset($trabajo['codigo_visible'])
            ? trabajoLaboratorioTextoUtf8($trabajo['codigo_visible']) : null,
        'codigo_origen' => isset($trabajo['codigo_origen'])
            ? trabajoLaboratorioTextoUtf8($trabajo['codigo_origen']) : null,
        'unidad_origen' => isset($trabajo['unidad_origen']) ? intval($trabajo['unidad_origen']) : 1,
        'cantidad_unidades_origen' => isset($trabajo['cantidad_unidades_origen'])
            ? intval($trabajo['cantidad_unidades_origen']) : 1,
        'estado_derivado' => $estado,
        'estado_nombre' => trabajoLaboratorioTextoUtf8($presentacion['nombre']),
        'estado_texto' => trabajoLaboratorioTextoUtf8($presentacion['nombre']),
        'estado_semantico' => $presentacion['semantica'],
        'color_semantico' => $presentacion['color'],
        'version' => intval(isset($trabajo['version']) ? $trabajo['version'] : 0),
        'fecha_objetivo' => isset($trabajo['fecha_objetivo']) ? $trabajo['fecha_objetivo'] : null,
        'fecha_actualizacion' => isset($trabajo['fecha_actualizacion'])
            ? $trabajo['fecha_actualizacion'] : null,
        'cod_tecnico_usuarioFK' => intval(isset($trabajo['cod_tecnico_usuarioFK'])
            ? $trabajo['cod_tecnico_usuarioFK'] : 0),
        'nombre_tecnico' => trabajoLaboratorioTextoUtf8($nombreTecnicoResumen),
        'tecnico' => trabajoLaboratorioPersonaRecorrido(
            isset($trabajo['cod_tecnico_usuarioFK']) ? $trabajo['cod_tecnico_usuarioFK'] : null,
            $nombreTecnicoResumen,
            $rolTecnicoResumen,
            isset($trabajo['tecnico_avatar']) ? $trabajo['tecnico_avatar'] : null
        ),
        'cod_custodio_actualFK' => intval(isset($trabajo['cod_custodio_actualFK'])
            ? $trabajo['cod_custodio_actualFK'] : 0),
        'nombre_custodio' => isset($trabajo['nombre_custodio'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_custodio']) : null,
        'custodio' => trabajoLaboratorioPersonaRecorrido(
            isset($trabajo['cod_custodio_actualFK']) ? $trabajo['cod_custodio_actualFK'] : null,
            isset($trabajo['nombre_custodio']) ? $trabajo['nombre_custodio'] : null
        ),
        'ciclo_actual' => $numeroCiclo,
        'ciclo_etiqueta' => $etiquetaCiclo,
        'ciclo' => array(
            'numero' => $numeroCiclo,
            'etiqueta' => $etiquetaCiclo
        ),
        'hitos_recientes' => trabajoLaboratorioHitosResumen($recorrido, 3)
    );
}

/**
 * Proyecta una version liviana del hilo unificado para las tarjetas de
 * tratamientos en Consulta. No incorpora miniaturas ni datos extensos: esos
 * contenidos se recuperan solamente cuando el usuario abre un nodo.
 */
function trabajoLaboratorioMicroHilosActivos(
    $mysqli,
    $trabajos,
    $limite = 4,
    $recorridos = null,
    $cadenas = null
)
{
    $trabajos = array_values(is_array($trabajos) ? $trabajos : array());
    $limite = max(3, min(5, intval($limite)));
    if (count($trabajos) === 0) {
        return array();
    }
    if (!is_array($recorridos)) {
        $recorridos = trabajoLaboratorioRecorridosPorTrabajos($mysqli, $trabajos);
    }
    if (!is_array($cadenas)) {
        $cadenas = trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, $trabajos, false);
    }
    $salida = array();
    foreach ($trabajos as $trabajo) {
        if (!is_array($trabajo) || intval(isset($trabajo['id']) ? $trabajo['id'] : 0) <= 0) {
            continue;
        }
        $idTrabajo = intval($trabajo['id']);
        $unificados = array();
        $sinIdentificador = array();
        $agregar = function ($nodo, $origen, $orden) use (&$unificados, &$sinIdentificador) {
            if (!is_array($nodo)) {
                return;
            }
            $idEvento = intval(isset($nodo['id_evento']) ? $nodo['id_evento']
                : (isset($nodo['id']) ? $nodo['id'] : 0));
            $registro = array('nodo' => $nodo, 'origen' => $origen, 'orden' => intval($orden));
            if ($idEvento <= 0) {
                $sinIdentificador[] = $registro;
                return;
            }
            if (!isset($unificados[$idEvento]) || $origen === 'custodia') {
                $unificados[$idEvento] = $registro;
            }
        };
        $orden = 0;
        foreach (isset($recorridos[$idTrabajo]) ? $recorridos[$idTrabajo] : array() as $nodo) {
            $agregar($nodo, 'operativo', $orden++);
        }
        foreach (isset($cadenas[$idTrabajo]) ? $cadenas[$idTrabajo] : array() as $nodo) {
            $agregar($nodo, 'custodia', $orden++);
        }
        $registros = array_values($unificados);
        foreach ($sinIdentificador as $registroSinId) {
            $registros[] = $registroSinId;
        }
        usort($registros, function ($izquierda, $derecha) {
            $nodoIzquierda = isset($izquierda['nodo']) ? $izquierda['nodo'] : array();
            $nodoDerecha = isset($derecha['nodo']) ? $derecha['nodo'] : array();
            $fechaIzquierda = isset($nodoIzquierda['fecha_inicio']) ? $nodoIzquierda['fecha_inicio']
                : (isset($nodoIzquierda['fecha_servidor']) ? $nodoIzquierda['fecha_servidor'] : '');
            $fechaDerecha = isset($nodoDerecha['fecha_inicio']) ? $nodoDerecha['fecha_inicio']
                : (isset($nodoDerecha['fecha_servidor']) ? $nodoDerecha['fecha_servidor'] : '');
            if ((string)$fechaIzquierda !== (string)$fechaDerecha) {
                return (string)$fechaIzquierda < (string)$fechaDerecha ? -1 : 1;
            }
            $idIzquierda = intval(isset($nodoIzquierda['id_evento']) ? $nodoIzquierda['id_evento']
                : (isset($nodoIzquierda['id']) ? $nodoIzquierda['id'] : 0));
            $idDerecha = intval(isset($nodoDerecha['id_evento']) ? $nodoDerecha['id_evento']
                : (isset($nodoDerecha['id']) ? $nodoDerecha['id'] : 0));
            if ($idIzquierda === $idDerecha) {
                return intval($izquierda['orden']) - intval($derecha['orden']);
            }
            return $idIzquierda - $idDerecha;
        });
        $totalNodos = count($registros);
        $ocultos = 0;
        if ($totalNodos > $limite) {
            $ultimos = max(1, $limite - 2);
            $registros = array_merge(
                array_slice($registros, 0, 1),
                array_slice($registros, -$ultimos)
            );
            $ocultos = $totalNodos - count($registros);
        }
        $nodosSalida = array();
        foreach ($registros as $registro) {
            $nodo = $registro['nodo'];
            $origen = $registro['origen'];
            $actor = $origen === 'custodia'
                && isset($nodo['responsable']) && is_array($nodo['responsable'])
                ? $nodo['responsable']
                : (isset($nodo['actor']) && is_array($nodo['actor']) ? $nodo['actor'] : array());
            $fecha = isset($nodo['fecha_inicio']) ? $nodo['fecha_inicio']
                : (isset($nodo['fecha_servidor']) ? $nodo['fecha_servidor'] : null);
            $nodosSalida[] = array(
                'id_evento' => intval(isset($nodo['id_evento']) ? $nodo['id_evento']
                    : (isset($nodo['id']) ? $nodo['id'] : 0)),
                'origen' => $origen,
                'tipo_evento' => isset($nodo['tipo_evento'])
                    ? trabajoLaboratorioTextoUtf8($nodo['tipo_evento']) : null,
                'titulo' => isset($nodo['titulo'])
                    ? trabajoLaboratorioTextoUtf8($nodo['titulo']) : 'Hito registrado',
                'fecha' => $fecha,
                'actor' => array(
                    'cod_usuario' => isset($actor['cod_usuario']) ? intval($actor['cod_usuario']) : null,
                    'nombre' => isset($actor['nombre'])
                        ? trabajoLaboratorioTextoUtf8($actor['nombre']) : 'Usuario registrado',
                    'rol' => isset($actor['rol'])
                        ? trabajoLaboratorioTextoUtf8($actor['rol']) : 'Responsable',
                    'avatar' => isset($actor['avatar'])
                        ? trabajoLaboratorioTextoUtf8($actor['avatar']) : null
                ),
                'local' => isset($nodo['local']) ? trabajoLaboratorioTextoUtf8($nodo['local']) : null,
                'duracion_texto' => isset($nodo['duracion_texto'])
                    ? trabajoLaboratorioTextoUtf8($nodo['duracion_texto']) : null,
                'estado' => isset($nodo['estado'])
                    ? trabajoLaboratorioTextoUtf8($nodo['estado']) : null,
                'estado_nombre' => isset($nodo['estado_nombre'])
                    ? trabajoLaboratorioTextoUtf8($nodo['estado_nombre']) : null,
                'estado_semantico' => isset($nodo['estado_semantico'])
                    ? trabajoLaboratorioTextoUtf8($nodo['estado_semantico']) : null,
                'actual' => !empty($nodo['actual']),
                'pendiente' => !empty($nodo['pendiente']),
                'tiene_media' => intval(isset($nodo['miniatura_media_id'])
                    ? $nodo['miniatura_media_id']
                    : (isset($nodo['media_id']) ? $nodo['media_id'] : 0)) > 0
            );
        }
        if (count($nodosSalida) > 0) {
            $tieneActual = false;
            foreach ($nodosSalida as $nodoSalida) {
                if (!empty($nodoSalida['actual'])) {
                    $tieneActual = true;
                    break;
                }
            }
            if (!$tieneActual && !in_array(
                isset($trabajo['estado_derivado']) ? (string)$trabajo['estado_derivado'] : '',
                array('instalado', 'cancelado'),
                true
            )) {
                $nodosSalida[count($nodosSalida) - 1]['actual'] = true;
            }
        }
        $presentacion = trabajoLaboratorioPresentacionEstadoRecorrido(
            isset($trabajo['estado_derivado']) ? $trabajo['estado_derivado'] : ''
        );
        $salida[] = array(
            'id' => $idTrabajo,
            'codigo_visible' => isset($trabajo['codigo_visible'])
                ? trabajoLaboratorioTextoUtf8($trabajo['codigo_visible']) : null,
            'codigo_origen' => isset($trabajo['codigo_origen'])
                ? trabajoLaboratorioTextoUtf8($trabajo['codigo_origen']) : null,
            'unidad_origen' => max(1, intval(isset($trabajo['unidad_origen'])
                ? $trabajo['unidad_origen'] : 1)),
            'cantidad_unidades_origen' => max(1, intval(isset($trabajo['cantidad_unidades_origen'])
                ? $trabajo['cantidad_unidades_origen'] : 1)),
            'estado_derivado' => isset($trabajo['estado_derivado'])
                ? trabajoLaboratorioTextoUtf8($trabajo['estado_derivado']) : null,
            'estado_nombre' => trabajoLaboratorioTextoUtf8($presentacion['nombre']),
            'estado_semantico' => $presentacion['semantica'],
            'nodos' => $nodosSalida,
            'total_nodos' => $totalNodos,
            'nodos_ocultos' => $ocultos
        );
    }
    return $salida;
}

/**
 * Localiza una declaracion historica vinculada al tratamiento. Es una
 * constancia administrativa y nunca se presenta como instalacion clinica.
 */
function trabajoLaboratorioAntecedenteHistoricoDetalle($mysqli, $codDetalle, $bloquear = false)
{
    $codDetalle = intval($codDetalle);
    if ($codDetalle <= 0
        || !trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_historico')) {
        return null;
    }
    $sql =
        'SELECT id,estado_convalidacion,id_trabajo_laboratorioFK,'
        .'fecha_convalidacion,fecha_actualizacion,fecha_sincronizacion '
        .'FROM trabajo_laboratorio_historico WHERE cod_detalle_ventaFK=? '
        .'ORDER BY (id_trabajo_laboratorioFK IS NOT NULL) DESC,'
        .'COALESCE(fecha_actualizacion,fecha_convalidacion,fecha_sincronizacion) DESC,id DESC LIMIT 1';
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $codDetalle);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        return null;
    }
    $idTrabajo = intval($fila['id_trabajo_laboratorioFK']);
    $promovido = $idTrabajo > 0
        || (string)$fila['estado_convalidacion'] === 'integrado_operativo';
    $etiqueta = $promovido
        ? 'Antecedente historico integrado al seguimiento operativo'
        : 'Antecedente historico vinculado al tratamiento';
    return array(
        'disponible' => true,
        'origen' => 'administrativo_historico',
        'tipo' => $promovido ? 'promovido' : 'vinculado',
        'etiqueta' => $etiqueta,
        'descripcion' => 'Declarado por Administracion. Esta referencia no acredita una instalacion clinica.',
        'declarado_por' => 'Administracion',
        'promovido' => $promovido,
        'es_instalacion_clinica' => false,
        'puede_abrir_ficha' => false,
        'id_historico' => intval($fila['id']),
        'id_trabajo_laboratorio' => $idTrabajo > 0 ? $idTrabajo : null,
        'estado_convalidacion' => trabajoLaboratorioTextoUtf8($fila['estado_convalidacion']),
        'fecha_referencia' => $fila['fecha_actualizacion'] !== null
            ? $fila['fecha_actualizacion']
            : ($fila['fecha_convalidacion'] !== null
                ? $fila['fecha_convalidacion'] : $fila['fecha_sincronizacion'])
    );
}

function trabajoLaboratorioObtenerContextoDetalle($mysqli, $codUsuario, $codDetalle)
{
    $bloqueos = array();
    $avisos = array();
    if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
        return array(
            'detalle' => null,
            'ubicaciones' => array(),
            'trabajo_activo' => null,
            'antecedente_historico' => null,
            'micro_hilos_activos' => array(),
            'tecnicos_disponibles' => array(),
            'puede_ver_resumen' => false,
            'puede_abrir_ficha' => false,
            'puede_iniciar' => false,
			'puede_asignar_ubicacion' => false,
            'puede_asegurar_hilo' => false,
			'requiere_regularizacion_administrativa' => false,
			'regularizacion_administrativa' => null,
            'bloqueos' => array(array(
                'codigo' => 'estructura_laboratorio_no_instalada',
                'mensaje' => 'El modulo de trabajos de laboratorio todavia no esta instalado.'
            )),
            'acciones_permitidas' => array(),
            'mensaje_contexto' => 'El modulo de trabajos de laboratorio todavia no esta instalado.'
        );
    }
    $fila = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, false);
    if (!$fila) {
        trabajoLaboratorioLanzar('detalle_no_encontrado', 'No se encontro el detalle de tratamiento.');
    }
    $config = $fila['configuracion_laboratorio'];
    $cantidad = floatval($fila['cantidad_detalle']);
    $cantidadValida = abs($cantidad - 1.0) < 0.000001;
    $cantidadAgrupada = trabajoLaboratorioCantidadAgrupadaDetalle($fila);
    $esDetalleAgrupado = $cantidadAgrupada > 1;
    $ubicaciones = trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, $codDetalle);
    $trabajosActivos = trabajoLaboratorioObtenerTrabajosActivosDetalle($mysqli, $codDetalle);
    $trabajoActivo = count($trabajosActivos) > 0 ? $trabajosActivos[0] : null;
    $regularizacionUnidades = !$trabajoActivo && $esDetalleAgrupado
        ? trabajoLaboratorioObtenerRegularizacionPendiente($mysqli, $codDetalle, false) : null;
    $trabajoActivoCompleto = null;
    $puedeAbrirFicha = false;
    if ($trabajoActivo) {
        $trabajoActivoCompleto = trabajoLaboratorioObtenerTrabajo($mysqli, intval($trabajoActivo['id']), false);
        if (!$trabajoActivoCompleto) {
            trabajoLaboratorioLanzar(
                'trabajo_no_encontrado',
                'No se pudo consultar el trabajo activo asociado al tratamiento.'
            );
        }
        $puedeAbrirFicha = trabajoLaboratorioPuedeVer(
            $mysqli,
            $codUsuario,
            $trabajoActivoCompleto
        );
    }
	$antecedenteHistorico = !$trabajoActivo && !$regularizacionUnidades
		? trabajoLaboratorioAntecedenteHistoricoDetalle($mysqli, $codDetalle) : null;

    /* El resumen sigue el alcance de consulta del detalle/local. Se conserva
       ademas el acceso previo de un tecnico o custodio que ya podia abrir la
       ficha del trabajo, aunque opere desde otra sucursal. */
    $puedeConsultarLocal = trabajoLaboratorioUsuarioPuedeLocal(
        $mysqli,
        $codUsuario,
        intval($fila['cod_local'])
    );
	$tienePermisoConsultaClinica = trabajoLaboratorioTienePermiso(
		$mysqli,
		$codUsuario,
		'VERFORMULARIOCONSULTORIO'
	);
    $puedeVerResumen = ($puedeConsultarLocal && $tienePermisoConsultaClinica) || $puedeAbrirFicha;
    if (!$puedeVerResumen) {
        trabajoLaboratorioLanzar(
            'local_no_autorizado',
            'El usuario no puede consultar el detalle ni el local de esta venta.'
        );
    }

    $esDoctor = trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario);
    $esAuditor = trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario);
    $tienePermisoCrear = trabajoLaboratorioTienePermiso(
        $mysqli,
        $codUsuario,
        'CREARTRABAJOLABORATORIO'
    );
	$tienePermisoEditarClinica = trabajoLaboratorioTienePermiso(
		$mysqli,
		$codUsuario,
		'EDITARFORMULARIOCONSULTORIO'
	);
    $puedeOperarLocal = trabajoLaboratorioUsuarioPuedeOperarLocal(
        $mysqli,
        $codUsuario,
        intval($fila['cod_local'])
    );
    $habilitadoParaPrepararInicio = !$trabajoActivo
		&& !$antecedenteHistorico
        && $puedeOperarLocal
        && ($esDoctor || $esAuditor)
        && $tienePermisoCrear;
    $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($fila['cod_ventaFK']), false);
    $requiere = !empty($config['ok']) && !empty($config['requiere_laboratorio']);
    $modo = !empty($config['modo_individualizacion']) ? $config['modo_individualizacion'] : '';
    $detalleInactivo = !trabajoLaboratorioDetalleClinicoActivo($fila);
	$detallePreparadoParaAcciones = $habilitadoParaPrepararInicio
		&& $requiere
		&& ($cantidadValida || ($esDetalleAgrupado && $regularizacionUnidades))
		&& !$detalleInactivo;
	$detallePreparadoParaUbicacion = !$trabajoActivo
		&& !$antecedenteHistorico
		&& $requiere
		&& $cantidadValida
		&& !$detalleInactivo;
	$puedeAsignarUbicacion = $detallePreparadoParaUbicacion
		&& $puedeConsultarLocal
		&& $tienePermisoConsultaClinica;
	$puedeRegularizarUnidades = !$trabajoActivo
		&& !$antecedenteHistorico
		&& !$regularizacionUnidades
		&& $requiere
		&& $esDetalleAgrupado
		&& !$detalleInactivo
		&& $puedeConsultarLocal
		&& $tienePermisoConsultaClinica;
	$tecnicos = $detallePreparadoParaAcciones
		? trabajoLaboratorioTecnicosDisponibles($mysqli, true) : array();

    if (!$requiere) {
        $bloqueos[] = array('codigo' => 'producto_no_requiere_laboratorio', 'mensaje' => 'El producto no esta configurado para laboratorio.');
    }
    if (!$cantidadValida && !$esDetalleAgrupado) {
		$cantidadTextoRegularizacion = rtrim(rtrim(number_format($cantidad, 2, ',', '.'), '0'), ',');
		$bloqueos[] = array(
			'codigo' => 'cantidad_laboratorio_invalida',
			'mensaje' => 'La cantidad '.$cantidadTextoRegularizacion.' no puede convertirse de forma segura en trabajos independientes. Administracion debe revisar el detalle sin modificar la venta.',
			'accion_sugerida' => 'regularizar_detalle_historico',
			'responsable_sugerido' => 'Administracion'
		);
    } elseif ($esDetalleAgrupado && !$regularizacionUnidades && !$trabajoActivo && !$antecedenteHistorico) {
		$bloqueos[] = array(
			'codigo' => 'unidades_agrupadas_sin_designar',
			'mensaje' => 'Este detalle originara '.$cantidadAgrupada.' trabajos independientes. Debe designar las piezas de cada trabajo por separado.',
			'accion_sugerida' => 'designar_unidades_laboratorio'
		);
    }
    if ($detalleInactivo) {
        $bloqueos[] = array('codigo' => 'detalle_venta_inactivo', 'mensaje' => 'No se puede iniciar un trabajo sobre una venta o detalle inactivo.');
    }
    $bloqueoUbicacion = $esDetalleAgrupado
        ? null : trabajoLaboratorioValidarUbicacionesModo($modo, $ubicaciones);
    if ($bloqueoUbicacion) {
        $bloqueos[] = $bloqueoUbicacion;
    }
    if ($trabajoActivo) {
        $bloqueos[] = array('codigo' => 'trabajo_activo_existente', 'mensaje' => 'Este detalle ya tiene un trabajo de laboratorio activo.');
    }
	if ($antecedenteHistorico) {
		$bloqueos[] = array(
			'codigo' => 'antecedente_historico_existente',
			'mensaje' => 'Este tratamiento ya posee un antecedente historico. Administracion debe regularizarlo antes de iniciar otro trabajo.',
			'accion_sugerida' => 'regularizar_historico'
		);
	}
    if (!$hilo) {
        $bloqueos[] = array(
            'codigo' => 'hilo_unico_no_vinculado',
            'mensaje' => 'La venta no tiene vinculado su hilo maestro de seguimiento.',
            'accion_sugerida' => 'vincular_hilo_maestro'
        );
    }
    if ($detallePreparadoParaAcciones && count($tecnicos) === 0) {
        $avisos[] = array(
            'codigo' => 'tecnico_pendiente_disponible',
            'mensaje' => 'Puede preparar el trabajo ahora y asignar el tecnico mas adelante.'
        );
    }
    if (!$trabajoActivo && !$esDoctor && !$esAuditor) {
        $bloqueos[] = array('codigo' => 'rol_clinico_requerido', 'mensaje' => 'Solo un profesional autorizado puede iniciar el trabajo.');
    }
    if (!$trabajoActivo && !$puedeOperarLocal) {
        $bloqueos[] = array(
            'codigo' => 'operacion_local_requerida',
            'mensaje' => 'Puede consultar el resumen, pero no iniciar trabajos en esta sucursal.'
        );
    }
    if (!$trabajoActivo && !$tienePermisoCrear) {
        $bloqueos[] = array('codigo' => 'permiso_creacion_requerido', 'mensaje' => 'El usuario no tiene permiso para iniciar trabajos de laboratorio.');
    }

	$puedeAsegurarHilo = !$trabajoActivo && !$antecedenteHistorico && !$hilo
		&& $requiere && ($cantidadValida || ($esDetalleAgrupado && $regularizacionUnidades)) && !$detalleInactivo
        && $puedeOperarLocal
        && ($esDoctor || $esAuditor)
        && $tienePermisoCrear;
    $puedeIniciar = $cantidadValida && !$trabajoActivo && count($bloqueos) === 0;
	$puedeIniciarAgrupados = $esDetalleAgrupado && $regularizacionUnidades
		&& !$trabajoActivo && count($bloqueos) === 0;

    $recorridosActivos = count($trabajosActivos) > 0
        ? trabajoLaboratorioRecorridosPorTrabajos($mysqli, $trabajosActivos) : array();
    $cadenasActivas = count($trabajosActivos) > 0
        ? trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, $trabajosActivos, false) : array();
    $trabajoActivoResumen = $trabajoActivoCompleto
        ? trabajoLaboratorioResumenTrabajoActivo(
            $mysqli,
            $trabajoActivoCompleto,
            isset($recorridosActivos[intval($trabajoActivoCompleto['id'])])
                ? $recorridosActivos[intval($trabajoActivoCompleto['id'])] : array()
        ) : null;
    $microHilosActivos = trabajoLaboratorioMicroHilosActivos(
        $mysqli,
        $trabajosActivos,
        4,
        $recorridosActivos,
        $cadenasActivas
    );
    if ($trabajoActivoResumen) {
        $trabajoActivoResumen['puede_abrir_ficha'] = $puedeAbrirFicha;
    }
    $accionesPermitidas = $trabajoActivo
        ? trabajoLaboratorioAccionesPermitidas($mysqli, $codUsuario, $trabajoActivoCompleto)
        : array(
			'iniciarTrabajo' => $puedeIniciar,
			'iniciarTrabajosAgrupados' => $puedeIniciarAgrupados,
			'guardarRegularizacionUnidades' => $puedeRegularizarUnidades
		);
    if (!$trabajoActivo) {
        $accionesPermitidas = trabajoLaboratorioAccionesConMotivoAuditor(
            $mysqli,
            $codUsuario,
            array(
                'cod_localFK' => intval($fila['cod_local']),
                'cod_custodio_actualFK' => intval($codUsuario),
                'cod_tecnico_usuarioFK' => 0
            ),
            $accionesPermitidas
        );
    }
    if ($trabajoActivoResumen) {
        $mensajeContexto = $puedeAbrirFicha
            ? 'El tratamiento posee un trabajo activo y puede abrirse su ficha.'
            : 'El tratamiento posee un trabajo activo disponible en modo resumen.';
    } elseif ($antecedenteHistorico) {
        $mensajeContexto = $antecedenteHistorico['etiqueta'].'. No acredita una instalacion clinica.';
    } elseif ($puedeIniciarAgrupados) {
		$mensajeContexto = 'Las piezas ya estan designadas. Puede preparar los '.$cantidadAgrupada.' trabajos independientes.'
            .(count($tecnicos) === 0 ? ' Quedaran con tecnico pendiente.' : '');
    } elseif ($puedeIniciar) {
        $mensajeContexto = 'El tratamiento esta preparado para iniciar un trabajo de laboratorio.'
            .(count($tecnicos) === 0 ? ' Puede continuar con tecnico pendiente.' : '');
    } else {
        $mensajeContexto = count($bloqueos) > 0
            ? $bloqueos[0]['mensaje'] : 'El resumen del tratamiento esta disponible en modo de solo lectura.';
    }

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
            'es_detalle_agrupado' => $esDetalleAgrupado,
            'cantidad_unidades_laboratorio' => $esDetalleAgrupado ? $cantidadAgrupada : 1,
            'requiere_regularizacion_administrativa' => !$cantidadValida && !$esDetalleAgrupado,
            'requiere_regularizacion_unidades' => $esDetalleAgrupado && !$regularizacionUnidades && !$trabajoActivo,
            'origen_requiere_laboratorio' => isset($config['origen_requiere_laboratorio']) ? $config['origen_requiere_laboratorio'] : null,
            'modo_individualizacion' => $modo
        ),
        'ubicaciones' => $ubicaciones,
        'trabajo_activo' => $trabajoActivoResumen,
        'trabajos_activos' => $trabajosActivos,
        'micro_hilos_activos' => $microHilosActivos,
        'antecedente_historico' => $antecedenteHistorico,
        'tecnicos_disponibles' => $tecnicos,
        'tecnico_puede_quedar_pendiente' => true,
        'puede_ver_resumen' => $puedeVerResumen,
        'puede_abrir_ficha' => $puedeAbrirFicha,
        'puede_iniciar' => $puedeIniciar,
		'puede_iniciar_trabajos_agrupados' => $puedeIniciarAgrupados,
		'puede_asignar_ubicacion' => $puedeAsignarUbicacion,
		'puede_regularizar_unidades' => $puedeRegularizarUnidades,
        'puede_asegurar_hilo' => $puedeAsegurarHilo,
		'requiere_regularizacion_administrativa' => !$cantidadValida && !$esDetalleAgrupado,
		'requiere_regularizacion_unidades' => $esDetalleAgrupado && !$regularizacionUnidades && !$trabajoActivo,
		'regularizacion_unidades' => $regularizacionUnidades,
		'regularizacion_administrativa' => !$cantidadValida && !$esDetalleAgrupado ? array(
			'requerida' => true,
			'motivo' => 'cantidad_no_entera_incompatible',
			'cantidad' => $cantidad,
			'responsable' => 'Administracion',
			'mensaje' => 'Conservar la venta y regularizar la relacion entre sus unidades y el seguimiento de laboratorio.'
		) : null,
        'bloqueos' => $bloqueos,
        'avisos' => $avisos,
        'acciones_permitidas' => $accionesPermitidas,
        'mensaje_contexto' => $mensajeContexto
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
        .'pt.nombre_persona AS nombre_tecnico,utec.tipo AS tecnico_rol,utec.url AS tecnico_avatar,'
        .'pcu.nombre_persona AS nombre_custodio,ucu.tipo AS custodio_rol,ucu.url AS custodio_avatar,'
        .'pdoc.nombre_persona AS nombre_doctor,'
        .'udoc.tipo AS doctor_rol,udoc.url AS doctor_avatar,'
        .'pini.nombre_persona AS nombre_iniciador,'
        .'uini.tipo AS iniciador_rol,uini.url AS iniciador_avatar,'
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
        .'LEFT JOIN usuario utec ON utec.cod_usuario=tl.cod_tecnico_usuarioFK '
        .'LEFT JOIN persona pt ON pt.cod_persona=tl.cod_tecnico_usuarioFK '
        .'LEFT JOIN usuario ucu ON ucu.cod_usuario=tl.cod_custodio_actualFK '
        .'LEFT JOIN persona pcu ON pcu.cod_persona=tl.cod_custodio_actualFK '
        .'LEFT JOIN usuario udoc ON udoc.cod_usuario=COALESCE(tl.cod_especialistaFK,tl.cod_usuarioFK_create) '
        .'LEFT JOIN persona pdoc ON pdoc.cod_persona=COALESCE(tl.cod_especialistaFK,tl.cod_usuarioFK_create) '
        .'LEFT JOIN usuario uini ON uini.cod_usuario=tl.cod_usuarioFK_create '
        .'LEFT JOIN persona pini ON pini.cod_persona=tl.cod_usuarioFK_create '
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

function trabajoLaboratorioAccionNaturalContexto($accion, $contexto)
{
    $contexto = is_array($contexto) ? $contexto : array();
    $localPropio = !empty($contexto['local_propio']);
    $custodio = !empty($contexto['custodio']);
    $tecnicoFormal = !empty($contexto['tecnico_formal']);
    $tecnico = !empty($contexto['tecnico']) && $tecnicoFormal;
    $doctor = !empty($contexto['doctor']);
    $auditor = !empty($contexto['auditor']);
    $estado = isset($contexto['estado_derivado'])
        ? (string)$contexto['estado_derivado']
        : (isset($contexto['estado']) ? (string)$contexto['estado'] : '');
    if (in_array($accion, array(
        'iniciarTrabajo',
        'iniciarTrabajosAgrupados',
        'asignarTecnico'
    ), true)) {
        return true;
    }
    if ($accion === 'guardarRegularizacionUnidades') {
        return $localPropio;
    }
    if ($accion === 'iniciarTransferencia') {
        return $custodio || ($localPropio && !$tecnicoFormal);
    }
    if ($accion === 'tomarHilo') {
        /* La custodia acredita recepcion fisica, no un rol ni un permiso
           operativo determinado. La cuenta autenticada debe seguir activa. */
        return true;
    }
    if ($accion === 'confirmarRecepcion' || $accion === 'iniciarDevolucion') {
        return $tecnico;
    }
    if ($accion === 'agregarEvidencia' || $accion === 'agregarNota'
        || $accion === 'registrarNovedad') {
        return $custodio || $tecnico;
    }
    if ($accion === 'rectificarCustodia') {
        return $auditor;
    }
    if ($accion === 'confirmarDevolucion') {
        return $localPropio && !$tecnicoFormal;
    }
    if ($accion === 'solicitarAjuste' || $accion === 'aprobarTrabajo') {
        return $doctor && $localPropio;
    }
    if ($accion === 'registrarInstalacion') {
        return $custodio;
    }
    if ($accion === 'cancelarTrabajo') {
        return $localPropio && !$tecnicoFormal;
    }
    return false;
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
    return trabajoLaboratorioAccionNaturalContexto($accion, array(
        'local_propio' => $localPropio,
        'custodio' => $custodio,
        'tecnico_formal' => $tecnicoFormal ? true : false,
        'tecnico' => $tecnico,
        'doctor' => trabajoLaboratorioUsuarioEsDoctor($mysqli, $codUsuario),
        'auditor' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario),
        'estado_derivado' => isset($trabajo['estado_derivado'])
            ? (string)$trabajo['estado_derivado'] : ''
    ));
}

function trabajoLaboratorioAccionRequiereMotivoExcepcionAuditor($mysqli, $codUsuario, $trabajo, $accion)
{
    return trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
        && !trabajoLaboratorioAccionNaturalUsuario($mysqli, $codUsuario, $trabajo, $accion);
}

function trabajoLaboratorioAccionesConMotivoAuditor($mysqli, $codUsuario, $trabajo, $acciones)
{
    $acciones = is_array($acciones) ? $acciones : array();
    foreach ($acciones as $accion => $permitida) {
        if (empty($permitida)) {
            continue;
        }
        $definicion = is_array($permitida) ? $permitida : array();
        $definicion['permitido'] = true;
        $definicion['requiere_motivo_excepcion'] =
            trabajoLaboratorioAccionRequiereMotivoExcepcionAuditor(
                $mysqli,
                $codUsuario,
                $trabajo,
                $accion
            ) ? 1 : 0;
        $acciones[$accion] = $definicion;
    }
    return $acciones;
}

function trabajoLaboratorioExigirMotivoExcepcionAuditor($mysqli, $codUsuario, $trabajo, $accion, $entrada)
{
    if (!trabajoLaboratorioAccionRequiereMotivoExcepcionAuditor(
        $mysqli,
        $codUsuario,
        $trabajo,
        $accion
    )) {
        return '';
    }
    $motivo = isset($entrada['motivo_excepcion'])
        ? trabajoLaboratorioTextoEntrada($entrada['motivo_excepcion'], 750) : '';
    if ($motivo === '') {
        trabajoLaboratorioLanzar(
            'motivo_excepcion_auditor_requerido',
            'La intervencion excepcional del auditor requiere una observacion.'
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
    $usuarioCatalogos = trabajoLaboratorioUsuario($mysqli, intval($codUsuario));
    $puedeConsultarCatalogos = $usuarioCatalogos && (
        trabajoLaboratorioTienePermiso(
            $mysqli,
            intval($codUsuario),
            'VERTRABAJOSLABORATORIO'
        )
        || trabajoLaboratorioTienePermiso(
            $mysqli,
            intval($codUsuario),
            'CREARTRABAJOLABORATORIO'
        )
        || trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario))
    );
    if (!$puedeConsultarCatalogos) {
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
    $usuario = $usuarioCatalogos;
    $todosLocales = true;
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
        array('codigo' => 'pendiente_tecnico', 'nombre' => 'Tecnico pendiente'),
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
        'tipos_novedad_custodia' => array(
            'modificacion_trabajo', 'cambio_color', 'ajuste_solicitado',
            'problema_detectado', 'pieza_danada', 'falta_informacion',
            'trabajo_listo', 'solicitud_confirmacion_clinica', 'observacion_general'
        ),
        'motivos_recepcion_sin_foto' => array(
            'falla_dispositivo', 'imposibilidad_operativa', 'foto_no_disponible', 'otro'
        ),
        'estados' => array(
            'pendiente_tecnico', 'pendiente_entrega_mecanico', 'en_transferencia_mecanico', 'en_laboratorio',
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

function trabajoLaboratorioRespuestaSinCostos($valor)
{
    if (!is_array($valor)) {
        return $valor;
    }
    $salida = array();
    $clavesCosto = array(
        'costo', 'costo_estimado', 'costo_original', 'costo_resuelto',
        'costo_legacy', 'costo_snapshot'
    );
    foreach ($valor as $clave => $item) {
        if (in_array((string)$clave, $clavesCosto, true)) {
            continue;
        }
        $salida[$clave] = trabajoLaboratorioRespuestaSinCostos($item);
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

function trabajoLaboratorioLimitesMedia()
{
    static $limites = null;
    if ($limites !== null) {
        return $limites;
    }
    $maximoAplicacion = 10 * 1024 * 1024;
    $maximoArchivosAplicacion = 5;
    $margenSolicitud = 512 * 1024;
    $limiteSubida = trabajoLaboratorioBytesConfiguracion(ini_get('upload_max_filesize'));
    $limiteSolicitud = trabajoLaboratorioBytesConfiguracion(ini_get('post_max_size'));
    $maximoArchivo = $maximoAplicacion;
    if ($limiteSubida > 0) {
        $maximoArchivo = min($maximoArchivo, $limiteSubida);
    }
    /* Una evidencia inicial puede viajar en base64 y crecer aproximadamente
       un tercio. Se reserva ademas margen para los restantes campos POST. */
    if ($limiteSolicitud > 0) {
        $maximoPorSolicitud = intval(floor(max(1, $limiteSolicitud - $margenSolicitud) * 0.75));
        $maximoArchivo = min($maximoArchivo, max(1, $maximoPorSolicitud));
    }
    $maximoArchivos = $maximoArchivosAplicacion;
    if ($limiteSolicitud > 0) {
        while ($maximoArchivos > 1) {
            $estimado = ($maximoArchivos * $maximoArchivo)
                + intval(ceil($maximoArchivo / 3))
                + $margenSolicitud;
            if ($estimado <= $limiteSolicitud) {
                break;
            }
            $maximoArchivos--;
        }
    }
    $limites = array(
        'max_archivos' => max(1, intval($maximoArchivos)),
        'max_bytes_archivo' => max(1, intval($maximoArchivo)),
        'max_bytes_solicitud' => $limiteSolicitud > 0 ? intval($limiteSolicitud) : -1
    );
    return $limites;
}

function trabajoLaboratorioMaximoArchivosMedia()
{
    $limites = trabajoLaboratorioLimitesMedia();
    return intval($limites['max_archivos']);
}

function trabajoLaboratorioMaximoBytesMedia()
{
    $limites = trabajoLaboratorioLimitesMedia();
    return intval($limites['max_bytes_archivo']);
}

function trabajoLaboratorioTamanoMediaTexto($bytes)
{
    $megabytes = max(0.1, floatval($bytes) / 1048576);
    $texto = abs($megabytes - round($megabytes)) < 0.01
        ? (string)intval(round($megabytes))
        : number_format($megabytes, 1, ',', '');
    return $texto.' MB';
}

function trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto, $permitirDocumentos = false)
{
    if (!is_array($evidencia)) {
        trabajoLaboratorioLanzar('evidencia_invalida', 'La evidencia enviada no es valida.');
    }
    $maximoBytes = trabajoLaboratorioMaximoBytesMedia();
    $maximoBase64 = intval(ceil($maximoBytes * 4 / 3)) + 256;
    $base64 = isset($evidencia['data_base64']) ? trim((string)$evidencia['data_base64']) : '';
    if ($base64 === '' || strlen($base64) > $maximoBase64) {
        trabajoLaboratorioLanzar('evidencia_invalida', 'El archivo esta vacio o supera el limite permitido.');
    }
    if (preg_match('#^data:([^;]+);base64,(.*)$#s', $base64, $coincidencia)) {
        $base64 = $coincidencia[2];
    }
    $binario = base64_decode(preg_replace('/\s+/', '', $base64), true);
    if ($binario === false || strlen($binario) === 0 || strlen($binario) > $maximoBytes) {
        trabajoLaboratorioLanzar(
            'evidencia_invalida',
            'El archivo no es valido o supera '.trabajoLaboratorioTamanoMediaTexto($maximoBytes).'.'
        );
    }
    if (!class_exists('finfo')) {
        trabajoLaboratorioLanzar('validacion_media_no_disponible', 'El servidor no puede validar el archivo.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($binario);
    $extensiones = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
    if ($permitirDocumentos) {
        $extensiones['application/pdf'] = 'pdf';
    }
    if (!isset($extensiones[$mime])) {
        trabajoLaboratorioLanzar(
            'formato_media_no_permitido',
            $permitirDocumentos
                ? 'Solo se admiten imagenes JPG, PNG o WEBP y documentos PDF.'
                : 'Solo se admiten imagenes JPG, PNG o WEBP.'
        );
    }
    $esImagen = strpos($mime, 'image/') === 0;
    $pixeles = 0;
    if ($esImagen) {
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
    } elseif (substr($binario, 0, 5) !== '%PDF-') {
        trabajoLaboratorioLanzar('documento_media_invalido', 'El documento PDF no posee una cabecera valida.');
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
    if ($esImagen && function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')) {
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
        'tecnico_asignado' => 'tecnico de laboratorio asignado',
        'transferencia_mecanico_iniciada' => 'envio al laboratorio iniciado',
        'recepcion_mecanico_confirmada' => 'recepcion por el tecnico confirmada',
        'evidencia_agregada' => 'evidencia agregada',
        'nota_agregada' => 'nota de seguimiento agregada',
        'devolucion_iniciada' => 'devolucion a la clinica iniciada',
        'devolucion_confirmada' => 'recepcion en clinica confirmada',
        'hilo_tomado' => 'nuevo responsable tomo el hilo de custodia',
        'datos_trabajo_actualizados' => 'datos de la version activa actualizados',
        'novedad_custodia' => 'novedad registrada durante la custodia',
        'custodia_rectificada' => 'custodia rectificada por Administracion',
        'ajuste_solicitado' => 'ajuste solicitado',
        'trabajo_aprobado' => 'trabajo aprobado',
        'instalacion_registrada' => 'instalacion registrada',
        'trabajo_cancelado' => 'trabajo cancelado',
        'registro_historico_convalidado' => 'registro historico convalidado por Administracion',
        'registro_historico_continuado' => 'trabajo historico continuado en el circuito operativo',
        'instalacion_historica_declarada' => 'trabajo historico instalado y entregado'
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

function trabajoLaboratorioTiposEventoCustodia()
{
    return array(
        'trabajo_iniciado',
        'recepcion_mecanico_confirmada',
        'devolucion_confirmada',
        'hilo_tomado',
        'custodia_rectificada',
        'registro_historico_continuado',
        'instalacion_historica_declarada'
    );
}

function trabajoLaboratorioEventoAbreCustodia($tipoEvento)
{
    return in_array((string)$tipoEvento, trabajoLaboratorioTiposEventoCustodia(), true);
}

function trabajoLaboratorioActualizarPunteroCustodia(
    $mysqli,
    $trabajo,
    $idEvento,
    $codCustodio,
    $versionResultante
) {
    if (!trabajoLaboratorioHiloCustodiaDisponible($mysqli)) {
        return;
    }
    $idTrabajo = intval($trabajo['id']);
    $idEvento = intval($idEvento);
    $codCustodio = intval($codCustodio);
    $versionResultante = intval($versionResultante);
    if ($idTrabajo <= 0 || $idEvento <= 0 || $codCustodio <= 0 || $versionResultante <= 0) {
        trabajoLaboratorioLanzar(
            'puntero_custodia_invalido',
            'No se pudo identificar el nuevo periodo de custodia.'
        );
    }
    $stmt = $mysqli->prepare(
        'UPDATE trabajo_laboratorio SET id_evento_custodia_actualFK=? '
        .'WHERE id=? AND cod_custodio_actualFK=? AND version=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'puntero_custodia_no_guardado',
            'No se pudo preparar el enlace del nuevo periodo de custodia.'
        );
    }
    $stmt->bind_param('iiii', $idEvento, $idTrabajo, $codCustodio, $versionResultante);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'puntero_custodia_no_guardado',
            'El trabajo cambio antes de enlazar el nuevo periodo de custodia.'
        );
    }
    $stmt->close();
}

function trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, $bloquear = false)
{
    $idTrabajo = isset($trabajo['id']) ? intval($trabajo['id']) : 0;
    if ($idTrabajo <= 0) {
        return 0;
    }
    if (trabajoLaboratorioHiloCustodiaDisponible($mysqli)
        && isset($trabajo['id_evento_custodia_actualFK'])
        && intval($trabajo['id_evento_custodia_actualFK']) > 0) {
        return intval($trabajo['id_evento_custodia_actualFK']);
    }
    $tipos = trabajoLaboratorioTiposEventoCustodia();
    $tipos[] = 'instalacion_registrada';
    $marcas = implode(',', array_fill(0, count($tipos), '?'));
    $sql = 'SELECT id FROM trabajo_laboratorio_evento WHERE id_trabajoFK=? '
        .'AND cod_custodio_nuevoFK=? AND tipo_evento IN ('.$marcas.') '
        .'ORDER BY fecha_servidor DESC,id DESC LIMIT 1';
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $valores = array($idTrabajo, intval($trabajo['cod_custodio_actualFK']));
    foreach ($tipos as $tipo) {
        $valores[] = $tipo;
    }
    trabajoLaboratorioVincularParametros($stmt, 'ii'.str_repeat('s', count($tipos)), $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? intval($fila['id']) : 0;
}

function trabajoLaboratorioRegistrarEvento($mysqli, $trabajo, $idCiclo, $idIdempotencia, $tipoEvento,
    $codUsuario, $versionResultante, $observacion = '', $metadata = array(), $idTransferencia = null,
    $custodioAnterior = null, $custodioNuevo = null, $remitente = null, $destinatario = null,
    $codConsulta = null, $codEvolucion = null, $idEventoCustodia = null)
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
    $idEventoCustodia = $idEventoCustodia === null ? null : intval($idEventoCustodia);
    $codConsulta = $codConsulta === null ? null : intval($codConsulta);
    $codEvolucion = $codEvolucion === null ? null : intval($codEvolucion);
    $custodioAnterior = $custodioAnterior === null ? null : intval($custodioAnterior);
    $custodioNuevo = $custodioNuevo === null ? null : intval($custodioNuevo);
    $remitente = $remitente === null ? null : intval($remitente);
    $destinatario = $destinatario === null ? null : intval($destinatario);
    $codLocal = intval($trabajo['cod_localFK']);
    $hilo = intval($trabajo['cod_interConsultaFK']);
    $estructuraHilo = trabajoLaboratorioHiloCustodiaDisponible($mysqli);
    if ($estructuraHilo) {
        $actor = trabajoLaboratorioUsuario($mysqli, $codUsuario);
        $usuarioUbicacion = $actor;
        if (!empty($metadata['nodo_custodia']) && $custodioNuevo !== null) {
            $custodioUbicacion = trabajoLaboratorioUsuario($mysqli, $custodioNuevo);
            if ($custodioUbicacion) {
                $usuarioUbicacion = $custodioUbicacion;
            }
        }
        if ($usuarioUbicacion && intval($usuarioUbicacion['cod_localFK']) > 0) {
            $codLocal = intval($usuarioUbicacion['cod_localFK']);
        }
        $actorNombre = trabajoLaboratorioTextoBaseDatos(
            trabajoLaboratorioTextoUtf8(
                $actor && isset($actor['nombre_persona']) ? $actor['nombre_persona'] : 'Usuario autorizado'
            ),
            255
        );
        $actorRol = trabajoLaboratorioTextoBaseDatos(
            trabajoLaboratorioTextoUtf8(
                $actor && isset($actor['tipo']) ? $actor['tipo'] : 'usuario'
            ),
            100
        );
        $localNombre = trabajoLaboratorioTextoBaseDatos(
            trabajoLaboratorioTextoUtf8(
                $usuarioUbicacion && isset($usuarioUbicacion['nombre_local'])
                    && trim((string)$usuarioUbicacion['nombre_local']) !== ''
                    ? $usuarioUbicacion['nombre_local']
                    : (isset($trabajo['nombre_local']) ? $trabajo['nombre_local'] : $trabajo['sigla_local_snapshot'])
            ),
            255
        );
        $stmt = $mysqli->prepare(
            'INSERT INTO trabajo_laboratorio_evento '
            .'(id_trabajoFK,id_cicloFK,id_transferenciaFK,id_idempotenciaFK,id_evento_custodiaFK,'
            .'cod_consulta_origenFK,cod_evolucion_origenFK,tipo_evento,cod_usuario_actorFK,'
            .'actor_nombre_snapshot,actor_rol_snapshot,cod_custodio_anteriorFK,cod_custodio_nuevoFK,'
            .'cod_remitenteFK,cod_destinatario_previstoFK,cod_localFK,local_nombre_snapshot,fecha_servidor,'
            .'observacion,metadata_json,version_resultante,cod_interConsultaFK,cod_mensaje_hiloFK) '
            .'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?)'
        );
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO trabajo_laboratorio_evento '
            .'(id_trabajoFK,id_cicloFK,id_transferenciaFK,id_idempotenciaFK,cod_consulta_origenFK,'
            .'cod_evolucion_origenFK,tipo_evento,cod_usuario_actorFK,cod_custodio_anteriorFK,'
            .'cod_custodio_nuevoFK,cod_remitenteFK,cod_destinatario_previstoFK,cod_localFK,fecha_servidor,'
            .'observacion,metadata_json,version_resultante,cod_interConsultaFK,cod_mensaje_hiloFK) '
            .'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?)'
        );
    }
    if (!$stmt) {
        trabajoLaboratorioLanzar('evento_no_guardado', 'No se pudo registrar la trazabilidad del trabajo.');
    }
    if ($estructuraHilo) {
        $valoresEvento = array(
            $idTrabajo, $idCiclo, $idTransferencia, $idIdempotencia, $idEventoCustodia,
            $codConsulta, $codEvolucion, $tipoEvento, $codUsuario, $actorNombre, $actorRol,
            $custodioAnterior, $custodioNuevo, $remitente, $destinatario, $codLocal, $localNombre,
            $observacionBd, $metadataJson, $versionResultante, $hilo, $idMensaje
        );
        $tiposEvento = str_repeat('i', 7).'si'.'ss'.str_repeat('i', 5).'sss'.str_repeat('i', 3);
        trabajoLaboratorioVincularParametros($stmt, $tiposEvento, $valoresEvento);
    } else {
        $stmt->bind_param(
            'iiiiiisiiiiiissiii',
            $idTrabajo, $idCiclo, $idTransferencia, $idIdempotencia, $codConsulta,
            $codEvolucion, $tipoEvento, $codUsuario, $custodioAnterior, $custodioNuevo,
            $remitente, $destinatario, $codLocal, $observacionBd, $metadataJson,
            $versionResultante, $hilo, $idMensaje
        );
    }
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('evento_no_guardado', 'No se pudo registrar la trazabilidad del trabajo.');
    }
    $id = intval($stmt->insert_id);
    $stmt->close();
    if ($estructuraHilo && trabajoLaboratorioEventoAbreCustodia($tipoEvento)) {
        trabajoLaboratorioActualizarPunteroCustodia(
            $mysqli,
            $trabajo,
            $id,
            $custodioNuevo,
            $versionResultante
        );
    }
    trabajoLaboratorioSincronizarAvanceClinico(
        $mysqli,
        $trabajo,
        $codUsuario,
        $tipoEvento
    );
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

/**
 * Presentacion estable para recorridos compactos. El color es semantico y no
 * contiene valores CSS: la interfaz decide el tono final sin depender solo del
 * color para explicar el estado.
 */
function trabajoLaboratorioPresentacionEstadoRecorrido($estado)
{
    $mapa = array(
        'pendiente_tecnico' => array(
            'nombre' => 'Tecnico pendiente', 'semantica' => 'advertencia', 'color' => 'naranja'
        ),
        'pendiente_entrega_mecanico' => array(
            'nombre' => 'Pendiente de entrega al mecanico', 'semantica' => 'pendiente', 'color' => 'amarillo'
        ),
        'en_transferencia_mecanico' => array(
            'nombre' => 'En traslado al laboratorio', 'semantica' => 'en_transito', 'color' => 'violeta'
        ),
        'en_laboratorio' => array(
            'nombre' => 'En poder del mecanico', 'semantica' => 'en_proceso', 'color' => 'azul'
        ),
        'en_transferencia_clinica' => array(
            'nombre' => 'En traslado a la clinica', 'semantica' => 'en_transito', 'color' => 'violeta'
        ),
        'pendiente_revision' => array(
            'nombre' => 'Pendiente de revision clinica', 'semantica' => 'revision', 'color' => 'naranja'
        ),
        'ajuste_solicitado' => array(
            'nombre' => 'Ajuste solicitado', 'semantica' => 'advertencia', 'color' => 'rojo'
        ),
        'listo_instalacion' => array(
            'nombre' => 'Listo para instalar', 'semantica' => 'listo', 'color' => 'turquesa'
        ),
        'instalado' => array(
            'nombre' => 'Instalado', 'semantica' => 'completo', 'color' => 'verde'
        ),
        'cancelado' => array(
            'nombre' => 'Cancelado', 'semantica' => 'inactivo', 'color' => 'gris'
        )
    );
    $estado = (string)$estado;
    if (isset($mapa[$estado])) {
        return $mapa[$estado];
    }
    return array(
        'nombre' => $estado !== '' ? str_replace('_', ' ', $estado) : 'Sin estado operativo',
        'semantica' => 'sin_definir',
        'color' => 'gris'
    );
}

function trabajoLaboratorioEstadoEventoRecorrido($tipoEvento, $metadata = array())
{
    $mapa = array(
        'trabajo_iniciado' => 'pendiente_entrega_mecanico',
        'tecnico_asignado' => 'pendiente_entrega_mecanico',
        'transferencia_mecanico_iniciada' => 'en_transferencia_mecanico',
        'recepcion_mecanico_confirmada' => 'en_laboratorio',
        'devolucion_iniciada' => 'en_transferencia_clinica',
        'devolucion_confirmada' => 'pendiente_revision',
        'ajuste_solicitado' => 'ajuste_solicitado',
        'trabajo_aprobado' => 'listo_instalacion',
        'instalacion_registrada' => 'instalado',
        'trabajo_cancelado' => 'cancelado'
    );
    $tipoEvento = (string)$tipoEvento;
    if (is_array($metadata) && isset($metadata['estado_resultante'])
        && trim((string)$metadata['estado_resultante']) !== '') {
        return (string)$metadata['estado_resultante'];
    }
    if ($tipoEvento === 'registro_historico_convalidado'
        && is_array($metadata)
        && isset($metadata['estado_declarado'])) {
        return (string)$metadata['estado_declarado'];
    }
    return isset($mapa[$tipoEvento]) ? $mapa[$tipoEvento] : '';
}

function trabajoLaboratorioEtiquetaEventoRecorrido($tipoEvento)
{
    $mapa = array(
        'trabajo_iniciado' => 'Trabajo iniciado',
        'tecnico_asignado' => 'Tecnico asignado',
        'registro_historico_convalidado' => 'Registro historico integrado',
        'registro_historico_continuado' => 'Trabajo historico continuado',
        'instalacion_historica_declarada' => 'Instalado y entregado',
        'transferencia_mecanico_iniciada' => 'Envio al laboratorio iniciado',
        'recepcion_mecanico_confirmada' => 'Recepcion del mecanico confirmada',
        'devolucion_iniciada' => 'Retorno a la clinica iniciado',
        'devolucion_confirmada' => 'Recepcion en clinica confirmada',
        'hilo_tomado' => 'Hilo tomado',
        'novedad_custodia' => 'Novedad de custodia',
        'custodia_rectificada' => 'Custodia rectificada',
        'ajuste_solicitado' => 'Ajuste solicitado',
        'trabajo_aprobado' => 'Trabajo aprobado',
        'instalacion_registrada' => 'Instalacion registrada',
        'trabajo_cancelado' => 'Trabajo cancelado'
    );
    $tipoEvento = (string)$tipoEvento;
    return isset($mapa[$tipoEvento]) ? $mapa[$tipoEvento] : str_replace('_', ' ', $tipoEvento);
}

function trabajoLaboratorioPersonaRecorrido($codUsuario, $nombre, $rol = null, $avatar = null)
{
    return array(
        'cod_usuario' => $codUsuario === null ? null : intval($codUsuario),
        'nombre' => trabajoLaboratorioTextoUtf8($nombre),
        'rol' => trabajoLaboratorioTextoUtf8($rol),
        'avatar' => trabajoLaboratorioTextoUtf8($avatar)
    );
}

/**
 * Recupera en una sola consulta los hitos operativos de todos los trabajos de
 * una pagina. No incluye notas ni evidencias como nodos; si un hito tiene
 * media, solamente se expone su identificador protegido.
 */
function trabajoLaboratorioRecorridosPorTrabajos($mysqli, $trabajos)
{
    $filasPorId = array();
    $recorridos = array();
    foreach ((array)$trabajos as $trabajo) {
        if (!is_array($trabajo) || !isset($trabajo['id'])) {
            continue;
        }
        $idTrabajo = intval($trabajo['id']);
        if ($idTrabajo <= 0) {
            continue;
        }
        $filasPorId[$idTrabajo] = $trabajo;
        $recorridos[$idTrabajo] = array();
    }
    $ids = array_keys($filasPorId);
    if (count($ids) === 0) {
        return $recorridos;
    }

    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $sql = 'SELECT e.id,e.id_trabajoFK,e.id_cicloFK,e.tipo_evento,e.cod_usuario_actorFK,'
        .'e.cod_custodio_anteriorFK,e.cod_custodio_nuevoFK,e.cod_remitenteFK,'
        .'e.cod_destinatario_previstoFK,e.cod_localFK,e.fecha_servidor,e.observacion,'
        .'e.metadata_json,e.version_resultante,pa.nombre_persona AS actor_nombre,'
        .'ua.tipo AS actor_rol,ua.url AS actor_avatar,pr.nombre_persona AS remitente_nombre,'
        .'pd.nombre_persona AS destinatario_nombre,pca.nombre_persona AS custodio_anterior_nombre,'
        .'pcn.nombre_persona AS custodio_nuevo_nombre,l.Nombre AS local_nombre,'
        .'c.numero_ciclo,c.tipo AS tipo_ciclo,'
        .'(SELECT MIN(me.id) FROM trabajo_laboratorio_media me WHERE me.id_eventoFK=e.id) AS miniatura_media_id '
        .'FROM trabajo_laboratorio_evento e '
        .'LEFT JOIN persona pa ON pa.cod_persona=e.cod_usuario_actorFK '
        .'LEFT JOIN usuario ua ON ua.cod_usuario=e.cod_usuario_actorFK '
        .'LEFT JOIN persona pr ON pr.cod_persona=e.cod_remitenteFK '
        .'LEFT JOIN persona pd ON pd.cod_persona=e.cod_destinatario_previstoFK '
        .'LEFT JOIN persona pca ON pca.cod_persona=e.cod_custodio_anteriorFK '
        .'LEFT JOIN persona pcn ON pcn.cod_persona=e.cod_custodio_nuevoFK '
        .'LEFT JOIN local l ON l.cod_local=e.cod_localFK '
        .'LEFT JOIN trabajo_laboratorio_ciclo c ON c.id=e.id_cicloFK '
        .'WHERE e.id_trabajoFK IN ('.$marcas.') '
        ."AND e.tipo_evento NOT IN ('nota_agregada','evidencia_agregada','hilo_tomado',"
        ."'novedad_custodia','datos_trabajo_actualizados','custodia_rectificada') "
        .'ORDER BY e.id_trabajoFK ASC,e.fecha_servidor ASC,e.id ASC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('recorrido_no_disponible', 'No se pudo preparar el recorrido de los trabajos.');
    }
    $valores = $ids;
    trabajoLaboratorioVincularParametros($stmt, str_repeat('i', count($ids)), $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('recorrido_no_disponible', 'No se pudo consultar el recorrido de los trabajos.');
    }
    $resultado = $stmt->get_result();
    $fechaAnterior = array();
    while ($fila = $resultado->fetch_assoc()) {
        $idTrabajo = intval($fila['id_trabajoFK']);
        if (!isset($recorridos[$idTrabajo])) {
            continue;
        }
        $metadata = trabajoLaboratorioDecodificarJson(
            trabajoLaboratorioTextoUtf8($fila['metadata_json']),
            array()
        );
        $estado = trabajoLaboratorioEstadoEventoRecorrido($fila['tipo_evento'], $metadata);
        $presentacion = trabajoLaboratorioPresentacionEstadoRecorrido($estado);
        $numeroCiclo = intval($fila['numero_ciclo']);
        $fechaActual = trabajoLaboratorioTimestampSistema($fila['fecha_servidor']);
        $dias = isset($fechaAnterior[$idTrabajo]) && $fechaAnterior[$idTrabajo] && $fechaActual
            ? max(0, intval(floor(($fechaActual - $fechaAnterior[$idTrabajo]) / 86400))) : 0;
        $recorridos[$idTrabajo][] = array(
            'id' => intval($fila['id']),
            'id_evento' => intval($fila['id']),
            'origen' => 'operativo',
            'tipo_evento' => trabajoLaboratorioTextoUtf8($fila['tipo_evento']),
            'titulo' => trabajoLaboratorioEtiquetaEventoRecorrido($fila['tipo_evento']),
            'fecha_servidor' => $fila['fecha_servidor'],
            'actor' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_usuario_actorFK'], $fila['actor_nombre'], $fila['actor_rol'], $fila['actor_avatar']
            ),
            'cod_local' => $fila['cod_localFK'] === null ? null : intval($fila['cod_localFK']),
            'local' => trabajoLaboratorioTextoUtf8($fila['local_nombre']),
            'id_ciclo' => $fila['id_cicloFK'] === null ? null : intval($fila['id_cicloFK']),
            'numero_ciclo' => $numeroCiclo > 0 ? $numeroCiclo : null,
            'ciclo_etiqueta' => $numeroCiclo <= 1 ? 'Original' : 'Ajuste '.intval($numeroCiclo - 1),
            'tipo_ciclo' => trabajoLaboratorioTextoUtf8($fila['tipo_ciclo']),
            'dias_desde_anterior' => $dias,
            'custodio_anterior' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_custodio_anteriorFK'], $fila['custodio_anterior_nombre']
            ),
            'custodio_nuevo' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_custodio_nuevoFK'], $fila['custodio_nuevo_nombre']
            ),
            'remitente' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_remitenteFK'], $fila['remitente_nombre']
            ),
            'destinatario' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_destinatario_previstoFK'], $fila['destinatario_nombre']
            ),
            'observacion' => trabajoLaboratorioTextoUtf8($fila['observacion']),
            'miniatura_media_id' => $fila['miniatura_media_id'] === null
                ? null : intval($fila['miniatura_media_id']),
            'pendiente' => false,
            'estado' => $estado !== '' ? $estado : null,
            'estado_nombre' => trabajoLaboratorioTextoUtf8($presentacion['nombre']),
            'estado_semantico' => $presentacion['semantica'],
            'color_semantico' => $presentacion['color'],
            'version_resultante' => intval($fila['version_resultante'])
        );
        if ($fechaActual) {
            $fechaAnterior[$idTrabajo] = $fechaActual;
        }
    }
    $stmt->close();

    foreach ($recorridos as $idTrabajo => &$nodos) {
        if (count($nodos) === 0) {
            continue;
        }
        $estadoActual = isset($filasPorId[$idTrabajo]['estado_derivado'])
            ? (string)$filasPorId[$idTrabajo]['estado_derivado'] : '';
        $ultimo = count($nodos) - 1;
        $nodos[$ultimo]['pendiente'] = !in_array($estadoActual, array('instalado', 'cancelado'), true);
        if ($nodos[$ultimo]['estado'] === null && $estadoActual !== '') {
            $presentacion = trabajoLaboratorioPresentacionEstadoRecorrido($estadoActual);
            $nodos[$ultimo]['estado'] = $estadoActual;
            $nodos[$ultimo]['estado_nombre'] = trabajoLaboratorioTextoUtf8($presentacion['nombre']);
            $nodos[$ultimo]['estado_semantico'] = $presentacion['semantica'];
            $nodos[$ultimo]['color_semantico'] = $presentacion['color'];
        }
    }
    unset($nodos);
    return $recorridos;
}

/**
 * Proyecta el recorrido que existia cuando se emitio cada mensaje del Hilo.
 *
 * El limite se obtiene de cod_mensaje_hiloFK, por lo que nunca usa el estado
 * actual del trabajo para completar o alterar un mensaje anterior. Las
 * ediciones, notas y evidencias se incorporan al nodo de custodia al que
 * pertenecen, sin fabricar nodos adicionales.
 */
function trabajoLaboratorioMiniHilosPorMensajes($mysqli, $referencias)
{
    $salida = array();
    $mensajes = array();
    $trabajosEsperados = array();
    foreach ((array)$referencias as $referencia) {
        $idMensaje = isset($referencia['cod_mensaje'])
            ? intval($referencia['cod_mensaje']) : 0;
        $idTrabajo = isset($referencia['id_trabajo'])
            ? intval($referencia['id_trabajo']) : 0;
        if ($idMensaje <= 0 || $idTrabajo <= 0) {
            continue;
        }
        $mensajes[$idMensaje] = $idMensaje;
        $trabajosEsperados[$idMensaje] = $idTrabajo;
    }
    $mensajes = array_values($mensajes);
    if (count($mensajes) === 0
        || !trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_evento')
        || !trabajoLaboratorioColumnaExiste(
            $mysqli,
            'trabajo_laboratorio_evento',
            'cod_mensaje_hiloFK'
        )) {
        return $salida;
    }

    $marcasMensajes = implode(',', array_fill(0, count($mensajes), '?'));
    $stmt = $mysqli->prepare(
        'SELECT e.id,e.id_trabajoFK,e.cod_mensaje_hiloFK,e.fecha_servidor,'
        .'e.tipo_evento,e.metadata_json,tl.codigo_visible '
        .'FROM trabajo_laboratorio_evento e '
        .'INNER JOIN trabajo_laboratorio tl ON tl.id=e.id_trabajoFK '
        .'WHERE e.cod_mensaje_hiloFK IN ('.$marcasMensajes.') '
        .'ORDER BY e.fecha_servidor ASC,e.id ASC'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'mini_hilo_no_disponible',
            'No se pudo preparar el estado historico de los mensajes.'
        );
    }
    $valoresMensajes = $mensajes;
    trabajoLaboratorioVincularParametros(
        $stmt,
        str_repeat('i', count($mensajes)),
        $valoresMensajes
    );
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'mini_hilo_no_disponible',
            'No se pudo consultar el estado historico de los mensajes.'
        );
    }
    $limites = array();
    $idsTrabajos = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $idMensaje = intval($fila['cod_mensaje_hiloFK']);
        $idTrabajo = intval($fila['id_trabajoFK']);
        if (!isset($trabajosEsperados[$idMensaje])
            || intval($trabajosEsperados[$idMensaje]) !== $idTrabajo) {
            continue;
        }
        $limites[$idMensaje] = $fila;
        $idsTrabajos[$idTrabajo] = $idTrabajo;
    }
    $stmt->close();
    $idsTrabajos = array_values($idsTrabajos);
    if (count($limites) === 0 || count($idsTrabajos) === 0) {
        return $salida;
    }

    $marcasTrabajos = implode(',', array_fill(0, count($idsTrabajos), '?'));
    $sqlEventos =
        'SELECT e.id,e.id_trabajoFK,e.id_evento_custodiaFK,e.tipo_evento,'
        .'e.cod_usuario_actorFK,e.actor_nombre_snapshot,e.actor_rol_snapshot,'
        .'e.cod_custodio_nuevoFK,e.cod_localFK,e.local_nombre_snapshot,'
        .'e.fecha_servidor,e.observacion,e.metadata_json,e.version_resultante,'
        .'pa.nombre_persona AS actor_nombre,ua.tipo AS actor_rol,ua.url AS actor_avatar,'
        .'pcn.nombre_persona AS responsable_nombre,ucn.tipo AS responsable_rol,'
        .'ucn.url AS responsable_avatar,l.Nombre AS local_nombre '
        .'FROM trabajo_laboratorio_evento e '
        .'LEFT JOIN persona pa ON pa.cod_persona=e.cod_usuario_actorFK '
        .'LEFT JOIN usuario ua ON ua.cod_usuario=e.cod_usuario_actorFK '
        .'LEFT JOIN persona pcn ON pcn.cod_persona=e.cod_custodio_nuevoFK '
        .'LEFT JOIN usuario ucn ON ucn.cod_usuario=e.cod_custodio_nuevoFK '
        .'LEFT JOIN local l ON l.cod_local=e.cod_localFK '
        .'WHERE e.id_trabajoFK IN ('.$marcasTrabajos.') '
        .'ORDER BY e.id_trabajoFK ASC,e.fecha_servidor ASC,e.id ASC';
    $stmt = $mysqli->prepare($sqlEventos);
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'mini_hilo_no_disponible',
            'No se pudo preparar el recorrido historico de los mensajes.'
        );
    }
    $valoresTrabajos = $idsTrabajos;
    trabajoLaboratorioVincularParametros(
        $stmt,
        str_repeat('i', count($idsTrabajos)),
        $valoresTrabajos
    );
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'mini_hilo_no_disponible',
            'No se pudo consultar el recorrido historico de los mensajes.'
        );
    }
    $eventosPorTrabajo = array();
    $idsEventos = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $idTrabajo = intval($fila['id_trabajoFK']);
        if (!isset($eventosPorTrabajo[$idTrabajo])) {
            $eventosPorTrabajo[$idTrabajo] = array();
        }
        $eventosPorTrabajo[$idTrabajo][] = $fila;
        $idsEventos[intval($fila['id'])] = intval($fila['id']);
    }
    $stmt->close();

    $mediaPorEvento = array();
    $idsEventos = array_values($idsEventos);
    if (count($idsEventos) > 0
        && trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_media')) {
        $marcasEventos = implode(',', array_fill(0, count($idsEventos), '?'));
        $stmtMedia = $mysqli->prepare(
            'SELECT id,id_eventoFK,nombre_original,mime,descripcion '
            .'FROM trabajo_laboratorio_media '
            .'WHERE id_eventoFK IN ('.$marcasEventos.') '
            .'ORDER BY id_eventoFK ASC,id ASC'
        );
        if ($stmtMedia) {
            $valoresEventos = $idsEventos;
            trabajoLaboratorioVincularParametros(
                $stmtMedia,
                str_repeat('i', count($idsEventos)),
                $valoresEventos
            );
            if ($stmtMedia->execute()) {
                $resultadoMedia = $stmtMedia->get_result();
                while ($media = $resultadoMedia->fetch_assoc()) {
                    $idEvento = intval($media['id_eventoFK']);
                    if (!isset($mediaPorEvento[$idEvento])) {
                        $mediaPorEvento[$idEvento] = array();
                    }
                    if (count($mediaPorEvento[$idEvento]) >= 4) {
                        continue;
                    }
                    $mediaPorEvento[$idEvento][] = array(
                        'id' => intval($media['id']),
                        'nombre' => trabajoLaboratorioTextoUtf8($media['nombre_original']),
                        'mime' => trabajoLaboratorioTextoUtf8($media['mime']),
                        'descripcion' => trabajoLaboratorioTextoUtf8($media['descripcion'])
                    );
                }
            }
            $stmtMedia->close();
        }
    }

    $origenesHistoricos = trabajoLaboratorioMiniHilosOrigenesHistoricos(
        $mysqli,
        $idsTrabajos
    );
    foreach ($limites as $idMensaje => $limite) {
        $idTrabajo = intval($limite['id_trabajoFK']);
        $idEventoLimite = intval($limite['id']);
        $eventos = isset($eventosPorTrabajo[$idTrabajo])
            ? $eventosPorTrabajo[$idTrabajo] : array();
        $nodos = array();
        $indicePorEventoCustodia = array();
        $snapshotVigente = null;
        $estadoVigente = '';
        $ultimoActor = array('nombre' => 'Usuario Telar', 'rol' => '', 'avatar' => '');
        $ultimoLocal = '';
        $fechaLimite = $limite['fecha_servidor'];
        $encontroLimite = false;

        if (isset($origenesHistoricos[$idTrabajo])) {
            $nodos[] = $origenesHistoricos[$idTrabajo];
        }
        foreach ($eventos as $evento) {
            $idEvento = intval($evento['id']);
            $metadata = trabajoLaboratorioDecodificarJson(
                trabajoLaboratorioTextoUtf8($evento['metadata_json']),
                array()
            );
            if (isset($metadata['datos_trabajo'])
                && is_array($metadata['datos_trabajo'])) {
                $snapshotVigente = trabajoLaboratorioUtf8($metadata['datos_trabajo']);
            }
            $estadoEvento = trabajoLaboratorioEstadoEventoRecorrido(
                $evento['tipo_evento'],
                $metadata
            );
            if ($estadoEvento !== '') {
                $estadoVigente = $estadoEvento;
            }
            $actorNombre = trim((string)$evento['actor_nombre_snapshot']) !== ''
                ? $evento['actor_nombre_snapshot'] : $evento['actor_nombre'];
            $actorRol = trim((string)$evento['actor_rol_snapshot']) !== ''
                ? $evento['actor_rol_snapshot'] : $evento['actor_rol'];
            $ultimoActor = array(
                'nombre' => trabajoLaboratorioTextoUtf8(
                    trim((string)$actorNombre) !== '' ? $actorNombre : 'Usuario Telar'
                ),
                'rol' => trabajoLaboratorioTextoUtf8($actorRol),
                'avatar' => trabajoLaboratorioTextoUtf8($evento['actor_avatar'])
            );
            $ultimoLocal = trabajoLaboratorioTextoUtf8(
                trim((string)$evento['local_nombre_snapshot']) !== ''
                    ? $evento['local_nombre_snapshot'] : $evento['local_nombre']
            );
            $mediaEvento = isset($mediaPorEvento[$idEvento])
                ? $mediaPorEvento[$idEvento] : array();
            $tipoEvento = (string)$evento['tipo_evento'];
            $idCustodia = $evento['id_evento_custodiaFK'] === null
                ? 0 : intval($evento['id_evento_custodiaFK']);
            $esRevisionNodo = in_array(
                $tipoEvento,
                array(
                    'datos_trabajo_actualizados',
                    'evidencia_agregada',
                    'nota_agregada',
                    'novedad_custodia'
                ),
                true
            );
            if ($esRevisionNodo) {
                $indiceNodo = $idCustodia > 0
                    && isset($indicePorEventoCustodia[$idCustodia])
                    ? intval($indicePorEventoCustodia[$idCustodia])
                    : count($nodos) - 1;
                if ($indiceNodo >= 0 && isset($nodos[$indiceNodo])) {
                    if ($snapshotVigente !== null) {
                        $nodos[$indiceNodo]['snapshot'] = $snapshotVigente;
                    }
                    if ($tipoEvento === 'datos_trabajo_actualizados') {
                        $nodos[$indiceNodo]['campos_modificados'] =
                            isset($metadata['campos_modificados'])
                            && is_array($metadata['campos_modificados'])
                                ? array_values($metadata['campos_modificados'])
                                : array();
                        $nodos[$indiceNodo]['version'] =
                            intval($evento['version_resultante']);
                    }
                    if (count($mediaEvento) > 0) {
                        $nodos[$indiceNodo]['media'] = array_merge(
                            isset($nodos[$indiceNodo]['media'])
                                ? $nodos[$indiceNodo]['media'] : array(),
                            $mediaEvento
                        );
                    }
                }
            } else {
                $presentacion = trabajoLaboratorioPresentacionEstadoRecorrido(
                    $estadoVigente
                );
                $responsableNombre = trim((string)$evento['responsable_nombre']) !== ''
                    ? $evento['responsable_nombre'] : $actorNombre;
                $responsableRol = trim((string)$evento['responsable_rol']) !== ''
                    ? $evento['responsable_rol'] : $actorRol;
                $titulo = trabajoLaboratorioEtiquetaEventoRecorrido($tipoEvento);
                if (in_array(
                    $tipoEvento,
                    array('instalacion_registrada', 'instalacion_historica_declarada'),
                    true
                )) {
                    $titulo = 'Instalado y entregado';
                }
                $nodo = array(
                    'clave' => 'evento-'.$idEvento,
                    'id_evento' => $idEvento,
                    'tipo_evento' => trabajoLaboratorioTextoUtf8($tipoEvento),
                    'titulo' => trabajoLaboratorioTextoUtf8($titulo),
                    'icono' => trabajoLaboratorioMiniHiloIconoEvento($tipoEvento),
                    'fecha' => $evento['fecha_servidor'],
                    'estado' => $estadoVigente,
                    'estado_nombre' => trabajoLaboratorioTextoUtf8(
                        isset($presentacion['nombre']) ? $presentacion['nombre'] : ''
                    ),
                    'estado_semantico' => isset($presentacion['semantica'])
                        ? (string)$presentacion['semantica'] : 'sin_definir',
                    'actor' => $ultimoActor,
                    'responsable' => array(
                        'nombre' => trabajoLaboratorioTextoUtf8(
                            trim((string)$responsableNombre) !== ''
                                ? $responsableNombre : 'Usuario Telar'
                        ),
                        'rol' => trabajoLaboratorioTextoUtf8($responsableRol),
                        'avatar' => trabajoLaboratorioTextoUtf8(
                            trim((string)$evento['responsable_avatar']) !== ''
                                ? $evento['responsable_avatar'] : $evento['actor_avatar']
                        )
                    ),
                    'local' => $ultimoLocal,
                    'observacion' => trabajoLaboratorioTextoUtf8($evento['observacion']),
                    'snapshot' => $snapshotVigente,
                    'campos_modificados' => isset($metadata['campos_modificados'])
                        && is_array($metadata['campos_modificados'])
                            ? array_values($metadata['campos_modificados'])
                            : array(),
                    'media' => $mediaEvento,
                    'version' => intval($evento['version_resultante']),
                    'terminal' => in_array(
                        $estadoVigente,
                        array('instalado', 'cancelado'),
                        true
                    ),
                    'cierre' => false
                );
                $nodos[] = $nodo;
                $indicePorEventoCustodia[$idEvento] = count($nodos) - 1;
            }
            if ($idEvento === $idEventoLimite) {
                $fechaLimite = $evento['fecha_servidor'];
                $encontroLimite = true;
                break;
            }
        }
        if (!$encontroLimite || count($nodos) === 0) {
            continue;
        }
        if (isset($origenesHistoricos[$idTrabajo])
            && isset($nodos[0])
            && $nodos[0]['snapshot'] === null) {
            $nodos[0]['snapshot'] =
                isset($origenesHistoricos[$idTrabajo]['snapshot'])
                    ? $origenesHistoricos[$idTrabajo]['snapshot'] : null;
        }
        $terminal = in_array($estadoVigente, array('instalado', 'cancelado'), true);
        if ($terminal) {
            $presentacionCierre = trabajoLaboratorioPresentacionEstadoRecorrido(
                $estadoVigente
            );
            $nodos[] = array(
                'clave' => 'cierre-'.$idEventoLimite,
                'id_evento' => null,
                'tipo_evento' => 'hilo_cerrado',
                'titulo' => 'Hilo cerrado',
                'icono' => 'fa-flag-checkered',
                'fecha' => $fechaLimite,
                'estado' => $estadoVigente,
                'estado_nombre' => $estadoVigente === 'cancelado'
                    ? 'Cancelado' : 'Finalizado',
                'estado_semantico' => isset($presentacionCierre['semantica'])
                    ? (string)$presentacionCierre['semantica'] : 'finalizado',
                'actor' => $ultimoActor,
                'responsable' => $ultimoActor,
                'local' => $ultimoLocal,
                'observacion' => $estadoVigente === 'cancelado'
                    ? 'El seguimiento fue cerrado por cancelacion.'
                    : 'Resultado registrado. El tratamiento quedo finalizado.',
                'snapshot' => $snapshotVigente,
                'campos_modificados' => array(),
                'media' => array(),
                'version' => null,
                'terminal' => true,
                'cierre' => true
            );
        }
        $salida[intval($idMensaje)] = array(
            'id_trabajo' => $idTrabajo,
            'codigo_visible' => trabajoLaboratorioTextoUtf8($limite['codigo_visible']),
            'id_evento_limite' => $idEventoLimite,
            'fecha_limite' => $fechaLimite,
            'estado' => $estadoVigente,
            'terminal' => $terminal,
            'nodos' => $nodos
        );
    }
    return $salida;
}

function trabajoLaboratorioMiniHilosOrigenesHistoricos($mysqli, $idsTrabajos)
{
    $origenes = array();
    $ids = array();
    foreach ((array)$idsTrabajos as $idTrabajo) {
        $idTrabajo = intval($idTrabajo);
        if ($idTrabajo > 0) {
            $ids[$idTrabajo] = $idTrabajo;
        }
    }
    $ids = array_values($ids);
    if (count($ids) === 0
        || !trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_historico')) {
        return $origenes;
    }
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $mysqli->prepare(
        'SELECT h.id,h.id_trabajo_laboratorioFK,h.estado_legacy_snapshot,'
        .'h.fecha_creacion_snapshot,h.observacion_snapshot,h.colorimetro_snapshot,'
        .'h.costo_snapshot,h.fecha_retiro_snapshot,h.fecha_entrega_snapshot,'
        .'h.cod_tipo_trabajo_snapshot,h.cod_mecanico_dental_snapshot,'
        .'h.cod_especialista_snapshot,h.cod_local_snapshot,h.cod_cliente_snapshot,'
        .'tt.descripcion AS tipo_trabajo,pp.nombre_persona AS paciente,'
        .'pm.nombre_persona AS mecanico,pd.nombre_persona AS doctor,'
        .'pc.nombre_persona AS creador,uc.tipo AS creador_rol,uc.url AS creador_avatar,'
        .'l.Nombre AS local_nombre,dv.cod_productoFK,p.nombre_producto '
        .'FROM trabajo_laboratorio_historico h '
        .'LEFT JOIN tipo_trabajo_mecanico_dental tt '
        .'ON tt.cod_tipo_trabajo_mecanico_dental=h.cod_tipo_trabajo_snapshot '
        .'LEFT JOIN persona pp ON pp.cod_persona=h.cod_cliente_snapshot '
        .'LEFT JOIN mecanico_dental md '
        .'ON md.cod_mecanico_dental=h.cod_mecanico_dental_snapshot '
        .'LEFT JOIN persona pm ON pm.cod_persona=md.cod_personaFK '
        .'LEFT JOIN persona pd ON pd.cod_persona=h.cod_especialista_snapshot '
        .'LEFT JOIN usuario uc ON uc.cod_usuario=h.cod_usuario_creador_snapshot '
        .'LEFT JOIN persona pc ON pc.cod_persona=h.cod_usuario_creador_snapshot '
        .'LEFT JOIN local l ON l.cod_local=h.cod_local_snapshot '
        .'LEFT JOIN detalle_venta dv ON dv.cod_detalle=h.cod_detalle_ventaFK '
        .'LEFT JOIN producto p ON p.cod_producto=dv.cod_productoFK '
        .'WHERE h.id_trabajo_laboratorioFK IN ('.$marcas.') '
        .'ORDER BY h.id ASC'
    );
    if (!$stmt) {
        return $origenes;
    }
    $valores = $ids;
    trabajoLaboratorioVincularParametros(
        $stmt,
        str_repeat('i', count($ids)),
        $valores
    );
    if (!$stmt->execute()) {
        $stmt->close();
        return $origenes;
    }
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $idTrabajo = intval($fila['id_trabajo_laboratorioFK']);
        if ($idTrabajo <= 0 || isset($origenes[$idTrabajo])) {
            continue;
        }
        $estado = trabajoLaboratorioNormalizarTexto($fila['estado_legacy_snapshot']);
        $presentacion = trabajoLaboratorioPresentacionEstadoRecorrido($estado);
        $origenes[$idTrabajo] = array(
            'clave' => 'original-'.intval($fila['id']),
            'id_evento' => null,
            'tipo_evento' => 'registro_original',
            'titulo' => 'Registro original',
            'icono' => 'fa-box-archive',
            'fecha' => $fila['fecha_creacion_snapshot'],
            'estado' => $estado,
            'estado_nombre' => trabajoLaboratorioTextoUtf8(
                trim((string)$fila['estado_legacy_snapshot']) !== ''
                    ? $fila['estado_legacy_snapshot'] : 'Situacion original'
            ),
            'estado_semantico' => isset($presentacion['semantica'])
                ? (string)$presentacion['semantica'] : 'sin_definir',
            'actor' => array(
                'nombre' => trabajoLaboratorioTextoUtf8(
                    trim((string)$fila['creador']) !== ''
                        ? $fila['creador'] : 'Autor del registro original'
                ),
                'rol' => trabajoLaboratorioTextoUtf8($fila['creador_rol']),
                'avatar' => trabajoLaboratorioTextoUtf8($fila['creador_avatar'])
            ),
            'responsable' => array(
                'nombre' => trabajoLaboratorioTextoUtf8(
                    trim((string)$fila['creador']) !== ''
                        ? $fila['creador'] : 'Autor del registro original'
                ),
                'rol' => trabajoLaboratorioTextoUtf8($fila['creador_rol']),
                'avatar' => trabajoLaboratorioTextoUtf8($fila['creador_avatar'])
            ),
            'local' => trabajoLaboratorioTextoUtf8($fila['local_nombre']),
            'observacion' => trabajoLaboratorioTextoUtf8($fila['observacion_snapshot']),
            'snapshot' => array(
                'cod_tipo_trabajo' => $fila['cod_tipo_trabajo_snapshot'] === null
                    ? null : intval($fila['cod_tipo_trabajo_snapshot']),
                'tipo_trabajo' => trabajoLaboratorioTextoUtf8($fila['tipo_trabajo']),
                'paciente' => trabajoLaboratorioTextoUtf8($fila['paciente']),
                'cod_producto' => trabajoLaboratorioTextoUtf8($fila['cod_productoFK']),
                'producto' => trabajoLaboratorioTextoUtf8($fila['nombre_producto']),
                'colorimetro' => trabajoLaboratorioTextoUtf8($fila['colorimetro_snapshot']),
                'cod_especialista' => $fila['cod_especialista_snapshot'] === null
                    ? null : intval($fila['cod_especialista_snapshot']),
                'doctor' => trabajoLaboratorioTextoUtf8($fila['doctor']),
                'cod_mecanico_dental' => $fila['cod_mecanico_dental_snapshot'] === null
                    ? null : intval($fila['cod_mecanico_dental_snapshot']),
                'mecanico_dental' => trabajoLaboratorioTextoUtf8($fila['mecanico']),
                'fecha_retiro' => $fila['fecha_retiro_snapshot'],
                'fecha_entrega' => $fila['fecha_entrega_snapshot'],
                'costo_estimado' => $fila['costo_snapshot'] === null
                    ? null : intval($fila['costo_snapshot']),
                'estado' => $estado,
                'cod_local' => $fila['cod_local_snapshot'] === null
                    ? null : intval($fila['cod_local_snapshot']),
                'local' => trabajoLaboratorioTextoUtf8($fila['local_nombre']),
                'observacion' => trabajoLaboratorioTextoUtf8(
                    $fila['observacion_snapshot']
                )
            ),
            'campos_modificados' => array(),
            'media' => array(),
            'version' => null,
            'terminal' => false,
            'cierre' => false
        );
    }
    $stmt->close();
    return $origenes;
}

function trabajoLaboratorioMiniHiloIconoEvento($tipoEvento)
{
    $mapa = array(
        'trabajo_iniciado' => 'fa-play',
        'tecnico_asignado' => 'fa-user-gear',
        'registro_historico_convalidado' => 'fa-box-archive',
        'registro_historico_continuado' => 'fa-hand-holding',
        'transferencia_mecanico_iniciada' => 'fa-truck',
        'recepcion_mecanico_confirmada' => 'fa-flask-vial',
        'devolucion_iniciada' => 'fa-truck-fast',
        'devolucion_confirmada' => 'fa-clinic-medical',
        'hilo_tomado' => 'fa-hand-holding',
        'custodia_rectificada' => 'fa-user-shield',
        'ajuste_solicitado' => 'fa-rotate',
        'trabajo_aprobado' => 'fa-circle-check',
        'instalacion_registrada' => 'fa-tooth',
        'instalacion_historica_declarada' => 'fa-tooth',
        'trabajo_cancelado' => 'fa-ban'
    );
    $tipoEvento = (string)$tipoEvento;
    return isset($mapa[$tipoEvento]) ? $mapa[$tipoEvento] : 'fa-circle';
}

function trabajoLaboratorioDuracionCustodiaTexto($segundos)
{
    $segundos = max(0, intval($segundos));
    $dias = intval(floor($segundos / 86400));
    $horas = intval(floor(($segundos % 86400) / 3600));
    $minutos = intval(floor(($segundos % 3600) / 60));
    if ($dias > 0) {
        return $dias.' '.($dias === 1 ? 'dia' : 'dias').($horas > 0 ? ' '.$horas.' h' : '');
    }
    if ($horas > 0) {
        return $horas.' h'.($minutos > 0 ? ' '.$minutos.' min' : '');
    }
    if ($minutos > 0) {
        return $minutos.' min';
    }
    return 'menos de 1 min';
}

function trabajoLaboratorioEtiquetaEventoCustodia($tipoEvento)
{
    $mapa = array(
        'trabajo_iniciado' => 'Trabajo iniciado',
        'recepcion_mecanico_confirmada' => 'Recepcion en laboratorio',
        'devolucion_confirmada' => 'Recepcion en clinica',
        'hilo_tomado' => 'Hilo tomado',
        'custodia_rectificada' => 'Custodia rectificada',
        'instalacion_registrada' => 'Custodia final historica',
        'registro_historico_continuado' => 'Trabajo regularizado',
        'instalacion_historica_declarada' => 'Instalado y entregado'
    );
    $tipoEvento = (string)$tipoEvento;
    return isset($mapa[$tipoEvento]) ? $mapa[$tipoEvento] : 'Custodia registrada';
}

/**
 * Proyecta los periodos de custodia desde los eventos inmutables. El traslado
 * externo no crea custodios: el periodo termina solo cuando otro usuario Telar
 * toma el hilo o cuando el trabajo alcanza un estado terminal.
 */
function trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, $trabajos, $incluirMiniaturas = true)
{
    $filasPorId = array();
    $cadenas = array();
    foreach ((array)$trabajos as $trabajo) {
        if (!is_array($trabajo) || !isset($trabajo['id'])) {
            continue;
        }
        $idTrabajo = intval($trabajo['id']);
        if ($idTrabajo <= 0) {
            continue;
        }
        $filasPorId[$idTrabajo] = $trabajo;
        $cadenas[$idTrabajo] = array();
    }
    $ids = array_keys($filasPorId);
    if (count($ids) === 0 || !trabajoLaboratorioHiloCustodiaDisponible($mysqli)) {
        return $cadenas;
    }

    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $sql = 'SELECT e.id,e.id_trabajoFK,e.id_cicloFK,e.tipo_evento,e.cod_usuario_actorFK,'
        .'e.cod_custodio_anteriorFK,e.cod_custodio_nuevoFK,e.cod_localFK,e.fecha_servidor,'
        .'e.observacion,e.metadata_json,e.version_resultante,e.actor_nombre_snapshot,'
        .'e.actor_rol_snapshot,e.local_nombre_snapshot,pa.nombre_persona AS actor_nombre,'
        .'ua.tipo AS actor_rol,ua.url AS actor_avatar,pcn.nombre_persona AS custodio_nombre,'
        .'ucn.tipo AS custodio_rol,ucn.url AS custodio_avatar,'
        .'pca.nombre_persona AS custodio_anterior_nombre,l.Nombre AS local_nombre,'
        .'(SELECT MIN(me.id) FROM trabajo_laboratorio_media me WHERE me.id_eventoFK=e.id) AS media_id,'
        .'(SELECT COUNT(*) FROM trabajo_laboratorio_media me WHERE me.id_eventoFK=e.id) AS media_cantidad,'
        .'(SELECT me.miniatura_relativa FROM trabajo_laboratorio_media me '
        .'WHERE me.id_eventoFK=e.id ORDER BY me.id ASC LIMIT 1) AS media_miniatura,'
        .'(SELECT me.mime FROM trabajo_laboratorio_media me '
        .'WHERE me.id_eventoFK=e.id ORDER BY me.id ASC LIMIT 1) AS media_mime,'
        .'(SELECT COUNT(*) FROM trabajo_laboratorio_evento nv '
        ."WHERE nv.id_evento_custodiaFK=e.id AND nv.tipo_evento='novedad_custodia') AS novedades_cantidad "
        .'FROM trabajo_laboratorio_evento e '
        .'LEFT JOIN persona pa ON pa.cod_persona=e.cod_usuario_actorFK '
        .'LEFT JOIN usuario ua ON ua.cod_usuario=e.cod_usuario_actorFK '
        .'LEFT JOIN persona pca ON pca.cod_persona=e.cod_custodio_anteriorFK '
        .'LEFT JOIN persona pcn ON pcn.cod_persona=e.cod_custodio_nuevoFK '
        .'LEFT JOIN usuario ucn ON ucn.cod_usuario=e.cod_custodio_nuevoFK '
        .'LEFT JOIN local l ON l.cod_local=e.cod_localFK '
        .'WHERE e.id_trabajoFK IN ('.$marcas.') AND ('
        ."e.tipo_evento IN ('trabajo_iniciado','recepcion_mecanico_confirmada',"
        ."'devolucion_confirmada','hilo_tomado','custodia_rectificada',"
        ."'registro_historico_continuado','instalacion_historica_declarada') "
        ."OR (e.tipo_evento='instalacion_registrada' AND e.cod_custodio_nuevoFK IS NOT NULL "
        ."AND (e.cod_custodio_anteriorFK IS NULL OR e.cod_custodio_anteriorFK<>e.cod_custodio_nuevoFK))) "
        .'ORDER BY e.id_trabajoFK ASC,e.fecha_servidor ASC,e.id ASC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'cadena_custodia_no_disponible',
            'No se pudo preparar el hilo de custodia de los trabajos.'
        );
    }
    $valores = $ids;
    trabajoLaboratorioVincularParametros($stmt, str_repeat('i', count($ids)), $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'cadena_custodia_no_disponible',
            'No se pudo consultar el hilo de custodia de los trabajos.'
        );
    }
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $idTrabajo = intval($fila['id_trabajoFK']);
        if (!isset($cadenas[$idTrabajo])) {
            continue;
        }
        $metadata = trabajoLaboratorioDecodificarJson(
            trabajoLaboratorioTextoUtf8($fila['metadata_json']),
            array()
        );
        $esRectificacion = (string)$fila['tipo_evento'] === 'custodia_rectificada';
        $nombreResponsable = $esRectificacion && !empty($metadata['custodio_nombre_snapshot'])
            ? $metadata['custodio_nombre_snapshot'] : $fila['custodio_nombre'];
        $rolResponsable = $esRectificacion && !empty($metadata['custodio_rol_snapshot'])
            ? $metadata['custodio_rol_snapshot'] : $fila['custodio_rol'];
        $avatarResponsable = $esRectificacion && isset($metadata['custodio_avatar_snapshot'])
            ? $metadata['custodio_avatar_snapshot'] : $fila['custodio_avatar'];
        if (!$esRectificacion
            && intval($fila['cod_custodio_nuevoFK']) === intval($fila['cod_usuario_actorFK'])) {
            $nombreResponsable = trim((string)$fila['actor_nombre_snapshot']) !== ''
                ? $fila['actor_nombre_snapshot']
                : (trim((string)$nombreResponsable) !== '' ? $nombreResponsable : $fila['actor_nombre']);
            $rolResponsable = trim((string)$fila['actor_rol_snapshot']) !== ''
                ? $fila['actor_rol_snapshot']
                : (trim((string)$rolResponsable) !== '' ? $rolResponsable : $fila['actor_rol']);
            $avatarResponsable = trim((string)$avatarResponsable) !== ''
                ? $avatarResponsable : $fila['actor_avatar'];
        }
        $localNodo = $esRectificacion && !empty($metadata['custodio_local_snapshot'])
            ? $metadata['custodio_local_snapshot']
            : (trim((string)$fila['local_nombre_snapshot']) !== ''
                ? $fila['local_nombre_snapshot'] : $fila['local_nombre']);
        $miniaturaUrl = $incluirMiniaturas && trim((string)$fila['media_miniatura']) !== ''
            ? trabajoLaboratorioDataUriMedia($fila['media_miniatura'], $fila['media_mime'])
            : null;
        $cadenas[$idTrabajo][] = array(
            'id' => intval($fila['id']),
            'id_evento' => intval($fila['id']),
            'id_trabajo' => $idTrabajo,
            'id_ciclo' => intval($fila['id_cicloFK']),
            'tipo_evento' => trabajoLaboratorioTextoUtf8($fila['tipo_evento']),
            'titulo' => trabajoLaboratorioEtiquetaEventoCustodia($fila['tipo_evento']),
            'fecha_inicio' => $fila['fecha_servidor'],
            'fecha_fin' => null,
            'duracion_segundos' => 0,
            'duracion_texto' => 'menos de 1 min',
            'responsable' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_custodio_nuevoFK'],
                $nombreResponsable,
                $rolResponsable,
                $avatarResponsable
            ),
            'custodio_anterior' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_custodio_anteriorFK'],
                $fila['custodio_anterior_nombre']
            ),
            'custodio_nuevo' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_custodio_nuevoFK'],
                $nombreResponsable,
                $rolResponsable,
                $avatarResponsable
            ),
            'actor' => trabajoLaboratorioPersonaRecorrido(
                $fila['cod_usuario_actorFK'],
                trim((string)$fila['actor_nombre_snapshot']) !== ''
                    ? $fila['actor_nombre_snapshot'] : $fila['actor_nombre'],
                trim((string)$fila['actor_rol_snapshot']) !== ''
                    ? $fila['actor_rol_snapshot'] : $fila['actor_rol'],
                $fila['actor_avatar']
            ),
            'cod_local' => $fila['cod_localFK'] === null ? null : intval($fila['cod_localFK']),
            'local' => trabajoLaboratorioTextoUtf8($localNodo),
            'condicion_recepcion' => isset($metadata['condicion_recepcion'])
                ? trabajoLaboratorioTextoUtf8($metadata['condicion_recepcion']) : null,
            'observacion' => trabajoLaboratorioTextoUtf8($fila['observacion']),
            'sin_foto' => !empty($metadata['sin_foto']) ? true : false,
            'motivo_sin_foto' => isset($metadata['motivo_sin_foto'])
                ? trabajoLaboratorioTextoUtf8($metadata['motivo_sin_foto']) : null,
            'media_id' => $fila['media_id'] === null ? null : intval($fila['media_id']),
            'miniatura_media_id' => $fila['media_id'] === null ? null : intval($fila['media_id']),
            'miniatura_url' => $miniaturaUrl,
            'evidencias_cantidad' => intval($fila['media_cantidad']),
            'novedades_cantidad' => intval($fila['novedades_cantidad']),
            'datos_trabajo' => isset($metadata['datos_trabajo']) && is_array($metadata['datos_trabajo'])
                ? $metadata['datos_trabajo'] : null,
            'datos_trabajo_anterior' => isset($metadata['datos_trabajo_anterior'])
                && is_array($metadata['datos_trabajo_anterior'])
                ? $metadata['datos_trabajo_anterior'] : null,
            'campos_modificados' => isset($metadata['campos_modificados']) && is_array($metadata['campos_modificados'])
                ? array_values($metadata['campos_modificados']) : array(),
            'eventos_version' => array(intval($fila['id'])),
            'ediciones_cantidad' => 0,
            'actual' => false,
            'cerrado' => false,
            'terminal' => false,
            'en_transporte' => false,
            'registro_historico' => trim((string)$fila['actor_nombre_snapshot']) === '',
            'version_resultante' => intval($fila['version_resultante'])
        );
    }
    $stmt->close();

    $revisionesPorCustodia = array();
    $sqlRevision = 'SELECT id,id_evento_custodiaFK,tipo_evento,metadata_json FROM trabajo_laboratorio_evento '
        .'WHERE id_trabajoFK IN ('.$marcas.') AND id_evento_custodiaFK IS NOT NULL '
        .'ORDER BY fecha_servidor ASC,id ASC';
    $stmtRevision = $mysqli->prepare($sqlRevision);
    if ($stmtRevision) {
        $valoresRevision = $ids;
        trabajoLaboratorioVincularParametros(
            $stmtRevision,
            str_repeat('i', count($ids)),
            $valoresRevision
        );
        if ($stmtRevision->execute()) {
            $resultadoRevision = $stmtRevision->get_result();
            while ($filaRevision = $resultadoRevision->fetch_assoc()) {
                $idCustodiaRevision = intval($filaRevision['id_evento_custodiaFK']);
                if (!isset($revisionesPorCustodia[$idCustodiaRevision])) {
                    $revisionesPorCustodia[$idCustodiaRevision] = array(
                        'ids' => array(),
                        'ediciones' => array(),
                        'metadata' => array()
                    );
                }
                $revisionesPorCustodia[$idCustodiaRevision]['ids'][] = intval($filaRevision['id']);
                if ((string)$filaRevision['tipo_evento'] === 'datos_trabajo_actualizados') {
                    $revisionesPorCustodia[$idCustodiaRevision]['ediciones'][] = intval($filaRevision['id']);
                    $revisionesPorCustodia[$idCustodiaRevision]['metadata'] =
                        trabajoLaboratorioDecodificarJson(
                            trabajoLaboratorioTextoUtf8($filaRevision['metadata_json']),
                            array()
                        );
                }
            }
        }
        $stmtRevision->close();
    }

    $ahora = time();
    foreach ($cadenas as $idTrabajo => &$nodos) {
        if (count($nodos) === 0) {
            continue;
        }
        $trabajo = $filasPorId[$idTrabajo];
        $estado = isset($trabajo['estado_derivado']) ? (string)$trabajo['estado_derivado'] : '';
        $esTerminal = in_array($estado, array('instalado', 'cancelado'), true);
        $fechaTerminal = $estado === 'instalado'
            ? (isset($trabajo['fecha_instalado']) ? $trabajo['fecha_instalado'] : null)
            : ($estado === 'cancelado'
                ? (isset($trabajo['fecha_cancelado']) ? $trabajo['fecha_cancelado'] : null)
                : null);
        $idActual = isset($trabajo['id_evento_custodia_actualFK'])
            ? intval($trabajo['id_evento_custodia_actualFK']) : 0;
        $indiceActual = count($nodos) - 1;
        foreach ($nodos as $indice => &$nodo) {
            $idNodo = intval($nodo['id_evento']);
            if (isset($revisionesPorCustodia[$idNodo])) {
                $revisionNodo = $revisionesPorCustodia[$idNodo];
                $metadataRevision = is_array($revisionNodo['metadata'])
                    ? $revisionNodo['metadata'] : array();
                if (!empty($metadataRevision['datos_trabajo'])
                    && is_array($metadataRevision['datos_trabajo'])) {
                    $nodo['datos_trabajo'] = $metadataRevision['datos_trabajo'];
                }
                if (isset($metadataRevision['campos_modificados'])
                    && is_array($metadataRevision['campos_modificados'])) {
                    $nodo['campos_modificados'] = array_values($metadataRevision['campos_modificados']);
                }
                $nodo['eventos_version'] = array_merge(
                    $nodo['eventos_version'],
                    $revisionNodo['ids']
                );
                $nodo['ediciones_cantidad'] = count($revisionNodo['ediciones']);
            }
            $nodo['version_nodo'] = $indice + 1;
            $fechaFin = isset($nodos[$indice + 1]) ? $nodos[$indice + 1]['fecha_inicio'] : null;
            if ($fechaFin === null && $esTerminal) {
                $fechaFin = $fechaTerminal;
            }
            $inicioTs = trabajoLaboratorioTimestampSistema($nodo['fecha_inicio']);
            $finTs = $fechaFin === null ? $ahora : trabajoLaboratorioTimestampSistema($fechaFin);
            if ($inicioTs === false) {
                $inicioTs = $ahora;
            }
            if ($finTs === false) {
                $finTs = $ahora;
            }
            $duracion = max(0, intval($finTs - $inicioTs));
            $nodo['fecha_fin'] = $fechaFin;
            $nodo['duracion_segundos'] = $duracion;
            $nodo['duracion_texto'] = trabajoLaboratorioDuracionCustodiaTexto($duracion);
            $nodo['cerrado'] = $fechaFin !== null;
            $nodo['terminal'] = $esTerminal && $indice === count($nodos) - 1;
            $nodo['estado_terminal'] = $nodo['terminal'] ? $estado : null;
            $nodo['motivo_cierre'] = !$nodo['terminal'] ? null
                : ($estado === 'cancelado' ? 'cancelacion' : 'instalacion');
            $nodo['en_transporte'] = !$esTerminal && $indice === count($nodos) - 1
                && in_array($estado, array('en_transferencia_mecanico', 'en_transferencia_clinica'), true);
            if ($idActual > 0 && intval($nodo['id_evento']) === $idActual) {
                $indiceActual = $indice;
            }
        }
        unset($nodo);
        for ($indice = 0; $indice < count($nodos) - 1; $indice++) {
            if (empty($nodos[$indice]['datos_trabajo'])
                && !empty($nodos[$indice + 1]['datos_trabajo_anterior'])) {
                $nodos[$indice]['datos_trabajo'] = $nodos[$indice + 1]['datos_trabajo_anterior'];
            }
        }
        foreach ($nodos as &$nodoSalida) {
            unset($nodoSalida['datos_trabajo_anterior']);
        }
        unset($nodoSalida);
        $nodos[$indiceActual]['actual'] = !$esTerminal;
        if (empty($nodos[$indiceActual]['datos_trabajo'])) {
            $nodos[$indiceActual]['datos_trabajo'] = trabajoLaboratorioSnapshotDatosTrabajo($trabajo);
        }
    }
    unset($nodos);
    return $cadenas;
}

function trabajoLaboratorioResumenCustodiaActual($cadena, $trabajo = array())
{
    $cadena = array_values(is_array($cadena) ? $cadena : array());
    if (count($cadena) === 0) {
        return array(
            'cod_usuario' => isset($trabajo['cod_custodio_actualFK'])
                ? intval($trabajo['cod_custodio_actualFK']) : null,
            'nombre' => isset($trabajo['nombre_custodio'])
                ? trabajoLaboratorioTextoUtf8($trabajo['nombre_custodio']) : null,
            'rol' => isset($trabajo['custodio_rol'])
                ? trabajoLaboratorioTextoUtf8($trabajo['custodio_rol']) : null,
            'avatar' => isset($trabajo['custodio_avatar'])
                ? trabajoLaboratorioTextoUtf8($trabajo['custodio_avatar']) : null,
            'id_evento_custodia' => null,
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'duracion_segundos' => null,
            'duracion_texto' => null,
            'vigente' => !in_array(
                isset($trabajo['estado_derivado']) ? (string)$trabajo['estado_derivado'] : '',
                array('instalado', 'cancelado'),
                true
            )
        );
    }
    $nodo = $cadena[count($cadena) - 1];
    foreach ($cadena as $candidato) {
        if (!empty($candidato['actual'])) {
            $nodo = $candidato;
            break;
        }
    }
    $responsable = isset($nodo['responsable']) && is_array($nodo['responsable'])
        ? $nodo['responsable'] : array();
    return array(
        'cod_usuario' => isset($responsable['cod_usuario'])
            ? intval($responsable['cod_usuario']) : null,
        'nombre' => isset($responsable['nombre'])
            ? trabajoLaboratorioTextoUtf8($responsable['nombre']) : null,
        'rol' => isset($responsable['rol'])
            ? trabajoLaboratorioTextoUtf8($responsable['rol']) : null,
        'avatar' => isset($responsable['avatar'])
            ? trabajoLaboratorioTextoUtf8($responsable['avatar']) : null,
        'id_evento_custodia' => isset($nodo['id_evento']) ? intval($nodo['id_evento']) : null,
        'fecha_inicio' => isset($nodo['fecha_inicio']) ? $nodo['fecha_inicio'] : null,
        'fecha_fin' => isset($nodo['fecha_fin']) ? $nodo['fecha_fin'] : null,
        'duracion_segundos' => isset($nodo['duracion_segundos'])
            ? intval($nodo['duracion_segundos']) : null,
        'duracion_texto' => isset($nodo['duracion_texto'])
            ? trabajoLaboratorioTextoUtf8($nodo['duracion_texto']) : null,
        'vigente' => !empty($nodo['actual'])
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
    $tecnicoPendiente = isset($trabajo['estado_derivado'])
        && (string)$trabajo['estado_derivado'] === 'pendiente_tecnico';
    $salida = array(
        'id' => intval($trabajo['id']),
        'codigo_visible' => trabajoLaboratorioTextoUtf8($trabajo['codigo_visible']),
        'codigo_origen' => !empty($trabajo['codigo_origen'])
            ? trabajoLaboratorioTextoUtf8($trabajo['codigo_origen']) : 'LEG-'.intval($trabajo['id']),
        'unidad_origen' => isset($trabajo['unidad_origen']) ? intval($trabajo['unidad_origen']) : 1,
        'cantidad_unidades_origen' => isset($trabajo['cantidad_unidades_origen'])
            ? intval($trabajo['cantidad_unidades_origen']) : 1,
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
        'tecnico' => isset($trabajo['nombre_tecnico']) && trim((string)$trabajo['nombre_tecnico']) !== ''
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_tecnico'])
            : ($tecnicoPendiente ? 'Tecnico pendiente' : null),
        'tecnico_rol' => isset($trabajo['tecnico_rol']) && trim((string)$trabajo['tecnico_rol']) !== ''
            ? trabajoLaboratorioTextoUtf8($trabajo['tecnico_rol'])
            : ($tecnicoPendiente ? 'Asignacion pendiente' : null),
        'tecnico_avatar' => isset($trabajo['tecnico_avatar']) ? trabajoLaboratorioTextoUtf8($trabajo['tecnico_avatar']) : null,
        'cod_custodio_actual' => intval($trabajo['cod_custodio_actualFK']),
        'custodio_actual' => isset($trabajo['nombre_custodio']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_custodio']) : null,
        'custodia_actual' => array(
            'cod_usuario' => intval($trabajo['cod_custodio_actualFK']),
            'nombre' => isset($trabajo['nombre_custodio'])
                ? trabajoLaboratorioTextoUtf8($trabajo['nombre_custodio']) : null,
            'rol' => isset($trabajo['custodio_rol'])
                ? trabajoLaboratorioTextoUtf8($trabajo['custodio_rol']) : null,
            'avatar' => isset($trabajo['custodio_avatar'])
                ? trabajoLaboratorioTextoUtf8($trabajo['custodio_avatar']) : null,
            'id_evento_custodia' => isset($trabajo['id_evento_custodia_actualFK'])
                ? intval($trabajo['id_evento_custodia_actualFK']) : null,
            'fecha_inicio' => isset($trabajo['fecha_custodio_actual'])
                ? $trabajo['fecha_custodio_actual'] : null,
            'duracion_segundos' => null,
            'duracion_texto' => null
        ),
        'cod_especialista' => isset($trabajo['cod_especialistaFK'])
            ? intval($trabajo['cod_especialistaFK']) : intval($trabajo['cod_usuarioFK_create']),
        'doctor' => isset($trabajo['nombre_doctor']) ? trabajoLaboratorioTextoUtf8($trabajo['nombre_doctor']) : null,
        'doctor_rol' => isset($trabajo['doctor_rol']) ? trabajoLaboratorioTextoUtf8($trabajo['doctor_rol']) : null,
        'doctor_avatar' => isset($trabajo['doctor_avatar']) ? trabajoLaboratorioTextoUtf8($trabajo['doctor_avatar']) : null,
        'cod_iniciador' => isset($trabajo['cod_usuarioFK_create'])
            ? intval($trabajo['cod_usuarioFK_create']) : null,
        'iniciador' => isset($trabajo['nombre_iniciador'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_iniciador']) : null,
        'iniciador_rol' => isset($trabajo['iniciador_rol'])
            ? trabajoLaboratorioTextoUtf8($trabajo['iniciador_rol']) : null,
        'iniciador_avatar' => isset($trabajo['iniciador_avatar'])
            ? trabajoLaboratorioTextoUtf8($trabajo['iniciador_avatar']) : null,
        'estado_derivado' => $trabajo['estado_derivado'],
        'ciclo_actual' => intval($trabajo['ciclo_actual']),
        'fecha_objetivo' => $trabajo['fecha_objetivo'],
        'tiempo_restante_segundos' => $indicadores['tiempo_restante_segundos'],
        'sla_vencido' => $indicadores['sla_vencido'],
        'fecha_retiro' => $trabajo['fecha_retiro'],
        'fecha_entrega' => $trabajo['fecha_entrega'],
        'fecha_creacion' => $trabajo['fecha_creacion'],
        'fecha_actualizacion' => $trabajo['fecha_actualizacion'],
        'fecha_completado' => isset($trabajo['fecha_completado']) ? $trabajo['fecha_completado'] : null,
        'fecha_instalado' => isset($trabajo['fecha_instalado']) ? $trabajo['fecha_instalado'] : null,
        'fecha_cancelado' => isset($trabajo['fecha_cancelado']) ? $trabajo['fecha_cancelado'] : null,
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
        if (trabajoLaboratorioUsuarioPuedeGestionarCosto($mysqli, $codUsuario)) {
            $salida['costo_estimado'] = $trabajo['costo_estimado'] === null
                ? null : intval($trabajo['costo_estimado']);
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
    $novedades = array();
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
            /* Los eventos creados antes de incorporar metadata_json pueden
               conservar NULL. El decodificador comun evita que PHP 8 emita
               una advertencia capaz de contaminar la respuesta JSON. */
            $metadata = trabajoLaboratorioDecodificarJson(
                trabajoLaboratorioTextoUtf8($fila['metadata_json']),
                array()
            );
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
                    'nombre' => trabajoLaboratorioTextoUtf8(
                        isset($fila['actor_nombre_snapshot'])
                            && trim((string)$fila['actor_nombre_snapshot']) !== ''
                            ? $fila['actor_nombre_snapshot'] : $fila['actor']
                    ),
                    'rol' => trabajoLaboratorioTextoUtf8(
                        isset($fila['actor_rol_snapshot'])
                            && trim((string)$fila['actor_rol_snapshot']) !== ''
                            ? $fila['actor_rol_snapshot'] : $fila['actor_rol']
                    ),
                    'avatar' => trabajoLaboratorioTextoUtf8($fila['actor_avatar'])
                ),
                'remitente' => trabajoLaboratorioTextoUtf8($fila['remitente']),
                'destinatario' => trabajoLaboratorioTextoUtf8($fila['destinatario']),
                'fecha' => $fila['fecha_servidor'],
                'fecha_hora' => $fila['fecha_servidor'],
                'local' => trabajoLaboratorioTextoUtf8(
                    isset($fila['local_nombre_snapshot'])
                        && trim((string)$fila['local_nombre_snapshot']) !== ''
                        ? $fila['local_nombre_snapshot'] : $fila['nombre_local']
                ),
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
            if ($fila['tipo_evento'] === 'novedad_custodia') {
                $novedades[] = array(
                    'id' => intval($fila['id']),
                    'id_evento_custodia' => isset($metadata['id_evento_custodia'])
                        ? intval($metadata['id_evento_custodia']) : null,
                    'tipo_novedad' => isset($metadata['tipo_novedad'])
                        ? trabajoLaboratorioTextoUtf8($metadata['tipo_novedad'])
                        : 'observacion_general',
                    'descripcion' => trabajoLaboratorioTextoUtf8($fila['observacion']),
                    'actor' => $evento['actor'],
                    'fecha' => $fila['fecha_servidor'],
                    'media_id' => $fila['id_media_evento'] === null
                        ? null : intval($fila['id_media_evento']),
                    'ciclo_etiqueta' => $cicloEtiqueta
                );
                continue;
            }
            if (in_array($fila['tipo_evento'], array('hilo_tomado', 'custodia_rectificada'), true)) {
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
    $origenesHistoricos = function_exists('trabajoLaboratorioHistoricoOriginalesPorTrabajos')
        ? trabajoLaboratorioHistoricoOriginalesPorTrabajos($mysqli, array($trabajo))
        : array();
    $trabajoFormateado['registro_historico_original'] = isset($origenesHistoricos[$idTrabajo])
        ? $origenesHistoricos[$idTrabajo] : null;
    $cadenas = trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, array($trabajo));
    $cadenaCustodia = isset($cadenas[$idTrabajo]) ? $cadenas[$idTrabajo] : array();
    $trabajoFormateado['cadena_custodia'] = $cadenaCustodia;
    $trabajoFormateado['hilo_custodia'] = $cadenaCustodia;
    $trabajoFormateado['custodia_actual'] = trabajoLaboratorioResumenCustodiaActual(
        $cadenaCustodia,
        $trabajo
    );
    if (!empty($media)) {
        $trabajoFormateado['miniatura_media_id'] = intval($media[0]['id']);
        $trabajoFormateado['miniatura_url'] = $media[0]['miniatura_url'];
        $trabajoFormateado['miniatura_fallback_original'] = !empty($media[0]['miniatura_fallback_original']);
    }
    return array(
        'trabajo' => $trabajoFormateado,
        'ciclos' => $ciclos,
        'eventos' => $eventos,
        'recorrido_operativo' => $eventos,
        'notas' => $notas,
        'novedades' => $novedades,
        'cadena_custodia' => $cadenaCustodia,
        'hilo_custodia' => $cadenaCustodia,
        'custodia_actual' => $trabajoFormateado['custodia_actual'],
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
    return trabajoLaboratorioTienePermiso(
        $mysqli,
        intval($codUsuario),
        'VERTRABAJOSLABORATORIO'
    ) || trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario))
        ? '1=1' : '0=1';
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
    if (!trabajoLaboratorioUsuario($mysqli, intval($codUsuario))) {
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
    $tipos = '';
    $valores = array();
    $condiciones = array(trabajoLaboratorioCondicionAccesoListado($mysqli, $codUsuario, $tipos, $valores));
    if (trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_consolidacion')) {
        $condiciones[] =
            'NOT EXISTS (SELECT 1 FROM trabajo_laboratorio_consolidacion tlcon '
            .'WHERE tlcon.id_trabajo_consolidadoFK=tl.id)';
    }
    if ($estado !== '') {
        $condiciones[] = 'tl.estado_derivado=?';
        $tipos .= 's';
        $valores[] = $estado;
    }
    $estadosValidos = array(
        'pendiente_tecnico', 'pendiente_entrega_mecanico', 'en_transferencia_mecanico', 'en_laboratorio',
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
        'pendientes_entrega' => array('pendiente_tecnico', 'pendiente_entrega_mecanico', 'ajuste_solicitado'),
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
        $usuarioVistaMecanico = trabajoLaboratorioUsuario($mysqli, $codUsuario);
        $localVistaMecanico = $usuarioVistaMecanico
            ? intval($usuarioVistaMecanico['cod_localFK']) : 0;
        $incluyeDestinoMecanico = $localVistaMecanico > 0
            && trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'RECIBIRTRABAJOLABORATORIO')
            && trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_transferencia');
        $destinoMecanicoSql = 'EXISTS (SELECT 1 FROM trabajo_laboratorio_transferencia tlm '
            .'WHERE tlm.id=tl.id_transferencia_pendienteFK AND tlm.id_trabajoFK=tl.id '
            .'AND tlm.cod_local_destinoFK=?)';
        $condicionVistaMecanico = '(tl.cod_tecnico_usuarioFK=? OR tl.cod_custodio_actualFK=?';
        $tipos .= 'ii';
        $valores[] = intval($codUsuario);
        $valores[] = intval($codUsuario);
        if ($incluyeDestinoMecanico) {
            $condicionVistaMecanico .= ' OR '.$destinoMecanicoSql;
            $tipos .= 'i';
            $valores[] = $localVistaMecanico;
        }
        $condiciones[] = $condicionVistaMecanico.')';
        if ($bandeja === 'por_recibir') {
            $condicionPorRecibir = "tl.estado_derivado='en_transferencia_mecanico' "
                .'AND (tl.cod_tecnico_usuarioFK=?';
            $tipos .= 'i';
            $valores[] = intval($codUsuario);
            if ($incluyeDestinoMecanico) {
                $condicionPorRecibir .= ' OR '.$destinoMecanicoSql;
                $tipos .= 'i';
                $valores[] = $localVistaMecanico;
            }
            $condiciones[] = $condicionPorRecibir.')';
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
    $codDetalleFiltro = trabajoLaboratorioEntero(isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta'] : 0);
    if ($codDetalleFiltro > 0) {
        $detalleFiltro = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalleFiltro, false);
        if (!$detalleFiltro || !trabajoLaboratorioUsuarioPuedeLocal($mysqli, $codUsuario, intval($detalleFiltro['cod_local']))) {
            trabajoLaboratorioLanzar('local_no_autorizado', 'El usuario no puede consultar el detalle solicitado.');
        }
        $condiciones[] = 'tl.cod_detalle_ventaFK=?';
        $tipos .= 'i';
        $valores[] = $codDetalleFiltro;
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
        .'LEFT JOIN usuario utec ON utec.cod_usuario=tl.cod_tecnico_usuarioFK '
        .'LEFT JOIN persona pt ON pt.cod_persona=tl.cod_tecnico_usuarioFK '
        .'LEFT JOIN usuario ucu ON ucu.cod_usuario=tl.cod_custodio_actualFK '
        .'LEFT JOIN persona pcu ON pcu.cod_persona=tl.cod_custodio_actualFK '
        .'LEFT JOIN usuario udoc ON udoc.cod_usuario=COALESCE(tl.cod_especialistaFK,tl.cod_usuarioFK_create) '
        .'LEFT JOIN persona pdoc ON pdoc.cod_persona=COALESCE(tl.cod_especialistaFK,tl.cod_usuarioFK_create) '
        .'LEFT JOIN usuario uini ON uini.cod_usuario=tl.cod_usuarioFK_create '
        .'LEFT JOIN persona pini ON pini.cod_persona=tl.cod_usuarioFK_create '
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
        .'pt.nombre_persona AS nombre_tecnico,utec.tipo AS tecnico_rol,utec.url AS tecnico_avatar,'
        .'pcu.nombre_persona AS nombre_custodio,ucu.tipo AS custodio_rol,ucu.url AS custodio_avatar,'
        .'pdoc.nombre_persona AS nombre_doctor,'
        .'udoc.tipo AS doctor_rol,udoc.url AS doctor_avatar,'
        .'pini.nombre_persona AS nombre_iniciador,'
        .'uini.tipo AS iniciador_rol,uini.url AS iniciador_avatar,tt.descripcion AS tipo_trabajo,'
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
    $filas = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    $stmt->close();
    $recorridos = trabajoLaboratorioRecorridosPorTrabajos($mysqli, $filas);
    $cadenasCustodia = trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, $filas);
    $origenesHistoricos = function_exists('trabajoLaboratorioHistoricoOriginalesPorTrabajos')
        ? trabajoLaboratorioHistoricoOriginalesPorTrabajos($mysqli, $filas)
        : array();
    $items = array();
    foreach ($filas as $fila) {
        $item = trabajoLaboratorioFormatearTrabajo($mysqli, $codUsuario, $fila, false);
        $idTrabajo = intval($fila['id']);
        $item['recorrido'] = isset($recorridos[$idTrabajo]) ? $recorridos[$idTrabajo] : array();
        $item['recorrido_operativo'] = $item['recorrido'];
        $item['cadena_custodia'] = isset($cadenasCustodia[$idTrabajo])
            ? $cadenasCustodia[$idTrabajo] : array();
        $item['hilo_custodia'] = $item['cadena_custodia'];
        $item['custodia_actual'] = trabajoLaboratorioResumenCustodiaActual(
            $item['cadena_custodia'],
            $fila
        );
        $item['registro_historico_original'] = isset($origenesHistoricos[$idTrabajo])
            ? $origenesHistoricos[$idTrabajo] : null;
        $items[] = $item;
    }
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
    if (trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_consolidacion')) {
        $condicion .=
            ' AND NOT EXISTS (SELECT 1 FROM trabajo_laboratorio_consolidacion tlcon '
            .'WHERE tlcon.id_trabajo_consolidadoFK=tl.id)';
    }
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
        ."SUM(estado_derivado IN ('pendiente_tecnico','pendiente_entrega_mecanico','ajuste_solicitado')) AS pendientes_entrega,"
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
    $trabajoFormateado = trabajoLaboratorioFormatearTrabajo($mysqli, $codUsuario, $trabajo, true);
    $cadenas = trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, array($trabajo));
    $cadenaCustodia = isset($cadenas[intval($idTrabajo)])
        ? $cadenas[intval($idTrabajo)] : array();
    $trabajoFormateado['cadena_custodia'] = $cadenaCustodia;
    $trabajoFormateado['hilo_custodia'] = $cadenaCustodia;
    $trabajoFormateado['custodia_actual'] = trabajoLaboratorioResumenCustodiaActual(
        $cadenaCustodia,
        $trabajo
    );
    $codDetalle = intval($trabajo['cod_detalle_ventaFK']);
    $progresoClinico = trabajoLaboratorioProgresoClinicoDetalle($mysqli, $codDetalle);
    return trabajoLaboratorioRespuesta(
        true,
        $codigo,
        $mensaje,
        array(
            'id' => intval($trabajo['id']),
            'id_trabajo' => intval($trabajo['id']),
            'cod_trabajo_laboratorio' => intval($trabajo['id']),
            'cod_detalle_venta' => $codDetalle,
            'progreso_clinico' => $progresoClinico,
            'trabajo' => $trabajoFormateado,
            'cadena_custodia' => $cadenaCustodia,
            'hilo_custodia' => $cadenaCustodia,
            'custodia_actual' => $trabajoFormateado['custodia_actual'],
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

function trabajoLaboratorioSnapshotDatosTrabajo($trabajo)
{
    $trabajo = is_array($trabajo) ? $trabajo : array();
    return array(
        'cod_tipo_trabajo' => isset($trabajo['cod_tipo_trabajoFK']) && $trabajo['cod_tipo_trabajoFK'] !== null
            ? intval($trabajo['cod_tipo_trabajoFK']) : null,
        'tipo_trabajo' => isset($trabajo['tipo_trabajo'])
            ? trabajoLaboratorioTextoUtf8($trabajo['tipo_trabajo']) : null,
        'cod_cliente' => isset($trabajo['cod_clienteFK']) ? intval($trabajo['cod_clienteFK']) : null,
        'paciente' => isset($trabajo['nombre_paciente'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_paciente']) : null,
        'cod_producto' => isset($trabajo['cod_productoFK'])
            ? trabajoLaboratorioTextoUtf8($trabajo['cod_productoFK']) : null,
        'producto' => isset($trabajo['nombre_producto'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_producto']) : null,
        'colorimetro' => isset($trabajo['colorimetro'])
            ? trabajoLaboratorioTextoUtf8($trabajo['colorimetro']) : null,
        'cod_especialista' => isset($trabajo['cod_especialistaFK']) && $trabajo['cod_especialistaFK'] !== null
            ? intval($trabajo['cod_especialistaFK']) : null,
        'doctor' => isset($trabajo['nombre_doctor'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_doctor']) : null,
        'cod_iniciador' => isset($trabajo['cod_usuarioFK_create'])
            ? intval($trabajo['cod_usuarioFK_create']) : null,
        'iniciador' => isset($trabajo['nombre_iniciador'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_iniciador']) : null,
        'iniciador_rol' => isset($trabajo['iniciador_rol'])
            ? trabajoLaboratorioTextoUtf8($trabajo['iniciador_rol']) : null,
        'iniciador_avatar' => isset($trabajo['iniciador_avatar'])
            ? trabajoLaboratorioTextoUtf8($trabajo['iniciador_avatar']) : null,
        'cod_mecanico_dental' => isset($trabajo['cod_mecanico_dentalFK']) && $trabajo['cod_mecanico_dentalFK'] !== null
            ? intval($trabajo['cod_mecanico_dentalFK']) : null,
        'cod_tecnico_usuario' => isset($trabajo['cod_tecnico_usuarioFK']) && $trabajo['cod_tecnico_usuarioFK'] !== null
            ? intval($trabajo['cod_tecnico_usuarioFK']) : null,
        'mecanico_dental' => isset($trabajo['nombre_tecnico'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_tecnico']) : null,
        'fecha_retiro' => isset($trabajo['fecha_retiro']) && trim((string)$trabajo['fecha_retiro']) !== ''
            ? $trabajo['fecha_retiro'] : null,
        'fecha_entrega' => isset($trabajo['fecha_entrega']) && trim((string)$trabajo['fecha_entrega']) !== ''
            ? $trabajo['fecha_entrega'] : null,
        'costo_estimado' => isset($trabajo['costo_estimado']) && $trabajo['costo_estimado'] !== null
            ? intval($trabajo['costo_estimado']) : null,
        'estado' => isset($trabajo['estado_derivado']) ? (string)$trabajo['estado_derivado'] : null,
        'cod_local' => isset($trabajo['cod_localFK']) ? intval($trabajo['cod_localFK']) : null,
        'local' => isset($trabajo['nombre_local'])
            ? trabajoLaboratorioTextoUtf8($trabajo['nombre_local']) : null,
        'observacion' => isset($trabajo['instrucciones'])
            ? trabajoLaboratorioTextoUtf8($trabajo['instrucciones']) : null,
        'version_registro' => isset($trabajo['version']) ? intval($trabajo['version']) : null
    );
}

function trabajoLaboratorioCamposModificadosSnapshot($anterior, $nuevo)
{
    $anterior = is_array($anterior) ? $anterior : array();
    $nuevo = is_array($nuevo) ? $nuevo : array();
    $claves = array(
        'cod_tipo_trabajo', 'colorimetro', 'cod_especialista', 'cod_mecanico_dental',
        'cod_tecnico_usuario', 'fecha_retiro', 'fecha_entrega', 'costo_estimado',
        'cod_local', 'observacion'
    );
    $modificados = array();
    foreach ($claves as $clave) {
        $valorAnterior = array_key_exists($clave, $anterior) ? $anterior[$clave] : null;
        $valorNuevo = array_key_exists($clave, $nuevo) ? $nuevo[$clave] : null;
        if ((string)$valorAnterior !== (string)$valorNuevo) {
            $modificados[] = $clave;
        }
    }
    return $modificados;
}

function trabajoLaboratorioFechaVersion($valor, $campo)
{
    $valor = trim((string)$valor);
    if ($valor === '') {
        return null;
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/', $valor, $partes) !== 1
        || !checkdate(intval($partes[2]), intval($partes[3]), intval($partes[1]))) {
        trabajoLaboratorioLanzar('fecha_trabajo_invalida', 'La '.$campo.' no tiene un formato valido.');
    }
    $hora = isset($partes[4]) ? intval($partes[4]) : 0;
    $minuto = isset($partes[5]) ? intval($partes[5]) : 0;
    $segundo = isset($partes[6]) ? intval($partes[6]) : 0;
    if ($hora > 23 || $minuto > 59 || $segundo > 59) {
        trabajoLaboratorioLanzar('fecha_trabajo_invalida', 'La '.$campo.' no tiene una hora valida.');
    }
    return sprintf(
        '%04d-%02d-%02d %02d:%02d:%02d',
        intval($partes[1]), intval($partes[2]), intval($partes[3]), $hora, $minuto, $segundo
    );
}

function trabajoLaboratorioDatosVersionEntrada($mysqli, $codUsuario, $trabajo, $entrada)
{
    $datos = isset($entrada['datos_trabajo'])
        ? trabajoLaboratorioDecodificarJson($entrada['datos_trabajo'], array()) : array();
    if (!is_array($datos)) {
        $datos = array();
    }
    $valor = function ($clave, $actual) use ($datos) {
        return array_key_exists($clave, $datos) ? $datos[$clave] : $actual;
    };

    /* El producto de la venta y el usuario iniciador son datos de origen.
       Las versiones operativas no pueden reinterpretarlos ni reemplazarlos. */
    $codTipo = isset($trabajo['cod_tipo_trabajoFK']) && $trabajo['cod_tipo_trabajoFK'] !== null
        ? intval($trabajo['cod_tipo_trabajoFK']) : null;
    $codDoctor = isset($trabajo['cod_especialistaFK']) && $trabajo['cod_especialistaFK'] !== null
        ? intval($trabajo['cod_especialistaFK']) : null;

    $codTecnico = intval($valor('cod_tecnico_usuario', isset($trabajo['cod_tecnico_usuarioFK'])
        ? $trabajo['cod_tecnico_usuarioFK'] : 0));
    $codMecanico = null;
    if ($codTecnico > 0) {
        $tecnico = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codTecnico, true);
        if (!$tecnico) {
            trabajoLaboratorioLanzar('tecnico_formal_invalido', 'Seleccione un mecanico dental activo y vinculado a Telar.');
        }
        $codMecanico = intval($tecnico['cod_mecanico_dental']);
    } else {
        $codTecnico = null;
    }

    $codLocal = intval($valor('cod_local', isset($trabajo['cod_localFK']) ? $trabajo['cod_localFK'] : 0));
    $stmt = $mysqli->prepare("SELECT 1 FROM local WHERE cod_local=? AND estado='Activo' LIMIT 1");
    if (!$stmt) {
        trabajoLaboratorioLanzar('local_trabajo_no_disponible', 'No se pudo validar el local seleccionado.');
    }
    $stmt->bind_param('i', $codLocal);
    $stmt->execute();
    $localValido = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if (!$localValido) {
        trabajoLaboratorioLanzar('local_trabajo_invalido', 'Seleccione un local activo.');
    }

    $costoActual = isset($trabajo['costo_estimado']) ? $trabajo['costo_estimado'] : null;
    $costoValor = trabajoLaboratorioUsuarioPuedeGestionarCosto($mysqli, $codUsuario)
        ? $valor('costo_estimado', $costoActual)
        : $costoActual;
    $costo = trim((string)$costoValor) === '' ? null : trabajoLaboratorioEntero($costoValor);
    if ($costo !== null && $costo < 0) {
        trabajoLaboratorioLanzar('costo_invalido', 'El costo no puede ser negativo.');
    }

    return array(
        'cod_tipo_trabajo' => $codTipo,
        'colorimetro' => trabajoLaboratorioTextoEntrada(
            $valor('colorimetro', isset($trabajo['colorimetro']) ? $trabajo['colorimetro'] : ''),
            30
        ),
        'cod_especialista' => $codDoctor,
        'cod_mecanico_dental' => $codMecanico,
        'cod_tecnico_usuario' => $codTecnico,
        'fecha_retiro' => trabajoLaboratorioFechaVersion(
            $valor('fecha_retiro', isset($trabajo['fecha_retiro']) ? $trabajo['fecha_retiro'] : ''),
            'fecha de retiro'
        ),
        'fecha_entrega' => trabajoLaboratorioFechaVersion(
            $valor('fecha_entrega', isset($trabajo['fecha_entrega']) ? $trabajo['fecha_entrega'] : ''),
            'fecha de entrega'
        ),
        'costo_estimado' => $costo,
        'cod_local' => $codLocal,
        'observacion' => trabajoLaboratorioTextoEntrada(
            $valor('observacion', isset($trabajo['instrucciones']) ? $trabajo['instrucciones'] : ''),
            1000
        )
    );
}

function trabajoLaboratorioAplicarDatosVersion($mysqli, $trabajo, $datos)
{
    $sql = 'UPDATE trabajo_laboratorio SET cod_tipo_trabajoFK=?,colorimetro=?,'
        .'cod_especialistaFK=?,cod_mecanico_dentalFK=?,cod_tecnico_usuarioFK=?,'
        .'fecha_retiro=?,fecha_entrega=?,costo_estimado=?,cod_localFK=?,instrucciones=? '
        .'WHERE id=? AND version=? AND cod_custodio_actualFK=? AND estado_derivado=? LIMIT 1';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('datos_trabajo_no_preparados', 'No se pudieron preparar los datos de esta version.');
    }
    $valores = array(
        $datos['cod_tipo_trabajo'],
        trabajoLaboratorioTextoBaseDatos($datos['colorimetro'], 30),
        $datos['cod_especialista'],
        $datos['cod_mecanico_dental'],
        $datos['cod_tecnico_usuario'],
        $datos['fecha_retiro'],
        $datos['fecha_entrega'],
        $datos['costo_estimado'],
        $datos['cod_local'],
        trabajoLaboratorioTextoBaseDatos($datos['observacion'], 1000),
        intval($trabajo['id']),
        intval($trabajo['version']),
        intval($trabajo['cod_custodio_actualFK']),
        (string)$trabajo['estado_derivado']
    );
    trabajoLaboratorioVincularParametros($stmt, 'isiiissiisiiis', $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('datos_trabajo_no_guardados', 'No se pudieron guardar los datos de esta version.');
    }
    $stmt->close();
}

function trabajoLaboratorioSnapshotBaseNodo($mysqli, $idEventoCustodia, $alternativa)
{
    $idEventoCustodia = intval($idEventoCustodia);
    if ($idEventoCustodia <= 0) {
        return is_array($alternativa) ? $alternativa : array();
    }
    $stmt = $mysqli->prepare(
        'SELECT metadata_json FROM trabajo_laboratorio_evento WHERE id=? LIMIT 1'
    );
    if ($stmt) {
        $stmt->bind_param('i', $idEventoCustodia);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($fila) {
            $metadata = trabajoLaboratorioDecodificarJson(
                trabajoLaboratorioTextoUtf8($fila['metadata_json']),
                array()
            );
            if (!empty($metadata['datos_trabajo']) && is_array($metadata['datos_trabajo'])) {
                return $metadata['datos_trabajo'];
            }
        }
    }
    $stmt = $mysqli->prepare(
        "SELECT metadata_json FROM trabajo_laboratorio_evento WHERE id_evento_custodiaFK=? "
        ."AND tipo_evento='datos_trabajo_actualizados' ORDER BY fecha_servidor ASC,id ASC LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('i', $idEventoCustodia);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($fila) {
            $metadata = trabajoLaboratorioDecodificarJson(
                trabajoLaboratorioTextoUtf8($fila['metadata_json']),
                array()
            );
            if (!empty($metadata['datos_base_nodo']) && is_array($metadata['datos_base_nodo'])) {
                return $metadata['datos_base_nodo'];
            }
        }
    }
    return is_array($alternativa) ? $alternativa : array();
}

function trabajoLaboratorioIniciar($mysqli, $codUsuario, $entrada)
{
    if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
        trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
    }
    $idRegularizacionEntrada = trabajoLaboratorioEntero(isset($entrada['id_regularizacion']) ? $entrada['id_regularizacion'] : 0);
    $accionComando = $idRegularizacionEntrada > 0 ? 'iniciarTrabajosAgrupados' : 'iniciarTrabajo';
    return trabajoLaboratorioEjecutarComando(
        $mysqli,
        $codUsuario,
        $accionComando,
        $entrada,
        function ($idIdempotencia, $contexto) use (
            $mysqli,
            $codUsuario,
            $entrada,
            $idRegularizacionEntrada,
            $accionComando
        ) {
            $codDetalle = trabajoLaboratorioEntero(isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta'] : 0);
            $codTecnico = trabajoLaboratorioEntero(isset($entrada['cod_tecnico_usuario'])
                ? $entrada['cod_tecnico_usuario']
                : (isset($entrada['cod_tecnico_usuarioFK']) ? $entrada['cod_tecnico_usuarioFK'] : 0));
            $detalle = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, true);
            if (!$detalle) {
                trabajoLaboratorioLanzar('detalle_no_encontrado', 'No se encontro el detalle de tratamiento.');
            }
            if (!trabajoLaboratorioDetalleClinicoActivo($detalle)) {
                trabajoLaboratorioLanzar('detalle_venta_inactivo', 'No se puede iniciar un trabajo sobre una venta o tratamiento inactivo o finalizado.');
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
                $accionComando,
                $entrada
            );
            $config = $detalle['configuracion_laboratorio'];
            if (empty($config['ok']) || empty($config['requiere_laboratorio'])) {
                trabajoLaboratorioLanzar('producto_no_requiere_laboratorio', 'El producto no esta configurado para laboratorio.');
            }
            $cantidadAgrupada = trabajoLaboratorioCantidadAgrupadaDetalle($detalle);
            $regularizacion = null;
            $unidadesRegularizadas = array();
            if ($cantidadAgrupada > 1) {
                $regularizacion = trabajoLaboratorioObtenerRegularizacionPendiente($mysqli, $codDetalle, true);
                if (!$regularizacion || intval($regularizacion['id']) !== $idRegularizacionEntrada) {
                    trabajoLaboratorioLanzar(
                        'regularizacion_unidades_requerida',
                        'Primero debe designar y confirmar las piezas de cada trabajo.'
                    );
                }
                if (intval($regularizacion['cantidad_unidades']) !== $cantidadAgrupada
                    || count($regularizacion['unidades']) !== $cantidadAgrupada) {
                    trabajoLaboratorioLanzar('regularizacion_unidades_incompleta', 'La regularizacion no contiene todas las unidades del detalle.');
                }
                $unidadesRegularizadas = $regularizacion['unidades'];
                $ubicaciones = array($unidadesRegularizadas[0]);
            } else {
                if (abs(floatval($detalle['cantidad_detalle']) - 1.0) >= 0.000001 || $idRegularizacionEntrada > 0) {
					trabajoLaboratorioLanzar(
						'cantidad_laboratorio_invalida',
						'La cantidad del detalle no admite el inicio solicitado.'
					);
                }
                $ubicaciones = trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, $codDetalle);
            }
            $bloqueoUbicacion = trabajoLaboratorioValidarUbicacionesModo($config['modo_individualizacion'], $ubicaciones);
            if ($bloqueoUbicacion) {
                trabajoLaboratorioLanzar($bloqueoUbicacion['codigo'], $bloqueoUbicacion['mensaje']);
            }
            if (trabajoLaboratorioObtenerTrabajoActivoDetalle($mysqli, $codDetalle)) {
                trabajoLaboratorioLanzar('trabajo_activo_existente', 'Este detalle ya tiene un trabajo de laboratorio activo.');
            }
			$antecedenteHistorico = trabajoLaboratorioAntecedenteHistoricoDetalle(
				$mysqli,
				$codDetalle,
				true
			);
			if ($antecedenteHistorico) {
				trabajoLaboratorioLanzar(
					'antecedente_historico_existente',
					'Este tratamiento ya posee un antecedente historico. Administracion debe regularizarlo antes de iniciar otro trabajo.'
				);
			}
            $tecnico = null;
            if ($codTecnico > 0) {
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
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidenciasAdicionales) > max(0, $maximoEvidencias - 1)) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Puede adjuntar hasta '.$maximoEvidencias.' imagenes al iniciar el trabajo.'
                );
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
            $codMecanico = $tecnico ? intval($tecnico['cod_mecanico_dental']) : null;
            $codTecnicoBd = $tecnico ? intval($tecnico['cod_usuarioFK']) : null;
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
            $estado = $tecnico ? 'pendiente_entrega_mecanico' : 'pendiente_tecnico';
            $dias = 30;
            $instruccionesBd = trabajoLaboratorioTextoBaseDatos($instrucciones, 1000);
            $codConsulta = $origen['cod_consulta_origen'];
            $codEvolucion = $origen['cod_evolucion_origen'];
            $claveOperacion = isset($entrada['clave_idempotencia']) ? $entrada['clave_idempotencia'] : '';
            $codigoOrigen = $regularizacion
                ? trabajoLaboratorioTextoBaseDatos($regularizacion['codigo_origen'], 100)
                : trabajoLaboratorioCodigoOrigenUnidades($codVenta, $codDetalle, $claveOperacion);
            $unidadOrigen = 1;
            $cantidadUnidadesOrigen = $cantidadAgrupada > 1 ? $cantidadAgrupada : 1;
            $idRegularizacionUnidad = $regularizacion
                ? intval($unidadesRegularizadas[0]['id']) : null;
            $valores = array(
                $idLegacy, $codVenta, $numeroBd, $siglaBd, $codigoOrigen, $codDetalle, $codDetalle,
                $unidadOrigen, $cantidadUnidadesOrigen, $idRegularizacionUnidad,
                intval($detalle['cod_clienteFK']), $productoBd, $codTipo, $codConsulta,
                $codEvolucion, intval($hilo['cod_interConsultaFK']), $codLocal, $codMecanico,
                $codUsuario, $codTecnicoBd, $codUsuario, $estado, $dias, trabajoLaboratorioTextoBaseDatos($color, 30),
                $instruccionesBd, $costo, $codUsuario, $codUsuario
            );
            $sqlInsertTrabajo =
                'INSERT INTO trabajo_laboratorio '
                .'(cod_trabajo_mecanico_legacyFK,cod_ventaFK,numero_venta_snapshot,sigla_local_snapshot,codigo_origen,'
                .'cod_detalle_ventaFK,cod_detalle_activo_unico,unidad_origen,cantidad_unidades_origen,id_regularizacion_unidadFK,'
                .'cod_clienteFK,cod_productoFK,cod_tipo_trabajoFK,'
                .'cod_consulta_origenFK,cod_evolucion_origenFK,cod_interConsultaFK,cod_localFK,'
                .'cod_mecanico_dentalFK,cod_especialistaFK,cod_tecnico_usuarioFK,cod_custodio_actualFK,ciclo_actual,estado_derivado,'
                .'fecha_objetivo,colorimetro,instrucciones,costo_estimado,version,fecha_creacion,cod_usuarioFK_create,'
                .'fecha_actualizacion,cod_usuarioFK_update) '
                .'VALUES ('.implode(',', array_fill(0, 21, '?')).',1,?,DATE_ADD(NOW(),INTERVAL ? DAY),?,?,?,1,NOW(),?,NOW(),?)';
            $stmt = $mysqli->prepare($sqlInsertTrabajo);
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
                $idLink = !empty($ubicacion['id']) ? intval($ubicacion['id']) : null;
                $pieza = trabajoLaboratorioTextoBaseDatos(isset($ubicacion['pieza']) ? $ubicacion['pieza'] : null, 5);
                $piezasJson = trabajoLaboratorioTextoBaseDatos(json_encode(isset($ubicacion['piezas']) ? $ubicacion['piezas'] : array()));
                $superficiesJson = trabajoLaboratorioTextoBaseDatos(json_encode(isset($ubicacion['superficies']) ? $ubicacion['superficies'] : array()));
                $denticion = trabajoLaboratorioTextoBaseDatos(isset($ubicacion['denticion']) ? $ubicacion['denticion'] : null, 20);
                $arcada = trabajoLaboratorioTextoBaseDatos(isset($ubicacion['arcada']) ? $ubicacion['arcada'] : null, 30);
                $cuadrante = trabajoLaboratorioTextoBaseDatos(isset($ubicacion['cuadrante']) ? $ubicacion['cuadrante'] : null, 30);
                $boca = !empty($ubicacion['boca_completa']) ? 1 : 0;
                $alcance = trabajoLaboratorioTextoBaseDatos(isset($ubicacion['alcance']) ? $ubicacion['alcance'] : 'pieza_dental', 40);
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
                    array(
                        'modo_individualizacion' => $config['modo_individualizacion'],
                        'codigo_origen' => $codigoOrigen,
                        'unidad_origen' => 1,
                        'cantidad_unidades_origen' => $cantidadUnidadesOrigen,
                        'datos_trabajo' => trabajoLaboratorioSnapshotDatosTrabajo($trabajo),
                        'campos_modificados' => array(),
                        'estado_resultante' => $estado,
                        'tecnico_pendiente' => $tecnico ? 0 : 1
                    ),
                    $motivoExcepcionInicio
                ),
                null, null, $codUsuario, $codUsuario, $codTecnicoBd, $codConsulta, $codEvolucion
            );
            $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
            trabajoLaboratorioInsertarMedia($mysqli, $trabajo, $idCiclo, $idEvento, $codUsuario, $media, 'inicial');
            foreach ($evidenciasAdicionales as $evidenciaAdicional) {
                $mediaAdicional = trabajoLaboratorioGuardarMediaProtegida($evidenciaAdicional, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli, $trabajo, $idCiclo, $idEvento, $codUsuario, $mediaAdicional, 'inicial_adicional'
                );
            }
            $idsTrabajos = array($idTrabajo);
            if ($regularizacion && $cantidadUnidadesOrigen > 1) {
                $claveBase = trabajoLaboratorioNormalizarClave($claveOperacion);
                for ($indiceUnidad = 1; $indiceUnidad < count($unidadesRegularizadas); $indiceUnidad++) {
                    $unidadRegularizada = $unidadesRegularizadas[$indiceUnidad];
                    $numeroUnidad = intval($unidadRegularizada['numero_unidad']);
                    $claveUnidad = substr($claveBase, 0, 86).'-unidad-'.$numeroUnidad;
                    $hashUnidad = hash('sha256', trabajoLaboratorioHashPayload($entrada).'|unidad|'.$numeroUnidad);
                    $idemUnidad = trabajoLaboratorioPrepararIdempotencia(
                        $mysqli,
                        intval($codUsuario),
                        'iniciarTrabajoUnidad',
                        $claveUnidad,
                        $hashUnidad
                    );
                    if (!empty($idemUnidad['repetida'])) {
                        trabajoLaboratorioLanzar('unidad_regularizacion_duplicada', 'Una unidad ya fue creada por otra operacion.');
                    }
                    $stmt = $mysqli->prepare(
                        'INSERT INTO trabajo_mecanico_dental '
                        .'(cod_ventaFK,cod_tipo_trabajoFK,observacion,colorimetro,costo,estado,'
                        .'cod_usuarioFK_create,cod_especialistaFK,cod_localFK,cod_mecanicoDentalFK) '
                        .'VALUES (?,?,?,?,?,?,?,?,?,?)'
                    );
                    if (!$stmt) {
                        trabajoLaboratorioLanzar('legado_no_guardado', 'No se pudo preparar una unidad compatible del trabajo.');
                    }
                    $valoresLegacyUnidad = $valoresLegacy;
                    trabajoLaboratorioVincularParametros(
                        $stmt,
                        str_repeat('s', count($valoresLegacyUnidad)),
                        $valoresLegacyUnidad
                    );
                    if (!$stmt->execute()) {
                        $stmt->close();
                        trabajoLaboratorioLanzar('legado_no_guardado', 'No se pudo conservar una unidad compatible del trabajo.');
                    }
                    $idLegacyUnidad = intval($stmt->insert_id);
                    $stmt->close();

                    $valoresUnidad = $valores;
                    $valoresUnidad[0] = $idLegacyUnidad;
                    $valoresUnidad[7] = $numeroUnidad;
                    $valoresUnidad[9] = intval($unidadRegularizada['id']);
                    $stmt = $mysqli->prepare($sqlInsertTrabajo);
                    if (!$stmt) {
                        trabajoLaboratorioLanzar('trabajo_no_guardado', 'No se pudo preparar una unidad del trabajo.');
                    }
                    trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valoresUnidad)), $valoresUnidad);
                    if (!$stmt->execute()) {
                        $errno = intval($stmt->errno);
                        $stmt->close();
                        if ($errno === 1062) {
                            trabajoLaboratorioLanzar('trabajo_activo_existente', 'Una unidad del detalle ya posee un trabajo activo.');
                        }
                        trabajoLaboratorioLanzar('trabajo_no_guardado', 'No se pudo crear una unidad del trabajo.');
                    }
                    $idTrabajoUnidad = intval($stmt->insert_id);
                    $stmt->close();

                    $codigoUnidad = trabajoLaboratorioCodigoVisible($ventaCodigo, $idTrabajoUnidad);
                    $codigoUnidadBd = trabajoLaboratorioTextoBaseDatos($codigoUnidad, 100);
                    $stmt = $mysqli->prepare('UPDATE trabajo_laboratorio SET codigo_visible=? WHERE id=? LIMIT 1');
                    $stmt->bind_param('si', $codigoUnidadBd, $idTrabajoUnidad);
                    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                        $stmt->close();
                        trabajoLaboratorioLanzar('codigo_no_guardado', 'No se pudo asignar el codigo trazable de una unidad.');
                    }
                    $stmt->close();

                    $stmt = $mysqli->prepare(
                        "INSERT INTO trabajo_laboratorio_ciclo "
                        ."(id_trabajoFK,numero_ciclo,tipo,fecha_objetivo,cod_usuario_solicitanteFK,fecha_creacion) "
                        ."VALUES (?,1,'original',DATE_ADD(NOW(),INTERVAL ? DAY),?,NOW())"
                    );
                    $stmt->bind_param('iii', $idTrabajoUnidad, $dias, $codUsuario);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        trabajoLaboratorioLanzar('ciclo_no_guardado', 'No se pudo crear el ciclo de una unidad.');
                    }
                    $idCicloUnidad = intval($stmt->insert_id);
                    $stmt->close();

                    $piezaUnidad = trabajoLaboratorioTextoBaseDatos($unidadRegularizada['pieza'], 5);
                    $piezasUnidadJson = trabajoLaboratorioTextoBaseDatos(json_encode($unidadRegularizada['piezas']));
                    $superficiesUnidadJson = trabajoLaboratorioTextoBaseDatos(json_encode(array()));
                    $denticionUnidad = trabajoLaboratorioTextoBaseDatos($unidadRegularizada['denticion'], 20);
                    $alcanceUnidad = trabajoLaboratorioTextoBaseDatos($unidadRegularizada['alcance'], 40);
                    $idLinkUnidad = null;
                    $arcadaUnidad = null;
                    $cuadranteUnidad = null;
                    $bocaUnidad = 0;
                    $stmt = $mysqli->prepare(
                        'INSERT INTO trabajo_laboratorio_ubicacion '
                        .'(id_trabajoFK,id_odontograma_link_origenFK,pieza,piezas_json,superficies_json,denticion,'
                        .'arcada,cuadrante,boca_completa,alcance_odontologico,fecha_creacion,cod_usuarioFK_create) '
                        .'VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)'
                    );
                    $valoresUbicacionUnidad = array(
                        $idTrabajoUnidad, $idLinkUnidad, $piezaUnidad, $piezasUnidadJson,
                        $superficiesUnidadJson, $denticionUnidad, $arcadaUnidad, $cuadranteUnidad,
                        $bocaUnidad, $alcanceUnidad, $codUsuario
                    );
                    trabajoLaboratorioVincularParametros(
                        $stmt,
                        str_repeat('s', count($valoresUbicacionUnidad)),
                        $valoresUbicacionUnidad
                    );
                    if (!$stmt->execute()) {
                        $stmt->close();
                        trabajoLaboratorioLanzar('ubicacion_no_guardada', 'No se pudo conservar la ubicacion de una unidad.');
                    }
                    $stmt->close();

                    $trabajoUnidad = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajoUnidad, true);
                    $idEventoUnidad = trabajoLaboratorioRegistrarEvento(
                        $mysqli,
                        $trabajoUnidad,
                        $idCicloUnidad,
                        intval($idemUnidad['id']),
                        'trabajo_iniciado',
                        $codUsuario,
                        1,
                        $observacionInicio,
                        trabajoLaboratorioMetadataExcepcionAuditor(
                            array(
                                'modo_individualizacion' => $config['modo_individualizacion'],
                                'codigo_origen' => $codigoOrigen,
                                'unidad_origen' => $numeroUnidad,
                                'cantidad_unidades_origen' => $cantidadUnidadesOrigen,
                                'datos_trabajo' => trabajoLaboratorioSnapshotDatosTrabajo($trabajoUnidad),
                                'campos_modificados' => array(),
                                'estado_resultante' => $estado,
                                'tecnico_pendiente' => $tecnico ? 0 : 1
                            ),
                            $motivoExcepcionInicio
                        ),
                        null,
                        null,
                        $codUsuario,
                        $codUsuario,
                        $codTecnicoBd,
                        $codConsulta,
                        $codEvolucion
                    );
                    $mediaUnidad = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajoUnidad, $contexto);
                    trabajoLaboratorioInsertarMedia(
                        $mysqli,
                        $trabajoUnidad,
                        $idCicloUnidad,
                        $idEventoUnidad,
                        $codUsuario,
                        $mediaUnidad,
                        'inicial'
                    );
                    foreach ($evidenciasAdicionales as $evidenciaAdicionalUnidad) {
                        $mediaAdicionalUnidad = trabajoLaboratorioGuardarMediaProtegida(
                            $evidenciaAdicionalUnidad,
                            $idTrabajoUnidad,
                            $contexto
                        );
                        trabajoLaboratorioInsertarMedia(
                            $mysqli,
                            $trabajoUnidad,
                            $idCicloUnidad,
                            $idEventoUnidad,
                            $codUsuario,
                            $mediaAdicionalUnidad,
                            'inicial_adicional'
                        );
                    }
                    $respuestaUnidad = trabajoLaboratorioRespuestaActualizada(
                        $mysqli,
                        $codUsuario,
                        $idTrabajoUnidad,
                        'trabajo_iniciado',
                        'La unidad '.$numeroUnidad.' de '.$cantidadUnidadesOrigen.' fue iniciada.'
                    );
                    trabajoLaboratorioCompletarIdempotencia(
                        $mysqli,
                        intval($idemUnidad['id']),
                        $idTrabajoUnidad,
                        $respuestaUnidad
                    );
                    $idsTrabajos[] = $idTrabajoUnidad;
                }
                $estadoConsumido = 'consumida';
                $idRegularizacion = intval($regularizacion['id']);
                $versionRegularizacion = intval($regularizacion['version']) + 1;
                $stmt = $mysqli->prepare(
                    'UPDATE trabajo_laboratorio_regularizacion SET estado=?,cod_detalle_pendiente_unico=NULL,'
                    .'fecha_consumo=NOW(),cod_usuarioFK_consumo=?,version=? '
                    .'WHERE id=? AND estado=\'pendiente_preparacion\' LIMIT 1'
                );
                $stmt->bind_param('siii', $estadoConsumido, $codUsuario, $versionRegularizacion, $idRegularizacion);
                if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                    $stmt->close();
                    trabajoLaboratorioLanzar('regularizacion_no_consumida', 'No se pudo cerrar la regularizacion de unidades.');
                }
                $stmt->close();
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                $regularizacion ? 'trabajos_agrupados_iniciados' : 'trabajo_iniciado',
                $regularizacion
                    ? 'Se iniciaron '.$cantidadUnidadesOrigen.' trabajos independientes con el mismo codigo de origen.'
                        .($tecnico ? '' : ' Todos quedaron con tecnico pendiente.')
                    : 'El trabajo de laboratorio fue iniciado.'
                        .($tecnico ? '' : ' Quedo con tecnico pendiente.')
            );
            if ($regularizacion && isset($respuesta['datos'])) {
                $trabajosCreados = array();
                foreach ($idsTrabajos as $idTrabajoCreado) {
                    $trabajoCreado = trabajoLaboratorioObtenerTrabajo($mysqli, intval($idTrabajoCreado), false);
                    if ($trabajoCreado) {
                        $trabajosCreados[] = trabajoLaboratorioFormatearTrabajo(
                            $mysqli,
                            $codUsuario,
                            $trabajoCreado,
                            true
                        );
                    }
                }
                $respuesta['datos']['trabajos'] = $trabajosCreados;
                $respuesta['datos']['cantidad_trabajos'] = count($trabajosCreados);
                $respuesta['datos']['codigo_origen'] = trabajoLaboratorioTextoUtf8($codigoOrigen);
                $respuesta['datos']['regularizacion_consumida'] = intval($regularizacion['id']);
            }
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
    /* Los registros incorporados desde el archivo historico conservan la fila
       legacy como evidencia de origen. Sus cambios posteriores quedan en el
       timeline nuevo y en la auditoria de convalidacion, sin reescribir el
       estado, las fechas ni el ultimo editor originales. */
    if (trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_historico')) {
        $stmtHistorico = $mysqli->prepare(
            'SELECT 1 FROM trabajo_laboratorio_historico '
            .'WHERE cod_trabajo_mecanico_legacyFK=? LIMIT 1'
        );
        if ($stmtHistorico) {
            $stmtHistorico->bind_param('i', $idLegacy);
            $stmtHistorico->execute();
            $esHistoricoSincronizado = $stmtHistorico->get_result()->num_rows > 0;
            $stmtHistorico->close();
            if ($esHistoricoSincronizado) {
                return;
            }
        }
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

function trabajoLaboratorioAsignarTecnico($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioEjecutarComando(
        $mysqli,
        $codUsuario,
        'asignarTecnico',
        $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajoSeleccionado = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, false);
            trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajoSeleccionado);

            $codTecnico = trabajoLaboratorioEntero(isset($entrada['cod_tecnico_usuario'])
                ? $entrada['cod_tecnico_usuario']
                : (isset($entrada['cod_tecnico_usuarioFK']) ? $entrada['cod_tecnico_usuarioFK'] : 0));
            $tecnico = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codTecnico, true);
            if (!$tecnico) {
                trabajoLaboratorioLanzar(
                    'tecnico_formal_invalido',
                    'Seleccione un tecnico activo vinculado a un usuario formal.'
                );
            }
            if (!trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'VERTRABAJOSLABORATORIO')
                || !trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'RECIBIRTRABAJOLABORATORIO')
                || !trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'ENTREGARTRABAJOLABORATORIO')) {
                trabajoLaboratorioLanzar(
                    'tecnico_sin_acceso_laboratorio',
                    'El tecnico seleccionado necesita permisos de acceso, recepcion y entrega para completar el circuito.'
                );
            }

            $codigoOrigen = trim((string)$trabajoSeleccionado['codigo_origen']);
            if ($codigoOrigen === '') {
                trabajoLaboratorioLanzar(
                    'codigo_origen_no_disponible',
                    'El trabajo no posee un codigo de origen valido para asignar el tecnico.'
                );
            }
            $stmt = $mysqli->prepare(
                'SELECT id FROM trabajo_laboratorio '
                .'WHERE codigo_origen=? AND cod_detalle_activo_unico IS NOT NULL '
                .'ORDER BY unidad_origen ASC,id ASC FOR UPDATE'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar(
                    'grupo_trabajos_no_disponible',
                    'No se pudo revisar el grupo de trabajos antes de asignar el tecnico.'
                );
            }
            $stmt->bind_param('s', $codigoOrigen);
            if (!$stmt->execute()) {
                $stmt->close();
                trabajoLaboratorioLanzar(
                    'grupo_trabajos_no_disponible',
                    'No se pudo revisar el grupo de trabajos antes de asignar el tecnico.'
                );
            }
            $resultado = $stmt->get_result();
            $idsGrupo = array();
            while ($fila = $resultado->fetch_assoc()) {
                $idsGrupo[] = intval($fila['id']);
            }
            $stmt->close();

            /* Todos los actores toman los bloqueos del lote en el mismo orden.
               Luego se revalidan estado, permisos y version sobre la fila ya bloqueada. */
            $trabajoSeleccionado = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli,
                $codUsuario,
                $trabajoSeleccionado,
                'asignarTecnico',
                $entrada
            );
            trabajoLaboratorioExigirVersion($trabajoSeleccionado, $entrada);

            $cantidadEsperada = max(1, intval($trabajoSeleccionado['cantidad_unidades_origen']));
            if (count($idsGrupo) !== $cantidadEsperada || !in_array($idTrabajo, $idsGrupo, true)) {
                trabajoLaboratorioLanzar(
                    'grupo_trabajos_inconsistente',
                    'El grupo de origen no esta completo. Actualice la vista antes de asignar el tecnico.'
                );
            }
            $idsProcesamiento = array($idTrabajo);
            foreach ($idsGrupo as $idGrupo) {
                if ($idGrupo !== $idTrabajo) {
                    $idsProcesamiento[] = $idGrupo;
                }
            }

            $claveBase = trabajoLaboratorioNormalizarClave(isset($entrada['clave_idempotencia'])
                ? $entrada['clave_idempotencia']
                : (isset($entrada['idempotency_key']) ? $entrada['idempotency_key'] : ''));
            $codMecanico = intval($tecnico['cod_mecanico_dental']);
            $estadoNuevo = 'pendiente_entrega_mecanico';
            $observacion = trabajoLaboratorioTextoEntrada(
                isset($entrada['observacion']) ? $entrada['observacion'] : '',
                750
            );
            $respuestasPorId = array();

            foreach ($idsProcesamiento as $indice => $idGrupo) {
                $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idGrupo, true);
                if (!$trabajo || (string)$trabajo['estado_derivado'] !== 'pendiente_tecnico') {
                    trabajoLaboratorioLanzar(
                        'grupo_trabajos_estado_invalido',
                        'Todos los trabajos del mismo origen deben continuar con tecnico pendiente.'
                    );
                }
                $idEventoIdempotencia = intval($idIdempotencia);
                $idIdempotenciaUnidad = 0;
                if ($indice > 0) {
                    $unidad = max(1, intval($trabajo['unidad_origen']));
                    $claveUnidad = substr($claveBase, 0, 82).'-tecnico-'.$unidad;
                    $hashUnidad = hash(
                        'sha256',
                        trabajoLaboratorioHashPayload($entrada).'|tecnico|'.$idGrupo.'|'.$codTecnico
                    );
                    $idemUnidad = trabajoLaboratorioPrepararIdempotencia(
                        $mysqli,
                        intval($codUsuario),
                        'asignarTecnicoUnidad',
                        $claveUnidad,
                        $hashUnidad
                    );
                    if (!empty($idemUnidad['repetida'])) {
                        trabajoLaboratorioLanzar(
                            'asignacion_tecnico_duplicada',
                            'Una unidad del grupo ya fue asignada por otra operacion.'
                        );
                    }
                    $idIdempotenciaUnidad = intval($idemUnidad['id']);
                    $idEventoIdempotencia = $idIdempotenciaUnidad;
                }

                $versionAnterior = intval($trabajo['version']);
                $versionNueva = $versionAnterior + 1;
                $stmt = $mysqli->prepare(
                    "UPDATE trabajo_laboratorio SET cod_mecanico_dentalFK=?,cod_tecnico_usuarioFK=?,"
                    ."estado_derivado=?,version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? "
                    ."WHERE id=? AND version=? AND estado_derivado='pendiente_tecnico' LIMIT 1"
                );
                if (!$stmt) {
                    trabajoLaboratorioLanzar('tecnico_no_asignado', 'No se pudo preparar la asignacion del tecnico.');
                }
                $stmt->bind_param(
                    'iisiiii',
                    $codMecanico,
                    $codTecnico,
                    $estadoNuevo,
                    $versionNueva,
                    $codUsuario,
                    $idGrupo,
                    $versionAnterior
                );
                if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                    $stmt->close();
                    trabajoLaboratorioLanzar(
                        'version_desactualizada',
                        'Uno de los trabajos cambio antes de asignar el tecnico.'
                    );
                }
                $stmt->close();

                $idLegacy = intval($trabajo['cod_trabajo_mecanico_legacyFK']);
                if ($idLegacy > 0) {
                    $stmt = $mysqli->prepare(
                        'UPDATE trabajo_mecanico_dental SET cod_mecanicoDentalFK=?,fecha_edit=NOW(),'
                        .'cod_usuarioFK_edit=? WHERE cod_trabajo_mecanico_dental=? LIMIT 1'
                    );
                    if (!$stmt) {
                        trabajoLaboratorioLanzar(
                            'legado_no_actualizado',
                            'No se pudo conservar la asignacion en el registro compatible.'
                        );
                    }
                    $stmt->bind_param('iii', $codMecanico, $codUsuario, $idLegacy);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        trabajoLaboratorioLanzar(
                            'legado_no_actualizado',
                            'No se pudo conservar la asignacion en el registro compatible.'
                        );
                    }
                    $stmt->close();
                }

                $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idGrupo, true);
                $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $actual);
                trabajoLaboratorioRegistrarEvento(
                    $mysqli,
                    $actual,
                    intval($ciclo['id']),
                    $idEventoIdempotencia,
                    'tecnico_asignado',
                    $codUsuario,
                    $versionNueva,
                    $observacion,
                    trabajoLaboratorioMetadataExcepcionAuditor(
                        array(
                            'codigo_origen' => $codigoOrigen,
                            'unidad_origen' => intval($actual['unidad_origen']),
                            'cantidad_unidades_origen' => intval($actual['cantidad_unidades_origen']),
                            'cod_tecnico_usuario' => $codTecnico,
                            'estado_resultante' => $estadoNuevo,
                            'transferencia_iniciada' => 0
                        ),
                        $motivoExcepcion
                    ),
                    null,
                    null,
                    null,
                    null,
                    $codTecnico
                );
                $respuestaUnidad = trabajoLaboratorioRespuestaActualizada(
                    $mysqli,
                    $codUsuario,
                    $idGrupo,
                    'tecnico_asignado',
                    'El tecnico fue asignado sin iniciar el traslado.'
                );
                if ($idIdempotenciaUnidad > 0) {
                    trabajoLaboratorioCompletarIdempotencia(
                        $mysqli,
                        $idIdempotenciaUnidad,
                        $idGrupo,
                        $respuestaUnidad
                    );
                }
                $respuestasPorId[$idGrupo] = $respuestaUnidad;
            }

            $respuesta = $respuestasPorId[$idTrabajo];
            $trabajosActualizados = array();
            foreach ($idsGrupo as $idGrupo) {
                $actualizado = trabajoLaboratorioObtenerTrabajo($mysqli, $idGrupo, false);
                if ($actualizado) {
                    $trabajosActualizados[] = trabajoLaboratorioFormatearTrabajo(
                        $mysqli,
                        $codUsuario,
                        $actualizado,
                        true
                    );
                }
            }
            $cantidadActualizada = count($trabajosActualizados);
            $respuesta['codigo'] = 'tecnico_asignado';
            $respuesta['mensaje'] = $cantidadActualizada > 1
                ? 'El tecnico fue asignado a los '.$cantidadActualizada.' trabajos del mismo origen. El traslado continua sin iniciar.'
                : 'El tecnico fue asignado. El traslado continua sin iniciar.';
            if (isset($respuesta['datos'])) {
                $respuesta['datos']['trabajos'] = $trabajosActualizados;
                $respuesta['datos']['cantidad_trabajos'] = $cantidadActualizada;
                $respuesta['datos']['codigo_origen'] = trabajoLaboratorioTextoUtf8($codigoOrigen);
                $respuesta['datos']['transferencia_iniciada'] = false;
            }
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
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
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidencias) < 1 || count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'evidencia_transferencia_requerida',
                    'Adjunte entre una y '.$maximoEvidencias.' fotos de la entrega.'
                );
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

function trabajoLaboratorioActualizarDatos($mysqli, $codUsuario, $entrada)
{
    trabajoLaboratorioExigirHiloCustodiaDisponible($mysqli);
    return trabajoLaboratorioEjecutarComando(
        $mysqli,
        $codUsuario,
        'actualizarDatosTrabajo',
        $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioExigirAcceso($mysqli, $codUsuario, $trabajo);
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            if (in_array((string)$trabajo['estado_derivado'], array('instalado', 'cancelado'), true)) {
                trabajoLaboratorioLanzar(
                    'version_trabajo_cerrada',
                    'Un trabajo finalizado conserva su ultima version en modo consulta.'
                );
            }
            if (intval($trabajo['cod_custodio_actualFK']) !== intval($codUsuario)) {
                trabajoLaboratorioLanzar(
                    'edicion_nodo_no_autorizada',
                    'Solo el responsable actual puede modificar la version activa.'
                );
            }
            $idEventoCustodia = trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, true);
            if ($idEventoCustodia <= 0) {
                trabajoLaboratorioLanzar(
                    'periodo_custodia_no_encontrado',
                    'No se pudo identificar el nodo activo que debe conservar esta version.'
                );
            }
            $snapshotAnterior = trabajoLaboratorioSnapshotDatosTrabajo($trabajo);
            $datos = trabajoLaboratorioDatosVersionEntrada($mysqli, $codUsuario, $trabajo, $entrada);
            trabajoLaboratorioAplicarDatosVersion($mysqli, $trabajo, $datos);
            $trabajoConDatos = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $snapshotNuevo = trabajoLaboratorioSnapshotDatosTrabajo($trabajoConDatos);
            $snapshotBase = trabajoLaboratorioSnapshotBaseNodo(
                $mysqli,
                $idEventoCustodia,
                $snapshotAnterior
            );
            $camposOperacion = trabajoLaboratorioCamposModificadosSnapshot(
                $snapshotAnterior,
                $snapshotNuevo
            );
            $camposNodo = trabajoLaboratorioCamposModificadosSnapshot(
                $snapshotBase,
                $snapshotNuevo
            );
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            if (count($evidencias) > trabajoLaboratorioMaximoArchivosMedia()) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Puede adjuntar hasta '.trabajoLaboratorioMaximoArchivosMedia().' fotografias por actualizacion.'
                );
            }
            if (count($camposOperacion) === 0 && count($evidencias) === 0) {
                trabajoLaboratorioLanzar(
                    'version_sin_cambios',
                    'Modifique al menos un dato o agregue una fotografia antes de guardar.'
                );
            }
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = (string)$trabajo['estado_derivado'];
            $custodio = intval($trabajo['cod_custodio_actualFK']);
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                .'WHERE id=? AND version=? AND cod_custodio_actualFK=? AND estado_derivado=? LIMIT 1'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar('version_no_guardada', 'No se pudo preparar la actualizacion del nodo.');
            }
            $stmt->bind_param(
                'iiiiis',
                $versionNueva,
                $codUsuario,
                $idTrabajo,
                $versionAnterior,
                $custodio,
                $estado
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar(
                    'version_ya_cambio',
                    'El trabajo cambio antes de guardar. Actualice la informacion y vuelva a revisar.'
                );
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $snapshotNuevo = trabajoLaboratorioSnapshotDatosTrabajo($actual);
            $snapshotNuevo['version_registro'] = $versionNueva;
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $actual);
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli,
                $actual,
                intval($ciclo['id']),
                $idIdempotencia,
                'datos_trabajo_actualizados',
                $codUsuario,
                $versionNueva,
                '',
                array(
                    'actualizacion_version' => 1,
                    'datos_base_nodo' => $snapshotBase,
                    'datos_trabajo' => $snapshotNuevo,
                    'campos_modificados_operacion' => $camposOperacion,
                    'campos_modificados' => $camposNodo,
                    'cantidad_evidencias' => count($evidencias),
                    'estado_resultante' => $estado
                ),
                null,
                $custodio,
                $custodio,
                null,
                null,
                null,
                null,
                $idEventoCustodia
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida($evidencia, $idTrabajo, $contexto);
                trabajoLaboratorioInsertarMedia(
                    $mysqli,
                    $actual,
                    intval($ciclo['id']),
                    $idEvento,
                    $codUsuario,
                    $media,
                    'version_trabajo'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                'datos_trabajo_actualizados',
                'La version activa quedo actualizada y la anterior se conserva en la trazabilidad.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioTomarHilo($mysqli, $codUsuario, $entrada, $accionComando = 'tomarHilo')
{
    trabajoLaboratorioExigirHiloCustodiaDisponible($mysqli);
    $accionesComando = array('tomarHilo', 'confirmarRecepcion', 'confirmarDevolucion');
    if (!in_array($accionComando, $accionesComando, true)) {
        $accionComando = 'tomarHilo';
    }
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, $accionComando, $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada, $accionComando) {
            $condicion = trabajoLaboratorioNormalizarTexto(
                isset($entrada['condicion_recepcion']) ? $entrada['condicion_recepcion']
                    : (isset($entrada['condicion']) ? $entrada['condicion'] : '')
            );
            if (!in_array($condicion, array('conforme', 'con_observaciones'), true)) {
                trabajoLaboratorioLanzar(
                    'condicion_recepcion_requerida',
                    'Indique si recibe el trabajo conforme o con observaciones.'
                );
            }
            $observacion = trabajoLaboratorioTextoEntrada(
                isset($entrada['observacion']) ? $entrada['observacion'] : '',
                750
            );
            if ($condicion === 'con_observaciones' && strlen($observacion) < 5) {
                trabajoLaboratorioLanzar(
                    'observacion_recepcion_requerida',
                    'Describa brevemente la condicion observada al recibir el trabajo.'
                );
            }
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Puede adjuntar hasta '.$maximoEvidencias.' fotos de la recepcion.'
                );
            }
            if (count($evidencias) < 1) {
                trabajoLaboratorioLanzar(
                    'foto_recepcion_requerida',
                    'Adjunte al menos una foto nueva para recibir el trabajo.'
                );
            }

            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            if ($accionComando === 'confirmarRecepcion'
                && (string)$trabajo['estado_derivado'] !== 'en_transferencia_mecanico') {
                trabajoLaboratorioLanzar(
                    'accion_no_permitida',
                    'La recepcion de laboratorio solamente puede confirmarse durante ese traslado.'
                );
            }
            if ($accionComando === 'confirmarDevolucion'
                && (string)$trabajo['estado_derivado'] !== 'en_transferencia_clinica') {
                trabajoLaboratorioLanzar(
                    'accion_no_permitida',
                    'La devolucion solamente puede confirmarse durante el retorno a la clinica.'
                );
            }
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'tomarHilo', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            if (intval($trabajo['cod_custodio_actualFK']) === intval($codUsuario)) {
                trabajoLaboratorioLanzar(
                    'custodia_ya_asignada',
                    'Este usuario ya es el responsable actual del trabajo.'
                );
            }
            $snapshotAnterior = trabajoLaboratorioSnapshotDatosTrabajo($trabajo);
            $datosVersion = trabajoLaboratorioDatosVersionEntrada($mysqli, $codUsuario, $trabajo, $entrada);
            trabajoLaboratorioAplicarDatosVersion($mysqli, $trabajo, $datosVersion);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $estadoAnterior = (string)$trabajo['estado_derivado'];
            $estadoNuevo = $estadoAnterior;
            $tipoEvento = 'hilo_tomado';
            $idTransferencia = null;
            $remitente = null;
            $destinatarioPrevisto = null;
            $actuaEnRepresentacion = false;
            $completaTransferencia = false;
            if ($estadoAnterior === 'en_transferencia_mecanico'
                || $estadoAnterior === 'en_transferencia_clinica') {
                $transferencia = trabajoLaboratorioObtenerTransferenciaPendiente($mysqli, $trabajo);
                $tipoEsperado = $estadoAnterior === 'en_transferencia_mecanico'
                    ? 'clinica_a_laboratorio' : 'laboratorio_a_clinica';
                if ((string)$transferencia['tipo'] !== $tipoEsperado) {
                    trabajoLaboratorioLanzar(
                        'tipo_transferencia_invalido',
                        'La transferencia pendiente no coincide con el recorrido actual del trabajo.'
                    );
                }
                $destinatarioPrevisto = intval($transferencia['cod_destinatario_previstoFK']);
                $actuaEnRepresentacion = $destinatarioPrevisto !== intval($codUsuario);
                $idTransferencia = intval($transferencia['id']);
                $remitente = intval($transferencia['cod_remitenteFK']);
                $usuarioReceptor = trabajoLaboratorioUsuario($mysqli, $codUsuario);
                $localReceptor = $usuarioReceptor ? intval($usuarioReceptor['cod_localFK']) : 0;
                $localDestino = intval($transferencia['cod_local_destinoFK']);
                /* Un custodio interno puede tomar el hilo durante el traslado.
                   La etapa operativa solo se completa cuando recibe el
                   destinatario previsto o alguien de la ubicacion destino. */
                $completaTransferencia = $destinatarioPrevisto === intval($codUsuario)
                    || ($localDestino > 0 && $localReceptor === $localDestino);
                if ($completaTransferencia) {
                    if ($estadoAnterior === 'en_transferencia_mecanico') {
                        $estadoNuevo = 'en_laboratorio';
                        $tipoEvento = 'recepcion_mecanico_confirmada';
                    } else {
                        $estadoNuevo = 'pendiente_revision';
                        $tipoEvento = 'devolucion_confirmada';
                    }
                }
            }

            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $custodioAnterior = intval($trabajo['cod_custodio_actualFK']);
            if ($completaTransferencia && $estadoAnterior === 'en_transferencia_mecanico') {
                $stmt = $mysqli->prepare(
                    'UPDATE trabajo_laboratorio SET estado_derivado=?,cod_custodio_actualFK=?,'
                    .'id_transferencia_pendienteFK=NULL,fecha_retiro=COALESCE(fecha_retiro,NOW()),'
                    .'version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                    .'WHERE id=? AND version=? AND cod_custodio_actualFK=? '
                    ."AND estado_derivado='en_transferencia_mecanico' LIMIT 1"
                );
                if (!$stmt) {
                    trabajoLaboratorioLanzar('custodia_no_guardada', 'No se pudo preparar la recepcion del trabajo.');
                }
                $stmt->bind_param(
                    'siiiiii',
                    $estadoNuevo,
                    $codUsuario,
                    $versionNueva,
                    $codUsuario,
                    $idTrabajo,
                    $versionAnterior,
                    $custodioAnterior
                );
            } elseif ($completaTransferencia && $estadoAnterior === 'en_transferencia_clinica') {
                $stmt = $mysqli->prepare(
                    'UPDATE trabajo_laboratorio SET estado_derivado=?,cod_custodio_actualFK=?,'
                    .'id_transferencia_pendienteFK=NULL,version=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                    .'WHERE id=? AND version=? AND cod_custodio_actualFK=? '
                    ."AND estado_derivado='en_transferencia_clinica' LIMIT 1"
                );
                if (!$stmt) {
                    trabajoLaboratorioLanzar('custodia_no_guardada', 'No se pudo preparar la recepcion del trabajo.');
                }
                $stmt->bind_param(
                    'siiiiii',
                    $estadoNuevo,
                    $codUsuario,
                    $versionNueva,
                    $codUsuario,
                    $idTrabajo,
                    $versionAnterior,
                    $custodioAnterior
                );
            } else {
                $stmt = $mysqli->prepare(
                    'UPDATE trabajo_laboratorio SET cod_custodio_actualFK=?,version=?,'
                    .'fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                    .'WHERE id=? AND version=? AND cod_custodio_actualFK=? AND estado_derivado=? LIMIT 1'
                );
                if (!$stmt) {
                    trabajoLaboratorioLanzar('custodia_no_guardada', 'No se pudo preparar el cambio de responsable.');
                }
                $stmt->bind_param(
                    'iiiiiis',
                    $codUsuario,
                    $versionNueva,
                    $codUsuario,
                    $idTrabajo,
                    $versionAnterior,
                    $custodioAnterior,
                    $estadoAnterior
                );
            }
            if (!$stmt || !$stmt->execute() || $stmt->affected_rows !== 1) {
                if ($stmt) {
                    $stmt->close();
                }
                trabajoLaboratorioLanzar(
                    'custodia_ya_cambio',
                    'Otra persona tomo el hilo antes de esta confirmacion. Actualice la vista.'
                );
            }
            $stmt->close();
            if ($tipoEvento === 'recepcion_mecanico_confirmada') {
                trabajoLaboratorioActualizarLegado($mysqli, $trabajo, $tipoEvento, $codUsuario);
            }
            $eventoCustodiaAnterior = trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, true);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $snapshotNuevo = trabajoLaboratorioSnapshotDatosTrabajo($actual);
            $snapshotNuevo['version_registro'] = $versionNueva;
            $camposModificados = trabajoLaboratorioCamposModificadosSnapshot(
                $snapshotAnterior,
                $snapshotNuevo
            );
            $metadata = trabajoLaboratorioMetadataExcepcionAuditor(
                array(
                    'nodo_custodia' => 1,
                    'datos_trabajo' => $snapshotNuevo,
                    'datos_trabajo_anterior' => $snapshotAnterior,
                    'campos_modificados' => $camposModificados,
                    'condicion_recepcion' => $condicion,
                    'sin_foto' => 0,
                    'motivo_sin_foto' => null,
                    'detalle_sin_foto' => null,
                    'cantidad_evidencias' => count($evidencias),
                    'id_evento_custodia_anterior' => $eventoCustodiaAnterior > 0
                        ? $eventoCustodiaAnterior : null,
                    'estado_anterior' => $estadoAnterior,
                    'estado_resultante' => $estadoNuevo,
                    'transferencia_completada' => $completaTransferencia ? 1 : 0,
                    'transferencia_continua' => $idTransferencia !== null && !$completaTransferencia ? 1 : 0,
                    'actuo_en_representacion' => $actuaEnRepresentacion ? 1 : 0,
                    'destinatario_previsto' => $destinatarioPrevisto
                ),
                $motivoExcepcion
            );
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli,
                $actual,
                intval($ciclo['id']),
                $idIdempotencia,
                $tipoEvento,
                $codUsuario,
                $versionNueva,
                $observacion,
                $metadata,
                $idTransferencia,
                $custodioAnterior,
                $codUsuario,
                $remitente,
                $destinatarioPrevisto
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida(
                    $evidencia,
                    $idTrabajo,
                    $contexto
                );
                trabajoLaboratorioInsertarMedia(
                    $mysqli,
                    $actual,
                    intval($ciclo['id']),
                    $idEvento,
                    $codUsuario,
                    $media,
                    $tipoEvento === 'devolucion_confirmada' ? 'recepcion_final' : 'recepcion_custodia'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                'hilo_tomado',
                'El hilo quedo a cargo del usuario que confirmo la recepcion.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioConfirmarRecepcion($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioTomarHilo($mysqli, $codUsuario, $entrada, 'confirmarRecepcion');
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
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidencias) < 1 || count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Adjunte entre una y '.$maximoEvidencias.' imagenes por operacion.'
                );
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'agregarEvidencia', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $idEventoCustodia = trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, false);
            $versionNueva = trabajoLaboratorioIncrementarVersion($mysqli, $trabajo, $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = isset($entrada['observacion']) ? $entrada['observacion'] : '';
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'evidencia_agregada',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array(
                        'cantidad' => count($evidencias),
                        'id_evento_custodia' => $idEventoCustodia > 0 ? $idEventoCustodia : null
                    ),
                    $motivoExcepcion
                ),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $idEventoCustodia > 0 ? $idEventoCustodia : null
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
            $idEventoCustodia = trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, false);
            $versionNueva = trabajoLaboratorioIncrementarVersion($mysqli, $trabajo, $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'nota_agregada',
                $codUsuario, $versionNueva, $nota,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array('id_evento_custodia' => $idEventoCustodia > 0 ? $idEventoCustodia : null),
                    $motivoExcepcion
                ),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $idEventoCustodia > 0 ? $idEventoCustodia : null
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli, $codUsuario, $idTrabajo, 'nota_agregada', 'La nota fue agregada al historial.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioRegistrarNovedad($mysqli, $codUsuario, $entrada)
{
    trabajoLaboratorioExigirHiloCustodiaDisponible($mysqli);
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'registrarNovedad', $entrada,
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $descripcion = trabajoLaboratorioTextoEntrada(
                isset($entrada['descripcion']) ? $entrada['descripcion']
                    : (isset($entrada['nota']) ? $entrada['nota']
                        : (isset($entrada['observacion']) ? $entrada['observacion'] : '')),
                750
            );
            if (strlen($descripcion) < 3) {
                trabajoLaboratorioLanzar(
                    'descripcion_novedad_requerida',
                    'Describa brevemente la novedad ocurrida durante su custodia.'
                );
            }
            $tipoNovedad = trabajoLaboratorioNormalizarTexto(
                isset($entrada['tipo_novedad']) ? $entrada['tipo_novedad'] : 'observacion_general'
            );
            $tiposPermitidos = array(
                'modificacion_trabajo', 'cambio_color', 'ajuste_solicitado',
                'problema_detectado', 'pieza_danada', 'falta_informacion',
                'trabajo_listo', 'solicitud_confirmacion_clinica', 'observacion_general'
            );
            if (!in_array($tipoNovedad, $tiposPermitidos, true)) {
                trabajoLaboratorioLanzar(
                    'tipo_novedad_invalido',
                    'Seleccione un tipo de novedad valido.'
                );
            }
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Puede adjuntar hasta '.$maximoEvidencias.' archivos por novedad.'
                );
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioExigirAccion(
                $mysqli,
                $codUsuario,
                $trabajo,
                'registrarNovedad',
                $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            if (intval($trabajo['cod_custodio_actualFK']) !== intval($codUsuario)) {
                trabajoLaboratorioLanzar(
                    'custodia_no_vigente',
                    'Solo el responsable actual puede registrar novedades en este periodo.'
                );
            }
            $idEventoCustodia = trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, true);
            if ($idEventoCustodia <= 0) {
                trabajoLaboratorioLanzar(
                    'periodo_custodia_no_encontrado',
                    'No se pudo identificar el periodo de custodia vigente.'
                );
            }
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionNueva = trabajoLaboratorioIncrementarVersion($mysqli, $trabajo, $codUsuario);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli,
                $actual,
                intval($ciclo['id']),
                $idIdempotencia,
                'novedad_custodia',
                $codUsuario,
                $versionNueva,
                $descripcion,
                array(
                    'tipo_novedad' => $tipoNovedad,
                    'id_evento_custodia' => $idEventoCustodia,
                    'cantidad_evidencias' => count($evidencias),
                    'estado_resultante' => (string)$trabajo['estado_derivado']
                ),
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $idEventoCustodia
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida(
                    $evidencia,
                    $idTrabajo,
                    $contexto,
                    true
                );
                trabajoLaboratorioInsertarMedia(
                    $mysqli,
                    $actual,
                    intval($ciclo['id']),
                    $idEvento,
                    $codUsuario,
                    $media,
                    'novedad_custodia'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                'novedad_custodia_registrada',
                'La novedad quedo vinculada a su periodo de custodia.'
            );
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioRegistrarNovedadCustodia($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioRegistrarNovedad($mysqli, $codUsuario, $entrada);
}

function trabajoLaboratorioRectificarCustodia($mysqli, $codUsuario, $entrada)
{
    trabajoLaboratorioExigirHiloCustodiaDisponible($mysqli);
    return trabajoLaboratorioEjecutarComando(
        $mysqli, $codUsuario, 'rectificarCustodia', $entrada,
        function ($idIdempotencia) use ($mysqli, $codUsuario, $entrada) {
            $justificacion = trabajoLaboratorioTextoEntrada(
                isset($entrada['justificacion']) ? $entrada['justificacion']
                    : (isset($entrada['motivo']) ? $entrada['motivo']
                        : (isset($entrada['observacion']) ? $entrada['observacion'] : '')),
                750
            );
            if ($justificacion === '') {
                trabajoLaboratorioLanzar(
                    'justificacion_rectificacion_requerida',
                    'Explique por que corresponde rectificar al responsable actual.'
                );
            }
            $codCustodioNuevo = trabajoLaboratorioEntero(
                isset($entrada['cod_custodio_rectificado']) ? $entrada['cod_custodio_rectificado']
                    : (isset($entrada['cod_custodio']) ? $entrada['cod_custodio']
                        : (isset($entrada['cod_custodio_nuevo']) ? $entrada['cod_custodio_nuevo'] : 0))
            );
            if ($codCustodioNuevo <= 0) {
                trabajoLaboratorioLanzar(
                    'custodio_rectificacion_requerido',
                    'Seleccione el usuario Telar que debe quedar como responsable.'
                );
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioExigirAccion(
                $mysqli,
                $codUsuario,
                $trabajo,
                'rectificarCustodia',
                $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $custodioAnterior = intval($trabajo['cod_custodio_actualFK']);
            $snapshotCustodiaAnterior = trabajoLaboratorioSnapshotDatosTrabajo($trabajo);
            if ($codCustodioNuevo === $custodioAnterior) {
                trabajoLaboratorioLanzar(
                    'custodio_sin_cambio',
                    'El usuario seleccionado ya es el responsable actual.'
                );
            }
            $custodioNuevo = trabajoLaboratorioUsuario($mysqli, $codCustodioNuevo);
            if (!$custodioNuevo) {
                trabajoLaboratorioLanzar(
                    'custodio_rectificacion_invalido',
                    'El usuario seleccionado debe ser una cuenta Telar activa.'
                );
            }
            $idEventoAnterior = trabajoLaboratorioEventoCustodiaActual($mysqli, $trabajo, true);
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = (string)$trabajo['estado_derivado'];
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET cod_custodio_actualFK=?,version=?,'
                .'fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                .'WHERE id=? AND version=? AND cod_custodio_actualFK=? AND estado_derivado=? LIMIT 1'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar(
                    'custodia_no_rectificada',
                    'No se pudo preparar la rectificacion de custodia.'
                );
            }
            $stmt->bind_param(
                'iiiiiis',
                $codCustodioNuevo,
                $versionNueva,
                $codUsuario,
                $idTrabajo,
                $versionAnterior,
                $custodioAnterior,
                $estado
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar(
                    'custodia_ya_cambio',
                    'El responsable cambio antes de confirmar la rectificacion.'
                );
            }
            $stmt->close();
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            trabajoLaboratorioRegistrarEvento(
                $mysqli,
                $actual,
                intval($ciclo['id']),
                $idIdempotencia,
                'custodia_rectificada',
                $codUsuario,
                $versionNueva,
                $justificacion,
                array(
                    'nodo_custodia' => 1,
                    'rectificacion_administrativa' => 1,
                    'datos_trabajo' => trabajoLaboratorioSnapshotDatosTrabajo($actual),
                    'datos_trabajo_anterior' => $snapshotCustodiaAnterior,
                    'campos_modificados' => array(),
                    'id_evento_custodia_anterior' => $idEventoAnterior > 0
                        ? $idEventoAnterior : null,
                    'custodio_nombre_snapshot' => trabajoLaboratorioTextoUtf8(
                        isset($custodioNuevo['nombre_persona']) ? $custodioNuevo['nombre_persona'] : ''
                    ),
                    'custodio_rol_snapshot' => trabajoLaboratorioTextoUtf8(
                        isset($custodioNuevo['tipo']) ? $custodioNuevo['tipo'] : ''
                    ),
                    'custodio_avatar_snapshot' => trabajoLaboratorioTextoUtf8(
                        isset($custodioNuevo['url']) ? $custodioNuevo['url'] : ''
                    ),
                    'custodio_local_snapshot' => trabajoLaboratorioTextoUtf8(
                        isset($custodioNuevo['nombre_local']) ? $custodioNuevo['nombre_local'] : ''
                    ),
                    'estado_resultante' => $estado
                ),
                null,
                $custodioAnterior,
                $codCustodioNuevo
            );
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                'custodia_rectificada',
                'La custodia fue rectificada sin sobrescribir el historial anterior.'
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
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidencias) < 1 || count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'evidencia_final_requerida',
                    'Adjunte entre una y '.$maximoEvidencias.' fotos del trabajo terminado.'
                );
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
    return trabajoLaboratorioTomarHilo($mysqli, $codUsuario, $entrada, 'confirmarDevolucion');
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
            if ($justificacion === '') {
                trabajoLaboratorioLanzar('justificacion_ajuste_requerida', 'Explique brevemente que debe ajustarse.');
            }
            $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            if (count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Puede adjuntar hasta '.$maximoEvidencias.' imagenes por ajuste.'
                );
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
        function ($idIdempotencia, $contexto) use ($mysqli, $codUsuario, $entrada) {
            $modoResolucion = trabajoLaboratorioNormalizarTexto(
                isset($entrada['modo_resolucion']) ? $entrada['modo_resolucion'] : ''
            );
            if ($modoResolucion !== '' && $modoResolucion !== 'instalado_entregado') {
                trabajoLaboratorioLanzar(
                    'modo_resolucion_invalido',
                    'Seleccione Instalado y finalizado para cerrar el trabajo.'
                );
            }
            $condicion = trabajoLaboratorioNormalizarTexto(
                isset($entrada['condicion_pre_entrega']) ? $entrada['condicion_pre_entrega'] : ''
            );
            if (!in_array($condicion, array('conforme', 'con_observaciones'), true)) {
                trabajoLaboratorioLanzar(
                    'condicion_entrega_requerida',
                    'Indique la situacion del trabajo antes de confirmar la entrega.'
                );
            }
            $observacionEntrega = trabajoLaboratorioTextoEntrada(
                isset($entrada['observacion_entrega']) ? $entrada['observacion_entrega'] : '',
                1000
            );
            if ($condicion === 'con_observaciones' && $observacionEntrega === '') {
                trabajoLaboratorioLanzar(
                    'observacion_entrega_requerida',
                    'Describa la situacion observada antes de la entrega.'
                );
            }
            $evidencias = trabajoLaboratorioEvidenciasFinales($entrada);
            $maximoEvidencias = trabajoLaboratorioMaximoArchivosMedia();
            $sinFoto = isset($entrada['sin_foto']) && (string)$entrada['sin_foto'] === '1';
            $motivoSinFoto = trabajoLaboratorioNormalizarTexto(
                isset($entrada['motivo_sin_foto']) ? $entrada['motivo_sin_foto'] : ''
            );
            $detalleSinFoto = trabajoLaboratorioTextoEntrada(
                isset($entrada['detalle_sin_foto']) ? $entrada['detalle_sin_foto'] : '',
                750
            );
            $motivosSinFoto = array(
                'falla_dispositivo', 'imposibilidad_operativa', 'foto_no_disponible', 'otro'
            );
            if (count($evidencias) > $maximoEvidencias) {
                trabajoLaboratorioLanzar(
                    'cantidad_evidencias_invalida',
                    'Puede adjuntar hasta '.$maximoEvidencias.' fotos para confirmar la instalacion.'
                );
            }
            if (count($evidencias) < 1) {
                if (!$sinFoto) {
                    trabajoLaboratorioLanzar(
                        'evidencia_instalacion_requerida',
                        'Adjunte al menos una foto o declare una excepcion justificada.'
                    );
                }
                if (!in_array($motivoSinFoto, $motivosSinFoto, true)) {
                    trabajoLaboratorioLanzar(
                        'motivo_sin_foto_requerido',
                        'Seleccione el motivo por el que no existe evidencia fotografica.'
                    );
                }
                if ($detalleSinFoto === '') {
                    trabajoLaboratorioLanzar(
                        'detalle_sin_foto_requerido',
                        'Explique por que no fue posible adjuntar la evidencia fotografica.'
                    );
                }
            } else {
                $sinFoto = false;
                $motivoSinFoto = '';
                $detalleSinFoto = '';
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $motivoExcepcion = trabajoLaboratorioExigirAccion(
                $mysqli, $codUsuario, $trabajo, 'registrarInstalacion', $entrada
            );
            trabajoLaboratorioExigirVersion($trabajo, $entrada);
            $esExcepcionAuditor = trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
                && $motivoExcepcion !== '';
            $codEvolucionOrigen = trabajoLaboratorioEntero(
                isset($entrada['cod_evolucion_origen']) ? $entrada['cod_evolucion_origen'] : 0
            );
            $origen = array(
                'cod_consulta_origen' => null,
                'cod_evolucion_origen' => null,
                'cod_usuario_evolucion' => 0
            );
            if ($codEvolucionOrigen > 0) {
                $origen = trabajoLaboratorioValidarEvolucionInstalacion(
                    $mysqli,
                    $trabajo,
                    $codUsuario,
                    isset($entrada['cod_consulta_origen']) ? $entrada['cod_consulta_origen'] : 0,
                    $codEvolucionOrigen,
                    $esExcepcionAuditor
                );
            }
            $estadoAnterior = (string)$trabajo['estado_derivado'];
            $transferenciaPendiente = null;
            $idTransferencia = null;
            $remitenteTransferencia = null;
            $destinatarioTransferencia = null;
            if (intval($trabajo['id_transferencia_pendienteFK']) > 0) {
                $transferenciaPendiente = trabajoLaboratorioObtenerTransferenciaPendiente(
                    $mysqli,
                    $trabajo
                );
                $idTransferencia = intval($transferenciaPendiente['id']);
                $remitenteTransferencia = intval($transferenciaPendiente['cod_remitenteFK']);
                $destinatarioTransferencia = intval(
                    $transferenciaPendiente['cod_destinatario_previstoFK']
                );
            }
            $ciclo = trabajoLaboratorioObtenerCicloActual($mysqli, $trabajo);
            $versionAnterior = intval($trabajo['version']);
            $versionNueva = $versionAnterior + 1;
            $estado = 'instalado';
            $stmt = $mysqli->prepare(
                'UPDATE trabajo_laboratorio SET estado_derivado=?,'
                .'id_transferencia_pendienteFK=NULL,fecha_instalado=NOW(),version=?,'
                .'fecha_actualizacion=NOW(),cod_usuarioFK_update=? '
                .'WHERE id=? AND version=? AND cod_custodio_actualFK=? LIMIT 1'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar('instalacion_no_guardada', 'No se pudo preparar el cierre del trabajo.');
            }
            $stmt->bind_param(
                'siiiii',
                $estado,
                $versionNueva,
                $codUsuario,
                $idTrabajo,
                $versionAnterior,
                $codUsuario
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('version_desactualizada', 'El trabajo cambio antes de registrar la instalacion.');
            }
            $stmt->close();
            $actual = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            $observacion = $condicion === 'con_observaciones'
                ? $observacionEntrega
                : trabajoLaboratorioTextoEntrada(
                    isset($entrada['observacion']) ? $entrada['observacion'] : '',
                    1000
                );
            $idEvento = trabajoLaboratorioRegistrarEvento(
                $mysqli, $actual, intval($ciclo['id']), $idIdempotencia, 'instalacion_registrada',
                $codUsuario, $versionNueva, $observacion,
                trabajoLaboratorioMetadataExcepcionAuditor(
                    array(
                        'resolucion_operativa' => 1,
                        'modo_resolucion' => 'instalado_entregado',
                        'condicion_pre_entrega' => $condicion,
                        'observacion_entrega' => $observacionEntrega,
                        'cantidad_evidencias' => count($evidencias),
                        'sin_foto' => $sinFoto ? 1 : 0,
                        'motivo_sin_foto' => $sinFoto ? $motivoSinFoto : null,
                        'detalle_sin_foto' => $sinFoto ? $detalleSinFoto : null,
                        'estado_anterior' => $estadoAnterior,
                        'transferencia_pendiente_cerrada' => $idTransferencia !== null ? 1 : 0,
                        'tipo_transferencia_cerrada' => $transferenciaPendiente
                            && isset($transferenciaPendiente['tipo_transferencia'])
                            ? (string)$transferenciaPendiente['tipo_transferencia'] : null,
                        'evolucion_clinica_explicita' => $codEvolucionOrigen > 0 ? 1 : 0,
                        'cod_usuario_evolucion' => intval($origen['cod_usuario_evolucion']),
                        'cierra_custodia' => 1,
                        'id_evento_custodia' => trabajoLaboratorioEventoCustodiaActual(
                            $mysqli,
                            $trabajo,
                            false
                        )
                    ),
                    $motivoExcepcion
                ),
                $idTransferencia,
                intval($trabajo['cod_custodio_actualFK']), null,
                $remitenteTransferencia, $destinatarioTransferencia,
                $origen['cod_consulta_origen'], $origen['cod_evolucion_origen']
            );
            foreach ($evidencias as $evidencia) {
                $media = trabajoLaboratorioGuardarMediaProtegida(
                    $evidencia,
                    $idTrabajo,
                    $contexto
                );
                trabajoLaboratorioInsertarMedia(
                    $mysqli,
                    $actual,
                    intval($ciclo['id']),
                    $idEvento,
                    $codUsuario,
                    $media,
                    'instalacion_final'
                );
            }
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                'instalacion_registrada',
                'El trabajo quedo instalado y finalizado. El hilo fue cerrado.'
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
            if ($motivo === '') {
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

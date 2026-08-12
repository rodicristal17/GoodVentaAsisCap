<?php

/**
 * Planificacion visual de especialistas - Sistema Telar / Clinident Salud.
 *
 * Endpoint JSON compatible con PHP 7.2. No crea ni altera estructuras.
 * Las migraciones actualizacion_27072026_planificacion_visual_especialistas.sql
 * y actualizacion_27072026_planificacion_especialistas_por_sucursal.sql deben
 * aplicarse de forma controlada para habilitar escrituras, vinculos e historial.
 */

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Asuncion');

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/planificacion_especialistas_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, private');

class PlanificacionEspecialistasExcepcion extends Exception
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

function planificacionLanzar($codigo, $mensaje, $datos = array())
{
    throw new PlanificacionEspecialistasExcepcion($codigo, $mensaje, $datos);
}

function planificacionUtf8($valor)
{
    if (is_array($valor)) {
        $salida = array();
        foreach ($valor as $clave => $item) {
            $salida[$clave] = planificacionUtf8($item);
        }
        return $salida;
    }
    if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
        return mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
    }
    return $valor;
}

function planificacionResponder($ok, $codigo, $mensaje, $datos = array(), $estadoHttp = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(intval($estadoHttp));
    }
    echo json_encode(
        planificacionUtf8(array(
            'ok' => $ok ? true : false,
            'codigo' => (string)$codigo,
            'mensaje' => (string)$mensaje,
            'datos' => $datos
        )),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function planificacionParametro($nombre, $predeterminado = '')
{
    if (isset($_POST[$nombre])) {
        return $_POST[$nombre];
    }
    if (isset($_GET[$nombre])) {
        return $_GET[$nombre];
    }
    return $predeterminado;
}

function planificacionEntero($valor)
{
    return is_numeric($valor) ? intval($valor) : 0;
}

function planificacionTextoEntrada($valor, $maximo = 255)
{
    if (is_array($valor) || is_object($valor)) {
        return '';
    }
    $texto = trim((string)$valor);
    if (mb_strlen($texto, 'UTF-8') > $maximo) {
        $texto = mb_substr($texto, 0, $maximo, 'UTF-8');
    }
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

function planificacionTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $claveCache = spl_object_hash($mysqli).'|'.(string)$tabla;
    if (isset($cache[$claveCache])) {
        return $cache[$claveCache];
    }
    $sql = "SELECT COUNT(*) FROM information_schema.tables
        WHERE table_schema=DATABASE() AND table_name=?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $tabla);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $total = 0;
    $stmt->bind_result($total);
    $stmt->fetch();
    $stmt->close();
    $cache[$claveCache] = intval($total) > 0;
    return $cache[$claveCache];
}

function planificacionEstructuraDisponible($mysqli)
{
    return planificacionTablaExiste($mysqli, 'planificacion_especialista_perfil')
        && planificacionTablaExiste($mysqli, 'planificacion_especialista_regla')
        && planificacionTablaExiste($mysqli, 'planificacion_especialista_asignacion')
        && planificacionTablaExiste($mysqli, 'planificacion_especialista_historial');
}

function planificacionVinculosLocalesDisponibles($mysqli)
{
    return planificacionTablaExiste($mysqli, 'planificacion_especialista_local');
}

function planificacionTienePermiso($mysqli, $codUsuario, $codigo)
{
    static $cache = array();
    $clave = intval($codUsuario).'|'.strtoupper((string)$codigo);
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
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $codigoIso = mb_convert_encoding(strtoupper((string)$codigo), 'ISO-8859-1', 'UTF-8');
    $stmt->bind_param('is', $codUsuario, $codigoIso);
    $permitido = false;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $permitido = $resultado && $resultado->num_rows > 0;
    }
    $stmt->close();
    $cache[$clave] = $permitido;
    return $permitido;
}

function planificacionUsuario($mysqli, $codUsuario)
{
    $sql = "SELECT u.cod_usuario,u.tipo,u.estado,u.cod_localFK,u.url,
            COALESCE(NULLIF(p.nombre_persona,''),u.login) AS nombre_persona,
            IFNULL(l.Nombre,'') AS nombre_local
        FROM usuario u
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN local l ON l.cod_local=u.cod_localFK
        WHERE u.cod_usuario=?
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $codUsuario);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $resultado = $stmt->get_result();
    $usuario = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $usuario;
}

function planificacionContexto($mysqli, $codUsuario)
{
    $usuario = planificacionUsuario($mysqli, $codUsuario);
    if (!$usuario || strtoupper(trim((string)$usuario['estado'])) !== 'ACTIVO') {
        planificacionLanzar('usuario_no_disponible', 'El usuario autenticado no esta activo.');
    }
    $permisos = array(
        'ver' => planificacionTienePermiso($mysqli, $codUsuario, 'VERPLANIFICACIONESPECIALISTAS'),
        'gestionar' => planificacionTienePermiso($mysqli, $codUsuario, 'GESTIONARPLANIFICACIONESPECIALISTAS'),
        'proponer' => planificacionTienePermiso($mysqli, $codUsuario, 'PROPONERPLANIFICACIONESPECIALISTAS'),
        'recurrencias' => planificacionTienePermiso($mysqli, $codUsuario, 'GESTIONARRECURRENCIASPLANIFICACION'),
        'historial' => planificacionTienePermiso($mysqli, $codUsuario, 'VERHISTORIALPLANIFICACION'),
        'todas_sucursales' => planificacionTienePermiso($mysqli, $codUsuario, 'VERPLANIFICACIONTODASSUCURSALES')
    );
    if (!$permisos['ver']) {
        planificacionLanzar('acceso_no_autorizado', 'No tiene permiso para ver la planificacion de especialistas.');
    }
    $esDoctor = strtoupper(trim((string)$usuario['tipo'])) === 'DOCTOR';
    return array(
        'cod_usuario' => intval($usuario['cod_usuario']),
        'nombre' => $usuario['nombre_persona'],
        'tipo' => $usuario['tipo'],
        'avatar' => $usuario['url'],
        'cod_local' => intval($usuario['cod_localFK']),
        'nombre_local' => $usuario['nombre_local'],
        'es_doctor' => $esDoctor,
        'solo_propio' => $esDoctor
            && !$permisos['gestionar']
            && !$permisos['proponer']
            && !$permisos['recurrencias'],
        'permisos' => $permisos
    );
}

function planificacionFechaValida($valor)
{
    $fecha = DateTime::createFromFormat('Y-m-d', (string)$valor);
    return $fecha && $fecha->format('Y-m-d') === (string)$valor;
}

function planificacionRango($desde, $hasta)
{
    if (!planificacionFechaValida($desde) || !planificacionFechaValida($hasta)) {
        planificacionLanzar('rango_invalido', 'Seleccione un rango de fechas valido.');
    }
    $inicio = new DateTime($desde);
    $fin = new DateTime($hasta);
    if ($fin < $inicio) {
        planificacionLanzar('rango_invalido', 'La fecha final no puede ser anterior a la inicial.');
    }
    $dias = intval($inicio->diff($fin)->format('%a')) + 1;
    if ($dias > 62) {
        planificacionLanzar('rango_demasiado_amplio', 'La vista admite hasta 62 dias por consulta.');
    }
    return array($inicio, $fin, $dias);
}

function planificacionLocalAutorizado($mysqli, $contexto, $solicitado)
{
    $codLocal = planificacionEntero($solicitado);
    if ($codLocal <= 0) {
        $codLocal = intval($contexto['cod_local']);
    }
    if (!$contexto['permisos']['todas_sucursales']
        && $codLocal !== intval($contexto['cod_local'])) {
        planificacionLanzar('local_no_autorizado', 'No tiene permiso para consultar esa sucursal.');
    }
    $sql = "SELECT cod_local,Nombre FROM local
        WHERE cod_local=? AND UPPER(TRIM(estado))='ACTIVO' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('consulta_no_disponible', 'No se pudo validar la sucursal.');
    }
    $stmt->bind_param('i', $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $local = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$local) {
        planificacionLanzar('local_no_disponible', 'La sucursal seleccionada no esta activa.');
    }
    return array('cod_local' => intval($local['cod_local']), 'nombre' => $local['Nombre']);
}

function planificacionLocales($mysqli, $contexto)
{
    $locales = array();
    if ($contexto['permisos']['todas_sucursales']) {
        $resultado = $mysqli->query(
            "SELECT cod_local,Nombre FROM local
             WHERE UPPER(TRIM(estado))='ACTIVO' ORDER BY Nombre"
        );
        while ($resultado && ($fila = $resultado->fetch_assoc())) {
            $locales[] = array('cod_local' => intval($fila['cod_local']), 'nombre' => $fila['Nombre']);
        }
        return $locales;
    }
    $locales[] = array(
        'cod_local' => intval($contexto['cod_local']),
        'nombre' => $contexto['nombre_local']
    );
    return $locales;
}

function planificacionSiglaLocal($nombre, $codLocal, &$usadas)
{
    $texto = trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', (string)$nombre)));
    $palabras = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
    $omitidas = array('CLINIDENT', 'SUCURSAL', 'CONSULTORIO', 'DE', 'DEL', 'LA', 'EL');
    $utiles = array();
    foreach ($palabras as $palabra) {
        $mayuscula = mb_strtoupper($palabra, 'UTF-8');
        if (!in_array($mayuscula, $omitidas, true)) {
            $utiles[] = $mayuscula;
        }
    }
    if (count($utiles) === 0) {
        $utiles = $palabras;
    }
    if (count($utiles) >= 2) {
        $sigla = mb_substr($utiles[0], 0, 1, 'UTF-8')
            .mb_substr($utiles[1], 0, 1, 'UTF-8');
    } else {
        $sigla = mb_substr(isset($utiles[0]) ? $utiles[0] : 'L', 0, 2, 'UTF-8');
    }
    $sigla = mb_strtoupper($sigla, 'UTF-8');
    if (isset($usadas[$sigla])) {
        $sigla .= (string)intval($codLocal);
    }
    $usadas[$sigla] = true;
    return $sigla;
}

function planificacionLocalesConSiglas($locales)
{
    $salida = array();
    $usadas = array();
    foreach ($locales as $local) {
        $local['sigla'] = planificacionSiglaLocal(
            isset($local['nombre']) ? $local['nombre'] : '',
            isset($local['cod_local']) ? $local['cod_local'] : 0,
            $usadas
        );
        $salida[] = $local;
    }
    return $salida;
}

function planificacionProfesionales($mysqli, $contexto, $codLocal, $estructura)
{
    $codLocal = intval($codLocal);
    $perfil = $estructura
        ? "LEFT JOIN planificacion_especialista_perfil pep ON pep.cod_usuarioFK=u.cod_usuario"
        : "";
    $especialidad = $estructura ? "IFNULL(pep.especialidad,'')" : "''";
    $vinculos = planificacionVinculosLocalesDisponibles($mysqli);
    $puedeVerVinculados = $vinculos && !empty($contexto['permisos']['todas_sucursales']);
    $vinculoJoin = $puedeVerVinculados
        ? "LEFT JOIN planificacion_especialista_local pel
            ON pel.cod_profesionalFK=u.cod_usuario
           AND pel.cod_localFK=".$codLocal."
           AND pel.estado='activo'"
        : "";
    $vinculada = $puedeVerVinculados ? "IF(pel.id_vinculo IS NULL,0,1)" : "0";
    $condicionVinculo = $puedeVerVinculados ? " OR pel.id_vinculo IS NOT NULL" : "";
    $sql = "SELECT DISTINCT u.cod_usuario,
            COALESCE(NULLIF(p.nombre_persona,''),u.login) AS nombre,
            IFNULL(u.url,'') AS avatar,
            ".$especialidad." AS especialidad,
            u.cod_localFK AS cod_local_base,
            IFNULL(lb.Nombre,'') AS nombre_local_base,
            ".$vinculada." AS vinculada_planificacion,
            IF(EXISTS (
                SELECT 1 FROM horario_usuario huh
                WHERE huh.cod_usuarioFK=u.cod_usuario
                  AND huh.cod_localFK=".$codLocal."
                  AND UPPER(IFNULL(huh.estado_horario,'ACTIVO'))='ACTIVO'
            ),1,0) AS tiene_horario_local
        FROM usuario u
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN local lb ON lb.cod_local=u.cod_localFK
        ".$perfil."
        ".$vinculoJoin."
        WHERE UPPER(TRIM(u.estado))='ACTIVO'
          AND UPPER(TRIM(u.tipo))='DOCTOR'
          AND (
            u.cod_localFK=".$codLocal."
            OR EXISTS (
                SELECT 1 FROM horario_usuario hu
                WHERE hu.cod_usuarioFK=u.cod_usuario
                  AND hu.cod_localFK=".$codLocal."
                  AND UPPER(IFNULL(hu.estado_horario,'ACTIVO'))='ACTIVO'
            )
            ".$condicionVinculo."
          )";
    if ($contexto['solo_propio']) {
        $sql .= " AND u.cod_usuario=".intval($contexto['cod_usuario']);
    }
    $sql .= " ORDER BY nombre";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('consulta_no_disponible', 'No se pudo obtener el listado de profesionales.');
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $profesionales = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $profesionales[] = array(
            'cod_profesional' => intval($fila['cod_usuario']),
            'nombre' => $fila['nombre'],
            'avatar' => $fila['avatar'],
            'especialidad' => $fila['especialidad'],
            'cod_local_base' => intval($fila['cod_local_base']),
            'nombre_local_base' => $fila['nombre_local_base'],
            'vinculada_planificacion' => intval($fila['vinculada_planificacion']) === 1,
            'tiene_horario_local' => intval($fila['tiene_horario_local']) === 1,
            'origen_listado' => intval($fila['cod_local_base']) === $codLocal
                ? 'base'
                : (intval($fila['tiene_horario_local']) === 1 ? 'horario' : 'vinculo')
        );
    }
    $stmt->close();
    return $profesionales;
}

function planificacionRequerirGestionMultisucursal($contexto)
{
    if (empty($contexto['permisos']['gestionar'])
        || empty($contexto['permisos']['todas_sucursales'])) {
        planificacionLanzar(
            'accion_no_autorizada',
            'Necesita permisos de gestion y acceso a todas las sucursales.'
        );
    }
}

function planificacionVinculoProfesionalLocal($mysqli, $codProfesional, $codLocal, $bloquear)
{
    if (!planificacionVinculosLocalesDisponibles($mysqli)) {
        return null;
    }
    $sql = "SELECT * FROM planificacion_especialista_local
        WHERE cod_profesionalFK=? AND cod_localFK=? LIMIT 1";
    if ($bloquear) {
        $sql .= " FOR UPDATE";
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('ii', $codProfesional, $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $fila;
}

function planificacionCandidatosSucursal($mysqli, $contexto, $entrada)
{
    planificacionRequerirGestionMultisucursal($contexto);
    if (!planificacionVinculosLocalesDisponibles($mysqli)) {
        planificacionLanzar(
            'vinculos_no_instalados',
            'La incorporacion de profesionales requiere aplicar la migracion controlada.'
        );
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $codLocal = intval($local['cod_local']);
    $estructura = planificacionEstructuraDisponible($mysqli);
    $perfil = $estructura
        ? "LEFT JOIN planificacion_especialista_perfil pep ON pep.cod_usuarioFK=u.cod_usuario"
        : "";
    $especialidad = $estructura ? "IFNULL(pep.especialidad,'')" : "''";
    $sql = "SELECT u.cod_usuario,
            COALESCE(NULLIF(p.nombre_persona,''),u.login) AS nombre,
            IFNULL(u.url,'') AS avatar,
            ".$especialidad." AS especialidad,
            u.cod_localFK AS cod_local_base,
            IFNULL(lb.Nombre,'') AS nombre_local_base
        FROM usuario u
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        INNER JOIN local lb ON lb.cod_local=u.cod_localFK
        ".$perfil."
        WHERE UPPER(TRIM(u.estado))='ACTIVO'
          AND UPPER(TRIM(u.tipo))='DOCTOR'
          AND UPPER(TRIM(lb.estado))='ACTIVO'
          AND u.cod_localFK<>?
          AND NOT EXISTS (
              SELECT 1 FROM horario_usuario hu
              WHERE hu.cod_usuarioFK=u.cod_usuario
                AND hu.cod_localFK=?
                AND UPPER(IFNULL(hu.estado_horario,'ACTIVO'))='ACTIVO'
          )
          AND NOT EXISTS (
              SELECT 1 FROM planificacion_especialista_local pel
              WHERE pel.cod_profesionalFK=u.cod_usuario
                AND pel.cod_localFK=?
                AND pel.estado='activo'
          )
        ORDER BY nombre";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('consulta_no_disponible', 'No se pudo obtener los profesionales disponibles.');
    }
    $stmt->bind_param('iii', $codLocal, $codLocal, $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $items = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $items[] = array(
            'cod_profesional' => intval($fila['cod_usuario']),
            'nombre' => $fila['nombre'],
            'avatar' => $fila['avatar'],
            'especialidad' => $fila['especialidad'],
            'cod_local_base' => intval($fila['cod_local_base']),
            'nombre_local_base' => $fila['nombre_local_base']
        );
    }
    $stmt->close();
    return array('items' => $items, 'local_destino' => $local);
}

function planificacionAgregarProfesionalSucursal($mysqli, $contexto, $entrada)
{
    planificacionRequerirGestionMultisucursal($contexto);
    if (!planificacionVinculosLocalesDisponibles($mysqli)
        || !planificacionEstructuraDisponible($mysqli)) {
        planificacionLanzar(
            'vinculos_no_instalados',
            'Aplique la migracion controlada antes de incorporar profesionales.'
        );
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $codProfesional = planificacionEntero(
        isset($entrada['cod_profesional']) ? $entrada['cod_profesional'] : 0
    );
    if ($codProfesional <= 0) {
        planificacionLanzar('profesional_requerido', 'Seleccione un profesional.');
    }
    $profesional = planificacionUsuario($mysqli, $codProfesional);
    if (!$profesional
        || strtoupper(trim((string)$profesional['estado'])) !== 'ACTIVO'
        || strtoupper(trim((string)$profesional['tipo'])) !== 'DOCTOR') {
        planificacionLanzar('profesional_no_disponible', 'El profesional no esta activo.');
    }
    if (intval($profesional['cod_localFK']) === intval($local['cod_local'])) {
        planificacionLanzar(
            'profesional_ya_disponible',
            'El profesional ya pertenece a la sucursal seleccionada.'
        );
    }
    $stmt = $mysqli->prepare(
        "SELECT 1
         FROM local lb
         WHERE lb.cod_local=?
           AND UPPER(TRIM(lb.estado))='ACTIVO'
         LIMIT 1"
    );
    if (!$stmt) {
        planificacionLanzar('consulta_no_disponible', 'No se pudo validar la sucursal base del profesional.');
    }
    $stmt->bind_param('i', $profesional['cod_localFK']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $baseActiva = $resultado && $resultado->num_rows > 0;
    $stmt->close();
    if (!$baseActiva) {
        planificacionLanzar(
            'profesional_no_disponible',
            'La sucursal base del profesional no esta activa.'
        );
    }
    $stmt = $mysqli->prepare(
        "SELECT 1
         FROM horario_usuario hu
         WHERE hu.cod_usuarioFK=?
           AND hu.cod_localFK=?
           AND UPPER(IFNULL(hu.estado_horario,'ACTIVO'))='ACTIVO'
         LIMIT 1"
    );
    if (!$stmt) {
        planificacionLanzar('consulta_no_disponible', 'No se pudo validar la disponibilidad del profesional.');
    }
    $stmt->bind_param('ii', $codProfesional, $local['cod_local']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $yaTieneHorario = $resultado && $resultado->num_rows > 0;
    $stmt->close();
    if ($yaTieneHorario) {
        planificacionLanzar(
            'profesional_ya_disponible',
            'El profesional ya esta disponible porque tiene un horario activo en la sucursal.'
        );
    }
    $motivo = 'Incorporado al listado de planificacion de '.$local['nombre'].'.';
    $mysqli->begin_transaction();
    try {
        $anterior = planificacionVinculoProfesionalLocal(
            $mysqli,
            $codProfesional,
            intval($local['cod_local']),
            true
        );
        if ($anterior && $anterior['estado'] === 'activo') {
            $mysqli->commit();
            return array(
                'id_vinculo' => intval($anterior['id_vinculo']),
                'ya_existia' => true
            );
        }
        if ($anterior) {
            $sql = "UPDATE planificacion_especialista_local
                SET estado='activo',motivo=?,version=version+1,fecha_edit=NOW(),
                    cod_usuarioFK_edit=?
                WHERE id_vinculo=? AND version=?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'siii',
                $motivo,
                $contexto['cod_usuario'],
                $anterior['id_vinculo'],
                $anterior['version']
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                planificacionLanzar('vinculo_no_guardado', 'El listado cambio. Actualice e intente nuevamente.');
            }
            $idVinculo = intval($anterior['id_vinculo']);
            $stmt->close();
            $accion = 'reactivar_profesional_sucursal';
        } else {
            $sql = "INSERT INTO planificacion_especialista_local
                (cod_profesionalFK,cod_localFK,estado,motivo,cod_usuarioFK_create)
                VALUES (?,?,'activo',?,?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'iisi',
                $codProfesional,
                $local['cod_local'],
                $motivo,
                $contexto['cod_usuario']
            );
            if (!$stmt->execute()) {
                $stmt->close();
                planificacionLanzar('vinculo_no_guardado', 'No se pudo incorporar el profesional.');
            }
            $idVinculo = intval($mysqli->insert_id);
            $stmt->close();
            $accion = 'agregar_profesional_sucursal';
        }
        $nuevo = planificacionVinculoProfesionalLocal(
            $mysqli,
            $codProfesional,
            intval($local['cod_local']),
            false
        );
        planificacionGuardarHistorial(
            $mysqli,
            'vinculo_local',
            $idVinculo,
            $accion,
            $anterior,
            $nuevo,
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_vinculo' => $idVinculo, 'ya_existia' => false);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionQuitarProfesionalSucursal($mysqli, $contexto, $entrada)
{
    planificacionRequerirGestionMultisucursal($contexto);
    if (!planificacionVinculosLocalesDisponibles($mysqli)
        || !planificacionEstructuraDisponible($mysqli)) {
        planificacionLanzar('vinculos_no_instalados', 'La funcion no esta disponible.');
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $codProfesional = planificacionEntero(
        isset($entrada['cod_profesional']) ? $entrada['cod_profesional'] : 0
    );
    $motivo = planificacionTextoEntrada(isset($entrada['motivo']) ? $entrada['motivo'] : '', 255);
    if ($codProfesional <= 0 || trim($motivo) === '') {
        planificacionLanzar('motivo_requerido', 'Indique el motivo para quitar al profesional.');
    }
    $mysqli->begin_transaction();
    try {
        $anterior = planificacionVinculoProfesionalLocal(
            $mysqli,
            $codProfesional,
            intval($local['cod_local']),
            true
        );
        if (!$anterior || $anterior['estado'] !== 'activo') {
            planificacionLanzar('vinculo_no_disponible', 'El profesional ya no pertenece al listado agregado.');
        }
        $sql = "UPDATE planificacion_especialista_local
            SET estado='inactivo',motivo=?,version=version+1,fecha_edit=NOW(),
                cod_usuarioFK_edit=?
            WHERE id_vinculo=? AND version=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param(
            'siii',
            $motivo,
            $contexto['cod_usuario'],
            $anterior['id_vinculo'],
            $anterior['version']
        );
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            planificacionLanzar('vinculo_no_guardado', 'El listado cambio. Actualice e intente nuevamente.');
        }
        $stmt->close();
        $nuevo = planificacionVinculoProfesionalLocal(
            $mysqli,
            $codProfesional,
            intval($local['cod_local']),
            false
        );
        planificacionGuardarHistorial(
            $mysqli,
            'vinculo_local',
            intval($anterior['id_vinculo']),
            'quitar_profesional_sucursal',
            $anterior,
            $nuevo,
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_vinculo' => intval($anterior['id_vinculo']));
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionConsultorios($mysqli, $codLocal)
{
    $sql = "SELECT id_consultorio,nombre,IFNULL(descripcion,'') AS descripcion,
            IFNULL(color,'') AS color
        FROM consultorios
        WHERE cod_localFk=? AND UPPER(TRIM(estado))='ACTIVO'
        ORDER BY id_consultorio";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('consulta_no_disponible', 'No se pudo obtener los consultorios.');
    }
    $stmt->bind_param('i', $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $consultorios = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $consultorios[] = array(
            'id_consultorio' => intval($fila['id_consultorio']),
            'nombre' => $fila['nombre'],
            'descripcion' => $fila['descripcion'],
            'color' => $fila['color']
        );
    }
    $stmt->close();
    return planificacionOrdenarYRotularConsultorios($consultorios);
}

/**
 * Proyecta la actividad diaria de Agenda sin exponer pacientes ni motivos.
 * La identidad proviene exclusivamente de un id_profesional valido informado
 * por Agenda. La interfaz puede representarla como asignacion visual cuando no
 * existe otra asignacion. Los turnos sin id_profesional conservan el conteo,
 * pero por si solos no ocupan la casilla ni crean otra identidad.
 */
function planificacionOcupacionesAgenda($mysqli, $codLocal, $desde, $hasta)
{
    if (!planificacionTablaExiste($mysqli, 'agenda')) {
        return array();
    }
    $sql = "SELECT a.fecha,a.id_consultorio,a.id_profesional,
            COUNT(*) AS cantidad_registros,
            MIN(a.hora_inicio) AS hora_desde,MAX(a.hora_fin) AS hora_hasta,
            COALESCE(NULLIF(p.nombre_persona,''),u.login,'') AS profesional,
            IFNULL(u.url,'') AS avatar
        FROM agenda a
        INNER JOIN consultorios c ON c.id_consultorio=a.id_consultorio
        LEFT JOIN usuario u ON u.cod_usuario=a.id_profesional
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE c.cod_localFk=?
          AND a.fecha BETWEEN ? AND ?
          AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'
        GROUP BY a.fecha,a.id_consultorio,a.id_profesional,
                 p.nombre_persona,u.login,u.url
        ORDER BY a.fecha,a.id_consultorio,a.id_profesional";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('iss', $codLocal, $desde, $hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $porCasilla = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $clave = $fila['fecha'].'|'.intval($fila['id_consultorio']);
        if (!isset($porCasilla[$clave])) {
            $porCasilla[$clave] = array(
                'clave' => $clave,
                'fecha' => $fila['fecha'],
                'id_consultorio' => intval($fila['id_consultorio']),
                'cantidad_registros' => 0,
                'cantidad_sin_profesional' => 0,
                'hora_desde' => null,
                'hora_hasta' => null,
                'profesionales' => array()
            );
        }
        $cantidad = intval($fila['cantidad_registros']);
        $porCasilla[$clave]['cantidad_registros'] += $cantidad;
        if ($fila['hora_desde'] !== null
            && ($porCasilla[$clave]['hora_desde'] === null
                || strcmp($fila['hora_desde'], $porCasilla[$clave]['hora_desde']) < 0)) {
            $porCasilla[$clave]['hora_desde'] = $fila['hora_desde'];
        }
        if ($fila['hora_hasta'] !== null
            && ($porCasilla[$clave]['hora_hasta'] === null
                || strcmp($fila['hora_hasta'], $porCasilla[$clave]['hora_hasta']) > 0)) {
            $porCasilla[$clave]['hora_hasta'] = $fila['hora_hasta'];
        }
        $codProfesional = intval($fila['id_profesional']);
        if ($codProfesional <= 0) {
            $porCasilla[$clave]['cantidad_sin_profesional'] += $cantidad;
            continue;
        }
        $porCasilla[$clave]['profesionales'][] = array(
            'cod_profesional' => $codProfesional,
            'nombre' => $fila['profesional'],
            'avatar' => $fila['avatar'],
            'cantidad_registros' => $cantidad,
            'hora_desde' => $fila['hora_desde'],
            'hora_hasta' => $fila['hora_hasta']
        );
    }
    $stmt->close();

    $ocupaciones = array();
    foreach ($porCasilla as $ocupacion) {
        $cantidadProfesionales = count($ocupacion['profesionales']);
        $ocupacion['cantidad_profesionales'] = $cantidadProfesionales;
        $ocupacion['doctor_sin_identificar'] = $ocupacion['cantidad_sin_profesional'] > 0;
        $ocupacion['bloquea_casilla'] = $cantidadProfesionales > 0;
        $ocupacion['estado_ocupacion'] = $cantidadProfesionales > 1
            ? 'conflicto_varios_doctores'
            : ($cantidadProfesionales === 1
                ? 'doctor_identificado'
                : 'actividad_sin_profesional_planificado');
        $ocupaciones[] = $ocupacion;
    }
    return $ocupaciones;
}

function planificacionFeriados($mysqli, $codLocal, $desde, $hasta)
{
    $feriados = array();
    if (!planificacionTablaExiste($mysqli, 'dias_feriados')) {
        return $feriados;
    }
    $sql = "SELECT id,fecha,IFNULL(descripcion,'Feriado') AS descripcion,cod_localFK
        FROM dias_feriados
        WHERE estado='activo'
          AND fecha BETWEEN ? AND ?
          AND (cod_localFK IS NULL OR cod_localFK=?)
        ORDER BY fecha";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return $feriados;
    }
    $stmt->bind_param('ssi', $desde, $hasta, $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $feriados[] = array(
            'id' => intval($fila['id']),
            'fecha' => $fila['fecha'],
            'descripcion' => $fila['descripcion'],
            'cod_local' => $fila['cod_localFK'] === null ? null : intval($fila['cod_localFK'])
        );
    }
    $stmt->close();
    return $feriados;
}

function planificacionHorarios($mysqli, $contexto, $codLocal)
{
    $sql = "SELECT hu.id,hu.cod_usuarioFK,hu.dia_semana,hu.hora_entrada,
            hu.hora_salida,hu.vigente_desde,hu.vigente_hasta
        FROM horario_usuario hu
        INNER JOIN usuario u ON u.cod_usuario=hu.cod_usuarioFK
        WHERE hu.cod_localFK=?
          AND UPPER(IFNULL(hu.estado_horario,'ACTIVO'))='ACTIVO'
          AND UPPER(TRIM(u.estado))='ACTIVO'
          AND UPPER(TRIM(u.tipo))='DOCTOR'";
    if ($contexto['solo_propio']) {
        $sql .= " AND hu.cod_usuarioFK=".intval($contexto['cod_usuario']);
    }
    $sql .= " ORDER BY hu.cod_usuarioFK,FIELD(hu.dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'),hu.hora_entrada";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('i', $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $horarios = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $horarios[] = array(
            'id_horario' => intval($fila['id']),
            'cod_profesional' => intval($fila['cod_usuarioFK']),
            'dia_semana' => $fila['dia_semana'],
            'hora_entrada' => $fila['hora_entrada'],
            'hora_salida' => $fila['hora_salida'],
            'vigente_desde' => $fila['vigente_desde'],
            'vigente_hasta' => $fila['vigente_hasta']
        );
    }
    $stmt->close();
    return $horarios;
}

function planificacionDiasIterar($desde, $hasta)
{
    $dias = array();
    $actual = new DateTime($desde);
    $fin = new DateTime($hasta);
    while ($actual <= $fin) {
        $dias[] = $actual->format('Y-m-d');
        $actual->modify('+1 day');
    }
    return $dias;
}

function planificacionFilaAsignacion($fila, $origen, $clave)
{
    return array(
        'clave' => $clave,
        'id_asignacion' => isset($fila['id_asignacion']) ? intval($fila['id_asignacion']) : null,
        'id_regla' => isset($fila['id_regla']) && $fila['id_regla'] !== null
            ? intval($fila['id_regla']) : null,
        'cod_profesional' => intval($fila['cod_profesionalFK']),
        'profesional' => $fila['profesional'],
        'avatar' => isset($fila['avatar']) ? $fila['avatar'] : '',
        'especialidad' => isset($fila['especialidad']) ? $fila['especialidad'] : '',
        'cod_local' => intval($fila['cod_localFK']),
        'id_consultorio' => intval($fila['id_consultorioFK']),
        'consultorio' => $fila['consultorio'],
        'fecha' => isset($fila['fecha']) ? $fila['fecha'] : '',
        'id_horario' => isset($fila['id_horario_usuarioFK']) && $fila['id_horario_usuarioFK'] !== null
            ? intval($fila['id_horario_usuarioFK']) : null,
        'hora_entrada' => isset($fila['hora_entrada']) ? $fila['hora_entrada'] : null,
        'hora_salida' => isset($fila['hora_salida']) ? $fila['hora_salida'] : null,
        'estado' => isset($fila['estado_asignacion'])
            ? $fila['estado_asignacion'] : $fila['estado'],
        'motivo' => isset($fila['motivo']) ? $fila['motivo'] : '',
        'version' => isset($fila['version']) ? intval($fila['version']) : null,
        'origen' => $origen,
        'solo_lectura' => $origen === 'legacy',
        'es_recurrente' => $origen === 'regla' || $origen === 'legacy'
    );
}

function planificacionListarAsignaciones($mysqli, $contexto, $codLocal, $desde, $hasta, $estructura)
{
    $asignaciones = array();
    $indicesRegla = array();
    $clavesOcupacion = array();
    $dias = planificacionDiasIterar($desde, $hasta);
    $perfilJoin = $estructura
        ? "LEFT JOIN planificacion_especialista_perfil pep ON pep.cod_usuarioFK=u.cod_usuario"
        : "";
    $especialidad = $estructura ? "IFNULL(pep.especialidad,'')" : "''";

    if ($estructura) {
        $sqlReglas = "SELECT r.id_regla,r.cod_profesionalFK,r.cod_localFK,
                r.id_consultorioFK,r.dia_semana,r.fecha_desde,r.fecha_hasta,
                r.id_horario_usuarioFK,r.estado_asignacion,r.motivo,r.version,
                COALESCE(NULLIF(p.nombre_persona,''),u.login) AS profesional,
                IFNULL(u.url,'') AS avatar,".$especialidad." AS especialidad,
                c.nombre AS consultorio,h.hora_entrada,h.hora_salida
            FROM planificacion_especialista_regla r
            INNER JOIN usuario u ON u.cod_usuario=r.cod_profesionalFK
            LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
            ".$perfilJoin."
            INNER JOIN consultorios c ON c.id_consultorio=r.id_consultorioFK
            LEFT JOIN horario_usuario h ON h.id=r.id_horario_usuarioFK
            WHERE r.cod_localFK=? AND r.estado='activo'
              AND r.fecha_desde<=?
              AND (r.fecha_hasta IS NULL OR r.fecha_hasta>=?)";
        if ($contexto['solo_propio']) {
            $sqlReglas .= " AND r.cod_profesionalFK=".intval($contexto['cod_usuario']);
        }
        $stmt = $mysqli->prepare($sqlReglas);
        if ($stmt) {
            $stmt->bind_param('iss', $codLocal, $hasta, $desde);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($resultado && ($regla = $resultado->fetch_assoc())) {
                foreach ($dias as $fecha) {
                    if ($fecha < $regla['fecha_desde']
                        || ($regla['fecha_hasta'] !== null && $fecha > $regla['fecha_hasta'])
                        || intval(date('N', strtotime($fecha))) !== intval($regla['dia_semana'])) {
                        continue;
                    }
                    $regla['fecha'] = $fecha;
                    $clave = 'regla-'.intval($regla['id_regla']).'-'.$fecha;
                    $indicesRegla[intval($regla['id_regla']).'|'.$fecha] = count($asignaciones);
                    $item = planificacionFilaAsignacion($regla, 'regla', $clave);
                    $asignaciones[] = $item;
                    $clavesOcupacion[$item['cod_profesional'].'|'.$fecha.'|'.$item['id_consultorio']] = true;
                }
            }
            $stmt->close();
        }

        $sqlAsignaciones = "SELECT a.id_asignacion,a.id_reglaFK AS id_regla,
                a.cod_profesionalFK,a.cod_localFK,a.id_consultorioFK,a.fecha,
                a.id_horario_usuarioFK,a.estado,a.motivo,a.version,
                COALESCE(NULLIF(p.nombre_persona,''),u.login) AS profesional,
                IFNULL(u.url,'') AS avatar,".$especialidad." AS especialidad,
                c.nombre AS consultorio,h.hora_entrada,h.hora_salida
            FROM planificacion_especialista_asignacion a
            INNER JOIN usuario u ON u.cod_usuario=a.cod_profesionalFK
            LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
            ".$perfilJoin."
            INNER JOIN consultorios c ON c.id_consultorio=a.id_consultorioFK
            LEFT JOIN horario_usuario h ON h.id=a.id_horario_usuarioFK
            WHERE a.cod_localFK=? AND a.fecha BETWEEN ? AND ?";
        if ($contexto['solo_propio']) {
            $sqlAsignaciones .= " AND a.cod_profesionalFK=".intval($contexto['cod_usuario']);
        }
        $sqlAsignaciones .= " ORDER BY a.fecha,a.id_consultorioFK,a.id_asignacion";
        $stmt = $mysqli->prepare($sqlAsignaciones);
        if ($stmt) {
            $stmt->bind_param('iss', $codLocal, $desde, $hasta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                $claveRegla = $fila['id_regla'] !== null
                    ? intval($fila['id_regla']).'|'.$fila['fecha'] : '';
                if ($fila['estado'] !== 'propuesta'
                    && $claveRegla !== '' && isset($indicesRegla[$claveRegla])) {
                    $indice = $indicesRegla[$claveRegla];
                    if (isset($asignaciones[$indice])) {
                        $anterior = $asignaciones[$indice];
                        unset($clavesOcupacion[
                            $anterior['cod_profesional'].'|'.$anterior['fecha'].'|'.$anterior['id_consultorio']
                        ]);
                        $asignaciones[$indice] = null;
                    }
                }
                if ($fila['estado'] === 'anulada') {
                    continue;
                }
                $item = planificacionFilaAsignacion(
                    $fila,
                    'asignacion',
                    'asignacion-'.intval($fila['id_asignacion'])
                );
                $asignaciones[] = $item;
                $clavesOcupacion[$item['cod_profesional'].'|'.$item['fecha'].'|'.$item['id_consultorio']] = true;
            }
            $stmt->close();
        }
    }

    if (planificacionTablaExiste($mysqli, 'consultorio_doctor_asignacion')) {
        $sqlLegacy = "SELECT cda.id_asignacion,hu.cod_usuarioFK AS cod_profesionalFK,
                hu.cod_localFK,cda.id_consultorio AS id_consultorioFK,
                hu.id AS id_horario_usuarioFK,hu.dia_semana,
                hu.hora_entrada,hu.hora_salida,hu.vigente_desde,hu.vigente_hasta,
                'confirmada' AS estado,'' AS motivo,
                COALESCE(NULLIF(p.nombre_persona,''),u.login) AS profesional,
                IFNULL(u.url,'') AS avatar,".$especialidad." AS especialidad,
                c.nombre AS consultorio
            FROM consultorio_doctor_asignacion cda
            INNER JOIN horario_usuario hu ON hu.id=cda.id_horario_usuario
            INNER JOIN consultorios c ON c.id_consultorio=cda.id_consultorio
            INNER JOIN usuario u ON u.cod_usuario=hu.cod_usuarioFK
            LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
            ".$perfilJoin."
            WHERE cda.estado='activo'
              AND hu.cod_localFK=?
              AND c.cod_localFk=?
              AND UPPER(IFNULL(hu.estado_horario,'ACTIVO'))='ACTIVO'";
        if ($contexto['solo_propio']) {
            $sqlLegacy .= " AND hu.cod_usuarioFK=".intval($contexto['cod_usuario']);
        }
        $stmt = $mysqli->prepare($sqlLegacy);
        if ($stmt) {
            $stmt->bind_param('ii', $codLocal, $codLocal);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $numeroDia = array(
                'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4,
                'viernes' => 5, 'sabado' => 6, 'domingo' => 7
            );
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                $diaNumero = isset($numeroDia[strtolower($fila['dia_semana'])])
                    ? $numeroDia[strtolower($fila['dia_semana'])] : 0;
                foreach ($dias as $fecha) {
                    if ($diaNumero !== intval(date('N', strtotime($fecha)))
                        || ($fila['vigente_desde'] && $fecha < $fila['vigente_desde'])
                        || ($fila['vigente_hasta'] && $fecha > $fila['vigente_hasta'])) {
                        continue;
                    }
                    $ocupacion = intval($fila['cod_profesionalFK']).'|'.$fecha.'|'.intval($fila['id_consultorioFK']);
                    if (isset($clavesOcupacion[$ocupacion])) {
                        continue;
                    }
                    $fila['fecha'] = $fecha;
                    $item = planificacionFilaAsignacion(
                        $fila,
                        'legacy',
                        'legacy-'.intval($fila['id_asignacion']).'-'.$fecha
                    );
                    $asignaciones[] = $item;
                    $clavesOcupacion[$ocupacion] = true;
                }
            }
            $stmt->close();
        }
    }

    $salida = array();
    foreach ($asignaciones as $item) {
        if ($item !== null) {
            $salida[] = $item;
        }
    }
    return $salida;
}

function planificacionAgregarCompromisoExterno(
    &$mapa,
    $item,
    $origen,
    $mostrarDetalles
)
{
    $codProfesional = isset($item['cod_profesional'])
        ? intval($item['cod_profesional']) : 0;
    $fecha = isset($item['fecha']) ? (string)$item['fecha'] : '';
    $codLocal = isset($item['cod_local']) ? intval($item['cod_local']) : 0;
    if ($codProfesional <= 0 || $fecha === '' || $codLocal <= 0) {
        return;
    }
    $claveLocal = $mostrarDetalles ? $codLocal : 0;
    $clave = $codProfesional.'|'.$fecha.'|'.$claveLocal;
    if (!isset($mapa[$clave])) {
        $mapa[$clave] = array(
            'clave' => 'compromiso-'.$codProfesional.'-'.$fecha.'-'.$claveLocal,
            'cod_profesional' => $codProfesional,
            'fecha' => $fecha,
            'cod_local' => $claveLocal,
            'nombre_local' => $mostrarDetalles
                ? (isset($item['nombre_local']) ? $item['nombre_local'] : '')
                : 'Otra sucursal',
            'consultorios' => array(),
            'hora_desde' => null,
            'hora_hasta' => null,
            'bloquea_dia' => false,
            'detalles_visibles' => $mostrarDetalles,
            'origenes' => array(),
            'cantidad_agenda' => 0
        );
    }
    $horaEntrada = isset($item['hora_entrada']) ? $item['hora_entrada'] : null;
    $horaSalida = isset($item['hora_salida']) ? $item['hora_salida'] : null;
    if (!$horaEntrada || !$horaSalida) {
        $mapa[$clave]['bloquea_dia'] = true;
    } else {
        if ($mapa[$clave]['hora_desde'] === null
            || strcmp($horaEntrada, $mapa[$clave]['hora_desde']) < 0) {
            $mapa[$clave]['hora_desde'] = $horaEntrada;
        }
        if ($mapa[$clave]['hora_hasta'] === null
            || strcmp($horaSalida, $mapa[$clave]['hora_hasta']) > 0) {
            $mapa[$clave]['hora_hasta'] = $horaSalida;
        }
    }
    if ($mostrarDetalles && !empty($item['consultorio'])) {
        $mapa[$clave]['consultorios'][(string)$item['consultorio']] = true;
    }
    $mapa[$clave]['origenes'][$origen] = true;
    if ($origen === 'agenda') {
        $mapa[$clave]['cantidad_agenda'] += isset($item['cantidad_agenda'])
            ? intval($item['cantidad_agenda']) : 0;
    }
}

/**
 * Resume compromisos del listado visible que pertenecen a otra sucursal.
 * No proyecta pacientes, tratamientos ni motivos. Los detalles de la sede se
 * muestran solamente con el permiso transversal ya utilizado por el modulo.
 */
function planificacionCompromisosOtrasSucursales(
    $mysqli,
    $contexto,
    $codLocalActual,
    $desde,
    $hasta,
    $estructura,
    $profesionales
)
{
    $permitidos = array();
    foreach ($profesionales as $profesional) {
        $codProfesional = isset($profesional['cod_profesional'])
            ? intval($profesional['cod_profesional']) : 0;
        if ($codProfesional > 0) {
            $permitidos[$codProfesional] = true;
        }
    }
    if (count($permitidos) === 0) {
        return array();
    }
    $mostrarDetalles = !empty($contexto['permisos']['todas_sucursales']);
    $locales = array();
    $stmt = $mysqli->prepare(
        "SELECT cod_local,Nombre
         FROM local
         WHERE UPPER(TRIM(estado))='ACTIVO' AND cod_local<>?
         ORDER BY Nombre"
    );
    if ($stmt) {
        $stmt->bind_param('i', $codLocalActual);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($resultado && ($fila = $resultado->fetch_assoc())) {
            $locales[] = array(
                'cod_local' => intval($fila['cod_local']),
                'nombre' => $fila['Nombre']
            );
        }
        $stmt->close();
    }

    $mapa = array();
    foreach ($locales as $local) {
        $fechasFeriadas = array();
        foreach (planificacionFeriados(
            $mysqli,
            $local['cod_local'],
            $desde,
            $hasta
        ) as $feriado) {
            $fechasFeriadas[$feriado['fecha']] = true;
        }
        $asignaciones = planificacionListarAsignaciones(
            $mysqli,
            $contexto,
            $local['cod_local'],
            $desde,
            $hasta,
            $estructura
        );
        foreach ($asignaciones as $asignacion) {
            if (isset($asignacion['estado']) && $asignacion['estado'] === 'propuesta') {
                continue;
            }
            if (isset($fechasFeriadas[$asignacion['fecha']])
                && ($asignacion['origen'] === 'regla'
                    || $asignacion['origen'] === 'legacy')) {
                continue;
            }
            if (!isset($permitidos[intval($asignacion['cod_profesional'])])) {
                continue;
            }
            $asignacion['nombre_local'] = $local['nombre'];
            planificacionAgregarCompromisoExterno(
                $mapa,
                $asignacion,
                'planificacion',
                $mostrarDetalles
            );
        }
    }

    if (planificacionTablaExiste($mysqli, 'agenda')) {
        $ids = array_keys($permitidos);
        $idsSql = implode(',', array_map('intval', $ids));
        $sqlAgenda = "SELECT a.fecha,a.id_profesional AS cod_profesional,
                c.cod_localFk AS cod_local,IFNULL(l.Nombre,'') AS nombre_local,
                c.nombre AS consultorio,COUNT(*) AS cantidad_agenda,
                MIN(a.hora_inicio) AS hora_entrada,MAX(a.hora_fin) AS hora_salida
            FROM agenda a
            INNER JOIN consultorios c ON c.id_consultorio=a.id_consultorio
            LEFT JOIN local l ON l.cod_local=c.cod_localFk
            WHERE a.fecha BETWEEN ? AND ?
              AND c.cod_localFk<>?
              AND a.id_profesional IN (".$idsSql.")
              AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'
            GROUP BY a.fecha,a.id_profesional,c.cod_localFk,l.Nombre,c.nombre
            ORDER BY a.fecha,a.id_profesional,c.cod_localFk,c.nombre";
        $stmt = $mysqli->prepare($sqlAgenda);
        if ($stmt) {
            $stmt->bind_param('ssi', $desde, $hasta, $codLocalActual);
            $stmt->execute();
            $resultado = $stmt->get_result();
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                planificacionAgregarCompromisoExterno(
                    $mapa,
                    $fila,
                    'agenda',
                    $mostrarDetalles
                );
            }
            $stmt->close();
        }
    }

    $compromisos = array();
    foreach ($mapa as $compromiso) {
        if ($compromiso['bloquea_dia']) {
            $compromiso['hora_desde'] = null;
            $compromiso['hora_hasta'] = null;
        }
        $compromiso['consultorios'] = array_keys($compromiso['consultorios']);
        sort($compromiso['consultorios']);
        $compromiso['origenes'] = array_keys($compromiso['origenes']);
        sort($compromiso['origenes']);
        $compromisos[] = $compromiso;
    }
    usort($compromisos, function ($a, $b) {
        if ($a['fecha'] !== $b['fecha']) {
            return strcmp($a['fecha'], $b['fecha']);
        }
        if ($a['cod_profesional'] !== $b['cod_profesional']) {
            return $a['cod_profesional'] - $b['cod_profesional'];
        }
        return $a['cod_local'] - $b['cod_local'];
    });
    return $compromisos;
}

function planificacionAdvertenciasLegacy($mysqli, $codLocal)
{
    $total = 0;
    $sql = "SELECT COUNT(*) FROM consultorios
        WHERE cod_localFk=? AND estado='ACTIVO' AND cod_doctorFK IS NOT NULL";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $codLocal);
        if ($stmt->execute()) {
            $stmt->bind_result($total);
            $stmt->fetch();
        }
        $stmt->close();
    }
    return intval($total);
}

function planificacionMinutosHora($hora)
{
    if (!$hora || !preg_match('/^(\d{1,2}):(\d{2})/', (string)$hora, $partes)) {
        return null;
    }
    return intval($partes[1]) * 60 + intval($partes[2]);
}

function planificacionAlertasTraslado($asignaciones, $umbralMinutos)
{
    $grupos = array();
    foreach ($asignaciones as $asignacion) {
        if (isset($asignacion['estado']) && $asignacion['estado'] === 'propuesta') {
            continue;
        }
        $clave = intval($asignacion['cod_profesional']).'|'.$asignacion['fecha'];
        if (!isset($grupos[$clave])) {
            $grupos[$clave] = array();
        }
        $grupos[$clave][] = $asignacion;
    }
    $alertas = array();
    foreach ($grupos as $items) {
        if (count($items) < 2) {
            continue;
        }
        usort($items, function ($a, $b) {
            $inicioA = planificacionMinutosHora($a['hora_entrada']);
            $inicioB = planificacionMinutosHora($b['hora_entrada']);
            if ($inicioA === null) {
                $inicioA = 9999;
            }
            if ($inicioB === null) {
                $inicioB = 9999;
            }
            if ($inicioA !== $inicioB) {
                return $inicioA - $inicioB;
            }
            return intval($a['cod_local']) - intval($b['cod_local']);
        });

        $programadas = array();
        $sinHorario = array();
        foreach ($items as $item) {
            if (planificacionMinutosHora($item['hora_entrada']) === null
                || planificacionMinutosHora($item['hora_salida']) === null) {
                $sinHorario[] = $item;
            } else {
                $programadas[] = $item;
            }
        }

        for ($i = 1; $i < count($programadas); $i++) {
            $actual = $programadas[$i];
            $inicioActual = planificacionMinutosHora($actual['hora_entrada']);
            $anterior = null;
            $diferencia = null;
            $mejorSuperposicion = null;
            $mejorTraslado = null;
            for ($j = 0; $j < $i; $j++) {
                $candidata = $programadas[$j];
                if (intval($candidata['cod_local']) === intval($actual['cod_local'])) {
                    continue;
                }
                $intervalo = $inicioActual - planificacionMinutosHora($candidata['hora_salida']);
                if ($intervalo < 0
                    && ($mejorSuperposicion === null || $intervalo < $mejorSuperposicion['diferencia'])) {
                    $mejorSuperposicion = array(
                        'asignacion' => $candidata,
                        'diferencia' => $intervalo
                    );
                } elseif ($intervalo >= 0 && $intervalo < intval($umbralMinutos)
                    && ($mejorTraslado === null || $intervalo < $mejorTraslado['diferencia'])) {
                    $mejorTraslado = array(
                        'asignacion' => $candidata,
                        'diferencia' => $intervalo
                    );
                }
            }
            if ($mejorSuperposicion !== null) {
                $anterior = $mejorSuperposicion['asignacion'];
                $diferencia = $mejorSuperposicion['diferencia'];
                $tipo = 'superposicion_sucursales';
                $mensaje = 'Superposicion entre sucursales';
            } elseif ($mejorTraslado !== null) {
                $anterior = $mejorTraslado['asignacion'];
                $diferencia = $mejorTraslado['diferencia'];
                $tipo = 'revisar_traslado';
                $mensaje = 'Revisar traslado';
            } else {
                continue;
            }
            $alertas[] = array(
                'tipo' => $tipo,
                'mensaje' => $mensaje,
                'fecha' => $actual['fecha'],
                'cod_profesional' => intval($actual['cod_profesional']),
                'clave_origen' => $anterior['clave'],
                'clave_destino' => $actual['clave'],
                'cod_local_origen' => intval($anterior['cod_local']),
                'cod_local_destino' => intval($actual['cod_local']),
                'minutos_disponibles' => $diferencia
            );
        }

        foreach ($sinHorario as $actual) {
            $referencia = null;
            foreach ($items as $posible) {
                if ($posible['clave'] !== $actual['clave']
                    && intval($posible['cod_local']) !== intval($actual['cod_local'])) {
                    $referencia = $posible;
                    break;
                }
            }
            if ($referencia === null) {
                continue;
            }
            $alertas[] = array(
                'tipo' => 'pendiente_traslado',
                'mensaje' => 'Pendiente definir traslado',
                'fecha' => $actual['fecha'],
                'cod_profesional' => intval($actual['cod_profesional']),
                'clave_origen' => $referencia['clave'],
                'clave_destino' => $actual['clave'],
                'cod_local_origen' => intval($referencia['cod_local']),
                'cod_local_destino' => intval($actual['cod_local']),
                'minutos_disponibles' => null
            );
        }
    }
    return $alertas;
}

function planificacionObtenerDatosMultisucursal($mysqli, $contexto, $entrada, $desde, $hasta, $estructura)
{
    if (empty($contexto['permisos']['todas_sucursales'])) {
        planificacionLanzar(
            'vista_multisucursal_no_autorizada',
            'No tiene permiso para consultar la planificacion de todas las sucursales.'
        );
    }
    $localActual = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $locales = planificacionLocalesConSiglas(planificacionLocales($mysqli, $contexto));
    $localesPorId = array();
    foreach ($locales as $local) {
        $localesPorId[intval($local['cod_local'])] = $local;
        if (intval($local['cod_local']) === intval($localActual['cod_local'])) {
            $localActual['sigla'] = $local['sigla'];
        }
    }

    $profesionalesMapa = array();
    $consultorios = array();
    $feriadosMapa = array();
    $asignaciones = array();
    $advertenciasLegacy = 0;

    foreach ($locales as $local) {
        $codLocal = intval($local['cod_local']);
        $profesionalesLocal = planificacionProfesionales($mysqli, $contexto, $codLocal, $estructura);
        foreach ($profesionalesLocal as $profesional) {
            $profesionalesMapa[intval($profesional['cod_profesional'])] = $profesional;
        }

        $consultoriosLocal = planificacionConsultorios($mysqli, $codLocal);
        foreach ($consultoriosLocal as $consultorio) {
            $consultorio['cod_local'] = $codLocal;
            $consultorio['nombre_local'] = $local['nombre'];
            $consultorio['sigla_local'] = $local['sigla'];
            $consultorios[] = $consultorio;
        }

        $feriadosLocal = planificacionFeriados($mysqli, $codLocal, $desde, $hasta);
        $fechasFeriadas = array();
        foreach ($feriadosLocal as $feriado) {
            $claveFeriado = intval($feriado['id']).'|'
                .($feriado['cod_local'] === null ? 'general' : intval($feriado['cod_local']));
            $feriado['nombre_local'] = $feriado['cod_local'] === null
                ? 'Todas las sucursales' : $local['nombre'];
            $feriado['sigla_local'] = $feriado['cod_local'] === null ? 'TG' : $local['sigla'];
            $feriadosMapa[$claveFeriado] = $feriado;
            $fechasFeriadas[$feriado['fecha']] = true;
        }

        $asignacionesLocal = planificacionListarAsignaciones(
            $mysqli,
            $contexto,
            $codLocal,
            $desde,
            $hasta,
            $estructura
        );
        foreach ($asignacionesLocal as $asignacion) {
            if (isset($fechasFeriadas[$asignacion['fecha']])
                && ($asignacion['origen'] === 'regla' || $asignacion['origen'] === 'legacy')) {
                continue;
            }
            $asignacion['nombre_local'] = $local['nombre'];
            $asignacion['sigla_local'] = $local['sigla'];
            $asignaciones[] = $asignacion;
            $idProfesional = intval($asignacion['cod_profesional']);
            if (!isset($profesionalesMapa[$idProfesional])) {
                $profesionalesMapa[$idProfesional] = array(
                    'cod_profesional' => $idProfesional,
                    'nombre' => $asignacion['profesional'],
                    'avatar' => $asignacion['avatar'],
                    'especialidad' => $asignacion['especialidad']
                );
            }
        }
        $advertenciasLegacy += planificacionAdvertenciasLegacy($mysqli, $codLocal);
    }

    $profesionales = array_values($profesionalesMapa);
    usort($profesionales, function ($a, $b) {
        return strcasecmp($a['nombre'], $b['nombre']);
    });
    usort($asignaciones, function ($a, $b) {
        if ($a['fecha'] !== $b['fecha']) {
            return strcmp($a['fecha'], $b['fecha']);
        }
        $horaA = $a['hora_entrada'] ? $a['hora_entrada'] : '99:99';
        $horaB = $b['hora_entrada'] ? $b['hora_entrada'] : '99:99';
        if ($horaA !== $horaB) {
            return strcmp($horaA, $horaB);
        }
        return intval($a['cod_local']) - intval($b['cod_local']);
    });

    $umbralTraslado = 60;
    return array(
        'estructura_instalada' => $estructura,
        'vinculos_instalados' => planificacionVinculosLocalesDisponibles($mysqli),
        'solo_consulta' => true,
        'modo_multisucursal' => true,
        'umbral_traslado_minutos' => $umbralTraslado,
        'contexto_usuario' => $contexto,
        'local_actual' => $localActual,
        'locales' => $locales,
        'profesionales' => $profesionales,
        'consultorios' => $consultorios,
        'horarios' => array(),
        'feriados' => array_values($feriadosMapa),
        'asignaciones' => $asignaciones,
        'ocupaciones_agenda' => array(),
        'alertas_traslado' => planificacionAlertasTraslado($asignaciones, $umbralTraslado),
        'advertencias' => array(
            'consultorios_con_doctor_estatico' => $advertenciasLegacy
        ),
        'rango' => array('fecha_desde' => $desde, 'fecha_hasta' => $hasta)
    );
}

function planificacionObtenerDatos($mysqli, $contexto, $entrada)
{
    $desde = isset($entrada['fecha_desde']) ? $entrada['fecha_desde'] : date('Y-m-01');
    $hasta = isset($entrada['fecha_hasta']) ? $entrada['fecha_hasta'] : date('Y-m-t');
    planificacionRango($desde, $hasta);
    $estructura = planificacionEstructuraDisponible($mysqli);
    if (isset($entrada['modo_multisucursal']) && (string)$entrada['modo_multisucursal'] === '1') {
        return planificacionObtenerDatosMultisucursal(
            $mysqli,
            $contexto,
            $entrada,
            $desde,
            $hasta,
            $estructura
        );
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $profesionales = planificacionProfesionales($mysqli, $contexto, $local['cod_local'], $estructura);
    $consultorios = planificacionConsultorios($mysqli, $local['cod_local']);
    $feriados = planificacionFeriados($mysqli, $local['cod_local'], $desde, $hasta);
    $fechasFeriadas = array();
    foreach ($feriados as $feriado) {
        $fechasFeriadas[$feriado['fecha']] = true;
    }
    $asignacionesSinFiltrar = planificacionListarAsignaciones(
        $mysqli,
        $contexto,
        $local['cod_local'],
        $desde,
        $hasta,
        $estructura
    );
    $asignaciones = array();
    foreach ($asignacionesSinFiltrar as $asignacion) {
        if (isset($fechasFeriadas[$asignacion['fecha']])
            && ($asignacion['origen'] === 'regla' || $asignacion['origen'] === 'legacy')) {
            continue;
        }
        $asignaciones[] = $asignacion;
    }
    return array(
        'estructura_instalada' => $estructura,
        'vinculos_instalados' => planificacionVinculosLocalesDisponibles($mysqli),
        'solo_consulta' => !$estructura
            || (!$contexto['permisos']['gestionar'] && !$contexto['permisos']['proponer']),
        'contexto_usuario' => $contexto,
        'local_actual' => $local,
        'locales' => planificacionLocales($mysqli, $contexto),
        'profesionales' => $profesionales,
        'consultorios' => $consultorios,
        'horarios' => planificacionHorarios($mysqli, $contexto, $local['cod_local']),
        'feriados' => $feriados,
        'asignaciones' => $asignaciones,
        'ocupaciones_agenda' => planificacionOcupacionesAgenda(
            $mysqli,
            $local['cod_local'],
            $desde,
            $hasta
        ),
        'compromisos_otras_sucursales' => planificacionCompromisosOtrasSucursales(
            $mysqli,
            $contexto,
            $local['cod_local'],
            $desde,
            $hasta,
            $estructura,
            $profesionales
        ),
        'advertencias' => array(
            'consultorios_con_doctor_estatico' => planificacionAdvertenciasLegacy($mysqli, $local['cod_local'])
        ),
        'rango' => array('fecha_desde' => $desde, 'fecha_hasta' => $hasta)
    );
}

function planificacionValidarFeriado($mysqli, $codLocal, $fecha)
{
    $feriado = planificacionFeriadoEnFecha($mysqli, $codLocal, $fecha);
    if ($feriado !== null) {
        planificacionLanzar(
            'fecha_no_operativa',
            'La fecha corresponde a un feriado: '.planificacionUtf8($feriado).'.'
        );
    }
}

function planificacionFeriadoEnFecha($mysqli, $codLocal, $fecha)
{
    if (!planificacionTablaExiste($mysqli, 'dias_feriados')) {
        return null;
    }
    $sql = "SELECT descripcion FROM dias_feriados
        WHERE fecha=? AND estado='activo'
          AND (cod_localFK IS NULL OR cod_localFK=?)
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('si', $fecha, $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $feriado = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $feriado ? $feriado['descripcion'] : null;
}

function planificacionValidarProfesional($mysqli, $codProfesional, $codLocal, $contexto)
{
    $codLocal = intval($codLocal);
    $vinculo = planificacionVinculosLocalesDisponibles($mysqli)
        ? "IF(EXISTS (
            SELECT 1 FROM planificacion_especialista_local pel
            WHERE pel.cod_profesionalFK=u.cod_usuario
              AND pel.cod_localFK=".$codLocal."
              AND pel.estado='activo'
        ),1,0)"
        : "0";
    $sql = "SELECT u.cod_usuario,u.cod_localFK,
            IF(u.cod_localFK=".$codLocal.",1,0) AS pertenece_local,
            IF(EXISTS (
                SELECT 1 FROM horario_usuario hu
                WHERE hu.cod_usuarioFK=u.cod_usuario
                  AND hu.cod_localFK=".$codLocal."
                  AND UPPER(IFNULL(hu.estado_horario,'ACTIVO'))='ACTIVO'
            ),1,0) AS tiene_horario_local,
            ".$vinculo." AS vinculada_planificacion
        FROM usuario u
        WHERE u.cod_usuario=?
          AND UPPER(TRIM(u.estado))='ACTIVO'
          AND UPPER(TRIM(u.tipo))='DOCTOR'
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('profesional_no_disponible', 'No se pudo validar el profesional.');
    }
    $stmt->bind_param('i', $codProfesional);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$fila
        || (intval($fila['pertenece_local']) !== 1
            && intval($fila['tiene_horario_local']) !== 1
            && intval($fila['vinculada_planificacion']) !== 1)) {
        planificacionLanzar('profesional_no_disponible', 'El profesional no esta activo en la sucursal.');
    }
    if (intval($fila['vinculada_planificacion']) === 1
        && intval($fila['pertenece_local']) !== 1
        && intval($fila['tiene_horario_local']) !== 1) {
        planificacionRequerirGestionMultisucursal($contexto);
    }
    return array(
        'cod_local_base' => intval($fila['cod_localFK']),
        'pertenece_local' => intval($fila['pertenece_local']) === 1,
        'tiene_horario_local' => intval($fila['tiene_horario_local']) === 1,
        'vinculada_planificacion' => intval($fila['vinculada_planificacion']) === 1,
        'es_multisucursal' => intval($fila['cod_localFK']) !== $codLocal,
        'es_vinculo_externo' => intval($fila['vinculada_planificacion']) === 1
            && intval($fila['pertenece_local']) !== 1
            && intval($fila['tiene_horario_local']) !== 1
    );
}

function planificacionValidarConsultorio($mysqli, $idConsultorio, $codLocal)
{
    $sql = "SELECT id_consultorio FROM consultorios
        WHERE id_consultorio=? AND cod_localFk=? AND estado='ACTIVO' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('consultorio_no_disponible', 'No se pudo validar el consultorio.');
    }
    $stmt->bind_param('ii', $idConsultorio, $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $valido = $resultado && $resultado->num_rows > 0;
    $stmt->close();
    if (!$valido) {
        planificacionLanzar('consultorio_no_disponible', 'El consultorio no esta activo en la sucursal.');
    }
}

function planificacionHorario($mysqli, $idHorario, $codProfesional, $codLocal, $fecha)
{
    if ($idHorario <= 0) {
        return null;
    }
    $sql = "SELECT id,cod_usuarioFK,cod_localFK,dia_semana,hora_entrada,hora_salida,
            vigente_desde,vigente_hasta
        FROM horario_usuario
        WHERE id=? AND cod_usuarioFK=? AND cod_localFK=?
          AND UPPER(IFNULL(estado_horario,'ACTIVO'))='ACTIVO'
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('horario_no_disponible', 'No se pudo validar el horario.');
    }
    $stmt->bind_param('iii', $idHorario, $codProfesional, $codLocal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $horario = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$horario) {
        planificacionLanzar('horario_no_disponible', 'El horario seleccionado no pertenece al profesional y la sucursal.');
    }
    $dias = array(1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo');
    $dia = intval(date('N', strtotime($fecha)));
    if (!isset($dias[$dia]) || strtolower($horario['dia_semana']) !== $dias[$dia]
        || ($horario['vigente_desde'] && $fecha < $horario['vigente_desde'])
        || ($horario['vigente_hasta'] && $fecha > $horario['vigente_hasta'])) {
        planificacionLanzar('horario_fuera_de_vigencia', 'El horario no esta vigente para la fecha seleccionada.');
    }
    return $horario;
}

function planificacionIntervalosSeSuperponen($entradaA, $salidaA, $entradaB, $salidaB)
{
    if (!$entradaA || !$salidaA || !$entradaB || !$salidaB) {
        return true;
    }
    return strcmp($entradaA, $salidaB) < 0 && strcmp($entradaB, $salidaA) < 0;
}

function planificacionConflictos(
    $mysqli,
    $contexto,
    $codLocal,
    $fecha,
    $codProfesional,
    $idConsultorio,
    $horario,
    $excluirId,
    $todasSucursales
)
{
    $estructura = planificacionEstructuraDisponible($mysqli);
    $locales = array(array('cod_local' => intval($codLocal), 'nombre' => 'Sucursal seleccionada'));
    if ($todasSucursales) {
        $locales = array();
        $resultadoLocales = $mysqli->query(
            "SELECT cod_local,Nombre
             FROM local
             WHERE UPPER(TRIM(estado))='ACTIVO'
             ORDER BY Nombre"
        );
        while ($resultadoLocales && ($filaLocal = $resultadoLocales->fetch_assoc())) {
            $locales[] = array(
                'cod_local' => intval($filaLocal['cod_local']),
                'nombre' => $filaLocal['Nombre']
            );
        }
    }
    $conflictos = array();
    foreach ($locales as $local) {
        $codLocalConsulta = intval($local['cod_local']);
        $existentes = planificacionListarAsignaciones(
            $mysqli,
            $contexto,
            $codLocalConsulta,
            $fecha,
            $fecha,
            $estructura
        );
        foreach ($existentes as $existente) {
            if (isset($existente['estado']) && $existente['estado'] === 'propuesta') {
                continue;
            }
            if ($excluirId > 0 && intval($existente['id_asignacion']) === $excluirId
                && $existente['origen'] === 'asignacion') {
                continue;
            }
            $mismoProfesional = intval($existente['cod_profesional']) === $codProfesional;
            $mismoConsultorio = $codLocalConsulta === intval($codLocal)
                && intval($existente['id_consultorio']) === $idConsultorio;
            if (!$mismoProfesional && !$mismoConsultorio) {
                continue;
            }
            /* El consultorio representa una unica casilla diaria. Aunque dos
               horarios no se crucen, no admite dos doctores en la misma fecha. */
            $seSuperpone = $mismoConsultorio || planificacionIntervalosSeSuperponen(
                $horario ? $horario['hora_entrada'] : null,
                $horario ? $horario['hora_salida'] : null,
                $existente['hora_entrada'],
                $existente['hora_salida']
            );
            if (!$seSuperpone) {
                continue;
            }
            $puedeVerDetalleLocal = !empty($contexto['permisos']['todas_sucursales'])
                || $codLocalConsulta === intval($codLocal);
            $conflictos[] = array(
                'tipo' => $mismoConsultorio ? 'consultorio' : 'profesional',
                'profesional' => $existente['profesional'],
                'consultorio' => $puedeVerDetalleLocal ? $existente['consultorio'] : '',
                'cod_local' => $puedeVerDetalleLocal ? $codLocalConsulta : 0,
                'nombre_local' => $puedeVerDetalleLocal && isset($local['nombre'])
                    ? $local['nombre'] : '',
                'hora_entrada' => $existente['hora_entrada'],
                'hora_salida' => $existente['hora_salida'],
                'origen' => $existente['origen']
            );
        }
    }
    return $conflictos;
}

function planificacionRestriccionAusencia($mysqli, $codProfesional, $fecha, $horario)
{
    if (!planificacionTablaExiste($mysqli, 'solicitudes_ausencia')) {
        return null;
    }
    $sql = "SELECT tipo,hora_desde,hora_hasta
        FROM solicitudes_ausencia
        WHERE cod_usuarioFK=?
          AND estado='aprobado'
          AND ? BETWEEN fecha_desde AND fecha_hasta
          AND tipo IN ('reposo_medico','permiso','vacaciones')
        ORDER BY FIELD(tipo,'reposo_medico','vacaciones','permiso'),id
        LIMIT 10";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('is', $codProfesional, $fecha);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $restriccion = null;
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $aplica = $fila['tipo'] !== 'permiso'
            || !$fila['hora_desde']
            || !$fila['hora_hasta']
            || !$horario
            || planificacionIntervalosSeSuperponen(
                $horario['hora_entrada'],
                $horario['hora_salida'],
                $fila['hora_desde'],
                $fila['hora_hasta']
            );
        if ($aplica) {
            $restriccion = array(
                'tipo' => $fila['tipo'],
                'mensaje' => $fila['tipo'] === 'reposo_medico'
                    ? 'El profesional tiene un reposo aprobado para la fecha.'
                    : ($fila['tipo'] === 'vacaciones'
                        ? 'El profesional se encuentra de vacaciones en la fecha.'
                        : 'El profesional tiene un permiso aprobado para la fecha.')
            );
            break;
        }
    }
    $stmt->close();
    return $restriccion;
}

function planificacionConflictosAgenda(
    $mysqli,
    $codLocal,
    $fecha,
    $codProfesional,
    $idConsultorio,
    $horario,
    $puedeVerTodasSucursales
)
{
    if (!planificacionTablaExiste($mysqli, 'agenda')) {
        return array();
    }
    $sql = "SELECT a.id_agenda,a.id_profesional,a.id_consultorio,
            a.hora_inicio,a.hora_fin,c.cod_localFk,c.nombre AS consultorio,
            IFNULL(l.Nombre,'') AS nombre_local,
            COALESCE(NULLIF(p.nombre_persona,''),u.login,'') AS profesional
        FROM agenda a
        INNER JOIN consultorios c ON c.id_consultorio=a.id_consultorio
        LEFT JOIN local l ON l.cod_local=c.cod_localFk
        LEFT JOIN usuario u ON u.cod_usuario=a.id_profesional
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE a.fecha=?
          AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'
          AND a.id_profesional IS NOT NULL
          AND a.id_profesional>0
          AND (a.id_profesional=? OR a.id_consultorio=?)
        ORDER BY a.hora_inicio,a.id_agenda";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('sii', $fecha, $codProfesional, $idConsultorio);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $conflictos = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $mismoProfesional = intval($fila['id_profesional']) === $codProfesional;
        $mismoConsultorio = intval($fila['id_consultorio']) === $idConsultorio;
        if ($mismoProfesional && $mismoConsultorio) {
            continue;
        }
        /* Agenda conserva turnos, pero no define la asignacion diaria del
           consultorio. Un doctor distinto en la misma casilla se informa en
           la interfaz para revision y no impide fijar la fuente oficial. */
        if ($mismoConsultorio) {
            continue;
        }
        $seSuperpone = planificacionIntervalosSeSuperponen(
            $horario ? $horario['hora_entrada'] : null,
            $horario ? $horario['hora_salida'] : null,
            $fila['hora_inicio'],
            $fila['hora_fin']
        );
        if (!$seSuperpone) {
            continue;
        }
        $puedeVerDetalleLocal = $puedeVerTodasSucursales
            || intval($fila['cod_localFk']) === intval($codLocal);
        $conflictos[] = array(
            'tipo' => 'agenda_profesional',
            'cod_local' => $puedeVerDetalleLocal ? intval($fila['cod_localFk']) : 0,
            'nombre_local' => $puedeVerDetalleLocal ? $fila['nombre_local'] : '',
            'profesional' => $puedeVerDetalleLocal ? $fila['profesional'] : '',
            'consultorio' => $puedeVerDetalleLocal ? $fila['consultorio'] : '',
            'hora_entrada' => $fila['hora_inicio'],
            'hora_salida' => $fila['hora_fin'],
            'origen' => 'agenda'
        );
    }
    $stmt->close();
    return $conflictos;
}

function planificacionMensajeConflictos($conflictos, $fecha)
{
    foreach ($conflictos as $conflicto) {
        $tipo = isset($conflicto['tipo']) ? $conflicto['tipo'] : '';
        if ($tipo !== 'consultorio' && $tipo !== 'agenda_consultorio') {
            continue;
        }
        $profesional = trim(isset($conflicto['profesional']) ? $conflicto['profesional'] : '');
        $consultorio = trim(isset($conflicto['consultorio']) ? $conflicto['consultorio'] : '');
        if ($profesional !== '') {
            return ($consultorio !== '' ? $consultorio : 'El consultorio')
                .' ya esta ocupado por '.$profesional.' el '.$fecha.'.';
        }
        return ($consultorio !== '' ? $consultorio : 'El consultorio')
            .' tiene una ocupacion de Agenda que debe revisarse el '.$fecha.'.';
    }
    foreach ($conflictos as $conflicto) {
        $tipo = isset($conflicto['tipo']) ? $conflicto['tipo'] : '';
        if ($tipo !== 'profesional' && $tipo !== 'agenda_profesional') {
            continue;
        }
        return 'El doctor ya posee otra ocupacion superpuesta el '.$fecha.'.';
    }
    return 'La asignacion se superpone con otra ocupacion.';
}

function planificacionGuardarHistorial($mysqli, $entidad, $idEntidad, $accion, $anterior, $nuevo, $motivo, $actor)
{
    $anteriorJson = $anterior === null ? null : json_encode(planificacionUtf8($anterior));
    $nuevoJson = $nuevo === null ? null : json_encode(planificacionUtf8($nuevo));
    $sql = "INSERT INTO planificacion_especialista_historial
        (entidad,id_entidad,accion,datos_anteriores,datos_nuevos,motivo,cod_usuarioFK_create)
        VALUES (?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('historial_no_disponible', 'No se pudo registrar la trazabilidad del cambio.');
    }
    $stmt->bind_param(
        'sissssi',
        $entidad,
        $idEntidad,
        $accion,
        $anteriorJson,
        $nuevoJson,
        $motivo,
        $actor
    );
    if (!$stmt->execute()) {
        $stmt->close();
        planificacionLanzar('historial_no_disponible', 'No se pudo registrar la trazabilidad del cambio.');
    }
    $stmt->close();
}

function planificacionAsignacionPorId($mysqli, $idAsignacion)
{
    $sql = "SELECT * FROM planificacion_especialista_asignacion
        WHERE id_asignacion=? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $idAsignacion);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $fila;
}

function planificacionGuardarAsignacion($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli)) {
        planificacionLanzar('estructura_no_instalada', 'Aplique primero la migracion controlada del modulo.');
    }
    $puedeGestionar = $contexto['permisos']['gestionar'];
    $puedeProponer = $contexto['permisos']['proponer'];
    if (!$puedeGestionar && !$puedeProponer) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para crear asignaciones.');
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $fecha = isset($entrada['fecha']) ? (string)$entrada['fecha'] : '';
    if (!planificacionFechaValida($fecha)) {
        planificacionLanzar('fecha_invalida', 'Seleccione una fecha valida.');
    }
    $codProfesional = planificacionEntero(isset($entrada['cod_profesional']) ? $entrada['cod_profesional'] : 0);
    $idConsultorio = planificacionEntero(isset($entrada['id_consultorio']) ? $entrada['id_consultorio'] : 0);
    $idHorario = planificacionEntero(isset($entrada['id_horario']) ? $entrada['id_horario'] : 0);
    $idAsignacion = planificacionEntero(isset($entrada['id_asignacion']) ? $entrada['id_asignacion'] : 0);
    $versionEsperada = planificacionEntero(isset($entrada['version_esperada']) ? $entrada['version_esperada'] : 0);
    $motivo = planificacionTextoEntrada(isset($entrada['motivo']) ? $entrada['motivo'] : '', 255);
    if ($codProfesional <= 0 || $idConsultorio <= 0) {
        planificacionLanzar('datos_incompletos', 'Seleccione profesional y consultorio.');
    }
    if ($idAsignacion > 0 && !$puedeGestionar) {
        planificacionLanzar('accion_no_autorizada', 'Las propuestas no pueden modificar asignaciones existentes.');
    }
    planificacionValidarFeriado($mysqli, $local['cod_local'], $fecha);
    $validacionProfesional = planificacionValidarProfesional(
        $mysqli,
        $codProfesional,
        $local['cod_local'],
        $contexto
    );
    planificacionValidarConsultorio($mysqli, $idConsultorio, $local['cod_local']);
    $horario = planificacionHorario($mysqli, $idHorario, $codProfesional, $local['cod_local'], $fecha);
    $estado = $puedeGestionar
        ? ($horario ? 'confirmada' : 'pendiente_horario')
        : 'propuesta';
    $restriccion = planificacionRestriccionAusencia(
        $mysqli,
        $codProfesional,
        $fecha,
        $horario
    );
    if ($restriccion !== null) {
        planificacionLanzar(
            'profesional_con_restriccion',
            $restriccion['mensaje'],
            array('tipo' => $restriccion['tipo'], 'fecha' => $fecha)
        );
    }
    $conflictos = planificacionConflictos(
        $mysqli,
        $contexto,
        $local['cod_local'],
        $fecha,
        $codProfesional,
        $idConsultorio,
        $horario,
        $idAsignacion,
        true
    );
    $conflictos = array_merge(
        $conflictos,
        planificacionConflictosAgenda(
            $mysqli,
            $local['cod_local'],
            $fecha,
            $codProfesional,
            $idConsultorio,
            $horario,
            !empty($contexto['permisos']['todas_sucursales'])
        )
    );
    if ($puedeGestionar && count($conflictos) > 0) {
        planificacionLanzar(
            'conflicto_planificacion',
            planificacionMensajeConflictos($conflictos, $fecha),
            array('fecha' => $fecha, 'conflictos' => $conflictos)
        );
    }

    $mysqli->begin_transaction();
    try {
        if ($idAsignacion > 0) {
            $anterior = planificacionAsignacionPorId($mysqli, $idAsignacion);
            if (!$anterior || intval($anterior['cod_localFK']) !== intval($local['cod_local'])
                || $anterior['estado'] === 'anulada') {
                planificacionLanzar('asignacion_no_disponible', 'La asignacion ya no esta disponible.');
            }
            if ($versionEsperada <= 0 || intval($anterior['version']) !== $versionEsperada) {
                planificacionLanzar('version_desactualizada', 'La asignacion cambio. Actualice la vista antes de guardar.');
            }
            $sql = "UPDATE planificacion_especialista_asignacion
                SET cod_profesionalFK=?,id_consultorioFK=?,fecha=?,
                    id_horario_usuarioFK=NULLIF(?,0),estado=?,motivo=?,
                    version=version+1,fecha_edit=NOW(),cod_usuarioFK_edit=?
                WHERE id_asignacion=? AND version=?";
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                planificacionLanzar('asignacion_no_guardada', 'No se pudo preparar la actualizacion.');
            }
            $stmt->bind_param(
                'iisissiii',
                $codProfesional,
                $idConsultorio,
                $fecha,
                $idHorario,
                $estado,
                $motivo,
                $contexto['cod_usuario'],
                $idAsignacion,
                $versionEsperada
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                planificacionLanzar('version_desactualizada', 'La asignacion cambio. Actualice la vista.');
            }
            $stmt->close();
            $nuevo = planificacionAsignacionPorId($mysqli, $idAsignacion);
            planificacionGuardarHistorial(
                $mysqli,
                'asignacion',
                $idAsignacion,
                'mover_editar',
                $anterior,
                $nuevo,
                $motivo,
                $contexto['cod_usuario']
            );
            $mysqli->commit();
            return array('id_asignacion' => $idAsignacion, 'estado' => $estado, 'conflictos' => $conflictos);
        }

        $sql = "INSERT INTO planificacion_especialista_asignacion
            (cod_profesionalFK,cod_localFK,id_consultorioFK,fecha,
             id_horario_usuarioFK,tipo_origen,estado,motivo,cod_usuarioFK_create)
            VALUES (?,?,?,?,NULLIF(?,0),'puntual',?,?,?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            planificacionLanzar('asignacion_no_guardada', 'No se pudo preparar la asignacion.');
        }
        $stmt->bind_param(
            'iiisissi',
            $codProfesional,
            $local['cod_local'],
            $idConsultorio,
            $fecha,
            $idHorario,
            $estado,
            $motivo,
            $contexto['cod_usuario']
        );
        if (!$stmt->execute()) {
            $stmt->close();
            planificacionLanzar('asignacion_no_guardada', 'No se pudo guardar la asignacion.');
        }
        $nuevoId = intval($mysqli->insert_id);
        $stmt->close();
        $nuevo = planificacionAsignacionPorId($mysqli, $nuevoId);
        planificacionGuardarHistorial(
            $mysqli,
            'asignacion',
            $nuevoId,
            $estado === 'propuesta' ? 'proponer' : 'crear',
            null,
            $nuevo,
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_asignacion' => $nuevoId, 'estado' => $estado, 'conflictos' => $conflictos);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionGuardarRegla($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli)) {
        planificacionLanzar('estructura_no_instalada', 'Aplique primero la migracion controlada del modulo.');
    }
    if (!$contexto['permisos']['recurrencias']) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para gestionar recurrencias.');
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $codProfesional = planificacionEntero(isset($entrada['cod_profesional']) ? $entrada['cod_profesional'] : 0);
    $idConsultorio = planificacionEntero(isset($entrada['id_consultorio']) ? $entrada['id_consultorio'] : 0);
    $idHorario = planificacionEntero(isset($entrada['id_horario']) ? $entrada['id_horario'] : 0);
    $fechaDesde = isset($entrada['fecha_desde']) ? (string)$entrada['fecha_desde'] : '';
    $fechaHasta = isset($entrada['fecha_hasta']) ? (string)$entrada['fecha_hasta'] : '';
    $diaSemana = planificacionEntero(isset($entrada['dia_semana']) ? $entrada['dia_semana'] : 0);
    $motivo = planificacionTextoEntrada(isset($entrada['motivo']) ? $entrada['motivo'] : '', 255);
    if (!planificacionFechaValida($fechaDesde)
        || ($fechaHasta !== '' && !planificacionFechaValida($fechaHasta))
        || ($fechaHasta !== '' && $fechaHasta < $fechaDesde)
        || $diaSemana < 1 || $diaSemana > 7) {
        planificacionLanzar('recurrencia_invalida', 'Revise el dia y la vigencia de la recurrencia.');
    }
    if (intval(date('N', strtotime($fechaDesde))) !== $diaSemana) {
        planificacionLanzar('recurrencia_invalida', 'La fecha inicial debe coincidir con el dia semanal seleccionado.');
    }
    $validacionProfesional = planificacionValidarProfesional(
        $mysqli,
        $codProfesional,
        $local['cod_local'],
        $contexto
    );
    if (!empty($validacionProfesional['es_vinculo_externo'])) {
        planificacionLanzar(
            'recurrencia_no_disponible',
            'Los profesionales agregados desde otra sucursal se asignan por fecha.'
        );
    }
    planificacionValidarConsultorio($mysqli, $idConsultorio, $local['cod_local']);
    $horario = planificacionHorario($mysqli, $idHorario, $codProfesional, $local['cod_local'], $fechaDesde);
    $estado = $contexto['permisos']['gestionar']
        ? ($horario ? 'confirmada' : 'pendiente_horario')
        : 'propuesta';

    $controlHasta = $fechaHasta !== ''
        ? $fechaHasta : date('Y-m-d', strtotime($fechaDesde.' +180 days'));
    if (intval((new DateTime($fechaDesde))->diff(new DateTime($controlHasta))->format('%a')) > 366) {
        $controlHasta = date('Y-m-d', strtotime($fechaDesde.' +366 days'));
    }
    foreach (planificacionDiasIterar($fechaDesde, $controlHasta) as $fecha) {
        if (intval(date('N', strtotime($fecha))) !== $diaSemana) {
            continue;
        }
        if (planificacionFeriadoEnFecha($mysqli, $local['cod_local'], $fecha) !== null) {
            continue;
        }
        $restriccion = planificacionRestriccionAusencia(
            $mysqli,
            $codProfesional,
            $fecha,
            $horario
        );
        if ($restriccion !== null) {
            planificacionLanzar(
                'profesional_con_restriccion',
                $restriccion['mensaje'],
                array('tipo' => $restriccion['tipo'], 'fecha' => $fecha)
            );
        }
        $conflictos = planificacionConflictos(
            $mysqli,
            $contexto,
            $local['cod_local'],
            $fecha,
            $codProfesional,
            $idConsultorio,
            $horario,
            0,
            true
        );
        $conflictos = array_merge(
            $conflictos,
            planificacionConflictosAgenda(
                $mysqli,
                $local['cod_local'],
                $fecha,
                $codProfesional,
                $idConsultorio,
                $horario,
                !empty($contexto['permisos']['todas_sucursales'])
            )
        );
        if ($contexto['permisos']['gestionar'] && count($conflictos) > 0) {
            planificacionLanzar(
                'conflicto_planificacion',
                planificacionMensajeConflictos($conflictos, $fecha),
                array('fecha' => $fecha, 'conflictos' => $conflictos)
            );
        }
    }

    $mysqli->begin_transaction();
    try {
        $sql = "INSERT INTO planificacion_especialista_regla
            (cod_profesionalFK,cod_localFK,id_consultorioFK,dia_semana,
             fecha_desde,fecha_hasta,id_horario_usuarioFK,estado_asignacion,
             motivo,cod_usuarioFK_create)
            VALUES (?,?,?,?,?,NULLIF(?,''),NULLIF(?,0),?,?,?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            planificacionLanzar('regla_no_guardada', 'No se pudo preparar la recurrencia.');
        }
        $stmt->bind_param(
            'iiiississi',
            $codProfesional,
            $local['cod_local'],
            $idConsultorio,
            $diaSemana,
            $fechaDesde,
            $fechaHasta,
            $idHorario,
            $estado,
            $motivo,
            $contexto['cod_usuario']
        );
        if (!$stmt->execute()) {
            $stmt->close();
            planificacionLanzar('regla_no_guardada', 'No se pudo guardar la recurrencia.');
        }
        $idRegla = intval($mysqli->insert_id);
        $stmt->close();
        $nuevo = array(
            'id_regla' => $idRegla,
            'cod_profesionalFK' => $codProfesional,
            'cod_localFK' => $local['cod_local'],
            'id_consultorioFK' => $idConsultorio,
            'dia_semana' => $diaSemana,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta !== '' ? $fechaHasta : null,
            'id_horario_usuarioFK' => $idHorario > 0 ? $idHorario : null,
            'estado_asignacion' => $estado
        );
        planificacionGuardarHistorial(
            $mysqli,
            'regla',
            $idRegla,
            'crear_recurrencia',
            null,
            $nuevo,
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_regla' => $idRegla, 'estado' => $estado);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionAnularAsignacion($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli)) {
        planificacionLanzar('estructura_no_instalada', 'Aplique primero la migracion controlada del modulo.');
    }
    if (!$contexto['permisos']['gestionar']) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para anular asignaciones.');
    }
    $idAsignacion = planificacionEntero(isset($entrada['id_asignacion']) ? $entrada['id_asignacion'] : 0);
    $version = planificacionEntero(isset($entrada['version_esperada']) ? $entrada['version_esperada'] : 0);
    $motivo = planificacionTextoEntrada(isset($entrada['motivo']) ? $entrada['motivo'] : '', 255);
    if ($idAsignacion <= 0 || $version <= 0 || $motivo === '') {
        planificacionLanzar('datos_incompletos', 'Indique la asignacion, su version y el motivo.');
    }
    $anterior = planificacionAsignacionPorId($mysqli, $idAsignacion);
    if (!$anterior || $anterior['estado'] === 'anulada') {
        planificacionLanzar('asignacion_no_disponible', 'La asignacion ya no esta disponible.');
    }
    planificacionLocalAutorizado($mysqli, $contexto, $anterior['cod_localFK']);
    if (intval($anterior['version']) !== $version) {
        planificacionLanzar('version_desactualizada', 'La asignacion cambio. Actualice la vista.');
    }
    $mysqli->begin_transaction();
    try {
        $sql = "UPDATE planificacion_especialista_asignacion
            SET estado='anulada',motivo=?,version=version+1,
                fecha_edit=NOW(),cod_usuarioFK_edit=?
            WHERE id_asignacion=? AND version=? AND estado<>'anulada'";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('siii', $motivo, $contexto['cod_usuario'], $idAsignacion, $version);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            planificacionLanzar('version_desactualizada', 'La asignacion cambio. Actualice la vista.');
        }
        $stmt->close();
        $nuevo = planificacionAsignacionPorId($mysqli, $idAsignacion);
        planificacionGuardarHistorial(
            $mysqli,
            'asignacion',
            $idAsignacion,
            'anular',
            $anterior,
            $nuevo,
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_asignacion' => $idAsignacion);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionAnularRegla($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli) || !$contexto['permisos']['recurrencias']) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para anular recurrencias.');
    }
    $idRegla = planificacionEntero(isset($entrada['id_regla']) ? $entrada['id_regla'] : 0);
    $motivo = planificacionTextoEntrada(isset($entrada['motivo']) ? $entrada['motivo'] : '', 255);
    $sql = "SELECT * FROM planificacion_especialista_regla WHERE id_regla=? AND estado='activo' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $idRegla);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $anterior = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$anterior || $motivo === '') {
        planificacionLanzar('regla_no_disponible', 'La recurrencia no esta disponible o falta el motivo.');
    }
    planificacionLocalAutorizado($mysqli, $contexto, $anterior['cod_localFK']);
    $mysqli->begin_transaction();
    try {
        $sql = "UPDATE planificacion_especialista_regla
            SET estado='inactivo',motivo=?,version=version+1,
                fecha_edit=NOW(),cod_usuarioFK_edit=?
            WHERE id_regla=? AND estado='activo'";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sii', $motivo, $contexto['cod_usuario'], $idRegla);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            planificacionLanzar('regla_no_disponible', 'La recurrencia cambio. Actualice la vista.');
        }
        $stmt->close();
        planificacionGuardarHistorial(
            $mysqli,
            'regla',
            $idRegla,
            'anular_recurrencia',
            $anterior,
            array('estado' => 'inactivo'),
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_regla' => $idRegla);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionAnularOcurrencia($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli) || !$contexto['permisos']['gestionar']) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para modificar la ocurrencia.');
    }
    $idRegla = planificacionEntero(isset($entrada['id_regla']) ? $entrada['id_regla'] : 0);
    $fecha = isset($entrada['fecha']) ? (string)$entrada['fecha'] : '';
    $motivo = planificacionTextoEntrada(isset($entrada['motivo']) ? $entrada['motivo'] : '', 255);
    if (!planificacionFechaValida($fecha) || $motivo === '') {
        planificacionLanzar('datos_incompletos', 'Indique fecha y motivo.');
    }
    $sql = "SELECT * FROM planificacion_especialista_regla WHERE id_regla=? AND estado='activo' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $idRegla);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $regla = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$regla || intval(date('N', strtotime($fecha))) !== intval($regla['dia_semana'])
        || $fecha < $regla['fecha_desde']
        || ($regla['fecha_hasta'] && $fecha > $regla['fecha_hasta'])) {
        planificacionLanzar('ocurrencia_no_disponible', 'La fecha no pertenece a la recurrencia activa.');
    }
    planificacionLocalAutorizado($mysqli, $contexto, $regla['cod_localFK']);
    $sql = "SELECT id_asignacion
        FROM planificacion_especialista_asignacion
        WHERE id_reglaFK=? AND fecha=? AND estado='anulada'
        LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        planificacionLanzar('ocurrencia_no_disponible', 'No se pudo validar la excepción recurrente.');
    }
    $stmt->bind_param('is', $idRegla, $fecha);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $excepcionExistente = $resultado && $resultado->num_rows > 0;
    $stmt->close();
    if ($excepcionExistente) {
        planificacionLanzar('ocurrencia_ya_anulada', 'La fecha ya fue excluida de esta recurrencia.');
    }
    $mysqli->begin_transaction();
    try {
        $sql = "INSERT INTO planificacion_especialista_asignacion
            (cod_profesionalFK,cod_localFK,id_consultorioFK,fecha,
             id_horario_usuarioFK,id_reglaFK,tipo_origen,estado,motivo,cod_usuarioFK_create)
            VALUES (?,?,?,?,?,?, 'ajuste_regla','anulada',?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param(
            'iiisiisi',
            $regla['cod_profesionalFK'],
            $regla['cod_localFK'],
            $regla['id_consultorioFK'],
            $fecha,
            $regla['id_horario_usuarioFK'],
            $idRegla,
            $motivo,
            $contexto['cod_usuario']
        );
        if (!$stmt->execute()) {
            $stmt->close();
            planificacionLanzar('ocurrencia_no_guardada', 'No se pudo registrar la excepcion.');
        }
        $idAsignacion = intval($mysqli->insert_id);
        $stmt->close();
        planificacionGuardarHistorial(
            $mysqli,
            'asignacion',
            $idAsignacion,
            'anular_ocurrencia',
            $regla,
            array('fecha' => $fecha, 'estado' => 'anulada'),
            $motivo,
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('id_asignacion' => $idAsignacion, 'id_regla' => $idRegla, 'fecha' => $fecha);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionGuardarEspecialidad($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli) || !$contexto['permisos']['gestionar']) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para editar especialidades.');
    }
    $codProfesional = planificacionEntero(isset($entrada['cod_profesional']) ? $entrada['cod_profesional'] : 0);
    $especialidad = planificacionTextoEntrada(isset($entrada['especialidad']) ? $entrada['especialidad'] : '', 120);
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    planificacionValidarProfesional(
        $mysqli,
        $codProfesional,
        $local['cod_local'],
        $contexto
    );
    $sql = "SELECT especialidad FROM planificacion_especialista_perfil WHERE cod_usuarioFK=? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $codProfesional);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $anterior = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    $mysqli->begin_transaction();
    try {
        $sql = "INSERT INTO planificacion_especialista_perfil
            (cod_usuarioFK,especialidad,fecha_edit,cod_usuarioFK_edit)
            VALUES (?,NULLIF(?,''),NOW(),?)
            ON DUPLICATE KEY UPDATE especialidad=VALUES(especialidad),
                fecha_edit=NOW(),cod_usuarioFK_edit=VALUES(cod_usuarioFK_edit)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isi', $codProfesional, $especialidad, $contexto['cod_usuario']);
        if (!$stmt->execute()) {
            $stmt->close();
            planificacionLanzar('especialidad_no_guardada', 'No se pudo guardar la especialidad.');
        }
        $stmt->close();
        planificacionGuardarHistorial(
            $mysqli,
            'perfil',
            $codProfesional,
            'editar_especialidad',
            $anterior,
            array('especialidad' => $especialidad),
            '',
            $contexto['cod_usuario']
        );
        $mysqli->commit();
        return array('cod_profesional' => $codProfesional, 'especialidad' => $especialidad);
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function planificacionListarHistorial($mysqli, $contexto, $entrada)
{
    if (!planificacionEstructuraDisponible($mysqli) || !$contexto['permisos']['historial']) {
        planificacionLanzar('accion_no_autorizada', 'No tiene permiso para ver el historial.');
    }
    $local = planificacionLocalAutorizado(
        $mysqli,
        $contexto,
        isset($entrada['cod_local']) ? $entrada['cod_local'] : 0
    );
    $vinculosDisponibles = planificacionVinculosLocalesDisponibles($mysqli);
    $vinculoJoin = $vinculosDisponibles
        ? "LEFT JOIN planificacion_especialista_local vl
            ON h.entidad='vinculo_local' AND vl.id_vinculo=h.id_entidad"
        : "";
    $vinculoWhere = $vinculosDisponibles ? " OR vl.cod_localFK=?" : "";
    $sql = "SELECT h.id_historial,h.entidad,h.id_entidad,h.accion,h.motivo,
            h.fecha_create,
            COALESCE(NULLIF(p.nombre_persona,''),u.login) AS actor
        FROM planificacion_especialista_historial h
        INNER JOIN usuario u ON u.cod_usuario=h.cod_usuarioFK_create
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN planificacion_especialista_asignacion a
            ON h.entidad='asignacion' AND a.id_asignacion=h.id_entidad
        LEFT JOIN planificacion_especialista_regla r
            ON h.entidad='regla' AND r.id_regla=h.id_entidad
        ".$vinculoJoin."
        LEFT JOIN usuario up
            ON h.entidad='perfil' AND up.cod_usuario=h.id_entidad
        WHERE a.cod_localFK=?
           OR r.cod_localFK=?
           ".$vinculoWhere."
           OR (
                h.entidad='perfil'
                AND (
                    up.cod_localFK=?
                    OR EXISTS (
                        SELECT 1 FROM horario_usuario hup
                        WHERE hup.cod_usuarioFK=up.cod_usuario
                          AND hup.cod_localFK=?
                          AND UPPER(IFNULL(hup.estado_horario,'ACTIVO'))='ACTIVO'
                    )
                )
           )
        ORDER BY h.fecha_create DESC,h.id_historial DESC
        LIMIT 100";
    $stmt = $mysqli->prepare($sql);
    if ($vinculosDisponibles) {
        $stmt->bind_param(
            'iiiii',
            $local['cod_local'],
            $local['cod_local'],
            $local['cod_local'],
            $local['cod_local'],
            $local['cod_local']
        );
    } else {
        $stmt->bind_param(
            'iiii',
            $local['cod_local'],
            $local['cod_local'],
            $local['cod_local'],
            $local['cod_local']
        );
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $items = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $items[] = array(
            'id_historial' => intval($fila['id_historial']),
            'entidad' => $fila['entidad'],
            'id_entidad' => intval($fila['id_entidad']),
            'accion' => $fila['accion'],
            'motivo' => $fila['motivo'],
            'fecha' => $fila['fecha_create'],
            'actor' => $fila['actor']
        );
    }
    $stmt->close();
    return array('items' => $items);
}

try {
    $accion = trim((string)planificacionParametro('accion', ''));
    if ($accion === '') {
        planificacionLanzar('accion_requerida', 'No se indico la accion solicitada.');
    }
    $codUsuario = planificacionEntero(planificacionParametro('useru', 0));
    $pass = str_replace('=', '+', (string)planificacionParametro('passu', ''));
    $navegador = planificacionTextoEntrada(planificacionParametro('navegador', ''), 100);
    if ($codUsuario <= 0 || $pass === '' || $navegador === '') {
        planificacionResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    if (verificar_navegador($codUsuario, $navegador, $pass) !== 'ok') {
        planificacionResponder(false, 'sesion_invalida', 'La sesion no es valida.', array(), 401);
    }
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        planificacionResponder(false, 'conexion_no_disponible', 'No se pudo conectar con el servidor.', array(), 500);
    }
    $contexto = planificacionContexto($mysqli, $codUsuario);
    $entrada = $_POST;

    switch ($accion) {
        case 'obtenerPlanificacion':
            $datos = planificacionObtenerDatos($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'planificacion_obtenida', 'Planificacion obtenida.', $datos);
            break;
        case 'listarCandidatosSucursal':
            $datos = planificacionCandidatosSucursal($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'candidatos_obtenidos', 'Profesionales disponibles obtenidos.', $datos);
            break;
        case 'agregarProfesionalSucursal':
            $datos = planificacionAgregarProfesionalSucursal($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'profesional_agregado', 'Profesional agregado al listado.', $datos);
            break;
        case 'quitarProfesionalSucursal':
            $datos = planificacionQuitarProfesionalSucursal($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'profesional_quitado', 'Profesional retirado del listado.', $datos);
            break;
        case 'guardarAsignacion':
        case 'moverAsignacion':
            $datos = planificacionGuardarAsignacion($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'asignacion_guardada', 'Asignacion guardada.', $datos);
            break;
        case 'guardarRegla':
            $datos = planificacionGuardarRegla($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'recurrencia_guardada', 'Recurrencia guardada.', $datos);
            break;
        case 'anularAsignacion':
            $datos = planificacionAnularAsignacion($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'asignacion_anulada', 'Asignacion anulada.', $datos);
            break;
        case 'anularRegla':
            $datos = planificacionAnularRegla($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'recurrencia_anulada', 'Recurrencia anulada.', $datos);
            break;
        case 'anularOcurrencia':
            $datos = planificacionAnularOcurrencia($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'ocurrencia_anulada', 'Ocurrencia anulada.', $datos);
            break;
        case 'guardarEspecialidad':
            $datos = planificacionGuardarEspecialidad($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'especialidad_guardada', 'Especialidad guardada.', $datos);
            break;
        case 'listarHistorial':
            $datos = planificacionListarHistorial($mysqli, $contexto, $entrada);
            planificacionResponder(true, 'historial_obtenido', 'Historial obtenido.', $datos);
            break;
        default:
            planificacionLanzar('accion_no_reconocida', 'La accion solicitada no existe.');
    }
} catch (PlanificacionEspecialistasExcepcion $e) {
    $estado = strpos($e->codigoOperacion, 'no_autoriz') !== false
        || $e->codigoOperacion === 'local_no_autorizado' ? 403 : 200;
    planificacionResponder(
        false,
        $e->codigoOperacion,
        $e->getMessage(),
        $e->datosOperacion,
        $estado
    );
} catch (Exception $e) {
    error_log('[PlanificacionEspecialistas] '.get_class($e).': '.$e->getMessage());
    planificacionResponder(false, 'error_interno', 'No se pudo completar la operacion.', array(), 500);
} catch (Throwable $e) {
    error_log('[PlanificacionEspecialistas] '.get_class($e).': '.$e->getMessage());
    planificacionResponder(false, 'error_interno', 'No se pudo completar la operacion.', array(), 500);
}

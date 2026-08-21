<?php

require_once __DIR__.'/central_telefonica_helper.php';
require_once __DIR__.'/central_telefonica_operacion_helper.php';

class MiCarteraExcepcion extends Exception
{
    public $codigoOperacion;
    public $datosOperacion;

    public function __construct($codigo, $mensaje, $datos = array())
    {
        parent::__construct($mensaje);
        $this->codigoOperacion = (string)$codigo;
        $this->datosOperacion = is_array($datos) ? $datos : array();
    }
}

function miCarteraLanzar($codigo, $mensaje, $datos = array())
{
    throw new MiCarteraExcepcion($codigo, $mensaje, $datos);
}

function miCarteraTexto($valor, $maximo)
{
    $valor = trim((string)$valor);
    if ($maximo > 0 && mb_strlen($valor, 'UTF-8') > $maximo) {
        $valor = mb_substr($valor, 0, $maximo, 'UTF-8');
    }
    return $valor;
}

function miCarteraTextoDb($valor, $maximo)
{
    return mb_convert_encoding(miCarteraTexto($valor, $maximo), 'ISO-8859-1', 'UTF-8');
}

function miCarteraBind($stmt, $tipos, $parametros)
{
    if ($tipos === '') {
        return true;
    }
    $referencias = array(&$tipos);
    foreach ($parametros as $indice => $valor) {
        $parametros[$indice] = $valor;
        $referencias[] = &$parametros[$indice];
    }
    return call_user_func_array(array($stmt, 'bind_param'), $referencias);
}

function miCarteraTablaExiste($mysqli, $tabla)
{
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '') {
        return false;
    }
    $resultado = $mysqli->query(
        "SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() "
        ."AND table_name='".$mysqli->real_escape_string($tabla)."' LIMIT 1"
    );
    return $resultado && $resultado->num_rows === 1;
}

function miCarteraEstructuraDisponible($mysqli)
{
    $tablas = array(
        'cartera_configuracion',
        'cartera_equipo',
        'cartera_asignacion',
        'cartera_gestion',
        'cartera_compromiso',
        'cartera_evento'
    );
    foreach ($tablas as $tabla) {
        if (!miCarteraTablaExiste($mysqli, $tabla)) {
            return false;
        }
    }
    return true;
}

function miCarteraUsuarioEsCarlos($codUsuario)
{
    return intval($codUsuario) === 5994;
}

function miCarteraContextoUsuario($mysqli, $codUsuario)
{
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,u.estado,u.tipo,u.login,u.cod_localFK,IFNULL(u.url,'') avatar,"
        ."COALESCE(NULLIF(p.nombre_persona,''),u.login) nombre_usuario,"
        ."IFNULL(l.Nombre,'') nombre_local FROM usuario u "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK WHERE u.cod_usuario=? LIMIT 1"
    );
    if (!$stmt) {
        miCarteraLanzar('usuario_no_disponible', 'No se pudo validar el usuario de Telar.');
    }
    $codUsuario = intval($codUsuario);
    $stmt->bind_param('i', $codUsuario);
    $usuario = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $usuario = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    if (!$usuario || strtoupper(trim((string)$usuario['estado'])) !== 'ACTIVO') {
        miCarteraLanzar('usuario_no_disponible', 'El usuario autenticado no esta activo.');
    }

    $roles = array();
    if (miCarteraTablaExiste($mysqli, 'cartera_equipo')) {
        $stmt = $mysqli->prepare(
            "SELECT rol,cod_localFK FROM cartera_equipo "
            ."WHERE cod_usuarioFK=? AND activo=1 ORDER BY rol,cod_localFK"
        );
        if ($stmt) {
            $stmt->bind_param('i', $codUsuario);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                while ($resultado && ($fila = $resultado->fetch_assoc())) {
                    $roles[] = array(
                        'rol' => (string)$fila['rol'],
                        'cod_local' => intval($fila['cod_localFK'])
                    );
                }
            }
            $stmt->close();
        }
    }

    $esCarlos = miCarteraUsuarioEsCarlos($codUsuario);
    $esJefe = false;
    foreach ($roles as $rol) {
        if ($rol['rol'] === 'jefe') {
            $esJefe = true;
        }
    }
    if (!$esCarlos && count($roles) === 0) {
        miCarteraLanzar(
            'acceso_no_configurado',
            'Su usuario no forma parte del equipo configurado de cartera.'
        );
    }

    return array(
        'cod_usuario' => $codUsuario,
        'nombre' => (string)$usuario['nombre_usuario'],
        'avatar' => trim((string)$usuario['avatar']) !== ''
            ? (string)$usuario['avatar'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
        'tipo' => (string)$usuario['tipo'],
        'cod_local' => intval($usuario['cod_localFK']),
        'local' => (string)$usuario['nombre_local'],
        'roles' => $roles,
        'es_carlos' => $esCarlos,
        'es_jefe' => $esJefe,
        'puede_configurar' => $esCarlos,
        'puede_supervisar' => $esCarlos || $esJefe
    );
}

function miCarteraExigirSupervisor($contexto)
{
    if (empty($contexto['puede_supervisar'])) {
        miCarteraLanzar('accion_no_autorizada', 'Esta accion corresponde al jefe de Cobranza.');
    }
}

function miCarteraExigirConfigurador($contexto)
{
    if (empty($contexto['puede_configurar'])) {
        miCarteraLanzar(
            'accion_no_autorizada',
            'Solo Carlos Faraone puede configurar los responsables de Mi cartera.'
        );
    }
}

function miCarteraConfiguracionBase($mysqli)
{
    $base = array(
        'cod_jefe' => 0,
        'jefe' => null,
        'dias_prevencion' => 7,
        'dias_escalamiento' => 90,
        'intentos_escalamiento' => 2,
        'activo' => true,
        'gestores' => array(),
        'cobradores' => array(),
        'completa' => false
    );
    if (!miCarteraEstructuraDisponible($mysqli)) {
        return $base;
    }
    $resultado = $mysqli->query(
        "SELECT cod_jefeFK,dias_prevencion,dias_escalamiento,intentos_escalamiento,activo "
        ."FROM cartera_configuracion WHERE id_configuracion=1 LIMIT 1"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if ($fila) {
        $base['cod_jefe'] = intval($fila['cod_jefeFK']);
        $base['dias_prevencion'] = intval($fila['dias_prevencion']);
        $base['dias_escalamiento'] = intval($fila['dias_escalamiento']);
        $base['intentos_escalamiento'] = intval($fila['intentos_escalamiento']);
        $base['activo'] = intval($fila['activo']) === 1;
    }
    $resultado = $mysqli->query(
        "SELECT e.cod_usuarioFK,e.rol,e.cod_localFK,"
        ."COALESCE(NULLIF(p.nombre_persona,''),u.login) nombre_usuario,"
        ."IFNULL(u.url,'') avatar,IFNULL(l.Nombre,'') nombre_local "
        ."FROM cartera_equipo e INNER JOIN usuario u ON u.cod_usuario=e.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=e.cod_localFK WHERE e.activo=1 "
        ."ORDER BY FIELD(e.rol,'jefe','gestor_local','cobrador_central'),l.Nombre,nombre_usuario"
    );
    while ($resultado && ($equipo = $resultado->fetch_assoc())) {
        $item = array(
            'cod_usuario' => intval($equipo['cod_usuarioFK']),
            'nombre' => (string)$equipo['nombre_usuario'],
            'avatar' => trim((string)$equipo['avatar']) !== ''
                ? (string)$equipo['avatar'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
            'cod_local' => intval($equipo['cod_localFK']),
            'local' => (string)$equipo['nombre_local']
        );
        if ($equipo['rol'] === 'gestor_local') {
            $base['gestores'][] = $item;
        } elseif ($equipo['rol'] === 'cobrador_central') {
            $base['cobradores'][] = $item;
        } elseif ($equipo['rol'] === 'jefe') {
            $base['jefe'] = $item;
        }
    }
    $base['completa'] = $base['cod_jefe'] > 0
        && count($base['gestores']) >= 1
        && count($base['cobradores']) >= 1;
    return $base;
}

function miCarteraCatalogosConfiguracion($mysqli, $contexto)
{
    miCarteraExigirConfigurador($contexto);
    $usuarios = array();
    $locales = array();
    $resultado = $mysqli->query(
        "SELECT u.cod_usuario,COALESCE(NULLIF(p.nombre_persona,''),u.login) nombre,"
        ."IFNULL(u.url,'') avatar,IFNULL(u.tipo,'') tipo,IFNULL(l.Nombre,'') local "
        ."FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."WHERE UPPER(TRIM(u.estado))='ACTIVO' ORDER BY nombre"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $usuarios[] = array(
            'cod_usuario' => intval($fila['cod_usuario']),
            'nombre' => (string)$fila['nombre'],
            'avatar' => trim((string)$fila['avatar']) !== ''
                ? (string)$fila['avatar'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
            'tipo' => (string)$fila['tipo'],
            'local' => (string)$fila['local']
        );
    }
    $resultado = $mysqli->query(
        "SELECT cod_local,Nombre FROM local WHERE UPPER(TRIM(estado))='ACTIVO' ORDER BY Nombre"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $locales[] = array(
            'cod_local' => intval($fila['cod_local']),
            'nombre' => (string)$fila['Nombre']
        );
    }
    return array(
        'configuracion' => miCarteraConfiguracionBase($mysqli),
        'usuarios' => $usuarios,
        'locales' => $locales
    );
}

function miCarteraIdsValidos($mysqli, $tabla, $campo, $ids, $condicion)
{
    $limpios = array();
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 0) {
            $limpios[$id] = $id;
        }
    }
    if (count($limpios) === 0) {
        return array();
    }
    $lista = implode(',', $limpios);
    $resultado = $mysqli->query(
        "SELECT ".$campo." id FROM ".$tabla." WHERE ".$campo." IN (".$lista.") ".$condicion
    );
    $validos = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $validos[intval($fila['id'])] = true;
    }
    return $validos;
}

function miCarteraRegistrarEvento(
    $mysqli,
    $contexto,
    $tipo,
    $detalle,
    $codCliente,
    $idAsignacion,
    $anteriores,
    $nuevos
) {
    $stmt = $mysqli->prepare(
        "INSERT INTO cartera_evento "
        ."(cod_clienteFK,id_asignacionFK,cod_usuario_actorFK,tipo_evento,detalle,"
        ."datos_anteriores,datos_nuevos,fecha_evento) VALUES (?,?,?,?,?,?,?,NOW())"
    );
    if (!$stmt) {
        return false;
    }
    $codCliente = $codCliente === null ? null : intval($codCliente);
    $idAsignacion = $idAsignacion === null ? null : intval($idAsignacion);
    $actor = intval($contexto['cod_usuario']);
    $tipo = miCarteraTextoDb($tipo, 40);
    $detalle = miCarteraTextoDb($detalle, 500);
    $anteriores = $anteriores === null ? null : json_encode(centralTelefonicaUtf8($anteriores));
    $nuevos = $nuevos === null ? null : json_encode(centralTelefonicaUtf8($nuevos));
    $stmt->bind_param(
        'iiissss',
        $codCliente,
        $idAsignacion,
        $actor,
        $tipo,
        $detalle,
        $anteriores,
        $nuevos
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function miCarteraValidarConfiguracionEntrada($mysqli, $entrada)
{
    $jefe = intval(isset($entrada['cod_jefe']) ? $entrada['cod_jefe'] : 0);
    $diasEscalamiento = intval(
        isset($entrada['dias_escalamiento']) ? $entrada['dias_escalamiento'] : 90
    );
    $gestores = json_decode(isset($entrada['gestores']) ? $entrada['gestores'] : '[]', true);
    $cobradores = json_decode(isset($entrada['cobradores']) ? $entrada['cobradores'] : '[]', true);
    if ($jefe <= 0 || !is_array($gestores) || count($gestores) < 1
        || !is_array($cobradores) || count($cobradores) < 1) {
        miCarteraLanzar(
            'configuracion_incompleta',
            'Seleccione un jefe, al menos un gestor de clinica y un cobrador central.'
        );
    }
    if ($diasEscalamiento < 30 || $diasEscalamiento > 365) {
        miCarteraLanzar(
            'configuracion_invalida',
            'Los dias para pasar a Cobranza central deben estar entre 30 y 365.'
        );
    }
    $mapaGestores = array();
    $usuariosEquipo = array($jefe => $jefe);
    foreach ($gestores as $gestor) {
        $local = intval(isset($gestor['cod_local']) ? $gestor['cod_local'] : 0);
        $usuario = intval(isset($gestor['cod_usuario']) ? $gestor['cod_usuario'] : 0);
        if ($local <= 0 || $usuario <= 0 || isset($mapaGestores[$local])) {
            miCarteraLanzar('configuracion_invalida', 'Cada clinica debe tener un unico gestor.');
        }
        if (isset($usuariosEquipo[$usuario])) {
            miCarteraLanzar('configuracion_invalida', 'Cada integrante debe ocupar un unico lugar en el equipo.');
        }
        $mapaGestores[$local] = $usuario;
        $usuariosEquipo[$usuario] = $usuario;
    }
    $mapaCobradores = array();
    foreach ($cobradores as $cobrador) {
        $usuario = intval($cobrador);
        if ($usuario <= 0 || isset($mapaCobradores[$usuario]) || isset($usuariosEquipo[$usuario])) {
            miCarteraLanzar('configuracion_invalida', 'Cada cobrador debe ser distinto del resto del equipo.');
        }
        $mapaCobradores[$usuario] = $usuario;
        $usuariosEquipo[$usuario] = $usuario;
    }
    $usuariosValidos = miCarteraIdsValidos(
        $mysqli,
        'usuario',
        'cod_usuario',
        array_values($usuariosEquipo),
        "AND UPPER(TRIM(estado))='ACTIVO'"
    );
    $localesValidos = miCarteraIdsValidos(
        $mysqli,
        'local',
        'cod_local',
        array_keys($mapaGestores),
        "AND UPPER(TRIM(estado))='ACTIVO'"
    );
    if (count($usuariosValidos) !== count($usuariosEquipo)
        || count($localesValidos) !== count($mapaGestores)) {
        miCarteraLanzar('configuracion_invalida', 'Uno de los usuarios o locales seleccionados ya no esta activo.');
    }

    return array(
        'cod_jefe' => $jefe,
        'dias_escalamiento' => $diasEscalamiento,
        'gestores' => $mapaGestores,
        'cobradores' => array_values($mapaCobradores),
        'usuarios_equipo' => $usuariosEquipo
    );
}

function miCarteraIdsOrdenados($ids)
{
    $resultado = array_values(array_unique(array_map('intval', $ids)));
    sort($resultado, SORT_NUMERIC);
    return $resultado;
}

function miCarteraPlanReconfiguracion($mysqli, $propuesta, $bloquear)
{
    $bloquear = (bool)$bloquear;
    $actual = miCarteraConfiguracionBase($mysqli);
    $actualCobradores = array();
    foreach ($actual['cobradores'] as $cobrador) {
        $actualCobradores[] = intval($cobrador['cod_usuario']);
    }
    $nuevosCobradores = miCarteraIdsOrdenados($propuesta['cobradores']);
    $cobradoresCambiaron = miCarteraIdsOrdenados($actualCobradores) !== $nuevosCobradores;
    $resultado = $mysqli->query(
        "SELECT id_asignacion,cod_clienteFK,cod_usuario_responsableFK,cod_local_origenFK,"
        ."tipo_responsable,prioridad,motivo_asignacion FROM cartera_asignacion "
        ."WHERE estado='activa' ORDER BY id_asignacion".($bloquear ? " FOR UPDATE" : "")
    );
    if (!$resultado) {
        miCarteraLanzar('configuracion_no_disponible', 'No se pudieron revisar las asignaciones actuales.');
    }
    $asignaciones = array();
    $clientes = array();
    while ($fila = $resultado->fetch_assoc()) {
        $asignaciones[] = $fila;
        $clientes[] = intval($fila['cod_clienteFK']);
    }
    $finanzas = miCarteraResumenesFinancieros($mysqli, $clientes);
    $especialesJefe = array(
        'promesa_incumplida',
        'solicita_revision',
        'escalamiento_manual',
        'toma_jefe'
    );
    $preparados = array();
    $centrales = array();
    foreach ($asignaciones as $asignacion) {
        $cliente = intval($asignacion['cod_clienteFK']);
        $dias = isset($finanzas[$cliente]) ? intval($finanzas[$cliente]['dias_mora']) : 0;
        $motivoActual = (string)$asignacion['motivo_asignacion'];
        $tipoActual = (string)$asignacion['tipo_responsable'];
        $destino = array(
            'asignacion' => $asignacion,
            'cod_usuario' => 0,
            'tipo' => 'sin_asignar',
            'prioridad' => $dias > 0 ? 'media' : 'baja',
            'motivo' => 'local_sin_gestor',
            'dias_mora' => $dias
        );
        if ($tipoActual === 'jefe_cobranza' || in_array($motivoActual, $especialesJefe, true)) {
            $destino['cod_usuario'] = intval($propuesta['cod_jefe']);
            $destino['tipo'] = 'jefe_cobranza';
            $destino['prioridad'] = 'alta';
            $destino['motivo'] = in_array($motivoActual, $especialesJefe, true)
                ? $motivoActual : 'toma_jefe';
        } elseif ($motivoActual === 'dos_intentos_sin_respuesta'
            || $dias >= intval($propuesta['dias_escalamiento'])) {
            $destino['tipo'] = 'cobranza_central';
            $destino['prioridad'] = 'alta';
            $destino['motivo'] = $motivoActual === 'dos_intentos_sin_respuesta'
                ? $motivoActual : 'mora_'.intval($propuesta['dias_escalamiento']).'_dias';
            $centrales[] = count($preparados);
        } elseif (isset($propuesta['gestores'][intval($asignacion['cod_local_origenFK'])])) {
            $destino['cod_usuario'] = intval(
                $propuesta['gestores'][intval($asignacion['cod_local_origenFK'])]
            );
            $destino['tipo'] = 'gestor_local';
            $destino['motivo'] = 'local_origen';
        }
        $preparados[] = $destino;
    }

    $cargas = array();
    foreach ($nuevosCobradores as $cobrador) {
        $cargas[$cobrador] = 0;
    }
    if (!$cobradoresCambiaron) {
        foreach ($centrales as $indice) {
            $actualUsuario = intval($preparados[$indice]['asignacion']['cod_usuario_responsableFK']);
            if (isset($cargas[$actualUsuario])) {
                $preparados[$indice]['cod_usuario'] = $actualUsuario;
                $cargas[$actualUsuario]++;
            }
        }
    }
    foreach ($centrales as $indice) {
        if (intval($preparados[$indice]['cod_usuario']) > 0) {
            continue;
        }
        $preparados[$indice]['cod_usuario'] = miCarteraCobradorMenorCarga($cargas);
        if (intval($preparados[$indice]['cod_usuario']) <= 0) {
            $preparados[$indice]['tipo'] = 'sin_asignar';
            $preparados[$indice]['motivo'] = 'central_sin_responsable';
        }
    }

    $impacto = array(
        'total_activo' => count($preparados),
        'reasignaciones' => 0,
        'sin_cambios' => 0,
        'gestores_locales' => 0,
        'cobranza_central' => 0,
        'jefe_cobranza' => 0,
        'sin_asignar' => 0,
        'cambios' => array()
    );
    foreach ($preparados as $destino) {
        if ($destino['tipo'] === 'gestor_local') {
            $impacto['gestores_locales']++;
        } elseif ($destino['tipo'] === 'cobranza_central') {
            $impacto['cobranza_central']++;
        } elseif ($destino['tipo'] === 'jefe_cobranza') {
            $impacto['jefe_cobranza']++;
        } else {
            $impacto['sin_asignar']++;
        }
        $asignacion = $destino['asignacion'];
        $cambio = intval($asignacion['cod_usuario_responsableFK']) !== intval($destino['cod_usuario'])
            || (string)$asignacion['tipo_responsable'] !== (string)$destino['tipo'];
        if ($cambio) {
            $impacto['reasignaciones']++;
            $impacto['cambios'][] = $destino;
        } else {
            $impacto['sin_cambios']++;
        }
    }
    return $impacto;
}

function miCarteraFirmaReconfiguracion($propuesta, $impacto)
{
    $cambios = array();
    foreach ($impacto['cambios'] as $cambio) {
        $cambios[] = array(
            'id_asignacion' => intval($cambio['asignacion']['id_asignacion']),
            'cod_usuario' => intval($cambio['cod_usuario']),
            'tipo' => (string)$cambio['tipo'],
            'motivo' => (string)$cambio['motivo']
        );
    }
    return hash('sha256', json_encode(array(
        'cod_jefe' => intval($propuesta['cod_jefe']),
        'dias_escalamiento' => intval($propuesta['dias_escalamiento']),
        'gestores' => $propuesta['gestores'],
        'cobradores' => miCarteraIdsOrdenados($propuesta['cobradores']),
        'total_activo' => intval($impacto['total_activo']),
        'cambios' => $cambios
    )));
}

function miCarteraPrevisualizarConfiguracion($mysqli, $contexto, $entrada)
{
    miCarteraExigirConfigurador($contexto);
    $propuesta = miCarteraValidarConfiguracionEntrada($mysqli, $entrada);
    $impacto = miCarteraPlanReconfiguracion($mysqli, $propuesta, false);
    $firma = miCarteraFirmaReconfiguracion($propuesta, $impacto);
    unset($impacto['cambios']);
    return array(
        'propuesta' => array(
            'cod_jefe' => intval($propuesta['cod_jefe']),
            'dias_escalamiento' => intval($propuesta['dias_escalamiento']),
            'cantidad_gestores' => count($propuesta['gestores']),
            'cantidad_cobradores' => count($propuesta['cobradores'])
        ),
        'impacto' => $impacto,
        'firma_impacto' => $firma
    );
}

function miCarteraGuardarConfiguracion($mysqli, $contexto, $entrada)
{
    miCarteraExigirConfigurador($contexto);
    if ((string)(isset($entrada['confirmar']) ? $entrada['confirmar'] : '') !== '1') {
        miCarteraLanzar(
            'confirmacion_requerida',
            'Revise la vista previa antes de confirmar los cambios del equipo.'
        );
    }
    $propuesta = miCarteraValidarConfiguracionEntrada($mysqli, $entrada);
    $jefe = intval($propuesta['cod_jefe']);
    $diasEscalamiento = intval($propuesta['dias_escalamiento']);
    $mapaGestores = $propuesta['gestores'];
    $mapaCobradores = array();
    foreach ($propuesta['cobradores'] as $cobrador) {
        $mapaCobradores[intval($cobrador)] = intval($cobrador);
    }
    $usuariosEquipo = $propuesta['usuarios_equipo'];
    $actor = intval($contexto['cod_usuario']);
    $firmaEsperada = preg_replace(
        '/[^a-f0-9]/',
        '',
        strtolower((string)(isset($entrada['firma_impacto']) ? $entrada['firma_impacto'] : ''))
    );
    $mysqli->begin_transaction();
    try {
        $planReconfiguracion = miCarteraPlanReconfiguracion($mysqli, $propuesta, true);
        $firmaActual = miCarteraFirmaReconfiguracion($propuesta, $planReconfiguracion);
        if (strlen($firmaEsperada) !== 64 || !hash_equals($firmaActual, $firmaEsperada)) {
            throw new Exception(
                'La cartera cambio desde la vista previa. Vuelva a revisar el impacto antes de confirmar.'
            );
        }
        $anterior = miCarteraConfiguracionBase($mysqli);
        if (!$mysqli->query("UPDATE cartera_equipo SET activo=0,fecha_actualizacion=NOW() WHERE activo=1")) {
            throw new Exception('No se pudo preparar la nueva configuracion.');
        }
        $stmt = $mysqli->prepare(
            "INSERT INTO cartera_equipo "
            ."(cod_usuarioFK,rol,cod_localFK,activo,cod_usuario_asignaFK,fecha_asignacion,fecha_actualizacion) "
            ."VALUES (?,?,?,1,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE activo=1,"
            ."cod_usuario_asignaFK=VALUES(cod_usuario_asignaFK),fecha_asignacion=NOW(),fecha_actualizacion=NOW()"
        );
        if (!$stmt) {
            throw new Exception('No se pudo preparar el equipo.');
        }
        $rol = 'jefe';
        $localCero = 0;
        $stmt->bind_param('isii', $jefe, $rol, $localCero, $actor);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo guardar el jefe de Cobranza.');
        }
        foreach ($mapaGestores as $local => $usuario) {
            $rol = 'gestor_local';
            $stmt->bind_param('isii', $usuario, $rol, $local, $actor);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar un gestor de sucursal.');
            }
        }
        foreach ($mapaCobradores as $usuario) {
            $rol = 'cobrador_central';
            $stmt->bind_param('isii', $usuario, $rol, $localCero, $actor);
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar un cobrador central.');
            }
        }
        $stmt->close();
        $stmt = $mysqli->prepare(
            "UPDATE cartera_configuracion SET cod_jefeFK=?,dias_prevencion=7,"
            ."dias_escalamiento=?,intentos_escalamiento=2,activo=1,"
            ."cod_usuario_actualizaFK=?,fecha_actualizacion=NOW() WHERE id_configuracion=1"
        );
        if (!$stmt) {
            throw new Exception('No se pudo preparar la configuracion.');
        }
        $stmt->bind_param('iii', $jefe, $diasEscalamiento, $actor);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo guardar la configuracion.');
        }
        $stmt->close();

        $idsAcceso = array_values($usuariosEquipo);
        $idsAcceso[] = 5994;
        $idsAcceso = array_unique(array_map('intval', $idsAcceso));
        $lista = implode(',', $idsAcceso);
        $sqlAccesos = "INSERT INTO dashboard_user_shortcuts "
            ."(user_id,access_id,shortcut_order,is_visible) "
            ."SELECT u.cod_usuario,c.id,COALESCE(MAX(CASE WHEN s.is_visible=1 THEN s.shortcut_order END),0)+1,1 "
            ."FROM usuario u INNER JOIN dashboard_access_catalog c ON c.access_key='mi_cartera' AND c.is_active=1 "
            ."LEFT JOIN dashboard_user_shortcuts s ON s.user_id=u.cod_usuario "
            ."WHERE u.cod_usuario IN (".$lista.") GROUP BY u.cod_usuario,c.id "
            ."ON DUPLICATE KEY UPDATE is_visible=1,updated_at=CURRENT_TIMESTAMP";
        if (!$mysqli->query($sqlAccesos)) {
            throw new Exception('No se pudieron habilitar los accesos del equipo.');
        }
        $stmtReasignar = $mysqli->prepare(
            "UPDATE cartera_asignacion SET cod_usuario_responsableFK=?,tipo_responsable=?,"
            ."prioridad=?,motivo_asignacion=?,fecha_actualizacion=NOW() "
            ."WHERE id_asignacion=? AND estado='activa'"
        );
        if (!$stmtReasignar && count($planReconfiguracion['cambios']) > 0) {
            throw new Exception('No se pudo preparar la redistribucion de carteras existentes.');
        }
        foreach ($planReconfiguracion['cambios'] as $cambio) {
            $asignacion = $cambio['asignacion'];
            $nuevoUsuario = intval($cambio['cod_usuario']) > 0 ? intval($cambio['cod_usuario']) : null;
            $nuevoTipo = (string)$cambio['tipo'];
            $nuevaPrioridad = (string)$cambio['prioridad'];
            $nuevoMotivo = miCarteraTextoDb((string)$cambio['motivo'], 80);
            $idAsignacion = intval($asignacion['id_asignacion']);
            $stmtReasignar->bind_param(
                'isssi',
                $nuevoUsuario,
                $nuevoTipo,
                $nuevaPrioridad,
                $nuevoMotivo,
                $idAsignacion
            );
            if (!$stmtReasignar->execute()) {
                throw new Exception('No se pudo redistribuir una cartera existente.');
            }
            if (!miCarteraRegistrarEvento(
                $mysqli,
                $contexto,
                'reasignacion_configuracion',
                'La asignacion cambio despues de confirmar la nueva configuracion.',
                intval($asignacion['cod_clienteFK']),
                $idAsignacion,
                array(
                    'responsable' => intval($asignacion['cod_usuario_responsableFK']),
                    'tipo' => (string)$asignacion['tipo_responsable']
                ),
                array(
                    'responsable' => intval($cambio['cod_usuario']),
                    'tipo' => $nuevoTipo,
                    'motivo' => (string)$cambio['motivo']
                )
            )) {
                throw new Exception('No se pudo auditar una reasignacion de configuracion.');
            }
        }
        if ($stmtReasignar) {
            $stmtReasignar->close();
        }
        $nueva = array(
            'cod_jefe' => $jefe,
            'dias_escalamiento' => $diasEscalamiento,
            'gestores' => $mapaGestores,
            'cobradores' => array_values($mapaCobradores)
        );
        if (!miCarteraRegistrarEvento(
            $mysqli,
            $contexto,
            'configuracion_equipo',
            'Se actualizo el equipo responsable de cartera.',
            null,
            null,
            $anterior,
            $nueva
        )) {
            throw new Exception('No se pudo registrar la auditoria de la configuracion.');
        }
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        miCarteraLanzar('configuracion_no_guardada', $e->getMessage());
    }
    $resultado = miCarteraConfiguracionBase($mysqli);
    $impacto = $planReconfiguracion;
    unset($impacto['cambios']);
    $resultado['impacto'] = $impacto;
    return $resultado;
}

function miCarteraPagoActivoSql($alias)
{
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    return "LOWER(TRIM(IFNULL(".$alias.".anulado,''))) NOT IN ('si','anulado','activo')";
}

function miCarteraSaldoSql($aliasCredito, $aliasPago)
{
    return "(GREATEST(0,(IFNULL(".$aliasCredito.".Monto,0)-IFNULL(".$aliasCredito
        .".descuento,0))-IFNULL(".$aliasPago.".pago_cuota,0))"
        ."+GREATEST(0,(IFNULL(".$aliasCredito.".totalinteres,0)+IFNULL(".$aliasCredito
        .".deudaInteres,0))-IFNULL(".$aliasPago.".pago_interes,0)))";
}

function miCarteraPagoAgrupadoSql()
{
    return "LEFT JOIN (SELECT cod_creditoFK,"
        ."SUM(CASE WHEN Tipo='Pago Cuota' AND ".miCarteraPagoActivoSql('pg')." THEN Monto ELSE 0 END) pago_cuota,"
        ."SUM(CASE WHEN Tipo='Interes' AND ".miCarteraPagoActivoSql('pg')." THEN Monto ELSE 0 END) pago_interes "
        ."FROM pago pg GROUP BY cod_creditoFK) pagos ON pagos.cod_creditoFK=cr.idcredito ";
}

function miCarteraResumenesFinancieros($mysqli, $codClientes)
{
    $ids = array();
    foreach ($codClientes as $id) {
        $id = intval($id);
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }
    if (count($ids) === 0) {
        return array();
    }
    $saldo = miCarteraSaldoSql('cr', 'pagos');
    $sql = "SELECT vt.cod_clienteFK,COUNT(DISTINCT vt.cod_venta) ventas,"
        ."SUM(CASE WHEN ".$saldo.">0 THEN ".$saldo." ELSE 0 END) saldo_total,"
        ."SUM(CASE WHEN ".$saldo.">0 AND cr.fechapago<CURDATE() THEN ".$saldo." ELSE 0 END) saldo_vencido,"
        ."SUM(CASE WHEN ".$saldo.">0 AND cr.fechapago<CURDATE() THEN 1 ELSE 0 END) cuotas_vencidas,"
        ."SUM(CASE WHEN ".$saldo.">0 THEN 1 ELSE 0 END) cuotas_pendientes,"
        ."MIN(CASE WHEN ".$saldo.">0 THEN cr.fechapago ELSE NULL END) proximo_vencimiento,"
        ."MAX(CASE WHEN ".$saldo.">0 AND cr.fechapago<CURDATE() "
        ."THEN DATEDIFF(CURDATE(),cr.fechapago) ELSE 0 END) dias_mora,"
        ."GROUP_CONCAT(DISTINCT l.Nombre ORDER BY l.Nombre SEPARATOR ' / ') locales "
        ."FROM credito cr INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta "
        .miCarteraPagoAgrupadoSql()
        ."LEFT JOIN local l ON l.cod_local=vt.cod_local "
        ."WHERE vt.cod_clienteFK IN (".implode(',', $ids).") "
        ."AND IFNULL(vt.anulado,'')='' AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado' "
        ."AND cr.fechapago<=DATE_ADD(CURDATE(),INTERVAL 7 DAY) GROUP BY vt.cod_clienteFK";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        miCarteraLanzar('saldo_no_disponible', 'No se pudo calcular la cartera desde las cuotas vigentes.');
    }
    $resumenes = array();
    while ($fila = $resultado->fetch_assoc()) {
        $id = intval($fila['cod_clienteFK']);
        $resumenes[$id] = array(
            'saldo_total' => (float)$fila['saldo_total'],
            'saldo_vencido' => (float)$fila['saldo_vencido'],
            'cuotas_vencidas' => intval($fila['cuotas_vencidas']),
            'cuotas_pendientes' => intval($fila['cuotas_pendientes']),
            'ventas' => intval($fila['ventas']),
            'proximo_vencimiento' => $fila['proximo_vencimiento']
                ? (string)$fila['proximo_vencimiento'] : '',
            'dias_mora' => intval($fila['dias_mora']),
            'locales' => (string)$fila['locales']
        );
    }
    foreach ($ids as $id) {
        if (!isset($resumenes[$id])) {
            $resumenes[$id] = array(
                'saldo_total' => 0,
                'saldo_vencido' => 0,
                'cuotas_vencidas' => 0,
                'cuotas_pendientes' => 0,
                'ventas' => 0,
                'proximo_vencimiento' => '',
                'dias_mora' => 0,
                'locales' => ''
            );
        }
    }
    return $resumenes;
}

function miCarteraCandidatosPendientes($mysqli)
{
    $saldo = miCarteraSaldoSql('cr', 'pagos');
    $sql = "SELECT vt.cod_clienteFK,"
        ."SUM(CASE WHEN ".$saldo.">0 THEN ".$saldo." ELSE 0 END) saldo_total,"
        ."MAX(CASE WHEN ".$saldo.">0 AND cr.fechapago<CURDATE() "
        ."THEN DATEDIFF(CURDATE(),cr.fechapago) ELSE 0 END) dias_mora,"
        ."CAST(SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN ".$saldo.">0 THEN vt.cod_local ELSE NULL END "
        ."ORDER BY CASE WHEN cr.fechapago<CURDATE() THEN 0 ELSE 1 END,cr.fechapago,cr.idcredito),',',1) AS UNSIGNED) cod_local_origen "
        ."FROM credito cr INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta "
        .miCarteraPagoAgrupadoSql()
        ."WHERE IFNULL(vt.anulado,'')='' AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado' "
        ."AND cr.fechapago<=DATE_ADD(CURDATE(),INTERVAL 7 DAY) GROUP BY vt.cod_clienteFK "
        ."HAVING saldo_total>0 ORDER BY dias_mora DESC,vt.cod_clienteFK";
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        miCarteraLanzar('cartera_no_disponible', 'No se pudo preparar la cartera desde las cuotas vigentes.');
    }
    $items = array();
    while ($fila = $resultado->fetch_assoc()) {
        $items[] = array(
            'cod_cliente' => intval($fila['cod_clienteFK']),
            'cod_local_origen' => intval($fila['cod_local_origen']),
            'saldo_total' => (float)$fila['saldo_total'],
            'dias_mora' => intval($fila['dias_mora'])
        );
    }
    return $items;
}

function miCarteraMapaEquipo($configuracion)
{
    $gestores = array();
    foreach ($configuracion['gestores'] as $gestor) {
        $gestores[intval($gestor['cod_local'])] = intval($gestor['cod_usuario']);
    }
    $cobradores = array();
    foreach ($configuracion['cobradores'] as $cobrador) {
        $cobradores[] = intval($cobrador['cod_usuario']);
    }
    return array('gestores' => $gestores, 'cobradores' => $cobradores);
}

function miCarteraCargasCentrales($mysqli, $cobradores)
{
    $cargas = array();
    foreach ($cobradores as $usuario) {
        $cargas[intval($usuario)] = 0;
    }
    if (count($cargas) === 0) {
        return $cargas;
    }
    $resultado = $mysqli->query(
        "SELECT cod_usuario_responsableFK,COUNT(*) total FROM cartera_asignacion "
        ."WHERE estado='activa' AND tipo_responsable='cobranza_central' "
        ."AND cod_usuario_responsableFK IN (".implode(',', array_keys($cargas)).") "
        ."GROUP BY cod_usuario_responsableFK"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $cargas[intval($fila['cod_usuario_responsableFK'])] = intval($fila['total']);
    }
    return $cargas;
}

function miCarteraCobradorMenorCarga(&$cargas)
{
    if (count($cargas) === 0) {
        return 0;
    }
    asort($cargas, SORT_NUMERIC);
    $usuarios = array_keys($cargas);
    $elegido = intval($usuarios[0]);
    $cargas[$elegido]++;
    return $elegido;
}

function miCarteraPlanAsignacion($mysqli)
{
    $configuracion = miCarteraConfiguracionBase($mysqli);
    if (empty($configuracion['completa'])) {
        miCarteraLanzar(
            'equipo_incompleto',
            'Configure el jefe, al menos un gestor de clinica y un cobrador central antes de repartir la cartera.'
        );
    }
    $mapa = miCarteraMapaEquipo($configuracion);
    $cargas = miCarteraCargasCentrales($mysqli, $mapa['cobradores']);
    $candidatos = miCarteraCandidatosPendientes($mysqli);
    $existentes = array();
    $resultado = $mysqli->query("SELECT cod_clienteFK,estado FROM cartera_asignacion");
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        if ((string)$fila['estado'] === 'activa') {
            $existentes[intval($fila['cod_clienteFK'])] = true;
        }
    }
    $plan = array();
    foreach ($candidatos as $candidato) {
        if (isset($existentes[$candidato['cod_cliente']])) {
            continue;
        }
        $usuario = 0;
        $tipo = 'sin_asignar';
        $motivo = 'local_sin_gestor';
        if ($candidato['dias_mora'] >= intval($configuracion['dias_escalamiento'])) {
            $usuario = miCarteraCobradorMenorCarga($cargas);
            $tipo = $usuario > 0 ? 'cobranza_central' : 'sin_asignar';
            $motivo = $usuario > 0
                ? 'mora_'.intval($configuracion['dias_escalamiento']).'_dias'
                : 'central_sin_responsable';
        } elseif (isset($mapa['gestores'][$candidato['cod_local_origen']])) {
            $usuario = intval($mapa['gestores'][$candidato['cod_local_origen']]);
            $tipo = 'gestor_local';
            $motivo = 'local_origen';
        }
        $prioridad = $candidato['dias_mora'] >= intval($configuracion['dias_escalamiento'])
            ? 'alta' : ($candidato['dias_mora'] > 0 ? 'media' : 'baja');
        $candidato['cod_usuario'] = $usuario;
        $candidato['tipo_responsable'] = $tipo;
        $candidato['motivo'] = $motivo;
        $candidato['prioridad'] = $prioridad;
        $plan[] = $candidato;
    }
    return array('configuracion' => $configuracion, 'items' => $plan);
}

function miCarteraPrevisualizarAsignacion($mysqli, $contexto)
{
    miCarteraExigirSupervisor($contexto);
    $plan = miCarteraPlanAsignacion($mysqli);
    $resumen = array(
        'total' => count($plan['items']),
        'dias_escalamiento' => intval($plan['configuracion']['dias_escalamiento']),
        'cantidad_cobradores' => count($plan['configuracion']['cobradores']),
        'gestores_locales' => 0,
        'cobranza_central' => 0,
        'sin_asignar' => 0,
        'por_responsable' => array()
    );
    $nombres = array();
    foreach ($plan['configuracion']['gestores'] as $item) {
        $nombres[intval($item['cod_usuario'])] = $item['nombre'].' · '.$item['local'];
    }
    foreach ($plan['configuracion']['cobradores'] as $item) {
        $nombres[intval($item['cod_usuario'])] = $item['nombre'].' · Cobranza central';
    }
    $conteos = array();
    foreach ($plan['items'] as $item) {
        if ($item['tipo_responsable'] === 'gestor_local') {
            $resumen['gestores_locales']++;
        } elseif ($item['tipo_responsable'] === 'cobranza_central') {
            $resumen['cobranza_central']++;
        } else {
            $resumen['sin_asignar']++;
        }
        $usuario = intval($item['cod_usuario']);
        if ($usuario > 0) {
            if (!isset($conteos[$usuario])) {
                $conteos[$usuario] = 0;
            }
            $conteos[$usuario]++;
        }
    }
    foreach ($conteos as $usuario => $total) {
        $resumen['por_responsable'][] = array(
            'cod_usuario' => intval($usuario),
            'nombre' => isset($nombres[$usuario]) ? $nombres[$usuario] : 'Usuario de Telar',
            'total' => intval($total)
        );
    }
    usort($resumen['por_responsable'], function ($a, $b) {
        return strcmp($a['nombre'], $b['nombre']);
    });
    return $resumen;
}

function miCarteraConfirmarAsignacion($mysqli, $contexto)
{
    miCarteraExigirSupervisor($contexto);
    $plan = miCarteraPlanAsignacion($mysqli);
    $actor = intval($contexto['cod_usuario']);
    $insertados = 0;
    $sinAsignar = 0;
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare(
            "INSERT INTO cartera_asignacion "
            ."(cod_clienteFK,cod_usuario_responsableFK,cod_local_origenFK,tipo_responsable,"
            ."estado,prioridad,motivo_asignacion,cod_usuario_asignaFK,fecha_asignacion,fecha_actualizacion) "
            ."VALUES (?,?,?,?,'activa',?,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE "
            ."cod_usuario_responsableFK=IF(estado='activa',cod_usuario_responsableFK,VALUES(cod_usuario_responsableFK)),"
            ."cod_local_origenFK=IF(estado='activa',cod_local_origenFK,VALUES(cod_local_origenFK)),"
            ."tipo_responsable=IF(estado='activa',tipo_responsable,VALUES(tipo_responsable)),"
            ."prioridad=IF(estado='activa',prioridad,VALUES(prioridad)),"
            ."motivo_asignacion=IF(estado='activa',motivo_asignacion,VALUES(motivo_asignacion)),"
            ."cod_usuario_asignaFK=IF(estado='activa',cod_usuario_asignaFK,VALUES(cod_usuario_asignaFK)),"
            ."fecha_asignacion=IF(estado='activa',fecha_asignacion,NOW()),estado='activa',fecha_actualizacion=NOW()"
        );
        if (!$stmt) {
            throw new Exception('No se pudo preparar el reparto.');
        }
        foreach ($plan['items'] as $item) {
            $cliente = intval($item['cod_cliente']);
            $usuario = intval($item['cod_usuario']);
            $usuarioDb = $usuario > 0 ? $usuario : null;
            $local = intval($item['cod_local_origen']);
            $tipo = (string)$item['tipo_responsable'];
            $prioridad = (string)$item['prioridad'];
            $motivo = (string)$item['motivo'];
            $stmt->bind_param(
                'iiisssi',
                $cliente,
                $usuarioDb,
                $local,
                $tipo,
                $prioridad,
                $motivo,
                $actor
            );
            if (!$stmt->execute()) {
                throw new Exception('No se pudo asignar uno de los pacientes.');
            }
            $idAsignacion = intval($stmt->insert_id);
            if ($idAsignacion === 0) {
                $consulta = $mysqli->query(
                    "SELECT id_asignacion FROM cartera_asignacion WHERE cod_clienteFK=".$cliente." LIMIT 1"
                );
                $fila = $consulta ? $consulta->fetch_assoc() : null;
                $idAsignacion = $fila ? intval($fila['id_asignacion']) : 0;
            }
            if (!miCarteraRegistrarEvento(
                $mysqli,
                $contexto,
                'asignacion_inicial',
                'Paciente incorporado a Mi cartera.',
                $cliente,
                $idAsignacion,
                null,
                array('responsable' => $usuario, 'tipo' => $tipo, 'local' => $local)
            )) {
                throw new Exception('No se pudo auditar una asignacion.');
            }
            $insertados++;
            if ($tipo === 'sin_asignar') {
                $sinAsignar++;
            }
        }
        $stmt->close();
        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        miCarteraLanzar('asignacion_no_confirmada', $e->getMessage());
    }
    return array('asignados' => $insertados, 'sin_asignar' => $sinAsignar);
}

function miCarteraTelefonosPorClientes($mysqli, $codClientes)
{
    $ids = array_unique(array_filter(array_map('intval', $codClientes)));
    $telefonos = array();
    if (count($ids) === 0 || !miCarteraTablaExiste($mysqli, 'central_telefonica_paciente_telefono')) {
        return $telefonos;
    }
    $resultado = $mysqli->query(
        "SELECT cod_clienteFK,telefono_normalizado,fuente FROM central_telefonica_paciente_telefono "
        ."WHERE activo=1 AND cod_clienteFK IN (".implode(',', $ids).") "
        ."ORDER BY cod_clienteFK,FIELD(fuente,'principal','whatsapp','trabajo1','trabajo2'),id_telefono"
    );
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $cliente = intval($fila['cod_clienteFK']);
        if (!isset($telefonos[$cliente])) {
            $telefonos[$cliente] = array();
        }
        $telefonos[$cliente][] = array(
            'numero' => (string)$fila['telefono_normalizado'],
            'fuente' => (string)$fila['fuente']
        );
    }
    return $telefonos;
}

function miCarteraResultadoEtiqueta($resultado)
{
    $mapa = array(
        'contactado' => 'Contactado',
        'sin_respuesta' => 'No contesta',
        'numero_incorrecto' => 'Numero incorrecto',
        'promesa_pago' => 'Promesa de pago',
        'solicita_revision' => 'Solicita revision',
        'escalar_cobranza' => 'Escalar al jefe de Cobranza',
        'pago_confirmado' => 'Pago confirmado'
    );
    return isset($mapa[$resultado]) ? $mapa[$resultado] : 'Sin gestion';
}

function miCarteraPrioridadEtiqueta($prioridad)
{
    $mapa = array('alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja');
    return isset($mapa[$prioridad]) ? $mapa[$prioridad] : 'Media';
}

function miCarteraAsignacionesBase($mysqli, $contexto, $vista, $entrada)
{
    $vista = in_array($vista, array('mi_cartera', 'equipo', 'sin_asignar', 'seguimiento'), true)
        ? $vista : 'mi_cartera';
    if (($vista === 'equipo' || $vista === 'sin_asignar') && empty($contexto['puede_supervisar'])) {
        $vista = 'mi_cartera';
    }
    $condiciones = array("ca.estado='activa'");
    $tipos = '';
    $parametros = array();
    if ($vista === 'mi_cartera' || $vista === 'seguimiento') {
        $condiciones[] = 'ca.cod_usuario_responsableFK=?';
        $tipos .= 'i';
        $parametros[] = intval($contexto['cod_usuario']);
    } elseif ($vista === 'sin_asignar') {
        $condiciones[] = "ca.tipo_responsable='sin_asignar'";
    }
    $buscar = miCarteraTextoDb(isset($entrada['buscar']) ? $entrada['buscar'] : '', 100);
    if ($buscar !== '') {
        $condiciones[] = "(p.nombre_persona LIKE ? OR c.ci_cliente LIKE ? OR CAST(ca.cod_clienteFK AS CHAR) LIKE ?)";
        $como = '%'.$buscar.'%';
        $tipos .= 'sss';
        $parametros[] = $como;
        $parametros[] = $como;
        $parametros[] = $como;
    }
    $local = intval(isset($entrada['cod_local']) ? $entrada['cod_local'] : 0);
    if ($local > 0) {
        $condiciones[] = 'ca.cod_local_origenFK=?';
        $tipos .= 'i';
        $parametros[] = $local;
    }
    $responsable = intval(isset($entrada['cod_responsable']) ? $entrada['cod_responsable'] : 0);
    if ($responsable > 0 && !empty($contexto['puede_supervisar'])) {
        $condiciones[] = 'ca.cod_usuario_responsableFK=?';
        $tipos .= 'i';
        $parametros[] = $responsable;
    }
    $prioridad = isset($entrada['prioridad']) ? (string)$entrada['prioridad'] : '';
    if (in_array($prioridad, array('alta', 'media', 'baja'), true)) {
        $condiciones[] = 'ca.prioridad=?';
        $tipos .= 's';
        $parametros[] = $prioridad;
    }
    $sql = "SELECT ca.id_asignacion,ca.cod_clienteFK,ca.cod_usuario_responsableFK,"
        ."ca.cod_local_origenFK,ca.tipo_responsable,ca.prioridad,ca.motivo_asignacion,"
        ."ca.fecha_asignacion,p.nombre_persona paciente,c.ci_cliente documento,"
        ."IFNULL(l.Nombre,'') local_origen,COALESCE(NULLIF(pr.nombre_persona,''),u.login,'') responsable,"
        ."IFNULL(u.url,'') avatar_responsable,IFNULL(g.resultado,'') ultimo_resultado,"
        ."IFNULL(g.nota,'') ultima_nota,g.fecha_gestion ultima_gestion,g.fecha_proxima_accion,"
        ."IFNULL(cp.estado,'') compromiso_estado,cp.fecha_compromiso,cp.monto_comprometido "
        ."FROM cartera_asignacion ca INNER JOIN cliente c ON c.cod_cliente=ca.cod_clienteFK "
        ."INNER JOIN persona p ON p.cod_persona=c.cod_cliente "
        ."LEFT JOIN local l ON l.cod_local=ca.cod_local_origenFK "
        ."LEFT JOIN usuario u ON u.cod_usuario=ca.cod_usuario_responsableFK "
        ."LEFT JOIN persona pr ON pr.cod_persona=u.cod_usuario "
        ."LEFT JOIN cartera_gestion g ON g.id_gestion=(SELECT MAX(g2.id_gestion) FROM cartera_gestion g2 WHERE g2.id_asignacionFK=ca.id_asignacion) "
        ."LEFT JOIN cartera_compromiso cp ON cp.id_compromiso=(SELECT MAX(cp2.id_compromiso) FROM cartera_compromiso cp2 WHERE cp2.id_asignacionFK=ca.id_asignacion) "
        ."WHERE ".implode(' AND ', $condiciones)." ORDER BY ca.prioridad='alta' DESC,ca.fecha_actualizacion ASC LIMIT 5000";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        miCarteraLanzar('cartera_no_disponible', 'No se pudo preparar el listado de cartera.');
    }
    miCarteraBind($stmt, $tipos, $parametros);
    if (!$stmt->execute()) {
        $stmt->close();
        miCarteraLanzar('cartera_no_disponible', 'No se pudo consultar el listado de cartera.');
    }
    $resultado = $stmt->get_result();
    $filas = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $filas[] = $fila;
    }
    $stmt->close();
    return array('vista' => $vista, 'filas' => $filas);
}

function miCarteraMontoRecuperadoMes($mysqli, $contexto, $vista)
{
    $condicion = '';
    if ($vista === 'mi_cartera' || $vista === 'seguimiento' || empty($contexto['puede_supervisar'])) {
        $condicion = ' AND ca.cod_usuario_responsableFK='.intval($contexto['cod_usuario']);
    }
    $sql = "SELECT IFNULL(SUM(pg.Monto),0) total FROM pago pg "
        ."INNER JOIN venta vt ON vt.cod_venta=pg.cod_venta_fk "
        ."INNER JOIN cartera_asignacion ca ON ca.cod_clienteFK=vt.cod_clienteFK "
        ."WHERE ca.estado='activa' ".$condicion." AND ".miCarteraPagoActivoSql('pg')." "
        ."AND pg.Fecha>=DATE_FORMAT(CURDATE(),'%Y-%m-01') "
        ."AND pg.Fecha>=DATE(ca.fecha_asignacion) AND pg.Tipo IN ('Pago Cuota','Interes')";
    $resultado = $mysqli->query($sql);
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    return $fila ? (float)$fila['total'] : 0;
}

function miCarteraListar($mysqli, $contexto, $entrada)
{
    $vistaSolicitada = isset($entrada['vista']) ? (string)$entrada['vista'] : 'mi_cartera';
    $base = miCarteraAsignacionesBase($mysqli, $contexto, $vistaSolicitada, $entrada);
    $clientes = array();
    foreach ($base['filas'] as $fila) {
        $clientes[] = intval($fila['cod_clienteFK']);
    }
    $finanzas = miCarteraResumenesFinancieros($mysqli, $clientes);
    $telefonos = miCarteraTelefonosPorClientes($mysqli, $clientes);
    $filtroEstado = isset($entrada['estado']) ? (string)$entrada['estado'] : '';
    $items = array();
    $kpis = array(
        'pacientes' => 0,
        'saldo_total' => 0,
        'urgentes' => 0,
        'promesas_hoy' => 0,
        'sin_contacto' => 0,
        'recuperado_mes' => miCarteraMontoRecuperadoMes($mysqli, $contexto, $base['vista'])
    );
    foreach ($base['filas'] as $fila) {
        $cliente = intval($fila['cod_clienteFK']);
        $resumen = isset($finanzas[$cliente]) ? $finanzas[$cliente] : array();
        $saldo = isset($resumen['saldo_total']) ? (float)$resumen['saldo_total'] : 0;
        $dias = isset($resumen['dias_mora']) ? intval($resumen['dias_mora']) : 0;
        $resultado = (string)$fila['ultimo_resultado'];
        if ($saldo <= 0) {
            $resultado = 'pago_confirmado';
        }
        $compromisoEstado = (string)$fila['compromiso_estado'];
        $estado = $saldo <= 0 ? 'pagado'
            : ($compromisoEstado === 'incumplido' || $dias >= 30 ? 'urgente'
            : ($dias > 0 ? 'vencido' : 'preventivo'));
        if ($filtroEstado !== '' && $filtroEstado !== $estado) {
            continue;
        }
        if ($base['vista'] === 'seguimiento') {
            $proxima = (string)$fila['fecha_proxima_accion'];
            if ($proxima !== '' && substr($proxima, 0, 10) > date('Y-m-d')) {
                continue;
            }
        }
        $kpis['pacientes']++;
        $kpis['saldo_total'] += $saldo;
        if ($estado === 'urgente') {
            $kpis['urgentes']++;
        }
        if ((string)$fila['fecha_compromiso'] === date('Y-m-d')
            && $compromisoEstado === 'vigente') {
            $kpis['promesas_hoy']++;
        }
        if ($resultado === '' || $resultado === 'sin_respuesta') {
            $kpis['sin_contacto']++;
        }
        $items[] = array(
            'id_asignacion' => intval($fila['id_asignacion']),
            'cod_cliente' => $cliente,
            'paciente' => (string)$fila['paciente'],
            'documento' => (string)$fila['documento'],
            'cod_local_origen' => intval($fila['cod_local_origenFK']),
            'local_origen' => (string)$fila['local_origen'],
            'tipo_responsable' => (string)$fila['tipo_responsable'],
            'cod_responsable' => intval($fila['cod_usuario_responsableFK']),
            'responsable' => (string)$fila['responsable'],
            'avatar_responsable' => trim((string)$fila['avatar_responsable']) !== ''
                ? (string)$fila['avatar_responsable'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
            'prioridad' => (string)$fila['prioridad'],
            'prioridad_etiqueta' => miCarteraPrioridadEtiqueta((string)$fila['prioridad']),
            'resultado' => $resultado,
            'resultado_etiqueta' => miCarteraResultadoEtiqueta($resultado),
            'ultima_nota' => (string)$fila['ultima_nota'],
            'ultima_gestion' => (string)$fila['ultima_gestion'],
            'proxima_accion' => (string)$fila['fecha_proxima_accion'],
            'compromiso' => array(
                'estado' => $compromisoEstado,
                'fecha' => (string)$fila['fecha_compromiso'],
                'monto' => (float)$fila['monto_comprometido']
            ),
            'estado' => $estado,
            'finanzas' => $resumen,
            'telefonos' => isset($telefonos[$cliente]) ? $telefonos[$cliente] : array()
        );
    }
    $total = count($items);
    $pagina = max(1, intval(isset($entrada['pagina']) ? $entrada['pagina'] : 1));
    $porPagina = intval(isset($entrada['por_pagina']) ? $entrada['por_pagina'] : 15);
    if (!in_array($porPagina, array(10, 15, 25, 50), true)) {
        $porPagina = 15;
    }
    $paginas = max(1, intval(ceil($total / $porPagina)));
    if ($pagina > $paginas) {
        $pagina = $paginas;
    }
    $items = array_slice($items, ($pagina - 1) * $porPagina, $porPagina);
    return array(
        'vista' => $base['vista'],
        'items' => $items,
        'total' => $total,
        'pagina' => $pagina,
        'paginas' => $paginas,
        'por_pagina' => $porPagina,
        'kpis' => $kpis
    );
}

function miCarteraAsignacionVisible($mysqli, $contexto, $idAsignacion, $exigirResponsable)
{
    $stmt = $mysqli->prepare(
        "SELECT id_asignacion,cod_clienteFK,cod_usuario_responsableFK,cod_local_origenFK,"
        ."tipo_responsable,estado,prioridad,motivo_asignacion,fecha_asignacion "
        ."FROM cartera_asignacion WHERE id_asignacion=? LIMIT 1"
    );
    if (!$stmt) {
        miCarteraLanzar('asignacion_no_disponible', 'No se pudo validar la asignacion.');
    }
    $idAsignacion = intval($idAsignacion);
    $stmt->bind_param('i', $idAsignacion);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    if (!$fila || (string)$fila['estado'] !== 'activa') {
        miCarteraLanzar('asignacion_no_disponible', 'La asignacion ya no esta activa.');
    }
    $esResponsable = intval($fila['cod_usuario_responsableFK']) === intval($contexto['cod_usuario']);
    if (!$esResponsable && empty($contexto['puede_supervisar'])) {
        miCarteraLanzar('accion_no_autorizada', 'El paciente pertenece a la cartera de otro responsable.');
    }
    if ($exigirResponsable && intval($fila['cod_usuario_responsableFK']) <= 0) {
        miCarteraLanzar('asignacion_sin_responsable', 'Asigne un responsable antes de registrar gestiones.');
    }
    return $fila;
}

function miCarteraDetalle($mysqli, $contexto, $idAsignacion)
{
    $asignacion = miCarteraAsignacionVisible($mysqli, $contexto, $idAsignacion, false);
    $cliente = intval($asignacion['cod_clienteFK']);
    $saldo = miCarteraSaldoSql('cr', 'pagos');
    $sql = "SELECT vt.cod_venta,IFNULL(vt.num_factura,'') num_factura,IFNULL(vt.puntoexpedicion,'') puntoexpedicion,"
        ."IFNULL(l.Nombre,'') local_nombre,COUNT(DISTINCT cr.idcredito) cuotas,"
        ."SUM(CASE WHEN ".$saldo.">0 THEN ".$saldo." ELSE 0 END) saldo,"
        ."MIN(CASE WHEN ".$saldo.">0 THEN cr.fechapago ELSE NULL END) proximo_vencimiento,"
        ."IFNULL((SELECT GROUP_CONCAT(DISTINCT pr.nombre_producto ORDER BY pr.nombre_producto SEPARATOR ', ') "
        ."FROM detalle_venta dv LEFT JOIN producto pr ON pr.cod_producto=dv.cod_productoFK "
        ."WHERE dv.cod_ventaFK=vt.cod_venta),'') productos "
        ."FROM venta vt INNER JOIN credito cr ON cr.cod_venta=vt.cod_venta "
        .miCarteraPagoAgrupadoSql()
        ."LEFT JOIN local l ON l.cod_local=vt.cod_local WHERE vt.cod_clienteFK=".$cliente." "
        ."AND IFNULL(vt.anulado,'')='' AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado' "
        ."GROUP BY vt.cod_venta,vt.num_factura,vt.puntoexpedicion,l.Nombre HAVING saldo>0 "
        ."ORDER BY proximo_vencimiento,vt.cod_venta";
    $resultado = $mysqli->query($sql);
    $ventas = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $visible = trim((string)$fila['puntoexpedicion']) !== ''
            ? $fila['puntoexpedicion'].'-'.$fila['num_factura']
            : (trim((string)$fila['num_factura']) !== '' ? $fila['num_factura'] : $fila['cod_venta']);
        $ventas[] = array(
            'cod_venta' => intval($fila['cod_venta']),
            'venta' => (string)$visible,
            'local' => (string)$fila['local_nombre'],
            'productos' => (string)$fila['productos'],
            'cuotas' => intval($fila['cuotas']),
            'saldo' => (float)$fila['saldo'],
            'proximo_vencimiento' => (string)$fila['proximo_vencimiento']
        );
    }
    $stmt = $mysqli->prepare(
        "SELECT g.id_gestion,g.resultado,g.telefono_normalizado,g.nota,g.fecha_proxima_accion,"
        ."g.fecha_gestion,COALESCE(NULLIF(p.nombre_persona,''),u.login) usuario,IFNULL(u.url,'') avatar "
        ."FROM cartera_gestion g INNER JOIN usuario u ON u.cod_usuario=g.cod_usuarioFK "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario WHERE g.id_asignacionFK=? "
        ."ORDER BY g.id_gestion DESC LIMIT 30"
    );
    $gestiones = array();
    if ($stmt) {
        $stmt->bind_param('i', $idAsignacion);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                $gestiones[] = array(
                    'id_gestion' => intval($fila['id_gestion']),
                    'resultado' => (string)$fila['resultado'],
                    'resultado_etiqueta' => miCarteraResultadoEtiqueta((string)$fila['resultado']),
                    'telefono' => (string)$fila['telefono_normalizado'],
                    'nota' => (string)$fila['nota'],
                    'proxima_accion' => (string)$fila['fecha_proxima_accion'],
                    'fecha' => (string)$fila['fecha_gestion'],
                    'usuario' => (string)$fila['usuario'],
                    'avatar' => trim((string)$fila['avatar']) !== ''
                        ? (string)$fila['avatar'] : '/GoodVentaAsisCap/iconos/sinperfil.png'
                );
            }
        }
        $stmt->close();
    }
    $resumen = miCarteraResumenesFinancieros($mysqli, array($cliente));
    $telefonos = miCarteraTelefonosPorClientes($mysqli, array($cliente));
    return array(
        'asignacion' => $asignacion,
        'finanzas' => isset($resumen[$cliente]) ? $resumen[$cliente] : array(),
        'telefonos' => isset($telefonos[$cliente]) ? $telefonos[$cliente] : array(),
        'ventas' => $ventas,
        'gestiones' => $gestiones
    );
}

function miCarteraTotalPagadoCliente($mysqli, $codCliente)
{
    $codCliente = intval($codCliente);
    $sql = "SELECT IFNULL(SUM(pg.Monto),0) total FROM pago pg "
        ."INNER JOIN venta vt ON vt.cod_venta=pg.cod_venta_fk "
        ."WHERE vt.cod_clienteFK=".$codCliente." AND ".miCarteraPagoActivoSql('pg')." "
        ."AND pg.Tipo IN ('Pago Cuota','Interes')";
    $resultado = $mysqli->query($sql);
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    return $fila ? (float)$fila['total'] : 0;
}

function miCarteraElegirCobradorCentral($mysqli)
{
    $configuracion = miCarteraConfiguracionBase($mysqli);
    $mapa = miCarteraMapaEquipo($configuracion);
    $cargas = miCarteraCargasCentrales($mysqli, $mapa['cobradores']);
    return miCarteraCobradorMenorCarga($cargas);
}

function miCarteraEscalarAsignacion($mysqli, $contexto, $asignacion, $motivo)
{
    if ((string)$asignacion['tipo_responsable'] === 'cobranza_central') {
        return intval($asignacion['cod_usuario_responsableFK']);
    }
    $nuevoResponsable = miCarteraElegirCobradorCentral($mysqli);
    if ($nuevoResponsable <= 0) {
        miCarteraLanzar(
            'cobranza_sin_responsable',
            'No hay cobradores centrales configurados para recibir el caso.'
        );
    }
    $idAsignacion = intval($asignacion['id_asignacion']);
    $stmt = $mysqli->prepare(
        "UPDATE cartera_asignacion SET cod_usuario_responsableFK=?,"
        ."tipo_responsable='cobranza_central',prioridad='alta',motivo_asignacion=?,"
        ."fecha_actualizacion=NOW() WHERE id_asignacion=? AND estado='activa'"
    );
    if (!$stmt) {
        miCarteraLanzar('escalamiento_no_guardado', 'No se pudo preparar el escalamiento.');
    }
    $motivoDb = miCarteraTextoDb($motivo, 80);
    $stmt->bind_param('isi', $nuevoResponsable, $motivoDb, $idAsignacion);
    if (!$stmt->execute()) {
        $stmt->close();
        miCarteraLanzar('escalamiento_no_guardado', 'No se pudo transferir el caso a Cobranza central.');
    }
    $stmt->close();
    if (!miCarteraRegistrarEvento(
        $mysqli,
        $contexto,
        'escalamiento_cobranza',
        'El caso paso a Cobranza central.',
        intval($asignacion['cod_clienteFK']),
        $idAsignacion,
        array(
            'responsable' => intval($asignacion['cod_usuario_responsableFK']),
            'tipo' => (string)$asignacion['tipo_responsable']
        ),
        array('responsable' => $nuevoResponsable, 'tipo' => 'cobranza_central', 'motivo' => $motivo)
    )) {
        miCarteraLanzar('escalamiento_no_guardado', 'No se pudo auditar el escalamiento.');
    }
    return $nuevoResponsable;
}

function miCarteraAsignarAJefe($mysqli, $contexto, $asignacion, $motivo)
{
    $configuracion = miCarteraConfiguracionBase($mysqli);
    $nuevoResponsable = intval($configuracion['cod_jefe']);
    if ($nuevoResponsable <= 0) {
        miCarteraLanzar(
            'jefe_sin_configurar',
            'Configure un jefe de Cobranza para recibir los casos especiales.'
        );
    }
    if ((string)$asignacion['tipo_responsable'] === 'jefe_cobranza'
        && intval($asignacion['cod_usuario_responsableFK']) === $nuevoResponsable) {
        return $nuevoResponsable;
    }
    $idAsignacion = intval($asignacion['id_asignacion']);
    $motivoDb = miCarteraTextoDb($motivo, 80);
    $stmt = $mysqli->prepare(
        "UPDATE cartera_asignacion SET cod_usuario_responsableFK=?,"
        ."tipo_responsable='jefe_cobranza',prioridad='alta',motivo_asignacion=?,"
        ."fecha_actualizacion=NOW() WHERE id_asignacion=? AND estado='activa'"
    );
    if (!$stmt) {
        miCarteraLanzar('asignacion_jefe_no_guardada', 'No se pudo preparar el caso especial del jefe.');
    }
    $stmt->bind_param('isi', $nuevoResponsable, $motivoDb, $idAsignacion);
    if (!$stmt->execute()) {
        $stmt->close();
        miCarteraLanzar('asignacion_jefe_no_guardada', 'No se pudo transferir el caso al jefe de Cobranza.');
    }
    $stmt->close();
    if (!miCarteraRegistrarEvento(
        $mysqli,
        $contexto,
        'asignacion_jefe_cobranza',
        'El jefe de Cobranza asumio un caso especial.',
        intval($asignacion['cod_clienteFK']),
        $idAsignacion,
        array(
            'responsable' => intval($asignacion['cod_usuario_responsableFK']),
            'tipo' => (string)$asignacion['tipo_responsable']
        ),
        array('responsable' => $nuevoResponsable, 'tipo' => 'jefe_cobranza', 'motivo' => $motivo)
    )) {
        miCarteraLanzar('asignacion_jefe_no_guardada', 'No se pudo auditar el caso especial del jefe.');
    }
    return $nuevoResponsable;
}

function miCarteraTomarCasoJefe($mysqli, $contexto, $idAsignacion)
{
    if (empty($contexto['es_jefe'])) {
        miCarteraLanzar(
            'accion_no_autorizada',
            'Solo el jefe de Cobranza puede tomar un caso para su cartera especial.'
        );
    }
    $asignacion = miCarteraAsignacionVisible($mysqli, $contexto, $idAsignacion, false);
    $mysqli->begin_transaction();
    try {
        $responsable = miCarteraAsignarAJefe($mysqli, $contexto, $asignacion, 'toma_jefe');
        $mysqli->commit();
        return array('id_asignacion' => intval($idAsignacion), 'cod_responsable' => $responsable);
    } catch (MiCarteraExcepcion $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Exception $e) {
        $mysqli->rollback();
        miCarteraLanzar('asignacion_jefe_no_guardada', $e->getMessage());
    }
}

function miCarteraGuardarGestion($mysqli, $contexto, $entrada)
{
    $idAsignacion = intval(isset($entrada['id_asignacion']) ? $entrada['id_asignacion'] : 0);
    $asignacion = miCarteraAsignacionVisible($mysqli, $contexto, $idAsignacion, true);
    $permitidos = array(
        'contactado',
        'sin_respuesta',
        'numero_incorrecto',
        'promesa_pago',
        'solicita_revision',
        'escalar_cobranza'
    );
    $resultadoGestion = isset($entrada['resultado']) ? (string)$entrada['resultado'] : '';
    if (!in_array($resultadoGestion, $permitidos, true)) {
        miCarteraLanzar('resultado_invalido', 'Seleccione el resultado real de la llamada.');
    }
    $nota = miCarteraTextoDb(isset($entrada['nota']) ? $entrada['nota'] : '', 1000);
    $proxima = miCarteraTexto(isset($entrada['proxima_accion']) ? $entrada['proxima_accion'] : '', 19);
    $fechaCompromisoEntrada = '';
    $montoCompromisoEntrada = 0;
    if ($resultadoGestion === 'promesa_pago') {
        $fechaCompromisoEntrada = miCarteraTexto(
            isset($entrada['fecha_compromiso']) ? $entrada['fecha_compromiso'] : '',
            10
        );
        $montoCompromisoEntrada = (float)(
            isset($entrada['monto_compromiso']) ? $entrada['monto_compromiso'] : 0
        );
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCompromisoEntrada)
            || $fechaCompromisoEntrada < date('Y-m-d') || $montoCompromisoEntrada <= 0) {
            miCarteraLanzar(
                'promesa_invalida',
                'Indique una fecha vigente y un monto positivo para la promesa.'
            );
        }
        if ($proxima === '') {
            $proxima = $fechaCompromisoEntrada.' 09:00:00';
        }
    }
    if ($proxima === '') {
        miCarteraLanzar('proxima_accion_requerida', 'Defina cuando se debe retomar el seguimiento.');
    }
    if ($proxima !== '' && strtotime($proxima) === false) {
        miCarteraLanzar('proxima_accion_invalida', 'La fecha de la proxima accion no es valida.');
    }
    $telefono = centralTelefonicaNormalizarTelefono(
        isset($entrada['telefono']) ? $entrada['telefono'] : ''
    );
    if ($telefono !== '') {
        $stmt = $mysqli->prepare(
            "SELECT 1 FROM central_telefonica_paciente_telefono "
            ."WHERE cod_clienteFK=? AND telefono_normalizado=? AND activo=1 LIMIT 1"
        );
        if (!$stmt) {
            miCarteraLanzar('telefono_invalido', 'No se pudo validar el telefono seleccionado.');
        }
        $cliente = intval($asignacion['cod_clienteFK']);
        $stmt->bind_param('is', $cliente, $telefono);
        $valido = $stmt->execute() && $stmt->get_result()->num_rows === 1;
        $stmt->close();
        if (!$valido) {
            miCarteraLanzar('telefono_invalido', 'El telefono seleccionado ya no figura entre los datos vigentes.');
        }
    }
    $idSolicitud = intval(isset($entrada['id_solicitud']) ? $entrada['id_solicitud'] : 0);
    if ($idSolicitud > 0) {
        $stmt = $mysqli->prepare(
            "SELECT 1 FROM central_telefonica_solicitud_llamada "
            ."WHERE id_solicitud=? AND cod_clienteFK=? AND cod_usuarioFK=? LIMIT 1"
        );
        if (!$stmt) {
            miCarteraLanzar('llamada_invalida', 'No se pudo validar la llamada vinculada.');
        }
        $cliente = intval($asignacion['cod_clienteFK']);
        $actor = intval($contexto['cod_usuario']);
        $stmt->bind_param('iii', $idSolicitud, $cliente, $actor);
        $valido = $stmt->execute() && $stmt->get_result()->num_rows === 1;
        $stmt->close();
        if (!$valido) {
            miCarteraLanzar('llamada_invalida', 'La llamada no corresponde a este seguimiento.');
        }
    } else {
        $idSolicitud = null;
    }
    $prioridad = isset($entrada['prioridad']) ? (string)$entrada['prioridad'] : (string)$asignacion['prioridad'];
    if (!in_array($prioridad, array('alta', 'media', 'baja'), true)) {
        $prioridad = (string)$asignacion['prioridad'];
    }

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare(
            "INSERT INTO cartera_gestion "
            ."(id_asignacionFK,cod_clienteFK,cod_usuarioFK,tipo,resultado,telefono_normalizado,"
            ."id_solicitud_llamadaFK,nota,fecha_proxima_accion,fecha_gestion) "
            ."VALUES (?,?,?,'llamada',?,?,?,?,?,NOW())"
        );
        if (!$stmt) {
            throw new Exception('No se pudo preparar la gestion.');
        }
        $cliente = intval($asignacion['cod_clienteFK']);
        $actor = intval($contexto['cod_usuario']);
        $proximaDb = $proxima !== '' ? $proxima : null;
        $stmt->bind_param(
            'iiississ',
            $idAsignacion,
            $cliente,
            $actor,
            $resultadoGestion,
            $telefono,
            $idSolicitud,
            $nota,
            $proximaDb
        );
        if (!$stmt->execute()) {
            throw new Exception('No se pudo guardar el resultado de la llamada.');
        }
        $idGestion = intval($stmt->insert_id);
        $stmt->close();
        $stmt = $mysqli->prepare(
            "UPDATE cartera_asignacion SET prioridad=?,fecha_actualizacion=NOW() WHERE id_asignacion=?"
        );
        $stmt->bind_param('si', $prioridad, $idAsignacion);
        if (!$stmt->execute()) {
            throw new Exception('No se pudo actualizar la prioridad.');
        }
        $stmt->close();

        if ($resultadoGestion === 'promesa_pago') {
            $fechaCompromiso = $fechaCompromisoEntrada;
            $monto = $montoCompromisoEntrada;
            $resumen = miCarteraResumenesFinancieros($mysqli, array($cliente));
            $saldo = isset($resumen[$cliente]) ? (float)$resumen[$cliente]['saldo_total'] : 0;
            if ($monto > $saldo + 0.01) {
                throw new Exception('El monto prometido no puede superar el saldo actual.');
            }
            $basePagada = miCarteraTotalPagadoCliente($mysqli, $cliente);
            $stmt = $mysqli->prepare(
                "INSERT INTO cartera_compromiso "
                ."(id_asignacionFK,id_gestion_origenFK,cod_clienteFK,cod_usuarioFK,"
                ."fecha_compromiso,monto_comprometido,monto_pagado_base,estado,"
                ."fecha_creacion,fecha_actualizacion) VALUES (?,?,?,?,?,?,?,'vigente',NOW(),NOW())"
            );
            if (!$stmt) {
                throw new Exception('No se pudo preparar la promesa de pago.');
            }
            $stmt->bind_param(
                'iiiisdd',
                $idAsignacion,
                $idGestion,
                $cliente,
                $actor,
                $fechaCompromiso,
                $monto,
                $basePagada
            );
            if (!$stmt->execute()) {
                throw new Exception('No se pudo guardar la promesa de pago.');
            }
            $stmt->close();
        }
        if ($resultadoGestion === 'escalar_cobranza') {
            miCarteraAsignarAJefe($mysqli, $contexto, $asignacion, 'escalamiento_manual');
        } elseif ($resultadoGestion === 'solicita_revision') {
            miCarteraAsignarAJefe($mysqli, $contexto, $asignacion, 'solicita_revision');
        } elseif ($resultadoGestion === 'sin_respuesta') {
            $stmt = $mysqli->prepare(
                "SELECT COUNT(*) total FROM cartera_gestion WHERE id_asignacionFK=? "
                ."AND resultado='sin_respuesta' AND fecha_gestion>=?"
            );
            if (!$stmt) {
                throw new Exception('No se pudo revisar la cantidad de intentos.');
            }
            $desde = (string)$asignacion['fecha_asignacion'];
            $stmt->bind_param('is', $idAsignacion, $desde);
            $stmt->execute();
            $fila = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $configuracion = miCarteraConfiguracionBase($mysqli);
            if (intval($fila['total']) >= intval($configuracion['intentos_escalamiento'])) {
                miCarteraEscalarAsignacion($mysqli, $contexto, $asignacion, 'dos_intentos_sin_respuesta');
            }
        }
        if (!miCarteraRegistrarEvento(
            $mysqli,
            $contexto,
            'gestion_llamada',
            'Se registro el resultado y la siguiente accion.',
            $cliente,
            $idAsignacion,
            null,
            array(
                'resultado' => $resultadoGestion,
                'proxima_accion' => $proxima,
                'prioridad' => $prioridad,
                'id_solicitud' => $idSolicitud
            )
        )) {
            throw new Exception('No se pudo registrar la auditoria de la gestion.');
        }
        $mysqli->commit();
    } catch (MiCarteraExcepcion $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Exception $e) {
        $mysqli->rollback();
        miCarteraLanzar('gestion_no_guardada', $e->getMessage());
    }
    return array('id_gestion' => $idGestion, 'resultado' => $resultadoGestion);
}

function miCarteraSincronizar($mysqli, $contexto)
{
    $cumplidos = 0;
    $incumplidos = 0;
    $escalados = 0;
    $mysqli->begin_transaction();
    try {
    $stmt = $mysqli->prepare(
        "SELECT cp.id_compromiso,cp.id_asignacionFK,cp.cod_clienteFK,cp.fecha_compromiso,"
        ."cp.monto_comprometido,cp.monto_pagado_base,ca.cod_usuario_responsableFK,"
        ."ca.cod_local_origenFK,ca.tipo_responsable,ca.estado,ca.prioridad,ca.motivo_asignacion,ca.fecha_asignacion "
        ."FROM cartera_compromiso cp INNER JOIN cartera_asignacion ca ON ca.id_asignacion=cp.id_asignacionFK "
        ."WHERE cp.estado='vigente' AND ca.estado='activa' ORDER BY cp.fecha_compromiso LIMIT 500"
    );
    $promesas = array();
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) { $stmt->close(); }
        throw new Exception('No se pudieron revisar las promesas vigentes.');
    }
    if ($stmt) {
        $resultado = $stmt->get_result();
        while ($resultado && ($fila = $resultado->fetch_assoc())) {
            $promesas[] = $fila;
        }
    }
    if ($stmt) {
        $stmt->close();
    }
    foreach ($promesas as $promesa) {
        $pagado = miCarteraTotalPagadoCliente($mysqli, intval($promesa['cod_clienteFK']));
        $avance = max(0, $pagado - (float)$promesa['monto_pagado_base']);
        $resumen = miCarteraResumenesFinancieros($mysqli, array(intval($promesa['cod_clienteFK'])));
        $saldo = isset($resumen[intval($promesa['cod_clienteFK'])])
            ? (float)$resumen[intval($promesa['cod_clienteFK'])]['saldo_total'] : 0;
        if ($saldo <= 0 || $avance + 0.01 >= (float)$promesa['monto_comprometido']) {
            $actualizado = $mysqli->query(
                "UPDATE cartera_compromiso SET estado='cumplido',fecha_resolucion=NOW(),"
                ."fecha_actualizacion=NOW() WHERE id_compromiso=".intval($promesa['id_compromiso'])
                ." AND estado='vigente'"
            );
            $huboCambio = $actualizado && $mysqli->affected_rows > 0;
            if ($huboCambio) {
                if (!miCarteraRegistrarEvento(
                    $mysqli,
                    $contexto,
                    'promesa_cumplida',
                    'Telar confirmo el compromiso contra pagos reales.',
                    intval($promesa['cod_clienteFK']),
                    intval($promesa['id_asignacionFK']),
                    array('estado' => 'vigente'),
                    array('estado' => 'cumplido', 'pago_detectado' => $avance, 'saldo' => $saldo)
                )) {
                    throw new Exception('No se pudo auditar una promesa cumplida.');
                }
                $cumplidos++;
            } elseif (!$actualizado) {
                throw new Exception('No se pudo actualizar una promesa cumplida.');
            }
        } elseif ((string)$promesa['fecha_compromiso'] < date('Y-m-d')) {
            $actualizado = $mysqli->query(
                "UPDATE cartera_compromiso SET estado='incumplido',fecha_resolucion=NOW(),"
                ."fecha_actualizacion=NOW() WHERE id_compromiso=".intval($promesa['id_compromiso'])
                ." AND estado='vigente'"
            );
            $huboCambio = $actualizado && $mysqli->affected_rows > 0;
            if ($huboCambio) {
                if (!miCarteraRegistrarEvento(
                    $mysqli,
                    $contexto,
                    'promesa_incumplida',
                    'La fecha prometida vencio sin cubrir el monto comprometido.',
                    intval($promesa['cod_clienteFK']),
                    intval($promesa['id_asignacionFK']),
                    array('estado' => 'vigente'),
                    array('estado' => 'incumplido', 'pago_detectado' => $avance)
                )) {
                    throw new Exception('No se pudo auditar una promesa incumplida.');
                }
                $incumplidos++;
            } elseif (!$actualizado) {
                throw new Exception('No se pudo actualizar una promesa incumplida.');
            }
            if ($huboCambio && (string)$promesa['tipo_responsable'] !== 'jefe_cobranza') {
                miCarteraAsignarAJefe($mysqli, $contexto, array(
                    'id_asignacion' => intval($promesa['id_asignacionFK']),
                    'cod_clienteFK' => intval($promesa['cod_clienteFK']),
                    'cod_usuario_responsableFK' => intval($promesa['cod_usuario_responsableFK']),
                    'cod_local_origenFK' => intval($promesa['cod_local_origenFK']),
                    'tipo_responsable' => (string)$promesa['tipo_responsable']
                ), 'promesa_incumplida');
                $escalados++;
            }
        }
    }

    $resultado = $mysqli->query(
        "SELECT id_asignacion,cod_clienteFK,cod_usuario_responsableFK,cod_local_origenFK,"
        ."tipo_responsable,estado,prioridad,motivo_asignacion,fecha_asignacion "
        ."FROM cartera_asignacion WHERE estado='activa' AND tipo_responsable='gestor_local' LIMIT 2000"
    );
    if (!$resultado) {
        throw new Exception('No se pudieron revisar los casos con mora prolongada.');
    }
    $asignaciones = array();
    $clientes = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $asignaciones[] = $fila;
        $clientes[] = intval($fila['cod_clienteFK']);
    }
    $finanzas = miCarteraResumenesFinancieros($mysqli, $clientes);
    $configuracion = miCarteraConfiguracionBase($mysqli);
    foreach ($asignaciones as $asignacion) {
        $cliente = intval($asignacion['cod_clienteFK']);
        $dias = isset($finanzas[$cliente]) ? intval($finanzas[$cliente]['dias_mora']) : 0;
        if ($dias >= intval($configuracion['dias_escalamiento'])) {
                miCarteraEscalarAsignacion(
                    $mysqli,
                    $contexto,
                    $asignacion,
                    'mora_'.intval($configuracion['dias_escalamiento']).'_dias'
                );
            $escalados++;
        }
    }
    $mysqli->commit();
    return array('promesas_cumplidas' => $cumplidos, 'promesas_incumplidas' => $incumplidos, 'escalados' => $escalados);
    } catch (MiCarteraExcepcion $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Exception $e) {
        $mysqli->rollback();
        miCarteraLanzar('sincronizacion_no_completada', $e->getMessage());
    }
}

function miCarteraContextoModulo($mysqli, $contexto)
{
    $configuracion = miCarteraConfiguracionBase($mysqli);
    $servicio = centralTelefonicaOperacionEstructuraDisponible($mysqli)
        ? centralTelefonicaOperacionServicio($mysqli)
        : array('activo' => false, 'origenacion_disponible' => false, 'mensaje' => 'Telefonia no instalada.');
    $extension = '';
    if (centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
        $stmt = $mysqli->prepare(
            "SELECT extension FROM central_telefonica_directorio WHERE activo=1 "
            ."AND cod_usuarioFK=? ORDER BY extension LIMIT 1"
        );
        $usuario = intval($contexto['cod_usuario']);
        if ($stmt) {
            $stmt->bind_param('i', $usuario);
            if ($stmt->execute()) {
                $fila = $stmt->get_result()->fetch_assoc();
                $extension = $fila ? (string)$fila['extension'] : '';
            }
            $stmt->close();
        }
    }
    return array(
        'usuario' => $contexto,
        'configuracion' => $configuracion,
        'telefonia' => array(
            'activo' => !empty($servicio['activo']) && !empty($servicio['origenacion_disponible']),
            'mensaje' => isset($servicio['mensaje']) ? (string)$servicio['mensaje'] : '',
            'extension' => $extension
        ),
        'resultados' => array(
            array('valor' => 'contactado', 'etiqueta' => 'Contactado'),
            array('valor' => 'sin_respuesta', 'etiqueta' => 'No contesta'),
            array('valor' => 'numero_incorrecto', 'etiqueta' => 'Numero incorrecto'),
            array('valor' => 'promesa_pago', 'etiqueta' => 'Promesa de pago'),
            array('valor' => 'solicita_revision', 'etiqueta' => 'Solicita revision'),
            array('valor' => 'escalar_cobranza', 'etiqueta' => 'Escalar al jefe de Cobranza')
        )
    );
}

?>

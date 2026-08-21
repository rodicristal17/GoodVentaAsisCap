<?php

/**
 * Operacion telefonica de Nivel 1 para Sistema Telar.
 *
 * La interfaz nunca conoce credenciales AMI. Este helper trabaja solamente
 * con la base local de Telar y conserva compatibilidad con PHP 7.2.
 */

require_once __DIR__.'/central_telefonica_helper.php';
require_once __DIR__.'/central_telefonica_directorio_helper.php';

class CentralTelefonicaOperacionExcepcion extends Exception
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

function centralTelefonicaOperacionLanzar($codigo, $mensaje, $datos = array())
{
    throw new CentralTelefonicaOperacionExcepcion($codigo, $mensaje, $datos);
}

function centralTelefonicaOperacionEstructuraDisponible($mysqli)
{
    $resultado = $mysqli->query(
        "SELECT COUNT(DISTINCT table_name) total "
        ."FROM information_schema.tables WHERE table_schema=DATABASE() "
        ."AND table_name IN ("
        ."'central_telefonica_operacion_servicio',"
        ."'central_telefonica_paciente_telefono',"
        ."'central_telefonica_solicitud_llamada',"
        ."'central_telefonica_llamada_viva',"
        ."'central_telefonica_operacion_evento')"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    return $fila && intval($fila['total']) === 5;
}

function centralTelefonicaOperacionTexto($valor, $maximo)
{
    $valor = trim((string)$valor);
    if ($maximo > 0 && mb_strlen($valor, 'UTF-8') > $maximo) {
        $valor = mb_substr($valor, 0, $maximo, 'UTF-8');
    }
    return $valor;
}

function centralTelefonicaOperacionContextoUsuario($mysqli, $codUsuario)
{
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,u.estado,u.tipo,u.login,u.cod_localFK,"
        ."IFNULL(p.nombre_persona,'') nombre_usuario,IFNULL(l.Nombre,'') nombre_local "
        ."FROM usuario u "
        ."LEFT JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."WHERE u.cod_usuario=? LIMIT 1"
    );
    if (!$stmt) {
        centralTelefonicaOperacionLanzar(
            'usuario_no_disponible',
            'No se pudo validar el usuario de Telar.'
        );
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
        centralTelefonicaOperacionLanzar(
            'usuario_no_disponible',
            'El usuario autenticado no esta activo.'
        );
    }

    $extension = '';
    if (centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
        $stmt = $mysqli->prepare(
            "SELECT extension FROM central_telefonica_directorio "
            ."WHERE activo=1 AND cod_usuarioFK=? ORDER BY extension LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param('i', $codUsuario);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $fila = $resultado ? $resultado->fetch_assoc() : null;
                $extension = $fila ? preg_replace('/[^0-9]/', '', (string)$fila['extension']) : '';
            }
            $stmt->close();
        }
    }

    return array(
        'cod_usuario' => $codUsuario,
        'nombre' => $usuario['nombre_usuario'],
        'tipo' => $usuario['tipo'],
        'login' => $usuario['login'],
        'cod_local' => intval($usuario['cod_localFK']),
        'local' => $usuario['nombre_local'],
        'extension' => $extension,
        'historial_permitido' => centralTelefonicaTienePermiso(
            $mysqli,
            $codUsuario,
            'VERCENTRALTELEFONICA'
        )
    );
}

function centralTelefonicaOperacionServicio($mysqli)
{
    $base = array(
        'estado' => 'sin_configurar',
        'mensaje' => 'El conector telefonico todavia no fue habilitado.',
        'evento_conectado' => false,
        'origenacion_disponible' => false,
        'fecha_ultimo_evento' => '',
        'fecha_ultimo_latido' => '',
        'activo' => false
    );
    if (!centralTelefonicaOperacionEstructuraDisponible($mysqli)) {
        return $base;
    }
    $resultado = $mysqli->query(
        "SELECT estado,mensaje,evento_conectado,origenacion_disponible,"
        ."fecha_ultimo_evento,fecha_ultimo_latido "
        ."FROM central_telefonica_operacion_servicio WHERE id_servicio=1 LIMIT 1"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if (!$fila) {
        return $base;
    }
    $latidoReciente = false;
    if (!empty($fila['fecha_ultimo_latido'])) {
        $marca = strtotime((string)$fila['fecha_ultimo_latido']);
        $latidoReciente = $marca !== false && $marca >= time() - 75;
    }
    return array(
        'estado' => (string)$fila['estado'],
        'mensaje' => (string)$fila['mensaje'],
        'evento_conectado' => intval($fila['evento_conectado']) === 1 && $latidoReciente,
        'origenacion_disponible' => intval($fila['origenacion_disponible']) === 1 && $latidoReciente,
        'fecha_ultimo_evento' => (string)$fila['fecha_ultimo_evento'],
        'fecha_ultimo_latido' => (string)$fila['fecha_ultimo_latido'],
        'activo' => $latidoReciente && (string)$fila['estado'] === 'disponible'
    );
}

function centralTelefonicaOperacionSaldoSql($aliasCredito)
{
    $aliasCredito = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasCredito);
    if ($aliasCredito === '') {
        $aliasCredito = 'cr';
    }
    return "(GREATEST(0,((IFNULL(".$aliasCredito.".Monto,0)-IFNULL("
        .$aliasCredito.".descuento,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg "
        ."WHERE pg.cod_creditoFK=".$aliasCredito.".idcredito AND pg.Tipo='Pago Cuota'),0)))"
        ."+GREATEST(0,((IFNULL(".$aliasCredito.".totalinteres,0)+IFNULL("
        .$aliasCredito.".deudaInteres,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg "
        ."WHERE pg.cod_creditoFK=".$aliasCredito.".idcredito AND pg.Tipo='Interes'),0))))";
}

function centralTelefonicaOperacionResumenFinanciero($mysqli, $codCliente)
{
    $saldo = centralTelefonicaOperacionSaldoSql('cr');
    $sql = "SELECT COUNT(DISTINCT vt.cod_venta) ventas_pendientes,"
        ."COUNT(DISTINCT cr.idcredito) cuotas_pendientes,"
        ."IFNULL(SUM(".$saldo."),0) saldo_pendiente,"
        ."MIN(CASE WHEN ".$saldo.">0 THEN cr.fechapago ELSE NULL END) proximo_vencimiento,"
        ."SUM(CASE WHEN ".$saldo.">0 AND cr.fechapago<CURDATE() THEN 1 ELSE 0 END) cuotas_vencidas "
        ."FROM credito cr INNER JOIN venta vt ON vt.cod_venta=cr.cod_venta "
        ."WHERE vt.cod_clienteFK=? AND IFNULL(vt.anulado,'')='' "
        ."AND IFNULL(vt.estadocuenta,'Activo')<>'Anulado' AND ".$saldo.">0";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array(
            'ventas_pendientes' => 0,
            'cuotas_pendientes' => 0,
            'cuotas_vencidas' => 0,
            'saldo_pendiente' => 0,
            'proximo_vencimiento' => ''
        );
    }
    $codCliente = intval($codCliente);
    $stmt->bind_param('i', $codCliente);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    return array(
        'ventas_pendientes' => $fila ? intval($fila['ventas_pendientes']) : 0,
        'cuotas_pendientes' => $fila ? intval($fila['cuotas_pendientes']) : 0,
        'cuotas_vencidas' => $fila ? intval($fila['cuotas_vencidas']) : 0,
        'saldo_pendiente' => $fila ? intval(round((float)$fila['saldo_pendiente'])) : 0,
        'proximo_vencimiento' => $fila && $fila['proximo_vencimiento']
            ? (string)$fila['proximo_vencimiento'] : ''
    );
}

function centralTelefonicaOperacionTelefonosCliente($mysqli, $codCliente)
{
    $telefonos = array();
    $stmt = $mysqli->prepare(
        "SELECT telefono_normalizado,fuente FROM central_telefonica_paciente_telefono "
        ."WHERE cod_clienteFK=? AND activo=1 ORDER BY FIELD(fuente,'principal','whatsapp','trabajo1','trabajo2'),id_telefono"
    );
    if (!$stmt) {
        return $telefonos;
    }
    $codCliente = intval($codCliente);
    $stmt->bind_param('i', $codCliente);
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($resultado && ($fila = $resultado->fetch_assoc())) {
            $telefonos[] = array(
                'numero' => (string)$fila['telefono_normalizado'],
                'fuente' => (string)$fila['fuente']
            );
        }
    }
    $stmt->close();
    return $telefonos;
}

function centralTelefonicaOperacionPaciente($mysqli, $codCliente, $incluirFinanzas)
{
    $stmt = $mysqli->prepare(
        "SELECT c.cod_cliente,IFNULL(p.nombre_persona,'') nombre,"
        ."IFNULL(c.ci_cliente,'') documento,IFNULL(c.estado,'') estado "
        ."FROM cliente c INNER JOIN persona p ON p.cod_persona=c.cod_cliente "
        ."WHERE c.cod_cliente=? LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    $codCliente = intval($codCliente);
    $stmt->bind_param('i', $codCliente);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    if (!$fila) {
        return null;
    }
    $paciente = array(
        'cod_cliente' => intval($fila['cod_cliente']),
        'nombre' => (string)$fila['nombre'],
        'documento' => (string)$fila['documento'],
        'estado' => (string)$fila['estado'],
        'telefonos' => centralTelefonicaOperacionTelefonosCliente($mysqli, $codCliente)
    );
    if ($incluirFinanzas) {
        $paciente['finanzas'] = centralTelefonicaOperacionResumenFinanciero($mysqli, $codCliente);
    }
    return $paciente;
}

function centralTelefonicaOperacionBuscarPacientes($mysqli, $texto)
{
    $texto = centralTelefonicaOperacionTexto($texto, 100);
    if (mb_strlen($texto, 'UTF-8') < 2) {
        centralTelefonicaOperacionLanzar(
            'busqueda_incompleta',
            'Escriba al menos dos caracteres para buscar un paciente.'
        );
    }
    $textoIso = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    $como = '%'.$textoIso.'%';
    $digitos = preg_replace('/[^0-9]/', '', $texto);
    $telefono = $digitos === '' ? '' : centralTelefonicaNormalizarTelefono($digitos);
    $ultimos = strlen($digitos) >= 6 ? substr($digitos, -9) : '';
    $sql = "SELECT DISTINCT c.cod_cliente,IFNULL(p.nombre_persona,'') nombre,"
        ."IFNULL(c.ci_cliente,'') documento,IFNULL(c.estado,'') estado "
        ."FROM cliente c INNER JOIN persona p ON p.cod_persona=c.cod_cliente "
        ."LEFT JOIN central_telefonica_paciente_telefono ct ON ct.cod_clienteFK=c.cod_cliente AND ct.activo=1 "
        ."WHERE p.nombre_persona LIKE ? OR c.ci_cliente LIKE ? ";
    $tipos = 'ss';
    $parametros = array($como, $como);
    if ($telefono !== '') {
        $sql .= "OR ct.telefono_normalizado=? ";
        $tipos .= 's';
        $parametros[] = $telefono;
    }
    if ($ultimos !== '') {
        $sql .= "OR ct.ultimos_digitos LIKE ? ";
        $tipos .= 's';
        $parametros[] = '%'.$ultimos;
    }
    $sql .= "ORDER BY p.nombre_persona LIMIT 12";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        centralTelefonicaOperacionLanzar(
            'busqueda_no_disponible',
            'No se pudo preparar la busqueda de pacientes.'
        );
    }
    centralTelefonicaOperacionBind($stmt, $tipos, $parametros);
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaOperacionLanzar(
            'busqueda_no_disponible',
            'No se pudo buscar pacientes en este momento.'
        );
    }
    $resultado = $stmt->get_result();
    $items = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $paciente = centralTelefonicaOperacionPaciente(
            $mysqli,
            intval($fila['cod_cliente']),
            true
        );
        if ($paciente) {
            $items[] = $paciente;
        }
    }
    $stmt->close();
    return array('items' => $items, 'total' => count($items));
}

function centralTelefonicaOperacionBind($stmt, $tipos, $parametros)
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

function centralTelefonicaOperacionToken()
{
    if (function_exists('random_bytes')) {
        $datos = random_bytes(16);
    } else {
        $datos = openssl_random_pseudo_bytes(16);
    }
    $hex = bin2hex($datos);
    return substr($hex, 0, 8).'-'.substr($hex, 8, 4).'-4'.substr($hex, 13, 3)
        .'-a'.substr($hex, 17, 3).'-'.substr($hex, 20, 12);
}

function centralTelefonicaOperacionSolicitarLlamada($mysqli, $contexto, $entrada, $ip)
{
    if (trim((string)$contexto['extension']) === '') {
        centralTelefonicaOperacionLanzar(
            'extension_no_asociada',
            'Su usuario no tiene una extension asociada. Solicite la vinculacion de su interno para poder llamar.'
        );
    }
    $servicio = centralTelefonicaOperacionServicio($mysqli);
    if (!$servicio['activo'] || !$servicio['origenacion_disponible']) {
        centralTelefonicaOperacionLanzar(
            'conector_no_disponible',
            'El conector con MicroSIP no esta disponible en este momento. Puede seguir llamando directamente desde MicroSIP.',
            array('servicio' => $servicio)
        );
    }
    $codCliente = intval(isset($entrada['cod_cliente']) ? $entrada['cod_cliente'] : 0);
    $telefono = centralTelefonicaNormalizarTelefono(
        isset($entrada['telefono']) ? $entrada['telefono'] : ''
    );
    if ($codCliente <= 0 || $telefono === '') {
        centralTelefonicaOperacionLanzar(
            'destino_invalido',
            'Seleccione un paciente y uno de sus telefonos registrados.'
        );
    }
    $stmt = $mysqli->prepare(
        "SELECT 1 FROM central_telefonica_paciente_telefono "
        ."WHERE cod_clienteFK=? AND telefono_normalizado=? AND activo=1 LIMIT 1"
    );
    if (!$stmt) {
        centralTelefonicaOperacionLanzar('destino_no_disponible', 'No se pudo validar el telefono seleccionado.');
    }
    $stmt->bind_param('is', $codCliente, $telefono);
    $valido = false;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $valido = $resultado && $resultado->num_rows === 1;
    }
    $stmt->close();
    if (!$valido) {
        centralTelefonicaOperacionLanzar(
            'destino_no_disponible',
            'El telefono ya no figura entre los datos vigentes del paciente. Actualice la busqueda.'
        );
    }

    $usuario = intval($contexto['cod_usuario']);
    $resultado = $mysqli->query(
        "SELECT COUNT(*) total FROM central_telefonica_solicitud_llamada "
        ."WHERE cod_usuarioFK=".$usuario." AND fecha_solicitud>=DATE_SUB(NOW(),INTERVAL 1 MINUTE)"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
    if (intval($fila['total']) >= 5) {
        centralTelefonicaOperacionLanzar(
            'demasiadas_solicitudes',
            'Ya se solicitaron varias llamadas en el ultimo minuto. Espere unos segundos para evitar duplicarlas.'
        );
    }

    $token = centralTelefonicaOperacionToken();
    $extension = (string)$contexto['extension'];
    $estado = 'pendiente';
    $mensaje = 'Solicitud recibida. En instantes sonara su MicroSIP.';
    $ip = centralTelefonicaOperacionTexto($ip, 45);
    $stmt = $mysqli->prepare(
        "INSERT INTO central_telefonica_solicitud_llamada "
        ."(token,cod_usuarioFK,cod_clienteFK,extension,telefono_normalizado,estado,mensaje,"
        ."ip_solicitud,fecha_solicitud,fecha_actualizacion) "
        ."VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())"
    );
    if (!$stmt) {
        centralTelefonicaOperacionLanzar('solicitud_no_disponible', 'No se pudo preparar la llamada.');
    }
    $stmt->bind_param(
        'siisssss',
        $token,
        $usuario,
        $codCliente,
        $extension,
        $telefono,
        $estado,
        $mensaje,
        $ip
    );
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaOperacionLanzar('solicitud_no_disponible', 'No se pudo guardar la solicitud de llamada.');
    }
    $id = intval($stmt->insert_id);
    $stmt->close();
    centralTelefonicaOperacionRegistrarEvento(
        $mysqli,
        null,
        $id,
        'solicitud_creada',
        $estado,
        $telefono,
        $extension,
        $usuario,
        $codCliente,
        'Solicitud creada desde Telar.'
    );
    return array(
        'id_solicitud' => $id,
        'token' => $token,
        'estado' => $estado,
        'mensaje' => $mensaje,
        'extension' => $extension
    );
}

function centralTelefonicaOperacionRegistrarEvento(
    $mysqli,
    $idLlamada,
    $idSolicitud,
    $tipo,
    $estado,
    $telefono,
    $extension,
    $codUsuario,
    $codCliente,
    $detalle
) {
    if (!centralTelefonicaOperacionEstructuraDisponible($mysqli)) {
        return false;
    }
    $stmt = $mysqli->prepare(
        "INSERT INTO central_telefonica_operacion_evento "
        ."(id_llamada_vivaFK,id_solicitudFK,tipo_evento,estado,telefono_normalizado,"
        ."extension,cod_usuarioFK,cod_clienteFK,detalle_seguro,fecha_evento) "
        ."VALUES (?,?,?,?,?,?,?,?,?,NOW())"
    );
    if (!$stmt) {
        return false;
    }
    $idLlamada = $idLlamada === null ? null : intval($idLlamada);
    $idSolicitud = $idSolicitud === null ? null : intval($idSolicitud);
    $codUsuario = $codUsuario === null ? null : intval($codUsuario);
    $codCliente = $codCliente === null ? null : intval($codCliente);
    $tipo = centralTelefonicaOperacionTexto($tipo, 32);
    $estado = centralTelefonicaOperacionTexto($estado, 24);
    $telefono = centralTelefonicaOperacionTexto($telefono, 24);
    $extension = centralTelefonicaOperacionTexto($extension, 20);
    $detalle = centralTelefonicaOperacionTexto($detalle, 255);
    $stmt->bind_param(
        'iissssiis',
        $idLlamada,
        $idSolicitud,
        $tipo,
        $estado,
        $telefono,
        $extension,
        $codUsuario,
        $codCliente,
        $detalle
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centralTelefonicaOperacionActividad($mysqli, $contexto)
{
    $usuario = intval($contexto['cod_usuario']);
    $llamadas = array();
    $stmt = $mysqli->prepare(
        "SELECT id_llamada_viva,clave_llamada,linkedid,direccion,telefono_normalizado,"
        ."extension,estado,cod_clienteFK,coincidencias_cliente,id_solicitudFK,"
        ."fecha_inicio,fecha_contestada,fecha_fin,fecha_actualizacion "
        ."FROM central_telefonica_llamada_viva "
        ."WHERE cod_usuarioFK=? AND fecha_actualizacion>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) "
        ."ORDER BY fecha_actualizacion DESC LIMIT 10"
    );
    if ($stmt) {
        $stmt->bind_param('i', $usuario);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                $paciente = null;
                if (intval($fila['cod_clienteFK']) > 0 && intval($fila['coincidencias_cliente']) === 1) {
                    $paciente = centralTelefonicaOperacionPaciente(
                        $mysqli,
                        intval($fila['cod_clienteFK']),
                        true
                    );
                }
                $llamadas[] = array(
                    'id_llamada_viva' => intval($fila['id_llamada_viva']),
                    'clave' => (string)$fila['clave_llamada'],
                    'direccion' => (string)$fila['direccion'],
                    'telefono' => (string)$fila['telefono_normalizado'],
                    'extension' => (string)$fila['extension'],
                    'estado' => (string)$fila['estado'],
                    'coincidencias_cliente' => intval($fila['coincidencias_cliente']),
                    'paciente' => $paciente,
                    'fecha_inicio' => (string)$fila['fecha_inicio'],
                    'fecha_contestada' => (string)$fila['fecha_contestada'],
                    'fecha_fin' => (string)$fila['fecha_fin'],
                    'fecha_actualizacion' => (string)$fila['fecha_actualizacion']
                );
            }
        }
        $stmt->close();
    }

    $solicitudes = array();
    $stmt = $mysqli->prepare(
        "SELECT id_solicitud,token,cod_clienteFK,extension,telefono_normalizado,estado,mensaje,"
        ."fecha_solicitud,fecha_respuesta,fecha_fin,fecha_actualizacion "
        ."FROM central_telefonica_solicitud_llamada "
        ."WHERE cod_usuarioFK=? AND fecha_solicitud>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) "
        ."ORDER BY id_solicitud DESC LIMIT 10"
    );
    if ($stmt) {
        $stmt->bind_param('i', $usuario);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($resultado && ($fila = $resultado->fetch_assoc())) {
                $solicitudes[] = array(
                    'id_solicitud' => intval($fila['id_solicitud']),
                    'token' => (string)$fila['token'],
                    'cod_cliente' => intval($fila['cod_clienteFK']),
                    'extension' => (string)$fila['extension'],
                    'telefono' => (string)$fila['telefono_normalizado'],
                    'estado' => (string)$fila['estado'],
                    'mensaje' => (string)$fila['mensaje'],
                    'fecha_solicitud' => (string)$fila['fecha_solicitud'],
                    'fecha_respuesta' => (string)$fila['fecha_respuesta'],
                    'fecha_fin' => (string)$fila['fecha_fin'],
                    'fecha_actualizacion' => (string)$fila['fecha_actualizacion']
                );
            }
        }
        $stmt->close();
    }
    return array(
        'usuario' => array(
            'cod_usuario' => intval($contexto['cod_usuario']),
            'nombre' => (string)$contexto['nombre'],
            'extension' => (string)$contexto['extension'],
            'historial_permitido' => !empty($contexto['historial_permitido'])
        ),
        'servicio' => centralTelefonicaOperacionServicio($mysqli),
        'llamadas' => $llamadas,
        'solicitudes' => $solicitudes
    );
}

function centralTelefonicaOperacionRefrescarTelefonos($mysqli)
{
    if (!centralTelefonicaOperacionEstructuraDisponible($mysqli)) {
        return array('procesados' => 0, 'telefonos' => 0);
    }
    $resultado = $mysqli->query(
        "SELECT c.cod_cliente,IFNULL(p.telefono,'') principal,IFNULL(c.whapp,'') whatsapp,"
        ."IFNULL(c.teleftrab1,'') trabajo1,IFNULL(c.teleftrab2,'') trabajo2 "
        ."FROM cliente c INNER JOIN persona p ON p.cod_persona=c.cod_cliente"
    );
    if (!$resultado) {
        return array('procesados' => 0, 'telefonos' => 0);
    }
    $stmtDesactivar = $mysqli->prepare(
        "UPDATE central_telefonica_paciente_telefono SET activo=0,fecha_actualizacion=NOW() "
        ."WHERE cod_clienteFK=?"
    );
    $stmtGuardar = $mysqli->prepare(
        "INSERT INTO central_telefonica_paciente_telefono "
        ."(cod_clienteFK,telefono_normalizado,ultimos_digitos,fuente,activo,fecha_actualizacion) "
        ."VALUES (?,?,?,?,1,NOW()) ON DUPLICATE KEY UPDATE ultimos_digitos=VALUES(ultimos_digitos),"
        ."activo=1,fecha_actualizacion=NOW()"
    );
    if (!$stmtDesactivar || !$stmtGuardar) {
        if ($stmtDesactivar) { $stmtDesactivar->close(); }
        if ($stmtGuardar) { $stmtGuardar->close(); }
        return array('procesados' => 0, 'telefonos' => 0);
    }
    $procesados = 0;
    $telefonos = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $codCliente = intval($fila['cod_cliente']);
        $stmtDesactivar->bind_param('i', $codCliente);
        $stmtDesactivar->execute();
        foreach (array('principal', 'whatsapp', 'trabajo1', 'trabajo2') as $fuente) {
            $normalizado = centralTelefonicaNormalizarTelefono($fila[$fuente]);
            $digitos = preg_replace('/[^0-9]/', '', $normalizado);
            if ($normalizado === '' || strlen($digitos) < 6) {
                continue;
            }
            $ultimos = substr($digitos, -15);
            $stmtGuardar->bind_param('isss', $codCliente, $normalizado, $ultimos, $fuente);
            if ($stmtGuardar->execute()) {
                $telefonos++;
            }
        }
        $procesados++;
    }
    $stmtDesactivar->close();
    $stmtGuardar->close();
    return array('procesados' => $procesados, 'telefonos' => $telefonos);
}

function centralTelefonicaOperacionCoincidenciasTelefono($mysqli, $telefono)
{
    $telefono = centralTelefonicaNormalizarTelefono($telefono);
    $salida = array('total' => 0, 'cod_cliente' => null);
    if ($telefono === '') {
        return $salida;
    }
    $stmt = $mysqli->prepare(
        "SELECT DISTINCT cod_clienteFK FROM central_telefonica_paciente_telefono "
        ."WHERE telefono_normalizado=? AND activo=1 LIMIT 3"
    );
    if (!$stmt) {
        return $salida;
    }
    $stmt->bind_param('s', $telefono);
    $clientes = array();
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        while ($resultado && ($fila = $resultado->fetch_assoc())) {
            $clientes[] = intval($fila['cod_clienteFK']);
        }
    }
    $stmt->close();
    $salida['total'] = count($clientes);
    $salida['cod_cliente'] = count($clientes) === 1 ? $clientes[0] : null;
    return $salida;
}

?>

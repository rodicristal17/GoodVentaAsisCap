<?php

/**
 * Worker aislado para llamadas salientes e identificacion entrante.
 *
 * Uso:
 *   php scripts/procesar_central_telefonica_tiempo_real.php --check
 *   php scripts/procesar_central_telefonica_tiempo_real.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_ami_helper.php';

function centralTelefonicaLiveActualizarServicio(
    $mysqli,
    $estado,
    $mensaje,
    $eventoConectado,
    $origenacionDisponible,
    $huboEvento
) {
    $sql = "UPDATE central_telefonica_operacion_servicio SET estado=?,mensaje=?,"
        ."evento_conectado=?,origenacion_disponible=?,fecha_ultimo_latido=NOW(),"
        .($huboEvento ? "fecha_ultimo_evento=NOW()," : '')
        ."fecha_actualizacion=NOW() WHERE id_servicio=1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $eventoConectado = $eventoConectado ? 1 : 0;
    $origenacionDisponible = $origenacionDisponible ? 1 : 0;
    $stmt->bind_param('ssii', $estado, $mensaje, $eventoConectado, $origenacionDisponible);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centralTelefonicaLiveTomarSolicitud($mysqli)
{
    $mysqli->begin_transaction();
    $resultado = $mysqli->query(
        "SELECT id_solicitud,token,cod_usuarioFK,cod_clienteFK,extension,telefono_normalizado,intentos "
        ."FROM central_telefonica_solicitud_llamada "
        ."WHERE estado='pendiente' ORDER BY id_solicitud ASC LIMIT 1 FOR UPDATE"
    );
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if (!$fila) {
        $mysqli->commit();
        return null;
    }
    $id = intval($fila['id_solicitud']);
    $stmt = $mysqli->prepare(
        "UPDATE central_telefonica_solicitud_llamada SET estado='procesando',"
        ."mensaje='Preparando llamada en Issabel.',intentos=intentos+1,"
        ."fecha_tomada=NOW(),fecha_actualizacion=NOW() "
        ."WHERE id_solicitud=? AND estado='pendiente'"
    );
    if (!$stmt) {
        $mysqli->rollback();
        return null;
    }
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    if (!$ok) {
        $mysqli->rollback();
        return null;
    }
    $mysqli->commit();
    $fila['id_solicitud'] = $id;
    return $fila;
}

function centralTelefonicaLiveResolverSolicitud($mysqli, $solicitud, $resultado)
{
    $id = intval($solicitud['id_solicitud']);
    $usuario = intval($solicitud['cod_usuarioFK']);
    $cliente = intval($solicitud['cod_clienteFK']);
    $extension = (string)$solicitud['extension'];
    $telefono = (string)$solicitud['telefono_normalizado'];
    $estado = $resultado['ok'] ? 'enviada' : 'error';
    $mensaje = $resultado['ok']
        ? 'Issabel acepto la solicitud. Atienda su MicroSIP.'
        : 'No se pudo preparar la llamada. Puede llamar directamente desde MicroSIP.';
    $actionId = isset($resultado['action_id']) ? (string)$resultado['action_id'] : '';
    $stmt = $mysqli->prepare(
        "UPDATE central_telefonica_solicitud_llamada SET estado=?,mensaje=?,action_id=?,"
        ."fecha_respuesta=NOW(),fecha_fin=".($resultado['ok'] ? 'NULL' : 'NOW()').","
        ."fecha_actualizacion=NOW() WHERE id_solicitud=?"
    );
    if ($stmt) {
        $stmt->bind_param('sssi', $estado, $mensaje, $actionId, $id);
        $stmt->execute();
        $stmt->close();
    }
    centralTelefonicaOperacionRegistrarEvento(
        $mysqli,
        null,
        $id,
        $resultado['ok'] ? 'origenacion_aceptada' : 'origenacion_rechazada',
        $estado,
        $telefono,
        $extension,
        $usuario,
        $cliente,
        $resultado['ok'] ? 'AMI acepto la llamada.' : 'AMI no acepto la llamada.'
    );
}

function centralTelefonicaLiveCerrarSolicitudesInterrumpidas($mysqli)
{
    $resultado = $mysqli->query(
        "SELECT id_solicitud,cod_usuarioFK,cod_clienteFK,extension,telefono_normalizado "
        ."FROM central_telefonica_solicitud_llamada "
        ."WHERE estado='procesando' AND fecha_tomada<DATE_SUB(NOW(),INTERVAL 2 MINUTE) "
        ."ORDER BY id_solicitud ASC LIMIT 100"
    );
    if (!$resultado) {
        return 0;
    }
    $cerradas = 0;
    while ($fila = $resultado->fetch_assoc()) {
        $id = intval($fila['id_solicitud']);
        $stmt = $mysqli->prepare(
            "UPDATE central_telefonica_solicitud_llamada SET estado='error',"
            ."mensaje='La preparacion se interrumpio. Puede llamar directamente desde MicroSIP.',"
            ."fecha_fin=COALESCE(fecha_fin,NOW()),fecha_actualizacion=NOW() "
            ."WHERE id_solicitud=? AND estado='procesando'"
        );
        if (!$stmt) {
            continue;
        }
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute() && $stmt->affected_rows === 1;
        $stmt->close();
        if (!$ok) {
            continue;
        }
        $cerradas++;
        centralTelefonicaOperacionRegistrarEvento(
            $mysqli,
            null,
            $id,
            'procesamiento_interrumpido',
            'error',
            (string)$fila['telefono_normalizado'],
            (string)$fila['extension'],
            intval($fila['cod_usuarioFK']),
            intval($fila['cod_clienteFK']),
            'La solicitud no se reintento para evitar una llamada duplicada.'
        );
    }
    return $cerradas;
}

function centralTelefonicaLiveSolicitudRelacionada($mysqli, $evento)
{
    $stmt = $mysqli->prepare(
        "SELECT id_solicitud,cod_clienteFK FROM central_telefonica_solicitud_llamada "
        ."WHERE extension=? AND telefono_normalizado=? "
        ."AND estado IN ('enviada','sonando','conectada') "
        ."AND fecha_solicitud>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) "
        ."ORDER BY id_solicitud DESC LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('ss', $evento['extension'], $evento['telefono']);
    $fila = null;
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        $fila = $resultado ? $resultado->fetch_assoc() : null;
    }
    $stmt->close();
    return $fila;
}

function centralTelefonicaLiveGuardarEvento($mysqli, $evento)
{
    $directorio = centralTelefonicaDirectorioResolver($mysqli, array($evento['extension']));
    $asignacion = isset($directorio[$evento['extension']])
        ? $directorio[$evento['extension']] : null;
    if (!$asignacion || intval($asignacion['cod_usuario']) <= 0) {
        return false;
    }
    $usuario = intval($asignacion['cod_usuario']);
    $coincidencia = centralTelefonicaOperacionCoincidenciasTelefono($mysqli, $evento['telefono']);
    $cliente = $coincidencia['cod_cliente'] === null
        ? null : intval($coincidencia['cod_cliente']);
    $solicitud = centralTelefonicaLiveSolicitudRelacionada($mysqli, $evento);
    $idSolicitud = $solicitud ? intval($solicitud['id_solicitud']) : null;
    if ($cliente === null && $solicitud && intval($solicitud['cod_clienteFK']) > 0) {
        $cliente = intval($solicitud['cod_clienteFK']);
        $coincidencia['total'] = 1;
    }

    $estadoAnterior = '';
    $stmtAnterior = $mysqli->prepare(
        "SELECT estado FROM central_telefonica_llamada_viva WHERE clave_llamada=? LIMIT 1"
    );
    if ($stmtAnterior) {
        $stmtAnterior->bind_param('s', $evento['clave']);
        if ($stmtAnterior->execute()) {
            $resultadoAnterior = $stmtAnterior->get_result();
            $filaAnterior = $resultadoAnterior ? $resultadoAnterior->fetch_assoc() : null;
            $estadoAnterior = $filaAnterior ? (string)$filaAnterior['estado'] : '';
        }
        $stmtAnterior->close();
    }

    $sql = "INSERT INTO central_telefonica_llamada_viva "
        ."(clave_llamada,linkedid,uniqueid,direccion,telefono_normalizado,extension,estado,"
        ."cod_usuarioFK,cod_clienteFK,coincidencias_cliente,id_solicitudFK,fecha_inicio,"
        ."fecha_contestada,fecha_fin,fecha_actualizacion) "
        ."VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),"
        ."CASE WHEN ?='conectada' THEN NOW() ELSE NULL END,"
        ."CASE WHEN ? IN ('finalizada','ocupada','no_contestada') THEN NOW() ELSE NULL END,NOW()) "
        ."ON DUPLICATE KEY UPDATE id_llamada_viva=LAST_INSERT_ID(id_llamada_viva),"
        ."uniqueid=VALUES(uniqueid),direccion=VALUES(direccion),telefono_normalizado=VALUES(telefono_normalizado),"
        ."estado=CASE WHEN estado IN ('finalizada','ocupada','no_contestada') "
        ."AND VALUES(estado) IN ('detectada','sonando') THEN estado ELSE VALUES(estado) END,"
        ."cod_usuarioFK=VALUES(cod_usuarioFK),cod_clienteFK=COALESCE(VALUES(cod_clienteFK),cod_clienteFK),"
        ."coincidencias_cliente=GREATEST(coincidencias_cliente,VALUES(coincidencias_cliente)),"
        ."id_solicitudFK=COALESCE(VALUES(id_solicitudFK),id_solicitudFK),"
        ."fecha_contestada=CASE WHEN VALUES(estado)='conectada' "
        ."THEN COALESCE(fecha_contestada,NOW()) ELSE fecha_contestada END,"
        ."fecha_fin=CASE WHEN VALUES(estado) IN ('finalizada','ocupada','no_contestada') "
        ."THEN COALESCE(fecha_fin,NOW()) ELSE fecha_fin END,fecha_actualizacion=NOW()";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $clienteBind = $cliente;
    $idSolicitudBind = $idSolicitud;
    $totalCoincidencias = intval($coincidencia['total']);
    $stmt->bind_param(
        'sssssssiiiiss',
        $evento['clave'],
        $evento['linkedid'],
        $evento['uniqueid'],
        $evento['direccion'],
        $evento['telefono'],
        $evento['extension'],
        $evento['estado'],
        $usuario,
        $clienteBind,
        $totalCoincidencias,
        $idSolicitudBind,
        $evento['estado'],
        $evento['estado']
    );
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $idLlamada = intval($stmt->insert_id);
    $stmt->close();

    if ($estadoAnterior !== $evento['estado']) {
        centralTelefonicaOperacionRegistrarEvento(
            $mysqli,
            $idLlamada,
            $idSolicitud,
            $evento['tipo_evento'],
            $evento['estado'],
            $evento['telefono'],
            $evento['extension'],
            $usuario,
            $cliente,
            'Cambio de estado detectado por el conector AMI.'
        );
    }
    if ($idSolicitud !== null) {
        $estadoSolicitud = $evento['estado'];
        $mensaje = 'Llamada en curso.';
        $fechaFin = false;
        if ($evento['estado'] === 'sonando') {
            $mensaje = 'MicroSIP esta sonando.';
        } elseif ($evento['estado'] === 'conectada') {
            $mensaje = 'Llamada conectada.';
        } elseif (in_array($evento['estado'], array('finalizada','ocupada','no_contestada'), true)) {
            $mensaje = $evento['estado'] === 'ocupada' ? 'El destino estaba ocupado.'
                : ($evento['estado'] === 'no_contestada' ? 'La llamada no fue contestada.' : 'Llamada finalizada.');
            $fechaFin = true;
        }
        $sqlSolicitud = "UPDATE central_telefonica_solicitud_llamada SET estado=?,mensaje=?,linkedid=?,"
            .($evento['estado'] === 'conectada' ? "fecha_respuesta=COALESCE(fecha_respuesta,NOW())," : '')
            .($fechaFin ? "fecha_fin=COALESCE(fecha_fin,NOW())," : '')
            ."fecha_actualizacion=NOW() WHERE id_solicitud=?";
        $stmtSolicitud = $mysqli->prepare($sqlSolicitud);
        if ($stmtSolicitud) {
            $stmtSolicitud->bind_param('sssi', $estadoSolicitud, $mensaje, $evento['linkedid'], $idSolicitud);
            $stmtSolicitud->execute();
            $stmtSolicitud->close();
        }
    }
    return true;
}

function centralTelefonicaLiveLeerEventos($socket, &$buffer, $config, $mysqli)
{
    $huboEvento = false;
    while (true) {
        $trozo = @fread($socket, 16384);
        if ($trozo === false || $trozo === '') {
            break;
        }
        $buffer .= $trozo;
        if (strlen($buffer) > 1048576) {
            $buffer = substr($buffer, -262144);
        }
    }
    while (($posicion = strpos($buffer, "\r\n\r\n")) !== false) {
        $texto = substr($buffer, 0, $posicion);
        $buffer = substr($buffer, $posicion + 4);
        $paquete = centralTelefonicaAmiPaquete($texto);
        if (!isset($paquete['event'])) {
            continue;
        }
        $evento = centralTelefonicaAmiAnalizarEvento($paquete, $config);
        if ($evento && centralTelefonicaLiveGuardarEvento($mysqli, $evento)) {
            $huboEvento = true;
        }
    }
    return $huboEvento;
}

$config = centralTelefonicaAmiConfiguracion();
$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(2);
}
if (!centralTelefonicaOperacionEstructuraDisponible($mysqli)) {
    fwrite(STDERR, "La migracion de Central Telefonica Nivel 1 no esta aplicada.\n");
    exit(3);
}

if (in_array('--check', $argv, true)) {
    fwrite(STDOUT, json_encode(array(
        'ok' => centralTelefonicaAmiOrigenacionConfigurada($config),
        'estructura' => true,
        'eventos_configurados' => centralTelefonicaAmiEventosConfigurados($config),
        'origenacion_configurada' => centralTelefonicaAmiOrigenacionConfigurada($config),
        'host_configurado' => trim((string)$config['host']) !== '',
        'puerto' => intval($config['port']),
        'contexto_configurado' => trim((string)$config['context']) !== ''
    ), JSON_UNESCAPED_UNICODE).PHP_EOL);
    $mysqli->close();
    exit(0);
}

$bloqueo = $mysqli->query("SELECT GET_LOCK('telar_central_telefonica_tiempo_real',0) adquirido");
$filaBloqueo = $bloqueo ? $bloqueo->fetch_assoc() : array('adquirido' => 0);
if (intval($filaBloqueo['adquirido']) !== 1) {
    fwrite(STDERR, "Ya existe un conector de Central Telefonica activo.\n");
    $mysqli->close();
    exit(4);
}

centralTelefonicaLiveCerrarSolicitudesInterrumpidas($mysqli);

$terminar = false;
if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGTERM, function () use (&$terminar) { $terminar = true; });
    pcntl_signal(SIGINT, function () use (&$terminar) { $terminar = true; });
}

$socketEventos = null;
$bufferEventos = '';
$proximoIntentoEventos = 0;
$proximaActualizacionTelefonos = 0;
$proximoLatido = 0;
$unaVez = in_array('--once', $argv, true);
$origenacionConfigurada = centralTelefonicaAmiOrigenacionConfigurada($config);

while (!$terminar) {
    $ahora = time();
    if ($ahora >= $proximaActualizacionTelefonos) {
        centralTelefonicaOperacionRefrescarTelefonos($mysqli);
        $proximaActualizacionTelefonos = $ahora + 120;
    }
    if (!$socketEventos && centralTelefonicaAmiEventosConfigurados($config)
        && $ahora >= $proximoIntentoEventos) {
        $conexionEventos = centralTelefonicaAmiAbrir(
            $config,
            $config['event_user'],
            $config['event_secret'],
            true
        );
        if ($conexionEventos['ok']) {
            $socketEventos = $conexionEventos['socket'];
            $bufferEventos = '';
        } else {
            fwrite(STDERR, '[Central Telefonica] '.$conexionEventos['codigo'].PHP_EOL);
            $proximoIntentoEventos = $ahora + 10;
        }
    }

    $solicitud = centralTelefonicaLiveTomarSolicitud($mysqli);
    if ($solicitud) {
        $resultadoOrigenacion = centralTelefonicaAmiOriginar($config, $solicitud);
        centralTelefonicaLiveResolverSolicitud($mysqli, $solicitud, $resultadoOrigenacion);
    }

    $huboEvento = false;
    if ($socketEventos) {
        if (feof($socketEventos)) {
            fclose($socketEventos);
            $socketEventos = null;
            $proximoIntentoEventos = $ahora + 5;
        } else {
            $huboEvento = centralTelefonicaLiveLeerEventos(
                $socketEventos,
                $bufferEventos,
                $config,
                $mysqli
            );
        }
    }

    if ($ahora >= $proximoLatido || $huboEvento) {
        $eventosConectados = is_resource($socketEventos);
        $estado = $origenacionConfigurada ? 'disponible' : 'sin_configurar';
        $mensaje = $origenacionConfigurada
            ? ($eventosConectados
                ? 'Conector de llamadas y reconocimiento entrante disponible.'
                : 'Las llamadas salientes estan disponibles; el reconocimiento entrante esta reconectando.')
            : 'Falta configurar el acceso AMI de origenacion.';
        centralTelefonicaLiveActualizarServicio(
            $mysqli,
            $estado,
            $mensaje,
            $eventosConectados,
            $origenacionConfigurada,
            $huboEvento
        );
        $proximoLatido = $ahora + 5;
    }
    if ($unaVez) {
        break;
    }
    usleep(250000);
}

if ($socketEventos) {
    centralTelefonicaAmiEscribir($socketEventos, array('Action' => 'Logoff'));
    fclose($socketEventos);
}
centralTelefonicaLiveActualizarServicio(
    $mysqli,
    'detenido',
    'El conector telefonico se detuvo de forma controlada.',
    false,
    false,
    false
);
$mysqli->query("SELECT RELEASE_LOCK('telar_central_telefonica_tiempo_real')");
$mysqli->close();

?>

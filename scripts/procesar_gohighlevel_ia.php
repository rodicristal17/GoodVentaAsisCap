<?php

/**
 * Procesa un lote pequeno de respuestas automaticas de GoHighLevel.
 * No hace nada salvo que los tres interruptores (servidor, modulo y automatico) esten activos.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/gohighlevel_helper.php';

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno || !goHighLevelEstructuraDisponible($mysqli)) {
    fwrite(STDERR, "GoHighLevel IA: estructura no disponible.\n");
    exit(2);
}

try {
    $config = goHighLevelConfiguracion();
    $deepseek = goHighLevelDeepSeekConfiguracion();
    $ia = goHighLevelIaConfiguracionLocal($mysqli);
    if (!goHighLevelConfigurado($config) || empty($config['write_enabled'])
        || empty($deepseek['automatico_servidor']) || empty($ia['asistente_habilitado'])
        || empty($ia['automatico_habilitado']) || empty($ia['clave_configurada'])) {
        exit(0);
    }
    $automatico = array(
        'alcance' => isset($deepseek['automatico_alcance']) ? $deepseek['automatico_alcance'] : 'pilot',
        'retardo_segundos' => isset($deepseek['automatico_retardo_segundos'])
            ? intval($deepseek['automatico_retardo_segundos']) : 120,
        'contactos_piloto' => isset($deepseek['automatico_contactos_piloto'])
            && is_array($deepseek['automatico_contactos_piloto'])
            ? $deepseek['automatico_contactos_piloto'] : array()
    );
    if ($automatico['alcance'] !== 'all' && count($automatico['contactos_piloto']) === 0) {
        exit(0);
    }
    $contexto = goHighLevelContextoUsuario($mysqli, 5994);
    $respuesta = goHighLevelApiGet($config, '/conversations/search', array(
        'locationId' => $config['location_id'],
        'limit' => 100,
        'sort' => 'desc'
    ));
    $procesadas = 0;
    foreach (goHighLevelItems($respuesta, array('conversations')) as $conversacion) {
        if ($procesadas >= 2 || !is_array($conversacion)) {
            break;
        }
        $contactId = goHighLevelIdSeguro(
            goHighLevelValor($conversacion, array('contactId', 'contact_id'), '')
        );
        if (!goHighLevelAutomaticoContactoPermitido($automatico, $contactId)) {
            continue;
        }
        $conversationId = goHighLevelIdSeguro(goHighLevelValor($conversacion, array('id', '_id'), ''));
        if ($conversationId === '') {
            continue;
        }
        $historial = goHighLevelListarMensajesConversacion($config, array(
            'conversation_id' => $conversationId,
            'limite' => 20
        ));
        $mensajes = isset($historial['items']) ? $historial['items'] : array();
        if (count($mensajes) === 0 || empty($historial['ventana_whatsapp']['abierta'])) {
            continue;
        }
        $ultimo = $mensajes[count($mensajes) - 1];
        if (!goHighLevelAutomaticoMensajeListo(
            $ultimo,
            $automatico['retardo_segundos']
        )) {
            continue;
        }
        $messageId = goHighLevelIdSeguro($ultimo['id']);
        if ($messageId === '') {
            continue;
        }
        $token = hash('sha256', 'auto|'.$config['location_id'].'|'.$messageId);
        $stmt = $mysqli->prepare(
            "SELECT estado FROM gohighlevel_ia_operacion WHERE token_cliente=? LIMIT 1"
        );
        $estadoExistente = '';
        if ($stmt) {
            $stmt->bind_param('s', $token);
            if ($stmt->execute()) {
                $resultadoEstado = $stmt->get_result();
                $filaEstado = $resultadoEstado ? $resultadoEstado->fetch_assoc() : null;
                $estadoExistente = $filaEstado ? (string)$filaEstado['estado'] : '';
            }
            $stmt->close();
        }
        if ($estadoExistente !== '') {
            continue;
        }
        $sugerencia = goHighLevelSugerirRespuestaIa($mysqli, $config, $contexto, array(
            'conversation_id' => $conversationId,
            'token_ia' => $token,
            'tipo_operacion' => 'automatico'
        ));
        $procesadas++;
        if (!empty($sugerencia['requiere_humano']) || trim((string)$sugerencia['respuesta']) === ''
            || floatval($sugerencia['confianza']) < 0.88) {
            continue;
        }
        $historialActual = goHighLevelListarMensajesConversacion($config, array(
            'conversation_id' => $conversationId,
            'limite' => 20
        ));
        $mensajesActuales = isset($historialActual['items']) ? $historialActual['items'] : array();
        $ultimoActual = count($mensajesActuales) > 0
            ? $mensajesActuales[count($mensajesActuales) - 1] : array();
        $ultimoActualId = goHighLevelIdSeguro(goHighLevelValor($ultimoActual, array('id'), ''));
        if ($ultimoActualId !== $messageId
            || !goHighLevelAutomaticoMensajeListo($ultimoActual, $automatico['retardo_segundos'])) {
            $stmtCancelado = $mysqli->prepare(
                "UPDATE gohighlevel_ia_operacion SET estado='cancelada',codigo_resultado='relevo_cancelado',"
                ."fecha_actualizacion=NOW() WHERE token_cliente=? LIMIT 1"
            );
            if ($stmtCancelado) {
                $stmtCancelado->bind_param('s', $token);
                $stmtCancelado->execute();
                $stmtCancelado->close();
            }
            continue;
        }
        $tokenEnvio = substr(hash('sha256', 'auto-envio|'.$config['location_id'].'|'.$messageId), 0, 48);
        goHighLevelEnviarRespuestaManual($mysqli, $config, $contexto, array(
            'conversation_id' => $conversationId,
            'mensaje' => $sugerencia['respuesta'],
            'token_envio' => $tokenEnvio,
            'confirmar_reglas' => 1,
            'esperar_ultimo_mensaje_id' => $messageId
        ));
        $stmtEnviado = $mysqli->prepare(
            "UPDATE gohighlevel_ia_operacion SET estado='enviada',codigo_resultado='respuesta_enviada',"
            ."fecha_actualizacion=NOW() WHERE token_cliente=? LIMIT 1"
        );
        if ($stmtEnviado) {
            $stmtEnviado->bind_param('s', $token);
            $stmtEnviado->execute();
            $stmtEnviado->close();
        }
    }
    exit(0);
} catch (GoHighLevelExcepcion $e) {
    fwrite(STDERR, 'GoHighLevel IA: '.$e->codigoOperacion."\n");
    exit(1);
} catch (Exception $e) {
    fwrite(STDERR, "GoHighLevel IA: error interno.\n");
    exit(1);
}

?>

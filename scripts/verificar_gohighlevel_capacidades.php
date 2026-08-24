<?php

/**
 * Preflight sin escrituras para las capacidades de equipo y tareas.
 * No imprime tokens, nombres, identificadores ni cuerpos recibidos.
 */

require_once dirname(__DIR__).'/php_system/gohighlevel_helper.php';

$config = goHighLevelConfiguracion();
$resultado = array(
    'configuracion' => goHighLevelConfigurado($config) ? 'ok' : 'no_disponible',
    'usuarios_lectura' => 'no_comprobado',
    'contactos_lectura' => 'no_comprobado',
    'tareas_lectura' => 'no_comprobado',
    'tareas_busqueda_lectura' => 'no_comprobado',
    'tareas_escritura' => !empty($config['task_write_enabled']) ? 'interruptor_activo' : 'interruptor_inactivo'
);

if (!goHighLevelConfigurado($config)) {
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
    exit(1);
}

try {
    goHighLevelUsuariosApi($config);
    $resultado['usuarios_lectura'] = 'ok';
} catch (GoHighLevelExcepcion $e) {
    $resultado['usuarios_lectura'] = $e->codigoOperacion;
}

try {
    $respuestaContactos = goHighLevelApiGet($config, '/contacts/', array(
        'locationId' => $config['location_id'],
        'limit' => 1
    ));
    $contactos = goHighLevelItems($respuestaContactos, array('contacts'));
    $resultado['contactos_lectura'] = 'ok';
    if (count($contactos) > 0 && is_array($contactos[0])) {
        $contactId = goHighLevelIdSeguro(goHighLevelValor($contactos[0], array('id', '_id'), ''));
        if ($contactId !== '') {
            goHighLevelApiGet(
                $config,
                '/contacts/'.rawurlencode($contactId).'/tasks',
                array(),
                'v3'
            );
            $resultado['tareas_lectura'] = 'ok';
        } else {
            $resultado['tareas_lectura'] = 'sin_contacto_valido';
        }
    } else {
        $resultado['tareas_lectura'] = 'sin_contactos';
    }
} catch (GoHighLevelExcepcion $e) {
    if ($resultado['contactos_lectura'] !== 'ok') {
        $resultado['contactos_lectura'] = $e->codigoOperacion;
    } else {
        $resultado['tareas_lectura'] = $e->codigoOperacion;
    }
}

try {
    goHighLevelApiBuscarTareas($config, 1, 0);
    $resultado['tareas_busqueda_lectura'] = 'ok';
} catch (GoHighLevelExcepcion $e) {
    $resultado['tareas_busqueda_lectura'] = $e->codigoOperacion;
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n";
exit(
    $resultado['usuarios_lectura'] === 'ok'
    && $resultado['contactos_lectura'] === 'ok'
    && $resultado['tareas_lectura'] === 'ok'
    && $resultado['tareas_busqueda_lectura'] === 'ok' ? 0 : 2
);

?>

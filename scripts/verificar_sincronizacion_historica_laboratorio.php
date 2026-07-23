<?php

/**
 * Verifica la sincronizacion historica de trabajos de laboratorio dental.
 *
 * Por defecto es estrictamente de solo lectura:
 *   php scripts/verificar_sincronizacion_historica_laboratorio.php
 *
 * Aplicacion controlada (ejecuta dos veces para comprobar idempotencia):
 *   php scripts/verificar_sincronizacion_historica_laboratorio.php --aplicar-migracion
 *
 * La salida contiene solamente estructura y conteos agregados. No muestra
 * nombres, documentos, observaciones ni otros datos identificables.
 * Compatible con PHP 7.2.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Este verificador solo puede ejecutarse por consola.'.PHP_EOL;
    exit(1);
}

require_once dirname(__DIR__).'/php_system/conexion.php';

function verificarHistLabFallar($mensaje)
{
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
    exit(1);
}

function verificarHistLabOk($mensaje)
{
    fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
}

function verificarHistLabInfo($mensaje)
{
    fwrite(STDOUT, '[INFO] '.$mensaje.PHP_EOL);
}

function verificarHistLabAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        verificarHistLabFallar($mensaje);
    }
    verificarHistLabOk($mensaje);
}

function verificarHistLabFila($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        verificarHistLabFallar('No se pudo completar una comprobacion agregada: '.$mysqli->error);
    }
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    return $fila ? $fila : array();
}

function verificarHistLabEscalar($mysqli, $sql)
{
    $fila = verificarHistLabFila($mysqli, $sql);
    if (!$fila) {
        return 0;
    }
    $valores = array_values($fila);
    return isset($valores[0]) ? intval($valores[0]) : 0;
}

function verificarHistLabTablaExiste($mysqli, $tabla)
{
    $tablaBd = $mysqli->real_escape_string($tabla);
    return verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.TABLES "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$tablaBd."'"
    ) === 1;
}

function verificarHistLabColumnaExiste($mysqli, $tabla, $columna)
{
    $tablaBd = $mysqli->real_escape_string($tabla);
    $columnaBd = $mysqli->real_escape_string($columna);
    return verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.COLUMNS "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$tablaBd."' "
        ."AND COLUMN_NAME='".$columnaBd."'"
    ) === 1;
}

function verificarHistLabIndiceUnicoExiste($mysqli, $tabla, $indice)
{
    $tablaBd = $mysqli->real_escape_string($tabla);
    $indiceBd = $mysqli->real_escape_string($indice);
    return verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.STATISTICS "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$tablaBd."' "
        ."AND INDEX_NAME='".$indiceBd."' AND NON_UNIQUE=0"
    ) > 0;
}

function verificarHistLabIndiceExiste($mysqli, $tabla, $indice)
{
    $tablaBd = $mysqli->real_escape_string($tabla);
    $indiceBd = $mysqli->real_escape_string($indice);
    return verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.STATISTICS "
        ."WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$tablaBd."' "
        ."AND INDEX_NAME='".$indiceBd."'"
    ) > 0;
}

function verificarHistLabRestriccionExiste($mysqli, $tabla, $restriccion)
{
    $tablaBd = $mysqli->real_escape_string($tabla);
    $restriccionBd = $mysqli->real_escape_string($restriccion);
    return verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS "
        ."WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='".$tablaBd."' "
        ."AND CONSTRAINT_NAME='".$restriccionBd."'"
    ) === 1;
}

function verificarHistLabTriggerExiste($mysqli, $trigger)
{
    $triggerBd = $mysqli->real_escape_string($trigger);
    return verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.TRIGGERS "
        ."WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME='".$triggerBd."'"
    ) === 1;
}

function verificarHistLabTriggerDefinicion($mysqli, $trigger)
{
    $triggerBd = $mysqli->real_escape_string($trigger);
    return verificarHistLabFila(
        $mysqli,
        "SELECT EVENT_MANIPULATION,ACTION_TIMING,ACTION_STATEMENT "
        ."FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() "
        ."AND TRIGGER_NAME='".$triggerBd."' LIMIT 1"
    );
}

function verificarHistLabEjecutarMigracion($mysqli, $ruta)
{
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        verificarHistLabFallar('No se pudo leer la migracion controlada.');
    }

    $delimitador = ';';
    $buffer = '';
    $numero = 0;
    foreach ($lineas as $linea) {
        $lineaRecortada = trim($linea);
        if ($buffer === '' && ($lineaRecortada === '' || strpos($lineaRecortada, '--') === 0)) {
            continue;
        }
        if ($buffer === '' && preg_match('/^DELIMITER\s+(.+)$/i', $lineaRecortada, $coincidencia)) {
            $delimitador = trim($coincidencia[1]);
            continue;
        }

        $buffer .= $linea.PHP_EOL;
        $contenido = rtrim($buffer);
        $largo = strlen($delimitador);
        if ($largo === 0 || strlen($contenido) < $largo
            || substr($contenido, -$largo) !== $delimitador) {
            continue;
        }

        $sentencia = trim(substr($contenido, 0, -$largo));
        $buffer = '';
        if ($sentencia === '') {
            continue;
        }
        $numero++;
        if (!$mysqli->query($sentencia)) {
            $mysqli->rollback();
            verificarHistLabFallar(
                'La migracion fallo en la sentencia '.$numero.': '.$mysqli->error
            );
        }
    }

    if (trim($buffer) !== '') {
        $mysqli->rollback();
        verificarHistLabFallar('La migracion contiene una sentencia sin delimitador final.');
    }
    verificarHistLabOk('Migracion ejecutada sin errores ('.$numero.' sentencias).');
}

function verificarHistLabHashFuenteSql($aliasTrabajo, $aliasVenta)
{
    $t = $aliasTrabajo;
    $v = $aliasVenta;
    $partes = array(
        "'v1'",
        "IF(".$t.".cod_trabajo_mecanico_dental IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_trabajo_mecanico_dental AS CHAR))))",
        "IF(".$t.".cod_ventaFK IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_ventaFK AS CHAR))))",
        "IF(".$v.".cod_clienteFK IS NULL,'N',CONCAT('V',HEX(CAST(".$v.".cod_clienteFK AS CHAR))))",
        "IF(".$v.".cod_local IS NULL,'N',CONCAT('V',HEX(CAST(".$v.".cod_local AS CHAR))))",
        "IF(".$t.".cod_tipo_trabajoFK IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_tipo_trabajoFK AS CHAR))))",
        "IF(".$t.".cod_mecanicoDentalFK IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_mecanicoDentalFK AS CHAR))))",
        "IF(".$t.".estado IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".estado AS CHAR))))",
        "IF(".$t.".observacion IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".observacion AS BINARY))))",
        "IF(".$t.".colorimetro IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".colorimetro AS BINARY))))",
        "IF(".$t.".costo IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".costo AS CHAR))))",
        "IF(".$t.".fecha_entrega IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".fecha_entrega AS CHAR))))",
        "IF(".$t.".fecha_retiro IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".fecha_retiro AS CHAR))))",
        "IF(".$t.".fecha_creacion IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".fecha_creacion AS CHAR))))",
        "IF(".$t.".cod_usuarioFK_create IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_usuarioFK_create AS CHAR))))",
        "IF(".$t.".fecha_edit IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".fecha_edit AS CHAR))))",
        "IF(".$t.".cod_usuarioFK_edit IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_usuarioFK_edit AS CHAR))))",
        "IF(".$t.".cod_especialistaFK IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_especialistaFK AS CHAR))))",
        "IF(".$t.".cod_localFK IS NULL,'N',CONCAT('V',HEX(CAST(".$t.".cod_localFK AS CHAR))))"
    );
    return 'SHA2(CONCAT_WS(CHAR(31),'.implode(',', $partes).'),256)';
}

function verificarHistLabFirmaLegacy($mysqli)
{
    $hash = verificarHistLabHashFuenteSql('t', 'v');
    $fila = verificarHistLabFila(
        $mysqli,
        'SELECT CAST(COUNT(*) AS CHAR) AS cantidad,'
        .'CAST(COALESCE(SUM(t.cod_trabajo_mecanico_dental),0) AS CHAR) AS suma_ids,'
        .'CAST(COALESCE(SUM(CRC32('.$hash.')),0) AS CHAR) AS suma_crc,'
        .'CAST(COALESCE(BIT_XOR(CRC32('.$hash.')),0) AS CHAR) AS xor_crc '
        .'FROM trabajo_mecanico_dental t '
        .'LEFT JOIN venta v ON v.cod_venta=t.cod_ventaFK'
    );
    return array(
        'cantidad' => isset($fila['cantidad']) ? (string)$fila['cantidad'] : '0',
        'suma_ids' => isset($fila['suma_ids']) ? (string)$fila['suma_ids'] : '0',
        'suma_crc' => isset($fila['suma_crc']) ? (string)$fila['suma_crc'] : '0',
        'xor_crc' => isset($fila['xor_crc']) ? (string)$fila['xor_crc'] : '0'
    );
}

function verificarHistLabFirmaTabla($mysqli, $tabla, $clave)
{
    $permitidas = array(
        'venta' => 'cod_venta',
        'detalle_venta' => 'cod_detalle',
        'cliente' => 'cod_cliente',
        'tipo_trabajo_mecanico_dental' => 'cod_tipo_trabajo_mecanico_dental',
        'mecanico_dental' => 'cod_mecanico_dental',
        'usuario' => 'cod_usuario',
        'local' => 'cod_local',
        'trabajo_laboratorio' => 'id'
    );
    if (!isset($permitidas[$tabla]) || $permitidas[$tabla] !== $clave) {
        verificarHistLabFallar('Se intento verificar una tabla fuera del alcance permitido.');
    }
    $fila = verificarHistLabFila(
        $mysqli,
        'SELECT CAST(COUNT(*) AS CHAR) AS cantidad,'
        .'CAST(COALESCE(SUM(`'.$clave.'`),0) AS CHAR) AS suma_ids,'
        .'CAST(COALESCE(BIT_XOR(CRC32(CAST(`'.$clave.'` AS CHAR))),0) AS CHAR) AS xor_ids '
        .'FROM `'.$tabla.'`'
    );
    return array(
        'cantidad' => isset($fila['cantidad']) ? (string)$fila['cantidad'] : '0',
        'suma_ids' => isset($fila['suma_ids']) ? (string)$fila['suma_ids'] : '0',
        'xor_ids' => isset($fila['xor_ids']) ? (string)$fila['xor_ids'] : '0'
    );
}

function verificarHistLabFirmasProtegidas($mysqli)
{
    $firmas = array();
    $firmas['trabajo_mecanico_dental'] = verificarHistLabFirmaLegacy($mysqli);
    $tablas = array(
        'venta' => 'cod_venta',
        'detalle_venta' => 'cod_detalle',
        'cliente' => 'cod_cliente',
        'tipo_trabajo_mecanico_dental' => 'cod_tipo_trabajo_mecanico_dental',
        'mecanico_dental' => 'cod_mecanico_dental',
        'usuario' => 'cod_usuario',
        'local' => 'cod_local',
        'trabajo_laboratorio' => 'id'
    );
    foreach ($tablas as $tabla => $clave) {
        $firmas[$tabla] = verificarHistLabFirmaTabla($mysqli, $tabla, $clave);
    }
    $firmas['permisos'] = array(
        'catalogo' => (string)verificarHistLabEscalar($mysqli, 'SELECT COUNT(*) FROM listadodeacceso'),
        'asignaciones' => (string)verificarHistLabEscalar($mysqli, 'SELECT COUNT(*) FROM accesosuser')
    );
    return $firmas;
}

function verificarHistLabFirmaSincronizacion($mysqli)
{
    $historicos = verificarHistLabFila(
        $mysqli,
        "SELECT CAST(COUNT(*) AS CHAR) AS cantidad,"
        ."CAST(COALESCE(SUM(id),0) AS CHAR) AS suma_ids,"
        ."CAST(COALESCE(BIT_XOR(CRC32(CONCAT_WS('|',id,cod_trabajo_mecanico_legacyFK,"
        ."fuente_hash,COALESCE(HEX(observacion_snapshot),'#'),"
        ."COALESCE(HEX(colorimetro_snapshot),'#'),COALESCE(costo_snapshot,'#'),"
        ."estado_convalidacion,COALESCE(estado_declarado,'#'),version,"
        ."fecha_sincronizacion,fecha_actualizacion))),0) AS CHAR) AS xor_filas "
        ."FROM trabajo_laboratorio_historico"
    );
    $eventos = verificarHistLabFila(
        $mysqli,
        "SELECT CAST(COUNT(*) AS CHAR) AS cantidad,"
        ."CAST(COALESCE(SUM(id),0) AS CHAR) AS suma_ids,"
        ."CAST(COALESCE(BIT_XOR(CRC32(CONCAT_WS('|',id,id_historicoFK,tipo_evento,"
        ."clave_idempotencia,payload_hash,version_resultante,fecha_servidor))),0) AS CHAR) AS xor_filas "
        ."FROM trabajo_laboratorio_historico_evento"
    );
    return array('historicos' => $historicos, 'eventos' => $eventos);
}

function verificarHistLabComprobarColumnas($mysqli, $tabla, $columnas)
{
    $faltantes = array();
    foreach ($columnas as $columna) {
        if (!verificarHistLabColumnaExiste($mysqli, $tabla, $columna)) {
            $faltantes[] = $columna;
        }
    }
    verificarHistLabAfirmar(
        count($faltantes) === 0,
        'La tabla '.$tabla.' conserva el contrato completo de columnas.'
    );
}

function verificarHistLabMostrarConteosLegacy($mysqli)
{
    $fila = verificarHistLabFila(
        $mysqli,
        "SELECT COUNT(*) AS total,"
        ."COALESCE(SUM(estado='pendiente'),0) AS pendiente,"
        ."COALESCE(SUM(estado='pagado'),0) AS pagado,"
        ."COALESCE(SUM(estado='retirado'),0) AS retirado,"
        ."COALESCE(SUM(estado='entregado'),0) AS entregado,"
        ."COALESCE(SUM(estado='inactivo'),0) AS inactivo,"
        ."COALESCE(SUM(estado IS NULL OR estado NOT IN "
        ."('pendiente','pagado','retirado','entregado','inactivo')),0) AS desconocido "
        ."FROM trabajo_mecanico_dental"
    );
    verificarHistLabInfo(
        'Legacy agregado: total='.intval($fila['total'])
        .', pendiente='.intval($fila['pendiente'])
        .', pagado='.intval($fila['pagado'])
        .', retirado='.intval($fila['retirado'])
        .', entregado='.intval($fila['entregado'])
        .', inactivo='.intval($fila['inactivo'])
        .', desconocido='.intval($fila['desconocido']).'.'
    );

    $localesInvalidos = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_mecanico_dental t '
        .'LEFT JOIN local l ON l.cod_local=t.cod_localFK '
        .'WHERE t.cod_localFK IS NULL OR t.cod_localFK=0 OR l.cod_local IS NULL'
    );
    $enlazados = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio '
        .'WHERE cod_trabajo_mecanico_legacyFK IS NOT NULL'
    );
    verificarHistLabInfo(
        'Relaciones agregadas: locales legacy sin referencia vigente='.$localesInvalidos
        .', legacy ya enlazados al flujo operativo='.$enlazados.'.'
    );
}

function verificarHistLabComprobarEstructura($mysqli)
{
    $columnasHistorico = array(
        'id', 'cod_trabajo_mecanico_legacyFK', 'cod_venta_snapshot',
        'cod_cliente_snapshot', 'cod_tipo_trabajo_snapshot',
        'cod_mecanico_dental_snapshot', 'cod_local_snapshot',
        'cod_especialista_snapshot', 'cod_usuario_creador_snapshot',
        'fecha_creacion_snapshot', 'cod_usuario_editor_snapshot',
        'fecha_edicion_snapshot', 'estado_legacy_snapshot',
        'fecha_retiro_snapshot', 'fecha_entrega_snapshot',
        'observacion_snapshot', 'colorimetro_snapshot', 'costo_snapshot',
        'fuente_hash',
        'estado_convalidacion', 'estado_declarado', 'origen_estado',
        'cod_detalle_ventaFK', 'cod_mecanico_dental_declaradoFK',
        'cod_tecnico_usuarioFK', 'cod_custodio_actualFK',
        'cod_local_declaradoFK', 'fecha_objetivo', 'fecha_retiro_declarada',
        'fecha_entrega_declarada', 'fecha_situacion_declarada',
        'justificacion_ultima', 'id_trabajo_laboratorioFK',
        'fecha_sincronizacion', 'fecha_convalidacion',
        'cod_usuarioFK_convalida', 'fecha_actualizacion',
        'cod_usuarioFK_update', 'version'
    );
    $columnasEvento = array(
        'id', 'id_historicoFK', 'tipo_evento',
        'estado_convalidacion_anterior', 'estado_convalidacion_nuevo',
        'estado_declarado_anterior', 'estado_declarado_nuevo',
        'cod_detalle_venta_anteriorFK', 'cod_detalle_venta_nuevoFK',
        'cod_mecanico_dental_anteriorFK', 'cod_mecanico_dental_nuevoFK',
        'cod_tecnico_usuario_anteriorFK', 'cod_tecnico_usuario_nuevoFK',
        'cod_custodio_anteriorFK', 'cod_custodio_nuevoFK',
        'cod_local_anteriorFK', 'cod_local_nuevoFK', 'fecha_servidor',
        'cod_usuario_actorFK', 'justificacion', 'metadata_json',
        'clave_idempotencia', 'payload_hash', 'version_resultante'
    );

    verificarHistLabComprobarColumnas(
        $mysqli,
        'trabajo_laboratorio_historico',
        $columnasHistorico
    );
    verificarHistLabComprobarColumnas(
        $mysqli,
        'trabajo_laboratorio_historico_evento',
        $columnasEvento
    );
    verificarHistLabAfirmar(
        verificarHistLabColumnaExiste($mysqli, 'trabajo_laboratorio', 'cod_especialistaFK'),
        'El trabajo operativo admite el profesional especialista.'
    );
    verificarHistLabAfirmar(
        verificarHistLabIndiceExiste(
            $mysqli,
            'trabajo_laboratorio',
            'idx_trabajo_laboratorio_especialista'
        ) && verificarHistLabRestriccionExiste(
            $mysqli,
            'trabajo_laboratorio',
            'fk_trabajo_laboratorio_especialista'
        ),
        'El especialista operativo tiene indice y relacion formal.'
    );
    verificarHistLabAfirmar(
        verificarHistLabIndiceUnicoExiste(
            $mysqli,
            'trabajo_laboratorio_historico',
            'uq_tlab_historico_legacy'
        ) && verificarHistLabIndiceUnicoExiste(
            $mysqli,
            'trabajo_laboratorio_historico',
            'uq_tlab_historico_trabajo'
        ),
        'Los vinculos legacy y operativo son unicos e idempotentes.'
    );
    verificarHistLabAfirmar(
        verificarHistLabIndiceUnicoExiste(
            $mysqli,
            'trabajo_laboratorio_historico_evento',
            'uq_tlab_hist_evento_idempotencia'
        ),
        'Cada clave idempotente produce como maximo un evento por historico.'
    );
    verificarHistLabAfirmar(
        verificarHistLabRestriccionExiste(
            $mysqli,
            'trabajo_laboratorio_historico',
            'fk_tlab_historico_legacy'
        ),
        'El vinculo formal impide perder el registro legacy sincronizado.'
    );

    $triggers = array(
        'trg_tlab_hist_evento_no_update',
        'trg_tlab_hist_evento_no_delete',
        'trg_tlab_historico_origen_no_update',
        'trg_tlab_historico_no_delete'
    );
    $presentes = true;
    foreach ($triggers as $trigger) {
        if (!verificarHistLabTriggerExiste($mysqli, $trigger)) {
            $presentes = false;
        }
    }
    verificarHistLabAfirmar(
        $presentes,
        'Los eventos no se sobrescriben y el origen historico no se altera ni elimina.'
    );

    $triggerOrigen = verificarHistLabTriggerDefinicion(
        $mysqli,
        'trg_tlab_historico_origen_no_update'
    );
    $camposOrigen = array(
        'cod_trabajo_mecanico_legacyFK', 'cod_venta_snapshot',
        'cod_cliente_snapshot', 'cod_tipo_trabajo_snapshot',
        'cod_mecanico_dental_snapshot', 'cod_local_snapshot',
        'cod_especialista_snapshot', 'cod_usuario_creador_snapshot',
        'fecha_creacion_snapshot', 'cod_usuario_editor_snapshot',
        'fecha_edicion_snapshot', 'estado_legacy_snapshot',
        'fecha_retiro_snapshot', 'fecha_entrega_snapshot',
        'observacion_snapshot', 'colorimetro_snapshot', 'costo_snapshot',
        'fuente_hash',
        'fecha_sincronizacion'
    );
    $protegeOrigen = isset($triggerOrigen['EVENT_MANIPULATION'])
        && strtoupper((string)$triggerOrigen['EVENT_MANIPULATION']) === 'UPDATE'
        && isset($triggerOrigen['ACTION_TIMING'])
        && strtoupper((string)$triggerOrigen['ACTION_TIMING']) === 'BEFORE'
        && isset($triggerOrigen['ACTION_STATEMENT'])
        && strpos((string)$triggerOrigen['ACTION_STATEMENT'], '<=>') !== false;
    foreach ($camposOrigen as $campoOrigen) {
        if (!isset($triggerOrigen['ACTION_STATEMENT'])
            || strpos((string)$triggerOrigen['ACTION_STATEMENT'], $campoOrigen) === false) {
            $protegeOrigen = false;
        }
    }
    verificarHistLabAfirmar(
        $protegeOrigen,
        'El trigger de actualizacion protege cada campo de procedencia con comparacion NULL-safe.'
    );
}

function verificarHistLabComprobarDatos($mysqli)
{
    $sinCobertura = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_mecanico_dental t '
        .'LEFT JOIN trabajo_laboratorio_historico h '
        .'ON h.cod_trabajo_mecanico_legacyFK=t.cod_trabajo_mecanico_dental '
        .'LEFT JOIN trabajo_laboratorio tl '
        .'ON tl.cod_trabajo_mecanico_legacyFK=t.cod_trabajo_mecanico_dental '
        .'WHERE h.id IS NULL AND tl.id IS NULL'
    );
    verificarHistLabAfirmar(
        $sinCobertura === 0,
        'Todo registro legacy esta sincronizado o ya estaba enlazado al flujo operativo.'
    );

    $duplicadosLegacy = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM ('
        .'SELECT cod_trabajo_mecanico_legacyFK FROM trabajo_laboratorio_historico '
        .'GROUP BY cod_trabajo_mecanico_legacyFK HAVING COUNT(*)>1'
        .') duplicados'
    );
    $duplicadosTrabajo = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM ('
        .'SELECT id_trabajo_laboratorioFK FROM trabajo_laboratorio_historico '
        .'WHERE id_trabajo_laboratorioFK IS NOT NULL '
        .'GROUP BY id_trabajo_laboratorioFK HAVING COUNT(*)>1'
        .') duplicados'
    );
    verificarHistLabAfirmar(
        $duplicadosLegacy === 0 && $duplicadosTrabajo === 0,
        'No existen vinculos historicos duplicados.'
    );

    $snapshotsDistintos = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico h '
        .'INNER JOIN trabajo_mecanico_dental t '
        .'ON t.cod_trabajo_mecanico_dental=h.cod_trabajo_mecanico_legacyFK '
        .'LEFT JOIN venta v ON v.cod_venta=t.cod_ventaFK '
        .'WHERE NOT (h.cod_venta_snapshot <=> t.cod_ventaFK) '
        .'OR NOT (h.cod_cliente_snapshot <=> v.cod_clienteFK) '
        .'OR NOT (h.cod_tipo_trabajo_snapshot <=> t.cod_tipo_trabajoFK) '
        .'OR NOT (h.cod_mecanico_dental_snapshot <=> t.cod_mecanicoDentalFK) '
        .'OR NOT (h.cod_local_snapshot <=> t.cod_localFK) '
        .'OR NOT (h.cod_especialista_snapshot <=> t.cod_especialistaFK) '
        .'OR NOT (h.cod_usuario_creador_snapshot <=> t.cod_usuarioFK_create) '
        .'OR NOT (h.fecha_creacion_snapshot <=> t.fecha_creacion) '
        .'OR NOT (h.cod_usuario_editor_snapshot <=> t.cod_usuarioFK_edit) '
        .'OR NOT (h.fecha_edicion_snapshot <=> t.fecha_edit) '
        .'OR NOT (h.estado_legacy_snapshot <=> t.estado) '
        ."OR NOT (h.fecha_retiro_snapshot <=> NULLIF(t.fecha_retiro,'0000-00-00')) "
        ."OR NOT (h.fecha_entrega_snapshot <=> NULLIF(t.fecha_entrega,'0000-00-00')) "
        .'OR NOT (h.observacion_snapshot <=> t.observacion) '
        .'OR NOT (h.colorimetro_snapshot <=> t.colorimetro) '
        .'OR NOT (h.costo_snapshot <=> t.costo)'
    );
    verificarHistLabAfirmar(
        $snapshotsDistintos === 0,
        'Los snapshots numericos, descriptivos, de estado y de fecha coinciden con su fuente legacy.'
    );

    $hashFuente = verificarHistLabHashFuenteSql('t', 'v');
    $hashDistinto = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico h '
        .'INNER JOIN trabajo_mecanico_dental t '
        .'ON t.cod_trabajo_mecanico_dental=h.cod_trabajo_mecanico_legacyFK '
        .'LEFT JOIN venta v ON v.cod_venta=t.cod_ventaFK '
        .'WHERE NOT (h.fuente_hash <=> '.$hashFuente.')'
    );
    verificarHistLabAfirmar(
        $hashDistinto === 0,
        'La huella agregada confirma que el contenido legacy permanece intacto.'
    );

    $mapeosInvalidos = verificarHistLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM trabajo_laboratorio_historico h "
        ."WHERE h.version=1 AND h.cod_usuarioFK_update IS NULL AND ("
        ."(h.estado_legacy_snapshot IN ('pendiente','pagado') AND NOT ("
        ."h.estado_convalidacion='situacion_por_actualizar' "
        ."AND h.estado_declarado IS NULL "
        ."AND h.origen_estado='legacy_sin_definir' "
        ."AND h.fecha_convalidacion IS NULL)) OR "
        ."(h.estado_legacy_snapshot='retirado' AND NOT ("
        ."h.estado_convalidacion='sincronizado_automatico' "
        ."AND h.estado_declarado='en_laboratorio' "
        ."AND h.origen_estado='migracion_automatica' "
        ."AND h.fecha_convalidacion IS NOT NULL)) OR "
        ."(h.estado_legacy_snapshot='entregado' AND NOT ("
        ."h.estado_convalidacion='sincronizado_automatico' "
        ."AND h.estado_declarado='pendiente_revision' "
        ."AND h.origen_estado='migracion_automatica' "
        ."AND h.fecha_convalidacion IS NOT NULL)) OR "
        ."(h.estado_legacy_snapshot='inactivo' AND NOT ("
        ."h.estado_convalidacion='sincronizado_automatico' "
        ."AND h.estado_declarado='cancelado' "
        ."AND h.origen_estado='migracion_automatica' "
        ."AND h.fecha_convalidacion IS NOT NULL)) OR "
        ."((h.estado_legacy_snapshot IS NULL OR h.estado_legacy_snapshot NOT IN "
        ."('pendiente','pagado','retirado','entregado','inactivo')) AND NOT ("
        ."h.estado_convalidacion='situacion_por_actualizar' "
        ."AND h.estado_declarado IS NULL "
        ."AND h.origen_estado='legacy_sin_definir' "
        ."AND h.fecha_convalidacion IS NULL)))"
    );
    verificarHistLabAfirmar(
        $mapeosInvalidos === 0,
        'Los estados iniciales respetan la equivalencia aprobada y fallan de forma segura.'
    );

    $detallesInferidos = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico '
        .'WHERE version=1 AND cod_usuarioFK_update IS NULL '
        .'AND cod_detalle_ventaFK IS NOT NULL'
    );
    verificarHistLabAfirmar(
        $detallesInferidos === 0,
        'La sincronizacion no eligio tratamientos de venta de forma automatica.'
    );

    $declaracionesBaseInvalidas = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico h '
        .'INNER JOIN trabajo_mecanico_dental t '
        .'ON t.cod_trabajo_mecanico_dental=h.cod_trabajo_mecanico_legacyFK '
        .'LEFT JOIN venta v ON v.cod_venta=t.cod_ventaFK '
        .'LEFT JOIN local ll ON ll.cod_local=t.cod_localFK '
        .'LEFT JOIN local lv ON lv.cod_local=v.cod_local '
        .'WHERE h.version=1 AND h.cod_usuarioFK_update IS NULL AND ('
        .'h.cod_mecanico_dental_declaradoFK IS NOT NULL '
        .'OR h.cod_tecnico_usuarioFK IS NOT NULL '
        .'OR NOT (h.cod_local_declaradoFK <=> COALESCE(ll.cod_local,lv.cod_local))'
        .')'
    );
    verificarHistLabAfirmar(
        $declaracionesBaseInvalidas === 0,
        'El mecanico espera declaracion administrativa y el local usa un respaldo vigente sin alterar su snapshot.'
    );

    $eventosFaltantes = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico h '
        .'LEFT JOIN trabajo_laboratorio_historico_evento e '
        .'ON e.id_historicoFK=h.id '
        ."AND e.clave_idempotencia=CONCAT('sync-legacy-',h.cod_trabajo_mecanico_legacyFK) "
        .'WHERE e.id IS NULL'
    );
    $eventosInicialesInvalidos = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico h '
        .'INNER JOIN trabajo_laboratorio_historico_evento e '
        .'ON e.id_historicoFK=h.id '
        ."AND e.clave_idempotencia=CONCAT('sync-legacy-',h.cod_trabajo_mecanico_legacyFK) "
        ."WHERE e.tipo_evento<>'sincronizacion_historica' "
        .'OR e.cod_usuario_actorFK IS NOT NULL '
        .'OR e.estado_convalidacion_anterior IS NOT NULL '
        .'OR e.estado_declarado_anterior IS NOT NULL '
        .'OR e.cod_detalle_venta_anteriorFK IS NOT NULL '
        .'OR e.cod_mecanico_dental_anteriorFK IS NOT NULL '
        .'OR e.cod_tecnico_usuario_anteriorFK IS NOT NULL '
        .'OR e.cod_custodio_anteriorFK IS NOT NULL '
        .'OR e.cod_local_anteriorFK IS NOT NULL '
        .'OR e.cod_mecanico_dental_nuevoFK IS NOT NULL '
        .'OR e.cod_tecnico_usuario_nuevoFK IS NOT NULL '
        .'OR e.cod_custodio_nuevoFK IS NOT NULL '
        .'OR CHAR_LENGTH(e.payload_hash)<>64 '
        .'OR e.version_resultante<>1'
    );
    verificarHistLabAfirmar(
        $eventosFaltantes === 0 && $eventosInicialesInvalidos === 0,
        'Cada historico tiene un unico evento inicial automatico, sin actor inventado.'
    );

    $promocionesInconsistentes = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico h '
        .'LEFT JOIN trabajo_laboratorio tl ON tl.id=h.id_trabajo_laboratorioFK '
        .'WHERE h.id_trabajo_laboratorioFK IS NOT NULL '
        .'AND (tl.id IS NULL OR NOT ('
        .'tl.cod_trabajo_mecanico_legacyFK <=> h.cod_trabajo_mecanico_legacyFK))'
    );
    verificarHistLabAfirmar(
        $promocionesInconsistentes === 0,
        'Todo historico promovido mantiene el mismo vinculo legacy en el flujo operativo.'
    );
}

function verificarHistLabMostrarConteosSincronizacion($mysqli)
{
    $fila = verificarHistLabFila(
        $mysqli,
        "SELECT COUNT(*) AS total,"
        ."COALESCE(SUM(estado_convalidacion='situacion_por_actualizar'),0) AS actualizar,"
        ."COALESCE(SUM(estado_convalidacion='sincronizado_automatico'),0) AS automatico,"
        ."COALESCE(SUM(estado_convalidacion NOT IN "
        ."('situacion_por_actualizar','sincronizado_automatico')),0) AS intervenido,"
        ."COALESCE(SUM(cod_detalle_ventaFK IS NULL),0) AS sin_detalle,"
        ."COALESCE(SUM(cod_tecnico_usuarioFK IS NULL),0) AS sin_tecnico,"
        ."COALESCE(SUM(cod_custodio_actualFK IS NULL),0) AS sin_custodio,"
        ."COALESCE(SUM(id_trabajo_laboratorioFK IS NOT NULL),0) AS promovido "
        ."FROM trabajo_laboratorio_historico"
    );
    $eventos = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_laboratorio_historico_evento'
    );
    verificarHistLabInfo(
        'Sincronizacion agregada: historicos='.intval($fila['total'])
        .', situacion_por_actualizar='.intval($fila['actualizar'])
        .', sincronizados_automaticamente='.intval($fila['automatico'])
        .', intervenidos='.intval($fila['intervenido'])
        .', sin_detalle='.intval($fila['sin_detalle'])
        .', sin_tecnico='.intval($fila['sin_tecnico'])
        .', sin_custodio='.intval($fila['sin_custodio'])
        .', promovidos='.intval($fila['promovido'])
        .', eventos='.$eventos.'.'
    );
}

$argumentos = isset($argv) ? $argv : array();
$aplicar = in_array('--aplicar-migracion', $argumentos, true);
$ayuda = in_array('--ayuda', $argumentos, true) || in_array('-h', $argumentos, true);
foreach ($argumentos as $indice => $argumento) {
    if ($indice === 0) {
        continue;
    }
    if (!in_array($argumento, array('--aplicar-migracion', '--ayuda', '-h'), true)) {
        verificarHistLabFallar('Argumento no reconocido. Use --ayuda para consultar el uso.');
    }
}
if ($ayuda) {
    fwrite(
        STDOUT,
        "Uso:\n"
        ."  php scripts/verificar_sincronizacion_historica_laboratorio.php\n"
        ."  php scripts/verificar_sincronizacion_historica_laboratorio.php --aplicar-migracion\n"
    );
    exit(0);
}

$rutaMigracion = dirname(__DIR__).'/actualizacion_21072026_sincronizacion_historica_laboratorio.sql';
$fuenteMigracion = file_get_contents($rutaMigracion);
verificarHistLabAfirmar(
    $fuenteMigracion !== false && $fuenteMigracion !== '',
    'La migracion historica esta disponible para su revision controlada.'
);
$fuenteSinComentarios = preg_replace('/^\s*--.*$/m', '', $fuenteMigracion);
verificarHistLabAfirmar(
    preg_match('/\b(?:listadodeacceso|accesosuser)\b/i', $fuenteSinComentarios) !== 1,
    'La migracion no crea ni concede permisos.'
);

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    verificarHistLabFallar('No se pudo conectar con la base local.');
}

$tablasBase = array(
    'trabajo_mecanico_dental', 'trabajo_laboratorio', 'venta', 'detalle_venta',
    'cliente', 'tipo_trabajo_mecanico_dental', 'mecanico_dental', 'usuario',
    'local', 'listadodeacceso', 'accesosuser'
);
$faltantesBase = array();
foreach ($tablasBase as $tablaBase) {
    if (!verificarHistLabTablaExiste($mysqli, $tablaBase)) {
        $faltantesBase[] = $tablaBase;
    }
}
verificarHistLabAfirmar(
    count($faltantesBase) === 0,
    'Las tablas base y el nucleo de laboratorio estan disponibles.'
);

verificarHistLabInfo(
    $aplicar
        ? 'Modo de aplicacion controlada: la migracion se ejecutara dos veces.'
        : 'Modo de solo lectura: no se ejecutara ninguna sentencia de la migracion.'
);
verificarHistLabMostrarConteosLegacy($mysqli);

$sinLocalDeRespaldo = verificarHistLabEscalar(
    $mysqli,
    'SELECT COUNT(*) FROM trabajo_mecanico_dental t '
    .'LEFT JOIN venta v ON v.cod_venta=t.cod_ventaFK '
    .'LEFT JOIN local ll ON ll.cod_local=t.cod_localFK '
    .'LEFT JOIN local lv ON lv.cod_local=v.cod_local '
    .'WHERE ll.cod_local IS NULL AND lv.cod_local IS NULL'
);
verificarHistLabAfirmar(
    $sinLocalDeRespaldo === 0,
    'Cada trabajo tiene un local legacy vigente o un local de venta valido como respaldo.'
);

$firmasAntes = null;
if ($aplicar) {
    $firmasAntes = verificarHistLabFirmasProtegidas($mysqli);
    verificarHistLabEjecutarMigracion($mysqli, $rutaMigracion);
    $firmasPrimera = verificarHistLabFirmasProtegidas($mysqli);
    verificarHistLabAfirmar(
        $firmasAntes === $firmasPrimera,
        'La primera ejecucion preservo legacy, ventas, pacientes, catalogos, trabajos y permisos.'
    );
    $firmaSincronizacionPrimera = verificarHistLabFirmaSincronizacion($mysqli);

    verificarHistLabEjecutarMigracion($mysqli, $rutaMigracion);
    $firmasSegunda = verificarHistLabFirmasProtegidas($mysqli);
    $firmaSincronizacionSegunda = verificarHistLabFirmaSincronizacion($mysqli);
    verificarHistLabAfirmar(
        $firmasAntes === $firmasSegunda,
        'La segunda ejecucion volvio a preservar todos los datos y permisos protegidos.'
    );
    verificarHistLabAfirmar(
        $firmaSincronizacionPrimera === $firmaSincronizacionSegunda,
        'La segunda ejecucion no duplico ni sobrescribio historicos o eventos.'
    );
}

$existeHistorico = verificarHistLabTablaExiste($mysqli, 'trabajo_laboratorio_historico');
$existeEvento = verificarHistLabTablaExiste($mysqli, 'trabajo_laboratorio_historico_evento');
if (!$existeHistorico && !$existeEvento) {
    $pendientes = verificarHistLabEscalar(
        $mysqli,
        'SELECT COUNT(*) FROM trabajo_mecanico_dental t '
        .'WHERE NOT EXISTS ('
        .'SELECT 1 FROM trabajo_laboratorio tl '
        .'WHERE tl.cod_trabajo_mecanico_legacyFK=t.cod_trabajo_mecanico_dental)'
    );
    verificarHistLabInfo(
        'La migracion aun no esta aplicada. Registros elegibles agregados='.$pendientes.'.'
    );
    $mysqli->close();
    verificarHistLabOk('Diagnostico previo finalizado sin modificar la base de datos.');
    exit(0);
}

verificarHistLabAfirmar(
    $existeHistorico && $existeEvento,
    'La estructura historica no debe quedar aplicada parcialmente.'
);
verificarHistLabComprobarEstructura($mysqli);
verificarHistLabComprobarDatos($mysqli);
verificarHistLabMostrarConteosSincronizacion($mysqli);

$mysqli->close();
verificarHistLabOk(
    $aplicar
        ? 'Aplicacion y verificacion historica finalizadas.'
        : 'Verificacion historica de solo lectura finalizada.'
);

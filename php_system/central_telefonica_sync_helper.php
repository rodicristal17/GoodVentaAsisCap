<?php

require_once __DIR__.'/central_telefonica_helper.php';

class CentralTelefonicaSyncExcepcion extends Exception
{
    public $codigoOperacion;

    public function __construct($codigo, $mensaje)
    {
        parent::__construct($mensaje);
        $this->codigoOperacion = centralTelefonicaErrorSeguro($codigo);
    }
}

function centralTelefonicaSyncLanzar($codigo, $mensaje)
{
    throw new CentralTelefonicaSyncExcepcion($codigo, $mensaje);
}

function centralTelefonicaSyncIdentificador($valor)
{
    $valor = trim((string)$valor);
    if ($valor === '' || !preg_match('/^[A-Za-z0-9_]+$/', $valor)) {
        centralTelefonicaSyncLanzar('esquema_cdr_incompatible', 'Identificador CDR invalido.');
    }
    return '`'.$valor.'`';
}

function centralTelefonicaSyncConectarIssabel($config)
{
    if (!centralTelefonicaConfiguracionDisponible($config)) {
        centralTelefonicaSyncLanzar(
            'configuracion_no_disponible',
            'Falta la configuracion privada de Issabel.'
        );
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $conexion = @new mysqli(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database'],
        intval($config['port'])
    );
    if ($conexion->connect_errno) {
        centralTelefonicaSyncLanzar(
            'conexion_issabel_no_disponible',
            'No se pudo abrir la conexion de solo lectura con Issabel.'
        );
    }
    if (!empty($config['charset'])) {
        @$conexion->set_charset($config['charset']);
    }
    return $conexion;
}

function centralTelefonicaSyncColumnasFuente($conexion, $tabla)
{
    $tablaSql = centralTelefonicaSyncIdentificador($tabla);
    $resultado = $conexion->query('SHOW COLUMNS FROM '.$tablaSql);
    if (!$resultado) {
        centralTelefonicaSyncLanzar(
            'tabla_cdr_no_disponible',
            'La tabla CDR configurada no se encuentra disponible.'
        );
    }
    $columnas = array();
    while ($fila = $resultado->fetch_assoc()) {
        $columnas[strtolower((string)$fila['Field'])] = (string)$fila['Field'];
    }
    return $columnas;
}

function centralTelefonicaSyncResolverColumna($columnas, $candidatas, $obligatoria)
{
    foreach ($candidatas as $candidata) {
        $clave = strtolower((string)$candidata);
        if (isset($columnas[$clave])) {
            return $columnas[$clave];
        }
    }
    if ($obligatoria) {
        centralTelefonicaSyncLanzar(
            'esquema_cdr_incompatible',
            'El CDR no contiene todas las columnas obligatorias.'
        );
    }
    return '';
}

function centralTelefonicaSyncMapaFuente($columnas)
{
    return array(
        'fecha_inicio' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('calldate', 'start', 'starttime', 'eventtime'),
            true
        ),
        'origen_original' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('src', 'source'),
            true
        ),
        'destino_original' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('dst', 'destination'),
            true
        ),
        'contexto' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('dcontext', 'context'),
            false
        ),
        'canal' => centralTelefonicaSyncResolverColumna($columnas, array('channel'), false),
        'canal_destino' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('dstchannel', 'destinationchannel'),
            false
        ),
        'disposicion' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('disposition', 'status'),
            true
        ),
        'duracion_seg' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('duration'),
            true
        ),
        'hablado_seg' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('billsec', 'billseconds'),
            true
        ),
        'cdr_uniqueid' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('uniqueid'),
            true
        ),
        'cdr_linkedid' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('linkedid'),
            false
        ),
        'cdr_sequence' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('sequence', 'sequence_number'),
            false
        ),
        'grabacion_referencia' => centralTelefonicaSyncResolverColumna(
            $columnas,
            array('recordingfile', 'recording', 'filename'),
            false
        ),
        'clid' => centralTelefonicaSyncResolverColumna($columnas, array('clid'), false),
        'lastapp' => centralTelefonicaSyncResolverColumna($columnas, array('lastapp'), false)
    );
}

function centralTelefonicaSyncExpresionColumna($columna, $alias, $predeterminado)
{
    if ($columna === '') {
        return $predeterminado.' AS `'.$alias.'`';
    }
    return centralTelefonicaSyncIdentificador($columna).' AS `'.$alias.'`';
}

function centralTelefonicaSyncLeerFuente($conexion, $config, $desde, $limite)
{
    $columnas = centralTelefonicaSyncColumnasFuente($conexion, $config['table']);
    $mapa = centralTelefonicaSyncMapaFuente($columnas);
    $seleccion = array();
    foreach ($mapa as $alias => $columna) {
        $predeterminado = $alias === 'cdr_sequence' ? 'NULL' : "''";
        $seleccion[] = centralTelefonicaSyncExpresionColumna($columna, $alias, $predeterminado);
    }

    $tablaSql = centralTelefonicaSyncIdentificador($config['table']);
    $fechaSql = centralTelefonicaSyncIdentificador($mapa['fecha_inicio']);
    $uniqueSql = centralTelefonicaSyncIdentificador($mapa['cdr_uniqueid']);
    $orden = $fechaSql.' ASC,'.$uniqueSql.' ASC';
    if ($mapa['cdr_sequence'] !== '') {
        $orden .= ','.centralTelefonicaSyncIdentificador($mapa['cdr_sequence']).' ASC';
    }
    $sql = 'SELECT '.implode(',', $seleccion)
        .' FROM '.$tablaSql
        .' WHERE '.$fechaSql.'>=?'
        .' ORDER BY '.$orden
        .' LIMIT ?';
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        centralTelefonicaSyncLanzar(
            'consulta_cdr_no_disponible',
            'No se pudo preparar la lectura incremental del CDR.'
        );
    }
    $limite = max(100, min(20000, intval($limite)));
    $stmt->bind_param('si', $desde, $limite);
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaSyncLanzar(
            'consulta_cdr_no_disponible',
            'No se pudo ejecutar la lectura incremental del CDR.'
        );
    }
    $resultado = $stmt->get_result();
    $filas = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $filas[] = $fila;
    }
    $stmt->close();
    return array('filas' => $filas, 'mapa' => $mapa);
}

function centralTelefonicaSyncUltimoCursor($mysqli, $config)
{
    $sql = "SELECT watermark_fecha,watermark_uniqueid
        FROM central_telefonica_sincronizacion
        WHERE estado='exitosa' AND watermark_fecha IS NOT NULL
        ORDER BY id_sincronizacion DESC LIMIT 1";
    $resultado = $mysqli->query($sql);
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if (!$fila || trim((string)$fila['watermark_fecha']) === '') {
        return array(
            'desde' => date(
                'Y-m-d H:i:s',
                strtotime('-'.intval($config['initial_days']).' days')
            ),
            'uniqueid' => ''
        );
    }
    return array(
        'desde' => date(
            'Y-m-d H:i:s',
            strtotime($fila['watermark_fecha'].' -'.intval($config['overlap_minutes']).' minutes')
        ),
        'uniqueid' => $fila['watermark_uniqueid']
    );
}

function centralTelefonicaSyncAbrirRegistro($mysqli, $desde)
{
    $estado = 'en_proceso';
    $stmt = $mysqli->prepare(
        'INSERT INTO central_telefonica_sincronizacion '
        .'(fecha_inicio,estado,fuente_desde,registros_consultados,registros_nuevos,'
        .'registros_actualizados,duracion_ms) VALUES (NOW(),?,?,0,0,0,0)'
    );
    if (!$stmt) {
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo iniciar el registro de sincronizacion.'
        );
    }
    $stmt->bind_param('ss', $estado, $desde);
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo registrar la sincronizacion.'
        );
    }
    $id = intval($mysqli->insert_id);
    $stmt->close();
    return $id;
}

function centralTelefonicaSyncCerrarRegistro($mysqli, $id, $datos)
{
    $sql = 'UPDATE central_telefonica_sincronizacion SET '
        .'fecha_fin=NOW(),estado=?,registros_consultados=?,registros_nuevos=?,'
        .'registros_actualizados=?,duracion_ms=?,watermark_fecha=?,'
        .'watermark_uniqueid=?,codigo_error=? WHERE id_sincronizacion=?';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $estado = (string)$datos['estado'];
    $consultados = intval($datos['consultados']);
    $nuevos = intval($datos['nuevos']);
    $actualizados = intval($datos['actualizados']);
    $duracion = intval($datos['duracion_ms']);
    $watermarkFecha = $datos['watermark_fecha'] === '' ? null : $datos['watermark_fecha'];
    $watermarkUniqueid = $datos['watermark_uniqueid'] === '' ? null : $datos['watermark_uniqueid'];
    $codigoError = $datos['codigo_error'] === '' ? null : $datos['codigo_error'];
    $stmt->bind_param(
        'siiiisssi',
        $estado,
        $consultados,
        $nuevos,
        $actualizados,
        $duracion,
        $watermarkFecha,
        $watermarkUniqueid,
        $codigoError,
        $id
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function centralTelefonicaSyncPrepararSegmento($fila, $config)
{
    $fecha = trim((string)$fila['fecha_inicio']);
    if ($fecha === '' || strtotime($fecha) === false) {
        centralTelefonicaSyncLanzar(
            'esquema_cdr_incompatible',
            'El CDR contiene una fecha no reconocida.'
        );
    }
    $segmento = array(
        'cdr_uniqueid' => trim((string)$fila['cdr_uniqueid']),
        'cdr_linkedid' => trim((string)$fila['cdr_linkedid']),
        'cdr_sequence' => $fila['cdr_sequence'] === null || $fila['cdr_sequence'] === ''
            ? null : intval($fila['cdr_sequence']),
        'fecha_inicio' => date('Y-m-d H:i:s', strtotime($fecha)),
        'origen_original' => trim((string)$fila['origen_original']),
        'destino_original' => trim((string)$fila['destino_original']),
        'contexto' => trim((string)$fila['contexto']),
        'canal' => trim((string)$fila['canal']),
        'canal_destino' => trim((string)$fila['canal_destino']),
        'disposicion' => trim((string)$fila['disposicion']),
        'duracion_seg' => max(0, intval($fila['duracion_seg'])),
        'hablado_seg' => max(0, intval($fila['hablado_seg'])),
        'grabacion_referencia' => trim((string)$fila['grabacion_referencia'])
    );
    if ($segmento['cdr_uniqueid'] === '') {
        centralTelefonicaSyncLanzar(
            'esquema_cdr_incompatible',
            'El CDR contiene un registro sin uniqueid.'
        );
    }
    $segmento['origen_normalizado'] = centralTelefonicaNormalizarTelefono(
        $segmento['origen_original']
    );
    $segmento['destino_normalizado'] = centralTelefonicaNormalizarTelefono(
        $segmento['destino_original']
    );
    $segmento['extension'] = centralTelefonicaNumeroEsExtension(
        $segmento['origen_original'],
        $config
    ) ? preg_replace('/[^0-9]/', '', $segmento['origen_original'])
        : (centralTelefonicaNumeroEsExtension($segmento['destino_original'], $config)
            ? preg_replace('/[^0-9]/', '', $segmento['destino_original']) : '');
    $segmento['grabacion_disponible'] = $segmento['grabacion_referencia'] !== '' ? 1 : 0;
    $segmento['datos_tecnicos'] = json_encode(array(
        'clid' => isset($fila['clid']) ? $fila['clid'] : '',
        'lastapp' => isset($fila['lastapp']) ? $fila['lastapp'] : ''
    ), JSON_UNESCAPED_UNICODE);
    $segmento['fuente_clave'] = centralTelefonicaClaveSegmento($segmento);
    $segmento['grupo_clave'] = centralTelefonicaClaveGrupo($segmento);
    return $segmento;
}

function centralTelefonicaSyncGuardarSegmento($mysqli, $segmento)
{
    $sql = "INSERT INTO central_telefonica_cdr_segmento
        (fuente,fuente_clave,grupo_clave,cdr_uniqueid,cdr_linkedid,cdr_sequence,
         fecha_inicio,origen_original,destino_original,origen_normalizado,
         destino_normalizado,extension,contexto,canal,canal_destino,disposicion,
         duracion_seg,hablado_seg,grabacion_disponible,grabacion_referencia,
         datos_tecnicos,fecha_captura,fecha_actualizacion)
        VALUES
        ('issabel',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
        ON DUPLICATE KEY UPDATE
         grupo_clave=VALUES(grupo_clave),cdr_linkedid=VALUES(cdr_linkedid),
         cdr_sequence=VALUES(cdr_sequence),fecha_inicio=VALUES(fecha_inicio),
         origen_original=VALUES(origen_original),destino_original=VALUES(destino_original),
         origen_normalizado=VALUES(origen_normalizado),destino_normalizado=VALUES(destino_normalizado),
         extension=VALUES(extension),contexto=VALUES(contexto),canal=VALUES(canal),
         canal_destino=VALUES(canal_destino),disposicion=VALUES(disposicion),
         duracion_seg=VALUES(duracion_seg),hablado_seg=VALUES(hablado_seg),
         grabacion_disponible=VALUES(grabacion_disponible),
         grabacion_referencia=VALUES(grabacion_referencia),
         datos_tecnicos=VALUES(datos_tecnicos),fecha_actualizacion=NOW()";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo preparar el segmento telefonico.'
        );
    }
    $sequence = $segmento['cdr_sequence'];
    $stmt->bind_param(
        'ssssissssssssssiiiss',
        $segmento['fuente_clave'],
        $segmento['grupo_clave'],
        $segmento['cdr_uniqueid'],
        $segmento['cdr_linkedid'],
        $sequence,
        $segmento['fecha_inicio'],
        $segmento['origen_original'],
        $segmento['destino_original'],
        $segmento['origen_normalizado'],
        $segmento['destino_normalizado'],
        $segmento['extension'],
        $segmento['contexto'],
        $segmento['canal'],
        $segmento['canal_destino'],
        $segmento['disposicion'],
        $segmento['duracion_seg'],
        $segmento['hablado_seg'],
        $segmento['grabacion_disponible'],
        $segmento['grabacion_referencia'],
        $segmento['datos_tecnicos']
    );
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo guardar el segmento telefonico.'
        );
    }
    $afectadas = intval($stmt->affected_rows);
    $stmt->close();
    return $afectadas;
}

function centralTelefonicaSyncSegmentosGrupo($mysqli, $grupo)
{
    $sql = "SELECT id_segmento,cdr_uniqueid,cdr_linkedid,cdr_sequence,
            fecha_inicio,origen_original,destino_original,origen_normalizado,
            destino_normalizado,extension,contexto,canal,canal_destino,
            disposicion,duracion_seg,hablado_seg,grabacion_disponible,
            grabacion_referencia
        FROM central_telefonica_cdr_segmento
        WHERE grupo_clave=? ORDER BY fecha_inicio,id_segmento";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo preparar la consolidacion telefonica.'
        );
    }
    $stmt->bind_param('s', $grupo);
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudieron consultar los segmentos telefonicos.'
        );
    }
    $resultado = $stmt->get_result();
    $segmentos = array();
    while ($resultado && ($fila = $resultado->fetch_assoc())) {
        $segmentos[] = $fila;
    }
    $stmt->close();
    return $segmentos;
}

function centralTelefonicaSyncGuardarConsolidado($mysqli, $llamada)
{
    $sql = "INSERT INTO central_telefonica_llamada
        (llamada_clave,grupo_clave,cdr_linkedid,cdr_uniqueid_principal,
         fecha_inicio,fecha_fin,tipo,estado,origen_original,destino_original,
         origen_normalizado,destino_normalizado,extension,duracion_seg,
         hablado_seg,cantidad_segmentos,grabacion_disponible,
         grabacion_segmento_id,clasificacion_motivo,fecha_creacion,fecha_actualizacion)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
        ON DUPLICATE KEY UPDATE
         grupo_clave=VALUES(grupo_clave),cdr_linkedid=VALUES(cdr_linkedid),
         cdr_uniqueid_principal=VALUES(cdr_uniqueid_principal),
         fecha_inicio=VALUES(fecha_inicio),fecha_fin=VALUES(fecha_fin),
         tipo=VALUES(tipo),estado=VALUES(estado),
         origen_original=VALUES(origen_original),destino_original=VALUES(destino_original),
         origen_normalizado=VALUES(origen_normalizado),destino_normalizado=VALUES(destino_normalizado),
         extension=VALUES(extension),duracion_seg=VALUES(duracion_seg),
         hablado_seg=VALUES(hablado_seg),cantidad_segmentos=VALUES(cantidad_segmentos),
         grabacion_disponible=VALUES(grabacion_disponible),
         grabacion_segmento_id=VALUES(grabacion_segmento_id),
         clasificacion_motivo=VALUES(clasificacion_motivo),fecha_actualizacion=NOW()";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo preparar la llamada consolidada.'
        );
    }
    $segmentoGrabacion = $llamada['grabacion_segmento_id'];
    $stmt->bind_param(
        'sssssssssssssiiiiis',
        $llamada['llamada_clave'],
        $llamada['grupo_clave'],
        $llamada['cdr_linkedid'],
        $llamada['cdr_uniqueid_principal'],
        $llamada['fecha_inicio'],
        $llamada['fecha_fin'],
        $llamada['tipo'],
        $llamada['estado'],
        $llamada['origen_original'],
        $llamada['destino_original'],
        $llamada['origen_normalizado'],
        $llamada['destino_normalizado'],
        $llamada['extension'],
        $llamada['duracion_seg'],
        $llamada['hablado_seg'],
        $llamada['cantidad_segmentos'],
        $llamada['grabacion_disponible'],
        $segmentoGrabacion,
        $llamada['clasificacion_motivo']
    );
    if (!$stmt->execute()) {
        $stmt->close();
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'No se pudo guardar la llamada consolidada.'
        );
    }
    $stmt->close();
}

function centralTelefonicaEjecutarSincronizacion($mysqli, $config, $opciones)
{
    if (!centralTelefonicaEstructuraDisponible($mysqli)) {
        centralTelefonicaSyncLanzar(
            'persistencia_no_disponible',
            'La migracion de Central Telefonica no esta aplicada.'
        );
    }
    $inicioMicro = microtime(true);
    $cursor = centralTelefonicaSyncUltimoCursor($mysqli, $config);
    $limite = isset($opciones['limite']) ? intval($opciones['limite']) : $config['batch_limit'];
    $soloLectura = !empty($opciones['dry_run']);
    $idRegistro = 0;
    $conexionIssabel = null;
    $resultadoFinal = array(
        'estado' => 'exitosa',
        'consultados' => 0,
        'nuevos' => 0,
        'actualizados' => 0,
        'grupos' => 0,
        'desde' => $cursor['desde'],
        'watermark_fecha' => '',
        'watermark_uniqueid' => '',
        'duracion_ms' => 0,
        'dry_run' => $soloLectura
    );

    try {
        if (!$soloLectura) {
            $idRegistro = centralTelefonicaSyncAbrirRegistro($mysqli, $cursor['desde']);
        }
        $conexionIssabel = centralTelefonicaSyncConectarIssabel($config);
        $lectura = centralTelefonicaSyncLeerFuente(
            $conexionIssabel,
            $config,
            $cursor['desde'],
            $limite
        );
        $resultadoFinal['consultados'] = count($lectura['filas']);
        if ($soloLectura) {
            foreach ($lectura['filas'] as $filaPrueba) {
                $preparado = centralTelefonicaSyncPrepararSegmento($filaPrueba, $config);
                $resultadoFinal['watermark_fecha'] = $preparado['fecha_inicio'];
                $resultadoFinal['watermark_uniqueid'] = $preparado['cdr_uniqueid'];
            }
            $resultadoFinal['duracion_ms'] = intval(round((microtime(true) - $inicioMicro) * 1000));
            $conexionIssabel->close();
            return $resultadoFinal;
        }

        $mysqli->begin_transaction();
        $grupos = array();
        foreach ($lectura['filas'] as $fila) {
            $segmento = centralTelefonicaSyncPrepararSegmento($fila, $config);
            $afectadas = centralTelefonicaSyncGuardarSegmento($mysqli, $segmento);
            if ($afectadas === 1) {
                $resultadoFinal['nuevos']++;
            } elseif ($afectadas >= 2) {
                $resultadoFinal['actualizados']++;
            }
            $grupos[$segmento['grupo_clave']] = true;
            if ($resultadoFinal['watermark_fecha'] === ''
                || $segmento['fecha_inicio'] >= $resultadoFinal['watermark_fecha']) {
                $resultadoFinal['watermark_fecha'] = $segmento['fecha_inicio'];
                $resultadoFinal['watermark_uniqueid'] = $segmento['cdr_uniqueid'];
            }
        }

        foreach (array_keys($grupos) as $grupo) {
            $segmentos = centralTelefonicaSyncSegmentosGrupo($mysqli, $grupo);
            $consolidado = centralTelefonicaConstruirConsolidado($segmentos, $config);
            if ($consolidado) {
                centralTelefonicaSyncGuardarConsolidado($mysqli, $consolidado);
                $resultadoFinal['grupos']++;
            }
        }
        $mysqli->commit();
        $resultadoFinal['duracion_ms'] = intval(round((microtime(true) - $inicioMicro) * 1000));
        centralTelefonicaSyncCerrarRegistro($mysqli, $idRegistro, array(
            'estado' => 'exitosa',
            'consultados' => $resultadoFinal['consultados'],
            'nuevos' => $resultadoFinal['nuevos'],
            'actualizados' => $resultadoFinal['actualizados'],
            'duracion_ms' => $resultadoFinal['duracion_ms'],
            'watermark_fecha' => $resultadoFinal['watermark_fecha'],
            'watermark_uniqueid' => $resultadoFinal['watermark_uniqueid'],
            'codigo_error' => ''
        ));
        $conexionIssabel->close();
        return $resultadoFinal;
    } catch (CentralTelefonicaSyncExcepcion $e) {
        if ($conexionIssabel instanceof mysqli) {
            $conexionIssabel->close();
        }
        if (!$soloLectura && $idRegistro > 0) {
            @$mysqli->rollback();
            centralTelefonicaSyncCerrarRegistro($mysqli, $idRegistro, array(
                'estado' => 'fallida',
                'consultados' => $resultadoFinal['consultados'],
                'nuevos' => 0,
                'actualizados' => 0,
                'duracion_ms' => intval(round((microtime(true) - $inicioMicro) * 1000)),
                'watermark_fecha' => '',
                'watermark_uniqueid' => '',
                'codigo_error' => $e->codigoOperacion
            ));
        }
        throw $e;
    } catch (Exception $e) {
        if ($conexionIssabel instanceof mysqli) {
            $conexionIssabel->close();
        }
        if (!$soloLectura && $idRegistro > 0) {
            @$mysqli->rollback();
            centralTelefonicaSyncCerrarRegistro($mysqli, $idRegistro, array(
                'estado' => 'fallida',
                'consultados' => $resultadoFinal['consultados'],
                'nuevos' => 0,
                'actualizados' => 0,
                'duracion_ms' => intval(round((microtime(true) - $inicioMicro) * 1000)),
                'watermark_fecha' => '',
                'watermark_uniqueid' => '',
                'codigo_error' => 'error_sincronizacion'
            ));
        }
        throw $e;
    }
}

?>

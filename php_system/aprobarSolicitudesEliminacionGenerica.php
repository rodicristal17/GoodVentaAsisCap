<?php
$ejecutarSaneamientoDirecto = isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__);

if ($ejecutarSaneamientoDirecto && session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("solicitud_eliminado_helper.php");

date_default_timezone_set('America/Asuncion');

function saneamientoHtml($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function saneamientoIdentificadorValido($valor) {
    return preg_match('/^[A-Za-z0-9_]+$/', (string)$valor) === 1;
}

function saneamientoNormalizarArchivo($archivo) {
    $archivo = strtolower(trim(str_replace('\\', '/', (string)$archivo)));
    $archivo = basename($archivo);
    if ($archivo === '') {
        return '';
    }
    return 'php_system/'.$archivo;
}

function saneamientoExtraerDatoResumen($resumen, $campo) {
    $resumen = (string)$resumen;
    $campo = preg_quote((string)$campo, '/');
    if (preg_match('/(?:^|\|)\s*'.$campo.'\s*:\s*([^|]+)/i', $resumen, $m)) {
        return trim($m[1]);
    }
    return '';
}

function saneamientoDecodificarJson($texto) {
    $datos = json_decode((string)$texto, true);
    return is_array($datos) ? $datos : array();
}

function saneamientoMapaSensible() {
    return array(
        'php_system/abmgasto.php::verificaroperaciongasto' => true,
        'php_system/abmcompra.php::verificar' => true,
        'php_system/abmcompra.php::eliminarcompra' => true,
        'php_system/abmdetallecompra.php::verificar' => true,
        'php_system/abmcaja.php::verificar' => true,
        'php_system/abmaperturacierrecaja.php::verificar' => true,
        'php_system/abmmigrarcaja.php::verificar' => true,
        'php_system/abmventa.php::verificar' => true,
        'php_system/abmventa.php::eliminarventa' => true,
        'php_system/abmventa.php::abmcancelarventa' => true,
        'php_system/abmdetalleventa.php::verificar' => true,
        'php_system/abmdetalleventa.php::quitarproducto' => true,
        'php_system/abmdetalleventa.php::quitardegarantia' => true,
        'php_system/abmdetalleventa.php::quitardevolucion' => true,
        'php_system/abmcreditos.php::verificar' => true,
        'php_system/abmcreditos.php::eliminarcreditorefin' => true,
        'php_system/abmpagos.php::verificaroperacionpagos' => true,
        'php_system/abmpagos.php::quitarpago' => true,
        'php_system/abmpagos.php::quitarhistorialpago' => true
    );
}

function saneamientoOrigenSolicitud($solicitud) {
    $resumen = isset($solicitud['registro_resumen']) ? $solicitud['registro_resumen'] : '';
    $archivo = saneamientoNormalizarArchivo(saneamientoExtraerDatoResumen($resumen, 'archivo'));
    $funcion = strtolower(trim(saneamientoExtraerDatoResumen($resumen, 'funcion')));
    if ($archivo !== '' && $funcion !== '') {
        return $archivo.'::'.$funcion;
    }
    return '';
}

function saneamientoSolicitudEsGastoCuotaAsociada($solicitud) {
    $tabla = strtolower(trim(isset($solicitud['tabla_nombre']) ? $solicitud['tabla_nombre'] : ''));
    $pk = strtolower(trim(isset($solicitud['registro_pk_columna']) ? $solicitud['registro_pk_columna'] : ''));
    $motivo = strtolower(trim(isset($solicitud['motivo']) ? (string)$solicitud['motivo'] : ''));

    return $tabla === 'gastos'
        && $pk === 'idgastos'
        && strpos($motivo, 'cuota asociada de gasto.') !== false;
}

function saneamientoSolicitudEsCierreOEdicionCaja($solicitud) {
    $motivo = strtolower(trim(isset($solicitud['motivo']) ? (string)$solicitud['motivo'] : ''));
    $motivo = rtrim($motivo, " \t\n\r\0\x0B.");
    return $motivo === 'solicitud automatica por cierre o edicion de caja';
}

function saneamientoSolicitudEsEdicionGasto($solicitud) {
    $motivo = strtolower(trim(isset($solicitud['motivo']) ? (string)$solicitud['motivo'] : ''));
    $motivo = rtrim($motivo, " \t\n\r\0\x0B.");
    return $motivo === 'solicitud automatica por edicion de gasto';
}

function saneamientoSolicitudEsSensible($solicitud) {
    if (saneamientoSolicitudEsGastoCuotaAsociada($solicitud)) {
        return array(false, '');
    }

    $origen = saneamientoOrigenSolicitud($solicitud);
    $mapa = saneamientoMapaSensible();
    if ($origen !== '' && isset($mapa[$origen])) {
        return array(true, $origen);
    }

    $tabla = strtolower(trim(isset($solicitud['tabla_nombre']) ? $solicitud['tabla_nombre'] : ''));
    $pk = strtolower(trim(isset($solicitud['registro_pk_columna']) ? $solicitud['registro_pk_columna'] : ''));
    $estadoColumna = trim(isset($solicitud['estado_columna']) ? $solicitud['estado_columna'] : '');
    $datos = saneamientoDecodificarJson(isset($solicitud['registro_resumen']) ? $solicitud['registro_resumen'] : '');

    if ($tabla === 'venta' && $pk === 'cod_venta' && $estadoColumna === '' && isset($datos['tipo']) && $datos['tipo'] === 'cancelacion_venta') {
        return array(true, 'php_system/abmventa.php::abmcancelarventa');
    }

    if ($tabla === 'pago' && $pk === 'cod_creditofk' && $estadoColumna === '' && isset($datos['motivo'], $datos['monto'], $datos['cuota'])) {
        return array(true, 'php_system/abmpagos.php::quitarpago');
    }

    if ($origen === '') {
        $patronesEspeciales = array(
            'gastos::idgastos' => 'php_system/abmgasto.php::verificarOperacionGasto',
            'compra::cod_compra' => 'php_system/abmcompra.php::verificar/eliminarcompra',
            'pagosdecompra::codpago' => 'php_system/abmcompra.php::verificar',
            'caja::idcaja' => 'php_system/abmCaja.php::verificar',
            'arqueocaja::idarqueocaja' => 'php_system/abmaperturacierrecaja.php::verificar',
            'migrar_caja::idmigrar_caja' => 'php_system/abmMigrarCaja.php::verificar',
            'venta::cod_venta' => 'php_system/abmventa.php::abmcancelarventa',
            'credito::idcredito' => 'php_system/abmcreditos.php::verificar/eliminarcreditorefin',
            'pago::cod_creditofk' => 'php_system/abmpagos.php::quitarpago',
            'pago::idpago' => 'php_system/abmpagos.php::verificarOperacionPagos/quitarhistorialpago',
            'detalle_compra::cod_detalle_compra' => 'php_system/abmdetallecompra.php::verificar',
            'detalle_venta::cod_detalle' => 'php_system/abmdetalleventa.php::verificar/quitarproducto/quitardegarantia/quitarDevolucion',
            'garantias::idgarantia' => 'php_system/abmdetalleventa.php::verificar'
        );
        $clave = $tabla.'::'.$pk;
        if (isset($patronesEspeciales[$clave])) {
            return array(true, $patronesEspeciales[$clave].' (inferido por tabla/pk sin origen)');
        }
    }

    return array(false, '');
}

function saneamientoSolicitudPareceGenerica($solicitud) {
    $resumen = isset($solicitud['registro_resumen']) ? (string)$solicitud['registro_resumen'] : '';
    $motivo = isset($solicitud['motivo']) ? strtolower(trim((string)$solicitud['motivo'])) : '';
    if (saneamientoOrigenSolicitud($solicitud) !== '') {
        return true;
    }
    if (strpos($motivo, 'solicitud automatica') === 0) {
        return true;
    }
    $datos = saneamientoDecodificarJson($resumen);
    return count($datos) > 0;
}

function saneamientoObtenerIdsPendientes($mysqli) {
    $ids = array();
    $sql = "SELECT id_solicitud_eliminado FROM solicitud_eliminado WHERE estado = 'pendiente' ORDER BY id_solicitud_eliminado ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return $ids;
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ids[] = intval($row['id_solicitud_eliminado']);
        }
    }
    $stmt->close();
    return $ids;
}

function saneamientoObtenerSolicitudBloqueada($mysqli, $idSolicitud) {
    $sql = "SELECT * FROM solicitud_eliminado WHERE id_solicitud_eliminado = ? AND estado = 'pendiente' LIMIT 1 FOR UPDATE";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array(null, 'No se pudo preparar la lectura de solicitud: '.$mysqli->error);
    }
    $stmt->bind_param('i', $idSolicitud);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return array(null, 'No se pudo leer la solicitud: '.$error);
    }
    $result = $stmt->get_result();
    $solicitud = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return array($solicitud, '');
}

function saneamientoTablaExiste($mysqli, $tabla) {
    if (!saneamientoIdentificadorValido($tabla)) {
        return false;
    }
    $sql = "SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $tabla);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return isset($row['total']) && intval($row['total']) > 0;
}

function saneamientoObtenerColumna($mysqli, $tabla, $columna) {
    if (!saneamientoIdentificadorValido($tabla) || !saneamientoIdentificadorValido($columna)) {
        return null;
    }
    $sql = "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function saneamientoValorInactivoParaColumna($columnaEstado) {
    $tipo = strtolower((string)$columnaEstado['DATA_TYPE']);
    $columnType = (string)$columnaEstado['COLUMN_TYPE'];

    if (in_array($tipo, array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'bit'))) {
        return '0';
    }

    if ($tipo === 'enum') {
        $preferidos = array("'Inactivo'", "'inactivo'", "'INACTIVO'", "'Eliminado'", "'eliminado'", "'ELIMINADO'");
        foreach ($preferidos as $valor) {
            if (strpos($columnType, $valor) !== false) {
                return trim($valor, "'");
            }
        }
        return null;
    }

    return 'Inactivo';
}

function saneamientoEsValorInactivo($valor) {
    $valor = strtolower(trim((string)$valor));
    return $valor === 'inactivo' || $valor === 'eliminado' || $valor === '0';
}

function saneamientoObtenerRegistroDestino($mysqli, $tabla, $pkColumna, $pkValor) {
    $sql = "SELECT * FROM `".$tabla."` WHERE `".$pkColumna."` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array(null, 'No se pudo preparar la consulta del destino: '.$mysqli->error);
    }
    $stmt->bind_param('s', $pkValor);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return array(null, 'No se pudo consultar el destino: '.$error);
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return array($row, '');
}

function saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $observacion) {
    $sql = "UPDATE solicitud_eliminado
            SET observacion_aprobacion = ?
            WHERE id_solicitud_eliminado = ?
              AND estado = 'pendiente'";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 'No se pudo preparar la observacion: '.$mysqli->error;
    }
    $stmt->bind_param('si', $observacion, $idSolicitud);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return 'No se pudo actualizar la observacion: '.$error;
    }
    $stmt->close();
    return '';
}

function saneamientoResolverSolicitud($mysqli, $idSolicitud, $estado, $codUsuario, $observacion) {
    $fecha = date('Y-m-d H:i:s');
    $sql = "UPDATE solicitud_eliminado
            SET estado = ?, fecha_aprobacion = ?, id_usuario_aprobacion = ?, observacion_aprobacion = ?
            WHERE id_solicitud_eliminado = ?
              AND estado = 'pendiente'";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 'No se pudo preparar la resolucion: '.$mysqli->error;
    }
    $stmt->bind_param('ssisi', $estado, $fecha, $codUsuario, $observacion, $idSolicitud);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return 'No se pudo resolver la solicitud: '.$error;
    }
    $afectadas = $stmt->affected_rows;
    $stmt->close();
    if ($afectadas < 1) {
        return 'La solicitud ya no estaba pendiente al resolver.';
    }
    return '';
}

function saneamientoActualizarDetalle($mysqli, $idDetalle, $estadoProceso) {
    $sql = "UPDATE solicitud_eliminado_detalle
            SET estado_proceso = ?, fecha_proceso = NOW()
            WHERE id_solicitud_eliminado_detalle = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return 'No se pudo preparar el marcado del detalle: '.$mysqli->error;
    }
    $stmt->bind_param('si', $estadoProceso, $idDetalle);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return 'No se pudo marcar el detalle: '.$error;
    }
    $stmt->close();
    return '';
}

function saneamientoInactivarDestino($mysqli, $tabla, $pkColumna, $pkValor, $estadoColumna) {
    if (!saneamientoIdentificadorValido($tabla) || !saneamientoIdentificadorValido($pkColumna) || !saneamientoIdentificadorValido($estadoColumna)) {
        return array(false, 'La tabla o columnas del destino no son validas.');
    }

    $columnaPk = saneamientoObtenerColumna($mysqli, $tabla, $pkColumna);
    if (!$columnaPk) {
        return array(false, 'No se encontro la tabla o la columna principal del destino.');
    }

    $columnaEstado = saneamientoObtenerColumna($mysqli, $tabla, $estadoColumna);
    if (!$columnaEstado) {
        return array(false, 'No se encontro la columna de estado '.$estadoColumna.' en '.$tabla.'.');
    }

    list($registro, $errorRegistro) = saneamientoObtenerRegistroDestino($mysqli, $tabla, $pkColumna, $pkValor);
    if ($errorRegistro !== '') {
        return array(false, $errorRegistro);
    }
    if (!$registro) {
        return array(true, 'no_aplica');
    }
    if (array_key_exists($estadoColumna, $registro) && saneamientoEsValorInactivo($registro[$estadoColumna])) {
        return array(true, 'no_aplica');
    }

    $valorInactivo = saneamientoValorInactivoParaColumna($columnaEstado);
    if ($valorInactivo === null) {
        return array(false, 'La columna '.$estadoColumna.' de '.$tabla.' no admite un valor inactivo conocido.');
    }

    $sql = "UPDATE `".$tabla."` SET `".$estadoColumna."` = ? WHERE `".$pkColumna."` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array(false, 'No se pudo preparar la inactivacion: '.$mysqli->error);
    }
    $stmt->bind_param('ss', $valorInactivo, $pkValor);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return array(false, 'No se pudo inactivar el destino: '.$error);
    }
    $afectadas = $stmt->affected_rows;
    $stmt->close();
    if ($afectadas < 1) {
        return array(false, 'No se modifico el destino; se evita aprobar para no duplicar acciones.');
    }
    return array(true, 'aplicado');
}

function saneamientoProcesarDetalles($mysqli, $idSolicitud) {
    if (!saneamientoTablaExiste($mysqli, 'solicitud_eliminado_detalle')) {
        return array(true, '');
    }

    $sql = "SELECT * FROM solicitud_eliminado_detalle WHERE id_solicitud_eliminado = ? ORDER BY id_solicitud_eliminado_detalle ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return array(false, 'No se pudo preparar la lectura de detalles: '.$mysqli->error);
    }
    $stmt->bind_param('i', $idSolicitud);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return array(false, 'No se pudo leer los detalles: '.$error);
    }
    $result = $stmt->get_result();
    $detalles = array();
    while ($row = $result->fetch_assoc()) {
        $detalles[] = $row;
    }
    $stmt->close();

    foreach ($detalles as $detalle) {
        $idDetalle = intval($detalle['id_solicitud_eliminado_detalle']);
        $requiereInactivacion = intval($detalle['requiere_inactivacion']);
        if ($requiereInactivacion !== 1) {
            $errorDetalle = saneamientoActualizarDetalle($mysqli, $idDetalle, 'omitido');
            if ($errorDetalle !== '') {
                return array(false, $errorDetalle);
            }
            continue;
        }

        $tabla = trim((string)$detalle['tabla_nombre']);
        $pkColumna = trim((string)$detalle['registro_pk_columna']);
        $pkValor = trim((string)$detalle['registro_pk_valor']);
        $estadoColumna = isset($detalle['estado_columna']) && trim((string)$detalle['estado_columna']) !== '' ? trim((string)$detalle['estado_columna']) : 'estado';
        list($ok, $estado) = saneamientoInactivarDestino($mysqli, $tabla, $pkColumna, $pkValor, $estadoColumna);
        if (!$ok) {
            return array(false, 'Detalle #'.$idDetalle.': '.$estado);
        }

        $estadoProceso = $estado === 'aplicado' ? 'aplicado' : 'omitido';
        $errorDetalle = saneamientoActualizarDetalle($mysqli, $idDetalle, $estadoProceso);
        if ($errorDetalle !== '') {
            return array(false, $errorDetalle);
        }
    }

    return array(true, '');
}

function saneamientoProcesarSolicitud($mysqli, $idSolicitud, $codUsuario, &$resumen, &$items) {
    $mysqli->begin_transaction();

    list($solicitud, $errorLectura) = saneamientoObtenerSolicitudBloqueada($mysqli, $idSolicitud);
    if ($errorLectura !== '') {
        $mysqli->rollback();
        $resumen['errores']++;
        $items[] = array($idSolicitud, 'error', $errorLectura);
        return;
    }
    if (!$solicitud) {
        $mysqli->rollback();
        return;
    }

    if (saneamientoSolicitudEsCierreOEdicionCaja($solicitud)) {
        $obs = 'Saneamiento automatico: aprobada por motivo de cierre o edicion de caja; no se ejecuto inactivacion.';
        $errorResolver = saneamientoResolverSolicitud($mysqli, $idSolicitud, 'aprobada', $codUsuario, $obs);
        if ($errorResolver !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorResolver);
            return;
        }
        $mysqli->commit();
        $resumen['procesadas_automaticamente']++;
        $items[] = array($idSolicitud, 'procesada automaticamente', $obs);
        return;
    }

    if (saneamientoSolicitudEsEdicionGasto($solicitud)) {
        $obs = 'Saneamiento automatico: aprobada por motivo de edicion de gasto; no se ejecuto inactivacion.';
        $errorResolver = saneamientoResolverSolicitud($mysqli, $idSolicitud, 'aprobada', $codUsuario, $obs);
        if ($errorResolver !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorResolver);
            return;
        }
        $mysqli->commit();
        $resumen['procesadas_automaticamente']++;
        $items[] = array($idSolicitud, 'procesada automaticamente', $obs);
        return;
    }

    $esGastoCuotaAsociada = saneamientoSolicitudEsGastoCuotaAsociada($solicitud);
    list($esSensible, $motivoSensible) = saneamientoSolicitudEsSensible($solicitud);
    if ($esSensible) {
        $mysqli->commit();
        $resumen['excluidas_sensibles']++;
        $items[] = array($idSolicitud, 'requiere revision manual', 'Excluida por lista sensible: '.$motivoSensible);
        return;
    }

    $tabla = trim((string)$solicitud['tabla_nombre']);
    $pkColumna = trim((string)$solicitud['registro_pk_columna']);
    $pkValor = trim((string)$solicitud['registro_pk_valor']);
    $estadoColumna = isset($solicitud['estado_columna']) ? trim((string)$solicitud['estado_columna']) : 'estado';

    if ($tabla === '' || $pkColumna === '' || $pkValor === '') {
        $obs = 'Saneamiento automatico: requiere revision. La solicitud no tiene tabla, columna o codigo de registro.';
        $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
        if ($errorObs !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorObs);
            return;
        }
        $mysqli->commit();
        $resumen['requieren_revision']++;
        $items[] = array($idSolicitud, 'requiere revision', $obs);
        return;
    }

    if (!saneamientoIdentificadorValido($tabla) || !saneamientoIdentificadorValido($pkColumna)) {
        $obs = 'Saneamiento automatico: requiere revision. La tabla o columna principal no es valida.';
        $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
        if ($errorObs !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorObs);
            return;
        }
        $mysqli->commit();
        $resumen['requieren_revision']++;
        $items[] = array($idSolicitud, 'requiere revision', $obs);
        return;
    }

    if (!saneamientoTablaExiste($mysqli, $tabla) || !saneamientoObtenerColumna($mysqli, $tabla, $pkColumna)) {
        $obs = 'Saneamiento automatico: requiere revision. No se pudo validar tabla o columna principal del destino.';
        $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
        if ($errorObs !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorObs);
            return;
        }
        $mysqli->commit();
        $resumen['requieren_revision']++;
        $items[] = array($idSolicitud, 'requiere revision', $obs);
        return;
    }

    list($registro, $errorRegistro) = saneamientoObtenerRegistroDestino($mysqli, $tabla, $pkColumna, $pkValor);
    if ($errorRegistro !== '') {
        $mysqli->rollback();
        $resumen['errores']++;
        $items[] = array($idSolicitud, 'error', $errorRegistro);
        return;
    }

    if (!$registro) {
        $obs = 'Saneamiento automatico: saneada/no aplica. El registro destino ya no existe; no se ejecuto accion.';
        $errorResolver = saneamientoResolverSolicitud($mysqli, $idSolicitud, 'rechazada', $codUsuario, $obs);
        if ($errorResolver !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorResolver);
            return;
        }
        $mysqli->commit();
        $resumen['saneadas_no_aplica']++;
        $items[] = array($idSolicitud, 'saneada/no aplica', $obs);
        return;
    }

    if ($estadoColumna === '') {
        $obs = 'Saneamiento automatico: requiere revision. La solicitud no tiene estado_columna; se evita ejecutar acciones fisicas o especiales.';
        $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
        if ($errorObs !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorObs);
            return;
        }
        $mysqli->commit();
        $resumen['requieren_revision']++;
        $items[] = array($idSolicitud, 'requiere revision', $obs);
        return;
    }

    if (!saneamientoIdentificadorValido($estadoColumna) || !saneamientoObtenerColumna($mysqli, $tabla, $estadoColumna)) {
        $obs = 'Saneamiento automatico: requiere revision. No se pudo validar la columna de estado '.$estadoColumna.'.';
        $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
        if ($errorObs !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorObs);
            return;
        }
        $mysqli->commit();
        $resumen['requieren_revision']++;
        $items[] = array($idSolicitud, 'requiere revision', $obs);
        return;
    }

    if (array_key_exists($estadoColumna, $registro) && saneamientoEsValorInactivo($registro[$estadoColumna])) {
        $obs = 'Saneamiento automatico: saneada/no aplica. El registro destino ya estaba inactivo; no se ejecuto accion.';
        $errorResolver = saneamientoResolverSolicitud($mysqli, $idSolicitud, 'rechazada', $codUsuario, $obs);
        if ($errorResolver !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorResolver);
            return;
        }
        $mysqli->commit();
        $resumen['saneadas_no_aplica']++;
        $items[] = array($idSolicitud, 'saneada/no aplica', $obs);
        return;
    }

    list($okDetalles, $errorDetalles) = saneamientoProcesarDetalles($mysqli, $idSolicitud);
    if (!$okDetalles) {
        $obs = 'Saneamiento automatico: requiere revision. '.$errorDetalles;
        $mysqli->rollback();
        $mysqli->begin_transaction();
        list($solicitudRevision) = saneamientoObtenerSolicitudBloqueada($mysqli, $idSolicitud);
        if ($solicitudRevision) {
            $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
            if ($errorObs === '') {
                $mysqli->commit();
                $resumen['requieren_revision']++;
                $items[] = array($idSolicitud, 'requiere revision', $obs);
                return;
            }
        }
        $mysqli->rollback();
        $resumen['errores']++;
        $items[] = array($idSolicitud, 'error', $obs);
        return;
    }

    list($okInactivar, $estadoInactivar) = saneamientoInactivarDestino($mysqli, $tabla, $pkColumna, $pkValor, $estadoColumna);
    if (!$okInactivar) {
        $obs = 'Saneamiento automatico: requiere revision. '.$estadoInactivar;
        $mysqli->rollback();
        $mysqli->begin_transaction();
        list($solicitudRevision) = saneamientoObtenerSolicitudBloqueada($mysqli, $idSolicitud);
        if ($solicitudRevision) {
            $errorObs = saneamientoActualizarObservacionPendiente($mysqli, $idSolicitud, $obs);
            if ($errorObs === '') {
                $mysqli->commit();
                $resumen['requieren_revision']++;
                $items[] = array($idSolicitud, 'requiere revision', $obs);
                return;
            }
        }
        $mysqli->rollback();
        $resumen['errores']++;
        $items[] = array($idSolicitud, 'error', $obs);
        return;
    }

    if ($estadoInactivar === 'no_aplica') {
        $obs = 'Saneamiento automatico: saneada/no aplica. El destino ya no requeria inactivacion.';
        $errorResolver = saneamientoResolverSolicitud($mysqli, $idSolicitud, 'rechazada', $codUsuario, $obs);
        if ($errorResolver !== '') {
            $mysqli->rollback();
            $resumen['errores']++;
            $items[] = array($idSolicitud, 'error', $errorResolver);
            return;
        }
        $mysqli->commit();
        $resumen['saneadas_no_aplica']++;
        $items[] = array($idSolicitud, 'saneada/no aplica', $obs);
        return;
    }

    $obs = $esGastoCuotaAsociada
        ? 'Saneamiento automatico: aprobada y procesada como cuota asociada de gasto.'
        : 'Saneamiento automatico: aprobada y procesada con inactivacion generica validada.';
    $errorResolver = saneamientoResolverSolicitud($mysqli, $idSolicitud, 'aprobada', $codUsuario, $obs);
    if ($errorResolver !== '') {
        $mysqli->rollback();
        $resumen['errores']++;
        $items[] = array($idSolicitud, 'error', $errorResolver);
        return;
    }

    $mysqli->commit();
    $resumen['procesadas_automaticamente']++;
    $items[] = array($idSolicitud, 'procesada automaticamente', $obs);
}

function saneamientoMostrarResumen($resumen, $items) {
    header('Content-Type: text/html; charset=UTF-8');
    echo "<!doctype html><html><head><meta charset='UTF-8'><title>Saneamiento solicitudes eliminacion</title></head><body>";
    echo "<h1>Saneamiento de solicitudes de eliminacion</h1>";
    echo "<ul>";
    echo "<li>Total pendientes encontradas: ".intval($resumen['total_pendientes'])."</li>";
    echo "<li>Procesadas automaticamente: ".intval($resumen['procesadas_automaticamente'])."</li>";
    echo "<li>Saneadas/no aplica: ".intval($resumen['saneadas_no_aplica'])."</li>";
    echo "<li>Excluidas por lista sensible: ".intval($resumen['excluidas_sensibles'])."</li>";
    echo "<li>Requieren revision: ".intval($resumen['requieren_revision'])."</li>";
    echo "<li>Errores: ".intval($resumen['errores'])."</li>";
    echo "</ul>";

    if (count($items) > 0) {
        echo "<h2>Detalle</h2>";
        echo "<table border='1' cellpadding='6' cellspacing='0'>";
        echo "<thead><tr><th>Solicitud</th><th>Resultado</th><th>Observacion</th></tr></thead><tbody>";
        foreach ($items as $item) {
            echo "<tr>";
            echo "<td>".intval($item[0])."</td>";
            echo "<td>".saneamientoHtml($item[1])."</td>";
            echo "<td>".saneamientoHtml($item[2])."</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    echo "</body></html>";
}

function ejecutarSaneamientoSolicitudesEliminacionGenerica() {
    $resumen = array(
        'total_pendientes' => 0,
        'procesadas_automaticamente' => 0,
        'saneadas_no_aplica' => 0,
        'excluidas_sensibles' => 0,
        'requieren_revision' => 0,
        'errores' => 0
    );
    $items = array();

    $_SESSION['saneamiento_solicitud_eliminado_generica'] = 1;
    $mysqli = conectar_al_servidor();

    if (!$mysqli || $mysqli->connect_errno) {
        unset($_SESSION['saneamiento_solicitud_eliminado_generica']);
        $resumen['errores']++;
        $items[] = array(0, 'error', 'No se pudo conectar a la base de datos.');
        saneamientoMostrarResumen($resumen, $items);
        exit;
    }

    $codUsuario = isset($_GET['useru']) ? intval($_GET['useru']) : 2;
    if ($codUsuario <= 0) {
        $codUsuario = 2;
    }

    $ids = saneamientoObtenerIdsPendientes($mysqli);
    $resumen['total_pendientes'] = count($ids);

    foreach ($ids as $idSolicitud) {
        saneamientoProcesarSolicitud($mysqli, $idSolicitud, $codUsuario, $resumen, $items);
    }

    mysqli_close($mysqli);
    unset($_SESSION['saneamiento_solicitud_eliminado_generica']);

    saneamientoMostrarResumen($resumen, $items);
}

if ($ejecutarSaneamientoDirecto) {
    ejecutarSaneamientoSolicitudesEliminacionGenerica();
}
?>

<?php

/**
 * Flujo acotado para la responsable oficial de Tesoreria.
 * No reemplaza el ABM general: limita sus escrituras a monto, fecha y
 * distribucion de egresos, y conserva los movimientos pagados como historicos.
 */

function gastoTesoreriaTablaExiste($mysqli, $tabla)
{
    $tabla = $mysqli->real_escape_string((string)$tabla);
    $resultado = $mysqli->query("SHOW TABLES LIKE '$tabla'");
    return $resultado && $resultado->num_rows > 0;
}

function gastoTesoreriaEstructuraDisponible($mysqli)
{
    return gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_configuracion')
        && gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_responsable_evento')
        && gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_modificacion')
        && gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_impacto');
}

function gastoTesoreriaTextoUtf8($valor)
{
    $texto = (string)$valor;
    if ($texto !== '' && !mb_check_encoding($texto, 'UTF-8')) {
        $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
    }
    return $texto;
}

function gastoTesoreriaFilaUtf8($fila)
{
    foreach ((array)$fila as $clave => $valor) {
        if (is_string($valor)) {
            $fila[$clave] = gastoTesoreriaTextoUtf8($valor);
        }
    }
    return $fila;
}

function gastoTesoreriaNombreNormalizado($nombre)
{
    $nombre = mb_strtoupper(gastoTesoreriaTextoUtf8($nombre), 'UTF-8');
    $nombre = strtr($nombre, array(
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N'
    ));
    return preg_replace('/\s+/u', ' ', trim($nombre));
}

function gastoTesoreriaPuedeConfigurar($mysqli, $codUsuario)
{
    $codUsuario = (int)$codUsuario;
    if ($codUsuario !== 5994
        || controldeaccesoacasas($codUsuario, 'CONFIGURARRESPONSABLETESORERIA', " u.accion='SI' ") != 1) {
        return false;
    }
    $stmt = $mysqli->prepare("SELECT p.nombre_persona FROM usuario u
        INNER JOIN persona p ON p.cod_persona=u.cod_usuario
        WHERE u.cod_usuario=? AND u.estado='Activo' LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $codUsuario);
    $fila = null;
    if ($stmt->execute()) {
        $fila = $stmt->get_result()->fetch_assoc();
    }
    $stmt->close();
    return $fila && strpos(gastoTesoreriaNombreNormalizado($fila['nombre_persona']), 'CARLOS FARAONE') === 0;
}

function gastoTesoreriaResponsableActual($mysqli, $bloquear = false)
{
    if (!gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_configuracion')) {
        return array();
    }
    $sql = "SELECT c.cod_usuario_responsableFK AS cod_usuario,p.nombre_persona AS nombre,
        IFNULL(u.url,'') AS avatar,IFNULL(u.tipo,'') AS rol,IFNULL(l.Nombre,'') AS nombre_local,
        c.fecha_actualizacion
        FROM gasto_tesoreria_configuracion c
        LEFT JOIN usuario u ON u.cod_usuario=c.cod_usuario_responsableFK AND u.estado='Activo'
        LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN local l ON l.cod_local=u.cod_localFK
        WHERE c.id_configuracion=1 LIMIT 1".($bloquear ? ' FOR UPDATE' : '');
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        throw new Exception('No se pudo consultar la responsable oficial de Tesoreria.');
    }
    $fila = $resultado->fetch_assoc();
    if (!$fila || empty($fila['cod_usuario']) || trim((string)$fila['nombre']) === '') {
        return array();
    }
    return gastoTesoreriaFilaUtf8($fila);
}

function gastoTesoreriaEsResponsable($mysqli, $codUsuario)
{
    $responsable = gastoTesoreriaResponsableActual($mysqli, false);
    return !empty($responsable) && (int)$responsable['cod_usuario'] === (int)$codUsuario;
}

function gastoTesoreriaUsuariosActivos($mysqli)
{
    $usuarios = array();
    $resultado = $mysqli->query("SELECT u.cod_usuario,p.nombre_persona AS nombre,
        IFNULL(u.url,'') AS avatar,IFNULL(u.tipo,'') AS rol,IFNULL(l.Nombre,'') AS nombre_local
        FROM usuario u
        INNER JOIN persona p ON p.cod_persona=u.cod_usuario
        LEFT JOIN local l ON l.cod_local=u.cod_localFK
        WHERE u.estado='Activo'
        ORDER BY p.nombre_persona,u.cod_usuario");
    if (!$resultado) {
        throw new Exception('No se pudo consultar la lista de usuarios activos de Telar.');
    }
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = gastoTesoreriaFilaUtf8($fila);
    }
    return $usuarios;
}

function gastoTesoreriaCajaActivaUsuarioLocal($mysqli, $codUsuario, $codLocal, $bloquear = false)
{
    $codUsuario = (int)$codUsuario;
    $codLocal = (int)$codLocal;
    if ($codUsuario <= 0 || $codLocal <= 0) {
        return null;
    }
    $stmt = $mysqli->prepare("SELECT idarqueocaja,caja_idcaja,cod_local,codusuarioap
        FROM arqueocaja
        WHERE codusuarioap=? AND cod_local=? AND LOWER(TRIM(IFNULL(estado,'')))='activo'
        ORDER BY idarqueocaja DESC LIMIT 1".($bloquear ? ' FOR UPDATE' : ''));
    if (!$stmt) {
        throw new Exception('No se pudo consultar la caja activa de Tesoreria.');
    }
    $stmt->bind_param('ii', $codUsuario, $codLocal);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ?: null;
}

function gastoTesoreriaActorUltimaEdicionPendiente($mysqli, $idgastos)
{
    if (!gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_modificacion')) {
        return 0;
    }
    $idgastos = (int)$idgastos;
    $solo = '['.$idgastos.']';
    $inicio = '['.$idgastos.',%';
    $medio = '%,'.$idgastos.',%';
    $final = '%,'.$idgastos.']';
    $stmt = $mysqli->prepare("SELECT cod_usuario_actorFK FROM gasto_tesoreria_modificacion
        WHERE tipo_modificacion='edicion_pendiente'
          AND (idgastosFK=? OR ids_afectados_json=? OR ids_afectados_json LIKE ?
            OR ids_afectados_json LIKE ? OR ids_afectados_json LIKE ?)
        ORDER BY id_modificacion DESC LIMIT 1");
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('issss', $idgastos, $solo, $inicio, $medio, $final);
    $actor = 0;
    if ($stmt->execute()) {
        $fila = $stmt->get_result()->fetch_assoc();
        $actor = $fila ? (int)$fila['cod_usuario_actorFK'] : 0;
    }
    $stmt->close();
    return $actor;
}

function gastoTesoreriaObtenerContexto($codUsuario)
{
    $mysqli = conectar_al_servidor();
    try {
        $estructura = gastoTesoreriaEstructuraDisponible($mysqli);
        $puedeConfigurar = gastoTesoreriaPuedeConfigurar($mysqli, $codUsuario);
        $responsable = $estructura ? gastoTesoreriaResponsableActual($mysqli) : array();
        $esResponsable = !empty($responsable) && (int)$responsable['cod_usuario'] === (int)$codUsuario;
        $usuarios = $puedeConfigurar ? gastoTesoreriaUsuariosActivos($mysqli) : array();
        $mysqli->close();
        return array(
            '1' => 'exito',
            'estructura_disponible' => $estructura ? 1 : 0,
            'puede_configurar' => $puedeConfigurar ? 1 : 0,
            'es_responsable' => $esResponsable ? 1 : 0,
            'responsable' => $responsable,
            'usuarios' => $usuarios
        );
    } catch (Exception $e) {
        $mysqli->close();
        return array('1' => 'error', '2' => $e->getMessage());
    }
}

function gastoTesoreriaGuardarResponsable($codUsuarioActor, $codUsuarioNuevo)
{
    $codUsuarioActor = (int)$codUsuarioActor;
    $codUsuarioNuevo = (int)$codUsuarioNuevo;
    $mysqli = conectar_al_servidor();
    if (!gastoTesoreriaEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('1' => 'error', '2' => 'Primero debe aplicarse la actualizacion de Tesoreria en la base de datos.');
    }
    if (!gastoTesoreriaPuedeConfigurar($mysqli, $codUsuarioActor)) {
        $mysqli->close();
        return array('1' => 'NI', '2' => 'Solo Carlos Faraone puede configurar la responsable oficial de Tesoreria.');
    }
    if ($codUsuarioNuevo <= 0) {
        $mysqli->close();
        return array('1' => 'error', '2' => 'Seleccione una usuaria activa de Telar.');
    }
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("SELECT u.cod_usuario FROM usuario u
            INNER JOIN persona p ON p.cod_persona=u.cod_usuario
            WHERE u.cod_usuario=? AND u.estado='Activo' LIMIT 1 FOR UPDATE");
        if (!$stmt) {
            throw new Exception('No se pudo validar la usuaria seleccionada.');
        }
        $stmt->bind_param('i', $codUsuarioNuevo);
        $stmt->execute();
        $valida = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$valida) {
            throw new Exception('La usuaria seleccionada ya no esta activa en Telar.');
        }
        $actual = gastoTesoreriaResponsableActual($mysqli, true);
        $codAnterior = !empty($actual) ? (int)$actual['cod_usuario'] : null;
        $stmt = $mysqli->prepare("INSERT INTO gasto_tesoreria_configuracion
            (id_configuracion,cod_usuario_responsableFK,cod_usuario_configuraFK,fecha_creacion,fecha_actualizacion)
            VALUES (1,?,?,NOW(),NOW()) ON DUPLICATE KEY UPDATE
            cod_usuario_responsableFK=VALUES(cod_usuario_responsableFK),
            cod_usuario_configuraFK=VALUES(cod_usuario_configuraFK),fecha_actualizacion=NOW()");
        if (!$stmt) {
            throw new Exception('No se pudo preparar la configuracion de Tesoreria.');
        }
        $stmt->bind_param('ii', $codUsuarioNuevo, $codUsuarioActor);
        if (!$stmt->execute()) {
            $mensaje = $stmt->error;
            $stmt->close();
            throw new Exception('No se pudo guardar la responsable de Tesoreria: '.$mensaje);
        }
        $stmt->close();
        if ($codAnterior !== $codUsuarioNuevo) {
            $stmt = $mysqli->prepare("INSERT INTO gasto_tesoreria_responsable_evento
                (cod_usuario_anteriorFK,cod_usuario_nuevoFK,cod_usuario_actorFK,fecha_hora)
                VALUES (?,?,?,NOW())");
            if (!$stmt) {
                throw new Exception('No se pudo preparar la auditoria de la configuracion.');
            }
            $stmt->bind_param('iii', $codAnterior, $codUsuarioNuevo, $codUsuarioActor);
            if (!$stmt->execute()) {
                $mensaje = $stmt->error;
                $stmt->close();
                throw new Exception('No se pudo auditar la configuracion de Tesoreria: '.$mensaje);
            }
            $stmt->close();
        }
        if (!$mysqli->commit()) {
            throw new Exception('No se pudo confirmar la configuracion de Tesoreria.');
        }
        $responsable = gastoTesoreriaResponsableActual($mysqli);
        $mysqli->close();
        return array('1' => 'exito', 'responsable' => $responsable);
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('1' => 'error', '2' => $e->getMessage());
    }
}

function gastoTesoreriaMonto($valor)
{
    if (function_exists('quitarseparadormiles')) {
        $valor = quitarseparadormiles($valor);
    }
    return (int)round((float)$valor);
}

function gastoTesoreriaFechaValida($valor)
{
    $valor = trim((string)$valor);
    $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
    return $fecha && $fecha->format('Y-m-d') === $valor;
}

function gastoTesoreriaLocalesAnaliticos()
{
    if (function_exists('gastoDistribucionLocalesGraficos')) {
        return gastoDistribucionLocalesGraficos();
    }
    return array(3, 5, 6, 7, 9);
}

function gastoTesoreriaNormalizarDistribucion($mysqli, $modo, $jsonAsignaciones, $monto, $codLocalPagoSolicitado)
{
    $modo = strtolower(trim((string)$modo));
    $monto = gastoTesoreriaMonto($monto);
    $codLocalPagoSolicitado = (int)$codLocalPagoSolicitado;
    if (!in_array($modo, array('local', 'compartido', 'personalizado'), true)) {
        throw new Exception('Seleccione una modalidad de distribucion valida.');
    }
    if ($monto <= 0) {
        throw new Exception('El monto corregido debe ser mayor a cero.');
    }
    $recibidas = json_decode((string)$jsonAsignaciones, true);
    if (!is_array($recibidas)) {
        throw new Exception('La distribucion por sucursal no tiene un formato valido.');
    }
    $localesPermitidos = array_map('intval', gastoTesoreriaLocalesAnaliticos());
    $asignaciones = array();
    foreach ($recibidas as $fila) {
        if (!is_array($fila)) {
            continue;
        }
        $codLocal = isset($fila['cod_local']) ? (int)$fila['cod_local'] : 0;
        $montoLocal = isset($fila['monto']) ? gastoTesoreriaMonto($fila['monto']) : 0;
        if ($codLocal <= 0 || $montoLocal <= 0 || isset($asignaciones[$codLocal])) {
            throw new Exception('Cada sucursal debe aparecer una sola vez y con un monto mayor a cero.');
        }
        $asignaciones[$codLocal] = $montoLocal;
    }
    if ($modo === 'local') {
        if ($codLocalPagoSolicitado <= 1 || count($asignaciones) !== 1
            || !isset($asignaciones[$codLocalPagoSolicitado])) {
            throw new Exception('En modo de un local, la sucursal que paga debe coincidir con el unico destino.');
        }
        $codLocalPago = $codLocalPagoSolicitado;
    } else {
        $codLocalPago = 1;
        if (count($asignaciones) < 1) {
            throw new Exception('Seleccione por lo menos una sucursal para el impacto contable.');
        }
        foreach (array_keys($asignaciones) as $codLocal) {
            if (!in_array((int)$codLocal, $localesPermitidos, true)) {
                throw new Exception('La distribucion contiene una sucursal no habilitada para el flujo consolidado.');
            }
        }
        if ($modo === 'compartido' && count($asignaciones) !== count($localesPermitidos)) {
            throw new Exception('La administracion compartida debe incluir las cinco sucursales.');
        }
    }
    if (array_sum($asignaciones) !== $monto) {
        throw new Exception('La suma distribuida debe coincidir exactamente con el monto del gasto.');
    }
    ksort($asignaciones, SORT_NUMERIC);
    if (count($asignaciones) > 0) {
        $resultado = $mysqli->query("SELECT cod_local FROM local WHERE cod_local IN (".implode(',', array_keys($asignaciones)).")");
        if (!$resultado || $resultado->num_rows !== count($asignaciones)) {
            throw new Exception('Una de las sucursales seleccionadas ya no existe.');
        }
    }
    return array('modo' => $modo, 'asignaciones' => $asignaciones, 'cod_local_pago' => $codLocalPago);
}

function gastoTesoreriaSnapshotBase($mysqli, $gasto, $bloquearDistribucion = false)
{
    $distribucion = gastoDistribucionObtenerEfectiva($mysqli, (int)$gasto['idgastos'], $bloquearDistribucion);
    return array(
        'monto' => (int)round($gasto['monto']),
        'fecha' => substr((string)$gasto['fecha'], 0, 10),
        'cod_local_pago' => (int)$gasto['cod_local'],
        'modo_distribucion' => (string)$distribucion['modo'],
        'asignaciones' => $distribucion['asignaciones']
    );
}

function gastoTesoreriaSnapshotEfectivoPagado($mysqli, $gasto, $bloquearDistribucion = false)
{
    $snapshot = gastoTesoreriaSnapshotBase($mysqli, $gasto, $bloquearDistribucion);
    if (!gastoTesoreriaTablaExiste($mysqli, 'gasto_tesoreria_modificacion')) {
        return $snapshot;
    }
    $idgastos = (int)$gasto['idgastos'];
    $stmt = $mysqli->prepare("SELECT valores_nuevos_json FROM gasto_tesoreria_modificacion
        WHERE idgastosFK=? AND tipo_modificacion='correccion_pagada'
        ORDER BY id_modificacion DESC LIMIT 1".($bloquearDistribucion ? ' FOR UPDATE' : ''));
    if (!$stmt) {
        throw new Exception('No se pudo consultar la ultima correccion del movimiento.');
    }
    $stmt->bind_param('i', $idgastos);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) {
        $decodificado = json_decode((string)$fila['valores_nuevos_json'], true);
        if (is_array($decodificado) && isset($decodificado['monto'], $decodificado['fecha'], $decodificado['asignaciones'])) {
            $snapshot = $decodificado;
        }
    }
    $snapshot['monto'] = (int)$snapshot['monto'];
    $snapshot['cod_local_pago'] = (int)$snapshot['cod_local_pago'];
    $normalizadas = array();
    foreach ((array)$snapshot['asignaciones'] as $codLocal => $montoLocal) {
        $normalizadas[(int)$codLocal] = (int)$montoLocal;
    }
    ksort($normalizadas, SORT_NUMERIC);
    $snapshot['asignaciones'] = $normalizadas;
    return $snapshot;
}

function gastoTesoreriaCuotasPendientesDesde($mysqli, $gasto, $bloquear = false)
{
    $idgastos = (int)$gasto['idgastos'];
    $idRaiz = !empty($gasto['cod_gasto_padre']) ? (int)$gasto['cod_gasto_padre'] : $idgastos;
    $fechaSeleccionada = substr((string)$gasto['fecha'], 0, 10);
    $sufijoBloqueo = $bloquear ? ' FOR UPDATE' : '';
    $sql = "SELECT * FROM gastos WHERE (idgastos=$idRaiz OR cod_gasto_padre=$idRaiz)
        AND LOWER(TRIM(IFNULL(estado,''))) IN ('pendiente','solicitado')
        AND (fecha>'".$mysqli->real_escape_string($fechaSeleccionada)."'
            OR (fecha='".$mysqli->real_escape_string($fechaSeleccionada)."' AND idgastos>=$idgastos))
        ORDER BY fecha,idgastos".$sufijoBloqueo;
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        throw new Exception('No se pudieron consultar las cuotas pendientes de la serie.');
    }
    $filas = array();
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    if (count($filas) === 0 && !empty($gasto['cod_proyecto_gastoFK'])) {
        $codProyecto = (int)$gasto['cod_proyecto_gastoFK'];
        $codHilo = (int)$gasto['cod_interConsultaFK'];
        $codMotivo = (int)$gasto['cod_motivoIngresoEgresoFK'];
        $sql = "SELECT * FROM gastos WHERE cod_proyecto_gastoFK=$codProyecto
            AND cod_interConsultaFK=$codHilo AND cod_motivoIngresoEgresoFK=$codMotivo
            AND modalidad='credito' AND LOWER(TRIM(IFNULL(estado,''))) IN ('pendiente','solicitado')
            AND (fecha>'".$mysqli->real_escape_string($fechaSeleccionada)."'
                OR (fecha='".$mysqli->real_escape_string($fechaSeleccionada)."' AND idgastos>=$idgastos))
            ORDER BY fecha,idgastos".$sufijoBloqueo;
        $resultado = $mysqli->query($sql);
        if (!$resultado) {
            throw new Exception('No se pudieron consultar las cuotas legacy pendientes.');
        }
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }
    }
    return $filas;
}

function gastoTesoreriaPrepararPlan($mysqli, $gasto, $solicitud, $bloquear = false)
{
    $estado = strtolower(trim((string)$gasto['estado']));
    $esPagado = $estado === 'activo';
    if (!$esPagado && !in_array($estado, array('pendiente', 'solicitado'), true)) {
        throw new Exception('Solo pueden modificarse movimientos pendientes o corregirse movimientos pagados.');
    }
    if (strtolower(trim((string)$gasto['tipo'])) !== 'egreso') {
        throw new Exception('El flujo de Tesoreria corresponde solamente a egresos.');
    }
    $montoNuevo = gastoTesoreriaMonto(isset($solicitud['monto']) ? $solicitud['monto'] : 0);
    $fechaNueva = trim((string)(isset($solicitud['fecha']) ? $solicitud['fecha'] : ''));
    if ($montoNuevo <= 0 || !gastoTesoreriaFechaValida($fechaNueva)) {
        throw new Exception('Ingrese un monto mayor a cero y una fecha valida.');
    }
    $alcanceMonto = isset($solicitud['alcance_monto']) ? strtolower(trim((string)$solicitud['alcance_monto'])) : 'actual';
    $alcanceFecha = isset($solicitud['alcance_fecha']) ? strtolower(trim((string)$solicitud['alcance_fecha'])) : 'mantener';
    if (!in_array($alcanceMonto, array('actual', 'pendientes'), true)
        || !in_array($alcanceFecha, array('mantener', 'actual', 'pendientes'), true)) {
        throw new Exception('El alcance elegido para monto o fecha no es valido.');
    }
    if ($esPagado) {
        $alcanceMonto = 'actual';
        $alcanceFecha = $alcanceFecha === 'mantener' ? 'mantener' : 'actual';
    }
    $distribucion = gastoTesoreriaNormalizarDistribucion(
        $mysqli,
        isset($solicitud['modo_distribucion']) ? $solicitud['modo_distribucion'] : '',
        isset($solicitud['distribucion_locales']) ? $solicitud['distribucion_locales'] : '[]',
        $montoNuevo,
        isset($solicitud['cod_local']) ? $solicitud['cod_local'] : $gasto['cod_local']
    );
    $snapshotAnterior = $esPagado
        ? gastoTesoreriaSnapshotEfectivoPagado($mysqli, $gasto, $bloquear)
        : gastoTesoreriaSnapshotBase($mysqli, $gasto, $bloquear);
    $snapshotNuevo = array(
        'monto' => $montoNuevo,
        'fecha' => $alcanceFecha === 'mantener' ? $snapshotAnterior['fecha'] : $fechaNueva,
        'cod_local_pago' => $distribucion['cod_local_pago'],
        'modo_distribucion' => $distribucion['modo'],
        'asignaciones' => $distribucion['asignaciones']
    );
    if ($esPagado && gastoTesoreriaJson($snapshotAnterior) === gastoTesoreriaJson($snapshotNuevo)) {
        throw new Exception('No hay cambios financieros para registrar sobre este movimiento pagado.');
    }
    $pendientes = $esPagado ? array() : gastoTesoreriaCuotasPendientesDesde($mysqli, $gasto, $bloquear);
    $pendientesFirma = array();
    foreach ($pendientes as $filaPendiente) {
        $pendientesFirma[] = array(
            'idgastos' => (int)$filaPendiente['idgastos'],
            'estado' => strtolower(trim((string)$filaPendiente['estado'])),
            'snapshot' => gastoTesoreriaSnapshotBase($mysqli, $filaPendiente, $bloquear)
        );
    }
    $idsMonto = array();
    $idsFecha = array();
    if (!$esPagado) {
        if ($alcanceMonto === 'pendientes') {
            foreach ($pendientes as $fila) {
                $idsMonto[] = (int)$fila['idgastos'];
            }
        } else {
            $idsMonto[] = (int)$gasto['idgastos'];
        }
        if ($alcanceFecha === 'pendientes') {
            foreach ($pendientes as $fila) {
                $idsFecha[] = (int)$fila['idgastos'];
            }
        } elseif ($alcanceFecha === 'actual') {
            $idsFecha[] = (int)$gasto['idgastos'];
        }
    }
    $idsAfectados = array_values(array_unique(array_merge($idsMonto, $idsFecha)));
    sort($idsAfectados, SORT_NUMERIC);
    return array(
        'es_pagado' => $esPagado,
        'estado' => $estado,
        'alcance_monto' => $alcanceMonto,
        'alcance_fecha' => $alcanceFecha,
        'periodicidad' => strtolower(trim((string)(isset($solicitud['periodicidad']) ? $solicitud['periodicidad'] : ''))),
        'anterior' => $snapshotAnterior,
        'nuevo' => $snapshotNuevo,
        'pendientes' => $pendientes,
        'pendientes_firma' => $pendientesFirma,
        'ids_monto' => $idsMonto,
        'ids_fecha' => $idsFecha,
        'ids_afectados' => $idsAfectados
    );
}

function gastoTesoreriaResumenPlan($plan, $gasto)
{
    $cantidadMonto = $plan['es_pagado'] ? 1 : count($plan['ids_monto']);
    $cantidadFecha = $plan['es_pagado'] ? ($plan['alcance_fecha'] === 'actual' ? 1 : 0) : count($plan['ids_fecha']);
    return array(
        'tipo' => $plan['es_pagado'] ? 'correccion_pagada' : 'edicion_pendiente',
        'movimiento' => (int)$gasto['idgastos'],
        'estado' => $plan['estado'],
        'monto_anterior' => (int)$plan['anterior']['monto'],
        'monto_nuevo' => (int)$plan['nuevo']['monto'],
        'fecha_anterior' => $plan['anterior']['fecha'],
        'fecha_nueva' => $plan['nuevo']['fecha'],
        'cuotas_monto' => $cantidadMonto,
        'cuotas_fecha' => $cantidadFecha,
        'modo_anterior' => $plan['anterior']['modo_distribucion'],
        'modo_nuevo' => $plan['nuevo']['modo_distribucion'],
        'asignaciones_nuevas' => $plan['nuevo']['asignaciones'],
        'mensaje' => $plan['es_pagado']
            ? 'El movimiento pagado no se reescribira. Se registrara una reversion del impacto anterior y una nueva aplicacion trazable.'
            : 'Se actualizaran unicamente las cuotas pendientes incluidas en este resumen; ninguna cuota pagada sera modificada.'
    );
}

function gastoTesoreriaFirmaPlan($plan)
{
    $datos = array(
        'es_pagado' => !empty($plan['es_pagado']) ? 1 : 0,
        'estado' => $plan['estado'],
        'alcance_monto' => $plan['alcance_monto'],
        'alcance_fecha' => $plan['alcance_fecha'],
        'periodicidad' => $plan['periodicidad'],
        'anterior' => $plan['anterior'],
        'nuevo' => $plan['nuevo'],
        'pendientes' => $plan['pendientes_firma'],
        'ids_monto' => array_values($plan['ids_monto']),
        'ids_fecha' => array_values($plan['ids_fecha']),
        'ids_afectados' => array_values($plan['ids_afectados'])
    );
    return hash('sha256', json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function gastoTesoreriaPrevisualizar($codUsuario, $solicitud)
{
    $mysqli = conectar_al_servidor();
    try {
        if (!gastoTesoreriaEstructuraDisponible($mysqli) || !gastoTesoreriaEsResponsable($mysqli, $codUsuario)) {
            throw new Exception('Solo la responsable oficial de Tesoreria puede preparar esta modificacion.');
        }
        $idgastos = isset($solicitud['idgastos']) ? (int)$solicitud['idgastos'] : 0;
        $stmt = $mysqli->prepare('SELECT * FROM gastos WHERE idgastos=? LIMIT 1');
        if (!$stmt) {
            throw new Exception('No se pudo consultar el movimiento.');
        }
        $stmt->bind_param('i', $idgastos);
        $stmt->execute();
        $gasto = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$gasto) {
            throw new Exception('El movimiento ya no existe.');
        }
        $plan = gastoTesoreriaPrepararPlan($mysqli, $gasto, $solicitud, false);
        $resumen = gastoTesoreriaResumenPlan($plan, $gasto);
        $mysqli->close();
        return array('1' => 'exito', 'resumen' => $resumen, 'firma_previa' => gastoTesoreriaFirmaPlan($plan));
    } catch (Exception $e) {
        $mysqli->close();
        return array('1' => 'error', '2' => $e->getMessage());
    }
}

function gastoTesoreriaJson($valor)
{
    return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function gastoTesoreriaRegistrarAuditoria($mysqli, $idgastos, $tipo, $estado, $alcanceMonto, $alcanceFecha, $motivo, $anterior, $nuevo, $ids, $codUsuario)
{
    $anteriorJson = gastoTesoreriaJson($anterior);
    $nuevoJson = gastoTesoreriaJson($nuevo);
    $idsJson = gastoTesoreriaJson(array_values($ids));
    $stmt = $mysqli->prepare("INSERT INTO gasto_tesoreria_modificacion
        (idgastosFK,tipo_modificacion,estado_movimiento,alcance_monto,alcance_fecha,motivo,
        valores_anteriores_json,valores_nuevos_json,ids_afectados_json,cod_usuario_actorFK,fecha_hora)
        VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
    if (!$stmt) {
        throw new Exception('No se pudo preparar la auditoria de Tesoreria.');
    }
    $stmt->bind_param('issssssssi', $idgastos, $tipo, $estado, $alcanceMonto, $alcanceFecha, $motivo, $anteriorJson, $nuevoJson, $idsJson, $codUsuario);
    if (!$stmt->execute()) {
        $mensaje = $stmt->error;
        $stmt->close();
        throw new Exception('No se pudo registrar la trazabilidad de Tesoreria: '.$mensaje);
    }
    $id = (int)$stmt->insert_id;
    $stmt->close();
    return $id;
}

function gastoTesoreriaValidarPresupuestoCorreccion($mysqli, $gasto, $anterior, $nuevo)
{
    $codMotivo = (int)$gasto['cod_motivoIngresoEgresoFK'];
    $cubetas = array();
    foreach (array('anterior' => $anterior, 'nuevo' => $nuevo) as $tipo => $snapshot) {
        $mes = substr((string)$snapshot['fecha'], 0, 7);
        foreach ((array)$snapshot['asignaciones'] as $codLocal => $monto) {
            $clave = $mes.'|'.(int)$codLocal;
            if (!isset($cubetas[$clave])) {
                $cubetas[$clave] = array('mes' => $mes, 'cod_local' => (int)$codLocal, 'anterior' => 0, 'nuevo' => 0);
            }
            $cubetas[$clave][$tipo] += (int)$monto;
        }
    }
    ksort($cubetas, SORT_STRING);
    foreach ($cubetas as $cubeta) {
        $codLocal = (int)$cubeta['cod_local'];
        $resultado = $mysqli->query("SELECT monto_limite FROM montos_limites_gasto_motivo
            WHERE cod_motivo_ingreso_egresoFK=$codMotivo AND cod_localFK=$codLocal
            ORDER BY cod_monto_limite_gasto_motivo FOR UPDATE");
        if (!$resultado) {
            throw new Exception('No se pudo validar el presupuesto de la correccion.');
        }
        if ($resultado->num_rows > 1) {
            throw new Exception('Existe mas de un presupuesto configurado para una sucursal de la correccion.');
        }
        $fila = $resultado->fetch_assoc();
        $limite = $fila ? (int)$fila['monto_limite'] : 0;
        if ($limite <= 0) {
            continue;
        }
        $desde = $cubeta['mes'].'-01';
        $fechaMes = DateTime::createFromFormat('!Y-m-d', $desde);
        $hasta = $fechaMes->format('Y-m-t');
        $usado = gastoDistribucionMontoUsadoPresupuesto($mysqli, $codLocal, $codMotivo, $desde, $hasta, array(), false);
        $proyectado = $usado - (int)$cubeta['anterior'] + (int)$cubeta['nuevo'];
        if ($proyectado > $limite) {
            throw new Exception('La correccion supera el presupuesto del concepto en la sucursal #'.$codLocal.'. Disponible: Gs. '.number_format(max(0, $limite - ($usado - (int)$cubeta['anterior'])), 0, ',', '.').'.');
        }
    }
}

function gastoTesoreriaRegistrarImpactosPagados($mysqli, $idModificacion, $gasto, $anterior, $nuevo, $codUsuario)
{
    $stmt = $mysqli->prepare("INSERT INTO gasto_tesoreria_impacto
        (id_modificacionFK,idgastosFK,cod_localFK,cod_local_pago_snapshot,cod_motivoIngresoEgresoFK,
        fecha_impacto,monto_impacto,tipo_impacto,cod_usuario_actorFK,fecha_hora)
        VALUES (?,?,?,?,?,?,?,?,?,NOW())");
    if (!$stmt) {
        throw new Exception('No se pudo preparar el impacto trazable de la correccion.');
    }
    $idgastos = (int)$gasto['idgastos'];
    $codMotivo = (int)$gasto['cod_motivoIngresoEgresoFK'];
    foreach (array('reversion' => $anterior, 'aplicacion' => $nuevo) as $tipo => $snapshot) {
        $fecha = (string)$snapshot['fecha'];
        $codLocalPago = (int)$snapshot['cod_local_pago'];
        foreach ((array)$snapshot['asignaciones'] as $codLocal => $monto) {
            $codLocal = (int)$codLocal;
            $montoImpacto = $tipo === 'reversion' ? -(int)$monto : (int)$monto;
            $stmt->bind_param('iiiiisisi', $idModificacion, $idgastos, $codLocal, $codLocalPago, $codMotivo, $fecha, $montoImpacto, $tipo, $codUsuario);
            if (!$stmt->execute()) {
                $mensaje = $stmt->error;
                $stmt->close();
                throw new Exception('No se pudo guardar el impacto de la correccion: '.$mensaje);
            }
        }
    }
    $stmt->close();
}

function gastoTesoreriaActualizarPendientes($mysqli, $gasto, $plan, $codUsuario)
{
    $filas = array();
    foreach ($plan['pendientes'] as $fila) {
        $filas[(int)$fila['idgastos']] = $fila;
    }
    $idSeleccionado = (int)$gasto['idgastos'];
    if (!isset($filas[$idSeleccionado])) {
        $filas[$idSeleccionado] = $gasto;
    }
    $idsMonto = array_flip(array_map('intval', $plan['ids_monto']));
    $idsFecha = array_flip(array_map('intval', $plan['ids_fecha']));
    $fechasNuevas = array();
    if (count($idsFecha) > 0) {
        if ($plan['alcance_fecha'] === 'pendientes' && count($idsFecha) > 1) {
            $permitidas = array('semanal', 'quincenal', 'mensual', 'semestral', 'anual');
            if (!in_array($plan['periodicidad'], $permitidas, true)) {
                throw new Exception('Seleccione una periodicidad valida para reprogramar las cuotas pendientes.');
            }
            $base = DateTime::createFromFormat('!Y-m-d', $plan['nuevo']['fecha']);
            $indice = 0;
            foreach ($plan['pendientes'] as $fila) {
                $id = (int)$fila['idgastos'];
                if (!isset($idsFecha[$id])) {
                    continue;
                }
                $fecha = calcularFechaCuotaRecurrente($base, $plan['periodicidad'], $indice);
                if (!$fecha) {
                    throw new Exception('No se pudo calcular la nueva fecha de una cuota.');
                }
                $fechasNuevas[$id] = $fecha->format('Y-m-d');
                $indice++;
            }
        } else {
            foreach ($idsFecha as $id => $ignorado) {
                $fechasNuevas[(int)$id] = $plan['nuevo']['fecha'];
            }
        }
    }
    $idsAfectados = array_values(array_unique(array_merge(array_keys($idsMonto), array_keys($idsFecha))));
    sort($idsAfectados, SORT_NUMERIC);
    $planesFinales = array();
    foreach ($idsAfectados as $id) {
        if (!isset($filas[$id])) {
            throw new Exception('Una cuota pendiente cambio mientras se preparaba la modificacion.');
        }
        $fila = $filas[$id];
        $snapshot = gastoTesoreriaSnapshotBase($mysqli, $fila, true);
        if (isset($idsMonto[$id])) {
            $snapshot['monto'] = $plan['nuevo']['monto'];
            $snapshot['cod_local_pago'] = $plan['nuevo']['cod_local_pago'];
            $snapshot['modo_distribucion'] = $plan['nuevo']['modo_distribucion'];
            $snapshot['asignaciones'] = $plan['nuevo']['asignaciones'];
        }
        if (isset($fechasNuevas[$id])) {
            $snapshot['fecha'] = $fechasNuevas[$id];
        }
        $planesFinales[$id] = array('fila' => $fila, 'snapshot' => $snapshot);
    }
    $agrupados = array();
    foreach ($planesFinales as $id => $detalle) {
        $fila = $detalle['fila'];
        $snapshot = $detalle['snapshot'];
        $mes = substr($snapshot['fecha'], 0, 7);
        $codMotivo = (int)$fila['cod_motivoIngresoEgresoFK'];
        $clave = $codMotivo.'|'.$mes;
        if (!isset($agrupados[$clave])) {
            $agrupados[$clave] = array('cod_motivo' => $codMotivo, 'mes' => $mes, 'asignaciones' => array());
        }
        foreach ($snapshot['asignaciones'] as $codLocal => $monto) {
            if (!isset($agrupados[$clave]['asignaciones'][$codLocal])) {
                $agrupados[$clave]['asignaciones'][$codLocal] = 0;
            }
            $agrupados[$clave]['asignaciones'][$codLocal] += (int)$monto;
        }
    }
    ksort($agrupados, SORT_STRING);
    foreach ($agrupados as $grupo) {
        $fechaMes = DateTime::createFromFormat('!Y-m-d', $grupo['mes'].'-01');
        $distribucionGrupo = array('asignaciones' => $grupo['asignaciones']);
        gastoDistribucionBloquearPresupuestos($mysqli, $distribucionGrupo, $grupo['cod_motivo']);
        gastoDistribucionValidarPresupuestos(
            $mysqli,
            $distribucionGrupo,
            $grupo['cod_motivo'],
            $fechaMes->format('Y-m-01'),
            $fechaMes->format('Y-m-t'),
            $idsAfectados,
            false
        );
    }
    $totalesHilo = array();
    foreach ($planesFinales as $detalle) {
        $codHilo = (int)$detalle['fila']['cod_interConsultaFK'];
        if ($codHilo > 0) {
            if (!isset($totalesHilo[$codHilo])) {
                $totalesHilo[$codHilo] = 0;
            }
            $totalesHilo[$codHilo] += (int)$detalle['snapshot']['monto'];
        }
    }
    foreach ($totalesHilo as $codHilo => $montoTotal) {
        gastoValidarLimiteHiloBloqueado($mysqli, $codHilo, $montoTotal, $idsAfectados);
    }
    $anteriores = array();
    $nuevos = array();
    $cajaAdministracion = null;
    foreach ($planesFinales as $idPlan => $detallePlan) {
        if (isset($idsMonto[$idPlan]) && (int)$detallePlan['snapshot']['cod_local_pago'] === 1) {
            $cajaAdministracion = gastoTesoreriaCajaActivaUsuarioLocal($mysqli, $codUsuario, 1, true);
            if (!$cajaAdministracion) {
                throw new Exception('Para dejar Administracion como caja pagadora, la responsable de Tesoreria debe tener una caja activa en Administracion.');
            }
            break;
        }
    }
    foreach ($planesFinales as $id => $detalle) {
        $fila = $detalle['fila'];
        $snapshotAnterior = gastoTesoreriaSnapshotBase($mysqli, $fila, true);
        $snapshotNuevo = $detalle['snapshot'];
        $anteriores[$id] = $snapshotAnterior;
        $nuevos[$id] = $snapshotNuevo;
        $monto = (int)$snapshotNuevo['monto'];
        $fecha = $snapshotNuevo['fecha'];
        $codLocalPago = (int)$snapshotNuevo['cod_local_pago'];
        $usarCajaAdministracion = isset($idsMonto[$id]) && $codLocalPago === 1 && $cajaAdministracion;
        $stmt = $mysqli->prepare($usarCajaAdministracion
            ? "UPDATE gastos SET monto=?,fecha=?,cod_local=?,codCaja=?,codApertura=?,cod_usuarioFK_edit=?
                WHERE idgastos=? AND LOWER(TRIM(IFNULL(estado,''))) IN ('pendiente','solicitado')"
            : "UPDATE gastos SET monto=?,fecha=?,cod_local=?,cod_usuarioFK_edit=?
                WHERE idgastos=? AND LOWER(TRIM(IFNULL(estado,''))) IN ('pendiente','solicitado')");
        if (!$stmt) {
            throw new Exception('No se pudo preparar la actualizacion de una cuota pendiente.');
        }
        if ($usarCajaAdministracion) {
            $codCajaAdministracion = (int)$cajaAdministracion['caja_idcaja'];
            $codAperturaAdministracion = (int)$cajaAdministracion['idarqueocaja'];
            $stmt->bind_param('isiiiii', $monto, $fecha, $codLocalPago, $codCajaAdministracion, $codAperturaAdministracion, $codUsuario, $id);
        } else {
            $stmt->bind_param('isiii', $monto, $fecha, $codLocalPago, $codUsuario, $id);
        }
        if (!$stmt->execute() || $stmt->affected_rows < 0) {
            $mensaje = $stmt->error;
            $stmt->close();
            throw new Exception('No se pudo actualizar una cuota pendiente: '.$mensaje);
        }
        $stmt->close();
        if (isset($idsMonto[$id])) {
            gastoDistribucionGuardar(
                $mysqli,
                $id,
                array('modo' => $snapshotNuevo['modo_distribucion'], 'asignaciones' => $snapshotNuevo['asignaciones']),
                $codUsuario,
                'tesoreria',
                'editar',
                false,
                array('modo' => $snapshotAnterior['modo_distribucion'], 'asignaciones' => $snapshotAnterior['asignaciones'])
            );
        }
        if (isset($idsFecha[$id]) && !empty($fila['cod_mensajeFK'])) {
            $codMensaje = (int)$fila['cod_mensajeFK'];
            $stmtMensaje = $mysqli->prepare("UPDATE mensaje SET fecha_creacion=? WHERE cod_mensaje=? AND LOWER(TRIM(IFNULL(estado,'')))<>'inactivo'");
            if (!$stmtMensaje) {
                throw new Exception('No se pudo preparar la reprogramacion del recordatorio.');
            }
            $stmtMensaje->bind_param('si', $fecha, $codMensaje);
            if (!$stmtMensaje->execute()) {
                $mensaje = $stmtMensaje->error;
                $stmtMensaje->close();
                throw new Exception('No se pudo reprogramar el recordatorio: '.$mensaje);
            }
            $stmtMensaje->close();
        }
    }
    return array('ids' => $idsAfectados, 'anteriores' => $anteriores, 'nuevos' => $nuevos);
}

function gastoTesoreriaAplicar($codUsuario, $solicitud)
{
    $codUsuario = (int)$codUsuario;
    $motivo = trim((string)(isset($solicitud['motivo_correccion']) ? $solicitud['motivo_correccion'] : ''));
    if ($motivo === '' || (function_exists('mb_strlen') ? mb_strlen($motivo, 'UTF-8') : strlen($motivo)) < 8) {
        return array('1' => 'error', '2' => 'Explique brevemente el motivo del cambio (minimo 8 caracteres).');
    }
    if ((function_exists('mb_strlen') ? mb_strlen($motivo, 'UTF-8') : strlen($motivo)) > 500) {
        return array('1' => 'error', '2' => 'El motivo puede tener hasta 500 caracteres.');
    }
    $firmaPrevia = trim((string)(isset($solicitud['firma_previa']) ? $solicitud['firma_previa'] : ''));
    if (strlen($firmaPrevia) !== 64 || !ctype_xdigit($firmaPrevia)) {
        return array('1' => 'error', '2' => 'Primero prepare y revise la vista previa del cambio.');
    }
    $motivoDb = mb_convert_encoding($motivo, 'ISO-8859-1', 'UTF-8');
    $mysqli = conectar_al_servidor();
    $mysqli->query('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
    if (!gastoTesoreriaEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('1' => 'error', '2' => 'La actualizacion de Tesoreria aun no fue aplicada en la base de datos.');
    }
    if (!$mysqli->begin_transaction()) {
        $mysqli->close();
        return array('1' => 'error', '2' => 'No se pudo iniciar la modificacion segura de Tesoreria.');
    }
    try {
        $responsable = gastoTesoreriaResponsableActual($mysqli, true);
        if (empty($responsable) || (int)$responsable['cod_usuario'] !== $codUsuario) {
            throw new Exception('La responsable oficial de Tesoreria cambio. Actualice la pantalla antes de continuar.');
        }
        $idgastos = isset($solicitud['idgastos']) ? (int)$solicitud['idgastos'] : 0;
        $stmt = $mysqli->prepare('SELECT * FROM gastos WHERE idgastos=? LIMIT 1 FOR UPDATE');
        if (!$stmt) {
            throw new Exception('No se pudo bloquear el movimiento seleccionado.');
        }
        $stmt->bind_param('i', $idgastos);
        $stmt->execute();
        $gasto = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$gasto) {
            throw new Exception('El movimiento seleccionado ya no existe.');
        }
        $plan = gastoTesoreriaPrepararPlan($mysqli, $gasto, $solicitud, true);
        if (!hash_equals(gastoTesoreriaFirmaPlan($plan), strtolower($firmaPrevia))) {
            throw new Exception('El movimiento o sus cuotas cambiaron despues de la vista previa. Vuelva a revisarla antes de confirmar.');
        }
        if ($plan['es_pagado']) {
            gastoTesoreriaValidarPresupuestoCorreccion($mysqli, $gasto, $plan['anterior'], $plan['nuevo']);
            $idModificacion = gastoTesoreriaRegistrarAuditoria(
                $mysqli,
                $idgastos,
                'correccion_pagada',
                $plan['estado'],
                'actual',
                $plan['alcance_fecha'],
                $motivoDb,
                $plan['anterior'],
                $plan['nuevo'],
                array($idgastos),
                $codUsuario
            );
            gastoTesoreriaRegistrarImpactosPagados($mysqli, $idModificacion, $gasto, $plan['anterior'], $plan['nuevo'], $codUsuario);
            $ids = array($idgastos);
        } else {
            $resultadoPendientes = gastoTesoreriaActualizarPendientes($mysqli, $gasto, $plan, $codUsuario);
            $idModificacion = gastoTesoreriaRegistrarAuditoria(
                $mysqli,
                $idgastos,
                'edicion_pendiente',
                $plan['estado'],
                $plan['alcance_monto'],
                $plan['alcance_fecha'],
                $motivoDb,
                $resultadoPendientes['anteriores'],
                $resultadoPendientes['nuevos'],
                $resultadoPendientes['ids'],
                $codUsuario
            );
            $ids = $resultadoPendientes['ids'];
        }
        if (!$mysqli->commit()) {
            throw new Exception('No se pudo confirmar la modificacion de Tesoreria.');
        }
        $mysqli->close();
        return array(
            '1' => 'exito',
            '2' => $idgastos,
            'id_modificacion' => $idModificacion,
            'tipo' => $plan['es_pagado'] ? 'correccion_pagada' : 'edicion_pendiente',
            'ids_afectados' => $ids
        );
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return array('1' => 'error', '2' => $e->getMessage());
    }
}

?>

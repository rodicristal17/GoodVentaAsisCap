<?php

/**
 * Fusion transaccional y auditable de Hilos.
 *
 * El hilo origen se conserva como registro inactivo. Sus consumidores activos
 * se reasignan al destino y la relacion queda asentada en interconsulta_fusion.
 */

function interconsultaFusionTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '' || !($mysqli instanceof mysqli)) return false;
    if (isset($cache[$tabla])) return $cache[$tabla];
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('s', $tabla);
    $stmt->execute();
    $cache[$tabla] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$tabla];
}

function interconsultaFusionColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$columna);
    $clave = $tabla.'.'.$columna;
    if ($tabla === '' || $columna === '' || !($mysqli instanceof mysqli)) return false;
    if (isset($cache[$clave])) return $cache[$clave];
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $cache[$clave] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$clave];
}

function interconsultaFusionTextoSalida($texto)
{
    $texto = (string)$texto;
    if ($texto !== '' && function_exists('mb_check_encoding') && !mb_check_encoding($texto, 'UTF-8')) {
        return mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
    }
    return $texto;
}

function interconsultaFusionObtenerHilos($mysqli, $origen, $destino, $bloquear)
{
    $sql = "SELECT cod_interConsulta,asunto,estado,tipo,cod_ventaFK,cod_usuarioFK_create,cod_localFK,fecha_creacion
        FROM interconsulta WHERE cod_interConsulta IN (?,?) ORDER BY cod_interConsulta".($bloquear ? ' FOR UPDATE' : '');
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return array();
    $stmt->bind_param('ii', $origen, $destino);
    if (!$stmt->execute()) { $stmt->close(); return array(); }
    $salida = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $salida[intval($fila['cod_interConsulta'])] = $fila;
    }
    $stmt->close();
    return $salida;
}

function interconsultaFusionAgregarValor(&$mapa, $valor)
{
    $valor = trim((string)$valor);
    if ($valor !== '' && $valor !== '0') $mapa[$valor] = 1;
}

function interconsultaFusionIdentidades($mysqli, $origen, $destino)
{
    $ids = array(intval($origen), intval($destino));
    $salida = array();
    foreach ($ids as $id) {
        $salida[$id] = array('cedulas' => array(), 'clientes' => array(), 'colaboradores' => array());
    }
    if (interconsultaFusionTablaExiste($mysqli, 'interconsulta_paciente')) {
        $stmt = $mysqli->prepare("SELECT cod_interConsultaFK,cedula_normalizada,cod_clienteFK_principal
            FROM interconsulta_paciente WHERE cod_interConsultaFK IN (?,?) AND estado='activo'");
        if ($stmt) {
            $stmt->bind_param('ii', $origen, $destino); $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $hilo = intval($fila['cod_interConsultaFK']);
                interconsultaFusionAgregarValor($salida[$hilo]['cedulas'], $fila['cedula_normalizada']);
                interconsultaFusionAgregarValor($salida[$hilo]['clientes'], intval($fila['cod_clienteFK_principal']));
            }
            $stmt->close();
        }
    }
    if (interconsultaFusionTablaExiste($mysqli, 'interconsulta_paciente_venta')) {
        $stmt = $mysqli->prepare("SELECT cod_interConsultaFK,cedula_normalizada,cod_clienteFK
            FROM interconsulta_paciente_venta WHERE cod_interConsultaFK IN (?,?) AND estado='activo'");
        if ($stmt) {
            $stmt->bind_param('ii', $origen, $destino); $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $hilo = intval($fila['cod_interConsultaFK']);
                interconsultaFusionAgregarValor($salida[$hilo]['cedulas'], $fila['cedula_normalizada']);
                interconsultaFusionAgregarValor($salida[$hilo]['clientes'], intval($fila['cod_clienteFK']));
            }
            $stmt->close();
        }
    }
    $stmt = $mysqli->prepare("SELECT ic.cod_interConsulta,v.cod_clienteFK
        FROM interconsulta ic LEFT JOIN venta v ON v.cod_venta=ic.cod_ventaFK
        WHERE ic.cod_interConsulta IN (?,?)");
    if ($stmt) {
        $stmt->bind_param('ii', $origen, $destino); $stmt->execute();
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $hilo = intval($fila['cod_interConsulta']);
            interconsultaFusionAgregarValor($salida[$hilo]['clientes'], intval($fila['cod_clienteFK']));
        }
        $stmt->close();
    }
    if (interconsultaFusionTablaExiste($mysqli, 'funcionario_hilo_principal')) {
        $stmt = $mysqli->prepare("SELECT cod_interConsultaFK,cod_usuarioFK FROM funcionario_hilo_principal
            WHERE cod_interConsultaFK IN (?,?) AND estado='activo'");
        if ($stmt) {
            $stmt->bind_param('ii', $origen, $destino); $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                $hilo = intval($fila['cod_interConsultaFK']);
                interconsultaFusionAgregarValor($salida[$hilo]['colaboradores'], intval($fila['cod_usuarioFK']));
            }
            $stmt->close();
        }
    }
    return $salida;
}

function interconsultaFusionConjuntosCompatibles($a, $b)
{
    $a = array_keys((array)$a);
    $b = array_keys((array)$b);
    if (count($a) === 0 || count($b) === 0) return true;
    return count(array_intersect($a, $b)) > 0;
}

function interconsultaFusionValidar($mysqli, $origen, $destino, $usuario, $bloquear)
{
    $origen = intval($origen); $destino = intval($destino); $usuario = intval($usuario);
    if ($origen <= 0 || $destino <= 0) return array('ok' => false, 'mensaje' => 'Seleccione ambos hilos.');
    if ($origen === $destino) return array('ok' => false, 'mensaje' => 'El hilo de origen y el hilo maestro deben ser diferentes.');
    if (!function_exists('interconsultaAccesoTienePermiso')
        || !interconsultaAccesoTienePermiso($usuario, 'FUSIONARINTERCONSULTA')) {
        return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para fusionar hilos.');
    }
    if (!interconsultaAccesoUsuarioPuedeAccederHilo($origen, $usuario, true, $mysqli)
        || !interconsultaAccesoUsuarioPuedeAccederHilo($destino, $usuario, true, $mysqli)) {
        return array('ok' => false, 'codigo' => 'NI_LOCAL', 'mensaje' => 'Ambos hilos deben estar activos y dentro de sus locales autorizados.');
    }
    $hilos = interconsultaFusionObtenerHilos($mysqli, $origen, $destino, $bloquear);
    if (!isset($hilos[$origen]) || !isset($hilos[$destino])) return array('ok' => false, 'mensaje' => 'No se encontraron ambos hilos.');
    if ($hilos[$origen]['estado'] === 'inactivo' || $hilos[$destino]['estado'] === 'inactivo') {
        return array('ok' => false, 'mensaje' => 'No se pueden fusionar hilos inactivos.');
    }
    $identidades = interconsultaFusionIdentidades($mysqli, $origen, $destino);
    $tipoOrigen = strtolower(trim((string)$hilos[$origen]['tipo']));
    $tipoDestino = strtolower(trim((string)$hilos[$destino]['tipo']));
    $esColaboradorOrigen = $tipoOrigen === 'colaborador' || count($identidades[$origen]['colaboradores']) > 0;
    $esColaboradorDestino = $tipoDestino === 'colaborador' || count($identidades[$destino]['colaboradores']) > 0;
    if ($esColaboradorOrigen || $esColaboradorDestino) {
        if (!$esColaboradorOrigen || !$esColaboradorDestino
            || !interconsultaFusionConjuntosCompatibles($identidades[$origen]['colaboradores'], $identidades[$destino]['colaboradores'])) {
            return array('ok' => false, 'mensaje' => 'Un hilo de colaborador solo puede fusionarse con otro hilo del mismo colaborador.');
        }
    } else {
        if (!interconsultaFusionConjuntosCompatibles($identidades[$origen]['cedulas'], $identidades[$destino]['cedulas'])) {
            return array('ok' => false, 'mensaje' => 'Los hilos pertenecen a pacientes con cedulas diferentes.');
        }
        if (!interconsultaFusionConjuntosCompatibles($identidades[$origen]['clientes'], $identidades[$destino]['clientes'])) {
            return array('ok' => false, 'mensaje' => 'Los hilos pertenecen a pacientes diferentes.');
        }
    }
    return array('ok' => true, 'hilos' => $hilos, 'identidades' => $identidades);
}

function interconsultaFusionContar($mysqli, $tabla, $columna, $origen)
{
    if (!interconsultaFusionTablaExiste($mysqli, $tabla) || !interconsultaFusionColumnaExiste($mysqli, $tabla, $columna)) return 0;
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', $columna);
    $stmt = $mysqli->prepare("SELECT COUNT(*) total FROM `".$tabla."` WHERE `".$columna."`=?");
    if (!$stmt) return 0;
    $stmt->bind_param('i', $origen); $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc(); $stmt->close();
    return $fila ? intval($fila['total']) : 0;
}

function interconsultaFusionResumen($mysqli, $origen)
{
    return array(
        'mensajes' => interconsultaFusionContar($mysqli, 'mensaje', 'cod_interConsultaFK', $origen),
        'dictamenes' => interconsultaFusionContar($mysqli, 'dictamenes', 'cod_interConsultaFK', $origen),
        'gastos' => interconsultaFusionContar($mysqli, 'gastos', 'cod_interConsultaFK', $origen),
        'seguimientos' => interconsultaFusionContar($mysqli, 'interconsulta_seguimiento_programado', 'cod_interConsultaFK', $origen),
        'facturas' => interconsultaFusionContar($mysqli, 'centro_factura', 'cod_interConsultaFK', $origen),
        'ventas_vinculadas' => interconsultaFusionContar($mysqli, 'interconsulta_paciente_venta', 'cod_interConsultaFK', $origen),
        'documentos_pagare' => interconsultaFusionContar($mysqli, 'centro_legajo_pagare_solicitud', 'cod_interConsultaFK', $origen),
        'recetas_indicaciones' => interconsultaFusionContar($mysqli, 'recetarios_indicaciones', 'hilo_id', $origen),
        'proyectos' => interconsultaFusionContar($mysqli, 'interconsulta_proyecto_gasto', 'cod_interConsultaFK', $origen)
    );
}

function interconsultaFusionPrevisualizar($origen, $destino, $usuario)
{
    $mysqli = conectar_al_servidor();
    $validacion = interconsultaFusionValidar($mysqli, intval($origen), intval($destino), intval($usuario), false);
    if (empty($validacion['ok'])) { $mysqli->close(); return $validacion; }
    $resumen = interconsultaFusionResumen($mysqli, intval($origen));
    $hiloOrigen = $validacion['hilos'][intval($origen)];
    $hiloDestino = $validacion['hilos'][intval($destino)];
    $mysqli->close();
    return array('ok' => true, 'origen' => array(
        'cod_interConsulta' => intval($origen), 'asunto' => interconsultaFusionTextoSalida($hiloOrigen['asunto'])
    ), 'destino' => array(
        'cod_interConsulta' => intval($destino), 'asunto' => interconsultaFusionTextoSalida($hiloDestino['asunto'])
    ), 'resumen' => $resumen, 'mensaje' => 'El hilo origen quedara archivado; no se eliminara ningun registro.');
}

function interconsultaFusionMoverSimple($mysqli, $tabla, $columna, $origen, $destino)
{
    if (!interconsultaFusionTablaExiste($mysqli, $tabla) || !interconsultaFusionColumnaExiste($mysqli, $tabla, $columna)) return 0;
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', $columna);
    $stmt = $mysqli->prepare("UPDATE `".$tabla."` SET `".$columna."`=? WHERE `".$columna."`=?");
    if (!$stmt) throw new Exception('No se pudo preparar el traslado de '.$tabla.'.');
    $stmt->bind_param('ii', $destino, $origen);
    if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo trasladar la informacion de '.$tabla.'.'); }
    $total = intval($stmt->affected_rows); $stmt->close();
    return $total;
}

function interconsultaFusionParticipantes($mysqli, $origen, $destino, $hilos)
{
    $usuarios = array();
    foreach (array($origen, $destino) as $hilo) {
        if (isset($hilos[$hilo]) && intval($hilos[$hilo]['cod_usuarioFK_create']) > 0) {
            $usuarios[intval($hilos[$hilo]['cod_usuarioFK_create'])] = 1;
        }
        $stmt = $mysqli->prepare("SELECT mn.cod_usuarioFK FROM menciones mn
            INNER JOIN mensaje m ON m.cod_mensaje=mn.cod_mensajeFK
            WHERE m.cod_mensaje=(SELECT m2.cod_mensaje FROM mensaje m2
                WHERE m2.cod_interConsultaFK=? AND m2.estado='activo' AND m2.fecha_creacion<=NOW()
                ORDER BY m2.fecha_creacion DESC,m2.cod_mensaje DESC LIMIT 1)
              AND mn.estado='activo'");
        if ($stmt) {
            $stmt->bind_param('i', $hilo); $stmt->execute();
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                if (intval($fila['cod_usuarioFK']) > 0) $usuarios[intval($fila['cod_usuarioFK'])] = 1;
            }
            $stmt->close();
        }
    }
    return array_keys($usuarios);
}

function interconsultaFusionVincularVentaDirecta($mysqli, $hiloOrigen, $hiloDestino, $usuario)
{
    $codVenta = intval($hiloOrigen['cod_ventaFK']);
    if ($codVenta <= 0) return;
    if (intval($hiloDestino['cod_ventaFK']) <= 0) {
        $stmt = $mysqli->prepare("UPDATE interconsulta SET cod_ventaFK=? WHERE cod_interConsulta=? AND IFNULL(cod_ventaFK,0)=0");
        if (!$stmt) throw new Exception('No se pudo conservar la venta principal del hilo.');
        $destino = intval($hiloDestino['cod_interConsulta']);
        $stmt->bind_param('ii', $codVenta, $destino);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo conservar la venta principal del hilo.'); }
        $stmt->close();
    }
    if (function_exists('seguimientoPacienteVincularVenta')) {
        $stmt = $mysqli->prepare("SELECT v.cod_venta,v.cod_clienteFK,v.cod_usuarioFK,c.ci_cliente
            FROM venta v LEFT JOIN cliente c ON c.cod_cliente=v.cod_clienteFK WHERE v.cod_venta=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $codVenta); $stmt->execute();
            $venta = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if ($venta) {
                $cedula = function_exists('seguimientoPacienteNormalizarCedula')
                    ? seguimientoPacienteNormalizarCedula($venta['ci_cliente']) : preg_replace('/[^0-9A-Za-z]/', '', (string)$venta['ci_cliente']);
                if (!seguimientoPacienteVincularVenta($mysqli, intval($hiloDestino['cod_interConsulta']), $venta, $cedula, $usuario)) {
                    throw new Exception('No se pudo conservar la venta vinculada al hilo origen.');
                }
            }
        }
    }
}

function interconsultaFusionMoverProyectos($mysqli, $origen, $destino)
{
    if (!interconsultaFusionTablaExiste($mysqli, 'interconsulta_proyecto_gasto')) return 0;
    $stmt = $mysqli->prepare("SELECT id,cod_proyecto_gastoFK,estado FROM interconsulta_proyecto_gasto WHERE cod_interConsultaFK=? FOR UPDATE");
    if (!$stmt) throw new Exception('No se pudieron validar los proyectos asociados.');
    $stmt->bind_param('i', $origen); $stmt->execute();
    $filas = array(); $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $filas[] = $fila;
    $stmt->close(); $movidos = 0;
    foreach ($filas as $fila) {
        $id = intval($fila['id']); $proyecto = intval($fila['cod_proyecto_gastoFK']);
        $stmt = $mysqli->prepare("SELECT id FROM interconsulta_proyecto_gasto WHERE cod_interConsultaFK=? AND cod_proyecto_gastoFK=? LIMIT 1 FOR UPDATE");
        $stmt->bind_param('ii', $destino, $proyecto); $stmt->execute();
        $existente = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($existente) {
            $idDestino = intval($existente['id']);
            $stmt = $mysqli->prepare("UPDATE interconsulta_proyecto_gasto SET estado='activo',fecha_edit=NOW() WHERE id=?");
            $stmt->bind_param('i', $idDestino); $stmt->execute(); $stmt->close();
            $stmt = $mysqli->prepare("UPDATE interconsulta_proyecto_gasto SET estado='inactivo',fecha_edit=NOW() WHERE id=?");
            $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
        } else {
            $stmt = $mysqli->prepare("UPDATE interconsulta_proyecto_gasto SET cod_interConsultaFK=?,fecha_edit=NOW() WHERE id=?");
            $stmt->bind_param('ii', $destino, $id);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo conservar un proyecto asociado.'); }
            $stmt->close(); $movidos++;
        }
    }
    return $movidos;
}

function interconsultaFusionMoverColaborador($mysqli, $origen, $destino, $usuario)
{
    if (!interconsultaFusionTablaExiste($mysqli, 'funcionario_hilo_principal')) return 0;
    $stmt = $mysqli->prepare("SELECT id,cod_usuarioFK FROM funcionario_hilo_principal WHERE cod_interConsultaFK=? AND estado='activo' FOR UPDATE");
    if (!$stmt) throw new Exception('No se pudo validar el hilo del colaborador.');
    $stmt->bind_param('i', $origen); $stmt->execute();
    $filas = array(); $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) $filas[] = $fila;
    $stmt->close(); $movidos = 0;
    foreach ($filas as $fila) {
        $id = intval($fila['id']); $colaborador = intval($fila['cod_usuarioFK']);
        $stmt = $mysqli->prepare("SELECT id FROM funcionario_hilo_principal WHERE cod_interConsultaFK=? AND cod_usuarioFK=? AND estado='activo' LIMIT 1 FOR UPDATE");
        $stmt->bind_param('ii', $destino, $colaborador); $stmt->execute();
        $existente = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($existente) {
            $motivo = 'Archivado por fusion con el hilo #'.$destino;
            $stmt = $mysqli->prepare("UPDATE funcionario_hilo_principal SET estado='inactivo',motivo_cambio=?,cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE id=?");
            $stmt->bind_param('sii', $motivo, $usuario, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE funcionario_hilo_principal SET cod_interConsultaFK=?,cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE id=?");
            $stmt->bind_param('iii', $destino, $usuario, $id); $movidos++;
        }
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo conservar el hilo del colaborador.'); }
        $stmt->close();
    }
    return $movidos;
}

function interconsultaFusionCopiarLecturas($mysqli, $origen, $destino)
{
    if (!interconsultaFusionTablaExiste($mysqli, 'interconsulta_lectura_usuario')) return;
    $stmt = $mysqli->prepare("INSERT INTO interconsulta_lectura_usuario
        (cod_interConsultaFK,cod_usuarioFK,fecha_inicio_conteo,fecha_ultima_apertura,estado)
        SELECT ?,cod_usuarioFK,fecha_inicio_conteo,fecha_ultima_apertura,estado
        FROM interconsulta_lectura_usuario WHERE cod_interConsultaFK=?
        ON DUPLICATE KEY UPDATE
          fecha_inicio_conteo=LEAST(fecha_inicio_conteo,VALUES(fecha_inicio_conteo)),
          fecha_ultima_apertura=CASE
            WHEN fecha_ultima_apertura IS NULL THEN VALUES(fecha_ultima_apertura)
            WHEN VALUES(fecha_ultima_apertura) IS NULL THEN fecha_ultima_apertura
            ELSE GREATEST(fecha_ultima_apertura,VALUES(fecha_ultima_apertura)) END,
          estado=IF(estado='activo' OR VALUES(estado)='activo','activo','inactivo')");
    if (!$stmt) throw new Exception('No se pudieron preparar los contadores de lectura.');
    $stmt->bind_param('ii', $destino, $origen);
    if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron conservar los contadores de lectura.'); }
    $stmt->close();
    if (interconsultaFusionTablaExiste($mysqli, 'interconsulta_mensaje_lectura')) {
        interconsultaFusionMoverSimple($mysqli, 'interconsulta_mensaje_lectura', 'cod_interConsultaFK', $origen, $destino);
    }
}

function interconsultaFusionEjecutarEnConexion($mysqli, $origen, $destino, $usuario)
{
    $origen = intval($origen); $destino = intval($destino); $usuario = intval($usuario);
    if (!($mysqli instanceof mysqli)) {
        throw new Exception('No se pudo abrir la conexion de fusion.');
    }
    if (!interconsultaFusionTablaExiste($mysqli, 'interconsulta_fusion')) {
        throw new Exception('Falta instalar la migracion de fusion segura.');
    }
    $validacion = interconsultaFusionValidar($mysqli, $origen, $destino, $usuario, true);
        if (empty($validacion['ok'])) throw new Exception(isset($validacion['mensaje']) ? $validacion['mensaje'] : 'No se pudo validar la fusion.');
        $stmt = $mysqli->prepare("SELECT id_fusion FROM interconsulta_fusion WHERE cod_interConsulta_origenFK=? LIMIT 1 FOR UPDATE");
        $stmt->bind_param('i', $origen); $stmt->execute();
        $fusionAnterior = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($fusionAnterior) throw new Exception('El hilo origen ya fue fusionado anteriormente.');

        $hilos = $validacion['hilos'];
        $participantes = interconsultaFusionParticipantes($mysqli, $origen, $destino, $hilos);
        $resumenAntes = interconsultaFusionResumen($mysqli, $origen);
        interconsultaFusionVincularVentaDirecta($mysqli, $hilos[$origen], $hilos[$destino], $usuario);

        $movidos = array();
        $movidos['mensajes'] = interconsultaFusionMoverSimple($mysqli, 'mensaje', 'cod_interConsultaFK', $origen, $destino);
        $movidos['dictamenes'] = interconsultaFusionMoverSimple($mysqli, 'dictamenes', 'cod_interConsultaFK', $origen, $destino);
        $movidos['gastos'] = interconsultaFusionMoverSimple($mysqli, 'gastos', 'cod_interConsultaFK', $origen, $destino);
        $movidos['seguimientos'] = interconsultaFusionMoverSimple($mysqli, 'interconsulta_seguimiento_programado', 'cod_interConsultaFK', $origen, $destino);
        $movidos['paciente'] = interconsultaFusionMoverSimple($mysqli, 'interconsulta_paciente', 'cod_interConsultaFK', $origen, $destino);
        $movidos['ventas_vinculadas'] = interconsultaFusionMoverSimple($mysqli, 'interconsulta_paciente_venta', 'cod_interConsultaFK', $origen, $destino);
        $movidos['documentos_pagare'] = interconsultaFusionMoverSimple($mysqli, 'centro_legajo_pagare_solicitud', 'cod_interConsultaFK', $origen, $destino);
        $movidos['recetas_indicaciones'] = interconsultaFusionMoverSimple($mysqli, 'recetarios_indicaciones', 'hilo_id', $origen, $destino);
        $movidos['proyectos'] = interconsultaFusionMoverProyectos($mysqli, $origen, $destino);
        $movidos['colaborador'] = interconsultaFusionMoverColaborador($mysqli, $origen, $destino, $usuario);
        interconsultaFusionCopiarLecturas($mysqli, $origen, $destino);

        if (interconsultaFusionTablaExiste($mysqli, 'centro_factura')) {
            $stmt = $mysqli->prepare("UPDATE centro_factura SET cod_interConsultaFK=?,cod_usuario_actualizacionFK=?,
                fecha_actualizacion=NOW(),version_registro=version_registro+1 WHERE cod_interConsultaFK=?");
            if (!$stmt) throw new Exception('No se pudieron preparar las facturas relacionadas.');
            $stmt->bind_param('iii', $destino, $usuario, $origen);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron conservar las facturas relacionadas.'); }
            $movidos['facturas'] = intval($stmt->affected_rows); $stmt->close();
        }

        $fechaFusion = date('Y-m-d H:i:s');
        $asuntoOrigen = trim((string)$hilos[$origen]['asunto']);
        $contenido = 'Fusion segura: se incorporo el hilo #'.$origen.($asuntoOrigen !== '' ? ' ('.$asuntoOrigen.')' : '').
            ' a este hilo maestro. La operacion fue realizada por @{'.$usuario.'}; se conservaron los registros y su orden por fecha y hora de guardado.';
        $usuarioSistema = null; $dictamenSistema = null;
        $stmt = $mysqli->prepare("INSERT INTO mensaje
            (contenido,fecha_creacion,cod_interConsultaFK,cod_usuarioFK,cod_dictamenFK) VALUES (?,?,?,?,?)");
        if (!$stmt) throw new Exception('No se pudo preparar el mensaje de trazabilidad.');
        $stmt->bind_param('ssiii', $contenido, $fechaFusion, $destino, $usuarioSistema, $dictamenSistema);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo registrar la fusion en el timeline.'); }
        $mensajeFusion = intval($stmt->insert_id); $stmt->close();

        $stmt = $mysqli->prepare("INSERT INTO menciones (cod_usuarioFK,cod_mensajeFK,isLeido,estado)
            VALUES (?,?,0,'activo') ON DUPLICATE KEY UPDATE estado='activo'");
        if (!$stmt) throw new Exception('No se pudieron conservar los participantes de ambos hilos.');
        foreach ($participantes as $participante) {
            $participante = intval($participante);
            if ($participante <= 0) continue;
            $stmt->bind_param('ii', $participante, $mensajeFusion);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron conservar todos los participantes.'); }
        }
        $stmt->close();

        if (function_exists('interconsultaLecturasAsegurarUsuariosHilo')) {
            interconsultaLecturasAsegurarUsuariosHilo($mysqli, $destino, $participantes, $fechaFusion);
        }
        $resumenAuditoria = json_encode(array('antes' => $resumenAntes, 'movidos' => $movidos, 'mensaje_fusion' => $mensajeFusion));
        if ($resumenAuditoria === false) $resumenAuditoria = '{}';
        $stmt = $mysqli->prepare("INSERT INTO interconsulta_fusion
            (cod_interConsulta_origenFK,cod_interConsulta_destinoFK,cod_usuarioFK,fecha_fusion,resumen_movimientos,estado)
            VALUES (?,?,?,?,?,'aplicada')");
        if (!$stmt) throw new Exception('No se pudo preparar la auditoria de la fusion.');
        $stmt->bind_param('iiiss', $origen, $destino, $usuario, $fechaFusion, $resumenAuditoria);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo auditar la fusion.'); }
        $idFusion = intval($stmt->insert_id); $stmt->close();

        $stmt = $mysqli->prepare("UPDATE interconsulta SET estado='inactivo',cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE cod_interConsulta=? AND estado<>'inactivo'");
        $stmt->bind_param('ii', $usuario, $origen);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo archivar el hilo origen.'); }
        $stmt->close();
        $stmt = $mysqli->prepare("UPDATE interconsulta SET cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE cod_interConsulta=?");
        $stmt->bind_param('ii', $usuario, $destino);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo actualizar el hilo maestro.'); }
        $stmt->close();

    return array('1' => 'exito', '2' => $origen, '3' => $destino, '4' => array(
        'id_fusion' => $idFusion, 'movidos' => $movidos, 'mensaje_fusion' => $mensajeFusion
    ));
}

function interconsultaFusionEjecutar($origen, $destino, $usuario)
{
    $mysqli = conectar_al_servidor();
    if (!interconsultaFusionTablaExiste($mysqli, 'interconsulta_fusion')) {
        $mysqli->close();
        return array('1' => 'error', '2' => 'Falta instalar la migracion de fusion segura.');
    }
    $mysqli->begin_transaction();
    try {
        $resultado = interconsultaFusionEjecutarEnConexion($mysqli, $origen, $destino, $usuario);
        $mysqli->commit();
        $mysqli->close();
        return $resultado;
    } catch (Exception $e) {
        $mysqli->rollback(); $mysqli->close();
        return array('1' => 'error', '2' => $e->getMessage());
    }
}

?>

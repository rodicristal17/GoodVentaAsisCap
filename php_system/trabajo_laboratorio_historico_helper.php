<?php

/**
 * Convalidacion e integracion de trabajos mecanicos historicos.
 *
 * Requiere que trabajo_laboratorio_helper.php ya se encuentre cargado. Este
 * archivo no modifica trabajo_mecanico_dental: la fuente legacy se conserva
 * como evidencia de solo lectura y todas las decisiones nuevas son trazables.
 *
 * Compatible con PHP 7.2.
 */

function trabajoLaboratorioHistoricoEstructuraDisponible($mysqli)
{
    return trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_historico')
        && trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_historico_evento')
        && trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio')
        && trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_ciclo')
        && trabajoLaboratorioTablaExiste($mysqli, 'trabajo_laboratorio_evento')
        && trabajoLaboratorioColumnaExiste(
            $mysqli,
            'trabajo_laboratorio_historico',
            'observacion_snapshot'
        )
        && trabajoLaboratorioColumnaExiste(
            $mysqli,
            'trabajo_laboratorio_historico',
            'colorimetro_snapshot'
        )
        && trabajoLaboratorioColumnaExiste(
            $mysqli,
            'trabajo_laboratorio_historico',
            'costo_snapshot'
        );
}

function trabajoLaboratorioHistoricoExigirEstructura($mysqli)
{
    if (!trabajoLaboratorioHistoricoEstructuraDisponible($mysqli)) {
        trabajoLaboratorioLanzar(
            'estructura_historica_no_instalada',
            'La estructura de convalidacion historica de laboratorio todavia no esta instalada.'
        );
    }
}

function trabajoLaboratorioHistoricoExigirAuditor($mysqli, $codUsuario)
{
    if (!trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario))) {
        trabajoLaboratorioLanzar(
            'auditoria_historica_no_autorizada',
            'El usuario no posee permiso para convalidar trabajos historicos de laboratorio.'
        );
    }
}

function trabajoLaboratorioHistoricoExigirUsuarioActivo($mysqli, $codUsuario)
{
    $usuario = trabajoLaboratorioUsuario($mysqli, intval($codUsuario));
    if (!$usuario) {
        trabajoLaboratorioLanzar(
            'usuario_inactivo',
            'El usuario no se encuentra activo.'
        );
    }
    if (!trabajoLaboratorioTienePermiso(
        $mysqli,
        intval($codUsuario),
        'VERTRABAJOSLABORATORIO'
    ) && !trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario))) {
        trabajoLaboratorioLanzar(
            'historico_no_autorizado',
            'El usuario no puede acceder a trabajos historicos de laboratorio.'
        );
    }
    return $usuario;
}

function trabajoLaboratorioHistoricoEstadosDeclarables()
{
    /* Los estados de traslado se excluyen expresamente: sin una recepcion
       confirmada no es posible reconstruir una transferencia historica. */
    return array(
        array(
            'codigo' => 'pendiente_entrega_mecanico',
            'nombre' => 'Pendiente de entrega al mecanico',
            'custodia_esperada' => 'clinica',
            'final' => false
        ),
        array(
            'codigo' => 'en_laboratorio',
            'nombre' => 'En poder del mecanico',
            'custodia_esperada' => 'mecanico',
            'final' => false
        ),
        array(
            'codigo' => 'pendiente_revision',
            'nombre' => 'Pendiente de revision clinica',
            'custodia_esperada' => 'clinica',
            'final' => false
        ),
        array(
            'codigo' => 'ajuste_solicitado',
            'nombre' => 'Ajuste solicitado',
            'custodia_esperada' => 'clinica',
            'final' => false
        ),
        array(
            'codigo' => 'listo_instalacion',
            'nombre' => 'Listo para instalar',
            'custodia_esperada' => 'clinica',
            'final' => false
        ),
        array(
            'codigo' => 'instalado',
            'nombre' => 'Instalado y entregado',
            'custodia_esperada' => 'clinica',
            'final' => true
        ),
        array(
            'codigo' => 'cancelado',
            'nombre' => 'Cancelado',
            'custodia_esperada' => 'clinica',
            'final' => true
        )
    );
}

function trabajoLaboratorioHistoricoCodigosEstadosDeclarables()
{
    $codigos = array();
    foreach (trabajoLaboratorioHistoricoEstadosDeclarables() as $estado) {
        $codigos[] = $estado['codigo'];
    }
    return $codigos;
}

function trabajoLaboratorioHistoricoEstadoEsFinal($estado)
{
    return in_array((string)$estado, array('instalado', 'cancelado'), true);
}

function trabajoLaboratorioHistoricoEstadoEsTransitorio($estado)
{
    return in_array(
        (string)$estado,
        array('en_transferencia_mecanico', 'en_transferencia_clinica'),
        true
    );
}

function trabajoLaboratorioHistoricoEtiquetaEstado($estado)
{
    $etiquetas = array(
        'pendiente_entrega_mecanico' => 'Pendiente de entrega al mecanico',
        'en_laboratorio' => 'En poder del mecanico',
        'pendiente_revision' => 'Pendiente de revision clinica',
        'ajuste_solicitado' => 'Ajuste solicitado',
        'listo_instalacion' => 'Listo para instalar',
        'instalado' => 'Instalado y entregado',
        'cancelado' => 'Cancelado'
    );
    return isset($etiquetas[$estado]) ? $etiquetas[$estado] : null;
}

function trabajoLaboratorioHistoricoIdEntrada($entrada)
{
    if (!is_array($entrada)) {
        return 0;
    }
    foreach (array('id_historico', 'id_trabajo_historico', 'id') as $clave) {
        if (isset($entrada[$clave])) {
            return trabajoLaboratorioEntero($entrada[$clave]);
        }
    }
    return 0;
}

function trabajoLaboratorioHistoricoVersionEntrada($entrada)
{
    if (!is_array($entrada)) {
        return 0;
    }
    if (isset($entrada['version_esperada'])) {
        return trabajoLaboratorioEntero($entrada['version_esperada']);
    }
    if (isset($entrada['version'])) {
        return trabajoLaboratorioEntero($entrada['version']);
    }
    return 0;
}

function trabajoLaboratorioHistoricoJustificacionEntrada($entrada)
{
    $justificacion = trabajoLaboratorioTextoEntrada(
        isset($entrada['justificacion']) ? $entrada['justificacion']
            : (isset($entrada['motivo']) ? $entrada['motivo'] : ''),
        750
    );
    if (strlen($justificacion) < 5) {
        trabajoLaboratorioLanzar(
            'justificacion_historica_requerida',
            'Explique el motivo de la convalidacion o rectificacion con al menos cinco caracteres.'
        );
    }
    return $justificacion;
}

function trabajoLaboratorioHistoricoFechaEntrada($valor, $nombreCampo)
{
    $texto = trabajoLaboratorioTextoEntrada($valor, 19);
    if ($texto === '') {
        return null;
    }
    $marca = trabajoLaboratorioTimestampSistema($texto);
    if ($marca === false) {
        trabajoLaboratorioLanzar(
            'fecha_historica_invalida',
            'La fecha indicada para '.$nombreCampo.' no es valida.'
        );
    }
    return date('Y-m-d H:i:s', $marca);
}

function trabajoLaboratorioHistoricoValorEntrada($entrada, $claves, $actual, &$fueEnviado)
{
    $fueEnviado = false;
    foreach ($claves as $clave) {
        if (array_key_exists($clave, $entrada)) {
            $fueEnviado = true;
            return $entrada[$clave];
        }
    }
    return $actual;
}

function trabajoLaboratorioHistoricoObtenerFila($mysqli, $idHistorico, $bloquear = false)
{
    $idHistorico = intval($idHistorico);
    if ($idHistorico <= 0) {
        return null;
    }
    $sql = 'SELECT * FROM trabajo_laboratorio_historico WHERE id=? LIMIT 1';
    if ($bloquear) {
        $sql .= ' FOR UPDATE';
    }
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'historico_no_disponible',
            'No se pudo consultar el trabajo historico.'
        );
    }
    $stmt->bind_param('i', $idHistorico);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'historico_no_disponible',
            'No se pudo consultar el trabajo historico.'
        );
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ?: null;
}

function trabajoLaboratorioHistoricoSqlDecoradoSelect()
{
    return 'SELECT h.*,'
        .'h.observacion_snapshot AS observacion_legacy,'
        .'h.colorimetro_snapshot AS colorimetro_legacy,'
        .'h.costo_snapshot AS costo_legacy,tmd.estado AS estado_legacy_actual,'
        .'v.cod_clienteFK AS cod_cliente_venta,v.cod_local AS cod_local_venta,'
        .'v.num_factura,v.puntoexpedicion,v.estado AS estado_venta,'
        .'pc.nombre_persona AS nombre_paciente,cl.ci_cliente,'
        .'tts.descripcion AS tipo_trabajo_snapshot,'
        .'pms.nombre_persona AS mecanico_snapshot,ums.tipo AS mecanico_snapshot_rol,'
        .'ums.url AS mecanico_snapshot_avatar,pmd.nombre_persona AS mecanico_declarado,'
        .'utf.tipo AS mecanico_declarado_rol,utf.url AS mecanico_declarado_avatar,'
        .'mds.estado AS estado_mecanico_snapshot,mdd.estado AS estado_mecanico_declarado,'
        .'mdd.cod_usuarioFK AS cod_tecnico_formal_actual,'
        .'utf.estado AS estado_tecnico_formal_actual,ptf.nombre_persona AS tecnico_formal_actual,'
        .'pt.nombre_persona AS tecnico_guardado,ut.estado AS estado_tecnico_guardado,'
        .'pcu.nombre_persona AS custodio_actual,ucu.estado AS estado_custodio_actual,'
        .'ucu.cod_localFK AS cod_local_custodio_actual,'
        .'ls.Nombre AS local_snapshot,ls.estado AS estado_local_snapshot,'
        .'ld.Nombre AS local_declarado,ld.estado AS estado_local_declarado,'
        .'pesp.nombre_persona AS especialista_snapshot,uesp.tipo AS especialista_snapshot_rol,'
        .'uesp.url AS especialista_snapshot_avatar,pcrea.nombre_persona AS usuario_creador_original,'
        .'ucrea.tipo AS usuario_creador_rol_original,ucrea.url AS usuario_creador_avatar_original,'
        .'pedit.nombre_persona AS usuario_editor_original,'
        .'pconv.nombre_persona AS usuario_convalida,'
        .'pupd.nombre_persona AS usuario_actualiza,'
        .'dv.cod_productoFK AS cod_producto_detalle,dv.estado AS estado_detalle,'
        .'dv.estado_tratamiento,dv.nroventa,dv.cantidad_detalle,'
        .'prod.nombre_producto,prod.estado AS estado_producto,'
        .'tl.codigo_visible,tl.estado_derivado AS estado_operativo,tl.version AS version_operativa,'
        .'tl.id_transferencia_pendienteFK AS id_transferencia_operativa_pendiente,'
        .'(SELECT ta.id FROM trabajo_laboratorio ta '
        .'WHERE ta.cod_detalle_activo_unico=h.cod_detalle_ventaFK LIMIT 1) AS id_trabajo_detalle_activo,'
        .'(SELECT COUNT(*) FROM detalle_venta dc WHERE dc.cod_ventaFK=h.cod_venta_snapshot) AS total_candidatos_detalle ';
}

function trabajoLaboratorioHistoricoSqlDecoradoDesde()
{
    return 'FROM trabajo_laboratorio_historico h '
        .'LEFT JOIN trabajo_mecanico_dental tmd '
        .'ON tmd.cod_trabajo_mecanico_dental=h.cod_trabajo_mecanico_legacyFK '
        .'LEFT JOIN venta v ON v.cod_venta=h.cod_venta_snapshot '
        .'LEFT JOIN cliente cl ON cl.cod_cliente=h.cod_cliente_snapshot '
        .'LEFT JOIN persona pc ON pc.cod_persona=h.cod_cliente_snapshot '
        .'LEFT JOIN tipo_trabajo_mecanico_dental tts '
        .'ON tts.cod_tipo_trabajo_mecanico_dental=h.cod_tipo_trabajo_snapshot '
        .'LEFT JOIN mecanico_dental mds '
        .'ON mds.cod_mecanico_dental=h.cod_mecanico_dental_snapshot '
        .'LEFT JOIN usuario ums ON ums.cod_usuario=mds.cod_usuarioFK '
        .'LEFT JOIN persona pms ON pms.cod_persona=mds.cod_personaFK '
        .'LEFT JOIN mecanico_dental mdd '
        .'ON mdd.cod_mecanico_dental=h.cod_mecanico_dental_declaradoFK '
        .'LEFT JOIN persona pmd ON pmd.cod_persona=mdd.cod_personaFK '
        .'LEFT JOIN usuario utf ON utf.cod_usuario=mdd.cod_usuarioFK '
        .'LEFT JOIN persona ptf ON ptf.cod_persona=utf.cod_usuario '
        .'LEFT JOIN usuario ut ON ut.cod_usuario=h.cod_tecnico_usuarioFK '
        .'LEFT JOIN persona pt ON pt.cod_persona=ut.cod_usuario '
        .'LEFT JOIN usuario ucu ON ucu.cod_usuario=h.cod_custodio_actualFK '
        .'LEFT JOIN persona pcu ON pcu.cod_persona=ucu.cod_usuario '
        .'LEFT JOIN local ls ON ls.cod_local=h.cod_local_snapshot '
        .'LEFT JOIN local ld ON ld.cod_local=h.cod_local_declaradoFK '
        .'LEFT JOIN usuario uesp ON uesp.cod_usuario=h.cod_especialista_snapshot '
        .'LEFT JOIN persona pesp ON pesp.cod_persona=h.cod_especialista_snapshot '
        .'LEFT JOIN usuario ucrea ON ucrea.cod_usuario=h.cod_usuario_creador_snapshot '
        .'LEFT JOIN persona pcrea ON pcrea.cod_persona=h.cod_usuario_creador_snapshot '
        .'LEFT JOIN persona pedit ON pedit.cod_persona=h.cod_usuario_editor_snapshot '
        .'LEFT JOIN persona pconv ON pconv.cod_persona=h.cod_usuarioFK_convalida '
        .'LEFT JOIN persona pupd ON pupd.cod_persona=h.cod_usuarioFK_update '
        .'LEFT JOIN detalle_venta dv ON dv.cod_detalle=h.cod_detalle_ventaFK '
        .'LEFT JOIN producto prod ON prod.cod_producto=dv.cod_productoFK '
        .'LEFT JOIN trabajo_laboratorio tl ON tl.id=h.id_trabajo_laboratorioFK ';
}

function trabajoLaboratorioHistoricoObtenerFilaDecorada($mysqli, $idHistorico)
{
    $idHistorico = intval($idHistorico);
    $sql = trabajoLaboratorioHistoricoSqlDecoradoSelect()
        .trabajoLaboratorioHistoricoSqlDecoradoDesde()
        .'WHERE h.id=? LIMIT 1';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('historico_no_disponible', 'No se pudo preparar el detalle historico.');
    }
    $stmt->bind_param('i', $idHistorico);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('historico_no_disponible', 'No se pudo consultar el detalle historico.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ?: null;
}

function trabajoLaboratorioHistoricoPuedeVer($mysqli, $codUsuario, $historico)
{
    return $historico
        && trabajoLaboratorioUsuario($mysqli, intval($codUsuario))
        && (
            trabajoLaboratorioTienePermiso(
                $mysqli,
                intval($codUsuario),
                'VERTRABAJOSLABORATORIO'
            )
            || trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario))
        );
}

function trabajoLaboratorioHistoricoExigirAcceso($mysqli, $codUsuario, $historico)
{
    if (!$historico) {
        trabajoLaboratorioLanzar('historico_no_encontrado', 'No se encontro el trabajo historico solicitado.');
    }
    if (!trabajoLaboratorioHistoricoPuedeVer($mysqli, $codUsuario, $historico)) {
        trabajoLaboratorioLanzar(
            'historico_no_autorizado',
            'El usuario no puede acceder a este trabajo historico.'
        );
    }
}

function trabajoLaboratorioHistoricoCondicionAcceso($mysqli, $codUsuario, $alias, &$tipos, &$valores)
{
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    if ($alias === '') {
        $alias = 'h';
    }
    return trabajoLaboratorioUsuario($mysqli, intval($codUsuario))
        && (
            trabajoLaboratorioTienePermiso(
                $mysqli,
                intval($codUsuario),
                'VERTRABAJOSLABORATORIO'
            )
            || trabajoLaboratorioUsuarioEsAuditor($mysqli, intval($codUsuario))
        ) ? '1=1' : '0=1';
}

function trabajoLaboratorioHistoricoNormalizarEnteroNullable($valor)
{
    if ($valor === null || trim((string)$valor) === '') {
        return null;
    }
    $numero = trabajoLaboratorioEntero($valor);
    return $numero > 0 ? $numero : null;
}

function trabajoLaboratorioHistoricoPendientesBasicos($mysqli, $historico)
{
    $pendientes = array();
    if (!$historico) {
        return $pendientes;
    }
    if (intval($historico['id_trabajo_laboratorioFK']) > 0
        || (string)$historico['estado_convalidacion'] === 'integrado_operativo') {
        return $pendientes;
    }
    $estado = isset($historico['estado_declarado']) ? (string)$historico['estado_declarado'] : '';
    if (!in_array($estado, trabajoLaboratorioHistoricoCodigosEstadosDeclarables(), true)) {
        $pendientes[] = array(
            'codigo' => 'situacion_por_actualizar',
            'mensaje' => 'Defina la situacion real y estable del trabajo.'
        );
    }
    if (intval($historico['cod_detalle_ventaFK']) <= 0) {
        $pendientes[] = array(
            'codigo' => 'tratamiento_exacto_pendiente',
            'mensaje' => 'Seleccione el tratamiento exacto de la venta.'
        );
    } elseif (array_key_exists('cod_producto_detalle', $historico)
        && trim((string)$historico['cod_producto_detalle']) === '') {
        $pendientes[] = array(
            'codigo' => 'tratamiento_no_disponible',
            'mensaje' => 'El tratamiento seleccionado ya no esta disponible.'
        );
    }
    if (!trabajoLaboratorioHistoricoEstadoEsFinal($estado)
        && isset($historico['id_trabajo_detalle_activo'])
        && intval($historico['id_trabajo_detalle_activo']) > 0
        && intval($historico['id_trabajo_detalle_activo']) !== intval($historico['id_trabajo_laboratorioFK'])) {
        $pendientes[] = array(
            'codigo' => 'tratamiento_ocupado',
            'mensaje' => 'El tratamiento seleccionado ya posee un trabajo operativo activo.'
        );
    }
    if (intval($historico['cod_mecanico_dental_declaradoFK']) <= 0) {
        $pendientes[] = array(
            'codigo' => 'mecanico_pendiente',
            'mensaje' => 'Declare el mecanico dental historico.'
        );
    } elseif (isset($historico['estado_mecanico_declarado'])
        && trabajoLaboratorioNormalizarTexto($historico['estado_mecanico_declarado']) !== 'activo') {
        $pendientes[] = array(
            'codigo' => 'mecanico_inactivo',
            'mensaje' => 'El mecanico declarado debe estar activo para integrar el seguimiento.'
        );
    }
    $codTecnicoActual = isset($historico['cod_tecnico_formal_actual'])
        ? intval($historico['cod_tecnico_formal_actual'])
        : intval($historico['cod_tecnico_usuarioFK']);
    if ($codTecnicoActual <= 0
        || (isset($historico['estado_tecnico_formal_actual'])
            && (string)$historico['estado_tecnico_formal_actual'] !== 'Activo')) {
        $pendientes[] = array(
            'codigo' => 'cuenta_mecanico_pendiente',
            'mensaje' => 'Vincule al mecanico con una cuenta Telar activa.'
        );
    } else {
        $permisosTecnico = array(
            'VERTRABAJOSLABORATORIO',
            'RECIBIRTRABAJOLABORATORIO',
            'ENTREGARTRABAJOLABORATORIO'
        );
        foreach ($permisosTecnico as $permiso) {
            if (!trabajoLaboratorioTienePermiso($mysqli, $codTecnicoActual, $permiso)) {
                $pendientes[] = array(
                    'codigo' => 'permisos_mecanico_pendientes',
                    'mensaje' => 'La cuenta del mecanico necesita permisos de acceso, recepcion y entrega.'
                );
                break;
            }
        }
    }
    if (intval($historico['cod_local_declaradoFK']) <= 0
        || (isset($historico['local_declarado']) && $historico['local_declarado'] === null)) {
        $pendientes[] = array(
            'codigo' => 'local_pendiente',
            'mensaje' => 'Seleccione la sucursal responsable.'
        );
    } elseif (isset($historico['estado_local_declarado'])
        && trabajoLaboratorioNormalizarTexto($historico['estado_local_declarado']) === 'inactivo') {
        $pendientes[] = array(
            'codigo' => 'local_inactivo',
            'mensaje' => 'Seleccione una sucursal activa para el seguimiento operativo.'
        );
    }
    $codCustodio = intval($historico['cod_custodio_actualFK']);
    if ($codCustodio <= 0) {
        $pendientes[] = array(
            'codigo' => 'custodio_pendiente',
            'mensaje' => 'Declare quien posee o resguarda actualmente el trabajo.'
        );
    } elseif (isset($historico['estado_custodio_actual'])
        && (string)$historico['estado_custodio_actual'] !== 'Activo') {
        $pendientes[] = array(
            'codigo' => 'custodio_inactivo',
            'mensaje' => 'El custodio declarado no tiene una cuenta activa.'
        );
    } elseif (!trabajoLaboratorioTienePermiso(
        $mysqli,
        $codCustodio,
        'VERTRABAJOSLABORATORIO'
    ) || !trabajoLaboratorioTienePermiso(
        $mysqli,
        $codCustodio,
        'RECIBIRTRABAJOLABORATORIO'
    )) {
        $pendientes[] = array(
            'codigo' => 'permisos_custodio_pendientes',
            'mensaje' => 'El custodio necesita permisos para ver y recibir trabajos de laboratorio.'
        );
    } elseif ($estado === 'en_laboratorio' && $codTecnicoActual > 0 && $codCustodio !== $codTecnicoActual) {
        $pendientes[] = array(
            'codigo' => 'custodio_mecanico_inconsistente',
            'mensaje' => 'Un trabajo en laboratorio debe quedar bajo custodia del mecanico declarado.'
        );
    } elseif ($estado !== '' && $estado !== 'en_laboratorio'
        && isset($historico['cod_local_custodio_actual'])
        && intval($historico['cod_local_custodio_actual']) !== intval($historico['cod_local_declaradoFK'])) {
        $pendientes[] = array(
            'codigo' => 'custodio_local_inconsistente',
            'mensaje' => 'El custodio de clinica debe pertenecer a la sucursal declarada.'
        );
    }
    if (trim((string)$historico['fecha_objetivo']) === '') {
        $pendientes[] = array(
            'codigo' => 'fecha_objetivo_pendiente',
            'mensaje' => 'Defina una fecha objetivo para integrar el seguimiento operativo.'
        );
    }
    if (trim((string)$historico['fecha_creacion_snapshot']) === '') {
        $pendientes[] = array(
            'codigo' => 'fecha_original_pendiente',
            'mensaje' => 'No se pudo comprobar la fecha de creacion original.'
        );
    }
    if (trim((string)$historico['fecha_situacion_declarada']) === '') {
        $pendientes[] = array(
            'codigo' => 'fecha_situacion_pendiente',
            'mensaje' => 'Indique desde cuando corresponde la situacion declarada.'
        );
    }
    return $pendientes;
}

function trabajoLaboratorioHistoricoPresentacionEstadoOriginal($estado)
{
    $estadoNormalizado = trabajoLaboratorioNormalizarTexto($estado);
    $mapa = array(
        'pendiente' => array(
            'nombre' => 'Pendiente en el registro original',
            'semantica' => 'pendiente',
            'color' => 'amarillo'
        ),
        'pagado' => array(
            'nombre' => 'Pagado en el registro original',
            'semantica' => 'pendiente',
            'color' => 'amarillo'
        ),
        'retirado' => array(
            'nombre' => 'Retirado por el mecanico (registro original)',
            'semantica' => 'en_proceso',
            'color' => 'azul'
        ),
        'entregado' => array(
            'nombre' => 'Entregado a la clinica (registro original)',
            'semantica' => 'revision',
            'color' => 'naranja'
        ),
        'inactivo' => array(
            'nombre' => 'Inactivo en el registro original',
            'semantica' => 'inactivo',
            'color' => 'gris'
        )
    );
    if (isset($mapa[$estadoNormalizado])) {
        return $mapa[$estadoNormalizado];
    }
    return array(
        'nombre' => $estadoNormalizado !== ''
            ? ucfirst(str_replace('_', ' ', $estadoNormalizado)).' (registro original)'
            : 'Situacion original sin definir',
        'semantica' => 'sin_definir',
        'color' => 'gris'
    );
}

function trabajoLaboratorioHistoricoEtiquetaEventoRecorrido($tipoEvento)
{
    $mapa = array(
        'convalidacion_administrativa' => 'Situacion convalidada por Administracion',
        'rectificacion_administrativa' => 'Situacion rectificada por Administracion',
        'promovido_operativo' => 'Registro integrado al seguimiento operativo'
    );
    $tipoEvento = (string)$tipoEvento;
    return isset($mapa[$tipoEvento])
        ? $mapa[$tipoEvento] : ucfirst(str_replace('_', ' ', $tipoEvento));
}

function trabajoLaboratorioHistoricoPresentacionEventoRecorrido($fila)
{
    $estadoDeclarado = isset($fila['estado_declarado_nuevo'])
        ? trim((string)$fila['estado_declarado_nuevo']) : '';
    if ($estadoDeclarado !== '') {
        return array(
            'estado' => $estadoDeclarado,
            'presentacion' => trabajoLaboratorioPresentacionEstadoRecorrido($estadoDeclarado)
        );
    }
    $estadoConvalidacion = isset($fila['estado_convalidacion_nuevo'])
        ? trim((string)$fila['estado_convalidacion_nuevo']) : '';
    $tipoEvento = isset($fila['tipo_evento']) ? (string)$fila['tipo_evento'] : '';
    if ($tipoEvento === 'promovido_operativo' || $estadoConvalidacion === 'integrado_operativo') {
        return array(
            'estado' => $estadoConvalidacion !== '' ? $estadoConvalidacion : 'integrado_operativo',
            'presentacion' => array(
                'nombre' => 'Integrado al seguimiento operativo',
                'semantica' => 'listo',
                'color' => 'turquesa'
            )
        );
    }
    if ($estadoConvalidacion === 'convalidado_administracion') {
        return array(
            'estado' => $estadoConvalidacion,
            'presentacion' => array(
                'nombre' => 'Convalidado por Administracion',
                'semantica' => 'revision',
                'color' => 'naranja'
            )
        );
    }
    return array(
        'estado' => $estadoConvalidacion !== '' ? $estadoConvalidacion : null,
        'presentacion' => array(
            'nombre' => 'Situacion por actualizar',
            'semantica' => 'pendiente',
            'color' => 'amarillo'
        )
    );
}

function trabajoLaboratorioHistoricoNodoOriginal($fila)
{
    $idHistorico = isset($fila['id']) ? intval($fila['id']) : 0;
    $presentacionOriginal = trabajoLaboratorioHistoricoPresentacionEstadoOriginal(
        isset($fila['estado_legacy_snapshot']) ? $fila['estado_legacy_snapshot'] : ''
    );
    $estadoOriginal = trabajoLaboratorioNormalizarTexto(
        isset($fila['estado_legacy_snapshot']) ? $fila['estado_legacy_snapshot'] : ''
    );
    $fechaOriginal = isset($fila['fecha_creacion_snapshot'])
        && trim((string)$fila['fecha_creacion_snapshot']) !== ''
        ? $fila['fecha_creacion_snapshot'] : null;
    return array(
        'id' => 'original-'.$idHistorico,
        'id_evento' => null,
        'id_historico' => $idHistorico,
        'id_trabajo' => isset($fila['id_trabajo_laboratorioFK'])
            ? intval($fila['id_trabajo_laboratorioFK']) : null,
        'origen' => 'historico',
        'tipo_evento' => 'registro_original',
        'titulo' => 'Registro original',
        'fecha_servidor' => $fechaOriginal,
        'actor' => trabajoLaboratorioPersonaRecorrido(
            isset($fila['cod_usuario_creador_snapshot'])
                ? $fila['cod_usuario_creador_snapshot'] : null,
            isset($fila['usuario_creador_original']) ? $fila['usuario_creador_original'] : null,
            isset($fila['usuario_creador_rol_original']) ? $fila['usuario_creador_rol_original'] : null,
            isset($fila['usuario_creador_avatar_original']) ? $fila['usuario_creador_avatar_original'] : null
        ),
        'cod_local' => isset($fila['cod_local_snapshot']) && intval($fila['cod_local_snapshot']) > 0
            ? intval($fila['cod_local_snapshot']) : null,
        'local' => isset($fila['local_snapshot'])
            ? trabajoLaboratorioTextoUtf8($fila['local_snapshot']) : null,
        'id_ciclo' => null,
        'numero_ciclo' => null,
        'ciclo_etiqueta' => 'Version historica original',
        'tipo_ciclo' => 'historico',
        'dias_desde_anterior' => 0,
        'custodio_anterior' => trabajoLaboratorioPersonaRecorrido(null, null),
        'custodio_nuevo' => trabajoLaboratorioPersonaRecorrido(null, null),
        'remitente' => trabajoLaboratorioPersonaRecorrido(null, null),
        'destinatario' => trabajoLaboratorioPersonaRecorrido(null, null),
        'observacion' => isset($fila['observacion_legacy'])
            ? trabajoLaboratorioTextoUtf8($fila['observacion_legacy']) : null,
        'miniatura_media_id' => null,
        'pendiente' => false,
        'estado' => $estadoOriginal !== '' ? $estadoOriginal : null,
        'estado_original' => $estadoOriginal !== '' ? $estadoOriginal : null,
        'estado_nombre' => trabajoLaboratorioTextoUtf8($presentacionOriginal['nombre']),
        'estado_semantico' => $presentacionOriginal['semantica'],
        'color_semantico' => $presentacionOriginal['color'],
        'version_resultante' => null,
        'referencia_origen' => 'trabajo_mecanico_dental:'.intval($fila['cod_trabajo_mecanico_legacyFK']),
        '_orden_origen' => 0,
        '_orden_id' => 0
    );
}

/**
 * Recupera por lote el nodo original de los trabajos promovidos desde el
 * registro legacy. La proyeccion es de solo lectura y no agrega eventos ni
 * modifica el snapshot historico.
 */
function trabajoLaboratorioHistoricoOriginalesPorTrabajos($mysqli, $trabajos)
{
    $origenes = array();
    $ids = array();
    if (!trabajoLaboratorioHistoricoEstructuraDisponible($mysqli)) {
        return $origenes;
    }
    foreach ((array)$trabajos as $trabajo) {
        if (!is_array($trabajo) || empty($trabajo['id'])) {
            continue;
        }
        $ids[intval($trabajo['id'])] = intval($trabajo['id']);
    }
    $ids = array_values($ids);
    if (count($ids) === 0) {
        return $origenes;
    }
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $sql = 'SELECT h.id,h.id_trabajo_laboratorioFK,h.cod_trabajo_mecanico_legacyFK,'
        .'h.estado_legacy_snapshot,h.fecha_creacion_snapshot,h.cod_usuario_creador_snapshot,'
        .'h.cod_local_snapshot,h.observacion_snapshot AS observacion_legacy,'
        .'pcrea.nombre_persona AS usuario_creador_original,'
        .'ucrea.tipo AS usuario_creador_rol_original,ucrea.url AS usuario_creador_avatar_original,'
        .'ls.Nombre AS local_snapshot FROM trabajo_laboratorio_historico h '
        .'LEFT JOIN usuario ucrea ON ucrea.cod_usuario=h.cod_usuario_creador_snapshot '
        .'LEFT JOIN persona pcrea ON pcrea.cod_persona=h.cod_usuario_creador_snapshot '
        .'LEFT JOIN local ls ON ls.cod_local=h.cod_local_snapshot '
        .'WHERE h.id_trabajo_laboratorioFK IN ('.$marcas.') ORDER BY h.id ASC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'origen_historico_no_disponible',
            'No se pudo preparar el origen historico de los trabajos.'
        );
    }
    $valores = $ids;
    trabajoLaboratorioVincularParametros($stmt, str_repeat('i', count($ids)), $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'origen_historico_no_disponible',
            'No se pudo consultar el origen historico de los trabajos.'
        );
    }
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $idTrabajo = intval($fila['id_trabajo_laboratorioFK']);
        if ($idTrabajo > 0 && !isset($origenes[$idTrabajo])) {
            $origenes[$idTrabajo] = trabajoLaboratorioHistoricoNodoOriginal($fila);
        }
    }
    $stmt->close();
    return $origenes;
}

/**
 * Arma el recorrido de una pagina historica con dos consultas agrupadas como
 * maximo: una para sus decisiones administrativas y otra, compartida con el
 * modulo vigente, para los hitos operativos de los registros promovidos.
 */
function trabajoLaboratorioHistoricoRecorridosPorFilas($mysqli, $filas)
{
    $filasPorId = array();
    $recorridos = array();
    $trabajosOperativos = array();
    $historicoPorTrabajo = array();
    foreach ((array)$filas as $fila) {
        if (!is_array($fila) || !isset($fila['id'])) {
            continue;
        }
        $idHistorico = intval($fila['id']);
        if ($idHistorico <= 0) {
            continue;
        }
        $filasPorId[$idHistorico] = $fila;
        $recorridos[$idHistorico] = array(
            trabajoLaboratorioHistoricoNodoOriginal($fila)
        );

        $idTrabajo = isset($fila['id_trabajo_laboratorioFK'])
            ? intval($fila['id_trabajo_laboratorioFK']) : 0;
        if ($idTrabajo > 0) {
            $trabajosOperativos[$idTrabajo] = array(
                'id' => $idTrabajo,
                'estado_derivado' => isset($fila['estado_operativo'])
                    ? $fila['estado_operativo'] : null,
                'id_transferencia_pendienteFK' => isset($fila['id_transferencia_operativa_pendiente'])
                    ? $fila['id_transferencia_operativa_pendiente'] : null
            );
            $historicoPorTrabajo[$idTrabajo] = $idHistorico;
        }
    }
    $ids = array_keys($filasPorId);
    if (count($ids) === 0) {
        return $recorridos;
    }

    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $sql = 'SELECT e.id,e.id_historicoFK,e.tipo_evento,e.estado_convalidacion_nuevo,'
        .'e.estado_declarado_nuevo,e.cod_custodio_anteriorFK,e.cod_custodio_nuevoFK,'
        .'e.cod_local_anteriorFK,e.cod_local_nuevoFK,e.fecha_servidor,e.cod_usuario_actorFK,'
        .'e.justificacion,e.version_resultante,pa.nombre_persona AS actor_nombre,'
        .'ua.tipo AS actor_rol,ua.url AS actor_avatar,l.Nombre AS local_nombre,'
        .'pca.nombre_persona AS custodio_anterior_nombre,uca.tipo AS custodio_anterior_rol,'
        .'uca.url AS custodio_anterior_avatar,pcn.nombre_persona AS custodio_nuevo_nombre,'
        .'ucn.tipo AS custodio_nuevo_rol,ucn.url AS custodio_nuevo_avatar '
        .'FROM trabajo_laboratorio_historico_evento e '
        .'LEFT JOIN persona pa ON pa.cod_persona=e.cod_usuario_actorFK '
        .'LEFT JOIN usuario ua ON ua.cod_usuario=e.cod_usuario_actorFK '
        .'LEFT JOIN local l ON l.cod_local=COALESCE(e.cod_local_nuevoFK,e.cod_local_anteriorFK) '
        .'LEFT JOIN persona pca ON pca.cod_persona=e.cod_custodio_anteriorFK '
        .'LEFT JOIN usuario uca ON uca.cod_usuario=e.cod_custodio_anteriorFK '
        .'LEFT JOIN persona pcn ON pcn.cod_persona=e.cod_custodio_nuevoFK '
        .'LEFT JOIN usuario ucn ON ucn.cod_usuario=e.cod_custodio_nuevoFK '
        .'WHERE e.id_historicoFK IN ('.$marcas.') '
        ."AND e.tipo_evento<>'sincronizacion_historica' "
        .'ORDER BY e.id_historicoFK ASC,e.fecha_servidor ASC,e.id ASC';
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'recorrido_historico_no_disponible',
            'No se pudo preparar el recorrido de los trabajos historicos.'
        );
    }
    $valores = $ids;
    trabajoLaboratorioVincularParametros($stmt, str_repeat('i', count($ids)), $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'recorrido_historico_no_disponible',
            'No se pudo consultar el recorrido de los trabajos historicos.'
        );
    }
    $resultado = $stmt->get_result();
    while ($evento = $resultado->fetch_assoc()) {
        $idHistorico = intval($evento['id_historicoFK']);
        if (!isset($recorridos[$idHistorico])) {
            continue;
        }
        $estadoEvento = trabajoLaboratorioHistoricoPresentacionEventoRecorrido($evento);
        $presentacion = $estadoEvento['presentacion'];
        $codLocalEvento = $evento['cod_local_nuevoFK'] !== null
            ? $evento['cod_local_nuevoFK'] : $evento['cod_local_anteriorFK'];
        $recorridos[$idHistorico][] = array(
            'id' => 'administrativo-'.intval($evento['id']),
            'id_evento' => intval($evento['id']),
            'origen' => 'administrativo',
            'tipo_evento' => trabajoLaboratorioTextoUtf8($evento['tipo_evento']),
            'titulo' => trabajoLaboratorioHistoricoEtiquetaEventoRecorrido($evento['tipo_evento']),
            'fecha_servidor' => $evento['fecha_servidor'],
            'actor' => trabajoLaboratorioPersonaRecorrido(
                $evento['cod_usuario_actorFK'], $evento['actor_nombre'],
                $evento['actor_rol'], $evento['actor_avatar']
            ),
            'cod_local' => $codLocalEvento === null ? null : intval($codLocalEvento),
            'local' => trabajoLaboratorioTextoUtf8($evento['local_nombre']),
            'id_ciclo' => null,
            'numero_ciclo' => null,
            'ciclo_etiqueta' => null,
            'tipo_ciclo' => null,
            'dias_desde_anterior' => 0,
            'custodio_anterior' => trabajoLaboratorioPersonaRecorrido(
                $evento['cod_custodio_anteriorFK'], $evento['custodio_anterior_nombre'],
                $evento['custodio_anterior_rol'], $evento['custodio_anterior_avatar']
            ),
            'custodio_nuevo' => trabajoLaboratorioPersonaRecorrido(
                $evento['cod_custodio_nuevoFK'], $evento['custodio_nuevo_nombre'],
                $evento['custodio_nuevo_rol'], $evento['custodio_nuevo_avatar']
            ),
            'remitente' => trabajoLaboratorioPersonaRecorrido(null, null),
            'destinatario' => trabajoLaboratorioPersonaRecorrido(null, null),
            'observacion' => trabajoLaboratorioTextoUtf8($evento['justificacion']),
            'miniatura_media_id' => null,
            'pendiente' => false,
            'estado' => $estadoEvento['estado'],
            'estado_nombre' => trabajoLaboratorioTextoUtf8($presentacion['nombre']),
            'estado_semantico' => $presentacion['semantica'],
            'color_semantico' => $presentacion['color'],
            'version_resultante' => intval($evento['version_resultante']),
            'referencia_origen' => null,
            '_orden_origen' => 10,
            '_orden_id' => intval($evento['id'])
        );
    }
    $stmt->close();

    if (count($trabajosOperativos) > 0) {
        $recorridosOperativos = trabajoLaboratorioRecorridosPorTrabajos(
            $mysqli,
            array_values($trabajosOperativos)
        );
        foreach ($recorridosOperativos as $idTrabajo => $nodosOperativos) {
            if (!isset($historicoPorTrabajo[$idTrabajo])) {
                continue;
            }
            $idHistorico = $historicoPorTrabajo[$idTrabajo];
            foreach ($nodosOperativos as $nodo) {
                $nodo['referencia_origen'] = null;
                $nodo['_orden_origen'] = 20;
                $nodo['_orden_id'] = isset($nodo['id_evento']) ? intval($nodo['id_evento']) : 0;
                $recorridos[$idHistorico][] = $nodo;
            }
        }
    }

    foreach ($recorridos as $idHistorico => &$nodos) {
        usort($nodos, function ($a, $b) {
            $fechaA = trabajoLaboratorioTimestampSistema(
                isset($a['fecha_servidor']) ? $a['fecha_servidor'] : null
            );
            $fechaB = trabajoLaboratorioTimestampSistema(
                isset($b['fecha_servidor']) ? $b['fecha_servidor'] : null
            );
            if ($fechaA === false && $fechaB !== false) {
                return -1;
            }
            if ($fechaA !== false && $fechaB === false) {
                return 1;
            }
            if ($fechaA !== $fechaB) {
                return $fechaA < $fechaB ? -1 : 1;
            }
            $origenA = isset($a['_orden_origen']) ? intval($a['_orden_origen']) : 0;
            $origenB = isset($b['_orden_origen']) ? intval($b['_orden_origen']) : 0;
            if ($origenA !== $origenB) {
                return $origenA < $origenB ? -1 : 1;
            }
            $idA = isset($a['_orden_id']) ? intval($a['_orden_id']) : 0;
            $idB = isset($b['_orden_id']) ? intval($b['_orden_id']) : 0;
            return $idA === $idB ? 0 : ($idA < $idB ? -1 : 1);
        });
        $fechaAnterior = false;
        foreach ($nodos as &$nodo) {
            $fechaActual = trabajoLaboratorioTimestampSistema(
                isset($nodo['fecha_servidor']) ? $nodo['fecha_servidor'] : null
            );
            $nodo['dias_desde_anterior'] = $fechaAnterior && $fechaActual
                ? max(0, intval(floor(($fechaActual - $fechaAnterior) / 86400))) : 0;
            if ($fechaActual) {
                $fechaAnterior = $fechaActual;
            }
            $nodo['pendiente'] = false;
            unset($nodo['_orden_origen']);
            unset($nodo['_orden_id']);
        }
        unset($nodo);
        if (count($nodos) > 0) {
            $fila = $filasPorId[$idHistorico];
            $integrado = isset($fila['id_trabajo_laboratorioFK'])
                && intval($fila['id_trabajo_laboratorioFK']) > 0;
            $estadoOperativo = isset($fila['estado_operativo']) ? (string)$fila['estado_operativo'] : '';
            $nodos[count($nodos) - 1]['pendiente'] = !$integrado
                || !in_array($estadoOperativo, array('instalado', 'cancelado'), true);
        }
    }
    unset($nodos);
    return $recorridos;
}

function trabajoLaboratorioHistoricoFormatearFila($mysqli, $codUsuario, $fila)
{
    $pendientes = trabajoLaboratorioHistoricoPendientesBasicos($mysqli, $fila);
    $estado = isset($fila['estado_declarado']) ? (string)$fila['estado_declarado'] : '';
    $integrado = intval($fila['id_trabajo_laboratorioFK']) > 0
        || (string)$fila['estado_convalidacion'] === 'integrado_operativo';
    $codTecnicoActual = isset($fila['cod_tecnico_formal_actual'])
        ? intval($fila['cod_tecnico_formal_actual']) : intval($fila['cod_tecnico_usuarioFK']);
    return array(
        'id' => intval($fila['id']),
        'id_historico' => intval($fila['id']),
        'cod_trabajo_mecanico_legacy' => intval($fila['cod_trabajo_mecanico_legacyFK']),
        'cod_venta' => intval($fila['cod_venta_snapshot']),
        'cod_cliente' => intval($fila['cod_cliente_snapshot']),
        'paciente' => isset($fila['nombre_paciente']) ? trabajoLaboratorioTextoUtf8($fila['nombre_paciente']) : null,
        'ci_cliente' => isset($fila['ci_cliente']) ? trabajoLaboratorioTextoUtf8($fila['ci_cliente']) : null,
        'cod_tipo_trabajo' => intval($fila['cod_tipo_trabajo_snapshot']),
        'tipo_trabajo' => isset($fila['tipo_trabajo_snapshot'])
            ? trabajoLaboratorioTextoUtf8($fila['tipo_trabajo_snapshot']) : null,
        'cod_mecanico_snapshot' => intval($fila['cod_mecanico_dental_snapshot']),
        'mecanico_snapshot' => isset($fila['mecanico_snapshot'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_snapshot']) : null,
        'cod_mecanico_dental' => intval($fila['cod_mecanico_dental_declaradoFK']),
        'mecanico_declarado' => isset($fila['mecanico_declarado'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_declarado']) : null,
        'cod_tecnico_usuario' => $codTecnicoActual,
        'tecnico' => isset($fila['tecnico_formal_actual'])
            ? trabajoLaboratorioTextoUtf8($fila['tecnico_formal_actual']) : null,
        'tecnico_rol' => isset($fila['mecanico_declarado_rol'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_declarado_rol'])
            : (isset($fila['mecanico_snapshot_rol'])
                ? trabajoLaboratorioTextoUtf8($fila['mecanico_snapshot_rol']) : null),
        'tecnico_avatar' => isset($fila['mecanico_declarado_avatar'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_declarado_avatar'])
            : (isset($fila['mecanico_snapshot_avatar'])
                ? trabajoLaboratorioTextoUtf8($fila['mecanico_snapshot_avatar']) : null),
        'mecanico' => isset($fila['mecanico_declarado'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_declarado'])
            : (isset($fila['mecanico_snapshot'])
                ? trabajoLaboratorioTextoUtf8($fila['mecanico_snapshot']) : null),
        'mecanico_rol' => isset($fila['mecanico_declarado_rol'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_declarado_rol'])
            : (isset($fila['mecanico_snapshot_rol'])
                ? trabajoLaboratorioTextoUtf8($fila['mecanico_snapshot_rol']) : null),
        'mecanico_avatar' => isset($fila['mecanico_declarado_avatar'])
            ? trabajoLaboratorioTextoUtf8($fila['mecanico_declarado_avatar'])
            : (isset($fila['mecanico_snapshot_avatar'])
                ? trabajoLaboratorioTextoUtf8($fila['mecanico_snapshot_avatar']) : null),
        'cod_especialista' => isset($fila['cod_especialista_snapshot'])
            ? intval($fila['cod_especialista_snapshot']) : 0,
        'doctor' => isset($fila['especialista_snapshot'])
            ? trabajoLaboratorioTextoUtf8($fila['especialista_snapshot']) : null,
        'doctor_rol' => isset($fila['especialista_snapshot_rol'])
            ? trabajoLaboratorioTextoUtf8($fila['especialista_snapshot_rol']) : null,
        'doctor_avatar' => isset($fila['especialista_snapshot_avatar'])
            ? trabajoLaboratorioTextoUtf8($fila['especialista_snapshot_avatar']) : null,
        'cod_custodio_actual' => intval($fila['cod_custodio_actualFK']),
        'custodio_actual' => isset($fila['custodio_actual'])
            ? trabajoLaboratorioTextoUtf8($fila['custodio_actual']) : null,
        'cod_local_snapshot' => intval($fila['cod_local_snapshot']),
        'local_snapshot' => isset($fila['local_snapshot'])
            ? trabajoLaboratorioTextoUtf8($fila['local_snapshot']) : null,
        'cod_local' => intval($fila['cod_local_declaradoFK']),
        'local_declarado' => isset($fila['local_declarado'])
            ? trabajoLaboratorioTextoUtf8($fila['local_declarado']) : null,
        'discrepancia_local' => intval($fila['cod_local_snapshot']) > 0
            && intval($fila['cod_local_snapshot']) !== intval($fila['cod_local_declaradoFK']),
        'estado_original' => trabajoLaboratorioTextoUtf8($fila['estado_legacy_snapshot']),
        'estado_original_actual' => isset($fila['estado_legacy_actual'])
            ? trabajoLaboratorioTextoUtf8($fila['estado_legacy_actual']) : null,
        'estado_convalidacion' => trabajoLaboratorioTextoUtf8($fila['estado_convalidacion']),
        'estado_declarado' => $estado !== '' ? $estado : null,
        'estado_declarado_nombre' => trabajoLaboratorioHistoricoEtiquetaEstado($estado),
        'origen_estado' => trabajoLaboratorioTextoUtf8($fila['origen_estado']),
        'cod_detalle_venta' => intval($fila['cod_detalle_ventaFK']),
        'cod_producto' => isset($fila['cod_producto_detalle'])
            ? trabajoLaboratorioTextoUtf8($fila['cod_producto_detalle']) : null,
        'producto' => isset($fila['nombre_producto'])
            ? trabajoLaboratorioTextoUtf8($fila['nombre_producto']) : null,
        'total_candidatos_detalle' => isset($fila['total_candidatos_detalle'])
            ? intval($fila['total_candidatos_detalle']) : null,
        'fecha_objetivo' => $fila['fecha_objetivo'],
        'fecha_retiro_original' => $fila['fecha_retiro_snapshot'],
        'fecha_entrega_original' => $fila['fecha_entrega_snapshot'],
        'fecha_retiro_declarada' => $fila['fecha_retiro_declarada'],
        'fecha_entrega_declarada' => $fila['fecha_entrega_declarada'],
        'fecha_situacion_declarada' => $fila['fecha_situacion_declarada'],
        'observacion_original' => isset($fila['observacion_legacy'])
            ? trabajoLaboratorioTextoUtf8($fila['observacion_legacy']) : null,
        'colorimetro_original' => isset($fila['colorimetro_legacy'])
            ? trabajoLaboratorioTextoUtf8($fila['colorimetro_legacy']) : null,
        'costo_original' => isset($fila['costo_legacy']) && $fila['costo_legacy'] !== null
            ? intval($fila['costo_legacy']) : null,
        'autor_original' => isset($fila['usuario_creador_original'])
            ? trabajoLaboratorioTextoUtf8($fila['usuario_creador_original']) : null,
        'autor_original_rol' => isset($fila['usuario_creador_rol_original'])
            ? trabajoLaboratorioTextoUtf8($fila['usuario_creador_rol_original']) : null,
        'autor_original_avatar' => isset($fila['usuario_creador_avatar_original'])
            ? trabajoLaboratorioTextoUtf8($fila['usuario_creador_avatar_original']) : null,
        'cod_usuario_creador_original' => intval($fila['cod_usuario_creador_snapshot']),
        'fecha_creacion_original' => $fila['fecha_creacion_snapshot'],
        'editor_original' => isset($fila['usuario_editor_original'])
            ? trabajoLaboratorioTextoUtf8($fila['usuario_editor_original']) : null,
        'cod_usuario_editor_original' => intval($fila['cod_usuario_editor_snapshot']),
        'fecha_edicion_original' => $fila['fecha_edicion_snapshot'],
        'justificacion_ultima' => trabajoLaboratorioTextoUtf8($fila['justificacion_ultima']),
        'fecha_sincronizacion' => $fila['fecha_sincronizacion'],
        'fecha_convalidacion' => $fila['fecha_convalidacion'],
        'fecha_actualizacion' => $fila['fecha_actualizacion'],
        'version' => intval($fila['version']),
        'integrado' => $integrado,
        'id_trabajo_laboratorio' => intval($fila['id_trabajo_laboratorioFK']),
        'codigo_trabajo_operativo' => isset($fila['codigo_visible'])
            ? trabajoLaboratorioTextoUtf8($fila['codigo_visible']) : null,
        'pendientes' => $pendientes,
        'cantidad_pendientes' => count($pendientes),
        'listo_para_integrar' => !$integrado
            && (string)$fila['estado_convalidacion'] === 'convalidado_administracion'
            && count($pendientes) === 0,
        'acciones' => array(
            'puede_convalidar' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
                && !$integrado
                && (string)$fila['estado_convalidacion'] !== 'convalidado_administracion',
            'puede_rectificar' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
                && !$integrado
                && (string)$fila['estado_convalidacion'] === 'convalidado_administracion',
            'puede_promover' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
                && !$integrado
                && (string)$fila['estado_convalidacion'] === 'convalidado_administracion'
                && count($pendientes) === 0,
            'puede_resolver' => trabajoLaboratorioUsuario($mysqli, intval($codUsuario))
                && !$integrado
        )
    );
}

function trabajoLaboratorioHistoricoListarHistoricos($mysqli, $codUsuario, $entrada)
{
    trabajoLaboratorioHistoricoExigirEstructura($mysqli);
    trabajoLaboratorioHistoricoExigirUsuarioActivo($mysqli, $codUsuario);
    $entrada = is_array($entrada) ? $entrada : array();
    $pagina = max(1, trabajoLaboratorioEntero(isset($entrada['pagina']) ? $entrada['pagina'] : 1));
    $porPagina = trabajoLaboratorioEntero(isset($entrada['por_pagina'])
        ? $entrada['por_pagina'] : (isset($entrada['limite']) ? $entrada['limite'] : 20));
    $porPagina = max(5, min(100, $porPagina));
    $offset = ($pagina - 1) * $porPagina;

    $tipos = '';
    $valores = array();
    $condiciones = array(
        trabajoLaboratorioHistoricoCondicionAcceso($mysqli, $codUsuario, 'h', $tipos, $valores),
        'h.id_trabajo_laboratorioFK IS NULL',
        "h.estado_convalidacion<>'integrado_operativo'"
    );
    $codVentaExactaTexto = trim(isset($entrada['cod_venta']) ? (string)$entrada['cod_venta'] : '');
    if ($codVentaExactaTexto !== '') {
        if (!ctype_digit($codVentaExactaTexto) || intval($codVentaExactaTexto) <= 0) {
            trabajoLaboratorioLanzar('filtro_venta_invalido', 'La venta solicitada para el filtro historico no es valida.');
        }
        $condiciones[] = 'h.cod_venta_snapshot=?';
        $tipos .= 'i';
        $valores[] = intval($codVentaExactaTexto);
    }
    $busqueda = trabajoLaboratorioTextoBaseDatos(
        isset($entrada['busqueda']) ? $entrada['busqueda'] : '',
        100
    );
    if ($busqueda !== '') {
        $condiciones[] = '(CAST(h.cod_trabajo_mecanico_legacyFK AS CHAR) LIKE CONCAT(\'%\',?,\'%\') '
            .'OR CAST(h.cod_venta_snapshot AS CHAR) LIKE CONCAT(\'%\',?,\'%\') '
            .'OR pc.nombre_persona LIKE CONCAT(\'%\',?,\'%\') '
            .'OR cl.ci_cliente LIKE CONCAT(\'%\',?,\'%\') '
            .'OR pmd.nombre_persona LIKE CONCAT(\'%\',?,\'%\') '
            .'OR pms.nombre_persona LIKE CONCAT(\'%\',?,\'%\') '
            .'OR tts.descripcion LIKE CONCAT(\'%\',?,\'%\') '
            .'OR tl.codigo_visible LIKE CONCAT(\'%\',?,\'%\'))';
        $tipos .= 'ssssssss';
        for ($i = 0; $i < 8; $i++) {
            $valores[] = $busqueda;
        }
    }
    $estadoOriginal = trabajoLaboratorioNormalizarTexto(
        isset($entrada['estado_original']) ? $entrada['estado_original'] : ''
    );
    if ($estadoOriginal !== '') {
        $permitidosOriginal = array('pendiente', 'entregado', 'retirado', 'pagado', 'inactivo');
        if (!in_array($estadoOriginal, $permitidosOriginal, true)) {
            trabajoLaboratorioLanzar('estado_original_invalido', 'El estado original solicitado no es valido.');
        }
        $condiciones[] = 'h.estado_legacy_snapshot=?';
        $tipos .= 's';
        $valores[] = $estadoOriginal;
    }
    $estadoDeclarado = trabajoLaboratorioNormalizarTexto(
        isset($entrada['estado_declarado']) ? $entrada['estado_declarado'] : ''
    );
    if ($estadoDeclarado !== '') {
        if ($estadoDeclarado === 'sin_definir' || $estadoDeclarado === 'situacion_por_actualizar') {
            $condiciones[] = '(h.estado_declarado IS NULL OR h.estado_declarado=\'\')';
        } elseif (in_array($estadoDeclarado, trabajoLaboratorioHistoricoCodigosEstadosDeclarables(), true)) {
            $condiciones[] = 'h.estado_declarado=?';
            $tipos .= 's';
            $valores[] = $estadoDeclarado;
        } else {
            trabajoLaboratorioLanzar('estado_declarado_invalido', 'La situacion declarada solicitada no es valida.');
        }
    }
    $estadoConvalidacion = trabajoLaboratorioNormalizarTexto(
        isset($entrada['estado_convalidacion']) ? $entrada['estado_convalidacion'] : ''
    );
    if ($estadoConvalidacion !== '') {
        $estadosConvalidacion = array(
            'situacion_por_actualizar', 'sincronizado_automatico',
            'convalidado_administracion', 'integrado_operativo'
        );
        if (!in_array($estadoConvalidacion, $estadosConvalidacion, true)) {
            trabajoLaboratorioLanzar(
                'estado_convalidacion_invalido',
                'El estado de convalidacion solicitado no es valido.'
            );
        }
        $condiciones[] = 'h.estado_convalidacion=?';
        $tipos .= 's';
        $valores[] = $estadoConvalidacion;
    }
    $pendienteRevision = trabajoLaboratorioNormalizarTexto(
        isset($entrada['pendiente_revision']) ? $entrada['pendiente_revision'] : ''
    );
    if (in_array($pendienteRevision, array('1', 'si', 'pendiente', 'por_actualizar'), true)) {
        $condiciones[] = "h.estado_convalidacion<>'integrado_operativo'";
    } elseif (in_array($pendienteRevision, array('0', 'no', 'resuelto', 'integrado'), true)) {
        $condiciones[] = "h.estado_convalidacion='integrado_operativo'";
    } elseif ($pendienteRevision === 'sin_detalle') {
        $condiciones[] = 'h.cod_detalle_ventaFK IS NULL';
    } elseif ($pendienteRevision === 'sin_cuenta_mecanico') {
        $condiciones[] = '(mdd.cod_usuarioFK IS NULL OR IFNULL(utf.estado,\'\')<>\'Activo\')';
    } elseif ($pendienteRevision === 'sin_custodio') {
        $condiciones[] = 'h.cod_custodio_actualFK IS NULL';
    }
    $codLocal = trabajoLaboratorioEntero(isset($entrada['cod_local']) ? $entrada['cod_local'] : 0);
    if ($codLocal > 0) {
        $condiciones[] = 'h.cod_local_declaradoFK=?';
        $tipos .= 'i';
        $valores[] = $codLocal;
    }
    $codMecanico = trabajoLaboratorioEntero(
        isset($entrada['cod_mecanico_dental']) ? $entrada['cod_mecanico_dental'] : 0
    );
    if ($codMecanico > 0) {
        $condiciones[] = 'h.cod_mecanico_dental_declaradoFK=?';
        $tipos .= 'i';
        $valores[] = $codMecanico;
    }
    $where = implode(' AND ', $condiciones);
    $desde = trabajoLaboratorioHistoricoSqlDecoradoDesde();
    $stmtTotal = $mysqli->prepare('SELECT COUNT(DISTINCT h.id) AS total '.$desde.'WHERE '.$where);
    if (!$stmtTotal) {
        trabajoLaboratorioLanzar('listado_historico_no_disponible', 'No se pudo preparar el listado historico.');
    }
    $valoresTotal = $valores;
    trabajoLaboratorioVincularParametros($stmtTotal, $tipos, $valoresTotal);
    if (!$stmtTotal->execute()) {
        $stmtTotal->close();
        trabajoLaboratorioLanzar('listado_historico_no_disponible', 'No se pudo contar el listado historico.');
    }
    $total = intval($stmtTotal->get_result()->fetch_assoc()['total']);
    $stmtTotal->close();

    $sql = trabajoLaboratorioHistoricoSqlDecoradoSelect().$desde.'WHERE '.$where
        ." ORDER BY FIELD(h.estado_convalidacion,'situacion_por_actualizar','sincronizado_automatico',"
        ."'convalidado_administracion','integrado_operativo'),h.fecha_actualizacion DESC,h.id DESC LIMIT ?,?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('listado_historico_no_disponible', 'No se pudo preparar el listado historico.');
    }
    $tiposLista = $tipos.'ii';
    $valoresLista = $valores;
    $valoresLista[] = $offset;
    $valoresLista[] = $porPagina;
    trabajoLaboratorioVincularParametros($stmt, $tiposLista, $valoresLista);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('listado_historico_no_disponible', 'No se pudo consultar el listado historico.');
    }
    $filas = array();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    $stmt->close();
    $recorridos = trabajoLaboratorioHistoricoRecorridosPorFilas($mysqli, $filas);
    $items = array();
    foreach ($filas as $fila) {
        $item = trabajoLaboratorioHistoricoFormatearFila($mysqli, $codUsuario, $fila);
        $idHistorico = intval($fila['id']);
        $item['recorrido'] = isset($recorridos[$idHistorico])
            ? $recorridos[$idHistorico] : array();
        $items[] = $item;
    }
    return array(
        'items' => $items,
        'historicos' => $items,
        'total' => $total,
        'hay_mas' => ($offset + count($items)) < $total,
        'paginacion' => array(
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total' => $total,
            'total_paginas' => $total > 0 ? intval(ceil($total / $porPagina)) : 0
        ),
        'estados_declarables' => trabajoLaboratorioHistoricoEstadosDeclarables(),
        'puede_auditar' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario),
        'puede_resolver' => true
    );
}

function trabajoLaboratorioHistoricoResumen($mysqli, $codUsuario, $entrada = array())
{
    trabajoLaboratorioHistoricoExigirEstructura($mysqli);
    trabajoLaboratorioHistoricoExigirUsuarioActivo($mysqli, $codUsuario);
    $entrada = is_array($entrada) ? $entrada : array();
    $tipos = '';
    $valores = array();
    $condiciones = array(
        trabajoLaboratorioHistoricoCondicionAcceso($mysqli, $codUsuario, 'h', $tipos, $valores),
        'h.id_trabajo_laboratorioFK IS NULL',
        "h.estado_convalidacion<>'integrado_operativo'"
    );
    $codLocal = trabajoLaboratorioEntero(isset($entrada['cod_local']) ? $entrada['cod_local'] : 0);
    if ($codLocal > 0) {
        $condiciones[] = 'h.cod_local_declaradoFK=?';
        $tipos .= 'i';
        $valores[] = $codLocal;
    }
    $where = implode(' AND ', $condiciones);
    $sql = "SELECT COUNT(*) AS total,"
        ."SUM(h.estado_convalidacion='situacion_por_actualizar') AS situacion_por_actualizar,"
        ."SUM(h.estado_convalidacion='sincronizado_automatico') AS sincronizados_automaticamente,"
        ."SUM(h.estado_convalidacion='convalidado_administracion') AS convalidados_administracion,"
        ."SUM(h.estado_convalidacion='integrado_operativo') AS integrados_operativos,"
        ."SUM(h.id_trabajo_laboratorioFK IS NULL) AS pendientes_integracion,"
        ."SUM(h.id_trabajo_laboratorioFK IS NULL AND h.cod_detalle_ventaFK IS NULL) AS sin_detalle,"
        ."SUM(h.id_trabajo_laboratorioFK IS NULL AND "
        ."(mdd.cod_usuarioFK IS NULL OR IFNULL(utf.estado,'')<>'Activo')) AS sin_cuenta_mecanico,"
        ."SUM(h.id_trabajo_laboratorioFK IS NULL AND h.cod_custodio_actualFK IS NULL) AS sin_custodio,"
        ."SUM(h.id_trabajo_laboratorioFK IS NULL AND h.estado_convalidacion='convalidado_administracion' "
        ."AND h.estado_declarado IS NOT NULL AND h.cod_detalle_ventaFK IS NOT NULL "
        ."AND mdd.cod_usuarioFK IS NOT NULL AND utf.estado='Activo' "
        ."AND h.cod_custodio_actualFK IS NOT NULL AND h.fecha_objetivo IS NOT NULL "
        ."AND ta.id IS NULL) AS preparados_relacionales "
        .'FROM trabajo_laboratorio_historico h '
        .'LEFT JOIN mecanico_dental mdd '
        .'ON mdd.cod_mecanico_dental=h.cod_mecanico_dental_declaradoFK '
        .'LEFT JOIN usuario utf ON utf.cod_usuario=mdd.cod_usuarioFK '
        .'LEFT JOIN trabajo_laboratorio ta ON ta.cod_detalle_activo_unico=h.cod_detalle_ventaFK '
        .'WHERE '.$where;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        trabajoLaboratorioLanzar('resumen_historico_no_disponible', 'No se pudo preparar el resumen historico.');
    }
    trabajoLaboratorioVincularParametros($stmt, $tipos, $valores);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('resumen_historico_no_disponible', 'No se pudo consultar el resumen historico.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $resumen = array(
        'total' => intval($fila['total']),
        'situacion_por_actualizar' => intval($fila['situacion_por_actualizar']),
        'por_actualizar' => intval($fila['situacion_por_actualizar']),
        'sincronizados_automaticamente' => intval($fila['sincronizados_automaticamente']),
        'convalidados_administracion' => intval($fila['convalidados_administracion']),
        'integrados_operativos' => intval($fila['integrados_operativos']),
        'pendientes_integracion' => intval($fila['pendientes_integracion']),
        'sin_detalle' => intval($fila['sin_detalle']),
        'sin_cuenta_mecanico' => intval($fila['sin_cuenta_mecanico']),
        'sin_custodio' => intval($fila['sin_custodio']),
        'preparados_relacionales' => intval($fila['preparados_relacionales'])
    );

    $porEstadoOriginal = array();
    $sqlGrupo = 'SELECT h.estado_legacy_snapshot AS estado,COUNT(*) AS total '
        .'FROM trabajo_laboratorio_historico h WHERE '.$where
        .' GROUP BY h.estado_legacy_snapshot ORDER BY h.estado_legacy_snapshot';
    $stmtGrupo = $mysqli->prepare($sqlGrupo);
    if ($stmtGrupo) {
        $valoresGrupo = $valores;
        trabajoLaboratorioVincularParametros($stmtGrupo, $tipos, $valoresGrupo);
        if ($stmtGrupo->execute()) {
            $resultadoGrupo = $stmtGrupo->get_result();
            while ($grupo = $resultadoGrupo->fetch_assoc()) {
                $porEstadoOriginal[trabajoLaboratorioTextoUtf8($grupo['estado'])] = intval($grupo['total']);
            }
        }
        $stmtGrupo->close();
    }
    $resumen['por_estado_original'] = $porEstadoOriginal;
    $resumen['puede_auditar'] = trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario);
    $resumen['puede_resolver'] = true;
    return $resumen;
}

function trabajoLaboratorioHistoricoCandidatosDetalle($mysqli, $historico)
{
    $codVenta = intval($historico['cod_venta_snapshot']);
    $seleccionado = intval($historico['cod_detalle_ventaFK']);
    $stmt = $mysqli->prepare(
        'SELECT dv.cod_detalle,dv.cod_ventaFK,dv.cod_productoFK,dv.cantidad_detalle,'
        .'dv.estado,dv.estado_tratamiento,dv.progreso_porcentaje,dv.descripcion,dv.detalleproducto,'
        .'p.nombre_producto,p.estado AS estado_producto,p.cod_categoriaFK,'
        .'tl.id AS id_trabajo_activo,tl.codigo_visible AS codigo_trabajo_activo,'
        .'(SELECT COUNT(*) FROM trabajo_laboratorio tx '
        .'WHERE tx.cod_detalle_ventaFK=dv.cod_detalle) AS total_trabajos_operativos '
        .'FROM detalle_venta dv '
        .'INNER JOIN producto p ON p.cod_producto=dv.cod_productoFK '
        .'LEFT JOIN trabajo_laboratorio tl ON tl.cod_detalle_activo_unico=dv.cod_detalle '
        .'WHERE dv.cod_ventaFK=? ORDER BY dv.cod_detalle ASC'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'candidatos_detalle_no_disponibles',
            'No se pudieron preparar los tratamientos candidatos.'
        );
    }
    $stmt->bind_param('i', $codVenta);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'candidatos_detalle_no_disponibles',
            'No se pudieron consultar los tratamientos candidatos.'
        );
    }
    $resultado = $stmt->get_result();
    $candidatos = array();
    while ($fila = $resultado->fetch_assoc()) {
        $idDetalle = intval($fila['cod_detalle']);
        $configuracion = trabajoLaboratorioObtenerConfiguracionProducto($mysqli, $fila['cod_productoFK']);
        $estadoNormalizado = trabajoLaboratorioNormalizarTexto($fila['estado']);
        $inactivo = in_array($estadoNormalizado, array('eliminado', 'inactivo', 'anulado'), true);
        $detalleActivo = trabajoLaboratorioDetalleClinicoActivo(array(
            'estado_detalle' => $fila['estado'],
            'estado_tratamiento' => $fila['estado_tratamiento'],
            'progreso_porcentaje' => $fila['progreso_porcentaje']
        ));
        $ubicaciones = trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, $idDetalle);
        $candidatos[] = array(
            'cod_detalle_venta' => $idDetalle,
            'cod_producto' => trabajoLaboratorioTextoUtf8($fila['cod_productoFK']),
            'producto' => trabajoLaboratorioTextoUtf8($fila['nombre_producto']),
            'cantidad' => floatval($fila['cantidad_detalle']),
            'estado_detalle' => trabajoLaboratorioTextoUtf8($fila['estado']),
            'estado_tratamiento' => trabajoLaboratorioTextoUtf8($fila['estado_tratamiento']),
            'progreso_porcentaje' => intval($fila['progreso_porcentaje']),
            'descripcion' => trabajoLaboratorioTextoUtf8($fila['descripcion']),
            'detalle_producto' => trabajoLaboratorioTextoUtf8($fila['detalleproducto']),
            'seleccionado' => $idDetalle === $seleccionado,
            'id_trabajo_activo' => intval($fila['id_trabajo_activo']),
            'codigo_trabajo_activo' => trabajoLaboratorioTextoUtf8($fila['codigo_trabajo_activo']),
            'total_trabajos_operativos' => intval($fila['total_trabajos_operativos']),
            'ocupado' => intval($fila['id_trabajo_activo']) > 0,
            'inactivo' => $inactivo,
            'finalizado' => !$detalleActivo,
            'puede_continuar' => $detalleActivo,
            /* La declaracion historica puede identificar un detalle hoy
               inactivo u ocupado. La promocion valida despues si el estado
               final permite conservarlo sin ocupar el vinculo activo unico. */
            'seleccionable' => true,
            'configuracion_laboratorio' => $configuracion,
            'ubicaciones' => $ubicaciones
        );
    }
    $stmt->close();
    return $candidatos;
}

function trabajoLaboratorioHistoricoCatalogoMecanicos($mysqli)
{
    $stmt = $mysqli->prepare(
        'SELECT md.cod_mecanico_dental,md.estado,pm.nombre_persona AS nombre_mecanico,'
        .'md.cod_usuarioFK,u.estado AS estado_usuario,u.cod_localFK,u.tipo,u.url,'
        .'pu.nombre_persona AS nombre_usuario,l.Nombre AS nombre_local '
        .'FROM mecanico_dental md '
        .'LEFT JOIN persona pm ON pm.cod_persona=md.cod_personaFK '
        .'LEFT JOIN usuario u ON u.cod_usuario=md.cod_usuarioFK '
        .'LEFT JOIN persona pu ON pu.cod_persona=u.cod_usuario '
        .'LEFT JOIN local l ON l.cod_local=u.cod_localFK '
        .'ORDER BY (md.estado=\'activo\') DESC,pm.nombre_persona ASC'
    );
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        return array();
    }
    $resultado = $stmt->get_result();
    $mecanicos = array();
    while ($fila = $resultado->fetch_assoc()) {
        $codTecnico = intval($fila['cod_usuarioFK']);
        $usuarioActivo = $codTecnico > 0 && (string)$fila['estado_usuario'] === 'Activo';
        $puedeVer = $usuarioActivo
            && trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'VERTRABAJOSLABORATORIO');
        $puedeRecibir = $usuarioActivo
            && trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'RECIBIRTRABAJOLABORATORIO');
        $puedeEntregar = $usuarioActivo
            && trabajoLaboratorioTienePermiso($mysqli, $codTecnico, 'ENTREGARTRABAJOLABORATORIO');
        $mecanicos[] = array(
            'cod_mecanico_dental' => intval($fila['cod_mecanico_dental']),
            'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_mecanico']),
            'estado' => trabajoLaboratorioTextoUtf8($fila['estado']),
            'cod_usuario' => $codTecnico,
            'nombre_usuario' => trabajoLaboratorioTextoUtf8($fila['nombre_usuario']),
            'estado_usuario' => trabajoLaboratorioTextoUtf8($fila['estado_usuario']),
            'cod_local' => intval($fila['cod_localFK']),
            'local' => trabajoLaboratorioTextoUtf8($fila['nombre_local']),
            'rol' => trabajoLaboratorioTextoUtf8($fila['tipo']),
            'avatar' => trabajoLaboratorioTextoUtf8($fila['url']),
            'cuenta_vinculada' => $codTecnico > 0,
            'cuenta_activa' => $usuarioActivo,
            'puede_ver_trabajos' => $puedeVer,
            'puede_recibir_trabajos' => $puedeRecibir,
            'puede_entregar_trabajos' => $puedeEntregar,
            'habilitado_promocion' => (string)$fila['estado'] === 'activo'
                && $usuarioActivo && $puedeVer && $puedeRecibir && $puedeEntregar
        );
    }
    $stmt->close();
    return $mecanicos;
}

function trabajoLaboratorioHistoricoCatalogoCustodios($mysqli)
{
    $stmt = $mysqli->prepare(
        "SELECT u.cod_usuario,u.cod_localFK,u.tipo,u.url,p.nombre_persona,l.Nombre AS nombre_local,"
        ."md.cod_mecanico_dental FROM usuario u "
        ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
        ."LEFT JOIN local l ON l.cod_local=u.cod_localFK "
        ."LEFT JOIN mecanico_dental md ON md.cod_usuarioFK=u.cod_usuario AND md.estado='activo' "
        ."WHERE u.estado='Activo' ORDER BY l.Nombre ASC,p.nombre_persona ASC"
    );
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        return array();
    }
    $resultado = $stmt->get_result();
    $custodios = array();
    while ($fila = $resultado->fetch_assoc()) {
        $codUsuario = intval($fila['cod_usuario']);
        $puedeVer = trabajoLaboratorioTienePermiso(
            $mysqli,
            $codUsuario,
            'VERTRABAJOSLABORATORIO'
        );
        $puedeRecibir = trabajoLaboratorioTienePermiso(
            $mysqli,
            $codUsuario,
            'RECIBIRTRABAJOLABORATORIO'
        );
        $custodios[] = array(
            'cod_usuario' => $codUsuario,
            'nombre' => trabajoLaboratorioTextoUtf8($fila['nombre_persona']),
            'rol' => trabajoLaboratorioTextoUtf8($fila['tipo']),
            'avatar' => trabajoLaboratorioTextoUtf8($fila['url']),
            'cod_local' => intval($fila['cod_localFK']),
            'local' => trabajoLaboratorioTextoUtf8($fila['nombre_local']),
            'es_mecanico' => intval($fila['cod_mecanico_dental']) > 0,
            'cod_mecanico_dental' => intval($fila['cod_mecanico_dental']),
            'puede_ver_trabajos' => $puedeVer,
            'puede_recibir_trabajos' => $puedeRecibir,
            'puede_entregar_trabajos' => trabajoLaboratorioTienePermiso(
                $mysqli,
                $codUsuario,
                'ENTREGARTRABAJOLABORATORIO'
            ),
            'habilitado_custodia' => $puedeVer && $puedeRecibir
        );
    }
    $stmt->close();
    return $custodios;
}

function trabajoLaboratorioHistoricoCatalogoLocales($mysqli)
{
    $stmt = $mysqli->prepare('SELECT cod_local,Nombre,estado FROM local ORDER BY Nombre ASC');
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        return array();
    }
    $resultado = $stmt->get_result();
    $locales = array();
    while ($fila = $resultado->fetch_assoc()) {
        $locales[] = array(
            'cod_local' => intval($fila['cod_local']),
            'nombre' => trabajoLaboratorioTextoUtf8($fila['Nombre']),
            'estado' => trabajoLaboratorioTextoUtf8($fila['estado'])
        );
    }
    $stmt->close();
    return $locales;
}

function trabajoLaboratorioHistoricoEventos($mysqli, $idHistorico)
{
    $idHistorico = intval($idHistorico);
    $stmt = $mysqli->prepare(
        'SELECT e.*,p.nombre_persona AS actor '
        .'FROM trabajo_laboratorio_historico_evento e '
        .'LEFT JOIN persona p ON p.cod_persona=e.cod_usuario_actorFK '
        .'WHERE e.id_historicoFK=? ORDER BY e.fecha_servidor ASC,e.id ASC'
    );
    if (!$stmt) {
        return array();
    }
    $stmt->bind_param('i', $idHistorico);
    if (!$stmt->execute()) {
        $stmt->close();
        return array();
    }
    $resultado = $stmt->get_result();
    $eventos = array();
    $camposEnteros = array(
        'id', 'id_historicoFK', 'cod_detalle_venta_anteriorFK', 'cod_detalle_venta_nuevoFK',
        'cod_mecanico_dental_anteriorFK', 'cod_mecanico_dental_nuevoFK',
        'cod_tecnico_usuario_anteriorFK', 'cod_tecnico_usuario_nuevoFK',
        'cod_custodio_anteriorFK', 'cod_custodio_nuevoFK',
        'cod_local_anteriorFK', 'cod_local_nuevoFK', 'cod_usuario_actorFK', 'version_resultante'
    );
    while ($fila = $resultado->fetch_assoc()) {
        foreach ($camposEnteros as $campo) {
            $fila[$campo] = $fila[$campo] === null ? null : intval($fila[$campo]);
        }
        $fila['actor'] = trabajoLaboratorioTextoUtf8($fila['actor']);
        $fila['justificacion'] = trabajoLaboratorioTextoUtf8($fila['justificacion']);
        $fila['metadata'] = trabajoLaboratorioDecodificarJson(
            trabajoLaboratorioTextoUtf8($fila['metadata_json']),
            array()
        );
        unset($fila['metadata_json']);
        unset($fila['payload_hash']);
        unset($fila['clave_idempotencia']);
        $eventos[] = trabajoLaboratorioUtf8($fila);
    }
    $stmt->close();
    return $eventos;
}

function trabajoLaboratorioHistoricoObtenerHistorico($mysqli, $codUsuario, $idHistorico)
{
    trabajoLaboratorioHistoricoExigirEstructura($mysqli);
    trabajoLaboratorioHistoricoExigirUsuarioActivo($mysqli, $codUsuario);
    $historico = trabajoLaboratorioHistoricoObtenerFilaDecorada($mysqli, intval($idHistorico));
    trabajoLaboratorioHistoricoExigirAcceso($mysqli, $codUsuario, $historico);
    $trabajo = trabajoLaboratorioHistoricoFormatearFila($mysqli, $codUsuario, $historico);
    $candidatos = trabajoLaboratorioHistoricoCandidatosDetalle($mysqli, $historico);
    $mecanicos = trabajoLaboratorioHistoricoCatalogoMecanicos($mysqli);
    $custodios = trabajoLaboratorioHistoricoCatalogoCustodios($mysqli);
    $locales = trabajoLaboratorioHistoricoCatalogoLocales($mysqli);
    $estados = trabajoLaboratorioHistoricoEstadosDeclarables();
    return array(
        'historico' => $trabajo,
        'trabajo_historico' => $trabajo,
        'candidatos_detalle' => $candidatos,
        'mecanicos' => $mecanicos,
        'custodios' => $custodios,
        'locales' => $locales,
        'eventos' => trabajoLaboratorioHistoricoEventos($mysqli, intval($historico['id'])),
        'pendientes' => $trabajo['pendientes'],
        'estados_declarables' => $estados,
        'opciones_convalidacion' => array(
            'candidatos_detalle' => $candidatos,
            'detalles_venta' => $candidatos,
            'mecanicos' => $mecanicos,
            'custodios' => $custodios,
            'locales' => $locales,
            'estados_declarables' => $estados
        ),
        'puede_auditar' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario),
        'puede_resolver' => !empty($trabajo['acciones']['puede_resolver'])
    );
}

function trabajoLaboratorioHistoricoValidarDetalleDeclarado($mysqli, $codDetalle, $codVenta)
{
    if ($codDetalle === null) {
        return null;
    }
    $codDetalle = intval($codDetalle);
    $codVenta = intval($codVenta);
    $stmt = $mysqli->prepare(
        'SELECT dv.cod_detalle,dv.cod_ventaFK,dv.cod_productoFK,dv.estado,dv.estado_tratamiento,'
        .'dv.progreso_porcentaje,v.cod_clienteFK,v.cod_local,v.estado AS estado_venta '
        .'FROM detalle_venta dv INNER JOIN venta v ON v.cod_venta=dv.cod_ventaFK '
        .'WHERE dv.cod_detalle=? AND dv.cod_ventaFK=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('detalle_historico_no_disponible', 'No se pudo validar el tratamiento seleccionado.');
    }
    $stmt->bind_param('ii', $codDetalle, $codVenta);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('detalle_historico_no_disponible', 'No se pudo validar el tratamiento seleccionado.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar(
            'detalle_historico_invalido',
            'El tratamiento seleccionado no pertenece a la venta historica.'
        );
    }
    return $fila;
}

function trabajoLaboratorioHistoricoValidarMecanicoDeclarado($mysqli, $codMecanico)
{
    $codMecanico = intval($codMecanico);
    if ($codMecanico <= 0) {
        trabajoLaboratorioLanzar(
            'mecanico_historico_requerido',
            'Declare el mecanico dental que tuvo a cargo el trabajo historico.'
        );
    }
    $stmt = $mysqli->prepare(
        'SELECT md.cod_mecanico_dental,md.estado,md.cod_usuarioFK,'
        .'u.estado AS estado_usuario,u.cod_localFK,p.nombre_persona '
        .'FROM mecanico_dental md '
        .'LEFT JOIN usuario u ON u.cod_usuario=md.cod_usuarioFK '
        .'LEFT JOIN persona p ON p.cod_persona=md.cod_personaFK '
        .'WHERE md.cod_mecanico_dental=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('mecanico_historico_no_disponible', 'No se pudo validar el mecanico declarado.');
    }
    $stmt->bind_param('i', $codMecanico);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('mecanico_historico_no_disponible', 'No se pudo validar el mecanico declarado.');
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar('mecanico_historico_invalido', 'El mecanico declarado no existe.');
    }
    return $fila;
}

function trabajoLaboratorioHistoricoValidarLocalDeclarado($mysqli, $codLocal)
{
    $codLocal = intval($codLocal);
    if ($codLocal <= 0) {
        trabajoLaboratorioLanzar('local_historico_requerido', 'Seleccione la sucursal responsable.');
    }
    $stmt = $mysqli->prepare('SELECT cod_local,Nombre,estado FROM local WHERE cod_local=? LIMIT 1');
    if (!$stmt) {
        trabajoLaboratorioLanzar('local_historico_no_disponible', 'No se pudo validar la sucursal declarada.');
    }
    $stmt->bind_param('i', $codLocal);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar('local_historico_invalido', 'La sucursal declarada no existe.');
    }
    return $fila;
}

function trabajoLaboratorioHistoricoValidarCustodioDeclarado($mysqli, $codCustodio)
{
    if ($codCustodio === null) {
        return null;
    }
    $codCustodio = intval($codCustodio);
    $stmt = $mysqli->prepare(
        'SELECT u.cod_usuario,u.cod_localFK,u.tipo,u.estado,p.nombre_persona,'
        .'EXISTS(SELECT 1 FROM mecanico_dental md '
        .'WHERE md.cod_usuarioFK=u.cod_usuario AND md.estado=\'activo\') AS es_mecanico '
        .'FROM usuario u LEFT JOIN persona p ON p.cod_persona=u.cod_usuario '
        .'WHERE u.cod_usuario=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('custodio_historico_no_disponible', 'No se pudo validar el custodio declarado.');
    }
    $stmt->bind_param('i', $codCustodio);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar('custodio_historico_invalido', 'El custodio declarado no existe.');
    }
    return $fila;
}

function trabajoLaboratorioHistoricoPrepararDeclaracion($mysqli, $historico, $entrada)
{
    $enviado = false;
    $estado = trabajoLaboratorioHistoricoValorEntrada(
        $entrada,
        array('estado_declarado', 'situacion'),
        $historico['estado_declarado'],
        $enviado
    );
    $estado = trabajoLaboratorioNormalizarTexto($estado);
    if (!in_array($estado, trabajoLaboratorioHistoricoCodigosEstadosDeclarables(), true)
        || trabajoLaboratorioHistoricoEstadoEsTransitorio($estado)) {
        trabajoLaboratorioLanzar(
            'estado_historico_invalido',
            'Seleccione una situacion estable. Los estados de traslado no pueden declararse historicamente.'
        );
    }

    $valorDetalle = trabajoLaboratorioHistoricoValorEntrada(
        $entrada,
        array('cod_detalle_venta', 'cod_detalle_ventaFK'),
        $historico['cod_detalle_ventaFK'],
        $enviado
    );
    $codDetalle = trabajoLaboratorioHistoricoNormalizarEnteroNullable($valorDetalle);
    trabajoLaboratorioHistoricoValidarDetalleDeclarado(
        $mysqli,
        $codDetalle,
        intval($historico['cod_venta_snapshot'])
    );

    $valorMecanico = trabajoLaboratorioHistoricoValorEntrada(
        $entrada,
        array('cod_mecanico_dental', 'cod_mecanico_dental_declarado'),
        $historico['cod_mecanico_dental_declaradoFK'],
        $enviado
    );
    $codMecanico = trabajoLaboratorioEntero($valorMecanico);
    $mecanico = trabajoLaboratorioHistoricoValidarMecanicoDeclarado($mysqli, $codMecanico);
    $codTecnico = intval($mecanico['cod_usuarioFK']) > 0 ? intval($mecanico['cod_usuarioFK']) : null;

    $valorLocal = trabajoLaboratorioHistoricoValorEntrada(
        $entrada,
        array('cod_local', 'cod_local_declarado'),
        $historico['cod_local_declaradoFK'],
        $enviado
    );
    $codLocal = trabajoLaboratorioEntero($valorLocal);
    trabajoLaboratorioHistoricoValidarLocalDeclarado($mysqli, $codLocal);

    $valorCustodio = trabajoLaboratorioHistoricoValorEntrada(
        $entrada,
        array('cod_custodio_actual', 'cod_custodio', 'cod_custodio_actualFK'),
        $historico['cod_custodio_actualFK'],
        $enviado
    );
    $codCustodio = trabajoLaboratorioHistoricoNormalizarEnteroNullable($valorCustodio);
    trabajoLaboratorioHistoricoValidarCustodioDeclarado($mysqli, $codCustodio);

    $fechas = array();
    $camposFecha = array(
        'fecha_objetivo' => array('fecha_objetivo'),
        'fecha_retiro_declarada' => array('fecha_retiro_declarada', 'fecha_retiro'),
        'fecha_entrega_declarada' => array('fecha_entrega_declarada', 'fecha_entrega'),
        'fecha_situacion_declarada' => array('fecha_situacion_declarada', 'fecha_situacion')
    );
    foreach ($camposFecha as $campo => $aliases) {
        $fueEnviada = false;
        $valorFecha = trabajoLaboratorioHistoricoValorEntrada(
            $entrada,
            $aliases,
            $historico[$campo],
            $fueEnviada
        );
        $fechas[$campo] = $fueEnviada
            ? trabajoLaboratorioHistoricoFechaEntrada($valorFecha, str_replace('_', ' ', $campo))
            : ($valorFecha !== null && trim((string)$valorFecha) !== '' ? (string)$valorFecha : null);
    }
    if ($fechas['fecha_situacion_declarada'] === null) {
        trabajoLaboratorioLanzar(
            'fecha_situacion_requerida',
            'Indique desde cuando corresponde la situacion declarada.'
        );
    }

    return array(
        'estado_convalidacion' => 'convalidado_administracion',
        'estado_declarado' => $estado,
        'origen_estado' => 'declarado_administracion',
        'cod_detalle_ventaFK' => $codDetalle,
        'cod_mecanico_dental_declaradoFK' => $codMecanico,
        'cod_tecnico_usuarioFK' => $codTecnico,
        'cod_custodio_actualFK' => $codCustodio,
        'cod_local_declaradoFK' => $codLocal,
        'fecha_objetivo' => $fechas['fecha_objetivo'],
        'fecha_retiro_declarada' => $fechas['fecha_retiro_declarada'],
        'fecha_entrega_declarada' => $fechas['fecha_entrega_declarada'],
        'fecha_situacion_declarada' => $fechas['fecha_situacion_declarada']
    );
}

function trabajoLaboratorioHistoricoDeclaracionCambio($historico, $nuevo)
{
    $campos = array(
        'estado_convalidacion', 'estado_declarado', 'origen_estado', 'cod_detalle_ventaFK',
        'cod_mecanico_dental_declaradoFK', 'cod_tecnico_usuarioFK', 'cod_custodio_actualFK',
        'cod_local_declaradoFK', 'fecha_objetivo', 'fecha_retiro_declarada',
        'fecha_entrega_declarada', 'fecha_situacion_declarada'
    );
    foreach ($campos as $campo) {
        $anterior = isset($historico[$campo]) ? (string)$historico[$campo] : '';
        $actual = isset($nuevo[$campo]) ? (string)$nuevo[$campo] : '';
        if ($anterior !== $actual) {
            return true;
        }
    }
    return false;
}

function trabajoLaboratorioHistoricoBuscarEventoIdempotente(
    $mysqli,
    $idHistorico,
    $tipoEvento,
    $clave,
    $payloadHash
) {
    $stmt = $mysqli->prepare(
        'SELECT id,tipo_evento,payload_hash,version_resultante '
        .'FROM trabajo_laboratorio_historico_evento '
        .'WHERE id_historicoFK=? AND clave_idempotencia=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('idempotencia_historica_no_disponible', 'No se pudo comprobar la operacion historica.');
    }
    $stmt->bind_param('is', $idHistorico, $clave);
    if (!$stmt->execute()) {
        $stmt->close();
        trabajoLaboratorioLanzar('idempotencia_historica_no_disponible', 'No se pudo comprobar la operacion historica.');
    }
    $evento = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$evento) {
        return null;
    }
    if ((string)$evento['tipo_evento'] !== (string)$tipoEvento
        || !hash_equals((string)$evento['payload_hash'], (string)$payloadHash)) {
        trabajoLaboratorioLanzar(
            'clave_idempotencia_reutilizada',
            'La clave de idempotencia historica ya fue utilizada con otros datos.'
        );
    }
    return $evento;
}

function trabajoLaboratorioHistoricoInsertarEvento(
    $mysqli,
    $anterior,
    $nuevo,
    $tipoEvento,
    $codUsuario,
    $justificacion,
    $clave,
    $payloadHash,
    $versionResultante,
    $metadata = array()
) {
    $metadataBase = array(
        'fuente_hash' => isset($anterior['fuente_hash']) ? $anterior['fuente_hash'] : null,
        'fecha_objetivo_anterior' => isset($anterior['fecha_objetivo']) ? $anterior['fecha_objetivo'] : null,
        'fecha_objetivo_nueva' => isset($nuevo['fecha_objetivo']) ? $nuevo['fecha_objetivo'] : null,
        'fecha_retiro_anterior' => isset($anterior['fecha_retiro_declarada'])
            ? $anterior['fecha_retiro_declarada'] : null,
        'fecha_retiro_nueva' => isset($nuevo['fecha_retiro_declarada'])
            ? $nuevo['fecha_retiro_declarada'] : null,
        'fecha_entrega_anterior' => isset($anterior['fecha_entrega_declarada'])
            ? $anterior['fecha_entrega_declarada'] : null,
        'fecha_entrega_nueva' => isset($nuevo['fecha_entrega_declarada'])
            ? $nuevo['fecha_entrega_declarada'] : null,
        'fecha_situacion_anterior' => isset($anterior['fecha_situacion_declarada'])
            ? $anterior['fecha_situacion_declarada'] : null,
        'fecha_situacion_nueva' => isset($nuevo['fecha_situacion_declarada'])
            ? $nuevo['fecha_situacion_declarada'] : null
    );
    foreach ((array)$metadata as $claveMetadata => $valorMetadata) {
        $metadataBase[$claveMetadata] = $valorMetadata;
    }
    $metadataJson = json_encode(trabajoLaboratorioUtf8($metadataBase));
    $justificacionBd = trabajoLaboratorioTextoBaseDatos($justificacion, 750);
    $valores = array(
        intval($anterior['id']),
        trabajoLaboratorioTextoBaseDatos($tipoEvento, 50),
        $anterior['estado_convalidacion'],
        $nuevo['estado_convalidacion'],
        $anterior['estado_declarado'],
        $nuevo['estado_declarado'],
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($anterior['cod_detalle_ventaFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($nuevo['cod_detalle_ventaFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($anterior['cod_mecanico_dental_declaradoFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($nuevo['cod_mecanico_dental_declaradoFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($anterior['cod_tecnico_usuarioFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($nuevo['cod_tecnico_usuarioFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($anterior['cod_custodio_actualFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($nuevo['cod_custodio_actualFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($anterior['cod_local_declaradoFK']),
        trabajoLaboratorioHistoricoNormalizarEnteroNullable($nuevo['cod_local_declaradoFK']),
        intval($codUsuario),
        $justificacionBd,
        $metadataJson,
        $clave,
        $payloadHash,
        intval($versionResultante)
    );
    $stmt = $mysqli->prepare(
        'INSERT INTO trabajo_laboratorio_historico_evento '
        .'(id_historicoFK,tipo_evento,estado_convalidacion_anterior,estado_convalidacion_nuevo,'
        .'estado_declarado_anterior,estado_declarado_nuevo,cod_detalle_venta_anteriorFK,'
        .'cod_detalle_venta_nuevoFK,cod_mecanico_dental_anteriorFK,cod_mecanico_dental_nuevoFK,'
        .'cod_tecnico_usuario_anteriorFK,cod_tecnico_usuario_nuevoFK,cod_custodio_anteriorFK,'
        .'cod_custodio_nuevoFK,cod_local_anteriorFK,cod_local_nuevoFK,fecha_servidor,'
        .'cod_usuario_actorFK,justificacion,metadata_json,clave_idempotencia,payload_hash,version_resultante) '
        .'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?)'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('evento_historico_no_guardado', 'No se pudo preparar la trazabilidad historica.');
    }
    trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valores)), $valores);
    if (!$stmt->execute()) {
        $errno = intval($stmt->errno);
        $stmt->close();
        if ($errno === 1062) {
            trabajoLaboratorioLanzar('operacion_historica_repetida', 'La operacion historica ya fue registrada.');
        }
        trabajoLaboratorioLanzar('evento_historico_no_guardado', 'No se pudo guardar la trazabilidad historica.');
    }
    $idEvento = intval($stmt->insert_id);
    $stmt->close();
    return $idEvento;
}

function trabajoLaboratorioHistoricoActualizarDeclaracion(
    $mysqli,
    $historico,
    $nuevo,
    $codUsuario,
    $justificacion,
    $esConvalidacion
) {
    $versionAnterior = intval($historico['version']);
    $versionNueva = $versionAnterior + 1;
    $justificacionBd = trabajoLaboratorioTextoBaseDatos($justificacion, 750);
    $estadoConvalidacion = $nuevo['estado_convalidacion'];
    $estadoDeclarado = $nuevo['estado_declarado'];
    $origenEstado = $nuevo['origen_estado'];
    $codDetalle = $nuevo['cod_detalle_ventaFK'];
    $codMecanico = intval($nuevo['cod_mecanico_dental_declaradoFK']);
    $codTecnico = $nuevo['cod_tecnico_usuarioFK'];
    $codCustodio = $nuevo['cod_custodio_actualFK'];
    $codLocal = intval($nuevo['cod_local_declaradoFK']);
    $fechaObjetivo = $nuevo['fecha_objetivo'];
    $fechaRetiro = $nuevo['fecha_retiro_declarada'];
    $fechaEntrega = $nuevo['fecha_entrega_declarada'];
    $fechaSituacion = $nuevo['fecha_situacion_declarada'];
    $idHistorico = intval($historico['id']);
    if ($esConvalidacion) {
        $stmt = $mysqli->prepare(
            'UPDATE trabajo_laboratorio_historico SET estado_convalidacion=?,estado_declarado=?,'
            .'origen_estado=?,cod_detalle_ventaFK=?,cod_mecanico_dental_declaradoFK=?,'
            .'cod_tecnico_usuarioFK=?,cod_custodio_actualFK=?,cod_local_declaradoFK=?,'
            .'fecha_objetivo=?,fecha_retiro_declarada=?,fecha_entrega_declarada=?,'
            .'fecha_situacion_declarada=?,justificacion_ultima=?,fecha_convalidacion=NOW(),'
            .'cod_usuarioFK_convalida=?,fecha_actualizacion=NOW(),cod_usuarioFK_update=?,version=? '
            .'WHERE id=? AND version=? LIMIT 1'
        );
        $valores = array(
            $estadoConvalidacion, $estadoDeclarado, $origenEstado, $codDetalle, $codMecanico,
            $codTecnico, $codCustodio, $codLocal, $fechaObjetivo, $fechaRetiro, $fechaEntrega,
            $fechaSituacion, $justificacionBd, intval($codUsuario), intval($codUsuario),
            $versionNueva, $idHistorico, $versionAnterior
        );
    } else {
        $stmt = $mysqli->prepare(
            'UPDATE trabajo_laboratorio_historico SET estado_convalidacion=?,estado_declarado=?,'
            .'origen_estado=?,cod_detalle_ventaFK=?,cod_mecanico_dental_declaradoFK=?,'
            .'cod_tecnico_usuarioFK=?,cod_custodio_actualFK=?,cod_local_declaradoFK=?,'
            .'fecha_objetivo=?,fecha_retiro_declarada=?,fecha_entrega_declarada=?,'
            .'fecha_situacion_declarada=?,justificacion_ultima=?,fecha_actualizacion=NOW(),'
            .'cod_usuarioFK_update=?,version=? WHERE id=? AND version=? LIMIT 1'
        );
        $valores = array(
            $estadoConvalidacion, $estadoDeclarado, $origenEstado, $codDetalle, $codMecanico,
            $codTecnico, $codCustodio, $codLocal, $fechaObjetivo, $fechaRetiro, $fechaEntrega,
            $fechaSituacion, $justificacionBd, intval($codUsuario), $versionNueva,
            $idHistorico, $versionAnterior
        );
    }
    if (!$stmt) {
        trabajoLaboratorioLanzar('declaracion_historica_no_guardada', 'No se pudo preparar la declaracion historica.');
    }
    trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valores)), $valores);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        trabajoLaboratorioLanzar(
            'version_historica_desactualizada',
            'El trabajo historico cambio antes de guardar. Actualice la ficha y vuelva a intentarlo.'
        );
    }
    $stmt->close();
    return $versionNueva;
}

function trabajoLaboratorioHistoricoEjecutarDeclaracion($mysqli, $codUsuario, $entrada, $modo)
{
    trabajoLaboratorioHistoricoExigirEstructura($mysqli);
    trabajoLaboratorioHistoricoExigirAuditor($mysqli, $codUsuario);
    $idHistorico = trabajoLaboratorioHistoricoIdEntrada($entrada);
    if ($idHistorico <= 0) {
        trabajoLaboratorioLanzar('historico_requerido', 'Seleccione el trabajo historico que desea actualizar.');
    }
    $justificacion = trabajoLaboratorioHistoricoJustificacionEntrada($entrada);
    $claveEntrada = isset($entrada['clave_idempotencia']) ? $entrada['clave_idempotencia']
        : (isset($entrada['idempotency_key']) ? $entrada['idempotency_key'] : '');
    $clave = trabajoLaboratorioNormalizarClave($claveEntrada);
    $payloadHash = trabajoLaboratorioHashPayload($entrada);
    $esConvalidacion = $modo === 'convalidar';
    $tipoEvento = $esConvalidacion ? 'convalidacion_administrativa' : 'rectificacion_administrativa';
    $codigoRespuesta = $esConvalidacion
        ? 'convalidacion_historica_registrada' : 'rectificacion_historica_registrada';
    $mensajeRespuesta = $esConvalidacion
        ? 'La situacion historica fue convalidada por Administracion.'
        : 'La situacion historica fue rectificada y quedo registrada en la auditoria.';
    $repetida = false;
    if (!$mysqli->begin_transaction()) {
        trabajoLaboratorioLanzar('transaccion_no_iniciada', 'No se pudo iniciar la actualizacion historica.');
    }
    try {
        $historico = trabajoLaboratorioHistoricoObtenerFila($mysqli, $idHistorico, true);
        if (!$historico) {
            trabajoLaboratorioLanzar('historico_no_encontrado', 'No se encontro el trabajo historico solicitado.');
        }
        $eventoExistente = trabajoLaboratorioHistoricoBuscarEventoIdempotente(
            $mysqli,
            $idHistorico,
            $tipoEvento,
            $clave,
            $payloadHash
        );
        if ($eventoExistente) {
            $repetida = true;
            if (!$mysqli->commit()) {
                trabajoLaboratorioLanzar('operacion_no_confirmada', 'No se pudo confirmar la operacion historica repetida.');
            }
        } else {
            $versionEsperada = trabajoLaboratorioHistoricoVersionEntrada($entrada);
            if ($versionEsperada <= 0 || $versionEsperada !== intval($historico['version'])) {
                trabajoLaboratorioLanzar(
                    'version_historica_desactualizada',
                    'El trabajo historico cambio. Actualice la ficha antes de guardar.',
                    array('version_actual' => intval($historico['version']))
                );
            }
            if ((string)$historico['estado_convalidacion'] === 'integrado_operativo'
                || intval($historico['id_trabajo_laboratorioFK']) > 0) {
                trabajoLaboratorioLanzar(
                    'historico_ya_integrado',
                    'El registro ya pertenece al circuito operativo y no puede rectificarse como historico.'
                );
            }
            if ($esConvalidacion
                && (string)$historico['estado_convalidacion'] === 'convalidado_administracion') {
                trabajoLaboratorioLanzar(
                    'historico_ya_convalidado',
                    'El registro ya fue convalidado. Utilice la accion de rectificacion.'
                );
            }
            if (!$esConvalidacion
                && (string)$historico['estado_convalidacion'] !== 'convalidado_administracion') {
                trabajoLaboratorioLanzar(
                    'historico_no_convalidado',
                    'Convalide primero la situacion historica antes de rectificarla.'
                );
            }
            $nuevo = trabajoLaboratorioHistoricoPrepararDeclaracion($mysqli, $historico, $entrada);
            if (!$esConvalidacion && !trabajoLaboratorioHistoricoDeclaracionCambio($historico, $nuevo)) {
                trabajoLaboratorioLanzar(
                    'rectificacion_sin_cambios',
                    'No se detectaron cambios para registrar como rectificacion.'
                );
            }
            $versionNueva = trabajoLaboratorioHistoricoActualizarDeclaracion(
                $mysqli,
                $historico,
                $nuevo,
                $codUsuario,
                $justificacion,
                $esConvalidacion
            );
            trabajoLaboratorioHistoricoInsertarEvento(
                $mysqli,
                $historico,
                $nuevo,
                $tipoEvento,
                $codUsuario,
                $justificacion,
                $clave,
                $payloadHash,
                $versionNueva,
                array('declarado_por_administracion' => 1)
            );
            if (!$mysqli->commit()) {
                trabajoLaboratorioLanzar('operacion_no_confirmada', 'No se pudo confirmar la actualizacion historica.');
            }
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }

    $datos = trabajoLaboratorioHistoricoObtenerHistorico($mysqli, $codUsuario, $idHistorico);
    $datos['operacion_repetida'] = $repetida;
    $version = isset($datos['historico']['version']) ? intval($datos['historico']['version']) : null;
    return trabajoLaboratorioRespuesta(true, $codigoRespuesta, $mensajeRespuesta, $datos, $version);
}

function trabajoLaboratorioHistoricoConvalidarHistorico($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioHistoricoEjecutarDeclaracion(
        $mysqli,
        $codUsuario,
        is_array($entrada) ? $entrada : array(),
        'convalidar'
    );
}

function trabajoLaboratorioHistoricoRectificarHistorico($mysqli, $codUsuario, $entrada)
{
    return trabajoLaboratorioHistoricoEjecutarDeclaracion(
        $mysqli,
        $codUsuario,
        is_array($entrada) ? $entrada : array(),
        'rectificar'
    );
}

function trabajoLaboratorioHistoricoModoResolucionEntrada($entrada)
{
    $modo = trabajoLaboratorioNormalizarTexto(
        isset($entrada['modo_resolucion']) ? $entrada['modo_resolucion']
            : (isset($entrada['resolucion']) ? $entrada['resolucion'] : '')
    );
    if (!in_array($modo, array('continuar', 'instalado_entregado'), true)) {
        trabajoLaboratorioLanzar(
            'resolucion_historica_invalida',
            'Seleccione Continuar trabajo o Instalado y entregado.'
        );
    }
    return $modo;
}

function trabajoLaboratorioHistoricoPrepararResolucion(
    $mysqli,
    $codUsuario,
    $historico,
    $entrada
) {
    if (!$historico) {
        trabajoLaboratorioLanzar(
            'historico_no_encontrado',
            'No se encontro el trabajo historico solicitado.'
        );
    }
    $usuario = trabajoLaboratorioHistoricoExigirUsuarioActivo($mysqli, $codUsuario);
    $modo = trabajoLaboratorioHistoricoModoResolucionEntrada($entrada);
    $codDetalle = trabajoLaboratorioEntero(
        isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta']
            : $historico['cod_detalle_ventaFK']
    );
    if ($codDetalle <= 0) {
        $candidatos = trabajoLaboratorioHistoricoCandidatosDetalle($mysqli, $historico);
        if (count($candidatos) === 1) {
            $codDetalle = intval($candidatos[0]['cod_detalle_venta']);
        }
    }
    $detalle = trabajoLaboratorioHistoricoValidarDetalleDeclarado(
        $mysqli,
        $codDetalle > 0 ? $codDetalle : null,
        intval($historico['cod_venta_snapshot'])
    );
    if (!$detalle) {
        trabajoLaboratorioLanzar(
            'tratamiento_exacto_pendiente',
            'Seleccione el tratamiento exacto de la venta antes de resolver el trabajo.'
        );
    }
    if (intval($historico['cod_cliente_snapshot']) > 0
        && intval($detalle['cod_clienteFK']) !== intval($historico['cod_cliente_snapshot'])) {
        trabajoLaboratorioLanzar(
            'cliente_historico_inconsistente',
            'La venta y el paciente historico no coinciden.'
        );
    }
    $stmt = $mysqli->prepare(
        'SELECT id,codigo_visible,estado_derivado FROM trabajo_laboratorio '
        .'WHERE cod_detalle_activo_unico=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'ocupacion_detalle_no_disponible',
            'No se pudo comprobar el tratamiento seleccionado.'
        );
    }
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $ocupado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($ocupado) {
        trabajoLaboratorioLanzar(
            'tratamiento_ocupado',
            (string)$ocupado['estado_derivado'] === 'instalado'
                ? 'El tratamiento seleccionado ya posee un hilo instalado, entregado y cerrado.'
                : 'El tratamiento seleccionado ya posee un trabajo operativo activo.',
            array(
                'id_trabajo' => intval($ocupado['id']),
                'codigo_visible' => trabajoLaboratorioTextoUtf8($ocupado['codigo_visible']),
                'estado_derivado' => trabajoLaboratorioTextoUtf8($ocupado['estado_derivado'])
            )
        );
    }

    $estado = $modo === 'instalado_entregado' ? 'instalado'
        : trabajoLaboratorioNormalizarTexto(
            isset($entrada['estado_continuacion']) ? $entrada['estado_continuacion']
                : (isset($entrada['estado_declarado']) ? $entrada['estado_declarado'] : '')
        );
    $estadosContinuacion = array(
        'pendiente_entrega_mecanico',
        'en_laboratorio',
        'pendiente_revision',
        'ajuste_solicitado',
        'listo_instalacion'
    );
    if ($modo === 'continuar' && !in_array($estado, $estadosContinuacion, true)) {
        trabajoLaboratorioLanzar(
            'estado_continuacion_invalido',
            'Seleccione en que etapa debe continuar el trabajo.'
        );
    }
    if ($modo === 'continuar' && !trabajoLaboratorioDetalleClinicoActivo($detalle)) {
        trabajoLaboratorioLanzar(
            'tratamiento_ya_finalizado',
            'El tratamiento seleccionado ya esta finalizado. Revise si corresponde declararlo instalado y entregado.',
            array(
                'modo_recomendado' => 'instalado_entregado',
                'cod_detalle_venta' => intval($detalle['cod_detalle']),
                'progreso_porcentaje' => isset($detalle['progreso_porcentaje'])
                    ? intval($detalle['progreso_porcentaje']) : 100
            )
        );
    }

    $codLocal = trabajoLaboratorioEntero(
        isset($entrada['cod_local']) ? $entrada['cod_local']
            : (intval($historico['cod_local_declaradoFK']) > 0
                ? $historico['cod_local_declaradoFK']
                : (intval($historico['cod_local_snapshot']) > 0
                    ? $historico['cod_local_snapshot']
                    : (intval($detalle['cod_local']) > 0
                        ? $detalle['cod_local'] : $usuario['cod_localFK'])))
    );
    $local = trabajoLaboratorioHistoricoValidarLocalDeclarado($mysqli, $codLocal);
    if ($modo === 'continuar'
        && trabajoLaboratorioNormalizarTexto($local['estado']) === 'inactivo') {
        trabajoLaboratorioLanzar(
            'local_historico_inactivo',
            'Seleccione una sucursal activa para continuar el seguimiento.'
        );
    }

    $codMecanico = trabajoLaboratorioEntero(
        isset($entrada['cod_mecanico_dental']) ? $entrada['cod_mecanico_dental']
            : (intval($historico['cod_mecanico_dental_declaradoFK']) > 0
                ? $historico['cod_mecanico_dental_declaradoFK']
                : $historico['cod_mecanico_dental_snapshot'])
    );
    $mecanico = null;
    $codTecnico = null;
    if ($codMecanico > 0) {
        $mecanico = trabajoLaboratorioHistoricoValidarMecanicoDeclarado(
            $mysqli,
            $codMecanico
        );
        if (intval($mecanico['cod_usuarioFK']) > 0
            && (string)$mecanico['estado_usuario'] === 'Activo') {
            $codTecnico = intval($mecanico['cod_usuarioFK']);
        }
    } else {
        $codMecanico = null;
    }
    $codTipoTrabajo = trabajoLaboratorioEntero(
        isset($entrada['cod_tipo_trabajo']) ? $entrada['cod_tipo_trabajo']
            : $historico['cod_tipo_trabajo_snapshot']
    );
    if (isset($entrada['cod_tipo_trabajo']) && $codTipoTrabajo > 0) {
        trabajoLaboratorioValidarTipoTrabajo($mysqli, $codTipoTrabajo);
    }
    if ($codTipoTrabajo <= 0) {
        $codTipoTrabajo = null;
    }
    $codEspecialista = trabajoLaboratorioEntero(
        isset($entrada['cod_especialista']) ? $entrada['cod_especialista']
            : $historico['cod_especialista_snapshot']
    );
    if ($codEspecialista > 0 && !trabajoLaboratorioUsuario($mysqli, $codEspecialista)) {
        $codEspecialista = null;
    }
    if ($codEspecialista <= 0) {
        $codEspecialista = null;
    }
    $colorimetro = trabajoLaboratorioTextoEntrada(
        isset($entrada['colorimetro']) ? $entrada['colorimetro']
            : $historico['colorimetro_legacy'],
        30
    );
    $observacionTrabajo = trabajoLaboratorioTextoEntrada(
        isset($entrada['observacion_trabajo']) ? $entrada['observacion_trabajo']
            : $historico['observacion_legacy'],
        1000
    );
    $costo = isset($entrada['costo_estimado']) && trim((string)$entrada['costo_estimado']) !== ''
        ? trabajoLaboratorioEntero($entrada['costo_estimado'])
        : ($historico['costo_legacy'] === null ? null : intval($historico['costo_legacy']));
    if ($costo !== null && $costo < 0) {
        trabajoLaboratorioLanzar(
            'costo_historico_invalido',
            'El costo no puede ser negativo.'
        );
    }

    $fechaSituacion = trabajoLaboratorioHistoricoFechaEntrada(
        isset($entrada['fecha_situacion_declarada'])
            ? $entrada['fecha_situacion_declarada'] : date('Y-m-d H:i:s'),
        'la resolucion'
    );
    if ($fechaSituacion === null) {
        $fechaSituacion = date('Y-m-d H:i:s');
    }
    $fechaObjetivo = trabajoLaboratorioHistoricoFechaEntrada(
        isset($entrada['fecha_objetivo']) ? $entrada['fecha_objetivo']
            : $historico['fecha_objetivo'],
        'el objetivo'
    );
    if ($fechaObjetivo === null) {
        $fechaObjetivo = $modo === 'continuar'
            ? date('Y-m-d H:i:s', strtotime('+30 days'))
            : $fechaSituacion;
    }
    $fechaRetiro = trabajoLaboratorioHistoricoFechaEntrada(
        isset($entrada['fecha_retiro_declarada']) ? $entrada['fecha_retiro_declarada']
            : (trim((string)$historico['fecha_retiro_declarada']) !== ''
                ? $historico['fecha_retiro_declarada'] : $historico['fecha_retiro_snapshot']),
        'el retiro'
    );
    $fechaEntrega = trabajoLaboratorioHistoricoFechaEntrada(
        isset($entrada['fecha_entrega_declarada']) ? $entrada['fecha_entrega_declarada']
            : (trim((string)$historico['fecha_entrega_declarada']) !== ''
                ? $historico['fecha_entrega_declarada'] : $historico['fecha_entrega_snapshot']),
        'la entrega'
    );
    if ($modo === 'instalado_entregado' && $fechaEntrega === null) {
        $fechaEntrega = $fechaSituacion;
    }

    $condicion = trabajoLaboratorioNormalizarTexto(
        isset($entrada['condicion_pre_entrega']) ? $entrada['condicion_pre_entrega']
            : (isset($entrada['condicion_recepcion']) ? $entrada['condicion_recepcion'] : '')
    );
    $observacionEntrega = trabajoLaboratorioTextoEntrada(
        isset($entrada['observacion_entrega']) ? $entrada['observacion_entrega']
            : (isset($entrada['observacion_recepcion']) ? $entrada['observacion_recepcion'] : ''),
        1000
    );
    $evidencias = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
    $sinFotoHistorica = $modo === 'instalado_entregado'
        && count($evidencias) < 1
        && isset($entrada['sin_foto_historica'])
        && (string)$entrada['sin_foto_historica'] === '1';
    if ($modo === 'instalado_entregado') {
        if (!in_array($condicion, array('conforme', 'con_observaciones'), true)) {
            trabajoLaboratorioLanzar(
                'condicion_entrega_requerida',
                'Indique la situacion del trabajo antes de entregarlo.'
            );
        }
        if ($condicion === 'con_observaciones' && strlen($observacionEntrega) < 3) {
            trabajoLaboratorioLanzar(
                'observacion_entrega_requerida',
                'Describa la situacion observada antes de la entrega.'
            );
        }
        if (count($evidencias) < 1 && !$sinFotoHistorica) {
            trabajoLaboratorioLanzar(
                'foto_entrega_requerida',
                'Agregue una fotografia o declare que no se dispone de fotografia historica.'
            );
        }
    }

    $historico['estado_convalidacion'] = 'convalidado_administracion';
    $historico['cod_cliente_snapshot'] = intval($detalle['cod_clienteFK']);
    $historico['estado_declarado'] = $estado;
    $historico['origen_estado'] = 'declarado_usuario_telar';
    $historico['cod_detalle_ventaFK'] = $codDetalle;
    $historico['cod_mecanico_dental_declaradoFK'] = $codMecanico;
    $historico['cod_tecnico_usuarioFK'] = $codTecnico;
    $historico['cod_tecnico_formal_actual'] = $codTecnico;
    $historico['cod_custodio_actualFK'] = intval($codUsuario);
    $historico['cod_local_declaradoFK'] = $codLocal;
    $historico['fecha_objetivo'] = $fechaObjetivo;
    $historico['fecha_retiro_declarada'] = $fechaRetiro;
    $historico['fecha_entrega_declarada'] = $fechaEntrega;
    $historico['fecha_situacion_declarada'] = $fechaSituacion;
    $historico['detalle_validado'] = $detalle;
    $historico['mecanico_validado'] = $mecanico;
    $historico['local_validado'] = $local;
    $historico['local_declarado'] = $local['Nombre'];
    $historico['custodio_validado'] = $usuario;
    $historico['cod_tipo_trabajo_resuelto'] = $codTipoTrabajo;
    $historico['cod_especialista_resuelto'] = $codEspecialista;
    $historico['colorimetro_resuelto'] = $colorimetro;
    $historico['observacion_trabajo_resuelta'] = $observacionTrabajo;
    $historico['costo_resuelto'] = $costo;
    $historico['modo_resolucion'] = $modo;
    $historico['condicion_pre_entrega'] = $condicion;
    $historico['observacion_entrega'] = $observacionEntrega;
    $historico['sin_foto_historica'] = $sinFotoHistorica;
    $historico['evidencias_resolucion'] = $evidencias;
    return $historico;
}

function trabajoLaboratorioHistoricoFinalizarTratamiento($mysqli, $codDetalle)
{
    $codDetalle = intval($codDetalle);
    $stmt = $mysqli->prepare(
        'SELECT IFNULL(progreso_porcentaje,0) AS progreso_porcentaje '
        .'FROM detalle_venta WHERE cod_detalle=? LIMIT 1 FOR UPDATE'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar(
            'avance_historico_no_disponible',
            'No se pudo validar el avance del tratamiento.'
        );
    }
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) {
        trabajoLaboratorioLanzar(
            'detalle_no_encontrado',
            'No se encontro el tratamiento vinculado.'
        );
    }
    $anterior = max(0, min(100, intval($fila['progreso_porcentaje'])));
    if ($anterior < 100) {
        $porcentaje = 100;
        $stmt = $mysqli->prepare(
            'UPDATE detalle_venta SET progreso_porcentaje=? '
            .'WHERE cod_detalle=? AND IFNULL(progreso_porcentaje,0)=? LIMIT 1'
        );
        if (!$stmt) {
            trabajoLaboratorioLanzar(
                'avance_historico_no_guardado',
                'No se pudo preparar la finalizacion del tratamiento.'
            );
        }
        $stmt->bind_param('iii', $porcentaje, $codDetalle, $anterior);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            trabajoLaboratorioLanzar(
                'avance_historico_no_guardado',
                'El avance del tratamiento cambio antes de finalizarlo.'
            );
        }
        $stmt->close();
    }
    return array(
        'actualizado' => $anterior < 100,
        'porcentaje_anterior' => $anterior,
        'porcentaje' => 100,
        'sin_evolucion_clinica' => true
    );
}

function trabajoLaboratorioHistoricoValidarPromocion($mysqli, $codUsuario, $historico)
{
    if (!$historico) {
        trabajoLaboratorioLanzar('historico_no_encontrado', 'No se encontro el trabajo historico solicitado.');
    }
    if ((string)$historico['estado_convalidacion'] !== 'convalidado_administracion') {
        trabajoLaboratorioLanzar(
            'historico_no_convalidado',
            'La situacion debe ser convalidada por Administracion antes de integrarse.'
        );
    }
    if (intval($historico['id_trabajo_laboratorioFK']) > 0) {
        trabajoLaboratorioLanzar('historico_ya_integrado', 'El registro ya posee un trabajo operativo vinculado.');
    }
    $estado = (string)$historico['estado_declarado'];
    if (!in_array($estado, trabajoLaboratorioHistoricoCodigosEstadosDeclarables(), true)
        || trabajoLaboratorioHistoricoEstadoEsTransitorio($estado)) {
        trabajoLaboratorioLanzar(
            'estado_historico_invalido',
            'La situacion historica no es estable y no puede integrarse.'
        );
    }
    $codDetalle = intval($historico['cod_detalle_ventaFK']);
    $detalle = trabajoLaboratorioHistoricoValidarDetalleDeclarado(
        $mysqli,
        $codDetalle > 0 ? $codDetalle : null,
        intval($historico['cod_venta_snapshot'])
    );
    if (!$detalle) {
        trabajoLaboratorioLanzar(
            'tratamiento_exacto_pendiente',
            'Seleccione el tratamiento exacto antes de integrar el trabajo.'
        );
    }
    if (intval($detalle['cod_clienteFK']) !== intval($historico['cod_cliente_snapshot'])) {
        trabajoLaboratorioLanzar(
            'cliente_historico_inconsistente',
            'La venta y el paciente historico no coinciden. Revise el registro antes de integrarlo.'
        );
    }
    if (!trabajoLaboratorioHistoricoEstadoEsFinal($estado)) {
        if (in_array(
            trabajoLaboratorioNormalizarTexto($detalle['estado']),
            array('eliminado', 'inactivo', 'anulado'),
            true
        ) || in_array(
            trabajoLaboratorioNormalizarTexto($detalle['estado_venta']),
            array('eliminado', 'inactivo', 'anulado'),
            true
        )) {
            trabajoLaboratorioLanzar(
                'tratamiento_historico_inactivo',
                'Un trabajo historico aun activo necesita una venta y un tratamiento vigentes.'
            );
        }
    }
    $stmt = $mysqli->prepare(
        'SELECT id,codigo_visible,estado_derivado FROM trabajo_laboratorio '
        .'WHERE cod_detalle_activo_unico=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('ocupacion_detalle_no_disponible', 'No se pudo comprobar el tratamiento seleccionado.');
    }
    $stmt->bind_param('i', $codDetalle);
    $stmt->execute();
    $ocupado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($ocupado && $estado !== 'cancelado') {
        trabajoLaboratorioLanzar(
            'tratamiento_ocupado',
            (string)$ocupado['estado_derivado'] === 'instalado'
                ? 'El tratamiento seleccionado ya posee un hilo instalado, entregado y cerrado.'
                : 'El tratamiento seleccionado ya posee un trabajo operativo activo.',
            array(
                'id_trabajo' => intval($ocupado['id']),
                'codigo_visible' => trabajoLaboratorioTextoUtf8($ocupado['codigo_visible']),
                'estado_derivado' => trabajoLaboratorioTextoUtf8($ocupado['estado_derivado'])
            )
        );
    }
    $stmt = $mysqli->prepare(
        'SELECT id,codigo_visible FROM trabajo_laboratorio '
        .'WHERE cod_trabajo_mecanico_legacyFK=? LIMIT 1'
    );
    if (!$stmt) {
        trabajoLaboratorioLanzar('vinculo_legacy_no_disponible', 'No se pudo comprobar el vinculo historico.');
    }
    $idLegacy = intval($historico['cod_trabajo_mecanico_legacyFK']);
    $stmt->bind_param('i', $idLegacy);
    $stmt->execute();
    $vinculado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($vinculado) {
        trabajoLaboratorioLanzar(
            'historico_ya_integrado',
            'El registro legacy ya posee un trabajo operativo vinculado.',
            array('id_trabajo' => intval($vinculado['id']))
        );
    }

    $codMecanico = intval($historico['cod_mecanico_dental_declaradoFK']);
    $mecanico = trabajoLaboratorioHistoricoValidarMecanicoDeclarado($mysqli, $codMecanico);
    if ((string)$mecanico['estado'] !== 'activo'
        || intval($mecanico['cod_usuarioFK']) <= 0
        || (string)$mecanico['estado_usuario'] !== 'Activo') {
        trabajoLaboratorioLanzar(
            'cuenta_mecanico_pendiente',
            'El mecanico declarado debe estar activo y vinculado a una cuenta Telar activa.'
        );
    }
    $codTecnico = intval($mecanico['cod_usuarioFK']);
    foreach (array(
        'VERTRABAJOSLABORATORIO',
        'RECIBIRTRABAJOLABORATORIO',
        'ENTREGARTRABAJOLABORATORIO'
    ) as $permisoTecnico) {
        if (!trabajoLaboratorioTienePermiso($mysqli, $codTecnico, $permisoTecnico)) {
            trabajoLaboratorioLanzar(
                'permisos_mecanico_pendientes',
                'La cuenta del mecanico necesita permisos de acceso, recepcion y entrega.'
            );
        }
    }
    $codLocal = intval($historico['cod_local_declaradoFK']);
    $local = trabajoLaboratorioHistoricoValidarLocalDeclarado($mysqli, $codLocal);
    if (trabajoLaboratorioNormalizarTexto($local['estado']) === 'inactivo') {
        trabajoLaboratorioLanzar(
            'local_historico_inactivo',
            'Seleccione una sucursal activa para el seguimiento operativo.'
        );
    }
    $codCustodio = intval($historico['cod_custodio_actualFK']);
    $custodio = trabajoLaboratorioHistoricoValidarCustodioDeclarado(
        $mysqli,
        $codCustodio > 0 ? $codCustodio : null
    );
    if (!$custodio || (string)$custodio['estado'] !== 'Activo') {
        trabajoLaboratorioLanzar(
            'custodio_historico_pendiente',
            'Seleccione un custodio con cuenta activa.'
        );
    }
    if (!trabajoLaboratorioTienePermiso($mysqli, $codCustodio, 'VERTRABAJOSLABORATORIO')
        || !trabajoLaboratorioTienePermiso($mysqli, $codCustodio, 'RECIBIRTRABAJOLABORATORIO')) {
        trabajoLaboratorioLanzar(
            'permisos_custodio_pendientes',
            'El custodio necesita permisos para ver y recibir trabajos de laboratorio.'
        );
    }
    if ($estado === 'en_laboratorio' && $codCustodio !== $codTecnico) {
        trabajoLaboratorioLanzar(
            'custodio_mecanico_inconsistente',
            'Un trabajo en laboratorio debe quedar bajo custodia del mecanico declarado.'
        );
    }
    if ($estado !== 'en_laboratorio') {
        if ($codCustodio === $codTecnico
            || intval($custodio['es_mecanico']) > 0
            || intval($custodio['cod_localFK']) !== $codLocal) {
            trabajoLaboratorioLanzar(
                'custodio_clinica_inconsistente',
                'Para esa situacion, el custodio debe pertenecer a la sucursal declarada.'
            );
        }
    }
    if (trim((string)$historico['fecha_objetivo']) === '') {
        trabajoLaboratorioLanzar(
            'fecha_objetivo_pendiente',
            'Defina una fecha objetivo antes de integrar el trabajo.'
        );
    }
    if (trim((string)$historico['fecha_creacion_snapshot']) === '') {
        trabajoLaboratorioLanzar(
            'fecha_original_pendiente',
            'No se pudo comprobar la fecha de creacion original.'
        );
    }
    if (trim((string)$historico['fecha_situacion_declarada']) === '') {
        trabajoLaboratorioLanzar(
            'fecha_situacion_pendiente',
            'Indique desde cuando corresponde la situacion declarada.'
        );
    }
    if (intval($historico['cod_usuario_creador_snapshot']) <= 0) {
        trabajoLaboratorioLanzar(
            'autor_original_pendiente',
            'No se pudo comprobar el usuario que creo el registro original.'
        );
    }
    $stmt = $mysqli->prepare('SELECT 1 FROM usuario WHERE cod_usuario=? LIMIT 1');
    if (!$stmt) {
        trabajoLaboratorioLanzar('autor_original_no_disponible', 'No se pudo validar la autoria original.');
    }
    $autorOriginal = intval($historico['cod_usuario_creador_snapshot']);
    $stmt->bind_param('i', $autorOriginal);
    $stmt->execute();
    $autorExiste = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if (!$autorExiste) {
        trabajoLaboratorioLanzar(
            'autor_original_no_disponible',
            'El usuario creador original ya no existe en el catalogo de usuarios.'
        );
    }
    $codEspecialista = trabajoLaboratorioHistoricoNormalizarEnteroNullable(
        $historico['cod_especialista_snapshot']
    );
    if ($codEspecialista !== null) {
        $stmt = $mysqli->prepare('SELECT 1 FROM usuario WHERE cod_usuario=? LIMIT 1');
        if (!$stmt) {
            trabajoLaboratorioLanzar(
                'especialista_original_no_disponible',
                'No se pudo validar al profesional historico.'
            );
        }
        $stmt->bind_param('i', $codEspecialista);
        $stmt->execute();
        $especialistaExiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$especialistaExiste) {
            trabajoLaboratorioLanzar(
                'especialista_original_no_disponible',
                'El profesional historico ya no existe en el catalogo de usuarios.'
            );
        }
    }
    if (!isset($historico['estado_legacy_actual']) || $historico['estado_legacy_actual'] === null) {
        trabajoLaboratorioLanzar(
            'fuente_legacy_no_disponible',
            'No se encontro el registro legacy utilizado como fuente.'
        );
    }
    $historico['detalle_validado'] = $detalle;
    $historico['mecanico_validado'] = $mecanico;
    $historico['local_validado'] = $local;
    $historico['custodio_validado'] = $custodio;
    $historico['cod_tecnico_formal_actual'] = $codTecnico;
    return $historico;
}

function trabajoLaboratorioHistoricoAsegurarHiloVenta($mysqli, $codUsuario, $codVenta)
{
    $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($codVenta), false);
    if ($hilo) {
        return $hilo;
    }
    if (!function_exists('asegurarEstructuraSeguimientoPacienteInterConsulta')
        || !function_exists('seguimientoPacienteAsegurarHiloPorVentaConConexion')) {
        $rutaHelper = __DIR__.'/interconsulta_seguimiento_paciente_helper.php';
        if (is_file($rutaHelper)) {
            require_once $rutaHelper;
        }
    }
    if (!function_exists('asegurarEstructuraSeguimientoPacienteInterConsulta')
        || !function_exists('seguimientoPacienteAsegurarHiloPorVentaConConexion')) {
        trabajoLaboratorioLanzar(
            'hilo_maestro_no_disponible',
            'No esta disponible el proceso seguro para preparar el Hilo maestro.'
        );
    }
    /* La posible instalacion de estructura ocurre fuera de la transaccion para
       que ningun DDL confirme parcialmente la promocion del trabajo. */
    if (!asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli)) {
        trabajoLaboratorioLanzar(
            'hilo_maestro_no_disponible',
            'No esta disponible la estructura del Hilo maestro.'
        );
    }
    if (!$mysqli->begin_transaction()) {
        trabajoLaboratorioLanzar('transaccion_no_iniciada', 'No se pudo preparar el Hilo maestro.');
    }
    try {
        $resultado = seguimientoPacienteAsegurarHiloPorVentaConConexion(
            $mysqli,
            intval($codVenta),
            intval($codUsuario),
            'trabajo_laboratorio_historico'
        );
        if (empty($resultado['ok'])) {
            trabajoLaboratorioLanzar(
                'hilo_maestro_no_preparado',
                'No se pudo preparar el Hilo maestro de la venta historica.'
            );
        }
        $hilo = trabajoLaboratorioObtenerHiloUnicoVenta($mysqli, intval($codVenta), true);
        if (!$hilo) {
            trabajoLaboratorioLanzar(
                'hilo_maestro_no_vinculado',
                'El Hilo maestro no quedo vinculado a la venta historica.'
            );
        }
        if (!$mysqli->commit()) {
            trabajoLaboratorioLanzar('hilo_maestro_no_confirmado', 'No se pudo confirmar el Hilo maestro.');
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        throw $e;
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
    return $hilo;
}

function trabajoLaboratorioHistoricoInsertarUbicaciones(
    $mysqli,
    $idTrabajo,
    $codDetalle,
    $codUsuario
) {
    $ubicaciones = trabajoLaboratorioObtenerUbicacionesDetalle($mysqli, intval($codDetalle));
    foreach ($ubicaciones as $ubicacion) {
        $valores = array(
            intval($idTrabajo),
            intval($ubicacion['id']),
            trabajoLaboratorioTextoBaseDatos($ubicacion['pieza'], 5),
            trabajoLaboratorioTextoBaseDatos(json_encode($ubicacion['piezas'])),
            trabajoLaboratorioTextoBaseDatos(json_encode($ubicacion['superficies'])),
            trabajoLaboratorioTextoBaseDatos($ubicacion['denticion'], 20),
            trabajoLaboratorioTextoBaseDatos($ubicacion['arcada'], 30),
            trabajoLaboratorioTextoBaseDatos($ubicacion['cuadrante'], 30),
            !empty($ubicacion['boca_completa']) ? 1 : 0,
            trabajoLaboratorioTextoBaseDatos($ubicacion['alcance'], 40),
            intval($codUsuario)
        );
        $stmt = $mysqli->prepare(
            'INSERT INTO trabajo_laboratorio_ubicacion '
            .'(id_trabajoFK,id_odontograma_link_origenFK,pieza,piezas_json,superficies_json,'
            .'denticion,arcada,cuadrante,boca_completa,alcance_odontologico,fecha_creacion,'
            .'cod_usuarioFK_create) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?)'
        );
        if (!$stmt) {
            trabajoLaboratorioLanzar('ubicacion_historica_no_guardada', 'No se pudo preparar la ubicacion clinica.');
        }
        trabajoLaboratorioVincularParametros($stmt, str_repeat('s', count($valores)), $valores);
        if (!$stmt->execute()) {
            $stmt->close();
            trabajoLaboratorioLanzar(
                'ubicacion_historica_no_guardada',
                'No se pudo conservar la ubicacion clinica existente.'
            );
        }
        $stmt->close();
    }
    return count($ubicaciones);
}

function trabajoLaboratorioHistoricoPromoverHistorico($mysqli, $codUsuario, $entrada)
{
    trabajoLaboratorioHistoricoExigirEstructura($mysqli);
    $entrada = is_array($entrada) ? $entrada : array();
    $esResolucion = isset($entrada['modo_resolucion'])
        || isset($entrada['resolucion']);
    $modoResolucion = $esResolucion
        ? trabajoLaboratorioHistoricoModoResolucionEntrada($entrada) : '';
    if ($esResolucion) {
        trabajoLaboratorioHistoricoExigirUsuarioActivo($mysqli, $codUsuario);
    } else {
        trabajoLaboratorioHistoricoExigirAuditor($mysqli, $codUsuario);
    }
    $idHistorico = trabajoLaboratorioHistoricoIdEntrada($entrada);
    if ($idHistorico <= 0) {
        trabajoLaboratorioLanzar('historico_requerido', 'Seleccione el trabajo historico que desea integrar.');
    }
    $justificacion = trabajoLaboratorioHistoricoJustificacionEntrada($entrada);
    $claveEntrada = isset($entrada['clave_idempotencia']) ? $entrada['clave_idempotencia']
        : (isset($entrada['idempotency_key']) ? $entrada['idempotency_key'] : '');
    $clave = trabajoLaboratorioNormalizarClave($claveEntrada);
    $payloadHash = trabajoLaboratorioHashPayload($entrada);

    /* El Hilo se prepara antes del comando principal porque su helper puede
       verificar/crear estructura y gestiona una transaccion independiente. */
    $preliminar = trabajoLaboratorioHistoricoObtenerFilaDecorada($mysqli, $idHistorico);
    if (!$preliminar) {
        trabajoLaboratorioLanzar('historico_no_encontrado', 'No se encontro el trabajo historico solicitado.');
    }
    if (intval($preliminar['id_trabajo_laboratorioFK']) <= 0
        && (string)$preliminar['estado_convalidacion'] !== 'integrado_operativo') {
        $versionEsperada = trabajoLaboratorioHistoricoVersionEntrada($entrada);
        if ($versionEsperada <= 0 || $versionEsperada !== intval($preliminar['version'])) {
            trabajoLaboratorioLanzar(
                'version_historica_desactualizada',
                'El trabajo historico cambio. Actualice la ficha antes de integrarlo.',
                array('version_actual' => intval($preliminar['version']))
            );
        }
        if ($esResolucion) {
            trabajoLaboratorioHistoricoPrepararResolucion(
                $mysqli,
                $codUsuario,
                $preliminar,
                $entrada
            );
        } else {
            trabajoLaboratorioHistoricoValidarPromocion($mysqli, $codUsuario, $preliminar);
        }
        trabajoLaboratorioHistoricoAsegurarHiloVenta(
            $mysqli,
            $codUsuario,
            intval($preliminar['cod_venta_snapshot'])
    );
}

    return trabajoLaboratorioEjecutarComando(
        $mysqli,
        $codUsuario,
        $esResolucion ? 'resolverHistorico' : 'promoverHistorico',
        $entrada,
        function ($idIdempotencia, $contexto) use (
            $mysqli,
            $codUsuario,
            $entrada,
            $idHistorico,
            $justificacion,
            $clave,
            $payloadHash,
            $esResolucion,
            $modoResolucion
        ) {
            $bloqueado = trabajoLaboratorioHistoricoObtenerFila($mysqli, $idHistorico, true);
            if (!$bloqueado) {
                trabajoLaboratorioLanzar('historico_no_encontrado', 'No se encontro el trabajo historico solicitado.');
            }
            $tipoEventoHistorico = $esResolucion
                ? ($modoResolucion === 'instalado_entregado'
                    ? 'instalacion_historica_declarada'
                    : 'continuacion_historica')
                : 'promovido_operativo';
            $eventoHistoricoExistente = trabajoLaboratorioHistoricoBuscarEventoIdempotente(
                $mysqli,
                $idHistorico,
                $tipoEventoHistorico,
                $clave,
                $payloadHash
            );
            if ($eventoHistoricoExistente) {
                trabajoLaboratorioLanzar(
                    'operacion_historica_repetida',
                    'La resolucion historica ya fue registrada.'
                );
            }
            $versionEsperada = trabajoLaboratorioHistoricoVersionEntrada($entrada);
            if ($versionEsperada <= 0 || $versionEsperada !== intval($bloqueado['version'])) {
                trabajoLaboratorioLanzar(
                    'version_historica_desactualizada',
                    'El trabajo historico cambio. Actualice la ficha antes de integrarlo.',
                    array('version_actual' => intval($bloqueado['version']))
                );
            }
            if ((string)$bloqueado['estado_convalidacion'] === 'integrado_operativo'
                || intval($bloqueado['id_trabajo_laboratorioFK']) > 0) {
                trabajoLaboratorioLanzar('historico_ya_integrado', 'El registro ya posee un trabajo operativo vinculado.');
            }
            $historicoAnterior = trabajoLaboratorioHistoricoObtenerFilaDecorada(
                $mysqli,
                $idHistorico
            );
            $historico = $esResolucion
                ? trabajoLaboratorioHistoricoPrepararResolucion(
                    $mysqli,
                    $codUsuario,
                    $historicoAnterior,
                    $entrada
                )
                : trabajoLaboratorioHistoricoValidarPromocion(
                    $mysqli,
                    $codUsuario,
                    $historicoAnterior
                );
            $codDetalle = intval($historico['cod_detalle_ventaFK']);
            $detalle = trabajoLaboratorioObtenerDetalleClinico($mysqli, $codDetalle, true);
            if (!$detalle || intval($detalle['cod_ventaFK']) !== intval($historico['cod_venta_snapshot'])) {
                trabajoLaboratorioLanzar(
                    'detalle_historico_invalido',
                    'El tratamiento seleccionado ya no corresponde a la venta historica.'
                );
            }
            $hilo = trabajoLaboratorioObtenerHiloUnicoVenta(
                $mysqli,
                intval($historico['cod_venta_snapshot']),
                true
            );
            if (!$hilo) {
                trabajoLaboratorioLanzar(
                    'hilo_maestro_no_vinculado',
                    'La venta historica no tiene un Hilo maestro activo.'
                );
            }
            $codTecnico = trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                $historico['cod_tecnico_formal_actual']
            );
            if (!$esResolucion) {
                $tecnico = trabajoLaboratorioObtenerTecnicoFormal(
                    $mysqli,
                    intval($codTecnico),
                    true
                );
                if (!$tecnico
                    || intval($tecnico['cod_mecanico_dental'])
                        !== intval($historico['cod_mecanico_dental_declaradoFK'])) {
                    trabajoLaboratorioLanzar(
                        'cuenta_mecanico_pendiente',
                        'La vinculacion formal del mecanico cambio antes de integrar el trabajo.'
                    );
                }
            }

            $ventaCodigo = array(
                'cod_venta' => intval($historico['cod_venta_snapshot']),
                'num_factura' => $historico['num_factura'],
                'puntoexpedicion' => $historico['puntoexpedicion'],
                'nroventa' => $detalle['nroventa'],
                'cod_local' => intval($historico['cod_local_declaradoFK']),
                'nombre_local' => $historico['local_declarado']
            );
            $numeroVenta = trabajoLaboratorioTextoBaseDatos(
                trabajoLaboratorioNumeroVenta($ventaCodigo),
                45
            );
            $sigla = trabajoLaboratorioTextoBaseDatos(
                trabajoLaboratorioSiglaLocal(
                    intval($historico['cod_local_declaradoFK']),
                    $historico['local_declarado']
                ),
                10
            );
            $estado = (string)$historico['estado_declarado'];
            $esFinal = trabajoLaboratorioHistoricoEstadoEsFinal($estado);
            /* Un tratamiento instalado y entregado conserva su reserva: el
               hilo queda cerrado y no puede originar otro trabajo. Solo una
               cancelacion libera el detalle para una decision posterior. */
            $codDetalleActivo = $estado === 'cancelado' ? null : $codDetalle;
            $fechaSituacion = trim((string)$historico['fecha_situacion_declarada']) !== ''
                ? $historico['fecha_situacion_declarada'] : null;
            $fechaCompletado = in_array($estado, array('listo_instalacion', 'instalado'), true)
                ? ($fechaSituacion !== null ? $fechaSituacion : $historico['fecha_entrega_declarada'])
                : null;
            $fechaInstalado = $estado === 'instalado' ? $fechaSituacion : null;
            $fechaCancelado = $estado === 'cancelado' ? $fechaSituacion : null;
            $motivoCancelacion = $estado === 'cancelado'
                ? trabajoLaboratorioTextoBaseDatos($justificacion, 500) : null;
            $ahora = date('Y-m-d H:i:s');
            $valoresTrabajo = array(
                intval($historico['cod_trabajo_mecanico_legacyFK']),
                intval($historico['cod_venta_snapshot']),
                $numeroVenta,
                $sigla,
                $codDetalle,
                $codDetalleActivo,
                intval($historico['cod_cliente_snapshot']),
                trabajoLaboratorioTextoBaseDatos($detalle['cod_productoFK'], 45),
                $esResolucion
                    ? $historico['cod_tipo_trabajo_resuelto']
                    : trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                        $historico['cod_tipo_trabajo_snapshot']
                    ),
                null,
                null,
                intval($hilo['cod_interConsultaFK']),
                intval($historico['cod_local_declaradoFK']),
                trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                    $historico['cod_mecanico_dental_declaradoFK']
                ),
                $esResolucion
                    ? $historico['cod_especialista_resuelto']
                    : trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                        $historico['cod_especialista_snapshot']
                    ),
                $codTecnico,
                intval($historico['cod_custodio_actualFK']),
                1,
                trabajoLaboratorioTextoBaseDatos($estado, 40),
                $historico['fecha_objetivo'],
                $historico['fecha_retiro_declarada'],
                $historico['fecha_entrega_declarada'],
                trabajoLaboratorioTextoBaseDatos(
                    $esResolucion
                        ? $historico['colorimetro_resuelto'] : $historico['colorimetro_legacy'],
                    30
                ),
                trabajoLaboratorioTextoBaseDatos(
                    $esResolucion
                        ? $historico['observacion_trabajo_resuelta'] : $historico['observacion_legacy'],
                    1000
                ),
                $esResolucion
                    ? $historico['costo_resuelto']
                    : ($historico['costo_legacy'] === null
                        ? null : intval($historico['costo_legacy'])),
                1,
                trim((string)$historico['fecha_creacion_snapshot']) !== ''
                    ? $historico['fecha_creacion_snapshot'] : $ahora,
                $esResolucion
                    ? intval($codUsuario)
                    : intval($historico['cod_usuario_creador_snapshot']),
                $ahora,
                intval($codUsuario),
                $fechaCompletado,
                $fechaInstalado,
                $fechaCancelado,
                $motivoCancelacion
            );
            $stmt = $mysqli->prepare(
                'INSERT INTO trabajo_laboratorio '
                .'(cod_trabajo_mecanico_legacyFK,cod_ventaFK,numero_venta_snapshot,sigla_local_snapshot,'
                .'cod_detalle_ventaFK,cod_detalle_activo_unico,cod_clienteFK,cod_productoFK,'
                .'cod_tipo_trabajoFK,cod_consulta_origenFK,cod_evolucion_origenFK,cod_interConsultaFK,'
                .'cod_localFK,cod_mecanico_dentalFK,cod_especialistaFK,cod_tecnico_usuarioFK,'
                .'cod_custodio_actualFK,'
                .'ciclo_actual,estado_derivado,fecha_objetivo,fecha_retiro,fecha_entrega,colorimetro,'
                .'instrucciones,costo_estimado,version,fecha_creacion,cod_usuarioFK_create,'
                .'fecha_actualizacion,cod_usuarioFK_update,fecha_completado,fecha_instalado,'
                .'fecha_cancelado,motivo_cancelacion) '
                .'VALUES ('.implode(',', array_fill(0, count($valoresTrabajo), '?')).')'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar('trabajo_historico_no_promovido', 'No se pudo preparar el trabajo operativo.');
            }
            trabajoLaboratorioVincularParametros(
                $stmt,
                str_repeat('s', count($valoresTrabajo)),
                $valoresTrabajo
            );
            if (!$stmt->execute()) {
                $errno = intval($stmt->errno);
                $stmt->close();
                if ($errno === 1062) {
                    trabajoLaboratorioLanzar(
                        'trabajo_historico_conflicto',
                        'El registro o tratamiento fue integrado por otra operacion. Actualice la ficha.'
                    );
                }
                trabajoLaboratorioLanzar('trabajo_historico_no_promovido', 'No se pudo crear el trabajo operativo.');
            }
            $idTrabajo = intval($stmt->insert_id);
            $stmt->close();

            $codigoVisible = trabajoLaboratorioCodigoVisible($ventaCodigo, $idTrabajo);
            $codigoVisibleBd = trabajoLaboratorioTextoBaseDatos($codigoVisible, 100);
            $stmt = $mysqli->prepare('UPDATE trabajo_laboratorio SET codigo_visible=? WHERE id=? LIMIT 1');
            if (!$stmt) {
                trabajoLaboratorioLanzar('codigo_no_guardado', 'No se pudo preparar el codigo trazable.');
            }
            $stmt->bind_param('si', $codigoVisibleBd, $idTrabajo);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar('codigo_no_guardado', 'No se pudo asignar el codigo trazable.');
            }
            $stmt->close();

            $tipoCiclo = 'historico';
            $motivoCiclo = 'migracion_historica';
            $justificacionCiclo = trabajoLaboratorioTextoBaseDatos($justificacion, 500);
            $fechaObjetivo = $historico['fecha_objetivo'];
            $stmt = $mysqli->prepare(
                'INSERT INTO trabajo_laboratorio_ciclo '
                .'(id_trabajoFK,numero_ciclo,tipo,motivo,justificacion,fecha_objetivo,'
                .'cod_usuario_solicitanteFK,fecha_creacion) VALUES (?,1,?,?,?,?,?,NOW())'
            );
            if (!$stmt) {
                trabajoLaboratorioLanzar('ciclo_historico_no_guardado', 'No se pudo preparar el ciclo historico.');
            }
            $stmt->bind_param(
                'issssi',
                $idTrabajo,
                $tipoCiclo,
                $motivoCiclo,
                $justificacionCiclo,
                $fechaObjetivo,
                $codUsuario
            );
            if (!$stmt->execute()) {
                $stmt->close();
                trabajoLaboratorioLanzar('ciclo_historico_no_guardado', 'No se pudo crear el ciclo historico.');
            }
            $idCiclo = intval($stmt->insert_id);
            $stmt->close();

            $cantidadUbicaciones = trabajoLaboratorioHistoricoInsertarUbicaciones(
                $mysqli,
                $idTrabajo,
                $codDetalle,
                $codUsuario
            );
            $trabajo = trabajoLaboratorioObtenerTrabajo($mysqli, $idTrabajo, true);
            if (!$trabajo) {
                trabajoLaboratorioLanzar('trabajo_historico_no_promovido', 'No se pudo recuperar el trabajo integrado.');
            }
            $metadataOperativa = array(
                'id_historico' => $idHistorico,
                'cod_trabajo_mecanico_legacy' => intval($historico['cod_trabajo_mecanico_legacyFK']),
                'estado_legacy_snapshot' => $historico['estado_legacy_snapshot'],
                'estado_declarado' => $estado,
                'origen_estado' => $esResolucion
                    ? 'declarado_usuario_telar' : 'declarado_administracion',
                'fuente_hash' => $historico['fuente_hash'],
                'cod_usuario_creador_original' => intval($historico['cod_usuario_creador_snapshot']),
                'fecha_creacion_original' => $historico['fecha_creacion_snapshot'],
                'cod_especialista_original' => trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                    $historico['cod_especialista_snapshot']
                ),
                'cod_usuario_editor_original' => trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                    $historico['cod_usuario_editor_snapshot']
                ),
                'fecha_edicion_original' => $historico['fecha_edicion_snapshot'],
                'declarado_por_administracion' => $esResolucion ? 0 : 1,
                'resuelto_por_usuario_autenticado' => $esResolucion ? 1 : 0,
                'modo_resolucion' => $esResolucion ? $modoResolucion : 'promocion_administrativa',
                'estado_resultante' => $estado,
                'nodo_custodia' => 1,
                'condicion_recepcion' => $esResolucion
                    ? $historico['condicion_pre_entrega'] : null,
                'condicion_pre_entrega' => $esResolucion
                    ? $historico['condicion_pre_entrega'] : null,
                'observacion_entrega' => $esResolucion
                    ? $historico['observacion_entrega'] : null,
                'sin_foto' => $esResolucion
                    && $modoResolucion === 'instalado_entregado'
                    && !empty($historico['sin_foto_historica']) ? 1 : 0,
                'motivo_sin_foto' => $esResolucion
                    && $modoResolucion === 'instalado_entregado'
                    && !empty($historico['sin_foto_historica'])
                        ? 'foto_historica_no_disponible' : null,
                'detalle_sin_foto' => $esResolucion
                    && $modoResolucion === 'instalado_entregado'
                    && !empty($historico['sin_foto_historica'])
                        ? $justificacion : null,
                'cantidad_evidencias' => $esResolucion
                    ? count($historico['evidencias_resolucion']) : 0,
                'sin_evidencia_historica' => $esResolucion
                    && $modoResolucion === 'instalado_entregado'
                    && count($historico['evidencias_resolucion']) > 0 ? 0 : 1,
                'sin_transferencias_historicas' => 1,
                'ubicaciones_clinicas_reutilizadas' => $cantidadUbicaciones,
                'sin_evolucion_clinica' => $esResolucion
                    && $modoResolucion === 'instalado_entregado' ? 1 : 0,
                'datos_trabajo' => trabajoLaboratorioSnapshotDatosTrabajo($trabajo)
            );
            $tipoEventoOperativo = $esResolucion
                ? ($modoResolucion === 'instalado_entregado'
                    ? 'instalacion_historica_declarada'
                    : 'registro_historico_continuado')
                : 'registro_historico_convalidado';
            $observacionNodo = $esResolucion
                && trim((string)$historico['observacion_entrega']) !== ''
                ? $historico['observacion_entrega'] : $justificacion;
            $idEventoOperativo = trabajoLaboratorioRegistrarEvento(
                $mysqli,
                $trabajo,
                $idCiclo,
                $idIdempotencia,
                $tipoEventoOperativo,
                $codUsuario,
                1,
                $observacionNodo,
                $metadataOperativa,
                null,
                null,
                intval($historico['cod_custodio_actualFK'])
            );
            if ($esResolucion) {
                foreach ($historico['evidencias_resolucion'] as $evidenciaResolucion) {
                    $mediaResolucion = trabajoLaboratorioGuardarMediaProtegida(
                        $evidenciaResolucion,
                        $idTrabajo,
                        $contexto
                    );
                    trabajoLaboratorioInsertarMedia(
                        $mysqli,
                        $trabajo,
                        $idCiclo,
                        $idEventoOperativo,
                        $codUsuario,
                        $mediaResolucion,
                        $modoResolucion === 'instalado_entregado'
                            ? 'instalacion_historica' : 'regularizacion_historica'
                    );
                }
            }
            $avanceHistorico = null;
            if ($esResolucion && $modoResolucion === 'instalado_entregado') {
                $avanceHistorico = trabajoLaboratorioHistoricoFinalizarTratamiento(
                    $mysqli,
                    $codDetalle
                );
            }

            $versionAnterior = intval($historicoAnterior['version']);
            $versionNueva = $versionAnterior + 1;
            $estadoConvalidacionNuevo = 'integrado_operativo';
            $justificacionBd = trabajoLaboratorioTextoBaseDatos($justificacion, 750);
            if ($esResolucion) {
                $stmt = $mysqli->prepare(
                    'UPDATE trabajo_laboratorio_historico SET estado_convalidacion=?,'
                    .'estado_declarado=?,origen_estado=?,cod_detalle_ventaFK=?,'
                    .'cod_mecanico_dental_declaradoFK=?,cod_tecnico_usuarioFK=?,'
                    .'cod_custodio_actualFK=?,cod_local_declaradoFK=?,fecha_objetivo=?,'
                    .'fecha_retiro_declarada=?,fecha_entrega_declarada=?,'
                    .'fecha_situacion_declarada=?,justificacion_ultima=?,'
                    .'id_trabajo_laboratorioFK=?,fecha_convalidacion=COALESCE(fecha_convalidacion,NOW()),'
                    .'cod_usuarioFK_convalida=COALESCE(cod_usuarioFK_convalida,?),'
                    .'fecha_actualizacion=NOW(),cod_usuarioFK_update=?,version=? '
                    .'WHERE id=? AND version=? AND id_trabajo_laboratorioFK IS NULL LIMIT 1'
                );
                $valoresVinculo = array(
                    $estadoConvalidacionNuevo,
                    $historico['estado_declarado'],
                    $historico['origen_estado'],
                    $codDetalle,
                    trabajoLaboratorioHistoricoNormalizarEnteroNullable(
                        $historico['cod_mecanico_dental_declaradoFK']
                    ),
                    $codTecnico,
                    intval($codUsuario),
                    intval($historico['cod_local_declaradoFK']),
                    $historico['fecha_objetivo'],
                    $historico['fecha_retiro_declarada'],
                    $historico['fecha_entrega_declarada'],
                    $historico['fecha_situacion_declarada'],
                    $justificacionBd,
                    $idTrabajo,
                    intval($codUsuario),
                    intval($codUsuario),
                    $versionNueva,
                    $idHistorico,
                    $versionAnterior
                );
            } else {
                $stmt = $mysqli->prepare(
                    'UPDATE trabajo_laboratorio_historico SET estado_convalidacion=?,'
                    .'id_trabajo_laboratorioFK=?,cod_tecnico_usuarioFK=?,justificacion_ultima=?,'
                    .'fecha_actualizacion=NOW(),cod_usuarioFK_update=?,version=? '
                    .'WHERE id=? AND version=? AND id_trabajo_laboratorioFK IS NULL LIMIT 1'
                );
                $valoresVinculo = array(
                    $estadoConvalidacionNuevo,
                    $idTrabajo,
                    $codTecnico,
                    $justificacionBd,
                    $codUsuario,
                    $versionNueva,
                    $idHistorico,
                    $versionAnterior
                );
            }
            if (!$stmt) {
                trabajoLaboratorioLanzar('vinculo_historico_no_guardado', 'No se pudo preparar el vinculo historico.');
            }
            trabajoLaboratorioVincularParametros(
                $stmt,
                str_repeat('s', count($valoresVinculo)),
                $valoresVinculo
            );
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                trabajoLaboratorioLanzar(
                    'version_historica_desactualizada',
                    'El registro historico cambio antes de completar el vinculo operativo.'
                );
            }
            $stmt->close();

            $nuevoHistorico = $historico;
            $nuevoHistorico['estado_convalidacion'] = $estadoConvalidacionNuevo;
            $nuevoHistorico['id_trabajo_laboratorioFK'] = $idTrabajo;
            $nuevoHistorico['cod_tecnico_usuarioFK'] = $codTecnico;
            trabajoLaboratorioHistoricoInsertarEvento(
                $mysqli,
                $historicoAnterior,
                $nuevoHistorico,
                $tipoEventoHistorico,
                $codUsuario,
                $justificacion,
                $clave,
                $payloadHash,
                $versionNueva,
                array(
                    'id_trabajo_laboratorio' => $idTrabajo,
                    'id_evento_operativo' => $idEventoOperativo,
                    'codigo_visible' => $codigoVisible,
                    'modo_resolucion' => $esResolucion ? $modoResolucion : null,
                    'condicion_pre_entrega' => $esResolucion
                        ? $historico['condicion_pre_entrega'] : null,
                    'observacion_entrega' => $esResolucion
                        ? $historico['observacion_entrega'] : null,
                    'evidencias_cantidad' => $esResolucion
                        ? count($historico['evidencias_resolucion']) : 0,
                    'sin_foto' => $esResolucion
                        && $modoResolucion === 'instalado_entregado'
                        && !empty($historico['sin_foto_historica']) ? 1 : 0,
                    'motivo_sin_foto' => $esResolucion
                        && $modoResolucion === 'instalado_entregado'
                        && !empty($historico['sin_foto_historica'])
                            ? 'foto_historica_no_disponible' : null,
                    'detalle_sin_foto' => $esResolucion
                        && $modoResolucion === 'instalado_entregado'
                        && !empty($historico['sin_foto_historica'])
                            ? $justificacion : null,
                    'avance_tratamiento' => $avanceHistorico,
                    'sin_evidencia_historica' => $esResolucion
                        && $modoResolucion === 'instalado_entregado'
                        && count($historico['evidencias_resolucion']) > 0 ? 0 : 1,
                    'sin_transferencias_historicas' => 1,
                    'sin_evolucion_clinica' => $esResolucion
                        && $modoResolucion === 'instalado_entregado' ? 1 : 0
                )
            );

            $codigoRespuesta = $esResolucion
                ? ($modoResolucion === 'instalado_entregado'
                    ? 'historico_instalado_entregado'
                    : 'historico_continuado_operativo')
                : 'historico_integrado_operativo';
            $mensajeRespuesta = $esResolucion
                ? ($modoResolucion === 'instalado_entregado'
                    ? 'El trabajo quedo instalado y entregado; el tratamiento alcanzo el 100 %.'
                    : 'El trabajo dejo de ser historico y continua en la vista operativa bajo su responsabilidad.')
                : 'El registro historico fue integrado al seguimiento operativo.';
            $respuesta = trabajoLaboratorioRespuestaActualizada(
                $mysqli,
                $codUsuario,
                $idTrabajo,
                $codigoRespuesta,
                $mensajeRespuesta
            );
            $respuesta['datos']['id_historico'] = $idHistorico;
            $respuesta['datos']['version_historica'] = $versionNueva;
            $respuesta['datos']['modo_resolucion'] = $esResolucion ? $modoResolucion : null;
            $respuesta['datos']['avance_tratamiento'] = $avanceHistorico;
            $respuesta['datos']['sin_evidencia_historica'] = !(
                $esResolucion
                && $modoResolucion === 'instalado_entregado'
                && count($historico['evidencias_resolucion']) > 0
            );
            $respuesta['datos']['sin_foto_historica'] = $esResolucion
                && $modoResolucion === 'instalado_entregado'
                && !empty($historico['sin_foto_historica']);
            $respuesta['datos']['sin_transferencias_historicas'] = true;
            return array('id_trabajo' => $idTrabajo, 'respuesta' => $respuesta);
        }
    );
}

function trabajoLaboratorioHistoricoResolverHistorico($mysqli, $codUsuario, $entrada)
{
    $entrada = is_array($entrada) ? $entrada : array();
    trabajoLaboratorioHistoricoModoResolucionEntrada($entrada);
    return trabajoLaboratorioHistoricoPromoverHistorico(
        $mysqli,
        $codUsuario,
        $entrada
    );
}

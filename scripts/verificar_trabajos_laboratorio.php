<?php

/**
 * Verificacion y aplicacion controlada de la migracion de trabajos de laboratorio.
 *
 * Uso:
 *   php scripts/verificar_trabajos_laboratorio.php
 *   php scripts/verificar_trabajos_laboratorio.php --solo-puras
 *   php scripts/verificar_trabajos_laboratorio.php --aplicar-migracion
 *
 * Compatible con PHP 7.2. No imprime credenciales ni datos identificables.
 */

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/centro_facturas_helper.php';
require_once dirname(__DIR__).'/php_system/centro_legajos_helper.php';
require_once dirname(__DIR__).'/php_system/trabajo_laboratorio_helper.php';
require_once dirname(__DIR__).'/php_system/trabajo_laboratorio_historico_helper.php';

function pruebaLabFallar($mensaje)
{
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
    exit(1);
}

function pruebaLabOk($mensaje)
{
    fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
}

function pruebaLabAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        pruebaLabFallar($mensaje);
    }
    pruebaLabOk($mensaje);
}

function pruebaLabAfirmarIgual($esperado, $actual, $mensaje)
{
    pruebaLabAfirmar($esperado === $actual, $mensaje);
}

function pruebaLabEsperarExcepcion($codigoEsperado, $callback, $mensaje)
{
    try {
        call_user_func($callback);
    } catch (TrabajoLaboratorioExcepcion $e) {
        pruebaLabAfirmarIgual($codigoEsperado, $e->codigoOperacion, $mensaje);
        return $e;
    } catch (Exception $e) {
        pruebaLabFallar($mensaje.' Se recibio una excepcion no esperada.');
    } catch (Throwable $e) {
        pruebaLabFallar($mensaje.' Se recibio un error no esperado.');
    }
    pruebaLabFallar($mensaje.' No se produjo la excepcion esperada.');
}

function pruebaLabAccionesActivas($acciones, $orden)
{
    $activas = array();
    foreach ($orden as $accion) {
        if (!empty($acciones[$accion])) {
            $activas[] = $accion;
        }
    }
    return $activas;
}

function pruebaLabRespuestaContieneMedia($valor)
{
    if (is_string($valor)) {
        return strpos($valor, 'data:image/') === 0;
    }
    if (!is_array($valor)) {
        return false;
    }
    $clavesMedia = array(
        'miniatura_url', 'url_visualizacion', 'url_original_autorizada',
        'data_base64', 'base64', 'imagen_principal', 'evidencia_principal', 'foto'
    );
    foreach ($valor as $clave => $item) {
        if (in_array((string)$clave, $clavesMedia, true)
            || pruebaLabRespuestaContieneMedia($item)) {
            return true;
        }
    }
    return false;
}

function pruebaLabBloqueFuncion($fuente, $nombre)
{
    $tokens = token_get_all($fuente);
    $cantidad = count($tokens);
    for ($i = 0; $i < $cantidad; $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
            continue;
        }
        $j = $i + 1;
        while ($j < $cantidad) {
            $token = $tokens[$j];
            if (is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) {
                $j++;
                continue;
            }
            if ($token === '&') {
                $j++;
                continue;
            }
            break;
        }
        if ($j >= $cantidad || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING
            || $tokens[$j][1] !== $nombre) {
            continue;
        }
        $bloque = '';
        $nivel = 0;
        $inicioCuerpo = false;
        for ($k = $i; $k < $cantidad; $k++) {
            $texto = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
            $bloque .= $texto;
            if (!is_array($tokens[$k]) && $tokens[$k] === '{') {
                $nivel++;
                $inicioCuerpo = true;
            } elseif (!is_array($tokens[$k]) && $tokens[$k] === '}' && $inicioCuerpo) {
                $nivel--;
                if ($nivel === 0) {
                    return $bloque;
                }
            }
        }
    }
    return '';
}

function pruebaLabBloqueJavascript($fuente, $nombre)
{
    $marca = '  function '.$nombre.'(';
    $inicio = strpos($fuente, $marca);
    if ($inicio === false) {
        return '';
    }
    $fin = strpos($fuente, "\n  function ", $inicio + strlen($marca));
    return $fin === false ? substr($fuente, $inicio) : substr($fuente, $inicio, $fin - $inicio);
}

function pruebaLabEscalar($mysqli, $sql)
{
    $resultado = $mysqli->query($sql);
    if (!$resultado) {
        pruebaLabFallar('No se pudo ejecutar una comprobacion de estructura.');
    }
    $fila = $resultado->fetch_row();
    $resultado->free();
    return $fila ? intval($fila[0]) : 0;
}

function pruebaLabConteosProtegidos($mysqli)
{
    $salida = array();
    foreach (array('categoria', 'producto', 'mecanico_dental', 'trabajo_mecanico_dental', 'venta', 'detalle_venta') as $tabla) {
        $salida[$tabla] = pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM `'.$tabla.'`');
    }
    return $salida;
}

function pruebaLabEjecutarMigracion($mysqli, $ruta)
{
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        pruebaLabFallar('No se pudo leer la migracion controlada.');
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
        if ($sentencia === '' || preg_match('/^--[^\r\n]*$/', $sentencia)) {
            continue;
        }
        $numero++;
        if (!$mysqli->query($sentencia)) {
            pruebaLabFallar('La migracion fallo en la sentencia '.$numero.': '.$mysqli->error);
        }
    }
    if (trim($buffer) !== '') {
        pruebaLabFallar('La migracion contiene una sentencia sin delimitador final.');
    }
    pruebaLabOk('Migracion aditiva ejecutada sin errores ('.$numero.' sentencias).');
}

$argumentos = isset($argv) ? $argv : array();
$soloPuras = in_array('--solo-puras', $argumentos, true);
$aplicar = in_array('--aplicar-migracion', $argumentos, true);
if ($soloPuras && $aplicar) {
    pruebaLabFallar('No combine --solo-puras con --aplicar-migracion.');
}

$ventaVm = array(
    'cod_venta' => 610,
    'num_factura' => '00610',
    'puntoexpedicion' => '',
    'nroventa' => '00610',
    'cod_local' => 9,
    'nombre_local' => 'VILLA MORRA'
);
pruebaLabAfirmar(
    trabajoLaboratorioCodigoVisible($ventaVm, 539) === '00610-VM-LAB-539',
    'La nomenclatura conserva el numero de venta, VM y el ID del trabajo.'
);
$ventaVm['num_factura'] = '00610-VM';
pruebaLabAfirmar(
    trabajoLaboratorioCodigoVisible($ventaVm, 539) === '00610-VM-LAB-539',
    'La sigla de la sucursal no se repite.'
);

$autorizacionBase = trabajoLaboratorioResolverAutorizacionPreparacionLocal(array(
    'usuario_activo' => true,
    'es_doctor' => true,
    'permiso_crear' => true,
    'cod_local_base' => 9,
    'cod_local_destino' => 9
));
pruebaLabAfirmar(
    !empty($autorizacionBase['autorizado'])
    && empty($autorizacionBase['multisucursal'])
    && $autorizacionBase['origen'] === 'sucursal_base',
    'Un doctor activo con permiso conserva la preparacion en su sucursal base.'
);
$autorizacionHorario = trabajoLaboratorioResolverAutorizacionPreparacionLocal(array(
    'usuario_activo' => true,
    'es_doctor' => true,
    'permiso_crear' => true,
    'cod_local_base' => 9,
    'cod_local_destino' => 4,
    'horario_local_activo' => true
));
pruebaLabAfirmar(
    !empty($autorizacionHorario['autorizado'])
    && !empty($autorizacionHorario['multisucursal'])
    && $autorizacionHorario['origen'] === 'horario_local',
    'Un horario vigente habilita solamente la preparacion multisucursal.'
);
$autorizacionPlanificacion = trabajoLaboratorioResolverAutorizacionPreparacionLocal(array(
    'usuario_activo' => true,
    'es_doctor' => true,
    'permiso_crear' => true,
    'cod_local_base' => 9,
    'cod_local_destino' => 4,
    'vinculo_planificacion_activo' => true
));
pruebaLabAfirmar(
    !empty($autorizacionPlanificacion['autorizado'])
    && $autorizacionPlanificacion['origen'] === 'planificacion_sucursal',
    'Un vinculo activo de planificacion habilita la preparacion sin cambiar la base laboral.'
);
pruebaLabAfirmar(
    empty(trabajoLaboratorioResolverAutorizacionPreparacionLocal(array(
        'usuario_activo' => true,
        'es_doctor' => true,
        'permiso_crear' => true,
        'cod_local_base' => 9,
        'cod_local_destino' => 4
    ))['autorizado']),
    'Una sucursal ajena sin horario ni planificacion permanece bloqueada.'
);
pruebaLabAfirmar(
    empty(trabajoLaboratorioResolverAutorizacionPreparacionLocal(array(
        'usuario_activo' => true,
        'es_doctor' => true,
        'permiso_crear' => false,
        'cod_local_base' => 9,
        'cod_local_destino' => 4,
        'vinculo_planificacion_activo' => true
    ))['autorizado']),
    'El vinculo de planificacion no reemplaza el permiso de crear trabajos.'
);
pruebaLabAfirmar(
    empty(trabajoLaboratorioResolverAutorizacionPreparacionLocal(array(
        'usuario_activo' => true,
        'es_doctor' => false,
        'permiso_crear' => true,
        'cod_local_base' => 9,
        'cod_local_destino' => 4,
        'vinculo_planificacion_activo' => true
    ))['autorizado']),
    'Un usuario que no es doctor ni auditor no obtiene acceso por el vinculo.'
);

$unaPieza = array(array('pieza' => '11', 'piezas' => array('11'), 'alcance' => 'pieza_dental'));
$dosPiezas = array(array('pieza' => '', 'piezas' => array('11', '12'), 'alcance' => 'piezas_multiples'));
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('pieza_individual', $unaPieza) === null, 'Pieza individual acepta una pieza.');
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('pieza_individual', $dosPiezas) === null, 'Un detalle de cantidad uno admite varias piezas dentro de un unico trabajo.');
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('multipieza', $dosPiezas) === null, 'Un tratamiento multipieza conserva varias piezas en un trabajo.');
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('multipieza', $unaPieza) !== null, 'Multipieza exige al menos dos piezas.');
pruebaLabAfirmarIgual(
    'ANA M. P. L.',
    trabajoLaboratorioNombreAbreviadoImpresion('ANA MARIA PEREZ LOPEZ'),
    'La impresion tecnica abrevia el paciente y no expone todos sus nombres.'
);
pruebaLabAfirmarIgual(
    'Piezas 11, 12',
    trabajoLaboratorioResumenUbicacionesImpresion($dosPiezas),
    'La impresion tecnica resume todas las piezas dentales sin duplicarlas.'
);
$detalleTresUnidades = array('cantidad_detalle' => 3);
pruebaLabAfirmarIgual(3, trabajoLaboratorioCantidadAgrupadaDetalle($detalleTresUnidades), 'Una cantidad entera de tres habilita tres trabajos independientes.');
pruebaLabAfirmarIgual(0, trabajoLaboratorioCantidadAgrupadaDetalle(array('cantidad_detalle' => 2.5)), 'Una cantidad fraccionaria no se convierte automaticamente en trabajos.');
$unidadesRegularizadas = trabajoLaboratorioNormalizarUnidadesRegularizacion(
    array(
        array('numero_unidad' => 1, 'piezas' => array('24', '25'), 'denticion' => 'permanente'),
        array('numero_unidad' => 2, 'piezas' => array('26'), 'denticion' => 'permanente'),
        array('numero_unidad' => 3, 'piezas' => array('64'), 'denticion' => 'temporal')
    ),
    3,
    'pieza_individual'
);
pruebaLabAfirmar(
    count($unidadesRegularizadas) === 3
    && count($unidadesRegularizadas[0]['piezas']) === 2
    && $unidadesRegularizadas[2]['pieza'] === '64',
    'Cada trabajo conserva su propia seleccion y admite multiseleccion independiente.'
);
$codigoOrigenA = trabajoLaboratorioCodigoOrigenUnidades(2241, 12560, 'clave-regularizacion-001');
$codigoOrigenB = trabajoLaboratorioCodigoOrigenUnidades(2241, 12560, 'clave-regularizacion-001');
pruebaLabAfirmar(
    $codigoOrigenA === $codigoOrigenB && strpos($codigoOrigenA, 'V2241-D12560-') === 0,
    'El lote conserva un codigo de origen estable para todas sus unidades.'
);
pruebaLabAfirmarIgual(25, trabajoLaboratorioObjetivoAvanceClinicoEvento('trabajo_iniciado'), 'El inicio del trabajo fija un piso clinico de 25%.');
pruebaLabAfirmarIgual(
    'pendiente_tecnico',
    trabajoLaboratorioEstadoEventoRecorrido('trabajo_iniciado', array('estado_resultante' => 'pendiente_tecnico')),
    'El nodo inicial refleja Tecnico pendiente cuando no hubo asignacion.'
);
pruebaLabAfirmarIgual(
    'pendiente_entrega_mecanico',
    trabajoLaboratorioEstadoEventoRecorrido('tecnico_asignado'),
    'Asignar el tecnico deja el trabajo pendiente de entrega sin iniciar un traslado.'
);
$presentacionTecnicoPendiente = trabajoLaboratorioPresentacionEstadoRecorrido('pendiente_tecnico');
pruebaLabAfirmarIgual(
    'Tecnico pendiente',
    $presentacionTecnicoPendiente['nombre'],
    'La vista identifica expresamente el trabajo que todavia no posee tecnico.'
);
pruebaLabAfirmarIgual(50, trabajoLaboratorioObjetivoAvanceClinicoEvento('recepcion_mecanico_confirmada'), 'La recepcion del mecanico fija un piso clinico de 50%.');
pruebaLabAfirmarIgual(75, trabajoLaboratorioObjetivoAvanceClinicoEvento('devolucion_confirmada'), 'El retorno a la clinica fija un piso clinico de 75%.');
pruebaLabAfirmarIgual(100, trabajoLaboratorioObjetivoAvanceClinicoEvento('instalacion_registrada'), 'La instalacion clinica confirmada fija el avance en 100%.');
pruebaLabAfirmarIgual(0, trabajoLaboratorioObjetivoAvanceClinicoEvento('trabajo_cancelado'), 'La cancelacion no reduce ni reescribe el avance clinico.');
pruebaLabAfirmar(
    trabajoLaboratorioDetalleClinicoActivo(array(
        'estado_detalle' => 'Activo', 'estado_tratamiento' => 'En proceso',
        'estado_venta' => 'Activo', 'progreso_porcentaje' => 75
    )),
    'Un tratamiento vigente con avance menor a 100 permanece habilitado.'
);
pruebaLabAfirmar(
    !trabajoLaboratorioDetalleClinicoActivo(array(
        'estado_detalle' => 'Activo', 'estado_tratamiento' => 'Completado',
        'estado_venta' => 'Activo', 'progreso_porcentaje' => 75
    )),
    'Un tratamiento finalizado no permite asignar ubicacion ni iniciar otro trabajo.'
);
pruebaLabAfirmar(
    !trabajoLaboratorioDetalleClinicoActivo(array(
        'estado_detalle' => 'Activo', 'estado_tratamiento' => '',
        'estado_venta' => 'Activo', 'progreso_porcentaje' => 100
    )),
    'Un detalle con avance completo se considera finalizado aunque el texto de estado este vacio.'
);
pruebaLabAfirmar(
    trabajoLaboratorioHashPayload(array('b' => 2, 'a' => 1)) === trabajoLaboratorioHashPayload(array('a' => 1, 'b' => 2)),
    'La huella idempotente es estable aunque cambie el orden de los campos.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('iniciarTrabajo', array('local_propio' => true)),
    'Un auditor puede iniciar un trabajo normal en su propio local sin justificar una excepcion.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('iniciarTrabajosAgrupados', array('local_propio' => true))
    && trabajoLaboratorioAccionNaturalContexto('asignarTecnico', array('local_propio' => true)),
    'El inicio agrupado y la asignacion posterior de tecnico son acciones normales dentro del local propio.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('iniciarTrabajo', array('local_propio' => false))
    && trabajoLaboratorioAccionNaturalContexto('iniciarTrabajosAgrupados', array('local_propio' => false))
    && trabajoLaboratorioAccionNaturalContexto('asignarTecnico', array('local_propio' => false)),
    'El inicio simple, agrupado y la asignacion de tecnico son ordinarios en cualquier local autorizado.'
);
pruebaLabAfirmar(
    !trabajoLaboratorioAccionNaturalContexto('guardarRegularizacionUnidades', array('local_propio' => false)),
    'La regularizacion administrativa de unidades conserva el alcance del local propio.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('registrarInstalacion', array(
        'custodio' => true,
        'doctor' => false
    )),
    'El custodio vigente puede cerrar el hilo sin depender del rango de doctor ni justificar una excepcion.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('tomarHilo', array(
        'local_propio' => true,
        'auditor' => true,
        'tecnico_formal' => false,
        'tecnico' => false,
        'estado_derivado' => 'en_transferencia_mecanico'
    )),
    'Tomar el hilo es una accion ordinaria para cualquier cuenta autenticada activa.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('tomarHilo', array(
        'local_propio' => false,
        'tecnico_formal' => false,
        'tecnico' => false,
        'estado_derivado' => 'en_transferencia_mecanico'
    )),
    'La toma no depende del local ni de ser el tecnico o destinatario previsto.'
);
pruebaLabAfirmar(
    trabajoLaboratorioAccionNaturalContexto('tomarHilo', array(
        'local_propio' => true,
        'tecnico_formal' => true,
        'tecnico' => false,
        'estado_derivado' => 'en_transferencia_clinica'
    )),
    'Una cuenta formal de tecnico tambien puede asumir una recepcion como cualquier cuenta autenticada activa.'
);

$accionesLaboratorio = array(
    'asignarTecnico', 'iniciarTransferencia', 'tomarHilo', 'agregarEvidencia', 'agregarNota',
    'registrarNovedad', 'rectificarCustodia', 'iniciarDevolucion', 'solicitarAjuste',
    'aprobarTrabajo', 'registrarInstalacion', 'cancelarTrabajo'
);
$matrizEstados = array(
    'pendiente_tecnico' => array(
        'asignarTecnico', 'tomarHilo', 'agregarEvidencia', 'agregarNota',
        'registrarNovedad', 'rectificarCustodia', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'pendiente_entrega_mecanico' => array(
        'iniciarTransferencia', 'tomarHilo', 'agregarEvidencia', 'agregarNota',
        'registrarNovedad', 'rectificarCustodia', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'en_transferencia_mecanico' => array(
        'tomarHilo', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
        'rectificarCustodia', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'en_laboratorio' => array(
        'tomarHilo', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
        'rectificarCustodia', 'iniciarDevolucion', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'en_transferencia_clinica' => array(
        'tomarHilo', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
        'rectificarCustodia', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'pendiente_revision' => array(
        'tomarHilo', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
        'rectificarCustodia', 'solicitarAjuste', 'aprobarTrabajo',
        'registrarInstalacion', 'cancelarTrabajo'
    ),
    'ajuste_solicitado' => array(
        'iniciarTransferencia', 'tomarHilo', 'agregarEvidencia', 'agregarNota',
        'registrarNovedad', 'rectificarCustodia', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'listo_instalacion' => array(
        'tomarHilo', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
        'rectificarCustodia', 'registrarInstalacion', 'cancelarTrabajo'
    ),
    'instalado' => array(),
    'cancelado' => array()
);
foreach ($matrizEstados as $estadoMatriz => $esperadas) {
    $actuales = array();
    foreach ($accionesLaboratorio as $accionMatriz) {
        if (trabajoLaboratorioEstadoPermiteAccion($estadoMatriz, $accionMatriz)) {
            $actuales[] = $accionMatriz;
        }
    }
    pruebaLabAfirmarIgual(
        $esperadas,
        $actuales,
        'La matriz estricta conserva las acciones del estado '.$estadoMatriz.'.'
    );
}
$accionesEstadoDesconocido = array();
foreach ($accionesLaboratorio as $accionMatriz) {
    if (trabajoLaboratorioEstadoPermiteAccion('estado_no_reconocido', $accionMatriz)) {
        $accionesEstadoDesconocido[] = $accionMatriz;
    }
}
pruebaLabAfirmarIgual(
    array(),
    $accionesEstadoDesconocido,
    'Un estado desconocido falla de forma cerrada.'
);
pruebaLabAfirmar(
    trabajoLaboratorioEstadoPermiteAccion('en_laboratorio', 'accion_no_reconocida') === false,
    'Una accion desconocida falla de forma cerrada.'
);

$codigosPermiso = array(
    'VERTRABAJOSLABORATORIO', 'CREARTRABAJOLABORATORIO',
    'ENTREGARTRABAJOLABORATORIO', 'RECIBIRTRABAJOLABORATORIO',
    'EVIDENCIATRABAJOLABORATORIO', 'AJUSTARTRABAJOLABORATORIO',
    'APROBARTRABAJOLABORATORIO', 'INSTALARTRABAJOLABORATORIO',
    'CANCELARTRABAJOLABORATORIO'
);
$permisosTodos = array_fill_keys($codigosPermiso, true);
$casosRoles = array(
    'doctor_tecnico_pendiente' => array(
        'estado' => 'pendiente_tecnico',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'asignarTecnico', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
            'registrarInstalacion', 'cancelarTrabajo'
        )
    ),
    'clinica_custodia_inicial' => array(
        'estado' => 'pendiente_entrega_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'iniciarTransferencia', 'agregarEvidencia', 'agregarNota', 'registrarNovedad',
            'registrarInstalacion', 'cancelarTrabajo'
        )
    ),
    'tecnico_por_recibir' => array(
        'estado' => 'en_transferencia_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => false,
            'tecnico' => true, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('tomarHilo', 'agregarEvidencia', 'agregarNota')
    ),
    'tecnico_con_custodia' => array(
        'estado' => 'en_laboratorio',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => true,
            'tecnico' => true, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'agregarEvidencia', 'agregarNota', 'registrarNovedad',
            'iniciarDevolucion', 'registrarInstalacion'
        )
    ),
    'clinica_por_recibir' => array(
        'estado' => 'en_transferencia_clinica',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => false,
            'tecnico' => false, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('tomarHilo', 'cancelarTrabajo')
    ),
    'tecnico_formal_habilitado_en_retorno' => array(
        'estado' => 'en_transferencia_clinica',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => false,
            'tecnico' => false, 'tecnico_formal' => true, 'doctor' => false,
            'permisos' => $permisosTodos
        ),
        'esperadas' => array('tomarHilo')
    ),
    'usuario_habilitado_sin_rol_especial' => array(
        'estado' => 'en_transferencia_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => false,
            'tecnico' => false, 'tecnico_formal' => false, 'doctor' => false,
            'permisos' => $permisosTodos
        ),
        'esperadas' => array('tomarHilo')
    ),
    'doctor_revision' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'agregarEvidencia', 'agregarNota', 'registrarNovedad', 'solicitarAjuste',
            'aprobarTrabajo', 'registrarInstalacion', 'cancelarTrabajo'
        )
    ),
    'custodio_no_doctor_cierre_operativo' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => true,
            'tecnico' => false, 'tecnico_formal' => true, 'doctor' => false,
            'permisos' => array(
                'VERTRABAJOSLABORATORIO' => true,
                'RECIBIRTRABAJOLABORATORIO' => true,
                'ENTREGARTRABAJOLABORATORIO' => true,
                'EVIDENCIATRABAJOLABORATORIO' => true
            )
        ),
        'esperadas' => array(
            'agregarEvidencia', 'agregarNota', 'registrarNovedad',
            'registrarInstalacion'
        )
    ),
    'doctor_instalacion' => array(
        'estado' => 'listo_instalacion',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'agregarEvidencia', 'agregarNota', 'registrarNovedad',
            'registrarInstalacion', 'cancelarTrabajo'
        )
    ),
    'auditor_fuera_local' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => true, 'local' => false, 'custodio' => false,
            'tecnico' => false, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'tomarHilo', 'rectificarCustodia', 'solicitarAjuste',
            'aprobarTrabajo', 'cancelarTrabajo'
        )
    ),
    'tecnico_sin_custodia_no_entrega' => array(
        'estado' => 'pendiente_entrega_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => false,
            'tecnico' => true, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('tomarHilo', 'agregarEvidencia', 'agregarNota')
    ),
    'sin_permisos' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => true, 'doctor' => true, 'permisos' => array()
        ),
        'esperadas' => array('registrarInstalacion')
    ),
    'terminal' => array(
        'estado' => 'instalado',
        'contexto' => array(
            'auditor' => true, 'local' => true, 'custodio' => true,
            'tecnico' => true, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array()
    )
);
foreach ($casosRoles as $nombreCaso => $casoRol) {
    $resueltas = trabajoLaboratorioResolverAcciones($casoRol['estado'], $casoRol['contexto']);
    pruebaLabAfirmarIgual(
        $casoRol['esperadas'],
        pruebaLabAccionesActivas($resueltas, $accionesLaboratorio),
        'La matriz de rol y permisos respeta el caso '.$nombreCaso.'.'
    );
}
$permisosSinEntrega = $permisosTodos;
$permisosSinEntrega['ENTREGARTRABAJOLABORATORIO'] = false;
$accionesSinEntrega = trabajoLaboratorioResolverAcciones(
    'en_laboratorio',
    array(
        'auditor' => false, 'local' => false, 'custodio' => true,
        'tecnico' => true, 'doctor' => false, 'permisos' => $permisosSinEntrega
    )
);
pruebaLabAfirmarIgual(
    array('agregarEvidencia', 'agregarNota', 'registrarNovedad', 'registrarInstalacion'),
    pruebaLabAccionesActivas($accionesSinEntrega, $accionesLaboratorio),
    'Quitar el permiso de entrega impide la devolucion, pero el custodio conserva el cierre del hilo.'
);
pruebaLabAfirmarIgual(
    'menos de 1 min',
    trabajoLaboratorioDuracionCustodiaTexto(59),
    'Un periodo breve de custodia no se presenta como cero dias.'
);
pruebaLabAfirmarIgual(
    '1 h 1 min',
    trabajoLaboratorioDuracionCustodiaTexto(3660),
    'La duracion de custodia conserva horas y minutos.'
);
pruebaLabAfirmarIgual(
    '1 dia 1 h',
    trabajoLaboratorioDuracionCustodiaTexto(90000),
    'La duracion de custodia resume dias y horas sin perder el tiempo util.'
);
$textoSnapshotUtf8 = "Custodia \xC3\x81rea t\xC3\xA9cnica \xC3\x91andut\xC3\xAD";
pruebaLabAfirmarIgual(
    $textoSnapshotUtf8,
    trabajoLaboratorioTextoUtf8(trabajoLaboratorioTextoBaseDatos($textoSnapshotUtf8, 255)),
    'Los snapshots convierten UTF-8 a la codificacion de la base y regresan legibles a la salida.'
);
pruebaLabAfirmarIgual(
    2 * 1024 * 1024,
    trabajoLaboratorioBytesConfiguracion('2M'),
    'El limite PHP de 2M se convierte a bytes sin asumir un valor decimal.'
);
pruebaLabAfirmarIgual(
    8 * 1024 * 1024,
    trabajoLaboratorioBytesConfiguracion('8M'),
    'El limite total PHP de 8M se convierte a bytes correctamente.'
);
$limitesMediaLocales = trabajoLaboratorioLimitesMedia();
pruebaLabAfirmar(
    intval($limitesMediaLocales['max_archivos']) === 3
    && intval($limitesMediaLocales['max_bytes_archivo']) === 2 * 1024 * 1024
    && intval($limitesMediaLocales['max_bytes_solicitud']) === 8 * 1024 * 1024,
    'La configuracion local deriva tres archivos de hasta 2 MB dentro de una solicitud de 8 MB.'
);
pruebaLabAfirmarIgual(
    '2 MB',
    trabajoLaboratorioTamanoMediaTexto($limitesMediaLocales['max_bytes_archivo']),
    'El limite efectivo se presenta al usuario en una unidad comprensible.'
);

$ahoraPrueba = trabajoLaboratorioTimestampSistema('2026-07-21 12:00:00');
$sla19 = trabajoLaboratorioCalcularIndicadoresPlazo(
    'en_laboratorio', '2026-07-02 12:00:00', '2026-08-01 12:00:00', '', $ahoraPrueba
);
pruebaLabAfirmar(
    $sla19['dias_totales'] === 19
    && $sla19['dias_custodio_actual'] === 19
    && $sla19['semaforo']['codigo'] === 'en_plazo'
    && $sla19['sla_vencido'] === false,
    'A los 19 dias el SLA sigue dentro del plazo y usa la creacion como custodia alternativa.'
);
$sla20 = trabajoLaboratorioCalcularIndicadoresPlazo(
    'en_laboratorio', '2026-07-01 12:00:00', '2026-07-31 12:00:00',
    '2026-07-18 12:00:00', $ahoraPrueba
);
pruebaLabAfirmar(
    $sla20['dias_totales'] === 20
    && $sla20['dias_custodio_actual'] === 3
    && $sla20['semaforo']['codigo'] === 'advertencia'
    && $sla20['sla_vencido'] === false,
    'A los 20 dias comienza la advertencia y se mide por separado la custodia.'
);
$sla30 = trabajoLaboratorioCalcularIndicadoresPlazo(
    'pendiente_revision', '2026-06-21 12:00:00', '2026-07-21 12:00:00',
    '2026-07-20 12:00:00', $ahoraPrueba
);
pruebaLabAfirmar(
    $sla30['dias_totales'] === 30
    && $sla30['semaforo']['codigo'] === 'advertencia'
    && $sla30['sla_vencido'] === false
    && $sla30['tiempo_restante_segundos'] === 0,
    'El dia 30 permanece en advertencia y el tiempo restante llega exactamente a cero.'
);
$sla31 = trabajoLaboratorioCalcularIndicadoresPlazo(
    'pendiente_revision', '2026-06-20 12:00:00', '2026-07-20 12:00:00',
    '2026-07-18 12:00:00', $ahoraPrueba
);
pruebaLabAfirmar(
    $sla31['dias_totales'] === 31
    && $sla31['semaforo']['codigo'] === 'atrasado'
    && $sla31['sla_vencido'] === true
    && $sla31['tiempo_restante_segundos'] === -86400,
    'A los 31 dias el trabajo activo queda atrasado y vencido.'
);
$slaFinal = trabajoLaboratorioCalcularIndicadoresPlazo(
    'instalado', '2026-06-20 12:00:00', '2026-07-20 12:00:00',
    '2026-07-18 12:00:00', $ahoraPrueba
);
pruebaLabAfirmar(
    $slaFinal['semaforo']['codigo'] === 'finalizado' && $slaFinal['sla_vencido'] === false,
    'Un trabajo terminal no queda marcado como SLA vencido.'
);
$slaFuturo = trabajoLaboratorioCalcularIndicadoresPlazo(
    'en_laboratorio', '2026-07-22 12:00:00', '2026-07-23 12:00:00',
    '2026-07-22 12:00:00', $ahoraPrueba
);
pruebaLabAfirmar(
    $slaFuturo['dias_totales'] === 0
    && $slaFuturo['dias_custodio_actual'] === 0
    && $slaFuturo['tiempo_restante_segundos'] === 172800,
    'Las fechas futuras no producen dias negativos y conservan el tiempo restante.'
);
$slaSinObjetivo = trabajoLaboratorioCalcularIndicadoresPlazo(
    'en_laboratorio', '2026-07-21 12:00:00', 'fecha-invalida', '', $ahoraPrueba
);
pruebaLabAfirmarIgual(
    null,
    $slaSinObjetivo['tiempo_restante_segundos'],
    'Una fecha objetivo invalida no inventa tiempo restante.'
);

$respuestaConMedia = array(
    'ok' => true,
    'codigo' => 'prueba_segura',
    'datos' => array(
        'id' => 17,
        'miniatura_url' => 'data:image/png;base64,AAA',
        'detalle' => array(
            'observacion' => 'Conservar',
            'foto' => 'data:image/jpeg;base64,BBB',
            'elementos' => array(
                array('texto' => 'Visible', 'base64' => 'CCC'),
                array('texto' => 'data:image/webp;base64,DDD')
            )
        )
    ),
    'version' => 4,
    'cadena_media' => 'data:image/png;base64,EEE'
);
$respuestaProtegida = trabajoLaboratorioRespuestaIdempotenciaProtegida($respuestaConMedia);
pruebaLabAfirmar(
    !pruebaLabRespuestaContieneMedia($respuestaProtegida)
    && $respuestaProtegida['datos']['id'] === 17
    && $respuestaProtegida['datos']['detalle']['observacion'] === 'Conservar'
    && $respuestaProtegida['datos']['detalle']['elementos'][0]['texto'] === 'Visible',
    'La respuesta idempotente elimina medios recursivamente y conserva datos ordinarios.'
);
pruebaLabAfirmar(
    isset($respuestaConMedia['datos']['miniatura_url']),
    'La proteccion idempotente no modifica el arreglo de respuesta original.'
);
pruebaLabAfirmarIgual(
    null,
    trabajoLaboratorioRespuestaIdempotenciaProtegida('data:image/png;base64,AAA'),
    'Una imagen embebida como respuesta raiz no se persiste.'
);

$payloadA = array(
    'useru' => 'usuario-a', 'passu' => 'secreto-a', 'accion' => 'accion-a',
    'clave_idempotencia' => 'clave-uno', 'dato' => 7,
    'anidado' => array('b' => 2, 'a' => 1), 'lista' => array('uno', 'dos')
);
$payloadB = array(
    'lista' => array('uno', 'dos'), 'anidado' => array('a' => 1, 'b' => 2),
    'dato' => 7, 'clave_idempotencia' => 'clave-dos',
    'accion' => 'accion-b', 'passu' => 'secreto-b', 'useru' => 'usuario-b'
);
pruebaLabAfirmarIgual(
    trabajoLaboratorioHashPayload($payloadA),
    trabajoLaboratorioHashPayload($payloadB),
    'El hash ignora transporte y credenciales, y ordena objetos anidados.'
);
$payloadNegocioDistinto = $payloadB;
$payloadNegocioDistinto['dato'] = 8;
pruebaLabAfirmar(
    trabajoLaboratorioHashPayload($payloadA) !== trabajoLaboratorioHashPayload($payloadNegocioDistinto),
    'El hash cambia cuando cambia un dato de negocio.'
);
$payloadListaDistinta = $payloadB;
$payloadListaDistinta['lista'] = array('dos', 'uno');
pruebaLabAfirmar(
    trabajoLaboratorioHashPayload($payloadA) !== trabajoLaboratorioHashPayload($payloadListaDistinta),
    'El hash conserva como significativa la secuencia de una lista.'
);

pruebaLabAfirmarIgual(
    'abcde123',
    trabajoLaboratorioNormalizarClave('abcde123'),
    'La clave idempotente acepta el limite minimo de ocho caracteres.'
);
pruebaLabAfirmarIgual(
    str_repeat('a', 100),
    trabajoLaboratorioNormalizarClave(str_repeat('a', 100)),
    'La clave idempotente acepta el limite maximo de cien caracteres.'
);
pruebaLabAfirmarIgual(
    'clave-0001',
    trabajoLaboratorioNormalizarClave('  clave-0001  '),
    'La clave idempotente elimina espacios exteriores.'
);
pruebaLabEsperarExcepcion(
    'clave_idempotencia_invalida',
    function () {
        trabajoLaboratorioNormalizarClave('corta');
    },
    'Una clave idempotente demasiado corta se rechaza.'
);
pruebaLabEsperarExcepcion(
    'clave_idempotencia_invalida',
    function () {
        trabajoLaboratorioNormalizarClave('clave con espacio');
    },
    'Una clave idempotente con caracteres no permitidos se rechaza.'
);
pruebaLabAfirmarIgual(
    7,
    trabajoLaboratorioExigirVersion(array('version' => 7), array('version_esperada' => '7')),
    'La version esperada vigente se acepta.'
);
pruebaLabEsperarExcepcion(
    'version_requerida',
    function () {
        trabajoLaboratorioExigirVersion(array('version' => 7), array());
    },
    'Una mutacion sin version se rechaza.'
);
$errorVersion = pruebaLabEsperarExcepcion(
    'version_desactualizada',
    function () {
        trabajoLaboratorioExigirVersion(array('version' => 7), array('version_esperada' => 6));
    },
    'Una version desactualizada se rechaza.'
);
pruebaLabAfirmarIgual(
    7,
    isset($errorVersion->datosOperacion['version_actual'])
        ? intval($errorVersion->datosOperacion['version_actual']) : 0,
    'El conflicto informa la version actual sin exponer datos clinicos.'
);

$rutaHelper = dirname(__DIR__).'/php_system/trabajo_laboratorio_helper.php';
$fuenteHelper = file_get_contents($rutaHelper);
pruebaLabAfirmar($fuenteHelper !== false && $fuenteHelper !== '', 'Se pudo leer el helper para verificar sus contratos.');
$fuenteConsultaJs = file_get_contents(dirname(__DIR__).'/js_system/consulta.js');
$fuenteInicioJs = file_get_contents(dirname(__DIR__).'/js_system/inicio.js');
$fuenteDashboardShortcutsJs = file_get_contents(dirname(__DIR__).'/js_system/dashboard_shortcuts.js');
$fuenteDashboardShortcutsPhp = file_get_contents(dirname(__DIR__).'/php_system/dashboard_shortcuts.php');
$fuenteOdontogramaJs = file_get_contents(dirname(__DIR__).'/js_system/odontograma.js');
$fuenteTrabajoLaboratorioJs = file_get_contents(dirname(__DIR__).'/js_system/trabajo_laboratorio.js');
$fuenteTrabajoLaboratorioCss = file_get_contents(dirname(__DIR__).'/css_system/trabajo_laboratorio.css');
$fuenteInicioCss = file_get_contents(dirname(__DIR__).'/css_system/inicio.css');
$fuenteInicioHtml = file_get_contents(dirname(__DIR__).'/system/inicio.html');
$fuenteConsultaPhp = file_get_contents(dirname(__DIR__).'/php_system/abmConsulta.php');
$fuenteOdontogramaPhp = file_get_contents(dirname(__DIR__).'/php_system/abmOdontograma.php');
$fuenteTrabajoLaboratorioPhp = file_get_contents(dirname(__DIR__).'/php_system/abmTrabajoLaboratorio.php');
$fuenteHistoricoHelper = file_get_contents(dirname(__DIR__).'/php_system/trabajo_laboratorio_historico_helper.php');
$fuenteMigracionHiloCustodia = file_get_contents(
    dirname(__DIR__).'/actualizacion_22072026_hilo_custodia_laboratorio.sql'
);
$fuenteMigracionPerformance = file_get_contents(
    dirname(__DIR__).'/actualizacion_25072026_trabajo_laboratorio_performance.sql'
);
pruebaLabAfirmar(
    $fuenteConsultaJs !== false && $fuenteInicioJs !== false && $fuenteOdontogramaJs !== false
    && $fuenteDashboardShortcutsJs !== false && $fuenteDashboardShortcutsPhp !== false
    && $fuenteTrabajoLaboratorioJs !== false && $fuenteTrabajoLaboratorioCss !== false
    && $fuenteInicioCss !== false && $fuenteInicioHtml !== false
    && $fuenteConsultaPhp !== false && $fuenteOdontogramaPhp !== false
    && $fuenteTrabajoLaboratorioPhp !== false
    && $fuenteHistoricoHelper !== false && $fuenteMigracionHiloCustodia !== false
    && $fuenteMigracionPerformance !== false,
    'Se pudieron leer las integraciones clinicas para verificar sus resguardos.'
);
$bloqueAutorizacionPreparacion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioAutorizacionPreparacionLocal'
);
$bloqueContextoDetalle = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioObtenerContextoDetalle'
);
$bloqueAsegurarHilo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioAsegurarHiloDetalle'
);
$bloqueIniciarTrabajo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioIniciar'
);
$bloqueAccionesTrabajo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioAccionesPermitidas'
);
pruebaLabAfirmar(
    strpos($bloqueAutorizacionPreparacion, 'horario_usuario') !== false
    && strpos($bloqueAutorizacionPreparacion, 'planificacion_especialista_local') !== false
    && strpos($bloqueAutorizacionPreparacion, "'CREARTRABAJOLABORATORIO'") !== false
    && strpos($bloqueContextoDetalle, 'trabajoLaboratorioAutorizacionPreparacionLocal') !== false
    && strpos($bloqueContextoDetalle, "'preparacion_multisucursal_autorizada'") !== false,
    'La preparacion valida permiso, horario o vinculo activo y explica el acceso multisucursal.'
);
pruebaLabAfirmar(
    strpos($bloqueAsegurarHilo, 'trabajoLaboratorioUsuarioPuedePrepararLocal') !== false
    && strpos($bloqueIniciarTrabajo, 'trabajoLaboratorioAutorizacionPreparacionLocal') !== false
    && strpos($bloqueIniciarTrabajo, "'preparacion_multisucursal'") !== false
    && strpos($bloqueIniciarTrabajo, "'origen_autorizacion_local'") !== false,
    'El inicio y el enlace del hilo aplican la misma autorizacion y dejan trazabilidad multisucursal.'
);
pruebaLabAfirmar(
    strpos($bloqueAccionesTrabajo, 'trabajoLaboratorioUsuarioPuedeOperarLocal') !== false
    && strpos($bloqueAccionesTrabajo, 'trabajoLaboratorioUsuarioPuedePrepararLocal') === false,
    'Las entregas, ajustes y acciones posteriores conservan el alcance local estricto.'
);
pruebaLabAfirmar(
    strpos($fuenteInicioHtml, "id='divMenuTrabajoLaboratorio' data-access-key='trabajos_mecanicos_dentales'") !== false
    && strpos($fuenteInicioJs, 'permisoAccesoUser("VERTRABAJOSLABORATORIO","accion")==false') !== false
    && strpos($fuenteInicioJs, '$("table[id=divMenuTrabajoLaboratorio]").remove()') !== false
    && strpos($fuenteDashboardShortcutsJs, 'trabajos_mecanicos_dentales: { sourceSelector: "#divMenuTrabajoLaboratorio", permissionKey: "VERTRABAJOSLABORATORIO"') !== false
    && strpos($fuenteDashboardShortcutsPhp, "'trabajos_mecanicos_dentales' => 'VERTRABAJOSLABORATORIO'") !== false,
    'El menu y los accesos rapidos del hilo requieren el permiso de trabajos de laboratorio.'
);
pruebaLabAfirmar(
    strpos($fuenteConsultaJs, 'solicitudActual !== tratamientoLaboratorioClinicoEstado.solicitudSecuencia') !== false
    && strpos($fuenteConsultaJs, 'tratamientoLaboratorioClinicoPuedeAsignarUbicacion') !== false
    && strpos($fuenteConsultaJs, 'tratamientoLaboratorioClinicoResolverAccionContexto') !== false
    && strpos($fuenteConsultaJs, 'mostrarPanel: false') !== false
    && strpos($fuenteConsultaJs, 'alResolver: function (contexto)') !== false,
    'Consulta descarta respuestas antiguas y ejecuta el siguiente paso desde el primer clic.'
);
pruebaLabAfirmar(
    strpos($fuenteOdontogramaJs, 'function odontogramaAbrirSelectorRapidoLaboratorio') !== false
    && strpos($fuenteOdontogramaJs, 'Designar piezas dentarias') !== false
    && strpos($fuenteOdontogramaJs, 'Guardar y preparar laboratorio') !== false
    && strpos($fuenteOdontogramaJs, 'piezas_json: JSON.stringify(piezas)') !== false
    && strpos($fuenteOdontogramaJs, 'selector_rapido_laboratorio: "1"') !== false
    && strpos($fuenteOdontogramaPhp, 'odontoPost("selector_rapido_laboratorio") == "1"') !== false
    && strpos($fuenteOdontogramaJs, 'odontogramaActualizarLaboratorioTrasUbicacion') !== false
    && strpos($fuenteConsultaJs, 'tratamientoLaboratorioClinicoAbrirPreparacionDetalle(idDetalle, origen)') !== false
    && strpos($fuenteConsultaJs, 'cerrarModalEvolucionTratamientoConsulta()') !== false,
    'La ubicacion usa un selector rapido multiseleccion, evita modales anidados y abre la preparacion al guardarse.'
);
pruebaLabAfirmar(
    strpos($fuenteConsultaJs, 'function tratamientoLaboratorioClinicoIniciarRegularizacionUnidades') !== false
    && strpos($fuenteConsultaJs, 'function tratamientoLaboratorioClinicoAbrirSelectorUnidad') !== false
    && strpos($fuenteConsultaJs, 'soloCapturar: true') !== false
    && strpos($fuenteConsultaJs, 'trabajoActual: numero') !== false
    && strpos($fuenteConsultaJs, 'cantidadTrabajos: estado.cantidad') !== false
    && strpos($fuenteConsultaJs, 'tratamientoLaboratorioClinicoAbrirSelectorUnidad(numero + 1)') !== false
    && strpos($fuenteOdontogramaJs, 'Guardar trabajo " + estado.trabajoActual + " y continuar') !== false
    && strpos($fuenteOdontogramaJs, 'ubicacion.numero_unidad = estado.trabajoActual || 1') !== false,
    'Las unidades agrupadas se designan en selectores consecutivos e independientes, con multiseleccion por trabajo.'
);
pruebaLabAfirmar(
    strpos($fuenteConsultaJs, 'datos.append("accion", "guardarRegularizacionUnidades")') !== false
    && strpos($fuenteConsultaJs, 'datos.append("unidades_json", JSON.stringify(estado.unidades))') !== false
    && strpos($fuenteConsultaJs, 'TrabajoLaboratorio.abrirRegularizacionUnidades') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'iniciarTrabajosAgrupados') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'renderGroupedStartSummary') !== false,
    'La revision guarda el lote y abre automaticamente la preparacion comun de sus trabajos.'
);
pruebaLabAfirmar(
    strpos($fuenteConsultaJs, 'Abrir historicos de esta venta') !== false
    && strpos($fuenteConsultaJs, 'cod_venta_historica: detalle.cod_venta || ""') !== false
    && strpos($fuenteConsultaJs, 'busqueda: String(detalle.cod_venta || detalle.nro_venta || "")') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'root.querySelector("#tlabHistoricalFilters").reset()') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'payload.cod_venta = state.moduleOptions.cod_venta_historica') !== false
    && strpos($fuenteHistoricoHelper, '$condiciones[] = \'h.cod_venta_snapshot=?\'') !== false,
	'Un antecedente historico real conserva su consulta con filtros limpios y coincidencia exacta de venta.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioCss, '#divAbmConsulta .consulta-laboratorio-card-action') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '#divAbmConsulta .consulta-treatment-location-actions') !== false
    && strpos($fuenteInicioCss, '#divAbmDetalleConsulta .consulta-laboratorio-card-action') === false,
    'El acceso contextual usa estilos compactos y aislados dentro de la ficha clinica correcta.'
);
pruebaLabAfirmar(
    strpos($fuenteConsultaJs, 'function tratamientoLaboratorioClinicoInicializarMicrohilos') !== false
    && strpos($fuenteConsultaJs, 'function tratamientoLaboratorioClinicoAbrirNodoMicrohilo') !== false
    && strpos($fuenteConsultaJs, 'micro_hilos_activos') !== false
    && strpos($fuenteConsultaPhp, "data-laboratorio-mini-hilo-slot") !== false
    && strpos($fuenteTrabajoLaboratorioCss, '.plan-definitivo-item__body.is-laboratorio-microhilo') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '.consulta-laboratorio-nodo-popover') !== false,
    'Consulta carga automaticamente microcadenas con avatares, detalle por clic y presentacion responsive.'
);
pruebaLabAfirmar(
    strpos($fuenteHelper, 'function trabajoLaboratorioMicroHilosActivos') !== false
    && strpos($fuenteHelper, "trabajoLaboratorioCadenasCustodiaPorTrabajos(\$mysqli, \$trabajos, false)") !== false
    && strpos($fuenteHelper, "'micro_hilos_activos' => \$microHilosActivos") !== false,
    'El contexto proyecta las cadenas agrupadas por lote sin incorporar miniaturas en la carga inicial.'
);
pruebaLabAfirmar(
    strpos($fuenteConsultaPhp, 'tiene_trabajo_laboratorio_activo') !== false
    && strpos($fuenteConsultaPhp, 'Abrir trabajo de laboratorio') !== false
	&& strpos($fuenteConsultaPhp, 'tiene_antecedente_laboratorio_historico') !== false
	&& strpos($fuenteConsultaPhp, 'data-laboratorio-regularizacion') !== false
	&& strpos($fuenteConsultaPhp, 'Regularizar para laboratorio') !== false
	&& strpos($fuenteConsultaPhp, '? "Designar ".intval(round($cantidadNumero))." trabajos"') !== false,
	'Las tarjetas distinguen trabajo activo, antecedente real, inicio simple y regularizacion guiada por unidades.'
);
pruebaLabAfirmar(
    strpos($fuenteOdontogramaPhp, 'odontoAutorizarUbicacionLaboratorioDetalle') !== false
    && strpos($fuenteOdontogramaPhp, '$permitirAccesoConsulta = false') !== false
    && strpos($fuenteOdontogramaPhp, 'trabajoLaboratorioUsuarioPuedeLocal') !== false
    && strpos($fuenteOdontogramaPhp, 'trabajoLaboratorioUsuarioPuedePrepararLocal') !== false
    && preg_match('/odontoPost\("detalle_venta_id"\)\s*,\s*true/s', $fuenteOdontogramaPhp) === 1
    && strpos($fuenteOdontogramaPhp, 'EDITARFORMULARIOCONSULTORIO') !== false
    && strpos($fuenteOdontogramaPhp, 'CREARTRABAJOLABORATORIO') !== false
	&& strpos($fuenteOdontogramaPhp, 'ubicacion_laboratorio_no_autorizada') !== false
	&& strpos($fuenteOdontogramaPhp, 'detalle_laboratorio_requiere_regularizacion') !== false
	&& strpos($fuenteOdontogramaPhp, 'trabajo_laboratorio_activo') !== false,
	'El servidor libera la asignacion directa para Consulta/local y conserva el contrato estricto en acciones destructivas.'
);
$bloqueEliminarUbicacion = pruebaLabBloqueFuncion($fuenteOdontogramaPhp, 'odontoEliminarLink');
$posObtenerLink = strpos($bloqueEliminarUbicacion, '$link = $stmt->get_result()->fetch_assoc()');
$posAutorizarLink = strpos(
    $bloqueEliminarUbicacion,
    'odontoAutorizarUbicacionLaboratorioDetalle($mysqli, $user, $link["detalle_venta_id"])'
);
$posPrepararEliminacion = strpos($bloqueEliminarUbicacion, 'odontoPrepararModificacion');
$posEliminarLink = strpos($bloqueEliminarUbicacion, 'UPDATE odontograma_tratamiento_links SET activo = 0');
pruebaLabAfirmar(
    $posObtenerLink !== false && $posAutorizarLink !== false && $posPrepararEliminacion !== false
    && $posEliminarLink !== false && $posObtenerLink < $posAutorizarLink
    && $posAutorizarLink < $posPrepararEliminacion && $posPrepararEliminacion < $posEliminarLink
    && strpos($fuenteOdontogramaJs, 'detalle_venta_id: detalleId || ""') !== false,
    'Quitar una ubicacion de laboratorio se autoriza con el detalle persistido antes de modificar datos.'
);
$bloqueGuardarUbicacion = pruebaLabBloqueFuncion($fuenteOdontogramaPhp, 'odontoGuardarLink');
$posNormalizarSelectorRapido = strpos(
    $bloqueGuardarUbicacion,
    'odontoAlcanceSelectorRapidoLaboratorio('
);
$posLimpiarAlcanceArcada = strpos($bloqueGuardarUbicacion, 'elseif ($alcance == "arcada")');
pruebaLabAfirmar(
    strpos($fuenteOdontogramaPhp, 'function odontoAlcanceSelectorRapidoLaboratorio') !== false
    && strpos($bloqueGuardarUbicacion, '$selectorRapidoLaboratorio') !== false
    && $posNormalizarSelectorRapido !== false && $posLimpiarAlcanceArcada !== false
    && $posNormalizarSelectorRapido < $posLimpiarAlcanceArcada,
    'El servidor conserva la pieza explicita del selector rapido antes de normalizar alcances antiguos.'
);
$posDetallePersistidoGuardar = strpos($bloqueGuardarUbicacion, '$detallePersistidoId');
$posAutorizarGuardar = strpos($bloqueGuardarUbicacion, 'odontoAutorizarUbicacionLaboratorioDetalle');
$posPrepararGuardar = strpos($bloqueGuardarUbicacion, 'odontoPrepararModificacion');
$posActualizarGuardar = strpos(
    $bloqueGuardarUbicacion,
    'UPDATE odontograma_tratamiento_links SET venta_id=?'
);
pruebaLabAfirmar(
    $posDetallePersistidoGuardar !== false && $posAutorizarGuardar !== false
    && $posPrepararGuardar !== false && $posActualizarGuardar !== false
    && $posDetallePersistidoGuardar < $posAutorizarGuardar
    && $posAutorizarGuardar < $posPrepararGuardar && $posPrepararGuardar < $posActualizarGuardar,
    'Editar por item de presupuesto conserva y autoriza el detalle persistido antes de modificarlo.'
);
$bloqueDeshacerUbicacion = pruebaLabBloqueFuncion($fuenteOdontogramaPhp, 'odontoDeshacer');
$posObtenerLinkDeshacer = strpos(
    $bloqueDeshacerUbicacion,
    'SELECT * FROM odontograma_tratamiento_links WHERE id = ? AND odontograma_id = ? LIMIT 1'
);
$posAutorizarDeshacer = strpos($bloqueDeshacerUbicacion, 'odontoAutorizarUbicacionLaboratorioDetalle');
$posActualizarDeshacer = strpos(
    $bloqueDeshacerUbicacion,
    'UPDATE odontograma_tratamiento_links SET activo = 0'
);
pruebaLabAfirmar(
    $posObtenerLinkDeshacer !== false && $posAutorizarDeshacer !== false
    && $posActualizarDeshacer !== false && $posObtenerLinkDeshacer < $posAutorizarDeshacer
    && $posAutorizarDeshacer < $posActualizarDeshacer
    && strpos($fuenteOdontogramaJs, 'datos.detalle_venta_laboratorio_id') !== false,
    'Deshacer una ubicacion aplica la autorizacion del vinculo persistido antes de modificarla.'
);
$bloqueMigrarUbicacion = pruebaLabBloqueFuncion(
    $fuenteOdontogramaPhp,
    'odontoMigrarPresupuestoAVenta'
);
$posAutorizarMigracion = strpos(
    $bloqueMigrarUbicacion,
    'odontoAutorizarUbicacionLaboratorioDetalle'
);
$posActualizarMigracion = strpos(
    $bloqueMigrarUbicacion,
    'UPDATE odontograma_tratamiento_links SET venta_id = ?'
);
pruebaLabAfirmar(
    strpos($bloqueMigrarUbicacion, '$asignaciones = array()') !== false
    && $posAutorizarMigracion !== false && $posActualizarMigracion !== false
    && $posAutorizarMigracion < $posActualizarMigracion
    && strpos($bloqueMigrarUbicacion, '$mysqli->begin_transaction()') !== false
    && strpos($bloqueMigrarUbicacion, '$mysqli->commit()') !== false
    && strpos($bloqueMigrarUbicacion, '$mysqli->rollback()') !== false
    && strpos($fuenteOdontogramaJs, 'Venta guardada; ubicaciones pendientes') !== false,
    'Migrar ubicaciones autoriza antes de escribir, usa transaccion completa y comunica rechazos.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'trabajo_laboratorio.css?v=20260725-02') !== false,
    'La carga dinamica usa la misma version vigente de estilos del modulo.'
);
$bloqueCargaInicial = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'loadInitialData');
$bloqueCargaTrabajos = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'loadWorks');
$bloqueCargaCatalogos = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'loadCatalogs');
pruebaLabAfirmar(
    strpos($bloqueCargaInicial, 'loadWorks(false);') !== false
    && strpos($bloqueCargaInicial, 'loadCatalogs(') === false
    && strpos($bloqueCargaTrabajos, 'payload.respuesta_compacta = "1"') !== false
    && strpos($bloqueCargaCatalogos, 'respuesta_compacta: "1"') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'CATALOG_CACHE_MS = 5 * 60 * 1000') !== false
    && strpos($fuenteInicioHtml, 'trabajo-laboratorio-20260725-03') !== false,
    'La bandeja abre con su listado, difiere los catalogos y reutiliza su cache sin bloquear la vista.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'data-tlab-thumbnail-id=') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'function loadAuthorizedMedia(mediaId, thumbnail)') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'miniatura: thumbnail ? "1" : "0"') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'state.mediaRequests[cacheKey]') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'new window.IntersectionObserver') !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "case 'descargarMedia':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "\$entrada['miniatura']") !== false,
    'Las miniaturas se recuperan una vez, bajo autorizacion y solamente al acercarse a la vista.'
);
$bloqueListadoCompacto = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioListar');
$bloqueCustodiaCompacta = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioCadenasCustodiaPorTrabajos'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioPhp, '$respuestaCompacta') !== false
    && strpos(
        $fuenteTrabajoLaboratorioPhp,
        "array('respuesta_compacta' => \$respuestaCompacta)"
    ) !== false
    && strpos($bloqueListadoCompacto, '!$respuestaCompacta') !== false
    && strpos($bloqueListadoCompacto, "\$salida['trabajos'] = \$items") !== false
    && strpos($bloqueCustodiaCompacta, '$seleccionMiniatura = $incluirMiniaturas') !== false,
    'El servidor conserva el contrato antiguo y ofrece una respuesta compacta sin aliases ni imagenes incrustadas.'
);
pruebaLabAfirmar(
    strpos($fuenteMigracionPerformance, 'idx_tlab_estado_actualizacion') !== false
    && strpos($fuenteMigracionPerformance, 'idx_tlab_tecnico_estado_actualizacion') !== false
    && strpos($fuenteMigracionPerformance, 'idx_tlab_custodio_estado_actualizacion') !== false
    && substr_count($fuenteMigracionPerformance, 'INFORMATION_SCHEMA.STATISTICS') >= 4
    && stripos($fuenteMigracionPerformance, 'INSERT INTO') === false
    && stripos($fuenteMigracionPerformance, 'UPDATE ') === false
    && stripos($fuenteMigracionPerformance, 'DELETE FROM') === false,
    'La migracion de rendimiento queda separada, idempotente y sin modificaciones de registros.'
);
pruebaLabAfirmar(
    strpos($fuenteMigracionHiloCustodia, 'ADD COLUMN IF NOT EXISTS') !== false
    && strpos($fuenteMigracionHiloCustodia, 'ADD INDEX IF NOT EXISTS') !== false
    && strpos($fuenteMigracionHiloCustodia, 'FOREIGN KEY IF NOT EXISTS') !== false
    && strpos($fuenteMigracionHiloCustodia, 'information_schema.REFERENTIAL_CONSTRAINTS') !== false
    && strpos($fuenteMigracionHiloCustodia, 'id_evento_custodia_actualFK') !== false
    && strpos($fuenteMigracionHiloCustodia, 'id_evento_custodiaFK') !== false
    && strpos($fuenteMigracionHiloCustodia, 'UPDATE `trabajo_laboratorio` AS tl') !== false
    && strpos($fuenteMigracionHiloCustodia, 'UPDATE `trabajo_laboratorio_evento`') === false
    && strpos($fuenteMigracionHiloCustodia, 'CREATE PROCEDURE') === false
    && strpos($fuenteMigracionHiloCustodia, 'DELIMITER $$') === false
    && stripos($fuenteMigracionHiloCustodia, 'ROW_NUMBER(') === false
    && stripos($fuenteMigracionHiloCustodia, 'WITH ') === false,
    'La migracion para MariaDB 10.6 es aditiva, idempotente, no requiere rutinas y recupera solo el puntero.'
);
$contratosTransicion = array(
    'trabajoLaboratorioIniciar' => array(
        'accion' => 'iniciarTrabajo', 'estado' => 'pendiente_entrega_mecanico',
        'evento' => 'trabajo_iniciado', 'version' => false
    ),
    'trabajoLaboratorioAsignarTecnico' => array(
        'accion' => 'asignarTecnico', 'estado' => 'pendiente_entrega_mecanico',
        'evento' => 'tecnico_asignado', 'version' => true
    ),
    'trabajoLaboratorioIniciarTransferencia' => array(
        'accion' => 'iniciarTransferencia', 'estado' => 'en_transferencia_mecanico',
        'evento' => 'transferencia_mecanico_iniciada', 'version' => true
    ),
    'trabajoLaboratorioTomarHilo' => array(
        'accion' => 'tomarHilo', 'estado' => 'en_laboratorio',
        'evento' => 'recepcion_mecanico_confirmada', 'version' => true
    ),
    'trabajoLaboratorioIniciarDevolucion' => array(
        'accion' => 'iniciarDevolucion', 'estado' => 'en_transferencia_clinica',
        'evento' => 'devolucion_iniciada', 'version' => true
    ),
    'trabajoLaboratorioSolicitarAjuste' => array(
        'accion' => 'solicitarAjuste', 'estado' => 'ajuste_solicitado',
        'evento' => 'ajuste_solicitado', 'version' => true
    ),
    'trabajoLaboratorioAprobar' => array(
        'accion' => 'aprobarTrabajo', 'estado' => 'listo_instalacion',
        'evento' => 'trabajo_aprobado', 'version' => true
    ),
    'trabajoLaboratorioRegistrarInstalacion' => array(
        'accion' => 'registrarInstalacion', 'estado' => 'instalado',
        'evento' => 'instalacion_registrada', 'version' => true
    ),
    'trabajoLaboratorioCancelar' => array(
        'accion' => 'cancelarTrabajo', 'estado' => 'cancelado',
        'evento' => 'trabajo_cancelado', 'version' => true
    )
);
$bloquesTransicion = array();
foreach ($contratosTransicion as $funcionTransicion => $contrato) {
    $bloque = pruebaLabBloqueFuncion($fuenteHelper, $funcionTransicion);
    $bloquesTransicion[$funcionTransicion] = $bloque;
    $contratoBasico = $bloque !== ''
        && strpos($bloque, 'trabajoLaboratorioEjecutarComando') !== false
        && strpos($bloque, "'".$contrato['accion']."'") !== false
        && strpos($bloque, "'".$contrato['estado']."'") !== false
        && strpos($bloque, "'".$contrato['evento']."'") !== false;
    if ($contrato['version']) {
        $contratoBasico = $contratoBasico
            && strpos($bloque, 'trabajoLaboratorioExigirVersion') !== false
            && strpos($bloque, 'WHERE id=? AND version=?') !== false;
    } else {
        $contratoBasico = $contratoBasico
            && strpos($bloque, 'trabajoLaboratorioExigirVersion') === false;
    }
    pruebaLabAfirmar(
        $contratoBasico,
        'El comando '.$contrato['accion'].' conserva estado, evento, transaccion y concurrencia esperados.'
    );
}
$bloqueConfirmarRecepcion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioConfirmarRecepcion'
);
$bloqueConfirmarDevolucion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioConfirmarDevolucion'
);
pruebaLabAfirmar(
    strpos($bloqueConfirmarRecepcion, 'trabajoLaboratorioTomarHilo') !== false
    && strpos($bloqueConfirmarRecepcion, "'confirmarRecepcion'") !== false
    && strpos($bloqueConfirmarDevolucion, 'trabajoLaboratorioTomarHilo') !== false
    && strpos($bloqueConfirmarDevolucion, "'confirmarDevolucion'") !== false
    && strpos($bloqueConfirmarRecepcion, 'UPDATE trabajo_laboratorio') === false
    && strpos($bloqueConfirmarDevolucion, 'UPDATE trabajo_laboratorio') === false,
    'Las confirmaciones anteriores son aliases estrictos de Tomar el hilo y no conservan una escritura paralela.'
);
$bloqueTomarHilo = $bloquesTransicion['trabajoLaboratorioTomarHilo'];
$bloqueActualizarDatos = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioActualizarDatos'
);
$bloqueAplicarDatosVersion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioAplicarDatosVersion'
);
$bloqueDatosVersion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioDatosVersionEntrada'
);
$bloqueSnapshotDatos = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioSnapshotDatosTrabajo'
);
$bloquePuedeGestionarCosto = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioUsuarioPuedeGestionarCosto'
);
$bloqueRespuestaSinCostos = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioRespuestaSinCostos'
);
$bloqueRegistrarNovedad = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioRegistrarNovedad'
);
$bloqueRectificarCustodia = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioRectificarCustodia'
);
$bloqueRegistrarEventoCustodia = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioRegistrarEvento'
);
$bloqueActualizarPunteroCustodia = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioActualizarPunteroCustodia'
);
$bloqueExigirAccion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioExigirAccion'
);
$bloqueLimitesMedia = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioLimitesMedia'
);
$bloqueHttpArchivos = pruebaLabBloqueFuncion(
    $fuenteTrabajoLaboratorioPhp,
    'trabajoLaboratorioHttpArchivos'
);
$bloqueHttpContextoUsuario = pruebaLabBloqueFuncion(
    $fuenteTrabajoLaboratorioPhp,
    'trabajoLaboratorioHttpContextoUsuario'
);
$bloqueUsuarioDestinoTransferencia = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioUsuarioEsDestinoTransferenciaPendiente'
);
$bloquePuedeVerTrabajo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioPuedeVer'
);
$bloqueCondicionAccesoListado = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioCondicionAccesoListado'
);
$bloqueCadenaCustodia = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioCadenasCustodiaPorTrabajos'
);
$bloqueRecorridoOperativo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioRecorridosPorTrabajos'
);
$bloqueMiniHiloMensajes = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioMiniHilosPorMensajes'
);
pruebaLabAfirmar(
    strpos($bloqueTomarHilo, "array('conforme', 'con_observaciones')") !== false
    && strpos($bloqueTomarHilo, "'foto_recepcion_requerida'") !== false
    && strpos($bloqueTomarHilo, "'motivo_sin_foto_requerido'") === false
    && strpos($bloqueTomarHilo, "'detalle_sin_foto_requerido'") === false
    && strpos($bloqueTomarHilo, "'custodia_ya_cambio'") !== false
    && strpos($bloqueTomarHilo, 'AND version=? AND cod_custodio_actualFK=?') !== false,
    'Tomar el hilo exige condicion y foto nueva sin excepcion, y protege la custodia contra concurrencia.'
);
pruebaLabAfirmar(
    strpos($bloqueTomarHilo, "'recepcion_mecanico_confirmada'") !== false
    && strpos($bloqueTomarHilo, "'devolucion_confirmada'") !== false
    && strpos($bloqueTomarHilo, "'hilo_tomado'") !== false
    && strpos($bloqueTomarHilo, '$codUsuario') !== false
    && strpos($bloqueTomarHilo, 'trabajoLaboratorioExigirAccion') !== false
    && strpos($bloqueTomarHilo, "'actuo_en_representacion'") !== false
    && strpos($bloqueTomarHilo, "'destinatario_previsto'") !== false
    && strpos($bloqueTomarHilo, "'destinatario_no_autorizado'") === false
    && strpos($bloqueTomarHilo, "'receptor_clinica_invalido'") === false,
    'El usuario autenticado toma para si; un destinatario previsto distinto queda trazado como representacion.'
);
pruebaLabAfirmar(
    strpos($bloqueExigirAccion, 'trabajoLaboratorioExigirAcceso') !== false
    && strpos($bloqueExigirAccion, 'trabajoLaboratorioAccionesPermitidas') !== false,
    'Toda accion vuelve a validar acceso al trabajo y disponibilidad en servidor.'
);
pruebaLabAfirmar(
    strpos($bloquePuedeVerTrabajo, 'VERTRABAJOSLABORATORIO') !== false
    && strpos($bloquePuedeVerTrabajo, 'trabajoLaboratorioUsuarioEsAuditor') !== false
    && strpos($bloqueCondicionAccesoListado, "? '1=1' : '0=1'") !== false
    && strpos($bloqueCondicionAccesoListado, 'VERTRABAJOSLABORATORIO') !== false
    && strpos($bloqueCondicionAccesoListado, 'trabajoLaboratorioUsuarioEsAuditor') !== false
    && strpos($bloqueCondicionAccesoListado, 'cod_local_destinoFK') === false,
    'El hilo completo requiere permiso de laboratorio o auditoria, sin restringir los trabajos por sucursal.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'state.context.forzar_bandeja === true') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'state.context.forzar_bandeja !== false') === false,
    'El mecanico abre todos los trabajos y conserva Mi bandeja como filtro voluntario.'
);
pruebaLabAfirmar(
    strpos($bloqueMiniHiloMensajes, 'cod_mensaje_hiloFK') !== false
    && strpos($bloqueMiniHiloMensajes, '$idEventoLimite') !== false
    && strpos($bloqueMiniHiloMensajes, 'break;') !== false
    && strpos($bloqueMiniHiloMensajes, "'datos_trabajo_actualizados'") !== false
    && strpos($bloqueMiniHiloMensajes, "'hilo_cerrado'") !== false,
    'El mini hilo de cada mensaje se limita por su evento exacto, integra versiones en el nodo y deriva el cierre sin persistirlo.'
);
pruebaLabAfirmar(
    $bloqueActualizarDatos !== ''
    && strpos($bloqueActualizarDatos, "'actualizarDatosTrabajo'") !== false
    && strpos($bloqueActualizarDatos, "'edicion_nodo_no_autorizada'") !== false
    && strpos($bloqueActualizarDatos, 'trabajoLaboratorioExigirVersion') !== false
    && strpos($bloqueActualizarDatos, 'id_evento_custodiaFK') === false
    && strpos($bloqueActualizarDatos, "'datos_trabajo_actualizados'") !== false
    && strpos($bloqueActualizarDatos, "'datos_base_nodo'") !== false
    && strpos($bloqueActualizarDatos, "'campos_modificados'") !== false
    && strpos($bloqueActualizarDatos, 'AND version=? AND cod_custodio_actualFK=?') !== false
    && strpos($bloqueAplicarDatosVersion, 'cod_cliente') === false
    && strpos($bloqueAplicarDatosVersion, 'cod_producto') === false
    && strpos($bloqueAplicarDatosVersion, 'estado_derivado=?') !== false,
    'Solo el custodio vigente edita datos operativos; paciente, producto y estado quedan fuera de la escritura y cada cambio genera auditoria.'
);
pruebaLabAfirmar(
    strpos($bloqueDatosVersion, '$codUsuario') !== false
    && strpos($bloqueDatosVersion, "\$valor('cod_tipo_trabajo'") === false
    && strpos($bloqueDatosVersion, "\$valor('cod_especialista'") === false
    && strpos($bloqueDatosVersion, "isset(\$trabajo['cod_tipo_trabajoFK'])") !== false
    && strpos($bloqueDatosVersion, "isset(\$trabajo['cod_especialistaFK'])") !== false
    && strpos($bloqueSnapshotDatos, "'cod_iniciador'") !== false
    && strpos($bloqueSnapshotDatos, "'iniciador'") !== false
    && strpos($fuenteHelper, 'pini.nombre_persona AS nombre_iniciador') !== false
    && strpos($fuenteHelper, 'uini.cod_usuario=tl.cod_usuarioFK_create') !== false,
    'El producto de la venta y el usuario que inicio el trabajo permanecen como datos de origen inmutables.'
);
pruebaLabAfirmar(
    strpos($bloquePuedeGestionarCosto, 'trabajoLaboratorioUsuarioEsAuditor') !== false
    && strpos($bloquePuedeGestionarCosto, 'trabajoLaboratorioObtenerTecnicoFormal') !== false
    && strpos($bloquePuedeGestionarCosto, "'administrativo'") !== false
    && strpos($bloqueDatosVersion, 'trabajoLaboratorioUsuarioPuedeGestionarCosto') !== false
    && strpos($bloqueDatosVersion, ': $costoActual') !== false
    && strpos($bloqueRespuestaSinCostos, "'costo_estimado'") !== false
    && strpos($bloqueRespuestaSinCostos, "'costo_original'") !== false
    && strpos($bloqueHttpContextoUsuario, "'puede_gestionar_costo'") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, 'trabajoLaboratorioRespuestaSinCostos($respuesta)') !== false,
    'El costo se conserva en servidor y solo Administracion o Auditoria pueden consultarlo o modificarlo.'
);
$respuestaSinCostos = trabajoLaboratorioRespuestaSinCostos(array(
    'trabajo' => array(
        'id' => 1,
        'costo_estimado' => 250,
        'versiones' => array(array('costo_original' => 200, 'estado' => 'activo'))
    )
));
pruebaLabAfirmar(
    !isset($respuestaSinCostos['trabajo']['costo_estimado'])
    && !isset($respuestaSinCostos['trabajo']['versiones'][0]['costo_original'])
    && $respuestaSinCostos['trabajo']['versiones'][0]['estado'] === 'activo',
    'La respuesta para perfiles sin acceso elimina costos anidados sin quitar los demas datos del hilo.'
);
pruebaLabAfirmar(
    strpos($bloqueTomarHilo, 'trabajoLaboratorioDatosVersionEntrada') !== false
    && strpos($bloqueTomarHilo, 'trabajoLaboratorioAplicarDatosVersion') !== false
    && strpos($bloqueTomarHilo, "'datos_trabajo'") !== false
    && strpos($bloqueTomarHilo, "'campos_modificados'") !== false
    && strpos($bloqueTomarHilo, 'trabajoLaboratorioSnapshotDatosTrabajo') !== false,
    'Tomar el hilo confirma los datos revisados como nueva version oficial dentro de la misma transaccion.'
);
pruebaLabAfirmar(
    strpos($bloqueTomarHilo, '$completaTransferencia = false;') !== false
    && strpos($bloqueTomarHilo, '$localReceptor') !== false
    && strpos($bloqueTomarHilo, '$localDestino') !== false
    && strpos($bloqueTomarHilo, '$destinatarioPrevisto === intval($codUsuario)') !== false
    && strpos($bloqueTomarHilo, '$localReceptor === $localDestino') !== false
    && strpos($bloqueTomarHilo, "'transferencia_completada'") !== false
    && strpos($bloqueTomarHilo, "'transferencia_continua'") !== false
    && substr_count($bloqueTomarHilo, 'id_transferencia_pendienteFK=NULL') === 2
    && strpos(
        $bloqueTomarHilo,
        'UPDATE trabajo_laboratorio SET cod_custodio_actualFK=?,version=?'
    ) !== false,
    'Un custodio intermedio cambia la responsabilidad sin consumir el traslado; solo destino o local destino completan la etapa.'
);
pruebaLabAfirmar(
    strpos($bloqueRegistrarEventoCustodia, '$usuarioUbicacion = $actor;') !== false
    && strpos($bloqueRegistrarEventoCustodia, "!empty(\$metadata['nodo_custodia'])") !== false
    && strpos($bloqueRegistrarEventoCustodia, 'trabajoLaboratorioUsuario($mysqli, $custodioNuevo)') !== false
    && strpos($bloqueRegistrarEventoCustodia, '$codLocal = intval($usuarioUbicacion[\'cod_localFK\'])') !== false
    && strpos($bloqueRegistrarEventoCustodia, "\$usuarioUbicacion['nombre_local']") !== false,
    'El nodo registra codigo y snapshot del local del custodio nuevo, no la sucursal fija del trabajo.'
);
pruebaLabAfirmar(
    strpos($bloqueLimitesMedia, "ini_get('upload_max_filesize')") !== false
    && strpos($bloqueLimitesMedia, "ini_get('post_max_size')") !== false
    && strpos($bloqueLimitesMedia, '$maximoArchivos--') !== false
    && strpos($bloqueHttpArchivos, "['max_archivos']") !== false
    && strpos($bloqueHttpArchivos, "['max_bytes_archivo']") !== false
    && strpos($bloqueHttpContextoUsuario, "'limites_media' => trabajoLaboratorioLimitesMedia()") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "'max_bytes_solicitud'") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "'carga_total_excedida'") !== false,
    'El backend deriva cantidad, peso individual y carga total desde php.ini y los publica en el contexto.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'var MAX_FILES = 3;') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'var MAX_FILE_SIZE = 2 * 1024 * 1024;') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'state.context.limites_media || {}') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'mediaLimits.max_archivos') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'mediaLimits.max_bytes_archivo') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'formatFileLimit(MAX_FILE_SIZE)') !== false
    && strpos($fuenteTrabajoLaboratorioJs, '10 MB') === false,
    'La interfaz adopta los limites del servidor y muestra tres archivos de 2 MB como valores seguros iniciales.'
);
pruebaLabAfirmar(
    $bloqueRegistrarNovedad !== ''
    && strpos($bloqueRegistrarNovedad, "'registrarNovedad'") !== false
    && strpos($bloqueRegistrarNovedad, "'novedad_custodia'") !== false
    && strpos($bloqueRegistrarNovedad, "'custodia_no_vigente'") !== false
    && strpos($bloqueRegistrarNovedad, '$idEventoCustodia') !== false
    && strpos($bloqueRegistrarNovedad, 'trabajoLaboratorioGuardarMediaProtegida') !== false
    && strpos($bloqueRegistrarNovedad, 'EVIDENCIATRABAJOLABORATORIO') === false,
    'La novedad pertenece al periodo vigente, admite adjuntos y no exige un permiso adicional al custodio.'
);
pruebaLabAfirmar(
    $bloqueRectificarCustodia !== ''
    && strpos($bloqueRectificarCustodia, "'rectificarCustodia'") !== false
    && strpos($bloqueRectificarCustodia, "'justificacion_rectificacion_requerida'") !== false
    && strpos($bloqueRectificarCustodia, "if (".'$justificacion'." === '')") !== false
    && strpos($bloqueRectificarCustodia, 'strlen($justificacion) < 5') === false
    && strpos($bloqueRectificarCustodia, 'trabajoLaboratorioUsuario($mysqli, $codCustodioNuevo)') !== false
    && strpos($bloqueRectificarCustodia, "'custodio_rectificacion_invalido'") !== false
    && strpos($bloqueRectificarCustodia, "'custodia_rectificada'") !== false
    && strpos($bloqueRectificarCustodia, 'AND version=? AND cod_custodio_actualFK=?') !== false,
    'La rectificacion administrativa exige motivo, cuenta Telar activa, version y custodio anterior vigentes.'
);
pruebaLabAfirmar(
    strpos($bloqueRegistrarEventoCustodia, 'actor_nombre_snapshot') !== false
    && strpos($bloqueRegistrarEventoCustodia, 'actor_rol_snapshot') !== false
    && strpos($bloqueRegistrarEventoCustodia, 'local_nombre_snapshot') !== false
    && strpos($bloqueRegistrarEventoCustodia, 'id_evento_custodiaFK') !== false
    && strpos($bloqueRegistrarEventoCustodia, 'trabajoLaboratorioEventoAbreCustodia') !== false
    && strpos($bloqueRegistrarEventoCustodia, 'trabajoLaboratorioActualizarPunteroCustodia') !== false
    && strpos($bloqueActualizarPunteroCustodia, 'WHERE id=? AND cod_custodio_actualFK=? AND version=?') !== false,
    'Cada nodo conserva snapshots y actualiza el puntero solo para el custodio y la version resultantes.'
);
pruebaLabAfirmar(
    $bloqueCadenaCustodia !== ''
    && substr_count($bloqueCadenaCustodia, '$mysqli->prepare(') >= 2
    && strpos($bloqueCadenaCustodia, "'trabajo_iniciado'") !== false
    && strpos($bloqueCadenaCustodia, "'hilo_tomado'") !== false
    && strpos($bloqueCadenaCustodia, "'custodia_rectificada'") !== false
    && strpos($bloqueCadenaCustodia, "'novedades_cantidad'") !== false
    && strpos($bloqueCadenaCustodia, "'datos_trabajo'") !== false
    && strpos($bloqueCadenaCustodia, "'eventos_version'") !== false
    && strpos($bloqueCadenaCustodia, "'datos_trabajo_actualizados'") !== false
    && strpos($bloqueCadenaCustodia, "'duracion_segundos'") !== false
    && strpos($bloqueCadenaCustodia, "'en_transporte'") !== false
    && strpos($bloqueCadenaCustodia, "'actual'") !== false
    && strpos($bloqueCadenaCustodia, "'terminal'") !== false,
    'La cadena se proyecta por lote con nodos, periodos, versiones, novedades, transporte y cierre terminal diferenciados.'
);
pruebaLabAfirmar(
    strpos($bloqueCadenaCustodia, "'estado_terminal'") !== false
    && strpos($bloqueCadenaCustodia, "'motivo_cierre'") !== false
    && strpos($bloqueCadenaCustodia, "'cancelacion'") !== false
    && strpos($bloqueCadenaCustodia, "'instalacion'") !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'cancelled = terminal && /cancel/.test(terminalState);') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'terminal && !cancelled') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'work.cancelled') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'is-cancelled') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'Trabajo cancelado') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'Custodia cerrada sin entrega final') !== false,
    'La cancelacion cierra el periodo, pero no se presenta como entrega ni como custodia final.'
);
pruebaLabAfirmar(
    strpos($bloqueRecorridoOperativo, "'hilo_tomado'") !== false
    && strpos($bloqueRecorridoOperativo, "'novedad_custodia'") !== false
    && strpos($bloqueRecorridoOperativo, "'custodia_rectificada'") !== false
    && strpos($bloqueRecorridoOperativo, 'NOT IN') !== false,
    'El recorrido operativo excluye los eventos exclusivos del hilo de custodia.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioPhp, "case 'tomarHilo':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "case 'actualizarDatosTrabajo':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "case 'registrarNovedad':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "case 'registrarNovedadCustodia':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "case 'rectificarCustodia':") !== false,
    'El endpoint publica toma, actualizacion versionada y acciones canonicas, conservando el alias de novedades anterior.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'function unifiedWorkRoute(work)') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'cadena_custodia') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'data-tlab-node-lane="unificado"') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'Hilo del trabajo') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'data-tlab-take-node') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'fa-hand-holding') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'inlineDetailHtml("operativo", work.id, rowIndex)') === false,
    'Cada tarjeta dibuja un solo hilo, deduplica eventos y cierra con el nodo visual para tomarlo.'
);
$bloqueListarHistoricos = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoListarHistoricos'
);
$bloqueObtenerHistorico = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoObtenerHistorico'
);
$bloquePrepararResolucionHistorica = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoPrepararResolucion'
);
$bloqueCandidatosResolucionHistorica = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoCandidatosDetalle'
);
$bloqueFinalizarTratamientoHistorico = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoFinalizarTratamiento'
);
$bloqueResolverHistorico = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoResolverHistorico'
);
$bloquePromoverHistorico = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoPromoverHistorico'
);
$bloqueExigirUsuarioHistorico = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoExigirUsuarioActivo'
);
pruebaLabAfirmar(
    strpos($bloqueListarHistoricos, 'trabajoLaboratorioHistoricoExigirUsuarioActivo') !== false
    && strpos($bloqueListarHistoricos, 'h.id_trabajo_laboratorioFK IS NULL') !== false
    && strpos($bloqueListarHistoricos, "h.estado_convalidacion<>'integrado_operativo'") !== false
    && strpos($bloqueObtenerHistorico, 'trabajoLaboratorioHistoricoExigirUsuarioActivo') !== false
    && strpos($bloqueExigirUsuarioHistorico, 'VERTRABAJOSLABORATORIO') !== false
    && strpos($bloqueExigirUsuarioHistorico, 'trabajoLaboratorioUsuarioEsAuditor') !== false
    && strpos($bloqueObtenerHistorico, "'puede_resolver'") !== false,
    'El permiso de laboratorio habilita pendientes historicos y los resueltos dejan esa bandeja.'
);
pruebaLabAfirmar(
    $bloqueResolverHistorico !== ''
    && strpos($bloqueResolverHistorico, 'trabajoLaboratorioHistoricoPromoverHistorico') !== false
    && strpos($bloquePrepararResolucionHistorica, "if (\$modo === 'instalado_entregado')") !== false
    && strpos($bloquePrepararResolucionHistorica, "'foto_entrega_requerida'") !== false
    && strpos($bloquePrepararResolucionHistorica, "\$sinFotoHistorica") !== false
    && strpos($bloquePrepararResolucionHistorica, "count(\$evidencias) < 1 && !\$sinFotoHistorica") !== false
    && strpos($bloquePrepararResolucionHistorica, "(string)\$entrada['sin_foto_historica'] === '1'") !== false
    && strpos($bloquePromoverHistorico, "'foto_historica_no_disponible'") !== false
    && strpos($bloquePromoverHistorico, "'sin_foto'") !== false
    && strpos($bloquePrepararResolucionHistorica, 'trabajoLaboratorioDetalleClinicoActivo') !== false
    && strpos($bloquePrepararResolucionHistorica, "'cod_custodio_actualFK'] = intval(\$codUsuario)") !== false
    && strpos($bloquePromoverHistorico, "'registro_historico_continuado'") !== false
    && strpos($bloquePromoverHistorico, "'instalacion_historica_declarada'") !== false,
    'Resolver un historico crea un nodo normal y admite ausencia explicita de foto solo en el cierre historico.'
);
pruebaLabAfirmar(
    strpos($bloquePrepararResolucionHistorica, 'trabajoLaboratorioUsuarioPuedeGestionarCosto') !== false
    && strpos($bloquePrepararResolucionHistorica, "\$historico['costo_legacy']") !== false,
    'Resolver un historico sin permiso de costos conserva el valor original y no acepta reemplazarlo.'
);
pruebaLabAfirmar(
    strpos($bloqueCandidatosResolucionHistorica, "'finalizado' => !\$detalleActivo") !== false
    && strpos($bloqueCandidatosResolucionHistorica, "'puede_continuar' => \$detalleActivo") !== false
    && strpos($bloquePrepararResolucionHistorica, "'modo_recomendado' => 'instalado_entregado'") !== false
    && strpos($bloquePrepararResolucionHistorica, "'progreso_porcentaje'") !== false,
    'El servidor identifica tratamientos finalizados y recomienda explicitamente el cierre historico.'
);
pruebaLabAfirmar(
    strpos($bloqueFinalizarTratamientoHistorico, 'progreso_porcentaje=?') !== false
    && strpos($bloqueFinalizarTratamientoHistorico, "'porcentaje' => 100") !== false
    && strpos($bloqueFinalizarTratamientoHistorico, 'INSERT INTO evoluciontratamiento') === false
    && strpos($bloquePromoverHistorico, 'trabajoLaboratorioHistoricoFinalizarTratamiento') !== false
    && strpos($bloquePromoverHistorico, "'sin_evolucion_clinica'") !== false,
    'Instalado y entregado lleva el tratamiento al 100 por ciento sin crear una evolucion clinica.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioPhp, "case 'resolverHistorico':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, 'trabajoLaboratorioHistoricoResolverHistorico') !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "'puede_resolver_historicos'") !== false,
    'El endpoint publica la resolucion historica para una sesion autorizada.'
);
$bloqueNodoOriginalHistorico = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoNodoOriginal'
);
$bloqueOrigenesHistoricos = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoOriginalesPorTrabajos'
);
$bloqueRutaUnificada = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'unifiedWorkRoute'
);
$bloqueNodoCierre = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'closureNode'
);
$bloqueFinHilo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'pendingNodeHtml'
);
$bloqueAccionesNodo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'nodeActionsHtml'
);
$bloqueTarjetaTrabajo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'workCardHtml'
);
$bloquePopoverCierre = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'closurePopoverHtml'
);
$bloqueAbrirNodo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'openNodePopover'
);
pruebaLabAfirmar(
    strpos($bloqueNodoOriginalHistorico, "'tipo_evento' => 'registro_original'") !== false
    && strpos($bloqueNodoOriginalHistorico, "'id_historico'") !== false
    && strpos($bloqueOrigenesHistoricos, 'id_trabajo_laboratorioFK IN') !== false
    && strpos($bloqueRutaUnificada, 'work.historicalOrigin') !== false
    && strpos($fuenteHelper, "'registro_historico_original'") !== false,
    'El trabajo promovido conserva Registro original como primer nodo consultable sin reescribir el snapshot.'
);
pruebaLabAfirmar(
    strpos($bloqueNodoCierre, '"hilo_cerrado"') !== false
    && strpos($bloqueNodoCierre, 'referencia_nodo_anterior') !== false
    && strpos($bloqueFinHilo, 'data-tlab-node-lane="cierre"') !== false
    && strpos($bloquePopoverCierre, '100 % finalizado') !== false
    && strpos($bloquePopoverCierre, 'solo lectura') !== false
    && strpos($bloqueAbrirNodo, 'lane === "cierre"') !== false
    && strpos($bloqueAbrirNodo, 'loadHistoricalNodeEnvelope(historicalId || rowId)') !== false,
    'El cierre es un resumen consultable y el nodo original integrado abre su ficha historica propia.'
);
$bloqueCamposCierreOperativo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'renderActionFields'
);
$bloqueValidarCierreOperativo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'validateActionStep'
);
$bloquePayloadCierreOperativo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'actionPayload'
);
$bloqueSubmitCierreOperativo = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'submitAction'
);
pruebaLabAfirmar(
    strpos($bloqueFinHilo, 'data-tlab-action="registrarInstalacion"') === false
    && strpos($bloqueFinHilo, 'Cierre disponible') !== false
    && strpos($bloqueFinHilo, 'ltimo nodo para finalizar') !== false
    && strpos($bloqueAccionesNodo, 'data-tlab-popover-action="registrarInstalacion"') !== false
    && strpos($bloqueAccionesNodo, 'CUSTODIO ACTUAL') !== false
    && strpos($bloqueAccionesNodo, 'Instalado y finalizado') !== false
    && strpos($bloqueAccionesNodo, 'Transferencia pendiente') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'submitLabel: "Confirmar instalaci') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'registrarInstalacion: {') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'evidence: true') !== false
    && strpos($bloqueCamposCierreOperativo, 'name="condicion_pre_entrega"') !== false
    && strpos($bloqueCamposCierreOperativo, 'name="observacion_entrega"') !== false
    && strpos($bloqueCamposCierreOperativo, 'name="sin_foto"') !== false
    && strpos($bloqueCamposCierreOperativo, '"motivo_sin_foto"') !== false
    && strpos($bloqueCamposCierreOperativo, 'name="detalle_sin_foto"') !== false
    && strpos($bloqueCamposCierreOperativo, 'sin una cantidad') !== false
    && strpos($bloqueValidarCierreOperativo, 'values.observacion_entrega).trim()') !== false
    && strpos($bloqueValidarCierreOperativo, 'values.motivo_sin_foto).trim()') !== false
    && strpos($bloqueValidarCierreOperativo, 'values.detalle_sin_foto).trim()') !== false
    && strpos($bloquePayloadCierreOperativo, 'payload.modo_resolucion = "instalado_entregado"') !== false
    && strpos($bloquePayloadCierreOperativo, 'state.action.code === "registrarInstalacion"') !== false
    && strpos($bloqueTarjetaTrabajo, 'ltimo nodo para finalizar') !== false
    && strpos($bloqueTarjetaTrabajo, 'action.code !== "cancelarTrabajo"') !== false
    && strpos($bloqueSubmitCierreOperativo, 'closeDetail(true)') !== false
    && substr_count($bloqueSubmitCierreOperativo, '"finalizados"') >= 2,
    'Vista operativa y Mi bandeja guian al custodio al ultimo nodo para cerrar con foto o excepcion justificada.'
);
$bloqueBindEvents = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'bindEvents');
$bloqueNodeEditor = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'nodeEditorHtml');
$bloqueVersionOperativa = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'nodeVersionPopoverHtml'
);
$bloqueVersionHistorica = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'historicalVersionPopoverHtml'
);
$bloqueSubmitNode = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'submitNodeVersion');
$bloqueCerrarNodo = pruebaLabBloqueJavascript($fuenteTrabajoLaboratorioJs, 'closeNodePopover');
$bloqueTarjetaResolucionHistorica = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'historicalResolverHtml'
);
$bloqueSubmitResolucionHistorica = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'submitHistoricalResolver'
);
$bloqueCandidatoHistoricoFinalizado = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'historicalCandidateIsFinalized'
);
$bloqueTarjetaHistorica = pruebaLabBloqueJavascript(
    $fuenteTrabajoLaboratorioJs,
    'historicalCardHtml'
);
pruebaLabAfirmar(
    strpos($bloqueBindEvents, 'mouseover') === false
    && strpos($bloqueBindEvents, 'mouseout') === false
    && strpos($bloqueBindEvents, 'focusin') === false
    && strpos($bloqueBindEvents, 'focusout') === false
    && strpos($fuenteTrabajoLaboratorioJs, 'toggleNodePopover(nodeTrigger') !== false
    && substr_count($fuenteTrabajoLaboratorioJs, 'id="tlabNodePopover"') === 1
    && strpos($bloqueCerrarNodo, 'state.nodePopoverRecord = null') !== false,
    'El detalle se abre solo por clic, existe un unico popover y se cierra al cambiar de nodo o hacer clic fuera.'
);
pruebaLabAfirmar(
    strpos($bloqueVersionOperativa, 'nodeWorkFieldHtml("Tipo de trabajo", snapshot.producto') !== false
    && strpos($bloqueVersionOperativa, 'nodeWorkFieldHtml("Iniciado por"') !== false
    && strpos($bloqueVersionOperativa, 'nodeWorkFieldHtml("Doctor"') === false
    && strpos($bloqueVersionOperativa, 'canManageWorkCost()') !== false
    && strpos($bloqueVersionHistorica, 'nodeWorkFieldHtml("Tipo de trabajo", historical.tipo_trabajo') !== false
    && strpos($bloqueVersionHistorica, 'nodeWorkFieldHtml("Doctor", historical.doctor') !== false
    && strpos($bloqueVersionHistorica, 'canManageWorkCost()') !== false,
    'La vista operativa usa el producto y el iniciador; la version historica conserva sus datos originales y protege el costo.'
);
pruebaLabAfirmar(
    strpos($bloqueNodeEditor, '<small>Tipo de trabajo</small>') !== false
    && strpos($bloqueNodeEditor, '<small>Iniciado por</small>') !== false
    && strpos($bloqueNodeEditor, 'Estado del proceso') !== false
    && strpos($bloqueNodeEditor, 'nodeSelectHtml("Tipo de trabajo"') === false
    && strpos($bloqueNodeEditor, 'nodeSelectHtml("Doctor"') === false
    && strpos($bloqueNodeEditor, 'canManageWorkCost()') !== false
    && strpos($bloqueNodeEditor, 'name="condicion_recepcion"') !== false
    && strpos($bloqueNodeEditor, 'data-tlab-node-file-input') !== false
    && strpos($bloqueNodeEditor, 'name="sin_foto"') === false
    && strpos($bloqueSubmitNode, 'Agregá al menos una fotografía nueva') !== false
    && strpos($bloqueSubmitNode, 'datos_trabajo:') !== false
    && strpos($bloqueSubmitNode, 'cod_tipo_trabajo:') === false
    && strpos($bloqueSubmitNode, 'cod_especialista:') === false
    && strpos($bloqueSubmitNode, 'payload.datos_trabajo.costo_estimado') !== false
    && strpos($bloqueSubmitNode, 'endpoint = "actualizarDatosTrabajo"') !== false,
    'La tarjeta operativa fija tipo e iniciador, oculta costo sin permiso y limita la edicion a los datos de seguimiento.'
);
pruebaLabAfirmar(
    strpos($bloqueTarjetaHistorica, 'inlineDetailHtml') === false
    && strpos($fuenteTrabajoLaboratorioJs, 'data-tlab-resolve-historical') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'Continuar trabajo') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'Instalado y entregado') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'name="sin_foto_historica"') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'Sin fotografía histórica disponible') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'canManageWorkCost()') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'function photoExceptionLabel') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'foto_historica_no_disponible') !== false
    && strpos($bloqueSubmitResolucionHistorica, 'request("resolverHistorico"') !== false
    && strpos($bloqueSubmitResolucionHistorica, '!state.nodeFiles.length && !noHistoricalPhoto') !== false
    && strpos($bloqueSubmitResolucionHistorica, 'sin_foto_historica: noHistoricalPhoto ? "1" : "0"') !== false
    && strpos($bloqueSubmitResolucionHistorica, 'payload.costo_estimado') !== false
    && strpos($bloqueCerrarNodo, 'state.historicalResolver = null') !== false,
    'El cierre historico exige foto o una declaracion explicita de que ya no esta disponible.'
);
pruebaLabAfirmar(
    strpos($bloqueCandidatoHistoricoFinalizado, 'progreso_porcentaje') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'continuationBlocked') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'disabled') !== false
    && strpos($bloqueTarjetaResolucionHistorica, 'Tratamiento finalizado') !== false
    && strpos($bloqueSubmitResolucionHistorica, 'input[name="modo_resolucion"]:checked') !== false
    && strpos($bloqueSubmitResolucionHistorica, 'error.code === "tratamiento_ya_finalizado"') !== false
    && strpos($bloqueSubmitResolucionHistorica, 'resolver.values.modo_resolucion = "instalado_entregado"') !== false,
    'La interfaz fija el cierre para un tratamiento finalizado y verifica directamente el modo antes de enviarlo.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'function closeAction(force)') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'state.action && state.action.saving && force !== true') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'if (state.action && state.action.saving)') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'else if (!state.root.querySelector("#tlabActionLayer").hidden) { closeAction(); }') !== false
    && strpos($fuenteTrabajoLaboratorioJs, "action.saving ? 'disabled' : ''") !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'closeAction(true);') !== false,
    'Cerrar, Cancelar, Escape y el cierre del modulo respetan el guardado; solo el exito fuerza el cierre.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'condicion_recepcion') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'Confirmar y tomar el hilo') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'tipo_novedad') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'application/pdf') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'cod_custodio_rectificado') !== false,
    'Los dialogos guiados envian condicion, novedad con PDF y rectificacion auditada.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioCss, '.tlab-shell .tlab-unified-lane') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '.tlab-shell .tlab-thread-row--unified') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '.tlab-shell .tlab-node-work-field.is-modified') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '@media (max-width: 760px)') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '@media (max-width: 620px)') !== false,
    'Los estilos del hilo unico, versiones modificadas y popover permanecen aislados y son adaptables.'
);
$contratosSubevento = array(
    'trabajoLaboratorioAgregarEvidencia' => array('agregarEvidencia', 'evidencia_agregada'),
    'trabajoLaboratorioAgregarNota' => array('agregarNota', 'nota_agregada')
);
foreach ($contratosSubevento as $funcionSubevento => $contratoSubevento) {
    $bloque = pruebaLabBloqueFuncion($fuenteHelper, $funcionSubevento);
    pruebaLabAfirmar(
        $bloque !== ''
        && strpos($bloque, 'trabajoLaboratorioEjecutarComando') !== false
        && strpos($bloque, "'".$contratoSubevento[0]."'") !== false
        && strpos($bloque, "'".$contratoSubevento[1]."'") !== false
        && strpos($bloque, 'trabajoLaboratorioExigirVersion') !== false
        && strpos($bloque, 'trabajoLaboratorioIncrementarVersion') !== false
        && strpos($bloque, 'trabajoLaboratorioEventoCustodiaActual') !== false
        && strpos($bloque, "'id_evento_custodia'") !== false
        && strpos($bloque, 'SET estado_derivado') === false,
        'El subevento '.$contratoSubevento[0].' incrementa version, queda dentro del nodo de custodia y no cambia el estado.'
    );
}

$bloqueComando = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioEjecutarComando');
$posBegin = strpos($bloqueComando, 'begin_transaction');
$posPreparar = strpos($bloqueComando, 'trabajoLaboratorioPrepararIdempotencia');
$posCallback = strpos($bloqueComando, 'call_user_func');
$posCompletar = strpos($bloqueComando, 'trabajoLaboratorioCompletarIdempotencia');
$posCommitFinal = strrpos($bloqueComando, '$mysqli->commit()');
pruebaLabAfirmar(
    $bloqueComando !== ''
    && strpos($bloqueComando, 'if (!$mysqli->begin_transaction())') !== false
    && strpos($bloqueComando, "'transaccion_no_iniciada'") !== false
    && $posBegin !== false && $posPreparar !== false && $posCallback !== false
    && $posCompletar !== false && $posCommitFinal !== false
    && $posBegin < $posPreparar && $posPreparar < $posCallback
    && $posCallback < $posCompletar && $posCompletar < $posCommitFinal
    && substr_count($bloqueComando, '$mysqli->rollback()') >= 2
    && substr_count($bloqueComando, '$commitIniciado = true') >= 2
    && substr_count($bloqueComando, 'if (!$commitIniciado)') >= 2
    && strpos($bloqueComando, 'catch (Exception $e)') !== false
    && strpos($bloqueComando, 'catch (Throwable $e)') !== false,
    'El comando inicia transaccion, completa idempotencia antes del commit y revierte ambos tipos de error.'
);
$bloqueTecnicos = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioTecnicosDisponibles');
$bloqueInicioTrabajo = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioIniciar');
pruebaLabAfirmar(
    strpos($bloqueTecnicos, '$soloHabilitados') !== false
    && strpos($bloqueTecnicos, 'VERTRABAJOSLABORATORIO') !== false
    && strpos($bloqueTecnicos, 'RECIBIRTRABAJOLABORATORIO') !== false
    && strpos($bloqueTecnicos, 'ENTREGARTRABAJOLABORATORIO') !== false
    && strpos($bloqueInicioTrabajo, "'tecnico_sin_acceso_laboratorio'") !== false,
    'Solo un tecnico formal con permisos para completar el circuito puede recibir una asignacion nueva.'
);
$bloqueAsignarTecnico = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioAsignarTecnico');
pruebaLabAfirmar(
    strpos($bloqueInicioTrabajo, '$tecnico = null;') !== false
    && strpos($bloqueInicioTrabajo, '$estado = $tecnico ? \'pendiente_entrega_mecanico\' : \'pendiente_tecnico\';') !== false
    && strpos($bloqueInicioTrabajo, "'tecnico_pendiente' => ".'$tecnico'." ? 0 : 1") !== false
    && strpos($bloqueAsignarTecnico, "'asignarTecnicoUnidad'") !== false
    && strpos($bloqueAsignarTecnico, "estado_derivado='pendiente_tecnico'") !== false
    && strpos($bloqueAsignarTecnico, "'transferencia_iniciada' => 0") !== false
    && strpos($bloqueAsignarTecnico, 'trabajo_laboratorio_transferencia') === false,
    'El inicio admite Tecnico pendiente y la asignacion posterior actualiza el origen completo sin iniciar traslado.'
);
$bloqueContextoDetalle = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioObtenerContextoDetalle'
);
pruebaLabAfirmar(
    strpos($bloqueContextoDetalle, 'VERFORMULARIOCONSULTORIO') !== false
    && strpos($bloqueContextoDetalle, "'puede_asignar_ubicacion'") !== false
    && strpos($bloqueContextoDetalle, "'antecedente_historico_existente'") !== false
	&& strpos($bloqueContextoDetalle, "'requiere_regularizacion_administrativa'") !== false
	&& strpos($bloqueContextoDetalle, "'regularizar_detalle_historico'") !== false
	&& strpos($bloqueContextoDetalle, '$detallePreparadoParaAcciones') !== false
    && strpos($bloqueInicioTrabajo, 'trabajoLaboratorioAntecedenteHistoricoDetalle') !== false
    && strpos($bloqueInicioTrabajo, "'antecedente_historico_existente'") !== false,
	'El contexto exige lectura clinica, habilita unidades enteras y guia solo cantidades incompatibles a Administracion.'
);
pruebaLabAfirmar(
    strpos($bloqueContextoDetalle, "'requiere_regularizacion_unidades'") !== false
    && strpos($bloqueContextoDetalle, "'regularizacion_unidades'") !== false
    && strpos($bloqueContextoDetalle, "'puede_regularizar_unidades'") !== false
    && strpos($bloqueContextoDetalle, "'puede_iniciar_trabajos_agrupados'") !== false
    && strpos($bloqueContextoDetalle, "'trabajos_activos'") !== false,
    'El contexto diferencia el lote pendiente, el lote designado y los varios trabajos ya activos.'
);
pruebaLabAfirmar(
    strpos($bloqueContextoDetalle, 'tecnicos_formales_no_disponibles') === false
    && strpos($bloqueContextoDetalle, "'tecnico_puede_quedar_pendiente' => true") !== false
    && strpos($bloqueContextoDetalle, "'tecnico_pendiente_disponible'") !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'requiere_mecanico: false') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'Asignar más adelante') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'asignarTecnico: {') !== false,
    'La falta de tecnicos deja de bloquear y la interfaz ofrece continuar o asignar mas adelante.'
);
$bloqueAccionNatural = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioAccionNaturalContexto');
$bloqueExigirMotivoAuditor = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioExigirMotivoExcepcionAuditor'
);
pruebaLabAfirmar(
    strpos($bloqueAccionNatural, "'iniciarTrabajosAgrupados'") !== false
    && strpos($bloqueAccionNatural, "'asignarTecnico'") !== false
    && strpos($bloqueAccionNatural, 'return true;') !== false
    && strpos($bloqueAccionNatural, "if (".'$accion'." === 'guardarRegularizacionUnidades')") !== false
    && strpos($bloqueAccionNatural, 'return $localPropio;') !== false
    && strpos($bloqueExigirMotivoAuditor, 'trabajoLaboratorioAccionRequiereMotivoExcepcionAuditor') !== false
    && strpos($bloqueExigirMotivoAuditor, "if (".'$motivo'." === '')") !== false
    && strpos($bloqueExigirMotivoAuditor, 'al menos cinco caracteres') === false
    && preg_match('/use\s*\([^)]*\$accionComando\s*\)/s', $bloqueInicioTrabajo) === 1,
    'El servidor libera la preparacion inicial entre locales y exige una observacion sin minimo para otras excepciones.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioJs, 'if (boolValue(config.requiere_motivo_excepcion))') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'Observación administrativa') !== false
    && strpos($fuenteTrabajoLaboratorioJs, '!toStringSafe(values.motivo_excepcion).trim()') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'motivo_excepcion).trim().length < 5') === false
    && strpos($fuenteTrabajoLaboratorioJs, 'action.code === "rectificarCustodia" && toStringSafe(values.justificacion).trim().length < 5') === false
    && strpos($fuenteInicioHtml, 'trabajo-laboratorio-20260725-03') !== false,
    'La interfaz ofrece una observacion inicial opcional y solo exige contenido, sin minimo, en otras excepciones.'
);
$bloqueGuardarRegularizacion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioGuardarRegularizacionUnidades'
);
pruebaLabAfirmar(
    strpos($bloqueGuardarRegularizacion, 'begin_transaction') !== false
    && strpos($bloqueGuardarRegularizacion, 'FOR UPDATE') !== false
    && strpos($bloqueGuardarRegularizacion, 'trabajoLaboratorioNormalizarUnidadesRegularizacion') !== false
    && strpos($bloqueGuardarRegularizacion, 'trabajo_laboratorio_regularizacion_unidad') !== false
    && strpos($bloqueGuardarRegularizacion, '$mysqli->commit()') !== false
    && substr_count($bloqueGuardarRegularizacion, '$mysqli->rollback()') >= 2,
    'La designacion agrupada valida todas las unidades y las persiste dentro de una unica transaccion.'
);
pruebaLabAfirmar(
    strpos($bloqueInicioTrabajo, '$cantidadUnidadesOrigen = $cantidadAgrupada > 1 ? $cantidadAgrupada : 1') !== false
    && strpos($bloqueInicioTrabajo, 'for ($indiceUnidad = 1; $indiceUnidad < count($unidadesRegularizadas); $indiceUnidad++)') !== false
    && strpos($bloqueInicioTrabajo, '$estadoConsumido = \'consumida\'') !== false
    && strpos($bloqueInicioTrabajo, '$respuesta[\'datos\'][\'trabajos\'] = $trabajosCreados') !== false
    && strpos($bloqueInicioTrabajo, '$respuesta[\'datos\'][\'cantidad_trabajos\'] = count($trabajosCreados)') !== false,
    'La preparacion crea todas las unidades dentro del comando atomico y consume una sola vez la regularizacion.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioPhp, "case 'guardarRegularizacionUnidades':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, 'trabajoLaboratorioGuardarRegularizacionUnidades') !== false
    && strpos($fuenteTrabajoLaboratorioPhp, "case 'iniciarTrabajosAgrupados':") !== false
    && substr_count($fuenteTrabajoLaboratorioPhp, 'trabajoLaboratorioIniciar($mysqli, $codUsuario, $entrada)') >= 2,
    'El endpoint publica por separado la designacion y el inicio agrupado, reutilizando el inicio protegido.'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioPhp, "case 'asignarTecnico':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, 'trabajoLaboratorioAsignarTecnico') !== false,
    'El endpoint publica la asignacion posterior con el mismo resguardo transaccional.'
);
$bloqueListarTrabajos = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioListar');
pruebaLabAfirmar(
    strpos($bloqueListarTrabajos, 'isset($entrada[\'cod_detalle_venta\'])') !== false
    && strpos($bloqueListarTrabajos, "'tl.cod_detalle_ventaFK=?'") !== false,
    'La vista operativa puede mostrar exactamente todos los trabajos del detalle agrupado.'
);
pruebaLabAfirmar(
    strpos($bloqueListarTrabajos, '$incluyeDestinoMecanico') !== false
    && strpos($bloqueListarTrabajos, '$destinoMecanicoSql') !== false
    && strpos($bloqueListarTrabajos, 'cod_local_destinoFK=?') !== false
    && strpos($bloqueListarTrabajos, '$bandeja === \'por_recibir\'') !== false,
    'Mi bandeja incluye los traslados por recibir destinados al local del usuario habilitado.'
);
$bloqueImpresionTecnica = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioListarImpresionTecnica'
);
pruebaLabAfirmar(
    $bloqueImpresionTecnica !== ''
    && strpos($bloqueImpresionTecnica, 'VERTRABAJOSLABORATORIO') !== false
    && strpos($bloqueImpresionTecnica, "'por_pagina'] = 100") !== false
    && strpos($bloqueImpresionTecnica, 'trabajoLaboratorioListar(') !== false
    && strpos($bloqueImpresionTecnica, 'trabajoLaboratorioDatosTecnicosImpresion') !== false
    && strpos($bloqueImpresionTecnica, 'do {') !== false
    && strpos($bloqueImpresionTecnica, 'while ($pagina <= $totalPaginas)') !== false
    && strpos($bloqueImpresionTecnica, 'INSERT INTO') === false
    && strpos($bloqueImpresionTecnica, 'UPDATE ') === false
    && strpos($bloqueImpresionTecnica, 'DELETE FROM') === false
    && strpos($bloqueImpresionTecnica, 'begin_transaction') === false,
    'El listado tecnico recorre todas las paginas y reutiliza filtros y autorizacion del listado operativo.'
);
$bloqueItemImpresionTecnica = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioItemImpresionTecnica'
);
pruebaLabAfirmar(
    strpos($fuenteTrabajoLaboratorioPhp, "case 'listarImpresionTecnica':") !== false
    && strpos($fuenteTrabajoLaboratorioPhp, 'trabajoLaboratorioListarImpresionTecnica') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'data-tlab-command="print-technical"') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'request("listarImpresionTecnica", payload)') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'technicalPrintFilterLabels') !== false
    && strpos($fuenteTrabajoLaboratorioCss, 'size: A4 landscape') !== false
    && strpos($fuenteTrabajoLaboratorioCss, '.tlab-print-signature') !== false
    && strpos($bloqueItemImpresionTecnica, 'paciente_abreviado') !== false
    && strpos($bloqueItemImpresionTecnica, 'pieza_dental') !== false
    && strpos($bloqueItemImpresionTecnica, 'colorimetro') !== false
    && strpos($bloqueItemImpresionTecnica, 'instrucciones') !== false
    && strpos($bloqueItemImpresionTecnica, 'costo') === false,
    'La interfaz prepara un A4 horizontal con filtros activos, datos tecnicos y espacio de firma.'
);
$bloqueObjetivoGrupo = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioObjetivoAvanceClinicoGrupo');
$bloqueSincronizarAvance = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioSincronizarAvanceClinico');
pruebaLabAfirmar(
    strpos($bloqueObjetivoGrupo, "'instalado' => 100") !== false
    && strpos($bloqueObjetivoGrupo, 'if (count($pisos) < $cantidad)') !== false
    && strpos($bloqueObjetivoGrupo, 'min($pisos)') !== false
    && strpos($bloqueSincronizarAvance, 'trabajoLaboratorioObjetivoAvanceClinicoGrupo') !== false,
    'El avance clinico agrupado sigue el menor hito del lote y solo llega a 100 cuando todas las unidades se instalaron.'
);
$bloqueCompletar = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioCompletarIdempotencia');
pruebaLabAfirmar(
    strpos($bloqueCompletar, "estado=\\'pendiente\\'") !== false
    && strpos($bloqueCompletar, '$stmt->affected_rows === 1') !== false,
    'La respuesta idempotente solo puede completarse una vez desde el estado pendiente.'
);
$bloqueAsegurarHilo = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioAsegurarHiloDetalle');
pruebaLabAfirmar(
    strpos($bloqueAsegurarHilo, 'if (!$mysqli->begin_transaction())') !== false
    && strpos($bloqueAsegurarHilo, "'transaccion_no_iniciada'") !== false
    && strpos($bloqueAsegurarHilo, '$mysqli->commit()') !== false
    && substr_count($bloqueAsegurarHilo, '$mysqli->rollback()') >= 2,
    'La vinculacion del Hilo maestro tambien protege begin, commit y rollback.'
);
$bloqueResolverHilo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioObtenerHiloUnicoVenta'
);
$posBloqueoHilo = strpos(
    $bloqueResolverHilo,
    'WHERE cod_interConsulta=? LIMIT 1 FOR UPDATE'
);
$posBloqueoVinculo = strpos(
    $bloqueResolverHilo,
    "WHERE cod_ventaFK=? AND estado='activo' LIMIT 1 FOR UPDATE"
);
pruebaLabAfirmar(
    $posBloqueoHilo !== false
    && $posBloqueoVinculo !== false
    && $posBloqueoHilo < $posBloqueoVinculo
    && strpos($bloqueResolverHilo, 'interconsulta_fusion') !== false
    && strpos($bloqueResolverHilo, '$visitados') !== false,
    'El Hilo se bloquea antes que el vinculo y una fusion ya aplicada se sigue hasta su maestro vigente.'
);
$bloqueRegistrarEvento = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioRegistrarEvento'
);
pruebaLabAfirmar(
    preg_match(
        "/trabajoLaboratorioObtenerHiloUnicoVenta\\s*\\(\\s*\\\$mysqli\\s*,\\s*intval\\(\\\$trabajo\\['cod_ventaFK'\\]\\)\\s*,\\s*true\\s*\\)/s",
        $bloqueRegistrarEvento
    ) === 1,
    'Cada evento bloquea el Hilo vigente antes de publicar su mensaje de trazabilidad.'
);
$posInsertarEvento = strpos($bloqueRegistrarEvento, 'INSERT INTO trabajo_laboratorio_evento');
$posSincronizarAvance = strpos($bloqueRegistrarEvento, 'trabajoLaboratorioSincronizarAvanceClinico');
pruebaLabAfirmar(
    $posInsertarEvento !== false && $posSincronizarAvance !== false
    && $posInsertarEvento < $posSincronizarAvance
    && strpos($fuenteHelper, 'if ($anterior >= $objetivo)') !== false
    && strpos($fuenteHelper, 'INSERT INTO evoluciontratamiento') !== false
    && strpos($fuenteTrabajoLaboratorioJs, 'tratamientoLaboratorioClinicoAplicarRespuestaOperacion(response)') !== false,
    'Cada hito sincroniza el piso de avance despues del evento, conserva no regresion y actualiza la ficha.'
);

$bloqueInstalacion = $bloquesTransicion['trabajoLaboratorioRegistrarInstalacion'];
$bloqueValidacionInstalacion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioValidarEvolucionInstalacion'
);
pruebaLabAfirmar(
    strpos($bloqueInstalacion, "'evolucion_origen_requerida'") === false
    && strpos($bloqueInstalacion, 'if ($codEvolucionOrigen > 0)') !== false
    && strpos($bloqueInstalacion, 'trabajoLaboratorioValidarEvolucionInstalacion') !== false
    && strpos($bloqueInstalacion, 'trabajoLaboratorioEvidenciasFinales') !== false
    && strpos($bloqueInstalacion, "'evidencia_instalacion_requerida'") !== false
    && strpos($bloqueInstalacion, "'motivo_sin_foto_requerido'") !== false
    && strpos($bloqueInstalacion, "'detalle_sin_foto_requerido'") !== false
    && strpos($bloqueInstalacion, "'condicion_entrega_requerida'") !== false
    && strpos($bloqueInstalacion, "'observacion_entrega_requerida'") !== false
    && strpos($bloqueInstalacion, 'trabajoLaboratorioGuardarMediaProtegida') !== false
    && strpos($bloqueInstalacion, "'instalacion_final'") !== false
    && strpos($bloqueInstalacion, 'cod_detalle_activo_unico=NULL') === false
    && strpos($bloqueInstalacion, 'fecha_instalado=NOW()') !== false
    && strpos($bloqueInstalacion, "'resolucion_operativa' => 1") !== false
    && strpos($bloqueInstalacion, "'modo_resolucion' => 'instalado_entregado'") !== false
    && strpos($bloqueInstalacion, "'sin_foto' => \$sinFoto ? 1 : 0") !== false
    && strpos($bloqueInstalacion, "'motivo_sin_foto'") !== false
    && strpos($bloqueInstalacion, "'detalle_sin_foto'") !== false
    && strpos($bloqueInstalacion, "\$origen['cod_consulta_origen']") !== false
    && strpos($bloqueInstalacion, "\$origen['cod_evolucion_origen']") !== false,
    'El cierre operativo exige condicion y una foto o excepcion justificada, admite una evolucion opcional y conserva la reserva unica.'
);
pruebaLabAfirmar(
    strpos($bloqueInstalacion, 'AND cod_custodio_actualFK=?') !== false
    && strpos($bloqueInstalacion, "'cierra_custodia' => 1") !== false
    && strpos($bloqueInstalacion, "'id_evento_custodia'") !== false
    && strpos($bloqueInstalacion, 'trabajoLaboratorioObtenerTransferenciaPendiente') !== false
    && strpos($bloqueInstalacion, 'id_transferencia_pendienteFK=NULL') !== false
    && strpos($bloqueInstalacion, "'transferencia_pendiente_cerrada'") !== false
    && strpos($bloqueInstalacion, '$idTransferencia,') !== false
    && strpos($bloqueInstalacion, 'SET cod_custodio_actualFK=') === false,
    'El custodio vigente cierra el hilo y cualquier transferencia pendiente queda vinculada al evento sin reasignar el trabajo.'
);
$bloquePromocionHistoricaUnica = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoPromoverHistorico'
);
$bloquePreparacionHistoricaUnica = pruebaLabBloqueFuncion(
    $fuenteHistoricoHelper,
    'trabajoLaboratorioHistoricoPrepararResolucion'
);
$bloqueListadoUnico = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioListar');
$bloqueResumenUnico = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioResumen');
pruebaLabAfirmar(
    strpos(
        $bloquePromocionHistoricaUnica,
        "\$codDetalleActivo = \$estado === 'cancelado' ? null : \$codDetalle"
    ) !== false
    && strpos($bloquePreparacionHistoricaUnica, 'cod_detalle_activo_unico') !== false
    && strpos($bloquePreparacionHistoricaUnica, "'tratamiento_ocupado'") !== false
    && strpos($bloqueListadoUnico, 'trabajo_laboratorio_consolidacion') !== false
    && strpos($bloqueResumenUnico, 'trabajo_laboratorio_consolidacion') !== false,
    'El cierre historico reserva el tratamiento y los listados omiten antecedentes consolidados sin eliminarlos.'
);
pruebaLabAfirmar(
    $bloqueValidacionInstalacion !== ''
    && strpos($bloqueValidacionInstalacion, 'FOR UPDATE') !== false
    && strpos($bloqueValidacionInstalacion, "'evolucion_instalacion_anterior_aprobacion'") !== false
    && strpos($bloqueValidacionInstalacion, "'evolucion_instalacion_otro_profesional'") !== false
    && strpos($bloqueValidacionInstalacion, 'cod_venta_consulta') !== false
    && strpos($bloqueValidacionInstalacion, 'cod_detalle_consulta') !== false
    && strpos($bloqueValidacionInstalacion, "'evolucion_instalacion_no_reciente'") === false
    && strpos($bloqueValidacionInstalacion, "'evolucion_instalacion_ya_utilizada'") !== false
    && strpos($bloqueValidacionInstalacion, "'instalacion_registrada'") !== false,
    'La evolucion de instalacion se bloquea, revalida consulta, debe ser posterior, propia y de uso unico.'
);

$bloqueInicio = $bloquesTransicion['trabajoLaboratorioIniciar'];
$bloqueAjuste = $bloquesTransicion['trabajoLaboratorioSolicitarAjuste'];
$bloqueRecepcion = $bloquesTransicion['trabajoLaboratorioTomarHilo'];
$bloqueDevolucion = $bloquesTransicion['trabajoLaboratorioIniciarDevolucion'];
$bloqueAprobacion = $bloquesTransicion['trabajoLaboratorioAprobar'];
$bloqueCancelacion = $bloquesTransicion['trabajoLaboratorioCancelar'];
pruebaLabAfirmar(
    strpos($bloqueAjuste, "if (".'$justificacion'." === '')") !== false
    && strpos($bloqueAjuste, 'strlen($justificacion) < 5') === false
    && strpos($bloqueCancelacion, "if (".'$motivo'." === '')") !== false
    && strpos($bloqueCancelacion, 'strlen($motivo) < 5') === false,
    'Ajustes y cancelaciones conservan texto obligatorio sin imponer una cantidad minima.'
);
pruebaLabAfirmar(
    substr_count($bloqueInicio, 'DATE_ADD(NOW(),INTERVAL ? DAY)') >= 2
    && strpos($bloqueInicio, '$dias = 30;') !== false
    && strpos($bloqueAjuste, 'DATE_ADD(NOW(),INTERVAL ? DAY)') !== false
    && strpos($bloqueAjuste, '$dias = 30;') !== false
    && strpos($bloqueRecepcion, 'fecha_retiro=COALESCE(fecha_retiro,NOW())') !== false
    && strpos($bloqueDevolucion, 'fecha_entrega=COALESCE(fecha_entrega,NOW())') !== false
    && strpos($bloqueAprobacion, 'fecha_completado=COALESCE(fecha_completado,NOW())') !== false
    && strpos($bloqueInstalacion, 'fecha_instalado=NOW()') !== false
    && strpos($bloqueCancelacion, 'fecha_cancelado=NOW()') !== false,
    'Los hitos y objetivos usan fechas del servidor y preservan la primera recepcion o entrega.'
);
$bloqueFormato = pruebaLabBloqueFuncion($fuenteHelper, 'trabajoLaboratorioFormatearTrabajo');
pruebaLabAfirmar(
    strpos($bloqueFormato, 'trabajoLaboratorioCalcularIndicadoresPlazo') !== false,
    'La salida visible reutiliza el calculo puro de SLA.'
);
$bloqueDetalleTrabajo = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioObtenerDetalleTrabajo'
);
pruebaLabAfirmar(
    strpos($bloqueDetalleTrabajo, 'trabajoLaboratorioDecodificarJson') !== false
    && strpos(
        $bloqueDetalleTrabajo,
        "json_decode(trabajoLaboratorioTextoUtf8(\$fila['metadata_json'])"
    ) === false
    && strpos($fuenteTrabajoLaboratorioPhp, "ini_set('display_errors', '0')") !== false,
    'El detalle tolera eventos antiguos sin metadata y preserva una respuesta JSON interpretable.'
);

if ($soloPuras) {
    pruebaLabOk('Verificacion pura y estructural finalizada sin acceder a registros clinicos.');
    exit(0);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    pruebaLabFallar('No se pudo conectar con la base local.');
}

$conteosAntes = pruebaLabConteosProtegidos($mysqli);
if ($aplicar) {
    $rutaMigracion = dirname(__DIR__).'/actualizacion_21072026_trabajos_laboratorio_telar.sql';
    pruebaLabEjecutarMigracion($mysqli, $rutaMigracion);
    $rutaRegularizacion = dirname(__DIR__).'/actualizacion_22072026_regularizacion_unidades_laboratorio.sql';
    pruebaLabEjecutarMigracion($mysqli, $rutaRegularizacion);
    $rutaTecnicoPendiente = dirname(__DIR__).'/actualizacion_22072026_tecnico_pendiente_laboratorio.sql';
    pruebaLabEjecutarMigracion($mysqli, $rutaTecnicoPendiente);
    $conteosHiloAntes = array(
        'trabajos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio'),
        'eventos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_evento'),
        'medios' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_media')
    );
    $rutaHiloCustodia = dirname(__DIR__).'/actualizacion_22072026_hilo_custodia_laboratorio.sql';
    pruebaLabEjecutarMigracion($mysqli, $rutaHiloCustodia);
    $conteosHiloDespues = array(
        'trabajos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio'),
        'eventos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_evento'),
        'medios' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_media')
    );
    pruebaLabAfirmar(
        $conteosHiloAntes === $conteosHiloDespues,
        'La migracion del hilo no crea ni elimina trabajos, eventos o evidencias existentes.'
    );
    $conteosUnicidadAntes = array(
        'trabajos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio'),
        'eventos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_evento'),
        'medios' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_media')
    );
    $rutaUnicidadHilo = dirname(__DIR__).'/actualizacion_23072026_unicidad_hilo_cerrado_laboratorio.sql';
    pruebaLabEjecutarMigracion($mysqli, $rutaUnicidadHilo);
    $conteosUnicidadDespues = array(
        'trabajos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio'),
        'eventos' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_evento'),
        'medios' => pruebaLabEscalar($mysqli, 'SELECT COUNT(*) FROM trabajo_laboratorio_media')
    );
    pruebaLabAfirmar(
        $conteosUnicidadAntes === $conteosUnicidadDespues,
        'La consolidacion no elimina trabajos, eventos ni evidencias existentes.'
    );
}

$tablasEsperadas = array(
    'trabajo_laboratorio', 'trabajo_laboratorio_ciclo', 'trabajo_laboratorio_transferencia',
    'trabajo_laboratorio_idempotencia', 'trabajo_laboratorio_evento',
    'trabajo_laboratorio_ubicacion', 'trabajo_laboratorio_media',
    'trabajo_laboratorio_regularizacion', 'trabajo_laboratorio_regularizacion_unidad',
    'trabajo_laboratorio_consolidacion'
);
$estructuraPresente = true;
foreach ($tablasEsperadas as $tabla) {
    if (pruebaLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$mysqli->real_escape_string($tabla)."'"
    ) !== 1) {
        $estructuraPresente = false;
    }
}

if ($aplicar || $estructuraPresente) {
    pruebaLabAfirmar($estructuraPresente, 'Existen las tablas operativas, historicas y de regularizacion por unidades.');
    $resultadoDoctorMultisucursal = $mysqli->query(
        "SELECT u.cod_usuario,u.cod_localFK,l.cod_local AS cod_local_destino
         FROM usuario u
         INNER JOIN local l
           ON l.cod_local<>u.cod_localFK
          AND UPPER(TRIM(l.estado))='ACTIVO'
         WHERE UPPER(TRIM(u.estado))='ACTIVO'
           AND UPPER(TRIM(u.tipo))='DOCTOR'
         ORDER BY u.cod_usuario,l.cod_local
         LIMIT 1"
    );
    if ($resultadoDoctorMultisucursal
        && ($filaDoctorMultisucursal = $resultadoDoctorMultisucursal->fetch_assoc())) {
        $autorizacionReal = trabajoLaboratorioAutorizacionPreparacionLocal(
            $mysqli,
            intval($filaDoctorMultisucursal['cod_usuario']),
            intval($filaDoctorMultisucursal['cod_local_destino'])
        );
        pruebaLabAfirmar(
            is_array($autorizacionReal)
            && array_key_exists('autorizado', $autorizacionReal)
            && array_key_exists('origen', $autorizacionReal),
            'La autorizacion multisucursal consulta el horario y el vinculo real sin exponer identidades.'
        );
    }
    if ($resultadoDoctorMultisucursal) {
        $resultadoDoctorMultisucursal->free();
    }
    $resultadoMecanicoCosto = $mysqli->query(
        "SELECT md.cod_usuarioFK FROM mecanico_dental md "
        ."INNER JOIN usuario u ON u.cod_usuario=md.cod_usuarioFK AND u.estado='Activo' "
        ."WHERE md.cod_usuarioFK IS NOT NULL AND md.estado='activo' LIMIT 1"
    );
    if ($resultadoMecanicoCosto && ($filaMecanicoCosto = $resultadoMecanicoCosto->fetch_assoc())) {
        $codMecanicoCosto = intval($filaMecanicoCosto['cod_usuarioFK']);
        pruebaLabAfirmar(
            trabajoLaboratorioUsuarioPuedeGestionarCosto($mysqli, $codMecanicoCosto)
                === trabajoLaboratorioUsuarioEsAuditor($mysqli, $codMecanicoCosto),
            'Una cuenta real vinculada como mecanico solo recibe costos si posee permiso auditor.'
        );
    }
    if ($resultadoMecanicoCosto) {
        $resultadoMecanicoCosto->free();
    }
    $resultadoAdministrativoCosto = $mysqli->query(
        "SELECT u.cod_usuario FROM usuario u "
        ."LEFT JOIN mecanico_dental md ON md.cod_usuarioFK=u.cod_usuario AND md.estado='activo' "
        ."WHERE u.estado='Activo' AND UPPER(TRIM(u.tipo)) IN ('ADMINISTRATIVO','ADMINISTRADOR') "
        ."AND md.cod_mecanico_dental IS NULL LIMIT 1"
    );
    if ($resultadoAdministrativoCosto
        && ($filaAdministrativoCosto = $resultadoAdministrativoCosto->fetch_assoc())) {
        pruebaLabAfirmar(
            trabajoLaboratorioUsuarioPuedeGestionarCosto(
                $mysqli,
                intval($filaAdministrativoCosto['cod_usuario'])
            ),
            'Una cuenta administrativa real no vinculada como mecanico conserva acceso al costo.'
        );
    }
    if ($resultadoAdministrativoCosto) {
        $resultadoAdministrativoCosto->free();
    }
    $resultadoTrabajoOrigen = $mysqli->query(
        'SELECT id FROM trabajo_laboratorio ORDER BY id DESC LIMIT 1'
    );
    if ($resultadoTrabajoOrigen && ($filaTrabajoOrigen = $resultadoTrabajoOrigen->fetch_assoc())) {
        $trabajoOrigen = trabajoLaboratorioObtenerTrabajo(
            $mysqli,
            intval($filaTrabajoOrigen['id']),
            false
        );
        pruebaLabAfirmar(
            $trabajoOrigen
            && array_key_exists('nombre_producto', $trabajoOrigen)
            && array_key_exists('nombre_iniciador', $trabajoOrigen)
            && array_key_exists('iniciador_rol', $trabajoOrigen),
            'La consulta real recupera el producto vendido y la identidad del iniciador.'
        );
    }
    if ($resultadoTrabajoOrigen) {
        $resultadoTrabajoOrigen->free();
    }
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND TABLE_NAME='trabajo_laboratorio' AND COLUMN_NAME IN "
            ."('codigo_origen','unidad_origen','cantidad_unidades_origen','id_regularizacion_unidadFK')"
        ) === 4,
        'Cada trabajo conserva codigo de origen, numero de unidad, total y vinculo a su designacion.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND TABLE_NAME='trabajo_laboratorio' "
            ."AND COLUMN_NAME IN ('cod_mecanico_dentalFK','cod_tecnico_usuarioFK') "
            ."AND IS_NULLABLE='YES'"
        ) === 2,
        'Mecanico y usuario tecnico admiten NULL para representar Tecnico pendiente sin cuentas ficticias.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM (SELECT INDEX_NAME,MIN(NON_UNIQUE) AS no_unico,"
            ."GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') AS columnas "
            ."FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND TABLE_NAME='trabajo_laboratorio' GROUP BY INDEX_NAME) indices "
            ."WHERE no_unico=0 AND columnas='cod_detalle_activo_unico,unidad_origen'"
        ) === 1,
        'La unicidad activa separa las unidades del mismo detalle sin permitir duplicados.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND TABLE_NAME='trabajo_laboratorio_consolidacion' AND COLUMN_NAME IN "
            ."('id_trabajo_canonicoFK','id_trabajo_consolidadoFK','motivo','detalle','origen','fecha_creacion')"
        ) === 6,
        'La consolidacion conserva el hilo canonico, el antecedente retirado y el motivo sin borrar datos.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            'SELECT COUNT(*) FROM trabajo_laboratorio_consolidacion c '
            .'INNER JOIN trabajo_laboratorio canonico ON canonico.id=c.id_trabajo_canonicoFK '
            .'INNER JOIN trabajo_laboratorio consolidado ON consolidado.id=c.id_trabajo_consolidadoFK '
            .'WHERE canonico.id=consolidado.id '
            .'OR canonico.cod_detalle_ventaFK<>consolidado.cod_detalle_ventaFK '
            .'OR canonico.unidad_origen<>consolidado.unidad_origen'
        ) === 0,
        'Cada antecedente consolidado pertenece al mismo tratamiento y unidad que su hilo canonico.'
    );
    $parDuplicadoConfirmado = pruebaLabEscalar(
        $mysqli,
        "SELECT COUNT(*) FROM trabajo_laboratorio "
        ."WHERE codigo_visible IN ('6413-CC-LAB-4','6413-CC-LAB-5')"
    );
    if ($parDuplicadoConfirmado === 2) {
        pruebaLabAfirmar(
            pruebaLabEscalar(
                $mysqli,
                'SELECT COUNT(*) FROM trabajo_laboratorio_consolidacion c '
                .'INNER JOIN trabajo_laboratorio canonico ON canonico.id=c.id_trabajo_canonicoFK '
                .'INNER JOIN trabajo_laboratorio consolidado ON consolidado.id=c.id_trabajo_consolidadoFK '
                ."WHERE canonico.codigo_visible='6413-CC-LAB-4' "
                ."AND consolidado.codigo_visible='6413-CC-LAB-5' "
                .'AND canonico.cod_detalle_activo_unico=canonico.cod_detalle_ventaFK'
            ) === 1,
            'LAB-4 queda como unico hilo visible y LAB-5 como antecedente consolidado.'
        );
    }
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND TABLE_NAME='trabajo_laboratorio_regularizacion' AND COLUMN_NAME IN "
            ."('codigo_origen','cod_detalle_ventaFK','cantidad_unidades','estado','clave_idempotencia',"
            ."'payload_hash','cod_usuarioFK_create','fecha_consumo','cod_usuarioFK_consumo','version')"
        ) === 10,
        'La cabecera de regularizacion conserva origen, idempotencia, estado y responsables.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND TABLE_NAME='trabajo_laboratorio_regularizacion_unidad' AND COLUMN_NAME IN "
            ."('id_regularizacionFK','numero_unidad','pieza','piezas_json','denticion',"
            ."'alcance_odontologico','fecha_creacion','cod_usuarioFK_create')"
        ) === 8,
        'Cada unidad conserva su seleccion dental independiente y el usuario que la asigno.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar($mysqli, "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mecanico_dental' AND COLUMN_NAME='cod_usuarioFK'") === 1,
        'El mecanico puede vincularse a una cuenta formal de Telar.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND ((TABLE_NAME='trabajo_laboratorio' AND COLUMN_NAME='id_evento_custodia_actualFK') "
            ."OR (TABLE_NAME='trabajo_laboratorio_evento' AND COLUMN_NAME IN "
            ."('id_evento_custodiaFK','actor_nombre_snapshot','actor_rol_snapshot','local_nombre_snapshot')))"
        ) === 5,
        'El hilo conserva puntero, vinculo de novedades y snapshots historicos en cinco columnas aditivas.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS "
            ."WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME IN "
            ."('fk_trabajo_laboratorio_evento_custodia_actual','fk_trabajo_laboratorio_evento_custodia')"
        ) === 2,
        'Los dos vinculos de custodia poseen claves foraneas instaladas.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() "
            ."AND INDEX_NAME IN ('idx_trabajo_laboratorio_evento_custodia_actual',"
            ."'idx_trabajo_laboratorio_evento_custodia')"
        ) === 4,
        'Los indices del puntero y de la cadena de eventos estan instalados con sus columnas ordenadas.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            'SELECT COUNT(*) FROM trabajo_laboratorio tl '
            .'LEFT JOIN trabajo_laboratorio_evento ev ON ev.id=tl.id_evento_custodia_actualFK '
            .'WHERE tl.id_evento_custodia_actualFK IS NULL OR ev.id IS NULL '
            .'OR ev.id_trabajoFK<>tl.id OR ev.cod_custodio_nuevoFK<>tl.cod_custodio_actualFK'
        ) === 0,
        'Cada trabajo apunta a un nodo propio cuyo responsable coincide con la custodia actual.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar(
            $mysqli,
            'SELECT COUNT(*) FROM trabajo_laboratorio_evento hijo '
            .'JOIN trabajo_laboratorio_evento padre ON padre.id=hijo.id_evento_custodiaFK '
            .'WHERE hijo.id_trabajoFK<>padre.id_trabajoFK'
        ) === 0,
        'Ninguna novedad puede enlazarse al periodo de custodia de otro trabajo.'
    );
    $trabajosCadena = array();
    $resultadoCadena = $mysqli->query(
        'SELECT id,estado_derivado,fecha_instalado,fecha_cancelado,id_evento_custodia_actualFK '
        .'FROM trabajo_laboratorio ORDER BY id ASC'
    );
    if (!$resultadoCadena) {
        pruebaLabFallar('No se pudo consultar la proyeccion agregada del hilo de custodia.');
    }
    while ($trabajoCadena = $resultadoCadena->fetch_assoc()) {
        $trabajosCadena[] = $trabajoCadena;
    }
    $resultadoCadena->free();
    $usuarioDetalle = pruebaLabEscalar(
        $mysqli,
        "SELECT MIN(u.cod_usuario) FROM usuario u "
        ."INNER JOIN accesosuser au ON au.usuarios_idusario=u.cod_usuario "
        ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
        ."WHERE u.estado='Activo' AND au.accion='SI' "
        ."AND la.codigo='VERTRABAJOSLABORATORIO'"
    );
    if (count($trabajosCadena) > 0 && $usuarioDetalle > 0) {
        $detalleTrabajo = trabajoLaboratorioObtenerDetalleTrabajo(
            $mysqli,
            $usuarioDetalle,
            intval($trabajosCadena[0]['id'])
        );
        pruebaLabAfirmar(
            is_array($detalleTrabajo)
            && isset($detalleTrabajo['trabajo'])
            && isset($detalleTrabajo['eventos'])
            && is_array($detalleTrabajo['eventos']),
            'La consulta real de un nodo devuelve un detalle estructurado aun con eventos anteriores.'
        );
    }
    $cadenasCustodia = trabajoLaboratorioCadenasCustodiaPorTrabajos($mysqli, $trabajosCadena);
    $cadenasValidas = count($cadenasCustodia) === count($trabajosCadena);
    foreach ($trabajosCadena as $trabajoCadena) {
        $idTrabajoCadena = intval($trabajoCadena['id']);
        $nodosCadena = isset($cadenasCustodia[$idTrabajoCadena])
            ? $cadenasCustodia[$idTrabajoCadena] : array();
        $terminalCadena = in_array(
            (string)$trabajoCadena['estado_derivado'],
            array('instalado', 'cancelado'),
            true
        );
        $actualesCadena = 0;
        foreach ($nodosCadena as $nodoCadena) {
            if (!empty($nodoCadena['actual'])) {
                $actualesCadena++;
            }
            if (empty($nodoCadena['responsable']['cod_usuario'])) {
                $cadenasValidas = false;
            }
        }
        if (count($nodosCadena) < 1 || (!$terminalCadena && $actualesCadena !== 1)
            || ($terminalCadena && $actualesCadena !== 0)) {
            $cadenasValidas = false;
        }
    }
    pruebaLabAfirmar(
        $cadenasValidas,
        'La proyeccion real devuelve una cadena por trabajo, responsable interno y un unico nodo actual no terminal.'
    );
    $microHilosReales = trabajoLaboratorioMicroHilosActivos($mysqli, $trabajosCadena, 4);
    $microHilosValidos = count($microHilosReales) === count($trabajosCadena);
    foreach ($microHilosReales as $microHiloReal) {
        $nodosMicro = isset($microHiloReal['nodos']) && is_array($microHiloReal['nodos'])
            ? $microHiloReal['nodos'] : array();
        if (count($nodosMicro) < 1 || count($nodosMicro) > 4
            || !isset($microHiloReal['total_nodos'])
            || !isset($microHiloReal['nodos_ocultos'])) {
            $microHilosValidos = false;
            break;
        }
        foreach ($nodosMicro as $nodoMicro) {
            if (empty($nodoMicro['actor']['nombre'])
                || array_key_exists('miniatura_url', $nodoMicro)) {
                $microHilosValidos = false;
                break 2;
            }
        }
    }
    pruebaLabAfirmar(
        $microHilosValidos,
        'La microcadena real conserva una fila por trabajo, avatar por nodo y omite imagenes hasta abrir el detalle.'
    );
    if (trabajoLaboratorioHistoricoEstructuraDisponible($mysqli)) {
        $origenesHistoricosReales = trabajoLaboratorioHistoricoOriginalesPorTrabajos(
            $mysqli,
            $trabajosCadena
        );
        $origenesHistoricosValidos = true;
        $cantidadOrigenesHistoricos = 0;
        $resultadoOrigenesHistoricos = $mysqli->query(
            'SELECT id,id_trabajo_laboratorioFK FROM trabajo_laboratorio_historico '
            .'WHERE id_trabajo_laboratorioFK IS NOT NULL ORDER BY id ASC'
        );
        if (!$resultadoOrigenesHistoricos) {
            pruebaLabFallar('No se pudo comprobar la proyeccion del registro historico original.');
        }
        while ($filaOrigenHistorico = $resultadoOrigenesHistoricos->fetch_assoc()) {
            $idTrabajoOrigen = intval($filaOrigenHistorico['id_trabajo_laboratorioFK']);
            $cantidadOrigenesHistoricos++;
            if (!isset($origenesHistoricosReales[$idTrabajoOrigen])
                || intval($origenesHistoricosReales[$idTrabajoOrigen]['id_historico'])
                    !== intval($filaOrigenHistorico['id'])
                || (string)$origenesHistoricosReales[$idTrabajoOrigen]['tipo_evento']
                    !== 'registro_original') {
                $origenesHistoricosValidos = false;
                break;
            }
        }
        $resultadoOrigenesHistoricos->free();
        pruebaLabAfirmar(
            $origenesHistoricosValidos
            && count($origenesHistoricosReales) === $cantidadOrigenesHistoricos,
            'Cada trabajo promovido recupera por lote su Registro original inmutable.'
        );
    }
    pruebaLabAfirmar(
        pruebaLabEscalar($mysqli, "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME LIKE 'trg_telar_lab_%'") === 11,
        'Los once resguardos de inmutabilidad historica estan instalados.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar($mysqli, "SELECT COUNT(*) FROM listadodeacceso WHERE codigo IN ('VERTRABAJOSLABORATORIO','CREARTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO','RECIBIRTRABAJOLABORATORIO','EVIDENCIATRABAJOLABORATORIO','APROBARTRABAJOLABORATORIO','AJUSTARTRABAJOLABORATORIO','INSTALARTRABAJOLABORATORIO','CANCELARTRABAJOLABORATORIO','AUDITARTRABAJOLABORATORIO','GESTIONARTECNICOSLABORATORIO') AND tipo='Administrativo'") === 11,
        'Los once permisos administrativos existen sin concesiones automaticas.'
    );
    pruebaLabAfirmar(trabajoLaboratorioEstructuraDisponible($mysqli), 'El helper reconoce la estructura completa del modulo.');
    pruebaLabAfirmar(trabajoLaboratorioHiloCustodiaDisponible($mysqli), 'El helper reconoce la estructura del hilo de custodia.');
    $referenciasMiniHilo = array();
    $limitesMiniHilo = array();
    $resultadoMiniHilo = $mysqli->query(
        'SELECT e.cod_mensaje_hiloFK,e.id_trabajoFK,e.id '
        .'FROM trabajo_laboratorio_evento e '
        .'WHERE e.cod_mensaje_hiloFK IS NOT NULL '
        .'ORDER BY e.cod_mensaje_hiloFK DESC LIMIT 40'
    );
    if ($resultadoMiniHilo) {
        while ($filaMiniHilo = $resultadoMiniHilo->fetch_assoc()) {
            $idMensajeMiniHilo = intval($filaMiniHilo['cod_mensaje_hiloFK']);
            $referenciasMiniHilo[] = array(
                'cod_mensaje' => $idMensajeMiniHilo,
                'id_trabajo' => intval($filaMiniHilo['id_trabajoFK'])
            );
            $limitesMiniHilo[$idMensajeMiniHilo] = intval($filaMiniHilo['id']);
        }
        $resultadoMiniHilo->free();
    }
    if (count($referenciasMiniHilo) > 0) {
        $proyeccionesMiniHilo = trabajoLaboratorioMiniHilosPorMensajes(
            $mysqli,
            $referenciasMiniHilo
        );
        $miniHilosValidos = count($proyeccionesMiniHilo) === count($referenciasMiniHilo);
        foreach ($proyeccionesMiniHilo as $idMensajeMiniHilo => $miniHilo) {
            if (!isset($limitesMiniHilo[$idMensajeMiniHilo])
                || intval($miniHilo['id_evento_limite'])
                    !== intval($limitesMiniHilo[$idMensajeMiniHilo])
                || empty($miniHilo['nodos'])) {
                $miniHilosValidos = false;
                break;
            }
            foreach ($miniHilo['nodos'] as $nodoMiniHilo) {
                if (!empty($nodoMiniHilo['id_evento'])
                    && intval($nodoMiniHilo['id_evento'])
                        > intval($limitesMiniHilo[$idMensajeMiniHilo])) {
                    $miniHilosValidos = false;
                    break 2;
                }
            }
            $ultimoNodoMiniHilo = end($miniHilo['nodos']);
            if (!empty($miniHilo['terminal'])
                && (!isset($ultimoNodoMiniHilo['tipo_evento'])
                    || $ultimoNodoMiniHilo['tipo_evento'] !== 'hilo_cerrado')) {
                $miniHilosValidos = false;
                break;
            }
        }
        pruebaLabAfirmar(
            $miniHilosValidos,
            'Los mensajes reales de laboratorio proyectan solo los nodos existentes al emitirse y conservan el cierre derivado.'
        );
    }
    $usuarioHistoricoNoAuditor = 0;
    $resultadoUsuariosHistoricos = $mysqli->query(
        "SELECT DISTINCT u.cod_usuario FROM usuario u "
        ."INNER JOIN accesosuser au ON au.usuarios_idusario=u.cod_usuario "
        ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
        ."WHERE u.estado='Activo' AND au.accion='SI' "
        ."AND la.codigo='VERTRABAJOSLABORATORIO' ORDER BY u.cod_usuario ASC"
    );
    if ($resultadoUsuariosHistoricos) {
        while ($filaUsuarioHistorico = $resultadoUsuariosHistoricos->fetch_assoc()) {
            $candidatoUsuarioHistorico = intval($filaUsuarioHistorico['cod_usuario']);
            if ($candidatoUsuarioHistorico > 0
                && !trabajoLaboratorioUsuarioEsAuditor($mysqli, $candidatoUsuarioHistorico)) {
                $usuarioHistoricoNoAuditor = $candidatoUsuarioHistorico;
                break;
            }
        }
        $resultadoUsuariosHistoricos->free();
    }
    if ($usuarioHistoricoNoAuditor > 0
        && trabajoLaboratorioHistoricoEstructuraDisponible($mysqli)) {
        $bandejaHistoricaReal = trabajoLaboratorioHistoricoListarHistoricos(
            $mysqli,
            $usuarioHistoricoNoAuditor,
            array('pagina' => 1, 'por_pagina' => 5)
        );
        pruebaLabAfirmar(
            is_array($bandejaHistoricaReal)
            && isset($bandejaHistoricaReal['historicos'])
            && isset($bandejaHistoricaReal['puede_resolver'])
            && $bandejaHistoricaReal['puede_resolver'] === true,
            'Una cuenta con permiso de laboratorio y sin auditoria puede consultar y resolver la bandeja historica.'
        );
        if (count($bandejaHistoricaReal['historicos']) > 0) {
            $detalleHistoricoReal = trabajoLaboratorioHistoricoObtenerHistorico(
                $mysqli,
                $usuarioHistoricoNoAuditor,
                intval($bandejaHistoricaReal['historicos'][0]['id_historico'])
            );
            pruebaLabAfirmar(
                isset($detalleHistoricoReal['historico']['acciones']['puede_resolver'])
                && $detalleHistoricoReal['historico']['acciones']['puede_resolver'] === true,
                'La consulta real del nodo historico habilita resolver sin exigir permiso auditor.'
            );
            $candidatosHistoricosCoherentes = true;
            foreach ($detalleHistoricoReal['candidatos_detalle'] as $candidatoHistoricoReal) {
                $activoHistoricoEsperado = trabajoLaboratorioDetalleClinicoActivo(array(
                    'estado_detalle' => $candidatoHistoricoReal['estado_detalle'],
                    'estado_tratamiento' => $candidatoHistoricoReal['estado_tratamiento'],
                    'progreso_porcentaje' => $candidatoHistoricoReal['progreso_porcentaje']
                ));
                if (!array_key_exists('finalizado', $candidatoHistoricoReal)
                    || !array_key_exists('puede_continuar', $candidatoHistoricoReal)
                    || boolval($candidatoHistoricoReal['finalizado']) === $activoHistoricoEsperado
                    || boolval($candidatoHistoricoReal['puede_continuar']) !== $activoHistoricoEsperado) {
                    $candidatosHistoricosCoherentes = false;
                    break;
                }
            }
            pruebaLabAfirmar(
                $candidatosHistoricosCoherentes,
                'Los candidatos reales informan de forma coherente si admiten continuar o deben cerrarse.'
            );
        }
        $resultadoHistoricoFinalizado = $mysqli->query(
            'SELECT h.id,dv.cod_detalle FROM trabajo_laboratorio_historico h '
            .'INNER JOIN detalle_venta dv ON dv.cod_ventaFK=h.cod_venta_snapshot '
            .'LEFT JOIN trabajo_laboratorio tl ON tl.cod_detalle_activo_unico=dv.cod_detalle '
            ."WHERE h.id_trabajo_laboratorioFK IS NULL "
            ."AND h.estado_convalidacion<>'integrado_operativo' "
            .'AND IFNULL(dv.progreso_porcentaje,0)>=100 AND tl.id IS NULL '
            .'ORDER BY h.id ASC,dv.cod_detalle ASC LIMIT 1'
        );
        if ($resultadoHistoricoFinalizado
            && ($filaHistoricoFinalizado = $resultadoHistoricoFinalizado->fetch_assoc())) {
            $historicoFinalizadoDecorado = trabajoLaboratorioHistoricoObtenerFilaDecorada(
                $mysqli,
                intval($filaHistoricoFinalizado['id'])
            );
            $errorModoFinalizado = pruebaLabEsperarExcepcion(
                'tratamiento_ya_finalizado',
                function () use (
                    $mysqli,
                    $usuarioHistoricoNoAuditor,
                    $historicoFinalizadoDecorado,
                    $filaHistoricoFinalizado
                ) {
                    trabajoLaboratorioHistoricoPrepararResolucion(
                        $mysqli,
                        $usuarioHistoricoNoAuditor,
                        $historicoFinalizadoDecorado,
                        array(
                            'modo_resolucion' => 'continuar',
                            'cod_detalle_venta' => intval($filaHistoricoFinalizado['cod_detalle']),
                            'estado_continuacion' => 'pendiente_revision'
                        )
                    );
                },
                'El servidor rechaza continuar un tratamiento real ya finalizado.'
            );
            pruebaLabAfirmar(
                isset($errorModoFinalizado->datosOperacion['modo_recomendado'])
                && $errorModoFinalizado->datosOperacion['modo_recomendado'] === 'instalado_entregado',
                'El rechazo real indica a la interfaz que debe seleccionar Instalado y entregado.'
            );
            $cierreHistoricoSinFoto = trabajoLaboratorioHistoricoPrepararResolucion(
                $mysqli,
                $usuarioHistoricoNoAuditor,
                $historicoFinalizadoDecorado,
                array(
                    'modo_resolucion' => 'instalado_entregado',
                    'cod_detalle_venta' => intval($filaHistoricoFinalizado['cod_detalle']),
                    'condicion_pre_entrega' => 'conforme',
                    'sin_foto_historica' => '1'
                )
            );
            pruebaLabAfirmar(
                isset($cierreHistoricoSinFoto['sin_foto_historica'])
                && $cierreHistoricoSinFoto['sin_foto_historica'] === true
                && isset($cierreHistoricoSinFoto['evidencias_resolucion'])
                && count($cierreHistoricoSinFoto['evidencias_resolucion']) === 0
                && $cierreHistoricoSinFoto['modo_resolucion'] === 'instalado_entregado',
                'El servidor admite cerrar un historico real sin foto cuando la ausencia se declara expresamente.'
            );
        }
        if ($resultadoHistoricoFinalizado) {
            $resultadoHistoricoFinalizado->free();
        }
    }
    $conteosDespues = pruebaLabConteosProtegidos($mysqli);
    pruebaLabAfirmar($conteosAntes === $conteosDespues, 'La migracion no altero registros existentes de ventas, productos ni trabajos anteriores.');
} else {
    fwrite(STDOUT, '[INFO] La estructura aun no esta aplicada; use --aplicar-migracion para instalarla.'.PHP_EOL);
}

$mysqli->close();
pruebaLabOk('Verificacion finalizada.');

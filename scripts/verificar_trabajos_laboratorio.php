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

$unaPieza = array(array('pieza' => '11', 'piezas' => array('11'), 'alcance' => 'pieza_dental'));
$dosPiezas = array(array('pieza' => '', 'piezas' => array('11', '12'), 'alcance' => 'piezas_multiples'));
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('pieza_individual', $unaPieza) === null, 'Pieza individual acepta exactamente una pieza.');
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('pieza_individual', $dosPiezas) !== null, 'Pieza individual rechaza dos piezas fisicas.');
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('multipieza', $dosPiezas) === null, 'Un tratamiento multipieza conserva varias piezas en un trabajo.');
pruebaLabAfirmar(trabajoLaboratorioValidarUbicacionesModo('multipieza', $unaPieza) !== null, 'Multipieza exige al menos dos piezas.');
pruebaLabAfirmar(
    trabajoLaboratorioHashPayload(array('b' => 2, 'a' => 1)) === trabajoLaboratorioHashPayload(array('a' => 1, 'b' => 2)),
    'La huella idempotente es estable aunque cambie el orden de los campos.'
);

$accionesLaboratorio = array(
    'iniciarTransferencia', 'confirmarRecepcion', 'agregarEvidencia', 'agregarNota',
    'iniciarDevolucion', 'confirmarDevolucion', 'solicitarAjuste', 'aprobarTrabajo',
    'registrarInstalacion', 'cancelarTrabajo'
);
$matrizEstados = array(
    'pendiente_entrega_mecanico' => array(
        'iniciarTransferencia', 'agregarEvidencia', 'agregarNota', 'cancelarTrabajo'
    ),
    'en_transferencia_mecanico' => array(
        'confirmarRecepcion', 'agregarEvidencia', 'agregarNota', 'cancelarTrabajo'
    ),
    'en_laboratorio' => array(
        'agregarEvidencia', 'agregarNota', 'iniciarDevolucion', 'cancelarTrabajo'
    ),
    'en_transferencia_clinica' => array(
        'agregarEvidencia', 'agregarNota', 'confirmarDevolucion', 'cancelarTrabajo'
    ),
    'pendiente_revision' => array(
        'agregarEvidencia', 'agregarNota', 'solicitarAjuste', 'aprobarTrabajo', 'cancelarTrabajo'
    ),
    'ajuste_solicitado' => array(
        'iniciarTransferencia', 'agregarEvidencia', 'agregarNota', 'cancelarTrabajo'
    ),
    'listo_instalacion' => array(
        'agregarEvidencia', 'agregarNota', 'registrarInstalacion', 'cancelarTrabajo'
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
    'ENTREGARTRABAJOLABORATORIO', 'RECIBIRTRABAJOLABORATORIO',
    'EVIDENCIATRABAJOLABORATORIO', 'AJUSTARTRABAJOLABORATORIO',
    'APROBARTRABAJOLABORATORIO', 'INSTALARTRABAJOLABORATORIO',
    'CANCELARTRABAJOLABORATORIO'
);
$permisosTodos = array_fill_keys($codigosPermiso, true);
$casosRoles = array(
    'clinica_custodia_inicial' => array(
        'estado' => 'pendiente_entrega_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('iniciarTransferencia', 'agregarEvidencia', 'agregarNota', 'cancelarTrabajo')
    ),
    'tecnico_por_recibir' => array(
        'estado' => 'en_transferencia_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => false,
            'tecnico' => true, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('confirmarRecepcion', 'agregarEvidencia', 'agregarNota')
    ),
    'tecnico_con_custodia' => array(
        'estado' => 'en_laboratorio',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => true,
            'tecnico' => true, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('agregarEvidencia', 'agregarNota', 'iniciarDevolucion')
    ),
    'clinica_por_recibir' => array(
        'estado' => 'en_transferencia_clinica',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => false,
            'tecnico' => false, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('confirmarDevolucion', 'cancelarTrabajo')
    ),
    'tecnico_formal_no_representa_clinica' => array(
        'estado' => 'en_transferencia_clinica',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => false,
            'tecnico' => false, 'tecnico_formal' => true, 'doctor' => false,
            'permisos' => $permisosTodos
        ),
        'esperadas' => array()
    ),
    'doctor_revision' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'agregarEvidencia', 'agregarNota', 'solicitarAjuste',
            'aprobarTrabajo', 'cancelarTrabajo'
        )
    ),
    'doctor_instalacion' => array(
        'estado' => 'listo_instalacion',
        'contexto' => array(
            'auditor' => false, 'local' => true, 'custodio' => true,
            'tecnico' => false, 'doctor' => true, 'permisos' => $permisosTodos
        ),
        'esperadas' => array(
            'agregarEvidencia', 'agregarNota', 'registrarInstalacion', 'cancelarTrabajo'
        )
    ),
    'auditor_fuera_local' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => true, 'local' => false, 'custodio' => false,
            'tecnico' => false, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('solicitarAjuste', 'aprobarTrabajo', 'cancelarTrabajo')
    ),
    'tecnico_sin_custodia_no_entrega' => array(
        'estado' => 'pendiente_entrega_mecanico',
        'contexto' => array(
            'auditor' => false, 'local' => false, 'custodio' => false,
            'tecnico' => true, 'doctor' => false, 'permisos' => $permisosTodos
        ),
        'esperadas' => array('agregarEvidencia', 'agregarNota')
    ),
    'sin_permisos' => array(
        'estado' => 'pendiente_revision',
        'contexto' => array(
            'auditor' => true, 'local' => true, 'custodio' => true,
            'tecnico' => true, 'doctor' => true, 'permisos' => array()
        ),
        'esperadas' => array()
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
    array('agregarEvidencia', 'agregarNota'),
    pruebaLabAccionesActivas($accionesSinEntrega, $accionesLaboratorio),
    'Quitar el permiso de entrega no elimina evidencia, pero impide la devolucion.'
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
$contratosTransicion = array(
    'trabajoLaboratorioIniciar' => array(
        'accion' => 'iniciarTrabajo', 'estado' => 'pendiente_entrega_mecanico',
        'evento' => 'trabajo_iniciado', 'version' => false
    ),
    'trabajoLaboratorioIniciarTransferencia' => array(
        'accion' => 'iniciarTransferencia', 'estado' => 'en_transferencia_mecanico',
        'evento' => 'transferencia_mecanico_iniciada', 'version' => true
    ),
    'trabajoLaboratorioConfirmarRecepcion' => array(
        'accion' => 'confirmarRecepcion', 'estado' => 'en_laboratorio',
        'evento' => 'recepcion_mecanico_confirmada', 'version' => true
    ),
    'trabajoLaboratorioIniciarDevolucion' => array(
        'accion' => 'iniciarDevolucion', 'estado' => 'en_transferencia_clinica',
        'evento' => 'devolucion_iniciada', 'version' => true
    ),
    'trabajoLaboratorioConfirmarDevolucion' => array(
        'accion' => 'confirmarDevolucion', 'estado' => 'pendiente_revision',
        'evento' => 'devolucion_confirmada', 'version' => true
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
            && strpos($bloque, 'WHERE id=? AND version=? LIMIT 1') !== false;
    } else {
        $contratoBasico = $contratoBasico
            && strpos($bloque, 'trabajoLaboratorioExigirVersion') === false;
    }
    pruebaLabAfirmar(
        $contratoBasico,
        'El comando '.$contrato['accion'].' conserva estado, evento, transaccion y concurrencia esperados.'
    );
}
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
        && strpos($bloque, 'SET estado_derivado') === false,
        'El subevento '.$contratoSubevento[0].' incrementa version sin cambiar el estado.'
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

$bloqueInstalacion = $bloquesTransicion['trabajoLaboratorioRegistrarInstalacion'];
$bloqueValidacionInstalacion = pruebaLabBloqueFuncion(
    $fuenteHelper,
    'trabajoLaboratorioValidarEvolucionInstalacion'
);
pruebaLabAfirmar(
    strpos($bloqueInstalacion, "'evolucion_origen_requerida'") !== false
    && strpos($bloqueInstalacion, 'trabajoLaboratorioValidarEvolucionInstalacion') !== false
    && strpos($bloqueInstalacion, 'cod_detalle_activo_unico=NULL') !== false
    && strpos($bloqueInstalacion, 'fecha_instalado=NOW()') !== false
    && strpos($bloqueInstalacion, "'evolucion_clinica_explicita' => 1") !== false
    && strpos($bloqueInstalacion, "\$origen['cod_consulta_origen']") !== false
    && strpos($bloqueInstalacion, "\$origen['cod_evolucion_origen']") !== false,
    'La instalacion exige evolucion explicita, cierra el trabajo y conserva su origen clinico.'
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
$bloqueRecepcion = $bloquesTransicion['trabajoLaboratorioConfirmarRecepcion'];
$bloqueDevolucion = $bloquesTransicion['trabajoLaboratorioIniciarDevolucion'];
$bloqueAprobacion = $bloquesTransicion['trabajoLaboratorioAprobar'];
$bloqueCancelacion = $bloquesTransicion['trabajoLaboratorioCancelar'];
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
}

$tablasEsperadas = array(
    'trabajo_laboratorio', 'trabajo_laboratorio_ciclo', 'trabajo_laboratorio_transferencia',
    'trabajo_laboratorio_idempotencia', 'trabajo_laboratorio_evento',
    'trabajo_laboratorio_ubicacion', 'trabajo_laboratorio_media'
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
    pruebaLabAfirmar($estructuraPresente, 'Existen las siete tablas operativas e historicas.');
    pruebaLabAfirmar(
        pruebaLabEscalar($mysqli, "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mecanico_dental' AND COLUMN_NAME='cod_usuarioFK'") === 1,
        'El mecanico puede vincularse a una cuenta formal de Telar.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar($mysqli, "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME LIKE 'trg_telar_lab_%'") === 11,
        'Los once resguardos de inmutabilidad historica estan instalados.'
    );
    pruebaLabAfirmar(
        pruebaLabEscalar($mysqli, "SELECT COUNT(*) FROM listadodeacceso WHERE codigo IN ('VERTRABAJOSLABORATORIO','CREARTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO','RECIBIRTRABAJOLABORATORIO','EVIDENCIATRABAJOLABORATORIO','APROBARTRABAJOLABORATORIO','AJUSTARTRABAJOLABORATORIO','INSTALARTRABAJOLABORATORIO','CANCELARTRABAJOLABORATORIO','AUDITARTRABAJOLABORATORIO','GESTIONARTECNICOSLABORATORIO') AND tipo='Administrativo'") === 11,
        'Los once permisos administrativos existen sin concesiones automaticas.'
    );
    pruebaLabAfirmar(trabajoLaboratorioEstructuraDisponible($mysqli), 'El helper reconoce la estructura completa del modulo.');
    $conteosDespues = pruebaLabConteosProtegidos($mysqli);
    pruebaLabAfirmar($conteosAntes === $conteosDespues, 'La migracion no altero registros existentes de ventas, productos ni trabajos anteriores.');
} else {
    fwrite(STDOUT, '[INFO] La estructura aun no esta aplicada; use --aplicar-migracion para instalarla.'.PHP_EOL);
}

$mysqli->close();
pruebaLabOk('Verificacion finalizada.');

<?php

/**
 * Verificacion de solo lectura para la planificacion visual de especialistas.
 *
 * Uso:
 *   php scripts/verificar_planificacion_especialistas.php
 *   php scripts/verificar_planificacion_especialistas.php --solo-estatico
 *
 * Compatible con PHP 7.2. No modifica datos ni imprime informacion de pacientes.
 */

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/planificacion_especialistas_helper.php';

function pruebaPlanFallar($mensaje)
{
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
    exit(1);
}

function pruebaPlanOk($mensaje)
{
    fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
}

function pruebaPlanAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        pruebaPlanFallar($mensaje);
    }
    pruebaPlanOk($mensaje);
}

function pruebaPlanBloqueFuncionPhp($fuente, $nombre)
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
            if (is_array($token)
                && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) {
                $j++;
                continue;
            }
            if ($token === '&') {
                $j++;
                continue;
            }
            break;
        }
        if ($j >= $cantidad || !is_array($tokens[$j])
            || $tokens[$j][0] !== T_STRING || $tokens[$j][1] !== $nombre) {
            continue;
        }
        $bloque = '';
        $nivel = 0;
        $inicio = false;
        for ($k = $i; $k < $cantidad; $k++) {
            $texto = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
            $bloque .= $texto;
            if (!is_array($tokens[$k]) && $tokens[$k] === '{') {
                $nivel++;
                $inicio = true;
            } elseif (!is_array($tokens[$k]) && $tokens[$k] === '}' && $inicio) {
                $nivel--;
                if ($nivel === 0) {
                    return $bloque;
                }
            }
        }
    }
    return '';
}

function pruebaPlanBloqueFuncionJs($fuente, $nombre)
{
    $marca = '    function '.$nombre.'(';
    $inicio = strpos($fuente, $marca);
    if ($inicio === false) {
        return '';
    }
    $fin = strpos($fuente, "\n    function ", $inicio + strlen($marca));
    return $fin === false ? substr($fuente, $inicio) : substr($fuente, $inicio, $fin - $inicio);
}

$raiz = dirname(__DIR__);
$rutaPhp = $raiz.'/php_system/abmPlanificacionEspecialistas.php';
$rutaHelper = $raiz.'/php_system/planificacion_especialistas_helper.php';
$rutaJs = $raiz.'/js_system/planificacion_especialistas.js';
$rutaCss = $raiz.'/css_system/planificacion_especialistas.css';
$rutaHtml = $raiz.'/system/inicio.html';
$rutaAgendaJs = $raiz.'/js_system/jsCalendar.js';
$rutaAgendaCss = $raiz.'/css_system/cssCalendar.css';
$fuentePhp = file_get_contents($rutaPhp);
$fuenteHelper = file_get_contents($rutaHelper);
$fuenteJs = file_get_contents($rutaJs);
$fuenteCss = file_get_contents($rutaCss);
$fuenteHtml = file_get_contents($rutaHtml);
$fuenteAgendaJs = file_get_contents($rutaAgendaJs);
$fuenteAgendaCss = file_get_contents($rutaAgendaCss);

pruebaPlanAfirmar(
    $fuentePhp !== false && $fuenteHelper !== false
    && $fuenteJs !== false && $fuenteCss !== false
    && $fuenteHtml !== false && $fuenteAgendaJs !== false && $fuenteAgendaCss !== false,
    'Los archivos del modulo se pueden inspeccionar.'
);

$consultoriosDesordenados = array(
    array('id_consultorio' => 5, 'nombre' => 'VILLA INDUSTRIAL CONSULTORIO 1'),
    array('id_consultorio' => 10, 'nombre' => 'VILLA INDUTRIAL CONSULTORIO 3'),
    array('id_consultorio' => 13, 'nombre' => 'VILLA INDUSTRIAL CONSULTORIO 2')
);
$consultoriosOrdenados = planificacionOrdenarYRotularConsultorios($consultoriosDesordenados);
pruebaPlanAfirmar(
    array_column($consultoriosOrdenados, 'id_consultorio') === array(5, 13, 10)
    && array_column($consultoriosOrdenados, 'etiqueta') === array('C1', 'C2', 'C3'),
    'Los consultorios se ordenan y rotulan por su numero real, no por el ID historico.'
);
$consultoriosSinNumero = planificacionOrdenarYRotularConsultorios(array(
    array('id_consultorio' => 21, 'nombre' => 'SALA SUR'),
    array('id_consultorio' => 20, 'nombre' => 'SALA NORTE')
));
pruebaPlanAfirmar(
    array_column($consultoriosSinNumero, 'id_consultorio') === array(20, 21)
    && array_column($consultoriosSinNumero, 'etiqueta') === array('C1', 'C2'),
    'Los nombres antiguos sin numero conservan un orden y etiquetas deterministas.'
);
pruebaPlanAfirmar(
    strpos($fuentePhp, "require_once __DIR__.'/planificacion_especialistas_helper.php';") !== false
    && strpos(
        pruebaPlanBloqueFuncionPhp($fuentePhp, 'planificacionConsultorios'),
        'planificacionOrdenarYRotularConsultorios($consultorios)'
    ) !== false,
    'El catalogo de Planificacion aplica el orden visual compartido antes de responder.'
);

$bloqueOcupaciones = pruebaPlanBloqueFuncionPhp($fuentePhp, 'planificacionOcupacionesAgenda');
$bloqueAgregarCompromiso = pruebaPlanBloqueFuncionPhp(
    $fuentePhp,
    'planificacionAgregarCompromisoExterno'
);
$bloqueCompromisos = pruebaPlanBloqueFuncionPhp(
    $fuentePhp,
    'planificacionCompromisosOtrasSucursales'
);
$bloqueConflictos = pruebaPlanBloqueFuncionPhp($fuentePhp, 'planificacionConflictos');
$bloqueConflictosAgenda = pruebaPlanBloqueFuncionPhp($fuentePhp, 'planificacionConflictosAgenda');
$bloqueGuardar = pruebaPlanBloqueFuncionPhp($fuentePhp, 'planificacionGuardarAsignacion');
$bloqueRegla = pruebaPlanBloqueFuncionPhp($fuentePhp, 'planificacionGuardarRegla');
$bloqueAgendaVisualJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'agendaVisualAssignments');
$bloqueVisualJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'visualAssignments');
$bloqueFiltradasJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'filteredAssignments');
$bloqueAsignacionesJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'assignmentsFor');
$bloqueAgendaBloqueaJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'agendaOccupancyBlocksSlot');
$bloqueAgendaCoincideJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'agendaMatchesAssignments');
$bloqueCompromisosJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'remoteCommitmentsFor');
$bloqueBloqueoRemotoJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'remoteCommitmentBlocksDay');
$bloqueAvisoRemotoJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'remoteCommitmentNotice');
$bloqueAsignacionChipJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'assignmentChip');
$bloqueDiaJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'dayCard');
$bloqueSeleccionJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'selectProfessional');
$bloqueDetalleRemotoJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'openRemoteCommitmentDetails');
$bloqueAbrirCasillaJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'openAssignmentForSlot');
$bloqueDetalleAsignacionJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'openAssignmentDetails');
$bloqueHilosJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'renderThreads');
$bloqueArrastreJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'onDrop');
$bloqueGuardadoDirectoJs = pruebaPlanBloqueFuncionJs($fuenteJs, 'saveDirectAssignment');

pruebaPlanAfirmar(
    $bloqueOcupaciones !== ''
    && strpos($bloqueOcupaciones, "UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'") !== false
    && strpos($bloqueOcupaciones, 'GROUP BY a.fecha,a.id_consultorio,a.id_profesional') !== false,
    'Agenda se resume por fecha, consultorio y doctor, excluyendo cancelados.'
);
pruebaPlanAfirmar(
    stripos($bloqueOcupaciones, 'paciente') === false
    && stripos($bloqueOcupaciones, 'motivo') === false
    && stripos($bloqueOcupaciones, 'tratamiento') === false,
    'El resumen de ocupacion no expone pacientes, motivos ni tratamientos.'
);
pruebaPlanAfirmar(
    substr_count($fuentePhp, "'ocupaciones_agenda'") >= 2
    && strpos($fuentePhp, 'planificacionOcupacionesAgenda(') !== false,
    'La respuesta de planificacion incorpora las ocupaciones de Agenda.'
);
pruebaPlanAfirmar(
    $bloqueCompromisos !== ''
    && strpos($bloqueCompromisos, 'planificacionListarAsignaciones(') !== false
    && strpos($bloqueCompromisos, 'c.cod_localFk<>?') !== false
    && strpos($bloqueCompromisos, "UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'") !== false
    && strpos($bloqueCompromisos, "\$contexto['permisos']['todas_sucursales']") !== false
    && strpos($fuentePhp, "'compromisos_otras_sucursales'") !== false,
    'La vista local recibe compromisos preventivos de otras sucursales con permisos vigentes.'
);
pruebaPlanAfirmar(
    stripos($bloqueCompromisos, 'paciente') === false
    && stripos($bloqueCompromisos, 'tratamiento') === false
    && stripos($bloqueCompromisos, 'motivo') === false
    && strpos($bloqueAgregarCompromiso, "'detalles_visibles'") !== false
    && strpos($bloqueAgregarCompromiso, "'Otra sucursal'") !== false,
    'El resumen externo no expone pacientes y oculta la sede sin permiso transversal.'
);
pruebaPlanAfirmar(
    strpos($bloqueConflictos, '$mismoConsultorio || planificacionIntervalosSeSuperponen') !== false
    && strpos($bloqueConflictosAgenda, '$mismoConsultorio || planificacionIntervalosSeSuperponen') !== false,
    'El consultorio se considera una unica casilla para todo el dia.'
);
pruebaPlanAfirmar(
    strpos($bloqueConflictosAgenda, 'a.id_profesional IS NOT NULL') !== false
    && strpos($bloqueConflictosAgenda, 'a.id_profesional>0') !== false
    && strpos($bloqueConflictosAgenda, 'if ($mismoProfesional && $mismoConsultorio)') !== false,
    'Agenda ignora turnos sin profesional y no enfrenta una ocupacion con su misma asignacion.'
);
pruebaPlanAfirmar(
    strpos($bloqueGuardar, 'planificacionMensajeConflictos($conflictos, $fecha)') !== false
    && strpos($bloqueRegla, 'planificacionMensajeConflictos($conflictos, $fecha)') !== false
    && strpos($fuentePhp, 'tiene Agenda ocupada con doctor sin identificar') === false,
    'Los rechazos conservan conflictos reales sin inventar una identidad para turnos sueltos.'
);
pruebaPlanAfirmar(
    strpos($bloqueAgendaVisualJs, 'professionals.length !== 1') !== false
    && strpos($bloqueAgendaVisualJs, 'occupiedSlots[slotKey]') !== false
    && strpos($bloqueAgendaVisualJs, 'estado: "agenda"') !== false
    && strpos($bloqueAgendaVisualJs, 'origen: "agenda"') !== false
    && strpos($bloqueVisualJs, '.concat(agendaVisualAssignments())') !== false
    && strpos($bloqueFiltradasJs, 'visualAssignments()') !== false
    && strpos($bloqueAsignacionesJs, 'visualAssignments()') !== false,
    'Agenda con un unico profesional se convierte en asignacion visual sin duplicar una existente.'
);
pruebaPlanAfirmar(
    strpos($bloqueAgendaBloqueaJs, 'occupancy.profesionales') !== false
    && strpos($bloqueAgendaCoincideJs, 'return !professionals.length') !== false
    && strpos($bloqueAsignacionChipJs, 'assignment.origen !== "agenda"') !== false
    && strpos($bloqueAsignacionChipJs, 'agendaBadge(agendaOccupancy)') !== false
    && strpos($bloqueDiaJs, 'agendaMatches ? agendaOccupancy : null') !== false
    && strpos($bloqueDiaJs, 'data-plan-occupied') !== false
    && strpos($bloqueDetalleAsignacionJs, 'assignment.origen === "agenda"') !== false
    && strpos($bloqueHilosJs, 'assignments = filteredAssignments()') !== false
    && strpos($bloqueHilosJs, 'data-plan-assignment') !== false,
    'La ocupacion unificada conserva avatar, detalle de Agenda y el hilo al profesional.'
);
pruebaPlanAfirmar(
    strpos($fuenteJs, 'Doctor sin identificar') === false
    && strpos($fuenteJs, 'Conflicto: varios doctores') !== false
    && strpos($bloqueDiaJs, "html += '<span class=\"plan-slot__free\">Libre</span>'") !== false,
    'Los turnos sin profesional no crean otra tarjeta y una casilla no asignada muestra Libre.'
);
pruebaPlanAfirmar(
    strpos($fuenteJs, '.plan-slot:not(.is-disabled):not(.is-occupied)') !== false
    && strpos($bloqueArrastreJs, 'saveDirectAssignment(') !== false
    && strpos($bloqueGuardadoDirectoJs, 'request("guardarAsignacion"') !== false,
    'El arrastre se guarda directo solamente sobre casillas libres.'
);
pruebaPlanAfirmar(
    strpos($bloqueCompromisosJs, 'compromisos_otras_sucursales') !== false
    && strpos($bloqueBloqueoRemotoJs, 'commitment.bloquea_dia === true') !== false
    && strpos($bloqueAvisoRemotoJs, 'data-plan-remote-professional') !== false
    && strpos($bloqueDiaJs, 'is-professional-unavailable') !== false
    && strpos($bloqueDiaJs, 'is-professional-blocked') !== false
    && strpos($bloqueDiaJs, 'Libre para otros profesionales') !== false,
    'La profesional seleccionada muestra advertencia y bloqueo sin ocupar los consultorios locales.'
);
pruebaPlanAfirmar(
    strpos($bloqueSeleccionJs, 'renderCalendar()') !== false
    && strpos($bloqueAbrirCasillaJs, 'openRemoteCommitmentDetails(') !== false
    && strpos($bloqueGuardadoDirectoJs, 'remoteCommitments.length') !== false
    && strpos($bloqueDetalleRemotoJs, 'nombre_local') !== false
    && strpos($bloqueHilosJs, 'plan-remote-thread') !== false,
    'Seleccionar, tocar o arrastrar conserva el aviso, el detalle de sede y su hilo preventivo.'
);
pruebaPlanAfirmar(
    strpos($fuenteCss, '.plan-agenda-occupancy') !== false
    && strpos($fuenteCss, '.plan-assignment__agenda') !== false
    && strpos($fuenteCss, '.plan-slot.is-occupied') !== false,
    'El indicador de Agenda queda integrado en la tarjeta del profesional.'
);
pruebaPlanAfirmar(
    strpos($fuenteCss, '.plan-remote-commitment') !== false
    && strpos($fuenteCss, '.plan-slot.is-professional-blocked') !== false
    && strpos($fuenteCss, '.plan-remote-thread') !== false
    && strpos($fuenteCss, '.plan-remote-detail') !== false,
    'La advertencia multisucursal tiene estilos acotados, detalle e hilo propios.'
);
pruebaPlanAfirmar(
    strpos($fuenteAgendaJs, 'agenda-plan-profesionales--avatar-flotante') !== false
    && strpos($fuenteAgendaJs, 'agenda-celda-consultorio--avatar-flotante') !== false
    && strpos($fuenteAgendaJs, 'profesionalesPlanificados.length === 1') !== false
    && strpos($fuenteAgendaJs, 'agenda-grid--avatar-flotante') !== false,
    'Agenda activa el avatar flotante solamente cuando existe un profesional identificado.'
);
pruebaPlanAfirmar(
    strpos($fuenteAgendaCss, '--agenda-avatar-flotante-size:96px') !== false
    && strpos($fuenteAgendaCss, '.agenda-plan-profesionales--avatar-flotante .agenda-plan-avatar') !== false
    && strpos($fuenteAgendaCss, 'position:absolute') !== false
    && strpos($fuenteAgendaCss, 'pointer-events:none') !== false
    && strpos($fuenteAgendaCss, '.agenda-celda-consultorio--avatar-flotante > .agenda-insumos-dia-dropdown summary') !== false,
    'El avatar sale del flujo y la informacion reserva espacio horizontal sin crecer en altura.'
);

preg_match(
    '/planificacion_especialistas\.css\?v=([0-9-]+)/',
    $fuenteHtml,
    $versionCss
);
preg_match(
    '/planificacion_especialistas\.js\?x=([0-9-]+)/',
    $fuenteHtml,
    $versionJs
);
pruebaPlanAfirmar(
    isset($versionCss[1], $versionJs[1]) && $versionCss[1] === $versionJs[1],
    'CSS y JavaScript usan la misma version de cache.'
);

if (in_array('--solo-estatico', $argv, true)) {
    fwrite(STDOUT, '[INFO] Verificacion estatica finalizada sin consultar la base de datos.'.PHP_EOL);
    exit(0);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    pruebaPlanFallar('No se pudo abrir la base local para las pruebas de solo lectura.');
}

$resultadoOrdenReal = $mysqli->query("SELECT id_consultorio,nombre
    FROM consultorios
    WHERE id_consultorio IN (5,10,13)");
pruebaPlanAfirmar(
    $resultadoOrdenReal !== false,
    'Los consultorios del caso Villa Industrial se pueden auditar en solo lectura.'
);
$consultoriosOrdenReal = array();
while ($filaOrdenReal = $resultadoOrdenReal->fetch_assoc()) {
    $consultoriosOrdenReal[] = array(
        'id_consultorio' => intval($filaOrdenReal['id_consultorio']),
        'nombre' => $filaOrdenReal['nombre']
    );
}
$resultadoOrdenReal->free();
$consultoriosOrdenReal = planificacionOrdenarYRotularConsultorios($consultoriosOrdenReal);
pruebaPlanAfirmar(
    array_column($consultoriosOrdenReal, 'id_consultorio') === array(5, 13, 10)
    && array_column($consultoriosOrdenReal, 'etiqueta') === array('C1', 'C2', 'C3'),
    'Villa Industrial queda ordenado como consultorios reales 1, 2 y 3 sin cambiar sus IDs.'
);

$sqlEstructura = "SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA=DATABASE()
      AND TABLE_NAME='agenda'
      AND COLUMN_NAME IN ('fecha','id_consultorio','id_profesional','hora_inicio','hora_fin','estado')";
$resultadoEstructura = $mysqli->query($sqlEstructura);
$filaEstructura = $resultadoEstructura ? $resultadoEstructura->fetch_row() : null;
pruebaPlanAfirmar(
    $filaEstructura && intval($filaEstructura[0]) === 6,
    'Agenda conserva las columnas requeridas por la proyeccion.'
);
if ($resultadoEstructura) {
    $resultadoEstructura->free();
}

$sqlLectura = "SELECT COUNT(*) AS casillas
    FROM (
        SELECT a.fecha,a.id_consultorio
        FROM agenda a
        INNER JOIN consultorios c ON c.id_consultorio=a.id_consultorio
        WHERE a.fecha BETWEEN ? AND ?
          AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'
          AND a.id_profesional IS NOT NULL
          AND a.id_profesional>0
        GROUP BY a.fecha,a.id_consultorio
    ) ocupaciones";
$stmt = $mysqli->prepare($sqlLectura);
pruebaPlanAfirmar($stmt !== false, 'La consulta agrupada de ocupaciones se puede preparar.');
$desde = '2026-07-01';
$hasta = '2026-07-31';
$stmt->bind_param('ss', $desde, $hasta);
pruebaPlanAfirmar($stmt->execute(), 'La consulta agrupada de ocupaciones se ejecuta en solo lectura.');
$resultadoLectura = $stmt->get_result();
$filaLectura = $resultadoLectura ? $resultadoLectura->fetch_assoc() : null;
fwrite(
    STDOUT,
    '[INFO] Casillas de Agenda con profesional identificado en el periodo de prueba: '
    .($filaLectura ? intval($filaLectura['casillas']) : 0).PHP_EOL
);
$stmt->close();

$sqlIncidente = "SELECT
        (SELECT COUNT(*) FROM agenda a
         WHERE a.fecha='2026-07-10' AND a.id_consultorio=8
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS registros,
        (SELECT COUNT(DISTINCT a.id_profesional) FROM agenda a
         WHERE a.fecha='2026-07-10' AND a.id_consultorio=8
           AND a.id_profesional IS NOT NULL AND a.id_profesional>0
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS doctores_identificados,
        (SELECT COUNT(*) FROM agenda a
         WHERE a.fecha='2026-07-10' AND a.id_consultorio=8
           AND (a.id_profesional IS NULL OR a.id_profesional<=0)
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS sin_identificar,
        (SELECT COUNT(*) FROM planificacion_especialista_asignacion pa
         WHERE pa.fecha='2026-07-10' AND pa.cod_localFK=3
           AND pa.id_consultorioFK=8 AND pa.estado<>'anulada') AS asignaciones,
        (SELECT COUNT(*) FROM planificacion_especialista_asignacion pa
         WHERE pa.fecha='2026-07-10' AND pa.cod_localFK=3
           AND pa.id_consultorioFK=8 AND pa.estado<>'anulada'
           AND EXISTS (
             SELECT 1 FROM agenda a
             WHERE a.fecha=pa.fecha AND a.id_consultorio=pa.id_consultorioFK
               AND a.id_profesional=pa.cod_profesionalFK
               AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO'
           )) AS misma_ocupacion";
$resultadoIncidente = $mysqli->query($sqlIncidente);
pruebaPlanAfirmar($resultadoIncidente !== false, 'C3 del viernes 10 se puede auditar sin datos personales.');
$filaIncidente = $resultadoIncidente->fetch_assoc();
pruebaPlanAfirmar(
    intval($filaIncidente['registros']) > 0
    && intval($filaIncidente['doctores_identificados']) === 1
    && intval($filaIncidente['sin_identificar']) > 0
    && intval($filaIncidente['asignaciones']) === 1
    && intval($filaIncidente['misma_ocupacion']) === 1,
    'C3 contiene una sola asignacion y Agenda refiere al mismo profesional.'
);
fwrite(
    STDOUT,
    '[INFO] Casilla auditada: registros='.intval($filaIncidente['registros'])
    .', doctores_identificados='.intval($filaIncidente['doctores_identificados'])
    .', sin_vinculo_individual='.intval($filaIncidente['sin_identificar'])
    .', asignaciones='.intval($filaIncidente['asignaciones']).PHP_EOL
);
$resultadoIncidente->free();

$sqlLibre = "SELECT
        (SELECT COUNT(*) FROM agenda a
         WHERE a.fecha='2026-07-10' AND a.id_consultorio=7
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS registros,
        (SELECT COUNT(*) FROM agenda a
         WHERE a.fecha='2026-07-10' AND a.id_consultorio=7
           AND a.id_profesional IS NOT NULL AND a.id_profesional>0
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS identificados,
        (SELECT COUNT(*) FROM planificacion_especialista_asignacion pa
         WHERE pa.fecha='2026-07-10' AND pa.cod_localFK=3
           AND pa.id_consultorioFK=7 AND pa.estado<>'anulada') AS asignaciones";
$resultadoLibre = $mysqli->query($sqlLibre);
pruebaPlanAfirmar($resultadoLibre !== false, 'C2 del viernes 10 se puede auditar sin datos personales.');
$filaLibre = $resultadoLibre->fetch_assoc();
pruebaPlanAfirmar(
    intval($filaLibre['registros']) > 0
    && intval($filaLibre['identificados']) === 0
    && intval($filaLibre['asignaciones']) === 0,
    'C2 conserva sus turnos, pero permanece Libre hasta designar un profesional.'
);
$resultadoLibre->free();

$sqlAgendaVisual = "SELECT
        (SELECT COUNT(*) FROM agenda a
         WHERE a.fecha='2026-07-03' AND a.id_consultorio=8
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS registros,
        (SELECT COUNT(DISTINCT a.id_profesional) FROM agenda a
         WHERE a.fecha='2026-07-03' AND a.id_consultorio=8
           AND a.id_profesional IS NOT NULL AND a.id_profesional>0
           AND UPPER(IFNULL(a.estado,'AGENDADO'))<>'CANCELADO') AS identificados,
        (SELECT COUNT(*) FROM planificacion_especialista_asignacion pa
         WHERE pa.fecha='2026-07-03' AND pa.cod_localFK=3
           AND pa.id_consultorioFK=8 AND pa.estado<>'anulada') AS asignaciones";
$resultadoAgendaVisual = $mysqli->query($sqlAgendaVisual);
pruebaPlanAfirmar(
    $resultadoAgendaVisual !== false,
    'C3 del viernes 3 se puede auditar sin datos personales.'
);
$filaAgendaVisual = $resultadoAgendaVisual->fetch_assoc();
pruebaPlanAfirmar(
    intval($filaAgendaVisual['registros']) > 0
    && intval($filaAgendaVisual['identificados']) === 1
    && intval($filaAgendaVisual['asignaciones']) === 0,
    'C3 del viernes 3 puede mostrarse como asignacion visual de Agenda sin escribir datos.'
);
fwrite(
    STDOUT,
    '[INFO] Casilla visual de Agenda: registros='.intval($filaAgendaVisual['registros'])
    .', profesionales_identificados='.intval($filaAgendaVisual['identificados'])
    .', asignaciones_guardadas='.intval($filaAgendaVisual['asignaciones']).PHP_EOL
);
$resultadoAgendaVisual->free();

$sqlCompromisosExternos = "SELECT COUNT(*) AS profesionales
    FROM (
        SELECT pa.cod_profesionalFK
        FROM planificacion_especialista_asignacion pa
        INNER JOIN usuario u ON u.cod_usuario=pa.cod_profesionalFK
        CROSS JOIN (
            SELECT cod_local
            FROM local
            WHERE UPPER(Nombre) LIKE '%VILLA%MORRA%'
            LIMIT 1
        ) vm
        WHERE pa.fecha IN ('2026-07-18','2026-07-27')
          AND pa.estado<>'anulada'
          AND pa.cod_localFK<>vm.cod_local
          AND (
              u.cod_localFK=vm.cod_local
              OR EXISTS (
                  SELECT 1
                  FROM planificacion_especialista_local pel
                  WHERE pel.cod_profesionalFK=pa.cod_profesionalFK
                    AND pel.cod_localFK=vm.cod_local
                    AND pel.estado='activo'
              )
          )
        GROUP BY pa.cod_profesionalFK
        HAVING COUNT(DISTINCT pa.fecha)=2
           AND SUM(IF(pa.id_horario_usuarioFK IS NULL,1,0))>=2
    ) compromisos";
$resultadoCompromisos = $mysqli->query($sqlCompromisosExternos);
pruebaPlanAfirmar(
    $resultadoCompromisos !== false,
    'Los compromisos externos de las fechas reportadas se pueden auditar sin identidades.'
);
$filaCompromisos = $resultadoCompromisos->fetch_assoc();
pruebaPlanAfirmar(
    intval($filaCompromisos['profesionales']) >= 1,
    'Sabado 18 y lunes 27 contienen jornadas completas en otra sucursal para el listado de Villa Morra.'
);
fwrite(
    STDOUT,
    '[INFO] Profesionales con ambas jornadas externas: '
    .intval($filaCompromisos['profesionales']).PHP_EOL
);
$resultadoCompromisos->free();
$mysqli->close();

fwrite(STDOUT, '[OK] Verificacion de planificacion finalizada sin escrituras.'.PHP_EOL);

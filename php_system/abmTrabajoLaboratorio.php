<?php

/**
 * Dispatcher HTTP de Trabajos de laboratorio dental - Sistema Telar.
 * Contrato oficial: POST `accion`. `funt` se conserva solamente como fallback.
 * Compatible con PHP 7.2.
 */

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/verificar_navegador.php';
require_once __DIR__.'/centro_facturas_helper.php';
require_once __DIR__.'/centro_legajos_helper.php';
require_once __DIR__.'/interconsulta_seguimiento_paciente_helper.php';
require_once __DIR__.'/trabajo_laboratorio_helper.php';

date_default_timezone_set('Etc/GMT+3');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, private');

function trabajoLaboratorioHttpResponder($respuesta, $estadoHttp = 200)
{
    if (!headers_sent()) {
        http_response_code(intval($estadoHttp));
    }
    echo json_encode(trabajoLaboratorioUtf8($respuesta), JSON_UNESCAPED_UNICODE);
    exit;
}

function trabajoLaboratorioHttpArchivos($archivos, $descripcion)
{
    $salida = array();
    if (!isset($archivos['name'])) {
        return $salida;
    }
    $nombres = is_array($archivos['name']) ? $archivos['name'] : array($archivos['name']);
    $temporales = is_array($archivos['tmp_name']) ? $archivos['tmp_name'] : array($archivos['tmp_name']);
    $errores = is_array($archivos['error']) ? $archivos['error'] : array($archivos['error']);
    $tamanos = is_array($archivos['size']) ? $archivos['size'] : array($archivos['size']);
    if (count($nombres) > 5) {
        trabajoLaboratorioLanzar('cantidad_evidencias_invalida', 'Puede adjuntar hasta cinco imagenes por operacion.');
    }
    foreach ($nombres as $indice => $nombre) {
        $error = isset($errores[$indice]) ? intval($errores[$indice]) : UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($error !== UPLOAD_ERR_OK) {
            trabajoLaboratorioLanzar('carga_media_incompleta', 'Una de las imagenes no pudo cargarse completamente.');
        }
        $tamano = isset($tamanos[$indice]) ? intval($tamanos[$indice]) : 0;
        if ($tamano <= 0 || $tamano > 10485760) {
            trabajoLaboratorioLanzar('evidencia_invalida', 'Cada imagen debe pesar como maximo 10 MB.');
        }
        $temporal = isset($temporales[$indice]) ? $temporales[$indice] : '';
        if ($temporal === '' || !is_uploaded_file($temporal)) {
            trabajoLaboratorioLanzar('carga_media_invalida', 'No se pudo validar el archivo recibido.');
        }
        $contenido = file_get_contents($temporal);
        if ($contenido === false || strlen($contenido) !== $tamano) {
            trabajoLaboratorioLanzar('carga_media_incompleta', 'No se pudo leer una de las imagenes recibidas.');
        }
        $salida[] = array(
            'data_base64' => base64_encode($contenido),
            'nombre_archivo' => trabajoLaboratorioTextoEntrada($nombre, 255),
            'descripcion' => trabajoLaboratorioTextoEntrada($descripcion, 255)
        );
    }
    return $salida;
}

function trabajoLaboratorioHttpContextoUsuario($mysqli, $codUsuario)
{
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    $esMecanico = trabajoLaboratorioObtenerTecnicoFormal($mysqli, $codUsuario, false) ? true : false;
    return array(
        'cod_usuario' => intval($codUsuario),
        'rol' => $usuario ? trabajoLaboratorioTextoUtf8($usuario['tipo']) : null,
        'cod_local' => $usuario ? intval($usuario['cod_localFK']) : null,
        'es_mecanico' => $esMecanico,
        'puede_ver_bandeja_mecanico' => $esMecanico
            && trabajoLaboratorioTienePermiso($mysqli, $codUsuario, 'VERTRABAJOSLABORATORIO'),
        'es_auditor' => trabajoLaboratorioUsuarioEsAuditor($mysqli, $codUsuario)
    );
}

try {
    $entrada = $_POST;
    $accion = trabajoLaboratorioTextoEntrada(isset($entrada['accion']) ? $entrada['accion'] : '', 50);
    if ($accion === '') {
        $accion = trabajoLaboratorioTextoEntrada(isset($entrada['funt']) ? $entrada['funt'] : '', 50);
    }
    if ($accion === '') {
        trabajoLaboratorioLanzar('accion_requerida', 'No se indico la accion solicitada.');
    }

    $codUsuario = trabajoLaboratorioEntero(isset($entrada['useru']) ? $entrada['useru'] : 0);
    $pass = isset($entrada['passu']) ? str_replace('=', '+', (string)$entrada['passu']) : '';
    $navegador = trabajoLaboratorioTextoEntrada(isset($entrada['navegador']) ? $entrada['navegador'] : '', 255);
    if ($codUsuario <= 0 || $pass === '' || $navegador === ''
        || verificar_navegador((string)$codUsuario, $navegador, $pass) !== 'ok') {
        trabajoLaboratorioHttpResponder(
            trabajoLaboratorioRespuesta(false, 'sesion_invalida', 'La sesion no es valida o ha vencido.'),
            401
        );
    }

    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        trabajoLaboratorioLanzar('conexion_no_disponible', 'No se pudo conectar con el servidor.');
    }
    if (!$mysqli->query("SET time_zone='-03:00'")) {
        trabajoLaboratorioLanzar(
            'zona_horaria_no_disponible',
            'No se pudo fijar la hora oficial de Paraguay para la operacion.'
        );
    }
    $usuario = trabajoLaboratorioUsuario($mysqli, $codUsuario);
    if (!$usuario) {
        trabajoLaboratorioHttpResponder(
            trabajoLaboratorioRespuesta(false, 'usuario_inactivo', 'El usuario no se encuentra activo.'),
            403
        );
    }

    if (isset($_FILES['evidencias'])) {
        $descripcionArchivos = isset($entrada['observacion']) ? $entrada['observacion'] : '';
        $evidenciasSubidas = trabajoLaboratorioHttpArchivos($_FILES['evidencias'], $descripcionArchivos);
        if (count($evidenciasSubidas) > 0) {
            $evidenciasExistentes = trabajoLaboratorioNormalizarEvidencias($entrada, 'evidencias');
            $entrada['evidencias'] = array_merge($evidenciasExistentes, $evidenciasSubidas);
        }
    }

    $contextoUsuario = trabajoLaboratorioHttpContextoUsuario($mysqli, $codUsuario);
    $accionesEscritura = array(
        'iniciarTrabajo', 'iniciarTransferencia', 'confirmarRecepcion', 'agregarEvidencia',
        'agregarNota', 'iniciarDevolucion', 'confirmarDevolucion', 'solicitarAjuste',
        'aprobarTrabajo', 'registrarInstalacion', 'cancelarTrabajo', 'asegurarHiloDetalle'
    );
    if (in_array($accion, $accionesEscritura, true) && !trabajoLaboratorioEstructuraDisponible($mysqli)) {
        trabajoLaboratorioLanzar(
            'estructura_laboratorio_no_instalada',
            'El modulo de trabajos de laboratorio todavia no esta instalado.'
        );
    }

    switch ($accion) {
        case 'iniciarTrabajo':
            $respuesta = trabajoLaboratorioIniciar($mysqli, $codUsuario, $entrada);
            break;
        case 'iniciarTransferencia':
            $respuesta = trabajoLaboratorioIniciarTransferencia($mysqli, $codUsuario, $entrada);
            break;
        case 'confirmarRecepcion':
            $respuesta = trabajoLaboratorioConfirmarRecepcion($mysqli, $codUsuario, $entrada);
            break;
        case 'agregarEvidencia':
            $respuesta = trabajoLaboratorioAgregarEvidencia($mysqli, $codUsuario, $entrada);
            break;
        case 'agregarNota':
            $respuesta = trabajoLaboratorioAgregarNota($mysqli, $codUsuario, $entrada);
            break;
        case 'iniciarDevolucion':
            $respuesta = trabajoLaboratorioIniciarDevolucion($mysqli, $codUsuario, $entrada);
            break;
        case 'confirmarDevolucion':
            $respuesta = trabajoLaboratorioConfirmarDevolucion($mysqli, $codUsuario, $entrada);
            break;
        case 'solicitarAjuste':
            $respuesta = trabajoLaboratorioSolicitarAjuste($mysqli, $codUsuario, $entrada);
            break;
        case 'aprobarTrabajo':
            $respuesta = trabajoLaboratorioAprobar($mysqli, $codUsuario, $entrada);
            break;
        case 'registrarInstalacion':
            $respuesta = trabajoLaboratorioRegistrarInstalacion($mysqli, $codUsuario, $entrada);
            break;
        case 'cancelarTrabajo':
            $respuesta = trabajoLaboratorioCancelar($mysqli, $codUsuario, $entrada);
            break;
        case 'asegurarHiloDetalle':
            $respuesta = trabajoLaboratorioAsegurarHiloDetalle(
                $mysqli,
                $codUsuario,
                isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta'] : 0
            );
            break;
        case 'obtenerContextoDetalle':
            $datos = trabajoLaboratorioObtenerContextoDetalle(
                $mysqli,
                $codUsuario,
                isset($entrada['cod_detalle_venta']) ? $entrada['cod_detalle_venta'] : 0
            );
            $datos['contexto_usuario'] = $contextoUsuario;
            $datos = array_merge($datos, $contextoUsuario);
            $respuesta = trabajoLaboratorioRespuesta(true, 'contexto_obtenido', 'Contexto clinico obtenido.', $datos, null);
            break;
        case 'obtenerTrabajo':
            if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
                trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
            }
            $idTrabajo = trabajoLaboratorioIdEntrada($entrada);
            $datos = trabajoLaboratorioObtenerDetalleTrabajo($mysqli, $codUsuario, $idTrabajo);
            $datos['contexto_usuario'] = $contextoUsuario;
            $datos = array_merge($datos, $contextoUsuario);
            $version = isset($datos['trabajo']['version']) ? intval($datos['trabajo']['version']) : null;
            $respuesta = trabajoLaboratorioRespuesta(true, 'trabajo_obtenido', 'Trabajo obtenido.', $datos, $version);
            break;
        case 'listarTrabajos':
            if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
                trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
            }
            $datos = trabajoLaboratorioListar($mysqli, $codUsuario, $entrada);
            $datos['contexto_usuario'] = $contextoUsuario;
            $datos = array_merge($datos, $contextoUsuario);
            $respuesta = trabajoLaboratorioRespuesta(true, 'trabajos_listados', 'Trabajos listados.', $datos, null);
            break;
        case 'obtenerResumen':
            if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
                trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
            }
            $resumen = trabajoLaboratorioResumen($mysqli, $codUsuario, $entrada);
            $datos = array_merge($resumen, array('resumen' => $resumen, 'contexto_usuario' => $contextoUsuario), $contextoUsuario);
            $respuesta = trabajoLaboratorioRespuesta(true, 'resumen_obtenido', 'Resumen obtenido.', $datos, null);
            break;
        case 'obtenerCatalogos':
            if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
                trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
            }
            $catalogos = trabajoLaboratorioCatalogos($mysqli, $codUsuario);
            $datos = array_merge($catalogos, array('catalogos' => $catalogos, 'contexto_usuario' => $contextoUsuario), $contextoUsuario);
            $respuesta = trabajoLaboratorioRespuesta(true, 'catalogos_obtenidos', 'Catalogos obtenidos.', $datos, null);
            break;
        case 'descargarMedia':
            if (!trabajoLaboratorioEstructuraDisponible($mysqli)) {
                trabajoLaboratorioLanzar('estructura_laboratorio_no_instalada', 'El modulo de laboratorio todavia no esta instalado.');
            }
            $media = trabajoLaboratorioDescargarMedia(
                $mysqli,
                $codUsuario,
                isset($entrada['id_media']) ? $entrada['id_media'] : 0,
                !empty($entrada['miniatura']) && trabajoLaboratorioEntero($entrada['miniatura']) === 1
            );
            $respuesta = trabajoLaboratorioRespuesta(
                true, 'media_obtenida', 'Evidencia protegida obtenida.', array('media' => $media), null
            );
            break;
        default:
            trabajoLaboratorioLanzar('accion_no_reconocida', 'La accion solicitada no existe.');
    }

    $mysqli->close();
    trabajoLaboratorioHttpResponder($respuesta, 200);
} catch (TrabajoLaboratorioExcepcion $e) {
    $esAutorizacion = strpos($e->codigoOperacion, 'no_autoriz') !== false
        || in_array($e->codigoOperacion, array('accion_no_permitida', 'local_no_autorizado'), true);
    trabajoLaboratorioHttpResponder(
        trabajoLaboratorioRespuesta(false, $e->codigoOperacion, $e->getMessage(), $e->datosOperacion, null),
        $esAutorizacion ? 403 : 200
    );
} catch (Exception $e) {
    error_log('[TrabajoLaboratorio] error controlado codigo='.get_class($e));
    trabajoLaboratorioHttpResponder(
        trabajoLaboratorioRespuesta(false, 'error_interno', 'No se pudo completar la operacion.'),
        500
    );
} catch (Throwable $e) {
    error_log('[TrabajoLaboratorio] error fatal tipo='.get_class($e));
    trabajoLaboratorioHttpResponder(
        trabajoLaboratorioRespuesta(false, 'error_interno', 'No se pudo completar la operacion.'),
        500
    );
}

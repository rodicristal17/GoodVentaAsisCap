<?php

function seguimientoProgramadoTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '') {
        return false;
    }
    if (isset($cache[$tabla])) {
        return $cache[$tabla];
    }

    $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $cache[$tabla] = false;
        return false;
    }
    $stmt->bind_param('s', $tabla);
    $stmt->execute();
    $cache[$tabla] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$tabla];
}

function seguimientoProgramadoColumnaExiste($mysqli, $tabla, $columna)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    $columna = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$columna);
    $clave = $tabla.'.'.$columna;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }

    $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $cache[$clave] = false;
        return false;
    }
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $cache[$clave] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $cache[$clave];
}

function seguimientoProgramadoEstructuraDisponible($mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $disponible = seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_plantilla')
        && seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_programado');
    if ($disponible) {
        $columnasPlantilla = array('id_plantilla','nombre','categoria','mensaje','orden','estado','cod_usuarioFK_create','fecha_creacion','cod_usuarioFK_edit','fecha_edit');
        $columnasSeguimiento = array('id_seguimiento','cod_interConsultaFK','id_plantillaFK','motivo','mensaje','fecha_programada','cod_responsableFK','estado','resultado','fecha_cierre','id_seguimiento_origenFK','token_solicitud','cod_usuarioFK_create','cod_usuarioFK_update','fecha_creacion','fecha_actualizacion');
        foreach ($columnasPlantilla as $columnaPlantilla) {
            if (!seguimientoProgramadoColumnaExiste($mysqli, 'interconsulta_seguimiento_plantilla', $columnaPlantilla)) {
                $disponible = false;
                break;
            }
        }
        if ($disponible) {
            foreach ($columnasSeguimiento as $columnaSeguimiento) {
                if (!seguimientoProgramadoColumnaExiste($mysqli, 'interconsulta_seguimiento_programado', $columnaSeguimiento)) {
                    $disponible = false;
                    break;
                }
            }
        }
    }
    if ($cerrar) {
        $mysqli->close();
    }
    return $disponible;
}

function seguimientoProgramadoRespuestaCitadaDisponible($mysqli = null)
{
    $cerrar = false;
    if (!$mysqli) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $disponible = seguimientoProgramadoColumnaExiste($mysqli, 'mensaje', 'cod_mensaje_respuestaFK');
    if ($cerrar) {
        $mysqli->close();
    }
    return $disponible;
}

function seguimientoProgramadoFilaUtf8($fila)
{
    $salida = array();
    foreach ((array)$fila as $clave => $valor) {
        if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
            $salida[$clave] = mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
        } else {
            $salida[$clave] = $valor;
        }
    }
    return $salida;
}

function seguimientoProgramadoLimpiarTexto($texto, $limite, $preservarSaltos = false)
{
    $texto = html_entity_decode(strip_tags((string)$texto), ENT_QUOTES | ENT_HTML5, 'ISO-8859-1');
    $texto = str_replace(array("\r\n", "\r"), "\n", $texto);
    $texto = str_replace("\xC2\xA0", ' ', $texto);
    if ($preservarSaltos) {
        $texto = preg_replace('/[ \t]+/', ' ', $texto);
        $texto = preg_replace('/\n{3,}/', "\n\n", $texto);
    } else {
        $texto = preg_replace('/\s+/', ' ', $texto);
    }
    $texto = trim($texto);
    return $texto;
}

function seguimientoProgramadoTextoExcede($texto, $limite)
{
    return function_exists('mb_strlen')
        ? mb_strlen((string)$texto, 'ISO-8859-1') > intval($limite)
        : strlen((string)$texto) > intval($limite);
}

function seguimientoProgramadoFechaValida($fecha, $exigirFutura = true)
{
    $fecha = trim(str_replace('T', ' ', (string)$fecha));
    if (strlen($fecha) === 16) {
        $fecha .= ':00';
    }
    $objeto = DateTime::createFromFormat('Y-m-d H:i:s', $fecha);
    $errores = DateTime::getLastErrors();
    if (!$objeto || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
        return array('ok' => false, 'mensaje' => 'La fecha y hora del seguimiento no son validas.');
    }
    if ($exigirFutura && $objeto <= new DateTime()) {
        return array('ok' => false, 'mensaje' => 'El seguimiento debe programarse para una fecha y hora futuras.');
    }
    return array('ok' => true, 'fecha' => $objeto->format('Y-m-d H:i:s'));
}

function seguimientoProgramadoCondicionAccesoLocalSql($codUsuario, $alias = 'ic', $mysqli = null)
{
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    if ($alias === '') {
        $alias = 'ic';
    }
    $codUsuario = intval($codUsuario);
    if ($codUsuario <= 0) {
        return '1=0';
    }
    if (function_exists('interconsultaAccesoCondicionLocalSql')) {
        return interconsultaAccesoCondicionLocalSql($codUsuario, $alias, $mysqli);
    }

    // Compatibilidad para consumidores antiguos que incluyan solo este helper.
    // El permiso del Centro de Facturas nunca amplia el alcance de Hilos.
    $puedeTodos = function_exists('controldeaccesoacasas')
        && controldeaccesoacasas($codUsuario, 'CAMBIARLOCAL', " u.accion='SI' ") == 1;
    if ($puedeTodos) {
        return '1=1';
    }

    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $codLocal = 0;
    $stmt = $mysqli->prepare("SELECT cod_localFK FROM usuario WHERE cod_usuario=? AND estado='Activo' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $codUsuario);
        if ($stmt->execute()) {
            $fila = $stmt->get_result()->fetch_assoc();
            $codLocal = $fila ? intval($fila['cod_localFK']) : 0;
        }
        $stmt->close();
    }
    if ($cerrar) {
        $mysqli->close();
    }
    if ($codLocal <= 0) {
        return '1=0';
    }

    return "(".$alias.".cod_localFK=".$codLocal."
        OR EXISTS(
            SELECT 1
            FROM interconsulta_paciente_venta isp_ipv
            INNER JOIN venta isp_vt ON isp_vt.cod_venta=isp_ipv.cod_ventaFK
            WHERE isp_ipv.cod_interConsultaFK=".$alias.".cod_interConsulta
              AND isp_ipv.estado='activo'
              AND isp_vt.cod_local=".$codLocal."
            LIMIT 1
        )
        OR EXISTS(
            SELECT 1
            FROM venta isp_vtd
            WHERE isp_vtd.cod_venta=".$alias.".cod_ventaFK
              AND isp_vtd.cod_local=".$codLocal."
            LIMIT 1
        )
        OR ((IFNULL(".$alias.".cod_localFK,0)=0)
            AND EXISTS(
                SELECT 1
                FROM usuario isp_uc
                WHERE isp_uc.cod_usuario=".$alias.".cod_usuarioFK_create
                  AND isp_uc.cod_localFK=".$codLocal."
                LIMIT 1
            )))";
}

function seguimientoProgramadoPuedeAccederHilo($codInterConsulta, $codUsuario, $exigirActivo = false, $mysqli = null)
{
    $codInterConsulta = intval($codInterConsulta);
    $codUsuario = intval($codUsuario);
    if ($codInterConsulta <= 0 || $codUsuario <= 0) {
        return false;
    }
    if (function_exists('interconsultaAccesoUsuarioPuedeAccederHilo')) {
        return interconsultaAccesoUsuarioPuedeAccederHilo($codInterConsulta, $codUsuario, $exigirActivo, $mysqli);
    }

    // Compatibilidad para consumidores antiguos que incluyan solo este helper.
    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $condicionEstado = $exigirActivo ? " AND ic.estado<>'inactivo'" : "";
    $condicionLocal = seguimientoProgramadoCondicionAccesoLocalSql($codUsuario, 'ic', $mysqli);
    $sql = "SELECT 1 FROM interconsulta ic WHERE ic.cod_interConsulta=?".$condicionEstado." AND ".$condicionLocal." LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        if ($cerrar) {
            $mysqli->close();
        }
        return false;
    }
    $stmt->bind_param('i', $codInterConsulta);
    $permitido = $stmt->execute() && $stmt->get_result()->num_rows > 0;
    $stmt->close();
    if ($cerrar) {
        $mysqli->close();
    }
    return $permitido;
}

function seguimientoProgramadoPuedeAdministrarPlantillas($codUsuario)
{
    if (!function_exists('controldeaccesoacasas')) {
        return false;
    }
    return controldeaccesoacasas(intval($codUsuario), 'ADMINPLANTILLASSEGUIMIENTOHILOS', " u.accion='SI' ") == 1;
}

function seguimientoProgramadoObtenerPlantillas($incluirInactivas = false)
{
    $mysqli = conectar_al_servidor();
    if (!seguimientoProgramadoEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array();
    }
    $sql = "SELECT id_plantilla, nombre, categoria, mensaje, orden, estado,
                   cod_usuarioFK_create, fecha_creacion, cod_usuarioFK_edit, fecha_edit
            FROM interconsulta_seguimiento_plantilla";
    if (!$incluirInactivas) {
        $sql .= " WHERE estado='activo'";
    }
    $sql .= " ORDER BY estado='activo' DESC, orden ASC, nombre ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        $mysqli->close();
        return array();
    }
    $registros = array();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $registros[] = seguimientoProgramadoFilaUtf8($fila);
    }
    $stmt->close();
    $mysqli->close();
    return $registros;
}

function seguimientoProgramadoObtenerResponsables($codInterConsulta, $codUsuarioActual)
{
    $codInterConsulta = intval($codInterConsulta);
    $codUsuarioActual = intval($codUsuarioActual);
    if ($codInterConsulta <= 0 || $codUsuarioActual <= 0) {
        return array();
    }
    $mysqli = conectar_al_servidor();
    if (!seguimientoProgramadoPuedeAccederHilo($codInterConsulta, $codUsuarioActual, true, $mysqli)) {
        $mysqli->close();
        return array();
    }
    $condicionResponsable = "";
    if (seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_programado')) {
        $condicionResponsable = "
                OR EXISTS(
                    SELECT 1
                    FROM interconsulta_seguimiento_programado sp
                    WHERE sp.cod_interConsultaFK=ic.cod_interConsulta
                      AND sp.cod_responsableFK=u.cod_usuario
                      AND sp.estado='programado'
                )";
    }
    $sql = "SELECT DISTINCT u.cod_usuario, p.nombre_persona, IFNULL(u.url,'') AS url_usuario
            FROM usuario u
            INNER JOIN persona p ON p.cod_persona=u.cod_usuario
            INNER JOIN interconsulta ic ON ic.cod_interConsulta=?
            WHERE u.estado='Activo'
              AND (
                u.cod_usuario=?
                OR u.cod_usuario=ic.cod_usuarioFK_create
                OR EXISTS(
                    SELECT 1
                    FROM menciones mc
                    INNER JOIN mensaje mj ON mj.cod_mensaje=mc.cod_mensajeFK
                    WHERE mj.cod_interConsultaFK=ic.cod_interConsulta
                      AND mc.cod_usuarioFK=u.cod_usuario
                      AND mc.estado='activo'
                )".$condicionResponsable."
              )
            ORDER BY p.nombre_persona ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $mysqli->close();
        return array();
    }
    $stmt->bind_param('ii', $codInterConsulta, $codUsuarioActual);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        return array();
    }
    $registros = array();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        if (seguimientoProgramadoPuedeAccederHilo($codInterConsulta, intval($fila['cod_usuario']), true, $mysqli)) {
            $registros[] = seguimientoProgramadoFilaUtf8($fila);
        }
    }
    $stmt->close();
    $mysqli->close();
    return $registros;
}

function seguimientoProgramadoResponsablePermitido($codInterConsulta, $codResponsable, $codUsuarioActual)
{
    foreach (seguimientoProgramadoObtenerResponsables($codInterConsulta, $codUsuarioActual) as $responsable) {
        if (intval($responsable['cod_usuario']) === intval($codResponsable)) {
            return true;
        }
    }
    return false;
}

function seguimientoProgramadoGuardarPlantilla($datos, $codUsuario)
{
    if (!seguimientoProgramadoPuedeAdministrarPlantillas($codUsuario)) {
        return array('ok' => false, 'mensaje' => 'No tiene permiso para administrar plantillas de seguimiento.');
    }
    $idPlantilla = isset($datos['id_plantilla']) ? intval($datos['id_plantilla']) : 0;
    $nombre = seguimientoProgramadoLimpiarTexto(isset($datos['nombre']) ? $datos['nombre'] : '', 120);
    $categoria = seguimientoProgramadoLimpiarTexto(isset($datos['categoria']) ? $datos['categoria'] : '', 80);
    $mensaje = seguimientoProgramadoLimpiarTexto(isset($datos['mensaje']) ? $datos['mensaje'] : '', 750, true);
    $orden = isset($datos['orden']) ? intval($datos['orden']) : 0;
    $estado = isset($datos['estado']) && $datos['estado'] === 'inactivo' ? 'inactivo' : 'activo';
    if ($nombre === '' || $mensaje === '') {
        return array('ok' => false, 'mensaje' => 'La plantilla necesita un nombre y un mensaje sugerido.');
    }
    if (seguimientoProgramadoTextoExcede($nombre, 120)
        || seguimientoProgramadoTextoExcede($categoria, 80)
        || seguimientoProgramadoTextoExcede($mensaje, 750)) {
        return array('ok' => false, 'mensaje' => 'La plantilla supera la longitud permitida. Revise nombre, categoria y mensaje.');
    }

    $mysqli = conectar_al_servidor();
    if (!seguimientoProgramadoEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La estructura de seguimientos programados no esta instalada.');
    }
    $sqlDuplicado = "SELECT id_plantilla FROM interconsulta_seguimiento_plantilla
                     WHERE LOWER(TRIM(nombre))=LOWER(TRIM(?)) AND id_plantilla<>? LIMIT 1";
    $stmt = $mysqli->prepare($sqlDuplicado);
    if (!$stmt) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'No se pudo validar la plantilla. Verifique la migracion instalada.');
    }
    $stmt->bind_param('si', $nombre, $idPlantilla);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'Ya existe una plantilla con ese nombre.');
    }
    $stmt->close();

    if ($idPlantilla > 0) {
        $stmtExiste = $mysqli->prepare("SELECT id_plantilla FROM interconsulta_seguimiento_plantilla WHERE id_plantilla=? LIMIT 1");
        if (!$stmtExiste) {
            $mysqli->close();
            return array('ok' => false, 'mensaje' => 'No se pudo validar la plantilla seleccionada.');
        }
        $stmtExiste->bind_param('i', $idPlantilla);
        $stmtExiste->execute();
        $existePlantilla = $stmtExiste->get_result()->num_rows > 0;
        $stmtExiste->close();
        if (!$existePlantilla) {
            $mysqli->close();
            return array('ok' => false, 'mensaje' => 'La plantilla seleccionada ya no existe.');
        }
        $sql = "UPDATE interconsulta_seguimiento_plantilla
                SET nombre=?, categoria=?, mensaje=?, orden=?, estado=?, cod_usuarioFK_edit=?, fecha_edit=NOW()
                WHERE id_plantilla=?";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sssisii', $nombre, $categoria, $mensaje, $orden, $estado, $codUsuario, $idPlantilla);
        }
    } else {
        $sql = "INSERT INTO interconsulta_seguimiento_plantilla
                (nombre,categoria,mensaje,orden,estado,cod_usuarioFK_create)
                VALUES (?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sssisi', $nombre, $categoria, $mensaje, $orden, $estado, $codUsuario);
        }
    }
    if (!$stmt || !$stmt->execute()) {
        if ($stmt) {
            $stmt->close();
        }
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'No se pudo guardar la plantilla. Intente nuevamente.');
    }
    if ($idPlantilla <= 0) {
        $idPlantilla = intval($stmt->insert_id);
    }
    $stmt->close();
    $mysqli->close();
    return array('ok' => true, 'id_plantilla' => $idPlantilla, 'mensaje' => 'Plantilla guardada.');
}

function seguimientoProgramadoCambiarEstadoPlantilla($idPlantilla, $estado, $codUsuario)
{
    if (!seguimientoProgramadoPuedeAdministrarPlantillas($codUsuario)) {
        return array('ok' => false, 'mensaje' => 'No tiene permiso para administrar plantillas de seguimiento.');
    }
    $idPlantilla = intval($idPlantilla);
    $estado = $estado === 'activo' ? 'activo' : 'inactivo';
    if ($idPlantilla <= 0) {
        return array('ok' => false, 'mensaje' => 'No se recibio una plantilla valida.');
    }
    $mysqli = conectar_al_servidor();
    if (!seguimientoProgramadoEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La estructura de seguimientos programados no esta instalada.');
    }
    $stmtExiste = $mysqli->prepare("SELECT id_plantilla FROM interconsulta_seguimiento_plantilla WHERE id_plantilla=? LIMIT 1");
    if (!$stmtExiste) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'No se pudo validar la plantilla seleccionada.');
    }
    $stmtExiste->bind_param('i', $idPlantilla);
    $stmtExiste->execute();
    $existePlantilla = $stmtExiste->get_result()->num_rows > 0;
    $stmtExiste->close();
    if (!$existePlantilla) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La plantilla seleccionada ya no existe.');
    }
    $sql = "UPDATE interconsulta_seguimiento_plantilla
            SET estado=?, cod_usuarioFK_edit=?, fecha_edit=NOW()
            WHERE id_plantilla=?";
    $stmt = $mysqli->prepare($sql);
    $ok = false;
    if ($stmt) {
        $stmt->bind_param('sii', $estado, $codUsuario, $idPlantilla);
        $ok = $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();
    return $ok
        ? array('ok' => true, 'mensaje' => $estado === 'activo' ? 'Plantilla activada.' : 'Plantilla inactivada.')
        : array('ok' => false, 'mensaje' => 'No se pudo cambiar el estado de la plantilla.');
}

function seguimientoProgramadoObtenerPlantillaActiva($mysqli, $idPlantilla)
{
    $idPlantilla = intval($idPlantilla);
    if ($idPlantilla <= 0) {
        return null;
    }
    $sql = "SELECT id_plantilla,nombre,mensaje FROM interconsulta_seguimiento_plantilla
            WHERE id_plantilla=? AND estado='activo' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $idPlantilla);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $fila ? $fila : null;
}

function seguimientoProgramadoCrear($datos, $codUsuario)
{
    $codInterConsulta = isset($datos['cod_interConsulta']) ? intval($datos['cod_interConsulta']) : 0;
    $idPlantilla = isset($datos['id_plantilla']) ? intval($datos['id_plantilla']) : 0;
    $codResponsable = isset($datos['cod_responsable']) ? intval($datos['cod_responsable']) : intval($codUsuario);
    $idOrigen = isset($datos['id_seguimiento_origen']) ? intval($datos['id_seguimiento_origen']) : 0;
    $motivo = seguimientoProgramadoLimpiarTexto(isset($datos['motivo']) ? $datos['motivo'] : '', 120);
    $mensaje = seguimientoProgramadoLimpiarTexto(isset($datos['mensaje']) ? $datos['mensaje'] : '', 750, true);
    $token = isset($datos['token_solicitud']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$datos['token_solicitud']) : '';
    if (strlen($token) > 64) {
        $token = substr($token, 0, 64);
    }
    if ($token === '') {
        $token = hash('sha256', intval($codUsuario).'|'.$codInterConsulta.'|'.microtime(true).'|'.mt_rand());
    }
    $fechaValidada = seguimientoProgramadoFechaValida(isset($datos['fecha_programada']) ? $datos['fecha_programada'] : '', true);
    if (!$fechaValidada['ok']) {
        return $fechaValidada;
    }
    if (seguimientoProgramadoTextoExcede($motivo, 120) || seguimientoProgramadoTextoExcede($mensaje, 750)) {
        return array('ok' => false, 'mensaje' => 'El motivo o la nota superan la longitud permitida.');
    }
    if ($codInterConsulta <= 0 || !seguimientoProgramadoPuedeAccederHilo($codInterConsulta, $codUsuario, true)) {
        return array('ok' => false, 'mensaje' => 'No tiene acceso al hilo indicado.');
    }
    if (!seguimientoProgramadoResponsablePermitido($codInterConsulta, $codResponsable, $codUsuario)) {
        return array('ok' => false, 'mensaje' => 'El responsable debe ser un participante activo del hilo.');
    }
    if ($mensaje === '') {
        return array('ok' => false, 'mensaje' => 'Ingrese el mensaje o la nota del seguimiento.');
    }

    $mysqli = conectar_al_servidor();
    if (!seguimientoProgramadoEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La estructura de seguimientos programados no esta instalada.');
    }
    $sqlToken = "SELECT id_seguimiento,cod_interConsultaFK,cod_usuarioFK_create
                 FROM interconsulta_seguimiento_programado WHERE token_solicitud=? LIMIT 1";
    $stmtToken = $mysqli->prepare($sqlToken);
    if (!$stmtToken) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'No se pudo validar la solicitud. Verifique la migracion instalada.');
    }
    $stmtToken->bind_param('s', $token);
    $stmtToken->execute();
    $existente = $stmtToken->get_result()->fetch_assoc();
    $stmtToken->close();
    if ($existente) {
        $mismoPedido = intval($existente['cod_interConsultaFK']) === $codInterConsulta
            && intval($existente['cod_usuarioFK_create']) === intval($codUsuario);
        $mysqli->close();
        return $mismoPedido
            ? array('ok' => true, 'id_seguimiento' => intval($existente['id_seguimiento']), 'repetido' => true, 'mensaje' => 'El seguimiento ya estaba programado.')
            : array('ok' => false, 'mensaje' => 'No se pudo validar la solicitud de seguimiento.');
    }

    $plantilla = seguimientoProgramadoObtenerPlantillaActiva($mysqli, $idPlantilla);
    if ($idPlantilla > 0 && !$plantilla) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La plantilla seleccionada ya no esta activa.');
    }
    if ($plantilla && $motivo === '') {
        $motivo = $plantilla['nombre'];
    }
    if ($motivo === '') {
        $motivo = 'Seguimiento personalizado';
    }

    $mysqli->begin_transaction();
    $errorDuplicado = false;
    try {
        $stmtHilo = $mysqli->prepare("SELECT estado FROM interconsulta WHERE cod_interConsulta=? FOR UPDATE");
        if (!$stmtHilo) {
            throw new Exception('No se pudo validar el estado del hilo.');
        }
        $stmtHilo->bind_param('i', $codInterConsulta);
        if (!$stmtHilo->execute()) {
            $stmtHilo->close();
            throw new Exception('No se pudo validar el estado del hilo.');
        }
        $hilo = $stmtHilo->get_result()->fetch_assoc();
        $stmtHilo->close();
        if (!$hilo || $hilo['estado'] === 'inactivo') {
            throw new Exception('No se puede programar un seguimiento en un hilo inactivo.');
        }

        if ($idOrigen > 0) {
            $sqlOrigen = "SELECT id_seguimiento,cod_interConsultaFK,cod_responsableFK,cod_usuarioFK_create,estado
                          FROM interconsulta_seguimiento_programado
                          WHERE id_seguimiento=? FOR UPDATE";
            $stmtOrigen = $mysqli->prepare($sqlOrigen);
            $stmtOrigen->bind_param('i', $idOrigen);
            $stmtOrigen->execute();
            $origen = $stmtOrigen->get_result()->fetch_assoc();
            $stmtOrigen->close();
            if (!$origen || intval($origen['cod_interConsultaFK']) !== $codInterConsulta || $origen['estado'] !== 'programado') {
                throw new Exception('El seguimiento original ya no puede reprogramarse.');
            }
            $puedeReprogramar = intval($origen['cod_responsableFK']) === intval($codUsuario)
                || intval($origen['cod_usuarioFK_create']) === intval($codUsuario);
            if (!$puedeReprogramar) {
                throw new Exception('Solo el responsable o el creador puede reprogramar este seguimiento.');
            }
            $resultadoOrigen = 'Reprogramado para '.$fechaValidada['fecha'].'.';
            $sqlCerrar = "UPDATE interconsulta_seguimiento_programado
                          SET estado='reprogramado',resultado=?,fecha_cierre=NOW(),
                              cod_usuarioFK_update=?,fecha_actualizacion=NOW()
                          WHERE id_seguimiento=? AND estado='programado'";
            $stmtCerrar = $mysqli->prepare($sqlCerrar);
            $stmtCerrar->bind_param('sii', $resultadoOrigen, $codUsuario, $idOrigen);
            if (!$stmtCerrar->execute() || $stmtCerrar->affected_rows !== 1) {
                $stmtCerrar->close();
                throw new Exception('No se pudo conservar la reprogramacion anterior.');
            }
            $stmtCerrar->close();
        }

        $idPlantillaInsert = $plantilla ? intval($idPlantilla) : null;
        $idOrigenInsert = $idOrigen > 0 ? $idOrigen : null;
        $sql = "INSERT INTO interconsulta_seguimiento_programado
                (cod_interConsultaFK,id_plantillaFK,motivo,mensaje,fecha_programada,cod_responsableFK,
                 estado,id_seguimiento_origenFK,token_solicitud,cod_usuarioFK_create)
                VALUES (?,?,?,?,?,?,'programado',?,?,?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception('No se pudo preparar el seguimiento. Verifique la migracion instalada.');
        }
        $stmt->bind_param(
            'iisssiisi',
            $codInterConsulta,
            $idPlantillaInsert,
            $motivo,
            $mensaje,
            $fechaValidada['fecha'],
            $codResponsable,
            $idOrigenInsert,
            $token,
            $codUsuario
        );
        if (!$stmt->execute()) {
            $errorDuplicado = intval($stmt->errno) === 1062;
            $stmt->close();
            throw new Exception('No se pudo guardar el seguimiento. Intente nuevamente.');
        }
        $idSeguimiento = intval($stmt->insert_id);
        $stmt->close();
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'id_seguimiento' => $idSeguimiento, 'mensaje' => $idOrigen > 0 ? 'Seguimiento reprogramado.' : 'Seguimiento programado.');
    } catch (Exception $error) {
        $mysqli->rollback();
        if ($errorDuplicado) {
            $stmtRecuperar = $mysqli->prepare($sqlToken);
            if ($stmtRecuperar) {
                $stmtRecuperar->bind_param('s', $token);
                $stmtRecuperar->execute();
                $existente = $stmtRecuperar->get_result()->fetch_assoc();
                $stmtRecuperar->close();
                if ($existente
                    && intval($existente['cod_interConsultaFK']) === $codInterConsulta
                    && intval($existente['cod_usuarioFK_create']) === intval($codUsuario)) {
                    $mysqli->close();
                    return array('ok' => true, 'id_seguimiento' => intval($existente['id_seguimiento']), 'repetido' => true, 'mensaje' => 'El seguimiento ya estaba programado.');
                }
            }
        }
        $mysqli->close();
        return array('ok' => false, 'mensaje' => $error->getMessage());
    }
}

function seguimientoProgramadoCompletar($idSeguimiento, $resultado, $codUsuario)
{
    $idSeguimiento = intval($idSeguimiento);
    $resultado = seguimientoProgramadoLimpiarTexto($resultado, 750, true);
    if ($idSeguimiento <= 0 || $resultado === '') {
        return array('ok' => false, 'mensaje' => 'Ingrese el resultado de la gestion realizada.');
    }
    if (seguimientoProgramadoTextoExcede($resultado, 750)) {
        return array('ok' => false, 'mensaje' => 'El resultado supera el limite de 750 caracteres.');
    }
    $mysqli = conectar_al_servidor();
    if (!seguimientoProgramadoEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La estructura de seguimientos programados no esta instalada.');
    }
    $mysqli->begin_transaction();
    try {
        $sql = "SELECT id_seguimiento,cod_interConsultaFK,cod_responsableFK,cod_usuarioFK_create,estado
                FROM interconsulta_seguimiento_programado WHERE id_seguimiento=? FOR UPDATE";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $idSeguimiento);
        $stmt->execute();
        $seguimiento = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$seguimiento || $seguimiento['estado'] !== 'programado') {
            throw new Exception('El seguimiento ya fue atendido o reprogramado.');
        }
        if (!seguimientoProgramadoPuedeAccederHilo($seguimiento['cod_interConsultaFK'], $codUsuario)) {
            throw new Exception('No tiene acceso al hilo indicado.');
        }
        $puedeCompletar = intval($seguimiento['cod_responsableFK']) === intval($codUsuario)
            || intval($seguimiento['cod_usuarioFK_create']) === intval($codUsuario);
        if (!$puedeCompletar) {
            throw new Exception('Solo el responsable o el creador puede completar el seguimiento.');
        }
        $sqlUpdate = "UPDATE interconsulta_seguimiento_programado
                      SET estado='completado',resultado=?,fecha_cierre=NOW(),
                          cod_usuarioFK_update=?,fecha_actualizacion=NOW()
                      WHERE id_seguimiento=? AND estado='programado'";
        $stmt = $mysqli->prepare($sqlUpdate);
        $stmt->bind_param('sii', $resultado, $codUsuario, $idSeguimiento);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            $stmt->close();
            throw new Exception('No se pudo completar el seguimiento.');
        }
        $stmt->close();
        $mysqli->commit();
        $mysqli->close();
        return array('ok' => true, 'mensaje' => 'Seguimiento completado.');
    } catch (Exception $error) {
        $mysqli->rollback();
        $mysqli->close();
        return array('ok' => false, 'mensaje' => $error->getMessage());
    }
}

function seguimientoProgramadoObtenerSeguimientosHilo($codInterConsulta, $limite = 40)
{
    $codInterConsulta = intval($codInterConsulta);
    $limite = max(1, min(intval($limite), 80));
    $mysqli = conectar_al_servidor();
    if ($codInterConsulta <= 0 || !seguimientoProgramadoEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array();
    }
    $sql = "SELECT * FROM (
                SELECT sp.*,
                       pr.nombre_persona AS nombre_responsable,
                       pc.nombre_persona AS nombre_creador,
                       pu.nombre_persona AS nombre_actualizador,
                       IFNULL(ur.url,'') AS url_responsable
                FROM interconsulta_seguimiento_programado sp
                LEFT JOIN persona pr ON pr.cod_persona=sp.cod_responsableFK
                LEFT JOIN persona pc ON pc.cod_persona=sp.cod_usuarioFK_create
                LEFT JOIN persona pu ON pu.cod_persona=sp.cod_usuarioFK_update
                LEFT JOIN usuario ur ON ur.cod_usuario=sp.cod_responsableFK
                WHERE sp.cod_interConsultaFK=?
                ORDER BY sp.fecha_creacion DESC,sp.id_seguimiento DESC
                LIMIT ".$limite."
            ) recientes
            ORDER BY fecha_creacion ASC,id_seguimiento ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $codInterConsulta);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        return array();
    }
    $registros = array();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $registros[] = seguimientoProgramadoFilaUtf8($fila);
    }
    $stmt->close();
    $mysqli->close();
    return $registros;
}

function seguimientoProgramadoObtenerActivosPorHilos($codigosHilo)
{
    $codigos = array();
    foreach ((array)$codigosHilo as $codigo) {
        $codigo = intval($codigo);
        if ($codigo > 0) {
            $codigos[$codigo] = $codigo;
        }
    }
    if (count($codigos) === 0) {
        return array();
    }
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        if ($mysqli) { $mysqli->close(); }
        return array();
    }
    $lista = implode(',', $codigos);
    $sql = "SELECT sp.*,pr.nombre_persona AS nombre_responsable,pc.nombre_persona AS nombre_creador,
                   IFNULL(ur.url,'') AS url_responsable
            FROM interconsulta_seguimiento_programado sp
            LEFT JOIN persona pr ON pr.cod_persona=sp.cod_responsableFK
            LEFT JOIN persona pc ON pc.cod_persona=sp.cod_usuarioFK_create
            LEFT JOIN usuario ur ON ur.cod_usuario=sp.cod_responsableFK
            WHERE sp.estado='programado' AND sp.cod_interConsultaFK IN (".$lista.")
            ORDER BY sp.cod_interConsultaFK ASC,sp.fecha_programada ASC,sp.id_seguimiento ASC";
    $result = $mysqli->query($sql);
    $mapa = array();
    if ($result) {
        while ($fila = $result->fetch_assoc()) {
            $codigo = intval($fila['cod_interConsultaFK']);
            if (!isset($mapa[$codigo])) {
                $mapa[$codigo] = seguimientoProgramadoFilaUtf8($fila);
            }
        }
    }
    $mysqli->close();
    return $mapa;
}

function seguimientoProgramadoEstadoVisual($seguimiento)
{
    $estado = isset($seguimiento['estado']) ? (string)$seguimiento['estado'] : 'programado';
    if ($estado !== 'programado') {
        return $estado;
    }
    $fechaProgramada = isset($seguimiento['fecha_programada']) ? (string)$seguimiento['fecha_programada'] : '';
    $fecha = substr($fechaProgramada, 0, 10);
    $hoy = date('Y-m-d');
    if ($fechaProgramada !== '' && strtotime($fechaProgramada) < time()) {
        return 'vencido';
    }
    if ($fecha === $hoy) {
        return 'para_hoy';
    }
    return 'programado';
}

function seguimientoProgramadoEtiquetaEstado($estadoVisual)
{
    $etiquetas = array(
        'programado' => 'Programado',
        'para_hoy' => 'Para hoy',
        'vencido' => 'Vencido',
        'completado' => 'Completado',
        'reprogramado' => 'Reprogramado',
        'cancelado' => 'Cancelado'
    );
    return isset($etiquetas[$estadoVisual]) ? $etiquetas[$estadoVisual] : ucfirst(str_replace('_', ' ', $estadoVisual));
}

function seguimientoProgramadoObtenerResumenAlertas($codUsuario)
{
    $codUsuario = intval($codUsuario);
    $resumen = array('hoy' => 0, 'vencidos' => 0, 'proximos' => 0, 'total_pendientes' => 0, 'items' => array());
    if ($codUsuario <= 0) {
        return $resumen;
    }
    $mysqli = conectar_al_servidor();
    if (!$mysqli || $mysqli->connect_errno) {
        if ($mysqli) { $mysqli->close(); }
        return $resumen;
    }
    $condicionAccesoLocal = seguimientoProgramadoCondicionAccesoLocalSql($codUsuario, 'ic', $mysqli);
    $sql = "SELECT
              SUM(sp.fecha_programada>=NOW() AND sp.fecha_programada<CURDATE() + INTERVAL 1 DAY) AS hoy,
              SUM(sp.fecha_programada<NOW()) AS vencidos,
              SUM(sp.fecha_programada>=CURDATE() + INTERVAL 1 DAY) AS proximos,
              COUNT(*) AS total_pendientes
            FROM interconsulta_seguimiento_programado sp
            INNER JOIN interconsulta ic ON ic.cod_interConsulta=sp.cod_interConsultaFK
            WHERE sp.cod_responsableFK=? AND sp.estado='programado' AND ic.estado<>'inactivo'
              AND ".$condicionAccesoLocal;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $mysqli->close();
        return $resumen;
    }
    $stmt->bind_param('i', $codUsuario);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        return $resumen;
    }
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) {
        $resumen['hoy'] = intval($fila['hoy']);
        $resumen['vencidos'] = intval($fila['vencidos']);
        $resumen['proximos'] = intval($fila['proximos']);
        $resumen['total_pendientes'] = intval($fila['total_pendientes']);
    }

    $sqlItems = "SELECT sp.id_seguimiento,sp.cod_interConsultaFK,sp.motivo,sp.fecha_programada,ic.asunto
                 FROM interconsulta_seguimiento_programado sp
                 INNER JOIN interconsulta ic ON ic.cod_interConsulta=sp.cod_interConsultaFK
                 WHERE sp.cod_responsableFK=? AND sp.estado='programado' AND ic.estado<>'inactivo'
                   AND ".$condicionAccesoLocal."
                   AND sp.fecha_programada<CURDATE() + INTERVAL 1 DAY
                 ORDER BY sp.fecha_programada ASC,sp.id_seguimiento ASC LIMIT 8";
    $stmt = $mysqli->prepare($sqlItems);
    if (!$stmt) {
        $mysqli->close();
        return $resumen;
    }
    $stmt->bind_param('i', $codUsuario);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        return $resumen;
    }
    $result = $stmt->get_result();
    while ($item = $result->fetch_assoc()) {
        $resumen['items'][] = seguimientoProgramadoFilaUtf8($item);
    }
    $stmt->close();
    $mysqli->close();
    return $resumen;
}

?>

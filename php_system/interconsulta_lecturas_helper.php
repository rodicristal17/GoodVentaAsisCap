<?php

/**
 * Lecturas y participantes de Hilos.
 *
 * La estructura se instala mediante una migracion controlada. Mientras no se
 * haya aplicado, todas las funciones degradan de forma segura al mecanismo
 * legacy de menciones sin interrumpir el modulo.
 */

function interconsultaLecturasTablaExiste($mysqli, $tabla)
{
    static $cache = array();
    $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tabla);
    if ($tabla === '' || !($mysqli instanceof mysqli)) {
        return false;
    }
    if (array_key_exists($tabla, $cache)) {
        return $cache[$tabla];
    }
    $stmt = $mysqli->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1");
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

function interconsultaLecturasEstructuraDisponible($mysqli = null)
{
    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $disponible = interconsultaLecturasTablaExiste($mysqli, 'interconsulta_lectura_usuario')
        && interconsultaLecturasTablaExiste($mysqli, 'interconsulta_mensaje_lectura');
    if ($cerrar) {
        $mysqli->close();
    }
    return $disponible;
}

function interconsultaLecturasIdsSeguros($ids, $maximo = 100)
{
    $seguros = array();
    foreach ((array)$ids as $id) {
        $id = intval($id);
        if ($id > 0) {
            $seguros[$id] = $id;
        }
        if (count($seguros) >= intval($maximo)) {
            break;
        }
    }
    return array_values($seguros);
}

function interconsultaLecturasIniciales($nombre)
{
    $nombre = trim(preg_replace('/\s+/', ' ', (string)$nombre));
    if ($nombre === '') {
        return 'US';
    }
    $partes = explode(' ', $nombre);
    $primera = function_exists('mb_substr') ? mb_substr($partes[0], 0, 1, 'UTF-8') : substr($partes[0], 0, 1);
    $ultima = count($partes) > 1
        ? (function_exists('mb_substr') ? mb_substr($partes[count($partes) - 1], 0, 1, 'UTF-8') : substr($partes[count($partes) - 1], 0, 1))
        : '';
    return strtoupper($primera.$ultima);
}

/**
 * Participantes actuales: creador del hilo mas las menciones activas del
 * ultimo mensaje vigente. Es la misma regla visible en el detalle del hilo.
 */
function interconsultaParticipantesActualesHilos($idsHilos, $mysqli = null)
{
    $idsHilos = interconsultaLecturasIdsSeguros($idsHilos, 100);
    $salida = array();
    if (count($idsHilos) === 0) {
        return $salida;
    }
    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $lista = implode(',', array_map('intval', $idsHilos));
    $sql = "SELECT participantes.cod_interConsulta,participantes.cod_usuario,
                IFNULL(p.nombre_persona,'Usuario') AS nombre_persona,IFNULL(u.url,'') AS url_usuario,
                IFNULL(u.estado,'') AS estado_usuario
            FROM (
                SELECT ic.cod_interConsulta,ic.cod_usuarioFK_create AS cod_usuario
                FROM interconsulta ic
                WHERE ic.cod_interConsulta IN (".$lista.") AND IFNULL(ic.cod_usuarioFK_create,0)>0
                UNION ALL
                SELECT ic.cod_interConsulta,mn.cod_usuarioFK AS cod_usuario
                FROM interconsulta ic
                INNER JOIN mensaje ultimo ON ultimo.cod_mensaje=(
                    SELECT m2.cod_mensaje FROM mensaje m2
                    WHERE m2.cod_interConsultaFK=ic.cod_interConsulta
                      AND m2.estado='activo' AND m2.fecha_creacion<=NOW()
                    ORDER BY m2.fecha_creacion DESC,m2.cod_mensaje DESC LIMIT 1
                )
                INNER JOIN menciones mn ON mn.cod_mensajeFK=ultimo.cod_mensaje AND mn.estado='activo'
                WHERE ic.cod_interConsulta IN (".$lista.") AND IFNULL(mn.cod_usuarioFK,0)>0
            ) participantes
            LEFT JOIN usuario u ON u.cod_usuario=participantes.cod_usuario
            LEFT JOIN persona p ON p.cod_persona=participantes.cod_usuario
            ORDER BY participantes.cod_interConsulta,p.nombre_persona,participantes.cod_usuario";
    $resultado = $mysqli->query($sql);
    while ($resultado && $fila = $resultado->fetch_assoc()) {
        $codHilo = intval($fila['cod_interConsulta']);
        $codUsuario = intval($fila['cod_usuario']);
        if ($codHilo <= 0 || $codUsuario <= 0) {
            continue;
        }
        if (!isset($salida[$codHilo])) {
            $salida[$codHilo] = array();
        }
        if (isset($salida[$codHilo][$codUsuario])) {
            continue;
        }
        $nombre = (string)$fila['nombre_persona'];
        if (is_string($nombre) && !mb_check_encoding($nombre, 'UTF-8')) {
            $nombre = mb_convert_encoding($nombre, 'UTF-8', 'ISO-8859-1');
        }
        $url = (string)$fila['url_usuario'];
        if (is_string($url) && !mb_check_encoding($url, 'UTF-8')) {
            $url = mb_convert_encoding($url, 'UTF-8', 'ISO-8859-1');
        }
        $salida[$codHilo][$codUsuario] = array(
            'cod_usuario' => $codUsuario,
            'nombre_persona' => $nombre,
            'url_usuario' => $url,
            'estado_usuario' => (string)$fila['estado_usuario']
        );
    }
    if ($resultado) {
        $resultado->free();
    }
    if ($cerrar) {
        $mysqli->close();
    }
    return $salida;
}

function interconsultaRenderGrupoParticipantes($participantes, $maximo = 5)
{
    $participantes = array_values((array)$participantes);
    $maximo = max(1, intval($maximo));
    $visibles = array_slice($participantes, 0, $maximo);
    $html = '<div class="interconsulta-list-avatar-group" aria-label="Participantes actuales">';
    foreach ($visibles as $participante) {
        $nombre = isset($participante['nombre_persona']) ? (string)$participante['nombre_persona'] : 'Usuario';
        $url = isset($participante['url_usuario']) ? trim((string)$participante['url_usuario']) : '';
        $titulo = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        if ($url !== '') {
            $html .= '<span class="interconsulta-list-avatar" title="'.$titulo.'"><img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="Foto de '.$titulo.'" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'grid\';"><b style="display:none">'.htmlspecialchars(interconsultaLecturasIniciales($nombre), ENT_QUOTES, 'UTF-8').'</b></span>';
        } else {
            $html .= '<span class="interconsulta-list-avatar" title="'.$titulo.'"><b>'.htmlspecialchars(interconsultaLecturasIniciales($nombre), ENT_QUOTES, 'UTF-8').'</b></span>';
        }
    }
    $restantes = count($participantes) - count($visibles);
    if ($restantes > 0) {
        $nombresRestantes = array();
        foreach (array_slice($participantes, $maximo) as $participante) {
            $nombresRestantes[] = isset($participante['nombre_persona']) ? $participante['nombre_persona'] : 'Usuario';
        }
        $html .= '<span class="interconsulta-list-avatar interconsulta-list-avatar--more" title="'.htmlspecialchars(implode(', ', $nombresRestantes), ENT_QUOTES, 'UTF-8').'">+'.$restantes.'</span>';
    }
    if (count($participantes) === 0) {
        $html .= '<span class="interconsulta-list-avatar interconsulta-list-avatar--empty" title="Sin participantes identificados"><i class="fa-solid fa-user-slash" aria-hidden="true"></i></span>';
    }
    return $html.'</div>';
}

function interconsultaColaboradoresDatosHilos($idsHilos, $mysqli = null)
{
    $idsHilos = interconsultaLecturasIdsSeguros($idsHilos, 100);
    $salida = array();
    if (count($idsHilos) === 0) {
        return $salida;
    }
    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    if (!interconsultaLecturasTablaExiste($mysqli, 'funcionario_hilo_principal')) {
        if ($cerrar) { $mysqli->close(); }
        return $salida;
    }
    $lista = implode(',', array_map('intval', $idsHilos));
    $tienePerfil = interconsultaLecturasTablaExiste($mysqli, 'usuario_perfil_extendido');
    $camposPerfil = $tienePerfil
        ? "IFNULL(NULLIF(TRIM(up.cargo_funcion),''),u.tipo) AS cargo_funcionario,IFNULL(up.area,'') AS area_funcionario"
        : "IFNULL(u.tipo,'') AS cargo_funcionario,'' AS area_funcionario";
    $joinPerfil = $tienePerfil ? " LEFT JOIN usuario_perfil_extendido up ON up.cod_usuarioFK=u.cod_usuario" : '';
    $sql = "SELECT fh.cod_interConsultaFK,fh.cod_usuarioFK,p.nombre_persona,u.url,u.tipo,l.Nombre AS nombre_local,".$camposPerfil."
            FROM funcionario_hilo_principal fh
            INNER JOIN usuario u ON u.cod_usuario=fh.cod_usuarioFK
            LEFT JOIN persona p ON p.cod_persona=u.cod_usuario
            LEFT JOIN local l ON l.cod_local=u.cod_localFK".$joinPerfil."
            WHERE fh.estado='activo' AND fh.cod_interConsultaFK IN (".$lista.")
            ORDER BY fh.id DESC";
    $resultado = $mysqli->query($sql);
    while ($resultado && $fila = $resultado->fetch_assoc()) {
        $codHilo = intval($fila['cod_interConsultaFK']);
        if ($codHilo <= 0 || isset($salida[$codHilo])) {
            continue;
        }
        $registro = array();
        foreach ($fila as $clave => $valor) {
            if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
                $registro[$clave] = mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
            } else {
                $registro[$clave] = $valor;
            }
        }
        $salida[$codHilo] = $registro;
    }
    if ($resultado) { $resultado->free(); }
    if ($cerrar) { $mysqli->close(); }
    return $salida;
}

function interconsultaLecturasAsegurarUsuariosHilo($mysqli, $codHilo, $participantes, $fechaInicio = '')
{
    if (!interconsultaLecturasEstructuraDisponible($mysqli)) {
        return false;
    }
    $codHilo = intval($codHilo);
    if ($codHilo <= 0) {
        return false;
    }
    $fechaInicio = trim((string)$fechaInicio);
    if ($fechaInicio === '' || strtotime($fechaInicio) === false) {
        $fechaInicio = date('Y-m-d H:i:s');
    }
    $stmt = $mysqli->prepare("INSERT IGNORE INTO interconsulta_lectura_usuario
        (cod_interConsultaFK,cod_usuarioFK,fecha_inicio_conteo,fecha_ultima_apertura,estado)
        VALUES (?,?,?,NULL,'activo')");
    if (!$stmt) {
        return false;
    }
    foreach ((array)$participantes as $participante) {
        $codUsuario = is_array($participante) ? intval(isset($participante['cod_usuario']) ? $participante['cod_usuario'] : 0) : intval($participante);
        if ($codUsuario <= 0) { continue; }
        $stmt->bind_param('iis', $codHilo, $codUsuario, $fechaInicio);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }
    $stmt->close();
    return true;
}

function interconsultaLecturasSincronizarParticipantesHilo($mysqli, $codHilo, $fechaInicio = '')
{
    $mapa = interconsultaParticipantesActualesHilos(array($codHilo), $mysqli);
    $participantes = isset($mapa[intval($codHilo)]) ? $mapa[intval($codHilo)] : array();
    return interconsultaLecturasAsegurarUsuariosHilo($mysqli, $codHilo, $participantes, $fechaInicio);
}

function interconsultaLecturasMarcarHiloAbierto($codHilo, $codUsuario)
{
    $codHilo = intval($codHilo);
    $codUsuario = intval($codUsuario);
    if ($codHilo <= 0 || $codUsuario <= 0) {
        return 0;
    }
    $mysqli = conectar_al_servidor();
    if (!interconsultaLecturasEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return 0;
    }
    $mysqli->begin_transaction();
    try {
        $ahora = date('Y-m-d H:i:s');
        $participantes = interconsultaParticipantesActualesHilos(array($codHilo), $mysqli);
        $usuarios = isset($participantes[$codHilo]) ? $participantes[$codHilo] : array();
        $usuarios[$codUsuario] = array('cod_usuario' => $codUsuario);
        if (!interconsultaLecturasAsegurarUsuariosHilo($mysqli, $codHilo, $usuarios, $ahora)) {
            throw new Exception('No se pudo preparar el estado de lectura.');
        }
        $stmt = $mysqli->prepare("INSERT IGNORE INTO interconsulta_mensaje_lectura
            (cod_mensajeFK,cod_interConsultaFK,cod_usuarioFK,fecha_lectura)
            SELECT m.cod_mensaje,m.cod_interConsultaFK,?,NOW()
            FROM mensaje m
            WHERE m.cod_interConsultaFK=? AND m.estado='activo' AND m.fecha_creacion<=NOW()
              AND IFNULL(m.cod_usuarioFK,0)>0 AND m.cod_usuarioFK<>?");
        if (!$stmt) { throw new Exception('No se pudo preparar la lectura.'); }
        $stmt->bind_param('iii', $codUsuario, $codHilo, $codUsuario);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo registrar la lectura.'); }
        $insertadas = intval($stmt->affected_rows);
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE interconsulta_lectura_usuario SET fecha_ultima_apertura=NOW(),estado='activo'
            WHERE cod_interConsultaFK=? AND cod_usuarioFK=?");
        if (!$stmt) { throw new Exception('No se pudo actualizar la apertura.'); }
        $stmt->bind_param('ii', $codHilo, $codUsuario);
        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo actualizar la apertura.'); }
        $stmt->close();

        // Mantiene el contrato legacy de menciones mientras existan consumidores antiguos.
        $stmt = $mysqli->prepare("UPDATE menciones mn INNER JOIN mensaje m ON m.cod_mensaje=mn.cod_mensajeFK
            SET mn.isLeido=1 WHERE m.cod_interConsultaFK=? AND m.fecha_creacion<=NOW()
              AND mn.cod_usuarioFK=? AND mn.isLeido=0");
        if ($stmt) {
            $stmt->bind_param('ii', $codHilo, $codUsuario);
            $stmt->execute();
            $stmt->close();
        }
        $mysqli->commit();
        $mysqli->close();
        return $insertadas;
    } catch (Exception $e) {
        $mysqli->rollback();
        $mysqli->close();
        return 0;
    }
}

function interconsultaLecturasNoLeidosHilos($idsHilos, $codUsuario, $mysqli = null)
{
    $idsHilos = interconsultaLecturasIdsSeguros($idsHilos, 100);
    $codUsuario = intval($codUsuario);
    $salida = array();
    foreach ($idsHilos as $id) { $salida[$id] = 0; }
    if ($codUsuario <= 0 || count($idsHilos) === 0) {
        return $salida;
    }
    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    if (!interconsultaLecturasEstructuraDisponible($mysqli)) {
        if ($cerrar) { $mysqli->close(); }
        return $salida;
    }
    $participantes = interconsultaParticipantesActualesHilos($idsHilos, $mysqli);
    $hilosParticipante = array();
    foreach ($idsHilos as $codHilo) {
        if (isset($participantes[$codHilo][$codUsuario])) {
            $hilosParticipante[] = $codHilo;
            interconsultaLecturasAsegurarUsuariosHilo($mysqli, $codHilo, array($participantes[$codHilo][$codUsuario]), date('Y-m-d H:i:s'));
        }
    }
    if (count($hilosParticipante) === 0) {
        if ($cerrar) { $mysqli->close(); }
        return $salida;
    }
    $lista = implode(',', array_map('intval', $hilosParticipante));
    $sql = "SELECT m.cod_interConsultaFK,COUNT(*) AS total
            FROM mensaje m
            INNER JOIN interconsulta_lectura_usuario lu
              ON lu.cod_interConsultaFK=m.cod_interConsultaFK AND lu.cod_usuarioFK=".$codUsuario." AND lu.estado='activo'
            LEFT JOIN interconsulta_mensaje_lectura ml
              ON ml.cod_mensajeFK=m.cod_mensaje AND ml.cod_usuarioFK=".$codUsuario."
            WHERE m.cod_interConsultaFK IN (".$lista.") AND m.estado='activo' AND m.fecha_creacion<=NOW()
              AND m.fecha_creacion>=lu.fecha_inicio_conteo AND IFNULL(m.cod_usuarioFK,0)>0
              AND m.cod_usuarioFK<>".$codUsuario." AND ml.id IS NULL
            GROUP BY m.cod_interConsultaFK";
    $resultado = $mysqli->query($sql);
    while ($resultado && $fila = $resultado->fetch_assoc()) {
        $salida[intval($fila['cod_interConsultaFK'])] = intval($fila['total']);
    }
    if ($resultado) { $resultado->free(); }
    if ($cerrar) { $mysqli->close(); }
    return $salida;
}

function interconsultaLecturasTotalUsuario($codUsuario)
{
    $codUsuario = intval($codUsuario);
    if ($codUsuario <= 0) { return 0; }
    $mysqli = conectar_al_servidor();
    if (!interconsultaLecturasEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return 0;
    }
    $condicionLocal = function_exists('interconsultaAccesoCondicionLocalSql')
        ? interconsultaAccesoCondicionLocalSql($codUsuario, 'ic', $mysqli) : '1=0';
    $resultado = $mysqli->query("SELECT ic.cod_interConsulta FROM interconsulta ic
        WHERE ic.estado<>'inactivo' AND ".$condicionLocal." ORDER BY ic.cod_interConsulta DESC");
    $total = 0;
    $lote = array();
    while ($resultado && $fila = $resultado->fetch_assoc()) {
        $lote[] = intval($fila['cod_interConsulta']);
        if (count($lote) >= 100) {
            $total += array_sum(interconsultaLecturasNoLeidosHilos($lote, $codUsuario, $mysqli));
            $lote = array();
        }
    }
    if ($resultado) { $resultado->free(); }
    if (count($lote) > 0) {
        $total += array_sum(interconsultaLecturasNoLeidosHilos($lote, $codUsuario, $mysqli));
    }
    $mysqli->close();
    return $total;
}

function interconsultaLecturasResumenMensajes($codHilo, $mensajes)
{
    $salida = array();
    $ids = array();
    $autores = array();
    foreach ((array)$mensajes as $mensaje) {
        $id = intval(isset($mensaje['cod_mensaje']) ? $mensaje['cod_mensaje'] : 0);
        if ($id > 0 && intval(isset($mensaje['cod_usuarioFK']) ? $mensaje['cod_usuarioFK'] : 0) > 0) {
            $ids[] = $id;
            $autores[$id] = intval($mensaje['cod_usuarioFK']);
            $salida[$id] = array('estado' => 'guardado', 'vistas' => 0, 'esperadas' => 0);
        }
    }
    if (count($ids) === 0) { return $salida; }
    $mysqli = conectar_al_servidor();
    if (!interconsultaLecturasEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return $salida;
    }
    $mapa = interconsultaParticipantesActualesHilos(array($codHilo), $mysqli);
    $participantes = isset($mapa[intval($codHilo)]) ? $mapa[intval($codHilo)] : array();
    $lectores = array();
    $lista = implode(',', array_map('intval', $ids));
    $resultado = $mysqli->query("SELECT cod_mensajeFK,cod_usuarioFK FROM interconsulta_mensaje_lectura WHERE cod_mensajeFK IN (".$lista.")");
    while ($resultado && $fila = $resultado->fetch_assoc()) {
        $idMensaje = intval($fila['cod_mensajeFK']);
        $idUsuario = intval($fila['cod_usuarioFK']);
        if (!isset($lectores[$idMensaje])) { $lectores[$idMensaje] = array(); }
        $lectores[$idMensaje][$idUsuario] = 1;
    }
    if ($resultado) { $resultado->free(); }
    foreach ($ids as $idMensaje) {
        $autor = isset($autores[$idMensaje]) ? $autores[$idMensaje] : 0;
        $esperados = array();
        foreach ($participantes as $codParticipante => $datosParticipante) {
            if (intval($codParticipante) !== $autor) { $esperados[intval($codParticipante)] = 1; }
        }
        $vistas = isset($lectores[$idMensaje]) ? count($lectores[$idMensaje]) : 0;
        $vistasEsperadas = 0;
        foreach ($esperados as $codEsperado => $uno) {
            if (isset($lectores[$idMensaje][$codEsperado])) { $vistasEsperadas++; }
        }
        $estado = 'guardado';
        if (count($esperados) > 0 && $vistasEsperadas >= count($esperados)) {
            $estado = 'todos';
        } elseif ($vistas > 0) {
            $estado = 'algunos';
        }
        $salida[$idMensaje] = array('estado' => $estado, 'vistas' => $vistas, 'esperadas' => count($esperados));
    }
    $mysqli->close();
    return $salida;
}

function interconsultaLecturasDetalleMensaje($codMensaje, $codHilo, $codUsuario)
{
    $codMensaje = intval($codMensaje);
    $codHilo = intval($codHilo);
    $codUsuario = intval($codUsuario);
    if ($codMensaje <= 0 || $codHilo <= 0 || $codUsuario <= 0) {
        return array('ok' => false, 'mensaje' => 'El mensaje no es valido.');
    }
    $mysqli = conectar_al_servidor();
    if (!interconsultaLecturasEstructuraDisponible($mysqli)) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'La migracion de lecturas aun no esta instalada.');
    }
    $stmt = $mysqli->prepare("SELECT m.cod_usuarioFK FROM mensaje m WHERE m.cod_mensaje=? AND m.cod_interConsultaFK=? AND m.estado='activo' LIMIT 1");
    if (!$stmt) { $mysqli->close(); return array('ok' => false, 'mensaje' => 'No se pudo consultar el mensaje.'); }
    $stmt->bind_param('ii', $codMensaje, $codHilo);
    $stmt->execute();
    $mensaje = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$mensaje || intval($mensaje['cod_usuarioFK']) <= 0) {
        $mysqli->close();
        return array('ok' => false, 'mensaje' => 'El mensaje no admite confirmaciones de lectura.');
    }
    $stmt = $mysqli->prepare("SELECT ml.cod_usuarioFK,ml.fecha_lectura,IFNULL(p.nombre_persona,'Usuario') AS nombre_persona,IFNULL(u.url,'') AS url_usuario
        FROM interconsulta_mensaje_lectura ml
        LEFT JOIN persona p ON p.cod_persona=ml.cod_usuarioFK
        LEFT JOIN usuario u ON u.cod_usuario=ml.cod_usuarioFK
        WHERE ml.cod_mensajeFK=? ORDER BY ml.fecha_lectura ASC,ml.id ASC");
    $lecturas = array();
    if ($stmt) {
        $stmt->bind_param('i', $codMensaje);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($fila = $resultado->fetch_assoc()) {
                foreach ($fila as $clave => $valor) {
                    if (is_string($valor) && !mb_check_encoding($valor, 'UTF-8')) {
                        $fila[$clave] = mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
                    }
                }
                $lecturas[] = $fila;
            }
        }
        $stmt->close();
    }
    $mysqli->close();
    return array('ok' => true, 'lecturas' => $lecturas);
}

?>

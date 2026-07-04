<?php
ob_start();

// ===============================
// CONEXIÓN BD CAMBIAR CREDENCIALES CORRECTAS
// ===============================
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("subir_foto_base64.php");
include_once("buscar_nivel.php");
include_once("classTable.php");
$host = "localhost";
$dbname = "syscvxco_ac";
$username = "root";
$password = '';

try {
    $credenciales = array(
        array('host' => $host, 'dbname' => $dbname, 'username' => 'syscvxco_ac', 'password' => 'syscvxco_ac'),
        array('host' => $host, 'dbname' => $dbname, 'username' => $username, 'password' => $password)
    );
    $pdo = null;
    $ultimoErrorConexion = null;

    foreach ($credenciales as $credencial) {
        try {
            $pdo = new PDO(
                "mysql:host={$credencial['host']};dbname={$credencial['dbname']};charset=utf8mb4",
                $credencial['username'],
                $credencial['password']
            );
            break;
        } catch (PDOException $e) {
            $ultimoErrorConexion = $e;
        }
    }

    if (!$pdo) {
        throw $ultimoErrorConexion;
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function grant_normalizar_utf8($valor)
{
    if (is_array($valor)) {
        foreach ($valor as $clave => $item) {
            $valor[$clave] = grant_normalizar_utf8($item);
        }
        return $valor;
    }

    if (!is_string($valor) || $valor === '') {
        return $valor;
    }

    if (function_exists('mb_check_encoding') && mb_check_encoding($valor, 'UTF-8')) {
        return $valor;
    }

    if (function_exists('mb_convert_encoding')) {
        $convertido = @mb_convert_encoding($valor, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        if (is_string($convertido)) {
            return $convertido;
        }
    }

    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'UTF-8//IGNORE', $valor);
        if (is_string($convertido)) {
            return $convertido;
        }
    }

    return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $valor);
}

function grant_url_redireccion($parametros_extra = array())
{
    $parametros = array_merge($_GET, $parametros_extra);
    unset($parametros['delete']);

    $url = 'Grant.php';
    if (!empty($parametros)) {
        $url .= '?' . http_build_query($parametros);
    }

    return $url;
}

function grant_redireccionar($url)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    }

    $url_segura = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    echo '<meta http-equiv="refresh" content="0;url=' . $url_segura . '">';
    echo '</head><body>';
    echo '<script>window.location.replace(' . json_encode($url) . ');</script>';
    echo '<a href="' . $url_segura . '">Continuar</a>';
    echo '</body></html>';
    exit;
}

function grant_mostrar_error($titulo, $detalle)
{
    error_log('Grant.php: ' . $titulo . ' ' . $detalle);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $url = grant_url_redireccion();
    $url_segura = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $titulo_seguro = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
    $detalle_seguro = htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8');

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<style>';
    echo 'body{margin:0;padding:16px;font-family:Arial,sans-serif;background:#fff;color:#172033;font-size:13px;}';
    echo '.grant-error{border:1px solid #fecaca;background:#fff7f7;color:#991b1b;border-radius:8px;padding:14px;line-height:1.45;}';
    echo '.grant-error h1{margin:0 0 6px;font-size:15px;}';
    echo '.grant-error p{margin:0 0 10px;color:#7f1d1d;}';
    echo '.grant-error a{display:inline-block;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:6px;padding:8px 10px;text-decoration:none;font-weight:700;}';
    echo '</style></head><body>';
    echo '<div class="grant-error">';
    echo '<h1>' . $titulo_seguro . '</h1>';
    echo '<p>' . $detalle_seguro . '</p>';
    echo '<a href="' . $url_segura . '">Volver al diagrama</a>';
    echo '</div>';
    echo '</body></html>';
    exit;
}

function grant_responder_json($informacion, $codigo_http = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($codigo_http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(grant_normalizar_utf8($informacion));
    exit;
}

function grant_nombre_identificador_valido($nombre)
{
    return preg_match('/^[a-zA-Z0-9_]+$/', (string) $nombre) === 1;
}

function grant_tabla_existe(PDO $pdo, $tabla)
{
    static $cache = array();

    if (!grant_nombre_identificador_valido($tabla)) {
        return false;
    }

    if (array_key_exists($tabla, $cache)) {
        return $cache[$tabla];
    }

    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute(array($tabla));
        $cache[$tabla] = $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        $cache[$tabla] = false;
    }

    return $cache[$tabla];
}

function grant_columna_existe(PDO $pdo, $tabla, $columna)
{
    static $cache = array();
    $clave = $tabla . '.' . $columna;

    if (!grant_nombre_identificador_valido($tabla) || !grant_nombre_identificador_valido($columna)) {
        return false;
    }

    if (array_key_exists($clave, $cache)) {
        return $cache[$clave];
    }

    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `" . $tabla . "` LIKE ?");
        $stmt->execute(array($columna));
        $cache[$clave] = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    } catch (PDOException $e) {
        $cache[$clave] = false;
    }

    return $cache[$clave];
}

function grant_fecha_iso($valor)
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return '';
    }

    $ts = strtotime($valor);
    if ($ts === false) {
        return '';
    }

    return date('Y-m-d', $ts);
}

function grant_normalizar_estado($estado)
{
    $estado = trim((string) $estado);
    $mapa = array(
        'pendiente' => 'Pendiente',
        'en progreso' => 'En Progreso',
        'en proceso' => 'En Progreso',
        'completada' => 'Completada',
        'culminada' => 'Completada',
        'anulada' => 'Anulada'
    );
    $clave = function_exists('mb_strtolower') ? mb_strtolower($estado, 'UTF-8') : strtolower($estado);

    return isset($mapa[$clave]) ? $mapa[$clave] : 'Pendiente';
}

function grant_valor_historial($valor)
{
    if ($valor === null) {
        return null;
    }

    if (is_array($valor) || is_object($valor)) {
        return json_encode(grant_normalizar_utf8($valor));
    }

    return (string) $valor;
}

function grant_obtener_nombre_usuario(PDO $pdo, $usuario_id)
{
    if ($usuario_id === null || $usuario_id === '') {
        return '';
    }

    try {
        $stmt = $pdo->prepare("SELECT nombre_persona FROM persona WHERE cod_persona = ? LIMIT 1");
        $stmt->execute(array($usuario_id));
        $nombre = $stmt->fetchColumn();
        return $nombre !== false ? (string) $nombre : '';
    } catch (PDOException $e) {
        return '';
    }
}

function grant_verificar_usuario_api(PDO $pdo, $payload)
{
    $usuario = isset($payload['useru']) ? trim((string) $payload['useru']) : '';
    $pass = isset($payload['passu']) ? (string) $payload['passu'] : '';
    $navegador = isset($payload['navegador']) ? trim((string) $payload['navegador']) : '';

    if ($usuario === '' || $pass === '' || $navegador === '') {
        grant_responder_json(array(
            'status' => 'error',
            'message' => 'Sesion no validada. Vuelva a ingresar al sistema.'
        ), 401);
    }

    $pass = str_replace('=', '+', $pass);
    $resp = verificar_navegador($usuario, $navegador, $pass);

    if ($resp !== 'ok') {
        grant_responder_json(array(
            'status' => 'error',
            'message' => 'Sesion expirada o usuario no autorizado.'
        ), 401);
    }

    return $usuario;
}

function grant_obtener_tarea(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ? LIMIT 1");
    $stmt->execute(array($id));
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    return $tarea ? $tarea : null;
}

function grant_registrar_historial(PDO $pdo, $tarea_id, $usuario_id, $accion, $campo, $valor_anterior, $valor_nuevo, $motivo, $origen, $metadata = array())
{
    if (!grant_tabla_existe($pdo, 'tarea_historial')) {
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO tarea_historial
                (tarea_id, usuario_id, accion, campo, valor_anterior, valor_nuevo, motivo, origen, created_at, metadata_json)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute(array(
            $tarea_id,
            $usuario_id !== '' ? $usuario_id : null,
            $accion,
            $campo,
            grant_valor_historial($valor_anterior),
            grant_valor_historial($valor_nuevo),
            $motivo !== '' ? $motivo : null,
            $origen,
            !empty($metadata) ? json_encode(grant_normalizar_utf8($metadata)) : null
        ));
    } catch (PDOException $e) {
        error_log('Grant.php historial: ' . $e->getMessage());
    }
}

function grant_registrar_cambios_tarea(PDO $pdo, $tarea_id, $usuario_id, $antes, $despues, $origen, $motivo = '')
{
    $campos = array(
        'titulo' => 'cambio_titulo',
        'fecha_inicio' => 'cambio_fecha_inicio',
        'fecha_fin' => 'cambio_fecha_fin',
        'progreso' => 'cambio_progreso',
        'estado' => 'cambio_estado',
        'dependencia' => 'cambio_dependencia',
        'sucursal' => 'cambio_sucursal',
        'responsable' => 'cambio_responsable',
        'observacion' => 'cambio_observacion',
        'prioridad' => 'cambio_prioridad'
    );

    $cambio_inicio = array_key_exists('fecha_inicio', $antes) && array_key_exists('fecha_inicio', $despues)
        && (string) $antes['fecha_inicio'] !== (string) $despues['fecha_inicio'];
    $cambio_fin = array_key_exists('fecha_fin', $antes) && array_key_exists('fecha_fin', $despues)
        && (string) $antes['fecha_fin'] !== (string) $despues['fecha_fin'];

    if ($cambio_inicio && $cambio_fin) {
        grant_registrar_historial(
            $pdo,
            $tarea_id,
            $usuario_id,
            'movimiento',
            'fechas',
            array('fecha_inicio' => $antes['fecha_inicio'], 'fecha_fin' => $antes['fecha_fin']),
            array('fecha_inicio' => $despues['fecha_inicio'], 'fecha_fin' => $despues['fecha_fin']),
            $motivo,
            $origen
        );
    } elseif ($cambio_inicio || $cambio_fin) {
        grant_registrar_historial(
            $pdo,
            $tarea_id,
            $usuario_id,
            'cambio_duracion',
            $cambio_inicio ? 'fecha_inicio' : 'fecha_fin',
            $cambio_inicio ? $antes['fecha_inicio'] : $antes['fecha_fin'],
            $cambio_inicio ? $despues['fecha_inicio'] : $despues['fecha_fin'],
            $motivo,
            $origen
        );
    }

    foreach ($campos as $campo => $accion) {
        if (!array_key_exists($campo, $antes) || !array_key_exists($campo, $despues)) {
            continue;
        }

        if ((string) $antes[$campo] === (string) $despues[$campo]) {
            continue;
        }

        if ($campo === 'estado') {
            if ($despues[$campo] === 'Completada') {
                $accion = 'culminar';
            } elseif ($antes[$campo] === 'Completada') {
                $accion = 'reabrir';
            } elseif ($despues[$campo] === 'Anulada') {
                $accion = 'anular';
            }
        }

        grant_registrar_historial($pdo, $tarea_id, $usuario_id, $accion, $campo, $antes[$campo], $despues[$campo], $motivo, $origen);
    }
}

function grant_actualizar_campos_tarea(PDO $pdo, $id, $campos)
{
    $columnas_base = array(
        'titulo' => true,
        'fecha_inicio' => true,
        'fecha_fin' => true,
        'progreso' => true,
        'estado' => true,
        'dependencia' => true,
        'sucursal' => true,
        'responsable' => true
    );
    $sets = array();
    $valores = array();

    foreach ($campos as $campo => $valor) {
        if (!isset($columnas_base[$campo]) && !grant_columna_existe($pdo, 'tareas', $campo)) {
            continue;
        }

        $sets[] = "`" . $campo . "` = ?";
        $valores[] = $valor;
    }

    if (empty($sets)) {
        return;
    }

    $valores[] = $id;
    $stmt = $pdo->prepare("UPDATE tareas SET " . implode(', ', $sets) . " WHERE id = ?");
    $stmt->execute($valores);
}

function grant_obtener_vinculados_tarea(PDO $pdo, $tarea_id)
{
    if (!grant_tabla_existe($pdo, 'tarea_usuarios')) {
        return array('ids' => array(), 'nombres' => '');
    }

    try {
        $stmt = $pdo->prepare("
            SELECT tu.cod_usuario, p.nombre_persona
            FROM tarea_usuarios tu
            INNER JOIN usuario u ON u.cod_usuario = tu.cod_usuario
            INNER JOIN persona p ON p.cod_persona = u.cod_usuario
            WHERE tu.tarea_id = ?
            ORDER BY p.nombre_persona ASC
        ");
        $stmt->execute(array($tarea_id));
        $ids = array();
        $nombres = array();

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $ids[] = (int) $fila['cod_usuario'];
            $nombres[] = $fila['nombre_persona'];
        }

        return array('ids' => $ids, 'nombres' => implode(', ', $nombres));
    } catch (PDOException $e) {
        return array('ids' => array(), 'nombres' => '');
    }
}

function grant_obtener_info_responsable(PDO $pdo, $responsable)
{
    $responsable = trim((string) $responsable);
    if ($responsable === '') {
        return array('id' => null, 'foto' => '', 'rol' => '');
    }

    try {
        $columna_rol = grant_columna_existe($pdo, 'usuario', 'tipo') ? 'u.tipo AS rol' : "'' AS rol";
        $stmt = $pdo->prepare("
            SELECT u.cod_usuario, u.url, " . $columna_rol . "
            FROM usuario u
            INNER JOIN persona p ON p.cod_persona = u.cod_usuario
            WHERE p.nombre_persona = ?
            LIMIT 1
        ");
        $stmt->execute(array($responsable));
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return array('id' => null, 'foto' => '', 'rol' => '');
        }

        return array('id' => (int) $fila['cod_usuario'], 'foto' => (string) $fila['url'], 'rol' => (string) $fila['rol']);
    } catch (PDOException $e) {
        return array('id' => null, 'foto' => '', 'rol' => '');
    }
}

function grant_formatear_ultimo_cambio($fila)
{
    if (!$fila || empty($fila['created_at'])) {
        return 'Sin cambios registrados';
    }

    $ts = strtotime($fila['created_at']);
    $fecha = $ts ? date('d/m/Y H:i', $ts) : $fila['created_at'];
    if ($ts && date('Y-m-d', $ts) === date('Y-m-d')) {
        $fecha = 'Hoy ' . date('H:i', $ts);
    }

    $usuario = !empty($fila['nombre_persona']) ? $fila['nombre_persona'] : 'usuario';
    return 'Ultimo cambio: ' . $fecha . ' por ' . $usuario;
}

function grant_obtener_ultimo_cambio_tarea(PDO $pdo, $tarea_id)
{
    if (!grant_tabla_existe($pdo, 'tarea_historial')) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT h.*, p.nombre_persona
            FROM tarea_historial h
            LEFT JOIN persona p ON p.cod_persona = h.usuario_id
            WHERE h.tarea_id = ?
            ORDER BY h.created_at DESC, h.id DESC
            LIMIT 1
        ");
        $stmt->execute(array($tarea_id));
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila : null;
    } catch (PDOException $e) {
        return null;
    }
}

function grant_obtener_ultimos_cambios_tareas(PDO $pdo)
{
    $ultimos = array();

    if (!grant_tabla_existe($pdo, 'tarea_historial')) {
        return $ultimos;
    }

    try {
        $stmt = $pdo->query("
            SELECT h.*, p.nombre_persona
            FROM tarea_historial h
            LEFT JOIN persona p ON p.cod_persona = h.usuario_id
            INNER JOIN (
                SELECT tarea_id, MAX(id) AS ultimo_id
                FROM tarea_historial
                GROUP BY tarea_id
            ) u ON u.ultimo_id = h.id
        ");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $ultimos[$fila['tarea_id']] = $fila;
        }
    } catch (PDOException $e) {
        error_log('Grant.php ultimos cambios: ' . $e->getMessage());
    }

    return $ultimos;
}

function grant_tarea_payload(PDO $pdo, $tarea)
{
    $responsable = isset($tarea['responsable']) ? trim((string) $tarea['responsable']) : '';
    $responsable_info = grant_obtener_info_responsable($pdo, $responsable);
    $vinculados = grant_obtener_vinculados_tarea($pdo, $tarea['id']);
    $estado = isset($tarea['estado']) ? trim((string) $tarea['estado']) : 'Pendiente';
    $estado_visual = $estado !== '' ? $estado : 'Pendiente';
    $class_estado = 'bar-estado-pendiente';
    $fecha_fin_ts = strtotime($tarea['fecha_fin']);
    $hoy_ts = strtotime(date('Y-m-d'));

    if ($estado === 'Completada') {
        $class_estado = 'bar-estado-completada';
        $estado_visual = 'Culminada';
    } elseif ($estado === 'Anulada') {
        $class_estado = 'bar-estado-anulada';
        $estado_visual = 'Anulada';
    } elseif ($fecha_fin_ts !== false && $fecha_fin_ts < $hoy_ts) {
        $class_estado = 'bar-estado-vencida';
        $estado_visual = 'Vencida';
    } elseif ($estado === 'En Progreso') {
        $class_estado = 'bar-estado-progreso';
        $estado_visual = 'En progreso';
    } elseif ($fecha_fin_ts !== false && $fecha_fin_ts <= strtotime('+2 days', $hoy_ts)) {
        $class_estado = 'bar-estado-proxima';
        $estado_visual = 'Proxima a vencer';
    }

    $class = 'bar-general';
    if (isset($tarea['sucursal']) && $tarea['sucursal'] === 'Villa Morra') {
        $class = 'bar-villamorra';
    }
    if (isset($tarea['sucursal']) && ($tarea['sucursal'] === 'Cerro Corá' || $tarea['sucursal'] === 'Cerro Corá' || $tarea['sucursal'] === 'Cerro Corá')) {
        $class = 'bar-CerroCora';
    }
    if (isset($tarea['sucursal']) && $tarea['sucursal'] === 'Oviedo') {
        $class = 'bar-Oviedo';
    }
    if (isset($tarea['sucursal']) && $tarea['sucursal'] === 'San Lorenzo') {
        $class = 'bar-SanLorenzo';
    }
    if (isset($tarea['sucursal']) && $tarea['sucursal'] === 'Santa Librada') {
        $class = 'bar-SantaLibrada';
    }

    $titulo = isset($tarea['titulo']) ? (string) $tarea['titulo'] : '';
    $nombre_barra = trim($titulo . ($responsable !== '' ? ', ' . $responsable : ''));
    $ultimo_cambio = grant_obtener_ultimo_cambio_tarea($pdo, $tarea['id']);

    return array(
        'id' => (string) $tarea['id'],
        'name' => $nombre_barra,
        'start' => $tarea['fecha_inicio'],
        'end' => $tarea['fecha_fin'],
        'progress' => isset($tarea['progreso']) ? (int) $tarea['progreso'] : 0,
        'dependencies' => !empty($tarea['dependencia']) ? (string) $tarea['dependencia'] : '',
        'custom_class' => $class . ' ' . $class_estado,
        'sucursal' => isset($tarea['sucursal']) ? $tarea['sucursal'] : '',
        'responsable' => $responsable,
        'responsable_id' => $responsable_info['id'],
        'responsable_rol' => $responsable_info['rol'],
        'usuarios_vinculados' => $vinculados['nombres'],
        'usuarios_vinculados_ids' => $vinculados['ids'],
        'estado' => $estado,
        'estado_visual' => $estado_visual,
        'foto_responsable' => $responsable_info['foto'],
        'titulo' => $titulo,
        'titulo_original' => $titulo,
        'nombre_barra' => $nombre_barra,
        'observacion' => isset($tarea['observacion']) ? (string) $tarea['observacion'] : '',
        'prioridad' => isset($tarea['prioridad']) && $tarea['prioridad'] !== '' ? (string) $tarea['prioridad'] : 'Normal',
        'culminada_en' => isset($tarea['culminada_en']) ? (string) $tarea['culminada_en'] : '',
        'anulada_en' => isset($tarea['anulada_en']) ? (string) $tarea['anulada_en'] : '',
        'motivo_anulacion' => isset($tarea['motivo_anulacion']) ? (string) $tarea['motivo_anulacion'] : '',
        'deleted_at' => isset($tarea['deleted_at']) ? (string) $tarea['deleted_at'] : '',
        'ultimo_cambio_texto' => grant_formatear_ultimo_cambio($ultimo_cambio)
    );
}

function grant_sincronizar_usuario_vinculado(PDO $pdo, $tarea_id, $usuario_id)
{
    if ($usuario_id === null || $usuario_id === '' || !grant_tabla_existe($pdo, 'tarea_usuarios')) {
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarea_usuarios WHERE tarea_id = ? AND cod_usuario = ?");
        $stmt->execute(array($tarea_id, $usuario_id));
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO tarea_usuarios (tarea_id, cod_usuario) VALUES (?, ?)");
        $stmt->execute(array($tarea_id, $usuario_id));
    } catch (PDOException $e) {
        error_log('Grant.php vincular usuario: ' . $e->getMessage());
    }
}

function grant_manejar_api_gantt(PDO $pdo, $payload)
{
    $accion = isset($payload['accion_gantt']) ? trim((string) $payload['accion_gantt']) : '';
    if ($accion === '') {
        grant_responder_json(array('status' => 'error', 'message' => 'Accion invalida.'), 400);
    }

    $usuario_id = grant_verificar_usuario_api($pdo, $payload);
    $origen = isset($payload['origen']) && $payload['origen'] !== '' ? (string) $payload['origen'] : 'grilla Gantt';

    try {
        if ($accion === 'crear_rapida') {
            $titulo = trim((string) (isset($payload['titulo']) ? $payload['titulo'] : ''));
            $fecha = grant_fecha_iso(isset($payload['fecha_inicio']) ? $payload['fecha_inicio'] : '');
            $sucursal = trim((string) (isset($payload['sucursal']) ? $payload['sucursal'] : 'Todas'));
            $responsable = trim((string) (isset($payload['responsable']) ? $payload['responsable'] : ''));
            $responsable_id = isset($payload['responsable_id']) ? trim((string) $payload['responsable_id']) : '';

            if ($titulo === '' || $fecha === '') {
                grant_responder_json(array('status' => 'error', 'message' => 'Titulo o fecha invalida.'), 400);
            }

            if ($sucursal === '') {
                $sucursal = 'Todas';
            }
            if ($responsable === '') {
                $responsable = 'Sin asignar';
            }

            $campos = array(
                'titulo' => $titulo,
                'fecha_inicio' => $fecha,
                'fecha_fin' => $fecha,
                'progreso' => 0,
                'estado' => 'Pendiente',
                'dependencia' => null,
                'sucursal' => $sucursal,
                'responsable' => $responsable
            );
            if (grant_columna_existe($pdo, 'tareas', 'prioridad')) {
                $campos['prioridad'] = 'Normal';
            }
            if (grant_columna_existe($pdo, 'tareas', 'created_by')) {
                $campos['created_by'] = $usuario_id;
            }
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) {
                $campos['updated_by'] = $usuario_id;
            }
            if (grant_columna_existe($pdo, 'tareas', 'created_at')) {
                $campos['created_at'] = date('Y-m-d H:i:s');
            }
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) {
                $campos['updated_at'] = date('Y-m-d H:i:s');
            }

            $columnas = array_keys($campos);
            $placeholders = implode(', ', array_fill(0, count($columnas), '?'));
            $sql = "INSERT INTO tareas (`" . implode('`, `', $columnas) . "`) VALUES (" . $placeholders . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($campos));
            $id = (int) $pdo->lastInsertId();

            grant_sincronizar_usuario_vinculado($pdo, $id, $responsable_id);
            grant_registrar_historial($pdo, $id, $usuario_id, 'creacion', 'tarea', null, $titulo, '', $origen, array('modo' => 'creacion_rapida'));

            $tarea = grant_obtener_tarea($pdo, $id);
            grant_responder_json(array(
                'status' => 'success',
                'message' => 'Tarea creada.',
                'task' => grant_tarea_payload($pdo, $tarea),
                'undo' => array('tipo' => 'anular_creacion', 'tarea_id' => $id)
            ));
        }

        if ($accion === 'actualizar_fechas') {
            $id = isset($payload['id']) ? (int) $payload['id'] : 0;
            $inicio = grant_fecha_iso(isset($payload['start']) ? $payload['start'] : '');
            $fin = grant_fecha_iso(isset($payload['end']) ? $payload['end'] : '');
            $progreso = isset($payload['progress']) ? max(0, min(100, (int) $payload['progress'])) : 0;
            $antes = grant_obtener_tarea($pdo, $id);

            if (!$antes || $inicio === '' || $fin === '') {
                grant_responder_json(array('status' => 'error', 'message' => 'Tarea o fecha invalida.'), 400);
            }

            if (strtotime($fin) < strtotime($inicio)) {
                $fin = $inicio;
            }

            $estado = $antes['estado'];
            if ($progreso === 100) {
                $estado = 'Completada';
            } elseif ($estado === 'Pendiente' && $progreso > 0) {
                $estado = 'En Progreso';
            }

            $campos = array(
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'progreso' => $progreso,
                'estado' => $estado
            );
            if ($estado === 'Completada' && grant_columna_existe($pdo, 'tareas', 'culminada_por')) {
                $campos['culminada_por'] = $usuario_id;
            }
            if ($estado === 'Completada' && grant_columna_existe($pdo, 'tareas', 'culminada_en')) {
                $campos['culminada_en'] = date('Y-m-d H:i:s');
            }
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) {
                $campos['updated_by'] = $usuario_id;
            }
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) {
                $campos['updated_at'] = date('Y-m-d H:i:s');
            }

            grant_actualizar_campos_tarea($pdo, $id, $campos);
            $despues = grant_obtener_tarea($pdo, $id);
            grant_registrar_cambios_tarea($pdo, $id, $usuario_id, $antes, $despues, $origen);

            grant_responder_json(array(
                'status' => 'success',
                'message' => ($antes['fecha_inicio'] !== $inicio && $antes['fecha_fin'] !== $fin) ? 'Tarea reprogramada.' : 'Duracion actualizada.',
                'task' => grant_tarea_payload($pdo, $despues),
                'undo' => array(
                    'tipo' => 'restaurar_campos',
                    'tarea_id' => $id,
                    'campos' => array(
                        'fecha_inicio' => $antes['fecha_inicio'],
                        'fecha_fin' => $antes['fecha_fin'],
                        'progreso' => $antes['progreso'],
                        'estado' => $antes['estado']
                    )
                )
            ));
        }

        if ($accion === 'actualizar_tarea') {
            $id = isset($payload['id']) ? (int) $payload['id'] : 0;
            $antes = grant_obtener_tarea($pdo, $id);
            if (!$antes) {
                grant_responder_json(array('status' => 'error', 'message' => 'Tarea no encontrada.'), 404);
            }

            $inicio = grant_fecha_iso(isset($payload['fecha_inicio']) ? $payload['fecha_inicio'] : $antes['fecha_inicio']);
            $fin = grant_fecha_iso(isset($payload['fecha_fin']) ? $payload['fecha_fin'] : $antes['fecha_fin']);
            if ($inicio === '') $inicio = $antes['fecha_inicio'];
            if ($fin === '') $fin = $antes['fecha_fin'];
            if (strtotime($fin) < strtotime($inicio)) $fin = $inicio;

            $estado = grant_normalizar_estado(isset($payload['estado']) ? $payload['estado'] : $antes['estado']);
            $progreso = isset($payload['progreso']) ? max(0, min(100, (int) $payload['progreso'])) : (int) $antes['progreso'];
            if ($estado === 'Completada') $progreso = 100;

            $campos = array(
                'titulo' => trim((string) (isset($payload['titulo']) ? $payload['titulo'] : $antes['titulo'])),
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'progreso' => $progreso,
                'estado' => $estado,
                'sucursal' => trim((string) (isset($payload['sucursal']) ? $payload['sucursal'] : $antes['sucursal'])),
                'responsable' => trim((string) (isset($payload['responsable']) ? $payload['responsable'] : $antes['responsable']))
            );
            if (isset($payload['dependencia'])) {
                $campos['dependencia'] = $payload['dependencia'] !== '' ? (int) $payload['dependencia'] : null;
            }
            if (grant_columna_existe($pdo, 'tareas', 'observacion')) {
                $campos['observacion'] = trim((string) (isset($payload['observacion']) ? $payload['observacion'] : ''));
            }
            if (grant_columna_existe($pdo, 'tareas', 'prioridad')) {
                $campos['prioridad'] = trim((string) (isset($payload['prioridad']) ? $payload['prioridad'] : 'Normal'));
                if ($campos['prioridad'] === '') $campos['prioridad'] = 'Normal';
            }
            if ($estado === 'Completada' && grant_columna_existe($pdo, 'tareas', 'culminada_por')) {
                $campos['culminada_por'] = $usuario_id;
            }
            if ($estado === 'Completada' && grant_columna_existe($pdo, 'tareas', 'culminada_en') && empty($antes['culminada_en'])) {
                $campos['culminada_en'] = date('Y-m-d H:i:s');
            }
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) {
                $campos['updated_by'] = $usuario_id;
            }
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) {
                $campos['updated_at'] = date('Y-m-d H:i:s');
            }

            if ($campos['titulo'] === '') {
                grant_responder_json(array('status' => 'error', 'message' => 'El titulo es obligatorio.'), 400);
            }

            grant_actualizar_campos_tarea($pdo, $id, $campos);
            if (isset($payload['responsable_id']) && $payload['responsable_id'] !== '') {
                grant_sincronizar_usuario_vinculado($pdo, $id, $payload['responsable_id']);
            }
            $despues = grant_obtener_tarea($pdo, $id);
            grant_registrar_cambios_tarea($pdo, $id, $usuario_id, $antes, $despues, $origen);

            $undo_campos = array();
            foreach (array('titulo', 'fecha_inicio', 'fecha_fin', 'progreso', 'estado', 'sucursal', 'responsable', 'dependencia', 'observacion', 'prioridad') as $campo) {
                if (array_key_exists($campo, $antes)) {
                    $undo_campos[$campo] = $antes[$campo];
                }
            }

            grant_responder_json(array(
                'status' => 'success',
                'message' => 'Tarea actualizada.',
                'task' => grant_tarea_payload($pdo, $despues),
                'undo' => array('tipo' => 'restaurar_campos', 'tarea_id' => $id, 'campos' => $undo_campos)
            ));
        }

        if ($accion === 'culminar') {
            $id = isset($payload['id']) ? (int) $payload['id'] : 0;
            $antes = grant_obtener_tarea($pdo, $id);
            if (!$antes) {
                grant_responder_json(array('status' => 'error', 'message' => 'Tarea no encontrada.'), 404);
            }

            $campos = array('estado' => 'Completada', 'progreso' => 100);
            if (grant_columna_existe($pdo, 'tareas', 'culminada_por')) $campos['culminada_por'] = $usuario_id;
            if (grant_columna_existe($pdo, 'tareas', 'culminada_en')) $campos['culminada_en'] = date('Y-m-d H:i:s');
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) $campos['updated_by'] = $usuario_id;
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) $campos['updated_at'] = date('Y-m-d H:i:s');
            grant_actualizar_campos_tarea($pdo, $id, $campos);

            $despues = grant_obtener_tarea($pdo, $id);
            grant_registrar_cambios_tarea($pdo, $id, $usuario_id, $antes, $despues, $origen);

            grant_responder_json(array(
                'status' => 'success',
                'message' => 'Tarea culminada.',
                'task' => grant_tarea_payload($pdo, $despues),
                'undo' => array(
                    'tipo' => 'restaurar_campos',
                    'tarea_id' => $id,
                    'campos' => array('estado' => $antes['estado'], 'progreso' => $antes['progreso'])
                )
            ));
        }

        if ($accion === 'anular') {
            $id = isset($payload['id']) ? (int) $payload['id'] : 0;
            $motivo = trim((string) (isset($payload['motivo']) ? $payload['motivo'] : ''));
            $antes = grant_obtener_tarea($pdo, $id);
            if (!$antes) {
                grant_responder_json(array('status' => 'error', 'message' => 'Tarea no encontrada.'), 404);
            }
            if ($motivo === '') {
                $motivo = 'Sin motivo especificado';
            }

            $campos = array('estado' => 'Anulada');
            if (grant_columna_existe($pdo, 'tareas', 'anulada_por')) $campos['anulada_por'] = $usuario_id;
            if (grant_columna_existe($pdo, 'tareas', 'anulada_en')) $campos['anulada_en'] = date('Y-m-d H:i:s');
            if (grant_columna_existe($pdo, 'tareas', 'motivo_anulacion')) $campos['motivo_anulacion'] = $motivo;
            if (grant_columna_existe($pdo, 'tareas', 'deleted_at')) $campos['deleted_at'] = date('Y-m-d H:i:s');
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) $campos['updated_by'] = $usuario_id;
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) $campos['updated_at'] = date('Y-m-d H:i:s');
            grant_actualizar_campos_tarea($pdo, $id, $campos);

            $despues = grant_obtener_tarea($pdo, $id);
            grant_registrar_cambios_tarea($pdo, $id, $usuario_id, $antes, $despues, $origen, $motivo);

            grant_responder_json(array(
                'status' => 'success',
                'message' => 'Tarea anulada.',
                'task' => grant_tarea_payload($pdo, $despues),
                'undo' => array(
                    'tipo' => 'restaurar_anulacion',
                    'tarea_id' => $id,
                    'estado_anterior' => $antes['estado']
                )
            ));
        }

        if ($accion === 'deshacer') {
            $undo = isset($payload['undo']) && is_array($payload['undo']) ? $payload['undo'] : array();
            $tipo = isset($undo['tipo']) ? (string) $undo['tipo'] : '';
            $id = isset($undo['tarea_id']) ? (int) $undo['tarea_id'] : 0;
            $antes = grant_obtener_tarea($pdo, $id);
            if (!$antes) {
                grant_responder_json(array('status' => 'error', 'message' => 'Tarea no encontrada.'), 404);
            }

            if ($tipo === 'anular_creacion') {
                $campos = array('estado' => 'Anulada');
                if (grant_columna_existe($pdo, 'tareas', 'deleted_at')) $campos['deleted_at'] = date('Y-m-d H:i:s');
                if (grant_columna_existe($pdo, 'tareas', 'anulada_por')) $campos['anulada_por'] = $usuario_id;
                if (grant_columna_existe($pdo, 'tareas', 'anulada_en')) $campos['anulada_en'] = date('Y-m-d H:i:s');
                if (grant_columna_existe($pdo, 'tareas', 'motivo_anulacion')) $campos['motivo_anulacion'] = 'Deshacer creacion rapida';
                grant_actualizar_campos_tarea($pdo, $id, $campos);
                $despues = grant_obtener_tarea($pdo, $id);
                grant_registrar_historial($pdo, $id, $usuario_id, 'deshacer_creacion', 'estado', $antes['estado'], 'Anulada', 'Deshacer', 'deshacer');
                grant_responder_json(array('status' => 'success', 'message' => 'Cambio deshecho.', 'removed' => true, 'task_id' => (string) $id));
            }

            if ($tipo === 'restaurar_anulacion') {
                $estado_anterior = isset($undo['estado_anterior']) && $undo['estado_anterior'] !== '' ? $undo['estado_anterior'] : 'Pendiente';
                $campos = array('estado' => grant_normalizar_estado($estado_anterior));
                if (grant_columna_existe($pdo, 'tareas', 'deleted_at')) $campos['deleted_at'] = null;
                if (grant_columna_existe($pdo, 'tareas', 'anulada_por')) $campos['anulada_por'] = null;
                if (grant_columna_existe($pdo, 'tareas', 'anulada_en')) $campos['anulada_en'] = null;
                if (grant_columna_existe($pdo, 'tareas', 'motivo_anulacion')) $campos['motivo_anulacion'] = null;
                grant_actualizar_campos_tarea($pdo, $id, $campos);
                $despues = grant_obtener_tarea($pdo, $id);
                grant_registrar_historial($pdo, $id, $usuario_id, 'restaurar', 'estado', $antes['estado'], $despues['estado'], 'Deshacer anulacion', 'deshacer');
                grant_responder_json(array('status' => 'success', 'message' => 'Cambio deshecho.', 'task' => grant_tarea_payload($pdo, $despues)));
            }

            if ($tipo === 'restaurar_campos') {
                $campos = isset($undo['campos']) && is_array($undo['campos']) ? $undo['campos'] : array();
                if (empty($campos)) {
                    grant_responder_json(array('status' => 'error', 'message' => 'No hay campos para deshacer.'), 400);
                }
                grant_actualizar_campos_tarea($pdo, $id, $campos);
                $despues = grant_obtener_tarea($pdo, $id);
                grant_registrar_cambios_tarea($pdo, $id, $usuario_id, $antes, $despues, 'deshacer', 'Deshacer');
                grant_responder_json(array('status' => 'success', 'message' => 'Cambio deshecho.', 'task' => grant_tarea_payload($pdo, $despues)));
            }

            grant_responder_json(array('status' => 'error', 'message' => 'No se pudo deshacer esta accion.'), 400);
        }

        if ($accion === 'historial') {
            $id = isset($payload['id']) ? (int) $payload['id'] : 0;
            if (!grant_tabla_existe($pdo, 'tarea_historial')) {
                grant_responder_json(array('status' => 'success', 'historial' => array()));
            }

            $stmt = $pdo->prepare("
                SELECT h.*, p.nombre_persona
                FROM tarea_historial h
                LEFT JOIN persona p ON p.cod_persona = h.usuario_id
                WHERE h.tarea_id = ?
                ORDER BY h.created_at DESC, h.id DESC
            ");
            $stmt->execute(array($id));
            grant_responder_json(array('status' => 'success', 'historial' => $stmt->fetchAll(PDO::FETCH_ASSOC)));
        }

        grant_responder_json(array('status' => 'error', 'message' => 'Accion no soportada.'), 400);
    } catch (PDOException $e) {
        grant_responder_json(array('status' => 'error', 'message' => $e->getMessage()), 500);
    }
}

$grant_content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if (isset($_GET['api_gantt']) || stripos($grant_content_type, 'application/json') !== false) {
    $grant_payload = json_decode(file_get_contents('php://input'), true);
    if (is_array($grant_payload) && isset($grant_payload['accion_gantt'])) {
        grant_manejar_api_gantt($pdo, $grant_payload);
    }
}

// ===============================
// CRUD - ELIMINAR
// ===============================
if (isset($_GET['delete'])) {
    try {
        $id = (int) $_GET['delete'];
        $antes = grant_obtener_tarea($pdo, $id);
        if ($antes) {
            $campos = array('estado' => 'Anulada');
            if (grant_columna_existe($pdo, 'tareas', 'deleted_at')) $campos['deleted_at'] = date('Y-m-d H:i:s');
            if (grant_columna_existe($pdo, 'tareas', 'motivo_anulacion')) $campos['motivo_anulacion'] = 'Anulada desde accion anterior';
            grant_actualizar_campos_tarea($pdo, $id, $campos);
            $despues = grant_obtener_tarea($pdo, $id);
            grant_registrar_cambios_tarea($pdo, $id, null, $antes, $despues, 'formulario completo', 'Anulacion compatible');
        }
        grant_redireccionar(grant_url_redireccion());
    } catch (PDOException $e) {
        grant_mostrar_error('No se pudo anular la tarea.', $e->getMessage());
    }
}

// ===============================
// CRUD - CREAR / ACTUALIZAR
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
        $dependencia = !empty($_POST['dependencia']) ? (int) $_POST['dependencia'] : null;
        $sucursal = !empty($_POST['sucursal']) ? $_POST['sucursal'] : 'General';
        $responsable = !empty($_POST['responsable']) ? $_POST['responsable'] : 'Sin asignar';
        $usuarios_vinculados = isset($_POST['usuarios_vinculados']) && is_array($_POST['usuarios_vinculados'])
            ? array_values(array_unique(array_filter(array_map('intval', $_POST['usuarios_vinculados']))))
            : array();
        $tarea_id_guardada = $id;
        $usuario_form = isset($_POST['useru']) && $_POST['useru'] !== '' ? trim((string) $_POST['useru']) : null;
        $tarea_antes = $id ? grant_obtener_tarea($pdo, $id) : null;
        $estado_form = grant_normalizar_estado(isset($_POST['estado']) ? $_POST['estado'] : 'Pendiente');
        $progreso_form = isset($_POST['progreso']) ? max(0, min(100, (int) $_POST['progreso'])) : 0;
        if ($estado_form === 'Completada') {
            $progreso_form = 100;
        }

        $pdo->beginTransaction();

        if ($id) {
            $campos_form = array(
                'titulo' => $_POST['titulo'],
                'fecha_inicio' => grant_fecha_iso($_POST['fecha_inicio']),
                'fecha_fin' => grant_fecha_iso($_POST['fecha_fin']),
                'progreso' => $progreso_form,
                'estado' => $estado_form,
                'dependencia' => $dependencia,
                'sucursal' => $sucursal,
                'responsable' => $responsable
            );
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) $campos_form['updated_by'] = $usuario_form;
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) $campos_form['updated_at'] = date('Y-m-d H:i:s');
            if ($estado_form === 'Completada' && grant_columna_existe($pdo, 'tareas', 'culminada_por')) $campos_form['culminada_por'] = $usuario_form;
            if ($estado_form === 'Completada' && grant_columna_existe($pdo, 'tareas', 'culminada_en')) $campos_form['culminada_en'] = date('Y-m-d H:i:s');
            grant_actualizar_campos_tarea($pdo, $id, $campos_form);
        } else {
            $campos_form = array(
                'titulo' => $_POST['titulo'],
                'fecha_inicio' => grant_fecha_iso($_POST['fecha_inicio']),
                'fecha_fin' => grant_fecha_iso($_POST['fecha_fin']),
                'progreso' => $progreso_form,
                'estado' => $estado_form,
                'dependencia' => $dependencia,
                'sucursal' => $sucursal,
                'responsable' => $responsable
            );
            if (grant_columna_existe($pdo, 'tareas', 'prioridad')) $campos_form['prioridad'] = 'Normal';
            if (grant_columna_existe($pdo, 'tareas', 'created_by')) $campos_form['created_by'] = $usuario_form;
            if (grant_columna_existe($pdo, 'tareas', 'updated_by')) $campos_form['updated_by'] = $usuario_form;
            if (grant_columna_existe($pdo, 'tareas', 'created_at')) $campos_form['created_at'] = date('Y-m-d H:i:s');
            if (grant_columna_existe($pdo, 'tareas', 'updated_at')) $campos_form['updated_at'] = date('Y-m-d H:i:s');

            $columnas_form = array_keys($campos_form);
            $stmt = $pdo->prepare("
                INSERT INTO tareas (`" . implode('`, `', $columnas_form) . "`)
                VALUES (" . implode(', ', array_fill(0, count($columnas_form), '?')) . ")
            ");
            $stmt->execute(array_values($campos_form));
            $tarea_id_guardada = (int) $pdo->lastInsertId();
        }

        if (grant_tabla_existe($pdo, 'tarea_usuarios')) {
            $pdo->prepare("DELETE FROM tarea_usuarios WHERE tarea_id=?")->execute(array($tarea_id_guardada));
        }
        if (!empty($usuarios_vinculados) && grant_tabla_existe($pdo, 'tarea_usuarios')) {
            $stmtUsuarioTarea = $pdo->prepare("
                INSERT INTO tarea_usuarios (tarea_id, cod_usuario)
                VALUES (?, ?)
            ");
            foreach ($usuarios_vinculados as $cod_usuario) {
                $stmtUsuarioTarea->execute(array($tarea_id_guardada, $cod_usuario));
            }
        }

        $tarea_despues = grant_obtener_tarea($pdo, $tarea_id_guardada);
        if ($tarea_antes) {
            grant_registrar_cambios_tarea($pdo, $tarea_id_guardada, $usuario_form, $tarea_antes, $tarea_despues, 'formulario completo');
        } else {
            grant_registrar_historial($pdo, $tarea_id_guardada, $usuario_form, 'creacion', 'tarea', null, $tarea_despues ? $tarea_despues['titulo'] : '', '', 'formulario completo');
        }

        $pdo->commit();
        grant_redireccionar(grant_url_redireccion());
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        grant_mostrar_error('No se pudo guardar la tarea.', $e->getMessage());
    }
}

// ===============================
// OBTENER TAREAS Y ORDENAR
// ===============================
$condiciones_tareas = array("(estado IS NULL OR estado <> 'Anulada')");
if (grant_columna_existe($pdo, 'tareas', 'deleted_at')) {
    $condiciones_tareas[] = "deleted_at IS NULL";
}
$stmt = $pdo->query("SELECT * FROM tareas WHERE " . implode(' AND ', $condiciones_tareas) . " ORDER BY fecha_inicio ASC");
$tareas_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuarios_por_tarea = [];
if (grant_tabla_existe($pdo, 'tarea_usuarios')) {
    $stmtTareaUsuarios = $pdo->query("
        SELECT tu.tarea_id,
               GROUP_CONCAT(tu.cod_usuario ORDER BY p.nombre_persona SEPARATOR ',') AS ids,
               GROUP_CONCAT(p.nombre_persona ORDER BY p.nombre_persona SEPARATOR ', ') AS nombres
        FROM tarea_usuarios tu
        INNER JOIN usuario u ON u.cod_usuario = tu.cod_usuario
        INNER JOIN persona p ON p.cod_persona = u.cod_usuario
        GROUP BY tu.tarea_id
    ");
    foreach ($stmtTareaUsuarios->fetchAll(PDO::FETCH_ASSOC) as $filaTareaUsuario) {
        $usuarios_por_tarea[$filaTareaUsuario['tarea_id']] = [
            'ids' => $filaTareaUsuario['ids'] !== '' ? array_map('intval', explode(',', $filaTareaUsuario['ids'])) : [],
            'nombres' => $filaTareaUsuario['nombres']
        ];
    }
}

foreach ($tareas_db as &$tarea_db) {
    $vinculados = isset($usuarios_por_tarea[$tarea_db['id']])
        ? $usuarios_por_tarea[$tarea_db['id']]
        : ['ids' => [], 'nombres' => ''];
    $tarea_db['usuarios_vinculados_ids'] = $vinculados['ids'];
    $tarea_db['usuarios_vinculados'] = $vinculados['nombres'];
}
unset($tarea_db);

$columna_rol_usuario = grant_columna_existe($pdo, 'usuario', 'tipo') ? 'u.tipo AS rol_usuario' : "'' AS rol_usuario";
$stmtUsuarios = $pdo->query("
    SELECT u.cod_usuario, p.nombre_persona, u.url, " . $columna_rol_usuario . "
    FROM usuario u
    INNER JOIN persona p ON p.cod_persona = u.cod_usuario
    WHERE u.estado = 'Activo'
    ORDER BY p.nombre_persona ASC
");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
$fotos_usuarios = [];
$ids_usuarios_por_nombre = [];
$roles_usuarios_por_nombre = [];
foreach ($usuarios as $usuario) {
    $nombre_usuario_foto = trim((string) $usuario['nombre_persona']);
    $url_usuario_foto = trim((string) $usuario['url']);
    $rol_usuario_foto = isset($usuario['rol_usuario']) ? trim((string) $usuario['rol_usuario']) : '';
    if ($nombre_usuario_foto !== '') {
        $fotos_usuarios[mb_strtolower($nombre_usuario_foto, 'UTF-8')] = $url_usuario_foto;
        $ids_usuarios_por_nombre[mb_strtolower($nombre_usuario_foto, 'UTF-8')] = (int) $usuario['cod_usuario'];
        $roles_usuarios_por_nombre[mb_strtolower($nombre_usuario_foto, 'UTF-8')] = $rol_usuario_foto;
    }
}

$tareas_ordenadas = [];
$visitados = [];

function armarArbolEstructurado($padre_id, $lista, $nivel, &$resultado, &$visitados)
{
    foreach ($lista as $t) {
        $es_hijo = ($padre_id === null && empty($t['dependencia'])) || ((string) $t['dependencia'] === (string) $padre_id);

        if ($es_hijo && !in_array($t['id'], $visitados)) {
            $visitados[] = $t['id'];

            // Lógica de Alertas Preventivas (48hs de margen)
            $alerta = '';
            $fecha_fin_ts = strtotime($t['fecha_fin']);
            $ahora_ts = time();
            $horas_restantes = ($fecha_fin_ts - $ahora_ts) / 3600;

            if ($t['estado'] != 'Completada' && $horas_restantes > 0 && $horas_restantes <= 48) {
                $alerta = ' <span style="color:red; font-weight:bold;" title="¡Vence en menos de 48hs!">⚠️</span>';
            } elseif ($t['estado'] != 'Completada' && $horas_restantes < 0) {
                $alerta = ' <span style="color:darkred; font-weight:bold;" title="¡Vencida!">🚨</span>';
            }

            $t['titulo_html'] = str_repeat("&nbsp;&nbsp;&nbsp;", $nivel) . ($nivel > 0 ? "↳ " : "") . htmlspecialchars($t['titulo'], ENT_QUOTES) . $alerta;
            $t['titulo_gantt'] = str_repeat("   ", $nivel) . ($nivel > 0 ? "↳ " : "") . htmlspecialchars($t['titulo'], ENT_QUOTES);

            $resultado[] = $t;
            armarArbolEstructurado($t['id'], $lista, $nivel + 1, $resultado, $visitados);
        }
    }
}

armarArbolEstructurado(null, $tareas_db, 0, $tareas_ordenadas, $visitados);

// Failsafe
foreach ($tareas_db as $t) {
    if (!in_array($t['id'], $visitados)) {
        $t['titulo_html'] = "⚠️ " . htmlspecialchars($t['titulo'], ENT_QUOTES);
        $t['titulo_gantt'] = "⚠️ " . htmlspecialchars($t['titulo'], ENT_QUOTES);
        $tareas_ordenadas[] = $t;
    }
}

// ===============================
// FORMATO JSON PARA EL GANTT <option value="Todas">Todas las Sucursales</option>

// ===============================
$tareas_gantt = [];
$ultimos_cambios_tareas = grant_obtener_ultimos_cambios_tareas($pdo);
foreach ($tareas_ordenadas as $t) {
    // Colores dinámicos por sucursal
    $class = 'bar-general';
    if ($t['sucursal'] == 'Villa Morra')
        $class = 'bar-villamorra';
    if ($t['sucursal'] == 'Cerro Corá')
        $class = 'bar-CerroCorá';
    if ($t['sucursal'] == 'Oviedo')
        $class = 'bar-Oviedo';
    if ($t['sucursal'] == 'San Lorenzo')
        $class = 'bar-SanLorenzo';
    if ($t['sucursal'] == 'Santa Librada')
        $class = 'bar-SantaLibrada';


    // Si está completada, forzamos verde
    $estadoTarea = trim((string) $t['estado']);
    $estadoVisual = $estadoTarea !== '' ? $estadoTarea : 'Pendiente';
    $classEstado = 'bar-estado-pendiente';
    $fechaFinTs = strtotime($t['fecha_fin']);
    $hoyTs = strtotime(date('Y-m-d'));

    if ($estadoTarea == 'Completada') {
        $classEstado = 'bar-estado-completada';
        $estadoVisual = 'Culminada';
    } elseif ($estadoTarea == 'Anulada') {
        $classEstado = 'bar-estado-anulada';
        $estadoVisual = 'Anulada';
    } elseif ($fechaFinTs !== false && $fechaFinTs < $hoyTs) {
        $classEstado = 'bar-estado-vencida';
        $estadoVisual = 'Vencida';
    } elseif ($estadoTarea == 'En Progreso') {
        $classEstado = 'bar-estado-progreso';
        $estadoVisual = 'En progreso';
    } elseif ($fechaFinTs !== false && $fechaFinTs <= strtotime('+2 days', $hoyTs)) {
        $classEstado = 'bar-estado-proxima';
        $estadoVisual = 'Proxima a vencer';
    }

    $responsableTarea = trim((string) $t['responsable']);
    $responsableId = isset($ids_usuarios_por_nombre[mb_strtolower($responsableTarea, 'UTF-8')])
        ? $ids_usuarios_por_nombre[mb_strtolower($responsableTarea, 'UTF-8')]
        : null;
    $fotoResponsable = $responsableTarea !== ''
        ? (isset($fotos_usuarios[mb_strtolower($responsableTarea, 'UTF-8')]) ? $fotos_usuarios[mb_strtolower($responsableTarea, 'UTF-8')] : '')
        : '';
    $rolResponsable = $responsableTarea !== ''
        ? (isset($roles_usuarios_por_nombre[mb_strtolower($responsableTarea, 'UTF-8')]) ? $roles_usuarios_por_nombre[mb_strtolower($responsableTarea, 'UTF-8')] : '')
        : '';
    $nombreBarra = trim($t['titulo_gantt'] . ($responsableTarea !== '' ? ', ' . $responsableTarea : ''));

    $tareas_gantt[] = [
        'id' => (string) $t['id'],
        'name' => $nombreBarra,
        'start' => $t['fecha_inicio'],
        'end' => $t['fecha_fin'],
        'progress' => (int) $t['progreso'],
        'dependencies' => $t['dependencia'] ? (string) $t['dependencia'] : '',
        'custom_class' => $class . ' ' . $classEstado,
        'sucursal' => $t['sucursal'],
        'responsable' => $responsableTarea,
        'responsable_id' => $responsableId,
        'responsable_rol' => $rolResponsable,
        'usuarios_vinculados' => $t['usuarios_vinculados'],
        'usuarios_vinculados_ids' => $t['usuarios_vinculados_ids'],
        'estado' => $estadoTarea,
        'estado_visual' => $estadoVisual,
        'foto_responsable' => $fotoResponsable,
        'titulo' => $t['titulo'],
        'titulo_original' => $t['titulo_gantt'],
        'nombre_barra' => $nombreBarra,
        'observacion' => isset($t['observacion']) ? $t['observacion'] : '',
        'prioridad' => isset($t['prioridad']) && $t['prioridad'] !== '' ? $t['prioridad'] : 'Normal',
        'culminada_en' => isset($t['culminada_en']) ? $t['culminada_en'] : '',
        'anulada_en' => isset($t['anulada_en']) ? $t['anulada_en'] : '',
        'motivo_anulacion' => isset($t['motivo_anulacion']) ? $t['motivo_anulacion'] : '',
        'deleted_at' => isset($t['deleted_at']) ? $t['deleted_at'] : '',
        'ultimo_cambio_texto' => isset($ultimos_cambios_tareas[$t['id']])
            ? grant_formatear_ultimo_cambio($ultimos_cambios_tareas[$t['id']])
            : 'Sin cambios registrados'
    ];
}

// ---- TAREA FANTASMA para centrar la fecha actual sin cargar todo el año ----
// ---- REPORTE DE ACTIVIDADES: incluye tareas activas y anuladas para filtrar la impresion ----
$tareas_reporte = [];
try {
    $stmtReporte = $pdo->query("SELECT * FROM tareas ORDER BY fecha_inicio ASC");
    $tareas_reporte_db = $stmtReporte->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Grant.php reporte actividades: ' . $e->getMessage());
    $tareas_reporte_db = $tareas_db;
}

foreach ($tareas_reporte_db as $t) {
    $responsableTarea = isset($t['responsable']) ? trim((string) $t['responsable']) : '';
    $claveResponsable = $responsableTarea !== '' ? mb_strtolower($responsableTarea, 'UTF-8') : '';
    $responsableId = $claveResponsable !== '' && isset($ids_usuarios_por_nombre[$claveResponsable])
        ? $ids_usuarios_por_nombre[$claveResponsable]
        : null;
    $fotoResponsable = $claveResponsable !== '' && isset($fotos_usuarios[$claveResponsable])
        ? $fotos_usuarios[$claveResponsable]
        : '';
    $rolResponsable = $claveResponsable !== '' && isset($roles_usuarios_por_nombre[$claveResponsable])
        ? $roles_usuarios_por_nombre[$claveResponsable]
        : '';
    $vinculados = isset($usuarios_por_tarea[$t['id']])
        ? $usuarios_por_tarea[$t['id']]
        : ['ids' => [], 'nombres' => ''];
    $estadoTarea = isset($t['estado']) ? trim((string) $t['estado']) : 'Pendiente';
    $fechaFinTs = isset($t['fecha_fin']) ? strtotime($t['fecha_fin']) : false;
    $hoyTs = strtotime(date('Y-m-d'));
    $deletedAtReporte = isset($t['deleted_at']) ? trim((string) $t['deleted_at']) : '';
    $estadoVisual = $estadoTarea !== '' ? $estadoTarea : 'Pendiente';

    if ($estadoTarea === 'Completada') {
        $estadoVisual = 'Culminada';
    } elseif ($estadoTarea === 'Anulada' || $deletedAtReporte !== '') {
        $estadoVisual = 'Anulada';
    } elseif ($fechaFinTs !== false && $fechaFinTs < $hoyTs) {
        $estadoVisual = 'Vencida';
    } elseif ($estadoTarea === 'En Progreso') {
        $estadoVisual = 'En proceso';
    }

    $tareas_reporte[] = [
        'id' => (string) $t['id'],
        'titulo' => isset($t['titulo']) ? (string) $t['titulo'] : '',
        'name' => isset($t['titulo']) ? (string) $t['titulo'] : '',
        'start' => isset($t['fecha_inicio']) ? $t['fecha_inicio'] : '',
        'end' => isset($t['fecha_fin']) ? $t['fecha_fin'] : '',
        'fecha_inicio' => isset($t['fecha_inicio']) ? $t['fecha_inicio'] : '',
        'fecha_fin' => isset($t['fecha_fin']) ? $t['fecha_fin'] : '',
        'progress' => isset($t['progreso']) ? (int) $t['progreso'] : 0,
        'sucursal' => isset($t['sucursal']) ? (string) $t['sucursal'] : '',
        'responsable' => $responsableTarea,
        'responsable_id' => $responsableId,
        'responsable_rol' => $rolResponsable,
        'foto_responsable' => $fotoResponsable,
        'usuarios_vinculados' => $vinculados['nombres'],
        'usuarios_vinculados_ids' => $vinculados['ids'],
        'estado' => $estadoTarea,
        'estado_visual' => $estadoVisual,
        'observacion' => isset($t['observacion']) ? (string) $t['observacion'] : '',
        'prioridad' => isset($t['prioridad']) && $t['prioridad'] !== '' ? (string) $t['prioridad'] : 'Normal',
        'culminada_en' => isset($t['culminada_en']) ? (string) $t['culminada_en'] : '',
        'anulada_en' => isset($t['anulada_en']) ? (string) $t['anulada_en'] : '',
        'motivo_anulacion' => isset($t['motivo_anulacion']) ? (string) $t['motivo_anulacion'] : '',
        'deleted_at' => $deletedAtReporte
    ];
}

// ---- TAREA FANTASMA para centrar la fecha actual sin cargar todo el ano ----
$inicio_horizonte = date('Y-m-d', strtotime('-30 days'));
$fin_horizonte = date('Y-m-d', strtotime('+30 days'));
$tareas_gantt[] = [
    'id' => '__horizon__',
    'name' => '',
    'start' => $inicio_horizonte,
    'end' => $fin_horizonte,
    'progress' => 0,
    'dependencies' => '',
    'custom_class' => 'bar-hidden',
    'sucursal' => '__none__',
    'responsable' => ''
];

$json = json_encode(grant_normalizar_utf8($tareas_gantt));
if ($json === false) {
    error_log('Grant.php: json_encode fallo: ' . json_last_error_msg());
    $json = '[]';
}
$json_reporte = json_encode(grant_normalizar_utf8($tareas_reporte));
if ($json_reporte === false) {
    error_log('Grant.php: json_encode reporte fallo: ' . json_last_error_msg());
    $json_reporte = '[]';
}
$grant_dashboard_embed = isset($_GET['embed']) && $_GET['embed'] === 'dashboard';
$grant_dashboard_modal = isset($_GET['modal']) && $_GET['modal'] === '1';
$grant_body_class = trim(($grant_dashboard_embed ? 'grant-dashboard-compact' : '') . ' ' . ($grant_dashboard_modal ? 'grant-dashboard-modal' : ''));
if (ob_get_length()) {
    ob_clean();
}
?>

<!DOCTYPE html>
<html lang="es" class="<?= $grant_dashboard_embed ? 'grant-dashboard-compact-root' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FARAONE CAPITAL S.A. | Planificación Operativa</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .header {
            background: #002D62;
            color: white;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            width: 98%;
            margin: auto;
            padding-top: 15px;
        }

        .gantt-layout {
            display: flex;
            height: 60vh;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .task-list-container {
            width: 45%;
            min-width: 400px;
            border-right: 1px solid #ddd;
            overflow-y: hidden;
            background: #fafafa;
        }

        .task-table {
            width: 100%;
            border-collapse: collapse;
        }

        .task-table th {
            background: #eee;
            height: 62px;
            padding: 0 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .task-table td {
            height: 38px;
            padding: 0 10px;
            border-bottom: 1px solid #ebebeb;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gantt-right-panel {
            width: 90%;
            display: flex;
            flex-direction: column;
        }

        /* =====================================================
       BARRA DE CONTROLES UNIFICADA: Filtros + Vistas
       ===================================================== */
        .view-controls {
            background: #f8f9fa;
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .view-btn {
            background: white;
            border: 1px solid #ccc;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .view-btn.active {
            background: #002D62;
            color: white;
            border-color: #002D62;
        }

        .filter-input {
            padding: 5px;
            font-size: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .report-filter-input {
            min-width: 118px;
        }

        .task-print-btn {
            font-weight: 700;
        }

        /* Separador visual entre filtros y vistas */
        .controls-divider {
            width: 1px;
            height: 24px;
            background: #ccc;
            margin: 0 4px;
        }

        .gantt-svg-container {
            overflow-x: auto;
            overflow-y: auto;
            flex-grow: 1;
        }

        .form-card {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            align-items: end;
        }

        .form-grid input,
        .form-grid select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-save {
            background: #198754;
            color: white;
            padding: 9px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-clear {
            background: #6c757d;
            color: white;
            padding: 9px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Colores por Sucursal (Dinámicos) */
        .bar-general .bar {
            fill: #6c757d !important;
        }

        .bar-villamorra .bar {
            fill: #6f42c1 !important;
        }

        .bar-luque .bar {
            fill: #fd7e14 !important;
        }

        .bar-villaelisa .bar {
            fill: #17a2b8 !important;
        }

        .bar-completada .bar {
            fill: #198754 !important;
        }

        /* Tarea fantasma: completamente invisible */
        .bar-hidden .bar {
            fill: transparent !important;
            stroke: none !important;
        }

        .bar-hidden .bar-label {
            display: none !important;
        }

        .bar-hidden .bar-progress {
            fill: transparent !important;
        }

        /* Botón eliminar junto al de editar */
        .btn-del {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            padding: 0 2px;
            line-height: 1;
        }

        .btn-del:hover {
            opacity: 0.7;
        }

        :root {
            --grant-bg: #f6f7fb;
            --grant-panel: #ffffff;
            --grant-panel-soft: #f9fafb;
            --grant-ink: #172033;
            --grant-muted: #667085;
            --grant-line: #e4e7ec;
            --grant-blue: #1f5eff;
            --grant-green: #159947;
            --grant-danger: #dc2626;
            --grant-shadow: 0 14px 38px rgba(16, 24, 40, 0.08);
            --grant-radius: 12px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            background: var(--grant-bg);
            color: var(--grant-ink);
            font-size: 13px;
        }

        .header {
            display: none;
        }

        .header span {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 8px 12px 12px;
        }

        .gantt-return-dashboard {
            position: fixed;
            top: 12px;
            right: 14px;
            z-index: 10050;
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 7px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.96);
            color: #172033;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
        }

        .gantt-return-dashboard:hover {
            border-color: #93a4b8;
            background: #ffffff;
        }

        .gantt-return-dashboard__icon {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 16px;
            font-weight: 900;
            line-height: 1;
        }

        body.grant-dashboard-modal .gantt-return-dashboard {
            display: none;
        }

        body.grant-dashboard-compact:not(.grant-dashboard-modal) .gantt-return-dashboard {
            width: 38px;
            min-width: 38px;
            height: 38px;
            min-height: 38px;
            padding: 0;
            border-radius: 50%;
        }

        body.grant-dashboard-compact:not(.grant-dashboard-modal) .gantt-return-dashboard span:not(.gantt-return-dashboard__icon) {
            display: none;
        }

        .form-card {
            margin-bottom: 8px;
            padding: 0;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: var(--grant-panel);
            box-shadow: none;
        }

        .task-form-panel summary {
            min-height: 34px;
            padding: 8px 12px;
            cursor: pointer;
            color: var(--grant-blue);
            font-size: 12px;
            font-weight: 800;
            list-style: none;
        }

        .task-form-panel summary::-webkit-details-marker {
            display: none;
        }

        .task-form-panel summary::before {
            content: "+";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin-right: 7px;
            border-radius: 50%;
            background: #eef4ff;
        }

        .task-form-panel[open] summary::before {
            content: "-";
        }

        .task-form-panel form {
            padding: 0 12px 12px;
        }

        .gantt-welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 42px;
            margin-bottom: 8px;
            padding: 8px 12px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
        }

        .gantt-welcome h1 {
            margin: 0;
            color: var(--grant-ink);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }

        .gantt-welcome span {
            color: var(--grant-blue);
        }

        .gantt-welcome p {
            margin: 2px 0 0;
            color: var(--grant-muted);
            font-size: 12px;
        }

        .form-grid {
            grid-template-columns: minmax(260px, 2fr) repeat(3, minmax(130px, 1fr)) repeat(4, minmax(160px, 1fr)) minmax(140px, 0.9fr);
            gap: 12px;
        }

        .form-grid div {
            min-width: 0;
        }

        .form-grid label {
            display: block;
            margin-bottom: 6px;
            color: var(--grant-muted);
            font-size: 11px !important;
            font-weight: 700 !important;
            line-height: 1.2;
        }

        .form-grid input,
        .form-grid select,
        .filter-input {
            width: 100%;
            min-width: 0;
            height: 38px;
            padding: 8px 10px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--grant-ink);
            font-size: 12px;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .usuarios-vinculados-field {
            grid-column: span 2;
            position: relative;
        }

        .usuarios-vinculados-picker {
            position: relative;
        }

        .usuarios-vinculados-picker[open]::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(15, 23, 42, 0.22);
        }

        .usuarios-vinculados-picker summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            height: 38px;
            padding: 8px 10px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--grant-ink);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            list-style: none;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .usuarios-vinculados-picker summary::-webkit-details-marker {
            display: none;
        }

        .usuarios-vinculados-picker summary::after {
            content: "⌄";
            color: var(--grant-muted);
            font-size: 14px;
            line-height: 1;
            transition: transform 0.16s ease;
        }

        .usuarios-vinculados-picker[open] summary {
            border-color: var(--grant-blue);
            box-shadow: 0 0 0 3px rgba(31, 94, 255, 0.12);
        }

        .usuarios-vinculados-picker[open] summary::after {
            transform: rotate(180deg);
        }

        .usuarios-vinculados-summary {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .usuarios-vinculados-count {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #eef4ff;
            color: var(--grant-blue);
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .usuarios-vinculados-panel {
            position: fixed;
            z-index: 90;
            top: 50%;
            left: 50%;
            width: min(620px, calc(100vw - 28px));
            max-height: min(76vh, 620px);
            padding: 14px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 24px 58px rgba(16, 24, 40, 0.26);
            transform: translate(-50%, -50%);
        }

        .usuarios-vinculados-panel-title {
            margin: 0 0 10px;
            color: var(--grant-ink);
            font-size: 14px;
            font-weight: 800;
        }

        .usuarios-vinculados-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 10px;
        }

        .usuarios-vinculados-actions button {
            height: 28px;
            padding: 0 10px;
            border: 1px solid var(--grant-line);
            border-radius: 7px;
            background: #fff;
            color: var(--grant-muted);
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .usuarios-vinculados-actions button:last-child {
            border-color: var(--grant-blue);
            background: var(--grant-blue);
            color: #fff;
        }

        .usuarios-checkbox-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 7px;
            max-height: calc(min(76vh, 620px) - 110px);
            overflow: auto;
            padding: 8px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #f9fafb;
        }

        .usuarios-checkbox-list label {
            display: flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            margin: 0;
            padding: 6px 8px;
            border: 1px solid #e7ebf1;
            border-radius: 8px;
            background: #fff;
            color: var(--grant-ink);
            font-size: 12px !important;
            font-weight: 600 !important;
            cursor: pointer;
            transition: background-color 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
        }

        .usuarios-checkbox-list label:hover {
            border-color: #b8c7e6;
            background: #f8fbff;
        }

        .usuarios-checkbox-list input {
            width: 15px;
            height: 15px;
            min-width: 15px;
            margin: 0;
            padding: 0;
            accent-color: var(--grant-blue);
        }

        .usuarios-checkbox-list label.is-selected {
            border-color: rgba(31, 94, 255, 0.45);
            background: #eef4ff;
            box-shadow: 0 0 0 2px rgba(31, 94, 255, 0.08);
            color: #173b91;
        }

        .usuarios-checkbox-list span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .form-grid input:focus,
        .form-grid select:focus,
        .filter-input:focus {
            border-color: var(--grant-blue);
            box-shadow: 0 0 0 3px rgba(31, 94, 255, 0.12);
        }

        .btn-save,
        .btn-clear,
        .view-btn {
            height: 38px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            transition: background-color 0.16s ease, border-color 0.16s ease, color 0.16s ease, transform 0.12s ease;
        }

        .btn-save {
            padding: 0 14px;
            background: var(--grant-green);
        }

        .btn-save:hover {
            background: #0f7a38;
        }

        .btn-save:disabled,
        .btn-save.is-saving {
            cursor: not-allowed;
            opacity: 0.72;
            background: #6b8f7b;
        }

        .btn-clear {
            min-width: 42px;
            padding: 0 12px;
            background: #eef2f7;
            color: var(--grant-muted);
        }

        .btn-clear:hover {
            background: #e2e8f0;
            color: var(--grant-danger);
        }

        .btn-save:active,
        .btn-clear:active,
        .view-btn:active {
            transform: translateY(1px);
        }

        .gantt-layout {
            height: 640px;
            min-height: 0;
            border: 1px solid var(--grant-line);
            border-radius: var(--grant-radius);
            background: var(--grant-panel);
            box-shadow: var(--grant-shadow);
        }

        .task-list-container {
            width: 42%;
            min-width: 390px;
            border-right: 1px solid var(--grant-line);
            background: #fff;
        }

        .task-table {
            table-layout: fixed;
            background: #fff;
        }

        .task-table tbody {
            background: #fff;
        }

        .task-table th {
            position: sticky;
            top: 0;
            z-index: 5;
            height: var(--gantt-controls-height, 54px);
            padding: 0 12px;
            border-bottom: 1px solid var(--grant-line);
            background: var(--grant-panel-soft);
            color: var(--grant-muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .task-table th:first-child {
            width: 36%;
        }

        .task-table th:nth-child(2),
        .task-table th:nth-child(3),
        .task-table th:nth-child(4) {
            width: 18%;
        }

        .task-table td {
            height: var(--gantt-row-height, 40px);
            padding: 0 12px;
            border-bottom: 1px solid #eef0f4;
            background: #fff;
            color: #344054;
            font-size: 12px;
        }

        .task-table .gantt-date-spacer td {
            height: var(--gantt-date-header-height, 58px);
            padding: 0;
            border-bottom: 1px solid var(--grant-line);
            background: #fff;
        }

        .task-table td strong {
            color: var(--grant-ink);
            font-weight: 700;
        }

        .task-table .task-row:nth-child(even) td {
            background: #fcfcfd;
        }

        .task-table tbody tr:hover td {
            background: #f8fbff;
        }

        .task-table a[title="Editar"],
        .task-table a[title="Eliminar"],
        .task-table a[title="Anular tarea"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: #f2f4f7;
            color: var(--grant-ink);
            text-decoration: none;
            transition: background-color 0.16s ease, transform 0.12s ease;
        }

        .task-table a[title="Editar"]:hover {
            background: #dbeafe;
        }

        .task-table a[title="Eliminar"]:hover,
        .task-table a[title="Anular tarea"]:hover {
            background: #fee2e2;
        }

        .task-table a[title="Editar"]:active,
        .task-table a[title="Eliminar"]:active,
        .task-table a[title="Anular tarea"]:active {
            transform: scale(0.96);
        }

        .gantt-right-panel {
            width: 58%;
            min-width: 0;
            background: #fff;
        }

        .view-controls {
            min-height: 54px;
            padding: 9px 12px;
            border-bottom: 1px solid var(--grant-line);
            background: var(--grant-panel-soft);
            color: var(--grant-muted);
            font-size: 12px;
        }

        .view-controls strong {
            color: var(--grant-ink);
            font-size: 12px;
        }

        .filter-input {
            width: auto;
            min-width: 180px;
            max-width: 260px;
            height: 34px;
            background: #fff;
        }

        .view-btn {
            height: 34px;
            padding: 0 12px;
            border: 1px solid var(--grant-line);
            background: #fff;
            color: var(--grant-muted);
        }

        .view-btn:hover {
            border-color: #b8c3d6;
            color: var(--grant-ink);
        }

        .view-btn.active {
            border-color: var(--grant-blue);
            background: var(--grant-blue);
            color: white;
        }

        .controls-divider {
            background: var(--grant-line);
        }

        .gantt-svg-container {
            min-height: 0;
            background: #fff;
        }

        .gantt .grid-header {
            fill: #f9fafb;
        }

        .gantt .grid-row {
            fill: #fff;
        }

        .gantt .grid-row:nth-child(even) {
            fill: #fcfcfd;
        }

        .gantt .row-line {
            stroke: #eef0f4;
        }

        .gantt .tick,
        .gantt .today-highlight {
            stroke: #edf1f7;
        }

        .gantt .bar {
            rx: 5;
            ry: 5;
        }

        .gantt .bar-label {
            fill: #fff !important;
            font-size: 10px;
            font-weight: 800;
            paint-order: stroke;
            stroke: rgba(15, 23, 42, 0.38);
            stroke-width: 1.8px;
            stroke-linejoin: round;
        }

        .gantt .grant-avatar {
            pointer-events: none;
        }

        .gantt .grant-avatar-fallback {
            pointer-events: none;
        }

        .gantt .bar-wrapper .bar-label {
            dominant-baseline: middle;
            text-rendering: geometricPrecision;
        }

        .gantt .lower-text,
        .gantt .upper-text {
            fill: var(--grant-muted);
            font-size: 10px;
        }

        .gantt-container .popup-wrapper {
            display: none !important;
        }

        .bar-general .bar {
            fill: #64748b !important;
        }

        .bar-CerroCora .bar {
            fill: #0ea5e9 !important;
        }

        .bar-villamorra .bar {
            fill: #7c3aed !important;
        }

        .bar-CerroCorá .bar,
        .bar-CerroCorá .bar {
            fill: #0ea5e9 !important;
        }

        .bar-Oviedo .bar {
            fill: #f97316 !important;
        }

        .bar-SanLorenzo .bar {
            fill: #2563eb !important;
        }

        .bar-SantaLibrada .bar {
            fill: #db2777 !important;
        }

        .bar-completada .bar {
            fill: var(--grant-green) !important;
        }

        .bar-estado-pendiente .bar {
            fill: #475569 !important;
        }

        .bar-estado-progreso .bar {
            fill: #1d4ed8 !important;
        }

        .bar-estado-proxima .bar {
            fill: #b45309 !important;
        }

        .bar-estado-vencida .bar {
            fill: #b91c1c !important;
        }

        .bar-estado-completada .bar {
            fill: #047857 !important;
        }

        .bar-estado-anulada .bar {
            fill: #6b7280 !important;
            opacity: 0.78;
        }

        .bar-estado-pendiente .bar-progress,
        .bar-estado-progreso .bar-progress,
        .bar-estado-proxima .bar-progress,
        .bar-estado-vencida .bar-progress,
        .bar-estado-completada .bar-progress,
        .bar-estado-anulada .bar-progress {
            fill: rgba(255, 255, 255, 0.28) !important;
        }

        .gantt .bar-wrapper {
            cursor: pointer;
        }

        .gantt-tooltip-custom {
            position: fixed;
            z-index: 99999;
            max-width: 300px;
            padding: 10px 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.96);
            color: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.2);
            pointer-events: none;
            opacity: 0;
            transform: translateY(4px);
            transition: opacity 0.12s ease, transform 0.12s ease;
        }

        .gantt-tooltip-custom.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .gantt-tooltip-custom__title {
            margin-bottom: 7px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.25;
        }

        .gantt-tooltip-custom__meta {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 4px 8px;
            color: #dbeafe;
            font-size: 11px;
            line-height: 1.35;
        }

        .gantt-tooltip-custom__meta b {
            color: #93c5fd;
            font-weight: 800;
        }

        .gantt-quick-create {
            position: fixed;
            z-index: 100020;
            width: min(260px, calc(100vw - 24px));
            padding: 6px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 18px 44px rgba(16, 24, 40, 0.22);
        }

        .gantt-quick-create input,
        .gantt-quick-panel input,
        .gantt-quick-panel select,
        .gantt-quick-panel textarea,
        .gantt-dialog select,
        .gantt-dialog textarea {
            width: 100%;
            min-width: 0;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
            color: var(--grant-ink);
            font-size: 12px;
            outline: none;
        }

        .gantt-quick-create input {
            height: 34px;
            padding: 8px 10px;
        }

        .gantt-quick-panel {
            position: fixed;
            z-index: 100010;
            top: 14px;
            right: 14px;
            width: min(390px, calc(100vw - 28px));
            max-height: calc(100vh - 28px);
            overflow: auto;
            padding: 14px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(16, 24, 40, 0.24);
        }

        .gantt-quick-panel__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .gantt-quick-panel__header h2 {
            margin: 4px 0 0;
            color: var(--grant-ink);
            font-size: 16px;
            line-height: 1.25;
        }

        .gantt-quick-panel__eyebrow {
            display: inline-flex;
            min-height: 22px;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            background: #eef4ff;
            color: var(--grant-blue);
            font-size: 11px;
            font-weight: 800;
        }

        .gantt-icon-btn {
            width: 30px;
            height: 30px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
            color: var(--grant-muted);
            cursor: pointer;
            font-weight: 800;
        }

        .gantt-quick-panel label,
        .gantt-dialog label {
            display: block;
            margin: 8px 0 5px;
            color: var(--grant-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .gantt-quick-panel input,
        .gantt-quick-panel select {
            height: 36px;
            padding: 8px 10px;
        }

        .gantt-quick-panel textarea,
        .gantt-dialog textarea {
            min-height: 72px;
            padding: 8px 10px;
            resize: vertical;
        }

        .gantt-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .gantt-quick-panel__last {
            margin: 10px 0 0;
            color: var(--grant-muted);
            font-size: 11px;
            line-height: 1.35;
        }

        .gantt-quick-panel__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .gantt-quick-panel__actions button {
            flex: 1 1 auto;
        }

        .gantt-danger-link {
            color: #b42318;
            border-color: #fecaca;
        }

        .gantt-quick-panel__history {
            margin-top: 10px;
            padding: 8px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #f9fafb;
            color: #344054;
            font-size: 11px;
            line-height: 1.4;
        }

        .gantt-history-item {
            padding: 7px 0;
            border-bottom: 1px solid #e7ebf1;
        }

        .gantt-history-item:last-child {
            border-bottom: 0;
        }

        .gantt-history-item strong {
            display: block;
            color: var(--grant-ink);
            font-size: 12px;
        }

        .gantt-dialog-backdrop {
            position: fixed;
            inset: 0;
            z-index: 100030;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.28);
        }

        .gantt-dialog {
            width: min(420px, 100%);
            padding: 16px;
            border: 1px solid var(--grant-line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(16, 24, 40, 0.28);
        }

        .gantt-dialog h3 {
            margin: 0 0 6px;
            color: var(--grant-ink);
            font-size: 16px;
        }

        .gantt-dialog p {
            margin: 0 0 10px;
            color: var(--grant-muted);
            font-size: 12px;
            line-height: 1.4;
        }

        .gantt-dialog select {
            height: 36px;
            padding: 8px 10px;
        }

        .gantt-dialog__actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 12px;
        }

        .gantt-btn-danger {
            background: #b42318 !important;
        }

        .gantt-toast-zone {
            position: fixed;
            z-index: 100040;
            right: 14px;
            bottom: 14px;
            display: grid;
            gap: 8px;
            width: min(340px, calc(100vw - 28px));
        }

        .gantt-toast {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: #111827;
            color: #fff;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.22);
            font-size: 12px;
        }

        .gantt-toast button {
            border: 0;
            background: transparent;
            color: #93c5fd;
            cursor: pointer;
            font-size: 12px;
            font-weight: 800;
        }

        @media (max-width: 1280px) {
            .form-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .gantt-layout {
                height: 640px;
            }
        }

        @media (max-width: 980px) {
            .header {
                padding: 12px 16px;
                font-size: 15px;
            }

            .container {
                padding: 12px;
            }

            .gantt-layout {
                flex-direction: column;
                height: auto;
                min-height: 0;
                overflow: visible;
            }

            .task-list-container,
            .gantt-right-panel {
                width: 100%;
                min-width: 0;
            }

            .task-list-container {
                max-height: 320px;
                overflow: auto;
                border-right: 0;
                border-bottom: 1px solid var(--grant-line);
            }

            .gantt-right-panel {
                min-height: 420px;
            }

            .gantt-svg-container {
                min-height: 360px;
            }
        }

        @media (max-width: 680px) {
            body {
                font-size: 12px;
            }

            .header span {
                white-space: normal;
                line-height: 1.3;
            }

            .form-card {
                padding: 12px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .form-grid input,
            .form-grid select,
            .btn-save,
            .btn-clear {
                height: 42px;
                font-size: 13px;
            }

            .view-controls {
                align-items: stretch;
                gap: 7px;
            }

            .view-controls strong,
            .controls-divider {
                display: none;
            }

            .filter-input,
            .view-btn {
                width: 100%;
                max-width: none;
            }

            .task-list-container {
                overflow-x: auto;
            }

            .task-table {
                min-width: 680px;
            }

            .gantt-right-panel {
                min-height: 380px;
            }

            .gantt-svg-container {
                min-height: 320px;
            }
        }
        .task-list-container,
        .gantt-right-panel {
            transition: flex-basis 0.28s ease, width 0.28s ease, min-width 0.28s ease, opacity 0.2s ease, border-color 0.2s ease;
        }

        .task-list-container {
            flex: 0 0 42%;
            max-width: 42%;
        }

        .gantt-right-panel {
            flex: 1 1 0;
            width: auto !important;
        }

        .gantt-layout.task-list-collapsed .task-list-container {
            flex-basis: 0 !important;
            width: 0 !important;
            min-width: 0 !important;
            max-width: 0 !important;
            border-right: 0;
            opacity: 0;
            pointer-events: none;
        }

        .gantt-layout.task-list-collapsed .gantt-right-panel {
            width: 100% !important;
        }

        @media (max-width: 980px) {
            .task-list-container {
                flex: 0 0 auto;
                width: 100%;
                max-width: none;
            }

            .gantt-right-panel {
                flex: 1 1 auto;
                width: 100% !important;
            }

            .gantt-layout.task-list-collapsed .task-list-container {
                height: 0 !important;
                max-height: 0 !important;
                overflow: hidden !important;
                border-bottom: 0;
            }
        }

        .task-toggle-btn {
            margin-left: auto;
            border-color: var(--grant-blue);
            color: var(--grant-blue);
        }

        .task-toggle-btn.active {
            background: #eef4ff;
            color: var(--grant-blue);
        }

        .task-print-btn {
            border-color: #16a34a;
            color: #15803d;
        }

        .task-print-btn:hover {
            border-color: #15803d;
            color: #166534;
            background: #f0fdf4;
        }

        .gantt-layout {
            height: 640px !important;
            min-height: 0 !important;
            overflow: visible !important;
        }

        .task-form-panel {
            padding: 0 !important;
        }

        .gantt-svg-container {
            flex: 0 0 auto !important;
            min-height: 360px;
            overflow-x: auto !important;
            overflow-y: visible !important;
        }

        /* Vista compacta cuando el Gantt se muestra dentro del dashboard */
        html.grant-dashboard-compact-root,
        html.grant-dashboard-compact-root body {
            overflow-x: hidden !important;
        }

        body.grant-dashboard-compact {
            display: block;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            background: #ffffff;
        }

        body.grant-dashboard-compact .container {
            height: 100%;
            min-height: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow: hidden;
            background: #ffffff;
        }

        body.grant-dashboard-compact .gantt-welcome {
            display: none !important;
        }

        body.grant-dashboard-compact .task-form-panel {
            flex: 0 0 auto;
            margin: 0 0 8px !important;
        }

        body.grant-dashboard-compact .task-form-panel:not([open]) {
            display: none;
        }

        body.grant-dashboard-compact .task-form-panel[open] {
            display: block;
            max-height: 270px;
            overflow: auto;
        }

        body.grant-dashboard-compact .task-form-panel summary {
            min-height: 30px;
            padding: 6px 10px;
        }

        body.grant-dashboard-compact .task-form-panel form {
            padding: 0 10px 10px;
        }

        body.grant-dashboard-compact .form-grid {
            gap: 8px;
        }

        body.grant-dashboard-compact .form-grid input,
        body.grant-dashboard-compact .form-grid select {
            height: 32px;
            padding: 6px 8px;
        }

        body.grant-dashboard-compact .gantt-layout {
            flex: 1 1 auto;
            align-items: stretch;
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            border-radius: 8px;
            box-shadow: none;
        }

        body.grant-dashboard-compact .gantt-right-panel {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 0;
            align-self: stretch;
            overflow-x: visible;
            overflow-y: hidden;
        }

        body.grant-dashboard-compact .view-controls {
            position: sticky;
            top: 0;
            z-index: 20;
            min-height: 44px;
            padding: 6px 8px;
            gap: 6px;
            flex: 0 0 auto;
        }

        body.grant-dashboard-compact .view-controls strong {
            font-size: 11px;
        }

        body.grant-dashboard-compact .filter-input {
            height: 30px;
            min-width: 150px;
            max-width: 210px;
            padding: 5px 8px;
            font-size: 11px;
        }

        body.grant-dashboard-compact .view-btn {
            height: 30px;
            padding: 0 10px;
            font-size: 11px;
        }

        body.grant-dashboard-compact .controls-divider {
            height: 20px;
        }

        body.grant-dashboard-compact .gantt-svg-container {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            scrollbar-gutter: auto;
            padding-bottom: 0;
        }

        body.grant-dashboard-compact .gantt-svg-container .gantt-container {
            overflow-x: hidden !important;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        body.grant-dashboard-compact .gantt-svg-container::-webkit-scrollbar:horizontal,
        body.grant-dashboard-compact .gantt-svg-container .gantt-container::-webkit-scrollbar,
        body.grant-dashboard-compact .gantt-svg-container .gantt-container::-webkit-scrollbar:horizontal {
            width: 0;
            height: 0;
            display: none;
        }

        body.grant-dashboard-compact .task-list-container {
            flex: 0 0 360px;
            max-width: 360px;
            min-width: 320px;
            overflow: auto;
        }

        body.grant-dashboard-compact .gantt-layout.task-list-collapsed .task-list-container {
            flex-basis: 0 !important;
            min-width: 0 !important;
            max-width: 0 !important;
        }

        @media (max-width: 980px) {
            body.grant-dashboard-compact .task-list-container {
                flex: 0 0 auto;
                width: 100%;
                max-width: none;
                min-width: 0;
                max-height: 260px;
            }

            body.grant-dashboard-compact .gantt-layout.task-list-collapsed .task-list-container {
                height: 0 !important;
                max-height: 0 !important;
                overflow: hidden !important;
            }
        }

        @media (max-width: 680px) {
            body.grant-dashboard-compact .container {
                padding: 0;
            }

            body.grant-dashboard-compact .view-controls {
                position: static;
            }

            body.grant-dashboard-compact .filter-input,
            body.grant-dashboard-compact .view-btn {
                width: auto;
                min-width: 0;
                max-width: none;
                flex: 1 1 120px;
            }

            body.grant-dashboard-compact .task-list-container {
                flex-basis: 220px;
                width: 100%;
                max-width: none;
                min-width: 0;
                max-height: 220px;
            }
        }

    </style>
</head>

<body class="<?= htmlspecialchars($grant_body_class, ENT_QUOTES, 'UTF-8') ?>">

    <button type="button" class="gantt-return-dashboard" onclick="volverDashboardDesdeGantt()" title="Volver al dashboard" aria-label="Cerrar diagrama de gant">
        <span class="gantt-return-dashboard__icon" aria-hidden="true">&times;</span>
        <span>Cerrar</span>
    </button>

    <div class="header">
        <span>FARAONE CAPITAL SOCIEDAD ANONIMA | Planificación de Expansión Operativa</span>
    </div>

<div class="container">
    
    <details class="form-card task-form-panel" id="task-form-panel">
        <summary>Nueva tarea</summary>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') ?>" id="taskForm" class="form-grid">
            <input type="hidden" name="id" id="form_id" value="">
            <input type="hidden" name="useru" id="grant_form_useru" value="">
            <input type="hidden" name="passu" id="grant_form_passu" value="">
            <input type="hidden" name="navegador" id="grant_form_navegador" value="">
            
            <div>
                <label style="font-size: 12px; font-weight:bold;" id="form_title">Nueva Tarea:</label>
                <input type="text" name="titulo" id="form_titulo" placeholder="Ej: Instalación red local" required>
            </div>
            <div>
                <label style="font-size: 12px;">Inicio:</label>
                <input type="date" name="fecha_inicio" id="form_inicio" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label style="font-size: 12px;">Fin:</label>
                <input type="date" name="fecha_fin" id="form_fin" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label style="font-size: 12px;">Progreso (%):</label>
                <input type="number" name="progreso" id="form_progreso" min="0" max="100" value="0" required>
            </div>
            <div>
                <label style="font-size: 12px;">Estado:</label>
                <select name="estado" id="form_estado">
                    <option value="Pendiente">Pendiente</option>
                    <option value="En Progreso">En Progreso</option>
                    <option value="Completada">Culminada</option>
                </select>
            </div>
            <div>
                <label style="font-size: 12px;">Sucursal / Proyecto:</label>
                <select name="sucursal" id="form_sucursal">
                     <option value="Todas">Todas las Sucursales</option>
                    <option value="Villa Morra">Villa Morra</option>
                    <option value="Cerro Corá">Cerro Corá</option>
                    <option value="Oviedo">Oviedo</option>
                    <option value="San Lorenzo">San Lorenzo</option>
                    <option value="Santa Librada">Santa Librada</option>
                </select>
            </div>
            <div>
                <label style="font-size: 12px;">Responsable:</label>
                <select name="responsable" id="form_responsable">
                    <option value="">Sin asignar</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= htmlspecialchars($usuario['nombre_persona'], ENT_QUOTES) ?>"
                            data-usuario-id="<?= (int) $usuario['cod_usuario'] ?>"
                            data-rol="<?= htmlspecialchars((isset($usuario['rol_usuario']) ? $usuario['rol_usuario'] : ''), ENT_QUOTES) ?>">
                            <?= htmlspecialchars($usuario['nombre_persona'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="usuarios-vinculados-field">
                <label style="font-size: 12px;">Usuarios vinculados:</label>
                <details class="usuarios-vinculados-picker" id="usuarios_vinculados_picker">
                    <summary>
                        <span class="usuarios-vinculados-summary" id="usuarios_vinculados_resumen">Seleccionar usuarios</span>
                        <span class="usuarios-vinculados-count" id="usuarios_vinculados_count">0 seleccionados</span>
                    </summary>
                    <div class="usuarios-vinculados-panel">
                        <h3 class="usuarios-vinculados-panel-title">Seleccionar usuarios vinculados</h3>
                        <div class="usuarios-checkbox-list" id="form_usuarios_vinculados">
                            <?php foreach ($usuarios as $usuario): ?>
                                <label>
                                    <input type="checkbox" name="usuarios_vinculados[]" value="<?= (int) $usuario['cod_usuario'] ?>">
                                    <span><?= htmlspecialchars($usuario['nombre_persona'], ENT_QUOTES) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="usuarios-vinculados-actions">
                            <button type="button" onclick="limpiarUsuariosVinculados()">Limpiar</button>
                            <button type="button" onclick="cerrarUsuariosVinculados()">Listo</button>
                        </div>
                    </div>
                </details>
            </div>
            <div>
                <label style="font-size: 12px;">Depende de:</label>
                <select name="dependencia" id="form_dependencia">
                    <option value="">(Tarea Principal)</option>
                    <?php foreach ($tareas_ordenadas as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= strip_tags($t['titulo_html']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 5px;">
                <button class="btn-save" type="submit" id="btn_submit" style="flex: 1;">Guardar</button>
                <button class="btn-clear" type="button" onclick="resetForm()">X</button>
            </div>
        </form>
    </details>

        <section class="gantt-welcome" aria-label="Bienvenida">
            <div>
                <h1>Bienvenido <span id="grant-user-name">usuario</span></h1>
                <p>Planifica tus actividades y revisa el avance operativo.</p>
            </div>
        </section>

        <div class="gantt-layout task-list-collapsed" id="gantt-layout">

            <div class="task-list-container" id="list-container">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Flujo de Tareas</th>
                            <th>Sucursal</th>
                            <th>Responsable</th>
                            <th>Vinculados</th>
                            <th style="width: 70px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <tr class="gantt-date-spacer" aria-hidden="true">
                            <td colspan="5"></td>
                        </tr>
                        <?php foreach ($tareas_ordenadas as $t): ?>
                            <tr class="task-row" data-task-id="<?= $t['id'] ?>" data-sucursal="<?= $t['sucursal'] ?>"
                                data-responsable="<?= strtolower($t['responsable']) ?>">
                                <td><strong><?= $t['titulo_html'] ?></strong></td>
                                <td><?= htmlspecialchars($t['sucursal'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($t['responsable'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($t['usuarios_vinculados'], ENT_QUOTES) ?></td>
                                <td>
                                    <!-- BOTÓN EDITAR -->
                                    <a style="cursor:pointer;" title="Editar"
                                        onclick='editarTarea(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</a>

                                    <!-- BOTÓN ELIMINAR (con confirmación) -->
                                    <a style="cursor:pointer;" title="Eliminar"
                                        onclick="eliminarTarea(<?= $t['id'] ?>, '<?= addslashes(htmlspecialchars($t['titulo'], ENT_QUOTES)) ?>')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="gantt-right-panel">

                <!-- =====================================================
                 BARRA UNIFICADA: Filtros + controles Día/Semana/Mes
                 ===================================================== -->
                <div class="view-controls">
                    <strong>Filtros:</strong>

                    <select id="filtro-sucursal" class="filter-input" onchange="aplicarFiltros()">
                        <option value="Todas">Sucursal</option>
                        <option value="Villa Morra">Villa Morra</option>
                        <option value="Cerro Corá">Cerro Corá</option>
                        <option value="Oviedo">Oviedo</option>
                        <option value="San Lorenzo">San Lorenzo</option>
                        <option value="Santa Librada">Santa Librada</option>
                    </select>

                    <input type="text" id="filtro-responsable" class="filter-input" placeholder="Buscar responsable..."
                        onkeyup="aplicarFiltros()">

                    <select id="filtro-usuario" class="filter-input" onchange="aplicarFiltroUsuarioGantt()">
                        <option value="">Funcionario</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= (int) $usuario['cod_usuario'] ?>" data-nombre="<?= htmlspecialchars(mb_strtolower($usuario['nombre_persona'], 'UTF-8'), ENT_QUOTES) ?>" data-rol="<?= htmlspecialchars((isset($usuario['rol_usuario']) ? $usuario['rol_usuario'] : ''), ENT_QUOTES) ?>">
                                <?= htmlspecialchars($usuario['nombre_persona'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date" id="reporte-fecha-desde" class="filter-input report-filter-input" title="Fecha desde" onchange="aplicarFiltros()">
                    <input type="date" id="reporte-fecha-hasta" class="filter-input report-filter-input" title="Fecha hasta" onchange="aplicarFiltros()">

                    <select id="reporte-estado" class="filter-input report-filter-input" title="Estado del reporte" onchange="aplicarFiltros()">
                        <option value="Todas">Todas</option>
                        <option value="Pendiente">Pendientes</option>
                        <option value="En Progreso">En proceso</option>
                        <option value="Completada">Culminadas</option>
                        <option value="Vencida">Vencidas</option>
                        <option value="Anulada">Anuladas</option>
                    </select>

                    <!-- Separador visual -->
                    <div class="controls-divider"></div>

                    <strong>Vista:</strong>
                    <button class="view-btn" id="btn-today" onclick="irHoyGantt()" type="button">Hoy</button>
                    <button class="view-btn active" id="btn-Day" onclick="changeGanttView('Day')">Día</button>
                    <button class="view-btn" id="btn-Week" onclick="changeGanttView('Week')">Semana</button>
                    <button class="view-btn" id="btn-Month" onclick="changeGanttView('Month')">Mes</button>
                    <button class="view-btn task-toggle-btn active" id="btn-toggle-tasks" onclick="toggleTaskList()" type="button">Mostrar tareas</button>
                    <button class="view-btn task-print-btn" id="btn-preview-report" onclick="vistaPreviaReporteActividades()" type="button">Vista previa</button>
                    <button class="view-btn task-print-btn" id="btn-print-gantt" onclick="imprimirReporteActividades()" type="button">Imprimir</button>
                    <button class="view-btn task-print-btn" id="btn-generate-pdf-report" onclick="generarPdfReporteActividades()" type="button">Generar PDF</button>
                    <button class="view-btn task-print-btn" id="btn-download-pdf-report" onclick="descargarPdfReporteActividades()" type="button">Descargar PDF</button>
                </div>

                <div class="gantt-svg-container" id="gantt-container">
                    <svg id="gantt"></svg>
                </div>
            </div>

        </div>
        <div class="gantt-quick-create" id="gantt-quick-create" style="display:none;">
            <input type="text" id="gantt-quick-create-input" maxlength="180" placeholder="Titulo de la tarea">
        </div>

        <aside class="gantt-quick-panel" id="gantt-quick-panel" aria-label="Vista rapida de tarea" style="display:none;">
            <div class="gantt-quick-panel__header">
                <div>
                    <span class="gantt-quick-panel__eyebrow" id="quick_estado_badge">Pendiente</span>
                    <h2 id="quick_panel_title">Tarea</h2>
                </div>
                <button type="button" class="gantt-icon-btn" onclick="cerrarVistaRapidaTarea()" aria-label="Cerrar">x</button>
            </div>
            <input type="hidden" id="quick_tarea_id" value="">
            <label>Titulo</label>
            <input type="text" id="quick_titulo" maxlength="180">
            <div class="gantt-quick-grid">
                <div>
                    <label>Inicio</label>
                    <input type="date" id="quick_fecha_inicio">
                </div>
                <div>
                    <label>Fin</label>
                    <input type="date" id="quick_fecha_fin">
                </div>
                <div>
                    <label>Estado</label>
                    <select id="quick_estado">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En Progreso">En progreso</option>
                        <option value="Completada">Culminada</option>
                    </select>
                </div>
                <div>
                    <label>Prioridad</label>
                    <select id="quick_prioridad">
                        <option value="Normal">Normal</option>
                        <option value="Alta">Alta</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>
            </div>
            <label>Responsable</label>
            <select id="quick_responsable"></select>
            <label>Sucursal</label>
            <select id="quick_sucursal"></select>
            <label>Observacion breve</label>
            <textarea id="quick_observacion" rows="3"></textarea>
            <p class="gantt-quick-panel__last" id="quick_ultimo_cambio">Sin cambios registrados</p>
            <div class="gantt-quick-panel__history" id="quick_historial_panel" style="display:none;"></div>
            <div class="gantt-quick-panel__actions">
                <button type="button" class="btn-save" onclick="guardarVistaRapidaTarea()">Guardar</button>
                <button type="button" class="view-btn" onclick="culminarVistaRapidaTarea()">Culminar</button>
                <button type="button" class="view-btn" onclick="verHistorialVistaRapidaTarea()">Ver historial</button>
                <button type="button" class="view-btn" onclick="editarCompletoVistaRapidaTarea()">Editar completo</button>
                <button type="button" class="view-btn gantt-danger-link" onclick="abrirDialogoAnularTarea()">Anular tarea</button>
            </div>
        </aside>

        <div class="gantt-dialog-backdrop" id="gantt-anular-backdrop" style="display:none;">
            <div class="gantt-dialog" role="dialog" aria-modal="true" aria-labelledby="gantt-anular-title">
                <h3 id="gantt-anular-title">Anular esta tarea?</h3>
                <p>La tarea saldra del Gantt principal, pero quedara registrada en auditoria.</p>
                <input type="hidden" id="gantt_anular_id" value="">
                <label>Motivo</label>
                <select id="gantt_anular_motivo" onchange="actualizarMotivoAnulacionGantt()">
                    <option value="Creada por error">Creada por error</option>
                    <option value="Duplicada">Duplicada</option>
                    <option value="Ya no corresponde">Ya no corresponde</option>
                    <option value="Reprogramada en otra tarea">Reprogramada en otra tarea</option>
                    <option value="Otro motivo">Otro motivo</option>
                </select>
                <textarea id="gantt_anular_otro" rows="3" style="display:none;" placeholder="Motivo"></textarea>
                <div class="gantt-dialog__actions">
                    <button type="button" class="view-btn" onclick="cerrarDialogoAnularTarea()">Cancelar</button>
                    <button type="button" class="btn-save gantt-btn-danger" onclick="confirmarAnularTarea()">Anular tarea</button>
                </div>
            </div>
        </div>

        <div class="gantt-toast-zone" id="gantt-toast-zone" aria-live="polite"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
    <script>
        function volverDashboardDesdeGantt() {
            try {
                if (window.parent && window.parent !== window) {
                    if (typeof window.parent.cerrarDiagramaGantSistema === 'function') {
                        window.parent.cerrarDiagramaGantSistema();
                        return;
                    }

                    window.parent.location.href = '/GoodVentaAsisCap/system/inicio.html';
                    return;
                }
            } catch (e) {
            }

            if (window.history && window.history.length > 1) {
                window.history.back();
                return;
            }

            window.location.href = '/GoodVentaAsisCap/system/inicio.html';
        }

        // allTasks incluye la tarea fantasma __horizon__ que centra la vista cerca de hoy.
        const allTasks = <?= $json ?>;
        const reportTasks = <?= $json_reporte ?>;
        let gantt;
        let vistaActual = 'Day';
        let idsTareasVisiblesGantt = new Set();
        let ordenTareasVisiblesGantt = [];
        let usuarioActualGantt = '';
        let tareaGanttGuardando = false;
        let ganttBloqueoClickRapidoHasta = 0;
        let quickCreateState = null;

        function mostrarMensajeGantt(mensaje) {
            const svg = document.getElementById('gantt');
            if (!svg) return;

            svg.innerHTML =
                '<text x="16" y="34" fill="#667085" font-size="13" font-family="Arial, sans-serif">' +
                escaparHtml(mensaje) +
                '</text>';
        }

        function fechaIsoValidaGantt(valor) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(String(valor || ''))) return false;
            return !isNaN(new Date(valor + 'T00:00:00').getTime());
        }

        function renderGantt(tasksToRender) {
            document.getElementById('gantt').innerHTML = '';

            const horizonTask = allTasks.find(t => t.id === '__horizon__');
            const tareasValidas = tasksToRender.filter(function (tarea) {
                if (tarea.id === '__horizon__') return true;
                return fechaIsoValidaGantt(tarea.start) && fechaIsoValidaGantt(tarea.end);
            });
            const sinHorizon = tareasValidas.filter(t => t.id !== '__horizon__' && tareaPerteneceAlMesActual(t));
            const tareasRenderBase = horizonTask ? [...sinHorizon, horizonTask] : sinHorizon;
            const tareasRender = prepararTareasParaVistaDesdeHoy(tareasRenderBase);
            ordenTareasVisiblesGantt = sinHorizon.map(t => String(t.id));
            idsTareasVisiblesGantt = new Set(ordenTareasVisiblesGantt);
            actualizarTablaDescripcionGantt();

            if (sinHorizon.length === 0) {
                gantt = null;
                mostrarMensajeGantt('No hay tareas que coincidan con el filtro actual.');
                sincronizarTablaConBarrasGantt();
                return;
            }

            if (typeof Gantt !== 'function') {
                gantt = null;
                mostrarMensajeGantt('No se pudo cargar la libreria del diagrama de Gantt.');
                console.error('Frappe Gantt no esta disponible.');
                return;
            }

            if (tareasRender.length > 0) {
                try {
                gantt = new Gantt("#gantt", tareasRender, {
                    view_mode: vistaActual,
                    language: 'es',

                    on_date_change: function (task, start, end) {

                        if (task.id === '__horizon__') return;
                        const startStrCalculado = formatDate(start);
                        const startStr = task.fecha_inicio_original && startStrCalculado === task.start
                            ? task.fecha_inicio_original
                            : startStrCalculado;
                        const endStrCalculado = formatDate(end);
                        const endStr = task.fecha_fin_original && endStrCalculado === task.end
                            ? task.fecha_fin_original
                            : endStrCalculado;

                        enviarActualizacionBack(task.id, startStr, endStr, task.progress);
                    },
                    on_progress_change: function (task, progress) {

                        if (task.id === '__horizon__') return;
                        const startStrCalculado = formatDate(task._start);
                        const endStrCalculado = formatDate(task._end);
                        const startStr = task.fecha_inicio_original && startStrCalculado === task.start
                            ? task.fecha_inicio_original
                            : startStrCalculado;
                        const endStr = task.fecha_fin_original && endStrCalculado === task.end
                            ? task.fecha_fin_original
                            : endStrCalculado;
                        enviarActualizacionBack(task.id, startStr, endStr, progress);
                    }
                });
                gantt.change_view_mode(vistaActual);
                mostrarMesEnFechasGantt();
                configurarScrollGantt();
                setTimeout(configurarScrollGantt, 160);
                setTimeout(configurarScrollGantt, 520);
                setTimeout(sincronizarTablaConBarrasGantt, 180);
                setTimeout(sincronizarTablaConBarrasGantt, 500);
                setTimeout(decorarBarrasGanttResponsables, 180);
                setTimeout(decorarBarrasGanttResponsables, 500);
                setTimeout(configurarTooltipsGantt, 220);
                setTimeout(configurarTooltipsGantt, 540);
                setTimeout(configurarInteraccionesBarrasGantt, 240);
                setTimeout(configurarInteraccionesBarrasGantt, 560);
                programarCentradoFechaActual();
                } catch (error) {
                    gantt = null;
                    mostrarMensajeGantt('No se pudo dibujar el diagrama. Revise fechas o datos de la tarea.');
                    console.error('Error al renderizar Gantt:', error, tareasRender);
                }
            } else {
                mostrarMensajeGantt('No hay tareas que coincidan con el filtro actual.');
            }
        }

        function prepararTareasParaVistaDesdeHoy(tareas) {
            const inicioHorizon = formatDate(new Date(new Date().getTime() - (30 * 86400000)));
            const finHorizon = formatDate(new Date(new Date().getTime() + (60 * 86400000)));

            return tareas.map(function (tarea) {
                if (tarea.id === '__horizon__') {
                    return Object.assign({}, tarea, {
                        start: inicioHorizon,
                        end: finHorizon
                    });
                }

                return tarea;
            });
        }

        function programarCentradoFechaActual() {
            posicionarGanttEnHoy();
            setTimeout(posicionarGanttEnHoy, 250);
            setTimeout(posicionarGanttEnHoy, 650);
            setTimeout(posicionarGanttEnHoy, 1200);
            setTimeout(posicionarGanttEnHoy, 2000);
            setTimeout(posicionarGanttEnHoy, 3200);
            setTimeout(posicionarGanttEnHoy, 4800);
        }

        function irHoyGantt() {
            programarCentradoFechaActual();
        }

        window.posicionarGanttEnHoy = posicionarGanttEnHoy;
        window.programarCentradoFechaActual = programarCentradoFechaActual;

        function refrescarLayoutGanttVisible() {
            if (!gantt) return;

            gantt.change_view_mode(vistaActual);
            configurarScrollGantt();
            setTimeout(configurarScrollGantt, 180);
            setTimeout(configurarScrollGantt, 520);
            mostrarMesEnFechasGantt();
            setTimeout(sincronizarTablaConBarrasGantt, 120);
            setTimeout(decorarBarrasGanttResponsables, 140);
            setTimeout(configurarTooltipsGantt, 180);
            programarCentradoFechaActual();
        }

        window.refrescarLayoutGanttVisible = refrescarLayoutGanttVisible;

        function mostrarMesEnFechasGantt() {
            setTimeout(function () {
                actualizarEspaciadorFechasGantt();
                if (vistaActual !== 'Day') return;

                const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                const fechaBase = gantt && gantt.gantt_start ? new Date(gantt.gantt_start) : new Date();
                fechaBase.setHours(0, 0, 0, 0);

                document.querySelectorAll('#gantt .lower-text').forEach(function (etiqueta, index) {
                    const fechaEtiqueta = new Date(fechaBase);
                    fechaEtiqueta.setDate(fechaBase.getDate() + index);

                    etiqueta.textContent = fechaEtiqueta.getDate() + ' ' + meses[fechaEtiqueta.getMonth()];
                });
            }, 140);
        }

        function actualizarEspaciadorFechasGantt() {
            const controles = document.querySelector('.view-controls');
            if (controles) {
                document.documentElement.style.setProperty('--gantt-controls-height', controles.offsetHeight + 'px');
            }

            const primeraFilaGantt = document.querySelector('#gantt .grid-row');
            if (!primeraFilaGantt) return;

            try {
                const caja = primeraFilaGantt.getBBox();
                const altoEncabezado = Math.max(0, Math.round(caja.y));
                document.documentElement.style.setProperty('--gantt-date-header-height', altoEncabezado + 'px');
                document.documentElement.style.setProperty('--gantt-row-height', Math.round(caja.height) + 'px');
            } catch (e) {
            }

            alinearFilasDescripcionConGantt();
            decorarBarrasGanttResponsables();
            configurarTooltipsGantt();
            configurarInteraccionesBarrasGantt();
        }

        function actualizarTablaDescripcionGantt() {
            document.querySelectorAll('.task-row').forEach(function (fila) {
                fila.style.display = idsTareasVisiblesGantt.has(String(fila.dataset.taskId)) ? '' : 'none';
            });
        }

        function obtenerIdsTareasDibujadasGantt() {
            return Array.from(document.querySelectorAll('#gantt .bar-wrapper[data-id]'))
                .map(function (barra) {
                    return String(barra.getAttribute('data-id'));
                })
                .filter(function (id) {
                    return id && id !== '__horizon__';
                });
        }

        function sincronizarTablaConBarrasGantt() {
            const tbody = document.getElementById('tabla-body');
            if (!tbody) return;

            const spacer = tbody.querySelector('.gantt-date-spacer');
            const filasPorId = {};
            document.querySelectorAll('.task-row').forEach(function (fila) {
                filasPorId[String(fila.dataset.taskId)] = fila;
            });

            const idsOrdenados = ordenTareasVisiblesGantt.length ? ordenTareasVisiblesGantt : Array.from(idsTareasVisiblesGantt);

            if (spacer) tbody.appendChild(spacer);

            document.querySelectorAll('.task-row').forEach(function (fila) {
                fila.style.display = 'none';
            });

            idsOrdenados.forEach(function (id) {
                const fila = filasPorId[id];
                if (!fila) return;

                fila.style.display = '';
                tbody.appendChild(fila);
            });

            alinearFilasDescripcionConGantt();
        }

        function alinearFilasDescripcionConGantt() {
            const filasTabla = Array.from(document.querySelectorAll('.task-row'))
                .filter(function (fila) {
                    return fila.style.display !== 'none';
                });
            const filasGantt = Array.from(document.querySelectorAll('#gantt .grid-row')).slice(0, filasTabla.length);

            filasTabla.forEach(function (fila, index) {
                const filaGantt = filasGantt[index];
                if (!filaGantt) return;

                try {
                    const caja = filaGantt.getBBox();
                    const altoFila = Math.round(caja.height) + 'px';
                    fila.style.height = altoFila;
                    fila.querySelectorAll('td').forEach(function (celda) {
                        celda.style.height = altoFila;
                    });
                } catch (e) {
                    fila.style.height = '';
                    fila.querySelectorAll('td').forEach(function (celda) {
                        celda.style.height = '';
                    });
                }
                });
        }

        function obtenerTareaGanttPorId(id) {
            return allTasks.find(function (tarea) {
                return String(tarea.id) === String(id);
            });
        }

        function obtenerInicialesResponsable(nombre) {
            const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
            if (!partes.length) return 'U';
            return partes.slice(0, 2).map(function (parte) {
                return parte.charAt(0).toUpperCase();
            }).join('');
        }

        function truncarTextoBarra(texto, anchoDisponible) {
            const anchoCaracterAprox = 5.55;
            const maxCaracteres = Math.floor(anchoDisponible / anchoCaracterAprox);

            if (maxCaracteres < 8) return '';
            if (String(texto).length <= maxCaracteres) return texto;
            return String(texto).slice(0, Math.max(0, maxCaracteres - 1)).trimEnd() + '…';
        }

        function decorarBarrasGanttResponsables() {
            const svg = document.getElementById('gantt');
            if (!svg) return;

            document.querySelectorAll('#gantt .grant-avatar, #gantt .grant-avatar-fallback, #gantt .grant-avatar-clip').forEach(function (elemento) {
                elemento.remove();
            });

            document.querySelectorAll('#gantt .bar-wrapper[data-id]').forEach(function (wrapper) {
                const id = wrapper.getAttribute('data-id');
                if (!id || id === '__horizon__') return;

                const tarea = obtenerTareaGanttPorId(id);
                if (!tarea) return;

                const barra = wrapper.querySelector('.bar');
                const etiqueta = wrapper.querySelector('.bar-label');
                if (!barra || !etiqueta) return;
                const grupoEtiqueta = etiqueta.parentNode || wrapper;

                const x = Number(barra.getAttribute('x')) || 0;
                const y = Number(barra.getAttribute('y')) || 0;
                const ancho = Number(barra.getAttribute('width')) || 0;
                const alto = Number(barra.getAttribute('height')) || 20;
                const avatarSize = Math.max(11, Math.min(16, alto - 7));
                const paddingX = ancho < 80 ? 3 : 5;
                const avatarX = x + paddingX;
                const avatarY = y + ((alto - avatarSize) / 2);
                const textoX = avatarX + avatarSize + 4;
                const textoY = y + (alto / 2);
                const anchoTexto = Math.max(0, (x + ancho) - textoX - 4);
                const textoBarra = truncarTextoBarra(tarea.nombre_barra || tarea.name || '', anchoTexto);

                etiqueta.textContent = textoBarra;
                etiqueta.setAttribute('x', textoX);
                etiqueta.setAttribute('y', textoY);
                etiqueta.setAttribute('text-anchor', 'start');
                etiqueta.setAttribute('dominant-baseline', 'middle');
                etiqueta.setAttribute('fill', '#fff');
                etiqueta.setAttribute('font-size', '9.6');
                etiqueta.setAttribute('font-weight', '800');
                etiqueta.setAttribute('dx', '0');
                etiqueta.setAttribute('paint-order', 'stroke');
                etiqueta.setAttribute('stroke', 'rgba(15,23,42,0.38)');
                etiqueta.setAttribute('stroke-width', '1.8');
                etiqueta.setAttribute('stroke-linejoin', 'round');
                etiqueta.style.textAnchor = 'start';
                etiqueta.style.pointerEvents = 'none';

                const fondo = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                fondo.setAttribute('class', 'grant-avatar-fallback');
                fondo.setAttribute('cx', avatarX + (avatarSize / 2));
                fondo.setAttribute('cy', avatarY + (avatarSize / 2));
                fondo.setAttribute('r', avatarSize / 2);
                fondo.setAttribute('fill', '#eef4ff');
                fondo.setAttribute('stroke', 'rgba(255,255,255,0.85)');
                fondo.setAttribute('stroke-width', '1');

                const iniciales = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                iniciales.setAttribute('class', 'grant-avatar-fallback');
                iniciales.setAttribute('x', avatarX + (avatarSize / 2));
                iniciales.setAttribute('y', avatarY + (avatarSize / 2));
                iniciales.setAttribute('text-anchor', 'middle');
                iniciales.setAttribute('dominant-baseline', 'middle');
                iniciales.setAttribute('fill', '#1f5eff');
                iniciales.setAttribute('font-size', '7');
                iniciales.setAttribute('font-weight', '800');
                iniciales.setAttribute('stroke', 'none');
                iniciales.textContent = obtenerInicialesResponsable(tarea.responsable);

                grupoEtiqueta.insertBefore(fondo, etiqueta);
                grupoEtiqueta.insertBefore(iniciales, etiqueta);

                const foto = String(tarea.foto_responsable || '').trim();
                if (foto && ancho >= 44) {
                    const clipId = 'grant-avatar-clip-' + String(id).replace(/[^a-zA-Z0-9_-]/g, '');
                    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                    defs.setAttribute('class', 'grant-avatar-clip');

                    const clipPath = document.createElementNS('http://www.w3.org/2000/svg', 'clipPath');
                    clipPath.setAttribute('id', clipId);

                    const clipCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    clipCircle.setAttribute('cx', avatarX + (avatarSize / 2));
                    clipCircle.setAttribute('cy', avatarY + (avatarSize / 2));
                    clipCircle.setAttribute('r', avatarSize / 2);
                    clipPath.appendChild(clipCircle);
                    defs.appendChild(clipPath);
                    svg.insertBefore(defs, svg.firstChild);

                    const imagen = document.createElementNS('http://www.w3.org/2000/svg', 'image');
                    imagen.setAttribute('class', 'grant-avatar');
                    imagen.setAttribute('x', avatarX);
                    imagen.setAttribute('y', avatarY);
                    imagen.setAttribute('width', avatarSize);
                    imagen.setAttribute('height', avatarSize);
                    imagen.setAttribute('clip-path', 'url(#' + clipId + ')');
                    imagen.setAttribute('preserveAspectRatio', 'xMidYMid slice');
                    imagen.setAttribute('href', foto);
                    imagen.setAttributeNS('http://www.w3.org/1999/xlink', 'href', foto);
                    imagen.addEventListener('error', function () {
                        imagen.remove();
                    });
                    grupoEtiqueta.insertBefore(imagen, etiqueta);
                }
            });
        }

        function escaparHtml(valor) {
            return String(valor === null || typeof valor === 'undefined' ? '' : valor)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function obtenerGlobalGantt(nombre) {
            try {
                if (window.parent && window.parent !== window && typeof window.parent[nombre] !== 'undefined') {
                    return window.parent[nombre] || '';
                }
            } catch (e) {
            }

            try {
                if (window.opener && typeof window.opener[nombre] !== 'undefined') {
                    return window.opener[nombre] || '';
                }
            } catch (e) {
            }

            try {
                if (typeof window[nombre] !== 'undefined') {
                    return window[nombre] || '';
                }
            } catch (e) {
            }

            return '';
        }

        function obtenerCredencialesGantt() {
            try {
                if (window.parent && window.parent !== window && typeof window.parent.obtener_datos_user === 'function') {
                    window.parent.obtener_datos_user();
                }
            } catch (e) {
            }

            try {
                if (window.opener && typeof window.opener.obtener_datos_user === 'function') {
                    window.opener.obtener_datos_user();
                }
            } catch (e) {
            }

            let useru = obtenerGlobalGantt('userid') || obtenerUsuarioActualIdGantt();
            let passu = obtenerGlobalGantt('passuser');
            let navegadorActual = obtenerGlobalGantt('navegador');

            try {
                if (!useru && window.parent && typeof window.parent.buscar_datos_url_usuario === 'function') {
                    useru = window.parent.buscar_datos_url_usuario('q') || '';
                }
                if (!passu && window.parent && typeof window.parent.buscar_datos_url_usuario === 'function') {
                    passu = window.parent.buscar_datos_url_usuario('p') || '';
                }
                if (!useru && window.parent && typeof window.parent.buscar_este_cookie === 'function') {
                    useru = window.parent.buscar_este_cookie('user') || '';
                }
                if (!passu && window.parent && typeof window.parent.buscar_este_cookie === 'function') {
                    passu = window.parent.buscar_este_cookie('pass') || '';
                }
                if (!navegadorActual && window.parent && typeof window.parent.obtener_navegor_en_uso === 'function') {
                    navegadorActual = window.parent.obtener_navegor_en_uso() || '';
                }
            } catch (e) {
            }

            try {
                if (!useru && window.opener && typeof window.opener.buscar_datos_url_usuario === 'function') {
                    useru = window.opener.buscar_datos_url_usuario('q') || '';
                }
                if (!passu && window.opener && typeof window.opener.buscar_datos_url_usuario === 'function') {
                    passu = window.opener.buscar_datos_url_usuario('p') || '';
                }
                if (!useru && window.opener && typeof window.opener.buscar_este_cookie === 'function') {
                    useru = window.opener.buscar_este_cookie('user') || '';
                }
                if (!passu && window.opener && typeof window.opener.buscar_este_cookie === 'function') {
                    passu = window.opener.buscar_este_cookie('pass') || '';
                }
                if (!navegadorActual && window.opener && typeof window.opener.obtener_navegor_en_uso === 'function') {
                    navegadorActual = window.opener.obtener_navegor_en_uso() || '';
                }
            } catch (e) {
            }

            return {
                useru: useru,
                passu: passu,
                navegador: navegadorActual
            };
        }

        function sincronizarCredencialesFormularioGantt() {
            const credenciales = obtenerCredencialesGantt();
            const campos = {
                grant_form_useru: credenciales.useru,
                grant_form_passu: credenciales.passu,
                grant_form_navegador: credenciales.navegador
            };

            Object.keys(campos).forEach(function (id) {
                const campo = document.getElementById(id);
                if (campo) campo.value = campos[id] || '';
            });
        }

        function llamarApiGantt(accion, payload) {
            const cuerpo = Object.assign({}, payload || {}, obtenerCredencialesGantt(), {
                accion_gantt: accion
            });

            return fetch('Grant.php?api_gantt=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cuerpo)
            })
                .then(function (respuesta) {
                    return respuesta.json().catch(function () {
                        return { status: 'error', message: 'Respuesta invalida del servidor.' };
                    }).then(function (datos) {
                        if (!respuesta.ok || datos.status !== 'success') {
                            throw new Error(datos.message || 'No se pudo completar la accion.');
                        }
                        return datos;
                    });
                });
        }

        function mostrarToastGantt(mensaje, undo) {
            const zona = document.getElementById('gantt-toast-zone');
            if (!zona) return;

            const toast = document.createElement('div');
            toast.className = 'gantt-toast';
            toast.innerHTML = '<span>' + escaparHtml(mensaje) + '</span>';

            if (undo) {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.textContent = 'Deshacer';
                boton.addEventListener('click', function () {
                    boton.disabled = true;
                    deshacerAccionGantt(undo, toast);
                });
                toast.appendChild(boton);
            }

            zona.appendChild(toast);
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 6500);
        }

        function deshacerAccionGantt(undo, toast) {
            llamarApiGantt('deshacer', {
                undo: undo,
                origen: 'deshacer'
            })
                .then(function (datos) {
                    if (datos.removed) {
                        quitarTareaGantt(datos.task_id);
                    }
                    if (datos.task) {
                        upsertTareaGantt(datos.task);
                    }
                    aplicarFiltros();
                    cerrarVistaRapidaTarea();
                    if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
                    mostrarToastGantt(datos.message || 'Cambio deshecho.');
                })
                .catch(function (error) {
                    mostrarToastGantt(error.message || 'No se pudo deshacer.');
                });
        }

        function upsertTareaReporteLocal(tarea) {
            if (!tarea || !tarea.id || tarea.id === '__horizon__' || !Array.isArray(reportTasks)) return;

            const id = String(tarea.id);
            const tareaReporte = Object.assign({}, tarea, {
                fecha_inicio: tarea.fecha_inicio || tarea.start || '',
                fecha_fin: tarea.fecha_fin || tarea.end || '',
                responsable_rol: tarea.responsable_rol || '',
                deleted_at: tarea.deleted_at || ''
            });
            const indice = reportTasks.findIndex(function (item) {
                return String(item.id) === id;
            });

            if (indice >= 0) {
                reportTasks[indice] = tareaReporte;
            } else {
                reportTasks.push(tareaReporte);
            }
        }

        function quitarTareaReporteLocal(id) {
            if (!Array.isArray(reportTasks)) return;
            const idTexto = String(id);
            const indice = reportTasks.findIndex(function (item) {
                return String(item.id) === idTexto;
            });
            if (indice >= 0) reportTasks.splice(indice, 1);
        }

        function upsertTareaGantt(tarea) {
            if (!tarea || !tarea.id || tarea.id === '__horizon__') return;

            const id = String(tarea.id);
            const indice = allTasks.findIndex(function (item) {
                return String(item.id) === id;
            });

            if (indice >= 0) {
                allTasks[indice] = tarea;
            } else {
                const indiceHorizon = allTasks.findIndex(function (item) {
                    return item.id === '__horizon__';
                });
                if (indiceHorizon >= 0) {
                    allTasks.splice(indiceHorizon, 0, tarea);
                } else {
                    allTasks.push(tarea);
                }
            }

            upsertTareaReporteLocal(tarea);
            actualizarFilaTablaTarea(tarea);
        }

        function quitarTareaGantt(id) {
            const idTexto = String(id);
            const indice = allTasks.findIndex(function (item) {
                return String(item.id) === idTexto;
            });
            if (indice >= 0) allTasks.splice(indice, 1);
            quitarTareaReporteLocal(idTexto);

            const fila = document.querySelector('.task-row[data-task-id="' + idTexto.replace(/"/g, '\\"') + '"]');
            if (fila && fila.parentNode) fila.parentNode.removeChild(fila);
        }

        function actualizarFilaTablaTarea(tarea) {
            const tbody = document.getElementById('tabla-body');
            if (!tbody || !tarea || !tarea.id) return;

            let fila = tbody.querySelector('.task-row[data-task-id="' + String(tarea.id).replace(/"/g, '\\"') + '"]');
            if (!fila) {
                fila = document.createElement('tr');
                fila.className = 'task-row';
                fila.setAttribute('data-task-id', tarea.id);
                fila.innerHTML =
                    '<td><strong></strong></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td>' +
                    '<a style="cursor:pointer;" title="Editar" onclick="abrirVistaRapidaTarea(\'' + String(tarea.id).replace(/'/g, "\\'") + '\', event)">Editar</a> ' +
                    '<a style="cursor:pointer;" title="Anular tarea" onclick="abrirDialogoAnularTarea(\'' + String(tarea.id).replace(/'/g, "\\'") + '\', event)">Anular</a>' +
                    '</td>';
                tbody.appendChild(fila);
            }

            fila.setAttribute('data-sucursal', tarea.sucursal || '');
            fila.setAttribute('data-responsable', String(tarea.responsable || '').toLowerCase());

            const celdas = fila.querySelectorAll('td');
            if (celdas[0]) celdas[0].innerHTML = '<strong>' + escaparHtml(tarea.titulo || tarea.titulo_original || tarea.name || '') + '</strong>';
            if (celdas[1]) celdas[1].textContent = tarea.sucursal || '';
            if (celdas[2]) celdas[2].textContent = tarea.responsable || '';
            if (celdas[3]) celdas[3].textContent = tarea.usuarios_vinculados || '';
        }

        function convertirTareaGanttAFormulario(tarea) {
            return {
                id: tarea.id,
                titulo: tarea.titulo || tarea.titulo_original || tarea.name || '',
                fecha_inicio: tarea.fecha_inicio_original || tarea.start,
                fecha_fin: tarea.fecha_fin_original || tarea.end,
                progreso: tarea.progress || 0,
                estado: tarea.estado || 'Pendiente',
                dependencia: tarea.dependencies || '',
                sucursal: tarea.sucursal || 'Todas',
                responsable: tarea.responsable || '',
                usuarios_vinculados_ids: tarea.usuarios_vinculados_ids || []
            };
        }

        function copiarOpcionesSelectGantt(origenId, destinoId) {
            const origen = document.getElementById(origenId);
            const destino = document.getElementById(destinoId);
            if (!origen || !destino || destino.dataset.copiado === '1') return;

            destino.innerHTML = origen.innerHTML;
            destino.dataset.copiado = '1';
        }

        function asegurarOpcionSelectGantt(select, valor) {
            if (!select || !valor) return;

            const existe = Array.from(select.options).some(function (option) {
                return option.value === valor;
            });
            if (existe) return;

            const option = document.createElement('option');
            option.value = valor;
            option.textContent = valor;
            select.appendChild(option);
        }

        function abrirVistaRapidaTarea(id, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const tarea = obtenerTareaGanttPorId(id);
            if (!tarea) return;

            copiarOpcionesSelectGantt('form_responsable', 'quick_responsable');
            copiarOpcionesSelectGantt('form_sucursal', 'quick_sucursal');

            const panel = document.getElementById('gantt-quick-panel');
            const responsable = document.getElementById('quick_responsable');
            const sucursal = document.getElementById('quick_sucursal');
            if (!panel) return;

            asegurarOpcionSelectGantt(responsable, tarea.responsable || '');
            asegurarOpcionSelectGantt(sucursal, tarea.sucursal || '');

            document.getElementById('quick_tarea_id').value = tarea.id;
            document.getElementById('quick_panel_title').textContent = tarea.titulo || tarea.titulo_original || tarea.name || 'Tarea';
            document.getElementById('quick_estado_badge').textContent = tarea.estado_visual || tarea.estado || 'Pendiente';
            document.getElementById('quick_titulo').value = tarea.titulo || tarea.titulo_original || tarea.name || '';
            document.getElementById('quick_fecha_inicio').value = tarea.fecha_inicio_original || tarea.start || '';
            document.getElementById('quick_fecha_fin').value = tarea.fecha_fin_original || tarea.end || '';
            document.getElementById('quick_estado').value = tarea.estado || 'Pendiente';
            document.getElementById('quick_prioridad').value = tarea.prioridad || 'Normal';
            if (responsable) responsable.value = tarea.responsable || '';
            if (sucursal) sucursal.value = tarea.sucursal || 'Todas';
            document.getElementById('quick_observacion').value = tarea.observacion || '';
            document.getElementById('quick_ultimo_cambio').textContent = tarea.ultimo_cambio_texto || 'Sin cambios registrados';
            document.getElementById('quick_historial_panel').style.display = 'none';
            document.getElementById('quick_historial_panel').innerHTML = '';

            panel.style.display = '';
        }

        function cerrarVistaRapidaTarea() {
            const panel = document.getElementById('gantt-quick-panel');
            if (panel) panel.style.display = 'none';
        }

        function obtenerResponsableQuickId() {
            const select = document.getElementById('quick_responsable');
            const option = select && select.options ? select.options[select.selectedIndex] : null;
            return option ? (option.dataset.usuarioId || '') : '';
        }

        function guardarVistaRapidaTarea() {
            const id = document.getElementById('quick_tarea_id').value;
            const tarea = obtenerTareaGanttPorId(id);
            if (!tarea) return;

            const payload = {
                id: id,
                titulo: document.getElementById('quick_titulo').value.trim(),
                fecha_inicio: document.getElementById('quick_fecha_inicio').value,
                fecha_fin: document.getElementById('quick_fecha_fin').value,
                estado: document.getElementById('quick_estado').value,
                prioridad: document.getElementById('quick_prioridad').value,
                responsable: document.getElementById('quick_responsable').value,
                responsable_id: obtenerResponsableQuickId(),
                sucursal: document.getElementById('quick_sucursal').value,
                observacion: document.getElementById('quick_observacion').value,
                progreso: tarea.progress || 0,
                origen: 'formulario rapido'
            };

            if (!payload.titulo) {
                mostrarToastGantt('Ingrese el titulo de la tarea.');
                return;
            }

            const cambioSensible = (payload.responsable !== (tarea.responsable || ''))
                || (payload.sucursal !== (tarea.sucursal || ''))
                || tarea.estado === 'Completada';

            if (cambioSensible && !confirm('Este cambio quedara registrado. Continuar?')) {
                return;
            }

            llamarApiGantt('actualizar_tarea', payload)
                .then(function (datos) {
                    upsertTareaGantt(datos.task);
                    aplicarFiltros();
                    abrirVistaRapidaTarea(datos.task.id);
                    mostrarToastGantt(datos.message || 'Tarea actualizada.', datos.undo);
                })
                .catch(function (error) {
                    mostrarToastGantt(error.message || 'No se pudo actualizar la tarea.');
                });
        }

        function culminarVistaRapidaTarea() {
            const id = document.getElementById('quick_tarea_id').value;
            if (!id) return;

            llamarApiGantt('culminar', {
                id: id,
                origen: 'formulario rapido'
            })
                .then(function (datos) {
                    upsertTareaGantt(datos.task);
                    aplicarFiltros();
                    abrirVistaRapidaTarea(datos.task.id);
                    mostrarToastGantt(datos.message || 'Tarea culminada.', datos.undo);
                })
                .catch(function (error) {
                    mostrarToastGantt(error.message || 'No se pudo culminar la tarea.');
                });
        }

        function editarCompletoVistaRapidaTarea() {
            const id = document.getElementById('quick_tarea_id').value;
            const tarea = obtenerTareaGanttPorId(id);
            if (!tarea) return;

            cerrarVistaRapidaTarea();
            editarTarea(convertirTareaGanttAFormulario(tarea));
        }

        function verHistorialVistaRapidaTarea() {
            const id = document.getElementById('quick_tarea_id').value;
            const panel = document.getElementById('quick_historial_panel');
            if (!id || !panel) return;

            panel.style.display = '';
            panel.innerHTML = 'Cargando historial...';

            llamarApiGantt('historial', {
                id: id,
                origen: 'formulario rapido'
            })
                .then(function (datos) {
                    const historial = datos.historial || [];
                    if (!historial.length) {
                        panel.innerHTML = 'Todavia no hay movimientos registrados.';
                        return;
                    }

                    panel.innerHTML = historial.map(function (item) {
                        const usuario = item.nombre_persona || 'usuario';
                        const campo = item.campo ? ' · ' + item.campo : '';
                        const motivo = item.motivo ? '<br>Motivo: ' + escaparHtml(item.motivo) : '';
                        return '<div class="gantt-history-item">'
                            + '<strong>' + escaparHtml(item.accion || 'cambio') + campo + '</strong>'
                            + '<span>' + escaparHtml(item.created_at || '') + ' por ' + escaparHtml(usuario) + '</span><br>'
                            + '<span>' + escaparHtml(item.valor_anterior || '-') + ' -> ' + escaparHtml(item.valor_nuevo || '-') + '</span>'
                            + motivo
                            + '</div>';
                    }).join('');
                })
                .catch(function (error) {
                    panel.innerHTML = escaparHtml(error.message || 'No se pudo cargar el historial.');
                });
        }

        function actualizarMotivoAnulacionGantt() {
            const select = document.getElementById('gantt_anular_motivo');
            const otro = document.getElementById('gantt_anular_otro');
            if (!select || !otro) return;
            otro.style.display = select.value === 'Otro motivo' ? '' : 'none';
        }

        function abrirDialogoAnularTarea(id, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const idFinal = id || (document.getElementById('quick_tarea_id') ? document.getElementById('quick_tarea_id').value : '');
            if (!idFinal) return;

            document.getElementById('gantt_anular_id').value = idFinal;
            document.getElementById('gantt_anular_motivo').value = 'Creada por error';
            document.getElementById('gantt_anular_otro').value = '';
            actualizarMotivoAnulacionGantt();
            document.getElementById('gantt-anular-backdrop').style.display = 'flex';
        }

        function cerrarDialogoAnularTarea() {
            const backdrop = document.getElementById('gantt-anular-backdrop');
            if (backdrop) backdrop.style.display = 'none';
        }

        function confirmarAnularTarea() {
            const id = document.getElementById('gantt_anular_id').value;
            const motivoSelect = document.getElementById('gantt_anular_motivo').value;
            const motivoOtro = document.getElementById('gantt_anular_otro').value.trim();
            const motivo = motivoSelect === 'Otro motivo' ? motivoOtro : motivoSelect;

            if (!id) return;
            if (motivoSelect === 'Otro motivo' && !motivo) {
                mostrarToastGantt('Ingrese el motivo de anulacion.');
                return;
            }

            llamarApiGantt('anular', {
                id: id,
                motivo: motivo,
                origen: 'formulario rapido'
            })
                .then(function (datos) {
                    quitarTareaGantt(id);
                    aplicarFiltros();
                    cerrarDialogoAnularTarea();
                    cerrarVistaRapidaTarea();
                    mostrarToastGantt(datos.message || 'Tarea anulada.', datos.undo);
                })
                .catch(function (error) {
                    mostrarToastGantt(error.message || 'No se pudo anular la tarea.');
                });
        }

        function obtenerFechaDesdeClickGantt(evento) {
            if (!gantt || !gantt.gantt_start) return '';

            const svg = document.getElementById('gantt');
            if (!svg) return '';
            const metricas = obtenerMetricasSvgGanttActual();
            const contenedor = obtenerContenedorScrollGantt() || document.getElementById('gantt-container');
            if (!metricas || !contenedor) return '';

            const rectContenedor = contenedor.getBoundingClientRect();
            const xEnViewport = (evento.clientX - rectContenedor.left) * metricas.escalaX;
            const yEnViewport = evento.clientY - rectContenedor.top;
            if (xEnViewport < 0 || yEnViewport < 0 || xEnViewport > rectContenedor.width * metricas.escalaX) return '';

            const primeraFila = document.querySelector('#gantt .grid-row');
            if (primeraFila) {
                try {
                    const rectFila = primeraFila.getBoundingClientRect();
                    if (evento.clientY < rectFila.top) return '';
                } catch (e) {
                }
            }

            return fechaDesdeXGanttActual(obtenerXVisibleGanttActual(metricas) + xEnViewport);
        }

        function abrirEditorCreacionRapida(evento, fecha) {
            const editor = document.getElementById('gantt-quick-create');
            const input = document.getElementById('gantt-quick-create-input');
            if (!editor || !input || !fecha) return;

            quickCreateState = { fecha: fecha, creando: false };
            editor.style.left = Math.min(window.innerWidth - 280, Math.max(8, evento.clientX + 6)) + 'px';
            editor.style.top = Math.min(window.innerHeight - 54, Math.max(8, evento.clientY + 6)) + 'px';
            editor.style.display = '';
            input.value = '';
            input.focus();
        }

        function cerrarEditorCreacionRapida() {
            const editor = document.getElementById('gantt-quick-create');
            if (editor) editor.style.display = 'none';
            quickCreateState = null;
        }

        function obtenerContextoCreacionRapidaGantt() {
            const filtroSucursal = document.getElementById('filtro-sucursal');
            const filtroUsuario = document.getElementById('filtro-usuario');
            const filtroResponsable = document.getElementById('filtro-responsable');
            const opcionUsuario = filtroUsuario && filtroUsuario.options ? filtroUsuario.options[filtroUsuario.selectedIndex] : null;

            let sucursal = filtroSucursal ? filtroSucursal.value : 'Todas';
            if (!sucursal) sucursal = 'Todas';

            let responsable = '';
            let responsableId = '';
            if (filtroUsuario && filtroUsuario.value && opcionUsuario) {
                responsable = opcionUsuario.textContent.trim();
                responsableId = filtroUsuario.value;
            } else if (filtroResponsable && filtroResponsable.value.trim()) {
                responsable = filtroResponsable.value.trim();
            }

            return {
                sucursal: sucursal,
                responsable: responsable,
                responsable_id: responsableId
            };
        }

        function asegurarFiltrosIncluyenTareaRapidaGantt(tarea) {
            if (!tarea) return;

            const fecha = String(tarea.start || tarea.fecha_inicio || '').slice(0, 10);
            const desde = document.getElementById('reporte-fecha-desde');
            const hasta = document.getElementById('reporte-fecha-hasta');
            const estado = document.getElementById('reporte-estado');

            if (fecha) {
                if (desde && (!desde.value || fecha < desde.value)) desde.value = fecha;
                if (hasta && (!hasta.value || fecha > hasta.value)) hasta.value = fecha;
            }

            if (estado && estado.value && estado.value !== 'Todas') {
                const estadoTarea = normalizarEstadoReporteActividades(tarea);
                if (estado.value !== estadoTarea) {
                    estado.value = 'Todas';
                }
            }
        }

        function confirmarCreacionRapidaGantt() {
            const input = document.getElementById('gantt-quick-create-input');
            if (!quickCreateState || !input || quickCreateState.creando) return;

            const titulo = input.value.trim();
            if (!titulo) {
                cerrarEditorCreacionRapida();
                return;
            }

            quickCreateState.creando = true;
            const contexto = obtenerContextoCreacionRapidaGantt();

            llamarApiGantt('crear_rapida', Object.assign({}, contexto, {
                titulo: titulo,
                fecha_inicio: quickCreateState.fecha,
                origen: 'grilla Gantt'
            }))
                .then(function (datos) {
                    upsertTareaGantt(datos.task);
                    asegurarFiltrosIncluyenTareaRapidaGantt(datos.task);
                    cerrarEditorCreacionRapida();
                    aplicarFiltros();
                    mostrarToastGantt(datos.message || 'Tarea creada.', datos.undo);
                })
                .catch(function (error) {
                    quickCreateState.creando = false;
                    mostrarToastGantt(error.message || 'No se pudo crear la tarea.');
                });
        }

        function configurarCreacionRapidaGantt() {
            const contenedor = document.getElementById('gantt-container');
            const input = document.getElementById('gantt-quick-create-input');
            if (contenedor && contenedor.dataset.quickCreateReady !== '1') {
                contenedor.dataset.quickCreateReady = '1';
                contenedor.addEventListener('click', function (evento) {
                    if (quickCreateState) return;
                    if (Date.now() < ganttBloqueoClickRapidoHasta) return;
                    if (evento.target.closest && evento.target.closest('.bar-wrapper')) return;
                    if (evento.target.closest && evento.target.closest('input,select,textarea,button,a,.gantt-quick-create,.gantt-quick-panel')) return;

                    const fecha = obtenerFechaDesdeClickGantt(evento);
                    if (fecha) abrirEditorCreacionRapida(evento, fecha);
                });
            }

            if (input && input.dataset.quickCreateInputReady !== '1') {
                input.dataset.quickCreateInputReady = '1';
                input.addEventListener('keydown', function (evento) {
                    if (evento.key === 'Enter') {
                        evento.preventDefault();
                        confirmarCreacionRapidaGantt();
                    }
                    if (evento.key === 'Escape') {
                        evento.preventDefault();
                        cerrarEditorCreacionRapida();
                    }
                });
                input.addEventListener('blur', function () {
                    setTimeout(function () {
                        if (quickCreateState) confirmarCreacionRapidaGantt();
                    }, 100);
                });
            }
        }

        function configurarInteraccionesBarrasGantt() {
            document.querySelectorAll('#gantt .bar-wrapper[data-id]').forEach(function (wrapper) {
                if (wrapper.dataset.quickPanelReady === '1') return;

                const id = wrapper.getAttribute('data-id');
                if (!id || id === '__horizon__') return;

                wrapper.dataset.quickPanelReady = '1';
                wrapper.addEventListener('click', function (evento) {
                    if (Date.now() < ganttBloqueoClickRapidoHasta) return;
                    abrirVistaRapidaTarea(id, evento);
                });
                wrapper.addEventListener('dblclick', function (evento) {
                    evento.preventDefault();
                    evento.stopPropagation();
                    const tarea = obtenerTareaGanttPorId(id);
                    if (tarea) editarTarea(convertirTareaGanttAFormulario(tarea));
                });
            });
        }

        function obtenerTextoSelectGantt(id) {
            const select = document.getElementById(id);
            if (!select || !select.options || select.selectedIndex < 0) return '';
            return select.options[select.selectedIndex].textContent.trim();
        }

        function crearCampoCabeceraImpresion(titulo, valor) {
            const valorNormalizado = normalizarValorCabeceraImpresion(valor);

            return ''
                + '<div class="print-filter-item">'
                + '<p><b>' + escaparHtml(titulo) + '</b></p>'
                + '<p>' + escaparHtml(valorNormalizado) + '</p>'
                + '</div>';
        }

        function normalizarValorCabeceraImpresion(valor) {
            const texto = String(valor || '').trim();
            if (!texto) return 'TODOS';
            if (/^todas?(\s|$)/i.test(texto) || /^todos?(\s|$)/i.test(texto)) return 'TODOS';
            return texto;
        }

        function obtenerHtmlTablaTareasImpresion() {
            const filas = Array.from(document.querySelectorAll('.task-row')).filter(function (fila) {
                return fila.style.display !== 'none';
            });

            if (!filas.length) {
                return '<p class="print-empty">No hay tareas visibles para imprimir.</p>';
            }

            const filasHtml = filas.map(function (fila) {
                const celdas = Array.from(fila.querySelectorAll('td')).slice(0, 4);
                const claseFila = filas.indexOf(fila) % 2 === 0 ? 'tableRegistroSearch' : 'tableRegistroSearch2';

                return '<tr class="' + claseFila + '">' + celdas.map(function (celda) {
                    return '<td>' + celda.innerHTML + '</td>';
                }).join('') + '</tr>';
            }).join('');

            return ''
                + '<h1 class="print-table-title">LISTA DE TAREAS</h1>'
                + '<table class="print-task-table">'
                + '<thead><tr>'
                + '<th>Flujo de tareas</th>'
                + '<th>Sucursal</th>'
                + '<th>Responsable</th>'
                + '<th>Vinculados</th>'
                + '</tr></thead>'
                + '<tbody>' + filasHtml + '</tbody>'
                + '</table>';
        }

        function crearSvgGanttImpresionPorTramo(svg, x, y, ancho, alto) {
            const svgClone = svg.cloneNode(true);
            svgClone.removeAttribute('style');
            svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svgClone.setAttribute('width', '100%');
            svgClone.setAttribute('height', '160mm');
            svgClone.setAttribute('viewBox', [x, y, ancho, alto].join(' '));
            svgClone.setAttribute('preserveAspectRatio', 'xMinYMin meet');

            return new XMLSerializer().serializeToString(svgClone);
        }

        function calcularXFechaGantt(fechaStr) {
            if (!gantt || !gantt.gantt_start || !fechaStr) return 0;

            const fecha = new Date(fechaStr + 'T00:00:00');
            const inicioGantt = new Date(gantt.gantt_start);
            fecha.setHours(0, 0, 0, 0);
            inicioGantt.setHours(0, 0, 0, 0);

            const diasDesdeInicio = Math.max(0, Math.floor((fecha - inicioGantt) / 86400000));
            const anchoColumna = obtenerAnchoColumnaVista();

            if (vistaActual === 'Month') return (diasDesdeInicio / 30) * anchoColumna;
            if (vistaActual === 'Week') return (diasDesdeInicio / 7) * anchoColumna;

            return diasDesdeInicio * anchoColumna;
        }

        function obtenerAnchoDiaGantt() {
            const anchoColumna = obtenerAnchoColumnaVista();

            if (vistaActual === 'Month') return anchoColumna / 30;
            if (vistaActual === 'Week') return anchoColumna / 7;

            return anchoColumna;
        }

        function obtenerHtmlGanttImpresion() {
            const svg = document.getElementById('gantt');
            if (!svg) return '<p class="print-empty">No se encontro el diagrama de Gantt.</p>';

            let caja = null;
            try {
                caja = svg.getBBox();
            } catch (e) {
            }

            if (!caja || !caja.width || !caja.height) {
                return crearSvgGanttImpresionPorTramo(svg, 0, 0, 1200, 600);
            }

            const rangoImpresion = obtenerRangoUltimoMesGantt();
            const xInicial = Math.max(caja.x, calcularXFechaGantt(rangoImpresion.inicioStr));
            const xFinal = calcularXFechaGantt(rangoImpresion.finStr) + obtenerAnchoDiaGantt();
            const yInicial = caja.y;
            const alto = Math.max(caja.height, 420);
            const anchoTramo = Math.max(1, xFinal - xInicial);
            const svgTramo = crearSvgGanttImpresionPorTramo(svg, xInicial, yInicial, anchoTramo, alto);

            return ''
                + '<section class="print-gantt-section">'
                + '<h2 class="print-section-title">Diagrama de Gantt</h2>'
                + '<div class="print-gantt">' + svgTramo + '</div>'
                + '</section>';
        }

        function obtenerFiltrosReporteActividades() {
            const filtroSucursal = document.getElementById('filtro-sucursal');
            const filtroUsuario = document.getElementById('filtro-usuario');
            const filtroResponsable = document.getElementById('filtro-responsable');
            const filtroEstado = document.getElementById('reporte-estado');
            const fechaDesde = document.getElementById('reporte-fecha-desde');
            const fechaHasta = document.getElementById('reporte-fecha-hasta');
            const opcionUsuario = filtroUsuario && filtroUsuario.options
                ? filtroUsuario.options[filtroUsuario.selectedIndex]
                : null;
            let desde = fechaDesde ? String(fechaDesde.value || '').trim() : '';
            let hasta = fechaHasta ? String(fechaHasta.value || '').trim() : '';

            if (desde && hasta && desde > hasta) {
                const temporal = desde;
                desde = hasta;
                hasta = temporal;
            }

            return {
                sucursal: filtroSucursal ? filtroSucursal.value : 'Todas',
                sucursal_texto: filtroSucursal ? obtenerTextoSelectGantt('filtro-sucursal') : 'Todas',
                funcionario_id: filtroUsuario ? String(filtroUsuario.value || '') : '',
                funcionario_nombre: opcionUsuario && filtroUsuario.value ? opcionUsuario.textContent.trim() : '',
                funcionario_nombre_normalizado: opcionUsuario && filtroUsuario.value
                    ? normalizarTextoFiltro(opcionUsuario.dataset.nombre || opcionUsuario.textContent || '')
                    : '',
                funcionario_rol: opcionUsuario ? String(opcionUsuario.dataset.rol || '').trim() : '',
                responsable_texto: filtroResponsable ? filtroResponsable.value.trim() : '',
                responsable_normalizado: filtroResponsable ? normalizarTextoFiltro(filtroResponsable.value) : '',
                desde: desde,
                hasta: hasta,
                estado: filtroEstado ? String(filtroEstado.value || 'Todas') : 'Todas'
            };
        }

        function parsearFechaReporte(valor) {
            const texto = String(valor || '').trim().slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(texto)) return null;
            const fecha = new Date(texto + 'T00:00:00');
            if (isNaN(fecha.getTime())) return null;
            fecha.setHours(0, 0, 0, 0);
            return fecha;
        }

        function normalizarEstadoReporteActividades(tarea) {
            const estado = String(tarea.estado || 'Pendiente').trim();
            if (estado === 'Anulada' || String(tarea.deleted_at || '').trim() !== '') return 'Anulada';
            if (estado === 'Completada') return 'Completada';

            const fechaFin = parsearFechaReporte(tarea.end || tarea.fecha_fin);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            if (fechaFin && fechaFin < hoy) return 'Vencida';
            if (estado === 'En Progreso' || estado === 'En proceso') return 'En Progreso';

            return 'Pendiente';
        }

        function etiquetaEstadoReporteActividades(estado) {
            const mapa = {
                'Pendiente': 'Pendiente',
                'En Progreso': 'En proceso',
                'Completada': 'Culminada',
                'Vencida': 'Vencida',
                'Anulada': 'Anulada'
            };
            return mapa[estado] || 'Pendiente';
        }

        function tareaCumpleFiltrosReporteActividades(tarea, filtros) {
            if (!tarea || tarea.id === '__horizon__') return false;

            const matchSucursal = !filtros.sucursal || filtros.sucursal === 'Todas' || tarea.sucursal === filtros.sucursal;
            const usuariosBusqueda = normalizarTextoFiltro((tarea.responsable || '') + ' ' + (tarea.usuarios_vinculados || ''));
            const vinculadosIds = (tarea.usuarios_vinculados_ids || []).map(function (id) {
                return String(id);
            });
            const responsableId = tarea.responsable_id !== null && typeof tarea.responsable_id !== 'undefined'
                ? String(tarea.responsable_id)
                : '';
            const matchFuncionario = !filtros.funcionario_id
                || responsableId === filtros.funcionario_id
                || vinculadosIds.includes(filtros.funcionario_id)
                || (filtros.funcionario_nombre_normalizado && usuariosBusqueda.includes(filtros.funcionario_nombre_normalizado));
            const matchResponsableTexto = !filtros.responsable_normalizado || usuariosBusqueda.includes(filtros.responsable_normalizado);
            const inicio = parsearFechaReporte(tarea.start || tarea.fecha_inicio);
            const fin = parsearFechaReporte(tarea.end || tarea.fecha_fin) || inicio;
            const desde = parsearFechaReporte(filtros.desde);
            const hasta = parsearFechaReporte(filtros.hasta);
            const matchFechas = (!desde || (fin && fin >= desde)) && (!hasta || (inicio && inicio <= hasta));
            const estadoNormalizado = normalizarEstadoReporteActividades(tarea);
            const matchEstado = !filtros.estado || filtros.estado === 'Todas' || estadoNormalizado === filtros.estado;

            return matchSucursal && matchFuncionario && matchResponsableTexto && matchFechas && matchEstado;
        }

        function obtenerTareasReporteActividades(filtros) {
            const fuente = Array.isArray(reportTasks) && reportTasks.length
                ? reportTasks
                : allTasks.filter(function (tarea) { return tarea.id !== '__horizon__'; });

            return fuente
                .filter(function (tarea) {
                    return tareaCumpleFiltrosReporteActividades(tarea, filtros);
                })
                .sort(function (a, b) {
                    return String(a.start || a.fecha_inicio || '').localeCompare(String(b.start || b.fecha_inicio || ''))
                        || String(a.titulo || a.name || '').localeCompare(String(b.titulo || b.name || ''));
                });
        }

        function calcularResumenReporteActividades(tareas) {
            const resumen = {
                total: tareas.length,
                pendiente: 0,
                progreso: 0,
                culminada: 0,
                vencida: 0,
                anulada: 0,
                cumplimiento: 0
            };

            tareas.forEach(function (tarea) {
                const estado = normalizarEstadoReporteActividades(tarea);
                if (estado === 'Completada') resumen.culminada++;
                else if (estado === 'En Progreso') resumen.progreso++;
                else if (estado === 'Vencida') resumen.vencida++;
                else if (estado === 'Anulada') resumen.anulada++;
                else resumen.pendiente++;
            });

            const activas = Math.max(0, resumen.total - resumen.anulada);
            resumen.cumplimiento = activas > 0 ? Math.round((resumen.culminada / activas) * 100) : 0;
            return resumen;
        }

        function obtenerInicialesReporteActividades(nombre) {
            const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
            if (!partes.length) return 'CS';
            return partes.slice(0, 2).map(function (parte) {
                return parte.charAt(0).toUpperCase();
            }).join('');
        }

        function obtenerFuncionarioReporteActividades(filtros, tareas) {
            let nombre = filtros.funcionario_nombre || '';
            let rol = filtros.funcionario_rol || '';
            let foto = '';

            const tareaFuncionario = tareas.find(function (tarea) {
                if (!filtros.funcionario_id) return false;
                const responsableId = tarea.responsable_id !== null && typeof tarea.responsable_id !== 'undefined'
                    ? String(tarea.responsable_id)
                    : '';
                const vinculadosIds = (tarea.usuarios_vinculados_ids || []).map(function (id) {
                    return String(id);
                });
                return responsableId === filtros.funcionario_id || vinculadosIds.includes(filtros.funcionario_id);
            }) || tareas[0];

            if (!nombre && filtros.responsable_texto) nombre = filtros.responsable_texto;
            if (!nombre && filtros.funcionario_id && tareaFuncionario) nombre = tareaFuncionario.responsable || '';
            if (!nombre) nombre = 'Todos los funcionarios';
            if (!rol && tareaFuncionario) rol = tareaFuncionario.responsable_rol || '';
            if (tareaFuncionario) foto = tareaFuncionario.foto_responsable || '';

            return {
                nombre: nombre,
                rol: rol || 'Rol no especificado',
                foto: foto,
                iniciales: obtenerInicialesReporteActividades(nombre)
            };
        }

        function obtenerSucursalReporteActividades(filtros, tareas) {
            if (filtros.sucursal && filtros.sucursal !== 'Todas') return filtros.sucursal_texto || filtros.sucursal;

            const sucursales = Array.from(new Set(tareas.map(function (tarea) {
                return tarea.sucursal || '';
            }).filter(Boolean)));

            return sucursales.length === 1 ? sucursales[0] : 'Todas las sucursales';
        }

        function formatearFechaHoraReporteActividades(valor) {
            if (!valor) return '-';
            if (valor instanceof Date) {
                const dia = String(valor.getDate()).padStart(2, '0');
                const mes = String(valor.getMonth() + 1).padStart(2, '0');
                const anio = valor.getFullYear();
                const hora = String(valor.getHours()).padStart(2, '0');
                const minuto = String(valor.getMinutes()).padStart(2, '0');
                return dia + '/' + mes + '/' + anio + ' ' + hora + ':' + minuto;
            }

            const texto = String(valor).trim();
            const fecha = new Date(texto.replace(' ', 'T'));
            if (isNaN(fecha.getTime())) {
                return texto.length === 10 ? formatearFechaLegible(texto) : texto;
            }
            return formatearFechaHoraReporteActividades(fecha);
        }

        function limitarTextoReporteActividades(texto, maximo) {
            const limpio = String(texto || '').replace(/\s+/g, ' ').trim();
            if (limpio.length <= maximo) return limpio;
            return limpio.slice(0, Math.max(0, maximo - 3)) + '...';
        }

        function crearResumenHtmlReporteActividades(resumen) {
            const partes = [
                resumen.total + ' tareas',
                resumen.pendiente + ' pendientes',
                resumen.progreso + ' en proceso',
                resumen.culminada + ' culminadas',
                resumen.vencida + ' vencidas',
                resumen.anulada + ' anuladas'
            ];

            return '<p class="report-summary-line">' + partes.map(function (parte) {
                return escaparHtml(parte);
            }).join(' &middot; ') + '</p>';
        }

        function clonarFechaReporte(fecha) {
            const copia = new Date(fecha.getTime());
            copia.setHours(0, 0, 0, 0);
            return copia;
        }

        function sumarDiasReporte(fecha, dias) {
            const copia = clonarFechaReporte(fecha);
            copia.setDate(copia.getDate() + dias);
            return copia;
        }

        function diferenciaDiasReporte(inicio, fin) {
            const inicioLimpio = clonarFechaReporte(inicio);
            const finLimpio = clonarFechaReporte(fin);
            return Math.round((finLimpio - inicioLimpio) / 86400000);
        }

        function obtenerDiasReporteActividades(inicio, fin) {
            const dias = [];
            let actual = clonarFechaReporte(inicio);
            const final = clonarFechaReporte(fin);

            while (actual <= final) {
                dias.push({
                    fecha: clonarFechaReporte(actual),
                    iso: formatDate(actual),
                    dia: String(actual.getDate()).padStart(2, '0'),
                    mes: String(actual.getMonth() + 1).padStart(2, '0')
                });
                actual = sumarDiasReporte(actual, 1);
            }

            return dias;
        }

        function obtenerRangoGanttReporteActividades(filtros, tareas) {
            let inicio = parsearFechaReporte(filtros.desde);
            let fin = parsearFechaReporte(filtros.hasta);

            if (!inicio && tareas.length) {
                tareas.forEach(function (tarea) {
                    const fecha = parsearFechaReporte(tarea.start || tarea.fecha_inicio);
                    if (fecha && (!inicio || fecha < inicio)) inicio = fecha;
                });
            }

            if (!fin && tareas.length) {
                tareas.forEach(function (tarea) {
                    const fecha = parsearFechaReporte(tarea.end || tarea.fecha_fin);
                    if (fecha && (!fin || fecha > fin)) fin = fecha;
                });
            }

            if (!inicio) inicio = sumarDiasReporte(new Date(), -15);
            if (!fin) fin = sumarDiasReporte(new Date(), 15);
            if (fin < inicio) {
                const temporal = inicio;
                inicio = fin;
                fin = temporal;
            }

            return {
                inicio: inicio,
                fin: fin,
                dias: obtenerDiasReporteActividades(inicio, fin)
            };
        }

        function obtenerClaseEstadoReporteGantt(estado) {
            return 'status-' + String(estado || 'Pendiente').toLowerCase().replace(/\s+/g, '-');
        }

        function obtenerTextoVistaActualGantt() {
            if (vistaActual === 'Month') return 'Mes';
            if (vistaActual === 'Week') return 'Semana';
            return 'Dia';
        }

        function obtenerTareasVisiblesReporteActual() {
            let ids = obtenerIdsTareasDibujadasGantt();
            if (!ids.length) {
                ids = ordenTareasVisiblesGantt.length ? ordenTareasVisiblesGantt : Array.from(idsTareasVisiblesGantt);
            }

            return ids.map(function (id) {
                return allTasks.find(function (tarea) {
                    return String(tarea.id) === String(id);
                });
            }).filter(Boolean);
        }

        function fechaDesdeXGanttActual(x) {
            if (!gantt || !gantt.gantt_start) return '';

            const anchoColumna = obtenerAnchoColumnaVista();
            let dias = 0;
            if (vistaActual === 'Month') dias = (x / anchoColumna) * 30;
            else if (vistaActual === 'Week') dias = (x / anchoColumna) * 7;
            else dias = x / anchoColumna;

            const fecha = new Date(gantt.gantt_start);
            fecha.setHours(0, 0, 0, 0);
            fecha.setDate(fecha.getDate() + Math.max(0, Math.floor(dias)));
            return formatDate(fecha);
        }

        function obtenerMetricasSvgGanttActual() {
            const svg = document.getElementById('gantt');
            if (!svg) return null;

            let caja = null;
            try {
                caja = svg.getBBox();
            } catch (e) {
            }

            const rectSvg = svg.getBoundingClientRect();
            const anchoSvg = caja && caja.width ? caja.width : (svg.viewBox && svg.viewBox.baseVal ? svg.viewBox.baseVal.width : svg.clientWidth || 1200);
            const altoSvg = caja && caja.height ? caja.height : (svg.viewBox && svg.viewBox.baseVal ? svg.viewBox.baseVal.height : svg.clientHeight || 620);
            const escalaX = rectSvg && rectSvg.width ? anchoSvg / rectSvg.width : 1;

            return {
                svg: svg,
                caja: caja,
                rectSvg: rectSvg,
                anchoSvg: anchoSvg,
                altoSvg: altoSvg,
                escalaX: escalaX || 1
            };
        }

        function obtenerXDesdeEtiquetaVisibleGantt(svg, contenedor, anchoColumna) {
            if (!svg || !contenedor) return null;

            const rectContenedor = contenedor.getBoundingClientRect();
            const etiquetas = Array.from(svg.querySelectorAll('.lower-text, .upper-text')).filter(function (etiqueta) {
                const rect = etiqueta.getBoundingClientRect();
                return rect.width > 0
                    && rect.height > 0
                    && rect.right >= rectContenedor.left
                    && rect.left <= rectContenedor.right
                    && rect.bottom >= rectContenedor.top
                    && rect.top <= rectContenedor.bottom;
            }).sort(function (a, b) {
                return a.getBoundingClientRect().left - b.getBoundingClientRect().left;
            });

            if (!etiquetas.length || !svg.createSVGPoint || !svg.getScreenCTM()) return null;

            try {
                const primera = etiquetas[0];
                const rectPrimera = primera.getBoundingClientRect();
                const punto = svg.createSVGPoint();
                punto.x = rectPrimera.left + (rectPrimera.width / 2);
                punto.y = rectPrimera.top + (rectPrimera.height / 2);
                const puntoSvg = punto.matrixTransform(svg.getScreenCTM().inverse());
                return Math.max(0, puntoSvg.x - (anchoColumna / 2));
            } catch (e) {
                return null;
            }
        }

        function obtenerXVisibleGanttActual(metricas) {
            if (!metricas || !metricas.svg) return 0;

            const contenedorPrincipal = document.getElementById('gantt-container');
            const contenedor = obtenerContenedorScrollGantt() || contenedorPrincipal;
            const candidatos = [];

            if (contenedor) {
                candidatos.push(Number(contenedor.scrollLeft) || 0);

                const rectContenedor = contenedor.getBoundingClientRect();
                if (metricas.rectSvg && rectContenedor && metricas.rectSvg.width) {
                    candidatos.push(Math.max(0, (rectContenedor.left - metricas.rectSvg.left) * metricas.escalaX));
                }
            }

            if (contenedorPrincipal) {
                const scrollables = [contenedorPrincipal].concat(Array.from(contenedorPrincipal.querySelectorAll('*')));
                scrollables.forEach(function (elemento) {
                    if (elemento && elemento.scrollWidth > elemento.clientWidth) {
                        candidatos.push(Number(elemento.scrollLeft) || 0);
                    }
                });

                const xEtiqueta = obtenerXDesdeEtiquetaVisibleGantt(metricas.svg, contenedorPrincipal, obtenerAnchoColumnaVista());
                if (xEtiqueta !== null) candidatos.push(xEtiqueta);
            }

            const x = Math.max.apply(null, candidatos.filter(function (valor) {
                return isFinite(valor) && valor >= 0;
            }).concat([0]));

            return Math.max(0, Math.min(x, Math.max(0, metricas.anchoSvg - 1)));
        }

        function obtenerRecorteSvgGanttActual() {
            const metricas = obtenerMetricasSvgGanttActual();
            if (!metricas || !metricas.svg) return null;

            const contenedor = obtenerContenedorScrollGantt();
            const anchoVisible = contenedor && contenedor.clientWidth
                ? Math.max(1, contenedor.clientWidth * metricas.escalaX)
                : (metricas.svg.clientWidth || metricas.anchoSvg);
            const x = obtenerXVisibleGanttActual(metricas);
            const y = 0;
            const ancho = Math.max(1, Math.min(anchoVisible, metricas.anchoSvg - x));
            const alto = Math.max(1, metricas.altoSvg);

            return {
                svg: metricas.svg,
                x: x,
                y: y,
                ancho: ancho,
                alto: alto,
                inicioStr: fechaDesdeXGanttActual(x),
                finStr: fechaDesdeXGanttActual(x + ancho)
            };
        }

        function obtenerRangoVisibleGanttActual() {
            const recorte = obtenerRecorteSvgGanttActual();
            if (recorte && recorte.inicioStr && recorte.finStr) {
                return {
                    inicioStr: recorte.inicioStr,
                    finStr: recorte.finStr
                };
            }

            const rango = obtenerRangoUltimoMesGantt();
            return {
                inicioStr: rango.inicioStr,
                finStr: rango.finStr
            };
        }

        function insertarEstilosSvgGanttImpresion(svgClone) {
            if (!svgClone) return;

            const style = document.createElementNS('http://www.w3.org/2000/svg', 'style');
            style.textContent = [
                '.grid-background{fill:#fff;stroke:none;}',
                '.grid-header{fill:#f9fafb;}',
                '.grid-row{fill:#fff;}',
                '.grid-row:nth-child(even){fill:#fcfcfd;}',
                '.row-line{stroke:#eef0f4;}',
                '.tick{stroke:#edf1f7;}',
                '.today-highlight{fill:#fff8dc;stroke:#f1d98a;opacity:.9;}',
                '.bar{rx:5;ry:5;fill:#64748b;visibility:visible;}',
                '.bar-progress{fill:rgba(255,255,255,.28);visibility:visible;}',
                '.bar-wrapper{visibility:visible;}',
                '.bar-label{fill:#fff;font-size:9.6px;font-weight:800;dominant-baseline:middle;paint-order:stroke;stroke:rgba(15,23,42,.38);stroke-width:1.8px;stroke-linejoin:round;text-rendering:geometricPrecision;}',
                '.lower-text,.upper-text{fill:#667085;font-size:10px;}',
                '.arrow{stroke:#94a3b8;fill:none;}',
                '.bar-general .bar{fill:#64748b;}',
                '.bar-CerroCora .bar,.bar-CerroCorá .bar,.bar-CerroCorá .bar{fill:#0ea5e9;}',
                '.bar-villamorra .bar{fill:#7c3aed;}',
                '.bar-Oviedo .bar{fill:#f97316;}',
                '.bar-SanLorenzo .bar{fill:#2563eb;}',
                '.bar-SantaLibrada .bar{fill:#db2777;}',
                '.bar-estado-pendiente .bar{fill:#475569;}',
                '.bar-estado-progreso .bar{fill:#1d4ed8;}',
                '.bar-estado-proxima .bar{fill:#b45309;}',
                '.bar-estado-vencida .bar{fill:#b91c1c;}',
                '.bar-estado-completada .bar{fill:#047857;}',
                '.bar-estado-anulada .bar{fill:#6b7280;opacity:.78;}',
                '.bar-hidden{display:none;}'
            ].join('\n');

            svgClone.insertBefore(style, svgClone.firstChild);
        }

        function obtenerSvgGanttVistaActualImpresion() {
            const recorte = obtenerRecorteSvgGanttActual();
            if (!recorte || !recorte.svg) {
                return '<p class="report-empty">No se encontro el diagrama de Gantt visible.</p>';
            }

            const svgClone = recorte.svg.cloneNode(true);
            svgClone.removeAttribute('style');
            svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svgClone.setAttribute('class', ((svgClone.getAttribute('class') || '') + ' gantt gantt-print-svg').trim());
            svgClone.setAttribute('width', '100%');
            svgClone.setAttribute('height', String(Math.round(recorte.alto)));
            svgClone.setAttribute('viewBox', [recorte.x, recorte.y, recorte.ancho, recorte.alto].join(' '));
            svgClone.setAttribute('preserveAspectRatio', 'xMinYMin meet');
            insertarEstilosSvgGanttImpresion(svgClone);
            svgClone.querySelectorAll('.bar').forEach(function (barra) {
                if (!barra.getAttribute('fill') && !/fill\s*:/.test(barra.getAttribute('style') || '')) {
                    barra.setAttribute('fill', '#64748b');
                }
                barra.setAttribute('visibility', 'visible');
                barra.style.visibility = 'visible';
            });
            svgClone.querySelectorAll('.bar-progress').forEach(function (barra) {
                barra.setAttribute('visibility', 'visible');
                barra.style.visibility = 'visible';
            });
            svgClone.querySelectorAll('.bar-wrapper').forEach(function (wrapper) {
                wrapper.setAttribute('visibility', 'visible');
                wrapper.style.visibility = 'visible';
            });

            return new XMLSerializer().serializeToString(svgClone);
        }

        function crearGanttHtmlReporteActividades() {
            const rango = obtenerRangoVisibleGanttActual();
            const svgHtml = obtenerSvgGanttVistaActualImpresion();

            return ''
                + '<section class="report-gantt-section">'
                + '<div class="section-heading"><h2>Diagrama de Gantt</h2><span>' + escaparHtml(formatearFechaLegible(rango.inicioStr) + ' al ' + formatearFechaLegible(rango.finStr) + ' - Vista: ' + obtenerTextoVistaActualGantt()) + '</span></div>'
                + '<div class="gantt-legend">'
                + '<span><i class="status-pendiente"></i>Pendiente</span>'
                + '<span><i class="status-en-progreso"></i>En proceso</span>'
                + '<span><i class="status-completada"></i>Culminada</span>'
                + '<span><i class="status-vencida"></i>Vencida</span>'
                + '<span><i class="status-anulada"></i>Anulada</span>'
                + '</div>'
                + '<div class="print-gantt-current">' + svgHtml + '</div>'
                + '</section>';
        }

        function crearNombreArchivoReporteActividades(funcionario, filtros) {
            const nombre = String(funcionario.nombre || 'funcionario')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '') || 'funcionario';
            const desde = filtros.desde || 'inicio';
            const hasta = filtros.hasta || 'fin';
            return 'reporte_gantt_actividades_' + nombre + '_' + desde + '_' + hasta + '.pdf';
        }

        function prepararVistaGanttParaImpresion(callback) {
            sincronizarTablaConBarrasGantt();
            decorarBarrasGanttResponsables();
            configurarTooltipsGantt();

            requestAnimationFrame(function () {
                sincronizarTablaConBarrasGantt();
                decorarBarrasGanttResponsables();
                if (typeof callback === 'function') callback();
            });
        }

        function crearHtmlReporteActividades(accion) {
            const filtros = obtenerFiltrosReporteActividades();
            const tareas = obtenerTareasVisiblesReporteActual();
            const resumen = calcularResumenReporteActividades(tareas);
            const funcionario = obtenerFuncionarioReporteActividades(filtros, tareas);
            const sucursal = obtenerSucursalReporteActividades(filtros, tareas);
            const rangoGantt = obtenerRangoVisibleGanttActual();
            const generado = new Date();
            const usuarioGenerador = obtenerNombreUsuarioGantt();
            const pageSize = 'A4 landscape';
            const pageWidth = '297mm';
            const pageMinHeight = '210mm';
            const periodo = (rangoGantt.inicioStr ? formatearFechaLegible(rangoGantt.inicioStr) : 'Sin fecha inicial')
                + ' al '
                + (rangoGantt.finStr ? formatearFechaLegible(rangoGantt.finStr) : 'Sin fecha final');
            const estado = filtros.estado && filtros.estado !== 'Todas'
                ? etiquetaEstadoReporteActividades(filtros.estado)
                : 'Todas';
            const archivo = crearNombreArchivoReporteActividades(funcionario, filtros);
            const avatarHtml = funcionario.foto
                ? '<img class="employee-avatar" src="' + escaparHtml(funcionario.foto) + '" alt="' + escaparHtml(funcionario.nombre) + '">'
                : '<div class="employee-avatar employee-avatar-fallback">' + escaparHtml(funcionario.iniciales) + '</div>';
            const accionesHtml = accion === 'preview'
                ? '<div class="screen-actions"><button onclick="window.print()">Imprimir</button><button onclick="window.print()">Generar PDF</button><button onclick="window.print()">Descargar PDF</button></div>'
                : '';

            const html = '<!DOCTYPE html>'
                + '<html lang="es"><head><meta charset="UTF-8">'
                + '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                + '<base href="' + escaparHtml(window.location.href.split('#')[0]) + '">'
                + '<title>' + escaparHtml(archivo) + '</title>'
                + '<style>'
                + '@page{size:' + pageSize + ';margin:8mm 8mm 12mm;}'
                + '*{box-sizing:border-box;}'
                + 'body{margin:0;background:#edf1f5;color:#172033;font-family:Arial,Helvetica,sans-serif;font-size:10px;line-height:1.28;}'
                + '.screen-actions{position:sticky;top:0;z-index:10;display:flex;gap:8px;justify-content:flex-end;padding:10px 14px;background:#172033;border-bottom:1px solid #0f172a;}'
                + '.screen-actions button{border:1px solid #cbd5e1;background:#fff;color:#172033;border-radius:4px;padding:7px 11px;font-size:12px;font-weight:700;cursor:pointer;}'
                + '.report-page{width:' + pageWidth + ';min-height:' + pageMinHeight + ';margin:12px auto;padding:0 0 12mm;background:#fff;box-shadow:0 10px 28px rgba(15,23,42,.16);position:relative;}'
                + '.report-inner{padding:8mm 8mm 6mm;}'
                + '.report-header{display:grid;grid-template-columns:28mm 1fr 20mm;gap:5mm;align-items:center;border-bottom:1px solid #d8e0e8;padding-bottom:4mm;}'
                + '.brand-logo{width:25mm;max-height:14mm;object-fit:contain;}'
                + '.report-title h1{margin:0 0 2mm;font-size:18px;line-height:1;color:#172033;font-weight:800;}'
                + '.report-title p{margin:0;color:#5f6b7a;font-size:9.5px;}'
                + '.header-meta{display:grid;grid-template-columns:repeat(4,1fr);gap:2mm 4mm;margin-top:3mm;}'
                + '.header-meta span{display:block;color:#667085;font-size:7.8px;text-transform:uppercase;letter-spacing:.04em;}'
                + '.header-meta strong{display:block;color:#111827;font-size:9px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}'
                + '.employee-box{text-align:center;}'
                + '.employee-avatar{width:18mm;height:18mm;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;background:#f8fafc;display:inline-flex;align-items:center;justify-content:center;color:#172033;font-weight:800;font-size:11px;}'
                + '.employee-avatar-fallback{font-size:11px;}'
                + '.report-summary-line{margin:3mm 0 3mm;padding:2mm 3mm;border:1px solid #d8e0e8;border-radius:6px;background:#fbfcfe;color:#344054;font-size:9.3px;font-weight:700;}'
                + '.section-heading{display:flex;justify-content:space-between;gap:4mm;align-items:end;margin:0 0 2mm;}'
                + '.section-heading h2{margin:0;color:#172033;font-size:12px;}'
                + '.section-heading span{color:#667085;font-size:8.5px;font-weight:700;}'
                + '.report-gantt-section{break-inside:auto;page-break-inside:auto;}'
                + '.gantt-legend{display:flex;gap:4mm;align-items:center;margin:0 0 2mm;color:#475467;font-size:8.2px;}'
                + '.gantt-legend span{display:inline-flex;align-items:center;gap:1.5mm;}'
                + '.gantt-legend i{width:12px;height:7px;border-radius:999px;display:inline-block;border:1px solid transparent;}'
                + '.print-gantt-current{border:1px solid #cbd5e1;border-radius:7px;overflow:hidden;background:#fff;line-height:0;}'
                + '.gantt-print-svg{display:block;width:100%;height:auto;max-height:154mm;background:#fff;}'
                + '.gantt-print-svg .grid-background{fill:#fff!important;stroke:none!important;}'
                + '.gantt-print-svg .grid-header{fill:#f9fafb!important;}'
                + '.gantt-print-svg .grid-row{fill:#fff!important;}'
                + '.gantt-print-svg .grid-row:nth-child(even){fill:#fcfcfd!important;}'
                + '.gantt-print-svg .row-line{stroke:#eef0f4!important;}'
                + '.gantt-print-svg .tick{stroke:#edf1f7!important;}'
                + '.gantt-print-svg .today-highlight{fill:#fff8dc!important;stroke:#f1d98a!important;opacity:.9;}'
                + '.gantt-print-svg .bar{rx:5;ry:5;}'
                + '.gantt-print-svg .bar-label{fill:#fff!important;font-size:9.6px;font-weight:800;dominant-baseline:middle;paint-order:stroke;stroke:rgba(15,23,42,.38);stroke-width:1.8px;stroke-linejoin:round;text-rendering:geometricPrecision;}'
                + '.gantt-print-svg .lower-text,.gantt-print-svg .upper-text{fill:#667085!important;font-size:10px;}'
                + '.gantt-print-svg .arrow{stroke:#94a3b8!important;fill:none!important;}'
                + '.gantt-print-svg .bar-progress{fill:rgba(255,255,255,.28)!important;}'
                + '.gantt-print-svg .bar-general .bar{fill:#64748b!important;}'
                + '.gantt-print-svg .bar-CerroCora .bar,.gantt-print-svg .bar-CerroCorá .bar,.gantt-print-svg .bar-CerroCorá .bar{fill:#0ea5e9!important;}'
                + '.gantt-print-svg .bar-villamorra .bar{fill:#7c3aed!important;}'
                + '.gantt-print-svg .bar-Oviedo .bar{fill:#f97316!important;}'
                + '.gantt-print-svg .bar-SanLorenzo .bar{fill:#2563eb!important;}'
                + '.gantt-print-svg .bar-SantaLibrada .bar{fill:#db2777!important;}'
                + '.gantt-print-svg .bar-estado-pendiente .bar{fill:#475569!important;}'
                + '.gantt-print-svg .bar-estado-progreso .bar{fill:#1d4ed8!important;}'
                + '.gantt-print-svg .bar-estado-proxima .bar{fill:#b45309!important;}'
                + '.gantt-print-svg .bar-estado-vencida .bar{fill:#b91c1c!important;}'
                + '.gantt-print-svg .bar-estado-completada .bar{fill:#047857!important;}'
                + '.gantt-print-svg .bar-estado-anulada .bar{fill:#6b7280!important;opacity:.78;}'
                + '.gantt-print-svg .bar-hidden{display:none!important;}'
                + '.status-pendiente{background:#e9eef5;color:#344054;border-color:#cbd5e1;}'
                + '.status-en-progreso{background:#dbeafe;color:#1d4ed8;border-color:#93c5fd;}'
                + '.status-completada{background:#dcfce7;color:#047857;border-color:#86efac;}'
                + '.status-vencida{background:#fee2e2;color:#b42318;border-color:#fca5a5;}'
                + '.status-anulada{background:#e5e7eb;color:#4b5563;border-color:#cbd5e1;}'
                + '.report-empty{border:1px dashed #cbd5e1;background:#f8fafc;color:#667085;padding:12px;border-radius:6px;}'
                + '.report-footer{position:fixed;left:10mm;right:10mm;bottom:5mm;border-top:1px solid #d8e0e8;padding-top:3mm;color:#667085;font-size:9px;text-align:center;background:#fff;}'
                + '.page-number:after{content:counter(page);}'
                + '@media print{body{background:#fff;}.screen-actions{display:none!important;}.report-page{width:auto;min-height:auto;margin:0;padding:0;box-shadow:none;}.report-inner{padding:0 0 9mm;}.report-footer{bottom:0;}}'
                + '</style></head><body>'
                + accionesHtml
                + '<main class="report-page">'
                + '<div class="report-inner">'
                + '<header class="report-header">'
                + '<div><img class="brand-logo" src="/GoodVentaAsisCap/iconos/Logo.jpg" alt="Clinident Salud"></div>'
                + '<div class="report-title"><h1>Reporte Gantt de actividades</h1><p>Vista imprimible del cronograma por funcionario/responsable.</p>'
                + '<div class="header-meta">'
                + '<div><span>Funcionario</span><strong>' + escaparHtml(funcionario.nombre) + '</strong></div>'
                + '<div><span>Cargo o rol</span><strong>' + escaparHtml(funcionario.rol) + '</strong></div>'
                + '<div><span>Sucursal</span><strong>' + escaparHtml(sucursal) + '</strong></div>'
                + '<div><span>Periodo</span><strong>' + escaparHtml(periodo) + '</strong></div>'
                + '<div><span>Estado</span><strong>' + escaparHtml(estado) + '</strong></div>'
                + '<div><span>Generado por</span><strong>' + escaparHtml(usuarioGenerador) + '</strong></div>'
                + '<div><span>Emision</span><strong>' + escaparHtml(formatearFechaHoraReporteActividades(generado)) + '</strong></div>'
                + '<div><span>Vista</span><strong>' + escaparHtml(obtenerTextoVistaActualGantt()) + '</strong></div>'
                + '</div></div>'
                + '<div class="employee-box">' + avatarHtml + '</div>'
                + '</header>'
                + crearResumenHtmlReporteActividades(resumen)
                + crearGanttHtmlReporteActividades()
                + '</div>'
                + '<footer class="report-footer">Clinident Salud &middot; Reporte generado el ' + escaparHtml(formatearFechaHoraReporteActividades(generado)) + ' &middot; Pagina <span class="page-number"></span></footer>'
                + '</main>'
                + '</body></html>';

            return { html: html, archivo: archivo };
        }

        function abrirVentanaReporteActividades(accion) {
            const ventana = window.open('', '_blank');
            if (!ventana) {
                alert('No se pudo abrir la vista previa. Habilite ventanas emergentes para generar el reporte.');
                return;
            }

            ventana.document.open();
            ventana.document.write('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Preparando reporte</title></head><body style="font-family:Arial,sans-serif;padding:18px;color:#172033;">Preparando vista actual del Gantt...</body></html>');
            ventana.document.close();
            ventana.focus();

            prepararVistaGanttParaImpresion(function () {
                try {
                    const reporte = crearHtmlReporteActividades(accion);
                    ventana.document.open();
                    ventana.document.write(reporte.html);
                    ventana.document.close();
                    ventana.focus();

                    if (accion !== 'preview') {
                        setTimeout(function () {
                            ventana.document.title = reporte.archivo;
                            ventana.print();
                        }, 250);
                    }
                } catch (error) {
                    ventana.document.open();
                    ventana.document.write('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error reporte</title></head><body style="font-family:Arial,sans-serif;padding:18px;color:#991b1b;"><h3>No se pudo preparar el reporte Gantt</h3><p>' + escaparHtml(error && error.message ? error.message : error) + '</p></body></html>');
                    ventana.document.close();
                }
            });
        }

        function vistaPreviaReporteActividades() {
            abrirVentanaReporteActividades('preview');
        }

        function imprimirReporteActividades() {
            abrirVentanaReporteActividades('print');
        }

        function generarPdfReporteActividades() {
            abrirVentanaReporteActividades('pdf');
        }

        function descargarPdfReporteActividades() {
            abrirVentanaReporteActividades('download');
        }

        function imprimirListaYGantt() {
            imprimirReporteActividades();
        }

        function formatearFechaLegible(fecha) {
            if (!fecha) return '-';
            const partes = String(fecha).split('-');
            if (partes.length !== 3) return fecha;
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }

        function obtenerTooltipGantt() {
            let tooltip = document.getElementById('gantt-tooltip-custom');
            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.id = 'gantt-tooltip-custom';
                tooltip.className = 'gantt-tooltip-custom';
                document.body.appendChild(tooltip);
            }
            return tooltip;
        }

        function moverTooltipGantt(evento) {
            const tooltip = obtenerTooltipGantt();
            const margen = 14;
            const ancho = tooltip.offsetWidth || 280;
            const alto = tooltip.offsetHeight || 120;
            let left = evento.clientX + margen;
            let top = evento.clientY + margen;

            if (left + ancho > window.innerWidth - 8) {
                left = evento.clientX - ancho - margen;
            }

            if (top + alto > window.innerHeight - 8) {
                top = evento.clientY - alto - margen;
            }

            tooltip.style.left = Math.max(8, left) + 'px';
            tooltip.style.top = Math.max(8, top) + 'px';
        }

        function mostrarTooltipGantt(tarea, evento) {
            const tooltip = obtenerTooltipGantt();
            tooltip.innerHTML =
                '<div class="gantt-tooltip-custom__title">' + escaparHtml(tarea.titulo_original || tarea.name) + '</div>' +
                '<div class="gantt-tooltip-custom__meta">' +
                    '<b>Responsable</b><span>' + escaparHtml(tarea.responsable || 'Sin asignar') + '</span>' +
                    '<b>Vinculados</b><span>' + escaparHtml(tarea.usuarios_vinculados || '-') + '</span>' +
                    '<b>Estado</b><span>' + escaparHtml(tarea.estado_visual || tarea.estado || 'Pendiente') + '</span>' +
                    '<b>Fechas</b><span>' + escaparHtml(formatearFechaLegible(tarea.start)) + ' - ' + escaparHtml(formatearFechaLegible(tarea.end)) + '</span>' +
                    '<b>Sucursal</b><span>' + escaparHtml(tarea.sucursal || '-') + '</span>' +
                    '<b>Progreso</b><span>' + escaparHtml((tarea.progress || 0) + '%') + '</span>' +
                '</div>';
            tooltip.classList.add('visible');
            moverTooltipGantt(evento);
        }

        function ocultarTooltipGantt() {
            const tooltip = document.getElementById('gantt-tooltip-custom');
            if (tooltip) tooltip.classList.remove('visible');
        }

        function configurarTooltipsGantt() {
            document.querySelectorAll('#gantt .bar-wrapper[data-id]').forEach(function (wrapper) {
                if (wrapper.dataset.tooltipReady === '1') return;

                const id = wrapper.getAttribute('data-id');
                if (!id || id === '__horizon__') return;

                wrapper.dataset.tooltipReady = '1';
                wrapper.addEventListener('mouseenter', function (evento) {
                    const tarea = obtenerTareaGanttPorId(id);
                    if (tarea) mostrarTooltipGantt(tarea, evento);
                });
                wrapper.addEventListener('mousemove', moverTooltipGantt);
                wrapper.addEventListener('mouseleave', ocultarTooltipGantt);
            });
        }

        function obtenerRangoUltimoMesGantt() {
            const fin = new Date();
            fin.setHours(0, 0, 0, 0);

            const inicio = new Date(fin);
            inicio.setMonth(inicio.getMonth() - 1);
            inicio.setHours(0, 0, 0, 0);

            return {
                inicio: inicio,
                fin: fin,
                inicioStr: formatDate(inicio),
                finStr: formatDate(fin)
            };
        }

        function tareaPerteneceAlMesActual(tarea) {
            const hoy = new Date();
            const inicioMes = new Date(hoy);
            const finMes = new Date(hoy);
            inicioMes.setDate(hoy.getDate() - 30);
            finMes.setDate(hoy.getDate() + 30);

            const inicioTarea = new Date(tarea.start + 'T00:00:00');
            const finTarea = new Date(tarea.end + 'T00:00:00');

            return inicioTarea <= finMes && finTarea >= inicioMes;
        }

        function obtenerAnchoContenidoGantt() {
            const svg = document.getElementById('gantt');
            const contenedorExterno = document.getElementById('gantt-container');
            const contenedorInterno = contenedorExterno ? contenedorExterno.querySelector('.gantt-container') : null;
            let anchoContenido = 0;

            if (svg) {
                anchoContenido = Math.max(anchoContenido, Number(svg.getAttribute('width')) || 0);

                try {
                    if (svg.viewBox && svg.viewBox.baseVal) {
                        anchoContenido = Math.max(anchoContenido, Number(svg.viewBox.baseVal.width) || 0);
                    }
                } catch (e) {
                }

                try {
                    const cajaSvg = svg.getBBox();
                    anchoContenido = Math.max(anchoContenido, Math.ceil(cajaSvg.x + cajaSvg.width));
                } catch (e) {
                }
            }

            if (contenedorInterno) {
                anchoContenido = Math.max(anchoContenido, contenedorInterno.scrollWidth || 0);
            }

            if (contenedorExterno) {
                anchoContenido = Math.max(anchoContenido, contenedorExterno.scrollWidth || 0, contenedorExterno.clientWidth || 0);
            }

            return Math.ceil(anchoContenido);
        }

        function normalizarAnchoScrollGantt(recalcular) {
            const svg = document.getElementById('gantt');
            const contenedorExterno = document.getElementById('gantt-container');
            const contenedorInterno = contenedorExterno ? contenedorExterno.querySelector('.gantt-container') : null;
            if (recalcular && svg) {
                svg.style.width = '';
                svg.style.minWidth = '';
            }
            if (recalcular && contenedorInterno) {
                contenedorInterno.style.width = '';
                contenedorInterno.style.minWidth = '';
            }

            const anchoContenido = obtenerAnchoContenidoGantt();
            if (!svg || !contenedorExterno || !anchoContenido) return;

            const anchoMinimo = Math.max(anchoContenido, contenedorExterno.clientWidth || 0);
            svg.style.width = anchoMinimo + 'px';
            svg.style.minWidth = anchoMinimo + 'px';
            svg.setAttribute('width', anchoMinimo);

            if (contenedorInterno) {
                contenedorInterno.style.width = anchoMinimo + 'px';
                contenedorInterno.style.minWidth = anchoMinimo + 'px';
            }
        }

        function obtenerContenedorScrollGantt() {
            const contenedorExterno = document.getElementById('gantt-container');
            if (!contenedorExterno) return null;

            normalizarAnchoScrollGantt(false);

            const contenedorInterno = contenedorExterno.querySelector('.gantt-container');
            if (contenedorInterno && (contenedorInterno.scrollLeft > 0 || contenedorInterno.scrollTop > 0)) {
                return contenedorInterno;
            }

            if (contenedorExterno.scrollLeft > 0 || contenedorExterno.scrollTop > 0) {
                return contenedorExterno;
            }

            if (contenedorInterno && (contenedorInterno.scrollWidth > contenedorInterno.clientWidth || contenedorInterno.scrollHeight > contenedorInterno.clientHeight)) {
                return contenedorInterno;
            }

            if (contenedorExterno.scrollWidth > contenedorExterno.clientWidth) {
                return contenedorExterno;
            }

            return contenedorExterno;
        }

        function obtenerNombreUsuarioGantt() {
            const idsNombre = ['pUsuarioCabecera', 'lblUser', 'ptituloUser2', 'bNombreUser', 'nombrePerfilUsuario'];
            const documentos = [document];

            try {
                if (window.parent && window.parent !== window && window.parent.document) {
                    documentos.push(window.parent.document);
                }
            } catch (e) {
            }

            for (const doc of documentos) {
                for (const id of idsNombre) {
                    const elemento = doc.getElementById(id);
                    const nombre = elemento ? elemento.textContent.trim() : '';
                    if (nombre && nombre.toLowerCase() !== 'usuario') return nombre;
                }
            }

            try {
                const parentUserId = window.parent && window.parent.userid ? window.parent.userid : '';
                const nombreGuardado = parentUserId ? localStorage.getItem('nombreUsuario' + parentUserId) : '';
                if (nombreGuardado) return nombreGuardado;
            } catch (e) {
            }

            return 'usuario';
        }

        function actualizarSaludoUsuarioGantt() {
            const etiquetaUsuario = document.getElementById('grant-user-name');
            if (etiquetaUsuario) {
                etiquetaUsuario.textContent = obtenerNombreUsuarioGantt();
            }
        }

        function obtenerUsuarioActualIdGantt() {
            try {
                if (window.parent && window.parent !== window && typeof window.parent.buscar_datos_url_usuario === 'function') {
                    const usuarioUrlPadre = window.parent.buscar_datos_url_usuario('q');
                    if (usuarioUrlPadre) return String(usuarioUrlPadre);
                }
            } catch (e) {
            }

            try {
                if (window.parent && window.parent !== window && typeof window.parent.buscar_este_cookie === 'function') {
                    const usuarioCookiePadre = window.parent.buscar_este_cookie('user');
                    if (usuarioCookiePadre) return String(usuarioCookiePadre);
                }
            } catch (e) {
            }

            try {
                if (window.parent && window.parent !== window && window.parent.userid) {
                    return String(window.parent.userid);
                }
            } catch (e) {
            }

            try {
                if (window.opener && window.opener.userid) {
                    return String(window.opener.userid);
                }
            } catch (e) {
            }

            try {
                if (typeof userid !== 'undefined' && userid) {
                    return String(userid);
                }
            } catch (e) {
            }

            return '';
        }

        function obtenerParametroUrlGantt(nombre) {
            const parametros = new URLSearchParams(window.location.search);
            return parametros.get(nombre) || '';
        }

        function seleccionarUsuarioActualFiltroGantt(usuarioForzado) {
            const filtroUsuario = document.getElementById('filtro-usuario');
            if (!filtroUsuario) return false;

            const usuarioActual = usuarioForzado
                ? String(usuarioForzado)
                : (obtenerParametroUrlGantt('usuario') || obtenerParametroUrlGantt('q') || obtenerUsuarioActualIdGantt());
            if (!usuarioActual) return false;

            const existeUsuario = Array.from(filtroUsuario.options).some(function (option) {
                return String(option.value) === usuarioActual;
            });

            if (!existeUsuario) return false;

            filtroUsuario.value = usuarioActual;
            usuarioActualGantt = usuarioActual;
            return true;
        }

        function obtenerUsuarioActualFormularioGantt(usuarioForzado) {
            const usuarioActual = usuarioForzado
                ? String(usuarioForzado)
                : (usuarioActualGantt || obtenerParametroUrlGantt('usuario') || obtenerParametroUrlGantt('q') || obtenerUsuarioActualIdGantt());
            if (usuarioActual) usuarioActualGantt = usuarioActual;
            return usuarioActual;
        }

        function seleccionarResponsableUsuarioActualGantt(usuarioForzado, forzarSeleccion) {
            const selectResponsable = document.getElementById('form_responsable');
            if (!selectResponsable) return false;

            const idFormulario = document.getElementById('form_id');
            if (!forzarSeleccion && idFormulario && idFormulario.value) return false;
            if (!forzarSeleccion && selectResponsable.value) return false;

            const usuarioActual = obtenerUsuarioActualFormularioGantt(usuarioForzado);
            if (!usuarioActual) return false;

            const opcionResponsable = Array.from(selectResponsable.options).find(function (option) {
                return String(option.dataset.usuarioId || '') === String(usuarioActual);
            });

            if (!opcionResponsable) return false;

            selectResponsable.value = opcionResponsable.value;
            return true;
        }

        function iniciarFiltroUsuarioActualGantt(intentos = 0) {
            if (seleccionarUsuarioActualFiltroGantt()) {
                seleccionarResponsableUsuarioActualGantt('', false);
                aplicarFiltros();
                return;
            }

            if (intentos < 25) {
                setTimeout(function () {
                    iniciarFiltroUsuarioActualGantt(intentos + 1);
                }, 160);
                return;
            }

            const filtroUsuario = document.getElementById('filtro-usuario');
            if (filtroUsuario) filtroUsuario.value = '';
            programarCentradoFechaActual();
        }

        window.aplicarUsuarioActualFiltroGantt = function (usuario) {
            seleccionarResponsableUsuarioActualGantt(usuario, false);
            if (seleccionarUsuarioActualFiltroGantt(usuario)) {
                aplicarFiltros();
            }
        };

        function posicionarGanttEnHoy(intentos = 0) {
            setTimeout(function () {
                const contenedor = obtenerContenedorScrollGantt();
                const panelDerecho = document.querySelector('.gantt-right-panel');
                if (!contenedor) return;

                if ((!gantt || !gantt.gantt_start) && intentos < 20) {
                    posicionarGanttEnHoy(intentos + 1);
                    return;
                }

                normalizarAnchoScrollGantt(false);

                const marcaHoy = document.querySelector('#gantt .today-highlight');
                const posicionMarcaHoy = obtenerPosicionMarcaHoy(marcaHoy, contenedor);
                const anchoVisible = contenedor.clientWidth || (panelDerecho ? panelDerecho.clientWidth : 0) || 0;
                const xHoy = posicionMarcaHoy
                    ? posicionMarcaHoy.x + (posicionMarcaHoy.width / 2)
                    : calcularPosicionFechaHoy() + (obtenerAnchoColumnaVista() / 2);
                const scrollMaximo = Math.max(0, contenedor.scrollWidth - contenedor.clientWidth);
                const scrollObjetivo = Math.min(
                    scrollMaximo,
                    Math.max(0, xHoy - (anchoVisible / 2))
                );

                if (typeof contenedor.scrollTo === 'function') {
                    contenedor.scrollTo({ left: scrollObjetivo, behavior: 'auto' });
                } else {
                    contenedor.scrollLeft = scrollObjetivo;
                }

                const contenedorExterno = document.getElementById('gantt-container');
                if (contenedorExterno && contenedorExterno !== contenedor) {
                    contenedorExterno.scrollLeft = scrollObjetivo;
                }
                const contenedorInterno = contenedorExterno ? contenedorExterno.querySelector('.gantt-container') : null;
                if (contenedorInterno && contenedorInterno !== contenedor) {
                    contenedorInterno.scrollLeft = scrollObjetivo;
                }
                if (panelDerecho) panelDerecho.scrollLeft = scrollObjetivo;
            }, 120);
        }

        function obtenerPosicionMarcaHoy(marcaHoy, contenedor) {
            if (!marcaHoy) return null;

            try {
                const cajaMarca = marcaHoy.getBoundingClientRect();
                const cajaContenedor = contenedor.getBoundingClientRect();
                const centroMarcaVisible = cajaMarca.left - cajaContenedor.left + contenedor.scrollLeft + (cajaMarca.width / 2);

                return {
                    x: centroMarcaVisible - (cajaMarca.width / 2),
                    width: cajaMarca.width
                };
            } catch (e) {
            }

            let x = Number(marcaHoy.getAttribute('x'));
            let width = Number(marcaHoy.getAttribute('width'));

            if ((!x && x !== 0) || !width) {
                try {
                    const caja = marcaHoy.getBBox();
                    x = caja.x;
                    width = caja.width;
                } catch (e) {
                    return null;
                }
            }

            return { x, width };
        }

        function calcularPosicionFechaHoy() {
            if (!gantt || !gantt.gantt_start) return 0;

            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            const inicio = new Date(gantt.gantt_start);
            inicio.setHours(0, 0, 0, 0);

            const diasDesdeInicio = Math.max(0, Math.floor((hoy - inicio) / 86400000));
            const anchoColumna = obtenerAnchoColumnaVista();

            if (vistaActual === 'Month') return (diasDesdeInicio / 30) * anchoColumna;
            if (vistaActual === 'Week') return (diasDesdeInicio / 7) * anchoColumna;

            return diasDesdeInicio * anchoColumna;
        }

        function obtenerAnchoColumnaVista() {
            if (gantt && gantt.options && gantt.options.column_width) {
                return Number(gantt.options.column_width) || 38;
            }

            if (vistaActual === 'Month') return 120;
            if (vistaActual === 'Week') return 140;

            return 38;
        }

        // ✅ NUEVA función auxiliar: Date → "YYYY-MM-DD"
        function formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        // Función unificada para enviar actualizaciones (fechas o progreso)
        function enviarActualizacionBack(id, start, end, progress) {
            ganttBloqueoClickRapidoHasta = Date.now() + 700;

            llamarApiGantt('actualizar_fechas', {
                id: id,
                start: start,
                end: end,
                progress: progress,
                origen: 'grilla Gantt'
            })
                .then(function (data) {
                    if (data.task) {
                        upsertTareaGantt(data.task);
                        aplicarFiltros();
                    }
                    mostrarToastGantt(data.message || 'Tarea reprogramada.', data.undo);
                })
                .catch(function (error) {
                    mostrarToastGantt(error.message || 'No se pudo guardar el cambio.');
                    aplicarFiltros();
                });
            return;



            fetch("update_task.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id, start, end, progress })
            })
                .then(r => r.json())
                .then(data => {

                    if (data.status !== 'success') alert("Error: " + data.message);
                })
                .catch(err => console.error('❌ Error fetch:', err));
        }

        // Sincronizar Scroll
        let contenedorScrollGanttActual = null;

        function sincronizarScrollListaTareas() {
            const contenedor = obtenerContenedorScrollGantt();
            const lista = document.getElementById('list-container');
            if (contenedor && lista) {
                lista.scrollTop = contenedor.scrollTop;
            }
        }

        function notificarScrollbarDashboardGantt() {
            try {
                if (window.parent && window.parent !== window && typeof window.parent.sincronizarGrantDashboardScrollbar === 'function') {
                    window.parent.sincronizarGrantDashboardScrollbar();
                }
            } catch (e) {
            }
        }

        function configurarScrollGantt() {
            normalizarAnchoScrollGantt(true);
            const contenedor = obtenerContenedorScrollGantt();
            if (contenedor && contenedor !== contenedorScrollGanttActual) {
                if (contenedorScrollGanttActual) {
                    contenedorScrollGanttActual.removeEventListener('scroll', sincronizarScrollListaTareas);
                }
                contenedor.addEventListener('scroll', sincronizarScrollListaTareas);
                contenedorScrollGanttActual = contenedor;
            }
            notificarScrollbarDashboardGantt();
        }

        configurarScrollGantt();

        // Inicializar
        renderGantt(allTasks);

        // Control de Vistas
        function changeGanttView(mode) {
            vistaActual = mode;
            if (!gantt) return;
            gantt.change_view_mode(mode);
            configurarScrollGantt();
            setTimeout(configurarScrollGantt, 180);
            setTimeout(configurarScrollGantt, 520);
            document.querySelectorAll('.view-btn:not(.task-toggle-btn)').forEach(btn => btn.classList.remove('active'));
            document.getElementById('btn-' + mode).classList.add('active');
            mostrarMesEnFechasGantt();
            setTimeout(sincronizarTablaConBarrasGantt, 180);
            setTimeout(decorarBarrasGanttResponsables, 180);
            setTimeout(configurarTooltipsGantt, 220);
            programarCentradoFechaActual();
        }

        // Filtros Visuales Intercomunicados (Tabla + Gráfico)
        function toggleTaskList() {
            const layout = document.getElementById('gantt-layout');
            const boton = document.getElementById('btn-toggle-tasks');
            if (!layout || !boton) return;

            const estaPlegado = layout.classList.toggle('task-list-collapsed');
            boton.textContent = estaPlegado ? 'Mostrar tareas' : 'Ocultar tareas';
            boton.classList.toggle('active', estaPlegado);

            setTimeout(function () {
                mostrarMesEnFechasGantt();
                sincronizarTablaConBarrasGantt();
                decorarBarrasGanttResponsables();
                configurarTooltipsGantt();
                programarCentradoFechaActual();
                try {
                    if (window.parent && typeof window.parent.refrescarGrantDashboard === 'function') {
                        window.parent.refrescarGrantDashboard();
                    }
                } catch (e) {
                }
            }, 320);
        }

        function aplicarFiltros() {
            const sucursalSel = document.getElementById('filtro-sucursal').value;
            const respText = normalizarTextoFiltro(document.getElementById('filtro-responsable').value);
            const filtroUsuario = document.getElementById('filtro-usuario');
            const usuarioSeleccionado = filtroUsuario ? String(filtroUsuario.value || '') : '';
            const opcionUsuario = filtroUsuario && filtroUsuario.options ? filtroUsuario.options[filtroUsuario.selectedIndex] : null;
            const nombreUsuarioSeleccionado = normalizarTextoFiltro(opcionUsuario ? (opcionUsuario.dataset.nombre || opcionUsuario.textContent || '') : '');
            const fechaDesde = parsearFechaReporte(document.getElementById('reporte-fecha-desde') ? document.getElementById('reporte-fecha-desde').value : '');
            const fechaHasta = parsearFechaReporte(document.getElementById('reporte-fecha-hasta') ? document.getElementById('reporte-fecha-hasta').value : '');
            const estadoReporte = document.getElementById('reporte-estado') ? String(document.getElementById('reporte-estado').value || 'Todas') : 'Todas';

            // Filtrar tareas reales (excluir la fantasma aquí; renderGantt la re-agrega)
            const tareasFiltradas = allTasks.filter(t => {
                if (t.id === '__horizon__') return false;
                const matchSucursal = (sucursalSel === 'Todas') || (t.sucursal === sucursalSel);
                const usuariosBusqueda = normalizarTextoFiltro((t.responsable || '') + ' ' + (t.usuarios_vinculados || ''));
                const matchResp = usuariosBusqueda.includes(respText);
                const vinculadosIds = (t.usuarios_vinculados_ids || []).map(function (id) {
                    return String(id);
                });
                const responsableId = t.responsable_id !== null && typeof t.responsable_id !== 'undefined'
                    ? String(t.responsable_id)
                    : '';
                const matchUsuario = !usuarioSeleccionado
                    || responsableId === usuarioSeleccionado
                    || (nombreUsuarioSeleccionado && usuariosBusqueda.includes(nombreUsuarioSeleccionado))
                    || vinculadosIds.includes(usuarioSeleccionado);
                const inicioTarea = parsearFechaReporte(t.start || t.fecha_inicio);
                const finTarea = parsearFechaReporte(t.end || t.fecha_fin) || inicioTarea;
                const matchFechas = (!fechaDesde || (finTarea && finTarea >= fechaDesde)) && (!fechaHasta || (inicioTarea && inicioTarea <= fechaHasta));
                const matchEstado = !estadoReporte || estadoReporte === 'Todas' || normalizarEstadoReporteActividades(t) === estadoReporte;
                return matchSucursal && matchResp && matchUsuario && matchFechas && matchEstado;
            });
            renderGantt(tareasFiltradas);
            programarCentradoFechaActual();
        }

        function aplicarFiltroUsuarioGantt() {
            const filtroResponsable = document.getElementById('filtro-responsable');
            if (filtroResponsable) filtroResponsable.value = '';
            aplicarFiltros();
        }

        function normalizarTextoFiltro(valor) {
            let texto = String(valor || '').trim().toLowerCase();
            if (typeof texto.normalize === 'function') {
                texto = texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }
            return texto;
        }

        
       function eliminarTarea(id, nombre) {
    abrirDialogoAnularTarea(String(id));
    return;
    if (confirm('¿Eliminar la tarea "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
        
        fetch('../php_system/Grant.php?delete=' + id)
            .then(response => {
                if (response.ok) {
                    
                    document.querySelectorAll('.task-row').forEach(function(fila) {
                        const btnEliminar = fila.querySelector('a[title="Eliminar"]');
                        if (btnEliminar && btnEliminar.getAttribute('onclick').includes('eliminarTarea(' + id + ',')) {
                            fila.remove();
                        }
                    });
                    const index = allTasks.findIndex(t => t.id == id || t.id === String(id));
                    if (index !== -1) allTasks.splice(index, 1);
                    aplicarFiltros();

                    alert('Tarea eliminada correctamente.');
                } else {
                    alert('Error al eliminar la tarea.');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error de conexión al eliminar.');
            });
    }

}

        // Edición y Formulario
        function abrirPanelNuevaTareaGantt() {
            const panelFormulario = document.getElementById('task-form-panel');
            resetForm(true);

            if (panelFormulario) {
                panelFormulario.open = true;
            }

            setTimeout(function () {
                const titulo = document.getElementById('form_titulo');
                if (titulo) titulo.focus();
                actualizarEspaciadorFechasGantt();
                sincronizarTablaConBarrasGantt();
            }, 80);
        }

        function editarTarea(tarea) {
            const panelFormulario = document.getElementById('task-form-panel');
            if (panelFormulario) panelFormulario.open = true;
            setEstadoGuardandoTareaGantt(false);

            document.getElementById('form_id').value = tarea.id;
            document.getElementById('form_title').innerText = 'Editar:';
            document.getElementById('form_titulo').value = tarea.titulo;
            document.getElementById('form_inicio').value = tarea.fecha_inicio;
            document.getElementById('form_fin').value = tarea.fecha_fin;
            document.getElementById('form_progreso').value = tarea.progreso;
            document.getElementById('form_estado').value = tarea.estado;
            document.getElementById('form_sucursal').value = tarea.sucursal || 'General';
            document.getElementById('form_responsable').value = tarea.responsable || '';
            seleccionarUsuariosVinculados(tarea.usuarios_vinculados_ids || []);
            document.getElementById('form_dependencia').value = tarea.dependencia || '';
            document.getElementById('btn_submit').innerText = 'Actualizar';
            document.getElementById('btn_submit').dataset.textoBase = 'Actualizar';
            setTimeout(function () {
                actualizarEspaciadorFechasGantt();
                sincronizarTablaConBarrasGantt();
            }, 80);
        }

    function setEstadoGuardandoTareaGantt(guardando) {
        tareaGanttGuardando = guardando === true;
        const boton = document.getElementById('btn_submit');
        if (!boton) return;

        if (tareaGanttGuardando && !boton.dataset.textoBase) {
            boton.dataset.textoBase = boton.innerText || 'Guardar';
        }

        boton.disabled = tareaGanttGuardando;
        boton.classList.toggle('is-saving', tareaGanttGuardando);
        boton.innerText = tareaGanttGuardando ? 'Guardando...' : (boton.dataset.textoBase || 'Guardar');
    }

    function configurarBloqueoFormularioTareaGantt() {
        const formulario = document.getElementById('taskForm');
        if (!formulario || formulario.dataset.bloqueoGuardado === '1') {
            return;
        }

        formulario.dataset.bloqueoGuardado = '1';
        formulario.addEventListener('submit', function (event) {
            sincronizarCredencialesFormularioGantt();
            if (tareaGanttGuardando) {
                event.preventDefault();
                return false;
            }

            setEstadoGuardandoTareaGantt(true);
            return true;
        });
    }

    function resetForm(mantenerAbierto) {
        setEstadoGuardandoTareaGantt(false);
        document.getElementById('taskForm').reset();
        document.getElementById('form_id').value        = '';
        document.getElementById('form_title').innerText = 'Nueva Tarea:';
        document.getElementById('btn_submit').innerText = 'Guardar';
        document.getElementById('btn_submit').dataset.textoBase = 'Guardar';
        seleccionarUsuariosVinculados([]);
        seleccionarResponsableUsuarioActualGantt('', true);
        setTimeout(function () {
            actualizarEspaciadorFechasGantt();
            sincronizarTablaConBarrasGantt();
        }, 80);
    }

    function seleccionarUsuariosVinculados(ids) {
        const contenedor = document.getElementById('form_usuarios_vinculados');
        if (!contenedor) return;

        const idsNormalizados = (ids || []).map(function (id) {
            return String(id);
        });

        Array.from(contenedor.querySelectorAll('input[type="checkbox"]')).forEach(function (checkbox) {
            checkbox.checked = idsNormalizados.includes(String(checkbox.value));
        });
        actualizarVistaUsuariosVinculados();
    }

    function actualizarVistaUsuariosVinculados() {
        const contenedor = document.getElementById('form_usuarios_vinculados');
        const contador = document.getElementById('usuarios_vinculados_count');
        const resumen = document.getElementById('usuarios_vinculados_resumen');
        if (!contenedor) return;

        const checks = Array.from(contenedor.querySelectorAll('input[type="checkbox"]'));
        const seleccionados = checks.filter(function (checkbox) {
            return checkbox.checked;
        });

        checks.forEach(function (checkbox) {
            const label = checkbox.closest('label');
            if (label) label.classList.toggle('is-selected', checkbox.checked);
        });

        if (contador) {
            contador.textContent = seleccionados.length === 1 ? '1 seleccionado' : seleccionados.length + ' seleccionados';
        }

        if (resumen) {
            const nombres = seleccionados.map(function (checkbox) {
                const label = checkbox.closest('label');
                const texto = label ? label.querySelector('span') : null;
                return texto ? texto.textContent.trim() : '';
            }).filter(Boolean);

            resumen.textContent = nombres.length ? nombres.join(', ') : 'Seleccionar usuarios';
        }
    }

    function cerrarUsuariosVinculados() {
        const picker = document.getElementById('usuarios_vinculados_picker');
        if (picker) picker.open = false;
    }

    function limpiarUsuariosVinculados() {
        const contenedor = document.getElementById('form_usuarios_vinculados');
        if (!contenedor) return;

        Array.from(contenedor.querySelectorAll('input[type="checkbox"]')).forEach(function (checkbox) {
            checkbox.checked = false;
        });
        actualizarVistaUsuariosVinculados();
    }

    function inicializarFiltrosReporteActividades() {
        const desde = document.getElementById('reporte-fecha-desde');
        const hasta = document.getElementById('reporte-fecha-hasta');
        const estado = document.getElementById('reporte-estado');
        const fin = new Date();
        fin.setHours(0, 0, 0, 0);
        fin.setDate(fin.getDate() + 30);
        const inicio = new Date();
        inicio.setHours(0, 0, 0, 0);
        inicio.setDate(inicio.getDate() - 30);

        if (desde && !desde.value) desde.value = formatDate(inicio);
        if (hasta && !hasta.value) hasta.value = formatDate(fin);
        if (estado && !estado.value) estado.value = 'Todas';
    }

    // Mantener filtros limpios al cargar.
    window.addEventListener('load', function () {
        sincronizarCredencialesFormularioGantt();
        configurarCreacionRapidaGantt();
        configurarBloqueoFormularioTareaGantt();
        document.getElementById('filtro-sucursal').value = 'Todas';
        document.getElementById('filtro-responsable').value = '';
        inicializarFiltrosReporteActividades();
        iniciarFiltroUsuarioActualGantt();
        const usuariosVinculados = document.getElementById('form_usuarios_vinculados');
        if (usuariosVinculados) {
            usuariosVinculados.addEventListener('change', actualizarVistaUsuariosVinculados);
            actualizarVistaUsuariosVinculados();
        }
        seleccionarResponsableUsuarioActualGantt('', false);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') cerrarUsuariosVinculados();
        });
        document.addEventListener('click', function (event) {
            const picker = document.getElementById('usuarios_vinculados_picker');
            const panel = picker ? picker.querySelector('.usuarios-vinculados-panel') : null;
            const summary = picker ? picker.querySelector('summary') : null;
            if (!picker || !picker.open) return;
            if ((panel && panel.contains(event.target)) || (summary && summary.contains(event.target))) return;
            picker.open = false;
        });
        actualizarSaludoUsuarioGantt();
        setTimeout(actualizarSaludoUsuarioGantt, 600);
        setTimeout(actualizarSaludoUsuarioGantt, 1400);
        actualizarEspaciadorFechasGantt();
        sincronizarTablaConBarrasGantt();
        posicionarGanttEnHoy();
        setTimeout(posicionarGanttEnHoy, 500);
        setTimeout(posicionarGanttEnHoy, 1000);
    });

    window.addEventListener('resize', function () {
        actualizarEspaciadorFechasGantt();
        sincronizarTablaConBarrasGantt();
        posicionarGanttEnHoy();
    });
</script>

</body>

</html>

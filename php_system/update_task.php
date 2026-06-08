<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Asuncion');

require_once("conexion.php");
include_once("verificar_navegador.php");

function responder_update_task($info, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode($info);
    exit;
}

function columna_existe_update_task(PDO $pdo, $tabla, $columna)
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla) || !preg_match('/^[a-zA-Z0-9_]+$/', $columna)) {
        return false;
    }

    $stmt = $pdo->prepare("SHOW COLUMNS FROM `" . $tabla . "` LIKE ?");
    $stmt->execute(array($columna));
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function tabla_existe_update_task(PDO $pdo, $tabla)
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) {
        return false;
    }

    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute(array($tabla));
    return $stmt->fetchColumn() !== false;
}

function registrar_historial_update_task(PDO $pdo, $tarea_id, $usuario_id, $accion, $campo, $anterior, $nuevo)
{
    if (!tabla_existe_update_task($pdo, 'tarea_historial')) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO tarea_historial
            (tarea_id, usuario_id, accion, campo, valor_anterior, valor_nuevo, motivo, origen, created_at, metadata_json)
        VALUES
            (?, ?, ?, ?, ?, ?, NULL, 'endpoint compatible', NOW(), NULL)
    ");
    $stmt->execute(array($tarea_id, $usuario_id, $accion, $campo, $anterior, $nuevo));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    responder_update_task(array('status' => 'error', 'message' => 'Datos invalidos'), 400);
}

$user = isset($data['useru']) ? trim((string)$data['useru']) : '';
$pass = isset($data['passu']) ? str_replace('=', '+', (string)$data['passu']) : '';
$navegador = isset($data['navegador']) ? trim((string)$data['navegador']) : '';

if ($user === '' || $pass === '' || $navegador === '' || verificar_navegador($user, $navegador, $pass) !== 'ok') {
    responder_update_task(array('status' => 'error', 'message' => 'Sesion no validada'), 401);
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=syscvxco_ac;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $startTs = strtotime(isset($data['start']) ? $data['start'] : '');
    $endTs = strtotime(isset($data['end']) ? $data['end'] : '');
    if ($startTs === false || $endTs === false) {
        responder_update_task(array('status' => 'error', 'message' => 'Fecha invalida'), 400);
    }

    $start = date('Y-m-d', $startTs);
    $end = date('Y-m-d', $endTs);
    if (strtotime($end) < strtotime($start)) {
        $end = $start;
    }

    $progress = isset($data['progress']) ? max(0, min(100, (int)$data['progress'])) : 0;
    $id = (int)$data['id'];

    $stmtAntes = $pdo->prepare("SELECT * FROM tareas WHERE id = ? LIMIT 1");
    $stmtAntes->execute(array($id));
    $antes = $stmtAntes->fetch(PDO::FETCH_ASSOC);
    if (!$antes) {
        responder_update_task(array('status' => 'error', 'message' => 'Tarea no encontrada'), 404);
    }

    $estado = $antes['estado'];
    if ($progress === 100) {
        $estado = 'Completada';
    } elseif ($estado === 'Pendiente' && $progress > 0) {
        $estado = 'En Progreso';
    }

    $sets = array('fecha_inicio = ?', 'fecha_fin = ?', 'progreso = ?', 'estado = ?');
    $valores = array($start, $end, $progress, $estado);
    if (columna_existe_update_task($pdo, 'tareas', 'updated_by')) {
        $sets[] = 'updated_by = ?';
        $valores[] = $user;
    }
    if (columna_existe_update_task($pdo, 'tareas', 'updated_at')) {
        $sets[] = 'updated_at = ?';
        $valores[] = date('Y-m-d H:i:s');
    }
    if ($estado === 'Completada' && columna_existe_update_task($pdo, 'tareas', 'culminada_por')) {
        $sets[] = 'culminada_por = ?';
        $valores[] = $user;
    }
    if ($estado === 'Completada' && columna_existe_update_task($pdo, 'tareas', 'culminada_en')) {
        $sets[] = 'culminada_en = ?';
        $valores[] = date('Y-m-d H:i:s');
    }

    $valores[] = $id;
    $stmt = $pdo->prepare("UPDATE tareas SET " . implode(', ', $sets) . " WHERE id = ?");
    $stmt->execute($valores);

    if ((string)$antes['fecha_inicio'] !== (string)$start) {
        registrar_historial_update_task($pdo, $id, $user, 'cambio_fecha_inicio', 'fecha_inicio', $antes['fecha_inicio'], $start);
    }
    if ((string)$antes['fecha_fin'] !== (string)$end) {
        registrar_historial_update_task($pdo, $id, $user, 'cambio_fecha_fin', 'fecha_fin', $antes['fecha_fin'], $end);
    }
    if ((string)$antes['progreso'] !== (string)$progress) {
        registrar_historial_update_task($pdo, $id, $user, 'cambio_progreso', 'progreso', $antes['progreso'], $progress);
    }
    if ((string)$antes['estado'] !== (string)$estado) {
        registrar_historial_update_task($pdo, $id, $user, $estado === 'Completada' ? 'culminar' : 'cambio_estado', 'estado', $antes['estado'], $estado);
    }

    responder_update_task(array('status' => 'success'));
} catch (PDOException $e) {
    responder_update_task(array('status' => 'error', 'message' => $e->getMessage()), 500);
}
?>

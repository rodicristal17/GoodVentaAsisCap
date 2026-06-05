<?php
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

$host     = 'localhost';
$dbname   = 'syscvxco_ac';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ CORRECCIÓN: el JS ahora envía YYYY-MM-DD limpio, pero dejamos strtotime como fallback
    $startTs = strtotime($data['start']);
    $endTs = strtotime($data['end']);
    if ($startTs === false || $endTs === false) {
        echo json_encode(array('status' => 'error', 'message' => 'Fecha inválida recibida: ' . $data['start'] . ' / ' . $data['end']));
        exit;
    }

    $start    = date('Y-m-d', $startTs);
    $end      = date('Y-m-d', $endTs);
    $progress = (int)$data['progress'];

    // Validación básica de fechas
    if ($start === '1970-01-01' || $end === '1970-01-01') {
        echo json_encode(array('status' => 'error', 'message' => 'Fecha inválida recibida: ' . $data['start'] . ' / ' . $data['end']));
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE tareas SET fecha_inicio = ?,
            fecha_fin    = ?,
            progreso     = ?,
            estado       = IF(? = 100, 'Completada',
                           IF(estado = 'Pendiente' AND ? > 0, 'En Progreso', estado))
        WHERE id = ?
    ");
    $stmt->execute([$start, $end, $progress, $progress, $progress, $data['id']]);

    echo json_encode(array('status' => 'success'));

} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
?>

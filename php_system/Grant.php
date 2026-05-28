<?php
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ===============================
// CRUD - ELIMINAR
// ===============================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM tareas WHERE id=?")->execute([$id]);
    header("Location./php_system/Grant.php");
    exit;
}

// ===============================
// CRUD - CREAR / ACTUALIZAR
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
    $dependencia = !empty($_POST['dependencia']) ? (int) $_POST['dependencia'] : null;
    $sucursal = !empty($_POST['sucursal']) ? $_POST['sucursal'] : 'General';
    $responsable = !empty($_POST['responsable']) ? $_POST['responsable'] : 'Sin asignar';

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE tareas 
            SET titulo=?, fecha_inicio=?, fecha_fin=?, progreso=?, estado=?, dependencia=?, sucursal=?, responsable=?
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['titulo'],
            $_POST['fecha_inicio'],
            $_POST['fecha_fin'],
            $_POST['progreso'],
            $_POST['estado'],
            $dependencia,
            $sucursal,
            $responsable,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO tareas (titulo, fecha_inicio, fecha_fin, progreso, estado, dependencia, sucursal, responsable)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['titulo'],
            $_POST['fecha_inicio'],
            $_POST['fecha_fin'],
            $_POST['progreso'],
            $_POST['estado'],
            $dependencia,
            $sucursal,
            $responsable
        ]);
    }
    header("Location: ../php_system/Grant.php");
    exit;
}

// ===============================
// OBTENER TAREAS Y ORDENAR
// ===============================
$stmt = $pdo->query("SELECT * FROM tareas ORDER BY fecha_inicio ASC");
$tareas_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtUsuarios = $pdo->query("
    SELECT u.cod_usuario, p.nombre_persona, u.url
    FROM usuario u
    INNER JOIN persona p ON p.cod_persona = u.cod_usuario
    WHERE u.estado = 'Activo'
    ORDER BY p.nombre_persona ASC
");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
$fotos_usuarios = [];
foreach ($usuarios as $usuario) {
    $nombre_usuario_foto = trim((string) $usuario['nombre_persona']);
    $url_usuario_foto = trim((string) $usuario['url']);
    if ($nombre_usuario_foto !== '') {
        $fotos_usuarios[mb_strtolower($nombre_usuario_foto, 'UTF-8')] = $url_usuario_foto;
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
        $estadoVisual = 'Completada';
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
    $fotoResponsable = $responsableTarea !== ''
        ? ($fotos_usuarios[mb_strtolower($responsableTarea, 'UTF-8')] ?? '')
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
        'estado' => $estadoTarea,
        'estado_visual' => $estadoVisual,
        'foto_responsable' => $fotoResponsable,
        'titulo_original' => $t['titulo_gantt'],
        'nombre_barra' => $nombreBarra
    ];
}

// ---- TAREA FANTASMA para centrar la fecha actual sin cargar todo el año ----
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

$json = json_encode($tareas_gantt);
?>

<!DOCTYPE html>
<html lang="es">

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
            width: 44%;
        }

        .task-table th:nth-child(2),
        .task-table th:nth-child(3) {
            width: 23%;
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
        .task-table a[title="Eliminar"] {
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

        .task-table a[title="Eliminar"]:hover {
            background: #fee2e2;
        }

        .task-table a[title="Editar"]:active,
        .task-table a[title="Eliminar"]:active {
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
            font-size: 11px;
            font-weight: 700;
        }

        .gantt .grant-avatar {
            pointer-events: none;
        }

        .gantt .grant-avatar-fallback {
            pointer-events: none;
        }

        .gantt .bar-wrapper .bar-label {
            dominant-baseline: middle;
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

        .bar-villamorra .bar {
            fill: #7c3aed !important;
        }

        .bar-CerroCorÃ¡ .bar,
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
            fill: #64748b !important;
        }

        .bar-estado-progreso .bar {
            fill: #2563eb !important;
        }

        .bar-estado-proxima .bar {
            fill: #f59e0b !important;
        }

        .bar-estado-vencida .bar {
            fill: #dc2626 !important;
        }

        .bar-estado-completada .bar {
            fill: #159947 !important;
        }

        .bar-estado-pendiente .bar-progress,
        .bar-estado-progreso .bar-progress,
        .bar-estado-proxima .bar-progress,
        .bar-estado-vencida .bar-progress,
        .bar-estado-completada .bar-progress {
            fill: rgba(255, 255, 255, 0.28) !important;
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
            transition: width 0.28s ease, min-width 0.28s ease, opacity 0.2s ease, border-color 0.2s ease;
        }

        .gantt-layout.task-list-collapsed .task-list-container {
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

        .task-toggle-btn {
            margin-left: auto;
            border-color: var(--grant-blue);
            color: var(--grant-blue);
        }

        .task-toggle-btn.active {
            background: #eef4ff;
            color: var(--grant-blue);
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

    </style>
</head>

<body>

    <div class="header">
        <span>FARAONE CAPITAL SOCIEDAD ANONIMA | Planificación de Expansión Operativa</span>
    </div>

<div class="container">
    
    <details class="form-card task-form-panel" id="task-form-panel">
        <summary>Nueva tarea</summary>
        <form method="POST" id="taskForm" class="form-grid">
            <input type="hidden" name="id" id="form_id" value="">
            
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
                    <option value="Completada">Completada</option>
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
                        <option value="<?= htmlspecialchars($usuario['nombre_persona'], ENT_QUOTES) ?>">
                            <?= htmlspecialchars($usuario['nombre_persona'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                            <th style="width: 70px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <tr class="gantt-date-spacer" aria-hidden="true">
                            <td colspan="4"></td>
                        </tr>
                        <?php foreach ($tareas_ordenadas as $t): ?>
                            <tr class="task-row" data-task-id="<?= $t['id'] ?>" data-sucursal="<?= $t['sucursal'] ?>"
                                data-responsable="<?= strtolower($t['responsable']) ?>">
                                <td><strong><?= $t['titulo_html'] ?></strong></td>
                                <td><?= htmlspecialchars($t['sucursal'], ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($t['responsable'], ENT_QUOTES) ?></td>
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

                    <!-- Separador visual -->
                    <div class="controls-divider"></div>

                    <strong>Vista:</strong>
                    <button class="view-btn" id="btn-today" onclick="irHoyGantt()" type="button">Hoy</button>
                    <button class="view-btn active" id="btn-Day" onclick="changeGanttView('Day')">Día</button>
                    <button class="view-btn" id="btn-Week" onclick="changeGanttView('Week')">Semana</button>
                    <button class="view-btn" id="btn-Month" onclick="changeGanttView('Month')">Mes</button>
                    <button class="view-btn task-toggle-btn active" id="btn-toggle-tasks" onclick="toggleTaskList()" type="button">Mostrar tareas</button>
                </div>

                <div class="gantt-svg-container" id="gantt-container">
                    <svg id="gantt"></svg>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
    <script>
        // allTasks incluye la tarea fantasma __horizon__ que limita el diagrama al mes actual
        const allTasks = <?= $json ?>;
        let gantt;
        let vistaActual = 'Day';
        let idsTareasVisiblesGantt = new Set();
        let ordenTareasVisiblesGantt = [];

        function renderGantt(tasksToRender) {
            document.getElementById('gantt').innerHTML = '';

            const horizonTask = allTasks.find(t => t.id === '__horizon__');
            const sinHorizon = tasksToRender.filter(t => t.id !== '__horizon__' && tareaPerteneceAlMesActual(t));
            const tareasRender = horizonTask ? [...sinHorizon, horizonTask] : sinHorizon;
            ordenTareasVisiblesGantt = sinHorizon.map(t => String(t.id));
            idsTareasVisiblesGantt = new Set(ordenTareasVisiblesGantt);
            actualizarTablaDescripcionGantt();

            if (tareasRender.length > 0) {
                gantt = new Gantt("#gantt", tareasRender, {
                    view_mode: vistaActual,
                    language: 'es',

                    on_date_change: function (task, start, end) {

                        if (task.id === '__horizon__') return;
                        const startStr = formatDate(start);
                        const endStr = formatDate(end);

                        enviarActualizacionBack(task.id, startStr, endStr, task.progress);
                    },
                    on_progress_change: function (task, progress) {

                        if (task.id === '__horizon__') return;
                        const startStr = formatDate(task._start);
                        const endStr = formatDate(task._end);
                        enviarActualizacionBack(task.id, startStr, endStr, progress);
                    }
                });
                gantt.change_view_mode(vistaActual);
                mostrarMesEnFechasGantt();
                configurarScrollGantt();
                setTimeout(sincronizarTablaConBarrasGantt, 180);
                setTimeout(sincronizarTablaConBarrasGantt, 500);
                setTimeout(decorarBarrasGanttResponsables, 180);
                setTimeout(decorarBarrasGanttResponsables, 500);
                setTimeout(configurarTooltipsGantt, 220);
                setTimeout(configurarTooltipsGantt, 540);
                programarCentradoFechaActual();
            } else {
                document.getElementById('gantt').innerHTML =
                    '<text x="10" y="30">No hay tareas que coincidan con el filtro.</text>';
            }
        }

        function programarCentradoFechaActual() {
            posicionarGanttEnHoy();
            setTimeout(posicionarGanttEnHoy, 250);
            setTimeout(posicionarGanttEnHoy, 650);
            setTimeout(posicionarGanttEnHoy, 1200);
            setTimeout(posicionarGanttEnHoy, 2000);
        }

        function irHoyGantt() {
            programarCentradoFechaActual();
        }

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
            const anchoCaracterAprox = 6.1;
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
                const avatarSize = Math.max(14, Math.min(20, alto - 8));
                const paddingX = ancho < 80 ? 4 : 7;
                const avatarX = x + paddingX;
                const avatarY = y + ((alto - avatarSize) / 2);
                const textoX = avatarX + avatarSize + 7;
                const textoY = y + (alto / 2);
                const anchoTexto = Math.max(0, (x + ancho) - textoX - 6);
                const textoBarra = truncarTextoBarra(tarea.nombre_barra || tarea.name || '', anchoTexto);

                etiqueta.textContent = textoBarra;
                etiqueta.setAttribute('x', textoX);
                etiqueta.setAttribute('y', textoY);
                etiqueta.setAttribute('text-anchor', 'start');
                etiqueta.setAttribute('dominant-baseline', 'middle');
                etiqueta.setAttribute('fill', '#fff');
                etiqueta.setAttribute('font-size', '10');
                etiqueta.setAttribute('font-weight', '800');
                etiqueta.setAttribute('dx', '0');
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
                iniciales.setAttribute('font-size', '8');
                iniciales.setAttribute('font-weight', '800');
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
            return String(valor || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
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

        function obtenerContenedorScrollGantt() {
            const contenedorExterno = document.getElementById('gantt-container');
            if (!contenedorExterno) return null;

            const contenedorInterno = contenedorExterno.querySelector('.gantt-container');
            return contenedorInterno || contenedorExterno;
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

        function posicionarGanttEnHoy(intentos = 0) {
            setTimeout(function () {
                const contenedor = obtenerContenedorScrollGantt();
                const panelDerecho = document.querySelector('.gantt-right-panel');
                const marcaHoy = document.querySelector('#gantt .today-highlight');

                if (!contenedor) return;

                if (!marcaHoy && intentos < 20) {
                    posicionarGanttEnHoy(intentos + 1);
                    return;
                }

                const posicionHoy = obtenerPosicionMarcaHoy(marcaHoy, contenedor);
                const xHoy = posicionHoy ? posicionHoy.x : calcularPosicionFechaHoy();
                const anchoHoy = posicionHoy ? posicionHoy.width : obtenerAnchoColumnaVista();
                const scrollMaximo = Math.max(0, contenedor.scrollWidth - contenedor.clientWidth);
                const scrollObjetivo = Math.min(
                    scrollMaximo,
                    Math.max(0, xHoy + (anchoHoy / 2) - (contenedor.clientWidth / 2))
                );

                if (typeof contenedor.scrollTo === 'function') {
                    contenedor.scrollTo({ left: scrollObjetivo, behavior: intentos === 0 ? 'auto' : 'smooth' });
                } else {
                    contenedor.scrollLeft = scrollObjetivo;
                }

                const contenedorExterno = document.getElementById('gantt-container');
                if (contenedorExterno && contenedorExterno !== contenedor) {
                    contenedorExterno.scrollLeft = scrollObjetivo;
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

        function configurarScrollGantt() {
            const contenedor = obtenerContenedorScrollGantt();
            if (contenedor && contenedor !== contenedorScrollGanttActual) {
                if (contenedorScrollGanttActual) {
                    contenedorScrollGanttActual.removeEventListener('scroll', sincronizarScrollListaTareas);
                }
                contenedor.addEventListener('scroll', sincronizarScrollListaTareas);
                contenedorScrollGanttActual = contenedor;
            }
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
            }, 320);
        }

        function aplicarFiltros() {
            const sucursalSel = document.getElementById('filtro-sucursal').value;
            const respText = document.getElementById('filtro-responsable').value.toLowerCase();

            // Filtrar tareas reales (excluir la fantasma aquí; renderGantt la re-agrega)
            const tareasFiltradas = allTasks.filter(t => {
                if (t.id === '__horizon__') return false;
                const matchSucursal = (sucursalSel === 'Todas') || (t.sucursal === sucursalSel);
                const matchResp = t.responsable.toLowerCase().includes(respText);
                return matchSucursal && matchResp;
            });
            renderGantt(tareasFiltradas);
        }

        
       function eliminarTarea(id, nombre) {
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
        function editarTarea(tarea) {
            const panelFormulario = document.getElementById('task-form-panel');
            if (panelFormulario) panelFormulario.open = true;

            document.getElementById('form_id').value = tarea.id;
            document.getElementById('form_title').innerText = 'Editar:';
            document.getElementById('form_titulo').value = tarea.titulo;
            document.getElementById('form_inicio').value = tarea.fecha_inicio;
            document.getElementById('form_fin').value = tarea.fecha_fin;
            document.getElementById('form_progreso').value = tarea.progreso;
            document.getElementById('form_estado').value = tarea.estado;
            document.getElementById('form_sucursal').value = tarea.sucursal || 'General';
            document.getElementById('form_responsable').value = tarea.responsable || '';
            document.getElementById('form_dependencia').value = tarea.dependencia || '';
            document.getElementById('btn_submit').innerText = 'Actualizar';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

    function resetForm() {
        document.getElementById('taskForm').reset();
        document.getElementById('form_id').value        = '';
        document.getElementById('form_title').innerText = 'Nueva Tarea:';
        document.getElementById('btn_submit').innerText = 'Guardar';
    }

    // Mantener filtros limpios al cargar.
    window.addEventListener('load', function () {
        document.getElementById('filtro-sucursal').value = 'Todas';
        document.getElementById('filtro-responsable').value = '';
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

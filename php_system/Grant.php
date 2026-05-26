<?php
// ===============================
// CONEXIÓN BD CAMBIAR CREDENCIALES CORRECTAS
// ===============================
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
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM tareas WHERE id=?")->execute([$id]);
    header("Location./php_system/Grant.php");
    exit;
}

// ===============================
// CRUD - CREAR / ACTUALIZAR
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $dependencia = !empty($_POST['dependencia']) ? (int)$_POST['dependencia'] : null;
    $sucursal = !empty($_POST['sucursal']) ? $_POST['sucursal'] : 'General';
    $responsable = !empty($_POST['responsable']) ? $_POST['responsable'] : 'Sin asignar';

    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE tareas 
            SET titulo=?, fecha_inicio=?, fecha_fin=?, progreso=?, estado=?, dependencia=?, sucursal=?, responsable=?
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['titulo'], $_POST['fecha_inicio'], $_POST['fecha_fin'], 
            $_POST['progreso'], $_POST['estado'], $dependencia, $sucursal, $responsable, $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO tareas (titulo, fecha_inicio, fecha_fin, progreso, estado, dependencia, sucursal, responsable)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['titulo'], $_POST['fecha_inicio'], $_POST['fecha_fin'], 
            $_POST['progreso'], $_POST['estado'], $dependencia, $sucursal, $responsable
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

$tareas_ordenadas = [];
$visitados = [];

function armarArbolEstructurado($padre_id, $lista, $nivel, &$resultado, &$visitados) {
    foreach ($lista as $t) {
        $es_hijo = ($padre_id === null && empty($t['dependencia'])) || ((string)$t['dependencia'] === (string)$padre_id);
        
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
    if ($t['sucursal'] == 'Villa Morra') $class = 'bar-villamorra';
    if ($t['sucursal'] == 'Cerro Corá') $class = 'bar-CerroCorá';
    if ($t['sucursal'] == 'Oviedo') $class = 'bar-Oviedo';
    if ($t['sucursal'] == 'San Lorenzo') $class = 'bar-SanLorenzo';
     if ($t['sucursal'] == 'Santa Librada') $class = 'bar-SantaLibrada';

    
    // Si está completada, forzamos verde
    if ($t['estado'] == 'Completada') $class = 'bar-completada';

    $tareas_gantt[] = [
        'id' => (string)$t['id'],
        'name' => $t['titulo_gantt'],
        'start' => $t['fecha_inicio'],
        'end' => $t['fecha_fin'],
        'progress' => (int)$t['progreso'],
        'dependencies' => $t['dependencia'] ? (string)$t['dependencia'] : '',
        'custom_class' => $class,
        'sucursal' => $t['sucursal'],
        'responsable' => $t['responsable']
    ];
}

// ---- TAREA FANTASMA para extender el horizonte hasta el 31 de diciembre del año en curso ----
// Esto garantiza que el diagrama siempre muestre todos los meses del año,
// sin importar hasta qué fecha lleguen las tareas reales.
$anio_actual = date('Y');
$tareas_gantt[] = [
    'id'           => '__horizon__',
    'name'         => '',
    'start'        => $anio_actual . '-01-01',
    'end'          => $anio_actual . '-12-31',
    'progress'     => 0,
    'dependencies' => '',
    'custom_class' => 'bar-hidden',
    'sucursal'     => '__none__',
    'responsable'  => ''
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
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
    .header { background: #002D62; color: white; padding: 15px; font-size: 20px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;}
    .container { width: 98%; margin: auto; padding-top: 15px; }
    
    .gantt-layout { display: flex; height: 60vh; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden; border: 1px solid #ddd; }
    
    .task-list-container { width: 45%; min-width: 400px; border-right: 1px solid #ddd; overflow-y: hidden; background: #fafafa; }
    .task-table { width: 100%; border-collapse: collapse; }
    .task-table th { background: #eee; height: 62px; padding: 0 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 13px; }
    .task-table td { height: 38px; padding: 0 10px; border-bottom: 1px solid #ebebeb; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .gantt-right-panel { width: 90%; display: flex; flex-direction: column; }
    
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
    .view-btn { background: white; border: 1px solid #ccc; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
    .view-btn.active { background: #002D62; color: white; border-color: #002D62; }
    .filter-input { padding: 5px; font-size: 12px; border: 1px solid #ccc; border-radius: 4px; }

    /* Separador visual entre filtros y vistas */
    .controls-divider {
        width: 1px;
        height: 24px;
        background: #ccc;
        margin: 0 4px;
    }

    .gantt-svg-container { overflow-x: auto; overflow-y: auto; flex-grow: 1; }

    .form-card { background: white; padding: 15px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; align-items: end;}
    .form-grid input, .form-grid select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; width: 100%; box-sizing: border-box;}
    .btn-save  { background: #198754; color: white; padding: 9px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;}
    .btn-clear { background: #6c757d; color: white; padding: 9px; border: none; border-radius: 4px; cursor: pointer; }
    
    /* Colores por Sucursal (Dinámicos) */
    .bar-general    .bar { fill: #6c757d !important; }
    .bar-villamorra .bar { fill: #6f42c1 !important; }
    .bar-luque      .bar { fill: #fd7e14 !important; }
    .bar-villaelisa .bar { fill: #17a2b8 !important; }
    .bar-completada .bar { fill: #198754 !important; }

    /* Tarea fantasma: completamente invisible */
    .bar-hidden .bar          { fill: transparent !important; stroke: none !important; }
    .bar-hidden .bar-label    { display: none !important; }
    .bar-hidden .bar-progress { fill: transparent !important; }

    /* Botón eliminar junto al de editar */
    .btn-del {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        padding: 0 2px;
        line-height: 1;
    }
    .btn-del:hover { opacity: 0.7; }
</style>
</head>

<body>

<div class="header">
    <span>FARAONE CAPITAL SOCIEDAD ANONIMA | Planificación de Expansión Operativa</span>
</div>

<div class="container">
    
    <div class="form-card">
        <form method="POST" id="taskForm" class="form-grid">
            <input type="hidden" name="id" id="form_id" value="">
            
            <div>
                <label style="font-size: 12px; font-weight:bold;" id="form_title">Nueva Tarea:</label>
                <input type="text" name="titulo" id="form_titulo" placeholder="Ej: Instalación red local" required>
            </div>
            <div>
                <label style="font-size: 12px;">Inicio:</label>
                <input type="date" name="fecha_inicio" id="form_inicio" required>
            </div>
            <div>
                <label style="font-size: 12px;">Fin:</label>
                <input type="date" name="fecha_fin" id="form_fin" required>
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
                <input type="text" name="responsable" id="form_responsable" placeholder="Ej: Jorge, Carlos...">
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
    </div>

    <div class="gantt-layout">
        
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
                    <?php foreach ($tareas_ordenadas as $t): ?>
                    <tr class="task-row" data-sucursal="<?= $t['sucursal'] ?>" data-responsable="<?= strtolower($t['responsable']) ?>">
                        <td><strong><?= $t['titulo_html'] ?></strong></td>
                        <td><?= htmlspecialchars($t['sucursal'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($t['responsable'], ENT_QUOTES) ?></td>
                        <td>
                            <!-- BOTÓN EDITAR -->
                            <a style="cursor:pointer;" title="Editar" onclick='editarTarea(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</a>

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
                    <option value="Todas">Todas las Sucursales</option>
                    <option value="Villa Morra">Villa Morra</option>
                    <option value="Cerro Corá">Cerro Corá</option>
                    <option value="Oviedo">Oviedo</option>
                    <option value="San Lorenzo">San Lorenzo</option>
                    <option value="Santa Librada">Santa Librada</option>
                </select>

                <input type="text" id="filtro-responsable" class="filter-input"
                       placeholder="Buscar responsable..." onkeyup="aplicarFiltros()">

                <!-- Separador visual -->
                <div class="controls-divider"></div>

                <strong>Vista:</strong>
                <button class="view-btn" id="btn-Day"   onclick="changeGanttView('Day')">Día</button>
                <button class="view-btn active" id="btn-Week"  onclick="changeGanttView('Week')">Semana</button>
                <button class="view-btn" id="btn-Month" onclick="changeGanttView('Month')">Mes</button>
            </div>
            
            <div class="gantt-svg-container" id="gantt-container">
                <svg id="gantt"></svg>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
<script>
    // allTasks incluye la tarea fantasma __horizon__ que extiende el diagrama hasta dic-31
    const allTasks = <?= $json ?>;
    let gantt;
    let vistaActual = 'Week';

function renderGantt(tasksToRender) {
    document.getElementById('gantt').innerHTML = '';

    const horizonTask = allTasks.find(t => t.id === '__horizon__');
    const sinHorizon  = tasksToRender.filter(t => t.id !== '__horizon__');
    const tareasRender = horizonTask ? [...sinHorizon, horizonTask] : sinHorizon;

    if (tareasRender.length > 0) {
        gantt = new Gantt("#gantt", tareasRender, {
            view_mode: vistaActual,
            language: 'es',

           on_date_change: function(task, start, end) {
  
    if (task.id === '__horizon__') return;
    const startStr = formatDate(start);
    const endStr   = formatDate(end);
  
    enviarActualizacionBack(task.id, startStr, endStr, task.progress);
},
on_progress_change: function(task, progress) {
    
    if (task.id === '__horizon__') return;
    const startStr = formatDate(task._start);
    const endStr   = formatDate(task._end);
    enviarActualizacionBack(task.id, startStr, endStr, progress);
}
        });
        gantt.change_view_mode(vistaActual);
    } else {
        document.getElementById('gantt').innerHTML =
            '<text x="10" y="30">No hay tareas que coincidan con el filtro.</text>';
    }
}

// ✅ NUEVA función auxiliar: Date → "YYYY-MM-DD"
function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    const yyyy = d.getFullYear();
    const mm   = String(d.getMonth() + 1).padStart(2, '0');
    const dd   = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

    // Inicializar
    renderGantt(allTasks);

    // Función unificada para enviar actualizaciones (fechas o progreso)
   function enviarActualizacionBack(id, start, end, progress) {
    
   
    
    fetch("update_task.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({ id, start, end, progress })
    })
    .then(r => r.json())
    .then(data => {
       
        if (data.status !== 'success') alert("Error: " + data.message);
    })
    .catch(err => console.error('❌ Error fetch:', err));
}

    // Sincronizar Scroll
    document.getElementById('gantt-container').addEventListener('scroll', function() {
        document.getElementById('list-container').scrollTop = this.scrollTop;
    });

    // Control de Vistas
    function changeGanttView(mode) {
        vistaActual = mode;
        if (!gantt) return;
        gantt.change_view_mode(mode);
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('btn-' + mode).classList.add('active');
    }

    // Filtros Visuales Intercomunicados (Tabla + Gráfico)
    function aplicarFiltros() {
        const sucursalSel = document.getElementById('filtro-sucursal').value;
        const respText    = document.getElementById('filtro-responsable').value.toLowerCase();
        
        // Filtrar tareas reales (excluir la fantasma aquí; renderGantt la re-agrega)
        const tareasFiltradas = allTasks.filter(t => {
            if (t.id === '__horizon__') return false;
            const matchSucursal = (sucursalSel === 'Todas') || (t.sucursal === sucursalSel);
            const matchResp     = t.responsable.toLowerCase().includes(respText);
            return matchSucursal && matchResp;
        });
        renderGantt(tareasFiltradas);

        // Filtrar visualmente la Tabla HTML izquierda
        document.querySelectorAll('.task-row').forEach(fila => {
            const matchS = (sucursalSel === 'Todas') || (fila.dataset.sucursal === sucursalSel);
            const matchR = fila.dataset.responsable.includes(respText);
            fila.style.display = (matchS && matchR) ? '' : 'none';
        });
    }

    // =====================================================
    // NUEVO: Eliminar tarea con confirmación
    // =====================================================
    function eliminarTarea(id, nombre) {
        if (confirm('¿Eliminar la tarea "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
            window.location.href = '.../php_system/Grant.php' + id;
        }
    }

    // Edición y Formulario
    function editarTarea(tarea) {
        document.getElementById('form_id').value        = tarea.id;
        document.getElementById('form_title').innerText = '✏️ Editar:';
        document.getElementById('form_titulo').value    = tarea.titulo;
        document.getElementById('form_inicio').value    = tarea.fecha_inicio;
        document.getElementById('form_fin').value       = tarea.fecha_fin;
        document.getElementById('form_progreso').value  = tarea.progreso;
        document.getElementById('form_estado').value    = tarea.estado;
        document.getElementById('form_sucursal').value  = tarea.sucursal || 'General';
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
</script>

</body>
</html>
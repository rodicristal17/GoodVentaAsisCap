<?php
require_once('conexion.php');
require_once('verificar_navegador.php');
require_once('buscar_nivel.php');

function errores_sistema_json($datos) { header('Content-Type: application/json; charset=utf-8'); echo json_encode($datos, JSON_UNESCAPED_UNICODE); exit; }
function errores_sistema_post($clave) { return isset($_POST[$clave]) ? trim((string)$_POST[$clave]) : ''; }

$user = errores_sistema_post('useru');
$pass = str_replace('=', '+', errores_sistema_post('passu'));
$navegador = errores_sistema_post('navegador');
if ($user === '' || verificar_navegador($user, $navegador, $pass) !== 'ok') errores_sistema_json(array('1' => 'UI'));
if (!controldeaccesoacasas($user, 'VERERRORESSISTEMA', 'accion')) errores_sistema_json(array('1' => 'NI', '2' => 'No tiene permiso para consultar errores del sistema.'));

$nivel = strtolower(errores_sistema_post('nivel'));
$buscar = strtolower(errores_sistema_post('buscar'));
$desde = errores_sistema_post('desde');
$archivos = glob('/var/log/telar/telar-errors-*.log');
rsort($archivos, SORT_STRING);
$registros = array();

foreach (array_slice($archivos, 0, 31) as $archivo) {
    $lineas = @file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lineas) continue;
    for ($i = count($lineas) - 1; $i >= 0 && count($registros) < 500; $i--) {
        $fila = json_decode($lineas[$i], true);
        if (!is_array($fila)) continue;
        if ($nivel !== '' && strtolower((string)$fila['nivel']) !== $nivel) continue;
        if ($desde !== '' && substr((string)$fila['fecha'], 0, 10) < $desde) continue;
        $texto = strtolower(implode(' ', array($fila['id'], $fila['mensaje'], $fila['archivo'], $fila['ruta'])));
        if ($buscar !== '' && strpos($texto, $buscar) === false) continue;
        $registros[] = $fila;
    }
    if (count($registros) >= 500) break;
}

errores_sistema_json(array('1' => 'exito', '2' => $registros, '3' => count($registros)));

<?php

$raiz = dirname(__DIR__);
$pruebas = array();

function carteraPrueba($nombre, $condicion, &$pruebas)
{
    $pruebas[] = array('nombre' => $nombre, 'ok' => (bool)$condicion);
}

function carteraContenido($raiz, $archivo)
{
    $ruta = $raiz.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $archivo);
    return is_file($ruta) ? file_get_contents($ruta) : '';
}

$migracion = carteraContenido($raiz, 'actualizacion_20082026_mi_cartera.sql');
$rollback = carteraContenido($raiz, 'actualizacion_20082026_mi_cartera_rollback.sql');
$helper = carteraContenido($raiz, 'php_system/mi_cartera_helper.php');
$endpoint = carteraContenido($raiz, 'php_system/abmMiCartera.php');
$javascript = carteraContenido($raiz, 'js_system/mi_cartera.js');
$css = carteraContenido($raiz, 'css_system/mi_cartera.css');
$inicio = carteraContenido($raiz, 'system/inicio.html');
$dashboardJs = carteraContenido($raiz, 'js_system/dashboard_shortcuts.js');
$dashboardPhp = carteraContenido($raiz, 'php_system/dashboard_shortcuts.php');

carteraPrueba('migracion aditiva presente', $migracion !== '', $pruebas);
carteraPrueba('seis tablas trazables', substr_count($migracion, 'CREATE TABLE IF NOT EXISTS cartera_') === 6, $pruebas);
carteraPrueba('una asignacion actual por paciente', strpos($migracion, 'uq_cartera_asignacion_cliente') !== false, $pruebas);
carteraPrueba('registra llamada vinculada', strpos($migracion, 'id_solicitud_llamadaFK') !== false, $pruebas);
carteraPrueba('compromiso conserva base pagada', strpos($migracion, 'monto_pagado_base') !== false, $pruebas);
carteraPrueba('rollback conserva auditoria', strpos(strtoupper($rollback), 'DROP TABLE') === false, $pruebas);
carteraPrueba('saldo proviene de credito y pago', strpos($helper, 'FROM credito cr') !== false && strpos($helper, 'FROM pago pg') !== false, $pruebas);
carteraPrueba('pagos anulados no impactan', strpos($helper, "NOT IN ('si','anulado','activo')") !== false, $pruebas);
carteraPrueba('ventana preventiva de siete dias', strpos($helper, 'INTERVAL 7 DAY') !== false, $pruebas);
carteraPrueba('escalamiento desde treinta dias', strpos($helper, 'mora_30_dias') !== false, $pruebas);
carteraPrueba('dos intentos sin respuesta', strpos($helper, 'dos_intentos_sin_respuesta') !== false, $pruebas);
carteraPrueba('pago confirmado no es resultado manual', strpos($helper, "'pago_confirmado' => 'Pago confirmado'") !== false && strpos($helper, "'pago_confirmado',\n        'contactado'") === false, $pruebas);
carteraPrueba('endpoint exige sesion', strpos($endpoint, 'verificar_navegador') !== false, $pruebas);
carteraPrueba('configuracion protegida para Carlos', strpos($helper, 'intval($codUsuario) === 5994') !== false, $pruebas);
carteraPrueba('icono independiente en dashboard', strpos($inicio, "id='divMenuMiCartera'") !== false, $pruebas);
carteraPrueba('ventana independiente en dashboard', strpos($inicio, "id='divMiCartera'") !== false, $pruebas);
carteraPrueba('catalogo de accesos registra mi cartera', strpos($dashboardJs, 'mi_cartera:') !== false, $pruebas);
carteraPrueba('servidor filtra equipo configurado', strpos($dashboardPhp, 'dashboard_user_can_access_mi_cartera') !== false, $pruebas);
carteraPrueba('interfaz lista y flujo guiado', strpos($javascript, 'Un vistazo al proceso') !== false && strpos($javascript, 'Guardar resultado y próxima acción') !== false, $pruebas);
carteraPrueba('interfaz adaptable a tablet', strpos($css, '@media (max-width: 820px)') !== false, $pruebas);
carteraPrueba('informe morosos queda fuera del flujo', stripos($helper.$endpoint.$javascript.$migracion, 'clientes_morosos') === false && stripos($helper.$endpoint.$javascript.$migracion, 'VERINFORMEMOROSO') === false, $pruebas);
carteraPrueba('sin sintaxis exclusiva de PHP 8', strpos($helper, 'match (') === false && strpos($helper, 'fn(') === false && strpos($helper, 'str_contains(') === false, $pruebas);

$fallos = 0;
foreach ($pruebas as $prueba) {
    echo ($prueba['ok'] ? '[OK] ' : '[ERROR] ').$prueba['nombre'].PHP_EOL;
    if (!$prueba['ok']) {
        $fallos++;
    }
}
echo PHP_EOL.(count($pruebas) - $fallos).'/'.count($pruebas).' verificaciones correctas.'.PHP_EOL;
exit($fallos > 0 ? 1 : 0);

?>

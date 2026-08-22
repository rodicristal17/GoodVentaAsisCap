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
$migracionFlexible = carteraContenido($raiz, 'actualizacion_21082026_mi_cartera_configuracion_flexible.sql');
$rollbackFlexible = carteraContenido($raiz, 'actualizacion_21082026_mi_cartera_configuracion_flexible_rollback.sql');
$migracionHeredada = carteraContenido($raiz, 'actualizacion_22082026_mi_cartera_fuente_heredada.sql');
$rollbackHeredada = carteraContenido($raiz, 'actualizacion_22082026_mi_cartera_fuente_heredada_rollback.sql');
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
carteraPrueba('migracion flexible inicia en noventa dias', strpos($migracionFlexible, 'dias_escalamiento=90') !== false && strpos($migracion, 'DEFAULT 90') !== false, $pruebas);
carteraPrueba('migracion flexible no pisa una configuracion posterior', strpos($migracionFlexible, 'migracion_regla_90') !== false && strpos($migracionFlexible, 'rollback_regla_90') !== false, $pruebas);
carteraPrueba('rollback flexible conserva historicos', $rollbackFlexible !== '' && strpos(strtoupper($rollbackFlexible), 'DROP TABLE') === false, $pruebas);
carteraPrueba('fuente heredada se configura por local', strpos($migracionHeredada, 'CREATE TABLE IF NOT EXISTS cartera_fuente_heredada') !== false && strpos($migracionHeredada, "l.cod_local=1") !== false, $pruebas);
carteraPrueba('asignaciones exclusivamente heredadas se archivan con auditoria', strpos($migracionHeredada, 'fuente_heredada_archivada') !== false && strpos($migracionHeredada, 'tmp_cartera_archivar_heredada') !== false, $pruebas);
carteraPrueba('rollback heredado conserva gestiones y promesas', $rollbackHeredada !== '' && strpos($rollbackHeredada, 'DROP TABLE IF EXISTS cartera_fuente_heredada') !== false && stripos($rollbackHeredada, 'DROP TABLE cartera_gestion') === false, $pruebas);
carteraPrueba('saldo proviene de credito y pago', strpos($helper, 'FROM credito cr') !== false && strpos($helper, 'FROM pago pg') !== false, $pruebas);
carteraPrueba('saldo operativo y heredado permanecen separados', strpos($helper, 'saldo_operativo') !== false && strpos($helper, 'saldo_heredado') !== false && strpos($helper, 'miCarteraCondicionClienteOperativoSql') !== false, $pruebas);
carteraPrueba('pagos anulados no impactan', strpos($helper, "NOT IN ('si','anulado','activo')") !== false, $pruebas);
carteraPrueba('ventana preventiva de siete dias', strpos($helper, 'INTERVAL 7 DAY') !== false, $pruebas);
carteraPrueba('escalamiento configurable entre treinta y trescientos sesenta y cinco dias', strpos($helper, '$diasEscalamiento < 30') !== false && strpos($helper, '$diasEscalamiento > 365') !== false, $pruebas);
carteraPrueba('dos intentos sin respuesta', strpos($helper, 'dos_intentos_sin_respuesta') !== false, $pruebas);
carteraPrueba('equipo admite cantidad flexible', strpos($helper, 'count($gestores) < 1') !== false && strpos($helper, 'count($cobradores) < 1') !== false, $pruebas);
carteraPrueba('cada clinica conserva un gestor unico', strpos($helper, 'Cada clinica debe tener un unico gestor') !== false, $pruebas);
carteraPrueba('configuracion requiere vista previa confirmada', strpos($endpoint, "case 'previsualizar_configuracion'") !== false && strpos($helper, 'confirmacion_requerida') !== false, $pruebas);
carteraPrueba('confirmacion rechaza una vista previa desactualizada', strpos($helper, 'firma_impacto') !== false && strpos($helper, 'hash_equals') !== false && strpos($helper, 'FOR UPDATE') !== false, $pruebas);
carteraPrueba('reasignacion de configuracion queda auditada', strpos($helper, 'reasignacion_configuracion') !== false, $pruebas);
carteraPrueba('jefe recibe casos especiales', strpos($helper, 'jefe_cobranza') !== false && strpos($helper, 'promesa_incumplida') !== false && strpos($helper, 'toma_jefe') !== false, $pruebas);
carteraPrueba('jefe puede tomar caso desde endpoint', strpos($endpoint, "case 'tomar_caso_jefe'") !== false && strpos($javascript, 'take-chief-case') !== false, $pruebas);
carteraPrueba('pago confirmado no es resultado manual', strpos($helper, "'pago_confirmado' => 'Pago confirmado'") !== false && strpos($helper, "'pago_confirmado',\n        'contactado'") === false, $pruebas);
carteraPrueba('endpoint exige sesion', strpos($endpoint, 'verificar_navegador') !== false, $pruebas);
carteraPrueba('configuracion protegida para Carlos', strpos($helper, 'intval($codUsuario) === 5994') !== false, $pruebas);
carteraPrueba('icono independiente en dashboard', strpos($inicio, "id='divMenuMiCartera'") !== false, $pruebas);
carteraPrueba('ventana independiente en dashboard', strpos($inicio, "id='divMiCartera'") !== false, $pruebas);
carteraPrueba('catalogo de accesos registra mi cartera', strpos($dashboardJs, 'mi_cartera:') !== false, $pruebas);
carteraPrueba('servidor filtra equipo configurado', strpos($dashboardPhp, 'dashboard_user_can_access_mi_cartera') !== false, $pruebas);
carteraPrueba('interfaz lista y flujo guiado', strpos($javascript, 'Un vistazo al proceso') !== false && strpos($javascript, 'Guardar resultado y próxima acción') !== false, $pruebas);
carteraPrueba('interfaz permite sumar y quitar integrantes', strpos($javascript, 'add-config-manager') !== false && strpos($javascript, 'remove-config-manager') !== false && strpos($javascript, 'add-config-collector') !== false && strpos($javascript, 'remove-config-collector') !== false, $pruebas);
carteraPrueba('interfaz muestra impacto antes de redistribuir', strpos($javascript, 'Reasignaciones') !== false && strpos($javascript, 'Confirmar cambios') !== false, $pruebas);
carteraPrueba('caso mixto muestra importes y advertencia', strpos($javascript, 'Paciente con cuentas de dos administraciones') !== false && strpos($javascript, 'finance.saldo_heredado') !== false && strpos($css, '.mi-cartera-legacy-warning') !== false, $pruebas);
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

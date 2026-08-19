<?php

function activosFijosFallar($mensaje)
{
    fwrite(STDERR, 'FALLO: '.$mensaje.PHP_EOL);
    exit(1);
}

function activosFijosAfirmar($condicion, $mensaje)
{
    if (!$condicion) {
        activosFijosFallar($mensaje);
    }
}

$raiz = dirname(__DIR__);
$php = file_get_contents($raiz.'/php_system/abmInventarioLocal.php');
$js = file_get_contents($raiz.'/js_system/abmInventarioLocal.js');
$jsAbm = file_get_contents($raiz.'/js_system/abm.js');
$html = file_get_contents($raiz.'/system/inicio.html');
$css = file_get_contents($raiz.'/css_system/inicio.css');
$migracion = file_get_contents($raiz.'/actualizacion_18082026_activos_fijos_control.sql');
$rollback = file_get_contents($raiz.'/actualizacion_18082026_activos_fijos_control_rollback.sql');

foreach (array($php, $js, $jsAbm, $html, $css, $migracion, $rollback) as $contenido) {
    activosFijosAfirmar($contenido !== false, 'Falta un archivo de la reestructuracion.');
}

activosFijosAfirmar(strpos($html, 'id="resumenInventarioLocal" class="inventario-local-resumen" hidden') !== false, 'El resumen no inicia colapsado.');
activosFijosAfirmar(strpos($html, '<th class="col-personas">Personas</th>') !== false, 'Falta la columna Personas.');
activosFijosAfirmar(strpos($html, 'Listado valorizado') !== false && strpos($html, 'Planilla de conteo físico') !== false, 'Faltan los dos reportes.');
activosFijosAfirmar(strpos($html, "ordenimpresion('insumosLocal')") === false, 'La impresion antigua continua conectada al modulo.');

activosFijosAfirmar(strpos($js, 'Cargó el activo') !== false, 'El listado no distingue a quien cargo el activo.');
activosFijosAfirmar(strpos($js, 'Responsable actual') !== false, 'El listado no conserva al responsable actual.');
activosFijosAfirmar(strpos($js, "function imprimirInventarioLocal(tipo)") !== false, 'Falta la impresion independiente.');
activosFijosAfirmar(strpos($js, "tipo === \"valorizado\"") !== false, 'La impresion no distingue el listado valorizado.');
activosFijosAfirmar(strpos($js, 'inventarioLocalAbrirAuditoria') !== false && strpos($js, 'inventarioLocalImprimirCompromisoSeleccionado') !== false, 'La reestructuracion perdio acciones existentes del activo.');
activosFijosAfirmar(strpos($jsAbm, '<b>CLINIDENT SALUD</b>') !== false, 'La carta de compromiso conserva una identidad ajena a Clinident.');
activosFijosAfirmar(strpos($jsAbm, "getElementById('inptLocalInventarioInsumo')") !== false, 'La carta de compromiso no utiliza el local del activo.');

activosFijosAfirmar(strpos($php, "case 'registrarVerificacion'") !== false, 'Falta el endpoint de verificacion fisica.');
activosFijosAfirmar(strpos($php, "case 'listarResponsables'") !== false, 'Falta el endpoint de responsables.');
activosFijosAfirmar(strpos($php, 'cod_usuario_responsableFK=?,cod_usuarioFK_edit=?') !== false, 'La edicion no persiste al responsable actual.');
activosFijosAfirmar(strpos($php, "'sssiisisiiisssiiiiissss'") !== false, 'La firma del alta completa no coincide con sus 23 parametros.');
activosFijosAfirmar(strpos($php, "'sssiisiiiissssiiiiisssssi'") !== false, 'La firma de edicion completa no coincide con sus 25 parametros.');
activosFijosAfirmar(strpos($php, "'isiisiisssi'") !== false, 'La firma de verificacion no coincide con sus 11 parametros.');

foreach (array('inventario_local_sector', 'inventario_local_verificacion', 'inventario_local_depreciacion_historial') as $tabla) {
    activosFijosAfirmar(strpos($migracion, $tabla) !== false, 'La migracion no contempla '.$tabla.'.');
}
activosFijosAfirmar(strpos($migracion, "DEFAULT ''pendiente''") !== false, 'La migracion no conserva los costos historicos como pendientes.');
activosFijosAfirmar(strpos($migracion, 'information_schema.COLUMNS') !== false, 'La migracion no comprueba columnas existentes.');
activosFijosAfirmar(strpos($rollback, "'NO_DESTRUCTIVO'") !== false, 'La reversion no protege la trazabilidad acumulada.');
activosFijosAfirmar(strpos($rollback, 'DROP TABLE') === false && strpos($rollback, 'DROP COLUMN') === false, 'La reversion elimina datos trazables.');

activosFijosAfirmar(strpos($css, '#divAbmInventarioLocal1 .inventario-local-tabla') !== false, 'El CSS del listado no esta limitado al modulo.');

echo 'OK: estructura, trazabilidad, reportes y migracion de Activos fijos verificados.'.PHP_EOL;

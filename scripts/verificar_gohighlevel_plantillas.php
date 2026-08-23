<?php

/** Pruebas puras del filtro de plantillas. Compatible con PHP 7.2. */

$helper = trim((string)getenv('TELAR_GHL_HELPER_PRUEBA'));
if ($helper === '') {
    $helper = dirname(__DIR__).'/php_system/gohighlevel_helper.php';
}
require_once $helper;

$errores = array();

function ghlPlantillaComprobar($condicion, $mensaje)
{
    global $errores;
    if (!$condicion) {
        $errores[] = $mensaje;
    }
}

$normal = goHighLevelNormalizarPlantilla(array(
    'id' => 'plantilla-demo-normal',
    'name' => 'confirmacion_demo',
    'type' => 'whatsapp',
    'status' => 'Active',
    'language' => 'Spanish',
    'category' => 'Utility',
    'template' => array('body' => 'Mensaje ficticio de confirmacion sin datos personales.')
));

$variable = goHighLevelNormalizarPlantilla(array(
    'id' => 'plantilla-demo-variable',
    'name' => 'seguimiento_variable_demo',
    'type' => 'whatsapp',
    'status' => 'Approved',
    'language' => 'es_PY',
    'category' => 'Utility',
    'template' => array('body' => 'Hola {{1}}, confirme su cita ficticia.')
));

$sensible = goHighLevelNormalizarPlantilla(array(
    'id' => 'plantilla-demo-sensible',
    'name' => 'aviso_judicial_demo',
    'type' => 'whatsapp',
    'status' => 'Activo',
    'language' => 'Spanish',
    'category' => 'Utility',
    'template' => array('body' => 'Comuniquese con administracion para revisar el caso ficticio.')
));

ghlPlantillaComprobar(!empty($normal['elegible']), 'La plantilla normal deberia ser elegible.');
ghlPlantillaComprobar(empty($normal['sensible_detectada']), 'La plantilla normal no deberia ser sensible.');
ghlPlantillaComprobar(!empty($variable['tiene_variables']), 'Debe detectar variables manuales.');
ghlPlantillaComprobar(empty($variable['elegible']), 'Una plantilla con variables debe quedar bloqueada.');
ghlPlantillaComprobar(!empty($sensible['elegible']), 'El aviso sensible aprobado debe permanecer incluido.');
ghlPlantillaComprobar(!empty($sensible['sensible_detectada']), 'Debe advertir un aviso judicial.');

if (count($errores) > 0) {
    fwrite(STDERR, "GoHighLevel plantillas: ".count($errores)." error(es).\n");
    foreach ($errores as $error) {
        fwrite(STDERR, '- '.$error."\n");
    }
    exit(1);
}

echo "GoHighLevel plantillas: filtros y advertencias correctos.\n";
exit(0);

?>

<?php

$archivos = array(
    '/var/www/html/js_system/jsCalendar.js' => array(
        "((cliente.nombre_persona || '') + ' ' + (cliente.apellido_persona || '')).trim()" => "((cliente.apellido_persona || '') + ' ' + (cliente.nombre_persona || '')).trim()",
        "((resp['2'].nombre_persona || '') + ' ' + (resp['2'].apellido_persona || '')).trim()" => "((resp['2'].apellido_persona || '') + ' ' + (resp['2'].nombre_persona || '')).trim()"
    ),
    '/var/www/html/js_system/abmCliente.js' => array(
        '((datos["2"].nombre_persona || "") + " " + (datos["2"].apellido_persona || "")).trim()' => '((datos["2"].apellido_persona || "") + " " + (datos["2"].nombre_persona || "")).trim()',
        '(String(nombre_persona || "").trim() + " " + String(apellido_persona || "").trim()).trim()' => '(String(apellido_persona || "").trim() + " " + String(nombre_persona || "").trim()).trim()'
    )
);

foreach ($archivos as $ruta => $cambios) {
    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        exit(1);
    }
    foreach ($cambios as $anterior => $nuevo) {
        if (substr_count($contenido, $anterior) !== 1) {
            fwrite(STDERR, "Coincidencia inesperada en ".$ruta."\n");
            exit(2);
        }
        $contenido = str_replace($anterior, $nuevo, $contenido);
    }
    if (!copy($ruta, $ruta.'.bak-20260828-apellido-nombre')) {
        exit(3);
    }
    if (file_put_contents($ruta, $contenido) === false) {
        exit(4);
    }
    echo "OK ".$ruta."\n";
}

$inicio = '/var/www/html/system/inicio.html';
$contenido = file_get_contents($inicio);
$cambiosCache = array(
    'jsCalendar.js?x=agenda-alta-paciente-bloqueo-20260826-2' => 'jsCalendar.js?x=apellido-nombre-paciente-20260828-1',
    'abmCliente.js?x=clientes-sin-apellido-20260828-1' => 'abmCliente.js?x=apellido-nombre-paciente-20260828-1'
);
foreach ($cambiosCache as $anterior => $nuevo) {
    if (substr_count($contenido, $anterior) !== 1) {
        fwrite(STDERR, "Version de cache inesperada.\n");
        exit(5);
    }
    $contenido = str_replace($anterior, $nuevo, $contenido);
}
if (!copy($inicio, $inicio.'.bak-20260828-apellido-nombre') || file_put_contents($inicio, $contenido) === false) {
    exit(6);
}
echo "OK ".$inicio."\n";

?>

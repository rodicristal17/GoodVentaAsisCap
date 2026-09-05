<?php

$js = '/var/www/html/js_system/jsCalendar.js';
$css = '/var/www/html/css_system/cssCalendar.css';
$inicio = '/var/www/html/system/inicio.html';

function reemplazarUnicoAgenda($contenido, $anterior, $nuevo, $etiqueta)
{
    $cantidad = substr_count($contenido, $anterior);
    if ($cantidad !== 1) {
        fwrite(STDERR, $etiqueta.': se esperaba 1 coincidencia y se encontraron '.$cantidad."\n");
        exit(2);
    }
    return str_replace($anterior, $nuevo, $contenido);
}

$contenidoJs = file_get_contents($js);
if ($contenidoJs === false || strpos($contenidoJs, 'agenda-evento--compacto') !== false) {
    fwrite(STDERR, "JS no disponible o parche ya aplicado.\n");
    exit(1);
}
$contenidoJs = reemplazarUnicoAgenda(
    $contenidoJs,
    "    return ''\r\n    + \"<div class='agenda-evento estado-\" + e.estado + \"' \"",
    "    var claseDensidad = altura < 58 ? \" agenda-evento--compacto\" : (altura >= 92 ? \" agenda-evento--amplio\" : \"\");\r\n\r\n    return ''\r\n    + \"<div class='agenda-evento estado-\" + e.estado + claseDensidad + \"' \"",
    'densidad'
);
$contenidoJs = reemplazarUnicoAgenda(
    $contenidoJs,
    "    + \"<span class='paciente'>\" + advertencia_datos_incompletos + escaparHtmlAgenda(e.paciente || '') + \"</span>\"",
    "    + \"<span class='paciente'><i class='fa-solid fa-user agenda-evento-icono' aria-hidden='true'></i><span>\" + advertencia_datos_incompletos + escaparHtmlAgenda(e.paciente || '') + \"</span></span>\"",
    'paciente'
);
$contenidoJs = reemplazarUnicoAgenda(
    $contenidoJs,
    "    + \"<span class='hora agenda-evento-hora'>\" + escaparHtmlAgenda(e.inicio || '') + \" - \" + escaparHtmlAgenda(e.fin || '') + \"</span>\"",
    "    + \"<span class='hora agenda-evento-hora'><i class='fa-regular fa-clock' aria-hidden='true'></i>\" + escaparHtmlAgenda(e.inicio || '') + \" - \" + escaparHtmlAgenda(e.fin || '') + \"</span>\"",
    'horario'
);
$contenidoJs = reemplazarUnicoAgenda(
    $contenidoJs,
    "    + \"<span class='nombre_doctor'>\" + escaparHtmlAgenda(e.nombre_doctor || '') + \"</span>\"",
    "    + \"<span class='nombre_doctor'><i class='fa-solid fa-user-doctor' aria-hidden='true'></i><span>\" + escaparHtmlAgenda(e.nombre_doctor || '') + \"</span></span>\"",
    'doctor'
);

$estilos = <<<'CSS'

/* agenda-telar-profesional-20260828-01 */
.agenda-evento{
    --agenda-evento-acento:rgba(255,255,255,.72);
    isolation:isolate;
    border:1px solid rgba(255,255,255,.38)!important;
    border-left:4px solid var(--agenda-evento-acento)!important;
    border-radius:12px;
    padding:7px 8px 7px 9px;
    background-image:linear-gradient(145deg,rgba(255,255,255,.08),rgba(15,23,42,.08));
    box-shadow:0 5px 14px rgba(15,23,42,.16),inset 0 1px 0 rgba(255,255,255,.16);
    transition:transform .16s ease,box-shadow .16s ease,filter .16s ease;
}
.agenda-evento:hover{z-index:18!important;transform:translateY(-1px);filter:saturate(1.04);box-shadow:0 9px 22px rgba(15,23,42,.22),inset 0 1px 0 rgba(255,255,255,.2)}
.agenda-evento-head{grid-template-columns:minmax(0,1fr) auto;align-items:start;gap:8px}
.agenda-evento .paciente{display:flex;align-items:flex-start;gap:5px;min-width:0;font-size:12.5px;font-weight:850;line-height:1.16;letter-spacing:.01em;white-space:normal;text-transform:uppercase;text-shadow:0 1px 2px rgba(15,23,42,.2)}
.agenda-evento .paciente>span{display:-webkit-box;min-width:0;overflow:hidden;-webkit-box-orient:vertical;-webkit-line-clamp:2}
.agenda-evento-icono{flex:0 0 auto;margin-top:1px;font-size:9px;opacity:.78}
.agenda-evento .agenda-evento-hora{display:inline-flex;align-items:center;gap:3px;min-height:17px;padding:2px 6px;border:1px solid rgba(255,255,255,.7);background:rgba(248,250,252,.94);color:#173451;font-size:8.5px;letter-spacing:.01em;box-shadow:0 2px 7px rgba(15,23,42,.13)}
.agenda-evento .nombre_doctor{display:flex;align-items:center;gap:4px;min-width:0;margin-top:4px;color:rgba(255,255,255,.9);font-size:9.5px;font-weight:650;opacity:1}
.agenda-evento .nombre_doctor i{flex:0 0 auto;font-size:9px;opacity:.76}
.agenda-evento .nombre_doctor span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.agenda-evento .agenda-alerta-badge{margin-top:4px;border:1px solid rgba(255,255,255,.48);box-shadow:0 2px 6px rgba(15,23,42,.1)}
.agenda-evento--compacto{padding:5px 7px 5px 8px;border-radius:10px}
.agenda-evento--compacto .paciente>span{-webkit-line-clamp:1}
.agenda-evento--compacto .nombre_doctor,.agenda-evento--compacto .agenda-evento-tratamientos{display:none}
.agenda-evento--amplio .paciente{font-size:13px}
.estado-AGENDADO{--agenda-evento-acento:#c4b5fd;background-color:#6651b8;background-image:linear-gradient(145deg,#745cc7,#51439a)}
.estado-CONFIRMADO{--agenda-evento-acento:#67e8f9;background-color:#087f92;background-image:linear-gradient(145deg,#0e93a7,#09697d)}
.estado-ATENDIDO{--agenda-evento-acento:#86efac;background-color:#18775b;background-image:linear-gradient(145deg,#20866a,#125b4b)}
.estado-CANCELADO{--agenda-evento-acento:#fca5a5;background-color:#a03e49;background-image:linear-gradient(145deg,#ad4652,#79333d)}
.estado-AUSENTE{--agenda-evento-acento:#cbd5e1;background-color:#64748b;background-image:linear-gradient(145deg,#718096,#4b596d)}
.agenda-evento-tratamientos{gap:4px;margin-top:5px;padding-top:4px;border-top:1px solid rgba(255,255,255,.18)}
.agenda-evento-tratamiento{font-size:9.5px}
.agenda-evento-tratamiento-progreso{background:rgba(15,23,42,.22)}
.agenda-evento-tratamiento b{border:1px solid rgba(255,255,255,.7);background:rgba(255,255,255,.94)}
@media only screen and (max-width:768px){.agenda-evento{border-radius:10px;padding:6px 7px 6px 8px}.agenda-evento .paciente{font-size:11.5px}.agenda-evento .agenda-evento-hora{font-size:8px;padding-inline:5px}.agenda-evento .nombre_doctor{font-size:9px}}
@media (prefers-reduced-motion:reduce){.agenda-evento{transition:none}}
CSS;

$contenidoCss = file_get_contents($css);
if ($contenidoCss === false || strpos($contenidoCss, 'agenda-telar-profesional-20260828-01') !== false) {
    fwrite(STDERR, "CSS no disponible o parche ya aplicado.\n");
    exit(3);
}
$contenidoCss .= $estilos."\r\n";

$contenidoInicio = file_get_contents($inicio);
if ($contenidoInicio === false) {
    exit(4);
}
$contenidoInicio = preg_replace(
    '#cssCalendar\.css\?x=[^\"\']+#',
    'cssCalendar.css?x=agenda-telar-profesional-20260828-01',
    $contenidoInicio,
    1,
    $cssCache
);
$contenidoInicio = preg_replace(
    '#jsCalendar\.js\?x=[^\"\']+#',
    'jsCalendar.js?x=agenda-telar-profesional-20260828-01',
    $contenidoInicio,
    1,
    $jsCache
);
if ($cssCache !== 1 || $jsCache !== 1) {
    fwrite(STDERR, "No se pudo actualizar la cache.\n");
    exit(5);
}

foreach (array($js, $css, $inicio) as $ruta) {
    if (!copy($ruta, $ruta.'.bak-20260828-diseno-agenda-telar')) {
        exit(6);
    }
}
if (file_put_contents($js, $contenidoJs) === false
    || file_put_contents($css, $contenidoCss) === false
    || file_put_contents($inicio, $contenidoInicio) === false) {
    exit(7);
}
echo "Diseno de agenda aplicado.\n";

?>

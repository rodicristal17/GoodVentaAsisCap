<?php

/** Entrega protegida de adjuntos de GoHighLevel almacenados en Telar. */

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src 'none'; sandbox");

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/gohighlevel_helper.php';

function goHighLevelAdjuntoFallar($estado, $mensaje)
{
    http_response_code(intval($estado));
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo (string)$mensaje;
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
    goHighLevelAdjuntoFallar(405, 'Metodo no permitido.');
}

$idAdjunto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$vence = isset($_GET['vence']) ? intval($_GET['vence']) : 0;
$firma = isset($_GET['firma']) ? strtolower(trim((string)$_GET['firma'])) : '';
$config = goHighLevelConfiguracionAdjuntos();
$ahora = time();
if ($idAdjunto <= 0 || $vence < $ahora || $vence > ($ahora + 3700)
    || !preg_match('/^[a-f0-9]{64}$/', $firma) || $config['clave'] === '') {
    goHighLevelAdjuntoFallar(403, 'El acceso al adjunto no es valido o ya vencio.');
}
$esperada = goHighLevelAdjuntoFirma($idAdjunto, $vence, $config['clave']);
if (!hash_equals($esperada, $firma)) {
    goHighLevelAdjuntoFallar(403, 'El acceso al adjunto no es valido.');
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno || !goHighLevelTablaExiste($mysqli, 'gohighlevel_adjunto_cache')) {
    goHighLevelAdjuntoFallar(503, 'El almacenamiento de adjuntos no esta disponible.');
}
$fila = goHighLevelObtenerAdjuntoCache($mysqli, $idAdjunto);
if (!$fila) {
    goHighLevelAdjuntoFallar(404, 'El adjunto no existe.');
}

$rutaRelativa = trim((string)$fila['ruta_relativa']);
$ruta = '';
if ((string)$fila['estado'] === 'listo'
    && preg_match('#^[a-f0-9]{2}/[0-9]+\.[a-z0-9]{1,12}$#', $rutaRelativa)) {
    $ruta = $config['directorio'].'/'.$rutaRelativa;
}
if ($ruta === '' || !is_file($ruta) || !is_readable($ruta)) {
    $fila = goHighLevelDescargarAdjunto($mysqli, $fila, $config);
    if (!$fila) {
        goHighLevelAdjuntoFallar(502, 'No se pudo recuperar el adjunto desde GoHighLevel.');
    }
    $rutaRelativa = trim((string)$fila['ruta_relativa']);
    if (!preg_match('#^[a-f0-9]{2}/[0-9]+\.[a-z0-9]{1,12}$#', $rutaRelativa)) {
        goHighLevelAdjuntoFallar(500, 'El adjunto no tiene una ruta valida.');
    }
    $ruta = $config['directorio'].'/'.$rutaRelativa;
}

$baseReal = realpath($config['directorio']);
$rutaReal = realpath($ruta);
if ($baseReal === false || $rutaReal === false
    || strpos($rutaReal, rtrim($baseReal, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR) !== 0
    || !is_file($rutaReal) || !is_readable($rutaReal)) {
    goHighLevelAdjuntoFallar(404, 'El archivo almacenado no esta disponible.');
}

$mime = goHighLevelTexto($fila['mime_type'], 120);
if (!goHighLevelMimeAdjuntoPermitido($mime)) {
    goHighLevelAdjuntoFallar(415, 'El tipo de archivo no esta permitido.');
}
$nombre = preg_replace('/[^A-Za-z0-9._ -]+/u', '_', (string)$fila['nombre_origen']);
if (trim($nombre) === '') {
    $nombre = 'adjunto_'.intval($idAdjunto).'.'.goHighLevelTexto($fila['extension'], 12);
}
$inline = strpos($mime, 'image/') === 0 || strpos($mime, 'video/') === 0
    || strpos($mime, 'audio/') === 0 || $mime === 'application/pdf';
$tamano = filesize($rutaReal);
$stmtVista = $mysqli->prepare(
    "UPDATE gohighlevel_adjunto_cache SET fecha_ultima_vista=NOW(),fecha_actualizacion=NOW() "
    ."WHERE id_adjunto=? LIMIT 1"
);
if ($stmtVista) {
    $stmtVista->bind_param('i', $idAdjunto);
    $stmtVista->execute();
    $stmtVista->close();
}

header('Content-Type: '.$mime);
header('Content-Length: '.intval($tamano));
header('Cache-Control: private, max-age=3600');
header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.str_replace('"', '', $nombre).'"');
readfile($rutaReal);
exit;

?>

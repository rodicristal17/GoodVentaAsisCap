<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$directorio= dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosMensaje';
foreach ($argv as $argumento) {
    if (strpos($argumento, '--directorio=') === 0) {
        $directorioSolicitado= trim(substr($argumento, strlen('--directorio=')));
        if ($directorioSolicitado !== '') {
            $directorio= $directorioSolicitado;
        }
    }
}

function almacenamientoAdjuntosHilosFallar($mensaje)
{
    fwrite(STDERR, 'ALMACENAMIENTO_HILOS_ERROR: '.$mensaje.PHP_EOL);
    exit(1);
}

if (!is_dir($directorio)) {
    almacenamientoAdjuntosHilosFallar('la carpeta no existe');
}

clearstatcache(true, $directorio);
if (!is_writable($directorio)) {
    almacenamientoAdjuntosHilosFallar('la carpeta no permite escritura al usuario actual');
}

try {
    $sufijo= bin2hex(random_bytes(6));
} catch (Exception $e) {
    $sufijo= str_replace('.', '', uniqid('', true));
}

$contenido= 'telar-adjuntos-hilos'.PHP_EOL;
$rutaPrueba= rtrim($directorio, '/\\').DIRECTORY_SEPARATOR.'.telar-write-test-'.$sufijo;
$bytes= @file_put_contents($rutaPrueba, $contenido, LOCK_EX);
if ($bytes === false || intval($bytes) !== strlen($contenido)) {
    if (is_file($rutaPrueba)) {
        @unlink($rutaPrueba);
    }
    almacenamientoAdjuntosHilosFallar('no se pudo crear el archivo temporal');
}

$contenidoLeido= @file_get_contents($rutaPrueba);
if ($contenidoLeido !== $contenido) {
    @unlink($rutaPrueba);
    almacenamientoAdjuntosHilosFallar('el archivo temporal no pudo comprobarse');
}

if (!@unlink($rutaPrueba) || is_file($rutaPrueba)) {
    almacenamientoAdjuntosHilosFallar('el archivo temporal no pudo eliminarse');
}

echo 'ALMACENAMIENTO_HILOS_OK'.PHP_EOL;

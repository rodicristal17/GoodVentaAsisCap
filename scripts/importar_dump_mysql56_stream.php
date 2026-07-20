<?php
if ($argc < 5) {
    fwrite(STDERR, "Uso: php importar_dump_mysql56_stream.php dump mysql.exe base error.log\n");
    exit(2);
}

$dump = $argv[1];
$mysql = $argv[2];
$base = $argv[3];
$errorLog = $argv[4];

if (!is_file($dump) || filesize($dump) <= 0) {
    fwrite(STDERR, "El dump no existe o esta vacio.\n");
    exit(3);
}
if (!is_file($mysql)) {
    fwrite(STDERR, "No se encontro mysql.exe.\n");
    exit(4);
}

$comando = escapeshellarg($mysql)
    .' --host=localhost --port=3306 --user=root --default-character-set=utf8mb4'
    .' --init-command='.escapeshellarg('SET SESSION FOREIGN_KEY_CHECKS=0')
    .' '.escapeshellarg($base);
$descriptores = array(
    0 => array('pipe', 'r'),
    1 => array('file', $errorLog.'.out', 'w'),
    2 => array('file', $errorLog, 'w')
);
$proceso = proc_open($comando, $descriptores, $tuberias);
if (!is_resource($proceso)) {
    fwrite(STDERR, "No se pudo iniciar el cliente MySQL.\n");
    exit(5);
}

$entrada = fopen($dump, 'rb');
$reemplazos = 0;
$falloEscritura = false;
while (($linea = fgets($entrada)) !== false) {
    if (strpos($linea, '`solicitud_abierta` tinyint(1) GENERATED ALWAYS AS') !== false) {
        $linea = "  `solicitud_abierta` tinyint(1) DEFAULT NULL,\n";
        $reemplazos++;
    }
    if (fwrite($tuberias[0], $linea) === false) {
        $falloEscritura = true;
        break;
    }
}
fclose($entrada);
fclose($tuberias[0]);
$codigo = proc_close($proceso);

if ($falloEscritura || $codigo !== 0) {
    fwrite(STDERR, "La importacion fallo. Codigo MySQL: ".$codigo."\n");
    exit(6);
}
if ($reemplazos !== 1) {
    fwrite(STDERR, "La importacion termino, pero se esperaban 1 y se realizaron ".$reemplazos." adaptaciones.\n");
    exit(7);
}

echo "IMPORTACION_STREAM_OK reemplazos=".$reemplazos."\n";
exit(0);

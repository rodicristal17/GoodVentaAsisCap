<?php
/**
 * Migracion controlada de personas_v2 hacia registro_policial (PHP 7.2+ / MySQL 5.6+).
 *
 * Uso:
 *   php scripts/migrar_registro_policial_personas_v2.php
 *
 * La carga se realiza primero en registro_policial_migracion_tmp. La tabla productiva
 * solo se sustituye cuando todos los conteos y controles obligatorios pasan.
 */

set_time_limit(0);
ini_set('memory_limit', '256M');

$mysql = 'C:\\wamp\\bin\\mysql\\mysql5.6.17\\bin\\mysql.exe';
$sourceDir = 'C:\\Users\\jorge\\Downloads';
$sourceNames = array(
    'personas_v2_11.sql', 'personas_v2_22.sql', 'personas_v2_33.sql',
    'personas_v2_44.sql', 'personas_v2_55.sql', 'personas_v2_66.sql',
    'personas_v2_77.sql', 'personas_v2_88.sql', 'personas_v2_99.sql'
);
$targetDb = 'registro_policial';
$stageDb = 'registro_policial_migracion_tmp';
$expectedRows = 8004632;
$logFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR
    . 'registro_policial_migracion_' . date('Ymd_His') . '.log';

function failMigration($message, $logFile)
{
    file_put_contents($logFile, '[' . date('c') . '] ERROR ' . $message . PHP_EOL, FILE_APPEND);
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function logMigration($message, $logFile)
{
    $line = '[' . date('c') . '] ' . $message;
    file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);
    fwrite(STDOUT, $line . PHP_EOL);
}

function mysqlCommand($mysql, $sql, $database, $logFile)
{
    $command = $mysql . ' -u root -N -B --default-character-set=utf8mb4';
    if ($database !== '') {
        $command .= ' ' . $database;
    }
    $descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        failMigration('No se pudo iniciar mysql.exe.', $logFile);
    }
    fwrite($pipes[0], $sql);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        failMigration("MySQL devolvio codigo {$exitCode}: {$stderr}", $logFile);
    }
    return trim($stdout);
}

function importSqlFile($mysql, $database, $path, $logFile)
{
    $command = $mysql . ' -u root --default-character-set=utf8mb4 ' . $database;
    $descriptors = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        failMigration('No se pudo iniciar mysql.exe para ' . basename($path), $logFile);
    }
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $input = fopen($path, 'rb');
    if ($input === false) {
        failMigration('No se pudo abrir ' . $path, $logFile);
    }

    fwrite($pipes[0], "SET NAMES utf8mb4; SET autocommit=0; START TRANSACTION;\n");
    $rows = 0;
    $batch = array();
    while (($line = fgets($input)) !== false) {
        if (strpos($line, 'INSERT INTO persona') !== 0) {
            fclose($input);
            failMigration('Linea inesperada en ' . basename($path) . ' cerca de la fila ' . ($rows + 1), $logFile);
        }
        $valuesPosition = strpos($line, 'VALUES');
        if ($valuesPosition === false) {
            fclose($input);
            failMigration('INSERT sin VALUES en ' . basename($path) . ' cerca de la fila ' . ($rows + 1), $logFile);
        }
        $tuple = trim(substr($line, $valuesPosition + 6));
        $tuple = rtrim($tuple, ";\r\n ");
        $batch[] = $tuple;
        $rows++;
        if (count($batch) === 1000) {
            fwrite($pipes[0], "INSERT INTO persona (sexo,domicilio,tipodoc,ci,nombres,apellidos,fechanac,nacio,lugarnac) VALUES\n" . implode(",\n", $batch) . ";\n");
            $batch = array();
        }
        if (($rows % 100000) === 0) {
            fwrite($pipes[0], "COMMIT; START TRANSACTION;\n");
        }
    }
    fclose($input);
    if (count($batch) > 0) {
        fwrite($pipes[0], "INSERT INTO persona (sexo,domicilio,tipodoc,ci,nombres,apellidos,fechanac,nacio,lugarnac) VALUES\n" . implode(",\n", $batch) . ";\n");
    }
    fwrite($pipes[0], "COMMIT;\n");
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    if ($exitCode !== 0 || $stderr !== '') {
        failMigration('Fallo importando ' . basename($path) . ': ' . trim($stderr . ' ' . $stdout), $logFile);
    }
    return $rows;
}

if (!is_file($mysql)) {
    failMigration('No existe mysql.exe en ' . $mysql, $logFile);
}
foreach ($sourceNames as $sourceName) {
    if (!is_file($sourceDir . DIRECTORY_SEPARATOR . $sourceName)) {
        failMigration('Falta el archivo requerido ' . $sourceName, $logFile);
    }
}

logMigration('Inicio. Fuentes: exclusivamente los nueve personas_v2.', $logFile);
$currentRows = mysqlCommand($mysql, 'SELECT COUNT(*) FROM persona;', $targetDb, $logFile);
if ($currentRows !== '0') {
    failMigration('La tabla destino dejo de estar vacia (filas=' . $currentRows . '). No se reemplazo.', $logFile);
}

$setup = "DROP DATABASE IF EXISTS `{$stageDb}`;\n"
    . "CREATE DATABASE `{$stageDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n"
    . "CREATE TABLE `{$stageDb}`.`persona` (\n"
    . " import_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
    . " sexo VARCHAR(10) NULL, domicilio VARCHAR(500) NULL, tipodoc VARCHAR(30) NULL,\n"
    . " ci VARCHAR(40) NULL, nombres VARCHAR(255) NULL, apellidos VARCHAR(255) NULL,\n"
    . " fechanac VARCHAR(30) NULL, nacio VARCHAR(30) NULL, lugarnac VARCHAR(255) NULL,\n"
    . " PRIMARY KEY (import_id), KEY idx_persona_import_ci (ci)\n"
    . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";
mysqlCommand($mysql, $setup, '', $logFile);

$imported = 0;
foreach ($sourceNames as $sourceName) {
    logMigration('Importando ' . $sourceName . '...', $logFile);
    $rows = importSqlFile($mysql, $stageDb, $sourceDir . DIRECTORY_SEPARATOR . $sourceName, $logFile);
    $imported += $rows;
    logMigration($sourceName . ': ' . $rows . ' filas.', $logFile);
}
if ($imported !== $expectedRows) {
    failMigration('Conteo fuente inesperado: ' . $imported . ' en vez de ' . $expectedRows, $logFile);
}
$stageRows = mysqlCommand($mysql, 'SELECT COUNT(*) FROM persona;', $stageDb, $logFile);
if ((int) $stageRows !== $expectedRows) {
    failMigration('Conteo staging inesperado: ' . $stageRows, $logFile);
}

logMigration('Transformando, validando CI y deduplicando...', $logFile);
$transform = "CREATE TABLE `{$stageDb}`.`persona_rechazada` LIKE `{$stageDb}`.`persona`;\n"
    . "ALTER TABLE `{$stageDb}`.`persona_rechazada` ADD COLUMN motivo VARCHAR(100) NOT NULL DEFAULT 'CI vacia o no numerica';\n"
    . "INSERT INTO `{$stageDb}`.`persona_rechazada` "
    . "SELECT p.*, 'CI vacia o no numerica' FROM `{$stageDb}`.`persona` p "
    . "WHERE TRIM(IFNULL(ci,''))='' OR TRIM(ci) NOT REGEXP '^[0-9]+$' OR CAST(TRIM(ci) AS UNSIGNED)=0;\n"
    . "CREATE TABLE `{$stageDb}`.`persona_final` (\n"
    . " sexo VARCHAR(1) NULL, domicilio VARCHAR(500) NULL, tipodoc VARCHAR(10) NULL,\n"
    . " ci INT UNSIGNED NOT NULL, nombres VARCHAR(255) NULL, apellidos VARCHAR(255) NULL,\n"
    . " fechanac DATE NULL, nacio INT UNSIGNED NULL, lugarnac VARCHAR(255) NULL,\n"
    . " PRIMARY KEY (ci), KEY idx_persona_nombres (apellidos,nombres), KEY idx_persona_tipodoc (tipodoc)\n"
    . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n"
    . "INSERT INTO `{$stageDb}`.`persona_final` (sexo,domicilio,tipodoc,ci,nombres,apellidos,fechanac,nacio,lugarnac)\n"
    . "SELECT LEFT(p.sexo,1),\n"
    . " CASE WHEN (p.domicilio LIKE '%Ã%' OR p.domicilio LIKE '%Â%') AND CONVERT(CAST(CONVERT(p.domicilio USING latin1) AS BINARY) USING utf8mb4) IS NOT NULL THEN CONVERT(CAST(CONVERT(p.domicilio USING latin1) AS BINARY) USING utf8mb4) ELSE p.domicilio END,\n"
    . " LEFT(p.tipodoc,10), CAST(TRIM(p.ci) AS UNSIGNED),\n"
    . " CASE WHEN (p.nombres LIKE '%Ã%' OR p.nombres LIKE '%Â%') AND CONVERT(CAST(CONVERT(p.nombres USING latin1) AS BINARY) USING utf8mb4) IS NOT NULL THEN CONVERT(CAST(CONVERT(p.nombres USING latin1) AS BINARY) USING utf8mb4) ELSE p.nombres END,\n"
    . " CASE WHEN (p.apellidos LIKE '%Ã%' OR p.apellidos LIKE '%Â%') AND CONVERT(CAST(CONVERT(p.apellidos USING latin1) AS BINARY) USING utf8mb4) IS NOT NULL THEN CONVERT(CAST(CONVERT(p.apellidos USING latin1) AS BINARY) USING utf8mb4) ELSE p.apellidos END,\n"
    . " CASE WHEN STR_TO_DATE(p.fechanac,'%d/%m/%Y') BETWEEN '1800-01-01' AND CURDATE() THEN STR_TO_DATE(p.fechanac,'%d/%m/%Y') ELSE NULL END,\n"
    . " CASE WHEN TRIM(p.nacio) REGEXP '^[0-9]+$' THEN CAST(TRIM(p.nacio) AS UNSIGNED) ELSE NULL END,\n"
    . " CASE WHEN (p.lugarnac LIKE '%Ã%' OR p.lugarnac LIKE '%Â%') AND CONVERT(CAST(CONVERT(p.lugarnac USING latin1) AS BINARY) USING utf8mb4) IS NOT NULL THEN CONVERT(CAST(CONVERT(p.lugarnac USING latin1) AS BINARY) USING utf8mb4) ELSE p.lugarnac END\n"
    . "FROM `{$stageDb}`.`persona` p INNER JOIN (\n"
    . " SELECT CAST(TRIM(ci) AS UNSIGNED) ci_num, MAX(import_id) import_id FROM `{$stageDb}`.`persona`\n"
    . " WHERE TRIM(IFNULL(ci,'')) REGEXP '^[0-9]+$' AND CAST(TRIM(ci) AS UNSIGNED)>0 GROUP BY CAST(TRIM(ci) AS UNSIGNED)\n"
    . ") ult ON ult.import_id=p.import_id;\n";
mysqlCommand($mysql, $transform, '', $logFile);

$finalRows = (int) mysqlCommand($mysql, 'SELECT COUNT(*) FROM persona_final;', $stageDb, $logFile);
$rejectedRows = (int) mysqlCommand($mysql, 'SELECT COUNT(*) FROM persona_rechazada;', $stageDb, $logFile);
$badDates = (int) mysqlCommand($mysql, "SELECT COUNT(*) FROM persona p INNER JOIN (SELECT CAST(TRIM(ci) AS UNSIGNED) ci_num,MAX(import_id) import_id FROM persona WHERE TRIM(IFNULL(ci,'')) REGEXP '^[0-9]+$' AND CAST(TRIM(ci) AS UNSIGNED)>0 GROUP BY CAST(TRIM(ci) AS UNSIGNED)) u ON u.import_id=p.import_id WHERE TRIM(IFNULL(p.fechanac,''))<>'' AND STR_TO_DATE(p.fechanac,'%d/%m/%Y') IS NULL;", $stageDb, $logFile);
if (($finalRows + $rejectedRows) > $expectedRows || $finalRows < 1) {
    failMigration('Validacion final inconsistente.', $logFile);
}

logMigration("Validacion: finales={$finalRows}, rechazadas={$rejectedRows}, fechas invalidas={$badDates}.", $logFile);
logMigration('Realizando sustitucion atomica de la tabla vacia...', $logFile);
$cutover = "DROP TABLE IF EXISTS `{$targetDb}`.`persona_antes_migracion`;\n"
    . "RENAME TABLE `{$targetDb}`.`persona` TO `{$targetDb}`.`persona_antes_migracion`,\n"
    . " `{$stageDb}`.`persona_final` TO `{$targetDb}`.`persona`,\n"
    . " `{$stageDb}`.`persona_rechazada` TO `{$targetDb}`.`persona_rechazada`;\n";
mysqlCommand($mysql, $cutover, '', $logFile);

$verified = (int) mysqlCommand($mysql, 'SELECT COUNT(*) FROM persona;', $targetDb, $logFile);
if ($verified !== $finalRows) {
    failMigration('El conteo posterior al corte no coincide.', $logFile);
}
logMigration("MIGRACION COMPLETA. persona={$verified}, rechazadas={$rejectedRows}, fechas invalidas convertidas a NULL={$badDates}.", $logFile);

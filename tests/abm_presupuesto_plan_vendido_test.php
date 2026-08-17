<?php
$directorioOriginal = getcwd();
chdir(__DIR__ . '/../php_system');
require_once('abmPresupuesto.php');
chdir($directorioOriginal);

$casos = array(
    array('entrada' => null, 'esperado' => null),
    array('entrada' => '', 'esperado' => null),
    array('entrada' => '   ', 'esperado' => null),
    array('entrada' => 'total', 'esperado' => 'total'),
    array('entrada' => ' prioritario ', 'esperado' => 'prioritario'),
    array('entrada' => 'urgente', 'esperado' => false)
);

$fallos = array();
foreach ($casos as $indice => $caso) {
    $resultado = normalizarPlanVendidoPresupuesto($caso['entrada']);
    if ($resultado !== $caso['esperado']) {
        $fallos[] = 'Caso ' . ($indice + 1) . ' no coincide.';
    }
}

if (count($fallos) > 0) {
    fwrite(STDERR, implode(PHP_EOL, $fallos) . PHP_EOL);
    exit(1);
}

echo "OK: plan_vendido conserva NULL y acepta solamente total o prioritario." . PHP_EOL;

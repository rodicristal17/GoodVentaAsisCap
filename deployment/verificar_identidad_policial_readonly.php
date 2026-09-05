<?php

require('/var/www/html/php_system/conexion.php');
require('/tmp/cliente_identidad_policial_helper.php');

$casos = array(
    6 => 'no_encontrado',
    224 => 'coincide',
    20 => 'diferente'
);
$mysqli = conectar_al_servidor();
if ($mysqli->connect_errno) {
    fwrite(STDERR, "conexion_error\n");
    exit(1);
}
foreach ($casos as $codCliente => $esperado) {
    $resultado = clienteIdentidadPolicialObtenerComparacion($mysqli, $codCliente);
    $estado = empty($resultado['ok'])
        ? 'error'
        : (empty($resultado['encontrado'])
            ? 'no_encontrado'
            : (!empty($resultado['coincide']) ? 'coincide' : 'diferente'));
    echo $codCliente.":".$estado."\n";
    if ($estado !== $esperado) {
        $mysqli->close();
        exit(2);
    }
    if (!empty($resultado['encontrado'])) {
        $datosCoincidentes = clienteIdentidadPolicialCompararDatos(
            $mysqli,
            $resultado['ci'],
            $resultado['nombre_policial'],
            $resultado['apellido_policial']
        );
        if (empty($datosCoincidentes['coincide'])) {
            $mysqli->close();
            exit(3);
        }
    }
}
$mysqli->close();

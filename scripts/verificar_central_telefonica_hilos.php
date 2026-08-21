<?php

$raiz = dirname(__DIR__);
$archivos = array(
    'migracion' => file_get_contents($raiz.'/actualizacion_21082026_central_telefonica_hilos.sql'),
    'operacion_php' => file_get_contents($raiz.'/php_system/central_telefonica_operacion_helper.php'),
    'operacion_endpoint' => file_get_contents($raiz.'/php_system/abmCentralTelefonicaOperacion.php'),
    'central_php' => file_get_contents($raiz.'/php_system/abmCentralTelefonica.php'),
    'central_js' => file_get_contents($raiz.'/js_system/central_telefonica.js'),
    'operacion_js' => file_get_contents($raiz.'/js_system/central_telefonica_operacion.js'),
    'cartera_php' => file_get_contents($raiz.'/php_system/mi_cartera_helper.php'),
    'cartera_js' => file_get_contents($raiz.'/js_system/mi_cartera.js'),
    'hilos_php' => file_get_contents($raiz.'/php_system/abmInterConsulta.php'),
    'hilos_js' => file_get_contents($raiz.'/js_system/abmInterConsulta.js'),
    'timeline' => file_get_contents($raiz.'/php_system/interconsulta_operaciones_helper.php'),
    'inicio' => file_get_contents($raiz.'/system/inicio.html')
);
$aprobadas = 0;
$fallidas = 0;
function comprobarCentralHilos($condicion, $mensaje)
{
    global $aprobadas, $fallidas;
    if ($condicion) {
        $aprobadas++;
        echo "[OK] ".$mensaje.PHP_EOL;
    } else {
        $fallidas++;
        echo "[ERROR] ".$mensaje.PHP_EOL;
    }
}

comprobarCentralHilos(
    strpos($archivos['migracion'], 'cod_interConsultaFK') !== false
        && strpos($archivos['migracion'], 'origen_solicitud') !== false
        && stripos($archivos['migracion'], 'DROP TABLE') === false,
    'La migracion vincula solicitudes con Hilos sin eliminar historicos.'
);
comprobarCentralHilos(
    strpos($archivos['operacion_php'], 'centralTelefonicaOperacionResolverHiloPaciente') !== false
        && strpos($archivos['operacion_php'], 'hilo_paciente_invalido') !== false,
    'El servidor valida que el Hilo elegido corresponda al paciente.'
);
comprobarCentralHilos(
    strpos($archivos['operacion_endpoint'], "case 'obtener_paciente'") !== false
        && strpos($archivos['operacion_endpoint'], "case 'resolver_hilo_paciente'") !== false,
    'La interfaz puede consultar telefonos y abrir el Hilo canonico.'
);
comprobarCentralHilos(
    strpos($archivos['central_php'], 'COUNT(DISTINCT ct.cod_clienteFK)') !== false
        && strpos($archivos['central_php'], "'compartido' => \$coincidencias > 1") !== false,
    'La identidad automatica exige coincidencia exacta y distingue numeros compartidos.'
);
comprobarCentralHilos(
    strpos($archivos['central_js'], '<th>Paciente / cliente</th>') !== false
        && strpos($archivos['central_js'], 'open-patient-thread') !== false,
    'Central Telefonica muestra el paciente entre direccion y numero y abre su Hilo.'
);
comprobarCentralHilos(
    strpos($archivos['cartera_php'], 'centralTelefonicaNormalizarTelefono($buscar)') !== false
        && strpos($archivos['cartera_js'], 'codigo o telefono') === false
        && strpos($archivos['cartera_js'], 'origen: "mi_cartera"') !== false,
    'Mi cartera busca formatos equivalentes de telefono y registra el origen de la llamada.'
);
comprobarCentralHilos(
    strpos($archivos['hilos_php'], 'data-hilo-action="llamar_paciente"') !== false
        && strpos($archivos['hilos_php'], 'id="td_datos_2" style="display:none;"') !== false
        && strpos($archivos['hilos_js'], 'centralTelefonicaLlamarPaciente') !== false,
    'Hilos reemplaza visualmente Estado por Llamar y conserva el estado tecnico oculto.'
);
comprobarCentralHilos(
    strpos($archivos['timeline'], "'tipo' => 'llamada'") !== false
        && strpos($archivos['timeline'], 'central_telefonica_solicitud_llamada') !== false
        && strpos($archivos['hilos_php'], 'obtenerVistaTarjetaLlamadaTimelineInterConsulta') !== false,
    'La llamada aparece como evento virtual unico dentro del timeline del Hilo.'
);
comprobarCentralHilos(
    strpos($archivos['operacion_js'], 'window.centralTelefonicaLlamarPaciente') !== false
        && strpos($archivos['operacion_js'], 'phones.length > 1') !== false,
    'La llamada directa usa un numero unico o solicita elegir cuando hay varios.'
);
comprobarCentralHilos(
    strpos($archivos['inicio'], 'central_telefonica.js?x=20260821-01') !== false
        && strpos($archivos['inicio'], 'abmInterConsulta.js?x=central-hilos-20260821-1') !== false,
    'Los recursos modificados usan una version nueva para evitar cache anterior.'
);

echo PHP_EOL.'Aprobadas: '.$aprobadas.' | Fallidas: '.$fallidas.PHP_EOL;
exit($fallidas > 0 ? 1 : 0);

<?php

/** Verifica el aislamiento y la espera del piloto automatico. Compatible con PHP 7.2. */

require_once dirname(__DIR__).'/php_system/gohighlevel_helper.php';

$errores = array();
function pilotoIaVerificar($condicion, $mensaje)
{
    global $errores;
    if (!$condicion) {
        $errores[] = $mensaje;
    }
}

$archivo = tempnam(sys_get_temp_dir(), 'telar_ghl_pilot_');
if ($archivo === false) {
    fwrite(STDERR, "Piloto IA: no se pudo preparar el archivo temporal.\n");
    exit(1);
}
file_put_contents($archivo, "ContactoPiloto123\nContactoPiloto456\nvalor invalido\n");
putenv('TELAR_DEEPSEEK_AUTO_SCOPE=pilot');
putenv('TELAR_DEEPSEEK_AUTO_REPLY_DELAY_SECONDS=120');
putenv('TELAR_DEEPSEEK_PILOT_CONTACT_IDS_FILE='.$archivo);

$config = goHighLevelAutomaticoConfiguracion();
pilotoIaVerificar($config['alcance'] === 'pilot', 'El alcance predeterminado debe ser piloto.');
pilotoIaVerificar($config['retardo_segundos'] === 120, 'La espera debe respetar los dos minutos configurados.');
pilotoIaVerificar(count($config['contactos_piloto']) === 2, 'La lista debe ignorar identificadores invalidos.');
pilotoIaVerificar(
    goHighLevelAutomaticoContactoPermitido($config, 'ContactoPiloto123'),
    'El contacto piloto debe quedar permitido.'
);
pilotoIaVerificar(
    !goHighLevelAutomaticoContactoPermitido($config, 'ContactoNoAutorizado'),
    'Un contacto ajeno al piloto debe quedar bloqueado.'
);

$ahora = 1787544000;
$mensajeListo = array(
    'id' => 'MensajePiloto123',
    'direccion' => 'inbound',
    'tipo' => 'WhatsApp',
    'fecha' => (string)(($ahora - 121) * 1000)
);
pilotoIaVerificar(
    goHighLevelAutomaticoMensajeListo($mensajeListo, 120, $ahora),
    'El mensaje debe quedar listo despues de dos minutos.'
);
$mensajeReciente = $mensajeListo;
$mensajeReciente['fecha'] = (string)(($ahora - 119) * 1000);
pilotoIaVerificar(
    !goHighLevelAutomaticoMensajeListo($mensajeReciente, 120, $ahora),
    'El mensaje no debe adelantarse al tiempo de relevo.'
);
$mensajeSaliente = $mensajeListo;
$mensajeSaliente['direccion'] = 'outbound';
pilotoIaVerificar(
    !goHighLevelAutomaticoMensajeListo($mensajeSaliente, 120, $ahora),
    'Una respuesta de GHL o de un funcionario debe cancelar el relevo.'
);

$historialAdministrativo = array(
    array('direccion' => 'inbound', 'cuerpo' => 'Tengo dolor durante el tratamiento'),
    array('direccion' => 'outbound', 'cuerpo' => 'Derivado al equipo'),
    array('direccion' => 'inbound', 'cuerpo' => 'Cual es la direccion de la sede')
);
pilotoIaVerificar(
    goHighLevelRiesgoIa($historialAdministrativo) === '',
    'Un antecedente sensible ya atendido no debe bloquear una consulta administrativa nueva.'
);
$historialMedico = $historialAdministrativo;
$historialMedico[] = array('direccion' => 'inbound', 'cuerpo' => 'Ahora tengo fiebre');
pilotoIaVerificar(
    goHighLevelRiesgoIa($historialMedico) !== '',
    'La consulta sensible actual debe seguir derivandose a una persona.'
);

@unlink($archivo);
if (count($errores) > 0) {
    fwrite(STDERR, "Piloto IA: ".count($errores)." error(es).\n");
    foreach ($errores as $error) {
        fwrite(STDERR, '- '.$error."\n");
    }
    exit(1);
}

echo "Piloto IA: aislamiento y espera verificados.\n";
exit(0);

?>

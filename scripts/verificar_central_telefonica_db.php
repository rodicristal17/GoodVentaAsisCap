<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__).'/php_system/conexion.php';
require_once dirname(__DIR__).'/php_system/central_telefonica_sync_helper.php';

$aprobadas = 0;
$fallidas = 0;

function centralTelefonicaDbPrueba($condicion, $mensaje)
{
    global $aprobadas, $fallidas;
    if ($condicion) {
        $aprobadas++;
        fwrite(STDOUT, '[OK] '.$mensaje.PHP_EOL);
        return;
    }
    $fallidas++;
    fwrite(STDERR, '[ERROR] '.$mensaje.PHP_EOL);
}

$mysqli = conectar_al_servidor();
if (!$mysqli || $mysqli->connect_errno) {
    fwrite(STDERR, "No se pudo abrir la base local de Telar.\n");
    exit(2);
}

centralTelefonicaDbPrueba(
    centralTelefonicaEstructuraDisponible($mysqli),
    'Las tres tablas de Central Telefonica estan disponibles.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM listadodeacceso "
    ."WHERE codigo LIKE '%CENTRALTELEFONICA%'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 6,
    'El catalogo contiene exactamente seis permisos telefonicos.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name IN ('central_telefonica_transcripcion',"
    ."'central_telefonica_transcripcion_evento','central_telefonica_transcripcion_servicio')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 3,
    'Las tres tablas aditivas de transcripcion estan disponibles.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM central_telefonica_transcripcion_servicio "
    ."WHERE id_servicio=1 AND proveedor='openai' "
    ."AND modelo='gpt-4o-transcribe-diarize' "
    ."AND estado IN ('sin_configurar','disponible','error','deshabilitado')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 1,
    'El servicio singleton conserva proveedor, modelo y un estado controlado.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total,"
    ."SUM(au.usuarios_idusario=5994) carlos FROM accesosuser au "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
    ."WHERE la.codigo='TRANSCRIBIRLLAMADACENTRALTELEFONICA' "
    ."AND au.tipo='Administrativo' AND UPPER(TRIM(au.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0, 'carlos' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 1 && intval($fila['carlos']) === 1,
    'Solo la cuenta protegida de Carlos puede solicitar y consultar transcripciones.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM information_schema.tables WHERE table_schema=DATABASE() "
    ."AND table_name='central_telefonica_directorio_evento'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 1 && centralTelefonicaDirectorioAdministracionDisponible($mysqli),
    'La auditoria y la administracion del directorio estan disponibles.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total,SUM(TRIM(cargo_visible)<>'') con_cargo "
    ."FROM central_telefonica_directorio WHERE extension IN "
    ."('1000','1001','1002','1003','1004','1005','1006','1007','1009','1010','1011',"
    ."'2000','2002','2003','2100','2101','2102','2200','2201','2202','2300','2301','2302')"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0, 'con_cargo' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 23 && intval($fila['con_cargo']) === 23,
    'Las 23 extensiones confirmadas estan precargadas con cargo visible.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM (SELECT cod_usuarioFK FROM central_telefonica_directorio "
    ."WHERE activo=1 AND cod_usuarioFK IS NOT NULL GROUP BY cod_usuarioFK HAVING COUNT(*)>1) duplicados"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 1);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 0,
    'Ningun usuario de Telar ocupa mas de una extension vigente.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total,SUM(au.usuarios_idusario=5994) carlos FROM accesosuser au "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
    ."WHERE la.codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA' "
    ."AND au.tipo='Administrativo' AND UPPER(TRIM(au.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0, 'carlos' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 1 && intval($fila['carlos']) === 1,
    'Solo la cuenta protegida de Carlos puede administrar asociaciones telefonicas.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM detallesniveles dn "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso "
    ."WHERE la.codigo='ADMINISTRARDIRECTORIOCENTRALTELEFONICA' "
    ."AND UPPER(TRIM(dn.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 1);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 0,
    'Ningun rol hereda la administracion del directorio telefonico.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM dashboard_access_catalog "
    ."WHERE access_key='central_telefonica' AND permission_key='VERCENTRALTELEFONICA' "
    ."AND is_active=1 AND is_default_quick_access=1"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 1,
    'El acceso rapido activo depende del permiso principal.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM detallesniveles dn "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso "
    ."WHERE la.codigo='ESCUCHARGRABACIONCENTRALTELEFONICA' "
    ."AND UPPER(TRIM(dn.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 0,
    'Ningun rol recibe permiso de escuchar grabaciones en la Fase 1.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM detallesniveles dn "
    ."INNER JOIN listado_niveles ln ON ln.cod_niveles=dn.cod_nivelesfk "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso "
    ."WHERE UPPER(TRIM(ln.nombre))='ADMINISTRATIVO' "
    ."AND la.codigo IN ('VERCENTRALTELEFONICA',"
    ."'VERTELEFONOSCOMPLETOSCENTRALTELEFONICA',"
    ."'VERDATOSTECNICOSCENTRALTELEFONICA') "
    ."AND UPPER(TRIM(dn.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) >= 3,
    'El rol administrativo recibe lectura, telefonos completos y datos tecnicos.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM accesosuser au "
    ."INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK "
    ."WHERE au.usuarios_idusario=5994 AND la.codigo='VERCENTRALTELEFONICA' "
    ."AND au.tipo='Administrativo' AND UPPER(TRIM(au.accion))='SI'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 0);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 1,
    'La cuenta administradora protegida puede abrir el modulo con validacion servidor.'
);

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM ("
    ."SELECT user_id FROM dashboard_user_shortcuts WHERE is_visible=1 "
    ."GROUP BY user_id HAVING COUNT(*)>20) excesos"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 1);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 0,
    'Ninguna personalizacion supera el limite de veinte accesos rapidos.'
);

$config = centralTelefonicaCargarConfiguracionIssabel();
$tablaFuentePrueba = 'tmp_central_issabel_cdr_fixture';
$mysqli->query('DROP TEMPORARY TABLE IF EXISTS '.$tablaFuentePrueba);
$creadaFuente = $mysqli->query(
    'CREATE TEMPORARY TABLE '.$tablaFuentePrueba.' ('
    .'calldate DATETIME NOT NULL,src VARCHAR(80),dst VARCHAR(80),'
    .'dcontext VARCHAR(120),channel VARCHAR(190),dstchannel VARCHAR(190),'
    .'disposition VARCHAR(40),duration INT,billsec INT,uniqueid VARCHAR(80),'
    .'linkedid VARCHAR(80),sequence INT,recordingfile VARCHAR(255),'
    .'clid VARCHAR(120),lastapp VARCHAR(80)) ENGINE=MEMORY'
);
centralTelefonicaDbPrueba(
    $creadaFuente === true,
    'La prueba puede reproducir el esquema CDR estandar sin tablas permanentes.'
);
if ($creadaFuente) {
    $mysqli->query(
        "INSERT INTO ".$tablaFuentePrueba
        ." (calldate,src,dst,dcontext,channel,dstchannel,disposition,duration,"
        ."billsec,uniqueid,linkedid,sequence,recordingfile,clid,lastapp) VALUES "
        ."(NOW(),'1009','0981000000','from-internal','PJSIP/1009-test',"
        ."'SIP/to-gw-gsm-test','ANSWERED',30,21,'fixture.1','fixture',1,'',"
        ."'Clinident <1009>','Dial')"
    );
    $configFuente = $config;
    $configFuente['table'] = $tablaFuentePrueba;
    $lecturaFuente = centralTelefonicaSyncLeerFuente(
        $mysqli,
        $configFuente,
        date('Y-m-d H:i:s', strtotime('-1 day')),
        100
    );
    centralTelefonicaDbPrueba(
        count($lecturaFuente['filas']) === 1
            && $lecturaFuente['filas'][0]['cdr_uniqueid'] === 'fixture.1'
            && intval($lecturaFuente['filas'][0]['hablado_seg']) === 21,
        'El lector detecta columnas reales y recupera un CDR estandar.'
    );
    $mysqli->query('DROP TEMPORARY TABLE IF EXISTS '.$tablaFuentePrueba);
}

$marca = str_replace('.', '', uniqid('ctest', true));
$filaFuente = array(
    'fecha_inicio' => date('Y-m-d H:i:s'),
    'origen_original' => '1009',
    'destino_original' => '0981000000',
    'contexto' => 'from-internal',
    'canal' => 'PJSIP/1009-test',
    'canal_destino' => 'SIP/to-gw-gsm-test',
    'disposicion' => 'ANSWERED',
    'duracion_seg' => 30,
    'hablado_seg' => 21,
    'cdr_uniqueid' => $marca.'.1',
    'cdr_linkedid' => $marca,
    'cdr_sequence' => 1,
    'grabacion_referencia' => '',
    'clid' => '',
    'lastapp' => 'Dial'
);

$mysqli->begin_transaction();
try {
    $segmento = centralTelefonicaSyncPrepararSegmento($filaFuente, $config);
    centralTelefonicaSyncGuardarSegmento($mysqli, $segmento);
    centralTelefonicaSyncGuardarSegmento($mysqli, $segmento);

    $stmt = $mysqli->prepare(
        'SELECT COUNT(*) total FROM central_telefonica_cdr_segmento WHERE fuente_clave=?'
    );
    $stmt->bind_param('s', $segmento['fuente_clave']);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    centralTelefonicaDbPrueba(
        intval($fila['total']) === 1,
        'Dos escrituras del mismo CDR conservan un solo segmento.'
    );

    $segmentos = centralTelefonicaSyncSegmentosGrupo($mysqli, $segmento['grupo_clave']);
    $consolidado = centralTelefonicaConstruirConsolidado($segmentos, $config);
    if (centralTelefonicaDirectorioEstructuraDisponible($mysqli)) {
        $consolidado = centralTelefonicaDirectorioEnriquecerConsolidado(
            $mysqli,
            $consolidado,
            $segmentos,
            $config
        );
    }
    centralTelefonicaSyncGuardarConsolidado($mysqli, $consolidado);
    centralTelefonicaSyncGuardarConsolidado($mysqli, $consolidado);
    $stmt = $mysqli->prepare(
        'SELECT COUNT(*) total FROM central_telefonica_llamada WHERE llamada_clave=?'
    );
    $stmt->bind_param('s', $consolidado['llamada_clave']);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    centralTelefonicaDbPrueba(
        intval($fila['total']) === 1,
        'Dos consolidaciones conservan una sola llamada comercial.'
    );
    centralTelefonicaDbPrueba(
        $consolidado['tipo'] === 'saliente_externa'
            && $consolidado['estado'] === 'contestada',
        'La persistencia conserva la clasificacion y el estado esperados.'
    );
    $mysqli->rollback();
} catch (Exception $e) {
    $mysqli->rollback();
    fwrite(STDERR, '[ERROR] La prueba transaccional fallo: '.$e->getMessage().PHP_EOL);
    $fallidas++;
} catch (Throwable $e) {
    $mysqli->rollback();
    fwrite(STDERR, '[ERROR] La prueba transaccional fallo: '.$e->getMessage().PHP_EOL);
    $fallidas++;
}

$resultado = $mysqli->query(
    "SELECT COUNT(*) total FROM central_telefonica_cdr_segmento "
    ."WHERE cdr_linkedid='".$mysqli->real_escape_string($marca)."'"
);
$fila = $resultado ? $resultado->fetch_assoc() : array('total' => 1);
centralTelefonicaDbPrueba(
    intval($fila['total']) === 0,
    'La prueba revirtio todos los CDR ficticios.'
);

$usuarioPrueba = null;
$resultado = $mysqli->query(
    "SELECT u.cod_usuario,IFNULL(u.cod_localFK,0) cod_local FROM usuario u "
    ."INNER JOIN persona p ON p.cod_persona=u.cod_usuario "
    ."WHERE UPPER(TRIM(IFNULL(u.estado,'')))='ACTIVO' "
    ."AND u.cod_localFK IS NOT NULL AND u.cod_localFK>0 "
    ."AND TRIM(IFNULL(p.nombre_persona,''))<>'' ORDER BY u.cod_usuario LIMIT 1"
);
if ($resultado) {
    $usuarioPrueba = $resultado->fetch_assoc();
}

function centralTelefonicaDbCrearTablaTemporalSombra($mysqli, $tabla)
{
    $resultado = $mysqli->query('SHOW CREATE TABLE `'.$tabla.'`');
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    if (!$fila) {
        return false;
    }
    $valores = array_values($fila);
    $sql = isset($valores[1]) ? (string)$valores[1] : '';
    $sql = preg_replace('/^CREATE TABLE /', 'CREATE TEMPORARY TABLE ', $sql, 1);
    return $sql !== '' && $mysqli->query($sql) === true;
}
$crudTemporal = false;
if ($usuarioPrueba
    && centralTelefonicaDbCrearTablaTemporalSombra($mysqli, 'central_telefonica_directorio')
    && centralTelefonicaDbCrearTablaTemporalSombra($mysqli, 'central_telefonica_directorio_evento')
    && centralTelefonicaDbCrearTablaTemporalSombra($mysqli, 'central_telefonica_llamada')) {
    $codUsuarioPrueba = intval($usuarioPrueba['cod_usuario']);
    $codLocalPrueba = intval($usuarioPrueba['cod_local']);
    try {
        $cargoRechazado = false;
        try {
            centralTelefonicaDirectorioAdministracionGuardar(
                $mysqli, '3990', '', $codUsuarioPrueba, $codLocalPrueba,
                '', false, true, $codUsuarioPrueba, '127.0.0.1'
            );
        } catch (CentralTelefonicaDirectorioExcepcion $e) {
            $cargoRechazado = $e->codigoOperacion === 'cargo_requerido';
        }
        centralTelefonicaDirectorioAdministracionGuardar(
            $mysqli, '3991', 'Prueba interna', $codUsuarioPrueba, $codLocalPrueba,
            '', false, true, $codUsuarioPrueba, '127.0.0.1'
        );
        $duplicadoRechazado = false;
        try {
            centralTelefonicaDirectorioAdministracionGuardar(
                $mysqli, '3992', 'Prueba interna 2', $codUsuarioPrueba, $codLocalPrueba,
                '', false, true, $codUsuarioPrueba, '127.0.0.1'
            );
        } catch (CentralTelefonicaDirectorioExcepcion $e) {
            $duplicadoRechazado = $e->codigoOperacion === 'funcionario_ya_asignado';
        }
        centralTelefonicaDirectorioAdministracionGuardar(
            $mysqli, '3991', 'Prueba interna', 0, 0,
            '', true, false, $codUsuarioPrueba, '127.0.0.1'
        );
        centralTelefonicaDirectorioAdministracionGuardar(
            $mysqli, '3992', 'Prueba interna 2', $codUsuarioPrueba, $codLocalPrueba,
            '', false, true, $codUsuarioPrueba, '127.0.0.1'
        );
        $resultado = $mysqli->query(
            "SELECT COUNT(*) total,SUM(extension='3991' AND cod_usuarioFK IS NULL "
            ."AND cod_localFK=".$codLocalPrueba." AND cargo_visible='Prueba interna') liberada,"
            ."SUM(extension='3992' AND cod_usuarioFK=".$codUsuarioPrueba.") reasignada "
            ."FROM central_telefonica_directorio"
        );
        $filaCrud = $resultado ? $resultado->fetch_assoc() : array();
        $crudTemporal = $cargoRechazado && $duplicadoRechazado
            && intval(isset($filaCrud['total']) ? $filaCrud['total'] : 0) === 2
            && intval(isset($filaCrud['liberada']) ? $filaCrud['liberada'] : 0) === 1
            && intval(isset($filaCrud['reasignada']) ? $filaCrud['reasignada'] : 0) === 1;
    } catch (Exception $e) {
        $crudTemporal = false;
    } catch (Throwable $e) {
        $crudTemporal = false;
    }
}
centralTelefonicaDbPrueba(
    $crudTemporal,
    'El alta manual, la exclusividad y Quitar usuario funcionan en tablas temporales.'
);
$mysqli->query('DROP TEMPORARY TABLE IF EXISTS central_telefonica_llamada');
$mysqli->query('DROP TEMPORARY TABLE IF EXISTS central_telefonica_directorio_evento');
$mysqli->query('DROP TEMPORARY TABLE IF EXISTS central_telefonica_directorio');

$mysqli->close();
fwrite(STDOUT, 'Aprobadas: '.$aprobadas.' | Fallidas: '.$fallidas.PHP_EOL);
exit($fallidas > 0 ? 1 : 0);

?>

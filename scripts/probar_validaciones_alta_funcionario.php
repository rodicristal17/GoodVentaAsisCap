<?php
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'php_system'.DIRECTORY_SEPARATOR.'abmusuarios.php');

$caso=isset($argv[1]) ? $argv[1] : 'nombre';
$mysqli=conectar_al_servidor();
$sql="SELECT au.usuarios_idusario
    FROM accesosuser au
    INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK
    WHERE la.codigo='INSERTARLISTADOUSUARIO' AND au.accion='SI'
    ORDER BY au.usuarios_idusario ASC LIMIT 1";
$resultado=$mysqli->query($sql);
$fila=$resultado ? $resultado->fetch_assoc() : null;
$mysqli->close();

if(!$fila){
    echo json_encode(array('1'=>'error','codigo'=>'sin_usuario_prueba')).PHP_EOL;
    exit(1);
}

$usuarioAccion=(int)$fila['usuarios_idusario'];
if($caso==='rollback_mecanico'){
    $mysqli=conectar_al_servidor();
    $resultadoRol=$mysqli->query("SELECT cod_niveles FROM listado_niveles WHERE UPPER(TRIM(nombre))='MECANICO DENTAL / LABORATORIO' AND estado='Activo' ORDER BY cod_niveles ASC LIMIT 1");
    $rol=$resultadoRol ? $resultadoRol->fetch_assoc() : null;
    $resultadoLocal=$mysqli->query("SELECT cod_local FROM local WHERE estado='Activo' ORDER BY cod_local ASC LIMIT 1");
    $local=$resultadoLocal ? $resultadoLocal->fetch_assoc() : null;
    $mysqli->close();
    if(!$rol || !$local){
        echo json_encode(array('1'=>'error','codigo'=>'configuracion_prueba_incompleta')).PHP_EOL;
        exit(1);
    }
    $loginPrueba='__telar_rollback_'.date('YmdHis').'_'.mt_rand(1000,9999);
    $documentoPrueba='__doc_rollback_'.date('YmdHis').'_'.mt_rand(1000,9999);
    ob_start();
    register_shutdown_function(function() use ($loginPrueba,$documentoPrueba){
        $respuesta=ob_get_clean();
        $datos=json_decode((string)$respuesta,true);
        $mysqliVerificacion=conectar_al_servidor();
        $stmt=$mysqliVerificacion->prepare("SELECT COUNT(*) FROM usuario WHERE login=? OR rut_usuario=?");
        $stmt->bind_param('ss',$loginPrueba,$documentoPrueba);
        $stmt->execute();
        $total=0;
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        $mysqliVerificacion->close();
        echo json_encode(array(
            'codigo'=>isset($datos['codigo']) ? $datos['codigo'] : 'respuesta_invalida',
            'campo'=>isset($datos['campo']) ? $datos['campo'] : '',
            'registros_persistidos'=>(int)$total
        )).PHP_EOL;
    });
    crearFuncionarioGuiadoTelar(
        'MECANICO DENTAL','FUNCIONARIO PRUEBA ROLLBACK','',$documentoPrueba,
        $loginPrueba,'ClaveTemporal1@','Activo',(int)$rol['cod_niveles'],(int)$local['cod_local'],
        '','','','',$usuarioAccion,'999999999','',''
    );
}

if($caso==='mecanico'){
    crearFuncionarioGuiadoTelar(
        'MECANICO DENTAL','FUNCIONARIO PRUEBA','','DOC-PRUEBA-NO-GUARDAR',
        'login-prueba-no-guardar','ClaveTemporal1@','Activo',1,1,
        '','','','',$usuarioAccion,'','',''
    );
}

crearFuncionarioGuiadoTelar(
    'ADMINISTRATIVO','','','DOC-PRUEBA-NO-GUARDAR',
    'login-prueba-no-guardar','ClaveTemporal1@','Activo',1,1,
    '','','','',$usuarioAccion,'','',''
);

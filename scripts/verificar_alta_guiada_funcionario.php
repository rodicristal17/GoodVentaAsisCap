<?php
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'php_system'.DIRECTORY_SEPARATOR.'conexion.php');

date_default_timezone_set('America/Asuncion');

function salidaAltaFuncionario($ok,$mensaje)
{
    echo ($ok ? '[OK] ' : '[ERROR] ').$mensaje.PHP_EOL;
    return $ok;
}

function aplicarMigracionAltaFuncionario($mysqli,$ruta)
{
    $sql=file_get_contents($ruta);
    if($sql===false || trim($sql)===''){
        throw new Exception('No se pudo leer la migracion.');
    }
    if(!$mysqli->multi_query($sql)){
        throw new Exception($mysqli->error);
    }
    do{
        if($resultado=$mysqli->store_result()){
            $resultado->free();
        }
        if(!$mysqli->more_results()){
            break;
        }
    }while($mysqli->next_result());
    if($mysqli->errno){
        throw new Exception($mysqli->error);
    }
}

$base=dirname(__DIR__);
$rutaMigracion=$base.DIRECTORY_SEPARATOR.'actualizacion_22072026_alta_guiada_funcionario_mecanico_dental.sql';
$aplicar=in_array('--apply',$argv,true);
$mysqli=conectar_al_servidor();
$correcto=true;

try{
    if($aplicar){
        aplicarMigracionAltaFuncionario($mysqli,$rutaMigracion);
        salidaAltaFuncionario(true,'Migracion del perfil de mecanico dental aplicada.');
    }

    $sqlRol="SELECT cod_niveles,nombre,estado
        FROM listado_niveles
        WHERE UPPER(TRIM(nombre))='MECANICO DENTAL / LABORATORIO'
          AND tipo='Administrativo'
        ORDER BY cod_niveles ASC";
    $resultado=$mysqli->query($sqlRol);
    $roles=$resultado ? $resultado->num_rows : 0;
    $rol=$resultado && $roles>0 ? $resultado->fetch_assoc() : null;
    $correcto=salidaAltaFuncionario($roles===1,'Existe un unico perfil MECANICO DENTAL / LABORATORIO.') && $correcto;
    $correcto=salidaAltaFuncionario($rol && $rol['estado']==='Activo','El perfil de mecanico dental esta activo.') && $correcto;

    if($rol){
        $codRol=(int)$rol['cod_niveles'];
        $sqlPermisos="SELECT
            COUNT(DISTINCT CASE WHEN dn.accion='SI' AND la.codigo IN
              ('VERTRABAJOSLABORATORIO','RECIBIRTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO')
              THEN la.codigo END) AS requeridos,
            COUNT(DISTINCT CASE WHEN dn.accion='SI' AND la.codigo NOT IN
              ('VERTRABAJOSLABORATORIO','RECIBIRTRABAJOLABORATORIO','ENTREGARTRABAJOLABORATORIO')
              THEN la.codigo END) AS adicionales
            FROM detallesniveles dn
            INNER JOIN listadodeacceso la ON la.idlistadodeacceso=dn.idlistadodeacceso
            WHERE dn.cod_nivelesfk=".$codRol;
        $resultadoPermisos=$mysqli->query($sqlPermisos);
        $permisos=$resultadoPermisos ? $resultadoPermisos->fetch_assoc() : null;
        $correcto=salidaAltaFuncionario(
            $permisos && (int)$permisos['requeridos']===3,
            'El perfil contiene los tres permisos operativos de laboratorio.'
        ) && $correcto;
        $correcto=salidaAltaFuncionario(
            $permisos && (int)$permisos['adicionales']===0,
            'El perfil no concede permisos adicionales.'
        ) && $correcto;
    }

    $resultadoColumna=$mysqli->query("SHOW COLUMNS FROM mecanico_dental LIKE 'cod_usuarioFK'");
    $correcto=salidaAltaFuncionario(
        $resultadoColumna && $resultadoColumna->num_rows===1,
        'La tabla mecanico_dental admite el vinculo con una cuenta Telar.'
    ) && $correcto;

    $archivos=array(
        'system/inicio.html'=>array('funcionarioAltaGuiada','MECANICO DENTAL'),
        'js_system/inicio.js'=>array('validarPasoAltaFuncionario','generarContrasenhaTemporalFuncionario','buscarMecanicosDisponiblesAlta'),
        'php_system/abmusuarios.php'=>array('crearFuncionarioGuiadoTelar','begin_transaction','funcionario_duplicado')
    );
    foreach($archivos as $relativa=>$marcas){
        $contenido=file_get_contents($base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relativa));
        $presente=$contenido!==false;
        foreach($marcas as $marca){
            $presente=$presente && strpos($contenido,$marca)!==false;
        }
        $correcto=salidaAltaFuncionario($presente,'Integracion presente en '.$relativa.'.') && $correcto;
    }
}catch(Exception $e){
    $correcto=false;
    salidaAltaFuncionario(false,$e->getMessage());
}

$mysqli->close();
echo $correcto ? 'RESULTADO: APROBADO'.PHP_EOL : 'RESULTADO: REVISAR'.PHP_EOL;
exit($correcto ? 0 : 1);


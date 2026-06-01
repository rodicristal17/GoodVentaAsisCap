<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("subir_foto_base64.php");
    include_once("abmgasto.php");
    require_once("abmDictamen.php");

    date_default_timezone_set('America/Asuncion');

    function verificarOperacionInterConsulta($funt) {
        $user = $_POST['useru'];
        $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
        $pass = $_POST['passu'];

        $pass = str_replace("=", "+", $pass);
        $navegador = $_POST['navegador'];
        $navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
        $resp = verificar_navegador($user, $navegador, $pass);
        if ($resp != "ok") {
            $informacion = array("1" => "UI");
            echo json_encode($informacion);
            exit;
        }

        $fechaActual= new DateTime();
        switch ($funt) {
            case 'buscarInterConsultaPorPaciente':
                $paciente= isset($_POST['paciente']) ? mb_convert_encoding((string)($_POST['paciente']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    "paciente" => $paciente,
                    "cod_usuarioFK" => $user
                );
                
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                buscarVistaPacienteConInterConsulta($filtros, $limite);
                break;
            case 'buscarInterConsultas':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $mencion= isset($_POST['mencion']) ? mb_convert_encoding((string)($_POST['mencion']), 'ISO-8859-1', 'UTF-8') : false;
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_cliente= isset($_POST['nombre_cliente']) ? mb_convert_encoding((string)($_POST['nombre_cliente']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_responsable= isset($_POST['nombre_responsable']) ? mb_convert_encoding((string)($_POST['nombre_responsable']), 'ISO-8859-1', 'UTF-8') : null;
                $ocultar_inactivos= isset($_POST['ocultar_inactivos']) ? mb_convert_encoding((string)($_POST['ocultar_inactivos']), 'ISO-8859-1', 'UTF-8') : null;
                $usuario_vinculado= isset($_POST['usuario_vinculado']) ? mb_convert_encoding((string)($_POST['usuario_vinculado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $busqueda_global= isset($_POST['busqueda_global']) ? mb_convert_encoding((string)($_POST['busqueda_global']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'asunto'=> $asunto,
                    'estado'=> $estado,
                    'tipo'=> $tipo,
                    'mencion'=> $mencion,
                    'cod_ventaFK'=> $cod_ventaFK,
                    'cod_usuarioFK'=> $cod_usuarioFK,
                    'cod_localFK'=> $cod_localFK,
                    'nombre_cliente'=> $nombre_cliente,
                    'nombre_responsable'=> $nombre_responsable,
                    'ocultar_inactivos'=> $ocultar_inactivos,
                    'usuario_vinculado'=> $usuario_vinculado,
                    'busqueda_global'=> $busqueda_global,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'fecha_limite' => "NOW()"
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaInterConsulta($filtros, $limite);
                break;
            case 'buscarInterConsultasYContenido':
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                
                $filtros= array(
                    "cod_ventaFK" => $cod_ventaFK,
                    "cod_interConsulta" => $cod_interConsulta,
                    "cod_usuarioFK" => $user
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario);
                break;
            case 'buscarFlujoGastosInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $registrosInterc= obtenerInterConsulta(array(
                    "cod_interConsulta" => $cod_interConsulta,
                    "cod_usuarioFK" => $user
                ), 1);
                if (count($registrosInterc) == 0) {
                    echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
                    break;
                }
                $paginaGastos= obtenerVistaFlujoGastosInterConsulta($cod_interConsulta);
                echo json_encode(array("1" => "exito", "2" => $paginaGastos));
                break;
            case 'nuevo/editar interconsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;
                $observacion= isset($_POST['observacion']) ? mb_convert_encoding((string)($_POST['observacion']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= (isset($_POST['cod_localFK']) && is_numeric($_POST['cod_localFK'])) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $monto_limite= (isset($_POST['monto_limite']) ? mb_convert_encoding((string)($_POST['monto_limite']), 'ISO-8859-1', 'UTF-8') : null);

                $cod_interConsulta= abmInterConsulta($cod_interConsulta, $asunto, $observacion, $estado, $tipo, $cod_ventaFK, $user, $user, $cod_localFK, $monto_limite);
                echo json_encode(array("1" => "exito", "2" => $cod_interConsulta));
                break;
            case 'marcarMensajesLeido':
                $cod_interConsulta= mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8');

                $registrosMens= obtenerMensaje(array(
                    'cod_interConsultaFK' => $cod_interConsulta,
                    'fecha_creacion' => "<= NOW()",
                ), 0);
                foreach ($registrosMens as $valueMens) {
                    $registrosMenc= obtenerMencion(array(
                        "cod_mensajeFK" => $valueMens['cod_mensaje'],
                        "cod_usuarioFK" => $user,
                        "isLeido" => 0
                    ), 0);

                    foreach ($registrosMenc as $key => $valueMenc) {
                        abmMencion($valueMenc['cod_mencion'], null, null, 1, 'activo');
                    }
                }
                echo json_encode(array("1" => "exito"));
                break;
            case 'eliminarMencionMensaje':
                $cod_mencion= mb_convert_encoding((string)($_POST['cod_mencion']), 'ISO-8859-1', 'UTF-8');
                $cod_interConsulta= mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8');

                // Obtiene informacion extra de la mencion
                $registroMenc= obtenerMencion(array(
                    'cod_mencion' => $cod_mencion
                ), 1)[0];

                abmMencion($cod_mencion, null, null, null, 'inactivo');
                
                // Se registra el cambio por auditoria
                $fechaActual= new DateTime();
                $fechaActual= $fechaActual->format('Y-m-d H:i:s');
                abmMensaje(null, 'Se quito la mencion de '.$registroMenc['nombre_persona'], $fechaActual, $cod_interConsulta, $user, NULL);

                echo json_encode(array("1" => "exito"));
                break;
            case 'buscarVistaMensajesSeleccionar':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $registrosMens= obtenerMensaje(array(
                    'cod_interConsultaFK'=> $cod_interConsulta,
                ), $limite);
                
                $pagina= "";
                foreach ($registrosMens as $regMens) {
                    $contenidoMensaje= $regMens['contenido'];
                    // Transforma las menciones a elementos
                    $usuarios= buscarUsuarios();
                    foreach ($usuarios as $valueUsu) {
                        $contenidoMensaje= str_replace(
                            '@{'.$valueUsu['cod_usuario'].'}', 
                            '<b class="menciones-mensaje" id="'.$valueUsu['cod_usuario'].'" title="Mencion a '.$valueUsu['nombre_persona'].'">@'.$valueUsu['nombre_persona'].'</b>', 
                            $contenidoMensaje
                        );
                    }
                    $contenidoMensaje = nl2br($contenidoMensaje, false);
                    $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding= "5"><tr onclick="obtenerDatosMensajeSeleccionar(this)">
                        <td id="td_id" style="display: none;">'.$regMens['cod_mensaje'].'</td>
                        <td id="td_datos_1" class="tdRegistroSearch" style="width: 10%;"><input type="checkbox"/></td>
                        <td id="td_datos_2" class="tdRegistroSearch" style="width: 65%;">'.$contenidoMensaje.'</td>
                        <td id="td_datos_3" class="tdRegistroSearch" style="width: 15%;">'.$regMens['fecha_creacion'].'</td>
                        <td id="td_datos_4" class="tdRegistroSearch" style="width: 10%;">'.$regMens['nombre_persona'].'</td>
                    </tr></table>';
                }

                echo json_encode(array("1" => "exito", "2" => $registrosMens, "3" => $pagina));
                break;
            case 'buscarMensaje':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_mensaje= isset($_POST['cod_mensaje']) ? mb_convert_encoding((string)($_POST['cod_mensaje']), 'ISO-8859-1', 'UTF-8') : null;
                $contenido= isset($_POST['contenido']) ? mb_convert_encoding((string)($_POST['contenido']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_creacion= isset($_POST['fecha_creacion']) ? mb_convert_encoding((string)($_POST['fecha_creacion']), 'ISO-8859-1', 'UTF-8') : "<= NOW()";

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'cod_mensaje'=> $cod_mensaje,
                    'contenido'=> $contenido,
                    'fecha_creacion' => $fecha_creacion,
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;
                
                obtenerVistaMensaje($filtros, $limite);
                break;
            case 'nuevo/editar mensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? mb_convert_encoding((string)($_POST['cod_mensaje']), 'ISO-8859-1', 'UTF-8') : null;
                $contenido= isset($_POST['contenido']) ? mb_convert_encoding((string)($_POST['contenido']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_creacion= isset($_POST['fecha_creacion']) ? mb_convert_encoding((string)($_POST['fecha_creacion']), 'ISO-8859-1', 'UTF-8') : 'NOW()';
                $cod_dictamenFK= isset($_POST['cod_dictamenFK']) && !empty($_POST['cod_dictamenFK']) ? mb_convert_encoding((string)($_POST['cod_dictamenFK']), 'ISO-8859-1', 'UTF-8') : NULL;

                $cod_mensaje= abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user,$cod_dictamenFK);
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'subirImagenMensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? mb_convert_encoding((string)($_POST['cod_mensaje']), 'ISO-8859-1', 'UTF-8') : null;
                $foto= isset($_POST['foto']) ? mb_convert_encoding((string)($_POST['foto']), 'ISO-8859-1', 'UTF-8') : null;
                $ext= isset($_POST['ext']) ? mb_convert_encoding((string)($_POST['ext']), 'ISO-8859-1', 'UTF-8') : null;
                subirImagenMensaje($cod_mensaje,$foto,$ext, 'url');
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'nuevo/editar mencion':
                $cod_mencion= isset($_POST['cod_mencion']) ? mb_convert_encoding((string)($_POST['cod_mencion']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_mensajeFK= isset($_POST['cod_mensajeFK']) ? mb_convert_encoding((string)($_POST['cod_mensajeFK']), 'ISO-8859-1', 'UTF-8') : null;
                $isLeido= isset($_POST['isLeido']) ? mb_convert_encoding((string)($_POST['isLeido']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : 'activo';

                abmMencion($cod_mencion, $cod_usuarioFK, $cod_mensajeFK, $isLeido, $estado);
                break;
            case 'buscarMasInterConsultasYContenido':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_dictamenFK= isset($_POST['cod_dictamenFK']) ? mb_convert_encoding((string)($_POST['cod_dictamenFK']), 'ISO-8859-1', 'UTF-8') : null;
                $offset= isset($_POST['offset']) ? mb_convert_encoding((string)($_POST['offset']), 'ISO-8859-1', 'UTF-8') : null;
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $fechaActual= new DateTime();
                $filtros= array(
                    "cod_interConsultaFK" => $cod_interConsulta,
                    "cod_usuarioFK" => $user,
                    'fecha_creacion' => "<= NOW()",
                    "cod_dictamenFK" => $cod_dictamenFK,
                    "sin_dictamen" => ($cod_dictamenFK == null || $cod_dictamenFK == "") ? true : NULL
                );
                $vistaTarjetas= obtenerVistaTarjetaInterConsuta($filtros, $limite, $offset);
                echo json_encode(array("1" => "exito", "2" => $vistaTarjetas));
                break;
            case 'buscarVistaAsociadoPaciente':
                $cod_cliente= isset($_POST['cod_cliente']) ? mb_convert_encoding((string)($_POST['cod_cliente']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                
                $registros= obtenerInterConsulta(array(
                    'cod_clienteFK' => $cod_cliente,
                    'ocultar_inactivos' => TRUE,
                    'id_interConsulta_distinto' => $cod_interConsulta,
                ), 0);

                $pagina= "";
                $locales= array();
                foreach ($registros as $key => $value) {
                    $locales[]= $value['nombre_local'];
                    $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding= "5"><tr onclick="obtenerDatosInterConsulta(this)">
                        <td id="td_id" style="display: none;">'.$value['cod_interConsulta'].'</td>
                        <td id="td_datos_5" style="display: none;">'.$value['nombre_persona'].'</td>
                        <td id="td_datos_11" style="display: none;">'.$value['cod_localFK'].'</td>
                        <td id="td_datos_12" style="display: none;">'.$value['nombre_local'].'</td>
                        <td id="td_datos_6" style="display: none;">'.$value['tipo'].'</td>
                        <td id="td_datos_7" style="display: none;">'.$value['cod_clienteFK'].'</td>
                        <td id="td_datos_8" style="display: none;">'.$value['fecha_creacion'].'</td>
                        <td id="td_datos_13" style="display: none;">'.$value['cantMensajes'].'</td>
                        <td id="td_datos_4" class="tdRegistroSearch" style="width: 10%;">'.$value['cod_ventaFK'].'</td>
                        <td id="td_datos_10" class="tdRegistroSearch" style="width: 40%;">'.$value['asunto'].'</td>
                        <td id="td_datos_9" class="tdRegistroSearch" style="width: 35%;">'.$value['nombre_persona_creador'].'</td>
                        <td id="td_datos_2" class="tdRegistroSearch" style="width: 15%;">'.$value['estado'].'</td>
                    </tr></table>';
                }
                
                // Formatea los locales
                $locales= array_unique($locales);
                $nombre_local= implode(" / ", $locales);

                echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $nombre_local));
                break;
            case 'solicitarAcceso':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $contenido= "El usuario $nombre_usuario solicito el acceso a esta conversacion.";
                
                $cod_mensaje= abmMensaje("", $contenido, $fechaActual->format('Y-m-d H:i:s'), $cod_interConsulta, NULL, NULL, FALSE);
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'fusionarInterConsultas':
                $cod_interConsulta= mb_convert_encoding((string)($_POST['cod_interConsulta']), "ISO-8859-1", "UTF-8");
                $cod_interConsulta_destino= mb_convert_encoding((string)($_POST['cod_interConsulta_destino']), "ISO-8859-1", "UTF-8");
                fusionarInterconsultas($cod_interConsulta, $cod_interConsulta_destino, $user);
                echo json_encode(array("1"=> "exito", "2" => $cod_interConsulta, "3" => $cod_interConsulta_destino));
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function fusionarInterconsultas($cod_interConsulta, $cod_interConsulta_destino, $cod_usuarioFK) {
        if (empty($cod_interConsulta) || empty($cod_interConsulta_destino)) {
            echo json_encode(array("1" => "error", "2" => "Campos vacios."));
            exit;
        }
        $ids_menciones= [];
        set_time_limit(300);
        
        // Obtiene la informacion de la interconsulta
        $registroInterc= obtenerInterConsulta(array(
            "cod_interConsulta" => $cod_interConsulta_destino
        ), 0)[0];
        
        // Obtiene las menciones de la interconsulta origen
        $fechaActual= new DateTime();
        $registrosMens= obtenerMensaje(array(
            'cod_interConsultaFK' => $cod_interConsulta,
        ), 0);
        $valueMens= end($registrosMens);
        $mencionesTemp= array();
        foreach ($registrosMens as $valueMens) {
            $registrosMenc= obtenerMencion(array(
                "cod_mensajeFK" => $valueMens['cod_mensaje'],
            ), 0);

            foreach ($registrosMenc as $value) {
                $mencionesTemp[$value['cod_usuarioFK']] = $value['estado'];
            }
        }
        foreach ($mencionesTemp as $key => $value) {
            if ($value != 'inactivo') {
                $ids_menciones[] = $key;
            }
        }
        $ids_menciones = array_unique($ids_menciones);
        
        // Genera un mensaje del sistema
        $fechaActual= new Datetime();
        $cod_mensaje= abmMensaje("", "esta y la interconsulta ".$registroInterc['asunto']." fueron unidas por @{$cod_usuarioFK}", $fechaActual->format('Y-m-d H:i:s'), $cod_interConsulta_destino, NULL, NULL, FALSE);

        // Pasa todos los mensajes al interconsulta destino
        foreach ($registrosMens as $mensj) {
            abmMensaje($mensj['cod_mensaje'], NULL, NULL, $cod_interConsulta_destino, NULL, NULL);
        }
        
        // Agrega las menciones faltantes al mensaje del sistema
        foreach ($ids_menciones as $value) {
            if (empty($value)) {continue;}
            abmMencion(null, $value, $cod_mensaje, 0, 'activo');
        }

        // Agrega las menciones a los mensajes futuros del sistema
        $registrosMens= obtenerMensaje(array(
            'cod_interConsultaFK' => $cod_interConsulta_destino,
            'fecha_creacion' => "> NOW()",
        ), 0);

        foreach ($registrosMens as $mensj) {
            $registroMenc = obtenerMencion(array(
                "cod_mensajeFK" => $mensj['cod_mensaje'],
            ), 0);
            
            // Construir array de usuarios mencionados existentes para evitar consultas frecuentes
            $usuariosMencionadosExistentes = array();
            foreach ($registroMenc as $mencion) {
                $usuariosMencionadosExistentes[] = $mencion['cod_usuarioFK'];
            }
            
            foreach ($ids_menciones as $value) {
                if (empty($value)) {continue;}
                
                // Verificar si la mención ya existe en el array de menciones existentes
                if (!in_array($value, $usuariosMencionadosExistentes)) {
                    abmMencion(null, $value, $mensj['cod_mensaje'], 0, 'activo');
                }
            }
        }

        // Actualizar el cod_interconsulta de los gastos
        $sql= "UPDATE gastos SET cod_interConsultaFK= ? WHERE cod_interConsultaFK= ?";
        $mysqli = conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii',$registroInterc['cod_interConsulta'], $cod_interConsulta);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        // Actualiza el estado de la interconsulta
        abmInterConsulta($cod_interConsulta, $registroInterc['asunto'], NULL, 'inactivo', NULL, NULL, NULL, $cod_usuarioFK, $registroInterc['cod_localFK'], NULL);
    }

  function escaparHtmlInterconsulta($texto) {
    return htmlspecialchars((string)(isset($texto) ? $texto : ''), ENT_QUOTES, 'UTF-8');
}

function convertirTextoDocumentoInterconsulta($texto) {
    $texto = trim((string)(isset($texto) ? $texto : ''));
    if ($texto === '') {
        return '<span style="color: #8b7b5c;">Sin contenido cargado.</span>';
    }

    return nl2br(escaparHtmlInterconsulta($texto), false);
}

    function obtenerBotonMasMensajesInterconsulta($offset, $cod_dictamen = '') {
        $cod_dictamen = $cod_dictamen === null ? '' : (string)$cod_dictamen;
        $codDictamenJs = htmlspecialchars(json_encode($cod_dictamen), ENT_QUOTES, 'UTF-8');

        return '<div data-role="dictamen-boton-mas" style="width: 100%; display: flex; justify-content: center; margin-bottom: 12px;">'
            . '<button class="btn btn-success" onclick="verMasMensajesInterconsulta('.intval($offset).', '.$codDictamenJs.')">Ver más mensajes...</button>'
            . '</div>';
    }

    function obtenerVistaDocumentoDictamenInterconsulta($dictamen, $interconsulta, $idDocumento, $nombreAutor, $fechaDictamen, $estadoDictamen, $estadoColor) {
        $estadoFondo = 'rgba(184, 134, 11, 0.14)';
        $estado = obtenerEstadoVisualDictamen($dictamen);

		if (in_array($estado, array('emitido', 'aprobado', 'autorizado'), true)) {
			$estadoFondo = 'rgba(47, 111, 62, 0.14)';
		} else if (in_array($estado, array('ejecutado', 'finalizado', 'complementaria'), true)) {
			$estadoFondo = 'rgba(31, 78, 121, 0.14)';
		} else if (in_array($estado, array('inactivo', 'rechazado', 'anulado', 'rectificado'), true)) {
			$estadoFondo = 'rgba(122, 122, 122, 0.16)';
		} else {
			$estadoFondo = '';
		}

        $asunto = escaparHtmlInterconsulta($dictamen['asunto']);
        $contenido = convertirTextoDocumentoInterconsulta($dictamen['dictamen']);
        $idDocumento = escaparHtmlInterconsulta($idDocumento);
        $nombreAutor = escaparHtmlInterconsulta($nombreAutor);
        $fechaDictamen = escaparHtmlInterconsulta($fechaDictamen);
        $estadoDictamen = escaparHtmlInterconsulta($estadoDictamen);
        $codInterconsulta = escaparHtmlInterconsulta($interconsulta['cod_interConsulta']);
        $nombreAutoriza = !empty($dictamen['nombre_persona_autoriz']) ? escaparHtmlInterconsulta($dictamen['nombre_persona_autoriz']) : '';
        $nombreEjecuta = !empty($dictamen['nombre_persona_ejecut']) ? escaparHtmlInterconsulta($dictamen['nombre_persona_ejecut']) : '';
        $fechaAutoriz = !empty($dictamen['fecha_autoriz']) ? escaparHtmlInterconsulta(date('d/m/Y H:i', strtotime($dictamen['fecha_autoriz']))) : '';
        $fechaEjecut = !empty($dictamen['fecha_ejecut']) ? escaparHtmlInterconsulta(date('d/m/Y H:i', strtotime($dictamen['fecha_ejecut']))) : '';

        $metaExtra = '';
        if ($nombreAutoriza !== '') {
            $metaExtra .= '<div><strong>Autorizado por:</strong> '.$nombreAutoriza.'</div>';
            $metaExtra .= '<div><strong>Fecha autorizado:</strong> '.$fechaAutoriz.'</div>';
        }
        if ($nombreEjecuta !== '') {
            $metaExtra .= '<div><strong>Ejecución registrada:</strong> '.$nombreEjecuta.'</div>';
            $metaExtra .= '<div><strong>Fecha ejecutado:</strong> '.$fechaEjecut.'</div>';
        }

        return '<div class="interc-dictamen-document-shell">
            <div class="interc-dictamen-document">
                <div class="interc-dictamen-band">
                    <span>Resolución administrativa</span>
                    <span>Hilo #'.$codInterconsulta.'</span>
                </div>
                <div class="interc-dictamen-kicker">Registro oficial del dictamen</div>
                <h3 class="interc-dictamen-doc-title">'.$asunto.'</h3>
                <div class="interc-dictamen-doc-subtitle">
                    <div><strong>Documento:</strong> '.$idDocumento.'</div>
                    <div><strong>Emitido:</strong> '.$fechaDictamen.'</div>
                    <div><strong>Responsable:</strong> '.$nombreAutor.'</div>
                    '.$metaExtra.'
                </div>
                <div class="interc-dictamen-doc-content">'.$contenido.'</div>
                <div class="interc-dictamen-doc-footer">
                    <div class="interc-dictamen-doc-status" style="color: '.$estadoColor.'; background-color: '.$estadoFondo.';">'.$estadoDictamen.'</div>
                </div>
            </div>
        </div>';
    }

    function obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario) {
        $pagina = "";
        $limiteMensajes= 5;
        $totalCantMensaje= 0;
        
        // Se obtienen las interconsultas
        $registrosInterc= obtenerInterConsulta(array(
            "cod_ventaFK" => $filtros['cod_ventaFK'],
            "cod_usuarioFK" => $filtros['cod_usuarioFK'],
            "cod_interConsulta" => $filtros['cod_interConsulta'],
        ), $limite);

        if (count($registrosInterc) == 0) {
            echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
            return false;
        }

        foreach ($registrosInterc as $valueInter) {
            // Se crea el encabezado
            $pagina.= '<div>';
            
            $mencionesElemento= "";
            $menciones= array();
            
            // Se obtienen todas las menciones
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'fecha_creacion' => "<= NOW()",
                'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
            ));

            if (count($registrosMens) > 0) {
                $ultimoMensaje= end($registrosMens);
    
                $paginaMensajes= "";
                // Obtiene todas las menciones
                $registrosMenc= obtenerMencion(array(
                    'cod_mensajeFK' => $ultimoMensaje['cod_mensaje'],
                    'estado' => 'activo'
                ), 0);
    
                foreach ($registrosMenc as $valueMenc) {
                    if ($valueMenc['estado'] == 'activo' && !in_array($valueMenc['nombre_persona'], $menciones)) {
                        $mencionesElemento .= '<li class="interconsulta-participant-item" style="display: '.(($valueInter['cod_usuarioFK_create'] != $valueMenc['cod_usuarioFK']) ? "flex" : "none").';">
                            <div class="interconsulta-participant-info">
                                <span class="interconsulta-participant-avatar"><i class="fa-solid fa-user"></i></span>
                                <span>'.$valueMenc['nombre_persona'].'</span>'.
                                (($valueMenc['isLeido'] == 1) ? '<i class="fa-solid fa-check-double interconsulta-participant-check" title="Participante activo"></i>' : '').
                            '</div>
                            <button type="button" class="interconsulta-participant-remove" title="Quitar participante" onclick="eliminarMencionMensaje('.$valueMenc["cod_mencion"].')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </li>';
                        $menciones[] = $valueMenc['nombre_persona'];
                    }
                }
            }

            // Se obtienen los mensajes sin dictamenes
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'fecha_creacion' => "<= NOW()",
                'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                'sin_dictamen' => TRUE
            ));

            // Se obtienen primero los dictamenes relacionados a esta interconsulta
            $registros_dictamenes= obtenerDictamen(array('cod_interConsultaFK' => $valueInter['cod_interConsulta']), 0);
            $paginaOpciones= "";
            foreach ($registros_dictamenes as $dictamen) {
                $paginaOpciones .= '<option value="'.$dictamen['id'].'">'.$dictamen['asunto'].'</option>';
                $nombreAutor = !empty($dictamen['nombre_persona_create']) ? $dictamen['nombre_persona_create'] : 'Sin autor';
                $fechaDictamen = !empty($dictamen['fecha_create']) ? date('d/m/Y H:i', strtotime($dictamen['fecha_create'])) : '';
                $fechaId = !empty($dictamen['fecha_create']) ? date('Y', strtotime($dictamen['fecha_create'])) : date('Y');
                $idDocumento = 'RES-'.$fechaId.'-'.$valueInter['cod_interConsulta'].'-'.str_pad($dictamen['id'], 2, '0', STR_PAD_LEFT);
                $estadoDictamenValor = obtenerEstadoVisualDictamen($dictamen);
                $estadoDictamen = obtenerEtiquetaEstadoDictamen($estadoDictamenValor);
                $estadoColor = obtenerColorEstadoDictamen($estadoDictamenValor);
                $urlAutor = !empty($dictamen['url_create']) ? $dictamen['url_create'] : '/GoodVentaAsisCap/iconos/user.png';
                $estadoDictamenClase = preg_replace('/[^a-z0-9_-]/', '', strtolower($estadoDictamenValor));
                $registrosMens2= obtenerMensaje(array(
                    'fecha_creacion' => "<= NOW()",
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_dictamenFK' => $dictamen['id']
                ));
                $cantidadMensajesDictamen = count($registrosMens2);

                $pagina .= '<section class="interc-dictamen-card interconsulta-resolution-card">
                    <div class="card-header interc-dictamen-toggle" type="button" onClick="mostrarItems(\'contenedorMensajesInterConsulta'.$dictamen['id'].'\')" style="
                        background: linear-gradient(180deg, #f6f0df 0%, #efe5cf 100%);
                        border: 1px solid #d8ccb2;
                        border-radius: 12px 12px 0 0;
                        padding: 0;
                        overflow: hidden;
                        cursor: pointer;
                        display: block;
                        box-shadow: inset 0 1px 0 rgba(255,255,255,0.65);
                    ">
                        <div style="
                            display: flex;
                            align-items: stretch;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            border-bottom: 1px solid #d9ceb6;
                            background: rgba(255,255,255,0.25);
                        ">
                            <div style="
                                display: flex;
                                align-items: center;
                                gap: 14px;
                                padding: 14px 18px;
                                flex: 1 1 360px;
                            ">
                                <div style="
                                    font-size: 18px;
                                    font-weight: 800;
                                    color: #1f5f96;
                                    letter-spacing: 0.4px;
                                    white-space: nowrap;
                                ">Resoluci&oacute;n administrativa &middot; NRO: '.$dictamen['id'].'</div>
                                <div style="
                                    width: 1px;
                                    align-self: stretch;
                                    background-color: #d7ccb7;
                                "></div>
                                <div style="
                                    font-size: 16px;
                                    font-weight: 700;
                                    color: #4d4a43;
                                    letter-spacing: 0.2px;
                                ">Documento: '.$idDocumento.'</div>
                            </div>
                            <div style="
                                display: flex;
                                align-items: center;
                                text-align: end;
                                gap: 12px;
                                padding: 5px 18px;
                                border-left: 1px solid #d9ceb6;
                                justify-content: flex-end;
                                background: rgba(255,255,255,0.18);
                            ">
                                <img src="'.$urlAutor.'" alt="Foto de '.$nombreAutor.'" style="
                                    width: 44px;
                                    height: 44px;
                                    border-radius: 8px;
                                    object-fit: cover;
                                    background: #d8d8d8;
                                    border: 1px solid rgba(0,0,0,0.08);
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
                                ">
                                <div>
                                    <div style="
                                        font-size: 16px;
                                        font-weight: 700;
                                        color: #2b2b2b;
                                        line-height: 1.1;
                                        width: fit-content;
                                    ">'.$nombreAutor.'</div>
                                    <div style="
                                        font-size: 12px;
                                        color: #6a6358;
                                        margin-top: 4px;
                                        width: fit-content;
                                    ">'.$fechaDictamen.'</div>
                                </div>
                            </div>
                        </div>
                        <div style="
                            padding: 0px 5px 0px 5px;
                            color: #2d2a24;
                            text-align: start;
                            background: linear-gradient(180deg, rgba(255,255,255,0.24) 0%, rgba(255,255,255,0.08) 100%);
                        ">
                            <div style="
                                font-size: 15px;
                                margin-bottom: 6px;
                                line-height: 1.35;
                            ">
                                <span style="font-weight: 800; color: #2f2a20;">Asunto:</span>
                                <span>'.$dictamen['asunto'].'</span>
                            </div>
                            <div style="
                                font-size: 15px;
                                line-height: 1.4;
                            ">
                                <span style="font-weight: 800; color: #2f2a20;">Resoluci&oacute;n emitida:</span>
                                <span>'.$dictamen['dictamen'].'</span>
                            </div>
                            <div class="interc-dictamen-origin">
                                <span>Basado en: '.$cantidadMensajesDictamen.' mensaje'.($cantidadMensajesDictamen == 1 ? '' : 's').' del hilo</span>
                            </div>
                        </div>
                        <div class="interc-dictamen-actions">
                            <span class="interc-dictamen-status-badge interc-dictamen-status-badge--'.$estadoDictamenClase.'" title="Estado administrativo del dictamen">Estado: '.$estadoDictamen.'</span>
                            <button type="button" class="interc-dictamen-action-btn" onclick="event.stopPropagation();mostrarItems(\'contenedorMensajesInterConsulta'.$dictamen['id'].'\');">Ver resoluci&oacute;n</button>
                            <button type="button" class="interc-dictamen-action-btn" onclick="event.stopPropagation();mostrarItems(\'contenedorMensajesInterConsulta'.$dictamen['id'].'\');">Ver mensajes relacionados</button>
                        </div>
                    </div>';
                
                $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_usuarioFK' => $filtros['cod_usuarioFK'],
                    'cod_dictamenFK' => $dictamen['id']
                ), $limiteMensajes, 0);

                $registrosMens2= obtenerMensaje(array(
                    'fecha_creacion' => "<= NOW()",
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_dictamenFK' => $dictamen['id']
                ));

                $pagina .= '<div id="contenedorMensajesInterConsulta'.$dictamen['id'].'" class="collapse show interc-dictamen-body" data-total-mensajes="'.count($registrosMens2).'">
                    <div class="interc-dictamen-layout">
                        <div class="interc-dictamen-chat-pane" data-role="dictamen-chat-panel" style="height: 500px;overflow-y: auto;">';
                
                if (count($registrosMens2) > $limiteMensajes) {
                    $pagina .= obtenerBotonMasMensajesInterconsulta($limiteMensajes, $dictamen['id']);
                }

                $pagina .= '<div data-role="dictamen-mensajes">'.$paginaMensajes.'</div>
                        </div>
                        <div class="interc-dictamen-preview-pane">'
                            .obtenerVistaDocumentoDictamenInterconsulta($dictamen, $valueInter, $idDocumento, $nombreAutor, $fechaDictamen, $estadoDictamen, $estadoColor).
                        '</div>
                    </div>
                </div></section>';
            }

            $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_usuarioFK' => $filtros['cod_usuarioFK'],
                    'sin_dictamen' => TRUE
                ), $limiteMensajes, 0);

            // Obtiene los mensajes programados
            $registrosMens2= obtenerMensaje(array(
                'fecha_creacion' => "> NOW()",
                "cod_interConsultaFK" => $valueInter["cod_interConsulta"],
                "estado" => 'activo'
            ));

            foreach ($registrosMens2 as $valueMens) {
                $paginaMensajes .= '<div class="interconsulta-message-row interconsulta-message-row--system">
                    <div class="interconsulta-system-event">
                        <span style="display: none;">'.$valueMens['cod_mensaje'].'</span>
                        <i class="fa-solid fa-clock"></i>
                        <p>Mensaje programado '.($valueMens['nombre_persona'] ? 'de '.$valueMens['nombre_persona'] : 'por el sistema').' para el '.$valueMens['fecha_creacion'].'</p>
                    </div>
                </div>';
            }
            
            $pagina .= '<div id="contenedorMensajesInterConsulta" class="collapse show" data-total-mensajes="'.count($registrosMens).'">
                <div data-role="dictamen-chat-panel">';

            if (count($registrosMens) > $limiteMensajes) {
                $pagina .= obtenerBotonMasMensajesInterconsulta($limiteMensajes, "");
            }
            $pagina .= $paginaMensajes. '</div></div>';

            // Obtiene la cantidad total de mensajes
            $totalCantMensaje2= obtenerMensaje(array(
                'cod_interConsultaFK' => $valueInter['cod_interConsulta']
            ));
            $totalCantMensaje += count($totalCantMensaje2);
        }   

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $filtros['cod_ventaFK'], "4" => $valueInter, "5" => $totalCantMensaje, "6" => $mencionesElemento, "7" => $paginaOpciones));
    }

    function obtenerVistaFlujoGastosInterConsulta($cod_interConsulta) {
        if (empty($cod_interConsulta)) {
            return '<div class="text-secondary" style="padding: 8px;">Sin gastos asociados.</div>';
        }

        $gastosElemento= "";
        $registrosGastos = buscarGasto("","","",'','','','','','true','', $cod_interConsulta, '', '','NULL','');
        $colorFondo= "";
        foreach ($registrosGastos as $key => $gast) {
            $gasto= $gast;
            if (!empty($registrosGastos[$key]['mostrado'])) {continue;}

            $registrosGastos[$key]['mostrado'] = true;

            if ($gasto['modalidad'] == 'credito') {
                // Evita mostrar cuotas repetidas y conserva el gasto principal.
                $gastos_asociados= obtenerGastosAsociados($gasto["idgastos"]);
                if (empty($gastos_asociados)) {continue;}
                
                $colorFondo= "background-color: #6c757d";
                foreach ($gastos_asociados as $value) {
                    foreach ($registrosGastos as &$value2) {
                        if ($value['idgastos'] == $value2['idgastos']) {
                            $value2['mostrado'] = true;
                        }
                    }
                    if (($value['estado'] != 'Activo' && $value['estado'] != 'Rechazado')) {
                        $colorFondo= '';
                    }
                    unset($value2);
                }

                $gasto= $gastos_asociados[0];
            } else {
                $colorFondo= "background-color: #6c757d";
            }

            $gastosElemento.= '<button class="btn-menu-extracto w-100" style="'.$colorFondo.'" data-id="'.$gasto['idgastos'].'" onclick="mostrarExtractoGasto('.$gasto['idgastos'].')">'.$gasto['descripcion'].'</button>';
        }

        if (empty($gastosElemento)) {
            return '<div class="text-secondary" style="padding: 8px;">Sin gastos asociados.</div>';
        }
        
        return $gastosElemento;
    }

    function buscarVistaPacienteConInterConsulta($filtros= array(), $limite= 0) {
        $registros= buscarPacienteConInterConsulta($filtros, $limite);

        $pagina= "";
        foreach ($registros as $key => $value) {
            $interConsultas= "";
            
            // Obtiene las interConsultas asociadas a este paciente
            $registrosInterconsulta= obtenerInterConsulta(array(
                "cod_ventaFK" => $value['cod_cliente']
            ), 5);

            $cant_mensajes_no_leidos= 0;
            foreach ($registrosInterconsulta as $valueInter) {
                $color="#8bc34a;";
                if ($color == "#8bc34a;") {
                    if ($valueInter['estado'] == 'proceso') {
                        $color=" #e53935; ";	
                    } else if ($valueInter['estado'] == 'pendiente') {
                        $color=" #e1c247; ";
                    }
                }
                $interConsultas .= '<li style="
                    background-color:'.$color.';
                    margin-bottom:4px;
                    padding:5px 10px;
                    border-radius:4px;
                    font-size:13px;
                ">'.$valueInter['asunto'].'</li>';

                $cant_mensajes_no_leidos += intval($valueInter['cantMensajesNoLeidos']);
            }

            // Coloca un elemento con la cantidad de mensajes pendientes
            $indicadorElemento= "" ;
            if ($cant_mensajes_no_leidos > 0) {
                $indicadorElemento= '<div style="
                    position: absolute;
                    top: -10px;
                    right: -10px;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    background:  #e53935;  /* Rojo */
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 14px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                ">
                    '.$cant_mensajes_no_leidos.'
                </div>';
                $indicadorElemento= "" ;
            }

            $pagina.= "<div class='tarjeta-paciente' onclick='obtenerDatosInterConsulta(this)' style='
                position: relative; /* Necesario para posicionar el círculo */
                border: 1px solid #ddd;
                border-radius: 8px;
                margin: 10px 0;
                height: auto;
                padding: 15px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                font-family: Arial, sans-serif;
                '>
                $indicadorElemento
                <h3 style='
                    margin-top:0;
                    margin-bottom:10px;
                    font-size: 16px;
                    color: #333;
                '>DATOS PACIENTE</h3>
                
                <p><strong>Nombre:</strong> ".$value['paciente']."</p>
                <p><strong>CI:</strong> ".$value['ci_cliente']."</p>

                <div style='
                    margin-top: 10px;
                    padding-top: 10px;
                '>
                    <strong>Ultimas conversaciones:</strong>
                    $interConsultas
                </div>
                <div style='display: none;'>
                    <span id='td_datos_1'>".$value['cod_cliente']."</span>
                    <span id='td_datos_2'>".$value['paciente']."</span>
                </div>
                </div>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina));
    }
    function buscarPacienteConInterConsulta($filtros= array(), $limite= 0) {
        $mysqli=conectar_al_servidor();

        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            
            $sqlFiltro .= " AND ";

            switch ($key) {
                case 'paciente':
		            $sqlFiltro=" concat(cl.ci_cliente,' ',cl.rut_cliente ,' ',p.nombre_persona )   like '%".$value."%' ";
                    break;
                case 'estado':
                    $sqlFiltro .= "cl.estado = '$value'";
                    break;
                case 'cod_usuarioFK':
                    $sqlFiltro .= "EXISTS (SELECT 1 FROM interconsulta ic INNER JOIN mensaje mj ON ic.cod_interConsulta = mj.cod_interConsultaFK
                                    INNER JOIN menciones mc ON mc.cod_mensajeFK = mj.cod_mensaje
                                    WHERE ic.cod_ventaFK = cl.cod_cliente AND mc.cod_usuarioFK = $value)";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "cl.$key = $value";
                    } else {
                        $sqlFiltro .= "cl.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "Select  p.nombre_persona as paciente,cl.ci_cliente,cl.cod_cliente
		from cliente cl
		inner join persona p on cod_cliente=cod_persona
		  where cl.estado = 'Activo' $sqlFiltro $limite";

        $stmt = $mysqli->prepare($sql);
        if ( ! $stmt->execute()) {
            echo "Error";
            exit;
        }
 
	    $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function obtenerVistaTarjetaInterConsuta($filtros= array(), $limite= 0, $offset= 0) {
        $paginaMensajes= "";

        // Reconstruye el limite si es necesario
        if ($offset != 0){
            $limite= "$limite OFFSET $offset";
        }

        // Obtiene todos los mensajes de la interConsulta
        $fechaActual= new DateTime();
        $regMensaje= obtenerMensaje(array(
                'fecha_creacion' => "<= NOW()",
                "cod_interConsultaFK" => $filtros["cod_interConsultaFK"],
                "cod_dictamenFK" => isset($filtros['cod_dictamenFK']) ? $filtros['cod_dictamenFK'] : NULL,
                "sin_dictamen" => isset($filtros['sin_dictamen']) ? $filtros['sin_dictamen'] : NULL,
            ), $limite);
        foreach ($regMensaje as $key => $valueMens) {
            $posicion= 'flex-start';
            $colorTarjeta="#e53935";
            
            if ($filtros['cod_usuarioFK'] == $valueMens['cod_usuarioFK']) {
                $posicion= 'flex-end';
                $colorTarjeta="#8bc34a";
            }

            $contenidoMensaje= $valueMens['contenido'];
            // Transforma las menciones a elementos
            $usuarios= buscarUsuarios();
            foreach ($usuarios as $valueUsu) {
                $contenidoMensaje= str_replace(
                    '@{'.$valueUsu['cod_usuario'].'}', 
                    '<b class="menciones-mensaje" id="'.$valueUsu['cod_usuario'].'" title="Mencion a '.$valueUsu['nombre_persona'].'">@'.$valueUsu['nombre_persona'].'</b>', 
                    $contenidoMensaje
                );
            }
            $contenidoMensaje = nl2br($contenidoMensaje, false);

            $miniatura_imagen= "";
            if ($valueMens['url']) {
                $urlAdjunto= escaparHtmlInterconsulta($valueMens['url']);
                $extensionAdjunto= escaparHtmlInterconsulta(obtenerExtensionUrlInterconsulta($valueMens['url']));
                $esImagenAdjunto= esUrlImagenInterconsulta($valueMens['url']);
                $urlMiniaturaAdjunto= $esImagenAdjunto ? $urlAdjunto : "/GoodVentaAsisCap/iconos/informedevolucion.png";
                $claseDocumentoAdjunto= $esImagenAdjunto ? "" : " imgFotoProductoDocumento";
                $miniatura_imagen= '<button type="button" class="interconsulta-message-attachment" onclick="vercerrarcargadefotos(\'fotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'\', false)" title="Ver adjunto">
                    <span id="imgfotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'" class="imgFotoProducto'.$claseDocumentoAdjunto.'" data-adjunto-url="'.$urlAdjunto.'" data-adjunto-ext="'.$extensionAdjunto.'" style="background-image: url('.$urlMiniaturaAdjunto.');"></span>
                    <small>Adjunto</small>
                </button>';
            }
            
            $contenidoPlano= mb_strtolower(strip_tags($contenidoMensaje));
            $esEventoSistema= (!$valueMens['cod_usuarioFK'] || $valueMens['cod_usuarioFK'] == "NULL")
                || strpos($contenidoPlano, ' modifico') !== false
                || strpos($contenidoPlano, 'fueron unidas') !== false
                || strpos($contenidoPlano, 'solicito el acceso') !== false;

            if ($esEventoSistema) {
                $paginaMensajes .= '<div class="interconsulta-message-row interconsulta-message-row--system">
                    <div class="interconsulta-system-event">
                        <i class="fa-solid fa-circle-info"></i>
                        <p>'.$contenidoMensaje.'</p>
                        <time>'.$valueMens['fecha_creacion'].'</time>
                    </div>
                </div>';
            } else {
                $claseMensajePropio= ($posicion == 'flex-end') ? ' interconsulta-message-row--own' : '';
                $paginaMensajes .= '<div class="interconsulta-message-row'.$claseMensajePropio.'">
                    <article class="interconsulta-message-card">
                        <header class="interconsulta-message-header">
                            <div class="interconsulta-message-author">
                                <img src="'.($valueMens['url_usuario'] == null ? "/GoodVentaAsisCap/iconos/user.png" : $valueMens['url_usuario']).'" alt="Foto de '.$valueMens['nombre_persona'].'">
                                <div>
                                    <strong>'.$valueMens['nombre_persona'].'</strong>
                                    <span>Participante del hilo</span>
                                </div>
                            </div>
                            <time>'.$valueMens['fecha_creacion'].'</time>
                        </header>
                        <div class="interconsulta-message-body">
                            '.$miniatura_imagen.'
                            <p>'.$contenidoMensaje.'</p>
                        </div>
                    </article>
                </div>';
            }
        }

        return $paginaMensajes;
    }

    function obtenerVistaInterConsulta($filtros= array(), $limite= 0) {
        $cantRegistros= obtenerInterConsulta($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerInterConsulta($filtros, $limite);

        $pagina= '';
        $datalist= '';
        $cant_mensajes_no_leidos= 0;
        $cant_interConsulta_abierto= 0;
        $styleName="tableRegistroSearch";
        foreach ($registros as $value) {
            if ($value['estado'] == 'pendiente' || $value['estado'] == 'proceso') {
                $cant_interConsulta_abierto++;
            }

            $styleName=CargarStyleTable($styleName);
            $style= "";

            $styleInterno= "";
            $colorText= "";
            if ($value['tipo'] == 'interno') {
                $styleInterno= "color: white; background-color: #585f08;";
            }
            if ($value['cantAsociadoGastos'] > 0) {
                $styleInterno= "color: white; background-color: #08525f;";
                $colorText= "color: #2ea3c0;";
            }
            // Marca la interconsulta si tiene gastos pendientes
            if (intval($value['cantGastosPendientes']) > 0) {
                $styleInterno= "color: white; background-color: #762424;";
            }
            
            $cantMensajesNoLeidosOtrosUsuarios= "";
            if (intval($value['cantMensajesNoLeidosOtrosUsuarios']) > 0) {
                $cantMensajesNoLeidosOtrosUsuarios= " (".$value['cantMensajesNoLeidosOtrosUsuarios'].")";
            }

            $esHiloVinculado = intval($value['cantAsociadoGastos']) > 0;
            $esHiloPendienteRespuesta = intval($value['cantMensajesNoLeidos']) > 0;
            $clasesTablaHilo = 'tableRegistroSearch2 interconsulta-thread-row'
                .($esHiloPendienteRespuesta ? ' interconsulta-thread-row--pending' : '')
                .($esHiloVinculado ? ' interconsulta-thread-row--linked' : '');
            $claseHiloVinculado = ' class="interconsulta-subject-text'.($esHiloVinculado ? ' interconsulta-linked-subject' : '').'"';
            $tituloHiloVinculado = $esHiloVinculado ? ' title="Hilo vinculado. Haga clic para ver la referencia asociada."' : '';
            $iconoHiloVinculado = $esHiloVinculado ? ' <i class="fa-solid fa-link interconsulta-linked-icon" title="Hilo vinculado. Haga clic para ver la referencia asociada." aria-hidden="true"></i>' : '';
            $contenidoAsunto = $value['asunto']
                .$iconoHiloVinculado
                .$cantMensajesNoLeidosOtrosUsuarios;
            if ($esHiloPendienteRespuesta) {
                $cant_mensajes_no_leidos += intval($value['cantMensajesNoLeidos']);
            }

            if ($value["cantMensajesProgramados"]) {
                // Obtiene los mensajes programados
                $registrosMens= obtenerMensaje(array(
                    'fecha_creacion' => "> NOW()",
                    "cod_interConsultaFK" => $value["cod_interConsulta"],
                ));
                foreach ($registrosMens as $valueMens) {
                    if ($valueMens['estado'] == 'activo') {
                        $fechaMensaje = new DateTime(substr($valueMens['fecha_creacion'], 0, 10));
                        $fechaActual = new DateTime();
                        $diasRestantes = $fechaMensaje->diff($fechaActual->setTime(0, 0, 0));
                        $contenidoAsunto .= '<i class="fa-solid fa-business-time" style="padding-left: 5px;font-size: 9pt;"></i>('.$diasRestantes->format('%a').') ';
                    }
                }
            }
            $contenidoAsuntoPendienteInicio = $esHiloPendienteRespuesta ? '<b>' : '';
            $contenidoAsuntoPendienteFin = $esHiloPendienteRespuesta ? '</b>' : '';
            $badgePendienteRespuesta = $esHiloPendienteRespuesta ? '<span class="interconsulta-pending-badge" title="Hilo pendiente de respuesta">Sin responder</span>' : '';
            $tooltipFila = $esHiloPendienteRespuesta ? ' title="Hilo pendiente de respuesta"' : '';
            $formatAsunto= '<div class="interconsulta-subject-wrap">'
                .'<p'.$claseHiloVinculado.$tituloHiloVinculado.' style="'.$colorText.'font-size: 9pt;">'
                .$contenidoAsuntoPendienteInicio
                .$contenidoAsunto
                .$contenidoAsuntoPendienteFin
                .'</p>'
                .$badgePendienteRespuesta
                .'</div>';
            
            $pagina .= '<table class="'.$clasesTablaHilo.'" border="1" cellspacing="1" cellpadding="1">
                <tr onclick="obtenerDatosInterConsulta(this)"'.$tooltipFila.'>
                    <td id="td_id" style="width: 5%;'.$styleInterno.'">'.$value['cod_interConsulta'].'</td>
                    <td id="td_datos_1" style="width: 25%;'.$style.'"><div>'.$formatAsunto.'</div></td>
                    <td id="td_datos_4" style="display: none;'.$style.'">'.$value['cod_ventaFK'].'</td>
                    <td id="td_datos_5" style="width: 15%;'.$style.'">'.$value['nombre_persona'].'</td>
                    <td id="td_datos_11" style="display: none;'.$style.'">'.$value['cod_localFK'].'</td>
                    <td id="td_datos_12" style="width: 10%;'.$style.'">'.$value['nombre_local'].'</td>
                    <td id="td_datos_2" style="width: 10%;'.$style.'">'.$value['estado'].'</td>
                    <td id="td_datos_6" style="width: 10%;'.$style.'">'.$value['tipo'].'</td>
                    <td id="td_datos_7" style="display: none;'.$style.'">'.$value['cod_clienteFK'].'</td>
                    <td id="td_datos_8" style="width: 10%;'.$style.'">'.$value['fecha_creacion'].'</td>
                    <td id="td_datos_9" style="width: 15%;'.$style.'">'.$value['nombre_persona_creador'].'</td>
                    <td id="td_datos_10" style="display: none;'.$style.'">'.$value['asunto'].'</td>
                    <td id="td_datos_13" style="display: none;'.$style.'">'.$value['cantMensajes'].'</td>
                    <td id="td_datos_14" style="display: none;'.$style.'">'.$value['cantMensajesNoLeidos'].'</td>
                    <td id="td_datos_15" style="display: none;'.$style.'">'.$value['monto_limite'].'</td>
                    <td id="td_datos_16" style="display: none;'.$style.'">'.$value['observacion'].'</td>
                </tr>
            </table>';

            $datalist .= '<option data-id="'.$value['cod_interConsulta'].'" value="'.$value['asunto'].'">';
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros, "6" => $cant_mensajes_no_leidos, "7" => $cant_interConsulta_abierto, "8" => $datalist));
    }

    function obtenerVistaMensaje($filtros= [], $limite= 0) {
        $cantRegistros= obtenerMensaje($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerMensaje($filtros, $limite);

        $pagina= '';
        foreach ($registros as $value) {
            $pagina .= '<div class="card my-3" style="border-left: 5px solid #ff5722;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span></span>
                    <small class="text-secondary">'. $value['fecha_creacion'] .'</small>
                </div>
                <div class="card-body">
                    <h5 class="card-title">'. $value['nombre_persona'] .'</h5>
                    <p class="card-text">'. $value['contenido'] .'</p>
                </div>
                </div>';
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros));
        return;
    }

    function buscarUsuarios() {
        $mysqli=conectar_al_servidor();

        $sql= "SELECT u.*, p.nombre_persona FROM usuario u JOIN persona p ON p.cod_persona = u.cod_usuario WHERE u.estado = 'Activo'";
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function esUrlImagenInterconsulta($url) {
        $extension= strtolower(pathinfo(parse_url((string)$url, PHP_URL_PATH), PATHINFO_EXTENSION));
        return in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'));
    }

    function obtenerExtensionUrlInterconsulta($url) {
        return strtolower(pathinfo(parse_url((string)$url, PHP_URL_PATH), PATHINFO_EXTENSION));
    }

    function subirImagenMensaje($cod_mensaje, $foto, $ext, $campo) {
        $ruta= NULL;

        if (!(empty($foto) || empty($ext))) {
            if (strpos($foto, ",") !== false) {
                $foto = substr($foto, strpos($foto, ",") + 1);
            }
            $foto = base64_decode($foto);
            $id_foto = "";
            $donde = "../fotos/fotosMensaje/";
            $id_foto = $cod_mensaje;
            $id_f = subir_imagen_base64($donde, $foto, $id_foto, $ext);
            $ruta = "/GoodVentaAsisCap/fotos/fotosMensaje/" . $cod_mensaje . $id_f . "." . $ext;
        }
        
        $mysqli=conectar_al_servidor();
        $consulta="Update mensaje set $campo=? where cod_mensaje=? ";	

        $stmt = $mysqli->prepare($consulta);
        $ss="si";
        $stmt->bind_param($ss,$ruta,$cod_mensaje);
        if ( ! $stmt->execute()) {
            echo "Error";
            exit;
        }

        return $cod_mensaje;
    }

    function obtenerMencion($filtros, $limite) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'nombre_usuario':
                    $sqlFiltro .= "p.nombre_persona like '%$value%'";
                    break;
                case 'estado':
                    $sqlFiltro .= "m.estado = '$value'";
                    break;
                case 'isLeido':
                    $sqlFiltro .= "m.isLeido = $value";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "m.$key = $value";
                    } else {
                        $sqlFiltro .= "m.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT m.*, p.nombre_persona AS nombre_persona FROM menciones m JOIN usuario u ON u.cod_usuario = m.cod_usuarioFK JOIN persona p ON p.cod_persona = u.cod_usuario $sqlFiltro $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmMencion($cod_mencion, $cod_usuarioFK, $cod_mensajeFK, $isLeido, $estado) {
        $mysqli = conectar_al_servidor();
        
        // Comprueba si la mencion ya existe
        $sql= "SELECT cod_mencion FROM menciones WHERE cod_usuarioFK = ? AND cod_mensajeFK = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $cod_usuarioFK, $cod_mensajeFK);

        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $cod_mencion = $row['cod_mencion'];
        }

        if ($cod_mencion) {
            $sql= "UPDATE menciones SET isLeido= ? WHERE cod_mencion = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('si', $isLeido, $cod_mencion);

            $parametros = array();
            $atributos = "";
            $ss = "";

            if (!empty($isLeido)) {
                $atributos .= "isLeido = ?, ";
                $parametros[] = $isLeido;
                $ss .= "i";
            }
            if (!empty($estado)) {
                $atributos .= "estado = ?, ";
                $parametros[] = $estado;
                $ss .= "s";
            }
            $atributos = substr($atributos, 0, -2);

            $parametros[] = $cod_mencion;
            $ss .= "i";

            $sql= "UPDATE menciones SET $atributos WHERE cod_mencion = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        } else {
            $sql = "INSERT INTO menciones (cod_usuarioFK, cod_mensajeFK, isLeido) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iii', $cod_usuarioFK, $cod_mensajeFK, $isLeido);
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        if (empty($cod_mencion)) {
            $cod_mencion = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_mencion;
    }

    function obtenerMensaje($filtros= array(), $limite= 0) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'cod_local':
                    $sqlFiltro .= "m.cod_localFK = '$value'";
                    break;
                case 'estado':
                    $sqlFiltro .= "m.estado = '$value'";
                    break;
                case 'fecha_creacion':
                    $sqlFiltro .= "m.fecha_creacion $value";
                    break;
                case 'sin_dictamen':
                    $sqlFiltro .= "m.cod_dictamenFK IS NULL";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "m.$key = $value";
                    } else {
                        $sqlFiltro .= "m.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT * FROM (
                SELECT m.*, 
                (SELECT url FROM usuario where cod_usuario = m.cod_usuarioFK) AS url_usuario,
                (SELECT nombre_persona FROM persona where cod_persona = m.cod_usuarioFK) AS nombre_persona
                FROM mensaje m $sqlFiltro ORDER BY m.fecha_creacion DESC $limite
            ) AS subquery ORDER BY fecha_creacion ASC, cod_mensaje ASC";

        $mysqli=conectar_al_servidor();

        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        // Reemplaza el bucle while en obtenerMensaje con esto SOLO si tienes datos mixtos:
        while ($row = $result->fetch_assoc()) {
            $reg = array();
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    // Verificar si es UTF-8 válido (PHP 8 compatible)
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $reg[$key] = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                    } else {
                        $reg[$key] = $value; // Ya es UTF-8
                    }
                } else {
                    $reg[$key] = $value; // NULL, números, etc.
                }
            }
            $registros[] = $reg;
        }
        $stmt->close();
        return $registros;
    }

    function abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user,$cod_dictamenFK, $visto_creador= FALSE) {
        $mysqli = conectar_al_servidor();
        $contenidoLimpiado= "";

        if ($contenido) {
            // Convierte el contenido a html
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // evitar warnings por HTML incompleto
            $dom->loadHTML($contenido);
    
            // Obtiene todas las menciones
            $spans = $dom->getElementsByTagName('b');
            $ids_menciones = [];
            foreach ($spans as $span) {
                if ($span->hasAttribute('id')) {
                    $ids_menciones[] = $span->getAttribute('id');
                }
            }
    
            // Limpia el contenido del mensaje
            $contenidoLimpiado= "";
            $xpath = new DOMXPath($dom);
    
            // Reemplazar cada <b> con su id
            foreach ($xpath->query('//b[@id]') as $b) {
                $id = $b->getAttribute('id');
                // Crear nodo de texto con la notación @{id}
                $nuevoTexto = $dom->createTextNode("@{" . $id . "}");
                $b->parentNode->replaceChild($nuevoTexto, $b);
            }
            
            // Extrae HTML del body y preserva saltos de linea de contenteditable.
            $body = $dom->getElementsByTagName('body')->item(0);
            $contenidoHtml = $body ? $dom->saveHTML($body) : $dom->saveHTML();
            $contenidoHtml = preg_replace('/^<body[^>]*>|<\/body>$/i', '', $contenidoHtml);
            $contenidoHtml = preg_replace('/<br\\s*\\/?>/i', "\n", $contenidoHtml);
            $contenidoHtml = preg_replace('/<\\/(div|p|li|tr|h[1-6])>/i', "\n", $contenidoHtml);

            // Obtener el texto plano resultante
            $contenidoLimpiado = strip_tags($contenidoHtml);

            // Limpiar espacios y entidades
            $contenidoLimpiado = html_entity_decode($contenidoLimpiado, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $contenidoLimpiado = str_replace("\xC2\xA0", " ", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/[ \t]+\n/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/\n[ \t]+/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/\R/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/\n{3,}/", "\n\n", $contenidoLimpiado);
            $contenidoLimpiado = trim($contenidoLimpiado);
        }

        if (empty($cod_mensaje)) {
            $sql = "INSERT INTO mensaje (contenido, fecha_creacion, cod_interConsultaFK, cod_usuarioFK, cod_dictamenFK) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssiis', $contenidoLimpiado, $fecha_creacion, $cod_interConsulta, $user, $cod_dictamenFK);
        } else {
            $parametros= array();
            $atributos= "";
            $ss= "";

            // Datos a modificar
            if (!empty($contenidoLimpiado)) {
                $atributos .= ", contenido= ?";
                $ss .= "s";
                $parametros[] = $contenidoLimpiado;
            }
            if ($cod_interConsulta !== NULL) {
                $atributos .= ", cod_interConsultaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_interConsulta;
            }
            if ($cod_dictamenFK !== NULL) {
                $atributos .= ", cod_dictamenFK= ?";
                $ss .= "i";
                $parametros[] = $cod_dictamenFK;
            }
            $parametros[] = $cod_mensaje;
            $ss .= "i";

            $atributos = substr($atributos, 2);
            $sql= "UPDATE mensaje SET $atributos WHERE cod_mensaje = ?";
            $stmt = $mysqli->prepare($sql);
            
            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }
        
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }
        
        if (empty($cod_mensaje)) {
            $cod_mensaje = $stmt->insert_id;
            
            $ids_menciones[] = $user;
            // Obtiene todas las menciones anteriores asociadas a esta interconsulta
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'cod_interConsultaFK' => $cod_interConsulta,
                'fecha_creacion' => "<= NOW()",
            ), 0);
            $valueMens= end($registrosMens);
            $mencionesTemp= array();
            foreach ($registrosMens as $valueMens) {
                $registrosMenc= obtenerMencion(array(
                    "cod_mensajeFK" => $valueMens['cod_mensaje'],
                    "isLeido" => 0
                ), 0);
    
                foreach ($registrosMenc as $value) {
                    $mencionesTemp[$value['cod_usuarioFK']] = $value['estado'];
                }
            }
            foreach ($mencionesTemp as $key => $value) {
                if ($value != 'inactivo') {
                    $ids_menciones[] = $key;
                }
            }
    
            // Guarda las menciones e incluye al creador
            $ids_menciones = array_unique($ids_menciones);
            foreach ($ids_menciones as $value) {
                if (empty($value)) {continue;}
                // Marca al creador como leido solo si no es mensaje programado
                $fechaActualObj = new DateTime();
                $fechaCreacionObj = new DateTime($fecha_creacion);
                // Verifica si la diferencia en minutos es menor a 10
                $intervalo = $fechaActualObj->diff($fechaCreacionObj);
                $minutosDiferencia = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;
    
                if (($value === $user && ($minutosDiferencia < 10) || $visto_creador)) {
                    abmMencion(null, $value, $cod_mensaje, 1, 'activo');
                } else {
                    abmMencion(null, $value, $cod_mensaje, 0, 'activo');
                }
            }
            
            $stmt->close();        
        }

        return $cod_mensaje;
    }

    function obtenerInterConsulta($filtros= [], $limite= 0) {
        $sqlFiltro= "";
        $sqlFiltroMenciones= "";
        $sqlFiltroMensaje= "";
        $sqlFiltroFechaLimite= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}

            if ($key != 'fecha_limite') {
                if ($sqlFiltro == "") {
                    $sqlFiltro .= "WHERE ";
                } else {
                    $sqlFiltro .= " AND ";
                }
            }
            
            switch ($key) {
                case 'cod_usuarioFK':
                    $sqlFiltro .= "(ic.cod_usuarioFK_create = $value OR EXISTS(select cod_mencion from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mc.cod_usuarioFK = $value))";
                    $sqlFiltroMenciones = " AND mc.cod_usuarioFK = $value ";
                    break;
                case 'cod_interConsulta':
                    $sqlFiltro .= "ic.cod_interConsulta = $value";
                    break;
                case 'cod_localFK':
                    $sqlFiltro .= "ic.cod_localFK = $value";
                    break;
                case 'estado':
                    if ($value == 'abiertos') {
                        $sqlFiltro .= "(ic.estado = 'pendiente' OR ic.estado = 'proceso')";
                    } else {
                        $sqlFiltro .= "ic.estado = '$value'";
                    }
                    break;
                case 'ocultar_inactivos':
                    $sqlFiltro .= "ic.estado != 'inactivo'";
                    break;
                case 'nombre_responsable':
                    $sqlFiltro .= "(SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE '%$value%'";
                    break;
                case 'id_interConsulta_distinto':
                    $sqlFiltro .= "ic.cod_interConsulta <> $value";
                    break;
                case 'nombre_cliente':
                    $sqlFiltro .= "CONCAT(
                        (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK)
                    ) LIKE '%$value%'";
                    break;
                case 'cod_clienteFK':
                    $sqlFiltro .= "(SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK) = $value";
                    break;
                case 'usuario_vinculado':
                    $sqlFiltro .= "EXISTS(select cod_mencion from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mc.cod_usuarioFK = $value)";
                    break;
                case 'busqueda_global':
                    $valorBusqueda = addslashes($value);
                    $sqlFiltro .= "(
                        ic.cod_interConsulta LIKE '%$valorBusqueda%' OR
                        ic.asunto LIKE '%$valorBusqueda%' OR
                        ic.estado LIKE '%$valorBusqueda%' OR
                        ic.tipo LIKE '%$valorBusqueda%' OR
                        ic.fecha_creacion LIKE '%$valorBusqueda%' OR
                        (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) LIKE '%$valorBusqueda%' OR
                        (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE '%$valorBusqueda%' OR
                        CONCAT(
                            (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                            (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                            (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK)
                        ) LIKE '%$valorBusqueda%'
                    )";
                    break;
                case 'fecha_desde':
                    $sqlFiltro .= "ic.fecha_creacion >= '$value 00:00:00'";
                    break;
                case 'fecha_hasta':
                    $sqlFiltro .= "ic.fecha_creacion <= '$value 23:59:59'";
                    break;
                case 'fecha_limite':
                    if ($value === "NOW()") {
                        $sqlFiltroMenciones .= " AND mj.fecha_creacion <= NOW() ";
                        $sqlFiltroMensaje .= " AND mj.fecha_creacion > NOW() ";
                        $sqlFiltroFechaLimite .= " AND mj2.fecha_creacion <= NOW()";
                    } else {
                        $sqlFiltroMenciones .= " AND mj.fecha_creacion <= '$value' ";
                        $sqlFiltroMensaje .= " AND mj.fecha_creacion > '$value' ";
                        $sqlFiltroFechaLimite .= " AND mj2.fecha_creacion <= '$value'";
                    }
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "ic.$key = $value";
                    } else {
                        $sqlFiltro .= "ic.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0 || $limite === '0') {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        // Se separa la tabla venta de la interconsulta ya que este es opcional
        $sql= "SELECT ic.*, 
            (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) AS nombre_local,
            (SELECT vt.cod_clienteFK from venta vt WHERE vt.cod_venta = ic.cod_ventaFK) AS cod_clienteFK,
            (SELECT vt.num_factura from venta vt WHERE vt.cod_venta = ic.cod_ventaFK) AS num_factura,
            (SELECT SUM(monto) FROM gastos WHERE cod_interConsultaFK = ic.cod_interConsulta) AS total_gastos,
            (SELECT p.nombre_persona from venta vt JOIN persona p where p.cod_persona = vt.cod_clienteFK AND vt.cod_venta = ic.cod_ventaFK) as nombre_persona,
            (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) as nombre_persona_creador,
            (SELECT c.ci_cliente from cliente c JOIN venta vt where c.cod_cliente = vt.cod_clienteFK AND vt.cod_venta = ic.cod_ventaFK) as cedula,
            (SELECT COUNT(*) FROM gastos WHERE estado = 'solicitado' AND cod_interConsultaFK = ic.cod_interConsulta) AS cantGastosPendientes,
            (SELECT COUNT(idgastos) FROM gastos g WHERE g.cod_interConsultaFK = ic.cod_interConsulta) AS cantAsociadoGastos,
            (SELECT COUNT(cod_mensaje) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta) AS cantMensajes,
            (SELECT COUNT(cod_mensaje) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta and estado = 'activo' $sqlFiltroMensaje) AS cantMensajesProgramados,
            (SELECT COUNT(mc.cod_mencion)
                FROM menciones mc
                JOIN mensaje mj 
                ON mc.cod_mensajeFK = mj.cod_mensaje
                WHERE mc.isLeido = 0
                AND mj.cod_interConsultaFK = ic.cod_interConsulta
                AND mj.fecha_creacion = (
                    SELECT MAX(mj2.fecha_creacion)
                    FROM mensaje mj2
                    WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta AND estado = 'activo' $sqlFiltroFechaLimite
                )
            ) AS cantMensajesNoLeidosOtrosUsuarios,
            (SELECT COUNT(cod_mencion) from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mc.isLeido = 0 $sqlFiltroMenciones AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mj.fecha_creacion = (
                SELECT MAX(mj2.fecha_creacion)
                FROM mensaje mj2
                WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta AND estado = 'activo' $sqlFiltroFechaLimite
            )) AS cantMensajesNoLeidos
            from interconsulta ic $sqlFiltro
            ORDER BY 
            (SELECT COUNT(cod_mencion) from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mc.isLeido = 0 $sqlFiltroMenciones AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mj.fecha_creacion = (
                SELECT MAX(mj2.fecha_creacion)
                FROM mensaje mj2
                WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta $sqlFiltroFechaLimite
            )) DESC,
            FIELD(ic.estado, 'proceso', 'pendiente', 'finalizado', 'inactivo'),
            ic.cod_interConsulta DESC $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo "$sql\n$limite\n";
            echo "Error: ". mysqli_error($mysqli);
        }
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmInterConsulta($cod_interConsulta, $asunto, $observacion, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_usuarioFK_edit, $cod_localFK, $monto_limite) {
        $mysqli = conectar_al_servidor();
        if (empty($cod_interConsulta)) {
            $sql = "INSERT INTO interconsulta (asunto, observacion, estado, tipo, cod_ventaFK, cod_usuarioFK_create, fecha_creacion, cod_localFK, monto_limite) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssssiiii',$asunto, $observacion, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_localFK, $monto_limite);
        } else {
            // Obtiene los datos de la interconsulta antes que sea modificada
            $interconsulta_original= obtenerInterConsulta(array(
                "cod_interConsulta" => $cod_interConsulta
            ), 1);
            // Una copia de los datos nuevos con su encabezado
            $nuevos_datos= array();

            $parametros = array();
            $atributos = "";
            $ss = "";

            $atributos .= "asunto= ?";
            $ss .= "s";
            $parametros[] = $asunto;
            $nuevos_datos['asunto'] = $asunto;

            $atributos .= ", cod_localFK= ?";
            $ss .= "i";
            $parametros[] = $cod_localFK;
            $nuevos_datos['cod_localFK'] = $cod_localFK;

            // Datos para auditoria
            $fechaActual= new DateTime();
            $atributos .= ",cod_usuarioFK_edit= ?";
            $ss .= "i";
            $parametros[] = $cod_usuarioFK_edit;
            $atributos .= ",fecha_edit= ?";
            $ss .= "s";
            $parametros[] = $fechaActual->format('Y-m-d H:i:s');

            // Datos a modificar
            if ($estado != NULL) {
                $atributos .= ", estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
                $nuevos_datos['estado'] = $estado;
            }
            if ($observacion != NULL) {
                $atributos .= ", observacion= ?";
                $ss .= "s";
                $parametros[] = $observacion;
                $nuevos_datos['observacion'] = $observacion;
            }
            if ($tipo != NULL) {
                $atributos .= ", tipo= ?";
                $ss .= "s";
                $parametros[] = $tipo;
                $nuevos_datos['tipo'] = $tipo;
            }
            if ($cod_ventaFK != NULL) {
                $atributos .= ", cod_ventaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_ventaFK;
                $nuevos_datos['cod_ventaFK'] = $cod_ventaFK;
            }
            if ($monto_limite != NULL) {
                $atributos .= ", monto_limite= ?";
                $ss .= "i";
                $parametros[] = $monto_limite;
                $nuevos_datos['monto_limite'] = $monto_limite;
            }
            
            $parametros[] = $cod_interConsulta;
            $ss .= "i";

            $sql= "UPDATE interconsulta SET $atributos WHERE cod_interConsulta = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        if (empty($cod_interConsulta)) {
            // Se inserto una nueva interconsulta
            $cod_interConsulta = $stmt->insert_id;
        } else {
            $mensaje = 'El usuario <b class="menciones-mensaje" id="'.$cod_usuarioFK_edit.'" title="Mencion a @nombre">@nombre</b>&nbsp; modifico';
            $mensajeDatosCambiados= "";
            // Se actualizo la interconsulta, se compara los datos para registrar los cambios en la tabla mensaje
            foreach ($nuevos_datos as $key => $value) {
                if ($interconsulta_original[0][$key] != $value) {
                    // Registrar cambio en un mensaje
                    $mensajeDatosCambiados .= ' el campo '.$key.' de '.$interconsulta_original[0][$key].' a '.$value.', ';
                }
            }

            if ($mensajeDatosCambiados != "") {
                $mensaje .= substr($mensajeDatosCambiados, 0, -2).'.';
                $fechaActual = new DateTime();
                $fechaActual = $fechaActual->format('Y-m-d H:i:s');
                abmMensaje(null, $mensaje, $fechaActual, $cod_interConsulta, $cod_usuarioFK_edit, NULL);
            }
        }

        $stmt->close();
        return $cod_interConsulta;
    }

    if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
        $operacion = $_POST['accion'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificarOperacionInterConsulta($operacion);
    }
?>

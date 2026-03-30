<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("subir_foto_base64.php");
    include_once("abmgasto.php");

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
                    'fecha_limite' => $fechaActual->format('Y-m-d H:i:s')
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
                    'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
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
                abmMensaje(null, 'Se quito la mencion de '.$registroMenc['nombre_persona'], $fechaActual, $cod_interConsulta, $user);

                echo json_encode(array("1" => "exito"));
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
                
                $cod_mensaje= abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user);
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
            case 'buscarDictamen':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $resultado= isset($_POST['resultado']) ? mb_convert_encoding((string)($_POST['resultado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsultaFK= isset($_POST['cod_interConsultaFK']) ? mb_convert_encoding((string)($_POST['cod_interConsultaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK_create= isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK_autoriz= isset($_POST['cod_usuarioFK_autoriz']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_autoriz']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK_ejecut= isset($_POST['cod_usuarioFK_ejecut']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_ejecut']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_persona_create= isset($_POST['nombre_persona_create']) ? mb_convert_encoding((string)($_POST['nombre_persona_create']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_persona_autoriz= isset($_POST['nombre_persona_autoriz']) ? mb_convert_encoding((string)($_POST['nombre_persona_autoriz']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_persona_ejecut= isset($_POST['nombre_persona_ejecut']) ? mb_convert_encoding((string)($_POST['nombre_persona_ejecut']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros= array(
                    'id' => $id,
                    'resultado' => $resultado,
                    'estado' => $estado,
                    'cod_interConsultaFK' => $cod_interConsultaFK,
                    'cod_usuarioFK_create' => $cod_usuarioFK_create,
                    'cod_usuarioFK_autoriz' => $cod_usuarioFK_autoriz,
                    'cod_usuarioFK_ejecut' => $cod_usuarioFK_ejecut,
                    'nombre_persona_create' => $nombre_persona_create,
                    'nombre_persona_autoriz' => $nombre_persona_autoriz,
                    'nombre_persona_ejecut' => $nombre_persona_ejecut,
                    'asunto' => $asunto,
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;
                $registros= obtenerDictamen($filtros, $limite);
                echo json_encode(array("1" => "exito", "2" => $registros));
                break;
            case 'nuevo/editar dictamen':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $resultado= isset($_POST['resultado']) ? mb_convert_encoding((string)($_POST['resultado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsultaFK= isset($_POST['cod_interConsultaFK']) ? mb_convert_encoding((string)($_POST['cod_interConsultaFK']), 'ISO-8859-1', 'UTF-8') : null;

                $id= abmDictamen($id, $resultado, $estado, $cod_interConsultaFK, $user, $user, $user);
                echo json_encode(array("1" => "exito", "2" => $id));
                break;
            case 'buscarMasInterConsultasYContenido':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $offset= isset($_POST['offset']) ? mb_convert_encoding((string)($_POST['offset']), 'ISO-8859-1', 'UTF-8') : null;
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $fechaActual= new DateTime();
                $filtros= array(
                    "cod_interConsultaFK" => $cod_interConsulta,
                    "cod_usuarioFK" => $user,
                    'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
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
                        <td id="td_datos_13" class="tdRegistroSearch" style="width: 15%;">'.$value['estado_fisico'].'</td>
                        <td id="td_datos_14" style="display: none;">'.$cant_ultima_edicion.'</td>
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
                
                $cod_mensaje= abmMensaje("", $contenido, $fechaActual->format('Y-m-d H:i:s'), $cod_interConsulta, "", FALSE);
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
        $cod_mensaje= abmMensaje("", "esta y la interconsulta ".$registroInterc['asunto']." fueron unidas por @{$cod_usuarioFK}", $fechaActual->format('Y-m-d H:i:s'), $cod_interConsulta_destino, "", FALSE);

        // Pasa todos los mensajes al interconsulta destino
        foreach ($registrosMens as $mensj) {
            abmMensaje($mensj['cod_mensaje'], NULL, NULL, $cod_interConsulta_destino, NULL);
        }
        
        // Agrega las menciones faltantes al mensaje del sistema
        foreach ($ids_menciones as $value) {
            if (empty($value)) {continue;}
            abmMencion(null, $value, $cod_mensaje, 0, 'activo');
        }

        // Agrega las menciones a los mensajes futuros del sistema
        $registrosMens= obtenerMensaje(array(
            'cod_interConsultaFK' => $cod_interConsulta_destino,
            'fecha_creacion' => "> '".$fechaActual->format('Y-m-d H:i:s')."'",
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
            $mencionesElemento= "";
            $menciones= array();
            
            // Se obtienen los mensajes
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
                'cod_interConsultaFK' => $valueInter['cod_interConsulta']
            ));

            if (count($registrosMens) > 0) {
                $ultimoMensaje= end($registrosMens);
    
                $paginaMensajes= "";
                // Obtiene todas las menciones
                $registrosMenc= obtenerMencion(array(
                    'cod_mensajeFK' => $ultimoMensaje['cod_mensaje']
                ), 0);
    
                foreach ($registrosMenc as $valueMenc) {
                    if ($valueMenc['estado'] == 'activo' && !in_array($valueMenc['nombre_persona'], $menciones)) {
                        $mencionesElemento .= '<li style="
                            background-color: #f2f2f2;
                            text-align: left;
                            margin-bottom:4px;
                            padding:5px 10px;
                            border-radius:4px;
                            font-size:13px;
                            display: '. (($valueInter['cod_usuarioFK_create'] != $valueMenc['cod_usuarioFK']) ? "flex" : "none").';
                            justify-content: space-between;
                        "><div>'.$valueMenc['nombre_persona'].
                        (($valueMenc['isLeido'] == 1) ? '<i class="fa-solid fa-check-double" style="color: #0cdd23;"></i>' : '').
                        '</div>
                        <img src="/GoodVentaAsisCap/iconos/botonCerrar.png" class="iconoBtn" title="Eliminar" onclick="eliminarMencionMensaje('.$valueMenc["cod_mencion"].')"></li>';
                        $menciones[] = $valueMenc['nombre_persona'];
                    }
                }
            }

            $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_usuarioFK' => $filtros['cod_usuarioFK']
                ), $limiteMensajes, 0);

            // Obtiene los mensajes programados
            $registrosMens2= obtenerMensaje(array(
                'fecha_creacion' => "> '".$fechaActual->format('Y-m-d H:i:s')."'",
                "cod_interConsultaFK" => $valueInter["cod_interConsulta"],
                "estado" => 'activo'
            ));

            foreach ($registrosMens2 as $valueMens) {
                $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: right;">
                    <div class="card my-3" style="border-left: 5px solid gray;width: 500px;margin-left: 10px; margin-right: 10px;">
                      <div class="card-body">
                          <div style="display: flex;">
                            <span style="display: none;">'.$valueMens['cod_mensaje'].'</span>
                            <p class="card-text" style="text-align: justify;">Mensaje programado '.($valueMens['nombre_persona'] ? 'de '.$valueMens['nombre_persona'] : 'por el sistema').' para el '.$valueMens['fecha_creacion'].'</p>
                          </div>
                      </div>
                    </div>
                  </div>';
            }

            $colorTarjeta="#8bc34a;";
            $claseEstado= "badge-success";
            if ($valueInter['estado'] == 'proceso') {
                $colorTarjeta=" #e53935; ";
                $claseEstado = "badge-danger";
            } else if ($valueInter['estado'] == 'pendiente') {
                $colorTarjeta=" #e1c247;";
                $claseEstado= "badge-warning";
            }
            // Se asigna el estilo para asuntos con mensajes sin leer
            $styleMensajeNoLeido= "";
            if (intval($valueInter['cantMensajesNoLeidos']) > 0) {
                $styleMensajeNoLeido= "border: 10px solid $colorTarjeta";
            }
            
            // Se crea el encabezado
            $pagina.= '<div id="contenedorMensajesInterConsulta">';
            
            if (count($registrosMens) > ($limiteMensajes)) {
                $pagina .= "<div style='width: 100%; justify-content: center;'>
                    <button class='btn btn-success' onclick='verMasMensajesInterconsulta($limiteMensajes)'>Ver más mensajes...</button>
                    </div>";
            }
            $pagina .= $paginaMensajes. '</div>';

            // Obtiene la cantidad total de mensajes
            $totalCantMensaje2= obtenerMensaje(array(
                'cod_interConsultaFK' => $valueInter['cod_interConsulta']
            ));
            $totalCantMensaje += count($totalCantMensaje2);
        }   

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $filtros['cod_ventaFK'], "4" => $valueInter, "5" => $totalCantMensaje, "6" => $mencionesElemento));
    }

    function obtenerVistaFlujoGastosInterConsulta($cod_interConsulta) {
        if (empty($cod_interConsulta)) {
            return '<div class="text-secondary" style="padding: 8px;">Sin gastos asociados.</div>';
        }

        $gastosElemento= "";
        $registrosGastos = buscarGasto("","","",'','','','','','true','', $cod_interConsulta, '', '','');
        $registrosGastos= $registrosGastos[9];
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
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
                "cod_interConsultaFK" => $filtros["cod_interConsultaFK"],
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
                    '<b class="menciones-mensaje" id="'.$valueUsu['cod_usuario'].'">@'.$valueUsu['nombre_persona'].'</b>', 
                    $contenidoMensaje
                );
            }
            $contenidoMensaje = nl2br($contenidoMensaje, false);

            $miniatura_imagen= "";
            if ($valueMens['url']) {
                $miniatura_imagen= '<div id="imgfotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'" class="imgFotoProducto" 
                    onclick="vercerrarcargadefotos(\'fotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'\', false)" style="background-image: url('.$valueMens['url'].');margin-right: 5px;">
                    </div>';
            }
            
            if (!$valueMens['cod_usuarioFK'] || $valueMens['cod_usuarioFK'] == "NULL") {
                $colorTarjeta= "#EABA4C";
                $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: center;">
                    <div class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 1000px;margin-left: 10px; margin-right: 10px;">
                        <span></span>
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <p class="card-text" style="text-align: justify;">'.$contenidoMensaje.' el '.$valueMens['fecha_creacion'].'</p>
                        </div>
                    </div>
                </div>';
            } else {
                $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: '.$posicion.';">
                        <div class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 500px;margin-left: 10px; margin-right: 10px;">
                          <span></span>
                          <div class="card-header d-flex justify-content-between align-items-center">
                              <div>
                                <img src="'.($valueMens['url_usuario'] == null ? "/GoodVentaAsisCap/iconos/user.png" : $valueMens['url_usuario']).'" style="max-height: 30px;max-width: 35px;"/>
                                <span>'.$valueMens['nombre_persona'].'</span>
                              </div>
                              <small class="text-secondary">
                                <input class="inputText" type="datetime-local" value="'.$valueMens['fecha_creacion'].'" disabled style="border: none;">
                              </small>
                          </div>
                          <div class="card-body">
                              <div style="display: flex;">
                                '.$miniatura_imagen.'
                                <p class="card-text" style="text-align: justify;">'.$contenidoMensaje.'</p>
                              </div>
                          </div>
                        </div>
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

            $formatAsunto= '<p style="'.$colorText.'font-size: 9pt;width: fit-content;">'
                .$value['asunto']
                .(($value['cantAsociadoGastos'] > 0) ? ' <img src="/GoodVentaAsisCap/iconos/checklist.png" style="color:green; height: 20px; margin-inline: 5px;"/>' : '')
                .$cantMensajesNoLeidosOtrosUsuarios;
            if (intval($value['cantMensajesNoLeidos']) > 0) {
                $style = 'background-color: rgb(140, 8, 8, 0.7);  color: #ffffff;';
                $cant_mensajes_no_leidos += intval($value['cantMensajesNoLeidos']);
                $formatAsunto= '<b style="'.$colorText.'font-size: 9pt;width: fit-content;">'
                    .$value['asunto']
                    .(($value['cantAsociadoGastos'] > 0) ? ' <img src="/GoodVentaAsisCap/iconos/checklist.png" style="color:green; height: 20px; margin-inline: 5px;"/>' : '')
                    .$cantMensajesNoLeidosOtrosUsuarios
                    .'</b>';
            }

            if ($value["cantMensajesProgramados"]) {
                // Obtiene los mensajes programados
                $registrosMens= obtenerMensaje(array(
                    'fecha_creacion' => "> '".(new DateTime())->format('Y-m-d H:i:s')."'",
                    "cod_interConsultaFK" => $value["cod_interConsulta"],
                ));
                foreach ($registrosMens as $valueMens) {
                    if ($valueMens['estado'] == 'activo') {
                        $fechaMensaje = new DateTime(substr($valueMens['fecha_creacion'], 0, 10));
                        $fechaActual = new DateTime();
                        $diasRestantes = $fechaMensaje->diff($fechaActual->setTime(0, 0, 0));
                        $formatAsunto .= '<i class="fa-solid fa-business-time" style="padding-left: 5px;font-size: 9pt;"></i>('.$diasRestantes->format('%a').') ';
                    }
                }
            }
            $formatAsunto .= '</p>';
            
            $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding="1">
                <tr onclick="obtenerDatosInterConsulta(this)">
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

    function obtenerDictamen($filtros= array(), $limite= 0) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if ($value === null || $value === "") {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'estado':
                    $sqlFiltro .= "d.estado = '$value'";
                    break;
                case 'cod_interConsultaFK':
                    $sqlFiltro .= "d.cod_interConsultaFK = $value";
                    break;
                case 'asunto':
                    $sqlFiltro .= "ic.asunto like '%$value%'";
                    break;
                case 'nombre_persona_create':
                    $sqlFiltro .= "pcreate.nombre_persona like '%$value%'";
                    break;
                case 'nombre_persona_autoriz':
                    $sqlFiltro .= "paut.nombre_persona like '%$value%'";
                    break;
                case 'nombre_persona_ejecut':
                    $sqlFiltro .= "peje.nombre_persona like '%$value%'";
                    break;
                case 'fecha_create':
                    $sqlFiltro .= "d.fecha_create $value";
                    break;
                case 'fecha_autoriz':
                    $sqlFiltro .= "d.fecha_autoriz $value";
                    break;
                case 'fecha_ejecut':
                    $sqlFiltro .= "d.fecha_ejecut $value";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "d.$key = $value";
                    } else {
                        $sqlFiltro .= "d.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0 || $limite === '0') {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT d.*, ic.asunto,
                pcreate.nombre_persona AS nombre_persona_create,
                paut.nombre_persona AS nombre_persona_autoriz,
                peje.nombre_persona AS nombre_persona_ejecut
            FROM dictamen d
            LEFT JOIN persona pcreate ON pcreate.cod_persona = d.cod_usuarioFK_create
            LEFT JOIN persona paut ON paut.cod_persona = d.cod_usuarioFK_autoriz
            LEFT JOIN persona peje ON peje.cod_persona = d.cod_usuarioFK_ejecut
            LEFT JOIN interconsulta ic ON ic.cod_interConsulta = d.cod_interConsultaFK
            $sqlFiltro
            ORDER BY FIELD(d.estado, 'solicitado', 'aprobado', 'ejecutado', 'inactivo'), d.id DESC
            $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al obtener dictamen: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            $reg = array();
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmDictamen($id, $resultado, $estado, $cod_interConsultaFK, $cod_usuarioFK_create, $cod_usuarioFK_autoriz= null, $cod_usuarioFK_ejecut= null) {
        $mysqli = conectar_al_servidor();
        $fechaActual= new DateTime();
        $fechaActual= $fechaActual->format('Y-m-d H:i:s');

        if (empty($id)) {
            if (empty($resultado) || empty($cod_interConsultaFK) || empty($cod_usuarioFK_create)) {
                echo json_encode(array("1" => "error", "2" => "Faltan datos para registrar el dictamen."));
                exit;
            }

            if (empty($estado)) {
                $estado = 'solicitado';
            }

            $fecha_autoriz = null;
            $fecha_ejecut = null;

            if ($estado == 'aprobado' || $estado == 'ejecutado') {
                if (empty($cod_usuarioFK_autoriz)) {
                    $cod_usuarioFK_autoriz = $cod_usuarioFK_create;
                }
                $fecha_autoriz = $fechaActual;
            }
            if ($estado == 'ejecutado') {
                if (empty($cod_usuarioFK_ejecut)) {
                    $cod_usuarioFK_ejecut = !empty($cod_usuarioFK_autoriz) ? $cod_usuarioFK_autoriz : $cod_usuarioFK_create;
                }
                $fecha_ejecut = $fechaActual;
            }

            $sql = "INSERT INTO dictamen (
                resultado, estado, fecha_create, cod_usuarioFK_create,
                fecha_autoriz, cod_usuarioFK_autoriz, fecha_ejecut, cod_usuarioFK_ejecut, cod_interConsultaFK
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'sssisisii',
                $resultado,
                $estado,
                $fechaActual,
                $cod_usuarioFK_create,
                $fecha_autoriz,
                $cod_usuarioFK_autoriz,
                $fecha_ejecut,
                $cod_usuarioFK_ejecut,
                $cod_interConsultaFK
            );
        } else {
            $dictamen_original= obtenerDictamen(array(
                "id" => $id
            ), 1);

            if (count($dictamen_original) == 0) {
                echo json_encode(array("1" => "error", "2" => "Dictamen no encontrado."));
                exit;
            }

            $dictamen_original= $dictamen_original[0];
            $parametros = array();
            $atributos = "";
            $ss = "";

            if ($resultado !== null) {
                $atributos .= "resultado = ?, ";
                $parametros[] = $resultado;
                $ss .= "s";
            }
            if ($estado !== null) {
                $atributos .= "estado = ?, ";
                $parametros[] = $estado;
                $ss .= "s";

                if (($estado == 'aprobado' || $estado == 'ejecutado') && empty($dictamen_original['fecha_autoriz'])) {
                    if (empty($cod_usuarioFK_autoriz)) {
                        $cod_usuarioFK_autoriz = $cod_usuarioFK_create;
                    }
                    $atributos .= "fecha_autoriz = ?, cod_usuarioFK_autoriz = ?, ";
                    $parametros[] = $fechaActual;
                    $parametros[] = $cod_usuarioFK_autoriz;
                    $ss .= "si";
                }
                if ($estado == 'ejecutado' && empty($dictamen_original['fecha_ejecut'])) {
                    if (empty($cod_usuarioFK_ejecut)) {
                        $cod_usuarioFK_ejecut = !empty($cod_usuarioFK_autoriz) ? $cod_usuarioFK_autoriz : $cod_usuarioFK_create;
                    }
                    $atributos .= "fecha_ejecut = ?, cod_usuarioFK_ejecut = ?, ";
                    $parametros[] = $fechaActual;
                    $parametros[] = $cod_usuarioFK_ejecut;
                    $ss .= "si";
                }
            }
            if ($cod_interConsultaFK !== null) {
                $atributos .= "cod_interConsultaFK = ?, ";
                $parametros[] = $cod_interConsultaFK;
                $ss .= "i";
            }

            if ($atributos == "") {
                return $id;
            }

            $atributos = substr($atributos, 0, -2);
            $parametros[] = $id;
            $ss .= "i";

            $sql= "UPDATE dictamen SET $atributos WHERE id = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}
            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar dictamen: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        if (empty($id)) {
            $id = $stmt->insert_id;
        }

        $stmt->close();
        return $id;
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

    function abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user, $visto_creador= FALSE) {
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
            $sql = "INSERT INTO mensaje (contenido, fecha_creacion, cod_interConsultaFK, cod_usuarioFK) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssii', $contenidoLimpiado, $fecha_creacion, $cod_interConsulta, $user);
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
            if (!empty($cod_interConsulta)) {
                $atributos .= ", cod_interConsultaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_interConsulta;
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
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
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
                    $sqlFiltro .= "ic.estado = '$value'";
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
                case 'fecha_limite':
                    $sqlFiltroMenciones .= " AND mj.fecha_creacion <= '$value' ";
                    $sqlFiltroMensaje .= " AND mj.fecha_creacion > '$value' ";
                    $sqlFiltroFechaLimite .= " AND mj2.fecha_creacion <= '$value'";
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
            $mensaje = 'El usuario <b class="menciones-mensaje" id="'.$cod_usuarioFK_edit.'">@nombre</b>&nbsp; modifico';
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
                abmMensaje(null, $mensaje, $fechaActual, $cod_interConsulta, $cod_usuarioFK_edit);
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

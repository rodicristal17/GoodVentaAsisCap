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
            case 'nuevo/editar interconsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= (isset($_POST['cod_localFK']) && is_numeric($_POST['cod_localFK'])) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $monto_limite= isset($_POST['monto_limite']) ? mb_convert_encoding((string)($_POST['monto_limite']), 'ISO-8859-1', 'UTF-8') : null;

                $cod_interConsulta= abmInterConsulta($cod_interConsulta, $asunto, $estado, $tipo, $cod_ventaFK, $user, $user, $cod_localFK, $monto_limite);
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
                        <td id="td_datos_2" class="tdRegistroSearch" style="width: 15%;">'.$value['estado'].'</td>
                        <td id="td_datos_9" class="tdRegistroSearch" style="width: 35%;">'.$value['nombre_persona_creador'].'</td>
                    </tr></table>';
                }
                
                // Formatea los locales
                $locales= array_unique($locales);
                $nombre_local= implode(" / ", $locales);

                echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $nombre_local));
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario) {
        $pagina = "";
        $limiteMensajes= 5;
        $totalCantMensaje= 0;
        $totalGastos= 0;
        
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
            
            // Genera el listado de Gastos
            $gastosElemento= "";
            $registrosGastos = buscarGasto("","","",'','','','','','true','', $valueInter['cod_interConsulta'])[9];
            foreach ($registrosGastos as $gasto) {
                $estadoClassGasto= 'text-bg-success';
                $aprobarElemento= "";
                switch (strtolower($gasto["estado"])) {
                    case 'rechazado':
                        $estadoClassGasto= 'text-bg-danger';
                        break;
                    case 'solicitado': 
                        $estadoClassGasto= 'text-bg-warning';
                        $aprobarElemento= '<i class="fa-solid fa-check" onclick="event.stopPropagation();aprobarMovimiento(true, this.parentElement)" style="font-size: 14pt; color: white; background-color: green; padding: 2px;border-radius: 5px;"></i>
                        <i class="fa-solid fa-xmark" onclick="event.stopPropagation();aprobarMovimiento(false, this.parentElement)" style="font-size: 14pt; color: white; background-color: red; padding: 2px;border-radius: 5px;"></i>';
                        break;
                    default:
                        $estadoClassGasto= 'text-bg-success';
                        break;
                }

                $gastosElemento .= '<table class="tableRegistroSearch" border="1" cellspacing="1" cellpadding="5">
				<tr id="tbSelecRegistro" onclick="obtenerdatosabmGasto(this);verCerrarAbmGasto();verVentanaEditarGasto(\'divAbmDetallesInterConsulta\');">
                    <td id="td_id" style="width:10%;">'.$gasto["idgastos"].'</td>
                    <td style="width: 25%;"><span class="badge '.$estadoClassGasto.'" style="font-size: 7pt;">'.$gasto["estado"].'</span></td>
                    <td  id="td_datos_2" style="display: none">'.$gasto["motivo"].'</td>
                    <td  style="width: 30%">'.$gasto["descripcion"].'</td>
                    <td  id="td_datos_1" style="display: none">'. number_format($gasto["monto"],0,',','.').'</td>
                    <td  id="td_datos_6" style="display: none">'.$gasto["tipo"].'</td>
                    <td  id="td_datos_3" style="display: none;">'.$gasto["fecha"].'</td>
                    <td  id="td_datos_3" style="display: none;">'.$gasto["nroboleta"].'</td>
                    <td  id="td_datos_9" style="display: none;">'.$gasto["banco"].'</td>
                    <td  id="td_datos_10" style="display: none;">'.$gasto["nrocuenta"].'</td>
                    <td  id="td_datos_11" style="display: none;">'.$gasto["arreglo"].'</td>
                    <td  id="td_datos_8" style="display: none">'.$gasto["usuarionombre"].'</td>
                    <td  id="td_datos_5" style="display:none">'.$gasto["estado"].'</td>
                    <td  id="td_datos_7" style="display:none">'.$gasto["cod_local"].'</td>
                    <td  id="td_datos_12" style="display:none">'.$gasto["url1"].'</td>
                    <td  id="td_datos_13" style="display:none">'.$gasto["descripcion"].'</td>
                    <td  id="td_datos_14" style="display:none">'.$gasto["motivo"].'</td>
                    <td  id="td_datos_15" style="display:none">'.$gasto["cod_interConsultaFK"].'</td>
                    <td  id="td_datos_16" style="display:none">'.$gasto["interconsulta_nombre"].'</td>
                    <td  id="td_datos_17" style="display:none">'.$gasto["cod_usuario_autoriz"].'</td>
                    <td  id="td_datos_18" style="display:none">'.$gasto["usuario_autoriz_nombre"].'</td>
                    <td  id="td_datos_19" style="display:none">'.$gasto["fecha_autoriz"].'</td>
                    <td  id="td_datos_20" style="display:none">'.$gasto["cod_motivoIngresoEgresoFK"].'</td>
                    <td class="td_registroSearch" style="width: 20%">'.number_format($gasto["monto"], 0, ",", ".").'</td>
                    <td class="td_registroSearch" style="width: 20%">
                        '.$aprobarElemento.'
                    </td>
                </tr></table>';
            }

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
            ));

            foreach ($registrosMens2 as $valueMens) {
                $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: right;">
                    <div class="card my-3" style="border-left: 5px solid gray;width: 500px;margin-left: 10px; margin-right: 10px;">
                      <div class="card-body">
                          <div style="display: flex;">
                            <span style="display: none;">'.$valueMens['cod_mensaje'].'</span>
                            <p class="card-text" style="text-align: justify;">Mensaje programado de '.$valueMens['nombre_persona'].' para el '.$valueMens['fecha_creacion'].'</p>
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
            $pagina.= '<div class="sugerencias-container" style="display: grid;justify-content: center;">
                <div id="contenedorEncabezadoInterConsulta" class="card my-3" style="border-left: 5px solid '.$colorTarjeta.'; width: 1200px;'.$styleMensajeNoLeido.'">
            <div class="card-body">
            <h5 class="card-title">
                '.$valueInter['asunto'].(empty($valueInter['cod_ventaFK']) ? '' : ' - '.$valueInter['nombre_persona']).'
                <img src="../iconos/editar.png" alt="Editar InterConsulta" style="height: 1.25rem;" onclick="obtenerDetallesInterConsulta(\'interConsulta\')">
            </h5>
            <div style="display: flex;">
            <div style="flex: 0.5;padding-top: 10px;border-top: 1px solid #ddd;">
            <strong>Mencionados</strong>
            <ul style="overflow-y: auto; height: 150px;">
            '.$mencionesElemento.'
            </ul>
            </div>
            <div style="flex: 0.5; text-align: left; padding-left: 15px;">
            <div style="margin-bottom: 5px;">
            <span class="fw-bold">Usuario creador:</span>
            <span class="text-uppercase">'.$valueInter['nombre_persona_creador'].'</span>
            </div>
            <div style="margin-bottom: 5px;">
            <span class="fw-bold">Fecha creacion:</span>
            <span class="text-uppercase">'.$valueInter['fecha_creacion'].'</span>
            </div>
            <div style="margin-bottom: 5px;">
            <span class="fw-bold">Estado:</span>
            <span id="td_datos_32" class="badge '.$claseEstado.' text-uppercase">'.$valueInter['estado'].'</span>
            </div>
            <div style="margin-bottom: 5px;">
            <span class="fw-bold">Tipo:</span>
            <span id="td_datos_33" class="badge badge-secondary text-uppercase">'.$valueInter['tipo'].'</span>
            </div>
            <div style="margin-bottom: 5px;">
                <span class="fw-bold">Cod. InterConsulta:</span>
                <span class="text-uppercase" id="td_datos_36">'.$valueInter['cod_interConsulta'].'</span>
            </div>
            <div style="margin-bottom: 5px;">
            <span class="fw-bold">Local:</span>
            <span id="localDetalleInterConsulta" class="text-uppercase">'.$valueInter['nombre_local'].'</span>
            </div>';
            if ($valueInter['tipo'] == 'clinico' || $valueInter['tipo'] == 'administrativo') {
                $pagina .= '<div style="margin-bottom: 5px;">
                <span class="fw-bold">Cod. Venta:</span>
                <span class="text-uppercase">'.$valueInter['num_factura'].'</span>
                </div>';
            }
            if ($valueInter['nombre_motivo_asociado']) {
                $pagina .= '<div style="margin-bottom: 5px;">
                <span class="fw-bold">Motivo Asociado:</span>
                <span class="text-uppercase">'.$valueInter['nombre_motivo_asociado'].'</span>
                </div>';
            }
            if ($gastosElemento && $valueInter['monto_limite']) {
                $pagina .= '<div style="margin-bottom: 5px;">
                <span class="fw-bold">Monto Limite:</span>
                <span class="text-uppercase">'.number_format($valueInter['monto_limite'], 0, ',', '.').' Gs.</span>
                </div>';
            }
            $pagina .= '</div>';

            if ($gastosElemento) {
                $pagina .= '<div style="flex: 0.5;">
                <span><strong>Gastos asociados</strong></span>
                <table class="tableCabeceraRegistro">
                    <tr>
                        <td class="td_registro" style="width: 10%;text-align: left;">Cod.</td>
                        <td class="td_registro" style="width: 25%;text-align: left;">Estado</td>
                        <td class="td_registro" style="width: 30%;text-align: left;">Descripcion</td>
                        <td class="td_registro" style= "width: 20%;text-align: left;">Monto</td>
                        <td class="td_registro" style= "width: 25%;text-align: left;"></td>
                    </tr>
                </table>
                <div style="overflow-y: auto; height: 150px;">
                    '.$gastosElemento.'
                </div>
                </div>';
            }

            $pagina .= '</div>
            <div style="display: none;">
            <span id="td_datos_31">'.$valueInter['asunto'].'</span>
            <span id="td_datos_34">'.$valueInter['cod_interConsulta'].'</span>
            <span id="td_datos_35">'.$valueInter['cod_ventaFK'].'</span>
            <span id="td_datos_37">'.$valueInter['nombre_persona'].'</span>
            <span id="td_datos_38">'.$valueInter['cod_localFK'].'</span>
            <span id="td_datos_39">'.$valueInter['cod_clienteFK'].'</span>
            <span id="td_datos_41">'.$valueInter['monto_limite'].'</span>
            </div>
            </div>
            </div>
            </div>
            <div id="contenedorMensajesInterConsulta">';
            
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

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $filtros['cod_ventaFK'], "4" => $filtros['cod_interConsulta'], "5" => $totalCantMensaje));
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

            $miniatura_imagen= "";
            if ($valueMens['url']) {
                $miniatura_imagen= '<div id="imgfotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'" class="imgFotoProducto" 
                    onclick="vercerrarcargadefotos(\'fotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'\', false)" style="background-image: url('.$valueMens['url'].');margin-right: 5px;">
                    </div>';
            }
            
            if (!$valueMens['cod_usuarioFK'] || $valueMens['cod_usuarioFK'] == "NULL") {
                $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: '.$posicion.';">
                    <div class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 500px;margin-left: 10px; margin-right: 10px;">
                        <span></span>
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                            <span>SISTEMA</span>
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
            if ($value['tipo'] == 'interno') {
                $styleInterno= "color: white; background-color: #08525f;";
            }
            
            $cantMensajesNoLeidosOtrosUsuarios= "";
            if (intval($value['cantMensajesNoLeidosOtrosUsuarios']) > 0) {
                $cantMensajesNoLeidosOtrosUsuarios= " (".$value['cantMensajesNoLeidosOtrosUsuarios'].")";
            }

            $colorText= "";
            if ($value['cantAsociadoGastos'] > 0) {
                $colorText= "color: rgb(14, 194, 32);";
            }

            $formatAsunto= '<p style="'.$colorText.'font-size: 9pt;width: fit-content;">'.$value['asunto'].$cantMensajesNoLeidosOtrosUsuarios.'</p>';
            if (intval($value['cantMensajesNoLeidos']) > 0) {
                $style = 'background-color: rgb(140, 8, 8, 0.7);  color: #ffffff;';
                $cant_mensajes_no_leidos += intval($value['cantMensajesNoLeidos']);
                $formatAsunto= '<b style="'.$colorText.'font-size: 9pt;width: fit-content;">'.$value['asunto'].$cantMensajesNoLeidosOtrosUsuarios.'</b>';
            }
            if ($value["cantMensajesProgramados"]) {
                $formatAsunto .= '<i class="fa-solid fa-business-time" style="padding-left: 5px;font-size: 9pt;"></i>';
            }
            
            $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding="1">
                <tr onclick="obtenerDatosInterConsulta(this)">
                    <td id="td_id" style="width: 5%;'.$styleInterno.'">'.$value['cod_interConsulta'].'</td>
                    <td id="td_datos_1" style="width: 25%;'.$style.'"><div style="display: flex;">'.$formatAsunto.'</div></td>
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
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros, "6" => $cant_mensajes_no_leidos, "7" => $cant_interConsulta_abierto));
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

    function abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user) {
        $mysqli = conectar_al_servidor();

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

        // Obtener el texto plano resultante
        $contenidoLimpiado = $dom->textContent;

        // Limpiar espacios y entidades
        $contenidoLimpiado = trim(html_entity_decode($contenidoLimpiado));
        $contenidoLimpiado = str_replace("\xC2\xA0", " ", $contenidoLimpiado);
        
        if (empty($cod_mensaje)) {
            $sql = "INSERT INTO mensaje (contenido, fecha_creacion, cod_interConsultaFK, cod_usuarioFK) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssii', $contenidoLimpiado, $fecha_creacion, $cod_interConsulta, $user);
        } else {
            $sql= "UPDATE mensaje SET contenido= ? WHERE cod_mensaje = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('si', $contenidoLimpiado, $cod_mensaje);
        }
        if (!$stmt->execute()) {
            print_r(array($contenidoLimpiado, $fecha_creacion, $cod_interConsulta, $user));
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }
        
        if (empty($cod_mensaje)) {
            $cod_mensaje = $stmt->insert_id;
        }

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
            // Marca al creador como leido solo si no es mensaje programado
            $fechaActualObj = new DateTime();
            $fechaCreacionObj = new DateTime($fecha_creacion);
            // Verifica si la diferencia en minutos es menor a 10
            $intervalo = $fechaActualObj->diff($fechaCreacionObj);
            $minutosDiferencia = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

            if ($value === $user && ($minutosDiferencia < 10)) {
                abmMencion(null, $value, $cod_mensaje, 1, 'activo');
            } else {
                abmMencion(null, $value, $cod_mensaje, 0, 'activo');
            }
        }
        
        $stmt->close();        

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
            (SELECT p.nombre_persona from venta vt JOIN persona p where p.cod_persona = vt.cod_clienteFK AND vt.cod_venta = ic.cod_ventaFK) as nombre_persona,
            (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) as nombre_persona_creador,
            (SELECT c.ci_cliente from cliente c JOIN venta vt where c.cod_cliente = vt.cod_clienteFK AND vt.cod_venta = ic.cod_ventaFK) as cedula,
            (SELECT descripcion FROM gastos_fijos gf WHERE gf.cod_interConsultaFK = ic.cod_interConsulta ORDER BY gf.cod_gastos_fijos DESC LIMIT 1) AS nombre_motivo_asociado,
            (SELECT COUNT(idgastos) FROM gastos g WHERE g.cod_interConsultaFK = ic.cod_interConsulta) AS cantAsociadoGastos,
            (SELECT COUNT(cod_mensaje) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta) AS cantMensajes,
            (SELECT COUNT(cod_mensaje) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta $sqlFiltroMensaje) AS cantMensajesProgramados,
            (SELECT COUNT(mc.cod_mencion)
                FROM menciones mc
                JOIN mensaje mj 
                ON mc.cod_mensajeFK = mj.cod_mensaje
                WHERE mc.isLeido = 0
                AND mj.cod_interConsultaFK = ic.cod_interConsulta
                AND mj.fecha_creacion = (
                    SELECT MAX(mj2.fecha_creacion)
                    FROM mensaje mj2
                    WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta  $sqlFiltroFechaLimite
                )
            ) AS cantMensajesNoLeidosOtrosUsuarios,
            (SELECT COUNT(cod_mencion) from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mc.isLeido = 0 $sqlFiltroMenciones AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mj.fecha_creacion = (
                SELECT MAX(mj2.fecha_creacion)
                FROM mensaje mj2
                WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta $sqlFiltroFechaLimite
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

    function abmInterConsulta($cod_interConsulta, $asunto, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_usuarioFK_edit, $cod_localFK, $monto_limite) {
        $mysqli = conectar_al_servidor();
        if (empty($cod_interConsulta)) {
            $sql = "INSERT INTO interconsulta (asunto, estado, tipo, cod_ventaFK,cod_usuarioFK_create, fecha_creacion, cod_localFK, monto_limite) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssiiii',$asunto, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_localFK, $monto_limite);
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
            if (!empty($estado)) {
                $atributos .= ", estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
                $nuevos_datos['estado'] = $estado;
            }
            if (!empty($tipo)) {
                $atributos .= ", tipo= ?";
                $ss .= "s";
                $parametros[] = $tipo;
                $nuevos_datos['tipo'] = $tipo;
            }
            if (!empty($cod_ventaFK)) {
                $atributos .= ", cod_ventaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_ventaFK;
                $nuevos_datos['cod_ventaFK'] = $cod_ventaFK;
            }
            if (!empty($monto_limite)) {
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
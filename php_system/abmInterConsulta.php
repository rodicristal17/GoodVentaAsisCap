<?php
    require("conexion.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");
    include("subir_foto_base64.php");

    date_default_timezone_set('America/Asuncion');

    function verificar($funt) {
        $user = $_POST['useru'];
        $user = utf8_decode($user);
        $pass = $_POST['passu'];

        $pass = str_replace("=", "+", $pass);
        $navegador = $_POST['navegador'];
        $navegador = utf8_decode($navegador);
        $resp = verificar_navegador($user, $navegador, $pass);
        if ($resp != "ok") {
            $informacion = array("1" => "UI");
            echo json_encode($informacion);
            exit;
        }

        switch ($funt) {
            case 'buscarInterConsultaPorPaciente':
                $paciente= isset($_POST['paciente']) ? utf8_decode($_POST['paciente']) : null;
                $filtros= array(
                    "paciente" => $paciente,
                    "cod_usuarioFK" => $user
                );
                
                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                buscarVistaPacienteConInterConsulta($filtros, $limite);
                break;
            case 'buscarInterConsultas':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $asunto= isset($_POST['asunto']) ? utf8_decode($_POST['asunto']) : null;
                $estado= isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $tipo= isset($_POST['tipo']) ? utf8_decode($_POST['tipo']) : null;
                $mencion= isset($_POST['mencion']) ? utf8_decode($_POST['mencion']) : false;
                $cod_clienteFK= isset($_POST['cod_clienteFK']) ? utf8_decode($_POST['cod_clienteFK']) : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? utf8_decode($_POST['cod_usuarioFK']) : null;
                $nombre_cliente= isset($_POST['nombre_cliente']) ? utf8_decode($_POST['nombre_cliente']) : null;

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'asunto'=> $asunto,
                    'estado'=> $estado,
                    'tipo'=> $tipo,
                    'mencion'=> $mencion,
                    'cod_clienteFK'=> $cod_clienteFK,
                    'cod_usuarioFK'=> $cod_usuarioFK,
                    'nombre_cliente'=> $nombre_cliente
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;
echo "Falta descomentar...";exit;
                obtenerVistaInterConsulta($filtros, $limite);
                break;
            case 'buscarInterConsultasYContenido':
                $cod_clienteFK= isset($_POST['cod_clienteFK']) ? utf8_decode($_POST['cod_clienteFK']) : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? utf8_decode($_POST['nombre_usuario']) : null;
                
                $filtros= array(
                    "cod_clienteFK" => $cod_clienteFK,
                    "cod_usuarioFK" => $user
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario);
                break;
            case 'nuevo/editar interconsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $asunto= isset($_POST['asunto']) ? utf8_decode($_POST['asunto']) : null;
                $estado= isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $tipo= isset($_POST['tipo']) ? utf8_decode($_POST['tipo']) : null;
                $cod_clienteFK= isset($_POST['cod_clienteFK']) ? utf8_decode($_POST['cod_clienteFK']) : null;

                $cod_interConsulta= abmInterConsulta($cod_interConsulta, $asunto, $estado, $tipo, $cod_clienteFK, $user, $user);
                echo json_encode(array("1" => "exito", "2" => $cod_interConsulta));
                break;
            case 'marcarMensajesLeido':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $registrosInterc= obtenerInterConsulta(array(
                    "cod_interConsulta" => $cod_interConsulta
                ));
                
                foreach ($registrosInterc as $valueInter) {
                    $registrosMens= obtenerMensaje(array(
                        'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                        'cod_usuarioFK_create' => $user
                    ), 0);

                    foreach ($registrosMens as $valueMens) {
                        abmMensaje($valueMens['cod_mensaje'], null, null, null, true);
                    }
                }
                echo json_encode(array("1" => "exito"));
                break;
            case 'buscarMensaje':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $cod_mensaje= isset($_POST['cod_mensaje']) ? utf8_decode($_POST['cod_mensaje']) : null;
                $contenido= isset($_POST['contenido']) ? utf8_decode($_POST['contenido']) : null;

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'cod_mensaje'=> $cod_mensaje,
                    'contenido'=> $contenido
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;
                
                obtenerVistaMensaje($filtros, $limite);
                break;
            case 'nuevo/editar mensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? utf8_decode($_POST['cod_mensaje']) : null;
                $contenido= isset($_POST['contenido']) ? utf8_decode($_POST['contenido']) : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                
                $cod_mensaje= abmMensaje($cod_mensaje, $contenido, $cod_interConsulta, $user);
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'subirImagenMensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? utf8_decode($_POST['cod_mensaje']) : null;
                $foto= isset($_POST['foto']) ? utf8_decode($_POST['foto']) : null;
                $ext= isset($_POST['ext']) ? utf8_decode($_POST['ext']) : null;
                subirImagenMensaje($cod_mensaje,$foto,$ext, 'url');
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'obtenerMenciones':
                $cod_mencion= isset($_POST['cod_mencion']) ? utf8_decode($_POST['cod_mencion']) : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? utf8_decode($_POST['cod_usuarioFK']) : null;
                $cod_mensajeFK= isset($_POST['cod_mensajeFK']) ? utf8_decode($_POST['cod_mensajeFK']) : null;

                $filtros= array(
                    'cod_mencion'=> $cod_mencion,
                    'cod_usuarioFK'=> $cod_usuarioFK,
                    'cod_mensajeFK'=> $cod_mensajeFK
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;
                
                obtenerVistaMencion($filtros, $limite);
                break;
            case 'nuevo/editar mencion':
                $cod_mencion= isset($_POST['cod_mencion']) ? utf8_decode($_POST['cod_mencion']) : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? utf8_decode($_POST['cod_usuarioFK']) : null;
                $cod_mensajeFK= isset($_POST['cod_mensajeFK']) ? utf8_decode($_POST['cod_mensajeFK']) : null;
                $isLeido= isset($_POST['isLeido']) ? utf8_decode($_POST['isLeido']) : null;

                abmMencion($cod_mencion, $cod_usuarioFK, $cod_mensajeFK, $isLeido);
                break;
            case 'buscarMasInterConsultasYContenido':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $offset= isset($_POST['offset']) ? utf8_decode($_POST['offset']) : null;
                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                $filtros= array(
                    "cod_interConsultaFK" => $cod_interConsulta,
                    "cod_usuarioFK" => $user
                );
                $vistaTarjetas= obtenerVistaTarjetaInterConsuta($filtros, $limite, $offset);

                // Se calcula si agregar o no el boton de ver mas
                $registrosMens= obtenerMensaje($filtros);
                $botonVerMas= "";
                if (count($registrosMens) > ($offset + $limite)) {
                    $botonVerMas= "<div style='width: 100%; justify-content: center;'>
                        <button class='btn btn-success' onclick='verMasMensajesInterconsulta(".$cod_interConsulta.", ".($offset + $limite).")'>Ver más mensajes...</button>
                        </div>";
                }
                
                $vistaTarjetas= $botonVerMas . $vistaTarjetas;
                echo json_encode(array("1" => "exito", "2" => $vistaTarjetas));
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario) {
        $pagina = "";
        $limiteMensajes= 10;
        
        // Se obtienen las interconsultas
        $registrosInterc= obtenerInterConsulta(array(
            "cod_clienteFK" => $filtros['cod_clienteFK']
        ), $limite);

        foreach ($registrosInterc as $valueInter) {
            $mencionesElemento= "";
            $menciones= array();
            
            // Se obtienen los mensajes
            $registrosMens= obtenerMensaje(array(
                'cod_interConsultaFK' => $valueInter['cod_interConsulta']
            ));
            
            $paginaMensajes= "";
            foreach ($registrosMens as $key => $valueMens) {
                // Obtiene todas las meciones
                $registrosMenc= obtenerMencion(array(
                    'cod_mensajeFK' => $valueMens['cod_mensaje']
                ), 0);
                
                foreach ($registrosMenc as $valueMenc) {
                    if (!in_array($valueMenc['nombre_persona'], $menciones) && $valueInter['cod_usuarioFK_create'] != $valueMenc['cod_usuarioFK']) {
                        $mencionesElemento .= '<li style="
                            background-color:#f2f2f2;
                            text-align: left;
                            margin-bottom:4px;
                            padding:5px 10px;
                            border-radius:4px;
                            font-size:13px;
                        ">'.$valueMenc['nombre_persona'].'</li>';
                        $menciones[] = $valueMenc['nombre_persona'];
                    }
                }
            }

            $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_usuarioFK' => $filtros['cod_usuarioFK']
                ), $limiteMensajes, 0);
            
            // Se asigna el estilo para asuntos con mensajes sin leer
            $styleMensajeNoLeido= "";
            if (intval($valueInter['cantMensajesNoLeidos']) > 0) {
                $styleMensajeNoLeido= "border: 10px solid #e1c247;";
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

            // Se crea el encabezado
            $pagina.= '<div class="sugerencias-container" style="display: grid;justify-content: center;" onclick="obtenerDetallesInterConsulta(this)">
            <div class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 1000px;'.$styleMensajeNoLeido.'">
            <div class="card-body">
            <h5 class="card-title">'.$valueInter['asunto'].' - '.$valueInter['nombre_persona'].'</h5>
            <div style="display: flex;">
            <div style="width: 50%;padding-top: 10px;border-top: 1px solid #ddd;">
            <strong>Mencionados</strong>
            '.$mencionesElemento.'
            </div>
            <div style="width: 50%;">
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
            <span id="td_datos_2" class="badge '.$claseEstado.' text-uppercase">'.$valueInter['estado'].'</span>
            </div>
            <div style="margin-bottom: 5px;">
            <span class="fw-bold">Tipo:</span>
            <span id="td_datos_3" class="badge badge-secondary text-uppercase">'.$valueInter['tipo'].'</span>
            </div>
            </div>
            </div>
            <div style="display: none;">
            <span id="td_datos_1">'.$valueInter['asunto'].'</span>
            <span id="td_datos_4">'.$valueInter['cod_interConsulta'].'</span>
            <span id="td_datos_5">'.$valueInter['cod_clienteFK'].'</span>
            </div>
            </div>
            </div>
            </div>
            <div id="contenedorMensajesInterConsulta'.$valueInter['cod_interConsulta'].'">';
            
            if (count($registrosMens) > ($limiteMensajes)) {
                $pagina .= "<div style='width: 100%; justify-content: center;'>
                    <button class='btn btn-success' onclick='verMasMensajesInterconsulta(".$valueInter['cod_interConsulta'].", $limiteMensajes)'>Ver más mensajes...</button>
                    </div>";
            }
            $pagina .= $paginaMensajes;

            // Se agrega el espacio para enviar un mensaje
            $fechaActual= new DateTime();
            $fechaActual= $fechaActual->format('Y-m-d\TH:i');

            $pagina .= '<div class="sugerencias-container" style="display: grid;justify-content: flex-end;">
                    <div class="card my-3" style="border-left: 5px solid #8BC34A;width: 500px;">
                      <div class="card-header d-flex justify-content-between align-items-center">
                          <span>'.$nombre_usuario.'</span>
                          <small class="text-secondary">
                            <input class="inputText" type="datetime-local" id="inptFechaAbmMensaje'.$valueInter['cod_interConsulta'].'" value="'.$fechaActual.'" style="margin-bottom: 5px;">
                          </small>
                      </div>
                      <div class="card-body">
                          <div style="display: flex;">
                            <div id="imgfotoAnexoInterchat" class="imgFotoProducto" onclick="vercerrarcargadefotos(\'fotoAnexoInterchat\')" style="background-image: url(\'/GoodVentaAsisCap/iconos/imagenphoto.png\');width:100px; height: 90px;margin-right: 5px;"></div>
                            <p id="inptContenidoAbmMensaje'.$valueInter['cod_interConsulta'].'" class="form-control mensaje-interconsulta" contenteditable="true" onfocus="marcarMensajeLeido(this);"></p>
                            <div style="width: 100px;margin-left: 10px;text-align: left;">
                              <input type="button" value="Enviar" class="btn1" onclick="verificarCamposMensaje('.$valueInter['cod_interConsulta'].')" style="background-color: rgb(76, 175, 80);width: 100%;">
                              <input type="button" value="Limpiar" class="btn1" onclick="limpiarcamposMensaje('.$valueInter['cod_interConsulta'].')" style="background-color: rgb(245, 59, 59);width: 100%;">
                            </div>
                          </div>
                      </div>
                    </div>
                  </div>';
            $pagina .= '</div>';
        }

        echo json_encode(array("1" => "exito", "2" => $pagina));
    }

    function buscarVistaPacienteConInterConsulta($filtros= array(), $limite= 0) {
        $registros= buscarPacienteConInterConsulta($filtros, $limite);

        $pagina= "";
        foreach ($registros as $key => $value) {
            $interConsultas= "";
            
            // Obtiene las interConsultas asociadas a este paciente
            $registrosInterconsulta= obtenerInterConsulta(array(
                "cod_clienteFK" => $value['cod_cliente']
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
                    border-top: 1px solid #ddd;
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
                                    WHERE ic.cod_clienteFK = cl.cod_cliente AND mc.cod_usuarioFK = $value)";
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
                $reg[$key]= utf8_encode($value);
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
        $regMensaje= obtenerMensaje($filtros, $limite);
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
                $miniatura_imagen= '<div id="imgfotoMensajeInterconsulta" class="imgFotoProducto" 
                    onclick="vercerrarcargadefotos(\'fotoMensajeInterconsulta\')" style="background-image: url('.$valueMens['url'].');margin-right: 5px;">
                    </div>';
            }
            
            $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: '.$posicion.';">
                    <div class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 500px;margin-left: 10px; margin-right: 10px;">
                      <div class="card-header d-flex justify-content-between align-items-center">
                          <span>'.$valueMens['nombre_persona'].'</span>
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

        return $paginaMensajes;
    }

    function obtenerVistaInterConsulta($filtros= array(), $limite= 0) {
        $cantRegistros= obtenerInterConsulta($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerInterConsulta($filtros, $limite);

        $pagina= '';
        $paginaMensajes= '';
        $styleName="tableRegistroSearch";
        foreach ($registros as $value) {
            $elementosInvolucrados= "";
            $involucrados= array();

            // Obtiene todos los mensajes de la interConsulta
            $regMensaje= obtenerMensaje($filtros);
            foreach ($regMensaje as $key => $valueMensj) {
                $elementosInvolucrados .= '<li style="
                    background-color:#f2f2f2;
                    margin-bottom:4px;
                    padding:5px 10px;
                    border-radius:4px;
                    font-size:13px;
                ">'.$valueMensj['nombre_persona'].'</li>';
                $involucrados[] = $valueMensj['nombre_persona'];

                // Obtiene las menciones
                $regMencion= obtenerMencion(array("cod_mensajeFK" => $value['cod_usuarioFK']), 0);
                foreach ($regMencion as $valueMenc) {
                    if (!in_array($valueMenc['nombre_persona'], $involucrados)) {
                        $elementosInvolucrados = '<li style="
                            background-color:#f2f2f2;
                            margin-bottom:4px;
                            padding:5px 10px;
                            border-radius:4px;
                            font-size:13px;
                        ">'.$valueMenc['nombre_persona'].'</li>';
                        $involucrados[] = $valueMenc['nombre_persona'];
                    }
                }
            }
            $styleName=CargarStyleTable($styleName);
            $pagina .= '<div class="card my-3" style="border-left: 5px solid #ff5722;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span></span>
                    <small class="text-secondary"><select class="inputSelect" id="inptTipoAbmInterConsulta" style="width:130px" value="'. $value['tipo'] .'">
                        <option value="clinico">CLINICO</option>
                        <option value="administrativo">ADMINISTRATIVO</option>
                    </select></small>
                    <span></span>
                    <small class="text-secondary"><select class="inputSelect" id="inptEstadoAbmInterConsulta" style="width:130px" value="'. $value['estado'] .'">
                        <option value="pendiente">PENDIENTE</option>
                        <option value="proceso">EN PROCESO</option>
                        <option value="finalizado">ADMINISTRATIVO</option>
                        <option value="inactivo">INACTIVO</option>
                    </select></small>
                </div>
                <div class="card-body">
                    <h5 class="card-title">'. $value['asunto'] .'</h5>
                    <p class="card-text">
                        <strong><ul style="list-style-type:none; padding:0; margin:0;">
                            <li style="
                                background-color:#f2f2f2;
                                margin-bottom:4px;
                                padding:5px 10px;
                                border-radius:4px;
                                font-size:13px;
                            ">'. $involucrados .'</li>
                        </ul></strong>
                    </p>
                </div>
                </div>';
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros));
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
                $reg[$key]= utf8_encode($value);
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
                $reg[$key]= utf8_encode($value);
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmMencion($cod_mencion, $cod_usuarioFK, $cod_mensajeFK, $isLeido) {
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
            $stmt->bind_param('s', $isLeido);
        } else {
            $sql = "INSERT INTO menciones (cod_usuarioFK, cod_mensajeFK) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ii', $cod_usuarioFK, $cod_mensajeFK);
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
                SELECT m.*, p.nombre_persona FROM mensaje m JOIN persona p ON p.cod_persona = m.cod_usuarioFK $sqlFiltro ORDER BY m.fecha_creacion DESC $limite
            ) AS subquery ORDER BY fecha_creacion ASC";

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
                $reg[$key]= utf8_encode($value);
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmMensaje($cod_mensaje, $contenido, $cod_interConsulta, $user) {
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
            $sql = "INSERT INTO mensaje (contenido, cod_interConsultaFK, cod_usuarioFK) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sii', $contenidoLimpiado, $cod_interConsulta, $user);
        } else {
            $sql= "UPDATE mensaje SET contenido= ? WHERE cod_mensaje = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('si', $contenidoLimpiado, $cod_mensaje);
        }
        
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }
        
        if (empty($cod_mensaje)) {
            $cod_mensaje = $stmt->insert_id;
        }

        // Guarda las menciones e incluye al creador
        $ids_menciones[] = $user;
        $ids_menciones = array_unique($ids_menciones);
        foreach ($ids_menciones as $value) {
            abmMencion(null, $value, $cod_mensaje, "false");
        }
        
        $stmt->close();        

        return $cod_mensaje;
    }

    function obtenerInterConsulta($filtros= [], $limite= 0) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}

            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'cod_usuarioFK':
                    $sqlFiltro .= "EXISTS(select cod_mensaje from mensaje m where m.cod_usuarioFK = $value) OR EXISTS(select cod_mencion from menciones me where me.cod_usuarioFK = $value)";
                    break;
                case 'cod_interConsulta':
                    $sqlFiltro .= "ic.cod_interConsulta = $value";
                    break;
                case 'estado':
                    $sqlFiltro .= "ic.estado = '$value'";
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

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT ic.*, 
            (SELECT nombre_persona from persona where cod_persona = ic.cod_clienteFK) as nombre_persona,
            (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) as nombre_persona_creador,
            (SELECT ci_cliente from cliente where cod_cliente = ic.cod_clienteFK) as cedula,
            (0) AS cantMensajesNoLeidos
            from interconsulta ic $sqlFiltro
            ORDER BY FIELD(ic.estado, 'proceso', 'pendiente', 'finalizado', 'inactivo'), cod_interConsulta DESC $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo $sql;
            echo "Error: ". mysqli_error($mysqli);
        }
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                $reg[$key]= utf8_encode($value);
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmInterConsulta($cod_interConsulta, $asunto, $estado, $tipo, $cod_clienteFK,$cod_usuarioFK_create, $cod_usuarioFK_edit) {
        $mysqli = conectar_al_servidor();

        if (empty($cod_interConsulta)) {
            $sql = "INSERT INTO interconsulta (asunto, estado, tipo, cod_clienteFK,cod_usuarioFK_create, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssii',$asunto, $estado, $tipo, $cod_clienteFK,$cod_usuarioFK_create);
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
            if (!empty($cod_clienteFK)) {
                $atributos .= ", cod_clienteFK= ?";
                $ss .= "i";
                $parametros[] = $cod_clienteFK;
                $nuevos_datos['cod_clienteFK'] = $cod_clienteFK;
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
            // Se actualizo la interconsulta, se compara los datos para registrar los cambios en la tabla mensaje
            foreach ($nuevos_datos as $key => $value) {
                if ($interconsulta_original[0][$key] != $value) {
                    // Registrar cambio en un mensaje
                    $mensaje .= ' el campo '.$key.' de '.$interconsulta_original[0][$key].' a '.$value.', ';
                }
            }

            $mensaje = substr($mensaje, 0, -2).'.';
            abmMensaje(null, $mensaje, $cod_interConsulta, $cod_usuarioFK_edit);
        }

        $stmt->close();
        return $cod_interConsulta;
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
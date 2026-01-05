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

        $fechaActual= new DateTime();
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
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? utf8_decode($_POST['cod_usuarioFK']) : null;
                $nombre_cliente= isset($_POST['nombre_cliente']) ? utf8_decode($_POST['nombre_cliente']) : null;
                $nombre_responsable= isset($_POST['nombre_responsable']) ? utf8_decode($_POST['nombre_responsable']) : null;
                $ocultar_inactivos= isset($_POST['ocultar_inactivos']) ? utf8_decode($_POST['ocultar_inactivos']) : null;
                $usuario_vinculado= isset($_POST['usuario_vinculado']) ? utf8_decode($_POST['usuario_vinculado']) : null;

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'asunto'=> $asunto,
                    'estado'=> $estado,
                    'tipo'=> $tipo,
                    'mencion'=> $mencion,
                    'cod_ventaFK'=> $cod_ventaFK,
                    'cod_usuarioFK'=> $cod_usuarioFK,
                    'nombre_cliente'=> $nombre_cliente,
                    'nombre_responsable'=> $nombre_responsable,
                    'ocultar_inactivos'=> $ocultar_inactivos,
                    'usuario_vinculado'=> $usuario_vinculado,
                    'fecha_limite' => $fechaActual->format('Y-m-d H:i:s')
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                obtenerVistaInterConsulta($filtros, $limite);
                break;
            case 'buscarInterConsultasYContenido':
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? utf8_decode($_POST['nombre_usuario']) : null;
                
                $filtros= array(
                    "cod_ventaFK" => $cod_ventaFK,
                    "cod_interConsulta" => $cod_interConsulta,
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
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;

                $cod_interConsulta= abmInterConsulta($cod_interConsulta, $asunto, $estado, $tipo, $cod_ventaFK, $user, $user);
                echo json_encode(array("1" => "exito", "2" => $cod_interConsulta));
                break;
            case 'marcarMensajesLeido':
                $cod_interConsulta= utf8_decode($_POST['cod_interConsulta']);

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
                        abmMencion($valueMenc['cod_mencion'], null, null, 1);
                    }
                }
                echo json_encode(array("1" => "exito"));
                break;
            case 'buscarMensaje':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $cod_mensaje= isset($_POST['cod_mensaje']) ? utf8_decode($_POST['cod_mensaje']) : null;
                $contenido= isset($_POST['contenido']) ? utf8_decode($_POST['contenido']) : null;
                $fecha_creacion= isset($_POST['fecha_creacion']) ? utf8_decode($_POST['fecha_creacion']) : "<= NOW()";

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'cod_mensaje'=> $cod_mensaje,
                    'contenido'=> $contenido,
                    'fecha_creacion' => $fecha_creacion,
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;
                
                obtenerVistaMensaje($filtros, $limite);
                break;
            case 'nuevo/editar mensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? utf8_decode($_POST['cod_mensaje']) : null;
                $contenido= isset($_POST['contenido']) ? utf8_decode($_POST['contenido']) : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? utf8_decode($_POST['cod_interConsulta']) : null;
                $fecha_creacion= isset($_POST['fecha_creacion']) ? utf8_decode($_POST['fecha_creacion']) : 'NOW()';
                
                $cod_mensaje= abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user);
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'subirImagenMensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? utf8_decode($_POST['cod_mensaje']) : null;
                $foto= isset($_POST['foto']) ? utf8_decode($_POST['foto']) : null;
                $ext= isset($_POST['ext']) ? utf8_decode($_POST['ext']) : null;
                subirImagenMensaje($cod_mensaje,$foto,$ext, 'url');
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
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

                $fechaActual= new DateTime();
                $filtros= array(
                    "cod_interConsultaFK" => $cod_interConsulta,
                    "cod_usuarioFK" => $user,
                    'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
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
        $limiteMensajes= 5;
        
        // Se obtienen las interconsultas
        $registrosInterc= obtenerInterConsulta(array(
            "cod_ventaFK" => $filtros['cod_ventaFK'],
            "cod_usuarioFK" => $filtros['cod_usuarioFK'],
            "cod_interConsulta" => $filtros['cod_interConsulta'],
        ), $limite);

        foreach ($registrosInterc as $valueInter) {
            $mencionesElemento= "";
            $menciones= array();
            
            // Se obtienen los mensajes
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
                'cod_interConsultaFK' => $valueInter['cod_interConsulta']
            ));
            
            $ultimoMensaje= end($registrosMens);

            $paginaMensajes= "";
            // Obtiene todas las menciones
            $registrosMenc= obtenerMencion(array(
                'cod_mensajeFK' => $ultimoMensaje['cod_mensaje']
            ), 0);

            foreach ($registrosMenc as $valueMenc) {
                if (!in_array($valueMenc['nombre_persona'], $menciones)) {
                    $mencionesElemento .= '<li style="
                        background-color: #f2f2f2;
                        text-align: left;
                        margin-bottom:4px;
                        padding:5px 10px;
                        border-radius:4px;
                        font-size:13px;
                        display: '. (($valueInter['cod_usuarioFK_create'] != $valueMenc['cod_usuarioFK']) ? "flex" : "none").';
                        justify-content: space-between;
                    ">'.$valueMenc['nombre_persona'].
                    (($valueMenc['isLeido'] == 1) ? '<i class="fa-solid fa-check-double" style="color: #0cdd23;"></i>' : '').
                    '</li>';
                    $menciones[] = $valueMenc['nombre_persona'];
                }
            }

            $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_usuarioFK' => $filtros['cod_usuarioFK']
                ), $limiteMensajes, 0);
            
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
            $pagina.= '<div class="sugerencias-container" style="display: grid;justify-content: center;" onclick="obtenerDetallesInterConsulta(this)">
                <div id="contenedorEncabezadoInterConsulta'.$valueInter['cod_interConsulta'].'" class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 1000px;'.$styleMensajeNoLeido.'">
            <div class="card-body">
            <h5 class="card-title">'.$valueInter['asunto'].(empty($valueInter['cod_ventaFK']) ? '' : ' - '.$valueInter['nombre_persona']).'</h5>
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
            <div style="margin-bottom: 5px;">
                <span class="fw-bold">Cod. InterConsulta:</span>
                <span class="text-uppercase" id="td_datos_6">'.$valueInter['cod_interConsulta'].'</span>
            </div>';
            if ($valueInter['tipo'] != 'interno' && $valueInter['tipo'] != 'resolucion') {
                $pagina .= '<div style="margin-bottom: 5px;">
                <span class="fw-bold">Cod. Cliente:</span>
                <span class="text-uppercase" id="td_datos_6">'.$valueInter['cod_clienteFK'].'</span>
                </div>
                <div style="margin-bottom: 5px;">
                <span class="fw-bold">Cod. Venta:</span>
                <span class="text-uppercase" id="td_datos_6">'.$valueInter['cod_ventaFK'].'</span>
                </div>';
            }
            $pagina .= '</div>
            </div>
            <div style="display: none;">
            <span id="td_datos_1">'.$valueInter['asunto'].'</span>
            <span id="td_datos_4">'.$valueInter['cod_interConsulta'].'</span>
            <span id="td_datos_5">'.$valueInter['cod_ventaFK'].'</span>
            <span id="td_datos_7">'.$valueInter['nombre_persona'].'</span>
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
                          <span>'.utf8_encode($nombre_usuario).'</span>
                          <small class="text-secondary">
                            <input class="inputText" type="datetime-local" id="inptFechaAbmMensaje'.$valueInter['cod_interConsulta'].'" value="'.$fechaActual.'" style="margin-bottom: 5px;">
                          </small>
                      </div>
                      <div class="card-body">
                          <div style="display: flex;">
                            <div id="imgfotoAnexoInterchat" class="imgFotoProducto" onclick="vercerrarcargadefotos(\'fotoAnexoInterchat\')" style="background-image: url(\'/GoodVentaAsisCap/iconos/subir_imagen.png\');width:100px; height: 90px;margin-right: 5px;"></div>
                            <p id="inptContenidoAbmMensaje'.$valueInter['cod_interConsulta'].'" class="form-control mensaje-interconsulta" contenteditable="true" onfocus="marcarMensajeLeido(this);"></p>
                            <div style="width: 100px;margin-left: 10px;text-align: left;">
                              <input id="btnEnviarContenidoAbmMensaje'.$valueInter['cod_interConsulta'].'" type="button" value="Enviar" class="btn1" onclick="verificarCamposMensaje('.$valueInter['cod_interConsulta'].')" style="background-color: rgb(76, 175, 80);width: 100%;">
                              <input type="button" value="Limpiar" class="btn1" onclick="limpiarcamposMensaje('.$valueInter['cod_interConsulta'].')" style="background-color: rgb(245, 59, 59);width: 100%;">
                            </div>
                          </div>
                      </div>
                    </div>
                  </div>';
            $pagina .= '</div>';
        }   

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $filtros['cod_ventaFK'], "4" => $filtros['cod_interConsulta']));
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
            
            $paginaMensajes .= '<div class="sugerencias-container" style="display: grid;justify-content: '.$posicion.';">
                    <div class="card my-3" style="border-left: 5px solid '.$colorTarjeta.';width: 500px;margin-left: 10px; margin-right: 10px;">
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
            
            $formatAsunto= '<p style="font-size: 9pt;">'.$value['asunto'].$cantMensajesNoLeidosOtrosUsuarios.'</p>';
            if (intval($value['cantMensajesNoLeidos']) > 0) {
                $style = 'background-color: #ff5050;  color: #ffffff;';
                $cant_mensajes_no_leidos += intval($value['cantMensajesNoLeidos']);
                $formatAsunto= '<b style="font-size: 9pt;">'.$value['asunto'].$cantMensajesNoLeidosOtrosUsuarios.'</b>';
            }
            
            $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding="1">
                <tr onclick="obtenerDatosInterConsulta(this)">
                    <td id="td_id" style="width: 5%;'.$styleInterno.'">'.$value['cod_interConsulta'].'</td>
                    <td id="td_datos_1" style="width: 25%;'.$style.'">'.$formatAsunto.'</td>
                    <td id="td_datos_4" style="display: none;'.$style.'">'.$value['cod_ventaFK'].'</td>
                    <td id="td_datos_5" style="width: 15%;'.$style.'">'.$value['nombre_persona'].'</td>
                    <td id="td_datos_2" style="width: 10%;'.$style.'">'.$value['estado'].'</td>
                    <td id="td_datos_6" style="width: 15%;'.$style.'">'.$value['tipo'].'</td>
                    <td id="td_datos_7" style="display: none;'.$style.'">'.$value['cod_clienteFK'].'</td>
                    <td style="width: 10%;'.$style.'">'.$value['fecha_creacion'].'</td>
                    <td style="width: 15%;'.$style.'">'.$value['nombre_persona_creador'].'</td>
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
            $stmt->bind_param('si', $isLeido, $cod_mencion);
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
                p.nombre_persona FROM mensaje m JOIN persona p ON p.cod_persona = m.cod_usuarioFK $sqlFiltro ORDER BY m.fecha_creacion DESC $limite
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
                // Solo codificar si NO es UTF-8 válido
                if (!mb_check_encoding($value, 'UTF-8')) {
                    $reg[$key] = utf8_encode($value);
                } else {
                    $reg[$key] = $value;
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
        foreach ($registrosMens as $valueMens) {
            $registrosMenc= obtenerMencion(array(
                "cod_mensajeFK" => $valueMens['cod_mensaje'],
                "isLeido" => 0
            ), 0);

            foreach ($registrosMenc as $value) {
                $ids_menciones[] = $value['cod_usuarioFK'];
            }
        }

        // Guarda las menciones e incluye al creador
        $ids_menciones = array_unique($ids_menciones);
        foreach ($ids_menciones as $value) {
            if ($value === $user) {
                abmMencion(null, $user, $cod_mensaje, 1);
            } else {
                abmMencion(null, $value, $cod_mensaje, 0);
            }
        }
        
        $stmt->close();        

        return $cod_mensaje;
    }

    function obtenerInterConsulta($filtros= [], $limite= 0) {
        $sqlFiltro= "";
        $sqlFiltroMenciones= "";
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
                case 'estado':
                    $sqlFiltro .= "ic.estado = '$value'";
                    break;
                case 'ocultar_inactivos':
                    $sqlFiltro .= "ic.estado != 'inactivo'";
                    break;
                case 'nombre_responsable':
                    $sqlFiltro .= "(SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE '%$value%'";
                    break;
                case 'nombre_cliente':
                    $sqlFiltro .= "CONCAT(
                        (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK)
                    ) LIKE '%$value%'";
                    break;
                case 'usuario_vinculado':
                    $sqlFiltro .= "EXISTS(select cod_mencion from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mc.cod_usuarioFK = $value)";
                    break;
                case 'fecha_limite':
                    $sqlFiltroMenciones .= " AND mj.fecha_creacion <= '$value' ";
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
            (SELECT vt.cod_clienteFK from venta vt WHERE vt.cod_venta = ic.cod_ventaFK) AS cod_clienteFK,
            (SELECT p.nombre_persona from venta vt JOIN persona p where p.cod_persona = vt.cod_clienteFK AND vt.cod_venta = ic.cod_ventaFK) as nombre_persona,
            (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) as nombre_persona_creador,
            (SELECT c.ci_cliente from cliente c JOIN venta vt where c.cod_cliente = vt.cod_clienteFK AND vt.cod_venta = ic.cod_ventaFK) as cedula,
            (SELECT COUNT(mc.cod_mencion)
                FROM menciones mc
                JOIN mensaje mj 
                ON mc.cod_mensajeFK = mj.cod_mensaje
                WHERE mc.isLeido = 0
                AND mj.cod_interConsultaFK = ic.cod_interConsulta
                AND mj.fecha_creacion = (
                    SELECT MAX(mj2.fecha_creacion)
                    FROM mensaje mj2
                    WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta
                )
            ) AS cantMensajesNoLeidosOtrosUsuarios,
            (SELECT COUNT(cod_mencion) from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mc.isLeido = 0 $sqlFiltroMenciones AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mj.fecha_creacion = (
                SELECT MAX(mj2.fecha_creacion)
                FROM mensaje mj2
                WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta
            )) AS cantMensajesNoLeidos
            from interconsulta ic $sqlFiltro
            ORDER BY 
            (SELECT COUNT(cod_mencion) from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mc.isLeido = 0 $sqlFiltroMenciones AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mj.fecha_creacion = (
                SELECT MAX(mj2.fecha_creacion)
                FROM mensaje mj2
                WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta
            )) DESC,
            FIELD(ic.estado, 'proceso', 'pendiente', 'finalizado', 'inactivo'),
            cod_interConsulta DESC $limite";

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
                $reg[$key]= utf8_encode($value);
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmInterConsulta($cod_interConsulta, $asunto, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_usuarioFK_edit) {
        $mysqli = conectar_al_servidor();

        if (empty($cod_interConsulta)) {
            $sql = "INSERT INTO interconsulta (asunto, estado, tipo, cod_ventaFK,cod_usuarioFK_create, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssii',$asunto, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create);
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
            if (!empty($cod_ventaFK)) {
                $atributos .= ", cod_ventaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_ventaFK;
                $nuevos_datos['cod_ventaFK'] = $cod_ventaFK;
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

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
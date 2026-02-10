<?php
    require("conexion.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");

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
            case "nuevo":
                $cod_ventaFK = isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_tipo_trabajoFK = isset($_POST['cod_tipo_trabajoFK']) ? utf8_decode($_POST['cod_tipo_trabajoFK']) : null;
                $observacion = isset($_POST['observacion']) ? utf8_decode($_POST['observacion']) : '';
                $colorimetro = isset($_POST['colorimetro']) ? utf8_decode($_POST['colorimetro']) : '';
                $costo = isset($_POST['costo']) ? utf8_decode($_POST['costo']) : 0.0;
                $fecha_entrega = isset($_POST['fecha_entrega']) ? utf8_decode($_POST['fecha_entrega']) : null;
                $fecha_retiro = isset($_POST['fecha_retiro']) ? utf8_decode($_POST['fecha_retiro']) : null;
                $estado = isset($_POST['estado']) ? utf8_decode($_POST['estado']) : 'pendiente';
                $cod_especialistaFK = isset($_POST['cod_especialistaFK']) ? utf8_decode($_POST['cod_especialistaFK']) : null;
                $cod_mecanicoDentalFK = isset($_POST['cod_mecanicoDentalFK']) ? utf8_decode($_POST['cod_mecanicoDentalFK']) : null;
                $cod_localFK = isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;

                $cod_trabajo_mecanico_dental = abmTrabajoMecanicoDental($cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro, $estado, $user, $cod_especialistaFK,$cod_mecanicoDentalFK,$cod_localFK,null);
                echo json_encode(array("1" => "exito", "cod_trabajo_mecanico_dental" => $cod_trabajo_mecanico_dental, "cod_mecanico_dental" => $cod_mecanicoDentalFK));
                break;
            case "editar":
                $cod_trabajo_mecanico_dental = $_POST['cod_trabajo_mecanico_dental'];
                $cod_trabajo_mecanico_dental = utf8_decode($cod_trabajo_mecanico_dental);
                $cod_ventaFK = isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_tipo_trabajoFK = isset($_POST['cod_tipo_trabajoFK']) ? utf8_decode($_POST['cod_tipo_trabajoFK']) : null;
                $observacion = isset($_POST['observacion']) ? utf8_decode($_POST['observacion']) : null;
                $colorimetro = isset($_POST['colorimetro']) ? utf8_decode($_POST['colorimetro']) : null;
                $costo = isset($_POST['costo']) ? utf8_decode($_POST['costo']) : null;
                $fecha_entrega = isset($_POST['fecha_entrega']) ? utf8_decode($_POST['fecha_entrega']) : null;
                $fecha_retiro = isset($_POST['fecha_retiro']) ? utf8_decode($_POST['fecha_retiro']) : null;
                $estado = isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $cod_especialistaFK = isset($_POST['cod_especialistaFK']) ? utf8_decode($_POST['cod_especialistaFK']) : null;
                $cod_mecanicoDentalFK = isset($_POST['cod_mecanicoDentalFK']) ? utf8_decode($_POST['cod_mecanicoDentalFK']) : null;
                $cod_localFK = isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;

                $cod_trabajo_mecanico_dental = abmTrabajoMecanicoDental($cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro, $estado,$user,$cod_especialistaFK,$cod_mecanicoDentalFK,$cod_localFK,$cod_trabajo_mecanico_dental);
                echo json_encode(array("1" => "exito", "cod_trabajo_mecanico_dental" => $cod_trabajo_mecanico_dental, "cod_mecanico_dental" => $cod_mecanicoDentalFK));
                break;
            case "buscar":
                $cod_trabajo_mecanico_dental = isset($_POST['cod_trabajo_mecanico_dental']) ? utf8_decode($_POST['cod_trabajo_mecanico_dental']) : null;
                $cod_ventaFK = isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_tipo_trabajoFK = isset($_POST['cod_tipo_trabajoFK']) ? utf8_decode($_POST['cod_tipo_trabajoFK']) : null;
                $tipo_trabajo = isset($_POST['tipo_trabajo']) ? utf8_decode($_POST['tipo_trabajo']) : null;
                $ocultar_inactivo = isset($_POST['ocultar_inactivo']) ? utf8_decode($_POST['ocultar_inactivo']) : null;
                $nombre_paciente = isset($_POST['nombre_paciente']) ? utf8_decode($_POST['nombre_paciente']) : null;
                $nombre_mecanico = isset($_POST['nombre_mecanico']) ? utf8_decode($_POST['nombre_mecanico']) : null;
                $estado = isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $cod_especialistaFK = isset($_POST['cod_especialistaFK']) ? utf8_decode($_POST['cod_especialistaFK']) : null;
                $cod_localFK = isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;

                $filtros = array(
                    'cod_trabajo_mecanico_dental' => $cod_trabajo_mecanico_dental,
                    'cod_ventaFK' => $cod_ventaFK,
                    'cod_tipo_trabajoFK' => $cod_tipo_trabajoFK,
                    'tipo_trabajo' => $tipo_trabajo,
                    'nombre_mecanico' => $nombre_mecanico,
                    'estado' => $estado,
                    'ocultar_inactivo' => $ocultar_inactivo,
                    'nombre_paciente' => $nombre_paciente,
                    'cod_localFK' => $cod_localFK,
                    'cod_especialistaFK' => $cod_especialistaFK
                );

                $fecha_desde = isset($_POST['fecha_desde']) ? utf8_decode($_POST['fecha_desde']) : null;
                $fecha_hasta = isset($_POST['fecha_hasta']) ? utf8_decode($_POST['fecha_hasta']) : null;
                $filtro_fecha = isset($_POST['filtro_fecha']) ? utf8_decode($_POST['filtro_fecha']) : null;
                if ($filtro_fecha == "2") {
                    $filtros['fecha_entrega_desde'] = $fecha_desde;
                    $filtros['fecha_entrega_hasta'] = $fecha_hasta;
                } else if($filtro_fecha == "3") {
                    $filtros['fecha_retiro_desde'] = $fecha_desde;
                    $filtros['fecha_retiro_hasta'] = $fecha_hasta;
                }
                
                $limite = isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                obtenerVistaTrabajoMecanicoDental($filtros, $limite);
                break;
            case 'nuevo_tipo_trabajo':
                $descripcion = utf8_decode($_POST['descripcion']);
                $estado = isset($_POST['estado']) ? utf8_decode($_POST['estado']) : 'activo';
                $cod_tipo_trabajo_mecanico_dental= abmTipoTrabajo(null, $descripcion, $estado);
                echo json_encode(array("1" => "exito", "cod_tipo_trabajo_mecanico_dental" => $cod_tipo_trabajo_mecanico_dental));
                break;
            case 'editar_tipo_trabajo':
                $cod_tipo_trabajo = isset($_POST['cod_tipo_trabajo']) ? utf8_decode($_POST['cod_tipo_trabajo']) : null;
                $descripcion = isset($_POST['descripcion']) ? utf8_decode($_POST['descripcion']) : null;
                $estado = utf8_decode($_POST['estado']);
                $cod_tipo_trabajo_mecanico_dental= abmTipoTrabajo($cod_tipo_trabajo, $descripcion, $estado);
                echo json_encode(array("1" => "exito", "cod_tipo_trabajo_mecanico_dental" => $cod_tipo_trabajo_mecanico_dental));
                break;
            case 'buscar_tipo_trabajo':
                $cod_tipo_trabajo = isset($_POST['cod_tipo_trabajo']) ? utf8_decode($_POST['cod_tipo_trabajo']) : null;
                $descripcion = isset($_POST['descripcion']) ? utf8_decode($_POST['descripcion']) : null;
                $filtros= array(
                    'cod_tipo_trabajo'=> $cod_tipo_trabajo,
                    'descripcion'=> $descripcion
                );
                obtenerVistaTipoTrabajo($filtros);
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerVistaTrabajoMecanicoDental($filtros, $limite = 0) {
        $registros = obtenerTrabajoMecanicosDentales($filtros);
        $totalRegistros = count($registros);
        $registros = obtenerTrabajoMecanicosDentales($filtros, $limite);

        $pagina= "";
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            $styleName=CargarStyleTable($styleName);
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='ObtenerdatosTrabajoMecanicoDental(this)'>
            <td id='td_id' style='width: 10%;'>".$value['cod_trabajo_mecanico_dental']."</td>
            <td id='td_datos_1' style='width:20%' class='tdRegistroSearch' >".$value['nombre_paciente']."</td>
            <td id='td_datos_12' style='width:15%' class='tdRegistroSearch' >".$value['nombre_mecanico']."</td>
            <td id='td_datos_3' style='width:20%' class='tdRegistroSearch' >".$value['nombre_tipo_trabajo']."</td>
            <td id='td_datos_20' style='width: 15%;' class='tdRegistroSearch' >".$value['nombre_local']."</td>
            <td id='td_datos_11' style='width:10%' class='tdRegistroSearch' >".ucfirst($value['estado'])."</td>
            <td id='td_datos_4' style='display: none;' class='tdRegistroSearch' >".$value['fecha_entrega']."</td>
            <td id='td_datos_5' style='display: none;' class='tdRegistroSearch' >".$value['fecha_retiro']."</td>
            <td id='td_datos_6' style='display: none;' class='tdRegistroSearch' >".$value['colorimetro']."</td>
            <td id='td_datos_7' style='display: none;' class='tdRegistroSearch' >".$value['costo']."</td>
            <td id='td_datos_8' style='display: none;' class='tdRegistroSearch' >".$value['observacion']."</td>
            <td id='td_datos_9' style='display: none;' class='tdRegistroSearch' >".$value['cod_ventaFK']."</td>
            <td id='td_datos_10' style='display: none;' class='tdRegistroSearch' >".$value['cod_mecanico_dental']."</td>
            <td id='td_datos_13' style='display: none;' class='tdRegistroSearch' >".$value['fecha_creacion']."</td>
            <td id='td_datos_14' style='display: none;' class='tdRegistroSearch' >".$value['nombre_usuario_creat']."</td>
            <td id='td_datos_15' style='display: none;' class='tdRegistroSearch' >".$value['fecha_edit']."</td>
            <td id='td_datos_16' style='display: none;' class='tdRegistroSearch' >".$value['nombre_usuario_edit']."</td>
            <td id='td_datos_17' style='display: none;' class='tdRegistroSearch' >".$value['ci_cliente']."</td>
            <td id='td_datos_18' style='display: none;' class='tdRegistroSearch' >".$value['cod_especialistaFK']."</td>
            <td id='td_datos_19' style='display: none;' class='tdRegistroSearch' >".$value['nombre_especialista']."</td>
            <td id='td_datos_21' style='display: none;' class='tdRegistroSearch' >".$value['cod_localFK']."</td>
            </tr>
            </table>";
        }
        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => count($registros), "4" => $totalRegistros));
        exit;
    }

    function obtenerVistaTipoTrabajo($filtros) {
        $registros= obtenerTiposTrabajo($filtros, 0);
                
        $pagina= "";
        $optionesHTML= "";
        $styleName="tableRegistroSearch";
        foreach ($registros as $key => $value) {
            $styleName=CargarStyleTable($styleName);
            $pagina.="
                <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
                <tr id='tbSelecRegistro' onclick='ObtenerdatosTipoTrabajo(this)'>
                <td id='td_id' style='display:none;'>".$value['cod_tipo_trabajo_mecanico_dental']."</td>
                <td id='td_datos_1'style='width:25%' class='tdRegistroSearch' >".$value['descripcion']."</td>
                <td  id='td_datos_2' style='display:none'>".$value['estado']."</td>
                </tr>
                </table>";
            if ($value['estado'] == 'activo') {
                $optionesHTML .= "<option id='".$value['cod_tipo_trabajo_mecanico_dental']."'>".$value['descripcion']."</option>";
            }
        }
        echo json_encode(array("1" => "exito", "2" => $pagina, "4" => $optionesHTML));
    }

    function obtenerTiposTrabajo($filtros, $limite= 0) {
        $sqlFiltro = "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "$key = $value";
                    } else {
                        $sqlFiltro .= "$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql = "SELECT * FROM tipo_trabajo_mecanico_dental $sqlFiltro ORDER BY descripcion ASC $limite";

        $mysqli = conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al buscar: " . $stmt->error, "sql" => $sql);
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

    function abmTipoTrabajo($cod_tipo_trabajo= null, $descripcion, $estado) {
        $mysqli = conectar_al_servidor();

        if (empty($cod_tipo_trabajo)) {
            $sql = "INSERT INTO tipo_trabajo_mecanico_dental (descripcion, estado) 
                VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ss', $descripcion, $estado);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            if ($descripcion != null) {$atributos .= ", descripcion = ?"; $ss .= "s"; $parametros[] = $descripcion;}
            if ($estado != null) {$atributos .= ", estado = ?"; $ss .= "s"; $parametros[] = $estado;}

            $atributos = substr($atributos, 2);
            $parametros[] = $cod_tipo_trabajo;
            $ss .= "i";

            $sql = "UPDATE tipo_trabajo_mecanico_dental SET $atributos WHERE cod_tipo_trabajo_mecanico_dental = ?";
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

        $cod_tipo_trabajo_mecanico_dental = empty($cod_tipo_trabajo) ? $stmt->insert_id : $cod_tipo_trabajo;
        
        $stmt->close();
        return $cod_tipo_trabajo_mecanico_dental;
    }

    function obtenerTrabajoMecanicosDentales($filtros, $limite = 0) {
        $sqlFiltro = "";

        foreach ($filtros as $key => $value) {
            if (!$value) {
                continue;
            }

            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'ocultar_inactivo':
                    $sqlFiltro .= "tmd.estado != 'inactivo'";
                    break;
                case 'fecha_entrega_desde':
                    $sqlFiltro .= "DATE(tmd.fecha_entrega) >= '$value'";
                    break;
                case 'fecha_entrega_hasta':
                    $sqlFiltro .= "DATE(tmd.fecha_entrega) <= '$value'";
                    break;
                case 'fecha_retiro_desde':
                    $sqlFiltro .= "DATE(tmd.fecha_retiro) >= '$value'";
                    break;
                case 'fecha_retiro_hasta':
                    $sqlFiltro .= "DATE(tmd.fecha_retiro) <= '$value'";
                    break;
                case 'tipo_trabajo':
                    $sqlFiltro .= "t.descripcion like '%$value%'";
                    break;
                case 'estado':
                    $sqlFiltro .= "tmd.estado = '$value'";
                    break;
                case 'nombre_paciente':
                    $sqlFiltro .= '(SELECT COUNT(*) 
                        FROM persona 
                        WHERE cod_persona = cl.cod_cliente AND (nombre_persona LIKE "%'.$value.'%" OR cl.ci_cliente LIKE "%'.$value.'%")) > 0';
                    break;
                case 'nombre_mecanico':
                    $sqlFiltro .= 'p.nombre_persona like "%'.$value.'%"';
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "tmd.$key = $value";
                    } else {
                        $sqlFiltro .= "tmd.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql = "SELECT tmd.*, t.descripcion as nombre_tipo_trabajo, p.nombre_persona AS nombre_mecanico, md.cod_mecanico_dental,
        (SELECT nombre_persona FROM persona WHERE cod_persona = tmd.cod_usuarioFK_create) AS nombre_usuario_creat,
        (SELECT nombre_persona FROM persona WHERE cod_persona = tmd.cod_usuarioFK_edit) AS nombre_usuario_edit,
        (SELECT nombre_persona FROM persona WHERE cod_persona = tmd.cod_especialistaFK) AS nombre_especialista,
        (SELECT Nombre FROM local WHERE cod_local = tmd.cod_localFK) AS nombre_local,
        cl.ci_cliente, (SELECT nombre_persona FROM persona WHERE cod_persona = cl.cod_cliente) AS nombre_paciente
         FROM trabajo_mecanico_dental tmd 
         JOIN tipo_trabajo_mecanico_dental t ON t.cod_tipo_trabajo_mecanico_dental = tmd.cod_tipo_trabajoFK 
         JOIN mecanico_dental md ON md.cod_mecanico_dental = tmd.cod_mecanicoDentalFK 
         JOIN persona p ON p.cod_persona = md.cod_personaFK 
         JOIN venta vt ON vt.cod_venta = tmd.cod_ventaFK
         JOIN cliente cl ON cl.cod_cliente = vt.cod_clienteFK $sqlFiltro ORDER BY tmd.cod_trabajo_mecanico_dental DESC $limite";
        
        $mysqli = conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al buscar: " . $stmt->error, "sql" => $sql);
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

    function abmTrabajoMecanicoDental($cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro, $estado,$cod_usuario,$cod_especialistaFK,$cod_mecanicoDentalFK,$cod_localFK,$cod_trabajo_mecanico_dental) {
        $mysqli = conectar_al_servidor();
        if ($cod_trabajo_mecanico_dental == null) {
            $sql = "INSERT INTO trabajo_mecanico_dental (cod_ventaFK, cod_tipo_trabajoFK, observacion, colorimetro, costo, fecha_entrega, fecha_retiro, estado, cod_usuarioFK_create, cod_especialistaFK, cod_localFK, cod_mecanicoDentalFK) 
                    VALUES (?, ?, ?, ?, ?, ?, ?,?, ?, ?,?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iisssssssiii', $cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro,$estado,$cod_usuario,$cod_especialistaFK,$cod_localFK,$cod_mecanicoDentalFK);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            // Agrega los datos de auditoria
            $atributos .= " cod_usuarioFK_edit = ?";
            $ss .= "i";
            $parametros[] = $cod_usuario;
            $atributos .= ", fecha_edit = NOW()";

            if ($cod_ventaFK != null) {$atributos .= ", cod_ventaFK = ?"; $ss .= "i"; $parametros[] = $cod_ventaFK;}
            if ($cod_tipo_trabajoFK != null) {$atributos .= ", cod_tipo_trabajoFK = ?"; $ss .= "i"; $parametros[] = $cod_tipo_trabajoFK;}
            if ($observacion != null) {$atributos .= ", observacion = ?"; $ss .= "s"; $parametros[] = $observacion;}
            if ($colorimetro != null) {$atributos .= ", colorimetro = ?"; $ss .= "s"; $parametros[] = $colorimetro;}
            if ($costo != null) {$atributos .= ", costo = ?"; $ss .= "d"; $parametros[] = $costo;}
            if ($fecha_entrega != null) {$atributos .= ", fecha_entrega = ?"; $ss .= "s"; $parametros[] = $fecha_entrega;}
            if ($fecha_retiro != null) {$atributos .= ", fecha_retiro = ?"; $ss .= "s"; $parametros[] = $fecha_retiro;}
            if ($estado != null) {$atributos .= ", estado = ?"; $ss .= "s"; $parametros[] = $estado;}
            if ($cod_especialistaFK != null) {$atributos .= ", cod_especialistaFK = ?"; $ss .= "i"; $parametros[] = $cod_especialistaFK;}
            if ($cod_mecanicoDentalFK != null) {$atributos .= ", cod_mecanicoDentalFK = ?"; $ss .= "i"; $parametros[] = $cod_mecanicoDentalFK;}
            if ($cod_localFK != null) {$atributos .= ", cod_localFK = ?"; $ss .= "i"; $parametros[] = $cod_localFK;}

            $parametros[] = $cod_trabajo_mecanico_dental;
            $ss .= "i";

            $sql = "UPDATE trabajo_mecanico_dental SET $atributos WHERE cod_trabajo_mecanico_dental = ?";
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

        if ($cod_trabajo_mecanico_dental == null) {
            $cod_trabajo_mecanico_dental = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_trabajo_mecanico_dental;
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
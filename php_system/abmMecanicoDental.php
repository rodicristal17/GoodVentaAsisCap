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
                $fecha_entrega = isset($_POST['fecha_entrega']) ? utf8_decode($_POST['fecha_entrega']) : date('Y-m-d H:i:s');
                $fecha_retiro = isset($_POST['fecha_retiro']) ? utf8_decode($_POST['fecha_retiro']) : null;

                $cod_mecanico_dental = abmMecanicoDental($cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro, null);
                echo json_encode(array("1" => "exito", "cod_mecanico_dental" => $cod_mecanico_dental));
                break;
            case "editar":
                $cod_mecanico_dental = $_POST['cod_mecanico_dental'];
                $cod_mecanico_dental = utf8_decode($cod_mecanico_dental);
                $cod_ventaFK = isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_tipo_trabajoFK = isset($_POST['cod_tipo_trabajoFK']) ? utf8_decode($_POST['cod_tipo_trabajoFK']) : null;
                $observacion = isset($_POST['observacion']) ? utf8_decode($_POST['observacion']) : '';
                $colorimetro = isset($_POST['colorimetro']) ? utf8_decode($_POST['colorimetro']) : '';
                $costo = isset($_POST['costo']) ? utf8_decode($_POST['costo']) : 0;
                $fecha_entrega = isset($_POST['fecha_entrega']) ? utf8_decode($_POST['fecha_entrega']) : date('Y-m-d H:i:s');
                $fecha_retiro = isset($_POST['fecha_retiro']) ? utf8_decode($_POST['fecha_retiro']) : null;

                $cod_mecanico_dental = abmMecanicoDental($cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro, $cod_mecanico_dental);
                echo json_encode(array("1" => "exito", "cod_mecanico_dental" => $cod_mecanico_dental));
                break;
            case "buscar":
                $cod_mecanico_dental = isset($_POST['cod_mecanico_dental']) ? utf8_decode($_POST['cod_mecanico_dental']) : null;
                $cod_ventaFK = isset($_POST['cod_ventaFK']) ? utf8_decode($_POST['cod_ventaFK']) : null;
                $cod_tipo_trabajoFK = isset($_POST['cod_tipo_trabajoFK']) ? utf8_decode($_POST['cod_tipo_trabajoFK']) : null;
                $tipo_trabajo = isset($_POST['tipo_trabajo']) ? utf8_decode($_POST['tipo_trabajo']) : null;
                $nombre_paciente = isset($_POST['nombre_paciente']) ? utf8_decode($_POST['nombre_paciente']) : null;

                $filtros = array(
                    'cod_mecanico_dental' => $cod_mecanico_dental,
                    'cod_ventaFK' => $cod_ventaFK,
                    'cod_tipo_trabajoFK' => $cod_tipo_trabajoFK,
                    'tipo_trabajo' => $tipo_trabajo,
                    'nombre_paciente' => $nombre_paciente
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

                obtenerVistaMecanicoDental($filtros, $limite);
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
        }
    }

    function obtenerVistaMecanicoDental($filtros, $limite = 0) {
        $registros = obtenerMecanicosDentales($filtros, $limite);

        $pagina= "";
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            $styleName=CargarStyleTable($styleName);
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='ObtenerdatosMecanicoDental(this)'>
            <td id='td_id' style='width: 10%;'>".$value['cod_mecanico_dental']."</td>
            <td id='td_datos_1'style='width:45%' class='tdRegistroSearch' >".$value['nombre_paciente']."</td>
            <td id='td_datos_3'style='width:25%' class='tdRegistroSearch' >".$value['nombre_tipo_trabajo']."</td>
            <td id='td_datos_4'style='width:10%' class='tdRegistroSearch' >".$value['fecha_entrega']."</td>
            <td id='td_datos_5'style='width:10%' class='tdRegistroSearch' >".$value['fecha_retiro']."</td>
            <td id='td_datos_6'style='display: none;' class='tdRegistroSearch' >".$value['colorimetro']."</td>
            <td id='td_datos_7'style='display: none;' class='tdRegistroSearch' >".$value['costo']."</td>
            <td id='td_datos_8'style='display: none;' class='tdRegistroSearch' >".$value['observacion']."</td>
            <td id='td_datos_9'style='display: none;' class='tdRegistroSearch' >".$value['cod_ventaFK']."</td>
            <td id='td_datos_10'style='display: none;' class='tdRegistroSearch' >".$value['cod_tipo_trabajoFK']."</td>
            </tr>
            </table>";
        }
        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros));
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
        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => $optionesHTML));
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
                        $sqlFiltro .= "$key = '$value'";
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
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $stmt->close();
        return $result;
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

    function obtenerMecanicosDentales($filtros, $limite = 0) {
        $sqlFiltro = "";
        $sqlFiltroPersona = '';

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
                case 'fecha_entrega_desde':
                    $sqlFiltro .= "DATE(md.fecha_entrega) >= '$value'";
                    break;
                case 'fecha_entrega_hasta':
                    $sqlFiltro .= "DATE(md.fecha_entrega) <= '$value'";
                    break;
                case 'fecha_retiro_desde':
                    $sqlFiltro .= "DATE(md.fecha_retiro) >= '$value'";
                    break;
                case 'fecha_retiro_hasta':
                    $sqlFiltro .= "DATE(md.fecha_retiro) <= '$value'";
                    break;
                case 'tipo_trabajo':
                    $sqlFiltro .= "t.descripcion like '%$value%'";
                    break;
                case 'estado':
                    $sqlFiltro .= "md.estado = '$value'";
                    break;
                case 'nombre_paciente':
                    $sqlFiltro .= '(SELECT COUNT(*) 
                        FROM persona 
                        JOIN venta v ON v.cod_clienteFK = cod_persona 
                        WHERE v.cod_venta = md.cod_ventaFK AND nombre_persona LIKE "%'.$value.'%") > 0';
                    $sqlFiltroPersona = 'AND nombre_persona like "%'.$value.'%"';
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "md.$key = $value";
                    } else {
                        $sqlFiltro .= "md.$key = '$value'";
                    }
                    break;
            }
        }

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql = "SELECT md.*, t.descripcion as nombre_tipo_trabajo, 
         (SELECT nombre_persona FROM persona JOIN venta v ON v.cod_clienteFK = cod_persona WHERE v.cod_venta = md.cod_ventaFK $sqlFiltroPersona) AS nombre_paciente
         FROM mecanico_dental md JOIN tipo_trabajo_mecanico_dental t ON t.cod_tipo_trabajo_mecanico_dental = md.cod_tipo_trabajoFK $sqlFiltro ORDER BY md.fecha_entrega DESC $limite";

        $mysqli = conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al buscar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        $result = $stmt->get_result();
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $stmt->close();
        return $result;
    }

    function abmMecanicoDental($cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro, $cod_mecanico_dental) {
        $mysqli = conectar_al_servidor();
        if ($cod_mecanico_dental == null) {
            $sql = "INSERT INTO mecanico_dental (cod_ventaFK, cod_tipo_trabajoFK, observacion, colorimetro, costo, fecha_entrega, fecha_retiro) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iisssss', $cod_ventaFK, $cod_tipo_trabajoFK, $observacion, $colorimetro, $costo, $fecha_entrega, $fecha_retiro);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            if ($cod_ventaFK != null) {$atributos .= ", cod_ventaFK = ?"; $ss .= "i"; $parametros[] = $cod_ventaFK;}
            if ($cod_tipo_trabajoFK != null) {$atributos .= ", cod_tipo_trabajoFK = ?"; $ss .= "i"; $parametros[] = $cod_tipo_trabajoFK;}
            if ($observacion != null) {$atributos .= ", observacion = ?"; $ss .= "s"; $parametros[] = $observacion;}
            if ($colorimetro != null) {$atributos .= ", colorimetro = ?"; $ss .= "s"; $parametros[] = $colorimetro;}
            if ($costo != null) {$atributos .= ", costo = ?"; $ss .= "d"; $parametros[] = $costo;}
            if ($fecha_entrega != null) {$atributos .= ", fecha_entrega = ?"; $ss .= "s"; $parametros[] = $fecha_entrega;}
            if ($fecha_retiro != null) {$atributos .= ", fecha_retiro = ?"; $ss .= "s"; $parametros[] = $fecha_retiro;}

            $atributos = substr($atributos, 2);
            $parametros[] = $cod_mecanico_dental;
            $ss .= "i";

            $sql = "UPDATE mecanico_dental SET $atributos WHERE cod_mecanico_dental = ?";
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

        if ($cod_mecanico_dental == null) {
            $cod_mecanico_dental = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_mecanico_dental;
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
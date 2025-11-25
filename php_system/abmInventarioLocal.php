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
            case 'nuevo/editar':
                $cod_inventario= isset($_POST['cod_inventario']) ? utf8_decode($_POST['cod_inventario']) : null;
                $nombre= isset($_POST['nombre']) ? utf8_decode($_POST['nombre']) : null;
                $descripcion= isset($_POST['descripcion']) ? utf8_decode($_POST['descripcion']) : null;
                $estado= isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $cantidad= isset($_POST['cantidad']) ? utf8_decode($_POST['cantidad']) : null;
                $costo= isset($_POST['costo']) ? utf8_decode($_POST['costo']) : null;
                $observacion= isset($_POST['observacion']) ? utf8_decode($_POST['observacion']) : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;
                
                $cod_inventario= abmInventarioLocal($cod_inventario, $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $cod_localFK, $user);
                echo json_encode(array("1" => "exito", "cod_inventario" => $cod_inventario));
                break;
            case 'buscarVista':
                $cod_inventario= isset($_POST['cod_inventario']) ? utf8_decode($_POST['cod_inventario']) : null;
                $nombre= isset($_POST['nombre']) ? utf8_decode($_POST['nombre']) : null;
                $estado= isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;

                $filtros= array(
                    'cod_insumo'=> $cod_inventario,
                    'nombre'=> $nombre,
                    'estado'=> $estado,
                    'cod_localFK'=> $cod_localFK
                );

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                obtenerVistaInsumosLocal($filtros, $limite);
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerVistaInsumosLocal($filtros, $limite) {
        $cantRegistros= obtenerInventarioLocal($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerInventarioLocal($filtros, $limite);

        $pagina= "";
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            $styleName=CargarStyleTable($styleName);
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='obtenerDatosInsumoLocal(this)'>
            <td id='td_id' style='width:10%;'>".$value['cod_insumo']."</td>
            <td id='td_datos_1'style='width:55%;'>".$value['nombre']."</td>
            <td id='td_datos_2'style='display:none;'>".$value['descripcion']."</td>
            <td id='td_datos_3'style='width:20%;'>".$value['nombreLocal'].".</td>
            <td id='td_datos_4'style='width:15%;'>".ucfirst($value['estado'])."</td>
            <td id='td_datos_5'style='display: none;'>".$value['cantidad']."</td>
            <td id='td_datos_6'style='display: none;'>".$value['costo']."</td>
            <td id='td_datos_7'style='display: none;'>".$value['observacion']."</td>
            <td id='td_datos_8'style='display: none;'>".$value['cod_localFK']."</td>
            <td id='td_datos_9'style='display: none;'>".$value['cod_usuarioFK_edit']."</td>
            </tr></table>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros));
    }

    function obtenerInventarioLocal($filtros= array(), $limite= 0) {
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
                    $sqlFiltro .= "il.cod_localFK = '$value'";
                    break;
                case 'estado':
                    $sqlFiltro .= "il.estado = '$value'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "il.$key = $value";
                    } else {
                        $sqlFiltro .= "il.$key like '$value'";
                    }
                    break;
            }
        }

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT il.* , l.Nombre as nombreLocal FROM insumos_local il JOIN local l ON l.cod_local = il.cod_localFK
            $sqlFiltro ORDER BY il.nombre ASC $limite";

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

    function abmInventarioLocal($cod_inventario, $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $cod_localFK, $cod_usuarioFK_edit) {
        $mysqli = conectar_al_servidor();

        if (empty($cod_inventario)) {
            $sql = "INSERT INTO insumos_local (cod_insumo, nombre, descripcion, estado, cantidad, costo, observacion, cod_localFK, cod_usuarioFK_edit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('isssiisii', $cod_inventario, $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $cod_localFK, $cod_usuarioFK_edit);
        } else {
            $parametros = array();

            // Datos para auditoria
            $atributos = "fecha_edit= ?";
            $ss = "s";
            $parametros[]= "NOW()";

            $atributos .= ", cod_usuarioFK_edit= ?";
            $ss .= "s";
            $parametros[] = $cod_usuarioFK_edit;
            
            // Datos a modificar
            if (!empty($nombre)) {
                $atributos .= ", nombre= ?";
                $ss .= "s";
                $parametros[] = $nombre;
            }
            if (!empty($descripcion)) {
                $atributos .= ", descripcion= ?";
                $ss .= "s";
                $parametros[] = $descripcion;
            }
            if (!empty($estado)) {
                $atributos .= ", estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
            }
            if (!empty($cantidad)) {
                $atributos .= ", cantidad= ?";
                $ss .= "s";
                $parametros[] = $cantidad;
            }
            if (!empty($costo)) {
                $atributos .= ", costo= ?";
                $ss .= "s";
                $parametros[] = $costo;
            }
            if (!empty($observacion)) {
                $atributos .= ", observacion= ?";
                $ss .= "s";
                $parametros[] = $observacion;
            }
            if (!empty($cod_localFK)) {
                $atributos .= ", cod_localFK= ?";
                $ss .= "s";
                $parametros[] = $cod_localFK;
            }
            
            $parametros[] = $cod_inventario;
            $ss .= "i";

            $sql= "UPDATE insumos_local SET $atributos WHERE cod_insumo = ?";
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

        if (empty($cod_inventario)) {
            $cod_inventario = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_inventario;
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
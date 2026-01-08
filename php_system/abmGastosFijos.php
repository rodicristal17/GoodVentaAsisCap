<?php
    require("conexion.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");
    include("subir_foto_base64.php");

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
            case 'nuevo/editar':
                $cod_gastos_fijos= !empty($_POST['cod_gastos_fijos']) ? utf8_decode($_POST['cod_gastos_fijos']) : null;
                $fecha= $fechaActual->format('Y-m-d H:i:s');
                $descripcion= isset($_POST['descripcion']) ? utf8_decode($_POST['descripcion']) : null;
                $cod_interConsultaFK= isset($_POST['cod_interConsultaFK']) ? utf8_decode($_POST['cod_interConsultaFK']) : null;
                $dia= isset($_POST['dia']) ? utf8_decode($_POST['dia']) : null;
                $estado= isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;
                $costo= isset($_POST['costo']) ? utf8_decode($_POST['costo']) : null;

                $cod_gastos_fijos = abmGastosFijos($cod_gastos_fijos, $user, $fecha, $descripcion, $cod_interConsultaFK, $dia, $estado, $costo, $cod_localFK);

                echo json_encode(array(
                    "1" => "exito",
                    "2" => $cod_gastos_fijos
                ));
                break;
            case "buscarVista":
                $cod_gastos_fijos= isset($_POST['cod_gastos_fijos']) ? utf8_decode($_POST['cod_gastos_fijos']) : null;
                $descripcion= isset($_POST['descripcion']) ? utf8_decode($_POST['descripcion']) : null;
                $cod_interConsultaFK= isset($_POST['cod_interConsultaFK']) ? utf8_decode($_POST['cod_interConsultaFK']) : null;
                $dia= isset($_POST['dia']) ? utf8_decode($_POST['dia']) : null;
                $estado= isset($_POST['estado']) ? utf8_decode($_POST['estado']) : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? utf8_decode($_POST['cod_localFK']) : null;

                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : null;
                $filtros= array(
                    'cod_gastos_fijos' => $cod_gastos_fijos,
                    'descripcion' => $descripcion,
                    'cod_interConsultaFK' => $cod_interConsultaFK,
                    'dia' => $dia,
                    'estado' => $estado,
                    'cod_localFK' => $cod_localFK
                );

                buscarVistaGastosFijos($filtros, $limite);
                break;
            default:
                echo json_encode(array(
                    "1" => "error",
                    "2" => "Operacion $funt no implementada"
                ));
                break;
        }
    }

    function buscarVistaGastosFijos($filtros, $limite) {
        $registros= obtenerGastosFijos($filtros);
        $totalRegistros= count($registros);
        $registros= obtenerGastosFijos($filtros, $limite);

        $pagina= '';
        $styleName="tableRegistroSearch";
        foreach ($registros as $value) {
            $styleName=CargarStyleTable($styleName);
            $pagina .= "<table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='obtenerDatosGastosFijos(this)'>
                <td id='td_id' style='width:10%;'>".$value['cod_gastos_fijos']."</td>
                <td id='td_datos_1' style='width:55%;'>".$value['descripcion']."</td>
                <td id='td_datos_2' style='width:15%;'>".$value['dia']."</td>
                <td id='td_datos_3' style='width:20%;'>".ucfirst($value['estado'])."</td>
                <td id='td_datos_4' style='display:none'>".$value['cod_interConsultaFK']."</td>
                <td id='td_datos_5' style='display:none'>".$value['cod_localFK']."</td>
                <td id='td_datos_6' style='display:none'>".$value['asunto_interConsulta']."</td>
                <td id='td_datos_7' style='display:none'>".$value['costo']."</td>
            </tr></table>";
        }
        echo json_encode(array(
            "1" => "exito",
            "2" => $pagina,
            "3" => $totalRegistros
        ));
    }

    function obtenerGastosFijos($filtros= array(), $limite= 0) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
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

        $sql= "SELECT *,
            (SELECT asunto FROM interConsulta WHERE cod_interConsulta = gf.cod_interConsultaFK) AS asunto_interConsulta
            FROM gastos_fijos gf ORDER BY cod_gastos_fijos ASC";

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

    function abmGastosFijos($cod_gastos_fijos, $cod_usuarioFK, $fecha, $descripcion, $cod_interConsultaFK, $dia, $estado, $costo, $cod_localFK) {
        $mysqli = conectar_al_servidor();

        if (empty($cod_gastos_fijos)) {
            $sql = "INSERT INTO gastos_fijos (cod_gastos_fijos, cod_usuarioFK_create, descripcion, cod_interconsultaFK, dia, estado, fecha_creacion, cod_localFK, costo) VALUES (?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iisiissii', $cod_gastos_fijos, $cod_usuarioFK, $descripcion, $cod_interConsultaFK, $dia, $estado, $fecha, $cod_localFK, $costo);
        } else {
            $parametros = array();

            // Datos para auditoria
            $atributos = "fecha_edit= ?";
            $ss = "s";
            $parametros[]= $fecha;

            $atributos .= ", cod_usuarioFK_edit= ?";
            $ss .= "i";
            $parametros[] = $cod_usuarioFK;
            
            // Datos a modificar
            if (!empty($estado)) {
                $atributos .= ", estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
            }
            if (!empty($descripcion)) {
                $atributos .= ", descripcion= ?";
                $ss .= "s";
                $parametros[] = $descripcion;
            }
            if (!empty($dia)) {
                $atributos .= ", dia= ?";
                $ss .= "i";
                $parametros[] = $dia;
            }
            if (!empty($cod_interConsultaFK)) {
                $atributos .= ", cod_interConsultaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_interConsultaFK;
            }
            if (!empty($cod_localFK)) {
                $atributos .= ", cod_localFK= ?";
                $ss .= "i";
                $parametros[]= $cod_localFK;
            }
            if (!empty($costo)) {
                $atributos .= ", costo= ?";
                $ss .= "i";
                $parametros[]= $costo;
            }
            
            $parametros[] = $cod_gastos_fijos;
            $ss .= "i";

            $sql= "UPDATE gastos_fijos SET $atributos WHERE cod_gastos_fijos = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql, "parametros" => array($cod_gastos_fijos, $cod_usuarioFK, $fecha, $descripcion, $cod_interConsultaFK, $dia, $estado, $cod_localFK));
            echo json_encode($informacion);
            exit;
        }

        if (empty($cod_gastos_fijos)) {
            $cod_gastos_fijos = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_gastos_fijos;
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
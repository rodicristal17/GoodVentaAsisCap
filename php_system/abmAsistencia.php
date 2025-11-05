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

        $horaActual= date('H:i:s');
        switch ($funt) {
            case "nuevo":
                $cod_usuario= $user;
                $hora_entrada= isset($_POST['hora_entrada']) ? utf8_decode($_POST['hora_entrada']) : $horaActual;
                $hora_salida = isset($_POST['hora_salida']) ? utf8_decode($_POST['hora_salida']) : null;
                $ip_publica = $_SERVER['REMOTE_ADDR'];
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, null, $ip_publica, null);
                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia));
                break;
            case "editar";
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = utf8_decode($cod_asistencia);
                $cod_usuario= $user;
                $hora_entrada = isset($_POST['hora_entrada']) ? utf8_decode($_POST['hora_entrada']) : null;
                $hora_salida= isset($_POST['hora_salida']) ? utf8_decode($_POST['hora_salida']) : null;
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, null, $cod_asistencia);
                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia));
                break;
            case "registrarSalida":
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = utf8_decode($cod_asistencia);
                registrarSalida($user, $horaActual, $cod_asistencia);
                echo json_encode(array("1" => "exito"));
                break;
            case "buscar":
                $hora_entrada = isset($_POST['hora_entrada']) ? utf8_decode($_POST['hora_entrada']) : null;
                $hora_salida= isset($_POST['hora_salida']) ? utf8_decode($_POST['hora_salida']) : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? utf8_decode($_POST['cod_asistencia']) : null;
                $cod_usuario= isset($_POST['cod_usuario']) ? utf8_decode($_POST['cod_usuario']) : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? utf8_decode($_POST['fecha_desde']) : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? utf8_decode($_POST['fecha_hasta']) : null;
                $sinSalida= isset($_POST['sinSalida']) ? utf8_decode($_POST['sinSalida']) : null;
                $filtros= array(
                    'hora_entrada'=> $hora_entrada,
                    'hora_salida'=> $hora_salida,
                    'cod_asistencia'=> $cod_asistencia,
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'sinSalida'=> $sinSalida
                );
                $limite= isset($_POST['limite']) ? utf8_decode($_POST['limite']) : 0;

                $registros= obtenerAsistencias($filtros, $limite);
                echo json_encode(array("1" => "exito", "registros" => $registros));
                break;
        }
    }

    function registrarSalida($cod_usuarioFK, $hora_salida, $cod_asistencia) {
        // Otiene la direccion de ip de la encargada
        // Compara con la direccion ip del usuario que registrara su salida
        // Si es diferente, retorna error con mensaje
        // Si es igual, registra la salida
        abmAsistencia($cod_usuarioFK, null, $hora_salida, null, $cod_asistencia);
    }

    function obtenerAsistencias($filtros, $limite= 0) {
        // Se genera el filtro
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'fecha_desde':
                    $sqlFiltro .= "DATE(fecha) <= '$value'";
                    break;
                case 'fecha_hasta':
                    $sqlFiltro .= "DATE(fecha) >= '$value'";
                    break;
                case 'sinSalida': 
                    $sqlFiltro .= "hora_salida is null";
                    break;
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

        $sql= "SELECT * FROM asistencia $sqlFiltro ORDER BY fecha DESC $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $result = mysqli_fetch_all($result);

        $stmt->close();
        return $result;
    }

    function abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, $ip_publica, $cod_asistencia) {
    	$mysqli=conectar_al_servidor();
        if ($cod_asistencia == null) {
            $sql = "INSERT INTO asistencia (cod_usuarioFK, hora_entrada, direccion_ip) 
                    VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iss', $cod_usuario, $hora_entrada, $ip_publica);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            if ($hora_entrada != null) {
                $atributos .= ", hora_entrada = ?";
                $ss .= "s";
                $parametros[] = $hora_entrada;
            }
            if ($hora_salida != null) {
                $atributos .= ", hora_salida = ?";
                $ss .= "s";
                $parametros[] = $hora_salida;
            }
            if ($ip_publica != null) {
                $atributos .= ", direccion_ip = ?";
                $ss .= "s";
                $parametros[] = $ip_publica;
            }

            $atributos = substr($atributos, 2);
            $parametros[] = $cod_asistencia;
            $ss .= "i";

            $sql = "UPDATE asistencia SET $atributos WHERE cod_asistencia = ?";
            $stmt = $mysqli->prepare($sql);

            // Convertir a referencias
            $refs = [];
            foreach ($parametros as $k => $v) {
                $refs[$k] = &$parametros[$k];
            }

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if ( ! $stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }

        if ($cod_asistencia == null) {
            $cod_asistencia = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_asistencia;
    }

    // Validacion e identificacion de funcion
    $operacion= $_POST['accion'];
    $operacion = utf8_decode($operacion);
    verificar($operacion);
?>
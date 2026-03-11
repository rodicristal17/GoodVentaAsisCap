<?php
    require("conexion.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");

    date_default_timezone_set('America/Asuncion');

    function verificar($funt) {
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

        $horaActual= date('H:i:s');
        switch ($funt) {
            case "nuevo":
                $cod_usuario= $user;
                $hora_entrada= isset($_POST['hora_entrada']) ? mb_convert_encoding((string)($_POST['hora_entrada']), 'ISO-8859-1', 'UTF-8') : $horaActual;
                $hora_salida = isset($_POST['hora_salida']) ? mb_convert_encoding((string)($_POST['hora_salida']), 'ISO-8859-1', 'UTF-8') : null;
                $ip_publica = $_SERVER['REMOTE_ADDR'];
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, null, $ip_publica, null);
                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia));
                break;
            case "editar";
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = mb_convert_encoding((string)($cod_asistencia), 'ISO-8859-1', 'UTF-8');
                $cod_usuario= $user;
                $hora_entrada = isset($_POST['hora_entrada']) ? mb_convert_encoding((string)($_POST['hora_entrada']), 'ISO-8859-1', 'UTF-8') : null;
                $hora_salida= isset($_POST['hora_salida']) ? mb_convert_encoding((string)($_POST['hora_salida']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, null, $cod_asistencia);
                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia));
                break;
            case "registrarSalida":
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = mb_convert_encoding((string)($cod_asistencia), 'ISO-8859-1', 'UTF-8');
                $cod_local= $_POST['cod_local'];
                $cod_local = mb_convert_encoding((string)($cod_local), 'ISO-8859-1', 'UTF-8');
                $fechaActual= date('Y-m-d');
                registrarSalida($user, $horaActual, $cod_asistencia, $cod_local, $fechaActual);
                break;
            case "buscar":
                $hora_entrada = isset($_POST['hora_entrada']) ? mb_convert_encoding((string)($_POST['hora_entrada']), 'ISO-8859-1', 'UTF-8') : null;
                $hora_salida= isset($_POST['hora_salida']) ? mb_convert_encoding((string)($_POST['hora_salida']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuario= isset($_POST['cod_usuario']) ? mb_convert_encoding((string)($_POST['cod_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $sinSalida= isset($_POST['sinSalida']) ? mb_convert_encoding((string)($_POST['sinSalida']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    'hora_entrada'=> $hora_entrada,
                    'hora_salida'=> $hora_salida,
                    'cod_asistencia'=> $cod_asistencia,
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'sinSalida'=> $sinSalida,
                    'cod_local'=> $cod_local,
                    'nombre_usuario'=> $nombre_usuario,
                );
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $registros= obtenerAsistencias($filtros, $limite);
                echo json_encode(array("1" => "exito", "registros" => $registros), JSON_UNESCAPED_UNICODE);
                // imprimir error json encode
                //echo json_last_error_msg();
                break;
            case 'buscarVistaInforme':
                $cod_usuario= isset($_POST['cod_usuario']) ? mb_convert_encoding((string)($_POST['cod_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'nombre_usuario'=> $nombre_usuario,
                    'cod_local'=> $cod_local,
                    'cod_asistencia'=> $cod_asistencia,
                );
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaAsistencia($filtros, $limite);
                break;
            case 'buscarMasVistaInforme':
                $cod_usuario= isset($_POST['cod_usuario']) ? mb_convert_encoding((string)($_POST['cod_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'nombre_usuario'=> $nombre_usuario,
                    'cod_local'=> $cod_local,
                    'cod_asistencia'=> $cod_asistencia,
                );
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : "0";

                obtenerVistaAsistencia($filtros, $limite);
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function registrarSalida($cod_usuarioFK, $hora_salida, $cod_asistencia, $cod_local, $fecha) {
        // Otiene la direccion de ip de la encargada
        $administrador_local= obtenerAsistencias(array('cod_local' => $cod_local, 'acceso' => 1, "fecha_desde" => $fecha, "fecha_hasta" => $fecha));

        // Compara con la direccion ip del usuario que registrara su salida
        $ip_valida = false;
        foreach ($administrador_local as $key => $value) {
            if (strcmp($value['direccion_ip'], $_SERVER['REMOTE_ADDR']) == 0) {
                $ip_valida = true;
                break;
            } else if ($value['cod_usuarioFK'] == $cod_usuarioFK) {
                // En caso de ser administrador no valida la ip
                $ip_valida = true;
                break;
            }
        }
        $cod_asistencia= abmAsistencia($cod_usuarioFK, null, $hora_salida, ($ip_valida ? $value['direccion_ip'] : NULL), $cod_asistencia);
        
        // Valida la ip y registra la salida o devuelve error
        if (! $ip_valida) {
            $informacion =array("1" => "red", "2" => "Se registro la asistencia pero la direccion IP de la salida no coincide con ningun administrador del local.", "3" => "Comunique si es un caso especial.", "4" => $_SERVER['REMOTE_ADDR'], "5" => $cod_asistencia);
            echo json_encode($informacion);	
            exit;
        }
        echo json_encode(array("1" => "exito", "2" => $cod_asistencia));
    }

    function obtenerVistaAsistencia($filtros, $limite= "0") {
        $cantRegistros= obtenerAsistencias($filtros);
        $cantRegistros= count($cantRegistros);

        $registros= obtenerAsistencias($filtros, $limite);

        $pagina= "";
        $minutosTotales= 0;
        foreach ($registros as $registro) {
            $minutosTotales += intval($registro['diferencia_minutos']);

            $pagina .= "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'><tr id='tbSelecRegistro'>
            <td style='width: 10%;'>".$registro['cod_asistencia']."</td>
            <td style='width: 45%;'>".$registro['nombre_persona']."</td>
            <td style='width: 15%;'>".substr($registro['fecha'], 0, 10)."</td>
            <td style='width: 10%;'>".$registro['hora_entrada']."</td>
            <td style='width: 10%;'>".$registro['hora_salida']."</td>
            <td style='width: 10%;'>".$registro['direccion_ip']."</td>
            </tr></table>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros, "6" => $minutosTotales));
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
                    $sqlFiltro .= "DATE(a.fecha) >= '$value'";
                    break;
                case 'fecha_hasta':
                    $sqlFiltro .= "DATE(a.fecha) <= '$value'";
                    break;
                case 'sinSalida': 
                    $sqlFiltro .= "a.hora_salida is null";
                    break;
                case 'cod_local':
                    $sqlFiltro .= "u.cod_localFK = '$value'";
                    break;
                case 'acceso':
                    $sqlFiltro .= "u.acceso = '$value'";
                    break;
                case 'nombre_usuario':
                    $sqlFiltro .= "(SELECT nombre_persona FROM persona WHERE cod_persona = a.cod_usuarioFK) LIKE '%$value%'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "a.$key = $value";
                    } else {
                        $sqlFiltro .= "a.$key = '$value'";
                    }
                    break;
            }
        }    

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT *, 
            IF(hora_salida IS NOT NULL,TIMESTAMPDIFF(MINUTE, hora_entrada, hora_salida),NULL) AS diferencia_minutos,
            IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona = cod_usuarioFK),'') AS nombre_persona
            FROM asistencia a JOIN usuario u ON u.cod_usuario = a.cod_usuarioFK $sqlFiltro ORDER BY fecha DESC $limite";

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
            $atributos .= ", direccion_ip = ?";
            $ss .= "s";
            $parametros[] = $ip_publica;

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
    $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
    verificar($operacion);
?>
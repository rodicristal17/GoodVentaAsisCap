<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("abmusuarios.php");

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
                $justificacion = isset($_POST['justificacion']) ? mb_convert_encoding((string)($_POST['justificacion']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, null, $ip_publica, $justificacion, null);

                // Obtiene la hora de entrada del usuario
                $diaActual= getdate()['wday'];
                switch ($diaActual) {
                    case 0:
                        $diaActual= "domingo";
                        break;
                    case 1:
                        $diaActual= "lunes";
                        break;
                    case 2:
                        $diaActual= "martes";
                        break;
                    case 3:
                        $diaActual= "miercoles";
                        break;
                    case 4:
                        $diaActual= "jueves";
                        break;
                    case 5:
                        $diaActual= "viernes";
                        break;
                    case 6:
                        $diaActual= "sabado";
                        break;
                }
                
                $horarios_usuario = buscarHorariosUsuario(null, $cod_usuario);
                $hora_entrada_usuario = obtenerHoraEntradaUsuarioMasCercana($horarios_usuario, $diaActual, $hora_entrada);

                // Compara si la hora_entrada_usuario es mayor que la hora_entrada por 10 min.
                $llegada_tardia = ($hora_entrada_usuario && (strtotime($hora_entrada) - strtotime($hora_entrada_usuario)) > 660) ? 1 : 0;

                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia, "llegada_tardia" => $llegada_tardia, 'hora_entrada' => $hora_entrada, 'hora_entrada_usuario' => $hora_entrada_usuario));
                break;
            case "editar";
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = mb_convert_encoding((string)($cod_asistencia), 'ISO-8859-1', 'UTF-8');
                $cod_usuario= $user;
                $hora_entrada = isset($_POST['hora_entrada']) ? mb_convert_encoding((string)($_POST['hora_entrada']), 'ISO-8859-1', 'UTF-8') : null;
                $hora_salida= isset($_POST['hora_salida']) ? mb_convert_encoding((string)($_POST['hora_salida']), 'ISO-8859-1', 'UTF-8') : null;
                $justificacion = isset($_POST['justificacion']) ? mb_convert_encoding((string)($_POST['justificacion']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, null, $justificacion, $cod_asistencia);
                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia));
                break;
            case 'registrarJustificacion':
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = mb_convert_encoding((string)($cod_asistencia), 'ISO-8859-1', 'UTF-8');
                $justificacion = mb_convert_encoding((string)($_POST['justificacion']), 'ISO-8859-1', 'UTF-8');
                $cod_asistencia = abmAsistencia(null, null, null, null, $justificacion, $cod_asistencia);
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

    function obtenerHoraEntradaUsuarioMasCercana($horarios_usuario, $diaActual, $hora_entrada) {
        $hora_entrada_usuario = null;
        $menor_diferencia = null;
        $hora_marcada = strtotime("2025-01-01 ".$hora_entrada);

        foreach ($horarios_usuario as $horario) {
            if (!isset($horario['dia']) || !isset($horario['hora_entrada']) || $horario['dia'] != $diaActual) {
                continue;
            }

            $hora_horario = strtotime("2025-01-01 ".$horario['hora_entrada']);
            
            $diferencia = abs($hora_marcada - $hora_horario);
            if ($menor_diferencia === null || $diferencia < $menor_diferencia) {
                $menor_diferencia = $diferencia;
                $hora_entrada_usuario = $horario['hora_entrada'];
            }
        }

        return $hora_entrada_usuario;
    }

    function registrarSalida($cod_usuarioFK, $hora_salida, $cod_asistencia, $cod_local, $fecha) {
        $ip_salida = $_SERVER['REMOTE_ADDR'];
        $asistencia_actual = obtenerAsistencias(array('cod_asistencia' => $cod_asistencia), 1);
        $ip_entrada = isset($asistencia_actual[0]['direccion_ip']) ? trim($asistencia_actual[0]['direccion_ip']) : '';
        $hora_entrada = isset($asistencia_actual[0]['hora_entrada']) ? $asistencia_actual[0]['hora_entrada'] : '';

        // Si no existe IP de entrada, no se puede confirmar una diferencia de ubicacion.
        $ip_valida = ($ip_entrada == '' || strcmp($ip_entrada, $ip_salida) == 0);
        $cod_asistencia= abmAsistencia($cod_usuarioFK, null, $hora_salida, ($ip_valida ? $ip_salida : NULL), NULL, $cod_asistencia);
        
        echo json_encode(array(
            "1" => "exito",
            "cod_asistencia" => $cod_asistencia,
            "llegada_tardia" => 0,
            'ip_valida' => ($ip_valida ? 1 : 0),
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida,
            'ip_entrada' => $ip_entrada,
            'ip_salida' => $ip_salida
        ));
    }

    function obtenerVistaAsistencia($filtros, $limite= "0") {
        $registros= obtenerAsistencias($filtros);

        $pagina= "";
        $minutosTotales= 0;
        $empleados= array();
        foreach ($registros as $registro) {
            $cod_usuario= $registro['cod_usuarioFK'];
            if (!isset($empleados[$cod_usuario])) {
                $empleados[$cod_usuario]= array(
                    'cod_usuario'=> $cod_usuario,
                    'nombre_persona'=> $registro['nombre_persona'],
                    'url_usuario'=> !empty($registro['url_usuario']) ? $registro['url_usuario'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
                    'registros'=> array(),
                    'total_minutos'=> 0,
                    'total_registros'=> 0,
                    'sin_salida'=> 0,
                    'con_justificacion'=> 0,
                );
            }

            $minutos= intval($registro['diferencia_minutos']);
            $minutosTotales += $minutos;
            $empleados[$cod_usuario]['total_minutos'] += $minutos;
            $empleados[$cod_usuario]['total_registros']++;
            if (empty($registro['hora_salida'])) {
                $empleados[$cod_usuario]['sin_salida']++;
            }
            if (!empty($registro['justificacion'])) {
                $empleados[$cod_usuario]['con_justificacion']++;
            }
            $empleados[$cod_usuario]['registros'][]= $registro;
        }

        if (count($empleados) == 0) {
            $pagina= "<div class='asistencia-resumen-vacio'>No se encontraron registros de asistencia.</div>";
        }

        foreach ($empleados as $empleado) {
            $cod_usuario_html= htmlspecialchars($empleado['cod_usuario'], ENT_QUOTES, 'UTF-8');
            $nombre_html= htmlspecialchars($empleado['nombre_persona'], ENT_QUOTES, 'UTF-8');
            $foto_html= htmlspecialchars($empleado['url_usuario'], ENT_QUOTES, 'UTF-8');
            $horas= floor($empleado['total_minutos'] / 60);
            $minutos= $empleado['total_minutos'] % 60;
            $detalle= "";

            foreach ($empleado['registros'] as $registro) {
                $detalle .= "<tr>
                    <td>".htmlspecialchars($registro['cod_asistencia'], ENT_QUOTES, 'UTF-8')."</td>
                    <td>".htmlspecialchars(substr($registro['fecha'], 0, 10), ENT_QUOTES, 'UTF-8')."</td>
                    <td>".htmlspecialchars($registro['hora_entrada'], ENT_QUOTES, 'UTF-8')."</td>
                    <td>".htmlspecialchars($registro['hora_salida'], ENT_QUOTES, 'UTF-8')."</td>
                    <td>".htmlspecialchars($registro['direccion_ip'], ENT_QUOTES, 'UTF-8')."</td>
                    <td>".htmlspecialchars($registro['justificacion'], ENT_QUOTES, 'UTF-8')."</td>
                </tr>";
            }

            $pagina .= "
            <div class='asistencia-empleado-card'>
                <button type='button' class='asistencia-empleado-card__resumen' onclick='toggleDetalleAsistenciaEmpleado(this)'>
                    <img class='asistencia-empleado-card__foto' src='".$foto_html."' onerror=\"this.src='/GoodVentaAsisCap/iconos/sinperfil.png'\" alt=''>
                    <span class='asistencia-empleado-card__info'>
                        <strong>".$nombre_html."</strong>
                        <small>Cod. ".$cod_usuario_html."</small>
                    </span>
                    <span class='asistencia-empleado-card__metricas'>
                        <span><b>".$empleado['total_registros']."</b><small>Registros</small></span>
                        <span><b>".$horas."h ".$minutos."m</b><small>Tiempo</small></span>
                        <span><b>".$empleado['sin_salida']."</b><small>Sin salida</small></span>
                        <span><b>".$empleado['con_justificacion']."</b><small>Justif.</small></span>
                    </span>
                </button>
                <div class='asistencia-empleado-card__detalle'>
                    <table class='asistencia-detalle-table'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>IP</th>
                                <th>Justificacion</th>
                            </tr>
                        </thead>
                        <tbody>".$detalle."</tbody>
                    </table>
                </div>
            </div>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => count($registros), "6" => $minutosTotales));
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

        $sql= "SELECT a.*, u.*, u.url AS url_usuario,
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
            $reg= array();
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, $ip_publica, $justificacion, $cod_asistencia) {
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
            if ($justificacion != null) {
                $atributos .= ", justificacion = ?";
                $ss .= "s";
                $parametros[] = $justificacion;
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $operacion= $_POST['accion'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificar($operacion);
    }
?>

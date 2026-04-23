<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");

    date_default_timezone_set('America/Asuncion');

    function verificarOperacionTareasProgramadas($operacion) {
        $user = isset($_POST['useru']) ? mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8') : null;
        $pass = isset($_POST['passu']) ? mb_convert_encoding((string)($_POST['passu']), 'ISO-8859-1', 'UTF-8') : null;
        $pass = str_replace("=", "+", $pass);
        $navegador = isset($_POST['navegador']) ? mb_convert_encoding((string)($_POST['navegador']), 'ISO-8859-1', 'UTF-8') : null;

        $resp = verificar_navegador($user, $navegador, $pass);
        if ($resp != "ok") {
            echo json_encode(array("1" => "UI"));
            exit;
        }

        switch ($operacion) {
            case 'nuevo/editar':
                $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : NULL;
                $nombre = isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : NULL;
                $hora = isset($_POST['hora']) ? mb_convert_encoding((string)($_POST['hora']), 'ISO-8859-1', 'UTF-8') : NULL;
                $estado = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : NULL;
                $fecha_realizado = isset($_POST['fecha_realizado']) ? mb_convert_encoding((string)($_POST['fecha_realizado']), 'ISO-8859-1', 'UTF-8') : NULL;
                $cod_usuarioFK = isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : NULL;

                $id = abmTareasProgramadas($id, $nombre, $hora, $estado, $fecha_realizado, $cod_usuarioFK, $user);
                echo json_encode(array("1" => "exito", "2" => $id, "id" => $id));
                break;
            case 'buscarVista':
                $filtros = array(
                    'nombre' => isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null,
                    'hora' => isset($_POST['hora']) ? mb_convert_encoding((string)($_POST['hora']), 'ISO-8859-1', 'UTF-8') : null,
                    'estado' => isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null,
                    'cod_usuarioFK' => isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : null,
                );
                
                $registros = obtenerTareasProgramadas($filtros);
                $cantRegistros = count($registros);

                $paginaHora = "";
                $paginaSinHora = "";
                $styleName = "tableRegistroSearch";
                foreach ($registros as $value) {
                    $styleName = CargarStyleTable($styleName);
                    $checked = $value["estado"] == "pendiente" ? "" : "checked";
                    $nombre = htmlspecialchars($value["nombre"], ENT_QUOTES, "UTF-8");
                    $hora = htmlspecialchars($value["hora"], ENT_QUOTES, "UTF-8");
                    if ($value['hora']) {
                        $paginaHora .= '<li><input type="checkbox" '.$checked.'> '.$hora.' | '.$nombre.'</li>';
                    } else {
                        $paginaSinHora .= '<li><input type="checkbox" '.$checked.'> '.$nombre.'</li>';
                    }
                }

                echo json_encode(array("1" => "exito", "2" => $paginaSinHora,"3" => $paginaHora, "4" => $registros, "5" => $cantRegistros));
                break;
            case 'buscarVistaInforme':
                $filtros = array(
                    'id' => isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null,
                    'nombre' => isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null,
                    'hora' => isset($_POST['hora']) ? mb_convert_encoding((string)($_POST['hora']), 'ISO-8859-1', 'UTF-8') : null,
                    'estado' => isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null,
                    'fecha_inicio' => isset($_POST['fecha_inicio']) ? mb_convert_encoding((string)($_POST['fecha_inicio']), 'ISO-8859-1', 'UTF-8') : null,
                    'fecha_fin' => isset($_POST['fecha_fin']) ? mb_convert_encoding((string)($_POST['fecha_fin']), 'ISO-8859-1', 'UTF-8') : null,
                    'fecha_realizado_inicio' => isset($_POST['fecha_realizado_inicio']) ? mb_convert_encoding((string)($_POST['fecha_realizado_inicio']), 'ISO-8859-1', 'UTF-8') : null,
                    'fecha_realizado_fin' => isset($_POST['fecha_realizado_fin']) ? mb_convert_encoding((string)($_POST['fecha_realizado_fin']), 'ISO-8859-1', 'UTF-8') : null,
                    'cod_usuarioFK' => isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : null,
                    'cod_usuarioFK_create' => isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null,
                    'nombre_usuarioFK_create' => isset($_POST['nombre_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['nombre_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null
                );
                $limite = isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;
                obtenerVistaTareasProgramadas($filtros, $limite);
                break;
            case 'marcarRealizado':
                $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_realizado = isset($_POST['fecha_realizado']) ? mb_convert_encoding((string)($_POST['fecha_realizado']), 'ISO-8859-1', 'UTF-8') : null;
                $id = abmTareasProgramadas($id, NULL, NULL, NULL, $fecha_realizado, NULL, NULL);
                echo json_encode(array("1" => "exito", "2" => $id));
                break;
            case 'eliminar':
                $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $id = abmTareasProgramadas($id, NULL, NULL, 'inactivo', NULL, NULL, NULL);
                echo json_encode(array("1" => "exito", "2" => $id));
                break;
            default:
                echo json_encode(array("1" => "error", "2" => "$operacion NO IMPLEMENTADA."));
                break;
        }
    }

    function obtenerVistaTareasProgramadas($filtros = array(), $limite = 0) {
        $cantRegistros = count(obtenerTareasProgramadas($filtros));
        $registros = obtenerTareasProgramadas($filtros, $limite);

        $pagina = "";
        $styleName = "tableRegistroSearch";
        foreach ($registros as $value) {
            $styleName = CargarStyleTable($styleName);
            $checked = $value["estado"] == "pendiente" ? "" : "checked";
            $pagina .= "<table class='tableRegistroSearch2' style='width:100%'>
                <tr id='tbSelecRegistro' onclick='obtenerDatosTareas(this);verCerrarVentanaTareasProgramadas(true, true)'>
                    <td id='td_id' style='width:8%;'>".$value["id"] ."</td>
		            <td id='td_datos_1' style='width:37%;text-align:left;'>".$value["nombre"] ."</td>
                    <td id='td_datos_2' style='width:15%;'>".($value["hora"] == '00:00:00' ? '' : $value["hora"]) ."</td>
                    <td id='td_datos_3' style='width:15%;'>".$value["estado"] ."</td>
                    <td id='td_datos_4' style='width:15%;'>".$value["fecha_realizado"] ."</td>
                    <td id='td_datos_5' style='width:10%;'>".$value["nombre_usuarioFK"] ."</td>
                    <td id='td_datos_6' style='display:none;'>".$value["cod_usuarioFK"] ."</td>
                </tr>
            </table>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina,"3" => $registros, "4" => $cantRegistros));
    }

    function obtenerTareasProgramadas($filtros = array(), $limite = 0) {
        $where = array();
        $parametros = array();
        $ss = "";

        foreach ($filtros as $key => $value) {
            if ($value === null || $value === "") {
                continue;
            }

            switch ($key) {
                case 'id':
                    $where[] = "tp.id = ?";
                    $ss .= "i";
                    $parametros[] = $value;
                    break;
                case 'nombre':
                    $where[] = "tp.nombre LIKE ?";
                    $ss .= "s";
                    $parametros[] = "%".$value."%";
                    break;
                case 'hora':
                    $where[] = "tp.hora = ?";
                    $ss .= "s";
                    $parametros[] = $value;
                    break;
                case 'estado':
                    $where[] = "tp.estado = ?";
                    $ss .= "s";
                    $parametros[] = $value;
                    break;
                case 'fecha_inicio':
                    $where[] = "DATE(tp.fecha_create) >= ?";
                    $ss .= "s";
                    $parametros[] = $value;
                    break;
                case 'fecha_fin':
                    $where[] = "DATE(tp.fecha_create) <= ?";
                    $ss .= "s";
                    $parametros[] = $value;
                    break;
                case 'fecha_realizado_inicio':
                    $where[] = "DATE(tp.fecha_realizado) >= ?";
                    $ss .= "s";
                    $parametros[] = $value;
                    break;
                case 'fecha_realizado_fin':
                    $where[] = "DATE(tp.fecha_realizado) <= ?";
                    $ss .= "s";
                    $parametros[] = $value;
                    break;
                case 'cod_usuarioFK':
                    $where[] = "tp.cod_usuarioFK = ?";
                    $ss .= "i";
                    $parametros[] = $value;
                    break;
                case 'cod_usuarioFK_create':
                    $where[] = "tp.cod_usuarioFK_create = ?";
                    $ss .= "i";
                    $parametros[] = $value;
                    break;
                case 'nombre_usuarioFK_create':
                    $where[] = "(SELECT nombre_persona FROM persona WHERE cod_persona = tp.cod_usuarioFK_create) LIKE ?";
                    $ss .= "s";
                    $parametros[] = "%".$value."%";
                    break;
            }
        }

        $sqlFiltro = "";
        if (count($where) > 0) {
            $sqlFiltro = "WHERE ".implode(" AND ", $where);
        }

        if ($limite == 0 || $limite === null || $limite === "") {
            $limite = "";
        } else {
            $limite = "LIMIT ".intval($limite);
        }

        $sql = "SELECT
                tp.*,
                (SELECT nombre_persona FROM persona WHERE cod_persona = IFNULL(tp.cod_usuarioFK, tp.cod_usuarioFK_create)) as nombre_usuarioFK,
                (SELECT nombre_persona FROM persona WHERE cod_persona = tp.cod_usuarioFK_create) as nombre_usuarioFK_create
                FROM tareas_programadas tp
                $sqlFiltro
                ORDER BY tp.hora ASC, tp.id DESC $limite";

        $mysqli = conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ($ss != "") {
            $refs = array();
            foreach ($parametros as $k => $v) {
                $refs[$k] = &$parametros[$k];
            }
            call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al obtener tareas programadas: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        $result = $stmt->get_result();
        $registros = array();
        while ($row = $result->fetch_assoc()) {
            $reg = array();
            foreach ($row as $key => $value) {
                $reg[$key] = mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmTareasProgramadas($id, $nombre, $hora, $estado, $fecha_realizado, $cod_usuarioFK, $cod_usuarioFK_create) {
        $mysqli = conectar_al_servidor();

        if ($cod_usuarioFK === "" || $cod_usuarioFK === null) {
            $cod_usuarioFK = $cod_usuarioFK_create;
        }
        if ($estado === null || $estado === "") {
            $estado = "pendiente";
        }
        if ($fecha_realizado === "") {
            $fecha_realizado = null;
        }
        if ($estado == "completada" && ($fecha_realizado === null || $fecha_realizado === "")) {
            $fecha_realizado = date('Y-m-d H:i:s');
        }

        if (empty($id)) {
            $sql = "INSERT INTO tareas_programadas (nombre, hora, estado, fecha_realizado, cod_usuarioFK, cod_usuarioFK_create) VALUES (?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssssii', $nombre, $hora, $estado, $fecha_realizado, $cod_usuarioFK, $cod_usuarioFK_create);
        } else {
            $parametros = array();
            $atributos = "";
            $ss = "";

            if ($nombre !== null) {
                $atributos .= "nombre= ?";
                $ss .= "s";
                $parametros[] = $nombre;
            }
            if ($hora !== null) {
                if ($atributos != "") {
                    $atributos .= ", ";
                }
                $atributos .= "hora= ?";
                $ss .= "s";
                $parametros[] = $hora;
            }
            if ($estado !== null) {
                if ($atributos != "") {
                    $atributos .= ", ";
                }
                $atributos .= "estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
            }
            if ($fecha_realizado !== null) {
                if ($atributos != "") {
                    $atributos .= ", ";
                }
                $atributos .= "fecha_realizado= ?";
                $ss .= "s";
                $parametros[] = $fecha_realizado;
            }
            if ($cod_usuarioFK !== null) {
                if ($atributos != "") {
                    $atributos .= ", ";
                }
                $atributos .= "cod_usuarioFK= ?";
                $ss .= "i";
                $parametros[] = $cod_usuarioFK;
            }

            if ($atributos == "") {
                return $id;
            }

            $parametros[] = $id;
            $ss .= "i";

            $sql = "UPDATE tareas_programadas SET $atributos WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $refs = array();
            foreach ($parametros as $k => $v) {
                $refs[$k] = &$parametros[$k];
            }
            call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar tarea programada: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        if (empty($id)) {
            $id = $stmt->insert_id;
        }

        $stmt->close();
        return $id;
    }

    if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
        $operacion = isset($_POST['accion']) ? mb_convert_encoding((string)($_POST['accion']), 'ISO-8859-1', 'UTF-8') : '';
        verificarOperacionTareasProgramadas($operacion);
    }
?>

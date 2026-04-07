<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("subir_foto_base64.php");

    date_default_timezone_set('America/Asuncion');

    function verificarOperacionDictamen($funt) {
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

        $fechaActual= new DateTime();
        switch ($funt) {
            case 'asignarMensajesDictamen':
                $cod_dictamen= mb_convert_encoding((string)($_POST['cod_dictamen']), 'ISO-8859-1', 'UTF-8');
                $cod_mensajeFK= mb_convert_encoding((string)($_POST['cod_mensajeFK']), 'ISO-8859-1', 'UTF-8');
                $asunto= mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8');

                // Actualiza el asunto del dictamen
                abmDictamen($cod_dictamen, $asunto, NULL, NULL, NULL, NULL );

                $cod_mensajeFK= explode(';', $cod_mensajeFK);
                foreach ($cod_mensajeFK as $cod_mensj) {
                    abmMensaje($cod_mensj, NULL, NULL, NULL, NULL, $cod_dictamen);
                }
                
                echo json_encode(array("1" => "exito"));
                break;
            case 'buscarDictamen':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $resultado= isset($_POST['resultado']) ? mb_convert_encoding((string)($_POST['resultado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsultaFK= isset($_POST['cod_interConsultaFK']) ? mb_convert_encoding((string)($_POST['cod_interConsultaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_interConsultaFK= isset($_POST['nombre_interConsultaFK']) ? mb_convert_encoding((string)($_POST['nombre_interConsultaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK_create= isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK_autoriz= isset($_POST['cod_usuarioFK_autoriz']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_autoriz']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK_ejecut= isset($_POST['cod_usuarioFK_ejecut']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_ejecut']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_persona_create= isset($_POST['nombre_persona_create']) ? mb_convert_encoding((string)($_POST['nombre_persona_create']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_persona_autoriz= isset($_POST['nombre_persona_autoriz']) ? mb_convert_encoding((string)($_POST['nombre_persona_autoriz']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_persona_ejecut= isset($_POST['nombre_persona_ejecut']) ? mb_convert_encoding((string)($_POST['nombre_persona_ejecut']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros= array(
                    'id' => $id,
                    'resultado' => $resultado,
                    'estado' => $estado,
                    'cod_interConsultaFK' => $cod_interConsultaFK,
                    'nombre_interConsultaFK' => $nombre_interConsultaFK,
                    'cod_usuarioFK_create' => $cod_usuarioFK_create,
                    'cod_usuarioFK_autoriz' => $cod_usuarioFK_autoriz,
                    'cod_usuarioFK_ejecut' => $cod_usuarioFK_ejecut,
                    'nombre_persona_create' => $nombre_persona_create,
                    'nombre_persona_autoriz' => $nombre_persona_autoriz,
                    'nombre_persona_ejecut' => $nombre_persona_ejecut,
                    'asunto' => $asunto,
                );

                $registros= obtenerDictamen($filtros);
                $totalRegistros= count($registros);

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;
                $registros= obtenerDictamen($filtros, $limite);

                $pagina = "";
                foreach ($registros as $reg) {
                    $pagina .= "<table class='tableRegistroSearch2' border='1' cellspacing='1' cellpadding= '5'>";
                    $pagina .= "<tr id='tbSelecRegistro' onclick='obtenerDatosDictamen(this)'>";
                    $pagina .= "<td class='tdRegistroSearch' style='width: 10%;' id='td_id'>" . $reg['id'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='width: 20%;' id='td_datos_1'>" . $reg['asunto'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='width: 20%;'><div style='width: fit-content; text-decoration: underline; color: blue;' 
                        onclick='ventanaAnterior.push(\"divInformeDictamen\");obtenerDatosInterConsulta(this.parentElement.parentElement)'>" 
                    . $reg['asunto_interConsulta'] 
                    . "</div></td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_10'>" . $reg['asunto_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_22'>" . $reg['cod_interConsultaFK'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='width: 10%;' id='td_datos_23'>" . $reg['estado'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='width: 40%;' id='td_datos_3'>" . $reg['dictamen'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_4'>" . $reg['fecha_create'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_2'>" . $reg['estado_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_5'>" . $reg['nombre_cliente_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_6'>" . $reg['tipo_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_7'>" . $reg['cod_clienteFK_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_11'>" . $reg['cod_localFK'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_15'>" . $reg['monto_limite_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_16'>" . $reg['observacion_interConsulta'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_17'>" . $reg['nombre_persona_create'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_18'>" . $reg['fecha_autoriz'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_19'>" . $reg['nombre_persona_autoriz'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_20'>" . $reg['fecha_ejecut'] . "</td>";
                    $pagina .= "<td class='tdRegistroSearch' style='display: none;' id='td_datos_21'>" . $reg['nombre_persona_ejecut'] . "</td>";
                    $pagina .= "</tr>";
                    $pagina .= "</table>";
                }

                echo json_encode(array("1" => "exito", "2" => $registros, "3" => $pagina, "4" => $limite, "5" => $totalRegistros));
                break;
            case 'nuevo/editar dictamen':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $resultado= isset($_POST['resultado']) ? mb_convert_encoding((string)($_POST['resultado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsultaFK= isset($_POST['cod_interConsultaFK']) ? mb_convert_encoding((string)($_POST['cod_interConsultaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8');

                $id= abmDictamen($id, $asunto, $resultado, $estado, $cod_interConsultaFK, $user, $user, $user);
                echo json_encode(array("1" => "exito", "2" => $id));
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }
    
    function obtenerDictamen($filtros= array(), $limite= 0) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if ($value === null || $value === "") {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'estado':
                    $sqlFiltro .= "d.estado = '$value'";
                    break;
                case 'cod_interConsultaFK':
                    $sqlFiltro .= "d.cod_interConsultaFK = $value";
                    break;
                case 'nombre_interConsultaFK':
                    $sqlFiltro .= "ic.nombre_interConsulta like '%$value%'";
                    break;
                case 'asunto':
                    $sqlFiltro .= "ic.asunto like '%$value%'";
                    break;
                case 'nombre_persona_create':
                    $sqlFiltro .= "pcreate.nombre_persona like '%$value%'";
                    break;
                case 'nombre_persona_autoriz':
                    $sqlFiltro .= "paut.nombre_persona like '%$value%'";
                    break;
                case 'nombre_persona_ejecut':
                    $sqlFiltro .= "peje.nombre_persona like '%$value%'";
                    break;
                case 'fecha_create':
                    $sqlFiltro .= "d.fecha_create $value";
                    break;
                case 'fecha_autoriz':
                    $sqlFiltro .= "d.fecha_autoriz $value";
                    break;
                case 'fecha_ejecut':
                    $sqlFiltro .= "d.fecha_ejecut $value";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "d.$key = $value";
                    } else {
                        $sqlFiltro .= "d.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0 || $limite === '0') {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT d.*,
                pcreate.nombre_persona AS nombre_persona_create,
                ic.asunto AS asunto_interConsulta, ic.cod_localFK AS cod_localFK, ic.tipo AS tipo_interConsulta, ic.estado AS estado_interConsulta,
                ic.monto_limite AS monto_limite_interConsulta, ic.observacion AS observacion_interConsulta,
                (SELECT nombre_persona FROM cliente JOIN venta v WHERE v.cod_clienteFK = cod_persona AND v.cod_venta = ic.cod_ventaFK) as nombre_cliente_interConsulta,
                (SELECT v.cod_clienteFK FROM venta v WHERE v.cod_venta = ic.cod_ventaFK) as cod_clienteFK_interConsulta,
                ic.cod_ventaFK AS cod_venta_interConsulta,
                (SELECT url FROM usuario WHERE cod_usuario = pcreate.cod_persona) AS url_create,
                (SELECT nombre_persona FROM persona WHERE cod_persona = d.cod_usuarioFK_autoriz) AS nombre_persona_autoriz,
                (SELECT nombre_persona FROM persona WHERE cod_persona = d.cod_usuarioFK_ejecut) AS nombre_persona_ejecut
            FROM dictamenes d
            LEFT JOIN persona pcreate ON pcreate.cod_persona = d.cod_usuarioFK_create
            LEFT JOIN interconsulta ic ON ic.cod_interConsulta = d.cod_interConsultaFK
            $sqlFiltro
            ORDER BY FIELD(d.estado, 'solicitado', 'aprobado', 'ejecutado', 'inactivo'), d.id DESC
            $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al obtener dictamen: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            $reg = array();
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmDictamen($id, $asunto, $dictamen, $estado, $cod_interConsultaFK, $cod_usuarioFK_create, $cod_usuarioFK_autoriz= null, $cod_usuarioFK_ejecut= null) {
        $mysqli = conectar_al_servidor();
        $fechaActual= new DateTime();
        $fechaActual= $fechaActual->format('Y-m-d H:i:s');

        $fecha_autoriz = NULL;
        $fecha_ejecut = NULL;
        $cod_usuarioFK_autoriz = NULL;
        $cod_usuarioFK_ejecut = NULL;

        if (empty($id)) {
            if (empty($dictamen) || empty($cod_interConsultaFK) || empty($cod_usuarioFK_create)) {
                echo json_encode(array("1" => "error", "2" => "Faltan datos para registrar el dictamen."));
                exit;
            }

            if (empty($estado)) {
                $estado = 'solicitado';
            }

            if ($estado == 'aprobado') {
                if (empty($cod_usuarioFK_autoriz)) {
                    $cod_usuarioFK_autoriz = $cod_usuarioFK_create;
                }
                $fecha_autoriz = $fechaActual;
            }
            if ($estado == 'ejecutado') {
                if (empty($cod_usuarioFK_ejecut)) {
                    $cod_usuarioFK_ejecut = !empty($cod_usuarioFK_autoriz) ? $cod_usuarioFK_autoriz : $cod_usuarioFK_create;
                }
                $fecha_ejecut = $fechaActual;
            }

            $sql = "INSERT INTO dictamenes (
                asunto, dictamen, estado, fecha_create, cod_usuarioFK_create,
                fecha_autoriz, cod_usuarioFK_autoriz, fecha_ejecut, cod_usuarioFK_ejecut, cod_interConsultaFK
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'ssssisisii',
                $asunto,
                $dictamen,
                $estado,
                $fechaActual,
                $cod_usuarioFK_create,
                $fecha_autoriz,
                $cod_usuarioFK_autoriz,
                $fecha_ejecut,
                $cod_usuarioFK_ejecut,
                $cod_interConsultaFK
            );
        } else {
            $dictamen_original= obtenerDictamen(array(
                "id" => $id
            ), 1);

            if (count($dictamen_original) == 0) {
                echo json_encode(array("1" => "error", "2" => "Dictamen no encontrado."));
                exit;
            }

            $dictamen_original= $dictamen_original[0];
            $parametros = array();
            $atributos = "";
            $ss = "";

            if ($asunto !== null && !empty($asunto)) {
                $atributos .= "asunto = ?, ";
                $parametros[] = $asunto;
                $ss .= "s";
            }
            if ($dictamen !== null && !empty($dictamen)) {
                $atributos .= "dictamen = ?, ";
                $parametros[] = $dictamen;
                $ss .= "s";
            }
            if ($estado !== null) {
                $atributos .= "estado = ?, ";
                $parametros[] = $estado;
                $ss .= "s";

                if ($estado == 'autorizado') {
                    if (empty($cod_usuarioFK_autoriz)) {
                        $cod_usuarioFK_autoriz = $cod_usuarioFK_create;
                    }
                    $atributos .= "fecha_autoriz = ?, cod_usuarioFK_autoriz = ?, ";
                    $parametros[] = $fechaActual;
                    $parametros[] = $cod_usuarioFK_autoriz;
                    $ss .= "si";
                }
                if ($estado == 'ejecutado') {
                    if (empty($cod_usuarioFK_ejecut)) {
                        $cod_usuarioFK_ejecut = !empty($cod_usuarioFK_autoriz) ? $cod_usuarioFK_autoriz : $cod_usuarioFK_create;
                    }
                    $atributos .= "fecha_ejecut = ?, cod_usuarioFK_ejecut = ?, ";
                    $parametros[] = $fechaActual;
                    $parametros[] = $cod_usuarioFK_ejecut;
                    $ss .= "si";
                }
            }
            if ($cod_interConsultaFK !== null) {
                $atributos .= "cod_interConsultaFK = ?, ";
                $parametros[] = $cod_interConsultaFK;
                $ss .= "i";
            }

            if ($atributos == "") {
                return $id;
            }

            $atributos = substr($atributos, 0, -2);
            $parametros[] = $id;
            $ss .= "i";

            $sql= "UPDATE dictamenes SET $atributos WHERE id = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}
            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar dictamen: " . $stmt->error, "sql" => $sql);
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
        $operacion = $_POST['accion'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificarOperacionDictamen($operacion);
    }
?>
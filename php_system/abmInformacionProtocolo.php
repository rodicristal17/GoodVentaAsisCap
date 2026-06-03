<?php
    require("conexion.php");
    require_once("solicitud_eliminado_helper.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");

    function verificar($funt) {
        $user = isset($_POST['useru']) ? mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8') : '';
        $pass = isset($_POST['passu']) ? mb_convert_encoding((string)($_POST['passu']), 'ISO-8859-1', 'UTF-8') : '';
        $pass = str_replace("=", "+", $pass);
        $navegador = isset($_POST['navegador']) ? mb_convert_encoding((string)($_POST['navegador']), 'ISO-8859-1', 'UTF-8') : '';

        $resp = verificar_navegador($user, $navegador, $pass);
        if ($resp != "ok") {
            $informacion = array("1" => "UI");
            echo json_encode($informacion);
            exit;
        }

        switch ($funt) {
            case 'nuevo/editar':
                $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : (isset($_POST['idabm']) ? mb_convert_encoding((string)($_POST['idabm']), 'ISO-8859-1', 'UTF-8') : null);
                $nombre = isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $descripcion = isset($_POST['descripcion']) ? mb_convert_encoding((string)($_POST['descripcion']), 'ISO-8859-1', 'UTF-8') : null;
                $estado = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;

                $id = abmInformacionProtocolo($id, $nombre, $descripcion, $estado, $user);
                echo json_encode(array("1" => "exito", "id" => $id));
                break;
            case 'buscarVista':
                $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre = isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : (isset($_POST['buscar']) ? mb_convert_encoding((string)($_POST['buscar']), 'ISO-8859-1', 'UTF-8') : null);
                $descripcion = isset($_POST['descripcion']) ? mb_convert_encoding((string)($_POST['descripcion']), 'ISO-8859-1', 'UTF-8') : null;
                $estado = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $ocultar_inactivo = isset($_POST['ocultar_inactivo']) ? mb_convert_encoding((string)($_POST['ocultar_inactivo']), 'ISO-8859-1', 'UTF-8') : null;
                $usuario_creador = isset($_POST['usuario_creador']) ? mb_convert_encoding((string)($_POST['usuario_creador']), 'ISO-8859-1', 'UTF-8') : null;
                $limite = isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $filtros = array(
                    'id' => $id,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'estado' => $estado,
                    'ocultar_inactivo' => $ocultar_inactivo,
                    'usuario_creador' => $usuario_creador
                );

                obtenerVistaInformacionProtocolo($filtros, $limite);
                break;
            case 'buscarOption':
                obtenerOptionInformacionProtocolo();
                break;
            default:
                echo json_encode(array("1" => "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerVistaInformacionProtocolo($filtros = array(), $limite = 0) {
        $registros = obtenerInformacionProtocolo($filtros);
        $cant_total_registros = count($registros);
        $registros = obtenerInformacionProtocolo($filtros, $limite);
        $pagina = "";
        $styleName = "tableRegistroSearch";

        foreach ($registros as $value) {
            $styleName = CargarStyleTable($styleName);
            $pagina .= "
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='obtenerDatosInformacionProtocolo(this)'>
                <td id='td_id' style='width: 10%;'>".$value['id']."</td>
                <td id='td_datos_1' style='width: 25%;'>".$value['nombre']."</td>
                <td id='td_datos_2' style='display: none;'>".$value['descripcion']."</td>
                <td id='td_datos_3' style='width: 10%;'>".ucfirst($value['estado'])."</td>
                <td id='td_datos_4' style='display: none;'>".$value['cod_usuarioFK_create']."</td>
                <td id='td_datos_5' style='width: 15%;'>".$value['fecha_create']."</td>
                <td id='td_datos_6' style='width: 25%;'>".$value['nombre_usuarioFK_create']."</td>
            </tr>
            </table>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cant_total_registros));
    }

    function obtenerOptionInformacionProtocolo() {
        $registros = obtenerInformacionProtocolo(array('estado' => 'activo'), 0);
        $pagina = "<option value='' >TODOS</option>";

        foreach ($registros as $value) {
            $pagina .= "<option value='".$value['id']."' >".$value['nombre']."</option>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => count($registros)));
    }

    function obtenerInformacionProtocolo($filtros = array(), $limite = 0) {
        $sqlFiltro = "";
        foreach ($filtros as $key => $value) {
            if ($value === null || $value === "") {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'usuario_creador':
                    $sqlFiltro .= "(SELECT nombre_persona FROM persona WHERE cod_persona = ip.cod_usuarioFK_create) LIKE '%".$value."%'";
                    break;
                case 'estado':
                    $sqlFiltro .= "ip.estado = '".$value."'";
                    break;
                case 'ocultar_inactivo':
                    $sqlFiltro .= "ip.estado != 'inactivo'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "ip.$key = ".intval($value);
                    } else {
                        $sqlFiltro .= "ip.$key LIKE '%".$value."%'";
                    }
                    break;
            }
        }

        if (!$limite || $limite == 0) {
            $limite = "";
        } else {
            $limite = "LIMIT ".$limite;
        }

        $sql = "SELECT
            ip.*,
            (SELECT nombre_persona FROM persona WHERE cod_persona = ip.cod_usuarioFK_create) as nombre_usuarioFK_create
            FROM informacion_protocolo ip
            $sqlFiltro
            ORDER BY ip.id ASC $limite";

        $mysqli = conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al obtener informacion de protocolo: " . $stmt->error, "sql" => $sql);
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

    function abmInformacionProtocolo($id, $nombre, $descripcion, $estado, $cod_usuarioFK_create) {
        if (empty($nombre)) {
            $informacion = array("1" => "camposvacio");
            echo json_encode($informacion);
            exit;
        }

        if (empty($id) && empty($estado)) {
            $estado = 'activo';
        }

        $mysqli = conectar_al_servidor();

        if (empty($id)) {
            $sql = "INSERT INTO informacion_protocolo (nombre, descripcion, estado, cod_usuarioFK_create) VALUES (?,?,?,?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sssi', $nombre, $descripcion, $estado, $cod_usuarioFK_create);
        } else {
            if ($estado !== null && solicitudEliminadoEsEstadoInactivo($estado)) {
                $respuestaSolicitud = registrarSolicitudEliminacionGenerica(
                    'informacion_protocolo',
                    'id',
                    $id,
                    'Solicitud de eliminacion de informacion de protocolo.',
                    $cod_usuarioFK_create,
                    'Informacion protocolo: '.$id
                );
                if (isset($respuestaSolicitud["1"]) && $respuestaSolicitud["1"] != "exito") {
                    echo json_encode($respuestaSolicitud);
                    exit;
                }
                return $id;
            }
            $parametros = array();
            $atributos = "";
            $ss = "";

            if ($nombre !== null) {
                $atributos .= "nombre = ?";
                $ss .= "s";
                $parametros[] = $nombre;
            }
            if ($descripcion !== null) {
                $atributos .= $atributos != "" ? ", descripcion = ?" : "descripcion = ?";
                $ss .= "s";
                $parametros[] = $descripcion;
            }
            if ($estado !== null) {
                $atributos .= $atributos != "" ? ", estado = ?" : "estado = ?";
                $ss .= "s";
                $parametros[] = $estado;
            }

            if ($atributos == "") {
                $informacion = array("1" => "camposvacio");
                echo json_encode($informacion);
                exit;
            }

            $parametros[] = $id;
            $ss .= "i";

            $sql = "UPDATE informacion_protocolo SET $atributos WHERE id = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = array();
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        if (empty($id)) {
            $id = $stmt->insert_id;
        }

        $stmt->close();
        return $id;
    }

    $operacion = isset($_POST['accion']) ? mb_convert_encoding((string)($_POST['accion']), 'ISO-8859-1', 'UTF-8') : (isset($_POST['funt']) ? mb_convert_encoding((string)($_POST['funt']), 'ISO-8859-1', 'UTF-8') : '');
    verificar($operacion);
?>

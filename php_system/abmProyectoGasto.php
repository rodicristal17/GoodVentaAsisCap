<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("abmgasto.php");

    function verificarProyectoGasto($funt) {
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

        switch ($funt) {
            case 'nuevo/editar':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $id= empty($id) && isset($_POST['cod_proyecto_gasto']) ? mb_convert_encoding((string)($_POST['cod_proyecto_gasto']), 'ISO-8859-1', 'UTF-8') : $id;
                $nombre= isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= empty($estado) ? 'activo' : strtolower($estado);

                $id= abmProyectoGasto($id, $nombre, $estado);
                echo json_encode(array("1" => "exito", "id" => $id));
                break;
            case 'buscarVista':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $id= empty($id) && isset($_POST['cod_proyecto_gasto']) ? mb_convert_encoding((string)($_POST['cod_proyecto_gasto']), 'ISO-8859-1', 'UTF-8') : $id;
                $nombre= isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= empty($estado) ? null : strtolower($estado);
                $ocultar_inactivo= isset($_POST['ocultar_inactivo']) ? mb_convert_encoding((string)($_POST['ocultar_inactivo']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros= array(
                    'id'=> $id,
                    'nombre'=> $nombre,
                    'estado'=> $estado,
                    'ocultar_inactivo'=> $ocultar_inactivo,
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaProyectoGasto($filtros, $limite);
                break;
            case 'buscarVistaSelect':
                $id= isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
                $id= empty($id) && isset($_POST['cod_proyecto_gasto']) ? mb_convert_encoding((string)($_POST['cod_proyecto_gasto']), 'ISO-8859-1', 'UTF-8') : $id;
                $nombre= isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= empty($estado) ? null : strtolower($estado);

                $filtros= array(
                    'id'=> $id,
                    'nombre'=> $nombre,
                    'estado'=> $estado,
                    'ocultar_inactivo'=> 'true',
                );

                $registros= obtenerProyectoGasto($filtros);

                $pagina= "";
                $monto_total= 0;
                $styleName="tableRegistroSearch";
                foreach($registros as $value) {
                    $monto_total += intval($value["monto_total"]);
                    $styleName=CargarStyleTable($styleName);
                    $pagina.="<option value='".$value['id']."'>".$value['nombre']."</option>";
                }

                echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros)));
                break;
            case 'obtenerGastosAsociados':
                $cod_proyecto_gastoFK= isset($_POST['cod_proyecto_gastoFK']) ? mb_convert_encoding((string)($_POST['cod_proyecto_gastoFK']), 'ISO-8859-1', 'UTF-8') : $id;

                obtenerVistaGastosAsociadosProyectoGasto($cod_proyecto_gastoFK);
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerVistaGastosAsociadosProyectoGasto($cod_proyecto_gastoFK) {
        $gastos= buscarGasto('','','','','','','','','true','','','','','','','ASC', $cod_proyecto_gastoFK);
        $proyecto= obtenerProyectoGasto(array('id' => $cod_proyecto_gastoFK));

        $total_pendiente= 0;
        $pagina= "";
        foreach ($gastos as $key => $gast) {
            if ($gast['estado'] == 'pendiente' || $gast['estado'] == 'solicitado') {
                $total_pendiente += $gast['monto'];
            }
            $estado= '<span style="text-transform: capitalize;" class="badge bg-';
            switch ($gast['estado']) {
                case 'Activo':
                    $estado .= 'primary">Pagado</span>';
                    break;
                case 'Rechazado':
                    $estado .= 'secondary">'.$gast['estado'].'</span>';
                    break;
                case 'pendiente':
                    $fechaActual = date('Y-m-d');
                    $fechaGasto = date('Y-m-d', strtotime($gast['fecha']));
                    if ($fechaActual >= $fechaGasto) {
                        $estado .= 'danger">solicitado</span>'
                        .'<i class="fa-solid fa-check" onclick="event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: green; padding: 2px;border-radius: 5px;margin-left: 5px;"></i>'
                        .'<i class="fa-solid fa-xmark" onclick="event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: red; padding: 2px;border-radius: 5px;"></i>';
                    } else {
                        $estado .= 'warning">'.$gast['estado'].'</span>';
                    }
                    break;
                case 'solicitado':
                    $fechaActual = date('Y-m-d');
                    $fechaGasto = date('Y-m-d', strtotime($gast['fecha']));
                    if ($fechaActual >= $fechaGasto) {
                        $estado .= 'danger">'.$gast['estado'].'</span>'
                        .'<i class="fa-solid fa-check" onclick="event.stopPropagation();aprobarMovimiento(true, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: green; padding: 2px;border-radius: 5px;margin-left: 5px;"></i>'
                        .'<i class="fa-solid fa-xmark" onclick="event.stopPropagation();aprobarMovimiento(false, this.parentElement.parentElement)" style="font-size: 14pt; color: white; background-color: red; padding: 2px;border-radius: 5px;"></i>';
                    } else {
                        $estado .= 'warning">Pendiente</span>';
                    }
                    break;
            }

            $pagina .= "<tr id='tbSelecRegistro' class='tableRegistroSearch2' style='border: none;font-size: 9pt;' onclick='seleccionarGastosAsociados(this);' style='".($estado=="Rechazado" || $estado=="Inactivo" ? "text-decoration: line-through;" : "")."'>
                <td id='td_id' style='width:5%; display: none; background-color: #efeded;color:red;'>".$gast['idgastos']."</td>
                <td  style='width:10%;border: none;'>".($key + 1)."/".count($gastos)."</td>
                <td  id='td_datos_3' style='width:15%;border: none;'>".$gast['fecha']."</td>
                <td  style='border: none;'>".$gast['descripcion']."</td>
                <td  id='td_datos_5' style='width: 20%;border: none;'>".$estado."</td>
                <td  id='td_datos_1' style='width: 15%;border: none;'>". number_format($gast['monto'],'0',',','.')."</td>
                <td  id='td_datos_2' style='width:10%; display: none;'>".$gast['motivo']."</td>
                <td  id='td_datos_16' style='display: none;'>".$gast['interconsulta_nombre']."</td>
                <td  id='td_datos_21' style='display: none;'>".$gast['modalidad']."</td>
                <td  id='td_datos_6' style='display: none;'>".$gast['tipo']."</td>
                <td  id='td_datos_8' style='display: none;'>".$gast['nroboleta']."</td>
                <td  id='td_datos_9' style='display: none;'>".$gast['banco']."</td>
                <td  id='td_datos_10' style='display: none;'>".$gast['nrocuenta']."</td>
                <td  id='td_datos_11' style='display: none;'>".$gast['arreglo']."</td>
                <td  id='td_datos_21' style='display: none;'>".$gast['usuarionombre']."</td>
                <td  id='' style='display: none;'>".$gast['nombrelocal']."</td>
                <td  id='td_datos_7' style='display:none;'>".$gast['cod_local']."</td>
                <td  id='td_datos_12' style='display:none;'>".$gast['url1']."</td>
                <td  id='td_datos_13' style='display:none;'>".$gast['descripcion']."</td>
                <td  id='td_datos_14' style='display:none;'>".$gast['motivo']."</td>
                <td  id='td_datos_15' style='display:none;'>".$gast['cod_interConsultaFK']."</td>
                <td  id='td_datos_17' style='display:none;'>".$gast['cod_usuario_autoriz']."</td>
                <td  id='td_datos_18' style='display:none;'>".$gast['usuario_autoriz_nombre']."</td>
                <td  id='td_datos_19' style='display:none;'>".$gast['fecha_autoriz']."</td>
                <td  id='td_datos_20' style='display:none;'>".$gast['cod_motivoIngresoEgresoFK']."</td>
            </tr>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => (isset($gastos[0]) ? $gastos[0] : null), "4" => (isset($proyecto[0]) ? $proyecto[0]['nombre'] : null), "5" => number_format($total_pendiente, 0, ',', '.')));
        exit;
    }

    function obtenerVistaProyectoGasto($filtros, $limite) {
        $cantRegistros= obtenerProyectoGasto($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerProyectoGasto($filtros, $limite);

        $pagina= "";
        $monto_total= 0;
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            $monto_total += intval($value["monto_total"]);
            $styleName=CargarStyleTable($styleName);
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='obtenerDatosProyectoGasto(this)'>
            <td id='td_id' style='width:10%;'>".str_pad($value['id'], 3, "0", STR_PAD_LEFT)."</td>
            <td id='td_datos_1' style='width:45%;'>".$value['nombre']."</td>
            <td id='td_datos_2' style='width:15%;'>".ucfirst($value['estado'])."</td>
            <td id='td_datos_3' style='width:15%;'>".$value['cantidad_gastos']."</td>
            <td id='td_datos_4' style='width:15%;'>".number_format($value['monto_total'], 0, ",", ".")."</td>
            </tr></table>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros, "6" => $monto_total));
    }

    function obtenerProyectoGasto($filtros= array(), $limite= 0) {
        $incluir_sin_gastos= isset($filtros['incluir_sin_gastos']) && $filtros['incluir_sin_gastos'] == 'true';
        $sqlFiltro= $incluir_sin_gastos ? "WHERE 1=1" : "WHERE EXISTS (SELECT 1 FROM gastos g WHERE g.cod_proyecto_gastoFK = pg.id AND g.estado!= 'Inactivo')";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($key == 'incluir_sin_gastos') {continue;}

            $sqlFiltro .= " AND ";
            switch ($key) {
                case 'estado':
                    $sqlFiltro .= "pg.estado = '$value'";
                    break;
                case 'nombre_exacto':
                    $sqlFiltro .= "pg.nombre = '$value'";
                    break;
                case 'ocultar_inactivo':
                    $sqlFiltro .= "pg.estado != 'inactivo'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "pg.$key = $value";
                    } else {
                        $sqlFiltro .= "pg.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT 
            pg.* ,
            IFNULL((SELECT SUM(g.monto) FROM gastos g WHERE g.cod_proyecto_gastoFK = pg.id), 0) as monto_total,
            IFNULL((SELECT COUNT(*) FROM gastos g WHERE g.cod_proyecto_gastoFK = pg.id), 0) as cantidad_gastos
            FROM proyectos_gasto pg
            $sqlFiltro ORDER BY pg.nombre ASC $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al obtener proyecto gasto: " . $stmt->error, "sql" => $sql);
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

    function abmProyectoGasto($id, $nombre, $estado) {
        $mysqli = conectar_al_servidor();

        if (empty($id)) {
            $sql = "INSERT INTO proyectos_gasto (nombre, estado) VALUES (?,?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ss', $nombre, $estado);
        } else {
            $parametros = array();
            $atributos = "";
            $ss = "";

            if ($nombre != NULL) {
                $atributos .= ($atributos == "" ? "" : ", ") . "nombre= ?";
                $ss .= "s";
                $parametros[] = $nombre;
            }
            if ($estado != NULL) {
                $atributos .= ($atributos == "" ? "" : ", ") . "estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
            }

            if ($atributos == "") {
                return $id;
            }

            $parametros[] = $id;
            $ss .= "i";

            $sql= "UPDATE proyectos_gasto SET $atributos WHERE id = ?";
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

        if (empty($id)) {
            $id = $stmt->insert_id;
        }

        $stmt->close();
        return $id;
    }

    if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
        $operacion = $_POST['accion'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificarProyectoGasto($operacion);
    }
?>

<?php
    require("conexion.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");
    include("subir_foto_base64.php");

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

        switch ($funt) {
            case 'cargar_imagen':
                $cod_inventario= mb_convert_encoding((string)($_POST['cod_inventario']), 'ISO-8859-1', 'UTF-8');
                $fotos= $_POST['fotos'];
                $ext= $_POST['exts'];
                for ($i= 0; $i < count($fotos); $i++) {
                    // Solo actualiza la imagen cuando realmente se envia una nueva foto.
                    if (!empty($ext[$i]) && !empty($fotos[$i])) {
                        $campo= "url" . ($i+1);
                        cargarImagenInventarioLocal($cod_inventario, $campo, $fotos[$i], $ext[$i]);
                    }
                }
                // Actualliza la foto de la factura solo si este se envia
                $fotoFactura= $_POST['fotoFactura'];
                $extFactura= $_POST['extFactura'];
                if (!empty($fotoFactura) || !empty($extFactura)) {
                    cargarImagenInventarioLocal($cod_inventario, 'url_factura', $fotoFactura, $extFactura);
                }
                // Carga la factura
                $fotoCompromiso= $_POST['fotoCompromiso'];
                $extCompromiso= $_POST['extCompromiso'];
                if (!empty($fotoCompromiso) || !empty($extCompromiso)) {
                    cargarImagenInventarioLocal($cod_inventario, 'url_compromiso', $fotoCompromiso, $extCompromiso);
                }
                echo json_encode(array("1" => "exito", "cod_inventario" => $cod_inventario));
                break;
            case 'nuevo/editar':
                $cod_inventario= isset($_POST['cod_inventario']) ? mb_convert_encoding((string)($_POST['cod_inventario']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre= isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $descripcion= isset($_POST['descripcion']) ? mb_convert_encoding((string)($_POST['descripcion']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cantidad= isset($_POST['cantidad']) ? mb_convert_encoding((string)($_POST['cantidad']), 'ISO-8859-1', 'UTF-8') : null;
                $costo= isset($_POST['costo']) ? mb_convert_encoding((string)($_POST['costo']), 'ISO-8859-1', 'UTF-8') : null;
                $observacion= isset($_POST['observacion']) ? mb_convert_encoding((string)($_POST['observacion']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuario_responsableFK= isset($_POST['cod_usuario_responsableFK']) ? mb_convert_encoding((string)($_POST['cod_usuario_responsableFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_marcaFK= isset($_POST['cod_usuario_responsableFK']) ? mb_convert_encoding((string)($_POST['cod_marcaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $modelo= isset($_POST['modelo']) ? mb_convert_encoding((string)($_POST['modelo']), 'ISO-8859-1', 'UTF-8') : null;
                $nro_serie= isset($_POST['nro_serie']) ? mb_convert_encoding((string)($_POST['nro_serie']), 'ISO-8859-1', 'UTF-8') : null;
                $estado_fisico= isset($_POST['estado_fisico']) ? mb_convert_encoding((string)($_POST['estado_fisico']), 'ISO-8859-1', 'UTF-8') : null;
                $categoria= isset($_POST['categoria']) ? mb_convert_encoding((string)($_POST['categoria']), 'ISO-8859-1', 'UTF-8') : null;

                $cod_inventario= abmInventarioLocal($cod_inventario, $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $modelo, $nro_serie, $cod_localFK, $cod_marcaFK,$cod_usuario_responsableFK, $user, $estado_fisico, $categoria);
                echo json_encode(array("1" => "exito", "cod_inventario" => $cod_inventario));
                break;
            case 'buscarVista':
                $cod_inventario= isset($_POST['cod_inventario']) ? mb_convert_encoding((string)($_POST['cod_inventario']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre= isset($_POST['nombre']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $ocultar_inactivo= isset($_POST['ocultar_inactivo']) ? mb_convert_encoding((string)($_POST['ocultar_inactivo']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario_responsable= isset($_POST['nombre_usuario_responsable']) ? mb_convert_encoding((string)($_POST['nombre_usuario_responsable']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros= array(
                    'cod_insumo'=> $cod_inventario,
                    'nombre'=> $nombre,
                    'estado'=> $estado,
                    'ocultar_inactivo'=> $ocultar_inactivo,
                    'cod_localFK'=> $cod_localFK,
                    'nombre_usuario_responsable'=> $nombre_usuario_responsable,
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaInsumosLocal($filtros, $limite);
                break;
            case 'obtenerUltimoId':
                $cod_inventario= obtenerUltimoId();
                echo json_encode(array("1" => "exito", "2" => $cod_inventario));
                break;
            case 'buscarHistorialResponsablesAnteriores':
                $cod_inventario= isset($_POST['cod_inventario']) ? mb_convert_encoding((string)($_POST['cod_inventario']), 'ISO-8859-1', 'UTF-8') : null;
                $result= obtenerHistorialResponsablesAnteriores(array('cod_insumoFK' => $cod_inventario));

                $pagina= "";
                foreach ($result as $key => $valor) {
                    $pagina .= '<tr>
                        <td style="width: 10%;">'.$valor["id"].'</td>
                        <td style="width: 60%;">'.$valor["nombre_usuarioFK_responsable_anterior"].'</td>
                        <td style="width: 30%;">'.$valor["fecha_creacion"].'</td>
                    </tr>';
                }
                echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $result));
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerUltimoId() {
        $sql= "SELECT (MAX(cod_insumo) + 1) as max_cod_insumo FROM insumos_local";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();

        // Obtiene el primer resultado
        $row = $result->fetch_assoc();
        $result= $row['max_cod_insumo'];

        $stmt->close();
        return $result;
    }

    function obtenerVistaInsumosLocal($filtros, $limite) {
        $cantRegistros= obtenerInventarioLocal($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerInventarioLocal($filtros, $limite);

        $pagina= "";
        $monto_total= 0;
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            // Calcula la diferencia de dias entre hoy y la ultima edicion
            $cant_ultima_edicion= 0;
            $fecha_ultima_edicion = !empty($value['fecha_edit']) ? $value['fecha_edit'] : $value['fecha_creacion'];
            if (!empty($fecha_ultima_edicion)) {
                $fechaUltimaEdicion = (new DateTime($fecha_ultima_edicion))->setTime(0, 0, 0);
                $fechaHoy = (new DateTime())->setTime(0, 0, 0);
                $cant_ultima_edicion = intval($fechaUltimaEdicion->diff($fechaHoy)->format('%a'));
            }

            $monto_total += $value["costo"];
            $styleName=CargarStyleTable($styleName);
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='obtenerDatosInsumoLocal(this)'>
            <td id='td_id' style='width:5%;'>".str_pad($value['cod_insumo'], 3, "0", STR_PAD_LEFT)."</td>
            <td id='td_datos_1' style='width:15%;'>".$value['nombre']."</td>
            <td id='td_datos_18' style='width:10%;'>".$value['nombre_marca']."</td>
            <td id='td_datos_20' style='width: 10%;'>".$value['modelo']."</td>
            <td id='td_datos_6' style='display: none;'>".number_format($value['costo'], 0, ",", ".")."</td>
            <td style='width: 5%;'>".number_format($value['costo'], 0, ",", ".").(intval($value['costo']) == 0 ? ' <i class="fa-solid fa-triangle-exclamation" style="font-size: 14px; color: gold;"></i>' : '')."</td>
            <td id='td_datos_19' style='display:none;'>".$value['nro_serie']."</td>
            <td id='td_datos_2' style='display:none;'>".$value['descripcion']."</td>
            <td id='td_datos_3' style='width:20%;'>".$value['nombreLocal'].".</td>
            <td style='width:15%;'>".$value['nombre_usuario_responsable'].($value['url_compromiso'] ? ' <i class="fa-solid fa-square-check" style="font-size: 14px; color: green;"></i>' : '')."</td>
            <td id='td_datos_14' style='display:none;'>".$value['nombre_usuario_responsable']."</td>
            <td id='td_datos_4' style='width: 5%;'>".ucfirst($value['estado'])."</td>
            <td id='td_datos_14' style='dislay:none;'>".$value['nombre_usuarioFK_create']."</td>
            <td id='td_datos_5' style='display: none;'>".$value['cantidad']."</td>
            <td id='td_datos_7' style='display: none;'>".$value['observacion']."</td>
            <td id='td_datos_8' style='display: none;'>".$value['cod_localFK']."</td>
            <td id='td_datos_9' style='display: none;'>".$value['cod_usuarioFK_edit']."</td>
            <td id='td_datos_10' style='display: none;'>".$value['url1']."</td>
            <td id='td_datos_11' style='display: none;'>".$value['url2']."</td>
            <td id='td_datos_12' style='display: none;'>".$value['url3']."</td>
            <td id='td_datos_13' style='display: none;'>".$value['cod_usuario_responsableFK']."</td>
            <td id='td_datos_15' style='display: none;'>".$value['fecha_creacion']."</td>
            <td id='td_datos_16' style='display: none;'>".$value['nombre_usuarioFK_edit']."</td>
            <td id='td_datos_17' style='display: none;'>".$value['fecha_edit']."</td>
            <td id='td_datos_21' style='display: none;'>".$value['cod_marcaFK']."</td>
            <td id='td_datos_22' style='display: none;'>".$value['url_factura']."</td>
            <td id='td_datos_23' style='display: none;'>".$value['ci_usuario_responsable']."</td>
            <td id='td_datos_24' style='display: none;'>".$value['tel_usuario_responsable']."</td>
            <td id='td_datos_25' style='display: none;'>".$value['url_compromiso']."</td>
            <td id='td_datos_26' style='width: 5%;'>".$value['estado_fisico']."</td>
            <td id='td_datos_27' style='width: 15%;'>hace ".$cant_ultima_edicion." d.</td>
            <td id='td_datos_28' style='display: none;'>".$value['categoria']."</td>
            </tr></table>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros, "6" => $monto_total));
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
                case 'ocultar_inactivo':
                    $sqlFiltro .= "il.estado != 'inactivo'";
                    break;
                case 'nombre_usuario_responsable':
                    $sqlFiltro .= "(SELECT nombre_persona FROM persona WHERE cod_persona = cod_usuario_responsableFK) like '%$value%'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "il.$key = $value";
                    } else {
                        $sqlFiltro .= "il.$key like '%$value%'";
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
            il.* ,
            (SELECT nombre_persona FROM persona WHERE cod_persona = cod_usuario_responsableFK) as nombre_usuario_responsable,
            (SELECT rut_usuario FROM usuario WHERE cod_usuario = cod_usuario_responsableFK) as ci_usuario_responsable,
            (SELECT telefono FROM persona WHERE cod_persona = cod_usuario_responsableFK) as tel_usuario_responsable,
            (SELECT nombre_persona FROM persona WHERE cod_persona = cod_usuarioFK_create) as nombre_usuarioFK_create,
            (SELECT nombre_persona FROM persona WHERE cod_persona = cod_usuarioFK_edit) as nombre_usuarioFK_edit,
            (SELECT descripcion FROM marcas WHERE cod_marcas = il.cod_marcaFK) as nombre_marca,
            l.Nombre as nombreLocal FROM insumos_local il JOIN local l ON l.cod_local = il.cod_localFK
            $sqlFiltro ORDER BY il.cod_insumo ASC $limite";

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

    function obtenerHistorialResponsablesAnteriores($filtros= array()) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if ($value === null || $value === "") {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'nombre_usuario_responsable_anterior':
                    $sqlFiltro .= "(SELECT nombre_persona FROM persona WHERE cod_persona = hil.cod_usuarioFK_responsable_anterior) like '%$value%'";
                    break;
                case 'nombre_usuarioFK_edit':
                    $sqlFiltro .= "(SELECT nombre_persona FROM persona WHERE cod_persona = hil.cod_usuarioFK_edit) like '%$value%'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "hil.$key = $value";
                    } else {
                        $sqlFiltro .= "hil.$key like '%$value%'";
                    }
                    break;
            }
        }

        $sql= "SELECT
            hil.*,
            (SELECT nombre_persona FROM persona WHERE cod_persona = hil.cod_usuarioFK_responsable_anterior) as nombre_usuarioFK_responsable_anterior,
            (SELECT nombre_persona FROM persona WHERE cod_persona = hil.cod_usuarioFK_edit) as nombre_usuarioFK_edit,
            (SELECT nombre FROM insumos_local WHERE cod_insumo = hil.cod_insumoFK) as nombre_insumo
            FROM historial_insumo_local hil
            $sqlFiltro
            ORDER BY hil.fecha_creacion DESC, hil.id DESC";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al obtener historial de responsables: " . $stmt->error, "sql" => $sql);
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

    function cargarImagenInventarioLocal($cod_inventario, $campo, $foto, $ext)
    {
        $ruta= NULL;
        if (!empty($foto) || !empty($ext)) {
            $foto = substr($foto, strpos($foto, ",") + 1);
            $foto = base64_decode($foto);
            $id_foto = "";
            $donde = "../fotos/fotosInsumoLocal/";
            $id_foto = $cod_inventario;
            $id_f = subir_imagen_base64($donde, $foto, $id_foto, $ext);
            $ruta = "/GoodVentaAsisCap/fotos/fotosInsumoLocal/" . $cod_inventario . $id_f . '.' . $ext;
        }
        
        $mysqli=conectar_al_servidor();
        $consulta="Update insumos_local set ".$campo."=? where cod_insumo=? ";	

        $stmt = $mysqli->prepare($consulta);
        $ss='ss';
        $stmt->bind_param($ss,$ruta,$cod_inventario);
        if ( ! $stmt->execute()) {
            echo "Error";
            exit;
        }
    }

    function abmInventarioLocal($cod_inventario, $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $modelo, $nro_serie,$cod_localFK, $cod_marcaFK,$cod_usuario_responsableFK,$cod_usuarioFK_edit, $estado_fisico, $categoria) {
        $mysqli = conectar_al_servidor();

        //Limpia el campo de monto
        if ($costo) {
            $costo= str_replace('.', '', $costo);
        }

        if (empty($cod_inventario)) {
            $sql = "INSERT INTO insumos_local (cod_insumo, nombre, descripcion, estado, cantidad, costo, observacion, modelo, nro_serie,cod_localFK, cod_marcaFK, cod_usuario_responsableFK, cod_usuarioFK_create, estado_fisico, categoria) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('isssiisssiiiiss', $cod_inventario, $nombre, $descripcion, $estado, $cantidad, $costo, $observacion, $modelo, $nro_serie, $cod_localFK,$cod_marcaFK,$cod_usuario_responsableFK, $cod_usuarioFK_edit,$estado_fisico,$categoria);
        } else {
            $parametros = array();

            // Datos para auditoria
            $atributos = "fecha_edit= NOW()";

            $atributos .= ", cod_usuarioFK_edit= ?";
            $ss = "s";
            $parametros[] = $cod_usuarioFK_edit;
            
            // Datos a modificar
            if ($nombre != NULL) {
                $atributos .= ", nombre= ?";
                $ss .= "s";
                $parametros[] = $nombre;
            }
            if ($modelo != NULL) {
                $atributos .= ", modelo= ?";
                $ss .= "s";
                $parametros[] = $modelo;
            }
            if ($nro_serie != NULL) {
                $atributos .= ", nro_serie= ?";
                $ss .= "s";
                $parametros[] = $nro_serie;
            }
            if ($cod_marcaFK != NULL) {
                $atributos .= ", cod_marcaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_marcaFK;
            }
            if ($descripcion != NULL) {
                $atributos .= ", descripcion= ?";
                $ss .= "s";
                $parametros[] = $descripcion;
            }
            if ($estado != NULL) {
                $atributos .= ", estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
            }
            if ($cantidad != NULL) {
                $atributos .= ", cantidad= ?";
                $ss .= "s";
                $parametros[] = $cantidad;
            }
            if ($costo != NULL) {
                $atributos .= ", costo= ?";
                $ss .= "s";
                $parametros[] = $costo;
            }
            if ($observacion != NULL) {
                $atributos .= ", observacion= ?";
                $ss .= "s";
                $parametros[] = $observacion;
            }
            if ($cod_localFK != NULL) {
                $atributos .= ", cod_localFK= ?";
                $ss .= "s";
                $parametros[] = $cod_localFK;
            }
            if ($categoria != NULL) {
                $atributos .= ", categoria= ?";
                $ss .= "s";
                $parametros[] = $categoria;
            }
            if ($estado_fisico != NULL) {
                $atributos .= ", estado_fisico= ?";
                $ss .= "s";
                $parametros[] = $estado_fisico;
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
    $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
    verificar($operacion);
?>

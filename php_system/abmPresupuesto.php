<?php
include_once('quitarseparadormiles.php');
include_once("buscar_nivel.php");
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("classTable.php");

date_default_timezone_set('America/Asuncion');

function verificarOperacionPresupuesto($operacion)
{
    $user = mb_convert_encoding((string)($_POST['useru']), 'ISO-8859-1', 'UTF-8');
    $pass = mb_convert_encoding((string)($_POST['passu']), 'ISO-8859-1', 'UTF-8');
    $pass = str_replace("=", "+", $pass);
    $navegador = mb_convert_encoding((string)($_POST['navegador']), 'ISO-8859-1', 'UTF-8');

    $resp = verificar_navegador($user, $navegador, $pass);
    if ($resp != "ok") {
        echo json_encode(array("1" => "UI"));
        exit;
    }

        switch ($operacion) {
        case 'obtenerPresupuesto':
            $filtro = array(
                'id' => isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_clienteFK' => isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_usuarioFK_create' => isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null,
                'nombre_cedula_cliente' => isset($_POST['nombre_cedula_cliente']) ? mb_convert_encoding((string)($_POST['nombre_cedula_cliente']), 'ISO-8859-1', 'UTF-8') : null,
                'fecha_inicio' => isset($_POST['fecha_inicio']) ? mb_convert_encoding((string)($_POST['fecha_inicio']), 'ISO-8859-1', 'UTF-8') : null,
                'fecha_fin' => isset($_POST['fecha_fin']) ? mb_convert_encoding((string)($_POST['fecha_fin']), 'ISO-8859-1', 'UTF-8') : null,
            );
            $limite = (isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : NULL);

            $result= obtenerPresupuesto($filtro);
            $totalRegistros= count($result);

            $result= obtenerPresupuesto($filtro, $limite);
            
            $pagina= "";
            foreach ($result as $value) {
                $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding="5">
                    <tr id="tbSelecRegistro" onclick="obtenerDatosPresupuesto(this)" style="text-align: center;">
                        <td id="td_id" style="width: 5%;">'.$value['id'].'</td>
                        <td id="td_datos_1" style="width: 15%;">'.$value['fecha_create'].'</td>
                        <td id="td_datos_2" style="display: none;">'.$value['cant_cuotas'].'</td>
                        <td id="td_datos_3" style="display: none;">'.$value['cod_clienteFK'].'</td>
                        <td id="td_datos_4" style="width: 30%;text-align: left;">'.$value['nombre_cliente'].'</td>
                        <td id="td_datos_5" style="width: 10%;">'.$value['rut_cliente'].'</td>
                        <td id="td_datos_7" style="width: 20%;text-align: end;">'.number_format($value['monto_total'], 0, ',','.').' Gs.</td>
                        <td id="td_datos_6" style="width: 20%;">'.$value['nombre_usuarioFK_create'].'</td>
                    </tr>
                </table>';
            }
            echo json_encode(array("1" => "exito", "2" => $result, "3" => $pagina, "4" => count($result), "5" => $totalRegistros));
            break;

        case 'abmPresupuesto':
            $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
            $cant_cuotas = isset($_POST['cant_cuotas']) ? mb_convert_encoding((string)($_POST['cant_cuotas']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_clienteFK = isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null;

            $idPresupuesto = abmPresupuesto($id, $cant_cuotas, $cod_clienteFK, $user);
            echo json_encode(array("1" => "exito", "2" => $idPresupuesto));
            break;

        case 'obtenerDetallesPresupuesto':
            $filtro = array(
                'id' => isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_productoFK' => isset($_POST['cod_productoFK']) ? mb_convert_encoding((string)($_POST['cod_productoFK']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_presupuestoFK' => isset($_POST['cod_presupuestoFK']) ? mb_convert_encoding((string)($_POST['cod_presupuestoFK']), 'ISO-8859-1', 'UTF-8') : null
            );
            $limite = (isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : NULL);

            obtenerVistaDetallesPresupuesto($filtro, $limite);
            break;
        case 'eliminarDetallePresupuesto':
            $idDetalle = mb_convert_encoding((string)($_POST['idDetalle']), 'ISO-8859-1', 'UTF-8');
            
            eliminarDetallePresupuesto($idDetalle);
            echo json_encode(array("1" => "exito", "2" => $idDetalle));
            break;
        case 'abmDetallesPresupuesto':
            $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_productoFK = isset($_POST['cod_productoFK']) ? mb_convert_encoding((string)($_POST['cod_productoFK']), 'ISO-8859-1', 'UTF-8') : null;
            $cantidad = isset($_POST['cantidad']) ? mb_convert_encoding((string)($_POST['cantidad']), 'ISO-8859-1', 'UTF-8') : null;
            $precio = isset($_POST['precio']) ? mb_convert_encoding((string)($_POST['precio']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_presupuestoFK = isset($_POST['cod_presupuestoFK']) ? mb_convert_encoding((string)($_POST['cod_presupuestoFK']), 'ISO-8859-1', 'UTF-8') : null;

            $idDetalle = abmDetallesPresupuesto($id, $cod_productoFK, $cantidad, $precio, $cod_presupuestoFK);
            echo json_encode(array("1" => "exito", "2" => $idDetalle));
            break;
        default:
            echo json_encode(array("1" => "error", "2" => "Operacion $operacion no definida"));
            break;
    }
}

function eliminarDetallePresupuesto($idDetalle) {
    $sql= "DELETE FROM detalles_presupuesto WHERE id= ?";

    $mysqli = conectar_al_servidor();
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i',$idDetalle);

    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al obtener presupuesto: " . $stmt->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }
}

function obtenerVistaDetallesPresupuesto($filtro, $limite) {
    $registros= obtenerDetallesPresupuesto($filtro, $limite);

    $pagina= "";
    foreach ($registros as $value) {
        $nroId = rand(1, 1000);
        $pagina .= "<table id='tdDetalleVenta_".$nroId."' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
			. "<tr id='tbSelecRegistro' onclick='eliminarFila(this)'  name='tdDetallePresupuesto'>"
			. "<td  id='td_datos_1' style='width:10%;'>".$value['cod_producto']."</td>"
			. "<td  id='td_datos_2' style='width:50%;'>".$value['nombre_producto']."</td>"
			. "<td  id='td_datos_3' style='width:10%'>".$value['cantidad']."</td>"
			. "<td  id='td_datos_4' style='width:15%'>".$value['precio']."</td>"
			. "<td  id='td_datos_5' style='width:15%'>".$value['subTotal']."</td>"
			. "<td  id='td_datos_6' style='display:none'></td>"
			. "<td  id='td_datos_7' style='display:none'>". 0 ."</td>"
			. "<td  id='td_datos_8' style='display:none'>".$value['cantidad']."</td>"
			. "<td  id='td_datos_9' style='display:none'>". 0 ."</td>"
			. "<td  id='td_datos_10' style='display:none'>".$value['precio']."</td>"
			. "<td  id='td_datos_11' style='display:none'>".$value['subTotal']."</td>"
			. "<td style='display:none' > <button class='btn-eliminar' >❌</button> </td>"
			. "</tr>"
			. "</table>";
    }

    echo json_encode(array("1" => "exito", "2" => $registros, "3" => $pagina));
}

function obtenerPresupuesto($filtros = array(), $limite = 0)
{
    $sqlFiltro = "";
    foreach ($filtros as $key => $value) {
        if ($value === null || $value === "") {
            continue;
        }
        if ($sqlFiltro == "") {
            $sqlFiltro .= "WHERE ";
        } else {
            $sqlFiltro .= " AND ";
        }

        switch ($key) {
            case 'nombre_cliente':
                $sqlFiltro .= "(SELECT nombre_persona FROM persona pe JOIN cliente c ON c.cod_personaFK = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) like '%$value%'";
                break;
            case 'fecha_inicio':
                $sqlFiltro .= "fecha_create >= $value";
                break;
            case 'fecha_fin':
                $sqlFiltro .= "fecha_create <= $value";
                break;
            case 'nombre_cedula_cliente':
                $sqlFiltro .= "((SELECT nombre_persona FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) like '%$value%' OR (SELECT c.rut_cliente FROM cliente c WHERE c.cod_cliente = p.cod_clienteFK) LIKE '%$value%')";
                break;
            default:
                if (is_numeric($value)) {
                    $sqlFiltro .= "p.$key = $value";
                } else {
                    $sqlFiltro .= "p.$key like '%$value%'";
                }
                break;
        }
    }

    if ($limite == 0) {
        $limite = '';
    } else {
        $limite = "LIMIT $limite";
    }

    $sql = "SELECT 
            p.*,
            (SELECT nombre_persona FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as nombre_cliente,
            (SELECT c.rut_cliente FROM cliente c WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as rut_cliente,
            IFNULL((SELECT sum(precio * cantidad) FROM detalles_presupuesto WHERE cod_presupuestoFK = p.id), 0) AS monto_total,
            (SELECT nombre_persona FROM persona WHERE cod_persona = p.cod_usuarioFK_create) as nombre_usuarioFK_create
            FROM presupuesto p
            $sqlFiltro ORDER BY p.id DESC $limite";

    $mysqli = conectar_al_servidor();
    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al obtener presupuesto: " . $stmt->error, "sql" => $sql);
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

function abmPresupuesto($id, $cant_cuotas, $cod_clienteFK, $cod_usuarioFK_create)
{
    $mysqli = conectar_al_servidor();

    if ($cant_cuotas !== null && $cant_cuotas !== '') {
        $cant_cuotas = str_replace('.', '', $cant_cuotas);
    }

    if (empty($id)) {
        $sql = "INSERT INTO presupuesto (cant_cuotas, cod_clienteFK, cod_usuarioFK_create) VALUES (?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iii', $cant_cuotas, $cod_clienteFK, $cod_usuarioFK_create);
    } else {
        $parametros = array();
        $atributos = "";
        $ss = "";

        if ($cant_cuotas != null) {
            $atributos .= "cant_cuotas= ?";
            $ss .= "i";
            $parametros[] = $cant_cuotas;
        }
        if ($cod_clienteFK != null) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "cod_clienteFK= ?";
            $ss .= "i";
            $parametros[] = $cod_clienteFK;
        }

        if ($atributos == "") {
            return $id;
        }

        $parametros[] = $id;
        $ss .= "i";

        $sql = "UPDATE presupuesto SET $atributos WHERE id = ?";
        $stmt = $mysqli->prepare($sql);

        $refs = array();
        foreach ($parametros as $k => $v) {
            $refs[$k] = &$parametros[$k];
        }

        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
    }

    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al guardar presupuesto: " . $stmt->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }

    if (empty($id)) {
        $id = $stmt->insert_id;
    }

    $stmt->close();
    return $id;
}

function obtenerDetallesPresupuesto($filtros = array(), $limite = 0)
{
    $sqlFiltro = "";
    foreach ($filtros as $key => $value) {
        if ($value === null || $value === "") {
            continue;
        }
        if ($sqlFiltro == "") {
            $sqlFiltro .= "WHERE ";
        } else {
            $sqlFiltro .= " AND ";
        }

        switch ($key) {
            case 'nombre_producto':
                $sqlFiltro .= "(SELECT nombre_producto FROM producto WHERE cod_producto = dp.cod_productoFK) like '%$value%'";
                break;
            case 'cod_barra':
                $sqlFiltro .= "(SELECT cod_barra FROM producto WHERE cod_producto = dp.cod_productoFK) like '%$value%'";
                break;
            default:
                if (is_numeric($value)) {
                    $sqlFiltro .= "dp.$key = $value";
                } else {
                    $sqlFiltro .= "dp.$key like '%$value%'";
                }
                break;
        }
    }

    if ($limite == 0) {
        $limite = '';
    } else {
        $limite = "LIMIT $limite";
    }

    $sql = "SELECT
            dp.*,
            (precio * cantidad) AS subTotal,
            (SELECT nombre_producto FROM producto WHERE cod_producto = dp.cod_productoFK) as nombre_producto,
            (SELECT cod_producto FROM producto WHERE cod_producto = dp.cod_productoFK) as cod_producto
            FROM detalles_presupuesto dp
            $sqlFiltro ORDER BY dp.id DESC $limite";

    $mysqli = conectar_al_servidor();
    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al obtener detalles del presupuesto: " . $stmt->error, "sql" => $sql);
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

function abmDetallesPresupuesto($id, $cod_productoFK, $cantidad, $precio, $cod_presupuestoFK)
{
    $mysqli = conectar_al_servidor();

    if ($precio !== null && $precio !== '') {
        $precio = str_replace('.', '', $precio);
    }

    if (empty($id)) {
        $sql = "INSERT INTO detalles_presupuesto (cod_productoFK, precio, cantidad, cod_presupuestoFK) VALUES (?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iiii', $cod_productoFK, $precio, $cantidad, $cod_presupuestoFK);
    } else {
        $parametros = array();
        $atributos = "";
        $ss = "";

        if ($cod_productoFK != null) {
            $atributos .= "cod_productoFK= ?";
            $ss .= "i";
            $parametros[] = $cod_productoFK;
        }
        if ($precio != null) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "precio= ?";
            $ss .= "i";
            $parametros[] = $precio;
        }
        if ($cantidad != null) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "cantidad= ?";
            $ss .= "i";
            $parametros[] = $cantidad;
        }
        if ($cod_presupuestoFK != null) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "cod_presupuestoFK= ?";
            $ss .= "i";
            $parametros[] = $cod_presupuestoFK;
        }

        if ($atributos == "") {
            return $id;
        }

        $parametros[] = $id;
        $ss .= "i";

        $sql = "UPDATE detalles_presupuesto SET $atributos WHERE id = ?";
        $stmt = $mysqli->prepare($sql);

        $refs = array();
        foreach ($parametros as $k => $v) {
            $refs[$k] = &$parametros[$k];
        }

        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($ss), $refs));
    }

    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al guardar detalle del presupuesto: " . $stmt->error, "sql" => $sql);
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
    $operacion = mb_convert_encoding((string)($_POST['accion']), 'ISO-8859-1', 'UTF-8');
    verificarOperacionPresupuesto($operacion);
}
?>

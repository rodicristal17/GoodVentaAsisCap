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
                'cant_cuotas' => isset($_POST['cant_cuotas']) ? mb_convert_encoding((string)($_POST['cant_cuotas']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_clienteFK' => isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_usuarioFK_create' => isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null
            );
            $limite = (isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : NULL);
            echo json_encode(array("1" => "exito", "2" => obtenerPresupuesto($filtro, $limite)));
            break;

        case 'abmPresupuesto':
            $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
            $cant_cuotas = isset($_POST['cant_cuotas']) ? mb_convert_encoding((string)($_POST['cant_cuotas']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_clienteFK = isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_usuarioFK_create = isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : $user;

            $idPresupuesto = abmPresupuesto($id, $cant_cuotas, $cod_clienteFK, $cod_usuarioFK_create);
            echo json_encode(array("1" => "exito", "2" => $idPresupuesto));
            break;

        case 'obtenerDetallesPresupuesto':
            $filtro = array(
                'id' => isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_productoFK' => isset($_POST['cod_productoFK']) ? mb_convert_encoding((string)($_POST['cod_productoFK']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_presupuestoFK' => isset($_POST['cod_presupuestoFK']) ? mb_convert_encoding((string)($_POST['cod_presupuestoFK']), 'ISO-8859-1', 'UTF-8') : null
            );
            $limite = (isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : NULL);
            echo json_encode(array("1" => "exito", "2" => obtenerDetallesPresupuesto($filtro, $limite)));
            break;

        case 'abmDetallesPresupuesto':
            $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_productoFK = isset($_POST['cod_productoFK']) ? mb_convert_encoding((string)($_POST['cod_productoFK']), 'ISO-8859-1', 'UTF-8') : null;
            $precio = isset($_POST['precio']) ? mb_convert_encoding((string)($_POST['precio']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_presupuestoFK = isset($_POST['cod_presupuestoFK']) ? mb_convert_encoding((string)($_POST['cod_presupuestoFK']), 'ISO-8859-1', 'UTF-8') : null;

            $idDetalle = abmDetallesPresupuesto($id, $cod_productoFK, $precio, $cod_presupuestoFK);
            echo json_encode(array("1" => "exito", "2" => $idDetalle));
            break;
        default:
            echo json_encode(array("1" => "error", "2" => "Operacion $operacion no definida"));
            break;
    }
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
            (SELECT nombre_persona FROM persona pe JOIN cliente c ON c.cod_personaFK = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as nombre_cliente,
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
                $sqlFiltro .= "(SELECT nombre FROM producto WHERE cod_producto = dp.cod_productoFK) like '%$value%'";
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
            (SELECT nombre FROM producto WHERE cod_producto = dp.cod_productoFK) as nombre_producto,
            (SELECT cod_barra FROM producto WHERE cod_producto = dp.cod_productoFK) as cod_barra
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

function abmDetallesPresupuesto($id, $cod_productoFK, $precio, $cod_presupuestoFK)
{
    $mysqli = conectar_al_servidor();

    if ($precio !== null && $precio !== '') {
        $precio = str_replace('.', '', $precio);
    }

    if (empty($id)) {
        $sql = "INSERT INTO detalles_presupuesto (cod_productoFK, precio, cod_presupuestoFK) VALUES (?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iii', $cod_productoFK, $precio, $cod_presupuestoFK);
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
    $operacion = mb_convert_encoding((string)($_POST['funt']), 'ISO-8859-1', 'UTF-8');
    verificarOperacionPresupuesto($operacion);
}
?>

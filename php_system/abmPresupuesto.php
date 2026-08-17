<?php
include_once('quitarseparadormiles.php');
include_once("buscar_nivel.php");
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("classTable.php");
include_once("abmproductos.php");
require_once("trabajo_laboratorio_helper.php");

date_default_timezone_set('Etc/GMT+3');

function normalizarPlanVendidoPresupuesto($valor)
{
    if ($valor === null) {
        return null;
    }

    $valor = trim((string)($valor));
    if ($valor === '') {
        return null;
    }

    if (!in_array($valor, array('total', 'prioritario'), true)) {
        return false;
    }

    return $valor;
}

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
                'plan_vendido' => isset($_POST['plan_vendido']) ? mb_convert_encoding((string)($_POST['plan_vendido']), 'ISO-8859-1', 'UTF-8') : null,
                'num_factura' => isset($_POST['num_factura']) ? mb_convert_encoding((string)($_POST['num_factura']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_localFK' => isset($_POST['cod_localFK']) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null,
                'nombre_usuario_create' => isset($_POST['nombre_usuario_create']) ? mb_convert_encoding((string)($_POST['nombre_usuario_create']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_clienteFK' => isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null,
                'cod_usuarioFK_create' => isset($_POST['cod_usuarioFK_create']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK_create']), 'ISO-8859-1', 'UTF-8') : null,
                'nombre_cedula_cliente' => isset($_POST['nombre_cedula_cliente']) ? mb_convert_encoding((string)($_POST['nombre_cedula_cliente']), 'ISO-8859-1', 'UTF-8') : null,
                'fecha_inicio' => isset($_POST['fecha_inicio']) ? mb_convert_encoding((string)($_POST['fecha_inicio']), 'ISO-8859-1', 'UTF-8') : null,
                'fecha_fin' => isset($_POST['fecha_fin']) ? mb_convert_encoding((string)($_POST['fecha_fin']), 'ISO-8859-1', 'UTF-8') : null,
            );
            $limite = (isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 25);
            $offset = (isset($_POST['offset']) ? mb_convert_encoding((string)($_POST['offset']), 'ISO-8859-1', 'UTF-8') : 0);

            $totalRegistros= contarPresupuesto($filtro);
            $result= obtenerPresupuesto($filtro, $limite, $offset);
            
            $pagina= "";
            foreach ($result as $value) {
                $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding="5">
                    <tr id="tbSelecRegistro" onclick="obtenerDatosPresupuesto(this)" style="text-align: center;">
                        <td id="td_id" style="width: 5%;">'.$value['id'].'</td>
                        <td id="td_datos_1" style="display: none;">'.$value['fecha_create'].'</td>
                        <td style="width: 15%;">'.date("d-m-Y H:i:s", strtotime($value['fecha_create'])).'</td>
                        <td id="td_datos_2" style="display: none;">'.$value['cant_cuotas'].'</td>
                        <td id="td_datos_3" style="display: none;">'.$value['cod_clienteFK'].'</td>
                        <td id="td_datos_4" style="width: 15%;text-align: left;">'.$value['nombre_cliente'].'</td>
                        <td id="td_datos_5" style="width: 10%;">'.$value['ci_cliente'].'</td>
                        <td id="td_datos_7" style="width: 10%;text-align: end;">'.number_format($value['monto_total'], 0, ',','.').' Gs.</td>
                        <td id="td_datos_8" style="width: 10%;text-align: end;">'.number_format($value['monto_total_prioritario'], 0, ',','.').' Gs.</td>
                        <td id="td_datos_9" style="width: 10%;text-transform: capitalize;">'.$value['plan_vendido'].'</td>
                        <td id="td_datos_10" style="width: 5%;"><div style= "text-decoration: underline;color: blue;font-weight: bold;" onclick="obtenerDatosVenta('.$value['cod_ventaFK'].', \'divListPresupuesto\')">'.$value['num_factura'].'</div></td>
                        <td id="td_datos_6" style="width: 15%;">'.$value['nombre_usuarioFK_create'].'</td>
                        <td id="td_datos_11" style="display: none;">'.$value['nombre_zona'].'</td>
                        <td id="td_datos_12" style="display: none;">'.$value['idzonaFk'].'</td>
                        <td id="td_datos_13" style="display: none;">'.$value['rut_cliente'].'</td>
                        <td id="td_datos_14" style="display: none;">'.$value['whapp'].'</td>
                        <td id="td_datos_15" style="display: none;">'.$value['fechanac'].'</td>
                        <td id="td_datos_16" style="display: none;">'.$value['telefono_cliente'].'</td>
                        <td id="td_datos_17" style="display: none;">'.$value['direccion_cliente'].'</td>
                        <td id="td_datos_18" style="display: none;">'.$value['referencia_cliente'].'</td>
                        <td id="td_datos_19" style="display: none;">'.$value['lugar_trabajo_cliente'].'</td>
                        <td id="td_datos_20" style="display: none;">'.$value['direccion_trabajo_cliente'].'</td>
                        <td id="td_datos_21" style="display: none;">'.$value['salario_cliente'].'</td>
                        <td id="td_datos_22" style="display: none;">'.$value['antiguedad_cliente'].'</td>
                        <td id="td_datos_23" style="display: none;">'.$value['telefono_trabajo_1_cliente'].'</td>
                        <td id="td_datos_24" style="display: none;">'.$value['telefono_trabajo_2_cliente'].'</td>
                        <td id="td_datos_25" style="display: none;">'.$value['acceso_credito_cliente'].'</td>
                    </tr>
                </table>';
            }
            echo json_encode(array("1" => "exito", "2" => $result, "3" => $pagina, "4" => count($result), "5" => $totalRegistros));
            break;

        case 'abmPresupuesto':
            $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
            $cant_cuotas = isset($_POST['cant_cuotas']) ? mb_convert_encoding((string)($_POST['cant_cuotas']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_clienteFK = isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_ventaFK = isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
            $plan_vendido = isset($_POST['plan_vendido']) ? mb_convert_encoding((string)($_POST['plan_vendido']), 'ISO-8859-1', 'UTF-8') : null;
            $plan_vendido = normalizarPlanVendidoPresupuesto($plan_vendido);

            if ($plan_vendido === false) {
                echo json_encode(array("1" => "error", "mensaje" => "El plan vendido debe ser total o prioritario."));
                exit;
            }

            $idPresupuesto = abmPresupuesto($id, $cant_cuotas, $cod_clienteFK, $user, $cod_ventaFK, $plan_vendido);
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
            $solo_eliminar_prioritario = mb_convert_encoding((string)($_POST['solo_eliminar_prioritario']), 'ISO-8859-1', 'UTF-8');
            $cod_presupuestoFK = mb_convert_encoding((string)($_POST['cod_presupuestoFK']), 'ISO-8859-1', 'UTF-8');

            if ($solo_eliminar_prioritario == 'true') {
                abmDetallesPresupuesto($idDetalle, NULL, NULL, NULL, 0, 0, $user, $cod_presupuestoFK);
            } else {
                eliminarDetallePresupuesto($idDetalle);
            }
            echo json_encode(array("1" => "exito", "2" => $idDetalle));
            break;
        case 'abmDetallesPresupuesto':
            $id = isset($_POST['id']) ? mb_convert_encoding((string)($_POST['id']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_productoFK = isset($_POST['cod_productoFK']) ? mb_convert_encoding((string)($_POST['cod_productoFK']), 'ISO-8859-1', 'UTF-8') : null;
            $cantidad = isset($_POST['cantidad']) ? mb_convert_encoding((string)($_POST['cantidad']), 'ISO-8859-1', 'UTF-8') : null;
            $precio = isset($_POST['precio']) ? mb_convert_encoding((string)($_POST['precio']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_presupuestoFK = isset($_POST['cod_presupuestoFK']) ? mb_convert_encoding((string)($_POST['cod_presupuestoFK']), 'ISO-8859-1', 'UTF-8') : null;
            $cod_clienteFK = isset($_POST['cod_clienteFK']) ? mb_convert_encoding((string)($_POST['cod_clienteFK']), 'ISO-8859-1', 'UTF-8') : null;
            $es_prioritario = isset($_POST['es_prioritario']) ? mb_convert_encoding((string)($_POST['es_prioritario']), 'ISO-8859-1', 'UTF-8') : null;
            $es_alternativo = isset($_POST['es_alternativo']) ? mb_convert_encoding((string)($_POST['es_alternativo']), 'ISO-8859-1', 'UTF-8') : null;

            $idDetalle = abmDetallesPresupuesto($id, $cod_productoFK, $cantidad, $precio, $es_prioritario, $es_alternativo, $user, $cod_presupuestoFK, $cod_clienteFK);
            $paginaprecios=buscardetallesprecios($cod_productoFK, $precio,0);
            
            echo json_encode(array("1" => "exito", "2" => $idDetalle, "3" => $paginaprecios));
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
    $paginaPrioritario= "";
    foreach ($registros as $value) {
        //$nroId = rand(1, 1000);
        $paginaprecios=buscardetallesprecios($value['cod_producto'], $value['precio'],0);
        $justificacionPresupuesto = htmlspecialchars(isset($value['justificacion_presupuesto']) ? $value['justificacion_presupuesto'] : '', ENT_QUOTES, 'UTF-8');

        $nroId = $value['id'];
        $elemento= "<table id='tdDetalleVenta_".$nroId."' class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='5'>"
			. "<tr id='tbSelecRegistro' onclick='eliminarFila(this)'  name='tdDetallePresupuesto'>"
			. "<td  id='td_datos_1' style='width:10%;'>".$value['cod_barra']."</td>"
			. "<td  id='td_datos_2' style='width:50%;'>".$value['nombre_producto']."</td>"
			. "<td  id='td_datos_3' class='presupuesto-doc-cantidad-acciones' style='width:10%'><button type='button' class='btn-eliminar presupuesto-doc-trash-btn' title='Eliminar tratamiento' onclick='event.stopPropagation(); eliminarFila(this); return false;'><i class='fa-solid fa-trash-can'></i></button><span>".$value['cantidad']."</span></td>"
			. "<td  id='td_datos_4' style='width:15%'>".number_format($value['precio'], 0, ",", ".")."</td>"
			. "<td  id='td_datos_5' style='width:15%'>".number_format($value['subTotal'], 0, ",", ".")."</td>"
			. "<td  id='td_datos_6' style='display:none'></td>"
			. "<td  id='td_datos_7' style='display:none'>". 0 ."</td>"
			. "<td  id='td_datos_8' style='display:none'>".$value['cantidad']."</td>"
			. "<td  id='td_datos_9' style='display:none'>". 0 ."</td>"
			. "<td  id='td_datos_10' style='display:none'>".$value['precio']."</td>"
			. "<td  id='td_datos_11' style='display:none'>".$value['subTotal']."</td>"
			. "<td  id='td_datos_12' style='display:none'>".$value['es_prioritario']."</td>"
			. "<td  id='td_datos_13' style='display:none'>".$value['es_alternativo']."</td>"
			. "<td  id='td_datos_14' style='display:none'>".$value['cod_producto']."</td>"
			. "<td  id='td_datos_15' style='display:none'>".$paginaprecios."</td>"
			. "<td  id='td_datos_16' style='display:none'>".$justificacionPresupuesto."</td>"
			. "</tr>"
			. "</table>";
        if ($value['es_alternativo'] != 1 && $value['es_alternativo'] != "1") {
            $pagina .= $elemento;
        }
        if ($value['es_prioritario'] == 1 || $value['es_prioritario'] == "1") {
            $paginaPrioritario .= $elemento;
        }
    }

    echo json_encode(array("1" => "exito", "2" => $registros, "3" => $pagina, "4" => $paginaPrioritario));
}

function obtenerSqlFiltroPresupuesto($filtros = array())
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
                $sqlFiltro .= "fecha_create >= '$value'";
                break;
            case 'fecha_fin':
                $sqlFiltro .= "fecha_create <= '$value 23:59:59'";
                break;
            case 'nombre_cedula_cliente':
                $sqlFiltro .= "((SELECT nombre_persona FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) like '%$value%' OR (SELECT c.rut_cliente FROM cliente c WHERE c.cod_cliente = p.cod_clienteFK) LIKE '%$value%')";
                break;
            case 'nombre_usuario_create':
                $sqlFiltro .= "(SELECT nombre_persona FROM persona WHERE cod_persona = p.cod_usuarioFK_create) like '%$value%'";
                break;
            case 'num_factura': 
                $sqlFiltro .= "(SELECT num_factura FROM venta WHERE cod_venta = p.cod_ventaFK) like '%$value%'";
                break;
            case 'cod_localFK':
                $sqlFiltro .= "(SELECT cod_localFK FROM usuario WHERE cod_usuario = cod_usuarioFK_create) like '%$value%'";
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

    return $sqlFiltro;
}

function normalizarLimitePresupuesto($limite = 0, $offset = 0)
{
    $limite = (int)$limite;
    $offset = (int)$offset;

    if ($limite <= 0) {
        return "";
    }

    if ($limite > 100) {
        $limite = 100;
    }

    if ($offset < 0) {
        $offset = 0;
    }

    return "LIMIT $limite OFFSET $offset";
}

function contarPresupuesto($filtros = array())
{
    $sqlFiltro = obtenerSqlFiltroPresupuesto($filtros);
    $sql = "SELECT COUNT(*) AS total FROM presupuesto p $sqlFiltro";

    $mysqli = conectar_al_servidor();
    $stmt = $mysqli->prepare($sql);
    if (!$stmt->execute()) {
        $informacion = array("1" => "error", "mensaje" => "Error al contar presupuesto: " . $stmt->error, "sql" => $sql);
        echo json_encode($informacion);
        exit;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return isset($row['total']) ? (int)$row['total'] : 0;
}

function obtenerPresupuesto($filtros = array(), $limite = 0, $offset = 0)
{
    $sqlFiltro = obtenerSqlFiltroPresupuesto($filtros);

    if ($limite == 0) {
        $limite = '';
    } else {
        $limite = normalizarLimitePresupuesto($limite, $offset);
    }

    $sql = "SELECT 
            p.*,
            (SELECT z.nombre FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona JOIN zona z ON c.idzonaFk = z.idzona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as nombre_zona,
            (SELECT nombre_persona FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as nombre_cliente,
            (SELECT c.idzonaFk FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as idzonaFk,
            (SELECT c.whapp FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as whapp,
            (SELECT c.fechanac FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as fechanac,
            (SELECT c.rut_cliente FROM cliente c WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as rut_cliente,
            (SELECT c.ci_cliente FROM cliente c WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as ci_cliente,
            (SELECT pe.telefono FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as telefono_cliente,
            (SELECT pe.direccion FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as direccion_cliente,
            (SELECT pe.email FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as referencia_cliente,
            (SELECT c.lugardetrabajo FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as lugar_trabajo_cliente,
            (SELECT c.direcciontrab FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as direccion_trabajo_cliente,
            (SELECT c.salario FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as salario_cliente,
            (SELECT c.antiguedad FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as antiguedad_cliente,
            (SELECT c.teleftrab1 FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as telefono_trabajo_1_cliente,
            (SELECT c.teleftrab2 FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as telefono_trabajo_2_cliente,
            (SELECT c.accesocredito FROM persona pe JOIN cliente c ON c.cod_cliente = pe.cod_persona WHERE c.cod_cliente = p.cod_clienteFK LIMIT 1) as acceso_credito_cliente,
            IFNULL((SELECT sum(precio * cantidad) FROM detalles_presupuesto WHERE cod_presupuestoFK = p.id AND es_alternativo = 0), 0) AS monto_total,
            IFNULL((SELECT sum(precio * cantidad) FROM detalles_presupuesto WHERE cod_presupuestoFK = p.id AND es_prioritario = 1), 0) AS monto_total_prioritario,
            (SELECT num_factura FROM venta WHERE cod_venta = p.cod_ventaFK) AS num_factura,
            (SELECT nombre_persona FROM persona WHERE cod_persona = p.cod_usuarioFK_create) as nombre_usuarioFK_create,
            (SELECT cod_localFK FROM usuario WHERE cod_usuario = p.cod_usuarioFK_create) as nombre_usuarioFK_local
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

function abmPresupuesto($id, $cant_cuotas, $cod_clienteFK, $cod_usuarioFK_create, $cod_ventaFK, $plan_vendido)
{
    $mysqli = conectar_al_servidor();

    if ($cant_cuotas !== null && $cant_cuotas !== '') {
        $cant_cuotas = str_replace('.', '', $cant_cuotas);
    }

    if (empty($id)) {
        $codCliente = (int)$cod_clienteFK;
        if ($codCliente <= 0) {
            echo json_encode(array("1" => "error", "mensaje" => "Seleccione nuevamente el paciente antes de crear el presupuesto."));
            exit;
        }

        $stmtCliente = $mysqli->prepare("SELECT cod_cliente FROM cliente WHERE cod_cliente = ? LIMIT 1");
        if (!$stmtCliente) {
            error_log("Presupuesto: no se pudo preparar la validacion del paciente: ".$mysqli->error);
            echo json_encode(array("1" => "error", "mensaje" => "No se pudo validar el paciente seleccionado."));
            exit;
        }
        $stmtCliente->bind_param('i', $codCliente);
        if (!$stmtCliente->execute() || $stmtCliente->get_result()->num_rows === 0) {
            $stmtCliente->close();
            error_log("Presupuesto: codigo de cliente inexistente recibido en alta.");
            echo json_encode(array("1" => "error", "mensaje" => "El paciente seleccionado no tiene un registro de cliente valido. Vuelva a seleccionarlo."));
            exit;
        }
        $stmtCliente->close();
        $cod_clienteFK = $codCliente;

        $sql = "INSERT INTO presupuesto (cant_cuotas, cod_clienteFK, cod_usuarioFK_create, cod_ventaFK, plan_vendido) VALUES (?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            error_log("Presupuesto: no se pudo preparar el alta: ".$mysqli->error);
            echo json_encode(array("1" => "error", "mensaje" => "No se pudo preparar el nuevo presupuesto."));
            exit;
        }
        $stmt->bind_param('iiiis', $cant_cuotas, $cod_clienteFK, $cod_usuarioFK_create, $cod_ventaFK, $plan_vendido);
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
        if ($cod_ventaFK != null) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "cod_ventaFK= ?";
            $ss .= "i";
            $parametros[] = $cod_ventaFK;
        }
        if ($plan_vendido != null) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "plan_vendido= ?";
            $ss .= "s";
            $parametros[] = $plan_vendido;
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
        error_log("Presupuesto: fallo al ejecutar alta o edicion: ".$stmt->error);
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
            (SELECT cod_barra FROM producto WHERE cod_producto = dp.cod_productoFK) as cod_barra,
            (SELECT cod_producto FROM producto WHERE cod_producto = dp.cod_productoFK) as cod_producto,
            (SELECT descripcion_producto FROM producto WHERE cod_producto = dp.cod_productoFK) as justificacion_presupuesto
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

function abmDetallesPresupuesto($id, $cod_productoFK, $cantidad, $precio, $es_prioritario, $es_alternativo, $cod_usuarioFK_edit, $cod_presupuestoFK, $cod_clienteFK = null)
{
    if (empty($cod_presupuestoFK)) {
        $informacion = array("1" => "error", "mensaje" => "El código de presupuesto es requerido para guardar el detalle del presupuesto.");
        echo json_encode($informacion);
        exit;
    }
    $mysqli = conectar_al_servidor();
	$productoActual = null;
	$cantidadActual = null;
	if (!empty($id)) {
		$stmtDetalleActual = $mysqli->prepare("SELECT cod_productoFK, cantidad FROM detalles_presupuesto WHERE id=? LIMIT 1");
		if ($stmtDetalleActual) {
			$stmtDetalleActual->bind_param("i", $id);
			if ($stmtDetalleActual->execute()) {
				$detalleActual = $stmtDetalleActual->get_result()->fetch_assoc();
				if ($detalleActual) {
					$productoActual = $detalleActual["cod_productoFK"];
					$cantidadActual = $detalleActual["cantidad"];
				}
			}
			$stmtDetalleActual->close();
		}
	}
	$productoValidar = ($cod_productoFK !== null && $cod_productoFK !== "") ? $cod_productoFK : $productoActual;
	$cantidadValidar = ($cantidad !== null && $cantidad !== "") ? $cantidad : $cantidadActual;
	if ($productoValidar !== null && $productoValidar !== "" && function_exists("trabajoLaboratorioObtenerConfiguracionProducto")) {
		$configuracionLaboratorio = trabajoLaboratorioObtenerConfiguracionProducto($mysqli, $productoValidar);
		$modoIndividualizacion = !empty($configuracionLaboratorio["ok"]) ? $configuracionLaboratorio["modo_individualizacion"] : "cantidad_libre";
		$requiereLaboratorio = !empty($configuracionLaboratorio["ok"])
			&& !empty($configuracionLaboratorio["requiere_laboratorio"]);
		if ($requiereLaboratorio || $modoIndividualizacion !== "cantidad_libre") {
			$cantidadNormalizada = function_exists("quitarseparadormiles")
				? quitarseparadormiles(trim((string)$cantidadValidar))
				: str_replace(",", ".", trim((string)$cantidadValidar));
			$cantidadNumerica = is_numeric($cantidadNormalizada) ? (float)$cantidadNormalizada : 0.0;
			$esHistoricoSinModificar = !empty($id)
				&& (string)$productoActual === (string)$productoValidar
				&& abs((float)$cantidadActual - $cantidadNumerica) < 0.000001
				&& abs((float)$cantidadActual - 1.0) >= 0.000001;
			if ($cantidadNumerica !== 1.0 && !$esHistoricoSinModificar) {
				echo json_encode(array(
					"1" => "error",
					"codigo" => "cantidad_tratamiento_unitaria",
					"mensaje" => "Este tratamiento clinico debe agregarse con cantidad 1. Para trabajos fisicos independientes, agregue una fila por cada tratamiento; si un unico tratamiento abarca varias piezas, seleccione todas sus ubicaciones en el odontograma."
				));
				exit;
			}
		}
	}

    if ($cod_clienteFK !== null && $cod_clienteFK !== '') {
        $sqlValidarPresupuesto = "SELECT cod_clienteFK FROM presupuesto WHERE id = ? LIMIT 1";
        $stmtValidarPresupuesto = $mysqli->prepare($sqlValidarPresupuesto);
        $stmtValidarPresupuesto->bind_param('i', $cod_presupuestoFK);

        if (!$stmtValidarPresupuesto->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al validar el presupuesto: " . $stmtValidarPresupuesto->error, "sql" => $sqlValidarPresupuesto);
            echo json_encode($informacion);
            exit;
        }

        $resultValidarPresupuesto = $stmtValidarPresupuesto->get_result();
        if ($resultValidarPresupuesto->num_rows == 0) {
            $informacion = array("1" => "error", "mensaje" => "El presupuesto seleccionado no existe.");
            echo json_encode($informacion);
            exit;
        }

        $datosPresupuesto = $resultValidarPresupuesto->fetch_assoc();
        if ((string)$datosPresupuesto['cod_clienteFK'] !== (string)$cod_clienteFK) {
            $informacion = array("1" => "error", "mensaje" => "El presupuesto no pertenece al paciente seleccionado.");
            echo json_encode($informacion);
            exit;
        }
        $stmtValidarPresupuesto->close();
    }

    if ($precio !== null && $precio !== '') {
        $precio = str_replace('.', '', $precio);
    }

    if (empty($id)) {
        if ($es_prioritario === null || $es_prioritario === '') {
            $es_prioritario = 0;
        }
        if ($es_alternativo === null || $es_alternativo === '') {
            $es_alternativo = 0;
        }
        $sql = "INSERT INTO detalles_presupuesto (cod_productoFK, precio, cantidad, es_prioritario, es_alternativo, cod_presupuestoFK) VALUES (?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iiiiii', $cod_productoFK, $precio, $cantidad, $es_prioritario, $es_alternativo, $cod_presupuestoFK);
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
        if ($es_prioritario !== NULL) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "es_prioritario= ?";
            $ss .= "i";
            $parametros[] = $es_prioritario;
        }
        if ($es_alternativo !== NULL) {
            if ($atributos != "") {
                $atributos .= ", ";
            }
            $atributos .= "es_alternativo= ?";
            $ss .= "i";
            $parametros[] = $es_alternativo;
        }

        if ($atributos == "") {
            return $id;
        }

        // Id del registro a editar
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
    } else {
        // Actualiza la auditoria del presupuesto
        $sql = "UPDATE presupuesto SET cod_usuarioFK_edit= ?,fecha_edit= NOW() WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $cod_usuarioFK_edit, $cod_presupuestoFK);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar detalle del presupuesto: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }
    }

    $stmt->close();
    return $id;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Content-Type: application/json; charset=utf-8');
    $operacion = mb_convert_encoding((string)($_POST['accion']), 'ISO-8859-1', 'UTF-8');
    verificarOperacionPresupuesto($operacion);
}
?>

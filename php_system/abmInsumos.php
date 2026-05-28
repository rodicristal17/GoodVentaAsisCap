<?php
// ── Conexión ───────────────────────────────────────────────────────────────────
require_once("conexion.php");
include_once("verificar_navegador.php");
include_once("classTable.php");
include_once('quitarseparadormiles.php');

function ObtenerDatos($operacion)
{
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

    switch ($operacion) {
        case "nuevo":
        case "editar":
            $id_insumo = isset($_POST['id_insumo']) ? $_POST['id_insumo'] : 0;
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $cant_stock = $_POST['cant_stock'];
            $unidad_medida = $_POST['unidad_medida'];
            $estado = $_POST['estado'];
            $productos = isset($_POST['productos']) ? $_POST['productos'] : [];
            $cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];

            $id_insumo = mb_convert_encoding((string)($id_insumo), 'ISO-8859-1', 'UTF-8');
            $nombre = mb_convert_encoding((string)($nombre), 'ISO-8859-1', 'UTF-8');
            $descripcion = mb_convert_encoding((string)($descripcion), 'ISO-8859-1', 'UTF-8');
            $cant_stock = mb_convert_encoding((string)($cant_stock), 'ISO-8859-1', 'UTF-8');
            $unidad_medida = mb_convert_encoding((string)($unidad_medida), 'ISO-8859-1', 'UTF-8');
            $estado = mb_convert_encoding((string)($estado), 'ISO-8859-1', 'UTF-8');

            abm($id_insumo, $nombre, $descripcion, $cant_stock, $unidad_medida, $estado, $productos, $cantidades, $operacion);
            break;
        case "buscar":
            $codigo = isset($_POST["codigo"]) ? mb_convert_encoding((string)($_POST["codigo"]), 'ISO-8859-1', 'UTF-8') : "";
            $nombre = isset($_POST["nombre"]) ? mb_convert_encoding((string)($_POST["nombre"]), 'ISO-8859-1', 'UTF-8') : "";
            $estado = isset($_POST["estado"]) ? mb_convert_encoding((string)($_POST["estado"]), 'ISO-8859-1', 'UTF-8') : "";
            BuscarRegistro($codigo, $nombre, $estado);
            break;
        case "buscarmas":
            $codigo = isset($_POST["codigo"]) ? mb_convert_encoding((string)($_POST["codigo"]), 'ISO-8859-1', 'UTF-8') : "";
            $nombre = isset($_POST["nombre"]) ? mb_convert_encoding((string)($_POST["nombre"]), 'ISO-8859-1', 'UTF-8') : "";
            $estado = isset($_POST["estado"]) ? mb_convert_encoding((string)($_POST["estado"]), 'ISO-8859-1', 'UTF-8') : "";
            $registrocargado = isset($_POST["registrocargado"]) ? mb_convert_encoding((string)($_POST["registrocargado"]), 'ISO-8859-1', 'UTF-8') : 0;
            BuscarMasRegistro($codigo, $nombre, $estado, $registrocargado);
            break;
        case "buscarporcodigoeditar":
            $buscar = $_POST["buscar"];
            $buscar = mb_convert_encoding((string)($buscar), 'ISO-8859-1', 'UTF-8');
            buscarporcodigoeditar($buscar);
            break;
        case "EliminarInsumo":
            $id_insumo = $_POST["id_insumo"];
            $id_insumo = mb_convert_encoding((string)($id_insumo), 'ISO-8859-1', 'UTF-8');
            EliminarInsumo($id_insumo);
            break;
        case "obtener_productos_lista":
            obtenerProductosLista();
            break;
        case "obtener_productos_asociados":
            $id_insumo = $_POST['id_insumo'];
            $id_insumo = mb_convert_encoding((string)($id_insumo), 'ISO-8859-1', 'UTF-8');
            obtenerProductosAsociados($id_insumo);
            break;
        case "obtener_insumos_producto":
            $cod_producto = isset($_POST['cod_producto']) ? $_POST['cod_producto'] : "";
            $cod_producto = mb_convert_encoding((string)($cod_producto), 'ISO-8859-1', 'UTF-8');
            obtenerInsumosPorProducto($cod_producto);
            break;
        default:
            echo json_encode(array("1" => "ERROR", "mensaje" => "Operación no válida."));
            break;
    }
}

function abm($id_insumo, $nombre, $descripcion, $cant_stock, $unidad_medida, $estado, $productos, $cantidades, $operacion)
{
    $mysqli = conectar_al_servidor();

    if (empty($nombre)) {
        echo json_encode(array("1" => "ERROR", "mensaje" => "El campo Nombre es obligatorio."));
        exit;
    }

    $estado_db = ($estado === 'Activo') ? 1 : 0;

    if ($operacion === "nuevo") {
        $sql = "INSERT INTO insumosconsl (nombre, descripcion, cant_stock, unidad_medida, estado)
                VALUES (?, ?, ?, ?, ?)";
    } else { // editar
        $sql = "UPDATE insumosconsl SET nombre=?, descripcion=?, cant_stock=?, unidad_medida=?, estado=?
                WHERE id_insumo=?";
    }
    
    $stmt = $mysqli->prepare($sql);
    if ($operacion === "nuevo") {
        $stmt->bind_param("ssisi", $nombre, $descripcion, $cant_stock, $unidad_medida, $estado_db);
    } else {
        $stmt->bind_param("ssisii", $nombre, $descripcion, $cant_stock, $unidad_medida, $estado_db, $id_insumo);
    }

    if (!$stmt->execute()) {
        $informacion = array("1" => "error");
        echo json_encode($informacion);
        exit;
    }

    if ($operacion === "nuevo") {
        $id_insumo = $mysqli->insert_id;
    }

    // Gestionar productos asociados
    mysqli_query($mysqli, "DELETE FROM insumo_producto WHERE id_insumo=$id_insumo");
    foreach ($productos as $i => $cod) {
        $cod = mysqli_real_escape_string($mysqli, trim($cod));
        $qty = mysqli_real_escape_string($mysqli, trim($cantidades[$i] ?? 1));
        if (!empty($cod)) {
            mysqli_query($mysqli, "INSERT INTO insumo_producto (id_insumo, cod_producto, cantidad) VALUES ('$id_insumo','$cod','$qty')");
        }
    }

    echo json_encode(array("1" => "exito", "mensaje" => "Insumo guardado correctamente.", "id_insumo" => $id_insumo));
    exit;
}

function eliminarInsumo($id_insumo)
{
    $mysqli = conectar_al_servidor();

    $sql = "UPDATE insumosconsl SET estado = 0 WHERE id_insumo = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_insumo);

    if (!$stmt->execute()) {
        echo json_encode(array("1" => "ERROR", "mensaje" => "Error al eliminar insumo: " . $stmt->error));
        exit;
    }

    echo json_encode(array("1" => "exito", "mensaje" => "Insumo marcado como Inactivo."));
    exit;
}

function buscarInsumos($id_insumo, $nombre, $descripcion, $unidad_medida, $estado)
{
    $mysqli = conectar_al_servidor();
    $condiciones = [];
    $tipos = "";
    $parametros = [];

    if (!empty($id_insumo)) {
        $condiciones[] = "id_insumo = ?";
        $tipos .= "i";
        $parametros[] = $id_insumo;
    }
    if (!empty($nombre)) {
        $condiciones[] = "nombre LIKE ?";
        $tipos .= "s";
        $parametros[] = "%" . $nombre . "%";
    }
    if (!empty($descripcion)) {
        $condiciones[] = "descripcion LIKE ?";
        $tipos .= "s";
        $parametros[] = "%" . $descripcion . "%";
    }
    if (!empty($unidad_medida)) {
        $condiciones[] = "unidad_medida LIKE ?";
        $tipos .= "s";
        $parametros[] = "%" . $unidad_medida . "%";
    }
    if (!empty($estado)) {
        $condiciones[] = "estado = ?";
        $tipos .= "i";
        $parametros[] = ($estado === 'Activo') ? 1 : 0;
    } else {
        $condiciones[] = "estado = 1"; // Default to active if no state is specified
    }

    $sql = "SELECT id_insumo, nombre, descripcion, cant_stock, unidad_medida, estado
            FROM insumosconsl";
    if (!empty($condiciones)) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    $sql .= " ORDER BY nombre ASC";

    $stmt = $mysqli->prepare($sql);
    if (!empty($parametros)) {
        $stmt->bind_param($tipos, ...$parametros);
    }

    if (!$stmt->execute()) {
        echo json_encode(array("1" => "ERROR", "mensaje" => "Error al buscar insumos: " . $stmt->error));
        exit;
    }

    $result = $stmt->get_result();
    $filas = [];
    while ($fila = $result->fetch_assoc()) {
        $fila['estado'] = ($fila['estado'] == 1) ? 'Activo' : 'Inactivo';
        $filas[] = $fila;
    }

    echo json_encode(array("1" => "OK", "filas" => $filas));
    exit;
}

function obtenerProductosAsociados($id_insumo)
{
    $mysqli = conectar_al_servidor();
    $sql = "SELECT ip.cod_producto, p.nombre_producto, ip.cantidad
            FROM insumo_producto ip
            JOIN producto p ON ip.cod_producto = p.cod_producto
            WHERE ip.id_insumo = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_insumo);

    if (!$stmt->execute()) {
        echo json_encode(array("1" => "ERROR", "mensaje" => "Error al obtener productos asociados: " . $stmt->error));
        exit;
    }

    $result = $stmt->get_result();
    $productos = [];
    while ($fila = $result->fetch_assoc()) {
        $fila['nombre_producto'] = mb_convert_encoding((string)$fila['nombre_producto'], 'UTF-8', 'ISO-8859-1');
        $productos[] = $fila;
    }

    echo json_encode(array("1" => "exito", "productos" => $productos), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function obtenerInsumosPorProducto($cod_producto)
{
    $mysqli = conectar_al_servidor();
    $sql = "SELECT i.id_insumo, i.nombre, i.descripcion, i.cant_stock, i.unidad_medida, i.estado, ip.cantidad
            FROM insumo_producto ip
            JOIN insumosconsl i ON ip.id_insumo = i.id_insumo
            WHERE ip.cod_producto = ?
            ORDER BY i.nombre ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(array("1" => "ERROR", "mensaje" => "Error al preparar consulta de insumos: " . $mysqli->error));
        exit;
    }
    $stmt->bind_param("s", $cod_producto);

    if (!$stmt->execute()) {
        echo json_encode(array("1" => "ERROR", "mensaje" => "Error al obtener insumos del producto: " . $stmt->error));
        exit;
    }

    $result = $stmt->get_result();
    $insumos = [];
    while ($fila = $result->fetch_assoc()) {
        $fila['nombre'] = mb_convert_encoding((string)$fila['nombre'], 'UTF-8', 'ISO-8859-1');
        $fila['descripcion'] = mb_convert_encoding((string)$fila['descripcion'], 'UTF-8', 'ISO-8859-1');
        $fila['unidad_medida'] = mb_convert_encoding((string)$fila['unidad_medida'], 'UTF-8', 'ISO-8859-1');
        $fila['estado_texto'] = ((int)$fila['estado'] === 1) ? "Activo" : "Inactivo";
        $insumos[] = $fila;
    }

    echo json_encode(array("1" => "exito", "insumos" => $insumos), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function obtenerProductosLista()
{
    $mysqli = conectar_al_servidor();
    $sql = "SELECT cod_producto, nombre_producto FROM producto ORDER BY nombre_producto ASC";
    $result = mysqli_query($mysqli, $sql);
    $productos_lista = [];
    if ($result) {
        while ($p = mysqli_fetch_assoc($result)) {
            $p['nombre_producto'] = mb_convert_encoding((string)$p['nombre_producto'], 'UTF-8', 'ISO-8859-1');
            $productos_lista[] = $p;
        }
    }
    echo json_encode(array("1" => "exito", "productos_lista" => $productos_lista), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function BuscarRegistro($codigo, $nombre, $estado)
{
    $mysqli = conectar_al_servidor();
    $condiciones = [];
    $tipos = "";
    $parametros = [];

    if ($codigo !== "") {
        $condiciones[] = "id_insumo = ?";
        $tipos .= "i";
        $parametros[] = (int)$codigo;
    }

    if ($nombre !== "") {
        $condiciones[] = "(nombre LIKE ? OR descripcion LIKE ?)";
        $tipos .= "ss";
        $buscar = "%" . $nombre . "%";
        $parametros[] = $buscar;
        $parametros[] = $buscar;
    }

    $estado = trim((string)$estado);
    if ($estado !== "") {
        $condiciones[] = "estado = ?";
        $tipos .= "i";
        $parametros[] = (strcasecmp($estado, "Activo") === 0 || $estado === "1") ? 1 : 0;
    }

    $sql = "SELECT id_insumo, nombre, descripcion, cant_stock, unidad_medida, estado
            FROM insumosconsl";
    if (count($condiciones) > 0) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    $sql .= " ORDER BY nombre ASC LIMIT 100";

    $stmt = $mysqli->prepare($sql);
    if ($tipos !== "") {
        $stmt->bind_param($tipos, ...$parametros);
    }

    if (!$stmt->execute()) {
        echo json_encode(array("1" => "error", "mensaje" => $stmt->error));
        exit;
    }

    $result = $stmt->get_result();
    $filas = [];
    while ($fila = $result->fetch_assoc()) {
        $fila["nombre"] = mb_convert_encoding((string)$fila["nombre"], "UTF-8", "ISO-8859-1");
        $fila["descripcion"] = mb_convert_encoding((string)$fila["descripcion"], "UTF-8", "ISO-8859-1");
        $fila["unidad_medida"] = mb_convert_encoding((string)$fila["unidad_medida"], "UTF-8", "ISO-8859-1");
        $fila["estado_texto"] = ((int)$fila["estado"] === 1) ? "Activo" : "Inactivo";
        $filas[] = $fila;
    }

    echo json_encode(array("1" => "exito", "filas" => $filas, "3" => count($filas)), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function BuscarMasRegistro($codigo, $nombre, $estado, $registrocargado)
{
    BuscarRegistro($codigo, $nombre, $estado);
}

function buscarporcodigoeditar($buscar)
{
    $mysqli = conectar_al_servidor();
    $sql = "SELECT id_insumo, nombre, descripcion, cant_stock, unidad_medida, estado
            FROM insumosconsl
            WHERE id_insumo = ?";
    $stmt = $mysqli->prepare($sql);
    $id = (int)$buscar;
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        echo json_encode(array("1" => "error", "mensaje" => $stmt->error));
        exit;
    }

    $result = $stmt->get_result();
    $fila = $result->fetch_assoc();
    if (!$fila) {
        echo json_encode(array("1" => "NoExiste"));
        exit;
    }

    $fila["nombre"] = mb_convert_encoding((string)$fila["nombre"], "UTF-8", "ISO-8859-1");
    $fila["descripcion"] = mb_convert_encoding((string)$fila["descripcion"], "UTF-8", "ISO-8859-1");
    $fila["unidad_medida"] = mb_convert_encoding((string)$fila["unidad_medida"], "UTF-8", "ISO-8859-1");
    $fila["estado_texto"] = ((int)$fila["estado"] === 1) ? "Activo" : "Inactivo";

    echo json_encode(array("1" => "exito", "fila" => $fila), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function guardarInsumoPagina($operacion)
{
    $mysqli = conectar_al_servidor();

    $id_insumo = isset($_POST['id_insumo']) ? (int)$_POST['id_insumo'] : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $cant_stock = isset($_POST['cant_stock']) && $_POST['cant_stock'] !== '' ? (int)$_POST['cant_stock'] : 0;
    $unidad_medida = isset($_POST['unidad_medida']) ? trim($_POST['unidad_medida']) : '';
    $estado = isset($_POST['estado']) && $_POST['estado'] === 'Inactivo' ? 0 : 1;
    $productos = isset($_POST['productos']) && is_array($_POST['productos']) ? $_POST['productos'] : [];
    $cantidades = isset($_POST['cantidades']) && is_array($_POST['cantidades']) ? $_POST['cantidades'] : [];

    if ($nombre === '') {
        return array("tipo" => "err", "mensaje" => "El campo Nombre es obligatorio.");
    }

    if ($operacion === 'editar' && $id_insumo <= 0) {
        return array("tipo" => "err", "mensaje" => "Selecciona un insumo para editar.");
    }

    if ($operacion === 'nuevo') {
        $sql = "INSERT INTO insumosconsl (nombre, descripcion, cant_stock, unidad_medida, estado)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssisi", $nombre, $descripcion, $cant_stock, $unidad_medida, $estado);
    } else {
        $sql = "UPDATE insumosconsl
                SET nombre = ?, descripcion = ?, cant_stock = ?, unidad_medida = ?, estado = ?
                WHERE id_insumo = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssisii", $nombre, $descripcion, $cant_stock, $unidad_medida, $estado, $id_insumo);
    }

    if (!$stmt || !$stmt->execute()) {
        $error = $stmt ? $stmt->error : $mysqli->error;
        return array("tipo" => "err", "mensaje" => "No se pudo guardar el insumo: " . $error);
    }

    if ($operacion === 'nuevo') {
        $id_insumo = $mysqli->insert_id;
    }

    $mysqli->query("DELETE FROM insumo_producto WHERE id_insumo = " . (int)$id_insumo);
    foreach ($productos as $i => $cod_producto) {
        $cod_producto = trim($cod_producto);
        if ($cod_producto === '') {
            continue;
        }

        $cantidad = isset($cantidades[$i]) && $cantidades[$i] !== '' ? (float)$cantidades[$i] : 1;
        $stmtDetalle = $mysqli->prepare("INSERT INTO insumo_producto (id_insumo, cod_producto, cantidad) VALUES (?, ?, ?)");
        if ($stmtDetalle) {
            $stmtDetalle->bind_param("isd", $id_insumo, $cod_producto, $cantidad);
            $stmtDetalle->execute();
        }
    }

    return array("tipo" => "ok", "mensaje" => "Insumo guardado correctamente.");
}

function eliminarInsumoPagina()
{
    $mysqli = conectar_al_servidor();
    $id_insumo = isset($_POST['id_insumo']) ? (int)$_POST['id_insumo'] : 0;

    if ($id_insumo <= 0) {
        return array("tipo" => "err", "mensaje" => "Selecciona un insumo para eliminar.");
    }

    $stmt = $mysqli->prepare("UPDATE insumosconsl SET estado = 0 WHERE id_insumo = ?");
    $stmt->bind_param("i", $id_insumo);

    if (!$stmt->execute()) {
        return array("tipo" => "err", "mensaje" => "No se pudo marcar el insumo como inactivo: " . $stmt->error);
    }

    return array("tipo" => "ok", "mensaje" => "Insumo marcado como Inactivo.");
}

function cargarInsumosPagina()
{
    $mysqli = conectar_al_servidor();
    $filas = [];
    $sql = "SELECT id_insumo, nombre, descripcion, cant_stock, unidad_medida, estado
            FROM insumosconsl
            ORDER BY nombre ASC";
    $result = $mysqli->query($sql);

    if ($result) {
        while ($fila = $result->fetch_assoc()) {
            $filas[] = $fila;
        }
    }

    return $filas;
}

function cargarProductosListaPagina()
{
    $mysqli = conectar_al_servidor();
    $productos_lista = [];
    $sql = "SELECT cod_producto, nombre_producto FROM producto ORDER BY nombre_producto ASC";
    $result = $mysqli->query($sql);

    if ($result) {
        while ($fila = $result->fetch_assoc()) {
            $productos_lista[] = $fila;
        }
    }

    return $productos_lista;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    if (isset($_POST['funt'])) {
        $operacion = $_POST['funt'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        ObtenerDatos($operacion);
    }

    echo "";
    exit;
}

$msg = "";
$msg_tipo = "ok";

$request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
if ($request_method === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'nuevo' || $accion === 'editar') {
        $respuesta = guardarInsumoPagina($accion);
    } elseif ($accion === 'eliminar') {
        $respuesta = eliminarInsumoPagina();
    } else {
        $respuesta = array("tipo" => "err", "mensaje" => "Accion no valida.");
    }

    $msg = $respuesta["mensaje"];
    $msg_tipo = $respuesta["tipo"];
}

$filas = cargarInsumosPagina();
$total_filas = count($filas);
$productos_lista = cargarProductosListaPagina();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Insumos</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: auto; font-family: Arial, sans-serif; font-size: 13px; background: #f0f4f8; color: #222; }

    .pagina { display: flex; flex-direction: column; min-height: 100vh; }

    .titulo-bar {
      background: linear-gradient(135deg, #1a3d5c, #2f6b8f);
      color: #fff; padding: 10px 18px;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 3px solid #1abc9c;
    }
    .titulo-bar h2 { font-size: 16px; font-weight: 700; letter-spacing: .5px; }
    .titulo-bar .acciones-top { display: flex; gap: 8px; }
    .btn-icon {
      background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
      color: #fff; border-radius: 50%; width: 32px; height: 32px;
      font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center;
      transition: background .2s;
    }
    .btn-icon:hover { background: rgba(255,255,255,0.28); }
    .btn-icon.verde  { background: #1abc9c; border-color: #1abc9c; }
    .btn-icon.rojo   { background: #e74c3c; border-color: #e74c3c; }

    .leyenda-bar {
      background: #fff; border-top: 1px solid #dde3ea;
      padding: 6px 16px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    }
    .leyenda-item { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #555; }
    .leyenda-color { width: 14px; height: 14px; border-radius: 3px; flex-shrink: 0; }

    .tabla-wrap { flex: 1; overflow-x: auto; background: #fff; }
    table.tbl-main { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.tbl-main thead tr.cabecera th {
      background: #2c5f82; color: #fff;
      padding: 8px 10px; font-size: 11px; font-weight: 700;
      text-align: center; border-right: 1px solid #3a7099;
      white-space: nowrap;
    }
    table.tbl-main thead tr.filtros th {
      background: #3a7099; padding: 4px 6px;
    }
    table.tbl-main thead tr.filtros th select,
    table.tbl-main thead tr.filtros th input[type="text"] {
      font-size: 10px; height: 22px; border: none; border-radius: 10px;
      padding: 2px 6px; background: #fff; color: #222; width: 100%;
    }
    table.tbl-main tbody tr { cursor: pointer; transition: background .1s; }
    table.tbl-main tbody tr:nth-child(even) td { background: #f7fafc; }
    table.tbl-main tbody tr:hover td { background: #d6eaf8 !important; }
    table.tbl-main tbody tr.seleccionado td { background: #aed6f1 !important; }
    table.tbl-main tbody td {
      padding: 7px 10px; font-size: 12px;
      border-bottom: 1px solid #e8edf2; text-align: center;
    }
    table.tbl-main tbody tr.fila-inactivo td { background: #fadbd8; }
    table.tbl-main tbody tr.fila-inactivo:hover td { background: #f1948a !important; }

    .badge-activo   { background: #1abc9c; color: #fff; border-radius: 10px; padding: 2px 8px; font-size: 10px; font-weight: 700; }
    .badge-inactivo { background: #e74c3c; color: #fff; border-radius: 10px; padding: 2px 8px; font-size: 10px; font-weight: 700; }

    .footer-bar {
      background: #fff; border-top: 2px solid #d0dce8;
      padding: 8px 14px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .footer-bar .contadores { display: flex; gap: 24px; }
    .contador-box { display: flex; flex-direction: column; align-items: center; gap: 2px; }
    .contador-box span.lbl { font-size: 10px; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .contador-box input {
      width: 64px; height: 28px; text-align: center; font-size: 13px; font-weight: 700;
      border: 1px solid #ccd6e0; border-radius: 20px; background: #f4f8fb; color: #2c5f82;
    }
    .spacer { flex: 1; }
    .btn-nuevo   { background: #1abc9c; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 12px; cursor: pointer; font-weight: 600; }
    .btn-editar  { background: #2980b9; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 12px; cursor: pointer; }
    .btn-borrar  { background: #e74c3c; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 12px; cursor: pointer; }

    .msg-ok  { color: #1a7a4a; font-size: 12px; padding: 6px 14px; background: #eafaf1; border-left: 3px solid #1abc9c; margin: 0; }
    .msg-err { color: #a93226; font-size: 12px; padding: 6px 14px; background: #fdedec; border-left: 3px solid #e74c3c; margin: 0; }

    .modal-overlay {
      display: none; position: absolute; top: 0; left: 0;
      width: 100%; min-height: 100%;
      background: rgba(0,0,0,0.5); z-index: 9999;
      align-items: flex-start; justify-content: center; padding: 30px 0 50px;
    }
    .modal-overlay.activo { display: flex; }

    .modal-box {
      background: #fff; border-radius: 10px; width: 520px; max-width: 97vw;
      box-shadow: 0 10px 40px rgba(0,0,0,0.28);
      display: flex; flex-direction: column;
    }
    .modal-titulo {
      padding: 12px 18px; font-size: 16px; font-weight: 700; color: #1a3d5c;
      border-bottom: 1px solid #e0e8f0;
      display: flex; align-items: center; justify-content: space-between;
    }
    .modal-titulo button {
      background: transparent; border: none; font-size: 20px;
      color: #e74c3c; cursor: pointer; line-height: 1;
    }
    .modal-cuerpo { padding: 18px 20px; display: flex; flex-direction: column; gap: 14px; }

    .fila-campos { display: flex; gap: 14px; }
    .fila-campos .campo { flex: 1; min-width: 0; }
    .campo { display: flex; flex-direction: column; gap: 4px; }
    .campo label { font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .4px; }
    .campo input[type="text"],
    .campo input[type="number"],
    .campo select,
    .campo textarea {
      width: 100%; font-size: 13px; padding: 7px 10px;
      border: 1px solid #c8d6e0; border-radius: 5px;
      background: #fff; color: #222; outline: none;
      transition: border-color .2s;
    }
    .campo input:focus, .campo select:focus, .campo textarea:focus { border-color: #2980b9; }
    .campo textarea { height: 64px; resize: vertical; }

    .seccion-titulo {
      font-size: 12px; font-weight: 700; color: #2c5f82;
      background: #eaf3fb; padding: 6px 12px; border-radius: 5px;
      margin-bottom: 6px;
    }
    .col-headers-prod {
      display: flex; gap: 8px; font-size: 10px; color: #999;
      font-weight: 700; text-transform: uppercase; padding: 0 4px; margin-bottom: 4px;
    }
    .col-headers-prod span:first-child { flex: 1; }
    .col-headers-prod span:nth-child(2) { width: 80px; text-align: center; flex-shrink: 0; }
    .col-headers-prod span:nth-child(3) { width: 30px; }

    .fila-producto { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
    .fila-producto select {
      flex: 1 !important; min-width: 0 !important; height: 32px !important;
      font-size: 12px !important; border: 1px solid #c8d6e0 !important;
      border-radius: 5px !important; padding: 0 8px !important;
      background: #fff !important; color: #222 !important;
    }
    .fila-producto input[type="number"] {
      width: 80px !important; flex: 0 0 80px !important; height: 32px !important;
      font-size: 12px !important; text-align: center !important;
      border: 1px solid #c8d6e0 !important; border-radius: 5px !important;
      padding: 0 6px !important;
    }
    .fila-producto .btn-quitar {
      width: 28px; height: 28px; flex-shrink: 0;
      background: #e74c3c; color: #fff; border: none;
      border-radius: 4px; cursor: pointer; font-size: 14px; line-height: 1;
    }
    .btn-agregar-prod {
      background: #2980b9; color: #fff; border: none;
      border-radius: 4px; padding: 5px 12px; font-size: 11px;
      cursor: pointer; margin-top: 4px;
    }

    .modal-footer {
      padding: 12px 20px; border-top: 1px solid #e8edf2;
      display: flex; justify-content: center; gap: 12px;
      background: #f8fbfd; border-radius: 0 0 10px 10px;
    }
    .btn-modal-cancelar { background: #e74c3c; color: #fff; border: none; border-radius: 5px; padding: 8px 26px; font-size: 13px; cursor: pointer; font-weight: 600; }
    .btn-modal-guardar  { background: #1abc9c; color: #fff; border: none; border-radius: 5px; padding: 8px 26px; font-size: 13px; cursor: pointer; font-weight: 600; }
  </style>
</head>
<body>
<div class="pagina">

  <?php if ($msg): ?>
    <p class="msg-<?= $msg_tipo ?>"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <!-- ── Título superior ── -->
  <div class="titulo-bar">
    <h2>&#128230; INSUMOS</h2>
    <div class="acciones-top">
     
    </div>
  </div>

  <!-- ── Tabla ── -->
  <div class="tabla-wrap">
    <table class="tbl-main" id="tablaInsumos">
      <thead>
        <tr class="cabecera">
          <th style="width:55px;">#</th>
          <th style="width:180px;">NOMBRE</th>
          <th>DESCRIPCIÓN</th>
          <th style="width:80px;">STOCK</th>
          <th style="width:120px;">UNIDAD</th>
          <th style="width:80px;">ESTADO</th>
        </tr>
        <tr class="filtros">
          <th><input type="text" placeholder="ID..."></th>
          <th><input type="text" placeholder="Buscar..."></th>
          <th><input type="text" placeholder="Buscar..."></th>
          <th><input type="text" placeholder=""></th>
          <th><input type="text" placeholder="Buscar..."></th>
          <th>
            <select>
              <option value="">TODOS</option>
              <option>Activo</option>
              <option>Inactivo</option>
            </select>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($filas) > 0): ?>
          <?php foreach ($filas as $fila): ?>
            <?php
              $est_raw = $fila['estado'] ?? '';
              $est     = ($est_raw == '1' || strtolower(trim($est_raw)) === 'activo') ? 'activo' : 'inactivo';
              $clsFila = ($est !== 'activo') ? 'fila-inactivo' : '';
            ?>
            <tr class="<?= $clsFila ?>"
                data-fila='<?= htmlspecialchars(json_encode($fila, JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES) ?>'
                onclick="seleccionarFila(this, <?= (int)$fila['id_insumo'] ?>)">
              <td><?= htmlspecialchars($fila['id_insumo'])     ?></td>
              <td style="text-align:left;"><?= htmlspecialchars($fila['nombre'])        ?></td>
              <td style="text-align:left;"><?= htmlspecialchars($fila['descripcion'])   ?></td>
              <td><?= htmlspecialchars($fila['cant_stock'])    ?></td>
              <td><?= htmlspecialchars($fila['unidad_medida']) ?></td>
              <td>
                <?php if ($est === 'activo'): ?>
                  <span class="badge-activo">Activo</span>
                <?php else: ?>
                  <span class="badge-inactivo">Inactivo</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" style="padding:1.2rem;color:#aaa;font-style:italic;">No hay registros cargados todavía.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ── Leyenda ── -->
  <div class="leyenda-bar">
    <div class="leyenda-item">
      <div class="leyenda-color" style="background:#d6eaf8;border:1px solid #aed6f1;"></div>
      <span>Seleccionado</span>
    </div>
    <div class="leyenda-item">
      <div class="leyenda-color" style="background:#fadbd8;border:1px solid #f1948a;"></div>
      <span>Inactivo</span>
    </div>
  </div>

  <!-- ── Footer ── -->
  <div class="footer-bar">
    <div class="contadores">
      <div class="contador-box">
        <span class="lbl">Registros</span>
        <input type="text" id="inputTotal" value="<?= $total_filas ?>" readonly>
      </div>
      <div class="contador-box">
        <span class="lbl">Seleccionado</span>
        <input type="text" id="inputSelec" value="" readonly>
      </div>
    </div>
    <div class="spacer"></div>
    <button class="btn-nuevo"  onclick="abrirModalNuevo()">&#43; Nuevo</button>
    <button class="btn-editar" onclick="editarSeleccionado()">&#9998; Editar</button>
    <button class="btn-borrar" onclick="eliminarSeleccionado()">&#128465; Eliminar</button>
  </div>

</div><!-- /pagina -->

<!-- ══════════════════════════════════
     MODAL
══════════════════════════════════ -->
<div class="modal-overlay" id="modalNuevo">
  <div class="modal-box">

    <div class="modal-titulo">
      <span id="modal-titulo-texto">Abm de Insumos</span>
      <button onclick="cerrarModal()" title="Cerrar">&#10005;</button>
    </div>

    <form method="POST" action="">
      <input type="hidden" name="accion"    id="modal-accion" value="nuevo">
      <input type="hidden" name="id_insumo" id="modal-id"     value="">

      <div class="modal-cuerpo">

        <div class="campo">
          <label>Nombre <span style="color:#e74c3c">*</span></label>
          <input type="text" name="nombre" id="campo-nombre" placeholder="Nombre del insumo" required>
        </div>

        <div class="campo">
          <label>Descripción</label>
          <textarea name="descripcion" id="campo-descripcion" placeholder="Descripción (opcional)"></textarea>
        </div>

        <div class="fila-campos">
          <div class="campo">
            <label>Cantidad en stock</label>
            <input type="number" name="cant_stock" id="campo-stock" placeholder="0" min="0" step="any">
          </div>
          <div class="campo">
            <label>Unidad de medida</label>
            <select name="unidad_medida" id="campo-unidad">
              <option value="">— Seleccionar —</option>
              <option>Unidad</option><option>Kg</option><option>Gramo</option>
              <option>Litro</option><option>Mililitro</option>
              <option>Metro</option><option>Caja</option><option>Paquete</option>
            </select>
          </div>
        </div>

        <div class="campo" style="max-width:180px;">
          <label>Estado</label>
          <select name="estado" id="campo-estado">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>

        <div>
          <div class="seccion-titulo">&#128279; Productos que usan este insumo <span style="font-weight:400;color:#888;">(opcional)</span></div>
          <div class="col-headers-prod">
            <span>Producto</span>
            <span>Cantidad</span>
            <span></span>
          </div>
          <div id="contenedor-productos"></div>
          <button type="button" class="btn-agregar-prod" onclick="agregarFilaProducto()">&#43; Agregar producto</button>
        </div>

      </div><!-- /modal-cuerpo -->

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancelar" onclick="cerrarModal()">&#10005; Cancelar</button>
        <button type="submit" class="btn-modal-guardar">&#10003; Guardar</button>
      </div>
    </form>

  </div>
</div>

<script>
  const productosDisponibles = <?= json_encode($productos_lista, JSON_INVALID_UTF8_SUBSTITUTE) ?>;
  let filaSeleccionada = null, idSeleccionado = null;

  // ── Selección de fila ─────────────────────────────────────────────────────
  function seleccionarFila(tr, id) {
    if (filaSeleccionada) filaSeleccionada.classList.remove('seleccionado');
    if (filaSeleccionada === tr) {
      filaSeleccionada = null; idSeleccionado = null;
      document.getElementById('inputSelec').value = '';
    } else {
      tr.classList.add('seleccionado');
      filaSeleccionada = tr; idSeleccionado = id;
      document.getElementById('inputSelec').value = id;
    }
  }

  // ── Auto-resize iframe ────────────────────────────────────────────────────
  function notificarAltura() {
    const h = document.body.scrollHeight;
    if (window.parent && window.parent !== window)
      window.parent.postMessage({ iframeHeight: h }, '*');
  }
  new ResizeObserver(notificarAltura).observe(document.body);

  // ── Abrir modal NUEVO ─────────────────────────────────────────────────────
  function abrirModalNuevo() {
    document.getElementById('modal-accion').value        = 'nuevo';
    document.getElementById('modal-id').value            = '';
    document.getElementById('modal-titulo-texto').textContent = 'Nuevo Insumo';
    document.getElementById('campo-nombre').value        = '';
    document.getElementById('campo-descripcion').value   = '';
    document.getElementById('campo-stock').value         = '';
    document.getElementById('campo-unidad').value        = '';
    document.getElementById('campo-estado').value        = 'Activo';
    document.getElementById('contenedor-productos').innerHTML = '';
    document.getElementById('modalNuevo').classList.add('activo');
    setTimeout(notificarAltura, 50);
  }

  // ── Abrir modal EDITAR ────────────────────────────────────────────────────
  function editarSeleccionado() {
    if (!idSeleccionado) { alert('Seleccioná un registro primero.'); return; }

    const datos = JSON.parse(filaSeleccionada.getAttribute('data-fila'));

    document.getElementById('modal-accion').value        = 'editar';
    document.getElementById('modal-id').value            = datos.id_insumo;
    document.getElementById('modal-titulo-texto').textContent = 'Editar Insumo';
    document.getElementById('campo-nombre').value        = datos.nombre        ?? '';
    document.getElementById('campo-descripcion').value   = datos.descripcion   ?? '';
    document.getElementById('campo-stock').value         = datos.cant_stock    ?? '';
    document.getElementById('campo-unidad').value        = datos.unidad_medida ?? '';

    // Estado: BD guarda 0/1, el select espera 'Activo'/'Inactivo'
    document.getElementById('campo-estado').value =
      (datos.estado == '1' || datos.estado === 'Activo') ? 'Activo' : 'Inactivo';

    document.getElementById('contenedor-productos').innerHTML = '';
    document.getElementById('modalNuevo').classList.add('activo');
    setTimeout(notificarAltura, 50);
  }

  // ── Cerrar modal ──────────────────────────────────────────────────────────
  function cerrarModal() {
    document.getElementById('modalNuevo').classList.remove('activo');
    setTimeout(notificarAltura, 50);
  }

  document.getElementById('modalNuevo').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });

  // ── Filas de producto ─────────────────────────────────────────────────────
  function agregarFilaProducto() {
    const contenedor = document.getElementById('contenedor-productos');
    let opciones = '<option value="">— Seleccionar producto —</option>';
    productosDisponibles.forEach(p => {
      opciones += `<option value="${p.cod_producto}">${p.nombre_producto}</option>`;
    });
    const div = document.createElement('div');
    div.className = 'fila-producto';
    div.innerHTML = `
      <select name="productos[]">${opciones}</select>
      <input type="number" name="cantidades[]" value="1" min="0.01" step="any" placeholder="Cant.">
      <button type="button" class="btn-quitar" onclick="this.parentElement.remove()" title="Quitar">&#10005;</button>
    `;
    contenedor.appendChild(div);
    notificarAltura();
  }

  // Reabrir modal si hubo error de validación
  <?php if ($msg_tipo === 'err'): ?> abrirModalNuevo(); <?php endif; ?>

  // ── Eliminar ──────────────────────────────────────────────────────────────
  function eliminarSeleccionado() {
  if (!idSeleccionado) {
    alert('Seleccioná un registro primero.');
    return;
  }

  if (confirm('¿Deseás marcar este insumo como Inactivo?')) {

    // Crear formulario dinámico
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    // Acción
    const accion = document.createElement('input');
    accion.type = 'hidden';
    accion.name = 'accion';
    accion.value = 'eliminar';

    // ID
    const id = document.createElement('input');
    id.type = 'hidden';
    id.name = 'id_insumo';
    id.value = idSeleccionado;

    form.appendChild(accion);
    form.appendChild(id);

    document.body.appendChild(form);
    form.submit();
  }
}
</script>
</body>
</html>

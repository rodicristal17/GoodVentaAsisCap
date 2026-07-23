<?php

ob_start();
ini_set('display_errors', '0');

require("conexion.php");
include("verificar_navegador.php");

date_default_timezone_set('America/Asuncion');

function dashboard_json($datos)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function dashboard_param($nombre, $default = '')
{
    if (isset($_POST[$nombre])) {
        return $_POST[$nombre];
    }

    if (isset($_GET[$nombre])) {
        return $_GET[$nombre];
    }

    return $default;
}

function dashboard_to_iso($valor)
{
    return mb_convert_encoding((string)$valor, 'ISO-8859-1', 'UTF-8');
}

function dashboard_to_utf8($valor)
{
    return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
}

function dashboard_authenticated_user()
{
    $user = dashboard_to_iso(dashboard_param('useru'));
    $pass = dashboard_param('passu');

    if ($user == '') {
        $user = dashboard_to_iso(dashboard_param('user'));
    }

    if ($pass == '') {
        $pass = dashboard_param('pass');
    }

    $pass = str_replace('=', '+', $pass);
    $navegador = dashboard_to_iso(dashboard_param('navegador'));

    if ($user == '' || $pass == '' || $navegador == '') {
        dashboard_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }

    $resp = verificar_navegador($user, $navegador, $pass);

    if ($resp != 'ok') {
        dashboard_json(array('1' => 'UI', '2' => 'Sesion invalida'));
    }

    return $user;
}

function dashboard_permission_key($accessKey, $catalogPermission)
{
    /* El hilo de laboratorio es institucional: toda cuenta autenticada activa
       puede abrirlo y tomarlo, aunque el catalogo instalado conserve el
       permiso historico de esta entrada. */
    if (trim((string)$accessKey) === 'trabajos_mecanicos_dentales') {
        return '';
    }
    $catalogPermission = trim((string)$catalogPermission);

    if ($catalogPermission != '') {
        return $catalogPermission;
    }

    $map = array(
        'cargar_compras' => 'VERCARGADECOMPRAS',
        'cuentas_a_cobrar' => 'VERCUENTASACOBRAR',
        'cobrar_cuota' => 'VERCOBRARCUOTA',
        'cobros_realizados' => 'VERCOBROSREALIZADOS',
        'expediente_cliente' => 'VEREXPEDIENTEDELCLIENTE',
        'historial_venta' => 'VERHISTORIALVENTA',
        'productos' => 'VERLISTADOPRODUCTOS',
        'nueva_venta' => 'VERVENTA',
        'flujo_egreso_ingreso' => 'VERLISTADOEGRESOINGRESO',
        'cerrar_caja' => 'VERCERRARCAJA',
        'migrar_caja' => 'VERMIGRARCAJA',
        'recibir_caja' => 'VERRECIBIRCAJA',
        'pagos_programados' => 'VERPAGOPROGRAMADO',
        'historial_consulta' => 'VERHISTORIALCONSULTA',
        'calendario' => 'VERFORMULARIOCALENDARIO',
        'asignar_tareas' => 'VERASIGNARTAREASUSUARIO',
        'cargar_sueldo' => 'VERCARGARSUELDO',
        'cuentas_a_pagar' => 'VERCUENTASAPAGAR',
        'consulta_cajas' => 'VERCONSULTADECAJA',
        'historial_compras' => 'VERHISTORIALCOMPRA',
        'productos_garantia' => 'VERINFORMEGARANTIA',
        'productos_baja' => 'VERINFORMEPRODUCTOSDEBAJA',
        'despachar_productor' => 'HACERDESPACHO',
        'control_deposito' => 'VERCONTROLDEPOSITO',
        'listado_tareas_usuario' => 'VERLISTADOTAREASUSUARIO',
        'listado_consultorios' => 'VERFORMULARIOCONSULTORIO',
        'listado_locales' => 'VERLISTADODELOCALES',
        'listado_zonas' => 'VERLISTADODEZONAS',
        'listado_cobradores' => 'VERLISTADOCOBRADORES',
        'listado_clientes' => 'VERLISTADODECLIENTES',
        'listado_productos' => 'VERLISTADOPRODUCTOS',
        'listado_proveedor' => 'VERLISTADOPROVEEDORES',
        'listado_vendedores' => 'VERLISTADOVENDEDORES',
        'listado_caja' => 'VERLISTADODECAJA',
        'lista_factura_habilitadas' => 'VERFACTURASHABILITADAS',
        'imprimir_precio' => 'VERINFORMECODIGOBARRA',
        'informe_general_cuentas' => 'VERINFORMECUENTAGENERAL',
        'informe_evaluacion' => 'VERINFORMEEVALUACION',
        'informe_inventario' => 'VERINFORMEDEINVENTARIO',
        'informe_ganancia_venta' => 'VERINFORMEDEGANANCIAPORVENTA',
        'informe_prod_comprados' => 'VERINFORMEDEPRODUCTOSCOMPRADOS',
        'informe_prod_vendidos' => 'VERINFORMEDEPRODUCTOSVENDIDOS',
        'informe_ventas_canceladas' => 'VERINFORMEDEVENTASCANCELADAS',
        'informe_comision_cobrador' => 'VERINFORMEDECOMISIONCOBRADOR',
        'informe_vendedores' => 'VERINFORMEDECOMISIONVENDEDOR',
        'informe_pagos_eliminados' => 'VERINFORMEDEPAGOSELIMINADOS',
        'informe_solicitud_eliminado' => 'VERINFORMESOLICITUDELIMINADO',
        'catalogo' => 'VERCATALOGO',
        'clientes_inactivos' => 'VERCLIENTESINACTIVOS',
        'productos_despachados' => 'VERDESPACHADOS',
        'informe_compras_eliminados' => 'VERINFORMEDECOMPRASELIMINADO',
        'clientes_morosos' => 'VERINFORMEMOROSO',
        'usuarios' => 'VERLISTADOUSUARIO',
        'listado_acceso' => 'VERLISTADODEACCESO',
        'listado_niveles' => 'VERLISTADODENIVELES'
    );

    return isset($map[$accessKey]) ? $map[$accessKey] : '';
}

function dashboard_user_can_access($mysqli, $user, $permissionKey)
{
    $permissionKey = trim((string)$permissionKey);

    if ($permissionKey == '') {
        return true;
    }

    $sql = "SELECT COUNT(*) 
            FROM accesosuser au
            INNER JOIN listadodeacceso la ON la.idlistadodeacceso = au.idlistadodeaccesoFK
            WHERE au.usuarios_idusario = ?
              AND la.codigo = ?
              AND au.accion = 'SI'
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $tipos = 'ss';
    $stmt->bind_param($tipos, $user, $permissionKey);

    if (!$stmt->execute()) {
        return false;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_row();

    return isset($row[0]) && (int)$row[0] > 0;
}

function dashboard_format_access_row($row)
{
    return array(
        'access_id' => (int)$row['id'],
        'access_key' => dashboard_to_utf8($row['access_key']),
        'label' => dashboard_to_utf8($row['label']),
        'module_key' => dashboard_to_utf8($row['module_key']),
        'module_label' => dashboard_to_utf8($row['module_label']),
        'icon_key' => dashboard_to_utf8($row['icon_key']),
        'route_path' => dashboard_to_utf8($row['route_path']),
        'permission_key' => dashboard_to_utf8($row['permission_key']),
        'shortcut_order' => isset($row['shortcut_order']) ? (int)$row['shortcut_order'] : (isset($row['default_quick_order']) ? (int)$row['default_quick_order'] : 0),
        'is_default_quick_access' => isset($row['is_default_quick_access']) ? (int)$row['is_default_quick_access'] : 0
    );
}

function dashboard_filter_access_rows($mysqli, $user, $rows)
{
    $accesos = array();

    foreach ($rows as $row) {
        $accessKey = dashboard_to_utf8($row['access_key']);
        $permissionKey = dashboard_permission_key($accessKey, $row['permission_key']);

        if (!dashboard_user_can_access($mysqli, $user, $permissionKey)) {
            continue;
        }

        $row['permission_key'] = dashboard_to_iso($permissionKey);
        $accesos[] = dashboard_format_access_row($row);
    }

    return $accesos;
}

function dashboard_fetch_rows($mysqli, $sql, $types = '', $params = array())
{
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        dashboard_json(array('1' => 'error', '2' => 'No se pudo preparar la consulta de accesos'));
    }

    if ($types != '') {
        $refs = array();

        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }

        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $refs));
    }

    if (!$stmt->execute()) {
        dashboard_json(array('1' => 'error', '2' => 'No se pudo consultar los accesos'));
    }

    $result = $stmt->get_result();
    $rows = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function dashboard_catalog($mysqli, $user)
{
    $sql = "SELECT id, access_key, label, module_key, module_label, icon_key, route_path, permission_key,
                   is_default_quick_access, default_quick_order
            FROM dashboard_access_catalog
            WHERE is_active = 1
            ORDER BY module_label ASC, label ASC";

    $rows = dashboard_fetch_rows($mysqli, $sql);
    $accesos = dashboard_filter_access_rows($mysqli, $user, $rows);

    dashboard_json(array('1' => 'exito', '2' => $accesos, 'catalog' => $accesos));
}

function dashboard_default_shortcuts($mysqli, $user)
{
    $sql = "SELECT id, access_key, label, module_key, module_label, icon_key, route_path, permission_key,
                   is_default_quick_access, default_quick_order, default_quick_order AS shortcut_order
            FROM dashboard_access_catalog
            WHERE is_active = 1
              AND is_default_quick_access = 1
            ORDER BY default_quick_order ASC, label ASC";

    $rows = dashboard_fetch_rows($mysqli, $sql);
    return dashboard_filter_access_rows($mysqli, $user, $rows);
}

function dashboard_user_shortcuts_array($mysqli, $user)
{
    $sql = "SELECT c.id, c.access_key, c.label, c.module_key, c.module_label, c.icon_key, c.route_path, c.permission_key,
                   c.is_default_quick_access, c.default_quick_order, us.shortcut_order
            FROM dashboard_user_shortcuts us
            INNER JOIN dashboard_access_catalog c ON c.id = us.access_id
            WHERE us.user_id = ?
              AND us.is_visible = 1
              AND c.is_active = 1
            ORDER BY us.shortcut_order ASC, c.label ASC";

    $rows = dashboard_fetch_rows($mysqli, $sql, 's', array($user));
    $accesos = dashboard_filter_access_rows($mysqli, $user, $rows);

    if (count($accesos) > 0) {
        return array('has_custom' => 1, 'shortcuts' => $accesos);
    }

    return array('has_custom' => 0, 'shortcuts' => dashboard_default_shortcuts($mysqli, $user));
}

function dashboard_user_shortcuts($mysqli, $user)
{
    $datos = dashboard_user_shortcuts_array($mysqli, $user);
    dashboard_json(array('1' => 'exito', '2' => $datos['shortcuts'], 'shortcuts' => $datos['shortcuts'], 'has_custom' => $datos['has_custom']));
}

function dashboard_save_user_shortcuts($mysqli, $user)
{
    $raw = dashboard_param('shortcuts', '[]');
    $shortcuts = json_decode($raw, true);

    if (!is_array($shortcuts)) {
        dashboard_json(array('1' => 'error', '2' => 'Formato invalido'));
    }

    if (count($shortcuts) == 0) {
        dashboard_json(array('1' => 'error', '2' => 'Selecciona al menos un acceso'));
    }

    if (count($shortcuts) > 20) {
        dashboard_json(array('1' => 'error', '2' => 'Solo se permiten hasta 20 accesos rapidos'));
    }

    $ids = array();
    $seen = array();

    foreach ($shortcuts as $shortcut) {
        if (!isset($shortcut['access_id'])) {
            dashboard_json(array('1' => 'error', '2' => 'Acceso invalido'));
        }

        $id = (int)$shortcut['access_id'];

        if ($id <= 0 || isset($seen[$id])) {
            dashboard_json(array('1' => 'error', '2' => 'Accesos duplicados o invalidos'));
        }

        $seen[$id] = true;
        $ids[] = $id;
    }

    $idList = implode(',', $ids);

    $sql = "SELECT id, access_key, label, module_key, module_label, icon_key, route_path, permission_key,
                   is_default_quick_access, default_quick_order
            FROM dashboard_access_catalog
            WHERE is_active = 1
              AND id IN ($idList)";

    $rows = dashboard_fetch_rows($mysqli, $sql);
    $validRows = array();

    foreach ($rows as $row) {
        $permissionKey = dashboard_permission_key(dashboard_to_utf8($row['access_key']), $row['permission_key']);

        if (!dashboard_user_can_access($mysqli, $user, $permissionKey)) {
            dashboard_json(array('1' => 'NI', '2' => 'Acceso no autorizado'));
        }

        $validRows[(int)$row['id']] = true;
    }

    foreach ($ids as $id) {
        if (!isset($validRows[$id])) {
            dashboard_json(array('1' => 'error', '2' => 'Uno de los accesos no existe o esta inactivo'));
        }
    }

    $mysqli->begin_transaction();

    try {
        $stmtHide = $mysqli->prepare("UPDATE dashboard_user_shortcuts SET is_visible = 0 WHERE user_id = ?");

        if (!$stmtHide) {
            throw new Exception('No se pudo preparar la limpieza de accesos');
        }

        $stmtHide->bind_param('s', $user);

        if (!$stmtHide->execute()) {
            throw new Exception('No se pudo limpiar la configuracion anterior');
        }

        $stmtSave = $mysqli->prepare("INSERT INTO dashboard_user_shortcuts (user_id, access_id, shortcut_order, is_visible)
                                      VALUES (?, ?, ?, 1)
                                      ON DUPLICATE KEY UPDATE
                                        shortcut_order = VALUES(shortcut_order),
                                        is_visible = 1,
                                        updated_at = CURRENT_TIMESTAMP");

        if (!$stmtSave) {
            throw new Exception('No se pudo preparar el guardado');
        }

        $order = 1;

        foreach ($ids as $id) {
            $stmtSave->bind_param('sii', $user, $id, $order);

            if (!$stmtSave->execute()) {
                throw new Exception('No se pudo guardar un acceso');
            }

            $order++;
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        dashboard_json(array('1' => 'error', '2' => 'No se pudo guardar la configuracion'));
    }

    $datos = dashboard_user_shortcuts_array($mysqli, $user);
    dashboard_json(array('1' => 'exito', '2' => $datos['shortcuts'], 'shortcuts' => $datos['shortcuts'], 'has_custom' => 1));
}

$operacion = dashboard_to_iso(dashboard_param('funt'));
$user = dashboard_authenticated_user();
$mysqli = conectar_al_servidor();

if ($operacion == 'catalog' || $operacion == 'access_catalog') {
    dashboard_catalog($mysqli, $user);
}

if ($operacion == 'user_shortcuts') {
    dashboard_user_shortcuts($mysqli, $user);
}

if ($operacion == 'save_user_shortcuts') {
    dashboard_save_user_shortcuts($mysqli, $user);
}

dashboard_json(array('1' => 'error', '2' => 'Operacion invalida'));

?>

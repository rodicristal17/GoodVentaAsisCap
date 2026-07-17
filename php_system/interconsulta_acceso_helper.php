<?php

/**
 * Reglas de alcance compartidas por Hilos. El local se obtiene siempre de la
 * sesion autenticada; nunca se confia en un usuario o local enviado por AJAX.
 */

function interconsultaAccesoTienePermiso($codUsuario, $codigo)
{
    static $cache = array();
    $codUsuario = intval($codUsuario);
    $codigo = strtoupper(trim((string)$codigo));
    $clave = $codUsuario.'|'.$codigo;
    if (isset($cache[$clave])) {
        return $cache[$clave];
    }
    if ($codUsuario <= 0 || $codigo === '' || !function_exists('controldeaccesoacasas')) {
        $cache[$clave] = false;
        return false;
    }
    $cache[$clave] = controldeaccesoacasas($codUsuario, $codigo, " u.accion='SI' ") == 1;
    return $cache[$clave];
}

function interconsultaAccesoPuedeVerTodosLocales($codUsuario)
{
    // Los permisos del Centro de Facturas no deben ampliar el alcance de Hilos.
    // CAMBIARLOCAL conserva el comportamiento legacy para usuarios con alcance
    // administrativo sobre todos los locales.
    return interconsultaAccesoTienePermiso($codUsuario, 'CAMBIARLOCAL');
}

function interconsultaAccesoLocalPrincipal($codUsuario, $mysqli = null)
{
    static $cache = array();
    $codUsuario = intval($codUsuario);
    if (isset($cache[$codUsuario])) {
        return $cache[$codUsuario];
    }
    if ($codUsuario <= 0) {
        return 0;
    }

    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $local = 0;
    $stmt = $mysqli->prepare("SELECT cod_localFK FROM usuario WHERE cod_usuario=? AND estado='Activo' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $codUsuario);
        if ($stmt->execute()) {
            $fila = $stmt->get_result()->fetch_assoc();
            $local = $fila ? intval($fila['cod_localFK']) : 0;
        }
        $stmt->close();
    }
    if ($cerrar) {
        $mysqli->close();
    }
    $cache[$codUsuario] = $local;
    return $local;
}

function interconsultaAccesoUsuarioPuedeUsarLocal($codUsuario, $codLocal, $mysqli = null)
{
    $codUsuario = intval($codUsuario);
    $codLocal = intval($codLocal);
    if ($codUsuario <= 0 || $codLocal <= 0) {
        return false;
    }
    if (interconsultaAccesoPuedeVerTodosLocales($codUsuario)) {
        return true;
    }
    return interconsultaAccesoLocalPrincipal($codUsuario, $mysqli) === $codLocal;
}

function interconsultaAccesoCondicionLocalSql($codUsuario, $alias = 'ic', $mysqli = null)
{
    $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    if ($alias === '') {
        $alias = 'ic';
    }
    $codUsuario = intval($codUsuario);
    if ($codUsuario <= 0) {
        return '1=0';
    }
    if (interconsultaAccesoPuedeVerTodosLocales($codUsuario)) {
        return '1=1';
    }
    $codLocal = interconsultaAccesoLocalPrincipal($codUsuario, $mysqli);
    if ($codLocal <= 0) {
        return '1=0';
    }

    return "(".$alias.".cod_localFK=".$codLocal."
        OR EXISTS(
            SELECT 1
            FROM interconsulta_paciente_venta ia_ipv
            INNER JOIN venta ia_vt ON ia_vt.cod_venta=ia_ipv.cod_ventaFK
            WHERE ia_ipv.cod_interConsultaFK=".$alias.".cod_interConsulta
              AND ia_ipv.estado='activo'
              AND ia_vt.cod_local=".$codLocal."
            LIMIT 1
        )
        OR EXISTS(
            SELECT 1
            FROM venta ia_vtd
            WHERE ia_vtd.cod_venta=".$alias.".cod_ventaFK
              AND ia_vtd.cod_local=".$codLocal."
            LIMIT 1
        )
        OR ((IFNULL(".$alias.".cod_localFK,0)=0)
            AND EXISTS(
                SELECT 1
                FROM usuario ia_uc
                WHERE ia_uc.cod_usuario=".$alias.".cod_usuarioFK_create
                  AND ia_uc.cod_localFK=".$codLocal."
                LIMIT 1
            )))";
}

function interconsultaAccesoUsuarioPuedeAccederHilo($codInterConsulta, $codUsuario, $exigirActivo = false, $mysqli = null)
{
    $codInterConsulta = intval($codInterConsulta);
    $codUsuario = intval($codUsuario);
    if ($codInterConsulta <= 0 || $codUsuario <= 0) {
        return false;
    }
    $cerrar = false;
    if (!($mysqli instanceof mysqli)) {
        $mysqli = conectar_al_servidor();
        $cerrar = true;
    }
    $condicionLocal = interconsultaAccesoCondicionLocalSql($codUsuario, 'ic', $mysqli);
    $condicionEstado = $exigirActivo ? " AND ic.estado<>'inactivo'" : '';
    $sql = "SELECT 1 FROM interconsulta ic
            WHERE ic.cod_interConsulta=?".$condicionEstado."
              AND ".$condicionLocal."
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $permitido = false;
    if ($stmt) {
        $stmt->bind_param('i', $codInterConsulta);
        if ($stmt->execute()) {
            $permitido = $stmt->get_result()->num_rows > 0;
        }
        $stmt->close();
    }
    if ($cerrar) {
        $mysqli->close();
    }
    return $permitido;
}

?>

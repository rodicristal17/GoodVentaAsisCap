<?php

if (!function_exists('ProductoRiesgoFinancieroCaseSql')) {
function ProductoRiesgoFinancieroCaseSql($campoPrecio)
{
    return "CASE
        WHEN CAST(".$campoPrecio." AS DECIMAL(18,2)) <= 350000 THEN 1
        WHEN CAST(".$campoPrecio." AS DECIMAL(18,2)) <= 800000 THEN 2
        WHEN CAST(".$campoPrecio." AS DECIMAL(18,2)) <= 1500000 THEN 3
        WHEN CAST(".$campoPrecio." AS DECIMAL(18,2)) <= 3000000 THEN 4
        ELSE 5
    END";
}

function ProductoRiesgoFinancieroCamposDisponibles($mysqli)
{
    static $disponible = null;
    if ($disponible !== null) {
        return $disponible;
    }

    $sql = "SHOW COLUMNS FROM producto LIKE 'nivel_riesgo_financiero'";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt || !$stmt->execute()) {
        $disponible = false;
        return $disponible;
    }

    $result = $stmt->get_result();
    $disponible = mysqli_num_rows($result) > 0;
    return $disponible;
}

function ProductoRiesgoFinancieroNivelSql($mysqli, $aliasProducto)
{
    $caseNivel = ProductoRiesgoFinancieroCaseSql($aliasProducto.".precio_producto");
    if (ProductoRiesgoFinancieroCamposDisponibles($mysqli)) {
        return "COALESCE(".$aliasProducto.".nivel_riesgo_financiero, ".$caseNivel.")";
    }
    return $caseNivel;
}

function ProductoRiesgoFinancieroSelectSql($mysqli, $aliasProducto = "pr", $aliasCampo = "nivel_riesgo_financiero")
{
    return ProductoRiesgoFinancieroNivelSql($mysqli, $aliasProducto)." AS ".$aliasCampo;
}

function ProductoRiesgoFinancieroTextoSql($nivelSql)
{
    return "CASE
        WHEN (".$nivelSql.") = 1 THEN 'N1 Bajo'
        WHEN (".$nivelSql.") = 2 THEN 'N2 Moderado'
        WHEN (".$nivelSql.") = 3 THEN 'N3 Controlado'
        WHEN (".$nivelSql.") = 4 THEN 'N4 Alto'
        ELSE 'N5 Critico'
    END";
}

function ProductoRiesgoFinancieroClaseSql($nivelSql)
{
    return "CASE
        WHEN (".$nivelSql.") = 1 THEN 'riesgo-nivel-1'
        WHEN (".$nivelSql.") = 2 THEN 'riesgo-nivel-2'
        WHEN (".$nivelSql.") = 3 THEN 'riesgo-nivel-3'
        WHEN (".$nivelSql.") = 4 THEN 'riesgo-nivel-4'
        ELSE 'riesgo-nivel-5'
    END";
}

function ProductoRiesgoFinancieroBadgeSql($nivelSql, $extraClass = "")
{
    $extraClass = trim(preg_replace('/[^A-Za-z0-9_ -]/', '', (string)$extraClass));
    $extraClass = $extraClass != "" ? $extraClass." " : "";
    $textoSql = ProductoRiesgoFinancieroTextoSql($nivelSql);
    $claseSql = ProductoRiesgoFinancieroClaseSql($nivelSql);
    return "CONCAT('<span class=\"riesgo-financiero-badge ".$extraClass."', ".$claseSql.", '\" title=\"Riesgo financiero\">', ".$textoSql.", '</span>')";
}

function ProductoRiesgoFinancieroNormalizar($nivel)
{
    $nivel = intval($nivel);
    if ($nivel < 1) {
        return 1;
    }
    if ($nivel > 5) {
        return 5;
    }
    return $nivel;
}

function ProductoRiesgoFinancieroTexto($nivel)
{
    $nivel = ProductoRiesgoFinancieroNormalizar($nivel);
    if ($nivel == 1) { return "N1 Bajo"; }
    if ($nivel == 2) { return "N2 Moderado"; }
    if ($nivel == 3) { return "N3 Controlado"; }
    if ($nivel == 4) { return "N4 Alto"; }
    return "N5 Critico";
}

function ProductoRiesgoFinancieroTooltip($nivel)
{
    $nivel = ProductoRiesgoFinancieroNormalizar($nivel);
    if ($nivel == 1) { return "Bajo riesgo financiero. Puede realizarse al inicio del plan."; }
    if ($nivel == 2) { return "Riesgo moderado. Recomendado con entrega inicial o primera cuota."; }
    if ($nivel == 3) { return "Riesgo controlado. Requiere avance inicial del plan."; }
    if ($nivel == 4) { return "Alto riesgo. Requiere control administrativo."; }
    return "Riesgo critico. Requiere autorizacion o anticipo importante.";
}

function ProductoRiesgoFinancieroBadgeHtml($nivel, $extraClass = "")
{
    $nivel = ProductoRiesgoFinancieroNormalizar($nivel);
    $extraClass = trim(preg_replace('/[^A-Za-z0-9_ -]/', '', (string)$extraClass));
    $extraClass = $extraClass != "" ? " ".$extraClass : "";
    $texto = htmlspecialchars(ProductoRiesgoFinancieroTexto($nivel), ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars(ProductoRiesgoFinancieroTooltip($nivel), ENT_QUOTES, 'UTF-8');
    return "<span class='riesgo-financiero-badge".$extraClass." riesgo-nivel-".$nivel."' title='".$title."'>".$texto."</span>";
}
}

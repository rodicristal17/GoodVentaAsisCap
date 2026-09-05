<?php

/**
 * Construye el nombre visible de un paciente sin modificar los datos almacenados.
 * Los registros historicos que no tienen apellido separado conservan nombre_persona.
 */
function nombrePacienteSql($aliasPersona)
{
    $aliasPersona = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasPersona);
    if ($aliasPersona === '') {
        $aliasPersona = 'p';
    }

    return "TRIM(CONCAT_WS(CHAR(32),NULLIF(TRIM(".$aliasPersona.".apellido_persona),''),NULLIF(TRIM(".$aliasPersona.".nombre_persona),'')))";
}

/**
 * Devuelve una condicion de busqueda compatible con APELLIDO NOMBRE y NOMBRE APELLIDO.
 * El valor debe llegar escapado con mysqli::real_escape_string().
 */
function nombrePacienteBusquedaSql($aliasPersona, $valorEscapado)
{
    $aliasPersona = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$aliasPersona);
    if ($aliasPersona === '') {
        $aliasPersona = 'p';
    }

    $like = "'%".$valorEscapado."%'";
    $apellidoNombre = nombrePacienteSql($aliasPersona);
    $nombreApellido = "TRIM(CONCAT_WS(CHAR(32),NULLIF(TRIM(".$aliasPersona.".nombre_persona),''),NULLIF(TRIM(".$aliasPersona.".apellido_persona),'')))";

    return "(".$apellidoNombre." LIKE ".$like." OR ".$nombreApellido." LIKE ".$like.")";
}

?>

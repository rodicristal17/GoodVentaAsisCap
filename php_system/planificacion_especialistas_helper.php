<?php

/**
 * Utilidades puras de presentacion para Planificacion de especialistas.
 * Compatible con PHP 7.2 y sin acceso a base de datos.
 */

function planificacionNumeroConsultorioNombre($nombre)
{
    $texto = trim((string)$nombre);
    if (preg_match('/CONSULTORIO\s*([0-9]+)\s*$/i', $texto, $coincidencias)) {
        $numero = intval($coincidencias[1]);
        return $numero > 0 ? $numero : null;
    }
    return null;
}

function planificacionOrdenarYRotularConsultorios($consultorios)
{
    $mayorNumero = 0;
    foreach ($consultorios as $indice => $consultorio) {
        $numero = planificacionNumeroConsultorioNombre(
            isset($consultorio['nombre']) ? $consultorio['nombre'] : ''
        );
        $consultorios[$indice]['_numero_visual'] = $numero;
        if ($numero !== null && $numero > $mayorNumero) {
            $mayorNumero = $numero;
        }
    }

    usort($consultorios, function ($a, $b) {
        $numeroA = isset($a['_numero_visual']) ? $a['_numero_visual'] : null;
        $numeroB = isset($b['_numero_visual']) ? $b['_numero_visual'] : null;
        if ($numeroA !== null && $numeroB !== null && $numeroA !== $numeroB) {
            return $numeroA < $numeroB ? -1 : 1;
        }
        if ($numeroA !== null && $numeroB === null) {
            return -1;
        }
        if ($numeroA === null && $numeroB !== null) {
            return 1;
        }
        $comparacionNombre = strnatcasecmp(
            isset($a['nombre']) ? $a['nombre'] : '',
            isset($b['nombre']) ? $b['nombre'] : ''
        );
        if ($comparacionNombre !== 0) {
            return $comparacionNombre;
        }
        $idA = isset($a['id_consultorio']) ? intval($a['id_consultorio']) : 0;
        $idB = isset($b['id_consultorio']) ? intval($b['id_consultorio']) : 0;
        if ($idA === $idB) {
            return 0;
        }
        return $idA < $idB ? -1 : 1;
    });

    $siguienteFallback = $mayorNumero + 1;
    foreach ($consultorios as $indice => $consultorio) {
        $numero = $consultorio['_numero_visual'];
        if ($numero === null) {
            $numero = $siguienteFallback;
            $siguienteFallback++;
        }
        $consultorios[$indice]['etiqueta'] = 'C'.$numero;
        unset($consultorios[$indice]['_numero_visual']);
    }
    return $consultorios;
}

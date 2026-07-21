<?php
    require("conexion.php");
    require_once("solicitud_eliminado_helper.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");

    function mecanicoDentalTieneColumnaUsuario($mysqli) {
        static $disponible = null;
        if ($disponible !== null) {
            return $disponible;
        }

        $tabla = 'mecanico_dental';
        $columna = 'cod_usuarioFK';
        $sql = "SELECT 1 FROM information_schema.columns
                WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $disponible = false;
            return false;
        }
        $stmt->bind_param('ss', $tabla, $columna);
        $stmt->execute();
        $result = $stmt->get_result();
        $disponible = $result && $result->num_rows > 0;
        $stmt->close();
        return $disponible;
    }

    function mecanicoDentalResponderError($mensaje, $codigo = 'error_guardado') {
        echo json_encode(array(
            "1" => "error",
            "codigo" => $codigo,
            "mensaje" => $mensaje
        ));
        exit;
    }

    function mecanicoDentalTextoUtf8($valor) {
        return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
    }

    function mecanicoDentalTienePermisoServidor($mysqli, $cod_usuario, $codigo) {
        $cod_usuario = (int)$cod_usuario;
        $codigo = strtoupper(trim((string)$codigo));
        if ($cod_usuario <= 0 || $codigo === '') {
            return false;
        }
        $stmt = $mysqli->prepare(
            "SELECT 1 FROM accesosuser au
             INNER JOIN listadodeacceso la ON la.idlistadodeacceso=au.idlistadodeaccesoFK
             WHERE au.usuarios_idusario=? AND la.codigo=? AND au.accion='SI' LIMIT 1"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('is', $cod_usuario, $codigo);
        $ok = $stmt->execute() && $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $ok;
    }

    function mecanicoDentalExigirPermisoServidor($mysqli, $cod_usuario, $codigo) {
        if (!mecanicoDentalTienePermisoServidor($mysqli, $cod_usuario, $codigo)) {
            mecanicoDentalResponderError(
                'El usuario no tiene permiso para realizar esta operacion.',
                'accion_no_autorizada'
            );
        }
    }

    function mecanicoDentalValidarCuentaTelar($mysqli, $cod_usuario, $cod_mecanico_dental = 0) {
        if ($cod_usuario === null) {
            return;
        }
        if (!mecanicoDentalTieneColumnaUsuario($mysqli)) {
            mecanicoDentalResponderError(
                'La vinculacion con cuentas Telar aun no esta habilitada. Aplique primero la actualizacion del modulo de laboratorio.',
                'vinculo_usuario_no_disponible'
            );
        }

        $sqlUsuario = "SELECT u.cod_usuario, u.estado
                       FROM usuario u
                       WHERE u.cod_usuario = ? LIMIT 1";
        $stmtUsuario = $mysqli->prepare($sqlUsuario);
        if (!$stmtUsuario) {
            mecanicoDentalResponderError('No se pudo validar la cuenta Telar seleccionada.', 'error_validacion_usuario');
        }
        $stmtUsuario->bind_param('i', $cod_usuario);
        if (!$stmtUsuario->execute()) {
            mecanicoDentalResponderError('No se pudo validar la cuenta Telar seleccionada.', 'error_validacion_usuario');
        }
        $resultUsuario = $stmtUsuario->get_result();
        $usuario = $resultUsuario ? $resultUsuario->fetch_assoc() : null;
        $stmtUsuario->close();

        if (!$usuario || strtolower(trim((string)$usuario['estado'])) !== 'activo') {
            mecanicoDentalResponderError(
                'La cuenta Telar seleccionada no existe o no se encuentra activa.',
                'usuario_telar_inactivo'
            );
        }

        $sqlVinculo = "SELECT cod_mecanico_dental
                       FROM mecanico_dental
                       WHERE cod_usuarioFK = ? AND cod_mecanico_dental <> ? LIMIT 1";
        $stmtVinculo = $mysqli->prepare($sqlVinculo);
        if (!$stmtVinculo) {
            mecanicoDentalResponderError('No se pudo verificar el vinculo de la cuenta Telar.', 'error_validacion_vinculo');
        }
        $cod_mecanico_dental = (int)$cod_mecanico_dental;
        $stmtVinculo->bind_param('ii', $cod_usuario, $cod_mecanico_dental);
        if (!$stmtVinculo->execute()) {
            mecanicoDentalResponderError('No se pudo verificar el vinculo de la cuenta Telar.', 'error_validacion_vinculo');
        }
        $resultVinculo = $stmtVinculo->get_result();
        $vinculo = $resultVinculo ? $resultVinculo->fetch_assoc() : null;
        $stmtVinculo->close();

        if ($vinculo) {
            mecanicoDentalResponderError(
                'La cuenta Telar seleccionada ya esta vinculada a otro mecanico dental.',
                'usuario_telar_ya_vinculado'
            );
        }
    }

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

        $mysqliPermisos = conectar_al_servidor();
        mecanicoDentalExigirPermisoServidor($mysqliPermisos, (int)$user, 'VERLISTADOMECANICODENTAL');
        $puedeGestionarCuentaTelar = mecanicoDentalTienePermisoServidor(
            $mysqliPermisos,
            (int)$user,
            'GESTIONARTECNICOSLABORATORIO'
        );
        $mysqliPermisos->close();

        switch ($funt) {
            case "nuevo/editar":
                $cod_mecanico_dental = $_POST['cod_mecanico_dental'];
                $cod_mecanico_dental = mb_convert_encoding((string)($cod_mecanico_dental), 'ISO-8859-1', 'UTF-8');
                $nombre = mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8');
                $telefono_referencia = isset($_POST['telefono_referencia']) ? mb_convert_encoding((string)($_POST['telefono_referencia']), 'ISO-8859-1', 'UTF-8') : null;
                $direccion = isset($_POST['direccion']) ? mb_convert_encoding((string)($_POST['direccion']), 'ISO-8859-1', 'UTF-8') : null;
                $telefono = isset($_POST['telefono']) ? mb_convert_encoding((string)($_POST['telefono']), 'ISO-8859-1', 'UTF-8') : null;
                $estado = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_persona = isset($_POST['cod_personaFK']) ? mb_convert_encoding((string)($_POST['cod_personaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $actualizar_cuenta_telar = array_key_exists('cod_usuarioFK', $_POST);
                $cod_usuarioFK = null;
                if ($actualizar_cuenta_telar && trim((string)$_POST['cod_usuarioFK']) !== '') {
                    if (!$puedeGestionarCuentaTelar) {
                        mecanicoDentalResponderError(
                            'El usuario no tiene permiso para vincular cuentas Telar.',
                            'vinculo_usuario_no_autorizado'
                        );
                    }
                    if (!ctype_digit(trim((string)$_POST['cod_usuarioFK'])) || (int)$_POST['cod_usuarioFK'] <= 0) {
                        mecanicoDentalResponderError('La cuenta Telar seleccionada no es valida.', 'usuario_telar_invalido');
                    }
                    $cod_usuarioFK = (int)$_POST['cod_usuarioFK'];
                }
                if ($actualizar_cuenta_telar && !$puedeGestionarCuentaTelar) {
                    // Conserva el vinculo existente al editar otros datos del mecanico.
                    $actualizar_cuenta_telar = false;
                }

                $cod_mecanico_dental = abmMecanicoDental(
                    $nombre,
                    $telefono_referencia,
                    $direccion,
                    $telefono,
                    $estado,
                    $cod_persona,
                    $cod_mecanico_dental,
                    $cod_usuarioFK,
                    $actualizar_cuenta_telar
                );
                echo json_encode(array("1" => "exito", "cod_mecanico_dental" => $cod_mecanico_dental));
                break;
            case "buscarVista":
                $cod_mecanico_dental = isset($_POST['cod_mecanico_dental']) ? mb_convert_encoding((string)($_POST['cod_mecanico_dental']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['nombre']), 'ISO-8859-1', 'UTF-8') : null;
                $estado = isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros = array(
                    'cod_mecanico_dental' => $cod_mecanico_dental,
                    'nombre' => $nombre,
                    'estado' => $estado
                );
                
                $limite = isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaMecanicoDental($filtros, $limite);
                break;
            case 'buscarVistaOpciones':
                obtenerVistaOpcionesMecanicoDental(array('estado' => 'activo'), 0);
                break;
            case 'buscarUsuariosTelar':
                if (!$puedeGestionarCuentaTelar) {
                    mecanicoDentalResponderError(
                        'El usuario no tiene permiso para consultar o vincular cuentas Telar.',
                        'vinculo_usuario_no_autorizado'
                    );
                }
                $busqueda = isset($_POST['busqueda']) ? mb_convert_encoding((string)$_POST['busqueda'], 'ISO-8859-1', 'UTF-8') : '';
                $mecanico_actual = isset($_POST['cod_mecanico_dental']) && ctype_digit((string)$_POST['cod_mecanico_dental'])
                    ? (int)$_POST['cod_mecanico_dental']
                    : 0;
                $usuario_actual = isset($_POST['cod_usuario_actual']) && ctype_digit((string)$_POST['cod_usuario_actual'])
                    ? (int)$_POST['cod_usuario_actual']
                    : 0;
                obtenerUsuariosTelarDisponiblesMecanicoDental($busqueda, $mecanico_actual, $usuario_actual);
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerUsuariosTelarDisponiblesMecanicoDental($busqueda, $mecanico_actual = 0, $usuario_actual = 0) {
        $mysqli = conectar_al_servidor();
        if (!mecanicoDentalTieneColumnaUsuario($mysqli)) {
            echo json_encode(array(
                "1" => "exito",
                "disponible" => false,
                "cuentas" => array(),
                "mensaje" => "La vinculacion de cuentas Telar quedara disponible al aplicar la actualizacion del modulo de laboratorio."
            ));
            exit;
        }

        $usuarioVinculado = 0;
        if ($mecanico_actual > 0) {
            $stmtActual = $mysqli->prepare(
                "SELECT cod_usuarioFK FROM mecanico_dental WHERE cod_mecanico_dental = ? LIMIT 1"
            );
            if ($stmtActual) {
                $stmtActual->bind_param('i', $mecanico_actual);
                if ($stmtActual->execute()) {
                    $resultActual = $stmtActual->get_result();
                    $rowActual = $resultActual ? $resultActual->fetch_assoc() : null;
                    $usuarioVinculado = $rowActual && !empty($rowActual['cod_usuarioFK'])
                        ? (int)$rowActual['cod_usuarioFK']
                        : 0;
                }
                $stmtActual->close();
            }
        }

        $termino = '%' . trim((string)$busqueda) . '%';
        $sql = "SELECT u.cod_usuario, p.nombre_persona, u.login, u.estado,
                       CASE WHEN LOWER(TRIM(u.estado)) = 'activo' THEN 1 ELSE 0 END AS cuenta_activa
                FROM usuario u
                INNER JOIN persona p ON p.cod_persona = u.cod_usuario
                WHERE (
                    LOWER(TRIM(u.estado)) = 'activo'
                    OR u.cod_usuario = ?
                )
                AND NOT EXISTS (
                    SELECT 1
                    FROM mecanico_dental md_vinculado
                    WHERE md_vinculado.cod_usuarioFK = u.cod_usuario
                      AND md_vinculado.cod_mecanico_dental <> ?
                )
                AND (
                    ? = '' OR p.nombre_persona LIKE ? OR u.login LIKE ? OR CAST(u.cod_usuario AS CHAR) LIKE ?
                    OR u.cod_usuario = ?
                )
                ORDER BY cuenta_activa DESC, p.nombre_persona ASC
                LIMIT 100";
        $busquedaVacia = trim((string)$busqueda);
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            mecanicoDentalResponderError('No se pudo preparar la busqueda de cuentas Telar.', 'error_busqueda_usuarios');
        }
        $stmt->bind_param(
            'iissssi',
            $usuarioVinculado,
            $mecanico_actual,
            $busquedaVacia,
            $termino,
            $termino,
            $termino,
            $usuario_actual
        );
        if (!$stmt->execute()) {
            mecanicoDentalResponderError('No se pudieron buscar las cuentas Telar activas.', 'error_busqueda_usuarios');
        }

        $result = $stmt->get_result();
        $cuentas = array();
        while ($row = $result->fetch_assoc()) {
            $cuentas[] = array(
                'cod_usuario' => (int)$row['cod_usuario'],
                'nombre_persona' => mecanicoDentalTextoUtf8($row['nombre_persona']),
                'login' => mecanicoDentalTextoUtf8($row['login']),
                'activa' => (int)$row['cuenta_activa'] === 1
            );
        }
        $stmt->close();

        echo json_encode(array(
            "1" => "exito",
            "disponible" => true,
            "cuentas" => $cuentas,
            "mensaje" => count($cuentas) > 0
                ? "Solo se muestran cuentas Telar activas y sin otro mecanico vinculado."
                : "No se encontraron cuentas Telar disponibles con ese criterio."
        ));
        exit;
    }

    function obtenerVistaOpcionesMecanicoDental($filtros, $limite = 0) {
        $registros = obtenerMecanicosDentales($filtros, $limite);

        $pagina= "";
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            $styleName=CargarStyleTable($styleName);
            $pagina.="<option value='".$value['cod_mecanico_dental']."'>".$value['nombre_persona']."</option>";
        }
        echo json_encode(array("1" => "exito", "2" => $pagina));
        exit;
    }

    function obtenerVistaMecanicoDental($filtros, $limite = 0) {
        $registros = obtenerMecanicosDentales($filtros, $limite);

        $pagina= "";
        $styleName="tableRegistroSearch";
        foreach($registros as $value) {
            $styleName=CargarStyleTable($styleName);
            $nombreCuenta = isset($value['nombre_usuario_telar']) ? trim($value['nombre_usuario_telar']) : '';
            $loginCuenta = isset($value['login_usuario_telar']) ? trim($value['login_usuario_telar']) : '';
            $estadoCuenta = isset($value['estado_usuario_telar']) ? strtolower(trim($value['estado_usuario_telar'])) : '';
            $textoCuenta = 'Sin vincular';
            if ($nombreCuenta !== '') {
                $textoCuenta = $nombreCuenta;
                if ($loginCuenta !== '') {
                    $textoCuenta .= ' (' . $loginCuenta . ')';
                }
                if ($estadoCuenta !== '' && $estadoCuenta !== 'activo') {
                    $textoCuenta .= ' - Inactiva';
                }
            }
            $textoCuentaHtml = htmlspecialchars($textoCuenta, ENT_QUOTES, 'UTF-8');
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='ObtenerdatosMecanicoDental(this)'>
            <td id='td_id' style='width: 10%;'>".$value['cod_mecanico_dental']."</td>
            <td id='td_datos_1'style='width:30%' class='tdRegistroSearch' >".$value['nombre_persona']."</td>
            <td id='td_datos_2'style='width:10%' class='tdRegistroSearch' >".ucfirst($value['estado'])."</td>
            <td id='td_datos_3'style='width:15%' class='tdRegistroSearch' >".$value['telefono']."</td>
            <td id='td_datos_7' style='width:35%' class='tdRegistroSearch'>".$textoCuentaHtml."</td>
            <td id='td_datos_4'style='display: none;' class='tdRegistroSearch' >".$value['telefono_referencia']."</td>
            <td id='td_datos_5'style='display: none;' class='tdRegistroSearch' >".$value['direccion']."</td>
            <td id='td_datos_6'style='display: none;' class='tdRegistroSearch' >".$value['cod_personaFK']."</td>
            <td id='td_datos_8' style='display:none' class='tdRegistroSearch'>".$value['cod_usuario_telar']."</td>
            <td id='td_datos_9' style='display:none' class='tdRegistroSearch'>".htmlspecialchars($nombreCuenta, ENT_QUOTES, 'UTF-8')."</td>
            </tr>
            </table>";
        }
        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => count($registros)));
        exit;
    }

    function obtenerMecanicosDentales($filtros, $limite = 0) {
        $sqlFiltro = "";
        $mysqli = conectar_al_servidor();

        foreach ($filtros as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'estado':
                    $sqlFiltro .= "md.estado = '$value'";
                    break;
                case 'nombre':
                    $sqlFiltro .= 'p.nombre_persona like "%'.$value.'%"';
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "md.$key = $value";
                    } else {
                        $sqlFiltro .= "md.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite == 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $selectCuenta = "NULL AS cod_usuario_telar, '' AS nombre_usuario_telar,
                         '' AS login_usuario_telar, '' AS estado_usuario_telar";
        $joinCuenta = "";
        if (mecanicoDentalTieneColumnaUsuario($mysqli)) {
            $selectCuenta = "md.cod_usuarioFK AS cod_usuario_telar,
                             pu.nombre_persona AS nombre_usuario_telar,
                             u.login AS login_usuario_telar,
                             u.estado AS estado_usuario_telar";
            $joinCuenta = " LEFT JOIN usuario u ON u.cod_usuario = md.cod_usuarioFK
                            LEFT JOIN persona pu ON pu.cod_persona = u.cod_usuario ";
        }

        $sql = "SELECT md.*, p.*, $selectCuenta
         FROM mecanico_dental md
         JOIN persona p ON p.cod_persona = md.cod_personaFK
         $joinCuenta $sqlFiltro
         ORDER BY p.nombre_persona ASC $limite";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al buscar: " . $stmt->error, "sql" => $sql);
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

    function abmMecanicoDental(
        $nombre,
        $telefono_referencia,
        $direccion,
        $telefono,
        $estado,
        $cod_persona,
        $cod_mecanico_dental,
        $cod_usuarioFK = null,
        $actualizar_cuenta_telar = false
    ) {
        $mysqli = conectar_al_servidor();
        $esNuevo = empty($cod_mecanico_dental);
        $columnaCuentaDisponible = mecanicoDentalTieneColumnaUsuario($mysqli);

        if ($actualizar_cuenta_telar) {
            mecanicoDentalValidarCuentaTelar($mysqli, $cod_usuarioFK, (int)$cod_mecanico_dental);
        }

        $mysqli->begin_transaction();
        try {
            if ($esNuevo) {
                $sqlPersona = "INSERT INTO persona (nombre_persona, telefono_referencia, direccion, telefono)
                               VALUES (?, ?, ?, ?)";
                $stmtPersona = $mysqli->prepare($sqlPersona);
                if (!$stmtPersona) {
                    throw new Exception($mysqli->error, (int)$mysqli->errno);
                }
                $stmtPersona->bind_param('ssss', $nombre, $telefono_referencia, $direccion, $telefono);
            } else {
                $sqlPersona = "UPDATE persona
                               SET nombre_persona = ?, telefono_referencia = ?, direccion = ?, telefono = ?
                               WHERE cod_persona = ?";
                $stmtPersona = $mysqli->prepare($sqlPersona);
                if (!$stmtPersona) {
                    throw new Exception($mysqli->error, (int)$mysqli->errno);
                }
                $cod_persona = (int)$cod_persona;
                $stmtPersona->bind_param('ssssi', $nombre, $telefono_referencia, $direccion, $telefono, $cod_persona);
            }

            if (!$stmtPersona->execute()) {
                throw new Exception($stmtPersona->error, (int)$stmtPersona->errno);
            }
            if ($esNuevo) {
                $cod_persona = (int)$stmtPersona->insert_id;
            }
            $stmtPersona->close();

            if ($esNuevo) {
                if ($columnaCuentaDisponible && $actualizar_cuenta_telar) {
                    $sqlMecanico = "INSERT INTO mecanico_dental (cod_personaFK, estado, cod_usuarioFK)
                                    VALUES (?, ?, ?)";
                    $stmtMecanico = $mysqli->prepare($sqlMecanico);
                    if (!$stmtMecanico) {
                        throw new Exception($mysqli->error, (int)$mysqli->errno);
                    }
                    $stmtMecanico->bind_param('isi', $cod_persona, $estado, $cod_usuarioFK);
                } else {
                    $sqlMecanico = "INSERT INTO mecanico_dental (cod_personaFK, estado) VALUES (?, ?)";
                    $stmtMecanico = $mysqli->prepare($sqlMecanico);
                    if (!$stmtMecanico) {
                        throw new Exception($mysqli->error, (int)$mysqli->errno);
                    }
                    $stmtMecanico->bind_param('is', $cod_persona, $estado);
                }
            } else {
                $cod_mecanico_dental = (int)$cod_mecanico_dental;
                if ($columnaCuentaDisponible && $actualizar_cuenta_telar) {
                    $sqlMecanico = "UPDATE mecanico_dental
                                    SET cod_personaFK = ?, estado = ?, cod_usuarioFK = ?
                                    WHERE cod_mecanico_dental = ?";
                    $stmtMecanico = $mysqli->prepare($sqlMecanico);
                    if (!$stmtMecanico) {
                        throw new Exception($mysqli->error, (int)$mysqli->errno);
                    }
                    $stmtMecanico->bind_param('isii', $cod_persona, $estado, $cod_usuarioFK, $cod_mecanico_dental);
                } else {
                    $sqlMecanico = "UPDATE mecanico_dental
                                    SET cod_personaFK = ?, estado = ?
                                    WHERE cod_mecanico_dental = ?";
                    $stmtMecanico = $mysqli->prepare($sqlMecanico);
                    if (!$stmtMecanico) {
                        throw new Exception($mysqli->error, (int)$mysqli->errno);
                    }
                    $stmtMecanico->bind_param('isi', $cod_persona, $estado, $cod_mecanico_dental);
                }
            }

            if (!$stmtMecanico->execute()) {
                throw new Exception($stmtMecanico->error, (int)$stmtMecanico->errno);
            }
            if ($esNuevo) {
                $cod_mecanico_dental = (int)$stmtMecanico->insert_id;
            }
            $stmtMecanico->close();
            $mysqli->commit();
            return $cod_mecanico_dental;
        } catch (Exception $e) {
            $mysqli->rollback();
            if ((int)$e->getCode() === 1062) {
                mecanicoDentalResponderError(
                    'La cuenta Telar seleccionada ya esta vinculada a otro mecanico dental.',
                    'usuario_telar_ya_vinculado'
                );
            }
            mecanicoDentalResponderError('No se pudieron guardar los datos del mecanico dental.', 'error_guardado');
        }
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
    verificar($operacion);
?>

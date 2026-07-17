<?php
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("abmusuarios.php");

    date_default_timezone_set('America/Asuncion');

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

        asegurarEstructuraAsistencia();
        $horaActual= date('H:i:s');
	        switch ($funt) {
	            case "nuevo":
	                $cod_usuario= $user;
	                $hora_entrada= $horaActual;
	                $ip_publica = $_SERVER['REMOTE_ADDR'];

	                // Obtiene la hora esperada antes de abrir la transaccion. La
	                // insercion, el bloqueo por usuario y la auditoria se guardan
	                // luego como una sola unidad atomica.
	                $diasSemana= array("domingo", "lunes", "martes", "miercoles", "jueves", "viernes", "sabado");
	                $diaActual= $diasSemana[intval(date('w'))];

	                $horarios_usuario = buscarHorariosUsuario(null, $cod_usuario);
	                $hora_entrada_usuario = obtenerHoraEntradaUsuarioMasCercana($horarios_usuario, $diaActual, $hora_entrada);

	                // Compara si la hora_entrada_usuario es mayor que la hora_entrada por 10 min.
	                $llegada_tardia = ($hora_entrada_usuario && (strtotime($hora_entrada) - strtotime($hora_entrada_usuario)) > 660) ? 1 : 0;
	                $resultadoEntrada=registrarEntradaAsistenciaSegura($cod_usuario, $hora_entrada, $ip_publica, $llegada_tardia == 1);
	                if (!$resultadoEntrada['ok']) {
	                    echo json_encode(array(
	                        "1" => "red",
	                        "2" => $resultadoEntrada['mensaje'],
	                        "cod_asistencia" => isset($resultadoEntrada['cod_asistencia']) ? $resultadoEntrada['cod_asistencia'] : "",
	                        "reconciliar" => 1
	                    ));
	                    break;
	                }

	                echo json_encode(array(
	                    "1" => "exito",
	                    "cod_asistencia" => $resultadoEntrada['cod_asistencia'],
	                    "llegada_tardia" => $llegada_tardia,
	                    "justificacion_pendiente" => $llegada_tardia,
	                    'fecha' => $resultadoEntrada['fecha'],
	                    'hora_entrada' => $hora_entrada,
	                    'hora_entrada_usuario' => $hora_entrada_usuario
	                ));
	                break;
            case "editar";
                $cod_asistencia= $_POST['cod_asistencia'];
                $cod_asistencia = mb_convert_encoding((string)($cod_asistencia), 'ISO-8859-1', 'UTF-8');
                $cod_usuario= $user;
                $hora_entrada = isset($_POST['hora_entrada']) ? mb_convert_encoding((string)($_POST['hora_entrada']), 'ISO-8859-1', 'UTF-8') : null;
                $hora_salida= isset($_POST['hora_salida']) ? mb_convert_encoding((string)($_POST['hora_salida']), 'ISO-8859-1', 'UTF-8') : null;
                $justificacion = isset($_POST['justificacion']) ? mb_convert_encoding((string)($_POST['justificacion']), 'ISO-8859-1', 'UTF-8') : null;
                $asistencia_anterior = obtenerAsistenciaPorId($cod_asistencia);
                if (!asistenciaPerteneceAUsuario($asistencia_anterior, $user)) {
                    echo json_encode(array("1" => "red", "2" => "No se puede editar un registro de asistencia que no pertenece al usuario actual."));
                    break;
                }
                $cod_asistencia = abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, null, $justificacion, $cod_asistencia);
                registrarAuditoriaAsistencia($cod_asistencia, $cod_usuario, "edicion", $asistencia_anterior, obtenerAsistenciaPorId($cod_asistencia), $_SERVER['REMOTE_ADDR']);
                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia));
                break;
	            case 'registrarJustificacion':
	                $cod_asistencia= $_POST['cod_asistencia'];
	                $cod_asistencia = mb_convert_encoding((string)($cod_asistencia), 'ISO-8859-1', 'UTF-8');
	                $justificacion = mb_convert_encoding((string)($_POST['justificacion']), 'ISO-8859-1', 'UTF-8');
	                $resultadoJustificacion= registrarJustificacionAsistenciaSegura($cod_asistencia, $user, $justificacion, $_SERVER['REMOTE_ADDR']);
	                if (!$resultadoJustificacion['ok']) {
	                    echo json_encode(array("1" => "red", "2" => $resultadoJustificacion['mensaje']));
	                    break;
	                }
	                echo json_encode(array("1" => "exito", "cod_asistencia" => $cod_asistencia, "justificacion_pendiente" => 0));
	                break;
            case 'registrarRecordatorioEntrada':
                registrarRecordatorioEntrada($user, $navegador);
                break;
	            case "registrarSalida":
	                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
	                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
	                $fechaActual= date('Y-m-d');
	                registrarSalida($user, $horaActual, $cod_asistencia, $cod_local, $fechaActual);
	                break;
	            case "buscarEstadoUsuario":
	                obtenerEstadoActualAsistenciaUsuario($user);
	                break;
            case "buscar":
                $hora_entrada = isset($_POST['hora_entrada']) ? mb_convert_encoding((string)($_POST['hora_entrada']), 'ISO-8859-1', 'UTF-8') : null;
                $hora_salida= isset($_POST['hora_salida']) ? mb_convert_encoding((string)($_POST['hora_salida']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuario= isset($_POST['cod_usuario']) ? mb_convert_encoding((string)($_POST['cod_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $sinSalida= isset($_POST['sinSalida']) ? mb_convert_encoding((string)($_POST['sinSalida']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    'hora_entrada'=> $hora_entrada,
                    'hora_salida'=> $hora_salida,
                    'cod_asistencia'=> $cod_asistencia,
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'sinSalida'=> $sinSalida,
                    'cod_local'=> $cod_local,
                    'nombre_usuario'=> $nombre_usuario,
                );
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                if (!usuarioPuedeVerInformeAsistencia($user) && ($cod_usuario == null || (string)$cod_usuario !== (string)$user)) {
                    echo json_encode(array("1" => "NI"));
                    break;
                }

                $registros= obtenerAsistencias($filtros, $limite);
                echo json_encode(array("1" => "exito", "registros" => $registros), JSON_UNESCAPED_UNICODE);
                // imprimir error json encode
                //echo json_last_error_msg();
                break;
            case 'buscarVistaInforme':
                if (!usuarioPuedeVerInformeAsistencia($user)) {
                    echo json_encode(array("1" => "NI"));
                    break;
                }

                $cod_usuario= isset($_POST['cod_usuario']) ? mb_convert_encoding((string)($_POST['cod_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
                $estado_incidencia= isset($_POST['estado_incidencia']) ? mb_convert_encoding((string)($_POST['estado_incidencia']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'nombre_usuario'=> $nombre_usuario,
                    'cod_local'=> $cod_local,
                    'cod_asistencia'=> $cod_asistencia,
                    'estado_incidencia'=> $estado_incidencia,
                );
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaAsistencia($filtros, $limite);
                break;
            case 'buscarMasVistaInforme':
                if (!usuarioPuedeVerInformeAsistencia($user)) {
                    echo json_encode(array("1" => "NI"));
                    break;
                }

                $cod_usuario= isset($_POST['cod_usuario']) ? mb_convert_encoding((string)($_POST['cod_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_local= isset($_POST['cod_local']) ? mb_convert_encoding((string)($_POST['cod_local']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_asistencia= isset($_POST['cod_asistencia']) ? mb_convert_encoding((string)($_POST['cod_asistencia']), 'ISO-8859-1', 'UTF-8') : null;
                $estado_incidencia= isset($_POST['estado_incidencia']) ? mb_convert_encoding((string)($_POST['estado_incidencia']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    'cod_usuarioFK'=> $cod_usuario,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'nombre_usuario'=> $nombre_usuario,
                    'cod_local'=> $cod_local,
                    'cod_asistencia'=> $cod_asistencia,
                    'estado_incidencia'=> $estado_incidencia,
                );
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : "0";

                obtenerVistaAsistencia($filtros, $limite);
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function obtenerHoraEntradaUsuarioMasCercana($horarios_usuario, $diaActual, $hora_entrada) {
        $hora_entrada_usuario = null;
        $menor_diferencia = null;
        $hora_marcada = strtotime("2025-01-01 ".$hora_entrada);

        foreach ($horarios_usuario as $horario) {
            if (!isset($horario['dia']) || !isset($horario['hora_entrada']) || $horario['dia'] != $diaActual) {
                continue;
            }

            $hora_horario = strtotime("2025-01-01 ".$horario['hora_entrada']);
            
            $diferencia = abs($hora_marcada - $hora_horario);
            if ($menor_diferencia === null || $diferencia < $menor_diferencia) {
                $menor_diferencia = $diferencia;
                $hora_entrada_usuario = $horario['hora_entrada'];
            }
        }

        return $hora_entrada_usuario;
    }

    function asegurarEstructuraAsistencia() {
        $mysqli= conectar_al_servidor();
        $sql= "CREATE TABLE IF NOT EXISTS asistencia_auditoria (
            cod_asistencia_auditoria INT NOT NULL AUTO_INCREMENT,
            cod_asistenciaFK INT NOT NULL,
            cod_usuarioFK_accion INT NOT NULL,
            accion VARCHAR(40) NOT NULL,
            fecha_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ip_accion VARCHAR(60) DEFAULT NULL,
            datos_anteriores TEXT DEFAULT NULL,
            datos_nuevos TEXT DEFAULT NULL,
            PRIMARY KEY (cod_asistencia_auditoria),
            KEY idx_asistencia_auditoria_asistencia (cod_asistenciaFK),
            KEY idx_asistencia_auditoria_usuario (cod_usuarioFK_accion),
            KEY idx_asistencia_auditoria_fecha (fecha_hora)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        $mysqli->query($sql);

        $sql= "CREATE TABLE IF NOT EXISTS asistencia_recordatorio_entrada (
            cod_recordatorio INT NOT NULL AUTO_INCREMENT,
            cod_usuarioFK INT NOT NULL,
            fecha_jornada DATE DEFAULT NULL,
            fecha_hora_acceso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            jornada_programada TEXT DEFAULT NULL,
            entrada_registrada VARCHAR(2) DEFAULT 'NO',
            recordatorio_mostrado VARCHAR(2) DEFAULT 'NO',
            accion_elegida VARCHAR(40) DEFAULT '',
            fecha_hora_accion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            cod_usuario_sesion INT DEFAULT NULL,
            modulo VARCHAR(120) DEFAULT '',
            navegador VARCHAR(160) DEFAULT '',
            ip_accion VARCHAR(60) DEFAULT '',
            cod_asistenciaFK INT DEFAULT NULL,
            PRIMARY KEY (cod_recordatorio),
            KEY idx_recordatorio_usuario_fecha (cod_usuarioFK, fecha_jornada),
            KEY idx_recordatorio_accion (accion_elegida),
            KEY idx_recordatorio_fecha_accion (fecha_hora_accion)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        $mysqli->query($sql);
    }

	    function obtenerAsistenciaPorId($cod_asistencia) {
	        $registros= obtenerAsistencias(array('cod_asistencia' => $cod_asistencia), 1);
	        return count($registros) > 0 ? $registros[0] : null;
	    }

	    function asistenciaTieneFechaSalida($mysqli) {
	        static $tieneColumna = null;
	        if ($tieneColumna !== null) {
	            return $tieneColumna;
	        }
	        $sql= "SELECT 1
	            FROM information_schema.COLUMNS
	            WHERE TABLE_SCHEMA = DATABASE()
	                AND TABLE_NAME = 'asistencia'
	                AND COLUMN_NAME = 'fecha_salida'
	            LIMIT 1";
	        $result= $mysqli->query($sql);
	        $tieneColumna= $result && $result->num_rows > 0;
	        return $tieneColumna;
	    }

	    function obtenerAsistenciaPorIdConexion($mysqli, $cod_asistencia, $bloquear = false) {
	        $sql= "SELECT a.* FROM asistencia a WHERE a.cod_asistencia = ? LIMIT 1";
	        if ($bloquear) {
	            $sql .= " FOR UPDATE";
	        }
	        $stmt= $mysqli->prepare($sql);
	        if (!$stmt) {
	            return null;
	        }
	        $cod_asistencia= intval($cod_asistencia);
	        $stmt->bind_param('i', $cod_asistencia);
	        if (!$stmt->execute()) {
	            $stmt->close();
	            return null;
	        }
	        $result= $stmt->get_result();
	        $fila= $result ? $result->fetch_assoc() : null;
	        $stmt->close();
	        return $fila;
	    }

	    function bloquearUsuarioParaMarcacionAsistencia($mysqli, $cod_usuarioFK) {
	        $stmt= $mysqli->prepare("SELECT cod_usuario FROM usuario WHERE cod_usuario = ? LIMIT 1 FOR UPDATE");
	        if (!$stmt) {
	            return false;
	        }
	        $cod_usuarioFK= intval($cod_usuarioFK);
	        $stmt->bind_param('i', $cod_usuarioFK);
	        $ok= $stmt->execute();
	        $result= $ok ? $stmt->get_result() : null;
	        $existe= $result && $result->num_rows === 1;
	        $stmt->close();
	        return $existe;
	    }

	    function obtenerAsistenciaAbiertaUsuarioConexion($mysqli, $cod_usuarioFK, $bloquear = false) {
	        // La ventana de 36 horas permite recuperar una jornada nocturna tras
	        // medianoche sin reactivar indefinidamente marcaciones historicas.
	        $sql= "SELECT a.*
	            FROM asistencia a
	            WHERE a.cod_usuarioFK = ?
	                AND a.hora_salida IS NULL
	                AND a.fecha >= DATE_SUB(NOW(), INTERVAL 36 HOUR)
	            ORDER BY a.fecha DESC, a.cod_asistencia DESC
	            LIMIT 1";
	        if ($bloquear) {
	            $sql .= " FOR UPDATE";
	        }
	        $stmt= $mysqli->prepare($sql);
	        if (!$stmt) {
	            return null;
	        }
	        $cod_usuarioFK= intval($cod_usuarioFK);
	        $stmt->bind_param('i', $cod_usuarioFK);
	        if (!$stmt->execute()) {
	            $stmt->close();
	            return null;
	        }
	        $result= $stmt->get_result();
	        $fila= $result ? $result->fetch_assoc() : null;
	        $stmt->close();
	        return $fila;
	    }

	    function obtenerAsistenciaAbiertaUsuario($cod_usuarioFK) {
	        $mysqli= conectar_al_servidor();
	        $registro= obtenerAsistenciaAbiertaUsuarioConexion($mysqli, $cod_usuarioFK, false);
	        mysqli_close($mysqli);
	        return $registro;
	    }

    function asistenciaPerteneceAUsuario($asistencia, $cod_usuarioFK) {
        return $asistencia != null && (string)$asistencia['cod_usuarioFK'] === (string)$cod_usuarioFK;
    }

    function usuarioPuedeVerInformeAsistencia($cod_usuarioFK) {
        if (!function_exists('controldeaccesoacasas')) {
            return false;
        }

        return controldeaccesoacasas($cod_usuarioFK, "VERLISTADOASISTENCIA", " u.accion='SI' ") == 1;
    }

	    function registrarAuditoriaAsistenciaConexion($mysqli, $cod_asistencia, $cod_usuario_accion, $accion, $datos_anteriores, $datos_nuevos, $ip_accion) {
	        $sql= "INSERT INTO asistencia_auditoria
	            (cod_asistenciaFK, cod_usuarioFK_accion, accion, ip_accion, datos_anteriores, datos_nuevos)
	            VALUES (?, ?, ?, ?, ?, ?)";
	        $stmt= $mysqli->prepare($sql);
	        if (!$stmt) {
	            return false;
	        }

        $accion= mb_convert_encoding((string)$accion, 'ISO-8859-1', 'UTF-8');
        $ip_accion= mb_convert_encoding((string)$ip_accion, 'ISO-8859-1', 'UTF-8');
        $datos_anteriores= $datos_anteriores == null ? null : mb_convert_encoding(json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE), 'ISO-8859-1', 'UTF-8');
        $datos_nuevos= $datos_nuevos == null ? null : mb_convert_encoding(json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE), 'ISO-8859-1', 'UTF-8');

        $stmt->bind_param('iissss', $cod_asistencia, $cod_usuario_accion, $accion, $ip_accion, $datos_anteriores, $datos_nuevos);
	        $ok= $stmt->execute();
	        $stmt->close();
	        return $ok;
	    }

	    function registrarAuditoriaAsistencia($cod_asistencia, $cod_usuario_accion, $accion, $datos_anteriores, $datos_nuevos, $ip_accion) {
	        $mysqli= conectar_al_servidor();
	        $ok= registrarAuditoriaAsistenciaConexion($mysqli, $cod_asistencia, $cod_usuario_accion, $accion, $datos_anteriores, $datos_nuevos, $ip_accion);
	        mysqli_close($mysqli);
	        return $ok;
	    }

	    function registrarEntradaAsistenciaSegura($cod_usuario, $hora_entrada, $ip_publica, $justificacionPendiente) {
	        $mysqli= conectar_al_servidor();
	        $mysqli->begin_transaction();
	        try {
	            if (!bloquearUsuarioParaMarcacionAsistencia($mysqli, $cod_usuario)) {
	                throw new Exception("No se encontro el usuario de la marcacion.");
	            }
	            $abierta= obtenerAsistenciaAbiertaUsuarioConexion($mysqli, $cod_usuario, true);
	            if ($abierta != null) {
	                $mysqli->rollback();
	                mysqli_close($mysqli);
	                return array(
	                    "ok" => false,
	                    "mensaje" => "Ya existe una entrada abierta. Se actualizara el estado de la jornada.",
	                    "cod_asistencia" => $abierta['cod_asistencia']
	                );
	            }

	            $stmt= $mysqli->prepare("INSERT INTO asistencia (cod_usuarioFK, hora_entrada, direccion_ip) VALUES (?, ?, ?)");
	            if (!$stmt) {
	                throw new Exception("No se pudo preparar la entrada.");
	            }
	            $cod_usuario= intval($cod_usuario);
	            $stmt->bind_param('iss', $cod_usuario, $hora_entrada, $ip_publica);
	            if (!$stmt->execute()) {
	                $stmt->close();
	                throw new Exception("No se pudo registrar la entrada.");
	            }
	            $cod_asistencia= intval($stmt->insert_id);
	            $stmt->close();
	            $registroNuevo= obtenerAsistenciaPorIdConexion($mysqli, $cod_asistencia, false);
	            $accionAuditoria= $justificacionPendiente ? "entrada_justificacion_pendiente" : "entrada";
	            if (!registrarAuditoriaAsistenciaConexion($mysqli, $cod_asistencia, $cod_usuario, $accionAuditoria, null, $registroNuevo, $ip_publica)) {
	                throw new Exception("No se pudo auditar la entrada.");
	            }
	            $mysqli->commit();
	            $fecha= isset($registroNuevo['fecha']) ? $registroNuevo['fecha'] : date('Y-m-d H:i:s');
	            mysqli_close($mysqli);
	            return array("ok" => true, "cod_asistencia" => $cod_asistencia, "fecha" => $fecha);
	        } catch (Exception $error) {
	            $mysqli->rollback();
	            mysqli_close($mysqli);
	            return array("ok" => false, "mensaje" => $error->getMessage());
	        }
	    }

	    function registrarJustificacionAsistenciaSegura($cod_asistencia, $cod_usuario, $justificacion, $ip_accion) {
	        $justificacion= trim((string)$justificacion);
	        if ($justificacion === '') {
	            return array("ok" => false, "mensaje" => "Debe ingresar una justificacion.");
	        }
	        $mysqli= conectar_al_servidor();
	        $mysqli->begin_transaction();
	        try {
	            if (!bloquearUsuarioParaMarcacionAsistencia($mysqli, $cod_usuario)) {
	                throw new Exception("No se encontro el usuario de la marcacion.");
	            }
	            $registroAnterior= obtenerAsistenciaPorIdConexion($mysqli, $cod_asistencia, true);
	            if (!asistenciaPerteneceAUsuario($registroAnterior, $cod_usuario)) {
	                throw new Exception("No se puede justificar un registro que no pertenece al usuario actual.");
	            }
	            $stmt= $mysqli->prepare("UPDATE asistencia SET justificacion = ? WHERE cod_asistencia = ? AND cod_usuarioFK = ?");
	            if (!$stmt) {
	                throw new Exception("No se pudo preparar la justificacion.");
	            }
	            $cod_asistencia= intval($cod_asistencia);
	            $cod_usuario= intval($cod_usuario);
	            $stmt->bind_param('sii', $justificacion, $cod_asistencia, $cod_usuario);
	            if (!$stmt->execute()) {
	                $stmt->close();
	                throw new Exception("No se pudo guardar la justificacion.");
	            }
	            $stmt->close();
	            $registroNuevo= obtenerAsistenciaPorIdConexion($mysqli, $cod_asistencia, false);
	            if (!registrarAuditoriaAsistenciaConexion($mysqli, $cod_asistencia, $cod_usuario, "justificacion", $registroAnterior, $registroNuevo, $ip_accion)) {
	                throw new Exception("No se pudo auditar la justificacion.");
	            }
	            $mysqli->commit();
	            mysqli_close($mysqli);
	            return array("ok" => true);
	        } catch (Exception $error) {
	            $mysqli->rollback();
	            mysqli_close($mysqli);
	            return array("ok" => false, "mensaje" => $error->getMessage());
	        }
	    }

	    function obtenerJustificacionPendienteUsuarioAsistencia($mysqli, $cod_usuario) {
	        $sql= "SELECT aa.cod_asistenciaFK, aa.accion
	            FROM asistencia_auditoria aa
	            INNER JOIN asistencia a ON a.cod_asistencia = aa.cod_asistenciaFK
	            WHERE a.cod_usuarioFK = ?
	                AND aa.accion IN ('entrada_justificacion_pendiente', 'salida_justificacion_pendiente')
	                AND NOT EXISTS (
	                    SELECT 1
	                    FROM asistencia_auditoria aa2
	                    WHERE aa2.cod_asistenciaFK = aa.cod_asistenciaFK
	                        AND aa2.accion = 'justificacion'
	                        AND aa2.cod_asistencia_auditoria > aa.cod_asistencia_auditoria
	                )
	            ORDER BY aa.cod_asistencia_auditoria DESC
	            LIMIT 1";
	        $stmt= $mysqli->prepare($sql);
	        if (!$stmt) {
	            return array("tipo" => "", "cod_asistencia" => "");
	        }
	        $cod_usuario= intval($cod_usuario);
	        $stmt->bind_param('i', $cod_usuario);
	        if (!$stmt->execute()) {
	            $stmt->close();
	            return array("tipo" => "", "cod_asistencia" => "");
	        }
	        $result= $stmt->get_result();
	        $fila= $result ? $result->fetch_assoc() : null;
	        $stmt->close();
	        if (!$fila) {
	            return array("tipo" => "", "cod_asistencia" => "");
	        }
	        return array(
	            "tipo" => $fila['accion'] === "entrada_justificacion_pendiente" ? "entrada_tardia" : "salida_ubicacion",
	            "cod_asistencia" => $fila['cod_asistenciaFK']
	        );
	    }

	    function normalizarRegistroAsistenciaRespuesta($registro) {
	        if (!is_array($registro)) {
	            return null;
	        }
	        $salida= array();
	        foreach ($registro as $clave => $valor) {
	            $salida[$clave]= mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
	        }
	        return $salida;
	    }

	    function obtenerEstadoActualAsistenciaUsuario($cod_usuario) {
	        $registros= obtenerAsistencias(array(
	            'cod_usuarioFK' => $cod_usuario,
	            'fecha_desde' => date('Y-m-d', strtotime('-1 day')),
	            'fecha_hasta' => date('Y-m-d')
	        ), 20);
	        $mysqli= conectar_al_servidor();
	        $abierta= obtenerAsistenciaAbiertaUsuarioConexion($mysqli, $cod_usuario, false);
	        $codigos= array();
	        foreach ($registros as $registro) {
	            $codigos[(string)$registro['cod_asistencia']]= true;
	        }
	        if ($abierta != null && !isset($codigos[(string)$abierta['cod_asistencia']])) {
	            $registros[]= normalizarRegistroAsistenciaRespuesta($abierta);
	        }

	        $pendiente= obtenerJustificacionPendienteUsuarioAsistencia($mysqli, $cod_usuario);
	        mysqli_close($mysqli);

	        echo json_encode(array(
	            "1" => "exito",
	            "registros" => $registros,
	            "registro_abierto" => normalizarRegistroAsistenciaRespuesta($abierta),
	            "justificacion_pendiente" => $pendiente['tipo'],
	            "cod_asistencia_justificacion" => $pendiente['cod_asistencia'],
	            "hora_servidor" => date('Y-m-d H:i:s')
	        ), JSON_UNESCAPED_UNICODE);
	    }

    function registrarRecordatorioEntrada($cod_usuario, $navegador) {
        $mysqli= conectar_al_servidor();
        $fecha_jornada= isset($_POST['fecha_jornada']) ? mb_convert_encoding((string)($_POST['fecha_jornada']), 'ISO-8859-1', 'UTF-8') : date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_jornada)) {
            $fecha_jornada= date('Y-m-d');
        }
        $fecha_hora_acceso= isset($_POST['fecha_hora_acceso']) ? mb_convert_encoding((string)($_POST['fecha_hora_acceso']), 'ISO-8859-1', 'UTF-8') : date('Y-m-d H:i:s');
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha_hora_acceso)) {
            $fecha_hora_acceso= date('Y-m-d H:i:s');
        }
        $jornada_programada= isset($_POST['jornada_programada']) ? mb_convert_encoding((string)($_POST['jornada_programada']), 'ISO-8859-1', 'UTF-8') : "";
        $entrada_registrada= isset($_POST['entrada_registrada']) && $_POST['entrada_registrada'] == "SI" ? "SI" : "NO";
        $recordatorio_mostrado= isset($_POST['recordatorio_mostrado']) && $_POST['recordatorio_mostrado'] == "SI" ? "SI" : "NO";
        $accion_elegida= isset($_POST['accion_elegida']) ? mb_convert_encoding((string)($_POST['accion_elegida']), 'ISO-8859-1', 'UTF-8') : "";
        $modulo= isset($_POST['modulo']) ? mb_convert_encoding((string)($_POST['modulo']), 'ISO-8859-1', 'UTF-8') : "";
        $cod_asistencia= isset($_POST['cod_asistencia']) && $_POST['cod_asistencia'] !== "" ? intval($_POST['cod_asistencia']) : 0;
        $ip_accion= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "";
        $cod_usuario_int= intval($cod_usuario);
        $cod_usuario_sesion= intval($cod_usuario);

        $sql= "INSERT INTO asistencia_recordatorio_entrada
            (cod_usuarioFK, fecha_jornada, fecha_hora_acceso, jornada_programada, entrada_registrada,
             recordatorio_mostrado, accion_elegida, fecha_hora_accion, cod_usuario_sesion, modulo, navegador, ip_accion, cod_asistenciaFK)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) {
            echo json_encode(array("1" => "error", "2" => "No se pudo preparar el registro del recordatorio."));
            return;
        }
        $tipos= "issssssisssi";
        $stmt->bind_param(
            $tipos,
            $cod_usuario_int,
            $fecha_jornada,
            $fecha_hora_acceso,
            $jornada_programada,
            $entrada_registrada,
            $recordatorio_mostrado,
            $accion_elegida,
            $cod_usuario_sesion,
            $modulo,
            $navegador,
            $ip_accion,
            $cod_asistencia
        );
        if (!$stmt->execute()) {
            echo json_encode(array("1" => "error", "2" => "No se pudo registrar el evento del recordatorio."));
            $stmt->close();
            return;
        }
        $stmt->close();
        echo json_encode(array("1" => "exito"));
    }

	    function registrarSalida($cod_usuarioFK, $hora_salida, $cod_asistencia, $cod_local, $fecha) {
	        $ip_salida = $_SERVER['REMOTE_ADDR'];
	        $mysqli= conectar_al_servidor();
	        $mysqli->begin_transaction();
	        try {
	            if (!bloquearUsuarioParaMarcacionAsistencia($mysqli, $cod_usuarioFK)) {
	                throw new Exception("No se encontro el usuario de la marcacion.");
	            }
	            $asistencia_actual= obtenerAsistenciaAbiertaUsuarioConexion($mysqli, $cod_usuarioFK, true);
	            if ($asistencia_actual == null) {
	                $mysqli->rollback();
	                mysqli_close($mysqli);
	                echo json_encode(array(
	                    "1" => "red",
	                    "2" => "La jornada ya no tiene una entrada abierta. Se actualizara el estado.",
	                    "reconciliar" => 1
	                ));
	                return;
	            }

	            $cod_asistencia= intval($asistencia_actual['cod_asistencia']);
	            $ip_entrada = isset($asistencia_actual['direccion_ip']) ? trim($asistencia_actual['direccion_ip']) : '';
	            $hora_entrada = isset($asistencia_actual['hora_entrada']) ? $asistencia_actual['hora_entrada'] : '';
	            $ip_valida = ($ip_entrada == '' || strcmp($ip_entrada, $ip_salida) == 0);
	            if (asistenciaTieneFechaSalida($mysqli)) {
	                $stmt= $mysqli->prepare("UPDATE asistencia
	                    SET hora_salida = ?, fecha_salida = NOW()
	                    WHERE cod_asistencia = ? AND cod_usuarioFK = ? AND hora_salida IS NULL");
	            } else {
	                $stmt= $mysqli->prepare("UPDATE asistencia
	                    SET hora_salida = ?
	                    WHERE cod_asistencia = ? AND cod_usuarioFK = ? AND hora_salida IS NULL");
	            }
	            if (!$stmt) {
	                throw new Exception("No se pudo preparar la salida.");
	            }
	            $cod_usuarioFK= intval($cod_usuarioFK);
	            $stmt->bind_param('sii', $hora_salida, $cod_asistencia, $cod_usuarioFK);
	            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
	                $stmt->close();
	                throw new Exception("La salida fue modificada desde otra sesion. Se actualizara el estado.");
	            }
	            $stmt->close();

	            $registroNuevo= obtenerAsistenciaPorIdConexion($mysqli, $cod_asistencia, false);
	            $accionAuditoria= $ip_valida ? "salida" : "salida_justificacion_pendiente";
	            if (!registrarAuditoriaAsistenciaConexion($mysqli, $cod_asistencia, $cod_usuarioFK, $accionAuditoria, $asistencia_actual, $registroNuevo, $ip_salida)) {
	                throw new Exception("No se pudo auditar la salida.");
	            }
	            $mysqli->commit();
	            mysqli_close($mysqli);

	            echo json_encode(array(
	                "1" => "exito",
	                "cod_asistencia" => $cod_asistencia,
	                "llegada_tardia" => 0,
	                'ip_valida' => ($ip_valida ? 1 : 0),
	                'justificacion_pendiente' => ($ip_valida ? 0 : 1),
	                'hora_entrada' => $hora_entrada,
	                'hora_salida' => $hora_salida,
	                'fecha_salida' => isset($registroNuevo['fecha_salida']) ? $registroNuevo['fecha_salida'] : date('Y-m-d H:i:s'),
	                'ip_entrada' => $ip_entrada,
	                'ip_salida' => $ip_salida
	            ));
	        } catch (Exception $error) {
	            $mysqli->rollback();
	            mysqli_close($mysqli);
	            echo json_encode(array("1" => "red", "2" => $error->getMessage(), "reconciliar" => 1));
	        }
	    }

    function obtenerVistaAsistencia($filtros, $limite= "0") {
        $rango= obtenerRangoInformeAsistencia($filtros);
        $filtros['fecha_desde']= $rango['desde'];
        $filtros['fecha_hasta']= $rango['hasta'];

        $registros= obtenerAsistencias($filtros);
        $informe= construirInformeGestionAsistencia($registros, $filtros, $rango);

        echo json_encode(array(
            "1" => "exito",
            "2" => $informe['html'],
            "3" => $registros,
            "4" => $informe['funcionarios_evaluados'],
            "5" => $informe['funcionarios_evaluados'],
            "6" => $informe['horas_trabajadas_texto'],
            "resumen" => $informe['resumen']
        ), JSON_UNESCAPED_UNICODE);
    }

    function normalizarFechaInformeAsistencia($fecha) {
        $fecha= trim((string)$fecha);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : "";
    }

    function obtenerRangoInformeAsistencia($filtros) {
        $desde= isset($filtros['fecha_desde']) ? normalizarFechaInformeAsistencia($filtros['fecha_desde']) : "";
        $hasta= isset($filtros['fecha_hasta']) ? normalizarFechaInformeAsistencia($filtros['fecha_hasta']) : "";

        if ($desde == "" && $hasta == "") {
            $desde= date('Y-m-d');
            $hasta= date('Y-m-d');
        } else if ($desde == "") {
            $desde= $hasta;
        } else if ($hasta == "") {
            $hasta= $desde;
        }

        if (strtotime($desde) > strtotime($hasta)) {
            $temporal= $desde;
            $desde= $hasta;
            $hasta= $temporal;
        }

        return array("desde" => $desde, "hasta" => $hasta);
    }

    function textoHtmlAsistencia($valor) {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }

    function normalizarUtf8Asistencia($valor) {
        return mb_convert_encoding((string)$valor, 'UTF-8', 'ISO-8859-1');
    }

    function formatearFechaVistaAsistencia($fecha) {
        if ($fecha == "") { return ""; }
        return date('d/m/Y', strtotime($fecha));
    }

    function obtenerNombreDiaAsistencia($fecha) {
        $dias= array(
            1 => array("key" => "lunes", "label" => "Lunes"),
            2 => array("key" => "martes", "label" => "Martes"),
            3 => array("key" => "miercoles", "label" => "Miércoles"),
            4 => array("key" => "jueves", "label" => "Jueves"),
            5 => array("key" => "viernes", "label" => "Viernes"),
            6 => array("key" => "sabado", "label" => "Sábado"),
            7 => array("key" => "domingo", "label" => "Domingo")
        );
        return $dias[intval(date('N', strtotime($fecha)))];
    }

    function obtenerNombreMesAsistencia($fecha) {
        $meses= array(
            1 => "Enero",
            2 => "Febrero",
            3 => "Marzo",
            4 => "Abril",
            5 => "Mayo",
            6 => "Junio",
            7 => "Julio",
            8 => "Agosto",
            9 => "Septiembre",
            10 => "Octubre",
            11 => "Noviembre",
            12 => "Diciembre"
        );
        return $meses[intval(date('n', strtotime($fecha)))];
    }

    function describirFechaPeriodoAsistencia($fecha) {
        $dia= obtenerNombreDiaAsistencia($fecha);
        $fechaVista= formatearFechaVistaAsistencia($fecha);
        $hoy= date('Y-m-d');
        $diferencia= intval((strtotime($fecha) - strtotime($hoy)) / 86400);

        if ($diferencia == 0) {
            return "Hoy ".$dia['label']." ".$fechaVista;
        }
        if ($diferencia == -1) {
            return "Ayer ".$dia['label']." ".$fechaVista;
        }
        if ($diferencia == 1) {
            return "Mañana ".$dia['label']." ".$fechaVista;
        }
        return $dia['label']." ".$fechaVista;
    }

    function rangoEsMesCompletoAsistencia($desde, $hasta) {
        return date('Y-m-01', strtotime($desde)) == $desde
            && date('Y-m-t', strtotime($desde)) == $hasta
            && date('Y-m', strtotime($desde)) == date('Y-m', strtotime($hasta));
    }

    function describirPeriodoResumenAsistencia($rango) {
        $desde= $rango['desde'];
        $hasta= $rango['hasta'];

        if ($desde == $hasta) {
            return array(
                "titulo" => "Resumen del día",
                "periodo" => "Período: ".describirFechaPeriodoAsistencia($desde),
                "detalle" => ""
            );
        }

        $detalle= "";
        if (rangoEsMesCompletoAsistencia($desde, $hasta)) {
            $detalle= obtenerNombreMesAsistencia($desde)." ".date('Y', strtotime($desde));
        }

        return array(
            "titulo" => "Resumen del período",
            "periodo" => "Período: ".formatearFechaVistaAsistencia($desde)." al ".formatearFechaVistaAsistencia($hasta),
            "detalle" => $detalle
        );
    }

    function formatearHoraVistaAsistencia($hora) {
        $hora= trim((string)$hora);
        if ($hora == "") { return ""; }
        return substr($hora, 0, 5);
    }

    function formatearHorasDecimalAsistencia($minutos) {
        $horas= round(intval($minutos) / 60, 2);
        $texto= number_format($horas, 2, ',', '.');
        $texto= rtrim(rtrim($texto, '0'), ',');
        if ($texto == "" || $texto == "-0") {
            $texto= "0";
        }
        return $texto." h";
    }

    function formatearDiferenciaHorasAsistencia($minutos) {
        $prefijo= intval($minutos) > 0 ? "+" : "";
        return $prefijo.formatearHorasDecimalAsistencia($minutos);
    }

    function formatearPorcentajeAsistencia($porcentaje) {
        if ($porcentaje === null) {
            return "Sin calendario";
        }
        $texto= number_format(round($porcentaje, 1), 1, ',', '.');
        $texto= rtrim(rtrim($texto, '0'), ',');
        return $texto."%";
    }

    function minutosEntreHorasAsistencia($hora_entrada, $hora_salida) {
        $hora_entrada= trim((string)$hora_entrada);
        $hora_salida= trim((string)$hora_salida);
        if ($hora_entrada == "" || $hora_salida == "") {
            return null;
        }

        $entrada= strtotime("2000-01-01 ".$hora_entrada);
        $salida= strtotime("2000-01-01 ".$hora_salida);
        if ($entrada === false || $salida === false) {
            return null;
        }

        if ($salida < $entrada) {
            return -1;
        }

        return intval(floor(($salida - $entrada) / 60));
    }

    function horarioAplicaFechaAsistencia($horario, $fecha) {
        $desde= isset($horario['vigente_desde']) ? trim((string)$horario['vigente_desde']) : "";
        $hasta= isset($horario['vigente_hasta']) ? trim((string)$horario['vigente_hasta']) : "";
        if ($desde != "" && $fecha < $desde) {
            return false;
        }
        if ($hasta != "" && $fecha > $hasta) {
            return false;
        }
        if (isset($horario['estado_horario']) && $horario['estado_horario'] == "inactivo" && $hasta == "") {
            return false;
        }
        return true;
    }

    function minutosEsperadosHorarioAsistencia($horario) {
        $tipoJornada= isset($horario['tipo_jornada']) ? $horario['tipo_jornada'] : "parcial";
        if ($tipoJornada == "no_laboral") {
            return 0;
        }

        if (isset($horario['horas_esperadas_minutos']) && intval($horario['horas_esperadas_minutos']) > 0) {
            return intval($horario['horas_esperadas_minutos']);
        }

        $minutos= minutosEntreHorasAsistencia($horario['hora_entrada'], $horario['hora_salida']);
        if ($minutos === null && $tipoJornada == "noche") {
            $entrada= strtotime("2000-01-01 ".$horario['hora_entrada']);
            $salida= strtotime("2000-01-02 ".$horario['hora_salida']);
            if ($entrada !== false && $salida !== false && $salida > $entrada) {
                $minutos= intval(floor(($salida - $entrada) / 60));
            }
        }
        if ($minutos === null || $minutos < 0) {
            return null;
        }

        $descansoInicio= isset($horario['descanso_inicio']) ? trim((string)$horario['descanso_inicio']) : "";
        $descansoFin= isset($horario['descanso_fin']) ? trim((string)$horario['descanso_fin']) : "";
        if ($descansoInicio != "" && $descansoFin != "") {
            $minutosDescanso= minutosEntreHorasAsistencia($descansoInicio, $descansoFin);
            if ($minutosDescanso !== null && $minutosDescanso > 0) {
                $minutos -= $minutosDescanso;
            }
        }

        return max(0, $minutos);
    }

    function agregarIncidenciaAsistencia(&$incidencias, $codigo, $texto) {
        if (!isset($incidencias[$codigo])) {
            $incidencias[$codigo]= $texto;
        }
    }

    function obtenerUsuariosConHorarioInformeAsistencia($filtros) {
        if (!empty($filtros['cod_asistencia'])) {
            return array();
        }

        $mysqli= conectar_al_servidor();
        if (function_exists('asegurarEstructuraHorarioUsuarioEsperado')) {
            asegurarEstructuraHorarioUsuarioEsperado($mysqli);
        }
        $condiciones= array("u.estado='Activo'", "hu.cod_localFK IS NOT NULL");
        $tipos= "";
        $parametros= array();
        $desde= isset($filtros['fecha_desde']) ? normalizarFechaInformeAsistencia($filtros['fecha_desde']) : "";
        $hasta= isset($filtros['fecha_hasta']) ? normalizarFechaInformeAsistencia($filtros['fecha_hasta']) : "";
        if ($desde == "") { $desde= date('Y-m-d'); }
        if ($hasta == "") { $hasta= $desde; }

        $condiciones[]= "(hu.vigente_desde IS NULL OR hu.vigente_desde <= ?)";
        $tipos .= "s";
        $parametros[]= $hasta;
        $condiciones[]= "(hu.vigente_hasta IS NULL OR hu.vigente_hasta >= ? OR IFNULL(hu.estado_horario,'activo')='activo')";
        $tipos .= "s";
        $parametros[]= $desde;
        $condiciones[]= "(IFNULL(hu.estado_horario,'activo')='activo' OR hu.vigente_hasta IS NOT NULL)";

        if (!empty($filtros['cod_usuarioFK'])) {
            $condiciones[]= "u.cod_usuario = ?";
            $tipos .= "i";
            $parametros[]= intval($filtros['cod_usuarioFK']);
        }

        if (!empty($filtros['cod_local'])) {
            $condiciones[]= "hu.cod_localFK = ?";
            $tipos .= "i";
            $parametros[]= intval($filtros['cod_local']);
        }

        if (!empty($filtros['nombre_usuario'])) {
            $condiciones[]= "IFNULL(p.nombre_persona, '') LIKE ?";
            $tipos .= "s";
            $parametros[]= "%".$filtros['nombre_usuario']."%";
        }

        $sql= "SELECT DISTINCT
                u.cod_usuario,
                IFNULL(p.nombre_persona, '') AS nombre_persona,
                IFNULL(u.url, '') AS url_usuario,
                u.cod_localFK
            FROM usuario u
            INNER JOIN horario_usuario hu ON hu.cod_usuarioFK = u.cod_usuario
            LEFT JOIN persona p ON p.cod_persona = u.cod_usuario
            WHERE ".implode(" AND ", $condiciones)."
            ORDER BY p.nombre_persona ASC, u.cod_usuario ASC";

        $stmt= $mysqli->prepare($sql);
        if (!$stmt) {
            return array();
        }
        enlazarParametrosAsistencia($stmt, $tipos, $parametros);
        if (!$stmt->execute()) {
            $stmt->close();
            return array();
        }

        $result= $stmt->get_result();
        $usuarios= array();
        while ($row= $result->fetch_assoc()) {
            $usuarios[$row['cod_usuario']]= array(
                "cod_usuario" => normalizarUtf8Asistencia($row['cod_usuario']),
                "nombre_persona" => normalizarUtf8Asistencia($row['nombre_persona']),
                "url_usuario" => normalizarUtf8Asistencia($row['url_usuario']),
                "cod_localFK" => normalizarUtf8Asistencia($row['cod_localFK'])
            );
        }

        $stmt->close();
        return $usuarios;
    }

    function obtenerUsuariosBaseInformeAsistencia($registros, $filtros) {
        $usuarios= array();
        foreach ($registros as $registro) {
            $cod_usuario= $registro['cod_usuarioFK'];
            $usuarios[$cod_usuario]= array(
                "cod_usuario" => $cod_usuario,
                "nombre_persona" => $registro['nombre_persona'],
                "url_usuario" => !empty($registro['url_usuario']) ? $registro['url_usuario'] : '/GoodVentaAsisCap/iconos/sinperfil.png',
                "cod_localFK" => isset($registro['cod_localFK']) ? $registro['cod_localFK'] : ""
            );
        }

        $usuariosHorario= obtenerUsuariosConHorarioInformeAsistencia($filtros);
        foreach ($usuariosHorario as $cod_usuario => $usuario) {
            if (!isset($usuarios[$cod_usuario])) {
                $usuarios[$cod_usuario]= $usuario;
            }
        }

        uasort($usuarios, function($a, $b) {
            return strcasecmp($a['nombre_persona'], $b['nombre_persona']);
        });

        return $usuarios;
    }

    function obtenerHorariosInformeAsistencia($codigos_usuario, $cod_local, $rango = null) {
        if (count($codigos_usuario) == 0) {
            return array();
        }

        $ids= array();
        foreach ($codigos_usuario as $cod_usuario) {
            $ids[]= intval($cod_usuario);
        }
        $ids= array_values(array_unique($ids));
        if (count($ids) == 0) {
            return array();
        }

        $mysqli= conectar_al_servidor();
        if (function_exists('asegurarEstructuraHorarioUsuarioEsperado')) {
            asegurarEstructuraHorarioUsuarioEsperado($mysqli);
        }

        $sqlLocal= "";
        if ($cod_local !== null && $cod_local !== "") {
            $sqlLocal= " AND cod_localFK = ".intval($cod_local)." ";
        }
        $sqlVigencia= "";
        if (is_array($rango) && !empty($rango['desde']) && !empty($rango['hasta'])) {
            $desde= normalizarFechaInformeAsistencia($rango['desde']);
            $hasta= normalizarFechaInformeAsistencia($rango['hasta']);
            if ($desde != "" && $hasta != "") {
                $sqlVigencia= " AND (vigente_desde IS NULL OR vigente_desde <= '".$hasta."')
                    AND (vigente_hasta IS NULL OR vigente_hasta >= '".$desde."' OR IFNULL(estado_horario,'activo')='activo')
                    AND (IFNULL(estado_horario,'activo')='activo' OR vigente_hasta IS NOT NULL) ";
            }
        }

        $sql= "SELECT
                cod_usuarioFK,
                dia_semana,
                cod_localFK,
                TIME_FORMAT(hora_entrada, '%H:%i') AS hora_entrada,
                TIME_FORMAT(hora_salida, '%H:%i') AS hora_salida,
                IFNULL(tipo_jornada, 'parcial') AS tipo_jornada,
                TIME_FORMAT(descanso_inicio, '%H:%i') AS descanso_inicio,
                TIME_FORMAT(descanso_fin, '%H:%i') AS descanso_fin,
                IFNULL(horas_esperadas_minutos, 0) AS horas_esperadas_minutos,
                IFNULL(jornada_equivalente, 0) AS jornada_equivalente,
                IFNULL(vigente_desde, '') AS vigente_desde,
                IFNULL(vigente_hasta, '') AS vigente_hasta,
                IFNULL(estado_horario, 'activo') AS estado_horario,
                IFNULL(observacion, '') AS observacion
            FROM horario_usuario
            WHERE cod_usuarioFK IN (".implode(",", $ids).")
            AND cod_localFK IS NOT NULL
            ".$sqlLocal."
            ".$sqlVigencia."
            ORDER BY FIELD(dia_semana,'lunes','martes','miercoles','jueves','viernes','sabado','domingo'), hora_entrada ASC, id ASC";

        $result= $mysqli->query($sql);
        $horarios= array();
        if (!$result) {
            return $horarios;
        }

        while ($row= $result->fetch_assoc()) {
            $cod_usuario= $row['cod_usuarioFK'];
            if (!isset($horarios[$cod_usuario])) {
                $horarios[$cod_usuario]= array();
            }
            $horarios[$cod_usuario][]= array(
                "dia" => normalizarUtf8Asistencia($row['dia_semana']),
                "cod_localFK" => normalizarUtf8Asistencia($row['cod_localFK']),
                "hora_entrada" => normalizarUtf8Asistencia($row['hora_entrada']),
                "hora_salida" => normalizarUtf8Asistencia($row['hora_salida']),
                "tipo_jornada" => normalizarUtf8Asistencia($row['tipo_jornada']),
                "descanso_inicio" => normalizarUtf8Asistencia($row['descanso_inicio']),
                "descanso_fin" => normalizarUtf8Asistencia($row['descanso_fin']),
                "horas_esperadas_minutos" => normalizarUtf8Asistencia($row['horas_esperadas_minutos']),
                "jornada_equivalente" => normalizarUtf8Asistencia($row['jornada_equivalente']),
                "vigente_desde" => normalizarUtf8Asistencia($row['vigente_desde']),
                "vigente_hasta" => normalizarUtf8Asistencia($row['vigente_hasta']),
                "estado_horario" => normalizarUtf8Asistencia($row['estado_horario']),
                "observacion" => normalizarUtf8Asistencia($row['observacion'])
            );
        }

        return $horarios;
    }

    function obtenerFeriadosInformeAsistencia($desde, $hasta) {
        $mysqli= conectar_al_servidor();
        $sql= "SELECT fecha, IFNULL(descripcion, '') AS descripcion, IFNULL(cod_localFK, '') AS cod_localFK
            FROM dias_feriados
            WHERE estado='activo'
            AND fecha >= ?
            AND fecha <= ?
            ORDER BY fecha ASC";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) {
            return array();
        }

        $stmt->bind_param('ss', $desde, $hasta);
        if (!$stmt->execute()) {
            $stmt->close();
            return array();
        }

        $result= $stmt->get_result();
        $feriados= array();
        while ($row= $result->fetch_assoc()) {
            $fecha= $row['fecha'];
            if (!isset($feriados[$fecha])) {
                $feriados[$fecha]= array("global" => null, "locales" => array());
            }

            $feriado= array(
                "fecha" => $fecha,
                "descripcion" => normalizarUtf8Asistencia($row['descripcion']),
                "cod_localFK" => normalizarUtf8Asistencia($row['cod_localFK'])
            );

            if ($feriado['cod_localFK'] == "") {
                $feriados[$fecha]["global"]= $feriado;
            } else {
                $feriados[$fecha]["locales"][$feriado['cod_localFK']]= $feriado;
            }
        }

        $stmt->close();
        return $feriados;
    }

    function buscarFeriadoDiaAsistencia($feriados, $fecha, $cod_local) {
        if (!isset($feriados[$fecha])) {
            return null;
        }
        if ($feriados[$fecha]['global'] != null) {
            return $feriados[$fecha]['global'];
        }
        if ($cod_local !== "" && isset($feriados[$fecha]['locales'][$cod_local])) {
            return $feriados[$fecha]['locales'][$cod_local];
        }
        return null;
    }

    function agruparRegistrosPorUsuarioDiaAsistencia($registros) {
        $agrupados= array();
        foreach ($registros as $registro) {
            $cod_usuario= $registro['cod_usuarioFK'];
            $fecha= substr($registro['fecha'], 0, 10);
            if (!isset($agrupados[$cod_usuario])) {
                $agrupados[$cod_usuario]= array();
            }
            if (!isset($agrupados[$cod_usuario][$fecha])) {
                $agrupados[$cod_usuario][$fecha]= array();
            }
            $agrupados[$cod_usuario][$fecha][]= $registro;
        }

        return $agrupados;
    }

    function calcularEsperadoDiaAsistencia($horarios, $fecha, $dia_semana, $feriados) {
        $minutos= 0;
        $tieneHorario= false;
        $horarioIncompleto= false;
        $horariosTexto= array();
        $feriadoAplicado= null;

        foreach ($horarios as $horario) {
            if (!horarioAplicaFechaAsistencia($horario, $fecha)) {
                continue;
            }
            if ($horario['dia'] != $dia_semana) {
                continue;
            }

            $tieneHorario= true;
            if (isset($horario['tipo_jornada']) && $horario['tipo_jornada'] == "no_laboral") {
                continue;
            }
            $feriadoHorario= buscarFeriadoDiaAsistencia($feriados, $fecha, $horario['cod_localFK']);
            if ($feriadoHorario != null) {
                $feriadoAplicado= $feriadoHorario;
                continue;
            }

            $textoHorario= formatearHoraVistaAsistencia($horario['hora_entrada'])." - ".formatearHoraVistaAsistencia($horario['hora_salida']);
            if (!empty($horario['descanso_inicio']) && !empty($horario['descanso_fin'])) {
                $textoHorario .= " (descanso ".formatearHoraVistaAsistencia($horario['descanso_inicio'])." - ".formatearHoraVistaAsistencia($horario['descanso_fin']).")";
            }
            $horariosTexto[]= $textoHorario;
            $minutosHorario= minutosEsperadosHorarioAsistencia($horario);

            if ($minutosHorario === null || $minutosHorario <= 0) {
                $horarioIncompleto= true;
                continue;
            }

            $minutos += $minutosHorario;
        }

        if (!$tieneHorario) {
            $feriadoGlobal= buscarFeriadoDiaAsistencia($feriados, $fecha, "");
            if ($feriadoGlobal != null) {
                return array(
                    "minutos" => 0,
                    "estado" => "feriado",
                    "label" => "Feriado",
                    "horarios" => array(),
                    "feriado" => $feriadoGlobal,
                    "incompleto" => false
                );
            }
        }

        if ($minutos > 0) {
            return array(
                "minutos" => $minutos,
                "estado" => "esperado",
                "label" => "Esperado",
                "horarios" => $horariosTexto,
                "feriado" => null,
                "incompleto" => false
            );
        }

        if ($feriadoAplicado != null) {
            return array(
                "minutos" => 0,
                "estado" => "feriado",
                "label" => "Feriado",
                "horarios" => $horariosTexto,
                "feriado" => $feriadoAplicado,
                "incompleto" => false
            );
        }

        if ($tieneHorario && $horarioIncompleto) {
            return array(
                "minutos" => 0,
                "estado" => "calendario_incompleto",
                "label" => "Sin calendario completo",
                "horarios" => $horariosTexto,
                "feriado" => null,
                "incompleto" => true
            );
        }

        return array(
            "minutos" => 0,
            "estado" => "no_laboral",
            "label" => "No laboral",
            "horarios" => array(),
            "feriado" => null,
            "incompleto" => false
        );
    }

    function calcularRealDiaAsistencia($registrosDia) {
        $minutos= 0;
        $incidencias= array();
        $entradas= array();
        $salidas= array();
        $conJustificacion= false;

        if (count($registrosDia) > 1) {
            agregarIncidenciaAsistencia($incidencias, "registros_multiples", "Marcaciones multiples");
        }

        foreach ($registrosDia as $registro) {
            $entrada= formatearHoraVistaAsistencia($registro['hora_entrada']);
            $salida= formatearHoraVistaAsistencia($registro['hora_salida']);
            if ($entrada != "") { $entradas[]= $entrada; }
            if ($salida != "") { $salidas[]= $salida; }

            if ($entrada == "") {
                agregarIncidenciaAsistencia($incidencias, "sin_entrada", "Sin entrada");
                continue;
            }

            if ($salida == "") {
                agregarIncidenciaAsistencia($incidencias, "sin_salida", "Sin salida");
                continue;
            }

	            $tieneFechaSalidaReal= isset($registro['fecha_salida']) && trim((string)$registro['fecha_salida']) !== '';
	            $minutosRegistro= $tieneFechaSalidaReal && isset($registro['diferencia_minutos'])
	                ? intval($registro['diferencia_minutos'])
	                : minutosEntreHorasAsistencia($entrada, $salida);
            if ($minutosRegistro === null) {
                agregarIncidenciaAsistencia($incidencias, "registro_revisar", "Registro a revisar");
                continue;
            }
            if ($minutosRegistro < 0) {
                agregarIncidenciaAsistencia($incidencias, "tiempo_negativo", "Tiempo negativo");
                continue;
            }
            if ($minutosRegistro == 0) {
                agregarIncidenciaAsistencia($incidencias, "duracion_cero", "Duracion cero");
                continue;
            }

            $minutos += $minutosRegistro;

            if (!empty($registro['justificacion'])) {
                $conJustificacion= true;
            }
        }

        if ($conJustificacion) {
            agregarIncidenciaAsistencia($incidencias, "justificacion", "Con justificacion");
        }

        sort($entradas);
        sort($salidas);

        return array(
            "minutos" => $minutos,
            "entrada" => count($entradas) > 0 ? $entradas[0] : "",
            "salida" => count($salidas) > 0 ? $salidas[count($salidas) - 1] : "",
            "incidencias" => $incidencias,
            "registros" => $registrosDia
        );
    }

    function determinarEstadoDiaAsistencia($esperado, $real, $tieneRegistros) {
        $incidencias= $real['incidencias'];
        $estado= "no_laboral";
        $label= "No laboral";

        if ($esperado['minutos'] > 0) {
            if (!$tieneRegistros) {
                $estado= "ausente";
                $label= "Ausente";
                agregarIncidenciaAsistencia($incidencias, "ausencia", "Ausencia");
            } else if (isset($incidencias['sin_salida'])) {
                $estado= "sin_salida";
                $label= "Sin salida";
            } else if (isset($incidencias['tiempo_negativo']) || isset($incidencias['duracion_cero']) || isset($incidencias['registro_revisar'])) {
                $estado= "revisar";
                $label= "A revisar";
            } else if ($real['minutos'] >= $esperado['minutos']) {
                $estado= "cumplido";
                $label= "Cumplido";
                if ($real['minutos'] > $esperado['minutos']) {
                    agregarIncidenciaAsistencia($incidencias, "horas_mayores", "Horas mayores a esperado");
                }
            } else if ($real['minutos'] > 0) {
                $estado= "parcial";
                $label= "Parcial";
                agregarIncidenciaAsistencia($incidencias, "jornada_parcial", "Jornada parcial");
            } else {
                $estado= "revisar";
                $label= "A revisar";
            }
        } else if ($tieneRegistros) {
            $estado= "no_esperado";
            $label= $esperado['estado'] == "feriado" ? "Feriado trabajado" : "No esperado";
            agregarIncidenciaAsistencia($incidencias, "dia_no_esperado", "Dia trabajado no esperado");
        } else if ($esperado['estado'] == "feriado") {
            $estado= "feriado";
            $label= "Feriado";
        } else if ($esperado['estado'] == "calendario_incompleto") {
            $estado= "calendario_incompleto";
            $label= "Sin calendario completo";
            agregarIncidenciaAsistencia($incidencias, "calendario_incompleto", "Calendario incompleto");
        }

        return array(
            "estado" => $estado,
            "label" => $label,
            "incidencias" => $incidencias
        );
    }

    function funcionarioCumpleFiltroEstadoAsistencia($datosFuncionario, $filtroEstado) {
        $filtroEstado= trim((string)$filtroEstado);
        if ($filtroEstado == "") {
            return true;
        }

        if ($filtroEstado == "incidencias") {
            return intval($datosFuncionario['incidencias_total']) > 0;
        }

        if ($filtroEstado == "sin_calendario") {
            return !empty($datosFuncionario['sin_calendario']);
        }

        foreach ($datosFuncionario['dias'] as $dia) {
            if ($dia['estado'] == $filtroEstado) {
                return true;
            }
            if (isset($dia['incidencias'][$filtroEstado])) {
                return true;
            }
            if ($filtroEstado == "revisar" && ($dia['estado'] == "revisar" || $dia['estado'] == "calendario_incompleto")) {
                return true;
            }
        }

        return false;
    }

    function construirDetalleTecnicoDiaAsistencia($registros) {
        if (count($registros) == 0) {
            return "";
        }

        $html= "<details class='asistencia-marcaciones-tecnicas'><summary>Marcaciones técnicas: ".count($registros)."</summary>";
        foreach ($registros as $registro) {
            $html .= "<div class='asistencia-marcacion-tecnica'>"
                ."<strong>#".textoHtmlAsistencia($registro['cod_asistencia'])."</strong>"
                ."<span>Entrada ".textoHtmlAsistencia(formatearHoraVistaAsistencia($registro['hora_entrada']))."</span>"
                ."<span>Salida ".textoHtmlAsistencia(formatearHoraVistaAsistencia($registro['hora_salida']) ?: "-")."</span>"
                ."<span>IP ".textoHtmlAsistencia($registro['direccion_ip'])."</span>";
            if (!empty($registro['justificacion'])) {
                $html .= "<em>".textoHtmlAsistencia($registro['justificacion'])."</em>";
            }
            $html .= "</div>";
        }
        $html .= "</details>";
        return $html;
    }

    function construirInformeGestionAsistencia($registros, $filtros, $rango) {
        $usuarios= obtenerUsuariosBaseInformeAsistencia($registros, $filtros);
        $horarios= obtenerHorariosInformeAsistencia(array_keys($usuarios), isset($filtros['cod_local']) ? $filtros['cod_local'] : "", $rango);
        $feriados= obtenerFeriadosInformeAsistencia($rango['desde'], $rango['hasta']);
        $registrosPorDia= agruparRegistrosPorUsuarioDiaAsistencia($registros);

        $resumen= array(
            "funcionarios_evaluados" => 0,
            "dias_esperados" => 0,
            "dias_trabajados" => 0,
            "horas_esperadas_minutos" => 0,
            "horas_trabajadas_minutos" => 0,
            "incidencias" => 0,
            "sin_salida" => 0
        );

        $htmlCards= "";
        foreach ($usuarios as $cod_usuario => $usuario) {
            $datosFuncionario= construirResumenFuncionarioAsistencia(
                $usuario,
                isset($horarios[$cod_usuario]) ? $horarios[$cod_usuario] : array(),
                isset($registrosPorDia[$cod_usuario]) ? $registrosPorDia[$cod_usuario] : array(),
                $feriados,
                $rango
            );

            if (!funcionarioCumpleFiltroEstadoAsistencia($datosFuncionario, isset($filtros['estado_incidencia']) ? $filtros['estado_incidencia'] : "")) {
                continue;
            }

            $resumen['funcionarios_evaluados']++;
            $resumen['dias_esperados'] += $datosFuncionario['dias_esperados'];
            $resumen['dias_trabajados'] += $datosFuncionario['dias_trabajados'];
            $resumen['horas_esperadas_minutos'] += $datosFuncionario['minutos_esperados'];
            $resumen['horas_trabajadas_minutos'] += $datosFuncionario['minutos_trabajados'];
            $resumen['incidencias'] += $datosFuncionario['incidencias_total'];
            $resumen['sin_salida'] += $datosFuncionario['sin_salida'];
            $htmlCards .= renderizarCardFuncionarioAsistencia($datosFuncionario);
        }

        if ($resumen['funcionarios_evaluados'] == 0) {
            $htmlCards= "<div class='asistencia-resumen-vacio'>No se encontraron funcionarios o marcaciones para el periodo.</div>";
        }

        $cumplimientoGeneral= $resumen['horas_esperadas_minutos'] > 0
            ? ($resumen['horas_trabajadas_minutos'] / $resumen['horas_esperadas_minutos']) * 100
            : null;

        $htmlResumen= renderizarResumenPeriodoAsistencia($resumen, $cumplimientoGeneral, $rango);

        return array(
            "html" => $htmlResumen.$htmlCards,
            "funcionarios_evaluados" => $resumen['funcionarios_evaluados'],
            "horas_trabajadas_texto" => formatearHorasDecimalAsistencia($resumen['horas_trabajadas_minutos']),
            "resumen" => $resumen
        );
    }

    function construirResumenFuncionarioAsistencia($usuario, $horarios, $registrosPorDia, $feriados, $rango) {
        $dias= array();
        $diasEsperados= 0;
        $diasTrabajados= 0;
        $minutosEsperados= 0;
        $minutosTrabajados= 0;
        $incidenciasTotal= 0;
        $sinSalida= 0;
        $marcacionesTecnicas= 0;

        $fecha= new DateTime($rango['desde']);
        $fechaFin= new DateTime($rango['hasta']);
        while ($fecha <= $fechaFin) {
            $fechaTexto= $fecha->format('Y-m-d');
            $diaInfo= obtenerNombreDiaAsistencia($fechaTexto);
            $registrosDia= isset($registrosPorDia[$fechaTexto]) ? $registrosPorDia[$fechaTexto] : array();
            $esperado= calcularEsperadoDiaAsistencia($horarios, $fechaTexto, $diaInfo['key'], $feriados);
            $real= calcularRealDiaAsistencia($registrosDia);
            $estado= determinarEstadoDiaAsistencia($esperado, $real, count($registrosDia) > 0);

            if ($esperado['minutos'] > 0) {
                $diasEsperados++;
                $minutosEsperados += $esperado['minutos'];
            }
            if (count($registrosDia) > 0) {
                $diasTrabajados++;
            }

            $minutosTrabajados += $real['minutos'];
            $marcacionesTecnicas += count($registrosDia);
            $incidenciasTotal += count($estado['incidencias']);
            if (isset($estado['incidencias']['sin_salida'])) {
                $sinSalida++;
            }

            $dias[]= array(
                "fecha" => $fechaTexto,
                "fecha_vista" => formatearFechaVistaAsistencia($fechaTexto),
                "dia" => $diaInfo['label'],
                "esperado" => $esperado,
                "real" => $real,
                "estado" => $estado['estado'],
                "estado_label" => $estado['label'],
                "incidencias" => $estado['incidencias']
            );

            $fecha->modify('+1 day');
        }

        $cumplimientoHoras= $minutosEsperados > 0 ? ($minutosTrabajados / $minutosEsperados) * 100 : null;
        $cumplimientoDias= $diasEsperados > 0 ? ($diasTrabajados / $diasEsperados) * 100 : null;

        return array(
            "usuario" => $usuario,
            "dias" => $dias,
            "dias_esperados" => $diasEsperados,
            "dias_trabajados" => $diasTrabajados,
            "minutos_esperados" => $minutosEsperados,
            "minutos_trabajados" => $minutosTrabajados,
            "cumplimiento_horas" => $cumplimientoHoras,
            "cumplimiento_dias" => $cumplimientoDias,
            "diferencia_minutos" => $minutosTrabajados - $minutosEsperados,
            "incidencias_total" => $incidenciasTotal,
            "sin_salida" => $sinSalida,
            "marcaciones_tecnicas" => $marcacionesTecnicas,
            "sin_calendario" => count($horarios) == 0
        );
    }

    function renderizarResumenPeriodoAsistencia($resumen, $cumplimientoGeneral, $rango) {
        $periodo= describirPeriodoResumenAsistencia($rango);
        $detallePeriodo= $periodo['detalle'] != "" ? "<em>".textoHtmlAsistencia($periodo['detalle'])."</em>" : "";
        return "
        <div class='asistencia-periodo-resumen'>
            <div class='asistencia-periodo-resumen__titulo'>
                <strong>".textoHtmlAsistencia($periodo['titulo'])."</strong>
                <span>".textoHtmlAsistencia($periodo['periodo'])."</span>
                ".$detallePeriodo."
            </div>
            <div class='asistencia-periodo-resumen__metricas'>
                <span><b>".textoHtmlAsistencia($resumen['funcionarios_evaluados'])."</b><small>Funcionarios</small></span>
                <span><b>".textoHtmlAsistencia($resumen['dias_esperados'])."</b><small>Días esperados</small></span>
                <span><b>".textoHtmlAsistencia($resumen['dias_trabajados'])."</b><small>Días trabajados</small></span>
                <span><b>".textoHtmlAsistencia(formatearHorasDecimalAsistencia($resumen['horas_esperadas_minutos']))."</b><small>Horas esperadas</small></span>
                <span><b>".textoHtmlAsistencia(formatearHorasDecimalAsistencia($resumen['horas_trabajadas_minutos']))."</b><small>Horas trabajadas</small></span>
                <span><b>".textoHtmlAsistencia(formatearPorcentajeAsistencia($cumplimientoGeneral))."</b><small>Cumplimiento</small></span>
                <span><b>".textoHtmlAsistencia($resumen['incidencias'])."</b><small>Incidencias</small></span>
                <span><b>".textoHtmlAsistencia($resumen['sin_salida'])."</b><small>Sin salida</small></span>
            </div>
        </div>";
    }

    function renderizarCardFuncionarioAsistencia($datos) {
        $usuario= $datos['usuario'];
        $cod_usuario_html= textoHtmlAsistencia($usuario['cod_usuario']);
        $nombre_html= textoHtmlAsistencia($usuario['nombre_persona']);
        $foto_html= textoHtmlAsistencia(!empty($usuario['url_usuario']) ? $usuario['url_usuario'] : '/GoodVentaAsisCap/iconos/sinperfil.png');
        $cumplimientoTexto= formatearPorcentajeAsistencia($datos['cumplimiento_horas']);
        $cumplimientoDiasTexto= formatearPorcentajeAsistencia($datos['cumplimiento_dias']);
        $barraCumplimiento= $datos['cumplimiento_horas'] === null ? 0 : min(100, max(0, round($datos['cumplimiento_horas'], 1)));
        $alertaCalendario= $datos['sin_calendario'] ? "<div class='asistencia-alerta-calendario'>Sin calendario esperado configurado</div>" : "";

        $calendario= "";
        $detalle= "";
        foreach ($datos['dias'] as $dia) {
            $calendario .= renderizarCeldaCalendarioAsistencia($dia);
            $detalle .= renderizarFilaDetalleDiaAsistencia($dia);
        }

        return "
        <div class='asistencia-empleado-card asistencia-gestion-card'>
            <button type='button' class='asistencia-empleado-card__resumen asistencia-gestion-card__resumen' onclick='toggleDetalleAsistenciaEmpleado(this)'>
                <img class='asistencia-empleado-card__foto' src='".$foto_html."' onerror=\"this.src='/GoodVentaAsisCap/iconos/sinperfil.png'\" alt=''>
                <span class='asistencia-empleado-card__info'>
                    <strong>".$nombre_html."</strong>
                    <small>Cod. ".$cod_usuario_html."</small>
                </span>
                <span class='asistencia-empleado-card__metricas asistencia-gestion-metricas'>
                    <span><b>".textoHtmlAsistencia($datos['dias_esperados'])."</b><small>Días esperados</small></span>
                    <span><b>".textoHtmlAsistencia($datos['dias_trabajados'])."</b><small>Días trabajados</small></span>
                    <span><b>".textoHtmlAsistencia(formatearHorasDecimalAsistencia($datos['minutos_esperados']))."</b><small>Horas esperadas</small></span>
                    <span><b>".textoHtmlAsistencia(formatearHorasDecimalAsistencia($datos['minutos_trabajados']))."</b><small>Horas trabajadas</small></span>
                    <span><b>".textoHtmlAsistencia($cumplimientoTexto)."</b><small>Cumplimiento</small></span>
                    <span><b>".textoHtmlAsistencia(formatearDiferenciaHorasAsistencia($datos['diferencia_minutos']))."</b><small>Diferencia</small></span>
                    <span><b>".textoHtmlAsistencia($datos['incidencias_total'])."</b><small>Incidencias</small></span>
                    <span><b>".textoHtmlAsistencia($datos['sin_salida'])."</b><small>Sin salida</small></span>
                </span>
            </button>
            <div class='asistencia-empleado-card__detalle asistencia-gestion-card__detalle'>
                ".$alertaCalendario."
                <div class='asistencia-evolucion'>
                    <div><strong>Cumplimiento por horas</strong><span>".textoHtmlAsistencia($cumplimientoTexto)."</span></div>
                    <div class='asistencia-evolucion__barra'><span style='width:".$barraCumplimiento."%'></span></div>
                    <small>Cumplimiento por días: ".textoHtmlAsistencia($cumplimientoDiasTexto)." · Marcaciones técnicas: ".textoHtmlAsistencia($datos['marcaciones_tecnicas'])."</small>
                </div>
                <div class='asistencia-card-subtitulo'>Calendario esperado vs real</div>
                <div class='asistencia-calendario'>".$calendario."</div>
                <div class='asistencia-card-subtitulo'>Detalle diario</div>
                <table class='asistencia-detalle-table asistencia-detalle-diario'>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Dia</th>
                            <th>Esperado</th>
                            <th>Real</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Incidencias</th>
                        </tr>
                    </thead>
                    <tbody>".$detalle."</tbody>
                </table>
            </div>
        </div>";
    }

    function renderizarCeldaCalendarioAsistencia($dia) {
        $esperado= $dia['esperado']['minutos'] > 0 ? formatearHorasDecimalAsistencia($dia['esperado']['minutos']) : "-";
        $real= $dia['real']['minutos'] > 0 ? formatearHorasDecimalAsistencia($dia['real']['minutos']) : "-";
        $titulo= $dia['fecha_vista']." - ".$dia['estado_label']." | Real ".$real." / Esperado ".$esperado;
        if ($dia['esperado']['feriado'] != null) {
            $titulo .= " | ".$dia['esperado']['feriado']['descripcion'];
        }

        return "<div class='asistencia-cal-dia asistencia-cal-dia--".textoHtmlAsistencia($dia['estado'])."' title='".textoHtmlAsistencia($titulo)."'>
            <strong>".textoHtmlAsistencia(date('d', strtotime($dia['fecha'])))."</strong>
            <small>".textoHtmlAsistencia($real)." / ".textoHtmlAsistencia($esperado)."</small>
            <em>".textoHtmlAsistencia($dia['estado_label'])."</em>
        </div>";
    }

    function renderizarFilaDetalleDiaAsistencia($dia) {
        $esperado= $dia['esperado']['minutos'] > 0 ? formatearHorasDecimalAsistencia($dia['esperado']['minutos']) : $dia['esperado']['label'];
        $real= $dia['real']['minutos'] > 0 ? formatearHorasDecimalAsistencia($dia['real']['minutos']) : "-";
        $incidencias= count($dia['incidencias']) > 0 ? implode(", ", array_values($dia['incidencias'])) : "-";
        $detalleTecnico= construirDetalleTecnicoDiaAsistencia($dia['real']['registros']);

        return "<tr>
            <td>".textoHtmlAsistencia($dia['fecha_vista'])."</td>
            <td>".textoHtmlAsistencia($dia['dia'])."</td>
            <td>".textoHtmlAsistencia($esperado)."</td>
            <td>".textoHtmlAsistencia($real)."</td>
            <td>".textoHtmlAsistencia($dia['real']['entrada'] ?: "-")."</td>
            <td>".textoHtmlAsistencia($dia['real']['salida'] ?: "-")."</td>
            <td><span class='asistencia-estado-badge asistencia-estado-badge--".textoHtmlAsistencia($dia['estado'])."'>".textoHtmlAsistencia($dia['estado_label'])."</span></td>
            <td>".textoHtmlAsistencia($incidencias).$detalleTecnico."</td>
        </tr>";
    }

    function normalizarLimiteAsistencia($limite) {
        $limite= trim((string)$limite);
        if ($limite == "" || $limite == "0") {
            return "";
        }

        if (preg_match('/^\d{1,6}(\s+OFFSET\s+\d{1,6})?$/i', $limite)) {
            return " LIMIT ".$limite;
        }

        $limite= intval($limite);
        return $limite > 0 ? " LIMIT ".$limite : "";
    }

    function enlazarParametrosAsistencia($stmt, $tipos, &$parametros) {
        if ($tipos == "") {
            return;
        }

        $refs= array();
        $refs[] = $tipos;
        foreach ($parametros as $key => $value) {
            $refs[] = &$parametros[$key];
        }

        call_user_func_array(array($stmt, 'bind_param'), $refs);
    }

    function obtenerAsistencias($filtros, $limite= 0) {
        $condiciones= array();
        $parametros= array();
        $tipos= "";

        foreach ($filtros as $key => $value) {
            if ($key != 'sinSalida' && ($value === null || $value === "")) {continue;}

            switch ($key) {
                case 'fecha_desde':
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value)) {
                        $condiciones[] = "DATE(a.fecha) >= ?";
                        $tipos .= "s";
                        $parametros[] = $value;
                    }
                    break;
                case 'fecha_hasta':
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value)) {
                        $condiciones[] = "DATE(a.fecha) <= ?";
                        $tipos .= "s";
                        $parametros[] = $value;
                    }
                    break;
                case 'sinSalida':
                    if ($value === true || $value === "true" || $value === "1" || $value === 1) {
                        $condiciones[] = "a.hora_salida IS NULL";
                    }
                    break;
                case 'cod_asistencia':
                    $condiciones[] = "a.cod_asistencia = ?";
                    $tipos .= "i";
                    $parametros[] = intval($value);
                    break;
                case 'cod_usuarioFK':
                    $condiciones[] = "a.cod_usuarioFK = ?";
                    $tipos .= "i";
                    $parametros[] = intval($value);
                    break;
                case 'cod_local':
                    $condiciones[] = "u.cod_localFK = ?";
                    $tipos .= "i";
                    $parametros[] = intval($value);
                    break;
                case 'acceso':
                    $condiciones[] = "u.acceso = ?";
                    $tipos .= "s";
                    $parametros[] = $value;
                    break;
                case 'nombre_usuario':
                    $condiciones[] = "IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona = a.cod_usuarioFK),'') LIKE ?";
                    $tipos .= "s";
                    $parametros[] = "%".$value."%";
                    break;
                case 'hora_entrada':
                    $condiciones[] = "a.hora_entrada = ?";
                    $tipos .= "s";
                    $parametros[] = $value;
                    break;
                case 'hora_salida':
                    $condiciones[] = "a.hora_salida = ?";
                    $tipos .= "s";
                    $parametros[] = $value;
                    break;
            }
        }    

        $sqlFiltro= count($condiciones) > 0 ? "WHERE ".implode(" AND ", $condiciones) : "";
        $limite= normalizarLimiteAsistencia($limite);

	        $mysqli=conectar_al_servidor();
	        $expresionDiferencia= asistenciaTieneFechaSalida($mysqli)
	            ? "IF(a.fecha_salida IS NOT NULL, TIMESTAMPDIFF(MINUTE, a.fecha, a.fecha_salida), IF(a.hora_salida IS NOT NULL, TIMESTAMPDIFF(MINUTE, a.hora_entrada, a.hora_salida), NULL))"
	            : "IF(a.hora_salida IS NOT NULL, TIMESTAMPDIFF(MINUTE, a.hora_entrada, a.hora_salida), NULL)";
	        $sql= "SELECT a.*, u.*, u.url AS url_usuario,
	            ".$expresionDiferencia." AS diferencia_minutos,
	            IFNULL((SELECT nombre_persona FROM persona WHERE cod_persona = cod_usuarioFK),'') AS nombre_persona
	            FROM asistencia a JOIN usuario u ON u.cod_usuario = a.cod_usuarioFK $sqlFiltro ORDER BY a.fecha DESC, a.cod_asistencia DESC $limite";

	        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $informacion =array("1" => "error", "mensaje" => "Error al preparar la consulta de asistencia: " . $mysqli->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }
        enlazarParametrosAsistencia($stmt, $tipos, $parametros);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        while ($row = $result->fetch_assoc()) {
            $reg= array();
            foreach ($row as $key => $value) {
                $reg[$key]= mb_convert_encoding((string)($value), 'UTF-8', 'ISO-8859-1');
            }
            $registros[] = $reg;
        }

        $stmt->close();
        return $registros;
    }

    function abmAsistencia($cod_usuario, $hora_entrada, $hora_salida, $ip_publica, $justificacion, $cod_asistencia) {
    	$mysqli=conectar_al_servidor();
        if ($cod_asistencia == null) {
            $sql = "INSERT INTO asistencia (cod_usuarioFK, hora_entrada, direccion_ip) 
                    VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                $informacion =array("1" => "error", "mensaje" => "Error al preparar el registro de asistencia: " . $mysqli->error, "sql" => $sql);
                echo json_encode($informacion);	
                exit;
            }
            $stmt->bind_param('iss', $cod_usuario, $hora_entrada, $ip_publica);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            if ($hora_entrada != null) {
                $atributos .= ", hora_entrada = ?";
                $ss .= "s";
                $parametros[] = $hora_entrada;
            }
            if ($hora_salida != null) {
                $atributos .= ", hora_salida = ?";
                $ss .= "s";
                $parametros[] = $hora_salida;
            }
            if ($justificacion != null) {
                $atributos .= ", justificacion = ?";
                $ss .= "s";
                $parametros[] = $justificacion;
            }
            if ($ip_publica != null) {
                $atributos .= ", direccion_ip = ?";
                $ss .= "s";
                $parametros[] = $ip_publica;
            }

            $atributos = substr($atributos, 2);
            if ($atributos == "") {
                return $cod_asistencia;
            }

            $parametros[] = $cod_asistencia;
            $ss .= "i";

            $sql = "UPDATE asistencia SET $atributos WHERE cod_asistencia = ?";
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                $informacion =array("1" => "error", "mensaje" => "Error al preparar el registro de asistencia: " . $mysqli->error, "sql" => $sql);
                echo json_encode($informacion);	
                exit;
            }

            // Convertir a referencias
            $refs = [];
            foreach ($parametros as $k => $v) {
                $refs[$k] = &$parametros[$k];
            }

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if ( ! $stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }

        if ($cod_asistencia == null) {
            $cod_asistencia = $stmt->insert_id;
        }

        $stmt->close();
        return $cod_asistencia;
    }

    // Validacion e identificacion de funcion
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $operacion= $_POST['accion'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificar($operacion);
    }
?>

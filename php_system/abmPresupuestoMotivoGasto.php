<?php
    include_once('quitarseparadormiles.php');
    include_once("buscar_nivel.php");
    require_once("conexion.php");
    include_once("verificar_navegador.php");
    include_once("classTable.php");
    include_once("subir_foto_base64.php");
    include_once("abmpagos.php");
    include_once("abmgasto.php");

    date_default_timezone_set('America/Asuncion');

    function verificarOperacionPresupuestoMotivoGasto($operacion) {
        $user=$_POST['useru'];
        $user = mb_convert_encoding((string)($user), 'ISO-8859-1', 'UTF-8');
        $pass=$_POST['passu'];	
        $pass = str_replace("=","+",$pass);
        $navegador=$_POST['navegador'];
        $navegador = mb_convert_encoding((string)($navegador), 'ISO-8859-1', 'UTF-8');
        $resp=verificar_navegador($user,$navegador,$pass);
        if($resp!="ok"){
            $informacion =array("1" => "UI");
            echo json_encode($informacion);	
            exit;
        }

        switch ($operacion) {
            case 'verficiarLimiteMotivo':
                $cod_motivo = $_POST['cod_motivo'];
                $cod_local = $_POST['cod_local'];
                
                // Obtiene las fechas del primer y ultimo dia del mes
                $fechaActual= new DateTime();
                $primerDiaMes= $fechaActual->format('Y-m-01');
                $ultimoDiaMes= $fechaActual->format('Y-m-t');

                $informacion2 = buscarGasto('', $primerDiaMes, $ultimoDiaMes, 'Activo', $cod_local, '', '', '','true', $cod_motivo, '', '', '');
                $informacion = obtenerPresupuestoMotivoGasto(array(
                    'cod_motivo_ingreso_egresoFK' => $cod_motivo,
                    'cod_localFK' => $cod_local
                ));

                if (count($informacion) > 0) {
                   echo json_encode(array(
					"1" => "exito",
					"2" => isset($informacion[0]["monto_limite"]) ? $informacion[0]["monto_limite"] : "",
					"3" => isset($informacion2["4"]) ? number_format(intval($informacion2["4"]), 0, ',', '.') : "0",
					"4" => isset($informacion2[9][0]["motivo"]) ? $informacion2[9][0]["motivo"] : ""
				));
                } else {
                   echo json_encode(array(
					"1" => "exito",
					"2" => 0,
					"3" => 0,
					"4" => isset($informacion[9][0]["motivo"]) ? $informacion[9][0]["motivo"] : ""
				));
                }

                break;
            case 'buscarVista':
                $cod_motivo_ingreso_egresoFK= mb_convert_encoding((string)($_POST['cod_motivo_ingreso_egresoFK']), 'ISO-8859-1', 'UTF-8');
                $registroPresupuesto= obtenerPresupuestoMotivoGasto(array(
                    "cod_motivo_ingreso_egresoFK" => $cod_motivo_ingreso_egresoFK
                ),0);
                
                $pagina= "";
                foreach ($registroPresupuesto as $key => $value) {
                    $monto_limite = empty($value['monto_limite']) ? "" : number_format($value['monto_limite'], 0, ',', '.');
                    $pagina .= "<table class='tableRegistroSearch' border='1' cellspacing='1' cellpadding='1'><tr>
                        <td id='td_id' style='display: none;'>".$value['cod_monto_limite_gasto_motivo']."</td>
                        <td id='td_datos_1' style='width:60%;'>".$value['nombre_local']."</td>
                        <td id='td_datos_2' style='width:40%;'><input value='".$monto_limite."' onkeyup='separadordemiles(this)' class='inputText'/></td>
                        <td id='td_datos_3' style='display: none;'>".$value['cod_localFK']."</td>
                        <td id='td_datos_4' style='display: none;'>".$value['cod_motivo_ingreso_egresoFK']."</td>
                    </tr></table>";
                }

                echo json_encode(array("1" => "exito", "2" => $pagina));
                break;
            case 'nuevo/editar':
                $cod_monto_limite_gasto_motivo = mb_convert_encoding((string)($_POST['cod_monto_limite_gasto_motivo']), 'ISO-8859-1', 'UTF-8');
                $monto_limite = mb_convert_encoding((string)($_POST['monto_limite']), 'ISO-8859-1', 'UTF-8');
                $monto_limite = quitarseparadormiles($monto_limite);
                $cod_localFK = mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8');
                $cod_motivo_ingreso_egresoFK = mb_convert_encoding((string)($_POST['cod_motivo_ingreso_egresoFK']), 'ISO-8859-1', 'UTF-8');

                $cod_monto_limite_gasto_motivo= abmPresupuestoMotivoGasto($cod_monto_limite_gasto_motivo, $monto_limite, $cod_motivo_ingreso_egresoFK, $cod_localFK, $user);
                echo json_encode(array("1" => "exito", "2" => $cod_monto_limite_gasto_motivo));
                break;
            default:
                echo json_encode(array("1" => "error", "2" => "Operacion $operacion no definida"));
                break;
        }
    }

    function obtenerPresupuestoMotivoGasto($filtro, $limite = 0) {
        // Prepara los filtros
        $limite = $limite > 0 ? "LIMIT $limite" : "";
        $sqlFiltro= "";
        foreach ($filtro as $key => $value) {
            if (empty($value)) {continue;}
            if (is_numeric($value)) {
                $sqlFiltro .= " AND $key = $value ";
            } else {
                $sqlFiltro .= " AND $key LIKE '%$value%' ";
            }
        }

        if (!empty($sqlFiltro)) {
            $sqlFiltro = "WHERE " . substr($sqlFiltro, 4);
        }
        
        $mysqli=conectar_al_servidor();
        $sql = "SELECT *,
         (SELECT Nombre from local WHERE cod_local=cod_localFK) as nombre_local
         FROM montos_limites_gasto_motivo $sqlFiltro $limite";
        
        $stmt = $mysqli->prepare($sql);

        if (!$stmt->execute()) {
            echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
            exit;
        }
        
        $result = $stmt->get_result();
        $data = array();
        
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                "cod_monto_limite_gasto_motivo" => mb_convert_encoding((string)($row['cod_monto_limite_gasto_motivo']), 'UTF-8', 'ISO-8859-1'),
                "monto_limite" => mb_convert_encoding((string)($row['monto_limite']), 'UTF-8', 'ISO-8859-1'),
                "nombre_local" => mb_convert_encoding((string)($row['nombre_local']), 'UTF-8', 'ISO-8859-1'),
                "cod_motivo_ingreso_egresoFK" => mb_convert_encoding((string)($row['cod_motivo_ingreso_egresoFK']), 'UTF-8', 'ISO-8859-1'),
                "cod_localFK" => mb_convert_encoding((string)($row['cod_localFK']), 'UTF-8', 'ISO-8859-1')
            );
        }
        
        $mysqli->close();
        return $data;
    }

    function abmPresupuestoMotivoGasto($cod_monto_limite_gasto_motivo, $monto_limite, $cod_motivo_ingreso_egresoFK, $cod_localFK, $user= NULL) {
        $mysqli=conectar_al_servidor();
        
        if (empty($cod_monto_limite_gasto_motivo)) {
            $sql = "INSERT INTO montos_limites_gasto_motivo (monto_limite, cod_motivo_ingreso_egresoFK, cod_localFK) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("dii", $monto_limite, $cod_motivo_ingreso_egresoFK, $cod_localFK);
        } else {
            $sql = "UPDATE montos_limites_gasto_motivo SET monto_limite=?, cod_motivo_ingreso_egresoFK=?, cod_localFK=?, fecha_edit=NOW(), cod_usuarioFK_edit=? WHERE cod_monto_limite_gasto_motivo=?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("iiiii", $monto_limite, $cod_motivo_ingreso_egresoFK, $cod_localFK, $user, $cod_monto_limite_gasto_motivo);
        }

        if (!$stmt->execute()) {
            echo trigger_error('The query execution failed; MySQL said ('.$stmt->errno.') '.$stmt->error, E_USER_ERROR);
            exit;
        }

        if (empty($cod_monto_limite_gasto_motivo)) {
            $cod_monto_limite_gasto_motivo = $stmt->insert_id;
        }

        $mysqli->close();
        return $cod_monto_limite_gasto_motivo;
    }
    
    if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
        $operacion = $_POST['funt'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificarOperacionPresupuestoMotivoGasto($operacion);
    }
?>
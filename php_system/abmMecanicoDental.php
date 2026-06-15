<?php
    require("conexion.php");
    require_once("solicitud_eliminado_helper.php");
    include("verificar_navegador.php");
    include("buscar_nivel.php");
    include("classTable.php");

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

                $cod_mecanico_dental = abmMecanicoDental($nombre,$telefono_referencia,$direccion,$telefono, $estado,$cod_persona,$cod_mecanico_dental);
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
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
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
            $pagina.="
            <table class='$styleName' border='1' cellspacing='1' cellpadding='5'>
            <tr id='tbSelecRegistro' onclick='ObtenerdatosMecanicoDental(this)'>
            <td id='td_id' style='width: 10%;'>".$value['cod_mecanico_dental']."</td>
            <td id='td_datos_1'style='width:35%' class='tdRegistroSearch' >".$value['nombre_persona']."</td>
            <td id='td_datos_2'style='width:10%' class='tdRegistroSearch' >".ucfirst($value['estado'])."</td>
            <td id='td_datos_3'style='width:10%' class='tdRegistroSearch' >".$value['telefono']."</td>
            <td id='td_datos_4'style='display: none;' class='tdRegistroSearch' >".$value['telefono_referencia']."</td>
            <td id='td_datos_5'style='display: none;' class='tdRegistroSearch' >".$value['direccion']."</td>
            <td id='td_datos_6'style='display: none;' class='tdRegistroSearch' >".$value['cod_personaFK']."</td>
            </tr>
            </table>";
        }
        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => count($registros)));
        exit;
    }

    function obtenerMecanicosDentales($filtros, $limite = 0) {
        $sqlFiltro = "";

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

        $sql = "SELECT md.*, p.*
         FROM mecanico_dental md JOIN persona p ON p.cod_persona = md.cod_personaFK $sqlFiltro $limite";

        $mysqli = conectar_al_servidor();
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

    function abmMecanicoDental($nombre,$telefono_referencia,$direccion,$telefono, $estado,$cod_persona,$cod_mecanico_dental) {
        $mysqli = conectar_al_servidor();


        if (empty($cod_mecanico_dental)) {
            // Primero se inserta los datos de la persona
            $sql = "INSERT INTO persona (nombre_persona, telefono_referencia, direccion, telefono)
                VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssss', $nombre,$telefono_referencia,$direccion,$telefono);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            if ($nombre != null) {$atributos .= ", nombre_persona = ?"; $ss .= "s"; $parametros[] = $nombre;}
            if ($telefono != null) {$atributos .= ", telefono = ?"; $ss .= "s"; $parametros[] = $telefono;}
            if ($telefono_referencia != null) {$atributos .= ", telefono_referencia = ?"; $ss .= "s"; $parametros[] = $telefono_referencia;}

            $atributos = substr($atributos, 2);
            $parametros[] = $cod_persona;
            $ss .= "i";

            $sql = "UPDATE persona SET $atributos WHERE cod_persona = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        // Obtiene el cod_persona si es que aun no se tiene
        $cod_persona = empty($cod_persona) ? $stmt->insert_id : $cod_persona;

        if (empty($cod_mecanico_dental)) {
            $sql = "INSERT INTO mecanico_dental (cod_personaFK, estado) 
                    VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('is', $cod_persona, $estado);
        } else {
            $atributos = "";
            $ss = "";
            $parametros = [];

            $parametros[] = $cod_persona;
            $ss .= "i";

            if ($estado != null) {$atributos .= ", estado = ?"; $ss .= "s"; $parametros[] = $estado;}

            $parametros[] = $cod_mecanico_dental;
            $ss .= "i";

            $sql = "UPDATE mecanico_dental SET cod_personaFK = ? $atributos WHERE cod_mecanico_dental = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        $cod_mecanico_dental =  empty($cod_mecanico_dental) ? $stmt->insert_id : $cod_mecanico_dental;

        $stmt->close();
        return $cod_mecanico_dental;
    }

    // Validacion e identificacion de funcion
    $operacion = $_POST['accion'];
    $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
    verificar($operacion);
?>

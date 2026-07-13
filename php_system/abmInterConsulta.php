<?php
    require_once("conexion.php");
    require_once("solicitud_eliminado_helper.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("subir_foto_base64.php");
    include_once("abmgasto.php");
    require_once("abmDictamen.php");
    require_once("interconsulta_seguimiento_paciente_helper.php");
    require_once("interconsulta_seguimiento_programado_helper.php");

    date_default_timezone_set('America/Asuncion');

    function normalizarLimiteListadoInterConsulta($limite, $maximo=60) {
        $limite = trim((string)$limite);
        if ($limite === "" || $limite === "0") {
            return (string)$maximo;
        }
        if (preg_match('/^\s*(\d+)(\s+OFFSET\s+\d+)?\s*$/i', $limite, $partes)) {
            $cantidad = min((int)$partes[1], (int)$maximo);
            $offset = isset($partes[2]) ? $partes[2] : "";
            return trim($cantidad.$offset);
        }
        return (string)$maximo;
    }

    function sanitizarAliasSqlInterConsulta($alias) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
    }

    function condicionCreditoPendienteHiloInterConsulta($aliasCredito) {
        $aliasCredito = sanitizarAliasSqlInterConsulta($aliasCredito);
        return "(
            ((IFNULL(".$aliasCredito.".Monto,0)-IFNULL(".$aliasCredito.".descuento,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=".$aliasCredito.".idcredito AND pg.tipo='Pago Cuota'),0)) > 0
            OR
            ((IFNULL(".$aliasCredito.".totalinteres,0)+IFNULL(".$aliasCredito.".deudaInteres,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=".$aliasCredito.".idcredito AND pg.tipo='Interes'),0)) > 0
        )";
    }

    function condicionCreditoActivoHiloInterConsulta($aliasCredito) {
        $aliasCredito = sanitizarAliasSqlInterConsulta($aliasCredito);
        return "UPPER(TRIM(IFNULL(".$aliasCredito.".Esado,''))) <> 'INACTIVO'";
    }

    function condicionCreditoVinculadoHiloInterConsulta($aliasCredito, $aliasInterConsulta= "ic") {
        $aliasCredito = sanitizarAliasSqlInterConsulta($aliasCredito);
        $aliasInterConsulta = sanitizarAliasSqlInterConsulta($aliasInterConsulta);
        return "(".$aliasCredito.".cod_venta = ".$aliasInterConsulta.".cod_ventaFK
            OR EXISTS (
                SELECT 1
                FROM interconsulta_paciente_venta ipv_credito
                WHERE ipv_credito.cod_interConsultaFK = ".$aliasInterConsulta.".cod_interConsulta
                    AND ipv_credito.estado = 'activo'
                    AND ipv_credito.cod_ventaFK = ".$aliasCredito.".cod_venta
                LIMIT 1
            ))";
    }

    function saldoPendienteCreditoHiloInterConsulta($aliasCredito) {
        $aliasCredito = sanitizarAliasSqlInterConsulta($aliasCredito);
        return "(
            GREATEST(((IFNULL(".$aliasCredito.".Monto,0)-IFNULL(".$aliasCredito.".descuento,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=".$aliasCredito.".idcredito AND pg.tipo='Pago Cuota'),0)),0)
            +
            GREATEST(((IFNULL(".$aliasCredito.".totalinteres,0)+IFNULL(".$aliasCredito.".deudaInteres,0))-IFNULL((SELECT SUM(pg.Monto) FROM pago pg WHERE pg.cod_creditoFK=".$aliasCredito.".idcredito AND pg.tipo='Interes'),0)),0)
        )";
    }

    function condicionAgendaVinculadaHiloInterConsulta($aliasAgenda, $aliasInterConsulta= "ic") {
        $aliasAgenda = sanitizarAliasSqlInterConsulta($aliasAgenda);
        $aliasInterConsulta = sanitizarAliasSqlInterConsulta($aliasInterConsulta);
        return "((
                ".$aliasAgenda.".cod_ventaFK IS NOT NULL
                AND (
                    ".$aliasAgenda.".cod_ventaFK = ".$aliasInterConsulta.".cod_ventaFK
                    OR EXISTS (
                        SELECT 1
                        FROM interconsulta_paciente_venta ipv_agenda
                        WHERE ipv_agenda.cod_interConsultaFK = ".$aliasInterConsulta.".cod_interConsulta
                            AND ipv_agenda.estado = 'activo'
                            AND ipv_agenda.cod_ventaFK = ".$aliasAgenda.".cod_ventaFK
                        LIMIT 1
                    )
                )
            )
            OR (
                ".$aliasAgenda.".id_paciente IS NOT NULL
                AND (
                    ".$aliasAgenda.".id_paciente = (SELECT vt_ag.cod_clienteFK FROM venta vt_ag WHERE vt_ag.cod_venta = ".$aliasInterConsulta.".cod_ventaFK LIMIT 1)
                    OR ".$aliasAgenda.".id_paciente = (SELECT ip_ag.cod_clienteFK_principal FROM interconsulta_paciente ip_ag WHERE ip_ag.cod_interConsultaFK = ".$aliasInterConsulta.".cod_interConsulta AND ip_ag.estado = 'activo' LIMIT 1)
                    OR EXISTS (
                        SELECT 1
                        FROM interconsulta_paciente_venta ipv_agpac
                        INNER JOIN venta vt_agpac ON vt_agpac.cod_venta = ipv_agpac.cod_ventaFK
                        WHERE ipv_agpac.cod_interConsultaFK = ".$aliasInterConsulta.".cod_interConsulta
                            AND ipv_agpac.estado = 'activo'
                            AND vt_agpac.cod_clienteFK = ".$aliasAgenda.".id_paciente
                        LIMIT 1
                    )
                )
            ))";
    }

    function normalizarTipoHiloInterConsulta($valor) {
        $texto = trim((string)$valor);
        $transliterado = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($transliterado === false || $transliterado === "") {
            $transliterado = @iconv('ISO-8859-1', 'ASCII//TRANSLIT//IGNORE', $texto);
        }
        if ($transliterado !== false && $transliterado !== "") {
            $texto = $transliterado;
        }
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
        return trim($texto, '_');
    }

    function obtenerCategoriasHilosInterConsulta() {
        return array(
            'pagos_egresos' => array(
                'nombre' => 'Pagos y Egresos',
                'tipos' => array('pagos', 'pago', 'compras', 'compra', 'egresos', 'egreso')
            ),
            'judiciales' => array(
                'nombre' => 'Judiciales',
                'tipos' => array('judicial', 'judiciales')
            ),
            'administrativo_clinico' => array(
                'nombre' => 'Administrativo y Clinico',
                'tipos' => array('administrativo', 'clinico', 'interno')
            )
        );
    }

    function obtenerCategoriaPrincipalHilo($tipoOriginal) {
        $tipo = normalizarTipoHiloInterConsulta($tipoOriginal);
        foreach (obtenerCategoriasHilosInterConsulta() as $categoria => $datos) {
            if ($tipo == $categoria) {
                return $categoria;
            }
            if (in_array($tipo, $datos['tipos'])) {
                return $categoria;
            }
        }
        return '';
    }

    function obtenerNombreCategoriaHilo($categoria) {
        $categorias = obtenerCategoriasHilosInterConsulta();
        return isset($categorias[$categoria]) ? $categorias[$categoria]['nombre'] : 'Hilos';
    }

    function condicionCategoriaHiloInterConsulta($categoria) {
        $categorias = obtenerCategoriasHilosInterConsulta();
        if (!isset($categorias[$categoria])) {
            return "";
        }

        $tipos = array();
        foreach ($categorias[$categoria]['tipos'] as $tipo) {
            $tipos[] = "'".addslashes($tipo)."'";
        }
        $condicion = "LOWER(TRIM(IFNULL(ic.tipo, ''))) IN (".implode(',', $tipos).")";

        // Compatibilidad: existen hilos historicos sin tipo. No se migra ni se
        // pisa el valor; solo se mantienen visibles en la categoria operativa
        // mas amplia hasta que el usuario decida su reclasificacion.
        if ($categoria == 'administrativo_clinico') {
            $condicion = "(".$condicion." OR TRIM(IFNULL(ic.tipo, '')) = '')";
        }

        return $condicion;
    }

    function condicionSubtipoHiloInterConsulta($tipoOriginal) {
        $tipo = normalizarTipoHiloInterConsulta($tipoOriginal);
        if ($tipo == "") {
            return "";
        }

        $equivalentes = array(
            'pagos' => array('pagos', 'pago'),
            'pago' => array('pagos', 'pago'),
            'compras' => array('compras', 'compra'),
            'compra' => array('compras', 'compra'),
            'egresos' => array('egresos', 'egreso'),
            'egreso' => array('egresos', 'egreso'),
            'judicial' => array('judicial', 'judiciales'),
            'judiciales' => array('judicial', 'judiciales'),
            'clinico' => array('clinico'),
            'administrativo' => array('administrativo'),
            'interno' => array('interno')
        );

        if (!isset($equivalentes[$tipo])) {
            return "ic.tipo LIKE '%".addslashes($tipoOriginal)."%'";
        }

        $valores = array();
        foreach ($equivalentes[$tipo] as $valor) {
            $valores[] = "'".addslashes($valor)."'";
        }
        return "LOWER(TRIM(IFNULL(ic.tipo, ''))) IN (".implode(',', $valores).")";
    }

    function verificarOperacionInterConsulta($funt) {
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

        $fechaActual= new DateTime();
        switch ($funt) {
            case 'buscarInterConsultaPorPaciente':
                $paciente= isset($_POST['paciente']) ? mb_convert_encoding((string)($_POST['paciente']), 'ISO-8859-1', 'UTF-8') : null;
                $filtros= array(
                    "paciente" => $paciente,
                    "cod_usuarioFK" => $user
                );
                
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 100;

                buscarVistaPacienteConInterConsulta($filtros, $limite);
                break;
            case 'buscarInterConsultas':
            case 'buscarInterConsultasEnriquecidos':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $mencion= isset($_POST['mencion']) ? mb_convert_encoding((string)($_POST['mencion']), 'ISO-8859-1', 'UTF-8') : false;
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_cliente= isset($_POST['nombre_cliente']) ? mb_convert_encoding((string)($_POST['nombre_cliente']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_responsable= isset($_POST['nombre_responsable']) ? mb_convert_encoding((string)($_POST['nombre_responsable']), 'ISO-8859-1', 'UTF-8') : null;
                $ocultar_inactivos= isset($_POST['ocultar_inactivos']) ? mb_convert_encoding((string)($_POST['ocultar_inactivos']), 'ISO-8859-1', 'UTF-8') : null;
                $usuario_vinculado= isset($_POST['usuario_vinculado']) ? mb_convert_encoding((string)($_POST['usuario_vinculado']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= isset($_POST['cod_localFK']) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $busqueda_global= isset($_POST['busqueda_global']) ? mb_convert_encoding((string)($_POST['busqueda_global']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $categoria_principal= isset($_POST['categoria_principal']) ? mb_convert_encoding((string)($_POST['categoria_principal']), 'ISO-8859-1', 'UTF-8') : 'pagos_egresos';

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'asunto'=> $asunto,
                    'estado'=> $estado,
                    'tipo'=> $tipo,
                    'categoria_principal'=> $categoria_principal,
                    'mencion'=> $mencion,
                    'cod_ventaFK'=> $cod_ventaFK,
                    'cod_usuarioFK'=> $cod_usuarioFK,
                    'cod_localFK'=> $cod_localFK,
                    'nombre_cliente'=> $nombre_cliente,
                    'nombre_responsable'=> $nombre_responsable,
                    'ocultar_inactivos'=> $ocultar_inactivos,
                    'usuario_vinculado'=> $usuario_vinculado,
                    'busqueda_global'=> $busqueda_global,
                    'fecha_desde'=> $fecha_desde,
                    'fecha_hasta'=> $fecha_hasta,
                    'fecha_limite' => $fechaActual->format('Y-m-d H:i:s')
                );

                $limiteSolicitado= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 30;
                $esConsultaAuxiliar= trim((string)$limiteSolicitado) === '0';
                $limite= $esConsultaAuxiliar ? '60' : normalizarLimiteListadoInterConsulta($limiteSolicitado, 30);

                if ($funt == 'buscarInterConsultasEnriquecidos') {
                    obtenerVistaInterConsulta($filtros, $limite, $esConsultaAuxiliar ? 60 : 30, $user);
                } else {
                    obtenerVistaInterConsultaBasica($filtros, $limite);
                }
                break;
            case 'crearHilosSeguimientoPacienteHistorico':
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;
                $resultado= seguimientoPacienteCrearHilosHistoricos($user, $limite);
                echo json_encode(array("1" => (!empty($resultado["ok"]) ? "exito" : "error"), "2" => $resultado));
                break;
            case 'previsualizarUnificacionSeguimientoPaciente':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $resultado= seguimientoPacientePrevisualizarUnificacionHilo($cod_interConsulta);
                echo json_encode(array(
                    "1" => (!empty($resultado["ok"]) ? "exito" : "error"),
                    "2" => seguimientoPacienteValorSalidaJson($resultado)
                ));
                break;
            case 'unificarSeguimientoPaciente':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $confirmar_conflicto= isset($_POST['confirmar_conflicto']) ? mb_convert_encoding((string)($_POST['confirmar_conflicto']), 'ISO-8859-1', 'UTF-8') : "0";
                $resultado= seguimientoPacienteEjecutarUnificacionHilo($cod_interConsulta, $user, $confirmar_conflicto == "1");
                echo json_encode(array(
                    "1" => (!empty($resultado["ok"]) ? "exito" : "error"),
                    "2" => seguimientoPacienteValorSalidaJson($resultado)
                ));
                break;
            case 'buscarInterConsultasYContenido':
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;

                $filtros= array(
                    "cod_ventaFK" => $cod_ventaFK,
                    "cod_interConsulta" => $cod_interConsulta,
                    "cod_usuarioFK" => $user
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario, array());
                break;
            case 'buscarDetalleDictamenInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_dictamen= isset($_POST['cod_dictamen']) ? mb_convert_encoding((string)($_POST['cod_dictamen']), 'ISO-8859-1', 'UTF-8') : null;
                obtenerDetalleDictamenDiferidoInterConsulta($cod_interConsulta, $cod_dictamen, $user, 10);
                break;
            case 'buscarResumenSeguimientoInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $registrosResumen= obtenerInterConsulta(array(
                    "cod_interConsulta" => $cod_interConsulta,
                    "cod_usuarioFK" => $user
                ), 1);
                if (count($registrosResumen) == 0) {
                    echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
                    break;
                }
                $registrosResumen[0]['resumen_seguimiento_cargado']= 1;
                echo json_encode(array("1" => "exito", "2" => $registrosResumen[0]));
                break;
            case 'buscarUsuariosMenciones':
                echo json_encode(array("1" => "exito", "2" => buscarUsuarios()));
                break;
            case 'buscarVentasSeguimientoPaciente':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $resultado= obtenerVentasSeguimientoPacienteInterConsulta($cod_interConsulta, $user);
                echo json_encode(array(
                    "1" => (!empty($resultado["ok"]) ? "exito" : "error"),
                    "2" => $resultado
                ));
                break;
            case 'buscarFlujoGastosInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $registrosInterc= obtenerInterConsultaDetalleRapido($cod_interConsulta, $user);
                if (count($registrosInterc) == 0) {
                    echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
                    break;
                }
                $paginaGastos= obtenerVistaFlujoGastosInterConsulta($cod_interConsulta);
                echo json_encode(array("1" => "exito", "2" => $paginaGastos));
                break;
            case 'nuevo/editar interconsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $asunto= isset($_POST['asunto']) ? mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8') : null;
                $observacion= isset($_POST['observacion']) ? mb_convert_encoding((string)($_POST['observacion']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_ventaFK= isset($_POST['cod_ventaFK']) ? mb_convert_encoding((string)($_POST['cod_ventaFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_localFK= (isset($_POST['cod_localFK']) && is_numeric($_POST['cod_localFK'])) ? mb_convert_encoding((string)($_POST['cod_localFK']), 'ISO-8859-1', 'UTF-8') : null;
                $monto_limite= (isset($_POST['monto_limite']) ? mb_convert_encoding((string)($_POST['monto_limite']), 'ISO-8859-1', 'UTF-8') : null);

                if (seguimientoPacienteEntero($cod_ventaFK) > 0) {
                    echo json_encode(array(
                        "1" => "error",
                        "2" => array(
                            "motivo" => "seguimiento_automatico_venta",
                            "mensaje" => "El seguimiento por paciente se genera automaticamente desde la venta. Use el hilo maestro existente."
                        )
                    ));
                    break;
                }

                $cod_interConsulta= abmInterConsulta($cod_interConsulta, $asunto, $observacion, $estado, $tipo, $cod_ventaFK, $user, $user, $cod_localFK, $monto_limite);
                echo json_encode(array("1" => "exito", "2" => $cod_interConsulta));
                break;
            case 'marcarMensajesLeido':
                $cod_interConsulta= mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8');
                $actualizadas= marcarMensajesLeidosInterConsulta($cod_interConsulta, $user);
                echo json_encode(array("1" => "exito", "2" => $actualizadas));
                break;
            case 'eliminarMencionMensaje':
                $cod_mencion= mb_convert_encoding((string)($_POST['cod_mencion']), 'ISO-8859-1', 'UTF-8');
                $cod_interConsulta= mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8');

                // Obtiene informacion extra de la mencion
                $registroMenc= obtenerMencion(array(
                    'cod_mencion' => $cod_mencion
                ), 1)[0];

                abmMencion($cod_mencion, null, null, 1, 'inactivo');
                
                // Se registra el cambio por auditoria
                $fechaActual= new DateTime();
                $fechaActual= $fechaActual->format('Y-m-d H:i:s');
                abmMensaje(null, 'Se quito la mencion de '.$registroMenc['nombre_persona'], $fechaActual, $cod_interConsulta, $user, NULL);

                echo json_encode(array("1" => "exito"));
                break;
            case 'buscarVistaMensajesSeleccionar':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $registrosMens= obtenerMensaje(array(
                    'cod_interConsultaFK'=> $cod_interConsulta,
                ), $limite);
                
                $pagina= "";
                foreach ($registrosMens as $regMens) {
                    $contenidoMensaje= $regMens['contenido'];
                    // Transforma las menciones a elementos
                    $usuarios= buscarUsuarios();
                    foreach ($usuarios as $valueUsu) {
                        $contenidoMensaje= str_replace(
                            '@{'.$valueUsu['cod_usuario'].'}', 
                            '<b class="menciones-mensaje" id="'.$valueUsu['cod_usuario'].'" title="Mencion a '.$valueUsu['nombre_persona'].'">@'.$valueUsu['nombre_persona'].'</b>', 
                            $contenidoMensaje
                        );
                    }
                    $contenidoMensaje = nl2br($contenidoMensaje, false);
                    $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding= "5"><tr onclick="obtenerDatosMensajeSeleccionar(this)">
                        <td id="td_id" style="display: none;">'.$regMens['cod_mensaje'].'</td>
                        <td id="td_datos_1" class="tdRegistroSearch" style="width: 10%;"><input type="checkbox"/></td>
                        <td id="td_datos_2" class="tdRegistroSearch" style="width: 65%;">'.$contenidoMensaje.'</td>
                        <td id="td_datos_3" class="tdRegistroSearch" style="width: 15%;">'.$regMens['fecha_creacion'].'</td>
                        <td id="td_datos_4" class="tdRegistroSearch" style="width: 10%;">'.$regMens['nombre_persona'].'</td>
                    </tr></table>';
                }

                echo json_encode(array("1" => "exito", "2" => $registrosMens, "3" => $pagina));
                break;
            case 'buscarMensaje':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_mensaje= isset($_POST['cod_mensaje']) ? mb_convert_encoding((string)($_POST['cod_mensaje']), 'ISO-8859-1', 'UTF-8') : null;
                $contenido= isset($_POST['contenido']) ? mb_convert_encoding((string)($_POST['contenido']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_creacion= isset($_POST['fecha_creacion']) ? mb_convert_encoding((string)($_POST['fecha_creacion']), 'ISO-8859-1', 'UTF-8') : "<= NOW()";

                $filtros= array(
                    'cod_interConsulta'=> $cod_interConsulta,
                    'cod_mensaje'=> $cod_mensaje,
                    'contenido'=> $contenido,
                    'fecha_creacion' => $fecha_creacion,
                );

                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;
                
                obtenerVistaMensaje($filtros, $limite);
                break;
            case 'obtenerContextoSeguimientoProgramado':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                if (!seguimientoProgramadoEstructuraDisponible()) {
                    echo json_encode(array("1" => "error", "2" => array("mensaje" => "La estructura de seguimientos programados no esta instalada.")));
                    break;
                }
                if (!seguimientoProgramadoPuedeAccederHilo($cod_interConsulta, $user, true)) {
                    echo json_encode(array("1" => "NI", "2" => array("mensaje" => "Usted no tiene acceso para programar seguimientos en este hilo.")));
                    break;
                }
                $puedeAdministrarPlantillas= seguimientoProgramadoPuedeAdministrarPlantillas($user);
                echo json_encode(array(
                    "1" => "exito",
                    "2" => array(
                        "plantillas" => seguimientoProgramadoObtenerPlantillas(false),
                        "responsables" => seguimientoProgramadoObtenerResponsables($cod_interConsulta, $user),
                        "puede_administrar_plantillas" => $puedeAdministrarPlantillas ? 1 : 0,
                        "plantillas_administracion" => $puedeAdministrarPlantillas ? seguimientoProgramadoObtenerPlantillas(true) : array(),
                        "alertas" => seguimientoProgramadoObtenerResumenAlertas($user)
                    )
                ));
                break;
            case 'guardarPlantillaSeguimientoProgramado':
                $datosPlantilla= array(
                    "id_plantilla" => isset($_POST['id_plantilla']) ? intval($_POST['id_plantilla']) : 0,
                    "nombre" => isset($_POST['nombre']) ? mb_convert_encoding((string)$_POST['nombre'], 'ISO-8859-1', 'UTF-8') : '',
                    "categoria" => isset($_POST['categoria']) ? mb_convert_encoding((string)$_POST['categoria'], 'ISO-8859-1', 'UTF-8') : '',
                    "mensaje" => isset($_POST['mensaje']) ? mb_convert_encoding((string)$_POST['mensaje'], 'ISO-8859-1', 'UTF-8') : '',
                    "orden" => isset($_POST['orden']) ? intval($_POST['orden']) : 0,
                    "estado" => isset($_POST['estado']) ? (string)$_POST['estado'] : 'activo'
                );
                $resultadoPlantilla= seguimientoProgramadoGuardarPlantilla($datosPlantilla, $user);
                echo json_encode(array("1" => !empty($resultadoPlantilla['ok']) ? "exito" : "error", "2" => seguimientoPacienteValorSalidaJson($resultadoPlantilla)));
                break;
            case 'cambiarEstadoPlantillaSeguimientoProgramado':
                $idPlantilla= isset($_POST['id_plantilla']) ? intval($_POST['id_plantilla']) : 0;
                $estadoPlantilla= isset($_POST['estado']) && $_POST['estado'] === 'activo' ? 'activo' : 'inactivo';
                $resultadoPlantilla= seguimientoProgramadoCambiarEstadoPlantilla($idPlantilla, $estadoPlantilla, $user);
                echo json_encode(array("1" => !empty($resultadoPlantilla['ok']) ? "exito" : "error", "2" => seguimientoPacienteValorSalidaJson($resultadoPlantilla)));
                break;
            case 'programarSeguimientoInterConsulta':
                $datosSeguimiento= array(
                    "cod_interConsulta" => isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0,
                    "id_plantilla" => isset($_POST['id_plantilla']) ? intval($_POST['id_plantilla']) : 0,
                    "motivo" => isset($_POST['motivo']) ? mb_convert_encoding((string)$_POST['motivo'], 'ISO-8859-1', 'UTF-8') : '',
                    "mensaje" => isset($_POST['mensaje']) ? mb_convert_encoding((string)$_POST['mensaje'], 'ISO-8859-1', 'UTF-8') : '',
                    "fecha_programada" => isset($_POST['fecha_programada']) ? (string)$_POST['fecha_programada'] : '',
                    "cod_responsable" => isset($_POST['cod_responsable']) ? intval($_POST['cod_responsable']) : intval($user),
                    "id_seguimiento_origen" => isset($_POST['id_seguimiento_origen']) ? intval($_POST['id_seguimiento_origen']) : 0,
                    "token_solicitud" => isset($_POST['token_solicitud']) ? (string)$_POST['token_solicitud'] : ''
                );
                $resultadoSeguimiento= seguimientoProgramadoCrear($datosSeguimiento, $user);
                echo json_encode(array("1" => !empty($resultadoSeguimiento['ok']) ? "exito" : "error", "2" => seguimientoPacienteValorSalidaJson($resultadoSeguimiento)));
                break;
            case 'completarSeguimientoInterConsulta':
                $idSeguimiento= isset($_POST['id_seguimiento']) ? intval($_POST['id_seguimiento']) : 0;
                $resultadoGestion= isset($_POST['resultado']) ? mb_convert_encoding((string)$_POST['resultado'], 'ISO-8859-1', 'UTF-8') : '';
                $resultadoSeguimiento= seguimientoProgramadoCompletar($idSeguimiento, $resultadoGestion, $user);
                echo json_encode(array("1" => !empty($resultadoSeguimiento['ok']) ? "exito" : "error", "2" => seguimientoPacienteValorSalidaJson($resultadoSeguimiento)));
                break;
            case 'buscarAlertasSeguimientoInterConsulta':
                echo json_encode(array("1" => "exito", "2" => seguimientoProgramadoObtenerResumenAlertas($user)));
                break;
            case 'buscarContextoMensajeInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $cod_mensaje_contexto= isset($_POST['cod_mensaje']) ? intval($_POST['cod_mensaje']) : 0;
                if (!seguimientoProgramadoPuedeAccederHilo($cod_interConsulta, $user)) {
                    echo json_encode(array("1" => "NI", "2" => array("mensaje" => "Usted no tiene acceso a este hilo.")));
                    break;
                }
                $mysqliContexto= conectar_al_servidor();
                $sqlContexto= "SELECT m.cod_mensaje,m.contenido,m.fecha_creacion,p.nombre_persona
                               FROM mensaje m
                               LEFT JOIN persona p ON p.cod_persona=m.cod_usuarioFK
                               WHERE m.cod_mensaje=? AND m.cod_interConsultaFK=?
                                 AND m.estado='activo' AND m.fecha_creacion<=NOW()
                               LIMIT 1";
                $stmtContexto= $mysqliContexto->prepare($sqlContexto);
                if (!$stmtContexto) {
                    $mysqliContexto->close();
                    echo json_encode(array("1" => "error", "2" => array("mensaje" => "No se pudo consultar el mensaje original.")));
                    break;
                }
                $stmtContexto->bind_param('ii', $cod_mensaje_contexto, $cod_interConsulta);
                $stmtContexto->execute();
                $mensajeContexto= $stmtContexto->get_result()->fetch_assoc();
                $stmtContexto->close();
                $mysqliContexto->close();
                if (!$mensajeContexto) {
                    echo json_encode(array("1" => "error", "2" => array("mensaje" => "El mensaje original ya no esta disponible.")));
                    break;
                }
                echo json_encode(array("1" => "exito", "2" => seguimientoProgramadoFilaUtf8($mensajeContexto)));
                break;
            case 'nuevo/editar mensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? mb_convert_encoding((string)($_POST['cod_mensaje']), 'ISO-8859-1', 'UTF-8') : null;
                $contenido= isset($_POST['contenido']) ? mb_convert_encoding((string)($_POST['contenido']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_creacion= $fechaActual->format('Y-m-d H:i:s');
                $cod_dictamenFK= isset($_POST['cod_dictamenFK']) && !empty($_POST['cod_dictamenFK']) ? mb_convert_encoding((string)($_POST['cod_dictamenFK']), 'ISO-8859-1', 'UTF-8') : NULL;
                $cod_mensaje_respuestaFK= isset($_POST['cod_mensaje_respuestaFK']) && intval($_POST['cod_mensaje_respuestaFK']) > 0 ? intval($_POST['cod_mensaje_respuestaFK']) : NULL;

                $codInterConsultaMensaje= intval($cod_interConsulta);
                if (!seguimientoProgramadoPuedeAccederHilo($codInterConsultaMensaje, $user, true)) {
                    echo json_encode(array("1" => "NI", "mensaje" => "Usted no tiene acceso para responder en este hilo o el hilo esta inactivo."));
                    break;
                }
                if (!empty($cod_mensaje)) {
                    $mysqliEdicionMensaje= conectar_al_servidor();
                    $stmtEdicionMensaje= $mysqliEdicionMensaje->prepare("SELECT cod_mensaje FROM mensaje WHERE cod_mensaje=? AND cod_interConsultaFK=? AND cod_usuarioFK=? AND estado='activo' LIMIT 1");
                    if (!$stmtEdicionMensaje) {
                        $mysqliEdicionMensaje->close();
                        echo json_encode(array("1" => "error", "mensaje" => "No se pudo validar el mensaje a editar."));
                        break;
                    }
                    $codMensajeEdicion= intval($cod_mensaje);
                    $codUsuarioEdicion= intval($user);
                    $stmtEdicionMensaje->bind_param('iii', $codMensajeEdicion, $codInterConsultaMensaje, $codUsuarioEdicion);
                    $stmtEdicionMensaje->execute();
                    $edicionPermitida= $stmtEdicionMensaje->get_result()->num_rows > 0;
                    $stmtEdicionMensaje->close();
                    $mysqliEdicionMensaje->close();
                    if (!$edicionPermitida) {
                        echo json_encode(array("1" => "NI", "mensaje" => "Solo puede editar sus propios mensajes dentro del hilo actual."));
                        break;
                    }
                }

                if ($cod_mensaje_respuestaFK !== NULL) {
                    if (!seguimientoProgramadoRespuestaCitadaDisponible()) {
                        echo json_encode(array("1" => "error", "mensaje" => "La respuesta citada todavia no esta disponible."));
                        break;
                    }
                    $mysqliRespuesta= conectar_al_servidor();
                    $sqlRespuesta= "SELECT cod_mensaje FROM mensaje
                                    WHERE cod_mensaje=? AND cod_interConsultaFK=?
                                      AND estado='activo' AND fecha_creacion<=NOW() LIMIT 1";
                    $stmtRespuesta= $mysqliRespuesta->prepare($sqlRespuesta);
                    if (!$stmtRespuesta) {
                        $mysqliRespuesta->close();
                        echo json_encode(array("1" => "error", "mensaje" => "No se pudo validar el mensaje citado."));
                        break;
                    }
                    $codInterConsultaRespuesta= intval($cod_interConsulta);
                    $stmtRespuesta->bind_param('ii', $cod_mensaje_respuestaFK, $codInterConsultaRespuesta);
                    $stmtRespuesta->execute();
                    $respuestaValida= $stmtRespuesta->get_result()->num_rows > 0;
                    $stmtRespuesta->close();
                    $mysqliRespuesta->close();
                    if (!$respuestaValida) {
                        echo json_encode(array("1" => "error", "mensaje" => "El mensaje citado no pertenece a este hilo o ya no esta disponible."));
                        break;
                    }
                }

                $cod_mensaje= abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user,$cod_dictamenFK, FALSE, $cod_mensaje_respuestaFK);
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'subirImagenMensaje':
                $cod_mensaje= isset($_POST['cod_mensaje']) ? mb_convert_encoding((string)($_POST['cod_mensaje']), 'ISO-8859-1', 'UTF-8') : null;
                $foto= isset($_POST['foto']) ? mb_convert_encoding((string)($_POST['foto']), 'ISO-8859-1', 'UTF-8') : null;
                $ext= isset($_POST['ext']) ? mb_convert_encoding((string)($_POST['ext']), 'ISO-8859-1', 'UTF-8') : null;
                subirImagenMensaje($cod_mensaje,$foto,$ext, 'url');
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'nuevo/editar mencion':
                $cod_mencion= isset($_POST['cod_mencion']) ? mb_convert_encoding((string)($_POST['cod_mencion']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_usuarioFK= isset($_POST['cod_usuarioFK']) ? mb_convert_encoding((string)($_POST['cod_usuarioFK']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_mensajeFK= isset($_POST['cod_mensajeFK']) ? mb_convert_encoding((string)($_POST['cod_mensajeFK']), 'ISO-8859-1', 'UTF-8') : null;
                $isLeido= isset($_POST['isLeido']) ? mb_convert_encoding((string)($_POST['isLeido']), 'ISO-8859-1', 'UTF-8') : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : 'activo';

                abmMencion($cod_mencion, $cod_usuarioFK, $cod_mensajeFK, $isLeido, $estado);
                break;
            case 'buscarMasInterConsultasYContenido':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_dictamenFK= isset($_POST['cod_dictamenFK']) ? mb_convert_encoding((string)($_POST['cod_dictamenFK']), 'ISO-8859-1', 'UTF-8') : null;
                $offset= isset($_POST['offset']) ? mb_convert_encoding((string)($_POST['offset']), 'ISO-8859-1', 'UTF-8') : null;
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                $fechaActual= new DateTime();
                $filtros= array(
                    "cod_interConsultaFK" => $cod_interConsulta,
                    "cod_usuarioFK" => $user,
                    'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
                    "cod_dictamenFK" => $cod_dictamenFK,
                    "sin_dictamen" => ($cod_dictamenFK == null || $cod_dictamenFK == "") ? true : NULL
                );
                $vistaTarjetas= obtenerVistaTarjetaInterConsuta($filtros, $limite, $offset);
                echo json_encode(array("1" => "exito", "2" => $vistaTarjetas));
                break;
            case 'buscarVistaAsociadoPaciente':
                $cod_cliente= isset($_POST['cod_cliente']) ? mb_convert_encoding((string)($_POST['cod_cliente']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                
                $registros= obtenerInterConsultasAsociadasPacienteRapido($cod_cliente, $cod_interConsulta);

                $pagina= "";
                $locales= array();
                foreach ($registros as $key => $value) {
                    $locales[]= $value['nombre_local'];
                    $pagina .= '<table class="tableRegistroSearch2" border="1" cellspacing="1" cellpadding= "5"><tr onclick="obtenerDatosInterConsulta(this)">
                        <td id="td_id" style="display: none;">'.$value['cod_interConsulta'].'</td>
                        <td id="td_datos_5" style="display: none;">'.$value['nombre_persona'].'</td>
                        <td id="td_datos_11" style="display: none;">'.$value['cod_localFK'].'</td>
                        <td id="td_datos_12" style="display: none;">'.$value['nombre_local'].'</td>
                        <td id="td_datos_6" style="display: none;">'.$value['tipo'].'</td>
                        <td id="td_datos_7" style="display: none;">'.$value['cod_clienteFK'].'</td>
                        <td id="td_datos_8" style="display: none;">'.$value['fecha_creacion'].'</td>
                        <td id="td_datos_13" style="display: none;">'.$value['cantMensajes'].'</td>
                        <td id="td_datos_4" class="tdRegistroSearch" style="width: 10%;">'.$value['cod_ventaFK'].'</td>
                        <td id="td_datos_10" class="tdRegistroSearch" style="width: 40%;">'.$value['asunto'].'</td>
                        <td id="td_datos_9" class="tdRegistroSearch" style="width: 35%;">'.$value['nombre_persona_creador'].'</td>
                        <td id="td_datos_2" class="tdRegistroSearch" style="width: 15%;">'.$value['estado'].'</td>
                    </tr></table>';
                }
                
                // Formatea los locales
                $locales= array_unique($locales);
                $nombre_local= implode(" / ", $locales);

                echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $nombre_local));
                break;
            case 'solicitarAcceso':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $nombre_usuario= isset($_POST['nombre_usuario']) ? mb_convert_encoding((string)($_POST['nombre_usuario']), 'ISO-8859-1', 'UTF-8') : null;
                $contenido= "El usuario $nombre_usuario solicito el acceso a esta conversacion.";
                
                $cod_mensaje= abmMensaje("", $contenido, $fechaActual->format('Y-m-d H:i:s'), $cod_interConsulta, NULL, NULL, FALSE);
                echo json_encode(array("1" => "exito", "2" => $cod_mensaje));
                break;
            case 'fusionarInterConsultas':
                if (!function_exists('controldeaccesoacasas') || controldeaccesoacasas(intval($user), 'FUSIONARINTERCONSULTA', " u.accion='SI' ") != 1) {
                    echo json_encode(array("1" => "NI", "2" => "No tiene permiso para fusionar hilos."));
                    break;
                }
                $cod_interConsulta= mb_convert_encoding((string)($_POST['cod_interConsulta']), "ISO-8859-1", "UTF-8");
                $cod_interConsulta_destino= mb_convert_encoding((string)($_POST['cod_interConsulta_destino']), "ISO-8859-1", "UTF-8");
                echo json_encode(fusionarInterconsultas($cod_interConsulta, $cod_interConsulta_destino, $user));
                break;
            default:
                echo json_encode(array("1"=> "error", "2" => "$funt NO IMPLEMENTADA."));
        }
    }

    function fusionarInterconsultas($cod_interConsulta, $cod_interConsulta_destino, $cod_usuarioFK) {
        $cod_interConsulta= intval($cod_interConsulta);
        $cod_interConsulta_destino= intval($cod_interConsulta_destino);
        $cod_usuarioFK= intval($cod_usuarioFK);
        if ($cod_interConsulta <= 0 || $cod_interConsulta_destino <= 0) {
            return array("1" => "error", "2" => "Campos vacios.");
        }
        if ($cod_interConsulta === $cod_interConsulta_destino) {
            return array("1" => "error", "2" => "El hilo de origen y destino deben ser diferentes.");
        }
        $ids_menciones= array();
        set_time_limit(300);

        $registrosDestino= obtenerInterConsulta(array(
            "cod_interConsulta" => $cod_interConsulta_destino
        ), 0);
        if (count($registrosDestino) === 0) {
            return array("1" => "error", "2" => "No se encontro el hilo de destino.");
        }
        $registroInterc= $registrosDestino[0];

        $registrosMens= obtenerMensaje(array(
            'cod_interConsultaFK' => $cod_interConsulta,
        ), 0);
        $mencionesTemp= array();
        foreach ($registrosMens as $valueMens) {
            $registrosMenc= obtenerMencion(array(
                "cod_mensajeFK" => $valueMens['cod_mensaje'],
            ), 0);

            foreach ($registrosMenc as $value) {
                $mencionesTemp[$value['cod_usuarioFK']] = $value['estado'];
            }
        }
        foreach ($mencionesTemp as $key => $value) {
            if ($value != 'inactivo') {
                $ids_menciones[] = $key;
            }
        }
        $ids_menciones = array_unique($ids_menciones);
        $mysqli = conectar_al_servidor();
        $mysqli->begin_transaction();
        try {
            $stmt= $mysqli->prepare("SELECT cod_interConsulta,estado FROM interconsulta WHERE cod_interConsulta IN (?,?) FOR UPDATE");
            if (!$stmt) { throw new Exception('No se pudieron bloquear los hilos para la fusion.'); }
            $stmt->bind_param('ii', $cod_interConsulta, $cod_interConsulta_destino);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron validar los hilos para la fusion.'); }
            $hilosFusion= array();
            $resultadoHilos= $stmt->get_result();
            while ($hiloFusion= $resultadoHilos->fetch_assoc()) {
                $hilosFusion[intval($hiloFusion['cod_interConsulta'])]= $hiloFusion['estado'];
            }
            $stmt->close();
            if (!isset($hilosFusion[$cod_interConsulta]) || !isset($hilosFusion[$cod_interConsulta_destino])) {
                throw new Exception('No se encontraron ambos hilos para la fusion.');
            }
            if ($hilosFusion[$cod_interConsulta] === 'inactivo' || $hilosFusion[$cod_interConsulta_destino] === 'inactivo') {
                throw new Exception('No se pueden fusionar hilos inactivos.');
            }

            $fechaFusion= (new DateTime())->format('Y-m-d H:i:s');
            $contenidoFusion= "esta y la interconsulta ".$registroInterc['asunto']." fueron unidas por @{".$cod_usuarioFK."}";
            $usuarioSistema= null;
            $dictamenSistema= null;
            $stmt= $mysqli->prepare("INSERT INTO mensaje (contenido,fecha_creacion,cod_interConsultaFK,cod_usuarioFK,cod_dictamenFK) VALUES (?,?,?,?,?)");
            if (!$stmt) { throw new Exception('No se pudo preparar el registro de auditoria de la fusion.'); }
            $stmt->bind_param('ssiii', $contenidoFusion, $fechaFusion, $cod_interConsulta_destino, $usuarioSistema, $dictamenSistema);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo registrar la fusion en el timeline.'); }
            $cod_mensaje= intval($stmt->insert_id);
            $stmt->close();

            $stmt= $mysqli->prepare("UPDATE mensaje SET cod_interConsultaFK=? WHERE cod_interConsultaFK=?");
            if (!$stmt) { throw new Exception('No se pudieron preparar los mensajes para la fusion.'); }
            $stmt->bind_param('ii', $cod_interConsulta_destino, $cod_interConsulta);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron mover los mensajes al hilo destino.'); }
            $stmt->close();

            $stmtMencion= $mysqli->prepare("INSERT INTO menciones (cod_usuarioFK,cod_mensajeFK,isLeido,estado) VALUES (?,?,0,'activo') ON DUPLICATE KEY UPDATE estado='activo'");
            if (!$stmtMencion) { throw new Exception('No se pudieron preparar las menciones para la fusion.'); }
            foreach ($ids_menciones as $codUsuarioMencion) {
                $codUsuarioMencion= intval($codUsuarioMencion);
                if ($codUsuarioMencion <= 0) { continue; }
                $stmtMencion->bind_param('ii', $codUsuarioMencion, $cod_mensaje);
                if (!$stmtMencion->execute()) { $stmtMencion->close(); throw new Exception('No se pudieron conservar las menciones de la fusion.'); }
            }

            $stmtFuturos= $mysqli->prepare("SELECT cod_mensaje FROM mensaje WHERE cod_interConsultaFK=? AND fecha_creacion>?");
            if (!$stmtFuturos) { $stmtMencion->close(); throw new Exception('No se pudieron consultar los recordatorios futuros.'); }
            $stmtFuturos->bind_param('is', $cod_interConsulta_destino, $fechaFusion);
            if (!$stmtFuturos->execute()) { $stmtFuturos->close(); $stmtMencion->close(); throw new Exception('No se pudieron consultar los recordatorios futuros.'); }
            $mensajesFuturos= array();
            $resultadoFuturos= $stmtFuturos->get_result();
            while ($mensajeFuturo= $resultadoFuturos->fetch_assoc()) {
                $mensajesFuturos[]= intval($mensajeFuturo['cod_mensaje']);
            }
            $stmtFuturos->close();
            foreach ($mensajesFuturos as $codMensajeFuturo) {
                foreach ($ids_menciones as $codUsuarioMencion) {
                    $codUsuarioMencion= intval($codUsuarioMencion);
                    if ($codUsuarioMencion <= 0) { continue; }
                    $stmtMencion->bind_param('ii', $codUsuarioMencion, $codMensajeFuturo);
                    if (!$stmtMencion->execute()) { $stmtMencion->close(); throw new Exception('No se pudieron conservar las menciones de los recordatorios futuros.'); }
                }
            }
            $stmtMencion->close();

            $stmt= $mysqli->prepare("UPDATE gastos SET cod_interConsultaFK=? WHERE cod_interConsultaFK=?");
            if (!$stmt) { throw new Exception('No se pudieron preparar los gastos para la fusion.'); }
            $stmt->bind_param('ii', $cod_interConsulta_destino, $cod_interConsulta);
            if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron mover los gastos al hilo destino.'); }
            $stmt->close();

            if (seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_programado')) {
                $stmt= $mysqli->prepare("UPDATE interconsulta_seguimiento_programado SET cod_interConsultaFK=?,cod_usuarioFK_update=?,fecha_actualizacion=NOW() WHERE cod_interConsultaFK=?");
                if (!$stmt) { throw new Exception('No se pudieron preparar los seguimientos para la fusion.'); }
                $stmt->bind_param('iii', $cod_interConsulta_destino, $cod_usuarioFK, $cod_interConsulta);
                if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudieron mover los seguimientos al hilo destino.'); }
                $stmt->close();
            }

            $stmt= $mysqli->prepare("UPDATE interconsulta SET estado='inactivo',cod_usuarioFK_edit=? WHERE cod_interConsulta=?");
            if (!$stmt) { throw new Exception('No se pudo preparar el cierre del hilo de origen.'); }
            $stmt->bind_param('ii', $cod_usuarioFK, $cod_interConsulta);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new Exception('No se pudo inactivar el hilo de origen.'); }
            $stmt->close();

            $mysqli->commit();
            $mysqli->close();
            return array("1"=> "exito", "2" => $cod_interConsulta, "3" => $cod_interConsulta_destino);
        } catch (Exception $error) {
            $mysqli->rollback();
            $mysqli->close();
            return array("1" => "error", "2" => $error->getMessage());
        }
    }

  function escaparHtmlInterconsulta($texto) {
    return htmlspecialchars((string)(isset($texto) ? $texto : ''), ENT_QUOTES, 'UTF-8');
}

function convertirTextoDocumentoInterconsulta($texto) {
    $texto = trim((string)(isset($texto) ? $texto : ''));
    if ($texto === '') {
        return '<span style="color: #8b7b5c;">Sin contenido cargado.</span>';
    }

    return nl2br(escaparHtmlInterconsulta($texto), false);
}

function obtenerResumenDictamenInterconsulta($texto, $limite = 220) {
    $texto = trim(strip_tags(html_entity_decode((string)(isset($texto) ? $texto : ''), ENT_QUOTES, 'UTF-8')));
    $texto = preg_replace('/\s+/', ' ', $texto);

    if ($texto === '') {
        return 'Sin contenido cargado.';
    }

    if (function_exists('mb_strlen')) {
        if (mb_strlen($texto, 'UTF-8') > $limite) {
            return mb_substr($texto, 0, $limite, 'UTF-8').'...';
        }
    } else if (strlen($texto) > $limite) {
        return substr($texto, 0, $limite).'...';
    }

    return $texto;
}

function obtenerNombreMesInterconsulta($numeroMes) {
    $meses = array(
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    );

    $numeroMes = intval($numeroMes);
    return isset($meses[$numeroMes]) ? $meses[$numeroMes] : '';
}

function obtenerNombreDiaInterconsulta($numeroDia) {
    $dias = array(
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo'
    );

    $numeroDia = intval($numeroDia);
    return isset($dias[$numeroDia]) ? $dias[$numeroDia] : '';
}

function obtenerFechaObjetoInterconsulta($fecha) {
    try {
        return new DateTime((string)$fecha);
    } catch (Exception $e) {
        return null;
    }
}

function obtenerEtiquetaDiaInterconsulta($fechaObj) {
    $hoy = new DateTime('today');
    $ayer = new DateTime('yesterday');
    $fechaDia = clone $fechaObj;
    $fechaDia->setTime(0, 0, 0);

    if ($fechaDia->format('Y-m-d') == $hoy->format('Y-m-d')) {
        return 'Hoy';
    }
    if ($fechaDia->format('Y-m-d') == $ayer->format('Y-m-d')) {
        return 'Ayer';
    }

    $diaSemana = obtenerNombreDiaInterconsulta($fechaObj->format('N'));
    $mes = obtenerNombreMesInterconsulta($fechaObj->format('n'));

    return ucfirst($diaSemana).' '.$fechaObj->format('d').' de '.$mes.' de '.$fechaObj->format('Y');
}

function obtenerSeparadoresCronologiaInterconsulta($fecha, &$mesActual, &$diaActual) {
    $fechaObj = obtenerFechaObjetoInterconsulta($fecha);
    if (!$fechaObj) {
        return '';
    }

    $html = '';
    $mesClave = $fechaObj->format('Y-m');
    $diaClave = $fechaObj->format('Y-m-d');

    if ($mesClave != $mesActual) {
        $mesActual = $mesClave;
        $mesTexto = strtoupper(obtenerNombreMesInterconsulta($fechaObj->format('n'))).' '.$fechaObj->format('Y');
        $html .= '<div class="interconsulta-timeline-separator interconsulta-timeline-separator--month" data-periodo="'.$mesClave.'"><span>'.$mesTexto.'</span></div>';
    }

    if ($diaClave != $diaActual) {
        $diaActual = $diaClave;
        $html .= '<div class="interconsulta-timeline-separator interconsulta-timeline-separator--day" data-fecha="'.$diaClave.'"><span>'.escaparHtmlInterconsulta(obtenerEtiquetaDiaInterconsulta($fechaObj)).'</span></div>';
    }

    return $html;
}

function obtenerTextoPlanoInterconsulta($texto) {
    $texto = str_replace(array('<br>', '<br/>', '<br />'), ' ', (string)$texto);
    $texto = trim(strip_tags(html_entity_decode($texto, ENT_QUOTES, 'UTF-8')));
    return preg_replace('/\s+/', ' ', $texto);
}

function obtenerTipoEventoSistemaInterconsulta($textoPlano) {
    $texto = mb_strtolower((string)$textoPlano);

    if (strpos($texto, 'rechazo') !== false || strpos($texto, 'rechaz') !== false) {
        return array('clase' => 'danger', 'icono' => 'fa-circle-xmark');
    }
    if (strpos($texto, 'aprobo') !== false || strpos($texto, 'aprob') !== false) {
        return array('clase' => 'success', 'icono' => 'fa-circle-check');
    }
    if (strpos($texto, 'programado') !== false) {
        return array('clase' => 'warning', 'icono' => 'fa-clock');
    }
    if (strpos($texto, 'creo') !== false) {
        return array('clase' => 'info', 'icono' => 'fa-circle-plus');
    }
    if (strpos($texto, 'modifico') !== false || strpos($texto, 'modific') !== false || strpos($texto, 'cambio') !== false || strpos($texto, 'cambi') !== false) {
        return array('clase' => 'info', 'icono' => 'fa-pen-to-square');
    }

    return array('clase' => 'neutral', 'icono' => 'fa-circle-info');
}

function obtenerVistaEventoSistemaInterconsulta($contenido, $fecha, $iconoForzado = '') {
    $textoPlano = obtenerTextoPlanoInterconsulta($contenido);
    $evento = obtenerTipoEventoSistemaInterconsulta($textoPlano);
    if ($iconoForzado !== '') {
        $evento['icono'] = $iconoForzado;
    }

    $fechaObj = obtenerFechaObjetoInterconsulta($fecha);
    $fechaCorta = $fechaObj ? $fechaObj->format('d/m/Y H:i') : $fecha;
    $fechaDia = $fechaObj ? $fechaObj->format('Y-m-d') : '';
    $titulo = escaparHtmlInterconsulta($textoPlano.' - '.$fechaCorta);

    return '<div class="interconsulta-message-row interconsulta-message-row--system" data-fecha="'.$fechaDia.'">
        <div class="interconsulta-system-event interconsulta-system-event--'.$evento['clase'].'" title="'.$titulo.'">
            <i class="fa-solid '.$evento['icono'].'" aria-hidden="true"></i>
            <span class="interconsulta-system-event__text">'.escaparHtmlInterconsulta($textoPlano).'</span>
            <time>'.escaparHtmlInterconsulta($fechaCorta).'</time>
        </div>
    </div>';
}

    function obtenerBotonMasMensajesInterconsulta($offset, $cod_dictamen = '') {
        $cod_dictamen = $cod_dictamen === null ? '' : (string)$cod_dictamen;
        $codDictamenJs = htmlspecialchars(json_encode($cod_dictamen), ENT_QUOTES, 'UTF-8');

        return '<div data-role="dictamen-boton-mas" style="width: 100%; display: flex; justify-content: center; margin-bottom: 12px;">'
            . '<button class="btn btn-success" onclick="verMasMensajesInterconsulta('.intval($offset).', '.$codDictamenJs.')">Ver más mensajes...</button>'
            . '</div>';
    }

    function obtenerVistaDocumentoDictamenInterconsulta($dictamen, $interconsulta, $idDocumento, $nombreAutor, $fechaDictamen, $estadoDictamen, $estadoColor) {
        $estadoFondo = 'rgba(184, 134, 11, 0.14)';
        $estado = obtenerEstadoVisualDictamen($dictamen);

		if (in_array($estado, array('emitido', 'aprobado', 'autorizado'), true)) {
			$estadoFondo = 'rgba(47, 111, 62, 0.14)';
		} else if (in_array($estado, array('ejecutado', 'finalizado', 'complementaria'), true)) {
			$estadoFondo = 'rgba(31, 78, 121, 0.14)';
		} else if (in_array($estado, array('inactivo', 'rechazado', 'anulado', 'rectificado'), true)) {
			$estadoFondo = 'rgba(122, 122, 122, 0.16)';
		} else {
			$estadoFondo = '';
		}

        $asunto = escaparHtmlInterconsulta($dictamen['asunto']);
        $contenido = convertirTextoDocumentoInterconsulta($dictamen['dictamen']);
        $idDocumento = escaparHtmlInterconsulta($idDocumento);
        $nombreAutor = escaparHtmlInterconsulta($nombreAutor);
        $fechaDictamen = escaparHtmlInterconsulta($fechaDictamen);
        $estadoDictamen = escaparHtmlInterconsulta($estadoDictamen);
        $codInterconsulta = escaparHtmlInterconsulta($interconsulta['cod_interConsulta']);
        $nombreAutoriza = !empty($dictamen['nombre_persona_autoriz']) ? escaparHtmlInterconsulta($dictamen['nombre_persona_autoriz']) : '';
        $nombreEjecuta = !empty($dictamen['nombre_persona_ejecut']) ? escaparHtmlInterconsulta($dictamen['nombre_persona_ejecut']) : '';
        $fechaAutoriz = !empty($dictamen['fecha_autoriz']) ? escaparHtmlInterconsulta(date('d/m/Y H:i', strtotime($dictamen['fecha_autoriz']))) : '';
        $fechaEjecut = !empty($dictamen['fecha_ejecut']) ? escaparHtmlInterconsulta(date('d/m/Y H:i', strtotime($dictamen['fecha_ejecut']))) : '';

        $metaExtra = '';
        if ($nombreAutoriza !== '') {
            $metaExtra .= '<div><strong>Autorizado por:</strong> '.$nombreAutoriza.'</div>';
            $metaExtra .= '<div><strong>Fecha autorizado:</strong> '.$fechaAutoriz.'</div>';
        }
        if ($nombreEjecuta !== '') {
            $metaExtra .= '<div><strong>Ejecución registrada:</strong> '.$nombreEjecuta.'</div>';
            $metaExtra .= '<div><strong>Fecha ejecutado:</strong> '.$fechaEjecut.'</div>';
        }

        return '<div class="interc-dictamen-document-shell">
            <div class="interc-dictamen-document">
                <div class="interc-dictamen-band">
                    <span>Resolución administrativa</span>
                    <span>Hilo #'.$codInterconsulta.'</span>
                </div>
                <div class="interc-dictamen-kicker">Registro oficial del dictamen</div>
                <h3 class="interc-dictamen-doc-title">'.$asunto.'</h3>
                <div class="interc-dictamen-doc-subtitle">
                    <div><strong>Documento:</strong> '.$idDocumento.'</div>
                    <div><strong>Emitido:</strong> '.$fechaDictamen.'</div>
                    <div><strong>Responsable:</strong> '.$nombreAutor.'</div>
                    '.$metaExtra.'
                </div>
                <div class="interc-dictamen-doc-content">'.$contenido.'</div>
                <div class="interc-dictamen-doc-footer">
                    <div class="interc-dictamen-doc-status" style="color: '.$estadoColor.'; background-color: '.$estadoFondo.';">'.$estadoDictamen.'</div>
                </div>
            </div>
        </div>';
    }

    function obtenerInterConsultaDetalleRapido($cod_interConsulta, $cod_usuarioFK) {
        $cod_interConsulta= intval($cod_interConsulta);
        $cod_usuarioFK= intval($cod_usuarioFK);
        if ($cod_interConsulta <= 0 || $cod_usuarioFK <= 0) {
            return array();
        }
        if (!seguimientoProgramadoPuedeAccederHilo($cod_interConsulta, $cod_usuarioFK)) {
            return array();
        }

        $mysqli= conectar_al_servidor();
        $sql= "SELECT ic.*,
                l.Nombre AS nombre_local,
                COALESCE(ip.cod_clienteFK_principal, vt.cod_clienteFK) AS cod_clienteFK,
                vt.num_factura,
                vt.apodo AS apodo_venta,
                COALESCE(NULLIF(ip.nombre_paciente_snapshot,''), paciente.nombre_persona) AS nombre_persona,
                creador.nombre_persona AS nombre_persona_creador,
                COALESCE(NULLIF(ip.cedula,''), cl.ci_cliente) AS cedula,
                IF(ip.id IS NULL,0,1) AS esSeguimientoPaciente,
                IFNULL(ip.estado_conflicto,0) AS seguimiento_conflicto,
                IFNULL(ip.detalle_conflicto,'') AS seguimiento_detalle_conflicto,
                IFNULL(ip.ventas_sin_plan_madre,0) AS ventas_sin_plan_madre,
                IFNULL(ip.total_ventas,0) AS total_ventas_paciente,
                IFNULL(ip.total_planes_madre,0) AS total_planes_madre,
                (SELECT COUNT(*) FROM gastos g WHERE g.cod_interConsultaFK=ic.cod_interConsulta) AS cantAsociadoGastos,
                (SELECT IFNULL(SUM(g.monto),0) FROM gastos g WHERE g.cod_interConsultaFK=ic.cod_interConsulta) AS total_gastos,
                (SELECT COUNT(*) FROM mensaje mt WHERE mt.cod_interConsultaFK=ic.cod_interConsulta) AS cantMensajes,
                (SELECT COUNT(*) FROM menciones mc
                    INNER JOIN mensaje mu ON mu.cod_mensaje=mc.cod_mensajeFK
                    WHERE mu.cod_interConsultaFK=ic.cod_interConsulta
                    AND mu.fecha_creacion<=NOW()
                    AND mc.cod_usuarioFK=?
                    AND mc.isLeido=0
                    AND mc.estado='activo') AS cantMensajesNoLeidos
            FROM interconsulta ic
            LEFT JOIN interconsulta_paciente ip
                ON ip.cod_interConsultaFK=ic.cod_interConsulta AND ip.estado='activo'
            LEFT JOIN venta vt ON vt.cod_venta=ic.cod_ventaFK
            LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
            LEFT JOIN persona paciente ON paciente.cod_persona=vt.cod_clienteFK
            LEFT JOIN persona creador ON creador.cod_persona=ic.cod_usuarioFK_create
            LEFT JOIN local l ON l.cod_local=ic.cod_localFK
            WHERE ic.cod_interConsulta=?
            LIMIT 1";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) {
            $mysqli->close();
            return array();
        }
        $stmt->bind_param('ii', $cod_usuarioFK, $cod_interConsulta);
        if (!$stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            return array();
        }

        $result= $stmt->get_result();
        $registros= array();
        while ($row= $result->fetch_assoc()) {
            $reg= array();
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $reg[$key]= mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                } else {
                    $reg[$key]= $value;
                }
            }
            $reg['resumen_seguimiento_cargado']= 0;
            $registros[]= $reg;
        }
        $stmt->close();
        $mysqli->close();
        return $registros;
    }

    function obtenerInterConsultasAsociadasPacienteRapido($cod_clienteFK, $cod_interConsultaExcluir) {
        $cod_clienteFK= intval($cod_clienteFK);
        $cod_interConsultaExcluir= intval($cod_interConsultaExcluir);
        if ($cod_clienteFK <= 0) { return array(); }

        $mysqli= conectar_al_servidor();
        $sql= "SELECT ic.*,
                l.Nombre AS nombre_local,
                COALESCE(ip.cod_clienteFK_principal, vt.cod_clienteFK) AS cod_clienteFK,
                COALESCE(NULLIF(ip.nombre_paciente_snapshot,''), paciente.nombre_persona) AS nombre_persona,
                creador.nombre_persona AS nombre_persona_creador,
                (SELECT COUNT(*) FROM mensaje mj WHERE mj.cod_interConsultaFK=ic.cod_interConsulta) AS cantMensajes
            FROM interconsulta ic
            LEFT JOIN interconsulta_paciente ip
                ON ip.cod_interConsultaFK=ic.cod_interConsulta AND ip.estado='activo'
            LEFT JOIN venta vt ON vt.cod_venta=ic.cod_ventaFK
            LEFT JOIN persona paciente ON paciente.cod_persona=vt.cod_clienteFK
            LEFT JOIN persona creador ON creador.cod_persona=ic.cod_usuarioFK_create
            LEFT JOIN local l ON l.cod_local=ic.cod_localFK
            WHERE ic.estado<>'inactivo'
            AND ic.cod_interConsulta<>?
            AND (vt.cod_clienteFK=? OR ip.cod_clienteFK_principal=?)
            ORDER BY ic.cod_interConsulta DESC
            LIMIT 50";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return array(); }
        $stmt->bind_param('iii', $cod_interConsultaExcluir, $cod_clienteFK, $cod_clienteFK);
        if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return array(); }
        $result= $stmt->get_result();
        $registros= array();
        while ($row= $result->fetch_assoc()) {
            $reg= array();
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $reg[$key]= mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                } else {
                    $reg[$key]= $value;
                }
            }
            $registros[]= $reg;
        }
        $stmt->close();
        $mysqli->close();
        return $registros;
    }

    function obtenerCantidadMensajesInterConsulta($cod_interConsulta, $cod_dictamenFK= null, $sin_dictamen= false, $solo_actuales= false) {
        $cod_interConsulta= intval($cod_interConsulta);
        if ($cod_interConsulta <= 0) { return 0; }

        $sql= "SELECT COUNT(*) AS total FROM mensaje WHERE cod_interConsultaFK=?";
        $tipos= 'i';
        $parametros= array($cod_interConsulta);
        if ($cod_dictamenFK !== null && $cod_dictamenFK !== '' && intval($cod_dictamenFK) > 0) {
            $sql .= " AND cod_dictamenFK=?";
            $tipos .= 'i';
            $parametros[]= intval($cod_dictamenFK);
        } else if ($sin_dictamen) {
            $sql .= " AND cod_dictamenFK IS NULL";
        }
        if ($solo_actuales) {
            $sql .= " AND fecha_creacion<=NOW()";
        }

        $mysqli= conectar_al_servidor();
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return 0; }
        $refs= array();
        foreach ($parametros as $key => $value) { $refs[$key]= &$parametros[$key]; }
        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($tipos), $refs));
        if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return 0; }
        $row= $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $mysqli->close();
        return isset($row['total']) ? intval($row['total']) : 0;
    }

    function obtenerConteosMensajesDictamenInterConsulta($cod_interConsulta) {
        $cod_interConsulta= intval($cod_interConsulta);
        if ($cod_interConsulta <= 0) { return array(); }
        $mysqli= conectar_al_servidor();
        $sql= "SELECT cod_dictamenFK, COUNT(*) AS total
            FROM mensaje
            WHERE cod_interConsultaFK=? AND cod_dictamenFK IS NOT NULL AND fecha_creacion<=NOW()
            GROUP BY cod_dictamenFK";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return array(); }
        $stmt->bind_param('i', $cod_interConsulta);
        if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return array(); }
        $result= $stmt->get_result();
        $conteos= array();
        while ($row= $result->fetch_assoc()) {
            $conteos[intval($row['cod_dictamenFK'])]= intval($row['total']);
        }
        $stmt->close();
        $mysqli->close();
        return $conteos;
    }

    function obtenerResumenesDictamenInterConsulta($cod_interConsulta) {
        $cod_interConsulta= intval($cod_interConsulta);
        if ($cod_interConsulta <= 0) { return array(); }
        $mysqli= conectar_al_servidor();
        $sql= "SELECT d.id, d.asunto, LEFT(IFNULL(d.dictamen,''),220) AS dictamen,
                d.estado, d.fecha_create, d.cod_usuarioFK_create,
                p.nombre_persona AS nombre_persona_create,
                u.url AS url_create
            FROM dictamenes d
            LEFT JOIN persona p ON p.cod_persona=d.cod_usuarioFK_create
            LEFT JOIN usuario u ON u.cod_usuario=d.cod_usuarioFK_create
            WHERE d.cod_interConsultaFK=?
            ORDER BY FIELD(d.estado,'emitido','solicitado','aprobado','autorizado','ejecutado','rechazado','finalizado','anulado','rectificado','complementaria','inactivo'), d.id DESC";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return array(); }
        $stmt->bind_param('i', $cod_interConsulta);
        if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return array(); }
        $result= $stmt->get_result();
        $registros= array();
        while ($row= $result->fetch_assoc()) {
            $reg= array();
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $reg[$key]= mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                } else {
                    $reg[$key]= $value;
                }
            }
            $registros[]= $reg;
        }
        $stmt->close();
        $mysqli->close();
        return $registros;
    }

    function marcarMensajesLeidosInterConsulta($cod_interConsulta, $cod_usuarioFK) {
        $cod_interConsulta= intval($cod_interConsulta);
        $cod_usuarioFK= intval($cod_usuarioFK);
        if ($cod_interConsulta <= 0 || $cod_usuarioFK <= 0) { return 0; }
        $mysqli= conectar_al_servidor();
        $sql= "UPDATE menciones mc
            INNER JOIN mensaje mj ON mj.cod_mensaje=mc.cod_mensajeFK
            SET mc.isLeido=1
            WHERE mj.cod_interConsultaFK=?
            AND mj.fecha_creacion<=NOW()
            AND mc.cod_usuarioFK=?
            AND mc.isLeido=0";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) { $mysqli->close(); return 0; }
        $stmt->bind_param('ii', $cod_interConsulta, $cod_usuarioFK);
        if (!$stmt->execute()) { $stmt->close(); $mysqli->close(); return 0; }
        $actualizadas= $stmt->affected_rows;
        $stmt->close();
        $mysqli->close();
        return $actualizadas;
    }

    function obtenerDetalleDictamenDiferidoInterConsulta($cod_interConsulta, $cod_dictamen, $cod_usuarioFK, $limiteMensajes= 10) {
        $registrosInterc= obtenerInterConsultaDetalleRapido($cod_interConsulta, $cod_usuarioFK);
        if (count($registrosInterc) == 0) {
            echo json_encode(array('1' => 'NI', '2' => 'Usted no tiene acceso a esta conversacion.'));
            return false;
        }
        $registrosDictamen= obtenerDictamen(array(
            'id' => intval($cod_dictamen),
            'cod_interConsultaFK' => intval($cod_interConsulta)
        ), 1);
        if (count($registrosDictamen) == 0) {
            echo json_encode(array('1' => 'error', '2' => 'No se encontro el dictamen solicitado.'));
            return false;
        }

        $dictamen= $registrosDictamen[0];
        $interconsulta= $registrosInterc[0];
        $nombreAutor= !empty($dictamen['nombre_persona_create']) ? $dictamen['nombre_persona_create'] : 'Sin autor';
        $fechaDictamen= !empty($dictamen['fecha_create']) ? date('d/m/Y H:i', strtotime($dictamen['fecha_create'])) : '';
        $fechaId= !empty($dictamen['fecha_create']) ? date('Y', strtotime($dictamen['fecha_create'])) : date('Y');
        $idDocumento= 'RES-'.$fechaId.'-'.$interconsulta['cod_interConsulta'].'-'.str_pad($dictamen['id'], 2, '0', STR_PAD_LEFT);
        $estadoVisual= obtenerEstadoVisualDictamen($dictamen);
        $estadoEtiqueta= obtenerEtiquetaEstadoDictamen($estadoVisual);
        $estadoColor= obtenerColorEstadoDictamen($estadoVisual);
        $usuarios= buscarUsuarios();
        $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
            'cod_interConsultaFK' => $interconsulta['cod_interConsulta'],
            'cod_usuarioFK' => $cod_usuarioFK,
            'cod_dictamenFK' => $dictamen['id']
        ), $limiteMensajes, 0, $usuarios);
        $totalMensajes= obtenerCantidadMensajesInterConsulta($interconsulta['cod_interConsulta'], $dictamen['id'], false, true);
        $paginaMensajesCompleta= '';
        if ($totalMensajes > $limiteMensajes) {
            $paginaMensajesCompleta .= obtenerBotonMasMensajesInterconsulta($limiteMensajes, $dictamen['id']);
        }
        $paginaMensajesCompleta .= '<div data-role="dictamen-mensajes">'.$paginaMensajes.'</div>';
        $documento= obtenerVistaDocumentoDictamenInterconsulta($dictamen, $interconsulta, $idDocumento, $nombreAutor, $fechaDictamen, $estadoEtiqueta, $estadoColor);

        echo json_encode(array(
            '1' => 'exito',
            '2' => $documento,
            '3' => $paginaMensajesCompleta,
            '4' => $totalMensajes
        ));
        return true;
    }

    function obtenerVistaSeguimientosProgramadosInterConsulta($codInterConsulta, $codUsuario) {
        if (!seguimientoProgramadoEstructuraDisponible()
            || !seguimientoProgramadoPuedeAccederHilo($codInterConsulta, $codUsuario)) {
            return '';
        }

        $seguimientos= seguimientoProgramadoObtenerSeguimientosHilo($codInterConsulta, 40);
        if (count($seguimientos) === 0) {
            return '';
        }

        $puedeAdministrar= seguimientoProgramadoPuedeAdministrarPlantillas($codUsuario);
        $cantidadActivos= 0;
        $tarjetas= '';
        foreach ($seguimientos as $seguimiento) {
            $idSeguimiento= intval($seguimiento['id_seguimiento']);
            $estadoVisual= seguimientoProgramadoEstadoVisual($seguimiento);
            $estadoEtiqueta= seguimientoProgramadoEtiquetaEstado($estadoVisual);
            $esActivo= isset($seguimiento['estado']) && $seguimiento['estado'] === 'programado';
            if ($esActivo) {
                $cantidadActivos++;
            }
            $puedeGestionar= $esActivo && (
                intval($seguimiento['cod_responsableFK']) === intval($codUsuario)
                || intval($seguimiento['cod_usuarioFK_create']) === intval($codUsuario)
                || $puedeAdministrar
            );
            $fechaProgramada= !empty($seguimiento['fecha_programada']) && strtotime($seguimiento['fecha_programada'])
                ? date('d/m/Y H:i', strtotime($seguimiento['fecha_programada']))
                : (string)$seguimiento['fecha_programada'];
            $fechaCreacion= !empty($seguimiento['fecha_creacion']) && strtotime($seguimiento['fecha_creacion'])
                ? date('d/m/Y H:i', strtotime($seguimiento['fecha_creacion']))
                : (string)$seguimiento['fecha_creacion'];
            $fechaCierre= !empty($seguimiento['fecha_cierre']) && strtotime($seguimiento['fecha_cierre'])
                ? date('d/m/Y H:i', strtotime($seguimiento['fecha_cierre']))
                : '';
            $motivo= trim((string)$seguimiento['motivo']) !== '' ? $seguimiento['motivo'] : 'Seguimiento personalizado';
            $nombreResponsable= trim((string)$seguimiento['nombre_responsable']) !== '' ? $seguimiento['nombre_responsable'] : 'Responsable no disponible';
            $nombreCreador= trim((string)$seguimiento['nombre_creador']) !== '' ? $seguimiento['nombre_creador'] : 'Usuario no disponible';
            $mensajeSeguimiento= isset($seguimiento['mensaje']) ? trim((string)$seguimiento['mensaje']) : '';
            $mensajePlano= trim(preg_replace('/\s+/', ' ', strip_tags($mensajeSeguimiento)));
            $urlResponsable= isset($seguimiento['url_responsable']) ? trim((string)$seguimiento['url_responsable']) : '';
            $urlAvatarResponsable= $urlResponsable !== '' ? $urlResponsable : '/GoodVentaAsisCap/iconos/user.png';
            $avatarResponsable= '<img src="'.escaparHtmlInterconsulta($urlAvatarResponsable).'" alt="Foto del responsable '.escaparHtmlInterconsulta($nombreResponsable).'" onerror="this.onerror=null;this.src=\'/GoodVentaAsisCap/iconos/user.png\';">';
            $resultado= isset($seguimiento['resultado']) ? trim((string)$seguimiento['resultado']) : '';

            $atributos= ' data-seguimiento-id="'.$idSeguimiento.'"'
                .' data-cod-hilo="'.intval($codInterConsulta).'"'
                .' data-plantilla-id="'.intval($seguimiento['id_plantillaFK']).'"'
                .' data-motivo="'.escaparHtmlInterconsulta($motivo).'"'
                .' data-mensaje="'.escaparHtmlInterconsulta($mensajeSeguimiento).'"'
                .' data-fecha-programada="'.escaparHtmlInterconsulta($seguimiento['fecha_programada']).'"'
                .' data-responsable="'.intval($seguimiento['cod_responsableFK']).'"';

            $mensajeHtml= $mensajeSeguimiento !== ''
                ? '<p class="interconsulta-followup-card__message" title="'.escaparHtmlInterconsulta($mensajePlano).'">'.nl2br(escaparHtmlInterconsulta($mensajeSeguimiento), false).'</p>'
                : '';
            $resultadoHtml= $resultado !== ''
                ? '<div class="interconsulta-followup-card__result"><strong>Resultado:</strong> '.nl2br(escaparHtmlInterconsulta($resultado), false).'</div>'
                : '';
            $auditoriaHtml= '<span class="interconsulta-followup-card__audit"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Programado por '.escaparHtmlInterconsulta($nombreCreador)
                .($fechaCreacion !== '' ? ' el '.escaparHtmlInterconsulta($fechaCreacion) : '')
                .($fechaCierre !== '' ? ' &middot; Cerrado el '.escaparHtmlInterconsulta($fechaCierre) : '')
                .'</span>';

            $accionesHtml= '';
            $completarHtml= '';
            if ($puedeGestionar) {
                $accionesHtml= '<div class="interconsulta-followup-card__actions">'
                    .'<button type="button" class="interconsulta-followup-action interconsulta-followup-action--complete" data-action="mostrar-completar-seguimiento" aria-expanded="false"><i class="fa-solid fa-check" aria-hidden="true"></i> Completar</button>'
                    .'<button type="button" class="interconsulta-followup-action" data-action="reprogramar-seguimiento"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Reprogramar</button>'
                    .'</div>';
                $completarHtml= '<div class="interconsulta-followup-complete" hidden>'
                        .'<label>Resultado de la gesti&oacute;n<textarea maxlength="750" data-role="resultado-seguimiento" placeholder="Ej.: Se logr&oacute; contactar y se coordin&oacute; el siguiente paso."></textarea></label>'
                        .'<div class="interconsulta-followup-complete__buttons">'
                            .'<button type="button" data-action="cancelar-completar-seguimiento">Cancelar</button>'
                            .'<button type="button" data-action="completar-seguimiento">Completar</button>'
                            .'<button type="button" data-action="completar-y-programar-seguimiento">Completar y programar otro</button>'
                        .'</div>'
                        .'</div>';
            }

            $tarjetas .= '<article id="seguimientoInterConsulta-'.$idSeguimiento.'" class="interconsulta-followup-card interconsulta-followup-card--'.escaparHtmlInterconsulta($estadoVisual).'"'.$atributos.'>'
                .'<span class="interconsulta-followup-card__avatar" title="Responsable: '.escaparHtmlInterconsulta($nombreResponsable).'">'.$avatarResponsable.'</span>'
                .'<div class="interconsulta-followup-card__body">'
                    .'<header class="interconsulta-followup-card__header">'
                        .'<div class="interconsulta-followup-card__title">'
                            .'<span>Seguimiento programado #'.$idSeguimiento.'</span>'
                            .'<h4 title="'.escaparHtmlInterconsulta($motivo).'">'.escaparHtmlInterconsulta($motivo).'</h4>'
                        .'</div>'
                    .'</header>'
                    .'<div class="interconsulta-followup-card__details">'
                        .'<span><i class="fa-regular fa-calendar" aria-hidden="true"></i><strong>Fecha:</strong>'.escaparHtmlInterconsulta($fechaProgramada).'</span>'
                        .'<span><i class="fa-regular fa-user" aria-hidden="true"></i><strong>Responsable:</strong>'.escaparHtmlInterconsulta($nombreResponsable).'</span>'
                        .$auditoriaHtml
                    .'</div>'
                    .$mensajeHtml
                    .$resultadoHtml
                .'</div>'
                .'<aside class="interconsulta-followup-card__aside">'
                    .'<span class="interconsulta-followup-status interconsulta-followup-status--'.escaparHtmlInterconsulta($estadoVisual).'">'.escaparHtmlInterconsulta($estadoEtiqueta).'</span>'
                    .$accionesHtml
                .'</aside>'
                .$completarHtml
            .'</article>';
        }

        return '<section class="interconsulta-followup-timeline" data-role="seguimientos-programados" aria-label="Seguimientos programados. Tareas internas que no env&iacute;an mensajes al paciente.">'
            .'<header class="interconsulta-followup-timeline__header">'
                .'<div><span>Tareas internas</span><span class="interconsulta-followup-timeline__separator" aria-hidden="true">&middot;</span><h3>Seguimientos programados</h3></div>'
                .'<span class="interconsulta-followup-timeline__count">'.$cantidadActivos.' pendiente'.($cantidadActivos === 1 ? '' : 's').'</span>'
            .'</header>'
            .'<div class="interconsulta-followup-timeline__list">'.$tarjetas.'</div>'
            .'</section>';
    }

    function obtenerVistaInterConsultaYMensajes($filtros, $limite, $nombre_usuario, $normalizacion_seguimiento = array()) {
        $pagina = "";
        $limiteMensajes= intval($limite);
        if ($limiteMensajes <= 0) {
            $limiteMensajes= 10;
        } else {
            $limiteMensajes= min($limiteMensajes, 20);
        }
        $totalCantMensaje= 0;
        
        // Se obtienen las interconsultas
        $registrosInterc= obtenerInterConsultaDetalleRapido($filtros['cod_interConsulta'], $filtros['cod_usuarioFK']);

        if (count($registrosInterc) == 0) {
            echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
            return false;
        }

        foreach ($registrosInterc as $valueInter) {
            if (!empty($normalizacion_seguimiento["conflicto"])) {
                $valueInter["seguimiento_conflicto"] = 1;
                $valueInter["seguimiento_detalle_conflicto"] = isset($normalizacion_seguimiento["detalle_conflicto"]) ? $normalizacion_seguimiento["detalle_conflicto"] : "La cedula esta asociada a mas de un paciente.";
            }
            $valueInter["asunto"] = asuntoVistaSeguimientoPacienteInterConsulta($valueInter);
            // Se crea el encabezado
            $pagina.= '<div>';
            
            $mencionesElemento= "";
            $menciones= array();
            
            // Solo se necesita el mensaje mas reciente para obtener los participantes actuales.
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
                'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
            ), 1);

            if (count($registrosMens) > 0) {
                $ultimoMensaje= $registrosMens[0];
    
                $paginaMensajes= "";
                // Obtiene todas las menciones
                $registrosMenc= obtenerMencion(array(
                    'cod_mensajeFK' => $ultimoMensaje['cod_mensaje'],
                    'estado' => 'activo'
                ), 0);
    
                foreach ($registrosMenc as $valueMenc) {
                    if ($valueMenc['estado'] == 'activo' && !in_array($valueMenc['nombre_persona'], $menciones)) {
                        $mencionesElemento .= '<li class="interconsulta-participant-item" style="display: '.(($valueInter['cod_usuarioFK_create'] != $valueMenc['cod_usuarioFK']) ? "flex" : "none").';">
                            <div class="interconsulta-participant-info">
                                <span class="interconsulta-participant-avatar"><i class="fa-solid fa-user"></i></span>
                                <span>'.$valueMenc['nombre_persona'].'</span>'.
                                (($valueMenc['isLeido'] == 1) ? '<i class="fa-solid fa-check-double interconsulta-participant-check" title="Participante activo"></i>' : '').
                            '</div>
                            <button type="button" class="interconsulta-participant-remove" title="Quitar participante" onclick="eliminarMencionMensaje('.$valueMenc["cod_mencion"].')">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </li>';
                        $menciones[] = $valueMenc['nombre_persona'];
                    }
                }
            }

            $usuariosMensaje= buscarUsuarios();
            $totalMensajesSinDictamen= obtenerCantidadMensajesInterConsulta($valueInter['cod_interConsulta'], null, true, true);
            $conteosMensajesDictamen= obtenerConteosMensajesDictamenInterConsulta($valueInter['cod_interConsulta']);

            // Se cargan solo metadatos y extractos; el documento y sus mensajes se consultan al desplegarlo.
            $registros_dictamenes= obtenerResumenesDictamenInterConsulta($valueInter['cod_interConsulta']);
            $paginaOpciones= "";
            foreach ($registros_dictamenes as $dictamen) {
                $paginaOpciones .= '<option value="'.$dictamen['id'].'">'.$dictamen['asunto'].'</option>';
                $nombreAutor = !empty($dictamen['nombre_persona_create']) ? $dictamen['nombre_persona_create'] : 'Sin autor';
                $fechaDictamen = !empty($dictamen['fecha_create']) ? date('d/m/Y H:i', strtotime($dictamen['fecha_create'])) : '';
                $fechaId = !empty($dictamen['fecha_create']) ? date('Y', strtotime($dictamen['fecha_create'])) : date('Y');
                $idDocumento = 'RES-'.$fechaId.'-'.$valueInter['cod_interConsulta'].'-'.str_pad($dictamen['id'], 2, '0', STR_PAD_LEFT);
                $estadoDictamenValor = obtenerEstadoVisualDictamen($dictamen);
                $estadoDictamen = obtenerEtiquetaEstadoDictamen($estadoDictamenValor);
                $estadoColor = obtenerColorEstadoDictamen($estadoDictamenValor);
                $urlAutor = !empty($dictamen['url_create']) ? $dictamen['url_create'] : '/GoodVentaAsisCap/iconos/user.png';
                $estadoDictamenClase = preg_replace('/[^a-z0-9_-]/', '', strtolower($estadoDictamenValor));
                $codDictamen = intval($dictamen['id']);
                $cantidadMensajesDictamen = isset($conteosMensajesDictamen[$codDictamen]) ? $conteosMensajesDictamen[$codDictamen] : 0;
                $idPanelResolucion = 'contenedorResolucionInterConsulta'.$codDictamen;
                $idPanelMensajes = 'contenedorMensajesInterConsulta'.$codDictamen;
                $asuntoDictamen = escaparHtmlInterconsulta($dictamen['asunto']);
                $resumenDictamen = escaparHtmlInterconsulta(obtenerResumenDictamenInterconsulta($dictamen['dictamen'], 220));
                $idDocumentoSeguro = escaparHtmlInterconsulta($idDocumento);
                $nombreAutorSeguro = escaparHtmlInterconsulta($nombreAutor);
                $fechaDictamenSeguro = escaparHtmlInterconsulta($fechaDictamen);
                $urlAutorSeguro = escaparHtmlInterconsulta($urlAutor);
                $estadoDictamenSeguro = escaparHtmlInterconsulta($estadoDictamen);
                $textoCantidadMensajes = $cantidadMensajesDictamen.' mensaje'.($cantidadMensajesDictamen == 1 ? '' : 's');

                $pagina .= '<section class="interc-dictamen-card interconsulta-resolution-card">
                    <article class="interc-dictamen-compact">
                        <div class="interc-dictamen-compact__head">
                            <div class="interc-dictamen-compact__icon" aria-hidden="true">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div class="interc-dictamen-compact__content">
                                <div class="interc-dictamen-compact__kicker">
                                    <span>Resoluci&oacute;n administrativa</span>
                                    <span>Documento '.$idDocumentoSeguro.'</span>
                                </div>
                                <h4 class="interc-dictamen-compact__title">'.$asuntoDictamen.'</h4>
                                <div class="interc-dictamen-compact__meta">
                                    <span><strong>Responsable:</strong> '.$nombreAutorSeguro.'</span>
                                    <span><strong>Emitido:</strong> '.$fechaDictamenSeguro.'</span>
                                    <span><strong>Basado en:</strong> '.$textoCantidadMensajes.'</span>
                                </div>
                            </div>
                            <img class="interc-dictamen-compact__avatar" src="'.$urlAutorSeguro.'" alt="Foto de '.$nombreAutorSeguro.'">
                        </div>
                        <p class="interc-dictamen-compact__excerpt">'.$resumenDictamen.'</p>
                        <div class="interc-dictamen-actions">
                            <span class="interc-dictamen-status-badge interc-dictamen-status-badge--'.$estadoDictamenClase.'" title="Estado administrativo del dictamen">Estado: '.$estadoDictamenSeguro.'</span>
                            <button type="button" class="interc-dictamen-action-btn" aria-expanded="false" aria-controls="'.$idPanelResolucion.'" data-text-open="Ver resoluci&oacute;n" data-text-close="Ocultar resoluci&oacute;n" onclick="event.stopPropagation();cargarPanelDictamenInterConsulta('.$codDictamen.', \''.$idPanelResolucion.'\', \''.$idPanelMensajes.'\', this);">
                                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                                <span data-label>Ver resoluci&oacute;n</span>
                            </button>
                            <button type="button" class="interc-dictamen-action-btn" aria-expanded="false" aria-controls="'.$idPanelMensajes.'" data-text-open="Ver mensajes relacionados" data-text-close="Ocultar mensajes" onclick="event.stopPropagation();cargarPanelDictamenInterConsulta('.$codDictamen.', \''.$idPanelResolucion.'\', \''.$idPanelMensajes.'\', this);">
                                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                                <span data-label>Ver mensajes relacionados</span>
                            </button>
                        </div>
                    </article>';
                
                $pagina .= '<div id="'.$idPanelResolucion.'" class="collapse interc-dictamen-body interc-dictamen-body--document">
                    <div class="interc-dictamen-layout interc-dictamen-layout--document">
                        <div class="interc-dictamen-preview-pane" data-role="dictamen-resolucion"><div class="interconsulta-flow-state">La resoluci&oacute;n se cargar&aacute; al abrir esta secci&oacute;n.</div></div>
                    </div>
                </div>';

                $pagina .= '<div id="'.$idPanelMensajes.'" class="collapse interc-dictamen-body interc-dictamen-body--messages" data-total-mensajes="'.$cantidadMensajesDictamen.'">
                    <div class="interc-dictamen-chat-pane" data-role="dictamen-chat-panel"><div class="interconsulta-flow-state">Los mensajes relacionados se cargar&aacute;n al abrir esta secci&oacute;n.</div></div>
                </div></section>';
            }

            $paginaMensajes= obtenerVistaTarjetaInterConsuta(array(
                    'cod_interConsultaFK' => $valueInter['cod_interConsulta'],
                    'cod_usuarioFK' => $filtros['cod_usuarioFK'],
                    'sin_dictamen' => TRUE
                ), $limiteMensajes, 0, $usuariosMensaje);

            // Obtiene los mensajes programados
            $registrosMens2= obtenerMensaje(array(
                'fecha_creacion' => "> '".$fechaActual->format('Y-m-d H:i:s')."'",
                "cod_interConsultaFK" => $valueInter["cod_interConsulta"],
                "estado" => 'activo'
            ));

            $mesActualProgramados = '';
            $diaActualProgramados = '';
            foreach ($registrosMens2 as $valueMens) {
                $textoProgramado = 'Recordatorio heredado '.($valueMens['nombre_persona'] ? 'de '.$valueMens['nombre_persona'] : 'del sistema').' para el '.$valueMens['fecha_creacion'];
                $paginaMensajes .= obtenerSeparadoresCronologiaInterconsulta($valueMens['fecha_creacion'], $mesActualProgramados, $diaActualProgramados);
                $paginaMensajes .= obtenerVistaEventoSistemaInterconsulta($textoProgramado, $valueMens['fecha_creacion'], 'fa-clock');
            }
            
            $pagina .= '<div id="contenedorMensajesInterConsulta" class="collapse show" data-total-mensajes="'.$totalMensajesSinDictamen.'">
                <div data-role="dictamen-chat-panel">';

            if ($totalMensajesSinDictamen > $limiteMensajes) {
                $pagina .= obtenerBotonMasMensajesInterconsulta($limiteMensajes, "");
            }
            $pagina .= $paginaMensajes. '</div></div>';
            $pagina .= obtenerVistaSeguimientosProgramadosInterConsulta($valueInter['cod_interConsulta'], $filtros['cod_usuarioFK']);

            $totalCantMensaje += obtenerCantidadMensajesInterConsulta($valueInter['cod_interConsulta']);
        }   

        echo json_encode(array(
            "1" => "exito",
            "2" => $pagina,
            "3" => $filtros['cod_ventaFK'],
            "4" => $valueInter,
            "5" => $totalCantMensaje,
            "6" => $mencionesElemento,
            "7" => $paginaOpciones,
            "8" => seguimientoPacienteValorSalidaJson($normalizacion_seguimiento)
        ));
    }

    function interConsultaFlujoGastoMonto($monto) {
        $numero= intval(preg_replace('/[^\d-]/', '', (string)$monto));
        return number_format($numero, 0, ',', '.')." Gs.";
    }

    function interConsultaFlujoGastoFecha($fecha) {
        if (function_exists('flujoGastoFechaCorta')) {
            return flujoGastoFechaCorta($fecha);
        }
        $fechaObj= DateTime::createFromFormat('!Y-m-d', substr((string)$fecha, 0, 10));
        return ($fechaObj ? $fechaObj->format('d/m/Y') : flujoGastoTextoSeguro($fecha));
    }

    function interConsultaFlujoGastoEstadoPagoUnico($gasto) {
        $estado= strtolower(trim((string)(isset($gasto['estado']) ? $gasto['estado'] : '')));
        $fechaObj= DateTime::createFromFormat('!Y-m-d', substr((string)(isset($gasto['fecha']) ? $gasto['fecha'] : ''), 0, 10));
        $hoy= new DateTime('today');

        if ($estado == 'activo') {
            return array(
                'tipo' => 'liquidado',
                'texto' => 'Liquidado',
                'detalle' => 'Pago unico',
                'icono' => '&#10003;',
            );
        }
        if ($estado == 'rechazado' || $estado == 'inactivo') {
            return array(
                'tipo' => 'observado',
                'texto' => ucfirst($estado),
                'detalle' => 'Pago unico',
                'icono' => '!',
            );
        }
        if ($estado == 'solicitado' || $estado == 'pendiente') {
            return array(
                'tipo' => ($fechaObj && $fechaObj <= $hoy ? 'vencido' : 'pendiente'),
                'texto' => ($fechaObj && $fechaObj <= $hoy ? 'Vencido' : 'Pendiente'),
                'detalle' => 'Pago unico',
                'icono' => '&#9203;',
            );
        }

        return array(
            'tipo' => 'sin-datos',
            'texto' => 'Sin datos',
            'detalle' => 'Pago unico',
            'icono' => '?',
        );
    }

    function interConsultaFlujoGastoEstadoCuotas($gastosSerie) {
        $resumen= obtenerResumenCuotasProgramadas($gastosSerie);
        $avance= intval($resumen['pagadas'])."/".intval($resumen['total']);

        if (intval($resumen['total']) <= 0) {
            return array(
                'tipo' => 'sin-datos',
                'texto' => 'Sin cuotas',
                'detalle' => '',
                'icono' => '?',
                'resumen' => $resumen,
            );
        }
        if (intval($resumen['pagadas']) >= intval($resumen['total'])) {
            return array(
                'tipo' => 'liquidado',
                'texto' => 'Liquidado',
                'detalle' => $avance,
                'icono' => '&#10003;',
                'resumen' => $resumen,
            );
        }
        if (intval($resumen['vencidas']) > 0) {
            return array(
                'tipo' => 'vencido',
                'texto' => 'Cuota vencida',
                'detalle' => $avance,
                'icono' => '!',
                'resumen' => $resumen,
            );
        }
        return array(
            'tipo' => 'en-cuotas',
            'texto' => 'En cuotas',
            'detalle' => $avance,
            'icono' => '&#9203;',
            'resumen' => $resumen,
        );
    }

    function interConsultaFlujoGastoFicha($gasto, $estadoVisual, $gastosSerie= array()) {
        $idGasto= isset($gasto['idgastos']) ? $gasto['idgastos'] : '';
        $descripcion= trim((string)(isset($gasto['descripcion']) ? $gasto['descripcion'] : ''));
        if ($descripcion == "" && isset($gasto['motivo'])) {
            $descripcion= trim((string)$gasto['motivo']);
        }
        if ($descripcion == "") {
            $descripcion= "Gasto #".$idGasto;
        }

        $total= intval(isset($gasto['monto']) ? $gasto['monto'] : 0);
        if (count($gastosSerie) > 0) {
            $total= 0;
            foreach ($gastosSerie as $gastoSerie) {
                $total += intval(isset($gastoSerie['monto']) ? $gastoSerie['monto'] : 0);
            }
        }

        $detalleEstado= trim((string)(isset($estadoVisual['detalle']) ? $estadoVisual['detalle'] : ''));
        $textoBadge= flujoGastoTextoSeguro($estadoVisual['texto']);
        if ($detalleEstado != "") {
            $textoBadge .= " <span>&middot;</span> ".flujoGastoTextoSeguro($detalleEstado);
        }

        $metaExtra= "";
        if (isset($estadoVisual['resumen']) && !empty($estadoVisual['resumen']['proximo'])) {
            $metaExtra= "<span>Prox. venc.: ".flujoGastoTextoSeguro($estadoVisual['resumen']['proximo'])."</span>";
        } else if (!empty($gasto['fecha'])) {
            $metaExtra= "<span>Fecha: ".interConsultaFlujoGastoFecha($gasto['fecha'])."</span>";
        }

        $titulo= flujoGastoTextoSeguro($estadoVisual['texto'].($detalleEstado != "" ? " - ".$detalleEstado : ""));

        return '<button type="button" class="btn-menu-extracto interconsulta-flow-expense interconsulta-flow-expense--'.flujoGastoTextoSeguro($estadoVisual['tipo']).' w-100" data-id="'.flujoGastoTextoSeguro($idGasto).'" title="'.$titulo.'" onclick="mostrarExtractoGasto('.intval($idGasto).')">
            <span class="interconsulta-flow-expense__top">
                <span class="interconsulta-flow-expense__title">'.flujoGastoTextoSeguro($descripcion).'</span>
                <span class="interconsulta-flow-expense__badge"><span class="interconsulta-flow-expense__icon">'.$estadoVisual['icono'].'</span>'.$textoBadge.'</span>
            </span>
            <span class="interconsulta-flow-expense__meta">
                <span>Total: '.interConsultaFlujoGastoMonto($total).'</span>
                '.$metaExtra.'
            </span>
        </button>';
    }

    function obtenerVistaFlujoGastosInterConsulta($cod_interConsulta) {
        if (empty($cod_interConsulta)) {
            return '<div class="text-secondary" style="padding: 8px;">Sin gastos asociados.</div>';
        }

        $gastosElemento= "";
        $registrosGastos = buscarGasto("","","",'','','Egreso','','','true','', $cod_interConsulta, '', '','','');
        foreach ($registrosGastos as $key => $gast) {
            $gasto= $gast;
            $gastosSerie= array();
            if (!empty($registrosGastos[$key]['mostrado'])) {continue;}

            $registrosGastos[$key]['mostrado'] = true;

            if (strtolower(trim((string)$gasto['modalidad'])) == 'credito') {
                // Evita mostrar cuotas repetidas y conserva el gasto principal.
                $gastos_asociados= obtenerGastosAsociados($gasto["idgastos"]);
                if (empty($gastos_asociados)) {continue;}

                $gastosSerie= function_exists('filtrarGastosCuotasProgramadas') ? filtrarGastosCuotasProgramadas($gastos_asociados) : $gastos_asociados;
                if (count($gastosSerie) < 1) {
                    $gastosSerie= $gastos_asociados;
                }

                foreach ($gastos_asociados as $value) {
                    foreach ($registrosGastos as &$value2) {
                        if ($value['idgastos'] == $value2['idgastos']) {
                            $value2['mostrado'] = true;
                        }
                    }
                    unset($value2);
                }

                $gasto= $gastosSerie[0];
                $estadoVisual= interConsultaFlujoGastoEstadoCuotas($gastosSerie);
            } else {
                $estadoVisual= interConsultaFlujoGastoEstadoPagoUnico($gasto);
            }

            $gastosElemento.= interConsultaFlujoGastoFicha($gasto, $estadoVisual, $gastosSerie);
        }

        if (empty($gastosElemento)) {
            return '<div class="text-secondary" style="padding: 8px;">Sin gastos asociados.</div>';
        }
        
        return $gastosElemento;
    }

    function buscarVistaPacienteConInterConsulta($filtros= array(), $limite= 0) {
        $registros= buscarPacienteConInterConsulta($filtros, $limite);

        $pagina= "";
        foreach ($registros as $key => $value) {
            $interConsultas= "";
            
            // Obtiene las interConsultas asociadas a este paciente
            $registrosInterconsulta= obtenerInterConsulta(array(
                "cod_ventaFK" => $value['cod_cliente']
            ), 5);

            $cant_mensajes_no_leidos= 0;
            foreach ($registrosInterconsulta as $valueInter) {
                $color="#8bc34a;";
                if ($color == "#8bc34a;") {
                    if ($valueInter['estado'] == 'proceso') {
                        $color=" #e53935; ";	
                    } else if ($valueInter['estado'] == 'pendiente') {
                        $color=" #e1c247; ";
                    }
                }
                $interConsultas .= '<li style="
                    background-color:'.$color.';
                    margin-bottom:4px;
                    padding:5px 10px;
                    border-radius:4px;
                    font-size:13px;
                ">'.$valueInter['asunto'].'</li>';

                $cant_mensajes_no_leidos += intval($valueInter['cantMensajesNoLeidos']);
            }

            // Coloca un elemento con la cantidad de mensajes pendientes
            $indicadorElemento= "" ;
            if ($cant_mensajes_no_leidos > 0) {
                $indicadorElemento= '<div style="
                    position: absolute;
                    top: -10px;
                    right: -10px;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    background:  #e53935;  /* Rojo */
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 14px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                ">
                    '.$cant_mensajes_no_leidos.'
                </div>';
                $indicadorElemento= "" ;
            }

            $pagina.= "<div class='tarjeta-paciente' onclick='obtenerDatosInterConsulta(this)' style='
                position: relative; /* Necesario para posicionar el círculo */
                border: 1px solid #ddd;
                border-radius: 8px;
                margin: 10px 0;
                height: auto;
                padding: 15px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                font-family: Arial, sans-serif;
                '>
                $indicadorElemento
                <h3 style='
                    margin-top:0;
                    margin-bottom:10px;
                    font-size: 16px;
                    color: #333;
                '>DATOS PACIENTE</h3>
                
                <p><strong>Nombre:</strong> ".$value['paciente']."</p>
                <p><strong>CI:</strong> ".$value['ci_cliente']."</p>

                <div style='
                    margin-top: 10px;
                    padding-top: 10px;
                '>
                    <strong>Ultimas conversaciones:</strong>
                    $interConsultas
                </div>
                <div style='display: none;'>
                    <span id='td_datos_1'>".$value['cod_cliente']."</span>
                    <span id='td_datos_2'>".$value['paciente']."</span>
                </div>
                </div>";
        }

        echo json_encode(array("1" => "exito", "2" => $pagina));
    }
    function buscarPacienteConInterConsulta($filtros= array(), $limite= 0) {
        $mysqli=conectar_al_servidor();

        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            
            $sqlFiltro .= " AND ";

            switch ($key) {
                case 'paciente':
		            $sqlFiltro=" concat(cl.ci_cliente,' ',cl.rut_cliente ,' ',p.nombre_persona )   like '%".$value."%' ";
                    break;
                case 'estado':
                    $sqlFiltro .= "cl.estado = '$value'";
                    break;
                case 'cod_usuarioFK':
                    $sqlFiltro .= "EXISTS (SELECT 1 FROM interconsulta ic INNER JOIN mensaje mj ON ic.cod_interConsulta = mj.cod_interConsultaFK
                                    INNER JOIN menciones mc ON mc.cod_mensajeFK = mj.cod_mensaje
                                    WHERE ic.cod_ventaFK = cl.cod_cliente AND mc.cod_usuarioFK = $value)";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "cl.$key = $value";
                    } else {
                        $sqlFiltro .= "cl.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "Select  p.nombre_persona as paciente,cl.ci_cliente,cl.cod_cliente
		from cliente cl
		inner join persona p on cod_cliente=cod_persona
		  where cl.estado = 'Activo' $sqlFiltro $limite";

        $stmt = $mysqli->prepare($sql);
        if ( ! $stmt->execute()) {
            echo "Error";
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

    function obtenerVistaTarjetaInterConsuta($filtros= array(), $limite= 0, $offset= 0, $usuarios= null) {
        $paginaMensajes= "";

        if (!is_array($usuarios)) {
            $usuarios= buscarUsuarios();
        }

        // Reconstruye el limite si es necesario
        if ($offset != 0){
            $limite= "$limite OFFSET $offset";
        }

        // Obtiene todos los mensajes de la interConsulta
        $fechaActual= new DateTime();
        $regMensaje= obtenerMensaje(array(
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
                "cod_interConsultaFK" => $filtros["cod_interConsultaFK"],
                "cod_dictamenFK" => isset($filtros['cod_dictamenFK']) ? $filtros['cod_dictamenFK'] : NULL,
                "sin_dictamen" => isset($filtros['sin_dictamen']) ? $filtros['sin_dictamen'] : NULL,
            ), $limite);
        $mesActualTimeline = '';
        $diaActualTimeline = '';
        foreach ($regMensaje as $key => $valueMens) {
            $paginaMensajes .= obtenerSeparadoresCronologiaInterconsulta($valueMens['fecha_creacion'], $mesActualTimeline, $diaActualTimeline);
            $posicion= 'flex-start';
            $colorTarjeta="#e53935";
            
            if ($filtros['cod_usuarioFK'] == $valueMens['cod_usuarioFK']) {
                $posicion= 'flex-end';
                $colorTarjeta="#8bc34a";
            }

            $contenidoMensaje= escaparHtmlInterconsulta($valueMens['contenido']);
            // Transforma las menciones con el mapa de usuarios cargado una sola vez por solicitud.
            foreach ($usuarios as $valueUsu) {
                $codUsuarioMencion= intval($valueUsu['cod_usuario']);
                $nombreUsuarioMencion= escaparHtmlInterconsulta($valueUsu['nombre_persona']);
                $contenidoMensaje= str_replace(
                    '@{'.$codUsuarioMencion.'}',
                    '<b class="menciones-mensaje" id="'.$codUsuarioMencion.'" title="Mencion a '.$nombreUsuarioMencion.'">@'.$nombreUsuarioMencion.'</b>',
                    $contenidoMensaje
                );
            }
            $contenidoMensaje = nl2br($contenidoMensaje, false);

            $miniatura_imagen= "";
            if ($valueMens['url']) {
                $urlAdjunto= escaparHtmlInterconsulta($valueMens['url']);
                $extensionAdjunto= escaparHtmlInterconsulta(obtenerExtensionUrlInterconsulta($valueMens['url']));
                $esImagenAdjunto= esUrlImagenInterconsulta($valueMens['url']);
                $urlMiniaturaAdjunto= $esImagenAdjunto ? $urlAdjunto : "/GoodVentaAsisCap/iconos/informedevolucion.png";
                $claseDocumentoAdjunto= $esImagenAdjunto ? "" : " imgFotoProductoDocumento";
                $miniatura_imagen= '<button type="button" class="interconsulta-message-attachment" onclick="vercerrarcargadefotos(\'fotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'\', false)" title="Ver adjunto">
                    <span id="imgfotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'" class="imgFotoProducto'.$claseDocumentoAdjunto.'" data-adjunto-url="'.$urlAdjunto.'" data-adjunto-ext="'.$extensionAdjunto.'" style="background-image: url('.$urlMiniaturaAdjunto.');"></span>
                    <small>Adjunto</small>
                </button>';
            }
            
            $contenidoPlano= mb_strtolower(strip_tags($contenidoMensaje));
            $esEventoSistema= (!$valueMens['cod_usuarioFK'] || $valueMens['cod_usuarioFK'] == "NULL")
                || strpos($contenidoPlano, ' modifico') !== false
                || strpos($contenidoPlano, 'modifico') !== false
                || strpos($contenidoPlano, 'decidio') !== false
                || strpos($contenidoPlano, 'aprobo') !== false
                || strpos($contenidoPlano, 'rechazo') !== false
                || strpos($contenidoPlano, 'creo un nuevo movimiento') !== false
                || strpos($contenidoPlano, 'fueron unidas') !== false
                || strpos($contenidoPlano, 'solicito el acceso') !== false;

            if ($esEventoSistema) {
                $paginaMensajes .= obtenerVistaEventoSistemaInterconsulta($contenidoMensaje, $valueMens['fecha_creacion']);
            } else {
                $claseMensajePropio= ($posicion == 'flex-end') ? ' interconsulta-message-row--own' : '';
                $fechaDiaMensaje = substr($valueMens['fecha_creacion'], 0, 10);
                $codigoMensaje= intval($valueMens['cod_mensaje']);
                $nombreAutorSeguro= escaparHtmlInterconsulta($valueMens['nombre_persona']);
                $urlAutorSeguro= escaparHtmlInterconsulta($valueMens['url_usuario'] == null ? "/GoodVentaAsisCap/iconos/user.png" : $valueMens['url_usuario']);
                $fechaMensajeSeguro= escaparHtmlInterconsulta($valueMens['fecha_creacion']);
                $respuestaCitada= '';
                $codigoRespuesta= isset($valueMens['cod_mensaje_respuestaFK']) ? intval($valueMens['cod_mensaje_respuestaFK']) : 0;
                if ($codigoRespuesta > 0) {
                    $respuestaDisponible= isset($valueMens['respuesta_cod_mensaje'])
                        && intval($valueMens['respuesta_cod_mensaje']) === $codigoRespuesta
                        && isset($valueMens['respuesta_estado'])
                        && $valueMens['respuesta_estado'] === 'activo'
                        && intval($valueMens['respuesta_cod_interConsultaFK']) === intval($filtros['cod_interConsultaFK']);
                    if ($respuestaDisponible) {
                        $textoRespuesta= isset($valueMens['respuesta_contenido']) ? (string)$valueMens['respuesta_contenido'] : '';
                        foreach ($usuarios as $usuarioRespuesta) {
                            $textoRespuesta= str_replace(
                                '@{'.intval($usuarioRespuesta['cod_usuario']).'}',
                                '@'.(string)$usuarioRespuesta['nombre_persona'],
                                $textoRespuesta
                            );
                        }
                        $textoRespuesta= limitarTextoListadoInterConsulta($textoRespuesta, 150);
                        $autorRespuesta= !empty($valueMens['respuesta_nombre_persona']) ? $valueMens['respuesta_nombre_persona'] : 'Participante del hilo';
                        $fechaRespuesta= !empty($valueMens['respuesta_fecha_creacion']) && strtotime($valueMens['respuesta_fecha_creacion'])
                            ? date('d/m/Y H:i', strtotime($valueMens['respuesta_fecha_creacion']))
                            : '';
                        $respuestaCitada= '<button type="button" class="interconsulta-message-quote" data-action="ir-mensaje-citado" data-cod-mensaje="'.$codigoRespuesta.'" title="Ir al mensaje original">'
                            .'<span class="interconsulta-message-quote__author"><i class="fa-solid fa-reply" aria-hidden="true"></i>'.escaparHtmlInterconsulta($autorRespuesta).'</span>'
                            .'<span class="interconsulta-message-quote__text">'.escaparHtmlInterconsulta($textoRespuesta != '' ? $textoRespuesta : 'Mensaje sin texto').'</span>'
                            .($fechaRespuesta != '' ? '<time>'.escaparHtmlInterconsulta($fechaRespuesta).'</time>' : '')
                            .'</button>';
                    } else {
                        $respuestaCitada= '<div class="interconsulta-message-quote interconsulta-message-quote--unavailable">'
                            .'<span class="interconsulta-message-quote__author"><i class="fa-solid fa-reply" aria-hidden="true"></i>Mensaje original no disponible</span>'
                            .'</div>';
                    }
                }
                $paginaMensajes .= '<div id="mensajeInterConsulta-'.$codigoMensaje.'" class="interconsulta-message-row'.$claseMensajePropio.'" data-fecha="'.escaparHtmlInterconsulta($fechaDiaMensaje).'" data-cod-mensaje="'.$codigoMensaje.'">
                    <article class="interconsulta-message-card">
                        <header class="interconsulta-message-header">
                            <div class="interconsulta-message-author">
                                <img src="'.$urlAutorSeguro.'" alt="Foto de '.$nombreAutorSeguro.'">
                                <div>
                                    <strong>'.$nombreAutorSeguro.'</strong>
                                    <span>Participante del hilo</span>
                                </div>
                            </div>
                            <div class="interconsulta-message-header__actions">
                                <time>'.$fechaMensajeSeguro.'</time>
                                <button type="button" class="interconsulta-message-reply" data-action="responder-mensaje" data-cod-mensaje="'.$codigoMensaje.'" title="Responder citando este mensaje" aria-label="Responder citando este mensaje">
                                    <i class="fa-solid fa-reply" aria-hidden="true"></i>
                                </button>
                            </div>
                        </header>
                        <div class="interconsulta-message-body">
                            '.$respuestaCitada.'
                            '.$miniatura_imagen.'
                            <p>'.$contenidoMensaje.'</p>
                        </div>
                    </article>
                </div>';
            }
        }

        return $paginaMensajes;
    }

    function obtenerConteosCategoriasInterConsulta($filtros= array()) {
        $conteos = array();
        foreach (array_keys(obtenerCategoriasHilosInterConsulta()) as $categoria) {
            $filtrosCategoria = $filtros;
            $filtrosCategoria['categoria_principal'] = $categoria;
            $conteos[$categoria] = obtenerCantidadInterConsulta($filtrosCategoria);
        }
        return $conteos;
    }

    function obtenerVistaEstadoVacioHilosInterConsulta($categoria) {
        $nombreCategoria = obtenerNombreCategoriaHilo($categoria);
        return '<div class="hilos-empty-state" role="status">
            <strong>No se encontraron hilos de '.htmlspecialchars($nombreCategoria, ENT_QUOTES, 'UTF-8').' con los filtros seleccionados.</strong>
            <span>Puede limpiar los filtros o crear un nuevo hilo dentro de la categoria activa.</span>
            <div class="hilos-empty-state__actions">
                <button type="button" class="hilos-empty-state__button hilos-empty-state__button--secondary" onclick="limpiarFiltrosInterConsulta()">Limpiar filtros</button>
                <button type="button" class="hilos-empty-state__button hilos-empty-state__button--primary" onclick="limpiarcamposInterconsulta();verCerrarVentanaInterConsulta(true, \'divListadoInterConsulta\');">Nuevo hilo</button>
            </div>
        </div>';
    }

    function renderAtributosAccionSeguimientoInterConsulta($accion= "", $datos= array()) {
        $accion = trim((string)$accion);
        if ($accion == "") {
            return "";
        }

        $atributos = ' data-hilo-action="'.htmlspecialchars($accion, ENT_QUOTES, 'UTF-8').'"';
        foreach ($datos as $clave => $valor) {
            $clave = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$clave);
            if ($clave == "") {
                continue;
            }
            $atributos .= ' data-'.$clave.'="'.htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8').'"';
        }
        return $atributos;
    }

    function renderResumenSeguimientoInterConsulta($clase, $principal, $detalle= "", $titulo= "", $accion= "", $datosAccion= array()) {
        $tituloFinal = $titulo != "" ? $titulo : trim($principal." ".$detalle);
        $tituloSeguro = htmlspecialchars($tituloFinal, ENT_QUOTES, 'UTF-8');
        $principalSeguro = htmlspecialchars($principal, ENT_QUOTES, 'UTF-8');
        $atributosInteraccion = 'title="'.$tituloSeguro.'" tabindex="0" role="button"';
        if (trim((string)$accion) != "") {
            $atributosInteraccion .= renderAtributosAccionSeguimientoInterConsulta($accion, $datosAccion);
        } else {
            $atributosInteraccion .= ' data-hilo-alert="1" data-hilo-alert-title="'.$principalSeguro.'" data-hilo-alert-detail="'.$tituloSeguro.'"';
        }
        return '<div class="interconsulta-status-pill interconsulta-status-pill--'.$clase.'" '.$atributosInteraccion.'>'
            .'<strong>'.htmlspecialchars($principal, ENT_QUOTES, 'UTF-8').'</strong>'
            .($detalle != "" ? '<span>'.htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8').'</span>' : '')
            .'</div>';
    }

    function obtenerInicialesInterConsulta($nombre, $fallback= "AG") {
        $texto = trim(preg_replace('/\s+/', ' ', strip_tags((string)$nombre)));
        if ($texto == "") {
            return $fallback;
        }
        $partes = preg_split('/\s+/', $texto);
        $iniciales = "";
        foreach ($partes as $parte) {
            if ($parte == "") {
                continue;
            }
            $iniciales .= mb_substr($parte, 0, 1, 'UTF-8');
            if (mb_strlen($iniciales, 'UTF-8') >= 2) {
                break;
            }
        }
        return mb_strtoupper($iniciales != "" ? $iniciales : $fallback, 'UTF-8');
    }

    function renderResumenCitaSeguimientoInterConsulta($clase, $fechaHora, $estado, $usuarioAgenda= "", $urlUsuarioAgenda= "", $titulo= "", $idAgenda= "", $fechaAgenda= "") {
        $fechaHora = trim((string)$fechaHora);
        $estado = trim((string)$estado);
        $usuarioAgenda = limitarTextoListadoInterConsulta($usuarioAgenda, 28);
        $urlUsuarioAgenda = trim((string)$urlUsuarioAgenda);
        $idAgenda = trim((string)$idAgenda);
        $fechaAgenda = trim((string)$fechaAgenda);
        $detalleLinea = $estado != "" ? $estado : "Agendado";
        if ($usuarioAgenda != "") {
            $detalleLinea .= " · ".$usuarioAgenda;
        }
        $tituloFinal = $titulo != "" ? $titulo : trim($fechaHora." ".$detalleLinea);
        $tituloSeguro = htmlspecialchars($tituloFinal, ENT_QUOTES, 'UTF-8');
        $fechaHoraSeguro = htmlspecialchars($fechaHora != "" ? $fechaHora : "Cita agendada", ENT_QUOTES, 'UTF-8');
        $detalleSeguro = htmlspecialchars($detalleLinea, ENT_QUOTES, 'UTF-8');
        $avatarTitulo = $usuarioAgenda != "" ? "Agendado por ".$usuarioAgenda : "Agendamiento";

        $avatarHtml = "";
        if ($urlUsuarioAgenda != "") {
            $avatarHtml = '<img src="'.htmlspecialchars($urlUsuarioAgenda, ENT_QUOTES, 'UTF-8').'" alt="Foto de '.htmlspecialchars($avatarTitulo, ENT_QUOTES, 'UTF-8').'">';
        } else {
            $avatarHtml = '<span>'.htmlspecialchars(obtenerInicialesInterConsulta($usuarioAgenda, "AG"), ENT_QUOTES, 'UTF-8').'</span>';
        }

        $atributosInteraccion = 'title="'.$tituloSeguro.'" tabindex="0" role="button"';
        if ($idAgenda != "") {
            $atributosInteraccion .= renderAtributosAccionSeguimientoInterConsulta('abrir_cita', array(
                'agenda-id' => $idAgenda,
                'agenda-fecha' => $fechaAgenda
            ));
        } else {
            $atributosInteraccion .= ' data-hilo-alert="1" data-hilo-alert-title="'.$fechaHoraSeguro.'" data-hilo-alert-detail="'.$tituloSeguro.'"';
        }

        return '<div class="interconsulta-appointment-summary interconsulta-appointment-summary--'.$clase.'" '.$atributosInteraccion.'>'
            .'<span class="interconsulta-appointment-summary__avatar" title="'.htmlspecialchars($avatarTitulo, ENT_QUOTES, 'UTF-8').'">'
                .$avatarHtml
                .'<span class="interconsulta-appointment-summary__calendar"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span>'
            .'</span>'
            .'<span class="interconsulta-appointment-summary__body">'
                .'<strong>'.$fechaHoraSeguro.'</strong>'
                .'<span>'.$detalleSeguro.'</span>'
            .'</span>'
            .'</div>';
    }

    function renderResumenNoAplicaInterConsulta($detalle= "Hilo administrativo") {
        $titulo = "Este dato no aplica para hilos sin paciente vinculado.";
        return '<div class="interconsulta-status-pill interconsulta-status-pill--not-applicable" title="'.htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8').'">'
            .'<strong>-</strong>'
            .'<span>'.htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8').'</span>'
            .'</div>';
    }

    function obtenerVentasSeguimientoPacienteInterConsulta($cod_interConsulta, $cod_usuarioFK) {
        $cod_interConsulta = seguimientoPacienteEntero($cod_interConsulta);
        if ($cod_interConsulta <= 0) {
            return array("ok" => false, "mensaje" => "No se recibio el hilo a consultar.");
        }

        $registrosInterc = obtenerInterConsulta(array(
            "cod_interConsulta" => $cod_interConsulta,
            "cod_usuarioFK" => $cod_usuarioFK
        ), 1);

        if (count($registrosInterc) == 0) {
            return array("ok" => false, "mensaje" => "Usted no tiene acceso a esta conversacion.");
        }

        $hilo = $registrosInterc[0];
        $codCliente = isset($hilo["cod_clienteFK"]) ? seguimientoPacienteEntero($hilo["cod_clienteFK"]) : 0;
        $cedula = isset($hilo["cedula"]) ? trim((string)$hilo["cedula"]) : "";
        $cedulaNormalizada = seguimientoPacienteNormalizarCedula($cedula);
        $nombrePaciente = isset($hilo["nombre_persona"]) ? trim((string)$hilo["nombre_persona"]) : "";

        if ($codCliente <= 0 && $cedulaNormalizada == "") {
            return array(
                "ok" => true,
                "mensaje" => "El hilo no tiene paciente vinculado.",
                "paciente" => array("cod_cliente" => "", "nombre" => "", "cedula" => ""),
                "ventas" => array()
            );
        }

        $mysqli = conectar_al_servidor();
        $codInterConsultaSql = (int)$cod_interConsulta;
        $cedulaSql = $mysqli->real_escape_string($cedulaNormalizada);
        $exprCedulaVenta = seguimientoPacienteSqlNormalizar("cl_sel.ci_cliente");
        $condicionCreditoActivo = condicionCreditoActivoHiloInterConsulta("cr_sel");
        $saldoPendienteCredito = saldoPendienteCreditoHiloInterConsulta("cr_sel");
        $condicionCreditoPendiente = "(".$saldoPendienteCredito." > 0)";
        $subVentasVinculadas = "(SELECT ipv_sel.cod_ventaFK
                FROM interconsulta_paciente_venta ipv_sel
                WHERE ipv_sel.cod_interConsultaFK = ".$codInterConsultaSql."
                    AND ipv_sel.estado = 'activo'
            UNION
            SELECT ic_sel.cod_ventaFK
                FROM interconsulta ic_sel
                WHERE ic_sel.cod_interConsulta = ".$codInterConsultaSql."
                    AND IFNULL(ic_sel.cod_ventaFK,0) > 0)";

        $filtroPaciente = "";
        if ($cedulaNormalizada != "") {
            $filtroPaciente = $exprCedulaVenta." = '".$cedulaSql."'";
        } else if ($codCliente > 0) {
            $filtroPaciente = "vt_sel.cod_clienteFK = ".(int)$codCliente;
        }

        $wherePaciente = "vt_sel.cod_venta IN ".$subVentasVinculadas;
        if ($filtroPaciente != "") {
            $wherePaciente .= " OR (".$filtroPaciente." AND EXISTS (
                SELECT 1
                FROM credito cr_pend
                WHERE cr_pend.cod_venta = vt_sel.cod_venta
                    AND ".condicionCreditoActivoHiloInterConsulta("cr_pend")."
                    AND ".saldoPendienteCreditoHiloInterConsulta("cr_pend")." > 0
                LIMIT 1
            ))";
        }

        $sql = "SELECT vt_sel.cod_venta,
                vt_sel.num_factura,
                vt_sel.fecha_venta,
                vt_sel.TipoVenta,
                (IFNULL(vt_sel.total_venta,0)-IFNULL(vt_sel.descuento,0)) AS total_venta,
                vt_sel.cod_clienteFK,
                IFNULL(cl_sel.ci_cliente,'') AS ci_cliente,
                IFNULL(p_sel.nombre_persona,'') AS nombre_paciente,
                IFNULL(l_sel.Nombre,'') AS nombre_local,
                CASE WHEN vt_sel.cod_venta IN ".$subVentasVinculadas." THEN 1 ELSE 0 END AS vinculada_hilo,
                COUNT(DISTINCT CASE WHEN cr_sel.idcredito IS NOT NULL AND ".$condicionCreditoActivo." THEN cr_sel.idcredito ELSE NULL END) AS total_creditos,
                SUM(CASE WHEN cr_sel.idcredito IS NOT NULL AND ".$condicionCreditoActivo." AND ".$condicionCreditoPendiente." THEN 1 ELSE 0 END) AS cuotas_pendientes,
                SUM(CASE WHEN cr_sel.idcredito IS NOT NULL AND ".$condicionCreditoActivo." AND ".$condicionCreditoPendiente." AND cr_sel.fechapago < CURDATE() THEN 1 ELSE 0 END) AS cuotas_vencidas,
                IFNULL(MIN(CASE WHEN cr_sel.idcredito IS NOT NULL AND ".$condicionCreditoActivo." AND ".$condicionCreditoPendiente." THEN cr_sel.fechapago ELSE NULL END),'') AS proxima_cuota,
                IFNULL(SUM(CASE WHEN cr_sel.idcredito IS NOT NULL AND ".$condicionCreditoActivo." AND ".$condicionCreditoPendiente." THEN ".$saldoPendienteCredito." ELSE 0 END),0) AS saldo_pendiente
            FROM venta vt_sel
            INNER JOIN cliente cl_sel ON cl_sel.cod_cliente = vt_sel.cod_clienteFK
            LEFT JOIN persona p_sel ON p_sel.cod_persona = vt_sel.cod_clienteFK
            LEFT JOIN local l_sel ON l_sel.cod_local = vt_sel.cod_local
            LEFT JOIN credito cr_sel ON cr_sel.cod_venta = vt_sel.cod_venta
            WHERE vt_sel.cod_clienteFK <> 7
                AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones cn_sel WHERE cn_sel.cod_venta = vt_sel.cod_venta LIMIT 1),0) = 0
                AND (".$wherePaciente.")
            GROUP BY vt_sel.cod_venta, vt_sel.num_factura, vt_sel.fecha_venta, vt_sel.TipoVenta,
                vt_sel.total_venta, vt_sel.descuento, vt_sel.cod_clienteFK, cl_sel.ci_cliente,
                p_sel.nombre_persona, l_sel.Nombre
            ORDER BY cuotas_vencidas DESC,
                cuotas_pendientes DESC,
                vinculada_hilo DESC,
                proxima_cuota ASC,
                vt_sel.fecha_venta DESC
            LIMIT 30";

        $result = $mysqli->query($sql);
        if (!$result) {
            return array("ok" => false, "mensaje" => "No se pudieron consultar las ventas del paciente.", "sql_error" => mysqli_error($mysqli));
        }

        $ventas = array();
        while ($row = $result->fetch_assoc()) {
            $cuotasPendientes = isset($row["cuotas_pendientes"]) ? (int)$row["cuotas_pendientes"] : 0;
            $cuotasVencidas = isset($row["cuotas_vencidas"]) ? (int)$row["cuotas_vencidas"] : 0;
            $saldoPendiente = isset($row["saldo_pendiente"]) ? (int)$row["saldo_pendiente"] : 0;
            $estado = "Sin cuotas pendientes";
            if ($cuotasVencidas > 0) {
                $estado = $cuotasVencidas." vencida(s)";
            } else if ($cuotasPendientes > 0) {
                $estado = $cuotasPendientes." pendiente(s)";
            }

            $ventas[] = array(
                "cod_venta" => mb_convert_encoding((string)$row["cod_venta"], 'UTF-8', 'ISO-8859-1'),
                "num_factura" => mb_convert_encoding((string)$row["num_factura"], 'UTF-8', 'ISO-8859-1'),
                "fecha_venta" => mb_convert_encoding((string)$row["fecha_venta"], 'UTF-8', 'ISO-8859-1'),
                "tipo_venta" => mb_convert_encoding((string)$row["TipoVenta"], 'UTF-8', 'ISO-8859-1'),
                "total_venta" => mb_convert_encoding((string)$row["total_venta"], 'UTF-8', 'ISO-8859-1'),
                "total_venta_formato" => "Gs. ".number_format((int)$row["total_venta"], 0, ',', '.'),
                "saldo_pendiente" => (string)$saldoPendiente,
                "saldo_pendiente_formato" => "Gs. ".number_format($saldoPendiente, 0, ',', '.'),
                "cuotas_pendientes" => (string)$cuotasPendientes,
                "cuotas_vencidas" => (string)$cuotasVencidas,
                "proxima_cuota" => mb_convert_encoding((string)$row["proxima_cuota"], 'UTF-8', 'ISO-8859-1'),
                "estado" => mb_convert_encoding($estado, 'UTF-8', 'ISO-8859-1'),
                "vinculada_hilo" => (string)((int)$row["vinculada_hilo"]),
                "nombre_local" => mb_convert_encoding((string)$row["nombre_local"], 'UTF-8', 'ISO-8859-1')
            );
        }

        return array(
            "ok" => true,
            "paciente" => array(
                "cod_cliente" => (string)$codCliente,
                "nombre" => $nombrePaciente,
                "cedula" => $cedula
            ),
            "ventas" => $ventas
        );
    }

    function renderBadgeSeguimientoInterConsulta($clase, $texto, $detalle= "") {
        $textoSeguro = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        $detalleFinal = trim((string)$detalle) != "" ? $detalle : $texto;
        $detalleSeguro = htmlspecialchars($detalleFinal, ENT_QUOTES, 'UTF-8');
        return '<span class="interconsulta-follow-badge interconsulta-follow-badge--'.$clase.'" title="'.$detalleSeguro.'" tabindex="0" role="button" data-hilo-alert="1" data-hilo-alert-title="'.$textoSeguro.'" data-hilo-alert-detail="'.$detalleSeguro.'">'.$textoSeguro.'</span>';
    }

    function limitarTextoListadoInterConsulta($texto, $limite= 70) {
        $texto = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string)$texto, ENT_QUOTES, 'UTF-8'))));
        if ($texto == "") {
            return "";
        }
        if (mb_strlen($texto, 'UTF-8') <= $limite) {
            return $texto;
        }
        return rtrim(mb_substr($texto, 0, $limite - 3, 'UTF-8'))."...";
    }

    function renderGestionProgramadaInterConsulta($contenido= "", $usuario= "", $fecha= "", $urlUsuario= "") {
        $contenidoSeguro = limitarTextoListadoInterConsulta($contenido, 64);
        $usuarioSeguro = limitarTextoListadoInterConsulta($usuario, 42);
        $urlUsuario = trim((string)$urlUsuario);
        $fechaTexto = "";
        if ($fecha != "" && strtotime($fecha)) {
            $fechaTexto = date('d/m H:i', strtotime($fecha));
        } else if ($fecha != "") {
            $fechaTexto = $fecha;
        }

        if ($contenidoSeguro == "" && $usuarioSeguro == "" && $fechaTexto == "") {
            return '<div class="interconsulta-management-pill interconsulta-management-pill--empty" title="Este hilo no tiene mensajes programados.">'
                .'<strong>Sin gestion</strong>'
                .'<span>Sin mensaje programado</span>'
                .'</div>';
        }

        $tituloPartes = array();
        if ($contenidoSeguro != "") {
            $tituloPartes[] = $contenidoSeguro;
        }
        if ($usuarioSeguro != "") {
            $tituloPartes[] = 'Usuario: '.$usuarioSeguro;
        }
        if ($fechaTexto != "") {
            $tituloPartes[] = 'Programado: '.$fechaTexto;
        }

        $tituloSeguro = htmlspecialchars(implode('. ', $tituloPartes), ENT_QUOTES, 'UTF-8');
        $usuarioFinal = $usuarioSeguro != "" ? $usuarioSeguro : "Sin usuario";
        $usuarioTitulo = $usuarioSeguro != "" ? "Programado por ".$usuarioSeguro : "Seguimiento programado";
        $avatarHtml = "";
        if ($urlUsuario != "") {
            $avatarHtml = '<img src="'.htmlspecialchars($urlUsuario, ENT_QUOTES, 'UTF-8').'" alt="Foto de '.htmlspecialchars($usuarioTitulo, ENT_QUOTES, 'UTF-8').'">';
        } else {
            $avatarHtml = '<span>'.htmlspecialchars(obtenerInicialesInterConsulta($usuarioSeguro, "GP"), ENT_QUOTES, 'UTF-8').'</span>';
        }

        return '<div class="interconsulta-management-summary" title="'.$tituloSeguro.'" tabindex="0">'
            .'<span class="interconsulta-management-summary__avatar" title="'.htmlspecialchars($usuarioTitulo, ENT_QUOTES, 'UTF-8').'">'
                .$avatarHtml
                .'<span class="interconsulta-management-summary__clock"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>'
            .'</span>'
            .'<span class="interconsulta-management-summary__body">'
                .'<strong>'.htmlspecialchars($contenidoSeguro != "" ? $contenidoSeguro : "Mensaje programado", ENT_QUOTES, 'UTF-8').'</strong>'
                .'<span>'.htmlspecialchars($usuarioFinal, ENT_QUOTES, 'UTF-8').'</span>'
                .($fechaTexto != "" ? '<small>Programado: '.htmlspecialchars($fechaTexto, ENT_QUOTES, 'UTF-8').'</small>' : '')
            .'</span>'
            .'</div>';
    }

    function renderSeguimientoProgramadoInternoInterConsulta($seguimiento) {
        if (!is_array($seguimiento) || empty($seguimiento['id_seguimiento'])) {
            return renderGestionProgramadaInterConsulta();
        }
        $estadoVisual= seguimientoProgramadoEstadoVisual($seguimiento);
        $estadoEtiqueta= seguimientoProgramadoEtiquetaEstado($estadoVisual);
        $motivo= limitarTextoListadoInterConsulta(isset($seguimiento['motivo']) ? $seguimiento['motivo'] : '', 58);
        $responsable= limitarTextoListadoInterConsulta(isset($seguimiento['nombre_responsable']) ? $seguimiento['nombre_responsable'] : '', 42);
        $fechaTexto= !empty($seguimiento['fecha_programada']) && strtotime($seguimiento['fecha_programada'])
            ? date('d/m H:i', strtotime($seguimiento['fecha_programada']))
            : (isset($seguimiento['fecha_programada']) ? $seguimiento['fecha_programada'] : '');
        $urlResponsable= isset($seguimiento['url_responsable']) ? trim((string)$seguimiento['url_responsable']) : '';
        $titulo= $estadoEtiqueta.': '.$motivo.'. Responsable: '.($responsable !== '' ? $responsable : 'Sin responsable').'. Fecha: '.$fechaTexto;
        $avatar= $urlResponsable !== ''
            ? '<img src="'.escaparHtmlInterconsulta($urlResponsable).'" alt="Foto de '.escaparHtmlInterconsulta($responsable).'">'
            : '<span>'.escaparHtmlInterconsulta(obtenerInicialesInterConsulta($responsable, 'SG')).'</span>';

        return '<div class="interconsulta-management-summary interconsulta-management-summary--'.escaparHtmlInterconsulta($estadoVisual).'" title="'.escaparHtmlInterconsulta($titulo).'" tabindex="0">'
            .'<span class="interconsulta-management-summary__avatar">'.$avatar
                .'<span class="interconsulta-management-summary__clock"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span>'
            .'</span>'
            .'<span class="interconsulta-management-summary__body">'
                .'<strong>'.escaparHtmlInterconsulta($motivo !== '' ? $motivo : 'Seguimiento personalizado').'</strong>'
                .'<span>'.escaparHtmlInterconsulta($responsable !== '' ? $responsable : 'Sin responsable').'</span>'
                .'<small>'.escaparHtmlInterconsulta($estadoEtiqueta.': '.$fechaTexto).'</small>'
            .'</span>'
            .'</div>';
    }

    function obtenerActividadDiariaSeguimientoInterConsulta($cod_localFK= "") {
        $mysqli= conectar_al_servidor();
        $codLocal= (is_numeric($cod_localFK) && intval($cod_localFK) > 0) ? intval($cod_localFK) : 0;
        $condicionLocalMensajes= $codLocal > 0 ? " AND ic.cod_localFK = ".$codLocal." " : "";
        $condicionLocalAgenda= $codLocal > 0 ? " AND u_ag.cod_localFK = ".$codLocal." " : "";
        $actividadSeguimientos= "";
        if (seguimientoProgramadoTablaExiste($mysqli, 'interconsulta_seguimiento_programado')) {
            $actividadSeguimientos= "
                UNION ALL
                SELECT sp.cod_usuarioFK_update AS cod_usuario, COUNT(DISTINCT sp.id_seguimiento) AS total
                FROM interconsulta_seguimiento_programado sp
                INNER JOIN interconsulta ic_sp ON ic_sp.cod_interConsulta=sp.cod_interConsultaFK
                WHERE sp.estado='completado'
                    AND sp.cod_usuarioFK_update IS NOT NULL
                    AND sp.fecha_cierre >= CURDATE()
                    AND sp.fecha_cierre < DATE_ADD(CURDATE(), INTERVAL 1 DAY)"
                    .($codLocal > 0 ? " AND ic_sp.cod_localFK = ".$codLocal." " : "")."
                GROUP BY sp.cod_usuarioFK_update";
        }

        $sql= "SELECT u.cod_usuario, IFNULL(p.nombre_persona, CONCAT('Usuario ', u.cod_usuario)) AS nombre_persona,
                IFNULL(u.url,'') AS url_usuario, SUM(actividad.total) AS total_gestiones
            FROM (
                SELECT m.cod_usuarioFK AS cod_usuario, COUNT(DISTINCT m.cod_mensaje) AS total
                FROM mensaje m
                INNER JOIN interconsulta ic ON ic.cod_interConsulta = m.cod_interConsultaFK
                WHERE m.estado = 'activo'
                    AND m.cod_usuarioFK IS NOT NULL
                    AND m.cod_usuarioFK <> 0
                    AND m.fecha_creacion >= CURDATE()
                    AND m.fecha_creacion < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                    ".$condicionLocalMensajes."
                GROUP BY m.cod_usuarioFK
                UNION ALL
                SELECT CAST(ag.creado_por AS UNSIGNED) AS cod_usuario, COUNT(DISTINCT ag.id_agenda) AS total
                FROM agenda ag
                INNER JOIN usuario u_ag ON u_ag.cod_usuario = CAST(ag.creado_por AS UNSIGNED)
                WHERE ag.creado_por REGEXP '^[0-9]+$'
                    AND ag.creado_en >= CURDATE()
                    AND ag.creado_en < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                    AND IFNULL(ag.estado,'') <> 'CANCELADO'
                    ".$condicionLocalAgenda."
                GROUP BY CAST(ag.creado_por AS UNSIGNED)
                ".$actividadSeguimientos."
            ) actividad
            INNER JOIN usuario u ON u.cod_usuario = actividad.cod_usuario
            LEFT JOIN persona p ON p.cod_persona = u.cod_usuario
            WHERE u.estado = 'Activo'
            GROUP BY u.cod_usuario, p.nombre_persona, u.url
            HAVING total_gestiones > 0
            ORDER BY total_gestiones DESC, p.nombre_persona ASC";

        $registros= array();
        $stmt= $mysqli->prepare($sql);
        if (!$stmt || !$stmt->execute()) {
            if ($stmt) { $stmt->close(); }
            $mysqli->close();
            return $registros;
        }
        $result= $stmt->get_result();
        while ($row= $result->fetch_assoc()) {
            $registros[]= array(
                "cod_usuario" => mb_convert_encoding((string)$row["cod_usuario"], 'UTF-8', 'ISO-8859-1'),
                "nombre_persona" => mb_convert_encoding((string)$row["nombre_persona"], 'UTF-8', 'ISO-8859-1'),
                "url_usuario" => mb_convert_encoding((string)$row["url_usuario"], 'UTF-8', 'ISO-8859-1'),
                "total_gestiones" => intval($row["total_gestiones"])
            );
        }
        $stmt->close();
        $mysqli->close();

        return $registros;
    }

    function obtenerVistaActividadDiariaSeguimientoInterConsulta($cod_localFK= "") {
        $registros= obtenerActividadDiariaSeguimientoInterConsulta($cod_localFK);
        if (count($registros) == 0) {
            return '<div class="interconsulta-daily-activity__empty">Sin gestiones hoy</div>';
        }

        $html= "";
        foreach ($registros as $registro) {
            $nombre= trim((string)$registro["nombre_persona"]);
            $url= trim((string)$registro["url_usuario"]);
            $total= intval($registro["total_gestiones"]);
            $titulo= $nombre." - ".$total." gestion".($total == 1 ? "" : "es")." hoy";
            $avatar= "";
            if ($url != "") {
                $avatar= '<img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="Foto de '.htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8').'">';
            } else {
                $avatar= '<span>'.htmlspecialchars(obtenerInicialesInterConsulta($nombre, "US"), ENT_QUOTES, 'UTF-8').'</span>';
            }

            $html .= '<span class="interconsulta-daily-activity__user" title="'.htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8').'" tabindex="0">'
                .'<span class="interconsulta-daily-activity__avatar">'.$avatar.'</span>'
                .'<strong>'.$total.'</strong>'
                .'</span>';
        }

        return $html;
    }

    function asuntoVistaSeguimientoPacienteInterConsulta($registro) {
        $asuntoActual = isset($registro['asunto']) ? trim((string)$registro['asunto']) : "";
        $esSeguimientoPaciente = isset($registro['esSeguimientoPaciente']) ? intval($registro['esSeguimientoPaciente']) > 0 : false;
        $codVenta = isset($registro['cod_ventaFK']) ? seguimientoPacienteEntero($registro['cod_ventaFK']) : 0;
        if (!$esSeguimientoPaciente && $codVenta <= 0) {
            return $asuntoActual;
        }

        $nombrePaciente = isset($registro['nombre_persona']) ? trim((string)$registro['nombre_persona']) : "";
        $cedula = isset($registro['cedula']) ? seguimientoPacienteNormalizarCedula($registro['cedula']) : "";
        if ($nombrePaciente == "" || $cedula == "") {
            return $asuntoActual;
        }

        $nombrePaciente = trim(preg_replace('/\s+/', ' ', $nombrePaciente));
        if ($nombrePaciente == "") {
            $nombrePaciente = "Paciente sin nombre";
        }
        $asunto = $nombrePaciente." - CI ".$cedula;
        return function_exists('mb_substr') ? mb_substr($asunto, 0, 100, 'UTF-8') : substr($asunto, 0, 100);
    }

    function obtenerInterConsultaBasica($filtros= array(), $limite= 30) {
        list($sqlFiltro, $sqlFiltroMenciones, $sqlFiltroMensaje, $sqlFiltroFechaLimite) = construirFiltrosInterConsulta($filtros);
        $limite= normalizarLimiteListadoInterConsulta($limite, 30);
        $codUsuario= isset($filtros['cod_usuarioFK']) ? intval($filtros['cod_usuarioFK']) : 0;
        $condicionUsuarioNoLeido= $codUsuario > 0 ? "AND mc.cod_usuarioFK=".$codUsuario : "";

        $sql= "SELECT ic.*,
                l.Nombre AS nombre_local,
                COALESCE(ip.cod_clienteFK_principal, vt.cod_clienteFK) AS cod_clienteFK,
                vt.num_factura,
                vt.apodo AS apodo_venta,
                COALESCE(NULLIF(ip.nombre_paciente_snapshot,''), paciente.nombre_persona) AS nombre_persona,
                creador.nombre_persona AS nombre_persona_creador,
                COALESCE(NULLIF(ip.cedula,''), cl.ci_cliente) AS cedula,
                IF(ip.id IS NULL,0,1) AS esSeguimientoPaciente,
                IFNULL(ip.estado_conflicto,0) AS seguimiento_conflicto,
                IFNULL(ip.detalle_conflicto,'') AS seguimiento_detalle_conflicto,
                IFNULL(ip.ventas_sin_plan_madre,0) AS ventas_sin_plan_madre,
                IFNULL(ip.total_ventas,0) AS total_ventas_paciente,
                IFNULL(ip.total_planes_madre,0) AS total_planes_madre,
                (SELECT COUNT(*) FROM gastos g WHERE g.cod_interConsultaFK=ic.cod_interConsulta) AS cantAsociadoGastos,
                (SELECT COUNT(*) FROM gastos g WHERE g.cod_interConsultaFK=ic.cod_interConsulta AND g.estado IN ('solicitado','pendiente') AND g.fecha<=CURDATE()) AS cantGastosPendientes,
                (SELECT COUNT(*) FROM mensaje mt WHERE mt.cod_interConsultaFK=ic.cod_interConsulta) AS cantMensajes,
                (SELECT COUNT(*) FROM mensaje mp WHERE mp.cod_interConsultaFK=ic.cod_interConsulta AND mp.estado='activo' AND mp.fecha_creacion>NOW()) AS cantMensajesProgramados,
                 (SELECT COUNT(mc.cod_mencion)
                    FROM menciones mc
                    INNER JOIN mensaje mj ON mc.cod_mensajeFK=mj.cod_mensaje
                    WHERE mc.isLeido=0
                    ".$sqlFiltroMenciones."
                    AND mj.cod_interConsultaFK=ic.cod_interConsulta
                    AND mj.fecha_creacion=(
                        SELECT MAX(mj2.fecha_creacion)
                        FROM mensaje mj2
                        WHERE mj2.cod_interConsultaFK=ic.cod_interConsulta
                        AND mj2.estado='activo'
                        ".$sqlFiltroFechaLimite."
                    )) AS cantMensajesNoLeidos,
                0 AS cantMensajesNoLeidosOtrosUsuarios
            FROM interconsulta ic
            LEFT JOIN interconsulta_paciente ip
                ON ip.cod_interConsultaFK=ic.cod_interConsulta AND ip.estado='activo'
            LEFT JOIN venta vt ON vt.cod_venta=ic.cod_ventaFK
            LEFT JOIN cliente cl ON cl.cod_cliente=vt.cod_clienteFK
            LEFT JOIN persona paciente ON paciente.cod_persona=vt.cod_clienteFK
            LEFT JOIN persona creador ON creador.cod_persona=ic.cod_usuarioFK_create
            LEFT JOIN local l ON l.cod_local=ic.cod_localFK
            ".$sqlFiltro."
            ORDER BY cantMensajesNoLeidos DESC,
                FIELD(ic.estado,'proceso','pendiente','finalizado','inactivo'),
                ic.cod_interConsulta DESC
            LIMIT ".$limite;

        $mysqli= conectar_al_servidor();
        $stmt= $mysqli->prepare($sql);
        if (!$stmt || !$stmt->execute()) {
            if ($stmt) { $stmt->close(); }
            $mysqli->close();
            return array();
        }
        $result= $stmt->get_result();
        $registros= array();
        while ($row= $result->fetch_assoc()) {
            $reg= array();
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $reg[$key]= mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                } else {
                    $reg[$key]= $value;
                }
            }
            $registros[]= $reg;
        }
        $stmt->close();
        $mysqli->close();
        return $registros;
    }

    function obtenerVistaInterConsultaBasica($filtros= array(), $limite= 30) {
        $cantRegistros= obtenerCantidadInterConsulta($filtros);
        $registros= obtenerInterConsultaBasica($filtros, $limite);
        $categoriaActiva= isset($filtros['categoria_principal']) ? $filtros['categoria_principal'] : 'pagos_egresos';
        $mostrarColumnasSeguimiento= in_array($categoriaActiva, array('administrativo_clinico','judiciales'), true);
        $mostrarGestionProgramada= $mostrarColumnasSeguimiento || $categoriaActiva == 'pagos_egresos';
        $pagina= '';
        $datalist= '';
        $estadoRegistros= array();
        $cantMensajesNoLeidos= 0;
        $cantInterConsultasAbiertas= 0;

        foreach ($registros as $value) {
            $estado= strtolower(trim((string)$value['estado']));
            if ($estado == 'pendiente' || $estado == 'proceso') {
                $cantInterConsultasAbiertas++;
            }
            $esSeguimiento= intval($value['esSeguimientoPaciente']) > 0;
            $esVinculado= intval($value['cantAsociadoGastos']) > 0 || !empty($value['cod_ventaFK']) || $esSeguimiento;
            $esPendiente= intval($value['cantMensajesNoLeidos']) > 0;
            $esAdministrativo= !$esSeguimiento && seguimientoPacienteEntero($value['cod_ventaFK']) <= 0;
            $asuntoVista= asuntoVistaSeguimientoPacienteInterConsulta($value);
            $cantMensajesNoLeidos += intval($value['cantMensajesNoLeidos']);
            $estadoRegistros[]= array(
                'cod_interConsulta' => $value['cod_interConsulta'],
                'cantMensajes' => $value['cantMensajes']
            );

            $clases= 'tableRegistroSearch2 interconsulta-thread-row interconsulta-thread-row--loading'
                .($esPendiente ? ' interconsulta-thread-row--pending' : '')
                .($esVinculado ? ' interconsulta-thread-row--linked' : '')
                .($esAdministrativo ? ' interconsulta-thread-row--administrative' : '')
                .($esSeguimiento ? ' interconsulta-thread-row--patient-master' : '');
            $badgePendiente= $esPendiente ? '<span class="interconsulta-pending-badge" title="Hilo pendiente de respuesta">Sin responder</span>' : '';
            $iconoVinculado= $esVinculado ? ' <i class="fa-solid fa-link interconsulta-linked-icon" title="Hilo vinculado" aria-hidden="true"></i>' : '';
            $lineaEstado= $badgePendiente != '' ? '<div class="interconsulta-follow-strip">'.$badgePendiente.'</div>' : '';
            $formatAsunto= '<div class="interconsulta-subject-wrap"><div class="interconsulta-subject-line"><p class="interconsulta-subject-text interconsulta-subject-title">'.htmlspecialchars($asuntoVista, ENT_QUOTES, 'UTF-8').$iconoVinculado.'</p></div>'.$lineaEstado.'</div>';
            $placeholder= renderResumenSeguimientoInterConsulta('muted','Cargando','Información complementaria');

            $anchoAsunto= $mostrarColumnasSeguimiento ? '21%' : '25%';
            $anchoCliente= $mostrarColumnasSeguimiento ? '13%' : '15%';
            $anchoLocal= $mostrarColumnasSeguimiento ? '9%' : '10%';
            $anchoEstado= $mostrarColumnasSeguimiento ? '8%' : '10%';
            $anchoTipo= $mostrarColumnasSeguimiento ? '8%' : '10%';
            $anchoFecha= $mostrarColumnasSeguimiento ? '7%' : '10%';
            $anchoResponsable= $mostrarColumnasSeguimiento ? '8%' : '15%';
            $celdaGestion= $mostrarGestionProgramada
                ? '<td class="interconsulta-management-cell" style="width: '.$anchoCliente.';">'.$placeholder.'</td><td id="td_datos_5" style="display:none;">'.htmlspecialchars((string)$value['nombre_persona'], ENT_QUOTES, 'UTF-8').'</td>'
                : '<td id="td_datos_5" style="width: '.$anchoCliente.';">'.htmlspecialchars((string)$value['nombre_persona'], ENT_QUOTES, 'UTF-8').'</td>';
            $columnasSeguimiento= $mostrarColumnasSeguimiento
                ? '<td class="hilos-follow-only interconsulta-follow-cell" style="width:10%;">'.$placeholder.'</td><td class="hilos-follow-only interconsulta-follow-cell" style="width:11%;">'.$placeholder.'</td>'
                : '';

            $pagina .= '<table class="'.$clases.'" border="1" cellspacing="1" cellpadding="1"><tr onclick="obtenerDatosInterConsulta(this)">
                <td id="td_id" style="width:5%;">'.$value['cod_interConsulta'].'</td>
                <td id="td_datos_1" style="width:'.$anchoAsunto.';"><div>'.$formatAsunto.'</div></td>
                <td id="td_datos_4" style="display:none;">'.$value['cod_ventaFK'].'</td>
                '.$celdaGestion.'
                <td id="td_datos_11" style="display:none;">'.$value['cod_localFK'].'</td>
                <td id="td_datos_12" style="width:'.$anchoLocal.';">'.htmlspecialchars((string)$value['nombre_local'], ENT_QUOTES, 'UTF-8').'</td>
                <td id="td_datos_2" style="width:'.$anchoEstado.';">'.htmlspecialchars((string)$value['estado'], ENT_QUOTES, 'UTF-8').'</td>
                <td id="td_datos_6" style="width:'.$anchoTipo.';">'.htmlspecialchars((string)$value['tipo'], ENT_QUOTES, 'UTF-8').'</td>
                '.$columnasSeguimiento.'
                <td id="td_datos_7" style="display:none;">'.$value['cod_clienteFK'].'</td>
                <td id="td_datos_8" style="width:'.$anchoFecha.';">'.$value['fecha_creacion'].'</td>
                <td id="td_datos_9" style="width:'.$anchoResponsable.';">'.htmlspecialchars((string)$value['nombre_persona_creador'], ENT_QUOTES, 'UTF-8').'</td>
                <td id="td_datos_10" style="display:none;">'.htmlspecialchars($asuntoVista, ENT_QUOTES, 'UTF-8').'</td>
                <td id="td_datos_13" style="display:none;">'.$value['cantMensajes'].'</td>
                <td id="td_datos_14" style="display:none;">'.$value['cantMensajesNoLeidos'].'</td>
                <td id="td_datos_15" style="display:none;">'.$value['monto_limite'].'</td>
                <td id="td_datos_16" style="display:none;">'.htmlspecialchars((string)$value['observacion'], ENT_QUOTES, 'UTF-8').'</td>
            </tr></table>';
            $datalist .= '<option data-id="'.$value['cod_interConsulta'].'" value="'.htmlspecialchars($asuntoVista, ENT_QUOTES, 'UTF-8').'">';
        }

        if ($pagina == '') {
            $pagina= obtenerVistaEstadoVacioHilosInterConsulta($categoriaActiva);
        }

        echo json_encode(array(
            '1' => 'exito',
            '2' => $pagina,
            '3' => $estadoRegistros,
            '4' => count($registros),
            '5' => $cantRegistros,
            '6' => $cantMensajesNoLeidos,
            '7' => $cantInterConsultasAbiertas,
            '8' => $datalist,
            '9' => array(),
            '10' => '',
            '11' => 'basico'
        ));
    }

    function obtenerVistaInterConsulta($filtros= array(), $limite= 0, $maximoLimite= 30, $codUsuarioSesion= 0) {
        $cantRegistros= obtenerCantidadInterConsulta($filtros);
        $limite = normalizarLimiteListadoInterConsulta($limite, $maximoLimite);
        $registros= obtenerInterConsulta($filtros, $limite);
        $codigosHilosSeguimiento= array();
        foreach ($registros as $registroSeguimiento) {
            if (!empty($registroSeguimiento['cod_interConsulta'])) {
                $codigosHilosSeguimiento[]= intval($registroSeguimiento['cod_interConsulta']);
            }
        }
        $seguimientosActivosPorHilo= seguimientoProgramadoObtenerActivosPorHilos($codigosHilosSeguimiento);
        $conteosCategorias = obtenerConteosCategoriasInterConsulta($filtros);
        $categoriaActiva = isset($filtros['categoria_principal']) ? $filtros['categoria_principal'] : 'pagos_egresos';
        $mostrarColumnasSeguimiento = in_array($categoriaActiva, array('administrativo_clinico', 'judiciales'), true);
        $mostrarGestionProgramada = $mostrarColumnasSeguimiento || $categoriaActiva == 'pagos_egresos';

        $pagina= '';
        $datalist= '';
        $cant_mensajes_no_leidos= 0;
        $cant_interConsulta_abierto= 0;
        $styleName="tableRegistroSearch";
        foreach ($registros as $value) {
            $codigoHiloActual= intval($value['cod_interConsulta']);
            $seguimientoActivo= isset($seguimientosActivosPorHilo[$codigoHiloActual]) ? $seguimientosActivosPorHilo[$codigoHiloActual] : null;
            $estadoSeguimientoActivo= $seguimientoActivo ? seguimientoProgramadoEstadoVisual($seguimientoActivo) : '';
            if ($value['estado'] == 'pendiente' || $value['estado'] == 'proceso') {
                $cant_interConsulta_abierto++;
            }

            $styleName=CargarStyleTable($styleName);
            $style= "";

            $styleInterno= "";
            $colorText= "";
            if ($value['tipo'] == 'interno') {
                $styleInterno= "color: white; background-color: #585f08;";
            }
            if ($value['cantAsociadoGastos'] > 0) {
                $styleInterno= "color: white; background-color: #08525f;";
                $colorText= "color: #2ea3c0;";
            }
            // Marca la interconsulta si tiene gastos pendientes
            if (intval($value['cantGastosPendientes']) > 0) {
                $styleInterno= "color: white; background-color: #762424;";
            }
            
            $cantMensajesNoLeidosOtrosUsuarios= "";
            if (intval($value['cantMensajesNoLeidosOtrosUsuarios']) > 0) {
                $cantMensajesNoLeidosOtrosUsuarios= " (".$value['cantMensajesNoLeidosOtrosUsuarios'].")";
            }

            $esSeguimientoPaciente = intval($value['esSeguimientoPaciente']) > 0;
            $tieneConflictoPaciente = intval($value['seguimiento_conflicto']) > 0;
            $tieneVentaPaciente = seguimientoPacienteEntero($value['cod_ventaFK']) > 0;
            $esHiloSeguimientoVisual = $esSeguimientoPaciente || $tieneVentaPaciente;
            $esHiloAdministrativoSinPaciente = !$esHiloSeguimientoVisual;
            $asuntoVista = asuntoVistaSeguimientoPacienteInterConsulta($value);
            $ventasSinPlanMadre = intval($value['ventas_sin_plan_madre']);
            $totalPlanesMadre = intval($value['total_planes_madre']);
            $totalLocalesSeguimiento = isset($value['seguimiento_total_locales']) ? intval($value['seguimiento_total_locales']) : 0;
            $tieneConflictoLocalPaciente = $totalLocalesSeguimiento > 1;
            $totalCreditosSeguimiento = isset($value['seguimiento_total_creditos']) ? intval($value['seguimiento_total_creditos']) : 0;
            $cuotasPendientesSeguimiento = isset($value['seguimiento_cuotas_pendientes']) ? intval($value['seguimiento_cuotas_pendientes']) : 0;
            $cuotasVencidasSeguimiento = isset($value['seguimiento_cuotas_vencidas']) ? intval($value['seguimiento_cuotas_vencidas']) : 0;
            $diasMoraSeguimiento = isset($value['seguimiento_dias_mora']) ? intval($value['seguimiento_dias_mora']) : 0;
            $saldoPendienteSeguimiento = isset($value['seguimiento_saldo_pendiente']) ? intval($value['seguimiento_saldo_pendiente']) : 0;
            $proximaCuotaSeguimiento = isset($value['seguimiento_proxima_cuota_fecha']) ? trim((string)$value['seguimiento_proxima_cuota_fecha']) : "";
            $citasFuturasSeguimiento = isset($value['seguimiento_citas_futuras']) ? intval($value['seguimiento_citas_futuras']) : 0;
            $proximaCitaIdSeguimiento = isset($value['seguimiento_proxima_cita_id']) ? trim((string)$value['seguimiento_proxima_cita_id']) : "";
            $proximaCitaSeguimiento = isset($value['seguimiento_proxima_cita_fecha']) ? trim((string)$value['seguimiento_proxima_cita_fecha']) : "";
            $proximaCitaHoraSeguimiento = isset($value['seguimiento_proxima_cita_hora']) ? trim((string)$value['seguimiento_proxima_cita_hora']) : "";
            $estadoCitaSeguimiento = isset($value['seguimiento_proxima_cita_estado']) ? trim((string)$value['seguimiento_proxima_cita_estado']) : "";
            $motivoCitaSeguimiento = isset($value['seguimiento_proxima_cita_motivo']) ? trim((string)$value['seguimiento_proxima_cita_motivo']) : "";
            $profesionalCitaSeguimiento = isset($value['seguimiento_proxima_cita_profesional']) ? trim((string)$value['seguimiento_proxima_cita_profesional']) : "";
            $creadorCitaSeguimiento = isset($value['seguimiento_proxima_cita_creador']) ? trim((string)$value['seguimiento_proxima_cita_creador']) : "";
            $urlCreadorCitaSeguimiento = isset($value['seguimiento_proxima_cita_creador_url']) ? trim((string)$value['seguimiento_proxima_cita_creador_url']) : "";
            $ventasRecientesSeguimiento = isset($value['seguimiento_ventas_recientes']) ? intval($value['seguimiento_ventas_recientes']) : 0;
            $ultimaVentaRecienteSeguimiento = isset($value['seguimiento_ultima_venta_reciente']) ? trim((string)$value['seguimiento_ultima_venta_reciente']) : "";
            $celdaCitaSeguimiento = "";
            $celdaPagoSeguimiento = "";
            $datosPagoMoraSeguimiento = array(
                'cod-interconsulta' => $value['cod_interConsulta'],
                'cod-cliente' => isset($value['cod_clienteFK']) ? $value['cod_clienteFK'] : "",
                'documento-paciente' => isset($value['cedula']) ? $value['cedula'] : "",
                'nombre-paciente' => isset($value['nombre_persona']) ? $value['nombre_persona'] : ""
            );
            if ($mostrarColumnasSeguimiento) {
                if ($esHiloAdministrativoSinPaciente) {
                    $celdaCitaSeguimiento = renderResumenNoAplicaInterConsulta("Sin paciente");
                    $celdaPagoSeguimiento = renderResumenNoAplicaInterConsulta("Sin paciente");
                } else if ($proximaCitaSeguimiento != "") {
                    $fechaCitaSeguimiento = strtotime($proximaCitaSeguimiento) ? date('d/m', strtotime($proximaCitaSeguimiento)) : $proximaCitaSeguimiento;
                    $textoCitaSeguimiento = $fechaCitaSeguimiento.($proximaCitaHoraSeguimiento != "" ? ' '.$proximaCitaHoraSeguimiento : '');
                    $detalleCitaPartes = array();
                    if ($estadoCitaSeguimiento != "") {
                        $detalleCitaPartes[] = $estadoCitaSeguimiento;
                    }
                    if ($citasFuturasSeguimiento > 1) {
                        $detalleCitaPartes[] = '+'.($citasFuturasSeguimiento - 1).' mas';
                    }
                    $tituloCitaPartes = array('Proxima cita: '.$textoCitaSeguimiento);
                    if ($estadoCitaSeguimiento != "") {
                        $tituloCitaPartes[] = 'Estado: '.$estadoCitaSeguimiento;
                    }
                    if ($profesionalCitaSeguimiento != "") {
                        $tituloCitaPartes[] = 'Profesional: '.$profesionalCitaSeguimiento;
                    }
                    if ($creadorCitaSeguimiento != "") {
                        $tituloCitaPartes[] = 'Agendado por: '.$creadorCitaSeguimiento;
                    }
                    if ($motivoCitaSeguimiento != "") {
                        $tituloCitaPartes[] = 'Motivo: '.$motivoCitaSeguimiento;
                    }
                    if ($citasFuturasSeguimiento > 1) {
                        $tituloCitaPartes[] = 'Citas futuras: '.$citasFuturasSeguimiento;
                    }
                    $claseCitaSeguimiento = stripos($estadoCitaSeguimiento, 'DEUDA') !== false ? 'warning' : 'info';
                    $celdaCitaSeguimiento = renderResumenCitaSeguimientoInterConsulta(
                        $claseCitaSeguimiento,
                        $textoCitaSeguimiento,
                        implode(' - ', $detalleCitaPartes),
                        $creadorCitaSeguimiento,
                        $urlCreadorCitaSeguimiento,
                        implode('. ', $tituloCitaPartes),
                        $proximaCitaIdSeguimiento,
                        $proximaCitaSeguimiento
                    );
                } else {
                    $celdaCitaSeguimiento = renderResumenSeguimientoInterConsulta(
                        'muted',
                        'Sin cita',
                        'Sin futura activa',
                        'No se encontro una cita futura activa para este hilo.'
                    );
                }

                if (!$esHiloAdministrativoSinPaciente) {
                    if ($cuotasVencidasSeguimiento > 0) {
                        $celdaPagoSeguimiento = renderResumenSeguimientoInterConsulta(
                            'warning',
                            'Alerta financiera',
                            $cuotasVencidasSeguimiento.' venc. - Gs. '.number_format($saldoPendienteSeguimiento, 0, ',', '.'),
                            'Alerta financiera: '.$cuotasVencidasSeguimiento.' cuota(s) vencida(s). Dias de mora: '.$diasMoraSeguimiento.'. Saldo pendiente: Gs. '.number_format($saldoPendienteSeguimiento, 0, ',', '.'),
                            'abrir_pago_mora',
                            $datosPagoMoraSeguimiento
                        );
                    } else if ($cuotasPendientesSeguimiento > 0) {
                        $detallePago = 'Saldo Gs. '.number_format($saldoPendienteSeguimiento, 0, ',', '.');
                        if ($proximaCuotaSeguimiento != "") {
                            $fechaProximaCuota = strtotime($proximaCuotaSeguimiento) ? date('d/m', strtotime($proximaCuotaSeguimiento)) : $proximaCuotaSeguimiento;
                            $detallePago .= ' - Prox. '.$fechaProximaCuota;
                        }
                        $celdaPagoSeguimiento = renderResumenSeguimientoInterConsulta(
                            'warning',
                            $cuotasPendientesSeguimiento.' cuotas',
                            $detallePago,
                            'Cuotas pendientes: '.$cuotasPendientesSeguimiento.'. '.$detallePago,
                            'abrir_pago_mora',
                            $datosPagoMoraSeguimiento
                        );
                    } else if ($totalCreditosSeguimiento > 0) {
                        $celdaPagoSeguimiento = renderResumenSeguimientoInterConsulta(
                            'success',
                            'Cuenta saldada',
                            'Sin saldo pendiente',
                            'Tiene cuotas registradas y no registra saldo pendiente.',
                            'abrir_pago_mora',
                            $datosPagoMoraSeguimiento
                        );
                    } else {
                        $celdaPagoSeguimiento = renderResumenSeguimientoInterConsulta(
                            'muted',
                            'Sin cuotas',
                            'Sin datos de pago',
                            'No se encontraron cuotas vinculadas a este hilo.'
                        );
                    }
                }
            }
            $esHiloVinculado = intval($value['cantAsociadoGastos']) > 0 || !empty($value['cod_ventaFK']) || $esSeguimientoPaciente;
            $esHiloPendienteRespuesta = intval($value['cantMensajesNoLeidos']) > 0;
            $clasesTablaHilo = 'tableRegistroSearch2 interconsulta-thread-row'
                .($esHiloPendienteRespuesta ? ' interconsulta-thread-row--pending' : '')
                .($esHiloVinculado ? ' interconsulta-thread-row--linked' : '')
                .($esHiloAdministrativoSinPaciente ? ' interconsulta-thread-row--administrative' : '')
                .($esSeguimientoPaciente ? ' interconsulta-thread-row--patient-master' : '')
                .(($tieneConflictoPaciente || $tieneConflictoLocalPaciente) ? ' interconsulta-thread-row--patient-conflict' : '')
                .($ventasSinPlanMadre > 0 ? ' interconsulta-thread-row--without-plan' : '')
                .($cuotasVencidasSeguimiento > 0 ? ' interconsulta-thread-row--financial-warning' : '')
                .(($totalCreditosSeguimiento > 0 && $cuotasPendientesSeguimiento == 0) ? ' interconsulta-thread-row--financial-ok' : '')
                .($estadoSeguimientoActivo === 'vencido' ? ' interconsulta-thread-row--followup-overdue' : '')
                .($estadoSeguimientoActivo === 'para_hoy' ? ' interconsulta-thread-row--followup-today' : '');
            $claseHiloVinculado = ' class="interconsulta-subject-text interconsulta-subject-title'.($esHiloVinculado ? ' interconsulta-linked-subject' : '').'"';
            $tituloHiloVinculado = $esHiloVinculado ? ' title="Hilo vinculado. Haga clic para ver la referencia asociada."' : '';
            $iconoHiloVinculado = $esHiloVinculado ? ' <i class="fa-solid fa-link interconsulta-linked-icon" title="Hilo vinculado. Haga clic para ver la referencia asociada." aria-hidden="true"></i>' : '';
            $badgesSeguimientoPaciente = "";
            if ($estadoSeguimientoActivo === 'vencido') {
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'conflict',
                    'Seguimiento vencido',
                    'El seguimiento interno asignado ya supero su fecha y hora programadas.'
                );
            } else if ($estadoSeguimientoActivo === 'para_hoy') {
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'warning',
                    'Seguimiento hoy',
                    'Este hilo tiene un seguimiento interno asignado para hoy.'
                );
            }
            if ($esHiloAdministrativoSinPaciente) {
                $tipoAdministrativo = trim((string)$value['tipo']) != "" ? ucfirst(strtolower(trim((string)$value['tipo']))) : "Administrativo";
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'operational',
                    $tipoAdministrativo,
                    'Hilo administrativo sin paciente vinculado.'
                );
            }
            if ($esHiloSeguimientoVisual && $ventasRecientesSeguimiento > 0) {
                $detallePacienteNuevo = 'Paciente con venta real registrada en los ultimos 30 dias.';
                if ($ultimaVentaRecienteSeguimiento != "" && strtotime($ultimaVentaRecienteSeguimiento)) {
                    $detallePacienteNuevo .= ' Ultima venta: '.date('d/m/Y', strtotime($ultimaVentaRecienteSeguimiento)).'.';
                }
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'new-patient',
                    'Paciente nuevo',
                    $detallePacienteNuevo
                );
            }
            if ($tieneConflictoPaciente) {
                $detalleConflicto = trim((string)$value['seguimiento_detalle_conflicto']);
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'conflict',
                    'Conflicto CI',
                    $detalleConflicto != "" ? $detalleConflicto : 'La cedula esta asociada a mas de un paciente.'
                );
            }
            if ($tieneConflictoLocalPaciente) {
                $detalleLocales = trim((string)$value['seguimiento_locales']);
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'conflict',
                    'Varios locales',
                    'El paciente tiene ventas vinculadas en mas de un local'.($detalleLocales != "" ? ': '.$detalleLocales : '.')
                );
            }
            if ($ventasSinPlanMadre > 0) {
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'warning',
                    'Sin plan madre: '.$ventasSinPlanMadre,
                    $ventasSinPlanMadre.' venta(s) real(es) del paciente no estan vinculadas a un plan madre.'
                );
            }
            if ($totalPlanesMadre > 1) {
                $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta(
                    'info',
                    $totalPlanesMadre.' planes madre',
                    'Planes madre vinculados a esta cedula: '.$totalPlanesMadre.'.'
                );
            }
            if (!$mostrarColumnasSeguimiento && !$esHiloAdministrativoSinPaciente) {
                if ($cuotasVencidasSeguimiento > 0) {
                    $detalleMora = 'Alerta financiera: '.$cuotasVencidasSeguimiento.' cuota(s) vencida(s). Dias de mora: '.$diasMoraSeguimiento.'. Saldo pendiente: Gs. '.number_format($saldoPendienteSeguimiento, 0, ',', '.');
                    $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta('warning', 'Alerta financiera', $detalleMora);
                } else if ($cuotasPendientesSeguimiento > 0) {
                    $detalleCuotas = 'Cuotas pendientes: '.$cuotasPendientesSeguimiento.'. Saldo pendiente: Gs. '.number_format($saldoPendienteSeguimiento, 0, ',', '.');
                    $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta('warning', 'Alerta financiera', $detalleCuotas);
                } else if ($totalCreditosSeguimiento > 0) {
                    $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta('success', 'Cuenta saldada', 'Tiene cuotas registradas sin saldo pendiente.');
                }
                if ($proximaCitaSeguimiento != "") {
                    $textoCita = date('d/m', strtotime($proximaCitaSeguimiento)).($proximaCitaHoraSeguimiento != "" ? ' '.$proximaCitaHoraSeguimiento : '');
                    $detalleCita = 'Proxima cita: '.$textoCita.($estadoCitaSeguimiento != "" ? ' - '.$estadoCitaSeguimiento : '');
                    $claseCita = stripos($estadoCitaSeguimiento, 'DEUDA') !== false ? 'warning' : 'info';
                    $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta($claseCita, 'Cita '.$textoCita, $detalleCita);
                } else if ($esSeguimientoPaciente) {
                    $badgesSeguimientoPaciente .= renderBadgeSeguimientoInterConsulta('muted', 'Sin cita', 'No se encontro cita futura activa para este paciente.');
                }
            }
            if ($esHiloAdministrativoSinPaciente) {
                $styleInterno = "";
                $colorText = "";
                if (intval($value['cantGastosPendientes']) > 0) {
                    $styleInterno= "color: white; background-color: #762424;";
                }
            }
            $contenidoAsunto = $asuntoVista
                .$iconoHiloVinculado
                .$cantMensajesNoLeidosOtrosUsuarios;
            $celdaGestionProgramada = $mostrarGestionProgramada
                ? ($seguimientoActivo ? renderSeguimientoProgramadoInternoInterConsulta($seguimientoActivo) : renderGestionProgramadaInterConsulta())
                : "";
            if ($esHiloPendienteRespuesta) {
                $cant_mensajes_no_leidos += intval($value['cantMensajesNoLeidos']);
            }

            if (!$seguimientoActivo && $value["cantMensajesProgramados"]) {
                // Obtiene los mensajes programados
                $registrosMens= obtenerMensaje(array(
                    'estado' => 'activo',
                    'fecha_creacion' => "> '".(new DateTime())->format('Y-m-d H:i:s')."'",
                    'orden_fecha' => 'ASC',
                    "cod_interConsultaFK" => $value["cod_interConsulta"],
                ), 1);
                foreach ($registrosMens as $valueMens) {
                    if ($valueMens['estado'] == 'activo') {
                        if ($mostrarGestionProgramada) {
                            $celdaGestionProgramada = renderGestionProgramadaInterConsulta(
                                isset($valueMens['contenido']) ? $valueMens['contenido'] : "",
                                isset($valueMens['nombre_persona']) ? $valueMens['nombre_persona'] : "",
                                isset($valueMens['fecha_creacion']) ? $valueMens['fecha_creacion'] : "",
                                isset($valueMens['url_usuario']) ? $valueMens['url_usuario'] : ""
                            );
                        } else {
                            $fechaMensaje = new DateTime(substr($valueMens['fecha_creacion'], 0, 10));
                            $fechaActual = new DateTime();
                            $diasRestantes = $fechaMensaje->diff($fechaActual->setTime(0, 0, 0));
                            $contenidoAsunto .= '<i class="fa-solid fa-business-time" style="padding-left: 5px;font-size: 9pt;"></i>('.$diasRestantes->format('%a').') ';
                        }
                    }
                }
            }
            $contenidoAsuntoPendienteInicio = $esHiloPendienteRespuesta ? '<b>' : '';
            $contenidoAsuntoPendienteFin = $esHiloPendienteRespuesta ? '</b>' : '';
            $badgePendienteRespuesta = $esHiloPendienteRespuesta ? '<span class="interconsulta-pending-badge" title="Hilo pendiente de respuesta">Sin responder</span>' : '';
            $tooltipFila = $esHiloPendienteRespuesta ? ' title="Hilo pendiente de respuesta"' : '';
            $lineaSeguimientoPaciente = ($badgePendienteRespuesta != "" || $badgesSeguimientoPaciente != "")
                ? '<div class="interconsulta-follow-strip">'.$badgePendienteRespuesta.$badgesSeguimientoPaciente.'</div>'
                : '';
            $formatAsunto= '<div class="interconsulta-subject-wrap">'
                .'<div class="interconsulta-subject-line">'
                .'<p'.$claseHiloVinculado.$tituloHiloVinculado.' style="'.$colorText.'">'
                .$contenidoAsuntoPendienteInicio
                .$contenidoAsunto
                .$contenidoAsuntoPendienteFin
                .'</p>'
                .'</div>'
                .$lineaSeguimientoPaciente
                .'</div>';
            $anchoAsunto = $mostrarColumnasSeguimiento ? '21%' : '25%';
            $anchoCliente = $mostrarColumnasSeguimiento ? '13%' : '15%';
            $anchoLocal = $mostrarColumnasSeguimiento ? '9%' : '10%';
            $anchoEstado = $mostrarColumnasSeguimiento ? '8%' : '10%';
            $anchoTipo = $mostrarColumnasSeguimiento ? '8%' : '10%';
            $anchoFecha = $mostrarColumnasSeguimiento ? '7%' : '10%';
            $anchoResponsable = $mostrarColumnasSeguimiento ? '8%' : '15%';
            $celdaGestionOPaciente = $mostrarGestionProgramada
                ? '<td class="interconsulta-management-cell" style="width: '.$anchoCliente.';'.$style.'">'.$celdaGestionProgramada.'</td>
                    <td id="td_datos_5" style="display: none;'.$style.'">'.$value['nombre_persona'].'</td>'
                : '<td id="td_datos_5" style="width: '.$anchoCliente.';'.$style.'">'.$value['nombre_persona'].'</td>';
            $columnasSeguimiento = "";
            if ($mostrarColumnasSeguimiento) {
                $columnasSeguimiento = '
                    <td class="hilos-follow-only interconsulta-follow-cell" style="width: 10%;'.$style.'">'.$celdaCitaSeguimiento.'</td>
                    <td class="hilos-follow-only interconsulta-follow-cell" style="width: 11%;'.$style.'">'.$celdaPagoSeguimiento.'</td>';
            }
            
            $pagina .= '<table class="'.$clasesTablaHilo.'" border="1" cellspacing="1" cellpadding="1">
                <tr onclick="obtenerDatosInterConsulta(this)"'.$tooltipFila.'>
                    <td id="td_id" style="width: 5%;'.$styleInterno.'">'.$value['cod_interConsulta'].'</td>
                    <td id="td_datos_1" style="width: '.$anchoAsunto.';'.$style.'"><div>'.$formatAsunto.'</div></td>
                    <td id="td_datos_4" style="display: none;'.$style.'">'.$value['cod_ventaFK'].'</td>
                    '.$celdaGestionOPaciente.'
                    <td id="td_datos_11" style="display: none;'.$style.'">'.$value['cod_localFK'].'</td>
                    <td id="td_datos_12" style="width: '.$anchoLocal.';'.$style.'">'.$value['nombre_local'].'</td>
                    <td id="td_datos_2" style="width: '.$anchoEstado.';'.$style.'">'.$value['estado'].'</td>
                    <td id="td_datos_6" style="width: '.$anchoTipo.';'.$style.'">'.$value['tipo'].'</td>
                    '.$columnasSeguimiento.'
                    <td id="td_datos_7" style="display: none;'.$style.'">'.$value['cod_clienteFK'].'</td>
                    <td id="td_datos_8" style="width: '.$anchoFecha.';'.$style.'">'.$value['fecha_creacion'].'</td>
                    <td id="td_datos_9" style="width: '.$anchoResponsable.';'.$style.'">'.$value['nombre_persona_creador'].'</td>
                    <td id="td_datos_10" style="display: none;'.$style.'">'.$asuntoVista.'</td>
                    <td id="td_datos_13" style="display: none;'.$style.'">'.$value['cantMensajes'].'</td>
                    <td id="td_datos_14" style="display: none;'.$style.'">'.$value['cantMensajesNoLeidos'].'</td>
                    <td id="td_datos_15" style="display: none;'.$style.'">'.$value['monto_limite'].'</td>
                    <td id="td_datos_16" style="display: none;'.$style.'">'.$value['observacion'].'</td>
                </tr>
            </table>';

            $datalist .= '<option data-id="'.$value['cod_interConsulta'].'" value="'.$asuntoVista.'">';
        }

        if ($pagina == "") {
            $pagina = obtenerVistaEstadoVacioHilosInterConsulta($categoriaActiva);
        }

        $actividadDiariaSeguimiento= obtenerVistaActividadDiariaSeguimientoInterConsulta(isset($filtros['cod_localFK']) ? $filtros['cod_localFK'] : "");
        $alertasSeguimientoProgramado= seguimientoProgramadoObtenerResumenAlertas($codUsuarioSesion);

        $estadoRegistros= array();
        foreach ($registros as $registroEstado) {
            $estadoRegistros[]= array(
                "cod_interConsulta" => isset($registroEstado['cod_interConsulta']) ? $registroEstado['cod_interConsulta'] : "",
                "cantMensajes" => isset($registroEstado['cantMensajes']) ? $registroEstado['cantMensajes'] : 0
            );
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $estadoRegistros, "4" => count($registros), "5" => $cantRegistros, "6" => $cant_mensajes_no_leidos, "7" => $cant_interConsulta_abierto, "8" => $datalist, "9" => $conteosCategorias, "10" => $actividadDiariaSeguimiento, "11" => "enriquecido", "12" => $alertasSeguimientoProgramado));
    }

    function obtenerVistaMensaje($filtros= [], $limite= 0) {
        $cantRegistros= obtenerMensaje($filtros);
        $cantRegistros= count($cantRegistros);
        $registros= obtenerMensaje($filtros, $limite);

        $pagina= '';
        foreach ($registros as $value) {
            $pagina .= '<div class="card my-3" style="border-left: 5px solid #ff5722;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span></span>
                    <small class="text-secondary">'. $value['fecha_creacion'] .'</small>
                </div>
                <div class="card-body">
                    <h5 class="card-title">'. $value['nombre_persona'] .'</h5>
                    <p class="card-text">'. $value['contenido'] .'</p>
                </div>
                </div>';
        }

        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $registros, "4" => count($registros), "5" => $cantRegistros));
        return;
    }

    function buscarUsuarios() {
        $mysqli=conectar_al_servidor();

        $sql= "SELECT u.cod_usuario, u.url, p.nombre_persona FROM usuario u JOIN persona p ON p.cod_persona = u.cod_usuario WHERE u.estado = 'Activo' ORDER BY p.nombre_persona ASC";
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
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

    function esUrlImagenInterconsulta($url) {
        $extension= strtolower(pathinfo(parse_url((string)$url, PHP_URL_PATH), PATHINFO_EXTENSION));
        return in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'));
    }

    function obtenerExtensionUrlInterconsulta($url) {
        return strtolower(pathinfo(parse_url((string)$url, PHP_URL_PATH), PATHINFO_EXTENSION));
    }

    function subirImagenMensaje($cod_mensaje, $foto, $ext, $campo) {
        $ruta= NULL;

        if (!(empty($foto) || empty($ext))) {
            if (strpos($foto, ",") !== false) {
                $foto = substr($foto, strpos($foto, ",") + 1);
            }
            $foto = base64_decode($foto);
            $id_foto = "";
            $donde = "../fotos/fotosMensaje/";
            $id_foto = $cod_mensaje;
            $id_f = subir_imagen_base64($donde, $foto, $id_foto, $ext);
            $ruta = "/GoodVentaAsisCap/fotos/fotosMensaje/" . $cod_mensaje . $id_f . "." . $ext;
        }
        
        $mysqli=conectar_al_servidor();
        $consulta="Update mensaje set $campo=? where cod_mensaje=? ";	

        $stmt = $mysqli->prepare($consulta);
        $ss="si";
        $stmt->bind_param($ss,$ruta,$cod_mensaje);
        if ( ! $stmt->execute()) {
            echo "Error";
            exit;
        }

        return $cod_mensaje;
    }

    function obtenerMencion($filtros, $limite) {
        $sqlFiltro= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'nombre_usuario':
                    $sqlFiltro .= "p.nombre_persona like '%$value%'";
                    break;
                case 'estado':
                    $sqlFiltro .= "m.estado = '$value'";
                    break;
                case 'isLeido':
                    $sqlFiltro .= "m.isLeido = $value";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "m.$key = $value";
                    } else {
                        $sqlFiltro .= "m.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $sql= "SELECT m.*, p.nombre_persona AS nombre_persona FROM menciones m JOIN usuario u ON u.cod_usuario = m.cod_usuarioFK JOIN persona p ON p.cod_persona = u.cod_usuario $sqlFiltro $limite";

        $mysqli=conectar_al_servidor();
        $stmt = $mysqli->prepare($sql);
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
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

    function abmMencion($cod_mencion, $cod_usuarioFK, $cod_mensajeFK, $isLeido, $estado) {
        $mysqli = conectar_al_servidor();
        
        // Comprueba si la mencion ya existe
        $sql= "SELECT cod_mencion FROM menciones WHERE cod_usuarioFK = ? AND cod_mensajeFK = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $cod_usuarioFK, $cod_mensajeFK);

        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error al registrar la asistencia: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);	
            exit;
        }
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $cod_mencion = $row['cod_mencion'];
        }

        if ($cod_mencion) {
            $sql= "UPDATE menciones SET isLeido= ? WHERE cod_mencion = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('si', $isLeido, $cod_mencion);

            $parametros = array();
            $atributos = "";
            $ss = "";

            if (!empty($isLeido)) {
                $atributos .= "isLeido = ?, ";
                $parametros[] = $isLeido;
                $ss .= "i";
            }
            if (!empty($estado)) {
                $atributos .= "estado = ?, ";
                $parametros[] = $estado;
                $ss .= "s";
            }
            $atributos = substr($atributos, 0, -2);

            $parametros[] = $cod_mencion;
            $ss .= "i";

            $sql= "UPDATE menciones SET $atributos WHERE cod_mencion = ?";
            $stmt = $mysqli->prepare($sql);

            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
        } else {
            $estadoInsert= !empty($estado) ? $estado : 'activo';
            $sql = "INSERT INTO menciones (cod_usuarioFK, cod_mensajeFK, isLeido, estado)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    isLeido=VALUES(isLeido),
                    estado=VALUES(estado),
                    cod_mencion=LAST_INSERT_ID(cod_mencion)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('iiis', $cod_usuarioFK, $cod_mensajeFK, $isLeido, $estadoInsert);
        }

        if (!$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "Error al guardar: " . $stmt->error, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        if (empty($cod_mencion)) {
            $cod_mencion = $stmt->insert_id;
            if (empty($cod_mencion)) {
                $resultInsert= $mysqli->query("SELECT LAST_INSERT_ID() AS cod_mencion");
                $rowInsert= $resultInsert ? $resultInsert->fetch_assoc() : null;
                $cod_mencion= $rowInsert && isset($rowInsert['cod_mencion']) ? intval($rowInsert['cod_mencion']) : 0;
                if ($resultInsert) { $resultInsert->free(); }
            }
        }

        $stmt->close();
        return $cod_mencion;
    }

    function obtenerMensaje($filtros= array(), $limite= 0) {
        $sqlFiltro= "";
        $ordenFechaMensaje = "DESC";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}
            if ($key == 'orden_fecha') {
                $ordenFechaMensaje = strtoupper(trim((string)$value)) == "ASC" ? "ASC" : "DESC";
                continue;
            }
            if ($sqlFiltro == "") {
                $sqlFiltro .= "WHERE ";
            } else {
                $sqlFiltro .= " AND ";
            }

            switch ($key) {
                case 'cod_local':
                    $sqlFiltro .= "m.cod_localFK = '$value'";
                    break;
                case 'estado':
                    $sqlFiltro .= "m.estado = '$value'";
                    break;
                case 'fecha_creacion':
                    $sqlFiltro .= "m.fecha_creacion $value";
                    break;
                case 'sin_dictamen':
                    $sqlFiltro .= "m.cod_dictamenFK IS NULL";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "m.$key = $value";
                    } else {
                        $sqlFiltro .= "m.$key like '%$value%'";
                    }
                    break;
            }
        }

        if ($limite === 0) {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $mysqli=conectar_al_servidor();
        $camposRespuesta = ", NULL AS respuesta_cod_mensaje, NULL AS respuesta_contenido,
            NULL AS respuesta_fecha_creacion, NULL AS respuesta_estado,
            NULL AS respuesta_cod_interConsultaFK, NULL AS respuesta_nombre_persona";
        $joinRespuesta = "";
        if (seguimientoProgramadoRespuestaCitadaDisponible($mysqli)) {
            $camposRespuesta = ", mr.cod_mensaje AS respuesta_cod_mensaje,
                mr.contenido AS respuesta_contenido,
                mr.fecha_creacion AS respuesta_fecha_creacion,
                mr.estado AS respuesta_estado,
                mr.cod_interConsultaFK AS respuesta_cod_interConsultaFK,
                pr.nombre_persona AS respuesta_nombre_persona";
            $joinRespuesta = "
                LEFT JOIN mensaje mr ON mr.cod_mensaje=m.cod_mensaje_respuestaFK
                LEFT JOIN persona pr ON pr.cod_persona=mr.cod_usuarioFK";
        }

        $sql= "SELECT * FROM (
                SELECT m.*, u.url AS url_usuario, p.nombre_persona AS nombre_persona".$camposRespuesta."
                FROM mensaje m
                LEFT JOIN usuario u ON u.cod_usuario=m.cod_usuarioFK
                LEFT JOIN persona p ON p.cod_persona=m.cod_usuarioFK".$joinRespuesta."
                $sqlFiltro ORDER BY m.fecha_creacion ".$ordenFechaMensaje.", m.cod_mensaje ".$ordenFechaMensaje." $limite
            ) AS subquery ORDER BY fecha_creacion ASC, cod_mensaje ASC";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt || !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "No se pudieron consultar los mensajes del hilo.");
            echo json_encode($informacion);	
            if ($stmt) { $stmt->close(); }
            $mysqli->close();
            exit;
        }        

        $result = $stmt->get_result();
        $registros= array();
        // Reemplaza el bucle while en obtenerMensaje con esto SOLO si tienes datos mixtos:
        while ($row = $result->fetch_assoc()) {
            $reg = array();
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    // Verificar si es UTF-8 válido (PHP 8 compatible)
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $reg[$key] = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                    } else {
                        $reg[$key] = $value; // Ya es UTF-8
                    }
                } else {
                    $reg[$key] = $value; // NULL, números, etc.
                }
            }
            $registros[] = $reg;
        }
        $stmt->close();
        $mysqli->close();
        return $registros;
    }

    function abmMensaje($cod_mensaje, $contenido, $fecha_creacion, $cod_interConsulta, $user,$cod_dictamenFK, $visto_creador= FALSE, $cod_mensaje_respuestaFK= NULL) {
        $mysqli = conectar_al_servidor();
        $contenidoLimpiado= "";

        if ($contenido) {
            // Convierte el contenido a html
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // evitar warnings por HTML incompleto
            $dom->loadHTML($contenido);
    
            // Obtiene todas las menciones
            $spans = $dom->getElementsByTagName('b');
            $ids_menciones = [];
            foreach ($spans as $span) {
                if ($span->hasAttribute('id')) {
                    $ids_menciones[] = $span->getAttribute('id');
                }
            }
    
            // Limpia el contenido del mensaje
            $contenidoLimpiado= "";
            $xpath = new DOMXPath($dom);
    
            // Reemplazar cada <b> con su id
            foreach ($xpath->query('//b[@id]') as $b) {
                $id = $b->getAttribute('id');
                // Crear nodo de texto con la notación @{id}
                $nuevoTexto = $dom->createTextNode("@{" . $id . "}");
                $b->parentNode->replaceChild($nuevoTexto, $b);
            }
            
            // Extrae HTML del body y preserva saltos de linea de contenteditable.
            $body = $dom->getElementsByTagName('body')->item(0);
            $contenidoHtml = $body ? $dom->saveHTML($body) : $dom->saveHTML();
            $contenidoHtml = preg_replace('/^<body[^>]*>|<\/body>$/i', '', $contenidoHtml);
            $contenidoHtml = preg_replace('/<br\\s*\\/?>/i', "\n", $contenidoHtml);
            $contenidoHtml = preg_replace('/<\\/(div|p|li|tr|h[1-6])>/i', "\n", $contenidoHtml);

            // Obtener el texto plano resultante
            $contenidoLimpiado = strip_tags($contenidoHtml);

            // Limpiar espacios y entidades
            $contenidoLimpiado = html_entity_decode($contenidoLimpiado, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $contenidoLimpiado = str_replace("\xC2\xA0", " ", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/[ \t]+\n/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/\n[ \t]+/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/\R/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado = preg_replace("/\n{3,}/", "\n\n", $contenidoLimpiado);
            $contenidoLimpiado = trim($contenidoLimpiado);
        }

        $encodingContenido = function_exists('mb_check_encoding') && mb_check_encoding($contenidoLimpiado, 'UTF-8') ? 'UTF-8' : 'ISO-8859-1';
        $longitudContenido = function_exists('mb_strlen') ? mb_strlen($contenidoLimpiado, $encodingContenido) : strlen($contenidoLimpiado);
        if (empty($cod_mensaje) && $contenidoLimpiado === '') {
            echo json_encode(array("1" => "error", "mensaje" => "Ingrese el contenido del mensaje."));
            $mysqli->close();
            exit;
        }
        if ($longitudContenido > 750) {
            echo json_encode(array("1" => "error", "mensaje" => "El mensaje supera el limite de 750 caracteres."));
            $mysqli->close();
            exit;
        }

        if (empty($cod_mensaje)) {
            if (seguimientoProgramadoRespuestaCitadaDisponible($mysqli)) {
                $sql = "INSERT INTO mensaje (contenido, fecha_creacion, cod_interConsultaFK, cod_usuarioFK, cod_dictamenFK, cod_mensaje_respuestaFK) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('ssiiii', $contenidoLimpiado, $fecha_creacion, $cod_interConsulta, $user, $cod_dictamenFK, $cod_mensaje_respuestaFK);
                }
            } else {
                $sql = "INSERT INTO mensaje (contenido, fecha_creacion, cod_interConsultaFK, cod_usuarioFK, cod_dictamenFK) VALUES (?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('ssiis', $contenidoLimpiado, $fecha_creacion, $cod_interConsulta, $user, $cod_dictamenFK);
                }
            }
        } else {
            $parametros= array();
            $atributos= "";
            $ss= "";

            // Datos a modificar
            if (!empty($contenidoLimpiado)) {
                $atributos .= ", contenido= ?";
                $ss .= "s";
                $parametros[] = $contenidoLimpiado;
            }
            if ($cod_interConsulta !== NULL) {
                $atributos .= ", cod_interConsultaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_interConsulta;
            }
            if ($cod_dictamenFK !== NULL) {
                $atributos .= ", cod_dictamenFK= ?";
                $ss .= "i";
                $parametros[] = $cod_dictamenFK;
            }
            $parametros[] = $cod_mensaje;
            $ss .= "i";

            $atributos = substr($atributos, 2);
            $sql= "UPDATE mensaje SET $atributos WHERE cod_mensaje = ?";
            $stmt = $mysqli->prepare($sql);
            
            $refs = [];
            foreach ($parametros as $k => $v) {$refs[$k] = &$parametros[$k];}

            if ($stmt) {
                call_user_func_array([$stmt, 'bind_param'], array_merge([$ss], $refs));
            }
        }
        
        if (!$stmt || !$stmt->execute()) {
            $informacion = array("1" => "error", "mensaje" => "No se pudo guardar el mensaje. Intente nuevamente.");
            echo json_encode($informacion);
            if ($stmt) { $stmt->close(); }
            $mysqli->close();
            exit;
        }
        
        if (empty($cod_mensaje)) {
            $cod_mensaje = $stmt->insert_id;
            
            $ids_menciones[] = $user;
            // Obtiene todas las menciones anteriores asociadas a esta interconsulta
            $fechaActual= new DateTime();
            $registrosMens= obtenerMensaje(array(
                'cod_interConsultaFK' => $cod_interConsulta,
                'fecha_creacion' => "<= '".$fechaActual->format('Y-m-d H:i:s')."'",
            ), 0);
            $valueMens= end($registrosMens);
            $mencionesTemp= array();
            foreach ($registrosMens as $valueMens) {
                $registrosMenc= obtenerMencion(array(
                    "cod_mensajeFK" => $valueMens['cod_mensaje'],
                    "isLeido" => 0
                ), 0);
    
                foreach ($registrosMenc as $value) {
                    $mencionesTemp[$value['cod_usuarioFK']] = $value['estado'];
                }
            }
            foreach ($mencionesTemp as $key => $value) {
                if ($value != 'inactivo') {
                    $ids_menciones[] = $key;
                }
            }
    
            // Guarda las menciones e incluye al creador
            $ids_menciones = array_unique($ids_menciones);
            foreach ($ids_menciones as $value) {
                if (empty($value)) {continue;}
                // Marca al creador como leido solo si no es mensaje programado
                $fechaActualObj = new DateTime();
                $fechaCreacionObj = new DateTime($fecha_creacion);
                // Verifica si la diferencia en minutos es menor a 10
                $intervalo = $fechaActualObj->diff($fechaCreacionObj);
                $minutosDiferencia = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;
    
                if (($value === $user && ($minutosDiferencia < 10) || $visto_creador)) {
                    abmMencion(null, $value, $cod_mensaje, 1, 'activo');
                } else {
                    abmMencion(null, $value, $cod_mensaje, 0, 'activo');
                }
            }
            
        }

        $stmt->close();
        $mysqli->close();
        return $cod_mensaje;
    }

    function construirFiltrosInterConsulta($filtros= array()) {
        $sqlFiltro= "";
        $sqlFiltroMenciones= "";
        $sqlFiltroMensaje= "";
        $sqlFiltroFechaLimite= "";
        foreach ($filtros as $key => $value) {
            if (empty($value)) {continue;}

            if ($key != 'fecha_limite') {
                if ($sqlFiltro == "") {
                    $sqlFiltro .= "WHERE ";
                } else {
                    $sqlFiltro .= " AND ";
                }
            }
            
            switch ($key) {
                case 'cod_usuarioFK':
                    $sqlFiltro .= "(ic.cod_usuarioFK_create = $value
                        OR EXISTS(select cod_mencion from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mc.cod_usuarioFK = $value)
                        OR EXISTS(SELECT 1 FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo'))";
                    $sqlFiltroMenciones = " AND mc.cod_usuarioFK = $value ";
                    break;
                case 'cod_interConsulta':
                    $sqlFiltro .= "ic.cod_interConsulta = $value";
                    break;
                case 'cod_localFK':
                    $sqlFiltro .= "(ic.cod_localFK = $value
                        OR EXISTS(
                            SELECT 1
                            FROM interconsulta_paciente ip
                            INNER JOIN interconsulta_paciente_venta ipv
                                ON ipv.cod_interConsultaFK = ip.cod_interConsultaFK
                                AND ipv.estado = 'activo'
                            INNER JOIN venta vt
                                ON vt.cod_venta = ipv.cod_ventaFK
                            WHERE ip.cod_interConsultaFK = ic.cod_interConsulta
                                AND ip.estado = 'activo'
                                AND vt.cod_local = $value
                            LIMIT 1
                        ))";
                    break;
                case 'estado':
                    $estadoFiltro = strtolower(trim((string)$value));
                    if ($estadoFiltro == 'abiertos'
                        || (strpos($estadoFiltro, 'pendiente') !== false && strpos($estadoFiltro, 'proceso') !== false)) {
                        $sqlFiltro .= "(ic.estado = 'pendiente' OR ic.estado = 'proceso')";
                    } else if (in_array($estadoFiltro, array('pendiente', 'proceso', 'finalizado', 'inactivo'), true)) {
                        $sqlFiltro .= "ic.estado = '".$estadoFiltro."'";
                    } else {
                        $sqlFiltro .= "1=1";
                    }
                    break;
                case 'tipo':
                    $condicionSubtipo = condicionSubtipoHiloInterConsulta($value);
                    $sqlFiltro .= $condicionSubtipo != "" ? $condicionSubtipo : "1=1";
                    break;
                case 'categoria_principal':
                    $condicionCategoria = condicionCategoriaHiloInterConsulta($value);
                    $sqlFiltro .= $condicionCategoria != "" ? $condicionCategoria : "1=1";
                    break;
                case 'ocultar_inactivos':
                    $sqlFiltro .= "ic.estado != 'inactivo'";
                    break;
                case 'nombre_responsable':
                    $sqlFiltro .= "(SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE '%$value%'";
                    break;
                case 'id_interConsulta_distinto':
                    $sqlFiltro .= "ic.cod_interConsulta <> $value";
                    break;
                case 'nombre_cliente':
                    $sqlFiltro .= "CONCAT(
                        (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK), ' ',
                        IFNULL((SELECT ip.nombre_paciente_snapshot FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                        IFNULL((SELECT ip.cedula FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                        IFNULL((SELECT ip.cod_clienteFK_principal FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), '')
                    ) LIKE '%$value%'";
                    break;
                case 'cod_clienteFK':
                    $sqlFiltro .= "((SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK) = $value
                        OR EXISTS(SELECT 1 FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.cod_clienteFK_principal = $value AND ip.estado = 'activo'))";
                    break;
                case 'usuario_vinculado':
                    $sqlFiltro .= "EXISTS(select cod_mencion from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mc.cod_usuarioFK = $value)";
                    break;
                case 'busqueda_global':
                    $valorBusqueda = addslashes($value);
                    $sqlFiltro .= "(
                        ic.cod_interConsulta LIKE '%$valorBusqueda%' OR
                        ic.asunto LIKE '%$valorBusqueda%' OR
                        ic.estado LIKE '%$valorBusqueda%' OR
                        ic.tipo LIKE '%$valorBusqueda%' OR
                        ic.fecha_creacion LIKE '%$valorBusqueda%' OR
                        (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) LIKE '%$valorBusqueda%' OR
                        (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE '%$valorBusqueda%' OR
                        CONCAT(
                            (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                            (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                            (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK), ' ',
                            IFNULL((SELECT ip.nombre_paciente_snapshot FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                            IFNULL((SELECT ip.cedula FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                            IFNULL((SELECT ip.cod_clienteFK_principal FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), '')
                        ) LIKE '%$valorBusqueda%'
                    )";
                    break;
                case 'fecha_desde':
                    $sqlFiltro .= "ic.fecha_creacion >= '$value 00:00:00'";
                    break;
                case 'fecha_hasta':
                    $sqlFiltro .= "ic.fecha_creacion <= '$value 23:59:59'";
                    break;
                case 'fecha_limite':
                    $sqlFiltroMenciones .= " AND mj.fecha_creacion <= '$value' ";
                    $sqlFiltroMensaje .= " AND mj.fecha_creacion > '$value' ";
                    $sqlFiltroFechaLimite .= " AND mj2.fecha_creacion <= '$value'";
                    break;
                default:
                    if (is_numeric($value)) {
                        $sqlFiltro .= "ic.$key = $value";
                    } else {
                        $sqlFiltro .= "ic.$key like '%$value%'";
                    }
                    break;
            }
        }

        return array($sqlFiltro, $sqlFiltroMenciones, $sqlFiltroMensaje, $sqlFiltroFechaLimite);
    }

    function obtenerCantidadInterConsulta($filtros= array()) {
        $mysqli=conectar_al_servidor();
        asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli);
        list($sqlFiltro) = construirFiltrosInterConsulta($filtros);
        $sql= "SELECT COUNT(*) AS total FROM interconsulta ic $sqlFiltro";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt || !$stmt->execute()) {
            $mensajeError = $stmt ? $stmt->error : mysqli_error($mysqli);
            $informacion =array("1" => "error", "mensaje" => "Error al contar interconsultas: " . $mensajeError, "sql" => $sql);
            echo json_encode($informacion);
            exit;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return isset($row["total"]) ? intval($row["total"]) : 0;
    }

    function obtenerInterConsulta($filtros= array(), $limite= 0) {
        $mysqli=conectar_al_servidor();
        asegurarEstructuraSeguimientoPacienteInterConsulta($mysqli);
        list($sqlFiltro, $sqlFiltroMenciones, $sqlFiltroMensaje, $sqlFiltroFechaLimite) = construirFiltrosInterConsulta($filtros);

        if ($limite === 0 || $limite === '0') {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $condicionCreditoActivoResumen = condicionCreditoActivoHiloInterConsulta("cr_sum");
        $saldoCapitalCreditoResumen = "GREATEST(((IFNULL(cr_sum.Monto,0)-IFNULL(cr_sum.descuento,0))-IFNULL(pg_sum.pago_cuota,0)),0)";
        $saldoInteresCreditoResumen = "GREATEST(((IFNULL(cr_sum.totalinteres,0)+IFNULL(cr_sum.deudaInteres,0))-IFNULL(pg_sum.pago_interes,0)),0)";
        $saldoPendienteCreditoResumen = "(".$saldoCapitalCreditoResumen." + ".$saldoInteresCreditoResumen.")";
        $condicionCreditoPendienteResumen = "(".$saldoPendienteCreditoResumen." > 0)";
        $condicionAgendaActivaResumen = "ag_sum.fecha >= CURDATE()
            AND UPPER(TRIM(IFNULL(ag_sum.estado,''))) NOT IN ('CANCELADO','ATENDIDO')";
        $exprCedulaPlanResumen = seguimientoPacienteSqlNormalizar("pd_plan.cedula");
        $exprCedulaClientePlanResumen = seguimientoPacienteSqlNormalizar("cl_plan.ci_cliente");
        $exprCedulaClienteDirectaResumen = seguimientoPacienteSqlNormalizar("cl_ic.ci_cliente");
        $exprCedulaClienteConflictoResumen = seguimientoPacienteSqlNormalizar("cl_conf.ci_cliente");
        $sqlJoinConflictoCedulaDirecta = "
            LEFT JOIN (
                SELECT ".$exprCedulaClienteConflictoResumen." AS cedula_normalizada,
                    COUNT(DISTINCT cl_conf.cod_cliente) AS total_clientes,
                    GROUP_CONCAT(CONCAT('Cliente ', cl_conf.cod_cliente, ' - ', IFNULL(p_conf.nombre_persona,'')) ORDER BY cl_conf.cod_cliente SEPARATOR '; ') AS detalle_conflicto
                FROM cliente cl_conf
                LEFT JOIN persona p_conf ON p_conf.cod_persona = cl_conf.cod_cliente
                WHERE TRIM(IFNULL(cl_conf.ci_cliente,'')) <> ''
                    AND ".$exprCedulaClienteConflictoResumen." <> ''
                GROUP BY ".$exprCedulaClienteConflictoResumen."
            ) seg_conflicto_ci ON IFNULL(cl_ic.ci_cliente,'') <> ''
                AND seg_conflicto_ci.cedula_normalizada = ".$exprCedulaClienteDirectaResumen;

        $sqlJoinResumenSeguimiento = "
            LEFT JOIN (
                SELECT ipv_loc.cod_interConsultaFK,
                    COUNT(DISTINCT vt_loc.cod_local) AS seguimiento_total_locales,
                    GROUP_CONCAT(DISTINCT l_loc.Nombre ORDER BY l_loc.Nombre SEPARATOR ' / ') AS seguimiento_locales
                FROM interconsulta_paciente_venta ipv_loc
                INNER JOIN venta vt_loc ON vt_loc.cod_venta = ipv_loc.cod_ventaFK
                INNER JOIN local l_loc ON l_loc.cod_local = vt_loc.cod_local
                WHERE ipv_loc.estado = 'activo'
                GROUP BY ipv_loc.cod_interConsultaFK
            ) seg_local ON seg_local.cod_interConsultaFK = ic.cod_interConsulta
            LEFT JOIN (
                SELECT hilo_venta.cod_interConsultaFK,
                    COUNT(DISTINCT cr_sum.idcredito) AS seguimiento_total_creditos,
                    SUM(CASE WHEN ".$condicionCreditoPendienteResumen." THEN 1 ELSE 0 END) AS seguimiento_cuotas_pendientes,
                    SUM(CASE WHEN ".$condicionCreditoPendienteResumen." AND cr_sum.fechapago < CURDATE() THEN 1 ELSE 0 END) AS seguimiento_cuotas_vencidas,
                    IFNULL(MAX(CASE WHEN ".$condicionCreditoPendienteResumen." AND cr_sum.fechapago < CURDATE() THEN DATEDIFF(CURDATE(), cr_sum.fechapago) ELSE 0 END),0) AS seguimiento_dias_mora,
                    IFNULL(SUM(CASE WHEN ".$condicionCreditoPendienteResumen." THEN ".$saldoPendienteCreditoResumen." ELSE 0 END),0) AS seguimiento_saldo_pendiente,
                    IFNULL(MIN(CASE WHEN ".$condicionCreditoPendienteResumen." AND cr_sum.fechapago >= CURDATE() THEN cr_sum.fechapago ELSE NULL END),'') AS seguimiento_proxima_cuota_fecha
                FROM (
                    SELECT ipv_cred.cod_interConsultaFK, ipv_cred.cod_ventaFK
                    FROM interconsulta_paciente_venta ipv_cred
                    WHERE ipv_cred.estado = 'activo'
                    UNION
                    SELECT ic_cred.cod_interConsulta AS cod_interConsultaFK, ic_cred.cod_ventaFK
                    FROM interconsulta ic_cred
                    WHERE IFNULL(ic_cred.cod_ventaFK,0) > 0
                ) hilo_venta
                INNER JOIN credito cr_sum ON cr_sum.cod_venta = hilo_venta.cod_ventaFK
                LEFT JOIN (
                    SELECT pg.cod_creditoFK,
                        SUM(CASE WHEN pg.tipo = 'Pago Cuota' THEN IFNULL(pg.Monto,0) ELSE 0 END) AS pago_cuota,
                        SUM(CASE WHEN pg.tipo = 'Interes' THEN IFNULL(pg.Monto,0) ELSE 0 END) AS pago_interes
                    FROM pago pg
                    WHERE pg.tipo IN ('Pago Cuota','Interes')
                    GROUP BY pg.cod_creditoFK
                ) pg_sum ON pg_sum.cod_creditoFK = cr_sum.idcredito
                WHERE ".$condicionCreditoActivoResumen."
                GROUP BY hilo_venta.cod_interConsultaFK
            ) seg_credito ON seg_credito.cod_interConsultaFK = ic.cod_interConsulta
            LEFT JOIN (
                SELECT agenda_hilo.cod_interConsultaFK,
                    COUNT(DISTINCT agenda_hilo.id_agenda) AS seguimiento_citas_futuras,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(agenda_hilo.id_agenda ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_id,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(agenda_hilo.fecha ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_fecha,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(TIME_FORMAT(agenda_hilo.hora_inicio, '%H:%i') ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_hora,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(IFNULL(agenda_hilo.estado,'') ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_estado,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(IFNULL(agenda_hilo.motivo,'') ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_motivo,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(IFNULL(p_prof.nombre_persona,'') ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_profesional,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(IFNULL(p_creador_ag.nombre_persona,'') ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_creador,
                    IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(IFNULL(u_creador_ag.url,'') ORDER BY agenda_hilo.fecha ASC, agenda_hilo.hora_inicio ASC SEPARATOR '|'), '|', 1),'') AS seguimiento_proxima_cita_creador_url
                FROM (
                    SELECT ipv_ag.cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta_paciente_venta ipv_ag
                    INNER JOIN agenda ag_sum ON ag_sum.cod_ventaFK = ipv_ag.cod_ventaFK
                    WHERE ipv_ag.estado = 'activo' AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ip_ag.cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta_paciente ip_ag
                    INNER JOIN agenda ag_sum ON ag_sum.id_paciente = ip_ag.cod_clienteFK_principal
                    WHERE ip_ag.estado = 'activo' AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ipv_ag.cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta_paciente_venta ipv_ag
                    INNER JOIN venta vt_ag ON vt_ag.cod_venta = ipv_ag.cod_ventaFK
                    INNER JOIN agenda ag_sum ON ag_sum.id_paciente = vt_ag.cod_clienteFK
                    WHERE ipv_ag.estado = 'activo' AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ic_ag.cod_interConsulta AS cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta ic_ag
                    INNER JOIN agenda ag_sum ON ag_sum.cod_ventaFK = ic_ag.cod_ventaFK
                    WHERE IFNULL(ic_ag.cod_ventaFK,0) > 0 AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ic_ag.cod_interConsulta AS cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta ic_ag
                    INNER JOIN venta vt_ag ON vt_ag.cod_venta = ic_ag.cod_ventaFK
                    INNER JOIN agenda ag_sum ON ag_sum.id_paciente = vt_ag.cod_clienteFK
                    WHERE IFNULL(ic_ag.cod_ventaFK,0) > 0 AND ".$condicionAgendaActivaResumen."
                ) agenda_hilo
                LEFT JOIN persona p_prof ON p_prof.cod_persona = agenda_hilo.id_profesional
                LEFT JOIN persona p_creador_ag ON p_creador_ag.cod_persona = agenda_hilo.creado_por
                LEFT JOIN usuario u_creador_ag ON u_creador_ag.cod_usuario = agenda_hilo.creado_por
                GROUP BY agenda_hilo.cod_interConsultaFK
            ) seg_agenda ON seg_agenda.cod_interConsultaFK = ic.cod_interConsulta
            LEFT JOIN (
                SELECT ip_plan.cod_interConsultaFK,
                    COUNT(DISTINCT pd_plan.id) AS seguimiento_planes_definitivos,
                    GROUP_CONCAT(DISTINCT pd_plan.estado ORDER BY pd_plan.estado SEPARATOR ', ') AS seguimiento_estado_planes
                FROM interconsulta_paciente ip_plan
                INNER JOIN plan_definitivo_tratamiento pd_plan
                LEFT JOIN cliente cl_plan ON cl_plan.cod_cliente = pd_plan.paciente_id
                WHERE ip_plan.estado = 'activo'
                    AND pd_plan.activo = 1
                    AND (".$exprCedulaPlanResumen." = ip_plan.cedula_normalizada OR ".$exprCedulaClientePlanResumen." = ip_plan.cedula_normalizada)
                GROUP BY ip_plan.cod_interConsultaFK
            ) seg_plan ON seg_plan.cod_interConsultaFK = ic.cod_interConsulta
            LEFT JOIN (
                SELECT hilo_venta_reciente.cod_interConsultaFK,
                    COUNT(DISTINCT CASE
                        WHEN DATE(hilo_venta_reciente.fecha_venta) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        THEN hilo_venta_reciente.cod_ventaFK
                        ELSE NULL
                    END) AS seguimiento_ventas_recientes,
                    IFNULL(MAX(CASE
                        WHEN DATE(hilo_venta_reciente.fecha_venta) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        THEN hilo_venta_reciente.fecha_venta
                        ELSE NULL
                    END),'') AS seguimiento_ultima_venta_reciente
                FROM (
                    SELECT ipv_new.cod_interConsultaFK, ipv_new.cod_ventaFK, vt_new.fecha_venta
                    FROM interconsulta_paciente_venta ipv_new
                    INNER JOIN venta vt_new ON vt_new.cod_venta = ipv_new.cod_ventaFK
                    WHERE ipv_new.estado = 'activo'
                        AND IFNULL(vt_new.cod_clienteFK,0) <> 7
                        AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones cn_new WHERE cn_new.cod_venta = vt_new.cod_venta LIMIT 1),0) = 0
                    UNION
                    SELECT ic_new.cod_interConsulta AS cod_interConsultaFK, ic_new.cod_ventaFK, vt_new.fecha_venta
                    FROM interconsulta ic_new
                    INNER JOIN venta vt_new ON vt_new.cod_venta = ic_new.cod_ventaFK
                    WHERE IFNULL(ic_new.cod_ventaFK,0) > 0
                        AND IFNULL(vt_new.cod_clienteFK,0) <> 7
                        AND IFNULL((SELECT COUNT(fecha) FROM cancelaciones cn_new WHERE cn_new.cod_venta = vt_new.cod_venta LIMIT 1),0) = 0
                ) hilo_venta_reciente
                GROUP BY hilo_venta_reciente.cod_interConsultaFK
            ) seg_venta_reciente ON seg_venta_reciente.cod_interConsultaFK = ic.cod_interConsulta";

        // Se separa la tabla venta de la interconsulta ya que este es opcional
        $sql= "SELECT ic.*, 
            (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) AS nombre_local,
            COALESCE(ip_seg.cod_clienteFK_principal, vt_ic.cod_clienteFK) AS cod_clienteFK,
            vt_ic.num_factura AS num_factura,
            vt_ic.apodo AS apodo_venta,
            (SELECT SUM(monto) FROM gastos WHERE cod_interConsultaFK = ic.cod_interConsulta) AS total_gastos,
            COALESCE(NULLIF(ip_seg.nombre_paciente_snapshot, ''), p_ic.nombre_persona) as nombre_persona,
            (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) as nombre_persona_creador,
            COALESCE(NULLIF(ip_seg.cedula, ''), cl_ic.ci_cliente) as cedula,
            IF(ip_seg.id IS NULL,0,1) AS esSeguimientoPaciente,
            IF(ip_seg.id IS NULL, IF(IFNULL(seg_conflicto_ci.total_clientes,0) > 1,1,0), IFNULL(ip_seg.estado_conflicto,0)) AS seguimiento_conflicto,
            IF(ip_seg.id IS NULL, IF(IFNULL(seg_conflicto_ci.total_clientes,0) > 1,IFNULL(seg_conflicto_ci.detalle_conflicto,''),''), IFNULL(ip_seg.detalle_conflicto,'')) AS seguimiento_detalle_conflicto,
            IFNULL(ip_seg.ventas_sin_plan_madre,0) AS ventas_sin_plan_madre,
            IFNULL(ip_seg.total_ventas,0) AS total_ventas_paciente,
            IFNULL(ip_seg.total_planes_madre,0) AS total_planes_madre,
            IFNULL(seg_local.seguimiento_total_locales,0) AS seguimiento_total_locales,
            IFNULL(seg_local.seguimiento_locales,'') AS seguimiento_locales,
            IFNULL(seg_credito.seguimiento_total_creditos,0) AS seguimiento_total_creditos,
            IFNULL(seg_credito.seguimiento_cuotas_pendientes,0) AS seguimiento_cuotas_pendientes,
            IFNULL(seg_credito.seguimiento_cuotas_vencidas,0) AS seguimiento_cuotas_vencidas,
            IFNULL(seg_credito.seguimiento_dias_mora,0) AS seguimiento_dias_mora,
            IFNULL(seg_credito.seguimiento_saldo_pendiente,0) AS seguimiento_saldo_pendiente,
            IFNULL(seg_credito.seguimiento_proxima_cuota_fecha,'') AS seguimiento_proxima_cuota_fecha,
            IFNULL(seg_agenda.seguimiento_citas_futuras,0) AS seguimiento_citas_futuras,
            IFNULL(seg_agenda.seguimiento_proxima_cita_id,'') AS seguimiento_proxima_cita_id,
            IFNULL(seg_agenda.seguimiento_proxima_cita_fecha,'') AS seguimiento_proxima_cita_fecha,
            IFNULL(seg_agenda.seguimiento_proxima_cita_hora,'') AS seguimiento_proxima_cita_hora,
            IFNULL(seg_agenda.seguimiento_proxima_cita_estado,'') AS seguimiento_proxima_cita_estado,
            IFNULL(seg_agenda.seguimiento_proxima_cita_motivo,'') AS seguimiento_proxima_cita_motivo,
            IFNULL(seg_agenda.seguimiento_proxima_cita_profesional,'') AS seguimiento_proxima_cita_profesional,
            IFNULL(seg_agenda.seguimiento_proxima_cita_creador,'') AS seguimiento_proxima_cita_creador,
            IFNULL(seg_agenda.seguimiento_proxima_cita_creador_url,'') AS seguimiento_proxima_cita_creador_url,
            IFNULL(seg_plan.seguimiento_planes_definitivos,0) AS seguimiento_planes_definitivos,
            IFNULL(seg_plan.seguimiento_estado_planes,'') AS seguimiento_estado_planes,
            IFNULL(seg_venta_reciente.seguimiento_ventas_recientes,0) AS seguimiento_ventas_recientes,
            IFNULL(seg_venta_reciente.seguimiento_ultima_venta_reciente,'') AS seguimiento_ultima_venta_reciente,
            (SELECT COUNT(*) FROM gastos WHERE estado IN ('solicitado','pendiente') AND fecha <= CURDATE() AND cod_interConsultaFK = ic.cod_interConsulta) AS cantGastosPendientes,
            (SELECT COUNT(idgastos) FROM gastos g WHERE g.cod_interConsultaFK = ic.cod_interConsulta) AS cantAsociadoGastos,
            (SELECT COUNT(cod_mensaje) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta) AS cantMensajes,
            (SELECT COUNT(cod_mensaje) FROM mensaje mj WHERE mj.cod_interConsultaFK = ic.cod_interConsulta and estado = 'activo' $sqlFiltroMensaje) AS cantMensajesProgramados,
            (SELECT COUNT(mc.cod_mencion)
                FROM menciones mc
                JOIN mensaje mj 
                ON mc.cod_mensajeFK = mj.cod_mensaje
                WHERE mc.isLeido = 0
                AND mj.cod_interConsultaFK = ic.cod_interConsulta
                AND mj.fecha_creacion = (
                    SELECT MAX(mj2.fecha_creacion)
                    FROM mensaje mj2
                    WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta AND estado = 'activo' $sqlFiltroFechaLimite
                )
            ) AS cantMensajesNoLeidosOtrosUsuarios,
            (SELECT COUNT(cod_mencion) from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mc.isLeido = 0 $sqlFiltroMenciones AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mj.fecha_creacion = (
                SELECT MAX(mj2.fecha_creacion)
                FROM mensaje mj2
                WHERE mj2.cod_interConsultaFK = ic.cod_interConsulta AND estado = 'activo' $sqlFiltroFechaLimite
            )) AS cantMensajesNoLeidos
            from interconsulta ic
            LEFT JOIN interconsulta_paciente ip_seg
                ON ip_seg.cod_interConsultaFK = ic.cod_interConsulta
                AND ip_seg.estado = 'activo'
            LEFT JOIN venta vt_ic ON vt_ic.cod_venta = ic.cod_ventaFK
            LEFT JOIN cliente cl_ic ON cl_ic.cod_cliente = vt_ic.cod_clienteFK
            ".$sqlJoinConflictoCedulaDirecta."
            LEFT JOIN persona p_ic ON p_ic.cod_persona = vt_ic.cod_clienteFK
            ".$sqlJoinResumenSeguimiento."
            $sqlFiltro
            ORDER BY cantMensajesNoLeidos DESC,
            FIELD(ic.estado, 'proceso', 'pendiente', 'finalizado', 'inactivo'),
            ic.cod_interConsulta DESC $limite";



        set_time_limit(2147483647);

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $informacion = array(
                "1" => "error",
                "mensaje" => "Error al preparar la consulta de hilos: " . mysqli_error($mysqli)
            );
            echo json_encode($informacion);
            exit;
        }
        if ( !$stmt->execute()) {
            $informacion =array("1" => "error", "mensaje" => "Error: " . $stmt->error, "sql" => $sql);
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

    function abmInterConsulta($cod_interConsulta, $asunto, $observacion, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_usuarioFK_edit, $cod_localFK, $monto_limite) {
        $mysqli = conectar_al_servidor();
        if (empty($cod_interConsulta)) {
            $sql = "INSERT INTO interconsulta (asunto, observacion, estado, tipo, cod_ventaFK, cod_usuarioFK_create, fecha_creacion, cod_localFK, monto_limite) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssssiiii',$asunto, $observacion, $estado, $tipo, $cod_ventaFK,$cod_usuarioFK_create, $cod_localFK, $monto_limite);
        } else {
            // Obtiene los datos de la interconsulta antes que sea modificada
            $interconsulta_original= obtenerInterConsulta(array(
                "cod_interConsulta" => $cod_interConsulta
            ), 1);
            // Una copia de los datos nuevos con su encabezado
            $nuevos_datos= array();

            $parametros = array();
            $atributos = "";
            $ss = "";

            $atributos .= "asunto= ?";
            $ss .= "s";
            $parametros[] = $asunto;
            $nuevos_datos['asunto'] = $asunto;

            $atributos .= ", cod_localFK= ?";
            $ss .= "i";
            $parametros[] = $cod_localFK;
            $nuevos_datos['cod_localFK'] = $cod_localFK;

            // Datos para auditoria
            $fechaActual= new DateTime();
            $atributos .= ",cod_usuarioFK_edit= ?";
            $ss .= "i";
            $parametros[] = $cod_usuarioFK_edit;
            $atributos .= ",fecha_edit= ?";
            $ss .= "s";
            $parametros[] = $fechaActual->format('Y-m-d H:i:s');

            // Datos a modificar
            if ($estado != NULL) {
                $atributos .= ", estado= ?";
                $ss .= "s";
                $parametros[] = $estado;
                $nuevos_datos['estado'] = $estado;
            }
            if ($observacion != NULL) {
                $atributos .= ", observacion= ?";
                $ss .= "s";
                $parametros[] = $observacion;
                $nuevos_datos['observacion'] = $observacion;
            }
            if ($tipo != NULL) {
                $atributos .= ", tipo= ?";
                $ss .= "s";
                $parametros[] = $tipo;
                $nuevos_datos['tipo'] = $tipo;
            }
            if ($cod_ventaFK != NULL) {
                $atributos .= ", cod_ventaFK= ?";
                $ss .= "i";
                $parametros[] = $cod_ventaFK;
                $nuevos_datos['cod_ventaFK'] = $cod_ventaFK;
            }
            if ($monto_limite != NULL) {
                $atributos .= ", monto_limite= ?";
                $ss .= "i";
                $parametros[] = $monto_limite;
                $nuevos_datos['monto_limite'] = $monto_limite;
            }
            
            $parametros[] = $cod_interConsulta;
            $ss .= "i";

            $sql= "UPDATE interconsulta SET $atributos WHERE cod_interConsulta = ?";
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

        if (empty($cod_interConsulta)) {
            // Se inserto una nueva interconsulta
            $cod_interConsulta = $stmt->insert_id;
        } else {
            $mensaje = 'El usuario <b class="menciones-mensaje" id="'.$cod_usuarioFK_edit.'" title="Mencion a @nombre">@nombre</b>&nbsp; modifico';
            $mensajeDatosCambiados= "";
            // Se actualizo la interconsulta, se compara los datos para registrar los cambios en la tabla mensaje
            foreach ($nuevos_datos as $key => $value) {
                if ($interconsulta_original[0][$key] != $value) {
                    // Registrar cambio en un mensaje
                    $mensajeDatosCambiados .= ' el campo '.$key.' de '.$interconsulta_original[0][$key].' a '.$value.', ';
                }
            }

            if ($mensajeDatosCambiados != "") {
                $mensaje .= substr($mensajeDatosCambiados, 0, -2).'.';
                $fechaActual = new DateTime();
                $fechaActual = $fechaActual->format('Y-m-d H:i:s');
                abmMensaje(null, $mensaje, $fechaActual, $cod_interConsulta, $cod_usuarioFK_edit, NULL);
            }
        }

        $tipoProyectoGasto= $tipo;
        if (empty($tipoProyectoGasto) && isset($interconsulta_original[0]['tipo'])) {
            $tipoProyectoGasto= $interconsulta_original[0]['tipo'];
        }
        if (function_exists('obtenerOCrearProyectoGastoParaInterConsulta') && obtenerCategoriaPrincipalHilo($tipoProyectoGasto) == 'pagos_egresos') {
            obtenerOCrearProyectoGastoParaInterConsulta($cod_interConsulta, $asunto);
        }

        $stmt->close();
        return $cod_interConsulta;
    }

    if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
        $operacion = $_POST['accion'];
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        verificarOperacionInterConsulta($operacion);
    }
?>

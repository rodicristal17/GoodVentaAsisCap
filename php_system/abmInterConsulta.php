<?php
    require_once("conexion.php");
    require_once("solicitud_eliminado_helper.php");
    include_once("verificar_navegador.php");
    include_once("buscar_nivel.php");
    include_once("classTable.php");
    include_once("subir_foto_base64.php");
    require_once("interconsulta_acceso_helper.php");
    require_once("interconsulta_lecturas_helper.php");
    include_once("abmgasto.php");
    require_once("abmDictamen.php");
    require_once("interconsulta_seguimiento_paciente_helper.php");
    require_once("interconsulta_seguimiento_programado_helper.php");
    require_once("centro_facturas_helper.php");
    require_once("interconsulta_operaciones_helper.php");
    require_once("interconsulta_fusion_helper.php");

    date_default_timezone_set('America/Asuncion');

    function registrarMedicionOperacionInterConsulta($operacion, $inicio, $umbralSegundos= 2.0) {
        $operacion = preg_replace('/[^a-zA-Z0-9_\/-]/', '', (string)$operacion);
        $inicio = (float)$inicio;
        register_shutdown_function(function () use ($operacion, $inicio, $umbralSegundos) {
            $duracion = microtime(true) - $inicio;
            $ultimoError = error_get_last();
            $esFatal = $ultimoError && in_array($ultimoError['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true);
            if ($duracion < $umbralSegundos && !$esFatal) {
                return;
            }
            $mensaje = sprintf(
                '[InterConsultaPerformance] accion=%s duracion=%.3fs memoria_pico=%.2fMB pid=%s estado=%s',
                $operacion !== '' ? $operacion : 'sin_accion',
                $duracion,
                memory_get_peak_usage(true) / 1048576,
                function_exists('getmypid') ? getmypid() : 'n/a',
                $esFatal ? 'fatal' : 'lenta'
            );
            if ($esFatal) {
                $mensaje .= ' error_tipo='.intval($ultimoError['type']);
            }
            error_log($mensaje);
        });
    }

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

    function normalizarEnteroFiltroInterConsulta($valor, $permitirVacio= true) {
        $valor = trim((string)$valor);
        if ($valor === '' && $permitirVacio) {
            return null;
        }
        if (!preg_match('/^\d+$/', $valor)) {
            return -1;
        }
        return intval($valor);
    }

    function esFechaFiltroInterConsultaValida($valor, $formato= 'Y-m-d') {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return false;
        }
        $fecha = DateTime::createFromFormat('!'.$formato, $valor);
        return $fecha instanceof DateTime && $fecha->format($formato) === $valor;
    }

    function literalTextoSqlInterConsulta($valor) {
        // Un literal hexadecimal evita interpolar comillas o metacaracteres SQL
        // y mantiene la codificacion legacy recibida por este modulo.
        $valor = (string)$valor;
        return $valor === '' ? "''" : '0x'.bin2hex($valor);
    }

    function limitarTextoFiltroInterConsulta($valor, $maximo= 160) {
        $valor = trim((string)$valor);
        if (function_exists('mb_substr')) {
            return mb_substr($valor, 0, intval($maximo), 'ISO-8859-1');
        }
        return substr($valor, 0, intval($maximo));
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
                'tipos' => array('pagos', 'pago', 'compras', 'compra', 'egresos', 'egreso', 'colaborador', 'rrhh')
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

    /**
     * Garantiza un unico hilo operativo para cada usuario activo con nombre,
     * cargo y local. Las cuentas tecnicas sin cargo no se aprovisionan.
     *
     * La fila de usuario se bloquea antes de consultar el vinculo para que dos
     * pestañas concurrentes no creen hilos duplicados.
     */
    function aprovisionarHilosColaboradoresActivosInterConsulta($codUsuarioAuditoria) {
        $codUsuarioAuditoria= intval($codUsuarioAuditoria);
        if ($codUsuarioAuditoria <= 0) { return array('creados' => 0, 'normalizados' => 0); }

        $mysqli= conectar_al_servidor();
        if (!interconsultaLecturasTablaExiste($mysqli, 'funcionario_hilo_principal')) {
            $mysqli->close();
            return array('creados' => 0, 'normalizados' => 0);
        }
        $sql= "SELECT u.cod_usuario,u.cod_localFK,u.tipo,p.nombre_persona
            FROM usuario u
            INNER JOIN persona p ON p.cod_persona=u.cod_usuario
            WHERE u.estado='Activo' AND IFNULL(u.cod_localFK,0)>0
              AND TRIM(IFNULL(u.tipo,''))<>'' AND TRIM(IFNULL(p.nombre_persona,''))<>''
              AND (
                (SELECT COUNT(*) FROM funcionario_hilo_principal fh_total
                    WHERE fh_total.cod_usuarioFK=u.cod_usuario AND fh_total.estado='activo')<>1
                OR NOT EXISTS(
                    SELECT 1 FROM funcionario_hilo_principal fh_vigente
                    INNER JOIN interconsulta ic_vigente ON ic_vigente.cod_interConsulta=fh_vigente.cod_interConsultaFK
                    WHERE fh_vigente.cod_usuarioFK=u.cod_usuario AND fh_vigente.estado='activo'
                      AND LOWER(TRIM(IFNULL(ic_vigente.estado,'')))<>'inactivo'
                    LIMIT 1
                )
                OR EXISTS(
                    SELECT 1 FROM funcionario_hilo_principal fh_tipo
                    INNER JOIN interconsulta ic_tipo ON ic_tipo.cod_interConsulta=fh_tipo.cod_interConsultaFK
                    WHERE fh_tipo.cod_usuarioFK=u.cod_usuario AND fh_tipo.estado='activo'
                      AND LOWER(TRIM(IFNULL(ic_tipo.estado,'')))<>'inactivo'
                      AND LOWER(TRIM(IFNULL(ic_tipo.tipo,'')))<>'colaborador'
                    LIMIT 1
                )
              )
            ORDER BY u.cod_usuario ASC";
        $resultado= $mysqli->query($sql);
        $funcionarios= array();
        while ($resultado && $fila= $resultado->fetch_assoc()) { $funcionarios[]= $fila; }
        if ($resultado) { $resultado->free(); }
        $creados= 0;
        $normalizados= 0;

        foreach ($funcionarios as $funcionario) {
            $codFuncionario= intval($funcionario['cod_usuario']);
            $codLocal= intval($funcionario['cod_localFK']);
            if ($codFuncionario <= 0 || $codLocal <= 0) { continue; }
            $mysqli->begin_transaction();
            try {
                $stmt= $mysqli->prepare("SELECT cod_usuario FROM usuario WHERE cod_usuario=? AND estado='Activo' FOR UPDATE");
                if (!$stmt) { throw new Exception('No se pudo bloquear el colaborador.'); }
                $stmt->bind_param('i', $codFuncionario);
                $stmt->execute();
                $vigente= $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$vigente) { $mysqli->rollback(); continue; }

                $stmt= $mysqli->prepare("SELECT fh.id,fh.cod_interConsultaFK,ic.estado AS hilo_estado,ic.tipo
                    FROM funcionario_hilo_principal fh
                    LEFT JOIN interconsulta ic ON ic.cod_interConsulta=fh.cod_interConsultaFK
                    WHERE fh.cod_usuarioFK=? AND fh.estado='activo'
                    ORDER BY fh.id DESC FOR UPDATE");
                if (!$stmt) { throw new Exception('No se pudo consultar el hilo del colaborador.'); }
                $stmt->bind_param('i', $codFuncionario);
                $stmt->execute();
                $resVinculos= $stmt->get_result();
                $vinculos= array();
                while ($vinculo= $resVinculos->fetch_assoc()) { $vinculos[]= $vinculo; }
                $stmt->close();

                $principal= null;
                foreach ($vinculos as $indice => $vinculo) {
                    if ($indice === 0 && intval($vinculo['cod_interConsultaFK']) > 0 && strtolower((string)$vinculo['hilo_estado']) !== 'inactivo') {
                        $principal= $vinculo;
                        continue;
                    }
                    $idVinculo= intval($vinculo['id']);
                    $motivoDuplicado= 'Normalizacion automatica: se conserva un unico hilo vigente por colaborador.';
                    $stmt= $mysqli->prepare("UPDATE funcionario_hilo_principal SET estado='inactivo',motivo_cambio=?,cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE id=?");
                    if (!$stmt) { throw new Exception('No se pudo normalizar un vinculo duplicado.'); }
                    $stmt->bind_param('sii', $motivoDuplicado, $codUsuarioAuditoria, $idVinculo);
                    if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo normalizar un vinculo duplicado.'); }
                    $stmt->close();
                }

                if ($principal) {
                    $codHilo= intval($principal['cod_interConsultaFK']);
                    if (normalizarTipoHiloInterConsulta($principal['tipo']) !== 'colaborador') {
                        $stmt= $mysqli->prepare("UPDATE interconsulta SET tipo='colaborador',cod_usuarioFK_edit=?,fecha_edit=NOW() WHERE cod_interConsulta=?");
                        if (!$stmt) { throw new Exception('No se pudo ubicar el hilo en Pagos y Egresos.'); }
                        $stmt->bind_param('ii', $codUsuarioAuditoria, $codHilo);
                        if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo ubicar el hilo en Pagos y Egresos.'); }
                        $stmt->close();
                        $normalizados++;
                    }
                    interconsultaLecturasSincronizarParticipantesHilo($mysqli, $codHilo, date('Y-m-d H:i:s'));
                    $mysqli->commit();
                    continue;
                }

                $nombre= trim((string)$funcionario['nombre_persona']);
                $cargo= trim((string)$funcionario['tipo']);
                $asunto= 'Colaborador - '.$nombre;
                $asunto= function_exists('mb_substr') ? mb_substr($asunto, 0, 100, 'ISO-8859-1') : substr($asunto, 0, 100);
                $observacion= 'Hilo laboral principal conectado al perfil y a las marcaciones de asistencia.';
                $estado= 'proceso';
                $tipo= 'colaborador';
                $stmt= $mysqli->prepare("INSERT INTO interconsulta
                    (asunto,observacion,estado,tipo,cod_ventaFK,cod_usuarioFK_create,fecha_creacion,cod_localFK,monto_limite)
                    VALUES (?,?,?,?,NULL,?,NOW(),?,NULL)");
                if (!$stmt) { throw new Exception('No se pudo preparar el hilo del colaborador.'); }
                $stmt->bind_param('ssssii', $asunto, $observacion, $estado, $tipo, $codFuncionario, $codLocal);
                if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo crear el hilo del colaborador.'); }
                $codHilo= intval($stmt->insert_id);
                $stmt->close();

                $motivo= 'aprovisionamiento_automatico';
                $stmt= $mysqli->prepare("INSERT INTO funcionario_hilo_principal
                    (cod_usuarioFK,cod_interConsultaFK,estado,observacion,motivo_cambio,cod_usuarioFK_vinculo,fecha_vinculacion)
                    VALUES (?,?,'activo',?,?,?,NOW())");
                if (!$stmt) { throw new Exception('No se pudo vincular el hilo del colaborador.'); }
                $stmt->bind_param('iissi', $codFuncionario, $codHilo, $observacion, $motivo, $codUsuarioAuditoria);
                if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo vincular el hilo del colaborador.'); }
                $stmt->close();

                $contenido= 'Hilo laboral creado para '.trim($nombre).'. Cargo o funcion: '.trim($cargo).'.';
                $stmt= $mysqli->prepare("INSERT INTO mensaje (contenido,fecha_creacion,cod_interConsultaFK,cod_usuarioFK,cod_dictamenFK)
                    VALUES (?,NOW(),?,NULL,NULL)");
                if (!$stmt) { throw new Exception('No se pudo registrar la auditoria del hilo.'); }
                $stmt->bind_param('si', $contenido, $codHilo);
                if (!$stmt->execute()) { $stmt->close(); throw new Exception('No se pudo registrar la auditoria del hilo.'); }
                $stmt->close();
                interconsultaLecturasSincronizarParticipantesHilo($mysqli, $codHilo, date('Y-m-d H:i:s'));
                $mysqli->commit();
                $creados++;
            } catch (Exception $e) {
                $mysqli->rollback();
                error_log('[HilosColaborador] usuario='.$codFuncionario.' error='.$e->getMessage());
            }
        }
        $mysqli->close();
        return array('creados' => $creados, 'normalizados' => $normalizados);
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
            case 'buscarResumenHilos':
                // Resumen liviano para la actualizacion periodica. No renderiza
                // filas ni ejecuta el enriquecimiento completo del listado.
                $filtrosResumenHilos= array(
                    'cod_usuarioFK' => intval($user),
                    'ocultar_inactivos' => true,
                    'fecha_limite' => $fechaActual->format('Y-m-d H:i:s')
                );
                $conteosResumenHilos= obtenerConteosCategoriasInterConsulta($filtrosResumenHilos);
                $noLeidosResumenHilos= interconsultaLecturasEstructuraDisponible()
                    ? interconsultaLecturasTotalUsuario(intval($user)) : 0;
                $alertasResumenHilos= seguimientoProgramadoObtenerResumenAlertas(intval($user));
                echo json_encode(array(
                    '1' => 'exito',
                    '2' => array(
                        'conteos' => $conteosResumenHilos,
                        'no_leidos' => $noLeidosResumenHilos,
                        'alertas' => $alertasResumenHilos
                    )
                ));
                break;
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
                $cod_interConsulta= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_interConsulta']) ? $_POST['cod_interConsulta'] : '');
                $asunto= isset($_POST['asunto']) ? limitarTextoFiltroInterConsulta(mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8')) : null;
                $estado= isset($_POST['estado']) ? mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8') : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $mencion= isset($_POST['mencion']) ? mb_convert_encoding((string)($_POST['mencion']), 'ISO-8859-1', 'UTF-8') : false;
                $cod_ventaFK= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_ventaFK']) ? $_POST['cod_ventaFK'] : '');
                // El alcance y las menciones siempre pertenecen a la sesion
                // autenticada. No se acepta suplantar otro usuario por AJAX.
                $cod_usuarioFK= intval($user);
                $nombre_cliente= isset($_POST['nombre_cliente']) ? limitarTextoFiltroInterConsulta(mb_convert_encoding((string)($_POST['nombre_cliente']), 'ISO-8859-1', 'UTF-8')) : null;
                $nombre_responsable= isset($_POST['nombre_responsable']) ? limitarTextoFiltroInterConsulta(mb_convert_encoding((string)($_POST['nombre_responsable']), 'ISO-8859-1', 'UTF-8')) : null;
                $ocultar_inactivos= isset($_POST['ocultar_inactivos']) ? mb_convert_encoding((string)($_POST['ocultar_inactivos']), 'ISO-8859-1', 'UTF-8') : null;
                $usuario_vinculado= normalizarEnteroFiltroInterConsulta(isset($_POST['usuario_vinculado']) ? $_POST['usuario_vinculado'] : '');
                $cod_localFK= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_localFK']) ? $_POST['cod_localFK'] : '');
                $busqueda_global= isset($_POST['busqueda_global']) ? limitarTextoFiltroInterConsulta(mb_convert_encoding((string)($_POST['busqueda_global']), 'ISO-8859-1', 'UTF-8')) : null;
                $fecha_desde= isset($_POST['fecha_desde']) ? mb_convert_encoding((string)($_POST['fecha_desde']), 'ISO-8859-1', 'UTF-8') : null;
                $fecha_hasta= isset($_POST['fecha_hasta']) ? mb_convert_encoding((string)($_POST['fecha_hasta']), 'ISO-8859-1', 'UTF-8') : null;
                $categoria_principal= isset($_POST['categoria_principal']) ? mb_convert_encoding((string)($_POST['categoria_principal']), 'ISO-8859-1', 'UTF-8') : 'pagos_egresos';
                $filtro_menciones= isset($_POST['filtro_menciones']) ? strtolower(trim((string)$_POST['filtro_menciones'])) : '';
                if (!in_array($filtro_menciones, array('', 'pendientes', 'todas'), true)) {
                    $filtro_menciones= '';
                }
                if ($cod_localFK !== null && ($cod_localFK <= 0
                    || !interconsultaAccesoUsuarioPuedeUsarLocal($user, intval($cod_localFK)))) {
                    // Conserva el contrato del listado, pero impide consultar
                    // silenciosamente un local fuera del alcance del usuario.
                    $cod_localFK= -1;
                }
                $codigos_hilos= array();
                if (isset($_POST['codigos_hilos'])) {
                    foreach (explode(',', (string)$_POST['codigos_hilos']) as $codigoHilo) {
                        $codigoHilo= intval($codigoHilo);
                        if ($codigoHilo > 0) { $codigos_hilos[$codigoHilo]= $codigoHilo; }
                    }
                    $codigos_hilos= array_values(array_slice($codigos_hilos, 0, 60, true));
                }

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
                    'filtro_menciones'=> $filtro_menciones,
                    'codigos_hilos'=> $codigos_hilos,
                    'fecha_limite' => $fechaActual->format('Y-m-d H:i:s')
                );

                $limiteSolicitado= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 30;
                $esConsultaAuxiliar= trim((string)$limiteSolicitado) === '0';
                $limite= $esConsultaAuxiliar ? '60' : normalizarLimiteListadoInterConsulta($limiteSolicitado, 30);

                if ($funt == 'buscarInterConsultas' && $categoria_principal === 'pagos_egresos') {
                    aprovisionarHilosColaboradoresActivosInterConsulta($user);
                }

                if ($funt == 'buscarInterConsultasEnriquecidos') {
                    if (count($codigos_hilos) > 0) {
                        $limite= (string)count($codigos_hilos);
                    }
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
            case 'buscarDetalleCuotasInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $cod_venta= isset($_POST['cod_venta']) ? intval($_POST['cod_venta']) : 0;
                $resultado= buscarDetalleCuotasInterConsulta($cod_interConsulta, $cod_venta, $user);
                echo json_encode(array(
                    "1" => (!empty($resultado['ok']) ? "exito" : (isset($resultado['codigo']) ? $resultado['codigo'] : "error")),
                    "2" => !empty($resultado['ok']) ? $resultado['datos'] : array(
                        "codigo" => isset($resultado['codigo']) ? $resultado['codigo'] : "error",
                        "mensaje" => isset($resultado['mensaje']) ? $resultado['mensaje'] : "No se pudo consultar el detalle de cuotas."
                    )
                ));
                break;
            case 'buscarMetricasGestionInterConsulta':
                $fecha_desde= isset($_POST['fecha_desde']) ? trim((string)$_POST['fecha_desde']) : '';
                $fecha_hasta= isset($_POST['fecha_hasta']) ? trim((string)$_POST['fecha_hasta']) : '';
                $cod_local= isset($_POST['cod_localFK']) ? intval($_POST['cod_localFK']) : 0;
                $resultado= obtenerMetricasGestionInterConsulta($user, $fecha_desde, $fecha_hasta, $cod_local);
                echo json_encode(array(
                    "1" => (!empty($resultado['ok']) ? "exito" : (isset($resultado['codigo']) ? $resultado['codigo'] : "error")),
                    "2" => !empty($resultado['ok']) ? $resultado['datos'] : array(
                        "codigo" => isset($resultado['codigo']) ? $resultado['codigo'] : "error",
                        "mensaje" => isset($resultado['mensaje']) ? $resultado['mensaje'] : "No se pudieron calcular las metricas."
                    )
                ));
                break;
            case 'reclasificarHiloInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $categoria= isset($_POST['categoria_principal']) ? (string)$_POST['categoria_principal'] : '';
                $tipo= isset($_POST['tipo']) ? (string)$_POST['tipo'] : '';
                $resultado= reclasificarHiloInterConsulta($cod_interConsulta, $categoria, $tipo, $user);
                echo json_encode(array(
                    "1" => !empty($resultado['ok']) ? "exito" : (isset($resultado['codigo']) ? $resultado['codigo'] : "error"),
                    "2" => !empty($resultado['ok']) ? $resultado['datos'] : array(
                        "codigo" => isset($resultado['codigo']) ? $resultado['codigo'] : "error",
                        "mensaje" => isset($resultado['mensaje']) ? $resultado['mensaje'] : "No se pudo reclasificar el hilo."
                    )
                ));
                break;
            case 'buscarTimelineUnificadoInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $limite= isset($_POST['limite']) ? intval($_POST['limite']) : 30;
                $offset= isset($_POST['offset']) ? intval($_POST['offset']) : 0;
                $resultado= buscarTimelineUnificadoInterConsulta($cod_interConsulta, $user, $limite, $offset);
                echo json_encode(array(
                    "1" => !empty($resultado['ok']) ? "exito" : (isset($resultado['codigo']) ? $resultado['codigo'] : "error"),
                    "2" => !empty($resultado['ok']) ? $resultado['datos'] : array(
                        "codigo" => isset($resultado['codigo']) ? $resultado['codigo'] : "error",
                        "mensaje" => isset($resultado['mensaje']) ? $resultado['mensaje'] : "No se pudo construir el timeline."
                    )
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
                $cod_interConsulta= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_interConsulta']) ? $_POST['cod_interConsulta'] : '');
                $asunto= isset($_POST['asunto']) ? limitarTextoFiltroInterConsulta(mb_convert_encoding((string)($_POST['asunto']), 'ISO-8859-1', 'UTF-8'), 180) : null;
                $observacion= isset($_POST['observacion']) ? limitarTextoFiltroInterConsulta(mb_convert_encoding((string)($_POST['observacion']), 'ISO-8859-1', 'UTF-8'), 2000) : null;
                $estado= isset($_POST['estado']) ? strtolower(trim(mb_convert_encoding((string)($_POST['estado']), 'ISO-8859-1', 'UTF-8'))) : null;
                $tipo= isset($_POST['tipo']) ? mb_convert_encoding((string)($_POST['tipo']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_ventaFK= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_ventaFK']) ? $_POST['cod_ventaFK'] : '');
                $cod_localFK= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_localFK']) ? $_POST['cod_localFK'] : '');
                $monto_limite= normalizarEnteroFiltroInterConsulta(isset($_POST['monto_limite']) ? $_POST['monto_limite'] : '0', false);

                if (!in_array($estado, array('pendiente', 'proceso', 'finalizado', 'inactivo'), true)
                    || $asunto === null || trim($asunto) === '' || $monto_limite < 0) {
                    echo json_encode(array("1" => "error", "2" => array(
                        "motivo" => "datos_invalidos",
                        "mensaje" => "Revise el asunto, el estado y el monto limite antes de guardar."
                    )));
                    break;
                }

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

                $esEdicion= intval($cod_interConsulta) > 0;
                $clasificacion= interconsultaOperacionTipoCategoria('', $tipo);
                if (empty($clasificacion['ok'])) {
                    echo json_encode(array("1" => "error", "2" => array(
                        "motivo" => "clasificacion_invalida",
                        "mensaje" => $clasificacion['mensaje']
                    )));
                    break;
                }
                $tipo= $clasificacion['tipo'];

                if ($esEdicion) {
                    if (!interconsultaAccesoTienePermiso($user, 'EDITARINTERCONSULTA')
                        || !interconsultaAccesoUsuarioPuedeAccederHilo($cod_interConsulta, $user, false)) {
                        echo json_encode(array("1" => "NI", "2" => "No tiene permiso o acceso al local de este hilo."));
                        break;
                    }

                    $mysqliEdicion= conectar_al_servidor();
                    $stmtEdicion= $mysqliEdicion->prepare("SELECT tipo,cod_localFK,cod_ventaFK FROM interconsulta WHERE cod_interConsulta=? LIMIT 1");
                    $actualEdicion= null;
                    if ($stmtEdicion) {
                        $stmtEdicion->bind_param('i', $cod_interConsulta);
                        if ($stmtEdicion->execute()) {
                            $actualEdicion= $stmtEdicion->get_result()->fetch_assoc();
                        }
                        $stmtEdicion->close();
                    }
                    $mysqliEdicion->close();
                    if (!$actualEdicion) {
                        echo json_encode(array("1" => "error", "2" => "No se encontro el hilo a editar."));
                        break;
                    }

                    // El endpoint heredado no puede trasladar el hilo entre
                    // locales ni reemplazar su venta vinculada.
                    $cod_localFK= intval($actualEdicion['cod_localFK']);
                    $cod_ventaFK= isset($actualEdicion['cod_ventaFK']) ? intval($actualEdicion['cod_ventaFK']) : null;
                    if (normalizarTipoHiloInterConsulta($actualEdicion['tipo']) !== normalizarTipoHiloInterConsulta($tipo)) {
                        $resultadoReclasificacion= reclasificarHiloInterConsulta($cod_interConsulta, '', $tipo, $user);
                        if (empty($resultadoReclasificacion['ok'])) {
                            echo json_encode(array(
                                "1" => isset($resultadoReclasificacion['codigo']) ? $resultadoReclasificacion['codigo'] : "error",
                                "2" => $resultadoReclasificacion
                            ));
                            break;
                        }
                    }
                    // La clasificacion ya fue validada y, si cambio, guardada
                    // transaccionalmente por el flujo dedicado.
                    $tipo= null;
                } else {
                    if (!interconsultaAccesoTienePermiso($user, 'CREARINTERCONSULTA')
                        || $cod_localFK === null
                        || !interconsultaAccesoUsuarioPuedeUsarLocal($user, $cod_localFK)) {
                        echo json_encode(array("1" => "NI", "2" => "No tiene permiso para crear hilos en el local seleccionado."));
                        break;
                    }
                    $cod_ventaFK= null;
                }

                $cod_interConsulta= abmInterConsulta($cod_interConsulta, $asunto, $observacion, $estado, $tipo, $cod_ventaFK, $user, $user, $cod_localFK, $monto_limite);
                echo json_encode(array("1" => "exito", "2" => $cod_interConsulta));
                break;
            case 'marcarMensajesLeido':
                $cod_interConsulta= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_interConsulta']) ? $_POST['cod_interConsulta'] : '', false);
                if ($cod_interConsulta <= 0
                    || !interconsultaAccesoUsuarioPuedeAccederHilo($cod_interConsulta, intval($user), false)) {
                    echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
                    break;
                }
                $actualizadas= marcarMensajesLeidosInterConsulta($cod_interConsulta, $user);
                echo json_encode(array("1" => "exito", "2" => $actualizadas));
                break;
            case 'buscarLecturasMensajeInterConsulta':
                $cod_interConsulta= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_interConsulta']) ? $_POST['cod_interConsulta'] : '', false);
                $cod_mensaje= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_mensaje']) ? $_POST['cod_mensaje'] : '', false);
                if ($cod_interConsulta <= 0 || $cod_mensaje <= 0
                    || !interconsultaAccesoUsuarioPuedeAccederHilo($cod_interConsulta, intval($user), false)) {
                    echo json_encode(array("1" => "NI", "2" => "No tiene acceso a las lecturas de este mensaje."));
                    break;
                }
                $detalleLecturas= interconsultaLecturasDetalleMensaje($cod_mensaje, $cod_interConsulta, intval($user));
                echo json_encode(array("1" => !empty($detalleLecturas['ok']) ? "exito" : "error", "2" => $detalleLecturas));
                break;
            case 'eliminarMencionMensaje':
                $cod_mencion= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_mencion']) ? $_POST['cod_mencion'] : '', false);
                $cod_interConsulta= normalizarEnteroFiltroInterConsulta(isset($_POST['cod_interConsulta']) ? $_POST['cod_interConsulta'] : '', false);
                $resultadoEliminarMencion= eliminarMencionMensajeInterConsulta(
                    $cod_mencion,
                    $cod_interConsulta,
                    intval($user)
                );
                echo json_encode($resultadoEliminarMencion);
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
                $sqlContexto= "SELECT m.cod_mensaje,m.contenido,m.fecha_creacion,m.cod_dictamenFK,p.nombre_persona
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
            case 'cargarMensajeCitadoInterConsulta':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $cod_mensaje_contexto= isset($_POST['cod_mensaje']) ? intval($_POST['cod_mensaje']) : 0;
                $offset_desde= isset($_POST['offset_desde']) ? max(0, intval($_POST['offset_desde'])) : 0;
                if (!seguimientoProgramadoPuedeAccederHilo($cod_interConsulta, $user)) {
                    echo json_encode(array("1" => "NI", "2" => array("mensaje" => "Usted no tiene acceso a este hilo.")));
                    break;
                }
                $mysqliCargaCitada= conectar_al_servidor();
                $sqlObjetivoCitado= "SELECT cod_mensaje,cod_dictamenFK,fecha_creacion
                    FROM mensaje
                    WHERE cod_mensaje=? AND cod_interConsultaFK=?
                      AND estado='activo' AND fecha_creacion<=NOW()
                    LIMIT 1";
                $stmtObjetivoCitado= $mysqliCargaCitada->prepare($sqlObjetivoCitado);
                if (!$stmtObjetivoCitado) {
                    $mysqliCargaCitada->close();
                    echo json_encode(array("1" => "error", "2" => array("mensaje" => "No se pudo localizar el mensaje citado.")));
                    break;
                }
                $stmtObjetivoCitado->bind_param('ii', $cod_mensaje_contexto, $cod_interConsulta);
                $mensajeObjetivoCitado= null;
                if ($stmtObjetivoCitado->execute()) {
                    $resultadoObjetivoCitado= $stmtObjetivoCitado->get_result();
                    $mensajeObjetivoCitado= $resultadoObjetivoCitado ? $resultadoObjetivoCitado->fetch_assoc() : null;
                }
                $stmtObjetivoCitado->close();
                if (!$mensajeObjetivoCitado) {
                    $mysqliCargaCitada->close();
                    echo json_encode(array("1" => "error", "2" => array("mensaje" => "El mensaje original ya no esta disponible.")));
                    break;
                }

                $codDictamenCitado= isset($mensajeObjetivoCitado['cod_dictamenFK']) && intval($mensajeObjetivoCitado['cod_dictamenFK']) > 0
                    ? intval($mensajeObjetivoCitado['cod_dictamenFK'])
                    : 0;
                $fechaObjetivoCitado= (string)$mensajeObjetivoCitado['fecha_creacion'];
                if ($codDictamenCitado === 0) {
                    $mysqliCargaCitada->close();
                    $ubicacionTimelineCitado= buscarTimelineUnificadoInterConsulta(
                        $cod_interConsulta,
                        $user,
                        1,
                        0,
                        'mensaje',
                        $cod_mensaje_contexto
                    );
                    $offsetObjetivoCitado= !empty($ubicacionTimelineCitado['ok'])
                        ? intval($ubicacionTimelineCitado['datos']['offset_objetivo'])
                        : -1;
                    if ($offsetObjetivoCitado < 0) {
                        echo json_encode(array("1" => "error", "2" => array("mensaje" => "El mensaje no pudo ubicarse en el orden actual del timeline.")));
                        break;
                    }
                    $totalMensajesCitado= intval($ubicacionTimelineCitado['datos']['total']);
                    if ($offset_desde > $offsetObjetivoCitado) {
                        $offset_desde= $offsetObjetivoCitado;
                    }
                    $cantidadCitada= min(100, max(1, ($offsetObjetivoCitado - $offset_desde) + 1));
                    $vistaTimelineCitada= obtenerVistaTimelineUnificadoInterConsulta(
                        $cod_interConsulta,
                        $user,
                        $cantidadCitada,
                        $offset_desde
                    );
                    $siguienteOffsetCitado= intval($vistaTimelineCitada['offset_siguiente']);
                    echo json_encode(array(
                        "1" => "exito",
                        "2" => array(
                            "html" => $vistaTimelineCitada['html'],
                            "offset_siguiente" => $siguienteOffsetCitado,
                            "offset_objetivo" => $offsetObjetivoCitado,
                            "objetivo_cargado" => $siguienteOffsetCitado > $offsetObjetivoCitado ? 1 : 0,
                            "total_mensajes" => $totalMensajesCitado,
                            "cod_dictamenFK" => null
                        )
                    ));
                    break;
                }
                if ($codDictamenCitado > 0) {
                    $sqlTotalCitado= "SELECT COUNT(*) AS total
                        FROM mensaje
                        WHERE cod_interConsultaFK=? AND cod_dictamenFK=? AND fecha_creacion<=NOW()";
                    $stmtTotalCitado= $mysqliCargaCitada->prepare($sqlTotalCitado);
                    if ($stmtTotalCitado) {
                        $stmtTotalCitado->bind_param('ii', $cod_interConsulta, $codDictamenCitado);
                    }
                    $sqlOffsetCitado= "SELECT COUNT(*) AS total
                        FROM mensaje
                        WHERE cod_interConsultaFK=? AND cod_dictamenFK=? AND fecha_creacion<=NOW()
                          AND (fecha_creacion>? OR (fecha_creacion=? AND cod_mensaje>?))";
                    $stmtOffsetCitado= $mysqliCargaCitada->prepare($sqlOffsetCitado);
                    if ($stmtOffsetCitado) {
                        $stmtOffsetCitado->bind_param('iissi', $cod_interConsulta, $codDictamenCitado, $fechaObjetivoCitado, $fechaObjetivoCitado, $cod_mensaje_contexto);
                    }
                } else {
                    $sqlTotalCitado= "SELECT COUNT(*) AS total
                        FROM mensaje
                        WHERE cod_interConsultaFK=? AND cod_dictamenFK IS NULL AND fecha_creacion<=NOW()";
                    $stmtTotalCitado= $mysqliCargaCitada->prepare($sqlTotalCitado);
                    if ($stmtTotalCitado) {
                        $stmtTotalCitado->bind_param('i', $cod_interConsulta);
                    }
                    $sqlOffsetCitado= "SELECT COUNT(*) AS total
                        FROM mensaje
                        WHERE cod_interConsultaFK=? AND cod_dictamenFK IS NULL AND fecha_creacion<=NOW()
                          AND (fecha_creacion>? OR (fecha_creacion=? AND cod_mensaje>?))";
                    $stmtOffsetCitado= $mysqliCargaCitada->prepare($sqlOffsetCitado);
                    if ($stmtOffsetCitado) {
                        $stmtOffsetCitado->bind_param('issi', $cod_interConsulta, $fechaObjetivoCitado, $fechaObjetivoCitado, $cod_mensaje_contexto);
                    }
                }
                if (!$stmtTotalCitado || !$stmtOffsetCitado
                    || !$stmtTotalCitado->execute() || !$stmtOffsetCitado->execute()) {
                    if ($stmtTotalCitado) { $stmtTotalCitado->close(); }
                    if ($stmtOffsetCitado) { $stmtOffsetCitado->close(); }
                    $mysqliCargaCitada->close();
                    echo json_encode(array("1" => "error", "2" => array("mensaje" => "No se pudo cargar el tramo del mensaje citado.")));
                    break;
                }
                $filaTotalCitado= $stmtTotalCitado->get_result()->fetch_assoc();
                $filaOffsetCitado= $stmtOffsetCitado->get_result()->fetch_assoc();
                $stmtTotalCitado->close();
                $stmtOffsetCitado->close();
                $mysqliCargaCitada->close();

                $totalMensajesCitado= isset($filaTotalCitado['total']) ? intval($filaTotalCitado['total']) : 0;
                $offsetObjetivoCitado= isset($filaOffsetCitado['total']) ? intval($filaOffsetCitado['total']) : 0;
                if ($offset_desde > $offsetObjetivoCitado) {
                    $offset_desde= $offsetObjetivoCitado;
                }
                $cantidadCitada= min(100, max(1, ($offsetObjetivoCitado - $offset_desde) + 1));
                $filtrosCargaCitada= array(
                    'cod_interConsultaFK' => $cod_interConsulta,
                    'cod_usuarioFK' => $user
                );
                if ($codDictamenCitado > 0) {
                    $filtrosCargaCitada['cod_dictamenFK']= $codDictamenCitado;
                } else {
                    $filtrosCargaCitada['sin_dictamen']= true;
                }
                $htmlCargaCitada= obtenerVistaTarjetaInterConsuta($filtrosCargaCitada, $cantidadCitada, $offset_desde);
                $siguienteOffsetCitado= $offset_desde + $cantidadCitada;
                echo json_encode(array(
                    "1" => "exito",
                    "2" => array(
                        "html" => $htmlCargaCitada,
                        "offset_siguiente" => $siguienteOffsetCitado,
                        "offset_objetivo" => $offsetObjetivoCitado,
                        "objetivo_cargado" => $siguienteOffsetCitado > $offsetObjetivoCitado ? 1 : 0,
                        "total_mensajes" => $totalMensajesCitado,
                        "cod_dictamenFK" => $codDictamenCitado > 0 ? $codDictamenCitado : null
                    )
                ));
                break;
            case 'contextoAdjuntoDocumento':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                if ($cod_interConsulta > 0 && !seguimientoProgramadoPuedeAccederHilo($cod_interConsulta, $user, true)) {
                    echo json_encode(array("1" => "NI", "mensaje" => "No tiene acceso al Hilo seleccionado."));
                    break;
                }
                $contextoAdjunto= centroFacturaContextoAdjuntoDocumento($user);
                echo json_encode(array("1" => "exito", "2" => centroFacturaValorUtf8($contextoAdjunto)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                break;
            case 'nuevo mensaje con adjunto':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $contenido= isset($_POST['contenido']) ? (string)$_POST['contenido'] : '';
                $cod_dictamenFK= isset($_POST['cod_dictamenFK']) && intval($_POST['cod_dictamenFK']) > 0 ? intval($_POST['cod_dictamenFK']) : null;
                $cod_mensaje_respuestaFK= isset($_POST['cod_mensaje_respuestaFK']) && intval($_POST['cod_mensaje_respuestaFK']) > 0 ? intval($_POST['cod_mensaje_respuestaFK']) : null;
                $tipoAdjunto= isset($_POST['tipo_adjunto']) ? strtolower(trim((string)$_POST['tipo_adjunto'])) : 'otro';
                $foto= isset($_POST['foto']) ? (string)$_POST['foto'] : '';
                $ext= isset($_POST['ext']) ? strtolower(trim((string)$_POST['ext'])) : '';
                $nombreArchivo= isset($_POST['nombre_archivo']) ? (string)$_POST['nombre_archivo'] : 'adjunto.'.$ext;
                $datosDocumento= array();
                if (isset($_POST['datos_documento']) && trim((string)$_POST['datos_documento']) !== '') {
                    $datosDecodificados= json_decode((string)$_POST['datos_documento'], true);
                    if (is_array($datosDecodificados)) {
                        $datosDocumento= $datosDecodificados;
                    }
                }
                $resultadoMensajeAdjunto= registrarMensajeConAdjuntoInterconsulta(
                    $cod_interConsulta, $contenido, $cod_dictamenFK, $cod_mensaje_respuestaFK,
                    $tipoAdjunto, $datosDocumento, array('data' => $foto, 'nombre' => $nombreArchivo, 'extension_solicitada' => $ext), $user
                );
                $estadoRespuesta= !empty($resultadoMensajeAdjunto['ok']) ? 'exito' : (!empty($resultadoMensajeAdjunto['codigo']) ? $resultadoMensajeAdjunto['codigo'] : 'error');
                $respuestaMensajeAdjunto= $resultadoMensajeAdjunto;
                $respuestaMensajeAdjunto['1']= $estadoRespuesta;
                if (!empty($resultadoMensajeAdjunto['cod_mensaje'])) {
                    $respuestaMensajeAdjunto['2']= intval($resultadoMensajeAdjunto['cod_mensaje']);
                }
                echo json_encode(centroFacturaValorUtf8($respuestaMensajeAdjunto), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
                $tipoAdjunto= isset($_POST['tipo_adjunto']) ? mb_convert_encoding((string)($_POST['tipo_adjunto']), 'ISO-8859-1', 'UTF-8') : 'otro';
                $tipoAdjunto= strtolower(trim((string)$tipoAdjunto));
                if (in_array($tipoAdjunto, array('factura','comprobante'), true)
                    && !centroFacturaTienePermiso($user, 'REGISTRARFACTURAHILO')) {
                    echo json_encode(array("1" => "NI", "mensaje" => "No tiene permiso para clasificar adjuntos financieros desde Hilos."));
                    break;
                }
                $validacionAdjunto= validarSubidaAdjuntoMensajeInterconsulta($cod_mensaje, $user);
                if (empty($validacionAdjunto['ok'])) {
                    echo json_encode(array("1" => "error", "mensaje" => $validacionAdjunto['mensaje']));
                    break;
                }
                $resultadoAdjunto= subirImagenMensaje($cod_mensaje,$foto,$ext, 'url', $tipoAdjunto);
                if (empty($resultadoAdjunto['ok'])) {
                    echo json_encode(array("1" => "error", "mensaje" => $resultadoAdjunto['mensaje']));
                    break;
                }
                $respuestaAdjunto= array("1" => "exito", "2" => $cod_mensaje, "tipo_adjunto" => $resultadoAdjunto['tipo_adjunto']);
                if (in_array($resultadoAdjunto['tipo_adjunto'], array('factura','comprobante'), true)) {
                    $respuestaAdjunto['centro_facturas']= centroFacturaRegistrarDesdeMensaje($cod_mensaje, $user);
                }
                echo json_encode(centroFacturaValorUtf8($respuestaAdjunto), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                break;
            case 'nuevo/editar mencion':
                // Las menciones solo se crean internamente al guardar mensajes.
                // Este endpoint heredado permitia alterar destinatarios arbitrarios.
                echo json_encode(array(
                    "1" => "NI",
                    "2" => "Las menciones se administran desde el mensaje correspondiente."
                ));
                break;
            case 'buscarMasInterConsultasYContenido':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? mb_convert_encoding((string)($_POST['cod_interConsulta']), 'ISO-8859-1', 'UTF-8') : null;
                $cod_dictamenFK= isset($_POST['cod_dictamenFK']) ? mb_convert_encoding((string)($_POST['cod_dictamenFK']), 'ISO-8859-1', 'UTF-8') : null;
                $offset= isset($_POST['offset']) ? mb_convert_encoding((string)($_POST['offset']), 'ISO-8859-1', 'UTF-8') : null;
                $limite= isset($_POST['limite']) ? mb_convert_encoding((string)($_POST['limite']), 'ISO-8859-1', 'UTF-8') : 0;

                if (!seguimientoProgramadoPuedeAccederHilo($cod_interConsulta, $user)) {
                    echo json_encode(array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion."));
                    break;
                }

                if ($cod_dictamenFK == null || $cod_dictamenFK == "") {
                    $cantidadTimeline= 10;
                    $offsetTimeline= 0;
                    if (preg_match('/^\s*(\d+)(?:\s+offset\s+(\d+))?\s*$/i', (string)$limite, $partesTimeline)) {
                        $cantidadTimeline= max(1, min(20, intval($partesTimeline[1])));
                        $offsetTimeline= isset($partesTimeline[2]) ? max(0, intval($partesTimeline[2])) : 0;
                    }
                    $timeline= obtenerVistaTimelineUnificadoInterConsulta($cod_interConsulta, $user, $cantidadTimeline, $offsetTimeline);
                    echo json_encode(array("1" => "exito", "2" => $timeline['html'], "3" => $timeline));
                    break;
                }

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
            case 'previsualizarFusionInterConsultas':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $cod_interConsulta_destino= isset($_POST['cod_interConsulta_destino']) ? intval($_POST['cod_interConsulta_destino']) : 0;
                echo json_encode(interconsultaFusionPrevisualizar($cod_interConsulta, $cod_interConsulta_destino, $user));
                break;
            case 'fusionarInterConsultas':
                $cod_interConsulta= isset($_POST['cod_interConsulta']) ? intval($_POST['cod_interConsulta']) : 0;
                $cod_interConsulta_destino= isset($_POST['cod_interConsulta_destino']) ? intval($_POST['cod_interConsulta_destino']) : 0;
                echo json_encode(interconsultaFusionEjecutar($cod_interConsulta, $cod_interConsulta_destino, $user));
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

        return '<div data-role="dictamen-boton-mas" data-next-offset="'.intval($offset).'" data-cod-dictamen="'.escaparHtmlInterconsulta($cod_dictamen).'" style="width: 100%; display: flex; justify-content: center; margin-bottom: 12px;">'
            . '<button type="button" class="btn btn-success" onclick="verMasMensajesInterconsulta('.intval($offset).', '.$codDictamenJs.')">Ver más mensajes...</button>'
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
        return enriquecerVisualListadoHilosInterConsulta($registros, $cod_usuarioFK);
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
        if (interconsultaLecturasEstructuraDisponible()) {
            return interconsultaLecturasMarcarHiloAbierto($cod_interConsulta, $cod_usuarioFK);
        }
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

    function eliminarMencionMensajeInterConsulta($cod_mencion, $cod_interConsulta, $cod_usuarioAutenticado) {
        $cod_mencion= intval($cod_mencion);
        $cod_interConsulta= intval($cod_interConsulta);
        $cod_usuarioAutenticado= intval($cod_usuarioAutenticado);
        if ($cod_mencion <= 0 || $cod_interConsulta <= 0 || $cod_usuarioAutenticado <= 0) {
            return array("1" => "error", "2" => "Los datos de la mencion no son validos.");
        }

        $mysqli= conectar_al_servidor();
        if (!$mysqli || !$mysqli->begin_transaction()) {
            if ($mysqli) { $mysqli->close(); }
            return array("1" => "error", "2" => "No se pudo iniciar la operacion.");
        }

        try {
            if (!interconsultaAccesoUsuarioPuedeAccederHilo(
                $cod_interConsulta,
                $cod_usuarioAutenticado,
                false,
                $mysqli
            )) {
                $mysqli->rollback();
                $mysqli->close();
                return array("1" => "NI", "2" => "Usted no tiene acceso a esta conversacion.");
            }

            $sql= "SELECT mc.cod_mencion, mc.cod_usuarioFK, mc.estado,
                    mj.cod_mensaje, mj.cod_interConsultaFK,
                    mj.cod_usuarioFK AS cod_usuario_autor,
                    mj.estado AS estado_mensaje,
                    IFNULL(p.nombre_persona, '') AS nombre_persona
                FROM menciones mc
                INNER JOIN mensaje mj ON mj.cod_mensaje=mc.cod_mensajeFK
                LEFT JOIN usuario um ON um.cod_usuario=mc.cod_usuarioFK
                LEFT JOIN persona p ON p.cod_persona=um.cod_usuario
                WHERE mc.cod_mencion=? AND mj.cod_interConsultaFK=?
                LIMIT 1
                FOR UPDATE";
            $stmt= $mysqli->prepare($sql);
            if (!$stmt) { throw new Exception('No se pudo validar la mencion.'); }
            $stmt->bind_param('ii', $cod_mencion, $cod_interConsulta);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('No se pudo validar la mencion.');
            }
            $registroMencion= $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$registroMencion) {
                $mysqli->rollback();
                $mysqli->close();
                return array("1" => "error", "2" => "La mencion no pertenece a un mensaje de este hilo.");
            }

            $codUsuarioMencionado= intval($registroMencion['cod_usuarioFK']);
            $codUsuarioAutor= intval($registroMencion['cod_usuario_autor']);
            if ($cod_usuarioAutenticado !== $codUsuarioMencionado
                && $cod_usuarioAutenticado !== $codUsuarioAutor) {
                $mysqli->rollback();
                $mysqli->close();
                return array("1" => "NI", "2" => "Solo el autor del mensaje o el usuario mencionado puede quitar esta mencion.");
            }
            if (strtolower(trim((string)$registroMencion['estado'])) !== 'activo'
                || strtolower(trim((string)$registroMencion['estado_mensaje'])) !== 'activo') {
                $mysqli->rollback();
                $mysqli->close();
                return array("1" => "error", "2" => "La mencion o su mensaje ya no estan activos.");
            }

            $stmt= $mysqli->prepare("UPDATE menciones SET isLeido=1, estado='inactivo' WHERE cod_mencion=? AND estado='activo'");
            if (!$stmt) { throw new Exception('No se pudo preparar la baja de la mencion.'); }
            $stmt->bind_param('i', $cod_mencion);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception('No se pudo quitar la mencion.');
            }
            $stmt->close();

            $nombreMencionado= trim(strip_tags((string)$registroMencion['nombre_persona']));
            $nombreMencionado= function_exists('mb_substr')
                ? mb_substr($nombreMencionado, 0, 180, 'ISO-8859-1')
                : substr($nombreMencionado, 0, 180);
            if ($nombreMencionado === '') {
                $nombreMencionado= 'usuario #'.$codUsuarioMencionado;
            }
            $contenidoAuditoria= 'Se quito la mencion de '.$nombreMencionado.'.';
            $stmt= $mysqli->prepare("INSERT INTO mensaje
                (contenido, estado, cod_interConsultaFK, cod_usuarioFK, fecha_creacion)
                VALUES (?, 'activo', ?, ?, NOW())");
            if (!$stmt) { throw new Exception('No se pudo preparar la auditoria de la mencion.'); }
            $stmt->bind_param('sii', $contenidoAuditoria, $cod_interConsulta, $cod_usuarioAutenticado);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('No se pudo registrar la auditoria de la mencion.');
            }
            $stmt->close();

            if (!$mysqli->commit()) {
                throw new Exception('No se pudo confirmar la baja de la mencion.');
            }
            $mysqli->close();
            return array("1" => "exito");
        } catch (Exception $e) {
            $mysqli->rollback();
            $mysqli->close();
            error_log('[InterConsultaMenciones] '.$e->getMessage());
            return array("1" => "error", "2" => "No se pudo quitar la mencion. Intente nuevamente.");
        }
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

    function obtenerVistaTarjetaSeguimientoTimelineInterConsulta($seguimiento, $codInterConsulta, $codUsuario) {
        if (!is_array($seguimiento) || empty($seguimiento['id_seguimiento'])) {
            return '';
        }
        $idSeguimiento= intval($seguimiento['id_seguimiento']);
        $estadoVisual= seguimientoProgramadoEstadoVisual($seguimiento);
        $estadoEtiqueta= seguimientoProgramadoEtiquetaEstado($estadoVisual);
        $esActivo= isset($seguimiento['estado']) && $seguimiento['estado'] === 'programado';
        $puedeGestionar= $esActivo && (
            intval($seguimiento['cod_responsableFK']) === intval($codUsuario)
            || intval($seguimiento['cod_usuarioFK_create']) === intval($codUsuario)
        );
        $fechaProgramada= !empty($seguimiento['fecha_programada']) && strtotime($seguimiento['fecha_programada'])
            ? date('d/m/Y H:i', strtotime($seguimiento['fecha_programada'])) : (string)$seguimiento['fecha_programada'];
        $fechaCreacion= !empty($seguimiento['fecha_creacion']) && strtotime($seguimiento['fecha_creacion'])
            ? date('d/m/Y H:i', strtotime($seguimiento['fecha_creacion'])) : (string)$seguimiento['fecha_creacion'];
        $fechaCierre= !empty($seguimiento['fecha_cierre']) && strtotime($seguimiento['fecha_cierre'])
            ? date('d/m/Y H:i', strtotime($seguimiento['fecha_cierre'])) : '';
        $motivo= trim((string)$seguimiento['motivo']) !== '' ? $seguimiento['motivo'] : 'Seguimiento personalizado';
        $nombreResponsable= trim((string)$seguimiento['nombre_responsable']) !== '' ? $seguimiento['nombre_responsable'] : 'Responsable no disponible';
        $nombreCreador= trim((string)$seguimiento['nombre_creador']) !== '' ? $seguimiento['nombre_creador'] : 'Usuario no disponible';
        $mensaje= isset($seguimiento['mensaje']) ? trim((string)$seguimiento['mensaje']) : '';
        $resultado= isset($seguimiento['resultado']) ? trim((string)$seguimiento['resultado']) : '';
        $urlResponsable= !empty($seguimiento['url_responsable']) ? $seguimiento['url_responsable'] : '/GoodVentaAsisCap/iconos/user.png';
        $atributos= ' data-seguimiento-id="'.$idSeguimiento.'" data-cod-hilo="'.intval($codInterConsulta).'"'
            .' data-plantilla-id="'.intval($seguimiento['id_plantillaFK']).'" data-motivo="'.escaparHtmlInterconsulta($motivo).'"'
            .' data-mensaje="'.escaparHtmlInterconsulta($mensaje).'" data-fecha-programada="'.escaparHtmlInterconsulta($seguimiento['fecha_programada']).'"'
            .' data-fecha-registro="'.escaparHtmlInterconsulta($seguimiento['fecha_creacion']).'" data-estado="'.escaparHtmlInterconsulta($estadoVisual).'"'
            .' data-responsable="'.intval($seguimiento['cod_responsableFK']).'"';
        $auditoria= '<span class="interconsulta-followup-card__audit"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Programado por '.escaparHtmlInterconsulta($nombreCreador)
            .($fechaCreacion !== '' ? ' el '.escaparHtmlInterconsulta($fechaCreacion) : '')
            .($fechaCierre !== '' ? ' &middot; Cerrado el '.escaparHtmlInterconsulta($fechaCierre) : '').'</span>';
        $acciones= '';
        $completar= '';
        if ($puedeGestionar) {
            $acciones= '<div class="interconsulta-followup-card__actions">'
                .'<button type="button" class="interconsulta-followup-action interconsulta-followup-action--complete" data-action="mostrar-completar-seguimiento" aria-expanded="false"><i class="fa-solid fa-check" aria-hidden="true"></i> Completar</button>'
                .'<button type="button" class="interconsulta-followup-action" data-action="reprogramar-seguimiento"><i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Reprogramar</button></div>';
            $completar= '<div class="interconsulta-followup-complete" hidden><label>Resultado de la gesti&oacute;n<textarea maxlength="750" data-role="resultado-seguimiento" placeholder="Ej.: Se logro contactar y se coordino el siguiente paso."></textarea></label>'
                .'<div class="interconsulta-followup-complete__buttons"><button type="button" data-action="cancelar-completar-seguimiento">Cancelar</button>'
                .'<button type="button" data-action="completar-seguimiento">Completar</button><button type="button" data-action="completar-y-programar-seguimiento">Completar y programar otro</button></div></div>';
        }
        return '<article id="seguimientoInterConsulta-'.$idSeguimiento.'" class="interconsulta-followup-card interconsulta-followup-card--'.escaparHtmlInterconsulta($estadoVisual).'"'.$atributos.'>'
            .'<span class="interconsulta-followup-card__avatar" title="Responsable: '.escaparHtmlInterconsulta($nombreResponsable).'"><img src="'.escaparHtmlInterconsulta($urlResponsable).'" alt="Foto del responsable '.escaparHtmlInterconsulta($nombreResponsable).'" onerror="this.onerror=null;this.src=\'/GoodVentaAsisCap/iconos/user.png\';"></span>'
            .'<div class="interconsulta-followup-card__body"><header class="interconsulta-followup-card__header"><div class="interconsulta-followup-card__title"><span>Tarea interna #'.$idSeguimiento.'</span><h4>'.escaparHtmlInterconsulta($motivo).'</h4></div></header>'
            .'<div class="interconsulta-followup-card__details"><span><i class="fa-regular fa-calendar" aria-hidden="true"></i><strong>Programada para:</strong>'.escaparHtmlInterconsulta($fechaProgramada).'</span>'
            .'<span><i class="fa-regular fa-user" aria-hidden="true"></i><strong>Responsable:</strong>'.escaparHtmlInterconsulta($nombreResponsable).'</span>'.$auditoria.'</div>'
            .($mensaje !== '' ? '<p class="interconsulta-followup-card__message">'.nl2br(escaparHtmlInterconsulta($mensaje), false).'</p>' : '')
            .($resultado !== '' ? '<div class="interconsulta-followup-card__result"><strong>Resultado:</strong> '.nl2br(escaparHtmlInterconsulta($resultado), false).'</div>' : '').'</div>'
            .'<aside class="interconsulta-followup-card__aside"><span class="interconsulta-followup-status interconsulta-followup-status--'.escaparHtmlInterconsulta($estadoVisual).'">'.escaparHtmlInterconsulta($estadoEtiqueta).'</span>'.$acciones.'</aside>'.$completar.'</article>';
    }

    function obtenerVistaTarjetaCitaTimelineInterConsulta($item) {
        $datos= isset($item['datos']) && is_array($item['datos']) ? $item['datos'] : array();
        $idAgenda= intval($item['id']);
        $fechaAgenda= substr((string)$item['fecha_evento'], 0, 10);
        $fechaHoraCita= !empty($item['fecha_evento']) && strtotime($item['fecha_evento'])
            ? date('d/m/Y H:i', strtotime($item['fecha_evento'])) : (string)$item['fecha_evento'];
        $fechaRegistro= !empty($item['fecha_registro']) && strtotime($item['fecha_registro'])
            ? date('d/m/Y H:i', strtotime($item['fecha_registro'])) : (string)$item['fecha_registro'];
        $creador= !empty($datos['nombre_creador']) ? $datos['nombre_creador'] : 'Usuario no disponible';
        $profesional= !empty($datos['nombre_profesional']) ? $datos['nombre_profesional'] : 'Sin profesional';
        $local= !empty($datos['nombre_local']) ? $datos['nombre_local'] : (!empty($datos['nombre_consultorio']) ? $datos['nombre_consultorio'] : 'Sin local');
        $motivo= !empty($datos['motivo']) ? $datos['motivo'] : 'Cita agendada';
        $estado= !empty($datos['estado']) ? $datos['estado'] : 'AGENDADO';
        $urlCreador= !empty($datos['url_creador']) ? $datos['url_creador'] : '/GoodVentaAsisCap/iconos/user.png';
        return '<article id="citaInterConsulta-'.$idAgenda.'" class="interconsulta-followup-card interconsulta-appointment-timeline-card" role="button" tabindex="0"'
            .' data-agenda-id="'.$idAgenda.'" data-agenda-fecha="'.escaparHtmlInterconsulta($fechaAgenda).'" data-fecha-registro="'.escaparHtmlInterconsulta($item['fecha_registro']).'"'
            .' onclick="event.stopPropagation();abrirCitaSeguimientoDesdeHilo(this)" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();abrirCitaSeguimientoDesdeHilo(this);}">'
            .'<span class="interconsulta-followup-card__avatar" title="Agendada por '.escaparHtmlInterconsulta($creador).'"><img src="'.escaparHtmlInterconsulta($urlCreador).'" alt="Foto de '.escaparHtmlInterconsulta($creador).'" onerror="this.onerror=null;this.src=\'/GoodVentaAsisCap/iconos/user.png\';"></span>'
            .'<div class="interconsulta-followup-card__body"><header class="interconsulta-followup-card__header"><div class="interconsulta-followup-card__title"><span>Cita agendada #'.$idAgenda.'</span><h4>'.escaparHtmlInterconsulta($motivo).'</h4></div></header>'
            .'<div class="interconsulta-followup-card__details"><span><i class="fa-regular fa-calendar" aria-hidden="true"></i><strong>Fecha y hora:</strong>'.escaparHtmlInterconsulta($fechaHoraCita).'</span>'
            .'<span><i class="fa-regular fa-user" aria-hidden="true"></i><strong>Profesional:</strong>'.escaparHtmlInterconsulta($profesional).'</span>'
            .'<span><i class="fa-solid fa-location-dot" aria-hidden="true"></i><strong>Local:</strong>'.escaparHtmlInterconsulta($local).'</span>'
            .'<span class="interconsulta-followup-card__audit"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Agendada por '.escaparHtmlInterconsulta($creador).($fechaRegistro !== '' ? ' el '.escaparHtmlInterconsulta($fechaRegistro) : '').'</span></div>'
            .'<p class="interconsulta-followup-card__message">'.escaparHtmlInterconsulta($motivo).'</p></div>'
            .'<aside class="interconsulta-followup-card__aside"><span class="interconsulta-followup-status interconsulta-followup-status--cita">'.escaparHtmlInterconsulta($estado).'</span></aside></article>';
    }

    function obtenerVistaTimelineUnificadoInterConsulta($codInterConsulta, $codUsuario, $limite, $offset= 0, $usuarios= null) {
        $resultado= buscarTimelineUnificadoInterConsulta($codInterConsulta, $codUsuario, $limite, $offset);
        if (empty($resultado['ok'])) {
            return array('html' => '', 'total' => 0, 'offset_siguiente' => intval($offset), 'hay_mas' => 0);
        }
        if (!is_array($usuarios)) {
            $usuarios= buscarUsuarios();
        }
        $html= '';
        $mesActual= '';
        $diaActual= '';
        foreach ($resultado['datos']['items'] as $item) {
            $fechaRegistro= isset($item['fecha_registro']) ? $item['fecha_registro'] : '';
            $html.= obtenerSeparadoresCronologiaInterconsulta($fechaRegistro, $mesActual, $diaActual);
            if ($item['tipo'] === 'mensaje') {
                if (!empty($item['es_legacy'])) {
                    $programado= !empty($item['fecha_evento']) && strtotime($item['fecha_evento']) ? date('d/m/Y H:i', strtotime($item['fecha_evento'])) : $item['fecha_evento'];
                    $autor= !empty($item['datos']['nombre_usuario']) ? ' de '.$item['datos']['nombre_usuario'] : '';
                    $html.= obtenerVistaEventoSistemaInterconsulta('Recordatorio heredado'.$autor.'. Programado para '.$programado.'. '.(string)$item['datos']['contenido'], $fechaRegistro, 'fa-clock');
                } else {
                    $html.= obtenerVistaTarjetaInterConsuta(array(
                        'cod_interConsultaFK' => $codInterConsulta,
                        'cod_usuarioFK' => $codUsuario,
                        'cod_mensaje' => intval($item['id']),
                        'incluir_programados' => true,
                        'fecha_registro_timeline' => $fechaRegistro,
                        'fecha_evento_timeline' => isset($item['fecha_evento']) ? $item['fecha_evento'] : '',
                        'sin_dictamen' => true,
                        'sin_separadores' => true
                    ), 1, 0, $usuarios);
                }
            } else if ($item['tipo'] === 'tarea') {
                $idSeguimiento= intval($item['id']);
                $datosTarea= isset($item['datos']) && is_array($item['datos']) ? $item['datos'] : array();
                $seguimientoTimeline= array(
                    'id_seguimiento' => $idSeguimiento,
                    'id_plantillaFK' => isset($datosTarea['id_plantilla']) ? intval($datosTarea['id_plantilla']) : 0,
                    'motivo' => isset($datosTarea['motivo']) ? $datosTarea['motivo'] : '',
                    'mensaje' => isset($datosTarea['mensaje']) ? $datosTarea['mensaje'] : '',
                    'estado' => isset($datosTarea['estado']) ? $datosTarea['estado'] : '',
                    'resultado' => isset($datosTarea['resultado']) ? $datosTarea['resultado'] : '',
                    'cod_responsableFK' => isset($datosTarea['cod_responsable']) ? intval($datosTarea['cod_responsable']) : 0,
                    'nombre_responsable' => isset($datosTarea['nombre_responsable']) ? $datosTarea['nombre_responsable'] : '',
                    'url_responsable' => isset($datosTarea['url_responsable']) ? $datosTarea['url_responsable'] : '',
                    'cod_usuarioFK_create' => isset($datosTarea['cod_creador']) ? intval($datosTarea['cod_creador']) : 0,
                    'nombre_creador' => isset($datosTarea['nombre_creador']) ? $datosTarea['nombre_creador'] : '',
                    'fecha_programada' => isset($item['fecha_evento']) ? $item['fecha_evento'] : '',
                    'fecha_creacion' => isset($item['fecha_registro']) ? $item['fecha_registro'] : '',
                    'fecha_cierre' => isset($datosTarea['fecha_cierre']) ? $datosTarea['fecha_cierre'] : ''
                );
                $html.= obtenerVistaTarjetaSeguimientoTimelineInterConsulta($seguimientoTimeline, $codInterConsulta, $codUsuario);
            } else if ($item['tipo'] === 'cita') {
                $html.= obtenerVistaTarjetaCitaTimelineInterConsulta($item);
            } else if ($item['tipo'] === 'asistencia') {
                $datosAsistencia= isset($item['datos']) && is_array($item['datos']) ? $item['datos'] : array();
                $eventoAsistencia= isset($datosAsistencia['evento']) && $datosAsistencia['evento'] === 'salida' ? 'salida' : 'entrada';
                $nombreAsistencia= isset($datosAsistencia['nombre_usuario']) ? trim((string)$datosAsistencia['nombre_usuario']) : 'El colaborador';
                $textoAsistencia= $nombreAsistencia.' marco '.$eventoAsistencia.' en Asistencia.';
                $iconoAsistencia= $eventoAsistencia === 'salida' ? 'fa-right-from-bracket' : 'fa-right-to-bracket';
                $html.= obtenerVistaEventoSistemaInterconsulta($textoAsistencia, $fechaRegistro, $iconoAsistencia);
            }
        }
        return array(
            'html' => $html,
            'total' => intval($resultado['datos']['total']),
            'offset_siguiente' => intval($resultado['datos']['offset_siguiente']),
            'hay_mas' => intval($resultado['datos']['hay_mas'])
        );
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

        // Abrir el hilo equivale a visualizarlo. La lectura se registra antes
        // de renderizar para que las palomitas y el contador salgan coherentes.
        marcarMensajesLeidosInterConsulta($filtros['cod_interConsulta'], $filtros['cod_usuarioFK']);

        foreach ($registrosInterc as $valueInter) {
            $valueInter['cantMensajesNoLeidos']= 0;
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
                        $urlMencionado= trim((string)(isset($valueMenc['url_usuario']) ? $valueMenc['url_usuario'] : ''));
                        $avatarMencionado= $urlMencionado !== ''
                            ? '<img src="'.escaparHtmlInterconsulta($urlMencionado).'" alt="Foto de '.escaparHtmlInterconsulta($valueMenc['nombre_persona']).'">'
                            : '<b>'.escaparHtmlInterconsulta(interconsultaLecturasIniciales($valueMenc['nombre_persona'])).'</b>';
                        $mencionesElemento .= '<li class="interconsulta-participant-item" data-cod-usuario="'.intval($valueMenc['cod_usuarioFK']).'" data-avatar-url="'.escaparHtmlInterconsulta($urlMencionado).'" style="display: '.(($valueInter['cod_usuarioFK_create'] != $valueMenc['cod_usuarioFK']) ? "flex" : "none").';">
                            <div class="interconsulta-participant-info">
                                <span class="interconsulta-participant-avatar">'.$avatarMencionado.'</span>
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

            $timelineUnificado= obtenerVistaTimelineUnificadoInterConsulta(
                $valueInter['cod_interConsulta'],
                $filtros['cod_usuarioFK'],
                $limiteMensajes,
                0,
                $usuariosMensaje
            );
            $paginaMensajes= $timelineUnificado['html'];
            
            $pagina .= '<div id="contenedorMensajesInterConsulta" class="collapse show" data-total-mensajes="'.$timelineUnificado['total'].'">
                <div data-role="dictamen-chat-panel">';

            if (!empty($timelineUnificado['hay_mas'])) {
                $pagina .= obtenerBotonMasMensajesInterconsulta($timelineUnificado['offset_siguiente'], "");
            }
            $pagina .= $paginaMensajes. '</div></div>';

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
        $incluirSeparadores= empty($filtros['sin_separadores']);

        if (!is_array($usuarios)) {
            $usuarios= buscarUsuarios();
        }

        // Reconstruye el limite si es necesario
        if ($offset != 0){
            $limite= "$limite OFFSET $offset";
        }

        // Obtiene todos los mensajes de la interConsulta
        $fechaActual= new DateTime();
        $filtrosMensajeTarjeta= array(
                "cod_interConsultaFK" => $filtros["cod_interConsultaFK"],
                "cod_dictamenFK" => isset($filtros['cod_dictamenFK']) ? $filtros['cod_dictamenFK'] : NULL,
                "sin_dictamen" => isset($filtros['sin_dictamen']) ? $filtros['sin_dictamen'] : NULL,
            );
        if (empty($filtros['incluir_programados'])) {
            $filtrosMensajeTarjeta['fecha_creacion']= "<= '".$fechaActual->format('Y-m-d H:i:s')."'";
        }
        if (isset($filtros['cod_mensaje']) && intval($filtros['cod_mensaje']) > 0) {
            $filtrosMensajeTarjeta['cod_mensaje']= intval($filtros['cod_mensaje']);
        }
        $regMensaje= obtenerMensaje($filtrosMensajeTarjeta, $limite);
        $resumenLecturasMensajes= interconsultaLecturasResumenMensajes($filtros["cod_interConsultaFK"], $regMensaje);
        $mesActualTimeline = '';
        $diaActualTimeline = '';
        foreach ($regMensaje as $key => $valueMens) {
            $fechaRegistroMensaje= !empty($filtros['fecha_registro_timeline'])
                ? (string)$filtros['fecha_registro_timeline'] : (string)$valueMens['fecha_creacion'];
            $fechaEventoMensaje= !empty($filtros['fecha_evento_timeline'])
                ? (string)$filtros['fecha_evento_timeline'] : (string)$valueMens['fecha_creacion'];
            $marcaRegistroMensaje= strtotime($fechaRegistroMensaje);
            $marcaEventoMensaje= strtotime($fechaEventoMensaje);
            $esMensajeProgramadoTimeline= !empty($filtros['incluir_programados'])
                && $marcaRegistroMensaje !== false && $marcaEventoMensaje !== false
                && $marcaEventoMensaje > ($marcaRegistroMensaje + 60);
            $avisoProgramadoTimeline= $esMensajeProgramadoTimeline
                ? '<span class="interconsulta-message-scheduled"><i class="fa-regular fa-clock" aria-hidden="true"></i> Programado para '.escaparHtmlInterconsulta(date('d/m/Y H:i', $marcaEventoMensaje)).'</span>'
                : '';
            if ($incluirSeparadores) {
                $paginaMensajes .= obtenerSeparadoresCronologiaInterconsulta($fechaRegistroMensaje, $mesActualTimeline, $diaActualTimeline);
            }
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
            $accionesCentroFactura= "";
            $tarjetaDocumento= "";
            if ($valueMens['url']) {
                $urlAdjunto= escaparHtmlInterconsulta($valueMens['url']);
                $extensionAdjunto= escaparHtmlInterconsulta(obtenerExtensionUrlInterconsulta($valueMens['url']));
                $esImagenAdjunto= esUrlImagenInterconsulta($valueMens['url']);
                $urlMiniaturaAdjunto= $esImagenAdjunto ? $urlAdjunto : "/GoodVentaAsisCap/iconos/informedevolucion.png";
                $claseDocumentoAdjunto= $esImagenAdjunto ? "" : " imgFotoProductoDocumento";
                $idFacturaAdjunto= isset($valueMens['centro_factura_id']) ? intval($valueMens['centro_factura_id']) : 0;
                $tipoAdjuntoMensaje= isset($valueMens['tipo_adjunto']) ? strtolower(trim((string)$valueMens['tipo_adjunto'])) : '';
                $tipoDocumentoCentro= !empty($valueMens['centro_factura_tipo_documento'])
                    ? strtolower(trim((string)$valueMens['centro_factura_tipo_documento']))
                    : ($tipoAdjuntoMensaje === 'comprobante' ? 'recibo' : ($tipoAdjuntoMensaje === 'factura' ? 'factura' : ''));
                $esFacturaAdjunto= $tipoAdjuntoMensaje === 'factura' || $tipoDocumentoCentro === 'factura';
                $esReciboAdjunto= $tipoAdjuntoMensaje === 'comprobante' || $tipoDocumentoCentro === 'recibo';
                $claseTipoDocumento= $esFacturaAdjunto ? ' interconsulta-document-card--factura' : ($esReciboAdjunto ? ' interconsulta-document-card--recibo' : '');
                $autorDocumento= !empty($valueMens['nombre_persona']) ? $valueMens['nombre_persona'] : 'Un participante';
                $tituloDocumento= $autorDocumento.' adjuntó '.($esFacturaAdjunto ? 'una factura' : ($esReciboAdjunto ? 'un recibo / comprobante' : 'una imagen o archivo'));
                $iconoDocumento= $esFacturaAdjunto ? 'fa-file-invoice' : ($esReciboAdjunto ? 'fa-receipt' : ($esImagenAdjunto ? 'fa-image' : 'fa-file'));
                $datosDocumento= '';
                $nombreContraparte= isset($valueMens['centro_factura_nombre_contraparte']) ? trim((string)$valueMens['centro_factura_nombre_contraparte']) : '';
                $documentoContraparte= isset($valueMens['centro_factura_documento_contraparte']) ? trim((string)$valueMens['centro_factura_documento_contraparte']) : '';
                $numeroDocumento= isset($valueMens['centro_factura_numero']) ? trim((string)$valueMens['centro_factura_numero']) : '';
                $fechaDocumento= isset($valueMens['centro_factura_fecha_emision']) ? trim((string)$valueMens['centro_factura_fecha_emision']) : '';
                $importeDocumento= isset($valueMens['centro_factura_importe_total']) ? (float)$valueMens['centro_factura_importe_total'] : 0;
                $descripcionDocumento= isset($valueMens['centro_factura_observaciones']) ? trim((string)$valueMens['centro_factura_observaciones']) : '';
                if ($nombreContraparte !== '') {
                    $datosDocumento.= '<span>Proveedor / raz&oacute;n social<b>'.escaparHtmlInterconsulta($nombreContraparte).'</b></span>';
                }
                if ($documentoContraparte !== '') {
                    $datosDocumento.= '<span>RUC<b>'.escaparHtmlInterconsulta($documentoContraparte).'</b></span>';
                }
                if ($numeroDocumento !== '') {
                    $datosDocumento.= '<span>'.($esReciboAdjunto ? 'N.&ordm; de recibo' : 'N.&ordm; de factura').'<b>'.escaparHtmlInterconsulta($numeroDocumento).'</b></span>';
                }
                if ($fechaDocumento !== '') {
                    $fechaDocumentoVista= strtotime($fechaDocumento) ? date('d/m/Y', strtotime($fechaDocumento)) : $fechaDocumento;
                    $datosDocumento.= '<span>Fecha<b>'.escaparHtmlInterconsulta($fechaDocumentoVista).'</b></span>';
                }
                if ($importeDocumento > 0) {
                    $datosDocumento.= '<span>Monto<b>Gs. '.number_format($importeDocumento, 0, ',', '.').'</b></span>';
                }
                if ($datosDocumento === '') {
                    $datosDocumento= '<span>Archivo<b>'.escaparHtmlInterconsulta(strtoupper($extensionAdjunto)).'</b></span>';
                }
                $accionesDocumento= '<button type="button" onclick="vercerrarcargadefotos(\'fotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'\', false)">'
                    .'<span id="imgfotoMensajeInterconsulta'.$valueMens["cod_mensaje"].'" class="imgFotoProducto'.$claseDocumentoAdjunto.'" data-adjunto-url="'.$urlAdjunto.'" data-adjunto-ext="'.$extensionAdjunto.'" style="display:none;background-image:url('.$urlMiniaturaAdjunto.');"></span>'
                    .'<i class="fa-solid fa-paperclip" aria-hidden="true"></i> Ver archivo</button>';
                if ($idFacturaAdjunto > 0) {
                    $accionesDocumento.= '<button type="button" onclick="centroFacturasAbrirDetalle('.$idFacturaAdjunto.')"><i class="fa-solid fa-folder-open" aria-hidden="true"></i> Ver en Centro de Facturas</button>';
                } elseif (($esFacturaAdjunto || $esReciboAdjunto)
                    && centroFacturaTienePermiso(isset($filtros['cod_usuarioFK']) ? $filtros['cod_usuarioFK'] : 0, 'REGISTRARFACTURAHILO')) {
                    $textoAccionFactura= $esReciboAdjunto ? 'Completar registro de recibo' : ($tipoAdjuntoMensaje === 'factura' ? 'Completar registro de factura' : 'Registrar como factura');
                    $accionesDocumento.= '<button type="button" onclick="centroFacturasRegistrarAdjuntoHilo('.intval($valueMens['cod_mensaje']).')"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> '.$textoAccionFactura.'</button>';
                }
                $tarjetaDocumento= '<section class="interconsulta-document-card'.$claseTipoDocumento.'">'
                    .'<div class="interconsulta-document-card__header"><i class="fa-solid '.$iconoDocumento.'" aria-hidden="true"></i><strong>'.escaparHtmlInterconsulta($tituloDocumento).'</strong></div>'
                    .'<div class="interconsulta-document-card__data">'.$datosDocumento.'</div>'
                    .($descripcionDocumento !== '' ? '<p class="interconsulta-document-card__description">'.nl2br(escaparHtmlInterconsulta($descripcionDocumento), false).'</p>' : '')
                    .'<div class="interconsulta-document-card__actions">'.$accionesDocumento.'</div>'
                    .'</section>';
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
                $contenidoEventoSistema= $avisoProgramadoTimeline !== '' ? $avisoProgramadoTimeline.' '.$contenidoMensaje : $contenidoMensaje;
                $paginaMensajes .= obtenerVistaEventoSistemaInterconsulta($contenidoEventoSistema, $fechaRegistroMensaje);
            } else {
                $claseMensajePropio= ($posicion == 'flex-end') ? ' interconsulta-message-row--own' : '';
                $fechaDiaMensaje = substr($fechaRegistroMensaje, 0, 10);
                $codigoMensaje= intval($valueMens['cod_mensaje']);
                $nombreAutorSeguro= escaparHtmlInterconsulta($valueMens['nombre_persona']);
                $urlAutorSeguro= escaparHtmlInterconsulta($valueMens['url_usuario'] == null ? "/GoodVentaAsisCap/iconos/user.png" : $valueMens['url_usuario']);
                $fechaMensajeSeguro= escaparHtmlInterconsulta($fechaRegistroMensaje);
                $estadoLecturaMensaje= isset($resumenLecturasMensajes[$codigoMensaje])
                    ? $resumenLecturasMensajes[$codigoMensaje]
                    : array('estado' => 'guardado', 'vistas' => 0, 'esperadas' => 0);
                $tipoLecturaMensaje= isset($estadoLecturaMensaje['estado']) ? $estadoLecturaMensaje['estado'] : 'guardado';
                $textoPalomitas= $tipoLecturaMensaje === 'guardado' ? '&#10003;' : '&#10003;&#10003;';
                $tituloPalomitas= $tipoLecturaMensaje === 'todos'
                    ? 'Visto por todos los participantes actuales'
                    : ($tipoLecturaMensaje === 'algunos' ? 'Visto por al menos una persona' : 'Mensaje guardado');
                $palomitasMensaje= '<button type="button" class="interconsulta-read-receipt interconsulta-read-receipt--'.escaparHtmlInterconsulta($tipoLecturaMensaje).'" '
                    .'onclick="event.stopPropagation();mostrarLecturasMensajeInterConsulta('.$codigoMensaje.', this)" '
                    .'title="'.escaparHtmlInterconsulta($tituloPalomitas).'" aria-label="'.escaparHtmlInterconsulta($tituloPalomitas).'">'.$textoPalomitas.'</button>';
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
                                '.$avisoProgramadoTimeline.'
                                <time>'.$fechaMensajeSeguro.'</time>
                                <button type="button" class="interconsulta-message-reply" data-action="responder-mensaje" data-cod-mensaje="'.$codigoMensaje.'" title="Responder citando este mensaje" aria-label="Responder citando este mensaje">
                                    <i class="fa-solid fa-reply" aria-hidden="true"></i>
                                    <span>Responder</span>
                                </button>
                            </div>
                        </header>
                        <div class="interconsulta-message-body">
                            '.$respuestaCitada.'
                            <p>'.$contenidoMensaje.'</p>
                            '.$tarjetaDocumento.'
                        </div>
                        <footer class="interconsulta-message-footer">'.$palomitasMensaje.'</footer>
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

    function renderGestionProgramadaInterConsulta($contenido= "", $usuario= "", $fecha= "", $urlUsuario= "", $codInterConsulta= "", $estado= "programado_legacy") {
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
            return '<button type="button" class="interconsulta-management-pill interconsulta-management-pill--empty" title="Programar una tarea interna sin abrir el hilo" data-cod-interconsulta="'.htmlspecialchars((string)$codInterConsulta, ENT_QUOTES, 'UTF-8').'" data-estado="sin_gestion" data-fecha-programada="">'
                .'<strong>Sin gestion</strong>'
                .'<span>Sin mensaje programado</span>'
                .'</button>';
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

        return '<div class="interconsulta-management-summary" title="'.$tituloSeguro.'" tabindex="0" data-cod-interconsulta="'.htmlspecialchars((string)$codInterConsulta, ENT_QUOTES, 'UTF-8').'" data-fecha-programada="'.htmlspecialchars((string)$fecha, ENT_QUOTES, 'UTF-8').'" data-estado="'.htmlspecialchars((string)$estado, ENT_QUOTES, 'UTF-8').'">'
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

    function renderSeguimientoProgramadoInternoInterConsulta($seguimiento, $codInterConsulta= "") {
        if (!is_array($seguimiento) || empty($seguimiento['id_seguimiento'])) {
            return renderGestionProgramadaInterConsulta("", "", "", "", $codInterConsulta);
        }
        if ($codInterConsulta === "" && isset($seguimiento['cod_interConsultaFK'])) {
            $codInterConsulta= $seguimiento['cod_interConsultaFK'];
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

        return '<div class="interconsulta-management-summary interconsulta-management-summary--'.escaparHtmlInterconsulta($estadoVisual).'" title="'.escaparHtmlInterconsulta($titulo).'" tabindex="0" data-cod-interconsulta="'.escaparHtmlInterconsulta($codInterConsulta).'" data-fecha-programada="'.escaparHtmlInterconsulta(isset($seguimiento['fecha_programada']) ? $seguimiento['fecha_programada'] : '').'" data-estado="'.escaparHtmlInterconsulta($estadoVisual).'">'
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

    function enriquecerVisualListadoHilosInterConsulta($registros, $codUsuario) {
        $ids= array();
        foreach ((array)$registros as $registro) {
            $id= intval(isset($registro['cod_interConsulta']) ? $registro['cod_interConsulta'] : 0);
            if ($id > 0) { $ids[]= $id; }
        }
        if (count($ids) === 0) { return $registros; }
        $mysqli= conectar_al_servidor();
        $participantes= interconsultaParticipantesActualesHilos($ids, $mysqli);
        $colaboradores= interconsultaColaboradoresDatosHilos($ids, $mysqli);
        $conteosLectura= interconsultaLecturasEstructuraDisponible($mysqli)
            ? interconsultaLecturasNoLeidosHilos($ids, intval($codUsuario), $mysqli)
            : array();
        $mysqli->close();
        foreach ($registros as &$registro) {
            $id= intval(isset($registro['cod_interConsulta']) ? $registro['cod_interConsulta'] : 0);
            $participantesHilo= isset($participantes[$id]) ? $participantes[$id] : array();
            $registro['participantes_actuales']= array_values($participantesHilo);
            $registro['participantes_html']= interconsultaRenderGrupoParticipantes($participantesHilo, 5);
            if (array_key_exists($id, $conteosLectura)) {
                $registro['cantMensajesNoLeidos']= intval($conteosLectura[$id]);
                $registro['cantMensajesNoLeidosOtrosUsuarios']= 0;
            }
            $registro['esHiloColaborador']= isset($colaboradores[$id]) ? 1 : 0;
            if (isset($colaboradores[$id])) {
                $datos= $colaboradores[$id];
                $registro['cod_funcionario']= intval($datos['cod_usuarioFK']);
                $registro['nombre_funcionario']= isset($datos['nombre_persona']) ? $datos['nombre_persona'] : '';
                $registro['url_funcionario']= isset($datos['url']) ? $datos['url'] : '';
                $registro['cargo_funcionario']= isset($datos['cargo_funcionario']) ? $datos['cargo_funcionario'] : '';
                $registro['area_funcionario']= isset($datos['area_funcionario']) ? $datos['area_funcionario'] : '';
            }
        }
        unset($registro);
        return $registros;
    }

    function renderCredencialColaboradorListadoInterConsulta($registro) {
        if (empty($registro['esHiloColaborador'])) { return ''; }
        $nombre= trim((string)(isset($registro['nombre_funcionario']) ? $registro['nombre_funcionario'] : 'Colaborador'));
        $cargo= trim((string)(isset($registro['cargo_funcionario']) ? $registro['cargo_funcionario'] : 'Colaborador'));
        $url= trim((string)(isset($registro['url_funcionario']) ? $registro['url_funcionario'] : ''));
        $nombreSeguro= htmlspecialchars($nombre !== '' ? $nombre : 'Colaborador', ENT_QUOTES, 'UTF-8');
        $cargoSeguro= htmlspecialchars($cargo !== '' ? $cargo : 'Colaborador', ENT_QUOTES, 'UTF-8');
        $foto= $url !== ''
            ? '<img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="Foto de '.$nombreSeguro.'" onerror="this.src=\'/GoodVentaAsisCap/iconos/sinperfil.png\';">'
            : '<span>'.htmlspecialchars(interconsultaLecturasIniciales($nombre), ENT_QUOTES, 'UTF-8').'</span>';
        return '<aside class="interconsulta-employee-credential" title="Hilo laboral de '.$nombreSeguro.' - '.$cargoSeguro.'">'
            .'<div class="interconsulta-employee-credential__photo">'.$foto.'</div>'
            .'<div class="interconsulta-employee-credential__data"><b>'.$nombreSeguro.'</b><small>'.$cargoSeguro.'</small><em>Colaborador</em></div>'
            .'</aside>';
    }

    function obtenerInterConsultaBasica($filtros= array(), $limite= 30) {
        list($sqlFiltro, $sqlFiltroMenciones, $sqlFiltroMensaje, $sqlFiltroFechaLimite, $sqlOrdenMenciones) = construirFiltrosInterConsulta($filtros);
        $limite= normalizarLimiteListadoInterConsulta($limite, 30);
        $codUsuario= isset($filtros['cod_usuarioFK']) ? intval($filtros['cod_usuarioFK']) : 0;
        $condicionUsuarioNoLeido= $codUsuario > 0 ? "AND mc.cod_usuarioFK=".$codUsuario : "";
        $seleccionOrdenMenciones= $sqlOrdenMenciones !== '' ? $sqlOrdenMenciones.' AS ultima_mencion_explicita,' : '0 AS ultima_mencion_explicita,';
        $ordenMenciones= $sqlOrdenMenciones !== '' ? 'ultima_mencion_explicita DESC,' : '';

        $sql= "SELECT ic.*,
                ".$seleccionOrdenMenciones."
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
            ORDER BY ".$ordenMenciones."
                cantMensajesNoLeidos DESC,
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
        $codUsuarioListado= isset($filtros['cod_usuarioFK']) ? intval($filtros['cod_usuarioFK']) : 0;
        $registros= enriquecerVisualListadoHilosInterConsulta($registros, $codUsuarioListado);
        $categoriaActiva= isset($filtros['categoria_principal']) ? $filtros['categoria_principal'] : 'pagos_egresos';
        $mostrarColumnasSeguimiento= in_array($categoriaActiva, array('administrativo_clinico','judiciales'), true);
        $mostrarGestionProgramada= $mostrarColumnasSeguimiento || $categoriaActiva == 'pagos_egresos';
        $pagina= '';
        $datalist= '';
        $estadoRegistros= array();
        $cantMensajesNoLeidos= 0;
        $cantInterConsultasAbiertas= 0;
        $codigosPagina= array();

        foreach ($registros as $value) {
            $codigosPagina[]= intval($value['cod_interConsulta']);
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
                .($esSeguimiento ? ' interconsulta-thread-row--patient-master' : '')
                .(!empty($value['esHiloColaborador']) ? ' interconsulta-thread-row--employee' : '');
            $badgePendiente= $esPendiente ? '<span class="interconsulta-pending-badge interconsulta-unread-count" title="'.intval($value['cantMensajesNoLeidos']).' mensaje(s) sin leer" aria-label="'.intval($value['cantMensajesNoLeidos']).' mensaje(s) sin leer">'.intval($value['cantMensajesNoLeidos']).'</span>' : '';
            $iconoVinculado= $esVinculado ? ' <i class="fa-solid fa-link interconsulta-linked-icon" title="Hilo vinculado" aria-hidden="true"></i>' : '';
            $credencialColaborador= renderCredencialColaboradorListadoInterConsulta($value);
            $formatAsunto= '<div class="interconsulta-thread-main">'.$credencialColaborador.'<div class="interconsulta-subject-wrap"><div class="interconsulta-subject-line"><p class="interconsulta-subject-text interconsulta-subject-title">'.htmlspecialchars($asuntoVista, ENT_QUOTES, 'UTF-8').$iconoVinculado.'</p>'.$badgePendiente.'</div></div></div>';
            $participantesHtml= isset($value['participantes_html']) ? $value['participantes_html'] : interconsultaRenderGrupoParticipantes(array(), 5);
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
                <td class="interconsulta-participants-cell" style="width:'.$anchoTipo.';">'.$participantesHtml.'</td>
                <td id="td_datos_6" style="display:none;">'.htmlspecialchars((string)$value['tipo'], ENT_QUOTES, 'UTF-8').'</td>
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

        $totalNoLeidosGlobal= interconsultaLecturasEstructuraDisponible()
            ? interconsultaLecturasTotalUsuario($codUsuarioListado)
            : $cantMensajesNoLeidos;

        echo json_encode(array(
            '1' => 'exito',
            '2' => $pagina,
            '3' => $estadoRegistros,
            '4' => count($registros),
            '5' => $cantRegistros,
            '6' => $totalNoLeidosGlobal,
            '7' => $cantInterConsultasAbiertas,
            '8' => $datalist,
            '9' => array(),
            '10' => '',
            '11' => 'basico',
            '12' => $codigosPagina,
            '13' => array()
        ));
    }

    function obtenerVistaInterConsulta($filtros= array(), $limite= 0, $maximoLimite= 30, $codUsuarioSesion= 0) {
        $filtrosTotales= $filtros;
        unset($filtrosTotales['codigos_hilos']);
        $cantRegistros= obtenerCantidadInterConsulta($filtrosTotales);
        $limite = normalizarLimiteListadoInterConsulta($limite, $maximoLimite);
        $registros= obtenerInterConsulta($filtros, $limite);
        $registros= enriquecerVisualListadoHilosInterConsulta($registros, $codUsuarioSesion);
        $codigosHilosSeguimiento= array();
        foreach ($registros as $registroSeguimiento) {
            if (!empty($registroSeguimiento['cod_interConsulta'])) {
                $codigosHilosSeguimiento[]= intval($registroSeguimiento['cod_interConsulta']);
            }
        }
        $seguimientosActivosPorHilo= seguimientoProgramadoObtenerActivosPorHilos($codigosHilosSeguimiento);
        $conteosCategorias = obtenerConteosCategoriasInterConsulta($filtrosTotales);
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
                .($estadoSeguimientoActivo === 'para_hoy' ? ' interconsulta-thread-row--followup-today' : '')
                .(!empty($value['esHiloColaborador']) ? ' interconsulta-thread-row--employee' : '');
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
                ? ($seguimientoActivo ? renderSeguimientoProgramadoInternoInterConsulta($seguimientoActivo, $codigoHiloActual) : renderGestionProgramadaInterConsulta("", "", "", "", $codigoHiloActual))
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
                                isset($valueMens['url_usuario']) ? $valueMens['url_usuario'] : "",
                                $codigoHiloActual,
                                "programado_legacy"
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
            $badgePendienteRespuesta = $esHiloPendienteRespuesta ? '<span class="interconsulta-pending-badge interconsulta-unread-count" title="'.intval($value['cantMensajesNoLeidos']).' mensaje(s) sin leer" aria-label="'.intval($value['cantMensajesNoLeidos']).' mensaje(s) sin leer">'.intval($value['cantMensajesNoLeidos']).'</span>' : '';
            $tooltipFila = $esHiloPendienteRespuesta ? ' title="'.intval($value['cantMensajesNoLeidos']).' mensaje(s) sin leer"' : '';
            $lineaSeguimientoPaciente = ($badgesSeguimientoPaciente != "")
                ? '<div class="interconsulta-follow-strip">'.$badgesSeguimientoPaciente.'</div>'
                : '';
            $credencialColaborador= renderCredencialColaboradorListadoInterConsulta($value);
            $participantesHtml= isset($value['participantes_html']) ? $value['participantes_html'] : interconsultaRenderGrupoParticipantes(array(), 5);
            $formatAsunto= '<div class="interconsulta-thread-main">'.$credencialColaborador.'<div class="interconsulta-subject-wrap">'
                .'<div class="interconsulta-subject-line">'
                .'<p'.$claseHiloVinculado.$tituloHiloVinculado.' style="'.$colorText.'">'
                .$contenidoAsuntoPendienteInicio
                .$contenidoAsunto
                .$contenidoAsuntoPendienteFin
                .'</p>'
                .$badgePendienteRespuesta
                .'</div>'
                .$lineaSeguimientoPaciente
                .'</div></div>';
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
                    <td class="interconsulta-participants-cell" style="width: '.$anchoTipo.';'.$style.'">'.$participantesHtml.'</td>
                    <td id="td_datos_6" style="display: none;'.$style.'">'.$value['tipo'].'</td>
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

        $metricasGestionResultado= obtenerMetricasGestionInterConsulta(
            $codUsuarioSesion,
            isset($filtros['fecha_desde']) ? $filtros['fecha_desde'] : '',
            isset($filtros['fecha_hasta']) ? $filtros['fecha_hasta'] : '',
            isset($filtros['cod_localFK']) ? intval($filtros['cod_localFK']) : 0
        );
        $metricasGestion= !empty($metricasGestionResultado['ok']) ? $metricasGestionResultado['datos'] : array(
            'fecha_desde' => date('Y-m-d'),
            'fecha_hasta' => date('Y-m-d'),
            'etiqueta' => 'Gestiones hoy',
            'rango' => date('d/m/Y'),
            'usuarios' => array()
        );
        $actividadDiariaSeguimiento= renderVistaMetricasGestionInterConsulta($metricasGestion);
        $alertasSeguimientoProgramado= seguimientoProgramadoObtenerResumenAlertas($codUsuarioSesion);

        $estadoRegistros= array();
        foreach ($registros as $registroEstado) {
            $estadoRegistros[]= array(
                "cod_interConsulta" => isset($registroEstado['cod_interConsulta']) ? $registroEstado['cod_interConsulta'] : "",
                "cantMensajes" => isset($registroEstado['cantMensajes']) ? $registroEstado['cantMensajes'] : 0
            );
        }

        $totalNoLeidosGlobal= interconsultaLecturasEstructuraDisponible()
            ? interconsultaLecturasTotalUsuario($codUsuarioSesion)
            : $cant_mensajes_no_leidos;
        echo json_encode(array("1" => "exito", "2" => $pagina, "3" => $estadoRegistros, "4" => count($registros), "5" => $cantRegistros, "6" => $totalNoLeidosGlobal, "7" => $cant_interConsulta_abierto, "8" => $datalist, "9" => $conteosCategorias, "10" => $actividadDiariaSeguimiento, "11" => "enriquecido", "12" => $alertasSeguimientoProgramado, "13" => $metricasGestion));
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

    function prepararContenidoMensajeConAdjuntoInterconsulta($contenido) {
        $contenido= (string)$contenido;
        if ($contenido !== '' && !mb_check_encoding($contenido, 'UTF-8')) {
            $contenido= mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
        }
        $contenidoLimpiado= '';
        $idsMenciones= array();
        if (trim($contenido) !== '') {
            $dom= new DOMDocument();
            libxml_use_internal_errors(true);
            $cargado= $dom->loadHTML('<?xml encoding="UTF-8"><html><body>'.$contenido.'</body></html>', LIBXML_HTML_NODEFDTD);
            if ($cargado) {
                $spans= $dom->getElementsByTagName('b');
                foreach ($spans as $span) {
                    if ($span->hasAttribute('id') && intval($span->getAttribute('id')) > 0) {
                        $idsMenciones[]= intval($span->getAttribute('id'));
                    }
                }
                $xpath= new DOMXPath($dom);
                $nodosMencion= array();
                foreach ($xpath->query('//b[@id]') as $nodoMencion) {
                    $nodosMencion[]= $nodoMencion;
                }
                foreach ($nodosMencion as $nodoMencion) {
                    $idMencion= intval($nodoMencion->getAttribute('id'));
                    $nodoMencion->parentNode->replaceChild($dom->createTextNode('@{'.$idMencion.'}'), $nodoMencion);
                }
                $contenidoHtml= '';
                $body= $dom->getElementsByTagName('body')->item(0);
                if ($body) {
                    foreach ($body->childNodes as $nodoContenido) {
                        $contenidoHtml.= $dom->saveHTML($nodoContenido);
                    }
                }
                $contenidoHtml= preg_replace('/<br\s*\/?>/i', "\n", $contenidoHtml);
                $contenidoHtml= preg_replace('/<\/(div|p|li|tr|h[1-6])>/i', "\n", $contenidoHtml);
                $contenidoLimpiado= strip_tags($contenidoHtml);
            }
            libxml_clear_errors();
            $contenidoLimpiado= html_entity_decode($contenidoLimpiado, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $contenidoLimpiado= str_replace("\xC2\xA0", ' ', $contenidoLimpiado);
            $contenidoLimpiado= preg_replace("/[ \t]+\n/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado= preg_replace("/\n[ \t]+/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado= preg_replace("/\R/u", "\n", $contenidoLimpiado);
            $contenidoLimpiado= preg_replace("/\n{3,}/", "\n\n", $contenidoLimpiado);
            $contenidoLimpiado= trim($contenidoLimpiado);
        }
        $longitud= function_exists('mb_strlen') ? mb_strlen($contenidoLimpiado, 'UTF-8') : strlen($contenidoLimpiado);
        if ($longitud > 750) {
            return array('ok' => false, 'mensaje' => 'El mensaje supera el limite de 750 caracteres.');
        }
        return array(
            'ok' => true,
            'contenido' => centroFacturaTextoBaseDatos($contenidoLimpiado, 750, true),
            'menciones' => array_values(array_unique($idsMenciones))
        );
    }

    function guardarArchivoPreparadoMensajeInterconsulta($codMensaje, $archivo) {
        $directorio= dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosMensaje';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
            return array('ok' => false, 'mensaje' => 'No se pudo preparar la carpeta de adjuntos.');
        }
        try {
            $sufijo= bin2hex(random_bytes(6));
        } catch (Exception $e) {
            $sufijo= str_replace('.', '', uniqid('', true));
        }
        $nombre= intval($codMensaje).'-'.$sufijo.'.'.$archivo['extension'];
        $rutaAbsoluta= $directorio.DIRECTORY_SEPARATOR.$nombre;
        $bytesEsperados= strlen($archivo['binario']);
        $bytesEscritos= file_put_contents($rutaAbsoluta, $archivo['binario'], LOCK_EX);
        if ($bytesEscritos === false || intval($bytesEscritos) !== $bytesEsperados) {
            if (is_file($rutaAbsoluta)) {
                @unlink($rutaAbsoluta);
            }
            return array('ok' => false, 'mensaje' => 'No se pudo guardar el adjunto.');
        }
        return array(
            'ok' => true,
            'ruta_absoluta' => $rutaAbsoluta,
            'url' => '/GoodVentaAsisCap/fotos/fotosMensaje/'.$nombre
        );
    }

    function registrarMencionesMensajeAdjuntoEnTransaccion($mysqli, $codMensaje, $codInterConsulta, $codUsuario, $idsMenciones) {
        $usuarios= array(intval($codUsuario) => 1);
        foreach ((array)$idsMenciones as $idMencion) {
            $idMencion= intval($idMencion);
            if ($idMencion > 0) {
                $usuarios[$idMencion]= 1;
            }
        }
        $stmt= $mysqli->prepare("SELECT DISTINCT mn.cod_usuarioFK
            FROM menciones mn INNER JOIN mensaje m ON m.cod_mensaje=mn.cod_mensajeFK
            WHERE m.cod_interConsultaFK=? AND m.estado='activo' AND m.fecha_creacion<=NOW()
              AND mn.isLeido=0 AND mn.estado<>'inactivo'");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $codInterConsulta);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $resultado= $stmt->get_result();
        while ($fila= $resultado->fetch_assoc()) {
            $idPendiente= intval($fila['cod_usuarioFK']);
            if ($idPendiente > 0) {
                $usuarios[$idPendiente]= 1;
            }
        }
        $stmt->close();
        $ids= array_keys($usuarios);
        if (count($ids) === 0) {
            return true;
        }
        $idsSeguros= array_map('intval', $ids);
        $resultadoUsuarios= $mysqli->query("SELECT cod_usuario FROM usuario WHERE cod_usuario IN (".implode(',', $idsSeguros).") AND estado='Activo'");
        if (!$resultadoUsuarios) {
            return false;
        }
        $usuariosValidos= array();
        while ($filaUsuario= $resultadoUsuarios->fetch_assoc()) {
            $usuariosValidos[]= intval($filaUsuario['cod_usuario']);
        }
        $resultadoUsuarios->free();
        $sql= "INSERT INTO menciones (cod_usuarioFK,cod_mensajeFK,isLeido,estado)
            VALUES (?,?,?,'activo')
            ON DUPLICATE KEY UPDATE isLeido=VALUES(isLeido),estado='activo'";
        $stmt= $mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        foreach ($usuariosValidos as $idUsuario) {
            $leido= $idUsuario === intval($codUsuario) ? 1 : 0;
            $stmt->bind_param('iii', $idUsuario, $codMensaje, $leido);
            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }
        }
        $stmt->close();
        return true;
    }

    function registrarMensajeConAdjuntoInterconsulta($codInterConsulta, $contenido, $codDictamenFK, $codMensajeRespuestaFK, $tipoAdjunto, $datosDocumento, $archivo, $codUsuario) {
        $codInterConsulta= intval($codInterConsulta);
        $codUsuario= intval($codUsuario);
        $tipoAdjunto= strtolower(trim((string)$tipoAdjunto));
        if ($codInterConsulta <= 0 || $codUsuario <= 0 || !in_array($tipoAdjunto, array('factura','comprobante','otro'), true)) {
            return array('ok' => false, 'codigo' => 'error', 'mensaje' => 'El Hilo o el tipo de adjunto no son validos.');
        }
        if (!seguimientoProgramadoPuedeAccederHilo($codInterConsulta, $codUsuario, true)) {
            return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene acceso para responder en este Hilo.');
        }
        if (in_array($tipoAdjunto, array('factura','comprobante'), true)
            && !centroFacturaTienePermiso($codUsuario, 'REGISTRARFACTURAHILO')) {
            return array('ok' => false, 'codigo' => 'NI', 'mensaje' => 'No tiene permiso para registrar facturas o recibos desde Hilos.');
        }
        $contenidoPreparado= prepararContenidoMensajeConAdjuntoInterconsulta($contenido);
        if (empty($contenidoPreparado['ok'])) {
            return $contenidoPreparado;
        }
        $archivoPreparado= centroFacturaPrepararArchivo($archivo);
        if (empty($archivoPreparado['ok'])) {
            return array('ok' => false, 'codigo' => 'archivo', 'mensaje' => $archivoPreparado['mensaje']);
        }
        $mysqli= conectar_al_servidor();
        if (in_array($tipoAdjunto, array('factura','comprobante'), true) && !centroFacturaEstructuraDisponible($mysqli)) {
            $mysqli->close();
            return array('ok' => false, 'codigo' => 'estructura', 'mensaje' => 'La estructura del Centro de Facturas no esta instalada.');
        }
        $rutaAbsoluta= '';
        if (!$mysqli->begin_transaction()) {
            $mysqli->close();
            return array('ok' => false, 'codigo' => 'error', 'mensaje' => 'No se pudo iniciar el envio seguro del mensaje.');
        }
        try {
            $stmt= $mysqli->prepare("SELECT cod_interConsulta,cod_localFK,estado FROM interconsulta WHERE cod_interConsulta=? LIMIT 1 FOR UPDATE");
            if (!$stmt) {
                throw new Exception('No se pudo validar el Hilo.');
            }
            $stmt->bind_param('i', $codInterConsulta);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('No se pudo validar el Hilo.');
            }
            $hilo= $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$hilo || strtolower(trim((string)$hilo['estado'])) === 'inactivo') {
                throw new Exception('El Hilo ya no esta activo.');
            }
            if (in_array($tipoAdjunto, array('factura','comprobante'), true)
                && !centroFacturaPuedeUsarLocal($codUsuario, $hilo['cod_localFK'], $mysqli)) {
                throw new Exception('No puede registrar documentos para el local del Hilo.');
            }
            if ($codMensajeRespuestaFK !== null) {
                if (!seguimientoProgramadoRespuestaCitadaDisponible($mysqli)) {
                    throw new Exception('La respuesta citada todavia no esta disponible.');
                }
                $stmt= $mysqli->prepare("SELECT cod_mensaje FROM mensaje
                    WHERE cod_mensaje=? AND cod_interConsultaFK=? AND estado='activo' AND fecha_creacion<=NOW() LIMIT 1");
                if (!$stmt) {
                    throw new Exception('No se pudo validar el mensaje citado.');
                }
                $stmt->bind_param('ii', $codMensajeRespuestaFK, $codInterConsulta);
                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new Exception('No se pudo validar el mensaje citado.');
                }
                $respuestaValida= $stmt->get_result()->num_rows > 0;
                $stmt->close();
                if (!$respuestaValida) {
                    throw new Exception('El mensaje citado ya no pertenece a este Hilo.');
                }
            }
            $validosDocumento= array('ok' => true, 'tipo_adjunto' => 'otro', 'tipo_documento' => '');
            if (in_array($tipoAdjunto, array('factura','comprobante'), true)) {
                $validosDocumento= centroFacturaValidarAdjuntoDocumental($mysqli, $tipoAdjunto, $datosDocumento);
                if (empty($validosDocumento['ok'])) {
                    throw new Exception($validosDocumento['mensaje']);
                }
            }
            $fechaCreacion= date('Y-m-d H:i:s');
            $contenidoBaseDatos= $contenidoPreparado['contenido'];
            if (seguimientoProgramadoRespuestaCitadaDisponible($mysqli)) {
                $stmt= $mysqli->prepare("INSERT INTO mensaje
                    (contenido,fecha_creacion,cod_interConsultaFK,cod_usuarioFK,cod_dictamenFK,cod_mensaje_respuestaFK)
                    VALUES (?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param('ssiiii', $contenidoBaseDatos, $fechaCreacion, $codInterConsulta, $codUsuario, $codDictamenFK, $codMensajeRespuestaFK);
                }
            } else {
                $stmt= $mysqli->prepare("INSERT INTO mensaje
                    (contenido,fecha_creacion,cod_interConsultaFK,cod_usuarioFK,cod_dictamenFK)
                    VALUES (?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param('ssiii', $contenidoBaseDatos, $fechaCreacion, $codInterConsulta, $codUsuario, $codDictamenFK);
                }
            }
            if (!$stmt || !$stmt->execute()) {
                throw new Exception('No se pudo crear el mensaje con su adjunto.');
            }
            $codMensaje= intval($stmt->insert_id);
            $stmt->close();
            $guardado= guardarArchivoPreparadoMensajeInterconsulta($codMensaje, $archivoPreparado);
            if (empty($guardado['ok'])) {
                throw new Exception($guardado['mensaje']);
            }
            $rutaAbsoluta= $guardado['ruta_absoluta'];
            $urlAdjunto= $guardado['url'];
            $stmt= $mysqli->prepare("UPDATE mensaje SET url=?,tipo_adjunto=? WHERE cod_mensaje=? AND estado='activo'");
            if (!$stmt) {
                throw new Exception('No se pudo preparar el vinculo del archivo con el mensaje.');
            }
            $stmt->bind_param('ssi', $urlAdjunto, $tipoAdjunto, $codMensaje);
            if (!$stmt->execute() || $stmt->affected_rows < 1) {
                $stmt->close();
                throw new Exception('No se pudo vincular el archivo con el mensaje.');
            }
            $stmt->close();
            $resultadoCentro= null;
            if (in_array($tipoAdjunto, array('factura','comprobante'), true)) {
                $mensajeCentro= array(
                    'cod_mensaje' => $codMensaje, 'cod_interConsultaFK' => $codInterConsulta,
                    'cod_localFK' => intval($hilo['cod_localFK']), 'url' => $urlAdjunto,
                    'contenido' => $contenidoBaseDatos, 'tipo_adjunto' => $tipoAdjunto
                );
                $resultadoCentro= centroFacturaInsertarDesdeMensajeEnTransaccion($mysqli, $mensajeCentro, $validosDocumento, $archivoPreparado, $codUsuario);
                if (empty($resultadoCentro['ok'])) {
                    throw new Exception($resultadoCentro['mensaje']);
                }
            }
            if (!registrarMencionesMensajeAdjuntoEnTransaccion($mysqli, $codMensaje, $codInterConsulta, $codUsuario, $contenidoPreparado['menciones'])) {
                throw new Exception('No se pudieron registrar los destinatarios del mensaje.');
            }
            if (!interconsultaLecturasSincronizarParticipantesHilo($mysqli, $codInterConsulta, $fechaCreacion)) {
                if (interconsultaLecturasEstructuraDisponible($mysqli)) {
                    throw new Exception('No se pudo preparar el contador de lectura del mensaje.');
                }
            }
            if (!$mysqli->commit()) {
                throw new Exception('No se pudo confirmar el mensaje con su adjunto.');
            }
            $mysqli->close();
            $respuesta= array(
                'ok' => true, 'cod_mensaje' => $codMensaje, 'tipo_adjunto' => $tipoAdjunto,
                'url' => $urlAdjunto
            );
            if ($resultadoCentro) {
                $respuesta['centro_facturas']= $resultadoCentro;
            }
            return $respuesta;
        } catch (Exception $e) {
            $mysqli->rollback();
            $mysqli->close();
            if ($rutaAbsoluta !== '' && is_file($rutaAbsoluta)) {
                @unlink($rutaAbsoluta);
            }
            return array('ok' => false, 'codigo' => 'error', 'mensaje' => $e->getMessage());
        }
    }

    function validarSubidaAdjuntoMensajeInterconsulta($codMensaje, $codUsuario) {
        $codMensaje= intval($codMensaje);
        $codUsuario= intval($codUsuario);
        if ($codMensaje <= 0 || $codUsuario <= 0) {
            return array('ok' => false, 'mensaje' => 'El mensaje o el usuario no son validos.');
        }
        $mysqli= conectar_al_servidor();
        $stmt= $mysqli->prepare("SELECT m.cod_interConsultaFK,m.cod_usuarioFK,m.estado,m.url,ic.estado AS hilo_estado
            FROM mensaje m INNER JOIN interconsulta ic ON ic.cod_interConsulta=m.cod_interConsultaFK
            WHERE m.cod_mensaje=? LIMIT 1");
        $stmt->bind_param('i', $codMensaje);
        $stmt->execute();
        $mensaje= $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $mysqli->close();
        if (!$mensaje || $mensaje['estado'] !== 'activo' || $mensaje['hilo_estado'] === 'inactivo') {
            return array('ok' => false, 'mensaje' => 'El mensaje o el Hilo ya no estan activos.');
        }
        if (intval($mensaje['cod_usuarioFK']) !== $codUsuario) {
            return array('ok' => false, 'mensaje' => 'Solo el autor del mensaje puede completar este adjunto.');
        }
        if (trim((string)$mensaje['url']) !== '') {
            return array('ok' => false, 'mensaje' => 'El mensaje ya tiene un adjunto y no puede reemplazarse silenciosamente.');
        }
        if (!seguimientoProgramadoPuedeAccederHilo($mensaje['cod_interConsultaFK'], $codUsuario, true)) {
            return array('ok' => false, 'mensaje' => 'No tiene acceso al Hilo de origen.');
        }
        return array('ok' => true, 'cod_interConsultaFK' => intval($mensaje['cod_interConsultaFK']));
    }

    function subirImagenMensaje($cod_mensaje, $foto, $ext, $campo, $tipoAdjunto= 'otro') {
        $cod_mensaje= intval($cod_mensaje);
        $campo= $campo === 'url' ? 'url' : '';
        $tipoAdjunto= strtolower(trim((string)$tipoAdjunto));
        if (!in_array($tipoAdjunto, array('factura','comprobante','otro'), true)) {
            $tipoAdjunto= 'otro';
        }
        $ext= strtolower(trim((string)$ext));
        $ext= preg_replace('/[^a-z0-9]/', '', $ext);
        $permitidas= array(
            'jpg' => array('image/jpeg'), 'jpeg' => array('image/jpeg'), 'png' => array('image/png'),
            'gif' => array('image/gif'), 'webp' => array('image/webp'), 'bmp' => array('image/bmp','image/x-ms-bmp'),
            'pdf' => array('application/pdf'), 'txt' => array('text/plain'),
            'doc' => array('application/msword','application/octet-stream'),
            'docx' => array('application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'xls' => array('application/vnd.ms-excel','application/octet-stream'),
            'xlsx' => array('application/zip','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        );
        if ($cod_mensaje <= 0 || $campo === '' || !isset($permitidas[$ext]) || empty($foto)) {
            return array('ok' => false, 'mensaje' => 'El adjunto no tiene un formato permitido.');
        }
        if (strpos($foto, ',') !== false) {
            $foto= substr($foto, strpos($foto, ',') + 1);
        }
        $contenido= base64_decode($foto, true);
        if ($contenido === false || strlen($contenido) < 1 || strlen($contenido) > 10485760) {
            return array('ok' => false, 'mensaje' => 'El archivo esta vacio, danado o supera el limite de 10 MB.');
        }
        $finfo= new finfo(FILEINFO_MIME_TYPE);
        $mime= $finfo->buffer($contenido);
        if (!in_array($mime, $permitidas[$ext], true)) {
            return array('ok' => false, 'mensaje' => 'El contenido del archivo no coincide con su extension.');
        }
        $directorio= dirname(__DIR__).DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR.'fotosMensaje';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
            return array('ok' => false, 'mensaje' => 'No se pudo preparar la carpeta de adjuntos.');
        }
        try {
            $sufijo= bin2hex(random_bytes(6));
        } catch (Exception $e) {
            $sufijo= str_replace('.', '', uniqid('', true));
        }
        $nombre= $cod_mensaje.'-'.$sufijo.'.'.$ext;
        $rutaAbsoluta= $directorio.DIRECTORY_SEPARATOR.$nombre;
        $bytesEsperados= strlen($contenido);
        $bytesEscritos= file_put_contents($rutaAbsoluta, $contenido, LOCK_EX);
        if ($bytesEscritos === false || intval($bytesEscritos) !== $bytesEsperados) {
            if (is_file($rutaAbsoluta)) {
                @unlink($rutaAbsoluta);
            }
            return array('ok' => false, 'mensaje' => 'No se pudo guardar el adjunto.');
        }
        $ruta= '/GoodVentaAsisCap/fotos/fotosMensaje/'.$nombre;
        $mysqli= conectar_al_servidor();
        $stmt= $mysqli->prepare("UPDATE mensaje SET url=?,tipo_adjunto=? WHERE cod_mensaje=? AND estado='activo'");
        $stmt->bind_param('ssi', $ruta, $tipoAdjunto, $cod_mensaje);
        $ok= $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        $mysqli->close();
        if (!$ok) {
            @unlink($rutaAbsoluta);
            return array('ok' => false, 'mensaje' => 'No se pudo vincular el adjunto con el mensaje.');
        }
        return array('ok' => true, 'cod_mensaje' => $cod_mensaje, 'url' => $ruta, 'tipo_adjunto' => $tipoAdjunto);
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

        $sql= "SELECT m.*, p.nombre_persona AS nombre_persona, u.url AS url_usuario FROM menciones m JOIN usuario u ON u.cod_usuario = m.cod_usuarioFK JOIN persona p ON p.cod_persona = u.cod_usuario $sqlFiltro $limite";

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
        $camposCentroFactura= ", NULL AS centro_factura_id, NULL AS centro_factura_tipo_documento,
            NULL AS centro_factura_estado_validacion, NULL AS centro_factura_estado_original,
            NULL AS centro_factura_nombre_contraparte, NULL AS centro_factura_documento_contraparte,
            NULL AS centro_factura_numero, NULL AS centro_factura_fecha_emision,
            NULL AS centro_factura_importe_total, NULL AS centro_factura_observaciones";
        $joinCentroFactura= "";
        if (centroFacturaEstructuraDisponible($mysqli)) {
            $camposCentroFactura= ", cf.id_factura AS centro_factura_id,
                cf.tipo_documento AS centro_factura_tipo_documento,
                cf.estado_validacion AS centro_factura_estado_validacion,
                cf.estado_original AS centro_factura_estado_original,
                cf.nombre_contraparte AS centro_factura_nombre_contraparte,
                cf.documento_contraparte AS centro_factura_documento_contraparte,
                cf.numero_factura AS centro_factura_numero,
                cf.fecha_emision AS centro_factura_fecha_emision,
                cf.importe_total AS centro_factura_importe_total,
                cf.observaciones AS centro_factura_observaciones";
            $joinCentroFactura= "
                LEFT JOIN centro_factura_archivo cfa ON cfa.cod_mensajeFK=m.cod_mensaje AND cfa.estado='activo'
                LEFT JOIN centro_factura cf ON cf.id_factura=cfa.id_facturaFK AND cf.estado_registro='activo'";
        }

        $sql= "SELECT * FROM (
                SELECT m.*, u.url AS url_usuario, p.nombre_persona AS nombre_persona".$camposRespuesta.$camposCentroFactura."
                FROM mensaje m
                LEFT JOIN usuario u ON u.cod_usuario=m.cod_usuarioFK
                LEFT JOIN persona p ON p.cod_persona=m.cod_usuarioFK".$joinRespuesta.$joinCentroFactura."
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

        $transaccionHiloMensaje= false;
        $hiloBloqueoMensaje= intval($cod_interConsulta);
        if ($hiloBloqueoMensaje <= 0 && intval($cod_mensaje) > 0) {
            $stmtHiloMensaje= $mysqli->prepare("SELECT cod_interConsultaFK FROM mensaje WHERE cod_mensaje=? LIMIT 1");
            if ($stmtHiloMensaje) {
                $codMensajeBloqueo= intval($cod_mensaje);
                $stmtHiloMensaje->bind_param('i', $codMensajeBloqueo);
                $stmtHiloMensaje->execute();
                $filaHiloMensaje= $stmtHiloMensaje->get_result()->fetch_assoc();
                $stmtHiloMensaje->close();
                $hiloBloqueoMensaje= $filaHiloMensaje ? intval($filaHiloMensaje['cod_interConsultaFK']) : 0;
            }
        }
        if ($hiloBloqueoMensaje <= 0 || !$mysqli->begin_transaction()) {
            echo json_encode(array("1" => "error", "mensaje" => "No se pudo validar el hilo del mensaje."));
            $mysqli->close();
            exit;
        }
        $stmtHiloMensaje= $mysqli->prepare("SELECT estado FROM interconsulta WHERE cod_interConsulta=? LIMIT 1 FOR UPDATE");
        if (!$stmtHiloMensaje) {
            $mysqli->rollback();
            echo json_encode(array("1" => "error", "mensaje" => "No se pudo bloquear el hilo del mensaje."));
            $mysqli->close();
            exit;
        }
        $stmtHiloMensaje->bind_param('i', $hiloBloqueoMensaje);
        $stmtHiloMensaje->execute();
        $hiloMensaje= $stmtHiloMensaje->get_result()->fetch_assoc();
        $stmtHiloMensaje->close();
        if (!$hiloMensaje || strtolower(trim((string)$hiloMensaje['estado'])) === 'inactivo') {
            $mysqli->rollback();
            echo json_encode(array("1" => "error", "mensaje" => "El hilo fue archivado y ya no admite nuevos mensajes."));
            $mysqli->close();
            exit;
        }
        $transaccionHiloMensaje= true;

        $esMensajeNuevo= empty($cod_mensaje);
        if ($esMensajeNuevo) {
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
            if ($transaccionHiloMensaje) { $mysqli->rollback(); }
            $informacion = array("1" => "error", "mensaje" => "No se pudo guardar el mensaje. Intente nuevamente.");
            echo json_encode($informacion);
            if ($stmt) { $stmt->close(); }
            $mysqli->close();
            exit;
        }
        
        if ($esMensajeNuevo) {
            $cod_mensaje = $stmt->insert_id;
        }
        if ($transaccionHiloMensaje && !$mysqli->commit()) {
            $mysqli->rollback();
            echo json_encode(array("1" => "error", "mensaje" => "No se pudo confirmar el mensaje."));
            $stmt->close();
            $mysqli->close();
            exit;
        }
        $transaccionHiloMensaje= false;

        if ($esMensajeNuevo && isset($ids_menciones)) {
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
            interconsultaLecturasSincronizarParticipantesHilo($mysqli, $cod_interConsulta, $fecha_creacion);

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
        $sqlOrdenMenciones= "";
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
                case 'codigos_hilos':
                    $codigosHilos= array();
                    foreach ((array)$value as $codigoHilo) {
                        $codigoHilo= intval($codigoHilo);
                        if ($codigoHilo > 0) { $codigosHilos[$codigoHilo]= $codigoHilo; }
                    }
                    $sqlFiltro .= count($codigosHilos) > 0
                        ? "ic.cod_interConsulta IN (".implode(',', $codigosHilos).")"
                        : "1=0";
                    break;
                case 'cod_usuarioFK':
                    $codUsuarioFiltro= intval($value);
                    $sqlFiltro .= interconsultaAccesoCondicionLocalSql($codUsuarioFiltro, 'ic');
                    $sqlFiltroMenciones = " AND mc.cod_usuarioFK = ".$codUsuarioFiltro." ";
                    break;
                case 'cod_interConsulta':
                    $sqlFiltro .= "ic.cod_interConsulta = ".intval($value);
                    break;
                case 'cod_localFK':
                    $codLocalFiltro= intval($value);
                    $sqlFiltro .= "(ic.cod_localFK = ".$codLocalFiltro."
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
                                AND vt.cod_local = ".$codLocalFiltro."
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
                    $literalResponsable= literalTextoSqlInterConsulta($value);
                    $sqlFiltro .= "(SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE CONCAT('%', ".$literalResponsable.", '%')";
                    break;
                case 'id_interConsulta_distinto':
                    $sqlFiltro .= "ic.cod_interConsulta <> ".intval($value);
                    break;
                case 'nombre_cliente':
                    $literalCliente= literalTextoSqlInterConsulta($value);
                    $sqlFiltro .= "CONCAT(
                        (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                        (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK), ' ',
                        IFNULL((SELECT ip.nombre_paciente_snapshot FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                        IFNULL((SELECT ip.cedula FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                        IFNULL((SELECT ip.cod_clienteFK_principal FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), '')
                    ) LIKE CONCAT('%', ".$literalCliente.", '%')";
                    break;
                case 'cod_clienteFK':
                    $codClienteFiltro= intval($value);
                    $sqlFiltro .= "((SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK) = ".$codClienteFiltro."
                        OR EXISTS(SELECT 1 FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.cod_clienteFK_principal = ".$codClienteFiltro." AND ip.estado = 'activo'))";
                    break;
                case 'usuario_vinculado':
                    $usuarioVinculado= intval($value);
                    $sqlFiltro .= "EXISTS(select cod_mencion from menciones mc JOIN mensaje mj WHERE mc.cod_mensajeFK = mj.cod_mensaje AND mj.cod_interConsultaFK= ic.cod_interConsulta AND mc.cod_usuarioFK = ".$usuarioVinculado." AND mc.estado='activo' AND mj.estado='activo')";
                    break;
                case 'filtro_menciones':
                    $modoMenciones= strtolower(trim((string)$value));
                    $codUsuarioMencion= isset($filtros['cod_usuarioFK']) ? intval($filtros['cod_usuarioFK']) : 0;
                    if ($codUsuarioMencion <= 0 || !in_array($modoMenciones, array('pendientes','todas'), true)) {
                        $sqlFiltro .= "1=1";
                        break;
                    }
                    $marcaMencion= '@{'.$codUsuarioMencion.'}';
                    $pendienteMencion= $modoMenciones === 'pendientes' ? " AND mc_exp.isLeido=0" : "";
                    $sqlFiltro .= "EXISTS(
                        SELECT 1
                        FROM menciones mc_exp
                        INNER JOIN mensaje mj_exp ON mj_exp.cod_mensaje=mc_exp.cod_mensajeFK
                        WHERE mj_exp.cod_interConsultaFK=ic.cod_interConsulta
                          AND mj_exp.estado='activo'
                          AND mc_exp.estado='activo'
                          AND mc_exp.cod_usuarioFK=".$codUsuarioMencion.
                          $pendienteMencion."
                          AND mj_exp.fecha_creacion <= NOW()
                          AND mj_exp.contenido LIKE '%".addslashes($marcaMencion)."%'
                        LIMIT 1
                    )";
                    $sqlOrdenMenciones= "(SELECT MAX(mj_ord.cod_mensaje)
                        FROM menciones mc_ord
                        INNER JOIN mensaje mj_ord ON mj_ord.cod_mensaje=mc_ord.cod_mensajeFK
                        WHERE mj_ord.cod_interConsultaFK=ic.cod_interConsulta
                          AND mj_ord.estado='activo'
                          AND mc_ord.estado='activo'
                          AND mc_ord.cod_usuarioFK=".$codUsuarioMencion.
                          ($modoMenciones === 'pendientes' ? " AND mc_ord.isLeido=0" : "")."
                          AND mj_ord.fecha_creacion <= NOW()
                          AND mj_ord.contenido LIKE '%".addslashes($marcaMencion)."%')";
                    break;
                case 'busqueda_global':
                    $valorBusqueda = literalTextoSqlInterConsulta($value);
                    $sqlFiltro .= "(
                        ic.cod_interConsulta LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        ic.asunto LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        ic.estado LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        ic.tipo LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        ic.fecha_creacion LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        (SELECT Nombre FROM local WHERE cod_local = ic.cod_localFK) LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        (SELECT nombre_persona from persona where cod_persona = ic.cod_usuarioFK_create) LIKE CONCAT('%', ".$valorBusqueda.", '%') OR
                        CONCAT(
                            (SELECT nombre_persona from persona join venta vt where cod_persona = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                            (SELECT ci_cliente from cliente join venta vt where cod_cliente = vt.cod_clienteFK AND vt.cod_venta= ic.cod_ventaFK), ' ',
                            (SELECT cod_clienteFK FROM venta WHERE cod_venta = ic.cod_ventaFK), ' ',
                            IFNULL((SELECT ip.nombre_paciente_snapshot FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                            IFNULL((SELECT ip.cedula FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), ''), ' ',
                            IFNULL((SELECT ip.cod_clienteFK_principal FROM interconsulta_paciente ip WHERE ip.cod_interConsultaFK = ic.cod_interConsulta AND ip.estado = 'activo' LIMIT 1), '')
                        ) LIKE CONCAT('%', ".$valorBusqueda.", '%')
                    )";
                    break;
                case 'fecha_desde':
                    $sqlFiltro .= esFechaFiltroInterConsultaValida($value)
                        ? "ic.fecha_creacion >= '".$value." 00:00:00'"
                        : "1=0";
                    break;
                case 'fecha_hasta':
                    $sqlFiltro .= esFechaFiltroInterConsultaValida($value)
                        ? "ic.fecha_creacion <= '".$value." 23:59:59'"
                        : "1=0";
                    break;
                case 'fecha_limite':
                    if (esFechaFiltroInterConsultaValida($value, 'Y-m-d H:i:s')) {
                        $sqlFiltroMenciones .= " AND mj.fecha_creacion <= '".$value."' ";
                        $sqlFiltroMensaje .= " AND mj.fecha_creacion > '".$value."' ";
                        $sqlFiltroFechaLimite .= " AND mj2.fecha_creacion <= '".$value."'";
                    }
                    break;
                default:
                    $columnasEnteras= array('cod_ventaFK');
                    $columnasTexto= array('asunto', 'observacion');
                    if (in_array($key, $columnasEnteras, true)) {
                        $sqlFiltro .= "ic.".$key." = ".intval($value);
                    } else if (in_array($key, $columnasTexto, true)) {
                        $literalTexto= literalTextoSqlInterConsulta($value);
                        $sqlFiltro .= "ic.".$key." LIKE CONCAT('%', ".$literalTexto.", '%')";
                    } else {
                        // Una clave no reconocida nunca se convierte en nombre de
                        // columna. Se ignora sin ampliar ni alterar el alcance local.
                        $sqlFiltro .= "1=1";
                    }
                    break;
            }
        }

        return array($sqlFiltro, $sqlFiltroMenciones, $sqlFiltroMensaje, $sqlFiltroFechaLimite, $sqlOrdenMenciones);
    }

    function obtenerCantidadInterConsulta($filtros= array()) {
        $mysqli=conectar_al_servidor();
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
        list($sqlFiltro, $sqlFiltroMenciones, $sqlFiltroMensaje, $sqlFiltroFechaLimite, $sqlOrdenMenciones) = construirFiltrosInterConsulta($filtros);
        $seleccionOrdenMenciones= $sqlOrdenMenciones !== '' ? $sqlOrdenMenciones.' AS ultima_mencion_explicita,' : '0 AS ultima_mencion_explicita,';
        $ordenMenciones= $sqlOrdenMenciones !== '' ? 'ultima_mencion_explicita DESC,' : '';

        if ($limite === 0 || $limite === '0') {
            $limite = '';
        } else {
            $limite = "LIMIT $limite";
        }

        $codigosResumen= array();
        if (isset($filtros['codigos_hilos'])) {
            foreach ((array)$filtros['codigos_hilos'] as $codigoResumen) {
                $codigoResumen= intval($codigoResumen);
                if ($codigoResumen > 0) { $codigosResumen[$codigoResumen]= $codigoResumen; }
            }
        }
        $listaCodigosResumen= count($codigosResumen) > 0 ? implode(',', $codigosResumen) : '';
        $filtroIpvLoc= $listaCodigosResumen !== '' ? " AND ipv_loc.cod_interConsultaFK IN (".$listaCodigosResumen.")" : '';
        $filtroIpvCred= $listaCodigosResumen !== '' ? " AND ipv_cred.cod_interConsultaFK IN (".$listaCodigosResumen.")" : '';
        $filtroIcCred= $listaCodigosResumen !== '' ? " AND ic_cred.cod_interConsulta IN (".$listaCodigosResumen.")" : '';
        $filtroIpvAg= $listaCodigosResumen !== '' ? " AND ipv_ag.cod_interConsultaFK IN (".$listaCodigosResumen.")" : '';
        $filtroIpAg= $listaCodigosResumen !== '' ? " AND ip_ag.cod_interConsultaFK IN (".$listaCodigosResumen.")" : '';
        $filtroIcAg= $listaCodigosResumen !== '' ? " AND ic_ag.cod_interConsulta IN (".$listaCodigosResumen.")" : '';
        $filtroIpPlan= $listaCodigosResumen !== '' ? " AND ip_plan.cod_interConsultaFK IN (".$listaCodigosResumen.")" : '';
        $filtroIpvNew= $listaCodigosResumen !== '' ? " AND ipv_new.cod_interConsultaFK IN (".$listaCodigosResumen.")" : '';
        $filtroIcNew= $listaCodigosResumen !== '' ? " AND ic_new.cod_interConsulta IN (".$listaCodigosResumen.")" : '';

        $condicionCreditoActivoResumen = condicionCreditoActivoHiloInterConsulta("cr_sum");
        $saldoCapitalCreditoResumen = "GREATEST(((IFNULL(cr_sum.Monto,0)-IFNULL(cr_sum.descuento,0))-IFNULL(pg_sum.pago_cuota,0)),0)";
        $saldoInteresCreditoResumen = "GREATEST(((IFNULL(cr_sum.totalinteres,0)+IFNULL(cr_sum.deudaInteres,0))-IFNULL(pg_sum.pago_interes,0)),0)";
        $saldoPendienteCreditoResumen = "(".$saldoCapitalCreditoResumen." + ".$saldoInteresCreditoResumen.")";
        $condicionCreditoPendienteResumen = "(".$saldoPendienteCreditoResumen." > 0)";
        $codUsuarioAgendaResumen = isset($filtros['cod_usuarioFK']) ? intval($filtros['cod_usuarioFK']) : 0;
        $codLocalAgendaResumen = isset($filtros['cod_localFK']) && intval($filtros['cod_localFK']) > 0
            ? intval($filtros['cod_localFK']) : 0;
        $condicionLocalAgendaResumen = interconsultaOperacionCondicionLocalAgenda(
            $codUsuarioAgendaResumen,
            $codLocalAgendaResumen,
            'ag_sum',
            'co_ag_sum',
            $mysqli
        );
        $condicionAgendaActivaResumen = "ag_sum.fecha >= CURDATE()
            AND UPPER(TRIM(IFNULL(ag_sum.estado,''))) NOT IN ('CANCELADO','ATENDIDO')
            AND ".$condicionLocalAgendaResumen;
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
                WHERE ipv_loc.estado = 'activo' ".$filtroIpvLoc."
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
                    WHERE ipv_cred.estado = 'activo' ".$filtroIpvCred."
                    UNION
                    SELECT ic_cred.cod_interConsulta AS cod_interConsultaFK, ic_cred.cod_ventaFK
                    FROM interconsulta ic_cred
                    WHERE IFNULL(ic_cred.cod_ventaFK,0) > 0 ".$filtroIcCred."
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
                    LEFT JOIN consultorios co_ag_sum ON co_ag_sum.id_consultorio = ag_sum.id_consultorio
                    WHERE ipv_ag.estado = 'activo' ".$filtroIpvAg." AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ip_ag.cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta_paciente ip_ag
                    INNER JOIN agenda ag_sum ON ag_sum.id_paciente = ip_ag.cod_clienteFK_principal
                    LEFT JOIN consultorios co_ag_sum ON co_ag_sum.id_consultorio = ag_sum.id_consultorio
                    WHERE ip_ag.estado = 'activo' ".$filtroIpAg." AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ipv_ag.cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta_paciente_venta ipv_ag
                    INNER JOIN venta vt_ag ON vt_ag.cod_venta = ipv_ag.cod_ventaFK
                    INNER JOIN agenda ag_sum ON ag_sum.id_paciente = vt_ag.cod_clienteFK
                    LEFT JOIN consultorios co_ag_sum ON co_ag_sum.id_consultorio = ag_sum.id_consultorio
                    WHERE ipv_ag.estado = 'activo' ".$filtroIpvAg." AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ic_ag.cod_interConsulta AS cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta ic_ag
                    INNER JOIN agenda ag_sum ON ag_sum.cod_ventaFK = ic_ag.cod_ventaFK
                    LEFT JOIN consultorios co_ag_sum ON co_ag_sum.id_consultorio = ag_sum.id_consultorio
                    WHERE IFNULL(ic_ag.cod_ventaFK,0) > 0 ".$filtroIcAg." AND ".$condicionAgendaActivaResumen."
                    UNION
                    SELECT ic_ag.cod_interConsulta AS cod_interConsultaFK, ag_sum.id_agenda, ag_sum.fecha, ag_sum.hora_inicio, ag_sum.estado, ag_sum.motivo, ag_sum.id_profesional, ag_sum.creado_por
                    FROM interconsulta ic_ag
                    INNER JOIN venta vt_ag ON vt_ag.cod_venta = ic_ag.cod_ventaFK
                    INNER JOIN agenda ag_sum ON ag_sum.id_paciente = vt_ag.cod_clienteFK
                    LEFT JOIN consultorios co_ag_sum ON co_ag_sum.id_consultorio = ag_sum.id_consultorio
                    WHERE IFNULL(ic_ag.cod_ventaFK,0) > 0 ".$filtroIcAg." AND ".$condicionAgendaActivaResumen."
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
                    ".$filtroIpPlan."
                    AND pd_plan.activo = 1
                    AND (".$exprCedulaPlanResumen." = ip_plan.cedula_normalizada OR ".$exprCedulaClientePlanResumen." = ip_plan.cedula_normalizada)
                GROUP BY ip_plan.cod_interConsultaFK
            ) seg_plan ON seg_plan.cod_interConsultaFK = ic.cod_interConsulta
            LEFT JOIN (
                SELECT hilo_venta_reciente.cod_interConsultaFK,
                    COUNT(DISTINCT CASE
                        WHEN hilo_venta_reciente.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        THEN hilo_venta_reciente.cod_ventaFK
                        ELSE NULL
                    END) AS seguimiento_ventas_recientes,
                    IFNULL(MAX(CASE
                        WHEN hilo_venta_reciente.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        THEN hilo_venta_reciente.fecha_venta
                        ELSE NULL
                    END),'') AS seguimiento_ultima_venta_reciente
                FROM (
                    SELECT ipv_new.cod_interConsultaFK, ipv_new.cod_ventaFK, vt_new.fecha_venta
                    FROM interconsulta_paciente_venta ipv_new
                    INNER JOIN venta vt_new ON vt_new.cod_venta = ipv_new.cod_ventaFK
                    WHERE ipv_new.estado = 'activo'
                        ".$filtroIpvNew."
                        AND IFNULL(vt_new.cod_clienteFK,0) <> 7
                        AND NOT EXISTS(SELECT 1 FROM cancelaciones cn_new WHERE cn_new.cod_venta = vt_new.cod_venta)
                    UNION
                    SELECT ic_new.cod_interConsulta AS cod_interConsultaFK, ic_new.cod_ventaFK, vt_new.fecha_venta
                    FROM interconsulta ic_new
                    INNER JOIN venta vt_new ON vt_new.cod_venta = ic_new.cod_ventaFK
                    WHERE IFNULL(ic_new.cod_ventaFK,0) > 0
                        ".$filtroIcNew."
                        AND IFNULL(vt_new.cod_clienteFK,0) <> 7
                        AND NOT EXISTS(SELECT 1 FROM cancelaciones cn_new WHERE cn_new.cod_venta = vt_new.cod_venta)
                ) hilo_venta_reciente
                GROUP BY hilo_venta_reciente.cod_interConsultaFK
            ) seg_venta_reciente ON seg_venta_reciente.cod_interConsultaFK = ic.cod_interConsulta";

        // Se separa la tabla venta de la interconsulta ya que este es opcional
        $sql= "SELECT ic.*, 
            ".$seleccionOrdenMenciones."
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
            ORDER BY ".$ordenMenciones."
            cantMensajesNoLeidos DESC,
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
        $inicioOperacionInterConsulta = microtime(true);
        $operacion = isset($_POST['accion']) ? $_POST['accion'] : '';
        $operacion = mb_convert_encoding((string)($operacion), 'ISO-8859-1', 'UTF-8');
        registrarMedicionOperacionInterConsulta($operacion, $inicioOperacionInterConsulta);
        verificarOperacionInterConsulta($operacion);
    }
?>

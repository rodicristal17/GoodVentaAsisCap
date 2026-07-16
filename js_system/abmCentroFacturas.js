var centroFacturasEstado = {
    contexto: null,
    tab: "entrantes",
    offset: 0,
    limite: 50,
    total: 0,
    filtroRapido: "",
    seleccion: {},
    detalleFactura: 0,
    detalleLote: 0,
    cargandoContexto: false,
    inicializado: false,
    esperaContexto: [],
    versionContexto: 0,
    temporizadorPermisos: null,
    periodoMes: "",
    metricasColapsadas: true
};

function centroFacturasEscapar(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function centroFacturasNumero(valor) {
    var numero = Number(valor || 0);
    return new Intl.NumberFormat("es-PY", { maximumFractionDigits: 2 }).format(numero);
}

function centroFacturasFecha(valor, conHora) {
    if (!valor) { return "—"; }
    var partes = String(valor).replace("T", " ").split(" ");
    var fecha = partes[0].split("-");
    if (fecha.length !== 3) { return centroFacturasEscapar(valor); }
    return fecha[2] + "/" + fecha[1] + "/" + fecha[0] + (conHora && partes[1] ? " " + partes[1].slice(0, 5) : "");
}

function centroFacturasMesActual() {
    var hoy = new Date();
    return hoy.getFullYear() + "-" + String(hoy.getMonth() + 1).padStart(2, "0");
}

function centroFacturasAplicarPeriodoMes(mes, buscar) {
    if (!/^\d{4}-\d{2}$/.test(String(mes || ""))) { mes = centroFacturasMesActual(); }
    var partes = mes.split("-");
    var anio = Number(partes[0]);
    var numeroMes = Number(partes[1]);
    if (numeroMes < 1 || numeroMes > 12) { mes = centroFacturasMesActual(); partes = mes.split("-"); anio = Number(partes[0]); numeroMes = Number(partes[1]); }
    var ultimoDia = new Date(anio, numeroMes, 0).getDate();
    var desde = mes + "-01";
    var hasta = mes + "-" + String(ultimoDia).padStart(2, "0");
    centroFacturasEstado.periodoMes = mes;
    var inputMes = document.getElementById("centroFacturasMes");
    var inputDesde = document.getElementById("centroFacturasFechaDesde");
    var inputHasta = document.getElementById("centroFacturasFechaHasta");
    if (inputMes) { inputMes.value = mes; }
    if (inputDesde) { inputDesde.value = desde; }
    if (inputHasta) { inputHasta.value = hasta; }
    centroFacturasActualizarTextoPeriodo(desde, hasta, mes);
    if (buscar) { centroFacturasBuscar(); }
}

function centroFacturasActualizarTextoPeriodo(desde, hasta, mes) {
    var nombres = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    var partes = String(mes || "").split("-");
    var titulo = partes.length === 2 ? nombres[Number(partes[1]) - 1] + " " + partes[0] : "Período personalizado";
    var textoMes = document.getElementById("centroFacturasMesTexto");
    var textoPeriodo = document.getElementById("centroFacturasPeriodoTexto");
    var resumen = document.getElementById("centroFacturasResumenTexto");
    var rango = centroFacturasFecha(desde) + " – " + centroFacturasFecha(hasta);
    if (textoMes) { textoMes.textContent = titulo; }
    if (textoPeriodo) { textoPeriodo.textContent = rango; }
    if (resumen) { resumen.textContent = titulo + " · " + rango; }
}

function centroFacturasSeleccionarMes(mes) {
    centroFacturasAplicarPeriodoMes(mes, true);
}

function centroFacturasCambiarMes(direccion) {
    var mes = centroFacturasEstado.periodoMes || centroFacturasMesActual();
    var partes = mes.split("-");
    var fecha = new Date(Number(partes[0]), Number(partes[1]) - 1 + Number(direccion || 0), 1);
    centroFacturasAplicarPeriodoMes(fecha.getFullYear() + "-" + String(fecha.getMonth() + 1).padStart(2, "0"), true);
}

function centroFacturasCambiarRangoManual() {
    var desde = document.getElementById("centroFacturasFechaDesde").value;
    var hasta = document.getElementById("centroFacturasFechaHasta").value;
    var mismoMes = desde && hasta && desde.slice(0, 7) === hasta.slice(0, 7) && desde.slice(8, 10) === "01";
    var mes = mismoMes ? desde.slice(0, 7) : "";
    if (mes) {
        var ultimo = new Date(Number(mes.slice(0, 4)), Number(mes.slice(5, 7)), 0).getDate();
        if (Number(hasta.slice(8, 10)) !== ultimo) { mes = ""; }
    }
    centroFacturasEstado.periodoMes = mes;
    var inputMes = document.getElementById("centroFacturasMes");
    if (inputMes) { inputMes.value = mes; }
    centroFacturasActualizarTextoPeriodo(desde, hasta, mes);
    centroFacturasBuscar();
}

function centroFacturasFijarMetricasColapsadas(colapsadas) {
    var resumen = document.getElementById("centroFacturasResumen");
    var boton = document.getElementById("centroFacturasResumenToggle");
    centroFacturasEstado.metricasColapsadas = !!colapsadas;
    if (!resumen) { return; }
    resumen.classList.toggle("is-collapsed", centroFacturasEstado.metricasColapsadas);
    if (boton) { boton.setAttribute("aria-expanded", centroFacturasEstado.metricasColapsadas ? "false" : "true"); }
}

function centroFacturasAlternarMetricas() {
    var resumen = document.getElementById("centroFacturasResumen");
    if (!resumen) { return; }
    centroFacturasFijarMetricasColapsadas(!resumen.classList.contains("is-collapsed"));
}

function centroFacturasPermiso(codigo) {
    return !!(centroFacturasEstado.contexto && centroFacturasEstado.contexto.permisos && centroFacturasEstado.contexto.permisos[codigo]);
}

function centroFacturasFormData(accion, extras) {
    var datos = new FormData();
    datos.append("useru", typeof userid !== "undefined" ? userid : "");
    datos.append("passu", typeof passuser !== "undefined" ? passuser : "");
    datos.append("navegador", typeof navegador !== "undefined" ? navegador : "");
    datos.append("funt", accion);
    Object.keys(extras || {}).forEach(function (clave) {
        var valor = extras[clave];
        if (valor !== undefined && valor !== null) {
            datos.append(clave, typeof valor === "object" ? JSON.stringify(valor) : valor);
        }
    });
    return datos;
}

function centroFacturasSolicitar(accion, extras, opciones) {
    opciones = opciones || {};
    var datos = centroFacturasFormData(accion, extras);
    (opciones.archivos || []).forEach(function (archivo) { datos.append("archivos[]", archivo); });
    if (opciones.cargando !== false) { centroFacturasMostrarCargando(true); }
    return $.ajax({
        data: datos,
        url: "../php_system/abmCentroFacturas.php",
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json"
    }).always(function () {
        if (opciones.cargando !== false) { centroFacturasMostrarCargando(false); }
    }).fail(function () {
        centroFacturasAviso("No se pudo comunicar con el servidor. Intente nuevamente.", "error");
    });
}

function centroFacturasMostrarCargando(mostrar) {
    var elemento = document.getElementById("centroFacturasCargando");
    if (elemento) { elemento.hidden = !mostrar; }
}

function centroFacturasAviso(mensaje, tipo) {
    var aviso = document.getElementById("centroFacturasAviso");
    if (!aviso) { return; }
    aviso.textContent = mensaje || "";
    aviso.className = "centro-facturas-notice" + (tipo ? " is-" + tipo : "");
    aviso.hidden = !mensaje;
    if (mensaje && tipo === "success") {
        window.setTimeout(function () { if (aviso.textContent === mensaje) { aviso.hidden = true; } }, 4500);
    }
}

function centroFacturasAplicarVisibilidadAcceso(visible) {
    var menu = document.getElementById("divMenuCentroFacturas");
    if (menu) { menu.style.display = visible ? "table" : "none"; }
    var accesos = document.querySelectorAll('[data-dashboard-rendered-shortcut="1"][data-dashboard-access-key="centro_facturas"]');
    Array.prototype.forEach.call(accesos, function (acceso) {
        acceso.style.display = visible ? "" : "none";
    });
}

function centroFacturasPuedeVerSegunSesion() {
    if (typeof permisoAccesoUser === "function") {
        return permisoAccesoUser("VERCENTROFACTURAS", "accion");
    }
    return !!(typeof accesosuser !== "undefined" && accesosuser
        && accesosuser.VERCENTROFACTURAS
        && String(accesosuser.VERCENTROFACTURAS.accion || "").toUpperCase() === "SI");
}

function centroFacturasResolverEsperaContexto(permitido) {
    var callbacks = centroFacturasEstado.esperaContexto.slice(0);
    centroFacturasEstado.esperaContexto = [];
    callbacks.forEach(function (callback) {
        callback(permitido);
    });
}

function centroFacturasCargarContexto(callback, silencioso) {
    if (centroFacturasEstado.contexto) {
        if (typeof callback === "function") { callback(true); }
        return;
    }
    if (typeof callback === "function") { centroFacturasEstado.esperaContexto.push(callback); }
    if (centroFacturasEstado.cargandoContexto) { return; }
    if (typeof userid === "undefined" || !userid || typeof passuser === "undefined" || !passuser) {
        if (!silencioso) { centroFacturasAviso("No se pudo identificar la sesion activa.", "error"); }
        centroFacturasResolverEsperaContexto(false);
        return;
    }
    centroFacturasEstado.cargandoContexto = true;
    var versionSolicitud = centroFacturasEstado.versionContexto;
    centroFacturasSolicitar("contexto", {}, { cargando: !silencioso }).done(function (respuesta) {
        if (versionSolicitud !== centroFacturasEstado.versionContexto) { return; }
        if (!respuesta || !respuesta.ok) {
            centroFacturasAplicarVisibilidadAcceso(false);
            if (!silencioso) { centroFacturasAviso((respuesta && respuesta.mensaje) || "No tiene acceso al Centro de Facturas.", "error"); }
            centroFacturasResolverEsperaContexto(false);
            return;
        }
        centroFacturasEstado.contexto = respuesta;
        centroFacturasAplicarVisibilidadAcceso(true);
        centroFacturasPrepararContexto();
        centroFacturasPintarBadge(respuesta.metricas || {});
        centroFacturasResolverEsperaContexto(true);
    }).fail(function () {
        if (versionSolicitud !== centroFacturasEstado.versionContexto) { return; }
        centroFacturasAplicarVisibilidadAcceso(false);
        centroFacturasResolverEsperaContexto(false);
    }).always(function () {
        if (versionSolicitud === centroFacturasEstado.versionContexto) {
            centroFacturasEstado.cargandoContexto = false;
        }
    });
}

function centroFacturasPrepararAccesoInicial() {
    var permitido = centroFacturasPuedeVerSegunSesion();
    centroFacturasAplicarVisibilidadAcceso(permitido);
    if (permitido) {
        centroFacturasCargarContexto(null, true);
    }
}

function centroFacturasActualizarPermisoSesion(codigo, accion) {
    var permisosCentro = [
        "VERCENTROFACTURAS", "VERCENTROFACTURASTODOSLOCALES", "REGISTRARFACTURAHILO",
        "REGISTRARFACTURAMANUAL", "VINCULARPAGOFACTURA", "ENVIARORIGINALFACTURA",
        "RECIBIRORIGINALFACTURA", "GESTIONARLOTESFACTURAS", "ADMINCENTROFACTURAS"
    ];
    codigo = String(codigo || "").trim().toUpperCase();
    if (permisosCentro.indexOf(codigo) < 0) { return; }

    centroFacturasEstado.versionContexto++;
    centroFacturasEstado.contexto = null;
    centroFacturasEstado.cargandoContexto = false;
    centroFacturasResolverEsperaContexto(false);
    var puedeVer = centroFacturasPuedeVerSegunSesion();
    centroFacturasAplicarVisibilidadAcceso(puedeVer);
    if (centroFacturasEstado.temporizadorPermisos) {
        window.clearTimeout(centroFacturasEstado.temporizadorPermisos);
        centroFacturasEstado.temporizadorPermisos = null;
    }

    if (codigo === "VERCENTROFACTURAS" && typeof dashboardShortcutRefreshAccessPermission === "function") {
        dashboardShortcutRefreshAccessPermission("centro_facturas");
    }
    if (!puedeVer) {
        verCerrarCentroFacturas(false);
        return;
    }

    centroFacturasEstado.temporizadorPermisos = window.setTimeout(function () {
        centroFacturasEstado.temporizadorPermisos = null;
        centroFacturasCargarContexto(null, true);
    }, 180);
}

function centroFacturasPrepararContexto() {
    var contexto = centroFacturasEstado.contexto || {};
    var selectLocal = document.getElementById("centroFacturasFiltroLocal");
    if (selectLocal) {
        var todos = centroFacturasPermiso("VERCENTROFACTURASTODOSLOCALES") ? '<option value="">Todos los locales</option>' : "";
        selectLocal.innerHTML = todos + (contexto.locales || []).map(function (local) {
            return '<option value="' + Number(local.cod_local) + '">' + centroFacturasEscapar(local.Nombre) + "</option>";
        }).join("");
    }
    var proveedor = document.getElementById("centroFacturasFiltroProveedor");
    var funcionario = document.getElementById("centroFacturasFiltroFuncionario");
    var responsable = document.getElementById("centroFacturasFiltroResponsable");
    if (proveedor) { proveedor.innerHTML = '<option value="">Todos</option>' + centroFacturasOpciones(contexto.proveedores, "cod_proveedor", "nombre_persona", ""); }
    if (funcionario) { funcionario.innerHTML = '<option value="">Todos</option>' + centroFacturasOpciones(contexto.funcionarios, "cod_usuario", "nombre_persona", ""); }
    if (responsable) { responsable.innerHTML = '<option value="">Todos</option>' + centroFacturasOpciones(contexto.funcionarios, "cod_usuario", "nombre_persona", ""); }
    var botonManual = document.getElementById("btnCentroFacturasManual");
    var botonConfig = document.getElementById("btnCentroFacturasConfig");
    if (botonManual) { botonManual.hidden = !centroFacturasPermiso("REGISTRARFACTURAMANUAL"); }
    if (botonConfig) { botonConfig.hidden = !centroFacturasPermiso("ADMINCENTROFACTURAS"); }
    if (!centroFacturasEstado.periodoMes) { centroFacturasAplicarPeriodoMes(centroFacturasMesActual(), false); }
    centroFacturasFijarMetricasColapsadas(centroFacturasEstado.metricasColapsadas);
}

function verCerrarCentroFacturas(mostrar) {
    var ventana = document.getElementById("divCentroFacturas");
    if (!ventana) { return; }
    if (!mostrar) {
        ventana.style.display = "none";
        ventana.setAttribute("aria-hidden", "true");
        centroFacturasCerrarDetalle();
        centroFacturasCerrarDialogo();
        return;
    }
    centroFacturasFijarMetricasColapsadas(true);
    centroFacturasCargarContexto(function (permitido) {
        if (!permitido) { return; }
        ventana.style.display = "block";
        ventana.setAttribute("aria-hidden", "false");
        centroFacturasEstado.inicializado = true;
        centroFacturasRecargar();
    }, false);
}

function centroFacturasActualizarBadge() {
    centroFacturasCargarContexto(function (permitido) {
        if (!permitido) { return; }
        centroFacturasSolicitar("metricas", {}, { cargando: false }).done(function (respuesta) {
            if (respuesta && respuesta.ok) {
                centroFacturasPintarBadge(respuesta.metricas || {});
            }
        });
    }, true);
}

function centroFacturasPintarBadge(metricas) {
    var total = Number(metricas.alertas_total || 0);
    var badges = document.querySelectorAll('#centroFacturasMenuBadge, [data-original-id="centroFacturasMenuBadge"]');
    var plantilla = (typeof DASHBOARD_ACCESS_REGISTRY !== "undefined" && DASHBOARD_ACCESS_REGISTRY.centro_facturas)
        ? DASHBOARD_ACCESS_REGISTRY.centro_facturas.template : null;
    var badgePlantilla = plantilla ? plantilla.querySelector('#centroFacturasMenuBadge, [data-original-id="centroFacturasMenuBadge"]') : null;
    Array.prototype.forEach.call(badges, function (badge) {
        badge.textContent = total > 99 ? "99+" : total;
        badge.hidden = total < 1;
    });
    if (badgePlantilla) {
        badgePlantilla.textContent = total > 99 ? "99+" : total;
        badgePlantilla.hidden = total < 1;
    }
}

function centroFacturasPintarMetricas(metricas, tab) {
    var contenedor = document.getElementById("centroFacturasMetricas");
    if (!contenedor) { return; }
    tab = tab || centroFacturasEstado.tab;
    var resumen = document.getElementById("centroFacturasResumen");
    if (resumen) { resumen.hidden = tab === "lotes"; }
    if (tab === "lotes") { contenedor.innerHTML = ""; return; }
    var items = tab === "emitidas" ? [
        ["eventos_periodo", "Cobros del período", "fa-cash-register", "", ""],
        ["ventas_contado", "Ventas al contado", "fa-money-bill-wave", "", "contado"],
        ["cuotas_cobradas", "Cuotas cobradas", "fa-calendar-check", "", "cuotas"],
        ["facturadas", "Con factura", "fa-file-circle-check", "", "con_factura"],
        ["sin_factura", "Sin factura", "fa-file-circle-xmark", "is-alert", "sin_factura"],
        ["observadas", "Datos observados", "fa-triangle-exclamation", "is-danger", "observadas"],
        ["anuladas", "Anulados", "fa-ban", "is-danger", "anuladas"]
    ] : [
        ["gastos_periodo", "Gastos del período", "fa-receipt", "", ""],
        ["con_factura", "Con factura", "fa-file-invoice", "", "con_factura"],
        ["con_recibo", "Con recibo", "fa-file-lines", "", "con_recibo"],
        ["por_vincular", "Por vincular", "fa-link", "is-alert", "por_vincular"],
        ["sin_comprobante", "Sin comprobante", "fa-file-circle-xmark", "is-alert", "sin_comprobante"],
        ["observadas", "Observados", "fa-triangle-exclamation", "is-danger", "observadas"],
        ["originales_recibidos", "Originales recibidos", "fa-circle-check", "", "recibidos"]
    ];
    contenedor.innerHTML = items.map(function (item) {
        var activa = String(centroFacturasEstado.filtroRapido || "") === item[4];
        return '<button type="button" class="centro-facturas-metric ' + item[3] + (activa ? ' is-active' : '') + '" onclick="centroFacturasAplicarFiltroRapido(\'' + item[4] + '\')">'
            + '<i class="fa-solid ' + item[2] + '" aria-hidden="true"></i><span><b>' + Number(metricas[item[0]] || 0) + '</b><span>' + item[1] + "</span></span></button>";
    }).join("");
}

function centroFacturasCambiarTab(tab) {
    if (["entrantes", "emitidas", "lotes"].indexOf(tab) < 0) { return; }
    centroFacturasEstado.tab = tab;
    centroFacturasEstado.offset = 0;
    centroFacturasEstado.filtroRapido = "";
    centroFacturasLimpiarSeleccion();
    Array.prototype.forEach.call(document.querySelectorAll("[data-cf-tab]"), function (boton) {
        boton.classList.toggle("is-active", boton.getAttribute("data-cf-tab") === tab);
    });
    var esEntrante = tab === "entrantes";
    ["centroFacturasFiltroPago", "centroFacturasFiltroOriginal"].forEach(function (id) {
        var campo = document.getElementById(id); if (campo) { campo.style.display = esEntrante ? "" : "none"; }
    });
    var manual = document.getElementById("btnCentroFacturasManual");
    if (manual) { manual.hidden = !esEntrante || !centroFacturasPermiso("REGISTRARFACTURAMANUAL"); }
    centroFacturasPintarMetricas({}, tab);
    centroFacturasBuscar();
}

function centroFacturasAplicarFiltroRapido(filtro) {
    centroFacturasEstado.filtroRapido = centroFacturasEstado.filtroRapido === filtro ? "" : filtro;
    centroFacturasBuscar();
}

function centroFacturasFiltros() {
    function valor(id) { var e = document.getElementById(id); return e ? e.value : ""; }
    return {
        busqueda: valor("centroFacturasBusqueda"), cod_local: valor("centroFacturasFiltroLocal"),
        estado_pago: valor("centroFacturasFiltroPago"), estado_original: valor("centroFacturasFiltroOriginal"),
        estado_validacion: valor("centroFacturasFiltroValidacion"), fecha_desde: valor("centroFacturasFechaDesde"),
        fecha_hasta: valor("centroFacturasFechaHasta"), filtro_rapido: centroFacturasEstado.filtroRapido,
        cod_proveedor: valor("centroFacturasFiltroProveedor"), cod_funcionario: valor("centroFacturasFiltroFuncionario"),
        cod_responsable: valor("centroFacturasFiltroResponsable"), cod_hilo: valor("centroFacturasFiltroHilo"),
        importe_desde: valor("centroFacturasImporteDesde"), importe_hasta: valor("centroFacturasImporteHasta"),
        incluir_anuladas: document.getElementById("centroFacturasIncluirAnuladas") && document.getElementById("centroFacturasIncluirAnuladas").checked ? 1 : 0,
        estado: centroFacturasEstado.tab === "lotes" ? valor("centroFacturasFiltroValidacion") : ""
    };
}

function centroFacturasBuscar() {
    centroFacturasEstado.offset = 0;
    centroFacturasCargarListado();
}

function centroFacturasRecargar() {
    centroFacturasCargarListado();
    centroFacturasActualizarBadge();
}

function centroFacturasCargarListado() {
    if (!centroFacturasEstado.contexto) { return; }
    var accion = centroFacturasEstado.tab === "emitidas" ? "listarEmitidas" : (centroFacturasEstado.tab === "lotes" ? "listarLotes" : "listarEntrantes");
    centroFacturasSolicitar(accion, {
        filtros: centroFacturasFiltros(), limite: centroFacturasEstado.limite, offset: centroFacturasEstado.offset
    }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) {
            centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo cargar el listado.", "error");
            centroFacturasRenderVacio("No se pudo consultar la informacion.");
            return;
        }
        centroFacturasEstado.total = Number(respuesta.total || 0);
        centroFacturasPintarMetricas(respuesta.metricas || {}, centroFacturasEstado.tab);
        if (centroFacturasEstado.tab === "entrantes") { centroFacturasRenderEntrantes(respuesta.registros || []); }
        else if (centroFacturasEstado.tab === "emitidas") { centroFacturasRenderEmitidas(respuesta.registros || []); }
        else { centroFacturasRenderLotes(respuesta.registros || []); }
        centroFacturasRenderPaginacion();
    });
}

function centroFacturasRenderVacio(mensaje) {
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    if (cuerpo) { cuerpo.innerHTML = ""; }
    if (vacio) { vacio.innerHTML = '<div><i class="fa-regular fa-folder-open fa-2x" aria-hidden="true"></i><p>' + centroFacturasEscapar(mensaje) + "</p></div>"; vacio.hidden = false; }
}

function centroFacturasBadge(texto, tipo) {
    return '<span class="cf-badge' + (tipo ? " cf-badge--" + tipo : "") + '">' + centroFacturasEscapar(texto || "Sin estado") + "</span>";
}

function centroFacturasTipoEstadoPago(estado) {
    if (estado === "Pagado") { return "success"; }
    if (estado === "Rechazado" || estado === "Anulado") { return "danger"; }
    if (estado === "En revision") { return "info"; }
    return "warning";
}

function centroFacturasRenderEntrantes(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    cabecera.innerHTML = "<tr><th></th><th>Gasto / origen</th><th>Contraparte</th><th>Respaldo</th><th>Importe</th><th>Estado documental</th><th>Original</th><th>Local</th><th>Acción</th></tr>";
    if (!registros.length) { centroFacturasRenderVacio("No hay gastos ni comprobantes recibidos con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (fila) {
        var id = Number(fila.id_factura || 0);
        var idGasto = Number(fila.id_gasto_esperado || fila.idgastosFK || 0);
        var idSugerida = Number(fila.id_factura_documento || 0);
        var visual = fila.estado_original_visual || {};
        var estadoDoc = fila.estado_documental || "sin_comprobante";
        var claseFila = estadoDoc === "consolidado" ? "cf-row--complete" : (estadoDoc === "por_vincular" ? "cf-row--pending" : (estadoDoc === "observado" ? "cf-row--observed" : "cf-row--missing"));
        var seleccionable = id && fila.tipo_documento !== "recibo" && centroFacturasPermiso("GESTIONARLOTESFACTURAS") && !fila.id_lote_actual
            && ["recibido", "no_requiere_original"].indexOf(fila.estado_original) < 0 && fila.estado_registro === "activo";
        var tipoRespaldo = fila.tipo_documento === "recibo" ? "Recibo" : (fila.tipo_documento === "factura" ? "Factura" : "Sin comprobante");
        var fiscal = fila.numero_factura ? '<strong>' + centroFacturasEscapar(fila.numero_factura) + '</strong><small>' + (fila.tipo_documento === "recibo" ? "Recibo" : "Timbrado " + centroFacturasEscapar(fila.timbrado || "—")) + " · " + centroFacturasFecha(fila.fecha_emision) + "</small>" : '<strong>' + centroFacturasEscapar(tipoRespaldo) + '</strong><small>' + (estadoDoc === "por_vincular" ? "Adjunto específico encontrado en el Hilo" : "Todavía no se adjuntó un respaldo") + '</small>';
        var estadoTexto = estadoDoc === "consolidado" ? "Respaldado" : (estadoDoc === "por_vincular" ? "Pendiente de vincular" : (estadoDoc === "observado" ? "Observado" : "Falta comprobante"));
        var estadoTipo = estadoDoc === "consolidado" ? "success" : (estadoDoc === "observado" ? "danger" : "warning");
        var origen = idGasto ? '<strong>Gasto #' + idGasto + " · " + centroFacturasFecha(fila.fecha_origen || fila.gasto_fecha) + '</strong>' : '<strong>Comprobante #' + id + " · " + centroFacturasFecha(fila.fecha_registro_digital, true) + '</strong>';
        var origenDetalle = fila.cod_interConsultaFK ? "Hilo #" + Number(fila.cod_interConsultaFK) : (idGasto ? "Sin Hilo vinculado" : "Carga manual");
        var accion = id ? '<button type="button" onclick="centroFacturasAbrirDetalle(' + id + ')">Ver detalle</button>'
            : (idSugerida ? '<button type="button" onclick="centroFacturasAbrirDetalle(' + idSugerida + ')">Revisar adjunto</button>'
                : (fila.cod_interConsultaFK ? '<button type="button" onclick="centroFacturasAbrirHilo(' + Number(fila.cod_interConsultaFK) + ')">Abrir Hilo</button>' : '<button type="button" onclick="centroFacturasAbrirMovimiento(\'gasto\',' + idGasto + ')">Ver gasto</button>'));
        return '<tr data-cf-id="' + id + '" class="' + claseFila + (centroFacturasEstado.seleccion[id] ? " is-selected" : "") + '"><td>'
            + (seleccionable ? '<input type="checkbox" aria-label="Seleccionar factura" ' + (centroFacturasEstado.seleccion[id] ? "checked" : "") + ' onchange="centroFacturasSeleccionar(' + id + ',' + Number(fila.cod_localFK) + ',this.checked)">' : "")
            + '</td><td>' + origen + '<small>' + centroFacturasEscapar(origenDetalle) + "</small></td>"
            + '<td><strong>' + centroFacturasEscapar(fila.contraparte_mostrar) + '</strong><small>' + centroFacturasEscapar(fila.documento_contraparte || fila.gasto_motivo || "") + "</small></td>"
            + "<td>" + fiscal + '</td><td><strong>Gs. ' + centroFacturasNumero(fila.importe_total || fila.importe_esperado) + '</strong><small>' + centroFacturasEscapar(fila.concepto || fila.gasto_motivo || "Sin concepto") + "</small></td>"
            + '<td>' + centroFacturasBadge(estadoTexto, estadoTipo) + (fila.estado_pago ? '<small>Movimiento: ' + centroFacturasEscapar(fila.estado_pago) + '</small>' : '') + "</td>"
            + '<td>' + (id ? centroFacturasBadge(visual.texto, visual.clase === "danger" ? "danger" : (visual.clase === "success" ? "success" : (visual.clase === "info" ? "info" : "warning"))) + '<small>Límite ' + centroFacturasFecha(fila.fecha_limite_original) + "</small>" : centroFacturasBadge("No gestionado", "warning")) + "</td>"
            + '<td>' + centroFacturasEscapar(fila.nombre_local) + '</td><td>' + accion + '</td></tr>';
    }).join("");
    centroFacturasActualizarSeleccion();
}

function centroFacturasRenderEmitidas(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    cabecera.innerHTML = "<tr><th>Fecha</th><th>Origen del cobro</th><th>Titular</th><th>Importe cobrado</th><th>Factura</th><th>Estado documental</th><th>Local</th><th>Responsable</th><th>Acción</th></tr>";
    if (!registros.length) { centroFacturasRenderVacio("No hay ventas al contado ni cuotas cobradas con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (fila) {
        var estado = fila.estado_documental || "sin_factura";
        var clase = estado === "facturada" ? "cf-row--complete" : (estado === "observada" ? "cf-row--observed" : "cf-row--missing");
        var origen = fila.tipo_evento === "cuota" ? "Cuota cobrada" : "Venta al contado";
        var referencia = fila.tipo_evento === "cuota" ? (fila.recibo_interno ? "Recibo interno " + fila.recibo_interno : "Pago #" + fila.id_pago) : "Venta #" + fila.cod_venta;
        var factura = fila.numero_factura ? '<strong>' + centroFacturasEscapar(fila.numero_factura) + '</strong><small>' + (fila.fecha_facturado ? "Emitida el " + centroFacturasFecha(fila.fecha_facturado) : "Factura de la venta") + '</small>' : '<strong>Sin factura</strong><small>Pendiente de emitir</small>';
        var estadoTexto = estado === "facturada" ? "Facturada" : (estado === "observada" ? "Revisar numeración" : "Falta factura");
        return '<tr class="' + clase + '"><td>' + centroFacturasFecha(fila.fecha_evento) + '</td><td><strong>' + centroFacturasEscapar(origen) + '</strong><small>' + centroFacturasEscapar(referencia) + "</small></td>"
            + '<td><strong>' + centroFacturasEscapar(fila.titular) + '</strong><small>' + centroFacturasEscapar(fila.documento || "") + "</small></td>"
            + '<td><strong>Gs. ' + centroFacturasNumero(fila.importe_evento) + '</strong></td><td>' + factura + '</td><td>' + centroFacturasBadge(estadoTexto, estado === "facturada" ? "success" : (estado === "observada" ? "danger" : "warning")) + "</td>"
            + '<td>' + centroFacturasEscapar(fila.nombre_local) + '</td><td>' + centroFacturasEscapar(fila.usuario_responsable || "—") + '</td><td><button type="button" onclick="centroFacturasAbrirVenta(' + Number(fila.cod_venta) + ')">Ver venta</button></td></tr>';
    }).join("");
}

function centroFacturasRenderLotes(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    cabecera.innerHTML = "<tr><th>Lote</th><th>Creacion</th><th>Local</th><th>Facturas</th><th>Importe</th><th>Estado</th><th>Responsable</th><th>Accion</th></tr>";
    if (!registros.length) { centroFacturasRenderVacio("Todavia no hay lotes con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (lote) {
        var tipo = lote.estado === "recibido" ? "success" : (lote.estado === "anulado" || lote.estado === "observado" ? "danger" : (lote.estado === "borrador" ? "warning" : "info"));
        return '<tr><td><strong>' + centroFacturasEscapar(lote.codigo_lote) + '</strong><small>' + centroFacturasEscapar(lote.destino) + '</small></td><td>' + centroFacturasFecha(lote.fecha_creacion, true) + '</td><td>' + centroFacturasEscapar(lote.nombre_local) + '</td>'
            + '<td><strong>' + Number(lote.cantidad_facturas || 0) + '</strong><small>' + Number(lote.cantidad_recibidas || 0) + ' recibidas · ' + Number(lote.cantidad_observadas || 0) + ' observadas</small></td><td>Gs. ' + centroFacturasNumero(lote.importe_total) + '</td><td>' + centroFacturasBadge(String(lote.estado).replace(/_/g, " "), tipo) + '</td><td>' + centroFacturasEscapar(lote.usuario_entrega || lote.usuario_creador || "—") + '</td><td><button type="button" onclick="centroFacturasAbrirLote(' + Number(lote.id_lote) + ')">Ver lote</button></td></tr>';
    }).join("");
}

function centroFacturasRenderPaginacion() {
    var pie = document.getElementById("centroFacturasPaginacion");
    var inicio = centroFacturasEstado.total ? centroFacturasEstado.offset + 1 : 0;
    var fin = Math.min(centroFacturasEstado.offset + centroFacturasEstado.limite, centroFacturasEstado.total);
    pie.innerHTML = '<span>' + inicio + "–" + fin + " de " + centroFacturasEstado.total + '</span><button type="button" class="cf-button cf-button--secondary" ' + (centroFacturasEstado.offset <= 0 ? "disabled" : "") + ' onclick="centroFacturasPagina(-1)">Anterior</button><button type="button" class="cf-button cf-button--secondary" ' + (fin >= centroFacturasEstado.total ? "disabled" : "") + ' onclick="centroFacturasPagina(1)">Siguiente</button>';
}

function centroFacturasPagina(sentido) {
    centroFacturasEstado.offset = Math.max(0, centroFacturasEstado.offset + (sentido * centroFacturasEstado.limite));
    centroFacturasCargarListado();
}

function centroFacturasAlternarFiltros() {
    var panel = document.getElementById("centroFacturasFiltrosExtra"); if (panel) { panel.hidden = !panel.hidden; }
}

function centroFacturasLimpiarFiltros() {
    ["centroFacturasBusqueda", "centroFacturasFiltroPago", "centroFacturasFiltroOriginal", "centroFacturasFiltroValidacion", "centroFacturasFiltroProveedor", "centroFacturasFiltroFuncionario", "centroFacturasFiltroResponsable", "centroFacturasFiltroHilo", "centroFacturasImporteDesde", "centroFacturasImporteHasta"].forEach(function (id) {
        var campo = document.getElementById(id); if (campo) { campo.value = ""; }
    });
    var anuladas = document.getElementById("centroFacturasIncluirAnuladas"); if (anuladas) { anuladas.checked = false; }
    centroFacturasEstado.filtroRapido = "";
    centroFacturasAplicarPeriodoMes(centroFacturasMesActual(), true);
}

function centroFacturasSeleccionar(idFactura, codLocal, seleccionado) {
    if (seleccionado) {
        var existentes = Object.keys(centroFacturasEstado.seleccion);
        if (existentes.length && Number(centroFacturasEstado.seleccion[existentes[0]]) !== Number(codLocal)) {
            centroFacturasAviso("Un lote solo puede incluir facturas del mismo local.", "error");
            var checkbox = document.querySelector('tr[data-cf-id="' + idFactura + '"] input[type="checkbox"]'); if (checkbox) { checkbox.checked = false; }
            return;
        }
        centroFacturasEstado.seleccion[idFactura] = codLocal;
    } else { delete centroFacturasEstado.seleccion[idFactura]; }
    var fila = document.querySelector('tr[data-cf-id="' + idFactura + '"]'); if (fila) { fila.classList.toggle("is-selected", seleccionado); }
    centroFacturasActualizarSeleccion();
}

function centroFacturasActualizarSeleccion() {
    var cantidad = Object.keys(centroFacturasEstado.seleccion).length;
    var barra = document.getElementById("centroFacturasSeleccion");
    if (barra) { barra.hidden = cantidad < 1; }
    var texto = document.getElementById("centroFacturasSeleccionCantidad"); if (texto) { texto.textContent = cantidad; }
}

function centroFacturasLimpiarSeleccion() {
    centroFacturasEstado.seleccion = {};
    centroFacturasActualizarSeleccion();
    Array.prototype.forEach.call(document.querySelectorAll('#centroFacturasTablaCuerpo input[type="checkbox"]'), function (c) { c.checked = false; });
    Array.prototype.forEach.call(document.querySelectorAll("#centroFacturasTablaCuerpo tr"), function (f) { f.classList.remove("is-selected"); });
}

function centroFacturasOpciones(lista, valor, texto, seleccionado) {
    return (lista || []).map(function (item) {
        var id = item[valor];
        return '<option value="' + centroFacturasEscapar(id) + '" ' + (String(id) === String(seleccionado || "") ? "selected" : "") + '>' + centroFacturasEscapar(item[texto]) + "</option>";
    }).join("");
}

function centroFacturasAbrirDialogo(titulo, etiqueta, contenido, acciones) {
    document.getElementById("centroFacturasDialogoTitulo").textContent = titulo;
    document.getElementById("centroFacturasDialogoEtiqueta").textContent = etiqueta || "Proceso guiado";
    document.getElementById("centroFacturasDialogoContenido").innerHTML = contenido;
    document.getElementById("centroFacturasDialogoAcciones").innerHTML = acciones || '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cerrar</button>';
    document.getElementById("centroFacturasDialogo").hidden = false;
}

function centroFacturasCerrarDialogo() {
    var dialogo = document.getElementById("centroFacturasDialogo"); if (dialogo) { dialogo.hidden = true; }
}

function centroFacturasAbrirRegistroManual() {
    var c = centroFacturasEstado.contexto || {};
    var localUsuario = c.usuario ? c.usuario.cod_localFK : "";
    var hoy = new Date();
    var fecha = hoy.getFullYear() + "-" + String(hoy.getMonth() + 1).padStart(2, "0") + "-" + String(hoy.getDate()).padStart(2, "0");
    var html = '<p class="cf-step-help"><b>1.</b> Identifique el tipo de respaldo. <b>2.</b> Adjunte el documento. <b>3.</b> Revise el resumen y registre.</p>'
        + '<form id="centroFacturasFormManual" class="cf-grid" onsubmit="event.preventDefault();centroFacturasGuardarManual();">'
        + '<label>Local<select id="cfManualLocal"><option value="">Seleccione</option>' + centroFacturasOpciones(c.locales, "cod_local", "Nombre", localUsuario) + '</select></label>'
        + '<label>Tipo de respaldo<select id="cfManualTipoDocumento" onchange="centroFacturasActualizarDocumentoManual()"><option value="factura">Factura recibida</option><option value="recibo">Recibo recibido</option></select></label>'
        + '<label>Tipo de contraparte<select id="cfManualTipo" onchange="centroFacturasActualizarTipoManual()"><option value="proveedor">Proveedor</option><option value="funcionario">Funcionario</option><option value="otro">Otro</option></select></label>'
        + '<label id="cfManualGrupoProveedor">Proveedor<select id="cfManualProveedor"><option value="">Seleccione</option>' + centroFacturasOpciones(c.proveedores, "cod_proveedor", "nombre_persona", "") + '</select></label>'
        + '<label id="cfManualGrupoFuncionario" hidden>Funcionario<select id="cfManualFuncionario"><option value="">Seleccione</option>' + centroFacturasOpciones(c.funcionarios, "cod_usuario", "nombre_persona", "") + '</select></label>'
        + '<label id="cfManualGrupoNombre" hidden>Nombre o raz&oacute;n social<input id="cfManualNombre" maxlength="255"></label>'
        + '<label id="cfManualGrupoDocumento" hidden>RUC o documento<input id="cfManualDocumento" maxlength="45"></label>'
        + '<label><span id="cfManualNumeroEtiqueta">N.&ordm; de factura</span><input id="cfManualNumero" maxlength="80" placeholder="Ej. 001-001-0001234"></label><label id="cfManualTimbradoGrupo">Timbrado<input id="cfManualTimbrado" maxlength="45"></label>'
        + '<label>Fecha de emisi&oacute;n<input id="cfManualFecha" type="date" value="' + fecha + '"></label><label>Importe total (Gs.)<input id="cfManualImporte" inputmode="decimal" placeholder="0"></label>'
        + '<label class="cf-span-2">Concepto<input id="cfManualConcepto" maxlength="255" placeholder="Describa brevemente el gasto"></label>'
        + '<label class="cf-span-2">Observaciones<textarea id="cfManualObservaciones" rows="2" maxlength="3000"></textarea></label>'
        + '<label class="cf-span-2"><span id="cfManualArchivoEtiqueta">Archivo de factura (PDF o imagen; hasta 10 p&aacute;ginas)</span><input id="cfManualArchivos" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif" multiple required></label>'
        + '<input type="hidden" id="cfManualConfirmarDuplicado" value="0"><div id="cfManualDuplicado" class="cf-span-2" hidden></div><div id="cfManualError" class="cf-form-error cf-span-2"></div></form>';
    centroFacturasAbrirDialogo("Registrar comprobante recibido", "Carga manual", html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasGuardarManual()">Registrar comprobante</button>');
}

function centroFacturasActualizarDocumentoManual() {
    var esRecibo = document.getElementById("cfManualTipoDocumento").value === "recibo";
    document.getElementById("cfManualNumeroEtiqueta").textContent = esRecibo ? "N.º de recibo" : "N.º de factura";
    document.getElementById("cfManualTimbradoGrupo").hidden = esRecibo;
    document.getElementById("cfManualArchivoEtiqueta").textContent = "Archivo de " + (esRecibo ? "recibo" : "factura") + " (PDF o imagen; hasta 10 páginas)";
}

function centroFacturasActualizarTipoManual() {
    var tipo = document.getElementById("cfManualTipo").value;
    document.getElementById("cfManualGrupoProveedor").hidden = tipo !== "proveedor";
    document.getElementById("cfManualGrupoFuncionario").hidden = tipo !== "funcionario";
    document.getElementById("cfManualGrupoNombre").hidden = tipo !== "otro";
    document.getElementById("cfManualGrupoDocumento").hidden = tipo !== "otro";
}

function centroFacturasDatosManual() {
    function v(id) { var e = document.getElementById(id); return e ? e.value.trim() : ""; }
    return { cod_local: v("cfManualLocal"), tipo_documento: v("cfManualTipoDocumento"), tipo_contraparte: v("cfManualTipo"), cod_proveedor: v("cfManualProveedor"), cod_funcionario: v("cfManualFuncionario"), nombre_contraparte: v("cfManualNombre"), documento_contraparte: v("cfManualDocumento"), numero_factura: v("cfManualNumero"), timbrado: v("cfManualTimbrado"), fecha_emision: v("cfManualFecha"), importe_total: v("cfManualImporte"), moneda: "PYG", concepto: v("cfManualConcepto"), observaciones: v("cfManualObservaciones"), confirmar_duplicado: v("cfManualConfirmarDuplicado"), motivo_duplicado: v("cfManualMotivoDuplicado") };
}

function centroFacturasGuardarManual() {
    var input = document.getElementById("cfManualArchivos");
    var archivos = input ? Array.prototype.slice.call(input.files || []) : [];
    var error = document.getElementById("cfManualError"); if (error) { error.textContent = ""; }
    centroFacturasSolicitar("registrarManual", { datos: centroFacturasDatosManual() }, { archivos: archivos }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) {
            if (respuesta && respuesta.codigo === "posible_duplicado") {
                var zona = document.getElementById("cfManualDuplicado");
                zona.hidden = false;
                zona.innerHTML = '<div class="cf-warning-box"><b>Posible duplicado.</b> Ya existe un comprobante del mismo tipo con la misma contraparte, número, fecha e importe.</div>'
                    + (centroFacturasPermiso("ADMINCENTROFACTURAS") ? '<label>Motivo para continuar<input id="cfManualMotivoDuplicado" maxlength="255" placeholder="Explique por que no es un duplicado"></label>' : '<p>Solicite la revision de un administrador.</p>');
                document.getElementById("cfManualConfirmarDuplicado").value = centroFacturasPermiso("ADMINCENTROFACTURAS") ? "1" : "0";
            }
            if (error) { error.textContent = (respuesta && respuesta.mensaje) || "Revise los datos ingresados."; }
            return;
        }
        centroFacturasCerrarDialogo();
        centroFacturasAviso("El comprobante fue registrado con trazabilidad y plazo de original.", "success");
        centroFacturasRecargar();
        centroFacturasAbrirDetalle(respuesta.id_factura);
    });
}

function centroFacturasCerrarDetalle() {
    var detalle = document.getElementById("centroFacturasDetalle"); if (detalle) { detalle.hidden = true; }
    centroFacturasEstado.detalleFactura = 0; centroFacturasEstado.detalleLote = 0;
}

function centroFacturasAbrirDetalle(idFactura) {
    idFactura = Number(idFactura || 0); if (!idFactura) { return; }
    centroFacturasCargarContexto(function (permitido) {
        if (!permitido) { return; }
        var ventana = document.getElementById("divCentroFacturas");
        if (ventana && ventana.style.display === "none") { ventana.style.display = "block"; ventana.setAttribute("aria-hidden", "false"); }
        centroFacturasSolicitar("detalle", { id_factura: idFactura }).done(function (respuesta) {
            if (!respuesta || !respuesta.ok) { centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo cargar la factura.", "error"); return; }
            centroFacturasEstado.detalleFactura = idFactura;
            centroFacturasRenderDetalle(respuesta);
            document.getElementById("centroFacturasDetalle").hidden = false;
        });
    }, false);
}

function centroFacturasRenderDetalle(datos) {
    var f = datos.factura;
    var c = centroFacturasEstado.contexto || {};
    var visual = f.estado_original_visual || {};
    var esRecibo = f.tipo_documento === "recibo";
    document.getElementById("centroFacturasDetalleTitulo").textContent = (esRecibo ? "Recibo #" : "Factura #") + f.id_factura;
    var html = '<div class="cf-detail-summary"><div><span>Pago</span>' + centroFacturasBadge(f.estado_pago, centroFacturasTipoEstadoPago(f.estado_pago)) + '</div><div><span>Original</span>' + centroFacturasBadge(visual.texto, visual.clase === "danger" ? "danger" : (visual.clase === "success" ? "success" : "warning")) + '</div><div><span>Validacion</span>' + centroFacturasBadge(String(f.estado_validacion).replace(/_/g, " "), f.estado_validacion === "validada" ? "success" : (f.estado_validacion === "rechazada" ? "danger" : "info")) + "</div></div>";
    if (f.posible_duplicado && !f.duplicado_confirmado) { html += '<div class="cf-warning-box"><b>Posible duplicado:</b> revise las coincidencias antes de validar.</div>'; }
    html += '<section class="cf-card"><h4>Datos del comprobante</h4><div class="cf-grid">'
        + '<label>Tipo de respaldo<select id="cfDetalleTipoDocumento" onchange="centroFacturasActualizarDocumentoDetalle()"><option value="factura" ' + (!esRecibo ? "selected" : "") + '>Factura</option><option value="recibo" ' + (esRecibo ? "selected" : "") + '>Recibo</option></select></label>'
        + '<label>Tipo<select id="cfDetalleTipo"><option value="proveedor" ' + (f.tipo_contraparte === "proveedor" ? "selected" : "") + '>Proveedor</option><option value="funcionario" ' + (f.tipo_contraparte === "funcionario" ? "selected" : "") + '>Funcionario</option><option value="otro" ' + (f.tipo_contraparte === "otro" ? "selected" : "") + '>Otro</option></select></label>'
        + '<label>Proveedor<select id="cfDetalleProveedor"><option value="">No aplica</option>' + centroFacturasOpciones(c.proveedores, "cod_proveedor", "nombre_persona", f.cod_proveedorFK) + '</select></label>'
        + '<label>Funcionario<select id="cfDetalleFuncionario"><option value="">No aplica</option>' + centroFacturasOpciones(c.funcionarios, "cod_usuario", "nombre_persona", f.cod_funcionarioFK) + '</select></label>'
        + '<label>Nombre / razon social<input id="cfDetalleNombre" value="' + centroFacturasEscapar(f.nombre_contraparte) + '"></label><label>RUC / documento<input id="cfDetalleDocumento" value="' + centroFacturasEscapar(f.documento_contraparte) + '"></label>'
        + '<label><span id="cfDetalleNumeroEtiqueta">N.&ordm; ' + (esRecibo ? "recibo" : "factura") + '</span><input id="cfDetalleNumero" value="' + centroFacturasEscapar(f.numero_factura) + '"></label><label id="cfDetalleTimbradoGrupo" ' + (esRecibo ? "hidden" : "") + '>Timbrado<input id="cfDetalleTimbrado" value="' + centroFacturasEscapar(f.timbrado) + '"></label>'
        + '<label>Fecha emision<input id="cfDetalleFecha" type="date" value="' + centroFacturasEscapar(f.fecha_emision) + '"></label><label>Importe<input id="cfDetalleImporte" value="' + centroFacturasEscapar(f.importe_total) + '"></label>'
        + '<label class="cf-span-2">Concepto<input id="cfDetalleConcepto" value="' + centroFacturasEscapar(f.concepto) + '"></label><label class="cf-span-2">Observaciones<textarea id="cfDetalleObservaciones">' + centroFacturasEscapar(f.observaciones) + '</textarea></label>'
        + '<label>Fecha limite original<input id="cfDetalleLimite" type="date" value="' + centroFacturasEscapar(f.fecha_limite_original) + '" ' + (!centroFacturasPermiso("ADMINCENTROFACTURAS") ? "disabled" : "") + '></label><label>Responsable<select id="cfDetalleResponsable"><option value="">Usuario actual</option>' + centroFacturasOpciones(c.funcionarios, "cod_usuario", "nombre_persona", f.cod_responsable_envioFK) + '</select></label></div>'
        + (datos.puede_editar ? '<div class="cf-card-actions"><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasGuardarDetalle(' + Number(f.version_registro) + ')">Guardar correcciones</button></div>' : '<p>Los datos fiscales ya no pueden editarse con su permiso actual.</p>') + '</section>';
    html += centroFacturasRenderArchivos(datos.archivos || [], f.id_factura);
    html += centroFacturasRenderPagoDetalle(f, datos.candidatos_financieros || {});
    html += centroFacturasRenderOriginalDetalle(f);
    if (f.cod_interConsultaFK) { html += '<section class="cf-card"><h4>Hilo de origen</h4><p>' + centroFacturasEscapar(f.hilo_asunto || "Hilo #" + f.cod_interConsultaFK) + '</p><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirHilo(' + Number(f.cod_interConsultaFK) + ')">Abrir Hilo</button></section>'; }
    if ((datos.lotes || []).length) { html += '<section class="cf-card"><h4>Lotes</h4><ul class="cf-timeline">' + datos.lotes.map(function (l) { return '<li><span><b>' + centroFacturasEscapar(l.codigo_lote) + '</b><br>' + centroFacturasEscapar(l.detalle_estado) + '</span><button type="button" onclick="centroFacturasAbrirLote(' + Number(l.id_lote) + ')">Ver</button></li>'; }).join("") + '</ul></section>'; }
    if ((datos.duplicados || []).length) { html += '<section class="cf-card"><h4>Coincidencias del comprobante</h4><ul class="cf-timeline">' + datos.duplicados.map(function (d) { return '<li><span>Comprobante #' + Number(d.id_factura) + ' · ' + centroFacturasEscapar(d.nombre_local) + '</span><button type="button" onclick="centroFacturasAbrirDetalle(' + Number(d.id_factura) + ')">Comparar</button></li>'; }).join("") + '</ul></section>'; }
    if (centroFacturasPermiso("ADMINCENTROFACTURAS")) { html += centroFacturasRenderAuditoria(datos.auditoria || []); }
    document.getElementById("centroFacturasDetalleContenido").innerHTML = html;
}

function centroFacturasActualizarDocumentoDetalle() {
    var esRecibo = document.getElementById("cfDetalleTipoDocumento").value === "recibo";
    document.getElementById("cfDetalleNumeroEtiqueta").textContent = esRecibo ? "N.º recibo" : "N.º factura";
    document.getElementById("cfDetalleTimbradoGrupo").hidden = esRecibo;
}

function centroFacturasRenderArchivos(archivos, idFactura) {
    var lista = archivos.map(function (a, indice) {
        return '<li><span><i class="fa-regular fa-file" aria-hidden="true"></i> Pagina ' + (indice + 1) + ' · ' + centroFacturasEscapar(a.nombre_original || a.extension || "Adjunto") + '</span><a href="' + centroFacturasEscapar(a.url_disponible) + '" target="_blank" rel="noopener">Ver archivo</a></li>';
    }).join("");
    return '<section class="cf-card"><h4>Archivo digital</h4>' + (lista ? '<ul class="cf-file-list">' + lista + '</ul>' : '<p>No hay archivos disponibles.</p>')
        + (centroFacturasPermiso("REGISTRARFACTURAMANUAL") || centroFacturasPermiso("ADMINCENTROFACTURAS") ? '<div class="cf-card-actions"><input type="file" id="cfDetalleNuevosArchivos" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif" multiple><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAgregarArchivos(' + Number(idFactura) + ')">Agregar paginas</button></div>' : "") + '</section>';
}

function centroFacturasRenderPagoDetalle(f, candidatos) {
    var html = '<section class="cf-card"><h4>Vinculo con pago</h4><p>Estado calculado: <b>' + centroFacturasEscapar(f.estado_pago) + '</b>';
    if (f.tipo_referencia_pago) { html += ' · ' + centroFacturasEscapar(f.tipo_referencia_pago) + ' #' + Number(f.idgastosFK || f.cod_compraFK); }
    html += "</p>";
    if (f.tipo_referencia_pago) {
        html += '<div class="cf-card-actions"><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasAbrirMovimiento(\'' + centroFacturasEscapar(f.tipo_referencia_pago) + '\',' + Number(f.idgastosFK || f.cod_compraFK) + ')">Ver movimiento original</button>';
        if (centroFacturasPermiso("ADMINCENTROFACTURAS")) { html += '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasSolicitarDesvinculo(' + Number(f.id_factura) + ')">Desvincular</button>'; }
        html += '</div>';
    }
    if (!f.tipo_referencia_pago && centroFacturasPermiso("VINCULARPAGOFACTURA")) {
        var movimientos = [];
        (candidatos.gastos || []).forEach(function (g) { movimientos.push({ tipo: "gasto", id: g.idgastos, monto: g.monto, texto: "Gasto #" + g.idgastos + " · " + (g.motivo || "Sin concepto") + " · " + g.estado, ocupado: g.vinculada_factura }); });
        (candidatos.compras || []).forEach(function (cp) { movimientos.push({ tipo: "compra", id: cp.cod_compra, monto: cp.monto, texto: "Compra #" + cp.cod_compra + " · " + (cp.num_comprobante || "Sin comprobante") + " · " + cp.estado, ocupado: cp.vinculada_factura }); });
        html += movimientos.length ? '<div>' + movimientos.map(function (m) { return '<div class="cf-candidate"><span><b>' + centroFacturasEscapar(m.texto) + '</b><br>Gs. ' + centroFacturasNumero(m.monto) + (m.ocupado ? ' · Vinculado a comprobante #' + Number(m.ocupado) : "") + '</span><button type="button" ' + (m.ocupado ? "disabled" : "") + ' onclick="centroFacturasVincular(' + Number(f.id_factura) + ',\'' + m.tipo + '\',' + Number(m.id) + ')">Vincular</button></div>'; }).join("") + '</div>' : '<p>No se encontraron candidatos del mismo Hilo o importe. El movimiento debe registrarse primero en el flujo financiero.</p>';
    }
    return html + "</section>";
}

function centroFacturasRenderOriginalDetalle(f) {
    var html = '<section class="cf-card"><h4>Original fisico</h4><p>Plazo: <b>' + centroFacturasFecha(f.fecha_limite_original) + '</b> · Estado: <b>' + centroFacturasEscapar((f.estado_original_visual || {}).texto) + '</b></p>';
    if (f.ubicacion_fisica) { html += '<p>Ubicacion: ' + centroFacturasEscapar(f.ubicacion_fisica) + '</p>'; }
    if (f.comentario_observacion) { html += '<div class="cf-warning-box">' + centroFacturasEscapar(f.motivo_observacion) + ': ' + centroFacturasEscapar(f.comentario_observacion) + '</div>'; }
    html += '<div class="cf-card-actions">';
    if (centroFacturasPermiso("ENVIARORIGINALFACTURA") && ["recibido", "no_requiere_original"].indexOf(f.estado_original) < 0) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasAbrirAccionOriginal(' + Number(f.id_factura) + ',\'enviar\')">Marcar enviado</button>'; }
    if (centroFacturasPermiso("RECIBIRORIGINALFACTURA")) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasAbrirAccionOriginal(' + Number(f.id_factura) + ',\'recibir\')">Recibir original</button><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirAccionOriginal(' + Number(f.id_factura) + ',\'observar\')">Observar</button>'; }
    if (centroFacturasPermiso("ADMINCENTROFACTURAS")) { html += '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirAccionOriginal(' + Number(f.id_factura) + ',\'no_requiere\')">No requiere</button><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirAccionOriginal(' + Number(f.id_factura) + ',\'revertir\')">Revertir</button>'; }
    html += "</div>";
    if (centroFacturasPermiso("ADMINCENTROFACTURAS")) { html += '<div class="cf-card-actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCambiarValidacionDialogo(' + Number(f.id_factura) + ')">Cambiar validacion</button></div>'; }
    return html + "</section>";
}

function centroFacturasRenderAuditoria(auditoria) {
    return '<section class="cf-card"><h4>Trazabilidad</h4>' + (auditoria.length ? '<ul class="cf-timeline">' + auditoria.map(function (a) { return '<li><span><b>' + centroFacturasEscapar(String(a.accion).replace(/_/g, " ")) + '</b><br>' + centroFacturasEscapar(a.usuario_nombre || "Usuario") + ' · ' + centroFacturasFecha(a.fecha_hora, true) + (a.motivo ? "<br>" + centroFacturasEscapar(a.motivo) : "") + '</span></li>'; }).join("") + '</ul>' : '<p>Sin movimientos auditados.</p>') + '</section>';
}

function centroFacturasDatosDetalle(version) {
    function v(id) { var e = document.getElementById(id); return e ? e.value.trim() : ""; }
    return { tipo_documento: v("cfDetalleTipoDocumento"), tipo_contraparte: v("cfDetalleTipo"), cod_proveedor: v("cfDetalleProveedor"), cod_funcionario: v("cfDetalleFuncionario"), nombre_contraparte: v("cfDetalleNombre"), documento_contraparte: v("cfDetalleDocumento"), numero_factura: v("cfDetalleNumero"), timbrado: v("cfDetalleTimbrado"), fecha_emision: v("cfDetalleFecha"), importe_total: v("cfDetalleImporte"), moneda: "PYG", concepto: v("cfDetalleConcepto"), observaciones: v("cfDetalleObservaciones"), fecha_limite_original: v("cfDetalleLimite"), cod_responsable: v("cfDetalleResponsable"), version_registro: version, enviar_revision: 1, motivo_cambio: "Correccion de datos desde el detalle" };
}

function centroFacturasGuardarDetalle(version) {
    var id = centroFacturasEstado.detalleFactura;
    centroFacturasSolicitar("guardarDatos", { id_factura: id, datos: centroFacturasDatosDetalle(version) }).done(function (r) {
        if (!r || !r.ok) { centroFacturasAviso((r && r.mensaje) || "No se pudieron guardar los datos.", "error"); return; }
        centroFacturasAviso("Los datos fueron actualizados y auditados.", "success"); centroFacturasAbrirDetalle(id); centroFacturasRecargar();
    });
}

function centroFacturasAgregarArchivos(id) {
    var input = document.getElementById("cfDetalleNuevosArchivos");
    var archivos = input ? Array.prototype.slice.call(input.files || []) : [];
    if (!archivos.length) { centroFacturasAviso("Seleccione al menos un archivo.", "error"); return; }
    centroFacturasSolicitar("agregarArchivos", { id_factura: id, tipo_origen: "carga_manual" }, { archivos: archivos }).done(function (r) {
        if (!r || !r.ok) { centroFacturasAviso((r && r.mensaje) || "No se pudieron agregar los archivos.", "error"); return; }
        centroFacturasAviso("Las paginas fueron agregadas.", "success"); centroFacturasAbrirDetalle(id);
    });
}

function centroFacturasVincular(idFactura, tipo, idMovimiento) {
    centroFacturasSolicitar("vincularMovimiento", { id_factura: idFactura, tipo_movimiento: tipo, id_movimiento: idMovimiento, motivo: "Vinculo seleccionado desde Centro de Facturas" }).done(function (r) {
        if (!r || !r.ok) { centroFacturasAviso((r && r.mensaje) || "No se pudo vincular el movimiento.", "error"); return; }
        centroFacturasAviso("Movimiento vinculado; el estado de pago se calcula desde su fuente.", "success"); centroFacturasAbrirDetalle(idFactura); centroFacturasRecargar();
    });
}

function centroFacturasSolicitarDesvinculo(idFactura) {
    centroFacturasAbrirDialogo("Desvincular movimiento", "Accion administrativa", '<p class="cf-step-help">El pago no se elimina. Solo se retira el vinculo con esta factura y queda registro en la auditoria.</p><label class="cf-field-label">Motivo obligatorio</label><textarea id="cfMotivoDesvinculo" rows="3"></textarea><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--danger" onclick="centroFacturasDesvincular(' + idFactura + ')">Desvincular</button>');
}

function centroFacturasDesvincular(idFactura) {
    var motivo = document.getElementById("cfMotivoDesvinculo").value.trim();
    centroFacturasSolicitar("desvincularMovimiento", { id_factura: idFactura, motivo: motivo }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo desvincular."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Vinculo retirado con trazabilidad.", "success"); centroFacturasAbrirDetalle(idFactura); centroFacturasRecargar();
    });
}

function centroFacturasAbrirAccionOriginal(idFactura, accion) {
    var titulo = { enviar: "Enviar original a central", recibir: "Recibir y archivar original", observar: "Observar original", no_requiere: "Marcar que no requiere original", revertir: "Revertir estado del original" }[accion];
    var html = '<p class="cf-step-help">La accion quedara registrada con usuario, fecha y motivo cuando corresponda.</p><input type="hidden" id="cfAccionOriginal" value="' + accion + '">';
    if (accion === "enviar") { html += '<label class="cf-field-label">Responsable de la entrega</label><select id="cfOriginalResponsable"><option value="">Usuario actual</option>' + centroFacturasOpciones((centroFacturasEstado.contexto || {}).funcionarios, "cod_usuario", "nombre_persona", "") + '</select>'; }
    if (accion === "recibir") { html += '<div class="cf-grid"><label>Lote / archivador<input id="cfOriginalLote"></label><label>Carpeta<input id="cfOriginalCarpeta"></label><label>Caja<input id="cfOriginalCaja"></label><label>Periodo<input id="cfOriginalPeriodo" placeholder="Ej. 2026-07"></label><label class="cf-span-2">Ubicacion fisica<input id="cfOriginalUbicacion" placeholder="Describa donde queda archivada"></label></div>'; }
    if (accion === "observar") { html += '<div class="cf-grid"><label>Motivo<select id="cfOriginalMotivoTipo"><option value="importe_diferente">Importe diferente</option><option value="numero_diferente">Numero diferente</option><option value="factura_ilegible">Factura ilegible</option><option value="documento_danado">Documento danado</option><option value="documento_incompleto">Documento incompleto</option><option value="falta_firma">Falta firma</option><option value="factura_equivocada">Factura equivocada</option><option value="duplicada">Duplicada</option><option value="otro">Otro</option></select></label><label>Responsable<select id="cfOriginalResponsable"><option value="">Responsable actual</option>' + centroFacturasOpciones((centroFacturasEstado.contexto || {}).funcionarios, "cod_usuario", "nombre_persona", "") + '</select></label><label class="cf-span-2">Descripcion<textarea id="cfOriginalComentario" rows="3"></textarea></label><label class="cf-span-2">Evidencia opcional<input id="cfOriginalEvidencia" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif"></label></div>'; }
    if (accion === "no_requiere" || accion === "revertir") { html += '<label class="cf-field-label">Motivo obligatorio</label><textarea id="cfOriginalMotivo" rows="3"></textarea>'; }
    html += '<div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo(titulo, "Original fisico", html, '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasConfirmarOriginal(' + Number(idFactura) + ')">Confirmar</button>');
}

function centroFacturasConfirmarOriginal(idFactura) {
    function v(id) { var e = document.getElementById(id); return e ? e.value.trim() : ""; }
    var accion = v("cfAccionOriginal");
    var datos = { cod_responsable: v("cfOriginalResponsable"), motivo: v("cfOriginalMotivo"), lote_archivo: v("cfOriginalLote"), carpeta_archivo: v("cfOriginalCarpeta"), caja_archivo: v("cfOriginalCaja"), periodo_archivo: v("cfOriginalPeriodo"), ubicacion_fisica: v("cfOriginalUbicacion"), motivo_observacion: v("cfOriginalMotivoTipo"), comentario_observacion: v("cfOriginalComentario") };
    centroFacturasSolicitar("cambiarOriginal", { id_factura: idFactura, accion_original: accion, datos: datos }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo cambiar el estado."; return; }
        var evidencia = document.getElementById("cfOriginalEvidencia");
        if (accion === "observar" && evidencia && evidencia.files && evidencia.files.length) {
            centroFacturasSolicitar("agregarArchivos", { id_factura: idFactura, tipo_origen: "evidencia_observacion" }, { archivos: [evidencia.files[0]] }).always(function () { centroFacturasCerrarDialogo(); centroFacturasAbrirDetalle(idFactura); centroFacturasRecargar(); });
        } else { centroFacturasCerrarDialogo(); centroFacturasAbrirDetalle(idFactura); centroFacturasRecargar(); }
        centroFacturasAviso("Estado del original actualizado con trazabilidad.", "success");
    });
}

function centroFacturasCambiarValidacionDialogo(idFactura) {
    centroFacturasAbrirDialogo("Cambiar validacion", "Control administrativo", '<p class="cf-step-help">Este estado valida la consistencia de la factura; no cambia el estado del pago ni del original.</p><label class="cf-field-label">Estado</label><select id="cfValidacionEstado"><option value="pendiente">Pendiente</option><option value="en_revision">En revision</option><option value="validada">Validada</option><option value="rechazada">Rechazada</option><option value="anulada">Anulada</option></select><label class="cf-field-label">Motivo (obligatorio para rechazo o anulacion)</label><textarea id="cfValidacionMotivo" rows="3"></textarea><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasConfirmarValidacion(' + idFactura + ')">Guardar estado</button>');
}

function centroFacturasConfirmarValidacion(idFactura) {
    centroFacturasSolicitar("cambiarValidacion", { id_factura: idFactura, estado_validacion: document.getElementById("cfValidacionEstado").value, motivo: document.getElementById("cfValidacionMotivo").value.trim() }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo cambiar la validacion."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Validacion actualizada.", "success"); centroFacturasAbrirDetalle(idFactura); centroFacturasRecargar();
    });
}

function centroFacturasRegistrarAdjuntoHilo(codMensaje) {
    centroFacturasCargarContexto(function (permitido) {
        if (!permitido) { return; }
        centroFacturasSolicitar("registrarDesdeMensaje", { cod_mensaje: codMensaje }).done(function (r) {
            if (!r || !r.ok) { if (typeof ver_vetana_informativa === "function") { ver_vetana_informativa("No se pudo registrar el comprobante", (r && r.mensaje) || "Revise sus permisos.", "advertencia"); } return; }
            var nombre = r.tipo_documento === "recibo" ? "Recibo" : "Factura";
            if (typeof ver_vetana_informativa === "function") { ver_vetana_informativa(nombre + " registrado", "Complete los datos desde el Centro de Facturas.", "info"); }
            centroFacturasActualizarBadge(); centroFacturasAbrirDetalle(r.id_factura);
        });
    }, false);
}

function centroFacturasAbrirHilo(idHilo) {
    verCerrarCentroFacturas(false);
    if (typeof seleccionarCategoriaHilosInterConsulta === "function") { seleccionarCategoriaHilosInterConsulta("pagos_egresos", false); }
    if (typeof verCerrarVentanaListadoInterConsulta === "function") { verCerrarVentanaListadoInterConsulta(true); }
    window.setTimeout(function () { if (typeof buscarInterConsultasYContenido === "function") { buscarInterConsultasYContenido(idHilo); } }, 200);
}

function centroFacturasAbrirVenta(codVenta) {
    verCerrarCentroFacturas(false);
    if (typeof verCerrarHistorialVenta === "function") { verCerrarHistorialVenta(); }
    window.setTimeout(function () {
        var campo = document.getElementById("inptBuscarHistorialVenta2"); if (campo) { campo.value = codVenta; }
        if (typeof buscarhistorialventa === "function") { buscarhistorialventa(); }
    }, 180);
}

function centroFacturasAbrirMovimiento(tipo, idMovimiento) {
    verCerrarCentroFacturas(false);
    if (tipo === "gasto") {
        if (typeof verCerrarAbmGasto === "function") { verCerrarAbmGasto(); }
        window.setTimeout(function () {
            if (typeof mostrarExtractoGasto === "function") { mostrarExtractoGasto(idMovimiento); }
        }, 220);
        return;
    }
    if (typeof verCerrarHistorialCompra === "function") { verCerrarHistorialCompra(); }
    window.setTimeout(function () {
        var campo = document.getElementById("inptBuscarHistorialCompra1"); if (campo) { campo.value = idMovimiento; }
        if (typeof buscarhistorialcompra === "function") { buscarhistorialcompra(); }
    }, 200);
}

function centroFacturasCrearLoteSeleccion() {
    var ids = Object.keys(centroFacturasEstado.seleccion).map(Number);
    if (!ids.length) { return; }
    var local = centroFacturasEstado.seleccion[ids[0]];
    var html = '<p class="cf-step-help">Se creara un borrador con <b>' + ids.length + '</b> facturas. Podra revisarlo e imprimirlo antes de marcar la entrega.</p><label class="cf-field-label">Destino</label><input id="cfLoteDestino" value="Administracion central"><label class="cf-field-label">Responsable de entrega</label><select id="cfLoteResponsable"><option value="">Usuario actual</option>' + centroFacturasOpciones((centroFacturasEstado.contexto || {}).funcionarios, "cod_usuario", "nombre_persona", "") + '</select><label class="cf-field-label">Observaciones</label><textarea id="cfLoteObservaciones" rows="3"></textarea><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo("Crear lote de originales", "Paso 1 de 2 · Preparar", html, '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasConfirmarCrearLote(' + Number(local) + ')">Crear borrador</button>');
}

function centroFacturasConfirmarCrearLote(codLocal) {
    var ids = Object.keys(centroFacturasEstado.seleccion).map(Number);
    centroFacturasSolicitar("crearLote", { cod_local: codLocal, facturas: ids, datos: { destino: document.getElementById("cfLoteDestino").value, cod_usuario_entrega: document.getElementById("cfLoteResponsable").value, observaciones: document.getElementById("cfLoteObservaciones").value } }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo crear el lote."; return; }
        centroFacturasCerrarDialogo(); centroFacturasLimpiarSeleccion(); centroFacturasAviso("Lote " + r.codigo_lote + " creado como borrador.", "success"); centroFacturasCambiarTab("lotes"); centroFacturasAbrirLote(r.id_lote);
    });
}

function centroFacturasAbrirLote(idLote) {
    centroFacturasSolicitar("detalleLote", { id_lote: idLote }).done(function (r) {
        if (!r || !r.ok) { centroFacturasAviso((r && r.mensaje) || "No se pudo cargar el lote.", "error"); return; }
        centroFacturasEstado.detalleLote = idLote; centroFacturasRenderDetalleLote(r); document.getElementById("centroFacturasDetalle").hidden = false;
    });
}

function centroFacturasRenderDetalleLote(datos) {
    var lote = datos.lote;
    document.getElementById("centroFacturasDetalleTitulo").textContent = lote.codigo_lote;
    var total = 0;
    var filas = (datos.facturas || []).map(function (f) {
        if (f.estado_detalle_lote !== "retirada") { total += Number(f.importe_total || 0); }
        return '<tr><td>#' + Number(f.id_factura) + '<small>' + centroFacturasEscapar(f.contraparte_mostrar) + '</small></td><td>' + centroFacturasEscapar(f.numero_factura || "Pendiente") + '</td><td>Gs. ' + centroFacturasNumero(f.importe_total) + '</td><td>' + centroFacturasBadge(String(f.estado_detalle_lote).replace(/_/g, " "), f.estado_detalle_lote === "recibida" ? "success" : (f.estado_detalle_lote === "observada" || f.estado_detalle_lote === "faltante" ? "danger" : "info")) + '</td><td><button type="button" onclick="centroFacturasAbrirDetalle(' + Number(f.id_factura) + ')">Ver</button>' + (lote.estado === "borrador" && centroFacturasPermiso("GESTIONARLOTESFACTURAS") && f.estado_detalle_lote !== "retirada" ? ' <button type="button" onclick="centroFacturasRetirarDeLote(' + Number(lote.id_lote) + ',' + Number(f.id_factura) + ')">Retirar</button>' : "") + '</td></tr>';
    }).join("");
    var html = '<div class="cf-detail-summary"><div><span>Estado</span>' + centroFacturasBadge(String(lote.estado).replace(/_/g, " "), lote.estado === "recibido" ? "success" : (lote.estado === "anulado" ? "danger" : "info")) + '</div><div><span>Facturas</span><b>' + (datos.facturas || []).filter(function (f) { return f.estado_detalle_lote !== "retirada"; }).length + '</b></div><div><span>Total</span><b>Gs. ' + centroFacturasNumero(total) + '</b></div></div>'
        + '<section class="cf-card"><h4>Entrega</h4><p><b>Origen:</b> ' + centroFacturasEscapar(lote.nombre_local) + '<br><b>Destino:</b> ' + centroFacturasEscapar(lote.destino) + '<br><b>Responsable:</b> ' + centroFacturasEscapar(lote.usuario_entrega || "Pendiente") + '<br><b>Creado:</b> ' + centroFacturasFecha(lote.fecha_creacion, true) + (lote.fecha_envio ? '<br><b>Enviado:</b> ' + centroFacturasFecha(lote.fecha_envio, true) : "") + '</p><div class="cf-card-actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasImprimirLote()"><i class="fa-solid fa-print"></i> Imprimir resumen</button>';
    if (lote.estado === "borrador" && centroFacturasPermiso("GESTIONARLOTESFACTURAS") && centroFacturasPermiso("ENVIARORIGINALFACTURA")) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasPrepararEnvioLote(' + Number(lote.id_lote) + ')">Marcar lote enviado</button>'; }
    if (["enviado", "recibido_parcial", "observado"].indexOf(lote.estado) >= 0 && centroFacturasPermiso("RECIBIRORIGINALFACTURA")) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasPrepararRecepcionLote(' + Number(lote.id_lote) + ')">Recibir lote</button>'; }
    if (lote.estado !== "anulado" && centroFacturasPermiso("GESTIONARLOTESFACTURAS")) { html += '<button type="button" class="cf-button cf-button--danger" onclick="centroFacturasPrepararAnulacionLote(' + Number(lote.id_lote) + ')">Anular</button>'; }
    html += '</div></section><section class="cf-card"><h4>Facturas incluidas</h4><div class="centro-facturas-table-wrap"><table class="centro-facturas-table"><thead><tr><th>Factura</th><th>Numero</th><th>Importe</th><th>Recepcion</th><th></th></tr></thead><tbody>' + filas + '</tbody></table></div></section>';
    if (centroFacturasPermiso("ADMINCENTROFACTURAS")) { html += centroFacturasRenderAuditoria(datos.auditoria || []); }
    document.getElementById("centroFacturasDetalleContenido").innerHTML = html;
}

function centroFacturasRetirarDeLote(idLote, idFactura) {
    centroFacturasSolicitar("retirarFacturaLote", { id_lote: idLote, id_factura: idFactura, motivo: "Retirada durante la revision del borrador" }).done(function (r) {
        if (!r || !r.ok) { centroFacturasAviso((r && r.mensaje) || "No se pudo retirar.", "error"); return; }
        centroFacturasAbrirLote(idLote); centroFacturasCargarListado();
    });
}

function centroFacturasPrepararEnvioLote(idLote) {
    centroFacturasAbrirDialogo("Confirmar entrega del lote", "Paso 2 de 2 · Enviar", '<p class="cf-step-help">Al confirmar, todas las facturas activas del lote quedaran como <b>Enviado a central</b>.</p><label class="cf-field-label">Responsable que transporta el lote</label><select id="cfEnvioLoteResponsable"><option value="">Usuario actual</option>' + centroFacturasOpciones((centroFacturasEstado.contexto || {}).funcionarios, "cod_usuario", "nombre_persona", "") + '</select><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasEnviarLote(' + idLote + ')">Confirmar envio</button>');
}

function centroFacturasEnviarLote(idLote) {
    centroFacturasSolicitar("enviarLote", { id_lote: idLote, cod_responsable: document.getElementById("cfEnvioLoteResponsable").value }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo enviar el lote."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Lote enviado y originales actualizados.", "success"); centroFacturasAbrirLote(idLote); centroFacturasRecargar();
    });
}

function centroFacturasPrepararRecepcionLote(idLote) {
    centroFacturasSolicitar("detalleLote", { id_lote: idLote }).done(function (r) {
        if (!r || !r.ok) { return; }
        var items = (r.facturas || []).filter(function (f) { return f.estado_detalle_lote !== "retirada"; }).map(function (f) {
            var estadoActual = f.estado_detalle_lote === "recibida" ? "recibida" : (f.estado_detalle_lote === "faltante" ? "faltante" : (f.estado_detalle_lote === "observada" ? "observada" : "recibida"));
            return '<div class="cf-reception-item" data-cf-recepcion="' + Number(f.id_factura) + '"><span><b>#' + Number(f.id_factura) + ' · ' + centroFacturasEscapar(f.numero_factura || "Sin numero") + '</b><br>' + centroFacturasEscapar(f.contraparte_mostrar) + '</span><select class="cfRecepcionEstado"><option value="recibida" ' + (estadoActual === "recibida" ? "selected" : "") + '>Recibida</option><option value="faltante" ' + (estadoActual === "faltante" ? "selected" : "") + '>Faltante</option><option value="observada" ' + (estadoActual === "observada" ? "selected" : "") + '>Observada</option></select><input class="cfRecepcionObservacion" placeholder="Observacion si falta o tiene diferencias" value="' + centroFacturasEscapar(f.observacion_lote) + '"></div>';
        }).join("");
        var html = '<p class="cf-step-help">Revise cada original. Las diferencias quedaran visibles para el responsable y en la auditoria.</p><div class="cf-reception-list">' + items + '</div><h4>Ubicacion de archivo</h4><div class="cf-grid"><label>Lote / archivador<input id="cfRecepcionLote"></label><label>Carpeta<input id="cfRecepcionCarpeta"></label><label>Caja<input id="cfRecepcionCaja"></label><label>Periodo<input id="cfRecepcionPeriodo"></label><label class="cf-span-2">Ubicacion fisica<input id="cfRecepcionUbicacion"></label></div><div id="cfDialogError" class="cf-form-error"></div>';
        centroFacturasAbrirDialogo("Recibir lote " + r.lote.codigo_lote, "Recepcion central", html, '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasRecibirLote(' + idLote + ')">Guardar recepcion</button>');
    });
}

function centroFacturasRecibirLote(idLote) {
    var recepciones = Array.prototype.map.call(document.querySelectorAll("[data-cf-recepcion]"), function (fila) {
        return { id_factura: Number(fila.getAttribute("data-cf-recepcion")), estado: fila.querySelector(".cfRecepcionEstado").value, observacion: fila.querySelector(".cfRecepcionObservacion").value };
    });
    var datos = { lote_archivo: document.getElementById("cfRecepcionLote").value, carpeta_archivo: document.getElementById("cfRecepcionCarpeta").value, caja_archivo: document.getElementById("cfRecepcionCaja").value, periodo_archivo: document.getElementById("cfRecepcionPeriodo").value, ubicacion_fisica: document.getElementById("cfRecepcionUbicacion").value };
    centroFacturasSolicitar("recibirLote", { id_lote: idLote, recepciones: recepciones, datos: datos }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo guardar la recepcion."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Recepcion guardada. Estado del lote: " + String(r.estado).replace(/_/g, " ") + ".", "success"); centroFacturasAbrirLote(idLote); centroFacturasRecargar();
    });
}

function centroFacturasPrepararAnulacionLote(idLote) {
    centroFacturasAbrirDialogo("Anular lote", "Accion controlada", '<p class="cf-step-help">No se eliminaran facturas ni archivos. El lote quedara anulado y auditado.</p><label class="cf-field-label">Motivo obligatorio</label><textarea id="cfAnularLoteMotivo" rows="3"></textarea><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--danger" onclick="centroFacturasAnularLote(' + idLote + ')">Anular lote</button>');
}

function centroFacturasAnularLote(idLote) {
    centroFacturasSolicitar("anularLote", { id_lote: idLote, motivo: document.getElementById("cfAnularLoteMotivo").value }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo anular."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Lote anulado sin eliminar sus facturas.", "success"); centroFacturasAbrirLote(idLote); centroFacturasRecargar();
    });
}

function centroFacturasImprimirLote() {
    document.body.classList.add("centro-facturas-print-lote");
    window.print();
    window.setTimeout(function () { document.body.classList.remove("centro-facturas-print-lote"); }, 300);
}

function centroFacturasAbrirConfiguracion() {
    var config = (centroFacturasEstado.contexto || {}).configuracion || {};
    var html = '<p class="cf-step-help">El plazo nuevo se aplicara solo a facturas registradas despues del cambio. No modifica vencimientos historicos.</p><label class="cf-field-label">Dias corridos para entregar el original</label><input id="cfConfigDias" type="number" min="1" max="60" value="' + Number(config.dias_plazo_original || 5) + '"><h4>OCR</h4><p>La integracion queda preparada, pero permanece deshabilitada hasta contar con un proveedor estable y autorizado.</p><label class="cf-check"><input id="cfConfigOcr" type="checkbox" ' + (Number(config.ocr_habilitado) ? "checked" : "") + '> Habilitar proveedor OCR</label><label class="cf-field-label">Proveedor OCR</label><input id="cfConfigOcrProveedor" value="' + centroFacturasEscapar(config.ocr_proveedor || "") + '" placeholder="Sin proveedor configurado"><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo("Configuracion", "Administracion", html, '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasGuardarConfiguracion()">Guardar configuracion</button>');
}

function centroFacturasGuardarConfiguracion() {
    centroFacturasSolicitar("actualizarConfiguracion", { dias_plazo_original: document.getElementById("cfConfigDias").value, ocr_habilitado: document.getElementById("cfConfigOcr").checked ? 1 : 0, ocr_proveedor: document.getElementById("cfConfigOcrProveedor").value }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo guardar."; return; }
        centroFacturasEstado.contexto.configuracion.dias_plazo_original = r.dias_plazo_original;
        centroFacturasCerrarDialogo(); centroFacturasAviso("Configuracion guardada sin recalcular facturas anteriores.", "success");
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () { window.setTimeout(centroFacturasPrepararAccesoInicial, 2500); });
} else {
    window.setTimeout(centroFacturasPrepararAccesoInicial, 2500);
}

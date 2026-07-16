var centroFacturasEstado = {
    contexto: null,
    tab: "entrantes",
    offset: 0,
    limite: 50,
    total: 0,
    filtroRapido: "",
    seleccion: {},
    seleccionDetalle: {},
    vistaLegajos: "ventas",
    seleccionLegajos: {},
    seleccionLegajosDetalle: {},
    detalleFactura: 0,
    detalleLote: 0,
    detalleLegajo: 0,
    detalleLoteLegajo: 0,
    detalleLegajoDatos: null,
    detalleLegajoVista: "documentos",
    detalleLegajoPagina: 0,
    detalleSolicitudPagare: 0,
    detalleSolicitudPagina: 0,
    detalleSolicitudDatos: null,
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

function centroFacturasPuedeVerLegajos() {
    return !!(centroFacturasEstado.contexto && centroFacturasEstado.contexto.legajos_disponibles
        && (centroFacturasPermiso("VERLEGAJOSVENTA") || centroFacturasPermiso("ADMINCENTROFACTURAS")));
}

function centroFacturasPuedeGestionarLegajos() {
    return centroFacturasPermiso("GESTIONARLEGAJOSVENTA") || centroFacturasPermiso("ADMINCENTROFACTURAS");
}

function centroFacturasPuedeGestionarLotesLegajos() {
    return centroFacturasPermiso("GESTIONARLOTESLEGAJOS") || centroFacturasPermiso("ADMINCENTROFACTURAS");
}

function centroFacturasPuedeVerSolicitudesPagare() {
    return centroFacturasPuedeVerLegajos() && !!(centroFacturasEstado.contexto || {}).solicitudes_pagare_disponibles;
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
        "RECIBIRORIGINALFACTURA", "GESTIONARLOTESFACTURAS", "ADMINCENTROFACTURAS",
        "VERLEGAJOSVENTA", "GESTIONARLEGAJOSVENTA", "GESTIONARLOTESLEGAJOS",
        "ENVIARLOTELEGAJOS", "RECIBIRLOTELEGAJOS"
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
    var botonLegajos = document.getElementById("btnCentroFacturasTabLegajos");
    var botonSolicitudesPagare = document.getElementById("btnCentroFacturasSolicitudesPagare");
    if (botonManual) { botonManual.hidden = !centroFacturasPermiso("REGISTRARFACTURAMANUAL"); }
    if (botonConfig) { botonConfig.hidden = !centroFacturasPermiso("ADMINCENTROFACTURAS"); }
    if (botonLegajos) { botonLegajos.hidden = !centroFacturasPuedeVerLegajos(); }
    if (botonSolicitudesPagare) { botonSolicitudesPagare.hidden = !centroFacturasPuedeVerSolicitudesPagare(); }
    if (!centroFacturasPuedeVerSolicitudesPagare() && centroFacturasEstado.vistaLegajos === "solicitudes") {
        centroFacturasEstado.vistaLegajos = "ventas";
    }
    if (!centroFacturasPuedeVerLegajos() && centroFacturasEstado.tab === "legajos") {
        centroFacturasEstado.tab = "entrantes";
        centroFacturasEstado.vistaLegajos = "ventas";
    }
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
    var ocultar = tab === "lotes" || (tab === "legajos" && centroFacturasEstado.vistaLegajos !== "ventas");
    if (resumen) { resumen.hidden = ocultar; }
    if (ocultar) { contenedor.innerHTML = ""; return; }
    var items = tab === "legajos" ? [
        ["ventas_periodo", "Ventas del período", "fa-cart-shopping", "", ""],
        ["legajos_completos", "Legajos completos", "fa-folder-closed", "", "completos"],
        ["legajos_incompletos", "Legajos incompletos", "fa-folder-minus", "is-alert", "incompletos"],
        ["listos_envio", "Listos para envío", "fa-box-open", "", "listos_envio"],
        ["en_transito", "En tránsito", "fa-truck-fast", "", "en_transito"],
        ["recibidos", "Recibidos", "fa-circle-check", "", "recibidos"],
        ["observados", "Observados", "fa-triangle-exclamation", "is-danger", "observados"]
    ] : tab === "emitidas" ? [
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
    if (["entrantes", "emitidas", "lotes", "legajos"].indexOf(tab) < 0) { return; }
    if (tab === "legajos" && !centroFacturasPuedeVerLegajos()) {
        centroFacturasAviso("No tiene permiso para consultar legajos de ventas.", "error");
        return;
    }
    centroFacturasEstado.tab = tab;
    centroFacturasEstado.offset = 0;
    centroFacturasEstado.filtroRapido = "";
    centroFacturasLimpiarSeleccion();
    centroFacturasLimpiarSeleccionLegajos();
    Array.prototype.forEach.call(document.querySelectorAll("[data-cf-tab]"), function (boton) {
        boton.classList.toggle("is-active", boton.getAttribute("data-cf-tab") === tab);
    });
    var esEntrante = tab === "entrantes";
    var esLegajos = tab === "legajos";
    ["centroFacturasFiltroPago", "centroFacturasFiltroOriginal"].forEach(function (id) {
        var campo = document.getElementById(id); if (campo) { campo.style.display = esEntrante ? "" : "none"; }
    });
    var manual = document.getElementById("btnCentroFacturasManual");
    if (manual) { manual.hidden = !esEntrante || !centroFacturasPermiso("REGISTRARFACTURAMANUAL"); }
    var config = document.getElementById("btnCentroFacturasConfig");
    if (config) { config.hidden = esLegajos || !centroFacturasPermiso("ADMINCENTROFACTURAS"); }
    var masFiltros = document.getElementById("btnCentroFacturasMasFiltros");
    if (masFiltros) { masFiltros.hidden = esLegajos; }
    if (esLegajos) {
        var filtrosExtra = document.getElementById("centroFacturasFiltrosExtra");
        if (filtrosExtra) { filtrosExtra.hidden = true; }
    }
    var subtabs = document.getElementById("centroFacturasLegajosSubtabs");
    if (subtabs) { subtabs.hidden = !esLegajos; }
    var busqueda = document.getElementById("centroFacturasBusqueda");
    if (busqueda) {
        busqueda.placeholder = esLegajos
            ? (centroFacturasEstado.vistaLegajos === "lotes" ? "Código, origen, destino o transportista" : "Paciente, cédula, venta o Legajo")
            : "Proveedor, factura, recibo, concepto o Hilo";
    }
    if (busqueda && esLegajos) { busqueda.placeholder = centroFacturasPlaceholderLegajos(centroFacturasEstado.vistaLegajos); }
    var tabla = document.getElementById("centroFacturasTabla");
    if (tabla) { tabla.classList.toggle("centro-facturas-table--legajos", esLegajos); }
    centroFacturasPintarMetricas({}, tab);
    centroFacturasBuscar();
}

function centroFacturasPlaceholderLegajos(vista) {
    if (vista === "lotes") { return "Codigo, origen, destino o transportista"; }
    if (vista === "solicitudes") { return "Solicitud, paciente, pagare o ubicacion"; }
    return "Paciente, cedula, venta o Legajo";
}

function centroFacturasCambiarVistaLegajos(vista) {
    if (["ventas", "lotes", "solicitudes"].indexOf(vista) < 0 || !centroFacturasPuedeVerLegajos()) { return; }
    if (vista === "solicitudes" && !centroFacturasPuedeVerSolicitudesPagare()) {
        centroFacturasAviso("La estructura de solicitudes de pagares todavia no esta instalada.", "error");
        return;
    }
    centroFacturasEstado.vistaLegajos = vista;
    centroFacturasEstado.offset = 0;
    centroFacturasEstado.filtroRapido = "";
    centroFacturasLimpiarSeleccionLegajos();
    Array.prototype.forEach.call(document.querySelectorAll("[data-cf-legajos-vista]"), function (boton) {
        var activo = boton.getAttribute("data-cf-legajos-vista") === vista;
        boton.classList.toggle("is-active", activo);
        boton.setAttribute("aria-pressed", activo ? "true" : "false");
    });
    var busqueda = document.getElementById("centroFacturasBusqueda");
    if (busqueda) { busqueda.placeholder = vista === "lotes" ? "Código, origen, destino o transportista" : "Paciente, cédula, venta o Legajo"; }
    if (busqueda) { busqueda.placeholder = centroFacturasPlaceholderLegajos(vista); }
    centroFacturasPintarMetricas({}, "legajos");
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
    var tabSolicitud = centroFacturasEstado.tab;
    var vistaSolicitud = centroFacturasEstado.vistaLegajos;
    var accion = tabSolicitud === "legajos"
        ? (vistaSolicitud === "lotes" ? "listarLotesLegajos" : (vistaSolicitud === "solicitudes" ? "listarSolicitudesPagare" : "listarLegajos"))
        : (tabSolicitud === "emitidas" ? "listarEmitidas" : (tabSolicitud === "lotes" ? "listarLotes" : "listarEntrantes"));
    centroFacturasSolicitar(accion, {
        filtros: centroFacturasFiltros(), limite: centroFacturasEstado.limite, offset: centroFacturasEstado.offset
    }).done(function (respuesta) {
        if (tabSolicitud !== centroFacturasEstado.tab || (tabSolicitud === "legajos" && vistaSolicitud !== centroFacturasEstado.vistaLegajos)) { return; }
        if (!respuesta || !respuesta.ok) {
            centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo cargar el listado.", "error");
            centroFacturasRenderVacio("No se pudo consultar la informacion.");
            return;
        }
        centroFacturasEstado.total = Number(respuesta.total || 0);
        centroFacturasPintarMetricas(respuesta.metricas || {}, centroFacturasEstado.tab);
        if (tabSolicitud === "entrantes") { centroFacturasRenderEntrantes(respuesta.registros || []); }
        else if (tabSolicitud === "emitidas") { centroFacturasRenderEmitidas(respuesta.registros || []); }
        else if (tabSolicitud === "lotes") { centroFacturasRenderLotes(respuesta.registros || []); }
        else if (vistaSolicitud === "lotes") { centroFacturasRenderLotesLegajos(respuesta.registros || []); }
        else if (vistaSolicitud === "solicitudes") { centroFacturasRenderSolicitudesPagare(respuesta.registros || []); }
        else { centroFacturasRenderLegajos(respuesta.registros || []); }
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

function centroFacturasUbicacionLoteActual(fila) {
    var detalle = String(fila.estado_detalle_lote_actual || "");
    var estadoLote = String(fila.estado_lote_actual || "");
    var partesArchivo = [fila.lote_archivo, fila.carpeta_archivo, fila.caja_archivo, fila.periodo_archivo].filter(function (valor) {
        return String(valor || "").trim() !== "";
    });
    if (detalle === "recibida") {
        return String(fila.ubicacion_fisica || partesArchivo.join(" · ") || "Ubicacion registrada en el lote");
    }
    if (detalle === "faltante") { return "Ubicacion sin confirmar"; }
    if (detalle === "observada") { return String(fila.ubicacion_fisica || "Ubicacion observada"); }
    if (detalle === "enviada" || ["enviado", "recibido_parcial"].indexOf(estadoLote) >= 0) {
        return "En transito a " + String(fila.destino_lote_actual || "destino");
    }
    return "En " + String(fila.nombre_local || "sucursal de origen");
}

function centroFacturasIndicadorLoteActual(fila) {
    var idLote = Number(fila.id_lote_actual || 0);
    if (!idLote) { return ""; }
    var codigo = String(fila.codigo_lote_actual || ("Lote #" + idLote));
    var estado = String(fila.estado_detalle_lote_actual || fila.estado_lote_actual || "incluida").replace(/_/g, " ");
    var ubicacion = centroFacturasUbicacionLoteActual(fila);
    return '<button type="button" class="cf-lot-indicator" onclick="centroFacturasAbrirLote(' + idLote + ')" title="Abrir ' + centroFacturasEscapar(codigo) + '">'
        + '<i class="fa-solid fa-box-archive" aria-hidden="true"></i><span><strong>' + centroFacturasEscapar(codigo) + '</strong>'
        + '<small>' + centroFacturasEscapar(estado + " · " + ubicacion) + '</small></span></button>';
}

function centroFacturasRenderEntrantes(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    cabecera.innerHTML = '<tr><th title="Seleccionar comprobante para lote">Lote</th><th>Gasto / origen</th><th>Contraparte</th><th>Respaldo</th><th>Importe</th><th>Estado documental</th><th>Original</th><th>Local</th><th>Acción</th></tr>';
    if (!registros.length) { centroFacturasRenderVacio("No hay gastos ni comprobantes recibidos con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (fila) {
        var id = Number(fila.id_factura || 0);
        var idGasto = Number(fila.id_gasto_esperado || fila.idgastosFK || 0);
        var idSugerida = Number(fila.id_factura_documento || 0);
        var visual = fila.estado_original_visual || {};
        var estadoDoc = fila.estado_documental || "sin_comprobante";
        var claseFila = estadoDoc === "consolidado" ? "cf-row--complete" : (estadoDoc === "por_vincular" ? "cf-row--pending" : (estadoDoc === "observado" ? "cf-row--observed" : "cf-row--missing"));
        var seleccionable = id && ["factura", "recibo"].indexOf(fila.tipo_documento) >= 0 && centroFacturasPermiso("GESTIONARLOTESFACTURAS") && !fila.id_lote_actual
            && ["recibido", "no_requiere_original"].indexOf(fila.estado_original) < 0 && ["rechazada", "anulada"].indexOf(fila.estado_validacion) < 0
            && fila.estado_registro === "activo";
        var tipoRespaldo = fila.tipo_documento === "recibo" ? "Recibo" : (fila.tipo_documento === "factura" ? "Factura" : "Sin comprobante");
        var fiscal = fila.numero_factura ? '<strong>' + centroFacturasEscapar(fila.numero_factura) + '</strong><small>' + (fila.tipo_documento === "recibo" ? "Recibo" : "Timbrado " + centroFacturasEscapar(fila.timbrado || "—")) + " · " + centroFacturasFecha(fila.fecha_emision) + "</small>"
            : (id ? '<strong>' + centroFacturasEscapar(tipoRespaldo) + '</strong><small>Sin número · ' + centroFacturasFecha(fila.fecha_emision) + '</small>'
                : '<strong>' + centroFacturasEscapar(tipoRespaldo) + '</strong><small>' + (estadoDoc === "por_vincular" ? "Adjunto específico encontrado en el Hilo" : "Todavía no se adjuntó un respaldo") + '</small>');
        var estadoTexto = estadoDoc === "consolidado" ? "Respaldado" : (estadoDoc === "por_vincular" ? "Pendiente de vincular" : (estadoDoc === "observado" ? "Observado" : "Falta comprobante"));
        var estadoTipo = estadoDoc === "consolidado" ? "success" : (estadoDoc === "observado" ? "danger" : "warning");
        var origen = idGasto ? '<strong>Gasto #' + idGasto + " · " + centroFacturasFecha(fila.fecha_origen || fila.gasto_fecha) + '</strong>' : '<strong>Comprobante #' + id + " · " + centroFacturasFecha(fila.fecha_registro_digital, true) + '</strong>';
        var origenDetalle = fila.cod_interConsultaFK ? "Hilo #" + Number(fila.cod_interConsultaFK) : (idGasto ? "Sin Hilo vinculado" : "Carga manual");
        var loteActual = centroFacturasIndicadorLoteActual(fila);
        var accion = id ? '<button type="button" onclick="centroFacturasAbrirDetalle(' + id + ')">Ver detalle</button>'
            : (idSugerida ? '<button type="button" onclick="centroFacturasAbrirDetalle(' + idSugerida + ')">Revisar adjunto</button>'
                : (fila.cod_interConsultaFK ? '<button type="button" onclick="centroFacturasAbrirHilo(' + Number(fila.cod_interConsultaFK) + ')">Abrir Hilo</button>' : '<button type="button" onclick="centroFacturasAbrirMovimiento(\'gasto\',' + idGasto + ')">Ver gasto</button>'));
        return '<tr data-cf-id="' + id + '" class="' + claseFila + (centroFacturasEstado.seleccion[id] ? " is-selected" : "") + '"><td>'
            + (loteActual || (seleccionable ? '<input type="checkbox" aria-label="Seleccionar ' + centroFacturasEscapar(tipoRespaldo.toLowerCase()) + ' para lote" ' + (centroFacturasEstado.seleccion[id] ? "checked" : "") + ' onchange="centroFacturasSeleccionar(' + id + ',' + Number(fila.cod_localFK) + ',this.checked,\'' + fila.tipo_documento + '\',' + Number(fila.importe_total || fila.importe_esperado || 0) + ')">' : ""))
            + '</td><td>' + origen + '<small>' + centroFacturasEscapar(origenDetalle) + "</small></td>"
            + '<td><strong>' + centroFacturasEscapar(fila.contraparte_mostrar) + '</strong><small>' + centroFacturasEscapar(fila.documento_contraparte || fila.gasto_motivo || "") + "</small></td>"
            + "<td>" + fiscal + '</td><td><strong>Gs. ' + centroFacturasNumero(fila.importe_total || fila.importe_esperado) + '</strong><small>' + centroFacturasEscapar(fila.concepto_contable || fila.concepto || fila.observaciones || fila.gasto_motivo || "Pendiente de clasificar") + "</small></td>"
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
    cabecera.innerHTML = "<tr><th>Lote</th><th>Creacion</th><th>Local</th><th>Documentos</th><th>Importes declarados</th><th>Estado</th><th>Responsable</th><th>Accion</th></tr>";
    if (!registros.length) { centroFacturasRenderVacio("Todavia no hay lotes con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (lote) {
        var tipo = lote.estado === "recibido" ? "success" : (lote.estado === "anulado" || lote.estado === "observado" ? "danger" : (lote.estado === "borrador" ? "warning" : "info"));
        var cantidadDocumentos = Number(lote.cantidad_documentos != null ? lote.cantidad_documentos : lote.cantidad_facturas || 0);
        var cantidadRecibos = Number(lote.cantidad_recibos || 0);
        var cantidadFacturas = Number(lote.cantidad_facturas_tipo != null ? lote.cantidad_facturas_tipo : Math.max(0, cantidadDocumentos - cantidadRecibos));
        return '<tr><td><strong>' + centroFacturasEscapar(lote.codigo_lote) + '</strong><small>' + centroFacturasEscapar(lote.destino) + '</small></td><td>' + centroFacturasFecha(lote.fecha_creacion, true) + '</td><td>' + centroFacturasEscapar(lote.nombre_local) + '</td>'
            + '<td><strong>' + cantidadDocumentos + ' originales</strong><small>' + cantidadFacturas + ' facturas · ' + cantidadRecibos + ' recibos<br>' + Number(lote.cantidad_recibidas || 0) + ' recibidos · ' + Number(lote.cantidad_observadas || 0) + ' observados</small></td>'
            + '<td><strong>Facturas: Gs. ' + centroFacturasNumero(lote.importe_facturas || 0) + '</strong><small>Recibos: Gs. ' + centroFacturasNumero(lote.importe_recibos || 0) + '</small></td><td>' + centroFacturasBadge(String(lote.estado).replace(/_/g, " "), tipo) + '</td><td>' + centroFacturasEscapar(lote.usuario_entrega || lote.usuario_creador || "—") + '</td><td><button type="button" onclick="centroFacturasAbrirLote(' + Number(lote.id_lote) + ')">Ver lote</button></td></tr>';
    }).join("");
}

function centroFacturasDocumentosLegajo(fila) {
    var salida = {};
    var documentos = fila && fila.documentos ? fila.documentos : {};
    if (Array.isArray(documentos)) {
        documentos.forEach(function (documento) {
            if (documento && documento.tipo_documento) { salida[documento.tipo_documento] = documento; }
        });
    } else {
        Object.keys(documentos || {}).forEach(function (tipo) { salida[tipo] = documentos[tipo]; });
    }
    return salida;
}

function centroFacturasEstadoDocumentoLegajo(documento, tipo) {
    documento = documento || {};
    var nombres = { contrato: "Contrato", pagare: "Pagaré", consentimiento: "Consentimiento", cedula: "Cédula", detalle_venta: "Detalle de venta" };
    var estado = String(documento.estado_documental || "pendiente");
    var fisico = String(documento.estado_fisico || "pendiente");
    var texto = "Pendiente";
    var clase = "pendiente";
    var icono = "fa-clock";
    var noRequerido = Number(documento.es_requerido) === 0;
    var conservaCopia = ["disponible", "validado", "observado"].indexOf(estado) >= 0
        || ["en_sucursal", "en_lote", "pendiente_custodia", "en_transito", "recibido", "faltante", "observado", "devuelto_cliente"].indexOf(fisico) >= 0;
    if (noRequerido && !conservaCopia) {
        texto = tipo === "pagare" ? "No aplica" : "No requerido"; clase = "no-aplica"; icono = "fa-minus";
    } else if (estado === "no_aplica" || fisico === "no_aplica") {
        texto = noRequerido && tipo !== "pagare" ? "No requerido" : "No aplica"; clase = "no-aplica"; icono = "fa-minus";
    } else if (estado === "observado" || ["faltante", "observado"].indexOf(fisico) >= 0) {
        texto = fisico === "faltante" ? "Faltante" : "Observado"; clase = "observado"; icono = "fa-triangle-exclamation";
    } else if (["disponible", "validado"].indexOf(estado) >= 0) {
        if (fisico === "en_sucursal") { texto = "Copia lista"; clase = "completo"; icono = "fa-check"; }
        else if (fisico === "en_lote") { texto = "En lote"; clase = "informativo"; icono = "fa-box"; }
        else if (fisico === "pendiente_custodia") { texto = "Por entregar"; clase = "informativo"; icono = "fa-handshake"; }
        else if (fisico === "en_transito") { texto = "En tránsito"; clase = "informativo"; icono = "fa-truck-fast"; }
        else if (fisico === "recibido") { texto = "Recibido"; clase = "completo"; icono = "fa-circle-check"; }
        else if (fisico === "devuelto_cliente") { texto = "Devuelto al cliente"; clase = "completo"; icono = "fa-handshake"; }
        else { texto = estado === "validado" ? "Validado" : "Disponible"; clase = "completo"; icono = "fa-check"; }
    } else if (documento.fuente_disponible) {
        texto = "Fuente disponible"; clase = "informativo"; icono = "fa-print";
    }
    if (noRequerido && conservaCopia && fisico !== "devuelto_cliente") { texto += " · no requerido"; }
    return { nombre: nombres[tipo] || tipo, codigo: documento.codigo_documento || "", texto: texto, clase: clase, icono: icono };
}

function centroFacturasRenderDocumentoLegajo(documento, tipo) {
    var visual = centroFacturasEstadoDocumentoLegajo(documento, tipo);
    return '<span class="cf-legajo-doc cf-legajo-doc--' + visual.clase + '" title="' + centroFacturasEscapar(visual.nombre + ': ' + visual.texto) + '">'
        + '<i class="fa-solid ' + visual.icono + '" aria-hidden="true"></i><span><strong>' + centroFacturasEscapar(visual.codigo || visual.nombre) + '</strong><small>' + centroFacturasEscapar(visual.nombre + ' · ' + visual.texto) + '</small></span></span>';
}

function centroFacturasRenderLegajos(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    var tipos = ["contrato", "pagare", "cedula", "consentimiento", "detalle_venta"];
    cabecera.innerHTML = '<tr><th title="Seleccionar legajo completo para envío">Lote</th><th>Legajo / venta</th><th>Paciente</th><th class="cf-legajo-documents-cell">Documentos del legajo</th><th>Integridad</th><th>Ubicación y trazabilidad</th><th>Local</th><th>Acción</th></tr>';
    if (!registros.length) { centroFacturasRenderVacio("No hay ventas para formar legajos con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (fila) {
        var codVenta = Number(fila.cod_venta || 0);
        var documentos = centroFacturasDocumentosLegajo(fila);
        var requeridos = Number(fila.cantidad_requerida != null ? fila.cantidad_requerida : 5);
        var enviables = Number(fila.cantidad_enviable != null ? fila.cantidad_enviable : requeridos);
        var listos = Number(fila.cantidad_lista || 0);
        var porcentaje = requeridos > 0 ? Math.min(100, Math.round((listos * 100) / requeridos)) : 100;
        var observado = fila.estado_legajo === "observado";
        var completo = fila.estado_legajo === "completo" || (requeridos > 0 && listos >= requeridos);
        var claseFila = observado ? "cf-row--observed" : (completo ? "cf-row--complete" : "cf-row--pending");
        var elegible = Number(fila.elegible_lote) === 1 && centroFacturasPuedeGestionarLotesLegajos();
        var lote = fila.lote_actual || {};
        var ubicacion = fila.ubicacion_actual || (lote.codigo_lote ? (lote.codigo_lote + " · " + String(lote.estado || "").replace(/_/g, " ")) : (completo ? "En sucursal, listo para preparar" : "Pendiente de completar"));
        var custodio = fila.custodio_actual || lote.custodio_actual || lote.transportista || "";
        var estadoVenta = Number(fila.es_anulada) ? centroFacturasBadge("Venta anulada", "danger") : centroFacturasBadge(fila.tipo_venta || fila.TipoVenta || "Venta", "info");
        return '<tr data-cf-legajo="' + codVenta + '" class="' + claseFila + (centroFacturasEstado.seleccionLegajos[codVenta] ? " is-selected" : "") + '"><td>'
            + (elegible ? '<input type="checkbox" aria-label="Seleccionar Legajo ' + codVenta + ' para lote" ' + (centroFacturasEstado.seleccionLegajos[codVenta] ? "checked" : "") + ' onchange="centroFacturasSeleccionarLegajo(' + codVenta + ',' + Number(fila.cod_local || fila.cod_localFK || 0) + ',this.checked,' + enviables + ')">' : "")
            + '</td><td><strong>Legajo #' + codVenta + '</strong><small>Venta del ' + centroFacturasFecha(fila.fecha_venta) + '</small>' + estadoVenta + '</td>'
            + '<td><strong>' + centroFacturasEscapar(fila.titular || fila.paciente || "Sin paciente") + '</strong><small>' + centroFacturasEscapar(fila.documento || "Sin documento") + '</small><small>Gs. ' + centroFacturasNumero(fila.importe_venta || fila.total_venta || 0) + '</small></td>'
            + '<td class="cf-legajo-documents-cell"><div class="cf-legajo-docs">' + tipos.map(function (tipo) { return centroFacturasRenderDocumentoLegajo(documentos[tipo], tipo); }).join("") + '</div></td>'
            + '<td><div class="cf-legajo-progress ' + (observado ? "cf-legajo-progress--observado" : (completo ? "" : "cf-legajo-progress--pendiente")) + '" style="--cf-legajo-progress:' + porcentaje + '%"><div class="cf-legajo-progress__heading"><b>' + listos + ' de ' + requeridos + '</b><span>obligatorios</span></div><div class="cf-legajo-progress__track"><div class="cf-legajo-progress__bar"></div></div><small>' + (requeridos === 1 ? 'Consentimiento obligatorio · otros no requeridos' : '5 documentos obligatorios') + '</small></div></td>'
            + '<td><strong>' + centroFacturasEscapar(ubicacion) + '</strong>' + (custodio ? '<small>Responsable: ' + centroFacturasEscapar(custodio) + '</small>' : '') + '</td>'
            + '<td>' + centroFacturasEscapar(fila.nombre_local || "—") + '</td><td><button type="button" onclick="centroFacturasAbrirLegajo(' + codVenta + ')">Ver legajo</button></td></tr>';
    }).join("");
    centroFacturasActualizarSeleccionLegajos();
}

function centroFacturasRenderLotesLegajos(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    cabecera.innerHTML = '<tr><th>Envío interno</th><th>Preparación</th><th>Origen → destino</th><th>Legajos / documentos</th><th>Importe referencial</th><th>Estado</th><th>Custodia actual</th><th>Recepción</th><th>Acción</th></tr>';
    if (!registros.length) { centroFacturasRenderVacio("Todavía no hay lotes de legajos con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (lote) {
        var estado = String(lote.estado || "borrador");
        var tipo = estado === "recibido" ? "success" : (["observado", "anulado"].indexOf(estado) >= 0 ? "danger" : (estado === "borrador" ? "warning" : "info"));
        var custodia = lote.custodio_actual || lote.usuario_custodia || (estado === "pendiente_custodia" ? "Pendiente de aceptación" : (lote.usuario_transportista || "Sin asignar"));
        return '<tr><td><strong>ENVÍO INTERNO DE DOCUMENTOS</strong><small>' + centroFacturasEscapar(lote.codigo_lote) + '</small></td>'
            + '<td>' + centroFacturasFecha(lote.fecha_creacion, true) + '<small>' + centroFacturasEscapar(lote.usuario_creador || "—") + '</small></td>'
            + '<td><strong>' + centroFacturasEscapar(lote.nombre_local_origen || lote.nombre_origen || "—") + '</strong><small>→ ' + centroFacturasEscapar(lote.nombre_local_destino || lote.destino_snapshot || "—") + '</small></td>'
            + '<td><strong>' + Number(lote.cantidad_legajos || 0) + ' legajos</strong><small>' + Number(lote.cantidad_documentos || 0) + ' documentos · ' + Number(lote.cantidad_recibidos || 0) + ' recibidos</small></td>'
            + '<td><strong>Gs. ' + centroFacturasNumero(lote.importe_ventas || 0) + '</strong><small>Total referencial de ventas</small></td>'
            + '<td>' + centroFacturasBadge(estado.replace(/_/g, " "), tipo) + '</td><td><strong>' + centroFacturasEscapar(custodia) + '</strong><small>Transportista: ' + centroFacturasEscapar(lote.usuario_transportista || "Pendiente") + '</small></td>'
            + '<td>' + (lote.fecha_recepcion ? centroFacturasFecha(lote.fecha_recepcion, true) : "Pendiente") + '<small>' + centroFacturasEscapar(lote.usuario_recepcion || "") + '</small></td>'
            + '<td><button type="button" onclick="centroFacturasAbrirLoteLegajo(' + Number(lote.id_lote) + ')">Ver envío</button></td></tr>';
    }).join("");
}

function centroFacturasEstadoSolicitudPagare(estado) {
    estado = String(estado || "solicitada");
    var mapa = {
        solicitada: { texto: "Solicitada", tipo: "warning", icono: "fa-clock" },
        aprobada: { texto: "Aprobada", tipo: "info", icono: "fa-check" },
        esperando_recepcion: { texto: "Esperando recepcion", tipo: "warning", icono: "fa-truck-fast" },
        preparada: { texto: "Preparada para entrega", tipo: "info", icono: "fa-box-open" },
        entregada: { texto: "Entregada", tipo: "success", icono: "fa-handshake" },
        rechazada: { texto: "Rechazada", tipo: "danger", icono: "fa-ban" },
        cancelada: { texto: "Cancelada", tipo: "danger", icono: "fa-xmark" }
    };
    return mapa[estado] || { texto: estado.replace(/_/g, " "), tipo: "info", icono: "fa-circle" };
}

function centroFacturasUbicacionPagare(solicitud) {
    var estadoFisico = String(solicitud.estado_fisico_actual || solicitud.estado_fisico || "pendiente");
    var loteActual = solicitud.lote_actual && typeof solicitud.lote_actual === "object" ? solicitud.lote_actual : {};
    var ubicacion = String(solicitud.ubicacion_actual || solicitud.ubicacion_fisica_actual || solicitud.ubicacion_fisica || "").trim();
    var lote = String(solicitud.codigo_lote_actual || loteActual.codigo_lote || "").trim();
    var estadoLote = String(solicitud.estado_lote_actual || loteActual.estado || "").replace(/_/g, " ");
    if (estadoFisico === "devuelto_cliente") { return "Devuelto al cliente"; }
    if (ubicacion) { return ubicacion; }
    if (lote) { return lote + (estadoLote ? " · " + estadoLote : ""); }
    if (["en_lote", "pendiente_custodia", "en_transito"].indexOf(estadoFisico) >= 0) { return "En traslado o custodia"; }
    return String(solicitud.nombre_local_ubicacion || solicitud.nombre_local_origen || solicitud.nombre_local || "Ubicacion pendiente");
}

function centroFacturasSolicitudPermite(solicitud, campo, predeterminado) {
    if (Object.prototype.hasOwnProperty.call(solicitud || {}, campo)) {
        return Number(solicitud[campo] || 0) === 1;
    }
    return !!predeterminado;
}

function centroFacturasAccionesSolicitudPagare(solicitud, incluirDetalle) {
    var id = Number(solicitud.id_solicitud || 0);
    var estado = String(solicitud.estado || "");
    var acciones = incluirDetalle === false ? "" : '<button type="button" onclick="centroFacturasAbrirSolicitudPagare(' + id + ')">Ver detalle</button>';
    if (estado === "solicitada" && centroFacturasSolicitudPermite(solicitud, "puede_aprobar", centroFacturasPermiso("ADMINCENTROFACTURAS"))) {
        acciones += ' <button type="button" onclick="centroFacturasPrepararResolucionPagare(' + id + ',\'aprobar\')">Aprobar</button>';
    }
    if (estado === "solicitada" && centroFacturasSolicitudPermite(solicitud, "puede_rechazar", centroFacturasPermiso("ADMINCENTROFACTURAS"))) {
        acciones += ' <button type="button" onclick="centroFacturasPrepararResolucionPagare(' + id + ',\'rechazar\')">Rechazar</button>';
    }
    if (["aprobada", "esperando_recepcion"].indexOf(estado) >= 0 && centroFacturasSolicitudPermite(solicitud, "puede_preparar", centroFacturasPuedeGestionarLegajos())) {
        acciones += ' <button type="button" onclick="centroFacturasPrepararPagare(' + id + ')">Preparar</button>';
    } else if (estado === "esperando_recepcion") {
        acciones += ' <span class="cf-muted">Esperando recepci&oacute;n</span>';
    }
    if (estado === "preparada" && centroFacturasSolicitudPermite(solicitud, "puede_entregar", centroFacturasPuedeGestionarLegajos())) {
        acciones += ' <button type="button" onclick="centroFacturasPrepararEntregaPagare(' + id + ')">Registrar entrega</button>';
    }
    if (["solicitada", "aprobada", "esperando_recepcion", "preparada"].indexOf(estado) >= 0 && centroFacturasSolicitudPermite(solicitud, "puede_cancelar", centroFacturasPuedeGestionarLegajos())) {
        acciones += ' <button type="button" onclick="centroFacturasPrepararCancelacionPagare(' + id + ')">Cancelar</button>';
    }
    return acciones;
}

function centroFacturasRenderSolicitudesPagare(registros) {
    var cabecera = document.getElementById("centroFacturasTablaCabecera");
    var cuerpo = document.getElementById("centroFacturasTablaCuerpo");
    var vacio = document.getElementById("centroFacturasVacio");
    cabecera.innerHTML = '<tr><th>Solicitud / pagar&eacute;</th><th>Paciente</th><th>Solicitud</th><th>Ubicaci&oacute;n actual</th><th>Estado</th><th>Responsables</th><th>Acci&oacute;n</th></tr>';
    if (!registros.length) { centroFacturasRenderVacio("No hay solicitudes de devolucion de pagares con estos filtros."); return; }
    vacio.hidden = true;
    cuerpo.innerHTML = registros.map(function (solicitud) {
        var estado = centroFacturasEstadoSolicitudPagare(solicitud.estado);
        var loteActual = solicitud.lote_actual && typeof solicitud.lote_actual === "object" ? solicitud.lote_actual : {};
        var codigoLoteActual = solicitud.codigo_lote_actual || loteActual.codigo_lote || "";
        var codigoDocumento = solicitud.codigo_documento || ("Legajo #" + Number(solicitud.cod_ventaFK || solicitud.cod_venta || 0) + "-02");
        var responsable = solicitud.usuario_prepara || solicitud.usuario_preparacion || solicitud.usuario_aprueba || solicitud.usuario_aprobacion || solicitud.usuario_solicita || solicitud.usuario_solicitud || "Pendiente";
        var lote = codigoLoteActual ? '<small><i class="fa-solid fa-box-archive" aria-hidden="true"></i> ' + centroFacturasEscapar(codigoLoteActual) + '</small>' : "";
        var clase = solicitud.estado === "entregada" ? "cf-row--complete" : (["rechazada", "cancelada"].indexOf(solicitud.estado) >= 0 ? "cf-row--observed" : "cf-row--pending");
        return '<tr class="' + clase + '"><td><strong>' + centroFacturasEscapar(solicitud.codigo_solicitud || ("Solicitud #" + Number(solicitud.id_solicitud))) + '</strong><small>' + centroFacturasEscapar(codigoDocumento) + '</small></td>'
            + '<td><strong>' + centroFacturasEscapar(solicitud.titular || solicitud.paciente || "Sin paciente") + '</strong><small>' + centroFacturasEscapar(solicitud.documento_cliente || solicitud.documento || "") + '</small></td>'
            + '<td><strong>' + centroFacturasEscapar(solicitud.solicitante_nombre || "Cliente") + '</strong><small>' + centroFacturasFecha(solicitud.fecha_solicitud, true) + ' · ' + centroFacturasEscapar(solicitud.motivo_solicitud || "Sin detalle") + '</small></td>'
            + '<td><strong>' + centroFacturasEscapar(centroFacturasUbicacionPagare(solicitud)) + '</strong>' + lote + '</td>'
            + '<td>' + centroFacturasBadge(estado.texto, estado.tipo) + '<small>' + centroFacturasEscapar(String(solicitud.estado_fisico_actual || solicitud.estado_fisico || "").replace(/_/g, " ")) + '</small></td>'
            + '<td><strong>' + centroFacturasEscapar(responsable) + '</strong><small>' + centroFacturasEscapar(solicitud.usuario_entrega || solicitud.usuario_rechaza || solicitud.usuario_rechazo || "") + '</small></td>'
            + '<td>' + centroFacturasAccionesSolicitudPagare(solicitud) + '</td></tr>';
    }).join("");
}

function centroFacturasSeleccionarLegajo(codVenta, codLocal, seleccionado, cantidadEnviable) {
    if (seleccionado) {
        var existentes = Object.keys(centroFacturasEstado.seleccionLegajos);
        if (existentes.length && Number(centroFacturasEstado.seleccionLegajos[existentes[0]]) !== Number(codLocal)) {
            centroFacturasAviso("Un lote de legajos solo puede salir de un mismo local.", "error");
            var checkbox = document.querySelector('tr[data-cf-legajo="' + codVenta + '"] input[type="checkbox"]');
            if (checkbox) { checkbox.checked = false; }
            return;
        }
        centroFacturasEstado.seleccionLegajos[codVenta] = Number(codLocal);
        centroFacturasEstado.seleccionLegajosDetalle[codVenta] = { cantidad_enviable: Number(cantidadEnviable || 0) };
    } else {
        delete centroFacturasEstado.seleccionLegajos[codVenta];
        delete centroFacturasEstado.seleccionLegajosDetalle[codVenta];
    }
    var fila = document.querySelector('tr[data-cf-legajo="' + codVenta + '"]');
    if (fila) { fila.classList.toggle("is-selected", seleccionado); }
    centroFacturasActualizarSeleccionLegajos();
}

function centroFacturasActualizarSeleccionLegajos() {
    var ids = Object.keys(centroFacturasEstado.seleccionLegajos);
    var cantidadDocumentos = ids.reduce(function (total, id) { return total + Number((centroFacturasEstado.seleccionLegajosDetalle[id] || {}).cantidad_enviable || 0); }, 0);
    var barra = document.getElementById("centroFacturasSeleccionLegajos");
    if (barra) { barra.hidden = centroFacturasEstado.tab !== "legajos" || centroFacturasEstado.vistaLegajos !== "ventas" || ids.length < 1; }
    var cantidad = document.getElementById("centroFacturasSeleccionLegajosCantidad");
    var documentos = document.getElementById("centroFacturasSeleccionLegajosDocumentos");
    if (cantidad) { cantidad.textContent = ids.length; }
    if (documentos) { documentos.textContent = cantidadDocumentos; }
}

function centroFacturasLimpiarSeleccionLegajos() {
    centroFacturasEstado.seleccionLegajos = {};
    centroFacturasEstado.seleccionLegajosDetalle = {};
    centroFacturasActualizarSeleccionLegajos();
    Array.prototype.forEach.call(document.querySelectorAll('#centroFacturasTablaCuerpo input[type="checkbox"]'), function (c) { c.checked = false; });
    Array.prototype.forEach.call(document.querySelectorAll("#centroFacturasTablaCuerpo tr"), function (f) { f.classList.remove("is-selected"); });
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

function centroFacturasSeleccionar(idFactura, codLocal, seleccionado, tipoDocumento, importe) {
    if (seleccionado) {
        var existentes = Object.keys(centroFacturasEstado.seleccion);
        if (existentes.length && Number(centroFacturasEstado.seleccion[existentes[0]]) !== Number(codLocal)) {
            centroFacturasAviso("Un lote solo puede incluir comprobantes del mismo local.", "error");
            var checkbox = document.querySelector('tr[data-cf-id="' + idFactura + '"] input[type="checkbox"]'); if (checkbox) { checkbox.checked = false; }
            return;
        }
        centroFacturasEstado.seleccion[idFactura] = codLocal;
        centroFacturasEstado.seleccionDetalle[idFactura] = {
            tipo_documento: tipoDocumento === "recibo" ? "recibo" : "factura",
            importe: Number(importe || 0)
        };
    } else {
        delete centroFacturasEstado.seleccion[idFactura];
        delete centroFacturasEstado.seleccionDetalle[idFactura];
    }
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
    centroFacturasEstado.seleccionDetalle = {};
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
        + '<label id="cfManualGrupoTipo">Tipo de contraparte<select id="cfManualTipo" onchange="centroFacturasActualizarTipoManual()"><option value="proveedor">Proveedor</option><option value="funcionario">Funcionario</option><option value="otro">Otro</option></select></label>'
        + '<label id="cfManualGrupoProveedor">Proveedor<select id="cfManualProveedor"><option value="">Seleccione</option>' + centroFacturasOpciones(c.proveedores, "cod_proveedor", "nombre_persona", "") + '</select></label>'
        + '<label id="cfManualGrupoFuncionario" hidden>Funcionario<select id="cfManualFuncionario"><option value="">Seleccione</option>' + centroFacturasOpciones(c.funcionarios, "cod_usuario", "nombre_persona", "") + '</select></label>'
        + '<label id="cfManualGrupoNombre" hidden>Nombre o raz&oacute;n social<input id="cfManualNombre" maxlength="255"></label>'
        + '<label id="cfManualGrupoDocumento">RUC o documento<input id="cfManualDocumento" maxlength="45" placeholder="Complete si no figura en el registro"></label>'
        + '<label><span id="cfManualNumeroEtiqueta">N.&ordm; de factura (opcional)</span><input id="cfManualNumero" maxlength="80" placeholder="Ej. 001-001-0001234"></label><label id="cfManualTimbradoGrupo">Timbrado<input id="cfManualTimbrado" maxlength="45"></label>'
        + '<label>Fecha de emisi&oacute;n<input id="cfManualFecha" type="date" value="' + fecha + '"></label><label>Importe total (Gs.)<input id="cfManualImporte" inputmode="decimal" placeholder="0"></label>'
        + '<label class="cf-span-2">Concepto contable (opcional)<input id="cfManualConcepto" maxlength="255" placeholder="Puede clasificarse posteriormente"></label>'
        + '<label class="cf-span-2"><span id="cfManualObservacionesEtiqueta">Observaciones (opcional)</span><textarea id="cfManualObservaciones" rows="2" maxlength="3000"></textarea></label>'
        + '<label class="cf-span-2"><span id="cfManualArchivoEtiqueta">Archivo de factura (PDF o imagen; hasta 10 p&aacute;ginas)</span><input id="cfManualArchivos" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif" multiple required></label>'
        + '<input type="hidden" id="cfManualConfirmarDuplicado" value="0"><div id="cfManualDuplicado" class="cf-span-2" hidden></div><div id="cfManualError" class="cf-form-error cf-span-2"></div></form>';
    centroFacturasAbrirDialogo("Registrar comprobante recibido", "Carga manual", html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasGuardarManual()">Registrar comprobante</button>');
    centroFacturasActualizarDocumentoManual();
}

function centroFacturasActualizarDocumentoManual() {
    var esRecibo = document.getElementById("cfManualTipoDocumento").value === "recibo";
    document.getElementById("cfManualNumeroEtiqueta").textContent = esRecibo ? "N.º de recibo (opcional)" : "N.º de factura (opcional)";
    document.getElementById("cfManualTimbradoGrupo").hidden = esRecibo;
    document.getElementById("cfManualArchivoEtiqueta").textContent = "Archivo de " + (esRecibo ? "recibo" : "factura") + " (PDF o imagen; hasta 10 páginas)";
    document.getElementById("cfManualObservacionesEtiqueta").textContent = esRecibo ? "Descripción del recibo" : "Observaciones (opcional)";
    centroFacturasActualizarTipoManual();
}

function centroFacturasActualizarTipoManual() {
    var tipo = document.getElementById("cfManualTipo").value;
    var esRecibo = document.getElementById("cfManualTipoDocumento").value === "recibo";
    document.getElementById("cfManualGrupoTipo").hidden = esRecibo;
    document.getElementById("cfManualGrupoProveedor").hidden = esRecibo || tipo !== "proveedor";
    document.getElementById("cfManualGrupoFuncionario").hidden = esRecibo || tipo !== "funcionario";
    document.getElementById("cfManualGrupoNombre").hidden = esRecibo || tipo !== "otro";
    document.getElementById("cfManualGrupoDocumento").hidden = esRecibo;
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

function centroFacturasNombreLocal(codLocal) {
    var locales = ((centroFacturasEstado.contexto || {}).locales_destino || (centroFacturasEstado.contexto || {}).locales || []);
    for (var i = 0; i < locales.length; i++) {
        if (Number(locales[i].cod_local) === Number(codLocal)) { return locales[i].Nombre || locales[i].nombre_local || "Local #" + codLocal; }
    }
    return "Local #" + codLocal;
}

function centroFacturasPrepararLoteLegajos() {
    var ids = Object.keys(centroFacturasEstado.seleccionLegajos).map(Number);
    if (!ids.length || !centroFacturasPuedeGestionarLotesLegajos()) { return; }
    var codLocal = Number(centroFacturasEstado.seleccionLegajos[ids[0]] || 0);
    var cantidadDocumentos = ids.reduce(function (total, id) { return total + Number((centroFacturasEstado.seleccionLegajosDetalle[id] || {}).cantidad_enviable || 0); }, 0);
    var contexto = centroFacturasEstado.contexto || {};
    var destinos = (contexto.locales_destino || contexto.locales || []).filter(function (local) { return Number(local.cod_local) !== codLocal; });
    var transportistas = (contexto.funcionarios || []).filter(function (funcionario) { return Number(funcionario.puede_custodiar_legajos) === 1; });
    var ayudaTransportista = transportistas.length
        ? '<small>Solo se muestran funcionarios autorizados para aceptar la custodia.</small>'
        : '<small class="cf-form-error">No hay funcionarios habilitados para custodiar legajos. Asigne ENVIARLOTELEGAJOS o ADMINCENTROFACTURAS antes de crear el lote.</small>';
    var html = '<p class="cf-step-help"><b>Envío interno de documentos.</b> El lote será exclusivo de legajos de ventas y no se mezclará con facturas o recibos. El servidor volverá a comprobar los documentos obligatorios y las copias físicas disponibles de cada venta.</p>'
        + '<div class="cf-detail-summary"><div><span>Origen</span><b>' + centroFacturasEscapar(centroFacturasNombreLocal(codLocal)) + '</b></div><div><span>Legajos</span><b>' + ids.length + '</b></div><div><span>Documentos físicos</span><b>' + cantidadDocumentos + '</b></div></div>'
        + '<div class="cf-grid"><label>Local de destino<select id="cfLegajoLoteDestino"><option value="">Seleccione el destino</option>' + centroFacturasOpciones(destinos, "cod_local", "Nombre", "") + '</select></label>'
        + '<label>Transportista asignado<select id="cfLegajoLoteTransportista" ' + (transportistas.length ? '' : 'disabled') + '><option value="">' + (transportistas.length ? 'Seleccione al responsable' : 'Sin funcionarios habilitados') + '</option>' + centroFacturasOpciones(transportistas, "cod_usuario", "nombre_persona", "") + '</select>' + ayudaTransportista + '</label>'
        + '<label class="cf-span-2">Observaciones del envío<textarea id="cfLegajoLoteObservaciones" rows="3" maxlength="3000" placeholder="Indicaciones de entrega o referencia interna"></textarea></label></div><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo("Preparar lote de legajos", "Paso 1 de 3 · Preparación", html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" ' + (transportistas.length ? '' : 'disabled') + ' onclick="centroFacturasConfirmarLoteLegajos(' + codLocal + ')">Crear borrador</button>');
}

function centroFacturasConfirmarLoteLegajos(codLocal) {
    var destino = Number((document.getElementById("cfLegajoLoteDestino") || {}).value || 0);
    var transportista = Number((document.getElementById("cfLegajoLoteTransportista") || {}).value || 0);
    var error = document.getElementById("cfDialogError");
    if (!destino || destino === Number(codLocal)) { if (error) { error.textContent = "Seleccione un local de destino diferente al origen."; } return; }
    if (!transportista) { if (error) { error.textContent = "Seleccione quién transportará y deberá aceptar la custodia."; } return; }
    var ids = Object.keys(centroFacturasEstado.seleccionLegajos).map(Number);
    centroFacturasSolicitar("crearLoteLegajos", {
        cod_local: codLocal,
        ventas: ids,
        datos: {
            cod_local_destino: destino,
            cod_usuario_transportista: transportista,
            observaciones: (document.getElementById("cfLegajoLoteObservaciones") || {}).value || ""
        }
    }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo crear el lote de legajos."; } return; }
        centroFacturasCerrarDialogo();
        centroFacturasLimpiarSeleccionLegajos();
        centroFacturasAviso("Lote " + respuesta.codigo_lote + " creado como borrador, sin mezclar documentos financieros.", "success");
        centroFacturasCambiarVistaLegajos("lotes");
        centroFacturasAbrirLoteLegajo(respuesta.id_lote);
    });
}

function centroFacturasConfigurarDrawer(tipo) {
    var drawer = document.getElementById("centroFacturasDetalle");
    if (!drawer) { return; }
    drawer.classList.toggle("is-legajo-detail", tipo === "legajo" || tipo === "solicitud_pagare");
    drawer.classList.toggle("is-solicitud-pagare", tipo === "solicitud_pagare");
}

function centroFacturasAbrirLegajo(codVenta) {
    codVenta = Number(codVenta || 0);
    if (!codVenta || !centroFacturasPuedeVerLegajos()) { return; }
    centroFacturasSolicitar("detalleLegajo", { cod_venta: codVenta }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo abrir el legajo.", "error"); return; }
        centroFacturasEstado.detalleLegajo = codVenta;
        centroFacturasConfigurarDrawer("legajo");
        centroFacturasRenderDetalleLegajoCompacto(respuesta);
        document.getElementById("centroFacturasDetalle").hidden = false;
    });
}

function centroFacturasRenderDetalleLegajo(datos) {
    var venta = datos.venta || datos.legajo || {};
    var documentos = centroFacturasDocumentosLegajo({ documentos: datos.documentos || venta.documentos || {} });
    var tipos = ["contrato", "pagare", "cedula", "consentimiento", "detalle_venta"];
    var requeridos = Number(venta.cantidad_requerida != null ? venta.cantidad_requerida : datos.cantidad_requerida || 5);
    var listos = Number(venta.cantidad_lista != null ? venta.cantidad_lista : datos.cantidad_lista || 0);
    document.getElementById("centroFacturasDetalleTitulo").textContent = "Legajo #" + Number(venta.cod_venta || centroFacturasEstado.detalleLegajo);
    var filas = tipos.map(function (tipo) {
        var documento = documentos[tipo] || { tipo_documento: tipo, estado_documental: tipo === "pagare" && String(venta.tipo_venta || venta.TipoVenta).toUpperCase() === "CONTADO" ? "no_aplica" : "pendiente" };
        var visual = centroFacturasEstadoDocumentoLegajo(documento, tipo);
        var bloqueado = ["en_lote", "pendiente_custodia", "en_transito", "recibido"].indexOf(String(documento.estado_fisico || "")) >= 0;
        var acciones = "";
        if (centroFacturasPuedeGestionarLegajos() && visual.clase !== "no-aplica" && !bloqueado && !Number(venta.es_anulada)) {
            if (["disponible", "validado"].indexOf(String(documento.estado_documental || "")) < 0 || String(documento.estado_fisico || "") !== "en_sucursal") {
                acciones += '<button type="button" onclick="centroFacturasPrepararCambioDocumentoLegajo(' + Number(venta.cod_venta) + ',\'' + tipo + '\',\'confirmar_copia\')">Confirmar copia física</button>';
            } else {
                acciones += '<button type="button" onclick="centroFacturasPrepararCambioDocumentoLegajo(' + Number(venta.cod_venta) + ',\'' + tipo + '\',\'marcar_pendiente\')">Marcar pendiente</button>';
            }
            acciones += ' <button type="button" onclick="centroFacturasPrepararCambioDocumentoLegajo(' + Number(venta.cod_venta) + ',\'' + tipo + '\',\'observar\')">Observar</button>';
        }
        var fuente = documento.fuente_disponible ? '<small>Existe una fuente en el sistema para revisar o imprimir; no confirma por sí sola la copia física.</small>' : "";
        return '<tr><td>' + centroFacturasRenderDocumentoLegajo(documento, tipo) + '</td><td>' + centroFacturasEscapar(String(documento.estado_fisico || "pendiente").replace(/_/g, " ")) + fuente + '</td><td>' + centroFacturasEscapar(documento.ubicacion_fisica || venta.nombre_local || "Pendiente") + '</td><td>' + centroFacturasEscapar(documento.usuario_confirmacion || "—") + '<small>' + centroFacturasFecha(documento.fecha_confirmacion, true) + '</small></td><td>' + acciones + '</td></tr>';
    }).join("");
    var lote = datos.lote_actual || venta.lote_actual || {};
    var html = '<div class="cf-detail-summary"><div><span>Venta</span><b>#' + Number(venta.cod_venta) + ' · ' + centroFacturasFecha(venta.fecha_venta) + '</b></div><div><span>Integridad</span><b>' + listos + ' de ' + requeridos + ' obligatorios</b></div><div><span>Condición</span><b>' + centroFacturasEscapar(venta.tipo_venta || venta.TipoVenta || "—") + '</b></div></div>'
        + '<section class="cf-card"><h4>Identificación del legajo</h4><p><b>Paciente:</b> ' + centroFacturasEscapar(venta.titular || venta.paciente || "—") + '<br><b>Documento:</b> ' + centroFacturasEscapar(venta.documento || "—") + '<br><b>Local de origen:</b> ' + centroFacturasEscapar(venta.nombre_local || "—") + '<br><b>Importe de la venta:</b> Gs. ' + centroFacturasNumero(venta.importe_venta || venta.total_venta || 0) + '</p><div class="cf-card-actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirVenta(' + Number(venta.cod_venta) + ')">Abrir venta</button></div></section>'
        + (lote.codigo_lote ? '<section class="cf-card"><h4>Ubicación y lote actual</h4><p><b>' + centroFacturasEscapar(lote.codigo_lote) + '</b> · ' + centroFacturasEscapar(String(lote.estado || "").replace(/_/g, " ")) + '<br>' + centroFacturasEscapar(lote.nombre_local_origen || venta.nombre_local || "") + ' → ' + centroFacturasEscapar(lote.nombre_local_destino || lote.destino_snapshot || "") + '</p><div class="cf-card-actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirLoteLegajo(' + Number(lote.id_lote) + ')">Ver trazabilidad</button></div></section>' : '')
        + '<section class="cf-card"><h4>Los cinco documentos</h4><p>Cada casilla representa la copia física de esta venta. Una foto vigente del paciente o una impresión disponible no se consideran copia confirmada hasta registrarlo aquí.</p><div class="centro-facturas-table-wrap"><table class="centro-facturas-table"><thead><tr><th>Documento</th><th>Estado físico</th><th>Ubicación</th><th>Confirmación</th><th>Acción</th></tr></thead><tbody>' + filas + '</tbody></table></div></section>';
    document.getElementById("centroFacturasDetalleContenido").innerHTML = html;
}

function centroFacturasCodigoDocumentoLegajo(codVenta, tipo) {
    var numeros = { contrato: "01", pagare: "02", cedula: "03", consentimiento: "04", detalle_venta: "05" };
    return "Legajo #" + Number(codVenta || 0) + "-" + (numeros[tipo] || "00");
}

function centroFacturasRenderDetalleLegajoCompacto(datos) {
    centroFacturasEstado.detalleLegajoDatos = datos || {};
    centroFacturasEstado.detalleLegajoVista = "documentos";
    centroFacturasEstado.detalleLegajoPagina = 0;
    centroFacturasPintarDetalleLegajo();
}

function centroFacturasCambiarVistaDetalleLegajo(vista) {
    if (["resumen", "documentos", "trazabilidad"].indexOf(vista) < 0) { return; }
    centroFacturasEstado.detalleLegajoVista = vista;
    centroFacturasEstado.detalleLegajoPagina = 0;
    centroFacturasPintarDetalleLegajo();
}

function centroFacturasPaginaDetalleLegajo(direccion) {
    var datos = centroFacturasEstado.detalleLegajoDatos || {};
    var eventos = datos.eventos || [];
    var paginas = Math.max(1, Math.ceil(eventos.length / 5));
    centroFacturasEstado.detalleLegajoPagina = Math.max(0, Math.min(paginas - 1, centroFacturasEstado.detalleLegajoPagina + Number(direccion || 0)));
    centroFacturasPintarDetalleLegajo();
}

function centroFacturasAccionesDocumentoLegajo(venta, documento, tipo, solicitudActiva) {
    var codVenta = Number(venta.cod_venta || centroFacturasEstado.detalleLegajo || 0);
    var fisico = String(documento.estado_fisico || "pendiente");
    var documental = String(documento.estado_documental || "pendiente");
    var noAplicaPagare = tipo === "pagare" && Number(documento.es_requerido) === 0;
    var bloqueado = ["en_lote", "pendiente_custodia", "en_transito", "recibido", "devuelto_cliente"].indexOf(fisico) >= 0;
    var pagareSolicitable = ["disponible", "validado"].indexOf(documental) >= 0
        && ["en_sucursal", "en_lote", "pendiente_custodia", "en_transito", "recibido"].indexOf(fisico) >= 0;
    var acciones = "";
    if (centroFacturasPuedeGestionarLegajos() && !noAplicaPagare && !bloqueado && !Number(venta.es_anulada)) {
        if (["disponible", "validado"].indexOf(documental) < 0 || fisico !== "en_sucursal") {
            acciones += '<button type="button" title="Confirmar copia fisica" aria-label="Confirmar copia fisica" onclick="centroFacturasPrepararCambioDocumentoLegajo(' + codVenta + ',\'' + tipo + '\',\'confirmar_copia\')"><i class="fa-solid fa-check"></i> Confirmar</button>';
        } else {
            acciones += '<button type="button" title="Marcar pendiente" aria-label="Marcar copia pendiente" onclick="centroFacturasPrepararCambioDocumentoLegajo(' + codVenta + ',\'' + tipo + '\',\'marcar_pendiente\')"><i class="fa-solid fa-rotate-left"></i> Pendiente</button>';
        }
        acciones += '<button type="button" title="Observar copia" aria-label="Observar copia fisica" onclick="centroFacturasPrepararCambioDocumentoLegajo(' + codVenta + ',\'' + tipo + '\',\'observar\')"><i class="fa-solid fa-triangle-exclamation"></i></button>';
    }
    if (tipo === "pagare" && !noAplicaPagare && centroFacturasPuedeVerSolicitudesPagare() && !Number(venta.es_anulada)) {
        if (solicitudActiva && Number(solicitudActiva.id_solicitud || 0)) {
            acciones += '<button type="button" class="cf-button--request" aria-label="Ver solicitud de devolucion" onclick="centroFacturasAbrirSolicitudPagare(' + Number(solicitudActiva.id_solicitud) + ')"><i class="fa-solid fa-file-signature"></i> Ver solicitud</button>';
        } else if (pagareSolicitable && centroFacturasPuedeGestionarLegajos()) {
            acciones += '<button type="button" class="cf-button--request" aria-label="Solicitar devolucion del pagare" onclick="centroFacturasAbrirNuevaSolicitudPagare(' + codVenta + ',' + Number(documento.id_documento || 0) + ')"><i class="fa-solid fa-file-signature"></i> Solicitar devolucion</button>';
        }
    }
    return acciones || '<span class="cf-muted">Sin acciones</span>';
}

function centroFacturasPintarDetalleLegajo() {
    var datos = centroFacturasEstado.detalleLegajoDatos || {};
    var venta = datos.venta || datos.legajo || {};
    var codVenta = Number(venta.cod_venta || centroFacturasEstado.detalleLegajo || 0);
    var documentos = centroFacturasDocumentosLegajo({ documentos: datos.documentos || venta.documentos || {} });
    var tipos = ["contrato", "pagare", "cedula", "consentimiento", "detalle_venta"];
    var vista = centroFacturasEstado.detalleLegajoVista;
    var requeridos = Number(venta.cantidad_requerida != null ? venta.cantidad_requerida : 5);
    var listos = Number(venta.cantidad_lista || 0);
    var solicitudActiva = datos.solicitud_pagare_activa || venta.solicitud_pagare_activa || null;
    var lote = datos.lote_actual || venta.lote_actual || {};
    var titulo = document.getElementById("centroFacturasDetalleTitulo");
    if (titulo) { titulo.textContent = "Legajo #" + codVenta; }
    var cabecera = '<div class="cf-legajo-detail__summary"><div><span>Paciente</span><b>' + centroFacturasEscapar(venta.titular || venta.paciente || "Sin paciente") + '</b></div>'
        + '<div><span>Venta</span><b>#' + codVenta + ' · ' + centroFacturasFecha(venta.fecha_venta) + '</b></div>'
        + '<div><span>Condicion</span><b>' + centroFacturasEscapar(venta.tipo_venta || venta.TipoVenta || "—") + '</b></div>'
        + '<div><span>Integridad</span><b>' + listos + ' de ' + requeridos + ' obligatorios</b></div></div>';
    var navegacion = '<nav class="cf-legajo-detail__tabs" aria-label="Detalle del legajo">' + [
        ["resumen", "fa-address-card", "Resumen"], ["documentos", "fa-folder-tree", "Documentos"], ["trazabilidad", "fa-route", "Trazabilidad"]
    ].map(function (item) {
        return '<button type="button" class="' + (vista === item[0] ? "is-active" : "") + '" aria-pressed="' + (vista === item[0] ? "true" : "false") + '" onclick="centroFacturasCambiarVistaDetalleLegajo(\'' + item[0] + '\')"><i class="fa-solid ' + item[1] + '"></i> ' + item[2] + '</button>';
    }).join("") + '</nav>';
    var contenido = "";
    if (vista === "resumen") {
        contenido = '<section class="cf-legajo-detail__panel"><div class="cf-legajo-data-grid">'
            + '<div><span>Documento del paciente</span><b>' + centroFacturasEscapar(venta.documento || "Sin documento") + '</b></div>'
            + '<div><span>Local de origen</span><b>' + centroFacturasEscapar(venta.nombre_local || "—") + '</b></div>'
            + '<div><span>Importe de venta</span><b>Gs. ' + centroFacturasNumero(venta.importe_venta || venta.total_venta || 0) + '</b></div>'
            + '<div><span>Ubicacion actual</span><b>' + centroFacturasEscapar(venta.ubicacion_actual || (lote.codigo_lote ? lote.codigo_lote : venta.nombre_local || "Pendiente")) + '</b></div>'
            + '<div><span>Lote vigente</span><b>' + centroFacturasEscapar(lote.codigo_lote || "Sin lote activo") + '</b></div>'
            + '<div><span>Copias enviables</span><b>' + Number(venta.cantidad_enviable || 0) + '</b></div></div>'
            + '<div class="cf-legajo-detail__actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirVenta(' + codVenta + ')">Abrir venta</button>'
            + (lote.id_lote ? '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasAbrirLoteLegajo(' + Number(lote.id_lote) + ')">Ver lote</button>' : '') + '</div></section>';
    } else if (vista === "documentos") {
        contenido = '<section class="cf-legajo-detail__panel"><p class="cf-legajo-detail__help">Cada fila representa una copia fisica identificada de esta venta.</p><div class="cf-legajo-document-list">' + tipos.map(function (tipo) {
            var documento = documentos[tipo] || { id_documento: 0, tipo_documento: tipo, es_requerido: 1, estado_documental: "pendiente", estado_fisico: "pendiente" };
            documento.codigo_documento = documento.codigo_documento || centroFacturasCodigoDocumentoLegajo(codVenta, tipo);
            var visual = centroFacturasEstadoDocumentoLegajo(documento, tipo);
            var ubicacion = documento.ubicacion_fisica || (documento.estado_fisico === "devuelto_cliente" ? "Devuelto al cliente" : venta.nombre_local || "Pendiente");
            return '<article class="cf-legajo-document-row cf-legajo-document-row--' + visual.clase + '"><i class="fa-solid ' + visual.icono + '" aria-hidden="true"></i><div><b>' + centroFacturasEscapar(documento.codigo_documento) + '</b><small>' + centroFacturasEscapar(visual.nombre) + (Number(documento.es_requerido) ? ' · obligatorio' : (tipo === "pagare" ? ' · no aplica' : ' · no requerido')) + '</small></div>'
                + '<div><span>Estado</span><b>' + centroFacturasEscapar(visual.texto) + '</b></div><div><span>Ubicacion</span><b title="' + centroFacturasEscapar(ubicacion) + '">' + centroFacturasEscapar(ubicacion) + '</b></div>'
                + '<div class="cf-legajo-document-row__actions">' + centroFacturasAccionesDocumentoLegajo(venta, documento, tipo, solicitudActiva) + '</div></article>';
        }).join("") + '</div></section>';
    } else {
        var eventos = datos.eventos || [];
        var pagina = centroFacturasEstado.detalleLegajoPagina;
        var paginas = Math.max(1, Math.ceil(eventos.length / 5));
        var inicio = pagina * 5;
        var visibles = eventos.slice(inicio, inicio + 5);
        var timeline = visibles.map(function (evento) {
            return '<li class="cf-legajo-trace__item"><span class="cf-legajo-trace__icon"><i class="fa-solid fa-circle"></i></span><span class="cf-legajo-trace__body"><b>' + centroFacturasEscapar(evento.codigo_documento || centroFacturasCodigoDocumentoLegajo(codVenta, evento.tipo_documento)) + ' · ' + centroFacturasEscapar(String(evento.accion || "evento").replace(/_/g, " ")) + '</b><span title="' + centroFacturasEscapar(evento.detalle || "") + '">' + centroFacturasEscapar(evento.detalle || "Sin observacion") + '</span></span><span class="cf-legajo-trace__meta">' + centroFacturasFecha(evento.fecha_hora, true) + '<br>' + centroFacturasEscapar(evento.usuario_actor || "") + '</span></li>';
        }).join("");
        contenido = '<section class="cf-legajo-detail__panel cf-legajo-trace-panel"><ul class="cf-legajo-trace">' + (timeline || '<li class="cf-legajo-empty-trace">Todavia no hay eventos documentales.</li>') + '</ul>'
            + '<div class="cf-legajo-detail__pagination"><button type="button" onclick="centroFacturasPaginaDetalleLegajo(-1)" ' + (pagina < 1 ? 'disabled' : '') + '>Anterior</button><span>' + (pagina + 1) + ' de ' + paginas + '</span><button type="button" onclick="centroFacturasPaginaDetalleLegajo(1)" ' + (pagina >= paginas - 1 ? 'disabled' : '') + '>Siguiente</button></div></section>';
    }
    document.getElementById("centroFacturasDetalleContenido").innerHTML = '<div class="cf-legajo-detail">' + cabecera + navegacion + contenido + '</div>';
}

function centroFacturasPrepararCambioDocumentoLegajo(codVenta, tipo, accion) {
    var titulos = { confirmar_copia: "Confirmar copia física", marcar_pendiente: "Marcar documento pendiente", observar: "Observar documento" };
    var ayudas = {
        confirmar_copia: "Confirme únicamente si la copia en papel correspondiente a esta venta está físicamente en la sucursal.",
        marcar_pendiente: "La copia dejará de estar disponible para nuevos lotes. Indique por qué debe volver a estado pendiente.",
        observar: "La observación impedirá incluir el legajo en un envío hasta que se regularice."
    };
    var html = '<p class="cf-step-help">' + centroFacturasEscapar(ayudas[accion] || "Revise la acción.") + '</p><label class="cf-field-label">Observación ' + (accion === "confirmar_copia" ? "(opcional)" : "obligatoria") + '</label><textarea id="cfLegajoDocumentoObservacion" rows="3" maxlength="3000"></textarea><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo(titulos[accion] || "Actualizar documento", "Control del Legajo #" + codVenta, html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasGuardarDocumentoLegajo(' + Number(codVenta) + ',\'' + tipo + '\',\'' + accion + '\')">Confirmar</button>');
}

function centroFacturasGuardarDocumentoLegajo(codVenta, tipo, accion) {
    var observaciones = (document.getElementById("cfLegajoDocumentoObservacion") || {}).value || "";
    var error = document.getElementById("cfDialogError");
    if (accion !== "confirmar_copia" && !observaciones.trim()) { if (error) { error.textContent = "Describa el motivo u observación."; } return; }
    centroFacturasSolicitar("guardarDocumentoLegajo", { cod_venta: codVenta, tipo_documento: tipo, accion_documento: accion, observaciones: observaciones }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo actualizar el documento."; } return; }
        centroFacturasCerrarDialogo();
        centroFacturasAviso("Documento actualizado sin modificar la venta ni sus archivos históricos.", "success");
        centroFacturasAbrirLegajo(codVenta);
        centroFacturasCargarListado();
    });
}

function centroFacturasAbrirNuevaSolicitudPagare(codVenta, idDocumento) {
    var datosLegajo = centroFacturasEstado.detalleLegajoDatos || {};
    var venta = datosLegajo.venta || datosLegajo.legajo || {};
    var codigo = centroFacturasCodigoDocumentoLegajo(codVenta, "pagare");
    var html = '<p class="cf-step-help">La solicitud no retira el pagar&eacute; de su ubicaci&oacute;n. Requiere aprobaci&oacute;n administrativa y, si est&aacute; en tr&aacute;nsito, esperar&aacute; su recepci&oacute;n.</p>'
        + '<div class="cf-detail-summary"><div><span>Documento</span><b>' + centroFacturasEscapar(codigo) + '</b></div><div><span>Paciente</span><b>' + centroFacturasEscapar(venta.titular || "Cliente") + '</b></div><div><span>Ubicaci&oacute;n</span><b>' + centroFacturasEscapar(((centroFacturasDocumentosLegajo({ documentos: datosLegajo.documentos || venta.documentos || {} }).pagare || {}).ubicacion_fisica) || venta.nombre_local || "Pendiente") + '</b></div></div>'
        + '<div class="cf-grid"><label>Solicitante<input id="cfPagareSolicitanteNombre" maxlength="150" value="' + centroFacturasEscapar(venta.titular || "") + '"></label><label>Documento del solicitante<input id="cfPagareSolicitanteDocumento" maxlength="45" value="' + centroFacturasEscapar(venta.documento || "") + '"></label>'
        + '<label class="cf-span-2">Motivo de la solicitud<textarea id="cfPagareMotivoSolicitud" rows="3" maxlength="1000" placeholder="Explique por qu&eacute; el cliente solicita la devoluci&oacute;n"></textarea></label></div><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo("Solicitar devolucion de pagare", codigo, html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasConfirmarNuevaSolicitudPagare(' + Number(codVenta) + ',' + Number(idDocumento) + ')">Registrar solicitud</button>');
}

function centroFacturasConfirmarNuevaSolicitudPagare(codVenta, idDocumento) {
    var nombre = (document.getElementById("cfPagareSolicitanteNombre") || {}).value || "";
    var documento = (document.getElementById("cfPagareSolicitanteDocumento") || {}).value || "";
    var motivo = (document.getElementById("cfPagareMotivoSolicitud") || {}).value || "";
    var error = document.getElementById("cfDialogError");
    if (!nombre.trim() || !documento.trim() || !motivo.trim()) { if (error) { error.textContent = "Complete solicitante, documento y motivo."; } return; }
    centroFacturasSolicitar("crearSolicitudPagare", { cod_venta: codVenta, datos: { solicitante_nombre: nombre, solicitante_documento: documento, motivo_solicitud: motivo } }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo registrar la solicitud."; } return; }
        centroFacturasCerrarDialogo();
        centroFacturasAviso("Solicitud registrada para aprobacion administrativa.", "success");
        centroFacturasCambiarVistaLegajos("solicitudes");
        centroFacturasAbrirSolicitudPagare(respuesta.id_solicitud);
    });
}

function centroFacturasAbrirSolicitudPagare(idSolicitud) {
    idSolicitud = Number(idSolicitud || 0);
    if (!idSolicitud || !centroFacturasPuedeVerSolicitudesPagare()) { return; }
    centroFacturasSolicitar("detalleSolicitudPagare", { id_solicitud: idSolicitud }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo abrir la solicitud.", "error"); return; }
        centroFacturasEstado.detalleSolicitudPagare = idSolicitud;
        centroFacturasEstado.detalleSolicitudPagina = 0;
        centroFacturasEstado.detalleSolicitudDatos = respuesta;
        centroFacturasConfigurarDrawer("solicitud_pagare");
        centroFacturasRenderDetalleSolicitudPagare();
        document.getElementById("centroFacturasDetalle").hidden = false;
    });
}

function centroFacturasPaginaSolicitudPagare(direccion) {
    var datos = centroFacturasEstado.detalleSolicitudDatos || {};
    var paginas = Math.max(1, Math.ceil((datos.eventos || []).length / 4));
    centroFacturasEstado.detalleSolicitudPagina = Math.max(0, Math.min(paginas - 1, centroFacturasEstado.detalleSolicitudPagina + Number(direccion || 0)));
    centroFacturasRenderDetalleSolicitudPagare();
}

function centroFacturasRenderDetalleSolicitudPagare() {
    var datos = centroFacturasEstado.detalleSolicitudDatos || {};
    var solicitud = datos.solicitud || datos.registro || {};
    var eventos = datos.eventos || [];
    var estado = centroFacturasEstadoSolicitudPagare(solicitud.estado);
    var id = Number(solicitud.id_solicitud || centroFacturasEstado.detalleSolicitudPagare || 0);
    var pagina = centroFacturasEstado.detalleSolicitudPagina;
    var paginas = Math.max(1, Math.ceil(eventos.length / 4));
    var visibles = eventos.slice(pagina * 4, pagina * 4 + 4);
    var codigoDocumento = solicitud.codigo_documento || centroFacturasCodigoDocumentoLegajo(solicitud.cod_ventaFK || solicitud.cod_venta, "pagare");
    document.getElementById("centroFacturasDetalleTitulo").textContent = solicitud.codigo_solicitud || ("Solicitud #" + id);
    var timeline = visibles.map(function (evento) {
        return '<li class="cf-legajo-trace__item"><span class="cf-legajo-trace__icon"><i class="fa-solid fa-circle"></i></span><span class="cf-legajo-trace__body"><b>' + centroFacturasEscapar(String(evento.accion || evento.tipo_evento || "evento").replace(/_/g, " ")) + '</b><span title="' + centroFacturasEscapar(evento.detalle || "") + '">' + centroFacturasEscapar(evento.detalle || "Sin observacion") + '</span></span><span class="cf-legajo-trace__meta">' + centroFacturasFecha(evento.fecha_hora, true) + '<br>' + centroFacturasEscapar(evento.usuario_actor || "") + '</span></li>';
    }).join("");
    var acciones = centroFacturasAccionesSolicitudPagare(solicitud, false);
    if (solicitud.evidencia_disponible || solicitud.tiene_evidencia || solicitud.evidencia_nombre_original) {
        acciones += ' <button type="button" onclick="centroFacturasDescargarEvidenciaPagare(' + id + ')"><i class="fa-solid fa-file-shield"></i> Ver constancia firmada</button>';
    }
    var html = '<div class="cf-legajo-detail cf-pagare-request-detail"><div class="cf-legajo-detail__summary"><div><span>Pagar&eacute;</span><b>' + centroFacturasEscapar(codigoDocumento) + '</b></div><div><span>Paciente</span><b>' + centroFacturasEscapar(solicitud.titular || solicitud.paciente || "—") + '</b></div><div><span>Estado</span>' + centroFacturasBadge(estado.texto, estado.tipo) + '</div><div><span>Ubicaci&oacute;n actual</span><b>' + centroFacturasEscapar(centroFacturasUbicacionPagare(solicitud)) + '</b></div></div>'
        + '<section class="cf-legajo-detail__panel"><div class="cf-legajo-data-grid"><div><span>Solicitante</span><b>' + centroFacturasEscapar(solicitud.solicitante_nombre || "—") + '</b><small>' + centroFacturasEscapar(solicitud.solicitante_documento || "") + '</small></div><div><span>Fecha</span><b>' + centroFacturasFecha(solicitud.fecha_solicitud, true) + '</b></div><div><span>Motivo</span><b class="cf-pagare-request-motive" title="' + centroFacturasEscapar(solicitud.motivo_solicitud || "") + '" aria-label="' + centroFacturasEscapar(solicitud.motivo_solicitud || "Sin motivo") + '">' + centroFacturasEscapar(solicitud.motivo_solicitud || "—") + '</b></div><div><span>&Uacute;ltimo lote</span><b>' + centroFacturasEscapar(solicitud.codigo_lote_actual || ((solicitud.lote_actual || {}).codigo_lote) || solicitud.codigo_lote_snapshot || "Sin lote") + '</b></div>'
        + '<div><span>Aprobaci&oacute;n</span><b>' + centroFacturasEscapar(solicitud.usuario_aprueba || solicitud.usuario_aprobacion || solicitud.usuario_rechaza || solicitud.usuario_rechazo || "Pendiente") + '</b></div><div><span>Entrega</span><b>' + centroFacturasEscapar(solicitud.receptor_nombre || "Pendiente") + '</b></div></div>'
        + '<div class="cf-legajo-detail__actions">' + (acciones || '<span class="cf-muted">Solicitud cerrada, disponible solo para consulta.</span>') + '</div></section>'
        + '<section class="cf-legajo-detail__panel cf-pagare-request-trace"><h4>Trazabilidad</h4><ul class="cf-legajo-trace">' + (timeline || '<li class="cf-legajo-empty-trace">Sin eventos.</li>') + '</ul><div class="cf-legajo-detail__pagination"><button type="button" onclick="centroFacturasPaginaSolicitudPagare(-1)" ' + (pagina < 1 ? 'disabled' : '') + '>Anterior</button><span>' + (pagina + 1) + ' de ' + paginas + '</span><button type="button" onclick="centroFacturasPaginaSolicitudPagare(1)" ' + (pagina >= paginas - 1 ? 'disabled' : '') + '>Siguiente</button></div></section></div>';
    document.getElementById("centroFacturasDetalleContenido").innerHTML = html;
}

function centroFacturasPrepararResolucionPagare(idSolicitud, accion) {
    var aprobar = accion === "aprobar";
    var datos = centroFacturasEstado.detalleSolicitudDatos || {};
    var solicitud = datos.solicitud || datos.registro || {};
    var motivo = solicitud.motivo_solicitud || "Sin motivo informado";
    var html = '<div class="cf-pagare-resolution-context"><span>Motivo de la solicitud</span><p>' + centroFacturasEscapar(motivo) + '</p></div><p class="cf-step-help">' + (aprobar ? 'Si el pagar&eacute; est&aacute; en tr&aacute;nsito, quedar&aacute; aprobado pero esperando su recepci&oacute;n.' : 'El rechazo cerrar&aacute; la solicitud sin modificar el pagar&eacute; ni su ubicaci&oacute;n.') + '</p><label class="cf-field-label">Observaci&oacute;n ' + (aprobar ? '(opcional)' : 'obligatoria') + '</label><textarea id="cfPagareResolucion" rows="3" maxlength="1000"></textarea><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo(aprobar ? "Aprobar solicitud" : "Rechazar solicitud", "Control administrativo", html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button ' + (aprobar ? 'cf-button--primary' : 'cf-button--danger') + '" onclick="centroFacturasResolverSolicitudPagare(' + Number(idSolicitud) + ',\'' + accion + '\')">Confirmar</button>');
}

function centroFacturasResolverSolicitudPagare(idSolicitud, accion) {
    var observacion = (document.getElementById("cfPagareResolucion") || {}).value || "";
    var error = document.getElementById("cfDialogError");
    if (accion === "rechazar" && !observacion.trim()) { if (error) { error.textContent = "Indique el motivo del rechazo."; } return; }
    centroFacturasSolicitar(accion === "aprobar" ? "aprobarSolicitudPagare" : "rechazarSolicitudPagare", { id_solicitud: idSolicitud, observacion: observacion }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo resolver la solicitud."; } return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Resolucion administrativa registrada.", "success"); centroFacturasAbrirSolicitudPagare(idSolicitud); centroFacturasCargarListado();
    });
}

function centroFacturasPrepararPagare(idSolicitud) {
    centroFacturasSolicitar("prepararSolicitudPagare", { id_solicitud: idSolicitud }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { centroFacturasAviso((respuesta && respuesta.mensaje) || "El pagare todavia no puede prepararse.", "error"); return; }
        centroFacturasAviso("Pagare preparado para entrega controlada.", "success"); centroFacturasAbrirSolicitudPagare(idSolicitud); centroFacturasCargarListado();
    });
}

function centroFacturasPrepararEntregaPagare(idSolicitud) {
    var html = '<p class="cf-step-help">La entrega requiere identificar a quien recibe y adjuntar una constancia firmada. El pagar&eacute; quedar&aacute; como <b>Devuelto al cliente</b>.</p><div class="cf-grid"><label>Nombre de quien recibe<input id="cfPagareReceptorNombre" maxlength="150"></label><label>Documento<input id="cfPagareReceptorDocumento" maxlength="45"></label><label>Relaci&oacute;n con el cliente<select id="cfPagareReceptorRelacion"><option value="cliente">Cliente titular</option><option value="autorizado">Persona autorizada</option><option value="representante">Representante legal</option></select></label><label>Constancia firmada<input id="cfPagareEvidencia" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif"></label><label class="cf-span-2">Observaci&oacute;n de entrega<textarea id="cfPagareObservacionEntrega" rows="2" maxlength="1000"></textarea></label></div><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo("Registrar entrega del pagare", "Ultimo paso · Evidencia obligatoria", html,
        '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasEntregarPagare(' + Number(idSolicitud) + ')">Confirmar entrega</button>');
}

function centroFacturasEntregarPagare(idSolicitud) {
    var nombre = (document.getElementById("cfPagareReceptorNombre") || {}).value || "";
    var documento = (document.getElementById("cfPagareReceptorDocumento") || {}).value || "";
    var relacion = (document.getElementById("cfPagareReceptorRelacion") || {}).value || "";
    var observacion = (document.getElementById("cfPagareObservacionEntrega") || {}).value || "";
    var input = document.getElementById("cfPagareEvidencia");
    var archivos = input ? Array.prototype.slice.call(input.files || []) : [];
    var error = document.getElementById("cfDialogError");
    if (!nombre.trim() || !documento.trim() || archivos.length !== 1) { if (error) { error.textContent = "Complete receptor, documento y una constancia firmada."; } return; }
    centroFacturasSolicitar("entregarSolicitudPagare", { id_solicitud: idSolicitud, datos: { receptor_nombre: nombre, receptor_documento: documento, receptor_relacion: relacion, observacion_entrega: observacion } }, { archivos: archivos }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo registrar la entrega."; } return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Entrega registrada con constancia firmada y trazabilidad.", "success"); centroFacturasAbrirSolicitudPagare(idSolicitud); centroFacturasCargarListado();
    });
}

function centroFacturasPrepararCancelacionPagare(idSolicitud) {
    var html = '<p class="cf-step-help">La cancelaci&oacute;n no eliminar&aacute; la solicitud ni sus eventos.</p><label class="cf-field-label">Motivo obligatorio</label><textarea id="cfPagareMotivoCancelacion" rows="3" maxlength="1000"></textarea><div id="cfDialogError" class="cf-form-error"></div>';
    centroFacturasAbrirDialogo("Cancelar solicitud", "Accion trazable", html, '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Volver</button><button type="button" class="cf-button cf-button--danger" onclick="centroFacturasCancelarSolicitudPagare(' + Number(idSolicitud) + ')">Cancelar solicitud</button>');
}

function centroFacturasCancelarSolicitudPagare(idSolicitud) {
    var motivo = (document.getElementById("cfPagareMotivoCancelacion") || {}).value || "";
    var error = document.getElementById("cfDialogError");
    if (!motivo.trim()) { if (error) { error.textContent = "Indique el motivo de cancelacion."; } return; }
    centroFacturasSolicitar("cancelarSolicitudPagare", { id_solicitud: idSolicitud, motivo: motivo }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo cancelar."; } return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Solicitud cancelada sin borrar su historial.", "success"); centroFacturasAbrirSolicitudPagare(idSolicitud); centroFacturasCargarListado();
    });
}

function centroFacturasDescargarEvidenciaPagare(idSolicitud) {
    var formulario = document.createElement("form");
    formulario.method = "post"; formulario.action = "../php_system/abmCentroFacturas.php"; formulario.target = "_blank"; formulario.hidden = true;
    var campos = { useru: typeof userid !== "undefined" ? userid : "", passu: typeof passuser !== "undefined" ? passuser : "", navegador: typeof navegador !== "undefined" ? navegador : "", funt: "descargarEvidenciaSolicitudPagare", id_solicitud: Number(idSolicitud || 0) };
    Object.keys(campos).forEach(function (clave) { var input = document.createElement("input"); input.name = clave; input.value = campos[clave]; formulario.appendChild(input); });
    document.body.appendChild(formulario); formulario.submit(); window.setTimeout(function () { if (formulario.parentNode) { formulario.parentNode.removeChild(formulario); } }, 1000);
}

function centroFacturasAbrirLoteLegajo(idLote) {
    idLote = Number(idLote || 0);
    if (!idLote || !centroFacturasPuedeVerLegajos()) { return; }
    centroFacturasSolicitar("detalleLoteLegajo", { id_lote: idLote }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo abrir el envío.", "error"); return; }
        centroFacturasEstado.detalleLoteLegajo = idLote;
        centroFacturasConfigurarDrawer("lote_legajo");
        centroFacturasRenderDetalleLoteLegajo(respuesta);
        document.getElementById("centroFacturasDetalle").hidden = false;
    });
}

function centroFacturasRenderDetalleLoteLegajo(datos) {
    var lote = datos.lote || {};
    var documentos = datos.documentos || datos.detalles || [];
    var eventos = datos.eventos || [];
    document.getElementById("centroFacturasDetalleTitulo").textContent = lote.codigo_lote || "Envío interno";
    var grupos = {};
    documentos.forEach(function (documento) {
        var venta = Number(documento.cod_ventaFK || documento.cod_venta || 0);
        if (!grupos[venta]) { grupos[venta] = []; }
        grupos[venta].push(documento);
    });
    var manifiesto = Object.keys(grupos).map(function (venta) {
        var items = grupos[venta];
        var importeVenta = Number((items[0] || {}).importe_venta || 0);
        return '<tr><td><strong>Legajo #' + Number(venta) + '</strong><small>' + centroFacturasEscapar((items[0] || {}).titular || "") + '</small></td><td><strong>Gs. ' + centroFacturasNumero(importeVenta) + '</strong></td><td>' + items.map(function (d) { return centroFacturasRenderDocumentoLegajo(d, d.tipo_documento); }).join(" ") + '</td><td>' + items.map(function (d) { return centroFacturasBadge(String(d.estado_lote || d.estado_detalle || d.estado || "incluido").replace(/_/g, " "), ["faltante", "observado"].indexOf(d.estado_lote || d.estado_detalle || d.estado) >= 0 ? "danger" : ((d.estado_lote || d.estado_detalle || d.estado) === "recibido" ? "success" : "info")); }).join(" ") + '</td></tr>';
    }).join("");
    var timeline = eventos.map(function (evento) {
        return '<li class="cf-legajo-trace__item"><span class="cf-legajo-trace__icon"><i class="fa-solid fa-circle"></i></span><span class="cf-legajo-trace__body"><b>' + centroFacturasEscapar(String(evento.tipo_evento || "evento").replace(/_/g, " ")) + '</b>' + centroFacturasEscapar(evento.detalle || evento.usuario_actor || "") + '</span><span class="cf-legajo-trace__meta">' + centroFacturasFecha(evento.fecha_hora, true) + '<br>' + centroFacturasEscapar(evento.usuario_actor || "") + '</span></li>';
    }).join("");
    var estado = String(lote.estado || "borrador");
    var usuarioContexto = Number((((centroFacturasEstado.contexto || {}).usuario || {}).cod_usuario) || 0);
    var esTransportistaAsignado = usuarioContexto > 0 && usuarioContexto === Number(lote.cod_usuario_transportistaFK || 0);
    var html = '<section class="cf-card cf-legajo-manifest-header"><span class="centro-facturas-eyebrow">ENVÍO INTERNO DE DOCUMENTOS</span><h4>' + centroFacturasEscapar(lote.codigo_lote || "") + '</h4><p><b>Origen:</b> ' + centroFacturasEscapar(lote.nombre_local_origen || "—") + '<br><b>Destino:</b> ' + centroFacturasEscapar(lote.nombre_local_destino || lote.destino_snapshot || "—") + '<br><b>Preparado por:</b> ' + centroFacturasEscapar(lote.usuario_creador || "—") + ' · ' + centroFacturasFecha(lote.fecha_creacion, true) + '<br><b>Transportista asignado:</b> ' + centroFacturasEscapar(lote.usuario_transportista || "—") + '<br><b>Custodia aceptada por:</b> ' + centroFacturasEscapar(lote.usuario_custodia || "Pendiente") + (lote.fecha_aceptacion_custodia ? ' · ' + centroFacturasFecha(lote.fecha_aceptacion_custodia, true) : '') + '<br><b>Recibido por:</b> ' + centroFacturasEscapar(lote.usuario_recepcion || "Pendiente") + '</p><div class="cf-card-actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasImprimirLote()"><i class="fa-solid fa-print"></i> Imprimir manifiesto</button>';
    if (estado === "borrador" && (centroFacturasPermiso("ENVIARLOTELEGAJOS") || centroFacturasPermiso("ADMINCENTROFACTURAS"))) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasPrepararEnvioLoteLegajo(' + Number(lote.id_lote) + ')">Entregar al transportista</button>'; }
    if (estado === "pendiente_custodia" && esTransportistaAsignado && (centroFacturasPermiso("ENVIARLOTELEGAJOS") || centroFacturasPermiso("ADMINCENTROFACTURAS"))) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasAceptarCustodiaLoteLegajo(' + Number(lote.id_lote) + ')">Aceptar custodia</button>'; }
    if (["en_transito", "recibido_parcial", "observado"].indexOf(estado) >= 0 && (centroFacturasPermiso("RECIBIRLOTELEGAJOS") || centroFacturasPermiso("ADMINCENTROFACTURAS"))) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasPrepararRecepcionLoteLegajo(' + Number(lote.id_lote) + ')">Registrar recepción</button>'; }
    if (estado !== "anulado" && centroFacturasPuedeGestionarLotesLegajos()) { html += '<button type="button" class="cf-button cf-button--danger" onclick="centroFacturasPrepararAnulacionLoteLegajo(' + Number(lote.id_lote) + ')">Anular</button>'; }
    html += '</div></section><section class="cf-card"><h4>Manifiesto agrupado por legajo</h4><div class="centro-facturas-table-wrap"><table class="centro-facturas-table"><thead><tr><th>Legajo</th><th>Importe de venta</th><th>Documentos</th><th>Situación</th></tr></thead><tbody>' + manifiesto + '</tbody></table></div></section>'
        + '<section class="cf-card"><h4>Trazabilidad de custodia</h4><ul class="cf-legajo-trace">' + (timeline || '<li class="cf-legajo-trace__item"><span class="cf-legajo-trace__icon"><i class="fa-solid fa-circle"></i></span><span class="cf-legajo-trace__body"><b>Sin eventos</b></span></li>') + '</ul></section>';
    document.getElementById("centroFacturasDetalleContenido").innerHTML = html;
}

function centroFacturasPrepararEnvioLoteLegajo(idLote) {
    centroFacturasAbrirDialogo("Entregar lote al transportista", "Paso 2 de 3 · Entrega", '<p class="cf-step-help">El lote quedará pendiente de aceptación. No figurará “En tránsito” hasta que el transportista asignado confirme la custodia.</p><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasEnviarLoteLegajo(' + Number(idLote) + ')">Confirmar entrega</button>');
}

function centroFacturasEnviarLoteLegajo(idLote) {
    centroFacturasSolicitar("enviarLoteLegajo", { id_lote: idLote }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { document.getElementById("cfDialogError").textContent = (respuesta && respuesta.mensaje) || "No se pudo entregar el lote."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Entrega registrada. Falta la aceptación de custodia del transportista.", "success"); centroFacturasAbrirLoteLegajo(idLote); centroFacturasCargarListado();
    });
}

function centroFacturasAceptarCustodiaLoteLegajo(idLote) {
    centroFacturasSolicitar("aceptarCustodiaLoteLegajo", { id_lote: idLote }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { centroFacturasAviso((respuesta && respuesta.mensaje) || "No se pudo aceptar la custodia.", "error"); return; }
        centroFacturasAviso("Custodia aceptada. El envío ahora figura En tránsito.", "success"); centroFacturasAbrirLoteLegajo(idLote); centroFacturasCargarListado();
    });
}

function centroFacturasPrepararRecepcionLoteLegajo(idLote) {
    centroFacturasSolicitar("detalleLoteLegajo", { id_lote: idLote }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { return; }
        var items = (respuesta.documentos || respuesta.detalles || []).filter(function (d) { return String(d.estado_lote || d.estado_detalle || d.estado) !== "retirado"; }).map(function (d) {
            var estado = String(d.estado_lote || d.estado_detalle || d.estado || "en_transito");
            var seleccionado = estado === "faltante" ? "faltante" : (estado === "observado" ? "observado" : "recibido");
            var visual = centroFacturasEstadoDocumentoLegajo(d, d.tipo_documento);
            var codigoDocumento = d.codigo_documento || centroFacturasCodigoDocumentoLegajo(d.cod_ventaFK || d.cod_venta, d.tipo_documento);
            return '<div class="cf-reception-item" data-cf-legajo-recepcion="' + Number(d.id_documento) + '"><span><b>' + centroFacturasEscapar(codigoDocumento) + ' · ' + centroFacturasEscapar(visual.nombre) + '</b><br>' + centroFacturasEscapar(d.titular || "") + '</span><select class="cfLegajoRecepcionEstado"><option value="recibido" ' + (seleccionado === "recibido" ? "selected" : "") + '>Recibido</option><option value="faltante" ' + (seleccionado === "faltante" ? "selected" : "") + '>Faltante</option><option value="observado" ' + (seleccionado === "observado" ? "selected" : "") + '>Observado</option></select><input class="cfLegajoRecepcionObservacion" maxlength="255" placeholder="Observación si falta o tiene diferencias" value="' + centroFacturasEscapar(d.observacion_lote || "") + '"></div>';
        }).join("");
        var html = '<p class="cf-step-help">Revise cada copia física. Los faltantes no serán ubicados en el destino y quedarán visibles en la trazabilidad.</p><div class="cf-reception-list">' + items + '</div><label class="cf-field-label">Ubicación física de lo recibido</label><input id="cfLegajoRecepcionUbicacion" maxlength="255" placeholder="Ej. Archivo central, estante, caja o carpeta"><div id="cfDialogError" class="cf-form-error"></div>';
        centroFacturasAbrirDialogo("Recibir " + respuesta.lote.codigo_lote, "Paso 3 de 3 · Recepción", html, '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasRecibirLoteLegajo(' + Number(idLote) + ')">Guardar recepción</button>');
    });
}

function centroFacturasRecibirLoteLegajo(idLote) {
    var recepciones = Array.prototype.map.call(document.querySelectorAll("[data-cf-legajo-recepcion]"), function (fila) {
        return { id_documento: Number(fila.getAttribute("data-cf-legajo-recepcion")), estado: fila.querySelector(".cfLegajoRecepcionEstado").value, observacion: fila.querySelector(".cfLegajoRecepcionObservacion").value };
    });
    var error = document.getElementById("cfDialogError");
    for (var i = 0; i < recepciones.length; i++) {
        if (recepciones[i].estado !== "recibido" && !String(recepciones[i].observacion || "").trim()) { if (error) { error.textContent = "Describa cada documento faltante u observado."; } return; }
    }
    centroFacturasSolicitar("recibirLoteLegajo", { id_lote: idLote, recepciones: recepciones, datos: { ubicacion_fisica: (document.getElementById("cfLegajoRecepcionUbicacion") || {}).value || "" } }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo guardar la recepción."; } return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Recepción registrada con control individual de documentos.", "success"); centroFacturasAbrirLoteLegajo(idLote); centroFacturasCargarListado();
    });
}

function centroFacturasPrepararAnulacionLoteLegajo(idLote) {
    centroFacturasAbrirDialogo("Anular lote de legajos", "Acción controlada", '<p class="cf-step-help">No se eliminarán ventas, documentos ni eventos de custodia.</p><label class="cf-field-label">Motivo obligatorio</label><textarea id="cfLegajoLoteMotivoAnulacion" rows="3" maxlength="255"></textarea><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--danger" onclick="centroFacturasAnularLoteLegajo(' + Number(idLote) + ')">Anular lote</button>');
}

function centroFacturasAnularLoteLegajo(idLote) {
    var motivo = (document.getElementById("cfLegajoLoteMotivoAnulacion") || {}).value || "";
    var error = document.getElementById("cfDialogError");
    if (!motivo.trim()) { if (error) { error.textContent = "Ingrese el motivo de anulación."; } return; }
    centroFacturasSolicitar("anularLoteLegajo", { id_lote: idLote, motivo: motivo }).done(function (respuesta) {
        if (!respuesta || !respuesta.ok) { if (error) { error.textContent = (respuesta && respuesta.mensaje) || "No se pudo anular el lote."; } return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Lote anulado sin borrar su trazabilidad.", "success"); centroFacturasAbrirLoteLegajo(idLote); centroFacturasCargarListado();
    });
}

function centroFacturasCerrarDetalle() {
    var detalle = document.getElementById("centroFacturasDetalle"); if (detalle) { detalle.hidden = true; }
    centroFacturasConfigurarDrawer("");
    centroFacturasEstado.detalleFactura = 0; centroFacturasEstado.detalleLote = 0;
    centroFacturasEstado.detalleLegajo = 0; centroFacturasEstado.detalleLoteLegajo = 0; centroFacturasEstado.detalleLegajoDatos = null;
    centroFacturasEstado.detalleSolicitudPagare = 0; centroFacturasEstado.detalleSolicitudDatos = null;
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
            centroFacturasConfigurarDrawer("factura");
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
        + '<label id="cfDetalleTipoGrupo" ' + (esRecibo ? "hidden" : "") + '>Tipo<select id="cfDetalleTipo" onchange="centroFacturasActualizarTipoDetalle()"><option value="proveedor" ' + (f.tipo_contraparte === "proveedor" ? "selected" : "") + '>Proveedor</option><option value="funcionario" ' + (f.tipo_contraparte === "funcionario" ? "selected" : "") + '>Funcionario</option><option value="otro" ' + (f.tipo_contraparte === "otro" ? "selected" : "") + '>Otro</option></select></label>'
        + '<label id="cfDetalleProveedorGrupo" ' + (esRecibo || f.tipo_contraparte !== "proveedor" ? "hidden" : "") + '>Proveedor<select id="cfDetalleProveedor"><option value="">No aplica</option>' + centroFacturasOpciones(c.proveedores, "cod_proveedor", "nombre_persona", f.cod_proveedorFK) + '</select></label>'
        + '<label id="cfDetalleFuncionarioGrupo" ' + (esRecibo || f.tipo_contraparte !== "funcionario" ? "hidden" : "") + '>Funcionario<select id="cfDetalleFuncionario"><option value="">No aplica</option>' + centroFacturasOpciones(c.funcionarios, "cod_usuario", "nombre_persona", f.cod_funcionarioFK) + '</select></label>'
        + '<label id="cfDetalleNombreGrupo" ' + (esRecibo || f.tipo_contraparte !== "otro" ? "hidden" : "") + '>Nombre / razon social<input id="cfDetalleNombre" value="' + centroFacturasEscapar(f.nombre_contraparte) + '"></label><label id="cfDetalleDocumentoGrupo" ' + (esRecibo ? "hidden" : "") + '>RUC / documento<input id="cfDetalleDocumento" value="' + centroFacturasEscapar(f.documento_contraparte) + '"></label>'
        + '<label><span id="cfDetalleNumeroEtiqueta">N.&ordm; ' + (esRecibo ? "recibo" : "factura") + ' (opcional)</span><input id="cfDetalleNumero" value="' + centroFacturasEscapar(f.numero_factura) + '"></label><label id="cfDetalleTimbradoGrupo" ' + (esRecibo ? "hidden" : "") + '>Timbrado<input id="cfDetalleTimbrado" value="' + centroFacturasEscapar(f.timbrado) + '"></label>'
        + '<label>Fecha emision<input id="cfDetalleFecha" type="date" value="' + centroFacturasEscapar(f.fecha_emision) + '"></label><label>Importe<input id="cfDetalleImporte" value="' + centroFacturasEscapar(f.importe_total) + '"></label>'
        + '<label class="cf-span-2">Concepto contable (opcional)<input id="cfDetalleConcepto" value="' + centroFacturasEscapar(f.concepto) + '"></label><label class="cf-span-2"><span id="cfDetalleObservacionesEtiqueta">' + (esRecibo ? "Descripción del recibo" : "Observaciones (opcional)") + '</span><textarea id="cfDetalleObservaciones">' + centroFacturasEscapar(f.observaciones) + '</textarea></label>'
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
    centroFacturasActualizarDocumentoDetalle();
}

function centroFacturasActualizarDocumentoDetalle() {
    var esRecibo = document.getElementById("cfDetalleTipoDocumento").value === "recibo";
    document.getElementById("cfDetalleNumeroEtiqueta").textContent = esRecibo ? "N.º recibo (opcional)" : "N.º factura (opcional)";
    document.getElementById("cfDetalleTimbradoGrupo").hidden = esRecibo;
    document.getElementById("cfDetalleObservacionesEtiqueta").textContent = esRecibo ? "Descripción del recibo" : "Observaciones (opcional)";
    centroFacturasActualizarTipoDetalle();
}

function centroFacturasActualizarTipoDetalle() {
    var esRecibo = document.getElementById("cfDetalleTipoDocumento").value === "recibo";
    var tipo = document.getElementById("cfDetalleTipo").value;
    document.getElementById("cfDetalleTipoGrupo").hidden = esRecibo;
    document.getElementById("cfDetalleProveedorGrupo").hidden = esRecibo || tipo !== "proveedor";
    document.getElementById("cfDetalleFuncionarioGrupo").hidden = esRecibo || tipo !== "funcionario";
    document.getElementById("cfDetalleNombreGrupo").hidden = esRecibo || tipo !== "otro";
    document.getElementById("cfDetalleDocumentoGrupo").hidden = esRecibo;
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
    var resumen = ids.reduce(function (acumulado, id) {
        var detalle = centroFacturasEstado.seleccionDetalle[id] || { tipo_documento: "factura", importe: 0 };
        if (detalle.tipo_documento === "recibo") {
            acumulado.recibos++;
            acumulado.importeRecibos += Number(detalle.importe || 0);
        } else {
            acumulado.facturas++;
            acumulado.importeFacturas += Number(detalle.importe || 0);
        }
        return acumulado;
    }, { facturas: 0, recibos: 0, importeFacturas: 0, importeRecibos: 0 });
    var html = '<p class="cf-step-help">Se creara un borrador con <b>' + ids.length + '</b> comprobantes. Podra revisarlo e imprimirlo antes de marcar la entrega.</p>'
        + '<div class="cf-detail-summary"><div><span>Documentos</span><b>' + ids.length + ' originales</b></div><div><span>Facturas</span><b>' + resumen.facturas + ' · Gs. ' + centroFacturasNumero(resumen.importeFacturas) + '</b></div><div><span>Recibos</span><b>' + resumen.recibos + ' · Gs. ' + centroFacturasNumero(resumen.importeRecibos) + '</b></div></div>'
        + '<label class="cf-field-label">Destino</label><input id="cfLoteDestino" value="Administracion central"><label class="cf-field-label">Responsable de entrega</label><select id="cfLoteResponsable"><option value="">Usuario actual</option>' + centroFacturasOpciones((centroFacturasEstado.contexto || {}).funcionarios, "cod_usuario", "nombre_persona", "") + '</select><label class="cf-field-label">Observaciones</label><textarea id="cfLoteObservaciones" rows="3"></textarea><div id="cfDialogError" class="cf-form-error"></div>';
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
        centroFacturasEstado.detalleLote = idLote; centroFacturasConfigurarDrawer("lote"); centroFacturasRenderDetalleLote(r); document.getElementById("centroFacturasDetalle").hidden = false;
    });
}

function centroFacturasRenderDetalleLote(datos) {
    var lote = datos.lote;
    document.getElementById("centroFacturasDetalleTitulo").textContent = lote.codigo_lote;
    var cantidadFacturas = 0;
    var cantidadRecibos = 0;
    var totalFacturas = 0;
    var totalRecibos = 0;
    var filas = (datos.facturas || []).map(function (f) {
        var esRecibo = f.tipo_documento === "recibo";
        var tipoDocumento = esRecibo ? "Recibo" : "Factura";
        if (f.estado_detalle_lote !== "retirada") {
            if (esRecibo) {
                cantidadRecibos++;
                totalRecibos += Number(f.importe_total || 0);
            } else {
                cantidadFacturas++;
                totalFacturas += Number(f.importe_total || 0);
            }
        }
        return '<tr><td><strong>' + tipoDocumento + ' #' + Number(f.id_factura) + '</strong><small>' + centroFacturasEscapar(f.contraparte_mostrar) + '</small></td><td>' + centroFacturasEscapar(f.numero_factura || "Sin numero") + '</td><td>Gs. ' + centroFacturasNumero(f.importe_total) + '</td><td>' + centroFacturasBadge(String(f.estado_detalle_lote).replace(/_/g, " "), f.estado_detalle_lote === "recibida" ? "success" : (f.estado_detalle_lote === "observada" || f.estado_detalle_lote === "faltante" ? "danger" : "info")) + '</td><td><button type="button" onclick="centroFacturasAbrirDetalle(' + Number(f.id_factura) + ')">Ver</button>' + (lote.estado === "borrador" && centroFacturasPermiso("GESTIONARLOTESFACTURAS") && f.estado_detalle_lote !== "retirada" ? ' <button type="button" onclick="centroFacturasRetirarDeLote(' + Number(lote.id_lote) + ',' + Number(f.id_factura) + ')">Retirar</button>' : "") + '</td></tr>';
    }).join("");
    var html = '<div class="cf-detail-summary"><div><span>Estado</span>' + centroFacturasBadge(String(lote.estado).replace(/_/g, " "), lote.estado === "recibido" ? "success" : (lote.estado === "anulado" ? "danger" : "info")) + '</div><div><span>Facturas</span><b>' + cantidadFacturas + ' · Gs. ' + centroFacturasNumero(totalFacturas) + '</b></div><div><span>Recibos</span><b>' + cantidadRecibos + ' · Gs. ' + centroFacturasNumero(totalRecibos) + '</b></div></div>'
        + '<section class="cf-card"><h4>Entrega</h4><p><b>Origen:</b> ' + centroFacturasEscapar(lote.nombre_local) + '<br><b>Destino:</b> ' + centroFacturasEscapar(lote.destino) + '<br><b>Responsable:</b> ' + centroFacturasEscapar(lote.usuario_entrega || "Pendiente") + '<br><b>Creado:</b> ' + centroFacturasFecha(lote.fecha_creacion, true) + (lote.fecha_envio ? '<br><b>Enviado:</b> ' + centroFacturasFecha(lote.fecha_envio, true) : "") + '</p><div class="cf-card-actions"><button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasImprimirLote()"><i class="fa-solid fa-print"></i> Imprimir resumen</button>';
    if (lote.estado === "borrador" && centroFacturasPermiso("GESTIONARLOTESFACTURAS") && centroFacturasPermiso("ENVIARORIGINALFACTURA")) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasPrepararEnvioLote(' + Number(lote.id_lote) + ')">Marcar lote enviado</button>'; }
    if (["enviado", "recibido_parcial", "observado"].indexOf(lote.estado) >= 0 && centroFacturasPermiso("RECIBIRORIGINALFACTURA")) { html += '<button type="button" class="cf-button cf-button--primary" onclick="centroFacturasPrepararRecepcionLote(' + Number(lote.id_lote) + ')">Recibir lote</button>'; }
    if (lote.estado !== "anulado" && centroFacturasPermiso("GESTIONARLOTESFACTURAS")) { html += '<button type="button" class="cf-button cf-button--danger" onclick="centroFacturasPrepararAnulacionLote(' + Number(lote.id_lote) + ')">Anular</button>'; }
    html += '</div></section><section class="cf-card"><h4>Documentos incluidos</h4><div class="centro-facturas-table-wrap"><table class="centro-facturas-table"><thead><tr><th>Documento</th><th>Numero</th><th>Importe</th><th>Recepcion</th><th></th></tr></thead><tbody>' + filas + '</tbody></table></div></section>';
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
    centroFacturasAbrirDialogo("Confirmar entrega del lote", "Paso 2 de 2 · Enviar", '<p class="cf-step-help">Al confirmar, todos los originales activos del lote quedaran como <b>Enviado a central</b>.</p><label class="cf-field-label">Responsable que transporta el lote</label><select id="cfEnvioLoteResponsable"><option value="">Usuario actual</option>' + centroFacturasOpciones((centroFacturasEstado.contexto || {}).funcionarios, "cod_usuario", "nombre_persona", "") + '</select><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--primary" onclick="centroFacturasEnviarLote(' + idLote + ')">Confirmar envio</button>');
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
            var tipoDocumento = f.tipo_documento === "recibo" ? "Recibo" : "Factura";
            return '<div class="cf-reception-item" data-cf-recepcion="' + Number(f.id_factura) + '"><span><b>' + tipoDocumento + ' #' + Number(f.id_factura) + ' · ' + centroFacturasEscapar(f.numero_factura || "Sin numero") + '</b><br>' + centroFacturasEscapar(f.contraparte_mostrar) + '</span><select class="cfRecepcionEstado"><option value="recibida" ' + (estadoActual === "recibida" ? "selected" : "") + '>Original recibido</option><option value="faltante" ' + (estadoActual === "faltante" ? "selected" : "") + '>Original faltante</option><option value="observada" ' + (estadoActual === "observada" ? "selected" : "") + '>Original observado</option></select><input class="cfRecepcionObservacion" placeholder="Observacion si falta o tiene diferencias" value="' + centroFacturasEscapar(f.observacion_lote) + '"></div>';
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
    centroFacturasAbrirDialogo("Anular lote", "Accion controlada", '<p class="cf-step-help">No se eliminaran documentos ni archivos. El lote quedara anulado y auditado.</p><label class="cf-field-label">Motivo obligatorio</label><textarea id="cfAnularLoteMotivo" rows="3"></textarea><div id="cfDialogError" class="cf-form-error"></div>', '<button type="button" class="cf-button cf-button--secondary" onclick="centroFacturasCerrarDialogo()">Cancelar</button><button type="button" class="cf-button cf-button--danger" onclick="centroFacturasAnularLote(' + idLote + ')">Anular lote</button>');
}

function centroFacturasAnularLote(idLote) {
    centroFacturasSolicitar("anularLote", { id_lote: idLote, motivo: document.getElementById("cfAnularLoteMotivo").value }).done(function (r) {
        if (!r || !r.ok) { document.getElementById("cfDialogError").textContent = (r && r.mensaje) || "No se pudo anular."; return; }
        centroFacturasCerrarDialogo(); centroFacturasAviso("Lote anulado sin eliminar sus documentos.", "success"); centroFacturasAbrirLote(idLote); centroFacturasRecargar();
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

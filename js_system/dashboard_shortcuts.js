var DASHBOARD_SHORTCUT_MAX = 20;
var dashboardShortcutCatalog = [];
var dashboardShortcutCatalogByKey = {};
var dashboardShortcutSelectedKeys = [];
var dashboardShortcutTemplatesReady = false;
var dashboardShortcutCatalogLoaded = false;
var dashboardShortcutCatalogLoading = false;
var dashboardShortcutCatalogCallbacks = [];
var dashboardShortcutCatalogLastError = "";
var dashboardShortcutDragSource = "";
var dashboardShortcutPointerDrag = null;

var DASHBOARD_SHORTCUT_DEFAULT_KEYS = [
	"cargar_compras",
	"cuentas_a_cobrar",
	"cobrar_cuota",
	"cobros_realizados",
	"expediente_cliente",
	"historial_venta",
	"productos",
	"nueva_venta",
	"flujo_egreso_ingreso",
	"cerrar_caja",
	"hilos_interconsultas",
	"historial_presupuestos",
	"insumos",
	"migrar_caja",
	"recibir_caja",
	"diagrama_gant",
	"agenda_dia"
];

var DASHBOARD_ACCESS_REGISTRY = {
	cargar_compras: { sourceSelector: "#divMenuCompra1", permissionKey: "VERCARGADECOMPRAS" },
	cuentas_a_cobrar: { sourceSelector: "#divMenuCuentasCobar1", permissionKey: "VERCUENTASACOBRAR" },
	cobrar_cuota: { sourceSelector: "#divMenuCobrarCuota1", permissionKey: "VERCOBRARCUOTA" },
	cobros_realizados: { sourceSelector: "#divMenuCobrosRealizado1", permissionKey: "VERCOBROSREALIZADOS" },
	expediente_cliente: { sourceSelector: "#divMenuExpedienteCliente1", permissionKey: "VEREXPEDIENTEDELCLIENTE" },
	historial_venta: { sourceSelector: "#divMenuHistorialVenta1", permissionKey: "VERHISTORIALVENTA" },
	productos: { sourceSelector: "#divMenuAbmProductos1", permissionKey: "VERLISTADOPRODUCTOS" },
	nueva_venta: { sourceSelector: "#quickAccessSection #divMenuVenta", permissionKey: "VERVENTA" },
	flujo_egreso_ingreso: { sourceSelector: "#divMenuEgreso_Ingreso2", permissionKey: "VERLISTADOEGRESOINGRESO" },
	cerrar_caja: { sourceSelector: "#divMenuArqueo", permissionKey: "VERCERRARCAJA" },
	hilos_interconsultas: { sourceSelector: "#divMenuInterConsulta" },
	historial_presupuestos: { sourceSelector: "#divMenuPresupuestoProducto2" },
	insumos: { sourceSelector: "#divMenuInsumos" },
	migrar_caja: { sourceSelector: "#divMenuMigrarCaja", permissionKey: "VERMIGRARCAJA" },
	recibir_caja: { sourceSelector: "#divMenuRecibirCaja", permissionKey: "VERRECIBIRCAJA" },
	diagrama_gant: { sourceSelector: "#divMenuDiagramaGant" },
	agenda_dia: { sourceSelector: "#divMenuAgendaDia" },

	historial_clinico_evolucion: { sourceSelector: "#divMenuHistorialClinicoEvolucion" },
	sugerencias_calificaciones: { sourceSelector: "#divMenuSugerenciasyCalificaciones" },
	pagos_programados: { sourceSelector: "#divMenuProximosPagos", permissionKey: "VERPAGOPROGRAMADO" },
	cargar_tratamientos: { sourceSelector: "#divMenuPresupuestoProductoDoc" },
	historial_consulta: { sourceSelector: "#divMenuHistorialConsulta", permissionKey: "VERHISTORIALCONSULTA" },
	calendario: { sourceSelector: "#divMenuCalendario", permissionKey: "VERFORMULARIOCALENDARIO" },
	asignar_tareas: { sourceSelector: "#divMenuAsignarTareas", permissionKey: "VERASIGNARTAREASUSUARIO" },

	cargar_sueldo: { sourceSelector: "#divMenuAbmCargarSueldo", permissionKey: "VERCARGARSUELDO" },
	cuentas_a_pagar: { sourceSelector: "#divMenuCuentasPagar", permissionKey: "VERCUENTASAPAGAR" },
	consulta_cajas: { sourceSelector: "#divMenuConsultadeCaja", permissionKey: "VERCONSULTADECAJA" },
	conciliacion_ueno: { sourceSelector: "#divMenuConciliacionUeno", permissionKey: "VERCONCILIACIONUENO" },
	historial_compras: { sourceSelector: "#divMenuHistorialCompra", permissionKey: "VERHISTORIALCOMPRA" },
	productos_garantia: { sourceSelector: "#divMenuProductosGarantia", permissionKey: "VERINFORMEGARANTIA" },
	productos_baja: { sourceSelector: "#divMenuProductosBaja", permissionKey: "VERINFORMEPRODUCTOSDEBAJA" },
	despachar_productor: { sourceSelector: "#divMenuListadoDespachado", permissionKey: "HACERDESPACHO" },
	control_deposito: { sourceSelector: "#divMenuControlDeposito", permissionKey: "VERCONTROLDEPOSITO" },
	agenda_cliente: { sourceSelector: "#divMenuAgenda" },
	cheque: { sourceSelector: "#administrativeSectionContent #divMenuCheque" },
	cargar_imagenes: { sourceSelector: "#administrativeSectionContent table[onclick*='verCerrarAbmCargarFotosClientePrincipal']" },
	activos_fijos: { sourceSelector: "#divMenuInventarioLocal" },

	listado_tareas_usuario: { sourceSelector: "#divMenuAbmTareasUsuario", permissionKey: "VERLISTADOTAREASUSUARIO" },
	listado_consultorios: { sourceSelector: "#divMenuAbmConsultorio", permissionKey: "VERFORMULARIOCONSULTORIO" },
	listado_locales: { sourceSelector: "#divMenuAbmLocales", permissionKey: "VERLISTADODELOCALES" },
	listado_zonas: { sourceSelector: "#divMenuAbmZona", permissionKey: "VERLISTADODEZONAS" },
	listado_cobradores: { sourceSelector: "#divMenuAbmCobradores", permissionKey: "VERLISTADOCOBRADORES" },
	listado_clientes: { sourceSelector: "#divMenuAbmClientes", permissionKey: "VERLISTADODECLIENTES" },
	listado_productos: { sourceSelector: "#divMenuAbmProductos2", permissionKey: "VERLISTADOPRODUCTOS" },
	listado_proveedor: { sourceSelector: "#divMenuAbmProveedores", permissionKey: "VERLISTADOPROVEEDORES" },
	listado_vendedores: { sourceSelector: "#divMenuAbmVendedores", permissionKey: "VERLISTADOVENDEDORES" },
	listado_caja: { sourceSelector: "#divMenuAbmCajas", permissionKey: "VERLISTADODECAJA" },
	lista_factura_habilitadas: { sourceSelector: "#divMenuAbmFacturas", permissionKey: "VERFACTURASHABILITADAS" },
	lista_tipos_pago: { sourceSelector: "#divMenuAbmTipoVenta" },
	lista_bancos: { sourceSelector: "#divMenuAbmBanco" },
	trabajos_mecanicos_dentales: { sourceSelector: "#divMenuTrabajoMecanicoDental" },
	listado_mecanicos_dentales: { sourceSelector: "#divMenuMecanicoDental" },

	imprimir_precio: { sourceSelector: "#divMenuCodigoBarra", permissionKey: "VERINFORMECODIGOBARRA" },
	informe_general_cuentas: { sourceSelector: "#divMenuCuentasGeneral", permissionKey: "VERINFORMECUENTAGENERAL" },
	informe_evaluacion: { sourceSelector: "#divMenuInformeEvaluacion", permissionKey: "VERINFORMEEVALUACION" },
	informe_inventario: { sourceSelector: "#divMenuInformeInventario", permissionKey: "VERINFORMEDEINVENTARIO" },
	informe_ganancia_venta: { sourceSelector: "#divMenuInformeGanPorVenta", permissionKey: "VERINFORMEDEGANANCIAPORVENTA" },
	informe_prod_comprados: { sourceSelector: "#divMenuProductosComprados", permissionKey: "VERINFORMEDEPRODUCTOSCOMPRADOS" },
	informe_prod_vendidos: { sourceSelector: "#divMenuProductosVendidos", permissionKey: "VERINFORMEDEPRODUCTOSVENDIDOS" },
	informe_ventas_canceladas: { sourceSelector: "#divMenuVentasCanceladas", permissionKey: "VERINFORMEDEVENTASCANCELADAS" },
	informe_comision_cobrador: { sourceSelector: "#divMenuComisionCobrador", permissionKey: "VERINFORMEDECOMISIONCOBRADOR" },
	informe_vendedores: { sourceSelector: "#divMenuComisionVendedor", permissionKey: "VERINFORMEDECOMISIONVENDEDOR" },
	informe_pagos_eliminados: { sourceSelector: "#divMenuPagosEliminados", permissionKey: "VERINFORMEDEPAGOSELIMINADOS" },
	informe_solicitud_eliminado: { sourceSelector: "#divMenuSolicitudEliminado", permissionKey: "VERINFORMESOLICITUDELIMINADO" },
	catalogo: { sourceSelector: "#divMenuCatalogo", permissionKey: "VERCATALOGO" },
	clientes_inactivos: { sourceSelector: "#divMenuClientesInactivo", permissionKey: "VERCLIENTESINACTIVOS" },
	productos_despachados: { sourceSelector: "#divMenuProductoDespachado", permissionKey: "VERDESPACHADOS" },
	informe_compras_eliminados: { sourceSelector: "#divMenuComprasEliminados", permissionKey: "VERINFORMEDECOMPRASELIMINADO" },
	informe_metas_vendedores: { sourceSelector: "#divMenuMetasVendedores" },
	clientes_morosos: { sourceSelector: "#divMenuClienteMoroso", permissionKey: "VERINFORMEMOROSO" },
	auditoria_producto: { sourceSelector: "#divMenuAuditoriaProducto" },
	cumpleanos_clientes: { sourceSelector: "#divMenuCumpleCliente" },
	informe_agenda_clientes: { sourceSelector: "#divMenuClieteImpago" },
	clientes_fieles: { sourceSelector: "#divMenuClienteFiel" },
	solicitud_descuento: { sourceSelector: "#divMenuSoliDescuento" },
	contabilidad_venta: { sourceSelector: "#divMenuContabilidad" },
	contabilidad_compra: { sourceSelector: "#divMenuContabilidadCompra" },
	informe_asistencia: { sourceSelector: "#divMenuAsistencia" },
	informe_dictamenes: { sourceSelector: "#divMenuDictamenes" },

	usuarios: { sourceSelector: "#divMenuAbmUsuarios", permissionKey: "VERLISTADOUSUARIO" },
	mis_datos: { sourceSelector: "#sistemaSectionContent table[onclick*='verCerrarMisDatos']" },
	listado_acceso: { sourceSelector: "#divMenuListadoAcceso", permissionKey: "VERLISTADODEACCESO" },
	listado_niveles: { sourceSelector: "#divMenuListadoNiveles", permissionKey: "VERLISTADODENIVELES" }
};

function asegurarBotonCerrarModalDashboard(ventana, claseExtra, etiqueta, accionCerrar) {
	if (!ventana || ventana.querySelector(".dashboard-modal-close")) {
		return;
	}

	var boton = document.createElement("button");
	boton.type = "button";
	boton.className = "dashboard-modal-close " + claseExtra;
	boton.title = "Cerrar";
	boton.setAttribute("aria-label", etiqueta);
	boton.innerHTML = "&times;";
	boton.onclick = accionCerrar;
	ventana.insertBefore(boton, ventana.firstChild);
}

function crearVentanaDiagramaGantSistema() {
	var ventana = document.createElement("div");
	ventana.className = "dashboard-gant-window";
	ventana.id = "dashboardGantWindow";
	ventana.style.display = "none";
	ventana.setAttribute("aria-hidden", "true");
	ventana.innerHTML =
		"<div class='dashboard-gant-window__header'>" +
		"<div>" +
		"<p class='dashboard-gant-window__eyebrow'>Planificacion</p>" +
		"<h2>Diagrama de gant</h2>" +
		"</div>" +
		"<div class='dashboard-gant-window__actions'>" +
		"<button type='button' onclick='recargarDiagramaGantSistema()' title='Actualizar diagrama'>" +
		"<i class='fa-solid fa-rotate-right' aria-hidden='true'></i>" +
		"<span>Actualizar</span>" +
		"</button>" +
		"</div>" +
		"</div>" +
		"<div class='dashboard-gant-window__body'>" +
		"<iframe id='iframeDiagramaGantSistema' title='Diagrama de gant' frameborder='0'></iframe>" +
		"</div>";
	asegurarBotonCerrarModalDashboard(ventana, "dashboard-gant-window__close", "Cerrar diagrama de gant", cerrarDiagramaGantSistema);
	document.body.appendChild(ventana);
	return ventana;
}

function obtenerVentanaDiagramaGantSistema() {
	var ventana = document.getElementById("dashboardGantWindow");

	if (!ventana && document.body) {
		ventana = crearVentanaDiagramaGantSistema();
	}

	asegurarBotonCerrarModalDashboard(ventana, "dashboard-gant-window__close", "Cerrar diagrama de gant", cerrarDiagramaGantSistema);
	return ventana;
}

function abrirDiagramaGantSistema() {
	var ventana = obtenerVentanaDiagramaGantSistema();
	var iframe = document.getElementById("iframeDiagramaGantSistema");

	if (!ventana || !iframe) {
		return;
	}

	if (!iframe.getAttribute("src")) {
		iframe.setAttribute("src", "/GoodVentaAsisCap/php_system/Grant.php?embed=dashboard&modal=1&x=gantt-modal-cierre-20260704");
	}

	ventana.style.display = "flex";
	ventana.setAttribute("aria-hidden", "false");

	if (document.body) {
		document.body.classList.add("dashboard-gant-open");
	}

	setTimeout(function () {
		try {
			iframe.focus();
		} catch (e) {
		}
	}, 120);
}

function cerrarDiagramaGantSistema() {
	var ventana = document.getElementById("dashboardGantWindow");

	if (ventana) {
		ventana.style.display = "none";
		ventana.setAttribute("aria-hidden", "true");
	}

	if (document.body) {
		document.body.classList.remove("dashboard-gant-open");
	}
}

function recargarDiagramaGantSistema() {
	var iframe = document.getElementById("iframeDiagramaGantSistema");

	if (!iframe) {
		abrirDiagramaGantSistema();
		return;
	}

	if (!iframe.getAttribute("src")) {
		abrirDiagramaGantSistema();
		return;
	}

	try {
		iframe.contentWindow.location.reload();
	} catch (e) {
		iframe.setAttribute("src", "/GoodVentaAsisCap/php_system/Grant.php?embed=dashboard&modal=1&x=gantt-modal-cierre-20260704");
	}
}

function crearVentanaAgendaDiaSistema() {
	var ventana = document.createElement("div");
	ventana.className = "dashboard-agenda-window";
	ventana.id = "dashboardAgendaDiaWindow";
	ventana.style.display = "none";
	ventana.setAttribute("aria-hidden", "true");
	ventana.innerHTML =
		"<div class='dashboard-agenda-window__header'>" +
		"<div>" +
		"<p class='dashboard-agenda-window__eyebrow'>Jornada laboral</p>" +
		"<h2>Actividades del dia</h2>" +
		"</div>" +
		"<div class='dashboard-agenda-window__actions'>" +
		"<button type='button' onclick='recargarAgendaDiaSistema()' title='Actualizar actividades'>" +
		"<i class='fa-solid fa-rotate-right' aria-hidden='true'></i>" +
		"<span>Actualizar</span>" +
		"</button>" +
		"</div>" +
		"</div>" +
		"<div class='dashboard-agenda-window__body' id='dashboardAgendaDiaWindowBody'></div>";
	asegurarBotonCerrarModalDashboard(ventana, "dashboard-agenda-window__close", "Cerrar actividades del dia", cerrarAgendaDiaSistema);
	document.body.appendChild(ventana);
	return ventana;
}

function obtenerVentanaAgendaDiaSistema() {
	var ventana = document.getElementById("dashboardAgendaDiaWindow");

	if (!ventana && document.body) {
		ventana = crearVentanaAgendaDiaSistema();
	}

	asegurarBotonCerrarModalDashboard(ventana, "dashboard-agenda-window__close", "Cerrar actividades del dia", cerrarAgendaDiaSistema);
	return ventana;
}

function obtenerWidgetAgendaDiaSistema() {
	if (typeof organizarDashboardPrincipal === "function") {
		organizarDashboardPrincipal();
	}

	return document.querySelector("#dashboardAgendaDiaWindowBody .perfil-widget") ||
		document.querySelector("#dashboardJornadaBody .perfil-widget") ||
		document.querySelector(".perfil-app .perfil-widget");
}

function moverAgendaDiaSistema(contenedor) {
	var widget = obtenerWidgetAgendaDiaSistema();

	if (!widget || !contenedor) {
		return false;
	}

	contenedor.appendChild(widget);
	widget.classList.add("perfil-widget--agenda-dia-window");
	return true;
}

function abrirAgendaDiaSistema() {
	var ventana = obtenerVentanaAgendaDiaSistema();
	var cuerpo = document.getElementById("dashboardAgendaDiaWindowBody");

	if (!ventana || !cuerpo) {
		return;
	}

	if (!moverAgendaDiaSistema(cuerpo)) {
		return;
	}

	ventana.style.display = "flex";
	ventana.setAttribute("aria-hidden", "false");

	if (document.body) {
		document.body.classList.add("dashboard-agenda-open");
	}

	recargarAgendaDiaSistema();
}

function cerrarAgendaDiaSistema() {
	var ventana = document.getElementById("dashboardAgendaDiaWindow");
	var destino = document.getElementById("dashboardJornadaBody");
	var widget = document.querySelector("#dashboardAgendaDiaWindowBody .perfil-widget");

	if (widget) {
		widget.classList.remove("perfil-widget--agenda-dia-window");
		if (destino) {
			destino.appendChild(widget);
		}
	}

	if (ventana) {
		ventana.style.display = "none";
		ventana.setAttribute("aria-hidden", "true");
	}

	if (document.body) {
		document.body.classList.remove("dashboard-agenda-open");
	}
}

function recargarAgendaDiaSistema() {
	if (typeof cargarTareasPendientesAdministrador === "function") {
		cargarTareasPendientesAdministrador({ forzarEstado: true });
	}
}

if (typeof window !== "undefined" && !window.dashboardGantEscapeReady) {
	window.dashboardGantEscapeReady = true;
	window.addEventListener("keydown", function (event) {
		if (event.key === "Escape") {
			cerrarDiagramaGantSistema();
			cerrarAgendaDiaSistema();
		}
	});
}

function dashboardShortcutFormData(funt) {
	var hasUrlCredentials = false;

	if (typeof buscar_datos_url_usuario === "function") {
		var urlUser = buscar_datos_url_usuario("q");
		var urlPass = buscar_datos_url_usuario("p");

		if (urlUser) {
			userid = urlUser;
		}

		if (urlPass) {
			passuser = urlPass;
		}

		hasUrlCredentials = !!(urlUser && urlPass);
	}

	if (!hasUrlCredentials && typeof buscar_este_cookie === "function") {
		var cookieUser = buscar_este_cookie("user");
		var cookiePass = buscar_este_cookie("pass");

		if (cookieUser) {
			userid = cookieUser;
		}

		if (cookiePass) {
			passuser = cookiePass;
		}
	}

	if (typeof obtener_navegor_en_uso === "function") {
		navegador = obtener_navegor_en_uso();
	}

	var datos = new FormData();
	datos.append("funt", funt);
	datos.append("useru", typeof userid !== "undefined" ? userid : "");
	datos.append("passu", typeof passuser !== "undefined" ? passuser : "");
	datos.append("navegador", typeof navegador !== "undefined" ? navegador : "");
	return datos;
}

function dashboardShortcutEscape(valor) {
	return String(valor || "").replace(/[&<>"']/g, function (char) {
		return {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': "&quot;",
			"'": "&#039;"
		}[char];
	});
}

function dashboardShortcutParseJson(responseText) {
	if (responseText && typeof responseText === "object") {
		return responseText;
	}

	var cleanResponse = String(responseText || "").replace(/^\uFEFF/, "").trim();

	try {
		return $.parseJSON(cleanResponse);
	} catch (error) {
		var jsonStart = cleanResponse.indexOf("{");
		var jsonEnd = cleanResponse.lastIndexOf("}");

		if (jsonStart !== -1 && jsonEnd > jsonStart) {
			try {
				return $.parseJSON(cleanResponse.substring(jsonStart, jsonEnd + 1));
			} catch (innerError) {
				console.error("No se pudo leer el JSON recuperado de accesos rapidos", innerError, responseText);
			}
		}

		console.error("No se pudo leer la respuesta de accesos rapidos", error, responseText);
		return null;
	}
}

function dashboardShortcutHasPermission(accessKey) {
	var registry = DASHBOARD_ACCESS_REGISTRY[accessKey];
	var permissionKey = registry ? registry.permissionKey : "";

	if (!permissionKey) {
		return true;
	}

	if (typeof accesosuser === "undefined" || !accesosuser) {
		return true;
	}

	return !!(accesosuser[permissionKey] && accesosuser[permissionKey]["accion"] == "SI");
}

function dashboardShortcutGetSource(accessKey) {
	var registry = DASHBOARD_ACCESS_REGISTRY[accessKey];

	if (!registry || !registry.sourceSelector) {
		return null;
	}

	return document.querySelector(registry.sourceSelector);
}

function dashboardShortcutAccessKeyFromElement(elemento, fallbackKey) {
	if (!elemento) {
		return "";
	}

	var accessKey = elemento.dataset && elemento.dataset.accessKey ? elemento.dataset.accessKey : "";

	if (!accessKey) {
		accessKey = elemento.getAttribute("data-access-key") || "";
	}

	if (accessKey) {
		return accessKey;
	}

	if (elemento.closest && elemento.closest("#quickAccessSection")) {
		console.warn("El boton de acceso rapido no tiene data-access-key:", elemento);
		return "";
	}

	return fallbackKey || "";
}

function dashboardShortcutIsInlineHidden(elemento) {
	if (!elemento) {
		return true;
	}

	var estilo = (elemento.getAttribute("style") || "").replace(/\s/g, "").toLowerCase();
	return estilo.indexOf("display:none") !== -1 || elemento.style.display == "none";
}

function dashboardShortcutPrepareTemplates() {
	if (dashboardShortcutTemplatesReady) {
		return;
	}

	for (var accessKey in DASHBOARD_ACCESS_REGISTRY) {
		if (!DASHBOARD_ACCESS_REGISTRY.hasOwnProperty(accessKey)) {
			continue;
		}

		var source = dashboardShortcutGetSource(accessKey);
		var sourceAccessKey = dashboardShortcutAccessKeyFromElement(source, accessKey);

		if (!source || dashboardShortcutIsInlineHidden(source) || !dashboardShortcutHasPermission(accessKey)) {
			continue;
		}

		if (sourceAccessKey != accessKey) {
			console.warn("Access key no coincide con el registro:", accessKey, sourceAccessKey, source);
			continue;
		}

		DASHBOARD_ACCESS_REGISTRY[accessKey].template = source.cloneNode(true);
	}

	dashboardShortcutTemplatesReady = true;

	if (dashboardShortcutSelectedKeys.length == 0) {
		dashboardShortcutSelectedKeys = dashboardShortcutDefaultKeys();
	}
}

function dashboardShortcutLabel(accessKey, accessData) {
	var registry = DASHBOARD_ACCESS_REGISTRY[accessKey];
	var template = registry ? registry.template : null;
	var labelElement = template ? template.querySelector(".pTitulo4") : null;

	if (labelElement && labelElement.textContent) {
		return labelElement.textContent.replace(/\s+/g, " ").trim();
	}

	return accessData && accessData.label ? accessData.label : accessKey;
}

function dashboardShortcutIcon(accessKey) {
	var registry = DASHBOARD_ACCESS_REGISTRY[accessKey];
	var template = registry ? registry.template : null;
	var icon = template ? template.querySelector(".imgIconoMenu") : null;

	return icon ? icon.getAttribute("src") : "/GoodVentaAsisCap/iconos/home.png";
}

function dashboardShortcutCleanClone(tile, accessKey) {
	tile.setAttribute("data-access-key", accessKey);
	tile.setAttribute("data-dashboard-access-key", accessKey);
	tile.setAttribute("data-dashboard-rendered-shortcut", "1");
	tile.classList.add("dashboard-access-tile");
	tile.removeAttribute("id");
	tile.style.display = "";
	tile.style.width = "";
	tile.style.minWidth = "";
	tile.style.maxWidth = "";

	var elementosConId = tile.querySelectorAll("[id]");

	for (var i = 0; i < elementosConId.length; i++) {
		elementosConId[i].setAttribute("data-original-id", elementosConId[i].id);
		elementosConId[i].removeAttribute("id");
	}

	return tile;
}

function dashboardShortcutCreateTile(accessKey) {
	var registry = DASHBOARD_ACCESS_REGISTRY[accessKey];
	var template = registry ? registry.template : null;

	if (!template) {
		return null;
	}

	return dashboardShortcutCleanClone(template.cloneNode(true), accessKey);
}

function dashboardShortcutPreserveOriginalTiles(grid) {
	var cache = document.getElementById("dashboardQuickAccessTemplateCache");

	if (!cache) {
		cache = document.createElement("div");
		cache.id = "dashboardQuickAccessTemplateCache";
		cache.style.display = "none";
		document.body.appendChild(cache);
	}

	while (grid.firstChild) {
		var child = grid.firstChild;

		if (child.nodeType === 1 && child.getAttribute("data-dashboard-rendered-shortcut") == "1") {
			grid.removeChild(child);
		} else {
			cache.appendChild(child);
		}
	}
}

function dashboardShortcutKeysFromAccessList(accessList) {
	var keys = [];

	for (var i = 0; i < accessList.length; i++) {
		var key = accessList[i].access_key || accessList[i].accessKey || "";

		if (key && DASHBOARD_ACCESS_REGISTRY[key] && DASHBOARD_ACCESS_REGISTRY[key].template) {
			keys.push(key);
		}
	}

	return keys;
}

function dashboardShortcutKeysFromCurrentDom() {
	var keys = [];
	var tiles = document.querySelectorAll("#quickAccessSection .dashboard-access-grid > .divMenub");

	for (var i = 0; i < tiles.length; i++) {
		if (dashboardShortcutIsInlineHidden(tiles[i])) {
			continue;
		}

		var key = dashboardShortcutAccessKeyFromElement(tiles[i], "");

		if (!key) {
			console.warn("El boton de acceso rapido no tiene data-access-key:", tiles[i]);
			continue;
		}

		if (DASHBOARD_ACCESS_REGISTRY[key] && DASHBOARD_ACCESS_REGISTRY[key].template) {
			keys.push(key);
		}
	}

	return keys;
}

function dashboardShortcutDefaultKeys() {
	var keys = [];

	for (var i = 0; i < DASHBOARD_SHORTCUT_DEFAULT_KEYS.length; i++) {
		var key = DASHBOARD_SHORTCUT_DEFAULT_KEYS[i];

		if (DASHBOARD_ACCESS_REGISTRY[key] && DASHBOARD_ACCESS_REGISTRY[key].template) {
			keys.push(key);
		}
	}

	return keys;
}

function dashboardShortcutSyncSelectionWithCatalog() {
	if (!dashboardShortcutCatalogLoaded) {
		return;
	}

	var validKeys = [];

	for (var i = 0; i < dashboardShortcutSelectedKeys.length; i++) {
		var key = dashboardShortcutSelectedKeys[i];

		if (dashboardShortcutCatalogByKey[key]) {
			validKeys.push(key);
		} else {
			console.warn("Access key no existe en catalogo o no esta disponible para el usuario:", key);
		}
	}

	dashboardShortcutSelectedKeys = validKeys;
}

function dashboardShortcutFlushCatalogCallbacks(success) {
	var callbacks = dashboardShortcutCatalogCallbacks.slice(0);
	dashboardShortcutCatalogCallbacks = [];

	for (var i = 0; i < callbacks.length; i++) {
		if (typeof callbacks[i] === "function") {
			callbacks[i](success);
		}
	}
}

function renderDashboardQuickAccess(accessList) {
	dashboardShortcutPrepareTemplates();

	var grid = document.querySelector("#quickAccessSection .dashboard-access-grid");

	if (!grid) {
		return;
	}

	var keys = dashboardShortcutKeysFromAccessList(accessList || []);

	if (keys.length == 0) {
		keys = dashboardShortcutDefaultKeys();
	}

	if (keys.length == 0) {
		keys = dashboardShortcutKeysFromCurrentDom();
	}

	if (keys.length == 0) {
		return;
	}

	dashboardShortcutPreserveOriginalTiles(grid);

	var fragment = document.createDocumentFragment();
	var renderedKeys = [];

	for (var i = 0; i < keys.length; i++) {
		var tile = dashboardShortcutCreateTile(keys[i]);

		if (tile) {
			fragment.appendChild(tile);
			renderedKeys.push(keys[i]);
		}
	}

	if (renderedKeys.length == 0) {
		return;
	}

	grid.appendChild(fragment);
	dashboardShortcutSelectedKeys = renderedKeys.slice(0);

	if (typeof actualizarBuscadorAccesosDashboard === "function") {
		setTimeout(actualizarBuscadorAccesosDashboard, 80);
	}
}

function cargarDashboardAccessCatalog(callback) {
	dashboardShortcutPrepareTemplates();

	if (typeof callback === "function") {
		dashboardShortcutCatalogCallbacks.push(callback);
	}

	if (dashboardShortcutCatalogLoaded) {
		dashboardShortcutFlushCatalogCallbacks(true);
		return;
	}

	if (dashboardShortcutCatalogLoading) {
		return;
	}

	dashboardShortcutCatalogLoading = true;

	var datos = dashboardShortcutFormData("catalog");

	$.ajax({
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		url: "/GoodVentaAsisCap/php_system/dashboard_shortcuts.php",
		type: "post",
		error: function () {
			dashboardShortcutCatalogLoading = false;
			dashboardShortcutCatalogLastError = "No se pudo conectar con el servidor del catalogo.";
			console.error("No se pudo cargar el catalogo de accesos rapidos");
			renderDashboardShortcutModalContent();
			dashboardShortcutFlushCatalogCallbacks(false);
		},
		success: function (responseText) {
			var respuesta = dashboardShortcutParseJson(responseText);

			if (!respuesta || respuesta["1"] != "exito") {
				dashboardShortcutCatalogLoading = false;
				if (respuesta && respuesta["1"] == "UI") {
					dashboardShortcutCatalogLastError = "La sesion no fue validada para cargar el catalogo.";
				} else if (respuesta && respuesta["2"]) {
					dashboardShortcutCatalogLastError = "El servidor respondio: " + respuesta["2"];
				} else {
					dashboardShortcutCatalogLastError = "El servidor devolvio una respuesta invalida del catalogo.";
				}
				console.error("Respuesta invalida al cargar catalogo", responseText);
				renderDashboardShortcutModalContent();
				dashboardShortcutFlushCatalogCallbacks(false);
				return;
			}

			var catalog = respuesta.catalog || respuesta["2"] || [];
			dashboardShortcutCatalog = [];
			dashboardShortcutCatalogByKey = {};

			for (var i = 0; i < catalog.length; i++) {
				var key = catalog[i].access_key;

				if (!key || !DASHBOARD_ACCESS_REGISTRY[key] || !DASHBOARD_ACCESS_REGISTRY[key].template) {
					continue;
				}

				if (!dashboardShortcutHasPermission(key)) {
					continue;
				}

				catalog[i].label = dashboardShortcutLabel(key, catalog[i]);
				catalog[i].icon_src = dashboardShortcutIcon(key);
				dashboardShortcutCatalog.push(catalog[i]);
				dashboardShortcutCatalogByKey[key] = catalog[i];
			}

			dashboardShortcutCatalogLoaded = true;
			dashboardShortcutCatalogLoading = false;
			dashboardShortcutCatalogLastError = "";
			dashboardShortcutSyncSelectionWithCatalog();
			renderDashboardShortcutModalContent();
			dashboardShortcutFlushCatalogCallbacks(true);
		}
	});
}

function cargarDashboardUserShortcuts() {
	dashboardShortcutPrepareTemplates();

	var datos = dashboardShortcutFormData("user_shortcuts");

	$.ajax({
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		url: "/GoodVentaAsisCap/php_system/dashboard_shortcuts.php",
		type: "post",
		error: function () {
			console.error("No se pudo cargar la configuracion de accesos rapidos");
		},
		success: function (responseText) {
			var respuesta = dashboardShortcutParseJson(responseText);

			if (!respuesta || respuesta["1"] != "exito") {
				console.error("Respuesta invalida al cargar accesos rapidos", responseText);
				return;
			}

			var shortcuts = respuesta.shortcuts || respuesta["2"] || [];
			renderDashboardQuickAccess(shortcuts);
		}
	});
}

function crearModalAccesosRapidos() {
	if (document.getElementById("dashboardShortcutModal")) {
		return;
	}

	var modal = document.createElement("div");
	modal.id = "dashboardShortcutModal";
	modal.className = "dashboard-shortcut-modal";
	modal.style.display = "none";
	modal.innerHTML =
		"<div class='dashboard-shortcut-modal__backdrop' onclick='cerrarModalAccesosRapidos()'></div>" +
		"<div class='dashboard-shortcut-modal__panel' role='dialog' aria-modal='true' aria-labelledby='dashboardShortcutModalTitle'>" +
		"<div class='dashboard-shortcut-modal__header'>" +
		"<div>" +
		"<p class='dashboard-shortcut-modal__eyebrow'>Personalizacion</p>" +
		"<h3 id='dashboardShortcutModalTitle'>Editar accesos rapidos</h3>" +
		"</div>" +
		"<button type='button' class='dashboard-shortcut-modal__close' onclick='cerrarModalAccesosRapidos()'>x</button>" +
		"</div>" +
		"<div class='dashboard-shortcut-modal__toolbar'>" +
		"<input type='text' id='dashboardShortcutSearch' class='dashboard-shortcut-modal__search' placeholder='Buscar acceso...' onkeyup='renderDashboardShortcutModalContent()' />" +
		"<span id='dashboardShortcutLimit' class='dashboard-shortcut-modal__limit'></span>" +
		"</div>" +
		"<div class='dashboard-shortcut-modal__body'>" +
		"<div class='dashboard-shortcut-modal__catalog' id='dashboardShortcutCatalog'></div>" +
		"<div class='dashboard-shortcut-modal__preview'>" +
		"<div class='dashboard-shortcut-modal__preview-header'>" +
		"<strong>Seleccionados</strong>" +
		"<small>Arrastra para ordenar. Tambien podes usar las flechas.</small>" +
		"</div>" +
		"<div id='dashboardShortcutPreview' ondragover='dashboardShortcutHandleDragOver(event)' ondrop='dashboardShortcutDropEnPreview(event)'></div>" +
		"</div>" +
		"</div>" +
		"<div class='dashboard-shortcut-modal__footer'>" +
		"<button type='button' class='dashboard-shortcut-modal__btn dashboard-shortcut-modal__btn--secondary' onclick='dashboardShortcutResetToDefault()'>Restablecer predeterminado</button>" +
		"<button type='button' class='dashboard-shortcut-modal__btn dashboard-shortcut-modal__btn--secondary' onclick='cerrarModalAccesosRapidos()'>Cancelar</button>" +
		"<button type='button' class='dashboard-shortcut-modal__btn dashboard-shortcut-modal__btn--primary' onclick='guardarAccesosRapidosUsuario()'>Guardar cambios</button>" +
		"</div>" +
		"</div>";

	document.body.appendChild(modal);
}

function abrirModalAccesosRapidos() {
	crearModalAccesosRapidos();

	if (dashboardShortcutCatalogLoaded) {
		dashboardShortcutSyncSelectionWithCatalog();
	}

	if (!dashboardShortcutCatalogLoaded) {
		cargarDashboardAccessCatalog();
	}

	renderDashboardShortcutModalContent();
	document.getElementById("dashboardShortcutModal").style.display = "";
}

function cerrarModalAccesosRapidos() {
	var modal = document.getElementById("dashboardShortcutModal");

	if (modal) {
		modal.style.display = "none";
	}
}

function dashboardShortcutIsSelected(accessKey) {
	return dashboardShortcutSelectedKeys.indexOf(accessKey) !== -1;
}

function dashboardShortcutToggle(accessKey, checked) {
	var index = dashboardShortcutSelectedKeys.indexOf(accessKey);

	if (checked && index === -1) {
		if (dashboardShortcutSelectedKeys.length >= DASHBOARD_SHORTCUT_MAX) {
			ver_vetana_informativa("Limite de accesos", "Solo podes seleccionar hasta " + DASHBOARD_SHORTCUT_MAX + " accesos rapidos.", "advertencia");
			renderDashboardShortcutModalContent();
			return;
		}

		dashboardShortcutSelectedKeys.push(accessKey);
	}

	if (!checked && index !== -1) {
		dashboardShortcutSelectedKeys.splice(index, 1);
	}

	renderDashboardShortcutModalContent();
}

function dashboardShortcutMove(accessKey, direction) {
	var index = dashboardShortcutSelectedKeys.indexOf(accessKey);
	var nextIndex = index + direction;

	if (index === -1 || nextIndex < 0 || nextIndex >= dashboardShortcutSelectedKeys.length) {
		return;
	}

	var temp = dashboardShortcutSelectedKeys[index];
	dashboardShortcutSelectedKeys[index] = dashboardShortcutSelectedKeys[nextIndex];
	dashboardShortcutSelectedKeys[nextIndex] = temp;
	renderDashboardShortcutModalContent();
}

function dashboardShortcutRemove(accessKey) {
	var index = dashboardShortcutSelectedKeys.indexOf(accessKey);

	if (index !== -1) {
		dashboardShortcutSelectedKeys.splice(index, 1);
	}

	renderDashboardShortcutModalContent();
}

function dashboardShortcutGetInsertIndex(targetKey, fallbackIndex) {
	if (!targetKey) {
		return fallbackIndex;
	}

	var index = dashboardShortcutSelectedKeys.indexOf(targetKey);
	return index === -1 ? fallbackIndex : index;
}

function dashboardShortcutInsertKeyAt(accessKey, targetIndex) {
	if (!accessKey || !dashboardShortcutCatalogByKey[accessKey]) {
		return false;
	}

	var currentIndex = dashboardShortcutSelectedKeys.indexOf(accessKey);

	if (currentIndex === -1 && dashboardShortcutSelectedKeys.length >= DASHBOARD_SHORTCUT_MAX) {
		ver_vetana_informativa("Limite de accesos", "Solo podes seleccionar hasta " + DASHBOARD_SHORTCUT_MAX + " accesos rapidos.", "advertencia");
		return false;
	}

	if (currentIndex !== -1) {
		dashboardShortcutSelectedKeys.splice(currentIndex, 1);
		if (targetIndex > currentIndex) {
			targetIndex--;
		}
	}

	targetIndex = parseInt(targetIndex, 10);
	if (isNaN(targetIndex)) {
		targetIndex = dashboardShortcutSelectedKeys.length;
	}
	targetIndex = Math.max(0, Math.min(targetIndex, dashboardShortcutSelectedKeys.length));
	dashboardShortcutSelectedKeys.splice(targetIndex, 0, accessKey);
	return true;
}

function dashboardShortcutResetToDefault() {
	if (!dashboardShortcutCatalogLoaded) {
		cargarDashboardAccessCatalog(function (success) {
			if (success) {
				dashboardShortcutResetToDefault();
			}
		});
		return;
	}

	var keys = [];
	for (var i = 0; i < DASHBOARD_SHORTCUT_DEFAULT_KEYS.length; i++) {
		var key = DASHBOARD_SHORTCUT_DEFAULT_KEYS[i];
		if (dashboardShortcutCatalogByKey[key]) {
			keys.push(key);
		}
	}

	if (keys.length == 0) {
		ver_vetana_informativa("Sin accesos predeterminados", "No hay accesos predeterminados disponibles para este usuario.", "advertencia");
		return;
	}

	dashboardShortcutSelectedKeys = keys.slice(0, DASHBOARD_SHORTCUT_MAX);
	renderDashboardShortcutModalContent();
}

function dashboardShortcutHandleDragStart(event, accessKey, source) {
	dashboardShortcutDragSource = source || "preview";

	if (event && event.dataTransfer) {
		event.dataTransfer.effectAllowed = source == "catalog" ? "copyMove" : "move";
		event.dataTransfer.setData("text/plain", accessKey);
		event.dataTransfer.setData("application/x-dashboard-access-key", accessKey);
	}

	var modal = document.getElementById("dashboardShortcutModal");
	if (modal) {
		modal.classList.add("dashboard-shortcut-modal--dragging");
	}
}

function dashboardShortcutHandleDragEnd() {
	dashboardShortcutDragSource = "";
	var modal = document.getElementById("dashboardShortcutModal");
	if (modal) {
		modal.classList.remove("dashboard-shortcut-modal--dragging");
	}
}

function dashboardShortcutHandleDragOver(event) {
	if (event) {
		event.preventDefault();
		if (event.dataTransfer) {
			event.dataTransfer.dropEffect = dashboardShortcutDragSource == "catalog" ? "copy" : "move";
		}
	}
}

function dashboardShortcutDropEnPreview(event, targetKey) {
	if (event) {
		event.preventDefault();
		event.stopPropagation();
	}

	var accessKey = "";
	if (event && event.dataTransfer) {
		accessKey = event.dataTransfer.getData("application/x-dashboard-access-key") || event.dataTransfer.getData("text/plain");
	}

	if (!accessKey) {
		return;
	}

	var targetIndex = dashboardShortcutGetInsertIndex(targetKey, dashboardShortcutSelectedKeys.length);
	if (dashboardShortcutInsertKeyAt(accessKey, targetIndex)) {
		renderDashboardShortcutModalContent();
	}
	dashboardShortcutHandleDragEnd();
}

function dashboardShortcutStartPointerDrag(event, accessKey) {
	if (!event || !accessKey) {
		return;
	}
	if (event.pointerType == "mouse" && event.button !== 0) {
		return;
	}

	event.preventDefault();
	dashboardShortcutPointerDrag = {
		accessKey: accessKey,
		pointerId: event.pointerId
	};

	try {
		event.currentTarget.setPointerCapture(event.pointerId);
	} catch (error) {}

	document.addEventListener("pointermove", dashboardShortcutPointerMove);
	document.addEventListener("pointerup", dashboardShortcutPointerEnd);
	document.addEventListener("pointercancel", dashboardShortcutPointerEnd);

	var modal = document.getElementById("dashboardShortcutModal");
	if (modal) {
		modal.classList.add("dashboard-shortcut-modal--dragging");
	}
}

function dashboardShortcutPointerMove(event) {
	if (!dashboardShortcutPointerDrag || event.pointerId !== dashboardShortcutPointerDrag.pointerId) {
		return;
	}

	var preview = document.getElementById("dashboardShortcutPreview");
	if (!preview) {
		return;
	}

	var element = document.elementFromPoint(event.clientX, event.clientY);
	var item = element && element.closest ? element.closest(".dashboard-shortcut-preview-item") : null;
	var targetIndex = dashboardShortcutSelectedKeys.length;

	if (item && preview.contains(item)) {
		var targetKey = item.getAttribute("data-shortcut-key") || "";
		var rect = item.getBoundingClientRect();
		targetIndex = dashboardShortcutSelectedKeys.indexOf(targetKey);
		if (event.clientY > rect.top + (rect.height / 2)) {
			targetIndex++;
		}
	}

	if (dashboardShortcutInsertKeyAt(dashboardShortcutPointerDrag.accessKey, targetIndex)) {
		renderDashboardShortcutModalContent();
	}
}

function dashboardShortcutPointerEnd(event) {
	if (dashboardShortcutPointerDrag && event && event.pointerId !== dashboardShortcutPointerDrag.pointerId) {
		return;
	}

	dashboardShortcutPointerDrag = null;
	document.removeEventListener("pointermove", dashboardShortcutPointerMove);
	document.removeEventListener("pointerup", dashboardShortcutPointerEnd);
	document.removeEventListener("pointercancel", dashboardShortcutPointerEnd);
	dashboardShortcutHandleDragEnd();
}

function renderDashboardShortcutModalContent() {
	var catalogContainer = document.getElementById("dashboardShortcutCatalog");
	var previewContainer = document.getElementById("dashboardShortcutPreview");
	var limit = document.getElementById("dashboardShortcutLimit");

	if (!catalogContainer || !previewContainer) {
		return;
	}

	var searchInput = document.getElementById("dashboardShortcutSearch");
	var search = searchInput ? searchInput.value.toLowerCase().trim() : "";
	var groups = {};
	var groupOrder = [];

	for (var i = 0; i < dashboardShortcutCatalog.length; i++) {
		var access = dashboardShortcutCatalog[i];
		var label = dashboardShortcutLabel(access.access_key, access);
		var moduleLabel = access.module_label || "Otros";
		var searchable = (label + " " + moduleLabel).toLowerCase();

		if (search && searchable.indexOf(search) === -1) {
			continue;
		}

		if (!groups[moduleLabel]) {
			groups[moduleLabel] = [];
			groupOrder.push(moduleLabel);
		}

		groups[moduleLabel].push(access);
	}

	var catalogHtml = "";

	if (dashboardShortcutCatalogLoading) {
		catalogHtml = "<div class='dashboard-shortcut-empty'>Cargando catalogo de accesos...</div>";
	} else if (dashboardShortcutCatalog.length == 0) {
		catalogHtml = "<div class='dashboard-shortcut-empty'>No se pudo cargar el catalogo de accesos.</div>";
	} else if (groupOrder.length == 0) {
		catalogHtml = "<div class='dashboard-shortcut-empty'>No hay accesos que coincidan con la busqueda.</div>";
	} else {
		for (var g = 0; g < groupOrder.length; g++) {
			var groupName = groupOrder[g];
			catalogHtml += "<section class='dashboard-shortcut-group'>";
			catalogHtml += "<h4>" + dashboardShortcutEscape(groupName) + "</h4>";
			catalogHtml += "<div class='dashboard-shortcut-options'>";

			for (var j = 0; j < groups[groupName].length; j++) {
				var item = groups[groupName][j];
				var checked = dashboardShortcutIsSelected(item.access_key) ? "checked" : "";
				var disabled = !checked && dashboardShortcutSelectedKeys.length >= DASHBOARD_SHORTCUT_MAX ? "disabled" : "";

				catalogHtml += "<label class='dashboard-shortcut-option' draggable='true' data-shortcut-catalog-key='" + dashboardShortcutEscape(item.access_key) + "' ondragstart='dashboardShortcutHandleDragStart(event,\"" + dashboardShortcutEscape(item.access_key) + "\",\"catalog\")' ondragend='dashboardShortcutHandleDragEnd()'>";
				catalogHtml += "<input type='checkbox' " + checked + " " + disabled + " onchange='dashboardShortcutToggle(\"" + dashboardShortcutEscape(item.access_key) + "\", this.checked)' />";
				catalogHtml += "<img src='" + dashboardShortcutEscape(item.icon_src || dashboardShortcutIcon(item.access_key)) + "' alt='' />";
				catalogHtml += "<span>" + dashboardShortcutEscape(dashboardShortcutLabel(item.access_key, item)) + "</span>";
				catalogHtml += "</label>";
			}

			catalogHtml += "</div>";
			catalogHtml += "</section>";
		}
	}

	catalogContainer.innerHTML = catalogHtml;

	var previewHtml = "";

	if (dashboardShortcutSelectedKeys.length == 0) {
		previewHtml = "<div class='dashboard-shortcut-empty'>Selecciona al menos un acceso.</div>";
	} else {
		for (var k = 0; k < dashboardShortcutSelectedKeys.length; k++) {
			var key = dashboardShortcutSelectedKeys[k];
			var accessData = dashboardShortcutCatalogByKey[key] || {};

			previewHtml += "<div class='dashboard-shortcut-preview-item' draggable='true' data-shortcut-key='" + dashboardShortcutEscape(key) + "' ondragstart='dashboardShortcutHandleDragStart(event,\"" + dashboardShortcutEscape(key) + "\",\"preview\")' ondragover='dashboardShortcutHandleDragOver(event)' ondrop='dashboardShortcutDropEnPreview(event,\"" + dashboardShortcutEscape(key) + "\")' ondragend='dashboardShortcutHandleDragEnd()'>";
			previewHtml += "<button type='button' class='dashboard-shortcut-drag-handle' title='Arrastrar para ordenar' onpointerdown='dashboardShortcutStartPointerDrag(event,\"" + dashboardShortcutEscape(key) + "\")'>::</button>";
			previewHtml += "<span class='dashboard-shortcut-preview-item__order'>" + (k + 1) + "</span>";
			previewHtml += "<img src='" + dashboardShortcutEscape(dashboardShortcutIcon(key)) + "' alt='' />";
			previewHtml += "<span class='dashboard-shortcut-preview-item__label'>" + dashboardShortcutEscape(dashboardShortcutLabel(key, accessData)) + "</span>";
			previewHtml += "<button type='button' title='Subir' onclick='dashboardShortcutMove(\"" + dashboardShortcutEscape(key) + "\", -1)'>&uarr;</button>";
			previewHtml += "<button type='button' title='Bajar' onclick='dashboardShortcutMove(\"" + dashboardShortcutEscape(key) + "\", 1)'>&darr;</button>";
			previewHtml += "<button type='button' title='Quitar' onclick='dashboardShortcutRemove(\"" + dashboardShortcutEscape(key) + "\")'>x</button>";
			previewHtml += "</div>";
		}
	}

	previewContainer.innerHTML = previewHtml;

	if (limit) {
		limit.innerHTML = dashboardShortcutSelectedKeys.length + " / " + DASHBOARD_SHORTCUT_MAX + " seleccionados";
	}
}

function guardarAccesosRapidosUsuario() {
	if (dashboardShortcutSelectedKeys.length == 0) {
		ver_vetana_informativa("Seleccion requerida", "Selecciona al menos un acceso rapido.", "advertencia");
		return;
	}

	if (!dashboardShortcutCatalogLoaded) {
		cargarDashboardAccessCatalog(function (success) {
			if (success) {
				guardarAccesosRapidosUsuario();
				return;
			}

			ver_vetana_informativa("Catalogo no disponible", dashboardShortcutCatalogLastError || "No se pudo cargar el catalogo de accesos rapidos. Intenta nuevamente.", "error");
		});
		return;
	}

	var shortcuts = [];

	for (var i = 0; i < dashboardShortcutSelectedKeys.length; i++) {
		var key = dashboardShortcutSelectedKeys[i];
		var access = dashboardShortcutCatalogByKey[key];

		if (!access || !access.access_id) {
			var source = dashboardShortcutGetSource(key);
			var detalle = key ? "No se pudo identificar el acceso con data-access-key: " + key : "Hay un boton de acceso rapido sin data-access-key.";

			if (source && !dashboardShortcutAccessKeyFromElement(source, "")) {
				detalle = "El boton " + (source.id || "(sin id)") + " no tiene data-access-key.";
			}

			console.warn(detalle, source || key);
			ver_vetana_informativa("Catalogo incompleto", detalle, "error");
			return;
		}

		shortcuts.push({
			access_id: access.access_id,
			shortcut_order: i + 1
		});
	}

	var datos = dashboardShortcutFormData("save_user_shortcuts");
	datos.append("shortcuts", JSON.stringify(shortcuts));

	$.ajax({
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		url: "/GoodVentaAsisCap/php_system/dashboard_shortcuts.php",
		type: "post",
		error: function () {
			ver_vetana_informativa("Error inesperado", "No se pudo guardar la configuracion de accesos.", "error");
		},
		success: function (responseText) {
			var respuesta = dashboardShortcutParseJson(responseText);

			if (!respuesta) {
				ver_vetana_informativa("Error inesperado", "No se pudo leer la respuesta del servidor.", "error");
				return;
			}

			if (respuesta["1"] == "UI") {
				ir_a_login();
				return;
			}

			if (respuesta["1"] != "exito") {
				ver_vetana_informativa("No se pudo guardar", respuesta["2"] || "Intenta nuevamente.", "error");
				return;
			}

			var shortcutsGuardados = respuesta.shortcuts || respuesta["2"] || [];
			renderDashboardQuickAccess(shortcutsGuardados);
			cerrarModalAccesosRapidos();
			ver_vetana_informativa("Accesos rapidos actualizados", "", "info");
		}
	});
}

function inicializarAccesosRapidosUsuario() {
	crearModalAccesosRapidos();
	dashboardShortcutPrepareTemplates();
	cargarDashboardAccessCatalog();
	cargarDashboardUserShortcuts();
}

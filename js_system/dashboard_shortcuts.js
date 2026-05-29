var DASHBOARD_SHORTCUT_MAX = 15;
var dashboardShortcutCatalog = [];
var dashboardShortcutCatalogByKey = {};
var dashboardShortcutSelectedKeys = [];
var dashboardShortcutTemplatesReady = false;
var dashboardShortcutCatalogLoaded = false;
var dashboardShortcutCatalogLoading = false;
var dashboardShortcutCatalogCallbacks = [];
var dashboardShortcutCatalogLastError = "";

var DASHBOARD_SHORTCUT_DEFAULT_KEYS = [
	"cargar_compras",
	"cuentas_a_cobrar",
	"cobros_realizados",
	"expediente_cliente",
	"historial_venta",
	"productos",
	"nueva_venta",
	"flujo_egreso_ingreso",
	"cerrar_caja",
	"hilos_interconsultas",
	"historial_presupuestos",
	"migrar_caja",
	"recibir_caja"
];

var DASHBOARD_ACCESS_REGISTRY = {
	cargar_compras: { sourceSelector: "#divMenuCompra1", permissionKey: "VERCARGADECOMPRAS" },
	cuentas_a_cobrar: { sourceSelector: "#divMenuCuentasCobar1", permissionKey: "VERCUENTASACOBRAR" },
	cobros_realizados: { sourceSelector: "#divMenuCobrosRealizado1", permissionKey: "VERCOBROSREALIZADOS" },
	expediente_cliente: { sourceSelector: "#divMenuExpedienteCliente1", permissionKey: "VEREXPEDIENTEDELCLIENTE" },
	historial_venta: { sourceSelector: "#divMenuHistorialVenta1", permissionKey: "VERHISTORIALVENTA" },
	productos: { sourceSelector: "#divMenuAbmProductos1", permissionKey: "VERLISTADOPRODUCTOS" },
	nueva_venta: { sourceSelector: "#quickAccessSection #divMenuVenta", permissionKey: "VERVENTA" },
	flujo_egreso_ingreso: { sourceSelector: "#divMenuEgreso_Ingreso2", permissionKey: "VERLISTADOEGRESOINGRESO" },
	cerrar_caja: { sourceSelector: "#divMenuArqueo", permissionKey: "VERCERRARCAJA" },
	hilos_interconsultas: { sourceSelector: "#divMenuInterConsulta" },
	historial_presupuestos: { sourceSelector: "#divMenuPresupuestoProducto2" },
	migrar_caja: { sourceSelector: "#divMenuMigrarCaja", permissionKey: "VERMIGRARCAJA" },
	recibir_caja: { sourceSelector: "#divMenuRecibirCaja", permissionKey: "VERRECIBIRCAJA" },

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
		"<small>Usa las flechas para ordenar</small>" +
		"</div>" +
		"<div id='dashboardShortcutPreview'></div>" +
		"</div>" +
		"</div>" +
		"<div class='dashboard-shortcut-modal__footer'>" +
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

				catalogHtml += "<label class='dashboard-shortcut-option'>";
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

			previewHtml += "<div class='dashboard-shortcut-preview-item'>";
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

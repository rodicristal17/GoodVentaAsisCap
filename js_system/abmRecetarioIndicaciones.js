var recetarioContexto = {};
var recetarioVentas = [];
var recetarioPlantillas = [];
var recetarioMedicamentos = [];
var recetarioIndicaciones = [];
var recetarioDocumentoId = "";
var recetarioDocumentoReemplazadoId = "";
var recetarioTipoCreacion = "";
var recetarioBloqueado = false;
var recetarioAnularId = "";
var recetarioContextValidationStatus = "idle";
var recetarioContextValidationMessage = "";
var emisorStatus = "authorized";
var emisorMessage = "Usuario del sistema";
var recetarioUltimasOpcionesContexto = {};
var recetarioGuardandoAccion = "";
var recetarioFirma = null;
var recetarioEstadoActual = "borrador";
var recetarioFirmaCanvas = {
	canvas: null,
	ctx: null,
	dibujando: false,
	firmado: false,
	ultimoX: 0,
	ultimoY: 0
};

var recetarioCategorias = [
	"Indicaciones generales",
	"Post extraccion",
	"Post cirugia",
	"Higiene oral",
	"Protesis removible",
	"PPR acrilica",
	"PPR flexible",
	"Endodoncia",
	"Ortodoncia",
	"Dolor e inflamacion",
	"Control posterior",
	"Medicacion y cuidados",
	"Emergencias / signos de alarma"
];

var recetarioFrecuenciasSugeridas = [
	"cada 6 horas",
	"cada 8 horas",
	"cada 12 horas",
	"cada 24 horas",
	"segun dolor",
	"antes de dormir",
	"despues de cada comida",
	"segun indicacion"
];

var recetarioDuracionesSugeridas = [
	"por 3 dias",
	"por 5 dias",
	"por 7 dias",
	"por 10 dias",
	"por 1 semana",
	"hasta terminar tratamiento",
	"segun indicacion profesional"
];

var recetarioViasSugeridas = [
	"oral",
	"sublingual",
	"topica",
	"bucal",
	"enjuague",
	"intramuscular",
	"segun indicacion"
];

var recetarioCantidadesSugeridas = [
	"10 comprimidos",
	"20 comprimidos",
	"1 caja",
	"1 frasco",
	"1 tubo"
];

var recetarioCategoriaEquivalencias = {
	"Indicaciones post extraccion": "Post extraccion",
	"Indicaciones post cirugia": "Post cirugia",
	"Indicaciones de higiene": "Higiene oral",
	"Indicaciones para protesis removible": "Protesis removible",
	"Indicaciones para PPR acrilica": "PPR acrilica",
	"Indicaciones para PPR flexible": "PPR flexible",
	"Indicaciones post endodoncia": "Endodoncia",
	"Indicaciones post limpieza/profilaxis": "Higiene oral",
	"Indicaciones de ortodoncia": "Ortodoncia",
	"Antibiotico + analgesico": "Medicacion y cuidados"
};

function recetarioEl(id) {
	return document.getElementById(id);
}

function recetarioTexto(valor, fallback) {
	var texto = (valor === undefined || valor === null) ? "" : String(valor).trim();
	return texto !== "" ? texto : (fallback || "");
}

function recetarioEscapeHtml(valor) {
	return String(valor === undefined || valor === null ? "" : valor)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function recetarioEscapeAttr(valor) {
	return recetarioEscapeHtml(valor).replace(/\n/g, "&#10;");
}

function recetarioDatalistHtml(id, opciones) {
	return "<datalist id='" + recetarioEscapeAttr(id) + "'>" + opciones.map(function (opcion) {
		return "<option value='" + recetarioEscapeAttr(opcion) + "'></option>";
	}).join("") + "</datalist>";
}

function recetarioCategoriaNormalizada(categoria) {
	var texto = recetarioTexto(categoria, "Indicaciones generales");
	return recetarioCategoriaEquivalencias[texto] || texto;
}

function recetarioOpcionesCategoriasHtml(categoriaActual) {
	var actual = recetarioCategoriaNormalizada(categoriaActual);
	var categorias = recetarioCategorias.slice();
	if (actual && categorias.indexOf(actual) === -1) {
		categorias.push(actual);
	}
	return categorias.map(function (categoria) {
		return "<option value='" + recetarioEscapeAttr(categoria) + "'>" + recetarioEscapeHtml(categoria) + "</option>";
	}).join("");
}

function recetarioResumenMedicamento(med) {
	var medicamento = recetarioTexto(med.medicamento, "");
	var presentacion = recetarioTexto(med.presentacion, "");
	var dosis = recetarioTexto(med.dosis, "");
	var frecuencia = recetarioTexto(med.frecuencia, "");
	var duracion = recetarioTexto(med.duracion, "");
	var cantidad = recetarioTexto(med.cantidad, "");
	var via = recetarioTexto(med.via, "");
	var partesUso = [dosis, frecuencia, duracion].filter(Boolean).join(" ");
	var resumen = [medicamento || "Medicamento sin nombre", presentacion].filter(Boolean).join(" ");
	if (partesUso) {
		resumen += " - " + partesUso;
	}
	if (via) {
		resumen += (partesUso ? ", " : " - ") + "via " + via;
	}
	if (cantidad) {
		resumen += "; cantidad: " + cantidad;
	}
	if (!medicamento && !presentacion && !partesUso && !cantidad && !via) {
		return "";
	}
	return resumen + ".";
}

function recetarioDescripcionPlantilla(plantilla) {
	var nombre = String(plantilla.nombre || "").toLowerCase();
	var categoria = String(plantilla.categoria || "").toLowerCase();
	var texto = nombre + " " + categoria;
	if (texto.indexOf("antibiotico") >= 0 || texto.indexOf("analgesico") >= 0) {
		return "Base para medicacion post procedimiento";
	}
	if (texto.indexOf("dolor") >= 0 || texto.indexOf("inflamacion") >= 0) {
		return "Indicaciones generales para manejo de molestias";
	}
	if (texto.indexOf("extraccion") >= 0) {
		return "Cuidados posteriores a extraccion dental";
	}
	if (texto.indexOf("ppr flexible") >= 0) {
		return "Indicaciones para uso y adaptacion";
	}
	if (texto.indexOf("ppr acrilica") >= 0 || texto.indexOf("protesis") >= 0) {
		return "Indicaciones para uso, limpieza y adaptacion";
	}
	if (texto.indexOf("higiene") >= 0 || texto.indexOf("profilaxis") >= 0) {
		return "Recomendaciones de higiene y cuidado";
	}
	if (texto.indexOf("endodoncia") >= 0) {
		return "Cuidados posteriores al tratamiento";
	}
	if (texto.indexOf("ortodoncia") >= 0) {
		return "Indicaciones para controles y molestias";
	}
	if (texto.indexOf("cirugia") >= 0) {
		return "Cuidados posteriores a procedimiento quirurgico";
	}
	return "Texto base editable antes de emitir";
}

function recetarioValorCampo(id) {
	var campo = recetarioEl(id);
	return campo ? String(campo.value || campo.textContent || "").trim() : "";
}

function recetarioSetValor(id, valor, fallback) {
	var campo = recetarioEl(id);
	if (!campo) {
		return;
	}
	var texto = recetarioTexto(valor, fallback || "Sin dato");
	if ("value" in campo) {
		campo.value = texto;
	} else {
		campo.textContent = texto;
	}
	campo.classList.toggle("recetario-context-value--empty", texto == "Sin dato" || texto.indexOf("Sin ") === 0);
}

function recetarioInicialesDoctor(nombre) {
	var partes = String(nombre || "").replace(/\([^)]*\)/g, " ").trim().split(/\s+/);
	var iniciales = "";
	partes.forEach(function (parte) {
		if (parte && iniciales.length < 2) {
			iniciales += parte.charAt(0).toUpperCase();
		}
	});
	return iniciales || "DR";
}

function recetarioFotoDoctorReal(urlFoto) {
	var foto = String(urlFoto || "").trim();
	if (foto == "" || foto == "null" || foto == "undefined") {
		return "";
	}
	if (foto.indexOf("/iconos/sinperfil.png") >= 0) {
		return "";
	}
	return foto;
}

function recetarioUsuarioLogueadoId() {
	return recetarioTexto(recetarioContexto.usuario_emisor_id, (typeof userid != "undefined" ? userid : ""));
}

function recetarioUsuarioLogueado() {
	return recetarioUsuarioLogueadoId() !== "";
}

function recetarioEstadoDoctorVisual() {
	if (recetarioContexto.doctor_estado) {
		return recetarioContexto.doctor_estado;
	}
	return "Usuario del sistema";
}

function renderDoctorEmisorRecetario() {
	var usuarioActual = recetarioTexto(recetarioContexto.usuario_actual_nombre, recetarioContexto.doctor_nombre || "");
	var nombre = recetarioTexto(recetarioContexto.doctor_nombre, usuarioActual || "Usuario logueado");
	var avatarNombre = nombre;
	var foto = recetarioFotoDoctorReal(recetarioContexto.doctor_foto_url || recetarioContexto.usuario_actual_foto_url);
	var estado = recetarioEstadoDoctorVisual();
	var nombreEl = recetarioEl("recetarioDoctor");
	var estadoEl = recetarioEl("recetarioDoctorEstado");
	var usuarioEl = recetarioEl("recetarioDoctorUsuarioActual");
	var inicialesEl = recetarioEl("recetarioDoctorIniciales");
	var fotoEl = recetarioEl("recetarioDoctorFoto");
	var avatarEl = recetarioEl("recetarioDoctorAvatar");
	var cardEl = recetarioEl("recetarioDoctorCard");

	if (nombreEl) {
		nombreEl.textContent = nombre;
	}
	if (estadoEl) {
		estadoEl.textContent = estado;
	}
	if (usuarioEl) {
		usuarioEl.textContent = "";
	}
	if (inicialesEl) {
		inicialesEl.textContent = recetarioInicialesDoctor(avatarNombre);
	}
	if (cardEl) {
		cardEl.classList.remove("recetario-doctor-card--invalid");
	}
	if (avatarEl) {
		avatarEl.classList.toggle("recetario-doctor-avatar--con-foto", foto !== "");
	}
	if (fotoEl) {
		fotoEl.onerror = function () {
			this.onerror = null;
			this.removeAttribute("src");
			this.style.display = "none";
			if (avatarEl) {
				avatarEl.classList.remove("recetario-doctor-avatar--con-foto");
			}
		};
		if (foto !== "") {
			fotoEl.style.display = "";
			fotoEl.src = foto;
		} else {
			fotoEl.removeAttribute("src");
			fotoEl.style.display = "none";
		}
	}
}

function recetarioFechaHoraVisual() {
	var fecha = new Date();
	var pad = function (n) { return n < 10 ? "0" + n : String(n); };
	return fecha.getFullYear() + "-" + pad(fecha.getMonth() + 1) + "-" + pad(fecha.getDate()) + " " + pad(fecha.getHours()) + ":" + pad(fecha.getMinutes());
}

function recetarioTieneFirmaVigente() {
	return recetarioFirma && recetarioFirma.estado == "vigente";
}

function recetarioFirmaInvalida() {
	return recetarioFirma && recetarioFirma.estado == "invalida";
}

function recetarioEstadoFirmaTexto() {
	if (recetarioTieneFirmaVigente()) {
		return "Firmado";
	}
	if (recetarioFirmaInvalida()) {
		return "Firma invalida por modificacion posterior";
	}
	return "Sin firma";
}

function renderEstadoFirmaRecetario() {
	var badge = recetarioEl("recetarioFirmaEstado");
	if (!badge) {
		return;
	}
	var texto = recetarioEstadoFirmaTexto();
	badge.textContent = texto;
	badge.className = "recetario-firma-badge";
	if (recetarioTieneFirmaVigente()) {
		badge.classList.add("recetario-firma-badge--firmado");
		badge.title = "Firmado por " + (recetarioFirma.nombre_firmante_snapshot || "") + " el " + (recetarioFirma.fecha_hora_firma || "");
		return;
	}
	if (recetarioFirmaInvalida()) {
		badge.classList.add("recetario-firma-badge--invalida");
		badge.title = texto;
		return;
	}
	badge.classList.add("recetario-firma-badge--sin");
	badge.title = "Sin firma";
}

function recetarioFirmaMiniHtml() {
	if (recetarioTieneFirmaVigente()) {
		return ""
			+ "<div class='recetario-preview-firma recetario-preview-firma--firmada'>"
			+ "  <img src='" + recetarioEscapeAttr(recetarioFirma.firma_imagen_path || "") + "' alt='Firma del emisor'>"
			+ "  <div><strong>Firmado por " + recetarioEscapeHtml(recetarioFirma.nombre_firmante_snapshot || "") + "</strong><br>"
			+ "  <span>" + recetarioEscapeHtml(recetarioFirma.fecha_hora_firma || "") + "</span></div>"
			+ "</div>";
	}
	if (recetarioFirmaInvalida()) {
		return "<div class='recetario-preview-firma recetario-preview-firma--invalida'>Firma invalida por modificacion posterior</div>";
	}
	return "<div class='recetario-preview-firma recetario-preview-firma--sin'>Firma: Pendiente</div>";
}

function recetarioAjax(funt, extra, success, errorCallback) {
	obtener_datos_user();
	var datos = new FormData();
	datos.append("useru", userid);
	datos.append("passu", passuser);
	datos.append("navegador", navegador);
	datos.append("funt", funt);

	extra = extra || {};
	Object.keys(extra).forEach(function (key) {
		datos.append(key, extra[key] === undefined || extra[key] === null ? "" : extra[key]);
	});

	$.ajax({
		data: datos,
		url: "/GoodVentaAsisCap/php_system/abmRecetarioIndicaciones.php",
		type: "post",
		cache: false,
		contentType: false,
		processData: false,
		error: function (jqXHR, textstatus, errorThrown) {
			if (typeof verCerrarEfectoCargando == "function") {
				verCerrarEfectoCargando("");
			}
			if (window.console && console.error) {
				console.error("Error AJAX recetario", funt, textstatus, errorThrown, jqXHR);
			}
			if (typeof errorCallback == "function") {
				errorCallback(jqXHR, textstatus, errorThrown);
			}
			manejadordeerroresjquery(jqXHR.status, textstatus, "abmventana");
		},
		success: function (responseText) {
			try {
				var datos = $.parseJSON(responseText);
				if (typeof success == "function") {
					success(datos, responseText);
				}
			} catch (error) {
				if (typeof verCerrarEfectoCargando == "function") {
					verCerrarEfectoCargando("");
				}
				ver_vetana_informativa("LO SENTIMOS HA OCURRIDO UN ERROR ");
				if (window.console && console.error) {
					console.error("Respuesta invalida recetario", funt, error, responseText);
				}
				if (typeof GuardarArchivosLog == "function") {
					GuardarArchivosLog("Error: " + error + " \r\n Consola: " + responseText);
				}
				if (typeof errorCallback == "function") {
					errorCallback(null, "parsererror", error, responseText);
				}
			}
		}
	});
}

function asegurarEstructuraRecetarioIndicaciones() {
	if (recetarioEl("divRecetarioIndicaciones")) {
		return;
	}

	var contenedor = document.createElement("div");
	contenedor.innerHTML = ''
		+ '<div class="recetario-backdrop" id="overlayRecetarioIndicaciones" style="display:none" onclick="verCerrarRecetarioIndicaciones(false)"></div>'
		+ '<div class="principal2 recetario-modal" id="divRecetarioIndicaciones" style="display:none">'
		+ '  <div class="recetario-shell">'
		+ '    <header class="recetario-topbar">'
		+ '      <div>'
		+ '        <p>Modulo clinico</p>'
		+ '        <h2>Recetario e Indicaciones</h2>'
		+ '        <span id="recetarioSubtitulo">Emision digital vinculada a venta, titular y ficha clinica.</span>'
		+ '      </div>'
		+ '      <div class="recetario-topbar__actions">'
		+ '        <button type="button" title="Limpiar" onclick="limpiarFormularioRecetarioIndicaciones()"><img src="/GoodVentaAsisCap/iconos/limpiarcampos.png" alt=""></button>'
		+ '        <button type="button" title="Cerrar" onclick="verCerrarRecetarioIndicaciones(false)"><img src="/GoodVentaAsisCap/iconos/botonCerrar.png" alt=""></button>'
		+ '      </div>'
		+ '    </header>'
		+ '    <div class="recetario-body">'
		+ '      <section class="recetario-main">'
		+ '        <div class="recetario-card recetario-context-card">'
		+ '          <div class="recetario-section-title"><span>Encabezado clinico-administrativo</span><strong id="recetarioEstadoDocumento">Borrador</strong></div>'
		+ '          <div class="recetario-context-grid">'
		+ '            <label><span>Paciente/beneficiario</span><div class="recetario-context-value" id="recetarioPaciente">Sin paciente</div></label>'
		+ '            <label><span>Titular contractual</span><div class="recetario-context-value" id="recetarioTitular">Sin titular</div></label>'
		+ '            <label><span>Cedula titular</span><div class="recetario-context-value" id="recetarioCedula">Sin cedula</div></label>'
		+ '            <label><span>Venta vinculada</span><select id="recetarioSelectorVenta" onchange="cambiarVentaRecetarioIndicaciones()"></select></label>'
		+ '            <label><span>Apodo de venta</span><div class="recetario-context-value" id="recetarioApodo">Sin apodo</div></label>'
		+ '            <label><span>Sucursal/local</span><div class="recetario-context-value" id="recetarioSucursal">Sin sucursal</div></label>'
		+ '            <label><span>Emisor</span><div class="recetario-doctor-card" id="recetarioDoctorCard">'
		+ '              <div class="recetario-doctor-avatar" id="recetarioDoctorAvatar"><span id="recetarioDoctorIniciales">DR</span><img id="recetarioDoctorFoto" alt=""></div>'
		+ '              <div class="recetario-doctor-info"><strong id="recetarioDoctor">Usuario logueado</strong><small id="recetarioDoctorUsuarioActual"></small><em id="recetarioDoctorEstado">Usuario del sistema</em></div>'
		+ '            </div></label>'
		+ '            <label><span>Fecha y hora</span><div class="recetario-context-value" id="recetarioFecha">Automatica</div></label>'
		+ '            <label><span>Firma</span><div class="recetario-firma-badge recetario-firma-badge--sin" id="recetarioFirmaEstado">Sin firma</div></label>'
		+ '            <label class="recetario-context-grid__wide"><span>Consulta/tratamiento relacionado</span><div class="recetario-context-value" id="recetarioConsulta">Sin consulta relacionada</div></label>'
		+ '          </div>'
		+ '          <div id="recetarioAlertasClinicas" class="recetario-alertas"></div>'
		+ '          <div id="recetarioEstadoValidacion" class="recetario-validacion"></div>'
		+ '          <div id="recetarioModoComplementaria" class="recetario-complementaria" style="display:none"></div>'
		+ '        </div>'
		+ '        <div class="recetario-card">'
		+ '          <div class="recetario-section-title"><span>RP / Receta</span><button type="button" id="btnRecetarioAgregarMedicamento" onclick="agregarMedicamentoRecetarioIndicaciones()">Agregar medicamento</button></div>'
		+ '          <div id="recetarioMedicamentosEditor" class="recetario-medicamentos"></div>'
		+ '        </div>'
		+ '        <div class="recetario-card">'
		+ '          <div class="recetario-section-title"><span>Indicaciones al paciente</span><button type="button" id="btnRecetarioAgregarIndicacion" onclick="agregarIndicacionRecetarioIndicaciones()">Agregar indicacion</button></div>'
		+ '          <div id="recetarioIndicacionesEditor" class="recetario-indicaciones"></div>'
		+ '        </div>'
		+ '        <div class="recetario-card">'
		+ '          <div class="recetario-section-title"><span>Observaciones generales</span></div>'
		+ '          <textarea id="recetarioObservacionesGenerales" class="recetario-textarea" placeholder="Ej. Control clinico en 7 dias."></textarea>'
		+ '        </div>'
		+ '      </section>'
		+ '      <aside class="recetario-side">'
		+ '        <details class="recetario-card recetario-templates" open>'
		+ '          <summary>Plantillas rapidas</summary>'
		+ '          <div id="recetarioPlantillasPanel" class="recetario-template-list"></div>'
		+ '        </details>'
		+ '        <div class="recetario-card recetario-preview-card">'
		+ '          <div class="recetario-section-title"><span>Vista previa</span><button type="button" onclick="actualizarPreviewRecetarioIndicaciones()">Actualizar</button></div>'
		+ '          <div id="recetarioPreview" class="recetario-preview"></div>'
		+ '        </div>'
		+ '        <div class="recetario-card">'
		+ '          <div class="recetario-section-title"><span>Historial del paciente</span></div>'
		+ '          <div id="recetarioHistorialLateral" class="recetario-history-list"></div>'
		+ '        </div>'
		+ '      </aside>'
		+ '    </div>'
		+ '    <footer class="recetario-footer">'
		+ '      <button type="button" class="recetario-btn recetario-btn--secondary" onclick="verCerrarRecetarioIndicaciones(false)">Cancelar</button>'
		+ '      <button type="button" class="recetario-btn recetario-btn--secondary" onclick="actualizarPreviewRecetarioIndicaciones()">Vista previa</button>'
		+ '      <button type="button" class="recetario-btn recetario-btn--draft" id="btnRecetarioBorrador" onclick="guardarRecetarioIndicaciones(\'guardar_borrador\')">Guardar borrador</button>'
		+ '      <button type="button" class="recetario-btn recetario-btn--primary" id="btnRecetarioEmitir" onclick="guardarRecetarioIndicaciones(\'emitir\')">Emitir documento</button>'
		+ '      <button type="button" class="recetario-btn recetario-btn--sign" id="btnRecetarioFirmar" onclick="abrirFirmaRecetarioIndicaciones()">Firmar documento</button>'
		+ '      <button type="button" class="recetario-btn recetario-btn--print" id="btnRecetarioImprimir" onclick="imprimirRecetarioIndicaciones(recetarioDocumentoId)" style="display:none">Imprimir</button>'
		+ '    </footer>'
		+ '  </div>'
		+ '</div>'
		+ '<div class="recetario-anular" id="modalAnularRecetarioIndicaciones" style="display:none">'
		+ '  <div class="recetario-anular__box">'
		+ '    <h3>Anular receta / indicaciones</h3>'
		+ '    <p>Ingrese el motivo obligatorio de anulacion. Esta accion queda auditada.</p>'
		+ '    <textarea id="motivoAnularRecetarioIndicaciones" placeholder="Motivo de anulacion"></textarea>'
		+ '    <div>'
		+ '      <button type="button" onclick="cerrarAnularRecetarioIndicaciones()">Cancelar</button>'
		+ '      <button type="button" onclick="confirmarAnularRecetarioIndicaciones()">Anular documento</button>'
		+ '    </div>'
		+ '  </div>'
		+ '</div>'
		+ '<div class="recetario-firma-modal" id="modalFirmaRecetarioIndicaciones" style="display:none">'
		+ '  <div class="recetario-firma-modal__box">'
		+ '    <div class="recetario-firma-modal__header">'
		+ '      <div><h3>Firma del emisor</h3><p>Firme dentro del recuadro. Esta firma quedara asociada al documento y al usuario actual.</p></div>'
		+ '      <button type="button" class="recetario-firma-modal__close" onclick="cerrarFirmaRecetarioIndicaciones()" title="Cerrar">x</button>'
		+ '    </div>'
		+ '    <div class="recetario-firma-modal__canvas-wrap">'
		+ '      <canvas id="canvasFirmaRecetarioIndicaciones"></canvas>'
		+ '    </div>'
		+ '    <div id="mensajeFirmaRecetarioIndicaciones" class="recetario-firma-modal__mensaje"></div>'
		+ '    <div class="recetario-firma-modal__actions">'
		+ '      <button type="button" onclick="limpiarFirmaRecetarioIndicaciones()">Limpiar firma</button>'
		+ '      <button type="button" onclick="cerrarFirmaRecetarioIndicaciones()">Cancelar</button>'
		+ '      <button type="button" class="recetario-firma-confirmar" onclick="confirmarFirmaRecetarioIndicaciones()">Confirmar firma</button>'
		+ '    </div>'
		+ '  </div>'
		+ '</div>'
		+ '<div class="recetario-firma-vista" id="modalVistaFirmaRecetarioIndicaciones" style="display:none">'
		+ '  <div class="recetario-firma-vista__box">'
		+ '    <div class="recetario-firma-vista__header">'
		+ '      <div><h3>Firma del emisor</h3><p id="vistaFirmaRecetarioDocumento">Documento relacionado</p></div>'
		+ '      <button type="button" onclick="cerrarVistaFirmaRecetarioIndicaciones()" title="Cerrar">x</button>'
		+ '    </div>'
		+ '    <div class="recetario-firma-vista__imagen"><img id="vistaFirmaRecetarioImagen" alt="Firma del emisor"></div>'
		+ '    <div class="recetario-firma-vista__meta">'
		+ '      <strong id="vistaFirmaRecetarioFirmante"></strong>'
		+ '      <span id="vistaFirmaRecetarioFecha"></span>'
		+ '      <em id="vistaFirmaRecetarioEstado"></em>'
		+ '    </div>'
		+ '    <div class="recetario-firma-vista__actions"><button type="button" onclick="cerrarVistaFirmaRecetarioIndicaciones()">Cerrar</button></div>'
		+ '  </div>'
		+ '</div>'
		+ recetarioDatalistHtml("recetarioFrecuenciaLista", recetarioFrecuenciasSugeridas)
		+ recetarioDatalistHtml("recetarioDuracionLista", recetarioDuracionesSugeridas)
		+ recetarioDatalistHtml("recetarioViaLista", recetarioViasSugeridas)
		+ recetarioDatalistHtml("recetarioCantidadLista", recetarioCantidadesSugeridas);

	document.body.appendChild(contenedor);
}

function verCerrarRecetarioIndicaciones(mostrar) {
	asegurarEstructuraRecetarioIndicaciones();
	var modal = recetarioEl("divRecetarioIndicaciones");
	var overlay = recetarioEl("overlayRecetarioIndicaciones");
	if (!modal || !overlay) {
		return;
	}
	if (mostrar === false || modal.style.display == "") {
		modal.style.display = "none";
		overlay.style.display = "none";
		return;
	}
	modal.style.display = "";
	overlay.style.display = "";
}

function limpiarFormularioRecetarioIndicaciones() {
	recetarioDocumentoId = "";
	recetarioDocumentoReemplazadoId = "";
	recetarioTipoCreacion = "";
	recetarioBloqueado = false;
	recetarioEstadoActual = "borrador";
	recetarioFirma = null;
	recetarioContexto = {};
	recetarioVentas = [];
	recetarioContextValidationStatus = "idle";
	recetarioContextValidationMessage = "";
	emisorStatus = "authorized";
	emisorMessage = "Usuario del sistema";
	recetarioGuardandoAccion = "";
	recetarioMedicamentos = [];
	recetarioIndicaciones = [];
	if (recetarioEl("recetarioObservacionesGenerales")) {
		recetarioEl("recetarioObservacionesGenerales").value = "";
	}
	if (recetarioEl("recetarioConfirmarVenta")) {
		recetarioEl("recetarioConfirmarVenta").checked = false;
	}
	cerrarFirmaRecetarioIndicaciones();
	agregarMedicamentoRecetarioIndicaciones();
	agregarIndicacionRecetarioIndicaciones();
	renderEstadoFormularioRecetario();
}

function obtenerContextoPreliminarRecetario(opciones) {
	opciones = opciones || {};
	var ventaId = opciones.venta_id || (typeof cod_ventaFKConsulta != "undefined" ? cod_ventaFKConsulta : "");
	var clienteId = opciones.cliente_id || (typeof cod_clienteConsulta != "undefined" ? cod_clienteConsulta : "");
	var numeroVenta = opciones.numero_venta || recetarioValorCampo("inptCodigoConsulta") || ventaId;
	var paciente = opciones.paciente_nombre || recetarioValorCampo("inptPacienteConsulta");
	var cedula = opciones.cedula_titular || recetarioValorCampo("inptCIConsulta");
	var apodo = opciones.apodo_venta || recetarioValorCampo("inptApodoConsulta");
	var sucursalId = opciones.sucursal_id || (typeof cod_localVentaConsulta != "undefined" ? cod_localVentaConsulta : "");
	var sucursalNombre = opciones.sucursal_nombre || (typeof nombre_localVentaConsulta != "undefined" ? nombre_localVentaConsulta : "");
	var doctor = "";
	if (recetarioEl("lblUser")) {
		doctor = String(recetarioEl("lblUser").textContent || "").trim();
	}
	if (doctor == "" && typeof localStorage != "undefined" && typeof userid != "undefined") {
		doctor = localStorage.getItem("nombreUsuario" + userid) || "";
	}
	var fotoDoctor = opciones.doctor_foto_url || (typeof fotocliente3 != "undefined" ? fotocliente3 : "");

	return {
		paciente_id: clienteId,
		beneficiario_id: "",
		paciente_nombre: paciente,
		titular_id: clienteId,
		titular_nombre: paciente,
		cedula_titular: cedula,
		venta_id: ventaId,
		numero_venta: numeroVenta,
		apodo_venta: apodo,
		consulta_id: opciones.consulta_id || "",
		consulta_resumen: opciones.consulta_resumen || (opciones.consulta_id ? "Consulta #" + opciones.consulta_id : ""),
		hilo_id: opciones.hilo_id || "",
		hilo_resumen: opciones.hilo_id ? "Hilo #" + opciones.hilo_id : "",
		doctor_id: (typeof userid != "undefined" ? userid : ""),
		doctor_nombre: doctor,
		doctor_foto_url: fotoDoctor,
		doctor_estado: "Usuario del sistema",
		perfil_clinico_autorizado: "SI",
		permiso_emitir_recetario: "SI",
		motivo_emisor: "",
		usuario_actual_id: (typeof userid != "undefined" ? userid : ""),
		usuario_actual_nombre: doctor,
		usuario_actual_foto_url: fotoDoctor,
		usuario_emisor_id: (typeof userid != "undefined" ? userid : ""),
		sucursal_id: sucursalId,
		sucursal_nombre: sucursalNombre,
		fecha_hora: recetarioFechaHoraVisual(),
		puede_borrador: "SI",
		puede_emitir: "SI",
		puede_imprimir: "NO",
		contexto_preliminar: "SI"
	};
}

function aplicarContextoPreliminarRecetario(opciones) {
	var contexto = obtenerContextoPreliminarRecetario(opciones);
	recetarioContextValidationStatus = "valid";
	recetarioContextValidationMessage = "";
	emisorStatus = "authorized";
	emisorMessage = "Usuario del sistema";
	recetarioContexto = contexto;
	recetarioEstadoActual = "borrador";
	recetarioFirma = null;
	recetarioVentas = [];
	if (contexto.venta_id) {
		recetarioVentas.push({
			venta_id: contexto.venta_id,
			numero_venta: contexto.numero_venta,
			apodo_venta: contexto.apodo_venta,
			rotulo: "Venta Nro. " + (contexto.numero_venta || contexto.venta_id) + " - " + recetarioTexto(contexto.titular_nombre, "Paciente") + (contexto.apodo_venta ? " (" + contexto.apodo_venta + ")" : "")
		});
	}
	renderContextoRecetarioIndicaciones([]);
}

function recetarioVentaSeleccionada() {
	var selector = recetarioEl("recetarioSelectorVenta");
	return selector ? String(selector.value || "").trim() : String(recetarioContexto.venta_id || "").trim();
}

function recetarioUsuarioDoctorMinimo() {
	return recetarioUsuarioLogueado();
}

function pendientesBorradorRecetario() {
	var pendientes = [];
	if (!recetarioUsuarioLogueado()) {
		pendientes.push("usuario logueado");
	}
	return pendientes;
}

function pendientesEmisionRecetario() {
	var pendientes = pendientesBorradorRecetario();
	if (obtenerMedicamentosValidosRecetario().length === 0 && obtenerIndicacionesValidasRecetario().length === 0) {
		pendientes.push("agregar al menos un medicamento o una indicacion");
	}
	return pendientes.filter(function (valor, index, lista) {
		return lista.indexOf(valor) === index;
	});
}

function renderValidacionBotonesRecetario() {
	var contenedor = recetarioEl("recetarioEstadoValidacion");
	if (!contenedor) {
		return;
	}
	var pendientesBorrador = pendientesBorradorRecetario();
	var pendientesEmision = pendientesEmisionRecetario();
	contenedor.innerHTML = "";

	var btnBorrador = recetarioEl("btnRecetarioBorrador");
	if (btnBorrador) {
		btnBorrador.title = pendientesBorrador.length > 0 ? "Falta: " + pendientesBorrador.join(", ") : "Guardar borrador";
	}
	var btnEmitir = recetarioEl("btnRecetarioEmitir");
	if (btnEmitir) {
		btnEmitir.title = pendientesEmision.length > 0 ? "Falta: " + pendientesEmision.join(", ") : "Emitir documento";
	}
}

function reintentarValidacionContextoRecetario() {
	cargarContextoRecetarioIndicaciones(recetarioUltimasOpcionesContexto || {});
}

function actualizarDisponibilidadAccionesRecetario() {
	var ventaSeleccionada = recetarioEl("recetarioSelectorVenta") && recetarioEl("recetarioSelectorVenta").value;
	var usuarioLogueado = recetarioUsuarioLogueado();
	var tieneContenido = obtenerMedicamentosValidosRecetario().length > 0 || obtenerIndicacionesValidasRecetario().length > 0;
	if (!ventaSeleccionada && recetarioEl("recetarioConfirmarVenta")) {
		recetarioEl("recetarioConfirmarVenta").checked = false;
	}
	var pendientesBorrador = pendientesBorradorRecetario();
	var pendientesEmision = pendientesEmisionRecetario();
	var botonBorrador = recetarioEl("btnRecetarioBorrador");
	if (botonBorrador) {
		botonBorrador.disabled = recetarioBloqueado || !usuarioLogueado || recetarioGuardandoAccion == "guardar_borrador";
		botonBorrador.textContent = recetarioGuardandoAccion == "guardar_borrador" ? "Guardando..." : "Guardar borrador";
	}
	var botonEmitir = recetarioEl("btnRecetarioEmitir");
	if (botonEmitir) {
		botonEmitir.disabled = recetarioBloqueado || !usuarioLogueado || !tieneContenido || recetarioGuardandoAccion == "emitir";
		botonEmitir.textContent = recetarioGuardandoAccion == "emitir" ? "Emitiendo..." : "Emitir documento";
	}
	var botonFirmar = recetarioEl("btnRecetarioFirmar");
	if (botonFirmar) {
		botonFirmar.disabled = !usuarioLogueado || !tieneContenido || recetarioEstadoActual == "anulada" || recetarioGuardandoAccion == "firma" || recetarioGuardandoAccion == "guardar_borrador";
		botonFirmar.textContent = recetarioGuardandoAccion == "firma" ? "Guardando firma..." : "Firmar documento";
		if (!tieneContenido) {
			botonFirmar.title = "Agregue al menos un medicamento o una indicacion antes de firmar.";
		} else if (recetarioTieneFirmaVigente()) {
			botonFirmar.title = "Documento firmado. Permite reemplazar la firma.";
		} else {
			botonFirmar.title = "Firma manuscrita digitalizada";
		}
	}
	if (recetarioEl("recetarioConfirmarVenta")) {
		recetarioEl("recetarioConfirmarVenta").disabled = recetarioBloqueado || !ventaSeleccionada;
	}
	var botonAgregarMedicamento = recetarioEl("btnRecetarioAgregarMedicamento");
	if (botonAgregarMedicamento) {
		botonAgregarMedicamento.disabled = recetarioBloqueado;
		botonAgregarMedicamento.title = "Agregar medicamento";
	}
	var botonAgregarIndicacion = recetarioEl("btnRecetarioAgregarIndicacion");
	if (botonAgregarIndicacion) {
		botonAgregarIndicacion.disabled = recetarioBloqueado;
		botonAgregarIndicacion.title = "Agregar indicacion";
	}
	document.querySelectorAll("#recetarioMedicamentosEditor .recetario-row-remove, #recetarioIndicacionesEditor .recetario-row-remove").forEach(function (boton) {
		boton.disabled = recetarioBloqueado;
	});
	renderValidacionBotonesRecetario();
}

function abrirRecetarioIndicacionesDesdeFicha() {
	if (typeof cod_ventaFKConsulta == "undefined" || String(cod_ventaFKConsulta || "").trim() == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA DEL PACIENTE");
		return;
	}
	var opciones = {
		venta_id: cod_ventaFKConsulta,
		cliente_id: (typeof cod_clienteConsulta != "undefined" ? cod_clienteConsulta : ""),
		sucursal_id: (typeof cod_localVentaConsulta != "undefined" ? cod_localVentaConsulta : ""),
		sucursal_nombre: (typeof nombre_localVentaConsulta != "undefined" ? nombre_localVentaConsulta : "")
	};
	abrirRecetarioIndicaciones(opciones);
}

function abrirRecetarioIndicacionesDesdeConsultaId(consultaId) {
	if (typeof cod_ventaFKConsulta == "undefined" || String(cod_ventaFKConsulta || "").trim() == "") {
		ver_vetana_informativa("FALTO SELECCIONAR UNA VENTA DEL PACIENTE");
		return;
	}
	var opciones = {
		venta_id: cod_ventaFKConsulta,
		cliente_id: (typeof cod_clienteConsulta != "undefined" ? cod_clienteConsulta : ""),
		consulta_id: consultaId || "",
		sucursal_id: (typeof cod_localVentaConsulta != "undefined" ? cod_localVentaConsulta : ""),
		sucursal_nombre: (typeof nombre_localVentaConsulta != "undefined" ? nombre_localVentaConsulta : "")
	};
	abrirRecetarioIndicaciones(opciones);
}

function abrirRecetarioIndicacionesDesdeHilo(codInterConsulta, ventaId, clienteId) {
	var venta = ventaId || (typeof cod_ventaFKConsulta != "undefined" ? cod_ventaFKConsulta : "");
	if (String(venta || "").trim() == "") {
		ver_vetana_informativa("EL HILO NO TIENE UNA VENTA VINCULADA PARA EMITIR RECETA O INDICACIONES");
		return;
	}
	abrirRecetarioIndicaciones({
		venta_id: venta,
		cliente_id: clienteId || (typeof cod_clienteConsulta != "undefined" ? cod_clienteConsulta : ""),
		hilo_id: codInterConsulta || ""
	});
}

function abrirRecetarioIndicaciones(opciones) {
	asegurarEstructuraRecetarioIndicaciones();
	limpiarFormularioRecetarioIndicaciones();
	opciones = opciones || {};
	recetarioDocumentoReemplazadoId = opciones.documento_reemplazado_id || "";
	recetarioTipoCreacion = opciones.tipo_creacion || "";
	aplicarContextoPreliminarRecetario(opciones);
	cargarContextoRecetarioIndicaciones(opciones);
	verCerrarRecetarioIndicaciones(true);
}

function cargarContextoRecetarioIndicaciones(opciones) {
	opciones = opciones || {};
	recetarioUltimasOpcionesContexto = opciones;
	recetarioContextValidationStatus = "valid";
	recetarioContextValidationMessage = "";
	renderEstadoFormularioRecetario();
	if (typeof verCerrarEfectoCargando == "function") {
		verCerrarEfectoCargando("1");
	}
	recetarioAjax("obtener_contexto", {
		venta_id: opciones.venta_id || "",
		cliente_id: opciones.cliente_id || "",
		consulta_id: opciones.consulta_id || "",
		hilo_id: opciones.hilo_id || ""
	}, function (datos) {
		if (typeof verCerrarEfectoCargando == "function") {
			verCerrarEfectoCargando("");
		}
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			recetarioContextValidationStatus = "valid";
			recetarioContextValidationMessage = "";
			emisorStatus = "authorized";
			emisorMessage = "Usuario del sistema";
			recetarioContexto.puede_borrador = "SI";
			recetarioContexto.puede_emitir = "SI";
			renderContextoRecetarioIndicaciones([]);
			return;
		}
		recetarioContextValidationStatus = "valid";
		recetarioContextValidationMessage = "";
		recetarioContexto = datos["2"] || {};
		emisorStatus = "authorized";
		emisorMessage = "Usuario del sistema";
		recetarioVentas = datos["3"] || [];
		recetarioPlantillas = datos["5"] || [];
		renderContextoRecetarioIndicaciones(datos["4"] || []);
		renderPlantillasRecetarioIndicaciones();
		buscarHistorialRecetariosLateral();
		actualizarPreviewRecetarioIndicaciones();
	}, function () {
		if (typeof verCerrarEfectoCargando == "function") {
			verCerrarEfectoCargando("");
		}
		recetarioContextValidationStatus = "valid";
		recetarioContextValidationMessage = "";
		emisorStatus = "authorized";
		emisorMessage = "Usuario del sistema";
		recetarioContexto.puede_borrador = "SI";
		recetarioContexto.puede_emitir = "SI";
		renderContextoRecetarioIndicaciones([]);
	});
}

function renderContextoRecetarioIndicaciones(alertas) {
	recetarioSetValor("recetarioPaciente", recetarioContexto.paciente_nombre, "Sin paciente");
	recetarioSetValor("recetarioTitular", recetarioContexto.titular_nombre, "Sin titular");
	recetarioSetValor("recetarioCedula", recetarioContexto.cedula_titular, "Sin cedula");
	recetarioSetValor("recetarioApodo", recetarioContexto.apodo_venta, "Sin apodo");
	recetarioSetValor("recetarioSucursal", recetarioContexto.sucursal_nombre, "Sin sucursal");
	renderDoctorEmisorRecetario();
	recetarioSetValor("recetarioFecha", recetarioContexto.fecha_hora, "Automatica al emitir");
	var relacionClinica = recetarioTexto(recetarioContexto.consulta_resumen, recetarioContexto.consulta_id ? "Consulta #" + recetarioContexto.consulta_id : "Sin consulta relacionada");
	if (recetarioContexto.hilo_resumen) {
		relacionClinica += " | " + recetarioContexto.hilo_resumen;
	}
	recetarioSetValor("recetarioConsulta", relacionClinica, "Sin consulta relacionada");

	var selector = recetarioEl("recetarioSelectorVenta");
	selector.innerHTML = "";
	if (recetarioVentas.length > 1) {
		selector.innerHTML = "<option value=''>Seleccionar venta vinculada</option>";
	}
	recetarioVentas.forEach(function (venta) {
		var option = document.createElement("option");
		option.value = venta.venta_id;
		option.textContent = venta.rotulo || ("Venta Nro. " + (venta.numero_venta || venta.venta_id) + (venta.apodo_venta ? " (" + venta.apodo_venta + ")" : ""));
		if (String(venta.venta_id) == String(recetarioContexto.venta_id)) {
			option.selected = true;
		}
		selector.appendChild(option);
	});
	if (recetarioVentas.length === 0 && recetarioContexto.venta_id) {
		selector.innerHTML = "<option value='" + recetarioEscapeAttr(recetarioContexto.venta_id) + "' selected>Nro. " + recetarioEscapeHtml(recetarioContexto.numero_venta || recetarioContexto.venta_id) + "</option>";
	}

	recetarioEl("recetarioAlertasClinicas").innerHTML = "";

	if (recetarioDocumentoReemplazadoId) {
		recetarioEl("recetarioModoComplementaria").style.display = "";
		recetarioEl("recetarioModoComplementaria").innerHTML = "Documento complementario vinculado al recetario #" + recetarioEscapeHtml(recetarioDocumentoReemplazadoId) + ".";
	} else {
		recetarioEl("recetarioModoComplementaria").style.display = "none";
		recetarioEl("recetarioModoComplementaria").innerHTML = "";
	}

	if (recetarioEl("recetarioConfirmarVenta")) {
		recetarioEl("recetarioConfirmarVenta").disabled = !selector.value || recetarioBloqueado;
	}
	renderEstadoFormularioRecetario();
}

function cambiarVentaRecetarioIndicaciones() {
	var ventaId = recetarioEl("recetarioSelectorVenta").value;
	if (!ventaId) {
		if (recetarioEl("recetarioConfirmarVenta")) {
			recetarioEl("recetarioConfirmarVenta").checked = false;
		}
		renderEstadoFormularioRecetario();
		return;
	}
	if (recetarioEl("recetarioConfirmarVenta")) {
		recetarioEl("recetarioConfirmarVenta").checked = false;
	}
	var ventaSeleccionada = null;
	recetarioVentas.forEach(function (venta) {
		if (String(venta.venta_id) == String(ventaId)) {
			ventaSeleccionada = venta;
		}
	});
	if (ventaSeleccionada) {
		recetarioContexto.venta_id = ventaSeleccionada.venta_id || ventaId;
		recetarioContexto.numero_venta = ventaSeleccionada.numero_venta || ventaId;
		recetarioContexto.apodo_venta = ventaSeleccionada.apodo_venta || "";
		recetarioContexto.sucursal_id = ventaSeleccionada.sucursal_id || "";
		recetarioContexto.sucursal_nombre = ventaSeleccionada.sucursal_nombre || "";
		renderContextoRecetarioIndicaciones([]);
	}
	cargarContextoRecetarioIndicaciones({
		venta_id: ventaId,
		cliente_id: recetarioContexto.titular_id || recetarioContexto.paciente_id || "",
		consulta_id: "",
		hilo_id: ""
	});
}

function renderEstadoFormularioRecetario() {
	var estado = recetarioBloqueado ? "Emitida" : (recetarioDocumentoId ? "Borrador guardado" : "Borrador");
	if (recetarioTipoCreacion == "complementaria") {
		estado = recetarioBloqueado ? "Complementaria emitida" : "Complementaria";
	}
	if (recetarioEl("recetarioEstadoDocumento")) {
		recetarioEl("recetarioEstadoDocumento").textContent = estado;
	}
	if (recetarioEl("btnRecetarioImprimir")) {
		recetarioEl("btnRecetarioImprimir").style.display = recetarioDocumentoId && recetarioContexto.puede_imprimir !== "NO" ? "" : "none";
	}
	renderEstadoFirmaRecetario();
	renderMedicamentosRecetarioIndicaciones();
	renderIndicacionesRecetarioIndicaciones();
	var ventaSeleccionada = recetarioEl("recetarioSelectorVenta") && recetarioEl("recetarioSelectorVenta").value;
	var inputs = document.querySelectorAll("#divRecetarioIndicaciones input, #divRecetarioIndicaciones textarea, #divRecetarioIndicaciones select");
	var bloquearClinico = recetarioBloqueado;
	inputs.forEach(function (input) {
		if (input.id == "recetarioSelectorVenta") {
			input.disabled = recetarioBloqueado;
			return;
		}
		if (input.id == "recetarioConfirmarVenta") {
			input.disabled = recetarioBloqueado || !ventaSeleccionada;
			return;
		}
		if (input.closest(".recetario-context-card")) {
			return;
		}
		input.disabled = bloquearClinico;
	});
	actualizarDisponibilidadAccionesRecetario();
}

function agregarMedicamentoRecetarioIndicaciones(valor) {
	recetarioMedicamentos.push(valor || {
		medicamento: "",
		presentacion: "",
		dosis: "",
		frecuencia: "",
		duracion: "",
		cantidad: "",
		via: "",
		observaciones: ""
	});
	renderMedicamentosRecetarioIndicaciones();
	actualizarDisponibilidadAccionesRecetario();
}

function eliminarMedicamentoRecetarioIndicaciones(index) {
	if (recetarioBloqueado) {
		return;
	}
	recetarioMedicamentos.splice(index, 1);
	if (recetarioMedicamentos.length === 0) {
		agregarMedicamentoRecetarioIndicaciones();
		return;
	}
	renderMedicamentosRecetarioIndicaciones();
	actualizarPreviewRecetarioIndicaciones();
	actualizarDisponibilidadAccionesRecetario();
}

function actualizarMedicamentoRecetarioIndicaciones(index, campo, valor) {
	if (!recetarioMedicamentos[index]) {
		return;
	}
	recetarioMedicamentos[index][campo] = valor;
	actualizarResumenMedicamentoRecetario(index);
	actualizarDisponibilidadAccionesRecetario();
}

function actualizarResumenMedicamentoRecetario(index) {
	var resumen = recetarioEl("recetarioMedicamentoResumen" + index);
	if (!resumen || !recetarioMedicamentos[index]) {
		return;
	}
	var texto = recetarioResumenMedicamento(recetarioMedicamentos[index]);
	resumen.textContent = texto;
	resumen.style.display = texto ? "" : "none";
}

function renderMedicamentosRecetarioIndicaciones() {
	var contenedor = recetarioEl("recetarioMedicamentosEditor");
	if (!contenedor) {
		return;
	}
	var html = "";
	recetarioMedicamentos.forEach(function (med, index) {
		var resumen = recetarioResumenMedicamento(med);
		html += ""
			+ "<div class='recetario-medicamento-row'>"
			+ "  <label><span>Medicamento</span><input value='" + recetarioEscapeAttr(med.medicamento) + "' placeholder='Ej. Paracetamol' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'medicamento', this.value)\"></label>"
			+ "  <label><span>Presentacion</span><input value='" + recetarioEscapeAttr(med.presentacion) + "' placeholder='Ej. comprimido 500 mg' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'presentacion', this.value)\"></label>"
			+ "  <label><span>Dosis</span><input value='" + recetarioEscapeAttr(med.dosis) + "' placeholder='Ej. 1 comprimido' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'dosis', this.value)\"></label>"
			+ "  <label><span>Frecuencia</span><input value='" + recetarioEscapeAttr(med.frecuencia) + "' placeholder='Ej. cada 8 horas' list='recetarioFrecuenciaLista' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'frecuencia', this.value)\"></label>"
			+ "  <label><span>Duracion</span><input value='" + recetarioEscapeAttr(med.duracion) + "' placeholder='Ej. por 5 dias' list='recetarioDuracionLista' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'duracion', this.value)\"></label>"
			+ "  <label><span>Cantidad</span><input value='" + recetarioEscapeAttr(med.cantidad) + "' placeholder='Ej. 10 comprimidos' list='recetarioCantidadLista' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'cantidad', this.value)\"></label>"
			+ "  <label><span>Via</span><input value='" + recetarioEscapeAttr(med.via) + "' placeholder='Ej. oral' list='recetarioViaLista' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'via', this.value)\"></label>"
			+ "  <label class='recetario-row-wide'><span>Observaciones</span><input value='" + recetarioEscapeAttr(med.observaciones) + "' placeholder='Ej. tomar despues de las comidas' oninput=\"actualizarMedicamentoRecetarioIndicaciones(" + index + ", 'observaciones', this.value)\"></label>"
			+ "  <div class='recetario-medicamento-resumen' id='recetarioMedicamentoResumen" + index + "' style='" + (resumen ? "" : "display:none") + "'>" + recetarioEscapeHtml(resumen) + "</div>"
			+ "  <button type='button' class='recetario-row-remove' onclick='eliminarMedicamentoRecetarioIndicaciones(" + index + ")' title='Quitar'>x</button>"
			+ "</div>";
	});
	contenedor.innerHTML = html;
}

function agregarIndicacionRecetarioIndicaciones(valor) {
	recetarioIndicaciones.push(valor || {
		categoria: "Indicaciones generales",
		texto: ""
	});
	renderIndicacionesRecetarioIndicaciones();
	actualizarDisponibilidadAccionesRecetario();
}

function eliminarIndicacionRecetarioIndicaciones(index) {
	if (recetarioBloqueado) {
		return;
	}
	recetarioIndicaciones.splice(index, 1);
	if (recetarioIndicaciones.length === 0) {
		agregarIndicacionRecetarioIndicaciones();
		return;
	}
	renderIndicacionesRecetarioIndicaciones();
	actualizarPreviewRecetarioIndicaciones();
	actualizarDisponibilidadAccionesRecetario();
}

function actualizarIndicacionRecetarioIndicaciones(index, campo, valor) {
	if (!recetarioIndicaciones[index]) {
		return;
	}
	recetarioIndicaciones[index][campo] = valor;
	actualizarDisponibilidadAccionesRecetario();
}

function renderIndicacionesRecetarioIndicaciones() {
	var contenedor = recetarioEl("recetarioIndicacionesEditor");
	if (!contenedor) {
		return;
	}
	var html = "";
	recetarioIndicaciones.forEach(function (indicacion, index) {
		var categoriaActual = recetarioCategoriaNormalizada(indicacion.categoria);
		var opcionesCategorias = recetarioOpcionesCategoriasHtml(categoriaActual);
		html += ""
			+ "<div class='recetario-indicacion-row'>"
			+ "  <label><span>Categoria</span><select onchange=\"actualizarIndicacionRecetarioIndicaciones(" + index + ", 'categoria', this.value)\">" + opcionesCategorias + "</select></label>"
			+ "  <label class='recetario-row-wide'><span>Texto</span><textarea oninput=\"actualizarIndicacionRecetarioIndicaciones(" + index + ", 'texto', this.value)\" placeholder='Ej. No mojar ni manipular la zona tratada durante las primeras 24 horas.'>" + recetarioEscapeHtml(indicacion.texto) + "</textarea></label>"
			+ "  <button type='button' class='recetario-row-remove' onclick='eliminarIndicacionRecetarioIndicaciones(" + index + ")' title='Quitar'>x</button>"
			+ "</div>";
	});
	contenedor.innerHTML = html;
	recetarioIndicaciones.forEach(function (indicacion, index) {
		var fila = contenedor.querySelectorAll(".recetario-indicacion-row")[index];
		var select = fila ? fila.querySelector("select") : null;
		if (select) {
			select.value = recetarioCategoriaNormalizada(indicacion.categoria);
		}
	});
}

function renderPlantillasRecetarioIndicaciones() {
	var panel = recetarioEl("recetarioPlantillasPanel");
	if (!panel) {
		return;
	}
	if (!recetarioPlantillas || recetarioPlantillas.length === 0) {
		panel.innerHTML = "<div class='recetario-empty'>No hay plantillas activas.</div>";
		return;
	}
	panel.innerHTML = recetarioPlantillas.map(function (plantilla, index) {
		var categoria = recetarioCategoriaNormalizada(plantilla.categoria || plantilla.tipo || "Indicaciones generales");
		return "<button type='button' onclick='aplicarPlantillaRecetarioIndicaciones(" + index + ")'>"
			+ "<strong>" + recetarioEscapeHtml(plantilla.nombre) + "</strong>"
			+ "<span>" + recetarioEscapeHtml(recetarioDescripcionPlantilla(plantilla)) + "</span>"
			+ "<em>" + recetarioEscapeHtml(categoria) + "</em>"
			+ "<b>Aplicar</b>"
			+ "</button>";
	}).join("");
}

function limpiarFilasVaciasAntesDePlantilla() {
	recetarioMedicamentos = recetarioMedicamentos.filter(function (med) {
		return Object.keys(med).some(function (key) { return String(med[key] || "").trim() !== ""; });
	});
	recetarioIndicaciones = recetarioIndicaciones.filter(function (ind) {
		return String(ind.texto || "").trim() !== "";
	});
}

function aplicarPlantillaRecetarioIndicaciones(index) {
	if (recetarioBloqueado) {
		return;
	}
	var plantilla = recetarioPlantillas[index];
	if (!plantilla) {
		return;
	}
	var contenido = {};
	try {
		contenido = JSON.parse(plantilla.contenido_json || "{}");
	} catch (error) {
		ver_vetana_informativa("La plantilla seleccionada no tiene un formato valido.");
		return;
	}
	limpiarFilasVaciasAntesDePlantilla();
	(contenido.medicamentos || []).forEach(function (med) {
		recetarioMedicamentos.push({
			medicamento: med.medicamento || "",
			presentacion: med.presentacion || "",
			dosis: med.dosis || "",
			frecuencia: med.frecuencia || "",
			duracion: med.duracion || "",
			cantidad: med.cantidad || "",
			via: med.via || "",
			observaciones: med.observaciones || ""
		});
	});
	(contenido.indicaciones || []).forEach(function (ind) {
		recetarioIndicaciones.push({
			categoria: recetarioCategoriaNormalizada(ind.categoria || plantilla.categoria || "Indicaciones generales"),
			texto: ind.texto || ""
		});
	});
	if (recetarioMedicamentos.length === 0) {
		agregarMedicamentoRecetarioIndicaciones();
	} else {
		renderMedicamentosRecetarioIndicaciones();
	}
	if (recetarioIndicaciones.length === 0) {
		agregarIndicacionRecetarioIndicaciones();
	} else {
		renderIndicacionesRecetarioIndicaciones();
	}
	actualizarPreviewRecetarioIndicaciones();
}

function obtenerMedicamentosValidosRecetario() {
	return recetarioMedicamentos.filter(function (med) {
		return Object.keys(med).some(function (key) { return String(med[key] || "").trim() !== ""; });
	});
}

function obtenerIndicacionesValidasRecetario() {
	return recetarioIndicaciones.filter(function (indicacion) {
		return String(indicacion.texto || "").trim() !== "";
	});
}

function actualizarPreviewRecetarioIndicaciones() {
	var preview = recetarioEl("recetarioPreview");
	if (!preview) {
		return;
	}
	var medicamentos = obtenerMedicamentosValidosRecetario();
	var indicaciones = obtenerIndicacionesValidasRecetario();
	var venta = recetarioTexto(recetarioContexto.numero_venta, recetarioContexto.venta_id);
	var html = ""
		+ "<div class='recetario-preview-doc'>"
		+ "<h3>Recetario e Indicaciones</h3>"
		+ "<p><b>Paciente:</b> " + recetarioEscapeHtml(recetarioContexto.paciente_nombre || "") + "</p>"
		+ "<p><b>Titular:</b> " + recetarioEscapeHtml(recetarioContexto.titular_nombre || "") + " / CI " + recetarioEscapeHtml(recetarioContexto.cedula_titular || "") + "</p>"
		+ "<p><b>Venta:</b> Nro. " + recetarioEscapeHtml(venta) + (recetarioContexto.apodo_venta ? " - " + recetarioEscapeHtml(recetarioContexto.apodo_venta) : "") + "</p>"
		+ "<p><b>Doctor:</b> " + recetarioEscapeHtml(recetarioContexto.doctor_nombre || "") + "</p>"
		+ recetarioFirmaMiniHtml()
		+ "<h4>RP / Receta</h4>";
	if (medicamentos.length === 0) {
		html += "<p class='recetario-muted'>Sin medicamentos cargados.</p>";
	} else {
		html += "<ol>";
		medicamentos.forEach(function (med) {
			var detalle = [med.presentacion, med.dosis, med.frecuencia, med.duracion, med.cantidad, med.via].filter(Boolean).join(" / ");
			html += "<li><strong>" + recetarioEscapeHtml(med.medicamento || "Medicamento") + "</strong><br>" + recetarioEscapeHtml(detalle) + (med.observaciones ? "<br><small>" + recetarioEscapeHtml(med.observaciones) + "</small>" : "") + "</li>";
		});
		html += "</ol>";
	}
	html += "<h4>Indicaciones</h4>";
	if (indicaciones.length === 0) {
		html += "<p class='recetario-muted'>Sin indicaciones cargadas.</p>";
	} else {
		html += "<ol>";
		indicaciones.forEach(function (ind) {
			html += "<li><strong>" + recetarioEscapeHtml(ind.categoria || "Indicacion") + "</strong><br>" + recetarioEscapeHtml(ind.texto).replace(/\n/g, "<br>") + "</li>";
		});
		html += "</ol>";
	}
	html += "</div>";
	preview.innerHTML = html;
}

function validarFormularioRecetario(accion) {
	if (!recetarioUsuarioLogueado()) {
		ver_vetana_informativa("No se pudo identificar el usuario logueado.");
		return false;
	}
	var pendientes = accion == "emitir" ? pendientesEmisionRecetario() : pendientesBorradorRecetario();
	if (pendientes.length > 0) {
		var prefijo = accion == "emitir" ? "Para emitir falta: " : "Para guardar borrador falta: ";
		ver_vetana_informativa(prefijo + pendientes.join(", "));
		return false;
	}
	var medicamentos = obtenerMedicamentosValidosRecetario();
	var indicaciones = obtenerIndicacionesValidasRecetario();
	if (accion == "emitir" && medicamentos.length === 0 && indicaciones.length === 0) {
		ver_vetana_informativa("DEBE CARGAR AL MENOS UN MEDICAMENTO O UNA INDICACION");
		return false;
	}
	return true;
}

function guardarRecetarioIndicaciones(accion, despuesGuardar, opciones) {
	opciones = opciones || {};
	if (!validarFormularioRecetario(accion == "emitir" ? "emitir" : "borrador")) {
		if (typeof despuesGuardar == "function") {
			despuesGuardar(false);
		}
		return;
	}
	recetarioGuardandoAccion = accion;
	renderEstadoFormularioRecetario();
	if (typeof verCerrarEfectoCargando == "function") {
		verCerrarEfectoCargando("1");
	}
	recetarioAjax(accion, {
		id: recetarioDocumentoId,
		venta_id: recetarioEl("recetarioSelectorVenta") ? recetarioEl("recetarioSelectorVenta").value : "",
		cliente_id: recetarioContexto.titular_id || recetarioContexto.paciente_id || "",
		consulta_id: recetarioContexto.consulta_id || "",
		hilo_id: recetarioContexto.hilo_id || "",
		documento_reemplazado_id: recetarioDocumentoReemplazadoId || "",
		tipo_creacion: recetarioTipoCreacion || "",
		confirmacion_venta: recetarioEl("recetarioConfirmarVenta") && recetarioEl("recetarioConfirmarVenta").checked ? "SI" : "NO",
		observaciones_generales: recetarioEl("recetarioObservacionesGenerales").value || "",
		medicamentos: JSON.stringify(obtenerMedicamentosValidosRecetario()),
		indicaciones: JSON.stringify(obtenerIndicacionesValidasRecetario())
	}, function (datos) {
		if (typeof verCerrarEfectoCargando == "function") {
			verCerrarEfectoCargando("");
		}
		recetarioGuardandoAccion = "";
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			renderEstadoFormularioRecetario();
			ver_vetana_informativa(datos.mensaje || "No se pudo guardar el documento");
			if (typeof despuesGuardar == "function") {
				despuesGuardar(false, datos);
			}
			return;
		}
		recetarioDocumentoId = datos["2"];
		if (datos["3"]) {
			cargarDocumentoRecetarioEnFormulario(datos["3"]);
		} else {
			recetarioContextValidationStatus = "valid";
			recetarioContextValidationMessage = "";
		}
		if (accion == "emitir") {
			recetarioBloqueado = true;
			if (!opciones.silencioso) {
				ver_vetana_informativa("DOCUMENTO EMITIDO CORRECTAMENTE");
			}
		} else {
			if (!opciones.silencioso) {
				ver_vetana_informativa("BORRADOR GUARDADO CORRECTAMENTE");
			}
		}
		renderEstadoFormularioRecetario();
		buscarHistorialRecetariosDesdeConsulta();
		buscarHistorialRecetariosLateral();
		actualizarPreviewRecetarioIndicaciones();
		if (typeof despuesGuardar == "function") {
			despuesGuardar(true, datos);
		}
	}, function () {
		recetarioGuardandoAccion = "";
		renderEstadoFormularioRecetario();
		ver_vetana_informativa("No se pudo guardar el documento. Revise la conexion o intente nuevamente.");
		if (typeof despuesGuardar == "function") {
			despuesGuardar(false);
		}
	});
}

function abrirVistaFirmaRecetarioIndicaciones(imagen, firmante, fecha, estado, documento) {
	asegurarEstructuraRecetarioIndicaciones();
	var modal = recetarioEl("modalVistaFirmaRecetarioIndicaciones");
	var img = recetarioEl("vistaFirmaRecetarioImagen");
	if (!modal || !img) {
		return;
	}
	img.src = imagen || "";
	if (recetarioEl("vistaFirmaRecetarioFirmante")) {
		recetarioEl("vistaFirmaRecetarioFirmante").textContent = firmante || "Firmante sin nombre";
	}
	if (recetarioEl("vistaFirmaRecetarioFecha")) {
		recetarioEl("vistaFirmaRecetarioFecha").textContent = fecha ? "Firmado el " + fecha : "Fecha de firma no disponible";
	}
	if (recetarioEl("vistaFirmaRecetarioEstado")) {
		recetarioEl("vistaFirmaRecetarioEstado").textContent = estado || "Firmado";
	}
	if (recetarioEl("vistaFirmaRecetarioDocumento")) {
		recetarioEl("vistaFirmaRecetarioDocumento").textContent = documento ? "Documento " + documento : "Documento relacionado";
	}
	modal.style.display = "";
}

function cerrarVistaFirmaRecetarioIndicaciones() {
	var modal = recetarioEl("modalVistaFirmaRecetarioIndicaciones");
	if (modal) {
		modal.style.display = "none";
	}
}

function abrirFirmaRecetarioIndicaciones() {
	var tieneContenido = obtenerMedicamentosValidosRecetario().length > 0 || obtenerIndicacionesValidasRecetario().length > 0;
	if (!tieneContenido) {
		ver_vetana_informativa("Agregue al menos un medicamento o una indicacion antes de firmar.");
		return;
	}
	if (!recetarioUsuarioLogueado()) {
		ver_vetana_informativa("No se pudo identificar el usuario logueado.");
		return;
	}
	if (recetarioEstadoActual == "anulada") {
		ver_vetana_informativa("No se puede firmar un documento anulado.");
		return;
	}
	if (!recetarioDocumentoId) {
		guardarRecetarioIndicaciones("guardar_borrador", function (ok) {
			if (ok) {
				abrirFirmaRecetarioIndicaciones();
			}
		}, { silencioso: true });
		return;
	}
	if (recetarioTieneFirmaVigente() && !window.confirm("Este documento ya tiene una firma. Desea reemplazarla?")) {
		return;
	}
	abrirModalFirmaRecetarioIndicaciones();
}

function abrirModalFirmaRecetarioIndicaciones() {
	asegurarEstructuraRecetarioIndicaciones();
	var modal = recetarioEl("modalFirmaRecetarioIndicaciones");
	if (!modal) {
		return;
	}
	modal.style.display = "";
	recetarioMostrarMensajeFirma("", "");
	setTimeout(function () {
		prepararCanvasFirmaRecetarioIndicaciones();
	}, 40);
}

function cerrarFirmaRecetarioIndicaciones() {
	var modal = recetarioEl("modalFirmaRecetarioIndicaciones");
	if (modal) {
		modal.style.display = "none";
	}
}

function recetarioMostrarMensajeFirma(texto, tipo) {
	var mensaje = recetarioEl("mensajeFirmaRecetarioIndicaciones");
	if (!mensaje) {
		return;
	}
	mensaje.textContent = texto || "";
	mensaje.className = "recetario-firma-modal__mensaje";
	if (tipo) {
		mensaje.classList.add("recetario-firma-modal__mensaje--" + tipo);
	}
}

function prepararCanvasFirmaRecetarioIndicaciones() {
	var canvas = recetarioEl("canvasFirmaRecetarioIndicaciones");
	if (!canvas) {
		return;
	}
	var rect = canvas.getBoundingClientRect();
	var ancho = Math.max(500, Math.floor(rect.width || 500));
	var alto = Math.max(220, Math.floor(rect.height || 220));
	var ratio = window.devicePixelRatio || 1;
	canvas.width = Math.floor(ancho * ratio);
	canvas.height = Math.floor(alto * ratio);
	canvas.style.width = "100%";
	canvas.style.height = alto + "px";

	var ctx = canvas.getContext("2d");
	ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
	ctx.fillStyle = "#ffffff";
	ctx.fillRect(0, 0, ancho, alto);
	ctx.strokeStyle = "#172033";
	ctx.lineWidth = 3;
	ctx.lineCap = "round";
	ctx.lineJoin = "round";

	recetarioFirmaCanvas.canvas = canvas;
	recetarioFirmaCanvas.ctx = ctx;
	recetarioFirmaCanvas.dibujando = false;
	recetarioFirmaCanvas.firmado = false;

	if (!canvas.dataset.eventosFirma) {
		canvas.addEventListener("pointerdown", iniciarTrazoFirmaRecetario);
		canvas.addEventListener("pointermove", dibujarTrazoFirmaRecetario);
		canvas.addEventListener("pointerup", finalizarTrazoFirmaRecetario);
		canvas.addEventListener("pointercancel", finalizarTrazoFirmaRecetario);
		canvas.addEventListener("pointerleave", finalizarTrazoFirmaRecetario);
		canvas.dataset.eventosFirma = "SI";
	}
}

function limpiarFirmaRecetarioIndicaciones() {
	prepararCanvasFirmaRecetarioIndicaciones();
	recetarioMostrarMensajeFirma("", "");
}

function posicionFirmaRecetario(evento) {
	var canvas = recetarioFirmaCanvas.canvas;
	var rect = canvas.getBoundingClientRect();
	return {
		x: evento.clientX - rect.left,
		y: evento.clientY - rect.top
	};
}

function iniciarTrazoFirmaRecetario(evento) {
	if (!recetarioFirmaCanvas.ctx || !recetarioFirmaCanvas.canvas) {
		return;
	}
	evento.preventDefault();
	if (recetarioFirmaCanvas.canvas.setPointerCapture && evento.pointerId !== undefined) {
		recetarioFirmaCanvas.canvas.setPointerCapture(evento.pointerId);
	}
	var pos = posicionFirmaRecetario(evento);
	recetarioFirmaCanvas.dibujando = true;
	recetarioFirmaCanvas.ultimoX = pos.x;
	recetarioFirmaCanvas.ultimoY = pos.y;
	recetarioFirmaCanvas.ctx.beginPath();
	recetarioFirmaCanvas.ctx.moveTo(pos.x, pos.y);
}

function dibujarTrazoFirmaRecetario(evento) {
	if (!recetarioFirmaCanvas.dibujando || !recetarioFirmaCanvas.ctx) {
		return;
	}
	evento.preventDefault();
	var pos = posicionFirmaRecetario(evento);
	recetarioFirmaCanvas.ctx.lineTo(pos.x, pos.y);
	recetarioFirmaCanvas.ctx.stroke();
	recetarioFirmaCanvas.ultimoX = pos.x;
	recetarioFirmaCanvas.ultimoY = pos.y;
	recetarioFirmaCanvas.firmado = true;
}

function finalizarTrazoFirmaRecetario(evento) {
	if (!recetarioFirmaCanvas.dibujando) {
		return;
	}
	if (evento && evento.preventDefault) {
		evento.preventDefault();
	}
	recetarioFirmaCanvas.dibujando = false;
	if (recetarioFirmaCanvas.ctx) {
		recetarioFirmaCanvas.ctx.closePath();
	}
}

function confirmarFirmaRecetarioIndicaciones() {
	if (!recetarioFirmaCanvas.canvas || !recetarioFirmaCanvas.firmado) {
		recetarioMostrarMensajeFirma("Debe realizar una firma antes de confirmar.", "error");
		return;
	}
	recetarioGuardandoAccion = "firma";
	renderEstadoFormularioRecetario();
	if (typeof verCerrarEfectoCargando == "function") {
		verCerrarEfectoCargando("1");
	}
	recetarioAjax("firmar_documento", {
		id: recetarioDocumentoId,
		firma_imagen: recetarioFirmaCanvas.canvas.toDataURL("image/png")
	}, function (datos) {
		if (typeof verCerrarEfectoCargando == "function") {
			verCerrarEfectoCargando("");
		}
		recetarioGuardandoAccion = "";
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			renderEstadoFormularioRecetario();
			recetarioMostrarMensajeFirma(datos.mensaje || "No se pudo guardar la firma.", "error");
			return;
		}
		if (datos["2"]) {
			cargarDocumentoRecetarioEnFormulario(datos["2"]);
		}
		cerrarFirmaRecetarioIndicaciones();
		renderEstadoFormularioRecetario();
		buscarHistorialRecetariosDesdeConsulta();
		buscarHistorialRecetariosLateral();
		actualizarPreviewRecetarioIndicaciones();
		ver_vetana_informativa(datos.mensaje || "Firma guardada correctamente.");
	}, function () {
		if (typeof verCerrarEfectoCargando == "function") {
			verCerrarEfectoCargando("");
		}
		recetarioGuardandoAccion = "";
		renderEstadoFormularioRecetario();
		recetarioMostrarMensajeFirma("No se pudo guardar la firma. Revise la conexion o intente nuevamente.", "error");
	});
}

function buscarHistorialRecetariosDesdeConsulta() {
	var contenedor = recetarioEl("divHistorial_RecetarioIndicaciones");
	if (!contenedor) {
		return;
	}
	var venta = (typeof cod_ventaFKConsulta != "undefined") ? String(cod_ventaFKConsulta || "").trim() : "";
	if (!venta) {
		contenedor.innerHTML = "<div class='recetario-empty'>Seleccione una venta para ver documentos.</div>";
		return;
	}
	contenedor.innerHTML = paginacargando;
	recetarioAjax("listar", {
		venta_id: venta
	}, function (datos) {
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			contenedor.innerHTML = "<div class='recetario-empty'>No se pudo cargar el historial.</div>";
			return;
		}
		contenedor.innerHTML = datos["2"];
	});
}

function buscarHistorialRecetariosLateral() {
	var contenedor = recetarioEl("recetarioHistorialLateral");
	if (!contenedor || !recetarioContexto.venta_id) {
		return;
	}
	contenedor.innerHTML = paginacargando;
	recetarioAjax("listar", {
		venta_id: recetarioContexto.venta_id
	}, function (datos) {
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			contenedor.innerHTML = "<div class='recetario-empty'>No se pudo cargar el historial.</div>";
			return;
		}
		contenedor.innerHTML = datos["2"];
	});
}

function cargarDocumentoRecetarioEnFormulario(doc) {
	asegurarEstructuraRecetarioIndicaciones();
	recetarioDocumentoId = doc.id || "";
	recetarioDocumentoReemplazadoId = doc.documento_reemplazado_id || "";
	recetarioTipoCreacion = doc.estado == "complementaria" ? "complementaria" : "";
	recetarioEstadoActual = doc.estado || "borrador";
	recetarioFirma = doc.firma || null;
	recetarioBloqueado = doc.estado != "borrador";
	recetarioContextValidationStatus = "valid";
	recetarioContextValidationMessage = "";
	emisorStatus = "authorized";
	emisorMessage = "Usuario del sistema";
	recetarioContexto = {
		paciente_id: doc.paciente_id,
		paciente_nombre: doc.paciente_nombre,
		titular_id: doc.titular_id,
		titular_nombre: doc.titular_nombre,
		cedula_titular: doc.cedula_titular,
		venta_id: doc.venta_id,
		numero_venta: doc.numero_venta,
		apodo_venta: doc.apodo_venta,
		consulta_id: doc.consulta_id,
		consulta_resumen: doc.consulta_resumen,
		hilo_id: doc.hilo_id,
		hilo_resumen: doc.hilo_asunto ? "Hilo #" + doc.hilo_id + " - " + doc.hilo_asunto : "",
		doctor_id: doc.doctor_id,
		doctor_nombre: doc.doctor_nombre,
		doctor_foto_url: doc.doctor_foto_url || "",
		doctor_estado: doc.doctor_estado || "Usuario del sistema",
		perfil_clinico_autorizado: "SI",
		permiso_emitir_recetario: "SI",
		motivo_emisor: doc.motivo_emisor || "",
		usuario_actual_id: doc.usuario_actual_id || doc.usuario_emisor_id,
		usuario_actual_nombre: doc.usuario_actual_nombre || doc.usuario_emisor_nombre || doc.doctor_nombre,
		usuario_actual_foto_url: doc.usuario_actual_foto_url || "",
		usuario_emisor_id: doc.usuario_emisor_id,
		sucursal_id: doc.sucursal_id,
		sucursal_nombre: doc.sucursal_nombre,
		fecha_hora: doc.fecha_emision || doc.created_at,
		doctor_tipo: doc.doctor_tipo || "",
		estado: doc.estado || "borrador",
		estado_firma: doc.estado_firma || "sin_firma",
		estado_firma_texto: doc.estado_firma_texto || "Sin firma",
		puede_borrador: "SI",
		puede_emitir: "SI",
		puede_imprimir: doc.puede_imprimir || "SI"
	};
	recetarioVentas = [{
		venta_id: doc.venta_id,
		numero_venta: doc.numero_venta,
		apodo_venta: doc.apodo_venta,
		rotulo: "Nro. " + (doc.numero_venta || doc.venta_id) + " - " + doc.titular_nombre + (doc.apodo_venta ? " (" + doc.apodo_venta + ")" : "")
	}];
	recetarioMedicamentos = doc.medicamentos || [];
	recetarioIndicaciones = (doc.indicaciones || []).map(function (ind) {
		return {
			categoria: recetarioCategoriaNormalizada(ind.categoria),
			texto: ind.texto || "",
			orden: ind.orden || ""
		};
	});
	if (recetarioMedicamentos.length === 0) {
		agregarMedicamentoRecetarioIndicaciones();
	}
	if (recetarioIndicaciones.length === 0) {
		agregarIndicacionRecetarioIndicaciones();
	}
	recetarioEl("recetarioObservacionesGenerales").value = doc.observaciones_generales || "";
	if (recetarioEl("recetarioConfirmarVenta")) {
		recetarioEl("recetarioConfirmarVenta").checked = false;
	}
	renderContextoRecetarioIndicaciones([]);
	renderEstadoFormularioRecetario();
	actualizarPreviewRecetarioIndicaciones();
	verCerrarRecetarioIndicaciones(true);
}

function verDetalleRecetarioIndicaciones(id) {
	recetarioAjax("detalle", { id: id }, function (datos) {
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			ver_vetana_informativa("NO SE PUDO OBTENER EL DOCUMENTO");
			return;
		}
		cargarDocumentoRecetarioEnFormulario(datos["2"]);
	});
}

function imprimirRecetarioIndicaciones(id) {
	if (!id) {
		ver_vetana_informativa("FALTO SELECCIONAR UN DOCUMENTO");
		return;
	}
	recetarioAjax("imprimir", { id: id }, function (datos) {
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			ver_vetana_informativa(datos.mensaje || "NO SE PUDO IMPRIMIR");
			return;
		}
		localStorage.setItem("reporte", datos["2"]);
		localStorage.setItem("tipo", "reporte");
		window.open("/GoodVentaAsisCap/system/reportInformes.html");
	});
}

function abrirAnularRecetarioIndicaciones(id) {
	recetarioAnularId = id;
	asegurarEstructuraRecetarioIndicaciones();
	recetarioEl("motivoAnularRecetarioIndicaciones").value = "";
	recetarioEl("modalAnularRecetarioIndicaciones").style.display = "";
}

function cerrarAnularRecetarioIndicaciones() {
	recetarioAnularId = "";
	if (recetarioEl("modalAnularRecetarioIndicaciones")) {
		recetarioEl("modalAnularRecetarioIndicaciones").style.display = "none";
	}
}

function confirmarAnularRecetarioIndicaciones() {
	var motivo = recetarioEl("motivoAnularRecetarioIndicaciones").value;
	if (String(motivo || "").trim() == "") {
		ver_vetana_informativa("FALTO INGRESAR EL MOTIVO DE ANULACION");
		return;
	}
	recetarioAjax("anular", {
		id: recetarioAnularId,
		motivo: motivo
	}, function (datos) {
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			ver_vetana_informativa(datos.mensaje || "NO SE PUDO ANULAR");
			return;
		}
		cerrarAnularRecetarioIndicaciones();
		ver_vetana_informativa("DOCUMENTO ANULADO CORRECTAMENTE");
		buscarHistorialRecetariosDesdeConsulta();
		buscarHistorialRecetariosLateral();
	});
}

function abrirComplementariaRecetarioIndicaciones(id) {
	recetarioAjax("detalle", { id: id }, function (datos) {
		var respuesta = respuestaJqueryAjax(datos["1"]);
		if (respuesta !== true) {
			ver_vetana_informativa("NO SE PUDO OBTENER EL DOCUMENTO ORIGINAL");
			return;
		}
		var doc = datos["2"];
		abrirRecetarioIndicaciones({
			venta_id: doc.venta_id,
			cliente_id: doc.titular_id,
			consulta_id: doc.consulta_id,
			hilo_id: doc.hilo_id,
			documento_reemplazado_id: doc.id,
			tipo_creacion: "complementaria"
		});
	});
}

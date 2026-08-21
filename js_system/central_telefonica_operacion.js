/* Central Telefonica Nivel 1 - llamadas desde Telar y aviso entrante. */
(function (window, document) {
    "use strict";

    var ENDPOINT = "/GoodVentaAsisCap/php_system/abmCentralTelefonicaOperacion.php";
    var state = {
        root: null,
        mounted: false,
        loading: false,
        searching: false,
        activity: null,
        searchItems: [],
        seen: {},
        pollTimer: null,
        stopped: false,
        historyAllowed: false
    };

    function escapeHtml(value) {
        return String(value === null || typeof value === "undefined" ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function appendCredentials(formData) {
        try {
            if (typeof window.obtener_datos_user === "function") { window.obtener_datos_user(); }
        } catch (ignore) {}
        if (typeof window.userid !== "undefined") { formData.append("useru", window.userid); }
        if (typeof window.passuser !== "undefined") { formData.append("passu", window.passuser); }
        if (typeof window.navegador !== "undefined") { formData.append("navegador", window.navegador); }
    }

    function request(action, payload) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            var formData = new FormData();
            var key;
            formData.append("accion", action);
            appendCredentials(formData);
            payload = payload || {};
            for (key in payload) {
                if (Object.prototype.hasOwnProperty.call(payload, key)) {
                    formData.append(key, payload[key]);
                }
            }
            xhr.open("POST", ENDPOINT, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.timeout = 20000;
            xhr.onreadystatechange = function () {
                var data;
                var error;
                if (xhr.readyState !== 4) { return; }
                try {
                    data = JSON.parse((xhr.responseText || "").replace(/^\uFEFF/, ""));
                } catch (ignore) {
                    reject(new Error("El servidor no devolvió una respuesta válida."));
                    return;
                }
                if (xhr.status < 200 || xhr.status >= 300 || !data.ok) {
                    error = new Error(data.mensaje || "No se pudo completar la operación telefónica.");
                    error.code = data.codigo || "";
                    error.data = data.datos || {};
                    reject(error);
                    return;
                }
                resolve(data.datos || {});
            };
            xhr.onerror = function () { reject(new Error("No se pudo comunicar con Telar.")); };
            xhr.ontimeout = function () { reject(new Error("La consulta telefónica tardó demasiado.")); };
            xhr.send(formData);
        });
    }

    function money(value) {
        var number = Number(value || 0);
        try {
            return new Intl.NumberFormat("es-PY", { maximumFractionDigits: 0 }).format(number) + " Gs.";
        } catch (ignore) {
            return String(Math.round(number)).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " Gs.";
        }
    }

    function sourceLabel(source) {
        var labels = { principal: "Principal", whatsapp: "WhatsApp", trabajo1: "Trabajo 1", trabajo2: "Trabajo 2" };
        return labels[source] || "Teléfono";
    }

    function statusLabel(status) {
        var labels = {
            pendiente: "En cola", procesando: "Preparando", enviada: "MicroSIP por sonar",
            sonando: "Sonando", conectada: "Conectada", finalizada: "Finalizada",
            ocupada: "Ocupado", no_contestada: "No contestada", error: "No preparada"
        };
        return labels[status] || status || "Sin estado";
    }

    function panelHtml() {
        return ""
            + "<section class='central-telefonica-panel central-telefonica-operation' id='centralTelefonicaOperation'>"
            + "  <div class='central-telefonica-operation__heading'>"
            + "    <div><p class='central-telefonica-eyebrow'>Gestión telefónica</p><h2>Llamar desde Telar</h2><span>Busque al paciente; Telar hará sonar su MicroSIP antes de llamar.</span></div>"
            + "    <div class='central-telefonica-operation__identity'><span id='centralTelefonicaOperationalService' class='central-telefonica-operation-badge'>Verificando conector…</span><span id='centralTelefonicaOperationalExtension'>Interno: —</span></div>"
            + "  </div>"
            + "  <form class='central-telefonica-operation__search' id='centralTelefonicaPatientSearch'>"
            + "    <label for='centralTelefonicaPatientQuery' class='central-telefonica-sr-only'>Buscar paciente</label>"
            + "    <i class='fa-solid fa-magnifying-glass' aria-hidden='true'></i>"
            + "    <input id='centralTelefonicaPatientQuery' type='search' maxlength='100' placeholder='Nombre, cédula o teléfono del paciente' autocomplete='off' />"
            + "    <button type='submit'><i class='fa-solid fa-magnifying-glass' aria-hidden='true'></i><span>Buscar</span></button>"
            + "  </form>"
            + "  <div class='central-telefonica-operation__guide'><strong>1. Busque</strong><span>2. Elija el teléfono</span><span>3. Atienda MicroSIP</span><span>4. Converse con el paciente</span></div>"
            + "  <div id='centralTelefonicaOperationMessage' class='central-telefonica-operation__message'>La búsqueda muestra únicamente datos administrativos y financieros.</div>"
            + "  <div id='centralTelefonicaPatientResults' class='central-telefonica-patient-results'></div>"
            + "  <div id='centralTelefonicaRecentRequests' class='central-telefonica-recent-requests'></div>"
            + "</section>";
    }

    function mount(root, historyAllowed) {
        var main;
        var wrapper;
        state.root = root;
        state.historyAllowed = !!historyAllowed;
        if (!root || root.querySelector("#centralTelefonicaOperation")) {
            renderActivity();
            return;
        }
        main = root.querySelector(".central-telefonica-main");
        if (!main) { return; }
        wrapper = document.createElement("div");
        wrapper.innerHTML = panelHtml();
        main.insertBefore(wrapper.firstChild, main.firstChild);
        bindPanel();
        state.mounted = true;
        renderActivity();
        pollNow();
    }

    function bindPanel() {
        var panel = state.root ? state.root.querySelector("#centralTelefonicaOperation") : null;
        var form = state.root ? state.root.querySelector("#centralTelefonicaPatientSearch") : null;
        if (!panel || panel.getAttribute("data-bound") === "1") { return; }
        panel.setAttribute("data-bound", "1");
        if (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                searchPatients();
            }, false);
        }
        panel.addEventListener("click", function (event) {
            var button = event.target.closest ? event.target.closest("[data-operational-action]") : null;
            if (!button) { return; }
            if (button.getAttribute("data-operational-action") === "call") {
                placeCall(button);
            }
        }, false);
    }

    function operationMessage(text, kind) {
        var element = state.root ? state.root.querySelector("#centralTelefonicaOperationMessage") : null;
        if (!element) { return; }
        element.className = "central-telefonica-operation__message central-telefonica-operation__message--" + (kind || "info");
        element.textContent = text;
    }

    function searchPatients() {
        var input = state.root ? state.root.querySelector("#centralTelefonicaPatientQuery") : null;
        var value = input ? input.value.trim() : "";
        if (state.searching) { return; }
        if (value.length < 2) {
            operationMessage("Escriba al menos dos caracteres para buscar.", "warning");
            return;
        }
        state.searching = true;
        operationMessage("Buscando pacientes…", "info");
        request("buscar_pacientes", { busqueda: value }).then(function (data) {
            state.searchItems = data.items || [];
            renderSearchResults();
            operationMessage(
                state.searchItems.length ? "Seleccione el teléfono que desea llamar." : "No se encontraron pacientes con esos datos.",
                state.searchItems.length ? "ok" : "warning"
            );
        }).catch(function (error) {
            operationMessage(error.message, "error");
        }).then(function () {
            state.searching = false;
        });
    }

    function renderSearchResults() {
        var container = state.root ? state.root.querySelector("#centralTelefonicaPatientResults") : null;
        var canCall = state.activity && state.activity.servicio
            && state.activity.servicio.origenacion_disponible
            && state.activity.usuario && state.activity.usuario.extension;
        var html = "";
        if (!container) { return; }
        state.searchItems.forEach(function (patient) {
            var finance = patient.finanzas || {};
            var phones = patient.telefonos || [];
            html += "<article class='central-telefonica-patient-card'>"
                + "<div class='central-telefonica-patient-card__avatar'><i class='fa-solid fa-user' aria-hidden='true'></i></div>"
                + "<div class='central-telefonica-patient-card__identity'><strong>" + escapeHtml(patient.nombre || "Paciente") + "</strong><span>Documento: " + escapeHtml(patient.documento || "Sin registrar") + "</span></div>"
                + "<div class='central-telefonica-patient-card__finance'><span>Saldo pendiente</span><strong>" + money(finance.saldo_pendiente) + "</strong><small>" + Number(finance.cuotas_vencidas || 0) + " cuota(s) vencida(s)</small></div>"
                + "<div class='central-telefonica-patient-card__phones'>";
            if (!phones.length) {
                html += "<span class='central-telefonica-no-phone'>Sin teléfono vigente</span>";
            }
            phones.forEach(function (phone) {
                html += "<button type='button' data-operational-action='call' data-client='" + escapeHtml(patient.cod_cliente) + "' data-phone='" + escapeHtml(phone.numero) + "' " + (canCall ? "" : "disabled") + ">"
                    + "<i class='fa-solid fa-phone' aria-hidden='true'></i><span><small>" + escapeHtml(sourceLabel(phone.fuente)) + "</small>" + escapeHtml(phone.numero) + "</span><strong>Llamar</strong></button>";
            });
            html += "</div></article>";
        });
        container.innerHTML = html;
    }

    function placeCall(button) {
        if (!button || button.disabled) { return; }
        button.disabled = true;
        operationMessage("Enviando la solicitud a Issabel…", "info");
        request("solicitar_llamada", {
            cod_cliente: button.getAttribute("data-client"),
            telefono: button.getAttribute("data-phone")
        }).then(function (data) {
            operationMessage(data.mensaje || "Solicitud enviada. Atienda su MicroSIP.", "ok");
            pollNow();
        }).catch(function (error) {
            operationMessage(error.message, "error");
            button.disabled = false;
        });
    }

    function renderActivity() {
        var serviceElement;
        var extensionElement;
        var service;
        var user;
        var requestsElement;
        var html = "";
        if (!state.root || !state.activity) { return; }
        service = state.activity.servicio || {};
        user = state.activity.usuario || {};
        serviceElement = state.root.querySelector("#centralTelefonicaOperationalService");
        extensionElement = state.root.querySelector("#centralTelefonicaOperationalExtension");
        if (serviceElement) {
            serviceElement.textContent = service.origenacion_disponible ? "Conector disponible" : "Llamada manual disponible";
            serviceElement.className = "central-telefonica-operation-badge "
                + (service.origenacion_disponible ? "central-telefonica-operation-badge--ok" : "central-telefonica-operation-badge--warning");
            serviceElement.title = service.mensaje || "";
        }
        if (extensionElement) {
            extensionElement.textContent = user.extension ? "Interno: " + user.extension : "Sin interno asociado";
        }
        requestsElement = state.root.querySelector("#centralTelefonicaRecentRequests");
        (state.activity.solicitudes || []).slice(0, 3).forEach(function (item) {
            html += "<article class='central-telefonica-request central-telefonica-request--" + escapeHtml(item.estado) + "'><i class='fa-solid fa-phone-volume' aria-hidden='true'></i><div><strong>" + escapeHtml(statusLabel(item.estado)) + "</strong><span>" + escapeHtml(item.mensaje || item.telefono) + "</span></div></article>";
        });
        if (requestsElement) { requestsElement.innerHTML = html; }
        renderSearchResults();
    }

    function inboundPopup(call) {
        var popup = document.getElementById("centralTelefonicaInboundPopup");
        var patient = call.paciente;
        var finance = patient ? (patient.finanzas || {}) : {};
        var identity;
        var details;
        if (!popup) {
            popup = document.createElement("aside");
            popup.id = "centralTelefonicaInboundPopup";
            popup.className = "central-telefonica-inbound";
            popup.setAttribute("role", "status");
            popup.setAttribute("aria-live", "polite");
            document.body.appendChild(popup);
        }
        if (patient) {
            identity = "<strong>" + escapeHtml(patient.nombre) + "</strong><span>Documento: " + escapeHtml(patient.documento || "Sin registrar") + "</span>";
            details = "<div class='central-telefonica-inbound__finance'><span><small>Saldo pendiente</small><strong>" + money(finance.saldo_pendiente) + "</strong></span><span><small>Cuotas vencidas</small><strong>" + Number(finance.cuotas_vencidas || 0) + "</strong></span><span><small>Próximo vencimiento</small><strong>" + escapeHtml(finance.proximo_vencimiento || "Sin pendiente") + "</strong></span></div>";
        } else if (Number(call.coincidencias_cliente || 0) > 1) {
            identity = "<strong>Número compartido</strong><span>Hay más de un paciente asociado. Confirme la identidad durante la llamada.</span>";
            details = "";
        } else {
            identity = "<strong>Número no identificado</strong><span>No coincide con un teléfono vigente de pacientes.</span>";
            details = "";
        }
        popup.innerHTML = "<div class='central-telefonica-inbound__header'><span><i class='fa-solid fa-phone-volume' aria-hidden='true'></i> " + (call.estado === "conectada" ? "En conversación" : "Llamada entrante") + "</span><button type='button' data-inbound-action='close' aria-label='Cerrar aviso'><i class='fa-solid fa-xmark'></i></button></div>"
            + "<div class='central-telefonica-inbound__number'>" + escapeHtml(call.telefono || "Número privado") + " · Interno " + escapeHtml(call.extension) + "</div>"
            + "<div class='central-telefonica-inbound__identity'>" + identity + "</div>" + details
            + "<div class='central-telefonica-inbound__note'><i class='fa-solid fa-shield-halved' aria-hidden='true'></i> Solo información administrativa y financiera.</div>"
            + "<button type='button' class='central-telefonica-inbound__open' data-inbound-action='open'>Abrir Central Telefónica</button>";
        popup.hidden = false;
    }

    function processInboundCalls() {
        (state.activity && state.activity.llamadas ? state.activity.llamadas : []).forEach(function (call) {
            var signature;
            if (call.direccion !== "entrante" || (call.estado !== "sonando" && call.estado !== "conectada")) { return; }
            signature = call.clave + "|" + call.estado;
            if (state.seen[signature]) { return; }
            state.seen[signature] = true;
            inboundPopup(call);
        });
    }

    function schedulePoll(delay) {
        window.clearTimeout(state.pollTimer);
        if (!state.stopped) {
            state.pollTimer = window.setTimeout(pollNow, delay);
        }
    }

    function pollNow() {
        if (state.loading || state.stopped) { return; }
        if (typeof window.userid === "undefined" || !window.userid) {
            schedulePoll(3000);
            return;
        }
        state.loading = true;
        request("consultar_actividad").then(function (data) {
            state.activity = data;
            renderActivity();
            processInboundCalls();
            schedulePoll(2500);
        }).catch(function (error) {
            if (error.code === "sesion_invalida") {
                schedulePoll(15000);
            } else if (error.code === "operacion_no_instalada") {
                state.stopped = true;
            } else {
                schedulePoll(10000);
            }
        }).then(function () {
            state.loading = false;
        });
    }

    document.addEventListener("click", function (event) {
        var button = event.target.closest ? event.target.closest("[data-inbound-action]") : null;
        var popup;
        if (!button) { return; }
        popup = document.getElementById("centralTelefonicaInboundPopup");
        if (button.getAttribute("data-inbound-action") === "close" && popup) {
            popup.hidden = true;
        }
        if (button.getAttribute("data-inbound-action") === "open") {
            if (typeof window.abrirCentralTelefonica === "function") { window.abrirCentralTelefonica(); }
            if (popup) { popup.hidden = true; }
        }
    }, false);

    window.centralTelefonicaNivel1Montar = mount;
    window.centralTelefonicaNivel1Actualizar = pollNow;

    function start() { schedulePoll(800); }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", start, false);
    } else {
        start();
    }
})(window, document);

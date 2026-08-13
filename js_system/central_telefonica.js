/* Central Telefonica - monitor CDR de solo lectura para Sistema Telar. */
(function (window, document) {
    "use strict";

    var ENDPOINT = "/GoodVentaAsisCap/php_system/abmCentralTelefonica.php";
    var ROOT_ID = "telarCentralTelefonica";
    var state = {
        root: null,
        open: false,
        loading: false,
        requestSequence: 0,
        page: 1,
        pages: 1,
        limit: 50,
        data: null,
        refreshTimer: null
    };

    function escapeHtml(value) {
        return String(value === null || typeof value === "undefined" ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function todayIso() {
        var date = new Date();
        var month = String(date.getMonth() + 1);
        var day = String(date.getDate());
        if (month.length < 2) { month = "0" + month; }
        if (day.length < 2) { day = "0" + day; }
        return date.getFullYear() + "-" + month + "-" + day;
    }

    function appendCredentials(formData) {
        try {
            if (typeof window.obtener_datos_user === "function") {
                window.obtener_datos_user();
            }
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
                if (Object.prototype.hasOwnProperty.call(payload, key)
                    && payload[key] !== null && typeof payload[key] !== "undefined") {
                    formData.append(key, payload[key]);
                }
            }
            xhr.open("POST", ENDPOINT, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.timeout = 30000;
            xhr.onreadystatechange = function () {
                var data;
                var error;
                if (xhr.readyState !== 4) { return; }
                try {
                    data = JSON.parse((xhr.responseText || "").replace(/^\uFEFF/, ""));
                } catch (parseError) {
                    reject(new Error("El servidor devolvió una respuesta que no se pudo interpretar."));
                    return;
                }
                if (xhr.status < 200 || xhr.status >= 300 || !data.ok) {
                    error = new Error(data.mensaje || "No se pudo completar la consulta.");
                    error.code = data.codigo || "";
                    reject(error);
                    return;
                }
                resolve(data.datos || {});
            };
            xhr.onerror = function () { reject(new Error("No se pudo comunicar con el servidor.")); };
            xhr.ontimeout = function () { reject(new Error("La consulta tardó demasiado. Intente nuevamente.")); };
            xhr.send(formData);
        });
    }

    function hasPermission() {
        if (typeof window.permisoAccesoUser === "function") {
            return window.permisoAccesoUser("VERCENTRALTELEFONICA", "accion") !== false;
        }
        if (typeof window.controlacceso2 === "function") {
            return window.controlacceso2("VERCENTRALTELEFONICA", "accion") !== false;
        }
        return false;
    }

    function notify(message, type) {
        var region;
        var toast;
        if (!state.root) { return; }
        region = state.root.querySelector("#centralTelefonicaLiveRegion");
        if (!region) { return; }
        toast = document.createElement("div");
        toast.className = "central-telefonica-toast central-telefonica-toast--" + (type || "info");
        toast.setAttribute("role", type === "error" ? "alert" : "status");
        toast.innerHTML = "<i class='fa-solid "
            + (type === "error" ? "fa-triangle-exclamation" : "fa-circle-info")
            + "' aria-hidden='true'></i><span>" + escapeHtml(message) + "</span>";
        region.innerHTML = "";
        region.appendChild(toast);
        window.setTimeout(function () {
            if (toast.parentNode) { toast.parentNode.removeChild(toast); }
        }, 4500);
    }

    function shellHtml() {
        var today = todayIso();
        return ""
            + "<div class='central-telefonica-shell'>"
            + "  <header class='central-telefonica-header'>"
            + "    <div class='central-telefonica-heading'>"
            + "      <span class='central-telefonica-heading__icon'><img src='/GoodVentaAsisCap/iconos/central-telefonica.svg' alt='' /></span>"
            + "      <div><p class='central-telefonica-eyebrow'>Telar · Control operativo</p><h1>Central Telefónica</h1><span>Movimientos de Issabel/Asterisk en modo de solo lectura</span></div>"
            + "    </div>"
            + "    <div class='central-telefonica-header__actions'>"
            + "      <div class='central-telefonica-sync' id='centralTelefonicaSync' role='status'><i class='fa-solid fa-circle-notch' aria-hidden='true'></i><span>Verificando sincronización…</span></div>"
            + "      <button type='button' class='central-telefonica-icon-button' data-central-action='refresh' title='Actualizar listado'><i class='fa-solid fa-rotate-right' aria-hidden='true'></i><span>Actualizar</span></button>"
            + "      <button type='button' class='central-telefonica-icon-button central-telefonica-icon-button--compact' data-central-action='minimize' title='Minimizar'><i class='fa-solid fa-window-minimize' aria-hidden='true'></i><span class='central-telefonica-sr-only'>Minimizar</span></button>"
            + "      <button type='button' class='central-telefonica-icon-button central-telefonica-icon-button--close' data-central-action='close' title='Cerrar'><i class='fa-solid fa-xmark' aria-hidden='true'></i><span class='central-telefonica-sr-only'>Cerrar</span></button>"
            + "    </div>"
            + "  </header>"
            + "  <main class='central-telefonica-main'>"
            + "    <section class='central-telefonica-summary' aria-label='Resumen de llamadas'>"
            + summaryCard("total", "fa-phone-volume", "Total de llamadas")
            + summaryCard("entrantes", "fa-arrow-down-long", "Entrantes")
            + summaryCard("salientes", "fa-arrow-up-long", "Salientes")
            + summaryCard("contestadas", "fa-circle-check", "Contestadas")
            + summaryCard("no_contestadas", "fa-phone-slash", "No contestadas")
            + summaryCard("tiempo_hablado_texto", "fa-clock", "Tiempo hablado")
            + summaryCard("internas", "fa-building", "Internas")
            + "    </section>"
            + "    <section class='central-telefonica-panel central-telefonica-filters-panel'>"
            + "      <div class='central-telefonica-panel__title'><div><p class='central-telefonica-eyebrow'>Consulta</p><h2>Filtros</h2></div><button type='button' class='central-telefonica-link-button' data-central-action='clear'>Limpiar filtros</button></div>"
            + "      <form id='centralTelefonicaFilters' class='central-telefonica-filters'>"
            + filterField("Desde", "<input type='date' id='centralTelefonicaDesde' value='" + today + "' required />")
            + filterField("Hasta", "<input type='date' id='centralTelefonicaHasta' value='" + today + "' required />")
            + filterField("Dirección", "<select id='centralTelefonicaTipo'><option value=''>Todas</option><option value='entrante_externa'>Entrante externa</option><option value='saliente_externa'>Saliente externa</option><option value='interna'>Interna</option><option value='servicio_prueba'>Servicio / prueba</option><option value='sin_clasificar'>Sin clasificar</option></select>")
            + filterField("Estado", "<select id='centralTelefonicaEstado'><option value=''>Todos</option><option value='contestada'>Contestada</option><option value='no_contestada'>No contestada</option><option value='ocupada'>Ocupada</option><option value='fallida'>Fallida</option><option value='congestion'>Congestión</option></select>")
            + filterField("Extensión", "<select id='centralTelefonicaExtension'><option value=''>Todas</option></select>")
            + filterField("Número telefónico", "<input type='search' id='centralTelefonicaTelefono' inputmode='tel' maxlength='40' placeholder='Ej.: 0981 o 595981' />")
            + "        <button type='submit' class='central-telefonica-primary-button'><i class='fa-solid fa-filter' aria-hidden='true'></i><span>Aplicar filtros</span></button>"
            + "      </form>"
            + "    </section>"
            + "    <section class='central-telefonica-panel central-telefonica-list-panel'>"
            + "      <div class='central-telefonica-panel__title'><div><p class='central-telefonica-eyebrow'>Movimientos</p><h2>Listado de llamadas</h2><span id='centralTelefonicaResultCount'>0 registros</span></div><span class='central-telefonica-readonly-badge'><i class='fa-solid fa-lock' aria-hidden='true'></i> Solo lectura</span></div>"
            + "      <div class='central-telefonica-table-wrap'>"
            + "        <table class='central-telefonica-table'>"
            + "          <thead><tr><th>Fecha y hora</th><th>Dirección</th><th>Número</th><th>Extensión</th><th>Estado</th><th>Duración</th><th>Hablado</th><th>Grabación</th><th><span class='central-telefonica-sr-only'>Acciones</span></th></tr></thead>"
            + "          <tbody id='centralTelefonicaRows'></tbody>"
            + "        </table>"
            + "        <div id='centralTelefonicaTableState' class='central-telefonica-table-state'><i class='fa-solid fa-circle-notch fa-spin' aria-hidden='true'></i><span>Cargando movimientos…</span></div>"
            + "      </div>"
            + "      <footer class='central-telefonica-pagination' id='centralTelefonicaPagination'></footer>"
            + "    </section>"
            + "  </main>"
            + "  <div class='central-telefonica-drawer-layer' id='centralTelefonicaDrawerLayer' hidden>"
            + "    <button type='button' class='central-telefonica-drawer-backdrop' data-central-action='close-detail' aria-label='Cerrar detalle'></button>"
            + "    <aside class='central-telefonica-drawer' role='dialog' aria-modal='true' aria-labelledby='centralTelefonicaDrawerTitle'>"
            + "      <div class='central-telefonica-drawer__header'><div><p class='central-telefonica-eyebrow'>Movimiento telefónico</p><h2 id='centralTelefonicaDrawerTitle'>Detalle de llamada</h2></div><button type='button' class='central-telefonica-icon-button central-telefonica-icon-button--close' data-central-action='close-detail'><i class='fa-solid fa-xmark' aria-hidden='true'></i><span class='central-telefonica-sr-only'>Cerrar</span></button></div>"
            + "      <div class='central-telefonica-drawer__body' id='centralTelefonicaDrawerBody'></div>"
            + "    </aside>"
            + "  </div>"
            + "  <div class='central-telefonica-live-region' id='centralTelefonicaLiveRegion' aria-live='polite' aria-atomic='true'></div>"
            + "</div>";
    }

    function summaryCard(key, icon, label) {
        return "<article class='central-telefonica-summary-card' data-summary='" + key + "'>"
            + "<span class='central-telefonica-summary-card__icon'><i class='fa-solid " + icon + "' aria-hidden='true'></i></span>"
            + "<div><strong id='centralTelefonicaSummary_" + key + "'>—</strong><span>" + label + "</span></div></article>";
    }

    function filterField(label, control) {
        return "<label class='central-telefonica-field'><span>" + label + "</span>" + control + "</label>";
    }

    function ensureRoot() {
        var root = document.getElementById(ROOT_ID);
        if (!root) { return false; }
        state.root = root;
        if (!root.getAttribute("data-central-ready")) {
            root.innerHTML = shellHtml();
            root.setAttribute("data-central-ready", "1");
            bindEvents();
        }
        return true;
    }

    function bindEvents() {
        var form = state.root.querySelector("#centralTelefonicaFilters");
        state.root.addEventListener("click", function (event) {
            var actionElement = event.target.closest ? event.target.closest("[data-central-action]") : null;
            var action;
            if (!actionElement) { return; }
            action = actionElement.getAttribute("data-central-action");
            if (action === "refresh") { loadCalls(false); }
            if (action === "clear") { clearFilters(); }
            if (action === "close") { window.cerrarCentralTelefonica(); }
            if (action === "minimize") { window.minimizarCentralTelefonica(); }
            if (action === "close-detail") { closeDetail(); }
            if (action === "detail") { openDetail(actionElement.getAttribute("data-call-id")); }
            if (action === "page-prev" && state.page > 1) { state.page--; loadCalls(true); }
            if (action === "page-next" && state.page < state.pages) { state.page++; loadCalls(true); }
        }, false);
        if (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                state.page = 1;
                loadCalls(true);
            }, false);
        }
    }

    function filterPayload() {
        return {
            desde: valueOf("centralTelefonicaDesde"),
            hasta: valueOf("centralTelefonicaHasta"),
            tipo: valueOf("centralTelefonicaTipo"),
            estado: valueOf("centralTelefonicaEstado"),
            extension: valueOf("centralTelefonicaExtension"),
            telefono: valueOf("centralTelefonicaTelefono"),
            pagina: state.page,
            limite: state.limit
        };
    }

    function valueOf(id) {
        var element = state.root ? state.root.querySelector("#" + id) : null;
        return element ? element.value : "";
    }

    function clearFilters() {
        var today = todayIso();
        state.root.querySelector("#centralTelefonicaDesde").value = today;
        state.root.querySelector("#centralTelefonicaHasta").value = today;
        state.root.querySelector("#centralTelefonicaTipo").value = "";
        state.root.querySelector("#centralTelefonicaEstado").value = "";
        state.root.querySelector("#centralTelefonicaExtension").value = "";
        state.root.querySelector("#centralTelefonicaTelefono").value = "";
        state.page = 1;
        loadCalls(true);
    }

    function setLoading(loading) {
        var tableState = state.root.querySelector("#centralTelefonicaTableState");
        var rows = state.root.querySelector("#centralTelefonicaRows");
        state.loading = loading;
        if (loading) {
            rows.innerHTML = "";
            tableState.hidden = false;
            tableState.className = "central-telefonica-table-state";
            tableState.innerHTML = "<i class='fa-solid fa-circle-notch fa-spin' aria-hidden='true'></i><span>Cargando movimientos…</span>";
        }
    }

    function showTableMessage(message, type) {
        var tableState = state.root.querySelector("#centralTelefonicaTableState");
        tableState.hidden = false;
        tableState.className = "central-telefonica-table-state central-telefonica-table-state--" + (type || "empty");
        tableState.innerHTML = "<i class='fa-solid "
            + (type === "error" ? "fa-triangle-exclamation" : "fa-inbox")
            + "' aria-hidden='true'></i><span>" + escapeHtml(message) + "</span>";
    }

    function loadCalls(scrollToList) {
        var sequence;
        if (!state.open || state.loading) { return; }
        sequence = ++state.requestSequence;
        setLoading(true);
        request("listar", filterPayload()).then(function (data) {
            if (sequence !== state.requestSequence || !state.open) { return; }
            state.data = data;
            state.loading = false;
            renderSummary(data.resumen || {});
            renderSync(data.sincronizacion || {});
            renderExtensions(data.extensiones || []);
            renderRows(data.llamadas || []);
            renderPagination(data.paginacion || {});
            if (scrollToList) {
                var panel = state.root.querySelector(".central-telefonica-list-panel");
                if (panel && panel.scrollIntoView) { panel.scrollIntoView({ behavior: "smooth", block: "start" }); }
            }
        }).catch(function (error) {
            if (sequence !== state.requestSequence || !state.open) { return; }
            state.loading = false;
            renderSync({ estado: "error", configurada: false });
            showTableMessage(error.message, "error");
            notify(error.message, "error");
            if (error.code === "sesion_invalida" && typeof window.ir_a_login === "function") {
                window.ir_a_login();
            }
        });
    }

    function renderSummary(summary) {
        var keys = ["total", "entrantes", "salientes", "contestadas", "no_contestadas", "tiempo_hablado_texto", "internas"];
        keys.forEach(function (key) {
            var element = state.root.querySelector("#centralTelefonicaSummary_" + key);
            if (element) { element.textContent = typeof summary[key] === "undefined" ? "0" : summary[key]; }
        });
    }

    function relativeSync(value) {
        var parsed;
        var seconds;
        if (!value) { return "Sin sincronizaciones registradas"; }
        parsed = new Date(String(value).replace(" ", "T"));
        if (isNaN(parsed.getTime())) { return "Última sincronización: " + value; }
        seconds = Math.max(0, Math.round((new Date().getTime() - parsed.getTime()) / 1000));
        if (seconds < 60) { return "Actualizado hace " + seconds + " s"; }
        if (seconds < 3600) { return "Actualizado hace " + Math.floor(seconds / 60) + " min"; }
        return "Última sincronización: " + value;
    }

    function renderSync(sync) {
        var element = state.root.querySelector("#centralTelefonicaSync");
        var className = "central-telefonica-sync";
        var icon = "fa-circle-info";
        var text;
        if (!sync.configurada) {
            className += " central-telefonica-sync--warning";
            icon = "fa-plug-circle-xmark";
            text = "Conector Issabel pendiente de configuración";
        } else if (sync.estado === "exitosa") {
            className += " central-telefonica-sync--ok";
            icon = "fa-circle-check";
            text = relativeSync(sync.ultima_sincronizacion);
        } else if (sync.estado === "fallida" || sync.estado === "error") {
            className += " central-telefonica-sync--error";
            icon = "fa-triangle-exclamation";
            text = "La última sincronización necesita revisión";
        } else {
            className += " central-telefonica-sync--warning";
            icon = "fa-clock";
            text = "Sincronización pendiente";
        }
        element.className = className;
        element.innerHTML = "<i class='fa-solid " + icon + "' aria-hidden='true'></i><span>" + escapeHtml(text) + "</span>";
    }

    function renderExtensions(extensions) {
        var select = state.root.querySelector("#centralTelefonicaExtension");
        var selected = select.value;
        var html = "<option value=''>Todas</option>";
        extensions.forEach(function (extension) {
            html += "<option value='" + escapeHtml(extension) + "'>" + escapeHtml(extension) + "</option>";
        });
        select.innerHTML = html;
        select.value = selected;
    }

    function typeLabel(type) {
        var labels = {
            entrante_externa: "Entrante",
            saliente_externa: "Saliente",
            interna: "Interna",
            servicio_prueba: "Servicio / prueba",
            sin_clasificar: "Sin clasificar"
        };
        return labels[type] || type || "Sin clasificar";
    }

    function typeIcon(type) {
        if (type === "entrante_externa") { return "fa-arrow-down-long"; }
        if (type === "saliente_externa") { return "fa-arrow-up-long"; }
        if (type === "interna") { return "fa-building"; }
        if (type === "servicio_prueba") { return "fa-flask"; }
        return "fa-circle-question";
    }

    function statusLabel(status) {
        var labels = {
            contestada: "Contestada",
            no_contestada: "No contestada",
            ocupada: "Ocupada",
            fallida: "Fallida",
            congestion: "Congestión",
            sin_estado: "Sin estado"
        };
        return labels[status] || status || "Sin estado";
    }

    function renderRows(calls) {
        var rows = state.root.querySelector("#centralTelefonicaRows");
        var tableState = state.root.querySelector("#centralTelefonicaTableState");
        var count = state.root.querySelector("#centralTelefonicaResultCount");
        var html = "";
        count.textContent = calls.length + (calls.length === 1 ? " registro visible" : " registros visibles");
        if (!calls.length) {
            rows.innerHTML = "";
            showTableMessage("No se encontraron llamadas para los filtros seleccionados.", "empty");
            return;
        }
        calls.forEach(function (call) {
            html += "<tr>"
                + "<td><strong>" + escapeHtml(call.fecha) + "</strong><span>" + escapeHtml(call.hora) + "</span></td>"
                + "<td><span class='central-telefonica-type central-telefonica-type--" + escapeHtml(call.tipo) + "'><i class='fa-solid " + typeIcon(call.tipo) + "' aria-hidden='true'></i>" + escapeHtml(typeLabel(call.tipo)) + "</span></td>"
                + "<td class='central-telefonica-number'>" + escapeHtml(call.numero_principal || "—") + "</td>"
                + "<td>" + escapeHtml(call.extension || "—") + "</td>"
                + "<td><span class='central-telefonica-status central-telefonica-status--" + escapeHtml(call.estado) + "'>" + escapeHtml(statusLabel(call.estado)) + "</span></td>"
                + "<td>" + escapeHtml(call.duracion_texto) + "</td>"
                + "<td><strong>" + escapeHtml(call.hablado_texto) + "</strong></td>"
                + "<td>" + (call.grabacion_disponible
                    ? "<span class='central-telefonica-recording central-telefonica-recording--available'><i class='fa-solid fa-wave-square' aria-hidden='true'></i> Disponible</span>"
                    : "<span class='central-telefonica-recording'><i class='fa-solid fa-minus' aria-hidden='true'></i> No disponible</span>") + "</td>"
                + "<td><button type='button' class='central-telefonica-detail-button' data-central-action='detail' data-call-id='" + escapeHtml(call.id_llamada) + "' aria-label='Ver detalle de la llamada'><i class='fa-solid fa-chevron-right' aria-hidden='true'></i></button></td>"
                + "</tr>";
        });
        rows.innerHTML = html;
        tableState.hidden = true;
    }

    function renderPagination(pagination) {
        var element = state.root.querySelector("#centralTelefonicaPagination");
        state.page = Number(pagination.pagina || 1);
        state.pages = Number(pagination.paginas || 1);
        element.innerHTML = "<span>Página " + state.page + " de " + state.pages + " · " + Number(pagination.total || 0) + " llamadas</span>"
            + "<div><button type='button' data-central-action='page-prev' " + (state.page <= 1 ? "disabled" : "") + "><i class='fa-solid fa-chevron-left' aria-hidden='true'></i> Anterior</button>"
            + "<button type='button' data-central-action='page-next' " + (state.page >= state.pages ? "disabled" : "") + ">Siguiente <i class='fa-solid fa-chevron-right' aria-hidden='true'></i></button></div>";
    }

    function openDetail(id) {
        var layer = state.root.querySelector("#centralTelefonicaDrawerLayer");
        var body = state.root.querySelector("#centralTelefonicaDrawerBody");
        layer.hidden = false;
        body.innerHTML = "<div class='central-telefonica-detail-loading'><i class='fa-solid fa-circle-notch fa-spin' aria-hidden='true'></i><span>Cargando detalle…</span></div>";
        request("detalle", { id_llamada: id }).then(function (data) {
            renderDetail(data.llamada || {});
        }).catch(function (error) {
            body.innerHTML = "<div class='central-telefonica-detail-error'><i class='fa-solid fa-triangle-exclamation' aria-hidden='true'></i><span>" + escapeHtml(error.message) + "</span></div>";
        });
    }

    function closeDetail() {
        var layer = state.root ? state.root.querySelector("#centralTelefonicaDrawerLayer") : null;
        if (layer) { layer.hidden = true; }
    }

    function detailItem(label, value) {
        return "<div class='central-telefonica-detail-item'><span>" + label + "</span><strong>" + escapeHtml(value || "—") + "</strong></div>";
    }

    function renderDetail(call) {
        var body = state.root.querySelector("#centralTelefonicaDrawerBody");
        var html = "<section class='central-telefonica-detail-section'><h3>Información general</h3><div class='central-telefonica-detail-grid'>"
            + detailItem("Inicio", call.fecha_inicio)
            + detailItem("Finalización", call.fecha_fin)
            + detailItem("Dirección", typeLabel(call.tipo))
            + detailItem("Estado", statusLabel(call.estado))
            + detailItem("Origen", call.origen)
            + detailItem("Destino", call.destino)
            + detailItem("Extensión", call.extension)
            + "</div></section>"
            + "<section class='central-telefonica-detail-section'><h3>Duración</h3><div class='central-telefonica-detail-grid'>"
            + detailItem("Duración total", call.duracion_texto)
            + detailItem("Tiempo hablado", call.hablado_texto)
            + "</div></section>"
            + "<section class='central-telefonica-detail-section'><h3>Clasificación</h3><p>" + escapeHtml(call.clasificacion_motivo || "Sin explicación disponible.") + "</p></section>"
            + "<section class='central-telefonica-detail-section'><h3>Grabación</h3><div class='central-telefonica-recording-card'>"
            + (call.grabacion_disponible
                ? "<i class='fa-solid fa-wave-square' aria-hidden='true'></i><div><strong>Grabación disponible</strong><span>La reproducción protegida se habilitará en una etapa posterior.</span></div>"
                : "<i class='fa-solid fa-volume-xmark' aria-hidden='true'></i><div><strong>Sin grabación disponible</strong><span>Issabel no informó una grabación para esta llamada.</span></div>")
            + "</div></section>";
        if (call.datos_tecnicos) {
            html += "<details class='central-telefonica-technical'><summary>Datos técnicos</summary>"
                + detailItem("UniqueID principal", call.datos_tecnicos.uniqueid_principal)
                + detailItem("LinkedID", call.datos_tecnicos.linkedid)
                + detailItem("Segmentos relacionados", call.datos_tecnicos.cantidad_segmentos)
                + renderSegments(call.segmentos || [])
                + "</details>";
        }
        body.innerHTML = html;
    }

    function renderSegments(segments) {
        var html = "";
        if (!segments.length) { return html; }
        html += "<div class='central-telefonica-segments'><h4>Recorrido técnico</h4>";
        segments.forEach(function (segment, index) {
            html += "<article><span>Segmento " + (index + 1) + "</span><strong>" + escapeHtml(segment.origen_original) + " → " + escapeHtml(segment.destino_original) + "</strong><small>" + escapeHtml(segment.canal || "Sin canal") + " · " + escapeHtml(statusLabel(segment.disposicion ? String(segment.disposicion).toLowerCase() : "")) + "</small></article>";
        });
        return html + "</div>";
    }

    function scheduleRefresh() {
        window.clearInterval(state.refreshTimer);
        state.refreshTimer = window.setInterval(function () {
            if (state.open && !state.loading) { loadCalls(false); }
        }, 60000);
    }

    window.abrirCentralTelefonica = function () {
        var container = document.getElementById("divCentralTelefonica");
        var marker;
        if (!hasPermission()) {
            if (typeof window.ver_vetana_informativa === "function") {
                window.ver_vetana_informativa("Acceso restringido", "No tiene permiso para ver Central Telefónica.", "advertencia");
            }
            return;
        }
        if (!container || !ensureRoot()) { return; }
        container.style.display = "";
        container.setAttribute("aria-hidden", "false");
        state.open = true;
        marker = document.getElementById("divMinimizadoCentralTelefonica");
        if (marker) { marker.style.display = "none"; }
        if (document.body) { document.body.classList.add("central-telefonica-open"); }
        loadCalls(false);
        scheduleRefresh();
    };

    window.cerrarCentralTelefonica = function () {
        var container = document.getElementById("divCentralTelefonica");
        if (container) {
            container.style.display = "none";
            container.setAttribute("aria-hidden", "true");
        }
        state.open = false;
        state.requestSequence++;
        state.loading = false;
        closeDetail();
        window.clearInterval(state.refreshTimer);
        if (document.body) { document.body.classList.remove("central-telefonica-open"); }
    };

    window.minimizarCentralTelefonica = function () {
        var marker = document.getElementById("divMinimizadoCentralTelefonica");
        window.cerrarCentralTelefonica();
        if (marker) { marker.style.display = ""; }
    };
})(window, document);

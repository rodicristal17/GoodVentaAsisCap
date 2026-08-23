/* Sistema Telar - GoHighLevel fase 2A: consulta y respuesta manual protegida. */
(function (window, document) {
    "use strict";

    var ENDPOINT = "/GoodVentaAsisCap/php_system/abmGoHighLevel.php";
    var state = {
        root: null,
        open: false,
        mounted: false,
        loading: false,
        context: null,
        tab: "conversaciones",
        summaryOpen: false,
        summary: null,
        cache: {},
        settings: null,
        queries: { conversaciones: "", contactos: "", oportunidades: "" },
        conversation: null,
        pendingMessage: null
    };

    function escapeHtml(value) {
        return String(value === null || typeof value === "undefined" ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function safeImage(value) {
        var text = String(value || "").trim();
        if (/^https:\/\//i.test(text) || /^\/GoodVentaAsisCap\//i.test(text)) { return escapeHtml(text); }
        return "";
    }

    function initials(name) {
        var parts = String(name || "?").trim().split(/\s+/).filter(Boolean);
        return ((parts[0] || "?").charAt(0) + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : "")).toUpperCase();
    }

    function avatar(name, image, extraClass) {
        var source = safeImage(image);
        if (source) {
            return "<span class='ghl-avatar " + escapeHtml(extraClass || "") + "'><img src='" + source + "' alt='' loading='lazy' onerror=\"this.parentNode.innerHTML='" + escapeHtml(initials(name)) + "'\"></span>";
        }
        return "<span class='ghl-avatar " + escapeHtml(extraClass || "") + "'>" + escapeHtml(initials(name)) + "</span>";
    }

    function credentials(form) {
        try { if (typeof window.obtener_datos_user === "function") { window.obtener_datos_user(); } } catch (ignore) {}
        form.append("useru", typeof window.userid !== "undefined" ? window.userid : "");
        form.append("passu", typeof window.passuser !== "undefined" ? window.passuser : "");
        form.append("navegador", typeof window.navegador !== "undefined" ? window.navegador : "");
    }

    function request(action, payload, timeout) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            var form = new FormData();
            var key;
            form.append("accion", action);
            credentials(form);
            payload = payload || {};
            for (key in payload) {
                if (Object.prototype.hasOwnProperty.call(payload, key)) { form.append(key, payload[key]); }
            }
            xhr.open("POST", ENDPOINT, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.timeout = timeout || 35000;
            xhr.onreadystatechange = function () {
                var data;
                var error;
                if (xhr.readyState !== 4) { return; }
                try { data = JSON.parse((xhr.responseText || "").replace(/^\uFEFF/, "")); }
                catch (ignore) { reject(new Error("Telar no devolvió una respuesta válida.")); return; }
                if (xhr.status < 200 || xhr.status >= 300 || !data.ok) {
                    error = new Error(data.mensaje || "No se pudo completar la consulta.");
                    error.code = data.codigo || "";
                    reject(error);
                    return;
                }
                resolve(data.datos || {});
            };
            xhr.onerror = function () { reject(new Error("No se pudo comunicar con Telar.")); };
            xhr.ontimeout = function () { reject(new Error("La consulta tardó demasiado. Intente nuevamente.")); };
            xhr.send(form);
        });
    }

    function mount() {
        if (state.mounted) { return true; }
        state.root = document.getElementById("telarGoHighLevel");
        if (!state.root) { return false; }
        state.root.innerHTML =
            "<section class='ghl-shell'>" +
                "<header class='ghl-header'>" +
                    "<div class='ghl-brand'><img src='/GoodVentaAsisCap/iconos/gohighlevel.svg?v=20260823-01' alt=''><div><small>TELAR · INTEGRACIÓN</small><h1>GoHighLevel</h1><p>Conversaciones y seguimiento vinculados con pacientes</p></div></div>" +
                    "<div class='ghl-header__actions'><span id='ghlConnection' class='ghl-connection'>Comprobando conexión…</span><button type='button' id='ghlSettingsButton' data-ghl-action='settings' title='Configuraciones y permisos' hidden><i class='fa-solid fa-gear'></i></button><button type='button' data-ghl-action='refresh' title='Actualizar'><i class='fa-solid fa-rotate-right'></i></button><button type='button' data-ghl-action='minimize' title='Minimizar'><i class='fa-solid fa-window-minimize'></i></button><button type='button' data-ghl-action='close' title='Cerrar'><i class='fa-solid fa-xmark'></i></button></div>" +
                "</header>" +
                "<div id='ghlMessage' class='ghl-message' hidden></div>" +
                "<section class='ghl-summary-strip' id='ghlSummaryStrip'><button type='button' data-ghl-action='toggle-summary'><span><i class='fa-solid fa-chart-line'></i><strong>Resumen general</strong><small>Indicadores plegables para priorizar conversaciones</small></span><i class='fa-solid fa-chevron-down' data-summary-chevron></i></button><div id='ghlSummaryBody' hidden></div></section>" +
                "<nav class='ghl-tabs' aria-label='Secciones de GoHighLevel'>" +
                    tabButton("resumen", "fa-chart-pie", "Resumen") +
                    tabButton("contactos", "fa-address-book", "Contactos") +
                    tabButton("oportunidades", "fa-filter-circle-dollar", "Oportunidades") +
                    tabButton("conversaciones", "fa-comments", "Conversaciones", true) +
                    tabButton("calendarios", "fa-calendar-days", "Calendarios") +
                    tabButton("sincronizacion", "fa-arrows-rotate", "Sincronización") +
                "</nav>" +
                "<main id='ghlContent' class='ghl-content'></main>" +
                "<div id='ghlModalLayer' class='ghl-modal-layer' hidden></div>" +
            "</section>";
        bindEvents();
        state.mounted = true;
        return true;
    }

    function tabButton(tab, icon, label, active) {
        return "<button type='button' data-ghl-tab='" + tab + "' class='" + (active ? "is-active" : "") + "'><i class='fa-solid " + icon + "'></i><span>" + label + "</span></button>";
    }

    function setMessage(text, kind) {
        var message = state.root ? state.root.querySelector("#ghlMessage") : null;
        if (!message) { return; }
        if (!text) { message.hidden = true; message.textContent = ""; return; }
        message.hidden = false;
        message.className = "ghl-message ghl-message--" + (kind || "info");
        message.textContent = text;
    }

    function setLoading(label) {
        var content = state.root.querySelector("#ghlContent");
        state.loading = true;
        content.innerHTML = "<div class='ghl-loading'><i class='fa-solid fa-spinner fa-spin'></i><strong>" + escapeHtml(label || "Consultando GoHighLevel…") + "</strong><small>La carga de datos no modifica automatizaciones.</small></div>";
    }

    function dateLabel(value) {
        var text = String(value || "");
        var date;
        var timestamp;
        if (!text) { return "Sin fecha"; }
        if (/^[0-9]{10,16}$/.test(text)) {
            timestamp = Number(text);
            if (text.length === 10) { timestamp *= 1000; }
            else if (text.length > 13) { timestamp = Math.floor(timestamp / Math.pow(10, text.length - 13)); }
            date = new Date(timestamp);
        } else {
            date = new Date(text);
        }
        if (isNaN(date.getTime())) { return escapeHtml(text.substring(0, 16).replace("T", " ")); }
        try { return date.toLocaleString("es-PY", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" }); }
        catch (ignore) { return escapeHtml(text.substring(0, 16).replace("T", " ")); }
    }

    function money(value) {
        try { return new Intl.NumberFormat("es-PY", { maximumFractionDigits: 0 }).format(Number(value || 0)) + " Gs."; }
        catch (ignore) { return String(Math.round(Number(value || 0))) + " Gs."; }
    }

    function channelLabel(channel) {
        var value = String(channel || "").toLowerCase();
        if (value.indexOf("whatsapp") >= 0) { return "WhatsApp"; }
        if (value.indexOf("email") >= 0) { return "Correo"; }
        if (value.indexOf("sms") >= 0) { return "SMS"; }
        if (value.indexOf("facebook") >= 0 || value.indexOf("messenger") >= 0) { return "Facebook"; }
        if (value.indexOf("instagram") >= 0) { return "Instagram"; }
        return channel ? channel : "Conversación";
    }

    function linkBadge(link) {
        link = link || {};
        if (link.estado === "vinculado" && link.paciente) {
            return "<span class='ghl-link ghl-link--ok'><i class='fa-solid fa-link'></i>Paciente: " + escapeHtml(link.paciente.nombre) + "</span>";
        }
        if (link.estado === "ambiguo") {
            return "<span class='ghl-link ghl-link--warning' title='Hay más de un paciente con este teléfono. No se vinculó automáticamente.'><i class='fa-solid fa-triangle-exclamation'></i>Coincidencia ambigua</span>";
        }
        return "<span class='ghl-link ghl-link--muted'><i class='fa-solid fa-link-slash'></i>Sin paciente vinculado</span>";
    }

    function emptyState(icon, title, detail) {
        return "<div class='ghl-empty'><i class='fa-solid " + icon + "'></i><h2>" + escapeHtml(title) + "</h2><p>" + escapeHtml(detail) + "</p></div>";
    }

    function searchToolbar(tab, placeholder, data) {
        var query = state.queries[tab] || "";
        var shown = (data.items || []).length;
        var total = Number(data.total || shown);
        return "<section class='ghl-toolbar'><form data-ghl-search-form='" + escapeHtml(tab) + "'><label><i class='fa-solid fa-magnifying-glass'></i><input type='search' maxlength='75' autocomplete='off' data-ghl-search-input='" + escapeHtml(tab) + "' placeholder='" + escapeHtml(placeholder) + "' value='" + escapeHtml(query) + "'></label><button type='submit' class='ghl-btn ghl-btn--primary'>Buscar</button>" + (query ? "<button type='button' class='ghl-btn ghl-btn--ghost' data-ghl-action='clear-search' data-tab='" + escapeHtml(tab) + "'>Limpiar</button>" : "") + "</form><span>Mostrando <strong>" + shown + "</strong> de <strong>" + total + "</strong></span></section>";
    }

    function paginationFooter(tab, data) {
        var pagination = data.paginacion || {};
        if (!pagination.hay_mas) { return ""; }
        return "<div class='ghl-pagination'><button type='button' class='ghl-btn ghl-btn--ghost' data-ghl-action='load-more' data-tab='" + escapeHtml(tab) + "'><i class='fa-solid fa-chevron-down'></i> Mostrar más</button><small>La lista se amplía por páginas para mantener Telar fluido.</small></div>";
    }

    function renderSummary(data, target) {
        var html = "<div class='ghl-kpis'>";
        [
            ["fa-address-book", "Contactos", data.contactos || 0],
            ["fa-comments", "Conversaciones", data.conversaciones || 0],
            ["fa-filter-circle-dollar", "Oportunidades", data.oportunidades || 0],
            ["fa-calendar-days", "Calendarios", data.calendarios || 0]
        ].forEach(function (item) {
            html += "<article><i class='fa-solid " + item[0] + "'></i><span><small>" + item[1] + "</small><strong>" + Number(item[2]) + "</strong></span></article>";
        });
        html += "</div><p class='ghl-readonly-note'><i class='fa-solid fa-shield-halved'></i>La consulta permanece en solo lectura. Los envíos manuales, cuando están habilitados, requieren permiso y confirmación.</p>";
        target.innerHTML = html;
    }

    function loadSummary(target, force) {
        if (state.summary && !force) { renderSummary(state.summary, target); return Promise.resolve(state.summary); }
        target.innerHTML = "<div class='ghl-inline-loading'><i class='fa-solid fa-spinner fa-spin'></i> Actualizando indicadores…</div>";
        return request("resumen", {}, 45000).then(function (data) {
            state.summary = data;
            renderSummary(data, target);
            return data;
        }).catch(function (error) {
            target.innerHTML = "<p class='ghl-inline-error'><i class='fa-solid fa-circle-exclamation'></i> " + escapeHtml(error.message) + "</p>";
            throw error;
        });
    }

    function renderConversations(data) {
        var items = data.items || [];
        var html = "<div class='ghl-section-title'><div><small>MESA DE TRABAJO PRINCIPAL</small><h2>Conversaciones recientes</h2><p>Ordenadas desde GoHighLevel y relacionadas automáticamente con Telar.</p></div><span class='ghl-total'>" + Number(data.total || items.length) + " conversaciones</span></div>";
        html += searchToolbar("conversaciones", "Buscar por nombre, teléfono o mensaje…", data);
        if (!items.length) { return html + emptyState("fa-comments", "No hay conversaciones para mostrar", state.queries.conversaciones ? "No hubo coincidencias para esta búsqueda." : "GoHighLevel no devolvió conversaciones recientes."); }
        html += "<div class='ghl-conversation-list'>";
        items.forEach(function (item) {
            html += "<article class='ghl-conversation'>" + avatar(item.nombre, item.avatar) +
                "<div class='ghl-conversation__main'><div class='ghl-conversation__top'><strong>" + escapeHtml(item.nombre) + "</strong><time>" + dateLabel(item.fecha) + "</time></div>" +
                "<div class='ghl-conversation__meta'><span class='ghl-channel'><i class='fa-brands " + (channelLabel(item.canal) === "WhatsApp" ? "fa-whatsapp" : "fa-rocketchat") + "'></i>" + escapeHtml(channelLabel(item.canal)) + "</span>" + linkBadge(item.vinculo) + (item.no_leidos ? "<span class='ghl-unread'>" + Number(item.no_leidos) + " sin leer</span>" : "") + "</div>" +
                "<p>" + escapeHtml(item.ultimo_mensaje || "Sin vista previa del mensaje") + "</p>" +
                (item.responsable ? "<small class='ghl-owner'><i class='fa-solid fa-user-check'></i> Responsable: " + escapeHtml(item.responsable) + "</small>" : "") +
                "</div><button type='button' class='ghl-open-conversation' data-ghl-action='open-conversation' data-conversation-id='" + escapeHtml(item.id) + "' title='Abrir historial'><i class='fa-solid fa-chevron-right'></i></button></article>";
        });
        return html + "</div>" + paginationFooter("conversaciones", data);
    }

    function renderContacts(data) {
        var items = data.items || [];
        var html = "<div class='ghl-section-title'><div><small>DIRECTORIO SINCRONIZADO</small><h2>Contactos</h2><p>La coincidencia con pacientes se realiza por teléfono exacto y único.</p></div><span class='ghl-total'>" + Number(data.total || items.length) + " contactos</span></div>";
        html += searchToolbar("contactos", "Buscar por nombre, teléfono o correo…", data);
        if (!items.length) { return html + emptyState("fa-address-book", "No hay contactos para mostrar", state.queries.contactos ? "No hubo coincidencias para esta búsqueda." : "GoHighLevel no devolvió contactos en esta consulta."); }
        html += "<div class='ghl-contact-grid'>";
        items.forEach(function (item) {
            var tags = "";
            (item.etiquetas || []).slice(0, 4).forEach(function (tag) { tags += "<span>" + escapeHtml(tag) + "</span>"; });
            html += "<article class='ghl-contact-card'><div class='ghl-contact-card__head'>" + avatar(item.nombre, item.avatar) + "<div><strong>" + escapeHtml(item.nombre) + "</strong><small>" + escapeHtml(item.telefono || "Sin teléfono") + "</small></div></div>" +
                (item.email ? "<p><i class='fa-solid fa-envelope'></i> " + escapeHtml(item.email) + "</p>" : "") +
                "<div class='ghl-tags'>" + tags + "</div>" + linkBadge(item.vinculo) + "</article>";
        });
        return html + "</div>" + paginationFooter("contactos", data);
    }

    function pipelineMaps(pipelines) {
        var maps = { pipeline: {}, stage: {} };
        (pipelines || []).forEach(function (pipeline) {
            maps.pipeline[pipeline.id] = pipeline.nombre;
            (pipeline.etapas || []).forEach(function (stage) { maps.stage[stage.id] = stage.nombre; });
        });
        return maps;
    }

    function renderOpportunities(data) {
        var items = data.items || [];
        var maps = pipelineMaps(data.pipelines || []);
        var html = "<div class='ghl-section-title'><div><small>PANEL SECUNDARIO</small><h2>Oportunidades</h2><p>Este panel queda separado para no entorpecer la mesa de conversaciones.</p></div><span class='ghl-total'>" + Number(data.total || items.length) + " oportunidades</span></div>";
        html += searchToolbar("oportunidades", "Buscar oportunidad, contacto o correo…", data);
        html += "<div class='ghl-pipeline-summary'>";
        (data.pipelines || []).forEach(function (pipeline) { html += "<span><strong>" + escapeHtml(pipeline.nombre) + "</strong><small>" + Number((pipeline.etapas || []).length) + " etapas</small></span>"; });
        html += "</div>";
        if (!items.length) { return html + emptyState("fa-filter-circle-dollar", "No hay oportunidades para mostrar", state.queries.oportunidades ? "No hubo coincidencias para esta búsqueda." : "Los pipelines existen, pero no se devolvieron oportunidades."); }
        html += "<div class='ghl-table-wrap'><table class='ghl-table'><thead><tr><th>Oportunidad</th><th>Pipeline</th><th>Etapa</th><th>Estado</th><th>Valor</th><th>Actualizada</th></tr></thead><tbody>";
        items.forEach(function (item) {
            html += "<tr><td><strong>" + escapeHtml(item.nombre || "Sin nombre") + "</strong></td><td>" + escapeHtml(maps.pipeline[item.pipeline_id] || "Sin pipeline") + "</td><td>" + escapeHtml(maps.stage[item.etapa_id] || "Sin etapa") + "</td><td><span class='ghl-status'>" + escapeHtml(item.estado || "abierta") + "</span></td><td>" + money(item.valor) + "</td><td>" + dateLabel(item.fecha) + "</td></tr>";
        });
        return html + "</tbody></table></div>" + paginationFooter("oportunidades", data);
    }

    function renderCalendars(data) {
        var items = data.items || [];
        var html = "<div class='ghl-section-title'><div><small>AGENDA DE HIGHLEVEL</small><h2>Calendarios</h2><p>Vista de calendarios disponibles; no crea ni modifica citas.</p></div><span class='ghl-total'>" + Number(data.total || items.length) + " calendarios</span></div>";
        if (!items.length) { return html + emptyState("fa-calendar-days", "No hay calendarios para mostrar", "La integración no devolvió calendarios disponibles."); }
        html += "<div class='ghl-calendar-grid'>";
        items.forEach(function (item) {
            html += "<article><i class='fa-solid fa-calendar-check'></i><div><strong>" + escapeHtml(item.nombre || "Calendario") + "</strong><p>" + escapeHtml(item.descripcion || "Calendario disponible en GoHighLevel") + "</p><span class='" + (item.activo ? "is-active" : "") + "'>" + (item.activo ? "Activo" : "Inactivo") + "</span></div></article>";
        });
        return html + "</div>";
    }

    function renderSync(data) {
        var link = data.vinculos || {};
        var html = "<div class='ghl-section-title'><div><small>CONTROL DE INTEGRACIÓN</small><h2>Sincronización</h2><p>Estado de la conexión y de los vínculos automáticos.</p></div><span class='ghl-total ghl-total--safe'><i class='fa-solid fa-lock'></i> Solo lectura</span></div>" +
            "<div class='ghl-sync-grid'><article><i class='fa-solid fa-cloud'></i><small>Integración</small><strong>" + (data.configurado ? "Conectada" : "Pendiente") + "</strong></article><article><i class='fa-solid fa-link'></i><small>Vinculados</small><strong>" + Number(link.vinculados || 0) + "</strong></article><article class='is-warning'><i class='fa-solid fa-triangle-exclamation'></i><small>Ambiguos</small><strong>" + Number(link.ambiguos || 0) + "</strong></article><article><i class='fa-solid fa-link-slash'></i><small>Sin coincidencia</small><strong>" + Number(link.sin_coincidencia || 0) + "</strong></article></div>" +
            "<section class='ghl-protections'><h3><i class='fa-solid fa-shield-halved'></i> Protecciones activas</h3>";
        (data.protecciones || []).forEach(function (item) { html += "<p><i class='fa-solid fa-check'></i>" + escapeHtml(item) + "</p>"; });
        return html + "</section>";
    }

    function requestPayload(tab, extra) {
        var payload = {};
        var query = state.queries[tab] || "";
        var key;
        if (query) { payload.buscar = query; }
        extra = extra || {};
        for (key in extra) {
            if (Object.prototype.hasOwnProperty.call(extra, key)) { payload[key] = extra[key]; }
        }
        return payload;
    }

    function uniqueItems(current, incoming) {
        var seen = {};
        var result = [];
        (current || []).concat(incoming || []).forEach(function (item) {
            var id = String(item && item.id || "");
            var key = id || "sin-id-" + result.length;
            if (!seen[key]) { seen[key] = true; result.push(item); }
        });
        return result;
    }

    function search(tab, value) {
        state.queries[tab] = String(value || "").trim().substring(0, 75);
        delete state.cache[tab];
        loadTab(true);
    }

    function loadMore(tab, button) {
        var current = state.cache[tab] || {};
        var pagination = current.paginacion || {};
        var extra = {};
        if (!pagination.hay_mas || button.disabled) { return; }
        if (tab === "oportunidades") { extra.pagina = pagination.siguiente_pagina || 2; }
        else {
            extra.cursor_fecha = pagination.cursor_fecha || "";
            extra.cursor_id = pagination.cursor_id || "";
        }
        button.disabled = true;
        button.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Cargando…";
        request(tab, requestPayload(tab, extra), 45000).then(function (data) {
            data.items = uniqueItems(current.items, data.items);
            if ((!data.pipelines || !data.pipelines.length) && current.pipelines) { data.pipelines = current.pipelines; }
            state.cache[tab] = data;
            renderTab(tab, data);
        }).catch(function (error) {
            setMessage(error.message, "error");
            button.disabled = false;
            button.innerHTML = "<i class='fa-solid fa-chevron-down'></i> Mostrar más";
        });
    }

    function conversationItem(conversationId) {
        var conversations = state.cache.conversaciones && state.cache.conversaciones.items || [];
        var found = null;
        conversations.some(function (item) {
            if (String(item.id) === String(conversationId)) { found = item; return true; }
            return false;
        });
        return found;
    }

    function sendToken() {
        var bytes;
        var output = "";
        var index;
        try {
            if (window.crypto && typeof window.crypto.getRandomValues === "function") {
                bytes = new Uint8Array(18);
                window.crypto.getRandomValues(bytes);
                for (index = 0; index < bytes.length; index += 1) { output += ("0" + bytes[index].toString(16)).slice(-2); }
                return output;
            }
        } catch (ignore) {}
        return "telar_" + String(Date.now()) + "_" + String(Math.random()).replace("0.", "");
    }

    function remainingWindow(seconds) {
        var total = Math.max(0, Number(seconds || 0));
        var hours = Math.floor(total / 3600);
        var minutes = Math.floor((total % 3600) / 60);
        return hours + " h " + minutes + " min";
    }

    function renderConversationComposer(item, data) {
        var windowData = data.ventana_whatsapp || {};
        var canReply = !!data.puede_responder;
        var enabled = !!data.envio_habilitado;
        var html = "<section class='ghl-composer'>";
        if (!canReply) {
            return html + "<div class='ghl-composer__blocked'><i class='fa-solid fa-user-lock'></i><div><strong>Consulta disponible</strong><p>Tu usuario no tiene permiso para responder. Puede habilitarse desde el engranaje del módulo.</p></div></div></section>";
        }
        if (!enabled) {
            return html + "<div class='ghl-composer__blocked'><i class='fa-solid fa-toggle-off'></i><div><strong>Envío preparado, todavía deshabilitado</strong><p>Falta activar el permiso mínimo de escritura en la conexión privada de HighLevel.</p></div></div></section>";
        }
        if (!windowData.abierta) {
            return html + "<div class='ghl-composer__blocked is-warning'><i class='fa-solid fa-clock'></i><div><strong>Ventana de 24 horas cerrada</strong><p>WhatsApp exige una plantilla aprobada. Esta fase no permite texto libre fuera de la ventana.</p></div></div></section>";
        }
        html += "<div class='ghl-window-open'><i class='fa-brands fa-whatsapp'></i><span><strong>Ventana de respuesta abierta</strong><small>Tiempo aproximado restante: " + escapeHtml(remainingWindow(windowData.segundos_restantes)) + "</small></span></div>";
        html += "<form data-ghl-send-form><label for='ghlManualReply'>Respuesta manual por WhatsApp</label><textarea id='ghlManualReply' maxlength='2000' rows='3' placeholder='Escriba una respuesta relacionada con la consulta del contacto…' required></textarea><div class='ghl-composer__actions'><label class='ghl-rule-check'><input type='checkbox' data-ghl-rules-confirmed><span>Confirmo que responde a la consulta actual y que revisé el destinatario.</span></label><button type='submit' class='ghl-btn ghl-btn--primary'><i class='fa-solid fa-paper-plane'></i> Revisar envío</button></div><div class='ghl-modal-message ghl-modal-message--error' data-ghl-send-error hidden></div></form>";
        html += "<div class='ghl-send-confirm' data-ghl-send-confirm hidden><div><small>CONFIRMACIÓN FINAL</small><strong>Se enviará por WhatsApp a " + escapeHtml(item.nombre || "este contacto") + "</strong><p data-ghl-send-preview></p><span>El texto no se guardará en la auditoría de Telar.</span></div><footer><button type='button' class='ghl-btn ghl-btn--ghost' data-ghl-action='cancel-send'>Volver a editar</button><button type='button' class='ghl-btn ghl-btn--primary' data-ghl-action='confirm-send'><i class='fa-solid fa-paper-plane'></i> Confirmar y enviar</button></footer><div class='ghl-modal-message ghl-modal-message--error' data-ghl-confirm-error hidden></div></div>";
        return html + "</section>";
    }

    function prepareManualReply(form) {
        var textarea = form.querySelector("textarea");
        var accepted = form.querySelector("[data-ghl-rules-confirmed]");
        var error = form.querySelector("[data-ghl-send-error]");
        var text = textarea ? textarea.value.trim() : "";
        var confirmBox;
        if (!text || text.length > 2000) {
            error.hidden = false;
            error.textContent = "Escriba un mensaje de entre 1 y 2000 caracteres.";
            return;
        }
        if (!accepted || !accepted.checked) {
            error.hidden = false;
            error.textContent = "Confirme que revisó el destinatario y que la respuesta corresponde a la consulta.";
            return;
        }
        error.hidden = true;
        state.pendingMessage = { text: text, token: sendToken() };
        form.hidden = true;
        confirmBox = state.root.querySelector("[data-ghl-send-confirm]");
        confirmBox.hidden = false;
        confirmBox.querySelector("[data-ghl-send-preview]").textContent = text;
    }

    function cancelManualReply() {
        var form = state.root.querySelector("[data-ghl-send-form]");
        var confirmBox = state.root.querySelector("[data-ghl-send-confirm]");
        state.pendingMessage = null;
        if (form) { form.hidden = false; }
        if (confirmBox) { confirmBox.hidden = true; }
    }

    function sendManualReply(button) {
        var selected = state.conversation || {};
        var pending = state.pendingMessage;
        var error = state.root.querySelector("[data-ghl-confirm-error]");
        if (!selected.item || !selected.item.id || !pending || button.disabled) { return; }
        button.disabled = true;
        button.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Enviando…";
        if (error) { error.hidden = true; }
        request("enviar_respuesta_manual", {
            conversation_id: selected.item.id,
            mensaje: pending.text,
            token_envio: pending.token,
            confirmar_reglas: 1
        }, 45000).then(function (data) {
            selected.data.items = selected.data.items || [];
            selected.data.items.push({
                id: data.message_id || pending.token,
                cuerpo: pending.text,
                direccion: "outbound",
                tipo: "WhatsApp",
                estado: "pendiente",
                fecha: new Date().toISOString(),
                adjuntos: 0
            });
            selected.data.ventana_whatsapp = data.ventana_whatsapp || selected.data.ventana_whatsapp;
            state.conversation = selected;
            state.pendingMessage = null;
            renderConversationDetail();
            setMessage("Respuesta enviada a GoHighLevel y registrada sin guardar el texto en la auditoría.", "info");
        }).catch(function (requestError) {
            if (error) {
                error.hidden = false;
                error.textContent = requestError.message;
            }
            button.disabled = false;
            button.innerHTML = "<i class='fa-solid fa-paper-plane'></i> Confirmar y enviar";
        });
    }

    function renderConversationDetail() {
        var layer = state.root.querySelector("#ghlModalLayer");
        var selected = state.conversation || {};
        var item = selected.item || {};
        var data = selected.data || {};
        var messages = data.items || [];
        var html = "<section class='ghl-modal ghl-conversation-modal' role='dialog' aria-modal='true'><header><div><small>HISTORIAL Y RESPUESTA PROTEGIDA</small><h2>" + escapeHtml(item.nombre || "Conversación") + "</h2><p>" + escapeHtml(channelLabel(item.canal)) + " · " + escapeHtml(item.telefono || "Sin teléfono disponible") + "</p></div><button type='button' data-ghl-action='close-settings' title='Cerrar'><i class='fa-solid fa-xmark'></i></button></header><div class='ghl-modal__body'>";
        if (data.paginacion && data.paginacion.hay_mas) {
            html += "<div class='ghl-older'><button type='button' class='ghl-btn ghl-btn--ghost' data-ghl-action='load-older-messages'><i class='fa-solid fa-clock-rotate-left'></i> Cargar mensajes anteriores</button></div>";
        }
        html += "<div class='ghl-message-history'>";
        if (!messages.length) {
            html += "<p class='ghl-inline-error'>Esta conversación todavía no devolvió mensajes.</p>";
        }
        messages.forEach(function (message) {
            var outbound = String(message.direccion || "").toLowerCase() === "outbound";
            var body = message.cuerpo || ("Actividad: " + channelLabel(message.tipo));
            html += "<article class='ghl-message-bubble " + (outbound ? "is-outbound" : "is-inbound") + "'><small>" + (outbound ? "Equipo" : escapeHtml(item.nombre || "Contacto")) + " · " + escapeHtml(channelLabel(message.tipo)) + "</small><p>" + escapeHtml(body) + "</p><footer><time>" + dateLabel(message.fecha) + "</time>" + (message.estado ? "<span>" + escapeHtml(message.estado) + "</span>" : "") + (message.adjuntos ? "<span><i class='fa-solid fa-paperclip'></i> " + Number(message.adjuntos) + " adjunto(s)</span>" : "") + "</footer></article>";
        });
        html += "</div>" + renderConversationComposer(item, data) + "</div><footer><span class='ghl-readonly-note'><i class='fa-solid fa-shield-halved'></i>Telar valida permiso, canal y ventana antes de cada envío.</span><button type='button' class='ghl-btn ghl-btn--ghost' data-ghl-action='close-settings'>Cerrar</button></footer></section>";
        layer.innerHTML = html;
    }

    function openConversation(conversationId) {
        var layer = state.root.querySelector("#ghlModalLayer");
        var item = conversationItem(conversationId);
        if (!item || !item.id) { setMessage("La conversación seleccionada no está disponible.", "error"); return; }
        layer.hidden = false;
        layer.innerHTML = "<section class='ghl-modal ghl-conversation-modal'><header><div><small>HISTORIAL Y RESPUESTA PROTEGIDA</small><h2>" + escapeHtml(item.nombre) + "</h2></div><button type='button' data-ghl-action='close-settings'><i class='fa-solid fa-xmark'></i></button></header><div class='ghl-loading'><i class='fa-solid fa-spinner fa-spin'></i><strong>Cargando conversación…</strong></div></section>";
        request("mensajes_conversacion", { conversation_id: item.id, limite: 50 }, 45000).then(function (data) {
            state.conversation = { item: item, data: data };
            renderConversationDetail();
        }).catch(function (error) {
            layer.innerHTML = "<section class='ghl-modal ghl-conversation-modal'><header><h2>No se pudo abrir la conversación</h2><button type='button' data-ghl-action='close-settings'><i class='fa-solid fa-xmark'></i></button></header><div class='ghl-modal__body'><p class='ghl-inline-error'>" + escapeHtml(error.message) + "</p></div></section>";
        });
    }

    function loadOlderMessages(button) {
        var selected = state.conversation || {};
        var pagination = selected.data && selected.data.paginacion || {};
        if (!selected.item || !pagination.hay_mas || !pagination.last_message_id || button.disabled) { return; }
        button.disabled = true;
        button.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Cargando…";
        request("mensajes_conversacion", {
            conversation_id: selected.item.id,
            last_message_id: pagination.last_message_id,
            limite: 50
        }, 45000).then(function (data) {
            data.items = uniqueItems(data.items, selected.data.items);
            data.ventana_whatsapp = selected.data.ventana_whatsapp || data.ventana_whatsapp;
            data.puede_responder = selected.data.puede_responder;
            data.envio_habilitado = selected.data.envio_habilitado;
            selected.data = data;
            state.conversation = selected;
            renderConversationDetail();
        }).catch(function (error) {
            setMessage(error.message, "error");
            button.disabled = false;
            button.innerHTML = "<i class='fa-solid fa-clock-rotate-left'></i> Cargar mensajes anteriores";
        });
    }

    function loadTab(force) {
        var action = state.tab;
        var content = state.root.querySelector("#ghlContent");
        var labels = { conversaciones: "Cargando conversaciones reales…", contactos: "Vinculando contactos con pacientes…", oportunidades: "Cargando pipelines y oportunidades…", calendarios: "Cargando calendarios…", resumen: "Actualizando resumen…", sincronizacion: "Comprobando sincronización…" };
        if (state.cache[action] && !force) { renderTab(action, state.cache[action]); return; }
        setLoading(labels[action]);
        request(action, requestPayload(action), action === "resumen" ? 45000 : 40000).then(function (data) {
            state.cache[action] = data;
            state.loading = false;
            renderTab(action, data);
        }).catch(function (error) {
            state.loading = false;
            content.innerHTML = emptyState("fa-triangle-exclamation", "No se pudo cargar esta sección", error.message);
            setMessage(error.message, "error");
        });
    }

    function renderTab(tab, data) {
        var content = state.root.querySelector("#ghlContent");
        setMessage("", "info");
        if (tab === "conversaciones") { content.innerHTML = renderConversations(data); }
        else if (tab === "contactos") { content.innerHTML = renderContacts(data); }
        else if (tab === "oportunidades") { content.innerHTML = renderOpportunities(data); }
        else if (tab === "calendarios") { content.innerHTML = renderCalendars(data); }
        else if (tab === "sincronizacion") { content.innerHTML = renderSync(data); }
        else if (tab === "resumen") {
            state.summary = data;
            content.innerHTML = "<div class='ghl-section-title'><div><small>VISIÓN GENERAL</small><h2>Resumen</h2><p>Indicadores generales consultados en tiempo real.</p></div></div><div id='ghlTabSummary'></div>";
            renderSummary(data, content.querySelector("#ghlTabSummary"));
        }
    }

    function selectTab(tab) {
        state.tab = tab;
        Array.prototype.forEach.call(state.root.querySelectorAll("[data-ghl-tab]"), function (button) {
            button.classList.toggle("is-active", button.getAttribute("data-ghl-tab") === tab);
        });
        loadTab(false);
    }

    function toggleSummary() {
        var body = state.root.querySelector("#ghlSummaryBody");
        var strip = state.root.querySelector("#ghlSummaryStrip");
        state.summaryOpen = !state.summaryOpen;
        body.hidden = !state.summaryOpen;
        strip.classList.toggle("is-open", state.summaryOpen);
        if (state.summaryOpen) { loadSummary(body, false).catch(function () {}); }
    }

    function openSettings() {
        var layer = state.root.querySelector("#ghlModalLayer");
        layer.hidden = false;
        layer.innerHTML = "<section class='ghl-modal'><header><div><small>CONFIGURACIONES Y PERMISOS</small><h2>Preparando equipo…</h2></div><button type='button' data-ghl-action='close-settings'><i class='fa-solid fa-xmark'></i></button></header><div class='ghl-loading'><i class='fa-solid fa-spinner fa-spin'></i></div></section>";
        request("configuracion_permisos").then(function (data) {
            state.settings = data;
            renderSettings();
        }).catch(function (error) {
            layer.innerHTML = "<section class='ghl-modal'><header><h2>No se pudo abrir la configuración</h2><button type='button' data-ghl-action='close-settings'><i class='fa-solid fa-xmark'></i></button></header><div class='ghl-modal__body'><p class='ghl-inline-error'>" + escapeHtml(error.message) + "</p></div></section>";
        });
    }

    function renderSettings() {
        var layer = state.root.querySelector("#ghlModalLayer");
        var rows = "";
        (state.settings.usuarios || []).forEach(function (user) {
            rows += "<tr data-user='" + Number(user.cod_usuario) + "'><td><div class='ghl-user'>" + avatar(user.nombre, user.avatar, "ghl-avatar--small") + "<span><strong>" + escapeHtml(user.nombre) + "</strong><small>" + escapeHtml(user.local || "Sin local") + "</small></span></div></td><td><label class='ghl-switch'><input type='checkbox' data-permission='view' " + (user.puede_ver ? "checked" : "") + " " + (user.bloqueado ? "disabled" : "") + "><span></span></label></td><td><label class='ghl-switch'><input type='checkbox' data-permission='reply' " + (user.puede_responder ? "checked" : "") + " " + (user.bloqueado ? "disabled" : "") + "><span></span></label></td><td><label class='ghl-switch'><input type='checkbox' data-permission='admin' " + (user.puede_configurar ? "checked" : "") + " " + (user.bloqueado ? "disabled" : "") + "><span></span></label></td></tr>";
        });
        layer.innerHTML = "<section class='ghl-modal' role='dialog' aria-modal='true'><header><div><small>ENGRANAJE DEL MÓDULO</small><h2>Configuraciones y permisos</h2><p>Defina por separado quién consulta, quién responde y quién administra.</p></div><button type='button' data-ghl-action='close-settings'><i class='fa-solid fa-xmark'></i></button></header><div class='ghl-modal__body'><div class='ghl-security-note'><i class='fa-solid fa-lock'></i><p><strong>El token nunca se muestra aquí.</strong><br>Responder solo habilita envíos manuales dentro de la ventana de WhatsApp; no permite editar contactos, oportunidades ni flujos.</p></div><div class='ghl-permission-table'><table><thead><tr><th>Usuario</th><th>Puede ver</th><th>Responde</th><th>Administra</th></tr></thead><tbody>" + rows + "</tbody></table></div><div id='ghlSettingsMessage' class='ghl-modal-message' hidden></div></div><footer><button type='button' class='ghl-btn ghl-btn--ghost' data-ghl-action='close-settings'>Cancelar</button><button type='button' class='ghl-btn ghl-btn--primary' data-ghl-action='save-settings'><i class='fa-solid fa-floppy-disk'></i>Guardar permisos</button></footer></section>";
    }

    function closeSettings() {
        var layer = state.root.querySelector("#ghlModalLayer");
        layer.hidden = true;
        layer.innerHTML = "";
        state.settings = null;
        state.conversation = null;
        state.pendingMessage = null;
    }

    function saveSettings(button) {
        var rows = state.root.querySelectorAll("#ghlModalLayer [data-user]");
        var permissions = [];
        var message = state.root.querySelector("#ghlSettingsMessage");
        Array.prototype.forEach.call(rows, function (row) {
            var view = row.querySelector("[data-permission='view']");
            var reply = row.querySelector("[data-permission='reply']");
            var admin = row.querySelector("[data-permission='admin']");
            permissions.push({ cod_usuario: Number(row.getAttribute("data-user")), puede_ver: view.checked ? 1 : 0, puede_responder: reply.checked ? 1 : 0, puede_configurar: admin.checked ? 1 : 0 });
        });
        button.disabled = true;
        message.hidden = false;
        message.className = "ghl-modal-message ghl-modal-message--info";
        message.textContent = "Guardando permisos y trazabilidad…";
        request("guardar_permisos", { permisos: JSON.stringify(permissions) }).then(function (data) {
            state.settings = data;
            message.className = "ghl-modal-message ghl-modal-message--success";
            message.textContent = "Permisos guardados correctamente.";
            window.setTimeout(closeSettings, 850);
        }).catch(function (error) {
            message.className = "ghl-modal-message ghl-modal-message--error";
            message.textContent = error.message;
            button.disabled = false;
        });
    }

    function loadContext() {
        var connection = state.root.querySelector("#ghlConnection");
        connection.textContent = "Comprobando conexión…";
        connection.className = "ghl-connection";
        request("contexto").then(function (data) {
            state.context = data;
            connection.textContent = data.integracion && data.integracion.configurado
                ? (data.integracion.respuestas_habilitadas ? "Conectado · Respuestas protegidas" : "Conectado · Solo lectura")
                : "Configuración pendiente";
            connection.className = "ghl-connection " + (data.integracion && data.integracion.configurado ? "is-online" : "is-pending");
            state.root.querySelector("#ghlSettingsButton").hidden = !(data.usuario && data.usuario.puede_configurar);
            loadTab(true);
        }).catch(function (error) {
            connection.textContent = "Sin conexión";
            connection.className = "ghl-connection is-offline";
            state.root.querySelector("#ghlContent").innerHTML = emptyState("fa-triangle-exclamation", "No se pudo abrir GoHighLevel", error.message);
        });
    }

    function refresh() {
        state.cache = {};
        state.summary = null;
        loadTab(true);
        if (state.summaryOpen) { loadSummary(state.root.querySelector("#ghlSummaryBody"), true).catch(function () {}); }
    }

    function bindEvents() {
        state.root.addEventListener("click", function (event) {
            var tab = event.target.closest("[data-ghl-tab]");
            var button = event.target.closest("[data-ghl-action]");
            var action;
            if (tab) { selectTab(tab.getAttribute("data-ghl-tab")); return; }
            if (!button) { return; }
            action = button.getAttribute("data-ghl-action");
            if (action === "close") { window.cerrarGoHighLevel(); }
            else if (action === "minimize") { window.minimizarGoHighLevel(); }
            else if (action === "refresh") { refresh(); }
            else if (action === "toggle-summary") { toggleSummary(); }
            else if (action === "settings") { openSettings(); }
            else if (action === "close-settings") { closeSettings(); }
            else if (action === "save-settings") { saveSettings(button); }
            else if (action === "clear-search") {
                state.queries[button.getAttribute("data-tab")] = "";
                delete state.cache[button.getAttribute("data-tab")];
                loadTab(true);
            }
            else if (action === "load-more") { loadMore(button.getAttribute("data-tab"), button); }
            else if (action === "open-conversation") { openConversation(button.getAttribute("data-conversation-id")); }
            else if (action === "load-older-messages") { loadOlderMessages(button); }
            else if (action === "cancel-send") { cancelManualReply(); }
            else if (action === "confirm-send") { sendManualReply(button); }
        });
        state.root.addEventListener("submit", function (event) {
            var form = event.target.closest("[data-ghl-search-form]");
            var sendForm = event.target.closest("[data-ghl-send-form]");
            var tab;
            var input;
            if (sendForm) {
                event.preventDefault();
                prepareManualReply(sendForm);
                return;
            }
            if (!form) { return; }
            event.preventDefault();
            tab = form.getAttribute("data-ghl-search-form");
            input = form.querySelector("[data-ghl-search-input]");
            search(tab, input ? input.value : "");
        });
        state.root.addEventListener("change", function (event) {
            var field = event.target;
            var row;
            if (!field.matches("[data-permission='admin'],[data-permission='reply']")) { return; }
            row = field.closest("[data-user]");
            if (field.checked && row) { row.querySelector("[data-permission='view']").checked = true; }
        });
    }

    window.abrirGoHighLevel = function () {
        var container = document.getElementById("divGoHighLevel");
        var marker;
        if (!container || !mount()) { return; }
        container.style.display = "";
        container.setAttribute("aria-hidden", "false");
        marker = document.getElementById("divMinimizadoGoHighLevel");
        if (marker) { marker.style.display = "none"; }
        state.open = true;
        if (document.body) { document.body.classList.add("gohighlevel-open"); }
        loadContext();
    };

    window.cerrarGoHighLevel = function () {
        var container = document.getElementById("divGoHighLevel");
        if (container) { container.style.display = "none"; container.setAttribute("aria-hidden", "true"); }
        state.open = false;
        closeSettings();
        if (document.body) { document.body.classList.remove("gohighlevel-open"); }
    };

    window.minimizarGoHighLevel = function () {
        var marker = document.getElementById("divMinimizadoGoHighLevel");
        window.cerrarGoHighLevel();
        if (marker) { marker.style.display = ""; }
    };

    window.addEventListener("keydown", function (event) {
        if (event.key !== "Escape" || !state.open) { return; }
        if (state.root && !state.root.querySelector("#ghlModalLayer").hidden) { closeSettings(); }
        else { window.cerrarGoHighLevel(); }
    });
})(window, document);

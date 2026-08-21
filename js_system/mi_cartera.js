/* Sistema Telar - Mi cartera. Compatible con navegadores Android actuales. */
(function (window, document) {
    "use strict";

    var ENDPOINT = "/GoodVentaAsisCap/php_system/abmMiCartera.php";
    var PHONE_ENDPOINT = "/GoodVentaAsisCap/php_system/abmCentralTelefonicaOperacion.php";
    var state = {
        root: null,
        open: false,
        mounted: false,
        loading: false,
        context: null,
        list: null,
        view: "mi_cartera",
        page: 1,
        expanded: 0,
        details: {},
        pendingCalls: {},
        configCatalog: null,
        pendingConfig: null,
        searchTimer: null
    };

    function escapeHtml(value) {
        return String(value === null || typeof value === "undefined" ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function credentials(formData) {
        try {
            if (typeof window.obtener_datos_user === "function") { window.obtener_datos_user(); }
        } catch (ignore) {}
        formData.append("useru", typeof window.userid !== "undefined" ? window.userid : "");
        formData.append("passu", typeof window.passuser !== "undefined" ? window.passuser : "");
        formData.append("navegador", typeof window.navegador !== "undefined" ? window.navegador : "");
    }

    function requestTo(endpoint, action, payload, timeout) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            var form = new FormData();
            var key;
            form.append("accion", action);
            credentials(form);
            payload = payload || {};
            for (key in payload) {
                if (Object.prototype.hasOwnProperty.call(payload, key)) {
                    form.append(key, payload[key]);
                }
            }
            xhr.open("POST", endpoint, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.timeout = timeout || 30000;
            xhr.onreadystatechange = function () {
                var data;
                var error;
                if (xhr.readyState !== 4) { return; }
                try {
                    data = JSON.parse((xhr.responseText || "").replace(/^\uFEFF/, ""));
                } catch (ignore) {
                    reject(new Error("Telar no devolvió una respuesta válida."));
                    return;
                }
                if (xhr.status < 200 || xhr.status >= 300 || !data.ok) {
                    error = new Error(data.mensaje || "No se pudo completar la operación.");
                    error.code = data.codigo || "";
                    error.data = data.datos || {};
                    reject(error);
                    return;
                }
                resolve(data.datos || {});
            };
            xhr.onerror = function () { reject(new Error("No se pudo comunicar con Telar.")); };
            xhr.ontimeout = function () { reject(new Error("La operación tardó demasiado. Intente nuevamente.")); };
            xhr.send(form);
        });
    }

    function request(action, payload, timeout) {
        return requestTo(ENDPOINT, action, payload, timeout);
    }

    function phoneRequest(action, payload) {
        return requestTo(PHONE_ENDPOINT, action, payload, 25000);
    }

    function money(value) {
        var number = Number(value || 0);
        try {
            return new Intl.NumberFormat("es-PY", { maximumFractionDigits: 0 }).format(number) + " Gs.";
        } catch (ignore) {
            return String(Math.round(number)).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " Gs.";
        }
    }

    function dateLabel(value, withTime) {
        var text = String(value || "");
        var parts;
        if (!text) { return "—"; }
        parts = text.substring(0, 10).split("-");
        if (parts.length !== 3) { return text; }
        return parts[2] + "/" + parts[1] + "/" + parts[0]
            + (withTime && text.length >= 16 ? " " + text.substring(11, 16) : "");
    }

    function sourceLabel(source) {
        var labels = {
            principal: "Principal",
            whatsapp: "WhatsApp",
            trabajo1: "Trabajo 1",
            trabajo2: "Trabajo 2"
        };
        return labels[source] || "Teléfono";
    }

    function setMessage(text, kind) {
        var element = state.root ? state.root.querySelector("#miCarteraMessage") : null;
        if (!element) { return; }
        if (!text) {
            element.hidden = true;
            element.textContent = "";
            return;
        }
        element.hidden = false;
        element.className = "mi-cartera-message mi-cartera-message--" + (kind || "info");
        element.textContent = text;
    }

    function button(icon, text, action, extraClass) {
        return "<button type='button' class='mi-cartera-btn " + escapeHtml(extraClass || "")
            + "' data-cartera-action='" + escapeHtml(action) + "'><i class='fa-solid "
            + escapeHtml(icon) + "' aria-hidden='true'></i><span>" + escapeHtml(text) + "</span></button>";
    }

    function mount() {
        var container = document.getElementById("telarMiCartera");
        if (!container) { return false; }
        if (state.mounted) {
            state.root = container.querySelector(".mi-cartera");
            return !!state.root;
        }
        container.innerHTML = ""
            + "<section class='mi-cartera' aria-label='Mesa de trabajo Mi cartera'>"
            + "<header class='mi-cartera-header'>"
            + "<div class='mi-cartera-brand'><span class='mi-cartera-brand__icon'><i class='fa-solid fa-users-viewfinder'></i></span><div>"
            + "<small>TELAR · COBRANZAS</small><h1>Mi cartera</h1><p>Seguimiento guiado de pacientes y compromisos de pago</p></div></div>"
            + "<div class='mi-cartera-header__actions'>"
            + "<span id='miCarteraPhoneState' class='mi-cartera-phone-state'><i class='fa-solid fa-phone'></i> Consultando telefonía</span>"
            + button("fa-rotate-right", "Actualizar", "refresh", "mi-cartera-btn--ghost")
            + "<button type='button' id='miCarteraConfigButton' class='mi-cartera-icon-btn' data-cartera-action='open-config' title='Configurar responsables' aria-label='Configurar responsables' hidden><i class='fa-solid fa-gear'></i></button>"
            + "<button type='button' class='mi-cartera-icon-btn' data-cartera-action='minimize' title='Minimizar' aria-label='Minimizar'><i class='fa-solid fa-minus'></i></button>"
            + "<button type='button' class='mi-cartera-icon-btn mi-cartera-icon-btn--close' data-cartera-action='close' title='Cerrar' aria-label='Cerrar'><i class='fa-solid fa-xmark'></i></button>"
            + "</div></header>"
            + "<main class='mi-cartera-main'>"
            + "<div id='miCarteraMessage' class='mi-cartera-message' hidden></div>"
            + "<section class='mi-cartera-guide' aria-label='Guía rápida'><div><i class='fa-solid fa-route'></i><strong>Un vistazo al proceso</strong></div>"
            + "<ol><li><b>1</b><span><strong>Llamá</strong><small>El paciente y su saldo ya están a la vista.</small></span></li>"
            + "<li><b>2</b><span><strong>Registrá el resultado</strong><small>Dejá una evidencia breve y concreta.</small></span></li>"
            + "<li><b>3</b><span><strong>Definí lo siguiente</strong><small>Ningún caso queda sin próxima acción.</small></span></li></ol>"
            + "<p><i class='fa-solid fa-circle-user'></i><span><strong>Responsable actual:</strong> quien debe hacer avanzar el caso. <strong>Resultado:</strong> lo que ocurrió en el último contacto.</span></p></section>"
            + "<section id='miCarteraKpis' class='mi-cartera-kpis' aria-label='Resumen de cartera'></section>"
            + "<section class='mi-cartera-toolbar'>"
            + "<nav id='miCarteraTabs' class='mi-cartera-tabs' aria-label='Vistas de cartera'>"
            + "<button type='button' data-cartera-view='mi_cartera' class='is-active'><i class='fa-solid fa-user-check'></i> Mi cartera</button>"
            + "<button type='button' data-cartera-view='equipo' data-supervisor-only hidden><i class='fa-solid fa-people-group'></i> Equipo</button>"
            + "<button type='button' data-cartera-view='sin_asignar' data-supervisor-only hidden><i class='fa-solid fa-user-plus'></i> Sin asignar</button>"
            + "<button type='button' data-cartera-view='seguimiento'><i class='fa-solid fa-calendar-check'></i> Seguimiento</button>"
            + "</nav>"
            + "<div class='mi-cartera-toolbar__actions'><button id='miCarteraDistributeButton' type='button' class='mi-cartera-btn mi-cartera-btn--primary' data-cartera-action='preview-distribution' hidden><i class='fa-solid fa-diagram-project'></i><span>Repartir cartera</span></button></div>"
            + "</section>"
            + "<section class='mi-cartera-filters'>"
            + "<label class='mi-cartera-search'><i class='fa-solid fa-magnifying-glass'></i><input id='miCarteraSearch' type='search' placeholder='Buscar paciente, cédula, código o teléfono' autocomplete='off'></label>"
            + "<label><span>Estado</span><select id='miCarteraStateFilter'><option value=''>Todos</option><option value='preventivo'>Por vencer</option><option value='vencido'>Vencido</option><option value='urgente'>Urgente</option><option value='pagado'>Pago confirmado</option></select></label>"
            + "<label><span>Local de origen</span><select id='miCarteraLocalFilter'><option value='0'>Todos</option></select></label>"
            + "<label data-supervisor-only hidden><span>Responsable</span><select id='miCarteraResponsibleFilter'><option value='0'>Todos</option></select></label>"
            + "<label><span>Prioridad</span><select id='miCarteraPriorityFilter'><option value=''>Todas</option><option value='alta'>Alta</option><option value='media'>Media</option><option value='baja'>Baja</option></select></label>"
            + "<button type='button' class='mi-cartera-clear' data-cartera-action='clear-filters'><i class='fa-solid fa-filter-circle-xmark'></i> Limpiar</button>"
            + "</section>"
            + "<section class='mi-cartera-table-card'>"
            + "<div class='mi-cartera-table-wrap'><table><thead><tr><th>Paciente</th><th>Origen</th><th>Saldo y antigüedad</th><th>Responsable y resultado</th><th>Próxima acción</th><th class='mi-cartera-table__actions'>Acciones</th></tr></thead><tbody id='miCarteraRows'></tbody></table></div>"
            + "<footer id='miCarteraPager' class='mi-cartera-pager'></footer>"
            + "</section></main>"
            + "<div id='miCarteraModalLayer' class='mi-cartera-modal-layer' hidden></div>"
            + "</section>";
        state.root = container.querySelector(".mi-cartera");
        state.mounted = true;
        bindEvents();
        return true;
    }

    function contextOptions() {
        var config = state.context ? state.context.configuracion || {} : {};
        var locals = {};
        var users = {};
        var localSelect = state.root.querySelector("#miCarteraLocalFilter");
        var responsibleSelect = state.root.querySelector("#miCarteraResponsibleFilter");
        var managers = config.gestores || [];
        var collectors = config.cobradores || [];
        var htmlLocal = "<option value='0'>Todos</option>";
        var htmlUsers = "<option value='0'>Todos</option>";
        managers.forEach(function (item) {
            locals[item.cod_local] = item.local;
            users[item.cod_usuario] = item.nombre;
        });
        collectors.forEach(function (item) { users[item.cod_usuario] = item.nombre; });
        if (config.cod_jefe) {
            users[config.cod_jefe] = "Jefe de Cobranza";
        }
        Object.keys(locals).sort(function (a, b) {
            return String(locals[a]).localeCompare(String(locals[b]));
        }).forEach(function (key) {
            htmlLocal += "<option value='" + escapeHtml(key) + "'>" + escapeHtml(locals[key]) + "</option>";
        });
        Object.keys(users).sort(function (a, b) {
            return String(users[a]).localeCompare(String(users[b]));
        }).forEach(function (key) {
            htmlUsers += "<option value='" + escapeHtml(key) + "'>" + escapeHtml(users[key]) + "</option>";
        });
        localSelect.innerHTML = htmlLocal;
        responsibleSelect.innerHTML = htmlUsers;
    }

    function applyContext() {
        var user = state.context ? state.context.usuario || {} : {};
        var config = state.context ? state.context.configuracion || {} : {};
        var phone = state.context ? state.context.telefonia || {} : {};
        var phoneElement = state.root.querySelector("#miCarteraPhoneState");
        var configButton = state.root.querySelector("#miCarteraConfigButton");
        var distributionButton = state.root.querySelector("#miCarteraDistributeButton");
        var supervisorElements = state.root.querySelectorAll("[data-supervisor-only]");
        var i;
        configButton.hidden = !user.puede_configurar;
        distributionButton.hidden = !user.puede_supervisar;
        for (i = 0; i < supervisorElements.length; i++) {
            supervisorElements[i].hidden = !user.puede_supervisar;
        }
        phoneElement.className = "mi-cartera-phone-state " + (phone.activo ? "is-online" : "is-offline");
        phoneElement.innerHTML = "<i class='fa-solid " + (phone.activo ? "fa-phone-volume" : "fa-phone-slash")
            + "'></i> " + (phone.activo ? "MicroSIP · Interno " + escapeHtml(phone.extension || "sin asignar") : "Llamada desde Telar no disponible");
        phoneElement.title = phone.mensaje || "";
        contextOptions();
        if (!config.completa) {
            setMessage(
                user.puede_configurar
                    ? "Antes del primer reparto, configure el jefe, al menos un gestor y un cobrador desde el engranaje."
                    : "El equipo de cartera todavía no está completamente configurado.",
                "warning"
            );
        }
    }

    function renderKpis(kpis) {
        var container = state.root.querySelector("#miCarteraKpis");
        kpis = kpis || {};
        container.innerHTML = ""
            + "<article><span class='mi-cartera-kpi-icon mi-cartera-kpi-icon--blue'><i class='fa-solid fa-address-book'></i></span><div><small>Pacientes en seguimiento</small><strong>" + Number(kpis.pacientes || 0) + "</strong></div></article>"
            + "<article><span class='mi-cartera-kpi-icon mi-cartera-kpi-icon--violet'><i class='fa-solid fa-wallet'></i></span><div><small>Saldo de la vista</small><strong>" + escapeHtml(money(kpis.saldo_total || 0)) + "</strong></div></article>"
            + "<article><span class='mi-cartera-kpi-icon mi-cartera-kpi-icon--red'><i class='fa-solid fa-triangle-exclamation'></i></span><div><small>Atención urgente</small><strong>" + Number(kpis.urgentes || 0) + "</strong></div></article>"
            + "<article><span class='mi-cartera-kpi-icon mi-cartera-kpi-icon--amber'><i class='fa-solid fa-handshake'></i></span><div><small>Promesas para hoy</small><strong>" + Number(kpis.promesas_hoy || 0) + "</strong></div></article>"
            + "<article><span class='mi-cartera-kpi-icon mi-cartera-kpi-icon--teal'><i class='fa-solid fa-coins'></i></span><div><small>Recuperado este mes</small><strong>" + escapeHtml(money(kpis.recuperado_mes || 0)) + "</strong></div></article>";
    }

    function stateBadge(item) {
        var labels = { preventivo: "Por vencer", vencido: "Vencido", urgente: "Urgente", pagado: "Pago confirmado" };
        return "<span class='mi-cartera-badge mi-cartera-badge--" + escapeHtml(item.estado) + "'>"
            + escapeHtml(labels[item.estado] || item.estado) + "</span>";
    }

    function resultBadge(item) {
        var value = item.resultado || "sin_gestion";
        return "<span class='mi-cartera-result mi-cartera-result--" + escapeHtml(value) + "'>"
            + escapeHtml(item.resultado_etiqueta || "Sin gestión") + "</span>";
    }

    function rowHtml(item) {
        var finance = item.finanzas || {};
        var phones = item.telefonos || [];
        var mainPhone = phones.length ? phones[0].numero : "";
        var phoneReady = state.context && state.context.telefonia && state.context.telefonia.activo && mainPhone;
        var next = item.proxima_accion
            ? "<strong>" + escapeHtml(dateLabel(item.proxima_accion, true)) + "</strong><small>Seguimiento programado</small>"
            : "<strong class='is-missing'>Definir siguiente acción</strong><small>El caso necesita continuidad</small>";
        return "<tr class='mi-cartera-row mi-cartera-row--" + escapeHtml(item.estado) + "' data-assignment='" + item.id_asignacion + "'>"
            + "<td><div class='mi-cartera-patient'><span class='mi-cartera-patient__avatar'><i class='fa-solid fa-user'></i></span><div><strong>" + escapeHtml(item.paciente) + "</strong><span>CI " + escapeHtml(item.documento || "sin registrar") + " · Paciente " + item.cod_cliente + "</span><small>" + escapeHtml(mainPhone || "Sin teléfono vigente") + "</small></div></div></td>"
            + "<td><strong>" + escapeHtml(item.local_origen || "Sin local") + "</strong><small>Origen: obligación más antigua</small><span>" + escapeHtml(finance.locales || "") + "</span></td>"
            + "<td><strong class='mi-cartera-money'>" + escapeHtml(money(finance.saldo_total || 0)) + "</strong><div class='mi-cartera-row-badges'>" + stateBadge(item) + "<span class='mi-cartera-priority mi-cartera-priority--" + escapeHtml(item.prioridad) + "'>" + escapeHtml(item.prioridad_etiqueta) + "</span></div><small>" + Number(finance.dias_mora || 0) + " días · " + Number(finance.cuotas_vencidas || 0) + " cuota(s) vencida(s)</small></td>"
            + "<td><div class='mi-cartera-owner'><img src='" + escapeHtml(item.avatar_responsable) + "' alt=''><div><strong>" + escapeHtml(item.responsable || "Sin asignar") + "</strong><small>Responsable actual</small></div></div>" + resultBadge(item) + "</td>"
            + "<td class='mi-cartera-next'>" + next + "</td>"
            + "<td class='mi-cartera-row-actions'><button type='button' class='mi-cartera-call' data-cartera-action='call' data-assignment='" + item.id_asignacion + "' data-client='" + item.cod_cliente + "' data-phone='" + escapeHtml(mainPhone) + "' " + (phoneReady ? "" : "disabled") + " title='" + escapeHtml(phoneReady ? "Llamar desde Telar" : "Puede llamar directamente desde MicroSIP y registrar el resultado") + "'><i class='fa-solid fa-phone'></i><span>Llamar</span></button><button type='button' class='mi-cartera-expand' data-cartera-action='expand' data-assignment='" + item.id_asignacion + "' aria-label='Abrir seguimiento'><i class='fa-solid " + (state.expanded === item.id_asignacion ? "fa-chevron-up" : "fa-chevron-down") + "'></i></button></td></tr>"
            + (state.expanded === item.id_asignacion ? expandedHtml(item) : "");
    }

    function expandedHtml(item) {
        var detail = state.details[item.id_asignacion];
        var resultOptions = state.context ? state.context.resultados || [] : [];
        var currentUser = state.context ? state.context.usuario || {} : {};
        var phoneOptions = "<option value=''>Seleccione el teléfono utilizado</option>";
        var resultHtml = "<option value=''>Seleccione el resultado</option>";
        var takeChief = currentUser.es_jefe && Number(item.cod_responsable || 0) !== Number(currentUser.cod_usuario || 0)
            ? "<button type='button' class='mi-cartera-btn mi-cartera-btn--chief' data-cartera-action='take-chief-case' data-assignment='" + item.id_asignacion + "'><i class='fa-solid fa-user-shield'></i><span>Tomar como caso especial</span></button>"
            : "";
        var history = "";
        var sales = "";
        (item.telefonos || []).forEach(function (phone) {
            phoneOptions += "<option value='" + escapeHtml(phone.numero) + "'>" + escapeHtml(sourceLabel(phone.fuente) + " · " + phone.numero) + "</option>";
        });
        resultOptions.forEach(function (result) {
            resultHtml += "<option value='" + escapeHtml(result.valor) + "'>" + escapeHtml(result.etiqueta) + "</option>";
        });
        if (detail && detail.gestiones) {
            detail.gestiones.slice(0, 5).forEach(function (entry) {
                history += "<article><img src='" + escapeHtml(entry.avatar) + "' alt=''><div><strong>" + escapeHtml(entry.resultado_etiqueta) + "</strong><span>" + escapeHtml(entry.usuario) + " · " + escapeHtml(dateLabel(entry.fecha, true)) + "</span><p>" + escapeHtml(entry.nota || "Sin observación") + "</p></div></article>";
            });
            detail.ventas.forEach(function (sale) {
                sales += "<article><strong>Venta " + escapeHtml(sale.venta) + "</strong><span>" + escapeHtml(sale.local) + " · " + Number(sale.cuotas) + " cuota(s)</span><small>" + escapeHtml(sale.productos || "Sin detalle de tratamiento") + "</small><b>" + escapeHtml(money(sale.saldo)) + "</b></article>";
            });
        }
        return "<tr class='mi-cartera-expanded'><td colspan='6'>"
            + "<div class='mi-cartera-workflow'>"
            + "<div class='mi-cartera-workflow__steps'><span class='is-active'><b>1</b><strong>Llamar</strong><small>Elegí un teléfono</small></span><i></i><span><b>2</b><strong>Resultado</strong><small>Qué ocurrió</small></span><i></i><span><b>3</b><strong>Continuidad</strong><small>Cuándo retomar</small></span></div>"
            + "<div class='mi-cartera-workflow__columns'>"
            + "<section class='mi-cartera-manage'><h3>Registrar seguimiento</h3><p>La llamada queda vinculada si se inició desde este botón. Si llamó por MicroSIP, igual puede registrar el resultado.</p>"
            + "<div class='mi-cartera-manage__grid'><label><span>Teléfono usado</span><select data-manage-field='telefono'>" + phoneOptions + "</select></label><label><span>Resultado de la llamada</span><select data-manage-field='resultado'>" + resultHtml + "</select></label><label><span>Prioridad</span><select data-manage-field='prioridad'><option value='alta' " + (item.prioridad === "alta" ? "selected" : "") + ">Alta</option><option value='media' " + (item.prioridad === "media" ? "selected" : "") + ">Media</option><option value='baja' " + (item.prioridad === "baja" ? "selected" : "") + ">Baja</option></select></label><label><span>Próxima acción</span><input type='datetime-local' data-manage-field='proxima_accion'></label></div>"
            + "<div class='mi-cartera-promise' data-promise-fields hidden><label><span>Fecha prometida</span><input type='date' data-manage-field='fecha_compromiso'></label><label><span>Monto prometido</span><input type='number' min='1' step='1' data-manage-field='monto_compromiso' placeholder='Gs.'></label><p><i class='fa-solid fa-circle-info'></i> Telar confirmará el pago únicamente contra pagos reales registrados.</p></div>"
            + "<label class='mi-cartera-notes'><span>Nota breve</span><textarea data-manage-field='nota' maxlength='1000' placeholder='Ej.: atiende, solicita llamada el lunes por la mañana'></textarea></label>"
            + "<div class='mi-cartera-manage__actions'>" + takeChief + "<button type='button' class='mi-cartera-call mi-cartera-call--secondary' data-cartera-action='call-selected' data-assignment='" + item.id_asignacion + "' data-client='" + item.cod_cliente + "'><i class='fa-solid fa-phone'></i> Llamar al número elegido</button><button type='button' class='mi-cartera-btn mi-cartera-btn--primary' data-cartera-action='save-management' data-assignment='" + item.id_asignacion + "'><i class='fa-solid fa-check'></i><span>Guardar resultado y próxima acción</span></button></div><div class='mi-cartera-inline-message' data-inline-message></div></section>"
            + "<aside class='mi-cartera-detail'><div><h3>Cuentas incluidas</h3><div class='mi-cartera-sales'>" + (detail ? (sales || "<p>Sin cuentas pendientes.</p>") : "<p>Cargando detalle…</p>") + "</div></div><div><h3>Últimas gestiones</h3><div class='mi-cartera-history'>" + (detail ? (history || "<p>Todavía no hay gestiones.</p>") : "<p>Cargando historial…</p>") + "</div></div></aside>"
            + "</div></div></td></tr>";
    }

    function renderRows() {
        var body = state.root.querySelector("#miCarteraRows");
        var items = state.list ? state.list.items || [] : [];
        if (!items.length) {
            body.innerHTML = "<tr><td colspan='6'><div class='mi-cartera-empty'><i class='fa-solid fa-inbox'></i><strong>No hay pacientes en esta vista</strong><p>Revise los filtros o, si es el primer uso, previsualice y confirme el reparto de cartera.</p></div></td></tr>";
            return;
        }
        body.innerHTML = items.map(rowHtml).join("");
    }

    function renderPager() {
        var pager = state.root.querySelector("#miCarteraPager");
        var list = state.list || {};
        var first = Number(list.total || 0) > 0
            ? (Number(list.pagina || 1) - 1) * Number(list.por_pagina || 15) + 1 : 0;
        var html = "<span>Mostrando " + first + " a " + Math.min(Number(list.total || 0), Number(list.pagina || 1) * Number(list.por_pagina || 15)) + " de " + Number(list.total || 0) + "</span><div>";
        var page = Number(list.pagina || 1);
        var pages = Number(list.paginas || 1);
        var start = Math.max(1, page - 2);
        var end = Math.min(pages, page + 2);
        html += "<button type='button' data-cartera-page='" + Math.max(1, page - 1) + "' " + (page <= 1 ? "disabled" : "") + "><i class='fa-solid fa-chevron-left'></i></button>";
        for (; start <= end; start++) {
            html += "<button type='button' data-cartera-page='" + start + "' class='" + (start === page ? "is-active" : "") + "'>" + start + "</button>";
        }
        html += "<button type='button' data-cartera-page='" + Math.min(pages, page + 1) + "' " + (page >= pages ? "disabled" : "") + "><i class='fa-solid fa-chevron-right'></i></button>"
            + "<select id='miCarteraPageSize' aria-label='Filas por página'><option value='10'>10 por página</option><option value='15'>15 por página</option><option value='25'>25 por página</option><option value='50'>50 por página</option></select></div>";
        pager.innerHTML = html;
        pager.querySelector("#miCarteraPageSize").value = String(list.por_pagina || 15);
    }

    function listPayload() {
        return {
            vista: state.view,
            pagina: state.page,
            por_pagina: (state.root.querySelector("#miCarteraPageSize") || {}).value || (state.list ? state.list.por_pagina : 15) || 15,
            buscar: state.root.querySelector("#miCarteraSearch").value,
            estado: state.root.querySelector("#miCarteraStateFilter").value,
            cod_local: state.root.querySelector("#miCarteraLocalFilter").value,
            cod_responsable: state.root.querySelector("#miCarteraResponsibleFilter").value,
            prioridad: state.root.querySelector("#miCarteraPriorityFilter").value
        };
    }

    function loadList(showMessage) {
        if (state.loading || !state.context) { return Promise.resolve(); }
        if (!state.context.configuracion.completa) {
            state.list = { items: [], kpis: {}, total: 0, pagina: 1, paginas: 1, por_pagina: 15 };
            renderKpis({});
            renderRows();
            renderPager();
            return Promise.resolve();
        }
        state.loading = true;
        state.root.classList.add("is-loading");
        if (showMessage) { setMessage("Actualizando cartera…", "info"); }
        return request("listar", listPayload()).then(function (data) {
            state.list = data;
            state.page = data.pagina || 1;
            renderKpis(data.kpis || {});
            renderRows();
            renderPager();
            if (showMessage) { setMessage("Cartera actualizada con cuotas y pagos vigentes.", "success"); }
        }).catch(function (error) {
            setMessage(error.message, "error");
        }).then(function () {
            state.loading = false;
            state.root.classList.remove("is-loading");
        });
    }

    function loadContext() {
        state.loading = true;
        state.root.classList.add("is-loading");
        setMessage("Preparando su mesa de trabajo…", "info");
        return request("contexto").then(function (data) {
            state.context = data;
            applyContext();
            if (!data.configuracion.completa) { return null; }
            return request("sincronizar").catch(function () { return null; });
        }).then(function () {
            state.loading = false;
            state.root.classList.remove("is-loading");
            if (state.context && state.context.configuracion.completa) {
                setMessage("", "info");
                return loadList(false);
            }
            renderKpis({});
            state.list = { items: [], total: 0, pagina: 1, paginas: 1, por_pagina: 15 };
            renderRows();
            renderPager();
            return null;
        }).catch(function (error) {
            state.loading = false;
            state.root.classList.remove("is-loading");
            setMessage(error.message, "error");
        });
    }

    function expandRow(id) {
        id = Number(id || 0);
        if (!id) { return; }
        if (state.expanded === id) {
            state.expanded = 0;
            renderRows();
            return;
        }
        state.expanded = id;
        renderRows();
        if (!state.details[id]) {
            request("detalle", { id_asignacion: id }).then(function (data) {
                state.details[id] = data;
                if (state.expanded === id) { renderRows(); }
            }).catch(function (error) { setMessage(error.message, "error"); });
        }
    }

    function inlineMessage(button, text, kind) {
        var workflow = button.closest(".mi-cartera-workflow");
        var element = workflow ? workflow.querySelector("[data-inline-message]") : null;
        if (!element) { setMessage(text, kind); return; }
        element.className = "mi-cartera-inline-message mi-cartera-inline-message--" + (kind || "info");
        element.textContent = text;
    }

    function startCall(button, selected) {
        var assignment = Number(button.getAttribute("data-assignment") || 0);
        var client = Number(button.getAttribute("data-client") || 0);
        var phone = button.getAttribute("data-phone") || "";
        var workflow;
        if (selected) {
            workflow = button.closest(".mi-cartera-workflow");
            phone = workflow ? workflow.querySelector("[data-manage-field='telefono']").value : "";
        }
        if (!phone) {
            inlineMessage(button, "Seleccione el teléfono que desea llamar.", "warning");
            return;
        }
        button.disabled = true;
        inlineMessage(button, "Enviando la llamada a su MicroSIP…", "info");
        phoneRequest("solicitar_llamada", {
            cod_cliente: client,
            telefono: phone,
            origen: "mi_cartera"
        }).then(function (data) {
            state.pendingCalls[assignment] = data.id_solicitud || 0;
            inlineMessage(button, data.mensaje || "Atienda su MicroSIP para continuar.", "success");
            button.disabled = false;
        }).catch(function (error) {
            inlineMessage(button, error.message + " Puede llamar directamente desde MicroSIP y registrar el resultado.", "error");
            button.disabled = false;
        });
    }

    function managementValue(workflow, field) {
        var element = workflow.querySelector("[data-manage-field='" + field + "']");
        return element ? element.value : "";
    }

    function saveManagement(button) {
        var workflow = button.closest(".mi-cartera-workflow");
        var assignment = Number(button.getAttribute("data-assignment") || 0);
        var payload = {
            id_asignacion: assignment,
            telefono: managementValue(workflow, "telefono"),
            resultado: managementValue(workflow, "resultado"),
            prioridad: managementValue(workflow, "prioridad"),
            proxima_accion: managementValue(workflow, "proxima_accion").replace("T", " "),
            nota: managementValue(workflow, "nota"),
            fecha_compromiso: managementValue(workflow, "fecha_compromiso"),
            monto_compromiso: managementValue(workflow, "monto_compromiso"),
            id_solicitud: state.pendingCalls[assignment] || 0
        };
        button.disabled = true;
        inlineMessage(button, "Guardando resultado y próxima acción…", "info");
        request("guardar_gestion", payload).then(function () {
            delete state.pendingCalls[assignment];
            delete state.details[assignment];
            inlineMessage(button, "Seguimiento guardado. El caso ya tiene continuidad.", "success");
            return loadList(false);
        }).catch(function (error) {
            inlineMessage(button, error.message, "error");
        }).then(function () { button.disabled = false; });
    }

    function userOptions(users, selected) {
        var html = "<option value=''>Seleccionar usuario activo de Telar</option>";
        users.forEach(function (user) {
            html += "<option value='" + user.cod_usuario + "' " + (Number(selected) === Number(user.cod_usuario) ? "selected" : "") + ">" + escapeHtml(user.nombre + (user.tipo ? " · " + user.tipo : "") + (user.local ? " · " + user.local : "")) + "</option>";
        });
        return html;
    }

    function localOptions(locals, selected) {
        var html = "<option value=''>Seleccionar clínica</option>";
        locals.forEach(function (local) {
            html += "<option value='" + local.cod_local + "' " + (Number(selected) === Number(local.cod_local) ? "selected" : "") + ">" + escapeHtml(local.nombre) + "</option>";
        });
        return html;
    }

    function openConfig() {
        setMessage("Cargando usuarios activos de Telar…", "info");
        request("configuracion").then(function (data) {
            state.configCatalog = data;
            state.pendingConfig = null;
            renderConfigModal();
            setMessage("", "info");
        }).catch(function (error) { setMessage(error.message, "error"); });
    }

    function configManagerRow(item, index, users, locals) {
        item = item || {};
        return "<div class='mi-cartera-config-row' data-config-manager-row><b>Clínica " + (index + 1) + "</b>"
            + "<select data-config-local>" + localOptions(locals, item.cod_local || 0) + "</select>"
            + "<select data-config-manager>" + userOptions(users, item.cod_usuario || 0) + "</select>"
            + "<button type='button' class='mi-cartera-config-remove' data-cartera-action='remove-config-manager' title='Quitar gestor' aria-label='Quitar gestor'><i class='fa-solid fa-trash-can'></i></button></div>";
    }

    function configCollectorRow(item, index, users) {
        var selected = item && typeof item === "object" ? item.cod_usuario : item;
        return "<div class='mi-cartera-config-collector' data-config-collector-row><b>Cobrador " + (index + 1) + "</b>"
            + "<select data-config-collector>" + userOptions(users, selected || 0) + "</select>"
            + "<button type='button' class='mi-cartera-config-remove' data-cartera-action='remove-config-collector' title='Quitar cobrador' aria-label='Quitar cobrador'><i class='fa-solid fa-trash-can'></i></button></div>";
    }

    function renumberConfigRows() {
        var layer = state.root.querySelector("#miCarteraModalLayer");
        Array.prototype.forEach.call(layer.querySelectorAll("[data-config-manager-row]"), function (row, index) {
            row.querySelector("b").textContent = "Clínica " + (index + 1);
        });
        Array.prototype.forEach.call(layer.querySelectorAll("[data-config-collector-row]"), function (row, index) {
            row.querySelector("b").textContent = "Cobrador " + (index + 1);
        });
    }

    function renderConfigModal(draft) {
        var layer = state.root.querySelector("#miCarteraModalLayer");
        var data = state.configCatalog;
        var config = data.configuracion || {};
        var users = data.usuarios || [];
        var locals = data.locales || [];
        var managerBySlot = draft ? draft.gestores || [] : config.gestores || [];
        var collectors = draft ? draft.cobradores || [] : config.cobradores || [];
        var chief = draft ? draft.cod_jefe : config.cod_jefe || 0;
        var days = draft ? draft.dias_escalamiento : config.dias_escalamiento || 90;
        var htmlManagers = "";
        var htmlCollectors = "";
        var i;
        if (!managerBySlot.length) { managerBySlot = [{}]; }
        if (!collectors.length) { collectors = [0]; }
        for (i = 0; i < managerBySlot.length; i++) {
            htmlManagers += configManagerRow(managerBySlot[i], i, users, locals);
        }
        for (i = 0; i < collectors.length; i++) {
            htmlCollectors += configCollectorRow(collectors[i], i, users);
        }
        layer.innerHTML = "<section class='mi-cartera-modal mi-cartera-config-modal' role='dialog' aria-modal='true' aria-labelledby='miCarteraConfigTitle'>"
            + "<header><div><small>CONFIGURACIÓN PROTEGIDA</small><h2 id='miCarteraConfigTitle'>Equipo de Mi cartera</h2><p>Solo Carlos Faraone puede cambiar estos responsables. Los selectores buscan entre usuarios activos de Telar.</p></div><button type='button' data-cartera-action='close-modal' aria-label='Cerrar'><i class='fa-solid fa-xmark'></i></button></header>"
            + "<div class='mi-cartera-modal__body'><section class='mi-cartera-config-rule'><div><span><i class='fa-solid fa-calendar-days'></i></span><label><strong>Paso a Cobranza central</strong><small>Antes de este plazo el paciente permanece con su clínica. Al cumplirlo pasa al equipo central.</small></label></div><label class='mi-cartera-config-days'><input type='number' min='30' max='365' step='1' value='" + escapeHtml(days) + "' data-config-days><b>días de mora</b></label></section>"
            + "<section class='mi-cartera-config-chief'><span><i class='fa-solid fa-user-shield'></i></span><label><strong>Jefe de Cobranza</strong><small>Supervisa y recibe promesas incumplidas, revisiones, escalamientos manuales y casos que decida tomar.</small><select data-config-chief>" + userOptions(users, chief) + "</select></label></section>"
            + "<section><h3><b>1</b> Gestores por clínica</h3><p class='mi-cartera-help'>Cada clínica puede tener un único gestor. Agregue solamente las clínicas que participarán.</p><div class='mi-cartera-config-grid' data-config-managers>" + htmlManagers + "</div><button type='button' class='mi-cartera-config-add' data-cartera-action='add-config-manager'><i class='fa-solid fa-plus'></i> Agregar clínica y gestor</button></section>"
            + "<section><h3><b>2</b> Cobradores centrales</h3><p class='mi-cartera-help'>Los casos que cumplen el plazo se equilibran entre todos los cobradores configurados.</p><div class='mi-cartera-config-collectors' data-config-collectors>" + htmlCollectors + "</div><button type='button' class='mi-cartera-config-add' data-cartera-action='add-config-collector'><i class='fa-solid fa-plus'></i> Agregar cobrador central</button></section>"
            + "<div class='mi-cartera-modal-message' data-modal-message></div></div>"
            + "<footer><button type='button' class='mi-cartera-btn mi-cartera-btn--ghost' data-cartera-action='close-modal'><span>Cancelar</span></button><button type='button' class='mi-cartera-btn mi-cartera-btn--primary' data-cartera-action='save-config'><i class='fa-solid fa-eye'></i><span>Revisar cambios</span></button></footer></section>";
        layer.hidden = false;
    }

    function addConfigManager() {
        var container = state.root.querySelector("[data-config-managers]");
        var data = state.configCatalog || {};
        if (!container) { return; }
        container.insertAdjacentHTML(
            "beforeend",
            configManagerRow({}, container.querySelectorAll("[data-config-manager-row]").length, data.usuarios || [], data.locales || [])
        );
        renumberConfigRows();
    }

    function removeConfigManager(button) {
        var rows = state.root.querySelectorAll("[data-config-manager-row]");
        if (rows.length <= 1) {
            modalMessage("Debe quedar al menos una clínica con su gestor.", "error");
            return;
        }
        button.closest("[data-config-manager-row]").remove();
        renumberConfigRows();
        modalMessage("", "info");
    }

    function addConfigCollector() {
        var container = state.root.querySelector("[data-config-collectors]");
        var data = state.configCatalog || {};
        if (!container) { return; }
        container.insertAdjacentHTML(
            "beforeend",
            configCollectorRow({}, container.querySelectorAll("[data-config-collector-row]").length, data.usuarios || [])
        );
        renumberConfigRows();
    }

    function removeConfigCollector(button) {
        var rows = state.root.querySelectorAll("[data-config-collector-row]");
        if (rows.length <= 1) {
            modalMessage("Debe quedar al menos un cobrador central.", "error");
            return;
        }
        button.closest("[data-config-collector-row]").remove();
        renumberConfigRows();
        modalMessage("", "info");
    }

    function modalMessage(text, kind) {
        var element = state.root.querySelector("[data-modal-message]");
        if (!element) { return; }
        element.className = "mi-cartera-modal-message mi-cartera-modal-message--" + (kind || "info");
        element.textContent = text;
    }

    function configDraft() {
        var layer = state.root.querySelector("#miCarteraModalLayer");
        var chief = layer.querySelector("[data-config-chief]").value;
        var days = layer.querySelector("[data-config-days]").value;
        var localFields = layer.querySelectorAll("[data-config-local]");
        var managerFields = layer.querySelectorAll("[data-config-manager]");
        var collectorFields = layer.querySelectorAll("[data-config-collector]");
        var managers = [];
        var collectors = [];
        var i;
        for (i = 0; i < localFields.length; i++) {
            managers.push({ cod_local: localFields[i].value, cod_usuario: managerFields[i].value });
        }
        for (i = 0; i < collectorFields.length; i++) { collectors.push(collectorFields[i].value); }
        return { cod_jefe: chief, dias_escalamiento: days, gestores: managers, cobradores: collectors };
    }

    function configPayload(draft, confirm) {
        return {
            cod_jefe: draft.cod_jefe,
            dias_escalamiento: draft.dias_escalamiento,
            gestores: JSON.stringify(draft.gestores || []),
            cobradores: JSON.stringify(draft.cobradores || []),
            firma_impacto: draft.firma_impacto || "",
            confirmar: confirm ? "1" : "0"
        };
    }

    function renderConfigPreview(data) {
        var layer = state.root.querySelector("#miCarteraModalLayer");
        var proposal = data.propuesta || {};
        var impact = data.impacto || {};
        layer.innerHTML = "<section class='mi-cartera-modal mi-cartera-config-preview' role='dialog' aria-modal='true'><header><div><small>VISTA PREVIA · SIN CAMBIOS TODAVÍA</small><h2>Confirmar equipo y reglas</h2><p>Revise el impacto sobre las carteras existentes antes de guardar.</p></div><button type='button' data-cartera-action='close-modal' aria-label='Cerrar'><i class='fa-solid fa-xmark'></i></button></header>"
            + "<div class='mi-cartera-modal__body'><div class='mi-cartera-config-preview__summary'><article><small>Paso a central</small><strong>" + Number(proposal.dias_escalamiento || 90) + " días</strong></article><article><small>Gestores locales</small><strong>" + Number(proposal.cantidad_gestores || 0) + "</strong></article><article><small>Cobradores centrales</small><strong>" + Number(proposal.cantidad_cobradores || 0) + "</strong></article><article class='is-warning'><small>Reasignaciones</small><strong>" + Number(impact.reasignaciones || 0) + "</strong></article></div>"
            + "<section class='mi-cartera-config-impact'><h3>Cómo quedarían las carteras activas</h3><div><span><b>" + Number(impact.gestores_locales || 0) + "</b> en clínicas</span><span><b>" + Number(impact.cobranza_central || 0) + "</b> en Cobranza central</span><span><b>" + Number(impact.jefe_cobranza || 0) + "</b> casos especiales del jefe</span><span><b>" + Number(impact.sin_asignar || 0) + "</b> sin asignar</span></div><p><i class='fa-solid fa-shield-halved'></i> Las gestiones, promesas y pagos no se eliminan. Sólo cambiará el responsable de los casos indicados.</p></section>"
            + "<div class='mi-cartera-modal-message' data-modal-message></div></div><footer><button type='button' class='mi-cartera-btn mi-cartera-btn--ghost' data-cartera-action='back-config'><i class='fa-solid fa-arrow-left'></i><span>Volver a editar</span></button><button type='button' class='mi-cartera-btn mi-cartera-btn--primary' data-cartera-action='confirm-config'><i class='fa-solid fa-check-double'></i><span>Confirmar cambios</span></button></footer></section>";
    }

    function saveConfig(button) {
        var draft = configDraft();
        state.pendingConfig = draft;
        button.disabled = true;
        modalMessage("Calculando el impacto sin modificar la cartera…", "info");
        request("previsualizar_configuracion", configPayload(draft, false), 90000).then(function (data) {
            state.pendingConfig.firma_impacto = data.firma_impacto || "";
            renderConfigPreview(data);
        }).catch(function (error) {
            modalMessage(error.message, "error");
            button.disabled = false;
        });
    }

    function confirmConfig(button) {
        if (!state.pendingConfig) { return; }
        button.disabled = true;
        modalMessage("Guardando el equipo y las reasignaciones confirmadas…", "info");
        request("guardar_configuracion", configPayload(state.pendingConfig, true), 120000).then(function (data) {
            var impact = data.impacto || {};
            modalMessage("Configuración guardada. " + Number(impact.reasignaciones || 0) + " caso(s) fueron reasignados.", "success");
            return request("contexto");
        }).then(function (data) {
            state.context = data;
            applyContext();
            state.pendingConfig = null;
            window.setTimeout(closeModal, 700);
            return loadList(false);
        }).catch(function (error) {
            modalMessage(error.message, "error");
            button.disabled = false;
        });
    }

    function backToConfig() {
        if (!state.pendingConfig) { return; }
        renderConfigModal(state.pendingConfig);
    }

    function takeChiefCase(button) {
        var assignment = Number(button.getAttribute("data-assignment") || 0);
        if (!assignment) { return; }
        button.disabled = true;
        inlineMessage(button, "Asignando el caso especial al jefe…", "info");
        request("tomar_caso_jefe", { id_asignacion: assignment }).then(function () {
            delete state.details[assignment];
            inlineMessage(button, "El caso ya está en su cartera especial.", "success");
            return loadList(false);
        }).catch(function (error) {
            inlineMessage(button, error.message, "error");
            button.disabled = false;
        });
    }

    function previewDistribution() {
        var layer = state.root.querySelector("#miCarteraModalLayer");
        layer.innerHTML = "<section class='mi-cartera-modal mi-cartera-distribution-modal'><header><div><small>REPARTO INICIAL</small><h2>Preparando vista previa</h2></div><button type='button' data-cartera-action='close-modal'><i class='fa-solid fa-xmark'></i></button></header><div class='mi-cartera-modal__loading'><i class='fa-solid fa-spinner fa-spin'></i><p>Calculando cada paciente desde sus cuotas vigentes…</p></div></section>";
        layer.hidden = false;
        request("previsualizar_asignacion", {}, 90000).then(function (data) {
            var responsibleRows = "";
            (data.por_responsable || []).forEach(function (item) {
                responsibleRows += "<li><span>" + escapeHtml(item.nombre) + "</span><strong>" + Number(item.total) + " pacientes</strong></li>";
            });
            layer.innerHTML = "<section class='mi-cartera-modal mi-cartera-distribution-modal' role='dialog' aria-modal='true'><header><div><small>VISTA PREVIA · SIN CAMBIOS TODAVÍA</small><h2>Confirmar reparto de cartera</h2><p>El saldo se calculó desde cuotas y pagos reales. Confirme únicamente después de revisar el resumen.</p></div><button type='button' data-cartera-action='close-modal'><i class='fa-solid fa-xmark'></i></button></header><div class='mi-cartera-modal__body'><div class='mi-cartera-distribution-summary'><article><small>Total nuevo</small><strong>" + Number(data.total || 0) + "</strong></article><article><small>Gestores locales</small><strong>" + Number(data.gestores_locales || 0) + "</strong></article><article><small>Cobranza central</small><strong>" + Number(data.cobranza_central || 0) + "</strong></article><article class='is-warning'><small>Sin asignar</small><strong>" + Number(data.sin_asignar || 0) + "</strong></article></div><div class='mi-cartera-distribution-rules'><h3>Reglas aplicadas</h3><p><i class='fa-solid fa-building'></i> El local de origen es el de la obligación vencida más antigua.</p><p><i class='fa-solid fa-arrow-trend-up'></i> Desde " + Number(data.dias_escalamiento || 90) + " días de mora el caso pasa al equipo central.</p><p><i class='fa-solid fa-scale-balanced'></i> Los casos centrales se equilibran entre " + Number(data.cantidad_cobradores || 0) + " cobrador(es) configurado(s).</p><p><i class='fa-solid fa-user-shield'></i> El jefe recibe excepciones y puede tomar casos especiales manualmente.</p></div><ul class='mi-cartera-distribution-list'>" + (responsibleRows || "<li><span>No hay nuevos casos por repartir.</span></li>") + "</ul><div class='mi-cartera-modal-message' data-modal-message></div></div><footer><button type='button' class='mi-cartera-btn mi-cartera-btn--ghost' data-cartera-action='close-modal'><span>Volver sin cambios</span></button><button type='button' class='mi-cartera-btn mi-cartera-btn--primary' data-cartera-action='confirm-distribution' " + (!data.total ? "disabled" : "") + "><i class='fa-solid fa-check-double'></i><span>Confirmar reparto</span></button></footer></section>";
        }).catch(function (error) {
            layer.hidden = true;
            setMessage(error.message, "error");
        });
    }

    function confirmDistribution(button) {
        button.disabled = true;
        modalMessage("Asignando pacientes y registrando la trazabilidad…", "info");
        request("confirmar_asignacion", {}, 120000).then(function (data) {
            modalMessage("Reparto confirmado: " + Number(data.asignados || 0) + " pacientes. " + Number(data.sin_asignar || 0) + " quedaron visibles como excepción.", "success");
            return loadList(false);
        }).then(function () { window.setTimeout(closeModal, 900); }).catch(function (error) {
            modalMessage(error.message, "error");
            button.disabled = false;
        });
    }

    function closeModal() {
        var layer = state.root ? state.root.querySelector("#miCarteraModalLayer") : null;
        if (!layer) { return; }
        layer.hidden = true;
        layer.innerHTML = "";
        state.pendingConfig = null;
    }

    function clearFilters() {
        state.root.querySelector("#miCarteraSearch").value = "";
        state.root.querySelector("#miCarteraStateFilter").value = "";
        state.root.querySelector("#miCarteraLocalFilter").value = "0";
        state.root.querySelector("#miCarteraResponsibleFilter").value = "0";
        state.root.querySelector("#miCarteraPriorityFilter").value = "";
        state.page = 1;
        loadList(false);
    }

    function bindEvents() {
        state.root.addEventListener("click", function (event) {
            var actionButton = event.target.closest("[data-cartera-action]");
            var viewButton = event.target.closest("[data-cartera-view]");
            var pageButton = event.target.closest("[data-cartera-page]");
            var action;
            if (viewButton) {
                state.view = viewButton.getAttribute("data-cartera-view") || "mi_cartera";
                state.page = 1;
                state.expanded = 0;
                Array.prototype.forEach.call(state.root.querySelectorAll("[data-cartera-view]"), function (button) {
                    button.classList.toggle("is-active", button === viewButton);
                });
                loadList(false);
                return;
            }
            if (pageButton && !pageButton.disabled) {
                state.page = Number(pageButton.getAttribute("data-cartera-page") || 1);
                loadList(false);
                return;
            }
            if (!actionButton) { return; }
            action = actionButton.getAttribute("data-cartera-action");
            if (action === "close") { window.cerrarMiCartera(); }
            else if (action === "minimize") { window.minimizarMiCartera(); }
            else if (action === "refresh") { loadContext(); }
            else if (action === "expand") { expandRow(actionButton.getAttribute("data-assignment")); }
            else if (action === "call") { startCall(actionButton, false); }
            else if (action === "call-selected") { startCall(actionButton, true); }
            else if (action === "save-management") { saveManagement(actionButton); }
            else if (action === "open-config") { openConfig(); }
            else if (action === "close-modal") { closeModal(); }
            else if (action === "save-config") { saveConfig(actionButton); }
            else if (action === "add-config-manager") { addConfigManager(); }
            else if (action === "remove-config-manager") { removeConfigManager(actionButton); }
            else if (action === "add-config-collector") { addConfigCollector(); }
            else if (action === "remove-config-collector") { removeConfigCollector(actionButton); }
            else if (action === "back-config") { backToConfig(); }
            else if (action === "confirm-config") { confirmConfig(actionButton); }
            else if (action === "take-chief-case") { takeChiefCase(actionButton); }
            else if (action === "preview-distribution") { previewDistribution(); }
            else if (action === "confirm-distribution") { confirmDistribution(actionButton); }
            else if (action === "clear-filters") { clearFilters(); }
        });
        state.root.addEventListener("change", function (event) {
            var field = event.target;
            var workflow;
            if (field.matches("[data-manage-field='resultado']")) {
                workflow = field.closest(".mi-cartera-workflow");
                workflow.querySelector("[data-promise-fields]").hidden = field.value !== "promesa_pago";
                return;
            }
            if (field.matches("#miCarteraStateFilter,#miCarteraLocalFilter,#miCarteraResponsibleFilter,#miCarteraPriorityFilter,#miCarteraPageSize")) {
                state.page = 1;
                loadList(false);
            }
        });
        state.root.addEventListener("input", function (event) {
            if (event.target.id !== "miCarteraSearch") { return; }
            window.clearTimeout(state.searchTimer);
            state.searchTimer = window.setTimeout(function () {
                state.page = 1;
                loadList(false);
            }, 350);
        });
    }

    window.abrirMiCartera = function () {
        var container = document.getElementById("divMiCartera");
        var marker;
        if (!container || !mount()) { return; }
        container.style.display = "";
        container.setAttribute("aria-hidden", "false");
        marker = document.getElementById("divMinimizadoMiCartera");
        if (marker) { marker.style.display = "none"; }
        state.open = true;
        if (document.body) { document.body.classList.add("mi-cartera-open"); }
        loadContext();
    };

    window.cerrarMiCartera = function () {
        var container = document.getElementById("divMiCartera");
        if (container) {
            container.style.display = "none";
            container.setAttribute("aria-hidden", "true");
        }
        state.open = false;
        closeModal();
        if (document.body) { document.body.classList.remove("mi-cartera-open"); }
    };

    window.minimizarMiCartera = function () {
        var marker = document.getElementById("divMinimizadoMiCartera");
        window.cerrarMiCartera();
        if (marker) { marker.style.display = ""; }
    };

    window.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && state.open) {
            var layer = state.root ? state.root.querySelector("#miCarteraModalLayer") : null;
            if (layer && !layer.hidden) { closeModal(); } else { window.cerrarMiCartera(); }
        }
    });
})(window, document);

/*
 * Planificacion visual de especialistas - Sistema Telar / Clinident Salud.
 * Vista semanal y mensual agrupada por semanas.
 */
(function (window, document) {
    "use strict";

    var ENDPOINT = "/GoodVentaAsisCap/php_system/abmPlanificacionEspecialistas.php";
    var ROOT_ID = "telarPlanificacionEspecialistas";
    var COLORS = [
        "#07899a", "#7357d9", "#ee6b19", "#3aa61b", "#e9487d",
        "#2563eb", "#9c4dcc", "#c47b00", "#00876b", "#d34040"
    ];
    var BRANCH_COLORS = [
        "#07899a", "#7357d9", "#ee7a16", "#2563eb", "#3aa61b",
        "#c47b00", "#9c4dcc", "#00876b"
    ];
    var MONTHS = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    var SHORT_DAYS = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
    var LONG_DAYS = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];

    var state = {
        root: null,
        date: startOfDay(new Date()),
        view: "month",
        scope: "branch",
        data: null,
        loading: false,
        savingAssignment: false,
        selectedProfessional: "",
        drag: null,
        filters: {
            specialty: "",
            professional: "",
            room: "",
            status: "",
            threads: "selected"
        },
        open: false,
        initialized: false,
        resizeTimer: null,
        requestedLocal: ""
    };

    function escapeHtml(value) {
        return String(value === null || typeof value === "undefined" ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, "&#096;");
    }

    function startOfDay(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    function cloneDate(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    function addDays(date, amount) {
        var result = cloneDate(date);
        result.setDate(result.getDate() + amount);
        return result;
    }

    function formatIso(date) {
        var month = String(date.getMonth() + 1);
        var day = String(date.getDate());
        if (month.length < 2) { month = "0" + month; }
        if (day.length < 2) { day = "0" + day; }
        return date.getFullYear() + "-" + month + "-" + day;
    }

    function parseIso(value) {
        var parts = String(value || "").split("-");
        if (parts.length !== 3) { return startOfDay(new Date()); }
        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    }

    function mondayOf(date) {
        var result = cloneDate(date);
        var day = result.getDay();
        result.setDate(result.getDate() - (day === 0 ? 6 : day - 1));
        return result;
    }

    function sundayOf(date) {
        return addDays(mondayOf(date), 6);
    }

    function monthRange(date) {
        var first = new Date(date.getFullYear(), date.getMonth(), 1);
        var last = new Date(date.getFullYear(), date.getMonth() + 1, 0);
        return { from: mondayOf(first), to: sundayOf(last) };
    }

    function currentRange() {
        if (state.view === "week") {
            return { from: mondayOf(state.date), to: sundayOf(state.date) };
        }
        return monthRange(state.date);
    }

    function datesBetween(from, to) {
        var dates = [];
        var cursor = cloneDate(from);
        while (cursor <= to) {
            dates.push(cloneDate(cursor));
            cursor = addDays(cursor, 1);
        }
        return dates;
    }

    function stableColor(id) {
        var text = String(id || "0");
        var hash = 0;
        var i;
        for (i = 0; i < text.length; i++) {
            hash = ((hash << 5) - hash) + text.charCodeAt(i);
            hash |= 0;
        }
        return COLORS[Math.abs(hash) % COLORS.length];
    }

    function stableBranchColor(id) {
        var text = String(id || "0");
        var hash = 0;
        var i;
        for (i = 0; i < text.length; i++) {
            hash = ((hash << 5) - hash) + text.charCodeAt(i);
            hash |= 0;
        }
        return BRANCH_COLORS[Math.abs(hash) % BRANCH_COLORS.length];
    }

    function initials(name) {
        var words = String(name || "?").trim().split(/\s+/);
        var value = words.length > 1
            ? words[0].charAt(0) + words[words.length - 1].charAt(0)
            : words[0].slice(0, 2);
        return value.toUpperCase();
    }

    window.TelarProfessionalIdentity = window.TelarProfessionalIdentity || {};
    window.TelarProfessionalIdentity.color = stableColor;
    window.TelarProfessionalIdentity.initials = initials;

    function avatarHtml(professional, className) {
        var avatar = professional && professional.avatar ? professional.avatar : "";
        var name = professional && (professional.nombre || professional.profesional)
            ? (professional.nombre || professional.profesional) : "Profesional";
        return '<span class="plan-avatar ' + escapeAttr(className || "") + '">'
            + (avatar
                ? '<img src="' + escapeAttr(avatar) + '" alt="">'
                : '<span>' + escapeHtml(initials(name)) + '</span>')
            + '</span>';
    }

    function rootTemplate() {
        return ''
            + '<div class="plan-app" role="application" aria-label="Planificación visual de especialistas">'
            + '  <header class="plan-topbar">'
            + '    <div class="plan-branch"><i class="fa-solid fa-building-columns" aria-hidden="true"></i>'
            + '      <label class="plan-sr-only" for="planBranch">Sucursal</label><select id="planBranch"></select></div>'
            + '    <div class="plan-topbar__actions">'
            + '      <div class="plan-popover-wrap"><button type="button" class="plan-icon-button" data-plan-action="alerts" aria-label="Alertas" title="Alertas"><i class="fa-regular fa-bell"></i><span class="plan-alert-dot" id="planAlertDot" hidden></span></button><div class="plan-popover plan-alerts" id="planAlerts" hidden></div></div>'
            + '      <div class="plan-popover-wrap"><button type="button" class="plan-icon-button" data-plan-action="settings" aria-label="Ajustes" title="Ajustes"><i class="fa-solid fa-gear"></i></button><div class="plan-popover plan-settings" id="planSettings" hidden></div></div>'
            + '      <button type="button" class="plan-icon-button" data-plan-action="help" aria-label="Ayuda" title="Ayuda"><i class="fa-regular fa-circle-question"></i></button>'
            + '      <span class="plan-user-chip" id="planUserChip"><span class="plan-user-chip__avatar">ST</span><span class="plan-user-chip__name">Sistema Telar</span></span>'
            + '      <button type="button" class="plan-icon-button plan-icon-button--close" data-plan-action="close" aria-label="Cerrar" title="Cerrar"><i class="fa-solid fa-xmark"></i></button>'
            + '    </div>'
            + '  </header>'
            + '  <section class="plan-heading">'
            + '    <div class="plan-heading__copy"><div class="plan-heading__title"><h1>Planificación visual de especialistas</h1><i class="fa-solid fa-circle-info" aria-hidden="true"></i></div><p>Organiza profesionales por fecha, sucursal y consultorio.</p></div>'
            + '    <div class="plan-controls">'
            + '      <div class="plan-view-switch" role="group" aria-label="Tipo de vista"><button type="button" data-plan-view="week">Semanal</button><button type="button" data-plan-view="month">Mensual</button></div>'
            + '      <button type="button" class="plan-date-button" data-plan-action="pick-date"><i class="fa-regular fa-calendar"></i><span id="planPeriodLabel">Mes</span><i class="fa-solid fa-chevron-down"></i></button><input type="date" id="planDatePicker" class="plan-date-picker" tabindex="-1" aria-hidden="true">'
            + '      <div class="plan-stepper"><button type="button" data-plan-action="previous" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button><button type="button" data-plan-action="next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button></div>'
            + '      <button type="button" class="plan-today-button" data-plan-action="today">Hoy</button>'
            + '    </div>'
            + '  </section>'
            + '  <div class="plan-notice" id="planNotice" hidden></div>'
            + '  <main class="plan-body" id="planBody">'
            + '    <aside class="plan-professionals" aria-label="Profesionales"><div class="plan-column-title"><div><strong>Profesionales</strong><span id="planProfessionalCount">0</span></div><button type="button" id="planAddProfessional" data-plan-action="add-professional" hidden><i class="fa-solid fa-user-plus"></i> Agregar doctor/a</button></div><div class="plan-professional-list" id="planProfessionalList"></div></aside>'
            + '    <section class="plan-calendar" id="planCalendar" aria-live="polite"></section>'
            + '    <svg class="plan-threads" id="planThreads" aria-hidden="true"></svg>'
            + '  </main>'
            + '  <footer class="plan-footer"><i class="fa-solid fa-circle-info"></i><span id="planFooterHint">Arrastra un doctor a una casilla libre para fijarlo. Las ocupaciones de Agenda se muestran con avatar.</span></footer>'
            + '  <div class="plan-live-region" id="planLiveRegion" aria-live="polite" aria-atomic="true"></div>'
            + '  <div class="plan-dialog-layer" id="planDialogLayer" hidden></div>'
            + '</div>';
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
            xhr.timeout = 45000;
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
                    error = new Error(data.mensaje || "No se pudo completar la operación.");
                    error.code = data.codigo || "";
                    error.data = data.datos || {};
                    reject(error);
                    return;
                }
                resolve(data.datos || {});
            };
            xhr.onerror = function () { reject(new Error("No se pudo comunicar con el servidor.")); };
            xhr.ontimeout = function () { reject(new Error("La operación tardó demasiado. Intente nuevamente.")); };
            xhr.send(formData);
        });
    }

    function notify(message, type) {
        var region;
        var toast;
        if (!state.root) { return; }
        region = state.root.querySelector("#planLiveRegion");
        if (!region) { return; }
        toast = document.createElement("div");
        toast.className = "plan-toast plan-toast--" + (type || "info");
        toast.setAttribute("role", type === "error" ? "alert" : "status");
        toast.innerHTML = '<i class="fa-solid '
            + (type === "success" ? "fa-circle-check" : (type === "error" ? "fa-triangle-exclamation" : "fa-circle-info"))
            + '"></i><span>' + escapeHtml(message) + '</span>';
        region.innerHTML = "";
        region.appendChild(toast);
        window.setTimeout(function () {
            if (toast.parentNode) { toast.parentNode.removeChild(toast); }
        }, 4800);
    }

    function ensureRoot() {
        var host = document.getElementById(ROOT_ID);
        if (!host) { return false; }
        if (!state.initialized) {
            host.innerHTML = rootTemplate();
            state.root = host;
            bindRootEvents();
            state.initialized = true;
        } else {
            state.root = host;
        }
        return true;
    }

    function selectedLocal() {
        var select = state.root ? state.root.querySelector("#planBranch") : null;
        if (state.requestedLocal) { return state.requestedLocal; }
        if (state.scope === "multi" && state.data && state.data.local_actual) {
            return state.data.local_actual.cod_local;
        }
        if (select && select.value) { return select.value; }
        if (state.data && state.data.local_actual) { return state.data.local_actual.cod_local; }
        if (typeof window.cod_localFKUSer !== "undefined") { return window.cod_localFKUSer; }
        return "";
    }

    function loadData(showLoader) {
        var range = currentRange();
        var calendar;
        if (!state.root || state.loading) { return; }
        state.loading = true;
        calendar = state.root.querySelector("#planCalendar");
        if (showLoader !== false && calendar) {
            calendar.innerHTML = '<div class="plan-loading"><img src="/GoodVentaAsisCap/iconos/telar-loader.svg?v=20260721-2" alt=""><strong>Organizando los hilos de la planificación…</strong></div>';
        }
        request("obtenerPlanificacion", {
            fecha_desde: formatIso(range.from),
            fecha_hasta: formatIso(range.to),
            cod_local: selectedLocal(),
            modo_multisucursal: state.scope === "multi" ? "1" : "0"
        }).then(function (data) {
            var profesionalSeleccionadoExiste = false;
            state.data = data;
            state.requestedLocal = "";
            state.loading = false;
            (data.profesionales || []).forEach(function (profesional) {
                if (String(profesional.cod_profesional) === String(state.selectedProfessional)) {
                    profesionalSeleccionadoExiste = true;
                }
            });
            if (!profesionalSeleccionadoExiste) {
                state.selectedProfessional = data.profesionales && data.profesionales.length
                    ? String(data.profesionales[0].cod_profesional) : "";
            }
            if (data.modo_multisucursal !== true && state.scope === "multi") {
                state.scope = "branch";
            }
            renderAll();
        }).catch(function (error) {
            state.loading = false;
            if (calendar) {
                calendar.innerHTML = '<div class="plan-empty plan-empty--error"><i class="fa-solid fa-triangle-exclamation"></i><strong>No se pudo abrir la planificación</strong><p>'
                    + escapeHtml(error.message) + '</p><button type="button" data-plan-action="retry">Reintentar</button></div>';
            }
            notify(error.message, "error");
        });
    }

    function renderAll() {
        var app;
        if (!state.data || !state.root) { return; }
        app = state.root.querySelector(".plan-app");
        if (app) {
            app.classList.toggle("is-multibranch", state.scope === "multi");
        }
        renderContext();
        renderProfessionals();
        renderCalendar();
        renderSettings();
        renderAlerts();
        renderPeriod();
        renderFooterHint();
        window.requestAnimationFrame(renderThreads);
    }

    function renderContext() {
        var data = state.data;
        var branch = state.root.querySelector("#planBranch");
        var userChip = state.root.querySelector("#planUserChip");
        var notice = state.root.querySelector("#planNotice");
        var html = "";
        if (state.scope === "multi") {
            branch.innerHTML = '<option value="">Todas las sucursales autorizadas</option>';
            branch.disabled = true;
        } else {
            (data.locales || []).forEach(function (local) {
                html += '<option value="' + escapeAttr(local.cod_local) + '"'
                    + (String(local.cod_local) === String(data.local_actual.cod_local) ? " selected" : "")
                    + '>' + escapeHtml(local.nombre) + '</option>';
            });
            branch.innerHTML = html;
            branch.disabled = (data.locales || []).length < 2;
        }
        if (userChip && data.contexto_usuario) {
            userChip.innerHTML = '<span class="plan-user-chip__avatar">'
                + escapeHtml(initials(data.contexto_usuario.nombre)) + '</span><span class="plan-user-chip__name">'
                + escapeHtml(data.contexto_usuario.nombre) + '</span>';
        }
        if (!data.estructura_instalada) {
            notice.hidden = false;
            notice.className = "plan-notice plan-notice--warning";
            notice.innerHTML = '<i class="fa-solid fa-database"></i><span><strong>Vista de consulta.</strong> La migración controlada del módulo aún no está aplicada; las asignaciones nuevas permanecen deshabilitadas.</span>';
        } else if (state.scope === "branch"
            && data.contexto_usuario
            && data.contexto_usuario.permisos
            && data.contexto_usuario.permisos.gestionar
            && data.contexto_usuario.permisos.todas_sucursales
            && !data.vinculos_instalados) {
            notice.hidden = false;
            notice.className = "plan-notice plan-notice--warning";
            notice.innerHTML = '<i class="fa-solid fa-user-plus"></i><span><strong>Agregar doctor/a todavía no está habilitado.</strong> Falta aplicar la migración controlada del vínculo entre profesionales y sucursales.</span>';
        } else if (data.advertencias && data.advertencias.consultorios_con_doctor_estatico > 0) {
            notice.hidden = false;
            notice.className = "plan-notice";
            notice.innerHTML = '<i class="fa-regular fa-circle-info"></i><span>Hay '
                + escapeHtml(data.advertencias.consultorios_con_doctor_estatico)
                + ' consultorio(s) con doctor fijo legacy. No se convierten en asignaciones por fecha para evitar interpretaciones incorrectas.</span>';
        } else {
            notice.hidden = true;
            notice.innerHTML = "";
        }
    }

    function renderFooterHint() {
        var hint = state.root ? state.root.querySelector("#planFooterHint") : null;
        var remoteDates = {};
        if (!hint) { return; }
        if (state.scope === "multi") {
            hint.textContent = "Vista mensual de consulta. Selecciona una profesional para recorrer sus sucursales y horarios.";
        } else {
            ((state.data && state.data.compromisos_otras_sucursales) || [])
                .forEach(function (commitment) {
                    if (String(commitment.cod_profesional)
                        === String(state.selectedProfessional)) {
                        remoteDates[commitment.fecha] = true;
                    }
                });
            hint.textContent = Object.keys(remoteDates).length
                ? "Los avisos naranja indican días u horarios comprometidos en otra sucursal para la profesional seleccionada."
                : "Arrastra un doctor a una casilla libre para fijarlo. Agenda muestra quién ocupa las demás casillas.";
        }
    }

    function filteredProfessionals() {
        var list = (state.data && state.data.profesionales) ? state.data.profesionales.slice() : [];
        return list.filter(function (professional) {
            if (state.filters.professional
                && String(professional.cod_profesional) !== String(state.filters.professional)) {
                return false;
            }
            if (state.filters.specialty
                && String(professional.especialidad || "") !== String(state.filters.specialty)) {
                return false;
            }
            return true;
        });
    }

    function agendaVisualAssignments() {
        var data = state.data || {};
        var rawAssignments = data.asignaciones || [];
        var occupiedSlots = {};
        var local = data.local_actual || {};
        var items = [];
        rawAssignments.forEach(function (assignment) {
            occupiedSlots[assignment.fecha + "|" + String(assignment.id_consultorio)] = true;
        });
        (data.ocupaciones_agenda || []).forEach(function (occupancy) {
            var professionals = occupancy.profesionales || [];
            var slotKey = occupancy.fecha + "|" + String(occupancy.id_consultorio);
            var professional;
            var room;
            if (professionals.length !== 1 || occupiedSlots[slotKey]) { return; }
            professional = professionals[0];
            room = roomById(occupancy.id_consultorio) || {};
            items.push({
                id_asignacion: 0,
                id_regla: null,
                clave: "agenda-" + occupancy.fecha + "-"
                    + occupancy.id_consultorio + "-" + professional.cod_profesional,
                cod_profesional: professional.cod_profesional,
                cod_local: local.cod_local || selectedLocal(),
                nombre_local: local.nombre || "",
                id_consultorio: occupancy.id_consultorio,
                consultorio: room.nombre || "Consultorio",
                fecha: occupancy.fecha,
                id_horario: null,
                hora_entrada: professional.hora_desde || occupancy.hora_desde || null,
                hora_salida: professional.hora_hasta || occupancy.hora_hasta || null,
                estado: "agenda",
                motivo: "Ocupación identificada en Agenda.",
                version: 0,
                profesional: professional.nombre,
                avatar: professional.avatar || "",
                especialidad: "",
                origen: "agenda",
                solo_lectura: true,
                es_recurrente: false,
                cantidad_agenda: occupancy.cantidad_registros || 0
            });
            occupiedSlots[slotKey] = true;
        });
        return items;
    }

    function visualAssignments() {
        return ((state.data && state.data.asignaciones) || [])
            .concat(agendaVisualAssignments());
    }

    function filteredAssignments() {
        var allowedProfessionals = {};
        filteredProfessionals().forEach(function (professional) {
            allowedProfessionals[String(professional.cod_profesional)] = true;
        });
        return visualAssignments().filter(function (assignment) {
            if (!allowedProfessionals[String(assignment.cod_profesional)]) { return false; }
            if (state.filters.room
                && String(assignment.id_consultorio) !== String(state.filters.room)) { return false; }
            if (state.filters.status && assignment.estado !== state.filters.status) { return false; }
            return true;
        });
    }

    function assignmentCountFor(professionalId) {
        var dates = {};
        filteredAssignments().forEach(function (assignment) {
            if (String(assignment.cod_profesional) === String(professionalId)) {
                dates[assignment.fecha] = true;
            }
        });
        return Object.keys(dates).length;
    }

    function assignmentStatsFor(professionalId) {
        var dates = {};
        var branches = {};
        filteredAssignments().forEach(function (assignment) {
            if (String(assignment.cod_profesional) !== String(professionalId)) { return; }
            dates[assignment.fecha] = true;
            branches[String(assignment.cod_local || "")] = true;
        });
        delete branches[""];
        return {
            days: Object.keys(dates).length,
            branches: Object.keys(branches).length
        };
    }

    function renderProfessionals() {
        var container = state.root.querySelector("#planProfessionalList");
        var count = state.root.querySelector("#planProfessionalCount");
        var addButton = state.root.querySelector("#planAddProfessional");
        var professionals = filteredProfessionals();
        var canAssign = canCreate();
        var html = "";
        if (addButton) {
            addButton.hidden = !(state.scope === "branch"
                && canManageMultibranch()
                && state.data.vinculos_instalados);
        }
        professionals.forEach(function (professional) {
            var id = String(professional.cod_profesional);
            var color = stableColor(id);
            var stats = assignmentStatsFor(id);
            var days = stats.days;
            var isExternalLink = professional.origen_listado === "vinculo";
            var canDrag = canAssign && (!isExternalLink || canManageMultibranch());
            var summary = state.scope === "multi"
                ? stats.branches + (stats.branches === 1 ? " sucursal" : " sucursales")
                    + " · " + days + (days === 1 ? " día asignado" : " días asignados")
                : days + (days === 1 ? " día asignado" : " días asignados");
            html += '<article class="plan-professional-card'
                + (id === String(state.selectedProfessional) ? " is-selected" : "")
                + (isExternalLink ? " is-external-link" : "")
                + '" data-plan-professional="' + escapeAttr(id) + '"'
                + (canDrag ? ' draggable="true"' : "")
                + ' style="--professional-color:' + escapeAttr(color) + '">'
                + avatarHtml(professional)
                + '<span class="plan-professional-card__copy"><strong><i></i>'
                 + escapeHtml(professional.nombre) + '</strong><span>'
                 + escapeHtml(professional.especialidad || "Especialidad no indicada")
                + '</span>'
                + (isExternalLink
                    ? '<small class="plan-professional-card__base"><i class="fa-solid fa-building-columns"></i> Base: '
                        + escapeHtml(professional.nombre_local_base || "Otra sucursal") + '</small>' : "")
                + '<small>' + escapeHtml(summary) + '</small></span>'
                + (isExternalLink && canManageMultibranch()
                    ? '<button type="button" class="plan-professional-card__remove" data-plan-action="remove-professional" data-plan-remove-professional="'
                        + escapeAttr(id) + '" aria-label="Quitar del listado" title="Quitar del listado"><i class="fa-solid fa-xmark"></i></button>' : "")
                + '<span class="plan-professional-card__node" aria-hidden="true"></span>'
                + '</article>';
        });
        container.innerHTML = html || '<div class="plan-empty plan-empty--compact"><strong>Sin profesionales</strong><p>No hay doctores activos para los filtros y la sucursal seleccionada.</p></div>';
        count.textContent = String(professionals.length);
    }

    function professionalById(id) {
        var result = null;
        ((state.data && state.data.profesionales) || []).some(function (professional) {
            if (String(professional.cod_profesional) === String(id)) {
                result = professional;
                return true;
            }
            return false;
        });
        return result;
    }

    function roomById(id) {
        var result = null;
        ((state.data && state.data.consultorios) || []).some(function (room) {
            if (String(room.id_consultorio) === String(id)) {
                result = room;
                return true;
            }
            return false;
        });
        return result;
    }

    function holidayByDate(date) {
        var result = null;
        ((state.data && state.data.feriados) || []).some(function (holiday) {
            if (holiday.fecha === date) {
                result = holiday;
                return true;
            }
            return false;
        });
        return result;
    }

    function assignmentsFor(date, roomId) {
        return visualAssignments().filter(function (assignment) {
            return assignment.fecha === date
                && String(assignment.id_consultorio) === String(roomId);
        });
    }

    function agendaOccupancyFor(date, roomId) {
        var result = null;
        ((state.data && state.data.ocupaciones_agenda) || []).some(function (occupancy) {
            if (occupancy.fecha === date
                && String(occupancy.id_consultorio) === String(roomId)) {
                result = occupancy;
                return true;
            }
            return false;
        });
        return result;
    }

    function agendaOccupancyBlocksSlot(occupancy) {
        return !!(occupancy && (occupancy.profesionales || []).length);
    }

    function slotIsOccupied(date, roomId) {
        return assignmentsFor(date, roomId).length > 0
            || agendaOccupancyBlocksSlot(agendaOccupancyFor(date, roomId));
    }

    function shortTimeRange(from, to) {
        if (!from) { return ""; }
        return String(from).slice(0, 5)
            + (to ? "–" + String(to).slice(0, 5) : "");
    }

    function agendaOccupancyLabel(occupancy) {
        var professionals = occupancy && occupancy.profesionales
            ? occupancy.profesionales : [];
        var names = professionals.map(function (professional) {
            return professional.nombre;
        });
        if (professionals.length > 1) {
            return "Conflicto: varios doctores en Agenda";
        }
        return names.join(" y ") || "Actividad de Agenda vinculada al consultorio";
    }

    function agendaMatchesAssignments(occupancy, assignments) {
        var professionals = occupancy && occupancy.profesionales
            ? occupancy.profesionales : [];
        var assignmentId;
        if (!assignments.length || professionals.length > 1) {
            return false;
        }
        assignmentId = String(assignments[0].cod_profesional);
        if (!assignments.every(function (assignment) {
            return String(assignment.cod_profesional) === assignmentId;
        })) {
            return false;
        }
        return !professionals.length
            || String(professionals[0].cod_profesional) === assignmentId;
    }

    function agendaCountLabel(occupancy) {
        var count = parseInt(occupancy && occupancy.cantidad_registros, 10) || 0;
        return count + (count === 1 ? " turno" : " turnos");
    }

    function agendaBadge(occupancy) {
        if (!occupancy) { return ""; }
        return '<span class="plan-assignment__agenda" title="La misma ocupación registra '
            + escapeAttr(agendaCountLabel(occupancy)) + ' en Agenda">'
            + '<i class="fa-regular fa-calendar-check" aria-hidden="true"></i><span>'
            + escapeHtml(occupancy.cantidad_registros || 0) + '</span></span>';
    }

    function remoteCommitmentsFor(date, professionalId) {
        return ((state.data && state.data.compromisos_otras_sucursales) || [])
            .filter(function (commitment) {
                return commitment.fecha === date
                    && String(commitment.cod_profesional) === String(professionalId);
            });
    }

    function remoteCommitmentsForSelected(date) {
        return state.selectedProfessional
            ? remoteCommitmentsFor(date, state.selectedProfessional) : [];
    }

    function remoteCommitmentBlocksDay(commitments) {
        return commitments.some(function (commitment) {
            return commitment.bloquea_dia === true;
        });
    }

    function professionalShortName(professional) {
        var name = professional && (professional.nombre || professional.profesional)
            ? (professional.nombre || professional.profesional) : "Profesional";
        var first = String(name).trim().split(/\s+/)[0] || "Profesional";
        return first === first.toUpperCase()
            ? first.charAt(0).toUpperCase() + first.slice(1).toLowerCase()
            : first;
    }

    function remoteCommitmentBranches(commitments) {
        var branches = {};
        commitments.forEach(function (commitment) {
            branches[commitment.nombre_local || "Otra sucursal"] = true;
        });
        return Object.keys(branches);
    }

    function remoteCommitmentTimeLabel(commitments) {
        var from = null;
        var to = null;
        if (remoteCommitmentBlocksDay(commitments)) {
            return "Día completo";
        }
        commitments.forEach(function (commitment) {
            if (commitment.hora_desde
                && (!from || String(commitment.hora_desde) < String(from))) {
                from = commitment.hora_desde;
            }
            if (commitment.hora_hasta
                && (!to || String(commitment.hora_hasta) > String(to))) {
                to = commitment.hora_hasta;
            }
        });
        return shortTimeRange(from, to) || "Horario comprometido";
    }

    function remoteCommitmentNotice(date, compact, commitments) {
        var professional = professionalById(state.selectedProfessional) || {};
        var branches = remoteCommitmentBranches(commitments);
        var blocksDay = remoteCommitmentBlocksDay(commitments);
        var branchLabel = branches.join(" y ") || "Otra sucursal";
        var mainLabel = professionalShortName(professional)
            + (blocksDay ? " no disponible" : " tiene otro compromiso");
        var detail = (commitments.length > 1
            ? commitments.length + " compromisos · " : "Asignada en ")
            + branchLabel + " · " + remoteCommitmentTimeLabel(commitments);
        return '<button type="button" class="plan-remote-commitment'
            + (compact ? " is-compact" : "")
            + (blocksDay ? " is-blocking" : " is-timed")
            + '" data-plan-remote-date="' + escapeAttr(date) + '"'
            + ' data-plan-remote-professional="' + escapeAttr(state.selectedProfessional) + '"'
            + ' title="' + escapeAttr(mainLabel + " · " + detail) + '">'
            + '<span class="plan-remote-commitment__icon"><i class="fa-solid fa-building-columns"></i>'
            + '<i class="fa-solid fa-lock"></i></span>'
            + '<span class="plan-remote-commitment__copy"><strong>' + escapeHtml(mainLabel)
            + '</strong><small>' + escapeHtml(detail) + '</small></span></button>';
    }

    function remoteCommitmentMessage(commitments) {
        var branches = remoteCommitmentBranches(commitments);
        return "La profesional ya tiene un compromiso en "
            + (branches.join(" y ") || "otra sucursal")
            + " · " + remoteCommitmentTimeLabel(commitments) + ".";
    }

    function remoteCommitmentLegend() {
        var hasCommitments = state.selectedProfessional
            && ((state.data && state.data.compromisos_otras_sucursales) || [])
                .some(function (commitment) {
                    return String(commitment.cod_profesional)
                        === String(state.selectedProfessional);
                });
        return hasCommitments
            ? '<span class="plan-remote-legend"><i class="fa-solid fa-lock"></i>'
                + ' Compromiso en otra sucursal</span>' : "";
    }

    function agendaOccupancyChip(occupancy, compact, forcedConflict) {
        var professionals = occupancy.profesionales || [];
        var first = professionals.length ? professionals[0] : null;
        var isConflict = professionals.length > 1 || !!forcedConflict;
        var label = professionals.length > 1
            ? "Conflicto: varios doctores"
            : (first ? first.nombre : "Actividad de Agenda");
        var color = first ? stableColor(first.cod_profesional) : "#64748b";
        var time = shortTimeRange(occupancy.hora_desde, occupancy.hora_hasta);
        var html;
        if (!first) { return ""; }
        html = '<button type="button" class="plan-assignment plan-agenda-occupancy'
            + (compact ? " is-compact" : "")
            + (isConflict ? " is-conflict" : "")
            + '" data-plan-agenda-date="' + escapeAttr(occupancy.fecha) + '"'
            + ' data-plan-agenda-room="' + escapeAttr(occupancy.id_consultorio) + '"'
            + ' style="--professional-color:' + escapeAttr(color) + '" title="'
            + escapeAttr(agendaOccupancyLabel(occupancy) + " · Ocupación registrada en Agenda")
            + '">';
        html += '<span class="plan-agenda-occupancy__avatars">';
        professionals.slice(0, 2).forEach(function (professional) {
            html += avatarHtml(professional, "plan-avatar--small");
        });
        html += '</span>';
        html += '<span class="plan-agenda-occupancy__copy"><strong>'
            + escapeHtml(compact && first && !isConflict ? initials(first.nombre) : label)
            + '</strong><small>Agenda · ' + escapeHtml(occupancy.cantidad_registros || 0)
            + (time ? " · " + escapeHtml(time) : "") + '</small></span>';
        return html + '</button>';
    }

    function statusLabel(status) {
        var labels = {
            confirmada: "Confirmada",
            pendiente_horario: "Asignada · horario por definir",
            agenda: "Asignada · Agenda",
            propuesta: "Propuesta"
        };
        return labels[status] || status || "Sin estado";
    }

    function assignmentChip(assignment, compact, agendaOccupancy) {
        var professional = professionalById(assignment.cod_profesional) || assignment;
        var color = stableColor(assignment.cod_profesional);
        var time = assignment.hora_entrada
            ? String(assignment.hora_entrada).slice(0, 5)
                + (assignment.hora_salida ? "–" + String(assignment.hora_salida).slice(0, 5) : "")
            : "";
        var secondary = assignment.origen === "agenda"
            ? statusLabel(assignment.estado) + (time ? " · " + time : "")
            : (time || statusLabel(assignment.estado));
        return '<button type="button" class="plan-assignment plan-assignment--'
            + escapeAttr(assignment.estado) + (compact ? " is-compact" : "")
            + '" data-plan-assignment="' + escapeAttr(assignment.clave) + '"'
            + ((canManage() && assignment.origen === "asignacion") ? ' draggable="true"' : "")
            + ' style="--professional-color:' + escapeAttr(color) + '" title="'
            + escapeAttr((professional.nombre || assignment.profesional) + " · " + statusLabel(assignment.estado)
                + (agendaOccupancy ? " · Agenda " + agendaCountLabel(agendaOccupancy) : ""))
            + '">'
            + (compact && assignment.origen !== "agenda"
                ? '<span class="plan-assignment__initials">' + escapeHtml(initials(professional.nombre || assignment.profesional)) + '</span>'
                : avatarHtml(professional, "plan-avatar--small"))
            + '<span class="plan-assignment__copy"><strong>'
            + escapeHtml(compact ? initials(professional.nombre || assignment.profesional) : (professional.nombre || assignment.profesional))
            + '</strong><small>' + escapeHtml(secondary) + '</small></span>'
            + (assignment.es_recurrente ? '<i class="fa-solid fa-repeat plan-assignment__recurrence" aria-label="Recurrente"></i>' : "")
            + agendaBadge(agendaOccupancy)
            + '</button>';
    }

    function dayCard(date, compact, outsideMonth) {
        var iso = formatIso(date);
        var holiday = holidayByDate(iso);
        var remoteCommitments = remoteCommitmentsForSelected(iso);
        var professionalBlocked = remoteCommitmentBlocksDay(remoteCommitments);
        var selectedProfessional = professionalById(state.selectedProfessional) || {};
        var rooms = state.data.consultorios || [];
        var html = '<article class="plan-day-card'
            + (holiday ? " is-holiday" : "")
            + (remoteCommitments.length ? " has-remote-commitment" : "")
            + (professionalBlocked ? " is-professional-unavailable" : "")
            + (formatIso(new Date()) === iso ? " is-today" : "")
            + (outsideMonth ? " is-outside" : "")
            + '"><header><span>' + escapeHtml(SHORT_DAYS[date.getDay()]) + '</span><strong>'
            + date.getDate() + '</strong></header>';
        if (holiday) {
            html += '<div class="plan-holiday-label"><i class="fa-regular fa-calendar-xmark"></i><strong>Feriado</strong><span>'
                + escapeHtml(holiday.descripcion) + '</span></div>';
        }
        if (!holiday && remoteCommitments.length) {
            html += remoteCommitmentNotice(iso, compact, remoteCommitments);
        }
        html += '<div class="plan-day-slots">';
        rooms.forEach(function (room) {
            var assignments = assignmentsFor(iso, room.id_consultorio);
            var agendaOccupancy = agendaOccupancyFor(iso, room.id_consultorio);
            var agendaBlocksSlot = agendaOccupancyBlocksSlot(agendaOccupancy);
            var agendaMatches = agendaOccupancy
                && agendaMatchesAssignments(agendaOccupancy, assignments);
            var occupied = assignments.length > 0 || agendaBlocksSlot;
            var blockedForSelected = professionalBlocked && !occupied;
            var interactive = !holiday && (occupied || (canCreate() && !blockedForSelected));
            var occupancyText = assignments.length
                ? (assignments[0].profesional || "Consultorio ocupado")
                : (agendaBlocksSlot ? agendaOccupancyLabel(agendaOccupancy) : "");
            html += '<div class="plan-slot' + (holiday ? " is-disabled" : "")
                + (occupied ? " is-occupied" : "")
                + (blockedForSelected ? " is-professional-blocked" : "")
                + '" data-plan-date="' + escapeAttr(iso)
                + '" data-plan-room="' + escapeAttr(room.id_consultorio)
                + '" data-plan-occupied="' + (occupied ? "1" : "0")
                + '" data-plan-professional-blocked="' + (blockedForSelected ? "1" : "0")
                + '" role="button" tabindex="' + (interactive ? "0" : "-1") + '" aria-label="'
                + escapeAttr(room.etiqueta + " " + room.nombre + ", " + LONG_DAYS[date.getDay()] + " " + date.getDate())
                + (occupancyText
                    ? ". Ocupado por " + escapeAttr(occupancyText)
                    : (blockedForSelected
                        ? ". " + escapeAttr(professionalShortName(selectedProfessional))
                            + " no disponible; libre para otros profesionales"
                        : ". Libre"))
                + '"><span class="plan-slot__label" title="' + escapeAttr(room.nombre) + '">'
                + escapeHtml(room.etiqueta) + '</span><div class="plan-slot__content">';
            if (holiday) {
                html += '<span class="plan-slot__free">—</span>';
            } else {
                assignments.forEach(function (assignment) {
                    html += assignmentChip(
                        assignment,
                        compact,
                        agendaMatches ? agendaOccupancy : null
                    );
                });
                if (agendaOccupancy && agendaBlocksSlot && !agendaMatches) {
                    html += agendaOccupancyChip(
                        agendaOccupancy,
                        compact,
                        assignments.length > 0
                    );
                }
                if (!occupied && blockedForSelected) {
                    html += '<span class="plan-slot__blocked">No disponible para '
                        + escapeHtml(professionalShortName(selectedProfessional))
                        + '<small>Libre para otros profesionales</small></span>';
                } else if (!occupied) {
                    html += '<span class="plan-slot__free">Libre</span>';
                }
            }
            html += '</div></div>';
        });
        if (!rooms.length) {
            html += '<div class="plan-slot-empty">Sin consultorios activos</div>';
        }
        html += '</div></article>';
        return html;
    }

    function localById(id) {
        var result = null;
        ((state.data && state.data.locales) || []).some(function (local) {
            if (String(local.cod_local) === String(id)) {
                result = local;
                return true;
            }
            return false;
        });
        return result;
    }

    function routeAssignmentsForDate(date) {
        return filteredAssignments().filter(function (assignment) {
            return assignment.fecha === date
                && String(assignment.cod_profesional) === String(state.selectedProfessional);
        }).sort(function (a, b) {
            var timeA = a.hora_entrada ? String(a.hora_entrada) : "99:99";
            var timeB = b.hora_entrada ? String(b.hora_entrada) : "99:99";
            if (timeA !== timeB) { return timeA < timeB ? -1 : 1; }
            return Number(a.cod_local || 0) - Number(b.cod_local || 0);
        });
    }

    function routeAlertFor(assignmentKey) {
        var result = null;
        var priority = {
            pendiente_traslado: 1,
            revisar_traslado: 2,
            superposicion_sucursales: 3
        };
        ((state.data && state.data.alertas_traslado) || []).forEach(function (alert) {
            if (String(alert.clave_destino) !== String(assignmentKey)) { return; }
            if (!result || (priority[alert.tipo] || 0) > (priority[result.tipo] || 0)) {
                result = alert;
            }
        });
        return result;
    }

    function routeAssignmentCard(assignment, previous) {
        var professional = professionalById(assignment.cod_profesional) || assignment;
        var local = localById(assignment.cod_local) || {};
        var branchColor = stableBranchColor(assignment.cod_local);
        var professionalColor = stableColor(assignment.cod_profesional);
        var alert = routeAlertFor(assignment.clave);
        var time = assignment.hora_entrada
            ? String(assignment.hora_entrada).slice(0, 5)
                + (assignment.hora_salida ? "–" + String(assignment.hora_salida).slice(0, 5) : "")
            : "";
        var changedBranch = previous
            && String(previous.cod_local) !== String(assignment.cod_local);
        var sigla = assignment.sigla_local || local.sigla || "SL";
        var html = '<article class="plan-route-assignment plan-route-assignment--'
            + escapeAttr(assignment.estado) + (alert ? " has-route-alert" : "")
            + '" data-plan-route-assignment="' + escapeAttr(assignment.clave) + '"'
            + ' data-plan-route-date="' + escapeAttr(assignment.fecha) + '"'
            + ' data-plan-route-local="' + escapeAttr(assignment.cod_local) + '"'
            + ' data-plan-route-status="' + escapeAttr(assignment.estado) + '"'
            + ' data-plan-route-sigla="' + escapeAttr(sigla) + '"'
            + ' style="--professional-color:' + escapeAttr(professionalColor)
            + ';--branch-color:' + escapeAttr(branchColor) + '" title="'
            + escapeAttr((professional.nombre || assignment.profesional) + " · "
                + (assignment.nombre_local || local.nombre || "Sucursal") + " · "
                + assignment.consultorio + " · " + statusLabel(assignment.estado)) + '">';
        if (changedBranch) {
            html += '<span class="plan-route-assignment__change"><i class="fa-solid fa-arrow-right-arrow-left"></i> Cambio de sucursal</span>';
        }
        html += '<div class="plan-route-assignment__main"><span class="plan-route-branch">'
            + '<i class="fa-solid fa-building-columns" aria-hidden="true"></i><strong>'
            + escapeHtml(sigla) + '</strong></span><span class="plan-route-assignment__copy"><strong>'
            + escapeHtml(assignment.consultorio || "Consultorio") + '</strong><small>'
            + escapeHtml(time || statusLabel(assignment.estado)) + '</small></span>'
            + '<span class="plan-route-assignment__status">' + escapeHtml(statusLabel(assignment.estado)) + '</span></div>';
        if (alert) {
            html += '<span class="plan-route-alert plan-route-alert--' + escapeAttr(alert.tipo) + '"><i class="fa-solid '
                + (alert.tipo === "superposicion_sucursales" ? "fa-circle-exclamation" : "fa-triangle-exclamation")
                + '"></i>' + escapeHtml(alert.mensaje) + '</span>';
        }
        return html + '</article>';
    }

    function routeWeekSummary(week) {
        var from = formatIso(week[0]);
        var to = formatIso(week[week.length - 1]);
        var stats = {};
        filteredAssignments().forEach(function (assignment) {
            var local;
            var key;
            if (String(assignment.cod_profesional) !== String(state.selectedProfessional)
                || assignment.fecha < from || assignment.fecha > to) {
                return;
            }
            key = String(assignment.cod_local);
            local = localById(assignment.cod_local) || {};
            if (!stats[key]) {
                stats[key] = {
                    name: local.nombre || assignment.nombre_local || "Sucursal",
                    dates: {}
                };
            }
            stats[key].dates[assignment.fecha] = true;
        });
        return Object.keys(stats).map(function (key) {
            var days = Object.keys(stats[key].dates).length;
            return stats[key].name + " " + days + (days === 1 ? " día" : " días");
        }).join(" · ");
    }

    function routeDayCard(date, outsideMonth) {
        var iso = formatIso(date);
        var assignments = routeAssignmentsForDate(iso);
        var html = '<article class="plan-day-card plan-route-day'
            + (formatIso(new Date()) === iso ? " is-today" : "")
            + (outsideMonth ? " is-outside" : "")
            + '"><header><span>' + escapeHtml(SHORT_DAYS[date.getDay()]) + '</span><strong>'
            + date.getDate() + '</strong></header><div class="plan-route-day__content">';
        if (!assignments.length) {
            html += '<span class="plan-route-day__free">Libre</span>';
        } else {
            assignments.forEach(function (assignment, index) {
                html += routeAssignmentCard(assignment, index > 0 ? assignments[index - 1] : null);
            });
        }
        return html + '</div></article>';
    }

    function renderMultibranchCalendar(calendar, dates) {
        var professional = professionalById(state.selectedProfessional);
        var html = '<div class="plan-calendar-heading plan-route-calendar-heading"><div><strong>'
            + escapeHtml(MONTHS[state.date.getMonth()] + " " + state.date.getFullYear())
            + '</strong><span>Vista mensual · Todas las sucursales autorizadas</span></div>';
        if (professional) {
            html += '<span class="plan-route-professional-badge" style="--professional-color:'
                + escapeAttr(stableColor(professional.cod_profesional)) + '"><i class="fa-solid fa-route"></i>'
                + 'Recorrido multisucursal · ' + escapeHtml(professional.nombre) + '</span>';
        }
        html += '</div>';
        if (!professional) {
            calendar.innerHTML = html + '<div class="plan-empty"><i class="fa-solid fa-user-doctor"></i><strong>Selecciona una profesional</strong><p>El recorrido mensual aparecerá después de seleccionar una tarjeta.</p></div>';
            return;
        }
        html += '<div class="plan-month-weeks plan-route-weeks">';
        for (var i = 0; i < dates.length; i += 7) {
            var week = dates.slice(i, i + 7);
            var weekNumber = (i / 7) + 1;
            var summary = routeWeekSummary(week);
            html += '<section class="plan-month-week plan-route-week" data-plan-route-week="' + weekNumber
                + '"><header><strong>Semana ' + weekNumber + '</strong><span>'
                + week[0].getDate() + " – " + week[6].getDate() + " "
                + escapeHtml(MONTHS[week[6].getMonth()].slice(0, 3).toLowerCase())
                + (summary ? " · " + escapeHtml(summary) : "") + '</span></header>'
                + '<div class="plan-month-week__days">';
            week.forEach(function (date) {
                html += routeDayCard(date, date.getMonth() !== state.date.getMonth());
            });
            html += '</div></section>';
        }
        html += '</div>';
        calendar.innerHTML = html;
    }

    function renderCalendar() {
        var calendar = state.root.querySelector("#planCalendar");
        var range = currentRange();
        var dates = datesBetween(range.from, range.to);
        var html = "";
        var i;
        if (state.scope === "multi") {
            renderMultibranchCalendar(calendar, dates);
            return;
        }
        if (state.view === "week") {
            html += '<div class="plan-calendar-heading"><div><strong>Vista semanal</strong><span>'
                + range.from.getDate() + " " + MONTHS[range.from.getMonth()].toLowerCase()
                + " – " + range.to.getDate() + " " + MONTHS[range.to.getMonth()].toLowerCase()
                + '</span></div><div class="plan-calendar-heading__aside">'
                + remoteCommitmentLegend() + '<span>'
                + (state.data.consultorios || []).length + ' consultorios</span></div></div>';
            html += '<div class="plan-week-grid">';
            dates.forEach(function (date) {
                html += dayCard(date, false, false);
            });
            html += '</div>';
        } else {
            html += '<div class="plan-calendar-heading"><div><strong>'
                + escapeHtml(MONTHS[state.date.getMonth()] + " " + state.date.getFullYear())
                + '</strong><span>Vista mensual agrupada por semanas</span></div>'
                + '<div class="plan-calendar-heading__aside">' + remoteCommitmentLegend()
                + '<span>' + (state.data.consultorios || []).length
                + ' consultorios</span></div></div>';
            html += '<div class="plan-month-weeks">';
            for (i = 0; i < dates.length; i += 7) {
                var week = dates.slice(i, i + 7);
                var weekNumber = (i / 7) + 1;
                html += '<section class="plan-month-week"><header><strong>Semana ' + weekNumber
                    + '</strong><span>' + week[0].getDate() + " – " + week[6].getDate() + " "
                    + escapeHtml(MONTHS[week[6].getMonth()].slice(0, 3).toLowerCase()) + '</span></header>'
                    + '<div class="plan-month-week__days">';
                week.forEach(function (date) {
                    html += dayCard(date, true, date.getMonth() !== state.date.getMonth());
                });
                html += '</div></section>';
            }
            html += '</div>';
        }
        calendar.innerHTML = html;
        bindDraggables();
    }

    function renderPeriod() {
        var label = state.root.querySelector("#planPeriodLabel");
        var picker = state.root.querySelector("#planDatePicker");
        var switches = state.root.querySelectorAll("[data-plan-view]");
        var range = currentRange();
        var i;
        if (state.view === "month") {
            label.textContent = MONTHS[state.date.getMonth()] + " " + state.date.getFullYear();
        } else {
            label.textContent = range.from.getDate() + " " + MONTHS[range.from.getMonth()].slice(0, 3)
                + " – " + range.to.getDate() + " " + MONTHS[range.to.getMonth()].slice(0, 3);
        }
        picker.value = formatIso(state.date);
        for (i = 0; i < switches.length; i++) {
            switches[i].classList.toggle("is-active", switches[i].getAttribute("data-plan-view") === state.view);
            switches[i].setAttribute("aria-pressed", switches[i].classList.contains("is-active") ? "true" : "false");
            switches[i].disabled = state.scope === "multi"
                && switches[i].getAttribute("data-plan-view") === "week";
            switches[i].title = switches[i].disabled
                ? "El recorrido multisucursal está disponible en la vista mensual." : "";
        }
    }

    function canManage() {
        return !!(state.data && state.data.estructura_instalada
            && state.data.contexto_usuario && state.data.contexto_usuario.permisos.gestionar);
    }

    function canManageMultibranch() {
        return !!(canManage()
            && state.data
            && state.data.contexto_usuario
            && state.data.contexto_usuario.permisos
            && state.data.contexto_usuario.permisos.todas_sucursales);
    }

    function canCreate() {
        var permissions = state.data && state.data.contexto_usuario
            ? state.data.contexto_usuario.permisos : {};
        return !!(state.data && state.data.estructura_instalada
            && (permissions.gestionar || permissions.proponer));
    }

    function canRecur() {
        return !!(state.data && state.data.estructura_instalada
            && state.data.contexto_usuario && state.data.contexto_usuario.permisos.recurrencias);
    }

    function renderSettings() {
        var panel = state.root.querySelector("#planSettings");
        var specialties = {};
        var canViewMulti = !!(state.data.contexto_usuario
            && state.data.contexto_usuario.permisos
            && state.data.contexto_usuario.permisos.todas_sucursales);
        var html = '<div class="plan-popover__header"><strong>Ajustes de la vista</strong><button type="button" data-plan-action="close-popovers" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>'
            + '<label class="plan-settings__scope">Vista de planificación<select data-plan-scope>'
            + '<option value="branch"' + (state.scope === "branch" ? " selected" : "") + '>Sucursal actual</option>'
            + (canViewMulti ? '<option value="multi"' + (state.scope === "multi" ? " selected" : "") + '>Recorrido multisucursal</option>' : "")
            + '</select><small>' + (state.scope === "multi"
                ? "Consulta mensual de todas las sucursales autorizadas."
                : "Gestión operativa de una sucursal.") + '</small></label>'
            + '<div class="plan-settings__grid">'
            + '<label>Profesional<select data-plan-filter="professional"><option value="">Todos</option>';
        (state.data.profesionales || []).forEach(function (professional) {
            html += '<option value="' + escapeAttr(professional.cod_profesional) + '"'
                + (String(state.filters.professional) === String(professional.cod_profesional) ? " selected" : "")
                + '>' + escapeHtml(professional.nombre) + '</option>';
            if (professional.especialidad) { specialties[professional.especialidad] = true; }
        });
        html += '</select></label><label>Especialidad<select data-plan-filter="specialty"><option value="">Todas</option>';
        Object.keys(specialties).sort().forEach(function (specialty) {
            html += '<option value="' + escapeAttr(specialty) + '"'
                + (state.filters.specialty === specialty ? " selected" : "") + '>'
                + escapeHtml(specialty) + '</option>';
        });
        html += '</select></label>';
        if (state.scope === "branch") {
            html += '<label>Consultorio<select data-plan-filter="room"><option value="">Todos</option>';
            (state.data.consultorios || []).forEach(function (room) {
                html += '<option value="' + escapeAttr(room.id_consultorio) + '"'
                    + (String(state.filters.room) === String(room.id_consultorio) ? " selected" : "") + '>'
                    + escapeHtml(room.etiqueta + " · " + room.nombre) + '</option>';
            });
            html += '</select></label>';
        }
        html += '<label>Estado<select data-plan-filter="status">'
            + '<option value="">Todos</option><option value="confirmada">Confirmadas</option>'
            + '<option value="pendiente_horario">Asignadas sin horario</option>'
            + '<option value="agenda">Asignadas desde Agenda</option><option value="propuesta">Propuestas</option>'
            + '</select></label><label>Hilos<select data-plan-filter="threads">'
            + '<option value="selected">Solo seleccionado</option>'
            + (state.scope === "branch" ? '<option value="all">Todos</option>' : "")
            + '<option value="off">Ocultos</option>'
            + '</select></label></div>'
            + '<div class="plan-legend"><strong>Leyenda</strong>'
            + '<span><i class="plan-legend__line"></i> ' + (state.scope === "multi" ? "Recorrido semanal" : "Hilo del profesional") + '</span>'
            + '<span><i class="plan-legend__box"></i> Libre</span>'
            + '<span><i class="plan-legend__box plan-legend__box--holiday"></i> Feriado</span>'
            + '<span><i class="plan-legend__box plan-legend__box--pending"></i> Asignada · horario por definir</span></div>';
        if (canManage() && state.scope === "branch") {
            html += '<form class="plan-specialty-editor" id="planSpecialtyForm"><strong>Especialidad opcional</strong>'
                + '<div><select name="cod_profesional" aria-label="Profesional">';
            (state.data.profesionales || []).forEach(function (professional) {
                html += '<option value="' + escapeAttr(professional.cod_profesional) + '">'
                    + escapeHtml(professional.nombre) + '</option>';
            });
            html += '</select><input name="especialidad" maxlength="120" value="'
                + escapeAttr((state.data.profesionales && state.data.profesionales[0]
                    && state.data.profesionales[0].especialidad) || "")
                + '" placeholder="Ej.: Ortodoncia">'
                + '<button type="submit">Guardar</button></div><small>El color se calcula automáticamente y permanece estable.</small></form>';
        }
        if (state.scope === "branch"
            && state.data.contexto_usuario.permisos.historial && state.data.estructura_instalada) {
            html += '<button type="button" class="plan-settings__history" data-plan-action="history"><i class="fa-solid fa-clock-rotate-left"></i> Ver historial de cambios</button>';
        }
        panel.innerHTML = html;
        setSelectValue(panel.querySelector('[data-plan-filter="status"]'), state.filters.status);
        setSelectValue(panel.querySelector('[data-plan-filter="threads"]'), state.filters.threads);
    }

    function setSelectValue(select, value) {
        if (select) { select.value = value || ""; }
    }

    function renderAlerts() {
        var panel = state.root.querySelector("#planAlerts");
        var dot = state.root.querySelector("#planAlertDot");
        var pending = 0;
        var proposals = 0;
        var transferAlerts = (state.data && state.data.alertas_traslado) || [];
        var overlaps = 0;
        var transfers = 0;
        ((state.data && state.data.asignaciones) || []).forEach(function (assignment) {
            if (assignment.estado === "pendiente_horario") { pending++; }
            if (assignment.estado === "propuesta") { proposals++; }
        });
        transferAlerts.forEach(function (alert) {
            if (alert.tipo === "superposicion_sucursales") {
                overlaps++;
            } else {
                transfers++;
            }
        });
        dot.hidden = state.scope === "multi"
            ? transferAlerts.length === 0
            : (pending + proposals) === 0;
        panel.innerHTML = '<div class="plan-popover__header"><strong>Atención requerida</strong><button type="button" data-plan-action="close-popovers" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>';
        if (state.scope === "multi") {
            panel.innerHTML += '<div class="plan-alert-row"><span class="plan-status-dot plan-status-dot--danger"></span><span>Superposiciones entre sucursales</span><strong>'
                + overlaps + '</strong></div>'
                + '<div class="plan-alert-row"><span class="plan-status-dot plan-status-dot--pending"></span><span>Traslados por revisar</span><strong>'
                + transfers + '</strong></div>'
                + (transferAlerts.length === 0
                    ? '<p class="plan-alert-empty">No hay conflictos de recorrido en el período visible.</p>' : "");
        } else {
            panel.innerHTML += '<div class="plan-alert-row"><span class="plan-status-dot plan-status-dot--pending"></span><span>Asignadas sin horario</span><strong>' + pending + '</strong></div>'
                + '<div class="plan-alert-row"><span class="plan-status-dot plan-status-dot--proposal"></span><span>Propuestas por revisar</span><strong>' + proposals + '</strong></div>'
                + ((pending + proposals) === 0 ? '<p class="plan-alert-empty">No hay asuntos pendientes en el período visible.</p>' : "");
        }
    }

    function closePopovers(exceptId) {
        ["planAlerts", "planSettings"].forEach(function (id) {
            var panel = state.root && state.root.querySelector("#" + id);
            if (panel && id !== exceptId) { panel.hidden = true; }
        });
    }

    function togglePopover(id) {
        var panel = state.root.querySelector("#" + id);
        var willOpen = panel.hidden;
        closePopovers(willOpen ? id : "");
        panel.hidden = !willOpen;
    }

    function bindRootEvents() {
        state.root.addEventListener("click", function (event) {
            var action = event.target.closest("[data-plan-action]");
            var view = event.target.closest("[data-plan-view]");
            var professional = event.target.closest("[data-plan-professional]");
            var remoteCommitment = event.target.closest("[data-plan-remote-date]");
            var assignment = event.target.closest("[data-plan-assignment]");
            var agendaOccupancy = event.target.closest("[data-plan-agenda-date]");
            var slot = event.target.closest(".plan-slot");
            if (action) {
                handleAction(action.getAttribute("data-plan-action"), action);
                return;
            }
            if (view) {
                changeView(view.getAttribute("data-plan-view"));
                return;
            }
            if (assignment) {
                event.stopPropagation();
                openAssignmentDetails(assignment.getAttribute("data-plan-assignment"));
                return;
            }
            if (agendaOccupancy) {
                event.stopPropagation();
                openAgendaOccupancy(
                    agendaOccupancy.getAttribute("data-plan-agenda-date"),
                    agendaOccupancy.getAttribute("data-plan-agenda-room")
                );
                return;
            }
            if (professional) {
                selectProfessional(professional.getAttribute("data-plan-professional"));
                return;
            }
            if (remoteCommitment) {
                openRemoteCommitmentDetails(
                    remoteCommitment.getAttribute("data-plan-remote-date"),
                    remoteCommitment.getAttribute("data-plan-remote-professional")
                );
                return;
            }
            if (slot && !slot.classList.contains("is-disabled")) {
                openAssignmentForSlot(slot.getAttribute("data-plan-date"), slot.getAttribute("data-plan-room"));
            }
        });

        state.root.addEventListener("change", function (event) {
            var filter = event.target.getAttribute("data-plan-filter");
            var scope = event.target.getAttribute("data-plan-scope");
            if (event.target.id === "planBranch") {
                state.selectedProfessional = "";
                loadData(true);
                return;
            }
            if (scope !== null) {
                if (scope !== "branch" && scope !== "multi") { return; }
                state.scope = scope;
                state.filters.room = "";
                if (state.scope === "multi") {
                    state.view = "month";
                    if (state.filters.threads === "all") {
                        state.filters.threads = "selected";
                    }
                }
                loadData(true);
                return;
            }
            if (event.target.id === "planDatePicker") {
                state.date = parseIso(event.target.value);
                loadData(true);
                return;
            }
            if (event.target.name === "cod_profesional"
                && event.target.closest("#planSpecialtyForm")) {
                var specialtyProfessional = professionalById(event.target.value);
                var specialtyInput = event.target.closest("#planSpecialtyForm").querySelector("input[name='especialidad']");
                if (specialtyInput) {
                    specialtyInput.value = specialtyProfessional
                        ? (specialtyProfessional.especialidad || "") : "";
                }
                return;
            }
            if (filter) {
                state.filters[filter] = event.target.value;
                if (filter === "professional" && event.target.value) {
                    state.selectedProfessional = String(event.target.value);
                }
                renderProfessionals();
                renderCalendar();
                window.requestAnimationFrame(renderThreads);
            }
        });

        state.root.addEventListener("submit", function (event) {
            if (event.target.id === "planSpecialtyForm") {
                event.preventDefault();
                saveSpecialty(event.target);
            }
            if (event.target.id === "planAssignmentForm") {
                event.preventDefault();
                saveAssignmentForm(event.target);
            }
        });

        state.root.addEventListener("keydown", function (event) {
            var slot = event.target.closest(".plan-slot");
            if (slot && (event.key === "Enter" || event.key === " ")) {
                event.preventDefault();
                if (slot.getAttribute("data-plan-professional-blocked") === "1") {
                    openRemoteCommitmentDetails(
                        slot.getAttribute("data-plan-date"),
                        state.selectedProfessional
                    );
                } else if (slot.getAttribute("data-plan-occupied") === "1"
                    && agendaOccupancyFor(slot.getAttribute("data-plan-date"), slot.getAttribute("data-plan-room"))) {
                    openAgendaOccupancy(slot.getAttribute("data-plan-date"), slot.getAttribute("data-plan-room"));
                } else {
                    openAssignmentForSlot(slot.getAttribute("data-plan-date"), slot.getAttribute("data-plan-room"));
                }
            }
            if (event.key === "Escape") {
                closeDialog();
                closePopovers("");
            }
        });
    }

    function handleAction(action, element) {
        switch (action) {
            case "close":
                window.cerrarPlanificacionEspecialistas();
                break;
            case "settings":
                togglePopover("planSettings");
                break;
            case "alerts":
                togglePopover("planAlerts");
                break;
            case "close-popovers":
                closePopovers("");
                break;
            case "help":
                openHelp();
                break;
            case "pick-date":
                state.root.querySelector("#planDatePicker").showPicker
                    ? state.root.querySelector("#planDatePicker").showPicker()
                    : state.root.querySelector("#planDatePicker").click();
                break;
            case "previous":
                navigate(-1);
                break;
            case "next":
                navigate(1);
                break;
            case "today":
                state.date = startOfDay(new Date());
                loadData(true);
                break;
            case "retry":
                loadData(true);
                break;
            case "history":
                openHistory();
                break;
            case "add-professional":
                openAddProfessionalDialog();
                break;
            case "remove-professional":
                openRemoveProfessionalDialog(
                    element ? element.getAttribute("data-plan-remove-professional") : ""
                );
                break;
        }
    }

    function changeView(view) {
        if (view !== "week" && view !== "month") { return; }
        if (state.scope === "multi" && view === "week") {
            notify("El recorrido multisucursal se consulta en la vista mensual.", "info");
            return;
        }
        state.view = view;
        try { window.localStorage.setItem("telarPlanificacionVista", view); } catch (ignore) {}
        loadData(true);
    }

    function navigate(direction) {
        if (state.view === "month") {
            state.date = new Date(state.date.getFullYear(), state.date.getMonth() + direction, 1);
        } else {
            state.date = addDays(state.date, direction * 7);
        }
        loadData(true);
    }

    function selectProfessional(id) {
        state.selectedProfessional = String(id);
        renderProfessionals();
        renderCalendar();
        renderFooterHint();
        window.requestAnimationFrame(renderThreads);
    }

    function bindDraggables() {
        var elements = state.root.querySelectorAll("[draggable='true']");
        var slots = state.root.querySelectorAll(".plan-slot:not(.is-disabled):not(.is-occupied)");
        var i;
        for (i = 0; i < elements.length; i++) {
            elements[i].addEventListener("dragstart", onDragStart, false);
            elements[i].addEventListener("dragend", onDragEnd, false);
        }
        for (i = 0; i < slots.length; i++) {
            slots[i].addEventListener("dragover", onDragOver, false);
            slots[i].addEventListener("dragleave", onDragLeave, false);
            slots[i].addEventListener("drop", onDrop, false);
        }
    }

    function onDragStart(event) {
        var professional = event.currentTarget.getAttribute("data-plan-professional");
        var assignment = event.currentTarget.getAttribute("data-plan-assignment");
        state.drag = professional
            ? { type: "professional", professional: professional }
            : { type: "assignment", key: assignment };
        event.currentTarget.classList.add("is-dragging");
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = assignment ? "move" : "copy";
            event.dataTransfer.setData("text/plain", professional || assignment || "");
        }
    }

    function onDragEnd(event) {
        event.currentTarget.classList.remove("is-dragging");
        state.drag = null;
        Array.prototype.forEach.call(state.root.querySelectorAll(".plan-slot.is-drop-target"), function (slot) {
            slot.classList.remove("is-drop-target");
        });
    }

    function dragProfessionalId(drag) {
        var assignment;
        if (!drag) { return ""; }
        if (drag.type === "professional") {
            return String(drag.professional || "");
        }
        assignment = assignmentByKey(drag.key);
        return assignment ? String(assignment.cod_profesional) : "";
    }

    function remoteCommitmentsBlockingDrag(drag, date) {
        var professionalId = dragProfessionalId(drag);
        var commitments = professionalId ? remoteCommitmentsFor(date, professionalId) : [];
        if (drag && drag.type === "professional") {
            return commitments;
        }
        return commitments.filter(function (commitment) {
            return commitment.bloquea_dia === true;
        });
    }

    function onDragOver(event) {
        var date = event.currentTarget.getAttribute("data-plan-date");
        if (!state.drag || event.currentTarget.classList.contains("is-occupied")
            || remoteCommitmentsBlockingDrag(state.drag, date).length) {
            if (event.dataTransfer) { event.dataTransfer.dropEffect = "none"; }
            return;
        }
        event.preventDefault();
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = state.drag.type === "assignment" ? "move" : "copy";
        }
        event.currentTarget.classList.add("is-drop-target");
    }

    function onDragLeave(event) {
        event.currentTarget.classList.remove("is-drop-target");
    }

    function onDrop(event) {
        var drag = state.drag;
        var date = event.currentTarget.getAttribute("data-plan-date");
        var roomId = event.currentTarget.getAttribute("data-plan-room");
        var remoteCommitments;
        var professionalId;
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove("is-drop-target");
        if (!drag) { return; }
        if (slotIsOccupied(date, roomId)) {
            notify("La casilla ya está ocupada. Abra el detalle para ver quién la utiliza.", "info");
            if (agendaOccupancyBlocksSlot(agendaOccupancyFor(date, roomId))) {
                openAgendaOccupancy(date, roomId);
            }
            return;
        }
        remoteCommitments = remoteCommitmentsBlockingDrag(drag, date);
        if (remoteCommitments.length) {
            professionalId = dragProfessionalId(drag);
            if (professionalId) {
                selectProfessional(professionalId);
            }
            notify(remoteCommitmentMessage(remoteCommitments), "info");
            openRemoteCommitmentDetails(date, professionalId);
            return;
        }
        if (drag.type === "professional") {
            state.selectedProfessional = String(drag.professional);
            if (canManage()) {
                saveDirectAssignment(
                    drag.professional,
                    date,
                    roomId,
                    event.currentTarget
                );
            } else {
                openAssignmentDialog({
                    cod_profesional: drag.professional,
                    fecha: date,
                    id_consultorio: roomId
                });
            }
        } else {
            openAssignmentDetails(
                drag.key,
                date,
                roomId
            );
        }
    }

    function assignmentByKey(key) {
        var result = null;
        visualAssignments().some(function (assignment) {
            if (assignment.clave === key) {
                result = assignment;
                return true;
            }
            return false;
        });
        return result;
    }

    function openAssignmentForSlot(date, roomId) {
        var occupancy = agendaOccupancyFor(date, roomId);
        var assignments = assignmentsFor(date, roomId);
        var remoteCommitments = remoteCommitmentsForSelected(date);
        if (agendaOccupancyBlocksSlot(occupancy)) {
            openAgendaOccupancy(date, roomId);
            return;
        }
        if (assignments.length) {
            notify("La casilla ya está ocupada por "
                + (assignments[0].profesional || "otro doctor") + ".", "info");
            return;
        }
        if (!canCreate()) {
            notify(state.data && !state.data.estructura_instalada
                ? "La vista está en modo consulta hasta aplicar la migración controlada."
                : "No tiene permiso para crear asignaciones.", "info");
            return;
        }
        if (!state.selectedProfessional) {
            notify("Seleccione primero un profesional.", "info");
            return;
        }
        if (remoteCommitmentBlocksDay(remoteCommitments)) {
            notify(remoteCommitmentMessage(remoteCommitments), "info");
            openRemoteCommitmentDetails(date, state.selectedProfessional);
            return;
        }
        openAssignmentDialog({
            cod_profesional: state.selectedProfessional,
            fecha: date,
            id_consultorio: roomId
        });
    }

    function saveDirectAssignment(professionalId, date, roomId, slot) {
        var remoteCommitments = remoteCommitmentsFor(date, professionalId);
        if (state.savingAssignment) { return; }
        if (slotIsOccupied(date, roomId)) {
            notify("La casilla dejó de estar libre. Actualice la vista y revise la ocupación.", "error");
            loadData(false);
            return;
        }
        if (remoteCommitments.length) {
            notify(remoteCommitmentMessage(remoteCommitments), "info");
            openRemoteCommitmentDetails(date, professionalId);
            return;
        }
        state.savingAssignment = true;
        if (slot) { slot.classList.add("is-saving"); }
        request("guardarAsignacion", {
            cod_local: selectedLocal(),
            cod_profesional: professionalId,
            fecha: date,
            id_consultorio: roomId,
            id_horario: "",
            motivo: "Asignado directamente desde planificación visual."
        }).then(function () {
            state.savingAssignment = false;
            notify("Doctor fijado al día y consultorio.", "success");
            loadData(false);
        }).catch(function (error) {
            state.savingAssignment = false;
            if (slot) { slot.classList.remove("is-saving"); }
            notify(error.message, "error");
            if (error.code === "conflicto_planificacion") {
                loadData(false);
            }
        });
    }

    function openAssignmentDetails(key, targetDate, targetRoom) {
        var assignment = assignmentByKey(key);
        if (!assignment) { return; }
        if (assignment.origen === "agenda") {
            openAgendaOccupancy(assignment.fecha, assignment.id_consultorio);
            return;
        }
        if (assignment.origen === "legacy") {
            openReadOnlyAssignment(assignment);
            return;
        }
        openAssignmentDialog({
            id_asignacion: assignment.id_asignacion,
            id_regla: assignment.id_regla,
            clave: assignment.clave,
            version: assignment.version,
            cod_profesional: assignment.cod_profesional,
            fecha: targetDate || assignment.fecha,
            fecha_original: assignment.fecha,
            id_consultorio: targetRoom || assignment.id_consultorio,
            id_horario: assignment.id_horario,
            estado: assignment.estado,
            motivo: assignment.motivo,
            origen: assignment.origen,
            es_recurrente: assignment.es_recurrente
        });
    }

    function scheduleOptions(professionalId, date, selectedId) {
        var dayNames = ["domingo", "lunes", "martes", "miercoles", "jueves", "viernes", "sabado"];
        var day = dayNames[parseIso(date).getDay()];
        var html = '<option value="">Sin horario vinculado · Se asignará al día</option>';
        ((state.data && state.data.horarios) || []).forEach(function (schedule) {
            if (String(schedule.cod_profesional) !== String(professionalId)
                || String(schedule.dia_semana).toLowerCase() !== day) {
                return;
            }
            if ((schedule.vigente_desde && date < schedule.vigente_desde)
                || (schedule.vigente_hasta && date > schedule.vigente_hasta)) {
                return;
            }
            html += '<option value="' + escapeAttr(schedule.id_horario) + '"'
                + (String(selectedId || "") === String(schedule.id_horario) ? " selected" : "")
                + '>' + escapeHtml(String(schedule.hora_entrada || "").slice(0, 5)
                    + (schedule.hora_salida ? " – " + String(schedule.hora_salida).slice(0, 5) : " · salida no indicada"))
                + '</option>';
        });
        return html;
    }

    function openAssignmentDialog(item) {
        var isDirectEdit = !!item.id_asignacion;
        var isRuleOccurrence = item.origen === "regla";
        var professional = professionalById(item.cod_profesional);
        var room = roomById(item.id_consultorio);
        var permissions = state.data.contexto_usuario.permisos;
        var externalWithoutSchedule = !!(professional
            && professional.origen_listado === "vinculo"
            && !professional.tiene_horario_local);
        var title = isDirectEdit ? "Editar asignación" : (isRuleOccurrence ? "Detalle de recurrencia" : "Nueva asignación");
        var html = '<div class="plan-dialog plan-dialog--assignment" role="dialog" aria-modal="true" aria-labelledby="planDialogTitle">'
            + '<header><div><span>Planificación de especialistas</span><h2 id="planDialogTitle">' + escapeHtml(title) + '</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<form id="planAssignmentForm"><input type="hidden" name="id_asignacion" value="' + escapeAttr(item.id_asignacion || "") + '">'
            + '<input type="hidden" name="id_regla" value="' + escapeAttr(item.id_regla || "") + '">'
            + '<input type="hidden" name="version_esperada" value="' + escapeAttr(item.version || "") + '">'
            + '<input type="hidden" name="origen" value="' + escapeAttr(item.origen || "") + '">'
            + '<div class="plan-dialog-summary"><span style="--professional-color:' + escapeAttr(stableColor(item.cod_profesional)) + '">'
            + avatarHtml(professional || { nombre: "Profesional" }) + '</span><div><small>Profesional seleccionado</small><strong>'
            + escapeHtml(professional ? professional.nombre : "Profesional") + '</strong><span>'
            + escapeHtml(professional && professional.especialidad ? professional.especialidad : "Especialidad no indicada") + '</span></div></div>'
            + (externalWithoutSchedule
                ? '<div class="plan-form-info plan-form-info--branch"><i class="fa-solid fa-route"></i><span><strong>Asignación en otra sucursal.</strong> Base: '
                    + escapeHtml(professional.nombre_local_base || "Otra sucursal") + ' · Destino: '
                    + escapeHtml(state.data.local_actual.nombre || "Sucursal seleccionada") + '.</span></div>' : "")
            + '<div class="plan-form-grid"><label>Profesional<select name="cod_profesional" id="planFormProfessional"'
            + (isRuleOccurrence ? " disabled" : "") + '>';
        (state.data.profesionales || []).forEach(function (entry) {
            html += '<option value="' + escapeAttr(entry.cod_profesional) + '"'
                + (String(entry.cod_profesional) === String(item.cod_profesional) ? " selected" : "") + '>'
                + escapeHtml(entry.nombre) + '</option>';
        });
        html += '</select></label><label>Fecha<input type="date" name="fecha" id="planFormDate" value="'
            + escapeAttr(item.fecha) + '"' + (isRuleOccurrence ? " disabled" : "") + '></label>'
            + '<label>Consultorio<select name="id_consultorio"' + (isRuleOccurrence ? " disabled" : "") + '>';
        (state.data.consultorios || []).forEach(function (entry) {
            html += '<option value="' + escapeAttr(entry.id_consultorio) + '"'
                + (String(entry.id_consultorio) === String(item.id_consultorio) ? " selected" : "") + '>'
                + escapeHtml(entry.etiqueta + " · " + entry.nombre) + '</option>';
        });
        html += '</select></label><label>Horario vinculado<select name="id_horario" id="planFormSchedule"'
            + (isRuleOccurrence ? " disabled" : "") + '>'
            + scheduleOptions(item.cod_profesional, item.fecha, item.id_horario) + '</select></label></div>';
        if (!isDirectEdit && !isRuleOccurrence && canRecur() && !externalWithoutSchedule) {
            html += '<fieldset class="plan-recurrence"><legend>Tipo de asignación</legend><label><input type="radio" name="tipo" value="puntual" checked><span><strong>Puntual</strong><small>Solamente la fecha elegida</small></span></label>'
                + '<label><input type="radio" name="tipo" value="recurrente"><span><strong>Semanal</strong><small>Se repite el mismo día</small></span></label>'
                + '<div class="plan-recurrence-end" id="planRecurrenceEnd" hidden><label>Hasta <input type="date" name="fecha_hasta"><small>Déjelo vacío para continuidad sin fecha final.</small></label></div></fieldset>';
        }
        html += '<label class="plan-form-reason">Motivo u observación<textarea name="motivo" maxlength="255" placeholder="Contexto breve para la trazabilidad">'
            + escapeHtml(item.motivo || "") + '</textarea></label>';
        if (!permissions.gestionar && permissions.proponer) {
            html += '<div class="plan-form-info"><i class="fa-regular fa-lightbulb"></i><span>Esta acción se guardará como <strong>propuesta</strong> para revisión.</span></div>';
        } else if (!item.id_horario && !isRuleOccurrence) {
            html += '<div class="plan-form-info plan-form-info--warning"><i class="fa-regular fa-clock"></i><span>La doctora quedará <strong>asignada a este día y consultorio</strong>. El horario detallado se completará desde Agenda.</span></div>';
        }
        html += '<footer>';
        if (isDirectEdit && canManage()) {
            html += '<button type="button" class="plan-button plan-button--danger" data-plan-dialog="cancel-assignment">Anular asignación</button>';
        }
        if (isRuleOccurrence && canManage()) {
            html += '<button type="button" class="plan-button plan-button--danger" data-plan-dialog="cancel-occurrence">Anular este día</button>';
        }
        if (isRuleOccurrence && canRecur()) {
            html += '<button type="button" class="plan-button plan-button--secondary" data-plan-dialog="cancel-rule">Finalizar serie</button>';
        }
        html += '<span class="plan-dialog-spacer"></span><button type="button" class="plan-button plan-button--ghost" data-plan-dialog="close">Cancelar</button>';
        if (!isRuleOccurrence) {
            html += '<button type="submit" class="plan-button plan-button--primary"><i class="fa-solid fa-check"></i>'
                + (isDirectEdit ? "Guardar cambios" : (permissions.gestionar ? "Asignar" : "Enviar propuesta")) + '</button>';
        }
        html += '</footer></form></div>';
        openDialog(html, item);
        bindAssignmentDialog();
    }

    function openReadOnlyAssignment(assignment) {
        var time = assignment.hora_entrada
            ? String(assignment.hora_entrada).slice(0, 5)
                + (assignment.hora_salida ? " – " + String(assignment.hora_salida).slice(0, 5) : "")
            : "Horario sin salida indicada";
        openDialog('<div class="plan-dialog" role="dialog" aria-modal="true"><header><div><span>Asignación existente de Agenda</span><h2>'
            + escapeHtml(assignment.profesional) + '</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<div class="plan-readonly-detail"><dl><div><dt>Fecha</dt><dd>' + escapeHtml(assignment.fecha) + '</dd></div><div><dt>Consultorio</dt><dd>'
            + escapeHtml(assignment.consultorio) + '</dd></div><div><dt>Horario</dt><dd>' + escapeHtml(time)
            + '</dd></div><div><dt>Origen</dt><dd>Agenda / asignación legacy</dd></div></dl>'
            + '<div class="plan-form-info"><i class="fa-solid fa-shield-halved"></i><span>Se muestra para compatibilidad y no se modifica desde este módulo.</span></div></div>'
            + '<footer><button type="button" class="plan-button plan-button--primary" data-plan-dialog="close">Entendido</button></footer></div>');
    }

    function openAgendaOccupancy(date, roomId) {
        var occupancy = agendaOccupancyFor(date, roomId);
        var room = roomById(roomId) || {};
        var assignments = assignmentsFor(date, roomId);
        var professionals;
        var matchesPlanning;
        var conflictWithPlanning;
        var plannedAssignment;
        var plannedProfessional;
        var html;
        if (!occupancy) {
            notify("La ocupación de Agenda ya no está disponible. Actualizando la vista.", "info");
            loadData(false);
            return;
        }
        professionals = occupancy.profesionales || [];
        matchesPlanning = agendaMatchesAssignments(occupancy, assignments);
        conflictWithPlanning = assignments.length > 0
            && agendaOccupancyBlocksSlot(occupancy)
            && !matchesPlanning;
        if (matchesPlanning) {
            plannedAssignment = assignments[0];
            plannedProfessional = professionalById(plannedAssignment.cod_profesional)
                || plannedAssignment;
            professionals = [{
                cod_profesional: plannedAssignment.cod_profesional,
                nombre: plannedProfessional.nombre || plannedAssignment.profesional,
                avatar: plannedProfessional.avatar || plannedAssignment.avatar || "",
                cantidad_registros: occupancy.cantidad_registros || 0,
                hora_desde: occupancy.hora_desde,
                hora_hasta: occupancy.hora_hasta,
                estado_planificacion: plannedAssignment.estado
            }];
        }
        html = '<div class="plan-dialog plan-dialog--agenda" role="dialog" aria-modal="true" aria-labelledby="planAgendaTitle">'
            + '<header><div><span>Ocupación vinculada con Agenda</span><h2 id="planAgendaTitle">'
            + escapeHtml((room.etiqueta ? room.etiqueta + " · " : "") + (room.nombre || "Consultorio"))
            + '</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<div class="plan-agenda-detail"><div class="plan-agenda-detail__date"><i class="fa-regular fa-calendar-check"></i>'
            + '<span><small>Fecha</small><strong>' + escapeHtml(date) + '</strong></span></div>';
        if (matchesPlanning) {
            html += '<div class="plan-form-info"><i class="fa-solid fa-link"></i>'
                + '<span><strong>'
                + (plannedAssignment.origen === "agenda"
                    ? "Agenda identifica al profesional asignado."
                    : "Planificación y Agenda representan la misma ocupación.")
                + '</strong> El profesional se muestra una sola vez y Agenda aporta '
                + escapeHtml(agendaCountLabel(occupancy)) + '.</span></div>';
        } else if (professionals.length > 1 || conflictWithPlanning) {
            html += '<div class="plan-form-info plan-form-info--danger"><i class="fa-solid fa-triangle-exclamation"></i>'
                + '<span><strong>Conflicto de profesionales.</strong> Agenda identifica un profesional diferente del planificado para esta casilla.</span></div>';
        } else if (!professionals.length) {
            html += '<div class="plan-form-info"><i class="fa-regular fa-calendar"></i>'
                + '<span>Agenda registra actividad, pero no existe un profesional identificado. La casilla permanece libre en Planificación.</span></div>';
        }
        html += '<div class="plan-agenda-detail__list">';
        professionals.forEach(function (professional) {
            html += '<article style="--professional-color:' + escapeAttr(stableColor(professional.cod_profesional)) + '">'
                + avatarHtml(professional, "plan-avatar--detail")
                + '<span><strong>' + escapeHtml(professional.nombre)
                + '</strong><small>' + escapeHtml(professional.cantidad_registros || 0) + ' turno(s)'
                + (professional.hora_desde
                    ? " · " + escapeHtml(shortTimeRange(professional.hora_desde, professional.hora_hasta)) : "")
                + '</small></span><em>'
                + escapeHtml(professional.estado_planificacion
                    ? statusLabel(professional.estado_planificacion) : "Agenda")
                + '</em></article>';
        });
        html += '</div><div class="plan-form-info"><i class="fa-solid fa-lock"></i>'
            + '<span>La regla es <strong>un doctor por día y consultorio</strong>. Planificación define la ocupación y Agenda conserva sus turnos.</span></div></div>'
            + '<footer><button type="button" class="plan-button plan-button--primary" data-plan-dialog="close">Entendido</button></footer></div>';
        openDialog(html);
    }

    function openRemoteCommitmentDetails(date, professionalId) {
        var commitments = remoteCommitmentsFor(date, professionalId);
        var professional = professionalById(professionalId) || {};
        var blocksDay = remoteCommitmentBlocksDay(commitments);
        var html = "";
        if (!commitments.length) {
            notify("No se encontró el compromiso de otra sucursal.", "info");
            return;
        }
        html = '<div class="plan-dialog plan-dialog--remote" role="dialog" aria-modal="true">'
            + '<header><div><span>Disponibilidad multisucursal</span><h2>'
            + escapeHtml(professional.nombre || "Profesional") + '</h2></div>'
            + '<button type="button" data-plan-dialog="close" aria-label="Cerrar">'
            + '<i class="fa-solid fa-xmark"></i></button></header>'
            + '<div class="plan-remote-detail"><div class="plan-remote-detail__date">'
            + '<i class="fa-regular fa-calendar-xmark"></i><span><small>Fecha</small><strong>'
            + escapeHtml(String(date).split("-").reverse().join("/"))
            + '</strong></span></div><div class="plan-form-info'
            + (blocksDay ? " plan-form-info--warning" : "") + '">'
            + '<i class="fa-solid fa-building-columns"></i><span><strong>'
            + (blocksDay ? "Profesional no disponible para esta fecha."
                : "Existe un compromiso horario en otra sucursal.")
            + '</strong> Los consultorios de la sede actual continúan libres para otros profesionales.</span></div>'
            + '<div class="plan-remote-detail__list">';
        commitments.forEach(function (commitment) {
            var rooms = (commitment.consultorios || []).join(", ");
            var origins = (commitment.origenes || []).map(function (origin) {
                return origin === "agenda" ? "Agenda" : "Planificación";
            }).join(" y ");
            html += '<article><span class="plan-remote-detail__icon">'
                + '<i class="fa-solid fa-building-columns"></i></span><div><strong>'
                + escapeHtml(commitment.nombre_local || "Otra sucursal")
                + '</strong><small>' + escapeHtml(
                    (rooms ? rooms + " · " : "")
                    + remoteCommitmentTimeLabel([commitment])
                    + (origins ? " · " + origins : "")
                ) + '</small></div></article>';
        });
        html += '</div></div><footer><button type="button" class="plan-button plan-button--primary" '
            + 'data-plan-dialog="close">Entendido</button></footer></div>';
        openDialog(html);
    }

    function openDialog(html, context) {
        var layer = state.root.querySelector("#planDialogLayer");
        layer.innerHTML = '<div class="plan-dialog-backdrop" data-plan-dialog="close"></div>' + html;
        layer.hidden = false;
        layer._planContext = context || null;
        layer.addEventListener("click", onDialogClick, false);
        window.setTimeout(function () {
            var focusable = layer.querySelector("button,select,input,textarea");
            if (focusable) { focusable.focus(); }
        }, 0);
    }

    function closeDialog() {
        var layer = state.root && state.root.querySelector("#planDialogLayer");
        if (!layer || layer.hidden) { return; }
        layer.removeEventListener("click", onDialogClick, false);
        layer.hidden = true;
        layer.innerHTML = "";
        layer._planContext = null;
    }

    function onDialogClick(event) {
        var command = event.target.closest("[data-plan-dialog]");
        var layer = state.root.querySelector("#planDialogLayer");
        var context = layer._planContext || {};
        if (!command) { return; }
        switch (command.getAttribute("data-plan-dialog")) {
            case "close":
                closeDialog();
                break;
            case "cancel-assignment":
                cancelAssignment(context);
                break;
            case "cancel-occurrence":
                cancelOccurrence(context);
                break;
            case "cancel-rule":
                cancelRule(context);
                break;
            case "add-branch-professional":
                addProfessionalToBranch(command);
                break;
            case "confirm-remove-professional":
                removeProfessionalFromBranch(command, context);
                break;
        }
    }

    function bindAssignmentDialog() {
        var layer = state.root.querySelector("#planDialogLayer");
        var professional = layer.querySelector("#planFormProfessional");
        var date = layer.querySelector("#planFormDate");
        var schedule = layer.querySelector("#planFormSchedule");
        var recurrenceRadios = layer.querySelectorAll("input[name='tipo']");
        var recurrenceEnd = layer.querySelector("#planRecurrenceEnd");
        function updateSchedules() {
            if (schedule && professional && date) {
                schedule.innerHTML = scheduleOptions(professional.value, date.value, "");
            }
        }
        if (professional) { professional.addEventListener("change", updateSchedules, false); }
        if (date) { date.addEventListener("change", updateSchedules, false); }
        Array.prototype.forEach.call(recurrenceRadios, function (radio) {
            radio.addEventListener("change", function () {
                recurrenceEnd.hidden = this.value !== "recurrente" || !this.checked;
            }, false);
        });
    }

    function formValues(form) {
        var values = {};
        Array.prototype.forEach.call(form.elements, function (field) {
            if (!field.name || field.disabled || ((field.type === "radio" || field.type === "checkbox") && !field.checked)) {
                return;
            }
            values[field.name] = field.value;
        });
        return values;
    }

    function saveAssignmentForm(form) {
        var values = formValues(form);
        var button = form.querySelector("button[type='submit']");
        var action = values.tipo === "recurrente" ? "guardarRegla"
            : (values.id_asignacion ? "moverAsignacion" : "guardarAsignacion");
        if (!values.fecha || !values.cod_profesional || !values.id_consultorio) {
            notify("Complete profesional, fecha y consultorio.", "error");
            return;
        }
        values.cod_local = selectedLocal();
        if (action === "guardarRegla") {
            values.fecha_desde = values.fecha;
            values.dia_semana = (parseIso(values.fecha).getDay() || 7);
        }
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando…';
        request(action, values).then(function () {
            closeDialog();
            notify(action === "guardarRegla" ? "Recurrencia guardada." : "Asignación guardada.", "success");
            loadData(false);
        }).catch(function (error) {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-check"></i> Reintentar';
            notify(error.message, "error");
        });
    }

    function reasonFromOpenForm() {
        var field = state.root.querySelector("#planDialogLayer textarea[name='motivo']");
        return field ? field.value.trim() : "";
    }

    function cancelAssignment(context) {
        var reason = reasonFromOpenForm();
        if (!reason) {
            notify("Indique el motivo antes de anular.", "error");
            return;
        }
        if (!window.confirm("¿Confirma la anulación de esta asignación? El historial se conservará.")) { return; }
        request("anularAsignacion", {
            id_asignacion: context.id_asignacion,
            version_esperada: context.version,
            motivo: reason
        }).then(function () {
            closeDialog();
            notify("Asignación anulada y registrada en el historial.", "success");
            loadData(false);
        }).catch(function (error) { notify(error.message, "error"); });
    }

    function cancelOccurrence(context) {
        var reason = reasonFromOpenForm();
        if (!reason) {
            notify("Indique el motivo de la excepción.", "error");
            return;
        }
        request("anularOcurrencia", {
            id_regla: context.id_regla,
            fecha: context.fecha_original || context.fecha,
            motivo: reason
        }).then(function () {
            closeDialog();
            notify("La fecha fue excluida de la recurrencia.", "success");
            loadData(false);
        }).catch(function (error) { notify(error.message, "error"); });
    }

    function cancelRule(context) {
        var reason = reasonFromOpenForm();
        if (!reason) {
            notify("Indique el motivo para finalizar la serie.", "error");
            return;
        }
        if (!window.confirm("¿Finalizar toda la serie recurrente? Las fechas históricas se conservarán.")) { return; }
        request("anularRegla", { id_regla: context.id_regla, motivo: reason }).then(function () {
            closeDialog();
            notify("Recurrencia finalizada.", "success");
            loadData(false);
        }).catch(function (error) { notify(error.message, "error"); });
    }

    function saveSpecialty(form) {
        var values = formValues(form);
        var professional = professionalById(values.cod_profesional);
        values.cod_local = selectedLocal();
        request("guardarEspecialidad", values).then(function () {
            if (professional) { professional.especialidad = values.especialidad; }
            notify("Especialidad actualizada. El color estable no cambia.", "success");
            renderAll();
        }).catch(function (error) { notify(error.message, "error"); });
    }

    function renderProfessionalCandidates(query) {
        var layer = state.root.querySelector("#planDialogLayer");
        var container = layer ? layer.querySelector("#planProfessionalCandidates") : null;
        var context = layer ? layer._planContext : null;
        var items = context && context.items ? context.items : [];
        var normalized = String(query || "").toLowerCase().trim();
        var html = "";
        if (!container) { return; }
        items.filter(function (professional) {
            var haystack = String(professional.nombre || "") + " "
                + String(professional.especialidad || "") + " "
                + String(professional.nombre_local_base || "");
            return !normalized || haystack.toLowerCase().indexOf(normalized) !== -1;
        }).forEach(function (professional) {
            html += '<article class="plan-candidate-card" style="--professional-color:'
                + escapeAttr(stableColor(professional.cod_profesional)) + '">'
                + avatarHtml(professional)
                + '<span><strong>' + escapeHtml(professional.nombre) + '</strong><small>'
                + escapeHtml(professional.especialidad || "Especialidad no indicada")
                + '</small><em><i class="fa-solid fa-building-columns"></i> Base: '
                + escapeHtml(professional.nombre_local_base || "Sin sucursal indicada")
                + '</em></span><button type="button" data-plan-dialog="add-branch-professional" data-plan-candidate="'
                + escapeAttr(professional.cod_profesional) + '"><i class="fa-solid fa-plus"></i> Agregar</button></article>';
        });
        container.innerHTML = html || '<div class="plan-empty plan-empty--compact"><strong>Sin coincidencias</strong><p>No hay profesionales disponibles para agregar.</p></div>';
    }

    function openAddProfessionalDialog() {
        var context;
        var layer;
        var search;
        if (!canManageMultibranch()) {
            notify("Necesita permisos de gestión multisucursal.", "error");
            return;
        }
        if (!state.data.vinculos_instalados) {
            notify("La función requiere aplicar su migración controlada.", "info");
            return;
        }
        context = { type: "add-professional", items: [] };
        openDialog('<div class="plan-dialog plan-dialog--candidates" role="dialog" aria-modal="true"><header><div><span>Listado de la sucursal</span><h2>Agregar doctor/a</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<div class="plan-candidate-intro"><i class="fa-solid fa-building-circle-arrow-right"></i><span>Se agregará a <strong>'
            + escapeHtml(state.data.local_actual.nombre || "la sucursal seleccionada")
            + '</strong> únicamente para planificación. Su sucursal base y RR. HH. no cambiarán.</span></div>'
            + '<label class="plan-candidate-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="planCandidateSearch" placeholder="Buscar por nombre, especialidad o sucursal base"></label>'
            + '<div class="plan-candidate-list" id="planProfessionalCandidates"><div class="plan-loading plan-loading--small"><i class="fa-solid fa-spinner fa-spin"></i><strong>Buscando especialistas…</strong></div></div>'
            + '<footer><button type="button" class="plan-button plan-button--primary" data-plan-dialog="close">Cerrar</button></footer></div>', context);
        layer = state.root.querySelector("#planDialogLayer");
        search = layer.querySelector("#planCandidateSearch");
        if (search) {
            search.addEventListener("input", function () {
                renderProfessionalCandidates(this.value);
            }, false);
        }
        request("listarCandidatosSucursal", { cod_local: selectedLocal() }).then(function (data) {
            if (!layer || layer.hidden || layer._planContext !== context) { return; }
            context.items = data.items || [];
            renderProfessionalCandidates(search ? search.value : "");
        }).catch(function (error) {
            var container = layer && layer.querySelector("#planProfessionalCandidates");
            if (container) {
                container.innerHTML = '<div class="plan-empty plan-empty--error"><strong>No se pudo cargar el listado</strong><p>'
                    + escapeHtml(error.message) + '</p></div>';
            }
        });
    }

    function addProfessionalToBranch(command) {
        var professionalId = command.getAttribute("data-plan-candidate");
        command.disabled = true;
        command.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Agregando…';
        request("agregarProfesionalSucursal", {
            cod_local: selectedLocal(),
            cod_profesional: professionalId
        }).then(function () {
            state.selectedProfessional = String(professionalId);
            closeDialog();
            notify("Profesional agregado al listado de la sucursal.", "success");
            loadData(false);
        }).catch(function (error) {
            command.disabled = false;
            command.innerHTML = '<i class="fa-solid fa-plus"></i> Reintentar';
            notify(error.message, "error");
        });
    }

    function openRemoveProfessionalDialog(professionalId) {
        var professional = professionalById(professionalId);
        if (!professional || professional.origen_listado !== "vinculo") {
            notify("El profesional no pertenece al listado agregado.", "error");
            return;
        }
        openDialog('<div class="plan-dialog plan-dialog--remove-professional" role="dialog" aria-modal="true"><header><div><span>Listado de la sucursal</span><h2>Quitar doctor/a</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<div class="plan-dialog-summary"><span style="--professional-color:'
            + escapeAttr(stableColor(professional.cod_profesional)) + '">'
            + avatarHtml(professional) + '</span><div><small>Profesional seleccionado</small><strong>'
            + escapeHtml(professional.nombre) + '</strong><span>Base: '
            + escapeHtml(professional.nombre_local_base || "Otra sucursal") + '</span></div></div>'
            + '<div class="plan-form-info"><i class="fa-solid fa-shield-halved"></i><span>Las asignaciones existentes y todo el historial se conservarán. Sólo dejará de aparecer para nuevas planificaciones.</span></div>'
            + '<label class="plan-form-reason">Motivo<textarea id="planRemoveProfessionalReason" maxlength="255" placeholder="Explique brevemente por qué se retira del listado"></textarea></label>'
            + '<footer><button type="button" class="plan-button plan-button--ghost" data-plan-dialog="close">Cancelar</button><button type="button" class="plan-button plan-button--danger" data-plan-dialog="confirm-remove-professional">Quitar del listado</button></footer></div>', {
                type: "remove-professional",
                professional: professional
            });
    }

    function removeProfessionalFromBranch(command, context) {
        var reason = state.root.querySelector("#planRemoveProfessionalReason");
        var value = reason ? reason.value.trim() : "";
        if (!value) {
            notify("Indique el motivo para conservar la trazabilidad.", "error");
            if (reason) { reason.focus(); }
            return;
        }
        command.disabled = true;
        command.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Quitando…';
        request("quitarProfesionalSucursal", {
            cod_local: selectedLocal(),
            cod_profesional: context.professional.cod_profesional,
            motivo: value
        }).then(function () {
            if (String(state.selectedProfessional) === String(context.professional.cod_profesional)) {
                state.selectedProfessional = "";
            }
            closeDialog();
            notify("Profesional retirado. Sus asignaciones existentes se conservaron.", "success");
            loadData(false);
        }).catch(function (error) {
            command.disabled = false;
            command.textContent = "Reintentar";
            notify(error.message, "error");
        });
    }

    function openHelp() {
        openDialog('<div class="plan-dialog plan-dialog--help" role="dialog" aria-modal="true"><header><div><span>Ayuda breve</span><h2>Cómo usar la planificación</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<ol><li><strong>Selecciona un profesional.</strong><span>Su color e hilo permanecen estables. Los avisos naranja muestran los días u horarios ya comprometidos en otra sucursal.</span></li>'
            + '<li><strong>Elige el período.</strong><span>Semanal muestra una semana; mensual agrupa el mes en filas semanales.</span></li>'
            + '<li><strong>Asigna el consultorio.</strong><span>Arrastra el profesional a una casilla libre para fijarlo directamente al día y consultorio. Tocar una casilla libre abre las opciones avanzadas.</span></li>'
            + '<li><strong>Revisa ocupaciones.</strong><span>Planificación y Agenda muestran una sola tarjeta cuando representan al mismo doctor. Si Agenda es la única fuente e identifica al profesional, se muestra como “Asignada · Agenda” con su avatar e hilo, sin crear otra asignación. Los turnos sin profesional no reemplazan la leyenda “Libre”.</span></li>'
            + '<li><strong>Evita conflictos.</strong><span>Cada consultorio admite un solo doctor por día; el servidor valida nuevamente antes de guardar y conserva la trazabilidad.</span></li></ol>'
            + '<div class="plan-form-info"><i class="fa-solid fa-gear"></i><span>Filtros, hilos, leyenda, especialidad e historial están dentro del engranaje junto a la campanita.</span></div>'
            + '<footer><button type="button" class="plan-button plan-button--primary" data-plan-dialog="close">Entendido</button></footer></div>');
    }

    function openHistory() {
        openDialog('<div class="plan-dialog" role="dialog" aria-modal="true"><header><div><span>Trazabilidad</span><h2>Historial de planificación</h2></div><button type="button" data-plan-dialog="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header>'
            + '<div class="plan-history" id="planHistory"><div class="plan-loading plan-loading--small"><i class="fa-solid fa-spinner fa-spin"></i><strong>Cargando historial…</strong></div></div>'
            + '<footer><button type="button" class="plan-button plan-button--primary" data-plan-dialog="close">Cerrar</button></footer></div>');
        request("listarHistorial", { cod_local: selectedLocal() }).then(function (data) {
            var container = state.root.querySelector("#planHistory");
            var html = "";
            (data.items || []).forEach(function (item) {
                html += '<article><i class="fa-solid fa-clock-rotate-left"></i><div><strong>'
                    + escapeHtml(item.accion.replace(/_/g, " ")) + '</strong><span>'
                    + escapeHtml(item.entidad + " #" + item.id_entidad) + '</span><small>'
                    + escapeHtml(item.actor + " · " + item.fecha)
                    + (item.motivo ? " · " + escapeHtml(item.motivo) : "") + '</small></div></article>';
            });
            container.innerHTML = html || '<div class="plan-empty plan-empty--compact"><strong>Sin cambios registrados</strong><p>El historial aparecerá aquí después de la primera operación.</p></div>';
        }).catch(function (error) {
            var container = state.root.querySelector("#planHistory");
            if (container) { container.innerHTML = '<div class="plan-empty plan-empty--error"><p>' + escapeHtml(error.message) + '</p></div>'; }
        });
    }

    function renderMultibranchThreads(svg, body, bodyRect) {
        var professionalId = String(state.selectedProfessional || "");
        var color = stableColor(professionalId);
        var weeks = state.root.querySelectorAll("[data-plan-route-week]");
        var source = state.root.querySelector('[data-plan-professional="' + professionalId + '"] .plan-professional-card__node');
        var firstTarget = state.root.querySelector("[data-plan-route-assignment]");
        var paths = "";
        var i;

        function pointFor(element) {
            var rect = element.getBoundingClientRect();
            return {
                x: rect.left - bodyRect.left + body.scrollLeft + rect.width / 2,
                y: rect.top - bodyRect.top + body.scrollTop + rect.height / 2
            };
        }

        function routePath(from, to, status, extraClass) {
            var distance = Math.max(26, Math.abs(to.x - from.x) * 0.34);
            var dash = status === "propuesta" ? ' stroke-dasharray="2 5"'
                : (status === "pendiente_horario" ? ' stroke-dasharray="7 5"' : "");
            if (Math.abs(to.x - from.x) < 12) {
                return '<path class="plan-route-thread ' + (extraClass || "") + '" d="M '
                    + from.x + " " + from.y + " L " + to.x + " " + to.y + '" stroke="'
                    + escapeAttr(color) + '"' + dash + '></path>';
            }
            return '<path class="plan-route-thread ' + (extraClass || "") + '" d="M '
                + from.x + " " + from.y + " C " + (from.x + distance) + " " + from.y + ", "
                + (to.x - distance) + " " + to.y + ", " + to.x + " " + to.y + '" stroke="'
                + escapeAttr(color) + '"' + dash + '></path>';
        }

        if (source && firstTarget) {
            var sourcePoint = pointFor(source);
            var targetRect = firstTarget.getBoundingClientRect();
            var targetPoint = {
                x: targetRect.left - bodyRect.left + body.scrollLeft + 2,
                y: targetRect.top - bodyRect.top + body.scrollTop + targetRect.height / 2
            };
            paths += routePath(sourcePoint, targetPoint, firstTarget.getAttribute("data-plan-route-status"), "plan-route-thread--entry");
        }

        for (i = 0; i < weeks.length; i++) {
            var assignments = weeks[i].querySelectorAll("[data-plan-route-assignment]");
            for (var j = 1; j < assignments.length; j++) {
                var previous = assignments[j - 1];
                var current = assignments[j];
                var from = pointFor(previous);
                var to = pointFor(current);
                var changedBranch = previous.getAttribute("data-plan-route-local")
                    !== current.getAttribute("data-plan-route-local");
                paths += routePath(from, to, current.getAttribute("data-plan-route-status"), "");
                if (changedBranch) {
                    var middleX = (from.x + to.x) / 2;
                    var middleY = (from.y + to.y) / 2;
                    var branchColor = stableBranchColor(current.getAttribute("data-plan-route-local"));
                    var sigla = String(current.getAttribute("data-plan-route-sigla") || "").slice(0, 3);
                    paths += '<g class="plan-route-thread-node"><rect x="' + (middleX - 7)
                        + '" y="' + (middleY - 7) + '" width="14" height="14" rx="2" transform="rotate(45 '
                        + middleX + " " + middleY + ')" fill="' + escapeAttr(branchColor)
                        + '"></rect><text x="' + middleX + '" y="' + (middleY + 2.5)
                        + '" text-anchor="middle">' + escapeHtml(sigla) + '</text></g>';
                }
            }
        }
        svg.innerHTML = paths;
    }

    function renderThreads() {
        var svg;
        var body;
        var bodyRect;
        var professionals;
        var assignments;
        var width;
        var height;
        var paths = "";
        if (!state.root || !state.open) { return; }
        svg = state.root.querySelector("#planThreads");
        body = state.root.querySelector("#planBody");
        if (!svg || !body || state.filters.threads === "off" || window.innerWidth < 760) {
            if (svg) { svg.innerHTML = ""; }
            return;
        }
        bodyRect = body.getBoundingClientRect();
        width = Math.max(body.scrollWidth, body.clientWidth);
        height = Math.max(body.scrollHeight, body.clientHeight);
        svg.setAttribute("viewBox", "0 0 " + width + " " + height);
        svg.setAttribute("width", width);
        svg.setAttribute("height", height);
        if (state.scope === "multi") {
            renderMultibranchThreads(svg, body, bodyRect);
            return;
        }
        professionals = state.filters.threads === "all"
            ? filteredProfessionals().map(function (p) { return String(p.cod_profesional); })
            : [String(state.selectedProfessional)];
        assignments = filteredAssignments();
        professionals.forEach(function (professionalId) {
            var source = state.root.querySelector('[data-plan-professional="' + professionalId + '"] .plan-professional-card__node');
            var sourceRect;
            var x1;
            var y1;
            var color;
            if (!source) { return; }
            sourceRect = source.getBoundingClientRect();
            x1 = sourceRect.left - bodyRect.left + body.scrollLeft + sourceRect.width / 2;
            y1 = sourceRect.top - bodyRect.top + body.scrollTop + sourceRect.height / 2;
            color = stableColor(professionalId);
            assignments.forEach(function (assignment) {
                var target;
                var targetRect;
                var x2;
                var y2;
                var mid;
                if (String(assignment.cod_profesional) !== professionalId) { return; }
                target = state.root.querySelector('[data-plan-assignment="' + assignment.clave + '"]');
                if (!target) { return; }
                targetRect = target.getBoundingClientRect();
                x2 = targetRect.left - bodyRect.left + body.scrollLeft + 3;
                y2 = targetRect.top - bodyRect.top + body.scrollTop + targetRect.height / 2;
                mid = Math.max(34, Math.min(100, (x2 - x1) * 0.42));
                paths += '<path d="M ' + x1 + " " + y1 + " C " + (x1 + mid) + " " + y1 + ", "
                    + (x2 - mid) + " " + y2 + ", " + x2 + " " + y2 + '" stroke="'
                    + escapeAttr(color) + '"></path>';
            });
            Array.prototype.forEach.call(
                state.root.querySelectorAll(
                    '[data-plan-remote-professional="' + professionalId + '"]'
                ),
                function (target) {
                    var targetRect = target.getBoundingClientRect();
                    var x2 = targetRect.left - bodyRect.left + body.scrollLeft + 3;
                    var y2 = targetRect.top - bodyRect.top + body.scrollTop
                        + targetRect.height / 2;
                    var mid = Math.max(34, Math.min(100, (x2 - x1) * 0.42));
                    paths += '<path class="plan-remote-thread" d="M ' + x1 + " " + y1
                        + " C " + (x1 + mid) + " " + y1 + ", "
                        + (x2 - mid) + " " + y2 + ", " + x2 + " " + y2
                        + '" stroke="' + escapeAttr(color) + '"></path>';
                }
            );
        });
        svg.innerHTML = paths;
    }

    window.abrirPlanificacionEspecialistas = function (opciones) {
        var container = document.getElementById("divPlanificacionEspecialistas");
        var savedView;
        opciones = opciones || {};
        if (!container || !ensureRoot()) { return; }
        if (opciones.fecha && /^\d{4}-\d{2}-\d{2}$/.test(String(opciones.fecha))) {
            state.date = parseIso(opciones.fecha);
        }
        if (opciones.cod_local) {
            state.requestedLocal = String(opciones.cod_local);
        }
        container.style.display = "";
        state.open = true;
        if (opciones.vista === "week" || opciones.vista === "month") {
            state.view = opciones.vista;
            if (opciones.vista === "week") {
                state.scope = "branch";
            }
        } else {
            try { savedView = window.localStorage.getItem("telarPlanificacionVista"); } catch (ignore) {}
            state.view = savedView === "week" ? "week" : "month";
        }
        loadData(true);
    };

    window.abrirPlanificacionEspecialistasDesdeAgenda = function (fecha, codLocal) {
        window.abrirPlanificacionEspecialistas({
            fecha: fecha,
            cod_local: codLocal,
            vista: "week"
        });
    };

    window.cerrarPlanificacionEspecialistas = function () {
        var container = document.getElementById("divPlanificacionEspecialistas");
        if (container) { container.style.display = "none"; }
        state.open = false;
        closeDialog();
    };

    window.minimizarPlanificacionEspecialistas = function () {
        window.cerrarPlanificacionEspecialistas();
        var marker = document.getElementById("divMinimizadoPlanificacionEspecialistas");
        if (marker) { marker.style.display = ""; }
    };

    window.addEventListener("resize", function () {
        window.clearTimeout(state.resizeTimer);
        state.resizeTimer = window.setTimeout(renderThreads, 120);
    }, false);

    document.addEventListener("scroll", function () {
        if (state.open) {
            window.clearTimeout(state.resizeTimer);
            state.resizeTimer = window.setTimeout(renderThreads, 60);
        }
    }, true);
})(window, document);

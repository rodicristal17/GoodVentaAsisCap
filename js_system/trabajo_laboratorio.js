/*
 * Trabajo de laboratorio dental - Sistema Telar
 *
 * CONTRATO JSON COORDINADO
 * -------------------------
 * Endpoint: /GoodVentaAsisCap/php_system/abmTrabajoLaboratorio.php
 * Método: POST multipart/form-data. El comando se envía siempre en `accion`.
 * Respuesta base: { ok: Boolean, codigo: String, mensaje: String,
 *                   datos: Object, version: Number|String }.
 *
 * Acciones de negocio consumidas:
 *   listarTrabajos, obtenerTrabajo, obtenerResumen, obtenerCatalogos,
 *   obtenerContextoDetalle, iniciarTrabajo, iniciarTransferencia,
 *   confirmarRecepcion, agregarEvidencia, agregarNota, iniciarDevolucion,
 *   confirmarDevolucion, solicitarAjuste, aprobarTrabajo,
 *   registrarInstalacion y cancelarTrabajo.
 *
 * Reglas del contrato frontend:
 * - Las acciones disponibles se dibujan exclusivamente desde
 *   `datos.acciones_permitidas`; el cliente nunca calcula una transición.
 * - Cada comando mutante lleva `version_esperada` y `clave_idempotencia`.
 * - Los archivos de avance se envían como `evidencias[]`. La evidencia inicial
 *   usa `evidencia_inicial` JSON {data_base64,nombre_archivo,descripcion}.
 * - El servidor deriva actor, permisos, sucursal y custodio desde la sesión.
 * - El listado puede devolver `datos.trabajos|items`, paginación y contexto.
 * - El detalle puede devolver `datos.trabajo`, `eventos`, `evidencias`,
 *   `notas`, `auditoria`, `acciones_permitidas` y catálogos acotados.
 * - Se tolera temporalmente el envoltorio legacy de éxito al leer respuestas,
 *   pero las solicitudes nuevas no envían `funt` ni estados manuales.
 */

(function (window, document) {
  "use strict";

  var ENDPOINT = "/GoodVentaAsisCap/php_system/abmTrabajoLaboratorio.php";
  var STYLE_URL = "/GoodVentaAsisCap/css_system/trabajo_laboratorio.css?v=20260721-3";
  var BRAND_MARK = "/GoodVentaAsisCap/iconos/telar-loader.svg?v=20260721-2";
  var ROOT_ID = "telarTrabajoLaboratorio";
  var PAGE_SIZE = 18;
  var MAX_FILES = 5;
  var MAX_FILE_SIZE = 10 * 1024 * 1024;
  var IMAGE_TYPES = { "image/jpeg": true, "image/png": true, "image/webp": true };

  var GROUPS = [
    { key: "pendientes_entrega", label: "Pendientes de entrega", icon: "fa-box-open" },
    { key: "en_laboratorio", label: "En laboratorio", icon: "fa-microscope" },
    { key: "pendientes_revision", label: "Pendientes de revisión", icon: "fa-user-doctor" },
    { key: "finalizados", label: "Finalizados", icon: "fa-circle-check" }
  ];

  var MECHANIC_TRAY = [
    { key: "por_recibir", label: "Por recibir" },
    { key: "en_mi_poder", label: "En mi poder" },
    { key: "ajuste_solicitado", label: "Con ajuste" },
    { key: "listos_entregar", label: "Acción: entregar" },
    { key: "finalizados", label: "Finalizados" }
  ];

  var ACTIONS = {
    iniciarTrabajo: {
      label: "Iniciar trabajo",
      icon: "fa-play",
      evidence: true,
      requiere_evidencia: true,
      requiere_mecanico: true,
      confirmation: "Confirmo que los datos corresponden al tratamiento y que la evidencia inicial es correcta."
    },
    iniciarTransferencia: {
      label: "Entregar al mecánico dental",
      icon: "fa-arrow-right-arrow-left",
      recipient: true,
      evidence: true,
      note: true,
      confirmation: "Confirmo la entrega física y el destinatario indicado."
    },
    confirmarRecepcion: {
      label: "Confirmar recepción",
      icon: "fa-hand-holding-medical",
      note: true,
      confirmation: "Confirmo que recibí físicamente este trabajo."
    },
    agregarEvidencia: {
      label: "Agregar fotos",
      icon: "fa-camera",
      evidence: true,
      note: true,
      confirmation: "Confirmo que las imágenes corresponden a este trabajo."
    },
    agregarNota: {
      label: "Agregar observación",
      icon: "fa-message",
      note: true,
      noteRequired: true,
      confirmation: "Confirmo que la observación es correcta."
    },
    iniciarDevolucion: {
      label: "Entregar trabajo realizado",
      icon: "fa-truck-arrow-right",
      recipient: true,
      evidence: true,
      note: true,
      confirmation: "Confirmo la entrega del trabajo terminado y su evidencia."
    },
    confirmarDevolucion: {
      label: "Confirmar recepción en clínica",
      icon: "fa-building-circle-check",
      note: true,
      confirmation: "Confirmo que la clínica recibió físicamente el trabajo."
    },
    solicitarAjuste: {
      label: "Solicitar ajuste",
      icon: "fa-screwdriver-wrench",
      reason: true,
      justification: true,
      evidenceOptional: true,
      confirmation: "Confirmo el motivo y la justificación del ajuste."
    },
    aprobarTrabajo: {
      label: "Aprobar trabajo",
      icon: "fa-circle-check",
      note: true,
      confirmation: "Confirmo la aprobación clínica del trabajo."
    },
    registrarInstalacion: {
      label: "Registrar instalación",
      icon: "fa-tooth",
      note: true,
      confirmation: "Confirmo que el trabajo fue instalado desde la evolución clínica correspondiente."
    },
    cancelarTrabajo: {
      label: "Cancelar trabajo",
      icon: "fa-ban",
      justification: true,
      danger: true,
      confirmation: "Confirmo la cancelación. El historial anterior se conservará."
    }
  };

  var ACTION_ALIASES = {
    iniciar_trabajo: "iniciarTrabajo",
    start_lab_work: "iniciarTrabajo",
    iniciar_transferencia: "iniciarTransferencia",
    entregar: "iniciarTransferencia",
    confirmar_recepcion: "confirmarRecepcion",
    add_evidence: "agregarEvidencia",
    agregar_evidencia: "agregarEvidencia",
    add_note: "agregarNota",
    agregar_nota: "agregarNota",
    iniciar_devolucion: "iniciarDevolucion",
    devolver: "iniciarDevolucion",
    confirmar_devolucion: "confirmarDevolucion",
    solicitar_ajuste: "solicitarAjuste",
    request_adjustment: "solicitarAjuste",
    aprobar: "aprobarTrabajo",
    aprobar_trabajo: "aprobarTrabajo",
    registrar_instalacion: "registrarInstalacion",
    cancelar: "cancelarTrabajo",
    cancelar_trabajo: "cancelarTrabajo"
  };

  var state = {
    root: null,
    open: false,
    loadingList: false,
    listRequest: 0,
    page: 1,
    hasMore: false,
    works: [],
    group: "pendientes_entrega",
    view: "operativa",
    mechanicTray: "por_recibir",
    summary: {},
    catalogs: {},
    context: {},
    detail: null,
    detailEnvelope: null,
    detailId: "",
    detailTab: "timeline",
    action: null,
    focusBeforeLayer: null,
    searchTimer: null,
    startContext: null,
    moduleOptions: {},
    objectUrls: []
  };

  function toStringSafe(value) {
    return value === null || typeof value === "undefined" ? "" : String(value);
  }

  function escapeHtml(value) {
    return toStringSafe(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, "&#96;");
  }

  function asArray(value) {
    if (Array.isArray(value)) { return value; }
    if (value && typeof value === "object") {
      return Object.keys(value).map(function (key) { return value[key]; });
    }
    return [];
  }

  function pick(source, keys, fallback) {
    var i;
    source = source || {};
    for (i = 0; i < keys.length; i += 1) {
      if (source[keys[i]] !== null && typeof source[keys[i]] !== "undefined" && source[keys[i]] !== "") {
        return source[keys[i]];
      }
    }
    return typeof fallback === "undefined" ? "" : fallback;
  }

  function numberValue(value, fallback) {
    var parsed = Number(value);
    return isFinite(parsed) ? parsed : (typeof fallback === "undefined" ? 0 : fallback);
  }

  function boolValue(value) {
    return value === true || value === 1 || value === "1" || value === "true" || value === "SI" || value === "si" || value === "sí";
  }

  function formatDate(value, includeTime) {
    var raw = toStringSafe(value).trim();
    var parsed;
    var options;
    if (!raw) { return "Sin registrar"; }
    /* Una fecha sin hora no representa UTC: se crea en horario local para
       evitar que Paraguay la muestre como el día anterior. */
    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
      parsed = new Date(
        Number(raw.substring(0, 4)),
        Number(raw.substring(5, 7)) - 1,
        Number(raw.substring(8, 10))
      );
    } else {
      raw = raw.replace(" ", "T");
      parsed = new Date(raw);
    }
    if (isNaN(parsed.getTime())) { return toStringSafe(value); }
    options = { day: "2-digit", month: "short", year: "numeric" };
    if (includeTime) {
      options.hour = "2-digit";
      options.minute = "2-digit";
    }
    try {
      return parsed.toLocaleString("es-PY", options);
    } catch (ignore) {
      return toStringSafe(value);
    }
  }

  function formatDays(value) {
    var amount = numberValue(value, 0);
    return amount + (amount === 1 ? " día" : " días");
  }

  function initials(name) {
    var parts = toStringSafe(name).trim().split(/\s+/).filter(Boolean);
    if (!parts.length) { return "?"; }
    return (parts[0].charAt(0) + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : "")).toUpperCase();
  }

  function person(value, roleFallback) {
    var data;
    if (value && typeof value === "object") {
      data = value;
      return {
        name: pick(data, ["nombre", "nombre_completo", "name", "persona"], "Sin asignar"),
        role: pick(data, ["rol", "perfil", "tipo", "role"], roleFallback || "Responsable"),
        avatar: pick(data, ["avatar_url", "avatar", "foto", "imagen"], "")
      };
    }
    return { name: toStringSafe(value) || "Sin asignar", role: roleFallback || "Responsable", avatar: "" };
  }

  function avatarHtml(data, extraClass) {
    var item = person(data);
    return '<span class="tlab-avatar ' + escapeAttr(extraClass || "") + '" aria-hidden="true">'
      + (item.avatar ? '<img src="' + escapeAttr(item.avatar) + '" alt="">' : escapeHtml(initials(item.name)))
      + '</span>';
  }

  function personHtml(data, fallbackRole) {
    var item = person(data, fallbackRole);
    return '<span class="tlab-person">' + avatarHtml(item)
      + '<span class="tlab-person__copy"><small>' + escapeHtml(item.role) + '</small><strong title="' + escapeAttr(item.name) + '">' + escapeHtml(item.name) + '</strong></span></span>';
  }

  function makeIdempotencyKey() {
    if (window.crypto && typeof window.crypto.randomUUID === "function") {
      return window.crypto.randomUUID();
    }
    return "tlab-" + Date.now() + "-" + Math.random().toString(36).slice(2) + "-" + Math.random().toString(36).slice(2);
  }

  function ensureStyle() {
    var link;
    if (document.getElementById("telarTrabajoLaboratorioCss")) { return; }
    link = document.createElement("link");
    link.id = "telarTrabajoLaboratorioCss";
    link.rel = "stylesheet";
    link.href = STYLE_URL;
    document.head.appendChild(link);
  }

  function loaderHtml(label, variant) {
    if (window.TelarLoader && typeof window.TelarLoader.html === "function") {
      return window.TelarLoader.html(label || "Cargando...", variant || "content");
    }
    return '<div class="tlab-fallback-loader" role="status" aria-live="polite">'
      + '<img src="' + escapeAttr(BRAND_MARK) + '" alt="" aria-hidden="true">'
      + '<span>' + escapeHtml(label || "Cargando...") + '</span></div>';
  }

  function notify(message, type) {
    var region;
    var toast;
    var icon;
    if (!state.root) { return; }
    region = state.root.querySelector("#tlabLiveRegion");
    if (!region) { return; }
    type = type || "info";
    icon = type === "error" ? "fa-triangle-exclamation" : (type === "success" ? "fa-circle-check" : "fa-circle-info");
    toast = document.createElement("div");
    toast.className = "tlab-toast tlab-toast--" + type;
    toast.setAttribute("role", type === "error" ? "alert" : "status");
    toast.innerHTML = '<i class="fa-solid ' + icon + '" aria-hidden="true"></i><span>' + escapeHtml(message) + '</span>';
    region.innerHTML = "";
    region.appendChild(toast);
    window.setTimeout(function () {
      if (toast.parentNode) { toast.parentNode.removeChild(toast); }
    }, 5200);
  }

  function normalizeResponse(raw) {
    var parsed = raw;
    var ok;
    var data;
    if (typeof parsed === "string") {
      try {
        parsed = JSON.parse(parsed.replace(/^\uFEFF/, ""));
      } catch (error) {
        throw new Error("El servidor devolvió una respuesta que no se pudo interpretar.");
      }
    }
    parsed = parsed || {};
    ok = parsed.ok === true || parsed.ok === 1 || parsed.ok === "1" || parsed.exito === true || parsed.estado === "exito" || parsed["1"] === "exito";
    data = parsed.datos || parsed.data || {};
    if (!Object.keys(data).length && parsed["2"] && typeof parsed["2"] === "object") {
      data = parsed["2"];
    }
    return {
      ok: ok,
      code: toStringSafe(parsed.codigo || parsed.code || parsed["1"]),
      message: toStringSafe(parsed.mensaje || parsed.message || parsed.error || ""),
      data: data,
      version: parsed.version || data.version || "",
      raw: parsed
    };
  }

  function appendPayload(formData, payload) {
    Object.keys(payload || {}).forEach(function (key) {
      var value = payload[key];
      if (value === null || typeof value === "undefined" || value === "") { return; }
      if (typeof value === "object" && !(value instanceof Blob)) {
        formData.append(key, JSON.stringify(value));
      } else {
        formData.append(key, value === true ? "1" : (value === false ? "0" : value));
      }
    });
  }

  /* Safari/iOS antiguos no implementan FormData.forEach. Los formularios de
     este modulo usan nombres unicos, por lo que recorrer sus controles mantiene
     el mismo contrato sin depender de esa API. */
  function forEachFormValue(form, callback) {
    if (!form || typeof callback !== "function") { return; }
    Array.prototype.forEach.call(form.elements || [], function (field) {
      var type;
      if (!field || !field.name || field.disabled) { return; }
      type = toStringSafe(field.type).toLowerCase();
      if (type === "file" || ((type === "checkbox" || type === "radio") && !field.checked)) { return; }
      if (field.tagName && field.tagName.toLowerCase() === "select" && field.multiple) {
        Array.prototype.forEach.call(field.options || [], function (option) {
          if (option.selected) { callback(option.value, field.name); }
        });
        return;
      }
      callback(field.value, field.name);
    });
  }

  function appendLegacyCredentials(formData) {
    try {
      if (typeof window.obtener_datos_user === "function") { window.obtener_datos_user(); }
    } catch (ignore) {}
    if (typeof window.userid !== "undefined" && window.userid !== "") { formData.append("useru", window.userid); }
    if (typeof window.passuser !== "undefined" && window.passuser !== "") { formData.append("passu", window.passuser); }
    if (typeof window.navegador !== "undefined" && window.navegador !== "") { formData.append("navegador", window.navegador); }
  }

  function request(action, payload, files, onProgress) {
    return new Promise(function (resolve, reject) {
      var formData = new FormData();
      var xhr = new XMLHttpRequest();
      formData.append("accion", action);
      appendLegacyCredentials(formData);
      appendPayload(formData, payload || {});
      asArray(files).forEach(function (file) {
        if (file instanceof Blob) { formData.append("evidencias[]", file, file.name || "evidencia.jpg"); }
      });
      xhr.open("POST", ENDPOINT, true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
      xhr.timeout = 60000;
      if (xhr.upload && typeof onProgress === "function") {
        xhr.upload.addEventListener("progress", function (event) {
          if (event.lengthComputable) { onProgress(Math.round((event.loaded * 100) / event.total)); }
        });
      }
      xhr.onreadystatechange = function () {
        var response;
        var error;
        if (xhr.readyState !== 4) { return; }
        try {
          response = xhr.responseText ? normalizeResponse(xhr.responseText) : null;
        } catch (parseError) {
          if (xhr.status >= 200 && xhr.status < 300) {
            reject(parseError);
            return;
          }
          response = null;
        }
        if (xhr.status < 200 || xhr.status >= 300) {
          error = new Error(response && response.message
            ? response.message
            : (xhr.status === 401 || xhr.status === 403
              ? "La sesión no tiene autorización para esta operación."
              : "No se pudo comunicar con el servidor."));
          error.status = xhr.status;
          error.code = response ? response.code : "";
          error.response = response;
          reject(error);
          return;
        }
        if (!response.ok) {
          error = new Error(response.message || "La operación no pudo completarse.");
          error.code = response.code;
          error.response = response;
          reject(error);
          return;
        }
        resolve(response);
      };
      xhr.onerror = function () { reject(new Error("Se perdió la conexión. Puede reintentar sin duplicar la acción.")); };
      xhr.ontimeout = function () { reject(new Error("La operación tardó demasiado. Puede reintentar de forma segura.")); };
      xhr.send(formData);
    });
  }

  function rootTemplate() {
    return ''
      + '<div class="tlab-app" role="application" aria-label="Trabajos de laboratorio dental">'
      + '  <header class="tlab-topbar">'
      + '    <div class="tlab-brand"><span class="tlab-brand__mark"><img src="' + escapeAttr(BRAND_MARK) + '" alt=""></span>'
      + '      <span class="tlab-brand__copy"><span class="tlab-brand__eyebrow">Sistema Telar · Clinident Salud</span><h1>Trabajos de laboratorio dental</h1></span></div>'
      + '    <div class="tlab-topbar__actions"><span class="tlab-role-badge" id="tlabRoleBadge"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span>Acceso autorizado</span></span>'
      + '      <button type="button" class="tlab-icon-button" data-tlab-command="refresh" aria-label="Actualizar trabajos" title="Actualizar"><i class="fa-solid fa-rotate" aria-hidden="true"></i></button>'
      + '      <button type="button" class="tlab-icon-button" data-tlab-command="close" aria-label="Cerrar módulo" title="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>'
      + '  </header>'
      + '  <main class="tlab-content">'
      + '    <section class="tlab-summary" id="tlabSummary" aria-label="Resumen operativo">' + loaderHtml("Preparando resumen...", "compact") + '</section>'
      + '    <section class="tlab-toolbar" aria-label="Búsqueda y filtros">'
      + '      <div class="tlab-toolbar__main"><div class="tlab-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><label class="sr-only" for="tlabSearch">Buscar trabajos</label><input id="tlabSearch" type="search" autocomplete="off" placeholder="Venta, código del trabajo, paciente o producto"></div>'
      + '        <button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="toggle-filters" aria-expanded="false" aria-controls="tlabFilters"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Filtros <span class="tlab-filter-count" id="tlabFilterCount" hidden>0</span></button>'
      + '      </div>'
      + '      <form class="tlab-filters" id="tlabFilters" hidden><div class="tlab-filters__grid">'
      + '        <div class="tlab-field"><label for="tlabFilterSituation">Situación calculada</label><select id="tlabFilterSituation" name="situacion"><option value="">Todas</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterBranch">Sucursal</label><select id="tlabFilterBranch" name="cod_local"><option value="">Todas las autorizadas</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterMechanic">Mecánico dental</label><select id="tlabFilterMechanic" name="cod_mecanico"><option value="">Todos</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterDoctor">Doctor</label><select id="tlabFilterDoctor" name="cod_doctor"><option value="">Todos</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterProduct">Producto</label><select id="tlabFilterProduct" name="cod_producto"><option value="">Todos</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterCustodian">Custodio actual</label><select id="tlabFilterCustodian" name="cod_custodio"><option value="">Todos</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterDeadline">Cumplimiento del plazo</label><select id="tlabFilterDeadline" name="plazo"><option value="">Todos</option><option value="en_plazo">Dentro del plazo</option><option value="advertencia">Próximo al límite</option><option value="atrasado">Atrasado</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabFilterAdjustments">Cantidad mínima de ajustes</label><input id="tlabFilterAdjustments" name="ajustes_desde" type="number" min="0" step="1" inputmode="numeric" placeholder="0"></div>'
      + '        <div class="tlab-field"><label for="tlabFilterFrom">Desde</label><input id="tlabFilterFrom" name="fecha_desde" type="date"></div>'
      + '        <div class="tlab-field"><label for="tlabFilterTo">Hasta</label><input id="tlabFilterTo" name="fecha_hasta" type="date"></div>'
      + '        <label class="tlab-check"><input id="tlabFilterPendingTransfer" name="transferencia_pendiente" type="checkbox"><span>Con transferencia pendiente</span></label>'
      + '      </div><div class="tlab-filters__actions"><button type="button" class="tlab-button tlab-button--ghost" data-tlab-command="clear-filters">Limpiar</button><button type="submit" class="tlab-button tlab-button--primary"><i class="fa-solid fa-filter" aria-hidden="true"></i>Aplicar filtros</button></div></form>'
      + '    </section>'
      + '    <div class="tlab-view-switch" id="tlabViewSwitch" hidden><button type="button" data-tlab-view="operativa" aria-pressed="true"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Vista operativa</button><button type="button" data-tlab-view="mecanico" aria-pressed="false"><i class="fa-solid fa-toolbox" aria-hidden="true"></i>Mi bandeja</button></div>'
      + '    <nav class="tlab-groups" id="tlabGroups" aria-label="Grupos operativos" role="tablist"></nav>'
      + '    <nav class="tlab-mechanic-tray" id="tlabMechanicTray" aria-label="Bandeja del mecánico" role="tablist" hidden></nav>'
      + '    <div class="tlab-section-heading"><div><h2 id="tlabListTitle">Pendientes de entrega</h2><p id="tlabListHint">Trabajos que requieren una acción de entrega.</p></div><span class="tlab-status tlab-status--neutral" id="tlabResultCount">0 trabajos</span></div>'
      + '    <section id="tlabResults" aria-live="polite" aria-busy="false"><div class="tlab-results-state">' + loaderHtml("Buscando trabajos...", "content") + '</div></section>'
      + '    <div class="tlab-load-more" id="tlabLoadMore" hidden><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="load-more">Cargar más trabajos</button></div>'
      + '  </main>'
      + '</div>'
      + '<div class="tlab-detail-layer" id="tlabDetailLayer" hidden><aside class="tlab-detail" role="dialog" aria-modal="true" aria-labelledby="tlabDetailTitle"><header class="tlab-detail__header"><div><small>Cadena de custodia</small><h2 id="tlabDetailTitle">Detalle del trabajo</h2></div><button type="button" class="tlab-icon-button tlab-icon-button--light" data-tlab-command="close-detail" aria-label="Cerrar detalle"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header><div class="tlab-detail__body" id="tlabDetailBody"></div></aside></div>'
      + '<div class="tlab-dialog-layer" id="tlabActionLayer" hidden></div>'
      + '<div class="tlab-viewer-layer" id="tlabViewerLayer" hidden></div>'
      + '<div class="tlab-live-region" id="tlabLiveRegion" aria-live="polite" aria-atomic="true"></div>';
  }

  function ensureDom() {
    var root = document.getElementById(ROOT_ID);
    ensureStyle();
    if (!root) {
      root = document.createElement("section");
      root.id = ROOT_ID;
      root.className = "tlab-shell";
      root.hidden = true;
      root.innerHTML = rootTemplate();
      document.body.appendChild(root);
    }
    state.root = root;
    bindEvents();
    renderGroupNavigation();
    return root;
  }

  function bindEvents() {
    var root = state.root;
    if (!root || root.getAttribute("data-tlab-bound") === "1") { return; }
    root.setAttribute("data-tlab-bound", "1");
    root.addEventListener("click", onRootClick);
    root.addEventListener("submit", onRootSubmit);
    root.addEventListener("change", onRootChange);
    root.addEventListener("input", onRootInput);
    document.addEventListener("keydown", onDocumentKeydown);
    if (window.visualViewport) {
      window.visualViewport.addEventListener("resize", updateViewportHeight);
    }
  }

  function updateViewportHeight() {
    if (state.root && window.visualViewport) {
      state.root.style.setProperty("--tlab-viewport-height", window.visualViewport.height + "px");
    }
  }

  function onRootClick(event) {
    var command = event.target.closest("[data-tlab-command]");
    var group = event.target.closest("[data-tlab-group]");
    var view = event.target.closest("[data-tlab-view]");
    var tray = event.target.closest("[data-tlab-tray]");
    var card = event.target.closest("[data-tlab-work-id]");
    var tab = event.target.closest("[data-tlab-detail-tab]");
    var action = event.target.closest("[data-tlab-action]");
    var previewRemove = event.target.closest("[data-tlab-preview-remove]");
    var evidence = event.target.closest("[data-tlab-media-id], [data-tlab-evidence-url]");
    if (command) {
      handleCommand(command.getAttribute("data-tlab-command"), command, event);
      return;
    }
    if (action) {
      event.preventDefault();
      openAction(action.getAttribute("data-tlab-action"));
      return;
    }
    if (group) {
      state.group = group.getAttribute("data-tlab-group");
      state.view = "operativa";
      renderGroupNavigation();
      loadWorks(false);
      return;
    }
    if (view) {
      state.view = view.getAttribute("data-tlab-view") === "mecanico" ? "mecanico" : "operativa";
      renderGroupNavigation();
      loadWorks(false);
      return;
    }
    if (tray) {
      state.mechanicTray = tray.getAttribute("data-tlab-tray");
      renderGroupNavigation();
      loadWorks(false);
      return;
    }
    if (tab) {
      state.detailTab = tab.getAttribute("data-tlab-detail-tab");
      renderDetailContent();
      return;
    }
    if (previewRemove) {
      removePreview(numberValue(previewRemove.getAttribute("data-tlab-preview-remove"), -1));
      return;
    }
    if (evidence) {
      event.preventDefault();
      if (evidence.getAttribute("data-tlab-media-id")) {
        openAuthorizedMedia(evidence.getAttribute("data-tlab-media-id"), evidence.getAttribute("data-tlab-evidence-caption"));
      } else {
        openViewer(evidence.getAttribute("data-tlab-evidence-url"), evidence.getAttribute("data-tlab-evidence-caption"));
      }
      return;
    }
    if (card) {
      openDetail(card.getAttribute("data-tlab-work-id"));
    }
  }

  function handleCommand(command, element, event) {
    var filters;
    switch (command) {
      case "close": closeModule(); break;
      case "refresh": refreshAll(); break;
      case "toggle-filters":
        filters = state.root.querySelector("#tlabFilters");
        filters.hidden = !filters.hidden;
        element.setAttribute("aria-expanded", filters.hidden ? "false" : "true");
        break;
      case "clear-filters": clearFilters(); break;
      case "load-more": loadWorks(true); break;
      case "close-detail": closeDetail(); break;
      case "close-action": closeAction(); break;
      case "action-back": actionBack(); break;
      case "action-next": actionNext(); break;
      case "close-viewer": closeViewer(); break;
      case "copy-code":
        event.stopPropagation();
        copyText(element.getAttribute("data-code"));
        break;
    }
  }

  function onRootSubmit(event) {
    if (event.target.id === "tlabFilters") {
      event.preventDefault();
      updateFilterCount();
      loadWorks(false);
      return;
    }
    if (event.target.id === "tlabActionForm") {
      event.preventDefault();
      submitAction();
    }
  }

  function onRootChange(event) {
    if (event.target.matches("[data-tlab-file-input]")) {
      captureActionValues();
      addFiles(event.target.files);
      event.target.value = "";
      return;
    }
    if (state.action && event.target.closest("#tlabActionForm")) {
      captureActionValues();
      if (state.action.code === "solicitarAjuste" && event.target.name === "motivo") {
        renderActionDialog();
      }
    }
  }

  function onRootInput(event) {
    if (event.target.id === "tlabSearch") {
      window.clearTimeout(state.searchTimer);
      state.searchTimer = window.setTimeout(function () { loadWorks(false); }, 380);
      return;
    }
    if (state.action && event.target.closest("#tlabActionForm")) {
      captureActionValues();
    }
  }

  function onDocumentKeydown(event) {
    var layer;
    if (!state.open) { return; }
    layer = activeLayer();
    if (event.key === "Escape") {
      if (!state.root.querySelector("#tlabViewerLayer").hidden) { closeViewer(); }
      else if (!state.root.querySelector("#tlabActionLayer").hidden) { closeAction(); }
      else if (!state.root.querySelector("#tlabDetailLayer").hidden) { closeDetail(); }
      else { closeModule(); }
      return;
    }
    if (event.key === "Tab" && layer) { trapFocus(event, layer); }
  }

  function activeLayer() {
    var selectors = ["#tlabViewerLayer", "#tlabActionLayer", "#tlabDetailLayer"];
    var i;
    var node;
    if (!state.root) { return null; }
    for (i = 0; i < selectors.length; i += 1) {
      node = state.root.querySelector(selectors[i]);
      if (node && !node.hidden) { return node; }
    }
    return null;
  }

  function trapFocus(event, container) {
    var elements = Array.prototype.slice.call(container.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'));
    var first;
    var last;
    if (!elements.length) { return; }
    first = elements[0];
    last = elements[elements.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function focusFirst(container) {
    var focusable = container && container.querySelector('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex="0"]');
    if (focusable) { window.setTimeout(function () { focusable.focus(); }, 30); }
  }

  function copyText(value) {
    var temporary;
    if (!value) { return; }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(value).then(function () { notify("Código copiado.", "success"); });
      return;
    }
    temporary = document.createElement("textarea");
    temporary.value = value;
    temporary.style.position = "fixed";
    temporary.style.opacity = "0";
    document.body.appendChild(temporary);
    temporary.select();
    document.execCommand("copy");
    document.body.removeChild(temporary);
    notify("Código copiado.", "success");
  }

  function openModule(options) {
    var root = ensureDom();
    options = options || {};
    state.moduleOptions = {
      cod_consulta_origen: options.cod_consulta_origen || "",
      cod_evolucion_origen: options.cod_evolucion_origen || ""
    };
    state.focusBeforeLayer = document.activeElement;
    state.open = true;
    state.group = options.grupo || state.group;
    state.view = options.vista === "mecanico" ? "mecanico" : state.view;
    state.mechanicTray = options.bandeja || state.mechanicTray;
    root.hidden = false;
    document.body.classList.add("tlab-lock");
    updateViewportHeight();
    renderGroupNavigation();
    loadInitialData();
    focusFirst(root.querySelector(".tlab-topbar"));
  }

  function closeModule() {
    if (!state.root) { return; }
    closeViewer();
    closeAction();
    closeDetail();
    state.open = false;
    state.root.hidden = true;
    document.body.classList.remove("tlab-lock");
    if (state.focusBeforeLayer && typeof state.focusBeforeLayer.focus === "function") {
      state.focusBeforeLayer.focus();
    }
  }

  function loadInitialData() {
    var summary = state.root.querySelector("#tlabSummary");
    var results = state.root.querySelector("#tlabResults");
    summary.innerHTML = loaderHtml("Preparando resumen...", "compact");
    results.innerHTML = '<div class="tlab-results-state">' + loaderHtml("Buscando trabajos...", "content") + '</div>';
    Promise.all([
      loadCatalogs().then(null, function (error) { notify(error.message, "error"); }),
      loadSummary().then(null, function (error) { renderSummary({}); notify(error.message, "error"); })
    ]).then(function () {
      if (state.open) { loadWorks(false); }
    });
  }

  function refreshAll() {
    loadSummary().then(null, function (error) { notify(error.message, "error"); });
    loadCatalogs().then(null, function () {});
    loadWorks(false);
    if (state.detailId) { openDetail(state.detailId, true); }
  }

  function mergeContext(data) {
    var context = data.contexto || data.context || {};
    state.context = Object.assign({}, state.context, context);
    if (data.es_mecanico !== undefined) { state.context.es_mecanico = data.es_mecanico; }
    if (data.es_auditor !== undefined) { state.context.es_auditor = data.es_auditor; }
    if (data.rol) { state.context.rol = data.rol; }
    updateRolePresentation();
  }

  function updateRolePresentation() {
    var badge;
    var switcher;
    var mechanicFilter;
    var mechanic = boolValue(state.context.es_mecanico) || toStringSafe(state.context.rol).toLowerCase().indexOf("mecán") >= 0 || toStringSafe(state.context.rol).toLowerCase().indexOf("mecan") >= 0;
    if (!state.root) { return; }
    badge = state.root.querySelector("#tlabRoleBadge span");
    switcher = state.root.querySelector("#tlabViewSwitch");
    mechanicFilter = state.root.querySelector("#tlabFilterMechanic");
    if (badge) { badge.textContent = mechanic ? "Mecánico dental" : (state.context.rol || "Acceso autorizado"); }
    switcher.hidden = !(mechanic || boolValue(state.context.puede_ver_bandeja_mecanico));
    if (mechanicFilter && mechanicFilter.closest(".tlab-field")) {
      mechanicFilter.closest(".tlab-field").hidden = mechanic;
    }
    if (mechanic && state.context.forzar_bandeja !== false && state.view === "operativa") {
      state.view = "mecanico";
    }
    renderGroupNavigation();
  }

  function loadCatalogs() {
    return request("obtenerCatalogos", {}).then(function (response) {
      state.catalogs = response.data.catalogos || response.data;
      mergeContext(response.data);
      renderCatalogs();
      return response;
    });
  }

  function loadSummary() {
    return request("obtenerResumen", {}).then(function (response) {
      state.summary = response.data.resumen || response.data;
      mergeContext(response.data);
      renderSummary(state.summary);
      renderGroupNavigation();
      return response;
    });
  }

  function summaryNumber(keys) {
    var value = pick(state.summary, keys, 0);
    if (value && typeof value === "object") { value = pick(value, ["cantidad", "total", "valor"], 0); }
    return numberValue(value, 0);
  }

  function renderSummary(summary) {
    var container;
    var cards;
    state.summary = summary || {};
    if (!state.root) { return; }
    container = state.root.querySelector("#tlabSummary");
    cards = [
      { label: "Pendientes de entrega", value: summaryNumber(["pendientes_entrega", "pendientes_de_entrega"]), hint: "Requieren preparar o entregar", icon: "fa-box-open", cls: "" },
      { label: "En poder del laboratorio", value: summaryNumber(["en_laboratorio", "en_poder_laboratorio"]), hint: "Custodia confirmada", icon: "fa-microscope", cls: "tlab-summary-card--teal" },
      { label: "Pendientes de revisión", value: summaryNumber(["pendientes_revision", "pendientes_revision_clinica"]), hint: "Esperan decisión clínica", icon: "fa-user-doctor", cls: "tlab-summary-card--violet" },
      { label: "Ajustes activos", value: summaryNumber(["ajustes_activos", "con_ajustes"]), hint: "Ciclos de ajuste abiertos", icon: "fa-screwdriver-wrench", cls: "tlab-summary-card--warning" },
      { label: "Fuera del plazo", value: summaryNumber(["fuera_plazo", "atrasados"]), hint: "Necesitan atención", icon: "fa-triangle-exclamation", cls: "tlab-summary-card--danger" },
      { label: "Finalizados recientes", value: summaryNumber(["finalizados_recientes", "finalizados"]), hint: "Cerrados en el período", icon: "fa-circle-check", cls: "tlab-summary-card--ok" }
    ];
    container.innerHTML = cards.map(function (card) {
      return '<article class="tlab-summary-card ' + card.cls + '"><span class="tlab-summary-card__label"><i class="fa-solid ' + card.icon + '" aria-hidden="true"></i>' + escapeHtml(card.label) + '</span><strong class="tlab-summary-card__value">' + card.value + '</strong><small class="tlab-summary-card__hint">' + escapeHtml(card.hint) + '</small></article>';
    }).join("");
  }

  function catalogItems(names) {
    var items = pick(state.catalogs, names, []);
    return asArray(items);
  }

  function optionHtml(item) {
    var value;
    var label;
    if (typeof item !== "object") { return '<option value="' + escapeAttr(item) + '">' + escapeHtml(item) + '</option>'; }
    value = pick(item, ["cod_tecnico_usuario", "cod_usuario", "id", "codigo", "cod", "valor", "value", "cod_persona", "cod_local", "cod_producto"], "");
    label = pick(item, ["nombre", "descripcion", "etiqueta", "label", "texto"], value);
    return '<option value="' + escapeAttr(value) + '">' + escapeHtml(label) + '</option>';
  }

  function fillSelect(id, items, firstLabel) {
    var select = state.root.querySelector("#" + id);
    var previous;
    if (!select) { return; }
    previous = select.value;
    select.innerHTML = '<option value="">' + escapeHtml(firstLabel) + '</option>' + items.map(optionHtml).join("");
    if (previous && select.querySelector('option[value="' + cssEscape(previous) + '"]')) { select.value = previous; }
  }

  function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === "function") { return window.CSS.escape(toStringSafe(value)); }
    return toStringSafe(value).replace(/(["\\])/g, "\\$1");
  }

  function renderCatalogs() {
    if (!state.root) { return; }
    fillSelect("tlabFilterSituation", catalogItems(["situaciones", "situaciones_calculadas", "estados"]), "Todas");
    fillSelect("tlabFilterBranch", catalogItems(["locales", "sucursales"]), "Todas las autorizadas");
    fillSelect("tlabFilterMechanic", catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"]), "Todos");
    fillSelect("tlabFilterDoctor", catalogItems(["doctores", "especialistas"]), "Todos");
    fillSelect("tlabFilterProduct", catalogItems(["productos", "tipos_trabajo"]), "Todos");
    fillSelect("tlabFilterCustodian", catalogItems(["custodios", "responsables"]), "Todos");
  }

  function groupCount(key) {
    var groups = state.summary.grupos || state.summary.grupos_operativos || {};
    var value = groups[key];
    if (value === undefined) { value = state.summary[key]; }
    if (value && typeof value === "object") { value = pick(value, ["cantidad", "total"], 0); }
    return numberValue(value, 0);
  }

  function trayCount(key) {
    var tray = state.summary.bandeja_mecanico || state.summary.mi_bandeja || {};
    var value = tray[key];
    if (value && typeof value === "object") { value = pick(value, ["cantidad", "total"], 0); }
    return numberValue(value, 0);
  }

  function renderGroupNavigation() {
    var groups;
    var tray;
    var switcher;
    var title;
    var hint;
    var selected;
    if (!state.root) { return; }
    groups = state.root.querySelector("#tlabGroups");
    tray = state.root.querySelector("#tlabMechanicTray");
    switcher = state.root.querySelector("#tlabViewSwitch");
    groups.hidden = state.view === "mecanico";
    tray.hidden = state.view !== "mecanico";
    if (switcher) {
      Array.prototype.forEach.call(switcher.querySelectorAll("[data-tlab-view]"), function (button) {
        button.setAttribute("aria-pressed", button.getAttribute("data-tlab-view") === state.view ? "true" : "false");
      });
    }
    groups.innerHTML = GROUPS.map(function (group) {
      return '<button type="button" role="tab" data-tlab-group="' + group.key + '" aria-selected="' + (group.key === state.group ? "true" : "false") + '"><i class="fa-solid ' + group.icon + '" aria-hidden="true"></i>' + escapeHtml(group.label) + '<span class="tlab-group-count">' + groupCount(group.key) + '</span></button>';
    }).join("");
    tray.innerHTML = MECHANIC_TRAY.map(function (item) {
      return '<button type="button" role="tab" data-tlab-tray="' + item.key + '" aria-selected="' + (item.key === state.mechanicTray ? "true" : "false") + '">' + escapeHtml(item.label) + '<span class="tlab-group-count">' + trayCount(item.key) + '</span></button>';
    }).join("");
    if (state.view === "mecanico") {
      selected = MECHANIC_TRAY.filter(function (item) { return item.key === state.mechanicTray; })[0] || MECHANIC_TRAY[0];
      title = "Mi bandeja · " + selected.label;
      hint = "Sólo se muestran trabajos asignados y acciones autorizadas.";
    } else {
      selected = GROUPS.filter(function (item) { return item.key === state.group; })[0] || GROUPS[0];
      title = selected.label;
      hint = selected.key === "finalizados" ? "Trazabilidad histórica conservada." : "La próxima acción surge de la cadena de custodia.";
    }
    state.root.querySelector("#tlabListTitle").textContent = title;
    state.root.querySelector("#tlabListHint").textContent = hint;
  }

  function filterPayload() {
    var form = state.root.querySelector("#tlabFilters");
    var payload = {};
    if (!form) { return payload; }
    forEachFormValue(form, function (value, key) {
      if (value !== "") { payload[key] = value; }
    });
    payload.transferencia_pendiente = state.root.querySelector("#tlabFilterPendingTransfer").checked ? "1" : "";
    payload.busqueda = state.root.querySelector("#tlabSearch").value.trim();
    payload.grupo_operativo = state.view === "operativa" ? state.group : "";
    payload.vista = state.view;
    payload.bandeja = state.view === "mecanico" ? state.mechanicTray : "";
    return payload;
  }

  function updateFilterCount() {
    var payload = filterPayload();
    var ignored = { busqueda: true, grupo_operativo: true, vista: true, bandeja: true };
    var count = Object.keys(payload).filter(function (key) { return !ignored[key] && payload[key] !== ""; }).length;
    var badge = state.root.querySelector("#tlabFilterCount");
    badge.textContent = count;
    badge.hidden = count === 0;
  }

  function clearFilters() {
    var form = state.root.querySelector("#tlabFilters");
    form.reset();
    state.root.querySelector("#tlabSearch").value = "";
    updateFilterCount();
    loadWorks(false);
  }

  function listFromResponse(data) {
    if (Array.isArray(data)) { return data; }
    return asArray(data.trabajos || data.items || data.registros || data.lista || []);
  }

  function loadWorks(append) {
    var requestId;
    var payload;
    var results;
    if (!state.root || (state.loadingList && append)) { return; }
    state.loadingList = true;
    state.page = append ? state.page + 1 : 1;
    requestId = ++state.listRequest;
    payload = filterPayload();
    payload.pagina = state.page;
    payload.limite = PAGE_SIZE;
    payload.por_pagina = PAGE_SIZE;
    results = state.root.querySelector("#tlabResults");
    results.setAttribute("aria-busy", "true");
    if (!append) {
      results.innerHTML = '<div class="tlab-results-state">' + loaderHtml("Buscando trabajos...", "content") + '</div>';
    }
    request("listarTrabajos", payload).then(function (response) {
      var items;
      var total;
      var loaded;
      if (requestId !== state.listRequest) { return; }
      mergeContext(response.data);
      items = listFromResponse(response.data);
      state.works = append ? state.works.concat(items) : items;
      total = numberValue(
        pick(response.data, ["total", "cantidad_total", "registros"],
          pick(response.data.paginacion || {}, ["total"], state.works.length)),
        state.works.length
      );
      loaded = state.works.length;
      state.hasMore = response.data.hay_mas === true || response.data.has_more === true || loaded < total;
      renderWorks(total);
    }).then(null, function (error) {
      if (requestId !== state.listRequest) { return; }
      if (append) { state.page = Math.max(1, state.page - 1); }
      renderListError(error.message);
    }).then(function () {
      if (requestId !== state.listRequest) { return; }
      state.loadingList = false;
      results.setAttribute("aria-busy", "false");
    });
  }

  function normalizeActions(value) {
    var source = value;
    if (source && !Array.isArray(source) && typeof source === "object"
        && !source.codigo && !source.accion && !source.code && !source.endpoint) {
      source = Object.keys(source).map(function (key) {
        var definition = source[key];
        if (definition === false || definition === 0 || definition === "0") { return null; }
        if (definition && typeof definition === "object") {
          return Object.assign({ codigo: key }, definition);
        }
        return { codigo: key };
      }).filter(Boolean);
    }
    return asArray(source).map(function (item) {
      var rawCode;
      var code;
      var base;
      if (typeof item === "string") { item = { codigo: item }; }
      if (!item || item.permitido === false || item.permitido === 0 || item.permitido === "0") { return null; }
      rawCode = pick(item, ["codigo", "accion", "code", "endpoint"], "");
      code = ACTION_ALIASES[rawCode] || rawCode;
      base = ACTIONS[code];
      if (!base) { return null; }
      return Object.assign({}, base, item, { code: code, endpoint: code, label: pick(item, ["etiqueta", "label", "nombre"], base.label) });
    }).filter(Boolean);
  }

  function normalizeWork(item) {
    item = item || {};
    var nestedDetail = item.detalle && typeof item.detalle === "object" ? item.detalle : {};
    var deadline = pick(item, ["semaforo", "plazo", "cumplimiento_plazo"], "");
    var deadlineText;
    var deadlineClass;
    var actions = normalizeActions(item.acciones_permitidas || item.acciones || []);
    if (deadline && typeof deadline === "object") {
      deadlineText = pick(deadline, ["texto", "etiqueta", "estado"], "Sin plazo");
      deadlineClass = pick(deadline, ["nivel", "clase", "codigo"], "neutral");
    } else {
      deadlineText = toStringSafe(deadline || "Sin plazo").replace(/_/g, " ");
      deadlineClass = toStringSafe(deadline).toLowerCase();
    }
    if (deadlineClass.indexOf("atras") >= 0 || deadlineClass.indexOf("venc") >= 0) { deadlineClass = "danger"; }
    else if (deadlineClass.indexOf("advert") >= 0 || deadlineClass.indexOf("proxim") >= 0) { deadlineClass = "warning"; }
    else if (deadlineClass.indexOf("plazo") >= 0 || deadlineClass.indexOf("ok") >= 0) { deadlineClass = "ok"; }
    else { deadlineClass = "neutral"; }
    return {
      raw: item,
      id: pick(item, ["id_trabajo", "cod_trabajo_laboratorio", "id", "cod_trabajo"], ""),
      code: pick(item, ["codigo_visible", "codigo_trabajo", "codigo", "nomenclatura"], pick(nestedDetail, ["codigo_visible", "nro_venta", "numero_venta"], "Nuevo trabajo")),
      sale: pick(item, ["numero_venta", "nro_venta", "venta"], pick(nestedDetail, ["numero_venta", "nro_venta", "venta"], "")),
      patient: pick(item, ["paciente_nombre", "nombre_paciente", "paciente"], pick(nestedDetail, ["paciente_nombre", "nombre_paciente", "paciente"], "Paciente autorizado")),
      product: pick(item, ["producto_nombre", "tipo_trabajo", "producto", "tratamiento"], pick(nestedDetail, ["producto_nombre", "nombre_producto", "producto", "tratamiento"], "Trabajo de laboratorio")),
      branch: pick(item, ["local_nombre", "sucursal_nombre", "local", "sucursal"], pick(nestedDetail, ["local_nombre", "nombre_local", "sucursal_nombre", "local", "sucursal"], "Sin sucursal")),
      mechanic: item.mecanico || item.mecanico_dental || item.tecnico || pick(item, ["nombre_mecanico"], "Sin asignar"),
      doctor: item.doctor || item.especialista || pick(item, ["nombre_doctor"], "Sin asignar"),
      custodian: item.custodio_actual || item.custodio || pick(item, ["nombre_custodio"], "Sin confirmar"),
      currentDays: numberValue(pick(item, ["dias_custodio_actual", "dias_con_custodio", "dias_responsable"], 0), 0),
      totalDays: numberValue(pick(item, ["dias_totales", "dias_total"], 0), 0),
      adjustments: numberValue(pick(item, ["cantidad_ajustes", "ajustes", "ciclo_actual"], 0), 0),
      targetDate: pick(item, ["fecha_objetivo", "fecha_limite"], ""),
      image: pick(item, ["miniatura_url", "imagen_principal", "evidencia_principal", "foto"], ""),
      situation: pick(item, ["situacion_texto", "situacion", "estado_derivado"], "En seguimiento"),
      deadlineText: deadlineText,
      deadlineClass: deadlineClass,
      pendingTransfer: boolValue(pick(item, ["transferencia_pendiente", "tiene_transferencia_pendiente"], false)),
      currentCycle: pick(item, ["ciclo_etiqueta", "ciclo", "tipo_ciclo"], "Original"),
      actions: actions,
      version: pick(item, ["version", "version_registro"], "")
    };
  }

  function imageBlock(url, alt) {
    if (!url) { return '<i class="fa-solid fa-tooth" aria-hidden="true"></i><span class="sr-only">Sin fotografía</span>'; }
    return '<img src="' + escapeAttr(url) + '" alt="' + escapeAttr(alt || "Evidencia del trabajo") + '" loading="lazy">';
  }

  function workCardHtml(item) {
    var work = normalizeWork(item);
    var next = work.actions[0];
    return '<article class="tlab-work-card">'
      + '<button type="button" class="tlab-work-card__open" data-tlab-work-id="' + escapeAttr(work.id) + '" aria-label="Abrir trabajo ' + escapeAttr(work.code) + '">'
      + '<span class="tlab-work-card__hero"><span class="tlab-work-card__image">' + imageBlock(work.image, "Evidencia principal de " + work.code) + '</span><span>'
      + '<span class="tlab-work-card__code"><strong>' + escapeHtml(work.code) + '</strong><span class="tlab-copy-hint" title="Código visible confirmado"><i class="fa-solid fa-link" aria-hidden="true"></i></span></span>'
      + '<span class="tlab-work-card__product">' + escapeHtml(work.product) + '</span><span class="tlab-work-card__patient"><i class="fa-solid fa-user" aria-hidden="true"></i> ' + escapeHtml(work.patient) + '</span><span class="tlab-work-card__branch"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' + escapeHtml(work.branch) + '</span></span></span>'
      + '<span class="tlab-work-card__body"><span class="tlab-custody"><small>Actualmente en poder de</small><strong>' + escapeHtml(person(work.custodian).name) + '</strong></span>'
      + '<span class="tlab-person-row">' + personHtml(work.doctor, "Doctor") + personHtml(work.mechanic, "Mecánico dental") + '</span>'
      + '<span class="tlab-metrics"><span class="tlab-metric"><strong>' + formatDays(work.currentDays) + '</strong><small>con custodio</small></span><span class="tlab-metric"><strong>' + formatDays(work.totalDays) + '</strong><small>totales</small></span><span class="tlab-metric"><strong>' + work.adjustments + '</strong><small>ajustes</small></span></span>'
      + '<span class="tlab-badges"><span class="tlab-badge"><i class="fa-solid fa-calendar-day" aria-hidden="true"></i>Objetivo: ' + escapeHtml(formatDate(work.targetDate, false)) + '</span><span class="tlab-badge"><i class="fa-solid fa-code-branch" aria-hidden="true"></i>' + escapeHtml(work.currentCycle) + '</span>' + (work.pendingTransfer ? '<span class="tlab-badge tlab-badge--alert"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Recepción pendiente</span>' : '') + '</span></span>'
      + '<span class="tlab-work-card__footer"><span class="tlab-status tlab-status--' + work.deadlineClass + '"><i class="fa-solid ' + (work.deadlineClass === "danger" ? "fa-triangle-exclamation" : "fa-clock") + '" aria-hidden="true"></i>' + escapeHtml(work.deadlineText) + '</span><span class="tlab-next-action">' + escapeHtml(next ? "Próxima: " + next.label : "Ver trazabilidad") + '</span></span>'
      + '</button></article>';
  }

  function renderWorks(total) {
    var results = state.root.querySelector("#tlabResults");
    var count = state.root.querySelector("#tlabResultCount");
    var more = state.root.querySelector("#tlabLoadMore");
    count.textContent = total + (total === 1 ? " trabajo" : " trabajos");
    if (!state.works.length) {
      results.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>No hay trabajos en esta bandeja</strong><span>Probá otro grupo o ajustá los filtros de búsqueda.</span></div></div>';
    } else {
      results.innerHTML = '<div class="tlab-cards">' + state.works.map(workCardHtml).join("") + '</div>';
    }
    more.hidden = !state.hasMore;
  }

  function renderListError(message) {
    var results = state.root.querySelector("#tlabResults");
    results.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo cargar la bandeja</strong><span>' + escapeHtml(message) + '</span><br><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="refresh">Reintentar</button></div></div>';
    state.root.querySelector("#tlabLoadMore").hidden = true;
  }

  function openDetail(id, preserveFocus) {
    var layer;
    var body;
    if (!id || !state.root) { return; }
    layer = state.root.querySelector("#tlabDetailLayer");
    body = state.root.querySelector("#tlabDetailBody");
    if (!preserveFocus) { state.focusBeforeLayer = document.activeElement; }
    state.detailId = id;
    state.detail = null;
    state.detailEnvelope = null;
    state.detailTab = "timeline";
    layer.hidden = false;
    body.innerHTML = '<div class="tlab-detail__loader">' + loaderHtml("Reconstruyendo la trazabilidad...", "content") + '</div>';
    return request("obtenerTrabajo", { id_trabajo: id, cod_trabajo_laboratorio: id }).then(function (response) {
      var envelope = response.data || {};
      var detail = envelope.trabajo || envelope.item || envelope;
      if (toStringSafe(state.detailId) !== toStringSafe(id)) { return; }
      if (!detail.acciones_permitidas && envelope.acciones_permitidas) { detail.acciones_permitidas = envelope.acciones_permitidas; }
      if (!detail.version && response.version) { detail.version = response.version; }
      state.detail = detail;
      state.detailEnvelope = envelope;
      mergeContext(envelope);
      renderDetailContent();
      focusFirst(layer);
      return response;
    }).then(null, function (error) {
      if (toStringSafe(state.detailId) !== toStringSafe(id)) { return null; }
      body.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-lock" aria-hidden="true"></i><strong>No se pudo abrir el trabajo</strong><span>' + escapeHtml(error.message) + '</span></div></div>';
      return null;
    });
  }

  function closeDetail() {
    var layer;
    if (!state.root) { return; }
    layer = state.root.querySelector("#tlabDetailLayer");
    layer.hidden = true;
    state.detailId = "";
    state.detail = null;
    state.detailEnvelope = null;
  }

  function detailArray(names) {
    var envelope = state.detailEnvelope || {};
    var detail = state.detail || {};
    return asArray(pick(envelope, names, pick(detail, names, [])));
  }

  function dataBox(label, value) {
    return '<div class="tlab-data"><small>' + escapeHtml(label) + '</small><strong>' + escapeHtml(value || "Sin registrar") + '</strong></div>';
  }

  function actionAllowedInCurrentContext(action) {
    if (!action || action.code !== "registrarInstalacion") { return true; }
    return numberValue(state.moduleOptions.cod_evolucion_origen, 0) > 0;
  }

  function contextActions(actions) {
    return normalizeActions(actions).filter(actionAllowedInCurrentContext);
  }

  function renderDetailContent() {
    var detail;
    var work;
    var envelope;
    var actions;
    var body;
    var tabs;
    var tabContent;
    var canAudit;
    if (!state.detail || !state.root) { return; }
    detail = state.detail;
    envelope = state.detailEnvelope || {};
    work = normalizeWork(detail);
    var serverActions = normalizeActions(detail.acciones_permitidas || envelope.acciones_permitidas || []);
    var installationFromEvolution = serverActions.some(function (action) {
      return action.code === "registrarInstalacion";
    }) && numberValue(state.moduleOptions.cod_evolucion_origen, 0) <= 0;
    actions = contextActions(serverActions);
    canAudit = boolValue(envelope.puede_ver_auditoria || detail.puede_ver_auditoria) && detailArray(["auditoria", "historial_auditoria"]).length > 0;
    state.root.querySelector("#tlabDetailTitle").textContent = work.code;
    tabs = [
      { key: "timeline", label: "Trazabilidad", icon: "fa-diagram-project" },
      { key: "evidence", label: "Evidencias", icon: "fa-images" },
      { key: "notes", label: "Observaciones", icon: "fa-message" }
    ];
    if (canAudit) { tabs.push({ key: "audit", label: "Auditoría", icon: "fa-shield-halved" }); }
    if (!tabs.some(function (tab) { return tab.key === state.detailTab; })) { state.detailTab = "timeline"; }
    tabContent = state.detailTab === "evidence" ? renderEvidenceTab()
      : (state.detailTab === "notes" ? renderNotesTab()
        : (state.detailTab === "audit" ? renderAuditTab() : renderTimelineTab()));
    body = state.root.querySelector("#tlabDetailBody");
    body.innerHTML = '<div class="tlab-detail-summary">'
      + '<section class="tlab-detail-hero"><div class="tlab-detail-hero__image">' + imageBlock(work.image, "Evidencia principal de " + work.code) + '</div><div class="tlab-detail-hero__copy"><h3>' + escapeHtml(work.product) + '</h3><p><i class="fa-solid fa-user" aria-hidden="true"></i> ' + escapeHtml(work.patient) + '</p><p><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' + escapeHtml(work.branch) + '</p><span class="tlab-status tlab-status--' + work.deadlineClass + '">' + escapeHtml(work.deadlineText) + '</span></div></section>'
      + '<section class="tlab-panel"><h3>Situación actual</h3><div class="tlab-custody"><small>Actualmente en poder de</small><strong>' + escapeHtml(person(work.custodian).name) + '</strong></div><div class="tlab-data-grid" style="margin-top:10px">'
      + dataBox("Venta", work.sale || work.code) + dataBox("Situación", work.situation) + dataBox("Fecha objetivo", formatDate(work.targetDate, false)) + dataBox("Ciclo", work.currentCycle)
      + dataBox("Días totales", formatDays(work.totalDays)) + dataBox("Días con custodio", formatDays(work.currentDays)) + dataBox("Ajustes", work.adjustments) + dataBox("Sucursal", work.branch)
      + '</div></section></div>'
      + '<section class="tlab-panel tlab-detail-section"><div class="tlab-section-heading"><div><h3>Próxima acción</h3><p>Disponible según permiso, asignación y custodia actual.</p></div></div><div class="tlab-actions">'
      + (actions.length ? actions.map(actionButtonHtml).join("") : '<p class="tlab-actions-empty"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> No hay acciones disponibles para este usuario.</p>')
      + '</div>' + (installationFromEvolution ? '<p class="tlab-actions-empty"><i class="fa-solid fa-tooth" aria-hidden="true"></i> La instalación se registra desde una evolución clínica del tratamiento.</p>' : '') + '</section>'
      + '<nav class="tlab-detail-tabs" role="tablist" aria-label="Información del trabajo">' + tabs.map(function (tab) {
        return '<button type="button" role="tab" data-tlab-detail-tab="' + tab.key + '" aria-selected="' + (state.detailTab === tab.key ? "true" : "false") + '"><i class="fa-solid ' + tab.icon + '" aria-hidden="true"></i> ' + escapeHtml(tab.label) + '</button>';
      }).join("") + '</nav><div class="tlab-tab-panel" role="tabpanel">' + tabContent + '</div>';
  }

  function actionButtonHtml(action) {
    return '<button type="button" class="tlab-button ' + (action.danger ? "tlab-button--danger" : (action.code === "agregarEvidencia" || action.code === "agregarNota" ? "tlab-button--secondary" : "tlab-button--primary")) + '" data-tlab-action="' + escapeAttr(action.code) + '"><i class="fa-solid ' + escapeAttr(action.icon || "fa-arrow-right") + '" aria-hidden="true"></i>' + escapeHtml(action.label) + '</button>';
  }

  function timelineEventHtml(item, index, length) {
    var actor = item.actor || item.usuario || item.realizado_por || pick(item, ["nombre_usuario"], "Usuario registrado");
    var title = pick(item, ["accion_texto", "titulo", "evento_texto", "tipo_evento", "accion"], "Evento registrado");
    var cycle = pick(item, ["ciclo_etiqueta", "ciclo", "tipo_ciclo"], "Original");
    var date = pick(item, ["fecha_hora", "server_timestamp", "fecha", "creado_en"], "");
    var branch = pick(item, ["local_nombre", "sucursal", "local"], "Sin sucursal");
    var note = pick(item, ["observacion", "nota", "justificacion", "detalle"], "");
    var image = pick(item, ["miniatura_url", "imagen", "foto", "evidencia_url"], "");
    var elapsed = pick(item, ["tiempo_desde_anterior_texto", "dias_desde_anterior", "dias_transcurridos"], "");
    var previous = person(item.custodio_anterior || pick(item, ["nombre_custodio_anterior"], "")).name;
    var next = person(item.custodio_nuevo || item.nuevo_custodio || pick(item, ["nombre_custodio_nuevo"], "")).name;
    var pending = boolValue(item.pendiente);
    var alert = boolValue(item.atrasado || item.demora);
    var adjustment = toStringSafe(cycle).toLowerCase().indexOf("ajuste") >= 0;
    if (elapsed !== "" && /^\d+$/.test(toStringSafe(elapsed))) { elapsed = formatDays(elapsed); }
    return '<article class="tlab-timeline-node"><div class="tlab-event-card ' + (pending ? "tlab-event-card--pending" : (alert ? "tlab-event-card--alert" : (adjustment ? "tlab-event-card--adjustment" : ""))) + '">'
      + '<div class="tlab-event-card__media"><button type="button" class="tlab-event-card__photo" ' + (image ? 'data-tlab-evidence-url="' + escapeAttr(image) + '" data-tlab-evidence-caption="' + escapeAttr(title) + '"' : 'disabled') + '>' + imageBlock(image, title) + '</button>' + avatarHtml(actor) + '</div>'
      + '<span class="tlab-event-card__cycle">' + escapeHtml(cycle) + '</span><h4>' + escapeHtml(toStringSafe(title).replace(/_/g, " ")) + '</h4><span class="tlab-event-card__who">' + escapeHtml(person(actor).name) + ' · ' + escapeHtml(person(actor).role) + '</span><span class="tlab-event-card__when">' + escapeHtml(formatDate(date, true)) + ' · ' + escapeHtml(branch) + '</span>'
      + (note ? '<p class="tlab-event-card__note">' + escapeHtml(note) + '</p>' : '')
      + ((previous !== "Sin asignar" || next !== "Sin asignar") ? '<details><summary>Ver transferencia</summary><p>De: ' + escapeHtml(previous) + '<br>Recibió: ' + escapeHtml(next) + '</p></details>' : '')
      + '</div>' + (index < length - 1 ? '<span class="tlab-timeline-node__knot" aria-hidden="true"></span><span class="tlab-timeline-node__elapsed">' + escapeHtml(elapsed || "Siguiente") + '</span>' : '') + '</article>';
  }

  function renderTimelineTab() {
    var events = detailArray(["eventos", "timeline", "trazabilidad"]).filter(function (item) {
      var type = toStringSafe(pick(item, ["tipo_evento", "tipo", "accion"], "")).toLowerCase();
      return type !== "evidencia_agregada" && type !== "nota_agregada";
    });
    if (!events.length) { return '<div class="tlab-empty"><div><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>Aún no hay eventos visibles</strong><span>La trazabilidad se formará con acciones y confirmaciones de custodia.</span></div></div>'; }
    return '<section class="tlab-timeline"><div class="tlab-section-heading"><div><h3>Hilo visual del trabajo</h3><p>Cada nodo representa una acción o una confirmación auditada.</p></div></div><div class="tlab-timeline-list">' + events.map(function (event, index) { return timelineEventHtml(event, index, events.length); }).join("") + '</div></section>';
  }

  function renderEvidenceTab() {
    var evidence = detailArray(["evidencias", "media", "fotos"]);
    var groups = {};
    if (!evidence.length) { return '<div class="tlab-empty"><div><i class="fa-solid fa-images" aria-hidden="true"></i><strong>Sin evidencias disponibles</strong><span>Las imágenes se mostrarán agrupadas por ciclo.</span></div></div>'; }
    evidence.forEach(function (item) {
      var cycle = pick(item, ["ciclo_etiqueta", "ciclo", "tipo_ciclo"], "Original");
      if (!groups[cycle]) { groups[cycle] = []; }
      groups[cycle].push(item);
    });
    return '<div class="tlab-gallery-groups">' + Object.keys(groups).map(function (cycle) {
      return '<section class="tlab-gallery-group"><h4>' + escapeHtml(cycle) + '</h4><div class="tlab-gallery">' + groups[cycle].map(function (item) {
        var url = pick(item, ["url_visualizacion", "miniatura_url", "url", "imagen"], "");
        var original = pick(item, ["url_original_autorizada"], "");
        var mediaId = pick(item, ["id_media", "id"], "");
        var label = pick(item, ["descripcion", "tipo_evidencia", "nombre_original", "nombre"], "Evidencia");
        return '<button type="button" ' + (mediaId
          ? 'data-tlab-media-id="' + escapeAttr(mediaId) + '"'
          : 'data-tlab-evidence-url="' + escapeAttr(original || url) + '"')
          + ' data-tlab-evidence-caption="' + escapeAttr(label) + '">' + imageBlock(url, label) + '<span>' + escapeHtml(label) + '</span></button>';
      }).join("") + '</div></section>';
    }).join("") + '</div>';
  }

  function renderNotesTab() {
    var notes = detailArray(["notas", "observaciones"]);
    if (!notes.length) {
      notes = detailArray(["eventos", "timeline", "trazabilidad"]).filter(function (item) {
        return toStringSafe(pick(item, ["tipo_evento", "tipo", "accion"], "")).toLowerCase() === "nota_agregada";
      });
    }
    if (!notes.length) { return '<div class="tlab-empty"><div><i class="fa-solid fa-message" aria-hidden="true"></i><strong>Sin observaciones</strong><span>Las notas autorizadas del trabajo aparecerán aquí.</span></div></div>'; }
    return '<ul class="tlab-note-list">' + notes.map(function (item) {
      return '<li><strong>' + escapeHtml(pick(item, ["texto", "nota", "observacion"], "Observación")) + '</strong><span>' + escapeHtml(person(item.actor || item.usuario || pick(item, ["nombre_usuario"], "Usuario")).name) + ' · ' + escapeHtml(formatDate(pick(item, ["fecha_hora", "fecha", "creado_en"], ""), true)) + '</span></li>';
    }).join("") + '</ul>';
  }

  function renderAuditTab() {
    var audit = detailArray(["auditoria", "historial_auditoria"]);
    return '<ul class="tlab-audit-list">' + audit.map(function (item) {
      return '<li><strong>' + escapeHtml(pick(item, ["accion_texto", "accion", "evento"], "Registro auditado")) + '</strong><span>' + escapeHtml(person(item.actor || item.usuario || pick(item, ["nombre_usuario"], "Usuario")).name) + ' · ' + escapeHtml(formatDate(pick(item, ["fecha_hora", "fecha", "creado_en"], ""), true)) + (item.motivo ? ' · ' + escapeHtml(item.motivo) : '') + '</span></li>';
    }).join("") + '</ul>';
  }

  function actionForCode(code) {
    var actions = contextActions((state.detail && state.detail.acciones_permitidas) || (state.detailEnvelope && state.detailEnvelope.acciones_permitidas) || []);
    return actions.filter(function (item) { return item.code === code; })[0] || null;
  }

  function openAction(code, suppliedAction, suppliedWork) {
    var action = suppliedAction || actionForCode(code);
    var work = suppliedWork || state.detail || state.startContext;
    if (!action || !ACTIONS[action.code]) {
      notify("Esta acción ya no está disponible. Actualizá el trabajo.", "error");
      return;
    }
    revokeObjectUrls();
    state.focusBeforeLayer = document.activeElement;
    state.action = {
      code: action.code,
      config: Object.assign({}, ACTIONS[action.code], action),
      work: work || {},
      step: 1,
      values: {},
      files: [],
      saving: false,
      idempotencyKey: makeIdempotencyKey()
    };
    renderActionDialog();
  }

  function actionContextWork() {
    return normalizeWork((state.action && state.action.work) || {});
  }

  function renderActionDialog() {
    var layer = state.root.querySelector("#tlabActionLayer");
    var action = state.action;
    var work;
    var body;
    var nextLabel;
    if (!action) { layer.hidden = true; return; }
    work = actionContextWork();
    body = action.step === 1 ? renderActionIntro(work)
      : (action.step === 2 ? renderActionFields(work) : renderActionConfirmation(work));
    nextLabel = action.step === 3 ? "Confirmar acción" : "Continuar";
    layer.innerHTML = '<section class="tlab-dialog" role="dialog" aria-modal="true" aria-labelledby="tlabActionTitle"><header class="tlab-dialog__header"><div><small>Acción guiada</small><h2 id="tlabActionTitle">' + escapeHtml(action.config.label) + '</h2></div><button type="button" class="tlab-icon-button tlab-icon-button--light" data-tlab-command="close-action" aria-label="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<form class="tlab-dialog__form" id="tlabActionForm"><div class="tlab-dialog__body"><div class="tlab-steps" aria-label="Progreso"><span class="tlab-step ' + (action.step === 1 ? "is-current" : "is-done") + '" data-step="1">Revisar</span><span class="tlab-step ' + (action.step === 2 ? "is-current" : (action.step > 2 ? "is-done" : "")) + '" data-step="2">Completar</span><span class="tlab-step ' + (action.step === 3 ? "is-current" : "") + '" data-step="3">Confirmar</span></div>' + body + '<div class="tlab-form-error" id="tlabActionError" hidden></div><div class="tlab-upload-progress" id="tlabUploadProgress" hidden><span></span></div></div>'
      + '<footer class="tlab-dialog__footer"><button type="button" class="tlab-button tlab-button--ghost" data-tlab-command="action-back" ' + (action.step === 1 ? "disabled" : "") + '><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Volver</button><div class="tlab-dialog__footer-actions"><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="close-action">Cancelar</button>'
      + (action.step === 3 ? '<button type="submit" class="tlab-button ' + (action.config.danger ? "tlab-button--danger" : "tlab-button--primary") + '" id="tlabActionSubmit">' : '<button type="button" class="tlab-button tlab-button--primary" data-tlab-command="action-next">') + '<i class="fa-solid ' + escapeAttr(action.config.icon || "fa-arrow-right") + '" aria-hidden="true"></i>' + escapeHtml(nextLabel) + '</button></div></footer></form></section>';
    layer.hidden = false;
    focusFirst(layer);
  }

  function renderActionIntro(work) {
    var action = state.action;
    var raw = action.work || {};
    return '<div class="tlab-action-context">'
      + '<div><small>Código</small><strong>' + escapeHtml(work.code) + '</strong></div>'
      + '<div><small>Paciente</small><strong>' + escapeHtml(work.patient) + '</strong></div>'
      + '<div><small>Producto</small><strong>' + escapeHtml(work.product) + '</strong></div>'
      + '</div><div class="tlab-confirm-box"><h3><i class="fa-solid ' + escapeAttr(action.config.icon || "fa-circle-check") + '" aria-hidden="true"></i> Qué se registrará</h3><p>' + escapeHtml(pick(action.config, ["ayuda", "descripcion"], actionHelp(action.code))) + '</p>'
      + (action.code === "iniciarTrabajo" && pick(raw, ["modo_individualizacion"], "") ? '<p><strong>Individualización:</strong> ' + escapeHtml(pick(raw, ["modo_individualizacion"], "").replace(/_/g, " ")) + '</p>' : '')
      + '<p><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> La fecha, el usuario y la trazabilidad serán registrados por el servidor.</p></div>';
  }

  function actionHelp(code) {
    var help = {
      iniciarTrabajo: "Se creará el ciclo Original y la primera evidencia de custodia.",
      iniciarTransferencia: "Se registrará una entrega pendiente hasta que el destinatario confirme la recepción.",
      confirmarRecepcion: "La confirmación creará un nuevo nodo y actualizará el custodio físico.",
      agregarEvidencia: "Las fotos se agregarán al ciclo actual sin reemplazar evidencias anteriores.",
      agregarNota: "La observación quedará vinculada al nodo actual sin cambiar la custodia.",
      iniciarDevolucion: "Se registrará la entrega del trabajo terminado y quedará pendiente de recepción en clínica.",
      confirmarDevolucion: "La clínica pasará a ser custodio y el trabajo quedará pendiente de revisión.",
      solicitarAjuste: "Se abrirá un nuevo ciclo de ajuste sin borrar la historia original.",
      aprobarTrabajo: "El tiempo de laboratorio quedará cerrado y el trabajo esperará su instalación.",
      registrarInstalacion: "La instalación cerrará el flujo desde la evolución clínica vinculada.",
      cancelarTrabajo: "La cancelación requiere motivo y conserva todos los eventos anteriores."
    };
    return help[code] || "La operación quedará registrada en la trazabilidad.";
  }

  function eligibleRecipients() {
    var config = state.action.config || {};
    var work = state.action.work || {};
    return asArray(config.destinatarios || config.destinatarios_permitidos || work.destinatarios_permitidos || (state.detailEnvelope && state.detailEnvelope.destinatarios_permitidos) || []);
  }

  function adjustmentReasons() {
    var config = state.action.config || {};
    return asArray(config.motivos || config.motivos_ajuste || catalogItems(["motivos_ajuste"]));
  }

  function renderActionFields(work) {
    var action = state.action;
    var config = action.config;
    var values = action.values;
    var fields = [];
    var raw = action.work || {};
    var recipients;
    var reasons;
    if (action.code === "iniciarTrabajo") {
      var tieneCatalogoContextual = Object.prototype.hasOwnProperty.call(raw, "tecnicos_disponibles")
        || Object.prototype.hasOwnProperty.call(raw, "mecanicos");
      var tecnicosInicio = asArray(raw.tecnicos_disponibles || raw.mecanicos || []);
      if (!tieneCatalogoContextual) {
        tecnicosInicio = catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"]).filter(function (item) {
          return typeof item !== "object" || item.habilitado_flujo !== false;
        });
      }
      fields.push(selectField("Mecánico dental", "cod_tecnico_usuario", tecnicosInicio, values.cod_tecnico_usuario, true, "Seleccionar técnico asignado"));
      fields.push('<div class="tlab-field"><label for="tlabActionColor">Color o colorímetro</label><input id="tlabActionColor" name="colorimetro" type="text" maxlength="30" placeholder="Ej.: A2" value="' + escapeAttr(values.colorimetro || pick(raw, ["colorimetro", "color", "color_precargado"], "")) + '"><small>Completálo cuando el tratamiento lo requiera.</small></div>');
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionInstructions">Instrucciones para el laboratorio</label><textarea id="tlabActionInstructions" name="instrucciones" maxlength="1000" placeholder="Material, diseño u otra especificación necesaria">' + escapeHtml(values.instrucciones || pick(raw, ["instrucciones", "observacion_precargada", "indicaciones"], "")) + '</textarea><small>El producto y las ubicaciones ya están vinculados; agregá solamente lo que falte.</small></div>');
    }
    if (config.recipient || boolValue(config.requiere_destinatario)) {
      recipients = eligibleRecipients();
      fields.push(selectField("Destinatario físico", "cod_destinatario", recipients, values.cod_destinatario, true, "Seleccionar destinatario autorizado"));
    }
    if (config.reason || boolValue(config.requiere_motivo)) {
      reasons = adjustmentReasons();
      fields.push(selectField("Motivo del ajuste", "motivo", reasons, values.motivo, true, "Seleccionar motivo"));
    }
    if (config.justification || boolValue(config.requiere_justificacion)) {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionJustification">Justificación</label><textarea id="tlabActionJustification" name="justificacion" required maxlength="1000" placeholder="Explicá el motivo de forma clara">' + escapeHtml(values.justificacion || "") + '</textarea><small>Este texto quedará auditado.</small></div>');
    }
    if (config.note || config.noteRequired || boolValue(config.permite_observacion)) {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionNote">' + (action.code === "agregarNota" ? "Observación" : "Indicaciones u observaciones") + '</label><textarea id="tlabActionNote" name="observacion" ' + (config.noteRequired ? "required" : "") + ' maxlength="1200" placeholder="Agregá sólo información necesaria para este trabajo">' + escapeHtml(values.observacion || pick(raw, ["observacion_precargada", "indicaciones"], "")) + '</textarea></div>');
    }
    if (boolValue(state.context.es_auditor)) {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionAuditReason">Motivo de intervención excepcional</label><textarea id="tlabActionAuditReason" name="motivo_excepcion" maxlength="750" placeholder="Completá este campo cuando actuás fuera del rol, asignación o sucursal habitual">' + escapeHtml(values.motivo_excepcion || "") + '</textarea><small>El servidor lo exigirá sólo si esta acción depende del permiso de auditoría.</small></div>');
    }
    if (action.code === "solicitarAjuste" && values.motivo && toStringSafe(values.motivo).toLowerCase() === "otro") {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionOtherReason">Descripción de “Otro”</label><input id="tlabActionOtherReason" name="motivo_otro" type="text" required maxlength="180" value="' + escapeAttr(values.motivo_otro || "") + '"></div>');
    }
    return '<div class="tlab-form-grid">' + fields.join("") + '</div>' + ((config.evidence || config.evidenceOptional || boolValue(config.requiere_evidencia) || boolValue(config.permite_evidencia)) ? renderEvidencePicker(config.evidence || boolValue(config.requiere_evidencia)) : '');
  }

  function selectField(label, name, items, selected, required, placeholder) {
    return '<div class="tlab-field"><label for="tlabAction_' + escapeAttr(name) + '">' + escapeHtml(label) + '</label><select id="tlabAction_' + escapeAttr(name) + '" name="' + escapeAttr(name) + '" ' + (required ? "required" : "") + '><option value="">' + escapeHtml(placeholder) + '</option>' + items.map(function (item) {
      var html = optionHtml(item);
      var value = typeof item === "object" ? pick(item, ["cod_tecnico_usuario", "cod_usuario", "id", "codigo", "cod", "valor", "value", "cod_persona"], "") : item;
      return toStringSafe(value) === toStringSafe(selected) ? html.replace("<option ", '<option selected ') : html;
    }).join("") + '</select></div>';
  }

  function renderEvidencePicker(required) {
    var files = state.action.files || [];
    return '<section class="tlab-evidence-box"><div class="tlab-evidence-box__heading"><div><strong>Fotografías ' + (required ? "obligatorias" : "opcionales") + '</strong><small>JPG, PNG o WEBP · máximo 10 MB por imagen.</small></div><span class="tlab-status ' + (files.length ? "tlab-status--ok" : "tlab-status--neutral") + '">' + files.length + ' de ' + MAX_FILES + '</span></div>'
      + '<div class="tlab-evidence-choices"><label class="tlab-file-choice"><i class="fa-solid fa-camera" aria-hidden="true"></i><span>Tomar foto</span><input type="file" accept="image/jpeg,image/png,image/webp" capture="environment" data-tlab-file-input aria-label="Tomar fotografía con la cámara"></label><label class="tlab-file-choice"><i class="fa-solid fa-images" aria-hidden="true"></i><span>Elegir de galería</span><input type="file" accept="image/jpeg,image/png,image/webp" multiple data-tlab-file-input aria-label="Seleccionar fotografías de la galería"></label></div>'
      + '<div class="tlab-preview-list" id="tlabPreviewList">' + files.map(previewHtml).join("") + '</div><div class="tlab-file-error" id="tlabFileError" hidden></div></section>';
  }

  function previewHtml(file, index) {
    var url = file._tlabUrl;
    if (!url) {
      url = URL.createObjectURL(file);
      file._tlabUrl = url;
      state.objectUrls.push(url);
    }
    return '<figure class="tlab-preview"><img src="' + escapeAttr(url) + '" alt="Vista previa ' + (index + 1) + '"><button type="button" data-tlab-preview-remove="' + index + '" aria-label="Quitar ' + escapeAttr(file.name || "imagen") + '"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></figure>';
  }

  function renderActionConfirmation(work) {
    var action = state.action;
    var values = action.values;
    var items = ["Trabajo: " + work.code, "Acción: " + action.config.label];
    var recipient = selectedOptionLabel("cod_destinatario");
    var mechanic = selectedOptionLabel("cod_tecnico_usuario");
    if (recipient) { items.push("Destinatario: " + recipient); }
    if (mechanic) { items.push("Mecánico: " + mechanic); }
    if (values.motivo) { items.push("Motivo: " + values.motivo + (values.motivo_otro ? " · " + values.motivo_otro : "")); }
    if (values.motivo_excepcion) { items.push("Excepción de auditoría: " + values.motivo_excepcion); }
    if (state.action.files.length) { items.push("Evidencias: " + state.action.files.length); }
    return '<div class="tlab-confirm-box"><h3>Revisá antes de confirmar</h3><ul class="tlab-confirm-list">' + items.map(function (item) { return '<li>' + escapeHtml(item) + '</li>'; }).join("") + '</ul><label class="tlab-check"><input type="checkbox" id="tlabActionConfirmed" required><span>' + escapeHtml(action.config.confirmation) + '</span></label></div>';
  }

  function selectedOptionLabel(name) {
    var value = state.action.values[name];
    var items = name === "cod_tecnico_usuario" ? catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"]) : eligibleRecipients();
    var found = items.filter(function (item) {
      var itemValue = typeof item === "object" ? pick(item, ["cod_tecnico_usuario", "cod_usuario", "id", "codigo", "cod", "valor", "value", "cod_persona"], "") : item;
      return toStringSafe(itemValue) === toStringSafe(value);
    })[0];
    if (!found) { return value || ""; }
    return typeof found === "object" ? pick(found, ["nombre", "descripcion", "etiqueta", "label"], value) : found;
  }

  function captureActionValues() {
    var form;
    if (!state.action || !state.root) { return; }
    form = state.root.querySelector("#tlabActionForm");
    if (!form) { return; }
    forEachFormValue(form, function (value, key) {
      if (key !== "evidencias[]") { state.action.values[key] = value; }
    });
  }

  function actionBack() {
    if (!state.action || state.action.saving) { return; }
    captureActionValues();
    state.action.step = Math.max(1, state.action.step - 1);
    renderActionDialog();
  }

  function actionNext() {
    var error;
    if (!state.action || state.action.saving) { return; }
    captureActionValues();
    error = validateActionStep(state.action.step);
    if (error) { showActionError(error); return; }
    state.action.step = Math.min(3, state.action.step + 1);
    renderActionDialog();
  }

  function validateActionStep(step) {
    var action = state.action;
    var config = action.config;
    var values = action.values;
    if (step < 2) { return ""; }
    if ((config.recipient || boolValue(config.requiere_destinatario)) && !values.cod_destinatario) { return "Seleccioná el destinatario físico."; }
    if ((config.mechanic || boolValue(config.requiere_mecanico)) && !values.cod_tecnico_usuario) { return "Seleccioná el mecánico responsable."; }
    if ((config.reason || boolValue(config.requiere_motivo)) && !values.motivo) { return "Seleccioná el motivo del ajuste."; }
    if ((config.justification || boolValue(config.requiere_justificacion)) && !toStringSafe(values.justificacion).trim()) { return "Escribí una justificación."; }
    if (config.noteRequired && !toStringSafe(values.observacion).trim()) { return "Escribí la observación."; }
    if (action.code === "solicitarAjuste" && toStringSafe(values.motivo).toLowerCase() === "otro" && !toStringSafe(values.motivo_otro).trim()) { return "Describí el motivo seleccionado como “Otro”."; }
    if ((config.evidence || boolValue(config.requiere_evidencia)) && !action.files.length) { return "Agregá al menos una fotografía para continuar."; }
    return "";
  }

  function showActionError(message) {
    var box = state.root.querySelector("#tlabActionError");
    if (!box) { return; }
    box.textContent = message;
    box.hidden = false;
    box.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  function addFiles(fileList) {
    var files = Array.prototype.slice.call(fileList || []);
    var error = "";
    if (!state.action) { return; }
    files.forEach(function (file) {
      if (state.action.files.length >= MAX_FILES) { error = "Podés adjuntar hasta " + MAX_FILES + " fotografías por acción."; return; }
      if (!IMAGE_TYPES[file.type]) { error = "Sólo se admiten imágenes JPG, PNG o WEBP."; return; }
      if (file.size > MAX_FILE_SIZE) { error = "Cada fotografía debe pesar como máximo 10 MB."; return; }
      state.action.files.push(file);
    });
    renderActionDialog();
    if (error) {
      var box = state.root.querySelector("#tlabFileError");
      if (box) { box.textContent = error; box.hidden = false; }
    }
  }

  function removePreview(index) {
    var file;
    if (!state.action || index < 0 || index >= state.action.files.length) { return; }
    file = state.action.files[index];
    if (file._tlabUrl) {
      URL.revokeObjectURL(file._tlabUrl);
      state.objectUrls = state.objectUrls.filter(function (url) { return url !== file._tlabUrl; });
    }
    state.action.files.splice(index, 1);
    renderActionDialog();
  }

  function revokeObjectUrls() {
    state.objectUrls.forEach(function (url) { try { URL.revokeObjectURL(url); } catch (ignore) {} });
    state.objectUrls = [];
  }

  function closeAction() {
    var layer;
    if (!state.root) { return; }
    layer = state.root.querySelector("#tlabActionLayer");
    layer.hidden = true;
    layer.innerHTML = "";
    revokeObjectUrls();
    state.action = null;
  }

  function actionPayload() {
    var work = state.action.work || {};
    var detalle = work.detalle || {};
    var values = state.action.values || {};
    var payload = {
      id_trabajo: pick(work, ["id_trabajo", "cod_trabajo_laboratorio", "id", "cod_trabajo"], state.detailId),
      cod_trabajo_laboratorio: pick(work, ["cod_trabajo_laboratorio", "id_trabajo", "id", "cod_trabajo"], state.detailId),
      version_esperada: pick(work, ["version", "version_registro"], (state.detailEnvelope && state.detailEnvelope.version) || ""),
      clave_idempotencia: state.action.idempotencyKey,
      cod_destinatario: values.cod_destinatario || "",
      cod_tecnico_usuario: values.cod_tecnico_usuario || "",
      motivo: values.motivo || "",
      motivo_otro: values.motivo_otro || "",
      motivo_excepcion: values.motivo_excepcion || "",
      justificacion: values.justificacion || "",
      observacion: values.observacion || (state.action.code === "iniciarTrabajo" ? (values.instrucciones || "") : "")
    };
    if (state.action.code === "iniciarTrabajo") {
      payload.cod_detalle_venta = pick(work, ["cod_detalle_venta", "id_detalle_venta", "sale_item_id"], pick(detalle, ["cod_detalle_venta", "id_detalle_venta"], ""));
      payload.cod_venta = pick(work, ["cod_venta", "sale_id"], pick(detalle, ["cod_venta", "sale_id"], ""));
      payload.cod_producto = pick(work, ["cod_producto", "product_id"], pick(detalle, ["cod_producto", "product_id"], ""));
      payload.cod_tratamiento = pick(work, ["cod_tratamiento", "treatment_id"], "");
      payload.cod_evolucion = pick(work, ["cod_evolucion", "clinical_evolution_id"], "");
      payload.cod_consulta_origen = pick(work, ["cod_consulta_origen"], state.moduleOptions.cod_consulta_origen || "");
      payload.cod_evolucion_origen = pick(work, ["cod_evolucion_origen", "cod_evolucion"], state.moduleOptions.cod_evolucion_origen || "");
      payload.modo_individualizacion = pick(work, ["modo_individualizacion"], pick(detalle, ["modo_individualizacion"], ""));
      payload.piezas_json = pick(work, ["piezas_json", "ubicaciones"], "");
      payload.colorimetro = values.colorimetro || "";
      payload.instrucciones = values.instrucciones || "";
    }
    if (state.action.code === "registrarInstalacion") {
      payload.cod_consulta_origen = state.moduleOptions.cod_consulta_origen || pick(work, ["cod_consulta_origen"], "");
      payload.cod_evolucion_origen = state.moduleOptions.cod_evolucion_origen || pick(work, ["cod_evolucion_origen", "cod_evolucion"], "");
    }
    return payload;
  }

  function prepareActionSubmission() {
    var action = state.action;
    var payload = actionPayload();
    var files = action.files.slice(0);
    if (action.code !== "iniciarTrabajo" || !files.length) {
      return Promise.resolve({ payload: payload, files: files });
    }
    return new Promise(function (resolve, reject) {
      var initialFile = files.shift();
      var reader = new FileReader();
      reader.onload = function (event) {
        payload.evidencia_inicial = {
          data_base64: event.target.result || "",
          nombre_archivo: initialFile.name || "evidencia-inicial.jpg",
          descripcion: state.action.values.observacion || "Evidencia inicial del trabajo"
        };
        resolve({ payload: payload, files: files });
      };
      reader.onerror = function () {
        reject(new Error("No se pudo preparar la evidencia inicial. Volvé a seleccionar la fotografía."));
      };
      reader.readAsDataURL(initialFile);
    });
  }

  function submitAction() {
    var action = state.action;
    var confirmed = state.root.querySelector("#tlabActionConfirmed");
    var submit = state.root.querySelector("#tlabActionSubmit");
    var progress = state.root.querySelector("#tlabUploadProgress");
    if (!action || action.saving) { return; }
    if (!confirmed || !confirmed.checked) { showActionError("Confirmá la declaración antes de guardar."); return; }
    action.saving = true;
    if (submit) { submit.disabled = true; submit.innerHTML = '<i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Guardando...'; }
    if (progress && action.files.length) { progress.hidden = false; }
    prepareActionSubmission().then(function (prepared) {
      return request(action.code, prepared.payload, prepared.files, function (percent) {
        var bar = state.root.querySelector("#tlabUploadProgress span");
        if (bar) { bar.style.width = percent + "%"; }
      });
    }).then(function (response) {
      var message = response.message || "La acción quedó registrada en la trazabilidad.";
      var workId = pick(response.data, ["id_trabajo", "cod_trabajo_laboratorio"], state.detailId);
      closeAction();
      notify(message, "success");
      loadSummary().then(null, function () {});
      loadWorks(false);
      if (workId) { openDetail(workId, true); }
    }).then(null, function (error) {
      action.saving = false;
      if (submit) { submit.disabled = false; submit.innerHTML = '<i class="fa-solid ' + escapeAttr(action.config.icon || "fa-arrow-right") + '" aria-hidden="true"></i>Confirmar acción'; }
      showActionError(error.message + (error.code && /CONFLICT|VERSION/i.test(error.code) ? " Actualizá el trabajo antes de volver a confirmar." : ""));
    });
  }

  function openViewer(url, caption) {
    var layer;
    if (!url || !state.root) { return; }
    state.focusBeforeLayer = document.activeElement;
    layer = state.root.querySelector("#tlabViewerLayer");
    layer.innerHTML = '<figure class="tlab-viewer" role="dialog" aria-modal="true" aria-label="Evidencia ampliada"><button type="button" class="tlab-icon-button" data-tlab-command="close-viewer" aria-label="Cerrar imagen"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button><img src="' + escapeAttr(url) + '" alt="' + escapeAttr(caption || "Evidencia del trabajo") + '"><figcaption class="tlab-viewer__caption">' + escapeHtml(caption || "Evidencia del trabajo") + '</figcaption></figure>';
    layer.hidden = false;
    focusFirst(layer);
  }

  function openAuthorizedMedia(mediaId, caption) {
    if (!mediaId) { return; }
    notify("Cargando evidencia protegida...", "info");
    request("descargarMedia", { id_media: mediaId }).then(function (response) {
      var media = response.data.media || response.data;
      var encoded = pick(media, ["data_base64", "base64"], "");
      var mime = pick(media, ["mime"], "image/jpeg");
      if (!encoded) { throw new Error("La evidencia protegida no está disponible."); }
      openViewer("data:" + mime + ";base64," + encoded, caption || pick(media, ["nombre"], "Evidencia del trabajo"));
    }).then(null, function (error) {
      notify(error.message, "error");
    });
  }

  function ensureThreadForDetail(detailId, options) {
    options = options || {};
    if (!detailId) { return Promise.reject(new Error("No se pudo identificar el tratamiento.")); }
    if (!options.hilo_confirmado && !window.confirm(
      "Esta venta histórica todavía no tiene su Hilo maestro vinculado. ¿Desea prepararlo ahora para continuar con el trabajo de laboratorio?"
    )) {
      return Promise.resolve(false);
    }
    notify("Preparando el Hilo maestro sin duplicar el seguimiento...", "info");
    return request("asegurarHiloDetalle", { cod_detalle_venta: detailId }).then(function (response) {
      notify(response.message || "El Hilo maestro quedó preparado.", "success");
      return openFromSaleDetail(detailId, options);
    }).then(null, function (error) {
      notify(error.message, "error");
      return false;
    });
  }

  function closeViewer() {
    var layer;
    if (!state.root) { return; }
    layer = state.root.querySelector("#tlabViewerLayer");
    layer.hidden = true;
    layer.innerHTML = "";
  }

  function openFromSaleDetail(detailId, options) {
    options = options || {};
    ensureDom();
    openModule(options);
    state.root.querySelector("#tlabResults").innerHTML = '<div class="tlab-results-state">' + loaderHtml("Preparando datos del tratamiento...", "content") + '</div>';
    return request("obtenerContextoDetalle", { cod_detalle_venta: detailId }).then(function (response) {
      var context = response.data.contexto || response.data;
      var actions = normalizeActions(response.data.acciones_permitidas || context.acciones_permitidas || []);
      var start = actions.filter(function (item) { return item.code === "iniciarTrabajo"; })[0];
      if (context.trabajo_activo && context.trabajo_activo.id) {
        notify("Este tratamiento ya tiene un trabajo activo. Se abrirá su trazabilidad.", "info");
        return openDetail(context.trabajo_activo.id);
      }
      if (!start) {
        if (boolValue(context.puede_asegurar_hilo || response.data.puede_asegurar_hilo)) {
          return ensureThreadForDetail(detailId, options);
        }
        throw new Error(response.data.mensaje_contexto || "Este tratamiento no habilita el inicio de un trabajo de laboratorio.");
      }
      context.cod_detalle_venta = context.cod_detalle_venta || detailId;
      context.cod_consulta_origen = context.cod_consulta_origen || options.cod_consulta_origen || "";
      context.cod_evolucion_origen = context.cod_evolucion_origen || options.cod_evolucion_origen || "";
      state.startContext = context;
      openAction("iniciarTrabajo", start, context);
      return response;
    }).then(null, function (error) {
      notify(error.message, "error");
      return false;
    });
  }

  function openWorkAction(id, actionCode, options) {
    options = options || {};
    ensureDom();
    openModule(options);
    return openDetail(id, true).then(function () {
      var action = actionForCode(actionCode);
      if (!state.detail || !action) {
        notify("La acción solicitada no está disponible para este usuario o para la situación actual.", "error");
        return false;
      }
      openAction(actionCode, action, state.detail);
      return true;
    });
  }

  window.abrirTrabajoLaboratorio = openModule;
  window.cerrarTrabajoLaboratorio = closeModule;
  window.verCerrarTrabajoLaboratorio = function (mostrar, options) {
    if (mostrar === false || mostrar === "" || mostrar === 0 || mostrar === "0") { closeModule(); }
    else { openModule(options); }
  };
  window.TrabajoLaboratorio = {
    abrir: openModule,
    cerrar: closeModule,
    actualizar: refreshAll,
    abrirTrabajo: function (id, options) { openModule(options); return openDetail(id); },
    abrirDesdeDetalleVenta: openFromSaleDetail,
    asegurarHiloDetalle: ensureThreadForDetail,
    abrirAccionTrabajo: openWorkAction,
    registrarInstalacion: function (id, options) { return openWorkAction(id, "registrarInstalacion", options); },
    endpoint: ENDPOINT
  };

}(window, document));

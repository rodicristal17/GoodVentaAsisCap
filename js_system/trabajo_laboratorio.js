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
 *   listarTrabajos, listarImpresionTecnica, obtenerTrabajo, obtenerResumen, obtenerCatalogos,
 *   obtenerContextoDetalle, iniciarTrabajo, asignarTecnico, iniciarTransferencia,
 *   tomarHilo, registrarNovedad, rectificarCustodia, agregarEvidencia,
 *   agregarNota, iniciarDevolucion, solicitarAjuste, aprobarTrabajo,
 *   registrarInstalacion y cancelarTrabajo. La bandeja histórica consume
 *   listarHistoricos, obtenerHistorico y resolverHistorico.
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
  var STYLE_URL = "/GoodVentaAsisCap/css_system/trabajo_laboratorio.css?v=20260819-direct-custody-1";
  var BRAND_MARK = "/GoodVentaAsisCap/iconos/telar-loader.svg?v=20260721-2";
  var ROOT_ID = "telarTrabajoLaboratorio";
  var PAGE_SIZE = 18;
  var CATALOG_CACHE_MS = 5 * 60 * 1000;
  var MAX_FILES = 3;
  var MAX_FILE_SIZE = 2 * 1024 * 1024;
  var IMAGE_TARGET_SIZE = 1536 * 1024;
  var IMAGE_MAX_DIMENSION = 1920;
  var IMAGE_INPUT_MAX_SIZE = 30 * 1024 * 1024;
  var IMAGE_INPUT_MAX_PIXELS = 40000000;
  var IMAGE_TYPES = { "image/jpeg": true, "image/png": true, "image/webp": true };
  var DOCUMENT_TYPES = { "application/pdf": true };

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
      requiere_mecanico: false,
      confirmation: "Confirmo que los datos corresponden al tratamiento y que la evidencia inicial es correcta."
    },
    iniciarTrabajosAgrupados: {
      label: "Iniciar trabajos independientes",
      icon: "fa-diagram-project",
      evidence: true,
      requiere_evidencia: true,
      requiere_mecanico: false,
      confirmation: "Confirmo las ubicaciones, el origen compartido y la preparacion de todos los trabajos independientes."
    },
    asignarTecnico: {
      label: "Asignar técnico",
      icon: "fa-user-gear",
      mechanic: true,
      confirmation: "Confirmo el técnico para este trabajo y los trabajos pendientes del mismo código de origen."
    },
    iniciarTransferencia: {
      label: "Registrar salida (opcional)",
      icon: "fa-arrow-right-arrow-left",
      evidence: true,
      note: true,
      confirmation: "Confirmo que deseo dejar constancia de la salida. La custodia seguirá a mi nombre hasta que otra persona tome el hilo."
    },
    tomarHilo: {
      label: "Tomar el hilo",
      icon: "fa-hand-holding",
      custodyReceipt: true,
      evidence: true,
      permite_excepcion_foto: false,
      submitLabel: "Confirmar y tomar el hilo",
      confirmation: "Confirmo que recibí físicamente este trabajo y asumo su custodia en Telar."
    },
    registrarNovedad: {
      label: "Registrar novedad",
      icon: "fa-pen-to-square",
      novelty: true,
      noteRequired: true,
      evidenceOptional: true,
      documents: true,
      submitLabel: "Guardar novedad",
      confirmation: "Confirmo que esta novedad corresponde al trabajo que tengo bajo mi custodia."
    },
    rectificarCustodia: {
      label: "Rectificar custodia",
      icon: "fa-user-shield",
      custodyCorrection: true,
      justification: true,
      danger: true,
      submitLabel: "Confirmar rectificación",
      confirmation: "Confirmo que esta rectificación es excepcional, necesaria y que el motivo quedará auditado."
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
      label: "Instalado y finalizado",
      icon: "fa-tooth",
      evidence: true,
      submitLabel: "Confirmar instalación y finalización",
      confirmation: "Confirmo que el trabajo fue instalado y finalizado, y que la evidencia o excepción declarada corresponde a este tratamiento."
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
    iniciar_trabajos_agrupados: "iniciarTrabajosAgrupados",
    asignar_tecnico: "asignarTecnico",
    assign_technician: "asignarTecnico",
    iniciar_transferencia: "iniciarTransferencia",
    entregar: "iniciarTransferencia",
    confirmar_recepcion: "tomarHilo",
    confirmarRecepcion: "tomarHilo",
    tomar_hilo: "tomarHilo",
    registrar_novedad: "registrarNovedad",
    registrar_novedad_custodia: "registrarNovedad",
    registrarNovedadCustodia: "registrarNovedad",
    rectificar_custodia: "rectificarCustodia",
    add_evidence: "agregarEvidencia",
    agregar_evidencia: "agregarEvidencia",
    add_note: "agregarNota",
    agregar_nota: "agregarNota",
    iniciar_devolucion: "iniciarDevolucion",
    devolver: "iniciarDevolucion",
    confirmar_devolucion: "tomarHilo",
    confirmarDevolucion: "tomarHilo",
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
    printing: false,
    listRequest: 0,
    page: 1,
    hasMore: false,
    works: [],
    group: "pendientes_entrega",
    view: "operativa",
    mechanicTray: "por_recibir",
    summary: {},
    historicalSummary: {},
    catalogs: {},
    catalogsLoadedAt: 0,
    catalogsLoading: null,
    mediaCache: {},
    mediaRequests: {},
    thumbnailObserver: null,
    context: {},
    filtersOpen: false,
    detail: null,
    detailEnvelope: null,
    detailId: "",
    detailKind: "",
    detailTab: "timeline",
    detailReturnFocus: null,
    detailRequest: 0,
    detailError: "",
    historicals: [],
    historicalDetail: null,
    historicalEnvelope: null,
    historicalWizard: null,
    historicalResolver: null,
    action: null,
    focusBeforeLayer: null,
    searchTimer: null,
    startContext: null,
    moduleOptions: {},
    objectUrls: [],
    nodePopover: null,
    nodePopoverPinned: false,
    popoverCloseTimer: null,
    nodePopoverRecord: null,
    nodeDetailCache: {},
    nodeEditor: null,
    nodeFiles: [],
    nodeObjectUrls: [],
    nodeFilesProcessing: false,
    camera: null,
    cameraRequest: 0
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

  function formatDurationSeconds(value) {
    var total = Math.max(0, Math.floor(numberValue(value, 0)));
    var days = Math.floor(total / 86400);
    var hours = Math.floor((total % 86400) / 3600);
    var minutes = Math.floor((total % 3600) / 60);
    if (days) { return days + (days === 1 ? " día" : " días") + (hours ? " " + hours + " h" : ""); }
    if (hours) { return hours + " h" + (minutes ? " " + minutes + " min" : ""); }
    return Math.max(1, minutes) + " min";
  }

  function formatFileLimit(bytes) {
    var megabytes = Math.max(0.1, numberValue(bytes, MAX_FILE_SIZE) / (1024 * 1024));
    return (Math.abs(megabytes - Math.round(megabytes)) < 0.01
      ? Math.round(megabytes).toString()
      : megabytes.toFixed(1).replace(".", ",")) + " MB";
  }

  function mediaFileName(file, fallback) {
    return toStringSafe(file && file.name).trim() || fallback || "fotografia.jpg";
  }

  function imageMimeFromFile(file) {
    var type = toStringSafe(file && file.type).toLowerCase().split(";")[0];
    var name = mediaFileName(file, "").toLowerCase();
    if (IMAGE_TYPES[type]) { return type; }
    if (/\.jpe?g$/.test(name)) { return "image/jpeg"; }
    if (/\.png$/.test(name)) { return "image/png"; }
    if (/\.webp$/.test(name)) { return "image/webp"; }
    return type;
  }

  function readBlobAsArrayBuffer(blob) {
    return new Promise(function (resolve, reject) {
      var reader = new FileReader();
      reader.onload = function (event) { resolve(event.target.result); };
      reader.onerror = function () {
        reject(new Error("La cámara no devolvió una fotografía legible. Volvé a tomarla o elegila de la galería."));
      };
      reader.onabort = function () {
        reject(new Error("La selección de la fotografía se interrumpió. Podés intentarlo nuevamente sin perder los datos."));
      };
      reader.readAsArrayBuffer(blob);
    });
  }

  function readBlobAsDataUrl(blob) {
    return new Promise(function (resolve, reject) {
      var reader = new FileReader();
      reader.onload = function (event) { resolve(event.target.result || ""); };
      reader.onerror = function () { reject(new Error("No se pudo preparar la fotografía para guardarla.")); };
      reader.readAsDataURL(blob);
    });
  }

  function namedMediaBlob(blob, name, type, metadata) {
    var named;
    var properties = metadata || {};
    try {
      named = new File([blob], name, { type: type || blob.type, lastModified: Date.now() });
    } catch (ignore) {
      named = new Blob([blob], { type: type || blob.type });
      try { named.name = name; } catch (ignoreName) {}
    }
    Object.keys(properties).forEach(function (key) {
      try { named[key] = properties[key]; } catch (ignoreProperty) {}
    });
    return named;
  }

  function loadImageBlob(blob) {
    return new Promise(function (resolve, reject) {
      var image = new Image();
      var url = URL.createObjectURL(blob);
      image.onload = function () {
        URL.revokeObjectURL(url);
        resolve(image);
      };
      image.onerror = function () {
        URL.revokeObjectURL(url);
        reject(new Error("La fotografía no pudo abrirse. Usá JPG, PNG o WEBP, o volvé a tomarla."));
      };
      image.src = url;
    });
  }

  function canvasToJpeg(canvas, quality) {
    return new Promise(function (resolve, reject) {
      if (canvas.toBlob) {
        canvas.toBlob(function (blob) {
          if (blob) { resolve(blob); }
          else { reject(new Error("No se pudo optimizar la fotografía en este dispositivo.")); }
        }, "image/jpeg", quality);
        return;
      }
      try {
        var dataUrl = canvas.toDataURL("image/jpeg", quality);
        var encoded = dataUrl.split(",")[1] || "";
        var binary = window.atob(encoded);
        var bytes = new Uint8Array(binary.length);
        var i;
        for (i = 0; i < binary.length; i += 1) { bytes[i] = binary.charCodeAt(i); }
        resolve(new Blob([bytes], { type: "image/jpeg" }));
      } catch (error) {
        reject(new Error("No se pudo optimizar la fotografía en este dispositivo."));
      }
    });
  }

  function encodeCanvasWithinLimit(canvas, targetBytes, qualityIndex) {
    var qualities = [0.88, 0.80, 0.72, 0.64, 0.56];
    var index = qualityIndex || 0;
    return canvasToJpeg(canvas, qualities[index]).then(function (blob) {
      var smaller;
      var context;
      if (blob.size <= targetBytes || index >= qualities.length - 1) {
        if (blob.size <= MAX_FILE_SIZE) { return blob; }
        if (canvas.width <= 960 && canvas.height <= 960) {
          throw new Error("La fotografía sigue siendo demasiado pesada después de optimizarla. Volvé a tomarla con menos zoom.");
        }
        smaller = document.createElement("canvas");
        smaller.width = Math.max(1, Math.round(canvas.width * 0.78));
        smaller.height = Math.max(1, Math.round(canvas.height * 0.78));
        context = smaller.getContext("2d");
        context.fillStyle = "#ffffff";
        context.fillRect(0, 0, smaller.width, smaller.height);
        context.drawImage(canvas, 0, 0, smaller.width, smaller.height);
        return encodeCanvasWithinLimit(smaller, targetBytes, 0);
      }
      return encodeCanvasWithinLimit(canvas, targetBytes, index + 1);
    });
  }

  function preparedImageFromRecord(record) {
    var source = new Blob([record.buffer], { type: record.mime });
    return loadImageBlob(source).then(function (image) {
      var width = image.naturalWidth || image.width;
      var height = image.naturalHeight || image.height;
      var pixels = width * height;
      var scale;
      var canvas;
      var context;
      var targetBytes = Math.max(64 * 1024, Math.min(IMAGE_TARGET_SIZE, Math.floor(MAX_FILE_SIZE * 0.82)));
      var needsOptimization = source.size > targetBytes || Math.max(width, height) > IMAGE_MAX_DIMENSION;
      if (!width || !height || pixels > IMAGE_INPUT_MAX_PIXELS) {
        throw new Error("La resolución de la fotografía es demasiado alta para procesarla de forma segura. Elegí una foto normal, no una panorámica.");
      }
      if (!needsOptimization) {
        return { blob: source, type: record.mime, width: width, height: height, optimized: false };
      }
      scale = Math.min(1, IMAGE_MAX_DIMENSION / Math.max(width, height));
      canvas = document.createElement("canvas");
      canvas.width = Math.max(1, Math.round(width * scale));
      canvas.height = Math.max(1, Math.round(height * scale));
      context = canvas.getContext("2d");
      if (!context) { throw new Error("La tablet no pudo preparar la fotografía. Probá nuevamente o elegila de la galería."); }
      context.fillStyle = "#ffffff";
      context.fillRect(0, 0, canvas.width, canvas.height);
      context.drawImage(image, 0, 0, canvas.width, canvas.height);
      return encodeCanvasWithinLimit(canvas, targetBytes, 0).then(function (blob) {
        return { blob: blob, type: "image/jpeg", width: canvas.width, height: canvas.height, optimized: true };
      });
    }).then(function (prepared) {
      var originalName = mediaFileName(record.file, "fotografia.jpg");
      var outputName = prepared.optimized
        ? originalName.replace(/\.[^.]+$/, "") + ".jpg"
        : originalName;
      var named = namedMediaBlob(prepared.blob, outputName, prepared.type, {
        _tlabOriginalName: originalName,
        _tlabOriginalSize: record.file.size,
        _tlabWidth: prepared.width,
        _tlabHeight: prepared.height,
        _tlabOptimized: prepared.optimized
      });
      return readBlobAsDataUrl(named).then(function (dataUrl) {
        try { named._tlabDataUrl = dataUrl; } catch (ignore) {}
        return named;
      });
    });
  }

  function preparedDocumentFromRecord(record) {
    var blob = new Blob([record.buffer], { type: "application/pdf" });
    if (blob.size > MAX_FILE_SIZE) {
      return Promise.reject(new Error("El documento supera " + formatFileLimit(MAX_FILE_SIZE) + ". Las fotografías sí se optimizan automáticamente; los PDF deben respetar ese límite."));
    }
    return Promise.resolve(namedMediaBlob(blob, mediaFileName(record.file, "documento.pdf"), "application/pdf"));
  }

  function readSelectedMedia(file, allowDocuments) {
    var mime = imageMimeFromFile(file);
    var isDocument = toStringSafe(file && file.type).toLowerCase() === "application/pdf"
      || /\.pdf$/i.test(mediaFileName(file, ""));
    if (isDocument && !allowDocuments) {
      return Promise.resolve({ error: "En esta acción se requieren fotografías JPG, PNG o WEBP." });
    }
    if (!isDocument && !IMAGE_TYPES[mime]) {
      return Promise.resolve({ error: allowDocuments
        ? "Usá fotografías JPG, PNG o WEBP, o un documento PDF."
        : "Usá una fotografía JPG, PNG o WEBP. Si la cámara guarda HEIC, elegí JPG en sus ajustes." });
    }
    if (!isDocument && numberValue(file.size, 0) > IMAGE_INPUT_MAX_SIZE) {
      return Promise.resolve({ error: "La fotografía es excepcionalmente grande y la tablet no puede procesarla de forma segura. Volvé a tomarla con resolución normal." });
    }
    if (isDocument && numberValue(file.size, 0) > MAX_FILE_SIZE) {
      return Promise.resolve({ error: "El documento supera " + formatFileLimit(MAX_FILE_SIZE) + "." });
    }
    return readBlobAsArrayBuffer(file).then(function (buffer) {
      if (!buffer || !buffer.byteLength) { throw new Error("La fotografía seleccionada está vacía."); }
      return { file: file, buffer: buffer, mime: isDocument ? "application/pdf" : mime, document: isDocument };
    }).then(null, function (error) {
      return { error: error.message || "No se pudo leer el archivo seleccionado." };
    });
  }

  function prepareMediaSelection(fileList, allowDocuments, availableSlots) {
    var selected = Array.prototype.slice.call(fileList || []);
    var overflow = selected.length > availableSlots;
    var reads = selected.slice(0, Math.max(0, availableSlots)).map(function (file) {
      return readSelectedMedia(file, allowDocuments);
    });
    return Promise.all(reads).then(function (records) {
      var result = { files: [], error: overflow ? "Podés adjuntar hasta " + MAX_FILES + " archivos por acción." : "" };
      var chain = Promise.resolve();
      records.forEach(function (record) {
        chain = chain.then(function () {
          if (record.error) {
            if (!result.error) { result.error = record.error; }
            return;
          }
          return (record.document ? preparedDocumentFromRecord(record) : preparedImageFromRecord(record)).then(function (file) {
            result.files.push(file);
          }).then(null, function (error) {
            if (!result.error) { result.error = error.message || "No se pudo preparar uno de los archivos."; }
          });
        });
      });
      return chain.then(function () { return result; });
    });
  }

  function releasePreparedMedia(files) {
    asArray(files).forEach(function (file) {
      var url = file && (file._tlabUrl || file._tlabNodeUrl);
      if (url) { try { URL.revokeObjectURL(url); } catch (ignore) {} }
    });
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

  function personFromRecord(primary, source, nameKeys, roleKeys, avatarKeys, roleFallback) {
    var base = person(primary || pick(source, nameKeys, ""), roleFallback);
    return {
      nombre: pick(source, nameKeys, base.name),
      rol: pick(source, roleKeys, base.role || roleFallback),
      avatar_url: pick(source, avatarKeys, base.avatar)
    };
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

  function appendPayload(formData, payload, incluirVacios) {
    Object.keys(payload || {}).forEach(function (key) {
      var value = payload[key];
      if (value === null || typeof value === "undefined" || (value === "" && !incluirVacios)) { return; }
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
      appendPayload(
        formData,
        payload || {},
        action === "convalidarHistorico" || action === "rectificarHistorico"
          || action === "resolverHistorico"
      );
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

  function cameraTargetAvailable(target) {
    if (target === "action") { return !!state.action; }
    return !!(state.nodeEditor || state.historicalResolver);
  }

  function stopCameraStream(stream) {
    if (!stream || !stream.getTracks) { return; }
    stream.getTracks().forEach(function (track) {
      try { track.stop(); } catch (ignore) {}
    });
  }

  function closeCamera(returnFocus) {
    var camera = state.camera;
    var layer = state.root && state.root.querySelector("#tlabCameraLayer");
    state.cameraRequest += 1;
    state.camera = null;
    if (camera) { stopCameraStream(camera.stream); }
    if (layer) {
      layer.hidden = true;
      layer.innerHTML = "";
    }
    if (returnFocus !== false && camera && camera.returnFocus
        && document.documentElement.contains(camera.returnFocus)
        && typeof camera.returnFocus.focus === "function") {
      camera.returnFocus.focus();
    }
  }

  function cameraFallbackInputHtml(target) {
    var attribute = target === "node" ? "data-tlab-node-file-input" : "data-tlab-file-input";
    return '<label class="tlab-camera-fallback"><i class="fa-solid fa-camera" aria-hidden="true"></i><span><strong>Usar cámara del dispositivo</strong><small>Telar conservará este formulario y preparará la foto apenas regreses.</small></span><input type="file" accept="image/jpeg,image/png,image/webp,image/*" capture="environment" ' + attribute + ' aria-label="Abrir la cámara del dispositivo"></label>';
  }

  function renderCameraLayer() {
    var camera = state.camera;
    var layer = state.root && state.root.querySelector("#tlabCameraLayer");
    var content;
    var footer;
    if (!layer || !camera) {
      if (layer) { layer.hidden = true; layer.innerHTML = ""; }
      return;
    }
    if (camera.error) {
      content = '<div class="tlab-camera-state tlab-camera-state--error"><i class="fa-solid fa-camera-rotate" aria-hidden="true"></i><strong>No se pudo mantener la cámara dentro de Telar</strong><p>' + escapeHtml(camera.error) + '</p>' + cameraFallbackInputHtml(camera.target) + '<label class="tlab-camera-gallery"><i class="fa-solid fa-images" aria-hidden="true"></i><span>Elegir de galería</span><input type="file" accept="image/jpeg,image/png,image/webp,image/*" ' + (camera.target === "node" ? "data-tlab-node-file-input" : "data-tlab-file-input") + '></label></div>';
      footer = '<button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="close-camera">Volver al formulario</button>';
    } else if (camera.starting) {
      content = '<div class="tlab-camera-state">' + loaderHtml("Preparando la cámara...", "compact") + '<p>Si Android solicita permiso, elegí Permitir mientras usás Telar.</p></div>';
      footer = '<button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="close-camera">Cancelar</button>';
    } else {
      content = '<div class="tlab-camera-viewport"><video id="tlabCameraPreview" autoplay muted playsinline aria-label="Vista de la cámara"></video><span><i class="fa-solid fa-circle" aria-hidden="true"></i>Cámara lista</span></div>';
      footer = '<button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="close-camera">Cancelar</button><button type="button" class="tlab-button tlab-button--ghost" data-tlab-command="switch-camera"><i class="fa-solid fa-camera-rotate" aria-hidden="true"></i>Cambiar cámara</button><button type="button" class="tlab-button tlab-button--primary" data-tlab-command="capture-camera"><i class="fa-solid fa-camera" aria-hidden="true"></i>Tomar fotografía</button>';
    }
    layer.innerHTML = '<section class="tlab-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="tlabCameraTitle"><header><div><small>Evidencia fotográfica</small><h2 id="tlabCameraTitle">Tomar foto sin salir de Telar</h2></div><button type="button" class="tlab-icon-button tlab-icon-button--light" data-tlab-command="close-camera" aria-label="Cerrar cámara"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header><div class="tlab-camera-dialog__body">' + content + '</div><footer>' + footer + '</footer></section>';
    layer.hidden = false;
    if (camera.stream && !camera.starting && !camera.error) {
      window.setTimeout(function () {
        var video = state.root && state.root.querySelector("#tlabCameraPreview");
        if (!video || state.camera !== camera) { return; }
        try {
          video.srcObject = camera.stream;
          var playResult = video.play();
          if (playResult && playResult.then) { playResult.then(null, function () {}); }
        } catch (error) {
          camera.error = "Android no pudo mostrar la vista previa. Podés continuar con la cámara del dispositivo o la galería.";
          stopCameraStream(camera.stream);
          camera.stream = null;
          renderCameraLayer();
        }
      }, 0);
    }
  }

  function cameraErrorMessage(error) {
    var name = toStringSafe(error && error.name);
    if (name === "NotAllowedError" || name === "PermissionDeniedError") {
      return "El permiso de cámara está bloqueado. Podés habilitarlo en el candado del navegador o continuar con la cámara del dispositivo.";
    }
    if (name === "NotFoundError" || name === "DevicesNotFoundError") {
      return "Android no informó una cámara disponible al navegador.";
    }
    if (name === "NotReadableError" || name === "TrackStartError") {
      return "Otra aplicación está usando la cámara. Cerrala y probá nuevamente, o usá la cámara del dispositivo.";
    }
    return "La cámara integrada no está disponible en esta conexión. Podés continuar sin perder los datos usando la cámara del dispositivo o la galería.";
  }

  function requestCameraStream(camera, requestId, genericConstraints) {
    var constraints = genericConstraints
      ? { video: true, audio: false }
      : { video: { facingMode: { ideal: camera.facing }, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false };
    navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
      var current = state.camera;
      if (!current || current !== camera || requestId !== state.cameraRequest || !cameraTargetAvailable(camera.target)) {
        stopCameraStream(stream);
        return;
      }
      camera.stream = stream;
      camera.starting = false;
      camera.error = "";
      renderCameraLayer();
    }).then(null, function (error) {
      var name = toStringSafe(error && error.name);
      if (!genericConstraints && (name === "OverconstrainedError" || name === "ConstraintNotSatisfiedError")) {
        requestCameraStream(camera, requestId, true);
        return;
      }
      if (state.camera !== camera || requestId !== state.cameraRequest) { return; }
      camera.starting = false;
      camera.error = cameraErrorMessage(error);
      renderCameraLayer();
    });
  }

  function openCamera(target) {
    var used = target === "action" && state.action ? state.action.files.length : state.nodeFiles.length;
    var camera;
    if (!cameraTargetAvailable(target)) { return; }
    /* La cámara integrada también puede redibujar el formulario al regresar.
       Guardamos sus valores antes de abrirla, igual que en el selector externo. */
    if (target === "action") {
      captureActionValues();
    } else if (state.historicalResolver) {
      captureHistoricalResolverValues();
    } else if (state.nodeEditor) {
      state.nodeEditor.values = Object.assign({}, state.nodeEditor.values, nodeFormValues());
    }
    if ((target === "action" && state.action.filesProcessing) || (target === "node" && state.nodeFilesProcessing)) {
      notify("Telar todavía está preparando la fotografía anterior.", "info");
      return;
    }
    if (used >= MAX_FILES) {
      notify("Ya alcanzaste el máximo de " + MAX_FILES + " fotografías.", "info");
      return;
    }
    closeCamera(false);
    camera = {
      target: target,
      facing: "environment",
      starting: true,
      capturing: false,
      error: "",
      stream: null,
      returnFocus: document.activeElement
    };
    state.camera = camera;
    state.cameraRequest += 1;
    renderCameraLayer();
    if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      camera.starting = false;
      camera.error = "Esta tablet no autorizó la cámara dentro de la página. El formulario seguirá abierto mientras usás la cámara del dispositivo.";
      renderCameraLayer();
      return;
    }
    requestCameraStream(camera, state.cameraRequest, false);
  }

  function switchCamera() {
    var camera = state.camera;
    if (!camera || camera.starting || camera.capturing) { return; }
    stopCameraStream(camera.stream);
    camera.stream = null;
    camera.facing = camera.facing === "environment" ? "user" : "environment";
    camera.starting = true;
    camera.error = "";
    state.cameraRequest += 1;
    renderCameraLayer();
    requestCameraStream(camera, state.cameraRequest, false);
  }

  function captureCameraPhoto() {
    var camera = state.camera;
    var video = state.root && state.root.querySelector("#tlabCameraPreview");
    var button = state.root && state.root.querySelector('[data-tlab-command="capture-camera"]');
    var width;
    var height;
    var scale;
    var canvas;
    var context;
    if (!camera || camera.capturing || !video || video.readyState < 2) {
      notify("Esperá un instante hasta que la cámara muestre la imagen.", "info");
      return;
    }
    width = video.videoWidth;
    height = video.videoHeight;
    if (!width || !height) { notify("La cámara todavía no está lista.", "info"); return; }
    camera.capturing = true;
    if (button) { button.disabled = true; button.innerHTML = '<i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Preparando...'; }
    scale = Math.min(1, IMAGE_MAX_DIMENSION / Math.max(width, height));
    canvas = document.createElement("canvas");
    canvas.width = Math.max(1, Math.round(width * scale));
    canvas.height = Math.max(1, Math.round(height * scale));
    context = canvas.getContext("2d");
    if (!context) {
      camera.capturing = false;
      notify("La tablet no pudo preparar la fotografía. Podés usar la cámara del dispositivo.", "error");
      return;
    }
    context.fillStyle = "#ffffff";
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    canvasToJpeg(canvas, 0.88).then(function (blob) {
      var target = camera.target;
      var named = namedMediaBlob(blob, "fotografia-" + Date.now() + ".jpg", "image/jpeg");
      closeCamera(false);
      if (target === "node") { addNodeFiles([named]); }
      else { addFiles([named]); }
    }).then(null, function (error) {
      if (state.camera === camera) {
        camera.capturing = false;
        camera.error = error.message || "No se pudo preparar la fotografía.";
        renderCameraLayer();
      }
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
      + '    <section class="tlab-toolbar" aria-label="Búsqueda y filtros">'
      + '      <div class="tlab-toolbar__main"><div class="tlab-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><label class="sr-only" for="tlabSearch">Buscar trabajos</label><input id="tlabSearch" type="search" autocomplete="off" placeholder="Venta, código del trabajo, paciente o producto"></div>'
      + '        <button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="toggle-filters" aria-expanded="false" aria-controls="tlabFilters"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Filtros <span class="tlab-filter-count" id="tlabFilterCount" hidden>0</span></button>'
      + '        <button type="button" class="tlab-button tlab-button--secondary tlab-print-command" id="tlabPrintTechnicalButton" data-tlab-command="print-technical"><i class="fa-solid fa-print" aria-hidden="true"></i><span>Imprimir listado</span></button>'
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
      + '      <form class="tlab-filters tlab-filters--historical" id="tlabHistoricalFilters" hidden><div class="tlab-filters__grid">'
      + '        <div class="tlab-field"><label for="tlabHistoricalOriginalState">Estado original</label><select id="tlabHistoricalOriginalState" name="estado_original"><option value="">Todos</option><option value="pendiente">Pendiente</option><option value="retirado">Retirado</option><option value="pagado">Pagado</option><option value="entregado">Entregado</option><option value="inactivo">Inactivo</option></select></div>'
      + '        <div class="tlab-field"><label for="tlabHistoricalDeclaredState">Situación declarada</label><select id="tlabHistoricalDeclaredState" name="estado_declarado"><option value="">Todas</option></select></div>'
      + '        <label class="tlab-check"><input id="tlabHistoricalPending" name="pendiente_revision" type="checkbox"><span>Sólo pendientes de integrar</span></label>'
      + '      </div><div class="tlab-filters__actions"><button type="button" class="tlab-button tlab-button--ghost" data-tlab-command="clear-filters">Limpiar</button><button type="submit" class="tlab-button tlab-button--primary"><i class="fa-solid fa-filter" aria-hidden="true"></i>Aplicar filtros</button></div></form>'
      + '    </section>'
      + '    <div class="tlab-view-switch" id="tlabViewSwitch" hidden><button type="button" data-tlab-view="operativa" aria-pressed="true"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Vista operativa</button><button type="button" data-tlab-view="mecanico" aria-pressed="false"><i class="fa-solid fa-toolbox" aria-hidden="true"></i>Mi bandeja</button><button type="button" data-tlab-view="historicos" aria-pressed="false" hidden><i class="fa-solid fa-box-archive" aria-hidden="true"></i>Históricos</button></div>'
      + '    <nav class="tlab-groups" id="tlabGroups" aria-label="Grupos operativos" role="tablist"></nav>'
      + '    <nav class="tlab-mechanic-tray" id="tlabMechanicTray" aria-label="Bandeja del mecánico" role="tablist" hidden></nav>'
      + '    <div class="tlab-section-heading"><div><h2 id="tlabListTitle">Pendientes de entrega</h2><p id="tlabListHint">Trabajos que requieren una acción de entrega.</p></div><span class="tlab-status tlab-status--neutral" id="tlabResultCount">0 trabajos</span></div>'
      + '    <section id="tlabResults" aria-live="polite" aria-busy="false"><div class="tlab-results-state">' + loaderHtml("Buscando trabajos...", "content") + '</div></section>'
      + '    <div class="tlab-load-more" id="tlabLoadMore" hidden><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="load-more">Cargar más trabajos</button></div>'
      + '  </main>'
      + '</div>'
      + '<section class="tlab-print-sheet" id="tlabPrintSheet" aria-label="Listado técnico de trabajos de laboratorio" hidden></section>'
      + '<div class="tlab-node-popover" id="tlabNodePopover" role="dialog" aria-modal="false" aria-label="Detalle del evento" hidden></div>'
      + '<div class="tlab-dialog-layer" id="tlabActionLayer" hidden></div>'
      + '<div class="tlab-viewer-layer" id="tlabViewerLayer" hidden></div>'
      + '<div class="tlab-camera-layer" id="tlabCameraLayer" hidden></div>'
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
    root.addEventListener("scroll", onRootScroll, true);
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
    var historicalCard = event.target.closest("[data-tlab-historical-id]");
    var tab = event.target.closest("[data-tlab-detail-tab]");
    var action = event.target.closest("[data-tlab-action]");
    var historicalAction = event.target.closest("[data-tlab-historical-action]");
    var previewRemove = event.target.closest("[data-tlab-preview-remove]");
    var evidence = event.target.closest("[data-tlab-media-id], [data-tlab-evidence-url]");
    var nodeTrigger = event.target.closest("[data-tlab-node-trigger]");
    var takeNode = event.target.closest("[data-tlab-take-node]");
    var resolveHistorical = event.target.closest("[data-tlab-resolve-historical]");
    var nodeEdit = event.target.closest("[data-tlab-node-edit]");
    var nodeEditCancel = event.target.closest("[data-tlab-node-edit-cancel]");
    var nodeAction = event.target.closest("[data-tlab-popover-action]");
    var nodePreviewRemove = event.target.closest("[data-tlab-node-preview-remove]");
    var popover = event.target.closest("#tlabNodePopover");
    var cameraLayer = event.target.closest("#tlabCameraLayer");
    if (state.nodePopover && !nodeTrigger && !takeNode && !resolveHistorical
        && !popover && !cameraLayer && closeNodePopover() === false) { return; }
    if (command) {
      handleCommand(command.getAttribute("data-tlab-command"), command, event);
      return;
    }
    if (action) {
      var rowActionId = action.getAttribute("data-tlab-row-work-id");
      var rowWork = rowActionId ? listedWorkRecord(rowActionId) : null;
      var rowActionCode = action.getAttribute("data-tlab-action");
      var rowAction = rowWork ? normalizeWork(rowWork).actions.filter(function (item) { return item.code === rowActionCode; })[0] : null;
      event.preventDefault();
      openAction(rowActionCode, rowAction, rowWork);
      return;
    }
    if (historicalAction) {
      event.preventDefault();
      openHistoricalWizard(historicalAction.getAttribute("data-tlab-historical-action"));
      return;
    }
    if (group) {
      if (closeDetail(true) === false) { return; }
      state.group = group.getAttribute("data-tlab-group");
      state.view = "operativa";
      state.moduleOptions.cod_venta_historica = "";
      renderGroupNavigation();
      loadWorks(false);
      return;
    }
    if (view) {
      var requestedView = view.getAttribute("data-tlab-view");
      if (requestedView === "historicos"
          && !boolValue(state.context.historicos_disponibles)) { return; }
      if (closeDetail(true) === false) { return; }
      state.view = requestedView === "mecanico" ? "mecanico" : (requestedView === "historicos" ? "historicos" : "operativa");
      if (state.view !== "historicos") { state.moduleOptions.cod_venta_historica = ""; }
      state.filtersOpen = false;
      renderGroupNavigation();
      loadWorks(false);
      return;
    }
    if (tray) {
      if (closeDetail(true) === false) { return; }
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
      closeNodePopover();
      if (evidence.getAttribute("data-tlab-media-id")) {
        openAuthorizedMedia(evidence.getAttribute("data-tlab-media-id"), evidence.getAttribute("data-tlab-evidence-caption"));
      } else {
        openViewer(evidence.getAttribute("data-tlab-evidence-url"), evidence.getAttribute("data-tlab-evidence-caption"));
      }
      return;
    }
    if (nodePreviewRemove) {
      event.preventDefault();
      removeNodeFile(numberValue(nodePreviewRemove.getAttribute("data-tlab-node-preview-remove"), -1));
      return;
    }
    if (nodeEdit) {
      event.preventDefault();
      beginCurrentNodeEdit();
      return;
    }
    if (nodeEditCancel) {
      event.preventDefault();
      cancelNodeEdit();
      return;
    }
    if (nodeAction) {
      event.preventDefault();
      openNodeRelatedAction(nodeAction.getAttribute("data-tlab-popover-action"));
      return;
    }
    if (takeNode) {
      event.preventDefault();
      openTakeNodePopover(takeNode);
      return;
    }
    if (resolveHistorical) {
      event.preventDefault();
      openHistoricalResolver(resolveHistorical);
      return;
    }
    if (nodeTrigger) {
      event.preventDefault();
      toggleNodePopover(nodeTrigger, event.detail === 0);
      return;
    }
    if (card) {
      openDetail(card.getAttribute("data-tlab-work-id"));
      return;
    }
    if (historicalCard) { return; }
  }

  function handleCommand(command, element, event) {
    var filters;
    switch (command) {
      case "close": closeModule(); break;
      case "refresh": refreshAll(); break;
      case "print-technical": printTechnicalList(); break;
      case "toggle-filters":
        filters = currentFilterForm();
        state.filtersOpen = filters ? filters.hidden : false;
        renderFilterPresentation();
        if (state.filtersOpen) {
          loadCatalogs(false).then(null, function (error) {
            notify(error.message, "error");
          });
        }
        break;
      case "clear-filters": clearFilters(); break;
      case "load-more": loadWorks(true); break;
      case "close-detail": closeDetail(); break;
      case "close-node-popover": closeNodePopover(true); break;
      case "close-action": closeAction(); break;
      case "action-back": actionBack(); break;
      case "action-next": actionNext(); break;
      case "open-camera-action": openCamera("action"); break;
      case "open-camera-node": openCamera("node"); break;
      case "capture-camera": captureCameraPhoto(); break;
      case "switch-camera": switchCamera(); break;
      case "close-camera": closeCamera(); break;
      case "historical-cancel": closeHistoricalWizard(); break;
      case "historical-back": historicalWizardBack(); break;
      case "historical-next": historicalWizardNext(); break;
      case "close-viewer": closeViewer(); break;
      case "copy-code":
        event.stopPropagation();
        copyText(element.getAttribute("data-code"));
        break;
    }
  }

  function onRootSubmit(event) {
    if (event.target.id === "tlabHistoricalResolverForm") {
      event.preventDefault();
      submitHistoricalResolver();
      return;
    }
    if (event.target.id === "tlabNodeVersionForm") {
      event.preventDefault();
      submitNodeVersion();
      return;
    }
    if (event.target.id === "tlabFilters" || event.target.id === "tlabHistoricalFilters") {
      event.preventDefault();
      if (closeDetail(true) === false) { return; }
      updateFilterCount();
      loadWorks(false);
      return;
    }
    if (event.target.id === "tlabHistoricalWizardForm") {
      event.preventDefault();
      submitHistoricalWizard();
      return;
    }
    if (event.target.id === "tlabActionForm") {
      event.preventDefault();
      submitAction();
    }
  }

  function onRootChange(event) {
    if (event.target.matches("[data-tlab-node-file-input]")) {
      var nodeInputFromCamera = event.target.closest("#tlabCameraLayer");
      if (state.historicalResolver) {
        captureHistoricalResolverValues();
        state.historicalResolver.values.sin_foto_historica = "0";
      } else if (state.nodeEditor) {
        state.nodeEditor.values = Object.assign({}, state.nodeEditor.values, nodeFormValues());
      }
      addNodeFiles(event.target.files);
      if (nodeInputFromCamera) { closeCamera(); }
      event.target.value = "";
      return;
    }
    if (state.historicalResolver && event.target.closest("#tlabHistoricalResolverForm")) {
      var resolverCandidates;
      var resolverCandidate;
      captureHistoricalResolverValues();
      if (event.target.name === "cod_detalle_venta") {
        resolverCandidates = historicalResolutionCandidates(state.historicalResolver.envelope);
        resolverCandidate = historicalSelectedCandidate(
          resolverCandidates,
          state.historicalResolver.values.cod_detalle_venta
        );
        if (historicalCandidateIsFinalized(resolverCandidate)) {
          state.historicalResolver.values.modo_resolucion = "instalado_entregado";
          state.historicalResolver.error = "Este tratamiento ya está finalizado. La resolución disponible es Instalado y entregado.";
        }
      }
      if (event.target.name === "modo_resolucion"
          && state.historicalResolver.values.modo_resolucion === "instalado_entregado") {
        state.historicalResolver.error = "";
      }
      if (event.target.name === "sin_foto_historica") {
        state.historicalResolver.error = "";
        if (event.target.checked) {
          revokeNodeObjectUrls();
          state.nodeFiles = [];
        }
      }
      if (event.target.name === "modo_resolucion"
          || event.target.name === "condicion_pre_entrega"
          || event.target.name === "cod_detalle_venta"
          || event.target.name === "sin_foto_historica") {
        renderHistoricalResolver();
      }
      return;
    }
    if (event.target.matches("[data-tlab-file-input]")) {
      var actionInputFromCamera = event.target.closest("#tlabCameraLayer");
      captureActionValues();
      addFiles(event.target.files);
      if (actionInputFromCamera) { closeCamera(); }
      event.target.value = "";
      return;
    }
    if (state.action && event.target.closest("#tlabActionForm")) {
      captureActionValues();
      if (state.action.code === "tomarHilo" && event.target.name === "condicion_recepcion"
          && event.target.value === "conforme") {
        state.action.values.observacion = "";
      }
      if ((state.action.code === "tomarHilo" || state.action.code === "registrarInstalacion")
          && event.target.name === "sin_foto" && event.target.checked) {
        revokeObjectUrls();
        state.action.files = [];
      }
      if ((state.action.code === "solicitarAjuste" && event.target.name === "motivo")
          || (state.action.code === "tomarHilo" && (event.target.name === "condicion_recepcion" || event.target.name === "sin_foto" || event.target.name === "motivo_sin_foto"))
          || (state.action.code === "registrarInstalacion" && (event.target.name === "condicion_pre_entrega" || event.target.name === "sin_foto" || event.target.name === "motivo_sin_foto"))) {
        renderActionDialog();
      }
      return;
    }
    if (state.historicalWizard && event.target.closest("#tlabHistoricalWizardForm")) {
      captureHistoricalWizardValues();
    }
  }

  function onRootInput(event) {
    if (event.target.id === "tlabSearch") {
      if (state.detailId && closeDetail(true) === false) { return; }
      if (state.view === "historicos") { state.moduleOptions.cod_venta_historica = ""; }
      window.clearTimeout(state.searchTimer);
      state.searchTimer = window.setTimeout(function () { loadWorks(false); }, 380);
      return;
    }
    if (state.action && event.target.closest("#tlabActionForm")) {
      captureActionValues();
      return;
    }
    if (state.historicalResolver && event.target.closest("#tlabHistoricalResolverForm")) {
      captureHistoricalResolverValues();
      return;
    }
    if (state.historicalWizard && event.target.closest("#tlabHistoricalWizardForm")) {
      captureHistoricalWizardValues();
    }
  }

  function hasFinePointer() {
    return !window.matchMedia || window.matchMedia("(hover: hover) and (pointer: fine)").matches;
  }

  function clearPopoverCloseTimer() {
    if (state.popoverCloseTimer) {
      window.clearTimeout(state.popoverCloseTimer);
      state.popoverCloseTimer = null;
    }
  }

  function scheduleNodePopoverClose() {
    clearPopoverCloseTimer();
    if (state.nodePopoverPinned) { return; }
    state.popoverCloseTimer = window.setTimeout(function () {
      var active = document.activeElement;
      var popover = state.root && state.root.querySelector("#tlabNodePopover");
      if (active && (active.closest("[data-tlab-node-trigger]") || (popover && popover.contains(active)))) { return; }
      closeNodePopover();
    }, 140);
  }

  function onRootMouseOver(event) {
    var trigger;
    if (!hasFinePointer()) { return; }
    trigger = event.target.closest("[data-tlab-node-trigger]");
    if (trigger) {
      clearPopoverCloseTimer();
      openNodePopover(trigger, false);
      return;
    }
    if (event.target.closest("#tlabNodePopover")) { clearPopoverCloseTimer(); }
  }

  function onRootMouseOut(event) {
    if (!hasFinePointer()) { return; }
    if (event.target.closest("[data-tlab-node-trigger], #tlabNodePopover")) { scheduleNodePopoverClose(); }
  }

  function onRootFocusIn(event) {
    var trigger = event.target.closest("[data-tlab-node-trigger]");
    if (trigger) {
      clearPopoverCloseTimer();
      openNodePopover(trigger, false);
    } else if (event.target.closest("#tlabNodePopover")) {
      clearPopoverCloseTimer();
    }
  }

  function onRootFocusOut(event) {
    if (event.target.closest("[data-tlab-node-trigger], #tlabNodePopover")) { scheduleNodePopoverClose(); }
  }

  function onRootScroll(event) {
    if (!state.nodePopover) { return; }
    if (event.target.closest && event.target.closest("#tlabNodePopover")) { return; }
    closeNodePopover();
  }

  function onDocumentKeydown(event) {
    var layer;
    if (!state.open) { return; }
    layer = activeLayer();
    if (event.key === "Escape") {
      if (!state.root.querySelector("#tlabCameraLayer").hidden) { closeCamera(); }
      else if (!state.root.querySelector("#tlabViewerLayer").hidden) { closeViewer(); }
      else if (!state.root.querySelector("#tlabActionLayer").hidden) { closeAction(); }
      else if (state.historicalWizard) { closeHistoricalWizard(); }
      else if (state.nodePopover) { closeNodePopover(true); }
      else if (state.detailId) { closeDetail(); }
      else { closeModule(); }
      return;
    }
    if (event.key === "Tab" && layer) { trapFocus(event, layer); }
  }

  function activeLayer() {
    var selectors = ["#tlabCameraLayer", "#tlabViewerLayer", "#tlabActionLayer"];
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
    var ventaHistorica;
    options = options || {};
    ventaHistorica = toStringSafe(options.cod_venta_historica).trim();
    state.moduleOptions = {
      cod_consulta_origen: options.cod_consulta_origen || "",
      cod_evolucion_origen: options.cod_evolucion_origen || "",
      cod_venta_historica: ventaHistorica,
      cod_detalle_operativo: options.cod_detalle_operativo || ""
    };
    state.focusBeforeLayer = document.activeElement;
    state.open = true;
    state.group = options.grupo || state.group;
    state.view = options.vista === "mecanico" ? "mecanico"
      : (options.vista === "historicos" ? "historicos" : (options.vista === "operativa" ? "operativa" : state.view));
    state.mechanicTray = options.bandeja || state.mechanicTray;
	if (ventaHistorica) {
		root.querySelector("#tlabHistoricalFilters").reset();
		state.filtersOpen = false;
	}
	if (Object.prototype.hasOwnProperty.call(options, "busqueda")) {
		root.querySelector("#tlabSearch").value = toStringSafe(options.busqueda);
	} else if (ventaHistorica) {
		root.querySelector("#tlabSearch").value = ventaHistorica;
	}
    root.hidden = false;
    document.body.classList.add("tlab-lock");
    updateViewportHeight();
    renderGroupNavigation();
    loadInitialData();
    focusFirst(root.querySelector(".tlab-topbar"));
  }

  function closeModule() {
    if (!state.root) { return; }
    if (state.action && state.action.saving) {
      notify("La acción se está guardando. Esperá la confirmación del servidor.", "info");
      return;
    }
    if (state.historicalWizard && state.historicalWizard.saving) {
      notify("La actualización histórica se está confirmando. Esperá a que termine.", "info");
      return;
    }
    if (state.nodeEditor && state.nodeEditor.saving) {
      notify("La versión del nodo se está guardando. Esperá la confirmación del servidor.", "info");
      return;
    }
    if (state.historicalResolver && state.historicalResolver.saving) {
      notify("La resolución histórica se está guardando. Esperá la confirmación del servidor.", "info");
      return;
    }
    closeViewer();
    closeCamera();
    closeAction();
    closeNodePopover();
    closeDetail(true);
    state.open = false;
    state.root.hidden = true;
    document.body.classList.remove("tlab-lock");
    if (state.focusBeforeLayer && typeof state.focusBeforeLayer.focus === "function") {
      state.focusBeforeLayer.focus();
    }
  }

  function loadInitialData() {
    var results = state.root.querySelector("#tlabResults");
    results.innerHTML = '<div class="tlab-results-state">' + loaderHtml("Buscando trabajos...", "content") + '</div>';
    loadWorks(false);
    loadSummary().then(null, function (error) { notify(error.message, "error"); });
  }

  function refreshAll() {
    loadSummary().then(null, function (error) { notify(error.message, "error"); });
    if (state.filtersOpen || state.catalogsLoadedAt > 0) {
      loadCatalogs(true).then(null, function () {});
    }
    loadWorks(false);
    if (state.detailId) {
      if (state.detailKind === "historico") { openHistoricalDetail(state.detailId, true); }
      else { openDetail(state.detailId, true); }
    }
  }

  function mergeContext(data) {
    var context = data.contexto_usuario || data.contexto || data.context || {};
    var mediaLimits;
    var contextKeys = [
      "es_mecanico", "es_auditor", "rol", "cod_usuario", "cod_local",
      "nombre", "nombre_usuario", "usuario_nombre", "avatar", "avatar_usuario", "usuario_avatar", "nombre_local", "local_nombre",
      "puede_ver_bandeja_mecanico", "historicos_disponibles",
      "puede_resolver_historicos", "puede_convalidar_historicos",
      "puede_rectificar_historicos", "puede_gestionar_costo"
    ];
    state.context = Object.assign({}, state.context, context);
    contextKeys.forEach(function (key) {
      if (data[key] !== undefined) { state.context[key] = data[key]; }
    });
    mediaLimits = state.context.limites_media || {};
    if (numberValue(mediaLimits.max_archivos, 0) > 0) {
      MAX_FILES = Math.max(1, Math.min(5, numberValue(mediaLimits.max_archivos, MAX_FILES)));
    }
    if (numberValue(mediaLimits.max_bytes_archivo, 0) > 0) {
      MAX_FILE_SIZE = numberValue(mediaLimits.max_bytes_archivo, MAX_FILE_SIZE);
    }
    updateRolePresentation();
  }

  function canManageWorkCost() {
    return boolValue(state.context.puede_gestionar_costo);
  }

  function updateRolePresentation() {
    var badge;
    var switcher;
    var historicalButton;
    var mechanicFilter;
    var mechanic = boolValue(state.context.es_mecanico) || toStringSafe(state.context.rol).toLowerCase().indexOf("mecán") >= 0 || toStringSafe(state.context.rol).toLowerCase().indexOf("mecan") >= 0;
    var auditor = boolValue(state.context.es_auditor);
    var historicosDisponibles = boolValue(state.context.historicos_disponibles);
    if (!state.root) { return; }
    badge = state.root.querySelector("#tlabRoleBadge span");
    switcher = state.root.querySelector("#tlabViewSwitch");
    historicalButton = switcher ? switcher.querySelector('[data-tlab-view="historicos"]') : null;
    mechanicFilter = state.root.querySelector("#tlabFilterMechanic");
    if (badge) { badge.textContent = mechanic ? "Mecánico dental" : (state.context.rol || "Acceso autorizado"); }
    if (historicalButton) { historicalButton.hidden = !historicosDisponibles; }
    switcher.hidden = !(mechanic || boolValue(state.context.puede_ver_bandeja_mecanico)
      || auditor || historicosDisponibles);
    if (mechanicFilter && mechanicFilter.closest(".tlab-field")) {
      mechanicFilter.closest(".tlab-field").hidden = mechanic;
    }
    /* El mecanico abre la vista operativa completa para poder tomar cualquier
       hilo. La bandeja personal sigue disponible como filtro voluntario. */
    if (mechanic && state.context.forzar_bandeja === true && state.view === "operativa") {
      state.view = "mecanico";
    }
    if (!historicosDisponibles && state.view === "historicos") {
      state.view = "operativa";
      state.filtersOpen = false;
    }
    renderGroupNavigation();
  }

  function catalogsAreFresh() {
    return state.catalogsLoadedAt > 0
      && (Date.now() - state.catalogsLoadedAt) < CATALOG_CACHE_MS
      && Object.keys(state.catalogs || {}).length > 0;
  }

  function loadCatalogs(force) {
    var pending;
    if (!force && catalogsAreFresh()) {
      renderCatalogs();
      return Promise.resolve({ data: { catalogos: state.catalogs } });
    }
    if (state.catalogsLoading) { return state.catalogsLoading; }
    pending = request("obtenerCatalogos", { respuesta_compacta: "1" }).then(function (response) {
      state.catalogs = response.data.catalogos || response.data;
      state.catalogsLoadedAt = Date.now();
      mergeContext(response.data);
      renderCatalogs();
      return response;
    });
    state.catalogsLoading = pending;
    pending.then(function () {
      state.catalogsLoading = null;
    }, function () {
      state.catalogsLoading = null;
    });
    return pending;
  }

  function loadSummary() {
    return request("obtenerResumen", { respuesta_compacta: "1" }).then(function (response) {
      state.summary = response.data.resumen || response.data;
      state.historicalSummary = pick(state.summary, ["historicos"], response.data.historicos || state.historicalSummary || {});
      mergeContext(response.data);
      renderGroupNavigation();
      return response;
    });
  }

  function catalogItems(names) {
    var items = pick(state.catalogs, names, []);
    return asArray(items);
  }

  function optionHtml(item) {
    var value;
    var label;
    if (typeof item !== "object") { return '<option value="' + escapeAttr(item) + '">' + escapeHtml(item) + '</option>'; }
    value = pick(item, ["cod_tecnico_usuario", "cod_custodio", "cod_usuario", "id", "codigo", "cod", "valor", "value", "cod_persona", "cod_local", "cod_producto"], "");
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
    fillSelect(
      "tlabHistoricalDeclaredState",
      [{ codigo: "situacion_por_actualizar", nombre: "Situación por actualizar" }].concat(
        catalogItems(["estados_declarables", "estados_historicos", "situaciones_historicas"])
      ),
      "Todas"
    );
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
    groups.hidden = state.view !== "operativa";
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
    if (state.view === "historicos") {
      title = "Trabajos históricos";
      hint = "Registros anteriores pendientes de resolución. Cualquier usuario autenticado puede continuarlos o cerrarlos como instalados y entregados.";
    } else if (state.view === "mecanico") {
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
    renderFilterPresentation();
  }

  function currentFilterForm() {
    if (!state.root) { return null; }
    return state.root.querySelector(state.view === "historicos" ? "#tlabHistoricalFilters" : "#tlabFilters");
  }

  function renderFilterPresentation() {
    var standard;
    var historical;
    var current;
    var button;
    var printButton;
    var search;
    if (!state.root) { return; }
    standard = state.root.querySelector("#tlabFilters");
    historical = state.root.querySelector("#tlabHistoricalFilters");
    current = state.view === "historicos" ? historical : standard;
    button = state.root.querySelector('[data-tlab-command="toggle-filters"]');
    printButton = state.root.querySelector('[data-tlab-command="print-technical"]');
    search = state.root.querySelector("#tlabSearch");
    if (standard) { standard.hidden = standard !== current || !state.filtersOpen; }
    if (historical) { historical.hidden = historical !== current || !state.filtersOpen; }
    if (button && current) {
      button.setAttribute("aria-controls", current.id);
      button.setAttribute("aria-expanded", state.filtersOpen ? "true" : "false");
    }
    if (printButton) {
      printButton.hidden = state.view === "historicos";
    }
    if (search) {
      search.placeholder = state.view === "historicos"
        ? "Código histórico, paciente, venta, mecánico o tipo de trabajo"
        : "Venta, código del trabajo, paciente o producto";
    }
    updateFilterCount();
  }

  function filterPayload() {
    var form = currentFilterForm();
    var payload = {};
    if (!form) { return payload; }
    forEachFormValue(form, function (value, key) {
      if (value !== "") { payload[key] = value; }
    });
    if (state.view === "historicos") {
      payload.pendiente_revision = state.root.querySelector("#tlabHistoricalPending").checked ? "1" : "";
      payload.busqueda = state.root.querySelector("#tlabSearch").value.trim();
      if (state.moduleOptions.cod_venta_historica) {
        payload.cod_venta = state.moduleOptions.cod_venta_historica;
      }
      return payload;
    }
    payload.transferencia_pendiente = state.root.querySelector("#tlabFilterPendingTransfer").checked ? "1" : "";
    payload.busqueda = state.root.querySelector("#tlabSearch").value.trim();
    payload.grupo_operativo = state.view === "operativa" ? state.group : "";
    payload.vista = state.view;
    payload.bandeja = state.view === "mecanico" ? state.mechanicTray : "";
    if (state.moduleOptions.cod_detalle_operativo) {
      payload.cod_detalle_venta = state.moduleOptions.cod_detalle_operativo;
    }
    return payload;
  }

  function technicalPrintViewTitle() {
    var title;
    if (!state.root) { return "Trabajos de laboratorio"; }
    title = state.root.querySelector("#tlabListTitle");
    return title ? title.textContent : "Trabajos de laboratorio";
  }

  function technicalPrintFilterLabels() {
    var labels = ["Pestaña: " + technicalPrintViewTitle()];
    var form = currentFilterForm();
    var search = state.root ? state.root.querySelector("#tlabSearch") : null;
    if (search && search.value.trim()) {
      labels.push("Búsqueda: " + search.value.trim());
    }
    Array.prototype.forEach.call(form ? form.elements : [], function (field) {
      var type;
      var label;
      var value;
      var option;
      if (!field || !field.name || field.disabled) { return; }
      type = toStringSafe(field.type).toLowerCase();
      if ((type === "checkbox" || type === "radio") && !field.checked) { return; }
      value = type === "checkbox" ? "Sí" : toStringSafe(field.value).trim();
      if (!value) { return; }
      label = field.id && state.root
        ? state.root.querySelector('label[for="' + escapeAttr(field.id) + '"]') : null;
      if (!label && field.closest) { label = field.closest("label"); }
      label = label ? label.textContent.trim() : field.name.replace(/_/g, " ");
      if (field.tagName && field.tagName.toLowerCase() === "select") {
        option = field.options[field.selectedIndex];
        value = option ? option.text : value;
      } else if (type === "date") {
        value = formatDate(value, false);
      }
      labels.push(label + ": " + value);
    });
    return labels;
  }

  function technicalPrintText(value, fallback) {
    var text = toStringSafe(value).trim();
    return escapeHtml(text || fallback || "Sin registrar").replace(/\r?\n/g, "<br>");
  }

  function technicalPrintRowHtml(item) {
    item = item || {};
    return '<tr>'
      + '<td class="tlab-print-code"><strong>' + technicalPrintText(item.codigo_visible, "-") + '</strong><small>Venta ' + technicalPrintText(item.nro_venta, "-") + '</small></td>'
      + '<td>' + technicalPrintText(item.paciente_abreviado, "Sin identificar") + '</td>'
      + '<td>' + technicalPrintText(item.pieza_dental, "Sin registrar") + '</td>'
      + '<td><strong>' + technicalPrintText(item.producto, "Sin producto") + '</strong><small>' + technicalPrintText(item.ciclo, "Original") + '</small></td>'
      + '<td>' + technicalPrintText(item.colorimetro, "Sin registrar") + '</td>'
      + '<td class="tlab-print-instructions">' + technicalPrintText(item.instrucciones, "Sin instrucciones") + '</td>'
      + '<td><strong>' + technicalPrintText(item.estado, "Sin estado") + '</strong><small>Objetivo: ' + technicalPrintText(formatDate(item.fecha_objetivo, false), "Sin registrar") + '</small></td>'
      + '<td>' + technicalPrintText(item.tecnico, "Técnico pendiente") + '<small>' + technicalPrintText(item.local, "Sin local") + '</small></td>'
      + '<td class="tlab-print-signature"><span></span><small>Firma</small></td>'
      + '</tr>';
  }

  function technicalPrintDocumentHtml(data, filterLabels) {
    var items = asArray(data.items || data.trabajos);
    var context = data.contexto_usuario || {};
    var userName = pick(context, ["nombre", "nombre_usuario", "usuario_nombre"], pick(state.context, ["nombre", "nombre_usuario", "usuario_nombre"], "Usuario autorizado"));
    var localName = pick(context, ["nombre_local", "local_nombre"], pick(state.context, ["nombre_local", "local_nombre"], "Local autorizado"));
    var filterHtml = asArray(filterLabels).map(function (label) {
      return '<span>' + escapeHtml(label) + '</span>';
    }).join("");
    return '<div class="tlab-print-document">'
      + '<header class="tlab-print-header"><div><span>SISTEMA TELAR · CLINIDENT SALUD</span><h1>Listado técnico de trabajos de laboratorio</h1><p>Documento operativo de consulta. No modifica estados ni custodias.</p></div>'
      + '<div class="tlab-print-meta"><strong>' + escapeHtml(formatDate(data.generado_en, true)) + '</strong><span>Generado por: ' + escapeHtml(userName) + '</span><span>Local: ' + escapeHtml(localName) + '</span></div></header>'
      + '<section class="tlab-print-summary"><div><strong>' + items.length + '</strong><span>' + (items.length === 1 ? "trabajo coincidente" : "trabajos coincidentes") + '</span></div><div class="tlab-print-filters">' + filterHtml + '</div></section>'
      + '<table class="tlab-print-table"><colgroup><col class="is-code"><col class="is-patient"><col class="is-tooth"><col class="is-product"><col class="is-color"><col class="is-instructions"><col class="is-status"><col class="is-technician"><col class="is-signature"></colgroup>'
      + '<thead><tr><th>Trabajo</th><th>Paciente</th><th>Pieza dental</th><th>Producto</th><th>Colorímetro</th><th>Instrucciones técnicas</th><th>Estado y fecha</th><th>Técnico</th><th>Firma</th></tr></thead>'
      + '<tbody>' + items.map(technicalPrintRowHtml).join("") + '</tbody></table>'
      + '<footer class="tlab-print-footer"><span>Pacientes identificados de forma abreviada por privacidad.</span><span>Hoja técnica · ' + escapeHtml(technicalPrintViewTitle()) + '</span></footer>'
      + '</div>';
  }

  function setTechnicalPrintLoading(active) {
    var button = state.root ? state.root.querySelector("#tlabPrintTechnicalButton") : null;
    if (!button) { return; }
    button.disabled = active;
    button.innerHTML = active
      ? '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i><span>Preparando...</span>'
      : '<i class="fa-solid fa-print" aria-hidden="true"></i><span>Imprimir listado</span>';
  }

  function finishTechnicalPrint() {
    var sheet = state.root ? state.root.querySelector("#tlabPrintSheet") : null;
    document.body.classList.remove("tlab-technical-print");
    window.removeEventListener("afterprint", finishTechnicalPrint);
    if (sheet) {
      sheet.hidden = true;
      sheet.innerHTML = "";
    }
    state.printing = false;
    setTechnicalPrintLoading(false);
  }

  function printTechnicalList() {
    var payload;
    var filterLabels;
    var sheet;
    if (!state.root || state.printing) { return; }
    if (state.view === "historicos") {
      notify("La impresión técnica está disponible en Vista operativa y Mi bandeja.", "info");
      return;
    }
    payload = filterPayload();
    filterLabels = technicalPrintFilterLabels();
    sheet = state.root.querySelector("#tlabPrintSheet");
    state.printing = true;
    setTechnicalPrintLoading(true);
    request("listarImpresionTecnica", payload).then(function (response) {
      var items = asArray(response.data.items || response.data.trabajos);
      if (!items.length) {
        finishTechnicalPrint();
        notify("No hay trabajos para imprimir con la pestaña y los filtros actuales.", "info");
        return;
      }
      mergeContext(response.data);
      sheet.innerHTML = technicalPrintDocumentHtml(response.data, filterLabels);
      sheet.hidden = false;
      document.body.classList.add("tlab-technical-print");
      window.addEventListener("afterprint", finishTechnicalPrint);
      window.print();
      window.setTimeout(finishTechnicalPrint, 500);
    }).then(null, function (error) {
      finishTechnicalPrint();
      notify(error.message || "No se pudo preparar el listado técnico.", "error");
    });
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
    var form = currentFilterForm();
    if (closeDetail(true) === false) { return; }
    form.reset();
    state.root.querySelector("#tlabSearch").value = "";
    if (state.view === "historicos") { state.moduleOptions.cod_venta_historica = ""; }
    if (state.view !== "historicos") { state.moduleOptions.cod_detalle_operativo = ""; }
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
    if (state.view === "historicos") { loadHistoricals(append); return; }
    if (!state.root || (state.loadingList && append)) { return; }
    state.loadingList = true;
    state.page = append ? state.page + 1 : 1;
    requestId = ++state.listRequest;
    payload = filterPayload();
    payload.pagina = state.page;
    payload.limite = PAGE_SIZE;
    payload.por_pagina = PAGE_SIZE;
    payload.respuesta_compacta = "1";
    results = state.root.querySelector("#tlabResults");
    results.setAttribute("aria-busy", "true");
    if (!append) {
      results.innerHTML = '<div class="tlab-results-state">' + loaderHtml("Buscando trabajos...", "content") + '</div>';
      state.root.querySelector("#tlabResultCount").textContent = "Buscando...";
    }
    request("listarTrabajos", payload).then(function (response) {
      var items;
      var total;
      var loaded;
      if (requestId !== state.listRequest) { return; }
      mergeContext(response.data);
      items = listFromResponse(response.data);
      if (!append) { state.nodeDetailCache = {}; }
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

  function historicalListFromResponse(data) {
    if (Array.isArray(data)) { return data; }
    return asArray(data.historicos || []);
  }

  function loadHistoricals(append) {
    var requestId;
    var payload;
    var results;
    if (!state.root || (state.loadingList && append)) { return; }
    if (!boolValue(state.context.historicos_disponibles)) {
      state.historicals = [];
      renderHistoricalListError("La bandeja histórica no está disponible.");
      return;
    }
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
      results.innerHTML = '<div class="tlab-results-state">' + loaderHtml("Buscando registros históricos...", "content") + '</div>';
      state.root.querySelector("#tlabResultCount").textContent = "Buscando...";
    }
    request("listarHistoricos", payload).then(function (response) {
      var items;
      var total;
      if (requestId !== state.listRequest || state.view !== "historicos") { return; }
      mergeContext(response.data);
      if (!boolValue(state.context.historicos_disponibles)) {
        throw new Error("La bandeja histórica ya no está disponible.");
      }
      items = historicalListFromResponse(response.data);
      if (response.data.estados_declarables) {
        state.catalogs.estados_declarables = response.data.estados_declarables;
        fillSelect(
          "tlabHistoricalDeclaredState",
          [{ codigo: "situacion_por_actualizar", nombre: "Situación por actualizar" }].concat(
            asArray(response.data.estados_declarables)
          ),
          "Todas"
        );
      }
      if (append) {
        var historicosConocidos = {};
        state.historicals.forEach(function (item) {
          historicosConocidos[toStringSafe(normalizeHistorical(item).id)] = true;
        });
        items.forEach(function (item) {
          var idHistorico = toStringSafe(normalizeHistorical(item).id);
          if (!historicosConocidos[idHistorico]) {
            state.historicals.push(item);
            historicosConocidos[idHistorico] = true;
          }
        });
      } else {
        state.nodeDetailCache = {};
        state.historicals = items;
      }
      total = numberValue(pick(response.data, ["total"], state.historicals.length), state.historicals.length);
      if (response.data.hay_mas !== undefined) {
        state.hasMore = boolValue(response.data.hay_mas);
      } else if (response.data.tiene_mas !== undefined) {
        state.hasMore = boolValue(response.data.tiene_mas);
      } else {
        state.hasMore = state.page * PAGE_SIZE < total;
      }
      state.historicalSummary = response.data.resumen || state.historicalSummary || {};
      renderHistoricals(total);
    }).then(null, function (error) {
      if (requestId !== state.listRequest) { return; }
      if (append) { state.page = Math.max(1, state.page - 1); }
      renderHistoricalListError(error.message);
    }).then(function () {
      if (requestId !== state.listRequest) { return; }
      state.loadingList = false;
      results.setAttribute("aria-busy", "false");
    });
  }

  function humanizeHistoricalValue(value, fallback) {
    var text = toStringSafe(value || fallback || "Sin registrar").replace(/_/g, " ").trim();
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : "Sin registrar";
  }

  function photoExceptionLabel(event) {
    var reason = toStringSafe(event && event.photoExceptionReason);
    if (reason === "foto_historica_no_disponible") {
      return "Sin fotografía histórica disponible";
    }
    return "Sin foto · " + humanizeHistoricalValue(reason, "Excepción auditada");
  }

  function historicalPendingItems(item) {
    var raw = item.pendientes || item.datos_pendientes || item.validaciones_pendientes || [];
    var pending = asArray(raw).map(function (entry) {
      if (entry && typeof entry === "object") {
        return pick(entry, ["etiqueta", "mensaje", "descripcion", "nombre", "campo"], "Dato pendiente");
      }
      return humanizeHistoricalValue(entry, "Dato pendiente");
    }).filter(Boolean);
    if (!pending.length && boolValue(item.requiere_detalle_venta || item.detalle_venta_pendiente)) { pending.push("Seleccionar el detalle exacto de la venta"); }
    if (!pending.length && boolValue(item.requiere_convalidacion || item.pendiente_convalidacion)) { pending.push("Actualizar la situación histórica"); }
    return pending;
  }

  function normalizeHistorical(item) {
    var author;
    var editor;
    item = item || {};
    author = item.creado_por || item.insertado_por || item.usuario_creacion || pick(item, ["nombre_usuario_insercion", "usuario_insercion", "user_insert"], "Usuario histórico");
    editor = item.editado_por || item.usuario_edicion || pick(item, ["nombre_usuario_edicion", "usuario_edicion", "user_update"], "");
    return {
      raw: item,
      id: pick(item, ["id_historico", "cod_trabajo_mecanico_dental", "id", "codigo_historico"], ""),
      code: pick(item, ["codigo_visible", "codigo_historico", "cod_trabajo_mecanico_legacy", "cod_trabajo_mecanico_dental", "id_historico"], "Sin código"),
      patient: pick(item, ["paciente_nombre", "nombre_paciente", "paciente", "cliente_nombre", "cliente"], "Paciente sin identificar"),
      sale: pick(item, ["numero_venta", "nro_venta", "cod_venta", "venta"], "Sin venta"),
      branch: pick(item, ["local_declarado", "local_snapshot", "local_nombre", "sucursal_nombre", "nombre_local", "local", "sucursal"], "Sin sucursal"),
      product: pick(item, ["tipo_trabajo", "trabajo", "producto_nombre", "producto", "descripcion_trabajo"], "Trabajo mecánico dental"),
      doctor: personFromRecord(
        item.doctor || item.especialista,
        item,
        ["nombre_doctor", "doctor_nombre", "odontologo_nombre", "nombre_odontologo"],
        ["doctor_rol", "odontologo_rol", "rol_doctor"],
        ["doctor_avatar", "doctor_avatar_url", "odontologo_avatar", "avatar_doctor"],
        "Odontólogo"
      ),
      originalState: pick(item, ["estado_original", "estado_historico", "estado"], "Sin registrar"),
      declaredState: pick(item, ["estado_declarado", "situacion_declarada"], "Situación por actualizar"),
      mechanic: personFromRecord(
        item.mecanico_declarado || item.mecanico_dental_declarado || item.mecanico || item.tecnico,
        item,
        ["nombre_mecanico_declarado", "nombre_mecanico_dental", "nombre_mecanico", "mecanico_nombre"],
        ["tecnico_rol", "mecanico_rol", "mecanico_declarado_rol"],
        ["tecnico_avatar", "mecanico_avatar", "mecanico_declarado_avatar", "avatar_mecanico"],
        "Mecánico dental"
      ),
      mechanicSnapshot: item.mecanico_snapshot || item.mecanico_dental || item.mecanico || pick(item, ["nombre_mecanico_dental", "nombre_mecanico", "mecanico_nombre"], "Sin dato original"),
      author: item.autor_original || author,
      authorDate: pick(item, ["fecha_creacion_original", "fecha_insercion", "fecha_creacion", "creado_en", "fecha_insert"], ""),
      editor: item.editor_original || editor,
      editorDate: pick(item, ["fecha_edicion_original", "fecha_edicion", "actualizado_en", "fecha_update"], ""),
      pending: historicalPendingItems(item),
      convalidated: toStringSafe(item.estado_convalidacion) === "convalidado_administracion" || boolValue(pick(item, ["convalidado", "esta_convalidado"], false)),
      synchronizedAutomatically: toStringSafe(item.estado_convalidacion) === "sincronizado_automatico",
      promoted: boolValue(item.integrado) || toStringSafe(item.estado_convalidacion) === "integrado_operativo" || boolValue(pick(item, ["promovido", "esta_promovido"], false)) || !!pick(item, ["id_trabajo_laboratorio", "cod_trabajo_laboratorio", "fecha_promocion"], ""),
      route: receivedRoute(item),
      version: pick(item, ["version", "version_registro"], "")
    };
  }

  function historicalStatusClass(value) {
    var text = toStringSafe(value).toLowerCase();
    if (text.indexOf("entreg") >= 0 || text.indexOf("final") >= 0 || text.indexOf("promovid") >= 0 || text.indexOf("convalidad") >= 0) { return "ok"; }
    if (text.indexOf("inactiv") >= 0 || text.indexOf("cancel") >= 0) { return "neutral"; }
    if (text.indexOf("pend") >= 0 || text.indexOf("actualizar") >= 0 || text.indexOf("pagad") >= 0) { return "warning"; }
    return "violet";
  }

  function receivedRoute(item) {
    var nested;
    var route;
    item = item || {};
    nested = item.detalle && typeof item.detalle === "object" ? item.detalle : {};
    route = item.recorrido_operativo;
    if (route === undefined || route === null) { route = item.recorrido; }
    if (route === undefined || route === null) { route = item.timeline; }
    if (route === undefined || route === null) { route = item.eventos_resumen; }
    if (route === undefined || route === null) { route = item.eventos; }
    if (route === undefined || route === null) { route = item.trazabilidad; }
    if ((route === undefined || route === null) && nested) { route = nested.recorrido; }
    if (route && !Array.isArray(route) && typeof route === "object" && route.eventos) { route = route.eventos; }
    return asArray(route);
  }

  function receivedCustodyChain(item) {
    var nested;
    var chain;
    item = item || {};
    nested = item.detalle && typeof item.detalle === "object" ? item.detalle : {};
    chain = item.cadena_custodia;
    if (chain === undefined || chain === null) { chain = item.hilo_custodia; }
    if ((chain === undefined || chain === null) && nested) { chain = nested.cadena_custodia || nested.hilo_custodia; }
    if (chain && !Array.isArray(chain) && typeof chain === "object") {
      chain = chain.nodos || chain.eventos || chain.items || chain.cadena || [];
    }
    return asArray(chain);
  }

  function safeDomToken(value) {
    return toStringSafe(value).replace(/[^a-zA-Z0-9_-]+/g, "-") || "sin-id";
  }

  function inlineDetailDomId(kind, id, rowIndex) {
    return "tlab-ficha-" + safeDomToken(kind) + "-" + safeDomToken(id) + "-" + numberValue(rowIndex, 0);
  }

  function isInlineDetailOpen(kind, id) {
    return state.detailKind === kind && toStringSafe(state.detailId) === toStringSafe(id);
  }

  function inlineDetailHtml(kind, id, rowIndex) {
    var expanded = isInlineDetailOpen(kind, id);
    return '<section class="tlab-row-detail" id="' + escapeAttr(inlineDetailDomId(kind, id, rowIndex)) + '" data-tlab-inline-kind="' + escapeAttr(kind) + '" data-tlab-inline-id="' + escapeAttr(id) + '" role="region" aria-label="Ficha general del trabajo" ' + (expanded ? '' : 'hidden') + '><div class="tlab-detail__body tlab-row-detail__body">' + (expanded ? loaderHtml("Cargando ficha general...", "compact") : '') + '</div></section>';
  }

  function routeEvent(item) {
    var actorRaw = item.actor || item.usuario || item.realizado_por || pick(item, ["nombre_usuario", "actor_nombre"], "Usuario registrado");
    var actor = person(actorRaw, pick(item, ["actor_rol", "rol_actor"], "Sin rol informado"));
    var title = pick(item, ["accion_texto", "titulo", "evento_texto", "tipo_evento", "accion"], "Evento registrado");
    var cycle = pick(item, ["ciclo_etiqueta", "ciclo", "tipo_ciclo"], "Sin ciclo informado");
    var elapsed = pick(item, ["tiempo_desde_anterior_texto", "dias_desde_anterior", "dias_transcurridos", "duracion_texto"], "");
    var mediaId = pick(item, ["miniatura_media_id", "id_media", "media_id", "evidencia_id"], "");
    var image = pick(item, ["miniatura_url", "url_visualizacion", "imagen", "foto", "evidencia_url"], "");
    var adjustmentText = (toStringSafe(cycle) + " " + toStringSafe(title) + " " + toStringSafe(pick(item, ["tipo_evento", "tipo"], ""))).toLowerCase();
    if (elapsed !== "" && /^\d+$/.test(toStringSafe(elapsed))) { elapsed = formatDays(elapsed); }
    return {
      raw: item,
      actor: actor,
      title: humanizeHistoricalValue(title, "Evento registrado"),
      cycle: humanizeHistoricalValue(cycle, "Sin ciclo informado"),
      date: pick(item, ["fecha_hora", "fecha_servidor", "server_timestamp", "fecha", "creado_en"], ""),
      branch: pick(item, ["local_nombre", "sucursal", "local"], "Sin registrar"),
      elapsed: elapsed,
      previous: person(item.custodio_anterior || pick(item, ["nombre_custodio_anterior"], "")).name,
      next: person(item.custodio_nuevo || item.nuevo_custodio || pick(item, ["nombre_custodio_nuevo"], "")).name,
      sender: person(item.remitente || pick(item, ["nombre_remitente"], "")).name,
      recipient: person(item.destinatario || pick(item, ["nombre_destinatario"], "")).name,
      note: pick(item, ["observacion", "nota", "justificacion", "detalle"], ""),
      mediaId: mediaId,
      image: image,
      pending: boolValue(item.pendiente),
      alert: boolValue(item.atrasado || item.demora),
      adjustment: adjustmentText.indexOf("ajuste") >= 0
    };
  }

  function custodyEvent(item) {
    var responsibleRaw;
    var responsible;
    var performedBy;
    var duration;
    var evidence;
    var evidenceCount;
    var condition;
    var terminalState;
    var terminal;
    var cancelled;
    item = item || {};
    responsibleRaw = item.responsable || item.custodio || item.actor || item.usuario
      || item.custodio_nuevo || pick(item, ["responsable_nombre", "custodio_nombre", "nombre_custodio", "actor_nombre"], "Responsable registrado");
    if (responsibleRaw && typeof responsibleRaw === "object") {
      responsibleRaw = responsibleRaw.persona || responsibleRaw.usuario || responsibleRaw;
    }
    responsible = person(responsibleRaw, pick(item, ["responsable_rol", "custodio_rol", "actor_rol", "rol"], "Responsable de custodia"));
    performedBy = person(item.actor || item.usuario || responsibleRaw, pick(item, ["actor_rol", "rol_actor"], "Usuario Telar"));
    duration = pick(item, ["duracion_texto", "tiempo_custodia_texto", "tiempo_transcurrido_texto", "tiempo_desde_anterior_texto"], "");
    if (!duration && pick(item, ["duracion_segundos", "segundos_custodia"], "") !== "") {
      duration = formatDurationSeconds(pick(item, ["duracion_segundos", "segundos_custodia"], 0));
    }
    evidence = item.evidencia || item.media || {};
    evidenceCount = pick(item, ["evidencias_cantidad", "cantidad_evidencias", "cantidad_media"], pick(evidence, ["cantidad"], ""));
    if (evidenceCount === "" && Array.isArray(item.evidencias)) { evidenceCount = item.evidencias.length; }
    condition = pick(item, ["condicion_recepcion_texto", "condicion_texto", "condicion_recepcion", "condicion"], "");
    terminalState = toStringSafe(pick(item, ["estado_terminal", "motivo_cierre"], "")).toLowerCase();
    terminal = boolValue(pick(item, ["terminal", "es_final", "final", "finalizado"], false));
    cancelled = terminal && /cancel/.test(terminalState);
    return {
      raw: item,
      actor: responsible,
      performedBy: performedBy,
      title: humanizeHistoricalValue(pick(item, ["titulo", "accion_texto", "evento_texto", "tipo_evento"], "Custodia asumida"), "Custodia asumida"),
      cycle: "El hilo de custodia",
      date: pick(item, ["fecha_inicio", "inicio", "fecha_hora", "fecha_servidor", "fecha"], ""),
      endDate: pick(item, ["fecha_fin", "fin", "fecha_cierre"], ""),
      branch: pick(item, ["local_nombre", "sucursal_nombre", "sucursal", "local"], "Sin registrar"),
      elapsed: duration,
      previous: person(item.custodio_anterior || pick(item, ["nombre_custodio_anterior"], "")).name,
      next: responsible.name,
      sender: "",
      recipient: responsible.name,
      note: pick(item, ["observacion_recepcion", "observacion", "nota", "detalle", "justificacion"], ""),
      mediaId: pick(evidence, ["id_media", "id"], pick(item, ["miniatura_media_id", "id_media", "media_id", "evidencia_id"], "")),
      image: pick(evidence, ["miniatura_url", "url_visualizacion", "url"], pick(item, ["miniatura_url", "url_visualizacion", "imagen", "foto"], "")),
      evidenceCount: numberValue(evidenceCount, 0),
      noveltyCount: numberValue(pick(item, ["novedades_cantidad", "cantidad_novedades", "novedades"], 0), 0),
      condition: condition ? humanizeHistoricalValue(condition, "") : "",
      current: boolValue(pick(item, ["es_actual", "actual", "custodia_actual"], false)),
      terminal: terminal,
      final: terminal && !cancelled,
      cancelled: cancelled,
      closed: boolValue(pick(item, ["cerrado"], false)),
      inTransport: boolValue(pick(item, ["en_transporte"], false)),
      historical: boolValue(pick(item, ["registro_historico"], false)),
      status: cancelled ? "Custodia cerrada por cancelación" : (terminal ? "Custodia final"
        : (boolValue(pick(item, ["actual", "es_actual", "custodia_actual"], false)) ? (boolValue(item.en_transporte) ? "Vigente · trabajo en transporte" : "Custodia vigente")
          : (boolValue(item.cerrado) ? "Período cerrado" : "Registrado"))),
      photoException: boolValue(pick(item, ["sin_foto", "excepcion_foto", "foto_exceptuada"], false)),
      photoExceptionReason: pick(item, ["motivo_sin_foto_texto", "motivo_sin_foto", "razon_excepcion_foto"], ""),
      workData: item.datos_trabajo && typeof item.datos_trabajo === "object" ? item.datos_trabajo : null,
      changedFields: asArray(item.campos_modificados),
      versionEvents: asArray(item.eventos_version),
      versionNumber: numberValue(pick(item, ["version_nodo"], 0), 0),
      editionCount: numberValue(pick(item, ["ediciones_cantidad"], 0), 0),
      pending: false,
      alert: false,
      adjustment: false
    };
  }

  function routeNodeHtml(item, index, length, kind, rowId, rowIndex) {
    var event = routeEvent(item);
    var nodeKey = safeDomToken(kind) + "-" + safeDomToken(rowId) + "-" + rowIndex + "-" + index;
    var classes = event.adjustment ? " is-adjustment" : (event.alert ? " is-alert" : (event.pending ? " is-pending" : ""));
    var evidence = event.image
      ? '<span class="tlab-route-node__evidence" aria-hidden="true"><img src="' + escapeAttr(event.image) + '" alt="" loading="lazy"></span>'
      : (event.mediaId ? '<span class="tlab-route-node__evidence" aria-hidden="true"><i class="fa-solid fa-image"></i></span>' : '');
    return '<li class="tlab-route-node' + classes + '">'
      + (event.elapsed ? '<span class="tlab-route-node__elapsed">' + escapeHtml(event.elapsed) + '</span>' : '<span class="tlab-route-node__elapsed tlab-route-node__elapsed--empty" aria-hidden="true"></span>')
      + '<button type="button" class="tlab-route-node__trigger" data-tlab-node-trigger data-tlab-node-lane="operativo" data-tlab-node-kind="' + escapeAttr(kind) + '" data-tlab-node-row-id="' + escapeAttr(rowId) + '" data-tlab-node-index="' + index + '" aria-haspopup="dialog" aria-expanded="false" aria-controls="tlabNodePopover" aria-label="Ver evento: ' + escapeAttr(event.title) + '">'
      + '<span class="tlab-route-node__avatar">' + avatarHtml(event.actor) + evidence + '</span>'
      + '<strong title="' + escapeAttr(event.title) + '">' + escapeHtml(event.title) + '</strong>'
      + '<time datetime="' + escapeAttr(event.date) + '">' + escapeHtml(formatDate(event.date, true)) + '</time></button>'
      + (index < length - 1 ? '<span class="tlab-route-node__connector" aria-hidden="true"></span>' : '')
      + '<span class="sr-only" id="tlab-node-' + escapeAttr(nodeKey) + '">' + escapeHtml(event.actor.name) + '</span></li>';
  }

  function routeHtml(route, kind, rowId, rowIndex) {
    var events = asArray(route);
    if (!events.length) {
      return '<div class="tlab-route-empty"><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><span>Sin nodos informados en el recorrido</span></div>';
    }
    return '<div class="tlab-route-scroll" tabindex="0" aria-label="Recorrido del trabajo; desplazamiento horizontal"><ol class="tlab-route-list">' + events.map(function (event, index) {
      return routeNodeHtml(event, index, events.length, kind, rowId, rowIndex);
    }).join("") + '</ol></div>';
  }

  function custodyNodeHtml(item, index, length, kind, rowId, rowIndex, forceCurrent) {
    var event = custodyEvent(item);
    if (forceCurrent) { event.current = true; }
    var classes = (event.current ? " is-current" : "") + (event.final ? " is-final" : "") + (event.cancelled ? " is-cancelled" : "") + (event.closed ? " is-closed" : "") + (event.inTransport ? " is-in-transport" : "") + (event.photoException ? " has-photo-exception" : "");
    var evidence = event.image
      ? '<span class="tlab-route-node__evidence" aria-label="Con evidencia"><img src="' + escapeAttr(event.image) + '" alt="" loading="lazy"></span>'
      : (event.mediaId ? '<span class="tlab-route-node__evidence" data-tlab-thumbnail-id="' + escapeAttr(event.mediaId) + '" aria-label="Cargando evidencia autorizada"><i class="fa-solid fa-camera"></i></span>'
        : (event.evidenceCount ? '<span class="tlab-route-node__evidence" aria-label="Con evidencia"><i class="fa-solid fa-camera"></i></span>'
          : (event.photoException ? '<span class="tlab-route-node__evidence tlab-route-node__evidence--exception" aria-label="Sin foto por excepción"><i class="fa-solid fa-camera-slash"></i></span>' : '')));
    var indicators = (event.noveltyCount ? '<span title="Novedades registradas"><i class="fa-solid fa-message" aria-hidden="true"></i>' + event.noveltyCount + '</span>' : '')
      + (event.inTransport ? '<span title="Trabajo en transporte"><i class="fa-solid fa-truck" aria-hidden="true"></i></span>' : '')
      + (event.historical ? '<span title="Registro anterior conservado"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span>' : '')
      + (event.cancelled ? '<span title="Custodia cerrada por cancelación"><i class="fa-solid fa-ban" aria-hidden="true"></i></span>' : '')
      + (event.condition ? '<span title="Condición de recepción"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></span>' : '');
    return '<li class="tlab-route-node tlab-custody-node' + classes + '">'
      + (event.elapsed ? '<span class="tlab-route-node__elapsed">' + escapeHtml(event.elapsed) + '</span>' : '<span class="tlab-route-node__elapsed tlab-route-node__elapsed--empty" aria-hidden="true"></span>')
      + '<button type="button" class="tlab-route-node__trigger" data-tlab-node-trigger data-tlab-node-lane="custodia" data-tlab-node-kind="' + escapeAttr(kind) + '" data-tlab-node-row-id="' + escapeAttr(rowId) + '" data-tlab-node-index="' + index + '" aria-haspopup="dialog" aria-expanded="false" aria-controls="tlabNodePopover" aria-label="Ver custodia: ' + escapeAttr(event.actor.name) + '">'
      + '<span class="tlab-route-node__avatar">' + avatarHtml(event.actor) + evidence + '</span>'
      + '<strong title="' + escapeAttr(event.title) + '">' + escapeHtml(event.title) + '</strong>'
      + '<time datetime="' + escapeAttr(event.date) + '">' + escapeHtml(formatDate(event.date, true)) + '</time>'
      + (indicators ? '<span class="tlab-custody-node__indicators">' + indicators + '</span>' : '') + '</button>'
      + (index < length - 1 ? '<span class="tlab-route-node__connector" aria-hidden="true"></span>' : '') + '</li>';
  }

  function custodyRouteHtml(chain, kind, rowId, rowIndex) {
    var nodes = asArray(chain);
    var hasCurrent = nodes.some(function (node) { return boolValue(pick(node, ["es_actual", "actual", "custodia_actual"], false)); });
    var lastIsTerminal = nodes.length ? boolValue(pick(nodes[nodes.length - 1], ["terminal", "es_final", "final", "finalizado"], false)) : false;
    if (!nodes.length) {
      return '<div class="tlab-route-empty tlab-route-empty--custody"><i class="fa-solid fa-link" aria-hidden="true"></i><span>El hilo comenzará al iniciar el trabajo</span></div>';
    }
    return '<div class="tlab-route-scroll tlab-route-scroll--custody" tabindex="0" aria-label="Hilo de custodia; desplazamiento horizontal"><ol class="tlab-route-list">' + nodes.map(function (node, index) {
      return custodyNodeHtml(node, index, nodes.length, kind, rowId, rowIndex, !hasCurrent && !lastIsTerminal && index === nodes.length - 1);
    }).join("") + '</ol></div>';
  }

  function eventId(item) {
    return toStringSafe(pick(item || {}, ["id_evento", "id"], ""));
  }

  function eventDate(item, lane) {
    return lane === "custodia"
      ? pick(item || {}, ["fecha_inicio", "inicio", "fecha_hora", "fecha_servidor", "fecha"], "")
      : pick(item || {}, ["fecha_hora", "fecha_servidor", "server_timestamp", "fecha", "creado_en"], "");
  }

  function unifiedWorkRoute(work) {
    var merged = {};
    var withoutId = [];
    function add(item, lane, index) {
      var id = eventId(item);
      var record = { raw: item, lane: lane, order: index };
      if (!id) {
        withoutId.push(record);
        return;
      }
      /* La custodia reemplaza al duplicado operativo porque contiene el
         responsable y la versión completa vinculada al mismo evento. */
      if (!merged[id] || lane === "custodia") { merged[id] = record; }
    }
    if (work.historicalOrigin) {
      add(work.historicalOrigin, "historico", -1);
    }
    asArray(work.route).forEach(function (item, index) { add(item, "operativo", index); });
    asArray(work.custodyChain).forEach(function (item, index) { add(item, "custodia", index); });
    return Object.keys(merged).map(function (key) { return merged[key]; }).concat(withoutId).sort(function (left, right) {
      var leftDate = toStringSafe(eventDate(left.raw, left.lane));
      var rightDate = toStringSafe(eventDate(right.raw, right.lane));
      if (leftDate !== rightDate) { return leftDate < rightDate ? -1 : 1; }
      return numberValue(eventId(left.raw), left.order) - numberValue(eventId(right.raw), right.order);
    });
  }

  function unifiedNodeHtml(record, index, length, work, rowIndex, hasEnd) {
    var event = record.lane === "custodia" ? custodyEvent(record.raw) : routeEvent(record.raw);
    var classes = record.lane === "custodia" ? " tlab-custody-node" : "";
    var evidence;
    var indicators = "";
    var ownCustody = record.lane === "custodia" && event.current
      && !!workActionByCode(work, "registrarInstalacion");
    if (record.lane === "custodia") {
      classes += (event.current ? " is-current" : "") + (event.final ? " is-final" : "")
        + (event.cancelled ? " is-cancelled" : "") + (event.closed ? " is-closed" : "")
        + (event.inTransport ? " is-in-transport" : "") + (event.photoException ? " has-photo-exception" : "");
      indicators = (event.editionCount ? '<span title="Versión editada"><i class="fa-solid fa-pen" aria-hidden="true"></i>' + event.editionCount + '</span>' : '')
        + (event.noveltyCount ? '<span title="Novedades registradas"><i class="fa-solid fa-message" aria-hidden="true"></i>' + event.noveltyCount + '</span>' : '')
        + (event.condition ? '<span title="Condición de recepción"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></span>' : '');
    } else {
      classes += event.adjustment ? " is-adjustment" : (event.alert ? " is-alert" : (event.pending ? " is-pending" : ""));
      if (record.lane === "historico") {
        classes += " is-historical-origin";
        indicators += '<span title="Version historica original"><i class="fa-solid fa-box-archive" aria-hidden="true"></i></span>';
      }
    }
    evidence = event.image
      ? '<span class="tlab-route-node__evidence" aria-label="Con fotografía"><img src="' + escapeAttr(event.image) + '" alt="" loading="lazy"></span>'
      : (event.mediaId ? '<span class="tlab-route-node__evidence" data-tlab-thumbnail-id="' + escapeAttr(event.mediaId) + '" aria-label="Cargando fotografía autorizada"><i class="fa-solid fa-camera" aria-hidden="true"></i></span>'
        : (event.evidenceCount ? '<span class="tlab-route-node__evidence" aria-label="Con fotografía"><i class="fa-solid fa-camera" aria-hidden="true"></i></span>'
          : (event.photoException ? '<span class="tlab-route-node__evidence tlab-route-node__evidence--exception" aria-label="Sin foto por excepción"><i class="fa-solid fa-camera-slash" aria-hidden="true"></i></span>' : '')));
    return '<li class="tlab-route-node tlab-unified-node' + classes + '">'
      + (event.elapsed ? '<span class="tlab-route-node__elapsed">' + escapeHtml(event.elapsed) + '</span>' : '<span class="tlab-route-node__elapsed tlab-route-node__elapsed--empty" aria-hidden="true"></span>')
      + '<button type="button" class="tlab-route-node__trigger" data-tlab-node-trigger data-tlab-node-lane="unificado" data-tlab-node-kind="operativo" data-tlab-node-row-id="' + escapeAttr(work.id) + '" data-tlab-node-index="' + index + '" aria-haspopup="dialog" aria-expanded="false" aria-controls="tlabNodePopover" aria-label="Ver nodo: ' + escapeAttr(event.title) + '">'
      + '<span class="tlab-route-node__avatar">' + avatarHtml(event.actor) + evidence + '</span>'
      + '<strong title="' + escapeAttr(event.title) + '">' + escapeHtml(event.title) + '</strong>'
      + '<time datetime="' + escapeAttr(event.date) + '">' + escapeHtml(formatDate(event.date, true)) + '</time>'
      + (ownCustody ? '<span class="tlab-current-custody-badge">TU CUSTODIA</span>' : '')
      + (indicators ? '<span class="tlab-custody-node__indicators">' + indicators + '</span>' : '') + '</button>'
      + (index < length - 1 || hasEnd ? '<span class="tlab-route-node__connector" aria-hidden="true"></span>' : '') + '</li>';
  }

  function closureNode(work) {
    var route = unifiedWorkRoute(work);
    var previousRecord = route.length ? route[route.length - 1] : null;
    var previousEvent = previousRecord
      ? (previousRecord.lane === "custodia" ? custodyEvent(previousRecord.raw) : routeEvent(previousRecord.raw))
      : null;
    var rawWork = work.raw || {};
    var cancelled = !!work.cancelled;
    var closedAt = cancelled
      ? pick(rawWork, ["fecha_cancelado", "fecha_completado", "fecha_actualizacion"], "")
      : pick(rawWork, ["fecha_instalado", "fecha_completado", "fecha_actualizacion"], "");
    if (!closedAt && previousEvent) { closedAt = previousEvent.endDate || previousEvent.date; }
    return {
      id: "cierre-" + toStringSafe(work.id),
      id_evento: null,
      id_trabajo: work.id,
      origen: "cierre_derivado",
      tipo_evento: cancelled ? "hilo_cancelado" : "hilo_cerrado",
      titulo: cancelled ? "Trabajo cancelado" : "Hilo cerrado",
      fecha_servidor: closedAt,
      actor: previousEvent ? previousEvent.actor : work.custodian,
      responsable: previousEvent ? previousEvent.actor : work.custodian,
      local: previousEvent ? previousEvent.branch : work.branch,
      ciclo_etiqueta: "Resultado final",
      observacion: cancelled
        ? "La custodia termino por cancelacion del trabajo."
        : "El seguimiento de laboratorio quedó instalado, finalizado y cerrado.",
      resultado: cancelled ? "Trabajo cancelado" : "Instalado y finalizado",
      tratamiento_porcentaje: cancelled ? null : 100,
      referencia_nodo_anterior: previousEvent ? previousEvent.title : "Ultimo nodo registrado",
      terminal: true,
      estado_terminal: cancelled ? "cancelado" : "instalado",
      motivo_cierre: cancelled ? "cancelacion" : "instalacion",
      pendiente: false
    };
  }

  function pendingNodeHtml(work) {
    var take = workActionByCode(work, "tomarHilo");
    var finish = workActionByCode(work, "registrarInstalacion");
    if (work.terminal) {
      return '<li class="tlab-route-node tlab-thread-end ' + (work.cancelled ? 'is-cancelled' : 'is-finished') + '"><button type="button" data-tlab-node-trigger data-tlab-node-lane="cierre" data-tlab-node-kind="operativo" data-tlab-node-row-id="' + escapeAttr(work.id) + '" data-tlab-node-index="0" aria-haspopup="dialog" aria-expanded="false" aria-controls="tlabNodePopover" aria-label="Consultar ' + (work.cancelled ? 'el cierre por cancelacion' : 'el cierre del hilo') + '"><span class="tlab-thread-end__icon"><i class="fa-solid ' + (work.cancelled ? 'fa-ban' : 'fa-flag-checkered') + '" aria-hidden="true"></i></span><strong>' + (work.cancelled ? 'Trabajo cancelado' : 'Hilo cerrado') + '</strong><small>' + (work.cancelled ? 'Custodia finalizada' : 'Consultar resultado') + '</small></button></li>';
    }
    if (take) {
      return '<li class="tlab-route-node tlab-thread-end is-action"><button type="button" data-tlab-take-node data-tlab-node-row-id="' + escapeAttr(work.id) + '" aria-haspopup="dialog" aria-expanded="false" aria-controls="tlabNodePopover" aria-label="Revisar y tomar el hilo"><span class="tlab-thread-end__icon"><i class="fa-solid fa-hand-holding" aria-hidden="true"></i><i class="fa-solid fa-minus tlab-thread-end__thread" aria-hidden="true"></i></span><strong>Tomar el hilo</strong><small>Revisar antes de recibir</small></button></li>';
    }
    if (finish) {
      return '<li class="tlab-route-node tlab-thread-end is-closure-guide" aria-label="El cierre está disponible desde tu último nodo"><span class="tlab-thread-end__icon"><i class="fa-solid fa-tooth" aria-hidden="true"></i><i class="fa-solid fa-check tlab-thread-end__final-check" aria-hidden="true"></i></span><strong>Cierre disponible</strong><small>Abrí tu último nodo para finalizar</small></li>';
    }
    return '<li class="tlab-route-node tlab-thread-end"><span class="tlab-thread-end__icon"><i class="fa-solid fa-hand-holding" aria-hidden="true"></i></span><strong>Próximo relevo</strong><small>Otro usuario puede tomarlo</small></li>';
  }

  function unifiedLaneHtml(work, rowIndex) {
    var route = unifiedWorkRoute(work);
    return '<section class="tlab-unified-lane" aria-label="Hilo único del trabajo"><header class="tlab-unified-lane__heading"><span><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>Hilo del trabajo</strong><small>Proceso, responsables y versiones</small></span></header><div class="tlab-route-scroll tlab-route-scroll--unified" tabindex="0" aria-label="Hilo del trabajo; desplazamiento horizontal"><ol class="tlab-route-list">'
      + route.map(function (record, index) { return unifiedNodeHtml(record, index, route.length, work, rowIndex, true); }).join("")
      + pendingNodeHtml(work) + '</ol></div></section>';
  }

  function identityPeopleHtml(doctor, mechanic) {
    return '<span class="tlab-thread-identity__people">' + personHtml(doctor, "Odontólogo") + personHtml(mechanic, "Mecánico dental") + '</span>';
  }

  function listedRouteFor(kind, rowId, lane) {
    var source = kind === "historico" ? state.historicals : state.works;
    var found = source.filter(function (item) {
      var normalized = kind === "historico" ? normalizeHistorical(item) : normalizeWork(item);
      return toStringSafe(normalized.id) === toStringSafe(rowId);
    })[0];
    var normalized;
    if (!found) { return []; }
    if (kind === "historico") { return normalizeHistorical(found).route; }
    normalized = normalizeWork(found);
    if (lane === "unificado") { return unifiedWorkRoute(normalized); }
    if (lane === "cierre") { return normalized.terminal ? [closureNode(normalized)] : []; }
    return lane === "custodia" ? normalized.custodyChain : normalized.route;
  }

  function listedWorkRecord(rowId) {
    return state.works.filter(function (item) {
      return toStringSafe(normalizeWork(item).id) === toStringSafe(rowId);
    })[0] || null;
  }

  function popoverFieldHtml(label, value) {
    return '<div><dt>' + escapeHtml(label) + '</dt><dd>' + escapeHtml(value || "Sin registrar") + '</dd></div>';
  }

  function nodePopoverHtml(event) {
    var evidence;
    if (event.mediaId) {
      evidence = '<button type="button" class="tlab-node-popover__evidence" data-tlab-media-id="' + escapeAttr(event.mediaId) + '" data-tlab-evidence-caption="' + escapeAttr(event.title) + '"><i class="fa-solid fa-image" aria-hidden="true"></i>Ver evidencia autorizada</button>';
    } else if (event.image) {
      evidence = '<button type="button" class="tlab-node-popover__evidence" data-tlab-evidence-url="' + escapeAttr(event.image) + '" data-tlab-evidence-caption="' + escapeAttr(event.title) + '"><i class="fa-solid fa-image" aria-hidden="true"></i>Ver evidencia disponible</button>';
    } else if (event.photoException) {
      evidence = '<span class="tlab-node-popover__no-evidence is-exception"><i class="fa-solid fa-camera-slash" aria-hidden="true"></i>' + escapeHtml(photoExceptionLabel(event)) + '</span>';
    } else {
      evidence = '<span class="tlab-node-popover__no-evidence"><i class="fa-solid fa-image" aria-hidden="true"></i>Sin evidencia asociada</span>';
    }
    return '<header class="tlab-node-popover__header"><div><small>' + escapeHtml(event.cycle) + '</small><h3>' + escapeHtml(event.title) + '</h3></div><button type="button" data-tlab-command="close-node-popover" aria-label="Cerrar detalle del evento"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<div class="tlab-node-popover__actor">' + avatarHtml(event.actor) + '<span><strong>' + escapeHtml(event.actor.name) + '</strong><small>' + escapeHtml(event.actor.role || "Sin rol informado") + '</small></span></div>'
      + '<dl class="tlab-node-popover__data">'
      + popoverFieldHtml("Fecha y hora", formatDate(event.date, true))
      + popoverFieldHtml("Duración", event.elapsed)
      + popoverFieldHtml("Sucursal / local", event.branch)
      + popoverFieldHtml("Ciclo", event.cycle)
      + popoverFieldHtml("Custodio anterior", event.previous)
      + popoverFieldHtml("Custodio nuevo", event.next)
      + popoverFieldHtml("Remitente", event.sender)
      + popoverFieldHtml("Destinatario", event.recipient)
      + (event.condition ? popoverFieldHtml("Condición al recibir", event.condition) : '')
      + (event.status ? popoverFieldHtml("Estado del período", event.status) : '')
      + (event.performedBy ? popoverFieldHtml("Acción registrada por", event.performedBy.name + " · " + event.performedBy.role) : '')
      + (event.historical ? popoverFieldHtml("Origen del dato", "Registro anterior conservado") : '')
      + (event.endDate ? popoverFieldHtml("Fin de custodia", formatDate(event.endDate, true)) : '')
      + (event.evidenceCount ? popoverFieldHtml("Evidencias", event.evidenceCount) : '')
      + (event.noveltyCount ? popoverFieldHtml("Novedades", event.noveltyCount) : '')
      + '</dl><div class="tlab-node-popover__note"><small>Observación</small><p>' + escapeHtml(event.note || "Sin observación registrada") + '</p></div>' + evidence;
  }

  function positionNodePopover(trigger, popover) {
    var triggerRect = trigger.getBoundingClientRect();
    var popoverRect;
    var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
    var left;
    var top;
    popover.style.left = "8px";
    popover.style.top = "8px";
    popoverRect = popover.getBoundingClientRect();
    left = Math.max(8, Math.min(triggerRect.left + (triggerRect.width / 2) - (popoverRect.width / 2), viewportWidth - popoverRect.width - 8));
    if (popover.classList.contains("is-editor") && triggerRect.left > viewportWidth / 2) {
      left = Math.max(8, Math.min(triggerRect.right - popoverRect.width, viewportWidth - popoverRect.width - 8));
    }
    top = triggerRect.bottom + 8;
    if (top + popoverRect.height > viewportHeight - 8) { top = Math.max(8, triggerRect.top - popoverRect.height - 8); }
    popover.style.left = Math.round(left) + "px";
    popover.style.top = Math.round(top) + "px";
  }

  function rawUnifiedNode(routeItem, lane) {
    return lane === "unificado" && routeItem && routeItem.raw ? routeItem.raw : routeItem;
  }

  function loadNodeEnvelope(rowId) {
    var key = toStringSafe(rowId);
    var cached = state.nodeDetailCache[key];
    if (cached && typeof cached.then === "function") { return cached; }
    if (cached) { return Promise.resolve(cached); }
    state.nodeDetailCache[key] = request("obtenerTrabajo", { id_trabajo: rowId, cod_trabajo_laboratorio: rowId }).then(function (response) {
      var envelope = response.data || {};
      var work = envelope.trabajo || envelope.item || envelope;
      if (!work.acciones_permitidas && envelope.acciones_permitidas) { work.acciones_permitidas = envelope.acciones_permitidas; }
      ["recorrido_operativo", "cadena_custodia", "hilo_custodia", "custodia_actual", "novedades"].forEach(function (keyName) {
        if ((work[keyName] === undefined || work[keyName] === null) && envelope[keyName] !== undefined) { work[keyName] = envelope[keyName]; }
      });
      if (!work.version && response.version) { work.version = response.version; }
      mergeContext(envelope);
      state.nodeDetailCache[key] = { envelope: envelope, work: work };
      return state.nodeDetailCache[key];
    }).then(null, function (error) {
      delete state.nodeDetailCache[key];
      throw error;
    });
    return state.nodeDetailCache[key];
  }

  function loadHistoricalNodeEnvelope(rowId) {
    var key = "historico:" + toStringSafe(rowId);
    var cached = state.nodeDetailCache[key];
    if (cached && typeof cached.then === "function") { return cached; }
    if (cached) { return Promise.resolve(cached); }
    state.nodeDetailCache[key] = request("obtenerHistorico", {
      id_historico: rowId,
      cod_trabajo_mecanico_dental: rowId
    }).then(function (response) {
      var envelope = response.data || {};
      var historical = envelope.historico || envelope.trabajo_historico || envelope.item || envelope;
      if (!historical.version && response.version) { historical.version = response.version; }
      mergeContext(envelope);
      state.nodeDetailCache[key] = { envelope: envelope, historical: historical };
      return state.nodeDetailCache[key];
    }).then(null, function (error) {
      delete state.nodeDetailCache[key];
      throw error;
    });
    return state.nodeDetailCache[key];
  }

  function nodeSnapshot(raw, work, takeMode) {
    var snapshot = raw && raw.datos_trabajo && typeof raw.datos_trabajo === "object" ? raw.datos_trabajo : null;
    var normalized;
    var chain;
    var current;
    if (!snapshot && work) {
      normalized = normalizeWork(work);
      chain = normalized.custodyChain;
      current = chain.filter(function (node) { return boolValue(pick(node, ["actual", "es_actual", "custodia_actual"], false)); })[0]
        || (chain.length ? chain[chain.length - 1] : null);
      if ((takeMode || (raw && boolValue(pick(raw, ["actual", "es_actual", "custodia_actual"], false))))
          && current && current.datos_trabajo && typeof current.datos_trabajo === "object") {
        snapshot = current.datos_trabajo;
      }
    }
    if (snapshot) {
      normalized = normalizeWork(work || {});
      snapshot = Object.assign({}, snapshot);
      snapshot.producto = snapshot.producto || normalized.product;
      snapshot.iniciador = snapshot.iniciador
        || pick(work || {}, ["iniciador", "nombre_iniciador"], "");
      return snapshot;
    }
    if (!work) { return null; }
    normalized = normalizeWork(work);
    return {
      cod_tipo_trabajo: pick(work, ["cod_tipo_trabajo", "cod_tipo_trabajoFK"], ""),
      tipo_trabajo: pick(work, ["tipo_trabajo"], normalized.product),
      paciente: normalized.patient,
      producto: normalized.product,
      colorimetro: pick(work, ["colorimetro", "color"], ""),
      cod_especialista: pick(work, ["cod_especialista", "cod_especialistaFK"], ""),
      doctor: person(normalized.doctor).name,
      cod_iniciador: pick(work, ["cod_iniciador", "cod_usuarioFK_create"], ""),
      iniciador: pick(work, ["iniciador", "nombre_iniciador"], ""),
      cod_tecnico_usuario: pick(work, ["cod_tecnico_usuario", "cod_tecnico_usuarioFK"], ""),
      mecanico_dental: person(normalized.mechanic).name,
      fecha_retiro: pick(work, ["fecha_retiro"], ""),
      fecha_entrega: pick(work, ["fecha_entrega"], ""),
      costo_estimado: pick(work, ["costo_estimado"], ""),
      estado: pick(work, ["estado_derivado", "estado"], normalized.situation),
      cod_local: pick(work, ["cod_local", "cod_localFK"], ""),
      local: normalized.branch,
      observacion: pick(work, ["instrucciones", "observacion"], "")
    };
  }

  function nodeMedia(record) {
    var envelope = record && record.envelope ? record.envelope : {};
    var raw = record && record.raw ? record.raw : {};
    var ids = asArray(raw.eventos_version);
    var baseId = eventId(raw);
    var media = asArray(pick(envelope, ["media", "evidencias"], []));
    if (!ids.length && baseId) { ids = [baseId]; }
    ids = ids.map(toStringSafe);
    return media.filter(function (item) {
      return ids.indexOf(toStringSafe(pick(item, ["id_evento", "id_eventoFK"], ""))) >= 0;
    });
  }

  function nodeMediaHtml(record, event) {
    var media = nodeMedia(record);
    if (media.length) {
      return '<div class="tlab-node-media" aria-label="Archivos de esta versión">' + media.map(function (item) {
        var mediaId = pick(item, ["id", "id_media"], "");
        var thumb = pick(item, ["miniatura_url", "url_visualizacion"], "");
        var documentFile = toStringSafe(pick(item, ["mime"], "")).toLowerCase() === "application/pdf";
        return '<button type="button" data-tlab-media-id="' + escapeAttr(mediaId) + '" data-tlab-evidence-caption="' + escapeAttr(pick(item, ["descripcion", "nombre"], event.title)) + '" aria-label="Abrir archivo de esta versión">'
          + (thumb ? '<img src="' + escapeAttr(thumb) + '" alt="" loading="lazy">' : '<i class="fa-solid ' + (documentFile ? 'fa-file-pdf' : 'fa-image') + '" aria-hidden="true"></i>') + '</button>';
      }).join("") + '</div>';
    }
    if (event.mediaId) {
      return '<button type="button" class="tlab-node-popover__evidence" data-tlab-media-id="' + escapeAttr(event.mediaId) + '" data-tlab-evidence-caption="' + escapeAttr(event.title) + '"><i class="fa-solid fa-image" aria-hidden="true"></i>Ver fotografía de este nodo</button>';
    }
    if (event.photoException) {
      return '<span class="tlab-node-popover__no-evidence is-exception"><i class="fa-solid fa-camera-slash" aria-hidden="true"></i>' + escapeHtml(photoExceptionLabel(event)) + '</span>';
    }
    return '<span class="tlab-node-popover__no-evidence"><i class="fa-solid fa-image" aria-hidden="true"></i>Este nodo no incorporó fotografías ni documentos</span>';
  }

  function changedNodeField(changed, keys) {
    return keys.some(function (key) { return changed.indexOf(key) >= 0; });
  }

  function nodeWorkFieldHtml(label, value, changed) {
    return '<div class="tlab-node-work-field' + (changed ? ' is-modified' : '') + '"><small>' + escapeHtml(label) + (changed ? '<i class="fa-solid fa-pen" aria-hidden="true"></i><span class="sr-only"> Modificado en esta versión</span>' : '') + '</small><strong>' + escapeHtml(value === null || value === "" || typeof value === "undefined" ? "No asignado" : value) + '</strong></div>';
  }

  function nodeActionsHtml(work, raw) {
    var actions;
    var closure;
    var normalized;
    var standard;
    var html = "";
    if (!boolValue(pick(raw, ["actual", "es_actual", "custodia_actual"], false))) { return ""; }
    actions = normalizeActions(work.acciones_permitidas || []);
    actions = actions.filter(function (action) { return action.code !== "tomarHilo" && actionAllowedInCurrentContext(action); });
    if (!actions.length) { return ""; }
    closure = actions.filter(function (action) { return action.code === "registrarInstalacion"; })[0] || null;
    standard = actions.filter(function (action) { return action.code !== "registrarInstalacion"; });
    if (standard.length) {
      html += '<div class="tlab-node-popover__actions" aria-label="Acciones del nodo actual">' + standard.map(function (action) {
      return '<button type="button" data-tlab-popover-action="' + escapeAttr(action.code) + '"><i class="fa-solid ' + escapeAttr(action.icon || "fa-arrow-right") + '" aria-hidden="true"></i>' + escapeHtml(action.label) + '</button>';
      }).join("") + '</div>';
    }
    if (closure) {
      normalized = normalizeWork(work);
      html += '<section class="tlab-node-popover__closure" aria-label="Finalización del hilo">'
        + '<div class="tlab-node-popover__closure-heading"><span>CUSTODIO ACTUAL</span><strong>Podés finalizar este hilo</strong><small>Sos responsable del último nodo.</small></div>'
        + (normalized.pendingTransfer ? '<p class="tlab-node-popover__closure-warning"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><span><strong>Transferencia pendiente</strong>Se cerrará junto con el hilo, dejando constancia.</span></p>' : '')
        + '<button type="button" class="tlab-node-popover__closure-button" data-tlab-popover-action="registrarInstalacion"><i class="fa-solid fa-tooth" aria-hidden="true"></i>Instalado y finalizado</button>'
        + '<small class="tlab-node-popover__closure-help"><i class="fa-solid fa-camera" aria-hidden="true"></i>Requiere una fotografía o una excepción justificada.</small></section>';
    }
    return html;
  }

  function nodeVersionPopoverHtml(record) {
    var event = record.event;
    var raw = record.raw || {};
    var work = record.work || {};
    var snapshot = nodeSnapshot(raw, work, false);
    var changed = asArray(raw.campos_modificados);
    var currentUser = numberValue(pick(state.context, ["cod_usuario"], 0), 0);
    var responsibleRaw = raw.responsable || raw.custodio_nuevo || {};
    var responsibleId = numberValue(pick(responsibleRaw, ["cod_usuario", "id"], pick(raw, ["cod_custodio_nuevoFK"], 0)), 0);
    var canEdit = !!snapshot && boolValue(pick(raw, ["actual", "es_actual", "custodia_actual"], false))
      && !event.terminal && currentUser > 0 && responsibleId === currentUser;
    var versionLabel = event.versionNumber ? "Versión " + event.versionNumber : event.cycle;
    var status = snapshot ? humanizeHistoricalValue(snapshot.estado || event.status, "PENDIENTE") : event.status;
    var fields = "";
    var costField = snapshot && canManageWorkCost()
      ? nodeWorkFieldHtml("Costo", snapshot.costo_estimado === null || snapshot.costo_estimado === "" ? "Sin registrar" : snapshot.costo_estimado, changedNodeField(changed, ["costo_estimado"]))
      : "";
    if (snapshot) {
      fields = '<div class="tlab-node-work-grid">'
        + nodeWorkFieldHtml("Tipo de trabajo", snapshot.producto || normalizeWork(work).product, false)
        + nodeWorkFieldHtml("Colorimetría", snapshot.colorimetro || "No asignado", changedNodeField(changed, ["colorimetro"]))
        + nodeWorkFieldHtml("Paciente", snapshot.paciente || normalizeWork(work).patient, false)
        + nodeWorkFieldHtml("Iniciado por", snapshot.iniciador || pick(work, ["iniciador", "nombre_iniciador"], "No asignado"), false)
        + nodeWorkFieldHtml("Mecánico dental", snapshot.mecanico_dental || "No asignado", changedNodeField(changed, ["cod_mecanico_dental", "cod_tecnico_usuario"]))
        + nodeWorkFieldHtml("Retiro", snapshot.fecha_retiro ? formatDate(snapshot.fecha_retiro, false) : "Sin fecha definida", changedNodeField(changed, ["fecha_retiro"]))
        + nodeWorkFieldHtml("Entrega", snapshot.fecha_entrega ? formatDate(snapshot.fecha_entrega, false) : "Sin fecha definida", changedNodeField(changed, ["fecha_entrega"]))
        + costField
        + nodeWorkFieldHtml("Local", snapshot.local || "No asignado", changedNodeField(changed, ["cod_local"])) + '</div>'
        + '<div class="tlab-node-popover__note' + (changedNodeField(changed, ["observacion"]) ? ' is-modified' : '') + '"><small>Observación' + (changedNodeField(changed, ["observacion"]) ? ' <i class="fa-solid fa-pen" aria-hidden="true"></i>' : '') + '</small><p>' + escapeHtml(snapshot.observacion || "Sin observación registrada") + '</p></div>';
    } else {
      fields = '<dl class="tlab-node-popover__data">' + popoverFieldHtml("Fecha y hora", formatDate(event.date, true))
        + popoverFieldHtml("Duración", event.elapsed) + popoverFieldHtml("Sucursal / local", event.branch)
        + popoverFieldHtml("Custodio anterior", event.previous) + popoverFieldHtml("Custodio nuevo", event.next) + '</dl>'
        + '<div class="tlab-node-popover__note"><small>Observación</small><p>' + escapeHtml(event.note || "Sin observación registrada") + '</p></div>';
    }
    return '<header class="tlab-node-popover__header"><div><small>' + escapeHtml(versionLabel) + '</small><h3>' + escapeHtml(event.title) + '</h3></div><span class="tlab-node-status">' + escapeHtml(status || "Registrado") + '</span><button type="button" data-tlab-command="close-node-popover" aria-label="Cerrar detalle del nodo"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<div class="tlab-node-popover__actor">' + avatarHtml(event.actor) + '<span><strong>' + escapeHtml(event.actor.name) + '</strong><small>' + escapeHtml((event.current ? "Responsable actual · " : "Responsable del nodo · ") + (event.actor.role || "Usuario Telar")) + '</small></span></div>'
      + nodeMediaHtml(record, event) + fields
      + (event.condition ? '<p class="tlab-node-condition"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>Recibido ' + escapeHtml(humanizeHistoricalValue(event.condition)) + '</p>' : '')
      + (canEdit ? '<div class="tlab-node-popover__edit"><button type="button" data-tlab-node-edit><i class="fa-solid fa-pen" aria-hidden="true"></i>Editar versión activa</button><small>La edición queda auditada en este mismo nodo.</small></div>' : '')
      + nodeActionsHtml(work, raw);
  }

  function historicalVersionPopoverHtml(record) {
    var event = record.event;
    var historical = record.historical || {};
    var normalized = normalizeHistorical(historical);
    var originalNode = toStringSafe(pick(record.raw || {}, ["tipo_evento"], "")) === "registro_original";
    var author = {
      name: historical.autor_original || normalized.author || event.actor.name,
      role: historical.autor_original_rol || event.actor.role || "Usuario del registro original",
      avatar: historical.autor_original_avatar || event.actor.avatar || ""
    };
    var declaredState = originalNode
      ? humanizeHistoricalValue(historical.estado_original || pick(record.raw || {}, ["estado_original", "estado"], ""), "Situación original sin definir")
      : (historical.estado_declarado
        ? historical.estado_declarado_nombre || humanizeHistoricalValue(historical.estado_declarado)
        : "Situación por actualizar");
    var mechanic = originalNode
      ? historical.mecanico_snapshot || normalized.mechanic.name
      : historical.mecanico_declarado || historical.mecanico_snapshot || normalized.mechanic.name;
    var withdrawalDate = originalNode
      ? historical.fecha_retiro_original
      : historical.fecha_retiro_declarada || historical.fecha_retiro_original;
    var deliveryDate = originalNode
      ? historical.fecha_entrega_original
      : historical.fecha_entrega_declarada || historical.fecha_entrega_original;
    var branch = originalNode
      ? historical.local_snapshot || normalized.branch
      : historical.local_declarado || historical.local_snapshot || normalized.branch;
    var historicalCostField = canManageWorkCost()
      ? nodeWorkFieldHtml("Costo", historical.costo_original === null || historical.costo_original === "" ? "Sin registrar" : historical.costo_original, false)
      : "";
    var evidence = event.mediaId
      ? '<button type="button" class="tlab-node-popover__evidence" data-tlab-media-id="' + escapeAttr(event.mediaId) + '" data-tlab-evidence-caption="Registro histórico"><i class="fa-solid fa-image" aria-hidden="true"></i>Ver fotografía del registro</button>'
      : (event.image
        ? '<button type="button" class="tlab-node-popover__evidence" data-tlab-evidence-url="' + escapeAttr(event.image) + '" data-tlab-evidence-caption="Registro histórico"><i class="fa-solid fa-image" aria-hidden="true"></i>Ver fotografía del registro</button>'
        : '<span class="tlab-node-popover__no-evidence"><i class="fa-solid fa-image" aria-hidden="true"></i>El registro importado no contiene fotografías</span>');
    return '<header class="tlab-node-popover__header"><div><small>Versión histórica original</small><h3>' + escapeHtml(event.title || "Registro original") + '</h3></div><span class="tlab-node-status">' + escapeHtml(declaredState) + '</span><button type="button" data-tlab-command="close-node-popover" aria-label="Cerrar detalle"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<div class="tlab-node-popover__actor">' + avatarHtml(author) + '<span><strong>' + escapeHtml(author.name) + '</strong><small>' + escapeHtml(author.role) + '</small></span></div>'
      + evidence
      + '<div class="tlab-node-work-grid">'
      + nodeWorkFieldHtml("Tipo de trabajo", historical.tipo_trabajo || normalized.product, false)
      + nodeWorkFieldHtml("Colorimetría", historical.colorimetro_original || "No asignado", false)
      + nodeWorkFieldHtml("Paciente", normalized.patient, false)
      + nodeWorkFieldHtml("Producto de la venta", historical.producto || normalized.product, false)
      + nodeWorkFieldHtml("Doctor", historical.doctor || normalized.doctor.name, false)
      + nodeWorkFieldHtml("Mecánico dental", mechanic, false)
      + nodeWorkFieldHtml("Retiro", withdrawalDate ? formatDate(withdrawalDate, false) : "Sin fecha definida", false)
      + nodeWorkFieldHtml("Entrega", deliveryDate ? formatDate(deliveryDate, false) : "Sin fecha definida", false)
      + historicalCostField
      + nodeWorkFieldHtml("Local", branch, false)
      + '</div><div class="tlab-node-popover__note"><small>Observación</small><p>'
      + escapeHtml(historical.observacion_original || event.note || "Sin observación registrada")
      + '</p></div><p class="tlab-node-history-note"><i class="fa-solid fa-box-archive" aria-hidden="true"></i>Esta versión se conserva como respaldo y no se sobrescribe al resolver el trabajo.</p>';
  }

  function closurePopoverHtml(record) {
    var work = normalizeWork(record.work || {});
    var closure = closureNode(work);
    var event = routeEvent(closure);
    var status = work.cancelled ? "CANCELADO" : "FINALIZADO";
    return '<header class="tlab-node-popover__header"><div><small>Resultado final del hilo</small><h3>'
      + escapeHtml(closure.titulo) + '</h3></div><span class="tlab-node-status">'
      + escapeHtml(status) + '</span><button type="button" data-tlab-command="close-node-popover" aria-label="Cerrar detalle del cierre"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<div class="tlab-node-popover__actor">' + avatarHtml(event.actor) + '<span><strong>'
      + escapeHtml(event.actor.name) + '</strong><small>Responsable final del seguimiento</small></span></div>'
      + '<dl class="tlab-node-popover__data">'
      + popoverFieldHtml("Fecha y hora del cierre", formatDate(event.date, true))
      + popoverFieldHtml("Resultado", closure.resultado)
      + popoverFieldHtml("Estado del hilo", "Cerrado")
      + (!work.cancelled ? popoverFieldHtml("Tratamiento", "100 % finalizado") : "")
      + popoverFieldHtml("Nodo anterior", closure.referencia_nodo_anterior)
      + popoverFieldHtml("Sucursal / local", event.branch)
      + '</dl><p class="tlab-node-history-note"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i>'
      + 'Este nodo resume el cierre registrado en el nodo anterior. Es de solo lectura y no reemplaza ninguna versión del recorrido.</p>';
  }

  function catalogValue(item, keys) {
    return typeof item === "object" ? pick(item, keys, "") : item;
  }

  function catalogLabel(item) {
    return typeof item === "object"
      ? pick(item, ["nombre", "producto", "nombre_producto", "descripcion", "etiqueta", "label", "texto"],
        catalogValue(item, ["id", "codigo", "cod", "valor", "value"]))
      : item;
  }

  function inferCatalogValue(items, value, label, keys) {
    if (toStringSafe(value)) { return value; }
    var target = toStringSafe(label).trim().toLowerCase();
    var found = asArray(items).filter(function (item) { return toStringSafe(catalogLabel(item)).trim().toLowerCase() === target; })[0];
    return found ? catalogValue(found, keys) : "";
  }

  function includeCurrentCatalogOption(items, value, keys, label) {
    var source = asArray(items).slice();
    var exists = source.some(function (item) {
      return toStringSafe(catalogValue(item, keys)) === toStringSafe(value);
    });
    var fallback;
    if (!toStringSafe(value) || exists) { return source; }
    fallback = { nombre: "Actual · " + humanizeHistoricalValue(label, value) };
    fallback[keys[0]] = value;
    source.unshift(fallback);
    return source;
  }

  function nodeSelectHtml(label, name, items, selected, keys, required) {
    var options = '<option value="">' + (required ? 'Seleccionar' : 'No asignado') + '</option>';
    asArray(items).forEach(function (item) {
      var value = catalogValue(item, keys);
      options += '<option value="' + escapeAttr(value) + '"' + (toStringSafe(value) === toStringSafe(selected) ? ' selected' : '') + '>' + escapeHtml(catalogLabel(item)) + '</option>';
    });
    return '<label class="tlab-node-form__field"><span>' + escapeHtml(label) + (required ? ' *' : '') + '</span><select name="' + escapeAttr(name) + '"' + (required ? ' required' : '') + '>' + options + '</select></label>';
  }

  function dateInputValue(value) {
    var match = toStringSafe(value).match(/^\d{4}-\d{2}-\d{2}/);
    return match ? match[0] : "";
  }

  function nodeFilePreviewHtml(file, index) {
    var url = file._tlabNodeUrl;
    if (!url) {
      url = URL.createObjectURL(file);
      file._tlabNodeUrl = url;
      state.nodeObjectUrls.push(url);
    }
    return '<figure class="tlab-node-file-preview" data-tlab-prepared-size="' + escapeAttr(file.size || 0) + '"><img src="' + escapeAttr(url) + '" alt="Nueva fotografía ' + (index + 1) + '"><figcaption><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Lista</figcaption><button type="button" data-tlab-node-preview-remove="' + index + '" aria-label="Quitar fotografía"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></figure>';
  }

  function nodeMediaPickerHtml() {
    var processing = state.nodeFilesProcessing;
    return '<div class="tlab-node-media-actions"><button type="button" data-tlab-command="open-camera-node" ' + (processing ? "disabled" : "") + '><i class="fa-solid fa-camera" aria-hidden="true"></i><span>Tomar foto</span></button><label class="' + (processing ? "is-disabled" : "") + '"><i class="fa-solid fa-images" aria-hidden="true"></i><span>Galería</span><input type="file" accept="image/jpeg,image/png,image/webp" multiple data-tlab-node-file-input ' + (processing ? "disabled" : "") + '></label></div>'
      + (processing ? '<p class="tlab-file-processing tlab-file-processing--compact"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i><span><strong>Preparando fotografía...</strong>Podés continuar cuando aparezca la miniatura.</span></p>' : '')
      + '<div class="tlab-node-file-list">' + state.nodeFiles.map(nodeFilePreviewHtml).join("") + '</div>';
  }

  function nodeEditorHtml() {
    var editor = state.nodeEditor;
    var record = state.nodePopoverRecord;
    var work = record.work || {};
    var snapshot = editor.values;
    var mechanics = catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"]);
    var branches = catalogItems(["locales", "sucursales"]);
    var mechanicId = inferCatalogValue(mechanics, snapshot.cod_tecnico_usuario, snapshot.mecanico_dental, ["cod_tecnico_usuario", "cod_usuario", "id", "codigo", "cod"]);
    var branchId = inferCatalogValue(branches, snapshot.cod_local, snapshot.local, ["cod_local", "id", "codigo", "cod"]);
    var taking = editor.mode === "take";
    var costInput = canManageWorkCost()
      ? '<label class="tlab-node-form__field"><span>Costo</span><input name="costo_estimado" type="number" min="0" step="1" value="' + escapeAttr(snapshot.costo_estimado === null ? "" : snapshot.costo_estimado) + '"></label>'
      : "";
    var existingMedia = record.raw && eventId(record.raw)
      ? '<section class="tlab-node-existing-media"><small>Archivos de la versión que estás revisando</small>' + nodeMediaHtml(record, custodyEvent(record.raw)) + '</section>' : '';
    var newMedia = '<section class="tlab-node-new-media" aria-busy="' + (state.nodeFilesProcessing ? "true" : "false") + '"><div><strong>' + (taking ? 'Nueva fotografía *' : 'Nueva fotografía') + '</strong><small>Se mostrará únicamente en ' + (taking ? 'el nuevo nodo' : 'esta versión activa') + '; Telar ajusta el peso automáticamente.</small></div>' + nodeMediaPickerHtml() + '</section>';
    return '<header class="tlab-node-popover__header"><div><small>' + (taking ? 'Trabajo a recibir' : 'Versión activa') + '</small><h3>' + (taking ? 'Revisar y tomar el hilo' : 'Editar datos del nodo') + '</h3></div><span class="tlab-node-status">' + escapeHtml(humanizeHistoricalValue(snapshot.estado, "PENDIENTE")) + '</span><button type="button" data-tlab-command="close-node-popover" aria-label="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<form id="tlabNodeVersionForm" class="tlab-node-form" novalidate><div class="tlab-node-readonly"><span><small>Paciente</small><strong>' + escapeHtml(snapshot.paciente || normalizeWork(work).patient) + '</strong></span><span><small>Tipo de trabajo</small><strong>' + escapeHtml(snapshot.producto || normalizeWork(work).product) + '</strong></span><span><small>Iniciado por</small><strong>' + escapeHtml(snapshot.iniciador || pick(work, ["iniciador", "nombre_iniciador"], "No asignado")) + '</strong></span><span><small>Estado del proceso</small><strong>' + escapeHtml(humanizeHistoricalValue(snapshot.estado, "PENDIENTE")) + '</strong></span></div>'
      + existingMedia + newMedia
      + '<div class="tlab-node-form__grid">'
      + '<label class="tlab-node-form__field"><span>Colorimetría</span><input name="colorimetro" type="text" maxlength="30" value="' + escapeAttr(snapshot.colorimetro || "") + '" placeholder="Ej.: B2"></label>'
      + nodeSelectHtml("Mecánico dental", "cod_tecnico_usuario", mechanics, mechanicId, ["cod_tecnico_usuario", "cod_usuario", "id", "codigo", "cod"], false)
      + '<label class="tlab-node-form__field"><span>Fecha de retiro</span><input name="fecha_retiro" type="date" value="' + escapeAttr(dateInputValue(snapshot.fecha_retiro)) + '"></label>'
      + '<label class="tlab-node-form__field"><span>Fecha de entrega</span><input name="fecha_entrega" type="date" value="' + escapeAttr(dateInputValue(snapshot.fecha_entrega)) + '"></label>'
      + costInput
      + nodeSelectHtml("Local", "cod_local", branches, branchId, ["cod_local", "id", "codigo", "cod"], true) + '</div>'
      + '<label class="tlab-node-form__field tlab-node-form__field--wide"><span>Observación del trabajo</span><textarea name="datos_observacion" maxlength="1000" rows="3">' + escapeHtml(snapshot.observacion || "") + '</textarea></label>'
      + (taking ? '<fieldset class="tlab-node-condition-field"><legend>¿Cómo recibís el trabajo? *</legend><label><input type="radio" name="condicion_recepcion" value="conforme" checked><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Conforme</span></label><label><input type="radio" name="condicion_recepcion" value="con_observaciones"><span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>Con observaciones</span></label></fieldset><label class="tlab-node-form__field tlab-node-form__field--wide"><span>Observación de recepción</span><textarea name="observacion_recepcion" maxlength="1000" rows="2" placeholder="Obligatoria si recibís con observaciones"></textarea></label>' : '')
      + '<div class="tlab-form-error" id="tlabNodeFormError"' + (editor.error ? '' : ' hidden') + '>' + escapeHtml(editor.error || "") + '</div>'
      + '<footer class="tlab-node-form__footer"><button type="button" class="tlab-button tlab-button--secondary" data-tlab-node-edit-cancel ' + (editor.saving ? 'disabled' : '') + '>Cancelar</button><button type="submit" class="tlab-button tlab-button--primary" ' + (editor.saving || state.nodeFilesProcessing ? 'disabled' : '') + '><i class="fa-solid ' + (editor.saving || state.nodeFilesProcessing ? 'fa-hourglass-half' : (taking ? 'fa-hand-holding' : 'fa-floppy-disk')) + '" aria-hidden="true"></i>' + (editor.saving ? 'Guardando...' : (state.nodeFilesProcessing ? 'Preparando foto...' : (taking ? 'Confirmar recepción' : 'Guardar cambios'))) + '</button></footer></form>';
  }

  function renderNodeEditor() {
    var popover = state.root && state.root.querySelector("#tlabNodePopover");
    if (!popover || !state.nodeEditor || !state.nodePopoverRecord) { return; }
    popover.classList.add("is-editor");
    popover.innerHTML = nodeEditorHtml();
    positionNodePopover(state.nodePopover, popover);
  }

  function beginCurrentNodeEdit() {
    var record = state.nodePopoverRecord;
    var snapshot;
    if (!record || !record.work || !record.raw) { return; }
    if (!catalogsAreFresh()) {
      notify("Preparando las opciones editables...", "info");
      loadCatalogs(false).then(function () {
        if (state.nodePopoverRecord === record) { beginCurrentNodeEdit(); }
      }).then(null, function (error) {
        notify(error.message || "No se pudieron cargar las opciones editables.", "error");
      });
      return;
    }
    snapshot = nodeSnapshot(record.raw, record.work, false);
    if (!snapshot) { notify("Este nodo histórico no tiene una versión completa editable.", "info"); return; }
    state.nodeEditor = { mode: "edit", values: snapshot, idempotencyKey: makeIdempotencyKey(), saving: false, error: "" };
    revokeNodeObjectUrls();
    state.nodeFiles = [];
    state.nodeFilesProcessing = false;
    renderNodeEditor();
  }

  function cancelNodeEdit() {
    var mode = state.nodeEditor ? state.nodeEditor.mode : "";
    if (state.nodeEditor && state.nodeEditor.saving) { return; }
    state.nodeEditor = null;
    if (state.camera && state.camera.target === "node") { closeCamera(false); }
    revokeNodeObjectUrls();
    state.nodeFiles = [];
    state.nodeFilesProcessing = false;
    if (mode === "take") { closeNodePopover(true); return; }
    if (state.nodePopoverRecord) {
      var popover = state.root.querySelector("#tlabNodePopover");
      popover.classList.remove("is-editor");
      popover.innerHTML = nodeVersionPopoverHtml(state.nodePopoverRecord);
      positionNodePopover(state.nodePopover, popover);
    }
  }

  function addNodeFiles(fileList) {
    var owner = state.nodeEditor || state.historicalResolver;
    var preparation;
    if (!owner || state.nodeFilesProcessing) { return; }
    preparation = prepareMediaSelection(fileList, false, MAX_FILES - state.nodeFiles.length);
    state.nodeFilesProcessing = true;
    owner.error = "";
    if (state.historicalResolver) { renderHistoricalResolver(); }
    else { renderNodeEditor(); }
    preparation.then(function (result) {
      if ((state.nodeEditor || state.historicalResolver) !== owner) {
        releasePreparedMedia(result.files);
        return;
      }
      result.files.forEach(function (file) { state.nodeFiles.push(file); });
      state.nodeFilesProcessing = false;
      owner.error = result.error || "";
      if (state.historicalResolver) { renderHistoricalResolver(); }
      else { renderNodeEditor(); }
    }).then(null, function (error) {
      if ((state.nodeEditor || state.historicalResolver) !== owner) { return; }
      state.nodeFilesProcessing = false;
      owner.error = error.message || "No se pudo preparar la fotografía.";
      if (state.historicalResolver) { renderHistoricalResolver(); }
      else { renderNodeEditor(); }
    });
  }

  function removeNodeFile(index) {
    var file;
    if (index < 0 || index >= state.nodeFiles.length) { return; }
    file = state.nodeFiles[index];
    if (file._tlabNodeUrl) {
      try { URL.revokeObjectURL(file._tlabNodeUrl); } catch (ignore) {}
      state.nodeObjectUrls = state.nodeObjectUrls.filter(function (url) { return url !== file._tlabNodeUrl; });
    }
    state.nodeFiles.splice(index, 1);
    if (state.nodeEditor) { state.nodeEditor.error = ""; }
    if (state.historicalResolver) { state.historicalResolver.error = ""; }
    if (state.historicalResolver) { renderHistoricalResolver(); }
    else { renderNodeEditor(); }
  }

  function revokeNodeObjectUrls() {
    state.nodeObjectUrls.forEach(function (url) { try { URL.revokeObjectURL(url); } catch (ignore) {} });
    state.nodeObjectUrls = [];
  }

  function nodeFormValues() {
    var form = state.root.querySelector("#tlabNodeVersionForm");
    var values = {};
    forEachFormValue(form, function (value, key) { values[key] = value; });
    return values;
  }

  function showNodeEditorError(message) {
    if (!state.nodeEditor) { return; }
    state.nodeEditor.error = message;
    renderNodeEditor();
    var error = state.root.querySelector("#tlabNodeFormError");
    if (error) { error.scrollIntoView({ behavior: "smooth", block: "nearest" }); }
  }

  function submitNodeVersion() {
    var editor = state.nodeEditor;
    var record = state.nodePopoverRecord;
    var values;
    var payload;
    var endpoint;
    if (!editor || !record || editor.saving) { return; }
    if (state.nodeFilesProcessing) { showNodeEditorError("Esperá a que Telar termine de preparar la fotografía."); return; }
    values = nodeFormValues();
    if (!values.cod_local) { showNodeEditorError("Seleccioná el local."); return; }
    if (editor.mode === "take" && values.condicion_recepcion !== "conforme" && values.condicion_recepcion !== "con_observaciones") { showNodeEditorError("Indicá cómo recibís el trabajo."); return; }
    if (editor.mode === "take" && values.condicion_recepcion === "con_observaciones" && toStringSafe(values.observacion_recepcion).trim().length < 5) { showNodeEditorError("Describí la observación de recepción con al menos cinco caracteres."); return; }
    if (editor.mode === "take" && !state.nodeFiles.length) { showNodeEditorError("Agregá al menos una fotografía nueva para recibir el trabajo."); return; }
    payload = {
      id_trabajo: pick(record.work, ["id_trabajo", "cod_trabajo_laboratorio", "id"], record.rowId),
      cod_trabajo_laboratorio: pick(record.work, ["cod_trabajo_laboratorio", "id_trabajo", "id"], record.rowId),
      version_esperada: pick(record.work, ["version", "version_registro"], ""),
      clave_idempotencia: editor.idempotencyKey,
      datos_trabajo: {
        colorimetro: values.colorimetro || "",
        cod_tecnico_usuario: values.cod_tecnico_usuario || "",
        fecha_retiro: values.fecha_retiro || "",
        fecha_entrega: values.fecha_entrega || "",
        cod_local: values.cod_local,
        observacion: values.datos_observacion || ""
      }
    };
    if (canManageWorkCost()) {
      payload.datos_trabajo.costo_estimado = values.costo_estimado || "";
    }
    if (editor.mode === "take") {
      payload.condicion_recepcion = values.condicion_recepcion;
      payload.observacion = values.condicion_recepcion === "con_observaciones" ? (values.observacion_recepcion || "") : "";
      payload.sin_foto = "0";
      endpoint = "tomarHilo";
    } else {
      payload.observacion = "";
      endpoint = "actualizarDatosTrabajo";
    }
    editor.saving = true;
    editor.error = "";
    renderNodeEditor();
    request(endpoint, payload, state.nodeFiles).then(function (response) {
      var message = response.message || (editor.mode === "take" ? "Ahora sos responsable del trabajo." : "La versión activa quedó actualizada.");
      delete state.nodeDetailCache[toStringSafe(record.rowId)];
      closeNodePopover(false, true);
      notify(message, "success");
      loadSummary().then(null, function () {});
      loadWorks(false);
    }).then(null, function (error) {
      if (!state.nodeEditor) { return; }
      state.nodeEditor.saving = false;
      showNodeEditorError(error.message + (error.code && /VERSION|CUSTODIA/i.test(error.code) ? " Actualizá la vista y revisá el nodo vigente." : ""));
    });
  }

  function openNodeRelatedAction(code) {
    var record = state.nodePopoverRecord;
    var work = record && record.work ? record.work : null;
    var action = work ? normalizeActions(work.acciones_permitidas || []).filter(function (item) { return item.code === code; })[0] : null;
    if (!work || !action) { notify("La acción ya no está disponible. Actualizá el trabajo.", "error"); return; }
    closeNodePopover(false, true);
    openAction(code, action, work);
  }

  function openTakeNodePopover(trigger) {
    var rowId = trigger.getAttribute("data-tlab-node-row-id");
    var popover = state.root.querySelector("#tlabNodePopover");
    if (state.nodeEditor && state.nodeEditor.saving) {
      notify("La versión se está guardando. Esperá la confirmación del servidor.", "info");
      return;
    }
    closeNodePopover(false, true);
    state.nodePopover = trigger;
    state.nodePopoverPinned = true;
    trigger.setAttribute("aria-expanded", "true");
    popover.classList.add("is-editor");
    popover.innerHTML = loaderHtml("Recuperando la versión vigente...", "compact");
    popover.hidden = false;
    positionNodePopover(trigger, popover);
    Promise.all([loadNodeEnvelope(rowId), loadCatalogs(false)]).then(function (responses) {
      var loaded = responses[0];
      var action = normalizeActions(loaded.work.acciones_permitidas || loaded.envelope.acciones_permitidas || []).filter(function (item) { return item.code === "tomarHilo"; })[0];
      var chain = normalizeWork(loaded.work).custodyChain;
      var currentNode = chain.filter(function (node) { return boolValue(pick(node, ["actual", "es_actual", "custodia_actual"], false)); })[0]
        || (chain.length ? chain[chain.length - 1] : {});
      var snapshot;
      if (state.nodePopover !== trigger) { return; }
      if (!action) { throw new Error("Otra persona ya tomó el hilo o la acción dejó de estar disponible."); }
      snapshot = nodeSnapshot(null, loaded.work, true);
      state.nodePopoverRecord = { rowId: rowId, raw: currentNode, event: custodyEvent(currentNode), envelope: loaded.envelope, work: loaded.work, take: true };
      state.nodeEditor = { mode: "take", values: snapshot, idempotencyKey: makeIdempotencyKey(), saving: false, error: "" };
      revokeNodeObjectUrls();
      state.nodeFiles = [];
      state.nodeFilesProcessing = false;
      renderNodeEditor();
      focusFirst(popover);
    }).then(null, function (error) {
      if (state.nodePopover !== trigger) { return; }
      popover.classList.remove("is-editor");
      popover.innerHTML = '<div class="tlab-node-load-error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo abrir el nodo</strong><span>' + escapeHtml(error.message) + '</span><button type="button" data-tlab-command="close-node-popover">Cerrar</button></div>';
      positionNodePopover(trigger, popover);
    });
  }

  function openNodePopover(trigger, pinned) {
    var kind;
    var rowId;
    var lane;
    var index;
    var route;
    var routeRecord;
    var raw;
    var event;
    var popover;
    var historicalOrigin;
    var historicalId;
    if (!trigger || !state.root) { return; }
    if (state.nodePopover === trigger && state.nodePopoverPinned && !pinned) { return; }
    kind = trigger.getAttribute("data-tlab-node-kind");
    rowId = trigger.getAttribute("data-tlab-node-row-id");
    lane = trigger.getAttribute("data-tlab-node-lane") || "operativo";
    index = numberValue(trigger.getAttribute("data-tlab-node-index"), -1);
    route = listedRouteFor(kind, rowId, lane);
    routeRecord = index >= 0 ? route[index] : null;
    raw = routeRecord;
    if (!raw) { return; }
    raw = rawUnifiedNode(raw, lane);
    historicalOrigin = kind === "historico"
      || (lane === "unificado" && routeRecord && routeRecord.lane === "historico")
      || toStringSafe(pick(raw, ["origen"], "")) === "historico";
    historicalId = historicalOrigin
      ? pick(raw, ["id_historico"], kind === "historico" ? rowId : "")
      : "";
    event = lane === "custodia" || (lane === "unificado" && routeRecord && routeRecord.lane === "custodia")
      ? custodyEvent(raw) : routeEvent(raw);
    popover = state.root.querySelector("#tlabNodePopover");
    clearPopoverCloseTimer();
    Array.prototype.forEach.call(state.root.querySelectorAll("[data-tlab-node-trigger]"), function (button) { button.setAttribute("aria-expanded", button === trigger ? "true" : "false"); });
    popover.classList.remove("is-editor");
    popover.innerHTML = loaderHtml("Recuperando el detalle del nodo...", "compact");
    popover.hidden = false;
    state.nodePopover = trigger;
    state.nodePopoverPinned = !!pinned;
    state.nodePopoverRecord = {
      kind: kind,
      rowId: rowId,
      lane: lane,
      index: index,
      raw: raw,
      event: event,
      envelope: null,
      work: null,
      historicalId: historicalId
    };
    positionNodePopover(trigger, popover);
    if (historicalOrigin) {
      loadHistoricalNodeEnvelope(historicalId || rowId).then(function (loaded) {
        if (state.nodePopover !== trigger || !state.nodePopoverRecord) { return; }
        state.nodePopoverRecord.envelope = loaded.envelope;
        state.nodePopoverRecord.historical = loaded.historical;
        popover.innerHTML = historicalVersionPopoverHtml(state.nodePopoverRecord);
        positionNodePopover(trigger, popover);
      }).then(null, function (error) {
        if (state.nodePopover !== trigger) { return; }
        popover.innerHTML = '<div class="tlab-node-load-error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo consultar el nodo</strong><span>' + escapeHtml(error.message) + '</span><button type="button" data-tlab-command="close-node-popover">Cerrar</button></div>';
        positionNodePopover(trigger, popover);
      });
    } else if (lane === "cierre") {
      loadNodeEnvelope(rowId).then(function (loaded) {
        var closed;
        if (state.nodePopover !== trigger || !state.nodePopoverRecord) { return; }
        closed = closureNode(normalizeWork(loaded.work));
        state.nodePopoverRecord.envelope = loaded.envelope;
        state.nodePopoverRecord.work = loaded.work;
        state.nodePopoverRecord.raw = closed;
        state.nodePopoverRecord.event = routeEvent(closed);
        popover.innerHTML = closurePopoverHtml(state.nodePopoverRecord);
        positionNodePopover(trigger, popover);
      }).then(null, function (error) {
        if (state.nodePopover !== trigger) { return; }
        popover.innerHTML = '<div class="tlab-node-load-error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo consultar el cierre</strong><span>' + escapeHtml(error.message) + '</span><button type="button" data-tlab-command="close-node-popover">Cerrar</button></div>';
        positionNodePopover(trigger, popover);
      });
    } else {
      loadNodeEnvelope(rowId).then(function (loaded) {
        if (state.nodePopover !== trigger || !state.nodePopoverRecord) { return; }
        state.nodePopoverRecord.envelope = loaded.envelope;
        state.nodePopoverRecord.work = loaded.work;
        popover.innerHTML = nodeVersionPopoverHtml(state.nodePopoverRecord);
        positionNodePopover(trigger, popover);
      }).then(null, function (error) {
        if (state.nodePopover !== trigger) { return; }
        popover.innerHTML = '<div class="tlab-node-load-error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo consultar el nodo</strong><span>' + escapeHtml(error.message) + '</span><button type="button" data-tlab-command="close-node-popover">Cerrar</button></div>';
        positionNodePopover(trigger, popover);
      });
    }
  }

  function toggleNodePopover(trigger, focusPopover) {
    var popover;
    if ((state.nodeEditor && state.nodeEditor.saving)
        || (state.historicalResolver && state.historicalResolver.saving)) {
      notify("La versión se está guardando. Esperá la confirmación del servidor.", "info");
      return;
    }
    if (state.nodePopover === trigger && state.nodePopoverPinned) {
      closeNodePopover(true);
      return;
    }
    openNodePopover(trigger, true);
    if (focusPopover && state.root) {
      popover = state.root.querySelector("#tlabNodePopover");
      focusFirst(popover);
    }
  }

  function closeNodePopover(returnFocus, force) {
    var trigger = state.nodePopover;
    var popover;
    if (state.nodeEditor && state.nodeEditor.saving && force !== true) {
      notify("La versión se está guardando. Esperá la confirmación del servidor.", "info");
      return false;
    }
    if (state.historicalResolver && state.historicalResolver.saving && force !== true) {
      notify("La resolución histórica se está guardando. Esperá la confirmación del servidor.", "info");
      return false;
    }
    clearPopoverCloseTimer();
    if (!state.root) { return; }
    popover = state.root.querySelector("#tlabNodePopover");
    if (popover) {
      popover.hidden = true;
      popover.innerHTML = "";
      popover.classList.remove("is-editor", "is-historical-resolver");
    }
    if (trigger && typeof trigger.setAttribute === "function") { trigger.setAttribute("aria-expanded", "false"); }
    state.nodePopover = null;
    state.nodePopoverPinned = false;
    state.nodePopoverRecord = null;
    state.nodeEditor = null;
    state.historicalResolver = null;
    state.nodeFiles = [];
    state.nodeFilesProcessing = false;
    if (state.camera && state.camera.target === "node") { closeCamera(false); }
    revokeNodeObjectUrls();
    if (returnFocus && trigger && typeof trigger.focus === "function" && document.documentElement.contains(trigger)) { trigger.focus(); }
    return true;
  }

  function localDateValue(date) {
    var value = date || new Date();
    var year = value.getFullYear();
    var month = String(value.getMonth() + 1);
    var day = String(value.getDate());
    return year + "-" + (month.length < 2 ? "0" + month : month)
      + "-" + (day.length < 2 ? "0" + day : day);
  }

  function futureDateValue(days) {
    var value = new Date();
    value.setDate(value.getDate() + days);
    return localDateValue(value);
  }

  function historicalCandidateIsFinalized(candidate) {
    var status;
    if (!candidate) { return false; }
    if (candidate.finalizado !== undefined && candidate.finalizado !== null) {
      return boolValue(candidate.finalizado);
    }
    if (candidate.puede_continuar !== undefined && candidate.puede_continuar !== null) {
      return !boolValue(candidate.puede_continuar);
    }
    if (numberValue(candidate.progreso_porcentaje, 0) >= 100) { return true; }
    status = (toStringSafe(candidate.estado_detalle) + " "
      + toStringSafe(candidate.estado_tratamiento)).toLowerCase();
    return ["eliminado", "inactivo", "anulado", "cancelado", "completado",
      "finalizado", "terminado", "realizado"].some(function (word) {
        return status.indexOf(word) >= 0;
      });
  }

  function historicalResolutionCandidates(envelope) {
    return asArray(envelope.candidatos_detalle
      || pick(envelope.opciones_convalidacion || {}, ["detalles_venta", "candidatos_detalle"], [])).map(function (item) {
        var finalized = historicalCandidateIsFinalized(item);
        return Object.assign({}, item, {
          finalizado: finalized,
          puede_continuar: !finalized,
          nombre: toStringSafe(item.producto || item.nombre_producto || "Tratamiento")
            + " · Detalle " + toStringSafe(item.cod_detalle_venta || item.id)
            + " · " + numberValue(item.progreso_porcentaje, 0) + "%"
            + (finalized ? " · Finalizado" : "")
            + (boolValue(item.ocupado) ? " · Ya tiene trabajo activo" : "")
        });
      });
  }

  function historicalSelectedCandidate(candidates, detailId) {
    return asArray(candidates).filter(function (item) {
      return toStringSafe(item.cod_detalle_venta || item.id) === toStringSafe(detailId);
    })[0] || null;
  }

  function historicalResolverInitialValues(historical, envelope) {
    var candidates = historicalResolutionCandidates(envelope);
    var selected = candidates.filter(function (item) {
      return boolValue(item.seleccionado)
        || toStringSafe(item.cod_detalle_venta) === toStringSafe(historical.cod_detalle_venta);
    })[0];
    var stateCode = toStringSafe(historical.estado_declarado);
    var continuationStates = {
      pendiente_entrega_mecanico: true,
      en_laboratorio: true,
      pendiente_revision: true,
      ajuste_solicitado: true,
      listo_instalacion: true
    };
    if (!continuationStates[stateCode]) { stateCode = "pendiente_revision"; }
    if (!selected && candidates.length === 1) { selected = candidates[0]; }
    return {
      modo_resolucion: historicalCandidateIsFinalized(selected)
        ? "instalado_entregado" : "continuar",
      cod_detalle_venta: selected ? selected.cod_detalle_venta : (historical.cod_detalle_venta || ""),
      estado_continuacion: stateCode,
      cod_tipo_trabajo: historical.cod_tipo_trabajo || "",
      cod_mecanico_dental: historical.cod_mecanico_dental || historical.cod_mecanico_snapshot || "",
      cod_especialista: historical.cod_especialista || "",
      cod_local: historical.cod_local || historical.cod_local_snapshot || "",
      colorimetro: historical.colorimetro_original || "",
      fecha_retiro_declarada: dateInputValue(historical.fecha_retiro_declarada || historical.fecha_retiro_original),
      fecha_entrega_declarada: dateInputValue(historical.fecha_entrega_declarada || historical.fecha_entrega_original),
      fecha_objetivo: dateInputValue(historical.fecha_objetivo) || futureDateValue(30),
      costo_estimado: historical.costo_original === null || typeof historical.costo_original === "undefined"
        ? "" : historical.costo_original,
      observacion_trabajo: historical.observacion_original || "",
      condicion_pre_entrega: "conforme",
      observacion_entrega: "",
      sin_foto_historica: "0",
      justificacion: ""
    };
  }

  function captureHistoricalResolverValues() {
    var resolver = state.historicalResolver;
    var form = state.root && state.root.querySelector("#tlabHistoricalResolverForm");
    var values = {};
    var noHistoricalPhoto;
    if (!resolver || !form) { return; }
    forEachFormValue(form, function (value, key) { values[key] = value; });
    noHistoricalPhoto = form.querySelector('[name="sin_foto_historica"]');
    values.sin_foto_historica = noHistoricalPhoto && noHistoricalPhoto.checked ? "1" : "0";
    resolver.values = Object.assign({}, resolver.values, values);
  }

  function historicalResolverHtml() {
    var resolver = state.historicalResolver;
    var historical = resolver.historical;
    var envelope = resolver.envelope;
    var values = resolver.values;
    var normalized = normalizeHistorical(historical);
    var modesInstalled = values.modo_resolucion === "instalado_entregado";
    var noHistoricalPhoto = boolValue(values.sin_foto_historica);
    var selectedCandidate;
    var continuationBlocked;
    var costInput = canManageWorkCost()
      ? '<label class="tlab-node-form__field"><span>Costo</span><input name="costo_estimado" type="number" min="0" step="1" value="' + escapeAttr(values.costo_estimado) + '"></label>'
      : "";
    var types = includeCurrentCatalogOption(
      catalogItems(["tipos_trabajo"]),
      values.cod_tipo_trabajo,
      ["id", "codigo", "cod"],
      historical.tipo_trabajo
    );
    var doctors = includeCurrentCatalogOption(
      catalogItems(["doctores", "especialistas"]),
      values.cod_especialista,
      ["cod_usuario", "id", "codigo", "cod"],
      historical.doctor
    );
    var candidates = historicalResolutionCandidates(envelope);
    selectedCandidate = historicalSelectedCandidate(candidates, values.cod_detalle_venta);
    continuationBlocked = historicalCandidateIsFinalized(selectedCandidate);
    if (continuationBlocked && !modesInstalled) {
      values.modo_resolucion = "instalado_entregado";
      modesInstalled = true;
    }
    var mechanics = asArray(envelope.mecanicos
      || pick(envelope.opciones_convalidacion || {}, ["mecanicos"], []));
    var branches = asArray(envelope.locales
      || pick(envelope.opciones_convalidacion || {}, ["locales"], []));
    var states = asArray(envelope.estados_declarables
      || pick(envelope.opciones_convalidacion || {}, ["estados_declarables"], [])).filter(function (item) {
        return !boolValue(item.final) && ["pendiente_entrega_mecanico", "en_laboratorio",
          "pendiente_revision", "ajuste_solicitado", "listo_instalacion"].indexOf(toStringSafe(item.codigo)) >= 0;
      });
    var currentUser = personFromRecord(
      state.context,
      state.context,
      ["nombre", "nombre_usuario", "usuario_nombre"],
      ["rol"],
      ["avatar", "avatar_usuario", "usuario_avatar"],
      "Usuario autenticado"
    );
    var photoSection = modesInstalled
      ? '<section class="tlab-node-new-media tlab-historical-final-media" aria-busy="' + (state.nodeFilesProcessing ? "true" : "false") + '"><div><strong>Evidencia del cierre histórico</strong><small>Adjuntá una fotografía si todavía está disponible. Telar ajusta el peso automáticamente.</small></div>'
        + (noHistoricalPhoto
          ? '<p class="tlab-historical-no-photo__notice"><i class="fa-solid fa-camera-slash" aria-hidden="true"></i><span>El último nodo indicará <strong>Sin fotografía histórica disponible</strong>.</span></p>'
          : nodeMediaPickerHtml())
        + '<div class="tlab-historical-no-photo"><label><input type="checkbox" name="sin_foto_historica" value="1"' + (noHistoricalPhoto ? " checked" : "") + '><span><strong>No se dispone de fotografía histórica</strong><small>Usar únicamente cuando la imagen ya no existe. El motivo de regularización quedará como respaldo.</small></span></label></div></section>'
      : "";
    var modeFields = modesInstalled
      ? '<fieldset class="tlab-node-condition-field"><legend>Situación antes de entregar *</legend><label><input type="radio" name="condicion_pre_entrega" value="conforme"' + (values.condicion_pre_entrega === "conforme" ? " checked" : "") + '><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Conforme</span></label><label><input type="radio" name="condicion_pre_entrega" value="con_observaciones"' + (values.condicion_pre_entrega === "con_observaciones" ? " checked" : "") + '><span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>Con observaciones</span></label></fieldset>'
        + (values.condicion_pre_entrega === "con_observaciones"
          ? '<label class="tlab-node-form__field tlab-node-form__field--wide"><span>Detalle de la situación *</span><textarea name="observacion_entrega" maxlength="1000" rows="2" required placeholder="Describí la condición observada antes de la entrega">' + escapeHtml(values.observacion_entrega || "") + '</textarea></label>'
          : '<input type="hidden" name="observacion_entrega" value="">')
      : '<div class="tlab-node-form__grid">'
        + nodeSelectHtml("Etapa en la que continuará", "estado_continuacion", states, values.estado_continuacion, ["codigo", "id", "valor"], true)
        + '<label class="tlab-node-form__field"><span>Fecha objetivo *</span><input name="fecha_objetivo" type="date" required value="' + escapeAttr(values.fecha_objetivo || "") + '"></label></div>';
    return '<header class="tlab-node-popover__header"><div><small>Trabajo histórico a resolver</small><h3>Revisar antes de asumir</h3></div><span class="tlab-node-status">' + escapeHtml(modesInstalled ? "Instalado y entregado" : "Continuar trabajo") + '</span><button type="button" data-tlab-command="close-node-popover" aria-label="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<form id="tlabHistoricalResolverForm" class="tlab-node-form tlab-historical-resolver-form" novalidate>'
      + '<div class="tlab-node-readonly"><span><small>Paciente</small><strong>' + escapeHtml(normalized.patient) + '</strong></span><span><small>Venta original</small><strong>Venta ' + escapeHtml(normalized.sale) + '</strong></span><span><small>Trabajo recibido</small><strong>' + escapeHtml(historical.tipo_trabajo || normalized.product) + '</strong></span></div>'
      + '<fieldset class="tlab-historical-resolution-modes"><legend>¿Qué ocurrió con este trabajo?</legend>'
      + '<label class="' + (continuationBlocked ? "is-disabled" : "") + '"><input type="radio" name="modo_resolucion" value="continuar"' + (!modesInstalled ? " checked" : "") + (continuationBlocked ? " disabled" : "") + '><span><i class="fa-solid fa-play" aria-hidden="true"></i><strong>Continuar trabajo</strong><small>' + escapeHtml(continuationBlocked ? "No disponible: el tratamiento seleccionado ya está finalizado." : "Deja de ser histórico y pasa al flujo operativo bajo tu responsabilidad.") + '</small></span></label>'
      + '<label><input type="radio" name="modo_resolucion" value="instalado_entregado"' + (modesInstalled ? " checked" : "") + '><span><i class="fa-solid fa-tooth" aria-hidden="true"></i><strong>Instalado y entregado</strong><small>Cierra el hilo de laboratorio y completa el tratamiento al 100%.</small></span></label></fieldset>'
      + (continuationBlocked ? '<p class="tlab-historical-finalized-notice"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><strong>Tratamiento finalizado</strong>El sistema seleccionó Instalado y entregado para evitar enviarlo nuevamente como trabajo en curso.</span></p>' : '')
      + '<div class="tlab-form-error" id="tlabHistoricalResolverError"' + (resolver.error ? "" : " hidden") + '>' + escapeHtml(resolver.error || "") + '</div>'
      + '<p class="tlab-historical-responsibility"><i class="fa-solid fa-user-check" aria-hidden="true"></i><span><strong>' + escapeHtml(currentUser.name) + '</strong> quedará como responsable del nodo que se genere.</span></p>'
      + nodeSelectHtml("Tratamiento exacto de la venta", "cod_detalle_venta", candidates, values.cod_detalle_venta, ["cod_detalle_venta", "id"], true)
      + modeFields + photoSection
      + '<div class="tlab-node-form__grid">'
      + nodeSelectHtml("Tipo de trabajo", "cod_tipo_trabajo", types, values.cod_tipo_trabajo, ["id", "codigo", "cod"], true)
      + '<label class="tlab-node-form__field"><span>Colorimetría</span><input name="colorimetro" type="text" maxlength="30" value="' + escapeAttr(values.colorimetro || "") + '" placeholder="Ej.: B2"></label>'
      + nodeSelectHtml("Doctor", "cod_especialista", doctors, values.cod_especialista, ["cod_usuario", "id", "codigo", "cod"], false)
      + nodeSelectHtml("Mecánico dental", "cod_mecanico_dental", mechanics, values.cod_mecanico_dental, ["cod_mecanico_dental", "id", "codigo", "cod"], false)
      + '<label class="tlab-node-form__field"><span>Fecha de retiro</span><input name="fecha_retiro_declarada" type="date" value="' + escapeAttr(values.fecha_retiro_declarada || "") + '"></label>'
      + '<label class="tlab-node-form__field"><span>Fecha de entrega</span><input name="fecha_entrega_declarada" type="date" value="' + escapeAttr(values.fecha_entrega_declarada || "") + '"></label>'
      + costInput
      + nodeSelectHtml("Local", "cod_local", branches, values.cod_local, ["cod_local", "id", "codigo", "cod"], true) + '</div>'
      + '<label class="tlab-node-form__field tlab-node-form__field--wide"><span>Observación del trabajo</span><textarea name="observacion_trabajo" maxlength="1000" rows="3">' + escapeHtml(values.observacion_trabajo || "") + '</textarea></label>'
      + '<label class="tlab-node-form__field tlab-node-form__field--wide"><span>Motivo de regularización *</span><textarea name="justificacion" maxlength="750" rows="2" required placeholder="Explicá brevemente por qué se continúa o se cierra este registro">' + escapeHtml(values.justificacion || "") + '</textarea></label>'
      + (modesInstalled ? '<p class="tlab-historical-close-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>Este cierre no genera una evolución clínica. Conserva la declaración y ' + (noHistoricalPhoto ? 'la ausencia explícita de fotografía' : 'la fotografía disponible') + ' como última versión del hilo de laboratorio.</p>' : '')
      + '<footer class="tlab-node-form__footer"><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="close-node-popover" ' + (resolver.saving ? "disabled" : "") + '>Cancelar</button><button type="submit" class="tlab-button tlab-button--primary" ' + (resolver.saving || state.nodeFilesProcessing ? "disabled" : "") + '><i class="fa-solid ' + (resolver.saving || state.nodeFilesProcessing ? "fa-hourglass-half" : (modesInstalled ? "fa-flag-checkered" : "fa-hand-holding")) + '" aria-hidden="true"></i>' + (resolver.saving ? "Guardando..." : (state.nodeFilesProcessing ? "Preparando foto..." : (modesInstalled ? "Confirmar instalación y entrega" : "Continuar y asumir responsabilidad"))) + '</button></footer></form>';
  }

  function renderHistoricalResolver() {
    var popover = state.root && state.root.querySelector("#tlabNodePopover");
    if (!popover || !state.historicalResolver || !state.nodePopover) { return; }
    popover.classList.add("is-editor", "is-historical-resolver");
    popover.innerHTML = historicalResolverHtml();
    positionNodePopover(state.nodePopover, popover);
  }

  function showHistoricalResolverError(message) {
    var error;
    if (!state.historicalResolver) { return; }
    state.historicalResolver.error = message;
    renderHistoricalResolver();
    error = state.root.querySelector("#tlabHistoricalResolverError");
    if (error) { error.scrollIntoView({ behavior: "smooth", block: "nearest" }); }
  }

  function openHistoricalResolver(trigger) {
    var rowId = trigger.getAttribute("data-tlab-historical-row-id");
    var popover = state.root.querySelector("#tlabNodePopover");
    if (!boolValue(state.context.puede_resolver_historicos)
        && state.context.puede_resolver_historicos !== undefined) {
      notify("Tu sesión ya no puede resolver trabajos históricos.", "error");
      return;
    }
    if (state.historicalResolver && state.historicalResolver.saving) {
      notify("La resolución histórica se está guardando. Esperá la confirmación del servidor.", "info");
      return;
    }
    closeNodePopover(false, true);
    state.nodePopover = trigger;
    state.nodePopoverPinned = true;
    trigger.setAttribute("aria-expanded", "true");
    popover.classList.add("is-editor", "is-historical-resolver");
    popover.innerHTML = loaderHtml("Recuperando el trabajo histórico...", "compact");
    popover.hidden = false;
    positionNodePopover(trigger, popover);
    Promise.all([loadHistoricalNodeEnvelope(rowId), loadCatalogs(false)]).then(function (responses) {
      var loaded = responses[0];
      if (state.nodePopover !== trigger) { return; }
      if (!boolValue(loaded.envelope.puede_resolver)
          && !boolValue(pick(loaded.historical.acciones || {}, ["puede_resolver"], false))) {
        throw new Error("Este trabajo ya fue resuelto o dejó de estar disponible.");
      }
      state.nodePopoverRecord = {
        kind: "historico",
        rowId: rowId,
        envelope: loaded.envelope,
        historical: loaded.historical
      };
      state.historicalResolver = {
        rowId: rowId,
        envelope: loaded.envelope,
        historical: loaded.historical,
        values: historicalResolverInitialValues(loaded.historical, loaded.envelope),
        idempotencyKey: makeIdempotencyKey(),
        saving: false,
        error: ""
      };
      revokeNodeObjectUrls();
      state.nodeFiles = [];
      state.nodeFilesProcessing = false;
      renderHistoricalResolver();
      focusFirst(popover);
    }).then(null, function (error) {
      if (state.nodePopover !== trigger) { return; }
      popover.classList.remove("is-editor", "is-historical-resolver");
      popover.innerHTML = '<div class="tlab-node-load-error"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo abrir el trabajo histórico</strong><span>' + escapeHtml(error.message) + '</span><button type="button" data-tlab-command="close-node-popover">Cerrar</button></div>';
      positionNodePopover(trigger, popover);
    });
  }

  function groupForHistoricalResolution(mode, stateCode) {
    if (mode === "instalado_entregado") { return "finalizados"; }
    if (stateCode === "en_laboratorio") { return "en_laboratorio"; }
    if (stateCode === "pendiente_revision"
        || stateCode === "ajuste_solicitado"
        || stateCode === "listo_instalacion") {
      return "pendientes_revision";
    }
    return "pendientes_entrega";
  }

  function submitHistoricalResolver() {
    var resolver = state.historicalResolver;
    var form;
    var checkedMode;
    var candidates;
    var selectedCandidate;
    var values;
    var payload;
    var installed;
    var noHistoricalPhoto;
    if (!resolver || resolver.saving) { return; }
    if (state.nodeFilesProcessing) { showHistoricalResolverError("Esperá a que Telar termine de preparar la fotografía."); return; }
    captureHistoricalResolverValues();
    form = state.root && state.root.querySelector("#tlabHistoricalResolverForm");
    checkedMode = form && form.querySelector('input[name="modo_resolucion"]:checked');
    if (checkedMode) {
      resolver.values.modo_resolucion = checkedMode.value;
    }
    values = resolver.values;
    candidates = historicalResolutionCandidates(resolver.envelope);
    selectedCandidate = historicalSelectedCandidate(candidates, values.cod_detalle_venta);
    if (values.modo_resolucion === "continuar"
        && historicalCandidateIsFinalized(selectedCandidate)) {
      resolver.values.modo_resolucion = "instalado_entregado";
      showHistoricalResolverError("El tratamiento ya está finalizado. Se seleccionó Instalado y entregado; revisá la condición y la evidencia disponible.");
      return;
    }
    installed = values.modo_resolucion === "instalado_entregado";
    noHistoricalPhoto = installed && boolValue(values.sin_foto_historica);
    if (values.modo_resolucion !== "continuar" && !installed) {
      showHistoricalResolverError("Seleccioná si el trabajo debe continuar o ya fue instalado y entregado.");
      return;
    }
    if (!values.cod_detalle_venta) {
      showHistoricalResolverError("Seleccioná el tratamiento exacto de la venta original.");
      return;
    }
    if (!values.cod_tipo_trabajo || !values.cod_local) {
      showHistoricalResolverError("Seleccioná el tipo de trabajo y el local.");
      return;
    }
    if (!installed && !values.estado_continuacion) {
      showHistoricalResolverError("Seleccioná la etapa en la que continuará el trabajo.");
      return;
    }
    if (toStringSafe(values.justificacion).trim().length < 5) {
      showHistoricalResolverError("Escribí un motivo de regularización de al menos cinco caracteres.");
      return;
    }
    if (installed && values.condicion_pre_entrega !== "conforme"
        && values.condicion_pre_entrega !== "con_observaciones") {
      showHistoricalResolverError("Indicá la situación del trabajo antes de entregarlo.");
      return;
    }
    if (installed && values.condicion_pre_entrega === "con_observaciones"
        && toStringSafe(values.observacion_entrega).trim().length < 3) {
      showHistoricalResolverError("Describí la situación observada antes de la entrega.");
      return;
    }
    if (installed && !state.nodeFiles.length && !noHistoricalPhoto) {
      showHistoricalResolverError("Adjuntá una fotografía o marcá que no se dispone de fotografía histórica.");
      return;
    }
    payload = {
      id_historico: resolver.rowId,
      cod_trabajo_mecanico_dental: resolver.rowId,
      version_esperada: resolver.historical.version,
      clave_idempotencia: resolver.idempotencyKey,
      modo_resolucion: values.modo_resolucion,
      cod_detalle_venta: values.cod_detalle_venta,
      estado_continuacion: installed ? "" : values.estado_continuacion,
      cod_tipo_trabajo: values.cod_tipo_trabajo,
      cod_mecanico_dental: values.cod_mecanico_dental || "",
      cod_especialista: values.cod_especialista || "",
      cod_local: values.cod_local,
      colorimetro: values.colorimetro || "",
      fecha_retiro_declarada: values.fecha_retiro_declarada || "",
      fecha_entrega_declarada: values.fecha_entrega_declarada || "",
      fecha_objetivo: installed ? "" : (values.fecha_objetivo || ""),
      observacion_trabajo: values.observacion_trabajo || "",
      condicion_pre_entrega: installed ? values.condicion_pre_entrega : "",
      observacion_entrega: installed ? (values.observacion_entrega || "") : "",
      sin_foto_historica: noHistoricalPhoto ? "1" : "0",
      justificacion: toStringSafe(values.justificacion).trim()
    };
    if (canManageWorkCost()) {
      payload.costo_estimado = values.costo_estimado || "";
    }
    resolver.saving = true;
    resolver.error = "";
    renderHistoricalResolver();
    request("resolverHistorico", payload, installed && !noHistoricalPhoto ? state.nodeFiles : []).then(function (response) {
      var message = response.message || (installed
        ? "El trabajo quedó instalado y entregado."
        : "El trabajo dejó de ser histórico y ahora está bajo tu responsabilidad.");
      delete state.nodeDetailCache["historico:" + toStringSafe(resolver.rowId)];
      closeNodePopover(false, true);
      notify(message, "success");
      state.view = "operativa";
      state.group = groupForHistoricalResolution(values.modo_resolucion, values.estado_continuacion);
      state.filtersOpen = false;
      state.moduleOptions.cod_venta_historica = "";
      state.moduleOptions.cod_detalle_operativo = "";
      renderGroupNavigation();
      loadSummary().then(null, function () {});
      loadWorks(false);
    }).then(null, function (error) {
      if (!state.historicalResolver) { return; }
      state.historicalResolver.saving = false;
      if (error.code === "tratamiento_ya_finalizado") {
        state.historicalResolver.values.modo_resolucion = "instalado_entregado";
        showHistoricalResolverError("El tratamiento ya está finalizado. Se seleccionó Instalado y entregado; revisá la condición y la evidencia disponible.");
        return;
      }
      showHistoricalResolverError(error.message + (error.code && /VERSION|OCUPADO|INTEGRADO/i.test(error.code)
        ? " Actualizá la vista y revisá la situación vigente." : ""));
    });
  }

  function historicalResolutionNodeHtml(historical) {
    return '<li class="tlab-route-node tlab-thread-end is-action tlab-historical-resolution-node">'
      + '<button type="button" data-tlab-resolve-historical data-tlab-historical-row-id="'
      + escapeAttr(historical.id)
      + '" aria-haspopup="dialog" aria-expanded="false" aria-controls="tlabNodePopover"'
      + ' aria-label="Resolver la situación del trabajo histórico">'
      + '<span class="tlab-thread-end__icon"><i class="fa-solid fa-hand-holding-medical"'
      + ' aria-hidden="true"></i><i class="fa-solid fa-minus tlab-thread-end__thread"'
      + ' aria-hidden="true"></i></span><strong>Resolver situación</strong>'
      + '<small>Continuar o cerrar</small></button></li>';
  }

  function historicalLaneHtml(historical, rowIndex) {
    var route = asArray(historical.route);
    return '<section class="tlab-unified-lane tlab-unified-lane--historical"'
      + ' aria-label="Hilo histórico pendiente"><header class="tlab-unified-lane__heading">'
      + '<span><i class="fa-solid fa-diagram-project" aria-hidden="true"></i>'
      + '<strong>Hilo del trabajo</strong><small>Origen y resolución pendiente</small></span>'
      + '</header><div class="tlab-route-scroll tlab-route-scroll--unified" tabindex="0"'
      + ' aria-label="Hilo histórico; desplazamiento horizontal"><ol class="tlab-route-list">'
      + route.map(function (event, index) {
        return routeNodeHtml(
          event,
          index,
          route.length + 1,
          "historico",
          historical.id,
          rowIndex
        );
      }).join("")
      + historicalResolutionNodeHtml(historical) + '</ol></div></section>';
  }

  function historicalCardHtml(item, rowIndex) {
    var historical = normalizeHistorical(item);
    return '<article class="tlab-thread-record tlab-thread-record--historical"><div class="tlab-thread-row tlab-thread-row--unified">'
      + '<div class="tlab-thread-identity tlab-thread-identity--historical tlab-thread-identity--summary">'
      + '<span class="tlab-thread-identity__code"><span>Venta ' + escapeHtml(historical.sale) + '</span><b aria-hidden="true">·</b><strong>Trabajo ' + escapeHtml(historical.code) + '</strong><em class="tlab-status tlab-status--' + historicalStatusClass(historical.declaredState) + '">' + escapeHtml(humanizeHistoricalValue(historical.declaredState)) + '</em></span>'
      + '<span class="tlab-thread-identity__patient">' + escapeHtml(historical.patient) + '</span><span class="tlab-thread-identity__product">' + escapeHtml(historical.product) + '</span><span class="tlab-thread-identity__branch"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>' + escapeHtml(historical.branch) + '</span>'
      + identityPeopleHtml(historical.doctor, historical.mechanic)
      + '<span class="tlab-thread-identity__open tlab-thread-identity__open--status"><span>Situación pendiente</span></span></div>'
      + '<div class="tlab-thread-route tlab-thread-route--unified" aria-label="Recorrido histórico recibido">'
      + historicalLaneHtml(historical, rowIndex) + '</div></div></article>';
  }

  function renderHistoricals(total) {
    var results = state.root.querySelector("#tlabResults");
    var count = state.root.querySelector("#tlabResultCount");
    var more = state.root.querySelector("#tlabLoadMore");
    closeNodePopover();
    count.textContent = total + (total === 1 ? " registro histórico" : " registros históricos");
    if (!state.historicals.length) {
      results.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-box-archive" aria-hidden="true"></i><strong>No hay históricos para estos filtros</strong><span>Probá otra búsqueda o limpiá los filtros históricos.</span></div></div>';
    } else {
      results.innerHTML = '<div class="tlab-thread-list tlab-thread-list--historical">' + state.historicals.map(historicalCardHtml).join("") + '</div>';
    }
    more.hidden = !state.hasMore;
  }

  function renderHistoricalListError(message) {
    var results = state.root.querySelector("#tlabResults");
    results.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-lock" aria-hidden="true"></i><strong>No se pudo cargar el archivo histórico</strong><span>' + escapeHtml(message) + '</span><br><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="refresh">Reintentar</button></div></div>';
    state.root.querySelector("#tlabLoadMore").hidden = true;
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

  function normalizeCurrentCustody(item, chain) {
    var raw = item.custodia_actual || item.responsable_actual || {};
    var nodes = asArray(chain);
    var currentNode;
    var primary;
    var responsible;
    var duration;
    if (!raw || typeof raw !== "object") { raw = {}; }
    currentNode = nodes.filter(function (node) {
      return boolValue(pick(node, ["es_actual", "actual", "custodia_actual"], false));
    })[0] || (nodes.length ? nodes[nodes.length - 1] : {});
    primary = raw.persona || raw.responsable || raw.custodio || raw.usuario
      || (pick(raw, ["nombre", "nombre_completo", "name"], "") ? raw : null)
      || currentNode.responsable || currentNode.custodio || currentNode.actor || currentNode.usuario;
    responsible = personFromRecord(
      primary,
      Object.assign({}, currentNode, item, raw),
      ["responsable_nombre", "custodio_nombre", "nombre_custodio_actual", "nombre_custodio", "actor_nombre"],
      ["responsable_rol", "custodio_rol", "rol_custodio_actual", "actor_rol"],
      ["responsable_avatar", "custodio_avatar", "custodio_avatar_url", "actor_avatar"],
      "Responsable actual"
    );
    duration = pick(raw, ["duracion_texto", "tiempo_texto", "tiempo_custodia_texto"], pick(currentNode, ["duracion_texto", "tiempo_custodia_texto"], ""));
    if (!duration && pick(raw, ["duracion_segundos", "segundos_custodia"], pick(currentNode, ["duracion_segundos", "segundos_custodia"], "")) !== "") {
      duration = formatDurationSeconds(pick(raw, ["duracion_segundos", "segundos_custodia"], pick(currentNode, ["duracion_segundos", "segundos_custodia"], 0)));
    }
    return {
      person: responsible,
      startedAt: pick(raw, ["fecha_inicio", "inicio", "desde"], pick(currentNode, ["fecha_inicio", "inicio", "fecha_servidor", "fecha_hora"], "")),
      duration: duration,
      local: pick(raw, ["local_nombre", "sucursal_nombre", "local"], pick(currentNode, ["local_nombre", "sucursal_nombre", "local"], ""))
    };
  }

  function normalizeWork(item) {
    item = item || {};
    var nestedDetail = item.detalle && typeof item.detalle === "object" ? item.detalle : {};
    var workState = pick(item, ["estado_derivado", "situacion"], "");
    var deadline = pick(item, ["semaforo", "plazo", "cumplimiento_plazo"], "");
    var deadlineText;
    var deadlineClass;
    var actions = normalizeActions(item.acciones_permitidas || item.acciones || []);
    var custodyChain = receivedCustodyChain(item);
    var currentCustody = normalizeCurrentCustody(item, custodyChain);
    var terminalState = toStringSafe(pick(item, ["estado_derivado", "situacion", "estado_actual", "estado"], "")).toLowerCase();
    var cancelled = boolValue(pick(item, ["cancelado"], false)) || /cancelad/.test(terminalState);
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
      originCode: pick(item, ["codigo_origen"], ""),
      originUnit: numberValue(pick(item, ["unidad_origen"], 1), 1),
      originTotal: numberValue(pick(item, ["cantidad_unidades_origen"], 1), 1),
      sale: pick(item, ["numero_venta", "nro_venta", "venta"], pick(nestedDetail, ["numero_venta", "nro_venta", "venta"], "")),
      patient: pick(item, ["paciente_nombre", "nombre_paciente", "paciente"], pick(nestedDetail, ["paciente_nombre", "nombre_paciente", "paciente"], "Paciente autorizado")),
      product: pick(item, ["producto_nombre", "tipo_trabajo", "producto", "tratamiento"], pick(nestedDetail, ["producto_nombre", "nombre_producto", "producto", "tratamiento"], "Trabajo de laboratorio")),
      branch: pick(item, ["local_nombre", "sucursal_nombre", "local", "sucursal"], pick(nestedDetail, ["local_nombre", "nombre_local", "sucursal_nombre", "local", "sucursal"], "Sin sucursal")),
      mechanic: personFromRecord(
        item.mecanico || item.mecanico_dental || item.tecnico
          || (workState === "pendiente_tecnico" ? { nombre: "Técnico pendiente", rol: "Asignación pendiente" } : null),
        item,
        ["nombre_mecanico", "mecanico_nombre", "tecnico_nombre", "nombre_tecnico"],
        ["tecnico_rol", "mecanico_rol", "rol_tecnico"],
        ["tecnico_avatar", "mecanico_avatar", "tecnico_avatar_url", "avatar_mecanico"],
        "Mecánico dental"
      ),
      doctor: personFromRecord(
        item.doctor || item.especialista,
        item,
        ["nombre_doctor", "doctor_nombre", "odontologo_nombre", "nombre_odontologo"],
        ["doctor_rol", "odontologo_rol", "rol_doctor"],
        ["doctor_avatar", "doctor_avatar_url", "odontologo_avatar", "avatar_doctor"],
        "Odontólogo"
      ),
      custodian: currentCustody.person,
      currentCustody: currentCustody,
      currentDays: numberValue(pick(item, ["dias_custodio_actual", "dias_con_custodio", "dias_responsable"], 0), 0),
      totalDays: numberValue(pick(item, ["dias_totales", "dias_total"], 0), 0),
      adjustments: numberValue(pick(item, ["cantidad_ajustes", "ajustes", "ciclo_actual"], 0), 0),
      targetDate: pick(item, ["fecha_objetivo", "fecha_limite"], ""),
      image: pick(item, ["miniatura_url", "imagen_principal", "evidencia_principal", "foto"], ""),
      imageId: pick(item, ["miniatura_media_id", "id_media_principal", "id_media"], ""),
      situation: pick(item, ["situacion_texto", "situacion", "estado_derivado"], "En seguimiento"),
      deadlineText: deadlineText,
      deadlineClass: deadlineClass,
      pendingTransfer: boolValue(pick(item, ["transferencia_pendiente", "tiene_transferencia_pendiente"], false)),
      currentCycle: pick(item, ["ciclo_etiqueta", "ciclo", "tipo_ciclo"], "Original"),
      route: receivedRoute(item),
      custodyChain: custodyChain,
      historicalOrigin: item.registro_historico_original && typeof item.registro_historico_original === "object"
        ? item.registro_historico_original : null,
      terminal: boolValue(pick(item, ["es_terminal", "terminal", "finalizado", "cancelado"], false))
        || /finaliz|instalad|cancelad/.test(terminalState),
      cancelled: cancelled,
      actions: actions,
      version: pick(item, ["version", "version_registro"], "")
    };
  }

  function imageBlock(url, alt) {
    if (!url) { return '<i class="fa-solid fa-tooth" aria-hidden="true"></i><span class="sr-only">Sin fotografía</span>'; }
    return '<img src="' + escapeAttr(url) + '" alt="' + escapeAttr(alt || "Evidencia del trabajo") + '" loading="lazy">';
  }

  function workActionByCode(work, code) {
    return work.actions.filter(function (action) { return action.code === code; })[0] || null;
  }

  function rowActionButtonHtml(action, work, modifier, label) {
    var visibleLabel;
    if (!action) { return ""; }
    visibleLabel = label || action.label;
    return '<button type="button" class="tlab-custody-action ' + escapeAttr(modifier || "") + '" data-tlab-action="' + escapeAttr(action.code) + '" data-tlab-row-work-id="' + escapeAttr(work.id) + '" aria-label="' + escapeAttr(visibleLabel) + '" title="' + escapeAttr(visibleLabel) + '"><i class="fa-solid ' + escapeAttr(action.icon || "fa-arrow-right") + '" aria-hidden="true"></i>' + escapeHtml(visibleLabel) + '</button>';
  }

  function operationalLaneHtml(work, rowIndex) {
    return '<section class="tlab-route-lane tlab-route-lane--operational" aria-label="Recorrido operativo">'
      + '<header class="tlab-route-lane__heading"><span><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>Recorrido operativo</strong><small>Etapas y decisiones del trabajo</small></span></header>'
      + routeHtml(work.route, "operativo", work.id, rowIndex) + '</section>';
  }

  function custodyLaneHtml(work, rowIndex) {
    var take = workActionByCode(work, "tomarHilo");
    var novelty = workActionByCode(work, "registrarNovedad");
    var correction = workActionByCode(work, "rectificarCustodia");
    var current = work.currentCustody || {};
    var currentPerson = person(current.person, "Responsable actual");
    var currentLabel = work.terminal ? "Último custodio" : "Responsable actual";
    var actions = rowActionButtonHtml(novelty, work, "is-secondary") + rowActionButtonHtml(correction, work, "is-audit");
    var end;
    if (work.terminal) {
      end = work.cancelled
        ? '<div class="tlab-custody-next is-cancelled" aria-label="Custodia cerrada por cancelación"><span class="tlab-custody-next__icon"><i class="fa-solid fa-ban" aria-hidden="true"></i></span><span><strong>Trabajo cancelado</strong><small>Custodia cerrada sin entrega final</small></span></div>'
        : '<div class="tlab-custody-next is-finished" aria-label="Custodia finalizada"><span class="tlab-custody-next__icon"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i></span><span><strong>Hilo cerrado</strong><small>Resultado final registrado</small></span></div>';
    } else if (take) {
      end = '<div class="tlab-custody-next is-action"><span class="tlab-custody-next__icon"><i class="fa-solid fa-link" aria-hidden="true"></i></span><span><strong>Tomar el hilo</strong><small>Iniciá tu período de custodia</small>' + rowActionButtonHtml(take, work, "is-primary", "Tomar el hilo") + '</span></div>';
    } else {
      end = '<div class="tlab-custody-next"><span class="tlab-custody-next__icon"><i class="fa-solid fa-flag" aria-hidden="true"></i></span><span><strong>Próximo relevo</strong><small>Se habilita al responsable autorizado</small></span></div>';
    }
    return '<section class="tlab-route-lane tlab-route-lane--custody" aria-label="El hilo de custodia">'
      + '<header class="tlab-route-lane__heading"><span><i class="fa-solid fa-link" aria-hidden="true"></i><strong>El hilo</strong><small>Custodios internos con cuenta Telar</small></span>'
      + '<span class="tlab-route-lane__current" title="' + escapeAttr(currentLabel) + ': ' + escapeAttr(currentPerson.name) + '">' + avatarHtml(currentPerson) + '<span><small>' + escapeHtml(currentLabel) + '</small><strong>' + escapeHtml(currentPerson.name) + '</strong></span></span>'
      + (actions ? '<span class="tlab-route-lane__actions">' + actions + '</span>' : '') + '</header>'
      + '<div class="tlab-custody-lane__body">' + custodyRouteHtml(work.custodyChain, "operativo", work.id, rowIndex) + end + '</div></section>';
  }

  function workCardHtml(item, rowIndex) {
    var work = normalizeWork(item);
    var take = workActionByCode(work, "tomarHilo");
    var finish = workActionByCode(work, "registrarInstalacion");
    var next = work.actions.filter(function (action) {
      return action.code !== "registrarNovedad" && action.code !== "rectificarCustodia"
        && action.code !== "agregarEvidencia" && action.code !== "agregarNota"
        && action.code !== "registrarInstalacion" && action.code !== "cancelarTrabajo";
    })[0] || null;
    var nextText = work.terminal ? "Proceso finalizado"
      : (take ? "Próxima: Tomar el hilo"
        : (finish ? "Próxima: Abrí el último nodo para finalizar"
          : (next ? "Próxima: " + next.label : "El avance continúa desde los nodos")));
    return '<article class="tlab-thread-record"><div class="tlab-thread-row tlab-thread-row--unified">'
      + '<div class="tlab-thread-identity tlab-thread-identity--summary" aria-label="Datos generales del trabajo ' + escapeAttr(work.code) + '">'
      + '<span class="tlab-thread-identity__code"><span>Venta ' + escapeHtml(work.sale || "-") + '</span><b aria-hidden="true">·</b><strong>Trabajo ' + escapeHtml(work.code) + '</strong><em class="tlab-status tlab-status--' + work.deadlineClass + '">' + escapeHtml(work.deadlineText) + '</em>'
      + (work.originTotal > 1 ? '<small class="tlab-origin-badge">Origen ' + escapeHtml(work.originCode) + ' · Trabajo ' + work.originUnit + ' de ' + work.originTotal + '</small>' : '') + '</span>'
      + '<span class="tlab-thread-identity__patient">' + escapeHtml(work.patient) + '</span><span class="tlab-thread-identity__product">' + escapeHtml(work.product) + '</span><span class="tlab-thread-identity__branch"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>' + escapeHtml(work.branch) + '</span>'
      + identityPeopleHtml(work.doctor, work.mechanic)
      + '<span class="tlab-thread-identity__next"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>' + escapeHtml(nextText) + '</span></div>'
      + '<div class="tlab-thread-route tlab-thread-route--unified">' + unifiedLaneHtml(work, rowIndex) + '</div></div></article>';
  }

  function renderWorks(total) {
    var results = state.root.querySelector("#tlabResults");
    var count = state.root.querySelector("#tlabResultCount");
    var more = state.root.querySelector("#tlabLoadMore");
    closeNodePopover();
    count.textContent = total + (total === 1 ? " trabajo" : " trabajos");
    if (!state.works.length) {
      results.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>No hay trabajos en esta bandeja</strong><span>Probá otro grupo o ajustá los filtros de búsqueda.</span></div></div>';
    } else {
      results.innerHTML = '<div class="tlab-thread-list">' + state.works.map(workCardHtml).join("") + '</div>';
      hydrateAuthorizedThumbnails(results);
    }
    more.hidden = !state.hasMore;
  }

  function renderListError(message) {
    var results = state.root.querySelector("#tlabResults");
    results.innerHTML = '<div class="tlab-empty"><div><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>No se pudo cargar la bandeja</strong><span>' + escapeHtml(message) + '</span><br><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="refresh">Reintentar</button></div></div>';
    state.root.querySelector("#tlabLoadMore").hidden = true;
  }

  function inlineDetailHost(kind, id) {
    var hosts;
    var found = null;
    if (!state.root) { return null; }
    hosts = state.root.querySelectorAll("[data-tlab-inline-kind][data-tlab-inline-id]");
    Array.prototype.some.call(hosts, function (host) {
      if (host.getAttribute("data-tlab-inline-kind") === kind && toStringSafe(host.getAttribute("data-tlab-inline-id")) === toStringSafe(id)) {
        found = host;
        return true;
      }
      return false;
    });
    return found;
  }

  function detailBodyElement() {
    var host = inlineDetailHost(state.detailKind, state.detailId);
    return host ? host.querySelector(".tlab-row-detail__body") : null;
  }

  function removeStandaloneDetailHosts() {
    if (!state.root) { return; }
    Array.prototype.forEach.call(state.root.querySelectorAll(".tlab-thread-record--standalone"), function (item) {
      if (item.parentNode) { item.parentNode.removeChild(item); }
    });
  }

  function createStandaloneDetailHost(kind, id) {
    var results;
    var host;
    if (!state.root) { return null; }
    results = state.root.querySelector("#tlabResults");
    if (!results) { return null; }
    removeStandaloneDetailHosts();
    results.insertAdjacentHTML("afterbegin", '<article class="tlab-thread-record tlab-thread-record--standalone ' + (kind === "historico" ? 'tlab-thread-record--historical' : '') + '">' + inlineDetailHtml(kind, id, 0) + '</article>');
    host = inlineDetailHost(kind, id);
    return host;
  }

  function setInlineDetailPresentation(kind, id, loadingLabel, scrollToDetail) {
    var host = inlineDetailHost(kind, id) || createStandaloneDetailHost(kind, id);
    var body;
    if (!state.root || !host) { return null; }
    Array.prototype.forEach.call(state.root.querySelectorAll(".tlab-row-detail"), function (item) { item.hidden = item !== host; });
    Array.prototype.forEach.call(state.root.querySelectorAll("[data-tlab-work-id], [data-tlab-historical-id]"), function (button) {
      var matches = (kind === "operativo" && toStringSafe(button.getAttribute("data-tlab-work-id")) === toStringSafe(id))
        || (kind === "historico" && toStringSafe(button.getAttribute("data-tlab-historical-id")) === toStringSafe(id));
      button.setAttribute("aria-expanded", matches ? "true" : "false");
      var label = button.querySelector(".tlab-thread-identity__open");
      var labelText = button.querySelector("[data-tlab-inline-label]");
      if (labelText) { labelText.textContent = matches ? "Ocultar ficha" : "Ver ficha"; }
      if (label && matches) {
        label.classList.add("is-open");
      } else if (label) {
        label.classList.remove("is-open");
      }
    });
    host.hidden = false;
    body = host.querySelector(".tlab-row-detail__body");
    if (body && loadingLabel) { body.innerHTML = '<div class="tlab-detail__loader">' + loaderHtml(loadingLabel, "content") + '</div>'; }
    if (scrollToDetail) {
      window.setTimeout(function () { host.scrollIntoView({ behavior: "smooth", block: "nearest" }); }, 30);
    }
    return body;
  }

  function inlineDetailHeaderHtml(eyebrow, title) {
    return '<header class="tlab-row-detail__header"><div><small>' + escapeHtml(eyebrow) + '</small><h3>' + escapeHtml(title) + '</h3></div><button type="button" class="tlab-icon-button tlab-icon-button--light" data-tlab-command="close-detail" aria-label="Ocultar ficha general"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button></header>';
  }

  function restoreInlineDetail() {
    var body;
    if (!state.detailId || !state.detailKind) { return; }
    body = setInlineDetailPresentation(state.detailKind, state.detailId, state.detail || state.historicalDetail ? "" : "Cargando ficha general...", false);
    if (!body) { return; }
    if (state.detailError) {
      body.innerHTML = inlineDetailHeaderHtml(state.detailKind === "historico" ? "Ficha general histórica" : "Ficha general", "Trabajo " + state.detailId) + '<div class="tlab-empty"><div><i class="fa-solid fa-lock" aria-hidden="true"></i><strong>No se pudo abrir la ficha</strong><span>' + escapeHtml(state.detailError) + '</span></div></div>';
      return;
    }
    if (state.detailKind === "historico" && state.historicalDetail) { renderHistoricalDetailContent(); }
    else if (state.detailKind === "operativo" && state.detail) { renderDetailContent(); }
  }

  function openDetail(id, preserveFocus) {
    var body;
    var requestId;
    var currentHost;
    if (!id || !state.root) { return; }
    currentHost = inlineDetailHost("operativo", id);
    if (!preserveFocus && isInlineDetailOpen("operativo", id) && currentHost && !currentHost.hidden) {
      closeDetail();
      return;
    }
    if (!preserveFocus) { state.detailReturnFocus = document.activeElement; }
    closeNodePopover();
    state.detailId = id;
    state.detailKind = "operativo";
    state.detailError = "";
    state.detail = null;
    state.detailEnvelope = null;
    state.historicalDetail = null;
    state.historicalEnvelope = null;
    state.historicalWizard = null;
    state.detailTab = "timeline";
    requestId = ++state.detailRequest;
    body = setInlineDetailPresentation("operativo", id, "Reconstruyendo la trazabilidad...", !preserveFocus);
    return request("obtenerTrabajo", { id_trabajo: id, cod_trabajo_laboratorio: id }).then(function (response) {
      var envelope = response.data || {};
      var detail = envelope.trabajo || envelope.item || envelope;
      if (requestId !== state.detailRequest || state.detailKind !== "operativo" || toStringSafe(state.detailId) !== toStringSafe(id)) { return; }
      if (!detail.acciones_permitidas && envelope.acciones_permitidas) { detail.acciones_permitidas = envelope.acciones_permitidas; }
      ["recorrido_operativo", "cadena_custodia", "hilo_custodia", "custodia_actual", "novedades"].forEach(function (key) {
        if ((detail[key] === undefined || detail[key] === null) && envelope[key] !== undefined) { detail[key] = envelope[key]; }
      });
      if (!detail.version && response.version) { detail.version = response.version; }
      state.detail = detail;
      state.detailEnvelope = envelope;
      mergeContext(envelope);
      renderDetailContent();
      return response;
    }).then(null, function (error) {
      if (requestId !== state.detailRequest || state.detailKind !== "operativo" || toStringSafe(state.detailId) !== toStringSafe(id)) { return null; }
      state.detailError = error.message;
      body = detailBodyElement();
      if (body) { body.innerHTML = inlineDetailHeaderHtml("Ficha general", "Trabajo " + id) + '<div class="tlab-empty"><div><i class="fa-solid fa-lock" aria-hidden="true"></i><strong>No se pudo abrir el trabajo</strong><span>' + escapeHtml(error.message) + '</span></div></div>'; }
      return null;
    });
  }

  function closeDetail(preserveFocus) {
    var returnFocus = state.detailReturnFocus;
    if (!state.root) { return; }
    if (state.historicalWizard && state.historicalWizard.saving) {
      notify("La actualización histórica se está confirmando. Esperá a que termine.", "info");
      return false;
    }
    ++state.detailRequest;
    Array.prototype.forEach.call(state.root.querySelectorAll(".tlab-row-detail"), function (item) { item.hidden = true; });
    removeStandaloneDetailHosts();
    Array.prototype.forEach.call(state.root.querySelectorAll("[data-tlab-work-id], [data-tlab-historical-id]"), function (button) {
      var labelText = button.querySelector("[data-tlab-inline-label]");
      button.setAttribute("aria-expanded", "false");
      if (labelText) { labelText.textContent = "Ver ficha"; }
    });
    state.detailId = "";
    state.detailKind = "";
    state.detailError = "";
    state.detail = null;
    state.detailEnvelope = null;
    state.historicalDetail = null;
    state.historicalEnvelope = null;
    state.historicalWizard = null;
    state.detailReturnFocus = null;
    if (!preserveFocus && returnFocus && typeof returnFocus.focus === "function" && document.documentElement.contains(returnFocus)) {
      returnFocus.focus();
    }
    return true;
  }

  function openHistoricalDetail(id, preserveFocus) {
    var body;
    var requestId;
    var currentHost;
    if (!id || !state.root) { return; }
    if (!boolValue(state.context.es_auditor)) {
      notify("Esta consulta requiere el permiso de auditoría de trabajos de laboratorio.", "error");
      return;
    }
    currentHost = inlineDetailHost("historico", id);
    if (!preserveFocus && isInlineDetailOpen("historico", id) && currentHost && !currentHost.hidden) {
      closeDetail();
      return;
    }
    if (!preserveFocus) { state.detailReturnFocus = document.activeElement; }
    closeNodePopover();
    state.detailId = id;
    state.detailKind = "historico";
    state.detailError = "";
    state.detail = null;
    state.detailEnvelope = null;
    state.historicalDetail = null;
    state.historicalEnvelope = null;
    state.historicalWizard = null;
    requestId = ++state.detailRequest;
    body = setInlineDetailPresentation("historico", id, "Recuperando el registro y su autoría...", !preserveFocus);
    return request("obtenerHistorico", { id_historico: id, cod_trabajo_mecanico_dental: id }).then(function (response) {
      var envelope = response.data || {};
      var detail = envelope.historico || envelope.item || envelope;
      if (requestId !== state.detailRequest || state.detailKind !== "historico" || toStringSafe(state.detailId) !== toStringSafe(id)) { return; }
      if (!detail.version && response.version) { detail.version = response.version; }
      state.historicalDetail = detail;
      state.historicalEnvelope = envelope;
      mergeContext(envelope);
      if (!boolValue(state.context.es_auditor)) { throw new Error("La sesión ya no tiene permiso para consultar históricos."); }
      renderHistoricalDetailContent();
      return response;
    }).then(null, function (error) {
      if (requestId !== state.detailRequest || state.detailKind !== "historico" || toStringSafe(state.detailId) !== toStringSafe(id)) { return null; }
      state.detailError = error.message;
      body = detailBodyElement();
      if (body) { body.innerHTML = inlineDetailHeaderHtml("Ficha general histórica", "Trabajo " + id) + '<div class="tlab-empty"><div><i class="fa-solid fa-lock" aria-hidden="true"></i><strong>No se pudo abrir el registro histórico</strong><span>' + escapeHtml(error.message) + '</span></div></div>'; }
      return null;
    });
  }

  function historicalArray(names) {
    var envelope = state.historicalEnvelope || {};
    var detail = state.historicalDetail || {};
    return asArray(pick(envelope, names, pick(detail, names, [])));
  }

  function historicalActionAllowed(actionCode) {
    var envelope = state.historicalEnvelope || {};
    var detail = state.historicalDetail || {};
    var options = envelope.opciones_convalidacion || detail.opciones_convalidacion || {};
    var directNames = actionCode === "convalidarHistorico"
      ? ["puede_convalidar", "permite_convalidar"]
      : (actionCode === "rectificarHistorico"
        ? ["puede_rectificar", "permite_rectificar"]
        : ["puede_promover", "permite_promover"]);
    var permissionMap = detail.acciones || envelope.acciones || options.acciones || {};
    var direct = pick(envelope, directNames, pick(detail, directNames, pick(permissionMap, directNames, pick(options, directNames, ""))));
    var actions = envelope.acciones_permitidas || detail.acciones_permitidas || options.acciones_permitidas || [];
    var target = actionCode.toLowerCase().replace(/[_-]/g, "");
    if (!boolValue(state.context.es_auditor)) { return false; }
    if (boolValue(direct)) { return true; }
    if (actions && !Array.isArray(actions) && typeof actions === "object") {
      if (boolValue(actions[actionCode])) { return true; }
      actions = Object.keys(actions).filter(function (key) { return boolValue(actions[key]) || (actions[key] && typeof actions[key] === "object"); });
    }
    return asArray(actions).some(function (entry) {
      var code = typeof entry === "object" ? pick(entry, ["codigo", "accion", "code", "endpoint"], "") : entry;
      return toStringSafe(code).toLowerCase().replace(/[_-]/g, "") === target;
    });
  }

  function historicalEventHtml(item) {
    var actor = item.actor || item.usuario || item.realizado_por || pick(item, ["nombre_usuario"], "Usuario registrado");
    var title = pick(item, ["titulo", "accion_texto", "evento_texto", "accion", "tipo_evento"], "Actualización histórica");
    var date = pick(item, ["fecha_servidor", "fecha_hora", "fecha", "creado_en", "server_timestamp"], "");
    var note = pick(item, ["justificacion", "observacion", "detalle", "nota"], "");
    return '<li><span class="tlab-history-events__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span><span><strong>' + escapeHtml(humanizeHistoricalValue(title)) + '</strong><small>' + escapeHtml(person(actor).name) + ' · ' + escapeHtml(formatDate(date, true)) + '</small>' + (note ? '<p>' + escapeHtml(note) + '</p>' : '') + '</span></li>';
  }

  function renderHistoricalDetailContent() {
    var detail;
    var historical;
    var body;
    var actions = [];
    var events;
    var author;
    var editor;
    if (!state.historicalDetail || !state.root || state.detailKind !== "historico") { return; }
    if (state.historicalWizard) { renderHistoricalWizard(); return; }
    detail = state.historicalDetail;
    historical = normalizeHistorical(detail);
    body = detailBodyElement();
    if (!body) { return; }
    events = historicalArray(["eventos", "auditoria", "historial"]);
    author = person(historical.author, "Carga histórica");
    editor = person(historical.editor || "Sin edición posterior", "Última edición histórica");
    if (historicalActionAllowed("convalidarHistorico")) {
      actions.push('<button type="button" class="tlab-button tlab-button--primary" data-tlab-historical-action="convalidarHistorico"><i class="fa-solid fa-shield-circle-check" aria-hidden="true"></i>Convalidar situación</button>');
    }
    if (historicalActionAllowed("rectificarHistorico")) {
      actions.push('<button type="button" class="tlab-button tlab-button--secondary" data-tlab-historical-action="rectificarHistorico"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>Rectificar declaración</button>');
    }
    /* La promoción jamás se infiere por estado: sólo aparece cuando la
       autorización explícita llega desde obtenerHistorico. */
    if (historicalActionAllowed("promoverHistorico")) {
      actions.push('<button type="button" class="tlab-button tlab-button--teal" data-tlab-historical-action="promoverHistorico"><i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i>Promover al flujo operativo</button>');
    }
    body.innerHTML = inlineDetailHeaderHtml("Archivo histórico · origen preservado", "Ficha general · " + historical.code)
      + '<section class="tlab-historical-detail-hero">'
      + '<div><span class="tlab-historical-detail-hero__code">Código histórico #' + escapeHtml(historical.code) + '</span><h3>' + escapeHtml(historical.patient) + '</h3><p>' + escapeHtml(historical.product) + '</p></div>'
      + '<div class="tlab-historical-detail-hero__states"><span><small>Estado original</small><strong>' + escapeHtml(humanizeHistoricalValue(historical.originalState)) + '</strong></span><span class="tlab-status tlab-status--' + historicalStatusClass(historical.declaredState) + '">' + escapeHtml(humanizeHistoricalValue(historical.declaredState)) + '</span></div>'
      + '</section>'
      + '<section class="tlab-panel tlab-detail-section"><h3>Información sincronizada</h3><div class="tlab-data-grid">'
      + dataBox("Paciente", historical.patient) + dataBox("Venta", historical.sale) + dataBox("Sucursal histórica", historical.branch) + dataBox("Tipo de trabajo", historical.product)
      + dataBox("Fecha de retiro declarada", formatDate(pick(detail, ["fecha_retiro_declarada", "fecha_retiro_original", "fecha_retiro"], ""), false)) + dataBox("Fecha de entrega declarada", formatDate(pick(detail, ["fecha_entrega_declarada", "fecha_entrega_original", "fecha_entrega"], ""), false))
      + dataBox("Fecha objetivo", formatDate(pick(detail, ["fecha_objetivo", "fecha_comprometida"], ""), false)) + dataBox("Fecha de la situación", formatDate(pick(detail, ["fecha_situacion_declarada", "fecha_estado"], ""), false))
      + '</div><div class="tlab-historical-mechanic"><i class="fa-solid fa-flask-vial" aria-hidden="true"></i><span><small>Mecánico histórico declarado por Administración</small><strong>' + escapeHtml(person(historical.mechanic).name) + '</strong><em>Dato original sugerido: ' + escapeHtml(person(historical.mechanicSnapshot).name) + '. El código declarado pertenece al catálogo histórico y no equivale a una cuenta de usuario técnico.</em></span></div></section>'
      + '<section class="tlab-panel tlab-detail-section"><h3>Autoría preservada</h3><div class="tlab-historical-authorship"><div>' + personHtml(author, "Carga histórica") + '<span>' + escapeHtml(formatDate(historical.authorDate, true)) + '</span></div><div>' + personHtml(editor, "Última edición histórica") + '<span>' + escapeHtml(formatDate(historical.editorDate, true)) + '</span></div></div></section>'
      + '<section class="tlab-panel tlab-detail-section"><div class="tlab-section-heading"><div><h3>Datos pendientes</h3><p>La falta de un detalle exacto no altera la relación ya conservada con paciente y venta.</p></div><span class="tlab-status ' + (historical.pending.length ? 'tlab-status--warning' : 'tlab-status--ok') + '">' + historical.pending.length + '</span></div>'
      + (historical.pending.length ? '<ul class="tlab-historical-pending-list">' + historical.pending.map(function (entry) { return '<li><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>' + escapeHtml(entry) + '</span></li>'; }).join("") + '</ul>' : '<p class="tlab-historical-complete"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>No hay datos pendientes informados por el servidor.</p>') + '</section>'
      + '<section class="tlab-panel tlab-detail-section"><div class="tlab-section-heading"><div><h3>Acciones administrativas</h3><p>La información original permanece intacta y cada declaración queda auditada.</p></div></div><div class="tlab-actions">' + (actions.length ? actions.join("") : '<p class="tlab-actions-empty"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> El servidor no habilita acciones para este registro.</p>') + '</div></section>'
      + '<section class="tlab-panel tlab-detail-section"><h3>Historial de convalidación</h3>' + (events.length ? '<ol class="tlab-history-events">' + events.map(historicalEventHtml).join("") + '</ol>' : '<p class="tlab-actions-empty">Todavía no hay intervenciones administrativas registradas.</p>') + '</section>';
  }

  function historicalOptions(names) {
    var envelope = state.historicalEnvelope || {};
    var detail = state.historicalDetail || {};
    var options = envelope.opciones_convalidacion || detail.opciones_convalidacion || {};
    var aliases = names.slice(0);
    if (names.indexOf("detalles_venta") >= 0 || names.indexOf("tratamientos_venta") >= 0) {
      aliases.push("candidatos_detalle");
    }
    return asArray(pick(options, aliases, pick(envelope, aliases, pick(detail, aliases, []))));
  }

  function historicalOptionValue(item, kind) {
    var keys;
    if (typeof item !== "object") { return item; }
    keys = kind === "detail"
      ? ["cod_detalle_venta", "id_detalle_venta", "id", "codigo", "valor", "value"]
      : (kind === "mechanic"
        ? ["cod_mecanico_dental", "id_mecanico_dental", "codigo", "id", "valor", "value"]
        : (kind === "branch"
          ? ["cod_local", "id_local", "codigo", "id", "valor", "value"]
          : (kind === "custodian"
            ? ["cod_custodio_actual", "cod_usuario", "cod_persona", "id", "codigo", "valor", "value"]
            : ["estado_declarado", "codigo", "cod", "valor", "value", "id"])));
    return pick(item, keys, "");
  }

  function historicalOptionLabel(item, fallback) {
    var label;
    if (typeof item !== "object") { return humanizeHistoricalValue(item, fallback); }
    label = pick(item, ["etiqueta", "label", "nombre", "producto", "producto_nombre", "tipo_trabajo", "descripcion", "texto"], fallback || "Opción disponible");
    if (item.cod_detalle_venta) {
      label += " · Detalle #" + item.cod_detalle_venta;
      if (item.detalle_producto) { label += " · " + item.detalle_producto; }
      if (boolValue(item.ocupado)) { label += " · Ya vinculado"; }
      else if (boolValue(item.inactivo)) { label += " · Inactivo"; }
    }
    if (item.habilitado_custodia === false) {
      label += " · Sin permisos operativos";
    }
    return label;
  }

  function historicalSelectField(label, name, kind, items, selected, required, placeholder, selectedLabel) {
    var found = false;
    var options = items.map(function (item) {
      var value = historicalOptionValue(item, kind);
      var isSelected = toStringSafe(value) === toStringSafe(selected);
      var unavailable = item && typeof item === "object" && item.seleccionable === false;
      if (isSelected) { found = true; }
      return '<option value="' + escapeAttr(value) + '" ' + (isSelected ? "selected" : "")
        + (unavailable && !isSelected ? " disabled" : "") + '>'
        + escapeHtml(historicalOptionLabel(item, value)) + '</option>';
    });
    if (selected !== "" && selected !== null && typeof selected !== "undefined" && !found) {
      options.unshift('<option value="' + escapeAttr(selected) + '" selected>' + escapeHtml(selectedLabel || ("Valor histórico #" + selected)) + '</option>');
    }
    return '<div class="tlab-field"><label for="tlabHistorical_' + escapeAttr(name) + '">' + escapeHtml(label) + '</label><select id="tlabHistorical_' + escapeAttr(name) + '" name="' + escapeAttr(name) + '" ' + (required ? "required" : "") + '><option value="">' + escapeHtml(placeholder) + '</option>' + options.join("") + '</select></div>';
  }

  function historicalDateInputValue(value) {
    var match = toStringSafe(value).match(/^\d{4}-\d{2}-\d{2}/);
    return match ? match[0] : "";
  }

  function historicalPositiveId(value) {
    var id = numberValue(value, 0);
    return id > 0 ? id : "";
  }

  function openHistoricalWizard(actionCode) {
    var detail = state.historicalDetail || {};
    var historical;
    var mode;
    var declaredMechanicId;
    var suggestedMechanicId;
    if (!detail || state.detailKind !== "historico") { return; }
    if (!historicalActionAllowed(actionCode)) {
      notify("El servidor no habilita esta acción para el registro actual.", "error");
      return;
    }
    mode = actionCode === "rectificarHistorico" ? "rectificar" : (actionCode === "promoverHistorico" ? "promover" : "convalidar");
    historical = normalizeHistorical(detail);
    declaredMechanicId = historicalPositiveId(pick(detail, ["cod_mecanico_dental", "id_mecanico_dental"], 0));
    suggestedMechanicId = declaredMechanicId || historicalPositiveId(pick(detail, ["cod_mecanico_snapshot"], 0));
    state.historicalWizard = {
      mode: mode,
      endpoint: actionCode,
      step: 1,
      totalSteps: mode === "promover" ? 2 : 3,
      saving: false,
      idempotencyKey: makeIdempotencyKey(),
      values: {
        estado_declarado: pick(detail, ["estado_declarado", "situacion_declarada"], ""),
        cod_detalle_venta: historicalPositiveId(pick(detail, ["cod_detalle_venta", "id_detalle_venta"], 0)),
        cod_mecanico_dental: suggestedMechanicId,
        cod_local: historicalPositiveId(pick(detail, ["cod_local", "id_local", "cod_local_snapshot"], 0)),
        cod_custodio_actual: historicalPositiveId(pick(detail, ["cod_custodio_actual", "cod_custodio", "cod_usuario_custodio"], 0)),
        fecha_objetivo: historicalDateInputValue(pick(detail, ["fecha_objetivo", "fecha_comprometida"], "")),
        fecha_retiro_declarada: historicalDateInputValue(pick(detail, ["fecha_retiro_declarada", "fecha_retiro"], "")),
        fecha_entrega_declarada: historicalDateInputValue(pick(detail, ["fecha_entrega_declarada", "fecha_entrega"], "")),
        fecha_situacion_declarada: historicalDateInputValue(pick(detail, ["fecha_situacion_declarada", "fecha_estado", "fecha_edicion", "fecha_insercion"], "")),
        justificacion: ""
      },
      historical: historical
    };
    renderHistoricalWizard();
    focusFirst(detailBodyElement());
  }

  function historicalWizardStepsHtml(wizard) {
    var labels = wizard.mode === "promover"
      ? ["Revisión", "Confirmación"]
      : ["Situación", "Relaciones y fechas", "Confirmación"];
    return '<div class="tlab-steps ' + (labels.length === 2 ? 'tlab-steps--two' : '') + '" aria-label="Progreso">' + labels.map(function (label, index) {
      var step = index + 1;
      var cls = step < wizard.step ? "is-done" : (step === wizard.step ? "is-current" : "");
      return '<span class="tlab-step ' + cls + '" data-step="' + step + '">' + escapeHtml(label) + '</span>';
    }).join("") + '</div>';
  }

  function renderHistoricalSituationStep() {
    var wizard = state.historicalWizard;
    var values = wizard.values;
    var states = historicalOptions(["situaciones", "estados_declarables", "estados", "situaciones_estables"]);
    return '<div class="tlab-historical-wizard__intro"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><div><h3>Declarar una situación estable</h3><p>Elegí la situación comprobada actualmente. El estado original seguirá visible y no será sobrescrito.</p></div></div>'
      + '<div class="tlab-form-grid">'
      + historicalSelectField("Situación actual comprobada", "estado_declarado", "state", states, values.estado_declarado, true, "Seleccionar situación estable", humanizeHistoricalValue(values.estado_declarado))
      + '<div class="tlab-field"><label for="tlabHistoricalSituationDate">Fecha de esta situación</label><input id="tlabHistoricalSituationDate" name="fecha_situacion_declarada" type="date" required value="' + escapeAttr(values.fecha_situacion_declarada) + '"><small>No uses la fecha de hoy si conocés cuándo cambió realmente la situación.</small></div>'
      + '</div><div class="tlab-historical-original-state"><small>Estado preservado del módulo anterior</small><strong>' + escapeHtml(humanizeHistoricalValue(wizard.historical.originalState)) + '</strong></div>';
  }

  function renderHistoricalRelationsStep() {
    var wizard = state.historicalWizard;
    var values = wizard.values;
    var detail = state.historicalDetail || {};
    var details = historicalOptions(["detalles_venta", "tratamientos_venta", "detalles"]);
    var mechanics = historicalOptions(["mecanicos", "mecanicos_dentales", "mecanicos_historicos"]);
    var branches = historicalOptions(["locales", "sucursales"]);
    var custodians = historicalOptions(["custodios", "responsables"]);
    return '<div class="tlab-historical-wizard__intro"><i class="fa-solid fa-link" aria-hidden="true"></i><div><h3>Completar relaciones y fechas</h3><p>El detalle exacto es opcional. Paciente y venta ya permanecen vinculados por el registro histórico.</p></div></div>'
      + '<div class="tlab-form-grid">'
      + historicalSelectField("Detalle exacto de la venta (opcional)", "cod_detalle_venta", "detail", details, values.cod_detalle_venta, false, "Dejar pendiente", pick(detail, ["detalle_venta_nombre", "tratamiento_nombre"], "Detalle #" + values.cod_detalle_venta))
      + historicalSelectField("Mecánico histórico", "cod_mecanico_dental", "mechanic", mechanics, values.cod_mecanico_dental, true, "Seleccionar mecánico", person(wizard.historical.mechanicSnapshot).name)
      + historicalSelectField("Sucursal declarada", "cod_local", "branch", branches, values.cod_local, true, "Seleccionar sucursal", wizard.historical.branch)
      + historicalSelectField("Custodio actual (opcional)", "cod_custodio_actual", "custodian", custodians, values.cod_custodio_actual, false, "Sin custodio comprobado", pick(detail, ["custodio_actual_nombre", "nombre_custodio"], "Custodio #" + values.cod_custodio_actual))
      + '<div class="tlab-field"><label for="tlabHistoricalTargetDate">Fecha objetivo</label><input id="tlabHistoricalTargetDate" name="fecha_objetivo" type="date" value="' + escapeAttr(values.fecha_objetivo) + '"></div>'
      + '<div class="tlab-field"><label for="tlabHistoricalWithdrawalDate">Fecha de retiro declarada</label><input id="tlabHistoricalWithdrawalDate" name="fecha_retiro_declarada" type="date" value="' + escapeAttr(values.fecha_retiro_declarada) + '"></div>'
      + '<div class="tlab-field"><label for="tlabHistoricalDeliveryDate">Fecha de entrega declarada</label><input id="tlabHistoricalDeliveryDate" name="fecha_entrega_declarada" type="date" value="' + escapeAttr(values.fecha_entrega_declarada) + '"></div>'
      + '<div class="tlab-field tlab-field--wide"><label for="tlabHistoricalJustification">Justificación administrativa</label><textarea id="tlabHistoricalJustification" name="justificacion" required maxlength="750" placeholder="Explicá qué información fue comprobada y por qué corresponde esta declaración">' + escapeHtml(values.justificacion) + '</textarea><small>Quedará registrada junto al usuario, fecha, estado anterior y estado nuevo.</small></div>'
      + '</div><div class="tlab-historical-mechanic-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span><strong>Sugerencia del registro original: ' + escapeHtml(person(wizard.historical.mechanicSnapshot).name) + '.</strong> Confirmá o cambiá la selección. Sólo al guardar se declarará por Administración mediante <code>cod_mecanico_dental</code>; no se asigna ni se suplanta una cuenta técnica.</span></div>';
  }

  function historicalSelectedLabel(name, kind, names) {
    var value = state.historicalWizard.values[name];
    var found = historicalOptions(names).filter(function (item) {
      return toStringSafe(historicalOptionValue(item, kind)) === toStringSafe(value);
    })[0];
    return found ? historicalOptionLabel(found, value) : value;
  }

  function renderHistoricalConfirmationStep() {
    var wizard = state.historicalWizard;
    var values = wizard.values;
    var items = [
      "Registro histórico: #" + wizard.historical.code,
      "Situación declarada: " + (historicalSelectedLabel("estado_declarado", "state", ["situaciones", "estados_declarables", "estados", "situaciones_estables"]) || "Sin seleccionar"),
      "Fecha de situación: " + formatDate(values.fecha_situacion_declarada, false),
      "Mecánico histórico: " + (historicalSelectedLabel("cod_mecanico_dental", "mechanic", ["mecanicos", "mecanicos_dentales", "mecanicos_historicos"]) || "Sin seleccionar"),
      "Sucursal: " + (historicalSelectedLabel("cod_local", "branch", ["locales", "sucursales"]) || "Sin seleccionar"),
      "Detalle exacto: " + (historicalSelectedLabel("cod_detalle_venta", "detail", ["detalles_venta", "tratamientos_venta", "detalles"]) || "Pendiente")
    ];
    if (values.cod_custodio_actual) { items.push("Custodio: " + historicalSelectedLabel("cod_custodio_actual", "custodian", ["custodios", "responsables"])); }
    if (values.fecha_objetivo) { items.push("Fecha objetivo: " + formatDate(values.fecha_objetivo, false)); }
    if (values.fecha_retiro_declarada) { items.push("Retiro declarado: " + formatDate(values.fecha_retiro_declarada, false)); }
    if (values.fecha_entrega_declarada) { items.push("Entrega declarada: " + formatDate(values.fecha_entrega_declarada, false)); }
    return '<div class="tlab-confirm-box"><h3>Revisá la declaración antes de guardar</h3><ul class="tlab-confirm-list">' + items.map(function (item) { return '<li>' + escapeHtml(item) + '</li>'; }).join("") + '</ul><div class="tlab-historical-justification-preview"><small>Justificación</small><p>' + escapeHtml(values.justificacion) + '</p></div><label class="tlab-check"><input type="checkbox" id="tlabHistoricalConfirmed" required><span>Confirmo que estos datos fueron comprobados por Administración y que el registro histórico original debe conservarse sin cambios.</span></label></div>';
  }

  function renderHistoricalPromotionStep() {
    var wizard = state.historicalWizard;
    var historical = wizard.historical;
    var detail = state.historicalDetail || {};
    var detalleSeleccionado = pick(detail, ["producto", "nombre_producto"], "Tratamiento sin descripción")
      + " · Detalle #" + pick(detail, ["cod_detalle_venta"], "-");
    var resumen = '<div class="tlab-data-grid">'
      + dataBox("Registro", "#" + historical.code)
      + dataBox("Paciente", historical.patient)
      + dataBox("Venta", historical.sale)
      + dataBox("Tratamiento exacto", detalleSeleccionado)
      + dataBox("Sucursal", historical.branch)
      + dataBox("Mecánico declarado", person(historical.mechanic).name)
      + dataBox("Custodio actual", pick(detail, ["custodio_actual", "nombre_custodio"], "Sin registrar"))
      + dataBox("Fecha objetivo", formatDate(pick(detail, ["fecha_objetivo"], ""), false))
      + dataBox("Situación declarada", humanizeHistoricalValue(historical.declaredState))
      + '</div>';
    if (wizard.step === 1) {
      return '<div class="tlab-historical-wizard__intro tlab-historical-wizard__intro--promotion"><i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i><div><h3>Promover al flujo operativo</h3><p>El servidor confirmó que este histórico reúne los requisitos. La promoción reutiliza los datos ya convalidados y no modifica el registro de origen.</p></div></div>'
        + resumen
        + '<div class="tlab-field tlab-field--wide tlab-historical-promotion-reason"><label for="tlabHistoricalPromotionReason">Justificación de la promoción</label><textarea id="tlabHistoricalPromotionReason" name="justificacion" required maxlength="750" placeholder="Indicá por qué corresponde incorporar este registro al seguimiento operativo">' + escapeHtml(wizard.values.justificacion) + '</textarea><small>La promoción y su autor quedarán auditados.</small></div>';
    }
    return '<div class="tlab-confirm-box"><h3>Confirmación final</h3><p>Se creará o vinculará el trabajo operativo utilizando exclusivamente los datos previamente convalidados.</p>' + resumen + '<div class="tlab-historical-justification-preview"><small>Justificación</small><p>' + escapeHtml(wizard.values.justificacion) + '</p></div><label class="tlab-check"><input type="checkbox" id="tlabHistoricalConfirmed" required><span>Confirmo la promoción del histórico #' + escapeHtml(historical.code) + ' al flujo operativo.</span></label></div>';
  }

  function renderHistoricalWizard() {
    var wizard = state.historicalWizard;
    var body;
    var content;
    var title;
    var last;
    if (!wizard || !state.root) { return; }
    title = wizard.mode === "rectificar" ? "Rectificar declaración histórica" : (wizard.mode === "promover" ? "Promover registro histórico" : "Convalidar situación histórica");
    if (wizard.mode === "promover") {
      content = renderHistoricalPromotionStep();
    } else {
      content = wizard.step === 1 ? renderHistoricalSituationStep() : (wizard.step === 2 ? renderHistoricalRelationsStep() : renderHistoricalConfirmationStep());
    }
    last = wizard.step === wizard.totalSteps;
    body = detailBodyElement();
    if (!body) { return; }
    body.innerHTML = inlineDetailHeaderHtml("Archivo histórico · Acción guiada", title) + '<form id="tlabHistoricalWizardForm" class="tlab-historical-wizard">' + historicalWizardStepsHtml(wizard) + '<div class="tlab-historical-wizard__body">' + content + '<div class="tlab-form-error" id="tlabHistoricalWizardError" hidden></div></div>'
      + '<footer class="tlab-historical-wizard__footer"><button type="button" class="tlab-button tlab-button--ghost" data-tlab-command="historical-back" ' + (wizard.step === 1 ? "disabled" : "") + '><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Volver</button><div><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="historical-cancel">Cancelar</button>'
      + (last ? '<button type="submit" class="tlab-button ' + (wizard.mode === "promover" ? 'tlab-button--teal' : 'tlab-button--primary') + '" id="tlabHistoricalWizardSubmit"><i class="fa-solid ' + (wizard.mode === "promover" ? 'fa-arrow-up-right-dots' : 'fa-shield-circle-check') + '" aria-hidden="true"></i>' + (wizard.mode === "promover" ? 'Confirmar promoción' : 'Guardar declaración') + '</button>' : '<button type="button" class="tlab-button tlab-button--primary" data-tlab-command="historical-next">Continuar<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>') + '</div></footer></form>';
  }

  function captureHistoricalWizardValues() {
    var form;
    if (!state.historicalWizard || !state.root) { return; }
    form = state.root.querySelector("#tlabHistoricalWizardForm");
    if (!form) { return; }
    forEachFormValue(form, function (value, key) {
      state.historicalWizard.values[key] = value;
    });
  }

  function validateHistoricalWizardStep(step) {
    var wizard = state.historicalWizard;
    var values = wizard.values;
    if (wizard.mode === "promover") {
      if (step >= 1 && !toStringSafe(values.justificacion).trim()) { return "Escribí la justificación de la promoción."; }
      return "";
    }
    if (step >= 1 && !values.estado_declarado) { return "Seleccioná la situación actual comprobada."; }
    if (step >= 1 && !values.fecha_situacion_declarada) { return "Indicá la fecha correspondiente a la situación declarada."; }
    if (step >= 2 && !values.cod_mecanico_dental) { return "Seleccioná el mecánico del catálogo histórico."; }
    if (step >= 2 && !values.cod_local) { return "Seleccioná la sucursal declarada."; }
    if (step >= 2 && !toStringSafe(values.justificacion).trim()) { return "Escribí la justificación administrativa."; }
    return "";
  }

  function showHistoricalWizardError(message) {
    var box = state.root.querySelector("#tlabHistoricalWizardError");
    if (!box) { return; }
    box.textContent = message;
    box.hidden = false;
    box.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  function historicalWizardBack() {
    if (!state.historicalWizard || state.historicalWizard.saving) { return; }
    captureHistoricalWizardValues();
    state.historicalWizard.step = Math.max(1, state.historicalWizard.step - 1);
    renderHistoricalWizard();
  }

  function historicalWizardNext() {
    var error;
    if (!state.historicalWizard || state.historicalWizard.saving) { return; }
    captureHistoricalWizardValues();
    error = validateHistoricalWizardStep(state.historicalWizard.step);
    if (error) { showHistoricalWizardError(error); return; }
    state.historicalWizard.step = Math.min(state.historicalWizard.totalSteps, state.historicalWizard.step + 1);
    renderHistoricalWizard();
  }

  function closeHistoricalWizard() {
    if (!state.historicalWizard || state.historicalWizard.saving) { return; }
    state.historicalWizard = null;
    renderHistoricalDetailContent();
    focusFirst(detailBodyElement());
  }

  function historicalWizardPayload() {
    var wizard = state.historicalWizard;
    var detail = state.historicalDetail || {};
    var values = wizard.values;
    var id = pick(detail, ["id_historico", "cod_trabajo_mecanico_dental", "id", "codigo_historico"], state.detailId);
    var payload = {
      id_historico: id,
      cod_trabajo_mecanico_dental: pick(detail, ["cod_trabajo_mecanico_dental"], id),
      version_esperada: pick(detail, ["version", "version_registro"], pick(state.historicalEnvelope || {}, ["version"], "")),
      clave_idempotencia: wizard.idempotencyKey,
      justificacion: values.justificacion || ""
    };
    if (wizard.mode !== "promover") {
      payload.estado_declarado = values.estado_declarado || "";
      payload.cod_detalle_venta = values.cod_detalle_venta || "";
      payload.cod_mecanico_dental = values.cod_mecanico_dental || "";
      payload.cod_local = values.cod_local || "";
      payload.cod_custodio_actual = values.cod_custodio_actual || "";
      payload.fecha_objetivo = values.fecha_objetivo || "";
      payload.fecha_retiro_declarada = values.fecha_retiro_declarada || "";
      payload.fecha_entrega_declarada = values.fecha_entrega_declarada || "";
      payload.fecha_situacion_declarada = values.fecha_situacion_declarada || "";
    }
    return payload;
  }

  function submitHistoricalWizard() {
    var wizard = state.historicalWizard;
    var confirmed = state.root.querySelector("#tlabHistoricalConfirmed");
    var submit = state.root.querySelector("#tlabHistoricalWizardSubmit");
    var error;
    var id;
    if (!wizard || wizard.saving) { return; }
    captureHistoricalWizardValues();
    error = validateHistoricalWizardStep(wizard.mode === "promover" ? 1 : 2);
    if (error) { showHistoricalWizardError(error); return; }
    if (!confirmed || !confirmed.checked) { showHistoricalWizardError("Confirmá la declaración antes de guardar."); return; }
    if (!historicalActionAllowed(wizard.endpoint)) {
      showHistoricalWizardError("El servidor ya no habilita esta acción. Actualizá el registro.");
      return;
    }
    wizard.saving = true;
    id = state.detailId;
    if (submit) { submit.disabled = true; submit.innerHTML = '<i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Guardando...'; }
    request(wizard.endpoint, historicalWizardPayload()).then(function (response) {
      var message = response.message || (wizard.mode === "promover" ? "El registro fue promovido al flujo operativo." : "La declaración histórica quedó auditada.");
      state.historicalWizard = null;
      notify(message, "success");
      loadHistoricals(false);
      loadSummary().then(null, function (summaryError) { notify(summaryError.message, "error"); });
      return openHistoricalDetail(id, true);
    }).then(null, function (requestError) {
      if (!state.historicalWizard) { return; }
      state.historicalWizard.saving = false;
      renderHistoricalWizard();
      showHistoricalWizardError(requestError.message + (requestError.code && /CONFLICT|VERSION/i.test(requestError.code) ? " Actualizá el registro antes de volver a confirmar." : ""));
    });
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
    return !!action;
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
    actions = contextActions(serverActions);
    canAudit = boolValue(envelope.puede_ver_auditoria || detail.puede_ver_auditoria) && detailArray(["auditoria", "historial_auditoria"]).length > 0;
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
    body = detailBodyElement();
    if (!body) { return; }
    body.innerHTML = inlineDetailHeaderHtml("Ficha general · cadena de custodia", work.code) + '<div class="tlab-detail-summary">'
      + '<section class="tlab-detail-hero"><div class="tlab-detail-hero__image">' + imageBlock(work.image, "Evidencia principal de " + work.code) + '</div><div class="tlab-detail-hero__copy"><h3>' + escapeHtml(work.product) + '</h3><p><i class="fa-solid fa-user" aria-hidden="true"></i> ' + escapeHtml(work.patient) + '</p><p><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' + escapeHtml(work.branch) + '</p><span class="tlab-status tlab-status--' + work.deadlineClass + '">' + escapeHtml(work.deadlineText) + '</span></div></section>'
      + '<section class="tlab-panel"><h3>Situación actual</h3><div class="tlab-custody"><small>Actualmente en poder de</small><strong>' + escapeHtml(person(work.custodian).name) + '</strong></div><div class="tlab-data-grid" style="margin-top:10px">'
      + dataBox("Venta", work.sale || work.code) + dataBox("Situación", work.situation) + dataBox("Fecha objetivo", formatDate(work.targetDate, false)) + dataBox("Ciclo", work.currentCycle)
      + dataBox("Odontólogo", person(work.doctor, "Odontólogo").name) + dataBox("Mecánico dental", person(work.mechanic, "Mecánico dental").name)
      + dataBox("Días totales", formatDays(work.totalDays)) + dataBox("Días con custodio", formatDays(work.currentDays)) + dataBox("Ajustes", work.adjustments) + dataBox("Sucursal", work.branch)
      + '</div></section></div>'
      + '<section class="tlab-panel tlab-detail-section"><div class="tlab-section-heading"><div><h3>Próxima acción</h3><p>Disponible según permiso, asignación y custodia actual.</p></div></div><div class="tlab-actions">'
      + (actions.length ? actions.map(actionButtonHtml).join("") : '<p class="tlab-actions-empty"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> No hay acciones disponibles para este usuario.</p>')
      + '</div></section>'
      + '<nav class="tlab-detail-tabs" role="tablist" aria-label="Información del trabajo">' + tabs.map(function (tab) {
        return '<button type="button" role="tab" data-tlab-detail-tab="' + tab.key + '" aria-selected="' + (state.detailTab === tab.key ? "true" : "false") + '"><i class="fa-solid ' + tab.icon + '" aria-hidden="true"></i> ' + escapeHtml(tab.label) + '</button>';
      }).join("") + '</nav><div class="tlab-tab-panel" role="tabpanel">' + tabContent + '</div>';
  }

  function actionButtonHtml(action) {
    var secondary = action.code === "agregarEvidencia" || action.code === "agregarNota" || action.code === "registrarNovedad";
    return '<button type="button" class="tlab-button ' + (action.danger ? "tlab-button--danger" : (secondary ? "tlab-button--secondary" : "tlab-button--primary")) + '" data-tlab-action="' + escapeAttr(action.code) + '"><i class="fa-solid ' + escapeAttr(action.icon || "fa-arrow-right") + '" aria-hidden="true"></i>' + escapeHtml(action.label) + '</button>';
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

  function custodyTimelineEventHtml(item, index, length) {
    var event = custodyEvent(item);
    var evidence = event.mediaId
      ? '<button type="button" class="tlab-event-card__photo" data-tlab-media-id="' + escapeAttr(event.mediaId) + '" data-tlab-evidence-caption="' + escapeAttr(event.title) + '">' + imageBlock(event.image, event.title) + '</button>'
      : '<button type="button" class="tlab-event-card__photo" ' + (event.image ? 'data-tlab-evidence-url="' + escapeAttr(event.image) + '" data-tlab-evidence-caption="' + escapeAttr(event.title) + '"' : 'disabled') + '>' + imageBlock(event.image, event.title) + '</button>';
    return '<article class="tlab-timeline-node tlab-timeline-node--custody"><div class="tlab-event-card ' + (event.current ? "tlab-event-card--current" : (event.cancelled ? "tlab-event-card--cancelled" : "")) + '">'
      + '<div class="tlab-event-card__media">' + evidence + avatarHtml(event.actor) + '</div>'
      + '<span class="tlab-event-card__cycle">' + (event.current ? "Custodia actual" : (event.cancelled ? "Cierre por cancelación" : (event.final ? "Custodia final" : "Custodia cerrada"))) + '</span><h4>' + escapeHtml(event.title) + '</h4>'
      + '<span class="tlab-event-card__who">' + escapeHtml(event.actor.name) + ' · ' + escapeHtml(event.actor.role) + '</span><span class="tlab-event-card__when">' + escapeHtml(formatDate(event.date, true)) + ' · ' + escapeHtml(event.branch) + '</span>'
      + (event.performedBy && event.performedBy.name !== event.actor.name ? '<p class="tlab-event-card__audit"><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Registrado por ' + escapeHtml(event.performedBy.name) + '</p>' : '')
      + (event.elapsed ? '<p class="tlab-event-card__duration"><i class="fa-solid fa-clock" aria-hidden="true"></i> ' + escapeHtml(event.elapsed) + '</p>' : '')
      + (event.condition ? '<p class="tlab-event-card__condition"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> ' + escapeHtml(event.condition) + '</p>' : '')
      + (event.cancelled ? '<p class="tlab-event-card__cancelled"><i class="fa-solid fa-ban" aria-hidden="true"></i> Custodia cerrada sin entrega final</p>' : '')
      + (event.note ? '<p class="tlab-event-card__note">' + escapeHtml(event.note) + '</p>' : '')
      + (event.photoException ? '<p class="tlab-event-card__exception"><i class="fa-solid fa-camera-slash" aria-hidden="true"></i> ' + escapeHtml(photoExceptionLabel(event)) + '</p>' : '')
      + (event.noveltyCount ? '<p class="tlab-event-card__novelties"><i class="fa-solid fa-message" aria-hidden="true"></i> ' + event.noveltyCount + (event.noveltyCount === 1 ? " novedad" : " novedades") + '</p>' : '')
      + '</div>' + (index < length - 1 ? '<span class="tlab-timeline-node__knot" aria-hidden="true"></span><span class="tlab-timeline-node__elapsed">' + escapeHtml(event.elapsed || "Relevo") + '</span>' : '') + '</article>';
  }

  function renderTimelineTab() {
    var work = normalizeWork(state.detail || {});
    var events = work.route.length ? work.route : detailArray(["eventos", "timeline", "trazabilidad"]);
    var custody = work.custodyChain;
    events = events.filter(function (item) {
      var type = toStringSafe(pick(item, ["tipo_evento", "tipo", "accion"], "")).toLowerCase();
      return type !== "evidencia_agregada" && type !== "nota_agregada" && type !== "novedad_custodia" && type !== "hilo_tomado" && type !== "custodia_rectificada";
    });
    return '<div class="tlab-detail-tracks"><section class="tlab-timeline"><div class="tlab-section-heading"><div><h3>Recorrido operativo</h3><p>Etapas, decisiones y avance del trabajo.</p></div></div>'
      + (events.length ? '<div class="tlab-timeline-list">' + events.map(function (event, index) { return timelineEventHtml(event, index, events.length); }).join("") + '</div>' : '<div class="tlab-empty tlab-empty--compact"><div><i class="fa-solid fa-diagram-project" aria-hidden="true"></i><strong>Sin etapas operativas visibles</strong></div></div>') + '</section>'
      + '<section class="tlab-timeline tlab-timeline--custody"><div class="tlab-section-heading"><div><h3>El hilo de custodia</h3><p>Cada nodo es una persona interna que tuvo físicamente el trabajo.</p></div></div>'
      + (custody.length ? '<div class="tlab-timeline-list">' + custody.map(function (node, index) { return custodyTimelineEventHtml(node, index, custody.length); }).join("") + '</div>' : '<div class="tlab-empty tlab-empty--compact"><div><i class="fa-solid fa-link" aria-hidden="true"></i><strong>El hilo comenzará al iniciar el trabajo</strong></div></div>') + '</section></div>';
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
        var mime = pick(item, ["mime", "mime_type", "tipo_mime"], "");
        return '<button type="button" ' + (mediaId
          ? 'data-tlab-media-id="' + escapeAttr(mediaId) + '"'
          : 'data-tlab-evidence-url="' + escapeAttr(original || url) + '"')
          + ' data-tlab-evidence-caption="' + escapeAttr(label) + '">' + (toStringSafe(mime).toLowerCase() === "application/pdf" ? '<i class="fa-solid fa-file-pdf tlab-gallery__document" aria-hidden="true"></i>' : imageBlock(url, label)) + '<span>' + escapeHtml(label) + '</span></button>';
      }).join("") + '</div></section>';
    }).join("") + '</div>';
  }

  function renderNotesTab() {
    var notes = detailArray(["novedades"]).concat(detailArray(["notas", "observaciones"]));
    if (!notes.length) {
      notes = detailArray(["eventos", "timeline", "trazabilidad"]).filter(function (item) {
        var type = toStringSafe(pick(item, ["tipo_evento", "tipo", "accion"], "")).toLowerCase();
        return type === "nota_agregada" || type === "novedad_custodia";
      });
    }
    if (!notes.length) { return '<div class="tlab-empty"><div><i class="fa-solid fa-message" aria-hidden="true"></i><strong>Sin observaciones</strong><span>Las notas autorizadas del trabajo aparecerán aquí.</span></div></div>'; }
    return '<ul class="tlab-note-list">' + notes.map(function (item) {
      return '<li><strong>' + escapeHtml(pick(item, ["texto", "nota", "observacion", "descripcion", "detalle"], "Observación")) + '</strong><span>' + escapeHtml(humanizeHistoricalValue(pick(item, ["tipo_novedad", "categoria"], "Observación"))) + ' · ' + escapeHtml(person(item.actor || item.usuario || pick(item, ["nombre_usuario", "actor_nombre"], "Usuario")).name) + ' · ' + escapeHtml(formatDate(pick(item, ["fecha_hora", "fecha_servidor", "fecha", "creado_en"], ""), true)) + '</span></li>';
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
      values: action.code === "registrarInstalacion"
        ? { modo_resolucion: "instalado_entregado", condicion_pre_entrega: "conforme" }
        : {},
      files: [],
      filesProcessing: false,
      fileError: "",
      saving: false,
      idempotencyKey: makeIdempotencyKey()
    };
    renderActionDialog();
  }

  function actionContextWork() {
    return normalizeWork((state.action && state.action.work) || {});
  }

  function isStartAction(code) {
    return code === "iniciarTrabajo" || code === "iniciarTrabajosAgrupados";
  }

  function isInitialPreparationAction(code) {
    return isStartAction(code) || code === "asignarTecnico";
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
    nextLabel = action.step === 3 ? (action.config.submitLabel || "Confirmar acción") : "Continuar";
    layer.innerHTML = '<section class="tlab-dialog" role="dialog" aria-modal="true" aria-labelledby="tlabActionTitle" aria-busy="' + (action.saving ? 'true' : 'false') + '"><header class="tlab-dialog__header"><div><small>Acción guiada</small><h2 id="tlabActionTitle">' + escapeHtml(action.config.label) + '</h2></div><button type="button" class="tlab-icon-button tlab-icon-button--light" data-tlab-command="close-action" aria-label="Cerrar" ' + (action.saving ? 'disabled' : '') + '><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></header>'
      + '<form class="tlab-dialog__form" id="tlabActionForm"><div class="tlab-dialog__body"><div class="tlab-steps" aria-label="Progreso"><span class="tlab-step ' + (action.step === 1 ? "is-current" : "is-done") + '" data-step="1">Revisar</span><span class="tlab-step ' + (action.step === 2 ? "is-current" : (action.step > 2 ? "is-done" : "")) + '" data-step="2">Completar</span><span class="tlab-step ' + (action.step === 3 ? "is-current" : "") + '" data-step="3">Confirmar</span></div>' + body + '<div class="tlab-form-error" id="tlabActionError" hidden></div><div class="tlab-upload-progress" id="tlabUploadProgress" hidden><span></span></div></div>'
      + '<footer class="tlab-dialog__footer"><button type="button" class="tlab-button tlab-button--ghost" data-tlab-command="action-back" ' + (action.step === 1 || action.saving ? "disabled" : "") + '><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Volver</button><div class="tlab-dialog__footer-actions"><button type="button" class="tlab-button tlab-button--secondary" data-tlab-command="close-action" ' + (action.saving ? 'disabled' : '') + '>Cancelar</button>'
      + (action.step === 3 ? '<button type="submit" class="tlab-button ' + (action.config.danger ? "tlab-button--danger" : "tlab-button--primary") + '" id="tlabActionSubmit" ' + (action.filesProcessing ? "disabled" : "") + '>' : '<button type="button" class="tlab-button tlab-button--primary" data-tlab-command="action-next" ' + (action.filesProcessing ? "disabled" : "") + '>') + '<i class="fa-solid ' + escapeAttr(action.filesProcessing ? "fa-hourglass-half" : (action.config.icon || "fa-arrow-right")) + '" aria-hidden="true"></i>' + escapeHtml(action.filesProcessing ? "Preparando foto..." : nextLabel) + '</button></div></footer></form></section>';
    layer.hidden = false;
    focusFirst(layer);
  }

  function renderActionIntro(work) {
    var action = state.action;
    var raw = action.work || {};
    var sessionPerson = person(
      state.context.usuario || state.context.actor || {
        nombre: pick(state.context, ["nombre_usuario", "usuario_nombre", "nombre"], "Usuario autenticado"),
        rol: pick(state.context, ["rol", "perfil"], "Usuario Telar"),
        avatar_url: pick(state.context, ["avatar", "avatar_usuario", "usuario_avatar", "avatar_url"], "")
      },
      "Usuario Telar"
    );
    var custodyGuide = action.code === "tomarHilo"
      ? '<section class="tlab-custody-guide"><div><small>Custodio anterior</small><strong>' + escapeHtml(person(work.currentCustody.person).name) + '</strong></div><div class="tlab-custody-guide__arrow"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></div><div class="tlab-custody-guide__person">' + avatarHtml(sessionPerson) + '<span><small>La custodia quedará a nombre de</small><strong>' + escapeHtml(sessionPerson.name) + '</strong><em>' + escapeHtml(sessionPerson.role) + (pick(state.context, ["nombre_local", "local_nombre"], "") ? ' · ' + escapeHtml(pick(state.context, ["nombre_local", "local_nombre"], "")) : '') + '</em></span></div><p><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Al confirmar, la fecha y hora del servidor abrirán tu período de custodia. Nadie externo a Telar se agrega como custodio.</p></section>'
      : '';
    return '<div class="tlab-action-context">'
      + '<div><small>Código</small><strong>' + escapeHtml(work.code) + '</strong></div>'
      + '<div><small>Paciente</small><strong>' + escapeHtml(work.patient) + '</strong></div>'
      + '<div><small>Producto</small><strong>' + escapeHtml(work.product) + '</strong></div>'
      + '</div>' + custodyGuide + '<div class="tlab-confirm-box"><h3><i class="fa-solid ' + escapeAttr(action.config.icon || "fa-circle-check") + '" aria-hidden="true"></i> Qué se registrará</h3><p>' + escapeHtml(pick(action.config, ["ayuda", "descripcion"], actionHelp(action.code))) + '</p>'
      + (isStartAction(action.code) && pick(raw, ["modo_individualizacion"], "") ? '<p><strong>Individualización:</strong> ' + escapeHtml(pick(raw, ["modo_individualizacion"], "").replace(/_/g, " ")) + '</p>' : '')
	  + (action.code === "iniciarTrabajosAgrupados" ? renderGroupedStartSummary(raw) : '')
      + '<p><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> La fecha, el usuario y la trazabilidad serán registrados por el servidor.</p></div>';
  }

  function renderGroupedStartSummary(raw) {
    var regularizacion = raw.regularizacion_unidades || raw.regularizacion || {};
    var unidades = asArray(regularizacion.unidades);
    return '<section class="tlab-grouped-origin"><div><small>Codigo de origen compartido</small><strong>'
      + escapeHtml(regularizacion.codigo_origen || "Pendiente") + '</strong></div><ol>'
      + unidades.map(function (unidad) {
        var piezas = asArray(unidad.piezas);
        return '<li><b>Trabajo ' + escapeHtml(unidad.numero_unidad || "-") + ' de '
          + escapeHtml(regularizacion.cantidad_unidades || unidades.length) + '</b><span>'
          + escapeHtml(piezas.length ? "Piezas " + piezas.join(", ") : (unidad.pieza ? "Pieza " + unidad.pieza : "Sin ubicacion"))
          + '</span></li>';
      }).join("") + '</ol><p>El técnico puede asignarse ahora o más adelante. Las instrucciones y la evidencia inicial se aplicarán al lote; después, cada trabajo avanzará de forma independiente.</p></section>';
  }

  function actionHelp(code) {
    var help = {
      iniciarTrabajo: "Se creará el ciclo Original y la primera evidencia de custodia. Si no elegís técnico, quedará con Técnico pendiente.",
      iniciarTrabajosAgrupados: "Se creará un trabajo independiente por cada selección, todos con el mismo código de origen. Si no elegís técnico, quedarán con Técnico pendiente.",
      asignarTecnico: "Se asignará el técnico a todos los trabajos pendientes del mismo código de origen, sin iniciar el traslado.",
      iniciarTransferencia: "Este registro es opcional. Deja constancia de la salida hacia el técnico asignado, pero no cambia la custodia ni impide que otra persona tome el hilo directamente.",
      tomarHilo: "Se cerrará el período del custodio anterior y se abrirá un nuevo nodo a nombre del usuario autenticado.",
      registrarNovedad: "La novedad quedará dentro de tu período de custodia, sin transferir el trabajo ni borrar antecedentes.",
      rectificarCustodia: "La corrección administrativa cambiará el responsable actual y conservará el motivo, el actor y la hora para auditoría.",
      agregarEvidencia: "Las fotos se agregarán al ciclo actual sin reemplazar evidencias anteriores.",
      agregarNota: "La observación quedará vinculada al nodo actual sin cambiar la custodia.",
      iniciarDevolucion: "Se registrará la entrega del trabajo terminado y quedará pendiente de recepción en clínica.",
      solicitarAjuste: "Se abrirá un nuevo ciclo de ajuste sin borrar la historia original.",
      aprobarTrabajo: "El tiempo de laboratorio quedará cerrado y el trabajo esperará su instalación.",
      registrarInstalacion: "Confirmará la instalación y finalización, llevará el tratamiento al 100 % y cerrará el hilo. Sólo puede hacerlo el custodio actual; si hay una transferencia pendiente, también se cerrará dejando constancia.",
      cancelarTrabajo: "La cancelación requiere motivo y conserva todos los eventos anteriores."
    };
    return help[code] || "La operación quedará registrada en la trazabilidad.";
  }

  function eligibleRecipients() {
    var config = state.action.config || {};
    var work = state.action.work || {};
    return asArray(config.destinatarios || config.destinatarios_permitidos || work.destinatarios_permitidos || (state.detailEnvelope && state.detailEnvelope.destinatarios_permitidos) || []);
  }

  function automaticTransferDestination() {
    var recipients = eligibleRecipients();
    return recipients.length ? person(recipients[0], "Mecánico dental") : null;
  }

  function transferDestinationHtml() {
    var destination = automaticTransferDestination();
    if (!destination) {
      return '<section class="tlab-transfer-destination is-warning"><i class="fa-solid fa-user-clock" aria-hidden="true"></i><span><small>Salida opcional</small><strong>Técnico no disponible</strong><em>No necesitás registrar esta salida para guardar ni continuar el trabajo.</em></span></section>';
    }
    return '<section class="tlab-transfer-destination"><div>' + avatarHtml(destination) + '<span><small>Destino previsto automático</small><strong>' + escapeHtml(destination.name) + '</strong><em>' + escapeHtml(destination.role || "Mecánico dental") + '</em></span></div><p><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Seguirás siendo custodio hasta que otra persona confirme <strong>Tomar el hilo</strong>.</p></section>';
  }

  function adjustmentReasons() {
    var config = state.action.config || {};
    return asArray(config.motivos || config.motivos_ajuste || catalogItems(["motivos_ajuste"]));
  }

  function noveltyTypes() {
    var config = state.action.config || {};
    var supplied = asArray(config.tipos_novedad || config.novedades_permitidas || catalogItems(["tipos_novedad_custodia"]));
    return supplied.length ? supplied : [
      { codigo: "modificacion_trabajo", nombre: "Modificación del trabajo" },
      { codigo: "cambio_color", nombre: "Cambio de color" },
      { codigo: "ajuste_solicitado", nombre: "Ajuste solicitado" },
      { codigo: "problema_detectado", nombre: "Problema detectado" },
      { codigo: "pieza_danada", nombre: "Pieza dañada" },
      { codigo: "falta_informacion", nombre: "Falta información" },
      { codigo: "trabajo_listo", nombre: "Trabajo listo" },
      { codigo: "solicitud_confirmacion_clinica", nombre: "Solicitud de confirmación clínica" },
      { codigo: "observacion_general", nombre: "Observación general" }
    ];
  }

  function noPhotoReasons() {
    var config = state.action.config || {};
    var supplied = asArray(config.motivos_sin_foto || config.motivos_excepcion_foto);
    return supplied.length ? supplied : [
      { codigo: "falla_dispositivo", nombre: "Falla del dispositivo" },
      { codigo: "imposibilidad_operativa", nombre: "Imposibilidad operativa" },
      { codigo: "foto_no_disponible", nombre: "La foto no está disponible" },
      { codigo: "otro", nombre: "Otro motivo" }
    ];
  }

  function custodyCorrectionPeople() {
    var config = state.action.config || {};
    var work = state.action.work || {};
    return asArray(config.custodios_permitidos || config.usuarios_telar || config.custodios
      || work.custodios_permitidos || work.usuarios_telar
      || (state.detailEnvelope && (state.detailEnvelope.custodios_permitidos || state.detailEnvelope.usuarios_telar))
      || catalogItems(["custodios", "responsables", "usuarios_telar"]));
  }

  function renderActionFields(work) {
    var action = state.action;
    var config = action.config;
    var values = action.values;
    var fields = [];
    var raw = action.work || {};
    var recipients;
    var reasons;
    var noPhoto = action.code === "registrarInstalacion" && boolValue(values.sin_foto);
    var evidenceHtml;
    if (isStartAction(action.code)) {
      var tieneCatalogoContextual = Object.prototype.hasOwnProperty.call(raw, "tecnicos_disponibles")
        || Object.prototype.hasOwnProperty.call(raw, "mecanicos");
      var tecnicosInicio = asArray(raw.tecnicos_disponibles || raw.mecanicos || []);
      if (!tieneCatalogoContextual) {
        tecnicosInicio = catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"]).filter(function (item) {
          return typeof item !== "object" || item.habilitado_flujo !== false;
        });
      }
      fields.push(selectField(
        "Técnico de laboratorio",
        "cod_tecnico_usuario",
        tecnicosInicio,
        values.cod_tecnico_usuario,
        false,
        "Asignar más adelante",
        tecnicosInicio.length
          ? "Podés elegirlo ahora o continuar con Técnico pendiente."
          : "No hay técnicos habilitados. El trabajo se guardará con Técnico pendiente."
      ));
      fields.push('<div class="tlab-field"><label for="tlabActionColor">Color o colorímetro</label><input id="tlabActionColor" name="colorimetro" type="text" maxlength="30" placeholder="Ej.: A2" value="' + escapeAttr(values.colorimetro || pick(raw, ["colorimetro", "color", "color_precargado"], "")) + '"><small>Completálo cuando el tratamiento lo requiera.</small></div>');
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionInstructions">Instrucciones para el laboratorio</label><textarea id="tlabActionInstructions" name="instrucciones" maxlength="1000" placeholder="Material, diseño u otra especificación necesaria">' + escapeHtml(values.instrucciones || pick(raw, ["instrucciones", "observacion_precargada", "indicaciones"], "")) + '</textarea><small>El producto y las ubicaciones ya están vinculados; agregá solamente lo que falte.</small></div>');
    }
    if (action.code === "asignarTecnico") {
      var tecnicosAsignacion = catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"]).filter(function (item) {
        return typeof item !== "object" || item.habilitado_flujo !== false;
      });
      fields.push(selectField(
        "Técnico de laboratorio",
        "cod_tecnico_usuario",
        tecnicosAsignacion,
        values.cod_tecnico_usuario,
        true,
        "Seleccionar técnico habilitado",
        "La asignación alcanzará a los trabajos con Técnico pendiente del mismo código de origen. No iniciará el traslado."
      ));
    }
    if (isInitialPreparationAction(action.code)) {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionAdministrativeNote">Observación administrativa</label><textarea id="tlabActionAdministrativeNote" name="observacion" maxlength="750" placeholder="Dato adicional para dejar registrado, si corresponde">' + escapeHtml(values.observacion || "") + '</textarea><small>Es opcional y quedará vinculada al evento en la trazabilidad.</small></div>');
    }
    if (action.code === "tomarHilo") {
      var receptionCondition = values.condicion_recepcion || "";
      fields.push('<fieldset class="tlab-field tlab-field--wide tlab-reception-condition"><legend>¿Cómo recibís este trabajo? <span aria-hidden="true">*</span></legend><label class="tlab-choice-card"><input type="radio" name="condicion_recepcion" value="conforme" ' + (receptionCondition === "conforme" ? "checked" : "") + '><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i><strong>Conforme</strong><small>Coincide con lo esperado y puede continuar.</small></span></label><label class="tlab-choice-card"><input type="radio" name="condicion_recepcion" value="con_observaciones" ' + (receptionCondition === "con_observaciones" ? "checked" : "") + '><span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Con observaciones</strong><small>Lo recibís, dejando constancia de una diferencia.</small></span></label></fieldset>');
      if (receptionCondition === "con_observaciones") {
        fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionNote">Observación de recepción <span aria-hidden="true">*</span></label><textarea id="tlabActionNote" name="observacion" required minlength="5" maxlength="750" placeholder="Describí qué observaste al recibir el trabajo">' + escapeHtml(values.observacion || "") + '</textarea><small>La observación quedará visible en este nodo de custodia.</small></div>');
      }
    }
    if (action.code === "registrarInstalacion") {
      var deliveryCondition = values.condicion_pre_entrega || "conforme";
      fields.push('<fieldset class="tlab-field tlab-field--wide tlab-reception-condition"><legend>Situación antes de entregar <span aria-hidden="true">*</span></legend><label class="tlab-choice-card"><input type="radio" name="condicion_pre_entrega" value="conforme" ' + (deliveryCondition === "conforme" ? "checked" : "") + '><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i><strong>Conforme</strong><small>El trabajo fue instalado y entregado sin novedades.</small></span></label><label class="tlab-choice-card"><input type="radio" name="condicion_pre_entrega" value="con_observaciones" ' + (deliveryCondition === "con_observaciones" ? "checked" : "") + '><span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Con observaciones</strong><small>Se cierra el hilo dejando constancia de la situación.</small></span></label></fieldset>');
      if (deliveryCondition === "con_observaciones") {
        fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionDeliveryNote">Detalle de la situación <span aria-hidden="true">*</span></label><textarea id="tlabActionDeliveryNote" name="observacion_entrega" required maxlength="1000" placeholder="Describí la situación observada antes de la entrega">' + escapeHtml(values.observacion_entrega || "") + '</textarea><small>Es obligatorio, sin una cantidad mínima de caracteres, y quedará en el último nodo.</small></div>');
      }
      fields.push('<label class="tlab-field tlab-field--wide tlab-photo-exception"><input type="checkbox" name="sin_foto" value="1"' + (noPhoto ? " checked" : "") + '><span><strong>No existe evidencia fotográfica</strong><small>Usá esta excepción sólo cuando realmente no sea posible adjuntar una foto. La justificación quedará auditada.</small></span></label>');
      if (noPhoto) {
        fields.push(selectField("Motivo de la excepción", "motivo_sin_foto", noPhotoReasons(), values.motivo_sin_foto, true, "Seleccionar motivo", "La excepción no reemplaza el cierre ni elimina su trazabilidad."));
        fields.push('<div class="tlab-field"><label for="tlabActionNoPhotoDetail">Justificación de la excepción <span aria-hidden="true">*</span></label><textarea id="tlabActionNoPhotoDetail" name="detalle_sin_foto" required maxlength="750" placeholder="Explicá por qué no fue posible obtener la fotografía">' + escapeHtml(values.detalle_sin_foto || "") + '</textarea><small>Este detalle será parte del evento final.</small></div>');
      }
    }
    if (action.code === "registrarNovedad") {
      fields.push(selectField("Tipo de novedad", "tipo_novedad", noveltyTypes(), values.tipo_novedad || "observacion_general", false, "Observación general", "Ayuda a identificar la novedad sin cambiar la custodia."));
    }
    if (action.code === "rectificarCustodia") {
      fields.push(selectField("Nuevo custodio interno", "cod_custodio_rectificado", custodyCorrectionPeople(), values.cod_custodio_rectificado, true, "Seleccionar usuario Telar", "Es una corrección administrativa; no representa una recepción normal."));
    }
    if (action.code === "iniciarTransferencia") {
      fields.push(transferDestinationHtml());
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
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionJustification">Justificación</label><textarea id="tlabActionJustification" name="justificacion" required ' + (action.code === "rectificarCustodia" ? 'maxlength="750"' : 'maxlength="1000"') + ' placeholder="Explicá el motivo de forma clara">' + escapeHtml(values.justificacion || "") + '</textarea><small>Es obligatoria, sin una cantidad mínima de caracteres, y quedará auditada.</small></div>');
    }
    if ((config.note || config.noteRequired || boolValue(config.permite_observacion)) && action.code !== "tomarHilo") {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionNote">' + (action.code === "registrarNovedad" ? "Descripción de la novedad" : (action.code === "agregarNota" ? "Observación" : "Indicaciones u observaciones")) + '</label><textarea id="tlabActionNote" name="observacion" ' + (config.noteRequired ? "required" : "") + (action.code === "registrarNovedad" ? ' minlength="3" maxlength="750"' : ' maxlength="1200"') + ' placeholder="Agregá sólo información necesaria para este trabajo">' + escapeHtml(values.observacion || pick(raw, ["observacion_precargada", "indicaciones"], "")) + '</textarea>' + (action.code === "registrarNovedad" ? '<small>Quedará vinculada a tu período actual de custodia.</small>' : '') + '</div>');
    }
    if (boolValue(config.requiere_motivo_excepcion)) {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionAuditReason">Observación de intervención excepcional <span aria-hidden="true">*</span></label><textarea id="tlabActionAuditReason" name="motivo_excepcion" required maxlength="750" placeholder="Dejá constancia de la intervención">' + escapeHtml(values.motivo_excepcion || "") + '</textarea><small>Es obligatoria, sin una cantidad mínima de caracteres, y quedará auditada.</small></div>');
    }
    if (action.code === "solicitarAjuste" && values.motivo && toStringSafe(values.motivo).toLowerCase() === "otro") {
      fields.push('<div class="tlab-field tlab-field--wide"><label for="tlabActionOtherReason">Descripción de “Otro”</label><input id="tlabActionOtherReason" name="motivo_otro" type="text" required maxlength="180" value="' + escapeAttr(values.motivo_otro || "") + '"></div>');
    }
    if (noPhoto) {
      evidenceHtml = '<section class="tlab-photo-exception-note"><i class="fa-solid fa-camera-slash" aria-hidden="true"></i><span><strong>Cierre sin fotografía</strong><small>El motivo y la justificación reemplazarán únicamente la evidencia faltante.</small></span></section>';
    } else if (config.evidence || config.evidenceOptional || boolValue(config.requiere_evidencia) || boolValue(config.permite_evidencia)) {
      evidenceHtml = renderEvidencePicker(
        config.evidence || boolValue(config.requiere_evidencia),
        boolValue(config.documents)
      );
    } else {
      evidenceHtml = "";
    }
    return '<div class="tlab-form-grid">' + fields.join("") + '</div>' + evidenceHtml;
  }

  function selectField(label, name, items, selected, required, placeholder, helper) {
    return '<div class="tlab-field"><label for="tlabAction_' + escapeAttr(name) + '">' + escapeHtml(label) + '</label><select id="tlabAction_' + escapeAttr(name) + '" name="' + escapeAttr(name) + '" ' + (required ? "required" : "") + '><option value="">' + escapeHtml(placeholder) + '</option>' + items.map(function (item) {
      var html = optionHtml(item);
      var value = typeof item === "object" ? pick(item, ["cod_tecnico_usuario", "cod_custodio", "cod_usuario", "id", "codigo", "cod", "valor", "value", "cod_persona"], "") : item;
      return toStringSafe(value) === toStringSafe(selected) ? html.replace("<option ", '<option selected ') : html;
    }).join("") + '</select>' + (helper ? '<small>' + escapeHtml(helper) + '</small>' : '') + '</div>';
  }

  function renderEvidencePicker(required, allowDocuments) {
    var files = state.action.files || [];
    var accept = allowDocuments ? "image/jpeg,image/png,image/webp,application/pdf" : "image/jpeg,image/png,image/webp";
    var processing = state.action.filesProcessing;
    var helper = allowDocuments
      ? "Telar optimiza las fotografías automáticamente. Los PDF pueden pesar hasta " + formatFileLimit(MAX_FILE_SIZE) + "."
      : "Telar ajusta el peso y el tamaño automáticamente; no necesitás comprimir la foto.";
    return '<section class="tlab-evidence-box" aria-busy="' + (processing ? "true" : "false") + '"><div class="tlab-evidence-box__heading"><div><strong>' + (allowDocuments ? "Archivos" : "Fotografías") + ' ' + (required ? (allowDocuments ? "obligatorios" : "obligatorias") : "opcionales") + '</strong><small>' + escapeHtml(helper) + '</small></div><span class="tlab-status ' + (processing ? "tlab-status--warning" : (files.length ? "tlab-status--ok" : "tlab-status--neutral")) + '">' + (processing ? "Preparando..." : files.length + " de " + MAX_FILES) + '</span></div>'
      + '<div class="tlab-evidence-choices"><button type="button" class="tlab-file-choice" data-tlab-command="open-camera-action" ' + (processing ? "disabled" : "") + '><i class="fa-solid fa-camera" aria-hidden="true"></i><span><strong>Tomar foto</strong><small>Sin salir de Telar cuando Android lo permite</small></span></button><label class="tlab-file-choice ' + (processing ? "is-disabled" : "") + '"><i class="fa-solid ' + (allowDocuments ? "fa-paperclip" : "fa-images") + '" aria-hidden="true"></i><span><strong>' + (allowDocuments ? "Elegir archivos" : "Elegir de galería") + '</strong><small>También se prepara automáticamente</small></span><input type="file" accept="' + accept + '" multiple data-tlab-file-input aria-label="' + (allowDocuments ? "Seleccionar archivos" : "Seleccionar fotografías de la galería") + '" ' + (processing ? "disabled" : "") + '></label></div>'
      + (processing ? '<p class="tlab-file-processing"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i><span><strong>Preparando fotografía...</strong>No cierres esta ventana; normalmente tarda sólo unos segundos.</span></p>' : '')
      + '<div class="tlab-preview-list" id="tlabPreviewList">' + files.map(previewHtml).join("") + '</div><div class="tlab-file-error" id="tlabFileError" ' + (state.action.fileError ? "" : "hidden") + '>' + escapeHtml(state.action.fileError || "") + '</div></section>';
  }

  function previewHtml(file, index) {
    var url = file._tlabUrl;
    var isPdf = file.type === "application/pdf";
    if (!url) {
      url = URL.createObjectURL(file);
      file._tlabUrl = url;
      state.objectUrls.push(url);
    }
    return '<figure class="tlab-preview ' + (isPdf ? "tlab-preview--document" : "") + '" data-tlab-prepared-size="' + escapeAttr(file.size || 0) + '">' + (isPdf ? '<span><i class="fa-solid fa-file-pdf" aria-hidden="true"></i><small title="' + escapeAttr(file.name || "Documento PDF") + '">' + escapeHtml(file.name || "Documento PDF") + '</small></span>' : '<img src="' + escapeAttr(url) + '" alt="Vista previa ' + (index + 1) + '"><figcaption><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Foto lista</figcaption>') + '<button type="button" data-tlab-preview-remove="' + index + '" aria-label="Quitar ' + escapeAttr(file.name || "archivo") + '"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></figure>';
  }

  function renderActionConfirmation(work) {
    var action = state.action;
    var values = action.values;
    var items = ["Trabajo: " + work.code, "Acción: " + action.config.label];
    var recipient = selectedOptionLabel("cod_destinatario");
    var mechanic = selectedOptionLabel("cod_tecnico_usuario");
    var correctedCustodian = selectedOptionLabel("cod_custodio_rectificado");
    var automaticDestination = action.code === "iniciarTransferencia" ? automaticTransferDestination() : null;
    if (recipient) { items.push("Destinatario: " + recipient); }
    if (automaticDestination) { items.push("Destino previsto: " + automaticDestination.name + " (automático)"); }
    if (action.code === "iniciarTransferencia") { items.push("Custodia: seguirá con el responsable actual hasta que otra persona tome el hilo"); }
    if (mechanic) { items.push("Técnico: " + mechanic); }
    else if (isStartAction(action.code)) { items.push("Técnico: Técnico pendiente"); }
    if (correctedCustodian) { items.push("Nuevo custodio: " + correctedCustodian); }
    if (values.condicion_recepcion) { items.push("Condición: " + humanizeHistoricalValue(values.condicion_recepcion)); }
    if (values.condicion_pre_entrega) { items.push("Situación antes de entregar: " + humanizeHistoricalValue(values.condicion_pre_entrega)); }
    if (values.observacion_entrega) { items.push("Detalle de la situación: " + toStringSafe(values.observacion_entrega).slice(0, 180)); }
    if (boolValue(values.sin_foto)) { items.push("Foto: excepción auditada · " + humanizeHistoricalValue(values.motivo_sin_foto)); }
    if (boolValue(values.sin_foto) && values.detalle_sin_foto) { items.push("Justificación sin foto: " + toStringSafe(values.detalle_sin_foto).slice(0, 180)); }
    if (action.code === "registrarInstalacion" && normalizeWork(work).pendingTransfer) { items.push("Transferencia pendiente: se cerrará con el hilo y quedará vinculada al evento final"); }
    if (values.tipo_novedad) { items.push("Tipo de novedad: " + humanizeHistoricalValue(values.tipo_novedad)); }
    if (values.motivo) { items.push("Motivo: " + values.motivo + (values.motivo_otro ? " · " + values.motivo_otro : "")); }
    if (values.observacion) { items.push((action.code === "registrarNovedad" ? "Descripción: " : (isInitialPreparationAction(action.code) ? "Observación administrativa: " : "Observación: ")) + toStringSafe(values.observacion).slice(0, 180)); }
    if (values.justificacion) { items.push("Justificación: " + toStringSafe(values.justificacion).slice(0, 180)); }
    if (values.motivo_excepcion) { items.push("Excepción de auditoría: " + values.motivo_excepcion); }
    if (state.action.files.length) { items.push("Archivos adjuntos: " + state.action.files.length); }
    if (action.code === "iniciarTrabajosAgrupados") {
      var regularizacion = action.work.regularizacion_unidades || action.work.regularizacion || {};
      items.push("Trabajos independientes: " + (regularizacion.cantidad_unidades || asArray(regularizacion.unidades).length));
      items.push("Código de origen: " + (regularizacion.codigo_origen || "Pendiente"));
    }
    return '<div class="tlab-confirm-box"><h3>Revisá antes de confirmar</h3><ul class="tlab-confirm-list">' + items.map(function (item) { return '<li>' + escapeHtml(item) + '</li>'; }).join("") + '</ul><label class="tlab-check"><input type="checkbox" id="tlabActionConfirmed" required><span>' + escapeHtml(action.config.confirmation) + '</span></label></div>';
  }

  function selectedOptionLabel(name) {
    var value = state.action.values[name];
    var items = name === "cod_tecnico_usuario" ? catalogItems(["mecanicos", "tecnicos", "tecnicos_disponibles"])
      : (name === "cod_custodio_rectificado" ? custodyCorrectionPeople() : eligibleRecipients());
    var found = items.filter(function (item) {
      var itemValue = typeof item === "object" ? pick(item, ["cod_tecnico_usuario", "cod_custodio", "cod_usuario", "id", "codigo", "cod", "valor", "value", "cod_persona"], "") : item;
      return toStringSafe(itemValue) === toStringSafe(value);
    })[0];
    if (!found) { return value || ""; }
    return typeof found === "object" ? pick(found, ["nombre", "descripcion", "etiqueta", "label"], value) : found;
  }

  function captureActionValues() {
    var form;
    var noPhoto;
    if (!state.action || !state.root) { return; }
    form = state.root.querySelector("#tlabActionForm");
    if (!form) { return; }
    forEachFormValue(form, function (value, key) {
      if (key !== "evidencias[]") { state.action.values[key] = value; }
    });
    noPhoto = form.querySelector('[name="sin_foto"]');
    if (noPhoto) {
      state.action.values.sin_foto = noPhoto.checked ? "1" : "0";
      if (!noPhoto.checked) {
        state.action.values.motivo_sin_foto = "";
        state.action.values.detalle_sin_foto = "";
      }
    }
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
    if (state.action.filesProcessing) { showActionError("Esperá a que Telar termine de preparar la fotografía."); return; }
    if (state.camera && state.camera.target === "action") { closeCamera(false); }
    captureActionValues();
    error = validateActionStep(state.action.step);
    if (error) { showActionError(error); return; }
    if (state.action.step === 1 && !catalogsAreFresh()) {
      if (state.action.loadingCatalogs) { return; }
      state.action.loadingCatalogs = true;
      notify("Preparando las opciones de la acción...", "info");
      loadCatalogs(false).then(function () {
        if (!state.action) { return; }
        state.action.loadingCatalogs = false;
        state.action.step = 2;
        renderActionDialog();
      }).then(null, function (catalogError) {
        if (state.action) { state.action.loadingCatalogs = false; }
        showActionError(catalogError.message || "No se pudieron preparar las opciones.");
      });
      return;
    }
    state.action.step = Math.min(3, state.action.step + 1);
    renderActionDialog();
  }

  function validateActionStep(step) {
    var action = state.action;
    var config = action.config;
    var values = action.values;
    if (step < 2) { return ""; }
    if (action.filesProcessing) { return "Esperá a que Telar termine de preparar la fotografía."; }
    if ((config.recipient || boolValue(config.requiere_destinatario)) && !values.cod_destinatario) { return "Seleccioná el destinatario físico."; }
    if ((config.mechanic || boolValue(config.requiere_mecanico)) && !values.cod_tecnico_usuario) { return "Seleccioná el mecánico responsable."; }
    if ((config.reason || boolValue(config.requiere_motivo)) && !values.motivo) { return "Seleccioná el motivo del ajuste."; }
    if ((config.justification || boolValue(config.requiere_justificacion)) && !toStringSafe(values.justificacion).trim()) { return "Escribí una justificación."; }
    if (boolValue(config.requiere_motivo_excepcion) && !toStringSafe(values.motivo_excepcion).trim()) { return "Escribí una observación para la intervención excepcional."; }
    if (config.noteRequired && !toStringSafe(values.observacion).trim()) { return "Escribí la observación."; }
    if (action.code === "tomarHilo" && values.condicion_recepcion !== "conforme" && values.condicion_recepcion !== "con_observaciones") { return "Indicá cómo recibís el trabajo."; }
    if (action.code === "tomarHilo" && values.condicion_recepcion === "con_observaciones" && toStringSafe(values.observacion).trim().length < 5) { return "Describí la observación de recepción con al menos cinco caracteres."; }
    if (action.code === "registrarInstalacion" && values.condicion_pre_entrega !== "conforme" && values.condicion_pre_entrega !== "con_observaciones") { return "Indicá la situación del trabajo antes de entregarlo."; }
    if (action.code === "registrarInstalacion" && values.condicion_pre_entrega === "con_observaciones" && !toStringSafe(values.observacion_entrega).trim()) { return "Describí la situación observada antes de la entrega."; }
    if (action.code === "registrarInstalacion" && boolValue(values.sin_foto) && !toStringSafe(values.motivo_sin_foto).trim()) { return "Seleccioná el motivo de la excepción fotográfica."; }
    if (action.code === "registrarInstalacion" && boolValue(values.sin_foto) && !toStringSafe(values.detalle_sin_foto).trim()) { return "Justificá por qué no fue posible adjuntar la fotografía."; }
    if (action.code === "registrarNovedad" && toStringSafe(values.observacion).trim().length < 3) { return "Describí la novedad con al menos tres caracteres."; }
    if (action.code === "rectificarCustodia" && !values.cod_custodio_rectificado) { return "Seleccioná el nuevo custodio interno."; }
    if (action.code === "solicitarAjuste" && toStringSafe(values.motivo).toLowerCase() === "otro" && !toStringSafe(values.motivo_otro).trim()) { return "Describí el motivo seleccionado como “Otro”."; }
    if ((config.evidence || boolValue(config.requiere_evidencia)) && !action.files.length
        && !(action.code === "registrarInstalacion" && boolValue(values.sin_foto))) { return "Agregá al menos una fotografía para continuar."; }
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
    var action = state.action;
    var allowDocuments;
    var preparation;
    if (!action || action.filesProcessing) { return; }
    allowDocuments = boolValue(action.config.documents);
    /* Las lecturas comienzan antes de redibujar para copiar el archivo temporal
       que Android entrega cuando se vuelve desde su aplicación de cámara. */
    preparation = prepareMediaSelection(fileList, allowDocuments, MAX_FILES - action.files.length);
    action.filesProcessing = true;
    action.fileError = "";
    renderActionDialog();
    preparation.then(function (result) {
      if (state.action !== action) { releasePreparedMedia(result.files); return; }
      result.files.forEach(function (file) { action.files.push(file); });
      action.filesProcessing = false;
      action.fileError = result.error || "";
      renderActionDialog();
    }).then(null, function (error) {
      if (state.action !== action) { return; }
      action.filesProcessing = false;
      action.fileError = error.message || "No se pudo preparar la fotografía.";
      renderActionDialog();
    });
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
    state.action.fileError = "";
    renderActionDialog();
  }

  function revokeObjectUrls() {
    state.objectUrls.forEach(function (url) { try { URL.revokeObjectURL(url); } catch (ignore) {} });
    state.objectUrls = [];
  }

  function closeAction(force) {
    var layer;
    if (!state.root) { return; }
    if (state.action && state.action.saving && force !== true) {
      notify("La acción se está guardando. Esperá la confirmación del servidor.", "info");
      return false;
    }
    if (state.camera && state.camera.target === "action") { closeCamera(false); }
    layer = state.root.querySelector("#tlabActionLayer");
    layer.hidden = true;
    layer.innerHTML = "";
    revokeObjectUrls();
    state.action = null;
    return true;
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
      condicion_recepcion: values.condicion_recepcion || "",
      sin_foto: state.action.code === "tomarHilo" || state.action.code === "registrarInstalacion"
        ? (boolValue(values.sin_foto) ? "1" : "0") : "",
      motivo_sin_foto: values.motivo_sin_foto || "",
      detalle_sin_foto: values.detalle_sin_foto || "",
      tipo_novedad: state.action.code === "registrarNovedad" ? (values.tipo_novedad || "observacion_general") : "",
      cod_custodio_rectificado: values.cod_custodio_rectificado || "",
      observacion: values.observacion || (isStartAction(state.action.code) ? (values.instrucciones || "") : "")
    };
    if (isStartAction(state.action.code)) {
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
      if (state.action.code === "iniciarTrabajosAgrupados") {
        var regularizacion = work.regularizacion_unidades || work.regularizacion || {};
        payload.id_regularizacion = regularizacion.id || "";
        payload.codigo_origen = regularizacion.codigo_origen || "";
      }
    }
    if (state.action.code === "registrarInstalacion") {
      payload.modo_resolucion = "instalado_entregado";
      payload.condicion_pre_entrega = values.condicion_pre_entrega || "";
      payload.observacion_entrega = values.observacion_entrega || "";
      payload.cod_consulta_origen = state.moduleOptions.cod_consulta_origen || "";
      payload.cod_evolucion_origen = state.moduleOptions.cod_evolucion_origen || "";
    }
    return payload;
  }

  function prepareActionSubmission() {
    var action = state.action;
    var payload = actionPayload();
    var files = action.files.slice(0);
    if (!isStartAction(action.code) || !files.length) {
      return Promise.resolve({ payload: payload, files: files });
    }
    var initialFile = files.shift();
    var readyData = initialFile._tlabDataUrl
      ? Promise.resolve(initialFile._tlabDataUrl)
      : readBlobAsDataUrl(initialFile);
    return readyData.then(function (dataUrl) {
      payload.evidencia_inicial = {
        data_base64: dataUrl,
        nombre_archivo: initialFile.name || "evidencia-inicial.jpg",
        descripcion: state.action.values.observacion || "Evidencia inicial del trabajo"
      };
      return { payload: payload, files: files };
    });
  }

  function submitAction() {
    var action = state.action;
    var confirmed = state.root.querySelector("#tlabActionConfirmed");
    var submit = state.root.querySelector("#tlabActionSubmit");
    var progress = state.root.querySelector("#tlabUploadProgress");
    if (!action || action.saving) { return; }
    if (action.filesProcessing) { showActionError("Esperá a que Telar termine de preparar la fotografía."); return; }
    if (!confirmed || !confirmed.checked) { showActionError("Confirmá la declaración antes de guardar."); return; }
    action.saving = true;
    if (submit) { submit.disabled = true; submit.innerHTML = '<i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Guardando...'; }
    Array.prototype.forEach.call(state.root.querySelectorAll('#tlabActionLayer [data-tlab-command="close-action"], #tlabActionLayer [data-tlab-command="action-back"]'), function (button) {
      button.disabled = true;
    });
    var dialog = state.root.querySelector("#tlabActionLayer .tlab-dialog");
    if (dialog) { dialog.setAttribute("aria-busy", "true"); }
    if (progress && action.files.length) { progress.hidden = false; }
    prepareActionSubmission().then(function (prepared) {
      return request(action.code, prepared.payload, prepared.files, function (percent) {
        var bar = state.root.querySelector("#tlabUploadProgress span");
        if (bar) { bar.style.width = percent + "%"; }
      });
    }).then(function (response) {
      var message = response.message || "La acción quedó registrada en la trazabilidad.";
      var workId = pick(response.data, ["id_trabajo", "cod_trabajo_laboratorio"], state.detailId);
      var groupedStart = action.code === "iniciarTrabajosAgrupados";
      var installationClosure = action.code === "registrarInstalacion";
      var groupedDetailId = pick(response.data, ["cod_detalle_venta"], pick(action.work, ["cod_detalle_venta"], ""));
      if (typeof window.tratamientoLaboratorioClinicoAplicarRespuestaOperacion === "function") {
        window.tratamientoLaboratorioClinicoAplicarRespuestaOperacion(response);
      }
      closeAction(true);
      notify(message, "success");
      loadSummary().then(null, function () {});
      if (groupedStart) {
        state.moduleOptions.cod_detalle_operativo = groupedDetailId;
        state.view = "operativa";
        state.group = "pendientes_entrega";
        renderGroupNavigation();
      }
      if (installationClosure) {
        closeDetail(true);
        if (state.view === "mecanico") {
          state.mechanicTray = "finalizados";
        } else {
          state.view = "operativa";
          state.group = "finalizados";
        }
        renderGroupNavigation();
      }
      loadWorks(false);
      if (workId && !groupedStart && !installationClosure) { openDetail(workId, true); }
    }).then(null, function (error) {
      action.saving = false;
      if (submit) { submit.disabled = false; submit.innerHTML = '<i class="fa-solid ' + escapeAttr(action.config.icon || "fa-arrow-right") + '" aria-hidden="true"></i>' + escapeHtml(action.config.submitLabel || "Confirmar acción"); }
      Array.prototype.forEach.call(state.root.querySelectorAll('#tlabActionLayer [data-tlab-command="close-action"], #tlabActionLayer [data-tlab-command="action-back"]'), function (button) {
        button.disabled = button.getAttribute("data-tlab-command") === "action-back" && action.step === 1;
      });
      var dialog = state.root.querySelector("#tlabActionLayer .tlab-dialog");
      if (dialog) { dialog.setAttribute("aria-busy", "false"); }
      showActionError(error.message + (error.code && /CONFLICT|VERSION/i.test(error.code) ? " Actualizá el trabajo antes de volver a confirmar." : ""));
    });
  }

  function openViewer(url, caption, mime) {
    var layer;
    var isPdf;
    if (!url || !state.root) { return; }
    isPdf = toStringSafe(mime).toLowerCase() === "application/pdf" || /^data:application\/pdf/i.test(url);
    state.focusBeforeLayer = document.activeElement;
    layer = state.root.querySelector("#tlabViewerLayer");
    layer.innerHTML = '<figure class="tlab-viewer ' + (isPdf ? "tlab-viewer--document" : "") + '" role="dialog" aria-modal="true" aria-label="Evidencia ampliada"><button type="button" class="tlab-icon-button" data-tlab-command="close-viewer" aria-label="Cerrar evidencia"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>' + (isPdf ? '<iframe sandbox referrerpolicy="no-referrer" src="' + escapeAttr(url) + '" title="' + escapeAttr(caption || "Documento del trabajo") + '"></iframe>' : '<img src="' + escapeAttr(url) + '" alt="' + escapeAttr(caption || "Evidencia del trabajo") + '">') + '<figcaption class="tlab-viewer__caption">' + escapeHtml(caption || "Evidencia del trabajo") + '</figcaption></figure>';
    layer.hidden = false;
    focusFirst(layer);
  }

  function loadAuthorizedMedia(mediaId, thumbnail) {
    var cacheKey;
    var pending;
    if (!mediaId) { return Promise.reject(new Error("No se pudo identificar la evidencia.")); }
    cacheKey = (thumbnail ? "miniatura:" : "original:") + toStringSafe(mediaId);
    if (state.mediaCache[cacheKey]) {
      return Promise.resolve(state.mediaCache[cacheKey]);
    }
    if (state.mediaRequests[cacheKey]) {
      return state.mediaRequests[cacheKey];
    }
    pending = request("descargarMedia", {
      id_media: mediaId,
      miniatura: thumbnail ? "1" : "0"
    }).then(function (response) {
      var media = response.data.media || response.data;
      var encoded = pick(media, ["data_base64", "base64"], "");
      var mime = pick(media, ["mime", "mime_type", "tipo_mime"], "image/jpeg");
      var loaded;
      if (!encoded) { throw new Error("La evidencia protegida no está disponible."); }
      loaded = {
        src: "data:" + mime + ";base64," + encoded,
        mime: mime,
        nombre: pick(media, ["nombre", "nombre_original"], "Evidencia del trabajo")
      };
      state.mediaCache[cacheKey] = loaded;
      return loaded;
    });
    state.mediaRequests[cacheKey] = pending;
    pending.then(function () {
      delete state.mediaRequests[cacheKey];
    }, function () {
      delete state.mediaRequests[cacheKey];
    });
    return pending;
  }

  function loadAuthorizedThumbnail(element) {
    var mediaId;
    if (!element || element.getAttribute("data-tlab-thumbnail-loading") === "1") { return; }
    mediaId = element.getAttribute("data-tlab-thumbnail-id");
    if (!mediaId) { return; }
    element.setAttribute("data-tlab-thumbnail-loading", "1");
    loadAuthorizedMedia(mediaId, true).then(function (media) {
      if (!document.documentElement.contains(element)
          || element.getAttribute("data-tlab-thumbnail-id") !== toStringSafe(mediaId)) {
        return;
      }
      element.innerHTML = '<img src="' + escapeAttr(media.src) + '" alt="" loading="lazy">';
      element.setAttribute("aria-label", "Evidencia autorizada");
      element.removeAttribute("data-tlab-thumbnail-loading");
      element.setAttribute("data-tlab-thumbnail-loaded", "1");
    }).then(null, function () {
      if (document.documentElement.contains(element)) {
        element.removeAttribute("data-tlab-thumbnail-loading");
        element.setAttribute("data-tlab-thumbnail-error", "1");
      }
    });
  }

  function ensureThumbnailObserver() {
    if (state.thumbnailObserver || !window.IntersectionObserver) {
      return state.thumbnailObserver;
    }
    state.thumbnailObserver = new window.IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) { return; }
        state.thumbnailObserver.unobserve(entry.target);
        loadAuthorizedThumbnail(entry.target);
      });
    }, { root: null, rootMargin: "160px" });
    return state.thumbnailObserver;
  }

  function hydrateAuthorizedThumbnails(scope) {
    var observer;
    var elements;
    if (!scope || !scope.querySelectorAll) { return; }
    elements = scope.querySelectorAll("[data-tlab-thumbnail-id]:not([data-tlab-thumbnail-loaded])");
    observer = ensureThumbnailObserver();
    if (observer) { observer.disconnect(); }
    Array.prototype.forEach.call(elements, function (element) {
      if (observer) { observer.observe(element); }
      else { loadAuthorizedThumbnail(element); }
    });
  }

  function openAuthorizedMedia(mediaId, caption) {
    if (!mediaId) { return; }
    notify("Cargando evidencia protegida...", "info");
    loadAuthorizedMedia(mediaId).then(function (media) {
      openViewer(media.src, caption || media.nombre, media.mime);
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
      if (options.regularizacion_unidades) {
        options.hilo_confirmado = true;
        return openGroupedRegularization(detailId, options.regularizacion_unidades, options);
      }
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
        if (options.soloListadoDetalle || asArray(context.trabajos_activos).length > 1) {
          state.moduleOptions.cod_detalle_operativo = detailId;
          state.view = "operativa";
          state.group = "pendientes_entrega";
          renderGroupNavigation();
          loadWorks(false);
          notify("Se muestran los trabajos independientes vinculados al mismo origen.", "info");
          return response;
        }
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

  function openGroupedRegularization(detailId, regularizacion, options) {
    options = Object.assign({}, options || {}, {
      vista: "operativa",
      grupo: "pendientes_entrega",
      cod_detalle_operativo: detailId,
      regularizacion_unidades: regularizacion || {}
    });
    ensureDom();
    openModule(options);
    state.root.querySelector("#tlabResults").innerHTML = '<div class="tlab-results-state">' + loaderHtml("Preparando los trabajos independientes...", "content") + '</div>';
    return request("obtenerContextoDetalle", { cod_detalle_venta: detailId }).then(function (response) {
      var context = response.data.contexto || response.data;
      var actions = normalizeActions(response.data.acciones_permitidas || context.acciones_permitidas || []);
      var start = actions.filter(function (item) { return item.code === "iniciarTrabajosAgrupados"; })[0];
      if (context.trabajo_activo && context.trabajo_activo.id) {
        state.moduleOptions.cod_detalle_operativo = detailId;
        loadWorks(false);
        notify("Los trabajos de este origen ya fueron creados.", "info");
        return response;
      }
      context.regularizacion_unidades = context.regularizacion_unidades || regularizacion || {};
      if (!start) {
        if (boolValue(context.puede_asegurar_hilo || response.data.puede_asegurar_hilo)) {
          options.regularizacion_unidades = context.regularizacion_unidades;
          return ensureThreadForDetail(detailId, options);
        }
        throw new Error(response.data.mensaje_contexto || "Las ubicaciones estan guardadas, pero este usuario no puede iniciar los trabajos.");
      }
      start.label = "Preparar " + (context.regularizacion_unidades.cantidad_unidades || "los") + " trabajos";
      context.cod_detalle_venta = detailId;
      context.cod_consulta_origen = context.cod_consulta_origen || options.cod_consulta_origen || "";
      context.cod_evolucion_origen = context.cod_evolucion_origen || options.cod_evolucion_origen || "";
      state.startContext = context;
      openAction("iniciarTrabajosAgrupados", start, context);
      return response;
    }).then(null, function (error) {
      notify(error.message, "error");
      loadWorks(false);
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
    abrirRegularizacionUnidades: openGroupedRegularization,
    asegurarHiloDetalle: ensureThreadForDetail,
    abrirAccionTrabajo: openWorkAction,
    registrarInstalacion: function (id, options) { return openWorkAction(id, "registrarInstalacion", options); },
    obtenerMedia: loadAuthorizedMedia,
    endpoint: ENDPOINT
  };

}(window, document));

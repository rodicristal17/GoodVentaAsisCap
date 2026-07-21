(function (global, document) {
  "use strict";

  var variantes = {
    compact: true,
    content: true,
    inline: true,
    overlay: true,
    page: true
  };

  function resolverRutaSvg() {
    var scriptActual = document.currentScript;
    var scripts;
    var indice;
    var rutaScript;

    if (!scriptActual) {
      scripts = document.getElementsByTagName("script");
      for (indice = scripts.length - 1; indice >= 0; indice -= 1) {
        if (scripts[indice].src && scripts[indice].src.indexOf("telar_loader.js") !== -1) {
          scriptActual = scripts[indice];
          break;
        }
      }
    }

    rutaScript = scriptActual && scriptActual.src ? scriptActual.src : "";
    if (rutaScript) {
      return rutaScript.replace(/js_system\/telar_loader\.js(?:\?.*)?$/i, "iconos/telar-loader.svg?v=20260721-2");
    }

    return "../iconos/telar-loader.svg?v=20260721-2";
  }

  function escaparHtml(valor) {
    return String(valor == null ? "" : valor)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  var rutaSvg = resolverRutaSvg();

  function icono() {
    return "<img class=\"telar-loader__mark\" src=\"" + rutaSvg + "\" width=\"48\" height=\"48\" alt=\"\" aria-hidden=\"true\" draggable=\"false\">";
  }

  function html(texto, variante, inverso) {
    var clase;
    variante = variantes[variante] ? variante : "content";
    texto = texto == null ? "Cargando..." : texto;
    clase = "telar-loader telar-loader--" + variante;
    if (inverso === true) {
      clase += " telar-loader--inverse";
    }

    return "<div class=\"" + clase + "\" role=\"status\" aria-live=\"polite\" aria-atomic=\"true\">"
      + icono()
      + "<span class=\"telar-loader__label\">" + escaparHtml(texto) + "</span>"
      + "</div>";
  }

  global.TelarLoader = {
    html: html,
    icono: icono,
    src: rutaSvg
  };
}(window, document));

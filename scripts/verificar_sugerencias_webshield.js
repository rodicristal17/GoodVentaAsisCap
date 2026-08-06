/*
 * Prueba de regresion del manejo de respuestas de Sugerencias.
 *
 * Uso:
 *   node scripts/verificar_sugerencias_webshield.js
 *
 * No usa HTTP ni base de datos. Ejecuta las funciones reales de inicio.js
 * con respuestas JSON y HTML simuladas.
 */

"use strict";

const fs = require("fs");
const path = require("path");
const vm = require("vm");

function afirmar(condicion, mensaje) {
    if (!condicion) {
        throw new Error(mensaje);
    }
    process.stdout.write("[OK] " + mensaje + "\n");
}

function extraerFuncion(fuente, nombre) {
    const inicio = fuente.indexOf("function " + nombre + "(");
    if (inicio < 0) {
        throw new Error("No se encontro la funcion " + nombre + ".");
    }

    const inicioBloque = fuente.indexOf("{", inicio);
    let nivel = 0;
    for (let indice = inicioBloque; indice < fuente.length; indice += 1) {
        if (fuente.charAt(indice) === "{") {
            nivel += 1;
        } else if (fuente.charAt(indice) === "}") {
            nivel -= 1;
            if (nivel === 0) {
                return fuente.substring(inicio, indice + 1);
            }
        }
    }

    throw new Error("La funcion " + nombre + " esta incompleta.");
}

function crearElemento() {
    return {
        children: [],
        className: "",
        colSpan: 0,
        innerHTML: "",
        style: {},
        textContent: "",
        title: "",
        appendChild: function (hijo) {
            this.children.push(hijo);
            return hijo;
        }
    };
}

const base = path.resolve(__dirname, "..");
const rutaInicio = path.join(base, "js_system", "inicio.js");
const rutaHtml = path.join(base, "system", "inicio.html");
const fuenteInicio = fs.readFileSync(rutaInicio, "utf8");
const fuenteHtml = fs.readFileSync(rutaHtml, "utf8");
const nombresFunciones = [
    "respuestaEsVerificacionSeguridadSugerencias",
    "interpretarRespuestaSugerencias",
    "mostrarAvisoSeguridadSugerencias",
    "manejarRespuestaInvalidaSugerencias",
    "buscarSugerencias"
];

const elementos = {
    sugerenciasContainer: crearElemento(),
    notificacionSugerencias: crearElemento()
};
let configuracionAjax = null;
let llamadasAjax = 0;
let mensajes = [];

const contexto = {
    Date: Date,
    JSON: JSON,
    String: String,
    console: { error: function () {} },
    document: {
        createElement: function () { return crearElemento(); },
        getElementById: function (id) { return elementos[id] || null; }
    },
    window: {
        console: { error: function () {} },
        location: { reload: function () {} }
    },
    $: {
        ajax: function (configuracion) {
            configuracionAjax = configuracion;
            llamadasAjax += 1;
        }
    },
    userid: "1",
    passuser: "prueba",
    navegador: "prueba",
    obtener_datos_user: function () {},
    cargarConectividad: function () {},
    manejadordeerroresjquery: function () {},
    respuestaJqueryAjax: function (valor) { return valor === "UI"; },
    ver_vetana_informativa: function (mensaje) { mensajes.push(mensaje); },
    sugerenciasAjaxEnCurso: false,
    sugerenciasUltimaCarga: 0,
    sugerenciasPausaHasta: 0,
    SUGERENCIAS_INTERVALO_MS: 300000,
    SUGERENCIAS_PAUSA_SEGURIDAD_MS: 600000
};

vm.createContext(contexto);
nombresFunciones.forEach(function (nombre) {
    vm.runInContext(extraerFuncion(fuenteInicio, nombre), contexto);
});

const htmlWebShield = "<!DOCTYPE html><html><body><script id='wsidchk'>/z0f76a1d14fd21a8fb5fd0d03e0fdc3d3cedae52f</script></body></html>";
let resultado = contexto.interpretarRespuestaSugerencias(htmlWebShield);
afirmar(!resultado.valida && resultado.seguridad, "La pagina de WebShield se reconoce como verificacion de seguridad.");

resultado = contexto.interpretarRespuestaSugerencias('{"1":"UI","2":"<div>Pendiente</div>","4":"1"}');
afirmar(resultado.valida && resultado.datos["4"] === "1", "La respuesta JSON valida conserva el formato existente.");

resultado = contexto.interpretarRespuestaSugerencias("respuesta incompleta");
afirmar(!resultado.valida && !resultado.seguridad, "Una respuesta no JSON se rechaza sin tratarla como datos.");

contexto.buscarSugerencias(false);
afirmar(llamadasAjax === 1 && configuracionAjax.dataType === "text", "La carga solicita texto para poder clasificar la respuesta.");
configuracionAjax.success(htmlWebShield);
configuracionAjax.complete();
afirmar(
    elementos.notificacionSugerencias.textContent === "!"
        && elementos.sugerenciasContainer.children.length === 1,
    "WebShield deja un aviso visible con opcion de actualizar la pagina."
);

contexto.buscarSugerencias(false);
afirmar(llamadasAjax === 1, "Durante la pausa de seguridad no se repite la solicitud automatica.");

contexto.sugerenciasPausaHasta = 0;
elementos.sugerenciasContainer.children = [];
contexto.buscarSugerencias(true);
configuracionAjax.success('{"1":"UI","2":"<div>Pendiente</div>","4":"1"}');
configuracionAjax.complete();
afirmar(
    elementos.sugerenciasContainer.innerHTML === "<div>Pendiente</div>"
        && elementos.notificacionSugerencias.textContent === 1,
    "La respuesta normal sigue actualizando la lista y el contador."
);

const bloqueSugerencias = fuenteInicio.substring(
    fuenteInicio.indexOf("var sugerenciasAjaxEnCurso"),
    fuenteInicio.indexOf("function verCerrarAbmMigrarCaja", fuenteInicio.indexOf("var sugerenciasAjaxEnCurso"))
);
afirmar(
    bloqueSugerencias.indexOf("GuardarArchivosLog") === -1,
    "Sugerencias ya no descarga el HTML completo como archivo de error."
);
afirmar(
    fuenteInicio.indexOf("if (controlMensaje == 300)") >= 0
        && fuenteInicio.indexOf("SUGERENCIAS_INTERVALO_MS = 300000") >= 0,
    "La actualizacion automatica quedo limitada a cinco minutos."
);
afirmar(
    fuenteHtml.indexOf("inicio.js?x=sugerencias-webshield-20260806-1") >= 0,
    "La pantalla solicita la version corregida de inicio.js."
);

afirmar(mensajes.length === 0, "La carga automatica bloqueada no genera ventanas repetitivas.");
process.stdout.write("RESULTADO: APROBADO\n");
